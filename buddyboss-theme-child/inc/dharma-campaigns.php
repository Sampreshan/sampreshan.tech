<?php
/**
 * Abhiyan (campaigns) led by Acharyas.
 *
 * A campaign is a site-run petition with meta:
 *   sp_campaign            campaign key (e.g. gau-mata-rashtramata)
 *   sp_campaign_champion   dharma profile slug of the Acharya leading it
 *   sp_campaign_keywords   comma separated match words (English + Hindi)
 *   sp_campaign_news       "|" separated Google News queries; prefix hi: for Hindi
 *
 * Updates of the champion (official calendar imports and trusted media) whose
 * title or summary match the keywords are tagged with _sp_campaign_id. Tagged
 * updates are shown ONLY on the campaign page, never in the general calendar,
 * profile update lists or the home page.
 *
 * Manual run: wp eval 'print_r( sp_campaign_news_import() );'
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Defaults for campaigns already on the site (applied once, editable in meta). */
function sp_campaign_seed_meta() {
	return array(
		'gau-mata-rashtramata' => array(
			'sp_campaign_keywords' => "gau mata, gaumata, gau raksha, gauraksha, gau seva, rashtra mata, rashtramata, cow protection, gau dhwaj, \u{0917}\u{094C} \u{092E}\u{093E}\u{0924}\u{093E}, \u{0917}\u{094C}\u{092E}\u{093E}\u{0924}\u{093E}, \u{0917}\u{094B} \u{092E}\u{093E}\u{0924}\u{093E}, \u{0917}\u{094C} \u{0930}\u{0915}\u{094D}\u{0937}\u{093E}, \u{0917}\u{094B}\u{0930}\u{0915}\u{094D}\u{0937}\u{093E}, \u{0917}\u{094C} \u{0938}\u{0947}\u{0935}\u{093E}, \u{0930}\u{093E}\u{0937}\u{094D}\u{091F}\u{094D}\u{0930}\u{092E}\u{093E}\u{0924}\u{093E}, \u{0930}\u{093E}\u{0937}\u{094D}\u{091F}\u{094D}\u{0930} \u{092E}\u{093E}\u{0924}\u{093E}, \u{0917}\u{094C} \u{0927}\u{094D}\u{0935}\u{091C}, \u{0917}\u{094B}\u{092E}\u{093E}\u{0924}\u{093E}",
			'sp_campaign_news'     => "hi:\u{0905}\u{0935}\u{093F}\u{092E}\u{0941}\u{0915}\u{094D}\u{0924}\u{0947}\u{0936}\u{094D}\u{0935}\u{0930}\u{093E}\u{0928}\u{0902}\u{0926} \u{0917}\u{094C} \u{092E}\u{093E}\u{0924}\u{093E}|hi:\u{0936}\u{0902}\u{0915}\u{0930}\u{093E}\u{091A}\u{093E}\u{0930}\u{094D}\u{092F} \u{0917}\u{094C} \u{092E}\u{093E}\u{0924}\u{093E} \u{0930}\u{093E}\u{0937}\u{094D}\u{091F}\u{094D}\u{0930}\u{092E}\u{093E}\u{0924}\u{093E}|hi:\u{0905}\u{0935}\u{093F}\u{092E}\u{0941}\u{0915}\u{094D}\u{0924}\u{0947}\u{0936}\u{094D}\u{0935}\u{0930}\u{093E}\u{0928}\u{0902}\u{0926} \u{0917}\u{094C}|Avimukteshwaranand gau mata|Shankaracharya Gau Mata Rashtra Mata",
		),
	);
}

add_action(
	'init',
	static function () {
		if ( '2026-10-b' === get_option( 'sp_campaign_seed_version' ) ) {
			return;
		}
		foreach ( sp_campaign_seed_meta() as $key => $meta ) {
			$ids = get_posts( array( 'post_type' => 'petition', 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 1, 'meta_key' => 'sp_campaign', 'meta_value' => $key ) );
			foreach ( $ids as $id ) {
				foreach ( $meta as $mk => $mv ) {
					$cur = (string) get_post_meta( $id, $mk, true );
					// Replace only empty values or the first seeded English-only query list.
					if ( '' === $cur || 'Avimukteshwaranand gau mata|Shankaracharya Gau Mata Rashtra Mata' === $cur ) {
						update_post_meta( $id, $mk, $mv );
					}
				}
			}
		}
		update_option( 'sp_campaign_seed_version', '2026-10-b', false );
		sp_campaign_tag_all();
	},
	40
);

/** All published campaigns. */
function sp_campaigns() {
	static $list = null;
	if ( null !== $list ) {
		return $list;
	}
	$list = array();
	$ids  = get_posts( array( 'post_type' => 'petition', 'post_status' => 'publish', 'fields' => 'ids', 'numberposts' => -1, 'meta_key' => 'sp_campaign', 'orderby' => 'date', 'order' => 'DESC' ) );
	foreach ( $ids as $id ) {
		$words = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $id, 'sp_campaign_keywords', true ) ) ) );
		$list[ $id ] = array(
			'id'       => (int) $id,
			'key'      => (string) get_post_meta( $id, 'sp_campaign', true ),
			'champion' => (string) get_post_meta( $id, 'sp_campaign_champion', true ),
			'title'    => get_the_title( $id ),
			'url'      => get_permalink( $id ),
			'summary'  => has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $id ) ) ), 32, '...' ),
			'thumb'    => (string) get_the_post_thumbnail_url( $id, 'medium_large' ),
			'keywords' => $words,
			'news'     => array_filter( array_map( 'trim', explode( '|', (string) get_post_meta( $id, 'sp_campaign_news', true ) ) ) ),
		);
	}
	return $list;
}

/** Campaigns led by the given profile slugs. */
function sp_campaigns_for( $slugs ) {
	$slugs = (array) $slugs;
	return array_values( array_filter( sp_campaigns(), static function ( $c ) use ( $slugs ) {
		return in_array( $c['champion'], $slugs, true );
	} ) );
}

/** Campaign id whose keywords match $text for the given champion profile, or 0. */
function sp_campaign_match( $profile_id, $text ) {
	$slug = get_post_field( 'post_name', (int) $profile_id );
	$text = mb_strtolower( (string) $text );
	foreach ( sp_campaigns_for( array( $slug ) ) as $c ) {
		foreach ( $c['keywords'] as $w ) {
			if ( '' !== $w && false !== mb_strpos( $text, mb_strtolower( $w ) ) ) {
				return $c['id'];
			}
		}
	}
	return 0;
}

/** Tag one update if it belongs to a campaign. */
function sp_campaign_tag_update( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || sp_dharma_update_post_type() !== $post->post_type || get_post_meta( $post_id, '_sp_campaign_id', true ) ) {
		return;
	}
	$pid = (int) get_post_meta( $post_id, '_sp_dharma_profile_id', true );
	if ( ! $pid ) {
		return;
	}
	$cid = sp_campaign_match( $pid, $post->post_title . ' ' . wp_strip_all_tags( $post->post_content ) );
	if ( $cid ) {
		update_post_meta( $post_id, '_sp_campaign_id', $cid );
	}
}
add_action( 'wp_insert_post', static function ( $post_id ) { sp_campaign_tag_update( $post_id ); }, 20 );

/** Re-check every untagged update (after keywords change). */
function sp_campaign_tag_all() {
	$ids = get_posts( array( 'post_type' => sp_dharma_update_post_type(), 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => -1, 'sp_campaign_all' => true ) );
	foreach ( $ids as $id ) {
		sp_campaign_tag_update( $id );
	}
	return count( $ids );
}

/**
 * Keep campaign updates out of every general update listing on the front end.
 * Campaign pages ask for them explicitly with the sp_campaign_feed query arg.
 */
add_action(
	'pre_get_posts',
	static function ( $q ) {
		if ( is_admin() || $q->get( 'sp_campaign_all' ) || $q->get( 'sp_campaign_feed' ) || $q->is_singular() ) {
			return;
		}
		$type = $q->get( 'post_type' );
		if ( sp_dharma_update_post_type() !== $type && ! ( is_array( $type ) && in_array( sp_dharma_update_post_type(), $type, true ) ) ) {
			return;
		}
		$mq   = (array) $q->get( 'meta_query' );
		$mq[] = array( 'key' => '_sp_campaign_id', 'compare' => 'NOT EXISTS' );
		$q->set( 'meta_query', $mq );
	}
);

/** Published updates of one campaign, newest first. */
function sp_campaign_updates( $campaign_id, $limit = 30 ) {
	return get_posts( array(
		'post_type'        => sp_dharma_update_post_type(),
		'post_status'      => 'publish',
		'numberposts'      => absint( $limit ),
		'sp_campaign_feed' => true,
		'meta_key'         => '_sp_campaign_id',
		'meta_value'       => (int) $campaign_id,
		'orderby'          => 'date',
		'order'            => 'DESC',
	) );
}

/** Daily: trusted media reports about each campaign. */
function sp_campaign_news_import( $per_campaign = 4 ) {
	if ( ! function_exists( 'sp_dharma_news_fetch' ) ) {
		return array();
	}
	$report = array();
	foreach ( sp_campaigns() as $c ) {
		$profile = $c['champion'] ? sp_dharma_profile_by_slug( $c['champion'] ) : null;
		if ( ! $profile || ! $c['news'] ) {
			continue;
		}
		$added = 0;
		foreach ( $c['news'] as $query ) {
			$lang = 0 === strpos( $query, 'hi:' ) ? 'hi' : 'en';
			foreach ( sp_dharma_news_fetch( 'hi' === $lang ? substr( $query, 3 ) : $query, $lang ) as $item ) {
				if ( $added >= $per_campaign ) {
					break 2;
				}
				if ( ! sp_dharma_news_is_trusted( $item['source'] ) || $item['time'] < time() - 30 * DAY_IN_SECONDS || sp_dharma_news_seen( $item['guid'], $item['title'] ) || ! sp_campaign_match( $profile->ID, $item['title'] ) ) {
					continue;
				}
				$review = sp_dharma_news_has_word( $item['title'], sp_dharma_news_review_words() );
				$id     = wp_insert_post(
					array(
						'post_type'     => sp_dharma_update_post_type(),
						'post_status'   => $review ? 'pending' : 'publish',
						'post_title'    => wp_strip_all_tags( $item['title'] ),
						'post_content'  => '',
						'post_excerpt'  => '',
						'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $item['time'] ),
						'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $item['time'] ) ),
						'meta_input'    => array(
							'_sp_dharma_profile_id'     => $profile->ID,
							'_sp_campaign_id'           => $c['id'],
							'_sp_news_guid'             => $item['guid'],
							'_sp_news_url'              => $item['url'],
							'_sp_news_source'           => $item['source'],
							'_sp_news_type'             => 'news',
							'_sp_news_kind'             => 'news',
							'_sp_news_origin'           => 'media',
							'_sp_event_date'            => wp_date( 'Y-m-d', $item['time'] ),
							'_sp_dharma_update_notified' => $item['time'] < time() - 3 * DAY_IN_SECONDS ? 'backfill' : '',
						),
					),
					true
				);
				if ( ! is_wp_error( $id ) ) {
					$added++;
				}
			}
		}
		$report[ $c['key'] ] = $added;
	}
	update_option( 'sp_campaign_news_last_run', array( 'time' => time(), 'report' => $report ), false );
	return $report;
}
add_action( 'sp_campaign_news_cron', 'sp_campaign_news_import' );
add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'sp_campaign_news_cron' ) ) {
			wp_schedule_event( time() + 600, 'daily', 'sp_campaign_news_cron' );
		}
	},
	41
);

/* ---------------------------------------------------------------------
 * Front end
 * ------------------------------------------------------------------ */

/** "Campaigns led" cards for an Acharya or Peeth page. */
function sp_campaign_cards( $slugs, $heading ) {
	$list = sp_campaigns_for( $slugs );
	if ( ! $list ) {
		return;
	}
	?>
	<section class="sp-abhiyan" aria-labelledby="sp-abhiyan-h">
		<p class="sp-abhiyan__eyebrow"><?php esc_html_e( 'Abhiyan', 'sampreshan-child' ); ?></p>
		<h2 id="sp-abhiyan-h" class="sp-abhiyan__title"><?php echo esc_html( $heading ); ?></h2>
		<div class="sp-abhiyan__grid">
			<?php foreach ( $list as $c ) : ?>
				<article class="sp-abhiyan__card">
					<?php if ( $c['thumb'] ) : ?><a class="sp-abhiyan__img" href="<?php echo esc_url( $c['url'] ); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( $c['thumb'] ); ?>" alt="" loading="lazy" /></a><?php endif; ?>
					<div class="sp-abhiyan__body">
						<h3><a href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['title'] ); ?></a></h3>
						<?php if ( $c['summary'] ) : ?><p><?php echo esc_html( $c['summary'] ); ?></p><?php endif; ?>
						<a class="sp-abhiyan__more" href="<?php echo esc_url( $c['url'] ); ?>"><?php sp_know_more( __( 'View', 'sampreshan-child' ), sprintf( __( 'View campaign: %s', 'sampreshan-child' ), $c['title'] ) ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/** Campaign page: updates gathered only for this campaign. */
function sp_campaign_updates_section( $campaign_id ) {
	$posts = sp_campaign_updates( $campaign_id );
	?>
	<section class="sp-abhiyan-feed card-3d" aria-labelledby="sp-abhiyan-feed-h">
		<h2 id="sp-abhiyan-feed-h"><?php esc_html_e( 'Campaign updates', 'sampreshan-child' ); ?></h2>
		<p class="sp-abhiyan-feed__note"><?php esc_html_e( 'Gathered every day from the official channels of the Acharya leading this campaign and from trusted media. Each entry links to its original source.', 'sampreshan-child' ); ?></p>
		<?php if ( $posts ) : ?>
			<ul class="sp-abhiyan-feed__list">
				<?php foreach ( $posts as $p ) : ?>
					<?php $src_url = (string) get_post_meta( $p->ID, '_sp_news_url', true ); ?>
					<li>
						<time datetime="<?php echo esc_attr( get_the_date( 'c', $p ) ); ?>"><?php echo esc_html( get_the_date( '', $p ) ); ?></time>
						<a href="<?php echo esc_url( $src_url ? $src_url : get_permalink( $p ) ); ?>" rel="nofollow noopener" target="_blank"><?php echo esc_html( get_the_title( $p ) ); ?></a>
						<?php echo sp_dcal_channel_icon( sp_dcal_channel( $src_url, (string) get_post_meta( $p->ID, '_sp_news_kind', true ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="sp-abhiyan-feed__note"><?php esc_html_e( 'New updates will appear here as soon as they are published.', 'sampreshan-child' ); ?></p>
		<?php endif; ?>
	</section>
	<?php
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_singular( array( 'petition', 'dharma_profile' ) ) ) {
			$file = '/assets/css/abhiyan.css';
			wp_enqueue_style( 'sp-abhiyan', get_stylesheet_directory_uri() . $file, array(), (string) filemtime( get_stylesheet_directory() . $file ) );
		}
	},
	40
);
