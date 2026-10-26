<?php
/**
 * Anusaran email digest.
 *
 * Every update published for a followed Peeth or Acharya is queued per user
 * and sent as one email every six hours, so a busy day is a single message.
 * Each email carries a one-click stop link.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const SP_DHARMA_MAIL_QUEUE = '_sp_dharma_mail_queue';
const SP_DHARMA_MAIL_OFF   = '_sp_dharma_mail_off';

function sp_dharma_mail_enabled( $user_id ) {
    return ! get_user_meta( (int) $user_id, SP_DHARMA_MAIL_OFF, true );
}

/** Queue one update for a user's next digest. */
function sp_dharma_mail_queue( $user_id, $update_id ) {
    if ( ! sp_dharma_mail_enabled( $user_id ) ) {
        return;
    }
    $queue = (array) get_user_meta( $user_id, SP_DHARMA_MAIL_QUEUE, true );
    $queue = array_filter( array_map( 'intval', $queue ) );
    if ( ! in_array( (int) $update_id, $queue, true ) ) {
        $queue[] = (int) $update_id;
        update_user_meta( $user_id, SP_DHARMA_MAIL_QUEUE, array_slice( $queue, -50 ) );
    }
}

function sp_dharma_mail_stop_url( $user_id ) {
    $key = wp_hash( 'sp-dharma-mail-off|' . (int) $user_id );
    return add_query_arg(
        array(
            'sp_anusaran_mail' => 'off',
            'u'                => (int) $user_id,
            'k'                => $key,
        ),
        home_url( '/' )
    );
}

/** Build and send one user's digest. */
function sp_dharma_mail_send_one( $user_id ) {
    $user  = get_userdata( $user_id );
    $queue = array_filter( array_map( 'intval', (array) get_user_meta( $user_id, SP_DHARMA_MAIL_QUEUE, true ) ) );
    delete_user_meta( $user_id, SP_DHARMA_MAIL_QUEUE );
    if ( ! $user || ! $queue || ! is_email( $user->user_email ) || ! sp_dharma_mail_enabled( $user_id ) ) {
        return false;
    }

    $lines = array();
    foreach ( $queue as $id ) {
        $post = get_post( $id );
        if ( ! $post || 'publish' !== $post->post_status ) {
            continue;
        }
        $pid     = (int) get_post_meta( $id, '_sp_dharma_profile_id', true );
        $lines[] = sprintf(
            "- %s%s\n  %s",
            $pid ? get_the_title( $pid ) . ': ' : '',
            wp_specialchars_decode( get_the_title( $post ), ENT_QUOTES ),
            get_permalink( $post )
        );
    }
    if ( ! $lines ) {
        return false;
    }

    $site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    $count   = count( $lines );
    $subject = sprintf(
        /* translators: 1: number of updates, 2: site name. */
        _n( '%1$d new update from the Acharyas you follow | %2$s', '%1$d new updates from the Acharyas you follow | %2$s', $count, 'sampreshan-child' ),
        $count,
        $site
    );
    $body = sprintf(
        "%s,\n\n%s\n\n%s\n\n%s\n%s\n\n%s\n%s",
        $user->display_name,
        __( 'New updates from the Peeths and Acharyas you follow on Sampreshan:', 'sampreshan-child' ),
        implode( "\n\n", $lines ),
        __( 'See all your notifications:', 'sampreshan-child' ),
        function_exists( 'bp_members_get_user_url' ) ? trailingslashit( bp_members_get_user_url( $user_id ) ) . 'notifications/' : home_url( '/dashboard/' ),
        __( 'ShivBodh Research Group', 'sampreshan-child' ),
        sprintf( __( 'Stop these emails: %s', 'sampreshan-child' ), sp_dharma_mail_stop_url( $user_id ) )
    );

    return wp_mail( $user->user_email, $subject, $body );
}

/** Six-hourly: send every pending digest. */
function sp_dharma_mail_send_all() {
    $users = get_users(
        array(
            'meta_key'     => SP_DHARMA_MAIL_QUEUE,
            'meta_compare' => 'EXISTS',
            'fields'       => 'ID',
            'number'       => 500,
        )
    );
    foreach ( $users as $user_id ) {
        sp_dharma_mail_send_one( (int) $user_id );
    }
}
add_action( 'sp_dharma_mail_cron', 'sp_dharma_mail_send_all' );

function sp_dharma_mail_schedule() {
    if ( ! wp_next_scheduled( 'sp_dharma_mail_cron' ) ) {
        wp_schedule_event( time() + 900, 'sp_six_hours', 'sp_dharma_mail_cron' );
    }
}
add_action( 'init', 'sp_dharma_mail_schedule' );

/** One-click stop link from the email. */
function sp_dharma_mail_handle_stop() {
    if ( empty( $_GET['sp_anusaran_mail'] ) || 'off' !== $_GET['sp_anusaran_mail'] ) {
        return;
    }
    $user_id = isset( $_GET['u'] ) ? absint( $_GET['u'] ) : 0;
    $key     = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
    if ( $user_id && hash_equals( wp_hash( 'sp-dharma-mail-off|' . $user_id ), $key ) ) {
        update_user_meta( $user_id, SP_DHARMA_MAIL_OFF, 1 );
        delete_user_meta( $user_id, SP_DHARMA_MAIL_QUEUE );
        wp_die(
            esc_html__( 'Done. You will no longer get Anusaran update emails. In-site notifications continue.', 'sampreshan-child' ),
            esc_html__( 'Emails stopped', 'sampreshan-child' ),
            array( 'response' => 200 )
        );
    }
}
add_action( 'template_redirect', 'sp_dharma_mail_handle_stop', 1 );
