<?php
/** Firebase web config (public values) and site settings. */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Config {
	public const PROJECT_ID = 'shivbodh-projects';
	public const FACEBOOK_APP_ID = '1674680850271158';

	/** @return array<string,string> */
	public static function web_config(): array {
		return (array) apply_filters( 'vp_firebase_web_config', array(
			'apiKey'            => 'AIzaSyDUL5PNkbzQKyuBddpl28kvZXQBuI51kEw',
			'authDomain'        => 'shivbodh-projects.firebaseapp.com',
			'projectId'         => self::PROJECT_ID,
			'appId'             => '1:374979585393:web:c6e7ce41bd9759a118c2d0',
			'messagingSenderId' => '374979585393',
		) );
	}

	private static function setting( string $name ): string {
		$const = 'VP_FIREBASE_' . $name;
		return defined( $const ) ? trim( (string) constant( $const ) ) : '';
	}

	/** Redirect logins to Firebase pages. Off by default while vp-clerk-auth is still active. */
	public static function routing_enabled(): bool {
		if ( defined( 'VP_FIREBASE_ROUTING' ) ) {
			return (bool) VP_FIREBASE_ROUTING;
		}
		return ! class_exists( '\VaranasiPooja\Clerk\Config' );
	}

	public static function sign_in_path(): string {
		$path = self::setting( 'SIGN_IN_PATH' );
		return '' !== $path ? $path : '/login-user/';
	}

	public static function sign_up_path(): string {
		$path = self::setting( 'SIGN_UP_PATH' );
		return '' !== $path ? $path : self::sign_in_path();
	}

	public static function brand(): string {
		return sanitize_text_field( self::setting( 'BRAND' ) );
	}

	public static function site_host(): string {
		return strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	}

	/** Visual theme for the login card: pooja | aarti | sampreshan. */
	public static function theme(): string {
		$host = self::site_host();
		if ( false !== strpos( $host, 'gangaaarti' ) ) {
			return 'aarti';
		}
		if ( false !== strpos( $host, 'sampreshan' ) ) {
			return 'sampreshan';
		}
		return 'pooja';
	}

	/** Origins allowed in the REST Origin header. */
	public static function origins(): array {
		$parts  = wp_parse_url( home_url() );
		$host   = (string) ( $parts['host'] ?? '' );
		$scheme = (string) ( $parts['scheme'] ?? 'https' );
		$list   = array( $scheme . '://' . $host );
		if ( 0 !== strpos( $host, 'www.' ) ) {
			$list[] = $scheme . '://www.' . $host;
		} else {
			$list[] = $scheme . '://' . substr( $host, 4 );
		}
		return (array) apply_filters( 'vp_firebase_origins', $list );
	}
}
