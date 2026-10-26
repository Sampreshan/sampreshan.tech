<?php
/**
 * Guru Parampara tree for Peeth and Sampradaya pages, and the
 * "place in the parampara" strip on Acharya profiles.
 *
 * Markup is always chronological (founder first). By default the tree is
 * shown present-first: the living Acharya at the crown on top, the founder
 * at the roots below. The Rotate button flips it (founder on top).
 * Data: inc/parampara-data.php.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Nodes shown in full at each end before the middle folds away. */
function sp_parampara_visible_edge() {
    return 6;
}

function sp_parampara_status_label( $status ) {
    $labels = array(
        'present'   => __( 'Present Acharya', 'sampreshan-child' ),
        'designate' => __( 'Successor', 'sampreshan-child' ),
        'claimant'  => __( 'Succession sub judice', 'sampreshan-child' ),
    );
    return $labels[ $status ] ?? '';
}

function sp_parampara_node_html( $node, $current_slug = '' ) {
    $is_gap  = ! empty( $node['gap'] );
    $status  = $node['status'] ?? '';
    $profile = ! empty( $node['profile'] ) ? sp_dharma_profile_by_slug( $node['profile'] ) : null;
    $classes = array( 'sp-pp__node' );
    if ( $is_gap ) { $classes[] = 'sp-pp__node--gap'; }
    if ( $status ) { $classes[] = 'sp-pp__node--' . $status; }
    if ( $profile && $current_slug && $node['profile'] === $current_slug ) { $classes[] = 'is-current'; }

    ob_start();
    ?>
    <li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
        <span class="sp-pp__leaf" aria-hidden="true"></span>
        <div class="sp-pp__card">
            <?php if ( ! empty( $node['n'] ) ) : ?><span class="sp-pp__n"><?php echo esc_html( number_format_i18n( (int) $node['n'] ) ); ?></span><?php endif; ?>
            <div class="sp-pp__body">
                <?php if ( $status && sp_parampara_status_label( $status ) ) : ?><span class="sp-pp__chip"><?php echo esc_html( sp_parampara_status_label( $status ) ); ?></span><?php endif; ?>
                <strong class="sp-pp__name"><?php echo esc_html( $node['name'] ); ?></strong>
                <?php if ( ! empty( $node['period'] ) ) : ?><span class="sp-pp__period"><?php echo esc_html( $node['period'] ); ?></span><?php endif; ?>
                <?php if ( ! empty( $node['note'] ) ) : ?><span class="sp-pp__note"><?php echo esc_html( $node['note'] ); ?></span><?php endif; ?>
                <?php if ( $profile ) : ?><a class="sp-pp__link" href="<?php echo esc_url( get_permalink( $profile ) ); ?>"><?php sp_know_more( __( 'View', 'sampreshan-child' ), __( 'View profile', 'sampreshan-child' ) ); ?></a><?php endif; ?>
            </div>
        </div>
    </li>
    <?php
    return ob_get_clean();
}

/** A run of nodes; long runs keep both ends and fold the middle. */
function sp_parampara_run_html( $nodes, $current_slug = '' ) {
    $edge  = sp_parampara_visible_edge();
    $total = count( $nodes );
    $html  = '';
    if ( $total <= $edge * 2 + 1 ) {
        foreach ( $nodes as $node ) { $html .= sp_parampara_node_html( $node, $current_slug ); }
        return $html;
    }
    $head   = array_slice( $nodes, 0, $edge );
    $middle = array_slice( $nodes, $edge, $total - $edge * 2 );
    $tail   = array_slice( $nodes, $total - $edge );
    foreach ( $head as $node ) { $html .= sp_parampara_node_html( $node, $current_slug ); }
    $first  = reset( $middle );
    $last   = end( $middle );
    $range  = ( ! empty( $first['n'] ) && ! empty( $last['n'] ) ) ? sprintf( ' (%s–%s)', number_format_i18n( $first['n'] ), number_format_i18n( $last['n'] ) ) : '';
    $html  .= '<li class="sp-pp__fold"><details><summary><span class="sp-pp__fold-label">'
        . esc_html( sprintf( _n( 'Show %s more Acharya', 'Show %s more Acharyas', count( $middle ), 'sampreshan-child' ), number_format_i18n( count( $middle ) ) ) . $range )
        . '</span></summary><ol class="sp-pp__list">';
    foreach ( $middle as $node ) { $html .= sp_parampara_node_html( $node, $current_slug ); }
    $html  .= '</ol></details></li>';
    foreach ( $tail as $node ) { $html .= sp_parampara_node_html( $node, $current_slug ); }
    return $html;
}

/**
 * Render the full tree for a Peeth or Sampradaya profile slug.
 *
 * @param string $slug         Peeth/Sampradaya profile slug.
 * @param string $current_slug Acharya slug to highlight (optional).
 */
function sp_parampara_render( $slug, $current_slug = '' ) {
    $pp = sp_parampara_for( $slug );
    if ( ! $pp ) {
        return;
    }
    $founder = $pp['founder'];
    ?>
    <section id="parampara" class="sp-pp" data-sp-pp aria-labelledby="sp-pp-title">
        <header class="sp-pp__head">
            <div>
                <p class="sp-pp__eyebrow"><?php esc_html_e( 'Guru Parampara', 'sampreshan-child' ); ?></p>
                <h2 id="sp-pp-title"><?php esc_html_e( 'The line of Acharyas', 'sampreshan-child' ); ?></h2>
                <p class="sp-pp__count"><?php echo esc_html( $pp['count'] ); ?></p>
            </div>
            <button type="button" class="sp-pp__rotate" data-sp-pp-rotate aria-pressed="false">
                <span class="sp-pp__rotate-icon" aria-hidden="true"></span>
                <span data-sp-pp-rotate-label data-label-present="<?php esc_attr_e( 'Show founder first', 'sampreshan-child' ); ?>" data-label-founder="<?php esc_attr_e( 'Show present Acharya first', 'sampreshan-child' ); ?>"><?php esc_html_e( 'Show founder first', 'sampreshan-child' ); ?></span>
            </button>
        </header>

        <?php
        if ( sp_parampara_tree_v2_enabled( $slug ) ) {
            sp_parampara_tree_v2_render( $slug, $pp );
        } else {
            sp_parampara_3d_render( $slug, $pp );
        }
        ?>

        <div class="sp-pp__stage">
            <ol class="sp-pp__tree sp-pp__list">
                <li class="sp-pp__root">
                    <span class="sp-pp__roots" aria-hidden="true">
                        <svg viewBox="0 0 240 90" focusable="false"><path d="M120 0v38M120 38c-14 12-38 16-58 40M120 38c14 12 38 16 58 40M120 46c-6 14-20 24-26 40M120 46c6 14 20 24 26 40M120 30c-24 6-56 2-84 22M120 30c24 6 56 2 84 22" /></svg>
                    </span>
                    <div class="sp-pp__card sp-pp__card--root">
                        <span class="sp-pp__chip"><?php if ( ! empty( $founder['label'] ) ) : echo esc_html( $founder['label'] ); else : esc_html_e( 'Founder', 'sampreshan-child' ); ?><?php endif; ?></span>
                        <strong class="sp-pp__name"><?php echo esc_html( $founder['name'] ); ?></strong>
                        <?php if ( ! empty( $founder['when'] ) ) : ?><span class="sp-pp__period"><?php echo esc_html( $founder['when'] ); ?></span><?php endif; ?>
                        <?php if ( ! empty( $founder['note'] ) ) : ?><span class="sp-pp__note"><?php echo esc_html( $founder['note'] ); ?></span><?php endif; ?>
                    </div>
                </li>
                <?php echo sp_parampara_run_html( $pp['nodes'], $current_slug ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?>
                <?php if ( ! empty( $pp['branches'] ) ) : ?>
                    <li class="sp-pp__fork">
                        <?php foreach ( $pp['branches'] as $branch ) : ?>
                            <div class="sp-pp__branch">
                                <p class="sp-pp__branch-label"><?php echo esc_html( $branch['label'] ); ?></p>
                                <ol class="sp-pp__list"><?php echo sp_parampara_run_html( $branch['nodes'], $current_slug ); // phpcs:ignore WordPress.Security.EscapeOutput ?></ol>
                            </div>
                        <?php endforeach; ?>
                    </li>
                <?php endif; ?>
                <li class="sp-pp__crown" aria-hidden="true"><span></span></li>
            </ol>
        </div>

        <?php if ( ! empty( $pp['notice'] ) ) : ?><p class="sp-pp__notice" role="note"><?php echo esc_html( $pp['notice'] ); ?></p><?php endif; ?>
        <p class="sp-pp__basis">
            <?php
            echo esc_html(
                'peetham' === $pp['basis']
                    ? __( 'Names, numbering and periods follow the Peetham\'s own published parampara and are its tradition. Early dates are traditional reckonings.', 'sampreshan-child' )
                    : __( 'Only well-documented links are shown. Stretches that are not yet verified are marked instead of guessed.', 'sampreshan-child' )
            );
            ?>
        </p>
    </section>
    <?php
}

/** Flatten a parampara to an ordered node list (first branch that has the slug). */
function sp_parampara_line_with( $pp, $slug ) {
    $line = $pp['nodes'];
    foreach ( $pp['branches'] ?? array() as $branch ) {
        foreach ( $branch['nodes'] as $node ) {
            if ( ( $node['profile'] ?? '' ) === $slug ) {
                return array_merge( $line, $branch['nodes'] );
            }
        }
    }
    return $line;
}

/** Guru (before) and successor (after) of an Acharya within its Peeth's line. */
function sp_parampara_position( $acharya_slug, $peeth_slug ) {
    $pp = sp_parampara_for( $peeth_slug );
    if ( ! $pp ) {
        return null;
    }
    $line = sp_parampara_line_with( $pp, $acharya_slug );
    foreach ( $line as $i => $node ) {
        if ( ( $node['profile'] ?? '' ) !== $acharya_slug ) {
            continue;
        }
        $prev = $i > 0 ? $line[ $i - 1 ] : array( 'name' => $pp['founder']['name'], 'period' => __( 'Founder', 'sampreshan-child' ) );
        $next = $line[ $i + 1 ] ?? null;
        return array( 'prev' => $prev, 'self' => $node, 'next' => $next );
    }
    return null;
}

function sp_parampara_position_render( $acharya_slug, $peeth_post ) {
    if ( ! $peeth_post ) {
        return;
    }
    $peeth_slug = get_post_field( 'post_name', $peeth_post );
    $pos        = sp_parampara_position( $acharya_slug, $peeth_slug );
    if ( ! $pos ) {
        return;
    }
    ?>
    <section class="sp-pp-pos" aria-labelledby="sp-pp-pos-title">
        <p class="sp-pp__eyebrow" id="sp-pp-pos-title"><?php esc_html_e( 'Place in the Guru Parampara', 'sampreshan-child' ); ?></p>
        <ol class="sp-pp-pos__chain">
            <?php foreach ( array( 'prev' => __( 'Before', 'sampreshan-child' ), 'self' => __( 'This Acharya', 'sampreshan-child' ), 'next' => __( 'After', 'sampreshan-child' ) ) as $key => $label ) : ?>
                <?php if ( empty( $pos[ $key ] ) || ! empty( $pos[ $key ]['gap'] ) ) { continue; } $n = $pos[ $key ]; ?>
                <li class="sp-pp-pos__item sp-pp-pos__item--<?php echo esc_attr( $key ); ?>">
                    <span class="sp-pp-pos__label"><?php echo esc_html( $label ); ?></span>
                    <strong><?php echo esc_html( ( ! empty( $n['n'] ) ? number_format_i18n( (int) $n['n'] ) . '. ' : '' ) . $n['name'] ); ?></strong>
                    <?php if ( ! empty( $n['period'] ) ) : ?><span class="sp-pp__period"><?php echo esc_html( $n['period'] ); ?></span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
        <a class="sp-pp-pos__all" href="<?php echo esc_url( get_permalink( $peeth_post ) . '#parampara' ); ?>"><?php echo esc_html( sprintf( __( 'See the full parampara of %s', 'sampreshan-child' ), get_the_title( $peeth_post ) ) ); ?></a>
    </section>
    <?php
}

/** True when the slug is a Peetham with its own Guru Parampara (not a sampradaya). */
function sp_parampara_is_peetham( $slug ) {
    return isset( sp_parampara_data_all()[ $slug ] );
}

/**
 * Peethams that show the realistic tree (v2). Preview on one Peetham
 * first; the others keep the v1 tree until it is approved.
 */
function sp_parampara_tree_v2_enabled( $slug ) {
    return in_array( $slug, array( 'dakshinamnaya-sri-sharada-peetham' ), true );
}

/** Flat chronological list of a Peetham's line for the 3D trees. */
function sp_parampara_3d_line( $pp ) {
    $line = array();
    $add  = function ( $node, $branch = '' ) use ( &$line ) {
        $line[] = array(
            'n'      => isset( $node['n'] ) ? (int) $node['n'] : null,
            'name'   => ! empty( $node['gap'] ) ? __( 'Not yet documented', 'sampreshan-child' ) : (string) ( $node['name'] ?? '' ),
            'period' => (string) ( $node['period'] ?? '' ),
            'status' => (string) ( $node['status'] ?? '' ),
            'gap'    => ! empty( $node['gap'] ),
            'branch' => $branch,
            'url'    => ! empty( $node['profile'] ) ? (string) get_permalink( get_page_by_path( $node['profile'], OBJECT, 'dharma_profile' ) ) : '',
        );
    };
    foreach ( $pp['nodes'] as $node ) {
        $add( $node );
    }
    foreach ( $pp['branches'] ?? array() as $branch ) {
        foreach ( $branch['nodes'] as $node ) {
            $add( $node, (string) $branch['label'] );
        }
    }
    return $line;
}

/**
 * Realistic 3D Guru Parampara tree (v2). It plays by itself: each Acharya's
 * lamp lights in turn with the name and period shown beside it, a Kaal
 * Khanda bar sized by the years of each tenure, and the full list beside
 * the tree. Names and periods come only from inc/parampara-data.php.
 */
function sp_parampara_tree_v2_render( $slug, $pp ) {
    $line = sp_parampara_3d_line( $pp );
    $data = array(
        'founder' => array( 'name' => $pp['founder']['name'], 'period' => (string) ( $pp['founder']['when'] ?? '' ) ),
        'line'    => $line,
        'base'    => get_stylesheet_directory_uri() . '/assets/',
        'ver'     => SAMPRESHAN_CHILD_VERSION,
    );
    $label = function ( $node ) {
        if ( ! empty( $node['n'] ) ) {
            /* translators: %d: position of the Acharya in the Peetham's count. */
            return sprintf( __( 'No. %d', 'sampreshan-child' ), $node['n'] );
        }
        return $node['branch'];
    };
    ?>
    <div class="sp-pp3t" data-sp-pp3t hidden>
        <script type="application/json" class="sp-pp3t__data"><?php echo wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
        <div class="sp-pp3t__main">
            <div class="sp-pp3t__stage">
                <canvas class="sp-pp3t__canvas" role="img" aria-label="<?php esc_attr_e( 'Three dimensional tree of the Guru Parampara. Each Acharya lights up in turn with the name and period.', 'sampreshan-child' ); ?>"></canvas>
                <div class="sp-pp3t__tags" aria-hidden="true"></div>
                <div class="sp-pp3t__now" aria-live="polite">
                    <span class="sp-pp3t__num"></span>
                    <strong class="sp-pp3t__name"></strong>
                    <span class="sp-pp3t__period"></span>
                    <a class="sp-pp3t__link" href="#" hidden><?php esc_html_e( 'View profile', 'sampreshan-child' ); ?></a>
                </div>
                <button type="button" class="sp-pp3t__play" aria-pressed="true" data-label-play="<?php esc_attr_e( 'Play', 'sampreshan-child' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause', 'sampreshan-child' ); ?>"><?php esc_html_e( 'Pause', 'sampreshan-child' ); ?></button>
                <p class="sp-pp3t__loading"><?php esc_html_e( 'Growing the tree', 'sampreshan-child' ); ?></p>
            </div>
            <ol class="sp-pp3t__list" aria-label="<?php esc_attr_e( 'Acharyas in order', 'sampreshan-child' ); ?>">
                <li><button type="button" data-k="0"><span><?php esc_html_e( 'Founder', 'sampreshan-child' ); ?></span><strong><?php echo esc_html( $pp['founder']['name'] ); ?></strong><em><?php echo esc_html( (string) ( $pp['founder']['when'] ?? '' ) ); ?></em></button></li>
                <?php foreach ( $line as $i => $node ) : ?>
                    <li<?php echo $node['gap'] ? ' class="is-gap"' : ''; ?>><button type="button" data-k="<?php echo esc_attr( $i + 1 ); ?>"><span><?php echo esc_html( $label( $node ) ); ?></span><strong><?php echo esc_html( $node['name'] ); ?></strong><em><?php echo esc_html( $node['period'] ); ?></em></button></li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div class="sp-pp3t__era">
            <p class="sp-pp3t__era-title"><?php esc_html_e( 'Kaal Khanda: each band is one Acharya, as wide as the years of the tenure', 'sampreshan-child' ); ?></p>
            <div class="sp-pp3t__bar"></div>
            <div class="sp-pp3t__ticks" aria-hidden="true"></div>
        </div>
        <p class="sp-pp3t__credit"><?php esc_html_e( 'Names and periods as published by the Peetham. Drag to turn the tree; tap a name to go to it.', 'sampreshan-child' ); ?></p>
    </div>
    <?php
}

/**
 * 3D Guru Parampara tree for Peetham pages: the founder at the roots and
 * every Acharya as a lamp rising up the tree in order, with a time slider.
 * The 2D tree below stays as the accessible, no-WebGL version.
 */
function sp_parampara_3d_render( $slug, $pp ) {
    if ( ! sp_parampara_is_peetham( $slug ) ) {
        return;
    }
    $line = array();
    $add  = function ( $node, $branch = '' ) use ( &$line ) {
        $line[] = array(
            'n'      => isset( $node['n'] ) ? (int) $node['n'] : null,
            'name'   => ! empty( $node['gap'] ) ? __( 'Not yet documented', 'sampreshan-child' ) : (string) ( $node['name'] ?? '' ),
            'period' => (string) ( $node['period'] ?? '' ),
            'status' => (string) ( $node['status'] ?? '' ),
            'gap'    => ! empty( $node['gap'] ),
            'branch' => $branch,
            'url'    => ! empty( $node['profile'] ) ? (string) get_permalink( get_page_by_path( $node['profile'], OBJECT, 'dharma_profile' ) ) : '',
        );
    };
    foreach ( $pp['nodes'] as $node ) {
        $add( $node );
    }
    foreach ( $pp['branches'] ?? array() as $branch ) {
        foreach ( $branch['nodes'] as $node ) {
            $add( $node, (string) $branch['label'] );
        }
    }
    $data = array(
        'founder' => array( 'name' => $pp['founder']['name'], 'period' => (string) ( $pp['founder']['when'] ?? '' ) ),
        'line'    => $line,
        'base'    => get_stylesheet_directory_uri() . '/assets/',
        'ver'     => SAMPRESHAN_CHILD_VERSION,
    );
    ?>
    <div class="sp-pp3d" data-sp-pp3d hidden>
        <script type="application/json" class="sp-pp3d__data"><?php echo wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
        <div class="sp-pp3d__view">
            <canvas class="sp-pp3d__canvas" role="img" aria-label="<?php esc_attr_e( 'Three dimensional tree of the Guru Parampara. Use the time slider to move through the Acharyas.', 'sampreshan-child' ); ?>"></canvas>
            <p class="sp-pp3d__loading"><?php esc_html_e( 'Growing the tree', 'sampreshan-child' ); ?></p>
            <div class="sp-pp3d__card" aria-live="polite">
                <span class="sp-pp3d__num"></span>
                <strong class="sp-pp3d__name"></strong>
                <span class="sp-pp3d__period"></span>
                <a class="sp-pp3d__link" href="#" hidden><?php esc_html_e( 'View profile', 'sampreshan-child' ); ?></a>
            </div>
        </div>
        <div class="sp-pp3d__time">
            <label class="sp-pp3d__label" for="sp-pp3d-range-<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Kaal Khanda: move through time', 'sampreshan-child' ); ?></label>
            <input id="sp-pp3d-range-<?php echo esc_attr( $slug ); ?>" class="sp-pp3d__range" type="range" min="0" max="<?php echo esc_attr( count( $line ) ); ?>" step="1" value="<?php echo esc_attr( count( $line ) ); ?>" />
            <div class="sp-pp3d__ends"><span><?php echo esc_html( $pp['founder']['name'] ); ?></span><span><?php esc_html_e( 'Present', 'sampreshan-child' ); ?></span></div>
        </div>
        <p class="sp-pp3d__credit"><?php esc_html_e( 'Drag to turn the tree, tap a lamp to read it. Tree model: Kenney Nature Kit (CC0).', 'sampreshan-child' ); ?></p>
    </div>
    <?php
}
