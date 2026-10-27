<?php
/** Restricted editorial review and certificate management. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'admin_menu', function() { add_management_page( 'Sangyani Sanatan', 'Sangyani Sanatan', 'manage_options', 'sp-sangyani', 'sp_quiz_admin' ); } );
function sp_quiz_admin() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    global $wpdb;
    $content = get_option( 'sp_quiz_content', array() ); $reports = $wpdb->get_results( 'SELECT id,card AS `key`,message,reported_at AS date,status FROM ' . $wpdb->prefix . 'sp_quiz_reports ORDER BY id DESC LIMIT 200', ARRAY_A );
    $message = '';
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        check_admin_referer( 'sp_quiz_editor' );
        $action = sanitize_key( $_POST['editor_action'] ?? '' );
        if ( 'content' === $action ) {
            $key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
            $fields = array_map( 'sanitize_text_field', explode( '|', wp_unslash( $_POST['fields'] ?? '' ) ) );
            $fields = array_map( 'trim', $fields );
            $explanation = sanitize_textarea_field( wp_unslash( $_POST['explanation'] ?? '' ) );
            $provenance = sanitize_textarea_field( wp_unslash( $_POST['provenance'] ?? '' ) );
            if ( preg_match( '/^L(0[1-9]|10)-Q0(0[1-9]|[1-4][0-9]|50)$/', $key ) && count( $fields ) === 5 && count( array_unique( array_slice( $fields, 1 ) ) ) === 4 && ! in_array( '', $fields, true ) && strlen( $explanation ) >= 20 && strlen( $provenance ) >= 10 && ! preg_match( '/[\x{0900}-\x{097F}]/u', implode( ' ', $fields ) . $explanation ) && strlen( implode( ' ', $fields ) ) <= 1600 && strlen( $explanation ) <= 4000 && strlen( $provenance ) <= 4000 ) {
                $entry = array( 'fields' => $fields, 'explanation' => $explanation, 'provenance' => $provenance, 'reviewer' => get_current_user_id(), 'date' => current_time( 'mysql', true ) );
                if ( isset( $_POST['publish'] ) ) {
                    $content[$key] = $entry; update_option( 'sp_quiz_content', $content, false ); delete_option( 'sp_quiz_draft_' . $key ); $message = 'Reviewed correction published. New cards will use it; active cards retain their issued answer.';
                } else { update_option( 'sp_quiz_draft_' . $key, $entry, false ); $message = 'Draft saved. It is not visible to learners.'; }
            } else { $message = 'Not saved: supply a valid card, four distinct options, an explanation and internal evidence.'; }
        } elseif ( 'resolve' === $action ) {
            $index = absint( $_POST['report'] ?? 0 ); $wpdb->update( $wpdb->prefix . 'sp_quiz_reports', array( 'status' => 'resolved' ), array( 'id' => $index ) ); $message = 'Report resolved. Refresh to see the updated queue.';
        } elseif ( 'certificate' === $action ) {
            $id = sanitize_text_field( wp_unslash( $_POST['certificate'] ?? '' ) );
            $name = sp_quiz_clean_name( wp_unslash( $_POST['certificate_name'] ?? '' ) );
            if ( preg_match( '/^SBT-[A-Z0-9]{8}$/', $id ) ) {
                $changes = isset( $_POST['revoke'] ) ? array( 'status' => 'revoked' ) : array( 'player_name' => $name );
                if ( isset( $_POST['revoke'] ) || '' !== $name ) {
                    $token = $wpdb->get_var( $wpdb->prepare( 'SELECT token FROM ' . sp_quiz_runs_table() . ' WHERE cert_id=%s AND status=%s', $id, 'completed' ) );
                    $request = new WP_REST_Request( 'POST' ); $request->set_param( 'token', (string) $token );
                    $result = sp_quiz_locked( $request, function() use ( $wpdb, $changes, $id ) { return $wpdb->update( sp_quiz_runs_table(), $changes, array( 'cert_id' => $id, 'status' => 'completed' ) ); } );
                    $count = ! is_wp_error( $result ) && $result;
                    $message = $count ? 'Certificate updated.' : 'No matching certificate changed.';
                }
            }
        }
    }
    $key = sanitize_text_field( wp_unslash( $_GET['card'] ?? 'L01-Q001' ) );
    if ( ! preg_match( '/^L(0[1-9]|10)-Q0(0[1-9]|[1-4][0-9]|50)$/', $key, $m ) ) { $key = 'L01-Q001'; }
    preg_match( '/^L(\d+)-Q(\d+)$/', $key, $m ); $level = (int) $m[1]; $index = (int) $m[2] - 1;
    $draft = get_option( 'sp_quiz_draft_' . $key ); $entry = $draft ?: ( $content[$key] ?? array() );
    $fields = $entry['fields'] ?? sp_quiz_bank()[$level]['items'][$index];
    $stats = $wpdb->get_results( 'SELECT card,attempts,wrong FROM ' . $wpdb->prefix . 'sp_quiz_stats ORDER BY wrong DESC,card', ARRAY_A );
    ?>
    <div class="wrap"><h1>Sangyani Sanatan</h1>
    <?php if ( $message ) { echo '<div class="notice notice-info"><p>' . esc_html( $message ) . '</p></div>'; } ?>
    <p>Completion certificates recognise finishing this learning course. They are not an invigilated examination or accreditation of scriptural expertise.</p>
    <h2>Content review</h2><p>Base cards and learning notes are editorial material awaiting independent scholarly verification. Record specific textual or tradition evidence here; internal provenance is never displayed publicly. Keep one unambiguous answer and qualify differing traditions.</p>
    <form method="get"><input type="hidden" name="page" value="sp-sangyani"><label>Card ID <input name="card" value="<?php echo esc_attr( $key ); ?>" pattern="L[0-9]{2}-Q[0-9]{3}" required></label> <button class="button">Load card</button></form>
    <form method="post"><?php wp_nonce_field( 'sp_quiz_editor' ); ?><input type="hidden" name="editor_action" value="content"><input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
    <p><label>Question | correct option | other option | other option | other option<br><textarea name="fields" rows="4" class="large-text" required><?php echo esc_textarea( implode( ' | ', $fields ) ); ?></textarea></label></p>
    <p><label>Explanation for learners<br><textarea name="explanation" rows="4" class="large-text" required><?php echo esc_textarea( $entry['explanation'] ?? sp_quiz_explanation( $level, $index ) ); ?></textarea></label></p>
    <p><label>Internal evidence: work, section, wording and tradition qualifications<br><textarea name="provenance" rows="4" class="large-text" required><?php echo esc_textarea( $entry['provenance'] ?? '' ); ?></textarea></label></p>
    <button class="button">Save draft</button> <button class="button button-primary" name="publish" value="1">Confirm review and publish</button></form>
    <h2>Question reports</h2><table class="widefat striped"><thead><tr><th>Card</th><th>Issue</th><th>Date</th><th>Review</th></tr></thead><tbody>
    <?php foreach ( $reports as $i => $report ) : ?><tr><td><a href="<?php echo esc_url( add_query_arg( 'card', $report['key'] ) ); ?>"><?php echo esc_html( $report['key'] ); ?></a></td><td><?php echo esc_html( $report['message'] ); ?></td><td><?php echo esc_html( $report['date'] ); ?></td><td><?php if ( 'open' === $report['status'] ) : ?><form method="post"><?php wp_nonce_field( 'sp_quiz_editor' ); ?><input type="hidden" name="editor_action" value="resolve"><input type="hidden" name="report" value="<?php echo esc_attr( $report['id'] ); ?>"><button class="button">Resolve</button></form><?php else : ?>Resolved<?php endif; ?></td></tr><?php endforeach; ?></tbody></table>
    <h2>Card performance</h2><p>Recorded since this release. Counts include retry attempts.</p><table class="widefat striped"><thead><tr><th>Card</th><th>Attempts</th><th>Incorrect or timeout</th></tr></thead><tbody><?php foreach ( $stats as $id => $counts ) { echo '<tr><td>' . esc_html( $counts['card'] ) . '</td><td>' . (int) $counts['attempts'] . '</td><td>' . (int) $counts['wrong'] . '</td></tr>'; } ?></tbody></table>
    <h2>Certificate correction or revocation</h2><form method="post"><?php wp_nonce_field( 'sp_quiz_editor' ); ?><input type="hidden" name="editor_action" value="certificate"><p><label>Certificate ID <input name="certificate" required pattern="SBT-[A-Z0-9]{8}"></label></p><p><label>Correct recipient name <input name="certificate_name" maxlength="60"></label></p><button class="button">Correct name</button> <button class="button" name="revoke" value="1">Revoke certificate</button></form>
    <h2>Privacy and retention</h2><p>Profiles are private by default. Guest progress without a certificate is removed after 90 days of inactivity. Export and erase member data through WordPress Tools. Erasing learning data also removes its verification records. Certificate links publicly display the name, completion date and score to anyone with the link.</p>
    </div><?php
}
