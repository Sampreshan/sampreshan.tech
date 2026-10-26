<?php
/**
 * "Know more" mark: an animated shankh that stands in for View / Read links.
 *
 * The shankh shows most of the time; now and then it fades and the short word
 * (View or Read) appears in its place, then the shankh returns. The full link
 * purpose is kept for screen readers. Motion stops under prefers-reduced-motion
 * (the shankh stays). Output goes INSIDE an existing <a>.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Inner markup for a know-more link.
 *
 * @param string $word  Short visible word: View or Read (already translated).
 * @param string $label Full accessible label; defaults to "Know more".
 * @return string
 */
function sp_know_more_html( $word, $label = '' ) {
	static $n = 0;
	$n++;
	$label = '' !== $label ? $label : __( 'Know more', 'sampreshan-child' );
	// Spread the reveal moments so many marks on one page never blink together.
	$delay = ( ( $n * 2.3 ) % 9 );
	$icon  = function_exists( 'sp_icon' ) ? sp_icon( 'sym-shankh', 'sp-icon sp-icon--sm', '' ) : '';
	return '<span class="sp-know" style="--sp-know-d:-' . esc_attr( number_format( $delay, 1, '.', '' ) ) . 's" title="' . esc_attr__( 'Know more', 'sampreshan-child' ) . '">'
		. '<span class="sp-know__shankh" aria-hidden="true">' . $icon . '</span>'
		. '<span class="sp-know__word" aria-hidden="true">' . esc_html( $word ) . '</span>'
		. '<span class="screen-reader-text">' . esc_html( $label ) . '</span>'
		. '</span>';
}

function sp_know_more( $word, $label = '' ) {
	echo sp_know_more_html( $word, $label ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		$file = '/assets/css/know-more.css';
		wp_enqueue_style( 'sp-know-more', get_stylesheet_directory_uri() . $file, array(), (string) filemtime( get_stylesheet_directory() . $file ) );
	},
	40
);
