<?php
/**
 * Plugin Name: ShivBodh Security Hardening
 * Description: Restrict private REST profiles and add compatible browser security headers.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

add_action('send_headers', 'sb_security_browser_headers', 1000);
function sb_security_browser_headers() {
    if (headers_sent()) {
        return;
    }
    $present = array();
    foreach (headers_list() as $line) {
        $present[strtolower(strtok($line, ':'))] = true;
    }
    $headers = array(
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        // Leave script/connect/frame sources unrestricted for WordPress and payment integrations.
        'Content-Security-Policy' => "base-uri 'self'; object-src 'none'; frame-ancestors 'self'",
    );
    if (is_ssl()) {
        $headers['Strict-Transport-Security'] = 'max-age=31536000';
    }
    foreach ($headers as $name => $value) {
        if (!isset($present[strtolower($name)])) {
            header($name . ': ' . $value);
        }
    }
}

add_filter('rest_pre_dispatch', 'sb_security_private_rest_users', 10, 3);
function sb_security_private_rest_users($response, $server, $request) {
    if (!preg_match('#^/wp/v2/users(?:/([^/]+))?(?:/|$)#i', $request->get_route(), $match)) {
        return $response;
    }
    if (current_user_can('list_users')) {
        return $response;
    }
    $target = isset($match[1]) ? $match[1] : '';
    $own_profile = is_user_logged_in() && (
        $target === 'me' || (ctype_digit($target) && (int) $target === get_current_user_id())
    );
    if ($own_profile) {
        return $response;
    }
    return new WP_Error('sb_private_users', 'User profiles require appropriate authentication.', array(
        'status' => is_user_logged_in() ? 403 : 401,
    ));
}
