<?php
/**
 * ShivBodh Trust Community bridge (same file on every partner site).
 *
 * This site keeps no member list. The join box posts here, and the email is
 * forwarded server-to-server (HMAC-signed) to the hub on varanasipooja.com,
 * where every member is managed (VPC Dashboard > ShivBodh Trust Community >
 * Partner Sites). Failed forwards are queued and retried every hour.
 * The box also shows the daily Varanasi Pooja Panchanga from the hub; it flips
 * to the new day at 00:00 IST (cron purges the cached pages, script swaps the card).
 *
 * wp-config.php (never in Git):
 *   define( 'SHIVBODH_COMMUNITY_SITE', 'gangaaarti' | 'trust' | 'sampreshan' );
 *   define( 'SHIVBODH_COMMUNITY_SECRET', '<key from the hub>' );
 *
 * Use: shivbodh_community_banner( array( 'lang' => 'en', 'page' => 'home', 'accent' => '#255e7e' ) ) or [shivbodh_community].
 *
 * @version 1.0.0 (2026-10-27)
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ShivBodh_Community_Bridge' ) ) {

	final class ShivBodh_Community_Bridge {

		const VERSION = '1.0.0';
		const HUB     = 'https://varanasipooja.com/wp-json/vpc/v1/community/';
		const QUEUE   = 'sbc_queue';
		const PAGES   = 'sbc_pages';
		const TZ      = 'Asia/Kolkata';

		public static function init() {
			add_shortcode( 'shivbodh_community', array( __CLASS__, 'shortcode' ) );
			add_action( 'admin_post_nopriv_sbc_join', array( __CLASS__, 'handle' ) );
			add_action( 'admin_post_sbc_join', array( __CLASS__, 'handle' ) );
			add_action( 'sbc_retry', array( __CLASS__, 'retry' ) );
			add_action( 'sbc_midnight', array( __CLASS__, 'midnight' ) );
			add_action( 'init', array( __CLASS__, 'crons' ) );
		}

		public static function configured() {
			return defined( 'SHIVBODH_COMMUNITY_SITE' ) && defined( 'SHIVBODH_COMMUNITY_SECRET' ) && SHIVBODH_COMMUNITY_SECRET;
		}

		private static function now() {
			return new DateTimeImmutable( 'now', new DateTimeZone( self::TZ ) );
		}

		private static function next_midnight() {
			return self::now()->modify( 'tomorrow' )->getTimestamp() + 5;
		}

		public static function crons() {
			if ( ! wp_next_scheduled( 'sbc_midnight' ) ) {
				wp_schedule_event( self::next_midnight(), 'daily', 'sbc_midnight' );
			}
			if ( ! wp_next_scheduled( 'sbc_retry' ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'sbc_retry' );
			}
		}

		/* ------------------------------------------------------------ text */

		private static function t() {
			return array(
				'h'     => 'Join ShivBodh Trust Community',
				'p'     => 'Festival greetings, Kashi updates and the daily Varanasi Pooja Panchanga in your inbox. No spam, ever.',
				'ph'    => 'Enter your email',
				'btn'   => 'Join Community',
				'note'  => 'By joining you agree to receive emails from ShivBodh Trust Community. Unsubscribe anytime.',
				'ok'    => 'Pranam! You are now part of ShivBodh Trust Community. Please check your email.',
				'later' => 'Thank you! We have your email and will add you shortly.',
				'bad'   => 'Please enter a valid email and try again.',
				'email' => 'Email',
			);
		}

		/* ---------------------------------------------------------- banner */

		public static function shortcode( $atts ) {
			return self::banner( shortcode_atts( array( 'lang' => 'both', 'page' => '', 'accent' => '' ), (array) $atts ), false );
		}

		/**
		 * @param array $args lang 'en' (no Devanagari) | 'both'; page slug for the tag.
		 */
		public static function banner( $args = array(), $echo = true ) {
			$args   = wp_parse_args( $args, array( 'lang' => 'both', 'page' => '', 'accent' => '' ) );
			$accent = sanitize_hex_color( (string) $args['accent'] );
			$t      = self::t();
			$page   = sanitize_title( $args['page'] ? $args['page'] : ( is_front_page() ? 'home' : (string) get_post_field( 'post_name', get_queried_object_id() ) ) );
			$status = isset( $_GET['sbc'] ) ? sanitize_key( wp_unslash( $_GET['sbc'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$ref    = is_front_page() ? home_url( '/' ) : (string) get_permalink();
			self::remember( $ref );

			$html  = self::style();
			$html .= '<section class="sbc" id="sbc" aria-labelledby="sbc-h"' . ( $accent ? ' style="--sbc-ac:' . esc_attr( $accent ) . '"' : '' ) . '><div class="sbc__in">';
			$html .= '<div class="sbc__txt"><h2 id="sbc-h">' . esc_html( $t['h'] ) . '</h2><p>' . esc_html( $t['p'] ) . '</p></div>';
			if ( 'ok' === $status || 'later' === $status ) {
				$html .= '<p class="sbc__msg is-ok" role="status">' . esc_html( $t[ $status ] ) . '</p>';
			} else {
				$html .= '<form class="sbc__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
					. '<input type="hidden" name="action" value="sbc_join">'
					. '<input type="hidden" name="ts" value="" data-sbc-ts>'
					. '<input type="hidden" name="page" value="' . esc_attr( $page ) . '">'
					. '<input type="hidden" name="ref" value="' . esc_url( $ref ) . '">'
					. '<input type="text" name="website" class="sbc__hp" tabindex="-1" autocomplete="off" aria-hidden="true">'
					. '<label class="sbc__sr" for="sbc-email">' . esc_html( $t['email'] ) . '</label>'
					. '<input type="email" id="sbc-email" name="email" placeholder="' . esc_attr( $t['ph'] ) . '" autocomplete="email" required>'
					. '<button type="submit">' . esc_html( $t['btn'] ) . '</button>'
					. ( 'bad' === $status ? '<p class="sbc__msg is-error" role="alert">' . esc_html( $t['bad'] ) . '</p>' : '' )
					. '<p class="sbc__note">' . esc_html( $t['note'] ) . '</p></form>';
			}
			$html .= self::panchanga( 'en' === $args['lang'] ? 'en' : 'both' );
			$html .= '</div></section>' . self::script();
			if ( $echo ) {
				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts; hub HTML passes wp_kses.
				return '';
			}
			return $html;
		}

		/** Pages that carry the banner, so the midnight cron can refresh their cache. */
		private static function remember( $url ) {
			$pages = (array) get_option( self::PAGES, array() );
			if ( ! in_array( $url, $pages, true ) && count( $pages ) < 30 ) {
				$pages[] = $url;
				update_option( self::PAGES, $pages, false );
			}
		}

		/* ------------------------------------------------------- panchanga */

		private static function panchanga( $lang ) {
			$key  = 'sbc_panchanga_' . $lang;
			$html = get_transient( $key );
			if ( false === $html ) {
				$r    = wp_remote_get( self::HUB . 'daily-panchanga' . ( 'en' === $lang ? '?lang=en' : '' ), array( 'timeout' => 6 ) );
				$body = is_wp_error( $r ) || 200 !== (int) wp_remote_retrieve_response_code( $r ) ? null : json_decode( (string) wp_remote_retrieve_body( $r ), true );
				if ( is_array( $body ) && ! empty( $body['html'] ) ) {
					$html = wp_kses( (string) $body['html'], self::allowed() );
					set_transient( $key, $html, max( 300, (int) $body['expires'] - time() ) );
					update_option( $key . '_last', $html, false );
				} else {
					$html = (string) get_option( $key . '_last', '' );
					set_transient( $key, $html, 15 * MINUTE_IN_SECONDS );
				}
			}
			return $html ? '<div class="sbc__pan">' . $html . '</div>' : '';
		}

		private static function allowed() {
			$a                 = wp_kses_allowed_html( 'post' );
			$a['div']['hidden'] = true;
			$a['div']['data-vpcd-pday'] = true;
			$a['div']['data-vpcd-panchanga'] = true;
			$a['picture']      = array( 'class' => true );
			$a['source']       = array( 'srcset' => true, 'media' => true, 'type' => true, 'sizes' => true );
			$a['img']['srcset']   = true;
			$a['img']['sizes']    = true;
			$a['img']['loading']  = true;
			$a['img']['decoding'] = true;
			return $a;
		}

		/* -------------------------------------------------------- forwarding */

		public static function handle() {
			$p    = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- anonymous cached form; honeypot + timer + throttle.
			$ref  = wp_validate_redirect( esc_url_raw( (string) ( isset( $p['ref'] ) ? $p['ref'] : '' ) ), home_url( '/' ) );
			$back = function ( $s ) use ( $ref ) {
				wp_safe_redirect( add_query_arg( 'sbc', $s, $ref ) . '#sbc' );
				exit;
			};
			$ts = (int) ( isset( $p['ts'] ) ? $p['ts'] : 0 );
			$ip = md5( (string) ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ) );
			$n  = (int) get_transient( 'sbc_t_' . $ip );
			if ( ! empty( $p['website'] ) || ( $ts && time() - (int) ( $ts / 1000 ) < 3 ) || $n >= 5 ) {
				$back( 'bad' );
			}
			set_transient( 'sbc_t_' . $ip, $n + 1, HOUR_IN_SECONDS );
			$email = sanitize_email( (string) ( isset( $p['email'] ) ? $p['email'] : '' ) );
			if ( ! is_email( $email ) ) {
				$back( 'bad' );
			}
			$item = array(
				'email' => $email,
				'name'  => sanitize_text_field( (string) ( isset( $p['name'] ) ? $p['name'] : '' ) ),
				'page'  => sanitize_title( (string) ( isset( $p['page'] ) ? $p['page'] : '' ) ),
			);
			if ( self::send( $item ) ) {
				$back( 'ok' );
			}
			self::enqueue( $item );
			$back( 'later' );
		}

		/** Signed POST to the hub. True only on a 2xx answer. */
		public static function send( array $item ) {
			if ( ! self::configured() ) {
				return false;
			}
			$body = wp_json_encode( $item );
			$time = (string) time();
			$site = (string) SHIVBODH_COMMUNITY_SITE;
			$r    = wp_remote_post(
				self::HUB . 'partner-join',
				array(
					'timeout' => 8,
					'headers' => array(
						'Content-Type' => 'application/json',
						'X-VPC-Site'   => $site,
						'X-VPC-Time'   => $time,
						'X-VPC-Sign'   => hash_hmac( 'sha256', $site . "\n" . $time . "\n" . $body, (string) SHIVBODH_COMMUNITY_SECRET ),
					),
					'body'    => $body,
				)
			);
			$code = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
			return $code >= 200 && $code < 300;
		}

		private static function enqueue( array $item ) {
			$q   = (array) get_option( self::QUEUE, array() );
			$q[] = $item + array( 'at' => time() );
			update_option( self::QUEUE, array_slice( $q, -500 ), false );
		}

		public static function retry() {
			$q = (array) get_option( self::QUEUE, array() );
			if ( ! $q ) {
				return;
			}
			$left = array();
			foreach ( $q as $item ) {
				if ( ! self::send( $item ) ) {
					$left[] = $item;
				}
			}
			update_option( self::QUEUE, $left, false );
		}

		/** 00:00 IST: new day — drop the cached card and refresh the banner pages. */
		public static function midnight() {
			delete_transient( 'sbc_panchanga_en' );
			delete_transient( 'sbc_panchanga_both' );
			foreach ( (array) get_option( self::PAGES, array() ) as $u ) {
				do_action( 'litespeed_purge_url', $u );
			}
		}

		/* ---------------------------------------------------------- assets */

		private static function script() {
			static $done = false;
			if ( $done ) {
				return '';
			}
			$done = true;
			return '<script>(function(){function d(){return new Date(Date.now()+19800000).toISOString().slice(0,10)}'
				. 'function sw(){var t=d();document.querySelectorAll("[data-vpcd-panchanga]").forEach(function(b){var c=b.querySelectorAll("[data-vpcd-pday]"),h=false;c.forEach(function(x){if(x.getAttribute("data-vpcd-pday")===t)h=true});if(h)c.forEach(function(x){x.hidden=x.getAttribute("data-vpcd-pday")!==t})});'
				. 'setTimeout(sw,86400000-((Date.now()+19800000)%86400000)+2000)}'
				. 'function go(){document.querySelectorAll("[data-sbc-ts]").forEach(function(i){i.value=String(Date.now())});sw()}'
				. 'if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",go);else go()})();</script>';
		}

		private static function style() {
			static $done = false;
			if ( $done ) {
				return '';
			}
			$done = true;
			return '<style id="sbc-css">'
				. '.sbc{--sbc-ac:#9a3412;padding:28px 16px;box-sizing:border-box}.sbc *{box-sizing:border-box}'
				. '.sbc__in{max-width:1140px;margin:0 auto;padding:20px;display:flex;flex-direction:column;gap:16px;background:linear-gradient(135deg,#1a1a2e 0%,#0a0f1f 100%);border:1px solid rgba(255,204,112,.25);border-radius:14px;box-shadow:0 8px 32px rgba(10,15,31,.3);color:#fff}'
				. '.sbc h2{margin:0 0 4px;font-size:20px;line-height:1.3;color:#fff}.sbc__txt p{margin:0;font-size:14px;line-height:1.5;color:#d4d4de}'
				. '.sbc__form{display:flex;flex-wrap:wrap;gap:10px;margin:0}'
				. '.sbc__form input[type=email]{flex:1 1 220px;min-width:0;height:46px;padding:0 16px;font:inherit;font-size:16px;color:#fff;background:rgba(255,255,255,.07);border:1px solid rgba(255,204,112,.35);border-radius:30px}'
				. '.sbc__form input[type=email]::placeholder{color:rgba(255,255,255,.6)}.sbc__form input[type=email]:focus{outline:2px solid #ffcc70;outline-offset:2px}'
				. '.sbc__form button{flex:0 0 auto;min-height:46px;padding:0 24px;font:inherit;font-size:15px;font-weight:700;color:#1a1a2e;background:linear-gradient(135deg,#ffcc70,#f0a83c);border:0;border-radius:30px;cursor:pointer}'
				. '.sbc__form button:focus-visible{outline:2px solid #fff;outline-offset:2px}'
				. '.sbc__note{flex-basis:100%;margin:0;font-size:12.5px;color:#b9b9c8}.sbc__msg{margin:0;font-size:14px;font-weight:600}.sbc__msg.is-ok{color:#9be7b0}.sbc__msg.is-error{flex-basis:100%;color:#ffb4a8}'
				. '.sbc__hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}.sbc__sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}'
				. '.sbc .vpcd-pday{padding:16px 18px;background:#fffaf3;border:1px solid #ecd7bd;border-radius:12px;color:#2b1d14;text-align:left}.sbc .vpcd-pday[hidden]{display:none}'
				. '.sbc .vpcd-pday__head{display:flex;flex-wrap:wrap;justify-content:space-between;gap:4px 12px;align-items:baseline;margin-bottom:10px}'
				. '.sbc .vpcd-pday__brand{font-size:16px;font-weight:700;color:var(--sbc-ac)}.sbc .vpcd-pday__brand small{display:block;font-size:13px;font-weight:500}.sbc .vpcd-pday__date{font-size:14px;font-weight:600;color:#2b1d14}'
				. '.sbc .vpcd-pday__fest{display:flex;gap:8px;align-items:center;margin:0 0 10px;padding:8px 10px;font-size:14px;font-weight:600;color:#7c2a0e;background:#fdecd6;border-radius:8px}'
				. '.sbc .vpcd-pday__fest .vpcd-icon,.sbc .vpcd-pday__fest picture,.sbc .vpcd-pday__fest img{flex:0 0 auto;display:block;width:28px;height:28px;object-fit:contain}'
				. '.sbc .vpcd-pday__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:8px 16px;margin:0;padding:0;list-style:none}'
				. '.sbc .vpcd-pday__grid li{display:flex;flex-direction:column;margin:0;font-size:14px;line-height:1.35;color:#2b1d14}.sbc .vpcd-pday__grid span{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#8a6a52}'
				. '.sbc .vpcd-pday__more{display:inline-flex;align-items:center;min-height:44px;margin-top:6px;font-weight:700;color:var(--sbc-ac);text-decoration:none}.sbc .vpcd-pday__more:hover,.sbc .vpcd-pday__more:focus-visible{text-decoration:underline}'
				. '.sbc .vpcd-pday__note{margin:0;font-size:12px;color:#8a6a52}'
				. '@media(min-width:900px){.sbc__in{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.2fr);align-items:start;column-gap:24px}.sbc__txt,.sbc__form,.sbc__msg{grid-column:1}.sbc__pan{grid-column:2;grid-row:1/span 3}}'
				. '</style>';
		}
	}

	ShivBodh_Community_Bridge::init();

	/** Template helper: echo the community box with the daily Panchanga. */
	function shivbodh_community_banner( $args = array() ) {
		ShivBodh_Community_Bridge::banner( $args, true );
	}
}
