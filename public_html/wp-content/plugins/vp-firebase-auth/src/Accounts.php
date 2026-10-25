<?php
/**
 * Map verified Firebase claims to a WordPress/WooCommerce account and sign it in.
 * Match order: saved Firebase uid, verified email, verified phone; otherwise create a customer.
 * Staff accounts (administrator, editor, shop manager...) are never signed in through Firebase.
 */

declare(strict_types=1);

namespace VaranasiPooja\FirebaseAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Accounts {
	public const META_UID = 'vp_firebase_uid';
	private const STAFF_CAPS = array( 'manage_options', 'edit_others_posts', 'manage_woocommerce', 'list_users' );

	public static function verified_email( array $claims ): string {
		if ( empty( $claims['email_verified'] ) ) {
			return '';
		}
		$email = strtolower( sanitize_email( (string) ( $claims['email'] ?? '' ) ) );
		return is_email( $email ) ? $email : '';
	}

	/** Phone as digits with country code (e.g. 919876543210), or ''. Firebase only issues it after OTP. */
	public static function verified_phone( array $claims ): string {
		$digits = (string) preg_replace( '/\D+/', '', (string) ( $claims['phone_number'] ?? '' ) );
		return ( strlen( $digits ) >= 11 && strlen( $digits ) <= 15 ) ? $digits : '';
	}

	public static function is_staff( \WP_User $user ): bool {
		foreach ( self::STAFF_CAPS as $cap ) {
			if ( user_can( $user, $cap ) ) {
				return true;
			}
		}
		return false;
	}

	/** @return \WP_User|\WP_Error */
	public static function resolve( array $claims ) {
		$uid   = (string) $claims['sub'];
		$email = self::verified_email( $claims );
		$phone = self::verified_phone( $claims );
		if ( '' === $email && '' === $phone ) {
			return new \WP_Error( 'vp_fb_unverified', __( 'Please verify your email or mobile number to continue.', 'vp-firebase-auth' ) );
		}

		$user  = null;
		$by_id = get_users( array( 'meta_key' => self::META_UID, 'meta_value' => $uid, 'number' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $by_id ) {
			$user = $by_id[0];
		}
		if ( ! $user && '' !== $email ) {
			$user = get_user_by( 'email', $email ) ?: null;
		}
		if ( ! $user && '' !== $phone ) {
			$user = class_exists( '\VaranasiPooja\VPC\Users' ) ? \VaranasiPooja\VPC\Users::by_phone( $phone ) : self::find_by_phone( $phone );
		}
		if ( ! $user ) {
			$user = self::create( $claims, $email, $phone );
			if ( is_wp_error( $user ) ) {
				return $user;
			}
		}
		if ( self::is_staff( $user ) ) {
			return new \WP_Error( 'vp_fb_staff', __( 'Staff accounts sign in with a password at the admin login.', 'vp-firebase-auth' ) );
		}

		update_user_meta( $user->ID, self::META_UID, $uid );
		if ( '' !== $phone && '' === self::phone_of( $user ) ) {
			update_user_meta( $user->ID, 'vpc_phone', $phone );
		}
		return $user;
	}

	public static function sign_in( \WP_User $user ): void {
		if ( class_exists( '\VaranasiPooja\VPC\Users' ) ) {
			\VaranasiPooja\VPC\Users::login( $user, true );
		} else {
			wp_clear_auth_cookie();
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
			do_action( 'wp_login', $user->user_login, $user );
		}
		update_user_meta( $user->ID, 'vp_firebase_last_login', time() );
		if ( class_exists( '\VaranasiPooja\VPC\Members' ) ) {
			\VaranasiPooja\VPC\Members::from_user( $user->ID, 'firebase', null );
		}
	}

	/** @return \WP_User|\WP_Error */
	private static function create( array $claims, string $email, string $phone ) {
		$name        = sanitize_text_field( (string) ( $claims['name'] ?? '' ) );
		$login_email = '' !== $email ? $email : 'fb-' . $phone . '@users.' . Config::site_host();
		if ( class_exists( '\VaranasiPooja\VPC\Users' ) ) {
			return \VaranasiPooja\VPC\Users::create( $login_email, $name, $phone, '', 'firebase' );
		}
		if ( ! function_exists( 'wc_create_new_customer' ) ) {
			$login = sanitize_user( (string) strtok( $login_email, '@' ), true );
			$login = ( '' === $login || username_exists( $login ) ) ? 'user_' . wp_generate_password( 10, false ) : $login;
			$id    = wp_insert_user( array(
				'user_login'   => $login,
				'user_email'   => $login_email,
				'user_pass'    => wp_generate_password( 24 ),
				'display_name' => '' !== $name ? $name : $login,
				'role'         => (string) get_option( 'default_role', 'subscriber' ),
			) );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			if ( '' !== $phone ) {
				update_user_meta( $id, 'vpc_phone', $phone );
			}
			return get_user_by( 'id', $id );
		}
		$id = wc_create_new_customer( $login_email, '', wp_generate_password( 24 ) );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		if ( '' !== $name ) {
			wp_update_user( array( 'ID' => $id, 'display_name' => $name ) );
		}
		if ( '' !== $phone ) {
			update_user_meta( $id, 'vpc_phone', $phone );
			update_user_meta( $id, 'billing_phone', '+' . $phone );
		}
		return get_user_by( 'id', $id );
	}

	private static function phone_of( \WP_User $user ): string {
		$raw    = (string) ( get_user_meta( $user->ID, 'vpc_phone', true ) ?: get_user_meta( $user->ID, 'billing_phone', true ) );
		$digits = (string) preg_replace( '/\D+/', '', $raw );
		return 10 === strlen( $digits ) ? '91' . $digits : $digits;
	}

	private static function find_by_phone( string $phone ): ?\WP_User {
		$found = get_users( array( 'meta_key' => 'vpc_phone', 'meta_value' => $phone, 'number' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		return $found ? $found[0] : null;
	}
}
