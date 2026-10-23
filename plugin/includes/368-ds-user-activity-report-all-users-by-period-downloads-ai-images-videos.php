<?php
/**
 * Code Snippets export — DS User Activity Report — all users by period (downloads, AI images/videos/audio/PPTs)
 *
 * Snippet ID : 1244
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-16 02:19:44
 * Load order : 368 of 369
 */
/**
 * DS User Activity Report - all users, by period
 * Daily download log (every plan, including unlimited ones) + a report table on
 * the DS User Activity page: credits, free / pro downloads, AI images, videos,
 * audio and PPTs per user for today / yesterday / this week / this month.
 * AI counts come from the dated history lists the AI snippets already keep.
 */
if ( ! defined('ABSPATH') ) exit;

function ds_ua_today() { return wp_date('Y-m-d'); }

/* one download, counted on the site's own calendar day */
function ds_ua_log_dl( $uid, $tier ) {
	$uid = (int) $uid; if ( ! $uid ) return;
	$tier = in_array($tier, array('pro', 'ppt'), true) ? $tier : 'free';
	$k = 'ds_dlc_' . ds_ua_today();
	$a = get_user_meta($uid, $k, true); if ( ! is_array($a) ) $a = array('free' => 0, 'pro' => 0, 'ppt' => 0);
	$a[$tier] = (int) ($a[$tier] ?? 0) + 1;
	update_user_meta($uid, $k, $a);
	ds_ua_seen($uid);
}

/* the days a user was on the site (last 70 days kept) */
function ds_ua_seen( $uid ) {
	$d = ds_ua_today();
	$days = get_user_meta($uid, 'ds_seen_days', true); if ( ! is_array($days) ) $days = array();
	if ( end($days) === $d ) return;
	$days[] = $d; $days = array_slice(array_values(array_unique($days)), -70);
	update_user_meta($uid, 'ds_seen_days', $days);
	update_user_meta($uid, 'ds_last_seen', current_time('mysql'));
}
add_action('init', function () {
	if ( ! is_user_logged_in() || wp_doing_cron() ) return;
	$uid = get_current_user_id();
	if ( get_transient('ds_seen_' . $uid) === ds_ua_today() ) return;
	ds_ua_seen($uid);
	set_transient('ds_seen_' . $uid, ds_ua_today(), 6 * HOUR_IN_SECONDS);
});

function ds_ua_range( $p ) {
	$tz = wp_timezone(); $now = new DateTime('now', $tz);
	$to = $now->format('Y-m-d');
	if ( $p === 'yesterday' ) { $y = (clone $now)->modify('-1 day')->format('Y-m-d'); return array($y, $y); }
	if ( $p === 'week' ) {
		$sow = (int) get_option('start_of_week', 1); $dow = (int) $now->format('w');
		$back = ($dow - $sow + 7) % 7;
		return array((clone $now)->modify("-{$back} days")->format('Y-m-d'), $to);
	}
	if ( $p === 'month' ) return array($now->format('Y-m-01'), $to);
	if ( $p === 'all' ) return array('0000-00-00', $to);
	return array($to, $to);
}

/* dated history entries inside the range (failed jobs not counted) */
function ds_ua_count( $list, $from, $to, $min_slides = 0 ) {
	if ( ! is_array($list) ) return 0;
	$n = 0;
	foreach ( $list as $g ) {
		if ( ! is_array($g) ) continue;
		$d = isset($g['date']) ? substr((string) $g['date'], 0, 10) : ( isset($g['t']) ? wp_date('Y-m-d', (int) $g['t']) : '' );
		if ( $d === '' || $d < $from || $d > $to ) continue;
		if ( ( $g['status'] ?? '' ) === 'failed' ) continue;
		if ( $min_slides && isset($g['num_slides']) && (int) $g['num_slides'] < $min_slides ) continue; /* an added slide is not a new deck */
		$n++;
	}
	return $n;
}

function ds_ua_dl_sum( $uid, $from, $to ) {
	$f = 0; $p = 0; $x = 0;
	foreach ( (array) get_user_meta($uid) as $mk => $mv ) {
		if ( strpos($mk, 'ds_dlc_') !== 0 ) continue;
		$day = substr($mk, 7); if ( $day < $from || $day > $to ) continue;
		$a = maybe_unserialize(is_array($mv) ? $mv[0] : $mv);
		if ( is_array($a) ) { $f += (int) ($a['free'] ?? 0); $p += (int) ($a['pro'] ?? 0); $x += (int) ($a['ppt'] ?? 0); }
	}
	return array('free' => $f, 'pro' => $p, 'ppt' => $x);
}

/* every finished presentation export (AI deck, template editor, free plan) is one PPT download */
add_filter('rest_request_after_callbacks', function ( $response, $handlers, $request ) {
	if ( is_wp_error($response) || ! is_a($response, 'WP_REST_Response') || $request->get_method() !== 'POST' ) return $response;
	if ( strpos($request->get_route(), '/ds/v1/') !== 0 ) return $response;
	$d = $response->get_data();
	if ( is_array($d) && ! empty($d['success']) && ! empty($d['url']) && is_string($d['url']) && strpos($d['url'], '/ds-ppt-downloads/') !== false ) ds_ua_log_dl(get_current_user_id(), 'ppt');
	return $response;
}, 5, 3);

function ds_ua_plan( $u ) {
	$roles = (array) $u->roles;
	if ( in_array('administrator', $roles, true) ) return 'admin';
	if ( function_exists('ds_access_plan') ) { $p = (string) ds_access_plan($u->ID); if ( $p !== '' ) return $p; }
	$rs = implode(',', $roles);
	if ( strpos($rs, 'business') !== false ) return 'business';
	if ( strpos($rs, '_pro') !== false ) return 'pro';
	if ( strpos($rs, 'premium') !== false ) return 'premium';
	return 'free';
}

function ds_ua_user_row( $u, $from, $to ) {
	$id = $u->ID;
	$dl = ds_ua_dl_sum($id, $from, $to);
	$r = array(
		'u' => $u,
		'plan' => ds_ua_plan($u),
		'credits' => function_exists('ds_get_credits') ? (int) ds_get_credits($id) : (int) get_user_meta($id, 'ds_ai_credits', true),
		'free' => $dl['free'],
		'pro' => $dl['pro'],
		'pptdl' => $dl['ppt'],
		'img' => ds_ua_count(get_user_meta($id, 'ds_ai_generations', true), $from, $to),
		'video' => ds_ua_count(get_user_meta($id, 'ds_ai_video_generations', true), $from, $to),
		'audio' => ds_ua_count(get_user_meta($id, 'ds_ai_audio_generations', true), $from, $to),
		'ppt' => ds_ua_count(get_user_meta($id, 'ds_ppt_generations', true), $from, $to, 2),
		'joined' => substr(get_date_from_gmt($u->user_registered, 'Y-m-d H:i:s'), 0, 16),
		'last' => '',
		'seen' => false,
	);
	$days = get_user_meta($id, 'ds_seen_days', true);
	if ( is_array($days) ) foreach ( $days as $d ) if ( $d >= $from && $d <= $to ) { $r['seen'] = true; break; }
	$ls = (string) get_user_meta($id, 'ds_last_seen', true); $ll = (string) get_user_meta($id, 'ds_last_login', true);
	$r['last'] = substr(max($ls, $ll), 0, 16);
	$ld = substr($ll, 0, 10); if ( $ld !== '' && $ld >= $from && $ld <= $to ) $r['seen'] = true;
	$r['acts'] = $r['free'] + $r['pro'] + $r['pptdl'] + $r['img'] + $r['video'] + $r['audio'] + $r['ppt'];
	$r['active'] = $r['acts'] > 0 || $r['seen'];
	return $r;
}

function ds_ua_render_report() {
	if ( ! current_user_can('manage_options') ) return;
	$periods = array('today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This week', 'month' => 'This month', 'all' => 'All time');
	$sorts = array('new' => 'New signups', 'name' => 'Name', 'active' => 'Active users');
	$plans = array('all' => 'All plans', 'free' => 'Free users', 'paid' => 'Paid users');
	$per = isset($_GET['ua_period'], $periods[$_GET['ua_period']]) ? $_GET['ua_period'] : 'today';
	$sort = isset($_GET['ua_sort'], $sorts[$_GET['ua_sort']]) ? $_GET['ua_sort'] : 'new';
	$pf = isset($_GET['ua_plan'], $plans[$_GET['ua_plan']]) ? $_GET['ua_plan'] : 'all';
	list($from, $to) = ds_ua_range($per);

	$users = get_users(array('fields' => 'all', 'number' => 5000));
	update_meta_cache('user', wp_list_pluck($users, 'ID'));
	$rows = array(); $T = array('users' => 0, 'freeu' => 0, 'paidu' => 0, 'new' => 0, 'active' => 0, 'free' => 0, 'pro' => 0, 'img' => 0, 'video' => 0, 'audio' => 0, 'ppt' => 0, 'pptdl' => 0);
	foreach ( $users as $u ) {
		$r = ds_ua_user_row($u, $from, $to);
		$isfree = $r['plan'] === 'free';
		$T['users']++; $isfree ? $T['freeu']++ : ( $r['plan'] !== 'admin' ? $T['paidu']++ : 0 );
		$jd = substr($r['joined'], 0, 10); if ( $jd >= $from && $jd <= $to ) $T['new']++;
		if ( $r['active'] ) $T['active']++;
		foreach ( array('free', 'pro', 'img', 'video', 'audio', 'ppt', 'pptdl') as $k ) $T[$k] += $r[$k];
		if ( $pf === 'free' && ! $isfree ) continue;
		if ( $pf === 'paid' && ( $isfree || $r['plan'] === 'admin' ) ) continue;
		if ( $sort === 'active' && ! $r['active'] ) continue;
		$rows[] = $r;
	}
	usort($rows, function ($a, $b) use ($sort) {
		if ( $sort === 'name' ) return strcasecmp($a['u']->display_name ?: $a['u']->user_login, $b['u']->display_name ?: $b['u']->user_login);
		if ( $sort === 'active' ) return $b['acts'] <=> $a['acts'] ?: strcmp($b['last'], $a['last']);
		return strcmp($b['u']->user_registered, $a['u']->user_registered);
	});
	$total = count($rows); $pp = 50; $pg = max(1, (int) ($_GET['ua_pg'] ?? 1)); $pages = max(1, (int) ceil($total / $pp)); $pg = min($pg, $pages);
	$rows = array_slice($rows, ($pg - 1) * $pp, $pp);

	$sel = function ($name, $opts, $cur) {
		$h = '<select name="' . $name . '" onchange="this.form.submit()">';
		foreach ( $opts as $k => $l ) $h .= '<option value="' . esc_attr($k) . '"' . selected($cur, $k, false) . '>' . esc_html($l) . '</option>';
		return $h . '</select>';
	};
	echo '<style>.dsua-cards{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0}.dsua-card{background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:10px 14px;min-width:110px}.dsua-card b{display:block;font-size:20px;line-height:1.3}.dsua-card span{color:#646970;font-size:12px}.dsua-t td,.dsua-t th{white-space:nowrap}.dsua-t td.n,.dsua-t th.n{text-align:center}.dsua-z{color:#c3c4c7}.dsua-plan{display:inline-block;padding:1px 8px;border-radius:10px;font-size:11px;background:#f0f0f1}.dsua-plan.free{background:#fcf0e3;color:#8a4b00}.dsua-plan.paid{background:#e7f5ea;color:#0a6b2a}</style>';
	echo '<h2 style="margin-top:28px">All users activity</h2>';
	echo '<form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><input type="hidden" name="page" value="ds-user-activity">';
	echo '<label>Period ' . $sel('ua_period', $periods, $per) . '</label>';
	echo '<label>Sort by ' . $sel('ua_sort', $sorts, $sort) . '</label>';
	echo '<label>Show ' . $sel('ua_plan', $plans, $pf) . '</label>';
	echo '<span style="color:#646970">' . esc_html($per === 'all' ? 'All time' : ( $from === $to ? wp_date('d M Y', strtotime($from)) : wp_date('d M', strtotime($from)) . ' – ' . wp_date('d M Y', strtotime($to)) )) . '</span></form>';

	$card = function ($v, $l) { return '<div class="dsua-card"><b>' . esc_html(number_format_i18n($v)) . '</b><span>' . esc_html($l) . '</span></div>'; };
	echo '<div class="dsua-cards">' . $card($T['users'], 'Total users') . $card($T['freeu'], 'Free users') . $card($T['paidu'], 'Paid users')
		. $card($T['new'], 'New signups') . $card($T['active'], 'Active users') . $card($T['free'], 'Free downloads') . $card($T['pro'], 'Pro downloads')
		. $card($T['img'], 'AI images') . $card($T['video'], 'AI videos') . $card($T['audio'], 'AI audio') . '<div class="dsua-card"><b>' . esc_html(number_format_i18n($T['ppt'])) . ' <span style="font-size:13px;color:#646970">generated</span> &rarr; ' . esc_html(number_format_i18n($T['pptdl'])) . ' <span style="font-size:13px;color:#646970">downloaded</span></b><span>PPTs</span></div>' . '</div>';

	echo '<table class="widefat striped dsua-t"><thead><tr><th>User</th><th>Plan</th><th class="n">Credits</th><th class="n">Free downloads</th><th class="n">Pro downloads</th><th class="n">AI images</th><th class="n">AI videos</th><th class="n">AI audio</th><th class="n">PPTs generated</th><th class="n">PPTs downloaded</th><th>Signed up</th><th>Last active</th></tr></thead><tbody>';
	if ( ! $rows ) echo '<tr><td colspan="12">No users for this view.</td></tr>';
	$num = function ($v) { return '<td class="n' . ( $v ? '' : ' dsua-z' ) . '">' . ( $v ? '<strong>' . esc_html($v) . '</strong>' : '0' ) . '</td>'; };
	foreach ( $rows as $r ) {
		$u = $r['u'];
		$url = add_query_arg(array('page' => 'ds-user-activity', 'ds_user' => $u->ID), admin_url('admin.php'));
		$nm = trim($u->display_name) !== '' && $u->display_name !== $u->user_email ? $u->display_name : $u->user_login;
		$pc = $r['plan'] === 'free' ? 'free' : ( $r['plan'] === 'admin' ? '' : 'paid' );
		echo '<tr><td><a href="' . esc_url($url) . '"><strong>' . esc_html($nm) . '</strong></a><br><span style="color:#646970;font-size:12px">' . esc_html($u->user_email) . '</span></td>';
		echo '<td><span class="dsua-plan ' . $pc . '">' . esc_html(ucfirst($r['plan'])) . '</span></td>';
		echo '<td class="n">' . esc_html(number_format_i18n($r['credits'])) . '</td>';
		echo $num($r['free']) . $num($r['pro']) . $num($r['img']) . $num($r['video']) . $num($r['audio']) . '<td class="n" style="background:#f6f7f7">' . ( $r['ppt'] ? '<strong>' . esc_html($r['ppt']) . '</strong> generated' : '<span class="dsua-z">0</span>' ) . '</td><td class="n" style="background:#f6f7f7">' . ( $r['pptdl'] ? '<strong>' . esc_html($r['pptdl']) . '</strong> downloaded' : '<span class="dsua-z">0</span>' ) . '</td>';
		echo '<td>' . esc_html($r['joined'] ? wp_date('d M Y', strtotime($r['joined'])) : '—') . '</td>';
		echo '<td>' . esc_html($r['last'] ? wp_date('d M Y, H:i', strtotime($r['last'])) : '—') . '</td></tr>';
	}
	echo '</tbody></table>';
	if ( $pages > 1 ) {
		echo '<p>';
		for ( $i = 1; $i <= $pages; $i++ ) {
			$l = add_query_arg(array('page' => 'ds-user-activity', 'ua_period' => $per, 'ua_sort' => $sort, 'ua_plan' => $pf, 'ua_pg' => $i), admin_url('admin.php'));
			echo $i === $pg ? '<strong style="margin-right:8px">' . $i . '</strong>' : '<a style="margin-right:8px" href="' . esc_url($l) . '">' . $i . '</a>';
		}
		echo '</p>';
	}
	echo '<p style="color:#646970"><em>Downloads are counted per day from ' . esc_html(wp_date('d M Y', (int) get_option('ds_ua_since', time()))) . ' (all plans, including unlimited ones). AI counts come from each user\'s saved history; failed jobs and single added slides are not counted.</em></p>';
}
add_action('admin_init', function () { if ( ! get_option('ds_ua_since') ) add_option('ds_ua_since', time(), '', 'no'); });
