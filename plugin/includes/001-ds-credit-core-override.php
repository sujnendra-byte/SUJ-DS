<?php
/**
 * Code Snippets export — DS Credit Core Override
 *
 * Snippet ID : 687
 * Active     : yes
 * Priority   : 1
 * Scope      : global
 * Tags       : designstager, ds-order-001
 * Modified   : 2026-09-09 02:41:30
 * Load order : 001 of 144
 */
// ── Date helpers ──────────────────────────────────────────────
// Month-end safe and WordPress-timezone aware.
// 31 Jan + 1 month = 28/29 Feb here, whereas strtotime('+1 month') gives 3 Mar.
if (!function_exists('ds_add_one_month')) {
function ds_add_one_month($from = null) {
    $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(date_default_timezone_get());
    try   { $d = new DateTime($from ?: 'now', $tz); }
    catch (Exception $e) { $d = new DateTime('now', $tz); }

    $day = (int)$d->format('j');
    $d->modify('first day of this month')->modify('+1 month');
    $d->setDate((int)$d->format('Y'), (int)$d->format('n'), min($day, (int)$d->format('t')));
    return $d->format('Y-m-d');
}
}

// ── Free-tier gate ────────────────────────────────────────────
// A user is on the free ration only when they hold no credits AND have no live
// paid period. Credits alone lift the limits — every pool expires within a month
// of purchase, so a positive balance is always recently paid for or earned.
if (!function_exists('ds_user_is_free_tier')) {
function ds_user_is_free_tier($uid) {
    // Enough to afford the cheapest model, not merely a non-zero balance — otherwise
    // a 1-credit credit-back would strip the free allowance and buy nothing.
    $min = (int) apply_filters('ds_free_tier_credit_floor', 2);
    if (function_exists('ds_get_credits') && (int) ds_get_credits($uid) >= $min) return false;

    $plan = function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : 'free';
    if ($plan === 'free') return true;

    $status = function_exists('ds_get_user_plan_status') ? ds_get_user_plan_status($uid) : 'active';
    if ($status === 'active') return false;

    // Cancelled or expired but still inside the period they paid for.
    $end = function_exists('ds_get_subscription_end') ? ds_get_subscription_end($uid) : '';
    if ($end && strtotime($end . ' 23:59:59') >= current_time('timestamp')) return false;

    return true;
}
}

// Expiry for subscription credits = the end of the billing period they belong to.
// Falls back to one month from today only when the period end genuinely isn't known.
if (!function_exists('ds_credit_expiry_for_period')) {
function ds_credit_expiry_for_period($period_end = '') {
    $period_end = trim((string)$period_end);
    if ($period_end !== '' && ($ts = strtotime($period_end)) !== false) {
        return date('Y-m-d', $ts);
    }
    return ds_add_one_month();
}
}

// ── Access plan: what a user may use, which is not always what they pay for ──
// Billing code keeps using ds_get_user_plan() (the stored plan). Feature checks use
// ds_access_plan(): the live paid plan, or - when there is no live plan - the last
// paid plan for as long as the user still holds enough unexpired credits for one
// presentation. Top-up and credit-back credits expire one month after they were
// added and subscription credits at the end of their period, so this access ends
// on its own when the credits are used up or expire.
if (!function_exists('ds_has_live_plan')) {
function ds_has_live_plan($uid) {
    $uid = (int) $uid;
    if (!$uid || !function_exists('ds_get_user_plan')) return false;
    if (!in_array(ds_get_user_plan($uid), array('premium', 'pro', 'business'), true)) return false;
    $status = function_exists('ds_get_user_plan_status') ? (string) ds_get_user_plan_status($uid) : 'active';
    if ($status === 'active') return true;
    // Cancelled but still inside the period that was paid for.
    $end = function_exists('ds_get_subscription_end') ? (string) ds_get_subscription_end($uid) : '';
    return $end !== '' && strtotime($end . ' 23:59:59') >= current_time('timestamp');
}
}

// Credits that can still be spent today. Same pools as ds_get_credits(), but a pool whose
// expiry date has passed is left out even before the nightly expiry job clears it.
if (!function_exists('ds_valid_credits')) {
function ds_valid_credits($uid) {
    $uid = (int) $uid;
    if (!$uid) return 0;
    $pairs = array(
        'ds_subscription_credits' => 'ds_subscription_credits_expiry',
        'ds_carried_credits'      => 'ds_carried_credits_expiry',
        'ds_creditback_credits'   => 'ds_creditback_credits_expiry',
        'ds_topup_credits'        => 'ds_topup_credits_expiry',
    );
    $bal = array();
    foreach ($pairs as $k => $e) $bal[$k] = (int) get_user_meta($uid, $k, true);
    // No pool balance at all: legacy single balance, exactly as ds_get_credits() reads it.
    if (!array_filter($bal)) return max(0, (int) get_user_meta($uid, 'ds_ai_credits', true));
    $today = current_time('Y-m-d');
    $sum = 0;
    foreach ($pairs as $k => $e) {
        if ($bal[$k] <= 0) continue;
        $exp = (string) get_user_meta($uid, $e, true);
        // Older subscription credits without their own expiry end with the subscription period.
        if ($exp === '' && $k === 'ds_subscription_credits' && function_exists('ds_get_subscription_end')) {
            $exp = (string) ds_get_subscription_end($uid);
        }
        if ($exp !== '') {
            $ts = strtotime($exp);
            if ($ts !== false && date('Y-m-d', $ts) < $today) continue;   // expired: valid through its date only
        }
        $sum += $bal[$k];
    }
    return $sum;
}
}

// Credits needed to keep paid access without a live plan: the price of the cheapest presentation.
if (!function_exists('ds_access_credit_floor')) {
function ds_access_credit_floor() {
    $floor = 5;
    if (function_exists('ds_ppt_credit_cost')) {
        $c = (int) ds_ppt_credit_cost(1, 'preserve');
        if ($c > 0) $floor = $c;
    }
    return max(1, (int) apply_filters('ds_access_credit_floor', $floor));
}
}

if (!function_exists('ds_access_plan')) {
function ds_access_plan($uid) {
    static $cache = array();
    $uid = (int) $uid;
    if (!$uid) return 'free';
    if (isset($cache[$uid])) return $cache[$uid];
    $paid = array('premium', 'pro', 'business');
    if (ds_has_live_plan($uid)) return $cache[$uid] = ds_get_user_plan($uid);
    if (ds_valid_credits($uid) < ds_access_credit_floor()) return $cache[$uid] = 'free';
    // Credits but no live plan: the last paid plan they had (history is newest first), else Premium.
    $log = get_user_meta($uid, 'ds_plan_history', true);
    if (is_array($log)) {
        foreach ($log as $row) {
            if (is_array($row) && !empty($row['plan']) && in_array($row['plan'], $paid, true)) return $cache[$uid] = $row['plan'];
        }
    }
    return $cache[$uid] = 'premium';
}
}

if ( ! defined('ABSPATH') ) exit;

// Single source of truth for credits. Pools: subscription + carried + creditback + topup.
if (!function_exists('ds_get_credits')) {
function ds_get_credits($uid) {
    $sub     = (int)get_user_meta($uid,'ds_subscription_credits',true);
    $topup   = (int)get_user_meta($uid,'ds_topup_credits',true);
    $carried = (int)get_user_meta($uid,'ds_carried_credits',true);
    $cb      = (int)get_user_meta($uid,'ds_creditback_credits',true);
    if ($sub || $topup || $carried || $cb) return max(0, $sub+$topup+$carried+$cb);
    return max(0, (int)get_user_meta($uid,'ds_ai_credits',true));
}
}

if (!function_exists('ds_deduct_credits')) {
function ds_deduct_credits($uid, $amount) {
    $amount = (int)$amount;
    if ($amount <= 0) return true;
    $bal = ds_get_credits($uid);
    if ($bal < $amount) return false;           // HARD refuse — never overspend

    $sub     = (int)get_user_meta($uid,'ds_subscription_credits',true);
    $topup   = (int)get_user_meta($uid,'ds_topup_credits',true);
    $carried = (int)get_user_meta($uid,'ds_carried_credits',true);
    $cb      = (int)get_user_meta($uid,'ds_creditback_credits',true);

    if ($sub || $topup || $carried || $cb) {     // pool mode
        // Deduct order: subscription → carried → creditback → topup
        $order = array('ds_subscription_credits','ds_carried_credits','ds_creditback_credits','ds_topup_credits');
        $rem = $amount;
        foreach ($order as $key) {
            if ($rem <= 0) break;
            $have = (int)get_user_meta($uid, $key, true);
            if ($have <= 0) continue;
            $take = min($have, $rem);
            update_user_meta($uid, $key, $have - $take);
            $rem -= $take;
        }
        update_user_meta($uid,'ds_ai_credits', ds_get_credits($uid)); // keep legacy in sync
        return true;
    }

    // Legacy-only balance (no pools yet)
    $legacy = (int)get_user_meta($uid,'ds_ai_credits',true);
    update_user_meta($uid,'ds_ai_credits', max(0, $legacy - $amount));
    return true;
}
}

// Top-up purchase → topup pool, expires 1 month from purchase date
if (!function_exists('ds_add_credits')) {
function ds_add_credits($uid, $amount) {
    $amount = (int)$amount;
    if ($amount <= 0) return true;
    $topup = (int)get_user_meta($uid,'ds_topup_credits',true);
    update_user_meta($uid,'ds_topup_credits', $topup + $amount);
    update_user_meta($uid,'ds_topup_credits_expiry', ds_add_one_month());
    update_user_meta($uid,'ds_ai_credits', ds_get_credits($uid));
    return true;
}
}

// Credit back (marketplace cashback etc.) → own pool, expires 1 month from credit-back date
if (!function_exists('ds_add_creditback_credits')) {
function ds_add_creditback_credits($uid, $amount) {
    $amount = (int)$amount;
    if ($amount <= 0) return true;
    $cb = (int)get_user_meta($uid,'ds_creditback_credits',true);
    update_user_meta($uid,'ds_creditback_credits', $cb + $amount);
    update_user_meta($uid,'ds_creditback_credits_expiry', ds_add_one_month());
    update_user_meta($uid,'ds_ai_credits', ds_get_credits($uid));
    return true;
}
}

// Earliest upcoming expiry + how many credits expire on that date
if (!function_exists('ds_get_next_credit_expiry_info')) {
function ds_get_next_credit_expiry_info($uid) {
    $pairs = array(
        'ds_subscription_credits' => 'ds_subscription_credits_expiry',
        'ds_carried_credits'      => 'ds_carried_credits_expiry',
        'ds_creditback_credits'   => 'ds_creditback_credits_expiry',
        'ds_topup_credits'        => 'ds_topup_credits_expiry',
    );
    $list = array();
    foreach ($pairs as $bal_key => $exp_key) {
        $bal = (int)get_user_meta($uid, $bal_key, true);
        if ($bal <= 0) continue;
        $exp = get_user_meta($uid, $exp_key, true);
        // Old subscription credits with no expiry meta: expire at renewal date
        if (!$exp && $bal_key === 'ds_subscription_credits' && function_exists('ds_get_subscription_end')) {
            $end = ds_get_subscription_end($uid);
            if ($end) $exp = date('Y-m-d', strtotime($end));
        }
        if (!$exp) continue;
        if (!isset($list[$exp])) $list[$exp] = 0;
        $list[$exp] += $bal;
    }
    // Legacy-only balance (no pools yet): expires at subscription renewal
    if (!$list) {
        $legacy = (int)get_user_meta($uid, 'ds_ai_credits', true);
        $end    = function_exists('ds_get_subscription_end') ? ds_get_subscription_end($uid) : '';
        if ($legacy > 0 && $end) return array('date'=>date('Y-m-d', strtotime($end)), 'amount'=>$legacy);
        return array('date'=>'', 'amount'=>0);
    }
    ksort($list);
    $date = array_key_first($list);
    return array('date'=>$date, 'amount'=>$list[$date]);
}
}

// Backwards-compatible: date only
if (!function_exists('ds_get_next_credit_expiry')) {
function ds_get_next_credit_expiry($uid) {
    $i = ds_get_next_credit_expiry_info($uid);
    return $i['date'];
}
}
