<?php

declare(strict_types=1);

namespace BuddyBossTheme\Admin\Mothership;

use BuddyBossTheme\GroundLevel\Mothership\AbstractPluginConnection;

/**
 * Plugin Connector class for BuddyBoss Platform.
 *
 * This class follows the GroundLevel AbstractPluginConnection pattern
 * for managing plugin-specific data and API connections.
 */
class BB_Theme_Connector extends AbstractPluginConnection {

	/**
	 * Constructor for the BB_Plugin_Connector class.
	 */
	public function __construct() {
		$this->pluginId     = $this->getDynamicPluginId();
		$this->pluginPrefix = 'buddyboss-theme';

		// The Mothership product ID is the dynamic plugin ID (edition) the license was
		// activated against. GroundLevel 9.x reads it directly (add-ons are fetched as
		// relations of this product; the license manager uses it for edition checks), so
		// it must never be left empty.
		$this->productId = $this->pluginId;
	}

	/**
	 * Get the dynamic plugin ID from stored option or default.
	 *
	 * @return string The plugin ID.
	 */
	public function getDynamicPluginId(): string {
		$storedPluginId = self::get_license_option( 'buddyboss_theme_dynamic_id', THEME_EDITION );
		return ! empty( $storedPluginId ) ? $storedPluginId : THEME_EDITION;
	}

	/**
	 * Set the dynamic plugin ID.
	 *
	 * @param string $pluginId The plugin ID to store.
	 */
	public function setDynamicPluginId( string $pluginId ): void {
		// Clear cache with old plugin ID before changing.
		if ( class_exists( '\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager' ) ) {
			\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::clearLicenseDetailsCache();
		}

		self::update_license_option( 'buddyboss_theme_dynamic_id', $pluginId );
		$this->pluginId  = $pluginId;
		$this->productId = $pluginId;

		// Clear cache with new plugin ID after changing.
		if ( class_exists( '\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager' ) ) {
			\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::clearLicenseDetailsCache();
		}
	}

	/**
	 * Clear the dynamic plugin ID.
	 */
	public function clearDynamicPluginId(): void {
		// Clear cache with old plugin ID before clearing.
		if ( class_exists( '\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager' ) ) {
			\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::clearLicenseDetailsCache();
		}

		self::delete_license_option( 'buddyboss_theme_dynamic_id' );
		$this->pluginId  = THEME_EDITION;
		$this->productId = THEME_EDITION;

		// Clear cache with default plugin ID after clearing.
		if ( class_exists( '\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager' ) ) {
			\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::clearLicenseDetailsCache();
		}
	}

	/**
	 * Get the current plugin ID.
	 *
	 * @return string The current plugin ID.
	 */
	public function getCurrentPluginId(): string {
		return $this->pluginId;
	}

	/**
	 * Gets the license activation status option.
	 *
	 * @return boolean The license activation status.
	 */
	public function getLicenseActivationStatus(): bool {
		$pluginId = $this->getCurrentPluginId();
		$status   = self::get_license_option( $pluginId . '_license_activation_status', false );
		return (bool) $status;
	}

	/**
	 * Sets the license activation status option.
	 *
	 * Overrides {@see AbstractPluginConnection::setLicenseActivationStatus()} so the
	 * BuddyBoss option name (`{pluginId}_license_activation_status`) is preserved — the
	 * GroundLevel 9.1.2 base defaults to `{pluginId}_license_active`, which would orphan
	 * every existing activation.
	 *
	 * @since 2.22.2
	 *
	 * @param boolean $status The status to update.
	 * @return boolean Whether the option was updated successfully.
	 */
	public function setLicenseActivationStatus( bool $status ): bool {
		$pluginId = $this->getCurrentPluginId();
		$updated  = self::update_license_option( $pluginId . '_license_activation_status', $status );

		// Clear license details cache when activation status changes.
		if ( class_exists( '\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager' ) ) {
			\BuddyBossTheme\Admin\Mothership\BB_Theme_License_Manager::clearLicenseDetailsCache();
		}

		return (bool) $updated;
	}

	/**
	 * Updates the license activation status option.
	 *
	 * Backward-compatible alias for {@see self::setLicenseActivationStatus()} retained
	 * for existing theme callers that expect a void return.
	 *
	 * @param boolean $status The status to update.
	 */
	public function updateLicenseActivationStatus( bool $status ): void {
		$this->setLicenseActivationStatus( $status );
	}

	/**
	 * Resolves the license key from storage.
	 *
	 * Overrides {@see AbstractPluginConnection::resolveLicenseKey()} so the GroundLevel
	 * 9.1.2 {@see Credentials} reads the dynamic-plugin-id-scoped option.
	 *
	 * @since 2.22.2
	 *
	 * @return string The license key.
	 */
	public function resolveLicenseKey(): string {
		$pluginId = $this->getCurrentPluginId();
		return (string) self::get_license_option( $pluginId . '_license_key', '' );
	}

	/**
	 * Stores the license key.
	 *
	 * Overrides {@see AbstractPluginConnection::storeLicenseKey()} so the GroundLevel
	 * 9.1.2 {@see Credentials} writes the dynamic-plugin-id-scoped option.
	 *
	 * @since 2.22.2
	 *
	 * @param string $licenseKey The license key to store.
	 * @return boolean Whether the option was updated successfully.
	 */
	public function storeLicenseKey( string $licenseKey ): bool {
		$pluginId = $this->getCurrentPluginId();
		return self::update_license_option( $pluginId . '_license_key', $licenseKey );
	}

	/**
	 * Gets the license key option.
	 *
	 * Convenience accessor retained for theme callers.
	 *
	 * @return string The license key.
	 */
	public function getLicenseKey(): string {
		return $this->resolveLicenseKey();
	}

	/**
	 * Updates the license key option.
	 *
	 * Backward-compatible alias for {@see self::storeLicenseKey()} retained for existing
	 * theme callers that expect a void return.
	 *
	 * @param string $licenseKey The license key to update.
	 */
	public function updateLicenseKey( string $licenseKey ): void {
		$this->storeLicenseKey( $licenseKey );
	}

	/**
	 * Resolves the license activation domain.
	 *
	 * Overrides {@see AbstractPluginConnection::resolveDomain()}, which returns only the
	 * host of the home URL. WordPress installs that live in a subdirectory (e.g.
	 * `example.com/community`) would otherwise share an activation identifier with a
	 * second install on the root domain, so activating both against the same license
	 * conflicts (PROD-9984). Appending the path gives each install its own identifier.
	 *
	 * The domain is the activation's identity on the licensing server: it is sent as the
	 * Basic-auth username and as a path segment of `licenses/{key}/activations/{domain}`,
	 * so `example.com` and `example.com/community` are two different activation records.
	 * Changing the format for a site that is ALREADY activated would orphan its record —
	 * the twice-daily status cron would 404 and GroundLevel would revoke the license. So
	 * the resolution order is:
	 *
	 * 1. The domain stored at activation time, when its host still matches this site (or, in
	 *    network mode, the main site).
	 * 2. The bare host, for a license activated before that option existed (legacy).
	 * 3. `host/path`, for a fresh activation only.
	 *
	 * The stored domain is ignored once its host no longer matches the site's home URL.
	 * A staging copy or database clone inherits the option, and without this check it
	 * would keep authenticating as production — and a "Deactivate" clicked on the clone
	 * would release production's activation on the licensing server.
	 *
	 * On a subdirectory multisite every subsite resolves its own `get_home_url()`, so each
	 * one keeps the identifier it was actually activated with.
	 *
	 * A `BUDDYBOSS_THEME_DOMAIN` constant or environment variable still takes precedence
	 * via {@see \BuddyBossTheme\GroundLevel\Mothership\Credentials::getDomain()}.
	 *
	 * @since 2.22.2
	 *
	 * @return string The activation domain (`host` or `host/path`).
	 */
	public function resolveDomain(): string {
		// In network mode the network owns one activation, identified by the network home URL.
		$network  = self::is_network_mode();
		$home_url = $network ? network_home_url() : get_home_url();
		$host     = (string) wp_parse_url( $home_url, PHP_URL_HOST );
		$stored   = $this->getStoredActivationDomain();

		if ( '' !== $stored ) {
			$stored_parts = explode( '/', $stored, 2 );

			// A network license may have been activated from the main site, whose host can
			// differ from the network's (domain mapping); both belong to this install.
			$hosts = array( strtolower( $host ) );
			if ( $network ) {
				$hosts[] = strtolower( (string) wp_parse_url( get_home_url( get_main_site_id() ), PHP_URL_HOST ) );
			}

			if ( '' === $host || in_array( strtolower( $stored_parts[0] ), $hosts, true ) ) {
				return $stored;
			}
		}

		if ( '' === $host ) {
			return '';
		}

		// An already-active license predates the stored-domain option and was activated
		// against the bare host. Keep sending the bare host or the server will not
		// recognise the activation and will revoke it on the next status check.
		if ( $this->getLicenseActivationStatus() ) {
			return $host;
		}

		return $host . untrailingslashit( (string) wp_parse_url( $home_url, PHP_URL_PATH ) );
	}

	/**
	 * Option holding the domain a license was actually activated against.
	 *
	 * @since 2.22.2
	 *
	 * @var string
	 */
	const ACTIVATION_DOMAIN_OPTION = 'buddyboss_theme_license_activation_domain';

	/**
	 * Gets the domain stored at activation time.
	 *
	 * @since 2.22.2
	 *
	 * @return string The stored domain, or an empty string when none is stored.
	 */
	public function getStoredActivationDomain(): string {
		$stored = self::get_license_option( self::ACTIVATION_DOMAIN_OPTION, '' );

		return is_string( $stored ) ? $stored : '';
	}

	/**
	 * Stores the domain a license was activated against.
	 *
	 * @since 2.22.2
	 *
	 * @param string $domain The domain the activation was performed with.
	 */
	public function storeActivationDomain( string $domain ): void {
		if ( '' === $domain ) {
			self::delete_license_option( self::ACTIVATION_DOMAIN_OPTION );

			return;
		}

		self::update_license_option( self::ACTIVATION_DOMAIN_OPTION, $domain );
	}

	/**
	 * Clears the stored activation domain.
	 *
	 * @since 2.22.2
	 */
	public function clearActivationDomain(): void {
		self::delete_license_option( self::ACTIVATION_DOMAIN_OPTION );
	}

	/**
	 * Gets the license activation domain.
	 *
	 * Backward-compatible alias for {@see self::resolveDomain()} retained for existing
	 * theme callers. Consumer code should prefer
	 * {@see \BuddyBossTheme\GroundLevel\Mothership\Credentials::getDomain()}, which also
	 * honors constant/environment overrides.
	 *
	 * @return string The activation domain.
	 */
	public function getDomain(): string {
		return $this->resolveDomain();
	}

	/**
	 * Site option recording that the main site's theme license was copied to the network.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @var string
	 */
	const NETWORK_SCOPE_MIGRATED_OPTION = 'bb_theme_license_network_scope_migrated';

	/**
	 * Per-request cache of {@see self::is_network_mode()}; reset when its inputs change.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @var bool|null
	 */
	private static $network_mode = null;

	/**
	 * Whether the theme license is owned by the network.
	 *
	 * A theme cannot be network-activated; it is Network Enabled and then activated per site.
	 * The network owns one theme license when all of these hold:
	 *
	 * - the install is multisite and BuddyBoss Theme is Network Enabled;
	 * - the main site runs BuddyBoss Theme (or a child) — Network Admin runs as the main site,
	 *   so that is the only place the theme can offer a Network Admin license screen;
	 * - BuddyBoss Platform is not active on the main site (or network-wide) — Platform then
	 *   owns licensing and the theme defers to it.
	 *
	 * Otherwise each site keeps its own theme license, as on a single-site install.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @return bool
	 */
	public static function is_network_mode(): bool {
		if ( null !== self::$network_mode ) {
			return self::$network_mode;
		}

		self::$network_mode = false;

		if ( ! is_multisite() ) {
			return false;
		}

		$main_site_id = get_main_site_id();
		if ( 'buddyboss-theme' !== get_blog_option( $main_site_id, 'template' ) ) {
			return false;
		}

		// Networks commonly Network Enable only the child theme; that still puts BuddyBoss
		// Theme on every site that uses it, so either the parent or the main site's own
		// (child) theme being network-enabled counts.
		$allowed         = \WP_Theme::get_allowed_on_network();
		$main_stylesheet = (string) get_blog_option( $main_site_id, 'stylesheet' );
		if ( empty( $allowed['buddyboss-theme'] ) && ( '' === $main_stylesheet || empty( $allowed[ $main_stylesheet ] ) ) ) {
			return false;
		}

		$plugins = array_merge(
			array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ),
			(array) get_blog_option( $main_site_id, 'active_plugins', array() )
		);

		foreach ( $plugins as $plugin ) {
			if ( preg_match( '#^buddyboss-platform[^/]*/bp-loader\.php$#', (string) $plugin ) ) {
				return false;
			}
		}

		self::$network_mode = true;

		return true;
	}

	/**
	 * Forget the cached mode (hooked to events that can change it mid-request).
	 *
	 * @since BuddyBoss Theme 2.22.2
	 */
	public static function reset_network_mode(): void {
		self::$network_mode = null;
	}

	/**
	 * Capability required to view and change the theme license.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @return string `manage_network_options` in network mode, otherwise `manage_options`.
	 */
	public static function license_capability(): string {
		return self::is_network_mode() ? 'manage_network_options' : 'manage_options';
	}

	/**
	 * Reads a license option from the active storage scope.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $name          Option name.
	 * @param mixed  $default_value Default value.
	 * @return mixed
	 */
	public static function get_license_option( string $name, $default_value = false ) {
		return self::is_network_mode() ? get_site_option( $name, $default_value ) : get_option( $name, $default_value );
	}

	/**
	 * Writes a license option to the active storage scope.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 * @return bool Whether the value was updated.
	 */
	public static function update_license_option( string $name, $value ): bool {
		return (bool) ( self::is_network_mode() ? update_site_option( $name, $value ) : update_option( $name, $value ) );
	}

	/**
	 * Deletes a license option from the active storage scope.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $name Option name.
	 * @return bool Whether the option was deleted.
	 */
	public static function delete_license_option( string $name ): bool {
		return (bool) ( self::is_network_mode() ? delete_site_option( $name ) : delete_option( $name ) );
	}

	/**
	 * Reads a license transient from the active storage scope.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $name Transient name.
	 * @return mixed
	 */
	public static function get_license_transient( string $name ) {
		return self::is_network_mode() ? get_site_transient( $name ) : get_transient( $name );
	}

	/**
	 * Writes a license transient to the active storage scope.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $name       Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Expiration in seconds.
	 * @return bool
	 */
	public static function set_license_transient( string $name, $value, int $expiration ): bool {
		return self::is_network_mode() ? set_site_transient( $name, $value, $expiration ) : set_transient( $name, $value, $expiration );
	}

	/**
	 * Deletes a license transient from both scopes.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $name Transient name.
	 */
	public static function delete_license_transient( string $name ): void {
		delete_transient( $name );
		delete_site_transient( $name );
	}

	/**
	 * Names of the options that make up the theme license state for a plugin ID.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 *
	 * @param string $plugin_id The dynamic plugin ID the per-SKU options are keyed by.
	 * @return string[]
	 */
	private static function license_option_names( string $plugin_id ): array {
		return array(
			'buddyboss_theme_dynamic_id',
			self::ACTIVATION_DOMAIN_OPTION,
			$plugin_id . '_license_key',
			$plugin_id . '_license_activation_status',
		);
	}

	/**
	 * Move the theme license to or from the network to match the current mode.
	 *
	 * Themes have no network (de)activation event, and the mode also changes when the main
	 * site switches theme or activates Platform, so this runs lazily on load: a single
	 * site-option read when nothing needs moving. Local writes only — no API calls.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 */
	public static function sync_license_scope(): void {
		if ( ! is_multisite() ) {
			return;
		}

		$migrated = (bool) get_site_option( self::NETWORK_SCOPE_MIGRATED_OPTION, false );

		if ( self::is_network_mode() && ! $migrated ) {
			self::move_license_to_network();
		} elseif ( ! self::is_network_mode() && $migrated ) {
			self::move_license_to_main_site();
		}
	}

	/**
	 * Copy the main site's theme license to the network (once, never overwriting).
	 *
	 * Only the main site's license is promoted: its domain is the network domain, so it stays
	 * the same activation on the licensing server. A subsite's license may be a different
	 * customer or edition and is never promoted. Per-site rows stay in place (dormant).
	 *
	 * @since BuddyBoss Theme 2.22.2
	 */
	private static function move_license_to_network(): void {
		update_site_option( self::NETWORK_SCOPE_MIGRATED_OPTION, 1 );

		$network_id = (string) get_site_option( 'buddyboss_theme_dynamic_id', THEME_EDITION );
		if ( '' !== (string) get_site_option( $network_id . '_license_key', '' ) ) {
			return;
		}

		$main_site_id = get_main_site_id();
		$plugin_id    = (string) get_blog_option( $main_site_id, 'buddyboss_theme_dynamic_id', '' );
		$plugin_id    = '' !== $plugin_id ? $plugin_id : THEME_EDITION;

		if ( '' === (string) get_blog_option( $main_site_id, $plugin_id . '_license_key', '' ) ) {
			return;
		}

		foreach ( self::license_option_names( $plugin_id ) as $name ) {
			$value = get_blog_option( $main_site_id, $name, null );
			if ( null !== $value ) {
				update_site_option( $name, $value );
			}
		}
	}

	/**
	 * Move the network theme license back to the main site when network mode ends.
	 *
	 * The network state is authoritative: the main site receives exactly the current rows, a
	 * row the network no longer holds is removed rather than letting a pre-network copy come
	 * back, and every network row is then deleted.
	 *
	 * @since BuddyBoss Theme 2.22.2
	 */
	private static function move_license_to_main_site(): void {
		$main_site_id = get_main_site_id();

		$plugin_id = (string) get_site_option( 'buddyboss_theme_dynamic_id', '' );
		$plugin_id = '' !== $plugin_id ? $plugin_id : THEME_EDITION;
		$main_id   = (string) get_blog_option( $main_site_id, 'buddyboss_theme_dynamic_id', '' );
		$main_id   = '' !== $main_id ? $main_id : THEME_EDITION;

		$current = self::license_option_names( $plugin_id );
		$names   = array_unique( array_merge( $current, self::license_option_names( $main_id ) ) );

		foreach ( $names as $name ) {
			$value = in_array( $name, $current, true ) ? get_site_option( $name, null ) : null;

			if ( null !== $value ) {
				update_blog_option( $main_site_id, $name, $value );
			} else {
				delete_blog_option( $main_site_id, $name );
			}
		}

		foreach ( $names as $name ) {
			delete_site_option( $name );
		}
		delete_site_option( self::NETWORK_SCOPE_MIGRATED_OPTION );
		delete_site_transient( $plugin_id . '_theme_license_details' );
	}

	/**
	 * Gets the BuddyBoss account dashboard URL.
	 *
	 * GroundLevel links here from license-related admin notices and update messages.
	 *
	 * @since 2.22.2
	 *
	 * @return string The account URL.
	 */
	public function getAccountUrl(): string {
		return 'https://www.buddyboss.com/my-account/';
	}
}
