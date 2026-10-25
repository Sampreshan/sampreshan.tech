<?php
/**
 * Class for the revision compare page.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Admin_Page_Revisions.
 */
class WPCode_Admin_Page_Revisions extends WPCode_Admin_Page {

	/**
	 * If the snippet id is not set, redirect to the list of snippets.
	 *
	 * @var int The snippet id.
	 */
	public $snippet_id;
	/**
	 * Main revision id to display when the page is first loaded.
	 *
	 * @var int
	 */
	public $revision_id;
	/**
	 * The page slug.
	 *
	 * @var string
	 */
	public $page_slug = 'wpcode-revisions';

	/**
	 * The page title.
	 *
	 * @var string
	 */
	public $page_title = '';

	/**
	 * What are we comparing?
	 *
	 * @var string
	 */
	public $compare_title = '';

	/**
	 * The URL to go back to the thing we're seeing revisions for.
	 *
	 * @var string
	 */
	public $back_url = '';

	/**
	 * The snippet for which revisions are loaded.
	 *
	 * @var WPCode_Snippet
	 */
	private $snippet;

	/**
	 * If this is a revision of the Header & Footer.
	 *
	 * @var bool
	 */
	protected $is_global = false;

	/**
	 * If this is a revision of page scripts.
	 *
	 * @var bool
	 */
	protected $is_page_scripts = false;

	/**
	 * Renders the sidebar, detail panel and modal for this item.
	 *
	 * Named differently from the parent's $view (a views/tabs string), whose
	 * public visibility a private property here cannot narrow.
	 *
	 * @var WPCode_Revisions_View
	 */
	private $revisions_view;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->page_title = __( 'Revisions', 'wpcode-premium' );
		$this->menu_title = '';
		parent::__construct();
		add_action( 'admin_head', array( $this, 'hide_menu' ) );
	}

	/**
	 * Page-specific hooks.
	 *
	 * @return void
	 */
	public function page_hooks() {
		add_action( 'admin_init', array( $this, 'capture_id_or_redirect' ) );
		add_action( 'admin_init', array( $this, 'maybe_restore_revision' ), 15 );
		add_filter( 'admin_body_class', array( $this, 'add_wpcode_classname' ) );
		add_filter( 'submenu_file', array( $this, 'change_current_menu' ) );

	}

	/**
	 * Remove the revisions page from the admin menu.
	 */
	public function hide_menu() {
		remove_submenu_page( 'wpcode', $this->page_slug );
	}

	/**
	 * Look for a valid snippet id or redirect to the list of snippets.
	 *
	 * @return void
	 */
	public function capture_id_or_redirect() {
		$id = ! empty( $_GET['revision'] ) ? absint( $_GET['revision'] ) : 0;// phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$parent_id = wpcode()->revisions->get_revision_parent_id( $id );
		if ( 0 === $parent_id ) {
			$this->handle_header_footer_revision( $id );

			return;
		}
		if ( wpcode_get_post_type() !== get_post_type( $parent_id ) ) {
			// Let's check if it's a post we actually have revisions for.
			$this->handle_page_script_revision( $id, $parent_id );

			return;
		}

		if ( ! current_user_can( 'edit_post', $parent_id ) ) {
			$id = 0;
		}

		if ( 0 === $id || false === $parent_id ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page' => 'wpcode',
					),
					$this->admin_url( 'admin.php' )
				)
			);
			exit;
		}

		$this->snippet_id    = $parent_id;
		$this->revision_id   = $id;
		$this->snippet       = wpcode_get_snippet( $this->snippet_id );
		$this->compare_title = $this->snippet->get_title();
		$this->back_url      = add_query_arg(
			array(
				'page'       => 'wpcode-snippet-manager',
				'snippet_id' => $this->snippet_id,
			),
			$this->admin_url( 'admin.php' )
		);
	}

	/**
	 * Setup for a revision of the Header & Footer.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return void
	 */
	public function handle_header_footer_revision( $revision_id ) {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unfiltered_html' ) ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page' => 'wpcode',
					),
					$this->admin_url( 'admin.php' )
				)
			);
			exit;
		}

		$this->is_global   = true;
		$this->revision_id = $revision_id;
		$this->snippet_id  = 0;

		$this->compare_title = __( 'Global Header & Footer', 'insert-headers-and-footers' );
		$this->back_url      = add_query_arg( 'page', 'wpcode-headers-footers', $this->admin_url( 'admin.php' ) );
	}

	/**
	 * Let's try to set up the revision for a page script.
	 *
	 * @param int $id The revision id.
	 * @param int $post_id The id of the post for which we're viewing page scripts revisions.
	 *
	 * @return void
	 */
	public function handle_page_script_revision( $id, $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) || false === $post_id ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page' => 'wpcode',
					),
					$this->admin_url( 'admin.php' )
				)
			);
			exit;
		}

		$this->is_page_scripts = true;
		$this->revision_id     = $id;
		$this->snippet_id      = $post_id;

		$this->compare_title = get_the_title( $post_id );
		$this->back_url      = add_query_arg( 'wpcode-show', 'revisions', get_edit_post_link( $post_id ) );
	}

	/**
	 * Listen for the restore action and restore a revision on the snippet.
	 *
	 * @return void
	 */
	public function maybe_restore_revision() {
		// Check params and nonce.
		if ( ! isset( $_GET['restore'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpcode-restore-revision' ) ) {
			wp_die( esc_html__( 'The link you followed has expired.', 'wpcode-premium' ) );
		}

		$revision_to_restore = absint( $_GET['restore'] );
		$revision            = wpcode()->revisions->get_revision_data( $revision_to_restore );
		if ( empty( $revision ) ) {
			wp_die( esc_html__( 'Invalid revision id.', 'wpcode-premium' ) );
		}
		// Let's determine the revision type.
		$parent = wpcode()->revisions->get_revision_parent_id( $revision_to_restore );
		if ( 0 === $parent ) {
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'unfiltered_html' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wpcode-premium' ), '', array( 'response' => 403 ) );
			}
			$this->handle_header_footer_restore( $revision_to_restore, $revision );

			return;
		}
		$post_type = get_post_type( $parent );
		if ( wpcode_get_post_type() !== $post_type ) {
			if ( ! current_user_can( 'edit_post', $parent ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wpcode-premium' ), '', array( 'response' => 403 ) );
			}
			$this->handle_page_script_restore( $revision_to_restore, $revision, $parent );

			return;
		}

		if ( ! current_user_can( 'edit_post', $parent ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wpcode-premium' ), '', array( 'response' => 403 ) );
		}

		// Bind the restored code type to the user's tier, the same rule the editor's save path applies.
		$code_type           = isset( $revision['code_type'] ) ? $revision['code_type'] : '';
		$required_capability = WPCode_Access::capability_for_code_type( $code_type );
		$can_edit_type       = empty( $required_capability ) ? current_user_can( 'wpcode_edit_snippets' ) : current_user_can( $required_capability );

		if ( ! $can_edit_type ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wpcode-premium' ), '', array( 'response' => 403 ) );
		}

		$revision['code'] = wp_slash( $revision['code'] );

		$snippet = wpcode_get_snippet( $revision );

		if ( ! $snippet->save() ) {
			wp_safe_redirect( $this->get_redirect_url( $revision_to_restore, 'restore_error', 1 ) );
			exit;
		}

		wp_safe_redirect( $this->get_restored_redirect_url( $revision_to_restore ) );
		exit;
	}

	/**
	 * Handle the restore of a revision of the Header & Footer.
	 *
	 * The undo revision is created while the body option is updated, so all three options are
	 * updated in a fixed order with the live value as the fallback for a key the revision lacks.
	 *
	 * @param int   $revision_to_restore The revision id.
	 * @param array $revision            The revision data.
	 *
	 * @return void
	 */
	public function handle_header_footer_restore( $revision_to_restore, $revision ) {
		$keys = array(
			'ihaf_insert_header',
			'ihaf_insert_footer',
			'ihaf_insert_body',
		);

		foreach ( $keys as $key ) {
			$value = array_key_exists( $key, $revision ) ? $revision[ $key ] : get_option( $key, '' );

			update_option( $key, $value );
		}

		wp_safe_redirect( $this->get_restored_redirect_url( $revision_to_restore ) );
		exit;
	}

	/**
	 * Handle the restore of a revision of a page script.
	 *
	 * @param     $revision_to_restore
	 * @param     $revision
	 * @param int $post_id The post id.
	 *
	 * @return void
	 */
	public function handle_page_script_restore( $revision_to_restore, $revision, $post_id ) {
		$allowed_keys = array(
			'_wpcode_header_scripts',
			'_wpcode_footer_scripts',
			'_wpcode_body_scripts',
			'_wpcode_page_snippets',
		);
		$updated_data = array();

		foreach ( $revision as $key => $value ) {
			if ( ! in_array( $key, $allowed_keys, true ) ) {
				continue;
			}
			$updated_data[ $key ] = $value;

			update_post_meta( $post_id, $key, $value );
		}

		wpcode()->revisions->create_revision( $post_id, $updated_data );

		wp_safe_redirect( $this->get_restored_redirect_url( $revision_to_restore ) );
		exit;
	}

	/**
	 * Where a successful restore lands: this item's revisions page with the new current revision
	 * selected.
	 *
	 * The restore has just written a revision, and with the revision limit on, the trim in the same
	 * request can remove the restored revision itself. A URL that selects the restored revision
	 * then names a row that no longer exists, and the page sends the user to the snippet list.
	 *
	 * @param int $revision_id The revision that was restored.
	 *
	 * @return string
	 */
	private function get_restored_redirect_url( $revision_id ) {
		// A fresh view, because the restore changed the rows and the live data.
		$view     = new WPCode_Revisions_View( $this->get_type(), $this->snippet_id );
		$selected = $view->get_current_revision();

		if ( ! $selected ) {
			$selected = $view->get_newest();
		}

		return $this->get_redirect_url( $selected ? absint( $selected->revision_id ) : $revision_id, 'restored', $revision_id );
	}

	/**
	 * Where a restore lands: back on this page with the revision selected and one status flag.
	 *
	 * @param int    $revision_id The revision that was restored, or refused.
	 * @param string $flag_key    The status argument the page reads on arrival.
	 * @param int    $flag_value  Its value.
	 *
	 * @return string
	 */
	private function get_redirect_url( $revision_id, $flag_key, $flag_value ) {
		return add_query_arg( array( $flag_key => absint( $flag_value ) ), $this->get_view()->get_view_url( $revision_id ) );
	}

	/**
	 * Add the admin-body class as it gets removed when we remove the submenu item.
	 *
	 * @param string $classes The admin body classes.
	 *
	 * @return string
	 */
	public function add_wpcode_classname( $classes ) {
		$classes .= ' wpcode-admin-page';

		if ( ! empty( wpcode()->settings->get_option( 'dark_mode' ) ) ) {
			$classes .= ' wpcode-dark-mode';
		}

		return $classes;
	}

	/**
	 * Change the active submenu for the revisions page.
	 *
	 * @param null|string $submenu_file The submenu file.
	 *
	 * @return null|string
	 */
	public function change_current_menu( $submenu_file ) {
		return 'wpcode';
	}

	/**
	 * The bottom of the header part.
	 *
	 * @return void
	 */
	public function output_header_bottom() {
		?>
		<div class="wpcode-column">
			<h1>
				<?php
				printf(
				// Translators: The placeholder gets replaced with the title of the snippet.
					esc_html__( 'Code Revisions for "%s"', 'wpcode-premium' ),
					esc_html( $this->compare_title )
				);
				?>
			</h1>
		</div>
		<div class="wpcode-column">
			<a href="<?php echo esc_url( $this->back_url ); ?>" class="wpcode-button-text">
				<?php
				wpcode_icon( 'arrow-left', 16, 8 );
				esc_html_e( 'Go back to editor', 'wpcode-premium' );
				?>
			</a>
		</div>
		<?php
	}

	/**
	 * Page content: the sidebar list and the detail panel for the selected revision.
	 *
	 * @return void
	 */
	public function output_content() {
		$view     = $this->get_view();
		$revision = $view->get_revision( $this->revision_id );

		if ( ! $revision ) {
			$revision = $view->get_newest();
		}
		if ( ! $revision ) {
			echo '<p>' . esc_html__( 'There are no revisions yet.', 'wpcode-premium' ) . '</p>';

			return;
		}

		$restored_id   = $this->get_restored_id();
		$restore_error = $this->get_restore_error();
		?>
		<div class="wpcode-revisions-layout">
			<?php echo $view->render_sidebar( absint( $revision->revision_id ), $restored_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the view. ?>
			<div class="wpcode-revisions-detail" id="wpcode-revisions-detail" data-restored="<?php echo absint( $restored_id ); ?>" aria-live="polite">
				<?php echo $view->render_detail( $revision, $restored_id, $restore_error ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the view. ?>
			</div>
		</div>
		<?php
	}

	/**
	 * The view for the item whose revisions are shown.
	 *
	 * @return WPCode_Revisions_View
	 */
	public function get_view() {
		if ( ! isset( $this->revisions_view ) ) {
			$this->revisions_view = new WPCode_Revisions_View( $this->get_type(), $this->snippet_id );
		}

		return $this->revisions_view;
	}

	/**
	 * The just-restored revision from the URL.
	 *
	 * Not checked against the item's revisions: the revision limit can remove the restored revision
	 * in the restore request, and the restored notice must still show. The view only compares it
	 * with the item's own rows.
	 *
	 * @return int
	 */
	private function get_restored_id() {
		return isset( $_GET['restored'] ) ? absint( $_GET['restored'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display flag only.
	}

	/**
	 * Whether the last restore attempt was refused.
	 *
	 * @return bool
	 */
	private function get_restore_error() {
		return isset( $_GET['restore_error'] ) && 1 === absint( $_GET['restore_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display flag only.
	}

	/**
	 * Footer: the shared overlays plus the restore confirmation modal.
	 *
	 * @return void
	 */
	public function output_footer() {
		parent::output_footer();
		echo $this->get_view()->render_modal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the view.
	}

	/**
	 * Override this method to prevent having two ".wrap" on the same page.
	 *
	 * @return void
	 */
	public function maybe_output_message() {
	}

	/**
	 * Get the label for a code type from its key.
	 *
	 * @param string $type The code type key.
	 *
	 * @return string
	 */
	public static function code_type_to_label( $type ) {
		$code_types = wpcode()->execute->get_options();

		return isset( $code_types[ $type ] ) ? $code_types[ $type ] : '';
	}

	/**
	 * Convert the schedule array to text for display.
	 *
	 * @param array $schedule The schedule array.
	 *
	 * @return string
	 */
	public static function schedule_to_text( $schedule ) {
		$start = isset( $schedule['start'] ) ? $schedule['start'] : '';
		$end   = isset( $schedule['end'] ) ? $schedule['end'] : '';

		return __( 'Start', 'wpcode-premium' ) . ': ' . $start . ' ' . __( 'End', 'wpcode-premium' ) . ': ' . $end;
	}


	/**
	 * Convert a set of conditional logic rules to a text representation.
	 *
	 * @param array $rules Array of rules as they are stored in the snippet object.
	 *
	 * @return string
	 * @see WPCode_Admin_Page_Snippet_Manager::get_conditional_logic_input()
	 * @see WPCode_Snippet::get_conditional_rules()
	 */
	public static function conditional_logic_to_text( $rules ) {
		if ( empty( $rules ) ) {
			return '';
		}

		$text = '';
		if ( isset( $rules['show'] ) ) {
			$text .= 'show' === $rules['show'] ? __( 'Show', 'wpcode-premium' ) : __( 'Hide', 'wpcode-premium' );
			$text .= ' ' . __( 'this code snippet if', 'wpcode-premium' );
			$text .= PHP_EOL;
		}
		if ( ! empty( $rules['groups'] ) ) {

			$rules_count = count( $rules['groups'] );
			foreach ( $rules['groups'] as $i => $group ) {
				$and_rules_count = count( $group );
				foreach ( $group as $and_i => $and_rules ) {
					$text .= self::get_conditional_logic_group_as_text( $and_rules );
					if ( $and_rules_count > 1 && $and_i < $and_rules_count - 1 ) {
						$text .= PHP_EOL . __( 'AND', 'wpcode-premium' ) . PHP_EOL;
					}
				}
				if ( $rules_count > 1 && $i < $rules_count - 1 ) {
					$text .= PHP_EOL . __( 'OR', 'wpcode-premium' ) . PHP_EOL;
				}
			}
		}

		return $text;
	}

	/**
	 * Convert conditional rules group to a human-readable text that can be easily compared.
	 *
	 * @param array $group The group of rules.
	 *
	 * @return string
	 */
	public static function get_conditional_logic_group_as_text( $group ) {
		$logic_options = wpcode()->conditional_logic->get_all_admin_options();

		if ( ! isset( $group['type'] ) || ! isset( $logic_options[ $group['type'] ] ) ) {
			return '';
		}
		$relation_labels = wpcode_get_conditions_relation_labels();

		$cl_options = $logic_options[ $group['type'] ];
		$value      = $group['value'];
		if ( 'logged_in' === $group['option'] ) {
			$value = boolval( $value );
		}
		if ( ! empty( $cl_options['options'][ $group['option'] ]['options'] ) && is_array( $cl_options['options'][ $group['option'] ]['options'] ) ) {
			foreach ( $cl_options['options'][ $group['option'] ]['options'] as $option ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $i => $v ) {
						if ( $option['value'] === $v ) {
							$value[ $i ] = $option['label'];
						}
					}
				} else {
					if ( $option['value'] === $value ) {
						$value = $option['label'];
					}
				}
			}
		}

		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		return '[' . $cl_options['label'] . '] [' . $cl_options['options'][ $group['option'] ]['label'] . '] [' . $relation_labels[ $group['relation'] ] . '] [' . $value . ']';
	}

	/**
	 * Convert the page scripts snippets to text for display.
	 *
	 * @param array $data The data to convert.
	 *
	 * @return string
	 */
	public static function page_scripts_snippets_to_text( $data ) {
		if ( ! is_array( $data ) ) {
			return '';
		}

		$lines = array();
		foreach ( $data as $snippet_data ) {
			if ( ! is_array( $snippet_data ) || ! isset( $snippet_data['snippet_id'], $snippet_data['location'] ) ) {
				continue;
			}

			$snippet = wpcode_get_snippet( $snippet_data['snippet_id'] );

			$line  = sprintf(
			// Translators: %s: Title of the snippet.
				esc_html__( 'Snippet: %s', 'wpcode-premium' ),
				$snippet->get_title()
			);
			$line .= "\n";
			$line .= sprintf(
			// Translators: %s: Label of the auto-insert location selected for the snippet.
				esc_html__( 'Location: %s', 'wpcode-premium' ),
				wpcode_find_location_label( $snippet_data['location'] )
			);

			$lines[] = $line;
		}

		return implode( "\n\n", $lines );
	}

	/**
	 *
	 * @param array $snippet_data The snippet data to extract.
	 *
	 * @return string
	 */
	public static function location_extra_to_text( $snippet_data ) {
		if ( empty( $snippet_data['location_extra'] ) ) {
			return '';
		}
		$anywhere_locations = array(
			'before_css_selector',
			'after_css_selector',
			'start_css_selector',
			'end_css_selector',
		);
		$text               = '';
		if ( in_array( $snippet_data['location'], $anywhere_locations, true ) ) {
			$location_data = json_decode( $snippet_data['location_extra'], true );
			if ( ! empty( $location_data['css_selector'] ) ) {
				$text .= sprintf(
				         /* Translators: %s: CSS selector */
					         esc_html__( 'CSS selector: %s', 'wpcode-premium' ),
					         $location_data['css_selector']
				         ) . "\n";
			}
			if ( ! empty( $location_data['index'] ) ) {
				$text .= sprintf(
				         /* Translators: %s: CSS selector */
					         esc_html__( 'Element Index: %s', 'wpcode-premium' ),
					         $location_data['index']
				         ) . "\n";
			}
		}

		return $text;
	}

	/**
	 * Get the type of revision we are displaying.
	 *
	 * @return string
	 */
	public function get_type() {
		$type = 'snippet';
		if ( $this->is_global ) {
			$type = 'header_footer';
		}
		if ( $this->is_page_scripts ) {
			$type = 'page_scripts';
		}

		return $type;
	}
}
