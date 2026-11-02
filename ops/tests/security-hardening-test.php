<?php
// Isolated authorization and IP-spoofing regression checks; no live WordPress is loaded.
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
$uid = 0;
$staff = false;
$transients = array();
function add_action(...$args) {}
function add_filter(...$args) {}
function current_user_can($cap) { return $GLOBALS['staff']; }
function is_user_logged_in() { return $GLOBALS['uid'] > 0; }
function get_current_user_id() { return $GLOBALS['uid']; }
function wp_salt($type) { return 'isolated-fixture-salt'; }
function get_transient($key) { return $GLOBALS['transients'][$key] ?? 0; }
function set_transient($key, $value, $ttl) { $GLOBALS['transients'][$key] = $value; }
class WP_Error {
    public $data;
    public function __construct($code, $message, $data) { $this->data = $data; }
}
class RequestFixture {
    private $route;
    public function __construct($route) { $this->route = $route; }
    public function get_route() { return $this->route; }
}
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
require __DIR__ . '/../../wp-content/mu-plugins/sb-security-hardening.php';
require __DIR__ . '/../../wp-content/plugins/vp-firebase-auth/src/Rest.php';
foreach (array('/wp/v2/users', '/wp/v2/users/1', '/wp/v2/users/me') as $route) {
    $r = sb_security_private_rest_users(null, null, new RequestFixture($route));
    check($r instanceof WP_Error && $r->data['status'] === 401, 'Anonymous user access must be denied.');
}
$uid = 7;
foreach (array('/wp/v2/users', '/wp/v2/users/8') as $route) {
    $r = sb_security_private_rest_users(null, null, new RequestFixture($route));
    check($r instanceof WP_Error && $r->data['status'] === 403, 'Non-staff cannot list other users.');
}
foreach (array('/wp/v2/users/me', '/wp/v2/users/7', '/wp/v2/users/7/application-passwords', '/wp/v2/posts') as $route) {
    check(sb_security_private_rest_users('existing', null, new RequestFixture($route)) === 'existing', 'Own/unrelated resources must preserve core authorization.');
}
$staff = true;
check(sb_security_private_rest_users('existing', null, new RequestFixture('/wp/v2/users')) === 'existing', 'Staff enumeration must remain available.');
$method = new ReflectionMethod('VaranasiPooja\\FirebaseAuth\\Rest', 'rate_limit');
$_SERVER['REMOTE_ADDR'] = '192.0.2.7';
for ($i = 0; $i < 30; $i++) {
    $_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.' . $i;
    check($method->invoke(null) === true, 'Initial permitted requests should pass.');
}
$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.99';
check($method->invoke(null) === false, 'Spoofed proxy header must not reset the rate limit.');
$_SERVER['REMOTE_ADDR'] = '192.0.2.8';
check($method->invoke(null) === true, 'Independent remote clients must have independent buckets.');
echo "PASS: REST ownership and spoofed-proxy rate-limit regression checks\n";
