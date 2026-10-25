<?php
/** REST bridge: exchange a verified Firebase ID token for a WordPress session. */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Rest {
	private const NS = 'vp-auth/v1';

	public static function register(): void {
		add_action( 'rest_api_init', static function (): void {
			register_rest_route( self::NS, '/state', array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'state' ),
				'permission_callback' => '__return_true',
			) );
			register_rest_route( self::NS, '/session', array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'session' ),
				'permission_callback' => array( __CLASS__, 'same_origin' ),
			) );
		} );
	}

	public static function same_origin( \WP_REST_Request $request ): bool {
		$allowed = Config::origins();
		$origin  = rtrim( (string) $request->get_header( 'origin' ), '/' );
		if ( '' !== $origin && in_array( $origin, $allowed, true ) ) {
			return true;
		}
		$site = strtolower( (string) $request->get_header( 'sec-fetch-site' ) );
		if ( in_array( $site, array( 'same-origin', 'same-site' ), true ) ) {
			return true;
		}
		$referer = (string) $request->get_header( 'referer' );
		if ( '' === $referer ) {
			return false;
		}
		$parts = wp_parse_url( $referer );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return false;
		}
		$from = $parts['scheme'] . '://' . $parts['host'];
		if ( ! empty( $parts['port'] ) ) {
			$from .= ':' . $parts['port'];
		}
		return in_array( rtrim( $from, '/' ), $allowed, true );
	}

	/** Who is signed in to WordPress (never cached). */
	public static function state(): \WP_REST_Response {
		$user_id = (int) wp_validate_auth_cookie( '', 'logged_in' );
		$user    = $user_id ? get_user_by( 'id', $user_id ) : null;
		$data    = array( 'loggedIn' => (bool) $user );
		if ( $user ) {
			$data['name']  = $user->display_name;
			$data['staff'] = Accounts::is_staff( $user );
		}
		return self::response( $data );
	}

	public static function session( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! self::rate_limit() ) {
			return self::response( array( 'error' => 'rate_limited', 'message' => __( 'Too many attempts. Please wait a few minutes.', 'vp-firebase-auth' ) ), 429 );
		}
		$header = (string) $request->get_header( 'authorization' );
		$token  = 0 === stripos( $header, 'Bearer ' ) ? trim( substr( $header, 7 ) ) : '';
		if ( '' === $token ) {
			$token = trim( (string) $request->get_param( 'idToken' ) );
		}
		if ( '' === $token || strlen( $token ) > 4096 ) {
			return self::response( array( 'error' => 'no_token', 'message' => __( 'We could not confirm your sign-in. Please request a new code and try again.', 'vp-firebase-auth' ) ), 401 );
		}
		$claims = Token::verify( $token );
		if ( is_wp_error( $claims ) ) {
			return self::response( array( 'error' => 'invalid_token', 'message' => __( 'Your sign-in expired. Please request a new code and try again.', 'vp-firebase-auth' ) ), 401 );
		}
		$user = Accounts::resolve( $claims );
		if ( is_wp_error( $user ) ) {
			return self::response( array( 'error' => $user->get_error_code(), 'message' => $user->get_error_message() ), 403 );
		}
		$current = wp_get_current_user();
		if ( ! $current->exists() || $current->ID !== $user->ID ) {
			Accounts::sign_in( $user );
		}
		$redirect = wp_validate_redirect( esc_url_raw( (string) $request->get_param( 'redirect' ) ), '' );
		return self::response( array( 'ok' => true, 'redirect' => $redirect ) );
	}

	private static function rate_limit(): bool {
		$ip  = (string) ( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$key = 'vp_fb_rl_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 32 );
		$n   = (int) get_transient( $key );
		if ( $n >= 30 ) {
			return false;
		}
		set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );
		return true;
	}

	private static function response( array $data, int $status = 200 ): \WP_REST_Response {
		do_action( 'litespeed_control_set_nocache', 'vp-firebase auth' );
		return new \WP_REST_Response( $data, $status, array( 'Cache-Control' => 'no-store, private', 'X-LiteSpeed-Cache-Control' => 'no-cache' ) );
	}
}
