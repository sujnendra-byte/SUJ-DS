<?php
/**
 * Code Snippets export — DS Pricing Settings
 *
 * Snippet ID : 746
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-060
 * Modified   : 2026-09-09 02:45:40
 * Load order : 060 of 144
 */
/**
 * Snippet Name: DS Pricing Settings
 * Run: Everywhere
 * Purpose: Admin-editable Plan IDs, prices & credits for the DS AI Pricing Page
 * (feeds the [ds_ai_pricing] shortcode in "Snippet 13 ds ai pricing")
 */

if (!function_exists('ds_pricing_defaults')) {
    function ds_pricing_defaults() {
        return array(
            'premium' => array(
                'label'       => 'Premium',
                'credits'     => 150,
                'usd_price'   => '$2.99/mo',
                'inr_price'   => '₹249/mo',
                'plan_id_usd' => 'plan_T52Y8CXO5RLpd6',
                'plan_id_inr' => 'plan_T52ZVGkju6U7vv',
            ),
            'pro' => array(
                'label'       => 'Pro',
                'credits'     => 300,
                'usd_price'   => '$4.99/mo',
                'inr_price'   => '₹450/mo',
                'plan_id_usd' => 'plan_T52aZskjDAa15X',
                'plan_id_inr' => 'plan_T52bNWgqvAMaab',
            ),
            'business' => array(
                'label'       => 'Business',
                'credits'     => 550,
                'usd_price'   => '$7.99/mo',
                'inr_price'   => '₹650/mo',
                'plan_id_usd' => 'plan_T52c40mtfhCQt8',
                'plan_id_inr' => 'plan_T52d8DrwB4H5X0',
            ),
        );
    }
}

if (!function_exists('ds_get_pricing_plans')) {
    function ds_get_pricing_plans() {
        $defaults = ds_pricing_defaults();
        $saved    = get_option('ds_pricing_plans', array());
        if (!is_array($saved)) $saved = array();
        $out = array();
        foreach ($defaults as $key => $def) {
            $row = isset($saved[$key]) && is_array($saved[$key]) ? $saved[$key] : array();
            $out[$key] = array_merge($def, $row);
        }
        return $out;
    }
}

if (!function_exists('ds_get_pricing_plan')) {
    function ds_get_pricing_plan($key) {
        $plans = ds_get_pricing_plans();
        return isset($plans[$key]) ? $plans[$key] : null;
    }
}

// ---- Top-up (Buy Extra Credits) tiers ----
if (!function_exists('ds_topup_defaults')) {
    function ds_topup_defaults() {
        return array(
            'tier1' => array('cr' => 100, 'usd' => '1.79', 'rs' => 130, 'color' => '#6366f1'),
            'tier2' => array('cr' => 200, 'usd' => '2.89', 'rs' => 230, 'color' => '#8b5cf6'),
            'tier3' => array('cr' => 300, 'usd' => '3.99', 'rs' => 320, 'color' => '#7c3aed'),
        );
    }
}

if (!function_exists('ds_get_topups')) {
    function ds_get_topups() {
        $defaults = ds_topup_defaults();
        $saved    = get_option('ds_topup_tiers', array());
        if (!is_array($saved)) $saved = array();
        $out = array();
        foreach ($defaults as $key => $def) {
            $row = isset($saved[$key]) && is_array($saved[$key]) ? $saved[$key] : array();
            $out[] = array(
                'cr'    => isset($row['cr']) ? (int)$row['cr'] : $def['cr'],
                'usd'   => isset($row['usd']) ? $row['usd'] : $def['usd'],
                'rs'    => isset($row['rs']) ? (int)$row['rs'] : $def['rs'],
                'color' => $def['color'],
            );
        }
        return $out;
    }
}

// ---- Admin menu + settings screen ----
add_action('admin_menu', function() {
    add_menu_page(
        'DS Pricing Settings',
        'DS Pricing',
        'manage_options',
        'ds-pricing-settings',
        function() {
            if (!current_user_can('manage_options')) {
                return;
            }

            $notice = '';
            if (isset($_POST['ds_pricing_save']) && check_admin_referer('ds_pricing_save_action', 'ds_pricing_nonce')) {
                $defaults = ds_pricing_defaults();
                $new = array();
                foreach ($defaults as $key => $def) {
                    $new[$key] = array(
                        'label'       => $def['label'],
                        'credits'     => isset($_POST['ds_credits'][$key]) ? max(0, intval($_POST['ds_credits'][$key])) : $def['credits'],
                        'usd_price'   => isset($_POST['ds_usd_price'][$key]) ? sanitize_text_field(wp_unslash($_POST['ds_usd_price'][$key])) : $def['usd_price'],
                        'inr_price'   => isset($_POST['ds_inr_price'][$key]) ? sanitize_text_field(wp_unslash($_POST['ds_inr_price'][$key])) : $def['inr_price'],
                        'plan_id_usd' => isset($_POST['ds_plan_id_usd'][$key]) ? sanitize_text_field(wp_unslash($_POST['ds_plan_id_usd'][$key])) : $def['plan_id_usd'],
                        'plan_id_inr' => isset($_POST['ds_plan_id_inr'][$key]) ? sanitize_text_field(wp_unslash($_POST['ds_plan_id_inr'][$key])) : $def['plan_id_inr'],
                    );
                }
                update_option('ds_pricing_plans', $new);

                $topup_defaults = ds_topup_defaults();
                $new_topups = array();
                foreach ($topup_defaults as $key => $def) {
                    $usd_raw = isset($_POST['ds_topup_usd'][$key]) ? wp_unslash($_POST['ds_topup_usd'][$key]) : $def['usd'];
                    $new_topups[$key] = array(
                        'cr'  => isset($_POST['ds_topup_cr'][$key]) ? max(0, intval($_POST['ds_topup_cr'][$key])) : $def['cr'],
                        'usd' => preg_replace('/[^0-9.]/', '', $usd_raw) !== '' ? preg_replace('/[^0-9.]/', '', $usd_raw) : $def['usd'],
                        'rs'  => isset($_POST['ds_topup_rs'][$key]) ? max(0, intval($_POST['ds_topup_rs'][$key])) : $def['rs'],
                    );
                }
                update_option('ds_topup_tiers', $new_topups);

                $notice = '<div class="notice notice-success is-dismissible"><p>Pricing settings saved.</p></div>';
            }

            $plans  = ds_get_pricing_plans();
            $topups = ds_get_topups();
            ?>
            <div class="wrap">
                <h1>DS Pricing Settings</h1>
                <p>Edit the Razorpay Plan ID, credits/month and displayed price for each paid plan. Changes appear immediately on the pricing page (<code>[ds_ai_pricing]</code>) &mdash; no code edits needed.</p>
                <?php echo $notice; ?>
                <form method="post">
                    <?php wp_nonce_field('ds_pricing_save_action', 'ds_pricing_nonce'); ?>
                    <table class="widefat striped" style="max-width:960px;margin-top:16px;">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Credits / month</th>
                                <th>Price (USD)</th>
                                <th>Price (INR)</th>
                                <th>Razorpay Plan ID (USD)</th>
                                <th>Razorpay Plan ID (INR)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($plans as $key => $p): ?>
                            <tr>
                                <td><strong><?php echo esc_html($p['label']); ?></strong></td>
                                <td><input type="number" min="0" name="ds_credits[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($p['credits']); ?>" style="width:90px;"></td>
                                <td><input type="text" name="ds_usd_price[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($p['usd_price']); ?>" style="width:110px;"></td>
                                <td><input type="text" name="ds_inr_price[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($p['inr_price']); ?>" style="width:110px;"></td>
                                <td><input type="text" name="ds_plan_id_usd[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($p['plan_id_usd']); ?>" style="width:220px;"></td>
                                <td><input type="text" name="ds_plan_id_inr[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($p['plan_id_inr']); ?>" style="width:220px;"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h2 style="margin-top:32px;">Buy Extra Credits (Top-ups)</h2>
                    <p>One-time credit packs shown further down the pricing page. Enter USD/INR prices as plain numbers (no $ or &#8377; symbol needed).</p>
                    <table class="widefat striped" style="max-width:640px;margin-top:8px;">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Credits</th>
                                <th>Price (USD)</th>
                                <th>Price (INR)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; foreach ($topups as $key => $t): ?>
                            <tr>
                                <td><strong>Tier <?php echo (int)$i; ?></strong></td>
                                <td><input type="number" min="0" name="ds_topup_cr[tier<?php echo (int)$i; ?>]" value="<?php echo esc_attr($t['cr']); ?>" style="width:90px;"></td>
                                <td>$<input type="text" name="ds_topup_usd[tier<?php echo (int)$i; ?>]" value="<?php echo esc_attr($t['usd']); ?>" style="width:80px;"></td>
                                <td>&#8377;<input type="number" min="0" name="ds_topup_rs[tier<?php echo (int)$i; ?>]" value="<?php echo esc_attr($t['rs']); ?>" style="width:90px;"></td>
                            </tr>
                        <?php $i++; endforeach; ?>
                        </tbody>
                    </table>

                    <p class="submit">
                        <button type="submit" name="ds_pricing_save" class="button button-primary">Save Pricing Settings</button>
                    </p>
                </form>
            </div>
            <?php
        },
        'dashicons-money-alt',
        58
    );
});
