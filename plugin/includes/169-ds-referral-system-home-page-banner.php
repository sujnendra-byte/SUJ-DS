<?php
/**
 * Code Snippets export — DS Referral System — Home Page Banner
 *
 * Snippet ID : 907
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-referrals
 * Modified   : 2026-09-30 04:00:15
 * Load order : 169 of 162
 */
/**
 * DS Referral System — Home Page Banner
 * Run: Everywhere
 *
 * Shows a referral banner on the home page (inside the page, not a popup).
 * WP Admin > Referrals > Home Banner: switch on/off, upload a desktop + mobile design,
 * choose where it sits, who sees it and where it links.
 * - Logged-in users: clicking opens the account popup on the "My Referrals" tab (or a custom link).
 * - Visitors: clicking goes to the sign-up page (default /login).
 * - No design uploaded? A built-in banner with your own headline/button text is shown instead.
 * - Also available anywhere as the shortcode [ds_referral_banner].
 * Needs "DS Referral System — Core + Admin". Does not modify any other snippet.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('dsrf_banner_settings') ) {

function dsrf_banner_defaults() {
	return array(
		'enabled'      => 1,
		'img_desktop'  => 0,
		'img_mobile'   => 0,
		'alt'          => 'Invite friends and earn free credits',
		'position'     => 'after_hero',   // after_hero | before_hero | before_footer | shortcode
		'show_to'      => 'all',          // all | logged_in | logged_out
		'link_in'      => 'popup',        // popup | url
		'url_in'       => '',
		'url_out'      => '/login',
		'new_tab'      => 0,
		'dismissible'  => 1,
		'max_width'    => 1280,
		'headline'     => 'Invite friends, earn free credits',
		'subtext'      => '',             // empty = the reward text from Referral settings
		'btn_in'       => 'Get my referral link',
		'btn_out'      => 'Sign up & start earning',
	);
}

function dsrf_banner_settings() {
	$o = get_option('dsrf_banner', array());
	return wp_parse_args(is_array($o) ? $o : array(), dsrf_banner_defaults());
}

function dsrf_banner_url($u) {
	$u = trim((string) $u);
	if ($u === '') return '';
	if (strpos($u, '/') === 0) return home_url($u);
	return $u;
}

/** Keep a site path like /login as-is, otherwise a clean full URL. */
function dsrf_banner_clean_url($u) {
	$u = trim((string) $u);
	if ($u === '') return '';
	if (strpos($u, '/') === 0 && strpos($u, '//') !== 0) return '/' . ltrim(sanitize_text_field($u), '/');
	return esc_url_raw($u);
}

function dsrf_banner_visible_for_user($b) {
	if ($b['show_to'] === 'logged_in' && !is_user_logged_in()) return false;
	if ($b['show_to'] === 'logged_out' && is_user_logged_in()) return false;
	return true;
}

/** Banner markup (used by the home page and the shortcode). */
function dsrf_banner_html($in_shortcode = false) {
	$b = dsrf_banner_settings();
	if (!function_exists('dsrf_settings')) return '';
	$s = dsrf_settings();
	if (!$s['enabled']) return '';
	if (!$in_shortcode && !$b['enabled']) return '';
	if (!dsrf_banner_visible_for_user($b)) return '';

	$logged = is_user_logged_in();
	if ($logged) {
		$href = ($b['link_in'] === 'url' && $b['url_in'] !== '') ? dsrf_banner_url($b['url_in']) : '#my-referrals';
		$popup = ($b['link_in'] !== 'url' || $b['url_in'] === '');
	} else {
		$href = dsrf_banner_url($b['url_out'] !== '' ? $b['url_out'] : '/login');
		$popup = false;
	}
	$target = (!$popup && $b['new_tab']) ? ' target="_blank" rel="noopener"' : '';
	$desk = $b['img_desktop'] ? wp_get_attachment_image_url((int) $b['img_desktop'], 'full') : '';
	$mob  = $b['img_mobile'] ? wp_get_attachment_image_url((int) $b['img_mobile'], 'full') : '';
	if (!$desk && $mob) $desk = $mob;
	$sub = $b['subtext'] !== '' ? $b['subtext'] : (function_exists('dsrf_reward_text') ? dsrf_reward_text() : '');
	$btn = $logged ? $b['btn_in'] : $b['btn_out'];
	$mw  = max(320, (int) $b['max_width']);

	ob_start(); ?>
<div class="dsrf-banner-wrap" data-dsrf-banner data-popup="<?php echo $popup ? '1' : '0'; ?>" data-dismiss="<?php echo $b['dismissible'] ? '1' : '0'; ?>" style="max-width:<?php echo (int) $mw; ?>px">
  <?php if ($b['dismissible']): ?><button type="button" class="dsrf-banner-x" aria-label="Close banner">&times;</button><?php endif; ?>
  <a class="dsrf-banner-link" href="<?php echo esc_url($href); ?>"<?php echo $target; ?>>
  <?php if ($desk): ?>
    <picture>
      <?php if ($mob): ?><source media="(max-width: 767px)" srcset="<?php echo esc_url($mob); ?>"><?php endif; ?>
      <img class="dsrf-banner-img" src="<?php echo esc_url($desk); ?>" alt="<?php echo esc_attr($b['alt']); ?>" loading="lazy" decoding="async">
    </picture>
  <?php else: ?>
    <div class="dsrf-banner-built">
      <div class="dsrf-bb-ico"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="9" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 13h18M12 9v12" stroke="currentColor" stroke-width="1.8"/><path d="M12 9c-1.5-3-5.5-3.5-5.5-1S10 9 12 9zm0 0c1.5-3 5.5-3.5 5.5-1S14 9 12 9z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></div>
      <div class="dsrf-bb-txt">
        <div class="dsrf-bb-h"><?php echo esc_html($b['headline']); ?></div>
        <?php if ($sub): ?><div class="dsrf-bb-s"><?php echo esc_html($sub); ?></div><?php endif; ?>
      </div>
      <span class="dsrf-bb-btn"><?php echo esc_html($btn); ?> <span aria-hidden="true">&rarr;</span></span>
    </div>
  <?php endif; ?>
  </a>
</div>
<?php
	return ob_get_clean();
}

function dsrf_banner_css() {
	static $done = false;
	if ($done) return '';
	$done = true;
	return '<style id="dsrf-banner-css">
.dsrf-banner-wrap{position:relative;margin:28px auto;padding:0 20px;box-sizing:border-box;width:100%}
.dsrf-banner-wrap[data-dsrf-pending]{display:none}
.dsrf-banner-link{display:block;text-decoration:none!important;border-radius:20px;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease}
.dsrf-banner-link:hover{transform:translateY(-2px);box-shadow:0 18px 50px rgba(99,102,241,.25)}
.dsrf-banner-img{display:block;width:100%;height:auto;border-radius:20px}
.dsrf-banner-x{position:absolute;top:10px;right:30px;z-index:2;width:30px;height:30px;border-radius:50%;border:none;background:rgba(15,15,35,.55);color:#fff;font-size:20px;line-height:30px;text-align:center;cursor:pointer;padding:0;backdrop-filter:blur(4px)}
.dsrf-banner-x:hover{background:rgba(15,15,35,.8)}
.dsrf-banner-built{display:flex;align-items:center;gap:20px;padding:26px 30px;border-radius:20px;background:radial-gradient(120% 160% at 0% 0%,#4f46e5 0%,#6d28d9 45%,#1e1b4b 100%);border:1px solid rgba(255,255,255,.12);color:#fff;font-family:Poppins,-apple-system,Segoe UI,sans-serif}
.dsrf-bb-ico{flex-shrink:0;width:58px;height:58px;border-radius:16px;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;color:#fde68a}
.dsrf-bb-ico svg{width:30px;height:30px}
.dsrf-bb-txt{flex:1;min-width:0}
.dsrf-bb-h{font-size:22px;font-weight:800;line-height:1.25;letter-spacing:-.01em}
.dsrf-bb-s{font-size:14px;opacity:.82;margin-top:4px;line-height:1.5}
.dsrf-bb-btn{flex-shrink:0;display:inline-flex;align-items:center;gap:6px;padding:12px 22px;border-radius:100px;background:#fff;color:#4338ca;font-weight:700;font-size:14px;white-space:nowrap}
@media (max-width:767px){
 .dsrf-banner-wrap{margin:20px auto;padding:0 14px}
 .dsrf-banner-x{right:22px;top:8px}
 .dsrf-banner-built{flex-direction:column;align-items:flex-start;gap:12px;padding:22px 20px}
 .dsrf-bb-h{font-size:18px;padding-right:26px}
 .dsrf-bb-s{font-size:13px}
 .dsrf-bb-btn{width:100%;justify-content:center;box-sizing:border-box}
}
</style>';
}

/* shared click / close behaviour */
function dsrf_banner_js() {
	static $done = false;
	if ($done) return '';
	$done = true;
	ob_start(); ?>
<script>
(function(){
  var KEY='dsrf_banner_closed';
  function closedRecently(){ try{ var t=+localStorage.getItem(KEY)||0; return t && (Date.now()-t) < 7*864e5; }catch(e){ return false; } }
  function openReferrals(){
    if(typeof window.dsAcctOpen!=='function') return false;
    window.dsAcctOpen();
    var btn=null;
    document.querySelectorAll('.ds-tab-btn').forEach(function(b){ if((b.getAttribute('onclick')||'').indexOf("'referrals'")>-1) btn=b; });
    if(btn && typeof window.dsTab==='function') window.dsTab('referrals', btn);
    return true;
  }
  window.dsrfBannerInit=function(w){
    if(!w || w.getAttribute('data-ready')) return;
    w.setAttribute('data-ready','1');
    if(w.getAttribute('data-dismiss')==='1' && closedRecently()){ w.parentNode && w.parentNode.removeChild(w); return; }
    var x=w.querySelector('.dsrf-banner-x');
    if(x) x.addEventListener('click', function(e){ e.preventDefault(); try{ localStorage.setItem(KEY, Date.now()); }catch(err){} w.parentNode && w.parentNode.removeChild(w); });
    var a=w.querySelector('.dsrf-banner-link');
    if(a && w.getAttribute('data-popup')==='1') a.addEventListener('click', function(e){ if(openReferrals()) e.preventDefault(); });
  };
  function all(){ document.querySelectorAll('[data-dsrf-banner]:not([data-dsrf-pending])').forEach(window.dsrfBannerInit); }
  if(document.readyState!=='loading') all(); else document.addEventListener('DOMContentLoaded', all);
})();
</script>
<?php
	return ob_get_clean();
}

/* shortcode: [ds_referral_banner] — shown wherever you place it (even if the home banner is off) */
add_shortcode('ds_referral_banner', function () {
	$html = dsrf_banner_html(true);
	if ($html === '') return '';
	return dsrf_banner_css() . $html . dsrf_banner_js();
});

/* home page: printed in the footer, then moved into place */
add_action('wp_footer', function () {
	if (!is_front_page()) return;
	$b = dsrf_banner_settings();
	if (!$b['enabled'] || $b['position'] === 'shortcode') return;
	$html = dsrf_banner_html(false);
	if ($html === '') return;
	$html = str_replace('data-dsrf-banner ', 'data-dsrf-banner data-dsrf-pending ', $html);
	echo dsrf_banner_css() . '<div id="dsrf-banner-holder">' . $html . '</div>';
	?>
<script>
(function(){
  var POS=<?php echo wp_json_encode($b['position']); ?>;
  function place(){
    var w=document.querySelector('#dsrf-banner-holder [data-dsrf-banner]'); if(!w) return;
    var hero=document.getElementById('dsHero');
    var block=hero ? (hero.closest('.elementor > .elementor-element') || hero.closest('.elementor > .e-con') || hero.closest('.elementor-top-section') || hero) : null;
    var content=document.querySelector('#content .elementor, #content, main, #primary');
    var footer=document.querySelector('footer.site-footer, #colophon, footer');
    if(POS==='before_hero' && block){ block.parentNode.insertBefore(w, block); }
    else if(POS==='before_footer' && footer){ footer.parentNode.insertBefore(w, footer); }
    else if(block){ block.parentNode.insertBefore(w, block.nextSibling); }
    else if(content){ content.insertBefore(w, content.firstChild); }
    w.removeAttribute('data-dsrf-pending');
    var h=document.getElementById('dsrf-banner-holder'); if(h&&!h.children.length) h.parentNode.removeChild(h);
    if(window.dsrfBannerInit) window.dsrfBannerInit(w);
  }
  if(document.readyState!=='loading') place(); else document.addEventListener('DOMContentLoaded', place);
})();
</script>
	<?php
	echo dsrf_banner_js();
}, 50);

/* =========================================================
 * ADMIN: Referrals > Home Banner
 * ======================================================= */
add_action('admin_menu', function () {
	global $admin_page_hooks;
	$parent = isset($admin_page_hooks['ds-referrals']) ? 'ds-referrals' : 'options-general.php';
	add_submenu_page($parent, 'Referral Home Banner', 'Home Banner', 'manage_options', 'ds-referral-banner', 'dsrf_banner_admin');
}, 20);

add_action('admin_enqueue_scripts', function ($hook) {
	if (isset($_GET['page']) && $_GET['page'] === 'ds-referral-banner') wp_enqueue_media();
});

function dsrf_banner_admin() {
	if (!current_user_can('manage_options')) return;
	$msg = '';
	if (isset($_POST['dsrf_banner_save']) && check_admin_referer('dsrf_banner')) {
		$p = wp_unslash($_POST);
		$d = dsrf_banner_defaults();
		update_option('dsrf_banner', array(
			'enabled'     => !empty($p['enabled']) ? 1 : 0,
			'img_desktop' => absint($p['img_desktop'] ?? 0),
			'img_mobile'  => absint($p['img_mobile'] ?? 0),
			'alt'         => sanitize_text_field($p['alt'] ?? $d['alt']),
			'position'    => in_array($p['position'] ?? '', array('after_hero', 'before_hero', 'before_footer', 'shortcode'), true) ? $p['position'] : 'after_hero',
			'show_to'     => in_array($p['show_to'] ?? '', array('all', 'logged_in', 'logged_out'), true) ? $p['show_to'] : 'all',
			'link_in'     => ($p['link_in'] ?? '') === 'url' ? 'url' : 'popup',
			'url_in'      => dsrf_banner_clean_url($p['url_in'] ?? ''),
			'url_out'     => dsrf_banner_clean_url($p['url_out'] ?? '/login'),
			'new_tab'     => !empty($p['new_tab']) ? 1 : 0,
			'dismissible' => !empty($p['dismissible']) ? 1 : 0,
			'max_width'   => max(320, min(2400, (int) ($p['max_width'] ?? 1280))),
			'headline'    => sanitize_text_field($p['headline'] ?? $d['headline']),
			'subtext'     => sanitize_text_field($p['subtext'] ?? ''),
			'btn_in'      => sanitize_text_field($p['btn_in'] ?? $d['btn_in']),
			'btn_out'     => sanitize_text_field($p['btn_out'] ?? $d['btn_out']),
		));
		// clear the cached home page so the change shows right away
		do_action('litespeed_purge_url', home_url('/'));
		do_action('litespeed_purge_all_object');
		$msg = 'Banner saved.';
	}
	$b = dsrf_banner_settings();
	$desk = $b['img_desktop'] ? wp_get_attachment_image_url((int) $b['img_desktop'], 'large') : '';
	$mob  = $b['img_mobile'] ? wp_get_attachment_image_url((int) $b['img_mobile'], 'medium_large') : '';
	?>
<div class="wrap">
<h1>Referral Home Banner</h1>
<?php if ($msg): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($msg); ?> <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank">View home page &rarr;</a></p></div><?php endif; ?>
<?php if (!function_exists('dsrf_settings')): ?><div class="notice notice-error"><p>Activate <strong>DS Referral System — Core + Admin</strong> first — the banner needs it.</p></div><?php endif; ?>
<style>
.dsrfb-box{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:6px 20px 16px;margin:16px 0;max-width:980px}
.dsrfb-pick{display:flex;gap:24px;flex-wrap:wrap}
.dsrfb-slot{flex:1;min-width:260px}
.dsrfb-prev{border:2px dashed #cbd5e1;border-radius:12px;min-height:120px;display:flex;align-items:center;justify-content:center;background:#f8fafc;overflow:hidden;margin:8px 0}
.dsrfb-prev img{display:block;max-width:100%;height:auto}
.dsrfb-prev span{color:#94a3b8;font-size:13px;padding:20px;text-align:center}
</style>
<form method="post">
<?php wp_nonce_field('dsrf_banner'); ?>

<div class="dsrfb-box">
<h2>Banner</h2>
<table class="form-table" role="presentation">
<tr><th>Show banner</th><td><label><input type="checkbox" name="enabled" value="1" <?php checked($b['enabled']); ?>> Show the referral banner on the home page</label>
<p class="description">It only appears while the referral program is on (Referrals &gt; Settings).</p></td></tr>
<tr><th>Position</th><td><select name="position">
	<option value="after_hero" <?php selected($b['position'], 'after_hero'); ?>>Below the top hero section (recommended)</option>
	<option value="before_hero" <?php selected($b['position'], 'before_hero'); ?>>Top of the page, above the hero</option>
	<option value="before_footer" <?php selected($b['position'], 'before_footer'); ?>>Bottom of the page, above the footer</option>
	<option value="shortcode" <?php selected($b['position'], 'shortcode'); ?>>Only where I place the shortcode</option>
</select>
<p class="description">Shortcode for any page or Elementor Shortcode widget: <code>[ds_referral_banner]</code></p></td></tr>
<tr><th>Show to</th><td><select name="show_to">
	<option value="all" <?php selected($b['show_to'], 'all'); ?>>Everyone</option>
	<option value="logged_in" <?php selected($b['show_to'], 'logged_in'); ?>>Only logged-in users</option>
	<option value="logged_out" <?php selected($b['show_to'], 'logged_out'); ?>>Only visitors who are not logged in</option>
</select></td></tr>
<tr><th>Close button</th><td><label><input type="checkbox" name="dismissible" value="1" <?php checked($b['dismissible']); ?>> Let people close the banner (hidden for 7 days on that device)</label></td></tr>
<tr><th>Maximum width</th><td><input type="number" name="max_width" min="320" max="2400" value="<?php echo esc_attr($b['max_width']); ?>" class="small-text"> px <span class="description">1280 matches your home page content width.</span></td></tr>
</table>
</div>

<div class="dsrfb-box">
<h2>Design</h2>
<p class="description">Upload your banner image. Recommended: desktop <strong>1280 × 300 px</strong>, mobile <strong>720 × 600 px</strong> (JPG, PNG or WebP). The mobile image is used on screens under 768 px; without it the desktop image is used everywhere.</p>
<div class="dsrfb-pick">
<?php foreach (array('img_desktop' => array('Desktop design', $desk), 'img_mobile' => array('Mobile design (optional)', $mob)) as $key => $info): ?>
	<div class="dsrfb-slot">
		<strong><?php echo esc_html($info[0]); ?></strong>
		<div class="dsrfb-prev" id="<?php echo esc_attr($key); ?>_prev"><?php echo $info[1] ? '<img src="' . esc_url($info[1]) . '" alt="">' : '<span>No image — the built-in banner below is shown</span>'; ?></div>
		<input type="hidden" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($b[$key]); ?>">
		<button type="button" class="button dsrfb-upload" data-target="<?php echo esc_attr($key); ?>">Upload / choose image</button>
		<button type="button" class="button-link dsrfb-remove" data-target="<?php echo esc_attr($key); ?>" style="margin-left:8px;color:#b32d2e;">Remove</button>
	</div>
<?php endforeach; ?>
</div>
<table class="form-table" role="presentation">
<tr><th>Image description</th><td><input type="text" name="alt" value="<?php echo esc_attr($b['alt']); ?>" class="regular-text"><p class="description">Read by screen readers and search engines.</p></td></tr>
</table>
</div>

<div class="dsrfb-box">
<h2>Where the banner links</h2>
<table class="form-table" role="presentation">
<tr><th>Logged-in users</th><td>
	<label><input type="radio" name="link_in" value="popup" <?php checked($b['link_in'], 'popup'); ?>> Open the account popup on <em>My Referrals</em> (shows their link)</label><br>
	<label><input type="radio" name="link_in" value="url" <?php checked($b['link_in'], 'url'); ?>> Go to this link:</label>
	<input type="text" name="url_in" value="<?php echo esc_attr($b['url_in']); ?>" class="regular-text" placeholder="/refer-and-earn">
</td></tr>
<tr><th>Visitors (not logged in)</th><td><input type="text" name="url_out" value="<?php echo esc_attr($b['url_out']); ?>" class="regular-text" placeholder="/login"><p class="description">Your sign-up page. A path like <code>/login</code> or a full URL.</p></td></tr>
<tr><th>New tab</th><td><label><input type="checkbox" name="new_tab" value="1" <?php checked($b['new_tab']); ?>> Open links in a new tab</label></td></tr>
</table>
</div>

<div class="dsrfb-box">
<h2>Built-in banner text <span style="font-weight:400;font-size:13px;color:#64748b;">(used only when no design is uploaded)</span></h2>
<table class="form-table" role="presentation">
<tr><th>Headline</th><td><input type="text" name="headline" value="<?php echo esc_attr($b['headline']); ?>" class="regular-text"></td></tr>
<tr><th>Sub-text</th><td><input type="text" name="subtext" value="<?php echo esc_attr($b['subtext']); ?>" class="large-text" placeholder="<?php echo esc_attr(function_exists('dsrf_reward_text') ? dsrf_reward_text() : ''); ?>"><p class="description">Leave empty to use the reward from your referral settings automatically.</p></td></tr>
<tr><th>Button — logged in</th><td><input type="text" name="btn_in" value="<?php echo esc_attr($b['btn_in']); ?>" class="regular-text"></td></tr>
<tr><th>Button — visitors</th><td><input type="text" name="btn_out" value="<?php echo esc_attr($b['btn_out']); ?>" class="regular-text"></td></tr>
</table>
</div>

<p class="submit"><button type="submit" name="dsrf_banner_save" value="1" class="button button-primary">Save banner</button></p>
</form>
</div>
<script>
jQuery(function($){
	var frames={};
	$('.dsrfb-upload').on('click', function(e){
		e.preventDefault();
		var t=$(this).data('target');
		if(!frames[t]){
			frames[t]=wp.media({title:'Choose banner image', button:{text:'Use this image'}, library:{type:'image'}, multiple:false});
			frames[t].on('select', function(){
				var a=frames[t].state().get('selection').first().toJSON();
				var url=(a.sizes&&a.sizes.large)?a.sizes.large.url:a.url;
				$('#'+t).val(a.id);
				$('#'+t+'_prev').html('<img src="'+url+'" alt="">');
			});
		}
		frames[t].open();
	});
	$('.dsrfb-remove').on('click', function(e){
		e.preventDefault();
		var t=$(this).data('target');
		$('#'+t).val('0');
		$('#'+t+'_prev').html('<span>No image — the built-in banner below is shown</span>');
	});
});
</script>
	<?php
}

} // function_exists guard
