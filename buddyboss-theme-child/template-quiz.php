<?php
/**
 * Template Name: Sangyani Sanatan Quiz
 *
 * Flash-card quiz in ten levels. All screens are rendered here and
 * switched by assets/js/quiz.js; answers are checked by the REST API.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
$bank = sp_quiz_bank();
?>

<main id="main" class="site-main sp-quiz" role="main" data-mood="idle">

    <!-- Animated stage (decorative) -->
    <div class="sp-quiz__stage" aria-hidden="true">
        <div class="sp-quiz__rays"></div>
        <svg class="sp-quiz__mandala" viewBox="-200 -200 400 400">
            <g class="sp-quiz__ring sp-quiz__ring--a">
                <?php for ( $i = 0; $i < 24; $i++ ) : ?>
                    <ellipse cx="0" cy="-150" rx="16" ry="46" transform="rotate(<?php echo (int) ( $i * 15 ); ?>)"/>
                <?php endfor; ?>
            </g>
            <g class="sp-quiz__ring sp-quiz__ring--b">
                <?php for ( $i = 0; $i < 16; $i++ ) : ?>
                    <path d="M0 -112 Q 22 -80 0 -48 Q -22 -80 0 -112Z" transform="rotate(<?php echo (int) ( $i * 22.5 ); ?>)"/>
                <?php endfor; ?>
                <circle r="118" />
                <circle r="44" />
            </g>
        </svg>
        <div class="sp-quiz__orb"></div>
        <canvas class="sp-quiz__fx" data-fx></canvas>
    </div>

    <div class="sp-quiz__countdown" data-countdown aria-hidden="true"><span></span></div>
    <div class="sp-quiz__pop-layer" data-pops aria-hidden="true"><canvas class="sp-quiz__fx" data-fx-top></canvas></div>

    <div class="sp-quiz__wrap">

        <!-- Start -->
        <section class="sp-quiz__screen is-active" data-screen="start" aria-labelledby="sp-quiz-title">
            <div class="sp-quiz__hero">
                <p class="sp-quiz__eyebrow"><?php esc_html_e( 'ShivBodh Trust presents', 'sampreshan-child' ); ?></p>
                <h1 id="sp-quiz-title" class="sp-quiz__title"><span><?php esc_html_e( 'Sangyani', 'sampreshan-child' ); ?></span> <em><?php esc_html_e( 'Sanatan', 'sampreshan-child' ); ?></em></h1>
                <p class="sp-quiz__lead"><?php esc_html_e( 'A ten-level journey of 500 flash cards, from simple to scholarly. Clear every level to earn the Sangyani Sanatan certificate.', 'sampreshan-child' ); ?></p>
            </div>

            <ul class="sp-quiz__rules">
                <li><b>+<?php echo esc_html( SP_QUIZ_POINTS_OK ); ?></b><span><?php esc_html_e( 'Correct', 'sampreshan-child' ); ?></span></li>
                <li><b><?php echo esc_html( SP_QUIZ_POINTS_BAD ); ?></b><span><?php esc_html_e( 'Wrong or timeout', 'sampreshan-child' ); ?></span></li>
                <li><b><?php echo esc_html( SP_QUIZ_SECONDS ); ?>s</b><span><?php esc_html_e( 'Per card', 'sampreshan-child' ); ?></span></li>
                <li><b>10</b><span><?php esc_html_e( 'Levels to clear', 'sampreshan-child' ); ?></span></li>
            </ul>

            <form class="sp-quiz__start" data-start-form>
                <label for="sp-quiz-name"><?php esc_html_e( 'Your name, as it should appear on the certificate', 'sampreshan-child' ); ?></label>
                <input id="sp-quiz-name" name="name" type="text" maxlength="60" autocomplete="name" required>
                <button type="submit" class="sp-quiz__btn sp-quiz__btn--primary sp-quiz__btn--play" data-start-btn><?php esc_html_e( 'Play', 'sampreshan-child' ); ?></button>
                <p class="sp-quiz__note" data-resume-note hidden></p>
                <?php if ( ! is_user_logged_in() ) : ?>
                    <p class="sp-quiz__note">
                        <?php esc_html_e( 'Guests can play. Sign in to keep your history on your dashboard.', 'sampreshan-child' ); ?>
                        <a href="<?php echo esc_url( wp_login_url( sp_quiz_page_url() ) ); ?>"><?php esc_html_e( 'Sign in', 'sampreshan-child' ); ?></a>
                    </p>
                <?php endif; ?>
                <p class="sp-quiz__error" data-error role="alert" hidden></p>
            </form>

            <p class="sp-quiz__section-label"><?php esc_html_e( 'The journey', 'sampreshan-child' ); ?></p>
            <ol class="sp-quiz__map" data-map>
                <?php foreach ( $bank as $n => $lvl ) : ?>
                    <li data-level="<?php echo esc_attr( $n ); ?>"><span class="sp-quiz__node"><?php echo esc_html( $n ); ?></span><span class="sp-quiz__node-name"><?php echo esc_html( $lvl['title'] ); ?></span></li>
                <?php endforeach; ?>
            </ol>
            <p class="sp-quiz__note sp-quiz__keys-hint"><?php esc_html_e( 'Tip: press keys 1 to 4 to answer and Enter for the next card.', 'sampreshan-child' ); ?></p>
        </section>

        <!-- Play -->
        <section class="sp-quiz__screen" data-screen="play" aria-label="<?php esc_attr_e( 'Quiz', 'sampreshan-child' ); ?>">
            <header class="sp-quiz__hud">
                <div class="sp-quiz__badge"><small><?php esc_html_e( 'Level', 'sampreshan-child' ); ?></small><b data-hud-num>1</b></div>
                <div class="sp-quiz__hud-mid">
                    <strong data-hud-title></strong>
                    <span data-hud-level></span>
                    <div class="sp-quiz__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-progress><span></span></div>
                    <p class="sp-quiz__progress-text" data-progress-text></p>
                </div>
                <div class="sp-quiz__hud-right">
                    <div class="sp-quiz__score" aria-live="polite"><b data-hud-score>0</b><small><?php esc_html_e( 'points', 'sampreshan-child' ); ?></small></div>
                    <div class="sp-quiz__streak" data-streak hidden><b data-streak-num>0</b><small><?php esc_html_e( 'streak', 'sampreshan-child' ); ?></small></div>
                    <button type="button" class="sp-quiz__icon-btn" data-mute aria-pressed="false"><?php esc_html_e( 'Sound on', 'sampreshan-child' ); ?></button>
                </div>
            </header>

            <div class="sp-quiz__deck">
                <div class="sp-quiz__ghost sp-quiz__ghost--2" aria-hidden="true"></div>
                <div class="sp-quiz__ghost sp-quiz__ghost--1" aria-hidden="true"></div>
                <article class="sp-quiz__card" data-card>
                    <div class="sp-quiz__timer" aria-hidden="true">
                        <svg viewBox="0 0 44 44"><circle class="sp-quiz__timer-bg" cx="22" cy="22" r="19"/><circle class="sp-quiz__timer-arc" cx="22" cy="22" r="19" data-timer-bar/></svg>
                        <span data-timer>30</span>
                    </div>
                    <p class="sp-quiz__card-tag" data-card-tag></p>
                    <h2 class="sp-quiz__question" data-question tabindex="-1"></h2>
                    <div class="sp-quiz__options" data-options role="group"></div>
                    <p class="sp-quiz__feedback" data-feedback aria-live="assertive"></p>
                </article>
            </div>

            <div class="sp-quiz__actions">
                <button type="button" class="sp-quiz__btn sp-quiz__btn--primary" data-next hidden><?php esc_html_e( 'Next card', 'sampreshan-child' ); ?></button>
                <button type="button" class="sp-quiz__btn sp-quiz__btn--link" data-quit><?php esc_html_e( 'End this game', 'sampreshan-child' ); ?></button>
            </div>
        </section>

        <!-- Level cleared -->
        <section class="sp-quiz__screen sp-quiz__center" data-screen="level" aria-live="polite">
            <div class="sp-quiz__burst" aria-hidden="true"></div>
            <p class="sp-quiz__eyebrow"><?php esc_html_e( 'Level cleared', 'sampreshan-child' ); ?></p>
            <h2 class="sp-quiz__title sp-quiz__title--win" data-level-heading></h2>
            <div class="sp-quiz__stars" aria-hidden="true"><i></i><i></i><i></i></div>
            <p class="sp-quiz__lead" data-level-summary></p>
            <ol class="sp-quiz__map sp-quiz__map--mini" data-map-mini aria-hidden="true"></ol>
            <button type="button" class="sp-quiz__btn sp-quiz__btn--primary sp-quiz__btn--play" data-continue><?php esc_html_e( 'Next level', 'sampreshan-child' ); ?></button>
        </section>

        <!-- Certificate -->
        <section class="sp-quiz__screen sp-quiz__center" data-screen="cert">
            <div class="sp-quiz__burst" aria-hidden="true"></div>
            <p class="sp-quiz__eyebrow" data-cert-eyebrow><?php esc_html_e( 'All ten levels cleared', 'sampreshan-child' ); ?></p>
            <h2 class="sp-quiz__title sp-quiz__title--win" data-cert-heading><?php esc_html_e( 'You are a Sangyani Sanatan', 'sampreshan-child' ); ?></h2>
            <canvas class="sp-quiz__cert" data-cert-canvas width="1600" height="1131" role="img" aria-label="<?php esc_attr_e( 'Certificate', 'sampreshan-child' ); ?>"></canvas>
            <div class="sp-quiz__actions">
                <button type="button" class="sp-quiz__btn sp-quiz__btn--primary" data-download-png><?php esc_html_e( 'Download certificate (PNG)', 'sampreshan-child' ); ?></button>
                <button type="button" class="sp-quiz__btn" data-download-pdf><?php esc_html_e( 'Print or save as PDF', 'sampreshan-child' ); ?></button>
                <a class="sp-quiz__btn sp-quiz__btn--link" data-cert-dashboard href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'See your history', 'sampreshan-child' ); ?></a>
            </div>
            <p class="sp-quiz__note" data-cert-verify></p>
        </section>

    </div>
</main>

<?php get_footer(); ?>
