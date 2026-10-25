<?php
/** Send customer login, registration and password-reset entry points to the Firebase login page. wp-login.php keeps working for staff. */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Routing {
	public static function sign_in_url(): string {
		return home_url( Config::sign_in_path() );
	}

	public static function sign_up_url(): string {
		return home_url( Config::sign_up_path() );
	}

	public static function register(): void {
		add_filter( 'the_content', array( __CLASS__, 'account_page' ), 5 );
		add_filter( 'register_url', array( __CLASS__, 'sign_up_url' ) );
		add_filter( 'lostpassword_url', array( __CLASS__, 'sign_in_url' ) );
		add_action( 'login_init', array( __CLASS__, 'wp_login_actions' ) );
		add_action( 'template_redirect', array( __CLASS__, 'alias_pages' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'no_cache' ), 2 );
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'checkout_login' ), 1 );
		add_filter( 'woocommerce_checkout_must_be_logged_in_message', array( __CLASS__, 'checkout_must_log_in' ) );
	}

	/** Missing /signup/ /sign-up/ /register/ URLs open the themed signup card. */
	public static function alias_pages(): void {
		if ( is_admin() || ! is_404() ) {
			return;
		}
		$path = strtolower( untrailingslashit( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( preg_match( '#/(register|registration|sign-up|signup)$#', $path ) ) {
			wp_safe_redirect( self::sign_up_url(), 301 );
			exit;
		}
		if ( preg_match( '#/(login|login-user|signin|sign-in)$#', $path ) ) {
			wp_safe_redirect( self::sign_in_url(), 301 );
			exit;
		}
	}

	/** Login and signup pages must not stay in LiteSpeed / Cloudflare HTML cache. */
	public static function no_cache(): void {
		if ( is_admin() ) {
			return;
		}
		$path = strtolower( untrailingslashit( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! preg_match( '#/(login|login-user|signin|sign-in|register|registration|sign-up|signup)$#', $path ) ) {
			return;
		}
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		do_action( 'litespeed_control_set_nocache', 'vp-firebase-auth' );
	}

	private static function checkout_sign_in_url(): string {
		return add_query_arg( 'redirect_to', rawurlencode( wc_get_checkout_url() ), self::sign_in_url() );
	}

	public static function checkout_login(): void {
		if ( is_user_logged_in() ) {
			return;
		}
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		wc_print_notice(
			esc_html__( 'Returning customer?', 'vp-firebase-auth' ) . ' <a href="' . esc_url( self::checkout_sign_in_url() ) . '">' . esc_html__( 'Sign in here', 'vp-firebase-auth' ) . '</a>',
			'notice'
		);
	}

	public static function checkout_must_log_in(): string {
		return esc_html__( 'Please sign in to complete your booking.', 'vp-firebase-auth' ) . ' <a href="' . esc_url( self::checkout_sign_in_url() ) . '">' . esc_html__( 'Sign in', 'vp-firebase-auth' ) . '</a>';
	}

	public static function account_page( $content ) {
		if ( is_user_logged_in() || ! function_exists( 'is_account_page' ) || ! is_account_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$intro = '<p class="vp-auth__intro">' . esc_html__( 'Sign in to see your bookings, orders and Kundli details. New here? An account is created for you automatically.', 'vp-firebase-auth' ) . '</p>';
		return $intro . '[vp_login redirect="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '"]';
	}

	public static function wp_login_actions(): void {
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'register' === $action ) {
			wp_safe_redirect( self::sign_up_url() );
			exit;
		}
		if ( in_array( $action, array( 'lostpassword', 'retrievepassword', 'rp', 'resetpass' ), true ) ) {
			wp_safe_redirect( self::sign_in_url() );
			exit;
		}
	}
}
