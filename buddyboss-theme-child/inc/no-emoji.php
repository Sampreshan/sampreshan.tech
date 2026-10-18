<?php
/**
 * No-emoji policy (docs/ICON-RULES.md).
 *
 * Emoji in post content are replaced by the matching SampreShan kit icon;
 * emoji with no counterpart are removed. Titles and excerpts are stripped,
 * since an <img> cannot live in a <title> or attribute.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_emoji_pattern() {
    return '/(?:[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B05}-\x{2B55}\x{2934}\x{2935}\x{3030}\x{303D}\x{3297}\x{3299}])(?:[\x{1F3FB}-\x{1F3FF}]|\x{FE0F}|\x{200D}[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]\x{FE0F}?)*/u';
}

/**
 * Emoji code point => kit icon (assets/icons/kit) or legacy SVG (assets/icons/3d, '.svg').
 */
function sp_emoji_icon_map() {
    // Keys are Unicode code points (hex), so this file itself stays emoji-free.
    return array(
        '1F549' => 'sym-om',
        '1F531' => 'sym-trishul',
        '1FA94' => 'sym-diya',
        '1F514' => 'nav-notifications',
        '1F41A' => 'sym-shankh',
        '1F33A' => 'sym-lotus',
        '1FAB7' => 'sym-lotus',
        '1F338' => 'sym-lotus',
        '1F33F' => 'seva-environment',
        '1F331' => 'seva-environment',
        '1F6D5' => 'nav-peeth',
        '1F3DB' => 'seva-temple-heritage',
        '1F5E3' => 'nav-awareness',
        '1F4E2' => 'nav-awareness',
        '1F4E3' => 'nav-awareness',
        '1F4D6' => 'nav-directory',
        '1F4DA' => 'seva-education',
        '1F393' => 'seva-education',
        '1F91D' => 'com-friends',
        '1F465' => 'com-group',
        '1F9D1' => 'nav-profile',
        '1F464' => 'nav-profile',
        '1F50D' => 'nav-search',
        '1F50E' => 'nav-search',
        '2699' => 'nav-settings',
        '1F3E0' => 'nav-home',
        '1F4CB' => 'nav-petition',
        '1F4D1' => 'nav-petition',
        '1F4DC' => 'nav-petition',
        '1F4DD' => 'act-sign',
        '270D' => 'act-sign',
        '2705' => 'st-success',
        '2714' => 'st-success',
        '2713' => 'st-success',
        '1F6AB' => 'st-rejected',
        '26D4' => 'st-rejected',
        '2717' => 'st-rejected',
        '274C' => 'st-rejected',
        '26A0' => 'st-warning',
        '1F4A1' => 'st-info',
        '2139' => 'st-info',
        '1F31F' => 'badge-pratham',
        '2B50' => 'badge-pratham',
        '1F4CA' => 'st-trending',
        '1F4C8' => 'st-trending',
        '2709' => 'nav-messages',
        '1F4E7' => 'nav-messages',
        '1F4E8' => 'nav-messages',
        '1F4AC' => 'act-comment',
        '1F4C5' => 'nav-calendar',
        '1F5D3' => 'nav-calendar',
        '1F5BC' => 'com-photo',
        '1F4F7' => 'com-photo',
        '1F3A5' => 'com-video',
        '1F4B3' => 'act-donate',
        '1F932' => 'act-donate',
        '1F5D1' => 'act-delete',
        '1F4E4' => 'act-upload',
        '1F4E5' => 'act-download',
        '2696' => 'seva-legal',
        '1F404' => 'seva-goseva',
        '1F30A' => 'seva-ganga',
        '1F511' => 'lock.svg',
        '1F512' => 'lock.svg',
        '1F6E1' => 'shield.svg',
        '1F30D' => 'globe.svg',
        '1F310' => 'globe.svg',
        '1F4CD' => 'location.svg',
        '1F3AF' => 'target.svg',
        '1F517' => 'link.svg',
    );
}

function sp_emoji_icon_html( $icon ) {
    $base = get_stylesheet_directory_uri() . '/assets/icons/';
    if ( '.svg' === substr( $icon, -4 ) ) {
        $src = $base . '3d/' . $icon;
        $set = '';
    } else {
        $src = $base . 'kit/' . $icon . '-64.webp';
        $set = ' srcset="' . esc_url( $src ) . ' 1x, ' . esc_url( $base . 'kit/' . $icon . '-128.webp' ) . ' 2x"';
    }
    return '<img class="sp-inline-icon" src="' . esc_url( $src ) . '"' . $set . ' width="20" height="20" alt="" aria-hidden="true" loading="lazy" decoding="async" />';
}

/**
 * Replace emoji in HTML with kit icons; drop the rest.
 */
function sp_replace_emoji( $html ) {
    if ( ! is_string( $html ) || '' === $html || ! preg_match( sp_emoji_pattern(), $html ) ) {
        return $html;
    }
    $map = sp_emoji_icon_map();
    return preg_replace_callback(
        sp_emoji_pattern(),
        function ( $m ) use ( $map ) {
            $base = preg_replace( '/[\x{FE0F}\x{1F3FB}-\x{1F3FF}]|\x{200D}.*/u', '', $m[0] );
            $key  = '' === $base ? '' : strtoupper( dechex( mb_ord( $base, 'UTF-8' ) ) );
            return isset( $map[ $key ] ) ? sp_emoji_icon_html( $map[ $key ] ) : '';
        },
        $html
    );
}

function sp_strip_emoji( $text ) {
    if ( ! is_string( $text ) || '' === $text ) {
        return $text;
    }
    return trim( preg_replace( '/\s{2,}/u', ' ', preg_replace( sp_emoji_pattern(), '', $text ) ) );
}

add_filter( 'the_content', 'sp_replace_emoji', 20 );
add_filter( 'comment_text', 'sp_replace_emoji', 20 );
add_filter( 'widget_text_content', 'sp_replace_emoji', 20 );
add_filter( 'the_title', 'sp_strip_emoji', 20 );
add_filter( 'get_the_excerpt', 'sp_strip_emoji', 20 );
add_filter( 'nav_menu_item_title', 'sp_strip_emoji', 20 );
add_filter( 'document_title', 'sp_strip_emoji', 20 );

// WordPress' own emoji script swaps emoji for Twemoji images; we never want those.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
