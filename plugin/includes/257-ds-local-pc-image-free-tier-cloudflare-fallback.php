<?php
/**
 * Code Snippets export — DS Local PC Image (free tier) + Cloudflare fallback
 *
 * Snippet ID : 1054 (staging)
 * Active     : yes
 * Priority   : 5
 * Scope      : global
 * Tags       : ds-ai-image, local-pc
 * Modified   : 2026-10-05 10:25:00
 * Load order : before include 089 (must define ds_call_cloudflare first)
 */
/**
 * DS Local PC Image (free tier) with automatic Cloudflare fallback
 *
 * Free-tier requests for the "cloudflare" image model are generated on the
 * owner's own PC first. The PC is never exposed to the internet: a small helper
 * program on the PC asks this site for waiting jobs (ds-localpc/v1/poll), makes
 * the image with its local Stable Diffusion Forge, and posts it back
 * (ds-localpc/v1/result). If the PC is off, busy, slow or fails, the normal
 * Cloudflare model is used automatically and the user never sees an error.
 *
 * No other snippet is edited. This snippet runs BEFORE "DS AI Image API - Part A"
 * (Code Snippets: priority 5, Part A is 10. Plugin: it is listed just before
 * include 089 in the load order) and defines ds_call_cloudflare() itself, as a thin
 * wrapper: try the PC, otherwise call ds_call_cloudflare_cloud(), which is an
 * unchanged copy of Part A's Cloudflare function. Part A skips its own copy
 * because of its function_exists() guard. Deactivating this snippet restores
 * Part A's original function with no other change.
 *
 * NOTE: if you later change the Cloudflare code in Part A, copy the change into
 * ds_call_cloudflare_cloud() below as well.
 *
 * ADMINISTRATORS: with the "Administrators" setting on, an administrator sees
 * one more entry in the AI Studio model list, "My PC (admin only)". It always
 * uses the PC (no Cloudflare fallback), costs no credits, and offers HD
 * (1280x720), FHD (1920x1080) and 4K (FHD upscaled 2x). Nobody else sees it and
 * the server ignores it from anyone who is not an administrator.
 *
 * Settings: wp-admin > Settings > DS Local PC Image. Off by default.
 */

if (!function_exists('ds_local_pc_opts')) {
function ds_local_pc_opts() {
	return array(
		'enabled' => get_option('ds_localpc_enabled', '0') === '1',
		'test'    => get_option('ds_localpc_test', '0') === '1',
		'key'     => (string) get_option('ds_localpc_key', ''),
		'steps'   => max(8, min(40, (int) get_option('ds_localpc_steps', 24))),
		'timeout' => max(20, min(90, (int) get_option('ds_localpc_timeout', 75))),
	);
}
}

// Private working folder inside uploads (job + result files, heartbeat).
if (!function_exists('ds_local_pc_dir')) {
function ds_local_pc_dir() {
	$up = wp_upload_dir(null, false);
	$dir = trailingslashit($up['basedir']) . 'ds-localpc';
	if (!is_dir($dir)) {
		wp_mkdir_p($dir);
		@file_put_contents($dir . '/index.php', '<?php // Silence is golden.');
		@file_put_contents($dir . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n");
	}
	return $dir;
}
}

// Seconds since the PC helper last asked for work (large number = never / off).
if (!function_exists('ds_local_pc_seen_age')) {
function ds_local_pc_seen_age() {
	$f = ds_local_pc_dir() . '/seen';
	clearstatcache(true, $f);
	return file_exists($f) ? (time() - (int) filemtime($f)) : 999999;
}
}

// SDXL-friendly size for a ratio: about 1280x720 worth of pixels, multiples of 8.
if (!function_exists('ds_local_pc_dims')) {
function ds_local_pc_dims($ratio) {
	$map = array(
		'1:1' => array(960, 960),   '16:9' => array(1280, 720), '9:16' => array(720, 1280),
		'4:3' => array(1104, 832),  '3:4'  => array(832, 1104), '3:2'  => array(1176, 784),
		'2:3' => array(784, 1176),  '21:9' => array(1464, 624),
	);
	return isset($map[$ratio]) ? $map[$ratio] : $map['1:1'];
}
}

if (!function_exists('ds_local_pc_ratio_from_dims')) {
function ds_local_pc_ratio_from_dims($dims) {
	$w = isset($dims['width']) ? (float) $dims['width'] : 1;
	$h = isset($dims['height']) ? (float) $dims['height'] : 1;
	$r = $w / max(1, $h);
	$known = array('1:1' => 1.0, '16:9' => 16/9, '9:16' => 9/16, '4:3' => 4/3, '3:4' => 3/4, '3:2' => 1.5, '2:3' => 2/3, '21:9' => 21/9);
	$best = '1:1'; $bd = 99;
	foreach ($known as $k => $val) { $d = abs($r - $val); if ($d < $bd) { $bd = $d; $best = $k; } }
	return $best;
}
}

if (!function_exists('ds_local_pc_log')) {
function ds_local_pc_log($what) {
	$log = get_option('ds_localpc_log', array());
	if (!is_array($log)) $log = array();
	array_unshift($log, gmdate('Y-m-d H:i:s') . ' UTC  ' . $what);
	update_option('ds_localpc_log', array_slice($log, 0, 20), false);
}
}

// Remove job/result files older than 5 minutes (left behind by crashes).
if (!function_exists('ds_local_pc_cleanup')) {
function ds_local_pc_cleanup($dir) {
	foreach (array_merge((array) glob($dir . '/job-*'), (array) glob($dir . '/taken-*'), (array) glob($dir . '/res-*'), (array) glob($dir . '/defer-*'), (array) glob($dir . '/doing-*')) as $f) {
		if (is_file($f) && (time() - (int) filemtime($f)) > 1800) @unlink($f);   // 30 minutes: administrator images may wait in line
	}
}
}

// The helper stopped: forget every waiting image and mark the ones a page is waiting for as failed.
if (!function_exists('ds_local_pc_reset')) {
function ds_local_pc_reset($dir) {
	$n = 0;
	foreach ((array) glob($dir . '/defer-*.json') as $f) {
		$d = json_decode((string) @file_get_contents($f), true);
		if (is_array($d) && !empty($d['uid']) && !empty($d['batch']) && function_exists('ds_ai_gens_update')) {
			$bb = (string) $d['batch'];
			ds_ai_gens_update((int) $d['uid'], function ($gens) use ($bb) {
				foreach ($gens as $i => $g) {
					if (is_array($g) && (($g['prediction_id'] ?? '') === $bb || ($g['batch_id'] ?? '') === $bb) && empty($g['url'])) { $gens[$i]['status'] = 'failed'; $gens[$i]['error'] = 'My PC: the PC stopped before this image was made.'; }
				}
				return $gens;
			});
		}
	}
	foreach (array_merge((array) glob($dir . '/job-*'), (array) glob($dir . '/taken-*'), (array) glob($dir . '/res-*'), (array) glob($dir . '/defer-*'), (array) glob($dir . '/doing-*')) as $f) {
		if (is_file($f) && @unlink($f) && strpos(basename($f), 'defer-') !== 0) $n++;
	}
	ds_local_pc_log('PC helper stopped responding - cleared the waiting images (' . $n . ' files)');
}
}

/**
 * Try the local PC. Returns array('images'=>[data-uri]) on success, or null so
 * the caller falls back to Cloudflare. Never returns an error to the user.
 */
if (!function_exists('ds_local_pc_try')) {
function ds_local_pc_try($prompt, $ratio, $count = 1, $free_tier = false, $force = false, $size = 'HD') {
	$o = ds_local_pc_opts();
	$GLOBALS['ds_localpc_why'] = 'the PC option is switched off';
	if (!$o['enabled'] || $o['key'] === '') return null;
	if (!$force || !in_array($size, array('FHD', '4K'), true)) $size = 'HD';   // free users always get the small, fast size
	if ($force) { $free_tier = true; $o['timeout'] = 40; }   // administrator chose "My PC": no cloud fallback. An image that is not ready after 40s (big size, or waiting in line) carries on in the gallery (see below)
	else { $o['timeout'] = min($o['timeout'], 42); }   // the web server repeats a request it has no answer to after 60s (and gives up at 120s), so every request must answer well inside 60s
	if (!$free_tier) return null;                 // paid users keep the cloud model
	$GLOBALS['ds_localpc_why'] = 'it makes one image at a time - set the number of images to 1';
	if ((int) $count !== 1) return null;          // one image at a time only
	$dir = ds_local_pc_dir();
	ds_local_pc_cleanup($dir);
	$busy = (glob($dir . '/job-*') || glob($dir . '/taken-*'));
	$GLOBALS['ds_localpc_why'] = 'the PC is offline (start the helper on the PC)';
	$age = ds_local_pc_seen_age();
	if ($busy && $age > 900) { ds_local_pc_reset($dir); $busy = false; }   // silent for 15 minutes: the helper stopped in the middle of an image
	if (!glob($dir . '/taken-*') && $age > 45) return null; // PC helper not running (while it works on an image it does not check in)
	// Free users never wait for the PC: busy means Cloudflare. An administrator's image waits in line (up to 6).
	$waiting = count((array) glob($dir . '/job-*')) + count((array) glob($dir . '/taken-*'));
	if ($busy && (!$force || $waiting >= 6)) { $GLOBALS['ds_localpc_why'] = $force ? 'the PC already has 6 images waiting - try again in a few minutes' : 'the PC is busy with another image'; ds_local_pc_log($force ? 'PC queue full - administrator request refused' : 'PC busy - used Cloudflare'); return null; }

	$id = strtolower(wp_generate_password(24, false, false));
	list($w, $h) = ds_local_pc_dims($ratio);
	$job = array(
		'id' => $id, 'prompt' => (string) $prompt,
		'negative_prompt' => 'blurry, low quality, lowres, jpeg artifacts, watermark, text, signature, distorted, deformed',
		'width' => $w, 'height' => $h, 'steps' => $o['steps'],
		'size' => $size,   // HD = as is, FHD = refined to 1.5x (1920x1080), 4K = FHD then upscaled 2x (3840x2160)
		'keep' => $force ? 1 : 0,   // administrator images: the helper also keeps a full-quality copy on the PC
	);
	if (@file_put_contents($dir . '/job-' . $id . '.json', wp_json_encode($job), LOCK_EX) === false) return null;

	$t0 = microtime(true);
	$res = $dir . '/res-' . $id . '.png';
	$err = $dir . '/res-' . $id . '.err';
	$out = null; $why = 'timed out';
	if (function_exists('set_time_limit')) @set_time_limit($o['timeout'] + 60);
	while ((microtime(true) - $t0) < $o['timeout']) {
		usleep(400000);
		clearstatcache();
		if (file_exists($res)) { $out = @file_get_contents($res); break; }
		if (file_exists($err)) { $why = 'PC reported: ' . substr((string) @file_get_contents($err), 0, 120); break; }
		// Not picked up within 12s although the helper looked alive: give up early.
		if ((microtime(true) - $t0) > 12 && file_exists($dir . '/job-' . $id . '.json') && !glob($dir . '/taken-*')) { $why = 'PC did not pick up the job'; break; }
	}
	if ($out === null && $why === 'timed out') { clearstatcache(); if (file_exists($res)) $out = @file_get_contents($res); }   // arrived at the last moment
	if ($out === null && $why === 'timed out' && $force && (file_exists($dir . '/taken-' . $id . '.json') || file_exists($dir . '/job-' . $id . '.json')) && function_exists('ds_ai_gens_update') && get_current_user_id()) {
		// The PC is still working. Hand the page the id of its own "pending" gallery
		// entry; the image is put into that entry when the helper sends it (see /result).
		$uid = get_current_user_id();
		$batch = '';
		foreach ((array) (get_user_meta($uid, 'ds_ai_generations', true) ?: array()) as $g) {
			if (is_array($g) && ($g['status'] ?? '') === 'pending' && strpos((string) ($g['batch_id'] ?? ''), 'sync_') === 0) { $batch = (string) $g['batch_id']; break; }
		}
		if ($batch !== '') {
			$mul = ($size === '4K') ? 3 : (($size === 'FHD') ? 1.5 : 1);
			@file_put_contents($dir . '/defer-' . $id . '.json', wp_json_encode(array('uid' => $uid, 'batch' => $batch, 'prompt' => (string) $prompt, 'ratio' => (string) $ratio, 'size' => $size, 'w' => (int) ($w * $mul), 'h' => (int) ($h * $mul), 't0' => time() - (int) (microtime(true) - $t0))), LOCK_EX);
			// The request's own placeholder stays (a reloaded page follows it); the image handler adds
			// a second entry for this id, and the two are merged when the image arrives.
			ds_local_pc_log((file_exists($dir . '/job-' . $id . '.json') ? 'Waiting in line for the PC (' : 'PC still working (') . $size . ') - will be added to the gallery when ready (administrator, My PC)');
			return array('prediction_id' => $batch, 'source' => 'local_pc');
		}
	}
	foreach (array('/job-', '/taken-') as $p) { @unlink($dir . $p . $id . '.json'); }
	@unlink($res); @unlink($err);

	$is_png = is_string($out) && substr($out, 1, 3) === 'PNG';
	$is_jpg = is_string($out) && substr($out, 0, 2) === "\xFF\xD8";
	if (!is_string($out) || strlen($out) < 2000 || (!$is_png && !$is_jpg)) {
		$GLOBALS['ds_localpc_why'] = ($out === null ? $why : 'the PC sent a broken image');
		ds_local_pc_log('PC failed (' . ($out === null ? $why : 'broken image') . ')' . ($force ? ' - administrator request, no fallback' : ' - used Cloudflare'));
		return null;
	}
	$mul = ($size === '4K') ? 3 : (($size === 'FHD') ? 1.5 : 1);
	ds_local_pc_log(sprintf('PC OK %dx%d in %.0fs%s', $w * $mul, $h * $mul, microtime(true) - $t0, $force ? ' (administrator, My PC)' : ''));
	return array('images' => array('data:image/' . ($is_jpg ? 'jpeg' : 'png') . ';base64,' . base64_encode($out)), 'source' => 'local_pc');
}
}

/* An administrator's FHD/4K image that took longer than one request: save it and
   put it into the gallery entry the page is waiting on. */
if (!function_exists('ds_local_pc_finish_deferred')) {
function ds_local_pc_finish_deferred($d, $img_b64, $error) {
	$uid = isset($d['uid']) ? (int) $d['uid'] : 0;
	$batch = isset($d['batch']) ? (string) $d['batch'] : '';
	if (!$uid || $batch === '' || !function_exists('ds_ai_gens_update')) return false;
	$prompt = isset($d['prompt']) ? (string) $d['prompt'] : '';
	$ratio = isset($d['ratio']) ? (string) $d['ratio'] : '16:9';
	$set = function ($fields) use ($uid, $batch, $prompt, $ratio) {
		ds_ai_gens_update($uid, function ($gens) use ($batch, $fields, $prompt, $ratio) {
			$hit = -1;
			foreach ($gens as $i => $g) { if (is_array($g) && ($g['batch_id'] ?? '') === $batch) { $hit = $i; break; } }
			if ($hit < 0) { foreach ($gens as $i => $g) { if (is_array($g) && ($g['prediction_id'] ?? '') === $batch) { $hit = $i; break; } } }
			if ($hit >= 0) {
				$gens[$hit] = array_merge($gens[$hit], $fields);
				// drop the handler's second entry for the same image
				foreach ($gens as $i => $g) { if ($i !== $hit && is_array($g) && ($g['prediction_id'] ?? '') === $batch && empty($g['url'])) unset($gens[$i]); }
				return array_values($gens);
			}
			array_unshift($gens, array_merge(array('prediction_id' => $batch, 'prompt' => $prompt, 'model' => 'cloudflare', 'ratio' => $ratio, 'count' => 1, 'credits' => 0, 'batch_id' => $batch), $fields));
			return $gens;
		});
	};
	$raw = ($img_b64 !== '') ? base64_decode($img_b64, true) : false;
	$is_png = is_string($raw) && substr($raw, 1, 3) === 'PNG';
	$is_jpg = is_string($raw) && substr($raw, 0, 2) === "\xFF\xD8";
	if (!is_string($raw) || strlen($raw) < 2000 || (!$is_png && !$is_jpg)) {
		$msg = 'My PC: ' . (substr(sanitize_text_field($error), 0, 160) ?: 'no image');
		$set(array('status' => 'failed', 'error' => $msg));
		ds_local_pc_log('PC failed (' . $msg . ') - administrator request, no fallback');
		return false;
	}
	if (function_exists('set_time_limit')) @set_time_limit(120);
	wp_set_current_user($uid);   // the image is saved as the administrator who asked for it
	$mime = $is_jpg ? 'image/jpeg' : 'image/png';
	$src = 'data:' . $mime . ';base64,' . $img_b64;
	$url = function_exists('ds_save_image_to_uploads') ? (string) ds_save_image_to_uploads($src, $prompt, $uid) : $src;
	$upload = wp_upload_dir();
	if (strpos($url, 'data:') === 0) {   // normal save failed: write the file directly
		$fname = 'ds-ai-pc-' . time() . '-' . wp_rand(100, 999) . ($is_jpg ? '.jpg' : '.png');
		if (@file_put_contents($upload['path'] . '/' . $fname, $raw) !== false) $url = $upload['url'] . '/' . $fname;
	}
	if (strpos($url, 'http') !== 0) {
		$set(array('status' => 'failed', 'error' => 'My PC: the website could not save the image.'));
		ds_local_pc_log('PC image arrived but could not be saved (administrator, My PC)');
		return false;
	}
	$file_id = function_exists('ds_hub_extract_file_id_from_proxy_url') ? (int) ds_hub_extract_file_id_from_proxy_url($url) : 0;
	$set(array('url' => $url, 'attachment_id' => 0, 'file_id' => $file_id, 'status' => 'succeeded', 'date' => current_time('Y-m-d H:i:s')));
	ds_local_pc_log(sprintf('PC OK %dx%d in %ds (administrator, My PC, added to gallery)', (int) ($d['w'] ?? 0), (int) ($d['h'] ?? 0), time() - (int) ($d['t0'] ?? time())));
	// Media-library entry (only when the file is on this server), same as the normal image handler.
	try {
		$fpath = $upload['path'] . '/' . basename((string) parse_url($url, PHP_URL_PATH));
		if (file_exists($fpath)) {
			$aid = wp_insert_attachment(array('post_mime_type' => $mime, 'post_title' => sanitize_text_field(substr($prompt, 0, 60)), 'post_content' => '', 'post_status' => 'inherit'), $fpath);
			if ($aid && !is_wp_error($aid)) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				wp_update_attachment_metadata($aid, wp_generate_attachment_metadata($aid, $fpath));
				update_post_meta($aid, 'ds_ai_prompt', $prompt);
				update_post_meta($aid, 'ds_ai_model', 'cloudflare');
				$set(array('attachment_id' => (int) $aid));
			}
		}
	} catch (\Throwable $e) { /* the gallery entry is already saved */ }
	return true;
}
}

/* ---------------- Endpoints used by the PC helper ---------------- */
if (!function_exists('ds_local_pc_auth')) {
function ds_local_pc_auth($request) {
	$o = ds_local_pc_opts();
	$given = (string) $request->get_header('x_ds_key');
	if ($given === '') { $given = (string) $request->get_param('key'); }
	if ($o['key'] === '' || $given === '' || !hash_equals($o['key'], $given)) {
		return new WP_Error('ds_localpc_forbidden', 'Not allowed.', array('status' => 403));
	}
	return true;
}
}

add_action('rest_api_init', function () {
	// The helper waits here (up to ~20s) for a job. Each call also marks the PC as online.
	register_rest_route('ds-localpc/v1', '/poll', array(
		'methods' => 'POST', 'permission_callback' => 'ds_local_pc_auth',
		'callback' => function ($request) {
			$o = ds_local_pc_opts();
			$dir = ds_local_pc_dir();
			if (function_exists('set_time_limit')) @set_time_limit(60);
			$t0 = microtime(true);
			do {
				@touch($dir . '/seen');
				if ($o['enabled']) {
					$jobs = (array) glob($dir . '/job-*.json');
					usort($jobs, function ($a, $b) { return (int) @filemtime($a) - (int) @filemtime($b); });   // oldest first
					foreach ($jobs as $f) {
						// Nobody is waiting for this one any more (its request ended without an answer): drop it.
						if ((time() - (int) @filemtime($f)) > 60 && !file_exists(str_replace('/job-', '/defer-', $f))) { @unlink($f); continue; }
						$taken = str_replace('/job-', '/taken-', $f);
						if (@rename($f, $taken)) {
							$job = json_decode((string) @file_get_contents($taken), true);
							if (is_array($job) && !empty($job['id'])) {
								$df = $dir . '/defer-' . preg_replace('/[^a-z0-9]/', '', (string) $job['id']) . '.json';
								if (file_exists($df) && function_exists('ds_ai_gens_update')) {
									// This image waited in line: its gallery entry starts counting now.
									$dd = json_decode((string) @file_get_contents($df), true);
									if (is_array($dd) && !empty($dd['uid']) && !empty($dd['batch'])) {
										$bb = (string) $dd['batch'];
										ds_ai_gens_update((int) $dd['uid'], function ($gens) use ($bb) {
											foreach ($gens as $i => $g) {
												if (is_array($g) && (($g['prediction_id'] ?? '') === $bb || ($g['batch_id'] ?? '') === $bb) && empty($g['url'])) { $gens[$i]['status'] = 'pending'; $gens[$i]['date'] = current_time('Y-m-d H:i:s'); unset($gens[$i]['error']); }
											}
											return $gens;
										});
									}
								}
								return rest_ensure_response(array('job' => $job));
							}
						}
					}
				}
				usleep(500000);
			} while ((microtime(true) - $t0) < 20 && !connection_aborted());
			return rest_ensure_response(array('job' => null, 'enabled' => $o['enabled']));
		},
	));
	// The helper posts the finished PNG (base64) or an error message here.
	register_rest_route('ds-localpc/v1', '/result', array(
		'methods' => 'POST', 'permission_callback' => 'ds_local_pc_auth',
		'callback' => function ($request) {
			$dir = ds_local_pc_dir();
			$id = preg_replace('/[^a-z0-9]/', '', strtolower((string) $request->get_param('id')));
			if ($id === '' || !file_exists($dir . '/taken-' . $id . '.json')) return rest_ensure_response(array('ok' => false, 'reason' => 'unknown job'));
			$img = (string) $request->get_param('image');
			if (file_exists($dir . '/defer-' . $id . '.json') && @rename($dir . '/defer-' . $id . '.json', $dir . '/doing-' . $id . '.json')) {
				$d = json_decode((string) @file_get_contents($dir . '/doing-' . $id . '.json'), true);
				@unlink($dir . '/taken-' . $id . '.json');
				$ok = ds_local_pc_finish_deferred(is_array($d) ? $d : array(), $img, (string) $request->get_param('error'));
				@unlink($dir . '/doing-' . $id . '.json');
				return rest_ensure_response(array('ok' => $ok, 'gallery' => true));
			}
			if ($img !== '') {
				$raw = base64_decode($img, true);
				if ($raw !== false && strlen($raw) > 2000) {
					@file_put_contents($dir . '/res-' . $id . '.tmp', $raw, LOCK_EX);
					@rename($dir . '/res-' . $id . '.tmp', $dir . '/res-' . $id . '.png');
					@unlink($dir . '/taken-' . $id . '.json');   // done: the PC is free for the next image even if the page stopped waiting
					return rest_ensure_response(array('ok' => true));
				}
			}
			@file_put_contents($dir . '/res-' . $id . '.err', substr(sanitize_text_field((string) $request->get_param('error')), 0, 200) ?: 'no image', LOCK_EX);
			@unlink($dir . '/taken-' . $id . '.json');
			return rest_ensure_response(array('ok' => true));
		},
	));
});

/* While a page is watching an image the PC is still making (or that waits in line),
   keep that image's "pending" gallery entry current, so a slow PC is not shown as failed. */
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
	if ($result !== null || $request->get_route() !== '/ds/v1/poll-image' || !function_exists('ds_ai_gens_update')) return $result;
	$pid = (string) $request->get_param('prediction_id');
	$uid = get_current_user_id();
	if (!$uid || strpos($pid, 'sync_') !== 0) return $result;
	$dir = ds_local_pc_dir();
	foreach ((array) glob($dir . '/defer-*.json') as $f) {
		$d = json_decode((string) @file_get_contents($f), true);
		if (!is_array($d) || (int) ($d['uid'] ?? 0) !== $uid || empty($d['batch'])) continue;
		$bb = (string) $d['batch'];
		if ($pid !== $bb && strpos($pid, $bb . '_') !== 0) continue;
		$id = substr(basename($f, '.json'), 6);
		if (!file_exists($dir . '/taken-' . $id . '.json') && !file_exists($dir . '/job-' . $id . '.json')) break;   // not being made any more
		$now = current_time('Y-m-d H:i:s');
		$stale = false;
		foreach ((array) (get_user_meta($uid, 'ds_ai_generations', true) ?: array()) as $g) {
			if (is_array($g) && (($g['prediction_id'] ?? '') === $bb || ($g['batch_id'] ?? '') === $bb) && empty($g['url']) && (($g['status'] ?? '') !== 'pending' || (strtotime($now) - (int) strtotime((string) ($g['date'] ?? ''))) > 60)) { $stale = true; break; }
		}
		if ($stale) {
			ds_ai_gens_update($uid, function ($gens) use ($bb, $now) {
				foreach ($gens as $i => $g) {
					if (is_array($g) && (($g['prediction_id'] ?? '') === $bb || ($g['batch_id'] ?? '') === $bb) && empty($g['url'])) { $gens[$i]['status'] = 'pending'; $gens[$i]['date'] = $now; unset($gens[$i]['error']); }
				}
				return $gens;
			});
		}
		break;
	}
	return $result;
}, 5, 3);

/* ---------------- Administrators: "My PC" in the model list ---------------- */
// The page sends the normal Cloudflare model plus the header X-DS-Use-PC. Only
// an administrator's request is honoured; it is then made on the PC only and
// costs no credits.
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
	if ($result !== null || $request->get_route() !== '/ds/v1/generate-image') return $result;
	if ((string) $request->get_header('x_ds_use_pc') !== '1') return $result;
	$o = ds_local_pc_opts();
	if (!$o['enabled'] || !$o['test'] || !current_user_can('manage_options')) return $result;
	$request->set_param('model', 'cloudflare');
	$GLOBALS['ds_localpc_force'] = 1;
	// no credits for this request, and never the free-plan daily limits
	add_filter('option_ds_image_credit_settings', function ($opt) {
		if (!is_array($opt)) $opt = array();
		if (!isset($opt['resolution_overrides']) || !is_array($opt['resolution_overrides'])) $opt['resolution_overrides'] = array();
		$opt['resolution_overrides']['cloudflare'] = array('HD' => 0, 'FHD' => 0, '2K' => 0, '3K' => 0, '4K' => 0);
		return $opt;
	}, 99);
	add_filter('default_option_ds_image_credit_settings', function ($opt) {
		return array('resolution_overrides' => array('cloudflare' => array('HD' => 0, 'FHD' => 0, '2K' => 0, '3K' => 0, '4K' => 0)));
	}, 99);
	add_filter('ds_free_tier_credit_floor', function () { return 0; }, 99);
	return $result;
}, 1, 3);

add_action('wp_footer', function () {
	if (!is_user_logged_in() || !current_user_can('manage_options')) return;
	$o = ds_local_pc_opts();
	if (!$o['enabled'] || !$o['test']) return;
	?>
<script id="ds-localpc-admin">
(function(){
	function sel(){ return document.getElementById('dsModel'); }
	function pcOn(){ var s = sel(); return !!(s && s.options[s.selectedIndex] && s.options[s.selectedIndex].getAttribute('data-pc') === '1'); }
	function label(){
		if (!pcOn()) return;
		var l = document.getElementById('dsGBL'); if (l) l.textContent = 'Generate (My PC, free)';
		/* My PC sizes: HD 1280x720 (fast), FHD 1920x1080, 4K 3840x2160 (FHD upscaled) - open to the administrator whatever the plan */
		var b = document.querySelectorAll('.ds-size-btn');
		for (var i = 0; i < b.length; i++) {
			var z = b[i].getAttribute('data-size');
			if (z === 'HD' || z === 'FHD' || z === '4K') { b[i].classList.remove('disabled'); b[i].classList.remove('model-locked'); }
			else { b[i].classList.add('disabled'); b[i].classList.add('model-locked'); }
		}
	}
	function setup(){
		var s = sel();
		if (!s || s.querySelector('option[data-pc="1"]')) return !!s;
		var o = document.createElement('option');
		o.value = 'cloudflare'; o.setAttribute('data-pc', '1'); o.setAttribute('data-cost', '0');
		o.textContent = 'My PC (admin only)';
		s.appendChild(o);
		s.addEventListener('change', function(){ setTimeout(label, 0); });
		document.addEventListener('click', function(){ setTimeout(label, 0); }, true);
		return true;
	}
	/* the page checks credits before sending: My PC is free, so let it through */
	function wrapGen(){
		if (typeof window.dsGen !== 'function' || window.dsGen.__pc) return;
		var orig = window.dsGen;
		window.dsGen = function(){
			if (!pcOn() || !window.dsAI) return orig.apply(this, arguments);
			var real = window.dsAI.credits;
			window.dsAI.credits = Math.max(Number(real) || 0, 9999);
			try { return orig.apply(this, arguments); } finally { window.dsAI.credits = real; }
		};
		window.dsGen.__pc = true;
	}
	/* mark the request so the server knows it is the My PC option */
	if (window.fetch && !window.fetch.__pc) {
		var of = window.fetch;
		window.fetch = function(url, init){
			try {
				if (typeof url === 'string' && url.indexOf('/ds/v1/generate-image') !== -1 && pcOn() && init) {
					init.headers = init.headers || {};
					if (typeof init.headers.set === 'function') init.headers.set('X-DS-Use-PC', '1'); else init.headers['X-DS-Use-PC'] = '1';
				}
			} catch (e) {}
			return of.apply(this, arguments);
		};
		window.fetch.__pc = true;
	}
	var n = 0, t = setInterval(function(){ var ok = setup(); wrapGen(); if ((ok && window.dsGen && window.dsGen.__pc) || ++n > 40) clearInterval(t); }, 250);
})();
</script>
	<?php
}, 99);

/* ---------------- Settings page ---------------- */
add_action('admin_menu', function () {
	add_options_page('DS Local PC Image', 'DS Local PC Image', 'manage_options', 'ds-local-pc', 'ds_local_pc_settings_page');
});

if (!function_exists('ds_local_pc_settings_page')) {
function ds_local_pc_settings_page() {
	if (!current_user_can('manage_options')) return;
	$msg = '';
	$test_img = '';
	if (isset($_POST['ds_localpc_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ds_localpc_nonce'])), 'ds_localpc_save')) {
		update_option('ds_localpc_enabled', isset($_POST['enabled']) ? '1' : '0', false);
		update_option('ds_localpc_test', isset($_POST['test']) ? '1' : '0', false);
		if (isset($_POST['key']) && $_POST['key'] !== '') update_option('ds_localpc_key', trim(wp_unslash($_POST['key'])), false);
		update_option('ds_localpc_steps', (int) ($_POST['steps'] ?? 24), false);
		update_option('ds_localpc_timeout', (int) ($_POST['timeout'] ?? 75), false);
		$msg = 'Saved.';
		if (isset($_POST['clearq'])) { ds_local_pc_reset(ds_local_pc_dir()); $msg = 'Saved. The waiting images were cleared.'; }
		if (isset($_POST['runtest'])) {
			$t0 = microtime(true);
			$r = ds_local_pc_try('a red vintage bicycle leaning against a white wall, soft morning light, photograph', '16:9', 1, true);
			$msg = $r ? sprintf('Test image came from your PC in %.0f seconds.', microtime(true) - $t0) : 'The test image did not come from the PC. See Recent activity below for the reason.';
			if ($r) $test_img = $r['images'][0];
		}
	}
	$o = ds_local_pc_opts();
	$age = ds_local_pc_seen_age();
	$log = get_option('ds_localpc_log', array());
	?>
	<div class="wrap">
		<h1>DS Local PC Image</h1>
		<p>Free-tier images are generated on your own PC first. If the PC is off, busy or fails, the normal Cloudflare model is used automatically.</p>
		<?php if ($msg) : ?><div class="notice notice-info"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
		<?php if ($test_img) : ?><p><img src="<?php echo esc_attr($test_img); ?>" alt="Test image" style="max-width:640px;height:auto;border:1px solid #ccd0d4;"></p><?php endif; ?>
		<p><strong>PC status:</strong>
			<?php if ($age <= 45) : ?><span style="color:#008a20;">ONLINE</span> (helper checked in <?php echo (int) $age; ?>s ago)
			<?php elseif ($age < 999999) : ?><span style="color:#b32d2e;">OFFLINE</span> (last seen <?php echo esc_html(human_time_diff(time() - $age)); ?> ago)
			<?php else : ?><span style="color:#b32d2e;">OFFLINE</span> (the helper has never connected)<?php endif; ?>
		</p>
		<p><strong>Hook:</strong> <?php echo defined('DS_LOCALPC_WRAPPER') ? 'active' : 'NOT active (another snippet defined ds_call_cloudflare first)'; ?></p>
		<form method="post">
			<?php wp_nonce_field('ds_localpc_save', 'ds_localpc_nonce'); ?>
			<table class="form-table">
				<tr><th>Enabled</th><td><label><input type="checkbox" name="enabled" <?php checked($o['enabled']); ?>> Use my PC for free-tier images</label></td></tr>
				<tr><th>Administrators</th><td><label><input type="checkbox" name="test" <?php checked($o['test']); ?>> Show "My PC (admin only)" in the AI Studio model list for administrators. It always uses the PC (no Cloudflare fallback) and costs no credits. Sizes: HD 1280x720 (about 1 minute), FHD 1920x1080 (about 2 minutes), 4K 3840x2160 (FHD upscaled, 3 to 4 minutes; the page keeps waiting and the image appears in the gallery). The PC keeps a full-quality copy of every administrator image. If the PC is busy, your next images wait in line (up to 6) and are made one after another. Nobody else sees it.</label></td></tr>
				<tr><th>Helper key</th><td><input type="password" name="key" class="regular-text" value="" autocomplete="new-password" placeholder="<?php echo $o['key'] !== '' ? 'saved - leave blank to keep' : 'paste the key from your PC'; ?>"><p class="description">Must match the key in the helper's config file on your PC.</p></td></tr>
				<tr><th>Steps</th><td><input type="number" name="steps" min="8" max="40" value="<?php echo (int) $o['steps']; ?>"> <span class="description">Lower is faster, higher is more detailed. Default 24.</span></td></tr>
				<tr><th>Give up after (seconds)</th><td><input type="number" name="timeout" min="20" max="90" value="<?php echo (int) $o['timeout']; ?>"> <span class="description">Then Cloudflare is used. Default 75.</span></td></tr>
			</table>
			<p><button class="button button-primary" name="save" value="1">Save</button> <button class="button" name="runtest" value="1">Save and make a test image on my PC</button> <button class="button" name="clearq" value="1">Clear waiting images (<?php echo count((array) glob(ds_local_pc_dir() . '/job-*')) + count((array) glob(ds_local_pc_dir() . '/taken-*')); ?>)</button></p>
		</form>
		<h2>Recent activity</h2>
		<?php if (empty($log)) : ?><p>No requests yet.</p><?php else : ?><pre style="background:#fff;padding:10px;border:1px solid #ccd0d4;max-width:800px;"><?php echo esc_html(implode("\n", (array) $log)); ?></pre><?php endif; ?>
	</div>
	<?php
}
}

/* ---------------- Unchanged copy of Part A's Cloudflare function (renamed) ---------------- */
if (!function_exists('ds_call_cloudflare_cloud')) {
	function ds_call_cloudflare_cloud($prompt, $dims, $count = 1, $image_size = 'HD', $free_tier = false) {
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


/* ---------------- Wrapper: PC first, Cloudflare as fallback ---------------- */
if (!function_exists('ds_call_cloudflare')) {
	define('DS_LOCALPC_WRAPPER', true);
	function ds_call_cloudflare($prompt, $dims, $count = 1, $image_size = 'HD', $free_tier = false) {
		if (!empty($GLOBALS['ds_localpc_force'])) {   // administrator picked "My PC" in the model list: PC only, never Cloudflare
			$lp = ds_local_pc_try($prompt, ds_local_pc_ratio_from_dims($dims), $count, true, true, strtoupper((string) $image_size));
			if ($lp) return $lp;
			return array('error' => 'My PC: ' . (isset($GLOBALS['ds_localpc_why']) ? $GLOBALS['ds_localpc_why'] : 'no image') . '.');
		}
		$lp = ds_local_pc_try($prompt, ds_local_pc_ratio_from_dims($dims), $count, $free_tier);
		if ($lp) return $lp;
		return ds_call_cloudflare_cloud($prompt, $dims, $count, $image_size, $free_tier);
	}
}
