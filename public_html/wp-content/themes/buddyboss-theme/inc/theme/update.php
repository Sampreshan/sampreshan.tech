<?php
/**
 * Theme Update Hooks.
 *
 * @package BuddyBoss_Theme
 */

// Clear transient after theme update.
if ( ! function_exists( 'buddyboss_theme_update' ) ) {

	/**
	 * Function is called when theme is updated.
	 *
	 * @since 1.7.3
	 */
	function buddyboss_theme_update() {
		$current_version = wp_get_theme( get_template() )->get( 'Version' );
		$old_version     = get_option( 'buddyboss_theme_version', '1.7.2' );

		if ( $old_version !== $current_version ) {

			// Call clear learndash group users transient.
			if ( version_compare( $current_version, '1.7.2', '>' ) && function_exists( 'bb_theme_update_1_7_3' ) ) {
				bb_theme_update_1_7_3();
			}

			// Call to back up default cover images.
			if ( version_compare( $current_version, '1.8.2', '>' ) && function_exists( 'bb_theme_update_1_8_3' ) ) {
				bb_theme_update_1_8_3();
			}

			// Call to back up default cover images.
			if ( version_compare( $current_version, '1.8.6', '>' ) && function_exists( 'bb_theme_update_1_8_7' ) ) {
				bb_theme_update_1_8_7();
			}

			// Call to back up default cover images.
			if ( version_compare( $current_version, '2.2.5', '>' ) && function_exists( 'bb_theme_update_2_2_6' ) ) {
				bb_theme_update_2_2_6();
			}

			// Set default logo destination url.
			if ( version_compare( $current_version, '2.3.40', '>' ) && function_exists( 'bb_theme_update_2_3_60' ) ) {
				bb_theme_update_2_3_60();
			}

			// update not to run twice.
			update_option( 'buddyboss_theme_version', $current_version );
		}

		bb_theme_setup_updater();

	}

	if ( is_admin() ) {
		add_action( 'after_setup_theme', 'buddyboss_theme_update' );
	}
}

/**
 * Clear the learndash course enrolled user count transient.
 *
 * @since 1.7.3
 */
function bb_theme_update_1_7_3() {
	global $wpdb;
	$sql       = 'select option_name from ' . $wpdb->options . ' where option_name like "%_transient_buddyboss_theme_ld_course_enrolled_users_count_%"';
	$all_cache = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( ! empty( $all_cache ) ) {
		foreach ( $all_cache as $cache_name ) {
			$cache_name = str_replace( '_site_transient_', '', $cache_name );
			$cache_name = str_replace( '_transient_', '', $cache_name );
			delete_transient( $cache_name );
			delete_site_transient( $cache_name );
		}
	}
}

/**
 * Backup default cover images.
 *
 * @since 1.8.4
 */
function bb_theme_update_1_8_3() {
	global $buddyboss_theme_options;

	$theme_default_member_cover = '';
	$theme_default_group_cover  = '';

	/* Check if options are set */
	if ( ! isset( $buddyboss_theme_options ) ) {
		$buddyboss_theme_options = get_option( 'buddyboss_theme_options', array() );
	}

	if ( isset( $buddyboss_theme_options['buddyboss_profile_cover_default'] ) ) {
		$theme_default_member_cover = $buddyboss_theme_options['buddyboss_profile_cover_default'];
	}

	if ( isset( $buddyboss_theme_options['buddyboss_group_cover_default'] ) ) {
		$theme_default_group_cover = $buddyboss_theme_options['buddyboss_group_cover_default'];
	}

	update_option( 'buddyboss_profile_cover_default_migration', $theme_default_member_cover );
	update_option( 'buddyboss_group_cover_default_migration', $theme_default_group_cover );

	// Delete custom css transient.
	delete_transient( 'buddyboss_theme_compressed_elementor_custom_css' );
}

/**
 * Backup cover width and height for profile and group.
 *
 * @since 1.8.7
 */
function bb_theme_update_1_8_7() {
	global $buddyboss_theme_options;

	$is_platform_upto_date = function_exists( 'buddypress' ) && defined( 'BP_PLATFORM_VERSION' ) && version_compare( BP_PLATFORM_VERSION, '1.9.1', '>=' );

	/* Check if options are empty */
	if ( ! isset( $buddyboss_theme_options ) ) {
		$buddyboss_theme_options = get_option( 'buddyboss_theme_options', array() );
	}

	if ( ! empty( $buddyboss_theme_options ) ) {
		update_option( 'old_buddyboss_theme_options_1_8_7', $buddyboss_theme_options );
	}

	if ( isset( $buddyboss_theme_options['buddyboss_profile_cover_width'] ) ) {
		$profile_cover_width = buddyboss_theme_get_option( 'buddyboss_profile_cover_width' );

		// If platform is not updated then option will migrate.
		if ( ! $is_platform_upto_date ) {
			delete_option( 'bb-pro-cover-profile-width' );
			add_option( 'bb-pro-cover-profile-width', $profile_cover_width );
		}
		unset( $buddyboss_theme_options['buddyboss_profile_cover_width'] );
	}

	if ( isset( $buddyboss_theme_options['buddyboss_profile_cover_height'] ) ) {
		$profile_cover_height = buddyboss_theme_get_option( 'buddyboss_profile_cover_height' );

		// If platform is not updated then option will migrate.
		if ( ! $is_platform_upto_date ) {
			delete_option( 'bb-pro-cover-profile-height' );
			add_option( 'bb-pro-cover-profile-height', $profile_cover_height );
		}
		unset( $buddyboss_theme_options['buddyboss_profile_cover_height'] );
	}

	if ( isset( $buddyboss_theme_options['buddyboss_group_cover_width'] ) ) {
		$group_cover_width = buddyboss_theme_get_option( 'buddyboss_group_cover_width' );

		// If platform is not updated then option will migrate.
		if ( ! $is_platform_upto_date ) {
			delete_option( 'bb-pro-cover-group-width' );
			add_option( 'bb-pro-cover-group-width', $group_cover_width );
		}
		unset( $buddyboss_theme_options['buddyboss_group_cover_width'] );
	}

	if ( isset( $buddyboss_theme_options['buddyboss_group_cover_height'] ) ) {
		$group_cover_height = buddyboss_theme_get_option( 'buddyboss_group_cover_height' );

		// If platform is not updated then option will migrate.
		if ( ! $is_platform_upto_date ) {
			delete_option( 'bb-pro-cover-group-height' );
			add_option( 'bb-pro-cover-group-height', $group_cover_height );
		}
		unset( $buddyboss_theme_options['buddyboss_group_cover_height'] );
	}

	if ( ! empty( $buddyboss_theme_options ) ) {
		update_option( 'buddyboss_theme_options', $buddyboss_theme_options );
	}
}

/**
 * Set up the BuddyBoss theme updater.
 *
 * @return void
 *
 * @since 1.8.7
 */
function bb_theme_setup_updater() {
	// Are we running an outdated version of BuddyBoss Theme?
	if ( wp_doing_ajax() || ! bb_theme_is_update() ) {
		return;
	}

	bb_theme_version_updater();
}

/**
 * Is this a BuddyBoss theme update?
 *
 * @return bool True if update, otherwise false.
 * @since 1.8.7
 */
function bb_theme_is_update() {

	// Current DB version of this site (per site in a multisite network).
	$current_db   = (int) get_option( '_bb_theme_db_version' );
	$current_live = (int) bb_theme_get_db_version();

	// Theme version history.
	bb_theme_version_bump();
	$bb_theme_version_history = (array) get_option( 'bb_theme_version_history', array() );
	$initial_version_data     = ! empty( $bb_theme_version_history ) ? end( $bb_theme_version_history ) : array();
	$bb_version_exists        = ! empty( $initial_version_data ) && ! empty( $initial_version_data['version'] ) && (string) buddyboss_theme()->version() === (string) $initial_version_data['version'];
	if ( ! $bb_version_exists || $current_live !== $current_db ) {
		$current_date               = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$bb_latest_theme_version    = array(
			'db_version' => $current_live,
			'date'       => $current_date->format( 'Y-m-d H:i:s' ),
			'version'    => buddyboss_theme()->version(),
		);
		$bb_theme_version_history[] = $bb_latest_theme_version;
		update_option( 'bb_theme_version_history', array_filter( $bb_theme_version_history ) );
	}

	$is_update = false;
	if ( $current_live !== $current_db ) {
		$is_update = true;
	}

	// Return the product of version comparison.
	return $is_update;
}

/**
 * Initialize an update or installation of BuddyBoss Theme.
 *
 * BuddyBoss Theme's version updater looks at what the current database version is,
 * and runs whatever other code is needed - either the "update" or "install"
 * code.
 *
 * @since 1.8.7
 */
function bb_theme_version_updater() {

	// Get current DB version.
	$current_db = (int) get_option( '_bb_theme_db_version' );
	// Get the raw database version.
	$raw_db_version = (int) bb_theme_get_db_version_raw();

	/* All done! *************************************************************/

	// Add the conditional logic for each version migration code.
	if ( $raw_db_version < 400 ) {
		bb_theme_update_2_0_0();
	}

	if ( $raw_db_version < 430 ) {
		// Function to migrate all menu icon type.
		bb_theme_update_nav_menu_icon_type_2_0_5();

		// Function to migrate image icon type to enabled by defaults.
		bb_theme_update_support_custom_icon_2_0_5();
	}

	if ( $raw_db_version < 435 ) {
		bb_theme_update_2_2_1_2();
	}

	if ( $raw_db_version !== $current_db ) {
		bb_theme_migrate_google_plus();
	}
}

/**
 * Update the BuddyBoss Theme version stored in the database to the current version.
 *
 * @since 1.8.7
 */
function bb_theme_version_bump() {
	update_option( '_bb_theme_db_version', bb_theme_get_db_version() );
}

/**
 * Output the BuddyBoss Theme database version.
 *
 * @since 1.8.7
 */
function bb_theme_db_version() {
	echo bb_theme_get_db_version();
}
/**
 * Return the BuddyBoss Theme database version.
 *
 * @since 1.8.7
 *
 * @return string The BuddyBoss Theme database version.
 */
function bb_theme_get_db_version() {
	return buddyboss_theme()->bb_theme_db_version;
}

/**
 * Output the BuddyBoss Theme database version.
 *
 * @since 1.8.7
 */
function bb_theme_db_version_raw() {
	echo bb_theme_get_db_version_raw();
}

/**
 * Return the BuddyBoss Theme database version.
 *
 * @since 1.8.7
 *
 * @return string The BuddyBoss Theme version direct from the database.
 */
function bb_theme_get_db_version_raw() {
	return ! empty( buddyboss_theme()->bb_theme_db_version_raw ) ? buddyboss_theme()->bb_theme_db_version_raw : 0;
}

/**
 * Migrate options for 2.0.0
 *
 * @since 2.0.0
 */
function bb_theme_migrate_components_options() {
	global $buddyboss_theme_options;

	/* Check if options are set */
	if ( ! isset( $buddyboss_theme_options ) ) {
		$buddyboss_theme_options = get_option( 'buddyboss_theme_options', array() );
	}

	// Set default styling option to theme 1.0 when updating the theme.
	// Set default logo on for header 3 style.
	if (
		! empty( $buddyboss_theme_options ) &&
		! isset( $buddyboss_theme_options['theme_template'] )
	) {
		$buddyboss_theme_options['theme_template'] = '1';
	} else {
		$buddyboss_theme_options['theme_template'] = '2';
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['mobile_header_search'] ) ) {
		$buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_header_search'] = buddyboss_theme_get_option( 'mobile_header_search' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['mobile_messages'] ) ) {
		$buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_messages'] = buddyboss_theme_get_option( 'mobile_messages' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['mobile_shopping_cart'] ) ) {
		$buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_shopping_cart'] = buddyboss_theme_get_option( 'mobile_shopping_cart' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['mobile_notifications'] ) ) {
		$buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_notifications'] = buddyboss_theme_get_option( 'mobile_notifications' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['header_search'] ) ) {
		$buddyboss_theme_options['desktop_component_opt_multi_checkbox']['desktop_header_search'] = buddyboss_theme_get_option( 'header_search' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['messages'] ) ) {
		$buddyboss_theme_options['desktop_component_opt_multi_checkbox']['desktop_messages'] = buddyboss_theme_get_option( 'messages' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['shopping_cart'] ) ) {
		$buddyboss_theme_options['desktop_component_opt_multi_checkbox']['desktop_shopping_cart'] = buddyboss_theme_get_option( 'shopping_cart' );
	}

	if ( isset( $buddyboss_theme_options ) && isset( $buddyboss_theme_options['notifications'] ) ) {
		$buddyboss_theme_options['desktop_component_opt_multi_checkbox']['desktop_notifications'] = buddyboss_theme_get_option( 'notifications' );
	}

	if (
		isset( $buddyboss_theme_options['buddyboss_header'] ) &&
		'3' === $buddyboss_theme_options['buddyboss_header']
	) {
		$buddyboss_theme_options['buddypanel_show_logo'] = '1';
	}

	// Set default 404 featured image to custom when updating the theme and image uploaded.
	$img_404 = buddyboss_theme_get_option( '404_image' );
	if ( is_array( $img_404 ) && $img_404['url'] ) {
		$buddyboss_theme_options['404_featured_image'] = 'custom';
	}

	// Migrate all the older maintenance social network to latest version.
	$social_network_twitter   = buddyboss_theme_get_option( 'social_network_twitter' );
	$social_network_facebook  = buddyboss_theme_get_option( 'social_network_facebook' );
	$social_network_google    = buddyboss_theme_get_option( 'social_network_google' );
	$social_network_instagram = buddyboss_theme_get_option( 'social_network_instagram' );
	$social_network_youtube   = buddyboss_theme_get_option( 'social_network_youtube' );
	$buddyboss_theme_options['maintenance_social_links']['twitter']   = ! empty( $social_network_twitter ) ? $social_network_twitter : '';
	$buddyboss_theme_options['maintenance_social_links']['facebook']  = ! empty( $social_network_facebook ) ? $social_network_facebook : '';
	$buddyboss_theme_options['maintenance_social_links']['google']    = ! empty( $social_network_google ) ? $social_network_google : '';
	$buddyboss_theme_options['maintenance_social_links']['instagram'] = ! empty( $social_network_instagram ) ? $social_network_instagram : '';
	$buddyboss_theme_options['maintenance_social_links']['youtube']   = ! empty( $social_network_youtube ) ? $social_network_youtube : '';

	update_option( 'buddyboss_theme_options', $buddyboss_theme_options );

	// Backward compatibility of icon picker.
	icon_picker_backward_compatibility();
}

/**
 * Backward compatibility of icon picker.
 *
 * @since 2.0.0
 */
function icon_picker_backward_compatibility() {
	// fix option table data.
	$menu_icons = get_option( 'menu-icons' );
	if ( isset( $menu_icons['global']['icon_types'] ) && ! empty( $menu_icons['global']['icon_types'] ) ) {
		if ( ! in_array( 'buddyboss', $menu_icons['global']['icon_types'], true ) ) {
			$menu_icons['global']['icon_types'][] = 'buddyboss';
		}
		if ( ! in_array( 'buddyboss_legacy', $menu_icons['global']['icon_types'], true ) ) {
			$menu_icons['global']['icon_types'][] = 'buddyboss_legacy';
		}
	} else {
		$menu_icons = array(
			'global' => array(
				'icon_types' => array(
					'buddyboss',
					'buddyboss_legacy',
				),
			),
		);
	}
	// update option.
	update_option( 'menu-icons', $menu_icons );

	// fix postmeta table data.
	$args = array(
		'post_type'   => 'nav_menu_item',
		'post_status' => 'publish',
	);

	$r              = wp_parse_args( null, $args );
	$get_posts      = new \WP_Query();
	$nav_menu_items = $get_posts->query( $r );

	if ( isset( $nav_menu_items ) && ! empty( $nav_menu_items ) ) {
		$nav_menu_items = wp_list_pluck( $nav_menu_items, 'ID' );
		foreach ( $nav_menu_items as $single ) {
			$menu_icons = get_post_meta( $single, 'menu-icons', true );
			if ( isset( $menu_icons['type'] ) && 'buddyboss' === $menu_icons['type'] ) {
				$menu_icons['type'] = 'buddyboss_legacy';
				update_post_meta( $single, 'menu-icons', $menu_icons );
			}
		}
	}
}

/**
 * Backward compatibility of header menu.
 *
 * @since 2.0.0
 */
function bb_theme_migrate_header_menu() {
	// Set the Header Menu - Logged in users to Header Menu - Logged out.
	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( isset( $locations ) && isset( $locations['header-menu'] ) ) {
		$locations['header-menu-logout'] = $locations['header-menu'];
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/**
 * Function to migrate all the theme data from old theme to new theme.
 *
 * @since 2.0.0
 */
function bb_theme_update_2_0_0() {

	// Migrate the header menu data.
	bb_theme_migrate_header_menu();

	// Migrate the component options and theme option.
	bb_theme_migrate_components_options();

	// Clear all the cache.
	if ( function_exists( 'buddyboss_theme_compressed_transient_delete' ) ) {
		buddyboss_theme_compressed_transient_delete();
	}

}

/**
 * Function to delete transient data from database.
 *
 * @since 2.2.1.2
 */
function bb_theme_update_2_2_1_2() {
	delete_transient( 'update_themes' );
	delete_transient( 'update_plugins' );
	delete_transient( 'bb_updates_bp-loader' );
	delete_transient( 'bb_updates_buddyboss-theme' );
	delete_transient( 'bb_updates_buddyboss-platform-pro' );
	// For Multi site.
	delete_site_transient( 'update_themes' );
	delete_site_transient( 'update_plugins' );
	delete_site_transient( 'bb_updates_bp-loader' );
	delete_site_transient( 'bb_updates_buddyboss-theme' );
	delete_site_transient( 'bb_updates_buddyboss-platform-pro' );
}

/**
 * Function to migrate all menu icon type.
 *
 * @since 2.0.5
 */
function bb_theme_update_nav_menu_icon_type_2_0_5() {
	$mapping_array = array(
		'buddyboss' => array(),
		'legacy'    => array(),
	);
	$args          = array(
		'post_type'   => 'nav_menu_item',
		'post_status' => 'publish',
	);

	if ( ! class_exists( 'Icon_Picker_Type_BuddyBoss_Legacy' ) ) {
		require_once buddyboss_theme()->inc_dir() . '/plugins/buddyboss-menu-icons/vendor/kucrut/icon-picker/includes/types/buddyboss_legacy.php';
	}

	if ( ! class_exists( 'Icon_Picker_Type_BuddyBoss' ) ) {
		require_once buddyboss_theme()->inc_dir() . '/plugins/buddyboss-menu-icons/vendor/kucrut/icon-picker/includes/types/buddyboss.php';
	}

	// Get BuddyBoss icons.
	$buddyboss_icon_object = new Icon_Picker_Type_BuddyBoss();
	$buddyboss_icon_array  = $buddyboss_icon_object->get_items();

	// Get BuddyBoss Legacy icons.
	$buddyboss_legacy_icon_object = new Icon_Picker_Type_BuddyBoss_Legacy();
	$buddyboss_legacy_icon_array  = $buddyboss_legacy_icon_object->get_items();

	$r              = wp_parse_args( null, $args );
	$get_posts      = new \WP_Query();
	$nav_menu_items = $get_posts->query( $r );

	if ( isset( $nav_menu_items ) && ! empty( $nav_menu_items ) ) {
		$nav_menu_items = wp_list_pluck( $nav_menu_items, 'ID' );
		foreach ( $nav_menu_items as $menu_id ) {
			$menu_icons = get_post_meta( $menu_id, 'menu-icons', true );

			$menu_icon      = '';
			$menu_icon_type = '';

			if ( isset( $menu_icons['icon'] ) && ! empty( $menu_icons['icon'] ) ) {
				$menu_icon = $menu_icons['icon'];
			}

			if ( ! empty( $menu_icon ) ) {

				if ( isset( $mapping_array['buddyboss'][ $menu_icon ] ) ) {
					$menu_icon_type = 'buddyboss';
				} elseif ( isset( $mapping_array['legacy'][ $menu_icon ] ) ) {
					$menu_icon_type = 'buddyboss_legacy';
				}

				if ( empty( $menu_icon_type ) ) {
					$buudyboss_icon_key = array_search( $menu_icon, array_column( $buddyboss_icon_array, 'id' ), true );

					if ( 0 <= $buudyboss_icon_key && isset( $buddyboss_icon_array[ $buudyboss_icon_key ] ) ) {
						$menu_icon_type                           = 'buddyboss';
						$mapping_array['buddyboss'][ $menu_icon ] = $buddyboss_icon_array[ $buudyboss_icon_key ];
					} else {

						$legacy_icon_key = array_search( $menu_icon, array_column( $buddyboss_legacy_icon_array, 'id' ), true );

						if ( 0 <= $legacy_icon_key && isset( $buddyboss_legacy_icon_array[ $legacy_icon_key ] ) ) {
							$menu_icon_type                        = 'buddyboss_legacy';
							$mapping_array['legacy'][ $menu_icon ] = $buddyboss_legacy_icon_array[ $legacy_icon_key ];
						}
					}
				}

				if ( ! empty( $menu_icon_type ) ) {
					$menu_icons['type'] = $menu_icon_type;
					update_post_meta( $menu_id, 'menu-icons', $menu_icons );
				}
			}
		}
	}
}

/**
 * Function to migrate image icon type to enabled by defaults.
 *
 * @since 2.0.5
 */
function bb_theme_update_support_custom_icon_2_0_5() {
	// Add the default icon types.
	$menu_icons = get_option( 'menu-icons' );
	if ( isset( $menu_icons['global']['icon_types'] ) && ! empty( $menu_icons['global']['icon_types'] ) ) {
		if ( ! in_array( 'image', $menu_icons['global']['icon_types'], true ) ) {
			$menu_icons['global']['icon_types'][] = 'image';
		}
		if ( ! in_array( 'manage', $menu_icons['global']['icon_types'], true ) ) {
			$menu_icons['global']['icon_types'][] = 'manage';
		}
	} else {
		$menu_icons = array(
			'global' => array(
				'icon_types' => array(
					'buddyboss',
					'buddyboss_legacy',
					'image',
					'manage',
				),
			),
		);
	}
	// update option.
	update_option( 'menu-icons', $menu_icons );
}

/**
 * Run the DB engine update.
 *
 * @since 2.2.6
 *
 * @return void
 *
 * @throws ReflectionException
 */
function bb_theme_update_2_2_6() {
	if ( function_exists( 'buddyboss_theme' ) ) {
		$base_theme_reflection = new ReflectionClass( get_class( buddyboss_theme() ) );
		$related_posts_helper  = $base_theme_reflection->getProperty( '_related_posts_helper' );
		$related_posts_helper->setAccessible( true );
		$releated_post_class = $related_posts_helper->getValue( buddyboss_theme() );
		if ( is_a( $releated_post_class, 'BuddyBossTheme\RelatedPostsHelper' ) ) {
			$releated_post_class->crp_create_index();
		}
	}
}

/**
 * Save the default logo destination to db.
 *
 * @since 2.3.60
 *
 * @return void
 */
function bb_theme_update_2_3_60() {

	$bb_theme_options = get_option( 'buddyboss_theme_options', array() );

	if ( empty( $bb_theme_options['header_logo_loggedin_link'] ) ) {
		$bb_theme_options['header_logo_loggedin_link'] = 'default';
	}

	if ( empty( $bb_theme_options['header_logo_loggedout_link'] ) ) {
		$bb_theme_options['header_logo_loggedout_link'] = 'default';
	}

	update_option( 'buddyboss_theme_options', $bb_theme_options );
}

/**
 * Remove support of google plus.
 *
 * @since 2.4.20
 *
 * @return void
 */
function bb_theme_migrate_google_plus() {
	$footer_socials = buddyboss_theme_get_option( 'boss_footer_social_links' );
	if ( ! empty( $footer_socials ) && isset( $footer_socials['google-plus'] ) ) {
		$buddyboss_theme_options = get_option( 'buddyboss_theme_options', array() );
		$google_plus             = $footer_socials['google-plus'];
		if ( ! empty( $google_plus ) ) {
			update_option( 'bb_theme_google_plus', $google_plus );
		}
		unset( $footer_socials['google-plus'] );
		$buddyboss_theme_options['boss_footer_social_links'] = $footer_socials;
		update_option( 'buddyboss_theme_options', $buddyboss_theme_options );
	}
}

/**
 * Themes screen "View version x.x.x details" link.
 *
 * This theme is distributed from BuddyBoss's own servers, so WordPress has no
 * theme-information source for it. Unlike the Plugins screen, the Themes
 * screen never calls themes_api() for this link: core builds it straight from
 * the update transient entry's 'url', which the updater leaves empty, so the
 * link resolves to a bare query string and loads the current admin screen in
 * the thickbox.
 *
 * Core builds that link in two places and hard-codes the thickbox class in
 * both: get_theme_update_available(), reached through
 * wp_prepare_themes_for_js() on the single-site Themes screen, and
 * wp_theme_update_row() on the Network Admin themes list table. buddyboss.com
 * sends X-Frame-Options: SAMEORIGIN, so an external page in the thickbox
 * iframe renders blank - both paths therefore have to lose that class, or
 * filling the URL in would trade one broken link for another.
 *
 * The two renderers are reached differently. wp_prepare_themes_for_js() is a
 * filter, so the single-site markup is rewritten in PHP. wp_theme_update_row()
 * prints instead of returning and has no filter of its own; the row is the
 * update notice, so nothing that risks losing it is worth a details link, and
 * buffering it cannot be made safe - ob_get_level() counts depth rather than
 * naming a buffer, so no amount of checking tells this code whether the buffer
 * it is about to close is still its own. That row is therefore left exactly as
 * core printed it and its anchor is adjusted in the browser instead, where a
 * failure can only leave core's own link in place.
 */

if ( ! function_exists( 'buddyboss_theme_release_notes_url' ) ) {
	/**
	 * Get the release notes page URL for a theme version.
	 *
	 * @since 2.22.2
	 *
	 * @param mixed $version Optional. Version to link directly to. Anything that
	 *                       does not name a release falls back to the archive.
	 *
	 * @return string Release notes URL.
	 */
	function buddyboss_theme_release_notes_url( $version = '' ) {
		$url = 'https://buddyboss.com/resources/buddyboss-theme-releases/';

		// The version comes from the update feed, so it is not trusted to be a
		// string: casting an object would throw, and casting an array warns.
		if ( ! is_scalar( $version ) ) {
			return $url;
		}

		$version = trim( (string) $version );

		// No release is named by anything this long; a value this size is junk.
		if ( 32 < strlen( $version ) ) {
			return $url;
		}

		// A "v" prefix still names a release.
		if ( '' !== $version && ( 'v' === $version[0] || 'V' === $version[0] ) ) {
			$version = substr( $version, 1 );
		}

		/*
		 * Take the leading two or three dot-separated numbers, so a suffixed
		 * version ("2.20.0-beta1") truncates to its base release and a longer
		 * one ("2.20.0.1") drops the build component. Requiring a digit-dot-digit
		 * shape means a degenerate value ("." or "2,20,0") names no page and
		 * falls back to the archive instead of linking at a 404.
		 */
		if ( ! preg_match( '/^[0-9]+(?:\.[0-9]+){1,2}/', $version, $matches ) ) {
			return $url;
		}

		return $url . str_replace( '.', '-', $matches[0] ) . '/';
	}
}

if ( ! function_exists( 'buddyboss_theme_update_response' ) ) {
	/**
	 * Read the 'response' map out of an 'update_themes' transient value.
	 *
	 * The container is normalized rather than indexed where it is found.
	 * 'response' is an array as core writes it and as the updater rewrites it,
	 * but a plugin that round-trips this transient through JSON - which caching,
	 * staging-sync and update manager plugins do routinely - hands back a
	 * stdClass, and indexing an object with [] is an uncaught Error on PHP 8
	 * rather than a notice. isset() is no protection at all here: the dimension
	 * fetch is evaluated before the existence test, so it throws from inside
	 * isset() itself, and it throws whether or not the key is present. Casting
	 * to array restores the JSON shape to what core wrote, and disarms every
	 * other object as well - the cast reads the property table and never calls
	 * offsetExists(), so an ArrayAccess implementation whose offsetExists()
	 * throws cannot reach out of here either. A container that is not an object
	 * at all never gets indexed in the first place.
	 *
	 * Every reader in this theme goes through here, and that is the point
	 * rather than a detail. On an admin screen WordPress indexes the same
	 * container raw first - get_theme_update_available() and
	 * wp_theme_update_row() both do - so defending only this file would buy
	 * nothing there. What it buys is the front end, where none of wp-admin is
	 * loaded and the theme is the only thing reading the transient:
	 * BaseTheme::bb_get_new_theme_version() is reached from _do_includes() on
	 * every request the theme serves, so an unnormalized read there takes the
	 * public site down while the dashboard stays up. That reader and
	 * BBThemeUpdate::bb_get_theme_update_available() therefore call this too.
	 * Do not add a third reader that indexes 'response' directly.
	 *
	 * @since 2.22.2
	 *
	 * @param mixed $value Value of the 'update_themes' site transient.
	 *
	 * @return array Response map, empty when there is none to read.
	 */
	function buddyboss_theme_update_response( $value ) {
		if ( ! is_object( $value ) || ! isset( $value->response ) ) {
			return array();
		}

		return (array) $value->response;
	}
}

if ( ! function_exists( 'buddyboss_theme_platform_owns_update_details' ) ) {
	/**
	 * Whether BuddyBoss Platform is handling this theme's update details link.
	 *
	 * Platform renders the release notes in a modal inside wp-admin, which is
	 * strictly better than the link below - that one can only leave the admin,
	 * because buddyboss.com refuses to be framed. Platform also covers the case
	 * this file cannot reach at all: a BuddyBoss theme that is installed but not
	 * active still shows an update row, and WordPress never loads an inactive
	 * theme's files. So when Platform is present it owns the link and everything
	 * here stands down.
	 *
	 * @since 2.22.2
	 *
	 * @return bool True when Platform's handler is available.
	 */
	function buddyboss_theme_platform_owns_update_details() {
		if ( ! function_exists( 'buddypress' ) ) {
			return false;
		}

		$bp = buddypress();

		return isset( $bp->admin )
			&& is_object( $bp->admin )
			&& is_callable( array( $bp->admin, 'bb_render_theme_changelog' ) );
	}
}

if ( ! function_exists( 'buddyboss_theme_update_details_url' ) ) {
	/**
	 * Give this theme's update transient entry a details URL.
	 *
	 * Only the 'response' entry is filled in. Nothing reads a details URL out of
	 * 'no_update': wp_prepare_themes_for_js() takes only the auto-update payload
	 * from there, and wp_theme_update_row() reads 'response' alone.
	 *
	 * @since 2.22.2
	 *
	 * @param mixed $value Value of the 'update_themes' site transient.
	 *
	 * @return mixed
	 */
	function buddyboss_theme_update_details_url( $value ) {
		/*
		 * Admin only, and the ordering is the reason. This filter runs on every
		 * read of the transient, including the front end and WP-Cron - and there
		 * the Platform check below is necessarily false, because BP_Admin is only
		 * constructed under is_admin(). So off-admin this would write its own URL
		 * into an entry Platform would otherwise claim, and that write does not
		 * stay in memory: wp_update_themes() reads the filtered transient and
		 * stores it back before its own request, keeping the value if that request
		 * fails. Back in wp-admin the entry then looks like another updater's, both
		 * handlers stand aside, and core frames a buddyboss.com URL that answers
		 * X-Frame-Options: SAMEORIGIN - the blank modal this all exists to fix.
		 * Nothing outside the admin renders a theme details link, so there is
		 * nothing to give up by returning early.
		 */
		if ( ! is_admin() ) {
			return $value;
		}

		if ( buddyboss_theme_platform_owns_update_details() ) {
			return $value;
		}

		if ( empty( $value ) || ! is_object( $value ) ) {
			return $value;
		}

		$response = buddyboss_theme_update_response( $value );

		$stylesheet = get_template();

		if ( ! isset( $response[ $stylesheet ] ) || ! is_array( $response[ $stylesheet ] ) ) {
			return $value;
		}

		// Never overwrite a URL another updater already supplied.
		if ( ! empty( $response[ $stylesheet ]['url'] ) ) {
			return $value;
		}

		/*
		 * Only a container core itself wrote gets written back to. Assigning
		 * into an object container throws in turn, and normalizing its shape
		 * here would hand every later filter something other than what it was
		 * given - this filter only adds a URL, it does not repair transients.
		 */
		if ( ! is_array( $value->response ) ) {
			return $value;
		}

		$version = isset( $response[ $stylesheet ]['new_version'] ) ? $response[ $stylesheet ]['new_version'] : '';

		$value->response[ $stylesheet ]['url'] = buddyboss_theme_release_notes_url( $version );

		return $value;
	}
}
add_filter( 'site_transient_update_themes', 'buddyboss_theme_update_details_url', 20 );

if ( ! function_exists( 'buddyboss_theme_update_details_markup' ) ) {
	/**
	 * Point this theme's details anchor at the release notes in a new tab.
	 *
	 * Nothing happens unless the markup carries the thickbox class and the
	 * exact URL this theme put in the transient, so an anchor that has already
	 * been rewritten, one that belongs to another theme, and one whose details
	 * URL another updater supplied all pass through untouched.
	 *
	 * @since 2.22.2
	 *
	 * @param string $html Markup containing the details anchor.
	 *
	 * @return string
	 */
	function buddyboss_theme_update_details_markup( $html ) {
		if ( buddyboss_theme_platform_owns_update_details() ) {
			return $html;
		}

		if ( ! is_string( $html ) || false === strpos( $html, 'class="thickbox open-plugin-details-modal"' ) ) {
			return $html;
		}

		$stylesheet = get_template();
		$response   = buddyboss_theme_update_response( get_site_transient( 'update_themes' ) );
		$version    = '';

		if (
			isset( $response[ $stylesheet ] )
			&& is_array( $response[ $stylesheet ] )
			&& isset( $response[ $stylesheet ]['new_version'] )
		) {
			$version = $response[ $stylesheet ]['new_version'];
		}

		/*
		 * The renderer appends thickbox's iframe sizing to the URL before
		 * printing it. Those arguments mean nothing to a tab, so undo them by
		 * rebuilding the decorated and the clean spelling of the same URL and
		 * swapping one for the other - which cannot touch any other anchor.
		 */
		$clean     = buddyboss_theme_release_notes_url( $version );
		$decorated = esc_url(
			add_query_arg(
				array(
					'TB_iframe' => 'true',
					'width'     => 1024,
					'height'    => 800,
				),
				$clean
			)
		);

		/*
		 * Rewrite only a link this theme put there. Another updater that
		 * supplied its own details URL keeps its own anchor - stripping the
		 * thickbox class off it would break a modal that works, and the sizing
		 * arguments could not be undone on a URL this does not know.
		 */
		if ( false === strpos( $html, $decorated ) ) {
			return $html;
		}

		$html = str_replace( $decorated, esc_url( $clean ), $html );

		$html = str_replace(
			'class="thickbox open-plugin-details-modal"',
			'target="_blank" rel="noopener noreferrer"',
			$html
		);

		/*
		 * Say that the link leaves the page. Core's aria-label follows the
		 * attributes just swapped in, so it can be extended in place without
		 * having to match the translated link text. The string is the theme's
		 * own: the build's text-domain check allows no other domain here.
		 */
		$announced = preg_replace_callback(
			'/(target="_blank" rel="noopener noreferrer" aria-label=")([^"]*)(")/',
			function ( $matches ) {
				return $matches[1] . $matches[2] . ' ' . esc_attr__( '(opens in a new tab)', 'buddyboss-theme' ) . $matches[3];
			},
			$html,
			1
		);

		return null === $announced ? $html : $announced;
	}
}

if ( ! function_exists( 'buddyboss_theme_update_details_target' ) ) {
	/**
	 * Rewrite this theme's details link on the single-site Themes screen.
	 *
	 * Runs at a later priority than
	 * BBThemeUpdate::bb_change_theme_update_info_html(), so it post-processes
	 * whichever markup ended up there.
	 *
	 * @since 2.22.2
	 *
	 * @param array $prepared_themes Theme data prepared for the Themes screen JS.
	 *
	 * @return array
	 */
	function buddyboss_theme_update_details_target( $prepared_themes ) {
		if ( buddyboss_theme_platform_owns_update_details() ) {
			return $prepared_themes;
		}

		$stylesheet = get_template();

		if ( empty( $prepared_themes[ $stylesheet ]['update'] ) ) {
			return $prepared_themes;
		}

		$prepared_themes[ $stylesheet ]['update'] = buddyboss_theme_update_details_markup( $prepared_themes[ $stylesheet ]['update'] );

		return $prepared_themes;
	}
}
add_filter( 'wp_prepare_themes_for_js', 'buddyboss_theme_update_details_target', 20 );

if ( ! function_exists( 'buddyboss_theme_update_details_script' ) ) {
	/**
	 * Point this theme's details anchor at the release notes on the Network Admin themes screen.
	 *
	 * Core's wp_theme_update_row() prints the row instead of returning it and
	 * has no filter of its own. The row is the update notice, so it is left
	 * untouched and its anchor is adjusted in the browser: the worst this can
	 * do is nothing, leaving the link exactly as core wrote it.
	 *
	 * Thickbox binds by delegation - tb_init() does
	 * jQuery( 'body' ).on( 'click', 'a.thickbox, ...', tb_click ) - so the
	 * selector is re-read at click time and dropping the class is enough to
	 * detach it, whenever this runs.
	 *
	 * The anchor is found by its href rather than by this theme's directory
	 * name: that URL is the one buddyboss_theme_update_details_url() put in the
	 * transient, so matching on it rewrites this theme's link and nothing else,
	 * including when another updater supplied a details URL of its own.
	 *
	 * @since 2.22.2
	 *
	 * @return void
	 */
	function buddyboss_theme_update_details_script() {
		if ( buddyboss_theme_platform_owns_update_details() ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		// WP_MS_Themes_List_Table only. The single-site Themes screen is served
		// by buddyboss_theme_update_details_target() instead.
		if ( ! is_object( $screen ) || 'themes-network' !== $screen->id ) {
			return;
		}

		// wp_json_encode() escapes '/' as '\/', so no translation can close
		// this script element, and the result is a quoted JavaScript string.
		$announce = wp_json_encode( ' ' . __( '(opens in a new tab)', 'buddyboss-theme' ) );
		$base     = wp_json_encode( buddyboss_theme_release_notes_url() );
		?>
		<script>
		( function () {
			var base = <?php echo $base; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() output. ?>,
				announce = <?php echo $announce; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() output. ?>,
				links = document.querySelectorAll( 'tr.plugin-update-tr a.open-plugin-details-modal' ),
				i, link, href, query, label;

			for ( i = 0; i < links.length; i++ ) {
				link = links[ i ];
				href = link.getAttribute( 'href' ) || '';

				if ( 0 !== href.indexOf( base ) ) {
					continue;
				}

				link.classList.remove( 'thickbox' );
				link.classList.remove( 'open-plugin-details-modal' );

				// Thickbox's iframe sizing means nothing to a tab. Everything
				// this theme links to is a bare page, so the query string goes.
				query = href.indexOf( '?' );

				if ( -1 !== query ) {
					link.setAttribute( 'href', href.slice( 0, query ) );
				}

				link.setAttribute( 'target', '_blank' );
				link.setAttribute( 'rel', 'noopener noreferrer' );

				label = link.getAttribute( 'aria-label' );

				// Dropping the class above already takes this anchor out of the
				// selector, so a second pass finds nothing; the tail test keeps
				// that true even if the selector is ever widened.
				if ( label && announce !== label.slice( -announce.length ) ) {
					link.setAttribute( 'aria-label', label + announce );
				}
			}
		} )();
		</script>
		<?php
	}
}
add_action( 'admin_footer-themes.php', 'buddyboss_theme_update_details_script' );

if ( ! function_exists( 'buddyboss_theme_platform_owns_plugin_details' ) ) {
	/**
	 * Whether BuddyBoss Platform is running and answering plugin-information
	 * requests for BuddyBoss plugins.
	 *
	 * When it is, its handlers own the Plugins screen modal for every BuddyBoss
	 * plugin - active ones through their own plugins_api handlers, inactive ones
	 * through Platform's fallback - and render the release notes inline. The
	 * theme's handler below stands down entirely, so the two never race and an
	 * older Platform without the method is treated as absent.
	 *
	 * @since 2.22.2
	 *
	 * @return bool
	 */
	function buddyboss_theme_platform_owns_plugin_details() {
		if ( ! function_exists( 'buddypress' ) ) {
			return false;
		}

		$bp = buddypress();

		return isset( $bp->admin )
			&& is_object( $bp->admin )
			&& is_callable( array( $bp->admin, 'bb_plugins_api_addon_fallback' ) );
	}
}

if ( ! function_exists( 'buddyboss_theme_is_buddyboss_plugin' ) ) {
	/**
	 * Whether an installed plugin is a BuddyBoss product distributed from buddyboss.com.
	 *
	 * The same two-part rule Platform's fallback applies, so the two never
	 * disagree about whose modal to answer: authored by BuddyBoss, and either
	 * Platform itself or a plugin that declares Platform in "Requires Plugins".
	 * The second condition is what keeps a BuddyBoss plugin hosted on
	 * wordpress.org (BP Auto Group Join, say) resolving through wordpress.org.
	 *
	 * @since 2.22.2
	 *
	 * @param string $plugin_file Plugin basename, e.g. 'buddyboss-addons/buddyboss-addons.php'.
	 * @param array  $plugin_data Plugin headers as get_plugins() returns them.
	 *
	 * @return bool
	 */
	function buddyboss_theme_is_buddyboss_plugin( $plugin_file, $plugin_data ) {
		$author = ! empty( $plugin_data['AuthorName'] )
			? $plugin_data['AuthorName']
			: ( ! empty( $plugin_data['Author'] ) ? $plugin_data['Author'] : '' );

		if ( false === stripos( wp_strip_all_tags( (string) $author ), 'buddyboss' ) ) {
			return false;
		}

		$slug = dirname( (string) $plugin_file );

		if ( 'buddyboss-platform' === $slug ) {
			return true;
		}

		// Read the header directly: get_plugins() only exposes 'RequiresPlugins'
		// from WordPress 6.5, and the check has to hold on older sites too.
		$headers  = get_file_data( WP_PLUGIN_DIR . '/' . $plugin_file, array( 'RequiresPlugins' => 'Requires Plugins' ) );
		$requires = ! empty( $headers['RequiresPlugins'] )
			? array_map( 'trim', explode( ',', (string) $headers['RequiresPlugins'] ) )
			: array();

		/**
		 * Filters the BuddyBoss plugin directories recognized without a "Requires Plugins" header.
		 *
		 * Shared with Platform so one filter covers both handlers.
		 *
		 * @since 2.22.2
		 *
		 * @param array $slugs Plugin directory names.
		 */
		$known = (array) apply_filters( 'bb_known_buddyboss_addon_slugs', array( 'buddyboss-app' ) );

		return in_array( 'buddyboss-platform', $requires, true )
			|| in_array( $slug, array_map( 'sanitize_title', $known ), true );
	}
}

if ( ! function_exists( 'buddyboss_theme_find_buddyboss_plugin' ) ) {
	/**
	 * Find the installed BuddyBoss plugin living in the directory a plugins_api slug names.
	 *
	 * The slug comes off the request, so anything that is not a bare directory
	 * name is refused before it reaches the filesystem, and the directory is
	 * only read when its name could plausibly be a BuddyBoss product - every
	 * product ships in a directory starting with "buddyboss", and the filter
	 * is the way in for one installed under another name.
	 *
	 * @since 2.22.2
	 *
	 * @param string $slug Plugin directory name from the plugins_api request.
	 *
	 * @return array Empty when none; otherwise 'file' (plugin basename) and 'data' (headers).
	 */
	function buddyboss_theme_find_buddyboss_plugin( $slug ) {
		if (
			! is_string( $slug ) ||
			'' === $slug ||
			false !== strpbrk( $slug, '/\\' ) ||
			0 === strpos( $slug, '.' )
		) {
			return array();
		}

		/** This filter is documented in buddyboss_theme_is_buddyboss_plugin(). */
		$known    = array_map( 'sanitize_title', (array) apply_filters( 'bb_known_buddyboss_addon_slugs', array( 'buddyboss-app' ) ) );
		$possible = 0 === strpos( $slug, 'buddyboss' ) || in_array( $slug, $known, true );

		/**
		 * Filters whether a plugins_api slug could name a BuddyBoss plugin.
		 *
		 * Shared with Platform so one filter covers both handlers.
		 *
		 * @since 2.22.2
		 *
		 * @param bool   $possible Whether the directory name looks like a BuddyBoss product.
		 * @param string $slug     Plugin directory name.
		 */
		$possible = (bool) apply_filters( 'bb_possible_buddyboss_addon_slug', $possible, $slug );

		if ( ! $possible || ! is_dir( WP_PLUGIN_DIR . '/' . $slug ) ) {
			return array();
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( get_plugins( '/' . $slug ) as $basename => $plugin_data ) {
			// get_plugins() also lists one level of sub-directories; only a file
			// directly inside the directory can be the plugin the slug names.
			if ( false !== strpos( $basename, '/' ) ) {
				continue;
			}

			$plugin_file = $slug . '/' . $basename;

			if ( buddyboss_theme_is_buddyboss_plugin( $plugin_file, $plugin_data ) ) {
				return array(
					'file' => $plugin_file,
					'data' => $plugin_data,
				);
			}
		}

		return array();
	}
}

if ( ! function_exists( 'buddyboss_theme_plugin_release_notes_url' ) ) {
	/**
	 * Public release-notes page for a BuddyBoss plugin.
	 *
	 * Always an archive, never a single release: add-on release permalinks are
	 * not derivable from a version, and this handler fetches nothing, so it has
	 * no way to know which page exists. The archive always does. The maps use
	 * the same filters Platform applies to its own copies, so a site that adds
	 * a product adds it once.
	 *
	 * @since 2.22.2
	 *
	 * @param string $slug        Plugin directory name.
	 * @param array  $plugin_data Plugin headers, for the PluginURI fallback.
	 *
	 * @return string URL.
	 */
	function buddyboss_theme_plugin_release_notes_url( $slug, $plugin_data ) {
		$pages = array(
			'buddyboss-platform'     => 'https://buddyboss.com/resources/buddyboss-platform-releases/',
			'buddyboss-platform-pro' => 'https://buddyboss.com/resources/buddyboss-platform-pro-releases/',
		);

		/** This filter is documented in buddyboss-platform/src/bp-core/classes/class-bp-admin.php */
		$pages = (array) apply_filters( 'bb_addon_release_page_bases', $pages );

		if ( ! empty( $pages[ $slug ] ) ) {
			return trailingslashit( (string) $pages[ $slug ] );
		}

		$terms = array(
			'buddyboss-offload-media'   => 'buddyboss-offload-media-releases',
			'buddyboss-gamification'    => 'buddyboss-gamification-releases',
			'buddyboss-sharing'         => 'buddyboss-sharing-releases',
			'buddyboss-learndash'       => 'buddyboss-learndash',
			'buddyboss-addons'          => 'buddyboss-addons',
			'buddyboss-tools'           => 'buddyboss-tools',
			'buddyboss-member-blogging' => 'member-blogging',
		);

		/** This filter is documented in buddyboss-platform/src/bp-core/classes/class-bp-admin.php */
		$terms = (array) apply_filters( 'bb_addon_release_terms', $terms );

		if ( ! empty( $terms[ $slug ] ) ) {
			return 'https://buddyboss.com/resources/addons/' . sanitize_title( (string) $terms[ $slug ] ) . '/';
		}

		return ! empty( $plugin_data['PluginURI'] ) ? (string) $plugin_data['PluginURI'] : 'https://buddyboss.com/';
	}
}

if ( ! function_exists( 'buddyboss_theme_plugins_api_fallback' ) ) {
	/**
	 * Answer the Plugins screen "View details" modal for a BuddyBoss plugin when
	 * Platform is not running to do it.
	 *
	 * The theme bundles the same Mothership library Platform does, and that
	 * library injects an update entry - with a slug - for every licensed
	 * BuddyBoss plugin that is installed, active or not. WordPress builds a
	 * "View details" link from that slug and asks wordpress.org about it, which
	 * answers "Plugin not found". With Platform active, its handlers answer
	 * instead; with Platform inactive, this theme is the only BuddyBoss code
	 * guaranteed to be running, and whoever injects an entry has to be able to
	 * answer for it.
	 *
	 * Priority 999, so any product's own handler wins, and only for a request
	 * nothing else has answered. It reads plugin headers and the update entry
	 * and never fetches: release notes live on buddyboss.com, which refuses to
	 * be framed, so the changelog tab carries a link that opens in a new tab.
	 *
	 * @since 2.22.2
	 *
	 * @param false|object|array $result Result so far; false when unanswered.
	 * @param string             $action plugins_api() action.
	 * @param object             $args   Request arguments; the slug is $args->slug.
	 *
	 * @return false|object|array Plugin information object, or $result untouched.
	 */
	function buddyboss_theme_plugins_api_fallback( $result, $action, $args ) {
		if (
			'plugin_information' !== $action ||
			false !== $result ||
			! is_admin() ||
			! is_object( $args ) ||
			empty( $args->slug ) ||
			! is_string( $args->slug )
		) {
			return $result;
		}

		if ( buddyboss_theme_platform_owns_plugin_details() ) {
			return $result;
		}

		$plugin = buddyboss_theme_find_buddyboss_plugin( $args->slug );

		if ( empty( $plugin['file'] ) ) {
			return $result;
		}

		$plugin_file = $plugin['file'];
		$plugin_data = $plugin['data'];

		// Some third-party update managers rewrite transient entries as arrays;
		// read this one only when it is the object core wrote.
		$update_data = get_site_transient( 'update_plugins' );
		$update      = ( is_object( $update_data ) && isset( $update_data->response[ $plugin_file ] ) && is_object( $update_data->response[ $plugin_file ] ) )
			? $update_data->response[ $plugin_file ]
			: null;

		$version = ! empty( $update->new_version )
			? $update->new_version
			: ( ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : '' );

		// install_plugin_information() prints 'version' through a short kses
		// allowlist and 'name' with none, so both are plain text here.
		$version = is_scalar( $version ) ? preg_replace( '/[^0-9A-Za-z.+_-]/', '', substr( trim( (string) $version ), 0, 128 ) ) : '';
		$package = ! empty( $update->package ) && is_string( $update->package ) ? $update->package : '';

		$plugin_uri = ! empty( $plugin_data['PluginURI'] ) ? $plugin_data['PluginURI'] : 'https://buddyboss.com/';
		$author_uri = ! empty( $plugin_data['AuthorURI'] ) ? $plugin_data['AuthorURI'] : 'https://buddyboss.com/';
		$author     = ! empty( $plugin_data['Author'] ) ? wp_strip_all_tags( $plugin_data['Author'] ) : 'BuddyBoss';

		if ( ! empty( $update->last_updated ) && is_scalar( $update->last_updated ) ) {
			$last_updated = (string) $update->last_updated;
		} else {
			$path         = WP_PLUGIN_DIR . '/' . $plugin_file;
			$mtime        = file_exists( $path ) ? filemtime( $path ) : false;
			$last_updated = gmdate( 'Y-m-d H:i:s', false !== $mtime ? $mtime : time() );
		}

		/*
		 * links_add_target() forces target="_blank" on every link in this
		 * section, so the new tab is announced rather than left as a surprise;
		 * span[class] survives the installer's kses allowlist and
		 * 'screen-reader-text' is styled inside the modal's iframe.
		 */
		$changelog = sprintf(
			'<p>%1$s</p><p><a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s<span class="screen-reader-text"> %4$s</span></a></p>',
			esc_html__( 'Release notes for BuddyBoss plugins are published on buddyboss.com. Activate BuddyBoss Platform to read them here.', 'buddyboss-theme' ),
			esc_url( buddyboss_theme_plugin_release_notes_url( $args->slug, $plugin_data ) ),
			esc_html__( 'View the release notes on buddyboss.com', 'buddyboss-theme' ),
			esc_html__( '(opens in a new tab)', 'buddyboss-theme' )
		);

		$information = array(
			'name'          => wp_strip_all_tags( $plugin_data['Name'] ),
			'slug'          => $args->slug,
			'version'       => $version,
			'author'        => '<a href="' . esc_url( $author_uri ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $author ) . '</a>',
			'homepage'      => esc_url( $plugin_uri ),
			'last_updated'  => $last_updated,
			'sections'      => array(
				'description' => '<p>' . wp_kses_post( isset( $plugin_data['Description'] ) ? $plugin_data['Description'] : '' ) . '</p>',
				'changelog'   => $changelog,
			),
			'download_link' => ( '' !== $package && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) ? $package : '',
		);

		/**
		 * Filters the plugin information the theme serves for a BuddyBoss plugin
		 * when Platform is not running.
		 *
		 * Return plain text for 'name' and 'version': install_plugin_information()
		 * prints both with little or no escaping.
		 *
		 * @since 2.22.2
		 *
		 * @param array  $information Plugin information for the plugin-information modal.
		 * @param string $slug        Plugin directory name that was asked for.
		 * @param string $plugin_file Plugin basename the information describes.
		 */
		$information = apply_filters( 'buddyboss_theme_plugins_api_fallback_information', $information, $args->slug, $plugin_file );

		// A filter that hands back something other than the array leaves the
		// request unanswered rather than sending core a shape it cannot print.
		return is_array( $information ) ? (object) $information : $result;
	}
}
add_filter( 'plugins_api', 'buddyboss_theme_plugins_api_fallback', 999, 3 );

if ( ! function_exists( 'buddyboss_theme_strip_dependency_api_data' ) ) {
	/**
	 * Keep the licensed package URL out of the plugin-dependencies cache.
	 *
	 * WordPress 6.5+ calls plugins_api() for every "Requires Plugins" slug on
	 * each plugins.php load and stores the whole answer in the
	 * wp_plugin_dependencies_plugin_data site transient, which never expires.
	 * The fallback above answers those calls whenever Platform is not running,
	 * so without this the signed download URL from the update entry would sit
	 * in the options table indefinitely. Platform does the same for itself in
	 * BP_Admin::bb_strip_dependency_api_data(); this covers the case where
	 * Platform is not there to do it.
	 *
	 * @since 2.22.2
	 *
	 * @param mixed $value Dependency data about to be cached.
	 *
	 * @return mixed The data with download_link and sections removed from BuddyBoss entries.
	 */
	function buddyboss_theme_strip_dependency_api_data( $value ) {
		if ( ! is_array( $value ) || buddyboss_theme_platform_owns_plugin_details() ) {
			return $value;
		}

		foreach ( $value as $slug => $entry ) {
			// Core stores each entry as (array) $information; anything else was
			// not written from here and is left exactly as it was handed over.
			if ( ! is_array( $entry ) || ! is_string( $slug ) ) {
				continue;
			}

			if ( ! buddyboss_theme_find_buddyboss_plugin( $slug ) ) {
				continue;
			}

			unset( $value[ $slug ]['download_link'], $value[ $slug ]['sections'] );
		}

		return $value;
	}
}
add_filter( 'pre_set_site_transient_wp_plugin_dependencies_plugin_data', 'buddyboss_theme_strip_dependency_api_data' );
