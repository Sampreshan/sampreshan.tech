<?php
/**
 * Sangyani Sanatan quiz: storage, REST API, certificate, dashboard + profile.
 *
 * Rules:
 * - 10 levels, 50 questions each (inc/quiz/bank/NN.txt).
 * - Correct answer: +10. Wrong answer or time-out: -5, and the question
 *   returns at the end of the level with its options reshuffled.
 * - A level is cleared only when every question in it is answered correctly.
 * - Clearing level 10 issues a certificate from ShivBodh Trust.
 * Answers are checked on the server; the browser never receives the key.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const SP_QUIZ_DB_VERSION = '1.0.0';
const SP_QUIZ_POINTS_OK  = 10;
const SP_QUIZ_POINTS_BAD = -5;
const SP_QUIZ_SECONDS    = 30;

/* ------------------------------------------------------------------ */
/* Question bank                                                       */
/* ------------------------------------------------------------------ */

/**
 * Parsed bank: level => array( 'title' => string, 'items' => array( array( q, correct, w1, w2, w3 ) ) ).
 *
 * @return array
 */
function sp_quiz_bank() {
    static $bank = null;
    if ( null !== $bank ) {
        return $bank;
    }
    $bank  = array();
    $files = glob( get_stylesheet_directory() . '/inc/quiz/bank/*.txt' );
    sort( $files );
    foreach ( $files as $file ) {
        $level = (int) basename( $file, '.txt' );
        $lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        $title = trim( preg_replace( '/^title:/', '', (string) array_shift( $lines ) ) );
        $items = array();
        foreach ( $lines as $line ) {
            $parts = array_map( 'trim', explode( '|', $line ) );
            if ( 5 === count( $parts ) ) {
                $items[] = $parts;
            }
        }
        $bank[ $level ] = array( 'title' => $title, 'items' => $items );
    }
    return $bank;
}

function sp_quiz_level_count() {
    return count( sp_quiz_bank() );
}

/* ------------------------------------------------------------------ */
/* Tables                                                              */
/* ------------------------------------------------------------------ */

function sp_quiz_runs_table() {
    global $wpdb;
    return $wpdb->prefix . 'sp_quiz_runs';
}

function sp_quiz_log_table() {
    global $wpdb;
    return $wpdb->prefix . 'sp_quiz_log';
}

add_action( 'init', 'sp_quiz_install' );
function sp_quiz_install() {
    if ( SP_QUIZ_DB_VERSION === get_option( 'sp_quiz_db_version' ) ) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $runs    = sp_quiz_runs_table();
    $log     = sp_quiz_log_table();
    dbDelta( "CREATE TABLE $runs (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        token char(40) NOT NULL,
        user_id bigint(20) unsigned NOT NULL DEFAULT 0,
        player_name varchar(80) NOT NULL DEFAULT '',
        level tinyint(3) unsigned NOT NULL DEFAULT 1,
        levels_cleared tinyint(3) unsigned NOT NULL DEFAULT 0,
        score int(11) NOT NULL DEFAULT 0,
        correct int(11) unsigned NOT NULL DEFAULT 0,
        wrong int(11) unsigned NOT NULL DEFAULT 0,
        status varchar(12) NOT NULL DEFAULT 'active',
        state longtext NULL,
        cert_id varchar(20) NULL,
        started_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        finished_at datetime NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY token (token),
        KEY user_status (user_id, status),
        KEY cert_id (cert_id)
    ) $charset;" );
    dbDelta( "CREATE TABLE $log (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        run_id bigint(20) unsigned NOT NULL,
        user_id bigint(20) unsigned NOT NULL DEFAULT 0,
        level tinyint(3) unsigned NOT NULL,
        correct int(11) unsigned NOT NULL DEFAULT 0,
        wrong int(11) unsigned NOT NULL DEFAULT 0,
        points int(11) NOT NULL DEFAULT 0,
        cleared_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY run_id (run_id),
        KEY user_id (user_id)
    ) $charset;" );
    update_option( 'sp_quiz_db_version', SP_QUIZ_DB_VERSION );
}

/* ------------------------------------------------------------------ */
/* Run helpers                                                         */
/* ------------------------------------------------------------------ */

function sp_quiz_get_run( $token ) {
    global $wpdb;
    if ( ! is_string( $token ) || ! preg_match( '/^[a-f0-9]{40}$/', $token ) ) {
        return null;
    }
    $table = sp_quiz_runs_table();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $run = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE token = %s", $token ) );
    if ( ! $run ) {
        return null;
    }
    // A run started by a signed-in member can only be continued by that member.
    if ( (int) $run->user_id && (int) $run->user_id !== get_current_user_id() ) {
        return null;
    }
    $run->state = json_decode( (string) $run->state, true );
    if ( ! is_array( $run->state ) ) {
        $run->state = array();
    }
    return $run;
}

function sp_quiz_save_run( $run ) {
    global $wpdb;
    $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        sp_quiz_runs_table(),
        array(
            'level'          => (int) $run->level,
            'levels_cleared' => (int) $run->levels_cleared,
            'score'          => (int) $run->score,
            'correct'        => (int) $run->correct,
            'wrong'          => (int) $run->wrong,
            'status'         => $run->status,
            'state'          => wp_json_encode( $run->state ),
            'cert_id'        => $run->cert_id,
            'updated_at'     => current_time( 'mysql', true ),
            'finished_at'    => $run->finished_at,
        ),
        array( 'id' => (int) $run->id )
    );
}

/** Fresh state for a level: every question queued in bank order. */
function sp_quiz_level_state( $level ) {
    $bank  = sp_quiz_bank();
    $count = isset( $bank[ $level ] ) ? count( $bank[ $level ]['items'] ) : 0;
    return array(
        'queue'   => range( 0, max( 0, $count - 1 ) ),
        'current' => null,
        'lc'      => 0,
        'lw'      => 0,
        'lp'      => 0,
    );
}

/** Pick the next question for the run and store its shuffled option order. */
function sp_quiz_issue_question( $run ) {
    $bank  = sp_quiz_bank();
    $items = $bank[ (int) $run->level ]['items'];
    $index = (int) $run->state['queue'][0];
    $order = array( 1, 2, 3, 4 );
    shuffle( $order );
    $run->state['current'] = array(
        'i'      => $index,
        'order'  => $order,
        'issued' => time(),
    );
    $item = $items[ $index ];
    return array(
        'text'    => $item[0],
        'options' => array_map( function ( $o ) use ( $item ) { return $item[ $o ]; }, $order ),
    );
}

/** Public summary of a run for the client. */
function sp_quiz_summary( $run ) {
    $bank  = sp_quiz_bank();
    $level = (int) $run->level;
    $total = isset( $bank[ $level ] ) ? count( $bank[ $level ]['items'] ) : 0;
    $left  = isset( $run->state['queue'] ) ? count( array_unique( $run->state['queue'] ) ) : 0;
    return array(
        'token'         => $run->token,
        'player'        => $run->player_name,
        'level'         => $level,
        'levels'        => sp_quiz_level_count(),
        'levelTitle'    => isset( $bank[ $level ] ) ? $bank[ $level ]['title'] : '',
        'levelTotal'    => $total,
        'levelDone'     => $total - $left,
        'levelsCleared' => (int) $run->levels_cleared,
        'score'         => (int) $run->score,
        'correct'       => (int) $run->correct,
        'wrong'         => (int) $run->wrong,
        'status'        => $run->status,
        'seconds'       => SP_QUIZ_SECONDS,
        'cert'          => $run->cert_id ? sp_quiz_cert_payload( $run ) : null,
    );
}

function sp_quiz_cert_payload( $run ) {
    return array(
        'id'    => $run->cert_id,
        'name'  => $run->player_name,
        'score' => (int) $run->score,
        'date'  => mysql2date( 'j F Y', get_date_from_gmt( (string) $run->finished_at ) ),
        'url'   => add_query_arg( 'certificate', $run->cert_id, sp_quiz_page_url() ),
    );
}

function sp_quiz_page_url() {
    if ( function_exists( 'sp_page_url_by_template' ) ) {
        return sp_page_url_by_template( 'template-quiz.php', 'sanatan-quiz' );
    }
    return home_url( '/sanatan-quiz/' );
}

function sp_quiz_clean_name( $name ) {
    $name = trim( preg_replace( '/\s+/', ' ', sanitize_text_field( (string) $name ) ) );
    return mb_substr( $name, 0, 60 );
}

/* ------------------------------------------------------------------ */
/* REST API                                                            */
/* ------------------------------------------------------------------ */

add_action( 'rest_api_init', 'sp_quiz_routes' );
function sp_quiz_routes() {
    $open = '__return_true';
    register_rest_route( 'sp-quiz/v1', '/start', array(
        'methods'             => 'POST',
        'callback'            => 'sp_quiz_rest_start',
        'permission_callback' => $open,
    ) );
    register_rest_route( 'sp-quiz/v1', '/answer', array(
        'methods'             => 'POST',
        'callback'            => 'sp_quiz_rest_answer',
        'permission_callback' => $open,
    ) );
    register_rest_route( 'sp-quiz/v1', '/restart', array(
        'methods'             => 'POST',
        'callback'            => 'sp_quiz_rest_restart',
        'permission_callback' => $open,
    ) );
    register_rest_route( 'sp-quiz/v1', '/certificate/(?P<id>SBT-[A-Z0-9]{8})', array(
        'methods'             => 'GET',
        'callback'            => 'sp_quiz_rest_certificate',
        'permission_callback' => $open,
    ) );
}

/**
 * Resume a run (by token, or the member's active run) or start a new one.
 */
function sp_quiz_rest_start( WP_REST_Request $req ) {
    global $wpdb;
    $user_id = get_current_user_id();
    $run     = sp_quiz_get_run( (string) $req->get_param( 'token' ) );

    if ( ! $run && $user_id ) {
        $table = sp_quiz_runs_table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $token = $wpdb->get_var( $wpdb->prepare( "SELECT token FROM $table WHERE user_id = %d AND status = 'active' ORDER BY id DESC LIMIT 1", $user_id ) );
        $run   = $token ? sp_quiz_get_run( $token ) : null;
    }
    if ( $run && 'active' !== $run->status ) {
        $run = null;
    }

    if ( ! $run ) {
        $name = sp_quiz_clean_name( $req->get_param( 'name' ) );
        if ( '' === $name ) {
            return new WP_Error( 'sp_quiz_name', 'Please enter your name to begin.', array( 'status' => 400 ) );
        }
        $now = current_time( 'mysql', true );
        $wpdb->insert( sp_quiz_runs_table(), array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            'token'       => bin2hex( random_bytes( 20 ) ),
            'user_id'     => $user_id,
            'player_name' => $name,
            'level'       => 1,
            'state'       => wp_json_encode( sp_quiz_level_state( 1 ) ),
            'started_at'  => $now,
            'updated_at'  => $now,
        ) );
        $table = sp_quiz_runs_table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $run = sp_quiz_get_run( $wpdb->get_var( $wpdb->prepare( "SELECT token FROM $table WHERE id = %d", $wpdb->insert_id ) ) );
        if ( ! $run ) {
            return new WP_Error( 'sp_quiz_db', 'Could not start the quiz. Please try again.', array( 'status' => 500 ) );
        }
    }

    $question = sp_quiz_issue_question( $run );
    sp_quiz_save_run( $run );
    return rest_ensure_response( array( 'run' => sp_quiz_summary( $run ), 'question' => $question ) );
}

/**
 * Check an answer. choice = index of the option as shown, or -1 for time-out.
 */
function sp_quiz_rest_answer( WP_REST_Request $req ) {
    global $wpdb;
    $run = sp_quiz_get_run( (string) $req->get_param( 'token' ) );
    if ( ! $run || 'active' !== $run->status || empty( $run->state['current'] ) ) {
        return new WP_Error( 'sp_quiz_run', 'This quiz session has ended. Please start again.', array( 'status' => 404 ) );
    }
    $choice  = (int) $req->get_param( 'choice' );
    $current = $run->state['current'];
    $order   = $current['order'];
    $right   = (int) array_search( 1, $order, true );
    $ok      = $choice === $right;
    $points  = $ok ? SP_QUIZ_POINTS_OK : SP_QUIZ_POINTS_BAD;

    $run->score += $points;
    $run->state['lp'] += $points;
    $queue = $run->state['queue'];
    array_shift( $queue );
    if ( $ok ) {
        $run->correct++;
        $run->state['lc']++;
    } else {
        $run->wrong++;
        $run->state['lw']++;
        $queue[] = (int) $current['i']; // Ask again at the end of the level.
    }
    $run->state['queue']   = $queue;
    $run->state['current'] = null;

    $cleared = empty( $queue );
    if ( $cleared ) {
        $wpdb->insert( sp_quiz_log_table(), array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            'run_id'     => (int) $run->id,
            'user_id'    => (int) $run->user_id,
            'level'      => (int) $run->level,
            'correct'    => (int) $run->state['lc'],
            'wrong'      => (int) $run->state['lw'],
            'points'     => (int) $run->state['lp'],
            'cleared_at' => current_time( 'mysql', true ),
        ) );
        $run->levels_cleared = (int) $run->level;
        if ( (int) $run->level >= sp_quiz_level_count() ) {
            $run->status      = 'completed';
            $run->finished_at = current_time( 'mysql', true );
            $run->cert_id     = 'SBT-' . strtoupper( wp_generate_password( 8, false, false ) );
        } else {
            $run->level++;
            $run->state = sp_quiz_level_state( (int) $run->level );
        }
    }

    $next = 'active' === $run->status ? sp_quiz_issue_question( $run ) : null;
    sp_quiz_save_run( $run );

    return rest_ensure_response( array(
        'correct'  => $ok,
        'answer'   => $right,
        'points'   => $points,
        'cleared'  => $cleared,
        'run'      => sp_quiz_summary( $run ),
        'question' => $next,
    ) );
}

function sp_quiz_rest_restart( WP_REST_Request $req ) {
    $run = sp_quiz_get_run( (string) $req->get_param( 'token' ) );
    if ( $run && 'active' === $run->status ) {
        $run->status      = 'abandoned';
        $run->finished_at = current_time( 'mysql', true );
        sp_quiz_save_run( $run );
    }
    return rest_ensure_response( array( 'ok' => true ) );
}

function sp_quiz_rest_certificate( WP_REST_Request $req ) {
    global $wpdb;
    $table = sp_quiz_runs_table();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $run = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE cert_id = %s AND status = 'completed'", $req['id'] ) );
    if ( ! $run ) {
        return new WP_Error( 'sp_quiz_cert', 'Certificate not found.', array( 'status' => 404 ) );
    }
    return rest_ensure_response( sp_quiz_cert_payload( $run ) );
}

/* ------------------------------------------------------------------ */
/* Privacy + history                                                   */
/* ------------------------------------------------------------------ */

function sp_quiz_is_public( $user_id ) {
    return '1' === get_user_meta( $user_id, 'sp_quiz_public', true );
}

add_action( 'admin_post_sp_quiz_privacy', 'sp_quiz_handle_privacy' );
function sp_quiz_handle_privacy() {
    if ( ! is_user_logged_in() || ! check_admin_referer( 'sp_quiz_privacy' ) ) {
        wp_die( esc_html__( 'Not allowed.', 'sampreshan-child' ) );
    }
    $public = isset( $_POST['visibility'] ) && 'public' === $_POST['visibility'];
    update_user_meta( get_current_user_id(), 'sp_quiz_public', $public ? '1' : '0' );
    wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url( '/dashboard/' ) );
    exit;
}

/**
 * Stats + history for one member, history ranked by score.
 */
function sp_quiz_user_data( $user_id ) {
    global $wpdb;
    $runs  = sp_quiz_runs_table();
    $log   = sp_quiz_log_table();
    // phpcs:disable WordPress.DB.DirectDatabaseQuery
    $rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $runs WHERE user_id = %d AND (levels_cleared > 0 OR status = 'active' OR correct > 0) ORDER BY score DESC, id DESC LIMIT 50", $user_id ) );
    $steps = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $log WHERE user_id = %d ORDER BY id DESC LIMIT 30", $user_id ) );
    // phpcs:enable
    $stats = array( 'best' => 0, 'certs' => 0, 'levels' => 0, 'plays' => count( $rows ), 'correct' => 0 );
    foreach ( $rows as $r ) {
        $stats['best']     = max( $stats['best'], (int) $r->score );
        $stats['levels']   = max( $stats['levels'], (int) $r->levels_cleared );
        $stats['correct'] += (int) $r->correct;
        if ( $r->cert_id ) {
            $stats['certs']++;
        }
    }
    return array( 'stats' => $stats, 'runs' => $rows, 'log' => $steps );
}

function sp_quiz_status_label( $run ) {
    if ( 'completed' === $run->status ) {
        return __( 'Certified', 'sampreshan-child' );
    }
    if ( 'active' === $run->status ) {
        /* translators: %d: level number */
        return sprintf( __( 'In progress, level %d', 'sampreshan-child' ), (int) $run->level );
    }
    return __( 'Ended', 'sampreshan-child' );
}

/** Stats tiles + ranked history table, shared by dashboard and profile. */
function sp_quiz_render_history( $data, $show_log ) {
    $s = $data['stats'];
    ?>
    <div class="sp-quiz-stats">
        <div><strong><?php echo esc_html( number_format_i18n( $s['best'] ) ); ?></strong><span><?php esc_html_e( 'Best score', 'sampreshan-child' ); ?></span></div>
        <div><strong><?php echo esc_html( $s['levels'] ); ?>/<?php echo esc_html( sp_quiz_level_count() ); ?></strong><span><?php esc_html_e( 'Highest level', 'sampreshan-child' ); ?></span></div>
        <div><strong><?php echo esc_html( $s['certs'] ); ?></strong><span><?php esc_html_e( 'Certificates', 'sampreshan-child' ); ?></span></div>
        <div><strong><?php echo esc_html( number_format_i18n( $s['correct'] ) ); ?></strong><span><?php esc_html_e( 'Correct answers', 'sampreshan-child' ); ?></span></div>
    </div>
    <?php if ( empty( $data['runs'] ) ) : ?>
        <p class="sp-quiz-empty"><?php esc_html_e( 'No quiz played yet.', 'sampreshan-child' ); ?></p>
        <?php return; ?>
    <?php endif; ?>
    <div class="sp-quiz-table-wrap">
        <table class="sp-quiz-table">
            <caption><?php esc_html_e( 'Ranked by score', 'sampreshan-child' ); ?></caption>
            <thead><tr>
                <th scope="col">#</th>
                <th scope="col"><?php esc_html_e( 'Started', 'sampreshan-child' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Levels', 'sampreshan-child' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Score', 'sampreshan-child' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Right / Wrong', 'sampreshan-child' ); ?></th>
                <th scope="col"><?php esc_html_e( 'Status', 'sampreshan-child' ); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $data['runs'] as $rank => $r ) : ?>
                <tr>
                    <td><?php echo esc_html( $rank + 1 ); ?></td>
                    <td><?php echo esc_html( mysql2date( 'j M Y', get_date_from_gmt( $r->started_at ) ) ); ?></td>
                    <td><?php echo esc_html( (int) $r->levels_cleared ); ?>/<?php echo esc_html( sp_quiz_level_count() ); ?></td>
                    <td><strong><?php echo esc_html( number_format_i18n( (int) $r->score ) ); ?></strong></td>
                    <td><?php echo esc_html( (int) $r->correct . ' / ' . (int) $r->wrong ); ?></td>
                    <td>
                        <?php if ( $r->cert_id ) : ?>
                            <a class="sp-text-link" href="<?php echo esc_url( add_query_arg( 'certificate', $r->cert_id, sp_quiz_page_url() ) ); ?>"><?php esc_html_e( 'View certificate', 'sampreshan-child' ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( sp_quiz_status_label( $r ) ); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ( $show_log && ! empty( $data['log'] ) ) : ?>
        <details class="sp-quiz-log">
            <summary><?php esc_html_e( 'Level log', 'sampreshan-child' ); ?></summary>
            <ul>
            <?php
            $bank = sp_quiz_bank();
            foreach ( $data['log'] as $l ) :
                $title = isset( $bank[ (int) $l->level ] ) ? $bank[ (int) $l->level ]['title'] : '';
                ?>
                <li>
                    <span><?php echo esc_html( mysql2date( 'j M Y, g:i a', get_date_from_gmt( $l->cleared_at ) ) ); ?></span>
                    <?php
                    /* translators: 1: level number, 2: level title, 3: points, 4: correct, 5: wrong */
                    echo esc_html( sprintf( __( 'Cleared level %1$d (%2$s): %3$+d points, %4$d right, %5$d wrong', 'sampreshan-child' ), (int) $l->level, $title, (int) $l->points, (int) $l->correct, (int) $l->wrong ) );
                    ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </details>
    <?php endif; ?>
    <?php
}

/** Dashboard card with privacy switch. */
function sp_quiz_dashboard_section() {
    if ( ! is_user_logged_in() ) {
        return;
    }
    $user_id = get_current_user_id();
    $public  = sp_quiz_is_public( $user_id );
    ?>
    <section class="sp-dash-card sp-dash-card--pad sp-quiz-card" aria-labelledby="sp-quiz-dash-title">
        <div class="sp-quiz-card__head">
            <div>
                <p class="sp-dash-eyebrow"><?php esc_html_e( 'Sangyani Sanatan Quiz', 'sampreshan-child' ); ?></p>
                <h2 id="sp-quiz-dash-title" class="sp-dash-card__title"><?php esc_html_e( 'Your quiz history', 'sampreshan-child' ); ?></h2>
            </div>
            <a class="sp-fu-btn sp-fu-btn--primary sp-fu-btn--sm" href="<?php echo esc_url( sp_quiz_page_url() ); ?>"><?php esc_html_e( 'Play the quiz', 'sampreshan-child' ); ?></a>
        </div>
        <form class="sp-quiz-privacy" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="sp_quiz_privacy">
            <?php wp_nonce_field( 'sp_quiz_privacy' ); ?>
            <span><?php esc_html_e( 'Show my quiz results on my public profile:', 'sampreshan-child' ); ?></span>
            <label><input type="radio" name="visibility" value="public" <?php checked( $public ); ?>> <?php esc_html_e( 'Public', 'sampreshan-child' ); ?></label>
            <label><input type="radio" name="visibility" value="private" <?php checked( ! $public ); ?>> <?php esc_html_e( 'Private', 'sampreshan-child' ); ?></label>
            <button type="submit" class="sp-quiz__btn sp-quiz__btn--sm"><?php esc_html_e( 'Save', 'sampreshan-child' ); ?></button>
        </form>
        <?php sp_quiz_render_history( sp_quiz_user_data( $user_id ), true ); ?>
    </section>
    <?php
}

/** Profile card: visible to the owner always, to others only when public. */
function sp_quiz_profile_card( $user_id, $is_me ) {
    if ( ! $user_id || ( ! $is_me && ! sp_quiz_is_public( $user_id ) ) ) {
        return;
    }
    $data = sp_quiz_user_data( $user_id );
    if ( ! $is_me && empty( $data['runs'] ) ) {
        return;
    }
    ?>
    <section class="sp-quiz-card sp-quiz-card--profile" aria-labelledby="sp-quiz-profile-title">
        <h2 id="sp-quiz-profile-title" class="sp-quiz-card__title">
            <?php esc_html_e( 'Sangyani Sanatan Quiz', 'sampreshan-child' ); ?>
            <?php if ( $is_me ) : ?>
                <small><?php echo sp_quiz_is_public( $user_id ) ? esc_html__( 'Public', 'sampreshan-child' ) : esc_html__( 'Private, only you can see this', 'sampreshan-child' ); ?></small>
            <?php endif; ?>
        </h2>
        <?php sp_quiz_render_history( $data, false ); ?>
    </section>
    <?php
}

/* ------------------------------------------------------------------ */
/* Assets                                                              */
/* ------------------------------------------------------------------ */

add_action( 'wp_enqueue_scripts', 'sp_quiz_assets', 30 );
function sp_quiz_assets() {
    $uri = get_stylesheet_directory_uri();
    if ( is_page_template( array( 'template-quiz.php', 'template-dashboard.php', 'template-profile.php' ) ) ) {
        wp_enqueue_style( 'sp-quiz', $uri . '/assets/css/quiz.css', array(), SAMPRESHAN_CHILD_VERSION );
    }
    if ( ! is_page_template( 'template-quiz.php' ) ) {
        return;
    }
    wp_enqueue_script( 'sp-quiz', $uri . '/assets/js/quiz.js', array(), SAMPRESHAN_CHILD_VERSION, true );
    $user = wp_get_current_user();
    wp_localize_script( 'sp-quiz', 'spQuiz', array(
        'api'       => esc_url_raw( rest_url( 'sp-quiz/v1/' ) ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'loggedIn'  => is_user_logged_in(),
        'name'      => $user->exists() ? $user->display_name : '',
        'dashboard' => home_url( '/dashboard/' ),
        'login'     => wp_login_url( sp_quiz_page_url() ),
        'audio'     => array(
            'correct' => $uri . '/assets/audio/quiz-correct.m4a',
            'wrong'   => $uri . '/assets/audio/quiz-wrong.m4a',
        ),
    ) );
}
