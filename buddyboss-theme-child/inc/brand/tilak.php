<?php
/**
 * Sampradaya tilak marks: a simple forehead badge showing the identifying
 * tilak of each sampradaya (used on directory cards instead of a blank/Om).
 *
 * Shapes follow published descriptions:
 * - Urdhva Pundra variants (Sri Vaishnava, Madhva, Nimbarka, Vallabha, Gaudiya,
 *   Ramanandi, Swaminarayan): en.wikipedia.org/wiki/Urdhva_Pundra, baps.org (Tilak-Chandlo).
 * - Tripundra (Shaiva / Smarta), Ganapatya red-sandal "U with centre line",
 *   Shakta kumkum: en.wikipedia.org/wiki/Tilaka, Banglapedia "Tilak", Times of India.
 * Traditions without one documented sect mark use the general mark of their family.
 *
 * Usage: echo sp_tilak_markup( 'madhva-sampradaya', 120 );
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Slug => array( style, caption ).
 */
function sp_tilak_map() {
    return array(
        'smarta-dashanami-sampradaya'    => array( 'tripundra-dot', 'Tripundra (vibhuti) with kumkum bindu' ),
        'sri-vaishnava-sampradaya'       => array( 'thiruman', 'Thiruman: white namam with red Srichurnam' ),
        'madhva-sampradaya'              => array( 'madhva', 'Gopichandana lines, angara line and akshate' ),
        'nimbarka-sampradaya'            => array( 'nimbarka', 'Gopichandana with black Radha-Krishna bindu' ),
        'pushtimarg-vallabha-sampradaya' => array( 'vallabha', 'Double red U (saffron) of the Vallabha line' ),
        'gaudiya-vaishnava-sampradaya'   => array( 'gaudiya', 'Gopichandana U with tulsi leaf' ),
        'ramanandi-sampradaya'           => array( 'ramanandi', 'White urdhva pundra with red Shri (Sita-Rama)' ),
        'warkari-sampradaya'             => array( 'warkari', 'Gopichandana urdhva pundra with bukka bindu' ),
        'swaminarayan-sampradaya'        => array( 'swaminarayan', 'Chandan U with kumkum chandlo' ),
        'ekasarana-dharma'               => array( 'chandan-line', 'Chandan urdhva mark' ),
        'mahanubhava-sampradaya'         => array( 'chandan-u', 'Chandan urdhva pundra' ),
        'shaiva-siddhanta'               => array( 'tripundra-dot', 'Tripundra (vibhuti) with kumkum pottu' ),
        'kashmir-shaivism'               => array( 'tripundra-saffron', 'Tripundra with saffron bindu' ),
        'lingayat-veerashaiva'           => array( 'tripundra', 'Tripundra of vibhuti' ),
        'nath-sampradaya'                => array( 'tripundra-sindoor', 'Tripundra with sindoor mark' ),
        'pashupata-shaivism'             => array( 'tripundra-wide', 'Full bhasma tripundra' ),
        'shakta-sampradaya'              => array( 'shakta', 'Kumkum urdhva mark with bindu' ),
        'ganapatya-sampradaya'           => array( 'ganapatya', 'Rakta chandan U with centre line' ),
        'kabir-panth'                    => array( 'kabir', 'White Vaishnava urdhva mark' ),
        'udasi-sampradaya'               => array( 'tripundra-saffron', 'Vibhuti tripundra with saffron bindu' ),
    );
}

function sp_tilak_style_for( $slug, $family = '' ) {
    $map = sp_tilak_map();
    if ( isset( $map[ $slug ] ) ) {
        return $map[ $slug ];
    }
    $by_family = array(
        'Shaiva'    => array( 'tripundra-dot', 'Tripundra' ),
        'Smarta'    => array( 'tripundra-dot', 'Tripundra' ),
        'Shakta'    => array( 'shakta', 'Kumkum mark' ),
        'Ganapatya' => array( 'ganapatya', 'Rakta chandan mark' ),
    );
    return $by_family[ $family ] ?? array( 'chandan-u', 'Urdhva pundra' );
}

/**
 * Inner SVG for a tilak style, drawn on a 100x120 forehead (brows at y≈92, nose below).
 */
function sp_tilak_shapes( $style, $u ) {
    $white = '#FBF8F1';
    $gopi  = '#F3E2B0';
    $chand = '#F2B640';
    $red   = '#C81E1E';
    $saf   = '#F08A1C';
    $black = '#1F1A17';
    $ash   = '#ECEAE6';
    $edge  = 'stroke="#A8957A" stroke-opacity=".55" stroke-width="1"';

    // Reusable pieces.
    $u_shape = static function ( $fill, $w = 12, $gap = 22, $top = 14, $bottom = 96 ) use ( $edge ) {
        $l  = 50 - $gap / 2 - $w;
        $r  = 50 + $gap / 2 + $w;
        $il = 50 - $gap / 2;
        $ir = 50 + $gap / 2;
        return sprintf(
            '<path d="M%1$s %5$sL%3$s %5$sL%3$s %6$sQ50 %7$s %4$s %6$sL%4$s %5$sL%2$s %5$sL%2$s %8$sQ50 %9$s %1$s %8$sZ" fill="%10$s" %11$s/>',
            $l, $r, $il, $ir, $top, $bottom - 14, $bottom - 4, $bottom - 10, $bottom + 8, $fill, $edge
        );
    };
    $tripundra = static function ( $width = 70, $th = 7 ) use ( $ash, $edge ) {
        $x = 50 - $width / 2;
        $o = '';
        foreach ( array( 38, 52, 66 ) as $y ) {
            $o .= sprintf( '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s" %s/>', $x, $y - $th / 2, $width, $th, $th / 2, $ash, $edge );
        }
        return $o;
    };
    $line = static function ( $fill, $w = 6, $top = 18, $bottom = 84 ) {
        return sprintf( '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s"/>', 50 - $w / 2, $top, $w, $bottom - $top, $w / 2, $fill );
    };
    $dot = static function ( $fill, $r = 5, $cy = 52 ) {
        return sprintf( '<circle cx="50" cy="%s" r="%s" fill="%s"/>', $cy, $r, $fill );
    };

    switch ( $style ) {
        case 'thiruman':
            return $u_shape( $white, 11, 18 ) . $line( $red, 6, 18, 90 );
        case 'ramanandi':
            return $u_shape( $white, 9, 20, 16, 92 ) . $line( $red, 8, 22, 86 ) . sprintf( '<rect x="46" y="96" width="8" height="12" rx="4" fill="%s"/>', $white );
        case 'madhva':
            return sprintf( '<rect x="31" y="16" width="10" height="72" rx="5" fill="%1$s" %2$s/><rect x="59" y="16" width="10" height="72" rx="5" fill="%1$s" %2$s/>', $gopi, $edge )
                . $line( $black, 5, 20, 80 ) . $dot( $chand, 4.5, 84 );
        case 'nimbarka':
            return $u_shape( $gopi, 9, 24 ) . $dot( $black, 5.5, 74 );
        case 'vallabha':
            return $u_shape( $red, 6, 10, 18, 94 );
        case 'gaudiya':
            return $u_shape( $gopi, 8, 16, 14, 90 )
                . sprintf( '<path d="M50 94C56 100 57 108 50 116C43 108 44 100 50 94Z" fill="%s" %s/>', $gopi, $edge );
        case 'warkari':
            return $u_shape( $gopi, 8, 16, 18, 92 ) . $dot( $black, 5, 70 );
        case 'swaminarayan':
            return $u_shape( $chand, 9, 22 ) . $dot( $red, 8, 66 );
        case 'chandan-line':
            return $line( $chand, 9, 16, 90 ) . $dot( $red, 3.5, 74 );
        case 'chandan-u':
            return $u_shape( $chand, 8, 18 );
        case 'kabir':
            return $u_shape( $white, 8, 14, 18, 94 );
        case 'tripundra':
            return $tripundra();
        case 'tripundra-wide':
            return $tripundra( 86, 9 );
        case 'tripundra-dot':
            return $tripundra() . $dot( $red, 6, 52 );
        case 'tripundra-saffron':
            return $tripundra() . $dot( $saf, 6, 52 );
        case 'tripundra-sindoor':
            return $tripundra() . sprintf( '<rect x="46" y="70" width="8" height="18" rx="4" fill="%s"/>', $red );
        case 'shakta':
            return sprintf( '<path d="M50 20C56 36 57 58 50 82C43 58 44 36 50 20Z" fill="%s"/>', $red ) . $dot( $red, 6.5, 88 );
        case 'ganapatya':
            return $u_shape( '#B3261E', 8, 18 ) . sprintf( '<path d="M50 30C54 44 54 62 50 82C46 62 46 44 50 30Z" fill="%s"/>', '#E04A2A' );
    }
    return $u_shape( $chand );
}

function sp_tilak_markup( $slug, $size = 120, $family = '' ) {
    list( $style, $caption ) = sp_tilak_style_for( (string) $slug, (string) $family );
    $u = 'sptk' . wp_unique_id();
    $w = max( 24, (int) $size );
    ob_start();
    ?>
    <span class="sp-tilak sp-tilak--<?php echo esc_attr( $style ); ?>" title="<?php echo esc_attr( $caption ); ?>">
        <svg viewBox="0 0 100 120" width="<?php echo (int) $w; ?>" height="<?php echo (int) round( $w * 1.2 ); ?>" role="img" aria-label="<?php echo esc_attr( $caption ); ?>">
            <defs>
                <radialGradient id="<?php echo $u; ?>-skin" cx=".5" cy=".35" r=".75">
                    <stop offset="0" stop-color="#E7B98F"/><stop offset=".6" stop-color="#C98E62"/><stop offset="1" stop-color="#9E6640"/>
                </radialGradient>
            </defs>
            <path d="M8 8Q50 -4 92 8L92 104Q50 112 8 104Z" fill="url(#<?php echo $u; ?>-skin)"/>
            <path d="M14 98Q30 88 44 96M56 96Q70 88 86 98" stroke="#4A2E1E" stroke-width="4" fill="none" stroke-linecap="round"/>
            <path d="M44 98Q50 120 56 98" fill="#B47A50" opacity=".45"/>
            <g><?php echo sp_tilak_shapes( $style, $u ); // phpcs:ignore WordPress.Security.EscapeOutput -- static numeric markup. ?></g>
        </svg>
    </span>
    <?php
    return ob_get_clean();
}
