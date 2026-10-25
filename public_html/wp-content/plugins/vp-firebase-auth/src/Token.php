<?php
/** Firebase ID token (RS256 JWT) verification against Google's securetoken JWKS. */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Token {
	private const LEEWAY    = 10;
	private const JWKS_URL  = 'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com';
	private const CACHE_KEY = 'vp_firebase_jwks';

	/** @return array<string,mixed>|\WP_Error Verified claims. */
	public static function verify( string $jwt ) {
		$parts = explode( '.', $jwt );
		if ( 3 !== count( $parts ) ) {
			return new \WP_Error( 'vp_fb_token', 'Malformed token.' );
		}
		$header = json_decode( (string) self::b64url( $parts[0] ), true );
		$claims = json_decode( (string) self::b64url( $parts[1] ), true );
		$sig    = self::b64url( $parts[2] );
		if ( ! is_array( $header ) || ! is_array( $claims ) || false === $sig ) {
			return new \WP_Error( 'vp_fb_token', 'Malformed token.' );
		}
		if ( 'RS256' !== ( $header['alg'] ?? '' ) || empty( $header['kid'] ) ) {
			return new \WP_Error( 'vp_fb_token', 'Unexpected token algorithm.' );
		}
		$pem = self::key_for( (string) $header['kid'] );
		if ( '' === $pem ) {
			return new \WP_Error( 'vp_fb_token', 'Unknown signing key.' );
		}
		if ( 1 !== openssl_verify( $parts[0] . '.' . $parts[1], $sig, $pem, OPENSSL_ALGO_SHA256 ) ) {
			return new \WP_Error( 'vp_fb_token', 'Invalid token signature.' );
		}
		$now = time();
		if ( ! isset( $claims['exp'] ) || (int) $claims['exp'] < $now - self::LEEWAY ) {
			return new \WP_Error( 'vp_fb_token', 'Token expired.' );
		}
		if ( ! isset( $claims['iat'] ) || (int) $claims['iat'] > $now + self::LEEWAY ) {
			return new \WP_Error( 'vp_fb_token', 'Token not valid yet.' );
		}
		if ( ( $claims['aud'] ?? '' ) !== Config::PROJECT_ID || ( $claims['iss'] ?? '' ) !== 'https://securetoken.google.com/' . Config::PROJECT_ID ) {
			return new \WP_Error( 'vp_fb_token', 'Token was issued for another project.' );
		}
		if ( empty( $claims['sub'] ) || ! is_string( $claims['sub'] ) || strlen( $claims['sub'] ) > 128 ) {
			return new \WP_Error( 'vp_fb_token', 'Missing subject.' );
		}
		return $claims;
	}

	private static function key_for( string $kid ): string {
		$keys = get_transient( self::CACHE_KEY );
		if ( ! is_array( $keys ) || ! isset( $keys[ $kid ] ) ) {
			$keys = self::fetch_jwks();
		}
		return (string) ( $keys[ $kid ] ?? '' );
	}

	/** @return array<string,string> kid => PEM */
	private static function fetch_jwks(): array {
		if ( get_transient( self::CACHE_KEY . '_lock' ) ) {
			$cached = get_transient( self::CACHE_KEY );
			return is_array( $cached ) ? $cached : array();
		}
		set_transient( self::CACHE_KEY . '_lock', 1, 30 );
		$response = wp_remote_get( self::JWKS_URL, array( 'timeout' => 10 ) );
		$keys     = array();
		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );
			foreach ( (array) ( $data['keys'] ?? array() ) as $jwk ) {
				if ( 'RSA' === ( $jwk['kty'] ?? '' ) && ! empty( $jwk['kid'] ) && ! empty( $jwk['n'] ) && ! empty( $jwk['e'] ) ) {
					$pem = self::rsa_pem( (string) $jwk['n'], (string) $jwk['e'] );
					if ( '' !== $pem ) {
						$keys[ (string) $jwk['kid'] ] = $pem;
					}
				}
			}
		}
		if ( $keys ) {
			set_transient( self::CACHE_KEY, $keys, 6 * HOUR_IN_SECONDS );
		}
		return $keys;
	}

	private static function rsa_pem( string $n, string $e ): string {
		$mod = self::b64url( $n );
		$exp = self::b64url( $e );
		if ( false === $mod || false === $exp || '' === $mod || '' === $exp ) {
			return '';
		}
		$int = static function ( string $bytes ): string {
			if ( ord( $bytes[0] ) > 0x7f ) {
				$bytes = "\x00" . $bytes;
			}
			return "\x02" . self::der_len( strlen( $bytes ) ) . $bytes;
		};
		$rsa  = $int( $mod ) . $int( $exp );
		$rsa  = "\x30" . self::der_len( strlen( $rsa ) ) . $rsa;
		$bits = "\x03" . self::der_len( strlen( $rsa ) + 1 ) . "\x00" . $rsa;
		$alg  = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
		$spki = "\x30" . self::der_len( strlen( $alg . $bits ) ) . $alg . $bits;
		return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----\n";
	}

	private static function der_len( int $len ): string {
		if ( $len < 0x80 ) {
			return chr( $len );
		}
		$bytes = ltrim( pack( 'N', $len ), "\x00" );
		return chr( 0x80 | strlen( $bytes ) ) . $bytes;
	}

	/** @return string|false */
	private static function b64url( string $data ) {
		$data = strtr( $data, '-_', '+/' );
		$pad  = strlen( $data ) % 4;
		if ( $pad ) {
			$data .= str_repeat( '=', 4 - $pad );
		}
		return base64_decode( $data, true );
	}
}
