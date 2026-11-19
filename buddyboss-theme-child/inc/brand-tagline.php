<?php
/**
 * SampreShan brand tagline (owner decision, 2026-11-19).
 *
 * The tagline is shown in Devanagari with its Roman transliteration and an
 * English meaning. It is the one Devanagari exception to docs/LANGUAGE-RULES.md;
 * keep the text here so every template prints the same words.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tagline parts.
 *
 * @return array{sa:string,roman:string,meaning:string}
 */
function sp_brand_tagline() {
    return array(
        'sa'      => 'परस्परानुकथनं पावनं भगवद्यशः',
        'roman'   => 'Parasparānukathanaṁ pāvanaṁ bhagavad-yaśaḥ',
        'meaning' => 'Sharing the glory of the Divine with one another purifies us all.',
    );
}

/**
 * Print the tagline block.
 *
 * @param string $class Extra class for the wrapper.
 */
function sp_brand_tagline_e( $class = '' ) {
    $t = sp_brand_tagline();
    ?>
    <p class="sp-tagline <?php echo esc_attr( $class ); ?>">
        <span class="sp-tagline__sa" lang="sa"><?php echo esc_html( $t['sa'] ); ?></span>
        <span class="sp-tagline__roman" lang="sa-Latn"><?php echo esc_html( $t['roman'] ); ?></span>
        <span class="sp-tagline__meaning"><?php echo esc_html( $t['meaning'] ); ?></span>
    </p>
    <?php
}
