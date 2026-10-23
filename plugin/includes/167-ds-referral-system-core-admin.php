<?php
/**
 * Code Snippets export — DS Referral System — Core + Admin
 *
 * Snippet ID : 905
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-referrals
 * Modified   : 2026-09-30 15:40:25
 * Load order : 167 of 162
 */
/**
 * DS Referral System — Core + Admin
 * Run: Everywhere
 *
 * - Every user gets one unique referral code (user meta dsrf_code) and a link: https://site/?ref=CODE
 * - A visitor who opens the link gets a cookie; when they sign up, they are recorded as that user's referral.
 * - When the referred friend buys a subscription, the referral becomes "Subscribed".
 * - After the friend completes the waiting period (default 30 days) without a refund, the referrer is
 *   rewarded automatically (hourly check): fixed credits per plan, a % of the plan's credits, or a
 *   % of the amount paid as a cash reward that the admin pays out manually.
 * - WP Admin > Referrals: settings, every referral with its status, manual grant / reject / mark paid.
 * - REST for the account popup: GET /ds/v1/referrals, POST /ds/v1/referrals/invite.
 *
 * Does NOT modify any other snippet. It watches the ds_plan_history user meta that the credit core
 * already writes on every subscription payment, and pays credits through ds_add_credits().
 * Undo: deactivate this snippet (the referrals table is kept).
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('dsrf_settings') ) {

define('DSRF_DB_VERSION', '1.1');

/* =========================================================
 * SETTINGS
 * ======================================================= */
function dsrf_default_settings() {
	return array(
		'enabled'        => 1,
		'reward_mode'    => 'fixed',      // fixed | percent | cash
		'fixed'          => array('premium' => 50, 'pro' => 100, 'business' => 150),
		'percent'        => 20,           // used by percent + cash
		'wait_days'      => 30,
		'condition'      => 'completed',  // completed | renewed
		'cookie_days'    => 30,
		'friend_bonus'   => 0,
		'hide_plans_tab' => 1,
		'email_referrer' => 1,
		'invites_per_day'=> 20,
		'block_same_ip'  => 0,
		'block_rejoin'   => 0,            // 1 = don't reward people who had an account before / were already referred
		'max_rewards'    => 0,            // per referrer, 0 = unlimited
	);
}

function dsrf_settings() {
	$o = get_option('dsrf_settings', array());
	$s = wp_parse_args(is_array($o) ? $o : array(), dsrf_default_settings());
	$s['fixed'] = wp_parse_args(is_array($s['fixed']) ? $s['fixed'] : array(), dsrf_default_settings()['fixed']);
	return $s;
}

function dsrf_paid_plans() { return array('premium', 'pro', 'business'); }

function dsrf_table() { global $wpdb; return $wpdb->prefix . 'ds_referrals'; }

function dsrf_now() { return current_time('mysql'); }

/* =========================================================
 * TABLE
 * ======================================================= */
function dsrf_install() {
	if ( get_option('dsrf_db_version') === DSRF_DB_VERSION ) return;
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t = dsrf_table();
	$c = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $t (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  referrer_id bigint(20) unsigned NOT NULL DEFAULT 0,
  referred_id bigint(20) unsigned NOT NULL DEFAULT 0,
  email varchar(190) NOT NULL DEFAULT '',
  status varchar(20) NOT NULL DEFAULT 'invited',
  plan varchar(20) NOT NULL DEFAULT '',
  invited_at datetime DEFAULT NULL,
  signup_at datetime DEFAULT NULL,
  subscribed_at datetime DEFAULT NULL,
  eligible_at datetime DEFAULT NULL,
  rewarded_at datetime DEFAULT NULL,
  reward_type varchar(10) NOT NULL DEFAULT '',
  reward_credits int(11) NOT NULL DEFAULT 0,
  reward_amount decimal(10,2) NOT NULL DEFAULT 0,
  reward_currency varchar(3) NOT NULL DEFAULT '',
  payout_status varchar(10) NOT NULL DEFAULT '',
  note varchar(255) NOT NULL DEFAULT '',
  signup_ip varchar(45) NOT NULL DEFAULT '',
  flags varchar(60) NOT NULL DEFAULT '',
  email_norm varchar(190) NOT NULL DEFAULT '',
  created_at datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY referrer_id (referrer_id),
  KEY referred_id (referred_id),
  KEY status (status),
  KEY email (email),
  KEY email_norm (email_norm)
) $c;";
	dbDelta($sql);
	dsrf_backfill_norm();
	update_option('dsrf_db_version', DSRF_DB_VERSION);
}
add_action('init', 'dsrf_install', 5);

/* ---------- one person = one referral (stops delete-and-re-join) ---------- */

/** Same person, same key: lower-case, no +tag; Gmail also ignores dots. */
function dsrf_norm_email($e) {
	$e = strtolower(trim((string) $e));
	if (strpos($e, '@') === false) return $e;
	list($local, $domain) = explode('@', $e, 2);
	$local = preg_replace('/\+.*$/', '', $local);
	if ($domain === 'gmail.com' || $domain === 'googlemail.com') { $local = str_replace('.', '', $local); $domain = 'gmail.com'; }
	return $local . '@' . $domain;
}

/** True when this e-mail had an account before (deleted) or was already referred once. */
function dsrf_is_rejoin($norm, $uid) {
	global $wpdb;
	$reg = get_option('dsrf_deleted_emails', array());
	if (is_array($reg) && isset($reg[sha1($norm)])) return true;
	if ($wpdb->get_var($wpdb->prepare('SELECT id FROM ' . dsrf_table() . ' WHERE email_norm = %s AND referred_id > 0 AND referred_id <> %d LIMIT 1', $norm, $uid))) return true;
	$u = get_userdata($uid);
	if ($u && strtolower($u->user_email) !== $norm) {
		$other = email_exists($norm);
		if ($other && (int) $other !== (int) $uid) return true;
	}
	return false;
}

/** Fill email_norm on old rows and mark later re-joins as not eligible. */
function dsrf_backfill_norm() {
	global $wpdb;
	$t = dsrf_table();
	$rows = $wpdb->get_results("SELECT id, email FROM $t WHERE email_norm = '' AND email <> ''");
	foreach ((array) $rows as $r) $wpdb->update($t, array('email_norm' => dsrf_norm_email($r->email)), array('id' => $r->id));
	if (empty(dsrf_settings()['block_rejoin'])) return;
	$rows = $wpdb->get_results("SELECT id, email_norm, referred_id FROM $t WHERE status IN ('signed_up','subscribed') AND referred_id > 0 AND email_norm <> ''");
	foreach ((array) $rows as $r) {
		$older = $wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE email_norm = %s AND referred_id > 0 AND referred_id <> %d AND id < %d LIMIT 1", $r->email_norm, $r->referred_id, $r->id));
		if ($older) $wpdb->update($t, array('status' => 'rejected', 'note' => 'Had an account before — re-joined, not eligible', 'flags' => 'rejoin'), array('id' => $r->id));
	}
}

/* remember deleted accounts' e-mails (only a one-way hash is kept) */
add_action('delete_user', function ($uid) {
	$u = get_userdata($uid);
	if (!$u || !$u->user_email) return;
	$reg = get_option('dsrf_deleted_emails', array());
	if (!is_array($reg)) $reg = array();
	$reg[sha1(dsrf_norm_email($u->user_email))] = current_time('Y-m-d');
	update_option('dsrf_deleted_emails', $reg, false);
});

/* =========================================================
 * HELPERS
 * ======================================================= */
function dsrf_ip() {
	foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR') as $h) {
		if (empty($_SERVER[$h])) continue;
		foreach (explode(',', (string) $_SERVER[$h]) as $cand) {
			$cand = trim($cand);
			if (filter_var($cand, FILTER_VALIDATE_IP)) return $cand;
		}
	}
	return '';
}

function dsrf_clean_code($code) {
	$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $code));
	return substr($code, 0, 16);
}

/** The user's unique referral code (created on first use). */
function dsrf_get_code($uid) {
	$uid = (int) $uid;
	if (!$uid) return '';
	$code = get_user_meta($uid, 'dsrf_code', true);
	if ($code) return $code;
	$u = get_userdata($uid);
	$base = $u ? strtoupper(preg_replace('/[^A-Za-z]/', '', $u->user_login)) : '';
	if (strlen($base) < 3 && $u) $base = strtoupper(preg_replace('/[^A-Za-z]/', '', $u->display_name));
	$base = substr($base !== '' ? $base : 'DS', 0, 4);
	$chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
	for ($i = 0; $i < 25; $i++) {
		$rand = '';
		for ($j = 0; $j < 4; $j++) $rand .= $chars[wp_rand(0, strlen($chars) - 1)];
		$try = $base . $rand;
		if (!dsrf_user_by_code($try)) { $code = $try; break; }
	}
	if (!$code) $code = 'DS' . $uid . strtoupper(wp_generate_password(4, false));
	update_user_meta($uid, 'dsrf_code', $code);
	return $code;
}

function dsrf_user_by_code($code) {
	$code = dsrf_clean_code($code);
	if ($code === '') return 0;
	$users = get_users(array('meta_key' => 'dsrf_code', 'meta_value' => $code, 'number' => 1, 'fields' => 'ID'));
	return $users ? (int) $users[0] : 0;
}

function dsrf_link($uid) {
	return add_query_arg('ref', dsrf_get_code($uid), home_url('/'));
}

function dsrf_plan_label($plan) {
	if (function_exists('ds_get_plans')) {
		$p = ds_get_plans();
		if (!empty($p[$plan]['label'])) return $p[$plan]['label'];
	}
	return ucfirst((string) $plan);
}

/** Credits + price of a plan, in the friend's currency. */
function dsrf_plan_info($plan, $uid = 0) {
	$country = $uid ? strtoupper((string) get_user_meta($uid, 'ds_user_country', true)) : '';
	$inr = ($country === 'IN');
	$out = array('credits' => 0, 'price' => 0.0, 'currency' => $inr ? 'INR' : 'USD');
	if (function_exists('ds_get_pricing_plans')) {
		$a = ds_get_pricing_plans();
		if (!empty($a[$plan])) {
			$p = $a[$plan];
			$out['credits'] = (int) ($p['credits'] ?? 0);
			$usd = (float) preg_replace('/[^0-9.]/', '', (string) ($p['usd_price'] ?? '0'));
			$rs  = (float) preg_replace('/[^0-9.]/', '', (string) ($p['inr_price'] ?? '0'));
			if ($inr && $rs > 0) { $out['price'] = $rs; } else { $out['price'] = $usd; $out['currency'] = 'USD'; }
			return $out;
		}
	}
	if (function_exists('ds_get_plans')) {
		$p = ds_get_plans();
		if (!empty($p[$plan])) {
			$out['credits'] = (int) ($p[$plan]['credits'] ?? 0);
			$out['price'] = (float) ($p[$plan]['price'] ?? 0);
			$out['currency'] = 'USD';
		}
	}
	return $out;
}

/** What the referrer earns for a friend on this plan. */
function dsrf_calc_reward($plan, $friend_uid) {
	$s = dsrf_settings();
	$mode = $s['reward_mode'];
	$r = array('type' => 'credits', 'credits' => 0, 'amount' => 0, 'currency' => '');
	if ($mode === 'fixed') {
		$r['credits'] = max(0, (int) ($s['fixed'][$plan] ?? 0));
	} elseif ($mode === 'percent') {
		$info = dsrf_plan_info($plan, $friend_uid);
		$r['credits'] = $info['credits'] > 0 ? max(1, (int) round($info['credits'] * ((float) $s['percent']) / 100)) : 0;
	} else {
		$info = dsrf_plan_info($plan, $friend_uid);
		$r['type'] = 'cash';
		$r['amount'] = round($info['price'] * ((float) $s['percent']) / 100, 2);
		$r['currency'] = $info['currency'];
	}
	return $r;
}

function dsrf_money($amount, $currency) {
	$sym = ($currency === 'INR') ? '₹' : '$';
	return $sym . number_format((float) $amount, 2);
}

/** One-line description of the reward, for the popup. */
function dsrf_reward_text() {
	$s = dsrf_settings();
	if ($s['reward_mode'] === 'fixed') {
		$vals = array();
		foreach (dsrf_paid_plans() as $p) { $v = (int) $s['fixed'][$p]; if ($v > 0) $vals[$p] = $v; }
		if (!$vals) return 'Earn rewards for every friend who subscribes.';
		$u = array_unique(array_values($vals));
		if (count($u) === 1) return 'Earn ' . $u[0] . ' credits for every friend who subscribes.';
		return 'Earn ' . min($u) . '–' . max($u) . ' credits for every friend who subscribes.';
	}
	if ($s['reward_mode'] === 'percent') {
		return 'Earn ' . (float) $s['percent'] . '% of your friend\'s plan credits for every friend who subscribes.';
	}
	return 'Earn ' . (float) $s['percent'] . '% of your friend\'s subscription payment.';
}
function dsrf_reward_breakdown() {
	$s = dsrf_settings();
	$rows = array();
	foreach (dsrf_paid_plans() as $p) {
		if ($s['reward_mode'] === 'fixed') {
			$v = (int) $s['fixed'][$p];
			if ($v > 0) $rows[] = array('plan' => dsrf_plan_label($p), 'reward' => $v . ' credits');
		} elseif ($s['reward_mode'] === 'percent') {
			$info = dsrf_plan_info($p, get_current_user_id());
			$v = $info['credits'] > 0 ? max(1, (int) round($info['credits'] * ((float) $s['percent']) / 100)) : 0;
			if ($v > 0) $rows[] = array('plan' => dsrf_plan_label($p), 'reward' => $v . ' credits');
		} else {
			$info = dsrf_plan_info($p, get_current_user_id());
			$v = round($info['price'] * ((float) $s['percent']) / 100, 2);
			if ($v > 0) $rows[] = array('plan' => dsrf_plan_label($p), 'reward' => dsrf_money($v, $info['currency']));
		}
	}
	return $rows;
}

function dsrf_mail($to, $subject, $body_html) {
	$html = '<div style="font-family:-apple-system,Segoe UI,Arial,sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1f2937;">'
		. '<div style="font-size:20px;font-weight:800;color:#6366f1;margin-bottom:16px;">' . esc_html(get_bloginfo('name')) . '</div>'
		. $body_html . '</div>';
	return wp_mail($to, $subject, $html, array('Content-Type: text/html; charset=UTF-8'));
}

function dsrf_get_row($id) {
	global $wpdb;
	return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . dsrf_table() . ' WHERE id = %d', (int) $id));
}

/* =========================================================
 * 1. CAPTURE ?ref=CODE  (cookie)
 * ======================================================= */
add_action('init', function () {
	if (empty($_GET['ref'])) return;
	$s = dsrf_settings();
	if (!$s['enabled']) return;
	$code = dsrf_clean_code(wp_unslash($_GET['ref']));
	$ref_uid = dsrf_user_by_code($code);
	if (!$ref_uid) return;
	if (is_user_logged_in()) return;                       // already has an account
	if (function_exists('do_action')) do_action('litespeed_control_set_nocache', 'ds referral link');
	if (!headers_sent()) nocache_headers();
	$current = isset($_COOKIE['ds_ref']) ? dsrf_clean_code($_COOKIE['ds_ref']) : '';
	if ($current === $code) return;
	if (!headers_sent()) {
		setcookie('ds_ref', $code, array(
			'expires'  => time() + max(1, (int) $s['cookie_days']) * DAY_IN_SECONDS,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		));
	}
	$_COOKIE['ds_ref'] = $code;
	dsrf_count_click($ref_uid);
}, 1);

function dsrf_count_click($ref_uid) {
	$ip = dsrf_ip();
	$k = 'dsrf_clk_' . md5($ref_uid . '|' . $ip . '|' . gmdate('Ymd'));
	if (get_transient($k)) return;
	set_transient($k, 1, DAY_IN_SECONDS);
	update_user_meta($ref_uid, 'dsrf_clicks', (int) get_user_meta($ref_uid, 'dsrf_clicks', true) + 1);
}

/* Same capture in the browser, for pages served from cache. */
add_action('wp_footer', function () {
	if (is_user_logged_in()) return;
	$s = dsrf_settings();
	if (!$s['enabled']) return;
	$days = max(1, (int) $s['cookie_days']);
	$click = esc_url_raw(rest_url('ds/v1/referrals/click'));
	?>
<script>
(function(){try{
  var m=location.search.match(/[?&]ref=([A-Za-z0-9]{3,16})/); if(!m) return;
  var code=m[1].toUpperCase();
  var cur=(document.cookie.match(/(?:^|;\s*)ds_ref=([^;]+)/)||[])[1]||'';
  if(cur.toUpperCase()===code) return;
  var d=new Date(); d.setTime(d.getTime()+<?php echo (int) $days; ?>*864e5);
  document.cookie='ds_ref='+code+';expires='+d.toUTCString()+';path=/;SameSite=Lax'+(location.protocol==='https:'?';Secure':'');
  if(window.fetch) fetch('<?php echo esc_js($click); ?>',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({code:code}),credentials:'omit'}).catch(function(){});
}catch(e){}})();
</script>
	<?php
}, 5);

/* =========================================================
 * 2. SIGN-UP  →  record the referral
 * ======================================================= */
add_action('user_register', function ($uid) {
	$s = dsrf_settings();
	if (!$s['enabled']) return;
	// an admin adding a user from wp-admin is not a referral sign-up
	if (is_admin() && !wp_doing_ajax() && current_user_can('create_users')) return;
	global $wpdb;
	$t = dsrf_table();
	$user = get_userdata($uid);
	if (!$user) return;
	$email = strtolower($user->user_email);
	$ip = dsrf_ip();
	update_user_meta($uid, 'dsrf_signup_ip', $ip);

	$ref_uid = 0;
	$code = isset($_COOKIE['ds_ref']) ? dsrf_clean_code(wp_unslash($_COOKIE['ds_ref'])) : '';
	if ($code) $ref_uid = dsrf_user_by_code($code);

	// no cookie? an e-mail invite for this address still counts
	$invite = null;
	if ($ref_uid) {
		$invite = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE referrer_id = %d AND email = %s AND referred_id = 0 ORDER BY id DESC LIMIT 1", $ref_uid, $email));
	} else {
		$invite = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE email = %s AND referred_id = 0 AND status = 'invited' ORDER BY id DESC LIMIT 1", $email));
		if ($invite) $ref_uid = (int) $invite->referrer_id;
	}
	if (!$ref_uid || $ref_uid === (int) $uid) return;
	if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE referred_id = %d LIMIT 1", $uid))) return;

	$flags = array();
	$ref_ips = array_filter(array(get_user_meta($ref_uid, 'dsrf_signup_ip', true), get_user_meta($ref_uid, 'dsrf_last_ip', true)));
	if ($ip && in_array($ip, $ref_ips, true)) $flags[] = 'same_ip';

	$status = 'signed_up';
	$note = '';
	if (in_array('same_ip', $flags, true) && $s['block_same_ip']) { $status = 'rejected'; $note = 'Same IP address as the referrer'; }
	$norm = dsrf_norm_email($email);
	if (dsrf_is_rejoin($norm, $uid)) {
		$flags[] = 'rejoin';   // always flagged for the admin
		if (!empty($s['block_rejoin'])) { $status = 'rejected'; $note = 'Had an account before — re-joined, not eligible'; }
	}

	$data = array(
		'referrer_id' => $ref_uid,
		'referred_id' => $uid,
		'email'       => $email,
		'status'      => $status,
		'signup_at'   => dsrf_now(),
		'signup_ip'   => $ip,
		'flags'       => implode(',', $flags),
		'note'        => $note,
		'email_norm'  => $norm,
	);
	if ($invite) {
		$wpdb->update($t, $data, array('id' => $invite->id));
	} else {
		$data['created_at'] = dsrf_now();
		$wpdb->insert($t, $data);
	}
	update_user_meta($uid, 'dsrf_referred_by', $ref_uid);
	// other pending invites for the same e-mail from other users are now void
	$wpdb->query($wpdb->prepare("UPDATE $t SET status = 'rejected', note = 'Joined through another invite' WHERE email = %s AND referred_id = 0 AND status = 'invited'", $email));

	if ($status === 'signed_up' && (int) $s['friend_bonus'] > 0 && function_exists('ds_add_credits')) {
		$b = (int) $s['friend_bonus'];
		ds_add_credits($uid, $b);
		dsrf_log_credit($uid, $b, 'Referral welcome bonus');
	}
	if ($status === 'signed_up' && $s['email_referrer']) {
		$ru = get_userdata($ref_uid);
		if ($ru) {
			dsrf_mail($ru->user_email, 'Your friend just joined ' . get_bloginfo('name') . '!',
				'<p>Hi ' . esc_html($ru->display_name) . ',</p><p><strong>' . esc_html($user->display_name) . '</strong> signed up with your referral link. '
				. 'When they subscribe and complete ' . (int) $s['wait_days'] . ' days, your reward is added automatically.</p>'
				. '<p>' . esc_html(dsrf_reward_text()) . '</p>');
		}
	}
	unset($_COOKIE['ds_ref']);
	if (!headers_sent()) setcookie('ds_ref', '', time() - 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
}, 30);

/* Credit-history entry (shows in the popup's Credit History as +N cr). */
function dsrf_log_credit($uid, $credits, $label) {
	$list = get_user_meta($uid, 'ds_credit_topups', true);
	if (!is_array($list)) $list = array();
	$list[] = array(
		'type'      => 'referral',
		'label'     => $label,
		'amount'    => (int) $credits,
		'date'      => current_time('Y-m-d'),
		'timestamp' => time(),
	);
	update_user_meta($uid, 'ds_credit_topups', $list);
}

/* =========================================================
 * 3. SUBSCRIPTION  →  mark "Subscribed"
 *    (reads the plan history the credit core writes on every payment)
 * ======================================================= */
function dsrf_paid_entries($uid) {
	$log = get_user_meta($uid, 'ds_plan_history', true);
	$out = array();
	if (!is_array($log)) return $out;
	foreach ($log as $e) {
		if (!is_array($e) || empty($e['date']) || empty($e['plan'])) continue;
		if (!in_array($e['plan'], dsrf_paid_plans(), true)) continue;
		if ((int) ($e['credits'] ?? 0) <= 0) continue;
		$out[] = $e;
	}
	usort($out, function ($a, $b) { return strtotime($a['date']) - strtotime($b['date']); }); // oldest first
	return $out;
}

function dsrf_check_subscribed($row) {
	if (!$row || $row->status !== 'signed_up' || !$row->referred_id) return false;
	$since = $row->signup_at ? strtotime($row->signup_at) - 60 : 0;
	foreach (dsrf_paid_entries($row->referred_id) as $e) {
		$ts = strtotime($e['date']);
		if ($ts < $since) continue;
		$s = dsrf_settings();
		global $wpdb;
		$wpdb->update(dsrf_table(), array(
			'status'        => 'subscribed',
			'plan'          => $e['plan'],
			'subscribed_at' => date('Y-m-d H:i:s', $ts),
			'eligible_at'   => date('Y-m-d H:i:s', $ts + max(0, (int) $s['wait_days']) * DAY_IN_SECONDS),
		), array('id' => $row->id, 'status' => 'signed_up'));
		return true;
	}
	return false;
}

function dsrf_on_meta($meta_id, $uid, $key) {
	if ($key !== 'ds_plan_history') return;
	global $wpdb;
	$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . dsrf_table() . " WHERE referred_id = %d AND status = 'signed_up' LIMIT 1", $uid));
	if ($row) dsrf_check_subscribed($row);
}
add_action('added_user_meta', 'dsrf_on_meta', 10, 3);
add_action('updated_user_meta', 'dsrf_on_meta', 10, 3);

/* =========================================================
 * 4. REWARD after the waiting period
 * ======================================================= */
function dsrf_was_refunded($uid, $since_ts) {
	if (get_user_meta($uid, 'ds_subscription_status', true) === 'refunded') return true;
	$r = get_user_meta($uid, 'ds_refund_history', true);
	if (is_array($r)) {
		foreach ($r as $x) {
			if (!empty($x['date']) && strtotime($x['date']) >= $since_ts - 60) return true;
		}
	}
	return false;
}

function dsrf_has_renewed($uid, $sub_ts) {
	foreach (dsrf_paid_entries($uid) as $e) {
		if (strtotime($e['date']) >= $sub_ts + 20 * DAY_IN_SECONDS) return true;
	}
	return false;
}

/**
 * Evaluate one "subscribed" referral. Returns 'rewarded', 'rejected' or 'waiting'.
 * $force = admin clicked "Grant now" (skips the waiting period and conditions).
 */
function dsrf_evaluate($row, $force = false) {
	global $wpdb;
	$t = dsrf_table();
	if (!$row || $row->status !== 'subscribed') return 'skip';
	$s = dsrf_settings();
	$friend = get_userdata($row->referred_id);
	$referrer = get_userdata($row->referrer_id);
	if (!$referrer) {
		$wpdb->update($t, array('status' => 'rejected', 'note' => 'Referrer account no longer exists'), array('id' => $row->id));
		return 'rejected';
	}
	if (!$force) {
		if (!$friend) {
			$wpdb->update($t, array('status' => 'rejected', 'note' => 'Friend deleted their account'), array('id' => $row->id));
			return 'rejected';
		}
		if (strtotime($row->eligible_at) > strtotime(dsrf_now())) return 'waiting';
		$sub_ts = strtotime($row->subscribed_at);
		if (dsrf_was_refunded($row->referred_id, $sub_ts)) {
			$wpdb->update($t, array('status' => 'rejected', 'note' => 'Friend took a refund'), array('id' => $row->id));
			return 'rejected';
		}
		if ($s['condition'] === 'renewed' && !dsrf_has_renewed($row->referred_id, $sub_ts)) {
			// allow 7 days for Razorpay retries before giving up
			$plan_now = function_exists('ds_get_user_plan') ? ds_get_user_plan($row->referred_id) : 'free';
			$grace_over = strtotime(dsrf_now()) > strtotime($row->eligible_at) + 7 * DAY_IN_SECONDS;
			if ($grace_over || $plan_now === 'free') {
				$wpdb->update($t, array('status' => 'rejected', 'note' => 'Friend did not renew'), array('id' => $row->id));
				return 'rejected';
			}
			return 'waiting';
		}
		if ((int) $s['max_rewards'] > 0) {
			$done = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE referrer_id = %d AND status = 'rewarded'", $row->referrer_id));
			if ($done >= (int) $s['max_rewards']) {
				$wpdb->update($t, array('status' => 'rejected', 'note' => 'Referrer reached the reward limit'), array('id' => $row->id));
				return 'rejected';
			}
		}
	}

	// reward is based on the plan the friend is on now (if still paid), else the plan they bought
	$plan = $row->plan;
	if ($friend && function_exists('ds_get_user_plan')) {
		$now = ds_get_user_plan($row->referred_id);
		if (in_array($now, dsrf_paid_plans(), true)) $plan = $now;
	}
	$rw = dsrf_calc_reward($plan, $row->referred_id);

	// claim the row atomically so a reward can never be paid twice
	$claimed = $wpdb->query($wpdb->prepare(
		"UPDATE $t SET status = 'rewarded', rewarded_at = %s, reward_type = %s, reward_credits = %d, reward_amount = %f, reward_currency = %s, payout_status = %s, plan = %s, note = %s WHERE id = %d AND status = 'subscribed'",
		dsrf_now(), $rw['type'], (int) $rw['credits'], (float) $rw['amount'], $rw['currency'],
		$rw['type'] === 'cash' ? 'unpaid' : '', $plan, $force ? 'Granted by admin' : '', $row->id
	));
	if (!$claimed) return 'skip';

	$friend_name = $friend ? $friend->display_name : 'your friend';
	if ($rw['type'] === 'credits' && $rw['credits'] > 0) {
		if (function_exists('ds_add_credits')) {
			ds_add_credits($row->referrer_id, (int) $rw['credits']);
		} else {
			update_user_meta($row->referrer_id, 'ds_ai_credits', (int) get_user_meta($row->referrer_id, 'ds_ai_credits', true) + (int) $rw['credits']);
		}
		dsrf_log_credit($row->referrer_id, $rw['credits'], 'Referral reward — ' . $friend_name . ' subscribed');
	}
	if ($s['email_referrer']) {
		$what = $rw['type'] === 'cash' ? dsrf_money($rw['amount'], $rw['currency']) . ((function_exists('dsrw_active') && dsrw_active()) ? ' (added to your wallet — withdraw it from your account once you reach the minimum)' : ' (paid out to you by our team)') : $rw['credits'] . ' credits';
		dsrf_mail($referrer->user_email, 'You earned a referral reward!',
			'<p>Hi ' . esc_html($referrer->display_name) . ',</p><p><strong>' . esc_html($friend_name) . '</strong> completed their subscription, so you earned <strong>' . esc_html($what) . '</strong>.</p><p>Thank you for sharing ' . esc_html(get_bloginfo('name')) . '!</p>');
	}
	return 'rewarded';
}

/** Move referrals forward. $referrer_id limits it to one user's referrals. */
function dsrf_process($referrer_id = 0) {
	global $wpdb;
	$t = dsrf_table();
	if ($referrer_id) {
		$rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE referrer_id = %d AND status IN ('signed_up','subscribed')", $referrer_id));
	} else {
		$rows = $wpdb->get_results("SELECT * FROM $t WHERE status IN ('signed_up','subscribed') ORDER BY id ASC LIMIT 500");
	}
	$stats = array('subscribed' => 0, 'rewarded' => 0, 'rejected' => 0);
	foreach ((array) $rows as $row) {
		if ($row->status === 'signed_up') {
			if (dsrf_check_subscribed($row)) { $stats['subscribed']++; $row = dsrf_get_row($row->id); }
		}
		if ($row && $row->status === 'subscribed') {
			$r = dsrf_evaluate($row);
			if (isset($stats[$r])) $stats[$r]++;
		}
	}
	return $stats;
}

add_action('init', function () {
	if (!wp_next_scheduled('dsrf_cron')) wp_schedule_event(time() + 300, 'hourly', 'dsrf_cron');
});
add_action('dsrf_cron', function () {
	if (!function_exists('dsrf_settings')) return;
	if (get_transient('dsrf_cron_lock')) return;
	set_transient('dsrf_cron_lock', 1, 10 * MINUTE_IN_SECONDS);
	dsrf_process();
	delete_transient('dsrf_cron_lock');
});

/* backup for sites where WP-Cron doesn't run: check at most once an hour, after the page is sent */
add_action('shutdown', function () {
	if (!function_exists('dsrf_process') || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) return;
	if (get_transient('dsrf_tick') || get_transient('dsrf_cron_lock')) return;
	set_transient('dsrf_tick', 1, HOUR_IN_SECONDS);
	set_transient('dsrf_cron_lock', 1, 10 * MINUTE_IN_SECONDS);
	dsrf_process();
	delete_transient('dsrf_cron_lock');
});

/* account deleted */
add_action('deleted_user', function ($uid) {
	global $wpdb;
	$t = dsrf_table();
	$wpdb->query($wpdb->prepare("UPDATE $t SET status = 'rejected', note = 'Referrer account deleted' WHERE referrer_id = %d AND status IN ('invited','signed_up','subscribed')", $uid));
	$wpdb->query($wpdb->prepare("UPDATE $t SET status = 'rejected', note = 'Friend deleted their account' WHERE referred_id = %d AND status IN ('signed_up','subscribed')", $uid));
});

/* =========================================================
 * 5. REST (account popup)
 * ======================================================= */
function dsrf_mask_email($e) {
	$e = (string) $e;
	if (strpos($e, '@') === false) return $e;
	list($a, $d) = explode('@', $e, 2);
	return substr($a, 0, min(2, strlen($a))) . str_repeat('•', max(2, min(5, strlen($a) - 2))) . '@' . $d;
}

function dsrf_fmt_date($mysql) {
	return $mysql ? date_i18n('j M Y', strtotime($mysql)) : '';
}

function dsrf_rest_get() {
	$uid = get_current_user_id();
	$s = dsrf_settings();
	global $wpdb;
	$t = dsrf_table();
	update_user_meta($uid, 'dsrf_last_ip', dsrf_ip());
	dsrf_process($uid);

	$rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE referrer_id = %d ORDER BY COALESCE(signup_at, invited_at, created_at) DESC, id DESC LIMIT 200", $uid));
	$items = array();
	$st = array('clicks' => (int) get_user_meta($uid, 'dsrf_clicks', true), 'invited' => 0, 'signed_up' => 0, 'subscribed' => 0, 'rewarded' => 0, 'credits_earned' => 0, 'cash_earned' => array());
	foreach ((array) $rows as $r) {
		$friend = $r->referred_id ? get_userdata($r->referred_id) : null;
		$signed = (int) $r->referred_id > 0;
		$subbed = in_array($r->status, array('subscribed', 'rewarded'), true) || ($r->status === 'rejected' && $r->subscribed_at);
		if ($r->status === 'invited') $st['invited']++;
		if ($signed) $st['signed_up']++;
		if ($subbed) $st['subscribed']++;
		if ($r->status === 'rewarded') {
			$st['rewarded']++;
			$st['credits_earned'] += (int) $r->reward_credits;
			if ($r->reward_type === 'cash') {
				$c = $r->reward_currency ?: 'USD';
				$st['cash_earned'][$c] = ($st['cash_earned'][$c] ?? 0) + (float) $r->reward_amount;
			}
		}
		$reward = '';
		if ($r->status === 'rewarded') {
			$reward = $r->reward_type === 'cash'
				? '+' . dsrf_money($r->reward_amount, $r->reward_currency) . ((function_exists('dsrw_active') && dsrw_active()) ? ' to wallet' : ($r->payout_status === 'paid' ? ' paid' : ' — payout pending'))
				: '+' . (int) $r->reward_credits . ' credits';
		}
		$items[] = array(
			'id'          => (int) $r->id,
			'name'        => $friend ? $friend->display_name : '',
			'email'       => $friend ? dsrf_mask_email($friend->user_email) : $r->email,
			'status'      => $r->status,
			'signed_up'   => $signed,
			'subscribed'  => (bool) $subbed,
			'plan'        => $r->plan ? dsrf_plan_label($r->plan) : '',
			'invited_on'  => dsrf_fmt_date($r->invited_at ?: $r->created_at),
			'signed_on'   => dsrf_fmt_date($r->signup_at),
			'subscribed_on' => dsrf_fmt_date($r->subscribed_at),
			'eligible_on' => dsrf_fmt_date($r->eligible_at),
			'rewarded_on' => dsrf_fmt_date($r->rewarded_at),
			'reward'      => $reward,
			'note'        => $r->status === 'rejected' ? $r->note : '',
		);
	}
	$cash = array();
	foreach ($st['cash_earned'] as $c => $v) $cash[] = dsrf_money($v, $c);
	$st['cash_earned'] = implode(' + ', $cash);

	$res = rest_ensure_response(array(
		'enabled'     => (bool) $s['enabled'],
		'code'        => dsrf_get_code($uid),
		'link'        => dsrf_link($uid),
		'reward_text' => dsrf_reward_text(),
		'breakdown'   => dsrf_reward_breakdown(),
		'mode'        => $s['reward_mode'],
		'wait_days'   => (int) $s['wait_days'],
		'condition'   => $s['condition'],
		'friend_bonus'=> (int) $s['friend_bonus'],
		'site'        => get_bloginfo('name'),
		'stats'       => $st,
		'items'       => $items,
	));
	$res->header('Cache-Control', 'no-store, private');
	return $res;
}

function dsrf_rest_invite(WP_REST_Request $req) {
	$uid = get_current_user_id();
	$s = dsrf_settings();
	if (!$s['enabled']) return new WP_Error('disabled', 'Referrals are paused right now.', array('status' => 400));
	$email = strtolower(sanitize_email((string) $req->get_param('email')));
	if (!$email || !is_email($email)) return new WP_Error('bad_email', 'Please enter a valid e-mail address.', array('status' => 400));
	$me = wp_get_current_user();
	if ($email === strtolower($me->user_email)) return new WP_Error('self', 'You can\'t invite yourself.', array('status' => 400));
	if (email_exists($email)) return new WP_Error('exists', 'This person already has an account.', array('status' => 400));
	global $wpdb;
	$t = dsrf_table();
	if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE referrer_id = %d AND email = %s LIMIT 1", $uid, $email))) {
		return new WP_Error('dup', 'You already invited this person.', array('status' => 400));
	}
	$limit = max(1, (int) $s['invites_per_day']);
	$today = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE referrer_id = %d AND invited_at >= %s", $uid, current_time('Y-m-d') . ' 00:00:00'));
	if ($today >= $limit) return new WP_Error('limit', 'You can send ' . $limit . ' invites a day. Share your link instead — it has no limit.', array('status' => 429));

	$wpdb->insert($t, array(
		'referrer_id' => $uid, 'email' => $email, 'status' => 'invited',
		'invited_at' => dsrf_now(), 'created_at' => dsrf_now(), 'email_norm' => dsrf_norm_email($email),
	));
	$link = dsrf_link($uid);
	$sent = dsrf_mail($email, $me->display_name . ' invited you to ' . get_bloginfo('name'),
		'<p>Hi,</p><p><strong>' . esc_html($me->display_name) . '</strong> thinks you\'ll love ' . esc_html(get_bloginfo('name')) . ' — create AI presentations, images, videos and more in one place.</p>'
		. '<p style="margin:24px 0;"><a href="' . esc_url($link) . '" style="background:#6366f1;color:#fff;padding:12px 22px;border-radius:100px;text-decoration:none;font-weight:700;">Accept invite</a></p>'
		. '<p style="font-size:12px;color:#6b7280;">Or open this link: ' . esc_html($link) . '</p>');
	return rest_ensure_response(array('success' => true, 'sent' => (bool) $sent, 'message' => 'Invite sent to ' . $email . '.'));
}

add_action('rest_api_init', function () {
	register_rest_route('ds/v1', '/referrals', array(
		'methods' => 'GET',
		'callback' => 'dsrf_rest_get',
		'permission_callback' => function () { return is_user_logged_in(); },
	));
	register_rest_route('ds/v1', '/referrals/invite', array(
		'methods' => 'POST',
		'callback' => 'dsrf_rest_invite',
		'permission_callback' => function () { return is_user_logged_in(); },
	));
	register_rest_route('ds/v1', '/referrals/click', array(
		'methods' => 'POST',
		'permission_callback' => '__return_true',
		'callback' => function (WP_REST_Request $req) {
			$s = dsrf_settings();
			if ($s['enabled']) {
				$ref = dsrf_user_by_code((string) $req->get_param('code'));
				if ($ref && !is_user_logged_in()) dsrf_count_click($ref);
			}
			return rest_ensure_response(array('ok' => true));
		},
	));
});

/* =========================================================
 * 6. USER PROFILE (wp-admin > Users > Edit)
 * ======================================================= */
function dsrf_profile_box($user) {
	if (!current_user_can('list_users')) return;
	$by = (int) get_user_meta($user->ID, 'dsrf_referred_by', true);
	$byu = $by ? get_userdata($by) : null;
	global $wpdb;
	$n = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . dsrf_table() . ' WHERE referrer_id = %d AND referred_id > 0', $user->ID));
	echo '<h3>Referrals</h3><table class="form-table"><tr><th>Referral code</th><td><code>' . esc_html(dsrf_get_code($user->ID)) . '</code> &nbsp; <a href="' . esc_url(admin_url('admin.php?page=ds-referrals&s=' . rawurlencode($user->user_email))) . '">' . $n . ' referral(s)</a></td></tr>';
	echo '<tr><th>Referred by</th><td>' . ($byu ? esc_html($byu->display_name . ' (' . $byu->user_email . ')') : '—') . '</td></tr></table>';
}
add_action('show_user_profile', 'dsrf_profile_box');
add_action('edit_user_profile', 'dsrf_profile_box');

/* =========================================================
 * 7. ADMIN PAGE  (WP Admin > Referrals)
 * ======================================================= */
add_action('admin_menu', function () {
	add_menu_page('Referrals', 'Referrals', 'manage_options', 'ds-referrals', 'dsrf_admin_page', 'dashicons-groups', 58);
});

function dsrf_admin_actions() {
	if (!current_user_can('manage_options')) return '';
	global $wpdb;
	$t = dsrf_table();
	$msg = '';

	if (isset($_POST['dsrf_save']) && check_admin_referer('dsrf_settings')) {
		$p = wp_unslash($_POST);
		$mode = in_array($p['reward_mode'] ?? '', array('fixed', 'percent', 'cash'), true) ? $p['reward_mode'] : 'fixed';
		$fixed = array();
		foreach (dsrf_paid_plans() as $pl) $fixed[$pl] = max(0, min(100000, (int) ($p['fixed'][$pl] ?? 0)));
		update_option('dsrf_settings', array(
			'enabled'         => !empty($p['enabled']) ? 1 : 0,
			'reward_mode'     => $mode,
			'fixed'           => $fixed,
			'percent'         => max(0, min(100, (float) ($p['percent'] ?? 0))),
			'wait_days'       => max(0, min(365, (int) ($p['wait_days'] ?? 30))),
			'condition'       => ($p['condition'] ?? '') === 'renewed' ? 'renewed' : 'completed',
			'cookie_days'     => max(1, min(365, (int) ($p['cookie_days'] ?? 30))),
			'friend_bonus'    => max(0, min(10000, (int) ($p['friend_bonus'] ?? 0))),
			'hide_plans_tab'  => !empty($p['hide_plans_tab']) ? 1 : 0,
			'email_referrer'  => !empty($p['email_referrer']) ? 1 : 0,
			'invites_per_day' => max(1, min(500, (int) ($p['invites_per_day'] ?? 20))),
			'block_same_ip'   => !empty($p['block_same_ip']) ? 1 : 0,
			'block_rejoin'    => !empty($p['block_rejoin']) ? 1 : 0,
			'max_rewards'     => max(0, (int) ($p['max_rewards'] ?? 0)),
		));
		$msg = 'Settings saved.';
	}

	if (isset($_POST['dsrf_row_action']) && check_admin_referer('dsrf_row')) {
		$id = (int) $_POST['row_id'];
		$row = dsrf_get_row($id);
		$act = sanitize_key($_POST['dsrf_row_action']);
		if ($row) {
			if ($act === 'grant') {
				if ($row->status === 'signed_up') dsrf_check_subscribed($row);
				$row = dsrf_get_row($id);
				if ($row->status === 'signed_up') {
					// admin override for a friend who paid outside Razorpay
					$pl = function_exists('ds_get_user_plan') ? ds_get_user_plan($row->referred_id) : '';
					$wpdb->update($t, array('status' => 'subscribed', 'plan' => in_array($pl, dsrf_paid_plans(), true) ? $pl : 'premium', 'subscribed_at' => dsrf_now(), 'eligible_at' => dsrf_now()), array('id' => $id));
					$row = dsrf_get_row($id);
				}
				$r = dsrf_evaluate($row, true);
				$msg = $r === 'rewarded' ? 'Reward granted.' : 'Nothing to grant for this referral.';
			} elseif ($act === 'reject') {
				$wpdb->update($t, array('status' => 'rejected', 'note' => 'Rejected by admin'), array('id' => $id));
				$msg = 'Referral rejected.';
			} elseif ($act === 'restore' && $row->status === 'rejected') {
				$st = $row->rewarded_at ? 'rewarded' : ($row->subscribed_at ? 'subscribed' : ($row->referred_id ? 'signed_up' : 'invited'));
				$wpdb->update($t, array('status' => $st, 'note' => ''), array('id' => $id));
				$msg = 'Referral restored.';
			} elseif ($act === 'paid') {
				$wpdb->update($t, array('payout_status' => 'paid'), array('id' => $id));
				$msg = 'Marked as paid.';
			} elseif ($act === 'unpaid') {
				$wpdb->update($t, array('payout_status' => 'unpaid'), array('id' => $id));
				$msg = 'Marked as unpaid.';
			} elseif ($act === 'delete') {
				$wpdb->delete($t, array('id' => $id));
				$msg = 'Referral deleted.';
			}
		}
	}

	if (isset($_POST['dsrf_run']) && check_admin_referer('dsrf_run')) {
		$r = dsrf_process();
		$msg = sprintf('Check finished: %d newly subscribed, %d rewarded, %d not eligible.', $r['subscribed'], $r['rewarded'], $r['rejected']);
	}

	if (isset($_POST['dsrf_link']) && check_admin_referer('dsrf_link')) {
		$friend = get_user_by('email', sanitize_email(wp_unslash($_POST['friend_email'] ?? '')));
		$ref_uid = dsrf_user_by_code(wp_unslash($_POST['ref_code'] ?? ''));
		if (!$ref_uid) {
			$ru = get_user_by('email', sanitize_email(wp_unslash($_POST['ref_code'] ?? '')));
			$ref_uid = $ru ? $ru->ID : 0;
		}
		if (!$friend || !$ref_uid) {
			$msg = 'Could not find that friend or referrer.';
		} elseif ($friend->ID === $ref_uid) {
			$msg = 'A user cannot refer themselves.';
		} elseif ($wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE referred_id = %d", $friend->ID))) {
			$msg = 'That user is already recorded as someone\'s referral.';
		} else {
			$wpdb->insert($t, array(
				'referrer_id' => $ref_uid, 'referred_id' => $friend->ID, 'email' => strtolower($friend->user_email),
				'status' => 'signed_up', 'signup_at' => $friend->user_registered ? get_date_from_gmt($friend->user_registered) : dsrf_now(),
				'created_at' => dsrf_now(), 'note' => 'Linked by admin', 'email_norm' => dsrf_norm_email($friend->user_email),
			));
			update_user_meta($friend->ID, 'dsrf_referred_by', $ref_uid);
			dsrf_check_subscribed(dsrf_get_row($wpdb->insert_id));
			$msg = 'Referral linked.';
		}
	}
	return $msg;
}

function dsrf_admin_page() {
	if (!current_user_can('manage_options')) return;
	$msg = dsrf_admin_actions();
	global $wpdb;
	$t = dsrf_table();
	$s = dsrf_settings();
	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'referrals';
	$base = admin_url('admin.php?page=ds-referrals');

	$counts = $wpdb->get_results("SELECT status, COUNT(*) n FROM $t GROUP BY status", OBJECT_K);
	$n = function ($k) use ($counts) { return isset($counts[$k]) ? (int) $counts[$k]->n : 0; };
	$credits_paid = (int) $wpdb->get_var("SELECT COALESCE(SUM(reward_credits),0) FROM $t WHERE status = 'rewarded'");
	$cash_owed = $wpdb->get_results("SELECT reward_currency c, SUM(reward_amount) a FROM $t WHERE status = 'rewarded' AND reward_type = 'cash' AND payout_status = 'unpaid' GROUP BY reward_currency");
	$owed = array();
	foreach ((array) $cash_owed as $o) $owed[] = dsrf_money($o->a, $o->c);
	$next = wp_next_scheduled('dsrf_cron');
	?>
<div class="wrap dsrf-admin">
<style>
.dsrf-admin .dsrf-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:16px 0 20px}
.dsrf-admin .dsrf-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px}
.dsrf-admin .dsrf-card b{display:block;font-size:24px;line-height:1.2;color:#111827}
.dsrf-admin .dsrf-card span{font-size:12px;color:#6b7280}
.dsrf-admin .dsrf-pill{display:inline-block;padding:2px 9px;border-radius:100px;font-size:11px;font-weight:600}
.dsrf-admin .st-invited{background:#f3f4f6;color:#4b5563}.dsrf-admin .st-signed_up{background:#e0e7ff;color:#3730a3}
.dsrf-admin .st-subscribed{background:#fef3c7;color:#92400e}.dsrf-admin .st-rewarded{background:#dcfce7;color:#166534}
.dsrf-admin .st-rejected{background:#fee2e2;color:#991b1b}
.dsrf-admin td form{display:inline}.dsrf-admin .button-link-delete{color:#b32d2e}
.dsrf-admin .dsrf-box{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:6px 20px 16px;margin-bottom:18px;max-width:900px}
.dsrf-admin .dsrf-flag{color:#b45309;font-size:11px}
.dsrf-admin .dsrf-mode-row{display:none}
</style>
<h1 class="wp-heading-inline">Referrals</h1>
<?php if ($msg): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
<?php if (!$s['enabled']): ?><div class="notice notice-warning"><p>The referral program is <strong>switched off</strong>. Links are not tracked and the My Referrals tab is hidden. Turn it on in Settings.</p></div><?php endif; ?>

<div class="dsrf-cards">
  <div class="dsrf-card"><b><?php echo $n('signed_up') + $n('subscribed') + $n('rewarded') + (int) $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE status='rejected' AND referred_id>0"); ?></b><span>Friends signed up</span></div>
  <div class="dsrf-card"><b><?php echo $n('invited'); ?></b><span>E-mail invites pending</span></div>
  <div class="dsrf-card"><b><?php echo $n('subscribed'); ?></b><span>Subscribed — waiting period</span></div>
  <div class="dsrf-card"><b><?php echo $n('rewarded'); ?></b><span>Rewards given</span></div>
  <div class="dsrf-card"><b><?php echo number_format($credits_paid); ?></b><span>Credits paid out</span></div>
  <?php if (function_exists('dsrw_active') && dsrw_active()): ?><div class="dsrf-card"><b><a href="<?php echo esc_url(admin_url('admin.php?page=ds-ref-withdrawals')); ?>" style="text-decoration:none">Withdrawals &rarr;</a></b><span>Cash rewards go to user wallets</span></div><?php else: ?><div class="dsrf-card"><b><?php echo $owed ? esc_html(implode(' + ', $owed)) : '—'; ?></b><span>Cash rewards to pay</span></div><?php endif; ?>
</div>

<h2 class="nav-tab-wrapper">
  <a href="<?php echo esc_url($base); ?>" class="nav-tab <?php echo $tab === 'referrals' ? 'nav-tab-active' : ''; ?>">All referrals</a>
  <a href="<?php echo esc_url($base . '&tab=top'); ?>" class="nav-tab <?php echo $tab === 'top' ? 'nav-tab-active' : ''; ?>">Top referrers</a>
  <a href="<?php echo esc_url($base . '&tab=settings'); ?>" class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
</h2>

<?php if ($tab === 'settings'): ?>
<form method="post">
<?php wp_nonce_field('dsrf_settings'); ?>
<div class="dsrf-box">
<h2>Program</h2>
<table class="form-table" role="presentation">
<tr><th>Status</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked($s['enabled']); ?>> Referral program is on</label></td></tr>
<tr><th>Account popup</th><td><label><input type="checkbox" name="hide_plans_tab" value="1" <?php checked($s['hide_plans_tab']); ?>> Replace the <em>Plans</em> tab with <em>My Referrals</em></label><p class="description">Untick to show both tabs.</p></td></tr>
<tr><th>Referral link lasts</th><td><input type="number" name="cookie_days" min="1" max="365" value="<?php echo esc_attr($s['cookie_days']); ?>" class="small-text"> days <p class="description">How long after clicking a link a visitor can still sign up and count as a referral.</p></td></tr>
</table>
</div>

<div class="dsrf-box">
<h2>Reward for the person who shared</h2>
<table class="form-table" role="presentation">
<tr><th>Reward type</th><td>
  <label><input type="radio" name="reward_mode" value="fixed" <?php checked($s['reward_mode'], 'fixed'); ?>> Fixed credits per plan</label><br>
  <label><input type="radio" name="reward_mode" value="percent" <?php checked($s['reward_mode'], 'percent'); ?>> Percentage of the friend's plan credits</label><br>
  <label><input type="radio" name="reward_mode" value="cash" <?php checked($s['reward_mode'], 'cash'); ?>> Percentage of the friend's payment, as a cash reward (you pay it out and mark it paid here)</label>
</td></tr>
<tr class="dsrf-mode-row" data-mode="fixed"><th>Credits per plan</th><td>
  <?php foreach (dsrf_paid_plans() as $pl): $info = dsrf_plan_info($pl); ?>
  <p><label style="display:inline-block;width:90px;"><?php echo esc_html(dsrf_plan_label($pl)); ?></label>
  <input type="number" min="0" name="fixed[<?php echo esc_attr($pl); ?>]" value="<?php echo esc_attr($s['fixed'][$pl]); ?>" class="small-text"> credits
  <span class="description">(plan gives <?php echo (int) $info['credits']; ?> credits / month)</span></p>
  <?php endforeach; ?>
</td></tr>
<tr class="dsrf-mode-row" data-mode="percent" data-mode2="cash"><th>Percentage</th><td>
  <input type="number" min="0" max="100" step="0.5" name="percent" value="<?php echo esc_attr($s['percent']); ?>" class="small-text"> %
  <p class="description dsrf-mode-row" data-mode="percent">e.g. 20% of a <?php echo (int) dsrf_plan_info('premium')['credits']; ?>-credit Premium plan = <?php echo max(1, (int) round(dsrf_plan_info('premium')['credits'] * (float) $s['percent'] / 100)); ?> credits.</p>
  <p class="description dsrf-mode-row" data-mode="cash">Worked out from the plan price in the friend's currency (₹ for India, $ elsewhere).</p>
</td></tr>
<tr><th>Reward is given</th><td>
  <input type="number" name="wait_days" min="0" max="365" value="<?php echo esc_attr($s['wait_days']); ?>" class="small-text"> days after the friend's first subscription payment, when:<br>
  <label><input type="radio" name="condition" value="completed" <?php checked($s['condition'], 'completed'); ?>> the friend did not take a refund (they completed the month)</label><br>
  <label><input type="radio" name="condition" value="renewed" <?php checked($s['condition'], 'renewed'); ?>> the friend's next monthly payment also succeeded (stricter)</label>
</td></tr>
<tr><th>Max rewards per user</th><td><input type="number" name="max_rewards" min="0" value="<?php echo esc_attr($s['max_rewards']); ?>" class="small-text"> <span class="description">0 = unlimited</span></td></tr>
</table>
</div>

<div class="dsrf-box">
<h2>Friend &amp; safety</h2>
<table class="form-table" role="presentation">
<tr><th>Welcome bonus for the friend</th><td><input type="number" name="friend_bonus" min="0" value="<?php echo esc_attr($s['friend_bonus']); ?>" class="small-text"> credits <p class="description">Extra credits for someone who signs up with a referral link (on top of the normal sign-up bonus). 0 = none.</p></td></tr>
<tr><th>E-mail invites per day</th><td><input type="number" name="invites_per_day" min="1" max="500" value="<?php echo esc_attr($s['invites_per_day']); ?>" class="small-text"> <p class="description">Limits e-mails sent from the popup, to protect your mail reputation. Sharing the link is unlimited.</p></td></tr>
<tr><th>Returning customers</th><td><label><input type="checkbox" name="block_rejoin" value="1" <?php checked(!empty($s['block_rejoin'])); ?>> Don't reward referrals of people who had an account before or were already referred</label><p class="description">Off (recommended): a returning customer who subscribes again counts as a normal referral. Either way they are flagged ⚠ re-joined in the list.</p></td></tr>
<tr><th>Same IP address</th><td><label><input type="checkbox" name="block_same_ip" value="1" <?php checked($s['block_same_ip']); ?>> Automatically reject a sign-up from the same IP as the referrer</label><p class="description">Off: such referrals are only flagged ⚠ in the list for you to review.</p></td></tr>
<tr><th>E-mails</th><td><label><input type="checkbox" name="email_referrer" value="1" <?php checked($s['email_referrer']); ?>> E-mail the referrer when a friend signs up and when a reward is given</label></td></tr>
</table>
</div>
<p class="submit"><button type="submit" name="dsrf_save" value="1" class="button button-primary">Save settings</button></p>
</form>
<script>
(function(){
  function sync(){
    var m=(document.querySelector('input[name=reward_mode]:checked')||{}).value;
    document.querySelectorAll('.dsrf-mode-row').forEach(function(r){
      var on=(r.getAttribute('data-mode')===m||r.getAttribute('data-mode2')===m);
      r.style.display=on?(r.tagName==='TR'?'table-row':'block'):'none';
    });
  }
  document.querySelectorAll('input[name=reward_mode]').forEach(function(i){i.addEventListener('change',sync);});
  sync();
})();
</script>

<?php elseif ($tab === 'top'): ?>
<?php
$top = $wpdb->get_results("SELECT referrer_id,
	SUM(referred_id > 0) signed,
	SUM(status IN ('subscribed','rewarded')) subbed,
	SUM(status = 'rewarded') rewarded,
	SUM(reward_credits) credits
	FROM $t GROUP BY referrer_id ORDER BY rewarded DESC, subbed DESC, signed DESC LIMIT 50");
?>
<table class="widefat striped" style="margin-top:16px;max-width:1000px;">
<thead><tr><th>#</th><th>User</th><th>Code</th><th>Link clicks</th><th>Signed up</th><th>Subscribed</th><th>Rewarded</th><th>Credits earned</th></tr></thead>
<tbody>
<?php if (!$top): ?><tr><td colspan="8">No referrals yet.</td></tr><?php endif; ?>
<?php $i = 0; foreach ((array) $top as $r): $u = get_userdata($r->referrer_id); $i++; ?>
<tr>
<td><?php echo $i; ?></td>
<td><?php echo $u ? '<a href="' . esc_url(get_edit_user_link($u->ID)) . '">' . esc_html($u->display_name) . '</a><br><small>' . esc_html($u->user_email) . '</small>' : '#' . (int) $r->referrer_id; ?></td>
<td><code><?php echo esc_html(get_user_meta($r->referrer_id, 'dsrf_code', true)); ?></code></td>
<td><?php echo (int) get_user_meta($r->referrer_id, 'dsrf_clicks', true); ?></td>
<td><?php echo (int) $r->signed; ?></td>
<td><?php echo (int) $r->subbed; ?></td>
<td><?php echo (int) $r->rewarded; ?></td>
<td><?php echo number_format((int) $r->credits); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>

<?php else: ?>
<?php
$status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
$q = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$paged = max(1, (int) ($_GET['paged'] ?? 1));
$per = 50;
$where = array('1=1');
$args = array();
if ($status && in_array($status, array('invited', 'signed_up', 'subscribed', 'rewarded', 'rejected'), true)) { $where[] = 'r.status = %s'; $args[] = $status; }
if ($status === 'unpaid') { $where[] = "r.reward_type = 'cash' AND r.payout_status = 'unpaid' AND r.status = 'rewarded'"; }
if ($q !== '') {
	$like = '%' . $wpdb->esc_like($q) . '%';
	$where[] = '(r.email LIKE %s OR f.user_email LIKE %s OR f.display_name LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s OR um.meta_value = %s)';
	array_push($args, $like, $like, $like, $like, $like, strtoupper($q));
}
$from = "FROM $t r LEFT JOIN {$wpdb->users} u ON u.ID = r.referrer_id LEFT JOIN {$wpdb->users} f ON f.ID = r.referred_id LEFT JOIN {$wpdb->usermeta} um ON um.user_id = r.referrer_id AND um.meta_key = 'dsrf_code'";
$wsql = implode(' AND ', $where);
$total_sql = "SELECT COUNT(*) $from WHERE $wsql";
$list_sql = "SELECT r.* $from WHERE $wsql ORDER BY r.id DESC LIMIT %d OFFSET %d";
$total = (int) ($args ? $wpdb->get_var($wpdb->prepare($total_sql, $args)) : $wpdb->get_var($total_sql));
$rows = $wpdb->get_results($wpdb->prepare($list_sql, array_merge($args, array($per, ($paged - 1) * $per))));
$labels = array('invited' => 'Invited', 'signed_up' => 'Signed up', 'subscribed' => 'Subscribed', 'rewarded' => 'Rewarded', 'rejected' => 'Not eligible');
?>
<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin:14px 0;">
  <ul class="subsubsub" style="margin:0;">
    <li><a href="<?php echo esc_url($base); ?>" class="<?php echo $status === '' ? 'current' : ''; ?>">All</a> |</li>
    <?php foreach ($labels as $k => $l): ?><li><a href="<?php echo esc_url($base . '&status=' . $k); ?>" class="<?php echo $status === $k ? 'current' : ''; ?>"><?php echo esc_html($l); ?> <span class="count">(<?php echo $n($k); ?>)</span></a> |</li><?php endforeach; ?>
    <li><a href="<?php echo esc_url($base . '&status=unpaid'); ?>" class="<?php echo $status === 'unpaid' ? 'current' : ''; ?>">Cash to pay</a></li>
  </ul>
  <form method="get" style="display:flex;gap:6px;">
    <input type="hidden" name="page" value="ds-referrals"><?php if ($status): ?><input type="hidden" name="status" value="<?php echo esc_attr($status); ?>"><?php endif; ?>
    <input type="search" name="s" value="<?php echo esc_attr($q); ?>" placeholder="Name, e-mail or code">
    <button class="button">Search</button>
  </form>
</div>

<table class="widefat striped">
<thead><tr><th>Referrer</th><th>Friend</th><th>Status</th><th>Signed up</th><th>Subscribed</th><th>Reward</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!$rows): ?><tr><td colspan="7">No referrals found.</td></tr><?php endif; ?>
<?php foreach ((array) $rows as $r):
	$u = get_userdata($r->referrer_id);
	$f = $r->referred_id ? get_userdata($r->referred_id) : null;
	$flags = array_filter(explode(',', (string) $r->flags));
?>
<tr>
<td><?php echo $u ? '<a href="' . esc_url(get_edit_user_link($u->ID)) . '">' . esc_html($u->display_name) . '</a><br><small>' . esc_html($u->user_email) . ' · <code>' . esc_html(get_user_meta($u->ID, 'dsrf_code', true)) . '</code></small>' : '<em>deleted #' . (int) $r->referrer_id . '</em>'; ?></td>
<td><?php
	if ($f) echo '<a href="' . esc_url(get_edit_user_link($f->ID)) . '">' . esc_html($f->display_name) . '</a><br><small>' . esc_html($f->user_email) . '</small>';
	else echo esc_html($r->email) . ($r->referred_id ? ' <em>(deleted)</em>' : '<br><small>invited ' . esc_html(dsrf_fmt_date($r->invited_at)) . '</small>');
	if (in_array('same_ip', $flags, true)) echo '<br><span class="dsrf-flag" title="Signed up from the same IP address as the referrer">⚠ same IP as referrer</span>';
	if (in_array('rejoin', $flags, true)) echo '<br><span class="dsrf-flag" title="This e-mail had an account before or was already referred">⚠ re-joined — e-mail used before</span>';
?></td>
<td><span class="dsrf-pill st-<?php echo esc_attr($r->status); ?>"><?php echo esc_html($labels[$r->status] ?? $r->status); ?></span><?php if ($r->note): ?><br><small><?php echo esc_html($r->note); ?></small><?php endif; ?></td>
<td><?php echo esc_html(dsrf_fmt_date($r->signup_at)) ?: '—'; ?></td>
<td><?php
	if ($r->subscribed_at) {
		echo esc_html(dsrf_plan_label($r->plan)) . ' · ' . esc_html(dsrf_fmt_date($r->subscribed_at));
		if ($r->status === 'subscribed') echo '<br><small>reward due ' . esc_html(dsrf_fmt_date($r->eligible_at)) . '</small>';
	} else echo '—';
?></td>
<td><?php
	if ($r->status === 'rewarded') {
		if ($r->reward_type === 'cash') echo esc_html(dsrf_money($r->reward_amount, $r->reward_currency)) . '<br><small>' . ($r->payout_status === 'paid' ? '✓ paid' : 'to pay') . '</small>';
		else echo '+' . (int) $r->reward_credits . ' credits';
		echo '<br><small>' . esc_html(dsrf_fmt_date($r->rewarded_at)) . '</small>';
	} elseif ($r->status === 'subscribed') {
		$rw = dsrf_calc_reward($r->plan, $r->referred_id);
		echo '<small>' . esc_html($rw['type'] === 'cash' ? dsrf_money($rw['amount'], $rw['currency']) : $rw['credits'] . ' credits') . '<br>auto-granted on ' . esc_html(dsrf_fmt_date($r->eligible_at)) . '</small>';
	} elseif ($r->status === 'signed_up') {
		echo '<small>Auto-granted ' . (int) dsrf_settings()['wait_days'] . ' days after they subscribe</small>';
	} else echo '—';
?></td>
<td>
<?php
$btn = function ($act, $label, $cls = 'button button-small') use ($r) {
	echo '<form method="post">';
	wp_nonce_field('dsrf_row');
	echo '<input type="hidden" name="row_id" value="' . (int) $r->id . '"><button class="' . esc_attr($cls) . '" name="dsrf_row_action" value="' . esc_attr($act) . '">' . esc_html($label) . '</button></form> ';
};
if (in_array($r->status, array('signed_up', 'subscribed'), true)) $btn('grant', 'Grant reward now');
if (in_array($r->status, array('invited', 'signed_up', 'subscribed'), true)) $btn('reject', 'Reject');
if ($r->status === 'rejected') $btn('restore', 'Restore');
if ($r->status === 'rewarded' && $r->reward_type === 'cash' && !(function_exists('dsrw_active') && dsrw_active())) $btn($r->payout_status === 'paid' ? 'unpaid' : 'paid', $r->payout_status === 'paid' ? 'Mark unpaid' : 'Mark paid', 'button button-small button-primary');
if ($r->status !== 'rewarded') $btn('delete', 'Delete', 'button-link button-link-delete');
?>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php
$pages = (int) ceil($total / $per);
if ($pages > 1) {
	echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => $pages)) . '</div></div>';
}
?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:22px;max-width:1000px;">
  <div class="dsrf-box" style="margin:0;">
    <h3>Check now</h3>
    <p class="description">Rewards are checked automatically every hour<?php echo $next ? ' (next: ' . esc_html(get_date_from_gmt(gmdate('Y-m-d H:i:s', $next), 'j M, H:i')) . ')' : ''; ?>. Run it now to update statuses immediately.</p>
    <form method="post"><?php wp_nonce_field('dsrf_run'); ?><button class="button" name="dsrf_run" value="1">Run check now</button></form>
  </div>
  <div class="dsrf-box" style="margin:0;">
    <h3>Link a referral manually</h3>
    <p class="description">For a friend who signed up without using the link.</p>
    <form method="post"><?php wp_nonce_field('dsrf_link'); ?>
      <p><input type="email" name="friend_email" placeholder="Friend's account e-mail" required style="width:100%"></p>
      <p><input type="text" name="ref_code" placeholder="Referrer's code or e-mail" required style="width:100%"></p>
      <button class="button" name="dsrf_link" value="1">Link</button>
    </form>
  </div>
</div>
<?php endif; ?>
</div>
	<?php
}

} // function_exists guard
