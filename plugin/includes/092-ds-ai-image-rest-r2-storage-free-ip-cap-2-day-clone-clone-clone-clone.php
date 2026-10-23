<?php
/**
 * Code Snippets export — DS AI Image REST (R2 Storage) — Free IP cap 2/day [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE]
 *
 * Snippet ID : 773
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-087
 * Modified   : 2026-09-09 02:46:05
 * Load order : 092 of 144
 */
/**
 * Snippet Name: DS AI Image REST (R2 Storage)
 * Run: Everywhere
 * Registers POST /ds/v1/generate-image and POST /ds/v1/poll-image
 * Requires: DS AI Image API - Part A (ds_generate_image_dispatch, ds_poll_replicate)
 *
 * Clone of the original "DS AI Image REST". Changes vs. the original:
 *  1. Every place that saves a generated image now also records the R2
 *     file_id (extracted from the proxy URL returned by ds_save_image_to_uploads)
 *     alongside the legacy attachment_id, in both $images_out and the
 *     ds_ai_generations history entries, so the gallery can show retention
 *     status and expiry for R2-backed images.
 *  2. Adds the previously-missing POST /ds/v1/delete-image endpoint that the
 *     existing "DS My Generated Images" front-end already calls. It supports
 *     both legacy local-attachment entries (attachment_id) and new R2 entries
 *     (file_id), refuses to delete Marketplace-accepted images, and — per the
 *     "never falsely report success" requirement — returns success:false with
 *     the real error if an R2 delete fails, instead of silently succeeding.
 * The original snippet is kept (deactivated) as a rollback backup — do not delete it.
 */


add_action('rest_api_init', function() {

// ── Generate Image ────────────────────────────────────────────────────
register_rest_route('ds/v1', '/generate-image', array(
	'methods' => 'POST',
	'permission_callback' => function() { return is_user_logged_in(); },
	'callback' => function(WP_REST_Request $req) {

		// Keep this request running to completion even if the browser
		// disconnects (e.g. a refresh mid-generation) — PHP's default
		// is to ABORT the script the moment the client goes away, so
		// without this, a sync model's placeholder never gets resolved
		// and sits stuck until the stale-cleanup sweep finds it.
		@ignore_user_abort(true);
		@set_time_limit(150);

		$uid = get_current_user_id();
		$plan = function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : 'free';
		$free_tier = function_exists('ds_user_is_free_tier') ? ds_user_is_free_tier($uid) : ($plan === 'free');
		// Free-tier IP throttle: 10 images/day per IP across all accounts
		if ($free_tier) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
			$key = 'ds_ipday_' . md5($ip . date('Y-m-d'));
			$n = (int)get_transient($key);
			if ($n >= 2) {
				return rest_ensure_response(array('error'=>true,'status'=>429,
					'message'=>'Daily free limit reached for this network.'));
			}
			set_transient($key, $n+1, DAY_IN_SECONDS);
		}
		$model = sanitize_text_field($req->get_param('model') ?: 'cloudflare');
		$prompt = sanitize_text_field($req->get_param('prompt') ?: '');
		$style = sanitize_text_field($req->get_param('style') ?: '');
		$ratio = sanitize_text_field($req->get_param('ratio') ?: '1:1');
		$count = max(1, min(4, (int)($req->get_param('count') ?: 1)));
		if (in_array($model, array('qwen-image','qwen-edit','nanobana'), true)) $count = 1;
		$size = sanitize_text_field($req->get_param('image_size') ?: 'HD');
		$ref = $req->get_param('reference_image') ?: null;
		// Normalize: could be single base64 string or array
		if (is_string($ref)) $ref = [$ref];
		$ref_str= floatval($req->get_param('ref_strength') ?: 0.75);

		if (empty($prompt)) {
			return rest_ensure_response(array('error' => true, 'message' => 'Prompt is required.'));
		}

		// ── Free plan: model lock ─────────────────────────────────────
		$free_models = array('cloudflare', 'cloudflare-klein');
		if ($free_tier && !in_array($model, $free_models)) {
			return rest_ensure_response(array(
				'error' => true,
				'status' => 403,
				'message' => 'This model requires a paid plan. Upgrade to unlock.',
			));
		}

		// ── Free plan: daily limit (2 images/day) ────────────────────
		if ($free_tier) {
			$today = date('Y-m-d');
			$day_key = 'ds_free_daily_' . $today;
			$day_used = (int) get_user_meta($uid, $day_key, true);
			if ($day_used >= 2) {
				return rest_ensure_response(array(
					'error' => true,
					'status' => 429,
					'message' => 'Free plan: 2 images/day limit reached. Resets at midnight.',
				));
			}
		}

		// ── Credit check ──────────────────────────────────────────────
		$costs = function_exists('ds_get_model_costs') ? ds_get_model_costs() : array(
			'cloudflare' => 2, 'cloudflare-klein' => 3, 'flux-schnell' => 4,
			'flux-dev' => 7, 'seedream' => 8, 'flux-pro' => 10, 'nanobana' => 13,
		);
		$cost_each = function_exists('ds_get_model_cost_each') ? ds_get_model_cost_each($model, $size) : ($costs[$model] ?? 4);
		// Free plan: cloudflare/cloudflare-klein cost 0 credits (counted by daily limit)
		$total_cost = $free_tier ? 0 : ($cost_each * $count);

		$credits = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int)get_user_meta($uid, 'ds_ai_credits', true);
		if ($total_cost > 0 && $credits < $total_cost) {
			return rest_ensure_response(array(
				'error' => true,
				'message' => 'Not enough credits. Need ' . $total_cost . '.',
			));
		}

		// ── Save reference image to disk if provided ──────────────────
		$ref_url = null;
		if (!empty($ref) && function_exists('ds_save_ref_image')) {
			if (is_array($ref)) {
				$saved = array_values(array_filter(array_map('ds_save_ref_image', $ref)));
				$ref_url = count($saved) === 1 ? $saved[0] : $saved;
			} else {
				$ref_url = ds_save_ref_image($ref);
			}
		}

		// ── Dispatch to API ───────────────────────────────────────────
		if (!function_exists('ds_generate_image_dispatch')) {
			return rest_ensure_response(array('error' => true, 'message' => 'API Part A snippet not active.'));
		}

		// ── Sync models (cloudflare/cloudflare-klein) have no external job ID,
		// so a mid-generation refresh loses all track of them. Save a
		// PENDING placeholder per image now; the resume-on-refresh flow
		// (already built for async models) will pick these up for free.
		$is_sync_model = in_array($model, array('cloudflare', 'cloudflare-klein'));
		$sync_batch_id = '';
		if ($is_sync_model) {
			$sync_batch_id = 'sync_' . uniqid('', true);
			$now_pre = current_time('Y-m-d H:i:s');
			ds_ai_gens_update($uid, function($gens_pre) use ($sync_batch_id, $prompt, $model, $ratio, $count, $now_pre) {
				for ($si = 0; $si < $count; $si++) {
					array_unshift($gens_pre, array(
						'prediction_id' => $sync_batch_id . '_' . $si,
						'prompt' => $prompt,
						'model' => $model,
						'ratio' => $ratio,
						'count' => 1,
						'credits' => 0,
						'date' => $now_pre,
						'status' => 'pending',
						'url' => '',
						'batch_id' => $sync_batch_id,
					));
				}
				return $gens_pre;
			});

			// Hand the client its answer right now and keep working
			// after the connection closes. This is the actual fix —
			// ignore_user_abort alone isn't enough on hosts where the
			// WEBSERVER (not PHP) kills the request on disconnect.
			if (function_exists('fastcgi_finish_request')) {
				if (!headers_sent()) {
					header('Content-Type: application/json; charset=UTF-8');
				}
				echo wp_json_encode(array(
					'prediction_id' => $sync_batch_id,
					'credits_left' => max(0, $credits - $total_cost),
				));
				fastcgi_finish_request();
			}
		}

		// qwen-edit requires image as array and count must be 1
		if ( $model === 'qwen-image' ) {
			$all_images = array();
			$all_pids = array();
			for ( $qi = 0; $qi < $count; $qi++ ) {
				$r = ds_generate_image_dispatch($model, $prompt, $style, $ratio, 1, $size, $ref_url, $ref_str);
				if ( ! empty($r['images']) ) {
					$all_images = array_merge($all_images, $r['images']);
				} elseif ( ! empty($r['prediction_id']) ) {
					$all_pids[] = $r['prediction_id'];
				}
			}
			if ( ! empty($all_images) ) {
				$result = array('images' => $all_images);
			} elseif ( ! empty($all_pids) ) {
				$result = count($all_pids) === 1
					? array('prediction_id' => $all_pids[0])
					: array('prediction_ids' => $all_pids);
			} else {
				$err_detail = isset($r['error']) ? (is_string($r['error']) ? $r['error'] : json_encode($r['error'])) : 'No images returned.';
				$result = array('error' => true, 'message' => $err_detail);
			}
		} elseif ( $model === 'qwen-edit' ) {
			$all_pids = array();
			for ( $qi = 0; $qi < $count; $qi++ ) {
				$r = ds_generate_image_dispatch($model, $prompt, $style, $ratio, 1, $size, $ref_url, $ref_str);
				if ( ! empty($r['prediction_id']) ) $all_pids[] = $r['prediction_id'];
			}
			if ( ! empty($all_pids) ) {
				$result = count($all_pids) === 1
					? array('prediction_id' => $all_pids[0])
					: array('prediction_ids' => $all_pids);
			} else {
				$err_detail = isset($r['error']) ? (is_string($r['error']) ? $r['error'] : json_encode($r['error'])) : 'No images returned.';
				$result = array('error' => true, 'message' => $err_detail);
			}
		} else {
			$result = ds_generate_image_dispatch($model, $prompt, $style, $ratio, $count, $size, $ref_url, $ref_str, $free_tier);
		}

		if (!empty($result['error'])) {
			if ($is_sync_model && $sync_batch_id) {
				ds_ai_gens_update($uid, function($gens_err) use ($sync_batch_id, $result) {
					foreach ($gens_err as $gi => $ge) {
						if (($ge['batch_id'] ?? '') === $sync_batch_id) {
							$gens_err[$gi]['status'] = 'failed';
							$gens_err[$gi]['error'] = $result['error'];
						}
					}
					return $gens_err;
				});
			}
			return rest_ensure_response(array('error' => true, 'message' => is_string($result['error']) ? $result['error'] : wp_json_encode($result['error'])));
		}

		// ── Deduct credits ONLY on success ────────────────────────────
		if ($total_cost > 0 && !ds_deduct_credits($uid, $total_cost)) {
			// Another request spent the credits while this one was generating.
			return rest_ensure_response(array('error' => true, 'message' => 'Not enough credits. Need ' . $total_cost . '.'));
		}

		// ── Increment free daily counter ONLY on success ──────────────
		if ($free_tier) {
			$today = date('Y-m-d');
			$day_key = 'ds_free_daily_' . $today;
			$used = (int) get_user_meta($uid, $day_key, true);
			update_user_meta($uid, $day_key, $used + $count); // charge per image
			// Clean up yesterday
			delete_user_meta($uid, 'ds_free_daily_' . date('Y-m-d', strtotime('-1 day')));
		}

		// ── Save images (R2 if configured, else media library) ────────
		$images_out = array();
		if (!empty($result['images'])) {
			$unique_images = array_unique($result['images']); // deduplicate
			foreach ($unique_images as $img_src) {
				if (function_exists('ds_save_image_to_uploads')) {
					$saved_url = ds_save_image_to_uploads($img_src, $prompt);
					// If save returned a data URI, the file save failed — log it
					if (strpos($saved_url, 'data:') === 0) {
						error_log('[DS] ds_save_image_to_uploads failed for model: ' . $model . ' — staying as data URI');
						$saved_url = $img_src; // keep original, will show live but not persist
					}
					// R2-backed file? Recover its file_id from the proxy URL.
					$file_id = function_exists('ds_hub_extract_file_id_from_proxy_url')
						? (int) ds_hub_extract_file_id_from_proxy_url($saved_url) : 0;
					// Try to get attachment ID (only applies to local-disk saves —
					// R2 saves never write a local file, so this naturally stays 0).
					$aid = 0;
					$upload = wp_upload_dir();
					$fname = basename(parse_url($saved_url, PHP_URL_PATH));
					$fpath = $upload['path'] . '/' . $fname;
					if (file_exists($fpath)) {
						$att = array(
							'post_mime_type' => 'image/png',
							'post_title' => sanitize_text_field(substr($prompt, 0, 60)),
							'post_content' => '',
							'post_status' => 'inherit',
						);
						$aid = wp_insert_attachment($att, $fpath);
						if (!is_wp_error($aid)) {
							require_once ABSPATH . 'wp-admin/includes/image.php';
							$meta = wp_generate_attachment_metadata($aid, $fpath);
							wp_update_attachment_metadata($aid, $meta);
							update_post_meta($aid, 'ds_ai_prompt', $prompt);
							update_post_meta($aid, 'ds_ai_model', $model);
						} else {
							$aid = 0;
						}
					}
					$images_out[] = array('url' => $saved_url, 'attachment_id' => $aid, 'file_id' => $file_id);
				} else {
					$images_out[] = array('url' => $img_src, 'attachment_id' => 0, 'file_id' => 0);
				}
			}

			// Resolve the PENDING placeholders created before dispatch
			// (one per image, tagged with $sync_batch_id) into the real
			// result, IN PLACE. This (a) lets the resume-on-refresh poll
			// find them by exact prediction_id, and (b) stores one entry
			// per image — the gallery groups same-prompt/same-minute
			// entries itself and only ever reads $gen['url'], so an
			// extra_urls array on a single entry was invisible to it.
			$credits_after = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int)get_user_meta($uid, 'ds_ai_credits', true);
			$now = current_time('Y-m-d H:i:s');
			// Use actual model cost for history display even if free plan paid 0
			$each_cost = function_exists('ds_get_model_cost_each') ? ds_get_model_cost_each($model, $size) : ($costs[$model] ?? 4); // always record real model cost per image

			ds_ai_gens_update($uid, function($gens) use ($is_sync_model, $sync_batch_id, $images_out, $each_cost, $prompt, $model, $ratio, $now) {
				if ($is_sync_model && $sync_batch_id) {
					$idx = 0;
					foreach ($gens as $gi => $gexist) {
						if (($gexist['batch_id'] ?? '') !== $sync_batch_id) continue;
						if (isset($images_out[$idx])) {
							$gens[$gi]['url'] = $images_out[$idx]['url'] ?? '';
							$gens[$gi]['attachment_id'] = $images_out[$idx]['attachment_id'] ?? 0;
							$gens[$gi]['file_id'] = $images_out[$idx]['file_id'] ?? 0;
							$gens[$gi]['credits'] = $each_cost;
							$gens[$gi]['status'] = 'succeeded';
						} else {
							$gens[$gi]['status'] = 'failed';
						}
						$idx++;
					}
				} else {
					// Fallback — placeholders weren't created; insert fresh (one per image).
					$batch_id = uniqid('b_', true);
					foreach (array_reverse($images_out) as $img_out) {
						array_unshift($gens, array(
							'url' => $img_out['url'] ?? '',
							'attachment_id' => $img_out['attachment_id'] ?? 0,
							'file_id' => $img_out['file_id'] ?? 0,
							'prompt' => $prompt,
							'model' => $model,
							'ratio' => $ratio,
							'count' => 1,
							'credits' => $each_cost,
							'date' => $now,
							'status' => 'succeeded',
							'batch_id' => $batch_id,
						));
					}
				}
				return $gens;
			});

			return rest_ensure_response(array(
				'images' => $images_out,
				'credits_left' => $credits_after,
			));
		}

		// Batch async (Seedream multi-image) — return all pids to frontend
		if (!empty($result['prediction_ids']) && is_array($result['prediction_ids'])) {
			// Persist a PENDING entry per prediction_id server-side, right now —
			// don't rely on the client's follow-up save-generation call, which
			// can be lost if the page is refreshed before it fires.
			$now_async = current_time('Y-m-d H:i:s');
			ds_ai_gens_update($uid, function($gens_async) use ($result, $prompt, $model, $ratio, $cost_each, $now_async) {
				foreach ($result['prediction_ids'] as $async_pid) {
					array_unshift($gens_async, array(
						'prediction_id' => $async_pid,
						'prompt' => $prompt,
						'model' => $model,
						'ratio' => $ratio,
						'count' => 1,
						'credits' => $cost_each,
						'date' => $now_async,
						'status' => 'pending',
						'url' => '',
					));
				}
				return $gens_async;
			});
			$credits_after = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int)get_user_meta($uid, 'ds_ai_credits', true);
			return rest_ensure_response(array(
				'prediction_ids' => $result['prediction_ids'],
				'credits_left' => $credits_after,
			));
		}

		// Single async prediction
		if (!empty($result['prediction_id'])) {
			// Persist a PENDING entry server-side right now, before returning —
			// closes the refresh-loses-everything window down to this one
			// fast dispatch call instead of depending on the client's
			// follow-up save-generation request surviving a refresh.
			ds_ai_gens_update($uid, function($gens_async2) use ($result, $prompt, $model, $ratio, $count, $cost_each) {
				array_unshift($gens_async2, array(
					'prediction_id' => $result['prediction_id'],
					'prompt' => $prompt,
					'model' => $model,
					'ratio' => $ratio,
					'count' => $count,
					'credits' => $cost_each,
					'date' => current_time('Y-m-d H:i:s'),
					'status' => 'pending',
					'url' => '',
				));
				return $gens_async2;
			});
			// Deduct credits optimistically for async
			if ($total_cost > 0) {
				// already deducted above — don't double deduct
			}
			$credits_after = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int)get_user_meta($uid, 'ds_ai_credits', true);
			return rest_ensure_response(array(
				'prediction_id' => $result['prediction_id'],
				'credits_left' => $credits_after,
			));
		}

		return rest_ensure_response(array('error' => true, 'message' => 'No images returned.'));
	},
));

// ── Poll Image (Replicate async) ──────────────────────────────────────
register_rest_route('ds/v1', '/poll-image', array(
	'methods' => 'POST',
	'permission_callback' => function() { return is_user_logged_in(); },
	'callback' => function(WP_REST_Request $req) {
		$pid = sanitize_text_field($req->get_param('prediction_id') ?: '');
				$uid = get_current_user_id();

				// This prediction may already have been resolved and saved by an
				// earlier poll (this same call retried, an overlapping timer, or a
				// resumed tab racing the original). Serve straight from history
				// instead of touching Replicate/storage again — makes repeated or
				// concurrent polls for the same prediction fully idempotent and
				// avoids uploading duplicate files.
				if (strpos($pid, 'sync_') !== 0) {
					$gens_check = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
					$already = array();
					foreach ($gens_check as $gc) {
						if (is_array($gc) && ($gc['prediction_id'] ?? '') === $pid && ($gc['status'] ?? '') === 'succeeded' && !empty($gc['url'])) {
							$already[] = array('url' => $gc['url'], 'attachment_id' => $gc['attachment_id'] ?? 0, 'file_id' => $gc['file_id'] ?? 0);
						}
					}
					if (!empty($already)) {
						return rest_ensure_response(array('status' => 'succeeded', 'images' => $already));
					}
				}
		if (empty($pid)) {
			return rest_ensure_response(array('status' => 'error', 'error' => 'No prediction ID.'));
		}

		// ── Sync models (cloudflare/cloudflare-klein): no real Replicate job to
		// poll. The original request updates this same user-meta entry
		// in place when it finishes — just report its current status.
		if (strpos($pid, 'sync_') === 0) {
			$uid_sync = get_current_user_id();
			$gens_sync = get_user_meta($uid_sync, 'ds_ai_generations', true) ?: array();

			// Exact per-image match first (used when resuming after a refresh).
			foreach ($gens_sync as $gen_sync) {
				if (!is_array($gen_sync) || ($gen_sync['prediction_id'] ?? '') !== $pid) continue;
				if (($gen_sync['status'] ?? '') === 'succeeded' && !empty($gen_sync['url'])) {
					return rest_ensure_response(array(
						'status' => 'succeeded',
						'images' => array(array(
							'url' => $gen_sync['url'],
							'attachment_id' => $gen_sync['attachment_id'] ?? 0,
						)),
					));
				}
				if (($gen_sync['status'] ?? '') === 'failed') {
					return rest_ensure_response(array('status' => 'failed', 'error' => $gen_sync['error'] ?? 'Generation failed.'));
				}
				return rest_ensure_response(array('status' => 'processing'));
			}

			// Otherwise $pid is a whole-batch id — aggregate every
			// placeholder that shares it (used right after Generate).
			$batch_items = array();
			foreach ($gens_sync as $gen_sync) {
				if (is_array($gen_sync) && ($gen_sync['batch_id'] ?? '') === $pid) {
					$batch_items[] = $gen_sync;
				}
			}
			if (!empty($batch_items)) {
				$all_done = true;
				$any_ok = false;
				$out_images = array();
				foreach ($batch_items as $bi) {
					$st = $bi['status'] ?? 'pending';
					if ($st === 'pending') { $all_done = false; continue; }
					if ($st === 'succeeded' && !empty($bi['url'])) {
						$any_ok = true;
						$out_images[] = array('url' => $bi['url'], 'attachment_id' => $bi['attachment_id'] ?? 0);
					}
				}
				if (!$all_done) {
					return rest_ensure_response(array('status' => 'processing'));
				}
				if ($any_ok) {
					return rest_ensure_response(array('status' => 'succeeded', 'images' => $out_images));
				}
				return rest_ensure_response(array('status' => 'failed', 'error' => 'Generation failed.'));
			}

			return rest_ensure_response(array('status' => 'failed', 'error' => 'Generation no longer available.'));
		}

		if (!function_exists('ds_poll_replicate')) {
			return rest_ensure_response(array('status' => 'error', 'error' => 'Poll function not available.'));
		}
		// Long-poll: check Replicate every 3s for up to 12s server-side
		// before returning. This cuts JS round-trips by ~4× for slow
		// models like Seedream (which run 60-90s on Replicate).
		$result = ds_poll_replicate($pid);

				// Refund credits if the async job ultimately failed — ONCE per
		// prediction — and mark its pending history entry as failed so it
		// doesn't sit as a stuck "Processing..." card until the 1-hour
		// stale-cleanup sweep catches it. Both must only run on a REAL
		// terminal failure: doing this on every poll (including while the
		// job is still processing, before Replicate ever reports 'failed')
		// was wrongly flipping in-flight generations to a "failed" history
		// entry the moment the very first poll came back.
		if ( isset($result['status']) && $result['status'] === 'failed' ) {
			$uid = get_current_user_id();
			$lock = 'ds_refunded_' . md5( (string) $pid );

			// add_option() is atomic — option_name has a UNIQUE index, so this
			// returns false if the key exists. Reloads, extra tabs and concurrent
			// polls therefore cannot refund the same prediction twice.
			if ( add_option( $lock, time(), '', 'no' ) ) {
				$model = sanitize_text_field($req->get_param('model') ?: '');
				$count = max(1, min(4, (int)($req->get_param('count') ?: 1)));
				if (in_array($model, array('qwen-image','qwen-edit','nanobana'), true)) $count = 1;
				$psize = sanitize_text_field($req->get_param('image_size') ?: 'HD');
				$costs = function_exists('ds_get_model_costs') ? ds_get_model_costs() : array();
				$cost_each = function_exists('ds_get_model_cost_each') ? ds_get_model_cost_each($model, $psize) : ($costs[$model] ?? 4);
				$refund = $cost_each * $count;
				if ($refund > 0 && function_exists('ds_add_credits')) {
					ds_add_credits($uid, $refund);
					error_log("DS: refunded $refund credits to uid=$uid pid=$pid (failed prediction)");
				}
			} else {
				error_log("DS: refund SKIPPED for pid=$pid uid=$uid — already refunded");
			}

			ds_ai_gens_update($uid, function($gens_fail) use ($pid, $result) {
				foreach ($gens_fail as $gfi => $gf) {
					if (is_array($gf) && ($gf['prediction_id'] ?? '') === $pid && ($gf['status'] ?? '') === 'pending') {
						$gens_fail[$gfi]['status'] = 'failed';
						$gens_fail[$gfi]['error'] = $result['error'] ?? 'Generation failed.';
						break;
					}
				}
				return $gens_fail;
			});
		}
		if (!empty($result['images'])) {
				$prompt = sanitize_text_field($req->get_param('prompt') ?: '');
				$model = sanitize_text_field($req->get_param('model') ?: '');
				$ratio = sanitize_text_field($req->get_param('ratio') ?: '1:1');
				$image_size = sanitize_text_field($req->get_param('image_size') ?: 'HD');
				$req_pid = sanitize_text_field($req->get_param('prediction_id') ?: '');

				// Only ONE poll-image call may actually upload and persist this
				// prediction's result. Replicate keeps returning the same succeeded
				// payload on every subsequent poll, so without this atomic guard,
				// repeated/overlapping polls (two timers, a resumed tab racing the
				// original) would each re-upload a fresh duplicate file to storage
				// and append a duplicate history row.
				$save_lock = $req_pid ? ('ds_imgsaved_' . md5((string) $req_pid)) : '';
				if ($req_pid && !add_option($save_lock, time(), '', 'no')) {
					// Someone else is already saving this prediction (another timer,
					// a resumed tab racing the original). Wait briefly for their
					// write to land instead of reporting "succeeded" with an empty
					// image list — the client would treat that as done-but-broken.
					$found = array();
					for ($wait_i = 0; $wait_i < 4; $wait_i++) {
						usleep(500000);
						$gens_existing = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
						foreach ($gens_existing as $ge) {
							if (is_array($ge) && ($ge['prediction_id'] ?? '') === $req_pid && ($ge['status'] ?? '') === 'succeeded' && !empty($ge['url'])) {
								$found[] = array('url' => $ge['url'], 'attachment_id' => $ge['attachment_id'] ?? 0, 'file_id' => $ge['file_id'] ?? 0);
							}
						}
						if (!empty($found)) break;
					}
					if (!empty($found)) {
						return rest_ensure_response(array('status' => 'succeeded', 'images' => $found));
					}
					return rest_ensure_response(array('status' => 'processing'));
				}

				if (in_array($model, array('flux-schnell','flux-dev','flux-pro','hidream-fast','qwen-image','qwen-edit','seedream'), true) && strtoupper($image_size) === '4K' && function_exists('ds_upscale_image_urls_to_4k') && function_exists('ds_get_dims') && (!function_exists('ds_user_is_free_tier') || !ds_user_is_free_tier($uid))) {
					$ds_up = ds_upscale_image_urls_to_4k($result['images'], ds_get_dims($ratio, $image_size));
					if (empty($ds_up['error'])) {
						$result['images'] = $ds_up['images'];
					}
				}
				$images_out = array();
				foreach ($result['images'] as $img_src) {
					if (function_exists('ds_save_image_to_uploads')) {
						$saved_url = ds_save_image_to_uploads($img_src, $prompt);
						if (strpos($saved_url, 'data:') === 0) {
							$upload = wp_upload_dir();
							$fname = 'ds-ai-cf-' . time() . '-' . rand(100,999) . '.png';
							$fpath = $upload['path'] . '/' . $fname;
							$parts = explode(',', $saved_url, 2);
							$decoded = base64_decode($parts[1] ?? '');
							if (!empty($decoded) && strlen($decoded) > 500) {
								file_put_contents($fpath, $decoded);
								if (file_exists($fpath) && filesize($fpath) > 500) {
									$saved_url = $upload['url'] . '/' . $fname;
								}
							}
						}
						$file_id = function_exists('ds_hub_extract_file_id_from_proxy_url')
							? (int) ds_hub_extract_file_id_from_proxy_url($saved_url) : 0;
						$aid = 0;
						$upload2 = wp_upload_dir();
						$fname2 = basename(parse_url($saved_url, PHP_URL_PATH));
						$fpath2 = $upload2['path'] . '/' . $fname2;
						if (file_exists($fpath2)) {
							$att2 = array(
								'post_mime_type' => 'image/png',
								'post_title' => sanitize_text_field(substr($prompt, 0, 60)),
								'post_content' => '',
								'post_status' => 'inherit',
							);
							$aid = wp_insert_attachment($att2, $fpath2);
							if (!is_wp_error($aid)) {
								require_once ABSPATH . 'wp-admin/includes/image.php';
								wp_update_attachment_metadata($aid, wp_generate_attachment_metadata($aid, $fpath2));
								update_post_meta($aid, 'ds_ai_prompt', $prompt);
								update_post_meta($aid, 'ds_ai_model', $model);
								update_post_meta($aid, 'ds_ai_generated', true);
							} else { $aid = 0; }
						}
						if (!empty($saved_url) && strpos($saved_url, 'http') === 0) {
							$images_out[] = array('url' => $saved_url, 'attachment_id' => $aid, 'file_id' => $file_id);
						} else {
							error_log('[DS] Skipping invalid URL for model: ' . $model);
						}
					} else {
						$images_out[] = array('url' => $img_src, 'attachment_id' => 0, 'file_id' => 0);
					}
				}
				$count = max(1, min(4, (int)($req->get_param('count') ?: 1)));
				$now = current_time('Y-m-d H:i:s');
				$psize2 = sanitize_text_field($req->get_param('image_size') ?: 'HD');
				$costs = function_exists('ds_get_model_costs') ? ds_get_model_costs() : array();
				$cost_each = function_exists('ds_get_model_cost_each') ? ds_get_model_cost_each($model, $psize2) : ($costs[$model] ?? 4);
				$total = $cost_each * count($images_out);

				ds_ai_gens_update($uid, function($gens) use ($req_pid, $images_out, $prompt, $model, $ratio, $cost_each, $now) {
					if ($req_pid) {
						foreach ($gens as $gi => $gen) {
							if (is_array($gen)
								&& ($gen['prediction_id'] ?? '') === $req_pid
								&& (($gen['status'] ?? '') === 'pending' || empty($gen['url']))) {
								unset($gens[$gi]);
								break;
							}
						}
						$gens = array_values($gens);
					}

					$batch_id2 = $req_pid ?: uniqid('b_', true);
					foreach (array_reverse($images_out) as $img_out) {
						array_unshift($gens, array(
							'url' => $img_out['url'] ?? '',
							'attachment_id' => $img_out['attachment_id'] ?? 0,
							'file_id' => $img_out['file_id'] ?? 0,
							'prompt' => $prompt,
							'model' => $model,
							'ratio' => $ratio,
							'count' => 1,
							'credits' => $cost_each,
							'date' => $now,
							'status' => 'succeeded',
							'prediction_id' => $req_pid,
							'batch_id' => $batch_id2,
						));
					}
					return $gens;
				});

				return rest_ensure_response(array('status' => 'succeeded', 'images' => $images_out));
			}
			return rest_ensure_response($result);
	},
));

// ── Download proxy (legacy local-disk images only) ─────────────────────
register_rest_route('ds/v1', '/download', array(
	'methods' => 'GET',
	'permission_callback' => function() { return is_user_logged_in(); },
	'callback' => function(WP_REST_Request $req) {
		$url = esc_url_raw($req->get_param('url') ?: '');
		if (empty($url)) wp_die('No URL');
		// Allow legacy local-disk uploads, or our R2 hub proxy (resolved server-side to a
		// fresh presigned URL, since this loopback request carries no browser auth cookies).
		$up = wp_upload_dir();
		$fetch_url = null;
		if (strpos($url, $up['baseurl']) === 0) {
			$fetch_url = $url;
		} elseif (function_exists('ds_hub_get_generated_file') && preg_match('#/ds-hub/v1/file/(\d+)#', $url, $m)) {
			$file = ds_hub_get_generated_file((int) $m[1]);
			if (!$file || !ds_hub_user_owns_file($file, get_current_user_id())) wp_die('Invalid URL');
			$presigned = ds_hub_get_download_url($file);
			if (is_wp_error($presigned)) wp_die('File unavailable');
			$fetch_url = $presigned;
		}
		if (!$fetch_url) wp_die('Invalid URL');
		$resp = wp_remote_get($fetch_url, array('timeout' => 60, 'sslverify' => true));
		if (is_wp_error($resp)) wp_die('Download failed');
		$body = wp_remote_retrieve_body($resp);
		$ct = wp_remote_retrieve_header($resp, 'content-type') ?: 'image/png';
		header('Content-Type: ' . $ct);
		header('Content-Disposition: attachment; filename="designstager-ai-' . time() . '.png"');
		header('Content-Length: ' . strlen($body));
		echo $body;
		exit;
	},
));

// ── Delete Image ────────────────────────────────────────────────────────
// Previously missing: the existing "DS My Generated Images" front-end JS
// (dsmyDelete) already calls this endpoint; it just had no server-side
// route. Supports legacy local-attachment history entries (attachment_id)
// and new R2-backed entries (file_id). Never deletes a design that was
// accepted into the Marketplace. Per the storage-system requirement to
// never falsely report success, an R2 delete failure returns
// success:false with the real error and the history entry is kept.
register_rest_route('ds/v1', '/delete-image', array(
	'methods' => 'POST',
	'permission_callback' => function() { return is_user_logged_in(); },
	'callback' => function(WP_REST_Request $req) {
		$uid = get_current_user_id();
		$body = $req->get_json_params() ?: array();
		$file_id = (int)($body['file_id'] ?? $req->get_param('file_id') ?: 0);
		$aid = (int)($body['attachment_id'] ?? $req->get_param('attachment_id') ?: 0);

		if (!$file_id && !$aid) {
			return rest_ensure_response(array('success' => false, 'error' => 'No image specified.'));
		}

		$gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
		$match_idx = null;
		foreach ($gens as $gi => $g) {
			if (!is_array($g)) continue;
			$g_fid = (int)($g['file_id'] ?? 0);
			$g_aid = (int)($g['attachment_id'] ?? 0);
			if ($file_id && $g_fid === $file_id) { $match_idx = $gi; break; }
			if (!$file_id && $aid && $g_aid === $aid) { $match_idx = $gi; break; }
		}
		if ($match_idx === null) {
			return rest_ensure_response(array('success' => false, 'error' => 'Image not found in your history.'));
		}

		$gen = $gens[$match_idx];

		// Marketplace content must never be deleted from here — only from the
		// dedicated marketplace-management flow (and it never auto-expires).
		if (!empty($gen['submitted']) && !empty($gen['attachment_id'])) {
			$dl_post_id = (int) get_post_meta((int)$gen['attachment_id'], 'ds_marketplace_download_id', true);
			if ($dl_post_id && get_post_status($dl_post_id) === 'publish') {
				return rest_ensure_response(array('success' => false, 'error' => 'This design was accepted into the Marketplace and cannot be deleted here.'));
			}
		}

		$use_file_id = $file_id ?: (int)($gen['file_id'] ?? 0);

		if ($use_file_id && function_exists('ds_hub_get_generated_file')) {
			$file = ds_hub_get_generated_file($use_file_id);
			if (!$file || !ds_hub_user_owns_file($file, $uid)) {
				return rest_ensure_response(array('success' => false, 'error' => 'Not allowed.'));
			}
			$result = ds_hub_delete_generated_file_now($use_file_id);
			if (is_wp_error($result)) {
				// Never falsely report success — keep the DB row + history entry.
				return rest_ensure_response(array('success' => false, 'error' => $result->get_error_message()));
			}
		} else {
			// Legacy local-attachment entry.
			$legacy_aid = (int)($gen['attachment_id'] ?? $aid);
			if ($legacy_aid) {
				$att = get_post($legacy_aid);
				if ($att) {
					$owns = ((int)$att->post_author === (int)$uid) || current_user_can('manage_options');
					if (!$owns) {
						return rest_ensure_response(array('success' => false, 'error' => 'Not allowed.'));
					}
					wp_delete_attachment($legacy_aid, true);
				}
				// If the attachment row is already gone, fall through and just
				// clean up the history entry below.
			}
		}

		unset($gens[$match_idx]);
		update_user_meta($uid, 'ds_ai_generations', array_values($gens));

		return rest_ensure_response(array('success' => true));
	},
));
});
