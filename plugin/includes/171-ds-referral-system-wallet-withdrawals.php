<?php
/**
 * Code Snippets export — DS Referral System — Wallet & Withdrawals
 *
 * Snippet ID : 909
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-referrals
 * Modified   : 2026-09-30 04:29:42
 * Load order : 171 of 162
 */
/**
 * DS Referral System — Wallet & Withdrawals
 * Run: Everywhere
 *
 * Cash referral rewards go into the user's wallet. The user adds a UPI ID or bank account
 * and asks for a withdrawal once the balance reaches the minimum (default ₹1,000).
 * - Account popup > My Referrals: balance, earned / withdrawn / in process, progress to the
 *   minimum, payout details form, Withdraw button, withdrawal history.
 * - WP Admin > Referrals > Withdrawals: requests with full payout details, Mark paid (with
 *   UTR / reference) or Reject (money returns to the balance), balances of all users, settings.
 * - Balance = cash rewards earned − withdrawals requested or paid (rejected ones don't count).
 * Needs "DS Referral System — Core + Admin". Does not modify any other snippet.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('dsrw_settings') ) {

define('DSRW_DB_VERSION', '1.0');

function dsrw_defaults() {
	return array(
		'enabled'      => 1,
		'min_inr'      => 1000,
		'min_usd'      => 12,
		'allow_upi'    => 1,
		'allow_bank'   => 1,
		'notify_email' => get_option('admin_email'),
	);
}
function dsrw_settings() {
	$o = get_option('dsrw_settings', array());
	return wp_parse_args(is_array($o) ? $o : array(), dsrw_defaults());
}
function dsrw_active() {
	$s = dsrw_settings();
	return !empty($s['enabled']) && function_exists('dsrf_table');
}
function dsrw_table() { global $wpdb; return $wpdb->prefix . 'ds_ref_withdrawals'; }

function dsrw_install() {
	if (get_option('dsrw_db_version') === DSRW_DB_VERSION) return;
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t = dsrw_table();
	$c = $wpdb->get_charset_collate();
	dbDelta("CREATE TABLE $t (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned NOT NULL DEFAULT 0,
  amount decimal(12,2) NOT NULL DEFAULT 0,
  currency varchar(3) NOT NULL DEFAULT 'INR',
  method varchar(10) NOT NULL DEFAULT '',
  details longtext NULL,
  status varchar(12) NOT NULL DEFAULT 'pending',
  txn_ref varchar(100) NOT NULL DEFAULT '',
  note varchar(255) NOT NULL DEFAULT '',
  requested_at datetime DEFAULT NULL,
  processed_at datetime DEFAULT NULL,
  processed_by bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY user_id (user_id),
  KEY status (status)
) $c;");
	update_option('dsrw_db_version', DSRW_DB_VERSION);
}
add_action('init', 'dsrw_install', 6);

/* ---------------- helpers ---------------- */
function dsrw_sym($cur) { return $cur === 'INR' ? '₹' : '$'; }
function dsrw_fmt($amount, $cur) { return dsrw_sym($cur) . number_format((float) $amount, 2); }
function dsrw_min($cur) { $s = dsrw_settings(); return (float) ($cur === 'INR' ? $s['min_inr'] : $s['min_usd']); }

/** Balances per currency for one user. */
function dsrw_balances($uid) {
	global $wpdb;
	$out = array();
	if (!function_exists('dsrf_table')) return $out;
	$rt = dsrf_table();
	$wt = dsrw_table();
	$earned = $wpdb->get_results($wpdb->prepare("SELECT reward_currency c, SUM(reward_amount) a FROM $rt WHERE referrer_id = %d AND status = 'rewarded' AND reward_type = 'cash' GROUP BY reward_currency", $uid));
	foreach ((array) $earned as $e) {
		$c = $e->c ?: 'INR';
		$out[$c] = array('currency' => $c, 'earned' => (float) $e->a, 'withdrawn' => 0.0, 'pending' => 0.0);
	}
	$wd = $wpdb->get_results($wpdb->prepare("SELECT currency c, status s, SUM(amount) a FROM $wt WHERE user_id = %d AND status IN ('pending','paid') GROUP BY currency, status", $uid));
	foreach ((array) $wd as $w) {
		if (!isset($out[$w->c])) $out[$w->c] = array('currency' => $w->c, 'earned' => 0.0, 'withdrawn' => 0.0, 'pending' => 0.0);
		if ($w->s === 'paid') $out[$w->c]['withdrawn'] += (float) $w->a; else $out[$w->c]['pending'] += (float) $w->a;
	}
	foreach ($out as $c => $b) {
		$out[$c]['available'] = round(max(0, $b['earned'] - $b['withdrawn'] - $b['pending']), 2);
		$out[$c]['min'] = dsrw_min($c);
	}
	// INR first
	uksort($out, function ($a, $b) { return $a === 'INR' ? -1 : ($b === 'INR' ? 1 : strcmp($a, $b)); });
	return $out;
}

function dsrw_get_payout($uid) {
	$p = get_user_meta($uid, 'dsrw_payout', true);
	return is_array($p) ? $p : array();
}

function dsrw_mask($num) {
	$num = preg_replace('/\D/', '', (string) $num);
	return strlen($num) > 4 ? str_repeat('•', min(8, strlen($num) - 4)) . substr($num, -4) : $num;
}

/** Payout details as shown to the user (account number masked). */
function dsrw_public_payout($p) {
	if (empty($p['method'])) return array('set' => false);
	if ($p['method'] === 'upi') return array('set' => true, 'method' => 'upi', 'label' => 'UPI', 'line' => $p['upi'], 'name' => $p['name'] ?? '', 'upi' => $p['upi']);
	return array(
		'set' => true, 'method' => 'bank', 'label' => 'Bank account',
		'line' => ($p['bank'] ? $p['bank'] . ' · ' : '') . 'A/c ' . dsrw_mask($p['account']) . ' · ' . $p['ifsc'],
		'name' => $p['name'], 'ifsc' => $p['ifsc'], 'bank' => $p['bank'], 'acc_masked' => dsrw_mask($p['account']),
	);
}

function dsrw_mail($to, $subject, $body) {
	if (function_exists('dsrf_mail')) return dsrf_mail($to, $subject, $body);
	return wp_mail($to, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
}

/* ---------------- REST ---------------- */
function dsrw_rest_get() {
	$uid = get_current_user_id();
	global $wpdb;
	$s = dsrw_settings();
	$rs = function_exists('dsrf_settings') ? dsrf_settings() : array('reward_mode' => '');
	$bal = dsrw_balances($uid);
	if (!$bal && ($rs['reward_mode'] ?? '') === 'cash') {
		$c = strtoupper((string) get_user_meta($uid, 'ds_user_country', true)) === 'IN' || !get_user_meta($uid, 'ds_user_country', true) ? 'INR' : 'USD';
		$bal[$c] = array('currency' => $c, 'earned' => 0.0, 'withdrawn' => 0.0, 'pending' => 0.0, 'available' => 0.0, 'min' => dsrw_min($c));
	}
	$hist = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . dsrw_table() . ' WHERE user_id = %d ORDER BY id DESC LIMIT 50', $uid));
	$has_pending = false;
	$h = array();
	foreach ((array) $hist as $w) {
		if ($w->status === 'pending') $has_pending = true;
		$d = json_decode((string) $w->details, true) ?: array();
		$h[] = array(
			'id' => (int) $w->id,
			'amount' => dsrw_fmt($w->amount, $w->currency),
			'status' => $w->status,
			'method' => $w->method === 'upi' ? 'UPI ' . ($d['upi'] ?? '') : 'Bank A/c ' . dsrw_mask($d['account'] ?? ''),
			'requested' => $w->requested_at ? date_i18n('j M Y', strtotime($w->requested_at)) : '',
			'processed' => $w->processed_at ? date_i18n('j M Y', strtotime($w->processed_at)) : '',
			'ref' => $w->txn_ref,
			'note' => $w->note,
		);
	}
	$b = array();
	foreach ($bal as $c => $x) {
		$b[] = array(
			'currency' => $c, 'sym' => dsrw_sym($c),
			'earned' => $x['earned'], 'withdrawn' => $x['withdrawn'], 'pending' => $x['pending'], 'available' => $x['available'], 'min' => $x['min'],
			'earned_f' => dsrw_fmt($x['earned'], $c), 'withdrawn_f' => dsrw_fmt($x['withdrawn'], $c), 'pending_f' => dsrw_fmt($x['pending'], $c),
			'available_f' => dsrw_fmt($x['available'], $c), 'min_f' => dsrw_fmt($x['min'], $c),
		);
	}
	$show = dsrw_active() && (($rs['reward_mode'] ?? '') === 'cash' || !empty($b) || !empty($h));
	$res = rest_ensure_response(array(
		'show' => $show,
		'balances' => $b,
		'payout' => dsrw_public_payout(dsrw_get_payout($uid)),
		'methods' => array('upi' => (bool) $s['allow_upi'], 'bank' => (bool) $s['allow_bank']),
		'has_pending' => $has_pending,
		'history' => $h,
	));
	$res->header('Cache-Control', 'no-store, private');
	return $res;
}

function dsrw_rest_payout(WP_REST_Request $req) {
	$uid = get_current_user_id();
	$s = dsrw_settings();
	$m = sanitize_key((string) $req->get_param('method'));
	$name = trim(sanitize_text_field((string) $req->get_param('name')));
	if ($m === 'upi') {
		if (!$s['allow_upi']) return new WP_Error('off', 'UPI payouts are not available.', array('status' => 400));
		$upi = strtolower(trim(sanitize_text_field((string) $req->get_param('upi'))));
		if (!preg_match('/^[a-z0-9._\-]{2,256}@[a-z][a-z0-9.\-]{1,63}$/', $upi)) return new WP_Error('upi', 'Please enter a valid UPI ID, like name@okaxis.', array('status' => 400));
		$p = array('method' => 'upi', 'upi' => $upi, 'name' => $name);
	} elseif ($m === 'bank') {
		if (!$s['allow_bank']) return new WP_Error('off', 'Bank payouts are not available.', array('status' => 400));
		$acc  = preg_replace('/\s+/', '', (string) $req->get_param('account'));
		$acc2 = preg_replace('/\s+/', '', (string) $req->get_param('account2'));
		$ifsc = strtoupper(trim(sanitize_text_field((string) $req->get_param('ifsc'))));
		$bank = trim(sanitize_text_field((string) $req->get_param('bank')));
		if (strlen($name) < 3) return new WP_Error('name', 'Please enter the account holder\'s name as per the bank.', array('status' => 400));
		if (!preg_match('/^\d{9,18}$/', $acc)) return new WP_Error('acc', 'The account number should be 9 to 18 digits.', array('status' => 400));
		if ($acc !== $acc2) return new WP_Error('acc2', 'The two account numbers don\'t match.', array('status' => 400));
		if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) return new WP_Error('ifsc', 'Please enter a valid 11-character IFSC code, like HDFC0001234.', array('status' => 400));
		$p = array('method' => 'bank', 'name' => $name, 'account' => $acc, 'ifsc' => $ifsc, 'bank' => $bank);
	} else {
		return new WP_Error('method', 'Choose UPI or bank account.', array('status' => 400));
	}
	$p['updated'] = current_time('mysql');
	update_user_meta($uid, 'dsrw_payout', $p);
	return rest_ensure_response(array('success' => true, 'message' => 'Payout details saved.', 'payout' => dsrw_public_payout($p)));
}

function dsrw_rest_withdraw(WP_REST_Request $req) {
	$uid = get_current_user_id();
	if (!dsrw_active()) return new WP_Error('off', 'Withdrawals are paused right now.', array('status' => 400));
	$lock = 'dsrw_lock_' . $uid;
	if (get_transient($lock)) return new WP_Error('busy', 'Please wait a moment and try again.', array('status' => 429));
	set_transient($lock, 1, 15);
	global $wpdb;
	$wt = dsrw_table();
	$cur = strtoupper(sanitize_text_field((string) $req->get_param('currency'))) ?: 'INR';
	$bal = dsrw_balances($uid);
	$payout = dsrw_get_payout($uid);
	$err = null;
	if (empty($payout['method'])) $err = 'Add your UPI ID or bank account first.';
	elseif ($wpdb->get_var($wpdb->prepare("SELECT id FROM $wt WHERE user_id = %d AND status = 'pending' LIMIT 1", $uid))) $err = 'You already have a withdrawal in process. You can request another once it is paid.';
	elseif (empty($bal[$cur])) $err = 'No balance to withdraw.';
	if ($err) { delete_transient($lock); return new WP_Error('no', $err, array('status' => 400)); }

	$available = $bal[$cur]['available'];
	$min = dsrw_min($cur);
	$amt = $req->get_param('amount');
	$amt = ($amt === null || $amt === '') ? $available : round((float) $amt, 2);
	if ($amt > $available + 0.001) $err = 'You can withdraw up to ' . dsrw_fmt($available, $cur) . '.';
	elseif ($amt < $min) $err = 'The minimum withdrawal is ' . dsrw_fmt($min, $cur) . '.';
	if ($err) { delete_transient($lock); return new WP_Error('amount', $err, array('status' => 400)); }

	$wpdb->insert($wt, array(
		'user_id' => $uid, 'amount' => $amt, 'currency' => $cur, 'method' => $payout['method'],
		'details' => wp_json_encode($payout), 'status' => 'pending', 'requested_at' => current_time('mysql'),
	));
	delete_transient($lock);

	$s = dsrw_settings();
	$u = wp_get_current_user();
	if (!empty($s['notify_email'])) {
		dsrw_mail($s['notify_email'], 'New referral withdrawal: ' . dsrw_fmt($amt, $cur) . ' — ' . $u->display_name,
			'<p><strong>' . esc_html($u->display_name) . '</strong> (' . esc_html($u->user_email) . ') asked to withdraw <strong>' . esc_html(dsrw_fmt($amt, $cur)) . '</strong>.</p>'
			. '<p><a href="' . esc_url(admin_url('admin.php?page=ds-ref-withdrawals')) . '">Open Withdrawals in WP Admin</a></p>');
	}
	return rest_ensure_response(array('success' => true, 'message' => 'Withdrawal of ' . dsrw_fmt($amt, $cur) . ' requested. We will send it to your ' . ($payout['method'] === 'upi' ? 'UPI ID' : 'bank account') . ' soon.'));
}

add_action('rest_api_init', function () {
	$logged = function () { return is_user_logged_in(); };
	register_rest_route('ds/v1', '/wallet', array('methods' => 'GET', 'callback' => 'dsrw_rest_get', 'permission_callback' => $logged));
	register_rest_route('ds/v1', '/wallet/payout', array('methods' => 'POST', 'callback' => 'dsrw_rest_payout', 'permission_callback' => $logged));
	register_rest_route('ds/v1', '/wallet/withdraw', array('methods' => 'POST', 'callback' => 'dsrw_rest_withdraw', 'permission_callback' => $logged));
});

/* ---------------- popup: wallet card in My Referrals ---------------- */
add_action('wp_footer', function () {
	if (!is_user_logged_in() || !dsrw_active()) return;
	$rest = esc_url_raw(rest_url('ds/v1/wallet'));
	$nonce = wp_create_nonce('wp_rest');
	?>
<style>
#dsrw-root{margin-bottom:4px}
.dsrw-card{background:linear-gradient(135deg,#0f0f23,#1e1040);border-radius:18px;padding:18px 20px;color:#fff}
.dsrw-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
.dsrw-k{font-size:10px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.1em}
.dsrw-big{font-size:34px;font-weight:900;line-height:1.1;margin-top:4px}
.dsrw-cur{display:flex;gap:4px}
.dsrw-cur button{border:1px solid rgba(255,255,255,.2);background:transparent;color:rgba(255,255,255,.7);border-radius:100px;font-size:11px;font-weight:700;padding:3px 10px;cursor:pointer;font-family:inherit}
.dsrw-cur button.on{background:#fff;color:#1e1040}
.dsrw-row3{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:14px}
.dsrw-row3 div{background:rgba(255,255,255,.07);border-radius:10px;padding:8px 10px}
.dsrw-row3 b{display:block;font-size:13px;font-weight:800}
.dsrw-row3 span{font-size:10px;color:rgba(255,255,255,.5)}
.dsrw-bar{height:6px;background:rgba(255,255,255,.12);border-radius:100px;overflow:hidden;margin-top:14px}
.dsrw-bar i{display:block;height:100%;background:linear-gradient(90deg,#fbbf24,#f97316);border-radius:100px}
.dsrw-hint{font-size:11px;color:#fbbf24;margin-top:6px}
.dsrw-wbtn{width:100%;margin-top:14px;border:none;border-radius:11px;padding:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
.dsrw-wbtn:disabled{background:rgba(255,255,255,.12);color:rgba(255,255,255,.45);cursor:not-allowed}
.dsrw-sec{margin-top:12px;border:1.5px solid #eef0f6;border-radius:14px;padding:12px 14px}
.dsrw-sec h5{margin:0 0 8px;font-size:12px;font-weight:800;color:#0f0f23;display:flex;justify-content:space-between;align-items:center}
.dsrw-sec h5 a{font-size:11px;color:#6366f1;cursor:pointer;font-weight:700}
.dsrw-pay{font-size:12px;color:#374151;line-height:1.5}
.dsrw-pay small{color:#9ca3af}
.dsrw-tabs{display:flex;gap:6px;margin-bottom:10px}
.dsrw-tabs button{flex:1;border:1.5px solid #e8eaf0;background:#fff;border-radius:9px;padding:8px;font-size:12px;font-weight:700;color:#6b7280;cursor:pointer;font-family:inherit}
.dsrw-tabs button.on{border-color:#6366f1;color:#4f46e5;background:#eef2ff}
.dsrw-f label{display:block;font-size:11px;font-weight:600;color:#6b7280;margin:8px 0 4px}
.dsrw-f input{width:100%;box-sizing:border-box;border:1.5px solid #e8eaf0;border-radius:9px;padding:9px 11px;font-size:13px;font-family:inherit;outline:none;color:#111827;background:#fff}
.dsrw-f input:focus{border-color:#a5b4fc}
.dsrw-actions{display:flex;gap:8px;margin-top:12px}
.dsrw-actions button{flex:1;border:none;border-radius:9px;padding:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}
.dsrw-save{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.dsrw-cancel{background:#f3f4f6;color:#374151}
.dsrw-msg{font-size:11.5px;margin-top:8px}
.dsrw-msg.ok{color:#059669}.dsrw-msg.err{color:#dc2626}
.dsrw-h{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:12px}
.dsrw-h:last-child{border-bottom:none}
.dsrw-h b{color:#0f0f23}
.dsrw-h small{display:block;color:#9ca3af;font-size:10.5px}
.dsrw-pill{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:100px;white-space:nowrap}
.dsrw-pill.pending{background:#fef3c7;color:#92400e}.dsrw-pill.paid{background:#dcfce7;color:#166534}.dsrw-pill.rejected{background:#fee2e2;color:#991b1b}
</style>
<script>
(function(){
  var REST='<?php echo esc_js($rest); ?>', NONCE='<?php echo esc_js($nonce); ?>';
  var D=null, CUR=null, MODE='view', PM=null, busy=false;
  function el(id){return document.getElementById(id);}
  function esc(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function nonce(){return (window.wpApiSettings&&wpApiSettings.nonce)?wpApiSettings.nonce:NONCE;}
  function api(path,body){
    return fetch(REST+path,{method:body?'POST':'GET',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce()},body:body?JSON.stringify(body):undefined})
      .then(function(r){return r.json().then(function(j){return {ok:r.ok,j:j};});});
  }
  function load(){
    if(busy) return; busy=true;
    api('').then(function(res){ busy=false; if(!res.ok) return; D=res.j; if(!CUR&&D.balances.length) CUR=D.balances[0].currency; render(); }).catch(function(){busy=false;});
  }
  function bal(){ for(var i=0;i<D.balances.length;i++) if(D.balances[i].currency===CUR) return D.balances[i]; return D.balances[0]; }

  function render(){
    var root=el('dsrw-root'); if(!root) return;
    if(!D||!D.show){ root.innerHTML=''; root.style.display='none'; return; }
    root.style.display='';
    var b=bal(), h='';
    if(b){
      h+='<div class="dsrw-card"><div class="dsrw-top"><div><div class="dsrw-k">Referral earnings — available</div><div class="dsrw-big">'+esc(b.available_f)+'</div></div>';
      if(D.balances.length>1){ h+='<div class="dsrw-cur">'; D.balances.forEach(function(x){ h+='<button type="button" data-cur="'+x.currency+'" class="'+(x.currency===CUR?'on':'')+'">'+x.currency+'</button>'; }); h+='</div>'; }
      h+='</div><div class="dsrw-row3"><div><b>'+esc(b.earned_f)+'</b><span>Total earned</span></div><div><b>'+esc(b.withdrawn_f)+'</b><span>Withdrawn</span></div><div><b>'+esc(b.pending_f)+'</b><span>In process</span></div></div>';
      var pct=b.min>0?Math.min(100,Math.round(b.available/b.min*100)):100;
      var ready=b.available>=b.min;
      if(!ready){ h+='<div class="dsrw-bar"><i style="width:'+pct+'%"></i></div><div class="dsrw-hint">'+esc(b.sym+(b.min-b.available).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}))+' more to reach the '+esc(b.min_f)+' minimum withdrawal</div>'; }
      var label, dis=false;
      if(D.has_pending){ label='Withdrawal in process'; dis=true; }
      else if(!ready){ label='Withdraw (min '+b.min_f+')'; dis=true; }
      else if(!D.payout.set){ label='Add UPI / bank to withdraw'; }
      else { label='Withdraw '+b.available_f; }
      h+='<button type="button" class="dsrw-wbtn" id="dsrw-wbtn"'+(dis?' disabled':'')+'>'+esc(label)+'</button>';
      h+='</div>';
    }
    if(MODE==='confirm'&&b){
      h+='<div class="dsrw-sec"><h5>Confirm withdrawal</h5><div class="dsrw-f"><label>Amount ('+esc(b.currency)+')</label><input type="number" id="dsrw-amt" min="'+b.min+'" max="'+b.available+'" step="0.01" value="'+b.available.toFixed(2)+'"></div>';
      h+='<div class="dsrw-pay" style="margin-top:8px">Paid to: <b>'+esc(D.payout.label)+'</b> — '+esc(D.payout.line)+'</div>';
      h+='<div class="dsrw-actions"><button type="button" class="dsrw-save" id="dsrw-confirm">Request withdrawal</button><button type="button" class="dsrw-cancel" id="dsrw-cancel">Cancel</button></div><div class="dsrw-msg" id="dsrw-msg"></div></div>';
    }
    // payout details
    h+='<div class="dsrw-sec"><h5>Payout details'+(D.payout.set&&MODE!=='edit'?'<a id="dsrw-edit">Edit</a>':'')+'</h5>';
    if(MODE==='edit'||!D.payout.set){
      var m=PM||(D.payout.method)||(D.methods.upi?'upi':'bank');
      PM=m;
      if(D.methods.upi&&D.methods.bank) h+='<div class="dsrw-tabs"><button type="button" data-pm="upi" class="'+(m==='upi'?'on':'')+'">UPI ID</button><button type="button" data-pm="bank" class="'+(m==='bank'?'on':'')+'">Bank account</button></div>';
      h+='<div class="dsrw-f">';
      if(m==='upi'){
        h+='<label>UPI ID</label><input id="dsrw-upi" placeholder="yourname@okaxis" value="'+esc(D.payout.method==='upi'?D.payout.upi:'')+'">';
        h+='<label>Name (optional)</label><input id="dsrw-name" value="'+esc(D.payout.name||'')+'">';
      } else {
        h+='<label>Account holder name</label><input id="dsrw-name" value="'+esc(D.payout.name||'')+'">';
        h+='<label>Account number</label><input id="dsrw-acc" inputmode="numeric" autocomplete="off" placeholder="'+(D.payout.method==='bank'?'Re-enter to change ('+esc(D.payout.acc_masked)+')':'')+'">';
        h+='<label>Confirm account number</label><input id="dsrw-acc2" inputmode="numeric" autocomplete="off">';
        h+='<label>IFSC code</label><input id="dsrw-ifsc" placeholder="HDFC0001234" style="text-transform:uppercase" value="'+esc(D.payout.ifsc||'')+'">';
        h+='<label>Bank name (optional)</label><input id="dsrw-bank" value="'+esc(D.payout.bank||'')+'">';
      }
      h+='</div><div class="dsrw-actions"><button type="button" class="dsrw-save" id="dsrw-psave">Save payout details</button>'+(D.payout.set?'<button type="button" class="dsrw-cancel" id="dsrw-pcancel">Cancel</button>':'')+'</div><div class="dsrw-msg" id="dsrw-pmsg"></div>';
    } else {
      h+='<div class="dsrw-pay"><b>'+esc(D.payout.label)+'</b><br>'+esc(D.payout.line)+(D.payout.name?'<br><small>'+esc(D.payout.name)+'</small>':'')+'</div>';
    }
    h+='</div>';
    // history
    if(D.history.length){
      h+='<div class="dsrw-sec"><h5>Withdrawals</h5>';
      D.history.forEach(function(w){
        var st=w.status==='paid'?'Paid'+(w.processed?' '+w.processed:''):(w.status==='rejected'?'Rejected':'Processing');
        var sub=esc(w.method)+' · requested '+esc(w.requested);
        if(w.status==='paid'&&w.ref) sub+='<br>Ref: '+esc(w.ref);
        if(w.status==='rejected'&&w.note) sub+='<br>'+esc(w.note)+' — amount returned to your balance';
        h+='<div class="dsrw-h"><div><b>'+esc(w.amount)+'</b><small>'+sub+'</small></div><span class="dsrw-pill '+esc(w.status)+'">'+esc(st)+'</span></div>';
      });
      h+='</div>';
    }
    root.innerHTML=h;
    bind();
  }

  function msg(id,t,ok){ var m=el(id); if(m){ m.className='dsrw-msg '+(ok?'ok':'err'); m.textContent=t; } }

  function bind(){
    document.querySelectorAll('#dsrw-root .dsrw-cur button').forEach(function(b){ b.onclick=function(){ CUR=b.getAttribute('data-cur'); MODE='view'; render(); }; });
    var w=el('dsrw-wbtn');
    if(w) w.onclick=function(){ if(!D.payout.set){ MODE='edit'; render(); var f=el('dsrw-upi')||el('dsrw-name'); if(f) f.focus(); return; } MODE='confirm'; render(); };
    var c=el('dsrw-cancel'); if(c) c.onclick=function(){ MODE='view'; render(); };
    var cf=el('dsrw-confirm');
    if(cf) cf.onclick=function(){
      var amt=parseFloat(el('dsrw-amt').value||'0');
      cf.disabled=true; cf.textContent='Sending…';
      api('/withdraw',{currency:CUR,amount:amt}).then(function(res){
        if(res.ok&&res.j.success){ MODE='view'; load(); setTimeout(function(){ msg('dsrw-pmsg',res.j.message,true); },400); }
        else { cf.disabled=false; cf.textContent='Request withdrawal'; msg('dsrw-msg',(res.j&&res.j.message)||'Could not request the withdrawal.',false); }
      }).catch(function(){ cf.disabled=false; cf.textContent='Request withdrawal'; msg('dsrw-msg','Network error. Please try again.',false); });
    };
    var ed=el('dsrw-edit'); if(ed) ed.onclick=function(){ MODE='edit'; PM=D.payout.method; render(); };
    var pc=el('dsrw-pcancel'); if(pc) pc.onclick=function(){ MODE='view'; render(); };
    document.querySelectorAll('#dsrw-root [data-pm]').forEach(function(b){ b.onclick=function(){ PM=b.getAttribute('data-pm'); render(); }; });
    var ps=el('dsrw-psave');
    if(ps) ps.onclick=function(){
      var body={method:PM,name:(el('dsrw-name')||{}).value||''};
      if(PM==='upi') body.upi=(el('dsrw-upi')||{}).value||'';
      else { body.account=el('dsrw-acc').value; body.account2=el('dsrw-acc2').value; body.ifsc=el('dsrw-ifsc').value; body.bank=el('dsrw-bank').value; }
      ps.disabled=true; ps.textContent='Saving…';
      api('/payout',body).then(function(res){
        if(res.ok&&res.j.success){ MODE='view'; load(); }
        else { ps.disabled=false; ps.textContent='Save payout details'; msg('dsrw-pmsg',(res.j&&res.j.message)||'Could not save.',false); }
      }).catch(function(){ ps.disabled=false; ps.textContent='Save payout details'; msg('dsrw-pmsg','Network error. Please try again.',false); });
    };
  }

  function mount(){
    var pane=el('ds-tab-referrals'); if(!pane) return false;
    if(!el('dsrw-root')){
      var r=document.createElement('div'); r.id='dsrw-root'; r.style.display='none';
      pane.insertBefore(r, pane.firstChild);
      if(window.MutationObserver){
        new MutationObserver(function(){ if(pane.classList.contains('active')) load(); }).observe(pane,{attributes:true,attributeFilter:['class']});
      }
      if(pane.classList.contains('active')) load();
    }
    return true;
  }
  if(!mount()){ var n=0, iv=setInterval(function(){ n++; if(mount()||n>60) clearInterval(iv); },250); }
})();
</script>
	<?php
}, 110);

/* ---------------- admin: Referrals > Withdrawals ---------------- */
add_action('admin_menu', function () {
	global $admin_page_hooks, $wpdb;
	$parent = isset($admin_page_hooks['ds-referrals']) ? 'ds-referrals' : 'options-general.php';
	$n = 0;
	if (get_option('dsrw_db_version')) $n = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . dsrw_table() . " WHERE status = 'pending'");
	$label = 'Withdrawals' . ($n ? ' <span class="awaiting-mod">' . $n . '</span>' : '');
	add_submenu_page($parent, 'Referral Withdrawals', $label, 'manage_options', 'ds-ref-withdrawals', 'dsrw_admin');
}, 21);

function dsrw_admin_actions() {
	global $wpdb;
	$wt = dsrw_table();
	$msg = '';
	if (isset($_POST['dsrw_save_settings']) && check_admin_referer('dsrw_settings')) {
		$p = wp_unslash($_POST);
		update_option('dsrw_settings', array(
			'enabled'      => !empty($p['enabled']) ? 1 : 0,
			'min_inr'      => max(0, (float) ($p['min_inr'] ?? 1000)),
			'min_usd'      => max(0, (float) ($p['min_usd'] ?? 12)),
			'allow_upi'    => !empty($p['allow_upi']) ? 1 : 0,
			'allow_bank'   => !empty($p['allow_bank']) ? 1 : 0,
			'notify_email' => sanitize_email($p['notify_email'] ?? ''),
		));
		$msg = 'Settings saved.';
	}
	if (isset($_POST['dsrw_act']) && check_admin_referer('dsrw_row')) {
		$id = (int) $_POST['wid'];
		$w = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wt WHERE id = %d", $id));
		$act = sanitize_key($_POST['dsrw_act']);
		if ($w && $w->status === 'pending') {
			$u = get_userdata($w->user_id);
			if ($act === 'paid') {
				$ref = sanitize_text_field(wp_unslash($_POST['txn_ref'] ?? ''));
				$wpdb->update($wt, array('status' => 'paid', 'txn_ref' => $ref, 'processed_at' => current_time('mysql'), 'processed_by' => get_current_user_id()), array('id' => $id, 'status' => 'pending'));
				if ($u) dsrw_mail($u->user_email, 'Your withdrawal of ' . dsrw_fmt($w->amount, $w->currency) . ' has been paid',
					'<p>Hi ' . esc_html($u->display_name) . ',</p><p>We have sent <strong>' . esc_html(dsrw_fmt($w->amount, $w->currency)) . '</strong> to your ' . ($w->method === 'upi' ? 'UPI ID' : 'bank account') . '.' . ($ref ? ' Reference: <strong>' . esc_html($ref) . '</strong>.' : '') . '</p><p>Thank you for sharing ' . esc_html(get_bloginfo('name')) . '!</p>');
				$msg = 'Marked as paid and the user was e-mailed.';
			} elseif ($act === 'reject') {
				$note = sanitize_text_field(wp_unslash($_POST['note'] ?? '')) ?: 'Payout details could not be verified';
				$wpdb->update($wt, array('status' => 'rejected', 'note' => $note, 'processed_at' => current_time('mysql'), 'processed_by' => get_current_user_id()), array('id' => $id, 'status' => 'pending'));
				if ($u) dsrw_mail($u->user_email, 'Your withdrawal request needs attention',
					'<p>Hi ' . esc_html($u->display_name) . ',</p><p>We could not complete your withdrawal of <strong>' . esc_html(dsrw_fmt($w->amount, $w->currency)) . '</strong>: ' . esc_html($note) . '.</p><p>The amount is back in your referral balance. Please check your payout details in your account and request again.</p>');
				$msg = 'Rejected. The amount is back in the user\'s balance.';
			}
		}
	}
	return $msg;
}

function dsrw_admin() {
	if (!current_user_can('manage_options')) return;
	$msg = dsrw_admin_actions();
	global $wpdb;
	$wt = dsrw_table();
	$s = dsrw_settings();
	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'requests';
	$base = admin_url('admin.php?page=ds-ref-withdrawals');
	$st = isset($_GET['status']) ? sanitize_key($_GET['status']) : 'pending';
	$cnt = $wpdb->get_results("SELECT status, COUNT(*) n, SUM(amount) a, currency c FROM $wt GROUP BY status, currency");
	$tot = array('pending' => array(), 'paid' => array());
	$num = array('pending' => 0, 'paid' => 0, 'rejected' => 0);
	foreach ((array) $cnt as $c) { $num[$c->status] = ($num[$c->status] ?? 0) + (int) $c->n; if (isset($tot[$c->status])) $tot[$c->status][] = dsrw_fmt($c->a, $c->c); }
	?>
<div class="wrap">
<h1>Referral Withdrawals</h1>
<?php if ($msg): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
<style>
.dsrw-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:16px 0}
.dsrw-cards div{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px}
.dsrw-cards b{display:block;font-size:22px}
.dsrw-cards span{font-size:12px;color:#6b7280}
.dsrw-box{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:6px 20px 16px;margin:16px 0;max-width:900px}
.dsrw-pay{font-family:ui-monospace,Menlo,monospace;font-size:12px;line-height:1.6}
.dsrw-pill{display:inline-block;padding:2px 9px;border-radius:100px;font-size:11px;font-weight:600}
.dsrw-pill.pending{background:#fef3c7;color:#92400e}.dsrw-pill.paid{background:#dcfce7;color:#166534}.dsrw-pill.rejected{background:#fee2e2;color:#991b1b}
.dsrw-act form{margin:0 0 6px}
.dsrw-act input[type=text]{width:170px}
</style>
<div class="dsrw-cards">
	<div><b><?php echo (int) $num['pending']; ?></b><span>Requests waiting<?php echo $tot['pending'] ? ' · ' . esc_html(implode(' + ', $tot['pending'])) : ''; ?></span></div>
	<div><b><?php echo $tot['paid'] ? esc_html(implode(' + ', $tot['paid'])) : '—'; ?></b><span>Paid out in total</span></div>
	<div><b><?php echo esc_html(dsrw_fmt($s['min_inr'], 'INR')); ?></b><span>Minimum withdrawal (India)</span></div>
</div>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo esc_url($base); ?>" class="nav-tab <?php echo $tab === 'requests' ? 'nav-tab-active' : ''; ?>">Requests</a>
	<a href="<?php echo esc_url($base . '&tab=balances'); ?>" class="nav-tab <?php echo $tab === 'balances' ? 'nav-tab-active' : ''; ?>">User balances</a>
	<a href="<?php echo esc_url($base . '&tab=settings'); ?>" class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
</h2>

<?php if ($tab === 'settings'): ?>
<form method="post"><?php wp_nonce_field('dsrw_settings'); ?>
<div class="dsrw-box"><table class="form-table" role="presentation">
<tr><th>Withdrawals</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked($s['enabled']); ?>> Let users withdraw their cash referral earnings</label></td></tr>
<tr><th>Minimum withdrawal (₹)</th><td><input type="number" name="min_inr" min="0" step="1" value="<?php echo esc_attr($s['min_inr']); ?>" class="small-text"> <span class="description">for users paid in rupees</span></td></tr>
<tr><th>Minimum withdrawal ($)</th><td><input type="number" name="min_usd" min="0" step="0.5" value="<?php echo esc_attr($s['min_usd']); ?>" class="small-text"> <span class="description">for users outside India, paid in dollars</span></td></tr>
<tr><th>Payout methods</th><td><label><input type="checkbox" name="allow_upi" value="1" <?php checked($s['allow_upi']); ?>> UPI ID</label><br><label><input type="checkbox" name="allow_bank" value="1" <?php checked($s['allow_bank']); ?>> Bank account (account number + IFSC)</label></td></tr>
<tr><th>Notify</th><td><input type="email" name="notify_email" value="<?php echo esc_attr($s['notify_email']); ?>" class="regular-text"><p class="description">E-mail for new withdrawal requests. Empty = no e-mail.</p></td></tr>
</table></div>
<p class="submit"><button class="button button-primary" name="dsrw_save_settings" value="1">Save settings</button></p>
</form>

<?php elseif ($tab === 'balances'): ?>
<?php
$rt = function_exists('dsrf_table') ? dsrf_table() : '';
$uids = $rt ? $wpdb->get_col("SELECT DISTINCT referrer_id FROM $rt WHERE status = 'rewarded' AND reward_type = 'cash' UNION SELECT DISTINCT user_id FROM $wt") : array();
?>
<table class="widefat striped" style="margin-top:16px;max-width:1100px;">
<thead><tr><th>User</th><th>Earned</th><th>Withdrawn</th><th>In process</th><th>Available</th><th>Payout details</th></tr></thead><tbody>
<?php if (!$uids): ?><tr><td colspan="6">No cash earnings yet.</td></tr><?php endif; ?>
<?php foreach ((array) $uids as $uid): $u = get_userdata($uid); foreach (dsrw_balances($uid) as $c => $b): $p = dsrw_public_payout(dsrw_get_payout($uid)); ?>
<tr>
<td><?php echo $u ? '<a href="' . esc_url(get_edit_user_link($uid)) . '">' . esc_html($u->display_name) . '</a><br><small>' . esc_html($u->user_email) . '</small>' : '#' . (int) $uid; ?></td>
<td><?php echo esc_html(dsrw_fmt($b['earned'], $c)); ?></td>
<td><?php echo esc_html(dsrw_fmt($b['withdrawn'], $c)); ?></td>
<td><?php echo esc_html(dsrw_fmt($b['pending'], $c)); ?></td>
<td><strong><?php echo esc_html(dsrw_fmt($b['available'], $c)); ?></strong><?php echo $b['available'] >= $b['min'] ? ' <span class="dsrw-pill paid">can withdraw</span>' : ''; ?></td>
<td><?php echo $p['set'] ? esc_html($p['label'] . ': ' . $p['line']) : '<em>not added</em>'; ?></td>
</tr>
<?php endforeach; endforeach; ?>
</tbody></table>

<?php else: ?>
<ul class="subsubsub" style="margin:12px 0;">
<?php foreach (array('pending' => 'Waiting', 'paid' => 'Paid', 'rejected' => 'Rejected', 'all' => 'All') as $k => $l): ?>
	<li><a href="<?php echo esc_url($base . '&status=' . $k); ?>" class="<?php echo $st === $k ? 'current' : ''; ?>"><?php echo esc_html($l); ?><?php echo isset($num[$k]) ? ' <span class="count">(' . (int) $num[$k] . ')</span>' : ''; ?></a><?php echo $k !== 'all' ? ' |' : ''; ?></li>
<?php endforeach; ?>
</ul>
<?php
$rows = ($st === 'all')
	? $wpdb->get_results("SELECT * FROM $wt ORDER BY id DESC LIMIT 200")
	: $wpdb->get_results($wpdb->prepare("SELECT * FROM $wt WHERE status = %s ORDER BY id " . ($st === 'pending' ? 'ASC' : 'DESC') . ' LIMIT 200', $st));
?>
<table class="widefat striped" style="clear:both;">
<thead><tr><th>User</th><th>Amount</th><th>Pay to</th><th>Requested</th><th>Status</th><th style="width:260px;">Action</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td colspan="6">Nothing here.</td></tr><?php endif; ?>
<?php foreach ((array) $rows as $w): $u = get_userdata($w->user_id); $d = json_decode((string) $w->details, true) ?: array(); $b = dsrw_balances($w->user_id); ?>
<tr>
<td><?php echo $u ? '<a href="' . esc_url(get_edit_user_link($u->ID)) . '">' . esc_html($u->display_name) . '</a><br><small>' . esc_html($u->user_email) . '</small>' : '#' . (int) $w->user_id; ?></td>
<td><strong><?php echo esc_html(dsrw_fmt($w->amount, $w->currency)); ?></strong><?php if (isset($b[$w->currency])): ?><br><small>earned <?php echo esc_html(dsrw_fmt($b[$w->currency]['earned'], $w->currency)); ?></small><?php endif; ?></td>
<td class="dsrw-pay"><?php
	if ($w->method === 'upi') echo 'UPI: <strong>' . esc_html($d['upi'] ?? '') . '</strong>' . (!empty($d['name']) ? '<br>' . esc_html($d['name']) : '');
	else echo esc_html($d['name'] ?? '') . '<br>A/c: <strong>' . esc_html($d['account'] ?? '') . '</strong><br>IFSC: <strong>' . esc_html($d['ifsc'] ?? '') . '</strong>' . (!empty($d['bank']) ? '<br>' . esc_html($d['bank']) : '');
?></td>
<td><?php echo esc_html($w->requested_at ? date_i18n('j M Y, H:i', strtotime($w->requested_at)) : ''); ?></td>
<td><span class="dsrw-pill <?php echo esc_attr($w->status); ?>"><?php echo esc_html(ucfirst($w->status === 'pending' ? 'waiting' : $w->status)); ?></span>
	<?php if ($w->txn_ref): ?><br><small>Ref: <?php echo esc_html($w->txn_ref); ?></small><?php endif; ?>
	<?php if ($w->note): ?><br><small><?php echo esc_html($w->note); ?></small><?php endif; ?>
	<?php if ($w->processed_at): ?><br><small><?php echo esc_html(date_i18n('j M Y', strtotime($w->processed_at))); ?></small><?php endif; ?></td>
<td class="dsrw-act"><?php if ($w->status === 'pending'): ?>
	<form method="post"><?php wp_nonce_field('dsrw_row'); ?><input type="hidden" name="wid" value="<?php echo (int) $w->id; ?>">
	<input type="text" name="txn_ref" placeholder="UTR / reference no."> <button class="button button-primary button-small" name="dsrw_act" value="paid">Mark paid</button></form>
	<form method="post"><?php wp_nonce_field('dsrw_row'); ?><input type="hidden" name="wid" value="<?php echo (int) $w->id; ?>">
	<input type="text" name="note" placeholder="Reason (shown to user)"> <button class="button button-small" name="dsrw_act" value="reject">Reject</button></form>
<?php else: ?>—<?php endif; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<p class="description" style="margin-top:12px;">Pay the user by UPI or bank transfer yourself, then click <strong>Mark paid</strong> with the UTR / reference number. The user is e-mailed and sees it in their account. <strong>Reject</strong> returns the amount to their balance.</p>
<?php endif; ?>
</div>
	<?php
}

} // function_exists guard
