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

<main id="main" class="site-main sp-quiz" role="main">
    <div class="sp-quiz__wrap">

        <!-- Start -->
        <section class="sp-quiz__screen is-active" data-screen="start" aria-labelledby="sp-quiz-title">
            <p class="sp-quiz__eyebrow"><?php esc_html_e( 'ShivBodh Trust presents', 'sampreshan-child' ); ?></p>
            <h1 id="sp-quiz-title" class="sp-quiz__title"><?php esc_html_e( 'Sangyani Sanatan Quiz', 'sampreshan-child' ); ?></h1>
            <p class="sp-quiz__lead"><?php esc_html_e( 'Ten levels, 500 questions, from simple to scholarly. Clear all ten levels to receive the Sangyani Sanatan certificate from ShivBodh Trust.', 'sampreshan-child' ); ?></p>

            <ul class="sp-quiz__rules">
                <li><strong>+<?php echo esc_html( SP_QUIZ_POINTS_OK ); ?></strong> <?php esc_html_e( 'for every correct answer', 'sampreshan-child' ); ?></li>
                <li><strong><?php echo esc_html( SP_QUIZ_POINTS_BAD ); ?></strong> <?php esc_html_e( 'for a wrong answer or when time runs out', 'sampreshan-child' ); ?></li>
                <li><strong><?php echo esc_html( SP_QUIZ_SECONDS ); ?>s</strong> <?php esc_html_e( 'to answer each card', 'sampreshan-child' ); ?></li>
                <li><?php esc_html_e( 'A level is cleared only when every question in it is answered correctly. Missed questions return at the end of the level.', 'sampreshan-child' ); ?></li>
            </ul>

            <ol class="sp-quiz__levels">
                <?php foreach ( $bank as $n => $lvl ) : ?>
                    <li><span><?php echo esc_html( $n ); ?></span><?php echo esc_html( $lvl['title'] ); ?></li>
                <?php endforeach; ?>
            </ol>

            <form class="sp-quiz__start" data-start-form>
                <label for="sp-quiz-name"><?php esc_html_e( 'Your name, as it should appear on the certificate', 'sampreshan-child' ); ?></label>
                <div class="sp-quiz__start-row">
                    <input id="sp-quiz-name" name="name" type="text" maxlength="60" autocomplete="name" required>
                    <button type="submit" class="sp-quiz__btn sp-quiz__btn--primary" data-start-btn><?php esc_html_e( 'Begin', 'sampreshan-child' ); ?></button>
                </div>
                <p class="sp-quiz__note" data-resume-note hidden></p>
                <?php if ( ! is_user_logged_in() ) : ?>
                    <p class="sp-quiz__note">
                        <?php esc_html_e( 'Playing as a guest works, but your history is saved to your dashboard only when you are signed in.', 'sampreshan-child' ); ?>
                        <a href="<?php echo esc_url( wp_login_url( sp_quiz_page_url() ) ); ?>"><?php esc_html_e( 'Sign in', 'sampreshan-child' ); ?></a>
                    </p>
                <?php endif; ?>
                <p class="sp-quiz__error" data-error role="alert" hidden></p>
            </form>
        </section>

        <!-- Play -->
        <section class="sp-quiz__screen" data-screen="play" aria-label="<?php esc_attr_e( 'Quiz', 'sampreshan-child' ); ?>">
            <header class="sp-quiz__hud">
                <div class="sp-quiz__hud-level">
                    <span data-hud-level></span>
                    <strong data-hud-title></strong>
                </div>
                <div class="sp-quiz__hud-right">
                    <span class="sp-quiz__score" aria-live="polite"><span data-hud-score>0</span> <?php esc_html_e( 'pts', 'sampreshan-child' ); ?></span>
                    <button type="button" class="sp-quiz__icon-btn" data-mute aria-pressed="false"><?php esc_html_e( 'Sound on', 'sampreshan-child' ); ?></button>
                </div>
            </header>
            <div class="sp-quiz__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-progress><span></span></div>
            <p class="sp-quiz__progress-text" data-progress-text></p>

            <article class="sp-quiz__card" data-card>
                <div class="sp-quiz__timer" aria-hidden="true"><span data-timer-bar></span></div>
                <p class="sp-quiz__timer-text"><span data-timer>30</span>s</p>
                <h2 class="sp-quiz__question" data-question tabindex="-1"></h2>
                <div class="sp-quiz__options" data-options role="group"></div>
                <p class="sp-quiz__feedback" data-feedback aria-live="assertive"></p>
            </article>

            <div class="sp-quiz__actions">
                <button type="button" class="sp-quiz__btn sp-quiz__btn--primary" data-next hidden><?php esc_html_e( 'Next card', 'sampreshan-child' ); ?></button>
                <button type="button" class="sp-quiz__btn sp-quiz__btn--link" data-quit><?php esc_html_e( 'End this game', 'sampreshan-child' ); ?></button>
            </div>
        </section>

        <!-- Level cleared -->
        <section class="sp-quiz__screen sp-quiz__center" data-screen="level" aria-live="polite">
            <p class="sp-quiz__eyebrow"><?php esc_html_e( 'Level cleared', 'sampreshan-child' ); ?></p>
            <h2 class="sp-quiz__title" data-level-heading></h2>
            <p class="sp-quiz__lead" data-level-summary></p>
            <button type="button" class="sp-quiz__btn sp-quiz__btn--primary" data-continue><?php esc_html_e( 'Continue to next level', 'sampreshan-child' ); ?></button>
        </section>

        <!-- Certificate -->
        <section class="sp-quiz__screen sp-quiz__center" data-screen="cert">
            <p class="sp-quiz__eyebrow" data-cert-eyebrow><?php esc_html_e( 'All ten levels cleared', 'sampreshan-child' ); ?></p>
            <h2 class="sp-quiz__title" data-cert-heading><?php esc_html_e( 'You are a Sangyani Sanatan', 'sampreshan-child' ); ?></h2>
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
