<?php
/**
 * Code Snippets export — DS AI Image Persistence
 *
 * Snippet ID : 714
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-028
 * Modified   : 2026-09-09 02:44:29
 * Load order : 028 of 144
 */
/**
 * Snippet Name: DS AI Image Persistence
 * Description: Saves pending image jobs immediately on generation,
 *              and updates them with real URLs when polling succeeds.
 *              Works alongside DS AI Images snippet automatically.
 */

// ââ Concurrency guard for ds_ai_generations (see DS AI Image REST for
// full rationale) â idempotent so it's safe regardless of which of the
// two snippets happens to load first.
if (!function_exists('ds_ai_gens_lock')) {
	function ds_ai_gens_lock($uid, $wait_ms = 4000) {
		$key = 'ds_gens_lock_' . $uid;
		$start = microtime(true);
		while (true) {
			if (add_option($key, time(), '', 'no')) return true;
			$held_since = (int) get_option($key);
			if ($held_since && (time() - $held_since) > 15) {
				delete_option($key);
				continue;
			}
			if ((microtime(true) - $start) * 1000 > $wait_ms) return false;
			usleep(100000);
		}
	}
	function ds_ai_gens_unlock($uid) {
		delete_option('ds_gens_lock_' . $uid);
	}
	function ds_ai_gens_update($uid, $mutator) {
		$locked = ds_ai_gens_lock($uid);
		$gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
		$gens = call_user_func($mutator, $gens);
		update_user_meta($uid, 'ds_ai_generations', array_slice($gens, 0, 200));
		if ($locked) ds_ai_gens_unlock($uid);
		return $gens;
	}
}

// ============================================================
// 1. HOOK: Save PENDING entry immediately when job is queued
// ============================================================
add_filter('ds_ai_generation_queued', 'ds_persist_pending_generation', 10, 2);
function ds_persist_pending_generation($prediction_id, $args) {
    $uid = get_current_user_id();
    if (!$uid) return $prediction_id;

    $pending = [
        'prediction_id' => $prediction_id,
        'prompt'        => sanitize_text_field($args['prompt']    ?? ''),
        'model'         => sanitize_text_field($args['model']     ?? ''),
        'ratio'         => sanitize_text_field($args['ratio']     ?? '1:1'),
        'style'         => sanitize_text_field($args['style']     ?? ''),
        'image_size'    => sanitize_text_field($args['image_size'] ?? 'HD'),
        'date'          => current_time('mysql'),
        'status'        => 'pending',
        'url'           => '',
        'attachment_id' => 0,
    ];

    ds_ai_gens_update($uid, function($generations) use ($pending) {
        array_unshift($generations, $pending);
        return $generations;
    });

    return $prediction_id;
}


// ============================================================
// 2. HOOK: Update entry with real URL when poll succeeds
// ============================================================
add_action('ds_ai_generation_succeeded', 'ds_persist_resolved_generation', 10, 3);
function ds_persist_resolved_generation($prediction_id, $images, $args) {
    $uid = get_current_user_id();
    if (!$uid) return;

    ds_ai_gens_update($uid, function($generations) use ($prediction_id, $images, $args) {
        $found = false;

        foreach ($generations as $i => $gen) {
            if (!is_array($gen)) continue;
            if (($gen['prediction_id'] ?? '') !== $prediction_id) continue;

            // Found the pending entry â update it with first image
            $first_img     = is_array($images[0] ?? null) ? $images[0] : ['url' => $images[0] ?? ''];
            $generations[$i] = array_merge($gen, [
                'url'           => $first_img['url']           ?? '',
                'attachment_id' => $first_img['attachment_id'] ?? 0,
                'status'        => 'succeeded',
            ]);

            // Store extra image URLs in the same entry â no separate entries
            if (count($images) > 1) {
                $extra_urls = [];
                $extra_aids = [];
                foreach (array_slice($images, 1) as $img) {
                    $img_data     = is_array($img) ? $img : ['url' => $img];
                    $extra_urls[] = $img_data['url']           ?? '';
                    $extra_aids[] = $img_data['attachment_id'] ?? 0;
                }
                $generations[$i]['extra_urls'] = $extra_urls;
                $generations[$i]['extra_aids'] = $extra_aids;
                $generations[$i]['count']      = count($images);
            }

            $found = true;
            break;
        }

        // Fallback: prediction_id not found â insert ONE combined entry
        if (!$found) {
            $first    = is_array($images[0] ?? null) ? $images[0] : ['url' => $images[0] ?? ''];
            $ex_urls  = [];
            $ex_aids  = [];
            foreach (array_slice($images, 1) as $img) {
                $img_data = is_array($img) ? $img : ['url' => $img];
                $ex_urls[] = $img_data['url']           ?? '';
                $ex_aids[] = $img_data['attachment_id'] ?? 0;
            }
            array_unshift($generations, [
                'prediction_id' => $prediction_id,
                'prompt'        => sanitize_text_field($args['prompt']    ?? ''),
                'model'         => sanitize_text_field($args['model']     ?? ''),
                'ratio'         => sanitize_text_field($args['ratio']     ?? '1:1'),
                'style'         => sanitize_text_field($args['style']     ?? ''),
                'image_size'    => sanitize_text_field($args['image_size'] ?? 'HD'),
                'date'          => current_time('mysql'),
                'status'        => 'succeeded',
                'url'           => $first['url']           ?? '',
                'attachment_id' => $first['attachment_id'] ?? 0,
                'extra_urls'    => $ex_urls,
                'extra_aids'    => $ex_aids,
                'count'         => count($images),
            ]);
        }

        return $generations;
    });
}


// ============================================================
// 3. HOOK: Mark entry as FAILED if poll fails
// ============================================================
add_action('ds_ai_generation_failed', 'ds_persist_failed_generation', 10, 2);
function ds_persist_failed_generation($prediction_id, $error_msg) {
    $uid = get_current_user_id();
    if (!$uid) return;

    ds_ai_gens_update($uid, function($generations) use ($prediction_id, $error_msg) {
        foreach ($generations as $i => $gen) {
            if (!is_array($gen)) continue;
            if (($gen['prediction_id'] ?? '') !== $prediction_id) continue;

            $generations[$i]['status'] = 'failed';
            $generations[$i]['error']  = sanitize_text_field($error_msg);
            break;
        }
        return $generations;
    });
}


// ============================================================
// 4. CLEANUP: Remove failed/empty pending entries older than 1 hour
//    Runs on page load for logged-in users (lightweight)
// ============================================================
add_action('init', 'ds_cleanup_stale_pending_generations');
function ds_cleanup_stale_pending_generations() {
    if (!is_user_logged_in()) return;

    // Only run 1 in 10 page loads to keep it light
    if (rand(1, 10) !== 1) return;

    $uid         = get_current_user_id();
    $cutoff      = strtotime('-1 hour');
    // Sync models (cloudflare/Cloudflare FLUX.2 Klein 9B) finish in well under 2 minutes;
    // if one is still pending after 3, something genuinely went wrong â
    // don't make the user wait up to an hour to see it clear.
    $sync_cutoff = strtotime('-3 minutes');

    // Cheap unlocked pre-check so the other 9-in-10 loads (and the common
    // case where nothing is actually stale) never take the write lock.
    $peek        = get_user_meta($uid, 'ds_ai_generations', true) ?: [];
    $would_change = false;
    foreach ($peek as $gen) {
        if (!is_array($gen)) continue;
        if (($gen['status'] ?? 'succeeded') !== 'pending') continue;
        if (empty($gen['url'])) {
            $is_sync      = strpos($gen['prediction_id'] ?? '', 'sync_') === 0;
            $entry_cutoff = $is_sync ? $sync_cutoff : $cutoff;
            if (strtotime($gen['date'] ?? '') < $entry_cutoff) { $would_change = true; break; }
        }
    }
    if (!$would_change) return;

    ds_ai_gens_update($uid, function($generations) use ($cutoff, $sync_cutoff) {
        foreach ($generations as $i => $gen) {
            if (!is_array($gen)) continue;
            if (($gen['status'] ?? 'succeeded') !== 'pending') continue;
            if (empty($gen['url'])) {
                $is_sync      = strpos($gen['prediction_id'] ?? '', 'sync_') === 0;
                $entry_cutoff = $is_sync ? $sync_cutoff : $cutoff;
                if (strtotime($gen['date'] ?? '') < $entry_cutoff) {
                    $generations[$i]['status'] = 'failed';
                }
            }
        }
        return $generations;
    });
}


// ============================================================
// 5. REST ENDPOINT: dedicated save endpoint, called directly
//    by the front-end JS when a job is queued / resolves / fails
// ============================================================
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/save-generation', [
        'methods'             => 'POST',
        'callback'            => 'ds_save_generation_endpoint',
        'permission_callback' => function() { return is_user_logged_in(); },
    ]);
});

function ds_save_generation_endpoint(WP_REST_Request $request) {
    $body   = $request->get_json_params() ?: [];
    $status = $body['status'] ?? '';

    // Only handle pending and failed â succeeded is handled by DS AI Image REST
    // to prevent duplicate history entries
    if ($status === 'pending') {
        ds_persist_pending_generation($body['prediction_id'] ?? '', $body);
    } elseif ($status === 'failed') {
        ds_persist_failed_generation($body['prediction_id'] ?? '', $body['error'] ?? 'Failed');
    }
    // DO NOT save succeeded here â REST endpoint already saves it

    return rest_ensure_response(['saved' => true]);
}
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/pending-generations', array(
        'methods'             => 'GET',
        'callback'            => function() {
            $uid  = get_current_user_id();
            $gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
            $cutoff = time() - 82800; // resume if within 23 hours (Replicate keeps results 24h)
            $pending = array();
            foreach ($gens as $g) {
                if (!is_array($g)) continue;
                if (($g['status'] ?? '') !== 'pending') continue;
                if (empty($g['prediction_id'])) continue;
                if (strtotime($g['date'] ?? '') < $cutoff) continue;
                $pending[] = $g;
            }
            return rest_ensure_response(array('pending' => $pending));
        },
        'permission_callback' => function() { return is_user_logged_in(); },
    ));
});
