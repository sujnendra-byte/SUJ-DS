<?php
/**
 * Code Snippets export — DS Refund System (24h Window)
 *
 * Snippet ID : 796
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-110
 * Modified   : 2026-09-09 02:46:56
 * Load order : 115 of 144
 */
/**
 * Snippet Name: DS Refund System (24h Window)
 * Run: Everywhere
 * Description: Adds a 24-hour refund window for subscription payments.
 * Rule: full refund only if the payment was made within the last 24 hours
 * AND no credits from that billing cycle have been used yet (all-or-nothing --
 * any usage blocks the refund entirely, it does not prorate). Refunding also
 * cancels the Razorpay subscription immediately and revokes access right away
 * (downgrade to Free). After 24h, or if any credits were used, the refund is
 * blocked -- the user can still use "Cancel Subscription" (stays active until
 * period end, no further charge).
 * Requires: DS Credit System Part A + Part B (Razorpay keys/helpers) active.
 */

if ( ! defined('ABSPATH') ) exit;

// =============================================
// Most recent ACTUAL charge (credits > 0) in the plan history.
// A downgrade entry has credits=0 and is skipped -- it isn't a payment.
// =============================================
if (!function_exists('ds_get_last_charge_history')) {
function ds_get_last_charge_history($uid) {
    $log = get_user_meta($uid, 'ds_plan_history', true) ?: array();
    foreach ($log as $entry) {
        if (!empty($entry['credits']) && (int)$entry['credits'] > 0) {
            return $entry;
        }
    }
    return null;
}
}

// =============================================
// Sum credits debited (generations) since a given timestamp.
// =============================================
if (!function_exists('ds_credits_used_since')) {
function ds_credits_used_since($uid, $since_ts) {
    $total = 0;
    $sources = array('ds_ai_generations', 'ds_ppt_generations', 'ds_ai_video_generations', 'ds_ai_audio_generations');
    foreach ($sources as $meta_key) {
        $items = get_user_meta($uid, $meta_key, true) ?: array();
        if (!is_array($items)) continue;
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $status = $it['status'] ?? 'succeeded';
            if (in_array($status, array('pending', 'failed'), true)) continue;
            $date = $it['date'] ?? '';
            $ts = $date ? strtotime($date) : 0;
            if (!$ts || $ts < $since_ts) continue;
            $cr = (int)($it['credits'] ?? 0);
            if ($cr <= 0 && $meta_key === 'ds_ppt_generations') {
                $slides = (int)($it['num_slides'] ?? $it['slides'] ?? 5);
                $cr = $slides * 4;
            }
            $total += max(0, $cr);
        }
    }
    return $total;
}
}

// =============================================
// Refund eligibility for the CURRENT user's active plan.
// =============================================
if (!function_exists('ds_get_refund_window_info')) {
function ds_get_refund_window_info($uid) {
    $info = array(
        'eligible'    => false,
        'reason'      => '',
        'hours_left'  => 0,
        'cycle_start' => '',
    );

    $plan = function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : 'free';
    if ($plan === 'free') { $info['reason'] = 'no_paid_plan'; return $info; }

    $status = function_exists('ds_get_user_plan_status') ? ds_get_user_plan_status($uid) : 'active';
    if (!in_array($status, array('active', 'cancelled'), true)) { $info['reason'] = 'not_active'; return $info; }

    $entry = ds_get_last_charge_history($uid);
    if (!$entry || empty($entry['date'])) { $info['reason'] = 'no_charge_found'; return $info; }

    $cycle_start_ts = strtotime($entry['date']);
    $info['cycle_start'] = $entry['date'];
    $elapsed = current_time('timestamp') - $cycle_start_ts;

    if ($elapsed > DAY_IN_SECONDS) { $info['reason'] = 'window_expired'; return $info; }
    $info['hours_left'] = max(0, round((DAY_IN_SECONDS - $elapsed) / 3600, 1));

    $used = ds_credits_used_since($uid, $cycle_start_ts);
    if ($used > 0) { $info['reason'] = 'credits_used'; return $info; }

    // Not every feature writes to the logs above (AI Document, charts, diagrams, doc
    // import ...), but every spend takes subscription credits first - so a balance
    // below what this payment granted means credits were used.
    if (metadata_exists('user', $uid, 'ds_subscription_credits')
        && (int) get_user_meta($uid, 'ds_subscription_credits', true) < (int) $entry['credits']) {
        $info['reason'] = 'credits_used'; return $info;
    }

    $info['eligible'] = true;
    return $info;
}
}

// =============================================
// REST: POST /ds/v1/request-refund
// =============================================
if (!function_exists('ds_rest_request_refund')) {
function ds_rest_request_refund(WP_REST_Request $req) {
    $uid = get_current_user_id();
    $info = ds_get_refund_window_info($uid);

    $messages = array(
        'no_paid_plan'    => "You're not on a paid plan.",
        'not_active'      => 'Your subscription is not active.',
        'no_charge_found' => 'No recent payment was found on your account.',
        'window_expired'  => 'The 24-hour refund window has passed. You can still cancel -- your plan stays active until the current billing period ends and you will not be charged again.',
        'credits_used'    => 'This billing cycle is not eligible for a refund because credits from it have already been used. You can still cancel -- your plan stays active until the period ends.',
    );

    if (!$info['eligible']) {
        $reason = $info['reason'] ?: 'not_eligible';
        return new WP_Error('not_eligible', $messages[$reason] ?? 'Not eligible for a refund.', array('status' => 400));
    }

    if (!function_exists('ds_get_razorpay_keys')) {
        return new WP_Error('not_configured', 'Payment system not available. Please contact support.', array('status' => 500));
    }

    $sub_id = get_user_meta($uid, 'ds_subscription_id', true);
    if (!$sub_id || $sub_id === 'manual') {
        return new WP_Error('no_subscription', 'No payment record found for this subscription. Please contact support.', array('status' => 400));
    }

    $keys = ds_get_razorpay_keys();
    $auth = 'Basic ' . base64_encode($keys['key_id'] . ':' . $keys['key_secret']);

    // Find the latest invoice/payment for this subscription
    $inv_resp = wp_remote_get(
        'https://api.razorpay.com/v1/invoices?subscription_id=' . rawurlencode($sub_id) . '&count=1',
        array('headers' => array('Authorization' => $auth), 'timeout' => 20)
    );
    if (is_wp_error($inv_resp)) {
        return new WP_Error('rzp_error', 'Could not reach the payment gateway. Please try again.', array('status' => 500));
    }
    $inv_data   = json_decode(wp_remote_retrieve_body($inv_resp), true);
    $invoice    = $inv_data['items'][0] ?? null;
    $payment_id = $invoice['payment_id'] ?? '';
    $amount     = (int)($invoice['amount'] ?? 0);
    $currency   = $invoice['currency'] ?? 'INR';

    if (!$payment_id) {
        error_log('DS Refund: no payment_id for uid=' . $uid . ' sub=' . $sub_id . ' resp=' . wp_remote_retrieve_body($inv_resp));
        return new WP_Error('no_payment', 'No payment record found for this subscription. Please contact support.', array('status' => 400));
    }

    // Guard against refunding the same payment twice
    $refund_log = get_user_meta($uid, 'ds_refund_history', true) ?: array();
    foreach ($refund_log as $r) {
        if (($r['payment_id'] ?? '') === $payment_id) {
            return new WP_Error('already_refunded', 'This payment has already been refunded.', array('status' => 400));
        }
    }

    // Issue the refund (full amount of the payment)
    $refund_resp = wp_remote_post(
        'https://api.razorpay.com/v1/payments/' . $payment_id . '/refund',
        array(
            'headers' => array('Authorization' => $auth, 'Content-Type' => 'application/json'),
            'body'    => json_encode(array(
                'speed' => 'optimum',
                'notes' => array('reason' => 'user_requested_24h_refund', 'uid' => (string)$uid),
            )),
            'timeout' => 30,
        )
    );
    if (is_wp_error($refund_resp)) {
        error_log('DS Refund error uid=' . $uid . ': ' . $refund_resp->get_error_message());
        return new WP_Error('rzp_error', 'Refund could not be processed. Please try again or contact support.', array('status' => 500));
    }
    $rcode = wp_remote_retrieve_response_code($refund_resp);
    $rdata = json_decode(wp_remote_retrieve_body($refund_resp), true);
    if ($rcode !== 200 && $rcode !== 201) {
        $msg = $rdata['error']['description'] ?? 'Refund failed.';
        error_log('DS Refund failed uid=' . $uid . ': ' . wp_remote_retrieve_body($refund_resp));
        return new WP_Error('rzp_error', $msg, array('status' => 500));
    }

    // Cancel the subscription immediately -- no further billing
    wp_remote_post(
        'https://api.razorpay.com/v1/subscriptions/' . $sub_id . '/cancel',
        array(
            'headers' => array('Authorization' => $auth, 'Content-Type' => 'application/json'),
            'body'    => json_encode(array('cancel_at_cycle_end' => 0)),
            'timeout' => 20,
        )
    );

    // Revoke access immediately -- refunded, so downgrade to Free now
    update_user_meta($uid, 'ds_subscription_plan', 'free');
    update_user_meta($uid, 'ds_subscription_status', 'refunded');
    update_user_meta($uid, 'ds_subscription_end', current_time('Y-m-d'));
    update_user_meta($uid, 'ds_subscription_credits', 0);
    if (function_exists('ds_get_credits')) {
        update_user_meta($uid, 'ds_ai_credits', ds_get_credits($uid));
    }

    $wp_user = get_user_by('id', $uid);
    if ($wp_user) {
        $protected = array('administrator', 'editor', 'shop_manager');
        if (!array_intersect($protected, (array)$wp_user->roles)) {
            $wp_user->set_role('subscriber');
        }
    }

    // Log it
    $refund_log[] = array(
        'payment_id' => $payment_id,
        'sub_id'     => $sub_id,
        'amount'     => $amount,
        'currency'   => $currency,
        'date'       => current_time('mysql'),
    );
    update_user_meta($uid, 'ds_refund_history', array_slice($refund_log, 0, 20));

    error_log("DS Refund: SUCCESS uid=$uid payment=$payment_id amount=$amount $currency");

    return rest_ensure_response(array(
        'success'  => true,
        'message'  => 'Your payment has been refunded and the subscription cancelled. You are now on the Free plan.',
        'amount'   => $amount,
        'currency' => $currency,
    ));
}
}

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/request-refund', array(
        'methods'             => 'POST',
        'callback'            => 'ds_rest_request_refund',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});
