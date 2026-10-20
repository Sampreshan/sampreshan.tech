<?php
/**
 * Sampreshan 3D messenger pigeon (sandesh-vahak) mark.
 * Grey rock pigeon in flight carrying a rolled scroll tied with red thread.
 * Wings flap continuously; click / tap (or Enter / Space) sends a quick
 * "delivery" burst (faster flaps + forward dart).
 *
 * Usage: sp_pigeon( array( 'size' => 240 ) );
 *        [sp_pigeon size="240"]
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'sp_pigeon_markup' ) ) {
    function sp_pigeon_markup( $args = array() ) {
        $args = wp_parse_args( $args, array( 'size' => 240, 'label' => __( 'Sandesh pigeon', 'sampreshan-child' ), 'class' => '' ) );
        $w    = max( 32, (int) $args['size'] );
        $h    = (int) round( $w * 200 / 240 );
        $u    = 'sppg' . wp_unique_id();

        // Flight feathers of the raised (far) wing: overlapping quills fanned up-left from the shoulder, darker toward the tips.
        $feathers = '';
        for ( $i = 0; $i < 11; $i++ ) {
            $deg = 196 + $i * 8;
            $l   = 98 - abs( $i - 4 ) * 4.5;
            $feathers .= sprintf(
                '<path transform="translate(132 100) rotate(%1$.1f)" d="M0 -6C%4$.1f -8 %2$.1f -7 %3$.1f -1C%2$.1f 5 %4$.1f 6 0 6Z" />',
                $deg, $l * .7, $l, $l * .35
            );
        }

        wp_enqueue_style( 'sp-pigeon' );
        wp_enqueue_script( 'sp-pigeon' );

        ob_start();
        ?>
        <button type="button" class="sp-pigeon <?php echo esc_attr( $args['class'] ); ?>" aria-label="<?php echo esc_attr( $args['label'] ); ?>">
            <svg viewBox="0 0 240 200" width="<?php echo (int) $w; ?>" height="<?php echo (int) $h; ?>" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="<?php echo $u; ?>-body" x1="0" y1="0" x2=".3" y2="1">
                        <stop offset="0" stop-color="#C9CDD3"/><stop offset=".5" stop-color="#A9AEB6"/><stop offset="1" stop-color="#6E737B"/>
                    </linearGradient>
                    <linearGradient id="<?php echo $u; ?>-wing" x1="1" y1="1" x2="0" y2="0">
                        <stop offset="0" stop-color="#D8DBE0"/><stop offset=".45" stop-color="#9EA2A9"/><stop offset=".75" stop-color="#6B6566"/><stop offset="1" stop-color="#4A4344"/>
                    </linearGradient>
                    <linearGradient id="<?php echo $u; ?>-neck" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#7A8090"/><stop offset=".4" stop-color="#2F8A6E"/><stop offset=".7" stop-color="#7B3F86"/><stop offset="1" stop-color="#8E93A0"/>
                    </linearGradient>
                    <linearGradient id="<?php echo $u; ?>-scroll" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#F6E3C0"/><stop offset=".6" stop-color="#DDBB86"/><stop offset="1" stop-color="#B48C55"/>
                    </linearGradient>
                    <linearGradient id="<?php echo $u; ?>-feather" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#C7CBD1"/><stop offset=".5" stop-color="#8E9097"/><stop offset=".8" stop-color="#5A5354"/><stop offset="1" stop-color="#3C3536"/>
                    </linearGradient>
                    <filter id="<?php echo $u; ?>-sh" x="-20%" y="-20%" width="140%" height="150%">
                        <feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#1E2430" flood-opacity=".28"/>
                    </filter>
                </defs>
                <g class="sp-pigeon__bird" filter="url(#<?php echo $u; ?>-sh)">
                    <!-- far wing (raised) -->
                    <g class="sp-pigeon__wing sp-pigeon__wing--far">
                        <g fill="url(#<?php echo $u; ?>-feather)" stroke="#2E2829" stroke-opacity=".35" stroke-width=".8"><?php echo $feathers; // phpcs:ignore WordPress.Security.EscapeOutput ?></g>
                        <path d="M132 100C120 84 104 74 86 70C82 80 92 94 108 104C116 108 126 108 132 104Z" fill="#B9BDC4"/>
                        <path d="M132 102C124 92 114 86 100 84M130 98C122 86 112 80 98 78" stroke="#7D8189" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                    </g>
                    <!-- tail -->
                    <path d="M76 132L28 158C22 162 26 168 34 166L82 150Z" fill="#8F949B"/>
                    <path d="M30 160L40 155L44 162L34 166Z" fill="#3A3536"/>
                    <!-- body -->
                    <path d="M70 136C84 118 112 108 140 104C160 102 176 108 182 120C186 134 172 150 146 156C120 162 92 158 70 146Z" fill="url(#<?php echo $u; ?>-body)"/>
                    <!-- neck + head -->
                    <path d="M150 112C150 96 156 82 168 76C178 72 190 74 196 82C200 90 196 100 186 106C176 114 168 124 166 132Z" fill="url(#<?php echo $u; ?>-neck)"/>
                    <circle cx="186" cy="82" r="14" fill="#8C919C"/>
                    <path d="M176 72C182 68 192 68 198 74" stroke="#FFFFFF" stroke-opacity=".35" stroke-width="2" fill="none" stroke-linecap="round"/>
                    <circle cx="190" cy="79" r="4" fill="#C4281C"/><circle cx="190" cy="79" r="1.9" fill="#111"/><circle cx="191" cy="78" r=".8" fill="#fff"/>
                    <!-- beak + cere -->
                    <path d="M198 82L208 85L198 88Z" fill="#3B3838"/>
                    <ellipse cx="199" cy="81.5" rx="3" ry="2" fill="#F1EEE8"/>
                    <!-- scroll held in beak -->
                    <g class="sp-pigeon__scroll">
                        <rect x="203" y="76" width="30" height="18" rx="4" fill="url(#<?php echo $u; ?>-scroll)"/>
                        <ellipse cx="233" cy="85" rx="4" ry="9" fill="#E9CFA0" stroke="#B48C55" stroke-width="1"/>
                        <path d="M233 82a2.4 2.4 0 1 1 -1 3" stroke="#A47A44" stroke-width="1" fill="none"/>
                        <path d="M210 76V94" stroke="#B71C1C" stroke-width="2.6"/>
                        <path class="sp-pigeon__thread" d="M210 94C208 104 214 110 208 120" stroke="#C62828" stroke-width="2" fill="none" stroke-linecap="round"/>
                    </g>
                    <!-- wing bars on near covert -->
                    <path d="M96 140C116 128 140 124 162 128" stroke="#3A3536" stroke-width="4" fill="none" stroke-linecap="round"/>
                    <path d="M92 148C112 138 136 134 156 138" stroke="#3A3536" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                    <!-- near wing (lower, folded down on downstroke) -->
                    <g class="sp-pigeon__wing sp-pigeon__wing--near">
                        <path d="M150 112C128 106 100 110 80 122C96 124 118 124 140 120Z" fill="#C3C7CD" opacity=".9"/>
                    </g>
                    <!-- feet + orange leg thread -->
                    <path d="M140 156L142 172M150 154L154 170" stroke="#D2343B" stroke-width="3.2" stroke-linecap="round"/>
                    <path d="M136 174L142 172L146 176M150 172L154 170L159 173" stroke="#D2343B" stroke-width="2.4" fill="none" stroke-linecap="round"/>
                    <path d="M84 146C72 152 66 160 58 162" stroke="#F08A24" stroke-width="2" fill="none" stroke-linecap="round"/>
                </g>
            </svg>
        </button>
        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists( 'sp_pigeon' ) ) {
    function sp_pigeon( $args = array() ) {
        echo sp_pigeon_markup( $args ); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}

add_shortcode( 'sp_pigeon', static function ( $atts ) {
    $atts = shortcode_atts( array( 'size' => 240 ), $atts, 'sp_pigeon' );
    return sp_pigeon_markup( array( 'size' => (int) $atts['size'] ) );
} );

add_action( 'wp_enqueue_scripts', static function () {
    $dir = get_stylesheet_directory_uri() . '/assets';
    wp_register_style( 'sp-pigeon', $dir . '/css/pigeon.css', array(), SAMPRESHAN_CHILD_VERSION );
    wp_register_script( 'sp-pigeon', $dir . '/js/pigeon.js', array(), SAMPRESHAN_CHILD_VERSION, true );
} );
