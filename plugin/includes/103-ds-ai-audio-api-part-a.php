<?php
/**
 * Code Snippets export — DS AI Audio API - Part A
 *
 * Snippet ID : 784
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-098
 * Modified   : 2026-09-09 02:42:55
 * Load order : 103 of 144
 */
/**
* Snippet Name: DS AI Audio API - Part A
* Run: Everywhere
* Description: API caller functions for AI Audio generation (WaveSpeedAI /
* ACE-Step 1.5 for music + Background Music, MiniMax Speech 02 HD for
* Text to Speech). Mirrors the pattern used by "DS AI Video API - Part A"
* (ds_call_wavespeed_seedance / ds_poll_wavespeed) so Audio plugs into the
* exact same credit + REST pipeline as AI Video / AI Images.
*
* Fully standalone — does NOT read from or modify any existing snippet.
* Shares the existing WaveSpeed key (option: ds_wavespeed_api_key), the
* same one already used by AI Video, so no new Settings page is needed.
*
* Background Music is NOT a separate model — it is the same ACE-Step 1.5
* model as Generate Music, just with the structured Purpose/Mood/Style/
* Intensity/Instrumental inputs converted server-side into an ACE-Step
* tags/lyrics prompt, so users never need to know music terminology.
*/

if ( ! defined('ABSPATH') ) exit;

/* ── Shared WaveSpeed key (same account/option as AI Video) ──────────── */
if ( ! function_exists('ds_audio_get_wavespeed_key') ) {
function ds_audio_get_wavespeed_key() {
if ( function_exists('ds_video_get_wavespeed_key') ) return ds_video_get_wavespeed_key();
return get_option('ds_wavespeed_api_key', '');
}
}

/* ── Credit cost tables ───────────────────────────────────────────────
* Music (ACE-Step 1.5): priced per 30s block of requested duration.
* Speech (MiniMax Speech 02 HD): priced per ~500 characters of text.
* Both admin-configurable via option 'ds_audio_credit_settings', same
* pattern as ds_video_credit_settings.
*/
if ( ! function_exists('ds_get_default_audio_model_costs') ) {
function ds_get_default_audio_model_costs() {
return array(
'ace-step-1.5' => 6, // credits per 30s block
'speech-02-hd' => 4, // credits per 500 characters
);
}
}

if ( ! function_exists('ds_get_audio_model_costs') ) {
function ds_get_audio_model_costs() {
$opt = get_option('ds_audio_credit_settings');
if ( is_array($opt) && isset($opt['models']) && is_array($opt['models']) && !empty($opt['models']) ) {
return $opt['models'];
}
return ds_get_default_audio_model_costs();
}
}

if ( ! function_exists('ds_get_audio_platform_charge') ) {
function ds_get_audio_platform_charge() {
$opt = get_option('ds_audio_credit_settings');
if ( is_array($opt) && isset($opt['platform_charge']) && $opt['platform_charge'] !== '' ) {
return max(0, (int)$opt['platform_charge']);
}
return 1;
}
}

/* -- Music/Background Music: 3 duration tiers (credits BEFORE platform charge).
 * Fixed thresholds: <=80s / <=160s / <=240s. Per-tier amount is
 * admin-configurable via option 'ds_audio_credit_settings'['music_tiers'].
 */
if ( ! function_exists('ds_get_audio_music_tiers') ) {
function ds_get_audio_music_tiers() {
$opt = get_option('ds_audio_credit_settings');
if ( is_array($opt) && isset($opt['music_tiers']) && is_array($opt['music_tiers']) && count($opt['music_tiers']) === 3 ) {
return array_map('intval', array_values($opt['music_tiers']));
}
return array(1, 2, 3);
}
}

/* -- Text to Speech: 4 character-count tiers (credits, TOTAL -- the
 * platform charge is NOT added on top of these). Admin-configurable via
 * 'ds_audio_credit_settings'['speech_tiers'].
 */
if ( ! function_exists('ds_get_audio_speech_tiers') ) {
function ds_get_audio_speech_tiers() {
$opt = get_option('ds_audio_credit_settings');
if ( is_array($opt) && isset($opt['speech_tiers']) && is_array($opt['speech_tiers']) && count($opt['speech_tiers']) === 4 ) {
return array_map('intval', array_values($opt['speech_tiers']));
}
return array(2, 4, 8, 12);
}
}

if ( ! function_exists('ds_get_music_cost') ) {
function ds_get_music_cost($duration = 60) {
$d = max(5, (int)$duration);
$tiers = ds_get_audio_music_tiers();
if ($d <= 80) { $base = $tiers[0]; } elseif ($d <= 160) { $base = $tiers[1]; } else { $base = $tiers[2]; }
$cost = $base + ds_get_audio_platform_charge();
return (int) apply_filters('ds_music_cost_calculated', max(1, (int)$cost), $duration);
}
}

if ( ! function_exists('ds_get_speech_cost') ) {
function ds_get_speech_cost($text) {
$chars = max(1, strlen((string)$text));
$tiers = ds_get_audio_speech_tiers();
if ($chars <= 500) { $cost = $tiers[0]; } elseif ($chars <= 2000) { $cost = $tiers[1]; } elseif ($chars <= 5000) { $cost = $tiers[2]; } else { $cost = $tiers[3]; }
return (int) apply_filters('ds_speech_cost_calculated', max(1, (int)$cost), $text);
}
}

/* ── Background Music: convert structured, non-technical inputs into an
* ACE-Step tags/lyrics prompt. Users pick Purpose/Mood/Style/Intensity —
* never see the word "prompt" or genre-tag syntax.
*/
if ( ! function_exists('ds_build_bgmusic_tags') ) {
function ds_build_bgmusic_tags($purpose = '', $mood = '', $style = '', $intensity = 'medium') {
$purpose_map = array(
'vlog' => 'upbeat, background, youtube vlog',
'corporate' => 'corporate, professional, clean',
'podcast' => 'soft, ambient, podcast intro',
'gaming' => 'energetic, electronic, gaming',
'wedding' => 'romantic, cinematic, wedding',
'social' => 'trendy, catchy, social media',
'presentation' => 'minimal, ambient, presentation',
'documentary' => 'cinematic, atmospheric, documentary',
);
$mood_map = array(
'uplifting' => 'uplifting, positive, bright',
'calm' => 'calm, relaxing, peaceful',
'energetic' => 'energetic, driving, motivational',
'emotional' => 'emotional, heartfelt, warm',
'dramatic' => 'dramatic, tense, cinematic',
'happy' => 'happy, cheerful, playful',
'mysterious' => 'mysterious, dark, moody',
);
$style_map = array(
'acoustic' => 'acoustic guitar, folk',
'electronic' => 'electronic, synth',
'cinematic' => 'orchestral, cinematic strings',
'lofi' => 'lo-fi, chill, mellow beats',
'pop' => 'pop, modern',
'jazz' => 'jazz, smooth, piano',
'ambient' => 'ambient, atmospheric, pads',
'rock' => 'rock, guitar driven',
);
$intensity_map = array(
'low' => 'soft, minimal, subtle',
'medium' => 'moderate energy, balanced',
'high' => 'intense, powerful, driving',
);

$parts = array();
if (isset($purpose_map[$purpose])) $parts[] = $purpose_map[$purpose];
if (isset($mood_map[$mood])) $parts[] = $mood_map[$mood];
if (isset($style_map[$style])) $parts[] = $style_map[$style];
if (isset($intensity_map[$intensity])) $parts[] = $intensity_map[$intensity];
if (empty($parts)) $parts[] = 'background music, instrumental';

$tags = implode(', ', array_unique(array_filter($parts)));
return apply_filters('ds_bgmusic_tags_built', $tags, $purpose, $mood, $style, $intensity);
}
}

/* ── Background Music: tiny generic lyric snippets used ONLY when the
* user flips the Background Music "Vocals" toggle on (instrumental=false).
* Users never type lyrics for Background Music, so we generate a short,
* generic, mood-flavored lyric snippet server-side instead of forcing
* silence — keeps the "Vocals" toggle meaningful without exposing any
* music/lyric-writing UI to the user.
*/
if ( ! function_exists('ds_build_bgmusic_lyrics') ) {
function ds_build_bgmusic_lyrics($mood = '', $purpose = '') {
$snippets = array(
'uplifting' => "[verse]\nRising up into the light\nEverything's gonna be alright\n[chorus]\nWe keep moving, we keep climbing higher",
'calm' => "[verse]\nSlow and easy, drifting by\nQuiet moments, open sky\n[chorus]\nLet it breathe, let it flow",
'energetic' => "[verse]\nFeel the beat, feel it now\nEvery step, we show them how\n[chorus]\nUp, up, we go tonight",
'emotional' => "[verse]\nHolding on to what we know\nGentle hearts begin to glow\n[chorus]\nThis is where we belong",
'dramatic' => "[verse]\nShadows fall, the tension grows\nEvery moment, no one knows\n[chorus]\nHold your breath, here it comes",
'happy' => "[verse]\nSunshine days and easy smiles\nDancing on for miles and miles\n[chorus]\nOoh, we feel so free today",
'mysterious' => "[verse]\nWhispers move through the dark unknown\nSecrets kept, we walk alone\n[chorus]\nSomething's waiting just out of sight",
);
if (isset($snippets[$mood])) return $snippets[$mood];
return "[verse]\nGentle sound behind the scene\nSetting the mood, calm and clean\n[chorus]\nLet the music carry on";
}
}

/* ── Submit an ACE-Step 1.5 music generation task ─────────────────────
* Used by both Generate Music (free-form $tags/$lyrics from the user)
* and Background Music (structured $tags built above, $instrumental
* forces "[inst]" lyrics so no vocals are generated).
*/
if ( ! function_exists('ds_call_wavespeed_acestep') ) {
function ds_call_wavespeed_acestep($tags, $lyrics = '', $duration = 60, $instrumental = false) {
$key = ds_audio_get_wavespeed_key();
if (empty($key)) return array('error' => 'WaveSpeed API key not set. Add it on Settings -> AI Video (WaveSpeed) — the same key is shared by AI Audio.');

$tags = trim((string)$tags);
if (empty($tags)) $tags = 'background music, instrumental';

$lyrics = trim((string)$lyrics);
if ($instrumental || $lyrics === '') $lyrics = '[inst]';

$payload = array(
'tags' => $tags,
'lyrics' => $lyrics,
'duration' => max(5, min(240, (int)$duration)),
'seed' => -1,
);

$resp = wp_remote_post('https://api.wavespeed.ai/api/v3/wavespeed-ai/ace-step-1.5', array(
'headers' => array('Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'),
'body' => wp_json_encode($payload),
'timeout' => 30,
));

if (is_wp_error($resp)) return array('error' => 'WaveSpeed: ' . $resp->get_error_message());
$d = json_decode(wp_remote_retrieve_body($resp), true);

if (!empty($d['data']['id'])) return array('prediction_id' => $d['data']['id']);
return array('error' => 'WaveSpeed: ' . (isset($d['message']) ? $d['message'] : wp_json_encode($d)));
}
}

/* ── Submit a MiniMax Speech 02 HD text-to-speech task ────────────────── */
if ( ! function_exists('ds_call_wavespeed_speech') ) {
function ds_call_wavespeed_speech($text, $voice_id = 'Friendly_Person', $speed = 1.0, $emotion = 'neutral', $format = 'mp3') {
$key = ds_audio_get_wavespeed_key();
if (empty($key)) return array('error' => 'WaveSpeed API key not set. Add it on Settings -> AI Video (WaveSpeed) — the same key is shared by AI Audio.');

$text = trim((string)$text);
if (empty($text)) return array('error' => 'Text is required.');
if (strlen($text) > 10000) $text = substr($text, 0, 10000);

$valid_formats = array('mp3', 'wav', 'pcm', 'flac');
if (!in_array($format, $valid_formats, true)) $format = 'mp3';

$payload = array(
'text' => $text,
'voice_id' => sanitize_text_field($voice_id ?: 'Friendly_Person'),
'speed' => max(0.5, min(2.0, (float)$speed)),
'volume' => 1,
'pitch' => 0,
'emotion' => sanitize_text_field($emotion ?: 'neutral'),
'format' => $format,
);

$resp = wp_remote_post('https://api.wavespeed.ai/api/v3/minimax/speech-02-hd', array(
'headers' => array('Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'),
'body' => wp_json_encode($payload),
'timeout' => 30,
));

if (is_wp_error($resp)) return array('error' => 'WaveSpeed: ' . $resp->get_error_message());
$d = json_decode(wp_remote_retrieve_body($resp), true);

if (!empty($d['data']['id'])) return array('prediction_id' => $d['data']['id']);
return array('error' => 'WaveSpeed: ' . (isset($d['message']) ? $d['message'] : wp_json_encode($d)));
}
}

/* ── Poll a WaveSpeed audio task (music or speech) for its result ─────
* Same envelope shape as ds_poll_wavespeed (video) — 'outputs' holds
* audio URLs instead of video URLs when status is "completed".
*/
if ( ! function_exists('ds_poll_wavespeed_audio') ) {
function ds_poll_wavespeed_audio($pid) {
$key = ds_audio_get_wavespeed_key();
$resp = wp_remote_get(
'https://api.wavespeed.ai/api/v3/predictions/' . rawurlencode($pid) . '/result',
array('headers' => array('Authorization' => 'Bearer ' . $key), 'timeout' => 20)
);
if (is_wp_error($resp)) return array('status' => 'error', 'error' => $resp->get_error_message());

$d = json_decode(wp_remote_retrieve_body($resp), true);
$data = isset($d['data']) ? $d['data'] : array();
$s = isset($data['status']) ? $data['status'] : 'unknown';

if ($s === 'completed') {
$out = isset($data['outputs']) ? array_values((array)$data['outputs']) : array();
return array('status' => 'succeeded', 'audio' => $out);
}
if ($s === 'failed' || $s === 'cancelled' || $s === 'timeout') {
return array('status' => 'failed', 'error' => isset($data['error']) ? $data['error'] : 'Generation failed.');
}
return array('status' => 'processing');
}
}

/* ── Download the finished audio and persist it (R2 first, local
* wp-content/uploads fallback) — mirrors ds_save_video_to_uploads exactly.
*/
if ( ! function_exists('ds_save_audio_to_uploads') ) {
/**
* array('url' => <string>, 'attachment_id' => <int, 0 on failure>, 'file_id' => <int, optional>)
*/
function ds_save_audio_to_uploads($src, $label = 'ai', $extension = 'mp3') {
$upload = wp_upload_dir();
$slug = sanitize_title(substr($label, 0, 30));
if (!$slug) $slug = 'audio';
$extension = preg_match('/^[a-z0-9]{2,5}$/i', $extension) ? strtolower($extension) : 'mp3';
$fname = 'ds-ai-audio-' . $slug . '-' . time() . '-' . uniqid('', true) . '.' . $extension;
$fpath = $upload['path'] . '/' . $fname;

$resp = wp_remote_get($src, array('timeout' => 120, 'sslverify' => false, 'stream' => true, 'filename' => $fpath));
if (is_wp_error($resp) || !file_exists($fpath) || filesize($fpath) < 500) {
if (file_exists($fpath)) @unlink($fpath);
return array('url' => $src, 'attachment_id' => 0);
}

$mime_map = array('mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'flac' => 'audio/flac', 'pcm' => 'audio/L16');
$mime = isset($mime_map[$extension]) ? $mime_map[$extension] : 'audio/mpeg';

if ( function_exists('ds_hub_upload_generated_bytes') ) {
$bytes = file_get_contents($fpath);
if ($bytes !== false && strlen($bytes) >= 500) {
$up = ds_hub_upload_generated_bytes($bytes, array(
'file_type' => 'audio',
'source' => 'generated',
'extension' => $extension,
'mime_type' => $mime,
'meta' => array('label' => $label),
));
unset($bytes);
if (!is_wp_error($up) && !empty($up['url'])) {
@unlink($fpath);
return array('url' => $up['url'], 'attachment_id' => 0, 'file_id' => $up['id']);
}
$err = is_wp_error($up) ? $up->get_error_message() : 'unknown error';
if (function_exists('ds_hub_log')) ds_hub_log('audio_upload_fallback_local', $err);
error_log('[DS] R2 upload failed for generated audio, falling back to local disk: ' . $err);
}
}

$url = $upload['url'] . '/' . $fname;
$att = array(
'post_mime_type' => $mime,
'post_title' => sanitize_text_field(substr($label, 0, 60)),
'post_content' => '',
'post_status' => 'inherit',
);
$aid = wp_insert_attachment($att, $fpath);
if (!is_wp_error($aid)) {
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
$meta = wp_generate_attachment_metadata($aid, $fpath);
wp_update_attachment_metadata($aid, $meta);
update_post_meta($aid, 'ds_ai_label', $label);
} else {
$aid = 0;
}

return array('url' => $url, 'attachment_id' => $aid);
}
}

/* ── Background rehost (called from /ds/v1/rehost-audio) ─────────────
* Downloads the WaveSpeed-hosted audio into R2/Media Library and writes
* the resulting file_id/attachment_id back into the matching
* ds_ai_audio_generations entry. Mirrors ds_video_rehost_background.
*/
if ( ! function_exists('ds_audio_rehost_background') ) {
add_action('ds_audio_rehost', 'ds_audio_rehost_background', 10, 4);
function ds_audio_rehost_background($uid, $pid, $remote_url, $label) {
// FIX 2026-09-23: runs in WP-Cron with no logged-in user -- act as the owner so the file is not saved under user_id 0.
if ( (int) $uid > 0 ) { wp_set_current_user( (int) $uid ); }
$saved = ds_save_audio_to_uploads($remote_url, $label, 'mp3');
if (empty($saved['attachment_id']) && empty($saved['file_id'])) return;

$gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: array();
foreach ($gens as $gi => $g) {
if (($g['prediction_id'] ?? '') === $pid) {
$gens[$gi]['url'] = $saved['url'];
$gens[$gi]['attachment_id'] = $saved['attachment_id'];
$gens[$gi]['file_id'] = $saved['file_id'] ?? 0;
break;
}
}
update_user_meta($uid, 'ds_ai_audio_generations', $gens);
}
}

/* FIX 2026-09-23: one-time repair -- give audio saved under user_id 0 back to their owners. */
add_action( 'admin_init', function () {
	if ( get_option( 'ds_audio_owner_repair_v1', false ) !== false ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	global $wpdb;
	$t = $wpdb->prefix . 'ds_generated_files';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) return;
	$rows  = $wpdb->get_results( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'ds_ai_audio_generations'" );
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
	update_option( 'ds_audio_owner_repair_v1', (string) $fixed, false );
} );
