<?php
/**
 * Home: Peeths with their Acharyas.
 *
 * @package SampreShan_Child
 */

if ( ! function_exists( 'sp_dharma_data_all' ) ) {
    return;
}

$peeths = array();
foreach ( sp_dharma_data_all() as $slug => $item ) {
    if ( 'peeth' !== $item['kind'] ) {
        continue;
    }
    $post = sp_dharma_profile_by_slug( $slug );
    if ( $post ) {
        $peeths[] = array( $post, $item );
    }
}
if ( empty( $peeths ) ) {
    return;
}
?>
<section class="home-section sp-dharma-home" aria-labelledby="sp-dharma-home-title">
    <div class="sp-fu-sechead">
        <div>
            <p class="sp-fu-eyebrow"><?php esc_html_e( 'Dharma directory', 'sampreshan-child' ); ?></p>
            <h2 id="sp-dharma-home-title" class="sp-fu-sectitle"><?php esc_html_e( 'Peeths & Acharyas', 'sampreshan-child' ); ?></h2>
        </div>
        <a class="sp-fu-link" href="<?php echo esc_url( sp_dharma_directory_url() ); ?>"><?php esc_html_e( 'Explore all profiles', 'sampreshan-child' ); ?> &rarr;</a>
    </div>
    <p class="sp-dharma-home__intro"><?php esc_html_e( 'Read verified, sourced profiles of the Peeths of the Shankara tradition and their Acharyas, and follow them for programme and news updates.', 'sampreshan-child' ); ?></p>
    <div class="sp-dharma-peeth-rail">
        <?php foreach ( $peeths as list( $post, $item ) ) : ?>
            <?php $image = sp_dharma_profile_image_url( $post->ID, 'medium_large' ); ?>
            <article class="sp-dharma-peeth-card">
                <a class="sp-dharma-peeth-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
                    <?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" width="480" height="300" /><?php endif; ?>
                    <?php if ( ! empty( $item['infobox']['Amnaya (direction)'] ) ) : ?><span><?php echo esc_html( $item['infobox']['Amnaya (direction)'] ); ?></span><?php endif; ?>
                </a>
                <div class="sp-dharma-peeth-card__body">
                    <h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
                    <?php if ( ! empty( $item['infobox']['Mahavakya'] ) ) : ?><p class="sp-dharma-card__maha"><?php echo esc_html( $item['infobox']['Mahavakya'] ); ?></p><?php endif; ?>
                    <ul class="sp-dharma-peeth-card__acharyas">
                        <?php foreach ( sp_dharma_peeth_acharya_posts( $item ) as $acharya ) : ?>
                            <?php $thumb = sp_dharma_profile_image_url( $acharya->ID, 'thumbnail' ); ?>
                            <li><a href="<?php echo esc_url( get_permalink( $acharya ) ); ?>"><?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" width="40" height="40" loading="lazy" /><?php endif; ?><span><?php echo esc_html( get_the_title( $acharya ) ); ?></span></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="sp-dharma-card__footer">
                        <a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Read article', 'sampreshan-child' ); ?></a>
                        <?php sp_dharma_follow_button( $post->ID, 'sp-dharma-follow--compact' ); ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
