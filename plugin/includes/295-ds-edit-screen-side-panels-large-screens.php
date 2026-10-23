<?php
/**
 * Code Snippets export — DS Edit Screen Side Panels (large screens)
 *
 * Snippet ID : 1125
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 14:07:03
 * Load order : 295 of 296
 */
add_action('wp_footer', function () {
	if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
	?>
<style id="ds-side-panels-css">
/* DS Edit Screen Side Panels — only while html has .dsel-focus.dsel-side (large screens) */
html.dsel-focus.dsel-side #dsp-ppt-bottom-bar{display:none!important}

/* left panel: the editor's tool buttons in one column */
html.dsel-focus.dsel-side #dsel-dock{left:16px!important;right:auto!important;top:64px!important;bottom:auto!important;transform:none!important;width:164px!important;max-height:calc(100vh - 130px);overflow-y:auto!important;overflow-x:hidden!important;flex-direction:column!important;align-items:stretch!important;padding:10px!important;border-radius:12px!important;box-shadow:0 10px 30px rgba(15,23,42,.25)!important}
html.dsel-focus.dsel-side #dsel-dock .dsel-right{display:grid!important;grid-template-columns:1fr 1fr;gap:6px;white-space:normal!important;flex-wrap:wrap!important}
html.dsel-focus.dsel-side #dsel-dock .dsel-right > *{grid-column:1 / -1;width:100%;min-width:0!important;justify-content:center;margin:0!important;box-sizing:border-box}
html.dsel-focus.dsel-side #dsel-dock .dsel-right > [data-a="undo"],html.dsel-focus.dsel-side #dsel-dock .dsel-right > [data-a="redo"]{grid-column:auto}
html.dsel-focus.dsel-side #dsel-dock .dsel-b{height:28px;min-height:0;font-size:12px;padding:0 8px;border-radius:7px}
html.dsel-focus.dsel-side #dsel-dock .dsel-edscr{background:#0F172A;color:#fff;border-color:#0F172A}
html.dsel-focus.dsel-side #dsel-dock .dsel-dsep{width:auto!important;height:1px!important;min-height:1px!important;align-self:auto!important;margin:2px 0!important}
html.dsel-focus.dsel-side #dsel-dock .dsel-side-theme{background:linear-gradient(90deg,#2563EB,#7C3AED);color:#fff;border:0;font-weight:700;gap:6px}
html.dsel-focus.dsel-side #dsel-dock .dsel-side-sw{width:11px;height:11px;border-radius:50%;background:conic-gradient(#F43F5E,#F59E0B,#22C55E,#3B82F6,#A855F7,#F43F5E);border:2px solid #fff;flex:none}
#dsel-dock .dsel-side-theme{display:none}
html.dsel-focus.dsel-side #dsel-dock .dsel-side-theme{display:flex}

/* right panel: settings of the selected element */
html.dsel-focus.dsel-side #dsel-bar{left:auto!important;right:16px!important;top:64px!important;bottom:auto!important;transform:none!important;width:214px!important;max-height:calc(100vh - 130px);overflow-y:auto!important;overflow-x:hidden!important;flex-direction:column!important;align-items:stretch!important;padding:10px!important;border-radius:12px!important;box-shadow:0 10px 30px rgba(15,23,42,.25)!important}
html.dsel-focus.dsel-side #dsel-bar .dsel-left{display:flex!important;flex-wrap:wrap!important;gap:5px!important;align-items:center;width:100%}
html.dsel-focus.dsel-side #dsel-bar .dsel-left > *{margin:0!important}
html.dsel-focus.dsel-side #dsel-bar .dsel-kind{flex:1 1 100%;font-size:11px;font-weight:800;letter-spacing:.12em;line-height:1.2;height:auto;text-transform:uppercase;color:#475569;padding:0 0 6px;border-bottom:1px solid #E2E8F0;margin-bottom:4px!important}
html.dsel-focus.dsel-side #dsel-bar .dsel-b:not(.dsel-ic){flex:1 1 100%;width:100%;max-width:none;justify-content:center;height:28px;min-height:0;font-size:12px;padding:0 8px;border-radius:7px}
html.dsel-focus.dsel-side #dsel-bar .dsel-fsz{flex:1 1 100%;justify-content:space-between;height:28px;font-size:12px}
html.dsel-focus.dsel-side #dsel-bar .dsel-b.dsel-ic{width:28px;height:28px;min-width:0;font-size:12px;padding:0}
html.dsel-focus.dsel-side #dsel-bar .dsel-b svg,html.dsel-focus.dsel-side #dsel-dock .dsel-b svg{width:14px;height:14px}
html.dsel-focus.dsel-side #dsel-bar .dsel-hint{flex:1 1 100%;white-space:normal;font-size:11.5px;line-height:1.35}
html.dsel-focus.dsel-side #dsel-bar .dsel-sep{display:none!important}

/* previous / next under the slide */
#dsel-sidenav{display:none}
html.dsel-focus.dsel-side #dsel-sidenav{display:flex;position:fixed;bottom:18px;left:196px;right:246px;justify-content:center;align-items:center;gap:8px;z-index:100005}
#dsel-sidenav .dsel-sn-b{font-family:inherit;font-size:12px;font-weight:600;line-height:1;text-transform:none;letter-spacing:0;color:#CBD5E1;background:rgba(15,23,42,.55);border:1px solid #475569;border-radius:999px;padding:5px 12px;cursor:pointer;height:auto;min-height:0;box-shadow:none}
#dsel-sidenav .dsel-sn-b:hover{color:#fff;border-color:#94A3B8}
#dsel-sidenav .dsel-sn-l{color:#94A3B8;font-size:12px;font-weight:600;line-height:1;min-width:44px;text-align:center}
html.dsel-focus.dsel-side #dsel-keys{left:16px!important;bottom:16px!important;top:auto!important;transform:scale(.85);transform-origin:left bottom}
html.dsel-focus.dsel-side #dsp-ppt-canvas-area{overflow:hidden!important}
html.dsel-focus.dsel-side #dsel-dock .dsel-right > [data-a="undo"],html.dsel-focus.dsel-side #dsel-dock .dsel-right > [data-a="redo"]{font-size:16px;font-weight:700;color:#0F172A;border:1px solid #CBD5E1;background:#fff}

/* theme popup (same look as the Position popup) */
#dsel-thpop{display:none;position:fixed;left:196px;top:64px;width:300px;max-height:calc(100vh - 90px);flex-direction:column;background:#fff;border-radius:12px;box-shadow:0 10px 34px rgba(0,0,0,.42);z-index:100010;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif;color:#0F172A;overflow:hidden}
#dsel-thpop .dsel-tp-h{display:flex;align-items:center;justify-content:space-between;padding:11px 12px 8px 14px;border-bottom:1px solid #E2E8F0;flex:none}
#dsel-thpop .dsel-tp-h b{font-size:13px;font-weight:700;color:#0F172A}
#dsel-thpop .dsel-tp-x{width:24px;height:24px;padding:0;border:0;background:transparent;color:#475569;font-size:19px;line-height:1;cursor:pointer;border-radius:6px;min-height:0;box-shadow:none}
#dsel-thpop .dsel-tp-x:hover{background:#F1F5F9;color:#0F172A}
#dsel-thpop .dsel-tp-g{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:10px 12px 12px;overflow-y:auto;overscroll-behavior:contain}
#dsel-thpop .dsel-tp-i{position:relative;display:flex;flex-direction:column;align-items:flex-start;gap:6px;padding:9px;border:1px solid #E2E8F0;border-radius:10px;background:#fff;cursor:pointer;text-align:left;font-family:inherit;min-width:0;min-height:0;height:auto;box-shadow:none;text-transform:none;letter-spacing:0;color:#0F172A}
#dsel-thpop .dsel-tp-i:hover{border-color:#94A3B8;background:#F8FAFC}
#dsel-thpop .dsel-tp-i.on{border-color:#2563EB;background:#EFF6FF;box-shadow:0 0 0 1px #2563EB}
#dsel-thpop .dsel-tp-pill{display:flex;width:100%;height:20px;border-radius:6px;overflow:hidden;border:1px solid rgba(15,23,42,.1)}
#dsel-thpop .dsel-tp-pill span{flex:1}
#dsel-thpop .dsel-tp-n{font-size:12px;font-weight:700;line-height:1.2;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsel-thpop .dsel-tp-f{font-size:11px;line-height:1.2;color:#64748B;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsel-thpop .dsel-tp-ck{position:absolute;top:-6px;right:-6px;width:16px;height:16px;border-radius:50%;background:#2563EB;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center}
#dsel-thpop .dsel-tp-e{grid-column:1/-1;font-size:12px;color:#64748B;padding:8px 2px}
html.dsel-tp-load #dsp-ed-theme-drawer,html.dsel-tp-load #dsp-ed-theme-scrim{visibility:hidden!important;transition:none!important}
#dsel-thpop .dsel-tp-g{grid-auto-rows:max-content;align-content:start;flex:1 1 auto;min-height:0}
#dsel-thpop .dsel-tp-i{font-size:12px;line-height:1.2;overflow:visible}
#dsel-thpop .dsel-tp-i > span{flex:none;display:block}
#dsel-thpop .dsel-tp-i > .dsel-tp-pill{display:flex;height:20px;min-height:20px}
#dsel-thpop .dsel-tp-i > .dsel-tp-ck{display:flex}
#dsel-thpop .dsel-tp-h{cursor:move;user-select:none}
#dsel-thpop .dsel-tp-h b::before{content:"\2807";display:inline-block;margin-right:6px;color:#94A3B8;font-weight:400;letter-spacing:-2px}
#dsel-thpop.dsel-tp-drag{box-shadow:0 16px 44px rgba(0,0,0,.5);opacity:.97}
#dsel-thpop .dsel-tp-x{cursor:pointer}
</style>
<script id="ds-side-panels-js">
/* DS Edit Screen Side Panels — large screens only (mouse + wider than 1024px).
   In "Edit screen" mode: tools in a left panel (Theme right under Exit), the selected element's settings in a right panel,
   Previous / Next slide under the slide, and the bottom Theme / Pick a Design bar hidden.
   Phones and tablets keep the current layout. */
(function(){
if (window.__dsSidePanels) return; window.__dsSidePanels = 1;
var MQ = window.matchMedia ? window.matchMedia('(min-width: 1025px) and (hover: hover) and (pointer: fine)') : null;
var H = document.documentElement;
function wanted(){ return !!window.__dsSideForce || !!(MQ && MQ.matches); }
function focusOn(){ return H.classList.contains('dsel-focus'); }
function refit(){ try { window.dispatchEvent(new Event('resize')); } catch (e) {} }
function themeBtn(){
  var dock = document.getElementById('dsel-dock'); if (!dock) return;
  var row = dock.querySelector('.dsel-right') || dock;
  if (row.querySelector('.dsel-side-theme')) return;
  var ex = row.querySelector('[data-a="edscreen"]');
  var b = document.createElement('button');
  b.type = 'button'; b.className = 'dsel-b dsel-side-theme'; b.title = 'Change the colour theme of this presentation';
  b.innerHTML = '<span class="dsel-side-sw"></span>Theme';
  b.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); if (H.classList.contains('dsel-side') && typeof window.__dsThemePopToggle === 'function') { window.__dsThemePopToggle(); return; } var t = document.getElementById('dsp-ed-theme-trigger'); if (t) t.click(); });
  if (ex && ex.nextSibling) row.insertBefore(b, ex.nextSibling); else row.appendChild(b);
}
function nav(){
  var n = document.getElementById('dsel-sidenav');
  if (!n) {
    n = document.createElement('div'); n.id = 'dsel-sidenav';
    n.innerHTML = '<button type="button" class="dsel-sn-b" data-d="-1" title="Previous slide">&#8249; Prev</button><span class="dsel-sn-l"></span><button type="button" class="dsel-sn-b" data-d="1" title="Next slide">Next &#8250;</button>';
    n.addEventListener('click', function(ev){ var b = ev.target.closest('[data-d]'); if (!b) return; if (typeof window.dspPptNav === 'function') window.dspPptNav(+b.getAttribute('data-d')); setTimeout(label, 60); });
    document.body.appendChild(n);
  }
  label();
}
function label(){ var n = document.getElementById('dsel-sidenav'), s = document.getElementById('dsp-ppt-nav-lbl'); if (n && s) n.querySelector('.dsel-sn-l').textContent = s.textContent; }
/* the editor pads the slide area for its own top and bottom bars; in side mode those bars are side panels, so keep the area padding small */
var PAD = {t:'20px', b:'52px', l:'196px', r:'246px'};
function pad(){
  if (!H.classList.contains('dsel-side')) return;
  var a = document.getElementById('dsp-ppt-canvas-area'); if (!a) return;
  if (a.style.paddingTop !== PAD.t || a.style.paddingBottom !== PAD.b || a.style.paddingLeft !== PAD.l || a.style.paddingRight !== PAD.r) {
    a.style.setProperty('padding-top', PAD.t, 'important'); a.style.setProperty('padding-bottom', PAD.b, 'important');
    a.style.setProperty('padding-left', PAD.l, 'important'); a.style.setProperty('padding-right', PAD.r, 'important');
    clearTimeout(pad.t); pad.t = setTimeout(function(){ refit(); setTimeout(fit, 80); }, 60);
  }
}
/* fit the slide into the space between the two panels (the zoom buttons are hidden in this mode) */
function fit(){
  if (!H.classList.contains('dsel-side')) return;
  var a = document.getElementById('dsp-ppt-canvas-area'), m = document.getElementById('dsp-ppt-main-slide'), o = document.getElementById('dsp-ppt-slide-outer');
  if (!a || !m || !o || typeof window.dspZoom !== 'function') return;
  var aw = a.clientWidth - parseFloat(PAD.l) - parseFloat(PAD.r) - 24, ah = a.clientHeight - parseFloat(PAD.t) - parseFloat(PAD.b) - 12;
  var mw = m.offsetWidth || 900, mh = m.offsetHeight || 506; if (aw < 50 || ah < 50) return;
  var z = Math.max(0.3, Math.min(2, Math.floor(Math.min(aw / mw, ah / mh) * 100) / 100));
  var cur = parseFloat(((o.style.transform || '').match(/scale\(([\d.]+)/) || [])[1]) || 1;
  if (Math.abs(z - cur) > 0.015) window.dspZoom(z - cur);
}
function unpad(){ var a = document.getElementById('dsp-ppt-canvas-area'); if (!a) return; ['padding-left', 'padding-right'].forEach(function(p){ a.style.removeProperty(p); }); a.__dselPad = 0; a.style.removeProperty('padding-top'); a.style.removeProperty('padding-bottom'); }
var last = null;
function apply(){
  var on = wanted() && focusOn();
  if (on !== last) { H.classList.toggle('dsel-side', on); if (!on && last) unpad(); last = on; setTimeout(refit, 30); setTimeout(refit, 250); }
  if (on) { themeBtn(); nav(); pad(); }
  if (on && on !== fit.was) setTimeout(fit, 300);
  if (!on && fit.was && typeof window.dspZoom === 'function') { var o0 = document.getElementById('dsp-ppt-slide-outer'), c0 = parseFloat(((o0 && o0.style.transform) || '').match(/scale\(([\d.]+)/) ? o0.style.transform.match(/scale\(([\d.]+)/)[1] : 1); if (c0 && Math.abs(1 - c0) > 0.015) window.dspZoom(1 - c0); }
  fit.was = on;
}
new MutationObserver(apply).observe(H, {attributes:true, attributeFilter:['class']});
if (MQ) { if (MQ.addEventListener) MQ.addEventListener('change', apply); else if (MQ.addListener) MQ.addListener(apply); }
var mo = new MutationObserver(function(){ if (H.classList.contains('dsel-side')) { themeBtn(); label(); } });
var mp = new MutationObserver(pad);
function watch(){ var ar = document.getElementById('dsp-ppt-canvas-area'); if (ar && !ar.__dsSide) { ar.__dsSide = 1; mp.observe(ar, {attributes:true, attributeFilter:['style']}); }
  var d = document.getElementById('dsel-dock'), s = document.getElementById('dsp-ppt-nav-lbl'); if (d && !d.__dsSide) { d.__dsSide = 1; mo.observe(d, {childList:true, subtree:true}); } if (s && !s.__dsSide) { s.__dsSide = 1; mo.observe(s, {childList:true, characterData:true, subtree:true}); } }
setInterval(function(){ watch(); apply(); }, 1000);
window.addEventListener('resize', function(){ clearTimeout(fit.r); fit.r = setTimeout(fit, 150); });
apply();
})();

/* Theme popup for the side-panel Edit screen: a small white panel (same style as Position) listing the themes with a colour pill and font. */
(function(){
if (window.__dsThemePop) return; window.__dsThemePop = 1;
var H = document.documentElement;
function cards(){ return [].slice.call(document.querySelectorAll('#dsp-ed-drawer-row .dsp-th-card')); }
function esc(s){ return String(s || '').replace(/[&<>"]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }
function info(c){
  var sw = c.querySelector('.dsp-th-swatch'), b = c.querySelector('.b'), nm = c.querySelector('.dsp-th-name');
  var name = nm ? nm.childNodes[0] && nm.childNodes[0].nodeType === 3 ? nm.childNodes[0].nodeValue : nm.textContent.replace('✓', '') : '';
  var ff = (b && b.style.fontFamily) || '';
  var font = c.getAttribute('data-th-font') || (ff.split(',')[0] || '').replace(/['"]/g, '').trim();
  return { name: (name || '').trim(), font: font, ff: ff, acc: (b && b.style.color) || '#2563EB', bg: (sw && sw.style.background) || '#fff', sel: c.classList.contains('dsp-th-sel') };
}
function pop(){
  var p = document.getElementById('dsel-thpop');
  if (!p) {
    p = document.createElement('div'); p.id = 'dsel-thpop';
    p.innerHTML = '<div class="dsel-tp-h"><b>Theme</b><button type="button" class="dsel-tp-x" title="Close">&times;</button></div><div class="dsel-tp-g"></div>';
    p.addEventListener('click', function(ev){
      if (ev.target.closest('.dsel-tp-x')) { hide(); return; }
      var it = ev.target.closest('[data-i]'); if (!it) return;
      var c = cards()[+it.getAttribute('data-i')]; if (!c) return;
      c.click();
      setTimeout(fill, 150); setTimeout(fill, 600);
    });
    document.body.appendChild(p);
  }
  return p;
}
function fill(){
  var p = document.getElementById('dsel-thpop'); if (!p || p.style.display === 'none') return;
  var g = p.querySelector('.dsel-tp-g');
  g.innerHTML = cards().map(function(c, i){
    var t = info(c);
    return '<button type="button" class="dsel-tp-i' + (t.sel ? ' on' : '') + '" data-i="' + i + '" title="' + esc(t.name) + '">' +
      '<span class="dsel-tp-pill"><span style="background:' + esc(t.acc) + '"></span><span style="background:' + esc(t.bg) + '"></span></span>' +
      '<span class="dsel-tp-n">' + esc(t.name) + '</span>' +
      '<span class="dsel-tp-f" style="font-family:' + esc(t.ff) + '">' + esc(t.font || ' ') + '</span>' +
      (t.sel ? '<span class="dsel-tp-ck">✓</span>' : '') + '</button>';
  }).join('') || '<div class="dsel-tp-e">Themes are loading…</div>';
}
/* the editor only fills its theme list when its drawer opens, so open and close it out of sight first */
var loading = 0;
function load(){
  var t = document.getElementById('dsp-ed-theme-trigger'), x = document.getElementById('dsp-ed-drawer-close'); if (!t || !x) return;
  H.classList.add('dsel-tp-load'); loading = 1; try { if (typeof t.onclick === 'function') t.onclick.call(t, new MouseEvent('click')); else t.click(); } finally { loading = 0; } x.click();
  clearTimeout(load.t); load.t = setTimeout(function(){ H.classList.remove('dsel-tp-load'); }, 700);
}
var MQ = window.matchMedia ? window.matchMedia('(min-width: 1025px) and (hover: hover) and (pointer: fine)') : null;
function big(){ return !!window.__dsSideForce || !!(MQ && MQ.matches); }
var mode = '';
/* place it beside the left panel in Edit screen, or just above the bottom-bar Theme button otherwise */
function place(p, btn){
  p.style.top = p.style.bottom = p.style.left = p.style.maxHeight = '';
  if (H.classList.contains('dsel-side') || !btn) { mode = 'side'; return; }
  mode = 'bar';
  var r = btn.getBoundingClientRect();
  p.style.left = Math.max(8, Math.min(r.left, innerWidth - 308)) + 'px';
  p.style.top = 'auto';
  p.style.bottom = Math.max(8, innerHeight - r.top + 8) + 'px';
  p.style.maxHeight = Math.max(220, r.top - 70) + 'px';
}
/* drag the popup by its header; it opens where it was last left */
var moved = null;
function clampTo(p, x, y){
  var w = p.offsetWidth || 300, h = p.offsetHeight || 200;
  x = Math.max(8, Math.min(x, innerWidth - w - 8)); y = Math.max(8, Math.min(y, innerHeight - 60));
  p.style.left = x + 'px'; p.style.top = y + 'px'; p.style.bottom = 'auto';
  p.style.maxHeight = Math.max(220, innerHeight - y - 16) + 'px';
}
document.addEventListener('mousedown', function(ev){
  var h = ev.target.closest && ev.target.closest('#dsel-thpop .dsel-tp-h'); if (!h || ev.button !== 0 || ev.target.closest('.dsel-tp-x')) return;
  var p = document.getElementById('dsel-thpop'), r = p.getBoundingClientRect(), dx = ev.clientX - r.left, dy = ev.clientY - r.top;
  ev.preventDefault(); p.classList.add('dsel-tp-drag');
  function mv(e){ clampTo(p, e.clientX - dx, e.clientY - dy); }
  function up(){ document.removeEventListener('mousemove', mv, true); document.removeEventListener('mouseup', up, true); p.classList.remove('dsel-tp-drag'); var b = p.getBoundingClientRect(); moved = {x: b.left, y: b.top}; }
  document.addEventListener('mousemove', mv, true); document.addEventListener('mouseup', up, true);
}, true);
function show(btn){ load(); var p = pop(); place(p, btn); if (moved) { p.style.display = 'flex'; clampTo(p, moved.x, moved.y); } p.style.display = 'flex'; fill(); setTimeout(fill, 250); setTimeout(fill, 800); var on = p.querySelector('.dsel-tp-i.on'); if (on) on.scrollIntoView({block:'nearest'}); }
function hide(){ var p = document.getElementById('dsel-thpop'); if (p) p.style.display = 'none'; }
function open(){ var p = document.getElementById('dsel-thpop'); return p && p.style.display !== 'none'; }
window.__dsThemePopToggle = function(btn){ if (open()) hide(); else show(btn); };
/* the bottom-bar Theme button opens the same popup on large screens (phones and tablets keep the drawer) */
document.addEventListener('click', function(ev){
  if (loading) return;
  var t = ev.target.closest && ev.target.closest('#dsp-ed-theme-trigger'); if (!t || !big()) return;
  ev.preventDefault(); ev.stopImmediatePropagation();
  window.__dsThemePopToggle(H.classList.contains('dsel-side') ? null : t);
}, true);
document.addEventListener('mousedown', function(ev){
  if (!open()) return;
  if (ev.target.closest('#dsel-thpop') || ev.target.closest('.dsel-side-theme') || ev.target.closest('#dsp-ed-theme-trigger')) return;
  hide();
}, true);
document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape' && open()) hide(); });
new MutationObserver(function(){ if (open() && (mode === 'side') !== H.classList.contains('dsel-side')) hide(); }).observe(H, {attributes:true, attributeFilter:['class']});
window.addEventListener('resize', function(){ if (open() && mode === 'bar' && !moved) hide(); });
})();
</script>
<?php
}, 1160);
