<?php
/**
 * Code Snippets export — link
 *
 * Snippet ID : 799
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-113
 * Modified   : 2026-09-09 02:43:03
 * Load order : 118 of 144
 */
add_action( 'admin_init', function () {
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['ds_link_dlm'] ) ) return;

    $csv_file = isset( $_GET['csv'] ) ? sanitize_file_name( $_GET['csv'] ) : '';
    if ( ! $csv_file ) wp_die( 'Add &csv=yourfile.csv to the URL.' );

    $csv_path = WP_CONTENT_DIR . '/uploads/dlm-import/' . $csv_file;
    if ( ! file_exists( $csv_path ) ) wp_die( 'CSV not found at: ' . esc_html( $csv_path ) );

    set_time_limit( 0 );
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $handle = fopen( $csv_path, 'r' );
    $header = fgetcsv( $handle );
    $log = []; $linked = 0; $skipped = 0; $missing = 0;
    $thumb_failed = 0;
    $thumb_fail_examples = 0;
    $broken_thumb = 0;

    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
        $data      = array_combine( $header, $row );
        $slug      = $data['Slug'];
        $title     = $data['Title'];
        $image_url = $data['Image URL'];
        $file_url  = $data['Attachment URL'];

        $found = get_posts( [ 'name' => $slug, 'post_type' => 'dlm_download', 'post_status' => 'any', 'numberposts' => 1 ] );
        if ( ! $found ) {
            $found = get_posts( [ 'name' => sanitize_title( $title ), 'post_type' => 'dlm_download', 'post_status' => 'any', 'numberposts' => 1 ] );
        }
        if ( ! $found ) { $missing++; $log[] = "MISSING POST: $title ($slug)"; continue; }

        $post = $found[0];
        $did_something = false;

        // 1. Featured image (thumbnail) — also catch broken/empty existing thumbnails
        $needs_thumb = true;
        if ( has_post_thumbnail( $post->ID ) ) {
            $current_thumb_id = get_post_thumbnail_id( $post->ID );
            $current_path     = get_attached_file( $current_thumb_id );
            $needs_thumb      = ! $current_path || ! file_exists( $current_path ) || filesize( $current_path ) === 0;
            if ( $needs_thumb ) $broken_thumb++;
        }
        if ( $image_url && $needs_thumb ) {
            $thumb_id = ds_attach_existing_file( $image_url, $post->ID );
            if ( $thumb_id ) {
                set_post_thumbnail( $post->ID, $thumb_id );
                $did_something = true;
            } else {
                $thumb_failed++;
                if ( $thumb_fail_examples < 15 ) {
                    $upload_dir = wp_upload_dir();
                    $expected_path = urldecode( str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $image_url ) );
                    $log[] = "THUMB FAIL: $title | url=$image_url | expected=$expected_path | exists=" . ( file_exists( $expected_path ) ? 'yes' : 'no' );
                    $thumb_fail_examples++;
                }
            }
        }

        // 2. Downloadable file (creates the DLM version child post)
        $has_version = get_posts( [ 'post_type' => 'dlm_download_version', 'post_parent' => $post->ID, 'post_status' => 'any', 'numberposts' => 1 ] );
        if ( $file_url && empty( $has_version ) ) {
            $upload_dir = wp_upload_dir();
            $file_path  = urldecode( str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url ) );
            if ( file_exists( $file_path ) ) {
                $version_id = wp_insert_post( [
                    'post_type'   => 'dlm_download_version',
                    'post_title'  => $post->post_title,
                    'post_status' => 'publish',
                    'post_parent' => $post->ID,
                ] );
                update_post_meta( $version_id, '_files', [ $file_url ] );
                update_post_meta( $version_id, '_version', '1.0' );
                update_post_meta( $version_id, '_filesize', filesize( $file_path ) );
                $did_something = true;
            } else {
                $log[] = "FILE NOT FOUND ON SERVER: $file_url";
            }
        }

        $did_something ? $linked++ : $skipped++;
    }
    fclose( $handle );

    echo '<h2>DLM Link Report</h2><p>Linked: ' . $linked . ' | Already linked/skipped: ' . $skipped . ' | Post not found: ' . $missing . ' | Thumbnail failures: ' . $thumb_failed . ' | Broken thumbnails retried: ' . $broken_thumb . '</p>';
    echo '<pre>' . esc_html( implode( "\n", $log ) ) . '</pre>';
    exit;
} );

function ds_attach_existing_file( $file_url, $parent_id ) {
    global $wpdb;
    $existing = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s AND post_type = 'attachment' LIMIT 1", $file_url ) );
    if ( $existing ) return (int) $existing;

    $upload_dir = wp_upload_dir();
    $file_path  = urldecode( str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url ) );
    if ( ! file_exists( $file_path ) ) return false;

    $filetype  = wp_check_filetype( basename( $file_path ), null );
    $attach_id = wp_insert_attachment( [
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name( basename( $file_path ) ),
        'post_status'    => 'inherit',
        'guid'           => $file_url,
    ], $file_path, $parent_id );

    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    return $attach_id;
}
