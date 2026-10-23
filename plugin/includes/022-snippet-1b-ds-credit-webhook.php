<?php
/**
 * Code Snippets export — Snippet 1b ds credit webhook
 *
 * Snippet ID : 708
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-022
 * Modified   : 2026-09-09 02:45:21
 * Load order : 022 of 144
 */
/**
 * Snippet Name: DS Credit System - Part B (Webhook + Admin)
 * Run: Everywhere
 * Description: Razorpay payment functions, webhook, admin display. Requires Part A.
 */

// =============================================
// CREATE RAZORPAY SUBSCRIPTION
// =============================================
function ds_rest_create_subscription(WP_REST_Request $req) {
    $uid     = get_current_user_id();
    $plan_key = sanitize_text_field($req->get_param('plan')); // premium|pro|business
    $rzp_plans = ds_get_razorpay_plans();

    if (!isset($rzp_plans[$plan_key])) {
        return new WP_Error('invalid_plan', 'Invalid plan', array('status'=>400));
    }

    $plan_data = $rzp_plans[$plan_key];
    $plan_id   = $plan_data['plan_id'];

    if (strpos($plan_id, 'plan_REPLACE') !== false) {
        return new WP_Error('not_configured', 'Razorpay plan ID not configured. Add plan IDs in ds_get_razorpay_plans()', array('status'=>500));
    }

    $keys = ds_get_razorpay_keys();
    $user = get_user_by('id', $uid);

    // Create subscription via Razorpay API
    $response = wp_remote_post('https://api.razorpay.com/v1/subscriptions', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($keys['key_id'] . ':' . $keys['key_secret']),
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode(array(
            'plan_id'         => $plan_id,
            'total_count'     => 120,        // 10 years max, cancellable anytime
            'quantity'        => 1,
            'customer_notify' => 1,
            'notes'           => array(
                'user_id'   => (string)$uid,
                'user_email'=> $user->user_email,
                'plan'      => $plan_key,
                'site'      => get_site_url(),
            ),
        )),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return new WP_Error('rzp_error', $response->get_error_message(), array('status'=>500));
    }

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200 && $code !== 201) {
        $msg = $data['error']['description'] ?? ('Razorpay error: HTTP ' . $code);
        error_log('DS Razorpay create subscription error: ' . wp_remote_retrieve_body($response));
        return new WP_Error('rzp_error', $msg, array('status'=>500));
    }

    // Return subscription ID to frontend for checkout
    return rest_ensure_response(array(
        'success'         => true,
        'subscription_id' => $data['id'],
        'plan'            => $plan_key,
        'plan_label'      => $plan_data['label'],
        'key_id'          => $keys['key_id'],
        'user_name'       => $user->display_name,
        'user_email'      => $user->user_email,
    ));
}

// =============================================
// CREATE RAZORPAY ORDER (for one-time top-ups)
// =============================================
function ds_rest_create_order(WP_REST_Request $req) {
    $uid      = get_current_user_id();
    $topup_key = sanitize_text_field($req->get_param('topup')); // topup_100|topup_200|topup_300
    $topups   = ds_get_topup_plans();

    if (!isset($topups[$topup_key])) {
        return new WP_Error('invalid_topup', 'Invalid top-up', array('status'=>400));
    }

    $topup    = $topups[$topup_key];
$keys     = ds_get_razorpay_keys();
$user     = get_user_by('id', $uid);
$country  = function_exists('ds_get_user_country') ? ds_get_user_country() : 'IN';
$currency = ($country === 'IN') ? 'INR' : 'USD';
$amount   = ($currency === 'INR') ? ($topup['price_inr'] * 100) : (int) round($topup['price'] * 100); // paise or cents

    $response = wp_remote_post('https://api.razorpay.com/v1/orders', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($keys['key_id'] . ':' . $keys['key_secret']),
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode(array(
            'amount'   => $amount,
            'currency' => $currency,
            'receipt'  => 'topup_' . $uid . '_' . time(),
            'notes'    => array(
                'user_id' => (string)$uid,
                'credits' => (string)$topup['credits'],
                'topup'   => $topup_key,
                'type'    => 'topup',
            ),
        )),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return new WP_Error('rzp_error', $response->get_error_message(), array('status'=>500));
    }

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        $msg = $data['error']['description'] ?? ('HTTP ' . $code);
        return new WP_Error('rzp_error', $msg, array('status'=>500));
    }

    return rest_ensure_response(array(
        'success'    => true,
        'order_id'   => $data['id'],
        'amount'     => $amount,
        'currency'   => $currency,
        'credits'    => $topup['credits'],
        'key_id'     => $keys['key_id'],
        'user_name'  => $user->display_name,
        'user_email' => $user->user_email,
    ));
}

// Read an order or subscription from Razorpay (GET). Returns the decoded array, or null.
if (!function_exists('ds_rzp_api_get')) {
function ds_rzp_api_get($path, $keys) {
    $r = wp_remote_get('https://api.razorpay.com/v1/' . $path, array(
        'headers' => array('Authorization' => 'Basic ' . base64_encode($keys['key_id'] . ':' . $keys['key_secret'])),
        'timeout' => 20,
    ));
    if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) return null;
    $d = json_decode(wp_remote_retrieve_body($r), true);
    return is_array($d) ? $d : null;
}
}

// True the first time it is called for $key, false ever after. A single INSERT IGNORE,
// so two requests arriving at the same moment cannot both get true.
if (!function_exists('ds_rzp_claim_once')) {
function ds_rzp_claim_once($key) {
    global $wpdb;
    $name = 'ds_rzp_done_' . md5((string) $key);
    return (bool) $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')",
        $name, (string) time()
    ));
}
}

// =============================================
// VERIFY PAYMENT (called after successful checkout)
// =============================================
function ds_rest_verify_payment(WP_REST_Request $req) {
    $uid     = get_current_user_id();
    $params  = $req->get_json_params();
    $keys    = ds_get_razorpay_keys();

    $type    = sanitize_text_field($params['type']    ?? 'subscription'); // subscription|topup
    $plan    = sanitize_text_field($params['plan']    ?? '');
    $credits = (int)($params['credits'] ?? 0);

    // Verify Razorpay signature
    $razorpay_payment_id     = sanitize_text_field($params['razorpay_payment_id']     ?? '');
    $razorpay_subscription_id = sanitize_text_field($params['razorpay_subscription_id'] ?? '');
    $razorpay_order_id       = sanitize_text_field($params['razorpay_order_id']       ?? '');
    $razorpay_signature      = sanitize_text_field($params['razorpay_signature']       ?? '');

    // Verify signature
    $verified = false;
    if ($type === 'subscription' && $razorpay_payment_id && $razorpay_subscription_id) {
        $payload  = $razorpay_payment_id . '|' . $razorpay_subscription_id;
        $expected = hash_hmac('sha256', $payload, $keys['key_secret']);
        $verified = hash_equals($expected, $razorpay_signature);
    } elseif ($type === 'topup' && $razorpay_order_id && $razorpay_payment_id) {
        $payload  = $razorpay_order_id . '|' . $razorpay_payment_id;
        $expected = hash_hmac('sha256', $payload, $keys['key_secret']);
        $verified = hash_equals($expected, $razorpay_signature);
    }

    if (!$verified) {
        error_log("DS Razorpay: signature verification FAILED for uid=$uid");
        return new WP_Error('invalid_signature', 'Payment verification failed', array('status'=>400));
    }

    // What was bought comes from Razorpay's copy of the order/subscription (its notes
    // were written by us when it was created) - never from the browser, which could
    // ask for any number of credits or a bigger plan than was paid for.
    if ($type === 'topup') {
        $order = ds_rzp_api_get('orders/' . rawurlencode($razorpay_order_id), $keys);
        $notes = (is_array($order) && isset($order['notes']) && is_array($order['notes'])) ? $order['notes'] : array();
        if (!$order || (int)($notes['user_id'] ?? 0) !== (int)$uid || ($notes['type'] ?? '') !== 'topup') {
            error_log("DS Razorpay: top-up order $razorpay_order_id could not be confirmed for uid=$uid");
            return new WP_Error('order_mismatch', 'Payment could not be confirmed. Please contact support.', array('status'=>400));
        }
        $credits = (int)($notes['credits'] ?? 0);
        $once_key = 'order:' . $razorpay_order_id;
    } else {
        $sub   = ds_rzp_api_get('subscriptions/' . rawurlencode($razorpay_subscription_id), $keys);
        $notes = (is_array($sub) && isset($sub['notes']) && is_array($sub['notes'])) ? $sub['notes'] : array();
        if (!$sub || (int)($notes['user_id'] ?? 0) !== (int)$uid) {
            error_log("DS Razorpay: subscription $razorpay_subscription_id could not be confirmed for uid=$uid");
            return new WP_Error('order_mismatch', 'Payment could not be confirmed. Please contact support.', array('status'=>400));
        }
        $plan = '';
        foreach (ds_get_razorpay_plans() as $pkey => $pdata) {
            if (!empty($pdata['plan_id']) && isset($sub['plan_id']) && $pdata['plan_id'] === $sub['plan_id']) { $plan = $pkey; break; }
        }
        if (!in_array($plan, array('premium', 'pro', 'business'), true)) {
            $plan = strtolower((string)($notes['plan'] ?? ''));
        }
        if (!in_array($plan, array('premium', 'pro', 'business'), true)) $plan = '';
        $once_key = 'pay:' . $razorpay_payment_id;
    }

    // Each payment counts once. Reloading the thank-you page, or the webhook
    // arriving for the same top-up, must not add the credits again.
    if (!ds_rzp_claim_once($once_key)) {
        $type = 'already_done';
    }

    // Payment verified!
    if ($type === 'subscription' && $plan) {
        // Calculate next billing date (1 month from now)
        $end_date = date('Y-m-d', strtotime('+1 month'));
        ds_activate_plan($uid, $plan, $razorpay_subscription_id, $end_date);
        // Store payment ID
        update_user_meta($uid, 'ds_last_payment_id', $razorpay_payment_id);
        error_log("DS Razorpay: subscription activated plan=$plan uid=$uid payment=$razorpay_payment_id");
    } elseif ($type === 'topup' && $credits > 0) {
        ds_add_credits($uid, $credits);
        update_user_meta($uid, 'ds_last_topup', array(
            'credits' => $credits,
            'date'    => current_time('mysql'),
            'payment' => $razorpay_payment_id,
        ));
        // Log to credit history
        $topups   = get_user_meta( $uid, 'ds_credit_topups', true ) ?: [];
        $topups[] = [
            'type'      => 'topup',
            'label'     => 'Credit Purchase — ' . $credits . ' credits',
            'amount'    => $credits,
            'date'      => current_time('Y-m-d'),
            'timestamp' => time(),
        ];
        update_user_meta( $uid, 'ds_credit_topups', $topups );
        error_log("DS Razorpay: top-up +$credits credits uid=$uid payment=$razorpay_payment_id");
    }

    $balance = ds_get_credits($uid);
    return rest_ensure_response(array(
        'success'         => true,
        'credits_balance' => $balance,
        'plan'            => ds_get_user_plan($uid),
        'plan_label'      => (ds_get_plans()[ds_get_user_plan($uid)]['label'] ?? 'Free'),
        'redirect_url'    => home_url('/thank-you'),
    ));
}

// =============================================
// CANCEL SUBSCRIPTION
// =============================================
function ds_rest_cancel_subscription(WP_REST_Request $req) {
    $uid    = get_current_user_id();
    $sub_id = get_user_meta($uid, 'ds_subscription_id', true);

    if ($sub_id) {
        $keys = ds_get_razorpay_keys();
        // Cancel at period end (cancel_at_cycle_end=1 means active until period ends)
        $response = wp_remote_post(
            'https://api.razorpay.com/v1/subscriptions/' . $sub_id . '/cancel',
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($keys['key_id'] . ':' . $keys['key_secret']),
                    'Content-Type'  => 'application/json',
                ),
                'body'    => json_encode(array('cancel_at_cycle_end' => 1)),
                'timeout' => 20,
            )
        );
        $data = json_decode(wp_remote_retrieve_body($response), true);
        error_log('DS Razorpay cancel: ' . wp_remote_retrieve_body($response));
    }

    update_user_meta($uid, 'ds_subscription_status', 'cancelled');
    $end = ds_get_subscription_end($uid) ?: date('Y-m-t');
    update_user_meta($uid, 'ds_subscription_end', $end);

    return rest_ensure_response(array(
        'success'  => true,
        'message'  => 'Subscription cancelled. Your plan remains active until ' . date('F j, Y', strtotime($end)) . '.',
        'end_date' => $end,
    ));
}

// =============================================
// ADMIN SET PLAN
// =============================================
function ds_rest_admin_set_plan(WP_REST_Request $req) {
    $uid     = (int)$req->get_param('user_id');
    $plan    = sanitize_text_field($req->get_param('plan'));
    $credits = (int)$req->get_param('credits');
    if (!$uid || !in_array($plan, array('free','premium','pro','business'))) {
        return new WP_Error('bad_params','Invalid params',array('status'=>400));
    }
    // Keep the real Razorpay subscription id unless one is supplied.
    // Writing the literal 'manual' over it used to orphan the subscription,
    // so later cancel/renew webhooks could no longer be matched to the user.
    $sub_id = sanitize_text_field((string) $req->get_param('sub_id'));
    if ($sub_id === '') {
        $sub_id = (string) get_user_meta($uid, 'ds_subscription_id', true);
    }
    $end_date = sanitize_text_field((string) $req->get_param('end_date'));
    ds_activate_plan($uid, $plan, $sub_id, ($end_date !== '' ? $end_date : null));
    if ($credits > 0) ds_add_credits($uid, $credits);
    return rest_ensure_response(array('success'=>true,'plan'=>$plan,'credits_added'=>$credits));
}

// =============================================
// RAZORPAY WEBHOOK (server-to-server)
// Events: subscription.activated, subscription.charged,
//         subscription.cancelled, subscription.completed,
//         payment.captured
// Webhook URL: /wp-json/designstager/v1/razorpay-webhook
// Webhook Secret: set in Razorpay Dashboard -> Webhooks
// =============================================
/**
 * Best-effort plan resolution for a Razorpay subscription event.
 *
 * Razorpay does not always echo our notes back (notes are only set at
 * subscription-creation time, and are absent on some renewal payloads).
 * Without this, a renewal for an account that had already lapsed to 'free'
 * silently did nothing - the customer was charged and stayed on the free plan.
 *
 * Order: the subscription's Razorpay plan_id (most authoritative) ->
 * the plan the user is on right now -> the last paid plan in their history.
 */
if (!function_exists('ds_resolve_plan_for_subscription')) {
function ds_resolve_plan_for_subscription($uid, $entity = array()) {
    $valid = array('premium', 'pro', 'business');

    // 1. Map the Razorpay plan_id on this subscription back to our plan key.
    $plan_id = isset($entity['plan_id']) ? (string) $entity['plan_id'] : '';
    if ($plan_id !== '' && function_exists('ds_get_razorpay_plans')) {
        foreach (ds_get_razorpay_plans() as $key => $data) {
            if (!empty($data['plan_id']) && $data['plan_id'] === $plan_id && in_array($key, $valid, true)) {
                return $key;
            }
        }
    }

    // 2. Whatever plan the account is on right now.
    if (function_exists('ds_get_user_plan')) {
        $current = ds_get_user_plan($uid);
        if (in_array($current, $valid, true)) {
            return $current;
        }
    }

    // 3. Last paid plan we recorded for this user (history is newest-first).
    $log = get_user_meta($uid, 'ds_plan_history', true);
    if (is_array($log)) {
        foreach ($log as $row) {
            if (!empty($row['plan']) && in_array($row['plan'], $valid, true)) {
                return $row['plan'];
            }
        }
    }

    return '';
}
}

function ds_handle_razorpay_webhook(WP_REST_Request $req) {
    if ($req->get_method() === 'GET') {
        return rest_ensure_response(array('status'=>'Razorpay webhook active','version'=>'1.0'));
    }

    $body      = $req->get_body();
    $signature = $req->get_header('X-Razorpay-Signature') ?: ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');
    $secret = get_option('ds_razorpay_webhook_secret', '');

    // Record that a delivery arrived BEFORE any rejection, so a bad secret or
    // a disabled/misconfigured webhook is visible in /wp-json/ds/v1/webhook-debug
    // instead of failing completely silently.
    update_option('ds_rzp_last_time', current_time('mysql'));
    update_option('ds_rzp_last_raw',  substr((string) $body, 0, 4000));

    // Fail closed if secret not configured
    if (empty($secret)) {
        error_log('DS Razorpay webhook: secret not set - rejecting');
        update_option('ds_rzp_last_event',  'rejected');
        update_option('ds_rzp_last_result', 'secret_not_configured');
        return new WP_REST_Response('Not configured', 500);
    }
    // Reject if signature missing OR wrong (no more skip-when-empty)
    $expected = hash_hmac('sha256', $body, $secret);
    if (empty($signature) || !hash_equals($expected, $signature)) {
        error_log('DS Razorpay webhook: INVALID or MISSING signature');
        update_option('ds_rzp_last_event',  'rejected');
        update_option('ds_rzp_last_result', empty($signature) ? 'signature_missing' : 'signature_mismatch');
        return new WP_REST_Response('Invalid signature', 401);
    }

    $payload = json_decode($body, true);
    if (!$payload) return new WP_REST_Response('Bad JSON', 400);

    $event   = $payload['event']          ?? '';
    $entity  = $payload['payload']['subscription']['entity'] ?? array();
    $payment = $payload['payload']['payment']['entity']      ?? array();
    $notes   = $entity['notes'] ?? $payment['notes'] ?? array();

    error_log("DS Razorpay webhook: event=$event");
    update_option('ds_rzp_last_event', $event);

    // Find user from notes (most reliable)
    $uid  = 0;
    $user = null;
    if (!empty($notes['user_id'])) {
        $user = get_user_by('id', (int)$notes['user_id']);
    }
    if (!$user && !empty($payment['email'])) {
        $user = get_user_by('email', $payment['email']);
    }
    if (!$user && !empty($entity['customer_id'])) {
        // Try to find by Razorpay customer ID stored in meta
        $users = get_users(array('meta_key'=>'ds_rzp_customer_id','meta_value'=>$entity['customer_id'],'number'=>1));
        if (!empty($users)) $user = $users[0];
    }

    if (!$user) {
        error_log("DS Razorpay webhook: user not found. notes=" . json_encode($notes));
        update_option('ds_rzp_last_result', 'user_not_found');
        return new WP_REST_Response('OK', 200);
    }

    $uid     = $user->ID;
    $plan    = strtolower($notes['plan'] ?? '');
    $sub_id  = $entity['id'] ?? '';
    $credits_note = (int)($notes['credits'] ?? 0);

    update_option('ds_rzp_last_email', $user->user_email);
    update_option('ds_rzp_last_plan',  $plan);

    // Calculate next billing date
    $current_end = $entity['current_end'] ?? 0;
    $end_date    = $current_end ? date('Y-m-d', $current_end) : date('Y-m-d', strtotime('+1 month'));

    // Razorpay often omits our notes on renewal payloads. Recover the plan
    // from the subscription itself so a charge is never applied to nothing.
    if (!$plan || !in_array($plan, array('premium', 'pro', 'business'), true)) {
        $plan = ds_resolve_plan_for_subscription($uid, $entity);
    }
    update_option('ds_rzp_last_plan', $plan);

    // Razorpay sends money in the smallest currency unit (paise for INR).
    // Recorded on the plan-history entry so the daily report can show what
    // was actually charged next to the credits that were granted.
    $paid_amount   = isset($payment['amount']) ? ((float) $payment['amount']) / 100 : 0;
    $paid_currency = isset($payment['currency']) ? strtoupper((string) $payment['currency']) : 'INR';

    switch ($event) {

        case 'subscription.activated':
case 'subscription.authenticated':
    // New subscription started — only activate if not already active
    // (avoid double-fire with ds_rest_verify_payment)
    if ($plan && in_array($plan, array('premium','pro','business'))) {
        $current_plan   = ds_get_user_plan($uid);
        $current_status = ds_get_user_plan_status($uid);
        if ($current_plan !== $plan || $current_status !== 'active') {
            ds_activate_plan($uid, $plan, $sub_id, $end_date, $paid_amount, $paid_currency);
        }
        update_option('ds_rzp_last_result', "activated plan=$plan uid=$uid");
        error_log("DS Razorpay: subscription activated plan=$plan uid=$uid");
    }
    break;

        case 'subscription.charged':
            // Monthly renewal payment succeeded. $plan is already resolved above,
            // so this also reinstates an account that had lapsed to free between
            // charges (previously this branch bailed out and did nothing).
            if ($plan && in_array($plan, array('premium', 'pro', 'business'), true)) {
                ds_activate_plan($uid, $plan, $sub_id, $end_date, $paid_amount, $paid_currency);
                update_option('ds_rzp_last_result', "charged plan=$plan uid=$uid");
                error_log("DS Razorpay: subscription charged - plan=$plan credits added for uid=$uid");
            } else {
                update_option('ds_rzp_last_result', "charged_unresolved uid=$uid sub=$sub_id");
                error_log("DS Razorpay: charged but no plan could be resolved for uid=$uid sub=$sub_id");
            }
            break;

        case 'payment.captured':
            // One-time payment captured (top-ups). Shares the once-only key with
            // ds_rest_verify_payment(), so a top-up is never credited twice.
            if ($credits_note > 0 && !empty($payment['order_id']) && ds_rzp_claim_once('order:' . $payment['order_id'])) {
                ds_add_credits($uid, $credits_note);
                update_user_meta($uid, 'ds_last_topup', array(
                    'credits' => $credits_note,
                    'date'    => current_time('mysql'),
                    'payment' => $payment['id'] ?? '',
                ));
                update_option('ds_rzp_last_result', "topup +$credits_note uid=$uid");
                error_log("DS Razorpay: top-up +$credits_note credits uid=$uid");
            }
            break;

        case 'subscription.cancelled':
        case 'subscription.completed':
            update_user_meta($uid, 'ds_subscription_status', 'cancelled');
    if (function_exists('ds_pool_subscription_broken')) {
        ds_pool_subscription_broken($uid);
    }
            update_user_meta($uid, 'ds_subscription_end',    $end_date);
            update_option('ds_rzp_last_result', "cancelled uid=$uid ends=$end_date");
            error_log("DS Razorpay: subscription cancelled uid=$uid ends=$end_date");
            break;

        case 'subscription.expired':
        case 'subscription.paused':
            $downgrade = ds_get_pending_downgrade($uid);
            if ($downgrade && $downgrade !== ds_get_user_plan($uid)) {
                ds_activate_plan($uid, $downgrade, null, null);
                delete_user_meta($uid, 'ds_pending_downgrade');
            } else {
                update_user_meta($uid, 'ds_subscription_plan',   'free');
                update_user_meta($uid, 'ds_subscription_status',  'expired');
                $wp_user = get_user_by('id', $uid);
                if ($wp_user) $wp_user->set_role('subscriber');
            }
            update_option('ds_rzp_last_result', "expired uid=$uid");
            break;

        case 'subscription.pending':
        case 'subscription.halted':
            // Payment failed - notify but don't change plan yet
            error_log("DS Razorpay: payment issue for uid=$uid event=$event");
            update_option('ds_rzp_last_result', "payment_issue uid=$uid event=$event");
            break;
    }

    return new WP_REST_Response('OK', 200);
}

// =============================================
// DAILY CRON - expire plans
// =============================================
if (!wp_next_scheduled('ds_check_subscriptions')) {
    wp_schedule_event(time(), 'daily', 'ds_check_subscriptions');
}
add_action('ds_check_subscriptions', function() {
    // Only paid accounts, and all of them. This used to load the first 500
    // users that had ANY plan value - every sign-up gets 'free', so once the
    // site passed 500 users, paid accounts beyond that were never checked
    // and never expired.
    $user_ids = get_users(array(
        'fields'     => 'ID',
        'number'     => -1,
        'meta_query' => array(
            array(
                'key'     => 'ds_subscription_plan',
                'value'   => array('premium', 'pro', 'business'),
                'compare' => 'IN',
            ),
        ),
    ));
    foreach ($user_ids as $uid) {
        $uid  = (int) $uid;
        $plan = ds_get_user_plan($uid);
        $end  = ds_get_subscription_end($uid);
        if ($plan === 'free' || !$end) continue;
        $status = ds_get_user_plan_status($uid);
        // Valid THROUGH the end date, plus 5 days' grace. Razorpay retries a
        // failed recurring charge over several days, so 2 days demoted paying
        // customers before the retry had a chance to land.
        if (in_array($status, array('cancelled','active'))
            && strtotime($end . ' 23:59:59') + (5 * DAY_IN_SECONDS) < current_time('timestamp')) {
            $dg = ds_get_pending_downgrade($uid);
            if ($dg && $dg !== $plan) {
                ds_activate_plan($uid, $dg, null, null);
                delete_user_meta($uid, 'ds_pending_downgrade');
            } else {
                update_user_meta($uid, 'ds_subscription_plan',   'free');
                update_user_meta($uid, 'ds_subscription_status',  'expired');
                $wp_user = get_user_by('id', $uid);
                // Never demote staff — same guard ds_activate_plan() uses.
                if ($wp_user && ! array_intersect(array('administrator','editor','shop_manager'), (array) $wp_user->roles)) {
                    $wp_user->set_role('subscriber');
                }
            }
        }
    }
});

// =============================================
// ADMIN USER PROFILE
// =============================================
add_action('show_user_profile', 'ds_show_user_plan_profile');
add_action('edit_user_profile', 'ds_show_user_plan_profile');
function ds_show_user_plan_profile($user) {
    if (!current_user_can('edit_users')) return;
    $uid  = $user->ID;
    $plan = ds_get_user_plan($uid);
    $log  = get_user_meta($uid, 'ds_plan_history', true) ?: array();
    echo '<h3>AI Studio Account</h3>';
    echo '<table class="form-table">';
    echo '<tr><th>Credits</th><td><input type="number" name="ds_ai_credits" value="' . ds_get_credits($uid) . '" style="width:100px;"/></td></tr>';
    echo '<tr><th>Plan</th><td><select name="ds_subscription_plan">';
    foreach (array('free','premium','pro','business') as $p)
        echo '<option value="'.$p.'"'.($plan===$p?' selected':'').'>'.ucfirst($p).'</option>';
    echo '</select></td></tr>';
    echo '<tr><th>Status</th><td><input type="text" name="ds_subscription_status" value="'.esc_attr(ds_get_user_plan_status($uid)).'" style="width:140px;"/></td></tr>';
    echo '<tr><th>Sub End Date</th><td><input type="date" name="ds_subscription_end" value="'.esc_attr(ds_get_subscription_end($uid)).'" style="width:155px;"/></td></tr>';
    echo '<tr><th>History</th><td><small style="color:#666;">';
    foreach (array_slice($log,0,5) as $l)
        echo esc_html($l['date']??'').': '.esc_html($l['from']??'').' to '.esc_html($l['plan']??'').' (+'.esc_html($l['credits']??0).'cr)<br>';
    echo '</small></td></tr></table>';
    wp_nonce_field('ds_save_plan','ds_plan_nonce');
}
add_action('personal_options_update',  'ds_save_user_plan_profile');
add_action('edit_user_profile_update', 'ds_save_user_plan_profile');
function ds_save_user_plan_profile($uid) {
    // Credits and plan are changed by staff only. Users can edit their own
    // profile, so 'edit_user' alone would let a customer give themselves credits.
    if (!current_user_can('edit_user',$uid) || !current_user_can('edit_users')) return;
    if (!isset($_POST['ds_plan_nonce'])||!wp_verify_nonce($_POST['ds_plan_nonce'],'ds_save_plan')) return;
    if (isset($_POST['ds_ai_credits'])) {
        $new_credits = (int)$_POST['ds_ai_credits'];
        $current     = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int)get_user_meta($uid,'ds_ai_credits',true);
        $diff        = $new_credits - $current;
        if ($diff > 0 && function_exists('ds_add_credits')) {
            ds_add_credits($uid, $diff);
        } elseif ($diff < 0) {
            update_user_meta($uid, 'ds_ai_credits', max(0, $new_credits));
            update_user_meta($uid, 'ds_topup_credits', max(0, (int)get_user_meta($uid,'ds_topup_credits',true) + $diff));
        }
    }
    if (isset($_POST['ds_subscription_plan']))   update_user_meta($uid,'ds_subscription_plan',sanitize_text_field($_POST['ds_subscription_plan']));
    if (isset($_POST['ds_subscription_status'])) update_user_meta($uid,'ds_subscription_status',sanitize_text_field($_POST['ds_subscription_status']));
    if (isset($_POST['ds_subscription_end']))    update_user_meta($uid,'ds_subscription_end',sanitize_text_field($_POST['ds_subscription_end']));
}

// =============================================
// SHORTCODE
// =============================================
add_shortcode('ds_credits', function() {
    if (!is_user_logged_in()) return '';
    return '<span style="font-weight:700;color:#6366f1;">'.number_format(ds_get_credits(get_current_user_id())).' Credits</span>';
});

// =============================================
// GLOBAL JS (credits + plan + Razorpay key)
// =============================================

// Marketplace functions moved to snippet-1b-ds-marketplace-admin.php
