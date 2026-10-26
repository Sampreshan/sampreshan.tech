<?php
/**
 * Dharma Calendar: dated programmes, videos and news for every Peeth and Acharya.
 *
 * Source policy (strict):
 *  1. Official sources first: the Peeth / Acharya website and the social
 *     accounts that the official website itself links to (verified chain).
 *  2. Backup: trusted, established news media only (see
 *     sp_dharma_news_trusted_sources() in inc/dharma-news.php).
 *  3. Nothing is generated, rewritten or guessed. Titles, dates, images and
 *     links are stored exactly as the original source published them, with the
 *     original link and source credit kept on every entry.
 *
 * Readers: TinyFish Fetch (when SP_TINYFISH_API_KEY is defined in wp-config.php)
 * renders script-heavy official pages; a direct HTTP reader is the fallback,
 * so the calendar keeps updating even if TinyFish is unavailable.
 *
 * Social: public Instagram posts are read daily through TinyFish. Facebook
 * pages need a login (TinyFish returns login_required), so Facebook stays a
 * linked source only until a Meta Graph API page token is configured.
 *
 * Manual run: wp eval 'print_r( sp_dcal_import_official() );'
 *             wp eval 'print_r( sp_dcal_import_social() );'
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verified official sources per profile slug. Social accounts listed here are
 * the ones linked from the official website itself.
 */
function sp_dcal_sources() {
    return array(
        'dakshinamnaya-sri-sharada-peetham' => array(
            'website' => 'https://www.sringeri.net/',
            'youtube' => 'UCC7AKcYvtFdlubqwW6Ave2Q',
            'instagram' => 'sharadapeetham',
            'parser'  => 'sringeri',
            'social'  => array(
                'YouTube'   => 'https://www.youtube.com/@sharadapeetham',
                'Facebook'  => 'https://www.facebook.com/sringerimath',
                'Instagram' => 'https://www.instagram.com/sharadapeetham/',
                'X'         => 'https://x.com/sringerimath',
                'Telegram'  => 'https://telegram.me/sringerimath',
                'WhatsApp'  => 'https://www.whatsapp.com/channel/0029VagEL8x6hENn0zfWSn2e',
            ),
        ),
        'kanchi-kamakoti-peetham'           => array(
            'website' => 'https://www.kamakoti.org/',
            'youtube' => 'UCCWq7MrlHSdkfe_2QslyOqA',
            'instagram' => 'kanchimutt',
            'parser'  => 'kanchi',
            'social'  => array(
                'YouTube'   => 'https://www.youtube.com/kanchimath',
                'Facebook'  => 'https://www.facebook.com/srikamakoti/',
                'Instagram' => 'https://www.instagram.com/kanchimutt',
                'X'         => 'https://twitter.com/KanchiMatham',
                'WhatsApp'  => 'https://whatsapp.com/channel/0029VaEaGInA89Mm0d75iW3M',
            ),
        ),
        'purvamnaya-govardhan-matha-puri'   => array(
            'website' => 'https://govardhanpeeth.org/en/',
            'youtube' => 'UCQgYH0jzHqHFWLKvzcgCmWw',
            'social'  => array(
                'YouTube'  => 'https://www.youtube.com/channel/UCQgYH0jzHqHFWLKvzcgCmWw',
                'Facebook' => 'https://www.facebook.com/govardhanpeeth/',
                'X'        => 'https://twitter.com/govardhanmath',
            ),
        ),
        'paschimamnaya-dwarka-sharada-peetham' => array(
            'website' => 'https://dwarakasharadapeetham.org/',
            'youtube' => 'UCWaDkBT_qaoBGPDYTtYWUcg',
            'social'  => array(
                'YouTube' => 'https://www.youtube.com/@DwarakaSharadaPeetham',
                'X'       => 'https://x.com/SriSharadaPeeth',
            ),
        ),
        'swami-avimukta'                    => array(
            'website' => 'https://1008.guru/',
            'youtube' => 'UCGSneKADR6fhEEg7rIGlgNA',
            'instagram' => '1008.guru',
            'social'  => array(
                'YouTube'   => 'https://www.youtube.com/channel/UCGSneKADR6fhEEg7rIGlgNA',
                'Facebook'  => 'https://www.facebook.com/1008.guru/',
                'Instagram' => 'https://www.instagram.com/1008.guru',
                'X'         => 'https://x.com/jyotirmathah',
            ),
        ),
    );
}

/**
 * Official sources to show on a profile page (its own, then its Peeth's).
 */
function sp_dcal_sources_for_profile( $profile_id ) {
    $sources = sp_dcal_sources();
    $slug    = get_post_field( 'post_name', $profile_id );
    if ( isset( $sources[ $slug ] ) ) {
        return $sources[ $slug ];
    }
    $data = sp_dharma_data( $profile_id );
    if ( ! empty( $data['peeth'] ) && isset( $sources[ $data['peeth'] ] ) ) {
        return $sources[ $data['peeth'] ];
    }
    // A Peeth whose Acharya holds the official website (Jyotirmath).
    if ( ! empty( $data['acharyas'] ) ) {
        foreach ( (array) $data['acharyas'] as $acharya ) {
            if ( isset( $sources[ $acharya ] ) ) {
                return $sources[ $acharya ];
            }
        }
    }
    return array();
}

/* -------------------------------------------------------------------------
 * Readers
 * ---------------------------------------------------------------------- */

function sp_dcal_http_get( $url ) {
    $resp = wp_remote_get(
        $url,
        array(
            'timeout'    => 25,
            'user-agent' => 'Mozilla/5.0 (compatible; SampreshanBot/1.0; +https://sampreshan.tech)',
        )
    );
    if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
        return '';
    }
    return (string) wp_remote_retrieve_body( $resp );
}

/**
 * TinyFish Fetch: returns array( text, links, image_links ) or null.
 */
function sp_dcal_tinyfish_fetch( $url ) {
    if ( ! defined( 'SP_TINYFISH_API_KEY' ) || ! SP_TINYFISH_API_KEY ) {
        return null;
    }
    $resp = wp_remote_post(
        'https://api.fetch.tinyfish.ai',
        array(
            'timeout' => 90,
            'headers' => array(
                'X-API-Key'    => SP_TINYFISH_API_KEY,
                'Content-Type' => 'application/json',
            ),
            'body'    => wp_json_encode(
                array(
                    'urls'        => array( $url ),
                    'format'      => 'markdown',
                    'links'       => true,
                    'image_links' => true,
                )
            ),
        )
    );
    if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
        return null;
    }
    $json = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( empty( $json['results'][0]['text'] ) ) {
        return null;
    }
    $r = $json['results'][0];
    return array(
        'text'        => (string) $r['text'],
        'links'       => isset( $r['links'] ) ? (array) $r['links'] : array(),
        'image_links' => isset( $r['image_links'] ) ? (array) $r['image_links'] : array(),
    );
}

/**
 * TinyFish Fetch for several URLs in one request. Returns results keyed by URL.
 */
function sp_dcal_tinyfish_fetch_many( $urls ) {
    if ( ! defined( 'SP_TINYFISH_API_KEY' ) || ! SP_TINYFISH_API_KEY || empty( $urls ) ) {
        return array();
    }
    $resp = wp_remote_post(
        'https://api.fetch.tinyfish.ai',
        array(
            'timeout' => 150,
            'headers' => array(
                'X-API-Key'    => SP_TINYFISH_API_KEY,
                'Content-Type' => 'application/json',
            ),
            'body'    => wp_json_encode( array( 'urls' => array_values( $urls ), 'format' => 'markdown', 'image_links' => true ) ),
        )
    );
    if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
        return array();
    }
    $json = json_decode( wp_remote_retrieve_body( $resp ), true );
    $out  = array();
    foreach ( (array) ( $json['results'] ?? array() ) as $r ) {
        if ( ! empty( $r['url'] ) ) {
            $out[ $r['url'] ] = $r;
        }
    }
    return $out;
}

/**
 * Official Instagram account: newest public posts with caption and date.
 * The caption is kept as published; only the hashtag-only lines are skipped
 * when choosing the title line.
 */
function sp_dcal_fetch_instagram( $handle, $limit = 6 ) {
    $profile = sp_dcal_tinyfish_fetch( 'https://www.instagram.com/' . rawurlencode( $handle ) . '/' );
    if ( ! $profile ) {
        return array();
    }
    $posts = array();
    foreach ( $profile['links'] as $link ) {
        if ( preg_match( '#instagram\.com/(?:[^/]+/)?(p|reel)/([A-Za-z0-9_-]+)#', $link, $m ) ) {
            $posts[ $m[2] ] = array( 'kind' => $m[1], 'url' => 'https://www.instagram.com/' . $m[1] . '/' . $m[2] . '/' );
        }
        if ( count( $posts ) >= $limit ) {
            break;
        }
    }
    $fresh = array();
    foreach ( $posts as $code => $p ) {
        if ( ! sp_dharma_news_seen( 'ig:' . $code, 'ig:' . $code ) ) {
            $fresh[ $code ] = $p;
        }
    }
    if ( ! $fresh ) {
        return array();
    }
    $pages = sp_dcal_tinyfish_fetch_many( wp_list_pluck( $fresh, 'url' ) );
    $items = array();
    foreach ( $fresh as $code => $p ) {
        $r = null;
        foreach ( $pages as $u => $page ) {
            if ( false !== strpos( $u, '/' . $code ) ) {
                $r = $page;
                break;
            }
        }
        if ( ! $r ) {
            continue;
        }
        $desc = (string) ( $r['description'] ?? '' );
        $time = 0;
        if ( preg_match( '/ on ([A-Z][a-z]+ \d{1,2}, \d{4})/', $desc, $dm ) ) {
            $time = strtotime( $dm[1] . ' 12:00:00' );
        } elseif ( ! empty( $r['published_date'] ) ) {
            $time = strtotime( $r['published_date'] );
        }
        $caption = '';
        if ( preg_match( '/: "(.*)"\.?\s*$/s', $desc, $cm ) ) {
            $caption = $cm[1];
        } elseif ( preg_match( '/on Instagram: "(.*)"/s', (string) ( $r['title'] ?? '' ), $cm ) ) {
            $caption = $cm[1];
        }
        $title = '';
        foreach ( preg_split( '/\R/u', $caption ) as $line ) {
            $line = trim( $line );
            if ( '' !== $line && ! preg_match( '/^(#\S+\s*)+$/u', $line ) ) {
                $title = $line;
                break;
            }
        }
        if ( '' === $title || ! $time ) {
            continue;
        }
        $items[] = array(
            'guid'       => 'ig:' . $code,
            'title'      => wp_html_excerpt( $title, 140, '...' ),
            'url'        => $p['url'],
            'source'     => '@' . $handle . ' (official Instagram)',
            'type'       => 'reel' === $p['kind'] ? 'video' : 'news',
            'event_date' => wp_date( 'Y-m-d', $time ),
            'time'       => $time,
            'thumb'      => '',
            'summary'    => wp_html_excerpt( trim( preg_replace( '/\s+/u', ' ', $caption ) ), 280, '...' ),
        );
    }
    return $items;
}

/**
 * Daily import from official social accounts (Instagram). Returns created per slug.
 */
function sp_dcal_import_social() {
    $report = array();
    foreach ( sp_dcal_sources() as $slug => $src ) {
        if ( empty( $src['instagram'] ) ) {
            continue;
        }
        $profile = sp_dharma_profile_by_slug( $slug );
        if ( ! $profile ) {
            continue;
        }
        $items   = sp_dcal_fetch_instagram( $src['instagram'] );
        $created = 0;
        foreach ( $items as $item ) {
            if ( sp_dcal_save_item( $profile, $item ) ) {
                $created++;
            }
        }
        $report[ $slug ] = array( 'found' => count( $items ), 'created' => $created );
    }
    update_option( 'sp_dcal_social_last_run', array( 'time' => time(), 'report' => $report ), false );
    return $report;
}
add_action( 'sp_dcal_social_cron', 'sp_dcal_import_social' );
add_action(
    'init',
    static function () {
        if ( ! wp_next_scheduled( 'sp_dcal_social_cron' ) ) {
            wp_schedule_event( time() + 300, 'daily', 'sp_dcal_social_cron' );
        }
    },
    31
);

/* -------------------------------------------------------------------------
 * Official fetchers. Each returns a list of items:
 * guid, title, url, source, type (event|video|news), event_date (Y-m-d),
 * time (published timestamp), thumb, summary.
 * ---------------------------------------------------------------------- */

/** Official YouTube channel (public RSS feed). Shorts are skipped. */
function sp_dcal_fetch_youtube( $channel_id, $limit = 6 ) {
    $body = sp_dcal_http_get( 'https://www.youtube.com/feeds/videos.xml?channel_id=' . rawurlencode( $channel_id ) );
    if ( '' === $body ) {
        return array();
    }
    $prev = libxml_use_internal_errors( true );
    $xml  = simplexml_load_string( $body );
    libxml_use_internal_errors( $prev );
    if ( ! $xml || empty( $xml->entry ) ) {
        return array();
    }
    $channel = trim( (string) $xml->author->name );
    $items   = array();
    foreach ( $xml->entry as $entry ) {
        $link = '';
        foreach ( $entry->link as $l ) {
            $link = (string) $l['href'];
        }
        if ( false !== strpos( $link, '/shorts/' ) ) {
            continue;
        }
        $yt    = $entry->children( 'http://www.youtube.com/xml/schemas/2015' );
        $media = $entry->children( 'http://search.yahoo.com/mrss/' );
        $vid   = (string) $yt->videoId;
        $time  = strtotime( (string) $entry->published );
        $title = trim( (string) $entry->title );
        if ( '' === $vid || '' === $title || ! $time ) {
            continue;
        }
        $desc    = $media->group ? trim( (string) $media->group->description ) : '';
        $items[] = array(
            'guid'       => 'yt:' . $vid,
            'title'      => $title,
            'url'        => 'https://www.youtube.com/watch?v=' . $vid,
            'source'     => $channel ? $channel . ' (official YouTube)' : 'Official YouTube',
            'type'       => 'video',
            'event_date' => wp_date( 'Y-m-d', $time ),
            'time'       => $time,
            'thumb'      => 'https://i.ytimg.com/vi/' . $vid . '/hqdefault.jpg',
            'summary'    => wp_html_excerpt( $desc, 280, '...' ),
        );
        if ( count( $items ) >= $limit ) {
            break;
        }
    }
    return $items;
}

/** Sringeri: upcoming events (direct HTML) and past events (TinyFish). */
function sp_dcal_fetch_sringeri() {
    $items = array();
    $up    = 'https://www.sringeri.net/events/upcoming-events';
    $html  = sp_dcal_http_get( $up );
    if ( $html && preg_match_all( '#>([A-Z][a-z]+ \d{1,2}, \d{4})</p>.*?uppercase[^"]*">([^<]+)</p><p[^>]*font-pt-serif">([^<]+)</p>#s', $html, $m, PREG_SET_ORDER ) ) {
        foreach ( $m as $row ) {
            $time = strtotime( $row[1] );
            if ( ! $time ) {
                continue;
            }
            $title   = trim( html_entity_decode( $row[3], ENT_QUOTES, 'UTF-8' ) );
            $place   = trim( html_entity_decode( $row[2], ENT_QUOTES, 'UTF-8' ) );
            $items[] = array(
                'guid'       => 'sringeri-event:' . gmdate( 'Y-m-d', $time ) . ':' . md5( $title ),
                'title'      => $title,
                'url'        => $up,
                'source'     => 'sringeri.net (official website)',
                'type'       => 'event',
                'event_date' => gmdate( 'Y-m-d', $time ),
                'time'       => time(),
                'thumb'      => '',
                'summary'    => $place,
            );
        }
    }

    $past = sp_dcal_tinyfish_fetch( 'https://www.sringeri.net/events/past-events' );
    if ( $past ) {
        $text  = $past['text'];
        $start = strpos( $text, 'Past Events' );
        $lines = array_values( array_filter( array_map( 'trim', explode( "\n", false === $start ? $text : substr( $text, $start ) ) ) ) );
        $links = array();
        foreach ( $past['links'] as $l ) {
            $l = is_array( $l ) ? ( $l['url'] ?? '' ) : (string) $l;
            if ( preg_match( '#sringeri\.net/events/([a-z0-9-]+)$#', $l, $lm ) && ! in_array( $lm[1], array( 'calendar', 'past-events', 'upcoming-events' ), true ) ) {
                $links[ $lm[1] ] = $l;
            }
        }
        $count = count( $lines );
        for ( $i = 0; $i < $count - 1; $i++ ) {
            if ( ! preg_match( '/^[A-Z][a-z]+ \d{1,2}, \d{4}$/', $lines[ $i ] ) ) {
                continue;
            }
            $time  = strtotime( $lines[ $i ] );
            $title = ltrim( $lines[ $i + 1 ], '# ' );
            if ( ! $time || strlen( $title ) > 160 || preg_match( '/^[A-Z][a-z]+ \d{1,2}, \d{4}$/', $title ) || $time < time() - 60 * DAY_IN_SECONDS ) {
                continue;
            }
            $slug    = sanitize_title( $title );
            $url     = 'https://www.sringeri.net/events/past-events';
            foreach ( $links as $key => $href ) {
                if ( 0 === strpos( $key, substr( $slug, 0, 30 ) ) ) {
                    $url = $href;
                    break;
                }
            }
            $items[] = array(
                'guid'       => 'sringeri-event:' . gmdate( 'Y-m-d', $time ) . ':' . md5( $title ),
                'title'      => $title,
                'url'        => $url,
                'source'     => 'sringeri.net (official website)',
                'type'       => 'event',
                'event_date' => gmdate( 'Y-m-d', $time ),
                'time'       => $time,
                'thumb'      => '',
                'summary'    => '',
            );
        }
    }
    return $items;
}

/** Kanchi: dated news links on the official home page. */
function sp_dcal_fetch_kanchi() {
    $html = sp_dcal_http_get( 'https://www.kamakoti.org/' );
    if ( '' === $html || ! preg_match_all( '#<a[^>]+href="([^"]*news/(\d{4})/([a-z0-9-]+-(\d{8}))\.html)"[^>]*>(.*?)</a>#s', $html, $m, PREG_SET_ORDER ) ) {
        return array();
    }
    $items = array();
    foreach ( $m as $row ) {
        $title = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $row[5] ), ENT_QUOTES, 'UTF-8' ) ) );
        $title = trim( preg_replace( '/[\s\-]*\d{1,2}-[A-Z][a-z]{2}-\d{4}$/', '', $title ), " -\t" );
        if ( '' === $title || isset( $items[ $row[3] ] ) ) {
            continue;
        }
        $time  = strtotime( $row[4] );
        $thumb = '';
        if ( preg_match( '#<img[^>]+src="([^"]*' . preg_quote( $row[3], '#' ) . '/[^"]+\.(?:jpe?g|png|webp))"#i', $html, $im ) ) {
            $thumb = $im[1];
        }
        $url             = 'https://www.kamakoti.org' . ( '/' === $row[1][0] ? '' : '/' ) . ltrim( $row[1], '/' );
        $items[ $row[3] ] = array(
            'guid'       => 'kanchi:' . $row[3],
            'title'      => $title,
            'url'        => $url,
            'source'     => 'kamakoti.org (official website)',
            'type'       => sp_dharma_news_has_word( $title, sp_dharma_news_programme_words() ) ? 'event' : 'news',
            'event_date' => gmdate( 'Y-m-d', $time ),
            'time'       => $time,
            'thumb'      => $thumb ? ( 0 === strpos( $thumb, 'http' ) ? $thumb : 'https://www.kamakoti.org/' . ltrim( $thumb, '/' ) ) : '',
            'summary'    => '',
        );
    }
    return array_values( $items );
}

/* -------------------------------------------------------------------------
 * Storage
 * ---------------------------------------------------------------------- */

/**
 * Save one item as a dharma_update linked to the profile. Returns post ID or 0.
 */
function sp_dcal_save_item( $profile, $item, $origin = 'official' ) {
    if ( sp_dharma_news_seen( $item['guid'], $item['title'] ) ) {
        return 0;
    }
    $content = '';
    if ( '' !== $item['summary'] ) {
        $content .= '<p>' . esc_html( $item['summary'] ) . '</p>';
    }
    $content .= '<p class="sp-news-origin">' . esc_html__( 'Original source:', 'sampreshan-child' ) . ' <a href="' . esc_url( $item['url'] ) . '" rel="nofollow noopener" target="_blank">' . esc_html( $item['source'] ) . '</a></p>';

    // Future-dated events are stored now; the event date lives in meta.
    $time = min( (int) $item['time'], time() );
    $id   = wp_insert_post(
        array(
            'post_type'     => sp_dharma_update_post_type(),
            'post_status'   => 'publish',
            'post_title'    => wp_strip_all_tags( $item['title'] ),
            'post_content'  => $content,
            'post_excerpt'  => sprintf( __( 'Source: %s', 'sampreshan-child' ), $item['source'] ),
            'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $time ),
            'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $time ) ),
            'meta_input'    => array_merge(
                array(
                    '_sp_dharma_profile_id' => $profile->ID,
                    '_sp_news_guid'         => $item['guid'],
                    '_sp_news_url'          => $item['url'],
                    '_sp_news_source'       => $item['source'],
                    '_sp_news_type'         => 'news' === $item['type'] ? 'news' : 'programme',
                    '_sp_news_kind'         => $item['type'],
                    '_sp_news_origin'       => $origin,
                    '_sp_event_date'        => $item['event_date'],
                    '_sp_news_thumb'        => $item['thumb'],
                ),
                $time < time() - 2 * DAY_IN_SECONDS ? array( '_sp_dharma_update_notified' => 'backfill' ) : array()
            ),
        ),
        true
    );
    return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Import from every verified official source. Returns created count per slug.
 */
function sp_dcal_import_official() {
    $report = array();
    foreach ( sp_dcal_sources() as $slug => $src ) {
        $profile = sp_dharma_profile_by_slug( $slug );
        if ( ! $profile ) {
            continue;
        }
        $items = array();
        if ( ! empty( $src['youtube'] ) ) {
            $items = array_merge( $items, sp_dcal_fetch_youtube( $src['youtube'] ) );
        }
        if ( 'sringeri' === ( $src['parser'] ?? '' ) ) {
            $items = array_merge( $items, sp_dcal_fetch_sringeri() );
        } elseif ( 'kanchi' === ( $src['parser'] ?? '' ) ) {
            $items = array_merge( $items, sp_dcal_fetch_kanchi() );
        }
        $created = 0;
        foreach ( $items as $item ) {
            if ( sp_dcal_save_item( $profile, $item ) ) {
                $created++;
            }
        }
        $report[ $slug ] = array( 'found' => count( $items ), 'created' => $created );
    }
    update_option( 'sp_dcal_last_run', array( 'time' => time(), 'report' => $report ), false );
    return $report;
}

add_action( 'sp_dcal_cron', 'sp_dcal_import_official' );
add_action(
    'init',
    static function () {
        if ( ! wp_next_scheduled( 'sp_dcal_cron' ) ) {
            wp_schedule_event( time() + 120, 'sp_six_hours', 'sp_dcal_cron' );
        }
    },
    31
);

/* -------------------------------------------------------------------------
 * Front end data and markup
 * ---------------------------------------------------------------------- */

/**
 * Filter keys for a profile: itself, its Peeth, and (for a Peeth) its Acharyas.
 */
function sp_dcal_profile_keys( $profile_id ) {
    static $cache = array();
    if ( isset( $cache[ $profile_id ] ) ) {
        return $cache[ $profile_id ];
    }
    $slug = get_post_field( 'post_name', $profile_id );
    $keys = array( $slug );
    $data = sp_dharma_data( $profile_id );
    if ( ! empty( $data['peeth'] ) ) {
        $keys[] = $data['peeth'];
    }
    if ( ! empty( $data['acharyas'] ) ) {
        $keys = array_merge( $keys, (array) $data['acharyas'] );
    }
    $cache[ $profile_id ] = array_values( array_unique( $keys ) );
    return $cache[ $profile_id ];
}

/**
 * Peeth and Acharya choices for the home page filter.
 */
function sp_dcal_filter_options() {
    static $opts = null;
    if ( null !== $opts ) {
        return $opts;
    }
    $opts = array( 'peeth' => array(), 'acharya' => array() );
    $q    = get_posts(
        array(
            'post_type'      => sp_dharma_profile_post_type(),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'meta_query'     => array( array( 'key' => '_sp_dharma_kind', 'value' => array( 'peeth', 'acharya' ), 'compare' => 'IN' ) ),
        )
    );
    foreach ( $q as $p ) {
        $kind                             = sp_dharma_profile_kind( $p->ID );
        $opts[ 'peeth' === $kind ? 'peeth' : 'acharya' ][ $p->post_name ] = get_the_title( $p );
    }
    return $opts;
}

/**
 * Calendar entries for the given profiles (null = all), newest first.
 */
function sp_dcal_items( $profile_ids = null, $limit = 150 ) {
    $args = array(
        'post_type'      => sp_dharma_update_post_type(),
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'no_found_rows'  => true,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    if ( null !== $profile_ids ) {
        $ids = array_values( array_filter( array_map( 'absint', (array) $profile_ids ) ) );
        if ( empty( $ids ) ) {
            return array();
        }
        $args['meta_query'] = array( array( 'key' => '_sp_dharma_profile_id', 'value' => $ids, 'compare' => 'IN' ) );
    }
    $out = array();
    foreach ( get_posts( $args ) as $post ) {
        $pid    = (int) get_post_meta( $post->ID, '_sp_dharma_profile_id', true );
        $date   = get_post_meta( $post->ID, '_sp_event_date', true );
        $kind   = get_post_meta( $post->ID, '_sp_news_kind', true );
        $thumb  = get_post_meta( $post->ID, '_sp_news_thumb', true );
        $origin = get_post_meta( $post->ID, '_sp_news_origin', true );
        if ( ! $kind ) {
            $kind = 'programme' === get_post_meta( $post->ID, '_sp_news_type', true ) ? 'event' : 'news';
        }
        if ( ! $thumb && $pid ) {
            $thumb = sp_dharma_profile_image_url( $pid, 'medium' );
        }
        $out[] = array(
            'id'      => $post->ID,
            'title'   => get_the_title( $post ),
            'date'    => $date ? $date : get_the_date( 'Y-m-d', $post ),
            'kind'    => $kind,
            'origin'  => $origin ? $origin : 'media',
            'source'  => (string) get_post_meta( $post->ID, '_sp_news_source', true ),
            'url'     => (string) get_post_meta( $post->ID, '_sp_news_url', true ),
            'link'    => get_permalink( $post ),
            'thumb'   => $thumb ? $thumb : '',
            'profile' => $pid ? get_the_title( $pid ) : '',
            'keys'    => $pid ? sp_dcal_profile_keys( $pid ) : array(),
        );
    }
    return $out;
}

function sp_dcal_kind_label( $kind ) {
    $labels = array(
        'event' => __( 'Event', 'sampreshan-child' ),
        'video' => __( 'Video', 'sampreshan-child' ),
        'news'  => __( 'News', 'sampreshan-child' ),
    );
    return $labels[ $kind ] ?? $labels['news'];
}

/**
 * One server-rendered entry (no-JS fallback and the home ticker).
 */
function sp_dcal_entry_html( $item, $class = 'sp-dcal__item' ) {
    ob_start();
    ?>
    <article class="<?php echo esc_attr( $class ); ?>">
        <?php if ( $item['thumb'] ) : ?><img class="sp-dcal__thumb" src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" loading="lazy" decoding="async" width="160" height="90" /><?php endif; ?>
        <div>
            <p class="sp-dcal__meta"><span class="sp-dcal__tag sp-dcal__tag--<?php echo esc_attr( $item['kind'] ); ?>"><?php echo esc_html( sp_dcal_kind_label( $item['kind'] ) ); ?></span> <?php echo esc_html( wp_date( 'j M Y', strtotime( $item['date'] ) ) ); ?></p>
            <h3><a href="<?php echo esc_url( $item['url'] ? $item['url'] : $item['link'] ); ?>" rel="nofollow noopener" target="_blank"><?php echo esc_html( $item['title'] ); ?></a></h3>
            <p class="sp-dcal__src"><?php echo esc_html( $item['profile'] ); ?><?php if ( $item['source'] ) : ?> · <?php echo esc_html( $item['source'] ); ?><?php endif; ?></p>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Render the calendar block.
 *
 * @param array $args items, sources (official links), filter (bool), ticker (bool).
 */
function sp_dcal_render( $args ) {
    $items   = $args['items'] ?? array();
    $sources = $args['sources'] ?? array();
    $filter  = ! empty( $args['filter'] );
    $payload = array(
        'today' => wp_date( 'Y-m-d' ),
        'items' => $items,
    );
    ?>
    <div class="sp-dcal" data-sp-dcal>
        <?php if ( ! empty( $args['ticker'] ) && $items ) : ?>
            <div class="sp-dcal__ticker" aria-label="<?php esc_attr_e( 'Latest updates', 'sampreshan-child' ); ?>">
                <div class="sp-dcal__track">
                    <?php
                    $tick = array_slice( $items, 0, 16 );
                    foreach ( array( false, true ) as $dup ) {
                        echo '<div class="sp-dcal__run"' . ( $dup ? ' aria-hidden="true"' : '' ) . '>';
                        foreach ( $tick as $t ) {
                            echo sp_dcal_entry_html( $t, 'sp-dcal__tile' ); // phpcs:ignore WordPress.Security.EscapeOutput
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $filter ) : ?>
            <div class="sp-dcal__filter">
                <label for="sp-dcal-filter"><?php esc_html_e( 'Show updates for', 'sampreshan-child' ); ?></label>
                <select id="sp-dcal-filter" data-sp-dcal-filter>
                    <option value=""><?php esc_html_e( 'All Peeths and Acharyas', 'sampreshan-child' ); ?></option>
                    <?php foreach ( sp_dcal_filter_options() as $group => $opts ) : ?>
                        <optgroup label="<?php echo esc_attr( 'peeth' === $group ? __( 'Peeths', 'sampreshan-child' ) : __( 'Acharyas', 'sampreshan-child' ) ); ?>">
                            <?php foreach ( $opts as $slug => $label ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="sp-dcal__grid">
            <div class="sp-dcal__cal" data-sp-dcal-cal aria-live="polite"></div>
            <div class="sp-dcal__panel">
                <h3 class="sp-dcal__panel-title" data-sp-dcal-title><?php esc_html_e( 'Upcoming and recent', 'sampreshan-child' ); ?></h3>
                <div class="sp-dcal__list" data-sp-dcal-list>
                    <?php
                    if ( $items ) {
                        foreach ( array_slice( $items, 0, 8 ) as $it ) {
                            echo sp_dcal_entry_html( $it ); // phpcs:ignore WordPress.Security.EscapeOutput
                        }
                    } else {
                        echo '<p class="sp-dharma-muted">' . esc_html__( 'Verified programmes and news will appear here as soon as an official source publishes them.', 'sampreshan-child' ) . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="sp-dcal__sources">
            <?php if ( $sources ) : ?>
                <p><strong><?php esc_html_e( 'Official sources:', 'sampreshan-child' ); ?></strong>
                    <a href="<?php echo esc_url( $sources['website'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( wp_parse_url( $sources['website'], PHP_URL_HOST ) ); ?></a>
                    <?php foreach ( $sources['social'] as $net => $url ) : ?> · <a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $net ); ?></a><?php endforeach; ?>
                </p>
            <?php elseif ( empty( $args['all'] ) ) : ?>
                <p><?php esc_html_e( 'No verified official website or social account has been confirmed yet. Until then, only reports from trusted news media are shown.', 'sampreshan-child' ); ?></p>
            <?php endif; ?>
            <p class="sp-dcal__policy"><?php esc_html_e( 'Every entry links to its original source. Only official Peeth and Acharya channels and trusted news media are used; nothing here is generated or rewritten.', 'sampreshan-child' ); ?></p>
        </div>

        <script type="application/json" class="sp-dcal__data"><?php echo wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ); ?></script>
    </div>
    <?php
}

/**
 * One-time audit of older updates: official-site entries are tagged official;
 * anything from an untrusted outlet, or not naming a Peeth / Acharya, is moved
 * to draft (never deleted). Run: wp eval 'print_r( sp_dcal_audit_existing() );'
 */
function sp_dcal_audit_existing() {
    $done = array( 'official' => 0, 'kept' => 0, 'drafted' => 0 );
    $ids  = get_posts(
        array(
            'post_type'      => sp_dharma_update_post_type(),
            'post_status'    => array( 'publish', 'pending' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
        )
    );
    foreach ( $ids as $id ) {
        $origin = get_post_meta( $id, '_sp_news_origin', true );
        $source = (string) get_post_meta( $id, '_sp_news_source', true );
        if ( 'official' === $origin ) {
            continue;
        }
        if ( false !== stripos( $source, 'Sringeri Sharada Peetham' ) ) {
            update_post_meta( $id, '_sp_news_origin', 'official' );
            update_post_meta( $id, '_sp_news_kind', 'event' );
            $done['official']++;
            continue;
        }
        if ( ! sp_dharma_news_is_trusted( $source ) || ! sp_dharma_news_is_relevant( get_the_title( $id ) ) ) {
            wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
            $done['drafted']++;
            continue;
        }
        if ( ! get_post_meta( $id, '_sp_news_origin', true ) ) {
            update_post_meta( $id, '_sp_news_origin', 'media' );
        }
        $done['kept']++;
    }
    return $done;
}
