<?php
/**
 * ShivBodh Research Group credit, shown on every content page.
 * The credit is the group name only, with no further text (site rule).
 *
 * Site rule (docs/CONTENT-SOURCING.md): content is compiled by the
 * ShivBodh Research Group and written in our own words, so pages carry
 * this credit instead of third-party source lists.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Credit text; one place so every page says the same thing. */
function sp_research_credit_text() {
    return __( 'ShivBodh Research Group', 'sampreshan-child' );
}

/** Print the credit block. */
function sp_research_credit( $class = '' ) {
    ?>
    <aside class="sp-research-credit <?php echo esc_attr( $class ); ?>" aria-label="<?php esc_attr_e( 'About this information', 'sampreshan-child' ); ?>">
        <?php sp_icon( 'seva-education', 'sp-icon sp-icon--lg', '' ); ?>
        <p class="sp-research-credit__title"><?php echo esc_html( sp_research_credit_text() ); ?></p>
    </aside>
    <?php
}

/** Append the credit to blog posts and site-run campaigns (not member petitions). */
function sp_research_credit_after_content( $content ) {
    if ( ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }
    $campaign = is_singular( 'petition' ) && get_post_meta( get_the_ID(), 'sp_campaign', true );
    if ( ! is_singular( 'post' ) && ! $campaign ) {
        return $content;
    }
    ob_start();
    sp_research_credit();
    return $content . ob_get_clean();
}
add_filter( 'the_content', 'sp_research_credit_after_content', 30 );
