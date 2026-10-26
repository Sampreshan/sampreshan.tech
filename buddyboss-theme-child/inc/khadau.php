<?php
/**
 * Wooden khadau (paduka) mark for "Anusaran" (follow).
 *
 * Four wooden padukas step left-right in sequence, like footprints being
 * followed. Inline SVG so it inherits size from text; colours are flat
 * fills (no gradient ids) so many copies on one page never collide.
 * Motion lives in icon-system.css and stops under prefers-reduced-motion.
 *
 * @package Sampreshan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Markup for the animated khadau trail.
 *
 * @param string $class Extra classes.
 * @return string
 */
function sp_khadau_icon( $class = '' ) {
    // One paduka pointing up in a 10x22 box: tapered wooden sole, toe knob (ghunti).
    $sole = '<path class="sp-khadau__sole" d="M5 .8C8.2.8 9.4 4 9.2 7.5 9 10.5 8 12 8.1 14.5c.1 3 .7 6.7-3.1 6.7S1.8 17.5 1.9 14.5C2 12 1 10.5.8 7.5.6 4 1.8.8 5 .8Z"/>'
        . '<path class="sp-khadau__grain" d="M3.4 9.5c.6 2.5.4 5.5 0 8M6.6 9.5c-.6 2.5-.4 5.5 0 8"/>'
        . '<circle class="sp-khadau__knob" cx="5" cy="4.6" r="1.7"/>';

    // Left foot on the upper track, right foot on the lower; walking to the right.
    $steps = array( array( 0, 1 ), array( 12, 13 ), array( 24, 1 ), array( 36, 13 ) );
    $out   = '';
    foreach ( $steps as $i => $xy ) {
        $out .= sprintf(
            '<g class="sp-khadau__step sp-khadau__step--%1$d" transform="translate(%2$d %3$d) rotate(90)">%4$s</g>',
            $i + 1,
            $xy[0] + 22,
            $xy[1],
            $sole
        );
    }

    return '<span class="sp-khadau ' . esc_attr( $class ) . '" aria-hidden="true">'
        . '<svg viewBox="0 0 58 24" width="58" height="24" focusable="false">' . $out . '</svg></span>';
}

/**
 * One pair of padukas (left + right) for the merged Anusaran mark.
 *
 * @return string
 */
function sp_khadau_pair() {
    $sole = '<path class="sp-khadau__sole" d="M5 .8C8.2.8 9.4 4 9.2 7.5 9 10.5 8 12 8.1 14.5c.1 3 .7 6.7-3.1 6.7S1.8 17.5 1.9 14.5C2 12 1 10.5.8 7.5.6 4 1.8.8 5 .8Z"/>'
        . '<path class="sp-khadau__grain" d="M3.4 9.5c.6 2.5.4 5.5 0 8M6.6 9.5c-.6 2.5-.4 5.5 0 8"/>'
        . '<circle class="sp-khadau__knob" cx="5" cy="4.6" r="1.7"/>';
    return '<svg viewBox="0 0 30 24" width="30" height="24" focusable="false">'
        . '<g class="sp-anu__foot sp-anu__foot--l" transform="translate(22 1) rotate(90)">' . $sole . '</g>'
        . '<g class="sp-anu__foot sp-anu__foot--r" transform="translate(28 13) rotate(90)">' . $sole . '</g>'
        . '</svg>';
}

/**
 * Letters of the label, each standing on a faint paduka footprint.
 *
 * @param string $text Already-translated label.
 * @return string
 */
function sp_khadau_letters( $text ) {
    $out = '';
    foreach ( mb_str_split( (string) $text ) as $i => $ch ) {
        $out .= ' ' === $ch
            ? '<span class="sp-anu__gap" aria-hidden="true"> </span>'
            : '<span class="sp-anu__l" style="--i:' . (int) $i . '" aria-hidden="true">' . esc_html( $ch ) . '</span>';
    }
    return $out;
}

/**
 * Anusaran button mark: the button starts blank, a paduka pair walks across,
 * each step leaves a footprint dash that turns into the next letter of
 * SAMPRESHAN; at the far edge the full word stands, then the walk repeats
 * in the other direction. Driven by assets/js/anusaran.js; the real label
 * (Anusaran / Anusaran mein hai) stays as the accessible name.
 *
 * @param string $text Already-translated label.
 * @return string
 */
function sp_khadau_label( $text ) {
    return '<span class="sp-anu" data-sp-anu-walk>'
        . '<span class="sp-khadau-label sp-anu__word" role="img" aria-label="' . esc_attr( $text ) . '" data-sp-anu>' . sp_khadau_letters( 'SAMPRESHAN' ) . '</span>'
        . '<span class="sp-khadau sp-anu__paduka" aria-hidden="true">' . sp_khadau_pair() . '</span>'
        . '</span>';
}
