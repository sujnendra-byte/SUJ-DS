<?php
/**
 * Code Snippets export — Ds ai video api callers (R2 Storage) [Admin Credit Settings]
 *
 * Snippet ID : 757
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-071
 * Modified   : 2026-09-09 02:45:49
 * Load order : 071 of 144
 */
/**
 * Snippet Name: DS AI Video API - Part A
 * Run: Everywhere
 * Description: API caller functions for AI video generation (WaveSpeedAI /
 * Seedance 1.5 Pro). Mirrors the pattern used by "DS AI Image API - Part A"
 * (ds_generate_image_dispatch / ds_poll_replicate) so the video feature
 * plugs into the exact same credit + REST pipeline as AI Images.
 *
 * Fully standalone — does NOT read from or modify your existing "DS API
 * Keys Settings" snippet. The WaveSpeed key lives in its own option,
 * managed by "DS AI Video Settings" (ds-ai-video-settings.php), and is
 * read here via ds_video_get_wavespeed_key().
 */

if ( ! defined('ABSPATH') ) exit;

/* ── Credit cost table (in DS credits, not $) ─────────────────────────
 * Base price is for 720p / 5s / audio off. Scales the same way WaveSpeed's
 * own pricing does: 1080p costs more than 720p, audio doubles the cost,
 * duration scales linearly. Tune ds_video_base_cost() via filter if needed.
 */
if ( ! function_exists('ds_get_default_video_model_costs') ) {
function ds_get_default_video_model_costs() {
    return array(
        'seedance-1.5-pro' => 15, // credits @ 720p / 5s / audio off
    );
}
}

if ( ! function_exists('ds_get_video_model_costs') ) {
function ds_get_video_model_costs() {
    // Admin-configurable via DS Credit Settings (option 'ds_video_credit_settings').
    $opt = get_option('ds_video_credit_settings');
    if ( is_array($opt) && isset($opt['models']) && is_array($opt['models']) && !empty($opt['models']) ) {
        return $opt['models'];
    }
    return ds_get_default_video_model_costs();
}
}

if ( ! function_exists('ds_get_video_cost') ) {
function ds_get_video_cost($model, $resolution = '720p', $duration = 5, $audio = false) {
    $costs = ds_get_video_model_costs();
    $base  = isset($costs[$model]) ? $costs[$model] : 15;

    $opt = get_option('ds_video_credit_settings');
    $res_mults = ( is_array($opt) && isset($opt['resolution_multipliers']) && is_array($opt['resolution_multipliers']) ) ? $opt['resolution_multipliers'] : array();
    if ( empty($res_mults) ) {
        $res_mults = array('480p' => 0.6, '720p' => 1.0, '1080p' => 1.8);
    }
    // The UI sends "480" / "720" / "1080" / "4K" while this table is keyed
    // "480p" / "720p" / "1080p". Unnormalised, a 480p clip silently billed at
    // the 720p rate and 4K matched nothing at all, falling through to 1.0 -
    // i.e. a 4K video cost the same as 720p, and the button never agreed with
    // the charge.
    $res_mults = array_change_key_case( $res_mults, CASE_LOWER );
    $res_key   = preg_replace( '/[^0-9kp]/', '', strtolower( (string) $resolution ) );
    if ( $res_key !== '' && $res_key !== '4k' && substr( $res_key, -1 ) !== 'p' ) {
        $res_key .= 'p';
    }
    if ( isset($res_mults[$res_key]) && $res_mults[$res_key] !== '' ) {
        $res_mult = (float) $res_mults[$res_key];
    } else {
        // Unknown or unconfigured size (4K has no multiplier configured yet):
        // bill at the highest configured tier rather than the cheapest.
        $res_mult = $res_mults ? max( array_map( 'floatval', $res_mults ) ) : 1.0;
    }

    $audio_x = ( is_array($opt) && isset($opt['audio_multiplier']) && $opt['audio_multiplier'] !== '' ) ? (float)$opt['audio_multiplier'] : 2.0;

    $dur_mult   = max(1, (int)$duration) / 5;
    $audio_mult = $audio ? $audio_x : 1;

    $cost = $base * $res_mult * $dur_mult * $audio_mult;
    return (int) apply_filters('ds_video_cost_calculated', max(1, ceil($cost)), $model, $resolution, $duration, $audio);
}
}

/* ── Submit a Seedance 1.5 Pro task (text-to-video or image-to-video) ── */
if ( ! function_exists('ds_call_wavespeed_seedance') ) {
function ds_call_wavespeed_seedance($prompt, $ratio = '16:9', $duration = 5, $resolution = '1080p', $audio = false, $start_image = null, $end_image = null) {
    $key = function_exists('ds_video_get_wavespeed_key') ? ds_video_get_wavespeed_key() : '';
    if (empty($key)) return array('error' => 'WaveSpeed API key not set. Add it on Settings -> AI Video (WaveSpeed).');

    $payload = array(
        'prompt'         => $prompt,
        'aspect_ratio'   => $ratio,
        'duration'       => max(4, min(12, (int)$duration)),
        'resolution'     => $resolution,
        'generate_audio' => (bool)$audio,
        'camera_fixed'   => false,
        'seed'           => -1,
    );

    if (!empty($start_image)) {
        $endpoint = 'https://api.wavespeed.ai/api/v3/bytedance/seedance-v1.5-pro/image-to-video';
        $payload['image'] = $start_image;
        if (!empty($end_image)) $payload['last_image'] = $end_image;
    } else {
        $endpoint = 'https://api.wavespeed.ai/api/v3/bytedance/seedance-v1.5-pro/text-to-video';
    }

    $resp = wp_remote_post($endpoint, array(
        'headers' => array('Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'),
        'body'    => wp_json_encode($payload),
        'timeout' => 30,
    ));

    if (is_wp_error($resp)) return array('error' => 'WaveSpeed: ' . $resp->get_error_message());
    $d = json_decode(wp_remote_retrieve_body($resp), true);

    if (!empty($d['data']['id'])) return array('prediction_id' => $d['data']['id']);
    return array('error' => 'WaveSpeed: ' . (isset($d['message']) ? $d['message'] : wp_json_encode($d)));
}
}

/* ── Poll a WaveSpeed task for its result ──────────────────────────── */
if ( ! function_exists('ds_poll_wavespeed') ) {
function ds_poll_wavespeed($pid) {
    $key = function_exists('ds_video_get_wavespeed_key') ? ds_video_get_wavespeed_key() : '';
    $resp = wp_remote_get(
        'https://api.wavespeed.ai/api/v3/predictions/' . rawurlencode($pid) . '/result',
        array('headers' => array('Authorization' => 'Bearer ' . $key), 'timeout' => 20)
    );
    if (is_wp_error($resp)) return array('status' => 'error', 'error' => $resp->get_error_message());

    $d = json_decode(wp_remote_retrieve_body($resp), true);
    $data = isset($d['data']) ? $d['data'] : array();
    $s    = isset($data['status']) ? $data['status'] : 'unknown';

    if ($s === 'completed') {
        $out = isset($data['outputs']) ? array_values((array)$data['outputs']) : array();
        return array('status' => 'succeeded', 'videos' => $out);
    }
    if ($s === 'failed' || $s === 'cancelled' || $s === 'timeout') {
        return array('status' => 'failed', 'error' => isset($data['error']) ? $data['error'] : 'Generation failed.');
    }
    return array('status' => 'processing'); // created / processing
}
}

/* ── Upload a local reference image to a URL WaveSpeed can fetch ───────
 * WaveSpeed's image-to-video endpoint needs a publicly reachable image
 * URL. We already have the image sitting in wp-content/uploads (dropped
 * there by the browser upload in the shortcode), so we just need its URL.
 * If it's a data: URI instead, save it to uploads first.
 */
if ( ! function_exists('ds_video_ref_to_url') ) {
function ds_video_ref_to_url($src) {
    if (empty($src)) return null;
    if (strpos($src, 'data:') !== 0) return $src; // already a URL

    $upload = wp_upload_dir();
    $fname  = 'ds-video-ref-' . time() . '-' . uniqid('', true) . '.png';
    $fpath  = $upload['path'] . '/' . $fname;
    $parts  = explode(',', $src, 2);
    $data   = base64_decode(isset($parts[1]) ? $parts[1] : '');
    if (empty($data) || strlen($data) < 100) return null;
    file_put_contents($fpath, $data);
    wp_schedule_single_event(time() + 3600, 'ds_cleanup_ref', array($fpath));
    return $upload['url'] . '/' . $fname;
}
}

/* ── Download the finished video into the media library ───────────── */
if ( ! function_exists('ds_save_video_to_uploads') ) {
/**
 * Downloads the WaveSpeed-hosted video into wp-content/uploads and
 * registers it as a media attachment. Always returns an array:
 *   array('url' => <string>, 'attachment_id' => <int, 0 on failure>)
 * On download failure, 'url' falls back to the original remote $src so
 * the video still plays (just isn't persisted locally / has no attachment).
 */
function ds_save_video_to_uploads($src, $prompt = 'ai') {
    $upload = wp_upload_dir();
    $slug   = sanitize_title(substr($prompt, 0, 30));
    if (!$slug) $slug = 'video';
    $fname  = 'ds-ai-video-' . $slug . '-' . time() . '-' . uniqid('', true) . '.mp4';
    $fpath  = $upload['path'] . '/' . $fname;

    $resp = wp_remote_get($src, array('timeout' => 180, 'sslverify' => false, 'stream' => true, 'filename' => $fpath));
    if (is_wp_error($resp) || !file_exists($fpath) || filesize($fpath) < 1000) {
        if (file_exists($fpath)) @unlink($fpath);
        return array('url' => $src, 'attachment_id' => 0); // fall back to remote URL
    }

    // ── Designstager Storage & Retention (R2) ─────────────────────────
    // Preferred path: upload the just-downloaded video to R2, then delete
    // the local copy immediately -- same post-process pattern already used
    // for AI Presentations. Falls back to keeping the local WP attachment
    // (legacy behavior) only if R2 isn't configured yet or the upload fails,
    // so video generation never silently breaks for the user mid-setup.
    if ( function_exists('ds_hub_upload_generated_bytes') ) {
    	$bytes = file_get_contents($fpath);
    	if ($bytes !== false && strlen($bytes) >= 1000) {
    		$up = ds_hub_upload_generated_bytes($bytes, array(
    			'file_type' => 'video',
    			'source'    => 'generated',
    			'extension' => 'mp4',
    			'mime_type' => 'video/mp4',
    			'meta'      => array('prompt' => $prompt, 'model' => 'seedance-1.5-pro'),
    		));
    		unset($bytes);
    		if (!is_wp_error($up) && !empty($up['url'])) {
    			@unlink($fpath); // local copy no longer needed -- it's in R2 now
    			return array('url' => $up['url'], 'attachment_id' => 0, 'file_id' => $up['id']);
    		}
    		$err = is_wp_error($up) ? $up->get_error_message() : 'unknown error';
    		if (function_exists('ds_hub_log')) ds_hub_log('video_upload_fallback_local', $err);
    		error_log('[DS] R2 upload failed for generated video, falling back to local disk: ' . $err);
    	}
    }
    
    $url = $upload['url'] . '/' . $fname;
    $att = array(
        'post_mime_type' => 'video/mp4',
        'post_title'     => sanitize_text_field(substr($prompt, 0, 60)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );
    $aid = wp_insert_attachment($att, $fpath);
    if (!is_wp_error($aid)) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        // wp_generate_attachment_metadata handles video too (needs ffmpeg for
        // thumbnails; safe to call even without it — just skips the poster).
        $meta = wp_generate_attachment_metadata($aid, $fpath);
        wp_update_attachment_metadata($aid, $meta);
        update_post_meta($aid, 'ds_ai_prompt', $prompt);
        update_post_meta($aid, 'ds_ai_model', 'seedance-1.5-pro');
    } else {
        $aid = 0;
    }

    return array('url' => $url, 'attachment_id' => $aid);
}
}

/* ── Background rehost (called from /ds/v1/rehost-video) ─────────────
 * Downloads the WaveSpeed video into the Media Library and writes the
 * resulting attachment_id back into the matching ds_ai_video_generations
 * entry. This is what makes the "Submit to Marketplace" button appear —
 * it only shows once a video has a real attachment_id.
 */
if ( ! function_exists('ds_video_rehost_background') ) {
add_action('ds_video_rehost', 'ds_video_rehost_background', 10, 4);
function ds_video_rehost_background($uid, $pid, $remote_url, $prompt) {
    // FIX 2026-09-23: this runs in WP-Cron where no user is logged in, so
    // ds_hub_upload_generated_bytes() saved the file under user_id 0 and the
    // owner could no longer load/download/save it. Act as the owner.
    if ( (int) $uid > 0 ) { wp_set_current_user( (int) $uid ); }
    $saved = ds_save_video_to_uploads($remote_url, $prompt);
    if (empty($saved['attachment_id']) && empty($saved['file_id'])) return; // rehost failed entirely -- remote URL keeps serving the video, nothing to update

    $gens = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
    foreach ($gens as $gi => $g) {
        if (($g['prediction_id'] ?? '') === $pid) {
            $gens[$gi]['url']           = $saved['url'];
            $gens[$gi]['attachment_id'] = $saved['attachment_id'];
            $gens[$gi]['file_id'] = $saved['file_id'] ?? 0;
            break;
        }
    }
    update_user_meta($uid, 'ds_ai_video_generations', $gens);
}
}

/* FIX 2026-09-23: one-time repair -- give videos saved under user_id 0 back to their owners. */
add_action( 'admin_init', function () {
	if ( get_option( 'ds_video_owner_repair_v1', false ) !== false ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	global $wpdb;
	$t = $wpdb->prefix . 'ds_generated_files';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) return;
	$rows  = $wpdb->get_results( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'ds_ai_video_generations'" );
	$fixed = 0;
	foreach ( (array) $rows as $r ) {
		$gens = maybe_unserialize( $r->meta_value );
		if ( ! is_array( $gens ) ) continue;
		foreach ( $gens as $g ) {
			$fid = isset( $g['file_id'] ) ? (int) $g['file_id'] : 0;
			if ( $fid <= 0 ) continue;
			$fixed += (int) $wpdb->query( $wpdb->prepare( "UPDATE {$t} SET user_id = %d WHERE id = %d AND user_id = 0", (int) $r->user_id, $fid ) );
		}
	}
	update_option( 'ds_video_owner_repair_v1', (string) $fixed, false );
} );
