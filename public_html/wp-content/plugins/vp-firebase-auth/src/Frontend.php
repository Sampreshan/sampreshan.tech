<?php
/** [vp_login] shortcode and assets. */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Frontend {
	private static bool $needed  = false;
	private static bool $printed = false;

	public static function register(): void {
		add_shortcode( 'vp_login', static fn( $atts ) => self::mount( (array) $atts ) );
		// Take over the old Clerk shortcodes once Clerk is gone, so existing pages keep working.
		add_action( 'init', static function (): void {
			foreach ( array( 'vp_clerk_sign_in', 'vp_clerk_sign_up' ) as $tag ) {
				if ( ! shortcode_exists( $tag ) ) {
					add_shortcode( $tag, static fn( $atts ) => self::mount( (array) $atts ) );
				}
			}
		}, 20 );
		add_action( 'wp_footer', array( __CLASS__, 'print_assets' ), 5 );
		add_action( 'shutdown', array( __CLASS__, 'print_assets' ), 0 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'script_attributes' ), 10, 2 );
	}

	private static function mount( array $atts ): string {
		self::$needed = true;
		$atts     = shortcode_atts(
			array(
				'redirect' => '',
				'mode'     => '',
			),
			$atts,
			'vp_login'
		);
		$redirect = (string) $atts['redirect'];
		if ( '' === $redirect && isset( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$redirect = (string) wp_unslash( $_GET['redirect_to'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}
		$redirect = wp_validate_redirect( esc_url_raw( $redirect ), function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) );
		$mode     = sanitize_key( (string) $atts['mode'] );
		if ( ! in_array( $mode, array( 'signin', 'signup' ), true ) ) {
			$path = strtolower( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$mode = preg_match( '#/(register|registration|sign-up|signup)/?#', $path ) ? 'signup' : 'signin';
		}
		$theme = Config::theme();
		// Print assets next to the mount so themes that skip wp_footer still work.
		return sprintf(
			'<div class="vp-auth" data-vp-auth data-mode="%1$s" data-vp-theme="%2$s" data-redirect="%3$s"><p class="vp-auth__loading">%4$s</p><noscript>%5$s</noscript></div>%6$s',
			esc_attr( $mode ),
			esc_attr( $theme ),
			esc_url( $redirect ),
			esc_html__( 'Loading secure sign-in…', 'vp-firebase-auth' ),
			esc_html__( 'Please enable JavaScript to sign in.', 'vp-firebase-auth' ),
			self::asset_html()
		);
	}

	/** @return array<string,mixed> */
	private static function boot(): array {
		return array(
			'firebase'      => Config::web_config(),
			'session'       => esc_url_raw( rest_url( 'vp-auth/v1/session' ) ),
			'signIn'        => esc_url_raw( home_url( Config::sign_in_path() ) ),
			'signUp'        => esc_url_raw( home_url( Config::sign_up_path() ) ),
			'account'       => esc_url_raw( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ),
			'brand'         => Config::brand() ?: 'Varanasi Pooja',
			'theme'         => Config::theme(),
			'facebookAppId' => Config::FACEBOOK_APP_ID,
		);
	}

	private static function asset_html(): string {
		if ( self::$printed ) {
			return '';
		}
		self::$printed = true;
		$base = plugin_dir_url( VP_FIREBASE_FILE ) . 'assets/';
		$css  = esc_url( $base . 'login.css?ver=' . VP_FIREBASE_VERSION );
		$js   = esc_url( $base . 'login.js?ver=' . VP_FIREBASE_VERSION );
		$cfg  = wp_json_encode( self::boot(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP );
		$fb = esc_attr( Config::FACEBOOK_APP_ID );
		return '<link rel="stylesheet" href="' . $css . '">'
			. '<script data-no-optimize="1" data-no-defer="1" data-cfasync="false">window.VPAuth=' . $cfg . ';window.fbAsyncInit=function(){if(window.FB){window.FB.init({appId:"' . $fb . '",cookie:true,xfbml:false,version:"v21.0"});}};</script>'
			. '<script src="https://connect.facebook.net/en_US/sdk.js" async defer crossorigin="anonymous" data-no-optimize="1" data-no-defer="1" data-cfasync="false"></script>'
			. '<script src="' . $js . '" data-no-optimize="1" data-no-defer="1" data-cfasync="false"></script>';
	}

	public static function print_assets(): void {
		if ( ! self::$needed || self::$printed ) {
			return;
		}
		echo self::asset_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/** Keep the bundle out of minify/defer/Rocket Loader. */
	public static function script_attributes( string $tag, string $handle ): string {
		if ( 'vp-firebase-auth' !== $handle ) {
			return $tag;
		}
		return str_replace( '<script ', '<script data-no-optimize="1" data-no-defer="1" data-cfasync="false" ', $tag );
	}
}
