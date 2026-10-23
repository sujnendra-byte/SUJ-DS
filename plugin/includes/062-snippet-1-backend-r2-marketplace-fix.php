<?php
/**
 * Code Snippets export — Snippet 1 backend (R2 Marketplace Fix)
 *
 * Snippet ID : 748
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-062
 * Modified   : 2026-09-09 02:46:51
 * Load order : 062 of 144
 */
/**
 * DS Creator Portal — Snippet 1: Backend  (PATCHED — captures every
 * Upload Product wizard field, not just title/description/file)
 * WHERE TO ADD: Paste this into Code Snippets (no leading <?php) AFTER
 * removing/replacing your existing Snippet 1. Everything is identical to
 * your original — the only additions are the "PATCHED" blocks.
 *
 * WHAT THIS DOES:
 *  - Registers custom post type: ds_creator_upload
 *  - Registers REST API endpoints under /wp-json/ds/v1/creator/
 *  - Handles file uploads to WordPress media library
 *  - Manages post meta for all file fields
 *
 * PATCHED — now also saves/returns:
 *  - Content Type (step 1 of the wizard)
 *  - Technical Details: slides, aspect ratio, editable, PPT version, fonts,
 *    icons, images, animations, transitions (step 5)
 *  - Rights confirmation, AI-content disclosure, licensing checkboxes
 *    (step 6)
 * None of this touches your existing fields — it's purely additive.
 */

// ─────────────────────────────────────────────
// 1. REGISTER CUSTOM POST TYPE
// ─────────────────────────────────────────────
add_action( 'init', 'ds_register_creator_upload_cpt' );
function ds_register_creator_upload_cpt() {
    register_post_type( 'ds_creator_upload', [
        'labels'              => [
            'name'          => 'Creator Uploads',
            'singular_name' => 'Creator Upload',
            'menu_name'     => 'Creator Uploads',
            'add_new_item'  => 'Add New Upload',
            'edit_item'     => 'Edit Upload',
            'view_item'     => 'View Upload',
            'search_items'  => 'Search Uploads',
        ],
        'public'              => false,   // not publicly queryable
        'publicly_queryable'  => false,
        'show_ui'             => true,    // visible in WP admin
        'show_in_menu'        => true,
        'show_in_rest'        => false,   // we handle REST ourselves
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'supports'            => [ 'title', 'author' ],
        'menu_icon'           => 'dashicons-upload',
        'menu_position'       => 25,
    ] );
}

// ─────────────────────────────────────────────
// 2. CREATE EARNINGS DB TABLE ON PLUGIN ACTIVATION
// ─────────────────────────────────────────────
register_activation_hook( __FILE__, 'ds_creator_create_earnings_table' );
// Also run on init so the table exists even if added via functions.php
add_action( 'init', 'ds_creator_create_earnings_table' );
function ds_creator_create_earnings_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'ds_creator_earnings';
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) return;

    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        file_id       BIGINT UNSIGNED NOT NULL,
        creator_id    BIGINT UNSIGNED NOT NULL,
        download_id   BIGINT UNSIGNED DEFAULT 0,
        amount        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        platform_cut  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        sale_price    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY file_id (file_id),
        KEY creator_id (creator_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

// ─────────────────────────────────────────────
// 3. REGISTER REST API ROUTES
// ─────────────────────────────────────────────
add_action( 'rest_api_init', 'ds_register_creator_routes' );
function ds_register_creator_routes() {
    $ns = 'ds/v1';

    // List current user's uploads
    register_rest_route( $ns, '/creator/uploads', [
        'methods'             => 'GET',
        'callback'            => 'ds_api_get_uploads',
        'permission_callback' => 'ds_is_logged_in',
    ] );

    // Create new upload (multipart/form-data)
    register_rest_route( $ns, '/creator/upload', [
        'methods'             => 'POST',
        'callback'            => 'ds_api_create_upload',
        'permission_callback' => 'ds_is_logged_in',
    ] );

    // Update existing upload
    register_rest_route( $ns, '/creator/upload/(?P<id>\d+)', [
        'methods'             => 'POST',   // POST with _method=PUT for form-data compat
        'callback'            => 'ds_api_update_upload',
        'permission_callback' => 'ds_is_logged_in',
        'args'                => [ 'id' => [ 'validate_callback' => function( $param ) { return is_numeric( $param ); } ] ],
    ] );

    // Delete upload
    register_rest_route( $ns, '/creator/upload/(?P<id>\d+)/delete', [
    'methods'             => 'POST',
    'callback'            => 'ds_api_delete_upload',
    'permission_callback' => 'ds_is_logged_in',
    'args'                => [ 'id' => [ 'validate_callback' => function( $param ) { return is_numeric( $param ); } ] ],
] );

    // Earnings dashboard data
    register_rest_route( $ns, '/creator/earnings', [
        'methods'             => 'GET',
        'callback'            => 'ds_api_get_earnings',
        'permission_callback' => 'ds_is_logged_in',
    ] );

    // DLM categories (for the category dropdown)
    register_rest_route( $ns, '/creator/categories', [
        'methods'             => 'GET',
        'callback'            => 'ds_api_get_categories',
        'permission_callback' => '__return_true',
    ] );


	// Notifications
	register_rest_route( $ns, '/creator/notifications', [
		'methods' => 'GET',
		'callback' => 'ds_api_get_notifications',
		'permission_callback' => 'ds_is_logged_in',
	] );
	register_rest_route( $ns, '/creator/notifications/(?P<id>\\d+)/read', [
		'methods' => 'POST',
		'callback' => 'ds_api_mark_notification_read',
		'permission_callback' => 'ds_is_logged_in',
	] );
	register_rest_route( $ns, '/creator/notifications/read-all', [
		'methods' => 'POST',
		'callback' => 'ds_api_mark_all_notifications_read',
		'permission_callback' => 'ds_is_logged_in',
	] );
	register_rest_route( $ns, '/creator/notifications/(?P<id>\\d+)', [
		'methods' => 'DELETE',
		'callback' => 'ds_api_delete_notification',
		'permission_callback' => 'ds_is_logged_in',
	] );

	// Creator profile
	register_rest_route( $ns, '/creator/profile', [
		'methods' => 'GET',
		'callback' => 'ds_api_get_profile',
		'permission_callback' => 'ds_is_logged_in',
	] );
	register_rest_route( $ns, '/creator/profile', [
		'methods' => 'POST',
		'callback' => 'ds_api_save_profile',
		'permission_callback' => 'ds_is_logged_in',
	] );

	// Policy documents (URLs of admin-uploaded files)
	register_rest_route( $ns, '/creator/policies', [
		'methods' => 'GET',
		'callback' => 'ds_api_get_policies',
		'permission_callback' => 'ds_is_logged_in',
	] );

}

// Permission helper
function ds_is_logged_in() {
    return is_user_logged_in();
}

// ─────────────────────────────────────────────
// 4. HELPER: UPLOAD FILE TO WP MEDIA LIBRARY
// ─────────────────────────────────────────────
function ds_handle_file_upload( $file_key, $post_id = 0, $is_downloadable_asset = false ) {
    if ( ! isset( $_FILES[ $file_key ] ) || $_FILES[ $file_key ]['error'] !== UPLOAD_ERR_OK ) {
        $code = isset( $_FILES[ $file_key ]['error'] ) ? $_FILES[ $file_key ]['error'] : UPLOAD_ERR_NO_FILE;
        $GLOBALS['ds_last_upload_error'] = ds_upload_error_message( $code );
        error_log( 'DS Creator Portal: upload error for ' . $file_key . ' — ' . $GLOBALS['ds_last_upload_error'] );
        return null;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // The design file is a downloadable deliverable — it's never displayed
    // at a WP crop size anywhere in this system, so skip generating those
    // extra sizes and skip the "scale down big images" pass. This is the
    // main thing making uploads feel slow; it's server processing time,
    // not your connection speed.
    if ( $is_downloadable_asset ) {
        add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 999 );
        add_filter( 'big_image_size_threshold', '__return_false', 999 );
    }

    $attachment_id = media_handle_upload( $file_key, $post_id );

    if ( $is_downloadable_asset ) {
        remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 999 );
        remove_filter( 'big_image_size_threshold', '__return_false', 999 );
    }

    if ( is_wp_error( $attachment_id ) ) {
        $GLOBALS['ds_last_upload_error'] = $attachment_id->get_error_message();
        return null;
    }

    // media_handle_upload() makes this attachment a CHILD of $post_id, which means
    // deleting that post later would auto-delete this file too — even after it's
    // been published live to DLM. Detach it so the file survives independently.
    wp_update_post( [ 'ID' => $attachment_id, 'post_parent' => 0 ] );

    return $attachment_id;
}

function ds_upload_error_message( $code ) {
    switch ( $code ) {
        case UPLOAD_ERR_INI_SIZE:   return 'File exceeds the server\'s upload_max_filesize limit (currently ' . ini_get('upload_max_filesize') . ').';
        case UPLOAD_ERR_FORM_SIZE:  return 'File exceeds the form\'s maximum size.';
        case UPLOAD_ERR_PARTIAL:    return 'File was only partially uploaded — connection was interrupted.';
        case UPLOAD_ERR_NO_FILE:    return 'No file was received by the server.';
        case UPLOAD_ERR_NO_TMP_DIR: return 'Server is missing a temporary folder for uploads.';
        case UPLOAD_ERR_CANT_WRITE: return 'Server failed to write the file to disk.';
        case UPLOAD_ERR_EXTENSION:  return 'A server extension blocked the upload.';
        default:                    return 'Unknown upload error (code ' . $code . ').';
    }
}

// ─────────────────────────────────────────────
// 5. HELPER: FORMAT UPLOAD POST FOR API RESPONSE
// ─────────────────────────────────────────────
function ds_format_upload( $post ) {
    if ( ! $post ) return null;
    $id         = $post->ID;
    $thumb_id   = ds_get_preview_attachment_id( $id );
$thumb_url  = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';

    return [
        'id'            => $id,
        'title'         => get_the_title( $post ),
        'status'        => get_post_meta( $id, 'ds_creator_status', true ) ?: 'pending',
        'category'      => get_post_meta( $id, 'ds_creator_category', true ),
        'shortDesc'     => get_post_meta( $id, 'ds_creator_short_description', true ),
        'longDesc'      => get_post_meta( $id, 'ds_creator_full_description', true ),
        'keywords'      => json_decode( get_post_meta( $id, 'ds_creator_keywords', true ) ?: '[]', true ),
        'tags'          => json_decode( get_post_meta( $id, 'ds_creator_tags', true ) ?: '[]', true ),
        'thumb'         => $thumb_url,
        'thumbnail_id'  => $thumb_id,
        'file_id'       => (int) get_post_meta( $id, 'ds_creator_file_id', true ),
        'fileName'      => get_post_meta( $id, 'ds_creator_file_name', true ),
        'fileSize'      => (int) get_post_meta( $id, 'ds_creator_file_size', true ),
        'earnings'      => (float) get_post_meta( $id, 'ds_creator_total_earnings', true ),
        'downloads'     => (int) get_post_meta( $id, 'ds_creator_download_count', true ),
        'revenue'       => (float) get_post_meta( $id, 'ds_creator_total_revenue', true ),
        'dlm_id'        => (int) get_post_meta( $id, 'ds_creator_dlm_link', true ),
        'date'          => strtotime( $post->post_date ) * 1000, // JS timestamp
        'history'       => json_decode( get_post_meta( $id, 'ds_creator_edit_history', true ) ?: '[]', true ),
        // PATCHED — return every wizard-step field so the frontend can show
        // real captured data instead of leaving it dummy.
        'contentType'   => get_post_meta( $id, 'ds_creator_content_type', true ),
        'technical'     => json_decode( get_post_meta( $id, 'ds_creator_technical', true ) ?: '{}', true ),
        'rightsOwner'   => (bool) get_post_meta( $id, 'ds_creator_rights_owner', true ),
        'aiDisclosure'  => get_post_meta( $id, 'ds_creator_ai_disclosure', true ) ?: 'none',
        'licensed'      => json_decode( get_post_meta( $id, 'ds_creator_licensed', true ) ?: '[]', true ),
    ];
}
if ( ! function_exists( 'ds_get_preview_attachment_id' ) ) {
function ds_get_preview_attachment_id( $post_id ) {
    // 1. The thumbnail field we normally rely on
    $thumb_id = (int) get_post_meta( $post_id, 'ds_creator_thumbnail_id', true );
    if ( $thumb_id && get_post( $thumb_id ) ) return $thumb_id;

    // 2. WordPress's own featured image — set at the same time as the field
    //    above, so if one ever drifted from the other, this still works.
    $featured_id = get_post_thumbnail_id( $post_id );
    if ( $featured_id ) return (int) $featured_id;

    // 3. The design file itself, if it's an image — covers uploads that
    //    never got a separate thumbnail attached.
    $file_id = (int) get_post_meta( $post_id, 'ds_creator_file_id', true );
    if ( $file_id ) {
        $mime = get_post_mime_type( $file_id );
        if ( $mime && strpos( $mime, 'image/' ) === 0 ) return $file_id;
    }

    return 0;
}
}

// ─────────────────────────────────────────────
// 6. GET UPLOADS — GET /ds/v1/creator/uploads
// ─────────────────────────────────────────────
function ds_api_get_uploads( $request ) {
    $user_id = get_current_user_id();
    $posts   = get_posts( [
        'post_type'      => 'ds_creator_upload',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    return rest_ensure_response( array_map( 'ds_format_upload', $posts ) );
}

// ─────────────────────────────────────────────
// 7. CREATE UPLOAD — POST /ds/v1/creator/upload
// ─────────────────────────────────────────────
function ds_api_create_upload( $request ) {
    $user_id = get_current_user_id();
    // Marks this as a direct portal upload — the file has no copy living
    // anywhere else, so DLM can reuse it as-is instead of duplicating it.

    // If the whole request exceeded post_max_size, PHP silently empties $_FILES
    // rather than giving a clean per-file error — catch that case explicitly,
    // since this is exactly what happens with larger files like video.
    if ( empty( $_FILES ) && ! empty( $_SERVER['CONTENT_LENGTH'] ) && (int) $_SERVER['CONTENT_LENGTH'] > 1000 ) {
        return new WP_Error(
            'post_too_large',
            'Upload is larger than this server currently allows (post_max_size = ' . ini_get('post_max_size') . ', upload_max_filesize = ' . ini_get('upload_max_filesize') . '). Ask your host to raise these for video uploads.',
            [ 'status' => 413 ]
        );
    }

    // Safety net against accidental duplicate submissions (double-click, two open
    // tabs, a slow connection retrying). Matches on a key the browser generates
    // once per upload attempt.
    $idempotency_key = sanitize_text_field( $request->get_param('idempotency_key') );
    if ( $idempotency_key ) {
        $existing_by_key = get_posts( [
            'post_type'      => 'ds_creator_upload',
            'post_status'    => 'publish',
            'author'         => $user_id,
            'posts_per_page' => 1,
            'meta_key'       => '_ds_idempotency_key',
            'meta_value'     => $idempotency_key,
        ] );
        if ( $existing_by_key ) {
            // The original request may still be mid-upload — wait briefly for
            // its file/thumbnail to attach rather than returning an incomplete
            // record straight away.
            $existing_id = $existing_by_key[0]->ID;
            for ( $i = 0; $i < 10; $i++ ) {
    if ( get_post_meta( $existing_id, 'ds_creator_file_id', true )
      && get_post_meta( $existing_id, 'ds_creator_thumbnail_id', true ) ) break;
    usleep( 300000 ); // 0.3s
}
            return rest_ensure_response( ds_format_upload( get_post( $existing_id ) ) );
        }
    }

    $title   = sanitize_text_field( $request->get_param('title') );

    // Title is optional now — the admin generates it later via AI Auto-Fill
    // during approval. Fall back to the uploaded filename, or a timestamped
    // placeholder if even that's missing.
    if ( empty( $title ) ) {
        if ( ! empty( $_FILES['design_file']['name'] ) ) {
            $title = sanitize_text_field( pathinfo( $_FILES['design_file']['name'], PATHINFO_FILENAME ) );
        }
        if ( empty( $title ) ) {
            $title = 'Untitled Upload — ' . current_time( 'Y-m-d H:i' );
        }
    }

    // Map status from frontend value to internal value
    $raw_status = $request->get_param('status');
    $status     = ( $raw_status === 'Draft' ) ? 'draft' : 'pending';

    // Create the post
    $post_id = wp_insert_post( [
        'post_type'   => 'ds_creator_upload',
        'post_title'  => $title,
        'post_status' => 'publish',  // WP post is always published; our status is in meta
        'post_author' => $user_id,
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'insert_failed', 'Could not create record.', [ 'status' => 500 ] );
    }

    if ( $idempotency_key ) {
        update_post_meta( $post_id, '_ds_idempotency_key', $idempotency_key );
    }

    // Give this request more breathing room — generating several image sizes
    // for TWO files (design + thumbnail) can be slow on shared hosting.
    if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 120 ); }

    // Handle file uploads — save each link the moment it succeeds, so a
    // timeout/crash later in this function can never leave the post
    // orphaned with no path back to its file.
    $design_attachment_id = ds_handle_file_upload( 'design_file', $post_id, true );
    if ( ! $design_attachment_id ) {
        wp_delete_post( $post_id, true );
        return new WP_Error( 'no_file', $GLOBALS['ds_last_upload_error'] ?? 'Design file upload failed.', [ 'status' => 400 ] );
    }
    update_post_meta( $post_id, 'ds_creator_file_id', $design_attachment_id );

    $thumb_attachment_id = ds_handle_file_upload( 'thumbnail', $post_id );
    if ( ! $thumb_attachment_id ) {
        wp_delete_post( $post_id, true );
        return new WP_Error( 'no_thumb', $GLOBALS['ds_last_upload_error'] ?? 'Thumbnail upload failed.', [ 'status' => 400 ] );
    }
    update_post_meta( $post_id, 'ds_creator_thumbnail_id', $thumb_attachment_id );
    set_post_thumbnail( $post_id, $thumb_attachment_id );

    // Get file info
    $file_path = get_attached_file( $design_attachment_id );
    $file_name = basename( $file_path );
    $file_size = $file_path ? filesize( $file_path ) : 0;

    // Handle new category creation
    $category = sanitize_text_field( $request->get_param('category') );
    if ( $category ) {
        ds_maybe_create_dlm_category( $category );
    }

    // Save all meta
    $meta_fields = [
        'ds_creator_status'            => $status,
        'ds_creator_category'          => $category,
        'ds_creator_short_description' => sanitize_text_field( $request->get_param('shortDesc') ),
        'ds_creator_full_description'  => sanitize_textarea_field( $request->get_param('longDesc') ),
		'ds_creator_meta_description'  => sanitize_textarea_field( $request->get_param('metaDesc') ),
		'ds_creator_meta_description'  => sanitize_textarea_field( $request->get_param('metaDesc') ),
        'ds_creator_keywords'          => wp_json_encode( ds_sanitize_array( $request->get_param('keywords') ) ),
        'ds_creator_tags'              => wp_json_encode( ds_sanitize_array( $request->get_param('tags') ) ),
        'ds_creator_file_name'         => $file_name,
        'ds_creator_file_size'         => $file_size,
        'ds_creator_upload_source'     => 'direct_upload',
        'ds_creator_total_earnings'    => 0,
        'ds_creator_download_count'    => 0,
        'ds_creator_total_revenue'     => 0,
        'ds_creator_edit_history'      => '[]',
        // PATCHED — capture every wizard step so nothing is dummy/lost:
        // Content Type (step 1), Technical Details (step 5), Rights &
        // License (step 6).
        'ds_creator_content_type'      => sanitize_text_field( $request->get_param('contentType') ),
        'ds_creator_technical'         => wp_json_encode( ds_sanitize_assoc_array( $request->get_param('technical') ) ),
        'ds_creator_rights_owner'      => $request->get_param('rightsOwner') ? 1 : 0,
        'ds_creator_ai_disclosure'     => sanitize_text_field( $request->get_param('aiDisclosure') ) ?: 'none',
        'ds_creator_licensed'          => wp_json_encode( ds_sanitize_array( $request->get_param('licensed') ) ),
    ];

    foreach ( $meta_fields as $key => $value ) {
        update_post_meta( $post_id, $key, $value );
    }

    $post = get_post( $post_id );
    return rest_ensure_response( ds_format_upload( $post ) );
}

// ─────────────────────────────────────────────
// 8. UPDATE UPLOAD — POST /ds/v1/creator/upload/{id}
// ─────────────────────────────────────────────
function ds_api_update_upload( $request ) {
    $user_id = get_current_user_id();
    $post_id = (int) $request->get_param('id');
    $post    = get_post( $post_id );

    // Security: verify ownership
    if ( ! $post || $post->post_type !== 'ds_creator_upload' || (int) $post->post_author !== $user_id ) {
        return new WP_Error( 'forbidden', 'You cannot edit this file.', [ 'status' => 403 ] );
    }

    $title = sanitize_text_field( $request->get_param('title') );
    if ( ! empty( $title ) ) {
        wp_update_post( [ 'ID' => $post_id, 'post_title' => $title ] );
    }

    // Optional re-uploads
    if ( isset( $_FILES['design_file'] ) && $_FILES['design_file']['error'] === UPLOAD_ERR_OK ) {
        $new_file_id = ds_handle_file_upload( 'design_file', $post_id, true );
        if ( $new_file_id ) {
            $file_path = get_attached_file( $new_file_id );
            update_post_meta( $post_id, 'ds_creator_file_id', $new_file_id );
            update_post_meta( $post_id, 'ds_creator_file_name', basename( $file_path ) );
            update_post_meta( $post_id, 'ds_creator_file_size', $file_path ? filesize( $file_path ) : 0 );
        }
    }

    if ( isset( $_FILES['thumbnail'] ) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK ) {
        $new_thumb_id = ds_handle_file_upload( 'thumbnail', $post_id );
        if ( $new_thumb_id ) {
            update_post_meta( $post_id, 'ds_creator_thumbnail_id', $new_thumb_id );
            set_post_thumbnail( $post_id, $new_thumb_id );
        }
    }

    // Handle category
    $category = sanitize_text_field( $request->get_param('category') );
    if ( $category ) {
        ds_maybe_create_dlm_category( $category );
        update_post_meta( $post_id, 'ds_creator_category', $category );
    }

    // Update meta fields
    $updatable = [
        'ds_creator_short_description' => sanitize_text_field( $request->get_param('shortDesc') ),
        'ds_creator_full_description'  => sanitize_textarea_field( $request->get_param('longDesc') ),
        'ds_creator_keywords'          => wp_json_encode( ds_sanitize_array( $request->get_param('keywords') ) ),
        'ds_creator_tags'              => wp_json_encode( ds_sanitize_array( $request->get_param('tags') ) ),
    ];

    // PATCHED — same wizard-step fields as create, only touched if sent.
    if ( $request->get_param('contentType') !== null ) {
        $updatable['ds_creator_content_type'] = sanitize_text_field( $request->get_param('contentType') );
    }
    if ( $request->get_param('technical') !== null ) {
        $updatable['ds_creator_technical'] = wp_json_encode( ds_sanitize_assoc_array( $request->get_param('technical') ) );
    }
    if ( $request->get_param('rightsOwner') !== null ) {
        $updatable['ds_creator_rights_owner'] = $request->get_param('rightsOwner') ? 1 : 0;
    }
    if ( $request->get_param('aiDisclosure') !== null ) {
        $updatable['ds_creator_ai_disclosure'] = sanitize_text_field( $request->get_param('aiDisclosure') ) ?: 'none';
    }
    if ( $request->get_param('licensed') !== null ) {
        $updatable['ds_creator_licensed'] = wp_json_encode( ds_sanitize_array( $request->get_param('licensed') ) );
    }

    $raw_status = $request->get_param('status');
    if ( $raw_status ) {
        $updatable['ds_creator_status'] = ( $raw_status === 'Draft' ) ? 'draft' : 'pending';
    }

    foreach ( $updatable as $key => $val ) {
        if ( $val !== null ) update_post_meta( $post_id, $key, $val );
    }

    // Append to edit history
    $history   = json_decode( get_post_meta( $post_id, 'ds_creator_edit_history', true ) ?: '[]', true );
    $history[] = time() * 1000;
    update_post_meta( $post_id, 'ds_creator_edit_history', wp_json_encode( $history ) );

    // Sync changes to DLM if already approved
    $dlm_id = (int) get_post_meta( $post_id, 'ds_creator_dlm_link', true );
    if ( $dlm_id && ! empty( $title ) ) {
        wp_update_post( [ 'ID' => $dlm_id, 'post_title' => $title ] );
    }

    return rest_ensure_response( ds_format_upload( get_post( $post_id ) ) );
}

// ─────────────────────────────────────────────
// 9. DELETE UPLOAD — DELETE /ds/v1/creator/upload/{id}
// ─────────────────────────────────────────────
function ds_api_delete_upload( $request ) {
    $user_id = get_current_user_id();
    $post_id = (int) $request->get_param('id');

    if ( ! $post_id ) {
        return new WP_Error( 'invalid_id', 'Invalid file ID.', [ 'status' => 400 ] );
    }

    $post = get_post( $post_id );

    if ( ! $post ) {
        // Already deleted — return success so frontend stays in sync
        return rest_ensure_response( [ 'deleted' => true, 'id' => $post_id ] );
    }

    if ( $post->post_type !== 'ds_creator_upload' ) {
        return new WP_Error( 'wrong_type', 'Not a creator upload.', [ 'status' => 403 ] );
    }

    // Admins can delete any upload, creators can only delete their own
    if ( ! current_user_can( 'manage_options' ) && (int) $post->post_author !== $user_id ) {
        return new WP_Error( 'forbidden', 'You cannot delete this file.', [ 'status' => 403 ] );
    }

    // Set linked DLM post to draft — don't hard-delete, preserve earnings history
    $dlm_id = (int) get_post_meta( $post_id, 'ds_creator_dlm_link', true );
    if ( $dlm_id && get_post( $dlm_id ) ) {
        wp_update_post( [
            'ID'          => $dlm_id,
            'post_status' => 'draft',
        ] );
    }

    // Delete the creator upload post
    $result = wp_delete_post( $post_id, true );

    if ( ! $result ) {
        return new WP_Error( 'delete_failed', 'Could not delete the file.', [ 'status' => 500 ] );
    }

    return rest_ensure_response( [ 'deleted' => true, 'id' => $post_id ] );
}

// ─────────────────────────────────────────────
// 10. EARNINGS — GET /ds/v1/creator/earnings
// ─────────────────────────────────────────────
function ds_api_get_earnings( $request ) {
    global $wpdb;
    $user_id = get_current_user_id();
    $table   = $wpdb->prefix . 'ds_creator_earnings';

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT e.*, p.post_title AS file_title
         FROM $table e
         LEFT JOIN {$wpdb->posts} p ON p.ID = e.file_id
         WHERE e.creator_id = %d
         ORDER BY e.created_at DESC",
        $user_id
    ) );

    $total_earnings = array_sum( array_column( $rows, 'amount' ) );
    $total_revenue  = array_sum( array_column( $rows, 'sale_price' ) );

    return rest_ensure_response( [
        'earnings' => $rows,
        'totals'   => [
            'earnings' => (float) $total_earnings,
            'revenue'  => (float) $total_revenue,
            'platform' => (float) ( $total_revenue - $total_earnings ),
        ],
    ] );
}

// ─────────────────────────────────────────────
// 11. CATEGORIES — GET /ds/v1/creator/categories
// ─────────────────────────────────────────────
function ds_api_get_categories( $request ) {
    $terms = get_terms( [
        'taxonomy'   => 'dlm_download_category',
        'hide_empty' => false,
    ] );

    if ( is_wp_error( $terms ) ) return rest_ensure_response( [] );

    return rest_ensure_response( array_map( function( $t ) {
        return [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'parent' => (int) $t->parent ];
    }, $terms ) );
}

// ─────────────────────────────────────────────
// 12. HELPER: CREATE DLM CATEGORY IF NEW
// ─────────────────────────────────────────────
function ds_maybe_create_dlm_category( $name ) {
    if ( ! $name ) return;
    $existing = get_term_by( 'name', $name, 'dlm_download_category' );
    if ( ! $existing ) {
        wp_insert_term( $name, 'dlm_download_category' );
    }
}

// ─────────────────────────────────────────────
// 13. HELPER: SANITIZE ARRAY OF STRINGS
// ─────────────────────────────────────────────
function ds_sanitize_array( $arr ) {
    if ( ! is_array( $arr ) ) {
        $decoded = json_decode( $arr, true );
        $arr     = is_array( $decoded ) ? $decoded : [];
    }
    return array_map( 'sanitize_text_field', $arr );
}

// ─────────────────────────────────────────────
// 13b. HELPER: SANITIZE ASSOCIATIVE ARRAY (key -> string value)
//      PATCHED — used for the Technical Details step (slides, aspect
//      ratio, fonts, etc.), which is a set of named fields, not a list.
// ─────────────────────────────────────────────
function ds_sanitize_assoc_array( $arr ) {
    if ( ! is_array( $arr ) ) {
        $decoded = json_decode( $arr, true );
        $arr     = is_array( $decoded ) ? $decoded : [];
    }
    $out = [];
    foreach ( $arr as $k => $v ) {
        if ( ! is_string( $k ) && ! is_int( $k ) ) continue;
        $out[ sanitize_key( (string) $k ) ] = sanitize_text_field( (string) $v );
    }
    return $out;
}

// Simple AJAX delete handler
add_action( 'wp_ajax_ds_delete_upload', 'ds_ajax_delete_upload' );
function ds_ajax_delete_upload() {
    check_ajax_referer( 'ds_delete_nonce', 'nonce' );
    $post_id = (int) $_POST['post_id'];
    $user_id = get_current_user_id();
    if ( ! $post_id ) { wp_send_json_error( 'Invalid ID' ); }
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'ds_creator_upload' ) { wp_send_json_error( 'Not found' ); }
    if ( (int) $post->post_author !== $user_id && ! current_user_can( 'manage_options' ) ) { wp_send_json_error( 'Not allowed' ); }
    $dlm_id = (int) get_post_meta( $post_id, 'ds_creator_dlm_link', true );
    if ( $dlm_id ) { wp_update_post( [ 'ID' => $dlm_id, 'post_status' => 'draft' ] ); }
    wp_delete_post( $post_id, true );
    wp_send_json_success( [ 'deleted' => true, 'id' => $post_id ] );
}
// ─────────────────────────────────────────────
// SUBMIT FROM MY GENERATED IMAGES → CREATOR UPLOADS
// ─────────────────────────────────────────────
add_action( 'rest_api_init', function() {
    register_rest_route( 'ds/v1', '/creator-from-image', [
        'methods'             => 'POST',
        'callback'            => 'ds_create_upload_from_image',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ] );
} );

function ds_create_upload_from_image( $request ) {
$user_id = get_current_user_id();
$attachment_id = (int) $request->get_param( 'attachment_id' );
$hub_file_id = (int) $request->get_param( 'file_id' );

// R2-stored images (via the Storage & Retention system) don't have a
// real WP attachment until now -- materialize one just-in-time so every
// line below this point (which all expects a genuine attachment_id)
// keeps working completely unchanged, whether the source image came
// from local disk (legacy) or R2 (current).
if ( ! $attachment_id && $hub_file_id && function_exists( 'ds_hub_materialize_attachment' ) ) {
$gens = get_user_meta( $user_id, 'ds_ai_generations', true ) ?: array();
$extra_meta = array();
foreach ( $gens as $g ) {
if ( is_array( $g ) && (int) ( $g['file_id'] ?? 0 ) === $hub_file_id ) {
$extra_meta = array( 'prompt' => $g['prompt'] ?? '', 'model' => $g['model'] ?? '' );
break;
}
}
$materialized = ds_hub_materialize_attachment( $hub_file_id, $user_id, $extra_meta );
if ( is_wp_error( $materialized ) ) {
return new WP_Error( 'materialize_failed', $materialized->get_error_message(), [ 'status' => 400 ] );
}
$attachment_id = $materialized;
}

if ( ! $attachment_id ) {
return new WP_Error( 'no_attachment', 'No image provided.', [ 'status' => 400 ] );
}
// Prevent duplicate submissions
$already_submitted = get_post_meta( $attachment_id, 'ds_creator_upload_id', true );
if ( $already_submitted && get_post( $already_submitted ) ) {
return new WP_Error( 'already_submitted', 'This image has already been submitted to Creator Uploads.', [ 'status' => 400 ] );
}

// Get attachment info
$attachment = get_post( $attachment_id );
if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
return new WP_Error( 'invalid_attachment', 'Invalid image.', [ 'status' => 400 ] );
}

    // Get prompt from attachment meta as title
    $prompt    = (string) get_post_meta( $attachment_id, 'ds_ai_prompt', true );
    $model     = (string) get_post_meta( $attachment_id, 'ds_ai_model',  true );
    $title     = $prompt ? wp_trim_words( $prompt, 12, '...' ) : 'AI Generated Image';
    $file_url  = wp_get_attachment_url( $attachment_id );

    // Create the ds_creator_upload post
    $post_id = wp_insert_post( [
        'post_type'   => 'ds_creator_upload',
        'post_title'  => $title,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'insert_failed', 'Could not create upload.', [ 'status' => 500 ] );
    }

    // Set thumbnail as the AI image itself
    set_post_thumbnail( $post_id, $attachment_id );

    // Save all meta
    update_post_meta( $post_id, 'ds_creator_status',           'pending' );
    // Marks this as coming from a personal AI-generated image — that original
    // can be deleted by the user independently, so DLM must keep copying this one.
    update_post_meta( $post_id, 'ds_creator_upload_source',    'ai_generated' );
    update_post_meta( $post_id, 'ds_creator_thumbnail_id',     $attachment_id );
    update_post_meta( $post_id, 'ds_creator_file_id',          $attachment_id );
    update_post_meta( $post_id, 'ds_creator_file_name',        basename( $file_url ) );
    update_post_meta( $post_id, 'ds_creator_file_size',        0 );
    update_post_meta( $post_id, 'ds_creator_category',         'AI Images' );
    update_post_meta( $post_id, 'ds_creator_short_description', $prompt ? substr( $prompt, 0, 150 ) : '' );
    update_post_meta( $post_id, 'ds_creator_full_description',  $prompt ?: '' );
    update_post_meta( $post_id, 'ds_creator_keywords',          wp_json_encode( $model ? [ $model ] : [] ) );
    update_post_meta( $post_id, 'ds_creator_tags',              wp_json_encode( [] ) );
    update_post_meta( $post_id, 'ds_creator_total_earnings',   0 );
    update_post_meta( $post_id, 'ds_creator_download_count',   0 );
    update_post_meta( $post_id, 'ds_creator_total_revenue',    0 );
    update_post_meta( $post_id, 'ds_creator_edit_history',     '[]' );

    // Also create DLM category if needed
    ds_maybe_create_dlm_category( 'AI Images' );

    // Mark the attachment as submitted to creator portal
    update_post_meta( $attachment_id, 'ds_creator_upload_id', $post_id );

    return rest_ensure_response( [
        'success'          => true,
        'pending_cashback' => 0,
        'upload_id'        => $post_id,
        'attachment_id'    => $attachment_id,
        'message'          => 'Submitted to Creator Uploads for review.',
    ] );
}

// =====================================================
// DS NOTIFICATIONS -- table + helpers + REST callbacks
// =====================================================
add_action( 'init', 'ds_maybe_create_notifications_table' );
function ds_maybe_create_notifications_table() {
	global $wpdb;
	if ( get_option( 'ds_notifications_table_v1' ) === 'yes' ) return;
	$table = $wpdb->prefix . 'ds_notifications';
	$charset_collate = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$sql = "CREATE TABLE $table (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NOT NULL,
		type VARCHAR(32) NOT NULL DEFAULT 'system',
		title VARCHAR(255) NOT NULL,
		message TEXT NOT NULL,
		link VARCHAR(255) DEFAULT '',
		is_read TINYINT(1) NOT NULL DEFAULT 0,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY user_id (user_id)
	) $charset_collate;";
	dbDelta( $sql );
	update_option( 'ds_notifications_table_v1', 'yes' );
}

if ( ! function_exists( 'ds_notify_user' ) ) {
function ds_notify_user( $user_id, $type, $title, $message, $link = '' ) {
	global $wpdb;
	$user_id = (int) $user_id;
	if ( ! $user_id ) return false;
	$table = $wpdb->prefix . 'ds_notifications';
	$wpdb->insert( $table, [
		'user_id'    => $user_id,
		'type'       => sanitize_key( $type ),
		'title'      => sanitize_text_field( $title ),
		'message'    => sanitize_textarea_field( $message ),
		'link'       => $link ? esc_url_raw( $link ) : '',
		'is_read'    => 0,
		'created_at' => current_time( 'mysql' ),
	], [ '%d', '%s', '%s', '%s', '%s', '%d', '%s' ] );
	return $wpdb->insert_id;
}
}

function ds_api_get_notifications( $request ) {
	global $wpdb;
	$user_id = get_current_user_id();
	$table = $wpdb->prefix . 'ds_notifications';
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE user_id=%d ORDER BY created_at DESC, id DESC LIMIT 50", $user_id ) );
	$out = array_map( function( $r ) {
		return [
			'id'      => (int) $r->id,
			'type'    => $r->type,
			'title'   => $r->title,
			'message' => $r->message,
			'link'    => $r->link,
			'read'    => (bool) $r->is_read,
			'date'    => $r->created_at,
		];
	}, $rows ?: [] );
	return rest_ensure_response( $out );
}

function ds_api_mark_notification_read( $request ) {
	global $wpdb;
	$user_id = get_current_user_id();
	$id = (int) $request['id'];
	$table = $wpdb->prefix . 'ds_notifications';
	$wpdb->update( $table, [ 'is_read' => 1 ], [ 'id' => $id, 'user_id' => $user_id ], [ '%d' ], [ '%d', '%d' ] );
	return rest_ensure_response( [ 'success' => true ] );
}

function ds_api_mark_all_notifications_read( $request ) {
	global $wpdb;
	$user_id = get_current_user_id();
	$table = $wpdb->prefix . 'ds_notifications';
	$wpdb->update( $table, [ 'is_read' => 1 ], [ 'user_id' => $user_id ], [ '%d' ], [ '%d' ] );
	return rest_ensure_response( [ 'success' => true ] );
}

function ds_api_delete_notification( $request ) {
	global $wpdb;
	$user_id = get_current_user_id();
	$id = (int) $request['id'];
	$table = $wpdb->prefix . 'ds_notifications';
	$wpdb->delete( $table, [ 'id' => $id, 'user_id' => $user_id ], [ '%d', '%d' ] );
	return rest_ensure_response( [ 'success' => true ] );
}
// =====================================================
// DS CREATOR PROFILE -- get/save (stored as user_meta)
// =====================================================
function ds_profile_meta_keys() {
	return [
		'display_name', 'website', 'portfolio_url', 'linkedin', 'instagram', 'behance', 'dribbble', 'other_social',
		'creator_types', 'skills', 'bio', 'years_experience', 'languages',
	];
}
function ds_api_get_profile( $request ) {
	$user_id = get_current_user_id();
	$user = get_userdata( $user_id );
	$out = [];
	foreach ( ds_profile_meta_keys() as $key ) {
		$val = get_user_meta( $user_id, 'ds_profile_' . $key, true );
		if ( in_array( $key, [ 'creator_types', 'skills', 'languages' ], true ) ) {
			$decoded = json_decode( $val ?: '[]', true );
			$out[ $key ] = is_array( $decoded ) ? $decoded : [];
		} else {
			$out[ $key ] = $val ?: '';
		}
	}
	if ( ! $out['display_name'] ) $out['display_name'] = $user->display_name;
	$out['email'] = $user->user_email;
	$out['member_since'] = $user->user_registered;
	return rest_ensure_response( $out );
}
function ds_api_save_profile( $request ) {
	$user_id = get_current_user_id();
	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) $body = $request->get_params();
	foreach ( ds_profile_meta_keys() as $key ) {
		if ( ! array_key_exists( $key, $body ) ) continue;
		$val = $body[ $key ];
		if ( in_array( $key, [ 'creator_types', 'skills', 'languages' ], true ) ) {
			$arr = is_array( $val ) ? $val : ( is_string( $val ) ? array_filter( array_map( 'trim', explode( ',', $val ) ) ) : [] );
			$arr = array_map( 'sanitize_text_field', $arr );
			update_user_meta( $user_id, 'ds_profile_' . $key, wp_json_encode( array_values( $arr ) ) );
		} elseif ( $key === 'bio' ) {
			update_user_meta( $user_id, 'ds_profile_' . $key, sanitize_textarea_field( $val ) );
		} elseif ( in_array( $key, [ 'website', 'portfolio_url', 'linkedin', 'instagram', 'behance', 'dribbble', 'other_social' ], true ) ) {
			update_user_meta( $user_id, 'ds_profile_' . $key, esc_url_raw( $val ) );
		} else {
			update_user_meta( $user_id, 'ds_profile_' . $key, sanitize_text_field( $val ) );
		}
	}
	if ( ! empty( $body['display_name'] ) ) {
		wp_update_user( [ 'ID' => $user_id, 'display_name' => sanitize_text_field( $body['display_name'] ) ] );
	}
	return ds_api_get_profile( $request );
}

// =====================================================
// DS POLICY DOCUMENTS -- read-only endpoint (files uploaded in wp-admin -> Creator Uploads -> Policy Documents)
// =====================================================
if ( ! function_exists( 'ds_policy_doc_keys' ) ) {
function ds_policy_doc_keys() {
	return [
		'creator_terms'     => 'Creator Terms',
		'marketplace_terms' => 'Marketplace Terms',
		'content_license'   => 'Content License',
		'copyright_policy'  => 'Copyright Policy',
		'payment_revenue'   => 'Payment & Revenue Policy',
		'ai_content_policy' => 'AI Content Policy',
	];
}
}
function ds_api_get_policies( $request ) {
	$out = [];
	foreach ( ds_policy_doc_keys() as $key => $label ) {
		$att_id = (int) get_option( 'ds_policy_doc_' . $key );
		$out[] = [
			'key'   => $key,
			'label' => $label,
			'url'   => $att_id ? wp_get_attachment_url( $att_id ) : '',
		];
	}
	return rest_ensure_response( $out );
}
