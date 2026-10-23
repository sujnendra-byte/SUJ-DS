<?php
/**
 * Code Snippets export — DS Video Marketplace Watermark (Upload Endpoint)
 *
 * Snippet ID : 792
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-106
 * Modified   : 2026-09-09 02:42:17
 * Load order : 111 of 144
 */
/**
 * Snippet Name: DS Video Marketplace Watermark (Upload Endpoint)
 * Run: Everywhere
 *
 * Accepts a watermarked video COPY that the browser generates (canvas + MediaRecorder,
 * burned-in "designstager.com" text) right after a creator submits an AI video to the
 * marketplace. The ORIGINAL video file is never touched -- this only stores a separate
 * derivative file and records its path in postmeta. ds_mkt_classify_media() (Snippet 121,
 * "Ds marketplace media pipeline") is updated separately to prefer this derivative for the
 * marketplace grid / popup preview when one exists, falling back to the raw original URL
 * exactly as it does today when one doesn't (e.g. videos submitted before this existed).
 * The real "Download" button (Download Monitor) is completely unaffected -- it always
 * serves the untouched clean original attachment, same as images already work.
 */

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/mkt-upload-video-watermark', array(
        'methods'             => 'POST',
        'callback'            => 'ds_mkt_upload_video_watermark',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});

function ds_mkt_upload_video_watermark($request) {
    $attachment_id = (int) $request->get_param('attachment_id');
    if (!$attachment_id) {
        return new WP_Error('bad_request', 'Missing attachment_id.', array('status' => 400));
    }

    $attachment = get_post($attachment_id);
    if (!$attachment || $attachment->post_type !== 'attachment') {
        return new WP_Error('invalid_attachment', 'Invalid attachment.', array('status' => 400));
    }
    if (strpos((string) get_post_mime_type($attachment_id), 'video/') !== 0) {
        return new WP_Error('not_a_video', 'Not a video attachment.', array('status' => 400));
    }

    // Ownership: only the creator who owns this attachment (or an admin) may attach a
    // watermark derivative to it.
    if ((int) $attachment->post_author !== get_current_user_id() && !current_user_can('manage_options')) {
        return new WP_Error('forbidden', 'You do not own this video.', array('status' => 403));
    }

    $files = $request->get_file_params();
    if (empty($files['watermarked_video']) || $files['watermarked_video']['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_failed', 'No file uploaded.', array('status' => 400));
    }
    $tmp_path = $files['watermarked_video']['tmp_name'];
    $size = (int) $files['watermarked_video']['size'];
    if ($size <= 0 || $size > 80 * 1024 * 1024) { // 80MB safety cap
        return new WP_Error('too_large', 'File too large.', array('status' => 400));
    }

    // Verify the REAL content type (not just the claimed one) before accepting it.
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $real_mime = $finfo ? finfo_file($finfo, $tmp_path) : $files['watermarked_video']['type'];
    if ($finfo) finfo_close($finfo);
    $allowed = array('video/webm', 'video/mp4');
    if (!in_array($real_mime, $allowed, true)) {
        return new WP_Error('bad_type', 'Unsupported file type: ' . $real_mime, array('status' => 400));
    }
    $ext = ($real_mime === 'video/mp4') ? 'mp4' : 'webm';

    $upload_dir = wp_upload_dir();
    $source_path = get_attached_file($attachment_id);
    $dir = $source_path ? dirname($source_path) : $upload_dir['path'];
    if (!is_dir($dir)) wp_mkdir_p($dir);

    $filename = 'ds-mkt-wm-' . $attachment_id . '-' . time() . '.' . $ext;
    $dest = trailingslashit($dir) . $filename;

    if (!@copy($tmp_path, $dest)) {
        return new WP_Error('save_failed', 'Could not save watermarked file.', array('status' => 500));
    }

    // Remove any previous derivative for this attachment before recording the new one.
    $old_rel = get_post_meta($attachment_id, '_ds_mkt_wm_video_file', true);
    if ($old_rel) {
        $old_abs = trailingslashit($upload_dir['basedir']) . $old_rel;
        if ($old_abs !== $dest && file_exists($old_abs)) @unlink($old_abs);
    }

    $rel = ltrim(str_replace(trailingslashit($upload_dir['basedir']), '', $dest), '/\\');
    update_post_meta($attachment_id, '_ds_mkt_wm_video_file', $rel);

    return rest_ensure_response(array('success' => true, 'file' => $rel));
}
