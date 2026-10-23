<?php
/**
 * Code Snippets export — import-marketplace-images
 *
 * Snippet ID : 800
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-114
 * Modified   : 2026-09-09 02:43:09
 * Load order : 119 of 144
 */
add_action( 'admin_init', function () {
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['ds_link_images'] ) ) return;

    $csv_file = isset( $_GET['csv'] ) ? sanitize_file_name( $_GET['csv'] ) : '';
    if ( ! $csv_file ) wp_die( 'Add &csv=yourfile.csv to the URL.' );

    $csv_path = WP_CONTENT_DIR . '/uploads/dlm-import/' . $csv_file;
    if ( ! file_exists( $csv_path ) ) wp_die( 'CSV not found at: ' . esc_html( $csv_path ) );

    $offset = isset( $_GET['offset'] ) ? (int) $_GET['offset'] : 0;
    $limit  = isset( $_GET['limit'] ) ? (int) $_GET['limit'] : 20;

    set_time_limit( 0 );
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    global $wpdb;

    $handle = fopen( $csv_path, 'r' );
    $header = fgetcsv( $handle );

    $thumb_fixed = 0; $thumb_ok = 0; $thumb_missing_file = 0;
    $version_created = 0; $version_skipped = 0;
    $missing_post = 0; $errors = 0;
    $log = [];
    $row_index = -1;
    $processed = 0;
    $reached_eof = true;

    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
        $row_index++;
        if ( $row_index < $offset ) continue;
        if ( $processed >= $limit ) { $reached_eof = false; break; }
        $processed++;

        if ( count( $row ) !== count( $header ) ) {
            $errors++;
            $log[] = "BAD ROW (row $row_index): column count mismatch";
            continue;
        }
        $data  = array_combine( $header, $row );
        $slug  = trim( $data['Slug'] );
        $title = $data['Title'];

        $found = get_posts( [ 'name' => $slug, 'post_type' => 'dlm_download', 'post_status' => 'any', 'numberposts' => 1 ] );
        if ( ! $found ) {
            $missing_post++;
            $log[] = "MISSING POST (row $row_index): $title ($slug)";
            continue;
        }
        $post    = $found[0];
        $post_id = $post->ID;

        $upload_dir = wp_upload_dir();

        $thumb_url  = $data['Image URL'];
        $thumb_path = urldecode( str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $thumb_url ) );

        $needs_thumb_fix = true;
        if ( has_post_thumbnail( $post_id ) ) {
            $current_thumb_id = get_post_thumbnail_id( $post_id );
            $current_path     = get_attached_file( $current_thumb_id );
            $current_ext      = $current_path ? strtolower( pathinfo( $current_path, PATHINFO_EXTENSION ) ) : '';
            if ( $current_path && file_exists( $current_path ) && filesize( $current_path ) > 0 && $current_ext === 'webp' ) {
                $needs_thumb_fix = false;
            }
        }

        if ( $needs_thumb_fix ) {
            if ( ! file_exists( $thumb_path ) ) {
                $thumb_missing_file++;
                $log[] = "MISSING WEBP THUMB (row $row_index): $title | $thumb_path";
            } else {
                $thumb_attach_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s AND post_type = 'attachment' LIMIT 1", $thumb_url ) );
                if ( ! $thumb_attach_id ) {
                    $filetype = wp_check_filetype( basename( $thumb_path ), null );
                    $thumb_attach_id = wp_insert_attachment( [
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => sanitize_file_name( basename( $thumb_path ) ),
                        'post_status'    => 'inherit',
                        'guid'           => $thumb_url,
                    ], $thumb_path, $post_id );
                    if ( is_wp_error( $thumb_attach_id ) || ! $thumb_attach_id ) {
                        $errors++;
                        $log[] = "THUMB ATTACH FAILED (row $row_index): $title";
                        $thumb_attach_id = 0;
                    } else {
                        $thumb_data = wp_generate_attachment_metadata( $thumb_attach_id, $thumb_path );
                        wp_update_attachment_metadata( $thumb_attach_id, $thumb_data );
                    }
                }
                if ( $thumb_attach_id ) {
                    set_post_thumbnail( $post_id, $thumb_attach_id );
                    $thumb_fixed++;
                }
            }
        } else {
            $thumb_ok++;
        }

        $att_url   = $data['Attachment URL'];
        $file_path = urldecode( str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $att_url ) );

        $has_version = get_posts( [ 'post_type' => 'dlm_download_version', 'post_parent' => $post_id, 'post_status' => 'any', 'numberposts' => 1 ] );
        if ( ! empty( $has_version ) ) {
            $version_skipped++;
        } elseif ( file_exists( $file_path ) ) {
            $version_id = wp_insert_post( [
                'post_type'   => 'dlm_download_version',
                'post_title'  => $post->post_title,
                'post_status' => 'publish',
                'post_parent' => $post_id,
            ] );
            if ( ! is_wp_error( $version_id ) && $version_id ) {
                update_post_meta( $version_id, '_files', [ $att_url ] );
                update_post_meta( $version_id, '_version', '1.0' );
                update_post_meta( $version_id, '_filesize', filesize( $file_path ) );
                $version_created++;
            } else {
                $errors++;
                $log[] = "VERSION CREATE FAILED (row $row_index): $title";
            }
        } else {
            $log[] = "MISSING PNG FILE (row $row_index): $title | $file_path";
        }
    }
    fclose( $handle );

    echo '<h2>Image Link Report (v2 - webp thumbnails)</h2>';
    echo '<p>Offset: ' . $offset . ' | Limit: ' . $limit . ' | Processed this batch: ' . $processed . '</p>';
    echo '<p>Thumbnails fixed to webp: ' . $thumb_fixed . ' | Already correct: ' . $thumb_ok . ' | Missing webp file: ' . $thumb_missing_file . '</p>';
    echo '<p>Download versions created: ' . $version_created . ' | Already had version: ' . $version_skipped . '</p>';
    echo '<p>Missing post: ' . $missing_post . ' | Errors: ' . $errors . '</p>';
    if ( $reached_eof ) {
        echo '<p><strong>Reached end of CSV.</strong></p>';
    } else {
        $next = $offset + $limit;
        echo '<p>Next batch: <a href="?ds_link_images=1&csv=' . urlencode( $csv_file ) . '&offset=' . $next . '&limit=' . $limit . '">offset=' . $next . '</a></p>';
    }
    echo '<pre>' . esc_html( implode( "
", $log ) ) . '</pre>';
    exit;
} );
