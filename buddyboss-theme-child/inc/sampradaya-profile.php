<?php
/**
 * Sampradaya profile template: one layout for every tradition.
 *
 * Order: at a glance, overview, core teachings, history, timeline,
 * philosophy, sacred texts, practice, any other sections, branches,
 * centres, related traditions. Blocks without data are skipped.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Ordered list of blocks [id, heading, type, payload] for one profile.
 */
function sp_sampradaya_blocks( $data ) {
    $sections = isset( $data['sections'] ) ? (array) $data['sections'] : array();
    $take     = function ( $key ) use ( &$sections ) {
        $html = isset( $sections[ $key ] ) ? $sections[ $key ] : '';
        unset( $sections[ $key ] );
        return $html;
    };
    $blocks = array();
    $add    = function ( $id, $heading, $type, $payload ) use ( &$blocks ) {
        if ( ! empty( $payload ) ) {
            $blocks[] = array( $id, $heading, $type, $payload );
        }
    };

    $add( 'overview', __( 'Overview', 'sampreshan-child' ), 'html', $take( 'Overview' ) );
    $add( 'teachings', __( 'Core teachings', 'sampreshan-child' ), 'cards', $data['teachings'] ?? array() );
    $add( 'history', __( 'Origin and history', 'sampreshan-child' ), 'html', $take( 'History' ) );
    $add( 'timeline', __( 'Timeline', 'sampreshan-child' ), 'timeline', $data['timeline'] ?? array() );
    $add( 'philosophy', __( 'Philosophy', 'sampreshan-child' ), 'html', $data['philosophy'] ?? '' );
    $add( 'texts', __( 'Sacred and classic texts', 'sampreshan-child' ), 'list', $data['texts'] ?? array() );
    $add( 'practice', __( 'Practice and worship', 'sampreshan-child' ), 'html', $take( 'Practice' ) );
    foreach ( $sections as $heading => $html ) {
        $add( sanitize_title( $heading ), $heading, 'html', $html );
    }
    $add( 'branches', __( 'Branches and schools', 'sampreshan-child' ), 'cards', $data['branches'] ?? array() );
    $add( 'centres', __( 'Key centres', 'sampreshan-child' ), 'list', $data['centres'] ?? array() );

    $related = array();
    foreach ( (array) ( $data['related'] ?? array() ) as $slug ) {
        $post = function_exists( 'sp_dharma_profile_by_slug' ) ? sp_dharma_profile_by_slug( $slug ) : null;
        if ( $post ) {
            $related[] = $post;
        }
    }
    $add( 'related', __( 'Related traditions', 'sampreshan-child' ), 'related', $related );

    return $blocks;
}

/**
 * Short facts for the at-a-glance strip.
 */
function sp_sampradaya_glance( $data ) {
    $box   = isset( $data['infobox'] ) ? (array) $data['infobox'] : array();
    $pick  = function ( $keys ) use ( $box ) {
        foreach ( $keys as $key ) {
            if ( ! empty( $box[ $key ] ) ) {
                return $box[ $key ];
            }
        }
        return '';
    };
    $facts = array(
        __( 'Family', 'sampreshan-child' )      => sp_sampradaya_family_label( $data['family'] ?? '' ),
        __( 'Origin', 'sampreshan-child' )      => $data['origin'] ?? '',
        __( 'Teacher', 'sampreshan-child' )     => $pick( array( 'Founder', 'Principal Acharya', 'Inspiration', 'Last Tirthankara', 'Key teachers', 'Key figures', 'Saints' ) ),
        __( 'Philosophy', 'sampreshan-child' )  => $pick( array( 'Philosophy', 'Doctrine', 'Key idea', 'Definition', 'Focus', 'Realities' ) ),
    );
    return array_filter( $facts );
}

/**
 * Render the main column of a sampradaya profile.
 */
function sp_sampradaya_profile_main( $data, $profile_id ) {
    $blocks = sp_sampradaya_blocks( $data );
    $glance = sp_sampradaya_glance( $data );
    $has_pp = function_exists( 'sp_parampara_for' ) && sp_parampara_for( get_post_field( 'post_name', $profile_id ) );
    ?>
    <?php if ( $glance ) : ?>
        <dl class="sp-samp-glance" aria-label="<?php esc_attr_e( 'At a glance', 'sampreshan-child' ); ?>">
            <?php foreach ( $glance as $label => $value ) : ?>
                <div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>

    <?php if ( count( $blocks ) > 1 ) : ?>
        <nav class="sp-wiki__toc sp-samp-toc" aria-labelledby="sp-wiki-toc-title">
            <p id="sp-wiki-toc-title"><?php esc_html_e( 'Contents', 'sampreshan-child' ); ?></p>
            <ol>
                <?php foreach ( $blocks as $block ) : ?>
                    <li><a href="#<?php echo esc_attr( $block[0] ); ?>"><?php echo esc_html( $block[1] ); ?></a></li>
                <?php endforeach; ?>
                <?php if ( $has_pp ) : ?><li><a href="#parampara"><?php esc_html_e( 'Guru Parampara', 'sampreshan-child' ); ?></a></li><?php endif; ?>
                <li><a href="#updates"><?php esc_html_e( 'Updates', 'sampreshan-child' ); ?></a></li>
            </ol>
        </nav>
    <?php endif; ?>

    <?php foreach ( $blocks as list( $id, $heading, $type, $payload ) ) : ?>
        <section id="<?php echo esc_attr( $id ); ?>" class="sp-wiki__section sp-samp-block sp-samp-block--<?php echo esc_attr( $type ); ?>">
            <h2><?php echo esc_html( $heading ); ?></h2>
            <?php if ( 'html' === $type ) : ?>
                <?php echo wp_kses_post( $payload ); ?>
            <?php elseif ( 'cards' === $type ) : ?>
                <div class="sp-samp-cards">
                    <?php foreach ( $payload as $title => $text ) : ?>
                        <article class="sp-samp-card"><h3><?php echo esc_html( $title ); ?></h3><p><?php echo esc_html( $text ); ?></p></article>
                    <?php endforeach; ?>
                </div>
            <?php elseif ( 'timeline' === $type ) : ?>
                <ol class="sp-samp-timeline">
                    <?php foreach ( $payload as $when => $event ) : ?>
                        <li><span class="sp-samp-timeline__when"><?php echo esc_html( $when ); ?></span><p><?php echo esc_html( $event ); ?></p></li>
                    <?php endforeach; ?>
                </ol>
            <?php elseif ( 'list' === $type ) : ?>
                <dl class="sp-samp-list">
                    <?php foreach ( $payload as $title => $text ) : ?>
                        <div><dt><?php echo esc_html( $title ); ?></dt><dd><?php echo esc_html( $text ); ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            <?php elseif ( 'related' === $type ) : ?>
                <ul class="sp-samp-related">
                    <?php foreach ( $payload as $post ) : $img = sp_dharma_profile_image_url( $post->ID, 'thumbnail' ); ?>
                        <li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" width="48" height="48" loading="lazy" /><?php endif; ?><span><?php echo esc_html( get_the_title( $post ) ); ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?php
}
