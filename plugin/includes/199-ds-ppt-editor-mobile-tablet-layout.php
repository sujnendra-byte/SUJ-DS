<?php
/**
 * Code Snippets export — DS PPT Editor — Mobile & Tablet Layout
 *
 * Snippet ID : 975
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : 
 * Modified   : 2026-10-08 09:44:28
 * Load order : 190 of 257
 */
/**
 * DS PPT Editor — Mobile & Tablet Layout
 * Run: front end
 *
 * On screens up to 1024px wide the presentation editor is stacked for touch:
 *   top bar  →  "Slides" strip (swipe or arrows)  →  the slide  →  THEMES / PICK A DESIGN / page / zoom bar
 *   →  "Layouts" strip (swipe or arrows).
 * THEMES opens a "Select themes" sheet with a grid of themes.
 * The slide is fitted to the screen and can be zoomed with two fingers (pinch), 20% - 300%.
 * Desktop (wider than 1024px) is not changed at all.
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
<style id="ds-mob-editor-css">
@media (max-width:1024px){
/* the editor takes the whole screen; the layout panel becomes a strip at the bottom */
body.vp-showing #dsp-editor-fullscreen,body.vp-collapsed #dsp-editor-fullscreen{right:0!important;}
body.vp-showing #dsp-editor-fullscreen.dsp-open{bottom:var(--dsmb-strip,132px)!important;height:auto!important;}
#dsp-editor-fullscreen{--dsmb-cw:clamp(104px,21.5vw,210px);}

/* top bar */
#dsp-ppt-toolbar{gap:8px!important;padding:6px 10px!important;min-height:48px!important;height:auto!important;box-sizing:border-box!important;}
#dsp-ppt-toolbar .dsp-tb-back{flex:0 0 auto!important;padding:6px 8px!important;font-size:12px!important;}
#dsp-ppt-toolbar #dsp-etitle{flex:1 1 40px!important;min-width:0!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important;font-size:13px!important;}
#dsp-ppt-toolbar .dsp-tb-build,#dsp-ppt-toolbar #dsp-tb-sharebtn{flex:0 0 auto!important;padding:8px 12px!important;font-size:12px!important;margin:0!important;}
#dsp-ppt-toolbar #dsp-imgstatus-tb{display:none!important;}
#dsp-ppt-toolbar #dsfp-chip{margin-left:0!important;font-size:9.5px!important;padding:3px 7px!important;white-space:normal!important;max-width:96px!important;line-height:1.15!important;}

/* workspace: strip on top, slide below */
#dsp-ppt-workspace{flex-direction:column!important;min-height:0!important;}
#dsp-ppt-panel-left{width:100%!important;flex:0 0 auto!important;border-right:0!important;border-bottom:1px solid #1a1a1a!important;position:relative!important;background:#35373a!important;}
#dsp-ppt-panel-left-header{border:0!important;padding:8px 44px 0!important;font-size:10px!important;letter-spacing:.3px!important;text-transform:none!important;color:#d5d8dc!important;}
#dsp-ppt-thumbs{flex:0 0 auto!important;flex-direction:row!important;align-items:flex-start!important;overflow-x:auto!important;overflow-y:hidden!important;gap:10px!important;padding:6px 0 10px!important;margin:0 40px!important;scrollbar-width:none!important;-webkit-overflow-scrolling:touch;scroll-behavior:smooth;}
#dsp-ppt-thumbs::-webkit-scrollbar{display:none!important;}
#dsp-ppt-thumbs .dsp-thumb-item{flex:0 0 var(--dsmb-cw)!important;width:var(--dsmb-cw)!important;}
#dsp-ppt-thumbs .dsp-thumb-num{display:none!important;}
#dsp-ppt-thumbs.dsat-on::after{display:none!important;}            /* the "+" tile is replaced by the Add slide button below */
#dsp-ppt-panel-left-footer{position:absolute!important;top:2px!important;right:40px!important;padding:0!important;border:0!important;}
#dsp-ppt-panel-left-footer button{width:auto!important;padding:3px 9px!important;font-size:10px!important;border-radius:999px!important;}
#dsp-ppt-panel-right{flex:1 1 auto!important;min-height:0!important;width:100%!important;}
#dsp-ppt-canvas-area{padding:12px!important;justify-content:safe center!important;align-items:safe center!important;touch-action:pan-x pan-y!important;overscroll-behavior:contain;}
#dsp-ppt-canvas-area.dsmb-pinch #dsp-ppt-slide-outer{transition:none!important;}
#dsp-ppt-slide-outer{flex:0 0 auto!important;}

/* bar under the slide: THEMES, PICK A DESIGN, page + zoom */
#dsp-ppt-bottom-bar{justify-content:flex-start!important;flex-wrap:wrap!important;gap:8px!important;padding:8px 10px!important;}
#dsp-ed-theme-trigger.dsp-ed-theme-trigger-barfixed{position:static!important;transform:none!important;order:1!important;background:#3b3bf5!important;border-radius:2px!important;padding:11px 20px!important;font-size:0!important;line-height:1!important;}
#dsp-ed-theme-trigger.dsp-ed-theme-trigger-barfixed::before{content:"THEMES";font-size:13px;font-weight:800;letter-spacing:.2px;}
#ds-tpl-pick-btn{position:static!important;transform:none!important;order:2!important;padding:11px 20px!important;white-space:nowrap!important;border-radius:2px!important;box-shadow:none!important;font-size:13px!important;font-weight:800!important;letter-spacing:.2px!important;line-height:1!important;gap:0!important;}
#dsp-bottom-pill{order:3!important;margin-left:auto!important;padding:4px 10px!important;gap:4px!important;}
#dsp-bottom-pill #dsp-ppt-nav-lbl{min-width:44px!important;font-size:11px!important;}
#dsp-bottom-pill button{padding:0!important;width:32px!important;min-width:0!important;height:32px!important;flex:0 0 32px!important;}

/* layouts strip */
#dsp-vp-panel{top:auto!important;bottom:0!important;left:0!important;right:0!important;width:100%!important;height:var(--dsmb-strip,132px)!important;border-left:0!important;border-top:1px solid #1a1a1a!important;background:#35373a!important;box-sizing:border-box!important;}
#dsp-vp-panel.vp-collapsed{width:100%!important;}
#dsp-vp-panel #vp-collapse-toggle,#dsp-vp-panel #vp-collapse-strip{display:none!important;}
#dsp-vp-panel.vp-collapsed .vp-hdr{display:block!important;}
#dsp-vp-panel.vp-collapsed .vp-scroll{display:flex!important;}
#dsp-vp-panel .vp-hdr{background:transparent!important;border:0!important;padding:8px 44px 0!important;}
#dsp-vp-panel .vp-hdr h3{font-size:0!important;margin:0!important;line-height:1!important;}
#dsp-vp-panel .vp-hdr h3::before{content:"Layouts";font-size:10px;font-weight:700;letter-spacing:.3px;color:#d5d8dc;text-transform:none;}
#dsp-vp-panel .vp-hdr p{display:none!important;}
#dsp-vp-panel .vp-scroll{flex-direction:row!important;align-items:flex-start!important;overflow-x:auto!important;overflow-y:hidden!important;gap:10px!important;padding:6px 0 8px!important;margin:0 40px!important;scrollbar-width:none!important;-webkit-overflow-scrolling:touch;scroll-behavior:smooth;}
#dsp-vp-panel .vp-scroll::-webkit-scrollbar{display:none!important;}
#dsp-vp-panel .vp-card{flex:0 0 var(--dsmb-cw,150px)!important;width:var(--dsmb-cw,150px)!important;border-radius:4px!important;}
#dsp-vp-panel .vp-lbl{padding:3px 6px!important;font-size:9px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
#dsp-vp-panel .vp-empty{padding:14px!important;white-space:nowrap!important;}

/* the support chat button moves above the layouts strip */
body.dsp-editor-open #ds-suji-btn{bottom:calc(var(--dsmb-strip,132px) + var(--dsmb-bar,60px) + 10px)!important;transform:scale(.82);transform-origin:bottom right;}

/* strip arrows */
.dsmb-arr{position:absolute;top:22px;bottom:8px;width:36px;display:flex;align-items:center;justify-content:center;background:transparent;border:0;padding:0;margin:0;color:#e8eaed;font:300 30px/1 -apple-system,"Segoe UI",Arial,sans-serif;cursor:pointer;z-index:5;-webkit-tap-highlight-color:transparent;}
.dsmb-arr.dsmb-l{left:2px;}.dsmb-arr.dsmb-r{right:2px;}
.dsmb-arr[disabled]{opacity:.25;cursor:default;}

/* THEMES sheet */
#dsp-ed-theme-drawer{left:12px!important;right:12px!important;bottom:0!important;max-height:62vh!important;border-radius:22px 22px 0 0!important;padding:18px 16px 14px!important;box-sizing:border-box!important;}
#dsp-ed-theme-drawer .dsp-th-hdr{position:relative!important;display:block!important;text-align:center!important;margin:0 0 14px!important;padding:6px 40px 14px!important;border-bottom:1px solid #d9dce1!important;}
#dsp-ed-theme-drawer .dsp-th-hdr h4{font-size:0!important;margin:0!important;line-height:1!important;}
#dsp-ed-theme-drawer .dsp-th-hdr h4::before{content:"SELECT THEMES";font-size:clamp(20px,4.6vw,30px);font-weight:900;color:#0b0b0f;letter-spacing:.2px;}
#dsp-ed-drawer-close{position:absolute!important;top:0!important;right:0!important;background:transparent!important;padding:4px 8px!important;font-size:0!important;border-radius:6px!important;}
#dsp-ed-drawer-close::before{content:"\2715";font-size:22px;font-weight:900;color:#0b0b0f;line-height:1;}
#dsp-ed-drawer-row{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:14px!important;overflow-x:hidden!important;overflow-y:auto!important;padding:2px 4px 10px!important;align-content:start!important;align-items:start!important;grid-auto-rows:max-content!important;min-height:0!important;-webkit-overflow-scrolling:touch;}
#dsp-ed-drawer-row .dsp-th-card{flex:none!important;width:auto!important;min-width:0!important;}
#dsp-ed-drawer-row .dsp-th-name{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
}
@media (max-width:640px){
#dsp-ed-drawer-row{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;}
#dsp-ppt-toolbar .dsp-tb-build,#dsp-ppt-toolbar #dsp-tb-sharebtn{padding:7px 8px!important;font-size:11px!important;}
#dsp-ppt-toolbar #dsp-tb-divider{display:none!important;}
#dsp-ed-theme-trigger.dsp-ed-theme-trigger-barfixed{padding:10px 14px!important;}
#ds-tpl-pick-btn{padding:10px 14px!important;}
#dsp-bottom-pill{flex:1 1 100%!important;justify-content:center!important;margin-left:0!important;box-sizing:border-box!important;}
}
</style>
<script id="ds-mob-editor-js">
(function(){
if (window.__dsMobEditor) return; window.__dsMobEditor = 1;
var MQ = window.matchMedia('(max-width:1024px)');
function $(id){ return document.getElementById(id); }
function on(){ var fs = $('dsp-editor-fullscreen'); return MQ.matches && fs && fs.classList.contains('dsp-open'); }

/* ---------- zoom: fit to the screen + two-finger pinch ---------- */
var Z = 1, userZoom = false, lastKey = '';
function mainSize(){ var m = $('dsp-ppt-main-slide'); return m ? {w:m.offsetWidth || 900, h:m.offsetHeight || 506} : null; }
function applyZoom(z, quiet){
	var o = $('dsp-ppt-slide-outer'), d = mainSize(); if (!o || !d) return;
	Z = Math.max(0.2, Math.min(3, z));
	o.style.transform = 'scale(' + Z + ')';
	/* the scaled slide takes exactly the room it shows, so it centres and scrolls correctly */
	var mx = -(d.w - d.w * Z) / 2, my = -(d.h - d.h * Z) / 2;
	o.style.margin = my.toFixed(1) + 'px ' + mx.toFixed(1) + 'px';
	var l = $('dsp-ppt-zoom-lbl'); if (l) l.textContent = Math.round(Z * 100) + '%';
	if (!quiet) pokeBars();
}
function clearZoom(){ var o = $('dsp-ppt-slide-outer'); if (o && o.style.margin) { o.style.margin = ''; } }
function fitZoom(){
	var a = $('dsp-ppt-canvas-area'), d = mainSize(); if (!a || !d) return 1;
	var cs = getComputedStyle(a), w = a.clientWidth - (parseFloat(cs.paddingLeft) || 0) - (parseFloat(cs.paddingRight) || 0) - 24, h = a.clientHeight - (parseFloat(cs.paddingTop) || 0) - (parseFloat(cs.paddingBottom) || 0) - 24; /* the room left between the element editor's top and bottom bars */
	if (w < 40 || h < 40) return Z;
	return Math.max(0.2, Math.min(1.6, w / d.w, h / d.h));
}
var pokeT = 0, poking = false;
function pokeBars(){               /* let the Edit slide / Logo buttons follow the slide */
	clearTimeout(pokeT);
	pokeT = setTimeout(function(){ poking = true; try { window.dispatchEvent(new Event('resize')); } catch (e) {} poking = false; }, 180);
}
function refit(force){
	if (!on()) return;
	var a = $('dsp-ppt-canvas-area'), d = mainSize(); if (!a || !d) return;
	var key = a.clientWidth + 'x' + a.clientHeight + 'p' + a.style.paddingTop + a.style.paddingBottom + '|' + d.w + 'x' + d.h;
	if (!force && key === lastKey) { var o = $('dsp-ppt-slide-outer'); if (o && !o.style.margin) applyZoom(Z, true); return; }
	lastKey = key;
	if (!userZoom || force) { userZoom = false; applyZoom(fitZoom()); } else applyZoom(Z);
}
/* the editor's own − / + buttons and mouse wheel keep working, in the same scale */
function hookZoom(){
	var f = window.dspZoom; if (typeof f !== 'function' || f.__dsmb) return;
	var w = function(delta){
		if (!on()) return f.apply(this, arguments);
		userZoom = true; applyZoom(Math.round((Z + (delta || 0)) * 100) / 100);
	};
	w.__dsmb = 1; window.dspZoom = w;
}
function dist(t){ var dx = t[0].clientX - t[1].clientX, dy = t[0].clientY - t[1].clientY; return Math.sqrt(dx * dx + dy * dy) || 1; }
var pin = null;
document.addEventListener('touchstart', function(e){
	if (!on() || e.touches.length !== 2) return;
	var a = $('dsp-ppt-canvas-area'); if (!a || !a.contains(e.target)) return;
	pin = {d:dist(e.touches), z:Z}; a.classList.add('dsmb-pinch');
	if (document.activeElement && document.activeElement.blur && a.contains(document.activeElement)) document.activeElement.blur();
	e.preventDefault();
}, {passive:false, capture:true});
document.addEventListener('touchmove', function(e){
	if (!pin || e.touches.length !== 2) return;
	e.preventDefault();
	userZoom = true; applyZoom(pin.z * dist(e.touches) / pin.d, true);
}, {passive:false, capture:true});
function endPinch(e){
	if (!pin || (e.touches && e.touches.length >= 2)) return;
	pin = null; var a = $('dsp-ppt-canvas-area'); if (a) a.classList.remove('dsmb-pinch');
	pokeBars();
}
document.addEventListener('touchend', endPinch, true);
document.addEventListener('touchcancel', endPinch, true);
/* Safari: stop the page itself from zooming while pinching the slide */
document.addEventListener('gesturestart', function(e){ var a = $('dsp-ppt-canvas-area'); if (on() && a && a.contains(e.target)) e.preventDefault(); }, {passive:false});

/* ---------- strips: arrows + keep the current slide in view ---------- */
function arrows(host, scroller, key){
	if (!host || !scroller) return;
	var l = host.querySelector('.dsmb-arr.dsmb-l'), r = host.querySelector('.dsmb-arr.dsmb-r');
	if (!MQ.matches) { if (l) l.remove(); if (r) r.remove(); return; }
	if (!l) {
		l = document.createElement('button'); l.type = 'button'; l.className = 'dsmb-arr dsmb-l'; l.setAttribute('aria-label', 'Previous ' + key); l.innerHTML = '&#10216;';
		r = document.createElement('button'); r.type = 'button'; r.className = 'dsmb-arr dsmb-r'; r.setAttribute('aria-label', 'More ' + key); r.innerHTML = '&#10217;';
		l.onclick = function(e){ e.stopPropagation(); scroller.scrollBy({left:-scroller.clientWidth * 0.8, behavior:'smooth'}); };
		r.onclick = function(e){ e.stopPropagation(); scroller.scrollBy({left:scroller.clientWidth * 0.8, behavior:'smooth'}); };
		host.appendChild(l); host.appendChild(r);
	}
	var max = scroller.scrollWidth - scroller.clientWidth;
	l.disabled = scroller.scrollLeft <= 2; r.disabled = scroller.scrollLeft >= max - 2;
}
var lastActive = null;
function keepActive(){
	var th = $('dsp-ppt-thumbs'); if (!th) return;
	var a = th.querySelector('.dsp-thumb-item.dsp-thumb-active');
	if (!a || a === lastActive) return; lastActive = a;
	var ar = a.getBoundingClientRect(), tr = th.getBoundingClientRect();
	if (ar.left < tr.left || ar.right > tr.right) th.scrollBy({left:ar.left - tr.left - (tr.width - ar.width) / 2, behavior:'smooth'});
}
function stripHeight(){
	var p = $('dsp-vp-panel'); if (!p) return;
	var c = p.querySelector('.vp-card'), h = 132;
	if (c && c.offsetHeight) h = Math.round(c.offsetHeight + 46);
	var v = h + 'px', b = $('dsp-ppt-bottom-bar'), bv = ((b && b.offsetHeight) || 60) + 'px';
	if (document.documentElement.style.getPropertyValue('--dsmb-bar') !== bv) document.documentElement.style.setProperty('--dsmb-bar', bv);
	if (document.documentElement.style.getPropertyValue('--dsmb-strip') !== v) document.documentElement.style.setProperty('--dsmb-strip', v);
}

var wasOn = false;
function tick(){
	hookZoom();
	var act = on();
	if (act) {
		arrows($('dsp-ppt-panel-left'), $('dsp-ppt-thumbs'), 'slides');
		var p = $('dsp-vp-panel'); arrows(p, p && p.querySelector('.vp-scroll'), 'layouts');
		stripHeight(); keepActive();
		if (!wasOn) { userZoom = false; lastKey = ''; }
		refit(false);
	} else if (wasOn) {
		clearZoom(); lastKey = '';
		if (!MQ.matches) { arrows($('dsp-ppt-panel-left'), $('dsp-ppt-thumbs'), ''); var p2 = $('dsp-vp-panel'); arrows(p2, p2 && p2.querySelector('.vp-scroll'), ''); var o = $('dsp-ppt-slide-outer'); if (o) o.style.transform = 'scale(1)'; var lb = $('dsp-ppt-zoom-lbl'); if (lb) lb.textContent = '100%'; Z = 1; }
	}
	wasOn = act;
}
setInterval(tick, 400);
window.addEventListener('resize', function(){ if (!poking) setTimeout(function(){ refit(false); }, 60); });
window.addEventListener('orientationchange', function(){ setTimeout(function(){ userZoom = false; refit(true); }, 250); });
})();
</script>
	<?php
}, 1320);
