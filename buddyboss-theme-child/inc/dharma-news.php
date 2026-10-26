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
 * This is now the BACKUP reader for the Dharma Calendar (inc/dharma-calendar.php):
 * only trusted news organisations are accepted, and only headlines that name
 * a Peeth or Acharya.
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

/**
 * Backup source policy: only established news organisations are accepted.
 * Anything else (blogs, aggregators, unknown portals) is skipped entirely.
 */
function sp_dharma_news_trusted_sources() {
    return array( 'the hindu', 'times of india', 'indian express', 'new indian express', 'hindustan times', 'deccan herald', 'deccan chronicle', 'the telegraph', 'the tribune', 'the statesman', 'ndtv', 'india today', 'aaj tak', 'news18', 'zee news', 'abp', 'republic', 'press trust of india', 'pti', 'ani', 'ians', 'doordarshan', 'dd news', 'all india radio', 'prasar bharati', 'dainik jagran', 'jagran', 'amar ujala', 'dainik bhaskar', 'divya bhaskar', 'hindustan', 'live hindustan', 'navbharat times', 'jansatta', 'patrika', 'nai dunia', 'naidunia', 'prabhat khabar', 'lokmat', 'sakal', 'loksatta', 'gujarat samachar', 'sandesh', 'vijay karnataka', 'prajavani', 'udayavani', 'vijayavani', 'kannada prabha', 'the hans india', 'eenadu', 'dinamalar', 'dinamani', 'dina thanthi', 'malayala manorama', 'mathrubhumi', 'sambad', 'dharitri', 'odishatv', 'odisha tv', 'orissapost', 'the pioneer', 'firstpost', 'the print', 'theprint', 'swarajya', 'organiser', 'business standard', 'economic times', 'mint', 'livemint', 'outlook', 'the week', 'free press journal', 'mid-day', 'telangana today', 'bhaskar english', 'etv bharat', 'tv9', 'news nation', 'india tv', 'times now', 'wion', 'bbc', 'reuters' );
}

function sp_dharma_news_is_trusted( $source ) {
    $source = strtolower( trim( $source ) );
    if ( '' === $source ) {
        return false;
    }
    foreach ( sp_dharma_news_trusted_sources() as $name ) {
        if ( $source === $name || 0 === strpos( $source, $name . ' ' ) || false !== strpos( ' ' . $source . ' ', ' ' . $name . ' ' ) ) {
            return true;
        }
    }
    return false;
}

/** A backup headline must name a Peeth or Acharya to be kept at all. */
function sp_dharma_news_is_relevant( $title ) {
    return sp_dharma_news_has_word( $title, array( 'shankaracharya', 'shankracharya', 'sringeri', 'kanchi', 'kamakoti', 'govardhan', 'jyotir', 'jyotish', 'jyothish', 'dwarka', 'sharada peeth', 'sharada peetham', 'peeth', 'peetham', 'avimukt', 'nischalan', 'nishchalan', 'sadanand', 'bharati tirtha', 'bharati teertha', 'vidhushekhara', 'vijayendra', 'chandrasekharendra', 'chandrashekarendra', 'jagadguru' ) );
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
function sp_dharma_news_fetch( $query, $lang = 'en' ) {
    $hindi = 'hi' === $lang;
    $url   = add_query_arg(
        array( 'q' => rawurlencode( $query . ' when:30d' ), 'hl' => $hindi ? 'hi' : 'en-IN', 'gl' => 'IN', 'ceid' => $hindi ? 'IN:hi' : 'IN:en' ),
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
            if ( ! sp_dharma_news_is_trusted( $item['source'] ) || ! sp_dharma_news_is_relevant( $item['title'] ) || $item['time'] < time() - 30 * DAY_IN_SECONDS || sp_dharma_news_seen( $item['guid'], $item['title'] ) ) {
                continue;
            }
            $review    = sp_dharma_news_has_word( $item['title'], sp_dharma_news_review_words() );
            $programme = sp_dharma_news_has_word( $item['title'], sp_dharma_news_programme_words() );
            $date      = wp_date( get_option( 'date_format' ), $item['time'] );
            $content   = '<p>' . esc_html( sprintf( __( 'Update on %1$s, %2$s.', 'sampreshan-child' ), get_the_title( $profile ), $date ) ) . '</p>';
            $id = wp_insert_post(
                array(
                    'post_type'     => sp_dharma_update_post_type(),
                    'post_status'   => $review ? 'pending' : 'publish',
                    'post_title'    => wp_strip_all_tags( $item['title'] ),
                    'post_content'  => $content,
                    'post_excerpt'  => '',
                    'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $item['time'] ),
                    'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $item['time'] ) ),
                    'meta_input'    => array_merge(
                        array(
                            '_sp_dharma_profile_id' => $profile->ID,
                            '_sp_news_guid'         => $item['guid'],
                            '_sp_news_url'          => $item['url'],
                            '_sp_news_source'       => $item['source'],
                            '_sp_news_type'         => $programme ? 'programme' : 'news',
                            '_sp_news_kind'         => $programme ? 'event' : 'news',
                            '_sp_news_origin'       => 'media',
                            '_sp_event_date'        => wp_date( 'Y-m-d', $item['time'] ),
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
