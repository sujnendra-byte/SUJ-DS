<?php
/**
 * Code Snippets export — Ds marketplace media pipeline
 *
 * Snippet ID : 718
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-032
 * Modified   : 2026-09-09 02:40:26
 * Load order : 032 of 144
 */
/**
 * Snippet Name: DS Marketplace Media Pipeline
 * Run: Everywhere
 * Description: Marketplace preview images.
 *   - Uploads by site admins (manage_options) — e.g. bulk Media Library
 *     uploads of pre-made thumbnails — are left completely untouched: no
 *     WordPress core sub-sizes, no grid size, no watermark copy. The single
 *     file you upload is what gets used everywhere.
 *   - Uploads made by anyone else (creator/marketplace submissions) get
 *     WordPress's own core sub-sizes suppressed too, and exactly ONE
 *     derivative is generated: a watermarked WEBP copy. That one file is
 *     used for both the marketplace grid card AND the popup preview.
 *   - The original uploaded file is NEVER modified/replaced in either case,
 *     so Download Monitor's gated download is unaffected.
 *
 * Requires: PHP GD extension with WEBP support (present on virtually every
 * modern WP host). Diagonal watermark text requires a TTF font on the
 * server; if none is found it automatically falls back to plain horizontal
 * tiled text so it still works everywhere.
 */

// ---------------------------------------------------------------------------
// 0. Kill WordPress's own default sub-sizes (thumbnail/medium/medium_large/
//    large) for every upload, admin or creator — nothing on this site reads
//    those sizes any more. Also stop WP's "scaled" >2560px copy that
//    duplicates the original file.
// ---------------------------------------------------------------------------
add_filter('intermediate_image_sizes_advanced', function () {
    return array();
});
add_filter('big_image_size_threshold', '__return_false');

// ---------------------------------------------------------------------------
// 1. Was an attachment uploaded by a site admin? Checked off the
//    attachment's post_author (the uploading user) so it works both at
//    upload time and when backfilling old attachments.
// ---------------------------------------------------------------------------
function ds_mkt_is_admin_upload($attachment_id) {
    $author_id = (int) get_post_field('post_author', $attachment_id);
    return $author_id && user_can($author_id, 'manage_options');
}

// ---------------------------------------------------------------------------
// 2. Watermark text helpers
// ---------------------------------------------------------------------------
function ds_mkt_watermark_text() {
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    return apply_filters('ds_mkt_watermark_text', $host);
}

function ds_mkt_resolve_font_path() {
    static $cached = null;
    if ($cached !== null) return $cached;

    $candidates = array_filter(array(
        apply_filters('ds_mkt_watermark_font_path', ''), // drop your own .ttf path in via this filter if you like
        get_stylesheet_directory() . '/fonts/ds-watermark.ttf', // or place one here
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        'C:/Windows/Fonts/arialbd.ttf',
    ));
    foreach ($candidates as $path) {
        if ($path && file_exists($path)) { $cached = $path; return $cached; }
    }
    return $cached = '';
}

// ---------------------------------------------------------------------------
// 3. Stamp a tiled watermark onto an image resource already loaded into GD.
// ---------------------------------------------------------------------------
function ds_mkt_stamp_watermark_resource($im) {
    imagealphablending($im, true);
    imagesavealpha($im, true);

    $w = imagesx($im);
    $h = imagesy($im);
    $text = ds_mkt_watermark_text();
    $alpha = (int) apply_filters('ds_mkt_watermark_alpha', 111); // 0 = opaque, 127 = invisible
    $color = imagecolorallocatealpha($im, 255, 255, 255, $alpha);
    $font = ds_mkt_resolve_font_path();

    if ($font) {
        $font_size = max(10, (int) round($w / 28));
        $angle = -28;
        $step_x = (int) round($font_size * (strlen($text) * 0.58 + 7));
        $step_y = (int) round($font_size * 3.6);
        for ($y = -$h; $y < $h * 2; $y += $step_y) {
            for ($x = -$w; $x < $w * 2; $x += $step_x) {
                imagettftext($im, $font_size, $angle, (int) $x, (int) $y, $color, $font, $text);
            }
        }
    } else {
        // No TTF font on this server — plain horizontal tiled rows instead of diagonal.
        $gd_font = 4;
        $step_x = (strlen($text) + 6) * imagefontwidth($gd_font);
        $step_y = imagefontheight($gd_font) * 4;
        for ($y = 0; $y < $h; $y += $step_y) {
            for ($x = -$w; $x < $w * 2; $x += $step_x) {
                imagestring($im, $gd_font, (int) $x, $y, $text, $color);
            }
        }
    }
    return $im;
}

// ---------------------------------------------------------------------------
// 4. Build the single watermarked WEBP preview from a creator's uploaded
//    file. Reads whatever format was uploaded (jpg/png/webp), stamps the
//    watermark, saves as ONE .webp file next to the original. The original
//    file itself is only ever read, never written to.
// ---------------------------------------------------------------------------
function ds_mkt_build_single_webp_preview($attachment_id) {
    if (get_post_meta($attachment_id, '_ds_mkt_preview_done', true) === '1') return;

    $mime = get_post_mime_type($attachment_id);
    if (!$mime || strpos($mime, 'image/') !== 0 || $mime === 'image/gif') {
        update_post_meta($attachment_id, '_ds_mkt_preview_done', '1');
        return;
    }
    if (!function_exists('imagewebp')) {
        update_post_meta($attachment_id, '_ds_mkt_preview_done', '1'); // no WEBP support on this server
        return;
    }

    $src_file = get_attached_file($attachment_id);
    if (!$src_file || !file_exists($src_file)) return;

    if (function_exists('wp_raise_memory_limit')) wp_raise_memory_limit('image');

    switch ($mime) {
        case 'image/jpeg': $im = @imagecreatefromjpeg($src_file); break;
        case 'image/png':  $im = @imagecreatefrompng($src_file);  break;
        case 'image/webp': $im = @imagecreatefromwebp($src_file); break;
        default: $im = false;
    }
    if (!$im) return;

    ds_mkt_stamp_watermark_resource($im);

    $info = pathinfo($src_file);
    $dest_file = $info['dirname'] . '/' . $info['filename'] . '-preview.webp';
    $ok = imagewebp($im, $dest_file, (int) apply_filters('ds_mkt_webp_quality', 82));
    imagedestroy($im);
    if (!$ok) return;

    $upload_dir = wp_get_upload_dir();
    $base_dir = trailingslashit($upload_dir['basedir']);
    $rel_path = str_replace($base_dir, '', $dest_file);

    update_post_meta($attachment_id, '_ds_mkt_preview_webp_file', $rel_path);
    update_post_meta($attachment_id, '_ds_mkt_preview_done', '1');
}

// ---------------------------------------------------------------------------
// 4b. Force-generate (or regenerate) the watermark preview for an attachment,
// bypassing the admin-upload skip entirely. Called from the marketplace
// approval step (creator-upload -> dlm_download) so that ANY image reaching
// the live marketplace gets a real watermarked derivative, even when the
// uploader (or the account clicking Approve, for copied attachments) is the
// site admin. Does nothing if a real watermark file already exists on disk.
// ---------------------------------------------------------------------------
function ds_mkt_force_watermark_preview($attachment_id) {
    if (!$attachment_id) return;

    $rel = get_post_meta($attachment_id, '_ds_mkt_preview_webp_file', true);
    if ($rel) {
        $upload_dir = wp_get_upload_dir();
        if (file_exists(trailingslashit($upload_dir['basedir']) . $rel)) return;
    }

    delete_post_meta($attachment_id, '_ds_mkt_preview_done');
    ds_mkt_build_single_webp_preview($attachment_id);
}

// ---------------------------------------------------------------------------
// 5. New uploads: run the pipeline shortly after upload (scheduled, so the
//    upload response itself stays fast). Admin uploads are skipped entirely
//    — nothing is ever generated for them.
// ---------------------------------------------------------------------------
add_filter('wp_generate_attachment_metadata', function ($metadata, $attachment_id) {
    if (ds_mkt_is_admin_upload($attachment_id)) {
        update_post_meta($attachment_id, '_ds_mkt_preview_done', '1'); // nothing to do, ever
        return $metadata;
    }
    if (!wp_next_scheduled('ds_mkt_process_attachment_event', array($attachment_id))) {
        wp_schedule_single_event(time(), 'ds_mkt_process_attachment_event', array($attachment_id));
    }
    // Nudge WP-Cron to run right now in the background (non-blocking) instead
    // of waiting for the next organic visitor — keeps uploads fast while
    // shrinking the unwatermarked window to a couple of seconds.
    if (function_exists('spawn_cron')) spawn_cron();
    return $metadata;
}, 20, 2);

add_action('ds_mkt_process_attachment_event', 'ds_mkt_build_single_webp_preview');

// ---------------------------------------------------------------------------
// 6. Resolve the one URL to show everywhere (grid card AND popup) for a
//    given attachment: the watermarked WEBP for creator uploads, or the
//    original file untouched for admin uploads.
// ---------------------------------------------------------------------------
function ds_mkt_get_preview_url($attachment_id) {
    if (!$attachment_id) return '';

    $rel = get_post_meta($attachment_id, '_ds_mkt_preview_webp_file', true);
    if ($rel) {
        $upload_dir = wp_get_upload_dir();
        $abs = trailingslashit($upload_dir['basedir']) . $rel;
        if (file_exists($abs)) {
            return trailingslashit($upload_dir['baseurl']) . $rel;
        }
    }
    return wp_get_attachment_url($attachment_id); // admin upload, or not processed yet
}

// ---------------------------------------------------------------------------
// 7. Classify any attachment (image / gif / video) into what the marketplace
//    grid + popup should render for it. Used by the marketplace shortcode.
// ---------------------------------------------------------------------------
function ds_mkt_classify_media($attachment_id) {
    $mime = get_post_mime_type($attachment_id);
    if (!$mime) return null;

    if (strpos($mime, 'video/') === 0) {
        $url = wp_get_attachment_url($attachment_id);
        if (!$url) return null;
        // Prefer the client-generated watermarked derivative (Snippet 630) for the
        // marketplace grid/preview, when one has been generated. The real attachment
        // file above is NEVER modified -- falls back to it unchanged when no
        // derivative exists yet (e.g. videos submitted before this existed).
        $wm_url = $url;
        $wm_rel = get_post_meta($attachment_id, '_ds_mkt_wm_video_file', true);
        if ($wm_rel) {
            $wm_upload_dir = wp_upload_dir();
            $wm_abs = trailingslashit($wm_upload_dir['basedir']) . $wm_rel;
            if (file_exists($wm_abs)) {
                $wm_url = trailingslashit($wm_upload_dir['baseurl']) . $wm_rel;
            }
        }
        return array('type' => 'video', 'grid' => $wm_url, 'preview' => $wm_url, 'mime' => $mime);
    }
    if ($mime === 'image/gif') {
        // Served as-is (original, 'full' size) so the animation is preserved —
        // resizing an animated GIF through GD collapses it to one frame.
        // Note: GIF/MP4 previews are NOT watermarked (see readme below).
        $url = wp_get_attachment_url($attachment_id);
        if (!$url) return null;
        return array('type' => 'gif', 'grid' => $url, 'preview' => $url, 'mime' => $mime);
    }
    if (strpos($mime, 'image/') === 0) {
        $url = ds_mkt_get_preview_url($attachment_id);
        return array('type' => 'image', 'grid' => $url, 'preview' => $url, 'mime' => $mime);
    }
    return null;
}

// ---------------------------------------------------------------------------
// 7b. Real width x height + simplified aspect ratio for an attachment, e.g.
// "1920x1080 (16:9)". Used by the marketplace grid cards. Reads whatever
// WordPress already extracted at upload time (images always have this;
// videos have it when the server can read video metadata).
// ---------------------------------------------------------------------------
function ds_mkt_get_resolution_label($attachment_id) {
    if (!$attachment_id) return '';

    $cached = get_post_meta($attachment_id, '_ds_mkt_resolution_label', true);
    if ($cached) return $cached;

    $meta = wp_get_attachment_metadata($attachment_id);
    $w = !empty($meta['width']) ? (int) $meta['width'] : 0;
    $h = !empty($meta['height']) ? (int) $meta['height'] : 0;

    $mime = get_post_mime_type($attachment_id);

    if (!$w || !$h) {
        if ($mime && strpos($mime, 'image/') === 0) {
            $file = get_attached_file($attachment_id);
            if ($file && file_exists($file)) {
                $info = @getimagesize($file);
                if ($info) { $w = (int) $info[0]; $h = (int) $info[1]; }
            }
        } elseif ($mime && strpos($mime, 'video/') === 0) {
            $file = get_attached_file($attachment_id);
            if ($file && file_exists($file)) {
                if (!class_exists('getID3') && file_exists(ABSPATH . WPINC . '/ID3/getid3.php')) {
                    require_once ABSPATH . WPINC . '/ID3/getid3.php';
                }
                if (class_exists('getID3')) {
                    try {
                        $id3 = new getID3();
                        $vinfo = $id3->analyze($file);
                        if (!empty($vinfo['video']['resolution_x']) && !empty($vinfo['video']['resolution_y'])) {
                            $w = (int) $vinfo['video']['resolution_x'];
                            $h = (int) $vinfo['video']['resolution_y'];
                        }
                    } catch (Exception $e) {}
                }
            }
        }
    }

    if (!$w || !$h) return '';

    $label = ds_mkt_format_resolution($w, $h);
    update_post_meta($attachment_id, '_ds_mkt_resolution_label', $label);
    return $label;
}

function ds_mkt_format_resolution($w, $h) {
    $decimal = $w / $h;
    $known = array(
        '16:9' => 16/9, '9:16' => 9/16, '4:3' => 4/3, '3:4' => 3/4,
        '1:1' => 1, '21:9' => 21/9, '3:2' => 3/2, '2:3' => 2/3,
    );
    $ratio_label = null;
    $best_diff = 0.02;
    foreach ($known as $label => $val) {
        $diff = abs($decimal - $val) / $val;
        if ($diff < $best_diff) { $best_diff = $diff; $ratio_label = $label; }
    }
    if (!$ratio_label) {
        $gcd = function ($a, $b) use (&$gcd) { return $b ? $gcd($b, $a % $b) : $a; };
        $g = max(1, $gcd($w, $h));
        $ratio_label = ($w / $g) . ':' . ($h / $g);
    }
    return $w . 'x' . $h . ' (' . $ratio_label . ')';
}

// ---------------------------------------------------------------------------
// 7c. Resolution label for a file that is NOT necessarily a WP attachment —
// e.g. a marketplace download file dropped straight into uploads/ without
// ever being added to the Media Library. Maps the URL to its local path
// and reads real dimensions directly off disk.
// ---------------------------------------------------------------------------
function ds_mkt_get_resolution_label_from_url($url) {
    if (!$url) return '';
    $upload_dir = wp_get_upload_dir();
    $path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
    if (!file_exists($path)) return '';

    $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION));
    $w = 0; $h = 0;

    if (in_array($ext, array('png', 'jpg', 'jpeg', 'webp', 'gif'))) {
        $info = @getimagesize($path);
        if ($info) { $w = (int) $info[0]; $h = (int) $info[1]; }
    } elseif (in_array($ext, array('mp4', 'mov', 'avi', 'webm'))) {
        if (!class_exists('getID3') && file_exists(ABSPATH . WPINC . '/ID3/getid3.php')) {
            require_once ABSPATH . WPINC . '/ID3/getid3.php';
        }
        if (class_exists('getID3')) {
            try {
                $id3 = new getID3();
                $vinfo = $id3->analyze($path);
                if (!empty($vinfo['video']['resolution_x']) && !empty($vinfo['video']['resolution_y'])) {
                    $w = (int) $vinfo['video']['resolution_x'];
                    $h = (int) $vinfo['video']['resolution_y'];
                }
            } catch (Exception $e) {}
        }
    }

    if (!$w || !$h) return '';
    return ds_mkt_format_resolution($w, $h);
}

// ---------------------------------------------------------------------------
// 8. Allow GIF/MP4 attachments to be picked in the native "Set featured image"
// box on the dlm_download edit screen (WP restricts that picker to images
// by default). Gallery/attached images already accept any file type.
// ---------------------------------------------------------------------------
add_action('admin_footer-post.php', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'dlm_download') return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.wp && wp.media && wp.media.controller && wp.media.controller.FeaturedImage) {
            wp.media.controller.FeaturedImage.prototype.defaults.library.type = ['image', 'video'];
        }
    });
    </script>
    <?php
});

// ---------------------------------------------------------------------------
// 9. Admin tool: backfill existing creator-submitted images in small batches
// (avoids a timeout if you try to process hundreds of images in one
// request). Admin-uploaded images are skipped — they're used as-is.
// Visit: wp-admin/admin.php?page=ds-mkt-warm
// ---------------------------------------------------------------------------
add_action('admin_menu', function () {
    add_management_page('Warm Marketplace Images', 'Warm Marketplace Images', 'manage_options', 'ds-mkt-warm', 'ds_mkt_render_warm_page');
});

function ds_mkt_render_warm_page() {
    if (!current_user_can('manage_options')) return;
    $batch_size = 8;

    if (!empty($_POST['ds_mkt_reset_nonce']) && wp_verify_nonce($_POST['ds_mkt_reset_nonce'], 'ds_mkt_reset')) {
        $all = get_posts(array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_mime_type' => array('image/jpeg', 'image/png', 'image/webp'),
            'meta_query' => array(array('key' => '_ds_mkt_preview_done', 'compare' => 'EXISTS')),
        ));
        $upload_dir = wp_get_upload_dir();
        foreach ($all as $aid) {
            $rel = get_post_meta($aid, '_ds_mkt_preview_webp_file', true);
            if ($rel) {
                $abs = trailingslashit($upload_dir['basedir']) . $rel;
                if (file_exists($abs)) @unlink($abs);
            }
            delete_post_meta($aid, '_ds_mkt_preview_webp_file');
            delete_post_meta($aid, '_ds_mkt_preview_done');

            // Clean up leftover files/meta from the old (pre-WEBP) pipeline, if present.
            $old_rel = get_post_meta($aid, '_ds_mkt_wm_full_file', true);
            if ($old_rel) {
                $old_abs = trailingslashit($upload_dir['basedir']) . $old_rel;
                if (file_exists($old_abs)) @unlink($old_abs);
            }
            delete_post_meta($aid, '_ds_mkt_wm_full_file');
            delete_post_meta($aid, '_ds_mkt_wm_full_done');
            delete_post_meta($aid, '_ds_mkt_grid_done');
        }
        echo '<div class="notice notice-success"><p>Reset ' . count($all) . ' image(s) — click "Process next" below to regenerate them all with the current watermark text.</p></div>';
    }

    if (!empty($_POST['ds_mkt_warm_nonce']) && wp_verify_nonce($_POST['ds_mkt_warm_nonce'], 'ds_mkt_warm')) {
        $ids = get_posts(array(
            'post_type' => 'attachment',
            'posts_per_page' => $batch_size,
            'fields' => 'ids',
            'post_mime_type' => array('image/jpeg', 'image/png', 'image/webp'),
            'meta_query' => array(array('key' => '_ds_mkt_preview_done', 'compare' => 'NOT EXISTS')),
        ));
        foreach ($ids as $aid) {
            if (ds_mkt_is_admin_upload($aid)) {
                update_post_meta($aid, '_ds_mkt_preview_done', '1');
                continue;
            }
            ds_mkt_build_single_webp_preview($aid);
        }
        echo '<div class="notice notice-success"><p>Processed ' . count($ids) . ' image(s).</p></div>';
    }

    $remaining = new WP_Query(array(
        'post_type' => 'attachment',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'post_mime_type' => array('image/jpeg', 'image/png', 'image/webp'),
        'meta_query' => array(array('key' => '_ds_mkt_preview_done', 'compare' => 'NOT EXISTS')),
    ));
    $left = $remaining->found_posts;
    ?>
    <div class="wrap">
        <h1>Warm Marketplace Images</h1>
        <p>Generates the single watermarked WEBP preview for creator-submitted images that don't have one yet. Admin-uploaded images are skipped (used as-is). New uploads get this automatically; this is only needed once for everything uploaded before this update.</p>
        <p><strong><?php echo intval($left); ?></strong> image(s) remaining.</p>
        <?php if ($left > 0): ?>
            <form method="post">
                <?php wp_nonce_field('ds_mkt_warm', 'ds_mkt_warm_nonce'); ?>
                <button type="submit" class="button button-primary">Process next <?php echo $batch_size; ?></button>
            </form>
        <?php else: ?>
            <p>All done.</p>
        <?php endif; ?>
        <hr style="margin:24px 0;">
        <p>Changed the watermark text? Already-processed images keep the old text until reset.</p>
        <form method="post" onsubmit="return confirm('This deletes all generated watermark copies so every image regenerates with the current text. Continue?');">
            <?php wp_nonce_field('ds_mkt_reset', 'ds_mkt_reset_nonce'); ?>
            <button type="submit" class="button">Reset all watermarks</button>
        </form>
    </div>
    <?php
}
