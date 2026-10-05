<?php
/**
 * Dharma profile: Peeths render as an encyclopaedia (wiki) article,
 * Acharyas as a portfolio. Content comes from inc/dharma-data.php.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
while ( have_posts() ) : the_post();
    $profile_id = get_the_ID();
    $kind       = sp_dharma_profile_kind( $profile_id );
    $data       = sp_dharma_data( $profile_id );
    $image_url  = sp_dharma_profile_image_url( $profile_id, 'large' );
    $updates    = sp_dharma_updates_for_profiles( 'peeth' === $kind ? sp_dharma_same_peeth_ids( $profile_id ) : array( $profile_id ), 10 );
    $peeth_post = ( 'acharya' === $kind && ! empty( $data['peeth'] ) ) ? sp_dharma_profile_by_slug( $data['peeth'] ) : null;
    $peeth_data = $peeth_post ? sp_dharma_data( $peeth_post ) : null;
    $sources    = $data && ! empty( $data['sources'] ) ? $data['sources'] : array();
    $follow_txt = sprintf( _n( '%s member follows this profile', '%s members follow this profile', sp_dharma_follow_count( $profile_id ), 'sampreshan-child' ), number_format_i18n( sp_dharma_follow_count( $profile_id ) ) );
?>
<main id="main" class="sp-page sp-dharma-profile sp-dharma-profile--<?php echo esc_attr( $kind ); ?>" role="main">
    <nav class="sp-dharma-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'sampreshan-child' ); ?>">
        <a href="<?php echo esc_url( sp_dharma_directory_url() ); ?>"><?php esc_html_e( 'Acharyas & Peeths', 'sampreshan-child' ); ?></a>
        <?php if ( $peeth_post ) : ?><span aria-hidden="true">/</span><a href="<?php echo esc_url( get_permalink( $peeth_post ) ); ?>"><?php echo esc_html( get_the_title( $peeth_post ) ); ?></a><?php endif; ?>
    </nav>

<?php if ( 'peeth' === $kind ) : ?>
    <?php $sections = $data ? $data['sections'] : array(); ?>
    <article class="sp-wiki">
        <header class="sp-wiki__head">
            <p class="sp-wiki__kicker"><?php esc_html_e( 'Peeth', 'sampreshan-child' ); ?></p>
            <h1><?php the_title(); ?></h1>
            <?php if ( ! empty( $data['name_hi'] ) ) : ?><p class="sp-wiki__alt" lang="hi"><?php echo esc_html( $data['name_hi'] ); ?></p><?php endif; ?>
            <div class="sp-wiki__actions">
                <?php sp_dharma_follow_button( $profile_id ); ?>
                <span class="sp-dharma-muted" data-sp-follow-count><?php echo esc_html( $follow_txt ); ?></span>
            </div>
        </header>

        <div class="sp-wiki__body">
            <aside class="sp-wiki__infobox" aria-label="<?php esc_attr_e( 'Key facts', 'sampreshan-child' ); ?>">
                <?php if ( $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" width="640" height="640" loading="eager" /><?php endif; ?>
                <?php if ( $data ) : ?>
                    <table>
                        <?php foreach ( $data['infobox'] as $label => $value ) : ?>
                            <tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </aside>

            <div class="sp-wiki__main">
                <p class="sp-wiki__lead"><?php echo esc_html( $data ? $data['summary'] : get_the_excerpt() ); ?></p>

                <?php if ( count( $sections ) > 1 ) : ?>
                    <nav class="sp-wiki__toc" aria-labelledby="sp-wiki-toc-title">
                        <p id="sp-wiki-toc-title"><?php esc_html_e( 'Contents', 'sampreshan-child' ); ?></p>
                        <ol>
                            <?php foreach ( array_keys( $sections ) as $heading ) : ?>
                                <li><a href="#<?php echo esc_attr( sanitize_title( $heading ) ); ?>"><?php echo esc_html( $heading ); ?></a></li>
                            <?php endforeach; ?>
                            <?php if ( $data && sp_dharma_peeth_acharya_posts( $data ) ) : ?><li><a href="#acharyas"><?php esc_html_e( 'Acharyas', 'sampreshan-child' ); ?></a></li><?php endif; ?>
                            <li><a href="#updates"><?php esc_html_e( 'Updates', 'sampreshan-child' ); ?></a></li>
                            <?php if ( $sources ) : ?><li><a href="#references"><?php esc_html_e( 'References', 'sampreshan-child' ); ?></a></li><?php endif; ?>
                        </ol>
                    </nav>
                <?php endif; ?>

                <?php if ( $data ) : ?>
                    <?php foreach ( $sections as $heading => $html ) : ?>
                        <section id="<?php echo esc_attr( sanitize_title( $heading ) ); ?>" class="sp-wiki__section">
                            <h2><?php echo esc_html( $heading ); ?></h2>
                            <?php echo wp_kses_post( $html ); ?>
                        </section>
                    <?php endforeach; ?>

                    <?php $acharyas = sp_dharma_peeth_acharya_posts( $data ); ?>
                    <?php if ( $acharyas ) : ?>
                        <section id="acharyas" class="sp-wiki__section">
                            <h2><?php esc_html_e( 'Acharyas', 'sampreshan-child' ); ?></h2>
                            <ul class="sp-wiki__people">
                                <?php foreach ( $acharyas as $acharya ) : $a = sp_dharma_data( $acharya ); $thumb = sp_dharma_profile_image_url( $acharya->ID, 'thumbnail' ); ?>
                                    <li>
                                        <a href="<?php echo esc_url( get_permalink( $acharya ) ); ?>">
                                            <?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" width="56" height="56" loading="lazy" /><?php endif; ?>
                                            <span><strong><?php echo esc_html( get_the_title( $acharya ) ); ?></strong><?php if ( $a ) : ?><small><?php echo esc_html( $a['role'] ); ?></small><?php endif; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="sp-wiki__section"><?php the_content(); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </article>

<?php else : ?>
    <?php
    $facts    = $data ? $data['facts'] : array();
    $timeline = $data ? $data['timeline'] : array();
    $work     = $data ? $data['work'] : array();
    ?>
    <article class="sp-folio">
        <header class="sp-folio__hero">
            <div class="sp-folio__portrait">
                <?php if ( $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" width="640" height="640" loading="eager" /><?php endif; ?>
            </div>
            <div class="sp-folio__intro">
                <?php if ( $data ) : ?><p class="sp-folio__role"><?php echo esc_html( $data['role'] ); ?></p><?php endif; ?>
                <h1><?php the_title(); ?></h1>
                <?php if ( ! empty( $data['name_hi'] ) ) : ?><p class="sp-folio__alt" lang="hi"><?php echo esc_html( $data['name_hi'] ); ?></p><?php endif; ?>
                <p class="sp-folio__summary"><?php echo esc_html( $data ? $data['summary'] : get_the_excerpt() ); ?></p>
                <div class="sp-folio__actions">
                    <?php sp_dharma_follow_button( $profile_id ); ?>
                    <?php if ( $peeth_post ) : ?><a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $peeth_post ) ); ?>"><?php esc_html_e( 'About the Peeth', 'sampreshan-child' ); ?></a><?php endif; ?>
                </div>
                <p class="sp-folio__followers" data-sp-follow-count><?php echo esc_html( $follow_txt ); ?></p>
            </div>
        </header>

        <?php if ( ! empty( $data['notice'] ) ) : ?>
            <p class="sp-folio__notice" role="note"><?php echo esc_html( $data['notice'] ); ?></p>
        <?php endif; ?>

        <?php if ( $facts ) : ?>
            <section class="sp-folio__facts" aria-label="<?php esc_attr_e( 'Key facts', 'sampreshan-child' ); ?>">
                <?php foreach ( $facts as $label => $value ) : ?>
                    <div><span><?php echo esc_html( $label ); ?></span><strong><?php echo esc_html( $value ); ?></strong></div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <div class="sp-folio__grid">
            <?php if ( $timeline ) : ?>
                <section class="sp-folio__card" aria-labelledby="sp-folio-journey">
                    <h2 id="sp-folio-journey"><?php esc_html_e( 'Journey', 'sampreshan-child' ); ?></h2>
                    <ol class="sp-folio__timeline">
                        <?php foreach ( $timeline as $year => $text ) : ?>
                            <li><span class="sp-folio__year"><?php echo esc_html( $year ); ?></span><p><?php echo esc_html( $text ); ?></p></li>
                        <?php endforeach; ?>
                    </ol>
                </section>
            <?php endif; ?>

            <?php if ( $work || $peeth_data ) : ?>
                <section class="sp-folio__card" aria-labelledby="sp-folio-work">
                    <?php if ( $work ) : ?>
                        <h2 id="sp-folio-work"><?php esc_html_e( 'Work & contributions', 'sampreshan-child' ); ?></h2>
                        <ul class="sp-folio__list"><?php foreach ( $work as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>
                    <?php if ( $peeth_data ) : ?>
                        <h3><?php echo esc_html( get_the_title( $peeth_post ) ); ?></h3>
                        <dl class="sp-folio__peeth">
                            <?php foreach ( array( 'Amnaya (direction)', 'Veda', 'Mahavakya' ) as $key ) : ?>
                                <?php if ( ! empty( $peeth_data['infobox'][ $key ] ) ) : ?><div><dt><?php echo esc_html( $key ); ?></dt><dd><?php echo esc_html( $peeth_data['infobox'][ $key ] ); ?></dd></div><?php endif; ?>
                            <?php endforeach; ?>
                        </dl>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>

        <?php if ( ! $data ) : ?><div class="sp-folio__card"><?php the_content(); ?></div><?php endif; ?>
    </article>
<?php endif; ?>

    <section id="updates" class="sp-dharma-profile__updates" aria-labelledby="sp-dharma-updates-title">
        <p class="sp-section__eyebrow"><?php esc_html_e( 'Verified updates', 'sampreshan-child' ); ?></p>
        <h2 id="sp-dharma-updates-title"><?php esc_html_e( 'Programmes, activities & news', 'sampreshan-child' ); ?></h2>
        <?php if ( $updates->have_posts() ) : ?>
            <div class="sp-dharma-update-list"><?php while ( $updates->have_posts() ) : $updates->the_post(); ?><article><p><?php echo esc_html( get_the_date() ); ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( get_the_excerpt() ); ?></p></article><?php endwhile; wp_reset_postdata(); ?></div>
        <?php else : ?>
            <p class="sp-dharma-muted"><?php esc_html_e( 'Verified programme, activity, and news updates will appear here when published. Follow to be notified.', 'sampreshan-child' ); ?></p>
        <?php endif; ?>
    </section>

    <?php if ( $sources ) : ?>
        <section id="references" class="sp-dharma-refs" aria-labelledby="sp-dharma-refs-title">
            <h2 id="sp-dharma-refs-title"><?php esc_html_e( 'References', 'sampreshan-child' ); ?></h2>
            <ol>
                <?php foreach ( $sources as $label => $url ) : ?>
                    <li><a href="<?php echo esc_url( $url ); ?>" rel="noopener nofollow" target="_blank"><?php echo esc_html( $label ); ?></a></li>
                <?php endforeach; ?>
            </ol>
            <p class="sp-dharma-muted"><?php echo esc_html( sprintf( __( 'Facts checked against official Peetham sources and reputable reporting. Last reviewed: %s. Traditional accounts are labelled as such. Spotted an error? Write to connect@sampreshan.tech.', 'sampreshan-child' ), sp_dharma_reviewed_on() ) ); ?></p>
        </section>
    <?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>
