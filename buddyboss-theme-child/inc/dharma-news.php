<?php
/**
 * Automatic Peeth / Acharya news and programme updates.
 *
 * Replaces the old Zyte `public_feeds` spider (it only read Facebook page titles
 * and its results never reached the site). Every six hours WP-Cron reads public
 * Google News RSS searches for each Peeth and Acharya and saves new headlines as
 * `dharma_update` posts linked to the profile. Publishing an update already
 * notifies followers and shows it on their Community Center feed.
 *
 * Headlines touching sensitive topics (courts, police, politics) are saved as
 * "pending" for an editor to review instead of being published automatically.
 *
 * Manual run: wp eval 'sp_dharma_news_import();'
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Profile slug => Google News search query.
 */
function sp_dharma_news_queries() {
    return array(
        'swami-nishchalananda-saraswati'                         => '"Nischalananda Saraswati" OR "Puri Shankaracharya"',
        'swami-sadanand-saraswati'                               => '"Sadanand Saraswati" Shankaracharya',
        'swami-avimukta'                                         => '"Avimukteshwaranand"',
        'swami-vidhushekhara-bharati'                            => '"Vidhushekhara Bharati"',
        'sri-satya-chandrashekarendra-saraswathi-shankaracharya' => '"Satya Chandrasekharendra" OR "Satya Chandrashekarendra"',
        'dakshinamnaya-sri-sharada-peetham'                      => '"Sringeri Sharada Peetham" OR "Sringeri Math" OR "Sringeri Jagadguru" OR "Bharati Tirtha"',
        'kanchi-kamakoti-peetham'                                => '"Kanchi Kamakoti Peetham" OR "Kanchi Math" OR "Vijayendra Saraswati"',
        'purvamnaya-govardhan-matha-puri'                        => '"Govardhan Math" OR "Govardhan Peeth" Puri',
        'paschimamnaya-dwarka-sharada-peetham'                   => '"Dwarka Sharada Peeth" OR "Dwarka Peeth"',
        'uttaraminaya-jyotirmath-peeth'                          => '"Jyotirmath" OR "Jyotish Peeth" Shankaracharya',
    );
}

/** Headlines with these words go to review instead of publishing. */
function sp_dharma_news_review_words() {
    return array( 'court', 'bail', 'fir ', 'arrest', 'police', 'case against', 'controvers', 'fact check', 'factcheck', 'fake', 'ai-generated', 'party', 'election', 'slams', 'attack', ' row', 'accused', 'protest', 'murder', 'probe', 'cbi', 'politic', 'hypocrite', 'so-called', 'hits out', 'slam', 'sparks', 'debate', 'bjp', 'congress', 'rss ', 'digvijaya', 'backs ', 'minister', ' mla', ' mp ', 'vs ', 'outrage', 'demand' );
}

/** Headlines with these words are marked as programmes / events. */
function sp_dharma_news_programme_words() {
    return array( 'will ', 'to visit', 'visit', 'upcoming', 'yatra', 'chaturmas', 'utsav', 'utsava', 'festival', 'programme', 'program', 'aradhana', 'jayanti', 'satsang', 'pravachan', 'discourse', 'darshan', 'navaratri', 'kumbh', 'camp', 'celebrat', 'inaugurat', 'consecrat', 'kumbhabhishek', 'puja', 'pooja', 'homa', 'yagna', 'yajna' );
}

function sp_dharma_news_has_word( $text, $words ) {
    $text = ' ' . strtolower( $text ) . ' ';
    foreach ( $words as $w ) {
        if ( false !== strpos( $text, $w ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Fetch one query; returns list of array( guid, title, source, url, time ).
 */
function sp_dharma_news_fetch( $query ) {
    $url  = add_query_arg(
        array( 'q' => rawurlencode( $query . ' when:30d' ), 'hl' => 'en-IN', 'gl' => 'IN', 'ceid' => 'IN:en' ),
        'https://news.google.com/rss/search'
    );
    $resp = wp_remote_get( $url, array( 'timeout' => 20, 'user-agent' => 'SampreshanBot/1.0 (+https://sampreshan.tech)' ) );
    if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
        return array();
    }
    $prev = libxml_use_internal_errors( true );
    $xml  = simplexml_load_string( wp_remote_retrieve_body( $resp ) );
    libxml_use_internal_errors( $prev );
    if ( ! $xml || empty( $xml->channel->item ) ) {
        return array();
    }
    $items = array();
    foreach ( $xml->channel->item as $item ) {
        $source = trim( (string) $item->source );
        $title  = trim( html_entity_decode( (string) $item->title, ENT_QUOTES, 'UTF-8' ) );
        if ( $source && substr( $title, -strlen( ' - ' . $source ) ) === ' - ' . $source ) {
            $title = substr( $title, 0, -strlen( ' - ' . $source ) );
        }
        $title = preg_replace( '/^[A-Z][A-Za-z ]{2,20} \| /', '', $title );
        $time  = strtotime( (string) $item->pubDate );
        if ( '' === $title || ! $time ) {
            continue;
        }
        $items[] = array(
            'guid'   => (string) $item->guid,
            'title'  => $title,
            'source' => $source,
            'url'    => esc_url_raw( (string) $item->link ),
            'time'   => $time,
        );
    }
    usort( $items, static function ( $a, $b ) { return $b['time'] - $a['time']; } );
    return $items;
}

function sp_dharma_news_seen( $guid, $title ) {
    global $wpdb;
    return (bool) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_sp_news_guid'
             WHERE p.post_type = %s AND ( m.meta_value = %s OR p.post_title = %s ) LIMIT 1",
            sp_dharma_update_post_type(),
            $guid,
            $title
        )
    );
}

/**
 * Import new headlines. Returns number of updates created.
 */
function sp_dharma_news_import( $per_profile = 3 ) {
    if ( ! function_exists( 'sp_dharma_profile_by_slug' ) ) {
        return 0;
    }
    $created = 0;
    foreach ( sp_dharma_news_queries() as $slug => $query ) {
        $profile = sp_dharma_profile_by_slug( $slug );
        if ( ! $profile ) {
            continue;
        }
        $added = 0;
        foreach ( sp_dharma_news_fetch( $query ) as $item ) {
            if ( $added >= $per_profile ) {
                break;
            }
            if ( $item['time'] < time() - 30 * DAY_IN_SECONDS || sp_dharma_news_seen( $item['guid'], $item['title'] ) ) {
                continue;
            }
            $review    = sp_dharma_news_has_word( $item['title'], sp_dharma_news_review_words() );
            $programme = sp_dharma_news_has_word( $item['title'], sp_dharma_news_programme_words() );
            $date      = wp_date( get_option( 'date_format' ), $item['time'] );
            $content   = sprintf(
                '<p>%1$s</p><p class="sp-news-origin">%2$s</p>',
                esc_html( sprintf( __( 'Update on %1$s, %2$s.', 'sampreshan-child' ), get_the_title( $profile ), $date ) ),
                sprintf(
                    /* translators: %s: publication name. */
                    esc_html__( 'Original report: %s', 'sampreshan-child' ),
                    '<a href="' . esc_url( $item['url'] ) . '" rel="nofollow noopener" target="_blank">' . esc_html( $item['source'] ? $item['source'] : __( 'news report', 'sampreshan-child' ) ) . '</a>'
                )
            );
            $id = wp_insert_post(
                array(
                    'post_type'     => sp_dharma_update_post_type(),
                    'post_status'   => $review ? 'pending' : 'publish',
                    'post_title'    => wp_strip_all_tags( $item['title'] ),
                    'post_content'  => $content,
                    'post_excerpt'  => $item['source'] ? sprintf( __( 'Reported by %s', 'sampreshan-child' ), $item['source'] ) : '',
                    'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $item['time'] ),
                    'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $item['time'] ) ),
                    'meta_input'    => array_merge(
                        array(
                            '_sp_dharma_profile_id' => $profile->ID,
                            '_sp_news_guid'         => $item['guid'],
                            '_sp_news_url'          => $item['url'],
                            '_sp_news_source'       => $item['source'],
                            '_sp_news_type'         => $programme ? 'programme' : 'news',
                        ),
                        // Only notify followers about fresh news, not the backlog.
                        $item['time'] < time() - 3 * DAY_IN_SECONDS ? array( '_sp_dharma_update_notified' => 'backfill' ) : array()
                    ),
                ),
                true
            );
            if ( ! is_wp_error( $id ) ) {
                $added++;
                $created++;
            }
        }
    }
    update_option( 'sp_dharma_news_last_run', array( 'time' => time(), 'created' => $created ), false );
    return $created;
}

add_filter(
    'cron_schedules',
    static function ( $schedules ) {
        $schedules['sp_six_hours'] = array( 'interval' => 6 * HOUR_IN_SECONDS, 'display' => 'Every 6 hours' );
        return $schedules;
    }
);
add_action( 'sp_dharma_news_cron', 'sp_dharma_news_import' );
add_action(
    'init',
    static function () {
        if ( ! wp_next_scheduled( 'sp_dharma_news_cron' ) ) {
            wp_schedule_event( time() + 60, 'sp_six_hours', 'sp_dharma_news_cron' );
        }
    },
    30
);

/**
 * Latest published updates across all Peeths, optionally of one type.
 */
function sp_dharma_news_latest( $type = '', $limit = 6 ) {
    $args = array(
        'post_type'      => sp_dharma_update_post_type(),
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'no_found_rows'  => true,
    );
    if ( 'programme' === $type ) {
        $args['meta_query'] = array( array( 'key' => '_sp_news_type', 'value' => 'programme' ) );
    } elseif ( 'news' === $type ) {
        $args['meta_query'] = array(
            'relation' => 'OR',
            array( 'key' => '_sp_news_type', 'value' => 'news' ),
            array( 'key' => '_sp_news_type', 'compare' => 'NOT EXISTS' ),
        );
    }
    return new WP_Query( $args );
}
