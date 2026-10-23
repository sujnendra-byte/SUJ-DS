<?php
/**
 * Code Snippets export — DS AI Video — Custom Pricing (Kling 3.0 4K + LTX-2.3)
 *
 * Snippet ID : 803
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-117
 * Modified   : 2026-09-09 02:43:47
 * Load order : 122 of 144
 */
if ( ! defined('ABSPATH') ) exit;

/**
 * Snippet Name: DS AI Video — Custom Pricing (Kling 3.0 4K + LTX-2.3)
 * Run: Everywhere
 *
 * SELF-CONTAINED, purely additive — hooks the 'ds_video_cost_calculated'
 * filter that ds_get_video_cost() already applies at the end of its
 * calculation (see "Ds ai video api callers (R2 Storage) [Admin Credit
 * Settings]"). Does not edit that snippet, the REST route, or any other
 * existing snippet — every other model's price passes through unchanged.
 *
 * Kling 3.0 4K: WaveSpeed prices this at a flat $/sec regardless of audio
 * (confirmed via their docs — audio adds no extra cost). So price here is
 * purely duration-based, linearly interpolated between two anchor points:
 *   3s  = 136 credits
 *   15s = 660 credits
 *
 * LTX-2.3: WaveSpeed generates audio automatically with no on/off
 * parameter, so audio never changes price either. Price is duration-based
 * per resolution tier, linearly interpolated between two anchor points
 * per tier:
 *   480p:  5s = 15 credits,  20s = 50 credits
 *   720p:  5s = 20 credits,  20s = 70 credits
 *   1080p: 5s = 26 credits,  20s = 90 credits
 */

add_filter('ds_video_cost_calculated', function ($cost, $model, $resolution, $duration, $audio) {

    if ($model === 'kling-v3.0-4k') {
        $d = max(3, min(15, (int) $duration));
        $lo_d = 3; $lo_c = 136;
        $hi_d = 15; $hi_c = 660;
        $interp = $lo_c + ($d - $lo_d) * ($hi_c - $lo_c) / ($hi_d - $lo_d);
        return max(1, (int) ceil($interp));
    }

    if ($model === 'ltx-2.3') {
        $tiers = array(
            '480p'  => array('lo_d' => 5, 'lo_c' => 15, 'hi_d' => 20, 'hi_c' => 50),
            '720p'  => array('lo_d' => 5, 'lo_c' => 20, 'hi_d' => 20, 'hi_c' => 70),
            '1080p' => array('lo_d' => 5, 'lo_c' => 26, 'hi_d' => 20, 'hi_c' => 90),
        );
        $tier = isset($tiers[$resolution]) ? $tiers[$resolution] : $tiers['720p'];
        $d = max($tier['lo_d'], min($tier['hi_d'], (int) $duration));
        $interp = $tier['lo_c'] + ($d - $tier['lo_d']) * ($tier['hi_c'] - $tier['lo_c']) / ($tier['hi_d'] - $tier['lo_d']);
        return max(1, (int) ceil($interp));
    }

    return $cost; // every other model: unchanged, existing generic formula
}, 10, 5);
