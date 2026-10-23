<?php
/**
 * Code Snippets export — DS PPT Free Plan — 2 free presentations a day (no AI, 5 slides, 3 designs, PPTX / PNG / JPG / PDF, not saved)
 *
 * Snippet ID : 968
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-ppt, free-plan
 * Modified   : 2026-10-16 (V41.2: free designs picked from a list of all templates)
 * Load order : 191 of 257
 */
/**
 * DS PPT Free Plan — free presentations without AI
 * Run: everywhere
 *
 * Gives FREE users a small number of free presentations (default 2) with these rules:
 *   - Preserve My Text only (their own text or an uploaded file). "Update with AI" and "Browse Templates" are locked.
 *   - No AI at all: the text is split into slides by fixed rules (headings, "Slide 1:" markers, bullets, paragraphs).
 *   - At most 5 slides per presentation (default).
 *   - Not saved to the account, no share link.
 *   - Download as PowerPoint, PNG, JPG or PDF (which formats: Settings > Free Presentations; the picture and PDF
 *     formats need the "DS PPT Export" snippet).
 *   - Three designs only: Classic, Glassline and Cobalt Bold (ids in Settings > Free Presentations). The other
 *     design templates are locked on the home screen and refused by the server.
 *   - In the editor: no new slides (Add Slide, chart slide, diagram slide), and a chart keeps its type and shape
 *     (pie stays pie) - its numbers, labels and unit can still be edited.
 *
 * WHO IS A FREE USER: no live paid plan AND fewer credits than the cheapest presentation costs.
 * Everybody else (any paid plan, or enough credits) never sees or runs any of this — nothing changes for them.
 *
 * Settings: WP Admin > Settings > Free Presentations (on/off, how many, slides, reset a user).
 * Admin test: open the AI Presentation page with  ?ds_free_preview=1  to see it as a free user (admins only),
 *             ?ds_free_preview=0  to leave. The test uses the admin's own counter (Reset button in the test bar).
 *
 * NEW SNIPPET — edits no other snippet. Deactivate to remove everything.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! defined('DS_FREEPPT_V1') ) {
define('DS_FREEPPT_V1', 1);

/* ------------------------------------------------------------------ settings */
function ds_fp_settings() {
	$o = get_option('ds_free_ppt', array());
	if ( ! is_array($o) ) $o = array();
	return array(
		'enabled'    => isset($o['enabled']) ? ( empty($o['enabled']) ? 0 : 1 ) : 1,
		'max_gens'   => isset($o['max_gens']) ? max(0, min(50, (int) $o['max_gens'])) : 2,
		'max_slides' => isset($o['max_slides']) ? max(1, min(20, (int) $o['max_slides'])) : 5,
		'formats'    => ds_fp_formats( isset($o['formats']) ? $o['formats'] : null ),
		'templates'  => ds_fp_templates( isset($o['templates']) ? $o['templates'] : null ),
	);
}
/* download formats a free presentation may use */
function ds_fp_formats( $raw ) {
	$all = array('pptx', 'png', 'jpg', 'pdf');
	if ( ! is_array($raw) ) return $all;                       /* never saved: all four */
	$out = array_values(array_intersect($all, array_map('strval', $raw)));
	return $out ? $out : array('pdf');                         /* at least one way to get the deck out */
}
/* design templates a free presentation may use ('classic' = the standard look, always allowed) */
function ds_fp_templates( $raw ) {
	if ( is_string($raw) ) $raw = preg_split('/[\s,]+/', $raw);
	if ( ! is_array($raw) ) $raw = array('classic', 'glassline', 'cobalt');
	$out = array('classic', 'classicx');   /* Classic and the editable Classic are always allowed */
	foreach ( $raw as $id ) {
		$id = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $id));
		if ( $id !== '' && ! in_array($id, $out, true) ) $out[] = $id;
	}
	return (array) apply_filters('ds_free_ppt_templates', $out);
}
function ds_fp_format_words( $formats ) {
	$names = array('pptx' => 'PowerPoint', 'png' => 'PNG', 'jpg' => 'JPG', 'pdf' => 'PDF');
	$w = array();
	foreach ( (array) $formats as $f ) if ( isset($names[$f]) ) $w[] = $names[$f];
	if ( count($w) <= 1 ) return $w ? $w[0] : 'PDF';
	$last = array_pop($w);
	return implode(', ', $w) . ' or ' . $last;
}
function ds_fp_credit_floor() {
	$floor = 5;
	if ( function_exists('ds_ppt_credit_cost') ) {
		$c = (int) ds_ppt_credit_cost(1, 'preserve');
		if ( $c > 0 ) $floor = $c;
	}
	return (int) apply_filters('ds_free_ppt_credit_floor', $floor);
}
function ds_fp_preview_on() {
	return ! empty($_COOKIE['ds_free_preview']) && current_user_can('manage_options');
}
/* free user = no live paid plan and not enough credits for the cheapest presentation */
function ds_fp_is_free( $uid ) {
	$uid = (int) $uid;
	if ( ! $uid ) return false;
	$s = ds_fp_settings();
	if ( ! $s['enabled'] ) return false;
	if ( user_can($uid, 'manage_options') ) return ( $uid === get_current_user_id() ) && ds_fp_preview_on();
	$credits = function_exists('ds_valid_credits') ? (int) ds_valid_credits($uid) : ( function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true) );   /* expired credits do not count */
	if ( $credits >= ds_fp_credit_floor() ) return false;
	$plan = function_exists('ds_get_user_plan') ? (string) ds_get_user_plan($uid) : 'free';
	if ( $plan === '' || $plan === 'free' ) return true;
	$status = function_exists('ds_get_user_plan_status') ? ds_get_user_plan_status($uid) : 'active';
	if ( $status === 'active' ) return false;
	$end = function_exists('ds_get_subscription_end') ? ds_get_subscription_end($uid) : '';
	if ( $end && strtotime($end . ' 23:59:59') >= current_time('timestamp') ) return false;
	return true;
}
function ds_fp_used( $uid ) { return max(0, (int) get_user_meta($uid, 'ds_free_ppt_used', true)); }
function ds_fp_left( $uid ) { $s = ds_fp_settings(); return max(0, $s['max_gens'] - ds_fp_used($uid)); }

/* admin test switch: ?ds_free_preview=1 / 0 */
add_action('init', function () {
	if ( ! isset($_GET['ds_free_preview']) || ! is_user_logged_in() || ! current_user_can('manage_options') ) return;
	$on = $_GET['ds_free_preview'] === '1';
	if ( ! headers_sent() ) setcookie('ds_free_preview', $on ? '1' : '', $on ? time() + 6 * HOUR_IN_SECONDS : time() - 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
	if ( $on ) $_COOKIE['ds_free_preview'] = '1'; else unset($_COOKIE['ds_free_preview']);
}, 1);

/* ------------------------------------------------------------------ helpers */
function ds_fp_text( $s, $max ) {
	$s = wp_strip_all_tags((string) $s);
	$s = trim(preg_replace('/\s+/u', ' ', $s));
	return function_exists('mb_substr') ? mb_substr($s, 0, $max) : substr($s, 0, $max);
}
function ds_fp_clean_slides( $raw, $max_slides ) {
	$types = array('title','bullet','two_column','three_column','image_text','feature_grid','stats','timeline','quote','five_point','six_point','seven_point','eight_point');
	$out = array();
	if ( ! is_array($raw) ) return $out;
	foreach ( $raw as $s ) {
		if ( ! is_array($s) ) continue;
		if ( count($out) >= $max_slides ) break;
		$type = isset($s['type']) ? (string) $s['type'] : 'bullet';
		if ( ! in_array($type, $types, true) ) $type = 'bullet';
		$content = array();
		if ( ! empty($s['content']) && is_array($s['content']) ) {
			foreach ( $s['content'] as $c ) {
				if ( ! is_scalar($c) ) continue;
				$c = ds_fp_text($c, 600);
				if ( $c === '' ) continue;
				$content[] = $c;
				if ( count($content) >= 16 ) break;
			}
		}
		$icons = array();
		if ( ! empty($s['icons']) && is_array($s['icons']) ) {
			foreach ( $s['icons'] as $ic ) { $ic = is_scalar($ic) ? (string) $ic : ''; $icons[] = preg_match('/^[a-z0-9:_-]{1,60}$/i', $ic) ? $ic : ''; if ( count($icons) >= 16 ) break; }
		}
		$emojis = array();
		if ( ! empty($s['emojis']) && is_array($s['emojis']) ) {
			foreach ( $s['emojis'] as $em ) { $emojis[] = is_scalar($em) ? ds_fp_text($em, 8) : ''; if ( count($emojis) >= 16 ) break; }
		}
		$icon = isset($s['icon']) && is_scalar($s['icon']) && preg_match('/^[a-z0-9:_-]{1,60}$/i', (string) $s['icon']) ? (string) $s['icon'] : '';
		$title = ds_fp_text(isset($s['title']) ? $s['title'] : '', 200);
		$sub   = ds_fp_text(isset($s['subtitle']) ? $s['subtitle'] : '', 300);
		if ( $title === '' && ! $content ) continue;
		$out[] = array(
			'type'        => $type,
			'title'       => $title,
			'subtitle'    => $sub,
			'content'     => $content,
			'image_query' => '',
			'icon'        => $icon,
			'icon_emoji'  => isset($s['icon_emoji']) && is_scalar($s['icon_emoji']) ? ds_fp_text($s['icon_emoji'], 8) : '',
			'icons'       => $icons,
			'emojis'      => $emojis,
		);
	}
	return $out;
}
/* decks the account already owns (saved drafts / paid generations) stay fully usable */
function ds_fp_known_id( $uid, $id ) {
	$id = (string) $id;
	if ( $id === '' || strpos($id, 'free_') === 0 ) return false;
	$drafts = get_user_meta($uid, 'ds_ppt_drafts', true);
	if ( is_array($drafts) && isset($drafts[$id]) ) return true;
	$jobs = get_user_meta($uid, 'ds_ppt_jobs', true);
	if ( is_array($jobs) ) foreach ( $jobs as $j ) { if ( is_array($j) && isset($j['id']) && $j['id'] === $id ) return true; }
	return false;
}
function ds_fp_known_title( $uid, $title ) {
	$title = trim((string) $title);
	if ( $title === '' ) return false;
	$drafts = get_user_meta($uid, 'ds_ppt_drafts', true);
	if ( is_array($drafts) ) foreach ( $drafts as $d ) { if ( is_array($d) && isset($d['title']) && trim((string) $d['title']) === $title ) return true; }
	return false;
}
function ds_fp_block( $msg ) {
	return new WP_REST_Response(array('success' => false, 'error' => $msg, 'message' => $msg, 'free_plan' => true), 200);
}

/* ------------------------------------------------------------------ REST */
add_action('rest_api_init', function () {
	$logged = function () { return is_user_logged_in(); };
	register_rest_route('ds/v1', '/free-ppt/status', array('methods' => 'GET', 'callback' => 'ds_fp_rest_status', 'permission_callback' => $logged));
	register_rest_route('ds/v1', '/free-ppt/start', array('methods' => 'POST', 'callback' => 'ds_fp_rest_start', 'permission_callback' => $logged));
	register_rest_route('ds/v1', '/free-ppt/reset', array('methods' => 'POST', 'callback' => 'ds_fp_rest_reset', 'permission_callback' => function () { return current_user_can('manage_options'); }));
});
function ds_fp_state( $uid ) {
	$s = ds_fp_settings();
	return array(
		'free'       => ds_fp_is_free($uid),
		'used'       => ds_fp_used($uid),
		'left'       => ds_fp_left($uid),
		'max_gens'   => $s['max_gens'],
		'max_slides' => $s['max_slides'],
		'formats'    => $s['formats'],
		'templates'  => $s['templates'],
		'preview'    => ds_fp_preview_on(),
	);
}
function ds_fp_rest_status() {
	return rest_ensure_response(array_merge(array('success' => true), ds_fp_state(get_current_user_id())));
}
function ds_fp_rest_reset() {
	$uid = get_current_user_id();
	delete_user_meta($uid, 'ds_free_ppt_used');
	delete_user_meta($uid, 'ds_free_ppt_log');
	return rest_ensure_response(array_merge(array('success' => true), ds_fp_state($uid)));
}
/* Claim one free presentation. The slides were already split in the browser by fixed rules — no AI is called here. */
function ds_fp_rest_start( WP_REST_Request $req ) {
	$uid = get_current_user_id();
	$s   = ds_fp_settings();
	if ( ! ds_fp_is_free($uid) ) {
		return rest_ensure_response(array('success' => false, 'error' => 'not_free', 'message' => 'Your account is not on the free plan any more. Please reload the page.'));
	}
	if ( ! current_user_can('manage_options') && ! get_user_meta($uid, 'ds_email_verified', true) ) {
		return rest_ensure_response(array('success' => false, 'error' => 'verify', 'message' => 'Please verify your email before creating a presentation.'));
	}
	if ( ds_fp_used($uid) >= $s['max_gens'] ) {
		return rest_ensure_response(array('success' => false, 'error' => 'limit', 'left' => 0,
			'message' => 'You have used your ' . $s['max_gens'] . ' free presentation' . ( $s['max_gens'] === 1 ? '' : 's' ) . '. Upgrade to create more.'));
	}
	if ( get_transient('ds_fp_lock_' . $uid) ) {
		return rest_ensure_response(array('success' => false, 'error' => 'busy', 'message' => 'Please wait a few seconds and try again.'));
	}
	$ppt = $req->get_param('ppt_data');
	$slides = ds_fp_clean_slides( is_array($ppt) && isset($ppt['slides']) ? $ppt['slides'] : array(), $s['max_slides'] );
	if ( ! $slides ) {
		return rest_ensure_response(array('success' => false, 'error' => 'empty', 'message' => 'No slides could be made from this text. Add a few lines and try again.'));
	}
	set_transient('ds_fp_lock_' . $uid, 1, 8);

	$title = ds_fp_text( is_array($ppt) && isset($ppt['title']) ? $ppt['title'] : '', 200 );
	if ( $title === '' ) $title = $slides[0]['title'] !== '' ? $slides[0]['title'] : 'Presentation';

	$used = ds_fp_used($uid) + 1;
	update_user_meta($uid, 'ds_free_ppt_used', $used);
	$log = get_user_meta($uid, 'ds_free_ppt_log', true);
	if ( ! is_array($log) ) $log = array();
	array_unshift($log, array('t' => time(), 'title' => $title, 'slides' => count($slides), 'file' => $req->get_param('source') ? 1 : 0));
	update_user_meta($uid, 'ds_free_ppt_log', array_slice($log, 0, 20));

	/* shows up in the existing admin activity views, at 0 credits */
	$gen = get_user_meta($uid, 'ds_ppt_generations', true);
	if ( ! is_array($gen) ) $gen = array();
	array_unshift($gen, array('date' => current_time('mysql'), 'prompt' => 'Free plan - Preserve My Text (no AI)', 'credits' => 0, 'num_slides' => count($slides)));
	update_user_meta($uid, 'ds_ppt_generations', array_slice($gen, 0, 100));

	return rest_ensure_response(array(
		'success'  => true,
		'ppt_id'   => 'free_' . time() . '_' . wp_generate_password(6, false, false),
		'ppt_data' => array('title' => $title, 'slides' => $slides, 'ds_free' => 1),
		'used'     => $used,
		'left'     => max(0, $s['max_gens'] - $used),
	));
}

/* ------------------------------------------------------------------ gate: free users only */
add_filter('rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( $result !== null ) return $result;
	$route = $request->get_route();
	if ( strpos($route, '/ds/v1/') !== 0 ) return $result;

	$ai = array('/ds/v1/generate-ppt', '/ds/v1/ai-preview', '/ds/v1/ai-confirm', '/ds/v1/doc-ai-preview', '/ds/v1/doc-preserve-preview', '/ds/v1/doc-preserve-confirm', '/ds/v1/preserve-preview', '/ds/v1/preserve-confirm', '/ds/v1/preserve-ai-rearrange', '/ds/v1/doc-ocr');
	$is_ai    = in_array($route, $ai, true);
	$is_save  = ( $route === '/ds/v1/save-ppt' );
	$is_share = ( $route === '/ds/v1/create-share-link' );
	$is_build = ( $route === '/ds/v1/build-pptx' || $route === '/ds/v1/build-pptx-v2' );
	if ( ! $is_ai && ! $is_save && ! $is_share && ! $is_build ) return $result;

	$uid = get_current_user_id();
	if ( ! $uid || ! ds_fp_is_free($uid) ) return $result;           /* everybody else: untouched */

	if ( $is_ai ) {
		return ds_fp_block('AI features are part of the paid plans. On the free plan you can build a presentation from your own text with Preserve My Text.');
	}
	$used = ds_fp_used($uid);
	$flag = (bool) $request->get_param('ds_free');
	if ( $is_save ) {
		$id   = (string) $request->get_param('ppt_id');
		$data = $request->get_param('ppt_data');
		if ( strpos($id, 'free_') === 0 || ( is_array($data) && ! empty($data['ds_free']) ) ) $flag = true;
		if ( $flag || ( $used > 0 && ! ds_fp_known_id($uid, $id) ) ) {
			return ds_fp_block('Free presentations are not saved. Download it as ' . ds_fp_format_words(ds_fp_settings()['formats']) . ', or upgrade to save and reopen your presentations.');
		}
		return $result;
	}
	/* share + PPTX of a free presentation (decks this account already has saved are untouched) */
	if ( $flag || ( $used > 0 && ! ds_fp_known_title($uid, $request->get_param('title')) ) ) {
		$s     = ds_fp_settings();
		$words = ds_fp_format_words($s['formats']);
		if ( $is_share ) {
			return ds_fp_block('Share links are part of the paid plans. Free presentations can be downloaded as ' . $words . '.');
		}
		if ( ! in_array('pptx', $s['formats'], true) ) {
			return ds_fp_block('PowerPoint (.pptx) download is part of the paid plans. Free presentations can be downloaded as ' . $words . '.');
		}
		/* PowerPoint is allowed: only with a free design and within the free slide limit */
		$tpl = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $request->get_param('tpl')));
		if ( $tpl !== '' && ! in_array($tpl, $s['templates'], true) ) {
			return ds_fp_block('This design is part of the paid plans. Free presentations can use the designs marked Free.');
		}
		$deck = $request->get_param('ppt_data');
		$n    = is_array($deck) && isset($deck['slides']) && is_array($deck['slides']) ? count($deck['slides']) : 0;
		$alt  = $request->get_param('slides');
		if ( is_array($alt) ) $n = max($n, count($alt));
		if ( $n > $s['max_slides'] ) {
			return ds_fp_block('Free presentations have up to ' . $s['max_slides'] . ' slides. Remove the extra slides, or upgrade for longer presentations.');
		}
	}
	return $result;
}, -50, 3);   /* runs before every other gate: the template engine answers some of these routes itself at priority 5 */

/* ------------------------------------------------------------------ settings page */
add_action('admin_menu', function () {
	add_options_page('Free Presentations', 'Free Presentations', 'manage_options', 'ds-free-ppt', 'ds_fp_settings_page');
});
function ds_fp_settings_page() {
	if ( ! current_user_can('manage_options') ) return;
	$note = '';
	if ( isset($_POST['ds_fp_save']) && check_admin_referer('ds_fp_save') ) {
		update_option('ds_free_ppt', array(
			'enabled'    => empty($_POST['enabled']) ? 0 : 1,
			'max_gens'   => max(0, min(50, (int) ( $_POST['max_gens'] ?? 2 ))),
			'max_slides' => max(1, min(20, (int) ( $_POST['max_slides'] ?? 5 ))),
			'formats'    => ds_fp_formats( isset($_POST['formats']) && is_array($_POST['formats']) ? array_map('sanitize_key', wp_unslash($_POST['formats'])) : array() ),
			'templates'  => ds_fp_templates( isset($_POST['tpl_pick']) && is_array($_POST['tpl_pick']) ? implode(',', array_map('sanitize_key', wp_unslash($_POST['tpl_pick']))) : sanitize_text_field(wp_unslash($_POST['templates'] ?? '')) ),
		));
		$note = 'Saved.';
	}
	if ( isset($_POST['ds_fp_reset']) && check_admin_referer('ds_fp_save') ) {
		$u = get_user_by('email', sanitize_email(wp_unslash($_POST['reset_email'] ?? '')));
		if ( $u ) { delete_user_meta($u->ID, 'ds_free_ppt_used'); delete_user_meta($u->ID, 'ds_free_ppt_log'); $note = 'Free presentations reset for ' . $u->user_email . '.'; }
		else $note = 'No user with that email address.';
	}
	$s = ds_fp_settings();
	global $wpdb;
	$users = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'ds_free_ppt_used' AND meta_value > 0");
	$total = (int) $wpdb->get_var("SELECT COALESCE(SUM(meta_value),0) FROM {$wpdb->usermeta} WHERE meta_key = 'ds_free_ppt_used'");
	?>
	<div class="wrap">
		<h1>Free Presentations</h1>
		<p>Free users (no live paid plan and fewer than <?php echo (int) ds_fp_credit_floor(); ?> credits) can build a few presentations from their own text, without AI. Paid users are never affected.</p>
		<?php if ( $note ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($note); ?></p></div><?php endif; ?>
		<form method="post">
			<?php wp_nonce_field('ds_fp_save'); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Status</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked($s['enabled'], 1); ?>> Free presentations are switched on</label></td></tr>
				<tr><th scope="row"><label for="ds_fp_g">Free presentations per account</label></th><td><input type="number" id="ds_fp_g" name="max_gens" min="0" max="50" value="<?php echo esc_attr($s['max_gens']); ?>" class="small-text"> <span class="description">in total, not per day</span></td></tr>
				<tr><th scope="row"><label for="ds_fp_s">Slides per free presentation</label></th><td><input type="number" id="ds_fp_s" name="max_slides" min="1" max="20" value="<?php echo esc_attr($s['max_slides']); ?>" class="small-text"></td></tr>
				<tr><th scope="row">Download formats</th><td>
					<?php foreach ( array('pptx' => 'PowerPoint (.pptx)', 'png' => 'PNG images', 'jpg' => 'JPG images', 'pdf' => 'PDF') as $fk => $fl ) : ?>
					<label style="margin-right:16px"><input type="checkbox" name="formats[]" value="<?php echo esc_attr($fk); ?>" <?php checked(in_array($fk, $s['formats'], true)); ?>> <?php echo esc_html($fl); ?></label>
					<?php endforeach; ?>
					<p class="description">What a free presentation can be downloaded as. The others show "Upgrade".</p></td></tr>
				<tr><th scope="row">Designs for free presentations</th><td>
<?php
/* every design template the site has right now (new or updated templates show here by themselves) */
$fp_reg = isset($GLOBALS['ds_tpl_registry']) && is_array($GLOBALS['ds_tpl_registry']) ? $GLOBALS['ds_tpl_registry'] : array();
$fp_list = array();
foreach ( $fp_reg as $fp_id => $fp_cfg ) {
	$fp_id = sanitize_key($fp_id); if ( $fp_id === '' || $fp_id === 'classic' || $fp_id === 'classicx' ) continue;
	$fp_list[$fp_id] = is_array($fp_cfg) && ! empty($fp_cfg['name']) ? (string) $fp_cfg['name'] : $fp_id;
}
foreach ( $s['templates'] as $fp_id ) { if ( $fp_id !== 'classic' && $fp_id !== 'classicx' && ! isset($fp_list[$fp_id]) ) $fp_list[$fp_id] = $fp_id . ' (not installed)'; }
natcasesort($fp_list);
$fp_on = 0; foreach ( $fp_list as $fp_id => $fp_n ) if ( in_array($fp_id, $s['templates'], true) ) $fp_on++;
?>
<input type="hidden" name="tpl_pick[]" value="classic">
<div id="ds-fp-tpl">
<p style="margin:0 0 8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
<input type="search" id="ds-fp-q" placeholder="Search designs..." style="min-width:220px">
<button type="button" class="button" data-fp="all">Select all</button>
<button type="button" class="button" data-fp="none">Clear</button>
<button type="button" class="button" data-fp="on">Show free only</button>
<span id="ds-fp-n" style="color:#646970"><?php echo (int) $fp_on; ?> of <?php echo count($fp_list); ?> designs free</span>
</p>
<div style="max-height:380px;overflow:auto;border:1px solid #dcdcde;border-radius:6px;background:#fff;padding:10px 12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:4px 16px">
<label style="color:#646970"><input type="checkbox" checked disabled> Classic <small>(always free)</small></label>
<?php foreach ( $fp_list as $fp_id => $fp_n ) : ?>
<label class="ds-fp-it" data-s="<?php echo esc_attr(strtolower($fp_n . ' ' . $fp_id)); ?>"><input type="checkbox" name="tpl_pick[]" value="<?php echo esc_attr($fp_id); ?>" <?php checked(in_array($fp_id, $s['templates'], true)); ?>> <?php echo esc_html($fp_n); ?> <small style="color:#8c8f94"><?php echo esc_html($fp_id); ?></small></label>
<?php endforeach; ?>
</div>
</div>
<p class="description">Tick the designs free users can pick, then Save Changes. Every design that is not ticked is locked for free users. New design templates appear in this list by themselves.</p>
<script>
(function(){
	var box = document.getElementById('ds-fp-tpl'); if (!box) return;
	var q = document.getElementById('ds-fp-q'), n = document.getElementById('ds-fp-n'), onlyOn = false;
	var items = function(){ return Array.prototype.slice.call(box.querySelectorAll('.ds-fp-it')); };
	function count(){ var a = items(), c = a.filter(function(l){ return l.querySelector('input').checked; }).length; n.textContent = c + ' of ' + a.length + ' designs free'; }
	function filter(){ var v = (q.value || '').toLowerCase().trim(); items().forEach(function(l){ var ok = (!v || l.getAttribute('data-s').indexOf(v) >= 0) && (!onlyOn || l.querySelector('input').checked); l.style.display = ok ? '' : 'none'; }); }
	q.addEventListener('input', filter);
	q.addEventListener('keydown', function(e){ if (e.key === 'Enter') e.preventDefault(); });
	box.addEventListener('change', count);
	box.addEventListener('click', function(e){
		var b = e.target.closest('[data-fp]'); if (!b) return;
		var a = b.getAttribute('data-fp');
		if (a === 'on') { onlyOn = !onlyOn; b.textContent = onlyOn ? 'Show all' : 'Show free only'; filter(); return; }
		items().forEach(function(l){ if (l.style.display !== 'none') l.querySelector('input').checked = (a === 'all'); });
		count();
	});
})();
</script>
</td></tr>
			</table>
			<p class="submit"><button type="submit" name="ds_fp_save" value="1" class="button button-primary">Save Changes</button></p>
			<h2>Give a user their free presentations back</h2>
			<p><input type="email" name="reset_email" placeholder="user@example.com" class="regular-text"> <button type="submit" name="ds_fp_reset" value="1" class="button">Reset</button></p>
		</form>
		<p><strong><?php echo number_format_i18n($total); ?></strong> free presentation(s) made so far by <strong><?php echo number_format_i18n($users); ?></strong> account(s).</p>
		<p class="description">Test it as an admin: open the AI Presentation page with <code>?ds_free_preview=1</code> (and <code>?ds_free_preview=0</code> to leave).</p>
	</div>
	<?php
}

/* ------------------------------------------------------------------ page side (printed for free users only) */
add_action('wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a($post, 'WP_Post') ) return;
	if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
	$uid = get_current_user_id();
	if ( ! ds_fp_is_free($uid) ) return;
	$st  = ds_fp_state($uid);
	$cfg = array(
		'free'      => true,
		'used'      => $st['used'],
		'left'      => $st['left'],
		'maxGens'   => $st['max_gens'],
		'maxSlides' => $st['max_slides'],
		'formats'   => $st['formats'],
		'templates' => $st['templates'],
		'preview'   => $st['preview'],
		'start'     => esc_url_raw(rest_url('ds/v1/free-ppt/start')),
		'reset'     => esc_url_raw(rest_url('ds/v1/free-ppt/reset')),
		'nonce'     => wp_create_nonce('wp_rest'),
		'pricing'   => esc_url_raw(home_url('/pricing/')),
		'exit'      => esc_url_raw(add_query_arg('ds_free_preview', '0', get_permalink($post))),
	);
	echo '<script id="ds-free-ppt-cfg">window.DS_FP_CFG=' . wp_json_encode($cfg) . ';</script>' . "\n";
	?>
<style id="ds-free-ppt-css">
#dsfp-banner{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:0 0 16px;padding:12px 14px;border-radius:12px;border:1px solid rgba(251,191,36,.35);background:rgba(251,191,36,.07);color:#f5e7c4;font:500 13px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;text-transform:none;letter-spacing:normal;}
#dsfp-banner b{color:#fbbf24;font-weight:800;}
#dsfp-banner .dsfp-txt{flex:1 1 260px;min-width:0;}
#dsfp-banner .dsfp-sub{display:block;color:#b9c0cf;font-size:12px;margin-top:2px;}
#dsfp-banner a,#dsfp-banner button{flex:0 0 auto;border-radius:9px;padding:8px 14px;font:800 12.5px/1 inherit;font-family:inherit;cursor:pointer;text-decoration:none;text-transform:none;letter-spacing:normal;}
#dsfp-banner a{background:#f59e0b;color:#1a1204;border:0;}
#dsfp-banner button{background:transparent;color:#e6ecf7;border:1px solid #3a465f;}
body.dsfp .dsfp-locked{position:relative;opacity:.55;cursor:not-allowed!important;}
body.dsfp .dsfp-locked *{cursor:not-allowed!important;}
body.dsfp .dsfp-locked::after{content:"\1F512  Paid plans";position:absolute;top:8px;right:8px;padding:3px 8px;border-radius:999px;background:#1a1204;border:1px solid rgba(251,191,36,.5);color:#fbbf24;font:800 10.5px/1.3 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;letter-spacing:.02em;text-transform:none;}
body.dsfp #dsp-costamt{display:none!important;}
body.dsfp #dsfp-cost{font:700 13px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#6ee7b7;}
body.dsfp #dsdi-ibox,body.dsfp #dssl-ibox-slot{display:none!important;}
body.dsfp-deck #dsp-savebtn,body.dsfp-deck #dsp-tb-sharebtn{display:none!important;}
body.dsfp #dste-grid .dste-card.dsfp-tlock{position:relative;}
body.dsfp #dste-grid .dste-card.dsfp-tlock .dste-th{opacity:.45;filter:grayscale(.6);}
body.dsfp #dste-grid .dste-card.dsfp-tlock::after{content:"\1F512  Paid plans";position:absolute;top:8px;right:8px;z-index:3;padding:3px 8px;border-radius:999px;background:#1a1204;border:1px solid rgba(251,191,36,.5);color:#fbbf24;font:800 10.5px/1.3 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;letter-spacing:.02em;text-transform:none;}
body.dsfp-deck .dsch-tab:not(.on),body.dsfp-deck .dsch-st:not(.on){opacity:.4;cursor:not-allowed!important;}
body.dsfp-deck #dsch-addbtn,body.dsfp-deck #dsdg-addbtn{opacity:.5;}
#dsfp-chip{display:inline-flex;align-items:center;gap:6px;margin-left:12px;padding:4px 10px;border-radius:999px;border:1px solid rgba(251,191,36,.45);color:#fbbf24;font:800 11px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;white-space:nowrap;text-transform:none;letter-spacing:normal;vertical-align:middle;}
.dsfp-ov{position:fixed;inset:0;z-index:2147482500;background:rgba(4,7,13,.78);display:flex;align-items:center;justify-content:center;padding:20px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
.dsfp-card{width:min(94vw,440px);background:#141a26;border:1px solid #2a3550;border-radius:16px;padding:26px 26px 22px;color:#e6ecf7;box-shadow:0 24px 70px rgba(0,0,0,.6);}
.dsfp-card h3{margin:0 0 8px;font-size:19px;font-weight:800;color:#f8fafc;line-height:1.3;}
.dsfp-card p{margin:0 0 14px;font-size:14px;line-height:1.55;color:#b9c0cf;}
.dsfp-card ul{margin:0 0 18px;padding:0;list-style:none;}
.dsfp-card li{position:relative;padding:5px 0 5px 22px;font-size:13.5px;line-height:1.45;color:#dbe4f3;}
.dsfp-card li::before{content:"";position:absolute;left:4px;top:12px;width:7px;height:7px;border-radius:50%;background:#60a5fa;}
.dsfp-card li.dsfp-warn::before{background:#fbbf24;}
.dsfp-acts{display:flex;gap:10px;}
.dsfp-acts button,.dsfp-acts a{flex:1;display:block;text-align:center;padding:12px;border-radius:10px;font:800 13.5px/1 inherit;font-family:inherit;cursor:pointer;text-decoration:none;border:0;text-transform:none;letter-spacing:normal;}
.dsfp-acts .dsfp-sec{background:#1e2740;color:#dbe4f3;}
.dsfp-acts .dsfp-pri{background:#2563eb;color:#fff;}
.dsfp-acts .dsfp-up{background:#f59e0b;color:#1a1204;}
.dsfp-sp{width:36px;height:36px;border:3px solid #2a3550;border-top-color:#60a5fa;border-radius:50%;margin:0 auto 14px;animation:dsfpspin .8s linear infinite;}
@keyframes dsfpspin{to{transform:rotate(360deg);}}
#dsfp-test{position:fixed;left:14px;bottom:14px;z-index:2147482400;display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;background:#3b0764;border:1px solid #a855f7;color:#f3e8ff;font:600 12px/1.3 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
#dsfp-test button{background:#581c87;border:1px solid #a855f7;color:#fff;border-radius:7px;padding:5px 9px;font:700 11.5px/1 inherit;font-family:inherit;cursor:pointer;text-transform:none;letter-spacing:normal;}
</style>
<script id="ds-free-ppt-js">
(function(){
	if (window.DSFP) return;
	var CFG = window.DS_FP_CFG || {};
	if (!CFG.free) return;
	var DSFP = window.DSFP = {left: CFG.left | 0, used: CFG.used | 0, exported: false};
	var MAX = CFG.maxSlides || 5, GENS = CFG.maxGens || 2, running = false;
	var FORMATS = CFG.formats && CFG.formats.length ? CFG.formats : ['pdf'];
	var TPLS = CFG.templates && CFG.templates.length ? CFG.templates : ['classic'];
	var FNAMES = {pptx: 'PowerPoint', png: 'PNG', jpg: 'JPG', pdf: 'PDF'};
	function fmtWords(){
		var w = FORMATS.map(function(k){ return FNAMES[k]; }).filter(Boolean);
		if (w.length <= 1) return w[0] || 'PDF';
		var last = w.pop(); return w.join(', ') + ' or ' + last;
	}
	function tplOk(id){ return !id || TPLS.indexOf(String(id)) >= 0; }
	function tplNames(){
		var X = window.DSTE, n = TPLS.map(function(id){ return id === 'classic' ? 'Classic' : (X && X.reg && X.reg[id] && X.reg[id].name) || id; });
		if (n.length <= 1) return n[0] || 'Classic';
		var last = n.pop(); return n.join(', ') + ' or ' + last;
	}
	function deckNow(){ return (window.S && S.data && S.data.slides && S.data.slides.length) ? S.data : ((window.G && G.pptData) || null); }
	DSFP.formats = FORMATS; DSFP.templates = TPLS; DSFP.fmtWords = fmtWords;
	/* asked by the Download menu: may a free presentation be downloaded as this format? */
	DSFP.canExport = function(k){ return FORMATS.indexOf(String(k)) >= 0; };
	/* asked just before any download of a free presentation: '' = fine, otherwise the reason it is refused */
	DSFP.exportBlock = function(){
		var d = deckNow(); if (!d || !d.slides) return '';
		if (d.slides.length > MAX) return 'Free presentations have up to ' + plural(MAX, 'slide') + '. Remove the extra slides, or upgrade for longer presentations.';
		if (d.tpl && !tplOk(d.tpl)) return 'This design is part of the paid plans. Free presentations use the ' + tplNames() + ' design.';
		return '';
	};

	function $(id){ return document.getElementById(id); }
	function val(id){ var e = $(id); return e ? String(e.value || '').trim() : ''; }
	function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
	function clean(s){ return String(s == null ? '' : s).replace(/ /g, ' ').replace(/[​﻿]/g, '').replace(/\s+/g, ' ').trim(); }
	function toast(m, t){ if (typeof window.dspToast === 'function') window.dspToast(m, t || 'success', 4500); }
	function plural(n, w){ return n + ' ' + w + (n === 1 ? '' : 's'); }

	DSFP.deckIsFree = function(){
		try {
			var g = window.G && G.pptData, s = window.S && S.data;
			/* a free presentation stays free until another saved one is opened — even when the editor swaps in a copy of the deck */
			if (DSFP.active) { if (g && !g.ds_free) g.ds_free = 1; if (s && !s.ds_free) s.ds_free = 1; return true; }
			if (s && s.slides && s.slides.length) {
				if (s.ds_free) return true;
				/* the editor sometimes works on a copy of the deck: same deck, so the mark is carried over */
				if (g && g.ds_free && g.slides && g.slides.length === s.slides.length && String((g.slides[0] || {}).title) === String((s.slides[0] || {}).title)) { s.ds_free = 1; return true; }
				return false;
			}
			return !!(g && g.ds_free);
		} catch(e){ return false; }
	};
	function editorOpen(){ var fs = $('dsp-editor-fullscreen'); return !!(fs && fs.classList.contains('dsp-open')); }

	/* ---------- small dialogs ---------- */
	function overlay(html){
		var o = document.createElement('div'); o.className = 'dsfp-ov'; o.innerHTML = '<div class="dsfp-card">' + html + '</div>';
		document.body.appendChild(o);
		return o;
	}
	DSFP.upgrade = function(msg){
		var old = $('dsfp-up'); if (old) old.remove();
		var o = overlay('<h3>&#128274; Available on paid plans</h3><p>' + esc(msg || 'This feature is part of the paid plans.') + '</p><div class="dsfp-acts"><button type="button" class="dsfp-sec">Maybe later</button><a class="dsfp-up" href="' + esc(CFG.pricing || '/pricing/') + '">See plans</a></div>');
		o.id = 'dsfp-up';
		o.querySelector('.dsfp-sec').onclick = function(){ o.remove(); };
		o.addEventListener('click', function(e){ if (e.target === o) o.remove(); });
	};
	function loader(msg){
		var o = overlay('<div class="dsfp-sp"></div><p id="dsfp-lmsg" style="text-align:center;margin:0;color:#e6ecf7;font-weight:700;">' + esc(msg) + '</p>');
		return {msg: function(m){ var x = $('dsfp-lmsg'); if (x) x.textContent = m; }, done: function(){ o.remove(); }};
	}

	/* ---------- rule-based splitter: text -> slides (no AI) ---------- */
	function blocksOf(text){
		var lines = String(text).replace(/\r/g, '').split('\n'), out = [], prevBlank = true, seenAny = false;
		function nextLine(i){ for (var j = i + 1; j < lines.length; j++) if (lines[j].trim()) return lines[j].trim(); return ''; }
		for (var i = 0; i < lines.length; i++) {
			var l = lines[i].trim(), m;
			if (!l) { prevBlank = true; continue; }
			if ((m = l.match(/^(#{1,4})\s+(.+)$/))) { out.push({k: 'h', lvl: Math.min(3, m[1].length), t: clean(m[2].replace(/[*_]+/g, ''))}); }
			else if ((m = l.match(/^(?:[-*•●▪◦►✓]|\d{1,2}[.)])\s+(.+)$/))) { out.push({k: 'li', t: clean(m[1].replace(/\*\*/g, ''))}); }
			else {
				var words = l.split(/\s+/).length, nx = nextLine(i);
				var bare = l.replace(/\*\*/g, '');
				var head = false;
				if (words <= 9 && bare.length <= 70 && nx) {
					if (/:$/.test(bare) && prevBlank) head = true;                                   /* "Our Services:" on its own line */
					else if (!/[.!?,;:]$/.test(bare) && (prevBlank || !seenAny) && bare.indexOf(':') < 0) head = true; /* short line after a gap */
				}
				if (head) out.push({k: 'h', lvl: seenAny ? 2 : 1, t: clean(bare.replace(/:$/, ''))});
				else if (words >= 26) out.push({k: 'p', t: clean(bare)});
				else out.push({k: 'li', t: clean(bare)});
			}
			prevBlank = false; seenAny = true;
		}
		/* a short line straight under the title is the cover's subtitle, not a slide of its own */
		if (out.length > 2 && out[0].k === 'h' && out[1].k === 'h' && out[2].k === 'h') out[1] = {k: 'sub', t: out[1].t};
		return out;
	}
	function firstWords(t){   /* a title for text that has no heading: its first sentence, or its first words */
		var m = String(t).match(/^[^.!?]{8,80}[.!?]/);
		if (m) return clean(m[0].replace(/[.!?]$/, ''));
		var w = String(t).split(/\s+/);
		return w.length <= 9 ? clean(t) : clean(w.slice(0, 8).join(' ')) + '\u2026';
	}
	function simpleDeck(text){   /* last resort: the site's own Preserve parser */
		var s = window.dspPreserveParse ? window.dspPreserveParse(text) : [];
		return s || [];
	}
	function splitText(text){
		var t = String(text).replace(/\r/g, '');
		var DI = window.DS_DOCIMPORT;
		var marks = t.match(/(?:^|\n)[ \t]*Slide\s+\d+\s*[:\-–—.]/gi) || [];
		if (marks.length >= 2 && window.dspPreserveParse) {
			/* the author already wrote "Slide 1: ..." — follow it exactly */
			return simpleDeck(t.replace(/(^|\n)([ \t]*Slide\s+\d+)\s*[\-–—.]\s*/gi, '$1$2: '));
		}
		if (!DI || !DI.sections || !DI.heuristic || !DI.toDeck) return simpleDeck(t);
		var blocks = blocksOf(t);
		if (!blocks.length) return [];
		var h = blocks.filter(function(b){ return b.k === 'h'; })[0];
		var title = blocks[0].k === 'h' ? blocks[0].t : firstWords(blocks[0].t);   /* a heading further down is a section, not the deck title */
		var secs = DI.sections(blocks, title);
		return DI.toDeck(DI.heuristic(secs, 60, title), secs);   /* trimmed to the free limit by the caller */
	}
	DSFP.split = splitText;

	/* ---------- Build (replaces the paid flows for free users) ---------- */
	async function run(){
		if (running) return;
		if (DSFP.left <= 0) { DSFP.upgrade('You have used your ' + plural(GENS, 'free presentation') + '. Upgrade to create more, with AI writing, every design and saving.'); return; }
		var DI = window.DS_DOCIMPORT, a = DI && DI.attached ? DI.attached() : null, prompt = val('dsp-prompt');
		if (!a && !prompt) { var pe = $('dsp-prompt'); if (pe) pe.focus(); toast('Paste your text or upload a file first.', 'error'); return; }
		running = true;
		var L = loader(a ? 'Reading your file…' : 'Arranging your text into slides…'), all;
		try {
			if (a) {
				await a.promise;
				if (a.status === 'error') throw new Error(a.msg);
				all = DI.toDeck(DI.heuristic(a.secs, 60, a.title), a.secs);
			} else all = splitText(prompt);
			all = (all || []).filter(function(s){ return s && (clean(s.title) || (s.content && s.content.length)); });
			if (!all.length) throw new Error('No slides could be made from this text. Add a few lines and try again.');
		} catch(err) { L.done(); running = false; toast((err && err.message) || 'Could not read your content.', 'error'); return; }
		L.done();
		var slides = all.slice(0, MAX), cut = all.length - slides.length;
		confirmBuild(slides, cut, a);
	}
	function confirmBuild(slides, cut, a){
		var after = DSFP.left - 1;
		var o = overlay('<h3>Build your free presentation?</h3><ul>'
			+ '<li>' + plural(slides.length, 'slide') + ' from your own words — nothing is rewritten.</li>'
			+ (cut > 0 ? '<li class="dsfp-warn">Your content makes ' + (slides.length + cut) + ' slides. The free plan builds the first ' + MAX + '.</li>' : '')
			+ '<li>Uses 1 of your ' + plural(GENS, 'free presentation') + ' (' + after + ' left after this).</li>'
			+ '<li>Download as ' + fmtWords() + '. It is not saved to your account.</li></ul>'
			+ '<div class="dsfp-acts"><button type="button" class="dsfp-sec">Cancel</button><button type="button" class="dsfp-pri">Build presentation</button></div>');
		o.querySelector('.dsfp-sec').onclick = function(){ o.remove(); running = false; };
		o.querySelector('.dsfp-pri').onclick = function(){ o.remove(); start(slides, a); };
	}
	function start(slides, a){
		var L = loader('Building your slides…');
		var imgs = slides.map(function(s){ return s.image_data || ''; });
		var title = clean((slides[0] && slides[0].title) || (a && a.title) || 'Presentation');
		var send = {title: title, slides: slides.map(function(s){ var o = {}; for (var k in s) if (k !== 'image_data' && k !== '_img') o[k] = s[k]; return o; })};
		fetch(CFG.start, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE || CFG.nonce}, body: JSON.stringify({ppt_data: send, source: a ? 1 : 0})})
		.then(function(r){ return r.json(); })
		.then(function(d){
			L.done(); running = false;
			if (!d || !d.success || !d.ppt_data) {
				if (d && d.error === 'limit') { DSFP.left = 0; home(); DSFP.upgrade(d.message); return; }
				toast((d && d.message) || 'Could not build the presentation. Please try again.', 'error'); return;
			}
			DSFP.left = d.left | 0; DSFP.used = d.used | 0; DSFP.exported = false;
			open(d.ppt_data, imgs, a);
		})
		.catch(function(){ L.done(); running = false; toast('Network error. Please try again.', 'error'); });
	}
	function tagTpl(fin){
		var X = window.DSTE; if (!X || !X.reg) return;
		var pk = X.pick && X.pick !== 'classic' && X.reg[X.pick] ? X.pick : null;
		if (pk && !tplOk(pk)) pk = null;   /* a locked design is never applied to a free presentation */
		if (!pk) { delete fin.tpl; return; }
		fin.tpl = pk; fin.tplTheme = X.homeTheme || '';
	}
	function open(fin, imgs, a){
		var G = window.G, design = val('dsp-design') || 'navy-pro', done = false;
		(fin.slides || []).forEach(function(s, i){ if (imgs[i]) s.image_data = imgs[i]; });
		fin.ds_free = 1; DSFP.active = true;
		fin.layout = val('dsp-layout') || '16:9';
		tagTpl(fin);
		function finish(){
			if (done) return; done = true;
			if (window.dspLiveGenHide) window.dspLiveGenHide();
			G.pptId = '';                          /* no id: the background autosave never runs for a free presentation */
			G.pptData = fin; G.pptTitle = fin.title || 'Presentation';
			if (window.buildEditor) window.buildEditor(fin);
			if (window.dspSyncPreserveData) window.dspSyncPreserveData(fin);
			if (a && window.DS_DOCIMPORT && window.DS_DOCIMPORT.detach) window.DS_DOCIMPORT.detach();
			home();
		}
		if (window.dspLiveGenShow && window.dspLiveGenStream) {
			try {
				window.dspLiveGenShow(fin.title || 'Presentation', fin.slides.length, design);
				var bt = $('dsp-lg-banner-txt'); if (bt) bt.innerHTML = '<b style="color:#111;">Building Your Slides:</b> Turning your own content into a deck.';
				setTimeout(finish, 15000);
				window.dspLiveGenStream(fin, design, finish);
			} catch(e) { finish(); }
		} else finish();
	}
	DSFP.reopen = function(){
		var G = window.G; if (!G || !G.pptData || !G.pptData.ds_free) return;
		if (window.buildEditor) window.buildEditor(G.pptData);
		if (window.dspSyncPreserveData) window.dspSyncPreserveData(G.pptData);
	};

	/* the editor's own "add a slide" functions do nothing for a free presentation, however they are reached */
	function wrapAdd(){
		['dspAddSlide', 'dspAddSlideGenerate'].forEach(function(n){
			var f = window[n]; if (typeof f !== 'function' || f.__dsfpAdd) return;
			var w = function(){
				if (DSFP.deckIsFree()) { DSFP.upgrade('Adding slides is part of the paid plans. Free presentations have up to ' + plural(MAX, 'slide') + '.'); return; }
				return f.apply(this, arguments);
			};
			for (var k in f) { try { w[k] = f[k]; } catch(e){} }
			w.__dsfpAdd = 1; window[n] = w;
		});
		/* the PowerPoint builder: refuse early, with the reason, instead of a server error */
		var bld = window.dspBuild;
		if (typeof bld === 'function' && !bld.__dsfpB) {
			var wb = function(){
				if (DSFP.deckIsFree()) {
					if (!DSFP.canExport('pptx')) { DSFP.upgrade('PowerPoint (.pptx) download is part of the paid plans. Free presentations can be downloaded as ' + fmtWords() + '.'); return; }
					var why = DSFP.exportBlock(); if (why) { DSFP.upgrade(why); return; }
				}
				return bld.apply(this, arguments);
			};
			for (var k2 in bld) { try { wb[k2] = bld[k2]; } catch(e){} }
			wb.__dsfpB = 1; window.dspBuild = wb;
		}
	}
	function wrapGen(){
		var f = window.dspGen; if (typeof f !== 'function' || f.__dsfp) return;
		var w = function(){ run(); };
		w.__dsfp = 1; w.__dsdi = 1;   /* __dsdi: the document-import snippet must not wrap this again */
		window.dspGen = w;
	}

	/* ---------- home screen ---------- */
	function home(){
		document.body.classList.add('dsfp');
		wrapGen(); wrapAdd();
		if (window.G && G.mode !== 'preserve' && typeof window.dspMode === 'function' && $('dsp-m-ai') && $('dsp-m-keep') && $('dsp-genbtn-lbl')) { try { window.dspMode('preserve'); } catch(e){} }
		['dsp-m-ai', 'ds30-mode-card'].forEach(function(id){ var e = $(id); if (e && !e.classList.contains('dsfp-locked')) e.classList.add('dsfp-locked'); });
		/* slide count: 1 .. MAX */
		var pill = $('dsp-pill-slides');
		if (pill) {
			var opts = pill.querySelectorAll('.dsp-pill-opt');
			for (var i = 0; i < opts.length; i++) { var hide = i >= MAX; if ((opts[i].style.display === 'none') !== hide) opts[i].style.display = hide ? 'none' : ''; }
			var cur = parseInt(val('dsp-slides'), 10) || 0;
			if (cur > MAX && typeof window.dspPillSet === 'function') window.dspPillSet('slides', MAX, MAX + ' cards');
		}
		/* designs: only the free ones can be picked */
		lockTemplates();
		/* translation needs AI: the language picker is hidden */
		var lg = $('dsp-pill-lang');
		if (lg) { var wrap = lg.closest ? (lg.closest('.dssl-f') || lg) : lg; if (wrap.style.display !== 'none') wrap.style.display = 'none'; }
		/* banner */
		var host = $('dsp-mode-row');
		if (host && host.parentNode) {
			var b = $('dsfp-banner');
			if (!b) { b = document.createElement('div'); b.id = 'dsfp-banner'; host.parentNode.insertBefore(b, host); }
			var key = DSFP.left + '|' + (window.G && G.pptData && G.pptData.ds_free ? 1 : 0);
			if (b.getAttribute('data-k') !== key) {
				b.setAttribute('data-k', key);
				var back = window.G && G.pptData && G.pptData.ds_free ? '<button type="button" id="dsfp-back">&#8617; Back to my presentation</button>' : '';
				b.innerHTML = '<span class="dsfp-txt">' + (DSFP.left > 0
					? '<b>Free plan:</b> ' + DSFP.left + ' of ' + plural(GENS, 'free presentation') + ' left.<span class="dsfp-sub">Built from your own text or file · up to ' + plural(MAX, 'slide') + ' · ' + fmtWords() + ' download · not saved</span>'
					: '<b>Free plan:</b> you have used your ' + plural(GENS, 'free presentation') + '.<span class="dsfp-sub">Upgrade for AI writing, more slides, every design and saving.</span>')
					+ '</span>' + back + '<a href="' + esc(CFG.pricing || '/pricing/') + '">Upgrade</a>';
				var bk = $('dsfp-back'); if (bk) bk.onclick = function(){ DSFP.reopen(); };
			}
		}
		/* price line */
		var amt = $('dsp-costamt');
		if (amt && amt.parentNode) {
			var c = $('dsfp-cost');
			if (!c) { c = document.createElement('div'); c.id = 'dsfp-cost'; amt.parentNode.insertBefore(c, amt.nextSibling); }
			var txt = DSFP.left > 0 ? 'Free · ' + DSFP.left + ' of ' + GENS + ' left' : 'Free presentations used';
			if (c.textContent !== txt) c.textContent = txt;
		}
		/* editor of a free presentation */
		var free = DSFP.deckIsFree() && editorOpen();
		document.body.classList.toggle('dsfp-deck', free);
		var chip = $('dsfp-chip'), ttl = $('dsp-etitle');
		if (free && ttl && !chip && ttl.parentNode) { chip = document.createElement('span'); chip.id = 'dsfp-chip'; chip.textContent = 'FREE · not saved · ' + fmtWords() + ' download'; ttl.parentNode.insertBefore(chip, ttl.nextSibling); }
		if (!free && chip) chip.remove();
	}

	/* ---------- designs: lock every template that is not free ---------- */
	function lockTemplates(){
		var g = $('dste-grid'); if (!g) return;
		var cards = g.querySelectorAll('.dste-card'), X = window.DSTE;
		for (var i = 0; i < cards.length; i++) {
			var lock = !tplOk(cards[i].getAttribute('data-id'));
			if (cards[i].classList.contains('dsfp-tlock') !== lock) cards[i].classList.toggle('dsfp-tlock', lock);
		}
		/* a locked design picked earlier (before the plan ended, or in another tab) goes back to Classic */
		if (X && X.pick && !tplOk(X.pick)) {
			var c = g.querySelector('.dste-card[data-id="classic"]');
			if (c) c.click();
			else { X.pick = 'classic'; try { sessionStorage.setItem('dsteTplPick', 'classic'); } catch(e){} }
		}
	}

	/* locked cards and editor actions */
	document.addEventListener('click', function(e){
		var t = e.target; if (!t || !t.closest) return;
		var tc = t.closest('#dste-grid .dste-card');
		if (tc && !tplOk(tc.getAttribute('data-id'))) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('This design is part of the paid plans. On the free plan you can use the ' + tplNames() + ' design.'); return; }
		if (t.closest('#dsp-m-ai')) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('Update with AI is part of the paid plans. On the free plan you can build a presentation from your own text with Preserve My Text.'); return; }
		if (t.closest('#ds30-mode-card')) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('Browse Templates is part of the paid plans. On the free plan you can build a presentation from your own text with Preserve My Text.'); return; }
		if (!DSFP.deckIsFree() || !editorOpen()) return;
		var oc = t.closest('[onclick]'), code = oc ? (oc.getAttribute('onclick') || '') : '';
		if (/dspAddSlide\(|dspAddSlideGenerate\(/.test(code) || t.closest('#dsch-addbtn, #dsdg-addbtn')) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('Adding slides is part of the paid plans. Free presentations have up to ' + plural(MAX, 'slide') + '.'); return; }
		/* charts: the numbers and labels can be edited, the chart type and shape cannot */
		var ct = t.closest('.dsch-tab, .dsch-st');
		if (ct) {
			if (!ct.classList.contains('on')) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('Changing the chart type is part of the paid plans. On the free plan you can edit the numbers, labels and unit of this chart.'); }
			return;
		}
		if (/vpImgOptionAI\(|vpDoGen\(|dspImgModalGenerate\(/.test(code)) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('AI images are part of the paid plans. You can upload your own picture instead.'); return; }
		if (/dspSaveDraft\(|dspSharePPT\(/.test(code)) { e.preventDefault(); e.stopImmediatePropagation(); DSFP.upgrade('Saving and share links are part of the paid plans. Free presentations can be downloaded as ' + fmtWords() + '.'); return; }
	}, true);

	/* mark requests that come from a free presentation, so the server can refuse PPTX / share for it */
	var _fetch = window.fetch;
	if (_fetch) window.fetch = function(url, opts){
		try {
			var us = typeof url === 'string' ? url : (url && url.url) || '';
			if (/\/ds\/v1\/load-ppt(\?|$)/.test(us)) DSFP.active = false;   /* a saved presentation is being opened */
			/* some editor actions save on their own: for a free presentation that is quietly skipped (the server would refuse it anyway) */
			if (/\/ds\/v1\/save-ppt(\?|$)/.test(us) && DSFP.deckIsFree()) return Promise.resolve(new Response(JSON.stringify({success: true, ppt_id: '', free_plan: true}), {status: 200, headers: {'Content-Type': 'application/json'}}));
			if (/\/ds\/v1\/(build-pptx|build-pptx-v2|create-share-link)(\?|$)/.test(us) && opts && typeof opts.body === 'string' && DSFP.deckIsFree()) {
				var b = JSON.parse(opts.body); b.ds_free = 1;
				var o2 = {}; for (var k in opts) o2[k] = opts[k]; o2.body = JSON.stringify(b); opts = o2;
			}
		} catch(err){}
		return _fetch.call(this, url, opts);
	};

	document.addEventListener('ds:ppt-exported', function(){ DSFP.exported = true; });
	window.addEventListener('beforeunload', function(e){
		if (window.G && G.pptData && G.pptData.ds_free && !DSFP.exported) { e.preventDefault(); e.returnValue = ''; return ''; }
	});

	/* admin test bar */
	function testBar(){
		if (!CFG.preview || $('dsfp-test')) return;
		var t = document.createElement('div'); t.id = 'dsfp-test';
		t.innerHTML = '<span>Free-plan test mode (admins only)</span><button type="button" data-a="reset">Reset my count</button><button type="button" data-a="exit">Exit test</button>';
		document.body.appendChild(t);
		t.addEventListener('click', function(e){
			var b = e.target.closest('button'); if (!b) return;
			if (b.getAttribute('data-a') === 'exit') { DSFP.exported = true; window.location.href = CFG.exit; return; }
			fetch(CFG.reset, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE || CFG.nonce}, body: '{}'})
				.then(function(r){ return r.json(); }).then(function(d){ if (d && d.success) { DSFP.left = d.left | 0; DSFP.used = 0; home(); toast('Free count reset', 'success'); } });
		});
	}

	setInterval(home, 700);
	if (document.readyState !== 'loading') { home(); testBar(); } else document.addEventListener('DOMContentLoaded', function(){ home(); testBar(); });
})();
</script>
	<?php
}, 1400);

} // DS_FREEPPT_V1
