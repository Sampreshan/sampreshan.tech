<?php
/**
 * Sampreshan Notification Center — on-site petition notifications.
 *
 * Petition events (new I, starter updates, victory, welcome) and Dharma
 * updates from followed Acharyas/Peeths are stored per user and shown in
 * the header bell. Email + dashboard activity continue to work alongside.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Per-user notification store. BuddyBoss Platform is not active on this
 * site, so notifications live in user meta (newest first, capped) and are
 * rendered by the header bell via AJAX.
 */
const SP_NOTIF_META = '_sp_notifications';
const SP_NOTIF_MAX  = 60;

function sp_notif_get_all( $user_id ) {
    $items = get_user_meta( (int) $user_id, SP_NOTIF_META, true );
    return is_array( $items ) ? $items : array();
}

function sp_notif_save_all( $user_id, $items ) {
    update_user_meta( (int) $user_id, SP_NOTIF_META, array_slice( array_values( $items ), 0, SP_NOTIF_MAX ) );
}

function sp_notif_unread_count( $user_id ) {
    $count = 0;
    foreach ( sp_notif_get_all( $user_id ) as $item ) {
        if ( ! empty( $item['new'] ) ) { $count++; }
    }
    return $count;
}

/**
 * Push one notification for a user.
 */
function sp_notify_user( $user_id, $action, $petition_id = 0, $secondary_id = 0, $allow_duplicate = false ) {
    $user_id = (int) $user_id;
    if ( $user_id <= 0 || ! get_userdata( $user_id ) ) { return false; }

    $action = sanitize_key( $action );
    $items  = sp_notif_get_all( $user_id );
    if ( ! $allow_duplicate ) {
        foreach ( $items as $item ) {
            if ( $item['action'] === $action && (int) $item['item'] === (int) $petition_id && (int) $item['secondary'] === (int) $secondary_id ) {
                return false;
            }
        }
    }

    array_unshift( $items, array(
        'id'        => wp_generate_uuid4(),
        'action'    => $action,
        'item'      => (int) $petition_id,
        'secondary' => (int) $secondary_id,
        'time'      => time(),
        'new'       => 1,
    ) );
    sp_notif_save_all( $user_id, $items );
    return true;
}

/**
 * Text + link for one stored notification, via the existing formatters.
 */
function sp_notif_render( $item ) {
    $out = apply_filters( 'bp_notifications_get_notifications_for_user', '', (int) $item['item'], (int) $item['secondary'], 1, 'array', $item['action'], 'sampreshan', 0 );
    return ( is_array( $out ) && ! empty( $out['text'] ) ) ? $out : null;
}

function sp_notif_ajax_list() {
    if ( ! is_user_logged_in() ) { wp_send_json_error( null, 401 ); }
    check_ajax_referer( 'sp_notifications', 'nonce' );

    $user_id = get_current_user_id();
    $list    = array();
    foreach ( array_slice( sp_notif_get_all( $user_id ), 0, 20 ) as $item ) {
        $rendered = sp_notif_render( $item );
        if ( ! $rendered ) { continue; }
        $list[] = array(
            'text' => $rendered['text'],
            'link' => esc_url_raw( $rendered['link'] ),
            'ago'  => sprintf( __( '%s ago', 'sampreshan-child' ), human_time_diff( (int) $item['time'], time() ) ),
            'new'  => ! empty( $item['new'] ),
        );
    }
    wp_send_json_success( array( 'items' => $list, 'unread' => sp_notif_unread_count( $user_id ) ) );
}
add_action( 'wp_ajax_sp_notifications_list', 'sp_notif_ajax_list' );

function sp_notif_ajax_mark_read() {
    if ( ! is_user_logged_in() ) { wp_send_json_error( null, 401 ); }
    check_ajax_referer( 'sp_notifications', 'nonce' );

    $user_id = get_current_user_id();
    $items   = sp_notif_get_all( $user_id );
    foreach ( $items as &$item ) { $item['new'] = 0; }
    unset( $item );
    sp_notif_save_all( $user_id, $items );
    wp_send_json_success( array( 'unread' => 0 ) );
}
add_action( 'wp_ajax_sp_notifications_read', 'sp_notif_ajax_mark_read' );

/**
 * Recent signer IDs for fan-out (excludes nobody; caller filters actor).
 */
function sp_notif_recent_signer_ids( $petition_id, $limit = 15 ) {
    $ids = array();
    if ( ! function_exists( 'sp_petition_get_signatures' ) ) { return $ids; }
    $rows = sp_petition_get_signatures( array( 'petition_id' => (int) $petition_id, 'per_page' => (int) $limit, 'page' => 1 ) );
    if ( ! $rows ) { return $ids; }
    foreach ( $rows as $r ) {
        $uid = isset( $r->user_id ) ? (int) $r->user_id : 0;
        if ( $uid > 0 && ! in_array( $uid, $ids, true ) ) { $ids[] = $uid; }
    }
    return $ids;
}

/**
 * Drop older unread notifications of the same kind so updates never pile up.
 */
function sp_notif_clear_kind( $user_id, $petition_id, $action ) {
    $action = sanitize_key( $action );
    $items  = array_filter( sp_notif_get_all( $user_id ), function ( $item ) use ( $petition_id, $action ) {
        return ! ( $item['action'] === $action && (int) $item['item'] === (int) $petition_id );
    } );
    sp_notif_save_all( $user_id, $items );
}

/**
 * Render our notifications inside BP/BuddyBoss bell + screens.
 */
function sp_notif_format_for_user( $action, $item_id, $secondary_item_id, $total_items, $format, $component_action_name = '', $component_name = '', $id = 0 ) {
    if ( 'sampreshan' !== $component_name ) { return $action; }

    $pid      = (int) $item_id;
    $petition = $pid > 0 ? get_post( $pid ) : null;
    $title    = ( $petition && 'petition' === $petition->post_type ) ? $petition->post_title : __( 'a petition', 'sampreshan-child' );
    $link     = ( $petition && 'petition' === $petition->post_type && function_exists( 'get_permalink' ) ) ? get_permalink( $pid ) : home_url( '/dashboard/' );

    switch ( $component_action_name ) {
        case 'new_signature':
            $who  = $secondary_item_id > 0 ? get_userdata( (int) $secondary_item_id ) : false;
            $name = $who ? $who->display_name : __( 'Someone', 'sampreshan-child' );
            $text = sprintf( __( '%1$s gave an I to your issue "%2$s"', 'sampreshan-child' ), $name, $title );
            break;
        case 'petition_updated':
            $text = sprintf( __( 'New update on "%s"', 'sampreshan-child' ), $title );
            break;
        case 'petition_victory':
            $text = sprintf( __( 'Victory! "%s" won.', 'sampreshan-child' ), $title );
            break;
        case 'welcome':
            $text = __( 'Welcome to Sampreshan — raise your first Issue Sampreshan.', 'sampreshan-child' );
            $link = home_url( '/start-a-petition/' );
            break;
        default:
            return $action;
    }

    if ( 'string' === $format ) {
        return '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>';
    }
    return array( 'text' => $text, 'link' => $link );
}
add_filter( 'bp_notifications_get_notifications_for_user', 'sp_notif_format_for_user', 10, 8 );

/**
 * New I → notify the petition author (never self-notify).
 */
function sp_notif_on_signature( $petition_id, $signer_id, $signature_id = 0 ) {
    $petition = get_post( (int) $petition_id );
    if ( ! $petition || 'petition' !== $petition->post_type ) { return; }
    $author_id = (int) $petition->post_author;
    if ( $author_id <= 0 || $author_id === (int) $signer_id ) { return; }
    sp_notify_user( $author_id, 'new_signature', (int) $petition_id, (int) $signer_id );
}
add_action( 'sampreshan_petition_signed', 'sp_notif_on_signature', 10, 3 );

/**
 * Starter update → notify recent signers (not the author writing it).
 */
function sp_notif_on_update( $petition_id, $actor_id = 0 ) {
    $petition = get_post( (int) $petition_id );
    if ( ! $petition || 'petition' !== $petition->post_type ) { return; }
    foreach ( sp_notif_recent_signer_ids( (int) $petition_id, 15 ) as $uid ) {
        if ( $uid === (int) $actor_id || $uid === (int) $petition->post_author ) { continue; }
        sp_notif_clear_kind( $uid, (int) $petition_id, 'petition_updated' );
        sp_notify_user( $uid, 'petition_updated', (int) $petition_id, (int) $actor_id, true );
    }
}
add_action( 'sampreshan_petition_updated', 'sp_notif_on_update', 10, 2 );

/**
 * Victory → notify author + recent signers.
 */
function sp_notif_on_victory( $petition_id, $actor_id = 0 ) {
    $petition = get_post( (int) $petition_id );
    if ( ! $petition || 'petition' !== $petition->post_type ) { return; }
    $targets = sp_notif_recent_signer_ids( (int) $petition_id, 15 );
    $targets[] = (int) $petition->post_author;
    foreach ( array_unique( $targets ) as $uid ) {
        if ( $uid <= 0 || $uid === (int) $actor_id ) { continue; }
        sp_notify_user( $uid, 'petition_victory', (int) $petition_id, (int) $actor_id );
    }
}
add_action( 'sampreshan_petition_victory', 'sp_notif_on_victory', 10, 2 );

/**
 * Welcome notification on registration (email, OTP, or standard signup).
 */
function sp_notif_on_register( $user_id ) {
    sp_notify_user( (int) $user_id, 'welcome', 0, 0 );
}
add_action( 'user_register', 'sp_notif_on_register', 20, 1 );
