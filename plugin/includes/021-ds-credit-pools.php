<?php
/**
 * Code Snippets export — DS Credit Pools
 *
 * Snippet ID : 707
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-021
 * Modified   : 2026-09-09 02:44:19
 * Load order : 021 of 144
 */
/**
 * Snippet Name: DS Credit Pools
 * Run: Everywhere
 * Description: Subscription / topup / creditback pools — ALL expire 1 month from purchase date.
 * Requires: DS Credit Core Override (Part 1)
 */

// ── Set subscription credits (called on plan activation) ─────
// $period_end = billing period end these credits belong to (Razorpay current_end).
function ds_pool_set_subscription_credits($uid, $amount, $is_upgrade = false, $period_end = '') {
    $old_sub = (int)get_user_meta($uid, 'ds_subscription_credits', true);

    $new_expiry = function_exists('ds_credit_expiry_for_period')
        ? ds_credit_expiry_for_period($period_end)
        : date('Y-m-d', strtotime($period_end ?: '+1 month'));

    if ($is_upgrade && $old_sub > 0) {
        // Mid-cycle UPGRADE — unused credits carry forward and now expire on the
        // SAME date as the new allocation, not on their original expiry.
        $carried = (int)get_user_meta($uid, 'ds_carried_credits', true);
        update_user_meta($uid, 'ds_carried_credits',        $carried + $old_sub);
        update_user_meta($uid, 'ds_carried_credits_expiry', $new_expiry);
    } else {
        // Fresh subscription — old subscription credits lapse, nothing carries.
        delete_user_meta($uid, 'ds_carried_credits');
        delete_user_meta($uid, 'ds_carried_credits_expiry');
    }

    update_user_meta($uid, 'ds_subscription_credits',        max(0, $amount));
    update_user_meta($uid, 'ds_subscription_credits_expiry', $new_expiry);
    // topup / creditback deliberately untouched — they run on their own purchase-date clocks

    ds_pool_sync_legacy($uid);
}

// ── Monthly renewal — old subscription credits LAPSE, new allocation replaces them ──
function ds_pool_renew_subscription($uid, $plan_credits, $period_end = '') {
    $new_expiry = function_exists('ds_credit_expiry_for_period')
        ? ds_credit_expiry_for_period($period_end)
        : date('Y-m-d', strtotime($period_end ?: '+1 month'));

    // Straight replace, never add — this is what makes last month's credits lapse.
    update_user_meta($uid, 'ds_subscription_credits',        max(0, $plan_credits));
    update_user_meta($uid, 'ds_subscription_credits_expiry', $new_expiry);
    // topup / creditback survive renewal by design
    ds_pool_sync_legacy($uid);
}

// ── On subscription break (cancel/expire) ─────────────────────
// Credits already carry their own expiry from purchase date, so we only
// backfill an expiry for old balances that never got one (legacy users).
function ds_pool_subscription_broken($uid) {
    $map = array(
        'ds_subscription_credits' => 'ds_subscription_credits_expiry',
        'ds_topup_credits'        => 'ds_topup_credits_expiry',
        'ds_creditback_credits'   => 'ds_creditback_credits_expiry',
    );
    foreach ($map as $bal_key => $exp_key) {
        $bal = (int)get_user_meta($uid, $bal_key, true);
        if ($bal > 0 && !get_user_meta($uid, $exp_key, true)) {
            update_user_meta($uid, $exp_key, ds_add_one_month());
        }
    }
}

// ── Sync legacy ds_ai_credits meta ────────────────────────────
function ds_pool_sync_legacy($uid) {
    $sub     = (int)get_user_meta($uid, 'ds_subscription_credits', true);
    $topup   = (int)get_user_meta($uid, 'ds_topup_credits',        true);
    $carried = (int)get_user_meta($uid, 'ds_carried_credits',      true);
    $cb      = (int)get_user_meta($uid, 'ds_creditback_credits',   true);
    update_user_meta($uid, 'ds_ai_credits', max(0, $sub + $topup + $carried + $cb));
}

// ── Migrate existing users to pool system (unchanged) ─────────
function ds_pool_migrate_user($uid) {
    if (metadata_exists('user', $uid, 'ds_subscription_credits')) return;
    $legacy  = (int)get_user_meta($uid, 'ds_ai_credits', true);
    $plan    = function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : 'free';
    $plans   = array('free'=>0,'premium'=>150,'pro'=>350,'business'=>600);
    $plan_cr = $plans[$plan] ?? 0;
    if ($plan !== 'free' && $plan_cr > 0) {
        $sub_cr   = min($legacy, $plan_cr);
        $topup_cr = max(0, $legacy - $plan_cr);
    } else {
        $sub_cr   = 0;
        $topup_cr = $legacy;
    }
    update_user_meta($uid, 'ds_subscription_credits', $sub_cr);
    update_user_meta($uid, 'ds_topup_credits', $topup_cr);
    update_user_meta($uid, 'ds_pool_migrated', true);
}

// ── Daily cron — expire credits ───────────────────────────────
add_action('init', function() {
    if (!wp_next_scheduled('ds_credit_expiry_cron')) {
        wp_schedule_event(time(), 'daily', 'ds_credit_expiry_cron');
    }
});

add_action('ds_credit_expiry_cron', function() {
    $today = current_time('Y-m-d');
    global $wpdb;

    // pool balance key => expiry key
    $pools = array(
        'ds_subscription_credits' => 'ds_subscription_credits_expiry',
        'ds_topup_credits'        => 'ds_topup_credits_expiry',
        'ds_creditback_credits'   => 'ds_creditback_credits_expiry',
        'ds_carried_credits'      => 'ds_carried_credits_expiry',
    );

    foreach ($pools as $bal_key => $exp_key) {
        $users = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->usermeta}
             WHERE meta_key = %s AND meta_value != '' AND meta_value < %s",
            $exp_key, $today
        ));
        foreach ($users as $uid) {
            $bal = (int)get_user_meta($uid, $bal_key, true);
            if ($bal > 0) {
                update_user_meta($uid, $bal_key, 0);
                error_log("DS Credits: {$bal_key} expired for user {$uid} ({$bal}cr)");
            }
            delete_user_meta($uid, $exp_key);
            ds_pool_sync_legacy($uid);
        }
    }
});

// ── One-time backfill: existing topup balances get an expiry ──
// (Old topups had no expiry date. This gives them 1 month from today, once.)
add_action('init', function() {
    if (get_option('ds_topup_expiry_backfill_done')) return;
    $users = get_users(array('number'=>200,'meta_key'=>'ds_topup_credits','meta_compare'=>'EXISTS'));
    foreach ($users as $u) {
        $topup = (int)get_user_meta($u->ID, 'ds_topup_credits', true);
        if ($topup > 0 && !get_user_meta($u->ID, 'ds_topup_credits_expiry', true)) {
            update_user_meta($u->ID, 'ds_topup_credits_expiry', ds_add_one_month());
        }
    }
    if (count($users) < 200) update_option('ds_topup_expiry_backfill_done', true);
}, 25);

// ── Migrate all existing users on first run (unchanged) ───────
add_action('init', function() {
    if (get_option('ds_pool_migration_done')) return;
    $users = get_users(array('number'=>50,'meta_key'=>'ds_ai_credits','meta_compare'=>'EXISTS','meta_query'=>array(array('key'=>'ds_pool_migrated','compare'=>'NOT EXISTS'))));
    foreach ($users as $u) {
        ds_pool_migrate_user($u->ID);
    }
    if (count($users) < 50) {
        update_option('ds_pool_migration_done', true);
    }
}, 20);
// Clear refund locks older than a week so wp_options doesn't grow forever.
add_action('ds_credit_expiry_cron', function () {
    global $wpdb;
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ds\\_refunded\\_%%' AND CAST(option_value AS UNSIGNED) < %d",
        time() - (7 * DAY_IN_SECONDS)
    ) );
});
