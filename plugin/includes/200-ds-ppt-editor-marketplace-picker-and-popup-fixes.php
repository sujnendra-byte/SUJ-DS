<?php
/**
 * Code Snippets export — DS PPT Editor - Marketplace Picker and Popup Fixes
 *
 * Snippet ID : 976
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : 
 * Modified   : 2026-10-03 02:26:06
 * Load order : 191 of 192
 */
/**
 * DS PPT Editor — Marketplace Picker & Popup Fixes
 * Run: front end
 *
 * 1. The image picker is titled "Browse From Marketplace".
 * 2. Steady picker grid: every image has a fixed box with a loading spinner, and the picture
 *    fades in inside that box. Nothing moves while images load, so a click always lands on
 *    the image the user aimed at. (Before, a late image grew from nothing and pushed the
 *    images below it down.)
 * 3. The EDIT SLIDE / LOGO buttons hide while any popup covers the slide and come back
 *    when it closes.
 * Also: category names show "&" instead of "&AMP;", and the picker's scrollbars are dark.
 *
 * Only CSS + a small script on top of the existing editor.
 * NEW SNIPPET — edits no other snippet. Undo: deactivate.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a($post, 'WP_Post') ) return;
	if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
	?>
<style id="ds-pickfix-css">
/* steady grid: rows of equal boxes instead of columns that re-flow while images load */
#ds-mkt-pick-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;column-count:auto!important;columns:auto!important;align-content:start!important;grid-auto-rows:max-content!important;scrollbar-width:thin;scrollbar-color:#3b4660 #12131c;}
@media(min-width:560px){#ds-mkt-pick-grid{grid-template-columns:repeat(3,minmax(0,1fr))!important;}}
@media(min-width:760px){#ds-mkt-pick-grid{grid-template-columns:repeat(4,minmax(0,1fr))!important;}}
@media(min-width:1000px){#ds-mkt-pick-grid{grid-template-columns:repeat(5,minmax(0,1fr))!important;}}
#ds-mkt-pick-grid .ds-mkt-pick-item{margin:0!important;position:relative;min-width:0;}
#ds-mkt-pick-grid .ds-mkt-pick-item-thumb{flex:0 0 auto!important;position:relative!important;aspect-ratio:16/10;overflow:hidden!important;background:#111318!important;}
#ds-mkt-pick-grid .ds-mkt-pick-item-thumb::before{content:"";position:absolute;top:50%;left:50%;width:28px;height:28px;margin:-14px 0 0 -14px;box-sizing:border-box;border:3px solid #262a33;border-top-color:#2563eb;border-radius:50%;animation:dspfSpin .8s linear infinite;}
#ds-mkt-pick-grid .ds-mkt-pick-item-thumb.dspf-done::before{display:none;}
#ds-mkt-pick-grid .ds-mkt-pick-item-thumb.dspf-err::after{content:"Image not available";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#5b6475;font:600 11px/1.3 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
#ds-mkt-pick-grid .ds-mkt-pick-item-thumb img{position:absolute!important;top:0;left:0;width:100%!important;height:100%!important;object-fit:cover;opacity:0;transition:opacity .25s ease;}
#ds-mkt-pick-grid .ds-mkt-pick-item-thumb img.dspf-ok{opacity:1;}
@keyframes dspfSpin{to{transform:rotate(360deg);}}
#ds-mkt-pick-cats{scrollbar-width:thin;scrollbar-color:#3b4660 #0d0d0d;}
/* EDIT SLIDE / LOGO: out of the way while a popup is open */
#dsce-bar.dspf-hide{visibility:hidden!important;pointer-events:none!important;}
</style>
<script id="ds-pickfix-js">
(function(){
if (window.__dsPickFix) return; window.__dsPickFix = 1;
function $(id){ return document.getElementById(id); }

/* ---- picker images: show each one only when it is fully loaded ---- */
function isPickImg(t){ return t && t.tagName === 'IMG' && t.parentNode && t.parentNode.classList && t.parentNode.classList.contains('ds-mkt-pick-item-thumb'); }
function done(img, ok){
	var th = img.parentNode; if (!th) return;
	if (ok) img.classList.add('dspf-ok'); else th.classList.add('dspf-err');
	th.classList.add('dspf-done');
}
document.addEventListener('load', function(e){ if (isPickImg(e.target)) done(e.target, true); }, true);
document.addEventListener('error', function(e){ if (isPickImg(e.target)) done(e.target, false); }, true);
function sweep(){
	var ov = $('ds-mkt-pick-overlay'); if (!ov || !ov.classList.contains('open')) return;
	var h = ov.querySelector('#ds-mkt-pick-header h3');
	if (h && h.getAttribute('data-dspf') !== '1') { h.textContent = '🏪 Browse From Marketplace'; h.setAttribute('data-dspf', '1'); }
	var cats = ov.querySelectorAll('.ds-mkt-pick-cat');
	for (var i = 0; i < cats.length; i++) { var t = cats[i].textContent; if (t.indexOf('&amp;') >= 0) cats[i].textContent = t.replace(/&amp;/gi, '&'); }
	var imgs = ov.querySelectorAll('.ds-mkt-pick-item-thumb img:not(.dspf-ok)');
	for (var k = 0; k < imgs.length; k++) { var im = imgs[k]; if (im.complete && !im.parentNode.classList.contains('dspf-done')) done(im, im.naturalWidth > 0); }
}

/* ---- EDIT SLIDE / LOGO: hide while something other than the editor canvas covers them ---- */
function barCheck(){
	var bar = $('dsce-bar'); if (!bar) return;
	var r = bar.getBoundingClientRect(), hide = false;
	if (r.width && r.height && document.elementsFromPoint) {
		var els = document.elementsFromPoint(r.left + r.width / 2, r.top + r.height / 2), top = null;
		for (var i = 0; i < els.length; i++) { if (els[i] !== bar && !bar.contains(els[i])) { top = els[i]; break; } }
		hide = !!top && !(top.closest && top.closest('#dsp-ppt-canvas-area'));
	}
	if (bar.classList.contains('dspf-hide') !== hide) bar.classList.toggle('dspf-hide', hide);
}
setInterval(function(){ try { sweep(); } catch (e) {} try { barCheck(); } catch (e) {} }, 250);
document.addEventListener('click', function(){ setTimeout(function(){ try { sweep(); barCheck(); } catch (e) {} }, 30); }, true);
})();
</script>
	<?php
}, 1330);
