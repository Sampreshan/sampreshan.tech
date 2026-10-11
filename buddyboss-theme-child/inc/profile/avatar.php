<?php
/**
 * Local profile photos instead of Gravatar.
 *
 * Members upload their own photo (stored as a media attachment in user
 * meta). Everyone else gets a bundled default image, so no request is
 * ever sent to gravatar.com.
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'SP_AVATAR_META', 'sp_avatar_id' );
define( 'SP_AVATAR_MAX_BYTES', 2 * MB_IN_BYTES );

function sp_avatar_attachment_id( $user_id ) {
    $id = (int) get_user_meta( (int) $user_id, SP_AVATAR_META, true );
    return ( $id > 0 && wp_attachment_is_image( $id ) ) ? $id : 0;
}

function sp_avatar_default_url() {
    return get_stylesheet_directory_uri() . '/assets/brand/avatar-default.svg';
}

/**
 * Resolve the user behind any value get_avatar() accepts.
 */
function sp_avatar_resolve_user_id( $id_or_email ) {
    if ( is_numeric( $id_or_email ) ) {
        return (int) $id_or_email;
    }
    if ( $id_or_email instanceof WP_User ) {
        return (int) $id_or_email->ID;
    }
    if ( $id_or_email instanceof WP_Post ) {
        return (int) $id_or_email->post_author;
    }
    if ( $id_or_email instanceof WP_Comment ) {
        return (int) $id_or_email->user_id;
    }
    if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        return $user ? (int) $user->ID : 0;
    }
    return 0;
}

function sp_avatar_pre_data( $args, $id_or_email ) {
    $user_id = sp_avatar_resolve_user_id( $id_or_email );
    $att_id  = $user_id ? sp_avatar_attachment_id( $user_id ) : 0;
    $url     = '';
    if ( $att_id ) {
        $size = ( isset( $args['size'] ) && (int) $args['size'] > 150 ) ? 'medium' : 'thumbnail';
        $url  = wp_get_attachment_image_url( $att_id, $size );
    }
    $args['url']          = $url ? $url : sp_avatar_default_url();
    $args['found_avatar'] = (bool) $url;
    return $args;
}
add_filter( 'pre_get_avatar_data', 'sp_avatar_pre_data', 10, 2 );

/**
 * Validate and store an uploaded photo for a user. Returns true or WP_Error.
 *
 * @param int    $user_id User to update.
 * @param string $field   $_FILES key.
 */
function sp_avatar_handle_upload( $user_id, $field = 'sp_avatar' ) {
    if ( empty( $_FILES[ $field ]['name'] ) ) {
        return true;
    }
    $file = $_FILES[ $field ];
    if ( ! empty( $file['error'] ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
        return new WP_Error( 'sp_avatar', __( 'The photo could not be uploaded. Please try again.', 'sampreshan-child' ) );
    }
    if ( (int) $file['size'] > SP_AVATAR_MAX_BYTES ) {
        return new WP_Error( 'sp_avatar', __( 'Photo must be 2 MB or smaller.', 'sampreshan-child' ) );
    }
    $mimes = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'webp'         => 'image/webp',
    );
    $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );
    if ( empty( $check['type'] ) || ! in_array( $check['type'], $mimes, true ) ) {
        return new WP_Error( 'sp_avatar', __( 'Please upload a JPG, PNG or WebP image.', 'sampreshan-child' ) );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $att_id = media_handle_upload( $field, 0, array( 'post_author' => (int) $user_id ), array( 'test_form' => false, 'mimes' => $mimes ) );
    if ( is_wp_error( $att_id ) ) {
        return $att_id;
    }
    sp_avatar_remove( $user_id );
    update_user_meta( $user_id, SP_AVATAR_META, (int) $att_id );
    update_user_meta( $user_id, 'sp_has_avatar', 1 );
    return true;
}

function sp_avatar_remove( $user_id ) {
    $old = (int) get_user_meta( $user_id, SP_AVATAR_META, true );
    if ( $old > 0 ) {
        wp_delete_attachment( $old, true );
    }
    delete_user_meta( $user_id, SP_AVATAR_META );
    delete_user_meta( $user_id, 'sp_has_avatar' );
}

/* ---------------------------------------------------------- wp-admin */

function sp_avatar_admin_form_tag() {
    echo ' enctype="multipart/form-data"';
}
add_action( 'user_edit_form_tag', 'sp_avatar_admin_form_tag' );

// Replaces the "You can change your profile picture on Gravatar" line.
function sp_avatar_admin_description( $description, $user ) {
    if ( ! $user instanceof WP_User || ! current_user_can( 'edit_user', $user->ID ) ) {
        return '';
    }
    ob_start();
    wp_nonce_field( 'sp_admin_avatar_' . $user->ID, 'sp_admin_avatar_nonce' );
    ?>
    <input type="file" name="sp_avatar" accept="image/jpeg,image/png,image/webp" />
    <?php if ( sp_avatar_attachment_id( $user->ID ) ) : ?>
        <label><input type="checkbox" name="sp_avatar_remove" value="1" /> <?php esc_html_e( 'Remove photo', 'sampreshan-child' ); ?></label>
    <?php endif; ?>
    <p class="description"><?php esc_html_e( 'JPG, PNG or WebP, up to 2 MB.', 'sampreshan-child' ); ?></p>
    <?php
    return ob_get_clean();
}
add_filter( 'user_profile_picture_description', 'sp_avatar_admin_description', 10, 2 );

function sp_avatar_admin_save( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) || empty( $_POST['sp_admin_avatar_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sp_admin_avatar_nonce'] ) ), 'sp_admin_avatar_' . $user_id ) ) {
        return;
    }
    if ( ! empty( $_POST['sp_avatar_remove'] ) ) {
        sp_avatar_remove( $user_id );
    }
    sp_avatar_handle_upload( $user_id );
}
add_action( 'personal_options_update', 'sp_avatar_admin_save' );
add_action( 'edit_user_profile_update', 'sp_avatar_admin_save' );
