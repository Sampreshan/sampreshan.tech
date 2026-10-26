<?php
/**
 * Home: live Dharma Calendar for all Peeths and Acharyas (inc/dharma-calendar.php).
 * Continuous ticker of the latest verified entries, a Peeth / Acharya filter
 * and the shared calendar.
 *
 * @package SampreShan_Child
 */

if ( ! function_exists( 'sp_dcal_render' ) ) {
    return;
}
$sp_dcal_all = sp_dcal_items( null, 200 );
if ( ! $sp_dcal_all ) {
    return;
}
?>
<section class="home-section sp-dharma-news" aria-labelledby="sp-dharma-news-title">
    <div class="sp-fu-sechead">
        <div>
            <p class="sp-fu-eyebrow"><?php esc_html_e( 'From the Peeths', 'sampreshan-child' ); ?></p>
            <h2 id="sp-dharma-news-title" class="sp-fu-sectitle"><?php esc_html_e( 'Dharma Calendar: Shankaracharya programmes, videos & news', 'sampreshan-child' ); ?></h2>
        </div>
        <a class="sp-fu-link" href="<?php echo esc_url( sp_dharma_directory_url() ); ?>"><?php esc_html_e( 'All Peeths & Acharyas', 'sampreshan-child' ); ?> &rarr;</a>
    </div>
    <?php
    sp_dcal_render(
        array(
            'items'  => $sp_dcal_all,
            'ticker' => true,
            'filter' => true,
            'all'    => true,
        )
    );
    ?>
</section>
