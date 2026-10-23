<?php
/**
 * Code Snippets export — DS PPT Free Plan - daily reset (2 free presentations per day)
 *
 * Snippet ID : 1055 (staging)
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-ppt, free-plan
 * Modified   : 2026-10-05 08:50:00
 * Load order : last (needs include 196, DS PPT Free Plan)
 */
/**
 * DS PPT Free Plan - daily reset (free presentations per DAY instead of per account)
 *
 * NEW SNIPPET - edits no other snippet. It works together with
 * "DS PPT Free Plan" (which counts free presentations in the user meta
 * 'ds_free_ppt_used' and never resets it).
 *
 * How: this snippet remembers the day the counter was last written
 * ('ds_free_ppt_day'). When the counter is read on a different day it is
 * reported as 0, so the user gets the full allowance again. The number of
 * free presentations per day is still the "Free presentations per account"
 * setting in Settings > Free Presentations (default 2).
 *
 * "Day" follows the WordPress site timezone and resets at midnight.
 * Deactivate this snippet to go back to the old per-account limit.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_fpd_today') ) {
function ds_fpd_today() {
	/* Administrators can pretend it is another day (cookie ds_fpd_testday=YYYY-MM-DD) to test the daily reset. */
	if ( ! empty($_COOKIE['ds_fpd_testday']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_COOKIE['ds_fpd_testday']) && current_user_can('manage_options') ) return (string) $_COOKIE['ds_fpd_testday'];
	return current_time('Y-m-d');
}
}

/* Reading the counter: only today's count is real, any older count reads as 0. */
add_filter('get_user_metadata', function ( $value, $uid, $key, $single ) {
	if ( $key !== 'ds_free_ppt_used' ) return $value;
	$day = (string) get_user_meta( (int) $uid, 'ds_free_ppt_day', true );
	if ( $day === ds_fpd_today() ) return $value;      /* written today: use the stored number */
	return $single ? '0' : array('0');
}, 10, 4);

/* Writing the counter: stamp it with today's date. */
if ( ! function_exists('ds_fpd_stamp') ) {
function ds_fpd_stamp( $mid, $uid, $key ) {
	if ( $key === 'ds_free_ppt_used' ) update_user_meta( (int) $uid, 'ds_free_ppt_day', ds_fpd_today() );
}
}
add_action('added_user_meta', 'ds_fpd_stamp', 10, 3);
add_action('updated_user_meta', 'ds_fpd_stamp', 10, 3);

/* Server message when the daily limit is reached. */
add_filter('rest_post_dispatch', function ( $response, $server, $request ) {
	if ( $request->get_route() !== '/ds/v1/free-ppt/start' || ! ( $response instanceof WP_REST_Response ) ) return $response;
	$d = $response->get_data();
	if ( is_array($d) && isset($d['error']) && $d['error'] === 'limit' && function_exists('ds_fp_settings') ) {
		$s = ds_fp_settings();
		$n = (int) $s['max_gens'];
		$d['message'] = 'You have used your ' . $n . ' free presentation' . ( $n === 1 ? '' : 's' ) . ' for today. Come back tomorrow, or upgrade to create more.';
		$response->set_data($d);
	}
	return $response;
}, 10, 3);

/* Page wording for free users: say "today" in the banner and the limit pop-up. */
add_action('wp_footer', function () {
	if ( ! is_user_logged_in() || ! function_exists('ds_fp_is_free') || ! ds_fp_is_free( get_current_user_id() ) ) return;
	?>
<script id="ds-free-ppt-daily">
(function(){
	function fixBanner(){
		var t = document.querySelector('#dsfp-banner .dsfp-txt');
		if (!t) return;
		for (var n = t.firstChild; n; n = n.nextSibling) {
			if (n.nodeType !== 3) continue;
			var s = n.nodeValue, r = s;
			if (/ left\.$/.test(s)) r = s.replace(/ left\.$/, ' left today.');
			else if (/you have used your .*free presentations?\.$/.test(s)) r = s.replace(/\.$/, ' for today. You get more tomorrow.');
			if (r !== s) n.nodeValue = r;
		}
	}
	function wrap(){
		var D = window.DSFP;
		if (!D || !D.upgrade || D.upgrade.__daily) return;
		var orig = D.upgrade;
		D.upgrade = function(msg){
			return orig.call(this, String(msg || '').replace(/(free presentations?)\. Upgrade to create more/, '$1 for today. Come back tomorrow, or upgrade to create more'));
		};
		D.upgrade.__daily = true;
	}
	function tick(){ wrap(); fixBanner(); }
	tick();
	if (window.MutationObserver) new MutationObserver(tick).observe(document.documentElement, {childList: true, subtree: true, characterData: true});
	else setInterval(tick, 1500);
})();
</script>
	<?php
}, 99);

/* Settings page: make the label say "per day". */
add_action('admin_footer-settings_page_ds-free-ppt', function () {
	?>
<script>(function(){var l=document.querySelector('label[for="ds_fp_g"]');if(l)l.textContent='Free presentations per day (per account)';})();</script>
	<?php
});
