<?php
/**
 * Code Snippets export — Snippet 4 dlm new
 *
 * Snippet ID : 712
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-026
 * Modified   : 2026-09-09 02:45:52
 * Load order : 026 of 144
 */
/**
 * DS Creator Portal — Snippet 4: DLM Integration  (PATCHED — flat ₹2/download)
 * WHERE TO ADD: Paste this into functions.php AFTER Snippet 1, 2, and 3.
 * Replaces your existing Snippet 4 — everything is identical except the
 * "PATCHED" block inside ds_on_dlm_download() near the bottom.
 *
 * WHAT THIS DOES:
 *  - On admin approval → auto-creates a dlm_download post
 *  - Copies title, description, category, thumbnail from the creator upload
 *  - Attaches the design file as a DLM download version
 *  - Links the DLM post ID back to the creator upload (ds_creator_dlm_link)
 *  - If the creator upload is later edited, syncs title/description to DLM
 *  - Assigns the correct DLM category (creates it if new)
 *  - Embeds the thumbnail + an internal category link + one external link
 *    into the page content, and saves Focus Keyphrase/meta description to AIOSEO
 *  - Credits the creator ₹2 (configurable) every time their file is downloaded
 *
 * REQUIRES: Download Monitor (DLM) plugin to be active on the same site.
 */

// ── SEO CONTENT CONFIG ───────────────────────────────────
// AIOSEO's "External links" check just wants at least one outbound link.
// Swap this for whatever resource fits your audience — left generic on purpose.
if ( ! defined( 'DS_DLM_EXTERNAL_LINK_URL' ) )  define( 'DS_DLM_EXTERNAL_LINK_URL',  'https://en.wikipedia.org/wiki/Graphic_design' );
if ( ! defined( 'DS_DLM_EXTERNAL_LINK_TEXT' ) ) define( 'DS_DLM_EXTERNAL_LINK_TEXT', 'graphic design' );

// ── PATCHED: flat per-download creator earning ───────────
// Every time a visitor downloads a creator's approved file (regardless of
// whether it's a paid WooCommerce sale), the creator is credited this
// amount. Change the number below to adjust the rate.
if ( ! defined( 'DS_PER_DOWNLOAD_RATE' ) ) define( 'DS_PER_DOWNLOAD_RATE', 2.00 ); // ₹2 per download

// ─────────────────────────────────────────────
// 1. MAIN FUNCTION: CREATE DLM POST FROM CREATOR UPLOAD
//    Called by Snippet 3 when admin clicks "Approve"
// ─────────────────────────────────────────────
function ds_copy_attachment_to_site_media( $source_attachment_id, $parent_post_id = 0 ) {
    $source_path = get_attached_file( $source_attachment_id );

    if ( ! $source_path || ! file_exists( $source_path ) ) {
        $source_url = wp_get_attachment_url( $source_attachment_id );
        if ( ! $source_url ) return 0;
        $source_path = download_url( $source_url );
        if ( is_wp_error( $source_path ) ) return 0;
        $downloaded = true;
    } else {
        $downloaded = false;
    }

    $upload_dir = wp_upload_dir();
    $filename   = wp_unique_filename( $upload_dir['path'], basename( $source_path ) );
    $dest_path  = $upload_dir['path'] . '/' . $filename;

    if ( ! copy( $source_path, $dest_path ) ) {
        if ( $downloaded ) @unlink( $source_path );
        return 0;
    }
    if ( $downloaded ) @unlink( $source_path );

    $filetype = wp_check_filetype( $filename, null );
    if ( ! $filetype['type'] ) return 0;

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $att_id = wp_insert_attachment(
        [ 'post_mime_type' => $filetype['type'], 'post_title' => preg_replace('/\.[^.]+$/', '', $filename), 'post_status' => 'inherit', 'post_parent' => $parent_post_id ],
        $dest_path,
        $parent_post_id
    );
    if ( is_wp_error( $att_id ) ) return 0;

    wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $dest_path ) );

    // Carry over the client-generated watermarked video derivative (if any) so the
    // new marketplace-facing attachment gets the same protected preview the creator's
    // original video had. The file being copied above (the real, clean video) is never
    // touched by this -- only the separate watermark-preview derivative is copied too.
    $wm_rel = get_post_meta( $source_attachment_id, '_ds_mkt_wm_video_file', true );
    if ( $wm_rel ) {
        $wm_abs = trailingslashit( $upload_dir['basedir'] ) . $wm_rel;
        if ( file_exists( $wm_abs ) ) {
            $wm_filename = wp_unique_filename( $upload_dir['path'], basename( $wm_abs ) );
            $wm_dest     = $upload_dir['path'] . '/' . $wm_filename;
            if ( copy( $wm_abs, $wm_dest ) ) {
                $wm_new_rel = ltrim( str_replace( trailingslashit( $upload_dir['basedir'] ), '', $wm_dest ), '/\\' );
                update_post_meta( $att_id, '_ds_mkt_wm_video_file', $wm_new_rel );
            }
        }
    }

    return (int) $att_id;
}
function ds_create_dlm_from_creator_upload( $creator_post_id ) {
    // Don't create a duplicate if one already exists
    $existing_dlm = (int) get_post_meta( $creator_post_id, 'ds_creator_dlm_link', true );
    if ( $existing_dlm && get_post( $existing_dlm ) ) {
        // Already has a DLM post — just make sure it's published
        wp_update_post( [ 'ID' => $existing_dlm, 'post_status' => 'publish' ] );
        // Keep AI Studio matching in sync on re-approve too.
        update_post_meta( $existing_dlm, '_ds_tpl_shape',    get_post_meta( $creator_post_id, 'ds_creator_content_shape', true ) );
        update_post_meta( $existing_dlm, '_ds_tpl_semantic', get_post_meta( $creator_post_id, 'ds_creator_semantic', true ) ? '1' : '' );
        update_post_meta( $existing_dlm, '_ds_tpl_keywords', get_post_meta( $creator_post_id, 'ds_creator_template_keywords', true ) );
        update_post_meta( $existing_dlm, '_ds_tpl_enabled',  get_post_meta( $creator_post_id, 'ds_creator_show_in_picker', true ) ? '1' : '' );
        return $existing_dlm;
    }

    $creator_post = get_post( $creator_post_id );
    if ( ! $creator_post ) return null;

    // Gather all meta from the creator upload
    $title       = get_the_title( $creator_post );
    $short_desc  = get_post_meta( $creator_post_id, 'ds_creator_short_description', true );
    $long_desc   = get_post_meta( $creator_post_id, 'ds_creator_full_description', true );
    $category_id    = (int) get_post_meta( $creator_post_id, 'ds_creator_category_id',    true );
    $subcategory_id = (int) get_post_meta( $creator_post_id, 'ds_creator_subcategory_id', true );
    $category       = get_post_meta( $creator_post_id, 'ds_creator_category', true ); // display name only, for the link text below
    $keywords    = json_decode( get_post_meta( $creator_post_id, 'ds_creator_keywords', true ) ?: '[]', true );
    $tags        = json_decode( get_post_meta( $creator_post_id, 'ds_creator_tags', true ) ?: '[]', true );
    $thumb_id    = (int) get_post_meta( $creator_post_id, 'ds_creator_thumbnail_id', true );
    $file_att_id = (int) get_post_meta( $creator_post_id, 'ds_creator_file_id', true );

    // Build post content from descriptions
    $post_content = '';
    if ( $short_desc ) $post_content .= '<p>' . wp_kses_post( $short_desc ) . '</p>';
    if ( $long_desc )  $post_content .= wp_kses_post( $long_desc );

    // ── CREATE THE dlm_download POST ──────────────────────
    $dlm_post_id = wp_insert_post( [
        'post_type'    => 'dlm_download',
        'post_title'   => $title,
        'post_content' => $post_content,
        'post_excerpt' => $short_desc,
        'post_status'  => 'publish',
        'post_author'  => $creator_post->post_author,
    ] );

    if ( is_wp_error( $dlm_post_id ) ) {
        error_log( 'DS Creator Portal: Failed to create DLM post for upload #' . $creator_post_id );
        return null;
    }

    // ── THUMBNAIL: reuse directly for portal uploads (no separate copy exists
    // to protect against); copy for AI-generated submissions (the user's
    // personal original is independent and could be deleted later).
    $upload_source = get_post_meta( $creator_post_id, 'ds_creator_upload_source', true );
    $site_thumb_id = 0;
    if ( $thumb_id ) {
        if ( $upload_source === 'direct_upload' ) {
            $site_thumb_id = $thumb_id;
        } else {
            $site_thumb_id = ds_copy_attachment_to_site_media( $thumb_id, $dlm_post_id );
        }
        set_post_thumbnail( $dlm_post_id, $site_thumb_id ?: $thumb_id );
		if ( function_exists( 'ds_mkt_force_watermark_preview' ) ) {
			// Force a real watermark preview for the marketplace-facing thumbnail on
			// approval, regardless of who uploaded/generated the original or copied
			// attachment (including the site admin account) -- this is now a genuine
			// live marketplace listing.
			ds_mkt_force_watermark_preview( $site_thumb_id ?: $thumb_id );
		}
    }

    // ── ASSIGN DLM CATEGORY ───────────────────────────────
    // Category/subcategory are now real term IDs chosen from a dropdown (see Snippet 3),
    // never free text — so there's nothing to match by name and nothing to accidentally
    // create. If either ID is invalid/missing, it's simply skipped, never invented.
    $category_link_url = false;
    $term_ids_to_assign = array();
    if ( $category_id    && term_exists( $category_id,    'dlm_download_category' ) ) $term_ids_to_assign[] = $category_id;
    if ( $subcategory_id && term_exists( $subcategory_id, 'dlm_download_category' ) ) $term_ids_to_assign[] = $subcategory_id;

    if ( $term_ids_to_assign ) {
        wp_set_object_terms( $dlm_post_id, $term_ids_to_assign, 'dlm_download_category' );
        $link = get_term_link( $category_id, 'dlm_download_category' );
        if ( ! is_wp_error( $link ) ) $category_link_url = $link;
    }

    // ── ASSIGN DLM TAGS ───────────────────────────────────
    $all_tags = array_merge( $keywords, $tags );
    if ( $all_tags ) {
        // DLM uses dlm_download_tag taxonomy if it exists, else post_tag
        $tag_taxonomy = taxonomy_exists( 'dlm_download_tag' ) ? 'dlm_download_tag' : 'post_tag';
        wp_set_object_terms( $dlm_post_id, $all_tags, $tag_taxonomy );
    }

    // ── ADD IMAGE + INTERNAL LINK + EXTERNAL LINK TO CONTENT ──────────────
    // These three AIOSEO checks ("Images/videos in content", "Internal links",
    // "External links") need a real <img>/<a> in the body — not just a featured
    // image — so we add them here instead of trusting the AI to invent working URLs.
    $embed_thumb_id = $site_thumb_id ?: $thumb_id;
    $extra_content  = '';

    if ( $embed_thumb_id ) {
        $img_url = wp_get_attachment_image_url( $embed_thumb_id, 'large' );
        if ( $img_url ) {
            $extra_content .= '<p><img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $title ) . '" /></p>';
        }
    }
    if ( $category_link_url ) {
        $extra_content .= '<p>Browse more <a href="' . esc_url( $category_link_url ) . '">' . esc_html( $category ) . ' designs</a> in our marketplace.</p>';
    }
    if ( defined( 'DS_DLM_EXTERNAL_LINK_URL' ) && DS_DLM_EXTERNAL_LINK_URL ) {
        $extra_content .= '<p>Learn more about <a href="' . esc_url( DS_DLM_EXTERNAL_LINK_URL ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( DS_DLM_EXTERNAL_LINK_TEXT ) . '</a> trends and best practices.</p>';
    }

    if ( $extra_content ) {
        $post_content .= $extra_content;
        wp_update_post( [ 'ID' => $dlm_post_id, 'post_content' => $post_content ] );
    }

    // ── CREATE DLM DOWNLOAD VERSION ───────────────────────
    // DLM stores versions as a custom post type: dlm_download_version
    if ( $file_att_id ) {
        if ( $upload_source === 'direct_upload' ) {
            $site_file_id = $file_att_id;
        } else {
            $site_file_id = ds_copy_attachment_to_site_media( $file_att_id, $dlm_post_id );
        }
        $file_url = $site_file_id ? wp_get_attachment_url( $site_file_id ) : wp_get_attachment_url( $file_att_id );
        if ( $file_url ) {
            ds_create_dlm_version( $dlm_post_id, $file_url, $site_file_id ?: $file_att_id );
			if ( function_exists( 'ds_mkt_force_watermark_preview' ) ) {
				$ds_mkt_wm_target = $site_file_id ?: $file_att_id;
				$ds_mkt_wm_mime = get_post_mime_type( $ds_mkt_wm_target );
				if ( $ds_mkt_wm_mime && strpos( $ds_mkt_wm_mime, 'image/' ) === 0 ) {
					ds_mkt_force_watermark_preview( $ds_mkt_wm_target );
				}
			}
        }
    }

	// ── SAVE SEO META TO DLM POST (AIOSEO) ──────────────────────────────
$meta_desc = get_post_meta( $creator_post_id, 'ds_creator_meta_description', true );
$focus_kw  = ! empty( $keywords ) ? ( is_array( $keywords ) ? $keywords[0] : $keywords ) : '';

if ( $meta_desc || $focus_kw ) {
    global $wpdb;
    $aioseo_table = $wpdb->prefix . 'aioseo_posts';
    if ( $wpdb->get_var("SHOW TABLES LIKE '$aioseo_table'") === $aioseo_table ) {
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $aioseo_table WHERE post_id = %d", $dlm_post_id
        ));
        if ( $existing ) {
            $wpdb->update(
                $aioseo_table,
                [
                    'title'         => $title,
                    'description'   => $meta_desc,
                    'keyphrases'    => $focus_kw ? wp_json_encode(['focus'=>['keyphrase'=>$focus_kw,'score'=>0,'analysis'=>[]]]) : null,
                    'updated'       => current_time('mysql'),
                ],
                [ 'post_id' => $dlm_post_id ],
                [ '%s', '%s', '%s', '%s' ],
                [ '%d' ]
            );
        } else {
            $wpdb->insert(
                $aioseo_table,
                [
                    'post_id'       => $dlm_post_id,
                    'title'         => $title,
                    'description'   => $meta_desc,
                    'keyphrases'    => $focus_kw ? wp_json_encode(['focus'=>['keyphrase'=>$focus_kw,'score'=>0,'analysis'=>[]]]) : null,
                    'created'       => current_time('mysql'),
                    'updated'       => current_time('mysql'),
                ],
                [ '%d', '%s', '%s', '%s', '%s', '%s' ]
            );
        }
    }
}

    // ── STORE DLM META ────────────────────────────────────
    // Link back to creator upload
    update_post_meta( $dlm_post_id, 'ds_creator_upload_id', $creator_post_id );
    // Mark creator upload with DLM ID
    update_post_meta( $creator_post_id, 'ds_creator_dlm_link', $dlm_post_id );
    // Mark creator ID on DLM post for earnings tracking
    update_post_meta( $dlm_post_id, 'ds_creator_id', $creator_post->post_author );

    // ── AI STUDIO SMART TEMPLATE MATCHING ──────────────────
    // Same meta keys Snippet 21's matching engine reads, so a template
    // approved here is immediately usable in "Pick a Design".
    update_post_meta( $dlm_post_id, '_ds_tpl_shape',    get_post_meta( $creator_post_id, 'ds_creator_content_shape', true ) );
    update_post_meta( $dlm_post_id, '_ds_tpl_semantic', get_post_meta( $creator_post_id, 'ds_creator_semantic', true ) ? '1' : '' );
    update_post_meta( $dlm_post_id, '_ds_tpl_keywords', get_post_meta( $creator_post_id, 'ds_creator_template_keywords', true ) );
    update_post_meta( $dlm_post_id, '_ds_tpl_enabled',  get_post_meta( $creator_post_id, 'ds_creator_show_in_picker', true ) ? '1' : '' );

    return $dlm_post_id;
}

// ─────────────────────────────────────────────
// 2. CREATE A DLM DOWNLOAD VERSION
//    DLM stores file info in dlm_download_version posts
// ─────────────────────────────────────────────
function ds_create_dlm_version( $dlm_post_id, $file_url, $attachment_id = 0 ) {
    if ( ! function_exists( 'download_monitor' ) ) return;

    $version = new DLM_Download_Version();
    $version->set_download_id( $dlm_post_id );
    $version->set_author( get_current_user_id() ?: 1 );
    $version->set_version( '1.0' );
    $version->set_date( new DateTime( current_time( 'mysql' ) ) );
    $version->set_mirrors( array( $file_url ) );

    download_monitor()->service( 'version_repository' )->persist( $version );

    $v_id = $version->get_id();
    if ( $v_id ) {
        update_post_meta( $v_id, '_download_files', $file_url );
    }

    download_monitor()->service( 'transient_manager' )->clear_versions_transient( $dlm_post_id );

    update_post_meta( $dlm_post_id, '_version', '1.0' );
    update_post_meta( $dlm_post_id, '_download_count', 0 );
}

// ─────────────────────────────────────────────
// 4. HOOK INTO DLM DOWNLOAD EVENT
//    When someone downloads a file, log it in our earnings table
//    This bridges DLM's download tracking to our earnings system
// ─────────────────────────────────────────────
// PATCHED — ensure the earnings table has a downloader_id column so we
// can de-dupe per user. Runs once (checked via option flag), safe to
// re-run, and safe even if another snippet already created this table.
add_action( 'init', function () {
    if ( get_option( 'ds_earnings_table_v2' ) === '1' ) return;
    global $wpdb;
    $table = $wpdb->prefix . 'ds_creator_earnings';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        file_id BIGINT UNSIGNED NOT NULL,
        creator_id BIGINT UNSIGNED NOT NULL,
        download_id BIGINT UNSIGNED DEFAULT 0,
        downloader_id BIGINT UNSIGNED DEFAULT 0,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        platform_cut DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        sale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY file_id (file_id),
        KEY creator_id (creator_id),
        KEY downloader_id (downloader_id)
    ) $charset;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    update_option( 'ds_earnings_table_v2', '1' );
} );

add_action( 'dlm_downloading', 'ds_on_dlm_download', 10, 2 );
function ds_on_dlm_download( $download, $version ) {
    // $download is a DLM Download object
    // FIXED — DLM_Download exposes its ID via get_id(), not a public ->id
	// property. The old isset($download->id) check was always false, so
	// this function silently returned before ever crediting a creator —
	// on every single download, for every product, since this hook existed.
	if ( is_object( $download ) && method_exists( $download, 'get_id' ) ) {
		$dlm_post_id = (int) $download->get_id();
	} elseif ( is_object( $download ) && isset( $download->id ) ) {
		$dlm_post_id = (int) $download->id; // fallback for older DLM versions
	} else {
		$dlm_post_id = 0;
	}
    if ( ! $dlm_post_id ) return;

    $creator_upload_id = (int) get_post_meta( $dlm_post_id, 'ds_creator_upload_id', true );
    if ( ! $creator_upload_id ) return;

    $creator_id = (int) get_post_meta( $dlm_post_id, 'ds_creator_id', true );
    if ( ! $creator_id ) return;

    global $wpdb;
    $table = $wpdb->prefix . 'ds_creator_earnings';

    // PATCHED — the same logged-in user downloading the same file more than
    // once in a calendar month only counts once for creator earnings.
    // Anonymous/logged-out downloads always count (no reliable identity to
    // de-dupe against) — but in practice the login gate means this is rare.
    $downloader_id = get_current_user_id();
    if ( $downloader_id ) {
        $month_start = gmdate( 'Y-m-01 00:00:00' );
        $already_counted = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE file_id = %d AND downloader_id = %d AND created_at >= %s",
            $creator_upload_id, $downloader_id, $month_start
        ) );
        if ( $already_counted > 0 ) {
            return; // already earned for this user + file this month
        }
    }

    // Get the sale price from WooCommerce/DLM if this download is tied to a
    // paid order (returns 0 for a normal free download).
    $sale_price = (float) apply_filters( 'ds_download_sale_price', 0, $dlm_post_id );

    // ── PATCHED ────────────────────────────────────────────────────────
    // Paid download (WooCommerce order matched via the filter above): keep
    // the existing 70/30 revenue-share on the sale price.
    // Free download (the normal case — no sale price): credit the creator
    // a flat DS_PER_DOWNLOAD_RATE per download instead of ₹0.
    if ( $sale_price > 0 ) {
        $creator_cut  = round( $sale_price * 0.70, 2 );
        $platform_cut = round( $sale_price * 0.30, 2 );
    } else {
        $creator_cut  = (float) DS_PER_DOWNLOAD_RATE;
        $platform_cut = 0.00;
    }
    // ── END PATCHED ───────────────────────────────────────────────────

    // Log to earnings table
    $wpdb->insert(
        $table,
        [
            'file_id'       => $creator_upload_id,
            'creator_id'    => $creator_id,
            'download_id'   => $dlm_post_id,
            'downloader_id' => $downloader_id,
            'amount'        => $creator_cut,
            'platform_cut'  => $platform_cut,
            'sale_price'    => $sale_price,
            'created_at'    => current_time( 'mysql' ),
        ],
        [ '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s' ]
    );

    // Update running totals on the creator upload post
    $old_count   = (int) get_post_meta( $creator_upload_id, 'ds_creator_download_count', true );
    $old_earn    = (float) get_post_meta( $creator_upload_id, 'ds_creator_total_earnings', true );
    $old_revenue = (float) get_post_meta( $creator_upload_id, 'ds_creator_total_revenue', true );
    update_post_meta( $creator_upload_id, 'ds_creator_download_count', $old_count + 1 );
    update_post_meta( $creator_upload_id, 'ds_creator_total_earnings', $old_earn + $creator_cut );
    update_post_meta( $creator_upload_id, 'ds_creator_total_revenue',  $old_revenue + $sale_price );

    // Update user totals
    $user_earn = (float) get_user_meta( $creator_id, 'ds_creator_total_earnings', true );
    $user_dl   = (int)   get_user_meta( $creator_id, 'ds_creator_total_downloads', true );
    update_user_meta( $creator_id, 'ds_creator_total_earnings',  $user_earn + $creator_cut );
    update_user_meta( $creator_id, 'ds_creator_total_downloads', $user_dl + 1 );
}

// ─────────────────────────────────────────────
// 5. ADMIN COLUMN ON DLM DOWNLOADS — SHOW CREATOR
// ─────────────────────────────────────────────
add_filter( 'manage_dlm_download_posts_columns', function( $cols ) {
    $cols['ds_creator'] = 'Creator';
    return $cols;
} );

add_action( 'manage_dlm_download_posts_custom_column', function( $col, $post_id ) {
    if ( $col !== 'ds_creator' ) return;
    $creator_id = (int) get_post_meta( $post_id, 'ds_creator_id', true );
    if ( $creator_id ) {
        $user = get_user_by( 'id', $creator_id );
        echo $user ? esc_html( $user->display_name ) : 'Creator #' . $creator_id;
    } else {
        echo '—';
    }
}, 10, 2 );
add_action( 'rest_api_init', function() {
    register_rest_route( 'ds/v1', '/repair-dlm-file', [
        'methods'             => 'POST',
        'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        'callback'            => function( WP_REST_Request $req ) {
            $dlm_post_id = (int) $req->get_param( 'dlm_post_id' );
            if ( ! $dlm_post_id ) return [ 'success' => false, 'error' => 'Missing dlm_post_id' ];

            $creator_upload_id = (int) get_post_meta( $dlm_post_id, 'ds_creator_upload_id', true );
            $att_id            = $creator_upload_id ? (int) get_post_meta( $creator_upload_id, 'ds_creator_file_id', true ) : 0;
            if ( ! $att_id ) return [ 'success' => false, 'error' => 'Cannot find original attachment' ];

            $new_att_id = ds_copy_attachment_to_site_media( $att_id, $dlm_post_id );
            if ( ! $new_att_id ) return [ 'success' => false, 'error' => 'File is gone from Media Library' ];

            $file_url = wp_get_attachment_url( $new_att_id );

            $versions = get_posts( [ 'post_type' => 'dlm_download_version', 'post_parent' => $dlm_post_id, 'posts_per_page' => -1, 'fields' => 'ids' ] );
            foreach ( $versions as $v_id ) {
                update_post_meta( $v_id, '_download_files', $file_url );
            }

            if ( function_exists( 'download_monitor' ) ) {
                download_monitor()->service( 'transient_manager' )->clear_versions_transient( $dlm_post_id );
            }

            return [ 'success' => true, 'repaired_url' => $file_url, 'versions_updated' => count( $versions ) ];
        },
    ] );
} );
