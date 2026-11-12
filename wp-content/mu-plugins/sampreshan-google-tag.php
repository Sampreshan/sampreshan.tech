<?php
/**
 * Plugin Name: Sampreshan Google Tag
 * Description: Loads the Google tag (GA4 G-TXRBLN47KQ + Google Ads AW-16725841100) for visitors and sends call, WhatsApp and email click events.
 * Version: 1.0.0
 *
 * Logged-in members and admins are not measured. Kill switch: wp option update sampreshan_gtag_mode off
 */
if (!defined('ABSPATH')) {
    exit;
}

const SAMPRESHAN_GA4_ID = 'G-TXRBLN47KQ';
const SAMPRESHAN_ADS_ID = 'AW-16725841100';

function sampreshan_gtag_enabled(): bool
{
    return 'off' !== get_option('sampreshan_gtag_mode', 'on') && !is_admin() && !is_user_logged_in();
}

add_action('wp_head', function () {
    if (!sampreshan_gtag_enabled()) {
        return;
    }
    $ga4 = esc_js(SAMPRESHAN_GA4_ID);
    $ads = esc_js(SAMPRESHAN_ADS_ID);
    ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr(SAMPRESHAN_GA4_ID); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo $ga4; ?>');
gtag('config', '<?php echo $ads; ?>');
document.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a) { return; }
    var h = a.getAttribute('href') || '';
    var ev = /^tel:/i.test(h) ? 'call_click' : (/wa\.me|api\.whatsapp\.com|^whatsapp:/i.test(h) ? 'whatsapp_click' : (/^mailto:/i.test(h) ? 'email_click' : ''));
    if (ev) { gtag('event', ev, { link_url: h, send_to: '<?php echo $ga4; ?>' }); }
}, true);
</script>
<?php
}, 1);
