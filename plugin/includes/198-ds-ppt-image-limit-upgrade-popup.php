<?php
/**
 * Code Snippets export — DS PPT Image Limit — Upgrade Popup
 *
 * Snippet ID : 973
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : ds-ppt
 * Modified   : 2026-10-02 14:34:43
 * Load order : 189 of 192
 */
/**
 * DS PPT Image Limit — Upgrade Popup
 * Run: front end
 *
 * When a marketplace image cannot be added to a presentation because the plan's
 * image limit is used up (free plan: 5 images per day, presentation images included)
 * or the image needs a higher plan, the editor used to show only a small red
 * error message. This shows a popup instead that explains it and offers the plans page.
 *
 * It only listens to the answer of /ds/v1/mkt-fetch-image — the limit itself is
 * still checked on the server exactly as before.
 * NEW SNIPPET — edits no other snippet. Undo: deactivate.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a($post, 'WP_Post') ) return;
	if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
	$pricing = esc_url_raw( home_url('/pricing/') );
	?>
<style id="ds-imglimit-css">
#dsil-ov{position:fixed;inset:0;z-index:2147482600;background:rgba(4,7,13,.78);display:flex;align-items:center;justify-content:center;padding:20px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
#dsil-ov .dsil-card{width:min(94vw,430px);background:#141a26;border:1px solid #2a3550;border-radius:16px;padding:28px 26px 22px;color:#e6ecf7;box-shadow:0 24px 70px rgba(0,0,0,.6);text-align:center;}
#dsil-ov .dsil-ic{width:58px;height:58px;border-radius:50%;background:rgba(245,158,11,.14);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;line-height:1;}
#dsil-ov h3{margin:0 0 10px;font-size:20px;font-weight:800;color:#f8fafc;line-height:1.3;text-transform:none;letter-spacing:normal;}
#dsil-ov p{margin:0 0 20px;font-size:14px;line-height:1.6;color:#b9c0cf;}
#dsil-ov .dsil-acts{display:flex;gap:10px;}
#dsil-ov .dsil-acts a,#dsil-ov .dsil-acts button{flex:1;display:block;text-align:center;padding:13px 12px;border-radius:10px;font:800 13.5px/1 inherit;font-family:inherit;cursor:pointer;text-decoration:none;border:0;text-transform:none;letter-spacing:normal;}
#dsil-ov .dsil-later{background:#1e2740;color:#dbe4f3;}
#dsil-ov .dsil-up{background:#f59e0b;color:#1a1204!important;}
</style>
<script id="ds-imglimit-js">
(function(){
if (window.__dsImgLimit) return; window.__dsImgLimit = 1;
var PRICING = <?php echo wp_json_encode($pricing); ?>;
var hush = 0, lastShown = 0;

function popup(reason){
	if (document.getElementById('dsil-ov')) return;
	var now = Date.now(); if (now - lastShown < 15000) return; lastShown = now;
	var up = reason === 'upgrade_required';
	var ov = document.createElement('div'); ov.id = 'dsil-ov';
	ov.innerHTML = '<div class="dsil-card" role="dialog" aria-modal="true">'
		+ '<div class="dsil-ic">' + (up ? '🔒' : '⏳') + '</div>'
		+ '<h3>' + (up ? 'This image needs a paid plan' : 'Your image imports are used up') + '</h3>'
		+ '<p>' + (up
			? 'Pro marketplace images are part of the paid plans. Subscribe to use this image in your presentation.'
			: 'You have used all the image imports included in your plan for now. The free plan includes 5 image imports per day, and images added to presentations count too. Upgrade to import more images.')
		+ '</p><div class="dsil-acts"><button type="button" class="dsil-later">Maybe later</button><a class="dsil-up" href="' + PRICING + '">Upgrade</a></div></div>';
	document.body.appendChild(ov);
	function close(){ if (ov.parentNode) ov.parentNode.removeChild(ov); document.removeEventListener('keydown', esc); }
	function esc(e){ if (e.key === 'Escape') close(); }
	ov.addEventListener('click', function(e){ if (e.target === ov) close(); });
	ov.querySelector('.dsil-later').addEventListener('click', close);
	document.addEventListener('keydown', esc);
}
window.dsImgLimitPopup = popup;

/* listen to the image answer; the page code still receives it unchanged */
var _fetch = window.fetch;
if (typeof _fetch === 'function') {
	window.fetch = function(input, init){
		var p = _fetch.apply(this, arguments), url = '';
		try { url = typeof input === 'string' ? input : (input && input.url) || ''; } catch (e) {}
		if (url.indexOf('/ds/v1/mkt-fetch-image') < 0) return p;
		return p.then(function(res){
			if (!res || res.ok || (res.status !== 403 && res.status !== 429)) return res;
			return res.clone().json().then(function(d){
				var r = d && d.reason;
				if (r === 'limit_reached' || r === 'upgrade_required') { hush = Date.now() + 4000; popup(r); }
				return res;
			}, function(){ return res; });
		});
	};
}

/* the popup replaces the small red message for this one case */
function wrapToast(){
	var t = window.dspToast;
	if (typeof t !== 'function' || t.__dsil) return;
	var w = function(msg, type){
		if (type === 'error' && Date.now() < hush && /limit|upgrade|HTTP 403|HTTP 429/i.test(String(msg))) return;
		return t.apply(this, arguments);
	};
	w.__dsil = 1; window.dspToast = w;
}
wrapToast(); setInterval(wrapToast, 1500);
})();
</script>
	<?php
}, 1310);
