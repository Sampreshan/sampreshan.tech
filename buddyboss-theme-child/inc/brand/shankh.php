<?php
/**
 * Sampreshan 3D Shankh (dakshinavarti conch) mark.
 * Click / tap (or Enter / Space) "blows" it: sound-wave rings from the
 * mouthpiece and a small recoil. Optional synthesized conch tone via
 * Web Audio when 'sound' => true (only ever on a user click).
 *
 * Usage: sp_shankh( array( 'size' => 220, 'sound' => true ) );
 *        [sp_shankh size="220" sound="1"]
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'sp_shankh_markup' ) ) {
    function sp_shankh_markup( $args = array() ) {
        $args = wp_parse_args( $args, array( 'size' => 220, 'sound' => false, 'label' => __( 'Shankh', 'sampreshan-child' ), 'class' => '', 'tag' => 'button' ) );
        $tag  = 'span' === $args['tag'] ? 'span' : 'button';
        $w    = max( 16, (int) $args['size'] );
        $h    = (int) round( $w * 150 / 240 );
        $u    = 'spsk' . wp_unique_id();
        // Turbinella pyrum, aperture toward viewer (matches reference 3D model): canal left, knobbed shoulder top, gold-capped spire right.
        $shell = 'M2 112C14 100 30 92 43 88C58 62 72 40 90 24C100 14 110 4 121 2C128 0 134 6 140 5C147 4 152 8 156 11L176 14C186 16 192 18 199 19L222 26L238 31L236 37L222 40C218 48 216 52 214 55C200 62 190 72 183 85C172 100 160 112 152 128C146 138 138 144 128 146C100 148 60 146 30 138C18 132 8 122 2 112Z';
        $mouth = 'M5 112C20 102 40 96 60 94C90 92 120 94 146 102C152 110 152 122 148 134C140 140 128 142 110 142C80 142 50 138 28 130C18 124 10 118 5 112Z';

        wp_enqueue_style( 'sp-shankh' );
        wp_enqueue_script( 'sp-shankh' );

        ob_start();
        ?>
        <<?php echo $tag; ?><?php echo 'button' === $tag ? ' type="button"' : ' aria-hidden="true"'; ?> class="sp-shankh <?php echo esc_attr( $args['class'] ); ?>" aria-label="<?php echo esc_attr( $args['label'] ); ?>"<?php echo $args['sound'] ? ' data-sound="1"' : ''; ?>>
            <svg viewBox="0 0 240 150" width="<?php echo (int) $w; ?>" height="<?php echo (int) $h; ?>" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="<?php echo $u; ?>-body" x1="0" y1="0" x2=".2" y2="1">
                        <stop offset="0" stop-color="#FFFDF8"/><stop offset=".4" stop-color="#F8F1E3"/><stop offset=".75" stop-color="#ECDFC8"/><stop offset="1" stop-color="#CDB894"/>
                    </linearGradient>
                    <radialGradient id="<?php echo $u; ?>-mouth" cx=".6" cy=".6" r=".75">
                        <stop offset="0" stop-color="#E2C3B2"/><stop offset=".5" stop-color="#F1DDD0"/><stop offset=".85" stop-color="#F9EEE4"/><stop offset="1" stop-color="#FFF9F2"/>
                    </radialGradient>
                    <radialGradient id="<?php echo $u; ?>-deep" cx=".6" cy=".3" r=".75"><stop offset="0" stop-color="#B98E78"/><stop offset=".45" stop-color="#D9B5A2" stop-opacity=".75"/><stop offset="1" stop-color="#F1DDD0" stop-opacity="0"/></radialGradient>
                    <linearGradient id="<?php echo $u; ?>-gold" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#FFF0B8"/><stop offset=".4" stop-color="#E8B93F"/><stop offset=".75" stop-color="#C38E1F"/><stop offset="1" stop-color="#8A5C0C"/>
                    </linearGradient>
                    <linearGradient id="<?php echo $u; ?>-saf" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#FF9A3C"/><stop offset=".6" stop-color="#E2451A"/><stop offset="1" stop-color="#B8200E"/>
                    </linearGradient>
                    <radialGradient id="<?php echo $u; ?>-shade" cx=".45" cy=".25" r=".85">
                        <stop offset="0" stop-color="#FFFFFF" stop-opacity="0"/><stop offset=".7" stop-color="#A88A5E" stop-opacity=".08"/><stop offset="1" stop-color="#6B4A1E" stop-opacity=".4"/>
                    </radialGradient>
                    <filter id="<?php echo $u; ?>-sh" x="-10%" y="-10%" width="120%" height="140%">
                        <feDropShadow dx="0" dy="7" stdDeviation="5" flood-color="#5A3E18" flood-opacity=".3"/>
                    </filter>
                    <clipPath id="<?php echo $u; ?>-clip"><path d="<?php echo $shell; ?>"/></clipPath>
                </defs>

                <!-- Sound waves (from the spire mouthpiece) -->
                <g class="sp-shankh__waves" fill="none" stroke="#FF9933" stroke-linecap="round">
                    <path d="M241 24Q249 35 241 46" stroke-width="3"/>
                    <path d="M241 16Q257 35 241 54" stroke-width="2.5"/>
                    <path d="M241 8Q265 35 241 62" stroke-width="2"/>
                </g>

                <g class="sp-shankh__body" filter="url(#<?php echo $u; ?>-sh)">
                    <path d="<?php echo $shell; ?>" fill="url(#<?php echo $u; ?>-body)"/>
                    <g clip-path="url(#<?php echo $u; ?>-clip)">
                        <!-- Knobbed shoulder (soft highlights + undershadows) -->
                        <g fill="#C7AE86" opacity=".28"><ellipse cx="118" cy="11" rx="8" ry="3" transform="rotate(-20 118 11)"/><ellipse cx="141" cy="12" rx="7" ry="2.6"/><ellipse cx="97" cy="28" rx="7" ry="2.6" transform="rotate(-45 97 28)"/><ellipse cx="79" cy="50" rx="6" ry="2.4" transform="rotate(-55 79 50)"/></g>
                        <!-- Fine growth lines + spiral cords -->
                        <g fill="none" stroke="#C9B08A" stroke-width=".5" opacity=".35">
                            <path d="M52 84C84 58 116 34 152 20"/><path d="M58 90C92 64 124 44 160 32"/><path d="M70 94C104 72 134 56 168 46"/><path d="M92 94C122 78 148 66 176 60"/>
                            <path d="M104 26Q110 60 108 92M126 14Q132 50 132 94" opacity=".6"/>
                        </g>
                        <g fill="none" stroke="#FFFFFF" stroke-width=".8" opacity=".75"><path d="M54 86C86 60 118 36 154 22"/><path d="M72 96C106 74 136 58 170 48"/></g>
                        <path d="M196 22C199 36 200 50 198 66" fill="none" stroke="#CDB48C" stroke-width=".8" opacity=".7"/>
                        <rect x="0" y="0" width="240" height="150" fill="url(#<?php echo $u; ?>-shade)"/>
                        <path d="M58 70C76 46 96 22 120 7" fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" opacity=".85"/>
                    </g>

                    <!-- Aperture: wide flared lip, cream with soft pink-tan interior -->
                    <path d="<?php echo $mouth; ?>" fill="url(#<?php echo $u; ?>-mouth)"/>
                    <path d="M28 110C60 98 110 98 142 106C146 116 144 126 138 132C100 132 60 124 28 112Z" fill="url(#<?php echo $u; ?>-deep)"/>
                    <path d="M6 112C20 102 40 96 60 94C90 92 120 94 146 102" fill="none" stroke="#FFFCF6" stroke-width="3" stroke-linecap="round"/>
                    <path d="M36 102C70 95 112 96 144 106" fill="none" stroke="#E7C6B6" stroke-width="1.2" opacity=".8"/>

                    <!-- Gold filigree bands with red-orange stones -->
                    <g fill="none" stroke-linecap="round">
                        <!-- thin rim along the outer (bottom) lip -->
                        <path d="M4 114C14 128 30 138 60 144C90 148 120 148 136 143" stroke="url(#<?php echo $u; ?>-gold)" stroke-width="1.8"/>
                        <path d="M4 111C16 101 30 94 44 90" stroke="#8A5C0C" stroke-width="4.8"/>
                        <path d="M4 111C16 101 30 94 44 90" stroke="url(#<?php echo $u; ?>-gold)" stroke-width="3.6"/>
                        <path d="M4 111C16 101 30 94 44 90" stroke="#D2400F" stroke-width="1.7" stroke-dasharray=".1 3.3"/>
                        <path d="M157 12C168 30 172 50 163 66C154 82 150 102 148 120C147 130 144 138 138 143" stroke="#8A5C0C" stroke-width="5.8"/>
                        <path d="M157 12C168 30 172 50 163 66C154 82 150 102 148 120C147 130 144 138 138 143" stroke="url(#<?php echo $u; ?>-gold)" stroke-width="4.6"/>
                        <path d="M157 12C168 30 172 50 163 66C154 82 150 102 148 120C147 130 144 138 138 143" stroke="#D2400F" stroke-width="1.7" stroke-dasharray=".1 3.3"/>
                        <path d="M197 19C201 32 204 44 211 55" stroke="#8A5C0C" stroke-width="5.4"/>
                        <path d="M197 19C201 32 204 44 211 55" stroke="url(#<?php echo $u; ?>-gold)" stroke-width="4.2"/>
                        <path d="M197 19C201 32 204 44 211 55" stroke="#D2400F" stroke-width="1.7" stroke-dasharray=".1 3.3"/>
                    </g>

                    <!-- Ornate engraved gold spire cap -->
                    <path d="M200 23L222 28C226 30 228 34 228 37C227 40 224 41 222 41C218 48 214 52 210 55C205 46 202 34 200 23Z" fill="url(#<?php echo $u; ?>-gold)" stroke="#8A5C0C" stroke-width=".8"/>
                    <g fill="none" stroke="#8A5C0C" stroke-width=".6" opacity=".85">
                        <circle cx="209" cy="34" r="3"/><circle cx="215" cy="40" r="2.6"/><circle cx="218" cy="32" r="2.2"/>
                        <path d="M203 28C210 30 216 31 222 33M205 42C210 44 214 45 220 43"/>
                    </g>
                    <path d="M202 25L221 29.5" stroke="#FFF0B8" stroke-width="1.1" opacity=".9"/>
                    <path d="M222 28L236 32L235 38L227 40C228 36 226 31 222 28Z" fill="#FBF3E4" stroke="#CDB48C" stroke-width=".6"/>

                    <!-- Tassel hanging from the cap -->
                    <g class="sp-shankh__tassel">
                        <circle cx="214" cy="57" r="2.4" fill="none" stroke="url(#<?php echo $u; ?>-gold)" stroke-width="1.3"/>
                        <path d="M214 59.5V88" stroke="#7A4A12" stroke-width=".9"/>
                        <circle cx="214" cy="91" r="3.6" fill="#D02A16"/>
                        <path d="M211 94.5H217L217.6 97.5H210.4Z" fill="url(#<?php echo $u; ?>-gold)"/>
                        <path d="M211 98L208 123M213 98L212 125M215 98L216 125M217 98L220 123" stroke="url(#<?php echo $u; ?>-saf)" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M214 98V126" stroke="#C8281A" stroke-width="1.6" stroke-linecap="round"/>
                    </g>
                </g>
            </svg>
        </<?php echo $tag; ?>>
        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists( 'sp_shankh' ) ) {
    function sp_shankh( $args = array() ) {
        echo sp_shankh_markup( $args ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above.
    }
}

add_shortcode( 'sp_shankh', static function ( $atts ) {
    $atts = shortcode_atts( array( 'size' => 220, 'sound' => '0' ), $atts, 'sp_shankh' );
    return sp_shankh_markup( array( 'size' => (int) $atts['size'], 'sound' => '1' === (string) $atts['sound'] ) );
} );

add_action( 'wp_enqueue_scripts', static function () {
    $dir = get_stylesheet_directory_uri() . '/assets';
    wp_register_style( 'sp-shankh', $dir . '/css/shankh.css', array(), SAMPRESHAN_CHILD_VERSION );
    wp_register_script( 'sp-shankh', $dir . '/js/shankh.js', array(), SAMPRESHAN_CHILD_VERSION, true );
}, 5 );
