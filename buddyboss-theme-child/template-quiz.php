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
        <div class="sp-quiz__floaters">
            <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                <span class="sp-quiz__floater sp-quiz__floater--<?php echo (int) $i; ?>"><?php sp_quiz_emblem( 'sm' ); ?></span>
            <?php endfor; ?>
        </div>
        <canvas class="sp-quiz__fx" data-fx></canvas>
    </div>

    <div class="sp-quiz__countdown" data-countdown aria-hidden="true"><span></span></div>
    <div class="sp-quiz__pop-layer" data-pops aria-hidden="true"><canvas class="sp-quiz__fx" data-fx-top></canvas></div>

    <div class="sp-quiz__wrap">

        <!-- Start -->
        <section class="sp-quiz__screen is-active" data-screen="start" aria-labelledby="sp-quiz-title">
            <div class="sp-quiz__hero">
                <div class="sp-quiz__hero-emblem"><?php sp_quiz_emblem( 'lg' ); ?></div>
                <p class="sp-quiz__eyebrow"><?php esc_html_e( 'ShivBodh Trust presents', 'sampreshan-child' ); ?></p>
                <h1 id="sp-quiz-title" class="sp-quiz__title"><span><?php esc_html_e( 'Sangyani', 'sampreshan-child' ); ?></span> <em><?php esc_html_e( 'Sanatan', 'sampreshan-child' ); ?></em></h1>
                <p class="sp-quiz__lead"><?php esc_html_e( 'Discover Sanatan traditions through 500 learning cards. Learn at your own pace, revisit what you miss, and celebrate each step.', 'sampreshan-child' ); ?></p>
            </div>

            <form class="sp-quiz__start" data-start-form>
                <label for="sp-quiz-name"><?php esc_html_e( 'Your name, as it should appear on the certificate', 'sampreshan-child' ); ?></label>
                <input id="sp-quiz-name" name="name" type="text" maxlength="60" autocomplete="name" required>
                <p class="sp-quiz__note" data-mode-note>Self-paced learning, with no timer.</p>
                <details class="sp-quiz__preferences"><summary>Learning preferences: pace, sound and motion</summary>
                <fieldset class="sp-quiz__modes"><legend>Choose your journey</legend>
                    <label><input type="radio" name="mode" value="learn" checked> Learn <small>No timer. Read, reflect and revise.</small></label>
                    <label><input type="radio" name="mode" value="challenge"> Challenge <small>30 seconds per card. Time continues when you leave.</small></label>
                </fieldset>
                <label class="sp-quiz__setting"><input type="checkbox" data-calm> Gentle motion</label>
                <label class="sp-quiz__setting"><input type="checkbox" data-sound> Enable sound</label>
                </details>
                <button type="submit" class="sp-quiz__btn sp-quiz__btn--primary sp-quiz__btn--play" data-start-btn><?php esc_html_e( 'Begin or resume', 'sampreshan-child' ); ?></button>
                <p class="sp-quiz__note" data-resume-note hidden></p>
                <?php if ( ! is_user_logged_in() ) : ?>
                    <p class="sp-quiz__note">
                        <?php esc_html_e( 'Guests can play. Sign in to keep your history on your dashboard.', 'sampreshan-child' ); ?>
                        <a href="<?php echo esc_url( wp_login_url( sp_quiz_page_url() ) ); ?>"><?php esc_html_e( 'Sign in', 'sampreshan-child' ); ?></a>
                    </p>
                <?php endif; ?>
                <button type="button" class="sp-quiz__btn sp-quiz__btn--link" data-new hidden>Start a new journey</button><button type="button" class="sp-quiz__btn sp-quiz__btn--link" data-forget hidden>Delete this saved journey</button>
                <p class="sp-quiz__note">Your name will appear on your completion certificate. Guest progress stays on this browser. Inactive guest progress without a certificate is removed after 90 days.</p>
                <p class="sp-quiz__error" data-error role="alert" hidden></p>
            </form>

            <ul class="sp-quiz__rules">
                <li><b>+<?php echo esc_html( SP_QUIZ_POINTS_OK ); ?></b><span><?php esc_html_e( 'Correct', 'sampreshan-child' ); ?></span></li>
                <li><b><?php echo esc_html( SP_QUIZ_POINTS_BAD ); ?></b><span><?php esc_html_e( 'Incorrect / timeout', 'sampreshan-child' ); ?></span></li>
                <li><b><?php echo esc_html( SP_QUIZ_SECONDS ); ?>s</b><span><?php esc_html_e( 'Challenge only', 'sampreshan-child' ); ?></span></li>
                <li><b>10</b><span><?php esc_html_e( 'Levels to clear', 'sampreshan-child' ); ?></span></li>
            </ul>

            <p class="sp-quiz__note">Complete all 10 topics to receive a course completion certificate from ShivBodh Trust.</p>
            <?php sp_research_credit(); ?>
            <p class="sp-quiz__section-label"><?php esc_html_e( 'The journey', 'sampreshan-child' ); ?></p>
            <ol class="sp-quiz__map" data-map>
                <?php foreach ( $bank as $n => $lvl ) : ?>
                    <li data-level="<?php echo esc_attr( $n ); ?>"><span class="sp-quiz__node" data-n="<?php echo esc_attr( $n ); ?>"><?php echo esc_html( $n ); ?></span><span class="sp-quiz__node-name"><?php echo esc_html( $lvl['title'] ); ?></span></li>
                <?php endforeach; ?>
            </ol>
            <p class="sp-quiz__note sp-quiz__keys-hint"><?php esc_html_e( 'Tip: press keys 1 to 4 to answer and Enter for the next card.', 'sampreshan-child' ); ?></p>
        </section>

        <!-- Play -->
        <section class="sp-quiz__screen" data-screen="play" aria-label="<?php esc_attr_e( 'Quiz', 'sampreshan-child' ); ?>">
            <header class="sp-quiz__hud">
                <div class="sp-quiz__badge"><span class="sp-quiz__badge-gem"><?php sp_quiz_emblem( 'sm' ); ?></span><small><?php esc_html_e( 'Level', 'sampreshan-child' ); ?></small><b data-hud-num>1</b></div>
                <div class="sp-quiz__hud-mid">
                    <strong data-hud-title></strong>
                    <span data-hud-level></span>
                    <div class="sp-quiz__progress" role="progressbar" aria-label="Topic progress" aria-valuemin="0" aria-valuemax="100" data-progress><span></span></div>
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
                    <span class="sp-quiz__card-glint" aria-hidden="true"></span>
                    <div class="sp-quiz__timer" aria-hidden="true">
                        <svg viewBox="0 0 44 44"><circle class="sp-quiz__timer-bg" cx="22" cy="22" r="19"/><circle class="sp-quiz__timer-arc" cx="22" cy="22" r="19" data-timer-bar/></svg>
                        <span data-timer>30</span>
                    </div>
                    <p class="sp-quiz__card-tag" data-card-tag></p>
                    <h2 class="sp-quiz__question" id="sp-current-question" data-question tabindex="-1"></h2>
                    <div class="sp-quiz__options" data-options role="group" aria-labelledby="sp-current-question"></div>
                    <p class="sp-quiz__note" data-timer-warning role="status"></p>
                    <p class="sp-quiz__feedback" data-feedback aria-live="assertive"></p>
                <div class="sp-quiz__explanation" data-explanation hidden></div>
                    <div class="sp-quiz__report" data-report-wrap hidden><details><summary>Report a question</summary><form data-report-form><label for="sp-report">What needs checking?</label><textarea id="sp-report" minlength="5" maxlength="1500" required></textarea><button class="sp-quiz__btn" type="submit">Send for review</button><p role="status" data-report-status></p></form></details></div>
                </article>
            </div>

            <div class="sp-quiz__actions">
                <button type="button" class="sp-quiz__btn sp-quiz__btn--primary" data-next hidden><?php esc_html_e( 'Next card', 'sampreshan-child' ); ?></button>
                <button type="button" class="sp-quiz__btn sp-quiz__btn--link" data-quit><?php esc_html_e( 'Save and exit', 'sampreshan-child' ); ?></button>
            </div>
        <p class="sp-quiz__note" data-checkpoint role="status"></p>
            <div class="sp-quiz__actions"><button type="button" class="sp-quiz__btn" data-practice>10-card revision</button><button type="button" class="sp-quiz__btn" data-claim hidden>Save to my account</button><button type="button" class="sp-quiz__btn" data-rename>Change certificate name</button></div>
            <details class="sp-quiz__explanation"><summary>My revision notebook</summary><p data-notebook></p><div data-revision-list></div></details>
            <p class="sp-quiz__error" data-play-error role="alert" hidden></p>
            <button type="button" class="sp-quiz__btn" data-recover hidden>Resume saved progress</button>
        </section>

        <!-- Level cleared -->
        <section class="sp-quiz__screen sp-quiz__center" data-screen="level" aria-live="polite">
            <div class="sp-quiz__burst" aria-hidden="true"></div>
            <div class="sp-quiz__win-emblem"><?php sp_quiz_emblem( 'lg' ); ?></div>
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
            <div class="sp-quiz__win-emblem"><?php sp_quiz_emblem( 'lg' ); ?></div>
            <p class="sp-quiz__eyebrow" data-cert-eyebrow><?php esc_html_e( 'All ten levels cleared', 'sampreshan-child' ); ?></p>
            <h2 class="sp-quiz__title sp-quiz__title--win" data-cert-heading><?php esc_html_e( 'Your learning milestone', 'sampreshan-child' ); ?></h2>
            <canvas class="sp-quiz__cert" data-cert-canvas width="1600" height="1131" role="img" aria-label="<?php esc_attr_e( 'Certificate', 'sampreshan-child' ); ?>"></canvas>
            <div data-cert-text class="sp-quiz__explanation"></div>
            <div class="sp-quiz__actions">
                <button type="button" class="sp-quiz__btn sp-quiz__btn--primary" data-download-png><?php esc_html_e( 'Download certificate (PNG)', 'sampreshan-child' ); ?></button>
                <button type="button" class="sp-quiz__btn" data-download-pdf><?php esc_html_e( 'Print or save as PDF', 'sampreshan-child' ); ?></button>
                <a class="sp-quiz__btn sp-quiz__btn--link" data-cert-dashboard href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'See your history', 'sampreshan-child' ); ?></a>
            </div>
            <p class="sp-quiz__note">A course completion record recognising study and revision of all 500 cards.</p>
            <p class="sp-quiz__note">Anyone with a certificate link can see its recipient name, completion date and score.</p>
            <button type="button" class="sp-quiz__btn" data-share>Share certificate link</button>
            <p class="sp-quiz__note" data-cert-verify></p>
        </section>

    </div>
</main>

<?php get_footer(); ?>
