<?php
/**
 * Renders the code of a revision as a unified diff against the live code.
 *
 * @package WPCode
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCode_Revisions_Code_Diff.
 */
class WPCode_Revisions_Code_Diff {

	/**
	 * Unchanged lines kept visible on each side of a change.
	 *
	 * @var int
	 */
	const CONTEXT_LINES = 2;

	/**
	 * Word marks are dropped when the changed characters exceed this share of the line, as core does.
	 *
	 * @var float
	 */
	const MARK_THRESHOLD = 0.6;

	/**
	 * Skip word marks for a change block with more line pairs than this; pairing is quadratic.
	 *
	 * @var int
	 */
	const MAX_MARK_PAIRS = 2500;

	/**
	 * Skip word marks for a line longer than this; the word diff is quadratic in line length.
	 *
	 * @var int
	 */
	const MAX_MARK_LINE_LENGTH = 2000;

	/**
	 * Render the diff markup, or an empty string when both texts are equal.
	 *
	 * @param string $current The live code.
	 * @param string $after   The code the revision would restore.
	 *
	 * @return string
	 */
	public static function render( $current, $after ) {
		$built = self::build( $current, $after );

		if ( empty( $built['lines'] ) ) {
			return '';
		}

		$folds = wp_list_filter( $built['lines'], array( 'type' => 'fold' ) );
		$html  = sprintf( '<div class="wpcode-code-diff" data-hunks="%d">', absint( $built['hunks'] ) );

		$html .= self::toolbar( $built['hunks'], ! empty( $folds ) );
		$html .= '<div class="wpcode-code-diff-lines">';

		foreach ( $built['lines'] as $line ) {
			$html .= 'fold' === $line['type'] ? self::fold_row( $line ) : self::line_row( $line );
		}

		$html .= '</div></div>';

		return $html;
	}

	/**
	 * Compute the folded line list and hunk count. Empty lines when the texts are equal.
	 *
	 * @param string $current The live code.
	 * @param string $after   The code the revision would restore.
	 *
	 * @return array{lines: array, hunks: int}
	 */
	public static function build( $current, $after ) {
		$current = self::normalize( $current );
		$after   = self::normalize( $after );

		if ( $current === $after ) {
			return array(
				'lines' => array(),
				'hunks' => 0,
			);
		}

		$lines = self::diff_lines( explode( "\n", $current ), explode( "\n", $after ) );
		$hunks = 0;

		foreach ( $lines as $line ) {
			if ( null !== $line['hunk'] ) {
				$hunks = max( $hunks, $line['hunk'] + 1 );
			}
		}

		return array(
			'lines' => self::fold( $lines ),
			'hunks' => $hunks,
		);
	}

	/**
	 * Whether the two texts differ, without building the diff.
	 *
	 * @param string $current The live code.
	 * @param string $after   The code the revision would restore.
	 *
	 * @return bool
	 */
	public static function differs( $current, $after ) {
		return self::normalize( $current ) !== self::normalize( $after );
	}

	/**
	 * Normalize line endings.
	 *
	 * @param string $text The text.
	 *
	 * @return string
	 */
	private static function normalize( $text ) {
		return str_replace( array( "\r\n", "\r" ), "\n", (string) $text );
	}

	/**
	 * Run the line diff and turn each edit into typed line records.
	 *
	 * @param array $current_lines Lines of the live code.
	 * @param array $after_lines   Lines of the revision code.
	 *
	 * @return array
	 */
	private static function diff_lines( $current_lines, $after_lines ) {
		if ( ! class_exists( 'WP_Text_Diff_Renderer_Table', false ) ) {
			require ABSPATH . WPINC . '/wp-diff.php';
		}

		$diff  = new Text_Diff( 'auto', array( $current_lines, $after_lines ) );
		$lines = array();
		$old   = 1;
		$new   = 1;
		$hunk  = -1;

		foreach ( $diff->getDiff() as $edit ) {
			if ( $edit instanceof Text_Diff_Op_copy ) {
				foreach ( $edit->orig as $text ) {
					$lines[] = self::line( 'context', $old++, $new++, self::escape( $text ), null );
				}
				continue;
			}

			$hunk++;
			$orig  = is_array( $edit->orig ) ? $edit->orig : array();
			$final = is_array( $edit->final ) ? $edit->final : array();

			list( $orig_html, $final_html ) = self::mark_words( $orig, $final );

			foreach ( $orig_html as $html ) {
				$lines[] = self::line( 'del', $old++, '', $html, $hunk );
			}
			foreach ( $final_html as $html ) {
				$lines[] = self::line( 'add', '', $new++, $html, $hunk );
			}
		}

		return $lines;
	}

	/**
	 * Build one line record.
	 *
	 * @param string   $type One of context, del, add.
	 * @param int|string $old  Old line number or empty string.
	 * @param int|string $new  New line number or empty string.
	 * @param string   $html Escaped line text, may contain <mark>.
	 * @param int|null $hunk Hunk index for changed lines.
	 *
	 * @return array
	 */
	private static function line( $type, $old, $new, $html, $hunk ) {
		return array(
			'type' => $type,
			'old'  => $old,
			'new'  => $new,
			'html' => $html,
			'hunk' => $hunk,
			'fold' => null,
		);
	}

	/**
	 * Escape one code line the way core's inline diff renderer does, existing entities included.
	 *
	 * esc_html() leaves an entity alone, so a line holding &amp; would read as & next to a
	 * word-marked line, which core double encodes.
	 *
	 * @param string $text The line text.
	 *
	 * @return string
	 */
	private static function escape( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Escape the lines of a change block and add <mark> around changed words in paired lines.
	 *
	 * @param array $orig  Deleted lines.
	 * @param array $final Added lines.
	 *
	 * @return array{0: array, 1: array} Escaped HTML for the deleted and the added lines.
	 */
	private static function mark_words( $orig, $final ) {
		$orig_html  = array_map( array( __CLASS__, 'escape' ), $orig );
		$final_html = array_map( array( __CLASS__, 'escape' ), $final );

		if ( empty( $orig ) || empty( $final ) || count( $orig ) * count( $final ) > self::MAX_MARK_PAIRS ) {
			return array( $orig_html, $final_html );
		}

		$table = new WP_Text_Diff_Renderer_Table();
		list( $orig_matches ) = $table->interleave_changed_lines( $orig, $final );

		foreach ( $orig_matches as $o => $f ) {
			if ( ! is_numeric( $f ) ) {
				continue;
			}

			// A minified script is one very long line: the word diff on it costs far more than the marks are worth.
			if ( strlen( $orig[ $o ] ) > self::MAX_MARK_LINE_LENGTH || strlen( $final[ $f ] ) > self::MAX_MARK_LINE_LENGTH ) {
				continue;
			}

			$renderer = new WP_Text_Diff_Renderer_inline();
			$inline   = rtrim( $renderer->render( new Text_Diff( 'auto', array( array( $orig[ $o ] ), array( $final[ $f ] ) ) ) ), "\n" );

			if ( ! self::marks_are_useful( $inline ) ) {
				continue;
			}

			$orig_html[ $o ]  = self::to_marks( preg_replace( '|<ins>.*?</ins>|', '', $inline ), 'del' );
			$final_html[ $f ] = self::to_marks( preg_replace( '|<del>.*?</del>|', '', $inline ), 'ins' );
		}

		return array( $orig_html, $final_html );
	}

	/**
	 * Core's rule: marks that cover most of the line are noise, so drop them.
	 *
	 * @param string $inline Inline renderer output for one pair of lines.
	 *
	 * @return bool
	 */
	private static function marks_are_useful( $inline ) {
		if ( ! preg_match_all( '!(<ins>.*?</ins>|<del>.*?</del>)!', $inline, $matches ) ) {
			return false;
		}

		// Drop only the renderer's own tags; wp_strip_all_tags() also trims, which skews indented code.
		$marked   = strlen( preg_replace( '#</?(ins|del)>#', '', implode( ' ', $matches[0] ) ) );
		$stripped = strlen( preg_replace( '#</?(ins|del)>#', '', $inline ) ) * 2 - $marked;

		return $stripped > 0 && ( $marked / $stripped ) <= self::MARK_THRESHOLD;
	}

	/**
	 * Replace one of the inline renderer's tags with <mark>.
	 *
	 * @param string $html The inline renderer output with the other tag already removed.
	 * @param string $tag  del or ins.
	 *
	 * @return string
	 */
	private static function to_marks( $html, $tag ) {
		return str_replace( array( "<$tag>", "</$tag>" ), array( '<mark>', '</mark>' ), $html );
	}

	/**
	 * Fold long runs of context lines, keeping CONTEXT_LINES next to each change.
	 *
	 * @param array $lines Line records in order.
	 *
	 * @return array
	 */
	private static function fold( $lines ) {
		$out       = array();
		$run       = array();
		$fold      = 0;
		$seen_hunk = false;
		$count     = count( $lines );

		for ( $i = 0; $i <= $count; $i++ ) {
			$at_end = $i === $count;

			if ( ! $at_end && 'context' === $lines[ $i ]['type'] ) {
				$run[] = $lines[ $i ];
				continue;
			}

			if ( ! empty( $run ) ) {
				$keep_start = $seen_hunk ? self::CONTEXT_LINES : 0;
				$keep_end   = $at_end ? 0 : self::CONTEXT_LINES;
				$out        = array_merge( $out, self::fold_run( $run, $keep_start, $keep_end, $fold ) );
				$run        = array();
			}

			if ( ! $at_end ) {
				$out[]     = $lines[ $i ];
				$seen_hunk = true;
			}
		}

		return $out;
	}

	/**
	 * Fold the middle of one run of context lines. A fold row standing in for a single line takes
	 * the same space as the line, so at least two lines must be hidden.
	 *
	 * @param array $run        The context lines.
	 * @param int   $keep_start Lines to keep visible at the start.
	 * @param int   $keep_end   Lines to keep visible at the end.
	 * @param int   $fold       Next fold index, incremented when a fold is emitted.
	 *
	 * @return array
	 */
	private static function fold_run( $run, $keep_start, $keep_end, &$fold ) {
		$total = count( $run );

		if ( $total - $keep_start - $keep_end < 2 ) {
			return $run;
		}

		$hidden = array_slice( $run, $keep_start, $total - $keep_start - $keep_end );

		foreach ( $hidden as &$line ) {
			$line['fold'] = $fold;
		}
		unset( $line );

		$marker = array(
			'type'  => 'fold',
			'old'   => '',
			'new'   => '',
			'html'  => '',
			'hunk'  => null,
			'fold'  => $fold,
			'count' => count( $hidden ),
		);

		$fold++;

		return array_merge(
			array_slice( $run, 0, $keep_start ),
			array( $marker ),
			$hidden,
			$keep_end ? array_slice( $run, $total - $keep_end ) : array()
		);
	}

	/**
	 * The counter, hunk navigation and, when a fold exists, the show/hide toggle.
	 *
	 * @param int  $hunks     Number of hunks.
	 * @param bool $has_folds Whether any line is folded.
	 *
	 * @return string
	 */
	private static function toolbar( $hunks, $has_folds ) {
		$counter = sprintf(
			/* translators: 1: the current change number (wrapped in a span), 2: the total number of changes. */
			esc_html__( 'Change %1$s of %2$s', 'wpcode-premium' ),
			'<span class="wpcode-code-diff-counter-current">1</span>',
			absint( $hunks )
		);
		$toggle = '';

		if ( $has_folds ) {
			$toggle = sprintf(
				'<button type="button" class="wpcode-button-text wpcode-code-diff-toggle" data-show="%1$s" data-hide="%2$s">%3$s</button>',
				esc_attr__( 'Show All Lines', 'wpcode-premium' ),
				esc_attr__( 'Hide Unchanged Lines', 'wpcode-premium' ),
				esc_html__( 'Show All Lines', 'wpcode-premium' )
			);
		}

		return sprintf(
			'<div class="wpcode-code-diff-toolbar"><div class="wpcode-code-diff-nav"><span class="wpcode-code-diff-counter">%1$s</span><button type="button" class="wpcode-button wpcode-button-secondary wpcode-button-small wpcode-code-diff-prev" disabled>%2$s</button><button type="button" class="wpcode-button wpcode-button-secondary wpcode-button-small wpcode-code-diff-next"%3$s>%4$s</button></div>%5$s</div>',
			$counter,
			esc_html__( 'Previous', 'wpcode-premium' ),
			$hunks > 1 ? '' : ' disabled',
			esc_html__( 'Next', 'wpcode-premium' ),
			$toggle
		);
	}

	/**
	 * One code line.
	 *
	 * @param array $line The line record.
	 *
	 * @return string
	 */
	private static function line_row( $line ) {
		$classes = array( 'wpcode-code-diff-line', 'wpcode-code-diff-line-' . $line['type'] );
		$attrs   = '';

		if ( null !== $line['hunk'] ) {
			$attrs .= sprintf( ' data-hunk="%d"', absint( $line['hunk'] ) );
		}
		if ( null !== $line['fold'] ) {
			$classes[] = 'wpcode-code-diff-line-folded';
			$attrs    .= sprintf( ' data-fold="%d"', absint( $line['fold'] ) );
		}

		if ( 'del' === $line['type'] ) {
			$sign = '−';
		} elseif ( 'add' === $line['type'] ) {
			$sign = '+';
		} else {
			$sign = $line['new'];
		}

		return sprintf(
			'<div class="%1$s"%2$s><span class="wpcode-code-diff-old">%3$s</span><span class="wpcode-code-diff-sign">%4$s</span><span class="wpcode-code-diff-text">%5$s</span></div>',
			esc_attr( implode( ' ', $classes ) ),
			$attrs,
			esc_html( $line['old'] ),
			esc_html( $sign ),
			wp_kses( $line['html'], array( 'mark' => array() ) )
		);
	}

	/**
	 * The clickable row standing in for hidden context lines.
	 *
	 * @param array $line The fold record.
	 *
	 * @return string
	 */
	private static function fold_row( $line ) {
		return sprintf(
			'<a href="#" class="wpcode-code-diff-fold" data-fold="%1$d"><span>···</span><span>%2$s</span></a>',
			absint( $line['fold'] ),
			esc_html(
				sprintf(
					/* translators: %d: number of unchanged lines hidden behind the fold. */
					_n( '%d unchanged line', '%d unchanged lines', $line['count'], 'wpcode-premium' ),
					$line['count']
				)
			)
		);
	}
}
