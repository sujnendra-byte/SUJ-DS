<?php
/**
 * Code Snippets export — DS Free Plan Switch - free presentations and free images: Limited / No limit / Stopped
 *
 * Snippet ID : 1182
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-free-switch
 * Modified   : 2026-10-11 11:05:47
 * Load order : 347 of 347
 */
/**
 * DS Free Plan Switch - one place to choose what free users get
 * Run: Everywhere
 *
 * WP Admin > Settings > Free Plan Switch. Two choices, each with three positions:
 *
 *   Free presentations            Free AI images
 *   - Limited   (as it is today)  - Limited   (as it is today: 2 a day)
 *   - No limit                    - No limit
 *   - Stopped   (no free ones)    - Stopped   (no free ones)
 *
 * "Limited" changes nothing at all. The other positions work on top of the existing snippets without editing them:
 *   Presentations ("DS PPT Free Plan"):  No limit = the counter is never used up (slides per presentation, download
 *     formats and free designs stay as set in Settings > Free Presentations).  Stopped = the free plan is switched off.
 *   Images ("DS AI Image REST" + "DS Free Tier Guard"):  No limit = no 2-a-day cap per account or per network and no
 *     waiting hours between images (free models only, the short cooldown and the daily $ budget in
 *     Settings > Free Tier Limits stay as a safety net).  Stopped = free accounts cannot generate images.
 *
 * The wording follows the choice everywhere it is printed: pricing page (Free card, compare table, FAQ, footer line),
 * home page, AI Studio (notes, lock message, upgrade pop-up), the free-plan banner on the AI Presentation page and the
 * support chat.
 *
 * NEW SNIPPET - nothing existing is changed. Undo: deactivate this snippet (everything is "Limited" again).
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_fsw_get') ) {

function ds_fsw_get() {
	$o = get_option('ds_free_switch', array());
	if ( ! is_array($o) ) $o = array();
	$ok = array('limited', 'unlimited', 'off');
	return array(
		'ppt' => isset($o['ppt']) && in_array($o['ppt'], $ok, true) ? $o['ppt'] : 'limited',
		'img' => isset($o['img']) && in_array($o['img'], $ok, true) ? $o['img'] : 'limited',
	);
}
function ds_fsw_mode( $what ) { $m = ds_fsw_get(); return $m[ $what ]; }
/* our changes never touch wp-admin screens, so the other settings pages always show (and save) their own real values */
function ds_fsw_live() { return ! is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ); }

/* the real "Free Presentations" settings, read without our own filter */
function ds_fsw_ppt_real() {
	$o = get_option('ds_free_ppt', array());
	if ( ! is_array($o) ) $o = array();
	if ( isset($o['_fsw_real']) && is_array($o['_fsw_real']) ) $o = $o['_fsw_real'];
	return array(
		'enabled'    => isset($o['enabled']) ? ( empty($o['enabled']) ? 0 : 1 ) : 1,
		'max_gens'   => isset($o['max_gens']) ? max(0, min(50, (int) $o['max_gens'])) : 2,
		'max_slides' => isset($o['max_slides']) ? max(1, min(20, (int) $o['max_slides'])) : 5,
	);
}
function ds_fsw_ppt_daily() { return function_exists('ds_fpd_today'); }   /* "DS PPT Free Plan - daily reset" is active */

/* ================================================================== presentations */
function ds_fsw_ppt_option( $o ) {
	if ( ! ds_fsw_live() ) return $o;
	$mode = ds_fsw_mode('ppt');
	if ( $mode === 'limited' ) return $o;
	if ( ! is_array($o) ) $o = array();
	if ( isset($o['_fsw_real']) ) return $o;
	$real = $o;
	if ( $mode === 'off' ) $o['enabled'] = 0;
	else { $o['enabled'] = 1; if ( ! isset($o['max_gens']) || (int) $o['max_gens'] < 1 ) $o['max_gens'] = 2; }
	$o['_fsw_real'] = $real;
	return $o;
}
add_filter('option_ds_free_ppt', 'ds_fsw_ppt_option', 20);
add_filter('default_option_ds_free_ppt', 'ds_fsw_ppt_option', 20);

/* No limit: the "used" counter always reads 0, so there is always a free presentation left */
add_filter('get_user_metadata', function ( $value, $uid, $key, $single ) {
	if ( $key !== 'ds_free_ppt_used' || ! ds_fsw_live() || ds_fsw_mode('ppt') !== 'unlimited' ) return $value;
	return $single ? '0' : array('0');
}, 99, 4);

/* ================================================================== images */
function ds_fsw_img_is_free( $uid ) {
	if ( ! $uid || user_can($uid, 'manage_options') ) return false;
	$u = get_userdata($uid);
	if ( $u && in_array('premium_member', (array) $u->roles, true) ) return false;
	if ( function_exists('ds_user_is_free_tier') ) return (bool) ds_user_is_free_tier($uid);
	return function_exists('ds_get_user_plan') ? ( ds_get_user_plan($uid) === 'free' ) : true;
}
function ds_fsw_freegen_option( $o ) {
	if ( empty($GLOBALS['ds_fsw_img_open']) ) return $o;
	if ( ! is_array($o) ) $o = array();
	$o['daily_max'] = 0;      /* 0 = no cap per account */
	$o['gap_hours'] = 0;      /* no waiting hours between two images */
	return $o;
}
add_filter('rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( $result !== null ) return $result;
	if ( strpos( (string) $request->get_route(), '/ds/v1/generate-image' ) === false ) return $result;
	$mode = ds_fsw_mode('img');
	if ( $mode === 'limited' ) return $result;
	$uid = get_current_user_id();
	if ( ! $uid ) return $result;

	if ( $mode === 'off' ) {
		if ( ! ds_fsw_img_is_free($uid) ) return $result;
		return new WP_REST_Response(array(
			'error'   => true,
			'status'  => 429,
			'message' => 'Free image generation is not available. Upgrade to a paid plan or add credits to generate images.',
		), 429);
	}

	/* No limit: lift the three counting limits for this request only */
	$GLOBALS['ds_fsw_img_open'] = 1;
	add_filter('option_ds_freegen_settings', 'ds_fsw_freegen_option', 20);
	add_filter('default_option_ds_freegen_settings', 'ds_fsw_freegen_option', 20);
	$zero = function () { return 0; };
	$ips = array();
	if ( ! empty($_SERVER['HTTP_CF_CONNECTING_IP']) ) $ips[] = (string) $_SERVER['HTTP_CF_CONNECTING_IP'];
	if ( isset($_SERVER['REMOTE_ADDR']) ) $ips[] = (string) $_SERVER['REMOTE_ADDR'];
	$ips[] = '';
	foreach ( array_unique($ips) as $ip ) {
		foreach ( array( date('Y-m-d'), current_time('Y-m-d') ) as $day ) add_filter('pre_transient_ds_ipday_' . md5($ip . $day), $zero, 20);
	}
	add_filter('get_user_metadata', function ( $value, $object_id, $key, $single ) {
		if ( ! is_string($key) || strpos($key, 'ds_free_daily_') !== 0 ) return $value;
		return $single ? '0' : array('0');
	}, 99, 4);
	return $result;
}, 1, 3);

/* ================================================================== wording */
function ds_fsw_words() {
	$m = ds_fsw_get();
	$p = ds_fsw_ppt_real();
	$per = ds_fsw_ppt_daily() ? ' a day' : '';
	$n = (int) $p['max_gens'];
	$ppt_on = ( $m['ppt'] === 'unlimited' ) || ( $m['ppt'] === 'limited' && $p['enabled'] && $n > 0 );
	$ppt_line = '';
	if ( $m['ppt'] === 'unlimited' ) $ppt_line = 'Unlimited free presentations (your own text, up to ' . (int) $p['max_slides'] . ' slides)';
	elseif ( $ppt_on ) $ppt_line = $n . ' free presentation' . ( $n === 1 ? '' : 's' ) . $per . ' (your own text, up to ' . (int) $p['max_slides'] . ' slides)';
	return array('img' => $m['img'], 'ppt' => $m['ppt'], 'ppt_on' => $ppt_on, 'ppt_line' => $ppt_line, 'n' => $n, 'per' => $per, 'slides' => (int) $p['max_slides']);
}

/* the printed pages (pricing, home, AI Studio): plain text outside scripts, changed just before the page is sent */
function ds_fsw_page( $html ) {
	if ( ! is_string($html) || strlen($html) < 500 ) return $html;
	try {
		$w = ds_fsw_words();
		$tick = "\u{2713}"; $cross = "\u{2717}";
		if ( $w['img'] !== 'limited' && ( strpos($html, 'images/') !== false || strpos($html, 'image generations per day') !== false ) ) {
			$un = ( $w['img'] === 'unlimited' );
			/* pricing: the big number on the Free card */
			$r = preg_replace('#>2</div>(\s*<div[^>]*>)images/ Per day</div>#', $un ? '>Unlimited</div>$1free images</div>' : '>0</div>$1free images</div>', $html);
			if ( is_string($r) ) $html = $r;
			/* pricing: the Free card list */
			if ( $un ) $html = str_replace('</span>2 free AI images/day</li>', '</span>Unlimited free AI images</li>', $html);
			else {
				$r = preg_replace('#<span style="color:\#10b981;([^"]*)">' . $tick . '</span>2 free AI images/day</li>#u', '<span style="color:#ef4444;$1">' . $cross . '</span>Free AI images</li>', $html);
				if ( is_string($r) ) $html = $r;
			}
			$pairs = $un ? array(
				'>2 images/per day<' => '>Unlimited images<',
				'Every new account gets 2 free AI image generations per day using' => 'Every account can generate free AI images, with no daily limit, using',
				'Start free with 2 images/day. No credit card needed.' => 'Start free with unlimited images. No credit card needed.',
				'2 AI images/day free + marketplace access' => 'Unlimited free AI images + marketplace access',
				'>2 AI images/day</strong> + free marketplace access!' => '>Unlimited AI images</strong> + free marketplace access!',
				'Free plan: 2 images/day free on Cloudflare FLUX-1-Schnell' => 'Free plan: unlimited free images on Cloudflare FLUX-1-Schnell',
			) : array(
				'>2 images/per day<' => '>-<',
				'Every new account gets 2 free AI image generations per day using the Cloudflare FLUX model or Cloudflare FLUX.2 Klein 9B model. No credit card required. Upgrade anytime to unlock more models and credits.' => 'Free AI image generation is not available at the moment. Choose a plan or add credits to generate images with every model.',
				'Start free with 2 images/day. No credit card needed.' => 'Create a free account and pick the plan that fits you.',
				'2 AI images/day free + marketplace access' => 'Free marketplace access',
				'>2 AI images/day</strong> + free marketplace access!' => '>Marketplace access</strong> is free!',
				'Free plan: 2 images/day free on Cloudflare FLUX-1-Schnell' => 'AI images are part of the paid plans - upgrade to generate',
			);
			$html = str_replace(array_keys($pairs), array_values($pairs), $html);
		}
		/* pricing: the Free card line about presentations */
		if ( $w['ppt_on'] && $w['ppt_line'] !== '' && strpos($html, '</span>AI Presentations</li>') !== false ) {
			$r = preg_replace('#<span style="color:rgba\(255,255,255,\.15\);([^"]*)">-</span>AI Presentations</li>#', '<span style="color:#10b981;$1">' . $tick . '</span>' . esc_html($w['ppt_line']) . '</li>', $html, 1);
			if ( is_string($r) ) $html = $r;
		}
	} catch ( \Throwable $e ) {}
	return $html;
}
add_action('template_redirect', function () {
	if ( is_admin() || is_feed() || ! empty($_GET['ds_view']) ) return;
	if ( ( defined('REST_REQUEST') && REST_REQUEST ) || ( defined('DOING_AJAX') && DOING_AJAX ) || ( defined('DOING_CRON') && DOING_CRON ) ) return;
	if ( ( isset($_SERVER['REQUEST_METHOD']) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET' ) !== 'GET' ) return;
	$w = ds_fsw_words();
	if ( $w['img'] === 'limited' && ! $w['ppt_on'] ) return;      /* nothing to say differently */
	ob_start('ds_fsw_page');
}, 1);

/* the support chat: what it knows, and its ready-made answer about the free plan */
function ds_fsw_chat_text( $s, $json ) {
	$w = ds_fsw_words();
	if ( $w['img'] === 'unlimited' ) {
		$s = str_replace('FREE PLAN (no credit card required): 2 AI images per day, using ONLY', 'FREE PLAN (no credit card required): unlimited free AI images (no daily cap), using ONLY', $s);
		$s = str_replace('On the Free plan you can generate 2 AI images per day, using only', 'On the Free plan you can generate AI images with no daily limit, using only', $s);
	} elseif ( $w['img'] === 'off' ) {
		$s = str_replace('FREE PLAN (no credit card required): 2 AI images per day, using ONLY the Cloudflare and Cloudflare FLUX.2 Klein 9B models.', 'FREE PLAN (no credit card required): NO free AI image generation - images need a paid plan or credits.', $s);
		$s = str_replace('On the Free plan you can generate 2 AI images per day, using only the Cloudflare and Cloudflare FLUX.2 Klein 9B models (the other 5 models need a paid plan).', 'The Free plan does not include AI image generation - images need a paid plan or credits.', $s);
	}
	if ( $w['ppt_on'] && $w['ppt_line'] !== '' ) {
		$line = $w['ppt_line'] . ', built without AI writing. No AI Presentations';
		$s = str_replace('No AI Presentations, no AI Video', $json ? trim( wp_json_encode($line), '"' ) . ', no AI Video' : $line . ', no AI Video', $s);
	}
	return $s;
}
add_filter('rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( $result === null && (string) $request->get_route() === '/ds/v1/suji-chat' ) {
		add_filter('http_request_args', function ( $args ) {
			if ( ! empty($args['body']) && is_string($args['body']) && strpos($args['body'], 'FREE PLAN') !== false ) $args['body'] = ds_fsw_chat_text($args['body'], true);
			return $args;
		}, 20);
	}
	return $result;
}, 2, 3);
add_filter('rest_post_dispatch', function ( $response, $server, $request ) {
	if ( (string) $request->get_route() !== '/ds/v1/suji-chat' || ! ( $response instanceof WP_REST_Response ) ) return $response;
	$d = $response->get_data();
	if ( is_array($d) && isset($d['reply']) && is_string($d['reply']) ) { $d['reply'] = ds_fsw_chat_text($d['reply'], false); $response->set_data($d); }
	return $response;
}, 20, 3);

/* wording that the page builds itself (lock message, pop-up, free-plan banner) */
add_action('wp_footer', function () {
	if ( is_admin() ) return;
	$w = ds_fsw_words();
	if ( $w['img'] === 'limited' && $w['ppt'] !== 'unlimited' ) return;
	$uid = get_current_user_id();
	$cfg = array(
		'img'     => $w['img'],
		'ppt'     => $w['ppt'],
		'imgFree' => $uid ? ds_fsw_img_is_free($uid) : false,
	);
	?>
<script id="ds-free-switch-js">
(function(){
  var C = <?php echo wp_json_encode($cfg); ?>;
  if (window.__dsFreeSwitch) return; window.__dsFreeSwitch = 1;
  function each(root, fn){
    if (!root) return;
    var tw = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null), n, list = [];
    while ((n = tw.nextNode())) list.push(n);
    list.forEach(function(t){ var a = t.nodeValue, b = fn(a); if (b !== a) t.nodeValue = b; });
  }
  /* ---------- images ---------- */
  function imgLockWords(){
    var b = document.getElementById('dsGB');
    if (b && /Daily limit reached/.test(b.textContent)) each(b, function(s){ return s.replace(/Daily limit reached \(2\/2\)/, 'Not on the free plan'); });
    var k = document.getElementById('ds-free-lock');
    if (k) {
      each(k, function(s){
        return s.replace(/\s*2\/2 free images used today/, ' Free image generation is not available')
                .replace(/Resets at midnight\s*.\s*upgrade to generate with all models/, 'Upgrade to generate images with all models')
                .replace(/^Resets in $/, '');
      });
      var t = document.getElementById('ds-free-timer'); if (t && t.parentNode && t.parentNode.style.display !== 'none') t.parentNode.style.display = 'none';
    }
  }
  function imgSetup(){
    if (C.img === 'limited' || typeof window.dsFreeHardLock !== 'function' || window.dsFreeHardLock.__fsw) return;
    var orig = window.dsFreeHardLock, fn;
    if (C.img === 'unlimited') fn = function(){};                         /* a "please wait" answer must not lock the button for the day */
    else fn = function(){ try { orig.apply(this, arguments); } catch (e) {} imgLockWords(); };
    fn.__fsw = 1; window.dsFreeHardLock = fn;
    if (C.img === 'off' && C.imgFree && document.getElementById('dsGB')) fn();
  }
  /* ---------- presentations: "2 of 2 left" makes no sense without a limit ---------- */
  function pptWords(){
    if (C.ppt !== 'unlimited') return;
    each(document.querySelector('#dsfp-banner .dsfp-txt'), function(s){
      return s.replace(/^\s*\d+ of \d+ free presentations? left( today)?\.\s*$/, ' unlimited free presentations.');
    });
    /* The free-plan snippet rewrites #dsfp-cost every 0.7 s, so changing its text made it blink.
       Hide that line and show our own "Free · unlimited" line next to it instead. */
    var c = document.getElementById('dsfp-cost');
    if (c && c.parentNode) {
      if (c.style.display !== 'none') c.style.display = 'none';
      var u = document.getElementById('dsfsw-cost');
      if (!u) {
        u = document.createElement('div'); u.id = 'dsfsw-cost';
        u.style.cssText = 'font:700 13px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#6ee7b7;';
        u.textContent = 'Free ' + String.fromCharCode(183) + ' unlimited';
      }
      if (u.previousSibling !== c) c.parentNode.insertBefore(u, c.nextSibling);
    }
    document.querySelectorAll('.dsfp-ov li, [id^="dsfp"] li').forEach(function(li){
      if (/^Uses 1 of your \d+ free presentations? \(\d+ left after this\)\.$/.test(li.textContent)) li.textContent = 'Free - there is no limit on free presentations.';
    });
  }
  var t = null;
  function run(){ t = null; try { imgSetup(); if (C.img === 'off') imgLockWords(); pptWords(); } catch (e) {} }
  function kick(){ if (!t) t = setTimeout(run, 150); }
  function start(){ run(); try { new MutationObserver(kick).observe(document.body, {childList: true, subtree: true, characterData: true}); } catch (e) { setInterval(run, 1500); } }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
</script>
	<?php
}, 2030);

/* ================================================================== settings page */
add_action('admin_menu', function () {
	add_options_page('Free Plan Switch', 'Free Plan Switch', 'manage_options', 'ds-free-switch', 'ds_fsw_settings_page');
});
function ds_fsw_settings_page() {
	if ( ! current_user_can('manage_options') ) return;
	$note = '';
	if ( isset($_POST['ds_fsw_save']) && check_admin_referer('ds_fsw_save') ) {
		$ok = array('limited', 'unlimited', 'off');
		$ppt = isset($_POST['ppt']) ? sanitize_key( wp_unslash($_POST['ppt']) ) : 'limited';
		$img = isset($_POST['img']) ? sanitize_key( wp_unslash($_POST['img']) ) : 'limited';
		update_option('ds_free_switch', array(
			'ppt' => in_array($ppt, $ok, true) ? $ppt : 'limited',
			'img' => in_array($img, $ok, true) ? $img : 'limited',
		), false);
		$note = 'Saved. The new choice is live now, on the site and in the wording of the pricing page, home page and AI Studio.';
	}
	$m = ds_fsw_get();
	$p = ds_fsw_ppt_real();
	$per = ds_fsw_ppt_daily() ? 'a day' : 'per account';
	$fg = function_exists('ds_freegen_get_settings') ? ds_freegen_get_settings() : array('daily_max' => 2, 'gap_hours' => 6, 'cooldown_seconds' => 30, 'daily_budget_usd' => 5);
	$ppt_now = $p['enabled'] ? ( (int) $p['max_gens'] . ' free presentation' . ( (int) $p['max_gens'] === 1 ? '' : 's' ) . ' ' . $per . ', up to ' . (int) $p['max_slides'] . ' slides each' ) : 'switched off in Settings > Free Presentations';
	$rows = array(
		'ppt' => array('Free presentations', array(
			'limited'   => array('Limited (as it is today)', 'Now: ' . $ppt_now . '. Change the numbers in Settings > Free Presentations.'),
			'unlimited' => array('No limit', 'Free users can make as many free presentations as they like. Everything else stays as set: built from their own text without AI, up to ' . (int) $p['max_slides'] . ' slides, the free designs and download formats.'),
			'off'       => array('Stopped - no free presentations', 'Free users get no free presentations. They are asked to upgrade or add credits, like before the free plan existed.'),
		)),
		'img' => array('Free AI images', array(
			'limited'   => array('Limited (as it is today)', 'Now: 2 free images a day per account and per network, ' . ( (float) $fg['gap_hours'] > 0 ? rtrim( rtrim( number_format( (float) $fg['gap_hours'], 1 ), '0' ), '.' ) . ' hours apart' : 'no waiting time' ) . ', free models only.'),
			'unlimited' => array('No limit', 'No daily cap per account or per network and no waiting hours between images. Still free models only, one image at a time. Safety nets that stay: the ' . (int) $fg['cooldown_seconds'] . '-second cooldown and the daily budget of $' . number_format( (float) $fg['daily_budget_usd'], 2 ) . ' for all free users together (Settings > Free Tier Limits; set the budget to 0 for no budget at all).'),
			'off'       => array('Stopped - no free images', 'Free users cannot generate images. They see an upgrade message. Paid plans and accounts with credits are not affected.'),
		)),
	);
	?>
	<div class="wrap">
		<h1>Free Plan Switch</h1>
		<p>Choose what free users get. Paid users and accounts with credits are never affected. The wording on the pricing page, home page, AI Studio and support chat follows your choice.</p>
		<?php if ( $note ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($note); ?></p></div><?php endif; ?>
		<form method="post">
			<?php wp_nonce_field('ds_fsw_save'); ?>
			<table class="form-table" role="presentation">
			<?php foreach ( $rows as $key => $row ) : ?>
				<tr><th scope="row"><?php echo esc_html($row[0]); ?></th><td><fieldset><legend class="screen-reader-text"><?php echo esc_html($row[0]); ?></legend>
				<?php foreach ( $row[1] as $val => $txt ) : ?>
					<p style="margin:0 0 12px"><label><input type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>" <?php checked($m[ $key ], $val); ?>> <strong><?php echo esc_html($txt[0]); ?></strong></label><br><span class="description" style="margin-left:24px;display:inline-block;max-width:760px"><?php echo esc_html($txt[1]); ?></span></p>
				<?php endforeach; ?>
				</fieldset></td></tr>
			<?php endforeach; ?>
			</table>
			<p class="submit"><button type="submit" name="ds_fsw_save" value="1" class="button button-primary">Save Changes</button></p>
		</form>
		<p class="description">Related screens: <a href="<?php echo esc_url( admin_url('options-general.php?page=ds-free-ppt') ); ?>">Free Presentations</a> (how many, slides, formats, designs) and Settings &gt; Free Tier Limits (image cooldown and daily budget).</p>
	</div>
	<?php
}

} /* function_exists guard */
