<?php
/**
 * Sampreshan "I-Eye" mark: a 3D letter I whose stem opens into a living eye.
 * Click / tap (or Enter / Space) makes it blink; it also blinks on its own and
 * the iris follows the pointer. Pure SVG + CSS + tiny JS, no external assets.
 *
 * Usage: sp_i_eye( array( 'size' => 120, 'label' => 'Sampreshan' ) );
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'sp_i_eye_markup' ) ) {
    function sp_i_eye_markup( $args = array() ) {
        $args  = wp_parse_args( $args, array( 'size' => 120, 'label' => __( 'Sampreshan', 'sampreshan-child' ), 'class' => '', 'tag' => 'button' ) );
        $tag  = 'span' === $args['tag'] ? 'span' : 'button';
        $size  = max( 16, (int) $args['size'] );
        $uid   = 'spie' . wp_unique_id();
        $label = (string) $args['label'];

        wp_enqueue_style( 'sp-i-eye' );
        wp_enqueue_script( 'sp-i-eye' );

        ob_start();
        ?>
        <<?php echo $tag; ?><?php echo 'button' === $tag ? ' type="button"' : ' aria-hidden="true"'; ?> class="sp-i-eye <?php echo esc_attr( $args['class'] ); ?>" style="--sp-ie-size:<?php echo (int) $size; ?>px" aria-label="<?php echo esc_attr( $label ); ?>">
            <svg viewBox="0 0 120 200" width="<?php echo (int) round( $size * 0.6 ); ?>" height="<?php echo (int) $size; ?>" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="<?php echo $uid; ?>-saf" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#FFC27A"/><stop offset=".45" stop-color="#FF9933"/><stop offset="1" stop-color="#C85F0A"/>
                    </linearGradient>
                    <linearGradient id="<?php echo $uid; ?>-gold" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#FFF1B8"/><stop offset=".5" stop-color="#E2B33C"/><stop offset="1" stop-color="#9A6B12"/>
                    </linearGradient>
                    <radialGradient id="<?php echo $uid; ?>-scl" cx=".5" cy=".42" r=".62">
                        <stop offset="0" stop-color="#FFFFFF"/><stop offset=".75" stop-color="#F3EEE6"/><stop offset="1" stop-color="#CFC5B6"/>
                    </radialGradient>
                    <radialGradient id="<?php echo $uid; ?>-iris" cx=".5" cy=".5" r=".5">
                        <stop offset="0" stop-color="#F5C34A"/><stop offset=".32" stop-color="#D98A1C"/><stop offset=".55" stop-color="#2B56B8"/><stop offset=".9" stop-color="#132F73"/><stop offset="1" stop-color="#0A1A45"/>
                    </radialGradient>
                    <clipPath id="<?php echo $uid; ?>-eye"><path d="M8 100 Q60 52 112 100 Q60 148 8 100Z"/></clipPath>
                    <filter id="<?php echo $uid; ?>-sh" x="-20%" y="-10%" width="140%" height="130%">
                        <feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#7A3A00" flood-opacity=".35"/>
                    </filter>
                </defs>
                <g filter="url(#<?php echo $uid; ?>-sh)">
                    <!-- Letter I: serifs + stem, saffron with gold bevel -->
                    <path d="M14 8h92a6 6 0 0 1 6 6v18a6 6 0 0 1-6 6H80v46H40V38H14a6 6 0 0 1-6-6V14a6 6 0 0 1 6-6Z" fill="url(#<?php echo $uid; ?>-saf)" stroke="url(#<?php echo $uid; ?>-gold)" stroke-width="3"/>
                    <path d="M40 116h40v46h26a6 6 0 0 1 6 6v18a6 6 0 0 1-6 6H14a6 6 0 0 1-6-6v-18a6 6 0 0 1 6-6h26Z" fill="url(#<?php echo $uid; ?>-saf)" stroke="url(#<?php echo $uid; ?>-gold)" stroke-width="3"/>
                    <path d="M18 13h84" stroke="#FFE2B8" stroke-width="3" stroke-linecap="round" opacity=".7"/>
                    <!-- Eye -->
                    <path d="M8 100 Q60 52 112 100 Q60 148 8 100Z" fill="url(#<?php echo $uid; ?>-scl)"/>
                    <g clip-path="url(#<?php echo $uid; ?>-eye)">
                        <g class="sp-i-eye__look">
                            <circle cx="60" cy="100" r="22" fill="url(#<?php echo $uid; ?>-iris)"/>
                            <g stroke="#0A1A45" stroke-width=".8" opacity=".35">
                                <path d="M60 80v8M60 112v8M40 100h8M72 100h8M46 86l6 6M68 108l6 6M74 86l-6 6M52 108l-6 6"/>
                            </g>
                            <circle class="sp-i-eye__pupil" cx="60" cy="100" r="9" fill="#05070D"/>
                            <circle cx="53" cy="92" r="4.2" fill="#fff" opacity=".95"/>
                            <circle cx="67" cy="108" r="1.8" fill="#fff" opacity=".6"/>
                        </g>
                        <path d="M8 100 Q60 52 112 100" fill="none" stroke="#000" stroke-opacity=".18" stroke-width="10"/>
                        <!-- Eyelids (slide in to blink) -->
                        <path class="sp-i-eye__lid sp-i-eye__lid--top" d="M0 40H120V102Q60 114 0 102Z" fill="url(#<?php echo $uid; ?>-saf)"/>
                        <path class="sp-i-eye__lid sp-i-eye__lid--bot" d="M0 160H120V100Q60 92 0 100Z" fill="url(#<?php echo $uid; ?>-saf)"/>
                    </g>
                    <path d="M8 100 Q60 52 112 100 Q60 148 8 100Z" fill="none" stroke="url(#<?php echo $uid; ?>-gold)" stroke-width="3.5"/>
                </g>
            </svg>
        </<?php echo $tag; ?>>
        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists( 'sp_support_eye' ) ) {
    /**
     * Inline I-Eye glyph used on every "Support" button (replaces the old ibadge icon).
     */
    function sp_support_eye( $size = 22 ) {
        echo sp_i_eye_markup( array( 'size' => (int) $size, 'tag' => 'span', 'class' => 'sp-i-eye--support' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}

if ( ! function_exists( 'sp_i_eye' ) ) {
    function sp_i_eye( $args = array() ) {
        echo sp_i_eye_markup( $args ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above.
    }
}

add_shortcode( 'sp_i_eye', static function ( $atts ) {
    $atts = shortcode_atts( array( 'size' => 120 ), $atts, 'sp_i_eye' );
    return sp_i_eye_markup( array( 'size' => (int) $atts['size'] ) );
} );

add_action( 'wp_enqueue_scripts', static function () {
    $dir = get_stylesheet_directory_uri() . '/assets';
    wp_register_style( 'sp-i-eye', $dir . '/css/i-eye.css', array(), SAMPRESHAN_CHILD_VERSION );
    wp_register_script( 'sp-i-eye', $dir . '/js/i-eye.js', array(), SAMPRESHAN_CHILD_VERSION, true );
}, 5 );
