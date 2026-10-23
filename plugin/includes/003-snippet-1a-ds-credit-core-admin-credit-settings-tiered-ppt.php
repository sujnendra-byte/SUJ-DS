<?php
/**
 * Code Snippets export — Snippet 1a ds credit core [Admin Credit Settings - Tiered PPT]
 *
 * Snippet ID : 689
 * Active     : yes
 * Priority   : 5
 * Scope      : global
 * Tags       : designstager, ds-order-003
 * Modified   : 2026-09-09 02:46:32
 * Load order : 003 of 144
 */
/**
* Snippet Name: DS Credit System - Part A (Core + REST Routes)
* Run: Everywhere
* Description: Credits, plans, roles, REST routes. Load BEFORE Part B.
*/
// =============================================
// RAZORPAY API KEYS
// =============================================
function ds_get_razorpay_keys() {
return array(
'key_id' => ds_get_key('razorpay_key_id'),
'key_secret' => ds_get_key('razorpay_key_secret'),
);
}

// =============================================
// RAZORPAY PLAN IDs
// Create these in Razorpay Dashboard -> Products -> Subscriptions -> Plans
// Then paste the plan IDs here
//
// NOTE: These now come from the "DS Pricing Settings" admin screen
// (Snippets -> DS Pricing) when that snippet is active. Editing the
// Plan ID / Price / Credits fields there updates this automatically.
// The array below is only the fallback if that snippet is ever removed.
// =============================================
function ds_get_razorpay_plans() {
$country = ds_get_user_country();
$is_india = ($country === 'IN');

if (function_exists('ds_get_pricing_plans')) {
$admin = ds_get_pricing_plans();
$out = array();
foreach (array('premium', 'pro', 'business') as $key) {
if (empty($admin[$key])) continue;
$p = $admin[$key];
$out[$key] = array(
'plan_id' => $is_india ? $p['plan_id_inr'] : $p['plan_id_usd'],
'plan_id_usd' => $p['plan_id_usd'],
'plan_id_inr' => $p['plan_id_inr'],
'label' => $p['label'],
'credits' => (int) $p['credits'],
'price' => (float) preg_replace('/[^0-9.]/', '', (string) $p['usd_price']),
'price_inr' => (float) preg_replace('/[^0-9.]/', '', (string) $p['inr_price']),
);
}
if (!empty($out)) return $out;
}

// Fallback — used only if the DS Pricing Settings snippet is inactive/missing
return array(
'premium' => array(
'plan_id' => $is_india ? 'plan_T52ZVGkju6U7vv' : 'plan_T52Y8CXO5RLpd6',
'plan_id_usd' => 'plan_T52Y8CXO5RLpd6',
'plan_id_inr' => 'plan_T52ZVGkju6U7vv',
'label' => 'Premium',
'credits' => 150,
'price' => 2.99,
'price_inr'=> 249,
),
'pro' => array(
'plan_id' => $is_india ? 'plan_T52bNWgqvAMaab' : 'plan_T52aZskjDAa15X',
'plan_id_usd' => 'plan_T52aZskjDAa15X',
'plan_id_inr' => 'plan_T52bNWgqvAMaab',
'label' => 'Pro',
'credits' => 300,
'price' => 4.99,
'price_inr'=> 450,
),
'business' => array(
'plan_id' => $is_india ? 'plan_T52d8DrwB4H5X0' : 'plan_T52c40mtfhCQt8',
'plan_id_usd' => 'plan_T52c40mtfhCQt8',
'plan_id_inr' => 'plan_T52d8DrwB4H5X0',
'label' => 'Business',
'credits' => 600,
'price' => 7.99,
'price_inr'=> 650,
),
);
}
function ds_get_user_country() {
/*
 * 17.3 — one country decision for BOTH the price shown and the plan charged.
 *
 * Before: the pricing page detected the country in the browser (visitor's
 * own IP, default US) but checkout decided on the server, using a country
 * cached in user meta FOREVER (first visit wins — e.g. India, then VPN) and,
 * when the lookup from the shared Hostinger IP was rate-limited, silently
 * defaulting to IN. So users abroad saw $ but Razorpay opened in rupees.
 *
 * Now: country is resolved from the CURRENT request IP, cached per IP for
 * 12h, with three lookup providers. The pricing page asks /ds/v1/geo, which
 * calls this same function, so the display and the charge always agree.
 */
static $memo = null;
if ($memo !== null) return $memo;

$ip = '';
foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR') as $h) {
if (empty($_SERVER[$h])) continue;
foreach (explode(',', (string) $_SERVER[$h]) as $cand) {
$cand = trim($cand);
if (filter_var($cand, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) { $ip = $cand; break 2; }
}
}

$valid = function ($c) { $c = strtoupper(trim((string) $c)); return (strlen($c) === 2 && ctype_alpha($c)) ? $c : ''; };

if ($ip) {
$tkey = 'ds_geo_' . md5($ip);
$hit  = $valid(get_transient($tkey));
if ($hit) { return $memo = $hit; }

$country = '';
$r = wp_remote_get('https://ipapi.co/' . rawurlencode($ip) . '/country/', array('timeout' => 4));
if (!is_wp_error($r) && 200 === (int) wp_remote_retrieve_response_code($r)) {
$country = $valid(wp_remote_retrieve_body($r));
}
if (!$country) {
$r = wp_remote_get('https://api.country.is/' . rawurlencode($ip), array('timeout' => 4));
if (!is_wp_error($r)) { $d = json_decode(wp_remote_retrieve_body($r), true); $country = $valid($d['country'] ?? ''); }
}
if (!$country) {
$r = wp_remote_get('https://ipwho.is/' . rawurlencode($ip) . '?fields=success,country_code', array('timeout' => 4));
if (!is_wp_error($r)) { $d = json_decode(wp_remote_retrieve_body($r), true); $country = $valid($d['country_code'] ?? ''); }
}

if ($country) {
set_transient($tkey, $country, 12 * HOUR_IN_SECONDS);
if (is_user_logged_in()) update_user_meta(get_current_user_id(), 'ds_user_country', $country); // last known, fallback only
return $memo = $country;
}
error_log('DS Country: lookup failed for IP ' . $ip);
}

// Lookup impossible right now: last country we saw for this user, else IN.
if (is_user_logged_in()) {
$last = $valid(get_user_meta(get_current_user_id(), 'ds_user_country', true));
if ($last) return $memo = $last;
}
return $memo = 'IN';
}

add_action('rest_api_init', function () {
register_rest_route('ds/v1', '/geo', array(
'methods'             => 'GET',
'permission_callback' => '__return_true',
'callback'            => function () {
$c = ds_get_user_country();
$res = rest_ensure_response(array('country' => $c, 'currency' => $c === 'IN' ? 'INR' : 'USD'));
$res->header('Cache-Control', 'no-store, private');
return $res;
},
));
});

// Credit top-up amounts
//
// NOTE: These now come from the "DS Pricing Settings" admin screen
// (Snippets -> DS Pricing -> Buy Extra Credits) when that snippet is
// active. The array below is only the fallback if that snippet is
// ever removed.
function ds_get_topup_plans() {
if (function_exists('ds_get_topups')) {
$tiers = ds_get_topups();
if (!empty($tiers)) {
$out = array();
foreach ($tiers as $t) {
$key = 'topup_' . (int) $t['cr'];
$out[$key] = array(
'credits' => (int) $t['cr'],
'price_inr' => (int) $t['rs'],
'price' => (float) $t['usd'],
);
}
return $out;
}
}
return array(
'topup_100' => array('credits'=>100, 'price_inr'=>130, 'price'=>1.79),
'topup_200' => array('credits'=>200, 'price_inr'=>230, 'price'=>2.89),
'topup_300' => array('credits'=>300, 'price_inr'=>320, 'price'=>3.99),
);
}

// =============================================
// PLAN DEFINITIONS
//
// NOTE: premium/pro/business now come from the "DS Pricing Settings"
// admin screen when that snippet is active. This controls credits
// granted on activation. The values below are only the fallback.
// =============================================
function ds_get_plans() {
if (function_exists('ds_get_pricing_plans')) {
$admin = ds_get_pricing_plans();
if (!empty($admin['premium']) && !empty($admin['pro']) && !empty($admin['business'])) {
return array(
'free' => array('label'=>'Free', 'credits'=>0, 'price'=>0),
'premium' => array(
'label' => $admin['premium']['label'],
'credits' => (int) $admin['premium']['credits'],
'price' => (float) preg_replace('/[^0-9.]/', '', (string) $admin['premium']['usd_price']),
),
'pro' => array(
'label' => $admin['pro']['label'],
'credits' => (int) $admin['pro']['credits'],
'price' => (float) preg_replace('/[^0-9.]/', '', (string) $admin['pro']['usd_price']),
),
'business' => array(
'label' => $admin['business']['label'],
'credits' => (int) $admin['business']['credits'],
'price' => (float) preg_replace('/[^0-9.]/', '', (string) $admin['business']['usd_price']),
),
);
}
}
return array(
'free' => array('label'=>'Free', 'credits'=>0, 'price'=>0),
'premium' => array('label'=>'Premium', 'credits'=>150, 'price'=>2.99),
'pro' => array('label'=>'Pro', 'credits'=>300, 'price'=>4.99),
'business' => array('label'=>'Business', 'credits'=>550, 'price'=>7.99),
);
}
function ds_get_plan_order() {
return array('free'=>0,'premium'=>1,'pro'=>2,'business'=>3);
}

// =============================================
// REGISTER CUSTOM WP ROLES
// =============================================
add_action('init', function() {
if (!get_role('premium_member')) add_role('premium_member', 'Premium Member', array('read'=>true));
if (!get_role('pro_member')) add_role('pro_member', 'Pro Member', array('read'=>true));
if (!get_role('business_member')) add_role('business_member', 'Business Member', array('read'=>true));
});

// =============================================
// SIGNUP - 10 credits = 5 free images at 2cr
// =============================================

// Allow login with email address (WordPress uses username by default)
add_filter('authenticate', function($user, $username, $password) {
if (is_a($user, 'WP_User')) return $user; // already authenticated
if (empty($username) || empty($password)) return $user;
// If the username looks like an email, find the real username
if (strpos($username, '@') !== false) {
$user_by_email = get_user_by('email', $username);
if ($user_by_email) {
$user = wp_authenticate_username_password(null, $user_by_email->user_login, $password);
}
}
return $user;
}, 20, 3);

add_action('user_register', function($uid) {
update_user_meta($uid, 'ds_ai_credits', 0);
update_user_meta($uid, 'ds_subscription_plan', 'free');
update_user_meta($uid, 'ds_subscription_status','active');
$u = get_user_by('id', $uid);
if ($u) $u->set_role('subscriber');
});

// =============================================
// CREDIT HELPERS
// =============================================
if (!function_exists('ds_get_credits')) {
function ds_get_credits($uid) {
$c = (int) get_user_meta($uid, 'ds_ai_credits', true);
if ($c === 0 && !metadata_exists('user', $uid, 'ds_ai_credits')) {
update_user_meta($uid, 'ds_ai_credits', 10);
return 10;
}
return $c;
}
}
if (!function_exists('ds_deduct_credits')) {
function ds_deduct_credits($uid, $amount) {
$cur = (int) get_user_meta($uid, 'ds_ai_credits', true);
if ($cur < $amount) return false;
update_user_meta($uid, 'ds_ai_credits', $cur - $amount);
return true;
}
}
if (!function_exists('ds_add_credits')) {
function ds_add_credits($uid, $amount) {
$cur = (int) get_user_meta($uid, 'ds_ai_credits', true);
update_user_meta($uid, 'ds_ai_credits', $cur + $amount);
return true;
}
}

/**
* Single source of truth for presentation pricing.
*
* Tiered: a flat base charge covers the first N slides; every slide
* beyond that costs a per-extra-slide rate. N, the base charge, and the
* extra-slide rate are all admin-configurable (DS Credit Settings,
* option 'ds_ppt_credit_settings'). Defaults match the original pricing:
* first 5 slides = 5 credits flat, then 1 credit per extra slide.
*
* add_filter('ds_credits_per_slide', fn() => 2); // still supported
*/
if ( ! function_exists('ds_ppt_credit_cost') ) {
function ds_ppt_credit_cost( $num_slides, $mode = 'ai' ) {
$opt = get_option('ds_ppt_credit_settings');
$base_slides = ( is_array($opt) && isset($opt['base_slides']) && $opt['base_slides'] !== '' ) ? (int)$opt['base_slides'] : 5;
$base_credits = ( is_array($opt) && isset($opt['base_credits']) && $opt['base_credits'] !== '' ) ? (int)$opt['base_credits'] : 5;
$extra_rate = ( is_array($opt) && isset($opt['extra_slide_rate']) && $opt['extra_slide_rate'] !== '' ) ? (int)$opt['extra_slide_rate'] : 1;
$base_slides = (int) apply_filters( 'ds_credits_base_slides', $base_slides, $mode );
$base_credits = (int) apply_filters( 'ds_credits_base_amount', $base_credits, $mode );
$extra_rate = (int) apply_filters( 'ds_credits_per_slide', $extra_rate, $mode );
$n = max( 1, (int) $num_slides );
if ( $n <= $base_slides ) {
return $base_credits;
}
return $base_credits + ( ( $n - $base_slides ) * $extra_rate );
}
}

function ds_get_user_plan($uid) {
$p = get_user_meta($uid, 'ds_subscription_plan', true);
if ($p && in_array($p, array('free','premium','pro','business'))) return $p;
// Fallback: check WP role
$user = get_user_by('id', $uid);
if ($user) {
$roles = (array)$user->roles;
if (in_array('business_member', $roles)) return 'business';
if (in_array('pro_member', $roles)) return 'pro';
if (in_array('premium_member', $roles)) return 'premium';
}
return 'free';
}
function ds_get_user_plan_status($uid) {
return get_user_meta($uid, 'ds_subscription_status', true) ?: 'active';
}
function ds_get_subscription_end($uid) {
return get_user_meta($uid, 'ds_subscription_end', true) ?: '';
}
function ds_get_pending_downgrade($uid) {
return get_user_meta($uid, 'ds_pending_downgrade', true) ?: '';
}

// =============================================
// ACTIVATE PLAN
// =============================================
// $amount / $currency are optional: the payment gateway passes what it actually
// charged so the daily report can show revenue next to the credits granted.
function ds_activate_plan($uid, $plan, $sub_id, $end_date, $amount = 0, $currency = 'INR') {
$plans = ds_get_plans();
$order = ds_get_plan_order();
$old_plan = ds_get_user_plan($uid);
$old_status = ds_get_user_plan_status($uid);
$old_idx = $order[$old_plan] ?? 0;
$new_idx = $order[$plan] ?? 0;

// Fresh sub, cancelled/expired re-subscribe, or upgrade = full credits
$is_fresh = in_array($old_status, array('cancelled','expired')) || $old_plan === 'free';
$credits_to_add = 0;
$is_renewal = false; // FIX: was left undefined on the downgrade path

if ($is_fresh || $new_idx > $old_idx) {
$credits_to_add = $plans[$plan]['credits'] ?? 0;
delete_user_meta($uid, 'ds_pending_downgrade');
} elseif ($new_idx === $old_idx) {
// Monthly renewal
$credits_to_add = $plans[$plan]['credits'] ?? 0;
$is_renewal = true;
}
// Downgrade: no immediate credits

update_user_meta($uid, 'ds_subscription_plan', $plan);
update_user_meta($uid, 'ds_subscription_status', 'active');
if ($sub_id) update_user_meta($uid, 'ds_subscription_id', $sub_id);
if ($end_date) update_user_meta($uid, 'ds_subscription_end', $end_date);

// Which billing period do these credits belong to?
// Prefer what the gateway just sent; otherwise reuse the stored end date,
// but only while it is still in the future — a stale one would expire credits instantly.
$period_end = $end_date ?: '';
if (!$period_end) {
$stored = get_user_meta($uid, 'ds_subscription_end', true);
if ($stored && strtotime($stored) > strtotime(current_time('Y-m-d'))) {
$period_end = $stored;
}
}

if ($credits_to_add > 0) {
if ($is_renewal && function_exists('ds_pool_renew_subscription')) {
ds_pool_renew_subscription($uid, $credits_to_add, $period_end);
} elseif (function_exists('ds_pool_set_subscription_credits')) {
$is_upgrade = (!$is_fresh && $new_idx > $old_idx);
ds_pool_set_subscription_credits($uid, $credits_to_add, $is_upgrade, $period_end);
} else {
ds_add_credits($uid, $credits_to_add);
}
}
// Sync WP role — never change role of administrators or editors
$wp_user = get_user_by('id', $uid);
if ($wp_user) {
$protected = array('administrator','editor','shop_manager');
$user_roles = (array)$wp_user->roles;
$is_protected = !empty(array_intersect($protected, $user_roles));
if (!$is_protected) {
$role_map = array('free'=>'subscriber','premium'=>'premium_member','pro'=>'pro_member','business'=>'business_member');
$wp_user->set_role($role_map[$plan] ?? 'subscriber');
}
}

// History
$log = get_user_meta($uid, 'ds_plan_history', true) ?: array();
array_unshift($log, array(
    'plan'         => $plan,
    'from'         => $old_plan,
    'credits'      => $credits_to_add,                            // credits we actually granted
    'plan_credits' => (int) (isset($plans[$plan]['credits']) ? $plans[$plan]['credits'] : 0), // credits this plan is defined to give
    'amount'       => (float) $amount,                            // charged by the gateway; 0 when not supplied
    'currency'     => strtoupper((string) $currency),
    'date'         => current_time('mysql'),
    'sub_id'       => $sub_id,
));
update_user_meta($uid, 'ds_plan_history', array_slice($log, 0, 24));

error_log("DS Plan: uid=$uid from=$old_plan to=$plan credits_added=$credits_to_add sub_id=$sub_id");
}

// =============================================
// REST ROUTES
// =============================================
add_action('rest_api_init', function() {

// Get credits + plan
register_rest_route('ds/v1', '/credits', array(
'methods' => 'GET',
'callback' => function() {
$uid = get_current_user_id();
$plans = ds_get_plans();
$plan = ds_get_user_plan($uid);
return rest_ensure_response(array(
'credits' => ds_get_credits($uid),
'plan' => $plan,
'plan_label' => $plans[$plan]['label'] ?? 'Free',
'status' => ds_get_user_plan_status($uid),
'end_date' => ds_get_subscription_end($uid),
'pending_downgrade'=> ds_get_pending_downgrade($uid),
));
},
'permission_callback' => function(){ return is_user_logged_in(); },
));

// Create Razorpay subscription order
register_rest_route('ds/v1', '/create-subscription', array(
'methods' => 'POST',
'callback' => 'ds_rest_create_subscription',
'permission_callback' => function(){ return is_user_logged_in(); },
));

// Create Razorpay one-time order (for credit top-ups)
register_rest_route('ds/v1', '/create-order', array(
'methods' => 'POST',
'callback' => 'ds_rest_create_order',
'permission_callback' => function(){ return is_user_logged_in(); },
));

// Verify payment after Razorpay checkout
register_rest_route('ds/v1', '/verify-payment', array(
'methods' => 'POST',
'callback' => 'ds_rest_verify_payment',
'permission_callback' => function(){ return is_user_logged_in(); },
));

// Cancel subscription
register_rest_route('ds/v1', '/cancel-subscription', array(
'methods' => 'POST',
'callback' => 'ds_rest_cancel_subscription',
'permission_callback' => function(){ return is_user_logged_in(); },
));

// Admin set plan manually
register_rest_route('ds/v1', '/admin-set-plan', array(
'methods' => 'POST',
'callback' => 'ds_rest_admin_set_plan',
'permission_callback' => function(){ return current_user_can('manage_options'); },
));

// Razorpay webhook (server-to-server)
register_rest_route('designstager/v1', '/razorpay-webhook', array(
'methods' => array('POST','GET'),
'callback' => 'ds_handle_razorpay_webhook',
'permission_callback' => '__return_true',
));

// Alias for account dropdown compatibility
register_rest_route('ds/v1', '/user-credit-log', array(
'methods' => 'GET',
'callback' => function() {
// Same as credit-log — just call it directly
$req = new WP_REST_Request('GET', '/ds/v1/credit-log');
return rest_do_request($req);
},
'permission_callback' => function(){ return is_user_logged_in(); },
));
// Credit usage history
register_rest_route('ds/v1', '/credit-log', array(
'methods' => 'GET',
'callback' => function() {
$uid = get_current_user_id();
$one_month_ago = strtotime('-90 days'); // extend to 90 days
$log = [];
$model_costs = [
'cloudflare'=>2,'Cloudflare FLUX.2 Klein 9B'=>3,'flux-schnell'=>4,
'flux-dev'=>7,'seedream'=>8,'flux-pro'=>10,'nanobana'=>13,
];

// 1. IMAGE GENERATIONS
$gens = get_user_meta($uid, 'ds_ai_generations', true) ?: [];
foreach ($gens as $gen) {
if (!is_array($gen)) continue;
$status = $gen['status'] ?? 'succeeded';
if ($status === 'pending') continue;
if ($status === 'failed') continue;
// Only skip if no URL AND no model (completely empty entry)
if (empty($gen['url']) && empty($gen['model'])) continue;
$date = $gen['date'] ?? '';
if ($date && strtotime($date) < $one_month_ago) continue;
$model = $gen['model'] ?? '';
$count = (int)($gen['count'] ?? 1);
$stored_cr = (int)($gen['credits'] ?? 0);
// Calculate credits — use stored total, or model cost × count
$cr_each = $model_costs[$model] ?? 2;
$credits = $stored_cr > 0 ? $stored_cr : ($cr_each * $count);
$log[] = [
'type' => 'image',
'label' => 'debit',
'prompt' => isset($gen['prompt']) ? wp_trim_words($gen['prompt'], 8, '...') : 'AI Image',
'model' => $model,
'credits' => $credits,
'count' => $count,
'url' => $gen['url'] ?? '',
'date' => $date,
'timestamp' => $date ? strtotime($date) : 0,
'batch_id' => $gen['batch_id'] ?? '',
];
}
// Group by batch_id — show only one entry per batch with TOTAL credits
$batch_totals = array(); // batch_id => total credits
$batch_counts = array(); // batch_id => image count
foreach ($log as $item) {
if ($item['type'] !== 'image') continue;
$bid = $item['batch_id'] ?? '';
if (empty($bid)) continue;
$batch_totals[$bid] = ($batch_totals[$bid] ?? 0) + (int)($item['credits'] ?? 0);
$batch_counts[$bid] = ($batch_counts[$bid] ?? 0) + 1;
}

$seen_batches = array();
$log_deduped = array();
foreach ($log as $item) {
if ($item['type'] !== 'image') {
$log_deduped[] = $item;
continue;
}
$bid = $item['batch_id'] ?? '';
if (empty($bid)) {
$log_deduped[] = $item;
continue;
}
if (!isset($seen_batches[$bid])) {
$seen_batches[$bid] = true;
// Use pre-calculated total for this batch
$item['credits'] = $batch_totals[$bid] ?? $item['credits'];
$item['count'] = $batch_counts[$bid] ?? 1;
$log_deduped[] = $item;
// Skip rest of this batch
}
// else: skip duplicate batch entries
}
$log = $log_deduped;
// 2. MARKETPLACE CASHBACKS
$transactions = get_user_meta($uid, 'ds_credit_transactions', true) ?: [];
foreach ($transactions as $tx) {
if (!is_array($tx)) continue;
$ts = $tx['timestamp'] ?? 0;
if ($ts && $ts < $one_month_ago) continue;
$log[] = [
'type' => 'cashback',
'prompt' => $tx['description'] ?? '',
'description' => $tx['description'] ?? '',
'product_name'=> $tx['description'] ?? $tx['label'] ?? '',
'model' => '',
'credits' => $tx['amount'] ?? 1,
'url' => '',
'date' => $ts ? date('Y-m-d H:i:s', $ts) : (isset($tx['date']) ? $tx['date'] : ''),
'timestamp' => $ts,
];
}

// 3. TOP-UP PURCHASES
$topups = get_user_meta($uid, 'ds_credit_topups', true) ?: [];
foreach ($topups as $tp) {
if (!is_array($tp)) continue;
$ts = $tp['timestamp'] ?? 0;
if ($ts && $ts < $one_month_ago) continue;
$log[] = [
'type' => 'topup',
'prompt' => $tp['label'] ?? 'Credit Purchase',
'model' => '',
'credits' => $tp['amount'] ?? 0,
'url' => '',
'date' => isset($tp['date']) ? $tp['date'] . ' 00:00:00' : '',
'timestamp' => $ts,
];
}

// 4. PLAN SUBSCRIPTIONS
$plan_history = get_user_meta($uid, 'ds_plan_history', true) ?: [];
foreach ($plan_history as $ph) {
if (!is_array($ph)) continue;
$ts = isset($ph['date']) ? strtotime($ph['date']) : 0;
if ($ts && $ts < $one_month_ago) continue;
if (empty($ph['credits'])) continue;
$log[] = [
'type' => 'subscription',
'prompt' => ucfirst($ph['plan'] ?? '') . ' Plan',
'model' => '',
'credits' => $ph['credits'] ?? 0,
'url' => '',
'date' => $ph['date'] ?? '',
'timestamp' => $ts,
];
}

// 5. AI PRESENTATION GENERATIONS
$ppt_gens = get_user_meta($uid, 'ds_ppt_generations', true) ?: [];
foreach ($ppt_gens as $pg) {
if (!is_array($pg)) continue;
$date = $pg['date'] ?? '';
$ts = $date ? strtotime($date) : 0;
if ($ts && $ts < $one_month_ago) continue;
$slides = (int)($pg['num_slides'] ?? $pg['slides'] ?? 5);
$credits = (int)($pg['credits'] ?? ($slides * 4));
$log[] = [
'type' => 'ppt',
'label' => 'debit',
'prompt' => isset($pg['prompt']) ? wp_trim_words($pg['prompt'], 8, '...') : 'AI Presentation',
'model' => 'groq',
'credits' => $credits,
'slides' => $slides,
'url' => '',
'date' => $date,
'timestamp' => $ts,
];
}

// 6. AI VIDEO GENERATIONS
$video_gens = get_user_meta($uid, 'ds_ai_video_generations', true) ?: [];
foreach ($video_gens as $gen) {
if (!is_array($gen)) continue;
$status = $gen['status'] ?? 'succeeded';
if ($status === 'pending') continue;
if ($status === 'failed') continue;
if (empty($gen['url']) && empty($gen['model'])) continue;
$date = $gen['date'] ?? '';
if ($date && strtotime($date) < $one_month_ago) continue;
$log[] = [
'type' => 'video',
'label' => 'debit',
'prompt' => isset($gen['prompt']) ? wp_trim_words($gen['prompt'], 8, '...') : 'AI Video',
'model' => $gen['model'] ?? '',
'credits' => (int)($gen['credits'] ?? 0),
'count' => 1,
'url' => $gen['url'] ?? '',
'date' => $date,
'timestamp' => $date ? strtotime($date) : 0,
];
}

// 7. AI AUDIO GENERATIONS
$audio_gens = get_user_meta($uid, 'ds_ai_audio_generations', true) ?: [];
foreach ($audio_gens as $gen) {
if (!is_array($gen)) continue;
$status = $gen['status'] ?? 'succeeded';
if ($status === 'pending') continue;
if ($status === 'failed') continue;
if (empty($gen['url']) && empty($gen['kind'])) continue;
$date = $gen['date'] ?? '';
if ($date && strtotime($date) < $one_month_ago) continue;
$kind = $gen['kind'] ?? 'music';
$default_label = ($kind === 'speech') ? 'AI Speech' : 'AI Music';
$log[] = [
'type' => 'audio',
'label' => 'debit',
'prompt' => !empty($gen['label']) ? wp_trim_words($gen['label'], 8, '...') : $default_label,
'model' => ($kind === 'speech') ? 'speech-02-hd' : 'ace-step-1.5',
'credits' => (int)($gen['credits'] ?? 0),
'count' => 1,
'url' => $gen['url'] ?? '',
'date' => $date,
'timestamp' => $date ? strtotime($date) : 0,
];
}

// Sort newest first
usort($log, function($a, $b) {
return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
});

return rest_ensure_response([
'log' => array_slice($log, 0, 100),
'total_credits' => ds_get_credits($uid),
]);
},
'permission_callback' => function(){ return is_user_logged_in(); },
));

// Debug endpoint (admin only)
register_rest_route('ds/v1', '/webhook-debug', array(
'methods' => 'GET',
'callback' => function() {
return rest_ensure_response(array(
'last_event' => get_option('ds_rzp_last_event', 'none'),
'last_email' => get_option('ds_rzp_last_email', ''),
'last_plan' => get_option('ds_rzp_last_plan', ''),
'last_result' => get_option('ds_rzp_last_result', ''),
'last_time' => get_option('ds_rzp_last_time', ''),
'last_raw' => get_option('ds_rzp_last_raw', ''),
));
},
'permission_callback' => function(){ return current_user_can('manage_options'); },
));
});

// Expose pricing to the front-end JS so the cost pill and the server charge
// can never disagree. Reads the same filters as ds_ppt_credit_cost().
add_action('wp_head', function(){
echo "<script>window.DS_CREDITS_PER_SLIDE=" . (int) apply_filters('ds_credits_per_slide', 1, 'ai')
. ";window.DS_MIN_CREDITS=" . (int) apply_filters('ds_credits_min_per_generation', 5, 'ai') . ";</script>
";
}, 1);
