<?php
/**
 * Pro-specific settings admin page.
 *
 * @package WPCode
 */

/**
 * Pro-specific settings admin page.
 */
class WPCode_Admin_Page_Settings_Pro extends WPCode_Admin_Page_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( $this->is_multisite_core() ) {
			$this->page_slug = 'wpcode-mu-settings';
		}
		parent::__construct();

		$this->register_network_page();
	}

	/**
	 * Register the network settings page.
	 *
	 * @return void
	 */
	public function register_network_page() {
		add_action( 'network_admin_menu', array( $this, 'add_network_page' ) );
	}

	/**
	 * Add the network settings page.
	 *
	 * @return void
	 */
	public function add_network_page() {
		add_filter( 'admin_body_class', array( $this, 'add_wpcode_classname' ) );
		add_submenu_page(
			'settings.php',
			$this->page_title,
			'WPCode',
			'wpcode_edit_snippets',
			$this->page_slug,
			array(
				wpcode()->admin_page_loader,
				'admin_menu_page',
			)
		);
	}

	/**
	 * Add page-specific hooks.
	 *
	 * @return void
	 */
	public function page_hooks() {
		parent::page_hooks();

		add_action( 'admin_init', array( $this, 'save_access_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_show_if_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_cleanup_styles' ) );

		add_filter( 'wpcode_admin_js_data', array( $this, 'add_js_data' ) );

		if ( $this->is_multisite_core() ) {
			add_action( 'wpcode_mu_admin_page', array( $this, 'output' ) );
			add_action( 'wpcode_mu_admin_page', array( $this, 'output_footer' ) );

			$this->views = array(
				'general' => __( 'Network Settings', 'wpcode-premium' ),
			);
		}
	}

	/**
	 * Check if we are on the network settings page using this actual class.
	 *
	 * @return bool
	 */
	public function is_multisite_core() {
		return is_multisite() && is_network_admin() && 'WPCode_Admin_Page_Settings_Pro' === get_class( $this );
	}

	/**
	 * Hide the top-right menu for the network settings page.
	 *
	 * @return void
	 */
	public function output_header_right() {
		if ( $this->is_multisite_core() ) {
			return;
		}
		parent::output_header_right();
	}

	/**
	 * Extend the settings page with pro-specific fields.
	 *
	 * @return void
	 */
	public function output_view_general() {
		if ( $this->is_multisite_core() ) {
			$this->multisite_addon();
			$this->metabox_row(
				__( 'License Key', 'wpcode-premium' ),
				$this->get_license_key_field(),
				'wpcode-setting-license-key'
			);

			return;
		}

		$this->metabox_row(
			__( 'License Key', 'wpcode-premium' ),
			$this->get_license_key_field(),
			'wpcode-setting-license-key'
		);

		$this->common_settings();

		$this->php_load_as_file_setting();

		$this->revisions_removal_setting();

		$this->uninstall_setting();

		wp_nonce_field( $this->action, $this->nonce_name );

		?>
		<button class="wpcode-button" type="submit">
			<?php esc_html_e( 'Save Changes', 'wpcode-premium' ); ?>
		</button>
		<?php
	}

	/**
	 * Give users a way to activate the Multisite Addon on this page.
	 *
	 * @return void
	 */
	public function multisite_addon() {
		$addon_name = 'wpcode-multisite';

		ob_start();
		if ( wpcode()->license->is_addon_allowed( $addon_name, true ) ) {
			// Is the addon installed?
			$addon_data  = wpcode()->addons->get_addon( $addon_name );
			$button_text = __( 'Install Addon Now', 'wpcode-premium' );
			if ( ! empty( $addon_data->installed ) ) {
				$button_text = __( 'Activate Addon', 'wpcode-premium' );
			}
			?>
			<button class="wpcode-button wpcode-button-install-addon" data-addon="<?php echo esc_attr( $addon_name ); ?>">
				<?php echo esc_html( $button_text ); ?>
			</button>
			<p><?php esc_html_e( 'Install the WPCode Multisite Addon to manage snippets across the whole network.', 'wpcode-premium' ); ?></p>
			<?php
		} else {
			?>
			<p>
				<strong><?php esc_html_e( 'The WPCode Multisite addon is available on the Elite plan.', 'wpcode-premium' ); ?></strong>
			</p>
			<p><?php esc_html_e( 'Please activate your license key below to get started.', 'wpcode-premium' ); ?></p>
			<?php
		}

		$addon_button = ob_get_clean();

		$this->metabox_row(
			__( 'Multisite Addon', 'wpcode-premium' ),
			$addon_button,
			'wpcode-multisite-addon-button'
		);
	}

	/**
	 * License key field for the Pro settings page.
	 *
	 * @return false|string
	 */
	public function get_license_key_field() {
		$license      = wpcode()->license->get_option( is_network_admin() );
		$key          = ! empty( $license['key'] ) ? $license['key'] : '';
		$type         = ! empty( $license['type'] ) ? $license['type'] : '';
		$is_valid_key = ! empty( $key ) &&
						( isset( $license['is_expired'] ) && $license['is_expired'] === false ) &&
						( isset( $license['is_disabled'] ) && $license['is_disabled'] === false ) &&
						( isset( $license['is_invalid'] ) && $license['is_invalid'] === false );

		$hide        = $is_valid_key ? '' : 'wpcode-hide';
		$account_url = wpcode_utm_url(
			'https://library.wpcode.com/account/downloads/',
			'settings-page',
			'license-key',
			'account'
		);

		ob_start();
		?>
		<span class="wpcode-setting-license-wrapper">
			<input type="password" id="wpcode-setting-license-key" value="<?php echo esc_attr( $key ); ?>" class="wpcode-input-text" <?php disabled( $is_valid_key ); ?>>
		</span>
		<button type="button" id="wpcode-setting-license-key-verify" class="wpcode-button <?php echo $is_valid_key ? 'wpcode-hide' : ''; ?>"><?php esc_html_e( 'Verify Key', 'wpcode-premium' ); ?></button>
		<button type="button" id="wpcode-setting-license-key-deactivate" class="wpcode-button <?php echo esc_attr( $hide ); ?>"><?php esc_html_e( 'Deactivate Key', 'wpcode-premium' ); ?></button>
		<button type="button" id="wpcode-setting-license-key-deactivate-force" class="wpcode-button wpcode-hide"><?php esc_html_e( 'Force Deactivate Key', 'wpcode-premium' ); ?></button>
		<p class="type <?php echo esc_attr( $hide ); ?>">
			<?php
			printf(
			/* translators: %s: the license type */
				esc_html__( 'Your license key level is %s.', 'wpcode-premium' ),
				'<strong>' . esc_html( $type ) . '</strong>'
			);
			?>
		</p>
		<p>
			<?php
			printf(
			/* translators: %1$s: opening link tag, %2$s: closing link tag */
				esc_html__( 'You can find your license key in your %1$sWPCode account%2$s.', 'wpcode-premium' ),
				'<a href="' . esc_url( $account_url ) . '" target="_blank">',
				'</a>'
			);
			?>
		</p>
		<?php if ( ! empty( $key ) ) : ?>
			<p class="wpcode-setting-license-key-refresh">
				<?php
				printf(
				/* translators: %1$s: opening link tag, %2$s: closing link tag */
					esc_html__( 'If your license has been upgraded or is incorrect, then please %1$sforce a refresh%2$s.', 'wpcode-premium' ),
					'<button class="wpcode-button-text"  id="wpcode-setting-license-key-refresh">',
					'</button>'
				);
				?>
			</p>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Output the form for the access management tab.
	 *
	 * @return void
	 */
	public function output_view_access() {

		$can_access = wpcode()->license->license_can( 'pro', is_multisite() && is_network_admin() );

		if ( ! $can_access ) {
			echo '<div class="wpcode-blur-area">';
		}

		$this->access_view_content();

		if ( ! $can_access ) {
			echo '</div>';
			echo $this->get_access_overlay();
		} else {
			// Nonce field.
			wp_nonce_field( 'wpcode_settings_access_save', 'wpcode_settings_access_nonce' );
		}
	}

	/**
	 * Process and Save access settings if any are set.
	 *
	 * @return void
	 */
	public function save_access_settings() {
		if ( ! isset( $_POST['wpcode_settings_access_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wpcode_settings_access_nonce'] ), 'wpcode_settings_access_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'wpcode_activate_snippets' ) ) {
			return;
		}

		$capabilities = array_keys( $this->get_capabilites() );

		foreach ( $capabilities as $capability ) {
			if ( isset( $_POST[ 'wpcode_capability_' . $capability ] ) ) {
				$roles = array_map( 'sanitize_key', $_POST[ 'wpcode_capability_' . $capability ] );
				wpcode()->settings->update_option( $capability, $roles );
			} else {
				wpcode()->settings->update_option( $capability, array() );
			}
		}

		if ( isset( $_POST['completely_disable_php'] ) ) {
			wpcode()->settings->update_option( 'completely_disable_php', true );
		} else {
			wpcode()->settings->update_option( 'completely_disable_php', false );
		}

		wp_safe_redirect( $this->get_page_action_url() );
		exit;
	}

	/**
	 * Drive the conditional rows on the settings screen.
	 *
	 * The shared handler lives in WPCodeSnippetManager, whose init() bails unless the snippet editor
	 * is present, so it never runs on a settings screen. Inlining a few lines here keeps the fix in
	 * includes/pro/ instead of the bundle shared with Lite.
	 *
	 * @return void
	 */
	public function enqueue_show_if_script() {
		if ( 'general' !== $this->view ) {
			return;
		}

		wp_add_inline_script(
			'wpcode-admin-js',
			'( function () {
				var rows = document.querySelectorAll( "[data-show-if-id]" );
				if ( ! rows.length ) { return; }
				function current( input ) {
					// A checkbox reports its value attribute whether or not it is checked, so
					// comparing .value would leave dependent rows visible while it is off.
					if ( "checkbox" === input.type || "radio" === input.type ) {
						return input.checked ? String( input.value || "1" ) : "";
					}
					return String( input.value );
				}
				function apply() {
					Array.prototype.forEach.call( rows, function ( row ) {
						var input = document.querySelector( row.getAttribute( "data-show-if-id" ) );
						if ( ! input ) { return; }
						var values = String( row.getAttribute( "data-show-if-value" ) ).split( "," );
						var hidden = -1 === values.indexOf( current( input ) );
						row.style.display = hidden ? "none" : "";
						// A hidden field the browser finds invalid blocks the whole form with no
						// message. Disabled fields skip validation and are not submitted, and the
						// save handler keeps the stored value for an absent field.
						Array.prototype.forEach.call( row.querySelectorAll( "input, select, textarea" ), function ( field ) {
							field.disabled = hidden;
						} );
					} );
				}
				document.addEventListener( "change", apply );
				apply();

				var data = window.wpcode && wpcode.revisions_cleanup;
				var box = document.getElementById( "wpcode-revisions-cleanup" );
				if ( ! data || ! box ) { return; }

				var busy = false;

				function num( n ) { return Number( n ).toLocaleString(); }

				function el( tag, cls, text ) {
					var node = document.createElement( tag );
					if ( cls ) { node.className = cls; }
					if ( text ) { node.textContent = text; }
					return node;
				}

				// Progress is a counting line, not a bar. The plugin has no progress-bar component,
				// and its only loading element is a centred overlay spinner meant for a whole panel.
				function showProgress( removed, total ) {
					box.innerHTML = "";
					box.appendChild( el( "p", "description", data.progress
						.replace( "%1$s", num( removed ) )
						.replace( "%2$s", num( total ) ) ) );
				}

				function showDone() {
					// The row turning into the all-clear state IS the confirmation, so there is no
					// separate success message to show and then remove.
					box.innerHTML = "";
					box.appendChild( el( "p", "description", data.clear_detail ) );
				}

				function showError( removed, total ) {
					box.innerHTML = "";
					box.appendChild( el( "p", "description", data.failed
						.replace( "%1$s", num( removed || 0 ) )
						.replace( "%2$s", num( total || 0 ) ) ) );

					var retry = el( "button", "wpcode-button wpcode-button-secondary", data.retry );
					retry.type = "button";
					retry.addEventListener( "click", function () { start(); } );
					box.appendChild( retry );
				}

				function step() {
					var body = new FormData();
					body.append( "action", "wpcode_revisions_cleanup_step" );
					body.append( "nonce", data.nonce );

					fetch( window.ajaxurl, { method: "POST", credentials: "same-origin", body: body } )
						.then( function ( r ) { return r.json(); } )
						.then( function ( res ) {
							if ( ! res || ! res.success ) { busy = false; showError( 0, 0 ); return; }
							showProgress( res.data.removed, res.data.total );
							if ( "running" === res.data.status ) {
								step();
								return;
							}
							busy = false;
							// Only a real "done" means the site is clean. "error", "idle" and
							// anything else must not render the all-clear.
							if ( "done" === res.data.status ) {
								showDone();
							} else {
								showError( res.data.removed, res.data.total );
							}
						} )
						.catch( function () { busy = false; showError( 0, 0 ); } );
				}

				function start() {
					if ( busy ) { return; }
					busy = true;
					showProgress( 0, 0 );
					step();
				}

				// A run left in flight by a closed tab or a navigation continues here, so returning
				// to the page shows real progress instead of a bar parked at zero.
				if ( data.running ) { start(); }

				box.addEventListener( "click", function ( e ) {
					var btn = e.target.closest( "#wpcode-revisions-cleanup-start" );
					if ( ! btn || busy ) { return; }
					e.preventDefault();
					jQuery.confirm( {
						title: data.title,
						content: data.body,
						type: "red",
						closeIcon: true,
						boxWidth: "480px",
						useBootstrap: false,
						buttons: {
							cancel: { text: data.cancel },
							// wpcode-btn-confirm is a class WPCode already owns: paired with type
							// "red" the plugin styles it in the WP danger red, so we add no rule.
							confirm: { text: data.confirm, btnClass: "wpcode-btn-confirm", action: function () { start(); } }
						}
					} );
				} );
			} )();'
		);
	}

	/**
	 * Show the PHP setting if PHP is not disabled.
	 *
	 * @return void
	 */
	public function php_setting() {
		if ( ! defined( 'WPCODE_DISABLE_PHP' ) || ! WPCODE_DISABLE_PHP ) {
			parent::php_setting();
		}
	}

	/**
	 * Get the capabilities for the access settings page.
	 *
	 * @return array[]
	 */
	public function get_capabilites() {
		$capabilities = WPCode_Access::capabilities();
		if ( WPCode_Access::php_disabled() ) {
			// If PHP is disabled, don't show the capability to edit php setting.
			unset( $capabilities['wpcode_edit_php_snippets'] );
		}

		return $capabilities;
	}

	/**
	 * Access control overlay.
	 *
	 * @return string
	 */
	public function get_access_overlay() {
		$text = sprintf(
		// translators: %1$s and %2$s are <u> tags.
			'<p>' . __( 'Improve the way you and your team manage your snippets with the WPCode Access Control settings. Enable other users on your site to manage different types of snippets or configure Conversion Pixels settings and update configuration files. This feature is available on the %1$sWPCode Pro%2$s plan or higher.', 'wpcode-premium' ) . '</p>',
			'<u>',
			'</u>'
		);

		return self::get_upsell_box(
			__( 'Access Control is not available on your plan', 'wpcode-premium' ),
			$text,
			array(
				'text' => __( 'Upgrade Now', 'wpcode-premium' ),
				'url'  => wpcode_utm_url( 'https://library.wpcode.com/account/downloads/', 'settings', 'tab-' . $this->view, 'upgrade-to-pro' ),
			),
			array(),
			array(
				__( 'Save time and improve website management with your team', 'wpcode-premium' ),
				__( 'Delegate snippet management to other users with full control', 'wpcode-premium' ),
				__( 'Enable other users to set up ads & 3rd party services', 'wpcode-premium' ),
				__( 'Choose if PHP snippets should be enabled on the site', 'wpcode-premium' ),
			)
		);
	}

	/**
	 * Add license strings to the JS object for the Pro settings page.
	 *
	 * @param string[] $data The translation strings.
	 *
	 * @return string[]
	 */
	public function add_js_data( $data ) {
		$data['license_error_title'] = __( 'We encountered an error activating your license key', 'wpcode-premium' );
		$data['multisite']           = is_network_admin();

		// Withheld from anyone the endpoint will refuse: it carries the cleanup nonce.
		if ( 'general' === $this->view && wpcode()->revisions_retention->is_enabled() && current_user_can( 'wpcode_manage_settings' ) ) {
			$data['revisions_cleanup'] = $this->get_cleanup_js_data();
		}

		return $data;
	}

	/**
	 * Everything the cleanup UI needs, resolved server side.
	 *
	 * The counts and the limit are sent for display only. The endpoint re-derives both from the
	 * saved settings, so nothing here can talk it into deleting more than the user agreed to.
	 *
	 * Plural forms are sent as pairs because the browser substitutes the numbers at runtime and
	 * cannot choose a form itself; _n() here would bake in whichever form the page load happened
	 * to need.
	 *
	 * @return array
	 */
	protected function get_cleanup_js_data() {
		$retention = wpcode()->revisions_retention;
		$state     = $retention->resumable_cleanup_state();
		$over      = $retention->count_over_limit();
		$orphans   = $retention->count_orphans();
		$keep      = $retention->get_keep_count();
		$total     = $over['revisions'] + $orphans;

		$body = '<p>' . sprintf(
			/* translators: %s: the saved limit. */
			esc_html__( 'This applies your saved limit of %s to revisions that already exist. It will permanently delete:', 'wpcode-premium' ),
			'<strong>' . esc_html( number_format_i18n( $keep ) ) . '</strong>'
		) . '</p>';

		$body .= '<ul>';
		$body .= '<li>' . sprintf(
			/* translators: %1$s: revisions over the limit, %2$s: number of items. */
			esc_html( _n( '%1$s revision across %2$s that is over the limit', '%1$s revisions across %2$s that are over the limit', $over['revisions'], 'wpcode-premium' ) ),
			'<strong>' . esc_html( number_format_i18n( $over['revisions'] ) ) . '</strong>',
			'<strong>' . esc_html(
				sprintf(
					/* translators: %s: number of items. */
					_n( '%s item', '%s items', $over['items'], 'wpcode-premium' ),
					number_format_i18n( $over['items'] )
				)
			) . '</strong>'
		) . '</li>';
		// No bullet at all when there are no orphans: a zero here reads as a second problem.
		if ( $orphans > 0 ) {
			$body .= '<li>' . sprintf(
				/* translators: %s: revisions belonging to deleted items. */
				esc_html( _n( '%s revision belonging to a snippet or page that was already deleted', '%s revisions belonging to snippets and pages that were already deleted', $orphans, 'wpcode-premium' ) ),
				'<strong>' . esc_html( number_format_i18n( $orphans ) ) . '</strong>'
			) . '</li>';
		}
		$body .= '</ul>';

		$body .= '<p>' . sprintf(
			/* translators: %s: the saved limit. */
			esc_html__( 'Included are your snippets, any page carrying page scripts, and your Header & Footer code. The most recent %s revisions of each are kept.', 'wpcode-premium' ),
			esc_html( number_format_i18n( $keep ) )
		) . '</p>';
		$body .= '<p><strong>' . esc_html__( 'There is no undo and no revision export.', 'wpcode-premium' ) . '</strong></p>';

		return array(
			'running'      => ! empty( $state ),
			// Keyed to the limit, so a tab still showing an older limit cannot run against the
			// current one. The dialog's numbers are rendered here; a run that would apply a
			// different limit than the one confirmed must be refused, not silently honoured.
			'nonce'        => wp_create_nonce( 'wpcode_revisions_cleanup_' . $keep ),
			'title'        => __( 'Delete older revisions?', 'wpcode-premium' ),
			'body'         => $body,
			'confirm'      => sprintf(
				/* translators: %s: total revisions that will be deleted. */
				_n( 'Delete %s revision', 'Delete %s revisions', $total, 'wpcode-premium' ),
				number_format_i18n( $total )
			),
			'cancel'       => __( 'Cancel', 'wpcode-premium' ),
			/* translators: %1$s: revisions removed so far, %2$s: total to remove. */
			'progress'     => __( 'Removing revisions, %1$s of %2$s done.', 'wpcode-premium' ),
			'clear_detail' => __( 'Nothing is over your limit.', 'wpcode-premium' ),
			/* translators: %1$s: revisions removed before it stopped, %2$s: total it set out to remove. */
			'failed'       => __( 'Stopped after removing %1$s of %2$s revisions.', 'wpcode-premium' ),
			'retry'        => __( 'Try again', 'wpcode-premium' ),
		);
	}

	/**
	 * Output the form for the error notifications tab.
	 *
	 * @return void
	 */
	public function error_view_fields() {
		$this->error_logging_field();
		?>
		<h2><?php esc_html_e( 'Email Notifications', 'wpcode-premium' ); ?></h2>
		<p>
			<?php esc_html_e( 'Receive email notifications when snippets throw errors or are automatically deactivated.', 'wpcode-premium' ); ?>
		</p>
		<?php $this->wp_mail_smtp_notice(); ?>
		<hr/>
		<?php
		$this->error_emails_fields();
	}

	/**
	 * Process settings before they are saved in the context of this child class.
	 * In other words, process pro-specific settings while keeping the save logic in the main class that is extended.
	 *
	 * @param array $settings The settings to be saved.
	 *
	 * @return array
	 */
	public function before_save( $settings ) {
		if ( ! isset( $_REQUEST[ $this->nonce_name ] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST[ $this->nonce_name ] ), $this->action ) ) {
			// Nonce is missing, so we're not even going to try.
			return $settings;
		}

		if ( 'errors' === $this->view ) {
			$emails_errors_addresses = isset( $_POST['wpcode-emails-errors-addresses'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcode-emails-errors-addresses'] ) ) : '';
			if ( ! empty( $emails_errors_addresses ) ) {
				$emails_errors_addresses = $this->clean_emails( $emails_errors_addresses );
			}
			$emails_deactivated_addresses = isset( $_POST['wpcode-emails-deactivated-addresses'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcode-emails-deactivated-addresses'] ) ) : '';
			if ( ! empty( $emails_deactivated_addresses ) ) {
				$emails_deactivated_addresses = $this->clean_emails( $emails_deactivated_addresses );
			}

			$settings['emails_errors']                = isset( $_POST['wpcode-emails-errors'] );
			$settings['emails_errors_addresses']      = $emails_errors_addresses;
			$settings['emails_deactivated']           = isset( $_POST['wpcode-emails-deactivated'] );
			$settings['emails_deactivated_addresses'] = $emails_deactivated_addresses;
		}

		// Handle PHP load as file setting.
		if ( 'general' === $this->view ) {
			$old_value = wpcode()->settings->get_option( 'php_load_as_file', false );
			$new_value = isset( $_POST['wpcode-php-load-as-file'] );

			$settings['php_load_as_file'] = $new_value;

			// The network Settings page renders neither revisions row, because output_view_general()
			// returns after the license key under is_multisite_core(). Writing these keys from there
			// would persist a limit nobody was shown, so the whole block is skipped.
			if ( ! $this->is_multisite_core() ) {
				$settings['revisions_removal_enabled'] = isset( $_POST['wpcode-revisions-removal-enabled'] );

				// A blank, absent or non-numeric field keeps whatever is already stored: failing
				// safe on a retention limit means keeping more revisions, never fewer. A number
				// below the minimum is raised to it.
				$submitted_count = isset( $_POST['wpcode-revisions-keep-count'] )
					? trim( sanitize_text_field( wp_unslash( $_POST['wpcode-revisions-keep-count'] ) ) )
					: '';

				$settings['revisions_keep_count'] = ( '' === $submitted_count || ! ctype_digit( $submitted_count ) )
					? wpcode()->revisions_retention->get_keep_count()
					: max( WPCode_Revisions_Retention::MIN_KEEP_COUNT, min( WPCode_Revisions_Retention::MAX_KEEP_COUNT, (int) $submitted_count ) );

				// Any change to either setting abandons a half-finished run. It was confirmed
				// against the old values, and resuming skips the dialog.
				if ( wpcode()->revisions_retention->is_enabled() !== $settings['revisions_removal_enabled']
					|| wpcode()->revisions_retention->get_keep_count() !== $settings['revisions_keep_count'] ) {
					wpcode()->revisions_retention->clear_cleanup_state();
				}
			}

			// If the setting changed, trigger bulk operations.
			if ( $old_value !== $new_value && class_exists( 'WPCode_Snippets_File_Handler' ) ) {
				if ( $new_value ) {
					// Setting enabled: generate files for all active PHP snippets.
					WPCode_Snippets_File_Handler::bulk_generate_php_files();
				} else {
					// Setting disabled: delete all PHP snippet files.
					WPCode_Snippets_File_Handler::bulk_delete_php_files();
				}
			}
		}

		return $settings;
	}

	/**
	 * Output the revision removal rows.
	 *
	 * Off by default. The count row is conditional on the toggle, driven by the inline script in
	 * enqueue_show_if_script() rather than the snippet-editor handler, which never runs here.
	 *
	 * @return void
	 */
	public function revisions_removal_setting() {
		$this->metabox_row(
			__( 'Remove Revisions', 'wpcode-premium' ),
			$this->get_checkbox_toggle(
				wpcode()->revisions_retention->is_enabled(),
				'wpcode-revisions-removal-enabled',
				esc_html__( 'Keep only the most recent revisions of each snippet, page and your Header & Footer code.', 'wpcode-premium' ),
				'1'
			),
			'wpcode-revisions-removal-enabled'
		);

		$this->metabox_row(
			__( 'Revisions to Keep', 'wpcode-premium' ),
			sprintf(
				'<input type="number" min="%1$d" max="%2$d" step="1" id="wpcode-revisions-keep-count" name="wpcode-revisions-keep-count" value="%3$d" />',
				WPCode_Revisions_Retention::MIN_KEEP_COUNT,
				WPCode_Revisions_Retention::MAX_KEEP_COUNT,
				absint( wpcode()->revisions_retention->get_keep_count() )
			),
			'wpcode-revisions-keep-count',
			'#wpcode-revisions-removal-enabled',
			'1',
			esc_html(
				sprintf(
					/* translators: %s: the lowest limit allowed. */
					__( 'Older revisions are removed the next time a change to an item is saved. The minimum is %s. This is permanent and cannot be undone.', 'wpcode-premium' ),
					number_format_i18n( WPCode_Revisions_Retention::MIN_KEEP_COUNT )
				)
			),
			false,
			'wpcode-revisions-keep-count-row'
		);

		$this->revisions_cleanup_row();
	}

	/**
	 * Output the one-off cleanup row for revisions that are already over the limit.
	 *
	 * The limit alone only trims an item when it next gets a revision, so without this an item
	 * nobody edits again stays over the limit forever.
	 *
	 * Built only from what the plugin already has: a label, a description and, where there is work
	 * to do, the same secondary button "Delete log" uses in WPCode -> Tools. No badge and no
	 * progress bar, because no settings row in the plugin carries either, and the danger colour
	 * belongs in the confirmation dialog rather than on the trigger.
	 *
	 * @return void
	 */
	public function revisions_cleanup_row() {
		$retention = wpcode()->revisions_retention;

		if ( ! $retention->is_enabled() ) {
			return;
		}

		$state   = $retention->resumable_cleanup_state();
		$running = ! empty( $state );
		$removed = isset( $state['removed'] ) ? absint( $state['removed'] ) : 0;

		$over    = $retention->count_over_limit();
		$orphans = $retention->count_orphans();
		$pending = $over['items'] > 0 || $orphans > 0;
		$keep    = $retention->get_keep_count();

		// Only offer the control to someone the endpoint will serve.
		$may_clean = current_user_can( 'wpcode_manage_settings' );

		ob_start();

		if ( $running ) {
			$total = max( 1, isset( $state['total'] ) ? absint( $state['total'] ) : 1 );
			?>
			<p class="description" id="wpcode-revisions-cleanup-progress">
				<?php
				printf(
					/* translators: %1$s: revisions removed so far, %2$s: total revisions to remove. */
					esc_html__( 'Removing revisions, %1$s of %2$s done.', 'wpcode-premium' ),
					'<strong>' . esc_html( number_format_i18n( $removed ) ) . '</strong>',
					'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>'
				);
				?>
			</p>
			<?php
		} elseif ( $pending ) {
			// One number, and it is the same total the confirmation button carries. The breakdown
			// between over-limit and already-deleted rows belongs in the dialog, where the user is
			// consenting to it, not in the prompt. The permanence warning sits on the row above.
			$total = $over['revisions'] + $orphans;
			?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: number of revisions that can be removed. */
					esc_html( _n( '%s revision can be removed.', '%s revisions can be removed.', $total, 'wpcode-premium' ) ),
					'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>'
				);
				?>
			</p>
			<?php if ( $may_clean ) { ?>
				<button type="button" class="wpcode-button wpcode-button-secondary" id="wpcode-revisions-cleanup-start">
					<?php esc_html_e( 'Delete older revisions', 'wpcode-premium' ); ?>
				</button>
			<?php } ?>
			<?php
		} else {
			?>
			<p class="description"><?php esc_html_e( 'Nothing is over your limit.', 'wpcode-premium' ); ?></p>
			<?php
		}

		$markup = ob_get_clean();

		$this->metabox_row(
			__( 'Existing Revisions', 'wpcode-premium' ),
			'<div id="wpcode-revisions-cleanup">' . $markup . '</div>',
			'',
			'#wpcode-revisions-removal-enabled',
			'1',
			'',
			false,
			'wpcode-revisions-cleanup-row'
		);
	}

	/**
	 * Styles for the confirmation dialog.
	 *
	 * The cleanup row itself needs no CSS: it is a description and a button the plugin already
	 * styles. These rules exist only because build/admin.css centres and inflates every paragraph
	 * inside a jconfirm content pane (`.jconfirm-content-pane p`), and a rule on the container
	 * cannot beat a direct rule on the paragraph.
	 *
	 * They hang off .jconfirm-type-red rather than a class of our own, because the bundled
	 * jquery-confirm has no boxClass option, so a custom class never reaches the DOM. These styles
	 * load only on Settings > General, so the wider scope costs nothing. The destructive button
	 * needs no rule at all: WPCode already styles .jconfirm-type-red button.wpcode-btn-confirm.
	 *
	 * @return void
	 */
	public function enqueue_cleanup_styles() {
		if ( 'general' !== $this->view || ! wpcode()->revisions_retention->is_enabled() ) {
			return;
		}

		wp_add_inline_style(
			'wpcode-admin-css',
			'body div.jconfirm .jconfirm-box-container .jconfirm-box.jconfirm-type-red div.jconfirm-title-c{text-align:left;font-size:20px;line-height:1.3}
			body div.jconfirm .jconfirm-box-container .jconfirm-box.jconfirm-type-red div.jconfirm-content-pane p{text-align:left;font-size:14px;line-height:1.6;margin:0 0 12px}
			body div.jconfirm .jconfirm-box-container .jconfirm-box.jconfirm-type-red div.jconfirm-content-pane ul{list-style:disc;margin:0 0 12px;padding-left:20px;text-align:left}
			body div.jconfirm .jconfirm-box-container .jconfirm-box.jconfirm-type-red div.jconfirm-content-pane ul li{display:list-item;margin:0 0 5px;font-size:14px;line-height:1.6;text-align:left}
			body div.jconfirm .jconfirm-box-container .jconfirm-box.jconfirm-type-red .jconfirm-buttons{text-align:right}'
		);
	}

	/**
	 * Output the PHP load as file setting field.
	 *
	 * @return void
	 */
	public function php_load_as_file_setting() {
		$php_load_as_file = wpcode()->settings->get_option( 'php_load_as_file', false );
		$description      = esc_html__( 'When enabled, all active PHP, HTML, and Universal snippets will be loaded from files instead of the database. Files are stored in ', 'wpcode-premium' );

		// Show site-specific path in multisite.
		if ( function_exists( 'is_multisite' ) && is_multisite() && function_exists( 'get_current_blog_id' ) ) {
			$blog_id = get_current_blog_id();
			if ( $blog_id > 0 ) {
				$file_path = str_replace( ABSPATH, '', WP_CONTENT_DIR . '/wpcode/snippets/site-' . absint( $blog_id ) . '/' );
			} else {
				$file_path = str_replace( ABSPATH, '', WP_CONTENT_DIR . '/wpcode/snippets/' );
			}
		} else {
			$file_path = str_replace( ABSPATH, '', WP_CONTENT_DIR . '/wpcode/snippets/' );
		}

		$description .= '<code>' . esc_html( $file_path ) . '</code>';

		if ( is_multisite() ) {
			$description .= ' <strong>' . esc_html__( 'Note: In multisite networks, each site has its own file directory to prevent cross-site conflicts.', 'wpcode-premium' ) . '</strong>';
		}

		$this->metabox_row(
			__( 'Load PHP Snippets as Files', 'wpcode-premium' ),
			$this->get_checkbox_toggle(
				$php_load_as_file,
				'wpcode-php-load-as-file',
				$description
			),
			'wpcode-php-load-as-file'
		);
	}

	/**
	 * Takes a comma separated list of emails and sanitizes each one.
	 *
	 * @param string $emails The comma separated list of emails.
	 *
	 * @return string
	 */
	public function clean_emails( $emails ) {
		$emails = explode( ',', $emails );
		$emails = array_map( 'sanitize_email', $emails );

		return implode( ',', $emails );
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
}
