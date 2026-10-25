<?php

declare(strict_types=1);

namespace BuddyBossTheme\Admin\Mothership;

use BuddyBossTheme\GroundLevel\Container\Container;
use BuddyBossTheme\GroundLevel\Mothership\MothershipServiceProvider;
use BuddyBossTheme\GroundLevel\Mothership\AbstractPluginConnection;
use BuddyBossTheme\GroundLevel\Mothership\Manager\LicenseManager;
use BuddyBossTheme\GroundLevel\InProductNotifications\IPNServiceProvider;

/**
 * Main loader class for BuddyBoss Theme Mothership functionality.
 *
 * Follows the GroundLevel 9.1.2 dependency-injection ServiceProvider pattern for service
 * registration and hook configuration.
 */
class BB_Theme_Mothership_Loader {

	/**
	 * Singleton instance.
	 *
	 * @var BB_Theme_Mothership_Loader|null
	 */
	private static $instance = null;

	/**
	 * Container for dependency injection.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Plugin connector instance.
	 *
	 * @var \BuddyBossTheme\Admin\Mothership\BB_Theme_Connector
	 */
	private $pluginConnector; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * Get singleton instance.
	 *
	 * @return BB_Theme_Mothership_Loader
	 */
	public static function instance(): BB_Theme_Mothership_Loader {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor (private to enforce singleton).
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the mothership functionality.
	 */
	private function init(): void {
		// The vendor tree is gitignored and is NOT refreshed by a branch switch, so a
		// checkout without `composer install` leaves an older GroundLevel on disk while this
		// code targets 9.1.2. Container::singleton() and the ServiceProvider classes below
		// are 9.x-only, and mothership-init.php runs unconditionally, so a mismatch would
		// fatal rather than degrade. Mirrors buddyboss-platform.
		if ( ! class_exists( MothershipServiceProvider::class ) || ! method_exists( Container::class, 'singleton' ) ) {
			self::log( 'BuddyBoss Theme: the GroundLevel vendor tree is out of date (run `composer install`) — Mothership licensing is disabled for this request.' );

			return;
		}

		// Move the theme license to or from the network to match the current multisite mode,
		// before the connector reads its plugin ID.
		BB_Theme_Connector::sync_license_scope();

		try {
			// Create the container.
			$this->container = new Container();

			// Create the plugin connector.
			$this->pluginConnector = new \BuddyBossTheme\Admin\Mothership\BB_Theme_Connector(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Register the connection so every GroundLevel service can resolve it.
			$plugin_connector = $this->pluginConnector; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$this->container->singleton(
				AbstractPluginConnection::class,
				static function () use ( $plugin_connector ) {
					return $plugin_connector;
				}
			);
		} catch ( \Throwable $e ) {
			self::log( 'BuddyBoss Theme: Mothership container setup failed: ' . $e->getMessage() );

			return;
		}

		// Register and boot the Mothership + In-Product Notifications service providers.
		$this->register_services();

		// Set up hooks.
		$this->setup_hooks();
	}

	/**
	 * Register and boot the GroundLevel service providers.
	 *
	 * @since 2.22.2
	 */
	private function register_services(): void {
		$plugin_id = $this->pluginConnector->getDynamicPluginId(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// IPN parameter overrides MUST be set before provider() — ServiceProvider::register()
		// only registers a default when the container does not already have the key.
		// Seed the vendor's API base URL from the theme's own resolver so BuddyBoss requests
		// and every GroundLevel request share one endpoint. Must precede provider(), since
		// ServiceProvider::register() only supplies a default when the key is absent.
		// Mirrors buddyboss-platform.
		$api_base_url = BB_Theme_License_Manager::get_api_base_url( $plugin_id );

		$this->container->parameters(
			array(
				MothershipServiceProvider::PARAM_API_BASE_URL => $api_base_url,
				IPNServiceProvider::PARAM_PRODUCT_SLUG => $plugin_id,
				IPNServiceProvider::PARAM_PREFIX       => sanitize_title( $plugin_id ),
				IPNServiceProvider::PARAM_MENU_SLUG    => 'buddyboss-settings',
				IPNServiceProvider::PARAM_RENDER_HOOK  => 'buddyboss_theme_admin_header_actions',
				IPNServiceProvider::PARAM_THEME        => array(
					'primaryColor'       => '#2f2f2f',
					'primaryColorDarker' => '#0a4b78',
					'inboxBtnIcon'       => 'bell',
					'inboxBtnVariant'    => 'icon',
					'inboxBtnSize'       => '1.8rem',
				),
			)
		);

		// Must precede boot(): the vendor LicenseManager schedules its status cron in its constructor.
		$this->limit_license_cron_to_main_site( $plugin_id );

		try {
			$this->container->provider( MothershipServiceProvider::class );
			$this->container->provider( IPNServiceProvider::class );

			// Services stay resolvable from the container either way; only the vendor hooks
			// are skipped where they have nothing to do (see should_boot_services()).
			if ( ! self::should_boot_services() ) {
				return;
			}

			// Boot registers the vendor WordPress hooks.
			$this->container->boot();

			$this->disable_overwrite_guard_for_per_site_licenses();
		} catch ( \Throwable $e ) {
			// A resolution/boot failure must never white-screen wp-admin. Log and degrade.
			self::log( 'BuddyBoss Theme Mothership bootstrap failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Logs a Mothership boot failure.
	 *
	 * Every caller is a failure path that leaves theme licensing disabled for the request,
	 * so the message is logged regardless of WP_DEBUG, matching buddyboss-platform.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $message The message to log.
	 */
	private static function log( string $message ): void {
		error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Whether the vendor service hooks are needed on this request.
	 *
	 * Booting the GroundLevel providers hooks `site_transient_update_themes` /
	 * `site_transient_update_plugins` (the vendor add-on update injection), which fetch the
	 * add-ons list from the licensing API on a cold cache. The theme reads the
	 * `update_themes` transient on every request, the front end included, so an anonymous
	 * page load could block on that remote call. The vendor hooks only serve wp-admin,
	 * admin AJAX, cron, WP-CLI and REST.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @return bool
	 */
	private static function should_boot_services(): bool {
		// ALTERNATE_WP_CRON runs cron inside an ordinary front-end request, after plugins have
		// loaded, so wp_doing_cron() is still false here while the vendor cron callbacks
		// (license status check, notification fetches) will be needed later in the request.
		// Such sites therefore keep the pre-gate behaviour of booting on every request.
		if ( is_admin() || wp_doing_cron() || wp_doing_ajax() || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) ) {
			$should_boot = true;
		} else {
			// REST_REQUEST is only defined once the request is parsed, long after this runs, so
			// detect both the pretty (`/wp-json/`) and the plain-permalink (`?rest_route=`) form.
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$should_boot = ! empty( $_GET['rest_route'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only request routing check.
				|| ( '' !== $request_uri && false !== strpos( $request_uri, '/' . rest_get_url_prefix() . '/' ) );
		}

		/**
		 * Filters whether the theme's GroundLevel Mothership services are booted on this request.
		 *
		 * @since BuddyBoss Theme 2.22.2
		 *
		 * @param bool $should_boot Whether to boot the services.
		 */
		return (bool) apply_filters( 'buddyboss_theme_mothership_boot_services', $should_boot );
	}

	/**
	 * Remove GroundLevel's license-key overwrite guard when licenses are held per site.
	 *
	 * The vendor guard compares the license key against the `{pluginId}_activation` SITE
	 * transient, which is shared by every site of the network that uses the same edition,
	 * while each site keeps its own key outside network mode. Sites with different keys
	 * would then deactivate each other, so the guard is removed in that mode only.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 */
	private function disable_overwrite_guard_for_per_site_licenses(): void {
		if ( ! is_multisite() || BB_Theme_Connector::is_network_mode() ) {
			return;
		}

		remove_action( 'admin_init', array( $this->container->get( LicenseManager::class ), 'onLicenseKeyOverwritten' ) );
	}

	/**
	 * Setup WordPress hooks.
	 */
	private function setup_hooks(): void {
		if ( is_admin() ) {
			// Register admin pages. In network mode the network owns the theme license, so the
			// page lives in Network Admin only.
			add_action( BB_Theme_Connector::is_network_mode() ? 'network_admin_menu' : 'admin_menu', array( $this, 'register_admin_pages' ), 99 );

			// Links built for the other admin (e.g. `network_admin_url()` on a per-site network)
			// would otherwise hit "Sorry, you are not allowed".
			add_action( 'admin_page_access_denied', array( $this, 'redirect_misrouted_license_page' ) );

			// Register license controller using BuddyBoss Theme custom manager.
			add_action( 'admin_init', array( \BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::class, 'controller' ), 20 );

			// Register AJAX handlers.
			add_action( 'wp_ajax_bb_reset_license_settings', array( \BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::class, 'ajax_reset_license_settings' ) );

			// ...and refuse to persist changes made there while it is not covered.
			//
			// PHP_INT_MAX so this runs LAST and its return value wins: a filter added at any
			// ordinary priority cannot re-permit the save by returning the new value after
			// us. Re-asserted on admin_init (below) so removing the hook early does not
			// stick.
			add_filter( 'pre_update_option_buddyboss_theme_options', array( $this, 'block_uncovered_theme_options_save' ), PHP_INT_MAX, 2 );
			add_action( 'admin_init', array( __CLASS__, 'assert_save_block' ), PHP_INT_MAX );

			// Correct the In-Product Notifications inbox asset URL (see fix_ipn_asset_url()).
			add_filter( 'script_loader_src', array( $this, 'fix_ipn_asset_url' ), 10, 1 );
		}

		$plugin_id = $this->pluginConnector->getDynamicPluginId(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Handle license status changes. GroundLevel 9.1.2's periodic license check fires
		// `{plugin_id}_active_license_invalidated` / `_active_license_expired` when the license
		// is revoked or expired. The legacy `_license_status_changed` event is kept for
		// backward compatibility with custom callers.
		add_action( $plugin_id . '_active_license_invalidated', array( $this, 'handle_license_revoked' ) );
		add_action( $plugin_id . '_active_license_expired', array( $this, 'handle_license_revoked' ) );
		add_action( $plugin_id . '_license_status_changed', array( $this, 'handle_license_status_change' ), 10, 2 );

		// For local development - disable SSL verification if needed.
		// The network-mode answer depends on the main site's theme, Platform's activation and the
		// network-enabled themes; forget it when any of those changes mid-request.
		foreach ( array( 'switch_theme', 'activated_plugin', 'deactivated_plugin', 'update_site_option_allowedthemes' ) as $mode_hook ) {
			add_action( $mode_hook, array( BB_Theme_Connector::class, 'reset_network_mode' ) );
		}

		if ( defined( 'BUDDYBOSS_DISABLE_SSL_VERIFY' ) && constant( 'BUDDYBOSS_DISABLE_SSL_VERIFY' ) ) {
			add_filter( 'https_ssl_verify', '__return_false' );
		}

		add_action( 'in_admin_header', array( $this, 'bb_render_admin_header' ), 999 );
	}

	/**
	 * Register admin pages.
	 */
	public function register_admin_pages(): void {
		$capability = BB_Theme_Connector::license_capability();

		if ( ! current_user_can( $capability ) ) {
			return;
		}

		// Register License page — under Themes in Network Admin, where the theme has no menu.
		\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Page::register( is_network_admin() ? 'themes.php' : '', $capability );
	}

	/**
	 * Keep the license status cron on the main site only in network mode.
	 *
	 * The vendor LicenseManager schedules a twice-daily status check on every site that boots
	 * it. In network mode every site shares one license, so extra runs are duplicate API calls
	 * that could each revoke it. Blocks scheduling on other sites and clears any existing event.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $plugin_id The dynamic plugin ID the vendor names the cron hook after.
	 */
	private function limit_license_cron_to_main_site( string $plugin_id ): void {
		if ( ! BB_Theme_Connector::is_network_mode() || is_main_site() ) {
			return;
		}

		$cron_hook = $plugin_id . '_check_license_activation_status_event';

		add_filter(
			'pre_schedule_event',
			static function ( $pre, $event ) use ( $cron_hook ) {
				return ( is_object( $event ) && isset( $event->hook ) && $cron_hook === $event->hook ) ? false : $pre;
			},
			10,
			2
		);

		if ( wp_next_scheduled( $cron_hook ) ) {
			wp_clear_scheduled_hook( $cron_hook );
		}
	}

	/**
	 * Redirect a License page request made in the admin that does not host it.
	 *
	 * In network mode the page exists only in Network Admin, so a site link is sent there for
	 * users who can manage the network license. Otherwise it exists only on sites, so a Network
	 * Admin link (the theme builds these with `network_admin_url()` on any multisite) is sent
	 * back to the site the user came from, or the main site.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 */
	public function redirect_misrouted_license_page(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing of a page slug.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( BB_Theme_License_Page::SLUG !== $page || ! is_multisite() ) {
			return;
		}

		$target = '';

		if ( BB_Theme_Connector::is_network_mode() ) {
			if ( ! is_network_admin() && current_user_can( 'manage_network_options' ) ) {
				$target = network_admin_url( 'admin.php?page=' . $page );
			}
		} elseif ( is_network_admin() ) {
			$referer = (string) wp_get_referer();

			if ( '' !== $referer && false !== strpos( $referer, '/wp-admin/' ) && false === strpos( $referer, '/wp-admin/network/' ) ) {
				$target = (string) preg_replace( '#/wp-admin/.*$#', '/wp-admin/admin.php?page=' . $page, $referer );
			} else {
				$target = get_admin_url( get_main_site_id(), 'admin.php?page=' . $page );
			}
		}

		if ( '' !== $target ) {
			wp_safe_redirect( $target );
			exit;
		}
	}

	/**
	 * Handle a license revocation/expiry reported by GroundLevel's periodic check.
	 *
	 * @since 2.22.2
	 */
	public function handle_license_revoked(): void {
		$this->handle_license_status_change( false, null );
	}

	/**
	 * Handle license status changes.
	 *
	 * @param bool  $is_active License active status.
	 * @param mixed $response  API response.
	 */
	public function handle_license_status_change( bool $is_active, $response ): void {
		$plugin_id = $this->pluginConnector->getDynamicPluginId(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( ! $is_active ) {
			// License is no longer active.
			$this->pluginConnector->updateLicenseActivationStatus( false ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Clear cached add-ons data (GroundLevel 9.1.2 cache key).
			delete_transient( $plugin_id . '-mosh-addons' );
			delete_site_transient( $plugin_id . '-mosh-addons' );

			// Log the deactivation.
			error_log( 'BuddyBoss Theme license deactivated' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			// License is active - ensure status is updated.
			$this->pluginConnector->updateLicenseActivationStatus( true ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
	}

	/**
	 * Get the container.
	 *
	 * @return Container The container instance.
	 */
	public function get_container(): Container {
		// init() bails before building the container when the vendor tree is stale, so this
		// can be reached with nothing set. The declared return type forbids null, so hand
		// back an empty container: an unresolved service then throws a catchable container
		// exception instead of a TypeError.
		if ( ! $this->container instanceof Container ) {
			$this->container = new Container();
		}

		return $this->container;
	}

	/**
	 * Get the container (backward-compatibility wrapper).
	 *
	 * @deprecated Use get_container() instead.
	 *
	 * @return Container The container instance.
	 */
	public function getContainer(): Container { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		return $this->get_container();
	}

	/**
	 * Correct the In-Product Notifications inbox script URL.
	 *
	 * The GroundLevel IPN service enqueues its bundle with plugin_dir_url(), which assumes the
	 * package lives under wp-content/plugins. Inside the theme the vendor is under
	 * wp-content/themes, so plugin_dir_url() produces an invalid URL and the inbox app fails to
	 * load. Rebuild the URL relative to the theme directory.
	 *
	 * @since 2.22.2
	 *
	 * @param string|false $src The script source URL, or false for a dependency-only handle.
	 * @return string|false The corrected URL, or the value unchanged.
	 */
	public function fix_ipn_asset_url( $src ) {
		// `script_loader_src` also runs for handles registered with no src (dependency-only
		// bundles), where $src is false. Passing that to strpos() is a fatal on PHP 8, and
		// anything walking every registered handle — Query Monitor's asset collector, for
		// one — will hit it. The return type is deliberately untyped so a false src passes
		// through unchanged rather than being coerced to an empty string, which would blank
		// out other plugins' handles.
		if ( ! is_string( $src ) || '' === $src ) {
			return $src;
		}

		$marker = 'vendor/caseproof/ground-level-in-product-notifications/';
		$pos    = strpos( $src, $marker );
		if ( false !== $pos ) {
			$src = trailingslashit( get_template_directory_uri() ) . substr( $src, $pos );
		}
		return $src;
	}


	/**
	 * (Re)installs the Theme Options save block at the last possible priority.
	 *
	 * Idempotent, and safe to call repeatedly: has_filter() keeps it from stacking. Called
	 * once when hooks are set up and again late on admin_init, so a `remove_filter()` that
	 * ran in between is undone before any save is processed.
	 *
	 * This raises the cost of tampering; it does not make the check unbypassable. Any code
	 * executing on the site can ultimately defeat a PHP-level licence check — the honest
	 * guarantee here is that an ordinary hook cannot quietly override it.
	 *
	 * @since 2.22.2
	 */
	public static function assert_save_block(): void {
		// Never call instance() from here during construction: setup_hooks() runs inside
		// init(), which runs inside instance(), so re-entering it would recurse. This only
		// ever runs on admin_init, by which point the singleton exists.
		if ( ! ( self::$instance instanceof self ) ) {
			return;
		}

		$callback = array( self::$instance, 'block_uncovered_theme_options_save' );

		if ( ! has_filter( 'pre_update_option_buddyboss_theme_options', $callback ) ) {
			add_filter( 'pre_update_option_buddyboss_theme_options', $callback, PHP_INT_MAX, 2 );
		}
	}

	/**
	 * Refuses to save Theme Options while the licence's plan does not include the theme.
	 *
	 * Returning the old value makes update_option() a no-op, so the settings screen stays
	 * readable but changes do not persist. The accompanying notice from
	 * {@see self::render_plan_coverage_notice()} explains why.
	 *
	 * Scoped tightly on purpose:
	 *
	 * - Only a definitive `false` from bb_theme_is_in_plan() blocks. `null` means the
	 *   add-ons cache is cold or the API is unreachable, and an outage must never stop a
	 *   paying customer from saving.
	 *   Only a submission of the options form is blocked — the option is also written
	 *   programmatically (defaults on first run, migrations, imports), and blocking those
	 *   would corrupt setup rather than enforce a licence.
	 *
	 * @since 2.22.2
	 *
	 * @param mixed $value     The new option value.
	 * @param mixed $old_value The existing option value.
	 * @return mixed The value to store.
	 */
	public function block_uncovered_theme_options_save( $value, $old_value ) {
		// Only intercept an actual form submission from the options screen.
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Redux verifies its own nonce; this only scopes the filter.
		if ( ! is_admin() || empty( $_POST ) || ! isset( $_POST['buddyboss_theme_options'] ) ) {
			return $value;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! current_user_can( 'manage_options' ) ) {
			return $value;
		}

		if ( ! class_exists( '\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager' ) ) {
			return $value;
		}

		if ( false !== \BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::bb_theme_is_in_plan() ) {
			return $value;
		}

		return $old_value;
	}

	/**
	 * Render the BuddyBoss admin header on relevant admin screens.
	 */
	public function bb_render_admin_header(): void {
		$screen = get_current_screen();

		if (
			(
				! empty( $screen->base ) &&
				(
					false !== strpos( $screen->base, 'buddyboss' ) ||
					false !== strpos( $screen->base, 'bp_' ) ||
					false !== strpos( $screen->base, 'bb_' )
				) &&
				(
					! empty( $screen->id ) &&
					(
						'buddyboss_page_bb-upgrade' !== $screen->id &&
						'buddyboss_page_bb-readylaunch' !== $screen->id
					)
				)
			) ||
			(
				! empty( $screen->post_type ) &&
				'buddyboss_fonts' === $screen->post_type
			)
		) {
			include __DIR__ . '/views/header.php';
		}
	}
}
