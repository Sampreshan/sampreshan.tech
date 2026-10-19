<?php
/**
 * Template Name: Icon Library
 *
 * Public catalogue of the SampreShan icon kit, grouped by name prefix.
 * Anyone building a page picks icons from here (docs/ICON-RULES.md).
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$sp_kit_dir   = get_stylesheet_directory() . '/assets/icons/kit/';
$sp_kit_names = array();
if ( file_exists( $sp_kit_dir . 'manifest.json' ) ) {
    $sp_kit_names = json_decode( (string) file_get_contents( $sp_kit_dir . 'manifest.json' ), true );
}
$sp_kit_names = is_array( $sp_kit_names ) ? $sp_kit_names : array();

$sp_groups = array(
    'nav'   => __( 'Navigation', 'sampreshan-child' ),
    'act'   => __( 'Actions', 'sampreshan-child' ),
    'com'   => __( 'Community', 'sampreshan-child' ),
    'seva'  => __( 'Seva & Causes', 'sampreshan-child' ),
    'peeth' => __( 'Peeth & Institutions', 'sampreshan-child' ),
    'role'  => __( 'Acharya Roles', 'sampreshan-child' ),
    'dash'  => __( 'Dashanami Names', 'sampreshan-child' ),
    'tilak' => __( 'Sampradaya Tilaks', 'sampreshan-child' ),
    'devi'  => __( 'Devi & Shakti', 'sampreshan-child' ),
    'fest'  => __( 'Festivals & Tithis', 'sampreshan-child' ),
    'katha' => __( 'Katha & Satsang', 'sampreshan-child' ),
    'evt'   => __( 'Events & News', 'sampreshan-child' ),
    'sym'   => __( 'Sacred Symbols', 'sampreshan-child' ),
    'place' => __( 'Tirthas', 'sampreshan-child' ),
    'st'    => __( 'Status', 'sampreshan-child' ),
    'badge' => __( 'Member Badges', 'sampreshan-child' ),
    'other' => __( 'Empty States & Illustrations', 'sampreshan-child' ),
);

// Phase 2 additions (October 2026), tagged so page builders can spot them.
$sp_phase2_prefixes = array( 'dash', 'devi', 'fest', 'katha' );
$sp_phase2_names    = array(
    'badge-guru-bhakt', 'badge-shatak', 'com-leaderboard', 'com-mentor',
    'evt-festival', 'evt-kumbh', 'peeth-akhara', 'peeth-ashram',
    'peeth-jyotirlinga', 'peeth-math', 'peeth-shakti', 'peeth-vaishnav',
    'place-ayodhya', 'place-haridwar', 'place-mathura', 'place-prayag',
    'place-rameshwaram', 'place-ujjain', 'role-acharya', 'role-guru-parampara',
    'role-mahamandaleshwar', 'role-mahant', 'role-pujari', 'role-sannyasi',
    'role-vidwan', 'role-yogi', 'seva-elder', 'seva-youth', 'sym-chhatra',
    'sym-dhwaja', 'sym-nandi', 'sym-swastik',
);

$sp_grouped = array_fill_keys( array_keys( $sp_groups ), array() );
foreach ( $sp_kit_names as $sp_name ) {
    $sp_name   = sanitize_key( $sp_name );
    $sp_prefix = strtok( $sp_name, '-' );
    $sp_key    = isset( $sp_groups[ $sp_prefix ] ) ? $sp_prefix : 'other';
    $sp_grouped[ $sp_key ][] = $sp_name;
}

get_header();
?>

<main id="main" class="site-main sp-page sp-iconlib" role="main">

    <header class="sp-page__hero">
        <div class="sp-page__hero-icon">
            <?php sp_icon( 'sym-om', 'sp-icon sp-icon--3xl', __( 'Icon Library', 'sampreshan-child' ) ); ?>
        </div>
        <h1 class="sp-page__hero-title"><?php esc_html_e( 'SampreShan Icon Library', 'sampreshan-child' ); ?></h1>
        <p class="sp-page__hero-sub">
            <?php
            printf(
                /* translators: %d = number of icons */
                esc_html__( '%d original icons, made for SampreShan. Every page uses only these icons, never emoji.', 'sampreshan-child' ),
                (int) count( $sp_kit_names )
            );
            ?>
        </p>
    </header>

    <div class="sp-page__body">
        <nav class="sp-iconlib__toc" aria-label="<?php esc_attr_e( 'Icon categories', 'sampreshan-child' ); ?>">
            <?php foreach ( $sp_groups as $sp_key => $sp_label ) : ?>
                <?php if ( empty( $sp_grouped[ $sp_key ] ) ) { continue; } ?>
                <a href="#icons-<?php echo esc_attr( $sp_key ); ?>"><?php echo esc_html( $sp_label ); ?> <span><?php echo (int) count( $sp_grouped[ $sp_key ] ); ?></span></a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ( $sp_groups as $sp_key => $sp_label ) : ?>
            <?php if ( empty( $sp_grouped[ $sp_key ] ) ) { continue; } ?>
            <section class="sp-page__card sp-iconlib__group" id="icons-<?php echo esc_attr( $sp_key ); ?>">
                <h2 class="sp-page__section-title">
                    <?php echo esc_html( $sp_label ); ?>
                    <code><?php echo 'other' === $sp_key ? 'empty-*' : esc_html( $sp_key . '-*' ); ?></code>
                </h2>
                <ul class="sp-iconlib__grid">
                    <?php foreach ( $sp_grouped[ $sp_key ] as $sp_name ) : ?>
                        <?php $sp_is_new = in_array( $sp_key, $sp_phase2_prefixes, true ) || in_array( $sp_name, $sp_phase2_names, true ); ?>
                        <li class="sp-iconlib__item">
                            <?php echo sp_icon_kit_img( $sp_name, $sp_name, 'sp-icon--xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
                            <code class="sp-iconlib__name"><?php echo esc_html( $sp_name ); ?></code>
                            <?php if ( $sp_is_new ) : ?>
                                <span class="sp-iconlib__tag"><?php esc_html_e( 'Phase 2', 'sampreshan-child' ); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>

        <section class="sp-page__card">
            <h2 class="sp-page__section-title"><?php esc_html_e( 'How to use', 'sampreshan-child' ); ?></h2>
            <p><?php esc_html_e( 'In theme templates call sp_icon( name, size class ), for example:', 'sampreshan-child' ); ?></p>
            <pre class="sp-iconlib__code">&lt;?php sp_icon( 'fest-diwali', 'sp-icon sp-icon--lg', 'Diwali' ); ?&gt;</pre>
            <p><?php esc_html_e( 'Sizes: xs 14, sm 18, md 24, lg 36, xl 56, 2xl 80, 3xl 120 px. Each icon ships at 64, 128 and 256 px so it stays sharp on phones, tablets and desktops.', 'sampreshan-child' ); ?></p>
        </section>
    </div>
</main>

<?php
get_footer();
