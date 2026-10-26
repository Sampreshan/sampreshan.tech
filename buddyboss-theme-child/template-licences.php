<?php
/**
 * Template Name: Open Source Licences
 *
 * Plain list of the software, fonts and services the site runs on, with the
 * licence each one is distributed under. Names only, no promotion.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$sp_licence_groups = array(
    array(
        'title' => __( 'Platform', 'sampreshan-child' ),
        'rows'  => array(
            array( 'WordPress', 'GPL-2.0-or-later' ),
            array( 'BuddyBoss Theme', 'GPL-3.0' ),
            array( 'Elementor', 'GPL-3.0' ),
            array( 'Elementor Pro', 'GPL-3.0' ),
            array( 'Rank Math SEO', 'GPL-3.0-or-later' ),
            array( 'LiteSpeed Cache', 'GPL-3.0' ),
            array( 'Two Factor', 'GPL-2.0-or-later' ),
            array( 'WPCode', 'GPL-2.0-or-later' ),
            array( 'Site Kit', 'Apache-2.0' ),
        ),
    ),
    array(
        'title' => __( 'Libraries', 'sampreshan-child' ),
        'rows'  => array(
            array( 'jQuery', 'MIT' ),
            array( 'jQuery Cookie', 'MIT' ),
            array( 'jQuery Validation', 'MIT' ),
            array( 'jQuery Mousewheel', 'MIT' ),
            array( 'Slick', 'MIT' ),
            array( 'Magnific Popup', 'MIT' ),
            array( 'Select2', 'MIT' ),
            array( 'jsSocials', 'MIT' ),
            array( 'PanelSlider', 'MIT' ),
            array( 'ProgressBar.js', 'MIT' ),
            array( 'Sticky-kit', 'MIT' ),
            array( 'Draggabilly', 'MIT' ),
            array( 'Slideout.js', 'MIT' ),
            array( 'FitVids.js', 'WTFPL' ),
            array( 'Firebase JavaScript SDK', 'Apache-2.0' ),
        ),
    ),
    array(
        'title' => __( 'Fonts', 'sampreshan-child' ),
        'rows'  => array(
            array( 'Inter', 'SIL Open Font License 1.1' ),
            array( 'Cormorant Garamond', 'SIL Open Font License 1.1' ),
            array( 'Tiro Devanagari', 'SIL Open Font License 1.1' ),
        ),
    ),
    array(
        'title' => __( 'Online services', 'sampreshan-child' ),
        'note'  => __( 'These are hosted services, not open-source code. They are used under their own terms of service.', 'sampreshan-child' ),
        'rows'  => array(
            array( __( 'Phone sign-in (Firebase Authentication)', 'sampreshan-child' ), __( 'Terms of service', 'sampreshan-child' ) ),
            array( __( 'Spam protection (reCAPTCHA)', 'sampreshan-child' ), __( 'Terms of service', 'sampreshan-child' ) ),
            array( __( 'Public page reader (TinyFish)', 'sampreshan-child' ), __( 'Terms of service', 'sampreshan-child' ) ),
            array( __( 'Public video and news feeds', 'sampreshan-child' ), __( 'Terms of service', 'sampreshan-child' ) ),
        ),
    ),
    array(
        'title' => __( 'Our own work', 'sampreshan-child' ),
        'rows'  => array(
            array( __( 'Sampreshan child theme code', 'sampreshan-child' ), 'GPL-2.0-or-later' ),
            array( __( 'Icons, artwork, research and text', 'sampreshan-child' ), __( 'ShivBodh Trust, all rights reserved', 'sampreshan-child' ) ),
        ),
    ),
);
?>

<main id="main" class="site-main sp-page" role="main">
    <header class="sp-page__hero">
        <h1 class="sp-page__hero-title"><?php esc_html_e( 'Open Source Licences', 'sampreshan-child' ); ?></h1>
        <p class="sp-page__hero-sub"><?php esc_html_e( 'Sampreshan is built on free and open-source software. Each part is listed with the licence it is shared under.', 'sampreshan-child' ); ?></p>
    </header>

    <div class="sp-page__body">
        <?php foreach ( $sp_licence_groups as $group ) : ?>
            <section class="sp-page__card">
                <h2 class="sp-page__section-title"><?php echo esc_html( $group['title'] ); ?></h2>
                <?php if ( ! empty( $group['note'] ) ) : ?>
                    <p><?php echo esc_html( $group['note'] ); ?></p>
                <?php endif; ?>
                <table class="sp-licences">
                    <thead><tr><th scope="col"><?php esc_html_e( 'Component', 'sampreshan-child' ); ?></th><th scope="col"><?php esc_html_e( 'Licence', 'sampreshan-child' ); ?></th></tr></thead>
                    <tbody>
                        <?php foreach ( $group['rows'] as $row ) : ?>
                            <tr><td><?php echo esc_html( $row[0] ); ?></td><td><?php echo esc_html( $row[1] ); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>

        <section class="sp-page__card">
            <p><?php esc_html_e( 'Full licence texts ship with each component. Compiled by ShivBodh Research Group.', 'sampreshan-child' ); ?></p>
        </section>
    </div>
</main>

<?php
get_footer();
