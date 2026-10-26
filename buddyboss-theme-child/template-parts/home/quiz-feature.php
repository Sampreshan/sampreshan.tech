<?php
/**
 * Homepage feature: Sangyani Sanatan, the flash-card game.
 *
 * @package SampreShan_Child
 */

if ( ! function_exists( 'sp_quiz_emblem' ) ) { return; }
$quiz_url = sp_quiz_page_url();
?>
<section class="sp-qhome" aria-labelledby="sp-qhome-title">
    <div class="sp-qhome__inner">
        <div class="sp-qhome__art">
            <span class="sp-qhome__orbit"><i></i><i></i><i></i></span>
            <span class="sp-qhome__orbit sp-qhome__orbit--b"><i></i><i></i></span>
            <ol class="sp-qhome__levels" aria-hidden="true">
                <?php for ( $i = 1; $i <= 10; $i++ ) : ?>
                    <li style="--i:<?php echo (int) ( $i - 1 ); ?>"><?php echo (int) $i; ?></li>
                <?php endfor; ?>
            </ol>
            <?php sp_quiz_emblem( 'xl' ); ?>
        </div>
        <div class="sp-qhome__text">
            <p class="sp-qhome__eyebrow"><?php esc_html_e( 'Play and learn', 'sampreshan-child' ); ?></p>
            <h2 id="sp-qhome-title" class="sp-qhome__title"><?php esc_html_e( 'Sangyani', 'sampreshan-child' ); ?> <em><?php esc_html_e( 'Sanatan', 'sampreshan-child' ); ?></em></h2>
            <p class="sp-qhome__lead"><?php esc_html_e( 'A flash-card game on Sanatan Dharma. Ten levels, from simple to scholarly. Answer every card right to clear a level, and finish all ten to earn a certificate from ShivBodh Trust.', 'sampreshan-child' ); ?></p>
            <ul class="sp-qhome__stats">
                <li><b>500</b><span><?php esc_html_e( 'Cards', 'sampreshan-child' ); ?></span></li>
                <li><b>10</b><span><?php esc_html_e( 'Levels', 'sampreshan-child' ); ?></span></li>
                <li><b>30s</b><span><?php esc_html_e( 'Per card', 'sampreshan-child' ); ?></span></li>
                <li><b>1</b><span><?php esc_html_e( 'Certificate', 'sampreshan-child' ); ?></span></li>
            </ul>
            <div class="sp-qhome__cta">
                <a class="sp-qhome__play" href="<?php echo esc_url( $quiz_url ); ?>"><?php esc_html_e( 'Play now', 'sampreshan-child' ); ?></a>
                <span class="sp-qhome__more"><?php esc_html_e( 'Free for everyone, guests included', 'sampreshan-child' ); ?></span>
            </div>
        </div>
    </div>
</section>
