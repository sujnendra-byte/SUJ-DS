<?php
/**
 * Code Snippets export — DS Marketplace – Daily Download Limit API — Free IP cap 5/day
 *
 * Snippet ID : 753
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-067
 * Modified   : 2026-10-16 02:04:25 (V41 update from staging)
 * Load order : 067 of 144
 */
/**
 * Snippet Name: DS Marketplace – Daily Download Limit API
 * Run: Everywhere
 * Description: REST endpoint POST /wp-json/ds/v1/dl-check
 *              Checks + records daily download counts per user.
 *              Called by the marketplace JS before each download.
 */

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/dl-check', array(
        'methods'             => 'POST',
        'callback'            => 'ds_dl_check_handler',
        'permission_callback' => '__return_true',
    ));
    register_rest_route('ds/v1', '/dl-status', array(
        'methods'             => 'GET',
        'callback'            => 'ds_dl_status_handler',
        'permission_callback' => '__return_true',
    ));
});

if ( ! function_exists('ds_dl_check_core') ) {
function ds_dl_check_core( $uid, $item_tier, $commit = true ) {
    $item_tier = in_array($item_tier, array('free', 'pro'), true) ? $item_tier : 'free';
    if ( ! $uid ) return array('ok' => false, 'reason' => 'not_logged_in');

    $user  = get_userdata($uid);
    $roles = $user ? (array) $user->roles : array();
    $rs    = implode(',', $roles);

    $tier = 'free';
    if (in_array('administrator', $roles) || in_array('editor', $roles) || in_array('business_member', $roles) || strpos($rs, 'business') !== false) {
        $tier = 'business';
    } elseif (in_array('pro_member', $roles) || strpos($rs, '_pro') !== false) {
        $tier = 'pro';
    } elseif (in_array('premium_member', $roles) || strpos($rs, 'premium') !== false) {
        $tier = 'premium';
    }
    /* No paid role (plan ended): the last paid plan still applies while the user holds enough credits. */
    if ($tier === 'free' && function_exists('ds_access_plan')) {
        $ap = ds_access_plan($uid);
        if (in_array($ap, array('premium', 'pro', 'business'), true)) $tier = $ap;
    }

    $limits = array(
        'free'     => array('period' => 'day',   'free' => 5,    'pro' => 0),
        'premium'  => array('period' => 'month', 'free' => 500,  'pro' => 50),
        'pro'      => array('period' => 'month', 'free' => 1000, 'pro' => 200),
        'business' => array('period' => 'month', 'free' => -1,   'pro' => 600),
    );
    $lim    = isset($limits[$tier]) ? $limits[$tier] : $limits['free'];
    $period = $lim['period'];
    $cap    = $lim[$item_tier];

    if ($cap === 0) {
        return array('ok' => false, 'reason' => 'upgrade_required');
    }
    if ($cap === -1) { if ($commit && function_exists('ds_ua_log_dl')) ds_ua_log_dl($uid, $item_tier); /* daily usage log, every plan */
        return array('ok' => true, 'tier' => $tier, 'limit' => -1, 'used' => 0, 'period' => $period);
    }

    $period_key = ($period === 'month') ? gmdate('Y-m') : gmdate('Y-m-d');
    $meta_key   = 'ds_dl_' . $item_tier . '_' . $period . '_' . $period_key;
    $used       = (int) get_user_meta($uid, $meta_key, true);

    // Free-tier IP throttle: 5 free downloads/day per IP, shared across all accounts on that network.
    $ip_gate = ($tier === 'free' && $item_tier === 'free');
    if ($ip_gate) {
        $ip      = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $ip_key  = 'ds_ipday_dl_' . md5($ip . date('Y-m-d'));
        $ip_used = (int) get_transient($ip_key);
        if ($ip_used >= 5) {
            return array('ok' => false, 'reason' => 'limit_reached', 'limit' => 5, 'used' => $ip_used, 'period' => 'day');
        }
    }

    if ($used >= $cap) {
        return array('ok' => false, 'reason' => 'limit_reached', 'limit' => $cap, 'used' => $used, 'period' => $period);
    }

    // Preview mode: just report whether it's allowed, don't touch the counter
    if ( ! $commit ) {
        return array('ok' => true, 'tier' => $tier, 'limit' => $cap, 'used' => $used, 'period' => $period);
    }

    $new = $used + 1;
    if ($used === 0) add_user_meta($uid, $meta_key, $new, true);
    else update_user_meta($uid, $meta_key, $new); if ($commit && function_exists('ds_ua_log_dl')) ds_ua_log_dl($uid, $item_tier); /* daily usage log, every plan */
    if ($ip_gate) {
        set_transient($ip_key, $ip_used + 1, DAY_IN_SECONDS);
    }

    return array('ok' => true, 'tier' => $tier, 'limit' => $cap, 'used' => $new, 'period' => $period);
}
}

function ds_dl_check_handler($req) {
    $uid       = get_current_user_id();
    $item_tier = sanitize_text_field($req->get_param('tier') ?: 'free');

    // Preview only — the real deduction happens once, in the DLM download
    // gate (DS Login Redirect snippet) when the file is actually served.
    $result = ds_dl_check_core($uid, $item_tier, false);

    if ( empty($result['ok']) ) {
        $code = ($result['reason'] === 'limit_reached') ? 429 : 403;
        $result['uid'] = $uid;
        return new WP_REST_Response($result, $code);
    }

    return new WP_REST_Response($result, 200);
}
if (!function_exists('ds_dl_status_handler')) {
function ds_dl_status_handler($req) {
    if (!is_user_logged_in()) {
        return new WP_REST_Response(array('used_free' => 0, 'limit_free' => 0, 'used_pro' => 0, 'limit_pro' => 0), 200);
    }
    $uid   = get_current_user_id();
    $roles = (array) wp_get_current_user()->roles;
    $rs    = implode(',', $roles);

    $tier = 'free';
    if (in_array('administrator', $roles) || in_array('editor', $roles)) {
        $tier = 'business';
    } else {
        $plan = strtolower(get_user_meta($uid, 'ds_subscription_plan', true) ?: '');
        if (function_exists('ds_access_plan')) $plan = ds_access_plan($uid);            /* live plan, or the last paid plan while credits last */
        elseif (function_exists('ds_get_user_plan')) $plan = ds_get_user_plan($uid);
        if      ($plan === 'business' || in_array('business_member', $roles) || strpos($rs, 'business') !== false) $tier = 'business';
        elseif  ($plan === 'pro'      || in_array('pro_member',      $roles) || strpos($rs, '_pro')      !== false) $tier = 'pro';
        elseif  ($plan === 'premium'  || in_array('premium_member',  $roles) || strpos($rs, 'premium')   !== false) $tier = 'premium';
    }

    $limits = array(
        'free'     => array('period' => 'day',   'free' => 5,    'pro' => 0),
        'premium'  => array('period' => 'month', 'free' => 500,  'pro' => 50),
        'pro'      => array('period' => 'month', 'free' => 1000, 'pro' => 200),
        'business' => array('period' => 'month', 'free' => -1,   'pro' => 600),
    );
    $lim        = isset($limits[$tier]) ? $limits[$tier] : $limits['free'];
    $period     = $lim['period'];
    $period_key = ($period === 'month') ? gmdate('Y-m') : gmdate('Y-m-d');
    $free_key   = 'ds_dl_free_' . $period . '_' . $period_key;
    $pro_key    = 'ds_dl_pro_'  . $period . '_' . $period_key;
    $used_free  = ($lim['free'] === -1) ? 0 : (int) get_user_meta($uid, $free_key, true);
    $used_pro   = ($lim['pro']  === -1) ? 0 : (int) get_user_meta($uid, $pro_key,  true);

    return new WP_REST_Response(array(
        'tier'       => $tier,
        'uid'        => $uid,
        'period'     => $period,
        'period_key' => $period_key,
        'free_key'   => $free_key,
        'pro_key'    => $pro_key,
        'used_free'  => $used_free,
        'limit_free' => $lim['free'],
        'used_pro'   => $used_pro,
        'limit_pro'  => $lim['pro'],
    ), 200);
}
}
