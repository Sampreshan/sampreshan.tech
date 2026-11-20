<?php
/**
 * Custom site footer — Premium Edition
 * Brand tagline band, link columns, newsletter signup and social icons.
 * Layout for every width lives in assets/css/site-footer.css.
 *
 * @package SampreShan_Child
 */

if ( ! function_exists( 'sp_footer_v2_active' ) || ! sp_footer_v2_active() ) {
    include __DIR__ . '/site-footer-v1.php';
    return;
}

$site_name    = get_bloginfo( 'name' );
$year         = date( 'Y' );
$is_logged_in = is_user_logged_in();
$logo_url     = function_exists( 'sp_logo_url' ) ? sp_logo_url() : content_url( 'uploads/2026/06/sampreshan-logo-svg.svg' );
?>
<?php
/* Close the BuddyBoss content wrappers opened in the parent header.php
 * (#content > .container > .bb-grid). This partial bypasses the parent
 * footer.php, so without these closes every page using it ships broken DOM.
 */
?>
</div><!-- .bb-grid -->
</div><!-- .container -->
</div><!-- #content -->
<footer class="site-footer site-footer--v2" role="contentinfo">
    <div class="site-footer__inner">
        <?php if ( function_exists( 'sp_brand_tagline_e' ) ) : ?>
            <div class="site-footer__motto">
                <?php sp_brand_tagline_e( 'site-footer__motto-text' ); ?>
            </div>
        <?php endif; ?>

        <div class="site-footer__grid">
            <div class="site-footer__brand">
                <a class="site-footer__brand-row" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_name ); ?>">
                    <img class="site-footer__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" width="40" height="40" loading="lazy" />
                    <span class="site-footer__title"><?php echo esc_html( $site_name ); ?></span>
                </a>
                <p class="site-footer__tagline">
                    A digital platform by ShivBodh Trust for the Sanatana Dharma community to connect, raise awareness, and gather support for meaningful causes — a worldwide Sanatani social media with Issue Sampreshan, where every support is an I.
                </p>
                <div class="site-footer__social">
                    <a class="site-footer__social-link site-footer__social-link--x" href="https://x.com/sampreshan_tech" aria-label="SampreShan on X" title="SampreShan on X" target="_blank" rel="noopener">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a class="site-footer__social-link site-footer__social-link--wa" href="https://wa.me/918303781171" aria-label="Chat with SampreShan on WhatsApp" title="Chat with SampreShan on WhatsApp" target="_blank" rel="noopener">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    </a>
                    <a class="site-footer__social-link" href="https://shivbodhtrust.org" aria-label="ShivBodh Trust website" title="ShivBodh Trust website" target="_blank" rel="noopener">
                        <?php sp_icon_e( 'dharma', 'sp-icon--sm', '' ); ?>
                    </a>
                </div>
            </div>

            <nav class="site-footer__col" aria-label="Platform">
                <h4 class="site-footer__col-title"><?php sp_icon_auto( 'spark', 'sp-icon--xs', '' ); ?> Platform</h4>
                <ul class="site-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/petitions/' ) ); ?>">Petitions</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/start-a-petition/' ) ); ?>">Start a Petition</a></li>
                    <li><a href="<?php echo esc_url( function_exists( 'sp_feed_url' ) ? sp_feed_url() : home_url( '/community-center/' ) ); ?>">Feed</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">Community</a></li>
                    <?php if ( $is_logged_in ) : ?>
                        <li><a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">Dashboard</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/my-petitions/' ) ); ?>">My Petitions</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
                </ul>
            </nav>

            <nav class="site-footer__col" aria-label="Community">
                <h4 class="site-footer__col-title"><?php sp_icon_auto( 'heart', 'sp-icon--xs', '' ); ?> Community</h4>
                <ul class="site-footer__list">
                    <li><a href="<?php echo esc_url( home_url( '/community-guidelines/' ) ); ?>">Guidelines</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>">Terms</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>">Disclaimer</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/open-source-licences/' ) ); ?>">Licences</a></li>
                </ul>
            </nav>

            <div class="site-footer__col site-footer__col--trust">
                <h4 class="site-footer__col-title"><?php sp_icon_e( 'dharma', 'sp-icon--xs', '' ); ?> ShivBodh Trust</h4>
                <ul class="site-footer__list">
                    <li><a href="https://shivbodhtrust.org" target="_blank" rel="noopener">shivbodhtrust.org</a></li>
                    <li><a href="https://shivbodhtrust.org/about/" target="_blank" rel="noopener">About the Trust</a></li>
                    <li><a href="https://shivbodhtrust.org/contact/" target="_blank" rel="noopener">Contact</a></li>
                </ul>
                <div class="site-footer__newsletter">
                    <p class="site-footer__newsletter-title" id="sp-footer-join">Join ShivBodh Trust Community</p>
                    <?php /* Joins ShivBodh Trust Community: forwarded to the varanasipooja.com hub (inc/shivbodh-community.php). */ ?>
                    <form class="site-footer__newsletter-form" aria-labelledby="sp-footer-join" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="sbc_join" />
                        <input type="hidden" name="page" value="footer" />
                        <input type="hidden" name="ts" value="" data-sbc-ts />
                        <input type="hidden" name="ref" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>" />
                        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0" />
                        <input class="site-footer__newsletter-input" type="email" name="email" required placeholder="Your email" aria-label="Email for ShivBodh Trust Community" autocomplete="email" />
                        <button class="site-footer__newsletter-btn" type="submit">Subscribe</button>
                    </form>
                    <?php if ( isset( $_GET['sbc'] ) && in_array( $_GET['sbc'], array( 'ok', 'later' ), true ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
                        <p class="site-footer__newsletter-status" role="status">Pranam! You are now part of ShivBodh Trust Community.</p>
                    <?php endif; ?>
                    <script>document.querySelectorAll('[data-sbc-ts]').forEach(function (i) { i.value = String(Date.now()); });</script>
                </div>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p class="site-footer__copy">&copy; <?php echo esc_html( $year ); ?> <?php echo esc_html( $site_name ); ?>. All rights reserved.</p>
            <p class="site-footer__credit">
                <span><?php esc_html_e( 'Dedicated to all who have faith in Sanatan Dharma', 'sampreshan-child' ); ?></span>
                <span class="site-footer__credit-sep" aria-hidden="true">&middot;</span>
                <span><?php esc_html_e( 'An initiative by', 'sampreshan-child' ); ?> <a href="https://shivbodhtrust.org" target="_blank" rel="noopener">ShivBodh Trust</a></span>
            </p>
        </div>
    </div>
</footer>

<!-- Scroll to top -->
<button class="scroll-top" id="scroll-top" aria-label="Scroll to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<?php if ( function_exists( 'sp_ai_agent_is_configured' ) && sp_ai_agent_is_configured() ) : ?>
<!-- Sampreshan Sahayak — floating AI help widget -->
<div class="sp-sahayak" id="sp-sahayak">
    <button class="sp-sahayak__launcher" type="button" data-sahayak-launcher aria-haspopup="dialog" aria-expanded="false" aria-controls="sp-sahayak-panel" aria-label="<?php esc_attr_e( 'Open Sampreshan Sahayak help chat', 'sampreshan-child' ); ?>">
        <?php sp_icon_auto( 'chat', 'sp-icon--md', '' ); ?>
    </button>
    <div class="sp-sahayak__panel" id="sp-sahayak-panel" role="dialog" aria-label="<?php esc_attr_e( 'Sampreshan Sahayak', 'sampreshan-child' ); ?>" aria-modal="false">
        <div class="sp-sahayak__head">
            <div>
                <h2><?php esc_html_e( 'Sampreshan Sahayak', 'sampreshan-child' ); ?></h2>
                <p><?php esc_html_e( 'Ask about petitions, profiles, or the dashboard', 'sampreshan-child' ); ?></p>
            </div>
            <button class="sp-sahayak__close" type="button" data-sahayak-close aria-label="<?php esc_attr_e( 'Close chat', 'sampreshan-child' ); ?>">
                <?php sp_icon_auto( 'close', 'sp-icon--sm', '' ); ?>
            </button>
        </div>
        <div class="sp-sahayak__body" data-sahayak-body aria-live="polite">
            <div class="sp-sahayak__msg sp-sahayak__msg--assistant"><?php esc_html_e( 'Namaste! Main Sampreshan Sahayak hoon. Petitions, Acharya/Peeth profiles, ya dashboard ke baare mein kuch bhi poochh sakte hain.', 'sampreshan-child' ); ?></div>
        </div>
        <form class="sp-sahayak__form" data-sahayak-form>
            <label class="screen-reader-text" for="sp-sahayak-input"><?php esc_html_e( 'Type your message', 'sampreshan-child' ); ?></label>
            <textarea class="sp-sahayak__input" id="sp-sahayak-input" data-sahayak-input rows="1" maxlength="1000" placeholder="<?php esc_attr_e( 'Type a message…', 'sampreshan-child' ); ?>" autocomplete="off"></textarea>
            <button class="sp-sahayak__send" type="submit" data-sahayak-send aria-label="<?php esc_attr_e( 'Send message', 'sampreshan-child' ); ?>">
                <?php sp_icon_auto( 'send', 'sp-icon--sm', '' ); ?>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
