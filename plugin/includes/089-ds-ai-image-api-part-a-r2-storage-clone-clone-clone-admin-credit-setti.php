<?php
/**
 * Code Snippets export — DS AI Image API - Part A (R2 Storage) [CLONE] [CLONE] [CLONE] [Admin Credit Settings] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE]
 *
 * Snippet ID : 770
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-084
 * Modified   : 2026-09-09 02:44:01
 * Load order : 089 of 144
 */
/**
 * Snippet Name: DS AI Image API - Part A (R2 Storage)
 * Run: Everywhere
 * Description: API caller functions for AI image generation. Clone of the
 * original "DS AI Image API - Part A" — only ds_save_image_to_uploads() was
 * changed, to upload generated images to Cloudflare R2 (with automatic
 * retention) instead of local disk, falling back to local disk if R2 is not
 * configured or the upload fails. Requires "DS Storage & API Settings (R2 +
 * Retention)" to be active. The original snippet is kept (deactivated) as a
 * rollback backup — do not delete it.
 */

if (!function_exists('ds_get_api_keys')) {
function ds_get_api_keys() {
return array(
'replicate' => ds_get_key('replicate'),
'openai' => ds_get_key('openai'),
'huggingface' => ds_get_key('huggingface'),
'cloudflare_account_id' => ds_get_key('cloudflare_account_id'),
'cloudflare_token' => ds_get_key('cloudflare_token'),
'groq' => ds_get_key('groq'),
'gemini' => ds_get_key('gemini'),
'openrouter' => ds_get_key('openrouter'),
);
}
}

if (!function_exists('ds_get_default_model_costs')) {
function ds_get_default_model_costs() {
return array(
'cloudflare' => 2,
'cloudflare-klein' => 3,
'hidream-fast' => 4,
'flux-schnell' => 4,
'flux-dev' => 7,
'qwen-image' => 8,
'qwen-edit' => 10,
'seedream' => 8,
'flux-pro' => 10,
'nanobana' => 13,
);
}
}
if (!function_exists('ds_get_model_costs')) {
function ds_get_model_costs() {
// Admin-configurable via DS Credit Settings (option 'ds_image_credit_settings').
$opt = get_option('ds_image_credit_settings');
if ( is_array($opt) && isset($opt['models']) && is_array($opt['models']) && !empty($opt['models']) ) {
return $opt['models'];
}
return ds_get_default_model_costs();
}
}
if (!function_exists('ds_get_model_cost_each')) {
function ds_get_model_cost_each($model, $size = '') {
$costs = ds_get_model_costs();
$base = isset($costs[$model]) ? $costs[$model] : 4;
$opt = get_option('ds_image_credit_settings');
$overrides = ( is_array($opt) && isset($opt['resolution_overrides']) && is_array($opt['resolution_overrides']) ) ? $opt['resolution_overrides'] : array();
$size_key = strtoupper((string)$size);
if ( isset($overrides[$model][$size_key]) && $overrides[$model][$size_key] !== '' ) {
return (int) $overrides[$model][$size_key];
}
// Fallback default (only used before the admin page has ever been saved):
// NanoBanana 2 at 4K costs more to run — surcharge on top of base cost.
if ( empty($opt) && $model === 'nanobana' && $size_key === '4K' ) {
return 19;
}
return $base;
}
}

if (!function_exists('ds_get_credits')) {
function ds_get_credits($uid) {
$c = (int)get_user_meta($uid, 'ds_ai_credits', true);
if ($c === 0 && !metadata_exists('user', $uid, 'ds_ai_credits')) {
update_user_meta($uid, 'ds_ai_credits', 10);
return 10;
}
return $c;
}
}

if (!function_exists('ds_deduct_credits')) {
function ds_deduct_credits($uid, $amount) {
$cur = (int)get_user_meta($uid, 'ds_ai_credits', true);
update_user_meta($uid, 'ds_ai_credits', max(0, $cur - $amount));
}
}

if (!function_exists('ds_add_credits')) {
function ds_add_credits($uid, $amount) {
$cur = (int)get_user_meta($uid, 'ds_ai_credits', true);
update_user_meta($uid, 'ds_ai_credits', $cur + $amount);
}
}

if (!function_exists('ds_get_size_long_edge')) {
function ds_get_size_long_edge($size) {
$map = array('HD'=>1280,'FHD'=>1920,'2K'=>2048,'3K'=>3072,'4K'=>4096);
return isset($map[strtoupper($size)]) ? $map[strtoupper($size)] : 2048;
}
}

if (!function_exists('ds_get_dims')) {
function ds_get_dims($ratio, $size = '2K') {
$long = ds_get_size_long_edge($size);
$ratios = array(
'1:1'=>array(1,1),'16:9'=>array(16,9),'9:16'=>array(9,16),
'4:3'=>array(4,3),'3:4'=>array(3,4),'3:2'=>array(3,2),
'2:3'=>array(2,3),'21:9'=>array(21,9),
);
$rv = isset($ratios[$ratio]) ? $ratios[$ratio] : array(1,1);
$rw = $rv[0]; $rh = $rv[1];
if ($rw >= $rh) { $w=$long; $h=(int)round($long*$rh/$rw); }
else { $h=$long; $w=(int)round($long*$rw/$rh); }
$w = max(512,(int)round($w/8)*8);
$h = max(512,(int)round($h/8)*8);
return array('width'=>$w,'height'=>$h,'size'=>strtoupper($size),'long_edge'=>$long);
}
}

if (!function_exists('ds_dims_to_aspect')) {
function ds_dims_to_aspect($dims) {
$r = $dims['width'] / max(1, $dims['height']);
if (abs($r-1) < 0.05) return '1:1';
if ($r > 2.0) return '21:9';
if ($r > 1.5) return '16:9';
if ($r > 1.2) return '4:3';
if ($r > 1.0) return '3:2';
if ($r < 0.5) return '9:16';
if ($r < 0.7) return '2:3';
return '3:4';
}
}

if (!function_exists('ds_snap64')) {
function ds_snap64($v) { return max(512, min(4096, (int)round($v/64)*64)); }
}
if (!function_exists('ds_nb_resolution')) {
function ds_nb_resolution($size) {
$s = strtoupper($size);
if ($s === '4K') return '4K';
if ($s === '3K' || $s === '2K') return '2K';
return '1K';
}
}

if (!function_exists('ds_crop_to_aspect_ratio')) {
	function ds_crop_to_aspect_ratio($image_data, $target_w, $target_h) {
		if (!function_exists('imagecreatefromstring') || empty($target_w) || empty($target_h)) return $image_data;
		$im = @imagecreatefromstring($image_data);
		if (!$im) return $image_data;
		$sw = imagesx($im);
		$sh = imagesy($im);
		if ($sw < 2 || $sh < 2) { imagedestroy($im); return $image_data; }
		$target_ratio = $target_w / $target_h;
		$src_ratio = $sw / $sh;
		// Already close enough to the target ratio — skip cropping.
		if (abs($src_ratio - $target_ratio) < 0.02) { imagedestroy($im); return $image_data; }
		if ($src_ratio > $target_ratio) {
			// Source is relatively wider than target — crop left/right.
			$ch = $sh;
			$cw = max(1, (int) round($sh * $target_ratio));
			$cx = (int) round(($sw - $cw) / 2);
			$cy = 0;
		} else {
			// Source is relatively taller than target — crop top/bottom.
			$cw = $sw;
			$ch = max(1, (int) round($sw / $target_ratio));
			$cx = 0;
			$cy = (int) round(($sh - $ch) / 2);
		}
		$dst = imagecreatetruecolor($cw, $ch);
		imagecopy($dst, $im, 0, 0, $cx, $cy, $cw, $ch);
		ob_start();
		imagepng($dst, null, 0);
		$cropped = ob_get_clean();
		imagedestroy($im);
		imagedestroy($dst);
		return (!empty($cropped) && strlen($cropped) > 200) ? $cropped : $image_data;
	}
}

if (!function_exists('ds_resize_square')) {
	function ds_resize_square($image_data, $target) {
		if (!function_exists('imagecreatefromstring') || empty($target)) return $image_data;
		$im = @imagecreatefromstring($image_data);
		if (!$im) return $image_data;
		$sw = imagesx($im);
		$sh = imagesy($im);
		if ($sw < 2 || $sh < 2) { imagedestroy($im); return $image_data; }
		if ($sw === (int)$target && $sh === (int)$target) { imagedestroy($im); return $image_data; }
		$dst = imagecreatetruecolor((int)$target, (int)$target);
		imagecopyresampled($dst, $im, 0, 0, 0, 0, (int)$target, (int)$target, $sw, $sh);
		ob_start();
		imagepng($dst, null, 0);
		$out = ob_get_clean();
		imagedestroy($im);
		imagedestroy($dst);
		return (!empty($out) && strlen($out) > 200) ? $out : $image_data;
	}
}

if (!function_exists('ds_call_replicate_upscaler')) {
	// Was google/upscaler (Replicate's proxy to Google Vertex AI's
	// imagen-4.0-upscale-preview), which consistently 404s server-side —
	// that's Replicate's own backend failing to reach Google's preview
	// endpoint, not something fixable from our side. Switched to
	// nightmareai/real-esrgan: mature (97M+ runs), cheaper ($0.002 vs
	// $0.02/image), and doesn't depend on that broken preview integration.
	function ds_call_replicate_upscaler($image_data, $scale = 4) {
		$keys = ds_get_api_keys();
		$key = isset($keys['replicate']) ? $keys['replicate'] : '';
		if (empty($key) || strpos($key,'YOUR_') === 0) return array('error'=>'Replicate API key not set (needed for 4K upscaling).');
		$data_uri = 'data:image/png;base64,' . base64_encode($image_data);
		$resp = wp_remote_post(
			'https://api.replicate.com/v1/models/nightmareai/real-esrgan/predictions',
			array(
				'headers' => array('Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json','Prefer'=>'wait=55'),
				'body' => json_encode(array('input'=>array(
					'image' => $data_uri,
					'scale' => $scale,
					'face_enhance' => false,
				))),
				'timeout' => 60,
			)
		);
		if (is_wp_error($resp)) return array('error'=>'Upscaler: '.$resp->get_error_message());
		$d = json_decode(wp_remote_retrieve_body($resp), true);
		if (isset($d['status']) && $d['status'] === 'succeeded' && !empty($d['output'])) {
			$out_url = is_array($d['output']) ? ($d['output'][0] ?? '') : $d['output'];
			if (empty($out_url)) return array('error'=>'Upscaler: no output returned.');
			$img_resp = wp_remote_get($out_url, array('timeout'=>60,'sslverify'=>true));
			if (is_wp_error($img_resp)) return array('error'=>'Upscaler: failed to fetch upscaled result.');
			$bytes = wp_remote_retrieve_body($img_resp);
			if (empty($bytes) || strlen($bytes) < 500) return array('error'=>'Upscaler: empty result.');
			return array('bytes'=>$bytes);
		}
		$detail = isset($d['detail']) ? $d['detail'] : (isset($d['error']) ? (is_string($d['error'])?$d['error']:json_encode($d['error'])) : json_encode($d));
		return array('error'=>'Upscaler: '.$detail);
	}
}

if (!function_exists('ds_call_cloudflare')) {
	function ds_call_cloudflare($prompt, $dims, $count = 1, $image_size = 'HD', $free_tier = false) {
		$keys = ds_get_api_keys();
		$acct = $keys['cloudflare_account_id'];
		$tok = $keys['cloudflare_token'];
		if (strpos($acct,'YOUR_') === 0) return array('error'=>'Cloudflare Account ID not set.');
		$url = 'https://api.cloudflare.com/client/v4/accounts/' . $acct . '/ai/run/@cf/black-forest-labs/flux-1-schnell';
		// 4K upscaling is a paid-only feature — even if a size string of '4K'
		// somehow reaches us for a free-tier user, treat it as not-4K here too
		// (defense in depth; the REST layer and frontend also block this).
		$is_4k = (strtoupper((string)$image_size) === '4K') && !$free_tier;
		$images = array();
		for ($i = 0; $i < min($count, 4); $i++) {
			$resp = wp_remote_post($url, array(
				'headers' => array('Authorization'=>'Bearer '.$tok,'Content-Type'=>'application/json'),
				'body' => json_encode(array('prompt'=>$prompt, 'steps'=>4)), // FLUX-1-schnell only accepts prompt+steps; width/height/num_steps are rejected by Cloudflare's schema
				'timeout' => 90,
			));
			if (is_wp_error($resp)) return array('error'=>'Cloudflare: '.$resp->get_error_message());
			$body = wp_remote_retrieve_body($resp);
			$ct = wp_remote_retrieve_header($resp,'content-type');
			// Get the raw square image bytes regardless of which shape Cloudflare responded with.
			$raw = null;
			if (strpos($ct,'image') !== false) {
				$raw = $body;
			} else {
				$d = json_decode($body, true);
				if (isset($d['result']['image'])) {
					$raw = base64_decode($d['result']['image']);
				} elseif (isset($d['errors'])) {
					return array('error'=>'Cloudflare: '.json_encode($d['errors']));
				}
			}
			if (empty($raw)) continue;

			// Cloudflare always returns a fixed 1024x1024 square for this model
			// (no width/height/aspect_ratio input exists on their schema). We
			// shape it ourselves after the fact, differently per plan/tier:
			//  - Free tier: downscale the square to 600x600 first, then crop to
			//    the requested ratio — small and cheap.
			//  - Paid tier, default (non-4K): crop the FULL native 1024x1024
			//    square straight to the requested ratio — no downscale, full
			//    source quality.
			//  - Paid tier, 4K option: crop the native square to the requested
			//    ratio, then send it through Replicate's google/upscaler (x4) to
			//    reach a true ~4096px-long-edge result.
			if ($free_tier) {
			// Free tier now gets the same full-quality 1024-based crop as the
			// default paid tier (just without the 4K upscale option below) --
			// previously this downscaled to 600x600 first, giving small
			// 600-based outputs (e.g. 600x338 for 16:9). Now it's 1024x576.
			$final = ds_crop_to_aspect_ratio($raw, $dims['width'], $dims['height']);
				} elseif ($is_4k) {
			// NOTE: there used to be a duplicated empty "elseif ($is_4k) {}" ahead of
			// this one. Every 4K request fell into that empty block, $final was never
			// assigned, and base64_encode($final) produced "data:image/png;base64,"
			// with no data - a charged generation that returned a broken image.
				$cropped = ds_crop_to_aspect_ratio($raw, $dims['width'], $dims['height']);
				$up = ds_call_replicate_upscaler($cropped, 4);
				if (!empty($up['error'])) return array('error'=>$up['error']);
				$final = $up['bytes'];
			} else {
				$final = ds_crop_to_aspect_ratio($raw, $dims['width'], $dims['height']);
			}
			$images[] = 'data:image/png;base64,'.base64_encode($final);
		}
		if (empty($images)) return array('error'=>'Cloudflare: No image returned.');
		return array('images'=>$images);
	}
}

if (!function_exists('ds_call_cloudflare_klein')) {
function ds_call_cloudflare_klein($prompt, $dims, $count = 1, $ref_image = null, $ref_strength = 0.75, $image_size = 'HD', $free_tier = false) {
$keys = ds_get_api_keys();
$acct = $keys['cloudflare_account_id'];
$tok = $keys['cloudflare_token'];
if (strpos($acct,'YOUR_') === 0) return array('error'=>'Cloudflare Account ID not set.');

$cf_max = 1440.0;
$cf_long = max($dims['width'], $dims['height']);
$cf_scale = $cf_long > $cf_max ? ($cf_max / $cf_long) : 1.0;
$w = ds_snap64((int)round($dims['width'] * $cf_scale));
$h = ds_snap64((int)round($dims['height'] * $cf_scale));

$url = 'https://api.cloudflare.com/client/v4/accounts/' . $acct . '/ai/run/@cf/black-forest-labs/flux-2-klein-9b';
$images = array();

for ($i = 0; $i < min($count, 4); $i++) {
// Build multipart form data manually
$boundary = '----CFBoundary' . uniqid();
$body = '';

// prompt field
$body .= '--' . $boundary . "\r\n";
$body .= 'Content-Disposition: form-data; name="prompt"' . "\r\n\r\n";
$body .= $prompt . "\r\n";

// width field
$body .= '--' . $boundary . "\r\n";
$body .= 'Content-Disposition: form-data; name="width"' . "\r\n\r\n";
$body .= $w . "\r\n";

// height field
$body .= '--' . $boundary . "\r\n";
$body .= 'Content-Disposition: form-data; name="height"' . "\r\n\r\n";
$body .= $h . "\r\n";

// seed field
$body .= '--' . $boundary . "\r\n";
$body .= 'Content-Disposition: form-data; name="seed"' . "\r\n\r\n";
$body .= mt_rand(1, 2147483647) . "\r\n";

// reference image (optional)
if (!empty($ref_image) && strpos($ref_image, 'data:image') === 0) {
$parts = explode(',', $ref_image, 2);
$img_data = base64_decode($parts[1]);
$body .= '--' . $boundary . "\r\n";
$body .= 'Content-Disposition: form-data; name="input_image_0"; filename="ref.png"' . "\r\n";
$body .= 'Content-Type: image/png' . "\r\n\r\n";
$body .= $img_data . "\r\n";
}

$body .= '--' . $boundary . '--' . "\r\n";

$resp = wp_remote_post($url, array(
'headers' => array(
'Authorization' => 'Bearer ' . $tok,
'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
),
'body' => $body,
'timeout' => 120,
));

if (is_wp_error($resp)) return array('error' => 'Cloudflare Klein: ' . $resp->get_error_message());

$code = wp_remote_retrieve_response_code($resp);
$body_resp = wp_remote_retrieve_body($resp);
$ct = wp_remote_retrieve_header($resp, 'content-type');

if (strpos($ct, 'image') !== false) {
$ds_klein_raw = $body_resp;
} else {
$d = json_decode($body_resp, true);
if (isset($d['result']['image'])) {
$ds_klein_raw = base64_decode($d['result']['image']);
} else {
return array('error' => 'Cloudflare Klein HTTP ' . $code . ': ' . substr($body_resp, 0, 300));
}
}
if (strtoupper((string)$image_size) === '4K' && !$free_tier && function_exists('ds_crop_to_aspect_ratio') && function_exists('ds_call_replicate_upscaler')) {
$ds_klein_cropped = ds_crop_to_aspect_ratio($ds_klein_raw, $dims['width'], $dims['height']);
$ds_klein_up = ds_call_replicate_upscaler($ds_klein_cropped, 4);
if (!empty($ds_klein_up['error'])) return array('error' => $ds_klein_up['error']);
$ds_klein_raw = $ds_klein_up['bytes'];
}
$images[] = 'data:image/png;base64,' . base64_encode($ds_klein_raw);
}

if (empty($images)) return array('error' => 'Cloudflare Klein: No image returned.');
return array('images' => $images);
}
}

if (!function_exists('ds_upscale_image_urls_to_4k')) {
function ds_upscale_image_urls_to_4k($urls, $dims) {
$out = array();
foreach ($urls as $u) {
if (empty($u)) continue;
$img_resp = wp_remote_get($u, array('timeout'=>60,'sslverify'=>true));
if (is_wp_error($img_resp)) return array('error'=>'4K upscale: failed to fetch source image.');
$raw = wp_remote_retrieve_body($img_resp);
if (empty($raw) || strlen($raw) < 500) return array('error'=>'4K upscale: empty source image.');
$cropped = ds_crop_to_aspect_ratio($raw, $dims['width'], $dims['height']);
$up = ds_call_replicate_upscaler($cropped, 4);
if (!empty($up['error'])) return array('error'=>$up['error']);
$out[] = 'data:image/png;base64,'.base64_encode($up['bytes']);
}
return array('images'=>$out);
}
}
if (!function_exists('ds_call_replicate_flux')) {
function ds_call_replicate_flux($model_id, $prompt, $dims, $count = 1, $ref_image_url = null, $ref_strength = 0.75, $image_size = 'HD', $free_tier = false) {
$keys = ds_get_api_keys();
$key = isset($keys['replicate']) ? $keys['replicate'] : '';
if (strpos($key,'YOUR_') === 0) return array('error'=>'Replicate key not set.');
$is_schnell = strpos($model_id,'flux-schnell') !== false;
if ( $is_schnell ) {
$input = array(
'prompt' => $prompt,
'num_outputs' => (int)min($count, 4),
'output_format' => 'png',
'output_quality' => 100,
'aspect_ratio' => ds_dims_to_aspect($dims),
'go_fast' => true,
);
} else {
// flux-dev and flux-pro ONLY accept aspect_ratio — NOT width/height
// width/height are silently ignored by Replicate and default to 1:1
$input = array(
'prompt' => $prompt,
'num_outputs' => (int)min($count, 4),
'output_format' => 'png',
'output_quality' => 100,
'aspect_ratio' => ds_dims_to_aspect($dims),
'go_fast' => false,
);
}
if (!empty($ref_image_url) && (strpos($model_id,'flux-dev') !== false || strpos($model_id,'flux-pro') !== false)) {
$input['image'] = $ref_image_url;
$input['prompt_strength'] = (float)$ref_strength;
}
$resp = wp_remote_post(
'https://api.replicate.com/v1/models/' . $model_id . '/predictions',
array(
'headers' => array('Authorization'=>'Token '.$key,'Content-Type'=>'application/json','Prefer'=>'wait=1'),
'body' => json_encode(array('input'=>$input)),
'timeout' => 65,
)
);
if (is_wp_error($resp)) return array('error'=>'Replicate: '.$resp->get_error_message());
$d = json_decode(wp_remote_retrieve_body($resp), true);
if (isset($d['status']) && $d['status']==='succeeded') {
$out = is_string($d['output']) ? array($d['output']) : array_values((array)(isset($d['output'])?$d['output']:array()));
if (strtoupper((string)$image_size)==='4K' && !$free_tier) {
$up = ds_upscale_image_urls_to_4k($out, $dims);
if (!empty($up['error'])) return $up;
$out = $up['images'];
}
return array('images'=>$out);
}
if (isset($d['id'])) return array('prediction_id'=>$d['id']);
return array('error'=>'Replicate: '.(isset($d['detail'])?$d['detail']:json_encode($d)));
}
}

if (!function_exists('ds_poll_replicate')) {
function ds_poll_replicate($pid) {
$keys = ds_get_api_keys();
$key = isset($keys['replicate']) ? $keys['replicate'] : '';
$resp = wp_remote_get(
'https://api.replicate.com/v1/predictions/' . $pid,
array('headers'=>array('Authorization'=>'Token '.$key),'timeout'=>20)
);
if (is_wp_error($resp)) return array('status'=>'error','error'=>$resp->get_error_message());
$d = json_decode(wp_remote_retrieve_body($resp), true);
$s = isset($d['status']) ? $d['status'] : 'unknown';
if ($s==='succeeded') {
$out = is_string($d['output']) ? array($d['output']) : array_values((array)(isset($d['output'])?$d['output']:array()));
return array('status'=>'succeeded','images'=>$out);
}
if ($s==='failed') return array('status'=>'failed','error'=>isset($d['error'])?$d['error']:'Failed.');
return array('status'=>$s);
}
}

if (!function_exists('ds_call_seedream_replicate')) {
function ds_call_seedream_replicate($prompt, $dims, $count = 1, $size = '2K', $ref_image = null, $ref_strength = 0.75) {
$keys = ds_get_api_keys();
$key = isset($keys['replicate']) ? $keys['replicate'] : '';
if (strpos($key,'YOUR_') === 0) return array('error'=>'Replicate API key not set.');
$size_val = in_array(strtoupper($size), array('3K','4K'), true) ? '3K' : '2K';
$count = max(1, min(4, (int)$count));

$pids = array();
for ($i = 0; $i < $count; $i++) {
$input = array(
'prompt' => $prompt,
'aspect_ratio' => ds_dims_to_aspect($dims),
'size' => $size_val,
);
if (!empty($ref_image)) {
$input['image_input'] = array($ref_image);
}
$resp = wp_remote_post(
'https://api.replicate.com/v1/models/bytedance/seedream-5-lite/predictions',
array(
'headers' => array('Authorization'=>'Token '.$key,'Content-Type'=>'application/json'),
'body' => json_encode(array('input'=>$input)),
'timeout' => 30,
)
);
if (is_wp_error($resp)) continue;
$d = json_decode(wp_remote_retrieve_body($resp), true);
if (!empty($d['id'])) $pids[] = $d['id'];
}

if (empty($pids)) return array('error'=>'Seedream: could not start any predictions.');
if (count($pids) === 1) return array('prediction_id' => $pids[0]);
// Return all IDs — frontend polls them in parallel
return array('prediction_ids' => $pids);
}
}

if (!function_exists('ds_call_nanobana_replicate')) {
function ds_call_nanobana_replicate($prompt, $dims, $count = 1, $image_size = '2K', $ref_image_url = null, $ref_strength = 0.75) {
$keys = ds_get_api_keys();
$key = isset($keys['replicate']) ? $keys['replicate'] : '';
if (strpos($key,'YOUR_') === 0) return array('error'=>'Replicate API key not set.');
$all_urls = array();
$first_pid = null;
for ($i = 0; $i < min($count, 4); $i++) {
$nb_input = array(
'prompt' => $prompt,
'aspect_ratio'=> ds_dims_to_aspect($dims),
'resolution' => ds_nb_resolution($image_size),
);
if (!empty($ref_image_url)) {
$nb_input['image_input'] = array($ref_image_url);
}
$resp = wp_remote_post(
'https://api.replicate.com/v1/models/google/nano-banana-2/predictions',
array(
'headers' => array('Authorization'=>'Token '.$key,'Content-Type'=>'application/json'),
'body' => json_encode(array('input'=>$nb_input)),
'timeout' => 35,
)
);
if (is_wp_error($resp)) return array('error'=>'NanoBanana: '.$resp->get_error_message());
$d = json_decode(wp_remote_retrieve_body($resp), true);
if (isset($d['id']) && $first_pid === null) {
$first_pid = $d['id'];
} elseif (!isset($d['id'])) {
return array('error'=>'NanoBanana: '.(isset($d['detail'])?$d['detail']:json_encode($d)));
}
}
if (!empty($all_urls)) return array('images'=>$all_urls);
if ($first_pid) return array('prediction_id'=>$first_pid);
return array('error'=>'NanoBanana: No image returned.');
}
}

if (!function_exists('ds_call_qwen_edit')) {
function ds_call_qwen_edit($prompt, $dims, $count = 1, $ref_image_url = null) {
$keys = ds_get_api_keys();
$key = isset($keys['replicate']) ? $keys['replicate'] : '';
if (strpos($key,'YOUR_') === 0) return array('error'=>'Replicate API key not set.');
if (empty($ref_image_url)) return array('error'=>'Qwen Image Edit requires a reference image — upload one in the Reference Image section.');

$count = max(1, min(4, (int)$count));
$pids = array();

for ($i = 0; $i < $count; $i++) {
$input = array(
'prompt' => $prompt,
'image' => is_array($ref_image_url) ? array_values($ref_image_url) : array($ref_image_url),
'aspect_ratio' => ds_dims_to_aspect($dims),
'go_fast' => true,
'output_format' => 'png',
'output_quality' => 100,
);
$resp = wp_remote_post(
'https://api.replicate.com/v1/models/qwen/qwen-image-edit-2511/predictions',
array(
'headers' => array('Authorization'=>'Token '.$key,'Content-Type'=>'application/json'),
'body' => json_encode(array('input'=>$input)),
'timeout' => 30,
)
);
if (is_wp_error($resp)) continue;
$d = json_decode(wp_remote_retrieve_body($resp), true);
if (!empty($d['id'])) $pids[] = $d['id'];
}

if (empty($pids)) return array('error'=>'Qwen Edit: could not start any predictions.');
if (count($pids) === 1) return array('prediction_id' => $pids[0]);
return array('prediction_ids' => $pids);
}
}

if (!function_exists('ds_save_ref_image')) {
function ds_save_ref_image($data_uri) {
if (empty($data_uri) || strpos($data_uri,'data:image') !== 0) return null;
$upload = wp_upload_dir();
$dir = $upload['basedir'].'/ds-ref-tmp/';
wp_mkdir_p($dir);
$ext = 'jpg';
if (strpos($data_uri,'image/png') !== false) $ext = 'png';
if (strpos($data_uri,'image/webp') !== false) $ext = 'webp';
$fname = 'ref-'.time().'-'.rand(100,999).'.'.$ext;
$fpath = $dir.$fname;
$furl = $upload['baseurl'].'/ds-ref-tmp/'.$fname;
$parts = explode(',', $data_uri, 2);
$data = base64_decode(isset($parts[1]) ? $parts[1] : '');
if (empty($data) || strlen($data) < 100) return null;
file_put_contents($fpath, $data);
wp_schedule_single_event(time()+3600, 'ds_cleanup_ref', array($fpath));
return $furl;
}
}
add_action('ds_cleanup_ref', function($f){ if(file_exists($f)) @unlink($f); });

if (!function_exists('ds_save_image_to_uploads')) {
function ds_save_image_to_uploads($src, $prompt = 'ai', $uid = 0) {
// Get raw bytes (data URI or remote fetch) — unchanged from legacy behavior.
if (strpos($src,'data:') === 0) {
$parts = explode(',', $src, 2);
$data = base64_decode(isset($parts[1]) ? $parts[1] : '');
} else {
$resp = wp_remote_get($src, array('timeout'=>120,'sslverify'=>false));
if (is_wp_error($resp)) return $src;
$data = wp_remote_retrieve_body($resp);
}
if (empty($data) || strlen($data) < 500) return $src;

// Normalize to PNG @96dpi in memory (same as legacy behavior) before upload.
$__ds_thumb_bytes = '';
$__ds_disp_bytes = '';
if (function_exists('imagecreatefromstring') && function_exists('imageresolution')) {
$im = @imagecreatefromstring($data);
if ($im) {
imageresolution($im,96,96);
ob_start();
imagepng($im, null, 0);
$normalized = ob_get_clean();
if (function_exists('imagescale') && function_exists('imagewebp')) {
$__ds_thumb_im = @imagescale($im, 480);
if ($__ds_thumb_im) {
ob_start();
imagewebp($__ds_thumb_im, null, 78);
$__ds_thumb_bytes = ob_get_clean();
imagedestroy($__ds_thumb_im);
}
}
if (function_exists('imagescale') && function_exists('imagewebp')) {
$__ds_disp_im = @imagescale($im, 1600);
if ($__ds_disp_im) {
ob_start();
imagewebp($__ds_disp_im, null, 85);
$__ds_disp_bytes = ob_get_clean();
imagedestroy($__ds_disp_im);
}
}
imagedestroy($im);
if (!empty($normalized) && strlen($normalized) > 500) {
$data = $normalized;
}
}
}

// ── Designstager Storage & Retention (R2) ─────────────────────────
// Preferred path: upload straight to R2 from memory — no local file is
// ever written, which satisfies "delete local copy immediately" by
// simply never creating one. Falls back to legacy local-disk storage
// below only if R2 isn't configured yet or the upload fails, so image
// generation never silently breaks for the user while they're mid-setup.
if (function_exists('ds_hub_upload_generated_bytes')) {
if (!$uid) $uid = get_current_user_id();
$up = ds_hub_upload_generated_bytes($data, array(
'file_type' => 'image',
'source'    => 'generated',
'extension' => 'png',
'mime_type' => 'image/png',
'user_id'   => $uid,
'meta'      => array('prompt' => $prompt),
));
if (!is_wp_error($up) && !empty($up['url'])) {
if (!empty($__ds_thumb_bytes) && !empty($up['id']) && !empty($up['r2_key']) && function_exists('ds_hub_get_r2_client')) {
$__ds_thumb_client = ds_hub_get_r2_client();
if (!is_wp_error($__ds_thumb_client)) {
$__ds_thumb_key = preg_replace('/\.[a-z0-9]+$/i', '', $up['r2_key']) . '-thumb.webp';
$__ds_thumb_put = $__ds_thumb_client->put_object($__ds_thumb_key, $__ds_thumb_bytes, 'image/webp');
if (!is_wp_error($__ds_thumb_put) && wp_remote_retrieve_response_code($__ds_thumb_put) < 300) {
global $wpdb;
$__ds_existing_meta = array();
if (function_exists('ds_hub_get_generated_file')) {
$__ds_row = ds_hub_get_generated_file($up['id']);
if ($__ds_row && !empty($__ds_row->meta)) {
$__ds_decoded_meta = json_decode($__ds_row->meta, true);
if (is_array($__ds_decoded_meta)) $__ds_existing_meta = $__ds_decoded_meta;
}
}
$__ds_existing_meta['thumb_r2_key'] = $__ds_thumb_key;
$wpdb->update($wpdb->prefix . 'ds_generated_files', array('meta' => wp_json_encode($__ds_existing_meta)), array('id' => (int) $up['id']));
}
}
}
if (!empty($__ds_disp_bytes) && !empty($up['id']) && !empty($up['r2_key']) && function_exists('ds_hub_get_r2_client')) {
$__ds_disp_client = ds_hub_get_r2_client();
if (!is_wp_error($__ds_disp_client)) {
$__ds_disp_key = preg_replace('/\.[a-z0-9]+$/i', '', $up['r2_key']) . '-display.webp';
$__ds_disp_put = $__ds_disp_client->put_object($__ds_disp_key, $__ds_disp_bytes, 'image/webp');
if (!is_wp_error($__ds_disp_put) && wp_remote_retrieve_response_code($__ds_disp_put) < 300) {
global $wpdb;
$__ds_existing_meta2 = array();
if (function_exists('ds_hub_get_generated_file')) {
$__ds_row2 = ds_hub_get_generated_file($up['id']);
if ($__ds_row2 && !empty($__ds_row2->meta)) {
$__ds_decoded_meta2 = json_decode($__ds_row2->meta, true);
if (is_array($__ds_decoded_meta2)) $__ds_existing_meta2 = $__ds_decoded_meta2;
}
}
$__ds_existing_meta2['disp_r2_key'] = $__ds_disp_key;
$wpdb->update($wpdb->prefix . 'ds_generated_files', array('meta' => wp_json_encode($__ds_existing_meta2)), array('id' => (int) $up['id']));
}
}
}
return $up['url'];
}
$err = is_wp_error($up) ? $up->get_error_message() : 'unknown error';
if (function_exists('ds_hub_log')) {
ds_hub_log('image_upload_fallback_local', $err);
}
error_log('[DS] R2 upload failed for generated image, falling back to local disk: ' . $err);
}

// ── Legacy fallback: local disk (used only if R2 unavailable) ─────
$upload = wp_upload_dir();
$slug = sanitize_title(substr($prompt,0,30));
if (!$slug) $slug = 'image';
$fname = 'ds-ai-'.$slug.'-'.time().'-'.uniqid('',true).'.png';
$fpath = $upload['path'].'/'.$fname;
file_put_contents($fpath, $data);
if (!file_exists($fpath) || filesize($fpath) < 500) return $src;
return $upload['url'].'/'.$fname;
}
}

if (!function_exists('ds_call_qwen_image')) {
function ds_call_qwen_image( $prompt, $dims, $count = 1, $ref_images = null, $extra = array(), $ref_strength = 0.85, $image_size = 'HD', $free_tier = false ) {
$keys = ds_get_api_keys();
$key = isset($keys['replicate']) ? $keys['replicate'] : '';
if (strpos($key,'YOUR_') === 0) return array('error'=>'Replicate API key not set.');

$input = array(
'prompt' => $prompt,
'aspect_ratio' => ds_dims_to_aspect($dims),
'go_fast' => false,
'guidance' => 3.5,
'strength' => 0.85,
'num_inference_steps' => 50,
'output_quality' => 100,
);

if ( ! empty($ref_images) ) {
$refs = is_array($ref_images) ? $ref_images : array($ref_images);
$refs = array_values(array_filter($refs));
if ( ! empty($refs) ) {
$ref_url_raw = $refs[0];
if ( !empty($dims['width']) && !empty($dims['height']) && function_exists('imagecreatefromstring') ) {
$up = wp_upload_dir();
$local = str_replace($up['baseurl'], $up['basedir'], $ref_url_raw);
$img_data = file_exists($local) ? @file_get_contents($local) : @file_get_contents($ref_url_raw);
if ( $img_data ) {
$src = @imagecreatefromstring($img_data);
if ( $src ) {
$sw = imagesx($src);
$sh = imagesy($src);
$tr = $dims['width'] / $dims['height'];
$sr = $sw / $sh;
if ( abs($sr - $tr) > 0.05 ) {
if ( $sr > $tr ) { $cw=(int)($sh*$tr); $ch=$sh; $cx=(int)(($sw-$cw)/2); $cy=0; }
else { $cw=$sw; $ch=(int)($sw/$tr); $cx=0; $cy=(int)(($sh-$ch)/2); }
$dst = imagecreatetruecolor($cw, $ch);
imagecopy($dst, $src, 0, 0, $cx, $cy, $cw, $ch);
$tmp = $up['basedir'].'/ds-ref-tmp/ref-cr-'.time().'-'.rand(100,999).'.jpg';
@mkdir(dirname($tmp), 0755, true);
imagejpeg($dst, $tmp, 95);
imagedestroy($src);
imagedestroy($dst);
$ref_url_raw = $up['baseurl'].'/ds-ref-tmp/'.basename($tmp);
} else {
imagedestroy($src);
}
}
}
}
$input['image'] = $ref_url_raw;
}
}

$resp = wp_remote_post(
'https://api.replicate.com/v1/models/qwen/qwen-image/predictions',
array(
'headers' => array('Authorization'=>'Token '.$key,'Content-Type'=>'application/json','Prefer'=>'wait=1'),
'body' => json_encode(array('input'=>$input)),
'timeout' => 90,
)
);
if (is_wp_error($resp)) return array('error'=>'Replicate: '.$resp->get_error_message());
$d = json_decode(wp_remote_retrieve_body($resp), true);
if (isset($d['status']) && $d['status']==='succeeded') {
$out = is_string($d['output']) ? array($d['output']) : array_values((array)($d['output'] ?? array()));
if (strtoupper((string)$image_size)==='4K' && !$free_tier) {
$up = ds_upscale_image_urls_to_4k($out, $dims);
if (!empty($up['error'])) return $up;
$out = $up['images'];
}
return array('images' => $out);
}
if (isset($d['id'])) return array('prediction_id' => $d['id']);
return array('error' => 'Qwen Image: '.(isset($d['detail']) ? $d['detail'] : json_encode($d)));
}
}

if (!function_exists('ds_generate_image_dispatch')) {
function ds_generate_image_dispatch($model, $prompt, $style, $ratio, $count, $image_size = '2K', $ref_image = null, $ref_strength = 0.75, $free_tier = false) {
$dims = ds_get_dims($ratio, $image_size);
$q = ', ultra high resolution, sharp focus, highly detailed, professional quality';
$full = $style ? trim($style.', '.$prompt.', '.$style.$q) : trim($prompt.$q);
switch ($model) {
case 'cloudflare': return ds_call_cloudflare($full, $dims, $count, $image_size, $free_tier);
case 'cloudflare-klein': return ds_call_cloudflare_klein($full, $dims, $count, null, 0.75, $image_size, $free_tier);
case 'flux-schnell': return ds_call_replicate_flux('black-forest-labs/flux-schnell', $full, $dims, $count, null, 0.75, $image_size, $free_tier);
case 'flux-dev': return ds_call_replicate_flux('black-forest-labs/flux-dev', $full, $dims, $count, $ref_image, $ref_strength, $image_size, $free_tier);
case 'flux-pro':
$fp_imgs = array();
$fp_pid = null;
for ($fp_i = 0; $fp_i < min($count, 4); $fp_i++) {
$fp_r = ds_call_replicate_flux('black-forest-labs/flux-pro', $full, $dims, 1, $ref_image, $ref_strength, $image_size, $free_tier);
if (!empty($fp_r['error'])) return $fp_r;
if (!empty($fp_r['images'])) {
foreach ($fp_r['images'] as $fp_u) $fp_imgs[] = $fp_u;
} elseif (!empty($fp_r['prediction_id']) && $fp_pid === null) {
$fp_pid = $fp_r['prediction_id'];
}
}
if (!empty($fp_imgs)) return array('images'=>$fp_imgs);
if ($fp_pid) return array('prediction_id'=>$fp_pid);
return array('error'=>'Flux Pro: No image returned.');
case 'qwen-image': return ds_call_qwen_image($full, $dims, $count, $ref_image, array(), $ref_strength, $image_size, $free_tier);
case 'hidream-fast': return ds_call_replicate_flux('prunaai/hidream-l1-fast', $full, $dims, $count, null, 0.75, $image_size, $free_tier);
case 'qwen-edit': return ds_call_qwen_edit($full, $dims, $count, $ref_image);
case 'seedream': return ds_call_seedream_replicate($full, $dims, $count, $image_size, $ref_image, $ref_strength);
case 'nanobana': return ds_call_nanobana_replicate($full, $dims, $count, $image_size, $ref_image, $ref_strength);
default: return array('error'=>'Unknown model: '.$model);
}
}
}
