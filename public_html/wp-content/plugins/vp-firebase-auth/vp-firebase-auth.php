<?php
/**
 * Plugin Name: VP Firebase Auth
 * Description: Firebase sign-in (mobile OTP, email + password, email link, Google, Facebook) for ShivBodh sites. Verifies the Firebase ID token on the server and signs the visitor into the matching WordPress/WooCommerce account. Administrators keep password login (plus Two Factor) on wp-login.php.
 * Version: 0.2.5
 * Requires PHP: 8.1
 * Author: ShivBodh Trust
 * Text Domain: vp-firebase-auth
 *
 * Optional wp-config.php constants:
 *   VP_FIREBASE_ROUTING        true/false  force login redirects on/off (default: on unless vp-clerk-auth is active)
 *   VP_FIREBASE_SIGN_IN_PATH   this site's login page (default /login-user/)
 *   VP_FIREBASE_SIGN_UP_PATH   this site's sign-up page (default same as sign-in)
 *   VP_FIREBASE_BRAND          name shown on the card
 *
 * @package VaranasiPooja\FirebaseAuth
 */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VP_FIREBASE_VERSION', '0.2.5' );
define( 'VP_FIREBASE_FILE', __FILE__ );
define( 'VP_FIREBASE_DIR', plugin_dir_path( __FILE__ ) );

require_once VP_FIREBASE_DIR . 'src/Config.php';
require_once VP_FIREBASE_DIR . 'src/Token.php';
require_once VP_FIREBASE_DIR . 'src/Accounts.php';
require_once VP_FIREBASE_DIR . 'src/Rest.php';
require_once VP_FIREBASE_DIR . 'src/Frontend.php';
require_once VP_FIREBASE_DIR . 'src/Routing.php';

add_action( 'plugins_loaded', static function (): void {
	Rest::register();
	Frontend::register();
	if ( Config::routing_enabled() ) {
		Routing::register();
	}
} );
