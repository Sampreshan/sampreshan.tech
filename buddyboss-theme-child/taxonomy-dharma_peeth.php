<?php
/**
 * Peeth archive.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
$term          = get_queried_object();
$peeth_profile = sp_dharma_peeth_profile_for_term( $term->term_id );
$peeth_updates = $peeth_profile ? sp_dharma_updates_for_profiles( sp_dharma_same_peeth_ids( $peeth_profile ), 10 ) : null;
?>
<main id="main" class="sp-page sp-dharma-directory" role="main">
    <header class="sp-dharma-hero">
        <p class="sp-section__eyebrow"><?php esc_html_e( 'Peeth tradition', 'sampreshan-child' ); ?></p>
        <h1><?php single_term_title(); ?></h1>
        <p><?php esc_html_e( 'Profiles and verified updates connected with this Peeth tradition.', 'sampreshan-child' ); ?></p>
        <div class="sp-dharma-profile__actions">
            <?php if ( $peeth_profile ) { sp_dharma_follow_button( $peeth_profile, 'btn btn--primary' ); } ?>
            <a class="btn btn--ghost" href="<?php echo esc_url( sp_dharma_directory_url() ); ?>"><?php esc_html_e( 'All Acharyas & Peeths', 'sampreshan-child' ); ?></a>
        </div>
        <?php if ( $peeth_profile ) : ?>
            <p class="sp-dharma-muted"><?php esc_html_e( 'Follow this Peeth to get updates from the Peeth and its Acharya in your notifications and feed.', 'sampreshan-child' ); ?></p>
        <?php endif; ?>
    </header>
    <?php if ( have_posts() ) : ?>
        <div class="sp-dharma-profile-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="sp-dharma-profile-card">
                    <p class="sp-dharma-card__peeth"><?php echo esc_html( $term->name ); ?></p>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php echo esc_html( get_the_excerpt() ); ?></p>
                    <div class="sp-dharma-card__footer"><a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Open profile', 'sampreshan-child' ); ?></a><?php sp_dharma_follow_button( get_the_ID() ); ?></div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <div class="sp-dash-empty sp-dash-card"><h2><?php esc_html_e( 'No profiles are published yet', 'sampreshan-child' ); ?></h2></div>
    <?php endif; ?>
    <?php if ( $peeth_updates && $peeth_updates->have_posts() ) : ?>
        <section class="sp-dharma-profile__updates" aria-labelledby="sp-peeth-updates-title">
            <h2 id="sp-peeth-updates-title"><?php esc_html_e( 'Programmes, activities & news', 'sampreshan-child' ); ?></h2>
            <div class="sp-dharma-update-list">
                <?php while ( $peeth_updates->have_posts() ) : $peeth_updates->the_post(); ?>
                    <article><p><?php echo esc_html( get_the_date() ); ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3></article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
