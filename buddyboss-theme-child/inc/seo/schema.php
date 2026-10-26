<?php
/**
 * Sampreshan SEO — structured data (JSON-LD) + social fallbacks.
 *
 * Rank Math already covers titles, meta, canonical, OG basics and the
 * homepage graph, so this file ONLY adds what Rank Math cannot know:
 * petition interaction counts (Is), collection ItemLists for listing
 * pages, member/profile Person graphs, and a logo fallback for the
 * OG image on cover-less petitions. Nothing here duplicates Rank Math.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_seo_site_org() {
    $logo = function_exists( 'sp_logo_url' ) ? sp_logo_url() : get_site_icon_url( 512 );
    $org = array(
        '@type' => 'Organization',
        '@id'   => home_url( '/#organization' ),
        'name'  => get_bloginfo( 'name' ),
        'url'   => home_url( '/' ),
        'description' => function_exists( 'sp_mission_short' ) ? sp_mission_short() : '',
        'logo'  => $logo ? array( '@type' => 'ImageObject', 'url' => $logo ) : home_url( '/' ),
    );
    if ( ! is_singular( 'dharma_profile' ) && ! is_post_type_archive( 'dharma_profile' ) && ! is_tax( 'dharma_peeth' ) ) {
        $org['sameAs'] = array( 'https://shivbodhtrust.org' );
    }
    return $org;
}

function sp_seo_petition_ids( $args = array() ) {
    $q = new WP_Query( array_merge( array(
        'post_type'      => 'petition',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ), $args ) );
    $ids = $q->posts ? array_map( 'intval', (array) $q->posts ) : array();
    wp_reset_postdata();
    return $ids;
}

function sp_seo_itemlist( $ids, $kind = 'Article' ) {
    $items = array();
    $pos   = 1;
    foreach ( (array) $ids as $id ) {
        $id = (int) $id;
        if ( $id <= 0 ) { continue; }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'url'      => get_permalink( $id ),
            'name'     => get_the_title( $id ),
            'item'     => array( '@type' => $kind, 'url' => get_permalink( $id ), 'name' => get_the_title( $id ) ),
        );
    }
    return $items;
}

/**
 * Print page-specific JSON-LD (single graph per page).
 */
function sp_seo_print_schema() {
    if ( is_admin() || ! function_exists( 'get_queried_object_id' ) ) { return; }
    $graph = null;

    /* --- Single petition: Article + I interaction counter --- */
    if ( is_singular( 'petition' ) ) {
        $pid = get_queried_object_id();
        $post = get_post( $pid );
        if ( $post && 'petition' === $post->post_type ) {
            $count = function_exists( 'sp_petition_signature_count' ) ? (int) sp_petition_signature_count( $pid ) : (int) get_post_meta( $pid, 'sampreshan_signatures', true );
            $goal  = (int) get_post_meta( $pid, 'sampreshan_goal', true );
            $cover = get_the_post_thumbnail_url( $pid, 'large' );
            $logo  = function_exists( 'sp_logo_url' ) ? sp_logo_url() : '';
            $author_id = (int) $post->post_author;
            $article = array(
                '@type'            => 'Article',
                'mainEntityOfPage' => get_permalink( $pid ),
                'headline'         => get_the_title( $pid ),
                'description'      => has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '...' ),
                'image'            => $cover ? $cover : $logo,
                'datePublished'    => get_post_time( 'c', true, $pid ),
                'dateModified'     => get_post_modified_time( 'c', true, $pid ),
                'author'           => array(
                    '@type' => 'Person',
                    'name'  => $author_id > 0 ? get_the_author_meta( 'display_name', $author_id ) : get_bloginfo( 'name' ),
                ),
                'publisher'        => sp_seo_site_org(),
                'interactionStatistic' => array(
                    '@type'              => 'InteractionCounter',
                    'interactionType'    => 'https://schema.org/LikeAction',
                    'userInteractionCount' => $count,
                ),
            );
            if ( $goal > 0 ) { $article['interactionStatistic']['target'] = $goal; }
            $graph = $article;
        }
    }
    /* --- All-petitions listing --- */
    elseif ( is_page_template( 'template-petitions.php' ) ) {
        $ids = sp_seo_petition_ids();
        if ( $ids ) {
            $graph = array(
                '@type'           => 'CollectionPage',
                'mainEntityOfPage'=> get_permalink(),
                'name'            => get_the_title(),
                'description'     => has_excerpt() ? get_the_excerpt() : '',
                'mainEntity'      => array( '@type' => 'ItemList', 'itemListElement' => sp_seo_itemlist( $ids ) ),
            );
        }
    }
    /* --- Cause category page --- */
    elseif ( is_page_template( 'template-category.php' ) ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $term = str_replace( 'category-', '', (string) $slug );
        $ids  = $term ? sp_seo_petition_ids( array(
            'tax_query' => array( array( 'taxonomy' => 'cause_category', 'field' => 'slug', 'terms' => $term ) ),
        ) ) : array();
        $graph = array(
            '@type'           => 'CollectionPage',
            'mainEntityOfPage'=> get_permalink(),
            'name'            => get_the_title(),
            'about'           => get_the_title(),
            'mainEntity'      => array( '@type' => 'ItemList', 'itemListElement' => sp_seo_itemlist( $ids ) ),
        );
    }
    /* --- Community members grid --- */
    elseif ( is_page_template( 'template-members.php' ) ) {
        $users = get_users( array( 'number' => 12, 'orderby' => 'registered', 'order' => 'DESC', 'fields' => array( 'ID', 'display_name' ) ) );
        $items = array();
        $pos   = 1;
        foreach ( (array) $users as $u ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'url'      => get_author_posts_url( (int) $u->ID ),
                'name'     => $u->display_name,
            );
        }
        if ( $items ) {
            $graph = array(
                '@type'           => 'CollectionPage',
                'mainEntityOfPage'=> get_permalink(),
                'name'            => get_the_title(),
                'mainEntity'      => array( '@type' => 'ItemList', 'itemListElement' => $items ),
            );
        }
    }
    /* --- Own profile page --- */
    elseif ( is_page_template( 'template-profile.php' ) && is_user_logged_in() ) {
        $uid = get_current_user_id();
        $graph = array(
            '@type'           => 'ProfilePage',
            'mainEntityOfPage'=> get_permalink(),
            'mainEntity'      => array(
                '@type' => 'Person',
                'name'  => wp_get_current_user()->display_name,
                'url'   => get_author_posts_url( $uid ),
            ),
        );
    }
    /* --- Dharma profile: Peeth = Organization, Acharya = Person, Sampradaya = Thing. --- */
    elseif ( is_singular( 'dharma_profile' ) ) {
        $profile_id = get_queried_object_id();
        $profile    = get_post( $profile_id );
        if ( $profile ) {
            $url  = get_permalink( $profile_id );
            $desc = sp_seo_dharma_description( $profile_id );
            $graph = array(
                '@type'            => 'ProfilePage',
                '@id'              => $url . '#webpage',
                'url'              => $url,
                'name'             => get_the_title( $profile_id ),
                'description'      => $desc,
                'inLanguage'       => 'en',
                'dateModified'     => get_post_modified_time( 'c', true, $profile_id ),
                'isPartOf'         => array( '@id' => home_url( '/#website' ) ),
                'publisher'        => sp_seo_site_org(),
                'mainEntity'       => sp_seo_dharma_entity( $profile_id, true ),
                'breadcrumb'       => array(
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => array(
                        array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
                        array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Peeths, Acharyas & Sampradayas', 'item' => sp_seo_dharma_directory_url() ),
                        array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title( $profile_id ), 'item' => $url ),
                    ),
                ),
            );
            $events = sp_seo_dharma_events( array( $profile_id ), 20 );
            if ( $events ) {
                $graph['hasPart'] = array( '@type' => 'ItemList', 'name' => 'Programmes and events', 'itemListElement' => $events );
            }
        }
    }
    /* --- Dharma directory and Peeth listings. --- */
    elseif ( is_post_type_archive( 'dharma_profile' ) || is_tax( 'dharma_peeth' ) ) {
        $ids = get_posts( array(
            'post_type'      => 'dharma_profile',
            'post_status'    => 'publish',
            'posts_per_page' => 60,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
        ) );
        $graph = array(
            '@type'           => 'CollectionPage',
            'url'             => is_tax() ? get_term_link( get_queried_object() ) : sp_seo_dharma_directory_url(),
            'name'            => is_tax() ? single_term_title( '', false ) : __( 'Peeths, Acharyas & Sampradayas', 'sampreshan-child' ),
            'description'     => function_exists( 'sp_mission_short' ) ? sp_mission_short() : '',
            'inLanguage'      => 'en',
            'publisher'       => sp_seo_site_org(),
            'mainEntity'      => array( '@type' => 'ItemList', 'numberOfItems' => count( $ids ), 'itemListElement' => sp_seo_dharma_itemlist( $ids ) ),
        );
    }
    /* --- Home: the one-place directory + live calendar. --- */
    elseif ( is_front_page() ) {
        $ids = get_posts( array(
            'post_type'      => 'dharma_profile',
            'post_status'    => 'publish',
            'posts_per_page' => 60,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
            'meta_query'     => array( array( 'key' => '_sp_dharma_kind', 'value' => array( 'peeth', 'acharya' ), 'compare' => 'IN' ) ),
        ) );
        $graph = array(
            '@type'       => 'WebPage',
            '@id'         => home_url( '/#sp-dharma' ),
            'url'         => home_url( '/' ),
            'name'        => 'Peeths, Acharyas and their programmes in one place',
            'description' => function_exists( 'sp_mission_short' ) ? sp_mission_short() : '',
            'inLanguage'  => 'en',
            'publisher'   => sp_seo_site_org(),
            'about'       => array( 'Shankaracharya Peeths', 'Sanatana Dharma Acharyas', 'Sampradayas of Bharat', 'Dharma calendar' ),
            'mainEntity'  => array( '@type' => 'ItemList', 'name' => 'Peeths and Acharyas', 'itemListElement' => sp_seo_dharma_itemlist( $ids ) ),
        );
        $events = sp_seo_dharma_events( null, 20 );
        if ( $events ) {
            $graph['hasPart'] = array( '@type' => 'ItemList', 'name' => 'Dharma calendar', 'itemListElement' => $events );
        }
    }

    if ( ! $graph ) { return; }
    $graph['@context'] = 'https://schema.org';
    return $graph;
}

/* ---------------------------------------------------------------------
 * Dharma profile helpers
 * ------------------------------------------------------------------ */

function sp_seo_dharma_directory_url() {
    return function_exists( 'sp_dharma_directory_url' ) ? sp_dharma_directory_url() : home_url( '/dharma-acharya/' );
}

function sp_seo_dharma_kind( $profile_id ) {
    $kind = function_exists( 'sp_dharma_profile_kind' ) ? sp_dharma_profile_kind( $profile_id ) : get_post_meta( $profile_id, '_sp_dharma_kind', true );
    return in_array( $kind, array( 'peeth', 'acharya', 'sampradaya' ), true ) ? $kind : 'acharya';
}

/** Description: excerpt first, else a kind-specific sentence. */
function sp_seo_dharma_description( $profile_id ) {
    $text = has_excerpt( $profile_id ) ? get_the_excerpt( $profile_id ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $profile_id ) ) ), 26, '...' );
    $text = trim( preg_replace( '/\s+/', ' ', (string) $text ) );
    if ( '' !== $text && false === stripos( $text, 'skip to main content' ) ) {
        return $text;
    }
    $name = get_the_title( $profile_id );
    switch ( sp_seo_dharma_kind( $profile_id ) ) {
        case 'peeth':
            return $name . ': history, guru parampara, present Acharya, teachings and a live calendar of programmes, checked from official sources on SampreShan.';
        case 'sampradaya':
            return $name . ': origin, teachings, key texts, practice, branches and centres, presented in one place on SampreShan.';
        default:
            return $name . ': life, teachings, place in the guru parampara and upcoming programmes, checked from official sources on SampreShan.';
    }
}

/** schema.org entity for a profile; $deep adds links to related profiles. */
function sp_seo_dharma_entity( $profile_id, $deep = false ) {
    $kind  = sp_seo_dharma_kind( $profile_id );
    $url   = get_permalink( $profile_id );
    $types = array( 'peeth' => 'Organization', 'acharya' => 'Person', 'sampradaya' => 'Thing' );
    $e     = array(
        '@type' => $types[ $kind ],
        '@id'   => $url . '#entity',
        'name'  => get_the_title( $profile_id ),
        'url'   => $url,
    );
    $img = function_exists( 'sp_dharma_profile_image_url' ) ? sp_dharma_profile_image_url( $profile_id, 'large' ) : '';
    if ( $img ) {
        $e['image'] = $img;
    }
    if ( ! $deep ) {
        return $e;
    }
    $e['description'] = sp_seo_dharma_description( $profile_id );
    $data = function_exists( 'sp_dharma_data' ) ? (array) sp_dharma_data( $profile_id ) : array();
    $ref  = static function ( $slug ) {
        $p = function_exists( 'sp_dharma_profile_by_slug' ) ? sp_dharma_profile_by_slug( $slug ) : null;
        return $p ? sp_seo_dharma_entity( $p->ID ) : null;
    };
    if ( 'acharya' === $kind && ! empty( $data['peeth'] ) && ( $peeth = $ref( $data['peeth'] ) ) ) {
        $e['affiliation'] = $peeth;
        $e['jobTitle']    = 'Acharya';
    }
    if ( 'peeth' === $kind && ! empty( $data['acharyas'] ) ) {
        $members = array_values( array_filter( array_map( $ref, (array) $data['acharyas'] ) ) );
        if ( $members ) {
            $e['member'] = $members;
        }
        $e['knowsAbout'] = array( 'Advaita Vedanta', 'Sanatana Dharma' );
    }
    return $e;
}

function sp_seo_dharma_itemlist( $ids ) {
    $items = array();
    $pos   = 1;
    foreach ( (array) $ids as $id ) {
        $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'item' => sp_seo_dharma_entity( (int) $id ) );
    }
    return $items;
}

/** Calendar entries of kind "event" as schema.org Event list items. */
function sp_seo_dharma_events( $profile_ids = null, $limit = 20 ) {
    if ( ! function_exists( 'sp_dcal_items' ) ) {
        return array();
    }
    $items = array();
    $pos   = 1;
    foreach ( sp_dcal_items( $profile_ids, 80 ) as $it ) {
        if ( 'event' !== $it['kind'] || ! preg_match( '/^\d{4}-\d{2}-\d{2}/', (string) $it['date'] ) ) {
            continue;
        }
        $pid = (int) get_post_meta( $it['id'], '_sp_dharma_profile_id', true );
        $ev  = array(
            '@type'               => 'Event',
            'name'                => $it['title'],
            'startDate'           => substr( $it['date'], 0, 10 ),
            'url'                 => $it['link'],
            'eventStatus'         => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'isAccessibleForFree' => true,
        );
        if ( $pid ) {
            $org = sp_seo_dharma_entity( $pid );
            $ev['organizer'] = $org;
            $ev['location']  = array( '@type' => 'Place', 'name' => get_the_title( $pid ) );
        }
        if ( $it['thumb'] ) {
            $ev['image'] = $it['thumb'];
        }
        $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'item' => $ev );
        if ( $pos > $limit ) {
            break;
        }
    }
    return $items;
}

/**
 * Add the child-theme graph to Rank Math's graph. Rank Math's json_ld data is
 * keyed by entity name (it builds @graph later), so append under our own key.
 * Where Rank Math prints no JSON-LD (e.g. dharma profiles), print it ourselves.
 */
function sp_seo_rank_math_graph( $data ) {
    $graph = sp_seo_print_schema();
    if ( ! $graph || ! is_array( $data ) ) {
        return $data;
    }
    unset( $graph['@context'] );
    $data['sp_child_graph']           = $graph;
    $GLOBALS['sp_seo_graph_printed'] = true;
    return $data;
}

function sp_seo_print_fallback_schema() {
    if ( ! empty( $GLOBALS['sp_seo_graph_printed'] ) ) {
        return;
    }
    $graph = sp_seo_print_schema();
    if ( $graph ) {
        echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }
}

if ( function_exists( 'rank_math' ) ) {
    add_filter( 'rank_math/json_ld', 'sp_seo_rank_math_graph', 99, 1 );
}
add_action( 'wp_head', 'sp_seo_print_fallback_schema', 99 );

/** Stable titles and descriptions for the public SampreShan information pages. */
function sp_seo_public_description( $description ) {
    if ( is_front_page() ) {
        return 'SampreShan: one free, public place for accurate information on the Shankaracharya Peeths, their Acharyas and sampradayas, with a live calendar of programmes, community dialogue and public causes.';
    }

    if ( is_singular( 'dharma_profile' ) ) {
        return wp_trim_words( sp_seo_dharma_description( get_queried_object_id() ), 30, '...' );
    }

    if ( is_post_type_archive( 'dharma_profile' ) && function_exists( 'sp_mission_short' ) ) {
        return sp_mission_short();
    }

    $descriptions = array(
        'about'                 => 'Learn about SampreShan, a community platform for Sanatana Dharma, dialogue, education and positive social participation.',
        'contact'               => 'Contact SampreShan for community questions, feedback, collaboration and platform support.',
        'contact-2'             => 'Contact SampreShan for community questions, feedback, collaboration and platform support.',
        'community-guidelines'  => 'Read the SampreShan community guidelines for respectful dialogue, participation and shared Sanatana values.',
        'petitions'             => 'Discover and support public petitions from the SampreShan community across social welfare, culture, education and dharmic causes.',
        'community-center'      => 'Read the latest SampreShan community stories, updates and petitions from members across the sangha.',
        'community'             => 'Connect with SampreShan members and discover community conversations, groups and shared causes.',
        'start-a-petition'      => 'Start a petition on SampreShan and bring a meaningful Sanatana, cultural or community cause to people who can support it.',
        'privacy-policy'        => 'Read the SampreShan privacy policy and learn how account, community and platform data is handled.',
        'terms-conditions'     => 'Read the SampreShan terms and conditions for using the platform and participating in the community.',
        'disclaimer'            => 'Read the SampreShan disclaimer covering platform information, community content and user participation.',
    );

    $slug = is_page() ? (string) get_post_field( 'post_name', get_queried_object_id() ) : '';
    if ( isset( $descriptions[ $slug ] ) ) {
        return $descriptions[ $slug ];
    }

    if ( is_singular( 'post' ) ) {
        $content = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 28, '...' );
        return $content ?: 'A community story shared on SampreShan, the Sanatana dialogue and participation platform.';
    }

    if ( is_singular( 'petition' ) ) {
        $content = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 28, '...' );
        return $content ?: 'Support a meaningful community cause on SampreShan.';
    }

    if ( is_404() ) {
        return 'This page could not be found on SampreShan. Explore petitions, community stories and the Acharyas and Peeths of Sanatana Dharma.';
    }

    if ( is_tax( 'dharma_peeth' ) || is_category() ) {
        $term = get_queried_object();
        $text = $term ? trim( wp_strip_all_tags( term_description( $term ) ) ) : '';
        if ( '' !== $text ) {
            return wp_trim_words( $text, 28, '...' );
        }
        if ( $term && is_tax( 'dharma_peeth' ) ) {
            return $term->name . ': history, tradition, Acharya and latest updates on SampreShan.';
        }
        if ( $term ) {
            return $term->name . ' stories and updates from the SampreShan community.';
        }
    }

    if ( is_page() && ( '' === trim( (string) $description ) || false !== stripos( (string) $description, 'skip to main content' ) ) ) {
        return get_the_title() . ' on SampreShan: Sanatana community, dialogue and positive participation.';
    }

    return $description;
}
add_filter( 'rank_math/frontend/description', 'sp_seo_public_description', 30 );

function sp_seo_public_title( $title ) {
    if ( is_front_page() ) {
        return 'SampreShan | Peeths, Acharyas & Dharma Calendar in One Place';
    }
    if ( is_singular( 'dharma_profile' ) ) {
        $labels = array( 'peeth' => 'Peeth Profile, Acharyas & Calendar', 'acharya' => 'Acharya Profile, Teachings & Programmes', 'sampradaya' => 'Sampradaya: Teachings, Texts & Centres' );
        return get_the_title() . ' | ' . $labels[ sp_seo_dharma_kind( get_queried_object_id() ) ] . ' | SampreShan';
    }
    if ( is_post_type_archive( 'dharma_profile' ) ) {
        return 'Peeths, Acharyas & Sampradayas of Bharat | SampreShan';
    }
    if ( is_singular( 'post' ) ) {
        return get_the_title() . ' | SampreShan Community';
    }
    if ( is_singular( 'petition' ) ) {
        return get_the_title() . ' | SampreShan Petition';
    }
    if ( is_page() && get_the_title() ) {
        return get_the_title() . ' | SampreShan';
    }
    return $title;
}
add_filter( 'rank_math/frontend/title', 'sp_seo_public_title', 30 );

function sp_seo_private_page_robots( $robots ) {
    if ( is_page() && in_array( (string) get_post_field( 'post_name', get_queried_object_id() ), array( 'login', 'register', 'dashboard', 'settings', 'my-petitions', 'signed-petitions', 'account-security' ), true ) ) {
        return array( 'noindex', 'nofollow' );
    }
    return $robots;
}
add_filter( 'rank_math/frontend/robots', 'sp_seo_private_page_robots', 30 );

/** Keep the XML sitemap itself crawlable; individual public URLs remain governed by Rank Math. */
function sp_seo_sitemap_headers( $headers ) {
    if ( is_array( $headers ) ) {
        unset( $headers['X-Robots-Tag'], $headers['x-robots-tag'] );
    }
    return $headers;
}
add_filter( 'rank_math/sitemap/http_headers', 'sp_seo_sitemap_headers', 99 );

add_action( 'send_headers', static function () {
    $path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    if ( false !== strpos( $path, 'sitemap' ) && function_exists( 'header_remove' ) ) {
        header_remove( 'X-Robots-Tag' );
    }
}, 999 );

add_filter( 'robots_txt', static function ( $output, $public ) {
    if ( ! $public ) { return $output; }
    $sitemap = home_url( '/sitemap_index.xml' );
    if ( false === strpos( $output, $sitemap ) ) {
        $output = rtrim( $output ) . "\nSitemap: " . esc_url_raw( $sitemap ) . "\n";
    }
    return $output;
}, 20, 2 );

/**
 * OG image fallback: logo when a petition has no cover
 * (no-ops harmlessly if Rank Math is absent or hooks differ).
 */
function sp_seo_og_image_fallback( $image ) {
    if ( ! empty( $image ) || ! is_singular( 'petition' ) ) { return $image; }
    if ( function_exists( 'sp_logo_url' ) ) {
        $logo = sp_logo_url();
        if ( $logo ) { return $logo; }
    }
    return $image;
}
add_filter( 'rank_math/opengraph/facebook/image', 'sp_seo_og_image_fallback', 20, 1 );
add_filter( 'rank_math/opengraph/twitter/image', 'sp_seo_og_image_fallback', 20, 1 );
