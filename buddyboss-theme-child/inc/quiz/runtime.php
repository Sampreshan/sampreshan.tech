<?php
/** Resumable quiz protocol. Tokens are private bearer credentials for guest progress. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_quiz_error( $code, $message, $status = 400 ) { return new WP_Error( 'sp_quiz_' . $code, $message, array( 'status' => $status ) ); }
function sp_quiz_limit( $scope, $limit, $seconds = 60 ) {
    global $wpdb;
    $scope = $wpdb->prefix . ':' . $scope;
    $identity = get_current_user_id() ?: ( $_SERVER['REMOTE_ADDR'] ?? 'local' );
    $key = 'spq_' . substr( hash_hmac( 'sha256', $scope . ':' . $identity, wp_salt() ), 0, 32 );
    $count = (int) get_transient( $key );
    if ( $count >= $limit ) { return false; }
    set_transient( $key, $count + 1, $seconds );
    return true;
}
/** All run mutations acquire the same database lock, including resume and account claiming. */
function sp_quiz_locked( $req, $callback, $resolve_member = false ) {
    global $wpdb;
    $token = (string) $req->get_param( 'token' );
    $lock = 'spq_' . substr( hash( 'sha256', $wpdb->prefix . $token ), 0, 48 );
    if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $lock ) ) ) {
        return sp_quiz_error( 'busy', 'Your previous action is still saving. Please retry.', 409 );
    }
    $secondary_lock = null;
    try {
        // A member may resume without a browser token. Resolve it while holding the
        // creation lock, then also hold the actual run lock before reading state.
        if ( $resolve_member && '' === $token && get_current_user_id() && ! $req->get_param( 'fresh' ) ) {
            $table = sp_quiz_runs_table();
            $saved = $wpdb->get_var( $wpdb->prepare( "SELECT token FROM $table WHERE user_id=%d AND status='active' ORDER BY id DESC LIMIT 1", get_current_user_id() ) );
            if ( $saved ) {
                $secondary_lock = 'spq_' . substr( hash( 'sha256', $wpdb->prefix . $saved ), 0, 48 );
                if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 5)', $secondary_lock ) ) ) {
                    return sp_quiz_error( 'busy', 'Your previous action is still saving. Please retry.', 409 );
                }
                $req->set_param( 'token', $saved );
            }
        }
        if ( false === $wpdb->query( 'START TRANSACTION' ) ) { return sp_quiz_error( 'storage', 'Please resume and retry shortly.', 503 ); }
        $result = call_user_func( $callback, $req );
        $failed = (bool) $wpdb->last_error;
        if ( is_wp_error( $result ) || $failed ) { $wpdb->query( 'ROLLBACK' ); }
        else { $failed = false === $wpdb->query( 'COMMIT' ); }
        if ( $failed ) { return sp_quiz_error( 'storage', 'Progress could not be saved. Please resume and retry.', 503 ); }
        return $result;
    } catch ( Throwable $error ) {
        $wpdb->query( 'ROLLBACK' );
        return sp_quiz_error( 'storage', 'Progress could not be saved. Please resume and retry.', 503 );
    } finally {
        if ( $secondary_lock ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $secondary_lock ) ); }
        $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
    }
}
function sp_quiz_response( $run ) {
    if ( ! empty( $run->state['receipt'] ) ) {
        $receipt = $run->state['receipt']; $receipt['run'] = sp_quiz_summary( $run );
        return array( 'run' => sp_quiz_summary( $run ), 'feedback' => $receipt, 'question' => $receipt['shown'] );
    }
    return array( 'run' => sp_quiz_summary( $run ), 'question' => 'active' === $run->status ? sp_quiz_issue_question( $run ) : null );
}
function sp_quiz_rest_start( WP_REST_Request $req ) {
    if ( ! sp_quiz_limit( 'start', 30 ) ) { return sp_quiz_error( 'rate', 'Please wait a minute before trying again.', 429 ); }
    return sp_quiz_locked( $req, function( $req ) {
        $token = (string) $req->get_param( 'token' );
        $run = sp_quiz_get_run( $token );
        if ( $token && ! $run ) { return sp_quiz_error( 'missing', 'Saved progress is unavailable. Use Start a new journey to begin again.', 404 ); }
        if ( ! $run && get_current_user_id() && ! $req->get_param( 'fresh' ) ) {
            global $wpdb; $t = sp_quiz_runs_table();
            $saved = $wpdb->get_var( $wpdb->prepare( "SELECT token FROM $t WHERE user_id=%d AND status='active' ORDER BY id DESC LIMIT 1", get_current_user_id() ) );
            if ( $saved ) { $run = sp_quiz_get_run( $saved ); }
        }
        if ( ! $run ) {
            if ( count( sp_quiz_bank() ) !== 10 ) { return sp_quiz_error( 'content', 'The learning collection is being updated. Please return shortly.', 503 ); }
            $result = sp_quiz_legacy_start( $req );
            if ( is_wp_error( $result ) ) { return $result; }
            $data = $result->get_data(); $run = sp_quiz_get_run( $data['run']['token'] );
            shuffle( $run->state['queue'] ); $run->state['current'] = null;
        }
        $response = sp_quiz_response( $run ); sp_quiz_save_run( $run );
        return rest_ensure_response( $response );
    }, true );
}
function sp_quiz_explanation( $level, $index ) {
    $key = sprintf( 'L%02d-Q%03d', $level, $index + 1 );
    $overrides = get_option( 'sp_quiz_content', array() );
    if ( ! empty( $overrides[$key]['explanation'] ) ) { return $overrides[$key]['explanation']; }
    static $notes = null;
    if ( null === $notes ) { $notes = json_decode( (string) file_get_contents( __DIR__ . '/explanations.json' ), true ); }
    return $notes[$key] ?? 'Revisit this card in your revision session to strengthen recall.';
}
function sp_quiz_rest_answer( WP_REST_Request $req ) {
    if ( ! sp_quiz_limit( 'answer', 120 ) ) { return sp_quiz_error( 'rate', 'Please pause briefly and retry.', 429 ); }
    return sp_quiz_locked( $req, function( $req ) {
        global $wpdb;
        $run = sp_quiz_get_run( (string) $req->get_param( 'token' ) );
        if ( ! $run ) { return sp_quiz_error( 'missing', 'Saved progress is unavailable.', 404 ); }
        $id = (string) $req->get_param( 'questionId' );
        if ( $id && isset( $run->state['receipt']['questionId'] ) && hash_equals( $run->state['receipt']['questionId'], $id ) ) {
            return rest_ensure_response( $run->state['receipt'] );
        }
        $current = $run->state['current'] ?? null;
        if ( 'active' !== $run->status || ! $current || ! $id || ! hash_equals( $current['id'] ?? '', $id ) || ! empty( $run->state['receipt'] ) ) {
            return sp_quiz_error( 'stale', 'This card has changed. Resume your saved journey to continue.', 409 );
        }
        $choice = $req->get_param( 'choice' );
        if ( ! is_int( $choice ) || $choice < -1 || $choice > 3 ) { return sp_quiz_error( 'choice', 'Choose one of the four answers.' ); }
        $mode = $run->state['mode'] ?? 'challenge';
        $expired = 'challenge' === $mode && time() - $current['issued'] >= SP_QUIZ_SECONDS;
        $right = (int) array_search( 1, $current['order'], true );
        $ok = ! $expired && $choice === $right;
        $shown = sp_quiz_issue_question( $run );
        $points = $ok ? SP_QUIZ_POINTS_OK : SP_QUIZ_POINTS_BAD;
        $run->score += $points; $run->state['lp'] += $points;
        array_shift( $run->state['queue'] ); $run->state['current'] = null;
        $key = sprintf( 'L%02d-Q%03d', $run->level, $current['i'] + 1 );
        $notebook = $run->state['notebook'] ?? array();
        if ( $ok ) { $run->correct++; $run->state['lc']++; unset( $notebook[$key] ); }
        else { $run->wrong++; $run->state['lw']++; $run->state['queue'][] = $current['i']; $notebook[$key] = true; }
        $attempts = 1 + (int) ( $run->state['attempts'] ?? 0 );
        $cleared = empty( $run->state['queue'] );
        $level = (int) $run->level;
        $explanation = $current['explanation'] ?? sp_quiz_explanation( $level, $current['i'] );
        if ( $cleared && 'practice' !== $mode ) {
            $wpdb->insert( sp_quiz_log_table(), array( 'run_id' => $run->id, 'user_id' => $run->user_id, 'level' => $level, 'correct' => $run->state['lc'], 'wrong' => $run->state['lw'], 'points' => $run->state['lp'], 'cleared_at' => current_time( 'mysql', true ) ) );
            $run->levels_cleared = $level;
            if ( $level === sp_quiz_level_count() ) {
                $run->status = 'completed'; $run->finished_at = current_time( 'mysql', true );
                $run->cert_id = 'SBT-' . strtoupper( bin2hex( random_bytes( 4 ) ) );
            } else { $run->level++; $run->state = sp_quiz_level_state( $run->level ); shuffle( $run->state['queue'] ); }
        } elseif ( $cleared ) { $run->status = 'practised'; $run->finished_at = current_time( 'mysql', true ); }
        $run->state['mode'] = $mode; $run->state['notebook'] = $notebook; $run->state['attempts'] = $attempts;
        $receipt = array( 'questionId' => $id, 'shown' => $shown, 'correct' => $ok, 'expired' => $expired, 'answer' => $right, 'points' => $points, 'explanation' => $explanation, 'cleared' => $cleared, 'checkpoint' => 0 === $attempts % 10, 'run' => sp_quiz_summary( $run ), 'question' => null );
        $run->state['receipt'] = $receipt; sp_quiz_save_run( $run );
        $table = $wpdb->prefix . 'sp_quiz_stats';
        $wpdb->query( $wpdb->prepare( "INSERT INTO $table (card,attempts,wrong) VALUES (%s,1,%d) ON DUPLICATE KEY UPDATE attempts=attempts+1,wrong=wrong+VALUES(wrong)", $key, $ok ? 0 : 1 ) );
        return rest_ensure_response( $receipt );
    } );
}
function sp_quiz_rest_action( WP_REST_Request $req ) {
    if ( ! sp_quiz_limit( 'action', 80 ) ) { return sp_quiz_error( 'rate', 'Please pause briefly and retry.', 429 ); }
    return sp_quiz_locked( $req, function( $req ) {
        global $wpdb;
        $run = sp_quiz_get_run( (string) $req->get_param( 'token' ) );
        if ( ! $run ) { return sp_quiz_error( 'missing', 'Saved progress is unavailable.', 404 ); }
        $action = $req->get_param( 'action' );
        if ( 'next' === $action ) {
            $receipt = $run->state['receipt'] ?? null;
            if ( $receipt && ! hash_equals( $receipt['questionId'], (string) $req->get_param( 'questionId' ) ) ) { return sp_quiz_error( 'stale', 'Resume your saved journey first.', 409 ); }
            unset( $run->state['receipt'] );
        } elseif ( 'claim' === $action ) {
            if ( ! get_current_user_id() || ! wp_verify_nonce( $req->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) { return sp_quiz_error( 'auth', 'Please sign in before saving to your account.', 403 ); }
            $run->user_id = get_current_user_id();
            $wpdb->update( sp_quiz_log_table(), array( 'user_id' => $run->user_id ), array( 'run_id' => $run->id ) );
        } elseif ( 'rename' === $action && 'active' === $run->status ) {
            $name = sp_quiz_clean_name( $req->get_param( 'name' ) );
            if ( '' === $name ) { return sp_quiz_error( 'name', 'Please enter your name.' ); } $run->player_name = $name;
        } elseif ( 'practice' === $action ) {
            $queue = array();
            $keys = array_keys( $run->state['notebook'] ?? array() );
            $practice_level = $run->level;
            if ( $keys && preg_match( '/^L(\d+)-/', $keys[0], $match ) ) { $practice_level = (int) $match[1]; }
            foreach ( array_keys( $run->state['notebook'] ?? array() ) as $key ) {
                if ( preg_match( '/^L(\d+)-Q(\d+)$/', $key, $m ) && (int) $m[1] === (int) $practice_level ) { $queue[] = (int) $m[2] - 1; }
            }
            if ( ! $queue ) { $queue = range( 0, count( sp_quiz_bank()[$practice_level]['items'] ) - 1 ); shuffle( $queue ); }
            $state = sp_quiz_level_state( $practice_level ); $state['queue'] = array_slice( $queue, 0, 10 ); $state['mode'] = 'practice'; $state['practiceTotal'] = count( $state['queue'] );
            $now = current_time( 'mysql', true );
            $wpdb->insert( sp_quiz_runs_table(), array( 'token' => bin2hex( random_bytes( 20 ) ), 'user_id' => $run->user_id, 'player_name' => $run->player_name, 'level' => $practice_level, 'state' => wp_json_encode( $state ), 'started_at' => $now, 'updated_at' => $now ) );
            $run = sp_quiz_get_run( $wpdb->get_var( $wpdb->prepare( 'SELECT token FROM ' . sp_quiz_runs_table() . ' WHERE id=%d', $wpdb->insert_id ) ) );
        } elseif ( 'report' === $action ) {
            $key = sanitize_text_field( $req->get_param( 'key' ) );
            $message = sanitize_textarea_field( $req->get_param( 'message' ) );
            if ( ! preg_match( '/^L(0[1-9]|10)-Q0(0[1-9]|[1-4][0-9]|50)$/', $key ) || strlen( $message ) < 5 || strlen( $message ) > 1500 ) { return sp_quiz_error( 'report', 'Please describe the issue in 5 to 1500 characters.' ); }
            $table = $wpdb->prefix . 'sp_quiz_reports';
            $wpdb->insert( $table, array( 'card' => $key, 'message' => $message, 'reported_at' => current_time( 'mysql', true ) ) );
            return rest_ensure_response( array( 'ok' => true ) );
        } elseif ( 'forget' === $action ) {
            $wpdb->delete( sp_quiz_log_table(), array( 'run_id' => $run->id ) ); $wpdb->delete( sp_quiz_runs_table(), array( 'id' => $run->id ) );
            return rest_ensure_response( array( 'ok' => true ) );
        } else { return sp_quiz_error( 'action', 'This action is unavailable.' ); }
        $response = sp_quiz_response( $run ); sp_quiz_save_run( $run ); return rest_ensure_response( $response );
    } );
}
function sp_quiz_rest_restart( WP_REST_Request $req ) {
    return sp_quiz_locked( $req, 'sp_quiz_legacy_restart' );
}
add_action( 'rest_api_init', function() {
    register_rest_route( 'sp-quiz/v1', '/action', array( 'methods' => 'POST', 'callback' => 'sp_quiz_rest_action', 'permission_callback' => '__return_true' ) );
} );
add_filter( 'rest_post_dispatch', function( $response, $server, $request ) {
    if ( 0 === strpos( $request->get_route(), '/sp-quiz/' ) && method_exists( $response, 'header' ) ) { $response->header( 'Cache-Control', 'private, no-store' ); }
    return $response;
}, 10, 3 );

add_action( 'init', function() { if ( ! wp_next_scheduled( 'sp_quiz_cleanup' ) ) { wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'sp_quiz_cleanup' ); } } );
add_action( 'sp_quiz_cleanup', function() {
    global $wpdb; $runs = sp_quiz_runs_table(); $log = sp_quiz_log_table();
    $wpdb->query( "DELETE l FROM $log l INNER JOIN $runs r ON r.id=l.run_id WHERE r.user_id=0 AND r.cert_id IS NULL AND r.updated_at < UTC_TIMESTAMP() - INTERVAL 90 DAY" );
    $wpdb->query( "DELETE FROM $runs WHERE user_id=0 AND cert_id IS NULL AND updated_at < UTC_TIMESTAMP() - INTERVAL 90 DAY" );
} );
add_filter( 'wp_privacy_personal_data_exporters', function( $exporters ) {
    $exporters['sangyani'] = array( 'exporter_friendly_name' => 'Sangyani Sanatan', 'callback' => 'sp_quiz_privacy_export' ); return $exporters;
} );
function sp_quiz_privacy_export( $email, $page = 1 ) {
    global $wpdb; $user = get_user_by( 'email', $email ); $data = array();
    if ( $user ) {
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT id,player_name,score,correct,wrong,status,cert_id,started_at FROM ' . sp_quiz_runs_table() . ' WHERE user_id=%d ORDER BY id LIMIT 100 OFFSET %d', $user->ID, ( $page - 1 ) * 100 ), ARRAY_A );
        foreach ( $rows as $row ) { $fields = array(); foreach ( $row as $name => $value ) { $fields[] = array( 'name' => $name, 'value' => (string) $value ); } $data[] = array( 'group_id' => 'sangyani', 'group_label' => 'Sangyani learning', 'item_id' => 'quiz-' . $row['id'], 'data' => $fields ); }
    }
    return array( 'data' => $data, 'done' => count( $data ) < 100 );
}
add_filter( 'wp_privacy_personal_data_erasers', function( $erasers ) {
    $erasers['sangyani'] = array( 'eraser_friendly_name' => 'Sangyani Sanatan', 'callback' => function( $email ) {
        global $wpdb; $user = get_user_by( 'email', $email );
        if ( $user ) { $wpdb->delete( sp_quiz_log_table(), array( 'user_id' => $user->ID ) ); $wpdb->delete( sp_quiz_runs_table(), array( 'user_id' => $user->ID ) ); delete_user_meta( $user->ID, 'sp_quiz_public' ); }
        return array( 'items_removed' => (bool) $user, 'items_retained' => false, 'messages' => array(), 'done' => true );
    } ); return $erasers;
} );

function sp_quiz_review( $run ) {
    $result = array(); $bank = sp_quiz_bank();
    foreach ( array_keys( $run->state['notebook'] ?? array() ) as $key ) {
        if ( preg_match( '/^L(\d+)-Q(\d+)$/', $key, $m ) && isset( $bank[(int) $m[1]]['items'][(int) $m[2] - 1] ) ) {
            $item = $bank[(int) $m[1]]['items'][(int) $m[2] - 1];
            $result[] = array( 'id' => $key, 'question' => $item[0], 'answer' => $item[1], 'note' => sp_quiz_explanation( (int) $m[1], (int) $m[2] - 1 ) );
        }
    }
    return $result;
}
add_action( 'template_redirect', function() {
    if ( is_page_template( 'template-quiz.php' ) ) {
        if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
        nocache_headers();
    }
} );
