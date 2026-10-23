<?php
/**
 * Code Snippets export — DS AI Video REST (R2 Storage) — my-videos Load More pagination + Expired Filter [CLONE]
 *
 * Snippet ID : 804
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-118
 * Modified   : 2026-09-09 02:44:51
 * Load order : 123 of 144
 */
/**
 * Snippet Name: DS AI Video REST
 * Run: Everywhere
 * Registers POST /ds/v1/generate-video and POST /ds/v1/poll-video.
 * Mirrors "DS AI Image REST" (generate-image / poll-image) exactly:
 * same credit functions (ds_get_credits / ds_deduct_credits / ds_add_credits),
 * same shared credit pool (ds_ai_credits), same /ds/v1/download proxy reused
 * as-is for video files.
 *
 * Requires: DS AI Video API - Part A (ds_call_wavespeed_seedance, ds_poll_wavespeed,
 * ds_save_video_to_uploads, ds_get_video_cost, ds_video_ref_to_url).
 */

if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function() {

    // ── Generate Video ──────────────────────────────────────────────────
    register_rest_route('ds/v1', '/generate-video', array(
        'methods'             => 'POST',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function(WP_REST_Request $req) {

            @ignore_user_abort(true);
            @set_time_limit(60); // submitting a task is fast; the actual render happens async on WaveSpeed

            $uid = get_current_user_id();

            $model       = sanitize_text_field($req->get_param('model') ?: 'seedance-1.5-pro');
            $prompt      = sanitize_textarea_field($req->get_param('prompt') ?: '');
            $ratio       = sanitize_text_field($req->get_param('ratio') ?: '16:9');
            if ($model === 'kling-v3.0-4k') {
                $duration = max(3, min(15, (int)($req->get_param('duration') ?: 5)));
            } elseif ($model === 'ltx-2.3') {
                $duration = max(5, min(20, (int)($req->get_param('duration') ?: 5)));
            } else {
                $duration = max(4, min(12, (int)($req->get_param('duration') ?: 5)));
            }
            $resolution  = sanitize_text_field($req->get_param('resolution') ?: '1080p');
            $audio       = filter_var($req->get_param('audio'), FILTER_VALIDATE_BOOLEAN);
            $start_image = $req->get_param('start_image') ?: null;
            $end_image   = $req->get_param('end_image') ?: null;

            if (empty($prompt)) {
                return rest_ensure_response(array('error' => true, 'message' => 'Prompt is required.'));
            }
            if (!in_array($resolution, array('480p', '720p', '1080p'), true)) $resolution = '1080p';

            // ── Credit check ──────────────────────────────────────────────
            $cost = function_exists('ds_get_video_cost') ? ds_get_video_cost($model, $resolution, $duration, $audio) : 15;
            $credits = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int)get_user_meta($uid, 'ds_ai_credits', true);
            if ($credits < $cost) {
                return rest_ensure_response(array('error' => true, 'message' => 'Not enough credits. Need ' . $cost . '.'));
            }

            if (!function_exists('ds_call_wavespeed_seedance')) {
                return rest_ensure_response(array('error' => true, 'message' => 'Video API snippet not active.'));
            }

            // ── Resolve reference images (data URI -> uploaded URL) ────────
            $start_url = $start_image ? (function_exists('ds_video_ref_to_url') ? ds_video_ref_to_url($start_image) : $start_image) : null;
            $end_url   = $end_image   ? (function_exists('ds_video_ref_to_url') ? ds_video_ref_to_url($end_image)   : $end_image)   : null;

            $result = ds_call_wavespeed_video($model, $prompt, $ratio, $duration, $resolution, $audio, $start_url, $end_url);

            if (!empty($result['error'])) {
                return rest_ensure_response(array('error' => true, 'message' => $result['error']));
            }
            if (empty($result['prediction_id'])) {
                return rest_ensure_response(array('error' => true, 'message' => 'No task ID returned.'));
            }

            // ── Deduct credits ONLY after WaveSpeed accepted the task ──────
            ds_deduct_credits($uid, $cost);

            // ── Save a PENDING placeholder so a mid-generation refresh
            //    can still find and resolve this job (same pattern as images).
            $gens = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
            $now  = current_time('Y-m-d H:i:s');
            array_unshift($gens, array(
                'prediction_id' => $result['prediction_id'],
                'poll_url'      => $result['poll_url'] ?? '',
                'prompt'        => $prompt,
                'model'         => $model,
                'ratio'         => $ratio,
                'duration'      => $duration,
                'resolution'    => $resolution,
                'audio'         => $audio,
                'credits'       => $cost,
                'date'          => $now,
                'status'        => 'pending',
                'url'           => '',
            ));
            update_user_meta($uid, 'ds_ai_video_generations', array_slice($gens, 0, 200));

            $credits_after = function_exists('ds_get_credits') ? ds_get_credits($uid) : ($credits - $cost);
            return rest_ensure_response(array(
                'prediction_id' => $result['prediction_id'],
                'credits_left'  => $credits_after,
            ));
        },
    ));

    // ── Poll Video (WaveSpeed async) ────────────────────────────────────
    register_rest_route('ds/v1', '/poll-video', array(
        'methods'             => 'POST',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function(WP_REST_Request $req) {
            $pid = sanitize_text_field($req->get_param('prediction_id') ?: '');
            if (empty($pid)) {
                return rest_ensure_response(array('status' => 'error', 'error' => 'No task ID.'));
            }
            if (!function_exists('ds_poll_wavespeed')) {
                return rest_ensure_response(array('status' => 'error', 'error' => 'Poll function not available.'));
            }

            $uid = get_current_user_id();

            // Use the exact result URL WaveSpeed gave us at submit time, if
            // we have it — the guessed /predictions/{id}/result pattern is
            // only a fallback and can silently 404 on some accounts, which
            // makes generations look stuck forever even though WaveSpeed
            // finished them minutes ago.
            $poll_url = '';
            $gens_lookup = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
            foreach ($gens_lookup as $gl) {
                if (($gl['prediction_id'] ?? '') === $pid) { $poll_url = $gl['poll_url'] ?? ''; break; }
            }

            $result = ds_poll_wavespeed($pid, $poll_url ?: null);

            // ── Refund credits if the job ultimately failed — ONCE per task ──
            if (isset($result['status']) && $result['status'] === 'failed') {
                $lock = 'ds_video_refunded_' . md5((string)$pid);
                if (add_option($lock, time(), '', 'no')) {
                    $gens = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
                    $refund = 0;
                    foreach ($gens as $gi => $g) {
                        if (($g['prediction_id'] ?? '') === $pid) {
                            $refund = (int)($g['credits'] ?? 0);
                            $gens[$gi]['status'] = 'failed';
                            $gens[$gi]['error']  = $result['error'] ?? 'Generation failed.';
                            break;
                        }
                    }
                    update_user_meta($uid, 'ds_ai_video_generations', $gens);
                    if ($refund > 0 && function_exists('ds_add_credits')) {
                        ds_add_credits($uid, $refund);
                    }
                }
                return rest_ensure_response($result);
            }

            if (!empty($result['videos'])) {
                $prompt = sanitize_textarea_field($req->get_param('prompt') ?: '');
                $model  = sanitize_text_field($req->get_param('model') ?: 'seedance-1.5-pro');

                // Only run this once per task, even if two polls race each
                // other in — otherwise both would schedule a rehost.
                $already_done = false;
                $gens = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
                foreach ($gens as $g) {
                    if (($g['prediction_id'] ?? '') === $pid && ($g['status'] ?? '') === 'succeeded') { $already_done = true; break; }
                }

                $video_src = function_exists('ds_video_output_to_url') ? ds_video_output_to_url($result['videos'][0]) : $result['videos'][0];

                if (!$already_done && !empty($video_src)) {
                    // Respond with WaveSpeed's own URL immediately — no file
                    // download here, so this can't time out or 500. The
                    // heavy download + media-library save happens in the
                    // background afterwards (see ds_video_rehost_background).
                    $found = false;
                    foreach ($gens as $gi => $g) {
                        if (($g['prediction_id'] ?? '') === $pid) {
                            $gens[$gi]['url']           = $video_src;
                            $gens[$gi]['attachment_id'] = 0;
                            $gens[$gi]['status']        = 'succeeded';
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        array_unshift($gens, array(
                            'prediction_id' => $pid,
                            'url'           => $video_src,
                            'attachment_id' => 0,
                            'prompt'        => $prompt,
                            'model'         => $model,
                            'status'        => 'succeeded',
                            'date'          => current_time('Y-m-d H:i:s'),
                        ));
                    }
                    update_user_meta($uid, 'ds_ai_video_generations', array_slice($gens, 0, 200));

                    if (!wp_next_scheduled('ds_video_rehost', array($uid, $pid, $video_src, $prompt))) {
                        wp_schedule_single_event(time(), 'ds_video_rehost', array($uid, $pid, $video_src, $prompt));
                    }
                }

                return rest_ensure_response(array(
                    'status' => 'succeeded',
                    'videos' => array(array('url' => $video_src, 'attachment_id' => 0)),
                ));
            }

            return rest_ensure_response($result); // status: processing
        },
    ));

    // ── Download Video ──────────────────────────────────────────────────
    register_rest_route('ds/v1', '/download-video', array(
        'methods'             => 'GET',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function(WP_REST_Request $req) {
            $fid_param = (int) ($req->get_param('file_id') ?: 0);
            $uid = get_current_user_id();

            /* A video that has just been generated: its card still holds the first
               link (the AI provider's). A moment later the video is copied to our own
               storage and the saved link changes, so that first link no longer matched
               and the download failed ("Not your video", HTTP 500) until the page was
               reloaded. The card now sends the generation id: look the video up by it,
               in this user's own list, and use whatever is stored for it right now. */
            $dsv_src = (string) ($req->get_param('url') ?: '');
            $dsv_pid = sanitize_text_field((string) ($req->get_param('prediction_id') ?: ''));
            if (!$fid_param && '' !== $dsv_pid) {
                foreach ((array) (get_user_meta($uid, 'ds_ai_video_generations', true) ?: array()) as $dsv_g) {
                    if (is_array($dsv_g) && ($dsv_g['prediction_id'] ?? '') === $dsv_pid) {
                        if (!empty($dsv_g['file_id'])) { $fid_param = (int) $dsv_g['file_id']; }
                        elseif (!empty($dsv_g['url'])) { $dsv_src = (string) $dsv_g['url']; }
                        break;
                    }
                }
            }
            /* a storage link (the ds-hub file route with its ID) carries the file id */
            if (!$fid_param && preg_match('#/ds-hub/v1/file/(\\d+)#', $dsv_src, $dsv_hub_m)) {
                $fid_param = (int) $dsv_hub_m[1];
            }

            if ($fid_param && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_user_owns_file') && function_exists('ds_hub_get_download_url')) {
                $f = ds_hub_get_generated_file($fid_param);
                if (!$f || !ds_hub_user_owns_file($f, $uid)) { status_header(403); wp_die('Not your video.'); }
                $resolved = ds_hub_get_download_url($f);
                if (is_wp_error($resolved)) { status_header(404); wp_die('File not available.'); }
                $url = esc_url_raw($resolved);
                $owns_it = true;
            } else {
                $url = esc_url_raw($dsv_src);
                if (empty($url)) { status_header(400); wp_die('No URL'); }
                $gens  = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
                $owns_it = false;
                foreach ($gens as $g) {
                    if (($g['url'] ?? '') === $url) { $owns_it = true; break; }
                }
            }
            if (empty($url)) { status_header(400); wp_die('No URL'); }
            if (!$owns_it) { status_header(403); wp_die('Not your video.'); }

            $resp = wp_remote_get($url, array('timeout' => 100, 'sslverify' => true));
            if (is_wp_error($resp)) { status_header(502); wp_die('Download failed: ' . $resp->get_error_message()); }
            $code = wp_remote_retrieve_response_code($resp);
            if ($code < 200 || $code >= 300) { status_header($code); wp_die('Download failed (HTTP ' . $code . ').'); }

            $body = wp_remote_retrieve_body($resp);
            $ct   = wp_remote_retrieve_header($resp, 'content-type') ?: 'video/mp4';
            nocache_headers();
            header('Content-Type: ' . $ct);
            header('Content-Disposition: attachment; filename="designstager-ai-video-' . time() . '.mp4"');
            header('Content-Length: ' . strlen($body));
           echo $body;
            exit;
        },
    ));

    // ── Rehost Video (synchronous fallback for hosts where WP-Cron never
    // fires) ────────────────────────────────────────────────────────────
    // The original design scheduled the download via wp_schedule_single_event
    // + WP-Cron so it wouldn't block the poll-video response. On some hosts
    // WP-Cron's pseudo-cron never actually runs the pending event (disabled,
    // blocked loopback requests, real server cron pointed elsewhere, etc.),
    // so the video stays on WaveSpeed's CDN forever and attachment_id never
    // gets set — which also means the "Submit to Marketplace" button never
    // appears, since that button requires a real attachment_id.
    // This route does the exact same download+save work, but is called
    // directly over plain REST (the same transport generate-video/poll-video
    // already use successfully on this site), fired as a fire-and-forget
    // request from the browser right after a generation succeeds. It does
    // NOT depend on WP-Cron at all, so it works regardless of whether cron
    // is reliable on this host.
    register_rest_route('ds/v1', '/rehost-video', array(
        'methods'             => 'POST',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function(WP_REST_Request $req) {
            @ignore_user_abort(true);
            @set_time_limit(120);

            $uid = get_current_user_id();
            $pid = sanitize_text_field($req->get_param('prediction_id') ?: '');
            if (!$pid) {
                return new WP_Error('missing_pid', 'Missing prediction_id.', array('status' => 400));
            }

            $gens  = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
            $entry = null;
            foreach ($gens as $g) {
                if (($g['prediction_id'] ?? '') === $pid) { $entry = $g; break; }
            }
            if (!$entry) {
                return new WP_Error('not_found', 'Generation not found.', array('status' => 404));
            }

            // Already rehosted (by this route or, if it ever fires, by cron) — no-op.
            if (!empty($entry['attachment_id']) || !empty($entry['file_id'])) {
                return rest_ensure_response(array(
                    'success'       => true,
                    'already_done'  => true,
                    'attachment_id' => (int) $entry['attachment_id'],
                    'file_id'       => (int) ($entry['file_id'] ?? 0),
                    'url'           => $entry['url'] ?? '',
                ));
            }

            $remote_url = $entry['url'] ?? '';
            $prompt     = $entry['prompt'] ?? '';
            if (empty($remote_url) || !function_exists('ds_video_rehost_background')) {
                return new WP_Error('cannot_rehost', 'Nothing to rehost.', array('status' => 400));
            }

            // Temporary diagnostic wrap: report the real PHP error instead of
            // WordPress's generic "critical error" page, so we can see what's
            // actually failing inside the download/save step.
            try {
                ds_video_rehost_background($uid, $pid, $remote_url, $prompt);
            } catch (\Throwable $e) {
                return new WP_Error('rehost_exception', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), array('status' => 500));
            }

            // Re-read after the save to report what actually happened.
            $gens2 = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
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
                'success'       => (bool) ($aid || $fid),
                'attachment_id' => $aid,
                'file_id'        => $fid,
                'url'           => $url,
            ));
        },
    ));

    // ── One-time backfill for videos generated before the rehost fix ─────
    // Those entries succeeded but never got an attachment_id (WP-Cron never
    // ran, and/or the media.php include was missing), so they're stuck
    // pointing at WaveSpeed's CDN forever and can never show the
    // marketplace button. This processes a small batch per call (so it
    // can't time out on slower hosts) — the frontend calls it repeatedly
    // until nothing is left.
    register_rest_route('ds/v1', '/rehost-backfill', array(
        'methods'             => 'POST',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function(WP_REST_Request $req) {
            @ignore_user_abort(true);
            @set_time_limit(150);

            $uid  = get_current_user_id();
            $gens = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();

            $pending = array();
            foreach ($gens as $g) {
                if (($g['status'] ?? '') === 'succeeded' && empty($g['attachment_id']) && empty($g['file_id']) && !empty($g['url'])) {
                    $pending[] = $g;
                }
            }

            $batch = array_slice($pending, 0, 2); // keep each request short
            $done  = 0;
            if (function_exists('ds_video_rehost_background')) {
                foreach ($batch as $g) {
                    try {
                        ds_video_rehost_background($uid, $g['prediction_id'] ?? '', $g['url'], $g['prompt'] ?? '');
                        $done++;
                    } catch (\Throwable $e) {
                        // Skip this one (e.g. its CDN URL may have expired by now); keep going.
                    }
                }
            }

            return rest_ensure_response(array(
                'success'   => true,
                'processed' => $done,
                'remaining' => max(0, count($pending) - $done),
                'total'     => count($pending),
            ));
        },
        ));

	register_rest_route( 'ds/v1', '/my-videos', array(
		'methods'              => 'GET',
		'permission_callback'  => function() { return is_user_logged_in(); },
		'callback'             => function( WP_REST_Request $req ) {
			$uid  = get_current_user_id();
			$gens = get_user_meta( $uid, 'ds_ai_video_generations', true ) ?: array();
			$out  = array();
			foreach ( $gens as $i => $gen ) {
				if ( ! is_array( $gen ) ) continue;
				$url      = $gen['url'] ?? '';
				$status   = $gen['status'] ?? 'succeeded';
				$file_id  = (int) ( $gen['file_id'] ?? 0 );
				$aid      = (int) ( $gen['attachment_id'] ?? 0 );
				if ( empty($url) && $status !== 'pending' ) continue;
				$needs_fix = $file_id && strpos( (string) $url, 'ds-hub/v1/file' ) !== false;
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
					'key'         => $file_id ? ('f'.$file_id) : ('i'.$i),
					'url'         => $url,
					'file_id'     => $file_id,
					'attachment_id' => $aid,
					'status'      => $status,
					'prompt'      => $gen['prompt'] ?? '',
					'model'       => $gen['model'] ?? '',
					'ratio'       => $gen['ratio'] ?? '16:9',
					'duration'    => $gen['duration'] ?? 5,
					'date'        => $gen['date'] ?? '',
					'needs_fix'   => $needs_fix,
					'exp_badge'   => $exp_badge,
					'is_expired'  => $is_expired,
				);
			}
			usort( $out, function( $a, $b ) { return strcmp( (string) $b['date'], (string) $a['date'] ); } );
			$total = count( $out );
			$offset = max( 0, (int) $req->get_param('offset') );
			$limit = (int) $req->get_param('limit');
			if ( $limit <= 0 ) $limit = 20;
			$out = array_slice( $out, $offset, $limit );
			return rest_ensure_response( array( 'success' => true, 'videos' => $out, 'total' => $total, 'next_offset' => $offset + count( $out ) ) );
		},
	) );
});
