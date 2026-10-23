<?php
/**
 * Code Snippets export — DS Editor Loading Screen — "Your presentation is loading..." until pictures and layouts are ready
 *
 * Snippet ID : 1232
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-editor
 * Modified   : 2026-10-15 06:19:57
 * Load order : 366 of 369
 */
/**
 * DS Editor Loading Screen - "Your presentation is loading..."
 * Run: Everywhere (prints only where the AI presentation editor is)
 *
 * When the presentation editor opens (right after generation, or when a saved deck is opened) the pictures
 * are still being fetched and the layouts are still settling. This covers the whole screen with an animated
 * "Your presentation is loading..." card and a progress bar, and blocks clicks and keys, until:
 *  - the automatic marketplace pictures have been placed (window.dsAutoMktFillAll has finished),
 *  - no slide is still generating an image (image_loading) and no picture is still downloading,
 *  - fonts are loaded and the slides have stopped re-drawing for a moment.
 * Safety: it never stays longer than 60 seconds.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_head', function () {
    if ( function_exists('ds_tpl_should_print') && ! ds_tpl_should_print() ) return;
    ?>
<style id="ds-eload-css">
#ds-eload{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;background:rgba(9,12,22,.74);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);cursor:progress;transition:opacity .3s ease}
#ds-eload.on{display:flex;animation:dseIn .25s ease both}
#ds-eload.out{opacity:0}
#ds-eload .dse-card{width:min(420px,calc(100vw - 32px));box-sizing:border-box;padding:30px 28px 24px;border-radius:18px;background:#111827;border:1px solid rgba(255,255,255,.08);box-shadow:0 30px 80px rgba(0,0,0,.5);text-align:center;color:#E5E7EB;font:500 14px/1.5 Inter,system-ui,-apple-system,Segoe UI,Arial,sans-serif}
#ds-eload .dse-stack{position:relative;width:132px;height:92px;margin:2px auto 22px}
#ds-eload .dse-stack i{position:absolute;left:16px;top:10px;width:100px;height:64px;border-radius:10px;box-shadow:0 10px 24px rgba(0,0,0,.4);animation:dseCard 2.4s ease-in-out infinite}
#ds-eload .dse-stack i::before{content:'';position:absolute;left:11px;top:11px;width:44px;height:7px;border-radius:4px;background:rgba(255,255,255,.9)}
#ds-eload .dse-stack i::after{content:'';position:absolute;left:11px;top:25px;width:46px;height:27px;border-radius:5px;background:rgba(255,255,255,.28);box-shadow:52px 0 0 -14px rgba(255,255,255,.28)}
#ds-eload .dse-stack i:nth-child(1){background:linear-gradient(135deg,#3B82F6,#8B5CF6)}
#ds-eload .dse-stack i:nth-child(2){background:linear-gradient(135deg,#F97316,#EF4444);animation-delay:-.8s}
#ds-eload .dse-stack i:nth-child(3){background:linear-gradient(135deg,#10B981,#06B6D4);animation-delay:-1.6s}
#ds-eload .dse-title{font-size:19px;font-weight:700;color:#fff;margin:0 0 6px;letter-spacing:.1px}
#ds-eload .dse-step{font-size:13px;color:#93C5FD;min-height:20px}
#ds-eload .dse-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.1);overflow:hidden;margin:16px 0 12px}
#ds-eload .dse-bar span{display:block;position:relative;overflow:hidden;height:100%;width:6%;border-radius:3px;background:linear-gradient(90deg,#3B82F6,#8B5CF6);transition:width .45s ease}
#ds-eload .dse-bar span::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);transform:translateX(-100%);animation:dseShine 1.2s linear infinite}
#ds-eload .dse-note{font-size:12px;color:#9CA3AF}
@keyframes dseIn{from{opacity:0}to{opacity:1}}
@keyframes dseShine{to{transform:translateX(100%)}}
@keyframes dseCard{0%,100%{transform:translate(-30px,12px) rotate(-11deg) scale(.84);opacity:.55;z-index:1}33%{transform:translate(0,0) rotate(0) scale(1);opacity:1;z-index:3}66%{transform:translate(30px,12px) rotate(11deg) scale(.84);opacity:.55;z-index:2}}
@media (prefers-reduced-motion:reduce){#ds-eload .dse-stack i,#ds-eload .dse-bar span::after{animation:none}#ds-eload .dse-stack i:nth-child(1){z-index:3}}
</style>
<script id="ds-eload-js">
(function(){
'use strict';
if (window.__dsEload) return; window.__dsEload = 1;
var MAXMS = 60000, QUIET = 800, MINSHOW = 700, NOFILL = 7000;
var ov = null, st = null, mo = null, lastMut = 0;
var fill = {busy: false, done: 0};
/* know when the automatic marketplace pictures are being placed (snippet "Auto Marketplace Image" defines this later) */
try {
var _fill;
Object.defineProperty(window, 'dsAutoMktFillAll', {configurable: true, get: function(){ return _fill; }, set: function(fn){
if (typeof fn !== 'function') { _fill = fn; return; }
_fill = function(){ fill.busy = true; var p; try { p = fn.apply(this, arguments); } catch (e) { fill.busy = false; fill.done = Date.now(); throw e; } Promise.resolve(p).then(fin, fin); return p; };
}});
} catch (e) {}
function fin(){ fill.busy = false; fill.done = Date.now(); }
function $(id){ return document.getElementById(id); }
function fs(){ return $('dsp-editor-fullscreen'); }
function isOpen(){ var f = fs(); return !!(f && f.classList.contains('dsp-open')); }
function slides(){ var d = (window.S && S.data && S.data.slides) ? S.data : ((window.G && G.pptData && G.pptData.slides) ? G.pptData : null); return d ? d.slides : null; }
function hasImg(i){ var a = window.S && S.data && S.data.slides && S.data.slides[i], b = window.G && G.pptData && G.pptData.slides && G.pptData.slides[i], d = (a && a.image_data) || (b && b.image_data) || ''; return typeof d === 'string' && d.length > 100; }
function build(){
var o = document.createElement('div'); o.id = 'ds-eload'; o.setAttribute('role', 'status'); o.setAttribute('aria-live', 'polite'); o.tabIndex = -1;
o.innerHTML = '<div class="dse-card"><div class="dse-stack"><i></i><i></i><i></i></div><div class="dse-title">Your presentation is loading\u2026</div><div class="dse-step">Preparing your slides</div><div class="dse-bar"><span></span></div><div class="dse-note">Please wait a moment. You can edit as soon as everything is in place.</div></div>';
['pointerdown', 'mousedown', 'click', 'dblclick', 'contextmenu', 'wheel', 'touchstart'].forEach(function(t){ o.addEventListener(t, function(e){ e.preventDefault(); e.stopPropagation(); }, {passive: false}); });
document.body.appendChild(o); return o;
}
function status(){
var f = fs(), sl = slides(), main = $('dsp-ppt-main-slide'), r = {ready: false, step: 'Preparing your slides', pct: 0.08};
if (!f || !sl || !sl.length || !main || !main.children.length) return r;
var el = Date.now() - st.t0, n = sl.length, gen = 0, i;
for (i = 0; i < n; i++) if (sl[i] && sl[i].image_loading) gen++;
if (st.need0 == null) { st.need0 = 0; for (i = 0; i < n; i++) if (!hasImg(i)) st.need0++; }
var need = 0; for (i = 0; i < n; i++) if (!hasImg(i)) need++;
var pend = 0, imgs = f.querySelectorAll('img');
for (i = 0; i < imgs.length; i++) { var im = imgs[i]; if (!im.getAttribute('src')) { pend++; continue; } if (im.loading !== 'lazy' && !im.complete) pend++; }
var busy = f.querySelectorAll('.ds-img-busy').length;
var fontsOk = !document.fonts || document.fonts.status === 'loaded';
var fillOk = !fill.busy && (need === 0 || fill.done >= st.t0 || el > NOFILL);
var quiet = Date.now() - lastMut >= QUIET;
var placed = Math.max(0, (st.need0 || 0) - need);
if (fill.busy || gen || (need && !fillOk)) r.step = 'Adding pictures' + (st.need0 ? ' (' + Math.min(placed, st.need0) + ' of ' + st.need0 + ')' : '');
else if (pend || busy) r.step = 'Loading pictures';
else if (!fontsOk) r.step = 'Applying fonts';
else r.step = 'Arranging layouts';
var part = st.need0 ? placed / st.need0 : (fillOk ? 1 : 0);
r.pct = Math.min(0.94, 0.12 + part * 0.62 + (fillOk ? 0.1 : 0) + (pend || busy ? 0 : 0.06) + (fontsOk ? 0.04 : 0));
r.ready = fillOk && !gen && !pend && !busy && fontsOk && quiet;
return r;
}
function start(){
if (st) return;
if (!ov || !ov.parentNode) ov = build();
st = {t0: Date.now(), need0: null}; lastMut = Date.now();
ov.classList.remove('out'); ov.classList.add('on');
ov.querySelector('.dse-bar span').style.width = '6%';
ov.querySelector('.dse-step').textContent = 'Preparing your slides';
document.documentElement.classList.add('ds-eload-on');
try { if (document.activeElement && document.activeElement.blur) document.activeElement.blur(); ov.focus({preventScroll: true}); } catch (e) {}
var f = fs();
if (f && window.MutationObserver) { mo = new MutationObserver(function(recs){ for (var i = 0; i < recs.length; i++) if (!ov.contains(recs[i].target)) { lastMut = Date.now(); return; } }); mo.observe(f, {childList: true, subtree: true}); }
loop();
}
function loop(){
if (!st) return;
if (!isOpen()) { hide(true); return; }
var r = status(), el = Date.now() - st.t0;
ov.querySelector('.dse-bar span').style.width = Math.round((r.ready ? 1 : r.pct) * 100) + '%';
var se = ov.querySelector('.dse-step'); if (se.textContent !== r.step) se.textContent = r.step;
if ((r.ready && el >= MINSHOW) || el > MAXMS) { hide(false); return; }
setTimeout(loop, 200);
}
function hide(now){
st = null; if (mo) { mo.disconnect(); mo = null; }
document.documentElement.classList.remove('ds-eload-on');
if (!ov) return; var o = ov;
if (now) { o.classList.remove('on', 'out'); return; }
o.querySelector('.dse-bar span').style.width = '100%';
setTimeout(function(){ o.classList.add('out'); setTimeout(function(){ o.classList.remove('on', 'out'); }, 320); }, 250);
}
/* keys do nothing while it loads (registered first, so it runs before the editor's own shortcuts) */
window.addEventListener('keydown', function(e){ if (st) { e.preventDefault(); e.stopImmediatePropagation(); } }, true);
window.addEventListener('keyup', function(e){ if (st) { e.preventDefault(); e.stopImmediatePropagation(); } }, true);
window.dsEditorLoading = function(){ return !!st; };
function hook(){
var f = fs(); if (!f) return false;
var was = f.classList.contains('dsp-open'); if (was) start();
new MutationObserver(function(){ var now = f.classList.contains('dsp-open'); if (now && !was) start(); was = now; }).observe(f, {attributes: true, attributeFilter: ['class']});
return true;
}
function wait(){ if (!hook()) setTimeout(wait, 150); }
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', wait); else wait();
})();
</script>
    <?php
}, 1);
