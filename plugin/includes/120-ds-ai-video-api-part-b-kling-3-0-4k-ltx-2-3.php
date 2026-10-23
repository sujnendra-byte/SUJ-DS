<?php
/**
 * Code Snippets export — DS AI Video API - Part B (Kling 3.0 4K + LTX-2.3)
 *
 * Snippet ID : 801
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-115
 * Modified   : 2026-09-09 02:46:01
 * Load order : 120 of 144
 */
if ( ! defined('ABSPATH') ) exit;

/* ── Kling 3.0 4K (native 4K, no resolution field — the slug IS the 4K tier) ── */
if ( ! function_exists('ds_call_wavespeed_kling4k') ) {
    function ds_call_wavespeed_kling4k($prompt, $ratio = '16:9', $duration = 5, $audio = false, $start_image = null) {
        $key = function_exists('ds_video_get_wavespeed_key') ? ds_video_get_wavespeed_key() : '';
        if (empty($key)) return array('error' => 'WaveSpeed API key not set.');

        $payload = array(
            'prompt' => $prompt,
            'aspect_ratio' => in_array($ratio, array('16:9','9:16','1:1'), true) ? $ratio : '16:9',
            'duration' => max(3, min(15, (int)$duration)),
            'sound' => (bool)$audio,
        );
        if (!empty($start_image)) {
            $endpoint = 'https://api.wavespeed.ai/api/v3/kwaivgi/kling-v3.0-4k/image-to-video';
            $payload['image'] = $start_image;
        } else {
            $endpoint = 'https://api.wavespeed.ai/api/v3/kwaivgi/kling-v3.0-4k/text-to-video';
        }

        $resp = wp_remote_post($endpoint, array(
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

/* ── LTX-2.3 (confirmed via WaveSpeed docs: prompt, resolution, aspect_ratio, duration 5-20, seed) ── */
if ( ! function_exists('ds_call_wavespeed_ltx23') ) {
    function ds_call_wavespeed_ltx23($prompt, $ratio = '16:9', $duration = 5, $resolution = '720p', $seed = -1, $start_image = null) {
        $key = function_exists('ds_video_get_wavespeed_key') ? ds_video_get_wavespeed_key() : '';
        if (empty($key)) return array('error' => 'WaveSpeed API key not set.');

        $payload = array(
            'prompt' => $prompt,
            'resolution' => in_array($resolution, array('480p','720p','1080p'), true) ? $resolution : '720p',
            'aspect_ratio' => in_array($ratio, array('16:9','9:16'), true) ? $ratio : '16:9',
            'duration' => max(5, min(20, (int)$duration)),
            'seed' => (int)$seed,
        );
        if (!empty($start_image)) {
            $endpoint = 'https://api.wavespeed.ai/api/v3/wavespeed-ai/ltx-2.3/image-to-video';
            $payload['image'] = $start_image;
        } else {
            $endpoint = 'https://api.wavespeed.ai/api/v3/wavespeed-ai/ltx-2.3/text-to-video';
        }

        $resp = wp_remote_post($endpoint, array(
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

/* ── Dispatcher: routes by $model, falls back to your existing Seedance caller ── */
if ( ! function_exists('ds_call_wavespeed_video') ) {
    function ds_call_wavespeed_video($model, $prompt, $ratio, $duration, $resolution, $audio, $start_image = null, $end_image = null) {
        switch ($model) {
            case 'kling-v3.0-4k':
                return ds_call_wavespeed_kling4k($prompt, $ratio, $duration, $audio, $start_image);
            case 'ltx-2.3':
                return ds_call_wavespeed_ltx23($prompt, $ratio, $duration, $resolution, -1, $start_image);
            default:
                return function_exists('ds_call_wavespeed_seedance')
                    ? ds_call_wavespeed_seedance($prompt, $ratio, $duration, $resolution, $audio, $start_image, $end_image)
                    : array('error' => 'Video API not available.');
        }
    }
}

/* ── Default credit costs — only fills gaps, never overrides your DS Credit Settings ── */
add_filter('option_ds_video_credit_settings', function ($value) {
    if (!is_array($value)) $value = array();
    if (!isset($value['models']) || !is_array($value['models'])) $value['models'] = array();
    if (!isset($value['models']['kling-v3.0-4k'])) $value['models']['kling-v3.0-4k'] = 65; // $0.42/sec on WaveSpeed
    if (!isset($value['models']['ltx-2.3'])) $value['models']['ltx-2.3'] = 6; // $0.15 @720p/5s on WaveSpeed
    return $value;
});
