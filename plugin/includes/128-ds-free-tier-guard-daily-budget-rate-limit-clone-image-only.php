<?php
/**
 * Code Snippets export — DS Free Tier Guard - Daily Budget + Rate Limit [CLONE - image only]
 *
 * Snippet ID : 808
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-122
 * Modified   : 2026-09-09 02:43:36
 * Load order : 128 of 144
 */
/**
 * DS Free Tier Guard - Daily Budget + Per-User Rate Limit
 *
 * Protects real API spend (WaveSpeed, image gen, etc.) from being run up by
 * bots or abuse on the FREE tier. Two layers, both scoped to non-premium
 * users only (the 'premium_member' role - same check already used by "DS
 * Pricing Plan Status" and "DS Premium Role Refresh" - is exempt from both;
 * they already pay via credits and are not the abuse vector this guards
 * against):
 *
 *   1. Per-user rate limit - a short cooldown between requests, plus a
 *      rolling-window cap, to stop rapid-fire abuse before it gets anywhere
 *      near the daily budget.
 *   2. A global daily $ budget for free-tier generation. Once today's
 *      estimated spend crosses the configured amount, ALL free-tier
 *      generation is disabled site-wide until the next day. Premium users
 *      are completely unaffected by this cap.
 *
Implementation: hooks the same rest_pre_dispatch filter already used by
 * the "Block generation until email verified" snippet (id 157, which gates
 * generate-image / generate-ppt) - runs ALONGSIDE that snippet, not instead
 * of it - no existing snippet touched.
 *
 * Scope: this gate only covers /ds/v1/generate-image. Video, music, speech
 * and PPT generation are intentionally NOT touched by this snippet - only
 * image generation has a free-tier cap right now.
 *
 * Everything below (daily budget, cooldown, window, per-type cost
 * estimates) is configurable from Settings -> Free Tier Limits in
 * wp-admin - no code edits needed to tune it.
 */

// ---------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------

if (!function_exists('ds_freegen_default_settings')) {
    function ds_freegen_default_settings() {
        return array(
            'enabled'          => 1,
            'daily_budget_usd' => 5.00,
            'cooldown_seconds' => 30,
            'daily_max'        => 2,
            'gap_hours'        => 6,
            // Rough per-generation cost estimates in USD. Starting point
            // derived from this site's own credit top-up pricing (about
            // $0.013-0.018 per credit - see ds_topup_defaults() in the
            // Pricing Settings snippet). Tune these as real provider
            // invoices come in - they don't need to be exact.
            'cost_image'   => 0.03,
        );
    }
}

if (!function_exists('ds_freegen_get_settings')) {
    function ds_freegen_get_settings() {
        $saved = get_option('ds_freegen_settings', array());
        if (!is_array($saved)) $saved = array();
        return array_merge(ds_freegen_default_settings(), $saved);
    }
}

// ---------------------------------------------------------------------
// Admin settings page: Settings -> Free Tier Limits
// ---------------------------------------------------------------------

add_action('admin_menu', function () {
    add_options_page(
        'Free Tier Limits',
        'Free Tier Limits',
        'manage_options',
        'ds-freegen-limits',
        'ds_freegen_render_settings_page'
    );
});

function ds_freegen_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $settings = ds_freegen_get_settings();
    $notice = '';

    if (!empty($_POST['ds_freegen_save']) && check_admin_referer('ds_freegen_save_settings')) {
        $settings = array(
            'enabled'          => !empty($_POST['enabled']) ? 1 : 0,
            'daily_budget_usd' => max(0, (float) $_POST['daily_budget_usd']),
            'cooldown_seconds' => max(0, (int) $_POST['cooldown_seconds']),
            'daily_max'        => max(1, (int) $_POST['daily_max']),
            'gap_hours'        => max(0, (int) $_POST['gap_hours']),
            'cost_image'        => max(0, (float) $_POST['cost_image']),
        );
        update_option('ds_freegen_settings', $settings, false);
        $notice = 'Saved.';
    }

    if (!empty($_POST['ds_freegen_reset_today']) && check_admin_referer('ds_freegen_save_settings')) {
        delete_option('ds_freegen_spend_' . current_time('Y-m-d'));
        $notice = "Today's counter has been reset.";
    }

    $today_spend = (float) get_option('ds_freegen_spend_' . current_time('Y-m-d'), 0);
    $pct = $settings['daily_budget_usd'] > 0 ? min(100, round(($today_spend / $settings['daily_budget_usd']) * 100)) : 0;
    ?>
    <div class="wrap">
        <h1>Free Tier Limits</h1>
        <p>Controls the daily spend cap and per-user rate limit for <strong>free-tier image generation</strong> only. Video, music, speech and PPT generation are not affected by this gate. Premium members are never affected by these limits.</p>

        <?php if ($notice): ?>
            <div class="notice notice-success"><p><?php echo esc_html($notice); ?></p></div>
        <?php endif; ?>

        <div class="notice notice-info" style="padding:12px;">
            <strong>Today's estimated free-tier spend:</strong>
            $<?php echo esc_html(number_format($today_spend, 4)); ?>
            of $<?php echo esc_html(number_format($settings['daily_budget_usd'], 2)); ?>
            (<?php echo esc_html($pct); ?>%)
        </div>

        <form method="post">
            <?php wp_nonce_field('ds_freegen_save_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Enabled</th>
                    <td><label><input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], 1); ?> /> Turn on the daily budget cap and rate limit</label></td>
                </tr>
                <tr>
                    <th scope="row">Daily budget ($)</th>
                    <td><input type="number" step="0.01" min="0" name="daily_budget_usd" value="<?php echo esc_attr($settings['daily_budget_usd']); ?>" class="regular-text" />
                        <p class="description">Once today's estimated free-tier <strong>image-generation</strong> spend reaches this, free image generation is disabled site-wide until midnight (site time). Video/audio/PPT generation and paid/premium members are unaffected.</p></td>
                </tr>
                <tr>
                    <th scope="row">Per-user cooldown (seconds)</th>
                    <td><input type="number" min="0" name="cooldown_seconds" value="<?php echo esc_attr($settings['cooldown_seconds']); ?>" class="regular-text" />
                        <p class="description">Minimum gap between one free user's generation requests. 0 disables this layer.</p></td>
                </tr>
                <tr>
                    <th scope="row">Daily free generation limit</th>
                    <td>
                        <input type="number" min="1" name="daily_max" value="<?php echo esc_attr($settings['daily_max']); ?>" style="width:70px;" /> free generations per calendar day, per user.
                        <p class="description">Resets at midnight (site time). This is the hard daily cap, e.g. 2 free generations per day.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Minimum gap between generations</th>
                    <td>
                        <input type="number" min="0" name="gap_hours" value="<?php echo esc_attr($settings['gap_hours']); ?>" style="width:70px;" /> hours.
                        <p class="description">The 2nd (and later) free generation on the same day must wait at least this many hours after the previous one. 0 disables this spacing requirement (only the daily cap above still applies).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Estimated cost per image generation ($)</th>
                    <td>
                        <input type="number" step="0.001" min="0" name="cost_image" value="<?php echo esc_attr($settings['cost_image']); ?>" style="width:90px;" />
                        <p class="description">Rough per-image cost estimate used to track the daily budget above. Not exact provider billing - tune as you learn real costs.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="ds_freegen_save" value="1" class="button button-primary">Save Changes</button>
                <button type="submit" name="ds_freegen_reset_today" value="1" class="button" onclick="return confirm('Reset today\'s spend counter to \$0?');">Reset today's counter</button>
            </p>
        </form>
    </div>
    <?php
}

// ---------------------------------------------------------------------
// Per-user rate limiting helpers
// ---------------------------------------------------------------------

if (!function_exists('ds_freegen_check_rate_limit')) {
    function ds_freegen_check_rate_limit($uid, $settings) {
        if ($settings['cooldown_seconds'] > 0) {
            if (get_transient('ds_freegen_cd_' . $uid)) {
                return 'Please wait a bit before generating again.';
            }
        }

        // Per-user, per-calendar-day counter (resets at local midnight).
        $day     = current_time('Y-m-d');
        $day_key = 'ds_freegen_daily_' . $uid . '_' . $day;
        $rec     = get_transient($day_key);
        if (!is_array($rec)) {
            $rec = array('count' => 0, 'last_ts' => 0);
        }

        $daily_max = isset($settings['daily_max']) ? (int) $settings['daily_max'] : 2;
        if ($daily_max > 0 && $rec['count'] >= $daily_max) {
            return "You've used today's free image generations. Please try again tomorrow.";
        }

        // Require a minimum gap since the previous generation *today* - this is what
        // stops "2 free generations back to back" and forces real spacing between them.
        $gap_seconds = (isset($settings['gap_hours']) ? (float) $settings['gap_hours'] : 6) * HOUR_IN_SECONDS;
        if ($rec['count'] > 0 && $gap_seconds > 0) {
            $elapsed = time() - $rec['last_ts'];
            if ($elapsed < $gap_seconds) {
                $remaining  = $gap_seconds - $elapsed;
                $hours_left = floor($remaining / HOUR_IN_SECONDS);
                $mins_left  = max(1, ceil(($remaining - ($hours_left * HOUR_IN_SECONDS)) / 60));
                $parts = array();
                if ($hours_left > 0) $parts[] = $hours_left . ' hour' . ($hours_left == 1 ? '' : 's');
                $parts[] = $mins_left . ' minute' . ($mins_left == 1 ? '' : 's');
                return 'Please wait about ' . implode(' ', $parts) . ' before your next free image generation today.';
            }
        }

        return true;
    }
}

if (!function_exists('ds_freegen_record_usage')) {
    function ds_freegen_record_usage($uid, $settings, $cost) {
        if ($settings['cooldown_seconds'] > 0) {
            set_transient('ds_freegen_cd_' . $uid, 1, $settings['cooldown_seconds']);
        }

        // Daily count + last-generation timestamp, keyed per calendar day so it
        // resets automatically at midnight (site time) with no cleanup cron needed.
        $day     = current_time('Y-m-d');
        $day_key = 'ds_freegen_daily_' . $uid . '_' . $day;
        $rec     = get_transient($day_key);
        if (!is_array($rec)) {
            $rec = array('count' => 0, 'last_ts' => 0);
        }
        $rec['count']++;
        $rec['last_ts'] = time();
        $seconds_until_midnight = strtotime('tomorrow midnight', current_time('timestamp')) - current_time('timestamp');
        set_transient($day_key, $rec, max(60, $seconds_until_midnight + 300));

        global $wpdb;
        $option_name = 'ds_freegen_spend_' . current_time('Y-m-d');
        $existing = $wpdb->get_var($wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", $option_name));
        if ($existing) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->options} SET option_value = CAST(option_value AS DECIMAL(10,4)) + %f WHERE option_name = %s",
                $cost, $option_name
            ));
            // This site runs a persistent object cache (confirmed via wp_using_ext_object_cache()).
            // A direct SQL UPDATE bypasses WP's option cache, so without this delete, get_option()
            // would keep returning the stale pre-update value forever and the daily budget cap
            // would never actually trigger once the option was first cached.
            wp_cache_delete($option_name, 'options');
        } else {
            add_option($option_name, $cost, '', 'no');
            wp_cache_delete($option_name, 'options');
        }
    }
}

// ---------------------------------------------------------------------
// The gate
// ---------------------------------------------------------------------

add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    if ($result !== null) return $result; // another gate (e.g. email verification) already decided

    $route = $request->get_route();
    $type_map = array(
        '/ds/v1/generate-image' => 'cost_image',
        // Video, music, speech and PPT generation are intentionally NOT in this
        // map - this gate only covers image generation for now.
    );

    $cost_key = null;
    foreach ($type_map as $r => $key) {
        if (strpos($route, $r) !== false) { $cost_key = $key; break; }
    }
    if ($cost_key === null) return $result; // not a generation-start route

    $settings = ds_freegen_get_settings();
    if (empty($settings['enabled'])) return $result;

    $uid = get_current_user_id();
    if (!$uid) return $result; // anonymous - the real endpoint's own is_user_logged_in() check handles this

    $user = wp_get_current_user();
    $is_premium = in_array('premium_member', (array) $user->roles, true) || current_user_can('manage_options')
        || ( function_exists('ds_access_plan') && ds_access_plan($uid) !== 'free' );   /* paid plan, or credits after the plan ended */
    if ($is_premium) return $result; // premium/admin users are exempt from both layers

    // Layer 2: daily global budget - a hard site-wide stop for free users.
    $today_spend = (float) get_option('ds_freegen_spend_' . current_time('Y-m-d'), 0);
    if ($settings['daily_budget_usd'] > 0 && $today_spend >= $settings['daily_budget_usd']) {
        return new WP_REST_Response(array(
            'error'   => true,
            'message' => 'Free image generation is temporarily unavailable - please try again tomorrow.',
        ), 403);
    }

    // Layer 1: per-user rate limit.
    $rl = ds_freegen_check_rate_limit($uid, $settings);
    if ($rl !== true) {
        return new WP_REST_Response(array('error' => true, 'message' => $rl), 429);
    }

    // Allowed - record estimated usage against both layers.
    $cost = isset($settings[$cost_key]) ? (float) $settings[$cost_key] : 0;
    ds_freegen_record_usage($uid, $settings, $cost);

    return $result; // null -> proceed to the real handler
}, 20, 3);
