<?php
/**
 * Code Snippets export — DS AI Audio REST
 *
 * Snippet ID : 785
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-099
 * Modified   : 2026-09-09 02:45:55
 * Load order : 104 of 144
 */
/**
* Snippet Name: DS AI Audio REST
* Run: Everywhere
* Registers the REST routes for AI Audio: Generate Music (and Background
* Music, which is the same ACE-Step 1.5 model in a specialized mode —
* not a separate route) plus Text to Speech.
*
* Mirrors "DS AI Video REST" exactly: same credit functions
* (ds_get_credits / ds_deduct_credits / ds_add_credits), same shared
* credit pool, same PENDING-placeholder + poll + refund-once + async
* rehost pattern.
*
* Requires: DS AI Audio API - Part A (ds_call_wavespeed_acestep,
* ds_call_wavespeed_speech, ds_poll_wavespeed_audio, ds_get_music_cost,
* ds_get_speech_cost, ds_build_bgmusic_tags, ds_audio_rehost_background).
*/

if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function() {

// ── Generate Music (covers both "Generate Music" free-form mode and
// "Background Music" structured mode via the 'mode' param) ───────────
register_rest_route('ds/v1', '/generate-music', array(
'methods' => 'POST',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {

@ignore_user_abort(true);
@set_time_limit(60);

$uid = get_current_user_id();
$mode = sanitize_text_field($req->get_param('mode') ?: 'generate'); // 'generate' | 'background'
$duration = max(5, min(240, (int)($req->get_param('duration') ?: 60)));
$instrumental = filter_var($req->get_param('instrumental'), FILTER_VALIDATE_BOOLEAN);

if ($mode === 'background') {
$purpose = sanitize_text_field($req->get_param('purpose') ?: '');
$mood = sanitize_text_field($req->get_param('mood') ?: '');
$style = sanitize_text_field($req->get_param('style') ?: '');
$intensity = sanitize_text_field($req->get_param('intensity') ?: 'medium');
$tags = function_exists('ds_build_bgmusic_tags') ? ds_build_bgmusic_tags($purpose, $mood, $style, $intensity) : 'background music, instrumental';
// Background Music defaults to instrumental so it never talks over the
// video, but honors the sidebar's Vocals toggle: when the user asks for
// vocals we generate a short, generic mood-flavored lyric snippet
// server-side (users never type lyrics for Background Music).
$instrumental = $req->has_param('instrumental') ? filter_var($req->get_param('instrumental'), FILTER_VALIDATE_BOOLEAN) : true;
$lyrics = $instrumental ? '[inst]' : (function_exists('ds_build_bgmusic_lyrics') ? ds_build_bgmusic_lyrics($mood, $purpose) : '[inst]');
$label = 'bgmusic-' . ($purpose ?: 'video');
} else {
$tags = sanitize_text_field($req->get_param('tags') ?: '');
$lyrics = sanitize_textarea_field($req->get_param('lyrics') ?: '');
$label = 'music-' . substr(sanitize_title($tags ?: 'track'), 0, 30);
if (empty($tags)) {
return rest_ensure_response(array('error' => true, 'message' => 'Please describe the style of music you want.'));
}
}

// ── Credit check ──────────────────────────────────────────────
$cost = function_exists('ds_get_music_cost') ? ds_get_music_cost($duration) : 6;
$credits = function_exists('ds_get_credits') ? ds_get_credits($uid) : 0;
if ($credits < $cost) {
return rest_ensure_response(array('error' => true, 'message' => 'Not enough credits. Need ' . $cost . '.'));
}

if (!function_exists('ds_call_wavespeed_acestep')) {
return rest_ensure_response(array('error' => true, 'message' => 'Audio API snippet not active.'));
}

$result = ds_call_wavespeed_acestep($tags, $lyrics, $duration, $instrumental);

if (!empty($result['error'])) {
return rest_ensure_response(array('error' => true, 'message' => $result['error']));
}
if (empty($result['prediction_id'])) {
return rest_ensure_response(array('error' => true, 'message' => 'No task ID returned.'));
}

// ── Deduct credits ONLY after WaveSpeed accepted the task ──────
if (!ds_deduct_credits($uid, $cost)) {
return rest_ensure_response(array('error' => true, 'message' => 'Not enough credits. Need ' . $cost . '.'));
}

$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
$now = current_time('Y-m-d H:i:s');
array_unshift($gens, array(
'prediction_id' => $result['prediction_id'],
'kind' => 'music',
'mode' => $mode,
'label' => $label,
'tags' => $tags,
'duration' => $duration,
'instrumental' => $instrumental,
'credits' => $cost,
'date' => $now,
'status' => 'pending',
'url' => '',
));
update_user_meta($uid, 'ds_ai_audio_generations', array_slice($gens, 0, 200));

$credits_after = function_exists('ds_get_credits') ? ds_get_credits($uid) : ($credits - $cost);
return rest_ensure_response(array(
'prediction_id' => $result['prediction_id'],
'credits_left' => $credits_after,
));
},
));

// ── Poll Music ───────────────────────────────────────────────────────
register_rest_route('ds/v1', '/poll-music', array(
'methods' => 'POST',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
return ds_audio_poll_common($req, 'music');
},
));

// ── Generate Speech (Text to Speech) ──────────────────────────────────
register_rest_route('ds/v1', '/generate-speech', array(
'methods' => 'POST',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {

@ignore_user_abort(true);
@set_time_limit(60);

$uid = get_current_user_id();
$text = sanitize_textarea_field($req->get_param('text') ?: '');
$voice_id = sanitize_text_field($req->get_param('voice_id') ?: 'Friendly_Person');
$speed = (float)($req->get_param('speed') ?: 1.0);
$emotion = sanitize_text_field($req->get_param('emotion') ?: 'neutral');
$format = sanitize_text_field($req->get_param('format') ?: 'mp3');

if (empty($text)) {
return rest_ensure_response(array('error' => true, 'message' => 'Please enter some text to convert to speech.'));
}
if (strlen($text) > 10000) {
return rest_ensure_response(array('error' => true, 'message' => 'Text is limited to 10,000 characters.'));
}

$cost = function_exists('ds_get_speech_cost') ? ds_get_speech_cost($text) : 4;
$credits = function_exists('ds_get_credits') ? ds_get_credits($uid) : 0;
if ($credits < $cost) {
return rest_ensure_response(array('error' => true, 'message' => 'Not enough credits. Need ' . $cost . '.'));
}

if (!function_exists('ds_call_wavespeed_speech')) {
return rest_ensure_response(array('error' => true, 'message' => 'Audio API snippet not active.'));
}

$result = ds_call_wavespeed_speech($text, $voice_id, $speed, $emotion, $format);

if (!empty($result['error'])) {
return rest_ensure_response(array('error' => true, 'message' => $result['error']));
}
if (empty($result['prediction_id'])) {
return rest_ensure_response(array('error' => true, 'message' => 'No task ID returned.'));
}

if (!ds_deduct_credits($uid, $cost)) {
return rest_ensure_response(array('error' => true, 'message' => 'Not enough credits. Need ' . $cost . '.'));
}

$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
$now = current_time('Y-m-d H:i:s');
array_unshift($gens, array(
'prediction_id' => $result['prediction_id'],
'kind' => 'speech',
'label' => substr(sanitize_text_field($text), 0, 60),
'voice_id' => $voice_id,
'credits' => $cost,
'date' => $now,
'status' => 'pending',
'url' => '',
));
update_user_meta($uid, 'ds_ai_audio_generations', array_slice($gens, 0, 200));

$credits_after = function_exists('ds_get_credits') ? ds_get_credits($uid) : ($credits - $cost);
return rest_ensure_response(array(
'prediction_id' => $result['prediction_id'],
'credits_left' => $credits_after,
));
},
));

// ── Poll Speech ──────────────────────────────────────────────────────
register_rest_route('ds/v1', '/poll-speech', array(
'methods' => 'POST',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
return ds_audio_poll_common($req, 'speech');
},
));

// ── Download Audio ────────────────────────────────────────────────────
register_rest_route('ds/v1', '/download-audio', array(
'methods' => 'GET',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
$fid_param = (int) ($req->get_param('file_id') ?: 0);
$uid = get_current_user_id();

if ($fid_param && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_user_owns_file') && function_exists('ds_hub_get_download_url')) {
$f = ds_hub_get_generated_file($fid_param);
if (!$f || !ds_hub_user_owns_file($f, $uid)) { status_header(403); wp_die('Not your audio file.'); }
$resolved = ds_hub_get_download_url($f);
if (is_wp_error($resolved)) { status_header(404); wp_die('File not available.'); }
$url = esc_url_raw($resolved);
$owns_it = true;
} else {
$url = esc_url_raw($req->get_param('url') ?: '');
if (empty($url)) { status_header(400); wp_die('No URL'); }
$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
$owns_it = false;
foreach ($gens as $g) {
if (($g['url'] ?? '') === $url) { $owns_it = true; break; }
}
}
if (empty($url)) { status_header(400); wp_die('No URL'); }
if (!$owns_it) { status_header(403); wp_die('Not your audio file.'); }

$resp = wp_remote_get($url, array('timeout' => 100, 'sslverify' => true));
if (is_wp_error($resp)) { status_header(502); wp_die('Download failed: ' . $resp->get_error_message()); }
$code = wp_remote_retrieve_response_code($resp);
if ($code < 200 || $code >= 300) { status_header($code); wp_die('Download failed (HTTP ' . $code . ').'); }

$body = wp_remote_retrieve_body($resp);
$ct = wp_remote_retrieve_header($resp, 'content-type') ?: 'audio/mpeg';
nocache_headers();
header('Content-Type: ' . $ct);
header('Content-Disposition: attachment; filename="designstager-ai-audio-' . time() . '.mp3"');
header('Content-Length: ' . strlen($body));
echo $body;
exit;
},
));

// ── Rehost Audio (synchronous fallback for hosts where WP-Cron never
// fires) — same rationale as /rehost-video. ───────────────────────────
register_rest_route('ds/v1', '/rehost-audio', array(
'methods' => 'POST',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
@ignore_user_abort(true);
@set_time_limit(120);

$uid = get_current_user_id();
$pid = sanitize_text_field($req->get_param('prediction_id') ?: '');
if (!$pid) {
return new WP_Error('missing_pid', 'Missing prediction_id.', array('status' => 400));
}

$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
$entry = null;
foreach ($gens as $g) {
if (($g['prediction_id'] ?? '') === $pid) { $entry = $g; break; }
}
if (!$entry) {
return new WP_Error('not_found', 'Generation not found.', array('status' => 404));
}

if (!empty($entry['attachment_id']) || !empty($entry['file_id'])) {
return rest_ensure_response(array(
'success' => true,
'already_done' => true,
'attachment_id' => (int) $entry['attachment_id'],
'file_id' => (int) ($entry['file_id'] ?? 0),
'url' => $entry['url'] ?? '',
));
}

$remote_url = $entry['url'] ?? '';
$label = $entry['label'] ?? '';
if (empty($remote_url) || !function_exists('ds_audio_rehost_background')) {
return new WP_Error('cannot_rehost', 'Nothing to rehost.', array('status' => 400));
}

try {
ds_audio_rehost_background($uid, $pid, $remote_url, $label);
} catch (\Throwable $e) {
return new WP_Error('rehost_exception', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), array('status' => 500));
}

$gens2 = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
$aid = 0; $fid = 0; $url = $remote_url;
foreach ($gens2 as $g) {
if (($g['prediction_id'] ?? '') === $pid) {
$aid = (int) ($g['attachment_id'] ?? 0);
$fid = (int) ($g['file_id'] ?? 0);
$url = $g['url'] ?? $remote_url;
break;
}
}

return rest_ensure_response(array(
'success' => (bool) ($aid || $fid),
'attachment_id' => $aid,
'file_id' => $fid,
'url' => $url,
));
},
));

// ── Analyze Video (Background Music duration-matching) ────────────────
// The frontend NEVER inspects the uploaded video itself — it just posts
// the raw file here. All analysis happens server-side via WordPress
// core's bundled getID3 reader (wp_read_video_metadata). We only need
// the duration in seconds; the file is deleted immediately after and is
// never stored, uploaded to R2, or associated with the user's media.
register_rest_route('ds/v1', '/analyze-video', array(
'methods' => 'POST',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
@ignore_user_abort(true);
@set_time_limit(60);

if (empty($_FILES['video']) || empty($_FILES['video']['tmp_name']) || !is_uploaded_file($_FILES['video']['tmp_name'])) {
return rest_ensure_response(array('error' => true, 'message' => 'No video file received.'));
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$overrides = array(
'test_form' => false,
'mimes' => array(
'mp4' => 'video/mp4',
'mov|qt' => 'video/quicktime',
'webm' => 'video/webm',
'avi' => 'video/x-msvideo',
'ogv' => 'video/ogg',
'm4v' => 'video/x-m4v',
),
);

$moved = wp_handle_upload($_FILES['video'], $overrides);
if (empty($moved['file']) || !empty($moved['error'])) {
return rest_ensure_response(array('error' => true, 'message' => isset($moved['error']) ? $moved['error'] : 'Upload failed.'));
}

$duration = 0;
$meta = wp_read_video_metadata($moved['file']);
if (is_array($meta) && !empty($meta['length'])) {
$duration = (int) $meta['length'];
}

// Never keep the uploaded video — we only needed its duration.
@unlink($moved['file']);

if ($duration <= 0) {
return rest_ensure_response(array('error' => true, 'message' => 'Could not read the video\'s duration. You can still set the duration manually.'));
}

$duration = max(5, min(240, $duration));
return rest_ensure_response(array('success' => true, 'duration' => $duration));
},
));

// ── My Audio (unified list: music + background music + speech) ───────
register_rest_route( 'ds/v1', '/my-audio', array(
'methods' => 'GET',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function( WP_REST_Request $req ) {
$uid = get_current_user_id();
$kind_filter = sanitize_text_field($req->get_param('kind') ?: ''); // '', 'music', 'speech'
$gens = get_user_meta( $uid, 'ds_ai_audio_generations', true ) ?: array();
$out = array();
foreach ( $gens as $i => $gen ) {
if ( ! is_array( $gen ) ) continue;
if ( $kind_filter && ($gen['kind'] ?? '') !== $kind_filter ) continue;
$url = $gen['url'] ?? '';
$status = $gen['status'] ?? 'succeeded';
$file_id = (int) ( $gen['file_id'] ?? 0 );
$aid = (int) ( $gen['attachment_id'] ?? 0 );
if ( empty($url) && $status !== 'pending' ) continue;
$exp_badge = ''; $is_expired = false;
if ( $file_id && function_exists('ds_hub_get_generated_file') ) {
$f = ds_hub_get_generated_file( $file_id );
if ( $f ) {
if ( $f->status === 'saved' ) {
$exp_badge = 'saved';
} elseif ( function_exists('ds_hub_file_is_available') && ! ds_hub_file_is_available( $f ) ) {
$exp_badge = 'expired'; $is_expired = true;
} elseif ( $f->expires_at ) {
$exp_badge = 'temp';
}
}
}
if ( $is_expired ) continue;
$out[] = array(
'key' => $file_id ? ('f'.$file_id) : ('i'.$i),
'url' => $url,
'file_id' => $file_id,
'attachment_id' => $aid,
'status' => $status,
'kind' => $gen['kind'] ?? 'music',
'mode' => $gen['mode'] ?? '',
'label' => $gen['label'] ?? '',
'tags' => $gen['tags'] ?? '',
'voice_id' => $gen['voice_id'] ?? '',
'duration' => $gen['duration'] ?? 0,
'date' => $gen['date'] ?? '',
'exp_badge' => $exp_badge,
'is_expired' => $is_expired,
);
}
usort( $out, function( $a, $b ) { return strcmp( (string) $b['date'], (string) $a['date'] ); } );
$total = count( $out );
$offset = max( 0, (int) $req->get_param('offset') );
$limit = (int) $req->get_param('limit');
if ( $limit <= 0 ) $limit = 20;
$out = array_slice( $out, $offset, $limit );
return rest_ensure_response( array( 'success' => true, 'audio' => $out, 'total' => $total, 'next_offset' => $offset + count( $out ) ) );
},
) );

});

/* ── Shared poll handler for /poll-music and /poll-speech ─────────────
* Identical refund-once + async-rehost logic as DS AI Video REST's
* /poll-video, generalized across the 'kind' field.
*/
if ( ! function_exists('ds_audio_poll_common') ) {
function ds_audio_poll_common(WP_REST_Request $req, $kind) {
$pid = sanitize_text_field($req->get_param('prediction_id') ?: '');
if (empty($pid)) {
return rest_ensure_response(array('status' => 'error', 'error' => 'No task ID.'));
}
if (!function_exists('ds_poll_wavespeed_audio')) {
return rest_ensure_response(array('status' => 'error', 'error' => 'Poll function not available.'));
}

$uid = get_current_user_id();
$result = ds_poll_wavespeed_audio($pid);

// ── Refund credits if the job ultimately failed — ONCE per task ──
if (isset($result['status']) && $result['status'] === 'failed') {
$lock = 'ds_audio_refunded_' . md5((string)$pid);
if (add_option($lock, time(), '', 'no')) {
$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
$refund = 0;
foreach ($gens as $gi => $g) {
if (($g['prediction_id'] ?? '') === $pid) {
$refund = (int)($g['credits'] ?? 0);
$gens[$gi]['status'] = 'failed';
$gens[$gi]['error'] = $result['error'] ?? 'Generation failed.';
break;
}
}
update_user_meta($uid, 'ds_ai_audio_generations', $gens);
if ($refund > 0 && function_exists('ds_add_credits')) {
ds_add_credits($uid, $refund);
}
}
return rest_ensure_response($result);
}

if (!empty($result['audio'])) {
$label = sanitize_text_field($req->get_param('label') ?: '');

$already_done = false;
$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
foreach ($gens as $g) {
if (($g['prediction_id'] ?? '') === $pid && ($g['status'] ?? '') === 'succeeded') { $already_done = true; break; }
}

$audio_src = $result['audio'][0];

if (!$already_done && !empty($audio_src)) {
$found = false;
foreach ($gens as $gi => $g) {
if (($g['prediction_id'] ?? '') === $pid) {
$gens[$gi]['url'] = $audio_src;
$gens[$gi]['attachment_id'] = 0;
$gens[$gi]['status'] = 'succeeded';
$found = true;
$label = $label ?: ($g['label'] ?? '');
break;
}
}
if (!$found) {
array_unshift($gens, array(
'prediction_id' => $pid,
'kind' => $kind,
'url' => $audio_src,
'attachment_id' => 0,
'label' => $label,
'status' => 'succeeded',
'date' => current_time('Y-m-d H:i:s'),
));
}
update_user_meta($uid, 'ds_ai_audio_generations', array_slice($gens, 0, 200));

if (!wp_next_scheduled('ds_audio_rehost', array($uid, $pid, $audio_src, $label))) {
wp_schedule_single_event(time(), 'ds_audio_rehost', array($uid, $pid, $audio_src, $label));
}
}

return rest_ensure_response(array(
'status' => 'succeeded',
'audio' => array(array('url' => $audio_src, 'attachment_id' => 0)),
));
}

return rest_ensure_response($result); // status: processing
}
}
