<?php
/**
 * Home: latest Peeth / Acharya news and programmes (filled by inc/dharma-news.php).
 *
 * @package SampreShan_Child
 */

if ( ! function_exists( 'sp_dharma_news_latest' ) ) {
    return;
}
$sp_news_cols = array(
    'programme' => array( __( 'Programmes & events', 'sampreshan-child' ), sp_dharma_news_latest( 'programme', 5 ) ),
    'news'      => array( __( 'Latest news', 'sampreshan-child' ), sp_dharma_news_latest( 'news', 5 ) ),
);
if ( ! $sp_news_cols['programme'][1]->have_posts() && ! $sp_news_cols['news'][1]->have_posts() ) {
    return;
}
?>
<section class="home-section sp-dharma-news" aria-labelledby="sp-dharma-news-title">
    <div class="sp-fu-sechead">
        <div>
            <p class="sp-fu-eyebrow"><?php esc_html_e( 'From the Peeths', 'sampreshan-child' ); ?></p>
            <h2 id="sp-dharma-news-title" class="sp-fu-sectitle"><?php esc_html_e( 'Acharya news & programmes', 'sampreshan-child' ); ?></h2>
        </div>
        <a class="sp-fu-link" href="<?php echo esc_url( get_post_type_archive_link( sp_dharma_update_post_type() ) ?: sp_dharma_directory_url() ); ?>"><?php esc_html_e( 'All updates', 'sampreshan-child' ); ?> &rarr;</a>
    </div>
    <div class="sp-dharma-news__cols">
        <?php foreach ( $sp_news_cols as $sp_type => list( $sp_label, $sp_q ) ) : ?>
            <?php if ( ! $sp_q->have_posts() ) { continue; } ?>
            <div class="sp-dharma-news__col sp-dharma-news__col--<?php echo esc_attr( $sp_type ); ?>">
                <h3><?php echo esc_html( $sp_label ); ?></h3>
                <ul>
                    <?php while ( $sp_q->have_posts() ) : $sp_q->the_post(); ?>
                        <?php $sp_pid = sp_dharma_update_profile_id( get_the_ID() ); ?>
                        <li>
                            <a href="<?php the_permalink(); ?>">
                                <span class="sp-dharma-news__meta"><?php echo esc_html( get_the_date() ); ?><?php if ( $sp_pid ) : ?> · <?php echo esc_html( get_the_title( $sp_pid ) ); ?><?php endif; ?></span>
                                <strong><?php the_title(); ?></strong>
                            </a>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>
