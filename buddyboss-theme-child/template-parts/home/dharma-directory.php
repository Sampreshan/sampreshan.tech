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
    <p class="sp-dharma-home__intro"><?php esc_html_e( 'One place for sourced profiles of the four Amnaya Shankaracharya Peethas, the Kanchi Kamakoti Peetham, their Acharyas and the major sampradayas of Bharat. Follow them for programme and news updates.', 'sampreshan-child' ); ?></p>
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
                        <?php foreach ( sp_dharma_listed_acharya_posts( $item ) as $acharya ) : ?>
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

    <?php
    $acharyas = array();
    foreach ( $peeths as list( $post, $item ) ) {
        foreach ( sp_dharma_listed_acharya_posts( $item ) as $acharya ) {
            $acharyas[] = array( $acharya, $post );
        }
    }
    ?>
    <?php if ( $acharyas ) : ?>
        <div class="sp-fu-sechead sp-dharma-acharya-row__head">
            <div>
                <p class="sp-fu-eyebrow"><?php esc_html_e( 'Acharya profiles', 'sampreshan-child' ); ?></p>
                <h3 class="sp-fu-sectitle"><?php esc_html_e( 'Acharyas of the Peeths', 'sampreshan-child' ); ?></h3>
            </div>
        </div>
        <div class="sp-dharma-acharya-row">
            <?php foreach ( $acharyas as list( $acharya, $peeth ) ) : ?>
                <?php $img = sp_dharma_profile_image_url( $acharya->ID, 'medium' ); $a = sp_dharma_data( $acharya ); ?>
                <article class="sp-dharma-acharya-card">
                    <a class="sp-dharma-acharya-card__media" href="<?php echo esc_url( get_permalink( $acharya ) ); ?>" tabindex="-1" aria-hidden="true">
                        <?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" width="160" height="160" loading="lazy" /><?php else : ?><span aria-hidden="true">ॐ</span><?php endif; ?>
                    </a>
                    <h4><a href="<?php echo esc_url( get_permalink( $acharya ) ); ?>"><?php echo esc_html( get_the_title( $acharya ) ); ?></a></h4>
                    <?php if ( ! empty( $a['role'] ) ) : ?><p class="sp-dharma-acharya-card__role"><?php echo esc_html( $a['role'] ); ?></p><?php endif; ?>
                    <div class="sp-dharma-acharya-card__cta">
                        <a class="sp-dharma-acharya-card__profile" href="<?php echo esc_url( get_permalink( $acharya ) ); ?>"><?php esc_html_e( 'View profile', 'sampreshan-child' ); ?></a>
                        <?php sp_dharma_follow_button( $acharya->ID, 'sp-dharma-follow--compact' ); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    $sampradayas = array();
    foreach ( sp_dharma_data_all() as $slug => $item ) {
        if ( 'sampradaya' === $item['kind'] && ( $post = sp_dharma_profile_by_slug( $slug ) ) ) {
            $sampradayas[] = array( $post, $item );
        }
    }
    ?>
    <?php if ( $sampradayas ) : ?>
        <div class="sp-fu-sechead sp-dharma-acharya-row__head">
            <div>
                <p class="sp-fu-eyebrow"><?php esc_html_e( 'Sampradayas', 'sampreshan-child' ); ?></p>
                <h3 class="sp-fu-sectitle"><?php esc_html_e( 'Sampradayas of Bharat', 'sampreshan-child' ); ?></h3>
            </div>
            <a class="sp-fu-link" href="<?php echo esc_url( add_query_arg( 'kind', 'sampradaya', sp_dharma_directory_url() ) ); ?>"><?php esc_html_e( 'All sampradayas', 'sampreshan-child' ); ?> &rarr;</a>
        </div>
        <div class="sp-dharma-acharya-row sp-dharma-sampradaya-row">
            <?php foreach ( $sampradayas as list( $post, $item ) ) : ?>
                <article class="sp-dharma-acharya-card sp-dharma-sampradaya-card">
                    <a class="sp-dharma-acharya-card__media sp-dharma-acharya-card__media--tilak" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
                        <?php echo sp_tilak_markup( $post->post_name, 84, $item['family'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </a>
                    <p class="sp-dharma-sampradaya-card__family"><?php echo esc_html( sp_sampradaya_family_label( $item['family'] ) ); ?></p>
                    <h4><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h4>
                    <?php if ( ! empty( $item['name_hi'] ) ) : ?><p class="sp-dharma-acharya-card__role" lang="hi"><?php echo esc_html( $item['name_hi'] ); ?></p><?php endif; ?>
                    <div class="sp-dharma-acharya-card__cta">
                        <a class="sp-dharma-acharya-card__profile" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Read more', 'sampreshan-child' ); ?></a>
                        <?php sp_dharma_follow_button( $post->ID, 'sp-dharma-follow--compact' ); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
