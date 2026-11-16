<?php
/** Member navigation mounted on the active parent-theme avatar controls. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function sp_account_menu_template() {
    if ( ! is_user_logged_in() ) { return; }
    do_action( 'litespeed_nonce', 'log-out' );
    $user_id = get_current_user_id();
    $profile = function_exists( 'sp_member_profile_url' ) ? sp_member_profile_url( $user_id ) : home_url( '/profile/' );
    ?>
    <template id="sp-account-menu-source">
        <details class="sp-account-menu" data-sp-account-menu>
            <summary class="sp-account-menu__toggle sp-noslide" aria-label="<?php esc_attr_e( 'Open account menu', 'sampreshan-child' ); ?>" aria-expanded="false"><span class="sp-account-menu__name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span><?php echo get_avatar( $user_id, 44, '', '', array( 'extra_attr' => 'aria-hidden="true"' ) ); ?></summary>
            <nav class="sp-account-menu__panel" aria-label="<?php esc_attr_e( 'Your account', 'sampreshan-child' ); ?>">
                <a class="sp-account-menu__link" href="<?php echo esc_url( sampreshan_dashboard_url() ); ?>"><?php sp_icon_auto( 'home', 'sp-icon--sm', '' ); ?><span><?php esc_html_e( 'Dashboard', 'sampreshan-child' ); ?></span></a>
                <a class="sp-account-menu__link" href="<?php echo esc_url( home_url( '/settings/' ) ); ?>"><?php sp_icon_auto( 'account', 'sp-icon--sm', '' ); ?><span><?php esc_html_e( 'Account', 'sampreshan-child' ); ?></span></a>
                <a class="sp-account-menu__link" href="<?php echo esc_url( $profile . '#sp-profile-activity' ); ?>"><?php sp_icon_auto( 'feed', 'sp-icon--sm', '' ); ?><span><?php esc_html_e( 'Activity', 'sampreshan-child' ); ?></span></a>
                <button class="sp-account-menu__link" type="button" data-sp-open-notifications aria-controls="sp-notif-panel"><?php sp_icon_auto( 'bell', 'sp-icon--sm', '' ); ?><span><?php esc_html_e( 'Notifications', 'sampreshan-child' ); ?></span></button>
                <a class="sp-account-menu__link sp-account-menu__logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php sp_icon_auto( 'logout', 'sp-icon--sm', '' ); ?><span><?php esc_html_e( 'Logout', 'sampreshan-child' ); ?></span></a>
            </nav>
        </details>
    </template>
    <div class="sp-notif" id="sp-member-notifications" data-sp-notif>
        <button type="button" data-sp-notif-toggle aria-controls="sp-notif-panel" aria-expanded="false" hidden><span hidden data-sp-notif-badge></span><?php esc_html_e( 'Notifications', 'sampreshan-child' ); ?></button>
        <section class="sp-notif__panel" id="sp-notif-panel" role="region" aria-label="<?php esc_attr_e( 'Notifications', 'sampreshan-child' ); ?>" hidden>
            <button type="button" data-sp-notif-close class="sp-noslide" aria-label="<?php esc_attr_e( 'Close notifications', 'sampreshan-child' ); ?>"><?php sp_icon_auto( 'close', 'sp-icon--sm', '' ); ?></button>
            <p class="sp-notif__head"><?php esc_html_e( 'Notifications', 'sampreshan-child' ); ?></p>
            <ul class="sp-notif__list" data-sp-notif-list></ul>
            <p class="sp-notif__empty" data-sp-notif-empty hidden><?php esc_html_e( 'No notifications yet. Follow an Acharya or Peeth to get their updates here.', 'sampreshan-child' ); ?></p>
        </section>
    </div>
    <?php
}
add_action( 'wp_footer', 'sp_account_menu_template', 5 );
