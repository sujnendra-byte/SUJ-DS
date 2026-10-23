<?php
/**
 * Code Snippets export — DS Account Dropdown [CLONE] - Refund + Cancel Buttons
 *
 * Snippet ID : 795
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-109
 * Modified   : 2026-09-09 02:46:10
 * Load order : 114 of 144
 */
/**
 * Snippet Name: DS Account Dropdown
 * Run: Everywhere
 * Bigger popup with credit usage history tab
 */
add_filter('wp_nav_menu_items', function($items, $args) {
    if ($args->theme_location !== 'primary') return $items;

    if (is_user_logged_in()) {
        $user    = wp_get_current_user();
        $uid     = $user->ID;
        $initial = strtoupper(substr($user->display_name, 0, 1));

        $plan        = function_exists('ds_get_user_plan')          ? ds_get_user_plan($uid)          : 'free';
        $plan_status = function_exists('ds_get_user_plan_status')   ? ds_get_user_plan_status($uid)   : 'active';
        $credits     = function_exists('ds_get_credits')            ? ds_get_credits($uid)            : 0;
        $end_date    = function_exists('ds_get_subscription_end')   ? ds_get_subscription_end($uid)   : '';
        $pending_dg  = function_exists('ds_get_pending_downgrade')  ? ds_get_pending_downgrade($uid)  : '';
		$exp_info = function_exists('ds_get_next_credit_expiry_info') ? ds_get_next_credit_expiry_info($uid) : array('date'=>'','amount'=>0);

        if (!function_exists('ds_get_user_plan')) {
            $roles = (array)$user->roles;
            if      (in_array('business_member', $roles)) $plan = 'business';
            elseif  (in_array('pro_member',      $roles)) $plan = 'pro';
            elseif  (in_array('premium_member',  $roles)) $plan = 'premium';
            else                                           $plan = 'free';
        }

        $plans = array(
            'free'     => array('label'=>'Free',     'color'=>'#6b7280'),
            'premium'  => array('label'=>'Premium',  'color'=>'#6366f1'),
            'pro'      => array('label'=>'Pro',      'color'=>'#8b5cf6'),
            'business' => array('label'=>'Business', 'color'=>'#0ea5e9'),
        );
        $plan_label = $plans[$plan]['label'] ?? ucfirst($plan);
        $plan_color = $plans[$plan]['color'] ?? '#6b7280';
        $is_paid    = in_array($plan, array('premium','pro','business')) && $plan_status === 'active';

        $status_msg = '';
        if ($plan_status === 'cancelled' && $end_date) {
            $status_msg = 'Plan active until ' . date('j M Y', strtotime($end_date)) . '. Reverts to Free after.';
        } elseif ($pending_dg && $end_date) {
            $pd = $plans[$pending_dg]['label'] ?? ucfirst($pending_dg);
            $status_msg = 'Downgrading to ' . $pd . ' on ' . date('j M Y', strtotime($end_date)) . '.';
        } elseif ($plan_status === 'expired') {
            $status_msg = 'Subscription expired. Renew to continue.';
        }

        $nonce       = wp_create_nonce('wp_rest');
        $rest_url    = trailingslashit(rest_url('ds/v1'));
        $logout_url  = wp_logout_url(home_url());
        $pricing_url = home_url('/pricing');
        $studio_url  = home_url('/ai-studio');
        $images_url  = home_url('/my-generated-images');

        $dl_limits_table = array(
            'free'     => array('period' => 'day',   'free' => 5,    'pro' => 0),
            'premium'  => array('period' => 'month', 'free' => 500,  'pro' => 50),
            'pro'      => array('period' => 'month', 'free' => 1000, 'pro' => 200),
            'business' => array('period' => 'month', 'free' => -1,   'pro' => 600),
        );
        $dl_lim          = isset($dl_limits_table[$plan]) ? $dl_limits_table[$plan] : $dl_limits_table['free'];
        $dl_limit_free   = $dl_lim['free'];
        $dl_limit_pro    = $dl_lim['pro'];
        $dl_can_pro      = ($dl_limit_pro !== 0);
        $dl_period_label = ($dl_lim['period'] === 'month') ? 'Resets monthly' : 'Resets at midnight UTC';

        $ds_first_name = $user->first_name ? $user->first_name : $user->display_name;
        $ds_first_name = trim(strtok($ds_first_name, ' '));
        $ds_first_name = trim(strtok($ds_first_name, '@'));
        if (mb_strlen($ds_first_name) > 6) $ds_first_name = mb_substr($ds_first_name, 0, 6) . '...';
        $items .= '
        <li class="menu-item ds-acct-nav-item" style="position:relative;list-style:none;">
            <a href="#" id="ds-avatar-trigger" onclick="dsAcctOpen(event)" style="display:flex;align-items:center;gap:8px;padding:4px 10px;text-decoration:none;border-radius:100px;transition:background .15s;" onmouseover="this.style.background=\'rgba(99,102,241,.08)\'" onmouseout="this.style.background=\'transparent\'">
                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,' . $plan_color . ',' . $plan_color . '99);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;border:2px solid ' . $plan_color . '40;">' . $initial . '</div>
                <span style="font-size:13px;font-weight:600;color:#1a1a1a;">Hi, ' . esc_html($ds_first_name) . '</span>
            </a>
        </li>';;

        $refund_info = function_exists('ds_get_refund_window_info') ? ds_get_refund_window_info($uid) : array('eligible'=>false,'hours_left'=>0);

		add_action('wp_footer', function() use (
    $uid, $initial, $user, $plan, $plan_label, $plan_color,
    $plan_status, $credits, $end_date, $pending_dg, $status_msg, $exp_info,
            $is_paid, $nonce, $rest_url, $logout_url, $pricing_url,
            $studio_url, $images_url, $plans,
            $dl_limit_free, $dl_limit_pro, $dl_can_pro, $dl_period_label, $refund_info
        ) { ?>
			
<style>
#ds-acct-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:999998;}
#ds-acct-modal{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:520px;max-width:calc(100vw - 20px);max-height:92vh;background:#fff;border-radius:24px;box-shadow:0 28px 90px rgba(0,0,0,.2);z-index:999999;overflow:hidden;display:none;flex-direction:column;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
#ds-modal-body{overflow-y:auto;flex:1;}
#ds-modal-body::-webkit-scrollbar{width:4px;}
#ds-modal-body::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:4px;}
.ds-tab-btn{padding:9px 16px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;color:#9ca3af;border-bottom:2.5px solid transparent;transition:all .15s;font-family:inherit;}
.ds-tab-btn.active{color:#6366f1;border-bottom-color:#6366f1;}
.ds-tab-pane{display:none;padding:16px 20px 20px;}
.ds-tab-pane.active{display:block;}
.ds-cr-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f3f4f6;}
.ds-cr-row:last-child{border-bottom:none;}
.ds-cr-thumb{width:44px;height:44px;border-radius:10px;object-fit:cover;flex-shrink:0;background:#f3f4f6;}
.ds-cr-thumb-placeholder{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#f3f4f6,#e5e7eb);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;}
.ds-action-btn{display:flex;align-items:center;gap:9px;padding:11px 14px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;transition:opacity .15s;cursor:pointer;border:none;font-family:inherit;width:100%;box-sizing:border-box;}
.ds-action-btn:hover{opacity:.85;}
</style>

<!-- OVERLAY -->
<div id="ds-acct-overlay" onclick="dsAcctClose()"></div>

<!-- MODAL -->
<div id="ds-acct-modal">

    <!-- HEADER -->
    <div style="padding:20px 22px 0;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,<?php echo $plan_color; ?>,<?php echo $plan_color; ?>99);display:flex;align-items:center;justify-content:center;font-size:21px;font-weight:900;color:#fff;flex-shrink:0;border:3px solid <?php echo $plan_color; ?>30;"><?php echo $initial; ?></div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:16px;font-weight:800;color:#0f0f23;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($user->display_name); ?></div>
                <div style="font-size:12px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($user->user_email); ?></div>
                <span style="display:inline-block;margin-top:4px;font-size:10px;font-weight:800;padding:2px 10px;border-radius:100px;background:<?php echo $plan_color; ?>18;color:<?php echo $plan_color; ?>;"><?php echo esc_html($plan_label); ?> Plan</span>
            </div>
            <button onclick="dsAcctClose()" style="width:30px;height:30px;border-radius:50%;border:none;background:transparent;cursor:pointer;font-size:18px;color:#6b7280;display:flex;align-items:center;justify-content:center;flex-shrink:0;">&#x2715;</button>
        </div>

        <!-- CREDIT CARD -->
        <div style="background:linear-gradient(135deg,#0f0f23,#1e1040);border-radius:18px;padding:18px 20px;margin-bottom:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div>
                    <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px;">Credit Balance</div>
                    <div style="display:flex;align-items:baseline;gap:8px;">
                        <span style="font-size:42px;font-weight:900;color:#fff;line-height:1;" id="ds-popup-cr"><?php echo number_format($credits); ?></span>
                        <span style="font-size:14px;color:rgba(255,255,255,.4);">credits</span>
                    </div>
                    <div id="ds-popup-expiry" style="font-size:11px;color:#fbbf24;margin-top:4px;"><?php echo (!empty($exp_info['date']) && $exp_info['amount'] > 0) ? '&#9203; ' . number_format($exp_info['amount']) . ' credits expire on ' . date('j M Y', strtotime($exp_info['date'])) : ''; ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:10px;color:rgba(255,255,255,.4);margin-bottom:6px;">Plan</div>
                    <span style="font-size:13px;font-weight:800;padding:4px 12px;border-radius:100px;background:<?php echo $plan_color; ?>;color:#fff;" id="ds-popup-plan-badge"><?php echo esc_html($plan_label); ?></span>
                    <?php if ($end_date && $plan !== 'free'): ?>
                    <div style="font-size:10px;color:rgba(255,255,255,.3);margin-top:6px;" id="ds-popup-end-date">
                        <?php echo $plan_status === 'cancelled' ? 'Ends' : 'Renews'; ?> <?php echo date('j M Y', strtotime($end_date)); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($status_msg): ?>
            <div id="ds-popup-status-msg" style="margin-top:12px;background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.3);border-radius:10px;padding:9px 12px;font-size:11px;color:#fbbf24;line-height:1.5;"><?php echo esc_html($status_msg); ?></div>
            <?php else: ?>
<div id="ds-popup-status-msg" style="display:none;"></div>
<?php endif; ?>
        </div>

        <!-- TABS -->
        <div style="display:flex;border-bottom:1.5px solid #f3f4f6;margin:0 -2px;">
            <button class="ds-tab-btn active" onclick="dsTab('overview',this)">Overview</button>
            <button class="ds-tab-btn" onclick="dsTab('history',this)">Credit History</button>
            <button class="ds-tab-btn" onclick="dsTab('plans',this)">Plans</button>
        </div>
    </div>

    <!-- SCROLLABLE BODY -->
    <div id="ds-modal-body">

        <!-- TAB: OVERVIEW -->
        <div class="ds-tab-pane active" id="ds-tab-overview">

            <!-- QUICK STATS -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                <div style="background:#f8f9ff;border-radius:12px;padding:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:900;color:#6366f1;" id="ds-stat-credits"><?php echo number_format($credits); ?></div>
                    <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Credits Left</div>
                </div>
                <div style="background:#f8f9ff;border-radius:12px;padding:12px;text-align:center;">
                    <div style="font-size:20px;font-weight:900;color:#10b981;" id="ds-stat-images">-</div>
                    <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Images Made</div>
                </div>
            </div>

           <!-- DAILY DOWNLOAD COUNTER -->
            <?php
            $dl_limits_table = array(
                'free'     => array('period' => 'day',   'free' => 5,    'pro' => 0),
                'premium'  => array('period' => 'month', 'free' => 500,  'pro' => 50),
                'pro'      => array('period' => 'month', 'free' => 1000, 'pro' => 200),
                'business' => array('period' => 'month', 'free' => -1,   'pro' => 600),
            );
            $dl_lim          = isset($dl_limits_table[$plan]) ? $dl_limits_table[$plan] : $dl_limits_table['free'];
            $dl_limit_free   = $dl_lim['free'];
            $dl_limit_pro    = $dl_lim['pro'];
            $dl_can_pro      = ($dl_limit_pro !== 0);
            $dl_period_label = ($dl_lim['period'] === 'month') ? 'Resets monthly' : 'Resets at midnight UTC';
            ?>
            <div style="background:#f8f9ff;border-radius:12px;padding:12px 14px;margin-bottom:10px;">
                <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px;">Downloads</div>

                <!-- Free content -->
                <?php if ($dl_limit_free === -1): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <span style="font-size:12px;font-weight:700;color:#374151;">Free content</span>
                    <span style="font-size:11px;font-weight:700;color:#10b981;">Unlimited</span>
                </div>
                <?php else: ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:12px;font-weight:700;color:#374151;">Free content</span>
                    <span id="ds-dl-free-left" style="font-size:11px;font-weight:900;color:#9ca3af;">...</span>
                </div>
                <div style="height:6px;background:#e5e7eb;border-radius:100px;overflow:hidden;margin-bottom:4px;">
                    <div id="ds-dl-free-bar" style="height:100%;width:0%;background:#10b981;border-radius:100px;transition:width .4s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span id="ds-dl-free-used" style="font-size:10px;color:#9ca3af;">loading...</span>
                    <span style="font-size:10px;color:#9ca3af;"><?php echo esc_html($dl_period_label); ?></span>
                </div>
                <?php endif; ?>

                <!-- Pro content -->
                <?php if (!$dl_can_pro): ?>
                <div style="padding:7px 10px;background:#fef3c7;border-radius:8px;font-size:10px;color:#92400e;">
                    👑 Pro items require a Premium, Pro, or Business plan
                </div>
                <?php elseif ($dl_limit_pro === -1): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:12px;font-weight:700;color:#374151;">Pro content</span>
                    <span style="font-size:11px;font-weight:700;color:#10b981;">Unlimited</span>
                </div>
                <?php else: ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:12px;font-weight:700;color:#374151;">Pro content</span>
                    <span id="ds-dl-pro-left" style="font-size:11px;font-weight:900;color:#9ca3af;">...</span>
                </div>
                <div style="height:6px;background:#e5e7eb;border-radius:100px;overflow:hidden;margin-bottom:4px;">
                    <div id="ds-dl-pro-bar" style="height:100%;width:0%;background:#7c3aed;border-radius:100px;transition:width .4s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span id="ds-dl-pro-used" style="font-size:10px;color:#9ca3af;">loading...</span>
                    <span style="font-size:10px;color:#9ca3af;"><?php echo esc_html($dl_period_label); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- ACTIONS -->
            <div style="display:flex;flex-direction:column;gap:7px;">
                <a href="<?php echo esc_url($studio_url); ?>" onclick="dsAcctClose()" class="ds-action-btn" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
                    Open AI Studio
                </a>
                <a href="<?php echo esc_url($images_url); ?>" onclick="dsAcctClose()" class="ds-action-btn" style="background:#f8f9ff;color:#374151;border:1.5px solid #e8eaf0;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#6366f1" stroke-width="1.8"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="#6366f1" stroke-width="1.8" stroke-linecap="round"/></svg>
                    My Generated Images
                </a>
                <a href="<?php echo esc_url($pricing_url); ?>" onclick="dsAcctClose()" class="ds-action-btn" style="background:#f8f9ff;color:#374151;border:1.5px solid #e8eaf0;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="#f59e0b" stroke-width="1.8"/></svg>
                    Buy Credits / Upgrade
                </a>
                <?php if ($is_paid && !empty($refund_info['eligible'])): ?>
<button id="ds-refund-btn" onclick="dsRefundShow()" class="ds-action-btn" style="background:#fff5f5;color:#ef4444;border:1.5px solid #fee2e2;">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 10h11a5 5 0 010 10H9M3 10l4-4M3 10l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
Request Refund <span style="opacity:.75;font-weight:600;">(<?php echo esc_html($refund_info['hours_left']); ?>h left)</span>
</button>
<?php endif; ?>
<?php if ($is_paid): ?>
                <button id="ds-cancel-btn" onclick="dsCancelShow()" class="ds-action-btn" style="background:#fff5f5;color:#ef4444;border:1.5px solid #fee2e2;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Cancel Subscription
                </button>
                <?php endif; ?>
                <a href="<?php echo esc_url($logout_url); ?>" class="ds-action-btn" style="background:#fff5f5;color:#ef4444;border:1.5px solid #fee2e2;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Sign Out
                </a>
            </div>

            <!-- CANCEL CONFIRM -->
            <div id="ds-cancel-confirm" style="display:none;margin-top:12px;background:#fff5f5;border:1.5px solid #fecaca;border-radius:14px;padding:16px;">
                <div style="font-size:13px;font-weight:700;color:#0f0f23;margin-bottom:6px;">Cancel your <?php echo esc_html($plan_label); ?> subscription?</div>
                <div style="font-size:12px;color:#6b7280;margin-bottom:14px;line-height:1.6;">Your plan stays active until <strong><?php echo $end_date ? date('j M Y', strtotime($end_date)) : 'end of billing period'; ?></strong>. After that you will be moved to Free.</div>
                <div style="display:flex;gap:8px;">
                    <button id="ds-cancel-confirm-btn" onclick="dsCancelConfirm()" style="flex:1;padding:10px;border-radius:9px;background:#ef4444;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;">Yes, Cancel</button>
                    <button onclick="dsCancelHide()" style="flex:1;padding:10px;border-radius:9px;background:#f3f4f6;color:#374151;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;">Keep Plan</button>
                </div>
            </div>
        </div>

        <?php if ($is_paid && !empty($refund_info['eligible'])): ?>
<div id="ds-refund-confirm" style="display:none;margin-top:12px;background:#fff5f5;border:1.5px solid #fecaca;border-radius:14px;padding:16px;">
<div style="font-size:13px;font-weight:700;color:#0f0f23;margin-bottom:6px;">Refund your <?php echo esc_html($plan_label); ?> payment?</div>
<div style="font-size:12px;color:#6b7280;margin-bottom:14px;line-height:1.6;">You'll get a full refund and your subscription will be cancelled with access removed immediately. Only available within 24 hours of payment, and only if no credits from this billing cycle have been used yet.</div>
<div style="display:flex;gap:8px;">
<button id="ds-refund-confirm-btn" onclick="dsRefundConfirm()" style="flex:1;padding:10px;border-radius:9px;background:#ef4444;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;">Yes, Refund Me</button>
<button onclick="dsRefundHide()" style="flex:1;padding:10px;border-radius:9px;background:#f3f4f6;color:#374151;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;">Keep Plan</button>
</div>
</div>
<?php endif; ?>

			<!-- TAB: CREDIT HISTORY -->
        <div class="ds-tab-pane" id="ds-tab-history">
            <div id="ds-history-loading" style="text-align:center;padding:30px;color:#9ca3af;font-size:13px;">Loading history...</div>
            <div id="ds-history-list" style="display:none;">
                <!-- Filled by JS -->
            </div>
            <div id="ds-history-empty" style="display:none;text-align:center;padding:40px 20px;">
                <div style="font-size:40px;margin-bottom:10px;">🎨</div>
                <div style="font-size:14px;font-weight:700;color:#374151;margin-bottom:6px;">No generations yet</div>
                <div style="font-size:12px;color:#9ca3af;margin-bottom:16px;">Go to AI Studio and create your first image!</div>
                <a href="<?php echo esc_url($studio_url); ?>" onclick="dsAcctClose()" style="display:inline-block;padding:10px 20px;border-radius:100px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:13px;font-weight:700;text-decoration:none;">Open Studio</a>
            </div>
        </div>

        <!-- TAB: PLANS -->
        <div class="ds-tab-pane" id="ds-tab-plans">
            <?php
            $plan_list = array(
  array('id'=>'free', 'label'=>'Free', 'credits'=>'5 images','price'=>'$0', 'color'=>'#6b7280'),
);
if (function_exists('ds_get_pricing_plans')) {
  $ds_dd_admin_plans = ds_get_pricing_plans();
  $ds_dd_colors = array('premium'=>'#6366f1','pro'=>'#8b5cf6','business'=>'#0ea5e9');
  foreach (array('premium','pro','business') as $ds_dd_pk) {
    if (empty($ds_dd_admin_plans[$ds_dd_pk])) continue;
    $ds_dd_p = $ds_dd_admin_plans[$ds_dd_pk];
    $ds_dd_price = preg_replace('#/mo\s*$#i', '', $ds_dd_p['usd_price']);
    $plan_list[] = array('id'=>$ds_dd_pk, 'label'=>$ds_dd_p['label'], 'credits'=>$ds_dd_p['credits'].'cr', 'price'=>$ds_dd_price, 'color'=>$ds_dd_colors[$ds_dd_pk]);
  }
} else {
  $plan_list[] = array('id'=>'premium', 'label'=>'Premium', 'credits'=>'150cr', 'price'=>'$2.99', 'color'=>'#6366f1');
  $plan_list[] = array('id'=>'pro', 'label'=>'Pro', 'credits'=>'300cr', 'price'=>'$4.99', 'color'=>'#8b5cf6');
  $plan_list[] = array('id'=>'business', 'label'=>'Business', 'credits'=>'550cr', 'price'=>'$7.99', 'color'=>'#0ea5e9');
}
            foreach ($plan_list as $pl):
                $is_current = ($pl['id'] === $plan) && ($plan_status === 'active' || $pl['id'] === 'free');
            ?>
            <div style="display:flex;align-items:center;padding:12px 14px;border-radius:13px;background:<?php echo $is_current ? $pl['color'].'10' : '#f9fafb'; ?>;border:1.5px solid <?php echo $is_current ? $pl['color'].'35' : '#f3f4f6'; ?>;margin-bottom:8px;">
                <div style="width:10px;height:10px;border-radius:50%;background:<?php echo $pl['color']; ?>;margin-right:12px;flex-shrink:0;"></div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:700;color:<?php echo $is_current ? $pl['color'] : '#374151'; ?>;"><?php echo $pl['label']; ?> <?php echo $is_current ? '<span style="font-size:10px;font-weight:600;opacity:.7;">(Current)</span>' : ''; ?></div>
                    <div style="font-size:11px;color:#9ca3af;"><?php echo $pl['credits']; ?> / month</div>
                </div>
                <div style="text-align:right;margin-right:12px;">
                    <div style="font-size:14px;font-weight:800;color:#0f0f23;"><?php echo $pl['price']; ?></div>
                    <div style="font-size:10px;color:#9ca3af;">/ month</div>
                </div>
                <?php if ($is_current): ?>
                <span style="font-size:10px;font-weight:800;padding:4px 10px;border-radius:100px;background:<?php echo $pl['color']; ?>;color:#fff;">Active</span>
                <?php elseif ($pl['id'] !== 'free'): ?>
                <a href="<?php echo esc_url($pricing_url); ?>" onclick="dsAcctClose()" style="font-size:11px;font-weight:700;padding:6px 12px;border-radius:100px;background:<?php echo $pl['color']; ?>;color:#fff;text-decoration:none;white-space:nowrap;">
                    <?php
                    $plan_order = array('free'=>0,'premium'=>1,'pro'=>2,'business'=>3);
                    $curr_ord   = $plan_order[$plan] ?? 0;
                    $this_ord   = $plan_order[$pl['id']] ?? 0;
                    echo $this_ord > $curr_ord ? 'Upgrade' : 'Switch';
                    ?>
                </a>
                <?php else: ?>
                <span style="font-size:10px;color:#9ca3af;">Default</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:14px;padding:13px;background:#f8f9ff;border-radius:12px;font-size:12px;color:#6b7280;text-align:center;line-height:1.6;">
                Credits are valid for 1 month from purchase<br>
                <a href="<?php echo esc_url($pricing_url); ?>" onclick="dsAcctClose()" style="color:#6366f1;font-weight:700;text-decoration:none;">View full pricing &rarr;</a>
            </div>
        </div>

    </div><!-- /ds-modal-body -->
</div><!-- /modal -->

<script>
(function(){
var REST='<?php echo esc_js($rest_url); ?>';
var NONCE='<?php echo esc_js($nonce); ?>';
var _loaded=false;

function q(id){return document.getElementById(id);}

window.dsAcctOpen=function(e){
    if(e)e.preventDefault();
    q('ds-acct-overlay').style.display='block';
    var m=q('ds-acct-modal');
    m.style.display='flex';
    document.body.style.overflow='hidden';
    // Live refresh credits
    fetch(REST+'credits',{credentials:'same-origin',headers:{'X-WP-Nonce':NONCE}})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.credits!==undefined){
    var cr=d.credits;
    [q('ds-popup-cr'),q('ds-stat-credits')].forEach(function(el){if(el)el.textContent=cr.toLocaleString();});
    var nb=q('ds-nav-cr');
    if(nb)nb.textContent=cr.toLocaleString()+'cr';
    }
        if(d.status==='cancelled'&&d.end_date) dsShowMsg('Plan active until '+dsDate(d.end_date)+'. Reverts to Free after.');
    }).catch(function(){});
    // Live refresh download counts
    fetch(REST+'dl-status',{credentials:'same-origin',headers:{'X-WP-Nonce':NONCE}})
    .then(function(r){return r.json();})
    .then(function(d){
        function dsPaintDl(prefix, used, limit, color){
            var usedEl=q('ds-dl-'+prefix+'-used');
            var leftEl=q('ds-dl-'+prefix+'-left');
            var barEl=q('ds-dl-'+prefix+'-bar');
            if(!usedEl)return;
            var left=(limit===-1)?'∞':Math.max(0,limit-used);
            var pct=(limit===-1||limit===0)?0:Math.round((used/limit)*100);
            var c=pct>=90?'#ef4444':(pct>=70?'#f59e0b':color);
            usedEl.textContent=used+' of '+(limit===-1?'∞':limit)+' used';
            if(leftEl){leftEl.textContent=(limit===-1?'∞':left)+' left';leftEl.style.color=c;}
            if(barEl){barEl.style.width=pct+'%';barEl.style.background=c;}
        }
        var usedFree=(d.used_free!==undefined)?d.used_free:0;
        var limitFree=(d.limit_free!==undefined)?d.limit_free:0;
        var usedPro=(d.used_pro!==undefined)?d.used_pro:0;
        var limitPro=(d.limit_pro!==undefined)?d.limit_pro:0;
        dsPaintDl('free', usedFree, limitFree, '#10b981');
        dsPaintDl('pro',  usedPro,  limitPro,  '#7c3aed');
    }).catch(function(){});
	
    // Always reload history fresh — don't trust a stale cache
    dsLoadHistory();
};

window.dsAcctClose=function(){
    q('ds-acct-overlay').style.display='none';
    var m=q('ds-acct-modal');
    m.style.display='none';
    document.body.style.overflow='';
};

window.dsTab=function(name,btn){
    document.querySelectorAll('.ds-tab-pane').forEach(function(p){p.classList.remove('active');});
    document.querySelectorAll('.ds-tab-btn').forEach(function(b){b.classList.remove('active');});
    q('ds-tab-'+name).classList.add('active');
    btn.classList.add('active');
    if(name==='history') dsLoadHistory();
};

function dsLoadHistory(){
    _loaded=true;
    var apiNonce=(typeof wpApiSettings!=='undefined'&&wpApiSettings.nonce)?wpApiSettings.nonce:NONCE;
    fetch(REST+'credit-log',{credentials:'include',headers:{'X-WP-Nonce':apiNonce}})
    .then(function(r){return r.json();})
    .then(function(d){
        q('ds-history-loading').style.display='none';
        var log=d.log||[];
        // Update stats
        var totalSpent=log.reduce(function(a,b){return a+(b.credits||0);},0);
        var imgCount=log.filter(function(x){return x.type==='image'||!x.type;}).length;
        if(q('ds-stat-images'))q('ds-stat-images').textContent=imgCount;
        if(q('ds-stat-spent'))q('ds-stat-spent').textContent=totalSpent;

        if(log.length===0){
            q('ds-history-empty').style.display='block';
            return;
        }

        // Recent preview (first 3 images)
        var imgLogs=log.filter(function(x){return (x.type==='image'||!x.type)&&x.url;}).slice(0,3);
        var prev=q('ds-recent-preview');
        if(prev&&imgLogs.length>0){
            prev.innerHTML='';
            imgLogs.forEach(function(it){
                var d2=document.createElement('div');
                d2.style.cssText='aspect-ratio:1;border-radius:10px;overflow:hidden;background:#f3f4f6;';
                d2.innerHTML='<img src="'+it.url+'" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display=\'none\'" loading="lazy"/>';
                prev.appendChild(d2);
            });
        }

        // Build history list
        var html='';
        var icons={image:'🖼️',ppt:'📊',thumbnail:'🎨',topup:'💳',subscription:'⭐',cashback:'🎉',marketplace:'🎉'};
        var modelColors={
    'cloudflare':'#10b981',
    'cloudflare-flux-klein':'#10b981',
    'qwen-image':'#f97316',
    'hidream-fast':'#14b8a6',
    'flux-schnell':'#6366f1',
    'flux-dev':'#f59e0b',
    'flux-pro':'#8b5cf6',
    'seedream':'#ef4444',
    'nanobana':'#0ea5e9',
    'nanobanana':'#0ea5e9',
	'qwen-edit':'#9333ea',
};
		// Remove visual duplicates — same prompt + same date + same credits + same image
var seen = {};
log = log.filter(function(item) {
    var key = (item.prompt||'') + '|' + (item.date||'').slice(0,16) + '|' + (item.credits||0) + '|' + (item.type||'') + '|' + (item.url||'');
    if (seen[key]) return false;
    seen[key] = true;
    return true;
});

        log.forEach(function(item){
            var icon=icons[item.type]||'[IMG]';
            var model=item.model||'';
            var mcolor=modelColors[model]||'#6b7280';
            var prompt=item.prompt||'';
            var credits=item.credits||item.amount||0;
            var date=item.date?(item.date.split(' ')[0]+' '+(item.date.split(' ')[1]||'').slice(0,5)):'';
            var url=item.url||'';
            var isIncome=item.type==='topup'||item.type==='subscription'||item.type==='cashback'||item.type==='marketplace';

            html+='<div class="ds-cr-row">';
            // Thumbnail or icon
            if(url&&(item.type==='image'||!item.type)){
                html+='<img class="ds-cr-thumb" src="'+url+'" alt="" loading="lazy" onerror="this.outerHTML=\'<div class=ds-cr-thumb-placeholder>'+icon+'</div>\'">';
            } else {
                html+='<div class="ds-cr-thumb-placeholder">'+icon+'</div>';
            }
            html+='<div style="flex:1;min-width:0;">';
            html+='<div style="font-size:12px;font-weight:700;color:#0f0f23;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">';
            if(item.type==='cashback'||item.type==='marketplace') {
    var productName = item.product_name || item.description || item.prompt || 'Marketplace Cashback';
    var dateTime = item.date || '';
    html+='<div style="display:flex;gap:8px;align-items:center;">';
    html+='<span style="background:#dcfce7;color:#166534;padding:2px 6px;border-radius:4px;font-weight:600;font-size:10px;">Credit Back</span>';
    html+='<span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+productName+'</span>';
    html+='</div>';
    html+='<div style="font-size:10px;color:#9ca3af;margin-top:3px;">'+dateTime+'</div>';
} else if(prompt) html+=prompt;
else if(item.type==='ppt') html+='AI Presentation';
else if(item.type==='topup') html+='Credit Top-up';
else if(item.type==='subscription') html+='Plan Subscription';
else html+='AI Generation';
            html+='</div>';
            html+='<div style="display:flex;align-items:center;gap:6px;margin-top:3px;">';
if(model) html+='<span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:100px;background:'+mcolor+'18;color:'+mcolor+';">'+model+'</span>';
if(item.type!=='cashback'&&item.type!=='marketplace') html+='<span style="font-size:10px;color:#9ca3af;">'+date+'</span>';
html+='</div></div>';
            // Credits used
            html+='<div style="text-align:right;flex-shrink:0;">';
            html+='<div style="font-size:14px;font-weight:900;color:'+(isIncome?'#10b981':'#ef4444')+';">'+(isIncome?'+':'-')+credits+'cr</div>';
                        html+='</div>';
            html+='</div>';
        });

        var list=q('ds-history-list');
        list.innerHTML=html;
        list.style.display='block';
    })
    .catch(function(){
        q('ds-history-loading').textContent='Could not load history.';
    });
}

// Cancel subscription
window.dsCancelShow=function(){
    q('ds-cancel-confirm').style.display='block';
    if(q('ds-cancel-btn'))q('ds-cancel-btn').style.display='none';
};
window.dsCancelHide=function(){
    q('ds-cancel-confirm').style.display='none';
    if(q('ds-cancel-btn'))q('ds-cancel-btn').style.display='flex';
};
window.dsCancelConfirm=function(){
    var btn=q('ds-cancel-confirm-btn');
    btn.textContent='Cancelling...'; btn.disabled=true;
    fetch(REST+'cancel-subscription',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
        body:JSON.stringify({})
    })
    .then(function(r){return r.json();})
    .then(function(d){
        q('ds-cancel-confirm').style.display='none';
        if(d.success){
            dsShowMsg(d.message||'Subscription cancelled.');
            var badge=q('ds-popup-plan-badge');
            if(badge)badge.style.background='#fef3c7';
            if(q('ds-cancel-btn'))q('ds-cancel-btn').style.display='none';
            if(q('ds-popup-end-date')&&d.end_date)
                q('ds-popup-end-date').textContent='Ends '+dsDate(d.end_date);
        } else {
            dsShowMsg('Error: '+(d.message||'Please contact support.'));
            btn.textContent='Yes, Cancel'; btn.disabled=false;
        }
    })
    .catch(function(){
        q('ds-cancel-confirm').style.display='none';
        dsShowMsg('Network error. Please try again.');
        btn.textContent='Yes, Cancel'; btn.disabled=false;
    });
};

// Request refund
window.dsRefundShow=function(){
q('ds-refund-confirm').style.display='block';
if(q('ds-refund-btn'))q('ds-refund-btn').style.display='none';
if(q('ds-cancel-btn'))q('ds-cancel-btn').style.display='none';
};
window.dsRefundHide=function(){
q('ds-refund-confirm').style.display='none';
if(q('ds-refund-btn'))q('ds-refund-btn').style.display='flex';
if(q('ds-cancel-btn'))q('ds-cancel-btn').style.display='flex';
};
window.dsRefundConfirm=function(){
var btn=q('ds-refund-confirm-btn');
btn.textContent='Processing...'; btn.disabled=true;
fetch(REST+'request-refund',{
method:'POST',
headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
body:JSON.stringify({})
})
.then(function(r){return r.json().then(function(d){return {ok:r.ok, d:d};});})
.then(function(res){
q('ds-refund-confirm').style.display='none';
if(res.ok && res.d.success){
dsShowMsg(res.d.message||'Refunded.');
if(q('ds-refund-btn'))q('ds-refund-btn').remove();
if(q('ds-cancel-btn'))q('ds-cancel-btn').remove();
var badge=q('ds-popup-plan-badge');
if(badge){badge.textContent='Free';badge.style.background='#6b7280';}
} else {
dsShowMsg('Error: '+(res.d.message||'Please contact support.'));
btn.textContent='Yes, Refund Me'; btn.disabled=false;
if(q('ds-refund-btn'))q('ds-refund-btn').style.display='flex';
}
})
.catch(function(){
q('ds-refund-confirm').style.display='none';
dsShowMsg('Network error. Please try again.');
btn.textContent='Yes, Refund Me'; btn.disabled=false;
if(q('ds-refund-btn'))q('ds-refund-btn').style.display='flex';
});
};

			function dsShowMsg(msg){
    var el=q('ds-popup-status-msg');
    if(el){el.textContent=msg;el.style.display='block';}
}
function dsDate(s){
    if(!s)return '';
    var d=new Date(s+'T00:00:00');
    var m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return d.getDate()+' '+m[d.getMonth()]+' '+d.getFullYear();
}

document.addEventListener('keydown',function(e){if(e.key==='Escape')dsAcctClose();});
})();
</script>
<?php
        }, 20);

    } else {
        $items .= '
        <li class="menu-item ds-login-circle-item">
            <a href="' . home_url('/login') . '" title="Login" class="ds-login-circle">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="#fff" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            </a>
        </li>';
    }
    return $items;
}, 10, 2);
		   

/**
 * A real address for the account popup: https://designstager.com/my-account/
 *
 * The popup above is added to the header menu and normally opens only when
 * the avatar is clicked. The /my-account/ page (and ?ds_account=1 or #account
 * on any page) now opens it straight away, so links, emails and the
 * PowerPoint add-in's "My Account" tile can point at it.
 *   - Signed out: sent to /login/ and brought back here after signing in.
 *   - Closing the popup on /my-account/ returns to the previous page on this
 *     site, or to the home page.
 */
function ds_acct_url_is_account_page() {
	if ( isset( $_GET['ds_account'] ) && '1' === (string) $_GET['ds_account'] ) { // phpcs:ignore
		return true;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore
	return '/my-account/' === '/' . trim( strtolower( $path ), '/' ) . '/';
}

add_action( 'template_redirect', function () {
	if ( is_user_logged_in() || ! ds_acct_url_is_account_page() ) {
		return;
	}
	$back = home_url( '/my-account/' );
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( $back ), home_url( '/login/' ) ) );
	exit;
}, 5 );

add_action( 'wp_footer', function () {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$on_page = ds_acct_url_is_account_page();
	?>
	<script id="ds-acct-url">
	(function () {
		var onPage = <?php echo $on_page ? 'true' : 'false'; ?>;
		if (!onPage && location.hash !== '#account') { return; }
		var home = <?php echo wp_json_encode( home_url( '/' ) ); ?>;
		var tries = 0;
		function go() {
			if (typeof window.dsAcctOpen !== 'function' || !document.getElementById('ds-acct-modal')) {
				if (++tries < 40) { setTimeout(go, 100); }
				return;
			}
			window.dsAcctOpen();
			if (!onPage) { return; }
			var close = window.dsAcctClose;
			window.dsAcctClose = function () {
				if (close) { close.apply(this, arguments); }
				var ref = document.referrer || '';
				/* Never back to the PowerPoint add-in's own panel page (it only works inside PowerPoint's side panel). */
				var same = ref.indexOf(location.origin + '/') === 0 && ref.indexOf(location.origin + '/ds-office/') !== 0 && ref.split('#')[0].split('?')[0] !== location.href.split('#')[0].split('?')[0];
				location.href = same ? ref : (document.documentElement.className.indexOf('ds-office-mode') !== -1 ? home + '?ds_office=1' : home);
			};
		}
		if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', go); } else { go(); }
	})();
	</script>
	<?php
}, 99 );
