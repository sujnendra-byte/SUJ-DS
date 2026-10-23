
/* A panel behind every chart and diagram on a design-template slide.
   - colour: the deck's accent colour, very light (glass templates: the template's own glass), so it follows the theme
   - per slide: slide.chart_bg = 'none' switches the panel off; slide.chart_bg_r = corner radius (0 = sharp corners,
     up to 48; not set = the template's own roundness)
   - the panel is a normal rectangle of the slide, so the PowerPoint / PDF / PNG / JPG downloads show it too
   - editor: a "Chart background" block inside Edit chart / Edit diagram: on / off and a slider for the roundness */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || !D.u || D.__chartBg) return; D.__chartBg = 1;
var u = D.u, rp = D.renderPage, W = 1280, H = 720, PAD = 14, depth = 0;
var zmap = typeof WeakMap === 'function' ? new WeakMap() : null, zidx = {};
function hx(c){ c = String(c || '').replace('#', ''); return /^[0-9a-fA-F]{6}$/.test(c) ? '#' + c.toUpperCase() : null; }
var RMAX = 48;
function modeOf(s){ return s && s.chart_bg === 'none' ? 'none' : 'on'; }
function defR(T){ var S = (T && T.s && T.s.card) || {}; return Math.min(RMAX, S.r != null && S.r >= 8 ? Math.round(S.r) : 18); }
function radOf(s, T){
  var v = s ? s.chart_bg_r : null;
  if (v != null && v !== '' && isFinite(+v)) return Math.max(0, Math.min(RMAX, Math.round(+v)));
  if (s && s.chart_bg === 'sharp') return 0;
  return defR(T);
}
function DG(){ return window.DS_DG || null; }

/* every diagram design tells us the exact box it draws in */
function hookDesigns(){
  var G = DG(); if (!G || !G.designs) return;
  var ks = Object.keys(G.designs); if (G.__cbgN === ks.length) return; G.__cbgN = ks.length;
  ks.forEach(function(k){
    var d = G.designs[k]; if (!d || typeof d.draw !== 'function' || d.draw.__cbg) return;
    var o = d.draw;
    d.draw = function(K, z){
      try { if (depth > 0 && K && K.C && typeof K.C.add === 'function' && z && z.w > 0 && z.h > 0) K.C.add({t:'rect', x:z.x, y:z.y, w:z.w, h:z.h, fill:'#000000', op:0, cbg:1}); } catch (e) {}
      return o.apply(this, arguments);
    };
    d.draw.__cbg = 1;
  });
}
function isGlassRect(e){ return e && e.t === 'rect' && !e.cbg && e.fill && !e.grad && e.op != null && e.op > 0.02 && e.op < 0.6 && e.w >= 140 && e.h >= 44 && !(e.w >= W - 8 && e.h >= H - 8); }
function look(T, page){
  var R = T && T.__rec, P = (T && T.pal) || {}, S = (T && T.s && T.s.card) || {};
  var pb = page.bg || {}, bg = null;                          /* the colour this slide really has (some layouts are inverted) */
  if (typeof pb.fill === 'string') bg = hx(pb.fill);
  else if (pb.grad && pb.grad.stops && pb.grad.stops.length) { var g0 = hx(pb.grad.stops[0][1]), g1 = hx(pb.grad.stops[pb.grad.stops.length - 1][1]); if (g0 && g1) bg = hx(u.mix(g0, g1, 0.5)); }
  bg = bg || hx(R ? R.bg : P.bg) || '#FFFFFF';
  var ac = hx(R ? R.acx : P.accent) || '#2563EB', dark = u.lum(bg) < 0.5;
  var rr = 0;
  if ((T && T.frost) || S.kind === 'glass') {                 /* glass template: the same glass as its cards */
    var g = null; for (var i = 0; i < page.els.length && !g; i++) if (isGlassRect(page.els[i])) g = page.els[i];
    if (g) return {glass:1, r:rr, fill:g.fill, op:g.op, line:g.line, lop:g.lop, lw:g.lw, shadow:g.shadow};
    return {glass:1, r:rr, fill:'#FFFFFF', op:dark ? 0.12 : 0.4, line:'#FFFFFF', lop:dark ? 0.3 : 0.7, lw:1.2};
  }
  var op = dark ? 0.13 : 0.07;
  return {r:rr, fill:ac, op:op, line:ac, lop:dark ? 0.38 : 0.24, lw:1.2, bg:bg, under:u.mix(bg, ac, op)};
}
function apply(page, T, slide, idx){
  if (!page || !page.els || !page.els.slice) return;
  var els = page.els, i, e, zones = [];
  for (i = 0; i < els.length; i++) {                           /* a real chart: the panel sits right behind it */
    e = els[i];
    if (e && e.t === 'chart' && !e.cbgd && e.w > 0 && e.h > 0) { e.cbgd = 1; els.splice(i, 0, {t:'rect', x:e.x, y:e.y, w:e.w, h:e.h, fill:'#000000', op:0, cbg:1}); i++; }
  }
  var marks = []; for (i = 0; i < els.length; i++) if (els[i] && els[i].cbg === 1) marks.push(i);
  if (!marks.length) { if (zmap && slide) try { zmap['delete'](slide); } catch (e1) {} if (idx != null) delete zidx[idx]; return; }
  var m = modeOf(slide), L = look(T, page);
  marks.forEach(function(k){
    var b = els[k], x0 = Math.max(12, b.x - PAD), y0 = Math.max(12, b.y - PAD), x1 = Math.min(W - 12, b.x + b.w + PAD), y1 = Math.min(H - 12, b.y + b.h + PAD);
    b.x = x0; b.y = y0; b.w = x1 - x0; b.h = y1 - y0; zones.push({x:x0, y:y0, w:b.w, h:b.h});
    if (m === 'none') return;
    b.fill = L.fill; b.op = L.op; b.r = radOf(slide, T); b.cbg = 2;
    if (L.line) { b.line = L.line; b.lop = L.lop; b.lw = L.lw; }
    if (L.shadow) b.shadow = L.shadow;
    if (L.glass) return;
    /* parts that were painted in the page colour (the hole of a donut, the gap between slices) take the panel's colour */
    for (var j = k + 1; j < els.length; j++) {
      var q = els[j]; if (!q || q.cbg) continue;
      if (q.x + 1 < x0 || q.y + 1 < y0 || q.x + q.w > x1 + 1 || q.y + q.h > y1 + 1) continue;
      if (q.t === 'chart') { if (hx(q.bgc) === L.bg) q.bgc = L.under; continue; }
      if ((q.t === 'rect' || q.t === 'ell' || q.t === 'shape' || q.t === 'path' || q.t === 'ring') && !q.grad && (q.op == null || q.op >= 1)) {
        if (q.fill && hx(q.fill) === L.bg) q.fill = L.under;
        if (q.t === 'path' && q.stroke && hx(q.stroke) === L.bg) q.stroke = L.under;
        if (q.line && hx(q.line) === L.bg) q.line = L.under;
      }
    }
  });
  if (m === 'none') page.els = els.filter(function(q){ return !(q && q.cbg === 1); });
  var z = zones[0]; zones.forEach(function(o){ var x1 = Math.max(z.x + z.w, o.x + o.w), y1 = Math.max(z.y + z.h, o.y + o.h); z = {x:Math.min(z.x, o.x), y:Math.min(z.y, o.y)}; z.w = x1 - z.x; z.h = y1 - z.y; });
  if (zmap && slide) try { zmap.set(slide, z); } catch (e2) {}
  if (idx != null) zidx[idx] = {t:String((slide && slide.title) || ''), z:z};
}
D.renderPage = function(T, slide, vid, idx, deck){
  var p; hookDesigns(); depth++;
  try { p = rp.apply(this, arguments); } finally { depth--; }
  try { apply(p, T, slide, idx); } catch (e) { if (window.console) console.warn('[DS chart background]', e); }
  return p;
};
D.chartBg = {modeOf:modeOf, radOf:radOf, defR:defR, max:RMAX, zoneOf:function(s, idx){ var z = zmap && s ? zmap.get(s) : null; if (!z && idx != null && zidx[idx] && s && zidx[idx].t === String(s.title || '')) z = zidx[idx].z; return z || null; }};

/* ---------------- editor: "Chart background" inside Edit chart / Edit diagram ---------------- */
function arrays(){
  var a = [], S = window.S, G = window.G;
  if (S && S.data && Array.isArray(S.data.slides)) a.push(S.data.slides);
  if (G && G.pptData && Array.isArray(G.pptData.slides) && a.indexOf(G.pptData.slides) < 0) a.push(G.pptData.slides);
  return a;
}
function curIdx(){ var S = window.S; if (S && typeof S.idx === 'number' && S.idx >= 0) return S.idx; return (window.G && window.G.activeSlideIdx) || 0; }
function curSlide(){ var a = arrays(); return a.length ? a[0][curIdx()] : null; }
function isChart(s){ return !!(s && (s.dg || s.chart || s.type === 'chart')); }
function curT(){ try { var A = window.DS_TPL_ACTIVE ? window.DS_TPL_ACTIVE() : null; return A && A.id && D.reg ? D.reg[A.id] : null; } catch (e) { return null; } }
var svT = 0, rfT = 0;
function save(){
  clearTimeout(svT);
  svT = setTimeout(function(){
    var G = window.G; if (!G || !G.pptData) return;
    if (G.pptId) {
      var url = (window.PURL ? String(window.PURL).replace('/generate-ppt', '/save-ppt') : '/wp-json/ds/v1/save-ppt'), dz = document.getElementById('dsp-design');
      try {
        fetch(url, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce':window.NONCE || window.DSP_NONCE || ''},
          body:JSON.stringify({ppt_id:G.pptId, title:G.pptTitle || 'Presentation', ppt_data:G.pptData, design:(dz && dz.value) || 'navy-pro'})})['catch'](function(){});
      } catch (e) {}
      return;
    }
    if (typeof window.dspSaveDraft === 'function') try { window.dspSaveDraft(); } catch (e2) {}
  }, 1200);
}
/* write {bg, r} to the current slide (or to every chart slide) in both copies of the deck */
function put(v, all){
  var i = curIdx();
  arrays().forEach(function(a){
    a.forEach(function(s, k){
      if (!s || (k !== i && !(all && isChart(s)))) return;
      if (v.bg === 'none') s.chart_bg = 'none'; else delete s.chart_bg;
      if (v.r == null) delete s.chart_bg_r; else s.chart_bg_r = v.r;
    });
  });
}
function redraw(){
  clearTimeout(rfT);
  rfT = setTimeout(function(){
    var i = curIdx();
    try { if (D.refreshEditor) D.refreshEditor(); } catch (e) {}
    try { if (window.dspRenderThumbs) window.dspRenderThumbs(); } catch (e1) {}
    try { var S = window.S; if (window.applyCanvas && S) window.applyCanvas(i, (S.choices && S.choices['slide_' + i]) || 'v1'); } catch (e2) {}
  }, 140);
}
/* the two editors redraw their own preview on any "input" of one of their fields */
function nudge(host){
  try {
    var el = host === 'dg' ? document.querySelector('#dsdg-eform input.dsdg-in') : (document.getElementById('dsch-angle') || document.querySelector('#dsch-rows .dsch-in'));
    if (el) el.dispatchEvent(new Event('input', {bubbles:false}));
  } catch (e) {}
}
function css(){
  if (document.getElementById('dscbg-css')) return;
  var st = document.createElement('style'); st.id = 'dscbg-css';
  st.textContent = '.dscbg-sec{margin:14px 0 4px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}'
    + '.dscbg-sec .dscbg-l{font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:#8b93a7;margin:0 0 7px;display:flex;align-items:center;justify-content:space-between;}'
    + '.dscbg-sec .dscbg-row{display:flex;align-items:center;gap:10px;min-height:30px;}'
    + '.dscbg-sec .dscbg-sw{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;font-weight:600;color:#e8eaed;user-select:none;}'
    + '.dscbg-sec .dscbg-sw input{position:absolute;opacity:0;width:0;height:0;}'
    + '.dscbg-sec .dscbg-sw i{display:block;width:34px;height:18px;border-radius:9px;background:#3a4150;position:relative;transition:background .15s;}'
    + '.dscbg-sec .dscbg-sw i:after{content:"";position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;transition:left .15s;}'
    + '.dscbg-sec .dscbg-sw input:checked + i{background:#1a73e8;}.dscbg-sec .dscbg-sw input:checked + i:after{left:18px;}'
    + '.dscbg-sec .dscbg-sw input:focus-visible + i{outline:2px solid #8ab4f8;outline-offset:2px;}'
    + '.dscbg-sec .dscbg-all{all:unset;box-sizing:border-box;margin-left:auto;padding:5px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.16);color:#8ab4f8;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;}'
    + '.dscbg-sec .dscbg-all:hover{background:rgba(138,180,248,.12);}'
    + '.dscbg-sec .dscbg-rng{display:flex;align-items:center;gap:10px;}'
    + '.dscbg-sec .dscbg-rng input[type=range]{flex:1;min-width:0;accent-color:#1a73e8;}'
    + '.dscbg-sec .dscbg-rng b{width:52px;text-align:right;font-size:12px;color:#e8eaed;font-weight:700;}'
    + '.dscbg-sec .dscbg-ends{display:flex;justify-content:space-between;font-size:10px;color:#8b93a7;margin:2px 62px 0 0;}'
    + '.dscbg-sec.off .dscbg-rwrap{opacity:.4;pointer-events:none;}';
  document.head.appendChild(st);
}
var SNAP = null;                                               /* the values when the editor was opened: Cancel puts them back */
function modalOn(id){ var m = document.getElementById(id); return !!(m && m.classList.contains('on')); }
function rLabel(r){ return r <= 0 ? 'Sharp' : r + ' px'; }
function build(host){
  var s = curSlide(), T = curT(); if (!s || !T) return null;
  css();
  var on = modeOf(s) !== 'none', r = radOf(s, T), n = 0; (arrays()[0] || []).forEach(function(x){ if (isChart(x)) n++; });
  var d = document.createElement('div'); d.className = 'dscbg-sec' + (on ? '' : ' off'); d.id = 'dscbg-sec'; d.setAttribute('data-host', host);
  d.innerHTML = '<div class="dscbg-l"><span>Chart background</span></div>'
    + '<div class="dscbg-row"><label class="dscbg-sw"><input type="checkbox" id="dscbg-on"' + (on ? ' checked' : '') + '/><i></i><span id="dscbg-onl">' + (on ? 'On' : 'Off') + '</span></label>'
    + (n > 1 ? '<button type="button" class="dscbg-all" id="dscbg-all" title="Use this background setting on every chart slide of the deck">Use on all chart slides</button>' : '') + '</div>'
    + '<div class="dscbg-rwrap"><div class="dscbg-l" style="margin-top:10px"><span>Corner roundness</span></div>'
    + '<div class="dscbg-rng"><input type="range" min="0" max="' + RMAX + '" step="1" id="dscbg-r" value="' + r + '" aria-label="Corner roundness of the chart background"/><b id="dscbg-rv">' + rLabel(r) + '</b></div>'
    + '<div class="dscbg-ends"><span>Sharp</span><span>Round</span></div></div>';
  function now(){ var cb = d.querySelector('#dscbg-on'), rg = d.querySelector('#dscbg-r'); return {bg:cb.checked ? '' : 'none', r:parseInt(rg.value, 10) || 0}; }
  function changed(all){ put(now(), all); if (SNAP) SNAP.dirty = 1; nudge(host); redraw(); }
  d.querySelector('#dscbg-on').addEventListener('change', function(){
    d.classList.toggle('off', !this.checked); d.querySelector('#dscbg-onl').textContent = this.checked ? 'On' : 'Off'; changed(false);
  });
  d.querySelector('#dscbg-r').addEventListener('input', function(){ d.querySelector('#dscbg-rv').textContent = rLabel(parseInt(this.value, 10) || 0); changed(false); });
  var al = d.querySelector('#dscbg-all');
  if (al) al.addEventListener('click', function(ev){
    ev.preventDefault(); ev.stopPropagation(); changed(true);
    if (window.dspToast) try { window.dspToast('Chart background used on all chart slides', 'success'); } catch (e) {}
  });
  ['keydown', 'mousedown'].forEach(function(n2){ d.addEventListener(n2, function(ev){ ev.stopPropagation(); }); });
  return d;
}
function snap(host){
  if (SNAP && SNAP.host === host && SNAP.idx === curIdx()) return;
  SNAP = {host:host, idx:curIdx(), list:arrays().map(function(a){ return a.map(function(s){ return s ? {bg:s.chart_bg, r:s.chart_bg_r} : null; }); })};
}
function restore(){
  if (!SNAP) return;
  var L = SNAP.list; SNAP = null;
  arrays().forEach(function(a, k){ var o = L[k]; if (!o) return; a.forEach(function(s, i){ var v = o[i]; if (!s || !v) return;
    if (v.bg == null) delete s.chart_bg; else s.chart_bg = v.bg;
    if (v.r == null) delete s.chart_bg_r; else s.chart_bg_r = v.r; }); });
  redraw();
}
function inject(){
  var tOn = false; try { tOn = !!(window.DS_TPL_ACTIVE && window.DS_TPL_ACTIVE()); } catch (e) {}
  var chOn = modalOn('dsch-modal'), dgOn = modalOn('dsdg-ed');
  if (!chOn && !dgOn) { if (SNAP) { var dirty = SNAP.dirty; SNAP = null; if (dirty) save(); } return; }   /* closed with Apply: keep and save */
  if (!tOn) return;
  var sec = document.getElementById('dscbg-sec');
  if (chOn) {
    var rows = document.getElementById('dsch-rows'), ttl = document.getElementById('dsch-title');
    if (!rows || !ttl || !/Edit chart/.test(ttl.textContent || '')) return;          /* not while a new chart is being made */
    var lab = rows.previousElementSibling; if (!lab || !lab.parentNode) return;
    if (sec && sec.parentNode === lab.parentNode) return;
    if (sec) sec.remove();
    snap('ch'); var d = build('ch'); if (d) lab.parentNode.insertBefore(d, lab);
    return;
  }
  var pv = document.getElementById('dsdg-eprev'); if (!pv || !pv.parentNode) return;
  if (sec && sec.parentNode === pv.parentNode) return;
  if (sec) sec.remove();
  snap('dg'); var d2 = build('dg'); if (d2) pv.parentNode.appendChild(d2);
}
/* Cancel / X / click beside the window / Esc: the background goes back to what it was */
document.addEventListener('click', function(ev){
  var t = ev.target && ev.target.closest ? ev.target.closest('#dsch-cancel,#dsch-x,#dsdg-ecancel,#dsdg-ex') : null;
  if (t) restore();
}, true);
document.addEventListener('mousedown', function(ev){ var t = ev.target; if (t && (t.id === 'dsch-modal' || t.id === 'dsdg-ed') && SNAP) restore(); }, true);
document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape' && SNAP && (modalOn('dsch-modal') || modalOn('dsdg-ed'))) restore(); }, true);
setInterval(function(){ try { inject(); } catch (e) {} }, 250);
try {
  new MutationObserver(function(ms){
    for (var i = 0; i < ms.length; i++) { var t = ms[i].target; if (t && (t.id === 'dsch-body' || t.id === 'dsch-modal' || t.id === 'dsdg-ed' || (t.closest && t.closest('#dsdg-ed')))) { try { inject(); } catch (e) {} return; } }
  }).observe(document.body, {childList:true, subtree:true, attributes:true, attributeFilter:['class']});
} catch (e) {}
})();
