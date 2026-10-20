<?php
/**
 * Template Name: Sanatan Login
 *
 * Behavior:
 *   - When the Digits (unitedover) plugin is ACTIVE: render a thin
 *     branded shell with a "Continue to sign in" button that takes
 *     the user to Digits' native login page (which already replaces
 *     /wp-login.php). We do NOT render our own form because Digits
 *     owns the OTP/email/WhatsApp flow and is far more complete.
 *   - When Digits is INACTIVE: render the full custom Sampreshan
 *     login form (login | register | lostpassword) using the 3D icon
 *     system + design tokens. This is the safety-net experience.
 *
 * Reads the `?action` query var for non-Digits mode.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$digits_active = function_exists( 'sp_digits_is_active' ) && sp_digits_is_active();

// If already logged in, send to profile.
if ( is_user_logged_in() && ! $digits_active ) {
    $u    = wp_get_current_user();
    $home = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( $u->ID ) : home_url( '/profile/' );
    wp_safe_redirect( $home );
    exit;
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
if ( '' === $action ) {
    $path = strtolower( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) );
    if ( preg_match( '#/(register|registration|sign-up|signup)/?#', $path ) ) {
        $action = 'register';
    } elseif ( preg_match( '#/(lost-password|lostpassword|reset-password)/?#', $path ) ) {
        $action = 'lostpassword';
    } else {
        $action = 'login';
    }
}
if ( ! in_array( $action, array( 'login', 'register', 'lostpassword' ), true ) ) {
    $action = 'login';
}

$login_error = isset( $_GET['sp_login_error'] ) ? sanitize_text_field( wp_unslash( $_GET['sp_login_error'] ) ) : '';
$error_messages = array(
    'expired' => __( 'That login link has expired. Please request a new one.', 'sampreshan-child' ),
    'invalid' => __( 'That login link is invalid.', 'sampreshan-child' ),
);
$login_notice = ( '' !== $login_error && isset( $error_messages[ $login_error ] ) ) ? $error_messages[ $login_error ] : '';
$sp_pigeon    = get_stylesheet_directory_uri() . '/assets/brand/pigeon-logo';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="theme-color" content="#FF9933" />
    <title><?php
        $titles = array(
            'login'         => __( 'Sign In', 'sampreshan-child' ),
            'register'      => __( 'Join SampreShan', 'sampreshan-child' ),
            'lostpassword'  => __( 'Reset Password', 'sampreshan-child' ),
        );
        echo esc_html( $titles[ $action ] . ' — ' . get_bloginfo( 'name' ) );
    ?></title>
    <?php wp_head(); ?>
</head>
<body class="sp-login-body<?php echo $digits_active ? ' sp-login-body--digits' : ''; ?>">

<div class="sp-login" data-sp-mode="<?php echo esc_attr( $action ); ?>" data-sp-digits="<?php echo $digits_active ? '1' : '0'; ?>">

    <a class="sp-login__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Home', 'sampreshan-child' ); ?>">
        <?php if ( function_exists( 'sp_logo_e' ) ) : ?>
            <?php sp_logo_e( 'sp-login__logo', 48 ); ?>
        <?php else : ?>
            <?php sp_icon_auto( 'dharma', 'sp-icon--xl sp-icon--saffron sp-icon--float', __( 'SampreShan', 'sampreshan-child' ) ); ?>
        <?php endif; ?>
        <span class="sp-login__brand-name display"><?php bloginfo( 'name' ); ?></span>
    </a>

    <?php if ( $digits_active ) : ?>

        <!-- ======================================================
             DIGITS MODE: hand off to Digits' own login page.
             Digits handles the entire OTP / phone / email flow.
             ====================================================== -->
        <main id="main" class="sp-login__card glass sp-login__card--digits" role="main">
            <div class="sp-login__art" aria-hidden="true">
                <video class="sp-login__pigeon" autoplay muted loop playsinline
                       poster="<?php echo esc_url( $sp_pigeon . '.png' ); ?>"
                       width="168" height="173">
                    <source src="<?php echo esc_url( $sp_pigeon . '.webm' ); ?>" type="video/webm">
                    <source src="<?php echo esc_url( $sp_pigeon . '.mp4' ); ?>" type="video/mp4">
                </video>
            </div>

            <header class="sp-login__head">
                <?php sp_icon_auto( 'lock', 'sp-icon--lg sp-icon--saffron', __( 'Sign in', 'sampreshan-child' ) ); ?>
                <h1 class="sp-login__title display"><?php esc_html_e( 'Sign in with mobile OTP', 'sampreshan-child' ); ?></h1>
                <p class="sp-login__sub"><?php esc_html_e( 'We use the Digits plugin to send a one-time passcode to your mobile number — no passwords, no email required.', 'sampreshan-child' ); ?></p>
            </header>

            <ul class="sp-login__feature-list">
                <li><?php sp_icon_auto( 'verified', 'sp-icon--sm sp-icon--success', __( 'Verified', 'sampreshan-child' ) ); ?><?php esc_html_e( 'Mobile OTP via SMS, Email, or WhatsApp', 'sampreshan-child' ); ?></li>
                <li><?php sp_icon_auto( 'lock',     'sp-icon--sm sp-icon--blue',    __( 'Secure',  'sampreshan-child' ) ); ?><?php esc_html_e( 'No passwords to remember', 'sampreshan-child' ); ?></li>
                <li><?php sp_icon_auto( 'star',     'sp-icon--sm sp-icon--gold',    __( 'Instant', 'sampreshan-child' ) ); ?><?php esc_html_e( 'Get signed in within seconds', 'sampreshan-child' ); ?></li>
            </ul>

            <?php
            // Embed Digits' own mobile-number form (shortcodes [dm-login-page]
            // / [dm-page]) so the number option renders inside our brand shell.
            $digits_embedded = function_exists( 'sp_digits_embed_login_form' ) ? sp_digits_embed_login_form() : false;
            $digits_login_url = function_exists( 'sp_digits_login_url' ) ? sp_digits_login_url() : home_url( '/wp-login.php' );
            ?>
            <?php if ( ! $digits_embedded ) : ?>
                <a class="sp-login__submit btn-3d btn-3d--lg" href="<?php echo esc_url( $digits_login_url ); ?>">
                    <?php sp_icon_e( 'check', 'sp-icon--sm sp-icon--white', __( 'Continue', 'sampreshan-child' ) ); ?>
                    <?php esc_html_e( 'Continue to sign in', 'sampreshan-child' ); ?>
                </a>
            <?php else : ?>
                <p class="sp-login__alt-row" style="justify-content:center">
                    <a class="sp-login__alt" href="<?php echo esc_url( $digits_login_url ); ?>">
                        <?php esc_html_e( 'Open full Digits login page', 'sampreshan-child' ); ?>
                    </a>
                </p>
            <?php endif; ?>

            <?php if ( is_user_logged_in() ) : ?>
                <p class="sp-login__alt-row" style="margin-top:1rem;text-align:center">
                    <a class="sp-login__alt" href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>">
                        <?php sp_icon_auto( 'logout', 'sp-icon--sm sp-icon--muted', __( 'Sign out', 'sampreshan-child' ) ); ?>
                        <?php esc_html_e( 'Sign out', 'sampreshan-child' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </main>

    <?php else : ?>

        <!-- ======================================================
             FALLBACK MODE: custom Sampreshan login form.
             Used when Digits is not installed.
             ====================================================== -->
        <main id="main" class="sp-login__card glass" role="main">

            <div class="sp-login__art" aria-hidden="true">
                <video class="sp-login__pigeon" autoplay muted loop playsinline
                       poster="<?php echo esc_url( $sp_pigeon . '.png' ); ?>"
                       width="168" height="173">
                    <source src="<?php echo esc_url( $sp_pigeon . '.webm' ); ?>" type="video/webm">
                    <source src="<?php echo esc_url( $sp_pigeon . '.mp4' ); ?>" type="video/mp4">
                </video>
            </div>

            <?php if ( $login_notice ) : ?>
                <div class="sp-login__notice" role="alert">
                    <?php sp_icon_auto( 'warning', 'sp-icon--md sp-icon--warning', __( 'Notice', 'sampreshan-child' ) ); ?>
                    <span><?php echo esc_html( $login_notice ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( isset( $_GET['deleted'] ) && '1' === $_GET['deleted'] ) : ?>
                <div class="sp-login__notice" role="status">
                    <?php sp_icon_auto( 'check', 'sp-icon--md sp-icon--success', __( 'Deleted', 'sampreshan-child' ) ); ?>
                    <span><?php esc_html_e( 'Your account has been deleted. Thank you for being part of SampreShan.', 'sampreshan-child' ); ?></span>
                </div>
            <?php endif; ?>

            <?php
            $sp_after = function_exists( 'bp_core_get_user_domain' ) && is_user_logged_in()
                ? bp_core_get_user_domain( get_current_user_id() )
                : home_url( '/profile/' );
            $sp_mode  = ( 'register' === $action ) ? 'signup' : 'signin';
            echo do_shortcode( '[vp_login mode="' . esc_attr( $sp_mode ) . '" redirect="' . esc_url( $sp_after ) . '"]' );
            ?>

        </main>
    <?php endif; ?>

    <footer class="sp-login__footer">
        <p>
            <?php sp_icon_auto( 'dharma', 'sp-icon--sm sp-icon--saffron', __( 'SampreShan', 'sampreshan-child' ) ); ?>
            <?php
            printf(
                /* translators: %s = site name */
                esc_html__( '%s — dedicated to all who have faith in Sanatan Dharma.', 'sampreshan-child' ),
                '<strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>'
            );
            ?>
        </p>
    </footer>

</div>

<?php wp_footer(); ?>
</body>
</html>
