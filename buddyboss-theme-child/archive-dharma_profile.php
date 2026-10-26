<?php
/**
 * Dharma Acharya and Peeth directory archive.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$peeth  = isset( $_GET['peeth'] ) ? sanitize_title( wp_unslash( $_GET['peeth'] ) ) : '';
$kind_f = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : '';
if ( ! in_array( $kind_f, array( 'peeth', 'acharya', 'sampradaya' ), true ) ) { $kind_f = ''; }
$terms  = get_terms( array( 'taxonomy' => 'dharma_peeth', 'hide_empty' => false ) );
$args   = array(
    'post_type'      => sp_dharma_profile_post_type(),
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => max( 1, (int) get_query_var( 'paged' ) ),
    'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
    'order'          => 'ASC',
);
if ( '' !== $search ) { $args['s'] = $search; }
if ( '' !== $kind_f ) {
    $args['meta_query'] = array( array( 'key' => '_sp_dharma_kind', 'value' => $kind_f ) );
}
if ( '' !== $peeth ) {
    $args['tax_query'] = array( array( 'taxonomy' => 'dharma_peeth', 'field' => 'slug', 'terms' => $peeth ) );
}
// Acharyas who have declared a successor appear only via their Peeth and successor pages.
if ( function_exists( 'sp_dharma_unlisted_ids' ) && ( $hidden = sp_dharma_unlisted_ids() ) ) {
    $args['post__not_in'] = $hidden;
}
$profiles = new WP_Query( $args );
?>
<main id="main" class="sp-page sp-dharma-directory" role="main">
    <header class="sp-dharma-hero">
        <p class="sp-section__eyebrow"><?php esc_html_e( 'Public information directory', 'sampreshan-child' ); ?></p>
        <h1><?php esc_html_e( 'Peeths, Acharyas & Sampradayas', 'sampreshan-child' ); ?></h1>
        <p><?php esc_html_e( 'One place for sourced profiles of the four Amnaya Shankaracharya Peethas, the Kanchi Kamakoti Peetham, their Acharyas and the major sampradayas of Bharat. Choose Anusaran for verified updates that matter to you.', 'sampreshan-child' ); ?></p>
    </header>
    <?php if ( function_exists( 'sp_mission_block' ) && ! is_paged() ) { sp_mission_block( 'full' ); } ?>
    <form class="sp-dharma-filters" method="get" action="<?php echo esc_url( sp_dharma_directory_url() ); ?>" role="search">
        <label>
            <span class="screen-reader-text"><?php esc_html_e( 'Search Acharyas and Peeths', 'sampreshan-child' ); ?></span>
            <input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search a Peeth, Acharya or Sampradaya…', 'sampreshan-child' ); ?>" />
        </label>
        <select name="kind" aria-label="<?php esc_attr_e( 'Profile type', 'sampreshan-child' ); ?>">
            <option value=""><?php esc_html_e( 'All profiles', 'sampreshan-child' ); ?></option>
            <option value="peeth" <?php selected( $kind_f, 'peeth' ); ?>><?php esc_html_e( 'Peeths', 'sampreshan-child' ); ?></option>
            <option value="acharya" <?php selected( $kind_f, 'acharya' ); ?>><?php esc_html_e( 'Acharyas', 'sampreshan-child' ); ?></option>
            <option value="sampradaya" <?php selected( $kind_f, 'sampradaya' ); ?>><?php esc_html_e( 'Sampradayas', 'sampreshan-child' ); ?></option>
        </select>
        <select name="peeth">
            <option value=""><?php esc_html_e( 'All Peeths', 'sampreshan-child' ); ?></option>
            <?php foreach ( $terms as $term ) : ?>
                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $peeth, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn--primary" type="submit"><?php esc_html_e( 'Search', 'sampreshan-child' ); ?></button>
    </form>
    <?php if ( $profiles->have_posts() ) : ?>
        <div class="sp-dharma-profile-grid" data-sp-infinite>
            <?php while ( $profiles->have_posts() ) : $profiles->the_post(); ?>
                <?php
                $profile_id = get_the_ID();
                $peeth_name = (string) get_post_meta( $profile_id, '_sp_dharma_peeth', true );
                $direction  = (string) get_post_meta( $profile_id, '_sp_dharma_direction', true );
                $veda       = (string) get_post_meta( $profile_id, '_sp_dharma_veda', true );
                $kind       = (string) get_post_meta( $profile_id, '_sp_dharma_kind', true );
                $image_url  = sp_dharma_profile_image_url( $profile_id, 'medium_large' );
                ?>
                <article class="sp-dharma-profile-card sp-dharma-profile-card--<?php echo esc_attr( in_array( $kind, array( 'peeth', 'sampradaya' ), true ) ? $kind : 'acharya' ); ?>">
                    <?php $sp_emblem = 'sampradaya' === $kind && false !== strpos( (string) $image_url, '/assets/images/sampradaya/' ) && in_array( sp_dharma_data_all()[ get_post_field( 'post_name', $profile_id ) ]['family'] ?? '', array( 'Sikh', 'Shramana', 'Darshana' ), true ); ?>
                    <?php if ( $sp_emblem ) : ?><a class="sp-dharma-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" /></a><?php elseif ( 'sampradaya' === $kind ) : ?><a class="sp-dharma-card__image sp-dharma-card__image--tilak" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo sp_tilak_markup( get_post_field( 'post_name', $profile_id ), 110, ( sp_dharma_data_all()[ get_post_field( 'post_name', $profile_id ) ]['family'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php elseif ( $image_url ) : ?><a class="sp-dharma-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" /></a><?php endif; ?>
                    <?php if ( $kind ) : ?><p class="sp-dharma-card__kind"><?php echo esc_html( 'sampradaya' === $kind ? __( 'Sampradaya profile', 'sampreshan-child' ) : ( 'peeth' === $kind ? __( 'Peeth profile', 'sampreshan-child' ) : __( 'Acharya profile', 'sampreshan-child' ) ) ); ?></p><?php endif; ?>
                    <p class="sp-dharma-card__peeth"><?php echo esc_html( $peeth_name ); ?></p>
                    <h2><?php sp_icon_e( sp_dharma_icon_name( get_the_ID() ), 'sp-icon--lg' ); ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php echo esc_html( get_the_excerpt() ); ?></p>
                    <?php if ( $direction || $veda ) : ?><dl><div><dt><?php esc_html_e( 'Direction', 'sampreshan-child' ); ?></dt><dd><?php echo esc_html( $direction ); ?></dd></div><div><dt><?php esc_html_e( 'Veda', 'sampreshan-child' ); ?></dt><dd><?php echo esc_html( $veda ); ?></dd></div></dl><?php endif; ?>
                    <div class="sp-dharma-card__footer"><a href="<?php the_permalink(); ?>"><?php sp_know_more( __( 'View', 'sampreshan-child' ), __( 'View profile', 'sampreshan-child' ) ); ?></a><?php sp_dharma_follow_button( $profile_id ); ?></div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <nav class="sp-users__pagination" aria-label="<?php esc_attr_e( 'Profile pages', 'sampreshan-child' ); ?>"><?php echo paginate_links( array( 'total' => $profiles->max_num_pages, 'current' => max( 1, (int) get_query_var( 'paged' ) ) ) ); ?></nav>
    <?php else : ?>
        <div class="sp-dash-empty sp-dash-card"><?php sp_icon_e( 'empty-search', 'sp-icon--3xl' ); ?><h2><?php esc_html_e( 'No profiles found', 'sampreshan-child' ); ?></h2><p><?php esc_html_e( 'Try a different name or select all Peeths.', 'sampreshan-child' ); ?></p></div>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
