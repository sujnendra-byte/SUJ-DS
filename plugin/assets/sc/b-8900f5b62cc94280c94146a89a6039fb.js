try{

(function(){
  var st = null;                       /* {idx, n} while the "Add slide" screen is showing */
  function $(id){ return document.getElementById(id); }
  /* change a class ONLY when it really changes: the editor re-renders its layout panel on every
     attribute change of the slide list, even a no-op one */
  function cls(el, name, on){ if (el && el.classList.contains(name) !== !!on) el.classList.toggle(name, !!on); }
  function items(th){ return th.querySelectorAll('.dsp-thumb-item'); }
  function nSlides(){ var S = window.S; return S && S.data && S.data.slides ? S.data.slides.length : -1; }
  function curIdx(){ var S = window.S; if (S && typeof S.idx === 'number') return S.idx; return window.G && typeof G.activeSlideIdx === 'number' ? G.activeSlideIdx : -1; }
  function onTile(th, e){
    if (e.target !== th) return false;
    var it = items(th), last = it[it.length - 1]; if (!last) return false;
    return e.clientY > last.getBoundingClientRect().bottom + 10;
  }
  function selectLast(th){ var it = items(th), last = it[it.length - 1]; if (last) (last.querySelector('.dsp-thumb-box') || last).click(); }
  function openPopup(){
    var th = $('dsp-ppt-thumbs'); if (!th) return;
    if (curIdx() !== items(th).length - 1) selectLast(th);
    setTimeout(function(){ if (typeof window.dspAddSlide === 'function') window.dspAddSlide(); }, 120);
  }
  function hidePH(){ st = null; var p = $('dsat-ph'); if (p) p.remove(); var th = $('dsp-ppt-thumbs'); cls(th, 'dsat-sel', false); }
  /* the white screen sits NEXT TO the main slide (not inside it): the editor re-renders the
     slide whenever something is added inside it, which would loop forever */
  function drawPH(){
    var main = $('dsp-ppt-main-slide'); if (!main || !st) return;
    var host = main.parentNode; if (!host) return;
    var p = $('dsat-ph');
    if (!p || p.parentNode !== host) {
      if (p) p.remove();
      if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
      p = document.createElement('div'); p.id = 'dsat-ph';
      p.innerHTML = '<button type="button" class="dsat-ph-btn" title="Add a new slide">+</button><div class="dsat-ph-t">Add slide</div>';
      p.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); if (e.target.closest('.dsat-ph-btn,.dsat-ph-t')) openPopup(); });
      host.appendChild(p);
    }
    var hr = host.getBoundingClientRect(), mr = main.getBoundingClientRect();
    var css = 'left:' + (mr.left - hr.left + host.scrollLeft).toFixed(1) + 'px;top:' + (mr.top - hr.top + host.scrollTop).toFixed(1) + 'px;width:' + mr.width.toFixed(1) + 'px;height:' + mr.height.toFixed(1) + 'px';
    if (p.getAttribute('data-z') !== css) { p.setAttribute('data-z', css); p.style.cssText = css; }
  }
  function showPH(){
    var th = $('dsp-ppt-thumbs'); if (!th) return;
    var target = items(th).length - 1, tries = 0;
    cls(th, 'dsat-sel', true);
    setTimeout(function(){ if (curIdx() !== target) selectLast(th); }, 0);
    (function wait(){                                     /* show once the last slide is really selected */
      if (curIdx() === target) { st = {idx:target, n:nSlides()}; cls(th, 'dsat-sel', true); drawPH(); return; }
      if (++tries === 20) selectLast(th);
      if (tries < 60) setTimeout(wait, 50); else cls(th, 'dsat-sel', false);
    })();
  }
  function bind(th){
    if (th.__dsat) return; th.__dsat = 1;
    th.addEventListener('mousedown', function(e){ if (e.button === 0 && onTile(th, e)) { e.preventDefault(); e.stopPropagation(); showPH(); } }, true);
    th.addEventListener('click', function(e){
      if (onTile(th, e)) { e.preventDefault(); e.stopPropagation(); return; }
      if (st && e.target !== th) hidePH();                 /* a real slide was picked */
    }, true);
    th.addEventListener('mousemove', function(e){ cls(th, 'dsat-hov', onTile(th, e)); });
    th.addEventListener('mouseleave', function(){ cls(th, 'dsat-hov', false); });
  }
  function ensure(){
    var th = $('dsp-ppt-thumbs'); if (!th) { if (st) hidePH(); return; }
    var it = items(th);
    if (!it.length) { cls(th, 'dsat-on', false); return; }
    bind(th); cls(th, 'dsat-on', true);
    var box = it[0].querySelector('.dsp-thumb-box');
    if (box && box.offsetWidth) {
      var w = box.offsetWidth + 'px', l = (box.getBoundingClientRect().left - it[0].getBoundingClientRect().left) + 'px';
      if (th.style.getPropertyValue('--dsat-w') !== w) th.style.setProperty('--dsat-w', w);
      if (th.style.getPropertyValue('--dsat-l') !== l) th.style.setProperty('--dsat-l', l);
    }
    if (st) {
      if (nSlides() !== st.n || curIdx() !== st.idx) hidePH();   /* slide added, or user moved to another slide */
      else { cls(th, 'dsat-sel', true); drawPH(); }
    }
  }
  setInterval(ensure, 300);
  ensure();
})();

}catch(e){if(window.console)console.error(e);}
try{

/* Template 30 — Azure Prism : bright blue gradient, slanted light prisms, navy glass panels and white cards (corporate proposal) */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
var NAVY = '#062B6E', DEEP = '#04204F', BLUE = '#0B63CE', SKY = '#4DB4FF', ICE = '#DCEEFF', MUTE = '#C9E0FA';
var BG = {ang:125, stops:[[0, '#0A3A8F'], [48, '#0B66D2'], [100, '#3FA9F5']]};
var P0 = {bg:'#0B5BC2', text:'#FFFFFF', title:'#FFFFFF', muted:MUTE, accent:'#FFFFFF', accent2:SKY, card:NAVY, line:'#7FB6F2', onAccent:NAVY};
var S = {
  margin:70, series:[SKY, '#FFFFFF', '#9ED3FF', '#1E88E5', '#BFE3FF', '#0D47A1'],
  header:{kind:'left', top:74, tMax:46, tMin:26, sMax:17, sMin:13},
  card:{kind:'glass', r:14, fill:DEEP, op:0.5, line:'#FFFFFF', lop:0.28, lw:1.2, dark:true, tc:'#FFFFFF', mc:MUTE,
        hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:NAVY, mc:'#3B5B8C'}},
  icon:{kind:'circle', bg:'#FFFFFF', fg:BLUE}, num:{kind:'square', bg:'#FFFFFF', fg:NAVY, r:8},
  conc:{kind:'box', fill:'#FFFFFF', op:0.14, line:'#FFFFFF', lw:1, r:12, label:'Key takeaway', lc:'#FFFFFF', tc:'#FFFFFF'},
  footer:{fn:foot}, imgFramed:true, imgR:16, bodyGap:28,
  imgPh:{grad:{ang:135, stops:[[0, '#8CC8FF'], [100, '#0B4FA8']]}}
};

/* ---------- motif ---------- */
function prism(C, x, w, op){ C.shape('parallelogram', x, -30, w, H + 60, {fill:'#FFFFFF', op:op}); }
function panel(C, x, y, w, h, o){
  o = o || {};
  if (o.white) { C.rect(x, y, w, h, {fill:'#FFFFFF', r:o.r != null ? o.r : 14, shadow:0.14}); return {tc:NAVY, mc:'#3B5B8C', ac:BLUE}; }
  C.rect(x, y, w, h, {fill:DEEP, op:(o.op != null ? o.op : 0.5) * 0.7, r:o.r != null ? o.r : 14});
  C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.26], [45, '#FFFFFF', 0.06], [100, '#FFFFFF', 0.12]]}, line:'#FFFFFF', lop:0.5, lw:1.2, r:o.r != null ? o.r : 14});
  return {tc:'#FFFFFF', mc:MUTE, ac:SKY};
}
function foot(C){
  var M = 70, y = H - 36, n = C.total || 1, f = C.cover;
  var left = f.website || f.company || '';
  if (left) C.text(M, y, 420, 18, left, {font:'body', size:12, wt:600, color:'#FFFFFF', op:0.85, lh:1, va:'m'});
  var bw = 150, bx = W - M - bw - 46;
  C.rect(bx, y + 7, bw, 4, {fill:'#FFFFFF', op:0.28, r:2});
  C.rect(bx, y + 7, Math.max(8, bw * Math.min(1, (C.i + 1) / n)), 4, {fill:'#FFFFFF', r:2});
  C.text(W - M - 40, y, 40, 18, pad2(C.i + 1), {font:'body', size:12, wt:700, color:'#FFFFFF', align:'r', lh:1, va:'m'});
}
function bg(C, kind){
  C.bg({grad:BG});
  var odd = (C.i || 0) % 2;
  prism(C, odd ? 120 : 760, 330, 0.08);
  prism(C, odd ? 380 : 980, 210, 0.11);
  prism(C, odd ? 900 : 300, 150, 0.05);
  D.glow(C, odd ? W * 0.9 : W * 0.1, H * 0.1, 360, 240, '#9FD8FF', 0.35);
  D.glow(C, odd ? W * 0.1 : W * 0.9, H * 0.98, 420, 240, '#04245E', 0.45);
}
function decor(C, kind){
  var im = (C.o || {}).img;
  if (im !== 'left' && im !== 'top') K.dots(C, 34, 34, 2, 3, 13, 2.2, '#FFFFFF', 0.75);
}
function brand(C, x, y, w, align){
  var f = C.cover; if (!f.company) return;
  var tw = Math.min(w - 34, String(f.company).length * 9.4 + 12), bx = align === 'c' ? x + (w - tw - 34) / 2 : x;
  C.rect(bx, y, 24, 24, {fill:'#FFFFFF', r:7});
  C.shape('parallelogram', bx + 6, y + 5, 12, 14, {fill:BLUE});
  C.text(bx + 34, y, tw + 20, 24, f.company, {font:'head', size:16, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:false, ls:0, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* lead + list: first point in a navy glass panel, the others on one white card */
function leadList(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  if (n === 1) { var c1 = panel(C, Z.x, Z.y, Z.w, Z.h, {}); var F1 = K.fitHD(C, it, Z.w - 80, 60, Z.h - 80, {hMax:26, dMax:20, noBoost:true}); K.hd(C, Z.x + 40, Z.y + 40, Z.w - 80, Z.h - 80, it[0], F1, {tc:c1.tc, mc:c1.mc}); return; }
  var narrow = Z.w < 760, lead = it[0], rest = it.slice(1), m = rest.length, gap = 24, pad = 30;
  var lw = narrow ? Z.w : Math.round(Z.w * 0.4), lh = narrow ? Math.round(Z.h * 0.34) : Z.h;
  var rx = narrow ? Z.x : Z.x + lw + gap, ry = narrow ? Z.y + lh + gap : Z.y, rw = narrow ? Z.w : Z.w - lw - gap, rh = narrow ? Z.h - lh - gap : Z.h;
  var c0 = panel(C, Z.x, Z.y, lw, lh, {});
  C.rect(Z.x + pad, Z.y + pad, 44, 4, {fill:SKY, r:2});
  var FL = K.fitHD(C, [lead], lw - pad * 2, 70, lh - pad * 2 - 24, {hMax:narrow ? 20 : 26, dMax:narrow ? 15 : 18, hMin:14, dMin:11, noBoost:true});
  K.hd(C, Z.x + pad, Z.y + pad + 24, lw - pad * 2, lh - pad * 2 - 24, lead, FL, {tc:c0.tc, mc:c0.mc});
  var c2 = panel(C, rx, ry, rw, rh, {white:true});
  var row = (rh - pad * 2) / m, tx = rx + pad + 54, tw = rw - pad * 2 - 54;
  var F = K.fitHD(C, rest, tw, 30, row - 16, {hMax:18, dMax:15, hMin:12.5, dMin:10.5, noBoost:true, hLines:1});
  rest.forEach(function(t, k){
    var y = ry + pad + k * row, bh = blockH(C, t, tw, F), oy = Math.max(0, (row - bh) / 2);
    C.rect(rx + pad, y + Math.max(0, (row - 38) / 2), 38, 38, {fill:BLUE, r:10});
    C.text(rx + pad, y + Math.max(0, (row - 38) / 2), 38, 38, pad2(k + 2), {font:'head', size:15, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    K.hd(C, tx, y + oy, tw, row - oy, t, F, {tc:c2.tc, mc:c2.mc, hLines:1});
    if (k < m - 1) C.rect(tx, y + row - 0.5, tw, 1, {fill:'#C9DCF3'});
  });
}

/* tab cards: white cards, each with a navy heading tab on top */
function tabCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)), rows = Math.ceil(n / cols), gx = 20, gy = 20;
  if (Z.w < 760 && cols > 2) { cols = 2; rows = Math.ceil(n / cols); }
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, pad = 20;
  var hasH = it.some(function(t){ return t.h; }), tabH = hasH ? Math.min(64, Math.max(46, ch * 0.24)) : 44;
  var hs = C.groupFit(it.map(function(t, k){ return t.h || 'Point ' + pad2(k + 1); }), cw - pad * 2 - 44, tabH - 14, {font:'head', max:n <= 3 ? 19 : 16.5, min:11.5, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - tabH - pad * 2, {font:'body', max:n <= 3 ? 18 : (n <= 4 ? 16 : 14.5), min:10.5, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = Math.min(ch, tabH + pad * 2 + dmx + 8), tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = y0 + r * (ch2 + gy);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', r:14, shadow:0.14});
    C.rect(x, y, cw, tabH, {fill:NAVY, r:14});
    C.rect(x, y + tabH - 14, cw, 14, {fill:NAVY});
    C.rect(x + pad, y + (tabH - 26) / 2, 26, 26, {fill:'#FFFFFF', op:0.16, r:7});
    C.text(x + pad, y + (tabH - 26) / 2, 26, 26, String(k + 1), {font:'head', size:13, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad + 38, y, cw - pad * 2 - 38, tabH, t.h || 'Point ' + pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1.12, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(x + pad, y + tabH + pad - 4, cw - pad * 2, ch2 - tabH - pad * 2 + 8, t.d, {font:'body', size:ds, color:'#33507E', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* ledger: one navy glass panel, numbered rows in one or two columns */
function ledger(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cc = panel(C, Z.x, Z.y, Z.w, Z.h, {});
  var pad = 28, cols = o.cols || (n > 4 && Z.w > 700 ? 2 : 1), per = Math.ceil(n / cols), gx = 44, cw = (Z.w - pad * 2 - gx * (cols - 1)) / cols, rh = (Z.h - pad * 2) / per;
  var tw = cw - 58;
  var F = K.fitHD(C, it, tw, 28, rh - 14, {hMax:17, dMax:14.5, hMin:12, dMin:10, noBoost:true, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + pad + c * (cw + gx), y = Z.y + pad + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(0, (rh - bh) / 2);
    var bs = Math.min(40, rh - 12);
    C.rect(x, y + (rh - bs) / 2, bs, bs, {fill:'#FFFFFF', r:9});
    C.text(x, y + (rh - bs) / 2, bs, bs, pad2(k + 1), {font:'head', size:Math.round(bs * 0.38), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    K.hd(C, x + 58, y + oy, tw, rh - oy, t, F, {tc:cc.tc, mc:cc.mc, hLines:1});
    if (r < per - 1) C.rect(x + 58, y + rh - 0.5, tw, 1, {fill:'#FFFFFF', op:0.2});
  });
  if (cols === 2) C.rect(Z.x + Z.w / 2 - 0.5, Z.y + pad, 1, Z.h - pad * 2, {fill:'#FFFFFF', op:0.2});
}

/* stats: white tiles with a big blue value, navy label strip */
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function statTiles(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 700 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), gx = 20, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = Math.min((Z.h - gy * (rows - 1)) / rows, 330), pad = 22;
  var vals = st.map(sv), labs = st.map(sl), hasX = st.some(function(t){ return t.x; });
  var vh = ch * (hasX ? 0.34 : 0.44);
  var vs = C.groupFit(vals, cw - pad * 2, vh, {font:'head', max:78, min:24, maxLines:1});
  var ls = C.groupFit(labs, cw - pad * 2, 50, {font:'head', max:18, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - pad * 2, ch - vh - 90, {font:'body', max:14.5, min:10.5, lh:1.38});
  var tot = ch * rows + gy * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = y0 + r * (ch + gy);
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:16, shadow:0.14});
    C.rect(x + pad, y + pad, 40, 4, {fill:BLUE, r:2});
    C.text(x + pad, y + pad + 14, cw - pad * 2, vh, vals[k], {font:'head', size:vs, wt:800, color:BLUE, va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y + pad + 14 + vh + 8, lh = C.measureText(labs[k], cw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + pad, ly, cw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:NAVY, lh:1.15, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + lh + 10, cw - pad * 2, y + ch - pad - ly - lh - 10, t.x, {font:'body', size:ds, color:'#4A678F', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* timeline: white step cards hanging from a light rail with square markers */
function railSteps(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var gap = 16, cw = (Z.w - gap * (n - 1)) / n, pad = 18, ry = Z.y + 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2, 34, {font:'head', max:24, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - 60 - hs * 1.2 - pad * 2 - 12, {font:'body', max:n <= 4 ? 16.5 : 14.5, min:10.5, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch = Math.min(Z.h - 60, pad * 2 + hs * 1.2 + 12 + dmx + 8), cy = ry + 38;
  C.rect(Z.x, ry - 1.5, Z.w, 3, {fill:'#FFFFFF', op:0.4, r:2});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cx = x + cw / 2, last = k === n - 1;
    C.line(cx, ry, cx, cy, {color:'#FFFFFF', lw:1.4, op:0.7});
    C.rect(cx - 9, ry - 9, 18, 18, {fill:last ? '#FFFFFF' : NAVY, line:'#FFFFFF', lw:2, r:5});
    if (last) C.rect(x, cy, cw, ch, {fill:'#FFFFFF', r:14, shadow:0.14}); else panel(C, x, cy, cw, ch, {});
    C.text(x + pad, cy + pad, cw - pad * 2, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:last ? BLUE : '#FFFFFF', lh:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, cy + pad + hs * 1.2 + 12, cw - pad * 2, ch - pad * 2 - hs * 1.2 - 12, t.d, {font:'body', size:ds, color:last ? '#33507E' : MUTE, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* statement: one navy glass panel, sky rule, large text */
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = panel(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 44, y = Z.y + pad;
  C.rect(Z.x + pad, y, 56, 5, {fill:SKY, r:3}); y += 28;
  if (it.h) { var he = C.text(Z.x + pad, y, Z.w - pad * 2, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:'#FFFFFF', lh:1.15, caps:false, ls:0, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.text(Z.x + pad, y, Z.w - pad * 2, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 30, min:14}, color:it.h ? cc.mc : '#FFFFFF', lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function meta(C, x, y, w, split){
  var f = C.cover, a = [f.presenter, f.designation].filter(Boolean).join(', '), b = f.date || '';
  if (a) { C.text(x, y, w / 2, 16, 'PRESENTED BY', {font:'body', size:10.5, wt:700, color:MUTE, ls:0.14, lh:1}); C.text(x, y + 20, split ? w / 2 - 10 : w, 22, a, {font:'body', size:15, wt:600, color:'#FFFFFF', lh:1.2, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (b && split) { C.text(x + w / 2, y, w / 2, 16, 'DATE', {font:'body', size:10.5, wt:700, color:MUTE, ls:0.14, lh:1, align:'r'}); C.text(x + w / 2, y + 20, w / 2, 22, b, {font:'body', size:15, wt:600, color:'#FFFFFF', lh:1.2, align:'r', maxLines:1}); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  if (o.img === 'right') {
    brand(C, 70, 56, 520, 'l');
    D.imgBox(C, 690, 60, W - 690 - 60, H - 120, {r:20});
    panel(C, 50, 190, 700, 330, {op:0.62, r:18});
    C.rect(90, 228, 56, 5, {fill:SKY, r:3});
    C.text(90, 250, 620, 170, C.s.title, {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'m', lh:1.04});
    if (C.s.subtitle) C.text(90, 430, 620, 66, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MUTE, ed:{f:'subtitle'}, lh:1.4, maxLines:3});
    meta(C, 70, 566, 560, false);
    return;
  }
  brand(C, 0, 64, W, 'c');
  panel(C, 150, 150, W - 300, 330, {op:0.42, r:20});
  C.rect(W / 2 - 34, 186, 68, 5, {fill:SKY, r:3});
  C.text(210, 206, W - 420, 180, C.s.title, {font:'head', fit:{max:72, min:32}, wt:800, color:'#FFFFFF', align:'c', ed:{f:'title'}, maxLines:3, va:'m', lh:1.04});
  if (C.s.subtitle) C.text(260, 396, W - 520, 62, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:ICE, align:'c', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  meta(C, 210, 520, W - 420, true);
  if (f.website) K.pill(C, W / 2 - 150, 610, 300, 38, f.website, {fill:DEEP, op:0.55, line:'#FFFFFF', lw:1, color:'#FFFFFF', size:14, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', x = photo ? 60 : 150, w = photo ? 640 : W - 300;
  brand(C, photo ? 70 : 0, 56, photo ? 520 : W, photo ? 'l' : 'c');
  if (photo) D.imgBox(C, 740, 60, W - 740 - 60, H - 120, {r:20});
  panel(C, x, 130, w, 210, {op:0.42, r:20});
  C.text(x + 40, 150, w - 80, 110, C.s.title || 'Thank You', {font:'head', fit:{max:70, min:34}, wt:800, color:'#FFFFFF', align:photo ? 'l' : 'c', ed:{f:'title'}, maxLines:2, va:'m', lh:1.04});
  if (C.s.subtitle) C.text(x + 40, 266, w - 80, 56, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:ICE, align:photo ? 'l' : 'c', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  var ph = photo ? 60 + cs.length * 62 : 150, py = 380;
  panel(C, x, py, w, ph, {op:0.6, r:18});
  if (photo) {
    cs.forEach(function(c, k){
      var y = py + 30 + k * 62;
      C.ell(x + 34, y, 44, 44, {fill:'#FFFFFF'}); C.icon(x + 46, y + 12, 20, c.icon, BLUE);
      C.text(x + 96, y, w - 130, 44, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    });
  } else {
    var n = cs.length, cw = (w - 80) / n;
    cs.forEach(function(c, k){
      var cx = x + 40 + k * cw;
      C.ell(cx, py + 34, 48, 48, {fill:'#FFFFFF'}); C.icon(cx + 13, py + 47, 22, c.icon, BLUE);
      C.text(cx + 62, py + 34, cw - 76, 18, c.label || '', {font:'body', size:11, wt:700, color:MUTE, caps:true, ls:0.12, lh:1});
      C.text(cx + 62, py + 56, cw - 76, 28, c.val, {font:'body', fit:{max:17, min:11}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
      if (k) C.rect(cx - 12, py + 30, 1, ph - 60, {fill:'#FFFFFF', op:0.25});
    });
  }
}
function agendaA(C, o){
  var te = C.text(70, 150, 420, 220, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1.04});
  C.rect(70, 150 + te._h + 20, 56, 5, {fill:SKY, r:3});
  if (C.s.subtitle) C.text(70, 150 + te._h + 46, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  ledger(C, {x:540, y:80, w:W - 540 - 70, h:H - 170}, {cols:1, max:8});
}
function agendaPhoto(C, o){
  C.text(70, 74, 620, 64, C.s.title || 'Agenda', {font:'head', fit:{max:46, min:26}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  D.imgBox(C, 600, 160, W - 600 - 70, 470, {r:16});
  ledger(C, {x:70, y:160, w:500, h:470}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:'#FFFFFF', tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', x = photo ? 60 : 150, w = photo ? 660 : W - 300;
  if (photo) D.imgBox(C, 760, 60, W - 760 - 60, H - 120, {r:20});
  panel(C, x, 130, w, H - 260, {op:0.45, r:20});
  C.rect(x + 44, 170, 64, 64, {fill:'#FFFFFF', r:16});
  C.text(x + 44, 182, 64, 64, '”', {font:'head', size:76, wt:800, color:BLUE, align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 44, 262, w - 88, H - 260 - 230, C.s.title, {font:'head', fit:{max:photo ? 34 : 40, min:18}, wt:600, color:'#FFFFFF', lh:1.28, caps:false, ls:0, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x + 44, 262 + qe._h + 26, 40, 3, {fill:SKY, r:2}); C.text(x + 44, 262 + qe._h + 42, w - 88, 26, bt, {font:'body', size:15, wt:600, color:ICE, lh:1.2, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}

D.register({
  id:'azureprism', name:'Azure Prism', tag:'Corporate · blue glass',
  desc:'Bright blue gradient pages with slanted light prisms, navy glass panels, white tab cards and a progress footer for proposals and company decks.',
  fonts:{head:'Plus Jakarta Sans', body:'Inter', headW:800, bodyW:400, headLh:1.08, headCaps:true, headLs:0.01, gf:'Plus Jakarta Sans:wght@500;600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Centred glass panel', {}), V('v2', 'Glass panel + photo', {img:'right'})],
    closing:[V('v1', 'Glass panel + contact bar', {}), V('v2', 'Photo + contact list', {img:'photo'})],
    agenda:[V('v1', 'Title + ledger', {fn:agendaA}), V('n2', 'Tab cards', {body:tabCards}), V('v2', 'Ledger + photo', {fn:agendaPhoto})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Tab cards', {body:tabCards}), V('n2', 'Lead + list', {body:leadList}), V('n3', 'Two panels + VS', {body:'duo', vs:true, hl:'first'}), V('v2', 'Ledger + image right', {body:ledger, img:'right', cols:1}), V('v3', 'Image left + tab cards', {body:tabCards, img:'left', cols:1})],
    p3:[V('v1', 'Tab cards', {body:tabCards}), V('n2', 'Lead + list', {body:leadList}), V('n3', 'Glass cards', {body:'cols', hl:'first'}), V('n4', 'Ledger', {body:ledger, cols:1}), V('v2', 'Ledger + image right', {body:ledger, img:'right', cols:1}), V('v3', 'Image left + lead + list', {body:leadList, img:'left'})],
    p4:[V('v1', 'Tab cards', {body:tabCards}), V('n2', 'Lead + list', {body:leadList}), V('n3', '2 × 2 tab cards', {body:tabCards, cols:2}), V('n4', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right', cols:1}), V('v3', 'Image left + tab cards', {body:tabCards, img:'left', cols:2})],
    p56:[V('v1', 'Tab cards', {body:tabCards}), V('n2', 'Ledger', {body:ledger}), V('n3', 'Glass grid', {body:'grid', hl:'first'}), V('v2', 'Ledger + image right', {body:ledger, img:'right', cols:1}), V('v3', 'Image left + ledger', {body:ledger, img:'left', cols:1})],
    p78:[V('v1', 'Tab cards', {body:tabCards}), V('n2', 'Ledger', {body:ledger}), V('n3', 'Glass grid', {body:'grid'}), V('v2', 'Ledger + image right', {body:ledger, img:'right', cols:1})],
    stats:[V('v1', 'White stat tiles', {body:statTiles}), V('n2', 'Progress rings', {body:'statRings', track:'#0A3F96'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Tiles + image right', {body:statTiles, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Rail steps', {body:railSteps}), V('n2', 'Arrow steps', {body:'tlChev'}), V('n3', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right', cols:1})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: opalglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 31 - Opal Glass : light pastel page, frosted white glass cards, glossy gradient orbs */
var INK = '#1F1B4D', BODY = '#4A4873', VIO = '#6C4CF1', TEAL = '#12B5A4', PINK = '#FF7AA8', SKY = '#4FA8FF';
var ORB = [[ '#B9A6FF', '#5B3DF0'], ['#8FF0E2', '#0FA89A'], ['#FFC2D6', '#F2548B'], ['#B5DBFF', '#2F86F0'], ['#FFE0A8', '#F59E2A']];
var P0 = {bg:'#F4F3FB', text:INK, title:INK, muted:BODY, accent:VIO, accent2:TEAL, card:'#FFFFFF', line:'#D9D5F0', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[VIO, TEAL, PINK, SKY, '#F59E2A', '#8B7CF6'],
  header:{kind:'left', top:70, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return pad2(C.i + 1) + ' / ' + pad2(C.total || C.i + 1); }, kPill:true, kPillFill:'#FFFFFF', kPillOp:0.7, kc:VIO, kGap:42, kSize:11.5},
  card:{kind:'glass', r:22, fill:'#FFFFFF', op:0.62, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.07, dark:false, tc:INK, mc:BODY,
        hl:{kind:'grad', grad:{ang:135, stops:[[0, '#7B5CF5'], [100, '#4C2FD9']]}, dark:true, tc:'#FFFFFF', mc:'#E4DEFF'}},
  icon:{kind:'soft', bg:VIO, fg:VIO, op:0.14}, num:{kind:'circle', bg:VIO, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#FFFFFF', op:0.75, line:'#FFFFFF', lw:1.5, label:'Takeaway', lc:VIO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:26, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#CFC4FF'], [100, '#9FE9DF']]}}
};
function orb(C, cx, cy, r, k, op){
  var c = ORB[((k % ORB.length) + ORB.length) % ORB.length];
  D.glow(C, cx + r * 0.15, cy + r * 0.75, r * 1.1, r * 0.5, c[1], 0.28 * (op == null ? 1 : op));
  C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:140, stops:[[0, c[0]], [100, c[1]]]}, op:op});
  C.ell(cx - r * 0.62, cy - r * 0.78, r * 0.9, r * 0.52, {fill:'#FFFFFF', op:0.42 * (op == null ? 1 : op)});
  C.ell(cx + r * 0.2, cy + r * 0.42, r * 0.5, r * 0.3, {fill:'#FFFFFF', op:0.14 * (op == null ? 1 : op)});
}
function ringOrb(C, cx, cy, r, k){ var c = ORB[k % ORB.length]; C.ell(cx - r, cy - r, r * 2, r * 2, {line:c[1], lw:Math.max(3, r * 0.16), lop:0.55}); C.ell(cx - r, cy - r, r * 2, r * 2, {line:'#FFFFFF', lw:1.5, lop:0.9}); }
function glass(C, x, y, w, h, o){
  o = o || {};
  if (o.vio) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#7B5CF5'], [100, '#4C2FD9']]}, r:o.r || 22, shadow:0.14}); C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.22], [50, '#FFFFFF', 0]]}, r:o.r || 22}); return {tc:'#FFFFFF', mc:'#E4DEFF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.9], [100, '#FFFFFF', 0.48]]}, line:'#FFFFFF', lop:1, lw:1.5, r:o.r || 22, shadow:0.07});
  return {tc:INK, mc:BODY, ac:VIO};
}
function foot(C){
  var M = 72, y = H - 40, f = C.cover;
  if (f.company) { orb(C, M + 8, y + 9, 8, C.i); C.text(M + 26, y, 420, 18, f.company, {font:'body', size:12, wt:700, color:INK, lh:1, va:'m'}); }
  C.rect(W - M - 62, y - 4, 62, 26, {fill:'#FFFFFF', op:0.75, line:'#FFFFFF', lw:1.2, r:13});
  C.text(W - M - 62, y - 4, 62, 26, pad2(C.i + 1), {font:'body', size:12, wt:700, color:VIO, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#F4F3FB'});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.92, W * 0.08, W * 0.6][k], H * 0.05, 480, 330, '#C9B8FF', 0.75);
  D.glow(C, [W * 0.05, W * 0.95, W * 0.1][k], H * 0.98, 460, 320, '#A8F0E0', 0.7);
  D.glow(C, [W * 0.55, W * 0.4, W * 0.95][k], H * 0.72, 360, 240, '#FFD3C2', 0.55);
  D.glow(C, [W * 0.3, W * 0.7, W * 0.35][k], H * 0.2, 300, 200, '#B9DCFF', 0.5);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') { orb(C, W - 96, 70, 26, C.i + 1); ringOrb(C, W - 150, 104, 14, C.i + 2); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function numOrb(C, cx, cy, r, n, k){ orb(C, cx, cy, r, k); C.text(cx - r, cy - r, r * 2, r * 2, pad2(n), {font:'head', size:Math.round(r * 0.72), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0}); }

/* glass cards with a numbered orb sitting on the top-left corner */
function orbCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)), rows, gx = 22, gy = 34;
  if (Z.w < 760 && cols > 2) cols = 2;
  rows = Math.ceil(n / cols);
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - 20 - gy * (rows - 1)) / rows, pad = 24, top = 40;
  var F = K.fitHD(C, it, cw - pad * 2, 54, ch - top - pad, {hMax:19, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2, F)); });
  var ch2 = Math.min(ch, top + bmx + pad + 6), tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + 20 + Math.max(0, (Z.h - 20 - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = y0 + r * (ch2 + gy), cc = glass(C, x, y, cw, ch2, {vio:o.hl && k === 0});
    numOrb(C, x + 34, y + 2, 24, k + 1, k);
    K.hd(C, x + pad, y + top, cw - pad * 2, ch2 - top - pad, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* stacked glass rows, gently staggered, orb icon on the left */
function glassRows(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 24, gy = 14, cw = (Z.w - gx * (cols - 1)) / cols, rh = (Z.h - gy * (per - 1)) / per, st = cols === 1 && Z.w > 700 ? 26 : 0;
  var rw = cw - st, tw = rw - 96 - 24;
  var F = K.fitHD(C, it, tw, 28, rh - 22, {hMax:19, dMax:15.5, hMin:12.5, dMin:10.5, noBoost:n > 3, hLines:1});
  var rh2 = Math.min(rh, 108 + (n <= 3 ? 30 : 0)), tot = rh2 * per + gy * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx) + (r % 2 ? st : 0), y = y0 + r * (rh2 + gy), cc = glass(C, x, y, rw, rh2, {r:Math.min(22, rh2 / 2)});
    var orr = Math.min(22, rh2 / 2 - 12);
    orb(C, x + 26 + orr, y + rh2 / 2, orr, k);
    C.icon(x + 26 + orr - orr * 0.55, y + rh2 / 2 - orr * 0.55, orr * 1.1, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), '#FFFFFF');
    var bh = blockH(C, t, tw, F), oy = Math.max(8, (rh2 - bh) / 2);
    K.hd(C, x + 96, y + oy, tw, rh2 - oy - 6, t, F, {tc:cc.tc, mc:cc.mc, hLines:1});
  });
}
/* bento: one tall violet glass card + smaller white glass cards */
function bento(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 700) return orbCards(C, Z, o);
  var gap = 20, lw = Math.round(Z.w * 0.36), rest = it.slice(1), m = rest.length, cols = m <= 2 ? 1 : 2, rows = Math.ceil(m / cols), rw = (Z.w - lw - gap - gap * (cols - 1)) / cols, rh = (Z.h - gap * (rows - 1)) / rows, pad = 26;
  var c0 = glass(C, Z.x, Z.y, lw, Z.h, {vio:true});
  orb(C, Z.x + lw - 54, Z.y + Z.h - 54, 64, 1, 0.9); ringOrb(C, Z.x + lw - 130, Z.y + Z.h - 34, 20, 3);
  C.text(Z.x + pad, Z.y + pad, 80, 26, '01', {font:'head', size:20, wt:700, color:'#FFFFFF', lh:1, caps:false, ls:0});
  var FL = K.fitHD(C, [it[0]], lw - pad * 2, 80, Z.h - pad * 2 - 150, {hMax:26, dMax:17, hMin:15, dMin:11, noBoost:true});
  K.hd(C, Z.x + pad, Z.y + pad + 40, lw - pad * 2, Z.h - pad * 2 - 150, it[0], FL, {tc:c0.tc, mc:c0.mc});
  var F = K.fitHD(C, rest, rw - pad * 2, 50, rh - pad * 2 - 30, {hMax:18, dMax:15, hMin:12.5, dMin:10.5, noBoost:true});
  rest.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, w2 = (cols === 2 && k === m - 1 && m % 2) ? rw * 2 + gap : rw, x = Z.x + lw + gap + c * (rw + gap), y = Z.y + r * (rh + gap), cc = glass(C, x, y, w2, rh, {});
    C.text(x + pad, y + pad - 4, 60, 22, pad2(k + 2), {font:'head', size:16, wt:700, color:ORB[(k + 1) % ORB.length][1], lh:1, caps:false, ls:0});
    C.rect(x + pad + 34, y + pad + 6, w2 - pad * 2 - 34, 1.2, {fill:'#D9D5F0'});
    K.hd(C, x + pad, y + pad + 26, w2 - pad * 2, rh - pad * 2 - 26, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: value inside a glossy orb, label on a glass plate under it */
function orbStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var r = Math.min(cw * 0.3, ch * 0.3, 96), vals = st.map(sv), labs = st.map(sl), pad = 20;
  var vs = C.groupFit(vals, r * 1.5, r * 0.8, {font:'head', max:44, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - pad * 2, 48, {font:'head', max:18, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - pad * 2, ch - r * 1.2 - 96, {font:'body', max:14.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var rr = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + rr * (ch + gy), cx = x + cw / 2;
    glass(C, x, y + r, cw, ch - r, {});
    orb(C, cx, y + r, r, k);
    C.text(cx - r * 0.8, y + r * 0.55, r * 1.6, r * 0.9, vals[k], {font:'head', size:vs, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y + r * 2 + 18, lh = C.measureText(labs[k], cw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + pad, ly, cw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + lh + 8, cw - pad * 2, y + ch - pad - ly - lh - 8, t.x, {font:'body', size:ds, color:BODY, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: orb beads on a line, glass cards alternating above and below */
function beads(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / n, cw = Math.min(slot * 1.7 - 16, 330), my = Z.y + Z.h / 2, ch = Z.h / 2 - 40, pad = 18;
  if (n <= 3) cw = slot - 20;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2, 30, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - hs * 1.2 - 8, {font:'body', max:15.5, min:10.5, lh:1.38});
  C.rect(Z.x, my - 2, Z.w, 4, {fill:'#FFFFFF', r:2});
  C.rect(Z.x, my - 2, Z.w, 4, {grad:{ang:90, stops:[[0, VIO, 0.5], [100, TEAL, 0.5]]}, r:2});
  it.forEach(function(t, k){
    var cx = Z.x + slot * (k + 0.5), up = n <= 3 ? false : k % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw / 2));
    var dh = C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(ch, pad * 2 + hs * 1.2 + 8 + dh + 4), y = up ? my - 34 - hh : my + 34;
    var cc = glass(C, x, y, cw, hh, {r:18});
    orb(C, cx, my, 15, k);
    C.text(x + pad, y + pad, cw - pad * 2, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:ORB[k % ORB.length][1], lh:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, y + pad + hs * 1.2 + 8, cw - pad * 2, hh - pad * 2 - hs * 1.2 - 8, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2 - (o.img ? 0 : 150);
  if (!o.img) { orb(C, Z.x + Z.w - 90, Z.y + Z.h - 80, 78, C.i); orb(C, Z.x + Z.w - 190, Z.y + Z.h - 40, 30, C.i + 2); ringOrb(C, Z.x + Z.w - 60, Z.y + Z.h - 190, 22, C.i + 1); }
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:VIO, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cluster(C, cx, cy, s){
  orb(C, cx, cy, 150 * s, 0); orb(C, cx + 150 * s, cy - 130 * s, 70 * s, 1); orb(C, cx - 150 * s, cy + 120 * s, 54 * s, 2); orb(C, cx + 120 * s, cy + 150 * s, 36 * s, 3);
  ringOrb(C, cx - 120 * s, cy - 150 * s, 44 * s, 4); ringOrb(C, cx + 215 * s, cy + 40 * s, 22 * s, 2);
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; orb(C, x + 12, y + 12, 12, 0); C.text(x + 34, y, 420, 24, f.company, {font:'head', size:17, wt:700, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 52);
  if (f.date) C.text(W - 372, 52, 300, 24, f.date, {font:'body', size:13, wt:600, color:BODY, align:'r', va:'m', lh:1});
  if (photo) { D.imgBox(C, 720, 110, 488, 500, {r:36}); orb(C, 730, 580, 62, 1); orb(C, 1190, 130, 34, 2); ringOrb(C, 800, 640, 20, 0); }
  else cluster(C, 960, 370, 1);
  glass(C, 60, 170, 640, 380, {r:30});
  C.text(100, 200, 560, 210, C.s.title, {font:'head', fit:{max:66, min:30}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'m', lh:1.05});
  if (C.s.subtitle) C.text(100, 420, 560, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { C.rect(100, 500, 3, 26, {fill:VIO, r:2}); C.text(114, 500, 540, 26, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 52);
  if (photo) { D.imgBox(C, 720, 110, 488, 500, {r:36}); orb(C, 1196, 596, 54, 3); ringOrb(C, 742, 128, 22, 1); }
  else cluster(C, 980, 360, 0.92);
  glass(C, 60, 130, 640, 250, {r:30});
  C.text(100, 150, 560, 130, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(100, 290, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 410 + k * 66;
    glass(C, 60, y, 640, 54, {r:27});
    orb(C, 60 + 30, y + 27, 18, k); C.icon(60 + 30 - 10, y + 17, 20, c.icon, '#FFFFFF');
    C.text(60 + 66, y, 640 - 90, 54, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(72, 150, 430, 230, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.03});
  if (C.s.subtitle) C.text(72, 150 + te._h + 18, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  orb(C, 150, 590, 70, C.i); orb(C, 250, 640, 30, C.i + 1); ringOrb(C, 300, 560, 20, C.i + 2);
  glassRows(C, {x:540, y:70, w:W - 540 - 72, h:H - 150}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:VIO, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 60 : 130, w = photo ? 640 : W - 260;
  if (photo) { D.imgBox(C, 730, 100, 480, 520, {r:36}); orb(C, 740, 600, 44, 2); }
  glass(C, x, 130, w, H - 260, {r:30});
  orb(C, x + 70, 130, 40, 0); C.text(x + 30, 100, 80, 80, '”', {font:'head', size:70, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 50, 210, w - 100, H - 260 - 190, C.s.title, {font:'head', fit:{max:photo ? 34 : 40, min:18}, wt:600, color:INK, lh:1.28, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x + 50, 210 + qe._h + 24, 3, 24, {fill:VIO, r:2}); C.text(x + 64, 210 + qe._h + 24, w - 120, 24, bt, {font:'body', size:15, wt:600, color:BODY, va:'m', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'opalglass', name:'Opal Glass', tag:'Light · frosted glass',
  desc:'Soft pastel pages with frosted white glass cards and glossy gradient orbs: a light, friendly glass look for projects, products and reports.',
  fonts:{head:'Sora', body:'DM Sans', headW:700, bodyW:400, headLh:1.1, gf:'Sora:wght@400;600;700;800|DM Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Glass panel + orbs', {}), V('v2', 'Glass panel + photo', {img:'photo'})],
    closing:[V('v1', 'Orbs + contact pills', {}), V('v2', 'Photo + contact pills', {img:'photo'})],
    agenda:[V('v1', 'Title + glass rows', {fn:agendaA}), V('n2', 'Orb cards', {body:orbCards}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1, max:7})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Orb cards', {body:orbCards, hl:true}), V('n2', 'Glass rows', {body:glassRows}), V('n3', 'Two panels + VS', {body:'duo', vs:true, hl:'first'}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + orb cards', {body:orbCards, img:'left', cols:1})],
    p3:[V('v1', 'Bento', {body:bento}), V('n2', 'Orb cards', {body:orbCards}), V('n3', 'Glass rows', {body:glassRows}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + glass rows', {body:glassRows, img:'left', cols:1})],
    p4:[V('v1', 'Orb cards', {body:orbCards}), V('n2', 'Bento', {body:bento}), V('n3', 'Glass rows', {body:glassRows}), V('n4', '2 × 2 orb cards', {body:orbCards, cols:2}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + orb cards', {body:orbCards, img:'left', cols:2})],
    p56:[V('v1', 'Orb cards', {body:orbCards}), V('n2', 'Bento', {body:bento}), V('n3', 'Glass rows', {body:glassRows}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + glass rows', {body:glassRows, img:'left', cols:1})],
    p78:[V('v1', 'Orb cards', {body:orbCards}), V('n2', 'Glass rows', {body:glassRows, max:8}), V('n3', 'Glass grid', {body:'grid'}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1, max:8})],
    stats:[V('v1', 'Orb numbers', {body:orbStats}), V('n2', 'Progress rings', {body:'statRings', track:'#E3DFF7'}), V('n3', 'Stat cards', {body:'statCards', icons:true}), V('v2', 'Orb numbers + image right', {body:orbStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Orb beads', {body:beads}), V('n2', 'Milestone cards', {body:'tlCards'}), V('n3', 'Glass rows', {body:glassRows}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: obsidianglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 32 - Obsidian Glass : near-black indigo, aurora glows, dark frosted glass with a light edge, glass cubes */
var CY = '#3DE3FF', VI = '#9B7BFF', MG = '#FF5FD2', MUTE = '#B4B2D6', INK = '#07061A';
var BG = {ang:150, stops:[[0, '#07061A'], [55, '#0D0B2B'], [100, '#170F40']]};
var P0 = {bg:'#0A0822', text:'#FFFFFF', title:'#FFFFFF', muted:MUTE, accent:CY, accent2:VI, card:'#1A1740', line:'#3A3668', onAccent:INK};
var S = {
  margin:72, series:[CY, VI, MG, '#7CF5C8', '#FFC857', '#6EA8FF'],
  header:{kind:'left', top:66, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return pad2(C.i + 1) + '  —  ' + (C.cover.company || 'Presentation'); }, kc:CY, kGap:30, kSize:12},
  card:{kind:'glass', r:18, fill:'#FFFFFF', op:0.07, line:'#FFFFFF', lop:0.26, lw:1.2, dark:true, tc:'#FFFFFF', mc:MUTE,
        hl:{kind:'grad', grad:{ang:135, stops:[[0, '#5B3DF0'], [100, '#1FB6E0']]}, dark:true, tc:'#FFFFFF', mc:'#E8F6FF'}},
  icon:{kind:'soft', bg:CY, fg:CY, op:0.16}, num:{kind:'ring', bg:CY, fg:CY},
  conc:{kind:'box', fill:'#FFFFFF', op:0.07, line:'#FFFFFF', lw:1, r:14, label:'Takeaway', lc:CY, tc:'#FFFFFF'},
  footer:{fn:foot}, imgFramed:true, imgR:22, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#2A1F6B'], [100, '#0C3B55']]}}
};
function cube(C, cx, cy, s, k, op){
  var a = op == null ? 1 : op, dx = s * 0.87, c = [[CY, VI], [VI, MG], [MG, CY]][((k % 3) + 3) % 3];
  D.glow(C, cx, cy + s * 0.2, s * 1.6, s * 1.3, c[0], 0.22 * a);
  K.poly(C, [[cx, cy - s], [cx + dx, cy - s / 2], [cx, cy], [cx - dx, cy - s / 2]], {fill:'#FFFFFF', op:0.34 * a, stroke:'#FFFFFF', lw:1.2, lop:0.75 * a});
  K.poly(C, [[cx - dx, cy - s / 2], [cx, cy], [cx, cy + s], [cx - dx, cy + s / 2]], {fill:c[0], op:0.42 * a, stroke:'#FFFFFF', lw:1.2, lop:0.55 * a});
  K.poly(C, [[cx + dx, cy - s / 2], [cx, cy], [cx, cy + s], [cx + dx, cy + s / 2]], {fill:c[1], op:0.62 * a, stroke:'#FFFFFF', lw:1.2, lop:0.55 * a});
  K.poly(C, [[cx - dx * 0.55, cy - s * 0.52], [cx - dx * 0.1, cy - s * 0.78], [cx + dx * 0.2, cy - s * 0.6], [cx - dx * 0.25, cy - s * 0.34]], {fill:'#FFFFFF', op:0.3 * a});
}
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 18;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#5B3DF0'], [100, '#1FB6E0']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.25], [55, '#FFFFFF', 0]]}, line:'#FFFFFF', lop:0.5, lw:1.2, r:r}); return {tc:'#FFFFFF', mc:'#E8F6FF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.17], [55, '#FFFFFF', 0.04], [100, '#FFFFFF', 0.09]]}, line:'#FFFFFF', lop:0.28, lw:1.2, r:r});
  if (o.edge !== false && w > 120) C.rect(x + r, y - 1, Math.min(w * 0.4, 220), 2, {grad:{ang:90, stops:[[0, o.ec || CY, 0], [50, o.ec || CY, 1], [100, o.ec || CY, 0]]}});
  return {tc:'#FFFFFF', mc:MUTE, ac:CY};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  C.rect(M, y - 10, W - 2 * M, 1, {fill:'#FFFFFF', op:0.14});
  if (f.website || f.company) C.text(M, y, 420, 18, f.website || f.company, {font:'body', size:11.5, wt:600, color:MUTE, lh:1, va:'m', ls:0.04});
  C.text(W - M - 160, y, 160, 18, pad2(C.i + 1) + ' / ' + pad2(C.total || C.i + 1), {font:'head', size:12, wt:600, color:'#FFFFFF', align:'r', lh:1, va:'m', caps:false, ls:0.06});
}
function bg(C, kind){
  C.bg({grad:BG});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.95, W * 0.05, W * 0.7][k], H * 0.02, 520, 340, '#6B3DFF', 0.5);
  D.glow(C, [W * 0.02, W * 0.98, W * 0.1][k], H * 1.0, 480, 300, '#12B9E6', 0.32);
  D.glow(C, [W * 0.6, W * 0.35, W * 0.98][k], H * 0.75, 300, 200, '#FF3FC8', 0.14);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') cube(C, W - 96, 78, 26, C.i);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* tall glass columns with a huge faded number at the bottom */
function glassCols(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  if (Z.w < 720 && n > 2) return indexPanel(C, Z, o);
  var gx = 18, cw = (Z.w - gx * (n - 1)) / n, pad = 24, wm = Math.min(120, cw * 0.5);
  var F = K.fitHD(C, it, cw - pad * 2, 60, Z.h - pad * 2 - wm * 0.7 - 30, {hMax:21, dMax:15.5, hMin:13, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), cc = glass(C, x, Z.y, cw, Z.h, {hot:o.hl && k === 0, ec:[CY, VI, MG][k % 3]});
    C.rect(x + pad, Z.y + pad, 30, 3, {fill:cc.ac, r:2});
    K.hd(C, x + pad, Z.y + pad + 20, cw - pad * 2, Z.h - pad * 2 - wm * 0.7 - 30, t, F, {tc:cc.tc, mc:cc.mc});
    C.text(x + pad - 4, Z.y + Z.h - wm - 6, cw - pad * 2, wm, pad2(k + 1), {font:'head', size:wm, wt:700, color:'#FFFFFF', op:0.1, lh:1, va:'b', caps:false, ls:-0.03});
  });
}
/* staircase: glass cards stepping down from left to right */
function steps(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 720) return indexPanel(C, Z, o);
  var gx = 16, cw = (Z.w - gx * (n - 1)) / n, drop = Math.min(46, (Z.h * 0.42) / (n - 1)), ch = Z.h - drop * (n - 1), pad = 22;
  var F = K.fitHD(C, it, cw - pad * 2, 56, ch - pad * 2 - 46, {hMax:20, dMax:15, hMin:13, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), y = Z.y + k * drop, cc = glass(C, x, y, cw, ch, {hot:k === n - 1, ec:[CY, VI, MG][k % 3]});
    C.rect(x + pad, y + pad, 34, 34, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.35, lw:1, r:10});
    C.text(x + pad, y + pad, 34, 34, pad2(k + 1), {font:'head', size:14, wt:700, color:k === n - 1 ? '#FFFFFF' : CY, align:'c', va:'m', lh:1, caps:false, ls:0});
    K.hd(C, x + pad, y + pad + 48, cw - pad * 2, ch - pad * 2 - 48, t, F, {tc:cc.tc, mc:cc.mc});
    if (k < n - 1) K.poly(C, [[x + cw + 3, y + 34], [x + cw + gx - 3, y + 39], [x + cw + 3, y + 44]], {fill:CY, op:0.8});
  });
}
/* one glass panel, cyan index numbers, rows in one or two columns */
function indexPanel(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {});
  var pad = 28, cols = o.cols || (n > 4 && Z.w > 700 ? 2 : 1), per = Math.ceil(n / cols), gx = 48, cw = (Z.w - pad * 2 - gx * (cols - 1)) / cols, rh = (Z.h - pad * 2) / per, tw = cw - 62;
  var F = K.fitHD(C, it, tw, 28, rh - 14, {hMax:18, dMax:14.5, hMin:12, dMin:10, noBoost:true, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + pad + c * (cw + gx), y = Z.y + pad + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(0, (rh - bh) / 2);
    C.text(x, y, 50, rh, pad2(k + 1), {font:'head', size:Math.min(26, rh * 0.42), wt:600, color:[CY, VI, MG][k % 3], va:'m', lh:1, caps:false, ls:-0.01});
    K.hd(C, x + 62, y + oy, tw, rh - oy, t, F, {tc:cc.tc, mc:cc.mc, hLines:1});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:'#FFFFFF', op:0.13});
  });
}
/* 2 x n glass grid with icon chip top-right */
function chipGrid(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? 2 : (n <= 6 ? 3 : 4)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22;
  var F = K.fitHD(C, it, cw - pad * 2 - 44, 50, ch - pad * 2, {hMax:19, dMax:15, hMin:12.5, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), cc = glass(C, x, y, cw, ch, {ec:[CY, VI, MG][k % 3]});
    C.rect(x + cw - pad - 36, y + pad, 36, 36, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.3, lw:1, r:11});
    C.icon(x + cw - pad - 27, y + pad + 9, 18, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), [CY, VI, MG][k % 3]);
    if (t.h) C.text(x + pad, y + pad, cw - pad * 2 - 48, F.hh + 4, t.h, {font:'head', size:F.hs, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    var dy = y + pad + (t.h ? Math.max(F.hh + 10, 46) : 46);
    C.text(x + pad, dy, cw - pad * 2, y + ch - pad - dy, t.d, {font:'body', size:F.ds, color:cc.mc, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: glass cards, big value, gradient meter along the bottom */
function meterStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 700 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, 340), pad = 26;
  var vals = st.map(sv), labs = st.map(sl), vh = ch * 0.34;
  var vs = C.groupFit(vals, cw - pad * 2, vh, {font:'head', max:76, min:24, maxLines:1});
  var ls = C.groupFit(labs, cw - pad * 2, 48, {font:'head', max:18, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - pad * 2, ch - vh - 120, {font:'body', max:14.5, min:10.5, lh:1.38});
  var tot = ch * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch + g), cc = glass(C, x, y, cw, ch, {ec:[CY, VI, MG][k % 3]}), col = [CY, VI, MG][k % 3];
    C.text(x + pad, y + pad, cw - pad * 2, vh, vals[k], {font:'head', size:vs, wt:600, color:'#FFFFFF', va:'m', lh:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y + pad + vh + 10, lh = C.measureText(labs[k], cw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + pad, ly, cw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:600, color:col, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + lh + 8, cw - pad * 2, y + ch - pad - 26 - ly - lh - 8, t.x, {font:'body', size:ds, color:MUTE, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
    C.rect(x + pad, y + ch - pad - 6, cw - pad * 2, 6, {fill:'#FFFFFF', op:0.12, r:3});
    C.rect(x + pad, y + ch - pad - 6, (cw - pad * 2) * pct(vals[k], k) / 100, 6, {grad:{ang:90, stops:[[0, VI], [100, col]]}, r:3});
  });
}
/* timeline: glass cards under a gradient track with diamond nodes */
function track(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 18, ty = Z.y + 16;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2, 34, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - 70 - hs * 1.2 - pad * 2, {font:'body', max:n <= 4 ? 16 : 14.5, min:10.5, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch = Math.min(Z.h - 56, pad * 2 + hs * 1.2 + 12 + dmx + 8), cy = ty + 40;
  C.rect(Z.x, ty - 1.5, Z.w, 3, {grad:{ang:90, stops:[[0, VI], [50, CY], [100, MG]]}, r:2});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), cx = x + 28;
    K.poly(C, [[cx, ty - 11], [cx + 11, ty], [cx, ty + 11], [cx - 11, ty]], {fill:INK, stroke:[CY, VI, MG][k % 3], lw:2});
    C.line(cx, ty + 11, cx, cy, {color:'#FFFFFF', lw:1, op:0.35, dash:true});
    var cc = glass(C, x, cy, cw, ch, {edge:false});
    C.text(x + pad, cy + pad, cw - pad * 2, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:[CY, VI, MG][k % 3], lh:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, cy + pad + hs * 1.2 + 12, cw - pad * 2, ch - pad * 2 - hs * 1.2 - 12, t.d, {font:'body', size:ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2 - (o.img ? 0 : 190);
  if (!o.img) { cube(C, Z.x + Z.w - 120, Z.y + Z.h / 2, 84, C.i); cube(C, Z.x + Z.w - 210, Z.y + Z.h / 2 + 96, 30, C.i + 1); }
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:600, color:CY, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:'#FFFFFF', lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cubes(C, cx, cy, s){ cube(C, cx, cy, 150 * s, 0); cube(C, cx + 190 * s, cy - 150 * s, 62 * s, 1); cube(C, cx - 200 * s, cy + 140 * s, 48 * s, 2); cube(C, cx + 170 * s, cy + 170 * s, 30 * s, 0, 0.8); }
function brand(C, x, y){ var f = C.cover; if (!f.company) return; K.poly(C, [[x + 11, y + 1], [x + 21, y + 11], [x + 11, y + 21], [x + 1, y + 11]], {fill:CY}); C.text(x + 32, y, 420, 22, f.company, {font:'head', size:16, wt:600, color:'#FFFFFF', va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', tw = photo ? 600 : 680;
  brand(C, 72, 52);
  if (f.date) C.text(W - 372, 52, 300, 22, f.date, {font:'body', size:13, wt:600, color:MUTE, align:'r', va:'m', lh:1});
  if (photo) { glass(C, 716, 96, 500, 528, {r:28}); D.imgBox(C, 732, 112, 468, 496, {r:20}); cube(C, 730, 600, 52, 1); }
  else cubes(C, 960, 360, 1);
  C.text(72, 150, tw, 34, 'PRESENTATION', {font:'body', size:12, wt:700, color:CY, ls:0.3, lh:1, va:'m'});
  C.text(72, 190, tw, 250, C.s.title, {font:'head', fit:{max:84, min:34}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'m', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 60, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 56); glass(C, 72, 560, pw, 46, {r:23, edge:false}); C.ell(72 + 16, 560 + 16, 14, 14, {fill:CY}); C.text(72 + 42, 560, pw - 60, 46, who, {font:'body', size:14, wt:600, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', tw = photo ? 600 : 660;
  brand(C, 72, 52);
  if (photo) { glass(C, 716, 96, 500, 528, {r:28}); D.imgBox(C, 732, 112, 468, 496, {r:20}); cube(C, 1196, 116, 44, 2); }
  else cubes(C, 980, 360, 0.9);
  C.text(72, 130, tw, 200, C.s.title || 'Thank You', {font:'head', fit:{max:96, min:38}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(72, 346, tw - 40, 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  glass(C, 72, 440, tw - 40, 28 + cs.length * 54, {});
  cs.forEach(function(c, k){
    var y = 454 + k * 54;
    C.icon(72 + 26, y + 15, 22, c.icon, [CY, VI, MG][k % 3]);
    C.text(72 + 66, y, tw - 140, 52, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(72 + 26, y, tw - 92, 1, {fill:'#FFFFFF', op:0.13});
  });
}
function agendaA(C, o){
  C.text(72, 140, 420, 20, 'CONTENTS', {font:'body', size:12, wt:700, color:CY, ls:0.3, lh:1});
  var te = C.text(72, 176, 430, 230, C.s.title || 'Agenda', {font:'head', fit:{max:76, min:34}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 176 + te._h + 18, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  cube(C, 170, 560, 70, C.i); cube(C, 290, 620, 28, C.i + 1);
  indexPanel(C, {x:540, y:80, w:W - 540 - 72, h:H - 170}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CY, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', x = photo ? 60 : 130, w = photo ? 640 : W - 260;
  if (photo) { glass(C, 724, 96, 496, 528, {r:28}); D.imgBox(C, 740, 112, 464, 496, {r:20}); }
  else cube(C, W - 150, 150, 54, C.i);
  glass(C, x, 130, w, H - 260, {r:26});
  C.text(x + 44, 150, 100, 90, '“', {font:'head', size:120, wt:700, color:CY, lh:1, va:'t', caps:false, ls:0});
  var qe = C.text(x + 50, 236, w - 100, H - 260 - 200, C.s.title, {font:'head', fit:{max:photo ? 34 : 40, min:18}, wt:500, color:'#FFFFFF', lh:1.3, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 50, 236 + qe._h + 26, w - 100, 24, bt, {font:'body', size:15, wt:600, color:CY, va:'m', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'obsidianglass', name:'Obsidian Glass', tag:'Dark · frosted glass',
  desc:'Near-black indigo pages with violet and cyan aurora light, dark frosted glass cards with a glowing edge and translucent glass cubes for pitch and tech decks.',
  fonts:{head:'Space Grotesk', body:'Inter', headW:600, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Space Grotesk:wght@400;500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Big type + glass cubes', {}), V('v2', 'Big type + glass photo', {img:'photo'})],
    closing:[V('v1', 'Cubes + contact panel', {}), V('v2', 'Glass photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + index panel', {fn:agendaA}), V('n2', 'Chip grid', {body:chipGrid}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1, max:8})],
    text:[V('v1', 'Glass statement + cube', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass columns', {body:glassCols, hl:true}), V('n2', 'Steps', {body:steps}), V('n3', 'Chip grid', {body:chipGrid}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1}), V('v3', 'Image left + chip grid', {body:chipGrid, img:'left', cols:1})],
    p3:[V('v1', 'Glass columns', {body:glassCols}), V('n2', 'Steps', {body:steps}), V('n3', 'Index panel', {body:indexPanel, cols:1}), V('n4', 'Chip grid', {body:chipGrid, cols:3}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1}), V('v3', 'Image left + index panel', {body:indexPanel, img:'left', cols:1})],
    p4:[V('v1', 'Chip grid', {body:chipGrid}), V('n2', 'Glass columns', {body:glassCols}), V('n3', 'Steps', {body:steps}), V('n4', 'Index panel', {body:indexPanel}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1}), V('v3', 'Image left + chip grid', {body:chipGrid, img:'left', cols:2})],
    p56:[V('v1', 'Chip grid', {body:chipGrid}), V('n2', 'Index panel', {body:indexPanel}), V('n3', 'Steps', {body:steps}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1}), V('v3', 'Image left + index panel', {body:indexPanel, img:'left', cols:1})],
    p78:[V('v1', 'Chip grid', {body:chipGrid}), V('n2', 'Index panel', {body:indexPanel}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1})],
    stats:[V('v1', 'Glass meters', {body:meterStats}), V('n2', 'Progress rings', {body:'statRings', track:'#2A2756'}), V('n3', 'Big numbers', {body:'statCols', vc:CY}), V('v2', 'Meters + image right', {body:meterStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gradient track', {body:track}), V('n2', 'Steps', {body:steps}), V('n3', 'Index panel', {body:indexPanel}), V('v2', 'Index panel + image right', {body:indexPanel, img:'right', cols:1})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + glass photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: circuittide */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 33 - Circuit Tide : deep teal, thin circuit traces with ring nodes, teal glass panels, gauge stats */
var AQ = '#5FF0E0', BL = '#8FC4FF', MUTE = '#A9C9D4', INK = '#051821';
var BG = {ang:140, stops:[[0, '#051821'], [50, '#0A3040'], [100, '#12586A']]};
var P0 = {bg:'#0A3040', text:'#FFFFFF', title:'#FFFFFF', muted:MUTE, accent:AQ, accent2:BL, card:'#11465A', line:'#2F6F82', onAccent:INK};
var S = {
  margin:72, series:[AQ, BL, '#B6F7EE', '#3FA9C9', '#DFF7FF', '#2DD4BF'],
  header:{kind:'left', top:70, tMax:42, tMin:24, sMax:17, sMin:13, bar:true, barW:46, barH:3, barC:AQ, barGap:20},
  card:{kind:'glass', r:14, fill:'#FFFFFF', op:0.1, line:AQ, lop:0.35, lw:1.2, dark:true, tc:'#FFFFFF', mc:MUTE,
        hl:{kind:'solid', fill:AQ, dark:false, tc:INK, mc:'#12404C'}},
  icon:{kind:'ring', bg:AQ, fg:AQ}, num:{kind:'ring', bg:AQ, fg:AQ},
  conc:{kind:'box', fill:'#FFFFFF', op:0.08, line:AQ, lw:1, r:12, label:'Takeaway', lc:AQ, tc:'#FFFFFF'},
  footer:{fn:foot}, imgFramed:true, imgR:14, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#1C6E80'], [100, '#072733']]}}
};
/* a circuit trace: straight runs with right-angle turns, a ring node at the end */
function trace(C, pts, o){
  o = o || {}; var d = pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; });
  C.path(d, {stroke:o.color || AQ, lw:o.lw || 1.3, lop:o.op != null ? o.op : 0.55});
  var e = pts[pts.length - 1], s = pts[0];
  if (o.end !== false) C.ell(e[0] - 4.5, e[1] - 4.5, 9, 9, {fill:INK, line:o.color || AQ, lw:1.5, lop:o.op != null ? Math.min(1, o.op + 0.3) : 0.9});
  if (o.start) C.ell(s[0] - 3, s[1] - 3, 6, 6, {fill:o.color || AQ, op:o.op != null ? o.op : 0.8});
}
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 14;
  if (o.aq) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#8FF7EA'], [100, '#39C9D6']]}, r:r}); return {tc:INK, mc:'#12404C', ac:INK}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.2], [60, '#FFFFFF', 0.07], [100, '#FFFFFF', 0.11]]}, line:'#FFFFFF', lop:0.3, lw:1.2, r:r});
  return {tc:'#FFFFFF', mc:MUTE, ac:AQ};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  trace(C, [[M, y + 9], [M + 26, y + 9]], {start:true, op:0.6});
  if (f.company) C.text(M + 42, y, 420, 18, f.company, {font:'body', size:11.5, wt:600, color:MUTE, lh:1, va:'m', caps:true, ls:0.12});
  C.text(W - M - 80, y, 80, 18, pad2(C.i + 1), {font:'head', size:13, wt:600, color:AQ, align:'r', lh:1, va:'m', caps:false, ls:0.08});
}
function bg(C, kind){
  C.bg({grad:BG});
  var odd = (C.i || 0) % 2;
  D.glow(C, odd ? W * 0.1 : W * 0.88, H * 0.3, 460, 300, '#3FD6D0', 0.22);
  D.glow(C, odd ? W * 0.9 : W * 0.15, H * 1.0, 480, 260, '#03121A', 0.5);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') { trace(C, [[W, 44], [W - 150, 44], [W - 176, 70], [W - 230, 70]], {op:0.5}); trace(C, [[W, 58], [W - 96, 58]], {op:0.35}); }
  if (im !== 'left' && im !== 'bottom') trace(C, [[0, H - 86], [34, H - 86], [50, H - 70]], {op:0.4});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* cards hanging from a shared bus line */
function busCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), gx = 18, gy = 44, top = 26, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - top - gy * (rows - 1)) / rows, pad = 22;
  var F = K.fitHD(C, it, cw - pad * 2, 54, ch - pad * 2, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 6);
  for (var r = 0; r < rows; r++) {
    var by = Z.y + r * (ch2 + gy) + 6, cnt = Math.min(cols, n - r * cols);
    C.path([['M', Z.x, by], ['L', Z.x + (cnt - 1) * (cw + gx) + cw / 2, by]], {stroke:AQ, lw:1.3, lop:0.55});
    C.ell(Z.x - 3, by - 3, 6, 6, {fill:AQ});
  }
  it.forEach(function(t, k){
    var r2 = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), by2 = Z.y + r2 * (ch2 + gy) + 6, y = by2 + top - 6, cc = glass(C, x, y, cw, ch2, {aq:o.hl && k === 0});
    C.line(x + cw / 2, by2, x + cw / 2, y, {color:AQ, lw:1.3, op:0.55});
    C.ell(x + cw / 2 - 5, by2 - 5, 10, 10, {fill:INK, line:AQ, lw:1.6});
    K.hd(C, x + pad, y + pad, cw - pad * 2, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* spec sheet: heading pill on the left, a trace to the explanation on the right */
function specRows(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), gy = 12, rh = Math.min((Z.h - gy * (n - 1)) / n, 120), pw = hasH ? Math.min(300, Z.w * 0.3) : 64, tx = Z.x + pw + 62, tw = Z.w - pw - 62;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), pw - 36, rh - 16, {font:'head', max:17, min:11.5, maxLines:2}) : 16;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 8, {font:'body', max:n <= 3 ? 18 : 15.5, min:10.5, lh:1.38});
  var tot = rh * n + gy * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.4);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + gy), ph = Math.min(rh, 64), py = y + (rh - ph) / 2;
    glass(C, Z.x, py, pw, ph, {r:ph / 2, aq:o.hl && k === 0});
    C.text(Z.x + 18, py, pw - 36, ph, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:o.hl && k === 0 ? INK : '#FFFFFF', align:'c', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    trace(C, [[Z.x + pw, y + rh / 2], [tx - 16, y + rh / 2]], {op:0.6});
    C.text(tx, y, tw, rh, t.d, {font:'body', size:ds, color:'#E6F4F7', va:'m', lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* one glass panel cut into cells, icon on top of each */
function cells(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 24, ic = rows > 1 ? 34 : 44;
  glass(C, Z.x, Z.y, Z.w, Z.h, {});
  var F = K.fitHD(C, it, cw - pad * 2, 50, ch - pad * 2 - ic - 14, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * ch, bh = blockH(C, t, cw - pad * 2, F), oy = Math.max(pad, (ch - ic - 14 - bh) / 2);
    if (c) C.rect(x - 0.5, y + 18, 1, ch - 36, {fill:'#FFFFFF', op:0.18});
    if (r && !c) C.rect(Z.x + 18, y - 0.5, Z.w - 36, 1, {fill:'#FFFFFF', op:0.18});
    C.icon(x + (cw - ic) / 2, y + oy, ic, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), AQ);
    K.hd(C, x + pad, y + oy + ic + 14, cw - pad * 2, ch - oy - ic - 14 - 10, t, F, {tc:'#FFFFFF', mc:MUTE, align:'c'});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: half-ring gauges */
function gauges(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), gx = 20, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var r = Math.min(cw * 0.36, ch * 0.42, 120), vals = st.map(sv), labs = st.map(sl), pad = 16;
  var vs = C.groupFit(vals, r * 1.3, r * 0.5, {font:'head', max:42, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - pad * 2, 46, {font:'head', max:17, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - pad * 2, ch - r - 110, {font:'body', max:14.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var rr = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + rr * (ch + gy), cx = x + cw / 2, cy = y + r + 12, p = pct(vals[k], k);
    D.arc(C, cx, cy, r, 180, 360, {color:'#FFFFFF', lw:12, op:0.14});
    D.arc(C, cx, cy, r, 180, 180 + 180 * p / 100, {color:k % 2 ? BL : AQ, lw:12});
    var ex = cx + r * Math.cos((180 + 180 * p / 100) * Math.PI / 180), ey = cy + r * Math.sin((180 + 180 * p / 100) * Math.PI / 180);
    C.ell(ex - 8, ey - 8, 16, 16, {fill:INK, line:'#FFFFFF', lw:2});
    C.text(cx - r * 0.75, cy - r * 0.52, r * 1.5, r * 0.5, vals[k], {font:'head', size:vs, wt:600, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = cy + 18, lh = C.measureText(labs[k], cw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + pad, ly, cw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:600, color:AQ, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + lh + 8, cw - pad * 2, y + ch - ly - lh - 8, t.x, {font:'body', size:ds, color:MUTE, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a stepped trace, glass tags alternating above and below */
function stepLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / n, my = Z.y + Z.h / 2, amp = 22, cw = Math.min(slot * 1.6 - 14, 320), ch = Z.h / 2 - 54, pad = 16;
  if (n <= 3) cw = slot - 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2, 30, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - hs * 1.2 - 8, {font:'body', max:15.5, min:10.5, lh:1.38});
  var d = [['M', Z.x, my + amp]];
  it.forEach(function(t, k){ var x0 = Z.x + slot * k, x1 = x0 + slot, yy = my + (k % 2 ? -amp : amp); d.push(['L', x0 + slot * 0.3, yy]); d.push(['L', x1 - slot * 0.3, yy]); if (k < n - 1) d.push(['L', x1 + slot * 0.0, my + ((k + 1) % 2 ? -amp : amp)]); else d.push(['L', x1, yy]); });
  C.path(d, {stroke:AQ, lw:1.6, lop:0.7});
  it.forEach(function(t, k){
    var cx = Z.x + slot * (k + 0.5), yy = my + (k % 2 ? -amp : amp), up = n <= 3 ? false : k % 2 === 1, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw / 2));
    var dh = C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(ch, pad * 2 + hs * 1.2 + 8 + dh + 4), y = up ? yy - 26 - hh : yy + 26;
    C.line(cx, yy, cx, up ? y + hh : y, {color:AQ, lw:1.2, op:0.6});
    C.ell(cx - 8, yy - 8, 16, 16, {fill:INK, line:AQ, lw:2}); C.ell(cx - 3, yy - 3, 6, 6, {fill:AQ});
    var cc = glass(C, x, y, cw, hh, {});
    C.text(x + pad, y + pad, cw - pad * 2, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:AQ, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, y + pad + hs * 1.2 + 8, cw - pad * 2, hh - pad * 2 - hs * 1.2 - 8, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
  trace(C, [[Z.x + Z.w - 30, Z.y + Z.h], [Z.x + Z.w - 30, Z.y + Z.h - 60], [Z.x + Z.w - 90, Z.y + Z.h - 120]], {op:0.5});
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:26, min:16}, wt:600, color:AQ, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:'#FFFFFF', lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function rings(C, cx, cy, s){
  [150, 122, 96, 70].forEach(function(r, k){ D.arc(C, cx, cy, r * s, 100 + k * 26, 380 + k * 14, {color:k % 2 ? BL : '#FFFFFF', lw:k === 0 ? 5 : 3, op:0.85 - k * 0.12}); });
  C.ell(cx - 26 * s, cy - 26 * s, 52 * s, 52 * s, {fill:AQ}); D.glow(C, cx, cy, 120 * s, 120 * s, AQ, 0.3);
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; C.ell(x, y + 3, 16, 16, {line:AQ, lw:2}); C.ell(x + 5, y + 8, 6, 6, {fill:AQ}); C.text(x + 28, y, 420, 22, f.company, {font:'head', size:15, wt:600, color:'#FFFFFF', va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 52);
  trace(C, [[0, 150], [46, 150], [46, 250], [20, 276]], {op:0.5}); trace(C, [[W, 620], [W - 120, 620], [W - 150, 650], [W - 240, 650]], {op:0.5}); trace(C, [[W, 640], [W - 70, 640]], {op:0.35});
  if (photo) { D.imgBox(C, 730, 0, W - 730, H, {r:0}); C.rect(730, 0, 3, H, {fill:AQ}); trace(C, [[730, 520], [680, 520], [656, 544]], {op:0.8}); }
  else { rings(C, 1010, 330, 1.1); trace(C, [[800, 560], [880, 560], [910, 530], [990, 530]], {op:0.6}); }
  var tw = photo ? 600 : 640;
  C.text(72, 190, tw, 240, C.s.title, {font:'head', fit:{max:66, min:30}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'m', lh:1.1});
  if (C.s.subtitle) { glass(C, 72, 452, tw, 64, {r:32}); C.text(100, 452, tw - 56, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6F4F7', ed:{f:'subtitle'}, lh:1.35, va:'m', maxLines:2}); }
  var who = [f.presenter, f.designation, f.date].filter(Boolean).join('   ·   ');
  if (who) C.text(72, 560, tw, 22, who, {font:'body', size:13.5, wt:600, color:MUTE, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', tw = photo ? 600 : 640;
  brand(C, 72, 52);
  if (photo) { D.imgBox(C, 730, 0, W - 730, H, {r:0}); C.rect(730, 0, 3, H, {fill:AQ}); }
  else rings(C, 1010, 350, 1);
  glass(C, 72, 150, tw, 400, {r:18});
  C.text(110, 180, tw - 76, 110, C.s.title || 'Thank You', {font:'head', fit:{max:60, min:30}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'m', lh:1.08});
  if (C.s.subtitle) C.text(110, 300, tw - 76, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MUTE, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 384 + k * 50;
    C.ell(110, y + 6, 34, 34, {line:AQ, lw:1.6}); C.icon(119, y + 15, 16, c.icon, AQ);
    C.text(160, y, tw - 130, 46, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(72, 150, 400, 220, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1.08});
  C.rect(72, 150 + te._h + 18, 46, 3, {fill:AQ});
  if (C.s.subtitle) C.text(72, 150 + te._h + 40, 380, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  rings(C, 190, 590, 0.6);
  specRows(C, {x:520, y:80, w:W - 520 - 72, h:H - 170}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:AQ, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', x = photo ? 72 : 150, w = photo ? 610 : W - 300;
  if (photo) { D.imgBox(C, 730, 0, W - 730, H, {r:0}); C.rect(730, 0, 3, H, {fill:AQ}); }
  trace(C, [[x - 40, 200], [x - 40, 150], [x, 150]], {op:0.7, end:false}); trace(C, [[x + w + 40, H - 200], [x + w + 40, H - 150], [x + w, H - 150]], {op:0.7, end:false});
  C.text(x, 170, 90, 80, '“', {font:'head', size:110, wt:600, color:AQ, lh:1, va:'t', caps:false});
  var qe = C.text(x, 250, w, H - 250 - 220, C.s.title, {font:'head', fit:{max:photo ? 32 : 38, min:18}, wt:500, color:'#FFFFFF', lh:1.32, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 250 + qe._h + 26, 40, 3, {fill:AQ}); C.text(x, 250 + qe._h + 42, w, 24, bt, {font:'body', size:15, wt:600, color:MUTE, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'circuittide', name:'Circuit Tide', tag:'Tech · teal glass',
  desc:'Deep teal pages wired with thin circuit traces and ring nodes, teal glass panels, bus-line cards and gauge stats for technology, AI and engineering decks.',
  fonts:{head:'Exo 2', body:'Inter', headW:600, bodyW:400, headLh:1.1, headLs:0.03, gf:'Exo 2:wght@400;500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Signal rings', {}), V('v2', 'Photo panel', {img:'photo'})],
    closing:[V('v1', 'Glass panel + rings', {}), V('v2', 'Glass panel + photo', {img:'photo'})],
    agenda:[V('v1', 'Title + spec rows', {fn:agendaA}), V('n2', 'Bus cards', {body:busCards}), V('v2', 'Spec rows + image right', {body:specRows, img:'right', max:8})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Bus cards', {body:busCards, hl:true}), V('n2', 'Spec rows', {body:specRows, hl:true}), V('n3', 'Glass cells', {body:cells}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + bus cards', {body:busCards, img:'left', cols:1})],
    p3:[V('v1', 'Bus cards', {body:busCards}), V('n2', 'Glass cells', {body:cells}), V('n3', 'Spec rows', {body:specRows}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + spec rows', {body:specRows, img:'left'})],
    p4:[V('v1', 'Glass cells', {body:cells}), V('n2', 'Bus cards', {body:busCards}), V('n3', 'Spec rows', {body:specRows}), V('n4', 'Four cells in a row', {body:cells, cols:4}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + cells', {body:cells, img:'left', cols:2})],
    p56:[V('v1', 'Bus cards', {body:busCards}), V('n2', 'Glass cells', {body:cells}), V('n3', 'Spec rows', {body:specRows}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + cells', {body:cells, img:'left', cols:2})],
    p78:[V('v1', 'Glass cells', {body:cells}), V('n2', 'Bus cards', {body:busCards}), V('v2', 'Cells + image right', {body:cells, img:'right', cols:2})],
    stats:[V('v1', 'Gauges', {body:gauges}), V('n2', 'Stat cards', {body:'statCards', icons:true}), V('n3', 'Big numbers', {body:'statCols', vc:AQ}), V('v2', 'Gauges + image right', {body:gauges, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Stepped trace', {body:stepLine}), V('n2', 'Bus cards', {body:busCards}), V('n3', 'Spec rows', {body:specRows}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'})],
    quote:[V('v1', 'Bracketed quote', {}), V('v2', 'Quote + photo panel', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: halotype */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 34 - Halo Type : oversized headings, giant faded page numerals, a curtain of light bars, white plates and charcoal tiles */
var EL = '#19B8FF', DEEPB = '#0A2A8C', INK = '#05070D', MUTE = '#B9C7DD', CH = '#141A26';
var P0 = {bg:'#05070D', text:'#FFFFFF', title:'#FFFFFF', muted:MUTE, accent:EL, accent2:'#6FE0FF', card:CH, line:'#2A3550', onAccent:INK};
var S = {
  margin:64, series:[EL, '#FFFFFF', '#6FE0FF', '#2D6BFF', '#BFEFFF', '#0FA3E0'],
  header:{kind:'left', top:54, tMax:64, tMin:30, sMax:17, sMin:13, gap:10, tH:140},
  card:{kind:'solid', r:6, fill:CH, op:0.92, line:'#FFFFFF', lop:0.12, lw:1, dark:true, tc:'#FFFFFF', mc:MUTE,
        hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:INK, mc:'#394357'}},
  icon:{kind:'plain', fg:EL}, num:{kind:'big', fg:EL},
  conc:{kind:'line', lineC:'#FFFFFF', label:'Takeaway', lc:EL, tc:'#FFFFFF'},
  footer:{fn:foot}, imgFramed:false, bodyGap:24,
  imgPh:{grad:{ang:135, stops:[[0, '#1F6FE0'], [100, '#07102A']]}}
};
function bars(C, x, w, top, h, seed){
  var n = Math.max(6, Math.round(w / 26)), bw = w / n;
  for (var k = 0; k < n; k++) { var t = Math.abs(Math.sin((k + 1) * 12.9898 + seed * 4.1)) , op = 0.05 + 0.3 * t * (1 - Math.abs(k / (n - 1) - 0.5) * 1.2);
    if (op > 0.02) C.rect(x + k * bw, top, bw * 0.62, h, {grad:{ang:180, stops:[[0, '#7FE3FF', 0], [45, '#7FE3FF', op], [100, '#1E5BFF', 0]]}}); }
}
function plus(C, x, y, s, color){ C.rect(x, y + s * 0.38, s, s * 0.24, {fill:color}); C.rect(x + s * 0.38, y, s * 0.24, s, {fill:color}); }
function foot(C){
  var M = 64, y = H - 36, f = C.cover;
  if (f.website || f.company) C.text(M, y, 420, 18, f.website || f.company, {font:'body', size:11.5, wt:600, color:MUTE, lh:1, va:'m'});
  plus(C, W - M - 12, y + 3, 12, EL);
}
function bg(C, kind){
  var k = (C.i || 0) % 4, light = kind === 'text' || kind === 'quote';
  C.bg({grad:{ang:[120, 200, 150, 60][k], stops:[[0, '#05070D'], [55, '#081A4A'], [100, light ? '#1D8FE6' : '#0E4FBF']]}});
  bars(C, [W * 0.45, 0, W * 0.2, W * 0.55][k], W * 0.55, 0, H, C.i || 0);
  D.glow(C, [W * 0.9, W * 0.1, W * 0.5, W * 0.85][k], H * 0.9, 460, 280, EL, 0.28);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img, n = pad2(C.i + 1);
  if (im !== 'right' && im !== 'top') C.text(W - 460, -40, 420, 300, n, {font:'head', size:300, wt:800, color:'#FFFFFF', op:0.07, align:'r', lh:1, va:'t', caps:false, ls:-0.04});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* white plates: plus-mark heading, black text */
function plates(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 14, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22;
  var F = K.fitHD(C, it, cw - pad * 2 - 24, 52, ch - pad * 2, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 8), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.25);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), dark = o.alt && (r + c) % 2 === 1;
    C.rect(x, y, cw, ch2, {fill:dark ? CH : '#FFFFFF', op:dark ? 0.92 : 0.96, r:6, line:dark ? '#FFFFFF' : null, lop:0.12});
    plus(C, x + pad, y + pad + 3, 14, EL);
    if (t.h) C.text(x + pad + 24, y + pad, cw - pad * 2 - 24, F.hh + 4, t.h, {font:'head', size:F.hs, color:dark ? '#FFFFFF' : INK, lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    var dy = y + pad + (t.h ? F.hh + 10 : 26);
    C.text(x + pad, dy, cw - pad * 2, y + ch2 - pad - dy + 6, t.d, {font:'body', size:F.ds, color:dark ? MUTE : '#2B3445', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* open plus list: no cards, plus marks, hairlines */
function plusList(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 720 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), tw = cw - 40;
  var F = K.fitHD(C, it, tw, 34, rh - 26, {hMax:22, dMax:15.5, hMin:13, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh;
    C.rect(x, y, cw, 1, {fill:'#FFFFFF', op:0.25});
    plus(C, x, y + 20, 16, EL);
    K.hd(C, x + 40, y + 16, tw, rh - 24, t, F, {tc:'#FFFFFF', mc:MUTE});
  });
}
/* poster split: first point huge on the left, the rest as charcoal tiles */
function poster(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 720) return plates(C, Z, o);
  var lw = Math.round(Z.w * 0.42), g = 14, rest = it.slice(1), m = rest.length, cols = m > 2 ? 2 : 1, rows = Math.ceil(m / cols), rw = (Z.w - lw - 30 - g * (cols - 1)) / cols, rh = (Z.h - g * (rows - 1)) / rows, pad = 22;
  var t0 = it[0];
  var he = C.text(Z.x, Z.y, lw, Z.h * 0.5, t0.h || t0.d, {font:'head', fit:{max:54, min:22}, wt:800, color:'#FFFFFF', lh:1.02, ed:{f:'x-part', i:t0.i, p:t0.h ? 0 : 1}, maxLines:4, va:'t'});
  C.rect(Z.x, Z.y + he._h + 18, 60, 6, {fill:EL});
  if (t0.h) C.text(Z.x, Z.y + he._h + 44, lw - 20, Z.h - he._h - 44, t0.d, {font:'body', fit:{max:18, min:12}, color:MUTE, lh:1.42, ed:{f:'x-part', i:t0.i, p:1}});
  var F = K.fitHD(C, rest, rw - pad * 2, 50, rh - pad * 2 - 30, {hMax:18, dMax:14.5, hMin:12.5, dMin:10.5, noBoost:true});
  rest.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + lw + 30 + c * (rw + g), y = Z.y + r * (rh + g), white = k === 0;
    C.rect(x, y, rw, rh, {fill:white ? '#FFFFFF' : CH, op:white ? 0.96 : 0.92, r:6, line:white ? null : '#FFFFFF', lop:0.12});
    C.text(x + pad, y + pad - 2, 80, 24, pad2(k + 2), {font:'head', size:18, wt:800, color:EL, lh:1, caps:false, ls:0});
    K.hd(C, x + pad, y + pad + 30, rw - pad * 2, rh - pad * 2 - 30, t, F, {tc:white ? INK : '#FFFFFF', mc:white ? '#2B3445' : MUTE});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: thick rings, value inside */
function fatRings(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, d = Math.min(cw * 0.62, ch * 0.62, 230), vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, d * 0.56, d * 0.3, {font:'head', max:44, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, 46, {font:'head', max:18, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 40, ch - d - 90, {font:'body', max:14.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * ch, cx = x + cw / 2;
    C.ring(cx - d / 2, y, d, pct(vals[k], k), {color:k % 2 ? '#FFFFFF' : EL, track:'#1B2C55', th:d * 0.16});
    C.text(cx - d * 0.3, y + d * 0.34, d * 0.6, d * 0.32, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y + d + 16, lh = C.measureText(labs[k], cw - 30, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + 15, ly, cw - 30, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:'#FFFFFF', align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 20, ly + lh + 8, cw - 40, y + ch - ly - lh - 8, t.x, {font:'body', size:ds, color:MUTE, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* stats: charcoal number tiles */
function numTiles(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n <= 2 || Z.w < 700 ? Math.min(n, 2) : (n === 4 ? 2 : 3), rows = Math.ceil(n / cols), g = 14, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, 260), pad = 24, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, cw - pad * 2, ch * 0.42, {font:'head', max:72, min:22, maxLines:1});
  var ls = C.groupFit(labs.map(function(l, k){ return l + (st[k].x ? ' ' + st[k].x : ''); }), cw - pad * 2, ch * 0.34, {font:'body', max:15.5, min:10.5, lh:1.36});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), white = k === 0;
    C.rect(x, y, cw, ch, {fill:white ? '#FFFFFF' : CH, op:white ? 0.96 : 0.92, r:6, line:white ? null : '#FFFFFF', lop:0.12});
    C.text(x + pad, y + pad - 4, cw - pad * 2, ch * 0.46, vals[k], {font:'head', size:vs, wt:800, color:white ? DEEPB : '#FFFFFF', va:'m', lh:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + ch * 0.46, 30, 3, {fill:EL});
    C.text(x + pad, y + pad + ch * 0.46 + 14, cw - pad * 2, ch - pad * 2 - ch * 0.46 - 12, labs[k], {font:'body', size:ls, wt:600, color:white ? '#2B3445' : MUTE, lh:1.36, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var y = Z.y;
  if (it.h) { plus(C, Z.x, y + 4, 16, EL); var he = C.text(Z.x + 30, y, Z.w - 30, 60, it.h, {font:'head', fit:{max:24, min:15}, wt:800, color:EL, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 22; }
  C.text(Z.x, y, Z.w * (o.img ? 1 : 0.86), Z.y + Z.h - y, it.d, {font:'head', fit:{max:it.h ? 40 : 46, min:16}, wt:700, color:'#FFFFFF', lh:1.2, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}, va:'t'});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', yr = (String(f.date || '').match(/(20\d\d)/) || [])[1] || '';
  if (f.website || f.company) C.text(64, 44, 500, 20, f.website || f.company, {font:'body', size:12.5, wt:600, color:'#FFFFFF', lh:1, va:'m', ed:{f:f.website ? 'tf-website' : 'tf-company'}});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) C.text(W - 564, 44, 500, 20, 'Presented by ' + who, {font:'body', size:12.5, wt:600, color:'#FFFFFF', align:'r', lh:1, va:'m'});
  if (photo) D.imgBox(C, 700, 110, W - 700 - 64, 330, {r:6});
  else if (yr) C.text(W - 800, 70, 760, 280, yr, {font:'head', size:250, wt:800, color:'#FFFFFF', op:0.12, align:'r', lh:1, va:'t', caps:false, ls:-0.05, maxLines:1});
  if (C.s.subtitle) C.text(64, 120, photo ? 560 : 520, 150, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6F1FF', ed:{f:'subtitle'}, lh:1.45, maxLines:6, va:'t'});
  C.text(56, 330, W - 112 - (f.company ? 250 : 0), 330, C.s.title, {font:'head', fit:{max:150, min:44}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:0.94, ls:-0.03});
  if (f.company) { plus(C, W - 64 - 210, 600, 14, EL); C.text(W - 64 - 186, 594, 186, 60, f.company, {font:'body', size:13, wt:700, color:'#FFFFFF', caps:true, ls:0.3, lh:1.5, va:'t', ed:{f:'tf-company'}, maxLines:2}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', cs = D.contacts(C), pw = 470, ph = Math.max(120, 36 + cs.length * 64);
  if (photo) D.imgBox(C, 64, 64, pw, 250, {r:6});
  C.rect(64, photo ? 340 : 110, pw, ph, {fill:'#FFFFFF', op:0.96, r:6});
  cs.forEach(function(c, k){
    var y = (photo ? 340 : 110) + 18 + k * 64;
    C.ell(64 + 24, y + 14, 36, 36, {line:DEEPB, lw:1.6}); C.icon(64 + 33, y + 23, 18, c.icon, DEEPB);
    C.text(64 + 78, y, pw - 100, 64, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(64 + 24, y, pw - 48, 1, {fill:'#C9D3E3'});
  });
  C.text(580, 250, W - 580 - 56, 400, C.s.title || 'Thank You', {font:'head', fit:{max:130, min:44}, wt:800, color:'#FFFFFF', align:'r', ed:{f:'title'}, maxLines:3, va:'b', lh:0.96, ls:-0.03});
  if (C.s.subtitle) C.text(580, 150, W - 580 - 64, 90, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6F1FF', align:'r', ed:{f:'subtitle'}, lh:1.45, maxLines:3, va:'b'});
}
function agendaA(C, o){
  C.text(56, 110, 520, 300, C.s.title || 'Agenda', {font:'head', fit:{max:120, min:44}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:0.96, ls:-0.03});
  if (C.s.subtitle) C.text(64, 560, 440, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3, va:'b'});
  plusList(C, {x:640, y:90, w:W - 640 - 64, h:H - 180}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:EL, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', w = photo ? 640 : W - 128;
  if (photo) D.imgBox(C, 760, 0, W - 760, H, {r:0});
  plus(C, 64, 110, 28, EL);
  var qe = C.text(64, 170, w, 360, C.s.title, {font:'head', fit:{max:photo ? 44 : 60, min:20}, wt:800, color:'#FFFFFF', lh:1.1, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(64, 170 + qe._h + 30, w, 1, {fill:'#FFFFFF', op:0.3}); C.text(64, 170 + qe._h + 46, w, 24, bt, {font:'body', size:15, wt:600, color:MUTE, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'halotype', name:'Halo Type', tag:'Big type · electric blue',
  desc:'Oversized headings, giant faded page numerals and a curtain of light bars on black-to-electric-blue pages, with white plates and charcoal tiles.',
  fonts:{head:'Archivo', body:'Inter', headW:800, bodyW:400, headLh:1.02, headLs:-0.02, gf:'Archivo:wght@500;600;700;800;900|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Giant title + year', {}), V('v2', 'Giant title + photo', {img:'photo'})],
    closing:[V('v1', 'Contact plate + giant title', {}), V('v2', 'Photo + contact plate', {img:'photo'})],
    agenda:[V('v1', 'Giant title + plus list', {fn:agendaA}), V('n2', 'White plates', {body:plates}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1})],
    text:[V('v1', 'Big statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'White plates', {body:plates}), V('n2', 'Poster split', {body:poster}), V('n3', 'Plus list', {body:plusList, cols:2}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p3:[V('v1', 'White plates', {body:plates}), V('n2', 'Poster split', {body:poster}), V('n3', 'Plus list', {body:plusList, cols:1}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plus list', {body:plusList, img:'left', cols:1})],
    p4:[V('v1', 'Poster split', {body:poster}), V('n2', 'Checker plates', {body:plates, cols:2, alt:true}), V('n3', 'Plus list', {body:plusList}), V('n4', 'White plates', {body:plates}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:2})],
    p56:[V('v1', 'Checker plates', {body:plates, alt:true}), V('n2', 'Plus list', {body:plusList}), V('n3', 'Poster split', {body:poster}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plus list', {body:plusList, img:'left', cols:1})],
    p78:[V('v1', 'Checker plates', {body:plates, alt:true}), V('n2', 'Plus list', {body:plusList}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1})],
    stats:[V('v1', 'Number tiles', {body:numTiles}), V('n2', 'Thick rings', {body:fatRings}), V('n3', 'Big numbers', {body:'statCols', vc:EL}), V('v2', 'Tiles + image right', {body:numTiles, img:'right'}), V('v3', 'Image left + rings', {body:fatRings, img:'left'})],
    timeline:[V('v1', 'Horizontal timeline', {body:'tlH'}), V('n2', 'White plates', {body:plates}), V('n3', 'Plus list', {body:plusList}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1})],
    quote:[V('v1', 'Big quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: sunsetglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 35 - Sunset Glass : warm peach-to-rose light page, clear glass discs and ribbon cards, serif headings */
var PLUM = '#3A1D3F', BODY = '#6B4A63', COR = '#F0603F', ROSE = '#E8497C', AMB = '#F5A524', VIO = '#8B5CF6';
var RIB = [COR, ROSE, AMB, VIO, '#2EB6A8'];
var P0 = {bg:'#FFF1E8', text:PLUM, title:PLUM, muted:BODY, accent:COR, accent2:ROSE, card:'#FFFFFF', line:'#F2CFC2', onAccent:'#FFFFFF'};
var S = {
  margin:74, series:[COR, ROSE, AMB, VIO, '#2EB6A8', '#F7B79F'],
  header:{kind:'left', top:66, tMax:48, tMin:26, sMax:17, sMin:13, bar:true, barW:54, barH:4, barC:COR, barR:2, barGap:22},
  card:{kind:'glass', r:20, fill:'#FFFFFF', op:0.55, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.07, dark:false, tc:PLUM, mc:BODY,
        hl:{kind:'grad', grad:{ang:135, stops:[[0, '#F7785A'], [100, '#E8497C']]}, dark:true, tc:'#FFFFFF', mc:'#FFE9E2'}},
  icon:{kind:'soft', bg:COR, fg:COR, op:0.15}, num:{kind:'circle', bg:COR, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#FFFFFF', op:0.7, line:'#FFFFFF', lw:1.5, label:'Takeaway', lc:COR, tc:PLUM},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#FFC7A8'], [100, '#F28BA8']]}}
};
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 20;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#F7785A'], [100, '#E8497C']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.3], [50, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#FFE9E2', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.88], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.06});
  return {tc:PLUM, mc:BODY, ac:COR};
}
/* a clear glass disc: pale fill, bright rim, soft inner highlight */
function disc(C, cx, cy, r, k, o){
  o = o || {}; var c = RIB[((k % RIB.length) + RIB.length) % RIB.length];
  D.glow(C, cx, cy + r * 0.2, r * 1.25, r * 1.1, c, 0.2);
  C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.92], [100, '#FFFFFF', 0.35]]}, line:'#FFFFFF', lw:2, lop:1});
  C.ell(cx - r * 0.86, cy - r * 0.86, r * 1.72, r * 1.72, {line:c, lw:o.lw || 2, lop:0.55});
  C.ell(cx - r * 0.55, cy - r * 0.82, r * 0.8, r * 0.36, {fill:'#FFFFFF', op:0.7});
}
function sun(C, cx, cy, r){
  D.glow(C, cx, cy, r * 2.1, r * 2.1, '#FFB36B', 0.55);
  C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:160, stops:[[0, '#FFD08A'], [55, '#F7785A'], [100, '#E8497C']]}});
  C.ell(cx - r * 0.6, cy - r * 0.8, r * 0.9, r * 0.42, {fill:'#FFFFFF', op:0.35});
}
function foot(C){
  var M = 74, y = H - 40, f = C.cover;
  if (f.company) C.text(M, y, 420, 18, f.company, {font:'body', size:12, wt:700, color:PLUM, lh:1, va:'m'});
  C.ell(W - M - 30, y - 6, 30, 30, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.92], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lw:1.5});
  C.text(W - M - 30, y - 6, 30, 30, String(C.i + 1), {font:'body', size:12, wt:700, color:COR, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:155, stops:[[0, '#FFF4EA'], [55, '#FFE3D6'], [100, '#F9D3E0']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.95, W * 0.05, W * 0.5][k], H * 0.0, 520, 340, '#FFC38F', 0.75);
  D.glow(C, [W * 0.0, W * 1.0, W * 0.05][k], H * 1.0, 500, 330, '#F6A5C1', 0.65);
  D.glow(C, [W * 0.6, W * 0.35, W * 0.95][k], H * 0.6, 340, 240, '#D9C2FF', 0.45);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') { sun(C, W - 100, 74, 22); disc(C, W - 150, 108, 15, C.i + 1, {lw:1.5}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* glass cards with a coloured ribbon down the left edge */
function ribbonCards(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 20, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26;
  var F = K.fitHD(C, it, cw - pad * 2 - 10, 54, ch - pad * 2 - 30, {hMax:20, dMax:15.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2 - 10, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 36), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), cc = glass(C, x, y, cw, ch2, {hot:o.hl && k === 0}), col = RIB[k % RIB.length];
    if (!(o.hl && k === 0)) C.rect(x + 12, y + 22, 5, ch2 - 44, {fill:col, r:3});
    C.text(x + pad + 6, y + pad - 4, 80, 24, pad2(k + 1), {font:'head', size:20, wt:600, color:o.hl && k === 0 ? '#FFFFFF' : col, lh:1, caps:false, ls:0, italic:true});
    K.hd(C, x + pad + 6, y + pad + 30, cw - pad * 2 - 10, ch2 - pad * 2 - 30, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* glass discs in a row: heading inside the disc, explanation under it */
function discRow(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  if (Z.w < 720 && n > 2) return ribbonCards(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.42, Z.h * 0.3, 130), cy = Z.y + r + 8, pad = 16;
  var hs = C.groupFit(it.map(function(t, k){ return t.h || pad2(k + 1); }), r * 1.36, r * 0.9, {font:'head', max:22, min:12, maxLines:3});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - r * 2 - 40, {font:'body', max:n <= 3 ? 17 : 15, min:10.5, lh:1.4});
  if (n > 1) C.rect(Z.x + cw / 2, cy - 1, Z.w - cw, 2, {fill:'#FFFFFF'});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    disc(C, cx, cy, r, k);
    C.text(cx - r * 0.68, cy - r * 0.5, r * 1.36, r, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:PLUM, align:'c', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:3});
    C.text(cx - cw / 2 + pad, cy + r + 24, cw - pad * 2, Z.y + Z.h - cy - r - 24, t.d, {font:'body', size:ds, color:BODY, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* editorial list: serif numerals, hairlines, no cards */
function editorial(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 720 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), tw = cw - 76;
  var F = K.fitHD(C, it, tw, 34, rh - 26, {hMax:21, dMax:15.5, hMin:13, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(12, (rh - bh) / 2);
    C.text(x, y, 60, rh, pad2(k + 1), {font:'head', size:Math.min(40, rh * 0.42), wt:600, color:RIB[k % RIB.length], va:'m', lh:1, caps:false, ls:0, italic:true});
    K.hd(C, x + 76, y + oy, tw, rh - oy - 4, t, F, {tc:PLUM, mc:BODY});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:PLUM, op:0.14});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: glass columns whose height follows the value */
function columns(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  if (Z.w < 640 && n > 2) return discStats(C, Z, o);
  var g = 22, cw = (Z.w - g * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), labH = 86, maxH = Z.h - labH, pad = 18;
  var vs = C.groupFit(vals, cw - pad * 2, 70, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs.map(function(l, k){ return l; }), cw - 8, 44, {font:'body', max:15.5, min:11, lh:1.3, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 8, labH - 44, {font:'body', max:13, min:10, lh:1.35});
  C.rect(Z.x, Z.y + maxH, Z.w, 2, {fill:PLUM, op:0.25});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + g), h = Math.max(vs * 1.5 + pad, maxH * (0.38 + 0.62 * pct(vals[k], k) / 100)), y = Z.y + maxH - h, col = RIB[k % RIB.length];
    C.rect(x, y, cw, h, {grad:{ang:180, stops:[[0, col, 0.9], [100, col, 0.25]]}, r:18});
    C.rect(x, y, cw, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.45], [60, '#FFFFFF', 0.05]]}, line:'#FFFFFF', lw:1.5, lop:0.9, r:18});
    C.text(x + pad, y + pad, cw - pad * 2, vs * 1.2, vals[k], {font:'head', size:vs, wt:600, color:'#FFFFFF', lh:1, caps:false, ls:-0.01, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + 4, Z.y + maxH + 14, cw - 8, 40, labs[k], {font:'body', size:ls, wt:700, color:PLUM, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 4, Z.y + maxH + 56, cw - 8, labH - 56, t.x, {font:'body', size:ds, color:BODY, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* stats: value inside a glass disc */
function discStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, r = Math.min(cw * 0.36, ch * 0.33, 120), vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, r * 1.4, r * 0.7, {font:'head', max:46, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, 46, {font:'body', max:16.5, min:11.5, lh:1.3, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 40, ch - r * 2 - 90, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var rr = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + rr * ch, cx = x + cw / 2, cy = y + r + 6;
    disc(C, cx, cy, r, k, {lw:3});
    C.text(cx - r * 0.72, cy - r * 0.38, r * 1.44, r * 0.76, vals[k], {font:'head', size:vs, wt:600, color:RIB[k % RIB.length], align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = cy + r + 20, lh = C.measureText(labs[k], cw - 30, {font:'body', max:ls, min:ls, lh:1.3, maxLines:2}).h;
    C.text(x + 15, ly, cw - 30, lh + 4, labs[k], {font:'body', size:ls, wt:700, color:PLUM, align:'c', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 20, ly + lh + 8, cw - 40, y + ch - ly - lh - 8, t.x, {font:'body', size:ds, color:BODY, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a rising path of glass discs with tags */
function rise(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / n, r = Math.min(34, slot * 0.2), lift = Math.min(Z.h * 0.34, 150), cw = slot - 14, pad = 6;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), r * 1.5, r * 0.9, {font:'head', max:17, min:10, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - lift - r * 2 - 30, {font:'body', max:n <= 4 ? 16 : 14.5, min:10.5, lh:1.4});
  var pts = it.map(function(t, k){ return [Z.x + slot * (k + 0.5), Z.y + r + 6 + lift * (1 - (n > 1 ? k / (n - 1) : 0))]; });
  if (n > 1) K.curve(C, pts, {stroke:COR, lw:2.5, lop:0.55});
  it.forEach(function(t, k){
    var cx = pts[k][0], cy = pts[k][1];
    disc(C, cx, cy, r, k, {lw:2});
    C.text(cx - r * 0.8, cy - r * 0.5, r * 1.6, r, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:PLUM, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(cx - cw / 2 + pad, cy + r + 16, cw - pad * 2, Z.y + Z.h - cy - r - 16, t.d, {font:'body', size:ds, color:BODY, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {r:28}), pad = 48, y = Z.y + pad, w = Z.w - pad * 2 - (o.img ? 0 : 160);
  if (!o.img) { sun(C, Z.x + Z.w - 100, Z.y + Z.h - 96, 62); disc(C, Z.x + Z.w - 190, Z.y + Z.h - 50, 30, 3); }
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:30, min:17}, wt:600, color:COR, lh:1.15, italic:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:PLUM, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function scene(C, cx, cy, s){ sun(C, cx, cy, 130 * s); disc(C, cx + 150 * s, cy + 110 * s, 78 * s, 1, {lw:3}); disc(C, cx - 160 * s, cy - 90 * s, 50 * s, 3, {lw:2.5}); disc(C, cx - 70 * s, cy + 170 * s, 30 * s, 2); disc(C, cx + 160 * s, cy - 150 * s, 22 * s, 4); }
function brand(C, x, y){ var f = C.cover; if (!f.company) return; sun(C, x + 11, y + 12, 9); C.text(x + 32, y, 420, 24, f.company, {font:'body', size:15, wt:700, color:PLUM, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 74, 52);
  if (f.date) C.text(W - 374, 52, 300, 24, f.date, {font:'body', size:13, wt:600, color:BODY, align:'r', va:'m', lh:1});
  if (photo) { D.imgBox(C, 730, 110, 476, 500, {shape:'ell'}); C.ell(718, 98, 500, 524, {line:'#FFFFFF', lw:3}); sun(C, 760, 560, 44); disc(C, 1180, 150, 30, 3); }
  else scene(C, 960, 360, 1);
  C.text(74, 170, 620, 250, C.s.title, {font:'head', fit:{max:78, min:32}, wt:600, color:PLUM, ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  C.rect(74, 440, 70, 4, {fill:COR, r:2});
  if (C.s.subtitle) C.text(74, 464, 560, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(560, who.length * 8.4 + 48); glass(C, 74, 570, pw, 46, {r:23}); C.text(74 + 22, 570, pw - 44, 46, who, {font:'body', size:14, wt:700, color:PLUM, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 74, 52);
  if (photo) { D.imgBox(C, 730, 110, 476, 500, {shape:'ell'}); C.ell(718, 98, 500, 524, {line:'#FFFFFF', lw:3}); sun(C, 1170, 560, 40); }
  else scene(C, 980, 360, 0.92);
  C.text(74, 120, 620, 210, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:36}, wt:600, color:PLUM, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(74, 346, 580, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  glass(C, 74, 440, 600, 30 + cs.length * 56, {r:24});
  cs.forEach(function(c, k){
    var y = 455 + k * 56;
    C.ell(74 + 22, y + 8, 40, 40, {fill:RIB[k % RIB.length]}); C.icon(74 + 32, y + 18, 20, c.icon, '#FFFFFF');
    C.text(74 + 78, y, 500, 56, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:PLUM, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(74, 140, 430, 240, C.s.title || 'Agenda', {font:'head', fit:{max:80, min:36}, wt:600, color:PLUM, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02});
  C.rect(74, 140 + te._h + 18, 70, 4, {fill:COR, r:2});
  if (C.s.subtitle) C.text(74, 140 + te._h + 42, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  sun(C, 150, 590, 56); disc(C, 240, 630, 32, 3);
  glass(C, 540, 70, W - 540 - 74, H - 150, {r:28});
  editorial(C, {x:580, y:90, w:W - 580 - 114, h:H - 190}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:COR, tc:PLUM}); return; }
  var photo = o.img === 'photo', x = 74, w = photo ? 620 : W - 148 - 220;
  if (photo) { D.imgBox(C, 760, 120, 440, 480, {shape:'ell'}); C.ell(748, 108, 464, 504, {line:'#FFFFFF', lw:3}); }
  else { sun(C, W - 190, 200, 70); disc(C, W - 120, 330, 40, 3); }
  C.text(x, 110, 120, 110, '“', {font:'head', size:160, wt:600, color:COR, lh:1, va:'t', caps:false, ls:0, italic:true});
  var qe = C.text(x, 220, w, 330, C.s.title, {font:'head', fit:{max:photo ? 38 : 46, min:19}, wt:600, color:PLUM, lh:1.24, italic:true, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 220 + qe._h + 28, 50, 3, {fill:COR, r:2}); C.text(x, 220 + qe._h + 44, w, 24, bt, {font:'body', size:15, wt:700, color:BODY, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'sunsetglass', name:'Sunset Glass', tag:'Light · warm glass',
  desc:'Warm peach-to-rose pages with clear glass discs, ribbon glass cards, a glowing sun motif and serif headings: a soft, human look for stories, proposals and group work.',
  fonts:{head:'Fraunces', body:'Nunito Sans', headW:600, bodyW:400, headLh:1.1, gf:'Fraunces:ital,wght@0,500;0,600;0,700;1,500;1,600|Nunito Sans:wght@400;600;700;800'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Sun + glass discs', {}), V('v2', 'Round photo', {img:'photo'})],
    closing:[V('v1', 'Sun + contact glass', {}), V('v2', 'Round photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + editorial list', {fn:agendaA}), V('n2', 'Ribbon cards', {body:ribbonCards}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass discs', {body:discRow}), V('n2', 'Ribbon cards', {body:ribbonCards, hl:true}), V('n3', 'Editorial list', {body:editorial, cols:1}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + ribbon cards', {body:ribbonCards, img:'left', cols:1})],
    p3:[V('v1', 'Glass discs', {body:discRow}), V('n2', 'Ribbon cards', {body:ribbonCards}), V('n3', 'Editorial list', {body:editorial, cols:1}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + ribbon cards', {body:ribbonCards, img:'left', cols:1})],
    p4:[V('v1', 'Ribbon cards', {body:ribbonCards}), V('n2', 'Glass discs', {body:discRow}), V('n3', 'Editorial list', {body:editorial}), V('n4', 'Ribbon cards in a row', {body:ribbonCards, cols:4}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + ribbon cards', {body:ribbonCards, img:'left', cols:2})],
    p56:[V('v1', 'Ribbon cards', {body:ribbonCards}), V('n2', 'Editorial list', {body:editorial}), V('n3', 'Glass discs', {body:discRow}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + editorial list', {body:editorial, img:'left', cols:1})],
    p78:[V('v1', 'Ribbon cards', {body:ribbonCards}), V('n2', 'Editorial list', {body:editorial}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1})],
    stats:[V('v1', 'Glass columns', {body:columns}), V('n2', 'Glass disc numbers', {body:discStats}), V('n3', 'Progress rings', {body:'statRings', track:'#F6D9CF'}), V('v2', 'Disc numbers + image right', {body:discStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Rising discs', {body:rise}), V('n2', 'Ribbon cards', {body:ribbonCards}), V('n3', 'Editorial list', {body:editorial}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1})],
    quote:[V('v1', 'Serif quote + sun', {}), V('v2', 'Quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: frostslate */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 36 - Frost Slate : cool light page, cobalt quarter-circles, frosted glass panels with a cobalt edge */
var CO = '#1747E0', CO2 = '#5B8CFF', INK = '#0F1B3D', BODY = '#4B5878', ICE = '#E8EEF9';
var P0 = {bg:'#EEF2F8', text:INK, title:INK, muted:BODY, accent:CO, accent2:CO2, card:'#FFFFFF', line:'#CAD5EA', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[CO, CO2, '#0B2A8F', '#9DB8FF', '#22B8CF', '#7C8DB5'],
  header:{kind:'left', top:64, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.website || C.cover.company || ''; }, kc:BODY, kGap:26, kSize:11.5},
  card:{kind:'glass', r:10, fill:'#FFFFFF', op:0.7, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.06, dark:false, tc:INK, mc:BODY,
        hl:{kind:'solid', fill:CO, dark:true, tc:'#FFFFFF', mc:'#DCE6FF'}},
  icon:{kind:'square', bg:CO, fg:'#FFFFFF', r:8}, num:{kind:'big', fg:CO},
  conc:{kind:'bar', barC:CO, label:'Takeaway', lc:CO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:10, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#B9CCFF'], [100, '#5B8CFF']]}}
};
function quarter(C, corner, r, color, op){
  var x = corner.indexOf('r') >= 0 ? W - r : -r, y = corner.indexOf('b') >= 0 ? H - r : -r;
  C.ell(x, y, r * 2, r * 2, {fill:color, op:op});
}
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 10;
  if (o.co) { C.rect(x, y, w, h, {grad:{ang:140, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.22], [50, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#DCE6FF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.55]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.06});
  if (o.edge !== false) C.rect(x + r, y, Math.min(64, w * 0.3), 3, {fill:CO});
  return {tc:INK, mc:BODY, ac:CO};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  if (f.company) C.text(M, y, 420, 18, f.company, {font:'body', size:11.5, wt:700, color:INK, lh:1, va:'m', caps:true, ls:0.12});
  C.rect(W - M - 58, y - 4, 58, 26, {fill:CO, r:4});
  C.text(W - M - 58, y - 4, 58, 26, pad2(C.i + 1), {font:'body', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#F6F8FC'], [100, '#E3EAF6']]}});
  var k = (C.i || 0) % 4;
  if (kind === 'cover' || kind === 'closing') return;
  quarter(C, ['br', 'bl', 'tr', 'br'][k], 170, CO, 0.9);
  quarter(C, ['br', 'bl', 'tr', 'br'][k], 250, CO2, 0.18);
  D.glow(C, [W * 0.1, W * 0.9, W * 0.2, W * 0.5][k], H * 0.1, 420, 280, '#C9D8FF', 0.6);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* glass panels with a cobalt square icon on the top-left */
function iconPanels(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 24, ic = 40;
  var side = rows > 1 || ch < 220;
  var tw = side ? cw - pad * 2 - ic - 16 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - ic - 18, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 18) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = glass(C, x, y, cw, ch2, {co:hot});
    C.rect(x + pad, y + pad, ic, ic, {fill:hot ? '#FFFFFF' : CO, r:9});
    C.icon(x + pad + 10, y + pad + 10, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? CO : '#FFFFFF');
    if (side) K.hd(C, x + pad + ic + 16, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
    else K.hd(C, x + pad, y + pad + ic + 18, tw, ch2 - pad * 2 - ic - 18, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* big cobalt numerals with text, hairlines between, no cards */
function numerals(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 720 ? 2 : 1), per = Math.ceil(n / cols), gx = 50, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), nw = Math.min(96, rh * 0.8), tw = cw - nw - 16;
  var F = K.fitHD(C, it, tw, 34, rh - 26, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 22, hMin:13, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(12, (rh - bh) / 2);
    C.text(x, y, nw, rh, pad2(k + 1), {font:'head', size:Math.min(54, rh * 0.5), wt:800, color:CO, va:'m', lh:1, caps:false, ls:-0.03});
    K.hd(C, x + nw + 16, y + oy, tw, rh - oy - 4, t, F, {tc:INK, mc:BODY});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:INK, op:0.14});
  });
}
/* a cobalt index panel on the left, the explanations on a glass sheet on the right */
function indexSheet(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760 || !it.some(function(t){ return t.h; })) return iconPanels(C, Z, o);
  var lw = Math.round(Z.w * 0.32), rh = Z.h / n, pad = 26, tw = Z.w - lw - pad * 2;
  glass(C, Z.x + lw - 10, Z.y, Z.w - lw + 10, Z.h, {edge:false});
  glass(C, Z.x, Z.y, lw, Z.h, {co:true});
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), lw - pad * 2 - 34, rh - 14, {font:'head', max:19, min:12, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw - 10, rh - 16, {font:'body', max:n <= 3 ? 17 : 15, min:10.5, lh:1.38});
  it.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.text(Z.x + pad, y, 30, rh, String(k + 1), {font:'head', size:15, wt:800, color:'#9DB8FF', va:'m', lh:1, caps:false});
    C.text(Z.x + pad + 34, y, lw - pad * 2 - 34, rh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + lw + pad, y, tw - 10, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
    if (k) { C.rect(Z.x + pad, y - 0.5, lw - pad * 2, 1, {fill:'#FFFFFF', op:0.25}); C.rect(Z.x + lw + pad, y - 0.5, tw - 10, 1, {fill:INK, op:0.12}); }
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: big cobalt numbers beside their label, in rows */
function numberRows(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n > 2 && Z.w > 760 ? 2 : 1, per = Math.ceil(n / cols), gx = 30, g = 16, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min((Z.h - g * (per - 1)) / per, 190), vals = st.map(sv), labs = st.map(sl), vw = cw * 0.4, pad = 26;
  var vs = C.groupFit(vals, vw - pad, rh * 0.6, {font:'head', max:70, min:22, maxLines:1});
  var ls = C.groupFit(labs, cw - vw - pad * 2, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - vw - pad * 2, rh - 80, {font:'body', max:14.5, min:10.5, lh:1.38});
  var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  st.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g);
    glass(C, x, y, cw, rh, {});
    C.text(x + pad, y, vw - pad, rh, vals[k], {font:'head', size:vs, wt:800, color:CO, va:'m', lh:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var lh = C.measureText(labs[k], cw - vw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.x ? C.measureText(t.x, cw - vw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h : 0, by = y + Math.max(14, (rh - lh - (dh ? dh + 8 : 0)) / 2);
    C.text(x + vw + pad, by, cw - vw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + vw + pad, by + lh + 8, cw - vw - pad * 2, dh + 4, t.x, {font:'body', size:ds, color:BODY, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:CO, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y, light){ var f = C.cover; if (!f.company) return; C.rect(x, y, 22, 22, {fill:light ? '#FFFFFF' : CO, r:5}); C.ell(x + 11, y + 11, 22, 22, {fill:light ? '#9DB8FF' : CO2, op:0.9}); C.text(x + 44, y, 420, 22, f.company, {font:'head', size:15, wt:800, color:light ? '#FFFFFF' : INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) {
    C.rect(W - 420, 0, 420, H, {grad:{ang:150, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}});
    D.imgBox(C, 640, 110, 500, 500, {r:10});
    brand(C, 72, 52);
  } else {
    C.rect(0, 0, W, H, {grad:{ang:140, stops:[[0, '#2F63FF'], [100, '#0B2596']]}});
    C.ell(W - 420, -220, 700, 700, {fill:'#FFFFFF', op:0.08}); C.ell(W - 260, 300, 560, 560, {fill:'#FFFFFF', op:0.07}); C.ell(-160, H - 240, 420, 420, {fill:'#FFFFFF', op:0.06});
    brand(C, 72, 52, true);
  }
  var lt = !photo, tw = photo ? 520 : 800;
  if (f.website) C.text(72, 190, tw, 20, f.website, {font:'body', size:13, wt:600, color:lt ? '#DCE6FF' : BODY, lh:1, ed:{f:'tf-website'}});
  C.text(72, 220, tw, 220, C.s.title, {font:'head', fit:{max:photo ? 62 : 88, min:32}, wt:800, color:lt ? '#FFFFFF' : INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:lt ? '#DCE6FF' : BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 44); C.rect(72, 566, pw, 44, {fill:lt ? '#FFFFFF' : CO, r:6}); C.text(72 + 20, 566, pw - 40, 44, who, {font:'body', size:14, wt:700, color:lt ? CO : '#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 52);
  C.rect(W - 420, 0, 420, H, {grad:{ang:150, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}});
  if (photo) D.imgBox(C, 640, 110, 500, 500, {r:10});
  else { C.ell(W - 520, 110, 500, 500, {fill:'#FFFFFF', op:0.1}); C.ell(W - 440, 190, 340, 340, {fill:'#FFFFFF', op:0.12}); C.ell(W - 360, 270, 180, 180, {fill:'#FFFFFF'}); C.icon(W - 310, 320, 80, 'mdi:hand-wave-outline', CO); }
  C.text(72, 140, 540, 190, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 346, 520, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 440 + k * 60;
    C.rect(72, y, 44, 44, {fill:CO, r:9}); C.icon(72 + 12, y + 12, 20, c.icon, '#FFFFFF');
    C.text(72 + 60, y, 460, 44, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 470, H, {grad:{ang:150, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}});
  C.ell(-140, H - 300, 460, 460, {fill:'#FFFFFF', op:0.08});
  var te = C.text(72, 150, 340, 240, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 150 + te._h + 18, 330, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:'#DCE6FF', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  numerals(C, {x:540, y:80, w:W - 540 - 72, h:H - 170}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CO, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 620 : W - 144 - 80;
  if (photo) D.imgBox(C, 760, 100, 448, 520, {r:10});
  C.rect(x, 130, 64, 64, {fill:CO, r:10}); C.text(x, 142, 64, 64, '”', {font:'head', size:74, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x, 226, w, 320, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:19}, wt:700, color:INK, lh:1.22, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 226 + qe._h + 28, 50, 3, {fill:CO}); C.text(x, 226 + qe._h + 44, w, 24, bt, {font:'body', size:15, wt:700, color:BODY, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'frostslate', name:'Frost Slate', tag:'Light · cool glass',
  desc:'A cool light page with cobalt quarter-circles and frosted glass panels edged in cobalt: crisp and calm for services, IT and consulting decks.',
  fonts:{head:'Manrope', body:'Inter', headW:800, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Manrope:wght@500;600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Cobalt cover', {}), V('v2', 'Photo on cobalt band', {img:'photo'})],
    closing:[V('v1', 'Cobalt band + contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Cobalt panel + numerals', {fn:agendaA}), V('n2', 'Icon panels', {body:iconPanels}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Icon panels', {body:iconPanels, hl:true}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals, cols:2}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + icon panels', {body:iconPanels, img:'left', cols:1})],
    p3:[V('v1', 'Icon panels', {body:iconPanels}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals, cols:1}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + icon panels', {body:iconPanels, img:'left', cols:1})],
    p4:[V('v1', 'Index sheet', {body:indexSheet}), V('n2', 'Icon panels', {body:iconPanels}), V('n3', 'Numerals', {body:numerals}), V('n4', 'Icon panels in a row', {body:iconPanels, cols:4}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + icon panels', {body:iconPanels, img:'left', cols:2})],
    p56:[V('v1', 'Icon panels', {body:iconPanels}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + numerals', {body:numerals, img:'left', cols:1})],
    p78:[V('v1', 'Icon panels', {body:iconPanels}), V('n2', 'Numerals', {body:numerals}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1})],
    stats:[V('v1', 'Number rows', {body:numberRows}), V('n2', 'Progress rings', {body:'statRings', track:'#D5DEF2'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Number rows + image right', {body:numberRows, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Milestone cards', {body:'tlCards'}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1})],
    quote:[V('v1', 'Bold quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: embernoir */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 37 - Ember Noir : warm near-black page, ember glow, smoked glass bands with an amber rim */
var AM = '#FFB347', CR = '#FF6B4A', INK = '#FFF6EC', MUT = '#C9B8A8', BG0 = '#140E0C';
var P0 = {bg:BG0, text:INK, title:INK, muted:MUT, accent:AM, accent2:CR, card:'#241A16', line:'#4A372E', onAccent:'#1A0F0A'};
var S = {
  margin:76, series:[AM, CR, '#FFD9A0', '#E0527A', '#F2E3D0', '#A8623C'],
  header:{kind:'left', top:62, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return pad2(C.i + 1) + '  /  ' + (C.cover.company || ''); }, kc:AM, kGap:26, kSize:11.5},
  card:{kind:'glass', r:18, fill:'#FFFFFF', op:0.07, line:'#FFFFFF', lop:0.22, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:AM, dark:false, tc:'#1A0F0A', mc:'#4A2E14'}},
  icon:{kind:'circle', bg:AM, fg:'#1A0F0A'}, num:{kind:'big', fg:AM},
  conc:{kind:'bar', barC:AM, label:'In short', lc:AM, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:18, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#5A3320'], [100, '#2A1810']]}}
};
function smoke(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 18;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:120, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#FFFFFF', 0.35], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#1A0F0A', mc:'#4A2414', ac:'#1A0F0A'}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.13], [100, '#FFFFFF', 0.03]]}, line:'#FFD9A0', lop:0.28, lw:1, r:r});
  C.rect(x + r, y, w - r * 2, 1, {grad:{ang:0, stops:[[0, '#FFFFFF', 0], [50, '#FFFFFF', 0.7], [100, '#FFFFFF', 0]]}});
  return {tc:INK, mc:MUT, ac:AM};
}
function foot(C){
  var M = 76, y = H - 36, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 1, {fill:'#FFFFFF', op:0.12});
  if (f.website || f.company) C.text(M, y, 500, 16, f.website || f.company, {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.06});
  C.text(W - M - 120, y, 120, 16, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'body', size:11, wt:700, color:AM, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:155, stops:[[0, '#1E1410'], [100, '#0C0807']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.92, W * 0.05, W * 0.6][k], [H * 0.95, H * 0.9, -H * 0.05][k], 760, 520, CR, 0.34);
  D.glow(C, [W * 0.75, W * 0.25, W * 0.85][k], [H * 1.02, H * 1.05, H * 0.02][k], 520, 360, AM, 0.3);
  if (kind === 'cover' || kind === 'closing') return;
  var ex = [W - 150, -90, W - 210][k], ey = [H - 230, H - 210, -110][k];
  C.ell(ex, ey, 300, 300, {grad:{ang:135, stops:[[0, '#FFC978', 0.5], [100, '#FF6B4A', 0.05]]}, line:'#FFD9A0', lop:0.35, lw:1});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* full-width smoked glass bands, an amber numeral disc on the left edge */
function bands(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var g = 14, rh = Math.min((Z.h - g * (n - 1)) / n, 150), dd = Math.min(60, rh - 22), pad = 24, tw = Z.w - pad * 2 - dd - 22;
  var F = K.fitHD(C, it, tw, 30, rh - 24, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, dd) + 34); var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), hot = o.hl && k === 0, cc = smoke(C, Z.x, y, Z.w, rh, {hot:hot, r:rh / 2 > 40 ? 40 : rh / 2});
    C.ell(Z.x + pad - 6, y + (rh - dd) / 2, dd, dd, hot ? {fill:'#1A0F0A'} : {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}});
    C.text(Z.x + pad - 6, y + (rh - dd) / 2, dd, dd, pad2(k + 1), {font:'head', size:dd * 0.36, wt:700, color:hot ? AM : '#1A0F0A', align:'c', va:'m', lh:1, caps:false});
    var bh = blockH(C, t, tw, F);
    K.hd(C, Z.x + pad + dd + 16, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* staircase: glass cards that step down from left to right */
function stairs(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return tiles(C, Z, o);
  var g = 16, cw = (Z.w - g * (n - 1)) / n, step = Math.min(34, Z.h * 0.08), ch = Z.h - step * (n - 1), pad = 24, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - 66, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + k * step, hot = o.hl && k === n - 1, cc = smoke(C, x, y, cw, ch, {hot:hot});
    C.text(x + pad, y + pad - 4, tw, 50, pad2(k + 1), {font:'head', size:42, wt:300, color:cc.ac, lh:1, caps:false, op:hot ? 1 : 0.95});
    C.rect(x + pad, y + pad + 52, 34, 2, {fill:cc.ac, op:0.8});
    K.hd(C, x + pad, y + pad + 70, tw, ch - pad * 2 - 70, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* glass tiles with an outlined amber icon ring */
function tiles(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22, ic = 44;
  var side = rows > 1 || ch < 230, tw = side ? cw - pad * 2 - ic - 16 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - ic - 18, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 18) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), cc = smoke(C, x, y, cw, ch2, {});
    C.ell(x + pad, y + pad, ic, ic, {fill:AM, op:0.14, line:AM, lop:0.9, lw:1.25});
    C.icon(x + pad + 12, y + pad + 12, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), AM);
    if (side) K.hd(C, x + pad + ic + 16, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
    else K.hd(C, x + pad, y + pad + ic + 18, tw, ch2 - pad * 2 - ic - 18, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: tall glass pillars of rising height, the number on top */
function pillars(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 18, cw = (Z.w - g * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), pad = 20, tw = cw - pad * 2;
  var vs = C.groupFit(vals, tw, 70, {font:'head', max:58, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:17, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, 80, {font:'body', max:13.5, min:10.5, lh:1.36});
  var minH = Math.min(Z.h, Math.max(Z.h * 0.62, 210));
  st.forEach(function(t, k){
    var ph = minH + (Z.h - minH) * (n > 1 ? k / (n - 1) : 1), x = Z.x + k * (cw + g), y = Z.y + Z.h - ph, hot = k === n - 1, cc = smoke(C, x, y, cw, ph, {hot:hot});
    C.text(x + pad, y + pad, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:300, color:cc.ac, lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + vs * 1.1 + 10, 30, 2, {fill:cc.ac, op:0.8});
    var le = C.text(x + pad, y + pad + vs * 1.1 + 24, tw, 46, labs[k], {font:'head', size:ls, wt:600, color:cc.tc, lh:1.16, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, y + pad + vs * 1.1 + 30 + le._h, tw, ph - pad * 2 - vs * 1.1 - 30 - le._h, t.x, {font:'body', size:ds, color:cc.mc, lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: discs along a glowing arc */
function arcPath(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return bands(C, Z, o);
  var cw = Z.w / n, dd = 46, top = Z.y + 20, drop = Math.min(110, Z.h * 0.3), tw = cw - 22;
  function yy(k){ var p = n > 1 ? k / (n - 1) : 0; return top + drop * (1 - Math.sin(p * Math.PI)); }
  var F = K.fitHD(C, it, tw, 44, Z.h - drop - dd - 40, {hMax:18, dMax:14, hMin:12, dMin:10.5});
  for (var k = 0; k < n - 1; k++) { var x1 = Z.x + cw * (k + 0.5), x2 = Z.x + cw * (k + 1.5); C.line(x1, yy(k) + dd / 2, x2, yy(k + 1) + dd / 2, {color:AM, w:1.5, op:0.55}); }
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), y = yy(k);
    C.ell(cx - dd / 2 - 7, y - 7, dd + 14, dd + 14, {fill:AM, op:0.16});
    C.ell(cx - dd / 2, y, dd, dd, {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}});
    C.text(cx - dd / 2, y, dd, dd, String(k + 1), {font:'head', size:17, wt:700, color:'#1A0F0A', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, cx - tw / 2, y + dd + 20, tw, Z.y + Z.h - y - dd - 20, t, F, {tc:INK, mc:MUT, align:'c'});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  smoke(C, Z.x, Z.y, Z.w, Z.h, {r:26}); var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  C.rect(Z.x + pad, y, 40, 3, {fill:AM}); y += 24;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:600, color:AM, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; C.ell(x, y, 22, 22, {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}}); C.text(x + 32, y, 420, 22, f.company, {font:'head', size:15, wt:600, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function ember(C, cx, cy, d){
  C.ell(cx - d * 0.7, cy - d * 0.7, d * 1.4, d * 1.4, {fill:CR, op:0.1});
  C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:135, stops:[[0, '#FFD08A'], [55, '#FF7A4D'], [100, '#8A2A1A']]}});
  C.ell(cx - d * 0.36, cy - d * 0.4, d * 0.42, d * 0.3, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.55], [100, '#FFFFFF', 0]]}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 76, 54);
  if (photo) { D.imgBox(C, 700, 90, 504, 540, {r:26}); smoke(C, 640, 470, 250, 110, {r:20}); if (f.website) C.text(662, 470, 206, 110, f.website, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  else { ember(C, 980, 330, 380); smoke(C, 760, 420, 300, 190, {r:26}); smoke(C, 1000, 130, 190, 150, {r:22}); if (f.website) C.text(786, 420, 248, 190, f.website, {font:'body', size:15, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  var tw = photo ? 540 : 640;
  C.rect(76, 196, 46, 3, {fill:AM});
  C.text(76, 220, tw, 230, C.s.title, {font:'head', fit:{max:photo ? 62 : 76, min:32}, wt:300, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.02});
  if (C.s.subtitle) C.text(76, 466, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) C.text(76, 590, tw, 22, who, {font:'body', size:14, wt:600, color:AM, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 76, 54);
  if (photo) D.imgBox(C, 700, 90, 504, 540, {r:26}); else ember(C, 1010, 250, 300);
  C.text(76, 130, 580, 190, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:300, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(76, 336, 540, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var pw = photo ? 560 : W - 152, cols = photo ? 1 : n, g = 14, cw = (pw - g * (cols - 1)) / cols, rh = photo ? 58 : 84;
  cs.forEach(function(c, k){
    var x = 76 + (photo ? 0 : k * (cw + g)), y = (photo ? 426 : 500) + (photo ? k * (rh + 10) : 0);
    smoke(C, x, y, cw, rh, {r:rh / 2});
    C.ell(x + 12, y + (rh - 38) / 2, 38, 38, {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}}); C.icon(x + 12 + 10, y + (rh - 38) / 2 + 10, 18, c.icon, '#1A0F0A');
    C.text(x + 62, y, cw - 80, rh, c.val, {font:'body', fit:{max:17, min:11.5}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  ember(C, 250, 520, 300);
  C.rect(76, 150, 46, 3, {fill:AM});
  var te = C.text(76, 172, 400, 220, C.s.title || 'Agenda', {font:'head', fit:{max:68, min:34}, wt:300, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(76, 172 + te._h + 16, 380, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  bands(C, {x:540, y:76, w:W - 540 - 76, h:H - 160}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:AM, tc:INK}); return; }
  var photo = o.img === 'photo', x = 76, w = photo ? 600 : W - 152;
  if (photo) D.imgBox(C, 760, 100, 444, 520, {r:26});
  smoke(C, x, 110, w, 500, {r:28});
  C.text(x + 44, 120, 100, 100, '“', {font:'head', size:120, wt:600, color:AM, lh:1, caps:false, ls:0});
  var qe = C.text(x + 48, 224, w - 96, 280, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:300, color:INK, lh:1.26, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 48, 224 + qe._h + 26, w - 96, 24, bt, {font:'body', size:15, wt:700, color:AM, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'embernoir', name:'Ember Noir', tag:'Dark · smoked glass',
  desc:'A warm near-black page lit by an ember glow, with smoked glass bands and amber rims: dramatic and premium for launches, brand and investor decks.',
  fonts:{head:'Outfit', body:'Inter', headW:300, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Outfit:wght@300;400;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Ember + glass', {}), V('v2', 'Photo + glass tag', {img:'photo'})],
    closing:[V('v1', 'Ember + glass contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Ember + glass bands', {fn:agendaA}), V('n2', 'Glass tiles', {body:tiles}), V('v2', 'Bands + image right', {body:bands, img:'right'})],
    text:[V('v1', 'Smoked statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass bands', {body:bands, hl:true}), V('n2', 'Staircase', {body:stairs}), V('n3', 'Glass tiles', {body:tiles}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p3:[V('v1', 'Staircase', {body:stairs, hl:true}), V('n2', 'Glass bands', {body:bands}), V('n3', 'Glass tiles', {body:tiles}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p4:[V('v1', 'Glass tiles', {body:tiles}), V('n2', 'Staircase', {body:stairs, hl:true}), V('n3', 'Glass bands', {body:bands}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p56:[V('v1', 'Glass tiles', {body:tiles}), V('n2', 'Glass bands', {body:bands}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p78:[V('v1', 'Glass tiles', {body:tiles}), V('v2', 'Tiles + image right', {body:tiles, img:'right', cols:2})],
    stats:[V('v1', 'Glass pillars', {body:pillars}), V('n2', 'Progress rings', {body:'statRings', track:'#3A2A22'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Pillars + image right', {body:pillars, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Ember arc', {body:arcPath}), V('n2', 'Staircase', {body:stairs}), V('n3', 'Glass bands', {body:bands}), V('v2', 'Bands + image right', {body:bands, img:'right'})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: linenglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 38 - Linen Glass : warm sand page, soft arches, milk-glass capsules, serif headings */
var CA = '#B5743B', RU = '#8C4A2F', INK = '#3B2F25', MUT = '#7A6A5A';
var P0 = {bg:'#F3EBDD', text:INK, title:INK, muted:MUT, accent:CA, accent2:RU, card:'#FBF6EC', line:'#DCCDB6', onAccent:'#FFFFFF'};
var S = {
  margin:80, series:[CA, RU, '#D9A66B', '#5E4B3A', '#E3C9A0', '#A0522D'],
  header:{kind:'center', top:60, tMax:48, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:CA, kGap:24, kSize:11.5},
  card:{kind:'glass', r:22, fill:'#FFFFFF', op:0.6, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:RU, dark:true, tc:'#FFFFFF', mc:'#F3DCCB'}},
  icon:{kind:'circle', bg:CA, fg:'#FFFFFF'}, num:{kind:'big', fg:CA},
  conc:{kind:'bar', barC:CA, label:'In essence', lc:CA, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:26, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#E6CFA9'], [100, '#C89B6A']]}}
};
function milk(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 22;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#A65E3C'], [100, '#7A3D25']]}, r:r, shadow:0.1}); return {tc:'#FFFFFF', mc:'#F3DCCB', ac:'#F0C89A'}; }
  C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.9], [100, '#FFFFFF', 0.42]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.05});
  return {tc:INK, mc:MUT, ac:CA};
}
function arch(C, x, y, w, h, color, op){ C.ell(x, y, w, w, {fill:color, op:op}); C.rect(x, y + w / 2, w, h - w / 2, {fill:color, op:op}); }
function foot(C){
  var y = H - 36;
  C.rect(W / 2 - 60, y - 14, 120, 1, {fill:INK, op:0.25});
  C.text(W / 2 - 200, y, 400, 16, (C.cover.website || C.cover.company || '') + '   ·   ' + pad2(C.i + 1), {font:'body', size:11, wt:500, color:MUT, align:'c', va:'m', lh:1, ls:0.08});
}
function bg(C, kind){
  C.bg({grad:{ang:170, stops:[[0, '#F8F1E4'], [100, '#EADDC8']]}});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  arch(C, [W - 200, -70, W - 150][k], [H - 250, H - 230, H - 330][k], [270, 230, 210][k], 400, '#D9B88A', 0.38);
  C.ell([-110, W - 170, -80][k], [-110, -120, 60][k], 270, 270, {fill:'#C8875A', op:0.16});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* tall milk-glass capsules, a caramel icon disc on top, text centred */
function capsules(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return diamonds(C, Z, o);
  var g = 20, cw = Math.min((Z.w - g * (n - 1)) / n, 330), tot = cw * n + g * (n - 1), x0 = Z.x + (Z.w - tot) / 2, r = Math.min(cw / 2, 110), pad = 26, tw = cw - pad * 2, ic = 56;
  var F = K.fitHD(C, it, tw, 54, Z.h - ic - 90 - r * 0.35, {hMax:23, dMax:15.5, hMin:13, dMin:10.5});
  it.forEach(function(t, k){
    var x = x0 + k * (cw + g), hot = o.hl && k === 1 && n === 3, cc = milk(C, x, Z.y, cw, Z.h, {r:r, hot:hot});
    C.ell(x + cw / 2 - ic / 2, Z.y + 30, ic, ic, {fill:hot ? '#FFFFFF' : CA});
    C.icon(x + cw / 2 - 12, Z.y + 30 + ic / 2 - 12, 24, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? RU : '#FFFFFF');
    K.hd(C, x + pad, Z.y + 30 + ic + 22, tw, Z.h - ic - 70 - r * 0.3, t, F, {tc:cc.tc, mc:cc.mc, align:'c'});
  });
}
/* open rows with a caramel diamond marker and a dotted leader, no boxes */
function diamonds(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 140), tw = cw - 44;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:22, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(10, (rh - bh) / 2);
    K.poly(C, [[x + 9, y + oy + 2], [x + 18, y + oy + 11], [x + 9, y + oy + 20], [x, y + oy + 11]], {fill:k % 2 ? RU : CA});
    if (r < per - 1) C.rect(x + 8.5, y + oy + 26, 1, rh - 30, {fill:CA, op:0.35});
    K.hd(C, x + 44, y + oy, tw, rh - oy - 4, t, F, {tc:INK, mc:MUT});
  });
}
/* two-column milk-glass panels, a serif numeral in the corner */
function panels(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 2 ? n : (n <= 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26, nw = 54, tw = cw - pad * 2 - nw;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2, {hMax:21, dMax:15, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, Math.max(bmx, 60) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = milk(C, x, y, cw, ch2, {hot:hot});
    C.text(x + pad, y + pad - 6, nw, 50, String(k + 1), {font:'head', size:46, wt:600, color:cc.ac, lh:1, caps:false, italic:true});
    K.hd(C, x + pad + nw, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: double-ring medallions */
function medallions(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, dd = Math.min(cw - 30, Z.h * 0.56, 230), vals = st.map(sv), labs = st.map(sl), tw = cw - 30;
  var vs = C.groupFit(vals, dd - 44, dd * 0.4, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 52, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - dd - 80, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), y = Z.y + 4;
    C.ell(cx - dd / 2, y, dd, dd, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.45]]}, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05});
    C.ell(cx - dd / 2 + 12, y + 12, dd - 24, dd - 24, {line:k % 2 ? RU : CA, lw:1.5, lop:0.9});
    C.text(cx - dd / 2 + 22, y, dd - 44, dd, vals[k], {font:'head', size:vs, wt:600, color:k % 2 ? RU : CA, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, y + dd + 18, tw, 50, labs[k], {font:'head', size:ls, wt:600, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, y + dd + 24 + le._h, tw, Z.y + Z.h - (y + dd + 24 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a centre thread, entries alternate above and below */
function thread(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return diamonds(C, Z, o);
  var cw = Z.w / n, my = Z.y + Z.h / 2, tw = cw - 20, half = Z.h / 2 - 34;
  var F = K.fitHD(C, it, tw, 44, half, {hMax:19, dMax:14, hMin:12, dMin:10.5});
  C.rect(Z.x, my - 0.75, Z.w, 1.5, {fill:CA, op:0.6});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), up = k % 2 === 0, bh = blockH(C, t, tw, F);
    C.ell(cx - 13, my - 13, 26, 26, {fill:'#FFFFFF', line:CA, lw:1.5}); C.ell(cx - 5, my - 5, 10, 10, {fill:up ? CA : RU});
    C.rect(cx - 0.5, up ? my - 30 : my + 13, 1, 17, {fill:CA, op:0.6});
    K.hd(C, cx - tw / 2, up ? my - 38 - bh : my + 38, tw, half, t, F, {tc:INK, mc:MUT, align:'c'});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  milk(C, Z.x, Z.y, Z.w, Z.h, {r:30}); var pad = 50, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:34, min:18}, wt:600, color:RU, italic:true, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) { arch(C, 730, 70, 440, 580, '#D9B88A', 0.5); C.ell(760, 100, 380, 380, {fill:'#FFFFFF', op:0.5}); D.imgBox(C, 770, 110, 360, 500, {r:180}); }
  else { arch(C, 800, 110, 360, 610, '#C8875A', 0.85); arch(C, 700, 300, 250, 420, '#E6CFA9', 0.9); C.ell(1020, 80, 150, 150, {grad:{ang:135, stops:[[0, '#F6D9A8'], [100, '#D99A5B']]}}); milk(C, 860, 430, 270, 150, {r:75}); if (f.website) C.text(884, 430, 222, 150, f.website, {font:'body', size:14, wt:600, color:INK, align:'c', va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  if (f.company) C.text(80, 62, 500, 20, f.company, {font:'body', size:13, wt:600, color:CA, lh:1, caps:true, ls:0.18, ed:{f:'tf-company'}, maxLines:1});
  C.text(80, 190, 600, 250, C.s.title, {font:'head', fit:{max:84, min:34}, wt:600, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1, ls:-0.01});
  if (C.s.subtitle) C.text(80, 458, 540, 80, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { C.rect(80, 588, 44, 1.5, {fill:CA}); C.text(80, 604, 600, 22, who, {font:'body', size:14, wt:600, color:INK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  arch(C, W / 2 - 330, 90, 660, 700, '#E6CFA9', 0.55);
  if (photo) D.imgBox(C, W / 2 - 80, 60, 160, 160, {r:80}); else C.ell(W / 2 - 46, 96, 92, 92, {grad:{ang:135, stops:[[0, '#F6D9A8'], [100, '#D99A5B']]}});
  C.text(W / 2 - 400, 220, 800, 130, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:36}, wt:600, color:INK, align:'c', ed:{f:'title'}, maxLines:1, va:'m', lh:1, italic:true});
  if (C.s.subtitle) C.text(W / 2 - 300, 356, 600, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var cw = Math.min(330, (W - 200 - 16 * (n - 1)) / n), tot = cw * n + 16 * (n - 1), x0 = (W - tot) / 2;
  cs.forEach(function(c, k){
    var x = x0 + k * (cw + 16), y = 470;
    milk(C, x, y, cw, 70, {r:35}); C.ell(x + 14, y + 14, 42, 42, {fill:CA}); C.icon(x + 14 + 11, y + 14 + 11, 20, c.icon, '#FFFFFF');
    C.text(x + 68, y, cw - 86, 70, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  arch(C, 80, 110, 360, 620, '#C8875A', 0.85);
  var te = C.text(110, 300, 300, 200, C.s.title || 'Agenda', {font:'head', fit:{max:66, min:32}, wt:600, color:'#FFFFFF', align:'c', italic:true, ed:{f:'title'}, maxLines:2, va:'t', lh:1});
  if (C.s.subtitle) C.text(120, 300 + te._h + 14, 280, 90, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FBEBDD', align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  diamonds(C, {x:520, y:90, w:W - 520 - 90, h:H - 180}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CA, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 80 : 190, w = photo ? 620 : W - 380, al = photo ? 'l' : 'c';
  if (photo) D.imgBox(C, 800, 110, 380, 500, {r:190});
  C.text(x, 110, w, 90, '“', {font:'head', size:140, wt:600, color:CA, align:al, lh:1, caps:false, ls:0});
  var qe = C.text(x, 210, w, 300, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:600, color:INK, italic:true, align:al, lh:1.2, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, 210 + qe._h + 30, w, 24, bt, {font:'body', size:15, wt:600, color:RU, align:al, lh:1.1, caps:true, ls:0.12, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'linenglass', name:'Linen Glass', tag:'Warm sand · milk glass',
  desc:'A warm sand page with soft arches, milk-glass capsules and elegant serif headings: calm and refined for wellness, interiors, hospitality and brand stories.',
  fonts:{head:'Cormorant Garamond', body:'Jost', headW:600, bodyW:400, headLh:1.06, gf:'Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600|Jost:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Arches + glass', {}), V('v2', 'Arched photo', {img:'photo'})],
    closing:[V('v1', 'Arch + glass contacts', {}), V('v2', 'Round photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Arch + diamond list', {fn:agendaA}), V('n2', 'Numbered panels', {body:panels}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1})],
    text:[V('v1', 'Milk-glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Numbered panels', {body:panels, hl:true}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + panels', {body:panels, img:'left', cols:1})],
    p3:[V('v1', 'Capsules', {body:capsules, hl:true}), V('n2', 'Diamond list', {body:diamonds}), V('n3', 'Numbered panels', {body:panels, cols:3}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + panels', {body:panels, img:'left', cols:1})],
    p4:[V('v1', 'Numbered panels', {body:panels}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Diamond list', {body:diamonds, cols:2}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + panels', {body:panels, img:'left', cols:1})],
    p56:[V('v1', 'Numbered panels', {body:panels}), V('n2', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + diamond list', {body:diamonds, img:'left', cols:1})],
    p78:[V('v1', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1})],
    stats:[V('v1', 'Medallions', {body:medallions}), V('n2', 'Progress rings', {body:'statRings', track:'#E3D4BC'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Medallions + image right', {body:medallions, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Thread', {body:thread}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1})],
    quote:[V('v1', 'Serif quote', {}), V('v2', 'Quote + arched photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: polarnight */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 39 - Polar Night : deep navy sky, ice-glass shards with a cut corner, cyan light */
var CY = '#5FE1FF', IC = '#BFEFFF', INK = '#F2FAFF', MUT = '#A9BDD6';
var P0 = {bg:'#071226', text:INK, title:INK, muted:MUT, accent:CY, accent2:'#7C9BFF', card:'#10213F', line:'#2A4066', onAccent:'#04101F'};
var S = {
  margin:74, series:[CY, '#7C9BFF', '#BFEFFF', '#3AA0FF', '#E6F7FF', '#4C6FBF'],
  header:{kind:'left', top:60, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return (C.cover.company || '') ; }, kc:CY, kGap:26, kSize:11.5},
  card:{kind:'glass', r:4, fill:'#FFFFFF', op:0.08, line:'#BFEFFF', lop:0.4, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:CY, dark:false, tc:'#04101F', mc:'#12324A'}},
  icon:{kind:'square', bg:CY, fg:'#04101F', r:4}, num:{kind:'big', fg:CY},
  conc:{kind:'bar', barC:CY, label:'Key point', lc:CY, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:4, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#1C3D6E'], [100, '#0B1B36']]}}
};
/* ice shard: a glass panel with the top-right corner cut off */
function shard(C, x, y, w, h, o){
  o = o || {}; var c = o.cut != null ? o.cut : Math.min(34, w * 0.18, h * 0.3), pts = [[x, y], [x + w - c, y], [x + w, y + c], [x + w, y + h], [x, y + h]];
  if (o.hot) { K.poly(C, pts, {fill:'#6FDCFF'}); K.poly(C, [[x, y], [x + w - c, y], [x + w, y + c], [x + w, y + h * 0.45], [x, y + h * 0.2]], {fill:'#FFFFFF', op:0.22}); return {tc:'#04101F', mc:'#12324A', ac:'#04101F'}; }
  K.poly(C, pts, {fill:'#FFFFFF', op:0.07}); K.poly(C, [[x, y], [x + w - c, y], [x + w, y + c], [x + w, y + h * 0.4], [x, y + h * 0.15]], {fill:'#FFFFFF', op:0.06}); for (var q = 0; q < 5; q++) { var a = pts[q], b = pts[(q + 1) % 5]; C.line(a[0], a[1], b[0], b[1], {color:'#BFEFFF', lw:1, op:0.42}); }
  C.rect(x, y, 3, h, {fill:CY, op:0.9});
  return {tc:INK, mc:MUT, ac:CY};
}
function foot(C){
  var M = 74, y = H - 36, f = C.cover;
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.08});
  K.poly(C, [[W - M - 54, y - 6], [W - M - 8, y - 6], [W - M, y + 2], [W - M, y + 20], [W - M - 54, y + 20]], {fill:CY});
  C.text(W - M - 54, y - 6, 54, 26, pad2(C.i + 1), {font:'head', size:12, wt:700, color:'#04101F', align:'c', va:'m', lh:1});
}
function peaks(C, y0, op){
  K.poly(C, [[0, H], [0, y0 + 90], [180, y0 + 10], [330, y0 + 100], [520, y0 - 30], [700, y0 + 110], [880, y0 + 30], [1060, y0 + 120], [1200, y0 + 40], [W, y0 + 100], [W, H]], {fill:'#16305A', op:op});
  K.poly(C, [[0, H], [0, y0 + 150], [240, y0 + 90], [430, y0 + 160], [640, y0 + 80], [830, y0 + 170], [1040, y0 + 110], [W, y0 + 170], [W, H]], {fill:'#0D2040', op:op});
}
function bg(C, kind){
  C.bg({grad:{ang:180, stops:[[0, '#050D1E'], [100, '#0B1F40']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.8, W * 0.15, W * 0.5][k], -H * 0.05, 900, 420, CY, 0.22);
  D.glow(C, [W * 0.2, W * 0.85, W * 0.9][k], H * 0.2, 600, 400, '#7C9BFF', 0.2);
  K.dots(C, [W - 200, 60, W - 320][k], [40, 50, 30][k], 6, 3, 26, 1.4, '#FFFFFF', 0.5);
  if (kind === 'cover' || kind === 'closing') return;
  peaks(C, H - 150, 0.75);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
/* shard cards with a big numeral behind the text */
function shards(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26, tw = cw - pad * 2 - 6;
  var top = rows === 1 ? 64 : 0, F = K.fitHD(C, it, tw, 52, ch - pad * 2 - top, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad * 2 + 8), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = shard(C, x, y, cw, ch2, {hot:hot});
    if (top) { C.text(x + pad, y + pad - 4, tw, 50, pad2(k + 1), {font:'head', size:40, wt:700, color:cc.ac, lh:1, caps:false, op:hot ? 0.9 : 1}); }
    else C.text(x + cw - 92, y + ch2 - 62, 80, 56, pad2(k + 1), {font:'head', size:50, wt:700, color:cc.ac, align:'r', lh:1, caps:false, op:0.16});
    K.hd(C, x + pad, y + pad + top, tw, ch2 - pad * 2 - top, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* beacon list: cyan hex beacons joined by a light beam, text to the right */
function beacons(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 130), hx = Math.min(46, rh - 18), tw = Z.w - hx - 26;
  var F = K.fitHD(C, it, tw, 34, rh - 20, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x + hx / 2 - 0.75, y0 + rh / 2, 1.5, rh * (n - 1), {fill:CY, op:0.4});
  it.forEach(function(t, k){
    var y = y0 + k * rh, cy = y + rh / 2, cx = Z.x + hx / 2, r = hx / 2;
    K.poly(C, [[cx, cy - r], [cx + r * 0.87, cy - r / 2], [cx + r * 0.87, cy + r / 2], [cx, cy + r], [cx - r * 0.87, cy + r / 2], [cx - r * 0.87, cy - r / 2]], {fill:CY}); C.ell(cx - r * 0.66, cy - r * 0.66, r * 1.32, r * 1.32, {fill:'#0B1F40'});
    C.icon(cx - hx * 0.22, cy - hx * 0.22, hx * 0.44, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), CY);
    var bh = blockH(C, t, tw, F);
    K.hd(C, Z.x + hx + 26, y + Math.max(8, (rh - bh) / 2), tw, rh - 8, t, F, {tc:INK, mc:MUT});
  });
}
/* split: one large hot shard on the left, the rest stacked on the right */
function lead(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return shards(C, Z, o);
  var g = 18, lw = Math.round(Z.w * 0.4), pad = 30, rest = it.slice(1), rn = rest.length, rw = Z.w - lw - g, rh = (Z.h - g * (rn - 1)) / rn;
  var c1 = shard(C, Z.x, Z.y, lw, Z.h, {hot:true, cut:50});
  C.icon(Z.x + pad, Z.y + pad, 40, D.iconFor(C.s, it[0].i, (it[0].h || '') + ' ' + (it[0].d || '')), c1.ac);
  var F1 = K.fitHD(C, [it[0]], lw - pad * 2, 80, Z.h - pad * 2 - 70, {hMax:28, dMax:18, hMin:15, dMin:11});
  K.hd(C, Z.x + pad, Z.y + pad + 62, lw - pad * 2, Z.h - pad * 2 - 62, it[0], F1, {tc:c1.tc, mc:c1.mc});
  var F = K.fitHD(C, rest, rw - 48, 30, rh - 24, {hMax:19, dMax:14.5, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var y = Z.y + k * (rh + g), cc = shard(C, Z.x + lw + g, y, rw, rh, {cut:22}), bh = blockH(C, t, rw - 48, F);
    K.hd(C, Z.x + lw + g + 26, y + Math.max(10, (rh - bh) / 2), rw - 48, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: summit peaks - a triangle per number, rising */
function summits(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 26, base = Z.y + Z.h * 0.6;
  var vs = C.groupFit(vals, tw, 70, {font:'head', max:56, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h * 0.4 - 70, {font:'body', max:13.5, min:10.5, lh:1.36});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), ph = (Z.h * 0.6 - vs - 26) * (0.5 + 0.5 * (n > 1 ? k / (n - 1) : 1)), pw = Math.min(cw * 0.8, 220);
    K.poly(C, [[cx - pw / 2, base], [cx, base - ph], [cx + pw / 2, base]], {fill:CY, op:0.3}); K.poly(C, [[cx, base - ph], [cx + pw / 2, base], [cx + pw * 0.08, base]], {fill:'#FFFFFF', op:0.12});
    K.poly(C, [[cx, base - ph], [cx + pw * 0.12, base - ph * 0.72], [cx, base - ph * 0.62], [cx - pw * 0.1, base - ph * 0.74]], {fill:'#FFFFFF', op:0.85});
    C.text(cx - tw / 2, base - ph - vs - 16, tw, vs + 8, vals[k], {font:'head', size:vs, wt:700, color:CY, align:'c', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + 16, tw, 46, labs[k], {font:'head', size:ls, wt:600, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + 22 + le._h, tw, Z.y + Z.h - base - 22 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
  C.rect(Z.x, base, Z.w, 1, {fill:IC, op:0.5});
}
/* timeline: ascent - steps climbing a ridge line */
function ascent(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return beacons(C, Z, o);
  var g = 14, cw = (Z.w - g * (n - 1)) / n, rise = Math.min(40, Z.h * 0.5 / n), ch = Z.h - rise * (n - 1), pad = 18, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 44, ch - pad * 2 - 40, {hMax:18, dMax:13.5, hMin:12, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + (n - 1 - k) * rise, cc = shard(C, x, y, cw, Z.y + Z.h - y, {cut:20, hot:k === n - 1});
    C.text(x + pad, y + pad - 2, tw, 26, 'STEP ' + (k + 1), {font:'head', size:11.5, wt:700, color:cc.ac, lh:1, ls:0.16});
    K.hd(C, x + pad, y + pad + 30, tw, ch - pad * 2 - 30, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  shard(C, Z.x, Z.y, Z.w, Z.h, {cut:56}); var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w - 30, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:CY, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; K.poly(C, [[x, y + 22], [x + 12, y], [x + 24, y + 22]], {fill:CY}); C.text(x + 36, y, 420, 22, f.company, {font:'head', size:15, wt:700, color:INK, va:'m', lh:1, ls:0.04, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  peaks(C, H - 250, 1);
  brand(C, 74, 54);
  if (photo) { D.imgBox(C, 720, 100, 486, 420, {r:4}); shard(C, 660, 440, 300, 110, {cut:26}); if (f.website) C.text(686, 440, 250, 110, f.website, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  else { C.ell(900, 90, 220, 220, {grad:{ang:135, stops:[[0, '#FFFFFF'], [100, '#9DF0FF']]}}); C.ell(860, 50, 300, 300, {fill:CY, op:0.12}); shard(C, 800, 330, 380, 130, {cut:30}); if (f.website) C.text(830, 330, 320, 130, f.website, {font:'body', size:15, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  var tw = photo ? 560 : 660;
  C.text(74, 170, tw, 230, C.s.title, {font:'head', fit:{max:photo ? 62 : 76, min:32}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.03, ls:-0.02});
  if (C.s.subtitle) C.text(74, 412, tw - 80, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:IC, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  /  ');
  if (who) { C.rect(74, 510, 3, 26, {fill:CY}); C.text(90, 510, tw, 26, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  peaks(C, H - 210, 1); brand(C, 74, 54);
  if (photo) D.imgBox(C, 760, 100, 446, 380, {r:4});
  var tw = photo ? 640 : W - 148;
  C.text(74, 120, tw, 170, C.s.title || 'Thank You', {font:'head', fit:{max:88, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(74, 304, Math.min(tw, 640), 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:IC, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var g = 14, cw = Math.min(340, (tw - g * (n - 1)) / n);
  cs.forEach(function(c, k){
    var x = 74 + k * (cw + g), y = 400;
    shard(C, x, y, cw, 76, {cut:18}); C.icon(x + 22, y + 27, 22, c.icon, CY);
    C.text(x + 58, y, cw - 78, 76, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(74, 120, 380, 220, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  C.rect(74, 120 + te._h + 16, 60, 3, {fill:CY});
  if (C.s.subtitle) C.text(74, 120 + te._h + 36, 360, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:IC, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  shard(C, 500, 70, W - 500 - 74, H - 190, {cut:50});
  beacons(C, {x:536, y:92, w:W - 536 - 110, h:H - 234}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CY, tc:INK}); return; }
  var photo = o.img === 'photo', x = 74, w = photo ? 640 : W - 148;
  if (photo) D.imgBox(C, 780, 100, 426, 480, {r:4});
  shard(C, x, 100, w, 420, {cut:60});
  C.text(x + 44, 110, 100, 90, '“', {font:'head', size:120, wt:700, color:CY, lh:1, caps:false, ls:0});
  var qe = C.text(x + 48, 204, w - 110, 230, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:500, color:INK, lh:1.25, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 48, 204 + qe._h + 22, w - 110, 24, bt, {font:'body', size:15, wt:700, color:CY, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'polarnight', name:'Polar Night', tag:'Dark · ice glass',
  desc:'A deep navy night sky over a mountain ridge, with cut-corner ice-glass shards and cyan light: sharp and ambitious for strategy, tech and expedition-style stories.',
  fonts:{head:'Space Grotesk', body:'IBM Plex Sans', headW:700, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Space+Grotesk:wght@500;600;700|IBM+Plex+Sans:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Moon over the ridge', {}), V('v2', 'Photo + ice tag', {img:'photo'})],
    closing:[V('v1', 'Ridge + ice contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Beacon list in ice', {fn:agendaA}), V('n2', 'Ice shards', {body:shards}), V('v2', 'Beacons + image right', {body:beacons, img:'right'})],
    text:[V('v1', 'Ice statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Ice shards', {body:shards, hl:true}), V('n2', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:1})],
    p3:[V('v1', 'Ice shards', {body:shards}), V('n2', 'Lead shard + stack', {body:lead}), V('n3', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:1})],
    p4:[V('v1', 'Lead shard + stack', {body:lead}), V('n2', 'Ice shards', {body:shards}), V('n3', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:2})],
    p56:[V('v1', 'Ice shards', {body:shards}), V('n2', 'Lead shard + stack', {body:lead}), V('n3', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:2})],
    p78:[V('v1', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'})],
    stats:[V('v1', 'Summits', {body:summits}), V('n2', 'Progress rings', {body:'statRings', track:'#1B3358'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Summits + image right', {body:summits, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Ascent', {body:ascent}), V('n2', 'Beacon list', {body:beacons}), V('n3', 'Ice shards', {body:shards}), V('v2', 'Beacons + image right', {body:beacons, img:'right'})],
    quote:[V('v1', 'Ice quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: garnetglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 40 - Garnet Glass : deep wine page, rose-gold light, rosy glass sheets and gems */
var RG = '#F2B8A0', PK = '#FF7A9C', INK = '#FFF3EE', MUT = '#D9B5B8';
var ROM = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
var P0 = {bg:'#1D0712', text:INK, title:INK, muted:MUT, accent:RG, accent2:PK, card:'#32101F', line:'#5A2A3A', onAccent:'#2A0A16'};
var S = {
  margin:78, series:[RG, PK, '#FFD9C8', '#C2476B', '#F7E3DA', '#8E2F4C'],
  header:{kind:'left', top:62, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return (C.cover.company || '') + '  —  ' + (ROM[C.i] || pad2(C.i + 1)); }, kc:RG, kGap:26, kSize:11.5},
  card:{kind:'glass', r:16, fill:'#FFFFFF', op:0.08, line:'#F2B8A0', lop:0.4, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:RG, dark:false, tc:'#2A0A16', mc:'#5A2634'}},
  icon:{kind:'circle', bg:RG, fg:'#2A0A16'}, num:{kind:'big', fg:RG},
  conc:{kind:'bar', barC:RG, label:'The point', lc:RG, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:16, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#6A2440'], [100, '#2E0D1C']]}}
};
function rosy(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 16;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#FFD9C8'], [100, '#F09A8C']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:175, stops:[[0, '#FFFFFF', 0.4], [40, '#FFFFFF', 0]]}, r:r}); return {tc:'#2A0A16', mc:'#5A2634', ac:'#7A1F3C'}; }
  C.rect(x, y, w, h, {grad:{ang:140, stops:[[0, '#FFC9D6', 0.2], [100, '#FFFFFF', 0.04]]}, line:'#F2B8A0', lop:0.42, lw:1, r:r});
  return {tc:INK, mc:MUT, ac:RG};
}
function gem(C, cx, cy, r, hot){
  K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], {fill:hot ? '#F6B9A6' : '#FFFFFF', op:hot ? 1 : 0.1});
  K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy]], {fill:'#FFFFFF', op:hot ? 0.35 : 0.08});
  K.poly(C, [[cx, cy - r], [cx - r, cy], [cx, cy]], {fill:'#FFFFFF', op:hot ? 0.15 : 0.03});
  var p = [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]];
  for (var q = 0; q < 4; q++) C.line(p[q][0], p[q][1], p[(q + 1) % 4][0], p[(q + 1) % 4][1], {color:RG, lw:1, op:0.7});
}
function foot(C){
  var M = 78, y = H - 36, f = C.cover;
  K.poly(C, [[M + 5, y + 3], [M + 10, y + 8], [M + 5, y + 13], [M, y + 8]], {fill:RG});
  C.text(M + 20, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.08});
  C.text(W - M - 120, y, 120, 16, pad2(C.i + 1), {font:'head', size:14, color:RG, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:150, stops:[[0, '#2C0A1A'], [100, '#12040B']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.95, W * 0.02, W * 0.7][k], [H * 0.05, H * 0.1, H * 1.0][k], 760, 520, PK, 0.26);
  D.glow(C, [W * 0.1, W * 0.9, W * 0.1][k], [H * 0.95, H * 0.9, H * 0.1][k], 600, 420, RG, 0.2);
  if (kind === 'cover' || kind === 'closing') return;
  gem(C, [W - 70, 60, W - 90][k], [H - 90, H - 80, 80][k], 110, false);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* zigzag: glass cards that alternate high and low */
function zigzag(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return sheet(C, Z, o);
  var g = 16, cw = (Z.w - g * (n - 1)) / n, off = Math.min(46, Z.h * 0.12), ch = Z.h - off, pad = 24, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - 58, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + (k % 2 ? off : 0), hot = o.hl && k === 0, cc = rosy(C, x, y, cw, ch, {hot:hot});
    C.ell(x + pad, y + pad, 42, 42, {fill:hot ? '#7A1F3C' : RG, op:hot ? 1 : 0.16, line:hot ? null : RG, lop:0.8, lw:1});
    C.icon(x + pad + 11, y + pad + 11, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? '#FFD9C8' : RG);
    K.hd(C, x + pad, y + pad + 60, tw, ch - pad * 2 - 60, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* one rosy glass sheet, items in ruled columns with roman numerals */
function sheet(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), pad = 30, gx = 44, cw = (Z.w - pad * 2 - gx * (cols - 1)) / cols, rh = Math.min((Z.h - pad * 2) / per, 150), nw = 50, tw = cw - nw;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 20, hMin:13, dMin:10.5, noBoost:n > 4});
  var sh = rh * per + pad * 2, y0 = Z.y + Math.max(0, (Z.h - sh) * 0.3);
  rosy(C, Z.x, y0, Z.w, sh, {r:20});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + pad + c * (cw + gx), y = y0 + pad + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(8, (rh - bh) / 2);
    C.text(x, y + oy - 2, nw - 8, 30, ROM[k], {font:'head', size:22, color:RG, lh:1, caps:false});
    K.hd(C, x + nw, y + oy, tw, rh - oy - 2, t, F, {tc:INK, mc:MUT});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:RG, op:0.28});
  });
  if (cols > 1) C.rect(Z.x + pad + cw + gx / 2, y0 + pad, 1, sh - pad * 2, {fill:RG, op:0.28});
}
/* glass tiles with a gem marker */
function gems(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 24, gm = 26, tw = cw - pad * 2 - gm * 2 - 14;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, Math.max(bmx, gm * 2) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), cc = rosy(C, x, y, cw, ch2, {});
    gem(C, x + pad + gm, y + pad + gm, gm, true);
    C.text(x + pad, y + pad, gm * 2, gm * 2, String(k + 1), {font:'head', size:17, color:'#2A0A16', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad + gm * 2 + 14, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: a big gem behind each number */
function gemStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw / 2 - 16, Z.h * 0.3, 130), vals = st.map(sv), labs = st.map(sl), tw = cw - 28;
  var vs = C.groupFit(vals, r * 1.15, r * 0.6, {font:'head', max:50, min:18, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - r * 2 - 80, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = Z.y + r + 4;
    gem(C, cx, cy, r, false);
    C.text(cx - r * 0.6, cy - r * 0.4, r * 1.2, r * 0.8, vals[k], {font:'head', size:vs, color:RG, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, cy + r + 18, tw, 50, labs[k], {font:'head', size:ls, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, cy + r + 24 + le._h, tw, Z.y + Z.h - (cy + r + 24 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: ladder of glass bars, each starting further right */
function ladder(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return sheet(C, Z, o);
  var g = 10, rh = Math.min((Z.h - g * (n - 1)) / n, 96), shift = Math.min(70, (Z.w * 0.34) / (n - 1)), bw = Z.w - shift * (n - 1), pad = 22, hw = Math.min(170, bw * 0.24), tw = bw - pad * 2 - hw - 16;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), hw, rh - 16, {font:'head', max:22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:15, min:10.5, lh:1.36});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * shift, y = y0 + k * (rh + g), hot = k === n - 1, cc = rosy(C, x, y, bw, rh, {hot:hot, r:rh / 2 > 30 ? 30 : rh / 2});
    C.text(x + pad, y, hw, rh, t.h || pad2(k + 1), {font:'head', size:hs, color:cc.ac, va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(x + pad + hw + 2, y + rh * 0.25, 1, rh * 0.5, {fill:cc.ac, op:0.5});
    C.text(x + pad + hw + 16, y, tw, rh, t.d, {font:'body', size:ds, color:cc.tc, va:'m', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  rosy(C, Z.x, Z.y, Z.w, Z.h, {r:22}); var pad = 50, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, color:RG, lh:1.14, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; K.poly(C, [[x + 10, y], [x + 20, y + 11], [x + 10, y + 22], [x, y + 11]], {fill:RG}); C.text(x + 32, y, 460, 22, f.company, {font:'body', size:13, wt:600, color:INK, va:'m', lh:1, caps:true, ls:0.16, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 78, 54);
  if (photo) { D.imgBox(C, 740, 90, 462, 540, {r:231}); gem(C, 760, 540, 70, true); }
  else { gem(C, 980, 350, 250, false); gem(C, 980, 350, 150, true); gem(C, 800, 520, 70, false); gem(C, 1150, 170, 50, false); }
  var tw = photo ? 600 : 660;
  C.text(78, 180, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 66 : 78, min:32}, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.06});
  if (C.s.subtitle) C.text(78, 448, tw - 80, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var pw = Math.min(tw, who.length * 8 + 56); rosy(C, 78, 560, pw, 48, {r:24}); C.text(78 + 26, 560, pw - 52, 48, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(78, 632, 500, 18, f.website, {font:'body', size:12.5, wt:500, color:RG, lh:1, ls:0.06, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 78, 54);
  if (photo) D.imgBox(C, 780, 110, 420, 500, {r:210}); else { gem(C, 1000, 360, 230, false); gem(C, 1000, 360, 130, true); }
  C.text(78, 130, 640, 190, C.s.title || 'Thank You', {font:'head', fit:{max:86, min:34}, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.04});
  if (C.s.subtitle) C.text(78, 336, 580, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  rosy(C, 78, 430, 620, n * 58 + 24, {r:20});
  cs.forEach(function(c, k){
    var y = 442 + k * 58;
    C.icon(78 + 28, y + 18, 22, c.icon, RG);
    C.text(78 + 70, y, 520, 58, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(78 + 28, y - 0.5, 564, 1, {fill:RG, op:0.28});
  });
}
function agendaA(C, o){
  gem(C, 250, 500, 190, false); gem(C, 250, 500, 100, true);
  var te = C.text(78, 110, 400, 200, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, color:INK, ed:{f:'title'}, maxLines:2, va:'t', lh:1.04});
  if (C.s.subtitle) C.text(78, 110 + te._h + 14, 380, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  sheet(C, {x:540, y:80, w:W - 540 - 78, h:H - 170}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RG, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 78 : 170, w = photo ? 620 : W - 340, al = photo ? 'l' : 'c';
  if (photo) D.imgBox(C, 800, 110, 400, 500, {r:200});
  if (!photo) { gem(C, W / 2, 150, 44, true); C.text(W / 2 - 44, 122, 88, 88, '”', {font:'head', size:64, color:'#2A0A16', align:'c', va:'m', lh:1, caps:false, ls:0}); }
  else C.text(x, 110, 100, 90, '“', {font:'head', size:130, color:RG, lh:1, caps:false, ls:0});
  var qe = C.text(x, 226, w, 290, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:19}, color:INK, align:al, lh:1.24, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, 226 + qe._h + 28, w, 24, bt, {font:'body', size:14, wt:600, color:RG, align:al, lh:1.1, caps:true, ls:0.14, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'garnetglass', name:'Garnet Glass', tag:'Dark · rose glass',
  desc:'A deep wine page with rose-gold light, rosy glass sheets and faceted gems: rich and elegant for luxury, beauty, events and premium brand decks.',
  fonts:{head:'Marcellus', body:'Mulish', headW:400, bodyW:400, headLh:1.1, gf:'Marcellus|Mulish:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Faceted gems', {}), V('v2', 'Oval photo + gem', {img:'photo'})],
    closing:[V('v1', 'Gem + glass contacts', {}), V('v2', 'Oval photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Gem + ruled sheet', {fn:agendaA}), V('n2', 'Gem tiles', {body:gems}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1})],
    text:[V('v1', 'Rosy statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Gem tiles', {body:gems}), V('n2', 'Zigzag cards', {body:zigzag, hl:true}), V('n3', 'Ruled sheet', {body:sheet, cols:2}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + gem tiles', {body:gems, img:'left', cols:1})],
    p3:[V('v1', 'Zigzag cards', {body:zigzag, hl:true}), V('n2', 'Gem tiles', {body:gems}), V('n3', 'Ruled sheet', {body:sheet, cols:1}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + gem tiles', {body:gems, img:'left', cols:1})],
    p4:[V('v1', 'Zigzag cards', {body:zigzag}), V('n2', 'Ruled sheet', {body:sheet}), V('n3', 'Gem tiles', {body:gems}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + gem tiles', {body:gems, img:'left', cols:1})],
    p56:[V('v1', 'Ruled sheet', {body:sheet}), V('n2', 'Gem tiles', {body:gems}), V('n3', 'Zigzag cards', {body:zigzag}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + sheet', {body:sheet, img:'left', cols:1})],
    p78:[V('v1', 'Ruled sheet', {body:sheet}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1})],
    stats:[V('v1', 'Gem stats', {body:gemStats}), V('n2', 'Progress rings', {body:'statRings', track:'#4A1C2E'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Gem stats + image right', {body:gemStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Ladder', {body:ladder}), V('n2', 'Zigzag cards', {body:zigzag}), V('n3', 'Ruled sheet', {body:sheet}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1})],
    quote:[V('v1', 'Gem quote', {}), V('v2', 'Quote + oval photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

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

}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: daybreakglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 41 - Daybreak Glass : sky-to-peach morning gradient, clear glass windows, coral and blue chips */
var CO = '#FF6F4E', BL = '#3D6BF2', INK = '#1B2547', MUT = '#55607F';
var P0 = {bg:'#DCEBFF', text:INK, title:INK, muted:MUT, accent:CO, accent2:BL, card:'#FFFFFF', line:'#C9D6EE', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[CO, BL, '#FFB199', '#8FB0FF', '#F7C948', '#1B2547'],
  header:{kind:'left', top:60, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:BL, kPill:true, kGap:28, kSize:11.5},
  card:{kind:'glass', r:20, fill:'#FFFFFF', op:0.5, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.06, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:CO, dark:true, tc:'#FFFFFF', mc:'#FFE6DE'}},
  icon:{kind:'circle', bg:BL, fg:'#FFFFFF'}, num:{kind:'big', fg:CO},
  conc:{kind:'bar', barC:CO, label:'Takeaway', lc:CO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:20, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#BCD4FF'], [100, '#FFC9B5']]}}
};
function pane(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 20;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#FF8A63'], [100, '#F2553A']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#FFFFFF', 0.3], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#FFE6DE', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.82], [100, '#FFFFFF', 0.36]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.06});
  return {tc:INK, mc:MUT, ac:CO};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover, t = f.website || f.company || '';
  if (t) C.text(M, y, 500, 18, t, {font:'body', size:11.5, wt:600, color:MUT, lh:1, va:'m'});
  C.rect(W - M - 64, y - 5, 64, 28, {fill:'#FFFFFF', op:0.75, r:14, line:'#FFFFFF', lw:1});
  C.text(W - M - 64, y - 5, 64, 28, pad2(C.i + 1), {font:'head', size:12, wt:700, color:BL, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:155, stops:[[0, '#CFE4FF'], [55, '#EEF0FB'], [100, '#FFDCCB']]}});
  var k = (C.i || 0) % 3;
  C.ell([W - 260, -120, W - 200][k], [H - 240, H - 220, -140][k], 380, 380, {grad:{ang:135, stops:[[0, '#FFD3A8', 0.95], [100, '#FF8A63', 0.55]]}});
  C.ell([-140, W - 220, -100][k], [-150, -160, H - 200][k], 340, 340, {grad:{ang:135, stops:[[0, '#A9C7FF', 0.8], [100, '#6E93FF', 0.35]]}});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* glass windows with a title bar of three dots */
function windows(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 24, bar = 34, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - bar, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + bar + pad * 2 + 4), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = pane(C, x, y, cw, ch2, {hot:hot});
    [CO, '#F7C948', BL].forEach(function(col, q){ C.ell(x + pad + q * 16, y + 14, 9, 9, {fill:hot ? '#FFFFFF' : col, op:hot ? 0.9 - q * 0.25 : 1}); });
    C.rect(x + 1, y + bar, cw - 2, 1, {fill:hot ? '#FFFFFF' : INK, op:hot ? 0.35 : 0.1});
    C.icon(x + cw - pad - 16, y + 9, 16, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? '#FFFFFF' : BL);
    K.hd(C, x + pad, y + bar + pad - 4, tw, ch2 - bar - pad * 2 + 4, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* chip list: every heading sits in a coloured chip, the text runs under it, no boxes */
function chips(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 46, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), tw = cw - 8;
  var hasH = it.some(function(t){ return t.h; });
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), tw - 60, 26, {font:'head', max:16, min:11.5, maxLines:1}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), hasH ? tw : tw - 60, rh - (hasH ? 54 : 20), {font:'body', max:hasH ? (cols === 1 ? 18.5 : 15.5) : 21, min:10.5, lh:1.4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, col = k % 2 ? BL : CO;
    if (hasH) {
      var pw = Math.min(tw, String(t.h || pad2(k + 1)).length * hs * 0.6 + 70);
      C.rect(x, y + 6, pw, 32, {fill:col, r:16});
      C.text(x + 12, y + 6, 26, 32, pad2(k + 1), {font:'head', size:11.5, wt:800, color:'#FFFFFF', va:'m', lh:1, op:0.85});
      C.text(x + 40, y + 6, pw - 54, 32, t.h || '', {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x + 4, y + 48, tw, rh - 54, t.d, {font:'body', size:ds, color:INK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      C.rect(x, y + (rh - 34) / 2, 46, 34, {fill:col, r:17});
      C.text(x, y + (rh - 34) / 2, 46, 34, pad2(k + 1), {font:'head', size:13, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
      C.text(x + 62, y, tw - 60, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.4, ed:{f:'content-plain', i:t.i}});
    }
  });
}
/* hub: four panes around a centre disc */
function hub(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4 || Z.w < 760 || Z.h < 300) return windows(C, Z, o);
  var g = 20, cw = (Z.w - g) / 2, ch = (Z.h - g) / 2, pad = 26, hd = Math.min(130, ch * 0.7), tw = cw - pad * 2 - hd / 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2, {hMax:21, dMax:15, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var c = k % 2, r = Math.floor(k / 2), x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), cc = pane(C, x, y, cw, ch, {});
    K.hd(C, c ? x + pad + hd / 2 : x + pad, y + pad, tw, ch - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2;
  C.ell(cx - hd / 2 - 8, cy - hd / 2 - 8, hd + 16, hd + 16, {fill:'#FFFFFF', op:0.9});
  C.ell(cx - hd / 2, cy - hd / 2, hd, hd, {grad:{ang:135, stops:[[0, '#FF8A63'], [100, '#3D6BF2']]}});
  C.icon(cx - hd * 0.2, cy - hd * 0.2, hd * 0.4, D.iconFor(C.s, 0, C.s.title || ''), '#FFFFFF');
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: glass capsules with the number in a coloured end cap */
function caps(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 16, rh = Math.min((Z.h - g * (n - 1)) / n, 124), vals = st.map(sv), labs = st.map(sl), vw = Math.min(250, Z.w * 0.3), tw = Z.w - vw - 60;
  var vs = C.groupFit(vals, vw - 40, rh - 24, {font:'head', max:52, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, rh - 50, {font:'body', max:14, min:10.5, lh:1.36, maxLines:2});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var y = y0 + k * (rh + g), col = k % 2 ? BL : CO;
    pane(C, Z.x, y, Z.w, rh, {r:rh / 2});
    C.rect(Z.x + 6, y + 6, vw, rh - 12, {fill:col, r:(rh - 12) / 2});
    C.text(Z.x + 6 + 20, y + 6, vw - 40, rh - 12, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var lh = C.measureText(labs[k], tw, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.x ? C.measureText(t.x, tw, {font:'body', max:ds, min:ds, lh:1.36, maxLines:2}).h : 0, by = y + Math.max(8, (rh - lh - (dh ? dh + 6 : 0)) / 2);
    C.text(Z.x + vw + 30, by, tw, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(Z.x + vw + 30, by + lh + 6, tw, dh + 4, t.x, {font:'body', size:ds, color:MUT, lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}
/* timeline: calendar pages, the heading on a coloured tab */
function pages(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return chips(C, Z, o);
  var g = 16, cw = (Z.w - g * (n - 1)) / n, tab = 54, pad = 18, tw = cw - pad * 2, ch = Math.min(Z.h, 330), y0 = Z.y + (Z.h - ch) * 0.3;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, tab - 12, {font:'head', max:22, min:12, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - tab - pad * 2, {font:'body', max:15.5, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), col = k % 2 ? BL : CO;
    pane(C, x, y0, cw, ch, {r:16});
    C.rect(x, y0, cw, tab, {fill:col, r:16}); C.rect(x, y0 + tab - 16, cw, 16, {fill:col});
    C.ell(x + cw * 0.28 - 5, y0 - 6, 10, 18, {fill:'#FFFFFF', line:col, lw:1.5}); C.ell(x + cw * 0.72 - 5, y0 - 6, 10, 18, {fill:'#FFFFFF', line:col, lw:1.5});
    C.text(x + pad, y0 + 4, tw, tab - 4, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(x + pad, y0 + tab + pad, tw, ch - tab - pad * 2, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  pane(C, Z.x, Z.y, Z.w, Z.h, {r:26}); var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:CO, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; C.ell(x, y, 24, 24, {grad:{ang:135, stops:[[0, '#FF8A63'], [100, '#3D6BF2']]}}); C.text(x + 34, y, 440, 24, f.company, {font:'head', size:15, wt:800, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function sun(C, cx, cy, d){
  C.ell(cx - d * 0.75, cy - d * 0.75, d * 1.5, d * 1.5, {fill:'#FFFFFF', op:0.35});
  C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:160, stops:[[0, '#FFE2A8'], [60, '#FF8A63'], [100, '#F2553A']]}});
  C.ell(cx - d * 0.32, cy - d * 0.4, d * 0.4, d * 0.26, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.6], [100, '#FFFFFF', 0]]}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 52);
  if (photo) { pane(C, 690, 80, 520, 560, {r:30}); D.imgBox(C, 710, 100, 480, 520, {r:20}); }
  else { sun(C, 960, 300, 300); pane(C, 740, 380, 460, 220, {r:30}); [CO, '#F7C948', BL].forEach(function(col, q){ C.ell(770 + q * 18, 400, 10, 10, {fill:col}); }); if (f.website) C.text(770, 440, 400, 130, f.website, {font:'head', size:20, wt:700, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  var tw = photo ? 560 : 620;
  C.text(72, 170, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 64 : 76, min:32}, wt:800, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.03, ls:-0.02});
  if (C.s.subtitle) C.text(72, 436, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 52); C.rect(72, 556, pw, 48, {fill:INK, r:24}); C.text(72 + 26, 556, pw - 52, 48, who, {font:'body', size:14, wt:700, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 52);
  pane(C, 72, 120, W - 144, 500, {r:34});
  if (photo) D.imgBox(C, W - 72 - 420, 150, 390, 440, {r:22}); else sun(C, W - 300, 370, 250);
  var tw = 620;
  C.text(120, 160, tw, 150, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(120, 324, tw - 40, 62, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 410 + k * 60, col = k % 2 ? BL : CO;
    C.ell(120, y, 44, 44, {fill:col}); C.icon(120 + 12, y + 12, 20, c.icon, '#FFFFFF');
    C.text(120 + 60, y, tw - 80, 44, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  sun(C, 230, 520, 230);
  var te = C.text(72, 110, 400, 220, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 110 + te._h + 14, 380, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  pane(C, 520, 70, W - 520 - 72, H - 170, {r:28});
  chips(C, {x:556, y:92, w:W - 556 - 108, h:H - 214}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CO, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 640 : W - 144;
  if (photo) D.imgBox(C, 770, 100, 438, 500, {r:22});
  pane(C, x, 100, w, 500, {r:30});
  C.ell(x + 44, 136, 64, 64, {fill:CO}); C.text(x + 44, 148, 64, 64, '”', {font:'head', size:70, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 230, w - 92, 270, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:700, color:INK, lh:1.24, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 230 + qe._h + 24, w - 92, 24, bt, {font:'body', size:15, wt:700, color:BL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'daybreakglass', name:'Daybreak Glass', tag:'Light · clear glass',
  desc:'A sky-to-peach morning gradient with clear glass windows and coral and blue chips: fresh and optimistic for product, SaaS and startup decks.',
  fonts:{head:'Urbanist', body:'Figtree', headW:800, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Urbanist:wght@600;700;800|Figtree:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Sun + glass window', {}), V('v2', 'Photo in glass frame', {img:'photo'})],
    closing:[V('v1', 'Glass sheet + sun', {}), V('v2', 'Glass sheet + photo', {img:'photo'})],
    agenda:[V('v1', 'Sun + chip list in glass', {fn:agendaA}), V('n2', 'Glass windows', {body:windows}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass windows', {body:windows, hl:true}), V('n2', 'Chip list', {body:chips, cols:2}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + windows', {body:windows, img:'left', cols:1})],
    p3:[V('v1', 'Glass windows', {body:windows, hl:true}), V('n2', 'Chip list', {body:chips, cols:1}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + windows', {body:windows, img:'left', cols:1})],
    p4:[V('v1', 'Hub', {body:hub}), V('n2', 'Glass windows', {body:windows}), V('n3', 'Chip list', {body:chips}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + windows', {body:windows, img:'left', cols:2})],
    p56:[V('v1', 'Glass windows', {body:windows}), V('n2', 'Chip list', {body:chips}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + chip list', {body:chips, img:'left', cols:1})],
    p78:[V('v1', 'Chip list', {body:chips}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1})],
    stats:[V('v1', 'Glass capsules', {body:caps}), V('n2', 'Progress rings', {body:'statRings', track:'#D6E0F5'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Capsules + image right', {body:caps, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Calendar pages', {body:pages}), V('n2', 'Chip list', {body:chips}), V('n3', 'Glass windows', {body:windows}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: citruspop */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 42 - Citrus Pop : lemon yellow page, black ink blocks with hard offset shadows, bold grotesque type */
var YE = '#FFD60A', BK = '#111111', WH = '#FFFFFF', OR = '#FF5A1F', MUT = '#3A3A3A';
var P0 = {bg:YE, text:BK, title:BK, muted:MUT, accent:BK, accent2:OR, card:WH, line:BK, onAccent:YE};
var S = {
  margin:70, series:[BK, OR, '#FFFFFF', '#7A5CFF', '#8A8A8A', '#FFB300'],
  header:{kind:'left', top:58, tMax:50, tMin:26, sMax:17, sMin:13, kicker:function(C){ return '#' + pad2(C.i + 1); }, kc:BK, kGap:26, kSize:13},
  card:{kind:'solid', r:0, fill:WH, line:BK, lw:2.5, shadow:0, dark:false, tc:BK, mc:MUT,
        hl:{kind:'solid', fill:BK, dark:true, tc:WH, mc:'#DADADA'}},
  icon:{kind:'square', bg:BK, fg:YE, r:0}, num:{kind:'big', fg:BK},
  conc:{kind:'bar', barC:BK, label:'So what', lc:BK, tc:BK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#2A2A2A'], [100, '#111111']]}}
};
/* sticker: a block with a hard offset shadow */
function stk(C, x, y, w, h, o){
  o = o || {}; var d = o.d != null ? o.d : 8, mode = o.mode || 'w';
  C.rect(x + d, y + d, w, h, {fill:mode === 'k' ? OR : BK});
  C.rect(x, y, w, h, {fill:mode === 'k' ? BK : (mode === 'o' ? OR : WH), line:BK, lw:2.5});
  return mode === 'k' ? {tc:WH, mc:'#DADADA', ac:YE} : (mode === 'o' ? {tc:WH, mc:'#FFE9DF', ac:BK} : {tc:BK, mc:MUT, ac:OR});
}
function foot(C){
  var M = 70, y = H - 38, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 2.5, {fill:BK});
  C.text(M, y, 600, 18, f.company || f.website || '', {font:'head', size:12, wt:800, color:BK, lh:1, va:'m', caps:true, ls:0.06});
  C.text(W - M - 120, y, 120, 18, pad2(C.i + 1) + '/' + pad2(C.total), {font:'head', size:12, wt:800, color:BK, align:'r', va:'m', lh:1});
}
function burst(C, cx, cy, r, color){
  var p = []; for (var k = 0; k < 24; k++) { var a = k * Math.PI / 12, rr = k % 2 ? r * 0.78 : r; p.push([cx + Math.cos(a) * rr, cy + Math.sin(a) * rr]); }
  K.poly(C, p, {fill:color});
}
function bg(C, kind){
  C.bg({fill:YE});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) burst(C, W - 90, 90, 70, OR);
  else if (k === 1) { C.rect(W - 150, 0, 150, 26, {fill:BK}); C.rect(W - 26, 0, 26, 150, {fill:BK}); }
  else K.dots(C, W - 190, 50, 6, 4, 24, 4, BK, 1);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* sticker cards with a black number tab */
function stickers(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 26, cw = (Z.w - 8 - g * (cols - 1)) / cols, ch = (Z.h - 8 - g * (rows - 1)) / rows, pad = 24, tab = 44, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - tab + 8, {hMax:22, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + tab + pad * 2), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = stk(C, x, y, cw, ch2, {mode:hot ? 'k' : 'w'});
    C.rect(x, y, 64, tab, {fill:hot ? YE : BK});
    C.text(x, y, 64, tab, pad2(k + 1), {font:'head', size:18, wt:800, color:hot ? BK : YE, align:'c', va:'m', lh:1});
    C.icon(x + cw - pad - 22, y + 11, 22, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? YE : BK);
    K.hd(C, x + pad, y + tab + pad - 6, tw, ch2 - tab - pad * 2 + 6, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* stripes: full-width rows that alternate black and white */
function stripes(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 130), nw = 90, pad = 22, hasH = it.some(function(t){ return t.h; }), hw = hasH ? Math.min(300, (Z.w - nw) * 0.34) : 0, tw = Z.w - nw - hw - pad * 2;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw - 10, rh - 16, {font:'head', max:22, min:12.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:hasH ? 16 : 21, min:10.5, lh:1.36});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x - 1, y0 - 1, Z.w + 2, tot + 2, {fill:BK});
  it.forEach(function(t, k){
    var y = y0 + k * rh, dk = k % 2 === 1;
    C.rect(Z.x + 1.5, y + 1.5, Z.w - 3, rh - 3, {fill:dk ? BK : WH});
    C.text(Z.x, y, nw, rh, pad2(k + 1), {font:'head', size:Math.min(40, rh * 0.42), wt:800, color:dk ? YE : OR, align:'c', va:'m', lh:1});
    if (hasH) C.text(Z.x + nw, y, hw - 10, rh, t.h || '', {font:'head', size:hs, wt:800, color:dk ? WH : BK, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + pad, y, tw, rh, t.d, {font:'body', size:ds, color:dk ? '#EDEDED' : BK, va:'m', lh:1.36, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* shout: one black sticker shouting the first point, the rest as arrow lines */
function shout(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return stickers(C, Z, o);
  var lw = Math.round(Z.w * 0.42), g = 40, pad = 30, rest = it.slice(1), rn = rest.length, rw = Z.w - lw - g, rh = Z.h / rn;
  var c1 = stk(C, Z.x, Z.y, lw - 8, Z.h - 8, {mode:'k', d:10});
  burst(C, Z.x + lw - 50, Z.y + 40, 46, YE); C.text(Z.x + lw - 96, Z.y - 6, 92, 92, '01', {font:'head', size:22, wt:800, color:BK, align:'c', va:'m', lh:1});
  var F1 = K.fitHD(C, [it[0]], lw - 8 - pad * 2, 90, Z.h - pad * 2 - 60, {hMax:32, dMax:18, hMin:16, dMin:11});
  K.hd(C, Z.x + pad, Z.y + 70, lw - 8 - pad * 2, Z.h - pad - 90, it[0], F1, {tc:c1.tc, mc:c1.mc});
  var F = K.fitHD(C, rest, rw - 54, 30, rh - 22, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var y = Z.y + k * rh, bh = blockH(C, t, rw - 54, F), oy = Math.max(8, (rh - bh) / 2);
    K.poly(C, [[Z.x + lw + g, y + oy + 2], [Z.x + lw + g + 22, y + oy + 2], [Z.x + lw + g + 34, y + oy + 12], [Z.x + lw + g + 22, y + oy + 22], [Z.x + lw + g, y + oy + 22]], {fill:BK});
    K.hd(C, Z.x + lw + g + 54, y + oy, rw - 54, rh - oy, t, F, {tc:BK, mc:MUT});
    if (k < rn - 1) C.rect(Z.x + lw + g, y + rh - 1, rw, 2.5, {fill:BK});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: black towers of growing height, the number in yellow on top */
function towers(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 30, cw = (Z.w - g * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 8, lab = Math.min(120, Z.h * 0.34), th = Z.h - lab;
  var vs = C.groupFit(vals, cw - 30, 80, {font:'head', max:60, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, lab - 50, {font:'body', max:13.5, min:10.5, lh:1.36});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + g), hh = Math.max(vs + 40, th * (0.5 + 0.5 * (n > 1 ? k / (n - 1) : 1))), y = Z.y + th - hh, mode = k === n - 1 ? 'o' : 'k';
    C.rect(x + 8, y + 8, cw - 8, hh - 8, {fill:mode === 'o' ? BK : OR}); C.rect(x, y, cw - 8, hh - 8, {fill:mode === 'o' ? OR : BK, line:BK, lw:2.5});
    C.text(x + 12, y + 12, cw - 32, vs + 12, vals[k], {font:'head', size:vs, wt:800, color:mode === 'o' ? WH : YE, lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x, Z.y + th + 16, tw, 46, labs[k], {font:'head', size:ls, wt:800, color:BK, lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x, Z.y + th + 22 + le._h, tw, lab - 22 - le._h, t.x, {font:'body', size:ds, color:MUT, lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
  C.rect(Z.x, Z.y + th, Z.w, 2.5, {fill:BK});
}
/* timeline: chevron chain */
function chevrons(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return stripes(C, Z, o);
  var cw = Z.w / n, ah = 64, nt = 22, tw = cw - 26;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - nt * 2 - 10, ah - 12, {font:'head', max:22, min:12, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - ah - 50, {font:'body', max:15.5, min:10.5, lh:1.4});
  var y = Z.y + 6;
  it.forEach(function(t, k){
    var x = Z.x + k * cw, dk = k % 2 === 0, last = k === n - 1;
    K.poly(C, [[x, y], [x + cw - nt, y], [x + cw, y + ah / 2], [x + cw - nt, y + ah], [x, y + ah], [k ? x + nt : x, y + ah / 2]], {fill:last ? OR : (dk ? BK : WH)});
    C.text(x + nt + 4, y, cw - nt * 2 - 8, ah, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:last ? WH : (dk ? YE : BK), align:'c', va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(x + 13, y + ah + 22, 2.5, Z.h - ah - 40, {fill:BK});
    C.text(x + 28, y + ah + 22, tw - 14, Z.h - ah - 40, t.d, {font:'body', size:ds, color:BK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  stk(C, Z.x, Z.y, Z.w - 10, Z.h - 10, {d:10}); var pad = 46, y = Z.y + pad, w = Z.w - 10 - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, wt:800, color:OR, lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - 10 - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:BK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, W, 26, {fill:BK}); 
  if (f.company) C.text(70, 56, 600, 24, f.company, {font:'head', size:16, wt:800, color:BK, lh:1, caps:true, ls:0.04, ed:{f:'tf-company'}, maxLines:1});
  if (photo) { C.rect(742, 122, 470, 470, {fill:BK}); D.imgBox(C, 726, 106, 470, 470, {r:0}); burst(C, 740, 560, 70, OR); }
  else { C.rect(810, 150, 380, 380, {fill:BK}); C.ell(770, 110, 380, 380, {fill:OR, line:BK, lw:2.5}); burst(C, 1130, 500, 90, WH); C.ell(830, 170, 260, 260, {line:BK, lw:2.5}); }
  var tw = photo ? 610 : 680;
  C.text(70, 150, tw, 300, C.s.title, {font:'head', fit:{max:photo ? 76 : 96, min:34}, wt:800, color:BK, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98, ls:-0.03});
  if (C.s.subtitle) { C.rect(70, 470, 60, 6, {fill:BK}); C.text(70, 492, tw - 100, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, wt:500, color:BK, ed:{f:'subtitle'}, lh:1.42, maxLines:3}); }
  var who = [f.presenter, f.designation].filter(Boolean).join(' / ');
  if (who) { var pw = Math.min(tw, who.length * 8.6 + 48); stk(C, 70, 596, pw, 46, {mode:'k', d:6}); C.text(70 + 22, 596, pw - 44, 46, who, {font:'body', size:14, wt:700, color:WH, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(W - 70 - 400, 644, 400, 18, f.website, {font:'head', size:13, wt:800, color:BK, align:'r', lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(0, 0, W, H, {fill:BK});
  burst(C, W - 200, 170, 120, YE);
  if (photo) { C.rect(796, 216, 400, 400, {fill:OR}); D.imgBox(C, 780, 200, 400, 400, {r:0}); }
  else C.text(W - 320, 110, 240, 120, ':)', {font:'head', size:64, wt:800, color:BK, align:'c', va:'m', lh:1, caps:false});
  C.text(70, 110, 700, 220, C.s.title || 'Thank You', {font:'head', fit:{max:110, min:38}, wt:800, color:YE, ed:{f:'title'}, maxLines:2, va:'b', lh:0.98, ls:-0.03});
  if (C.s.subtitle) C.text(70, 346, 640, 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:WH, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 440 + k * 62;
    C.rect(70, y, 46, 46, {fill:YE}); C.icon(70 + 12, y + 12, 22, c.icon, BK);
    C.text(70 + 64, y, 600, 46, c.val, {font:'head', fit:{max:20, min:12}, wt:700, color:WH, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 440, H, {fill:BK});
  burst(C, 380, 560, 90, OR);
  var te = C.text(70, 120, 320, 260, C.s.title || 'Agenda', {font:'head', fit:{max:76, min:34}, wt:800, color:YE, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98, ls:-0.03});
  if (C.s.subtitle) C.text(70, 120 + te._h + 18, 310, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:WH, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  stripes(C, {x:510, y:76, w:W - 510 - 70, h:H - 170}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BK, tc:BK}); return; }
  var photo = o.img === 'photo', x = 70, w = photo ? 640 : W - 150;
  if (photo) { C.rect(786, 116, 420, 480, {fill:BK}); D.imgBox(C, 770, 100, 420, 480, {r:0}); }
  stk(C, x, 100, w, 480, {mode:'w', d:10});
  C.rect(x + 40, 100, 90, 70, {fill:BK}); C.text(x + 40, 112, 90, 70, '“', {font:'head', size:90, wt:800, color:YE, align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 44, 206, w - 88, 280, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:800, color:BK, lh:1.14, ls:-0.02, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 44, 206 + qe._h + 24, w - 88, 24, bt, {font:'body', size:15, wt:700, color:OR, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'citruspop', name:'Citrus Pop', tag:'Lemon · bold blocks',
  desc:'A lemon-yellow page with black ink blocks, hard offset shadows and loud grotesque type: playful and punchy for marketing, creators, events and campaigns.',
  fonts:{head:'Bricolage Grotesque', body:'Work Sans', headW:800, bodyW:400, headLh:1.02, headLs:-0.02, gf:'Bricolage+Grotesque:wght@600;700;800|Work+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Big type + pop shapes', {}), V('v2', 'Photo sticker', {img:'photo'})],
    closing:[V('v1', 'Black page + burst', {}), V('v2', 'Black page + photo', {img:'photo'})],
    agenda:[V('v1', 'Black panel + stripes', {fn:agendaA}), V('n2', 'Sticker cards', {body:stickers}), V('v2', 'Stripes + image right', {body:stripes, img:'right'})],
    text:[V('v1', 'Sticker statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Sticker cards', {body:stickers, hl:true}), V('n2', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stickers', {body:stickers, img:'left', cols:1})],
    p3:[V('v1', 'Sticker cards', {body:stickers, hl:true}), V('n2', 'Shout + arrows', {body:shout}), V('n3', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stickers', {body:stickers, img:'left', cols:1})],
    p4:[V('v1', 'Shout + arrows', {body:shout}), V('n2', 'Sticker cards', {body:stickers}), V('n3', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stickers', {body:stickers, img:'left', cols:2})],
    p56:[V('v1', 'Sticker cards', {body:stickers}), V('n2', 'Stripes', {body:stripes}), V('n3', 'Shout + arrows', {body:shout}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stripes', {body:stripes, img:'left'})],
    p78:[V('v1', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'})],
    stats:[V('v1', 'Towers', {body:towers}), V('n2', 'Progress rings', {body:'statRings', track:'#E8BE00'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Towers + image right', {body:towers, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Chevron chain', {body:chevrons}), V('n2', 'Stripes', {body:stripes}), V('n3', 'Sticker cards', {body:stickers}), V('v2', 'Stripes + image right', {body:stripes, img:'right'})],
    quote:[V('v1', 'Sticker quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: monoink */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 43 - Mono Ink : newsprint page, black ink rules, one red accent, editorial serif headlines */
var BK = '#141414', RD = '#D7261E', PA = '#F4F1EA', MUT = '#55524C';
var P0 = {bg:PA, text:BK, title:BK, muted:MUT, accent:RD, accent2:BK, card:'#FFFFFF', line:BK, onAccent:'#FFFFFF'};
var S = {
  margin:76, series:[BK, RD, '#8C8880', '#C9C4B8', '#5A1410', '#3A3A3A'],
  header:{kind:'left', top:86, tMax:48, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'outline', r:0, fill:'#FFFFFF', line:BK, lw:1, shadow:0, dark:false, tc:BK, mc:MUT,
        hl:{kind:'solid', fill:BK, dark:true, tc:'#FFFFFF', mc:'#D8D5CE'}},
  icon:{kind:'none', fg:RD}, num:{kind:'big', fg:RD},
  conc:{kind:'bar', barC:RD, label:'Bottom line', lc:RD, tc:BK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:24,
  imgPh:{grad:{ang:135, stops:[[0, '#CFCAC0'], [100, '#8C8880']]}}
};
function mast(C){
  var M = 76, f = C.cover;
  C.rect(M, 44, W - M * 2, 3, {fill:BK}); C.rect(M, 50, W - M * 2, 1, {fill:BK});
  C.text(M, 58, 500, 16, f.company || '', {font:'body', size:10.5, wt:700, color:BK, lh:1, caps:true, ls:0.18});
  C.text(W - M - 400, 58, 400, 16, 'No. ' + pad2(C.i + 1), {font:'body', size:10.5, wt:700, color:RD, align:'r', lh:1, caps:true, ls:0.18});
}
function foot(C){
  var M = 76, y = H - 34, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 1, {fill:BK});
  C.text(M, y, 500, 16, f.website || '', {font:'body', size:10.5, wt:500, color:MUT, lh:1, va:'m', ls:0.06});
  C.text(W - M - 200, y, 200, 16, 'Page ' + (C.i + 1) + ' of ' + C.total, {font:'body', size:10.5, wt:500, color:MUT, align:'r', va:'m', lh:1});
}
function bg(C, kind){ C.bg({fill:PA}); if (kind === 'cover' || kind === 'closing') return; mast(C); }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* newspaper columns with hairline rules between and a red numeral */
function columns(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : 3); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), gx = 44, gy = 26, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, top = 44;
  var F = K.fitHD(C, it, cw, 56, ch - top - 4, {hMax:24, dMax:15.5, hMin:13, dMin:10.5});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy);
    C.rect(x, y, cw, 3, {fill:BK});
    C.text(x, y + 12, 60, 24, pad2(k + 1), {font:'body', size:12, wt:700, color:RD, lh:1, ls:0.14});
    K.hd(C, x, y + top, cw, ch - top, t, F, {tc:BK, mc:MUT});
    if (c < cols - 1 && k < n - 1) C.rect(x + cw + gx / 2, y, 1, ch, {fill:BK, op:0.35});
  });
}
/* ledger: heading on the left, copy on the right, a rule above each row */
function ledger(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 130), hasH = it.some(function(t){ return t.h; }), nw = 50, hw = hasH ? Math.min(310, (Z.w - nw) * 0.36) : 0, tw = Z.w - nw - hw - (hasH ? 30 : 0);
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw, rh - 20, {font:'head', max:23, min:13, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 20, {font:'body', max:hasH ? 16 : 22, min:10.5, lh:1.4});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    C.rect(Z.x, y, Z.w, k ? 1 : 3, {fill:BK});
    C.text(Z.x, y + 4, nw, rh - 4, pad2(k + 1), {font:'body', size:12, wt:700, color:RD, va:'m', lh:1, ls:0.1});
    if (hasH) C.text(Z.x + nw, y + 4, hw, rh - 4, t.h || '', {font:'head', size:hs, wt:700, color:BK, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + (hasH ? 30 : 0), y + 4, tw, rh - 4, t.d, {font:'body', size:ds, color:hasH ? MUT : BK, va:'m', lh:1.4, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
  C.rect(Z.x, y0 + tot, Z.w, 1, {fill:BK});
}
/* front page: the lead story across the top, the briefs in columns below */
function front(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return columns(C, Z, o);
  var lh = Math.round(Z.h * 0.4), rest = it.slice(1), rn = rest.length, gx = 40, cw = (Z.w - gx * (rn - 1)) / rn, by = Z.y + lh + 26, bh = Z.h - lh - 26;
  C.rect(Z.x, Z.y, Z.w, 3, {fill:BK});
  C.rect(Z.x, Z.y + 16, 86, 24, {fill:RD}); C.text(Z.x, Z.y + 16, 86, 24, 'LEAD', {font:'body', size:11, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, ls:0.2});
  var hw = Z.w * 0.42, t0 = it[0];
  if (t0.h) C.text(Z.x, Z.y + 52, hw - 30, lh - 56, t0.h, {font:'head', fit:{max:38, min:18}, wt:800, color:BK, lh:1.06, ed:{f:'x-part', i:t0.i, p:0}, maxLines:3, va:'t'});
  C.text(t0.h ? Z.x + hw : Z.x, Z.y + 52, t0.h ? Z.w - hw : Z.w, lh - 56, t0.d, {font:'body', fit:{max:20, min:12}, color:BK, lh:1.42, ed:t0.h ? {f:'x-part', i:t0.i, p:1} : {f:'content-plain', i:t0.i}});
  var F = K.fitHD(C, rest, cw, 50, bh - 20, {hMax:20, dMax:14.5, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var x = Z.x + k * (cw + gx);
    C.rect(x, by, cw, 1, {fill:BK});
    K.hd(C, x, by + 16, cw, bh - 16, t, F, {tc:BK, mc:MUT});
    if (k < rn - 1) C.rect(x + cw + gx / 2, by, 1, bh, {fill:BK, op:0.35});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: huge serif figures over a double rule */
function figures(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var gx = 44, cw = (Z.w - gx * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), vh = Math.min(150, Z.h * 0.45);
  var vs = C.groupFit(vals, cw, vh, {font:'head', max:104, min:28, maxLines:1});
  var ls = C.groupFit(labs, cw, 54, {font:'head', max:21, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw, Z.h - vh - 90, {font:'body', max:14.5, min:10.5, lh:1.4});
  var y0 = Z.y + Math.max(0, (Z.h - vh - 150) * 0.25);
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gx);
    C.text(x, y0, cw, vh, vals[k], {font:'head', size:vs, wt:800, color:k % 2 ? RD : BK, va:'b', lh:0.95, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x, y0 + vh + 12, cw, 3, {fill:BK}); C.rect(x, y0 + vh + 18, cw, 1, {fill:BK});
    var le = C.text(x, y0 + vh + 32, cw, 52, labs[k], {font:'head', size:ls, wt:700, color:BK, lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x, y0 + vh + 40 + le._h, cw, Z.y + Z.h - (y0 + vh + 40 + le._h), t.x, {font:'body', size:ds, color:MUT, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: dateline - boxed dates on a rule, copy beneath */
function dateline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return ledger(C, Z, o);
  var gx = 26, cw = (Z.w - gx * (n - 1)) / n, bh = 46, y = Z.y + 10;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - 20, bh - 10, {font:'head', max:22, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw, Z.h - bh - 60, {font:'body', max:15.5, min:10.5, lh:1.42});
  C.rect(Z.x, y + bh / 2 - 0.5, Z.w, 1, {fill:BK});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), last = k === n - 1, bw = Math.min(cw, String(t.h || pad2(k + 1)).length * hs * 0.6 + 34);
    C.rect(x, y, bw, bh, {fill:last ? RD : BK});
    C.text(x, y, bw, bh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x, y + bh + 24, cw, Z.h - bh - 40, t.d, {font:'body', size:ds, color:BK, lh:1.42, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, 3, {fill:BK}); var y = Z.y + 30;
  if (it.h) { var he = C.text(Z.x, y, Z.w, 90, it.h, {font:'head', fit:{max:40, min:20}, wt:800, color:BK, italic:true, lh:1.08, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.rect(Z.x, y + 6, 4, Math.min(Z.y + Z.h - y - 20, 200), {fill:RD});
  C.text(Z.x + 28, y, Z.w - 28, Z.y + Z.h - y - 10, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:BK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', M = 76;
  C.rect(M, 56, W - M * 2, 5, {fill:BK}); C.rect(M, 66, W - M * 2, 1, {fill:BK});
  C.text(M, 82, W - M * 2, 46, f.company || 'The Presentation', {font:'head', size:34, wt:800, color:BK, align:'c', lh:1, ed:{f:'tf-company'}, maxLines:1});
  C.rect(M, 140, W - M * 2, 1, {fill:BK}); 
  C.text(M, 148, 500, 18, f.website || '', {font:'body', size:11, wt:600, color:MUT, lh:1, va:'m', caps:true, ls:0.14, ed:f.website ? {f:'tf-website'} : null});
  C.text(W - M - 300, 148, 300, 18, 'Special edition', {font:'body', size:11, wt:700, color:RD, align:'r', lh:1, va:'m', caps:true, ls:0.14});
  C.rect(M, 174, W - M * 2, 3, {fill:BK});
  var tw = photo ? 620 : W - M * 2;
  if (photo) D.imgBox(C, M + 660, 204, W - M * 2 - 660, 380, {r:0});
  C.text(M, 204, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 72 : 100, min:34}, wt:800, color:BK, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98, ls:-0.02});
  if (C.s.subtitle) C.text(M, 476, Math.min(tw, 700), 76, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:MUT, italic:true, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.rect(M, 606, W - M * 2, 1, {fill:BK});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(M, 622, 60, 20, 'By', {font:'body', size:13, wt:700, color:RD, lh:1, caps:true, ls:0.14}); C.text(M + 40, 620, 700, 22, who, {font:'body', size:15, wt:600, color:BK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', M = 76;
  C.rect(0, 0, W, H, {fill:BK});
  C.rect(M, 56, W - M * 2, 5, {fill:'#FFFFFF'}); C.rect(M, 66, W - M * 2, 1, {fill:'#FFFFFF'});
  if (photo) D.imgBox(C, W - M - 400, 110, 400, 480, {r:0});
  var tw = photo ? 640 : W - M * 2;
  C.text(M, 110, tw, 220, C.s.title || 'Thank You', {font:'head', fit:{max:120, min:40}, wt:800, color:'#FFFFFF', italic:true, ed:{f:'title'}, maxLines:2, va:'b', lh:0.98, ls:-0.02});
  if (C.s.subtitle) C.text(M, 346, Math.min(tw, 680), 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#D8D5CE', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var cw = tw / n;
  C.rect(M, 450, tw, 1, {fill:'#FFFFFF'});
  cs.forEach(function(c, k){
    var x = M + k * cw;
    C.icon(x, 478, 22, c.icon, RD);
    C.text(x, 512, cw - 24, 30, c.val, {font:'body', fit:{max:18, min:11}, wt:600, color:'#FFFFFF', va:'t', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(x - 14, 466, 1, 80, {fill:'#FFFFFF', op:0.4});
  });
}
function agendaA(C, o){
  var M = 76;
  var te = C.text(M, 100, 400, 240, C.s.title || 'Agenda', {font:'head', fit:{max:84, min:36}, wt:800, color:BK, italic:true, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98});
  C.rect(M, 100 + te._h + 16, 70, 5, {fill:RD});
  if (C.s.subtitle) C.text(M, 100 + te._h + 40, 380, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  ledger(C, {x:540, y:100, w:W - 540 - M, h:H - 190}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], M = 76;
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RD, tc:BK}); return; }
  var photo = o.img === 'photo', w = photo ? 640 : W - M * 2;
  if (photo) D.imgBox(C, W - M - 420, 100, 420, 500, {r:0});
  C.rect(M, 100, w, 3, {fill:BK});
  C.text(M, 110, 120, 110, '“', {font:'head', size:170, wt:800, color:RD, lh:1, caps:false, ls:0});
  var qe = C.text(M, 226, w, 290, C.s.title, {font:'head', fit:{max:photo ? 40 : 52, min:20}, wt:700, color:BK, italic:true, lh:1.16, ed:{f:'title'}, maxLines:6, va:'t'});
  C.rect(M, 226 + qe._h + 24, w, 1, {fill:BK});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(M, 226 + qe._h + 40, w, 24, bt, {font:'body', size:14, wt:700, color:BK, lh:1.1, caps:true, ls:0.14, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'monoink', name:'Mono Ink', tag:'Newsprint · editorial',
  desc:'A newsprint page with black ink rules, one red accent and big editorial serif headlines: authoritative and timeless for reports, research, journalism and thought leadership.',
  fonts:{head:'Playfair Display', body:'Libre Franklin', headW:800, bodyW:400, headLh:1.06, gf:'Playfair+Display:ital,wght@0,700;0,800;1,700;1,800|Libre+Franklin:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Front page', {}), V('v2', 'Front page + photo', {img:'photo'})],
    closing:[V('v1', 'Black back page', {}), V('v2', 'Back page + photo', {img:'photo'})],
    agenda:[V('v1', 'Headline + ledger', {fn:agendaA}), V('n2', 'Columns', {body:columns}), V('v2', 'Ledger + image right', {body:ledger, img:'right'})],
    text:[V('v1', 'Editorial statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Columns', {body:columns}), V('n2', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + columns', {body:columns, img:'left', cols:1})],
    p3:[V('v1', 'Columns', {body:columns}), V('n2', 'Front page', {body:front}), V('n3', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + ledger', {body:ledger, img:'left'})],
    p4:[V('v1', 'Front page', {body:front}), V('n2', 'Columns', {body:columns}), V('n3', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + columns', {body:columns, img:'left', cols:2})],
    p56:[V('v1', 'Columns', {body:columns}), V('n2', 'Ledger', {body:ledger}), V('n3', 'Front page', {body:front}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + ledger', {body:ledger, img:'left'})],
    p78:[V('v1', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'})],
    stats:[V('v1', 'Big figures', {body:figures}), V('n2', 'Progress rings', {body:'statRings', track:'#D8D3C7'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Figures + image right', {body:figures, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Dateline', {body:dateline}), V('n2', 'Ledger', {body:ledger}), V('n3', 'Columns', {body:columns}), V('v2', 'Ledger + image right', {body:ledger, img:'right'})],
    quote:[V('v1', 'Pull quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: rosewaterglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 44 - Rosewater Glass : blush page, pearly glass bubbles and petals, berry ink */
var BE = '#B4235A', PL = '#F28CA8', INK = '#4A1230', MUT = '#8A5A6E';
var P0 = {bg:'#FCE9EE', text:INK, title:INK, muted:MUT, accent:BE, accent2:PL, card:'#FFFFFF', line:'#F2C9D5', onAccent:'#FFFFFF'};
var S = {
  margin:76, series:[BE, PL, '#7A1640', '#FFC2D1', '#D96C8E', '#4A1230'],
  header:{kind:'center', top:58, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:BE, kGap:24, kSize:11.5},
  card:{kind:'glass', r:28, fill:'#FFFFFF', op:0.55, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:BE, dark:true, tc:'#FFFFFF', mc:'#FFDCE6'}},
  icon:{kind:'circle', bg:BE, fg:'#FFFFFF'}, num:{kind:'big', fg:BE},
  conc:{kind:'bar', barC:BE, label:'Remember', lc:BE, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#FFD0DC'], [100, '#E88BA8']]}}
};
function pearl(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 28;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:140, stops:[[0, '#D4447A'], [100, '#9A1B4C']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#FFFFFF', 0.28], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#FFDCE6', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.9], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.05});
  return {tc:INK, mc:MUT, ac:BE};
}
function bubble(C, cx, cy, d, deep){
  C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:140, stops:deep ? [[0, '#F7A8BE'], [100, '#C73A72']] : [[0, '#FFFFFF', 0.95], [100, '#FFC2D1', 0.55]]}, line:'#FFFFFF', lop:0.9, lw:1.5});
  C.ell(cx - d * 0.3, cy - d * 0.36, d * 0.34, d * 0.2, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.75], [100, '#FFFFFF', 0]]}});
}
function foot(C){
  var y = H - 36, f = C.cover;
  C.ell(W / 2 - 18, y - 6, 36, 28, {fill:'#FFFFFF', op:0.8});
  C.text(W / 2 - 18, y - 6, 36, 28, String(C.i + 1), {font:'head', size:12, wt:700, color:BE, align:'c', va:'m', lh:1});
  C.text(76, y, 400, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#FFF3F5'], [100, '#F9D6E0']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.9, W * 0.1, W * 0.5][k], [H * 0.9, H * 0.9, 0][k], 700, 480, '#F7A8BE', 0.5);
  if (kind === 'cover' || kind === 'closing') return;
  bubble(C, [W - 70, 60, W - 80][k], [H - 80, H - 70, 70][k], 190, false);
  bubble(C, [W - 190, 180, W - 200][k], [H - 40, H - 30, 40][k], 80, true);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* bubbles: each point lives inside a pearly glass circle */
function bubbles(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760 || Z.h < 280) return petals(C, Z, o);
  var g = 18, d = Math.min((Z.w - g * (n - 1)) / n, Z.h), tot = d * n + g * (n - 1), x0 = Z.x + (Z.w - tot) / 2, y0 = Z.y + (Z.h - d) / 2, tw = d * 0.68;
  var F = K.fitHD(C, it, tw, 50, d * 0.62 - 44, {hMax:20, dMax:14.5, hMin:12, dMin:10});
  it.forEach(function(t, k){
    var cx = x0 + k * (d + g) + d / 2, cy = y0 + d / 2, hot = o.hl && k === 0;
    if (hot) { C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:140, stops:[[0, '#D4447A'], [100, '#9A1B4C']]}, shadow:0.12}); C.ell(cx - d * 0.3, cy - d * 0.42, d * 0.5, d * 0.22, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.3], [100, '#FFFFFF', 0]]}}); }
    else C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05});
    C.icon(cx - 13, cy - d * 0.36, 26, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? '#FFFFFF' : BE);
    var bh = blockH(C, t, tw, F);
    K.hd(C, cx - tw / 2, cy - d * 0.36 + 40, tw, d * 0.62, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#FFDCE6' : MUT, align:'c'});
  });
}
/* petals: soft glass rows with a berry bubble number */
function petals(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 18, g = 12, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min((Z.h - g * (per - 1)) / per, 130), bd = Math.min(52, rh - 20), pad = 18, tw = cw - pad * 2 - bd - 16;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, bd) + 30); var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g), cc = pearl(C, x, y, cw, rh, {r:Math.min(rh / 2, 40)});
    bubble(C, x + pad + bd / 2, y + rh / 2, bd, true);
    C.text(x + pad, y + rh / 2 - bd / 2, bd, bd, String(k + 1), {font:'head', size:bd * 0.38, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + pad + bd + 16, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* scallop cards: glass cards with a bubble sitting half over the top edge */
function scallops(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 4 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, bd = 56, gy = bd / 2 + 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - bd / 2 - gy * (rows - 1)) / rows, pad = 22, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad - bd / 2 - 20, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + bd / 2 + pad + 26), tot = bd / 2 + ch2 * rows + gy * (rows - 1), y0 = Z.y + bd / 2 + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + gy), hot = o.hl && k === 1, cc = pearl(C, x, y, cw, ch2, {hot:hot});
    bubble(C, x + cw / 2, y, bd, !hot);
    C.icon(x + cw / 2 - 11, y - 11, 22, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? BE : '#FFFFFF');
    K.hd(C, x + pad, y + bd / 2 + 14, tw, ch2 - bd / 2 - pad - 8, t, F, {tc:cc.tc, mc:cc.mc, align:'c'});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: bubbles of growing size */
function growing(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 26, dmax = Math.min(cw - 24, Z.h * 0.58, 240), base = Z.y + dmax;
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - dmax - 76, {font:'body', max:13.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var d = dmax * (0.66 + 0.34 * (n > 1 ? k / (n - 1) : 1)), cx = Z.x + cw * (k + 0.5), cy = base - d / 2, deep = k === n - 1;
    bubble(C, cx, cy, d, deep);
    C.text(cx - d * 0.4, cy - d * 0.25, d * 0.8, d * 0.5, vals[k], {font:'head', fit:{max:54, min:18}, wt:700, color:deep ? '#FFFFFF' : BE, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + 18, tw, 46, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + 24 + le._h, tw, Z.y + Z.h - base - 24 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: string of pearls */
function pearls(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return petals(C, Z, o);
  var cw = Z.w / n, bd = 74, y = Z.y + 6, tw = cw - 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), bd - 14, bd * 0.5, {font:'head', max:19, min:10, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - bd - 40, {font:'body', max:15.5, min:10.5, lh:1.4});
  C.rect(Z.x + cw / 2, y + bd / 2 - 1, Z.w - cw, 2, {fill:PL, op:0.8});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), deep = k === n - 1;
    bubble(C, cx, y + bd / 2, bd, deep);
    C.text(cx - bd / 2 + 6, y, bd - 12, bd, t.h || String(k + 1), {font:'head', size:hs, wt:700, color:deep ? '#FFFFFF' : BE, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ph = Math.min(Z.h - bd - 30, C.measureText(t.d, tw - 28, {font:'body', max:ds, min:ds, lh:1.4}).h + 34); pearl(C, cx - tw / 2, y + bd + 22, tw, ph, {r:20});
    C.text(cx - tw / 2 + 14, y + bd + 36, tw - 28, ph - 22, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  pearl(C, Z.x, Z.y, Z.w, Z.h, {r:36}); var pad = 52, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:700, color:BE, align:'c', lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, align:'c', lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) { bubble(C, 960, 360, 560, false); D.imgBox(C, 730, 130, 460, 460, {r:230}); bubble(C, 760, 560, 110, true); bubble(C, 1180, 170, 70, true); }
  else { bubble(C, 980, 360, 480, false); bubble(C, 880, 300, 230, true); bubble(C, 1100, 480, 150, false); bubble(C, 1150, 190, 80, true); bubble(C, 790, 560, 60, true); }
  if (f.company) C.text(76, 60, 500, 20, f.company, {font:'body', size:13, wt:700, color:BE, lh:1, caps:true, ls:0.16, ed:{f:'tf-company'}, maxLines:1});
  C.text(76, 170, 620, 260, C.s.title, {font:'head', fit:{max:76, min:32}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.01});
  if (C.s.subtitle) C.text(76, 446, 540, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(600, who.length * 8.2 + 56); pearl(C, 76, 556, pw, 50, {r:25}); C.text(76 + 28, 556, pw - 56, 50, who, {font:'body', size:14, wt:700, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(76, 630, 500, 18, f.website, {font:'body', size:12.5, wt:600, color:BE, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  bubble(C, W / 2, 330, 620, false); bubble(C, 250, 560, 130, true); bubble(C, W - 230, 150, 100, true); bubble(C, W - 150, 520, 60, false);
  if (photo) D.imgBox(C, W / 2 - 70, 70, 140, 140, {r:70});
  C.text(W / 2 - 280, 210, 560, 130, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:700, color:INK, align:'c', ed:{f:'title'}, maxLines:1, va:'m', lh:1});
  if (C.s.subtitle) C.text(W / 2 - 230, 344, 460, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:MUT, align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var cw = Math.min(320, (W - 200 - 14 * (n - 1)) / n), tot = cw * n + 14 * (n - 1), x0 = (W - tot) / 2;
  cs.forEach(function(c, k){
    var x = x0 + k * (cw + 14), y = 560;
    pearl(C, x, y, cw, 64, {r:32}); bubble(C, x + 32, y + 32, 44, true); C.icon(x + 22, y + 22, 20, c.icon, '#FFFFFF');
    C.text(x + 64, y, cw - 82, 64, c.val, {font:'body', fit:{max:16, min:11}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  bubble(C, 250, 400, 420, false); bubble(C, 120, 600, 110, true);
  var te = C.text(90, 300, 320, 130, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:32}, wt:700, color:INK, align:'c', ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(110, 436, 280, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:MUT, align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  petals(C, {x:540, y:76, w:W - 540 - 76, h:H - 170}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BE, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 76 : 150, w = photo ? 640 : W - 300, al = photo ? 'l' : 'c';
  if (photo) D.imgBox(C, 790, 130, 420, 420, {r:210});
  pearl(C, x - (photo ? 0 : 40), 110, w + (photo ? 0 : 80), 470, {r:44});
  bubble(C, photo ? x + 76 : W / 2, 110, 76, true); C.text((photo ? x + 76 : W / 2) - 38, 84, 76, 76, '”', {font:'head', size:60, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 44, 184, w - 88, 290, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:600, color:INK, align:al, lh:1.26, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 44, 184 + qe._h + 24, w - 88, 24, bt, {font:'body', size:15, wt:700, color:BE, align:al, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'rosewaterglass', name:'Rosewater Glass', tag:'Light · blush glass',
  desc:'A blush page with pearly glass bubbles, soft petal rows and berry ink: gentle and modern for beauty, wellness, lifestyle, weddings and personal brands.',
  fonts:{head:'Quicksand', body:'Nunito', headW:700, bodyW:400, headLh:1.1, gf:'Quicksand:wght@500;600;700|Nunito:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Glass bubbles', {}), V('v2', 'Round photo in bubble', {img:'photo'})],
    closing:[V('v1', 'Big bubble + contacts', {}), V('v2', 'Bubble + small photo', {img:'photo'})],
    agenda:[V('v1', 'Bubble + petal rows', {fn:agendaA}), V('n2', 'Scallop cards', {body:scallops}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1})],
    text:[V('v1', 'Pearl statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Scallop cards', {body:scallops}), V('n2', 'Bubbles', {body:bubbles, hl:true}), V('n3', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p3:[V('v1', 'Bubbles', {body:bubbles, hl:true}), V('n2', 'Scallop cards', {body:scallops, hl:true}), V('n3', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p4:[V('v1', 'Scallop cards', {body:scallops}), V('n2', 'Bubbles', {body:bubbles}), V('n3', 'Petal rows', {body:petals, cols:2}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p56:[V('v1', 'Scallop cards', {body:scallops}), V('n2', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p78:[V('v1', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1})],
    stats:[V('v1', 'Growing bubbles', {body:growing}), V('n2', 'Progress rings', {body:'statRings', track:'#F6CFDA'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Bubbles + image right', {body:growing, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'String of pearls', {body:pearls}), V('n2', 'Petal rows', {body:petals}), V('n3', 'Scallop cards', {body:scallops}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1})],
    quote:[V('v1', 'Pearl quote', {}), V('v2', 'Quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: slatebrief */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 45 - Slate Brief : white consulting page, petrol ink, rust accent, tables, matrices and hairlines */
var PE = '#0F4C5C', RU = '#E36414', INK = '#1F2933', MUT = '#5B6770', HL = '#D5DBE0', TINT = '#EEF3F5';
var P0 = {bg:'#FFFFFF', text:INK, title:PE, muted:MUT, accent:PE, accent2:RU, card:TINT, line:HL, onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[PE, RU, '#5F9EA0', '#9FB4BC', '#F2A65A', '#1F2933'],
  header:{kind:'left', top:54, tMax:38, tMin:24, sMax:16, sMin:12.5, kicker:null, bar:true},
  card:{kind:'solid', r:2, fill:TINT, line:HL, lw:1, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:PE, dark:true, tc:'#FFFFFF', mc:'#D2E3E7'}},
  icon:{kind:'none', fg:PE}, num:{kind:'big', fg:PE},
  conc:{kind:'bar', barC:RU, label:'Implication', lc:RU, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:2, bodyGap:24,
  imgPh:{grad:{ang:135, stops:[[0, '#C9D8DD'], [100, '#8FA9B2']]}}
};
function foot(C){
  var M = 70, y = H - 34, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 1, {fill:HL});
  C.text(M, y, 600, 16, [f.company, 'Confidential'].filter(Boolean).join('  |  '), {font:'body', size:10.5, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, String(C.i + 1), {font:'body', size:11, wt:700, color:PE, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  C.rect(0, 0, 14, H, {fill:PE}); C.rect(14, 0, 4, H, {fill:RU});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* table: header row and ruled rows */
function table(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var hh = 38, rh = Math.min((Z.h - hh) / n, 110), hasH = it.some(function(t){ return t.h; }), nw = 56, hw = hasH ? Math.min(300, (Z.w - nw) * 0.34) : 0, tw = Z.w - nw - hw - 36;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw - 16, rh - 16, {font:'head', max:19, min:12.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:hasH ? 15.5 : 20, min:10.5, lh:1.38});
  var tot = hh + rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.25);
  C.rect(Z.x, y0, Z.w, hh, {fill:PE});
  C.text(Z.x + 16, y0, nw, hh, '#', {font:'body', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1});
  if (hasH) C.text(Z.x + nw, y0, hw, hh, 'Topic', {font:'body', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.12});
  C.text(Z.x + nw + hw + 18, y0, tw, hh, hasH ? 'Detail' : 'Item', {font:'body', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.12});
  it.forEach(function(t, k){
    var y = y0 + hh + k * rh;
    if (k % 2 === 0) C.rect(Z.x, y, Z.w, rh, {fill:TINT});
    C.text(Z.x + 16, y, nw - 16, rh, pad2(k + 1), {font:'body', size:12.5, wt:700, color:RU, va:'m', lh:1});
    if (hasH) C.text(Z.x + nw, y, hw - 16, rh, t.h || '', {font:'head', size:hs, wt:600, color:PE, va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + 18, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
    C.rect(Z.x, y + rh - 0.5, Z.w, 1, {fill:HL});
  });
}
/* matrix: a 2x2 quadrant with axes */
function matrix(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4 || Z.w < 720 || Z.h < 300) return notes(C, Z, o);
  var ax = 30, x0 = Z.x + ax, w = Z.w - ax, h = Z.h - ax, cw = w / 2, ch = h / 2, pad = 24, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 18, {hMax:21, dMax:15, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var c = k % 2, r = Math.floor(k / 2), x = x0 + c * cw, y = Z.y + r * ch, hot = k === 1;
    C.rect(x + 3, y + 3, cw - 6, ch - 6, {fill:hot ? PE : TINT});
    C.text(x + pad, y + pad - 4, 60, 18, 'Q' + (k + 1), {font:'body', size:11.5, wt:700, color:hot ? '#F2A65A' : RU, lh:1, ls:0.14});
    K.hd(C, x + pad, y + pad + 20, tw, ch - pad * 2 - 20, t, F, {tc:hot ? '#FFFFFF' : PE, mc:hot ? '#D2E3E7' : MUT});
  });
  C.rect(x0 - 1, Z.y, 2, h, {fill:INK}); C.rect(x0, Z.y + h - 1, w, 2, {fill:INK});
  K.poly(C, [[x0, Z.y - 8], [x0 - 6, Z.y + 4], [x0 + 6, Z.y + 4]], {fill:INK}); K.poly(C, [[x0 + w + 8, Z.y + h], [x0 + w - 4, Z.y + h - 6], [x0 + w - 4, Z.y + h + 6]], {fill:INK});
  C.text(x0, Z.y + h + 8, w, 18, 'Low  →  High', {font:'body', size:10.5, wt:600, color:MUT, align:'c', lh:1, caps:true, ls:0.16});
}
/* notes: columns with a petrol top bar and a small label */
function notes(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : 3); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 22, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, top = 46, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - top - pad - 6, {hMax:21, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad + 10), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.25);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0;
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:HL, lw:1});
    C.rect(x, y, cw, 5, {fill:hot ? RU : PE});
    C.text(x + pad, y + 20, 160, 16, 'Point ' + (k + 1), {font:'body', size:10.5, wt:700, color:hot ? RU : MUT, lh:1, caps:true, ls:0.16});
    C.icon(x + cw - pad - 18, y + 18, 18, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? RU : PE);
    K.hd(C, x + pad, y + top, tw, ch2 - top - pad, t, F, {tc:PE, mc:INK});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: one ruled KPI strip */
function kpi(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), pad = 26, tw = cw - pad * 2, sh = Math.min(Z.h, 300), y0 = Z.y + (Z.h - sh) * 0.3;
  var vs = C.groupFit(vals, tw, 84, {font:'head', max:68, min:24, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'body', max:16, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, sh - 190, {font:'body', max:13.5, min:10.5, lh:1.38});
  C.rect(Z.x, y0, Z.w, 3, {fill:PE}); C.rect(Z.x, y0 + sh - 1, Z.w, 1, {fill:PE});
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.rect(x, y0 + 20, 1, sh - 40, {fill:HL});
    C.text(x + pad, y0 + 26, tw, 16, 'KPI ' + (k + 1), {font:'body', size:10.5, wt:700, color:RU, lh:1, ls:0.16});
    C.text(x + pad, y0 + 50, tw, vs + 10, vals[k], {font:'head', size:vs, wt:600, color:PE, lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x + pad, y0 + 68 + vs, tw, 46, labs[k], {font:'body', size:ls, wt:700, color:INK, lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, y0 + 76 + vs + le._h, tw, sh - 90 - vs - le._h, t.x, {font:'body', size:ds, color:MUT, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: gantt - bars stepping along a time axis */
function gantt(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return table(C, Z, o);
  var lw = Math.min(170, Z.w * 0.18), ax = 30, rh = Math.min((Z.h - ax) / n, 92), gw = Z.w - lw, seg = gw / n, bw = seg * 1.0;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), lw - 16, rh - 14, {font:'head', max:22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t, k){ return t.d; }), gw * 0.46, rh - 22, {font:'body', max:14.5, min:10.5, lh:1.34});
  var y0 = Z.y + ax;
  for (var q = 0; q <= n; q++) C.rect(Z.x + lw + q * seg, Z.y + 8, 1, ax - 8 + rh * n, {fill:HL});
  C.rect(Z.x, y0, Z.w, 1.5, {fill:PE});
  it.forEach(function(t, k){
    var y = y0 + k * rh, bx = Z.x + lw + k * seg, right = k < n / 2, tw = right ? Z.x + Z.w - bx - bw - 14 : bx - Z.x - lw - 14;
    C.text(Z.x, y, lw - 16, rh, t.h || 'Phase ' + (k + 1), {font:'head', size:hs, wt:600, color:PE, va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(bx, y + rh * 0.28, bw, rh * 0.44, {fill:k === n - 1 ? RU : PE, r:2});
    C.text(right ? bx + bw + 14 : Z.x + lw, y, Math.max(tw, 120), rh, t.d, {font:'body', size:ds, color:INK, va:'m', align:right ? 'l' : 'r', lh:1.34, ed:{f:'x-part', i:t.i, p:1}});
    C.rect(Z.x, y + rh - 0.5, Z.w, 1, {fill:HL});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:TINT}); C.rect(Z.x, Z.y, 6, Z.h, {fill:RU});
  var pad = 44, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:17}, wt:600, color:PE, lh:1.14, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 28, min:14}, color:INK, lh:1.48, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, photo ? 520 : 440, H, {fill:PE}); C.rect(photo ? 520 : 440, 0, 8, H, {fill:RU});
  if (photo) D.imgBox(C, 0, 0, 520, H, {r:0});
  else { K.grid(C, 44, '#FFFFFF', 0.08, 0, 0, 440, H); C.rect(70, 70, 54, 54, {fill:RU}); C.rect(124, 124, 54, 54, {fill:'#FFFFFF', op:0.9}); C.rect(70, 178, 54, 54, {fill:'#FFFFFF', op:0.3});
    if (f.website) C.text(70, H - 90, 320, 20, f.website, {font:'body', size:13, wt:600, color:'#FFFFFF', lh:1, ed:{f:'tf-website'}}); }
  var x = (photo ? 520 : 440) + 70, tw = W - x - 70;
  if (f.company) C.text(x, 70, tw, 20, f.company, {font:'body', size:13, wt:700, color:RU, lh:1, caps:true, ls:0.16, ed:{f:'tf-company'}, maxLines:1});
  C.text(x, 170, tw, 250, C.s.title, {font:'head', fit:{max:64, min:30}, wt:600, color:PE, ed:{f:'title'}, maxLines:4, va:'t', lh:1.06, ls:-0.01});
  if (C.s.subtitle) C.text(x, 440, tw - 40, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.rect(x, 560, tw, 1, {fill:HL});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(x, 578, tw, 16, 'Prepared by', {font:'body', size:10.5, wt:700, color:MUT, lh:1, caps:true, ls:0.16}); C.text(x, 600, tw, 24, who, {font:'body', size:16, wt:600, color:INK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(W - (photo ? 520 : 440), 0, photo ? 520 : 440, H, {fill:PE}); C.rect(W - (photo ? 520 : 440) - 8, 0, 8, H, {fill:RU});
  if (photo) D.imgBox(C, W - 520, 0, 520, H, {r:0}); else K.grid(C, 44, '#FFFFFF', 0.08, W - 440, 0, 440, H);
  var tw = W - (photo ? 520 : 440) - 150;
  C.text(70, 120, tw, 190, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:600, color:PE, ed:{f:'title'}, maxLines:2, va:'b', lh:1.04});
  if (C.s.subtitle) C.text(70, 326, tw, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){
    var y = 420 + k * 58;
    C.rect(70, y, tw, 1, {fill:HL});
    C.icon(70, y + 19, 20, c.icon, RU);
    C.text(70 + 38, y, tw - 40, 58, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
  if (cs.length) C.rect(70, 420 + cs.length * 58, tw, 1, {fill:HL});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RU, tc:PE}); return; }
  var photo = o.img === 'photo', x = 90, w = photo ? 620 : W - 180;
  if (photo) D.imgBox(C, 780, 110, 430, 480, {r:2});
  C.rect(x, 130, 6, 400, {fill:RU});
  var qe = C.text(x + 40, 130, w - 40, 320, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:500, color:PE, italic:true, lh:1.22, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 40, 130 + qe._h + 28, w - 40, 24, bt, {font:'body', size:14, wt:700, color:MUT, lh:1.1, caps:true, ls:0.14, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'slatebrief', name:'Slate Brief', tag:'White · consulting',
  desc:'A clean white consulting page in petrol and rust with tables, a 2x2 matrix, KPI strips and a Gantt timeline: rigorous and quiet for strategy, audits and board papers.',
  fonts:{head:'Newsreader', body:'Public Sans', headW:600, bodyW:400, headLh:1.1, gf:'Newsreader:ital,wght@0,500;0,600;1,500|Public+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Petrol panel + grid', {}), V('v2', 'Photo panel', {img:'photo'})],
    closing:[V('v1', 'Contacts + petrol panel', {}), V('v2', 'Contacts + photo panel', {img:'photo'})],
    agenda:[V('v1', 'Table', {body:table}), V('n2', 'Note columns', {body:notes}), V('v2', 'Table + image right', {body:table, img:'right'})],
    text:[V('v1', 'Tinted statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Note columns', {body:notes, hl:true}), V('n2', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:1})],
    p3:[V('v1', 'Note columns', {body:notes, hl:true}), V('n2', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + table', {body:table, img:'left'})],
    p4:[V('v1', '2x2 matrix', {body:matrix}), V('n2', 'Note columns', {body:notes}), V('n3', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:2})],
    p56:[V('v1', 'Table', {body:table}), V('n2', 'Note columns', {body:notes}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + table', {body:table, img:'left'})],
    p78:[V('v1', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'})],
    stats:[V('v1', 'KPI strip', {body:kpi}), V('n2', 'Progress rings', {body:'statRings', track:HL}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'KPI strip + image right', {body:kpi, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gantt', {body:gantt}), V('n2', 'Table', {body:table}), V('n3', 'Note columns', {body:notes}), V('v2', 'Table + image right', {body:table, img:'right'})],
    quote:[V('v1', 'Bar quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: tangerinesplit */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 46 - Tangerine Split : cream and tangerine split screens, cocoa ink, checker blocks and ticket stubs */
var TA = '#FF7A1A', CR = '#FFF4E6', INK = '#2B1B12', MUT = '#6E5A4C', DK = '#3A2416';
var P0 = {bg:CR, text:INK, title:INK, muted:MUT, accent:TA, accent2:DK, card:'#FFFFFF', line:'#EBD9C3', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[TA, DK, '#FFB37A', '#A8876E', '#FFD9B8', '#C2500A'],
  header:{kind:'left', top:60, tMax:48, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:TA, kGap:26, kSize:11.5},
  card:{kind:'solid', r:0, fill:'#FFFFFF', line:null, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:TA, dark:true, tc:'#FFFFFF', mc:'#FFE9D6'}},
  icon:{kind:'circle', bg:TA, fg:'#FFFFFF'}, num:{kind:'big', fg:TA},
  conc:{kind:'bar', barC:TA, label:'In short', lc:TA, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#FFC79A'], [100, '#FF8E3C']]}}
};
/* 3D cube drawn from three faces */
function cube(C, cx, cy, s, c){
  var a = s * 0.866;
  K.poly(C, [[cx - a, cy - s / 2], [cx, cy], [cx, cy + s], [cx - a, cy + s / 2]], {fill:c[1]});
  K.poly(C, [[cx + a, cy - s / 2], [cx, cy], [cx, cy + s], [cx + a, cy + s / 2]], {fill:c[2]});
  K.poly(C, [[cx, cy - s], [cx + a, cy - s / 2], [cx, cy], [cx - a, cy - s / 2]], {fill:c[0]});
}
function cubes(C, cx, cy, s){
  var a = s * 0.866;
  K.poly(C, [[cx - a * 2.2, cy + s * 1.55], [cx, cy + s * 2.5], [cx + a * 2.2, cy + s * 1.55], [cx, cy + s * 0.6]], {fill:DK, op:0.18});
  cube(C, cx - a, cy + s / 2, s, [CR, '#FFD9B8', '#E8B98C']);
  cube(C, cx + a, cy + s / 2, s, ['#5A3A26', DK, '#24140B']);
  cube(C, cx, cy + s, s, ['#FFFFFF', '#FFE3C7', '#F2C49A']);
  cube(C, cx, cy - s, s, [CR, '#FFD9B8', DK]);
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.rect(M, y + 1, 14, 14, {fill:TA}); C.rect(M + 14, y + 1, 14, 14, {fill:DK});
  C.text(M + 40, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:600, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1), {font:'head', size:14, wt:800, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:CR});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { C.rect(W - 120, 0, 120, 120, {fill:TA}); C.rect(W - 180, 120, 60, 60, {fill:DK}); }
  else if (k === 1) { C.rect(W - 46, 0, 46, H, {fill:TA}); }
  else { K.poly(C, [[W - 190, 0], [W, 0], [W, 190]], {fill:TA}); K.poly(C, [[W - 90, 0], [W, 0], [W, 90]], {fill:DK}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* checker: cells with no gaps, alternating tangerine, white and cocoa */
function checker(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 26, tw = cw - pad * 2, top = rows === 1 ? 62 : 40;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - top, {hMax:22, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad * 2 + 6), tot = ch2 * rows, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = y0 + r * ch2, m = (r + c) % 3, fill = m === 0 ? TA : (m === 1 ? '#FFFFFF' : DK), lt = m !== 1;
    C.rect(x, y, cw + 0.5, ch2 + 0.5, {fill:fill});
    C.text(x + pad, y + pad - 4, 160, 44, pad2(k + 1), {font:'head', size:rows === 1 ? 38 : 26, wt:800, color:m === 0 ? DK : TA, lh:1, caps:false, maxLines:1});
    C.icon(x + cw - pad - 24, y + pad - 2, 24, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), lt ? '#FFFFFF' : TA);
    K.hd(C, x + pad, y + pad + top, tw, ch2 - pad * 2 - top, t, F, {tc:lt ? '#FFFFFF' : INK, mc:m === 0 ? '#FFF1E4' : (m === 1 ? MUT : '#E8D6C6')});
  });
}
/* tickets: stubs with a notch on each side and a dashed tear line */
function tickets(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 22, g = 14, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min((Z.h - g * (per - 1)) / per, 136), sw = Math.min(110, cw * 0.2), pad = 22, tw = cw - sw - pad * 2;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, 54) + 30); var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g);
    C.rect(x, y, cw, rh, {fill:'#FFFFFF', r:10}); C.rect(x, y, sw, rh, {fill:k % 2 ? DK : TA, r:10}); C.rect(x + sw - 10, y, 10, rh, {fill:k % 2 ? DK : TA});
    C.ell(x + sw - 9, y - 9, 18, 18, {fill:CR}); C.ell(x + sw - 9, y + rh - 9, 18, 18, {fill:CR});
    for (var q = 0; q < Math.floor((rh - 26) / 10); q++) C.rect(x + sw - 1, y + 14 + q * 10, 2, 5, {fill:CR});
    C.text(x, y, sw, rh, pad2(k + 1), {font:'head', size:Math.min(34, rh * 0.36), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + sw + pad, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:INK, mc:MUT});
  });
}
/* half and half: the first point on a tangerine half, the others on the cream half */
function halves(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return checker(C, Z, o);
  var lw = Math.round(Z.w * 0.44), pad = 34, rest = it.slice(1), rn = rest.length, rw = Z.w - lw - 40, rh = Z.h / rn;
  C.rect(Z.x, Z.y, lw, Z.h, {fill:TA});
  C.text(Z.x + pad, Z.y + pad - 6, 120, 70, '01', {font:'head', size:64, wt:800, color:DK, lh:1, caps:false});
  var F1 = K.fitHD(C, [it[0]], lw - pad * 2, 90, Z.h - pad * 2 - 80, {hMax:30, dMax:18, hMin:16, dMin:11});
  K.hd(C, Z.x + pad, Z.y + pad + 76, lw - pad * 2, Z.h - pad * 2 - 76, it[0], F1, {tc:'#FFFFFF', mc:'#FFF1E4'});
  var F = K.fitHD(C, rest, rw - 70, 30, rh - 22, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var y = Z.y + k * rh, bh = blockH(C, t, rw - 70, F), oy = Math.max(8, (rh - bh) / 2);
    C.text(Z.x + lw + 40, y + oy - 4, 60, 40, pad2(k + 2), {font:'head', size:28, wt:800, color:TA, lh:1, caps:false});
    K.hd(C, Z.x + lw + 110, y + oy, rw - 70, rh - oy, t, F, {tc:INK, mc:MUT});
    if (k < rn - 1) C.rect(Z.x + lw + 40, y + rh - 1, rw, 2, {fill:INK, op:0.12});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: split blocks - number half in cocoa or tangerine, label half in white */
function splitStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n === 4 ? 2 : (Z.w < 760 ? 1 : n), rows = Math.ceil(n / cols), g = 20, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, rows === 1 ? 330 : 190), vals = st.map(sv), labs = st.map(sl), pad = 22, vert = rows === 1 && cols > 1;
  var vw = vert ? cw : cw * 0.42, vh = vert ? ch * 0.46 : ch, tw = (vert ? cw : cw - vw) - pad * 2;
  var vs = C.groupFit(vals, vw - pad * 2, vh - 20, {font:'head', max:66, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, (vert ? ch - vh : ch) - 76, {font:'body', max:13.5, min:10.5, lh:1.38});
  var tot = ch * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch + g), dk = k % 2 === 1;
    C.rect(x, y, cw, ch, {fill:'#FFFFFF'}); C.rect(x, y, vw, vh, {fill:dk ? DK : TA});
    C.text(x + pad, y, vw - pad * 2, vh, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:vert ? 'l' : 'c', va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var tx = vert ? x + pad : x + vw + pad, ty = vert ? y + vh + 18 : y + 20;
    var le = C.text(tx, ty, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(tx, ty + le._h + 8, tw, y + ch - ty - le._h - 20, t.x, {font:'body', size:ds, color:MUT, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: flags on poles of rising height */
function flags(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return tickets(C, Z, o);
  var cw = Z.w / n, fh = 46, fw = Math.min(cw - 30, 150), base = Z.y + Math.min(Z.h * 0.5, 200), tw = cw - 24;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), fw - 26, fh - 10, {font:'head', max:21, min:11.5, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.y + Z.h - base - 26, {font:'body', max:15.5, min:10.5, lh:1.4});
  C.rect(Z.x, base, Z.w, 4, {fill:INK});
  it.forEach(function(t, k){
    var x = Z.x + k * cw + 10, ph = (base - Z.y - 6) * (0.5 + 0.5 * (n > 1 ? k / (n - 1) : 1)), top = base - ph, last = k === n - 1, col = last ? DK : TA;
    C.rect(x, top, 4, ph, {fill:INK});
    K.poly(C, [[x + 4, top], [x + 4 + fw, top], [x + 4 + fw - 14, top + fh / 2], [x + 4 + fw, top + fh], [x + 4, top + fh]], {fill:col});
    C.text(x + 12, top, fw - 30, fh, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.ell(x - 5, base - 5, 14, 14, {fill:col});
    C.text(x, base + 22, tw, Z.y + Z.h - base - 24, t.d, {font:'body', size:ds, color:INK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF'}); C.rect(Z.x, Z.y, 90, 90, {fill:TA}); C.text(Z.x, Z.y + 10, 90, 90, '“', {font:'head', size:90, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var pad = 46, y = Z.y + pad, x = Z.x + 90 + 36, w = Z.w - 90 - 36 - pad;
  if (it.h) { var he = C.text(x, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, wt:800, color:TA, lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(x, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', sx = 700;
  C.rect(sx, 0, W - sx, H, {fill:TA});
  if (photo) D.imgBox(C, sx + 60, 110, W - sx - 120, 500, {r:0});
  else { cubes(C, sx + (W - sx) / 2, 370, 118); }
  if (f.company) { C.rect(72, 60, 14, 14, {fill:TA}); C.rect(86, 60, 14, 14, {fill:DK}); C.text(112, 56, 500, 22, f.company, {font:'head', size:15, wt:800, color:INK, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  var tw = sx - 72 - 50;
  C.text(72, 170, tw, 270, C.s.title, {font:'head', fit:{max:78, min:32}, wt:800, color:INK, ed:{f:'title'}, maxLines:4, va:'t', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 40, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { C.rect(72, 574, 5, 44, {fill:TA}); C.text(92, 574, tw, 44, who, {font:'body', size:15, wt:700, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(sx + 60, H - 60, W - sx - 120, 18, f.website, {font:'body', size:12.5, wt:700, color:photo ? '#FFFFFF' : DK, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', sx = 560;
  C.rect(0, 0, sx, H, {fill:TA});
  if (photo) D.imgBox(C, 60, 110, sx - 120, 500, {r:0}); else { cubes(C, sx / 2, 370, 110); }
  var x = sx + 70, tw = W - x - 72;
  C.text(x, 110, tw, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(x, 326, tw, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 420 + k * 62;
    C.rect(x, y, 46, 46, {fill:k % 2 ? DK : TA}); C.icon(x + 12, y + 12, 22, c.icon, '#FFFFFF');
    C.text(x + 62, y, tw - 62, 46, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 460, H, {fill:TA}); C.rect(0, H - 130, 130, 130, {fill:DK}); cube(C, 340, H - 190, 70, [CR, '#FFD9B8', DK]);
  var te = C.text(72, 120, 340, 260, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(72, 120 + te._h + 18, 320, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:'#FFF1E4', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  tickets(C, {x:530, y:76, w:W - 530 - 72, h:H - 170}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:TA, tc:INK}); return; }
  var photo = o.img === 'photo';
  C.rect(0, 0, 330, H, {fill:TA}); C.text(60, 90, 240, 240, '“', {font:'head', size:300, wt:800, color:'#FFFFFF', lh:1, caps:false, ls:0});
  if (photo) D.imgBox(C, 60, 330, 210, 210, {r:105});
  var x = 400, w = W - x - 80;
  var qe = C.text(x, 150, w, 330, C.s.title, {font:'head', fit:{max:46, min:20}, wt:700, color:INK, lh:1.16, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 150 + qe._h + 26, 60, 5, {fill:TA}); C.text(x, 150 + qe._h + 46, w, 24, bt, {font:'body', size:15, wt:700, color:MUT, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'tangerinesplit', name:'Tangerine Split', tag:'Cream · split screen',
  desc:'Cream and tangerine split screens with cocoa ink, checker blocks, ticket stubs and flags: warm and energetic for retail, food, travel and community decks.',
  fonts:{head:'Syne', body:'Karla', headW:800, bodyW:400, headLh:1.04, headLs:-0.01, gf:'Syne:wght@600;700;800|Karla:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Split + circles', {}), V('v2', 'Split + photo', {img:'photo'})],
    closing:[V('v1', 'Split + contacts', {}), V('v2', 'Photo split + contacts', {img:'photo'})],
    agenda:[V('v1', 'Tangerine half + tickets', {fn:agendaA}), V('n2', 'Checker', {body:checker}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1})],
    text:[V('v1', 'White statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Checker', {body:checker}), V('n2', 'Tickets', {body:tickets, cols:1}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + tickets', {body:tickets, img:'left', cols:1})],
    p3:[V('v1', 'Checker', {body:checker}), V('n2', 'Half and half', {body:halves}), V('n3', 'Tickets', {body:tickets, cols:1}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + tickets', {body:tickets, img:'left', cols:1})],
    p4:[V('v1', 'Half and half', {body:halves}), V('n2', 'Checker', {body:checker}), V('n3', 'Tickets', {body:tickets}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + checker', {body:checker, img:'left', cols:2})],
    p56:[V('v1', 'Checker', {body:checker}), V('n2', 'Tickets', {body:tickets}), V('n3', 'Half and half', {body:halves}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + tickets', {body:tickets, img:'left', cols:1})],
    p78:[V('v1', 'Tickets', {body:tickets, max:8}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1, max:8})],
    stats:[V('v1', 'Split blocks', {body:splitStats}), V('n2', 'Progress rings', {body:'statRings', track:'#F1DDC7'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Split blocks + image right', {body:splitStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Flags', {body:flags}), V('n2', 'Tickets', {body:tickets}), V('n3', 'Checker', {body:checker}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1})],
    quote:[V('v1', 'Split quote', {}), V('v2', 'Split quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* "Cycle" layout: each text now sits at the height of its own icon. Before, the left column was filled from the top,
   so with 3 points the third text stood top-left while its icon was bottom-left and the bottom-left stayed empty. */
(function(){
var D = window.DSTE; if (!D || !D.B || !D.B.xCycle || !D.K2 || D.__cycleFix) return; D.__cycleFix = 1;
var K = D.K2, u = D.u;
D.B.xCycle = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var R = Math.min(Z.h * 0.4, 170), cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, d = Math.min(62, R * 0.55);
  D.ring(C, cx, cy, R, {color:u.mix(P.bg, P.text, 0.25), lw:3});
  C.ell(cx - R * 0.58, cy - R * 0.58, R * 1.16, R * 1.16, {fill:P.accent});
  C.text(cx - R * 0.5, cy - R * 0.5, R, R, String(C.s.title || '').split(/\s+/).slice(0, 4).join(' '), {font:'head', fit:{max:20, min:11}, color:P.onAccent || '#FFFFFF', align:'c', va:'m', maxLines:3, lh:1.1});
  var left = Math.ceil(n / 2), sw = (Z.w - R * 2 - d - 80) / 2, rows = left, gy = 10, rh = (Z.h - gy * (rows - 1)) / rows;
  var F = K.fitHD(C, it, sw, rh * 0.42, rh, {hMax:18, dMax:13.5, hLines:1});
  var pts = it.map(function(t, k){ return K.pol(cx, cy, R, -90 + k * 360 / n); });
  /* one row slot per text, chosen by the height of its icon; texts of one side never share a slot */
  var slot = [];
  ['r', 'l'].forEach(function(side){
    var ks = []; it.forEach(function(t, k){ if ((k < left ? 'r' : 'l') === side) ks.push(k); });
    ks.sort(function(a, b){ return pts[a][1] - pts[b][1]; });
    var used = -1;
    ks.forEach(function(k, j){
      var want = Math.round((pts[k][1] - Z.y - rh / 2) / (rh + gy));
      want = Math.max(used + 1, Math.min(rows - (ks.length - j), want));
      slot[k] = want; used = want;
    });
  });
  it.forEach(function(t, k){
    var p = pts[k], c = K.col(C, k);
    C.ell(p[0] - d / 2 - 4, p[1] - d / 2 - 4, d + 8, d + 8, {fill:P.bg});
    C.ell(p[0] - d / 2, p[1] - d / 2, d, d, {fill:c});
    K.icon(C, p[0] - d * 0.24, p[1] - d * 0.24, d * 0.48, t, K.on(c));
    var side = k < left ? 'r' : 'l', y = Z.y + slot[k] * (rh + gy), x = side === 'r' ? Z.x + Z.w - sw : Z.x;
    C.text(side === 'r' ? x - 34 : x + sw + 8, y, 26, 22, u.pad2(k + 1), {font:'head', size:15, wt:800, color:c, align:'c', lh:1, caps:false, ls:0});
    K.hd(C, x, y, sw, rh, t, F, {align:side === 'r' ? 'l' : 'r'});
  });
};
})();

}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: hexforge */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 47 - Hex Forge : light steel page, 3D gears and hex prisms, crimson accent, glass plates */
var RD = '#E02D3C', ST = '#2A3442', INK = '#18202B', MUT = '#586474', LT = '#E9EDF2';
var P0 = {bg:LT, text:INK, title:INK, muted:MUT, accent:RD, accent2:ST, card:'#FFFFFF', line:'#C7D0DB', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[RD, ST, '#F28A93', '#8A97A8', '#B01E2B', '#C7D0DB'],
  header:{kind:'left', top:58, tMax:48, tMin:26, sMax:17, sMin:13, kicker:function(C){ return (C.cover.company || '') + '  //  ' + pad2(C.i + 1); }, kc:RD, kGap:26, kSize:12},
  card:{kind:'glass', r:6, fill:'#FFFFFF', op:0.7, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.07, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:ST, dark:true, tc:'#FFFFFF', mc:'#C7D0DB'}},
  icon:{kind:'square', bg:RD, fg:'#FFFFFF', r:4}, num:{kind:'big', fg:RD},
  conc:{kind:'bar', barC:RD, label:'Result', lc:RD, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:6, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#C7D0DB'], [100, '#8A97A8']]}}
};
function hexP(cx, cy, r){ var a = r * 0.866; return [[cx, cy - r], [cx + a, cy - r / 2], [cx + a, cy + r / 2], [cx, cy + r], [cx - a, cy + r / 2], [cx - a, cy - r / 2]]; }
/* hexagonal prism: top face plus two visible side faces */
function prism(C, cx, cy, r, d, c){
  var a = r * 0.866;
  K.poly(C, [[cx - a, cy + r / 2], [cx, cy + r], [cx, cy + r + d], [cx - a, cy + r / 2 + d]], {fill:c[1]});
  K.poly(C, [[cx + a, cy + r / 2], [cx, cy + r], [cx, cy + r + d], [cx + a, cy + r / 2 + d]], {fill:c[2]});
  K.poly(C, hexP(cx, cy, r), {fill:c[0]});
}
var GI = 0;
function gearP(cx, cy, r, n, rot, ky){
  var p = [], w = Math.PI / n;
  for (var k = 0; k < n; k++) { var a = rot + k * 2 * Math.PI / n; [[0.8, -w * 0.64], [1, -w * 0.34], [1, w * 0.34], [0.8, w * 0.64]].forEach(function(q){ p.push([cx + Math.cos(a + q[1]) * r * q[0], cy + Math.sin(a + q[1]) * r * q[0] * ky]); }); }
  return p;
}
/* 3D gear seen at a tilt: a thick rim, a recessed web with holes, a raised round hub and a bore */
function gear(C, cx, cy, r, n, c, o){
  var hub = typeof o === 'string' ? o : ((o && o.hub) || '#FFFFFF'), solid = o && o.solid, rot = o && o.rot != null ? o.rot : (GI * 0.53 + cx * 0.011 + r * 0.02), ky = 0.84, d = Math.max(7, r * 0.2), L = Math.max(3, Math.round(d / 2.5));
  K.poly(C, gearP(cx + d * 0.5, cy + d * 1.5, r, n, rot, ky), {fill:'#18202B', op:0.14});
  for (var q = L; q >= 1; q--) K.poly(C, gearP(cx, cy + d * q / L, r, n, rot, ky), {fill:c[1]});
  K.poly(C, gearP(cx, cy, r, n, rot, ky), {fill:c[0]});
  C.ell(cx - r * 0.68, cy - r * 0.68 * ky, r * 1.36, r * 1.36 * ky, {grad:{ang:90, stops:[[0, c[1]], [100, c[0]]]}});
  C.ell(cx - r * 0.68, cy - r * 0.68 * ky, r * 1.36, r * 1.36 * ky, {line:'#FFFFFF', lop:0.35, lw:1});
  for (var h = 0; h < 5; h++) { var a = rot + 0.4 + h * 2 * Math.PI / 5, hx = cx + Math.cos(a) * r * 0.49, hy = cy + Math.sin(a) * r * 0.49 * ky; C.ell(hx - r * 0.1, hy - r * 0.1 * ky, r * 0.2, r * 0.2 * ky, {fill:'#11161D', op:0.55}); C.ell(hx - r * 0.1, hy - r * 0.1 * ky + r * 0.045, r * 0.2, r * 0.2 * ky - r * 0.045, {fill:c[1]}); }
  C.ell(cx - r * 0.34, cy - r * 0.34 * ky, r * 0.68, r * 0.68 * ky, {fill:c[1]});
  C.rect(cx - r * 0.34, cy - d * 0.4, r * 0.68, d * 0.4, {fill:c[1]});
  C.ell(cx - r * 0.34, cy - d * 0.4 - r * 0.34 * ky, r * 0.68, r * 0.68 * ky, {grad:{ang:90, stops:[[0, '#FFFFFF'], [100, hub]]}});
  if (!solid) { C.ell(cx - r * 0.15, cy - d * 0.4 - r * 0.15 * ky, r * 0.3, r * 0.3 * ky, {fill:'#11161D'}); C.ell(cx - r * 0.15, cy - d * 0.4 - r * 0.15 * ky + r * 0.07, r * 0.3, r * 0.3 * ky - r * 0.07, {fill:'#3A4654'}); }
}
function plate(C, x, y, w, h, o){
  o = o || {};
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#364252'], [100, '#1B232E']]}, r:6, shadow:0.14}); C.rect(x, y, w, 4, {fill:RD}); return {tc:'#FFFFFF', mc:'#C7D0DB', ac:'#FF6B77'}; }
  C.rect(x, y, w, h, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.5]]}, line:'#FFFFFF', lop:1, lw:1.5, r:6, shadow:0.07});
  if (o.bolts !== false) [[x + 12, y + 12], [x + w - 12, y + 12], [x + 12, y + h - 12], [x + w - 12, y + h - 12]].forEach(function(p){ K.poly(C, hexP(p[0], p[1], 4), {fill:'#B3BECB'}); });
  return {tc:INK, mc:MUT, ac:RD};
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  K.poly(C, hexP(M + 8, y + 8, 9), {fill:RD});
  C.text(M + 28, y, 500, 16, f.website || f.company || '', {font:'body', size:11.5, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:14, wt:700, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#F3F5F8'], [100, '#DCE2EA']]}}); GI = C.i || 0;
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { gear(C, W - 86, 62, 56, 10, ['#CBD3DD', '#98A5B4'], '#EEF1F5'); gear(C, W - 176, 50, 30, 8, ['#4A5869', '#232C38'], '#DDE3EA'); }
  else if (k === 1) { prism(C, W - 70, 70, 54, 16, ['#F06A75', '#C4232F', '#9C1924']); prism(C, W - 150, 26, 30, 10, ['#465466', '#2A3442', '#1B232E']); }
  else { gear(C, W - 60, 40, 80, 10, ['#CBD3DD', '#AEB9C6'], '#EEF1F5'); gear(C, W - 170, 88, 44, 8, ['#F06A75', '#C4232F'], '#EEF1F5'); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* honeycomb list: a 3D hex badge beside every point */
function honey(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 140), hr = Math.min(32, rh * 0.3), tw = cw - hr * 2 - 26;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx) + (cols === 1 && r % 2 ? 0 : 0), y = y0 + r * rh, cy = y + rh / 2 - 4, red = k % 2 === 0;
    prism(C, x + hr, cy, hr, hr * 0.3, red ? ['#F0505D', '#C4232F', '#9C1924'] : ['#465466', '#2A3442', '#1B232E']);
    C.text(x, cy - hr, hr * 2, hr * 2, pad2(k + 1), {font:'head', size:hr * 0.62, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + hr * 2 + 26, y + Math.max(8, (rh - bh) / 2), tw, rh - 8, t, F, {tc:INK, mc:MUT});
  });
}
/* bolted glass plates with an icon tab */
function plates(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26, ic = 44, side = rows > 1, tw = side ? cw - pad * 2 - ic - 16 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - ic - 18, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 18) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = plate(C, x, y, cw, ch2, {hot:hot});
    K.poly(C, hexP(x + pad + ic / 2, y + pad + ic / 2, ic / 2), {fill:RD});
    C.icon(x + pad + ic / 2 - 10, y + pad + ic / 2 - 10, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), '#FFFFFF');
    if (side) K.hd(C, x + pad + ic + 16, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
    else K.hd(C, x + pad, y + pad + ic + 18, tw, ch2 - pad * 2 - ic - 18, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* gear train: meshed 3D gears, one per step, with the text under each */
function train(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760 || Z.h < 300) return honey(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.5, Z.h * 0.24, 92), tw = cw - 26, gy = Z.y + r + 8;
  var F = K.fitHD(C, it, tw, 46, Z.h - r * 2 - 50, {hMax:19, dMax:14.5, hMin:12, dMin:10.5});
  for (var k = n - 1; k >= 0; k--) { var cx = Z.x + cw * (k + 0.5), red = k % 2 === 0; gear(C, cx, gy + (k % 2 ? r * 0.14 : 0), r, 10, red ? ['#F0505D', '#B01E2B'] : ['#4A5869', '#232C38'], {hub:red ? '#FBD0D4' : '#C9D2DC', solid:true, rot:(C.i || 0) * 0.41 + (k % 2 ? Math.PI / 10 : 0)}); }
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), y = gy + (k % 2 ? r * 0.14 : 0);
    C.text(cx - r * 0.38, y - r * 0.2 - r * 0.33, r * 0.76, r * 0.66, String(k + 1), {font:'head', size:r * 0.36, wt:700, color:INK, align:'c', va:'m', lh:1});
    K.hd(C, cx - tw / 2, Z.y + r * 2 + 46, tw, Z.h - r * 2 - 46, t, F, {tc:INK, mc:MUT, align:'c'});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: hex prisms of growing depth */
function hexStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.42, Z.h * 0.25, 104), vals = st.map(sv), labs = st.map(sl), tw = cw - 26, dmax = Math.min(70, Z.h * 0.16), base = Z.y + r * 2 + dmax + 6;
  var vs = C.groupFit(vals, r * 1.3, r * 0.7, {font:'head', max:50, min:18, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.y + Z.h - base - 70, {font:'body', max:13.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), d = dmax * (0.3 + 0.7 * (n > 1 ? k / (n - 1) : 1)), cy = base - d - r, red = k === n - 1;
    prism(C, cx, cy, r, d, red ? ['#F0505D', '#C4232F', '#9C1924'] : ['#FFFFFF', '#C7D0DB', '#AEB9C6']);
    C.text(cx - r * 0.75, cy - r * 0.4, r * 1.5, r * 0.8, vals[k], {font:'head', size:vs, wt:700, color:red ? '#FFFFFF' : RD, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + 18, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + 24 + le._h, tw, Z.y + Z.h - base - 24 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  plate(C, Z.x, Z.y, Z.w, Z.h, {}); C.rect(Z.x, Z.y + 30, 5, 70, {fill:RD});
  var pad = 46, y = Z.y + pad - 10, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, wt:700, color:RD, lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 12; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; prism(C, x + 12, y + 10, 12, 4, ['#F0505D', '#C4232F', '#9C1924']); C.text(x + 36, y, 460, 24, f.company, {font:'head', size:17, wt:700, color:INK, va:'m', lh:1, caps:true, ls:0.06, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function machine(C, cx, cy, s){
  gear(C, cx + s * 0.9, cy - s * 0.55, s * 0.62, 10, ['#4A5869', '#232C38'], '#DDE3EA');
  gear(C, cx - s * 0.2, cy + s * 0.1, s, 14, ['#F0505D', '#B01E2B'], '#FBE3E5');
  gear(C, cx + s * 0.95, cy + s * 0.95, s * 0.46, 8, ['#CBD3DD', '#98A5B4'], '#F3F5F8');
  prism(C, cx - s * 1.15, cy - s * 0.95, s * 0.3, s * 0.12, ['#FFFFFF', '#C7D0DB', '#AEB9C6']);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) { K.poly(C, hexP(960, 360, 300), {fill:'#FFFFFF'}); D.imgBox(C, 760, 160, 400, 400, {r:6}); prism(C, 760, 560, 54, 18, ['#F0505D', '#C4232F', '#9C1924']); gear(C, 1170, 170, 60, 10, ['#4A5869', '#232C38'], '#DDE3EA'); }
  else machine(C, 960, 350, 170);
  var tw = photo ? 600 : 620;
  C.rect(72, 176, 60, 6, {fill:RD});
  C.text(72, 200, tw, 250, C.s.title, {font:'head', fit:{max:84, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 462, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  |  ');
  if (who) { var pw = Math.min(tw, who.length * 8.4 + 50); C.rect(72, 570, pw, 46, {fill:ST, r:4}); C.rect(72, 570, 6, 46, {fill:RD}); C.text(72 + 26, 570, pw - 44, 46, who, {font:'body', size:14, wt:600, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(72, 640, 500, 18, f.website, {font:'body', size:12.5, wt:600, color:RD, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) { D.imgBox(C, 760, 130, 440, 440, {r:6}); gear(C, 770, 570, 60, 10, ['#F0505D', '#B01E2B'], '#FBE3E5'); } else machine(C, 980, 340, 150);
  C.text(72, 130, 620, 190, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:36}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 336, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 430 + k * 62;
    prism(C, 72 + 22, y + 20, 22, 6, k % 2 ? ['#465466', '#2A3442', '#1B232E'] : ['#F0505D', '#C4232F', '#9C1924']); C.icon(72 + 12, y + 10, 20, c.icon, '#FFFFFF');
    C.text(72 + 62, y, 520, 46, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  gear(C, 230, 520, 130, 12, ['#F0505D', '#B01E2B'], '#FBE3E5'); gear(C, 400, 420, 60, 8, ['#4A5869', '#232C38'], '#DDE3EA');
  C.rect(72, 110, 60, 6, {fill:RD});
  var te = C.text(72, 132, 400, 200, C.s.title || 'Agenda', {font:'head', fit:{max:80, min:36}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'t', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 132 + te._h + 12, 380, 70, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  plate(C, 530, 70, W - 530 - 72, H - 170, {});
  honey(C, {x:566, y:92, w:W - 566 - 108, h:H - 214}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RD, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 640 : W - 144;
  if (photo) D.imgBox(C, 770, 100, 438, 500, {r:6});
  plate(C, x, 100, w, 500, {});
  prism(C, x + 76, 150, 36, 10, ['#F0505D', '#C4232F', '#9C1924']); C.text(x + 40, 130, 72, 60, '”', {font:'head', size:64, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 232, w - 92, 270, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:600, color:INK, lh:1.16, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 232 + qe._h + 24, w - 92, 24, bt, {font:'body', size:15, wt:700, color:RD, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'hexforge', name:'Hex Forge', tag:'Light steel · 3D gears',
  desc:'A light steel page with 3D gears, hexagonal prisms and bolted glass plates in crimson and graphite: engineered and precise for manufacturing, operations, engineering and process decks.',
  fonts:{head:'Rajdhani', body:'Barlow', headW:700, bodyW:400, headLh:1.04, gf:'Rajdhani:wght@500;600;700|Barlow:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', '3D gear machine', {}), V('v2', 'Photo on hexagon', {img:'photo'})],
    closing:[V('v1', 'Gears + hex contacts', {}), V('v2', 'Photo + hex contacts', {img:'photo'})],
    agenda:[V('v1', 'Gears + honeycomb list', {fn:agendaA}), V('n2', 'Bolted plates', {body:plates}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1})],
    text:[V('v1', 'Plate statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Bolted plates', {body:plates, hl:true}), V('n2', 'Gear train', {body:train}), V('n3', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p3:[V('v1', 'Gear train', {body:train}), V('n2', 'Bolted plates', {body:plates, hl:true}), V('n3', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p4:[V('v1', 'Bolted plates', {body:plates}), V('n2', 'Gear train', {body:train}), V('n3', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p56:[V('v1', 'Bolted plates', {body:plates}), V('n2', 'Honeycomb list', {body:honey}), V('n3', 'Gear train', {body:train}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + honeycomb', {body:honey, img:'left', cols:1})],
    p78:[V('v1', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1})],
    stats:[V('v1', 'Hex prisms', {body:hexStats}), V('n2', 'Progress rings', {body:'statRings', track:'#CBD3DD'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Hex prisms + image right', {body:hexStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gear train', {body:train}), V('n2', 'Honeycomb list', {body:honey}), V('n3', 'Bolted plates', {body:plates}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1})],
    quote:[V('v1', 'Plate quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* DS Template Scenario Layouts - every design template (not only the glass ones) gets the same set of "where does the
   text go / where does the photo go" layouts: one text panel left, right, centre, top, bottom or in a corner, over the
   page, over a full photo or beside a half photo. The panel, colours, fonts and marks are the template's own. */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || D.__scn) return; D.__scn = 1;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
function con(a, b){ return Math.abs(u.lum(a) - u.lum(b)); }
/* the text panel: the template's own card style, made solid enough to read on a photo */
function panel(C, R, onPhoto){
  var S = C.S.card || {}, P = C.P, k = S.kind || 'solid', r = Math.min(S.r != null ? S.r : 16, 28), base = null;
  if (k === 'glass') { if (!C.T.frost && onPhoto) C.rect(R.x, R.y, R.w, R.h, {fill:P.bg, op:0.55, r:r}); }
  else if (k === 'none' || k === 'rule' || (k === 'outline' && !S.fill) || (S.op != null && S.op < 0.9)) { base = P.bg; C.rect(R.x, R.y, R.w, R.h, {fill:P.bg, op:onPhoto ? 0.94 : 1, r:k === 'none' || k === 'rule' ? Math.min(r, 14) : r}); }
  var q = D.card(C, R.x, R.y, R.w, R.h, {});
  var fill = k === 'glass' ? (onPhoto ? P.bg : P.bg) : (base || S.fill || P.card || P.bg);
  var tc = q.tc || P.text, mc = q.mc || P.muted;
  if (base || k === 'glass') { tc = P.title || P.text; mc = P.muted || P.text; if (k === 'glass' && S.tc) { tc = S.tc; mc = S.mc || S.tc; } }
  var ac = P.accent; if (con(ac, fill) < 0.22) ac = (P.accent2 && con(P.accent2, fill) >= 0.22) ? P.accent2 : tc;
  var inset = (k === 'bar' ? (S.barW || 6) : 0);
  return {tc:tc, mc:mc, ac:ac, fill:fill, inset:inset, top:(k === 'top' ? (S.barW || 5) : 0)};
}
/* nothing stays blank: every area the panel leaves free gets a quiet tinted field with a graphic made from the slide's
   own topic - the icon of the slide in the middle and the icons of its points around it, joined by lines */
function zones(R, o){
  var pos = o.pos || 'l', Z = [], g = 24, L = 56, T0 = 56, RT = W - 56, B = H - 68;
  function z(x, y, w, h){ if (w >= 90 && h >= 90) Z.push({x:x, y:y, w:w, h:h}); }
  if (pos === 'l') z(R.x + R.w + g, R.y, RT - (R.x + R.w + g), R.h);
  else if (pos === 'r') z(L, R.y, R.x - g - L, R.h);
  else if (pos === 't') z(R.x, R.y + R.h + g, R.w, B - (R.y + R.h + g));
  else if (pos === 'b') z(R.x, T0, R.w, R.y - g - T0);
  else if (pos === 'c') { z(L, R.y, R.x - g - L, R.h); z(R.x + R.w + g, R.y, RT - (R.x + R.w + g), R.h); }
  else if (pos === 'tl') { z(R.x + R.w + g, T0, RT - (R.x + R.w + g), B - T0); z(R.x, R.y + R.h + g, R.w, B - (R.y + R.h + g)); }
  else if (pos === 'bl') { z(R.x + R.w + g, T0, RT - (R.x + R.w + g), R.y + R.h - T0); z(R.x, T0, R.w, R.y - g - T0); }
  else if (pos === 'tr') { z(L, T0, R.x - g - L, B - T0); z(R.x, R.y + R.h + g, R.w, B - (R.y + R.h + g)); }
  else if (pos === 'br') { z(L, T0, R.x - g - L, R.y + R.h - T0); z(R.x, T0, R.w, R.y - g - T0); }
  return Z;
}
function icons(C, n){
  var a = [], seen = {}, i, ic;
  for (i = 0; i < C.it.length && a.length < n; i++) { ic = D.iconFor(C.s, C.it[i].i, (C.it[i].h || '') + ' ' + (C.it[i].d || '')); if (!seen[ic]) { seen[ic] = 1; a.push(ic); } }
  var more = [C.s.title, C.s.subtitle, C.s.conclusion, 'idea vision', 'growth scale', 'goal target', 'data insight', 'team people'];
  for (i = 0; i < more.length && a.length < n; i++) { if (!more[i]) continue; ic = D.iconFor({}, 0, more[i]); if (!seen[ic]) { seen[ic] = 1; a.push(ic); } }
  return a;
}
function tile(C, cx, cy, s, ic, ac, r, strong){
  C.rect(cx - s / 2, cy - s / 2, s, s, {fill:u.mix(C.P.bg, ac, 0.1), r:Math.min(r, s * 0.28)});      /* hides the joining line behind the tile */
  C.rect(cx - s / 2, cy - s / 2, s, s, {fill:ac, op:strong ? 0.26 : 0.16, r:Math.min(r, s * 0.28)});
  C.icon(cx - s * 0.29, cy - s * 0.29, s * 0.58, ic, ac);
}
function graphic(C, F, ac, r, plain){
  C.rect(F.x, F.y, F.w, F.h, {fill:ac, op:0.1, r:r});
  var wide = F.w >= F.h * 1.5, tall = F.h >= F.w * 1.5, i;
  if (plain) {                                                    /* cover, closing, quote: calm stepped bars */
    var n = wide ? 5 : 3, g = 14, p = Math.min(34, Math.min(F.w, F.h) * 0.16), bw = wide ? (F.w - p * 2 - g * (n - 1)) / n : F.w - p * 2, bh = wide ? F.h - p * 2 : (F.h - p * 2 - g * (n - 1)) / n;
    for (i = 0; i < n; i++) C.rect(F.x + p + (wide ? i * (bw + g) : 0), F.y + p + (wide ? 0 : i * (bh + g)), bw, bh, {fill:ac, op:0.1 + 0.06 * i, r:Math.min(r, 12)});
    var sz = Math.min(F.w, F.h) * (wide || tall ? 0.42 : 0.3);
    C.icon(F.x + (F.w - sz) / 2, F.y + (F.h - sz) / 2, sz, D.iconFor(C.s, 0, (C.s.title || '') + ' ' + (C.s.subtitle || '')), ac);
    return;
  }
  if (wide || tall) {                                             /* a chain of the points' icons */
    var len = wide ? F.w : F.h, th = wide ? F.h : F.w, m = Math.max(3, Math.min(5, C.it.length || 3)), ts = Math.min(th * 0.56, 104, (len - 40) / m * 0.7), ic = icons(C, m), step = (len - 40 - ts) / Math.max(1, m - 1), c0 = 20 + ts / 2;
    if (wide) C.line(F.x + c0, F.y + F.h / 2, F.x + c0 + step * (m - 1), F.y + F.h / 2, {color:ac, lw:2, op:0.45});
    else C.line(F.x + F.w / 2, F.y + c0, F.x + F.w / 2, F.y + c0 + step * (m - 1), {color:ac, lw:2, op:0.45});
    for (i = 0; i < m; i++) tile(C, wide ? F.x + c0 + step * i : F.x + F.w / 2, wide ? F.y + F.h / 2 : F.y + c0 + step * i, ts, ic[i] || ic[0], ac, r, i === 0);
    return;
  }
  var cx = F.x + F.w / 2, cy = F.y + F.h / 2, mn = Math.min(F.w, F.h), cs = mn * 0.34, ss = mn * 0.19, k = Math.max(2, Math.min(4, C.it.length || 3)), all = icons(C, k + 1);
  var rx = F.w / 2 - ss / 2 - 22, ry = F.h / 2 - ss / 2 - 22, ang = k === 2 ? [-45, 135] : k === 3 ? [-90, 30, 150] : [-135, -45, 45, 135], pts = [];
  for (i = 0; i < k; i++) { var q = ang[i] * Math.PI / 180, d = Math.min(rx / Math.max(0.01, Math.abs(Math.cos(q))), ry / Math.max(0.01, Math.abs(Math.sin(q)))); d = Math.min(d, mn * 0.44); pts.push([cx + Math.cos(q) * d, cy + Math.sin(q) * d]); }
  for (i = 0; i < k; i++) C.line(cx, cy, pts[i][0], pts[i][1], {color:ac, lw:2, op:0.45});
  tile(C, cx, cy, cs, D.iconFor({}, 0, (C.s.title || '') + ' ' + (C.s.subtitle || '')), ac, r, true);
  for (i = 0; i < k; i++) tile(C, pts[i][0], pts[i][1], ss, all[i] || all[0], ac, r, false);
}
function field(C, R, o){
  var P = C.P, ac = P.accent2 && con(P.accent2, P.bg) > 0.12 ? P.accent2 : P.accent, r = Math.min((C.S.card || {}).r != null ? C.S.card.r : 16, 28);
  var plain = C.kind === 'cover' || C.kind === 'closing' || C.kind === 'quote';
  zones(R, o).forEach(function(F){ graphic(C, F, ac, r, plain); });
}
var RECTS = {l:[56, 56, 600, 596], r:[624, 56, 600, 596], t:[56, 48, 1168, 336], b:[56, 320, 1168, 332], c:[190, 76, 900, 556], tl:[56, 56, 680, 420], tr:[544, 56, 680, 420], bl:[56, 232, 680, 420], br:[544, 232, 680, 420], w:[56, 48, 1168, 604]};
function hit(e, B){
  if (e.x == null || e.w == null) return true;                  /* lines, paths: cannot tell, so they go */
  return !(e.x >= B.x + B.w || e.x + e.w <= B.x || e.y >= B.y + B.h || e.y + e.h <= B.y);
}
/* the template's own background decoration stays, except where the panel, the colour field or the photo will sit */
function clearUnder(C, boxes){
  C.els = C.els.filter(function(e){ for (var i = 0; i < boxes.length; i++) if (boxes[i] && hit(e, boxes[i])) return false; return true; });
}
function place(C, o){
  var ph = o.ph, a = RECTS[o.pos || 'l'] || RECTS.l, R = {x:a[0], y:a[1], w:a[2], h:a[3]};
  if (ph === 'left') R = {x:470, y:64, w:754, h:580}; else if (ph === 'right') R = {x:56, y:64, w:754, h:580}; else if (ph === 'top') R = {x:90, y:230, w:1100, h:422}; else if (ph === 'bottom') R = {x:90, y:56, w:1100, h:422};
  var IM = ph === 'full' ? {x:0, y:0, w:W, h:H} : ph === 'left' ? {x:0, y:0, w:600, h:H} : ph === 'right' ? {x:680, y:0, w:600, h:H} : ph === 'top' ? {x:0, y:0, w:W, h:350} : ph === 'bottom' ? {x:0, y:370, w:W, h:350} : null;
  clearUnder(C, [R, IM].concat(ph ? [] : zones(R, o)));
  if (ph === 'full') D.imgBox(C, 0, 0, W, H, {r:0});
  else if (ph === 'left') D.imgBox(C, 0, 0, 600, H, {r:0}); else if (ph === 'right') D.imgBox(C, 680, 0, 600, H, {r:0}); else if (ph === 'top') D.imgBox(C, 0, 0, W, 350, {r:0}); else if (ph === 'bottom') D.imgBox(C, 0, 370, W, 350, {r:0});
  else field(C, R, o);
  var q = panel(C, R, !!ph); q.R = R; return q;
}
function mark(C, x, y, q){ C.rect(x, y, 46, 5, {fill:q.ac}); }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function listIn(C, Z, q){
  var it = C.it.slice(0, 16), n = it.length; if (!n) return;
  var gx = 28, mxc = Math.max(1, Math.floor((Z.w + gx) / (236 + gx))), rw, cl;
  if (Z.w <= 860 && n <= 4) { cl = 1; rw = n; } else { rw = Math.ceil(n / mxc); cl = Math.ceil(n / rw); }
  var cw = (Z.w - gx * (cl - 1)) / cl, ch = Math.min(Z.h / rw, cl === 1 ? 104 : 190);
  if (C.kind === 'stats') {
    var vals = it.map(sv), labs = it.map(sl), vs = C.groupFit(vals, cw, ch * 0.5, {font:'head', max:54, min:20, maxLines:1}), ls = C.groupFit(labs, cw, Math.max(24, ch - vs * 1.15 - 10), {font:'body', max:15, min:11, lh:1.3, maxLines:3});
    it.forEach(function(t, k){
      var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
      C.text(x, y, cw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:q.ac, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, y + vs * 1.1 + 4, cw, Math.max(24, ch - vs * 1.15 - 10), labs[k], {font:'body', size:ls, wt:500, color:q.tc, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
    });
    return;
  }
  var simple = !it.some(function(t){ return t.h && t.d; });
  if (simple) {
    if (Z.w <= 860 && n <= 7) { cl = 1; rw = n; cw = Z.w; ch = Math.min(Z.h / rw, 76); }
    var tx = it.map(function(t){ return t.h || t.d || ''; }), fs = C.groupFit(tx, cw - 50, ch - 12, {font:'body', max:20, min:12, lh:1.25, maxLines:2});
    it.forEach(function(t, k){
      var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
      if (Math.floor(k / cl)) C.rect(x, y - 1, cw, 1, {fill:q.tc, op:0.18});
      C.text(x, y, 44, ch, pad2(k + 1), {font:'head', size:Math.min(20, fs + 2), wt:800, color:q.ac, va:'m', lh:1, caps:false});
      C.text(x + 50, y, cw - 50, ch, tx[k], {font:'body', size:fs, wt:600, color:q.tc, va:'m', lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:0} : {f:'content-plain', i:t.i}, maxLines:2});
    });
    return;
  }
  var nw = 44, tw = cw - nw, F = K.fitHD(C, it, tw, 30, ch - 14, {hMax:18, dMax:it.some(function(t){ return t.h; }) ? 14 : 17, hMin:11.5, dMin:10});
  it.forEach(function(t, k){
    var cc = k % cl, r = Math.floor(k / cl), x = Z.x + cc * (cw + gx), y = Z.y + r * ch, oy = cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8;
    if (r && cl === 1) C.rect(x, y - 1, cw, 1, {fill:q.tc, op:0.18});
    C.text(x, y + oy + 1, 40, 22, pad2(k + 1), {font:'head', size:Math.max(13, Math.min(18, F.hs || 14)), wt:800, color:q.ac, lh:1, caps:false});
    K.hd(C, x + nw, y + oy, tw, ch - 12, t, F, {tc:q.tc, mc:q.mc});
  });
}
function scene(C, o){
  o = o || C.o || {};
  var q = place(C, o), R = q.R, pad = Math.min(40, Math.max(28, R.w * 0.05)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad + q.top, bot = R.y + R.h - pad, wide = R.w > 860;
  mark(C, x, y, q); y += 22;
  var te = C.text(x, y, wide ? w * 0.7 : w, 100, C.s.title || '', {font:'head', fit:{max:wide ? 36 : 31, min:19}, color:q.tc, lh:1.1, ed:{f:'title'}, maxLines:2}); y += te._h + 6;
  if (C.s.subtitle) { var se = C.text(x, y, wide ? w * 0.7 : w, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:q.mc, lh:1.35, ed:{f:'subtitle'}, maxLines:2}); y += se._h + 6; }
  y += 12;
  if (C.kind === 'text') {
    var it = C.it[0]; if (!it) return;
    if (it.h) { var he = C.text(x, y, w, 60, it.h, {font:'head', fit:{max:24, min:15}, color:q.ac, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 10; }
    C.text(x, y, w, bot - y, it.d, {font:'body', fit:{max:it.h ? 21 : 24, min:13}, color:q.tc, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
    return;
  }
  if (C.s.conclusion && bot - y > 230) { bot -= 50; C.rect(x, bot + 12, w, 1, {fill:q.tc, op:0.22}); C.text(x, bot + 18, w, 32, C.s.conclusion, {font:'body', fit:{max:14, min:11}, wt:600, color:q.ac, va:'m', lh:1.25, ed:{f:'x-conc'}, maxLines:2}); }
  listIn(C, {x:x, y:y, w:w, h:bot - y}, q);
}
function quote(C, o){
  o = o || C.o || {};
  var q = place(C, o), R = q.R, by = C.it[0], pad = Math.min(54, Math.max(34, R.w * 0.06)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad, mid = (o.pos || 'c') === 'c' || o.pos === 't' || o.pos === 'b';
  C.text(mid ? x + w / 2 - 40 : x - 4, y - 16, 80, 80, '“', {font:'head', size:92, wt:800, color:q.ac, lh:1, align:mid ? 'c' : 'l', caps:false, ls:0}); y += 62;
  C.text(x, y, w, R.y + R.h - pad - y - (by ? 46 : 0), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 38 : 31, min:18}, wt:600, color:q.tc, lh:1.22, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:6});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, R.y + R.h - pad - 26, w, 26, bt, {font:'body', size:14, wt:600, color:q.ac, lh:1.1, align:mid ? 'c' : 'l', va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
function cover(C){
  var o = C.o || {}, f = C.cover, q = place(C, o), R = q.R, pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad + q.top, bot = R.y + R.h - pad, mid = o.pos === 'c';
  if (f.company) C.text(x, y, w, 22, f.company, {font:'body', size:13.5, wt:700, color:q.ac, align:mid ? 'c' : 'l', va:'m', lh:1, caps:true, ls:0.14, ed:{f:'tf-company'}, maxLines:1});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  '), th = bot - y - 40 - (who ? 50 : 0) - (C.s.subtitle ? 70 : 0);
  C.text(x, y + 40, w, Math.max(80, th), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 62 : 50, min:26}, color:q.tc, lh:1.06, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:R.h < 360 ? 2 : 4});
  if (C.s.subtitle) C.text(x, y + 48 + Math.max(80, th), w, 62, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:q.mc, lh:1.4, align:mid ? 'c' : 'l', ed:{f:'subtitle'}, maxLines:3});
  if (who) { if (!mid) C.rect(x, bot - 30, 34, 3, {fill:q.ac}); C.text(x + (mid ? 0 : 46), bot - 40, w - (mid ? 0 : 46), 24, who, {font:'body', size:14, wt:700, color:q.tc, lh:1, va:'m', align:mid ? 'c' : 'l', ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C){
  var o = C.o || {}, q = place(C, o), R = q.R, pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad + q.top, wide = R.w > 860, cs = D.contacts(C), tw = wide ? w * 0.52 : w;
  mark(C, x, y, q); y += 24;
  var te = C.text(x, y, tw, wide ? R.h - pad * 2 - 110 : 150, C.s.title || 'Thank You', {font:'head', fit:{max:wide ? 66 : 54, min:28}, color:q.tc, lh:1.06, va:wide ? 'm' : 't', ed:{f:'title'}, maxLines:2});
  var sy = wide ? R.y + R.h - pad - 70 : y + te._h + 10;
  if (C.s.subtitle) C.text(x, sy, tw, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:q.mc, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cx = wide ? x + w * 0.58 : x, cw = wide ? w * 0.42 : w, n = cs.length, rh = Math.min(56, (wide ? R.h - pad * 2 : R.y + R.h - pad - (sy + 80)) / Math.max(1, n)), cy = wide ? R.y + (R.h - rh * n) / 2 : R.y + R.h - pad - rh * n;
  cs.forEach(function(c, k){
    var yy = cy + k * rh;
    C.icon(cx, yy + rh / 2 - 11, 22, c.icon, q.ac);
    C.text(cx + 38, yy, cw - 38, rh, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:q.tc, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(cx, yy, cw, 1, {fill:q.tc, op:0.18});
  });
}
/* the layout list. few = slides with little text also get the corner panels; many = 9-16 points only get the wide panels */
function sc(fn, mode){
  function v(id, lbl, o){ o.fn = fn; o.scn = 1; if (o.ph === 'full' || o.ph === 'bottom' || o.pos === 'b' || o.pos === 'w' || o.pos === 'bl' || o.pos === 'br') o.foot = false; return V(id, lbl, o); }
  if (mode === 'many') return [
    v('s3', 'Text panel centre', {pos:'c'}), v('s15', 'Text panel full width', {pos:'w'}),
    v('s10', 'Full photo + text centre', {img:'photo', ph:'full', pos:'c'}), v('s16', 'Full photo + wide text panel', {img:'photo', ph:'full', pos:'w'}),
    v('s13', 'Photo on top + text below', {img:'photo', ph:'top'}), v('s14', 'Text above + photo below', {img:'photo', ph:'bottom'})
  ];
  var a = [
    v('s1', 'Text panel left', {pos:'l'}), v('s2', 'Text panel right', {pos:'r'}), v('s3', 'Text panel centre', {pos:'c'}),
    v('s4', 'Text panel on top', {pos:'t'}), v('s5', 'Text panel at the bottom', {pos:'b'}),
    v('s6', 'Full photo + text left', {img:'photo', ph:'full', pos:'l'}), v('s7', 'Full photo + text right', {img:'photo', ph:'full', pos:'r'}),
    v('s8', 'Full photo + text at the bottom', {img:'photo', ph:'full', pos:'b'}), v('s9', 'Full photo + text on top', {img:'photo', ph:'full', pos:'t'}),
    v('s10', 'Full photo + text centre', {img:'photo', ph:'full', pos:'c'}),
    v('s11', 'Photo left half + text', {img:'photo', ph:'left'}), v('s12', 'Photo right half + text', {img:'photo', ph:'right'}),
    v('s13', 'Photo on top + text below', {img:'photo', ph:'top'}), v('s14', 'Text above + photo below', {img:'photo', ph:'bottom'})
  ];
  if (mode === 'few') a = a.concat([
    v('s17', 'Full photo + text top-left', {img:'photo', ph:'full', pos:'tl'}), v('s18', 'Full photo + text top-right', {img:'photo', ph:'full', pos:'tr'}),
    v('s19', 'Full photo + text bottom-left', {img:'photo', ph:'full', pos:'bl'}), v('s20', 'Full photo + text bottom-right', {img:'photo', ph:'full', pos:'br'}),
    v('s21', 'Text panel top-left', {pos:'tl'}), v('s22', 'Text panel bottom-right', {pos:'br'})
  ]);
  return a;
}
var MODE = {agenda:'', text:'few', p2:'few', p3:'few', p4:'', p56:'', p78:'', p9:'many', stats:'few', timeline:''};
function extend(T){
  if (!T || !T.id || T.frost || T.__scnDone || T.noScenario) return;
  T.__scnDone = 1;
  var tv = T.variants = T.variants || {}, k;
  for (k in MODE) { var base = D.variantsFor(T, k).slice(); tv[k] = base.concat(sc(scene, MODE[k])); }
  tv.quote = D.variantsFor(T, 'quote').slice().concat(sc(quote, 'few'));
  tv.cover = D.variantsFor(T, 'cover').slice().concat(sc(null, 'few'));
  tv.closing = D.variantsFor(T, 'closing').slice().concat(sc(null, 'few'));
  delete T.__agx;
  var oc = T.cover, ol = T.closing;
  T.cover = function(C, v){ if (C.o && C.o.scn) return cover(C); return (oc || D.genericCover).apply(this, arguments); };
  T.closing = function(C, v){ if (C.o && C.o.scn) return closing(C); return (ol || D.genericClosing).apply(this, arguments); };
}
Object.keys(D.reg || {}).forEach(function(id){ extend(D.reg[id]); });
var reg = D.register;
D.register = function(T){ var r = reg.apply(this, arguments); try { extend(D.reg[T && T.id]); } catch (e) {} return r; };
D.scenarioLayouts = {extend:extend};
})();

}catch(e){if(window.console)console.error(e);}
try{

/* DesignStager template: stackglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 48 - Stack Glass : dark data-centre page, 3D isometric servers, chips and databases, glass rack units */
var BL = '#4D8DFF', MI = '#3DDBB4', INK = '#EEF4FF', MUT = '#9FB0CC';
var P0 = {bg:'#080C18', text:INK, title:INK, muted:MUT, accent:BL, accent2:MI, card:'#111B33', line:'#263759', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[BL, MI, '#9CC0FF', '#F5B84A', '#2E5FCC', '#C9D6EE'],
  header:{kind:'left', top:58, tMax:44, tMin:26, sMax:17, sMin:13, kicker:function(C){ return '> ' + (C.cover.company || 'deck').toLowerCase().replace(/\s+/g, '-') + ' / ' + pad2(C.i + 1); }, kc:MI, kGap:26, kSize:12},
  card:{kind:'glass', r:8, fill:'#FFFFFF', op:0.07, line:'#9CC0FF', lop:0.35, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:BL, dark:true, tc:'#FFFFFF', mc:'#DCE8FF'}},
  icon:{kind:'square', bg:BL, fg:'#FFFFFF', r:6}, num:{kind:'big', fg:BL},
  conc:{kind:'bar', barC:MI, label:'Output', lc:MI, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:8, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#1D3463'], [100, '#0C1530']]}}
};
function unit(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 8;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#5B9BFF'], [100, '#2C5FD6']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:175, stops:[[0, '#FFFFFF', 0.25], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#DCE8FF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.13], [100, '#FFFFFF', 0.03]]}, line:'#9CC0FF', lop:0.38, lw:1, r:r});
  return {tc:INK, mc:MUT, ac:BL};
}
/* isometric slab: top face and two sides */
function slab(C, cx, cy, s, h, c){
  var a = s * 0.866;
  K.poly(C, [[cx - a, cy], [cx, cy + s / 2], [cx, cy + s / 2 + h], [cx - a, cy + h]], {fill:c[1]});
  K.poly(C, [[cx + a, cy], [cx, cy + s / 2], [cx, cy + s / 2 + h], [cx + a, cy + h]], {fill:c[2]});
  K.poly(C, [[cx, cy - s / 2], [cx + a, cy], [cx, cy + s / 2], [cx - a, cy]], {fill:c[0]});
}
/* 3D server tower: stacked units with status lights and vents */
function server(C, cx, cy, s, n){
  var h = s * 0.32, gap = s * 0.1, a = s * 0.866;
  K.poly(C, [[cx, cy + s / 2 + h + 10], [cx + a * 1.25, cy + h + s * 0.12 + 10], [cx, cy - s * 0.4 + h + 40], [cx - a * 1.25, cy + h + s * 0.12 + 10]], {fill:'#000000', op:0.35});
  for (var k = 0; k < n; k++) {
    var y = cy - k * (h + gap);
    slab(C, cx, y, s, h, ['#3A4E7A', '#1B2848', '#111A32']);
    K.poly(C, [[cx - a * 0.86, y + h * 0.3 + s * 0.07], [cx - a * 0.66, y + h * 0.3 + s * 0.185], [cx - a * 0.66, y + h * 0.62 + s * 0.185], [cx - a * 0.86, y + h * 0.62 + s * 0.07]], {fill:k % 2 ? BL : MI});
    for (var q = 0; q < 4; q++) { var t0 = 0.2 + q * 0.18; K.poly(C, [[cx + a * t0, y + h * 0.3 + s * 0.5 * (1 - t0)], [cx + a * (t0 + 0.1), y + h * 0.3 + s * 0.5 * (1 - t0 - 0.1)], [cx + a * (t0 + 0.1), y + h * 0.7 + s * 0.5 * (1 - t0 - 0.1)], [cx + a * t0, y + h * 0.7 + s * 0.5 * (1 - t0)]], {fill:'#000000', op:0.45}); }
  }
  var ty = cy - (n - 1) * (h + gap);
  K.poly(C, [[cx, ty - s * 0.3], [cx + a * 0.6, ty], [cx, ty + s * 0.3], [cx - a * 0.6, ty]], {fill:BL, op:0.5});
}
/* 3D chip: a flat board with a raised glowing core and pins */
function chip(C, cx, cy, s){
  var a = s * 0.866;
  for (var q = -2; q <= 2; q++) { C.line(cx - a * (0.5 + q * 0.2) - a * 0.3, cy + s * (0.25 - q * 0.1) + s * 0.15 + 6, cx - a * (0.5 + q * 0.2), cy + s * (0.25 - q * 0.1) + 6, {color:MI, lw:2, op:0.8}); C.line(cx + a * (0.5 + q * 0.2) + a * 0.3, cy + s * (0.25 - q * 0.1) + s * 0.15 + 6, cx + a * (0.5 + q * 0.2), cy + s * (0.25 - q * 0.1) + 6, {color:BL, lw:2, op:0.8}); }
  slab(C, cx, cy, s, s * 0.12, ['#2B3C63', '#16213F', '#0E1730']);
  slab(C, cx, cy - s * 0.12, s * 0.55, s * 0.1, ['#6FA4FF', '#2C5FD6', '#1E46A8']);
  K.poly(C, [[cx, cy - s * 0.12 - s * 0.14], [cx + a * 0.28, cy - s * 0.12], [cx, cy - s * 0.12 + s * 0.14], [cx - a * 0.28, cy - s * 0.12]], {fill:'#FFFFFF', op:0.5});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.rect(M, y + 3, 8, 8, {fill:MI}); C.rect(M + 12, y + 3, 8, 8, {fill:BL}); C.rect(M + 24, y + 3, 8, 8, {fill:'#FFFFFF', op:0.3});
  C.text(M + 44, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 120, y, 120, 16, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:12, wt:700, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#0B1226'], [100, '#060912']]}});
  K.grid(C, 64, '#9CC0FF', 0.045);
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.9, W * 0.1, W * 0.6][k], [H * 0.95, H * 0.95, 0][k], 760, 480, BL, 0.26);
  D.glow(C, [W * 0.1, W * 0.9, W * 0.05][k], [0, 0, H * 0.6][k], 500, 360, MI, 0.14);
  if (kind === 'cover' || kind === 'closing') return;
  if (k === 0) server(C, W - 150, 92, 40, 3); else if (k === 1) chip(C, W - 160, 70, 64); else { slab(C, W - 150, 50, 40, 40, ['#6FA4FF', '#2C5FD6', '#1E46A8']); slab(C, W - 215, 84, 24, 24, ['#3A4E7A', '#1B2848', '#111A32']); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* rack: every point is a rack unit with status lights and vents */
function rack(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 10, rh = Math.min((Z.h - g * (n - 1)) / n, 124), lw = 92, vw = Z.w > 760 ? 86 : 0, pad = 20, tw = Z.w - lw - vw - pad * 2;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, 46) + 28); var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), hot = o.hl && k === 0, cc = unit(C, Z.x, y, Z.w, rh, {hot:hot, r:6});
    C.rect(Z.x + 16, y + rh / 2 - 13, 8, 8, {fill:hot ? '#FFFFFF' : MI}); C.rect(Z.x + 16, y + rh / 2 + 5, 8, 8, {fill:hot ? '#FFFFFF' : BL, op:hot ? 0.6 : 1});
    C.text(Z.x + 34, y, lw - 34, rh, 'U' + pad2(k + 1), {font:'head', size:Math.min(22, rh * 0.26), wt:700, color:cc.ac, va:'m', lh:1, caps:false});
    C.rect(Z.x + lw, y + 12, 1, rh - 24, {fill:'#FFFFFF', op:0.18});
    var bh = blockH(C, t, tw, F);
    K.hd(C, Z.x + lw + pad, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
    if (vw) for (var q = 0; q < 6; q++) C.rect(Z.x + Z.w - vw + q * 11, y + rh * 0.3, 4, rh * 0.4, {fill:'#FFFFFF', op:hot ? 0.35 : 0.12, r:2});
  });
}
/* chips: glass cards with pins along the top and bottom edges */
function chips(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 22, gy = 30, pin = 9, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - pin * 2 - gy * (rows - 1)) / rows, pad = 24, top = 40, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - top, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad * 2 + 4), tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + pin + Math.max(0, (Z.h - pin * 2 - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + gy), hot = o.hl && k === 0, np = Math.max(4, Math.floor((cw - 40) / 26));
    for (var q = 0; q < np; q++) { var px = x + 20 + q * ((cw - 40 - 8) / (np - 1)); C.rect(px, y - pin, 8, pin, {fill:q % 2 ? BL : MI, op:0.85}); C.rect(px, y + ch2, 8, pin, {fill:q % 2 ? MI : BL, op:0.85}); }
    var cc = unit(C, x, y, cw, ch2, {hot:hot, r:6});
    C.text(x + pad, y + pad - 4, 120, 24, 'IC-' + pad2(k + 1), {font:'head', size:13, wt:700, color:hot ? '#FFFFFF' : MI, lh:1, caps:false, ls:0.08});
    C.icon(x + cw - pad - 22, y + pad - 6, 22, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), cc.ac);
    K.hd(C, x + pad, y + pad + top - 8, tw, ch2 - pad * 2 - top + 8, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* network: a 3D core in the middle, the points wired to it from both sides */
function network(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 3 || Z.w < 800 || Z.h < 300) return chips(C, Z, o);
  var L = it.filter(function(t, k){ return k % 2 === 0; }), R = it.filter(function(t, k){ return k % 2 === 1; }), cwid = 190, cw = (Z.w - cwid) / 2 - 10, cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, pad = 20, tw = cw - pad * 2;
  var per = Math.max(L.length, R.length), g = 14, rh = (Z.h - g * (per - 1)) / per;
  var F = K.fitHD(C, it, tw, 30, rh - 24, {hMax:19, dMax:14.5, hMin:12.5, dMin:10.5});
  [L, R].forEach(function(side, si){
    var tot = rh * side.length + g * (side.length - 1), y0 = Z.y + (Z.h - tot) / 2;
    side.forEach(function(t, k){
      var x = si ? Z.x + Z.w - cw : Z.x, y = y0 + k * (rh + g), ey = y + rh / 2, ex = si ? x : x + cw;
      C.line(ex, ey, cx + (si ? 50 : -50), ey, {color:si ? BL : MI, lw:1.5, op:0.7}); C.line(cx + (si ? 50 : -50), ey, cx + (si ? 30 : -30), cy, {color:si ? BL : MI, lw:1.5, op:0.7});
      C.rect(ex - 4, ey - 4, 8, 8, {fill:si ? BL : MI});
      var cc = unit(C, x, y, cw, rh, {r:6}), bh = blockH(C, t, tw, F);
      K.hd(C, x + pad, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
    });
  });
  server(C, cx, cy + 34, 46, 3);
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: 3D database cylinders of growing height */
function drums(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, dw = Math.min(cw * 0.62, 190), eh = dw * 0.3, vals = st.map(sv), labs = st.map(sl), tw = cw - 26, lab = Math.min(120, Z.h * 0.34), hmax = Z.h - lab - eh;
  var vs = C.groupFit(vals, dw - 24, 60, {font:'head', max:46, min:18, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, lab - 56, {font:'body', max:13.5, min:10.5, lh:1.38});
  var base = Z.y + Z.h - lab;
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), bh = Math.max(vs + 26, hmax * (0.45 + 0.55 * (n > 1 ? k / (n - 1) : 1))), top = base - bh, hot = k === n - 1, c1 = hot ? '#5B9BFF' : '#2B3C63', c2 = hot ? '#2C5FD6' : '#141E3A', c3 = hot ? '#9CC0FF' : '#46598A';
    C.ell(cx - dw / 2, base - eh / 2, dw, eh, {fill:c2});
    C.rect(cx - dw / 2, top, dw, bh, {grad:{ang:90, stops:[[0, c1], [100, c2]]}});
    for (var q = 1; q < 3; q++) if (bh > 90 * q) C.ell(cx - dw / 2, top + bh * q / 3 - eh / 2, dw, eh, {line:'#FFFFFF', lop:0.18, lw:1});
    C.ell(cx - dw / 2, top - eh / 2, dw, eh, {fill:c3, line:'#FFFFFF', lop:0.35, lw:1});
    C.text(cx - dw / 2 + 12, top + eh / 2 + 4, dw - 24, vs + 14, vals[k], {font:'head', size:vs, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + eh / 2 + 14, tw, 46, labs[k], {font:'head', size:ls, wt:600, color:INK, align:'c', lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + eh / 2 + 20 + le._h, tw, Z.y + Z.h - base - eh / 2 - 20 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: build pipeline - stages joined by arrows, the last one live */
function pipeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rack(C, Z, o);
  var ar = 30, cw = (Z.w - ar * (n - 1)) / n, pad = 18, tw = cw - pad * 2, top = 54, ch = Math.min(Z.h, 320), y0 = Z.y + (Z.h - ch) * 0.3;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw - 22, 30, {font:'head', max:22, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - top - pad * 2, {font:'body', max:15, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + ar), last = k === n - 1, cc = unit(C, x, y0, cw, ch, {hot:last, r:6});
    C.rect(x + pad, y0 + 22, 12, 12, {fill:last ? '#FFFFFF' : MI});
    C.text(x + pad + 22, y0 + 14, tw - 22, 28, t.h || 'Stage ' + (k + 1), {font:'head', size:hs, wt:700, color:cc.tc, va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y0 + top - 2, tw, 1, {fill:'#FFFFFF', op:0.2});
    C.text(x + pad, y0 + top + 14, tw, ch - top - pad - 14, t.d, {font:'body', size:ds, color:last ? '#FFFFFF' : MUT, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    if (!last) { var ax = x + cw + 5, ay = y0 + ch / 2; C.rect(ax, ay - 1.5, ar - 16, 3, {fill:BL}); K.poly(C, [[ax + ar - 18, ay - 8], [ax + ar - 8, ay], [ax + ar - 18, ay + 8]], {fill:BL}); }
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  unit(C, Z.x, Z.y, Z.w, Z.h, {r:10}); C.rect(Z.x + 20, Z.y + 18, 10, 10, {fill:MI}); C.rect(Z.x + 36, Z.y + 18, 10, 10, {fill:BL}); C.rect(Z.x + 52, Z.y + 18, 10, 10, {fill:'#FFFFFF', op:0.3}); C.rect(Z.x, Z.y + 44, Z.w, 1, {fill:'#FFFFFF', op:0.18});
  var pad = 44, y = Z.y + 44 + 30, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:MI, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; slab(C, x + 12, y + 8, 13, 7, ['#6FA4FF', '#2C5FD6', '#1E46A8']); C.text(x + 36, y, 460, 24, f.company, {font:'head', size:15, wt:700, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function scene(C, cx, cy){
  K.poly(C, [[cx, cy - 60], [cx + 330, cy + 130], [cx, cy + 320], [cx - 330, cy + 130]], {fill:'#9CC0FF', op:0.06});
  K.poly(C, [[cx, cy - 20], [cx + 260, cy + 130], [cx, cy + 280], [cx - 260, cy + 130]], {fill:'#9CC0FF', op:0.07});
  server(C, cx - 120, cy + 90, 80, 4); server(C, cx + 60, cy + 30, 80, 5); chip(C, cx + 150, cy + 200, 76);
  slab(C, cx - 40, cy + 210, 34, 34, ['#7CF0D0', '#2CB894', '#1C8E72']);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) { unit(C, 700, 90, 508, 540, {r:10}); D.imgBox(C, 716, 134, 476, 480, {r:6}); C.rect(718, 106, 10, 10, {fill:MI}); C.rect(734, 106, 10, 10, {fill:BL}); C.rect(750, 106, 10, 10, {fill:'#FFFFFF', op:0.3}); }
  else scene(C, 960, 200);
  var tw = photo ? 570 : 600;
  C.text(72, 160, tw, 20, '> init presentation', {font:'head', size:14, wt:600, color:MI, lh:1, caps:false});
  C.text(72, 196, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 62 : 72, min:32}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 60); unit(C, 72, 566, pw, 48, {r:6}); C.rect(72 + 16, 566 + 19, 10, 10, {fill:MI}); C.text(72 + 38, 566, pw - 54, 48, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(72, 636, 500, 18, f.website, {font:'body', size:12.5, wt:500, color:BL, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) D.imgBox(C, 760, 120, 448, 480, {r:8}); else scene(C, 980, 190);
  C.text(72, 130, 620, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 336, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 426 + k * 62;
    unit(C, 72, y, 560, 52, {r:6}); C.rect(72 + 16, y + 21, 10, 10, {fill:k % 2 ? BL : MI}); C.icon(72 + 40, y + 16, 20, c.icon, INK);
    C.text(72 + 74, y, 470, 52, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  server(C, 240, 520, 84, 4);
  var te = C.text(72, 110, 400, 200, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 110 + te._h + 14, 380, 70, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  rack(C, {x:520, y:76, w:W - 520 - 72, h:H - 170}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BL, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 640 : W - 144;
  if (photo) D.imgBox(C, 770, 100, 438, 500, {r:8});
  unit(C, x, 100, w, 500, {r:10}); C.rect(x + 20, 118, 10, 10, {fill:MI}); C.rect(x + 36, 118, 10, 10, {fill:BL}); C.rect(x, 144, w, 1, {fill:'#FFFFFF', op:0.18});
  C.text(x + 44, 160, 100, 80, '“', {font:'head', size:110, wt:700, color:BL, lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 250, w - 92, 250, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:600, color:INK, lh:1.24, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 250 + qe._h + 24, w - 92, 24, bt, {font:'body', size:15, wt:600, color:MI, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'stackglass', name:'Stack Glass', tag:'Dark · 3D servers',
  desc:'A dark data-centre page with 3D isometric servers, chips and database drums, glass rack units and status lights: technical and modern for IT, cloud, security and software decks.',
  fonts:{head:'Red Hat Display', body:'Red Hat Text', headW:700, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Red+Hat+Display:wght@500;600;700;800|Red+Hat+Text:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', '3D server scene', {}), V('v2', 'Photo in glass window', {img:'photo'})],
    closing:[V('v1', '3D scene + contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Server + rack list', {fn:agendaA}), V('n2', 'Chips', {body:chips}), V('v2', 'Rack list + image right', {body:rack, img:'right'})],
    text:[V('v1', 'Terminal statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Chips', {body:chips, hl:true}), V('n2', 'Rack units', {body:rack}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + chips', {body:chips, img:'left', cols:1})],
    p3:[V('v1', 'Chips', {body:chips, hl:true}), V('n2', 'Rack units', {body:rack, hl:true}), V('n3', 'Network', {body:network}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + rack units', {body:rack, img:'left'})],
    p4:[V('v1', 'Network', {body:network}), V('n2', 'Chips', {body:chips}), V('n3', 'Rack units', {body:rack}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + chips', {body:chips, img:'left', cols:2})],
    p56:[V('v1', 'Chips', {body:chips}), V('n2', 'Network', {body:network}), V('n3', 'Rack units', {body:rack}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + rack units', {body:rack, img:'left'})],
    p78:[V('v1', 'Rack units', {body:rack, max:8}), V('v2', 'Rack units + image right', {body:rack, img:'right', max:8})],
    stats:[V('v1', 'Database drums', {body:drums}), V('n2', 'Progress rings', {body:'statRings', track:'#1E2C4D'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Drums + image right', {body:drums, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Pipeline', {body:pipeline}), V('n2', 'Rack units', {body:rack}), V('n3', 'Chips', {body:chips}), V('v2', 'Rack units + image right', {body:rack, img:'right'})],
    quote:[V('v1', 'Terminal quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || D.glassKit) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* DS Template Glass Kit - one frosted-glass layout system that a template fills with its own colours, fonts, glass shape and background scene */
D.glassKit = function(c){
  var TX = c.tx || '#FFFFFF', MU = c.mu || '#B9C0CC', AC = c.ac, BG = c.bg, R0 = c.r != null ? c.r : 20, CAPS = !!c.caps, LS = CAPS ? 0.03 : 0;
  var S = {
    margin:72, series:c.series || [AC, TX, c.ac2 || MU, '#7C8798', '#4B5563', '#D1D5DB'],
    header:{kind:'left', top:58, tMax:42, tMin:26, sMax:15, sMin:12, kicker:null},
    card:{kind:'glass', r:R0, fill:'#FFFFFF', op:0.08, line:c.rim || '#FFFFFF', lop:0.4, lw:1.2, shadow:0, dark:true, tc:TX, mc:MU,
          hl:{kind:'glass', fill:'#FFFFFF', op:0.16, dark:true, tc:TX, mc:MU}},
    icon:{kind:'plain', fg:AC}, num:{kind:'big', fg:AC},
    conc:{kind:'box', fill:'#FFFFFF', op:0.08, r:Math.min(R0, 22), line:c.rim || '#FFFFFF', lw:1.2, label:'Takeaway', lc:AC, tc:TX},
    footer:{fn:foot}, imgFramed:true, imgR:R0, bodyGap:26, imgPh:{grad:c.ph}
  };
  var U = {W:W, H:H, K:K, D:D, AC:AC, AC2:c.ac2, TX:TX, BG:BG};
  /* frosted glass: a dark veil (the layer that receives the blurred backdrop), a bright rim and a short shine on the top edge */
  function glass(C, x, y, w, h, r, o){
    o = o || {}; r = Math.min(r, h / 2, w / 2);
    var e = C.rect(x, y, w, h, {fill:c.veil || '#05070C', op:o.veil != null ? o.veil : (c.clear != null ? c.clear : 0.2), r:r}); e.frost = c.blur || 26;
    var f = C.rect(x, y, w, h, {fill:'#FFFFFF', op:c.tint != null ? c.tint : 0.08, line:c.rim || '#FFFFFF', lop:c.rimOp != null ? c.rimOp : 0.45, lw:1.2, r:r}); f.frost = 0;
    if (c.edge !== false) { var s = C.rect(x + r + 6, y + 1, Math.max(10, w * 0.4 - r), 1.5, {fill:'#FFFFFF', op:0.5}); s.frost = 0; }
  }
  function mark(C, x, y){
    var m = c.mark || 'bar';
    if (m === 'dot') { C.ell(x, y - 2, 9, 9, {fill:AC}); C.ell(x + 15, y - 2, 9, 9, {line:AC, lw:1.5}); }
    else if (m === 'tick') { C.rect(x, y - 4, 3, 16, {fill:AC}); C.rect(x, y - 4, 16, 3, {fill:AC}); }
    else if (m === 'line') C.rect(x, y + 1, 96, 2, {fill:AC});
    else C.rect(x, y, 46, 5, {fill:AC, r:R0 ? 3 : 0});
  }
  function num(C, x, y, k, s){
    var n = c.num || 'plain';
    if (n === 'box' && s < 20) { C.rect(x, y - 2, s * 1.9, s * 1.5, {fill:AC, r:3}); C.text(x, y - 2, s * 1.9, s * 1.5, pad2(k + 1), {font:'head', size:s * 0.9, wt:800, color:c.onAc || '#0A0C10', align:'c', va:'m', lh:1, caps:false}); }
    else if (n === 'ring' && s < 20) { C.ell(x, y - 4, s * 1.9, s * 1.9, {line:AC, lw:1.4}); C.text(x, y - 4, s * 1.9, s * 1.9, String(k + 1), {font:'head', size:s * 0.9, wt:700, color:AC, align:'c', va:'m', lh:1, caps:false}); }
    else C.text(x, y, 60, s * 1.2, pad2(k + 1), {font:'head', size:s, wt:800, color:AC, lh:1, caps:false});
  }
  /* the background scene stays quiet so the text leads: everything the scene draws is faded by the template's calm factor */
  function back(C, R, k){
    var n = C.els.length, q = c.calm != null ? c.calm : 0.5;
    try { c.scene(C, R, k || 0, U); } catch (e) {}
    for (var i = n; i < C.els.length; i++) {
      var e = C.els[i];
      if (e.grad && e.grad.stops) e.grad = {type:e.grad.type, ang:e.grad.ang, stops:e.grad.stops.map(function(s){ return [s[0], s[1], (s[2] != null ? s[2] : 1) * q]; })};
      else if (e.fill) e.op = (e.op != null ? e.op : 1) * q;
      if (e.line || e.stroke) e.lop = (e.lop != null ? e.lop : 1) * q;
    }
  }
  function brand(C, x, y){
    var f = C.cover; if (!f.company) return;
    C.rect(x, y + 4, 14, 14, {fill:AC, r:Math.min(R0, 7)});
    C.text(x + 26, y, 420, 22, f.company, {font:'head', size:13.5, wt:700, color:TX, va:'m', lh:1, caps:CAPS, ls:CAPS ? 0.1 : 0, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
  }
  function foot(C){
    var M = 72, y = H - 38, f = C.cover;
    C.rect(M, y + 2, 12, 12, {fill:AC, r:Math.min(R0, 6)});
    C.text(M + 24, y, 500, 16, f.company || f.website || '', {font:'head', size:12, wt:600, color:TX, lh:1, va:'m'});
    var e = C.rect(W - M - 62, y - 7, 62, 30, {fill:'#FFFFFF', op:0.1, line:c.rim || '#FFFFFF', lop:0.4, lw:1, r:Math.min(R0, 15)}); e.frost = 0;
    C.text(W - M - 62, y - 7, 62, 30, pad2(C.i + 1), {font:'head', size:12.5, wt:700, color:TX, align:'c', va:'m', lh:1});
  }
  function bg(C, kind){
    C.bg({fill:BG});
    if (kind === 'cover' || kind === 'closing' || kind === 'quote' || (C.o && C.o.fn)) return;
    back(C, {x:72, y:170, w:1136, h:450}, C.i || 0);
  }
  function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
  /* ---------- bodies made of several glass boxes ---------- */
  function cols(C, Z, o){
    var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
    var g = 20, off = c.stag === false ? 0 : Math.min(24, Z.h * 0.06), cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 50;
    var F = K.fitHD(C, it, tw, 54, Z.h - off - top - pad * 2, {hMax:20, dMax:15, hMin:13, dMin:10.5});
    var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
    var ch = Math.min(Z.h - off, top + bmx + pad * 2 + 8), y0 = Z.y + Math.max(0, (Z.h - off - ch) * 0.3);
    it.forEach(function(t, k){
      var x = Z.x + k * (cw + g), y = y0 + (k % 2 ? off : 0);
      glass(C, x, y, cw, ch, R0);
      C.text(x + pad, y + pad - 2, 80, 30, pad2(k + 1), {font:'head', size:24, wt:800, color:AC, lh:1, caps:false});
      K.hd(C, x + pad, y + pad + top - 8, tw, ch - top - pad * 2 + 8, t, F, {tc:TX, mc:MU});
    });
  }
  function rows(C, Z, o){
    var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
    var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 112), nw = 70, pad = 24, tw = Z.w - nw - pad * 2;
    var F = K.fitHD(C, it, tw, 30, rh - 20, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 14.5 : 19, hMin:12.5, dMin:10.5});
    var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
    it.forEach(function(t, k){
      var y = y0 + k * (rh + g);
      glass(C, Z.x, y, Z.w, rh, R0);
      C.text(Z.x + pad, y, nw - 10, rh, pad2(k + 1), {font:'head', size:Math.min(26, rh * 0.34), wt:800, color:AC, va:'m', lh:1, caps:false});
      K.hd(C, Z.x + pad + nw, y + Math.max(8, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:TX, mc:MU});
    });
  }
  function grid(C, Z, o){
    var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
    var cl = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cl = Math.min(cl, 2);
    var rw = Math.ceil(n / cl), g = 16, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = 20, tw = cw - pad * 2;
    var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 30, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
    var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
    var ch2 = Math.min(ch, bmx + pad * 2 + 36), tot = ch2 * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
    it.forEach(function(t, k){
      var r = Math.floor(k / cl), cc = k % cl, x = Z.x + cc * (cw + g), y = y0 + r * (ch2 + g);
      glass(C, x, y, cw, ch2, Math.min(R0, 20));
      num(C, x + pad, y + pad - 1, k, 14);
      K.hd(C, x + pad, y + pad + 28, tw, ch2 - pad * 2 - 28, t, F, {tc:TX, mc:MU});
    });
  }
  function sv(t){ return t.h || String(t.d).split(' ')[0]; }
  function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
  function statCards(C, Z, o){
    var st = C.it.slice(0, 4), n = st.length; if (!n) return;
    var cl = Z.w < 760 ? (n > 2 ? 2 : 1) : n, rw = Math.ceil(n / cl), g = 20, cw = (Z.w - g * (cl - 1)) / cl, ch = Math.min((Z.h - g * (rw - 1)) / rw, 300), pad = 26, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
    var vs = C.groupFit(vals, tw, ch * 0.4, {font:'head', max:64, min:22, maxLines:1});
    var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
    var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs * 1.1 - 110, {font:'body', max:14, min:10.5, lh:1.4});
    var tot = ch * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
    st.forEach(function(t, k){
      var r = Math.floor(k / cl), cc = k % cl, x = Z.x + cc * (cw + g), y = y0 + r * (ch + g);
      glass(C, x, y, cw, ch, R0);
      C.text(x + pad, y + pad, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:AC, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.rect(x + pad, y + pad + vs * 1.1 + 10, 40, 3, {fill:TX});
      var le = C.text(x + pad, y + pad + vs * 1.1 + 26, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:TX, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
      if (t.x) C.text(x + pad, y + pad + vs * 1.1 + 34 + le._h, tw, ch - (pad * 2 + vs * 1.1 + 34 + le._h), t.x, {font:'body', size:ds, color:MU, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
    });
  }
  function steps(C, Z, o){
    var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
    var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 18, tw = cw - pad * 2, st = c.stag === false ? 0 : Math.min(26, Z.h * 0.32 / (n - 1)), ch = Math.min(Z.h - st * (n - 1), 330);
    var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 34, {font:'head', max:26, min:13, maxLines:1});
    var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - 84, {font:'body', max:14.5, min:10.5, lh:1.4});
    if (!st) C.rect(Z.x, Z.y + 20, Z.w, 2, {fill:AC});
    it.forEach(function(t, k){
      var x = Z.x + k * (cw + g), y = Z.y + k * st + (st ? 0 : 44);
      if (!st) C.ell(x + pad - 4, Z.y + 12, 18, 18, {fill:AC});
      glass(C, x, y, cw, ch - (st ? 0 : 44), R0);
      C.text(x + pad, y + pad, tw, 34, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:AC, lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.rect(x + pad, y + pad + 42, 34, 3, {fill:TX});
      C.text(x + pad, y + pad + 58, tw, ch - (st ? 0 : 44) - pad * 2 - 58, t.d, {font:'body', size:ds, color:TX, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    });
  }
  function statement(C, Z, o){
    var it = C.it[0]; if (!it) return;
    glass(C, Z.x, Z.y, Z.w, Z.h, R0);
    var pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
    mark(C, Z.x + pad, y); y += 28;
    if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:AC, lh:1.12, caps:CAPS, ls:LS, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
    C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:TX, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
  }
  /* ---------- one glass panel that holds the whole slide: any side or corner, over the scene or over a photo ---------- */
  var RECTS = {l:[56, 56, 600, 596], r:[624, 56, 600, 596], t:[56, 48, 1168, 336], b:[56, 320, 1168, 332], c:[190, 76, 900, 556], tl:[56, 56, 680, 420], tr:[544, 56, 680, 420], bl:[56, 232, 680, 420], br:[544, 232, 680, 420]};
  function place(C, o){
    var ph = o.ph, a = RECTS[o.pos || 'l'] || RECTS.l, R = {x:a[0], y:a[1], w:a[2], h:a[3]};
    if (ph === 'left') R = {x:470, y:64, w:754, h:580}; else if (ph === 'right') R = {x:56, y:64, w:754, h:580}; else if (ph === 'top') R = {x:90, y:230, w:1100, h:422}; else if (ph === 'bottom') R = {x:90, y:56, w:1100, h:422};
    if (ph === 'full') D.imgBox(C, 0, 0, W, H, {r:0});
    else {
      back(C, R, (C.i || 0) + (o.pos === 'r' || o.pos === 'tr' ? 1 : 0));
      if (ph === 'left') D.imgBox(C, 0, 0, 600, H, {r:0}); else if (ph === 'right') D.imgBox(C, 680, 0, 600, H, {r:0}); else if (ph === 'top') D.imgBox(C, 0, 0, W, 350, {r:0}); else if (ph === 'bottom') D.imgBox(C, 0, 370, W, 350, {r:0});
    }
    glass(C, R.x, R.y, R.w, R.h, Math.min(R0 * 1.2, 34), {veil:ph ? (c.clearPh != null ? c.clearPh : 0.3) : null});
    return R;
  }
  function listIn(C, Z){
    var it = C.it.slice(0, 8), n = it.length; if (!n) return;
    var wide = Z.w > 860, cl = wide ? (n <= 4 ? n : Math.ceil(n / 2)) : (n > 4 && Z.w > 520 ? 2 : 1), rw = Math.ceil(n / cl), gx = 28, cw = (Z.w - gx * (cl - 1)) / cl, ch = Math.min(Z.h / rw, cl === 1 ? 104 : 190);
    if (C.kind === 'stats') {
      var vals = it.map(sv), labs = it.map(sl), vs = C.groupFit(vals, cw, ch * 0.5, {font:'head', max:54, min:20, maxLines:1}), ls = C.groupFit(labs, cw, Math.max(24, ch - vs * 1.15 - 10), {font:'body', max:15, min:11, lh:1.3, maxLines:3});
      it.forEach(function(t, k){
        var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
        C.text(x, y, cw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:AC, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
        C.text(x, y + vs * 1.1 + 4, cw, Math.max(24, ch - vs * 1.15 - 10), labs[k], {font:'body', size:ls, wt:500, color:TX, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
      });
      return;
    }
    var nw = 44, tw = cw - nw, F = K.fitHD(C, it, tw, 30, ch - 14, {hMax:18, dMax:it.some(function(t){ return t.h; }) ? 14 : 17, hMin:12, dMin:10.5});
    it.forEach(function(t, k){
      var cc = k % cl, r = Math.floor(k / cl), x = Z.x + cc * (cw + gx), y = Z.y + r * ch, oy = cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8;
      if (r && cl === 1) C.rect(x, y - 1, cw, 1, {fill:TX, op:0.22});
      num(C, x, y + oy + 2, k, 13);
      K.hd(C, x + nw, y + oy, tw, ch - 12, t, F, {tc:TX, mc:MU});
    });
  }
  function scene(C, o){
    o = o || C.o || {};
    var R = place(C, o), pad = Math.min(40, Math.max(28, R.w * 0.05)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, wide = R.w > 860;
    mark(C, x, y); y += 22;
    var te = C.text(x, y, wide ? w * 0.7 : w, 100, C.s.title || '', {font:'head', fit:{max:wide ? 36 : 31, min:19}, wt:700, color:TX, lh:1.1, caps:CAPS, ls:LS, ed:{f:'title'}, maxLines:2}); y += te._h + 6;
    if (C.s.subtitle) { var se = C.text(x, y, wide ? w * 0.7 : w, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:MU, lh:1.35, ed:{f:'subtitle'}, maxLines:2}); y += se._h + 6; }
    y += 12;
    if (C.s.conclusion && bot - y > 230) { bot -= 50; C.rect(x, bot + 12, w, 1, {fill:TX, op:0.25}); C.text(x, bot + 18, w, 32, C.s.conclusion, {font:'body', fit:{max:14, min:11}, wt:600, color:AC, va:'m', lh:1.25, ed:{f:'x-conc'}, maxLines:2}); }
    listIn(C, {x:x, y:y, w:w, h:bot - y});
  }
  function quote(C, v){
    var o = C.o || {}, R = place(C, o), by = C.it[0], pad = Math.min(54, Math.max(34, R.w * 0.06)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, mid = (o.pos || 'c') === 'c' || o.pos === 't' || o.pos === 'b';
    C.text(mid ? x + w / 2 - 40 : x - 4, y - 16, 80, 80, '“', {font:'head', size:92, wt:800, color:AC, lh:1, align:mid ? 'c' : 'l', caps:false, ls:0}); y += 62;
    C.text(x, y, w, R.y + R.h - pad - y - (by ? 46 : 0), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 38 : 31, min:18}, wt:600, color:TX, lh:1.22, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:6});
    if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, R.y + R.h - pad - 26, w, 26, bt, {font:'body', size:14, wt:600, color:AC, lh:1.1, align:mid ? 'c' : 'l', va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
  }
  function cover(C, v){
    var o = C.o || {}, f = C.cover, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, mid = o.pos === 'c';
    if (f.company) { if (mid) C.text(x, y, w, 22, f.company, {font:'head', size:13.5, wt:700, color:AC, align:'c', va:'m', lh:1, caps:true, ls:0.14, ed:{f:'tf-company'}, maxLines:1}); else brand(C, x, y); }
    var who = [f.presenter, f.designation].filter(Boolean).join('  ·  '), th = bot - y - 40 - (who ? 50 : 0) - (C.s.subtitle ? 70 : 0);
    C.text(x, y + 40, w, Math.max(80, th), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? (c.big ? 86 : 62) : (c.big ? 60 : 50), min:26}, wt:800, color:TX, lh:1.06, caps:CAPS, ls:LS, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:R.h < 360 ? 2 : 4});
    if (C.s.subtitle) C.text(x, y + 48 + Math.max(80, th), w, 62, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, align:mid ? 'c' : 'l', ed:{f:'subtitle'}, maxLines:3});
    if (who) { if (!mid) C.rect(x, bot - 30, 34, 3, {fill:AC}); C.text(x + (mid ? 0 : 46), bot - 40, w - (mid ? 0 : 46), 24, who, {font:'head', size:14, wt:700, color:TX, lh:1, va:'m', align:mid ? 'c' : 'l', ed:{f:'tf-presenter'}, maxLines:1}); }
  }
  function closing(C, v){
    var o = C.o || {}, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, wide = R.w > 860, cs = D.contacts(C), tw = wide ? w * 0.52 : w;
    mark(C, x, y); y += 24;
    var te = C.text(x, y, tw, wide ? R.h - pad * 2 - 110 : 150, C.s.title || 'Thank You', {font:'head', fit:{max:wide ? 66 : 54, min:28}, wt:800, color:TX, lh:1.06, caps:CAPS, ls:LS, va:wide ? 'm' : 't', ed:{f:'title'}, maxLines:2});
    var sy = wide ? R.y + R.h - pad - 70 : y + te._h + 10;
    if (C.s.subtitle) C.text(x, sy, tw, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
    var cx = wide ? x + w * 0.58 : x, cw = wide ? w * 0.42 : w, n = cs.length, rh = Math.min(56, (wide ? R.h - pad * 2 : R.y + R.h - pad - (sy + 80)) / Math.max(1, n)), cy = wide ? R.y + (R.h - rh * n) / 2 : R.y + R.h - pad - rh * n;
    cs.forEach(function(q, k){
      var yy = cy + k * rh;
      C.icon(cx, yy + rh / 2 - 11, 22, q.icon, AC);
      C.text(cx + 38, yy, cw - 38, rh, q.val, {font:'body', fit:{max:17, min:12}, wt:600, color:TX, va:'m', ed:q.i >= 0 ? {f:'x-part', i:q.i, p:1} : null, maxLines:1});
      if (k) C.rect(cx, yy, cw, 1, {fill:TX, op:0.2});
    });
  }
  function sc(fn, few){
    var a = [
      V('g1', 'Glass panel left', {fn:fn, pos:'l'}), V('g2', 'Glass panel right', {fn:fn, pos:'r'}), V('g3', 'Glass panel centre', {fn:fn, pos:'c'}),
      V('g4', 'Glass panel on top', {fn:fn, pos:'t'}), V('g5', 'Glass panel at the bottom', {fn:fn, pos:'b'}),
      V('f1', 'Full photo + glass left', {fn:fn, img:'photo', ph:'full', pos:'l'}), V('f2', 'Full photo + glass right', {fn:fn, img:'photo', ph:'full', pos:'r'}),
      V('f3', 'Full photo + glass at the bottom', {fn:fn, img:'photo', ph:'full', pos:'b'}), V('f4', 'Full photo + glass on top', {fn:fn, img:'photo', ph:'full', pos:'t'}),
      V('f5', 'Full photo + glass centre', {fn:fn, img:'photo', ph:'full', pos:'c'}),
      V('h1', 'Photo left half + glass', {fn:fn, img:'photo', ph:'left'}), V('h2', 'Photo right half + glass', {fn:fn, img:'photo', ph:'right'}),
      V('h3', 'Photo on top + glass below', {fn:fn, img:'photo', ph:'top'}), V('h4', 'Glass above + photo below', {fn:fn, img:'photo', ph:'bottom'})
    ];
    if (few) a = a.concat([
      V('c1', 'Full photo + glass top-left', {fn:fn, img:'photo', ph:'full', pos:'tl'}), V('c2', 'Full photo + glass top-right', {fn:fn, img:'photo', ph:'full', pos:'tr'}),
      V('c3', 'Full photo + glass bottom-left', {fn:fn, img:'photo', ph:'full', pos:'bl'}), V('c4', 'Full photo + glass bottom-right', {fn:fn, img:'photo', ph:'full', pos:'br'}),
      V('c5', 'Glass panel top-left', {fn:fn, pos:'tl'}), V('c6', 'Glass panel bottom-right', {fn:fn, pos:'br'})
    ]);
    return a;
  }
  /* cover / closing / quote: the same positions; the template's favourite one comes first */
  function own(first){
    var all = sc(null, true).map(function(v){ var o = {}; for (var k in v.o) if (k !== 'fn') o[k] = v.o[k]; return [v.lbl, o]; }), a = [], b = [];
    all.forEach(function(p){ ((p[1].pos || '') === first && !p[1].ph ? a : b).push(p); });
    var i = -1; b.some(function(p, k){ if (p[1].ph === 'full' && p[1].pos === first) { i = k; return true; } return false; });
    if (i >= 0) a.push(b.splice(i, 1)[0]);
    return a.concat(b).map(function(p, k){ return V(k < 2 ? 'v' + (k + 1) : 'g' + (k + 1), p[0], p[1]); });
  }
  var B3 = c.b3 === 'rows' ? rows : cols, B3b = c.b3 === 'rows' ? cols : rows, n3 = c.b3 === 'rows' ? ['Glass rows', 'Glass columns'] : ['Glass columns', 'Glass rows'];
  D.register({
    id:c.id, name:c.name, tag:c.tag, desc:c.desc, frost:true, fonts:c.fonts,
    pal:{bg:BG, text:TX, title:TX, muted:MU, accent:AC, accent2:c.ac2 || MU, card:c.card || '#161A22', line:c.line || '#2B313D', onAccent:c.onAc || '#0A0C10'},
    s:S, bg:bg, cover:cover, closing:closing, quote:quote,
    variants:{
      cover:own(c.cover || 'l'), closing:own(c.close || 'c'), quote:own(c.quote || 'c'),
      agenda:[V('v1', 'Glass rows', {body:rows}), V('n2', 'Glass grid', {body:grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene)),
      text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})].concat(sc(scene, true)),
      p2:[V('v1', n3[0], {body:B3}), V('n2', n3[1], {body:B3b}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
      p3:[V('v1', n3[0], {body:B3}), V('n2', n3[1], {body:B3b}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
      p4:[V('v1', c.b4 === 'grid' ? 'Glass grid' : 'Glass columns', {body:c.b4 === 'grid' ? grid : cols}), V('n2', 'Glass rows', {body:rows}), V('n3', c.b4 === 'grid' ? 'Glass columns' : 'Glass grid', {body:c.b4 === 'grid' ? cols : grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
      p56:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
      p78:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows, max:8}), V('v2', 'Glass grid + image right', {body:grid, img:'right', cols:2})].concat(sc(scene)),
      stats:[V('v1', 'Glass numbers', {body:statCards}), V('n2', 'Progress rings', {body:'statRings', track:c.line || '#2B313D'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + image right', {body:statCards, img:'right'}), V('v3', 'Image left + numbers', {body:statCards, img:'left'})].concat(sc(scene, true)),
      timeline:[V('v1', 'Glass steps', {body:steps}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene))
    }
  });
};
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 49 - Chevron Teal : white page, teal chevron bands that cut across the photo, pennant cards */
var TE = '#0E7C86', DK = '#0A4F5C', LT = '#7FD1D6', SAND = '#F4EDE2', INK = '#12343B', MUT = '#56707A';
var P0 = {bg:'#FFFFFF', text:INK, title:DK, muted:MUT, accent:TE, accent2:LT, card:'#F1F8F8', line:'#CFE3E5', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[TE, DK, LT, '#E0A458', '#9BB8BD', '#12343B'],
  header:{kind:'left', top:60, tMax:44, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'solid', r:10, fill:'#F1F8F8', line:null, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:TE, dark:true, tc:'#FFFFFF', mc:'#D9F1F2'}},
  icon:{kind:'square', bg:TE, fg:'#FFFFFF', r:8}, num:{kind:'big', fg:TE},
  conc:{kind:'bar', barC:TE, label:'Key point', lc:TE, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#B9DCDF'], [100, '#5FA9B0']]}}
};
/* a chevron band: a ">" shaped strip, tip at (tx, cy), arms going back to the left */
function chev(C, tx, cy, half, th, color, op){
  K.poly(C, [[tx - half - th, cy - half], [tx - half, cy - half], [tx, cy], [tx - half, cy + half], [tx - half - th, cy + half], [tx - th, cy]], {fill:color, op:op == null ? 1 : op});
}
/* the same strip pointing left: a "<" */
function chevL(C, tx, cy, half, th, color, op){
  K.poly(C, [[tx + half + th, cy - half], [tx + half, cy - half], [tx, cy], [tx + half, cy + half], [tx + half + th, cy + half], [tx + th, cy]], {fill:color, op:op == null ? 1 : op});
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  chev(C, x + 22, y + 11, 11, 7, light ? '#FFFFFF' : TE); chev(C, x + 12, y + 11, 11, 7, light ? '#BFE9EC' : LT);
  C.text(x + 34, y, 420, 22, f.company, {font:'body', size:13, wt:600, color:light ? '#FFFFFF' : INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 70, y = H - 36, f = C.cover;
  C.rect(M, y - 10, W - M * 2, 1, {fill:INK, op:0.12});
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  chev(C, W - M - 30, y + 8, 8, 5, LT); chev(C, W - M - 40, y + 8, 8, 5, TE);
  C.text(W - M - 26, y, 26, 16, pad2(C.i + 1), {font:'head', size:12, wt:700, color:DK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { chev(C, W + 40, 70, 150, 54, '#E3F3F4'); chev(C, W - 60, 70, 150, 26, '#C7E8EA'); }
  else if (k === 1) { chevL(C, -40, H - 90, 150, 54, '#E3F3F4'); chevL(C, 60, H - 90, 150, 26, '#C7E8EA'); }
  else { chev(C, W + 20, H - 110, 130, 46, '#E3F3F4'); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* pennant cards: a teal header that ends in a point, the text hangs below it */
function pennants(C, Z, o){
  var it = C.it.slice(0, o.max || 4), n = it.length; if (n < 2 || Z.w < 720) return tags(C, Z, o);
  var g = 20, cw = (Z.w - g * (n - 1)) / n, hh = 74, tip = 22, pad = 22, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, Z.h - hh - tip - pad * 2, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h, hh + tip + bmx + pad * 2 + 6), y0 = Z.y + Math.max(0, (Z.h - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), col = k % 2 ? DK : TE;
    C.rect(x, y0, cw, ch, {fill:'#F1F8F8', r:0});
    K.poly(C, [[x, y0], [x + cw, y0], [x + cw, y0 + hh], [x + cw / 2, y0 + hh + tip], [x, y0 + hh]], {fill:col});
    C.text(x + pad, y0, 70, hh, pad2(k + 1), {font:'head', size:30, wt:800, color:'#FFFFFF', va:'m', lh:1, caps:false, op:0.95});
    C.icon(x + cw - pad - 28, y0 + hh / 2 - 14, 28, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), '#FFFFFF');
    K.hd(C, x + pad, y0 + hh + tip + pad - 4, tw, ch - hh - tip - pad * 2 + 4, t, F, {tc:INK, mc:MUT});
  });
}
/* arrow tags: the heading sits on a tag that points at its explanation */
function tags(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 110), hasH = it.some(function(t){ return t.h; }), tgw = hasH ? Math.min(300, Z.w * 0.34) : 74, tip = Math.min(24, rh / 2), tw = Z.w - tgw - tip - 26;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), tgw - 62, rh - 14, {font:'head', max:19, min:12, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 12, {font:'body', max:hasH ? 15.5 : 20, min:10.5, lh:1.38});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), col = k % 2 ? DK : TE;
    C.rect(Z.x + tgw, y, Z.w - tgw, rh, {fill:'#F1F8F8'});
    K.poly(C, [[Z.x, y], [Z.x + tgw, y], [Z.x + tgw + tip, y + rh / 2], [Z.x + tgw, y + rh], [Z.x, y + rh]], {fill:col});
    C.text(Z.x + 16, y, 40, rh, pad2(k + 1), {font:'head', size:Math.min(22, rh * 0.3), wt:800, color:'#FFFFFF', va:'m', lh:1, caps:false, op:0.8});
    if (hasH) C.text(Z.x + 58, y, tgw - 62, rh, t.h || '', {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + tgw + tip + 14, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* tiles: a grid of light tiles with a teal corner wedge and an icon */
function tiles(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22, ic = 40, side = rows > 1, tw = side ? cw - pad * 2 - ic - 14 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, side ? ch - pad * 2 : ch - pad * 2 - ic - 16, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 16) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0;
    C.rect(x, y, cw, ch2, {fill:hot ? TE : '#F1F8F8'});
    K.poly(C, [[x + cw - 44, y], [x + cw, y], [x + cw, y + 44]], {fill:hot ? '#FFFFFF' : LT, op:hot ? 0.3 : 0.9});
    C.rect(x + pad, y + pad, ic, ic, {fill:hot ? '#FFFFFF' : TE, r:8});
    C.icon(x + pad + 10, y + pad + 10, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? TE : '#FFFFFF');
    if (side) K.hd(C, x + pad + ic + 14, y + pad, tw, ch2 - pad * 2, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#D9F1F2' : MUT});
    else K.hd(C, x + pad, y + pad + ic + 16, tw, ch2 - pad * 2 - ic - 16, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#D9F1F2' : MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: each number sits between two chevron bands */
function chevStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 70, bh = Math.min(170, Z.h * 0.5), y0 = Z.y + Math.max(0, (Z.h - bh - 110) * 0.25);
  var vs = C.groupFit(vals, tw - 30, bh * 0.6, {font:'head', max:64, min:22, maxLines:1});
  var ls = C.groupFit(labs, cw - 40, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 40, Z.y + Z.h - y0 - bh - 70, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var x = Z.x + k * cw, cx = x + cw / 2, cy = y0 + bh / 2, col = k % 2 ? DK : TE;
    chevL(C, x + 10, cy, bh / 2, 20, LT, 0.9); chev(C, x + cw - 10, cy, bh / 2, 20, col);
    C.text(cx - tw / 2, y0, tw, bh, vals[k], {font:'head', size:vs, wt:800, color:col, align:'c', va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x + 20, y0 + bh + 18, cw - 40, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 20, y0 + bh + 24 + le._h, cw - 40, Z.y + Z.h - (y0 + bh + 24 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a teal rail, a square stop for each step, text in columns */
function rail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 720) return tags(C, Z, o);
  var cw = Z.w / n, sq = 54, y = Z.y + 6, tw = cw - 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 30, {font:'head', max:21, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - sq - 80, {font:'body', max:15, min:10.5, lh:1.4});
  C.rect(Z.x, y + sq / 2 - 3, Z.w, 6, {fill:'#D9EEF0'});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, last = k === n - 1, col = last ? DK : TE;
    C.rect(x, y + sq / 2 - 3, last ? cw * 0.5 : cw, 6, {fill:col, op:0.9});
    C.rect(x, y, sq, sq, {fill:col}); K.poly(C, [[x + sq, y], [x + sq + 16, y + sq / 2], [x + sq, y + sq]], {fill:col});
    C.text(x, y, sq, sq, pad2(k + 1), {font:'head', size:20, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
    C.text(x, y + sq + 20, tw, 30, t.h || '', {font:'head', size:hs, wt:800, color:DK, lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x, y + sq + 56, tw, Z.h - sq - 60, t.d, {font:'body', size:ds, color:INK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#F1F8F8'}); chev(C, Z.x + 46, Z.y + 60, 30, 16, TE); chev(C, Z.x + 24, Z.y + 60, 30, 16, LT);
  var pad = 44, x = Z.x + 80, y = Z.y + pad - 12, w = Z.w - 80 - pad;
  if (it.h) { var he = C.text(x, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:TE, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 12; }
  C.text(x, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.48, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
/* the photo side: the picture fills only the inside of the chevron; everything left of the bands stays white */
function photoSide(C, right, o){
  var pw = right ? 580 : 520, x = right ? W - pw : 0;
  if (o && o.img) D.imgBox(C, x, 0, pw, H, {r:0}); else C.rect(x, 0, pw, H, {grad:{ang:150, stops:[[0, '#17A2AD'], [100, DK]]}});
  if (right) { K.poly(C, [[x - 2, -2], [x, -2], [x + 362, H / 2], [x, H + 2], [x - 2, H + 2]], {fill:'#FFFFFF'}); chev(C, x + 360, H / 2, H / 2 + 40, 30, LT); chev(C, x + 296, H / 2, H / 2 + 40, 58, TE); chev(C, x + 206, H / 2, H / 2 + 40, 16, DK); }
  else { chevL(C, x + pw - 150, H / 2, H / 2 + 40, 200, '#FFFFFF'); chevL(C, x + pw - 230, H / 2, H / 2 + 40, 60, TE); chevL(C, x + pw - 300, H / 2, H / 2 + 40, 34, LT, 0.9); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  photoSide(C, true, {img:o.img === 'photo'});
  brand(C, 70, 56);
  C.text(70, 180, 600, 250, C.s.title, {font:'head', fit:{max:70, min:32}, wt:800, color:DK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.02});
  if (C.s.subtitle) C.text(70, 444, 520, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(70, 560, 560, 22, 'Presentation by ' + who, {font:'body', size:15, wt:700, color:INK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) { var ww = Math.min(420, f.website.length * 8 + 60); C.rect(70, 606, ww, 38, {line:TE, lw:1.5, r:19}); C.text(70 + 22, 606, ww - 44, 38, f.website, {font:'body', size:13, wt:600, color:TE, va:'m', lh:1, ed:{f:'tf-website'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {};
  photoSide(C, true, {img:o.img === 'photo'});
  brand(C, 70, 56);
  C.text(70, 150, 600, 190, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:800, color:DK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(70, 356, 540, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 448 + k * 56;
    C.ell(70, y, 40, 40, {fill:k % 2 ? DK : TE}); C.icon(70 + 10, y + 10, 20, c.icon, '#FFFFFF');
    C.text(70 + 56, y, 500, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  photoSide(C, false, {img:false});
  C.text(70, 250, 330, 120, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1.02});
  if (C.s.subtitle) C.text(70, 380, 280, 80, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#D9F1F2', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  tags(C, {x:590, y:80, w:W - 590 - 70, h:H - 180}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:TE, tc:INK}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 600 : W - 220;
  if (photo) D.imgBox(C, 800, 110, 410, 500, {r:0});
  chev(C, x + 30, 150, 34, 18, TE); chev(C, x + 4, 150, 34, 18, LT);
  var qe = C.text(x, 220, w, 300, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:700, color:DK, lh:1.2, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 220 + qe._h + 26, 60, 4, {fill:TE}); C.text(x, 220 + qe._h + 44, w, 24, bt, {font:'body', size:15, wt:700, color:MUT, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'chevronteal', name:'Chevron Teal', tag:'White · teal chevrons',
  desc:'A clean white page with a photo that fills the inside of a teal chevron, pennant cards and arrow tags: confident and friendly for partnerships, sales and company decks.',
  fonts:{head:'Be Vietnam Pro', body:'Be Vietnam Pro', headW:800, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Be+Vietnam+Pro:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Chevron bands', {}), V('v2', 'Photo behind the chevrons', {img:'photo'})],
    closing:[V('v1', 'Chevron bands + contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Chevron panel + arrow tags', {fn:agendaA}), V('n2', 'Tiles', {body:tiles}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'})],
    text:[V('v1', 'Statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Pennant cards', {body:pennants}), V('n2', 'Arrow tags', {body:tags}), V('n3', 'Tiles', {body:tiles, hl:true}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p3:[V('v1', 'Pennant cards', {body:pennants}), V('n2', 'Arrow tags', {body:tags}), V('n3', 'Tiles', {body:tiles, hl:true}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p4:[V('v1', 'Arrow tags', {body:tags}), V('n2', 'Pennant cards', {body:pennants}), V('n3', 'Tiles', {body:tiles}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p56:[V('v1', 'Tiles', {body:tiles}), V('n2', 'Arrow tags', {body:tags}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p78:[V('v1', 'Tiles', {body:tiles}), V('n2', 'Arrow tags', {body:tags, max:8}), V('v2', 'Tiles + image right', {body:tiles, img:'right', cols:2})],
    stats:[V('v1', 'Chevron numbers', {body:chevStats}), V('n2', 'Progress rings', {body:'statRings', track:'#D9EEF0'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Chevron numbers + image right', {body:chevStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Rail', {body:rail}), V('n2', 'Pennant cards', {body:pennants}), V('n3', 'Arrow tags', {body:tags}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'})],
    quote:[V('v1', 'Chevron quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

/* Some layouts (Mono Portfolio "Index rows", "Ghost columns" and similar) print a huge, very faint number BEHIND the
   heading. With a colour theme the number was recoloured to the full heading colour, so number and heading read as
   overlapping text. A big number that lies under other text is now kept faint in every theme. */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || D.__ghostNum) return; D.__ghostNum = 1;
var u = D.u, rp = D.renderPage;
function bgOf(page){
  var b = page.bg || {};
  if (typeof b.fill === 'string') return b.fill;
  if (b.grad && b.grad.stops && b.grad.stops.length) { var a = b.grad.stops[0][1], z = b.grad.stops[b.grad.stops.length - 1][1]; return typeof a === 'string' && typeof z === 'string' ? u.mix(a, z, 0.5) : null; }
  return null;
}
function hit(a, b){ return !(a.x >= b.x + b.w || a.x + a.w <= b.x || a.y >= b.y + b.h || a.y + a.h <= b.y); }
function fix(page){
  var els = page.els || [], bg = bgOf(page); if (!bg || !/^#[0-9a-fA-F]{6}$/.test(bg)) return;
  var texts = els.filter(function(e){ return e && e.t === 'text' && typeof e.text === 'string'; });
  texts.forEach(function(e){
    if (!(e.size >= 60) || !/^\(?\d{1,2}\)?$/.test(e.text.replace(/\s+/g, '')) || e.ed) return;
    if (typeof e.color !== 'string' || !/^#[0-9a-fA-F]{6}$/.test(e.color)) return;
    if (Math.abs(u.lum(e.color) - u.lum(bg)) < 0.3) return;                 /* already faint */
    var core = {x:e.x + e.size * 0.1, y:e.y + (e.h - e.size * 0.7) / 2, w:Math.min(e.w, e.size * 1.15), h:e.size * 0.7};
    var under = texts.some(function(o){ return o !== e && o.size < e.size * 0.6 && hit(core, o); });
    if (under) e.color = u.mix(bg, e.color, 0.14);
  });
}
D.renderPage = function(T){
  var page = rp.apply(this, arguments);
  try { if (page && T && T.__rec) fix(page); } catch (err) {}
  return page;
};
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 50 - Copper Diamond : cream page, layered copper corner folds, diamond outlines round the photo */
var CO = '#B8642F', DK = '#6E3414', LT = '#E7B48E', CR = '#FBF6EF', INK = '#2E1D12', MUT = '#7A6455';
var P0 = {bg:CR, text:INK, title:DK, muted:MUT, accent:CO, accent2:DK, card:'#FFFFFF', line:'#EAD9C8', onAccent:'#FFFFFF'};
var S = {
  margin:74, series:[CO, DK, LT, '#3F6B5E', '#D9C2AE', '#2E1D12'],
  header:{kind:'left', top:92, tMax:44, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'solid', r:14, fill:'#FFFFFF', line:null, shadow:0.08, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:CO, dark:true, tc:'#FFFFFF', mc:'#FBE7D8'}},
  icon:{kind:'circle', bg:CO, fg:'#FFFFFF'}, num:{kind:'big', fg:CO},
  conc:{kind:'bar', barC:CO, label:'In short', lc:CO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:18, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#EBCBB0'], [100, '#B8642F']]}}
};
function dia(cx, cy, r){ return [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]]; }
function diaLine(C, cx, cy, r, color, lw, op){ var p = dia(cx, cy, r); for (var q = 0; q < 4; q++) C.line(p[q][0], p[q][1], p[(q + 1) % 4][0], p[(q + 1) % 4][1], {color:color, lw:lw || 2, op:op == null ? 1 : op}); }
/* layered folds in a corner: three triangles, light to dark */
function folds(C, corner, s){
  var r = corner.indexOf('r') >= 0, b = corner.indexOf('b') >= 0, x = r ? W : 0, y = b ? H : 0, dx = r ? -1 : 1, dy = b ? -1 : 1;
  K.poly(C, [[x, y], [x + dx * s, y], [x, y + dy * s]], {fill:LT, op:0.55});
  K.poly(C, [[x, y], [x + dx * s * 0.74, y], [x, y + dy * s * 0.74]], {fill:CO});
  K.poly(C, [[x, y], [x + dx * s * 0.44, y], [x, y + dy * s * 0.44]], {fill:DK});
}
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  K.poly(C, dia(x + 10, y + 11, 10), {fill:CO}); K.poly(C, dia(x + 20, y + 11, 10), {fill:DK, op:0.85});
  C.text(x + 40, y, 420, 22, f.company, {font:'head', size:12.5, wt:800, color:DK, va:'m', lh:1, caps:true, ls:0.1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 74, y = H - 36, f = C.cover;
  C.text(M, y, 500, 16, f.website || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  K.poly(C, dia(W - M - 14, y + 8, 15), {fill:CO});
  C.text(W - M - 29, y - 7, 30, 30, String(C.i + 1), {font:'head', size:11.5, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:CR});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  folds(C, 'tr', [150, 110, 190][k]);
  brand(C, 74, 46);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* a photo cut to a diamond: the square picture first, then its four corners are covered in the page colour */
function diaPhoto(C, cx, cy, r, col, ring){
  D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {r:0});
  [[-1, -1], [1, -1], [1, 1], [-1, 1]].forEach(function(q){ K.poly(C, [[cx + q[0] * (r + 2), cy + q[1] * (r + 2)], [cx + q[0] * (r + 2), cy], [cx, cy + q[1] * (r + 2)]], {fill:CR}); });
  C._dia = (C._dia || []).concat([[cx, cy, r, col || CO, ring || LT]]);
}
/* the outlines go on last, so no cover triangle can hide them */
function diaEdges(C){
  (C._dia || []).forEach(function(d){ diaLine(C, d[0], d[1], d[2] - 2, d[3], 7); diaLine(C, d[0], d[1], d[2] * 1.3, d[3], 2.5, 0.9); diaLine(C, d[0] + d[2] * 0.14, d[1] + d[2] * 0.1, d[2] * 1.16, d[4], 2); });
  C._dia = null;
}
/* band cards: a copper band runs across the slide and white cards sit half over it */
function bandCards(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return checks(C, Z, o);
  var g = 22, cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 54;
  var F = K.fitHD(C, it, tw, 52, Z.h - top - pad * 2 - 30, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - 20, bmx + top + pad * 2), y0 = Z.y + Math.max(10, (Z.h - ch) * 0.35);
  C.rect(0, y0 + ch * 0.38, W, ch * 0.74, {grad:{ang:0, stops:[[0, DK], [100, CO]]}});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g);
    C.rect(x, y0, cw, ch, {fill:'#FFFFFF', r:14, shadow:0.12});
    K.poly(C, dia(x + pad + 18, y0 + pad + 14, 20), {fill:k % 2 ? DK : CO});
    C.text(x + pad - 2, y0 + pad - 6, 40, 40, String(k + 1), {font:'head', size:15, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
    C.icon(x + cw - pad - 24, y0 + pad + 2, 24, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), CO);
    K.hd(C, x + pad, y0 + pad + top, tw, ch - top - pad * 2, t, F, {tc:INK, mc:MUT});
  });
}
/* check list: a diamond bullet, heading and text, in one or two columns, no boxes */
function checks(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 50, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 136), tw = cw - 46;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(10, (rh - bh) / 2);
    K.poly(C, dia(x + 13, y + oy + 12, 13), {fill:k % 2 ? DK : CO}); K.poly(C, dia(x + 13, y + oy + 12, 5), {fill:'#FFFFFF'});
    K.hd(C, x + 46, y + oy, tw, rh - oy - 4, t, F, {tc:INK, mc:MUT});
    if (r < per - 1) C.rect(x + 46, y + rh - 0.5, tw, 1, {fill:DK, op:0.14});
  });
}
/* fold cards: white cards with a turned-down copper corner */
function foldCards(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22, fs = 34, tw = cw - pad * 2 - 8;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 8), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0;
    C.rect(x, y, cw, ch2, {fill:hot ? CO : '#FFFFFF', r:6, shadow:0.08});
    K.poly(C, [[x + cw - fs, y], [x + cw, y], [x + cw, y + fs]], {fill:CR});
    K.poly(C, [[x + cw - fs, y], [x + cw - fs, y + fs], [x + cw, y + fs]], {fill:hot ? DK : LT});
    C.rect(x, y + 14, 5, ch2 - 28, {fill:hot ? '#FFFFFF' : CO, op:hot ? 0.6 : 1});
    K.hd(C, x + pad + 4, y + pad, tw, ch2 - pad * 2, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#FBE7D8' : MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: copper badges - the number and its label side by side in one rounded tag */
function badges(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n === 4 ? 2 : (Z.w < 760 ? 1 : n), rows = Math.ceil(n / cols), g = 20, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, 170), vals = st.map(sv), labs = st.map(sl), pad = 22, vw = cw * 0.46, tw = cw - vw - pad * 2;
  var vs = C.groupFit(vals, vw - pad, ch - 30, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, ch - 30, {font:'head', max:18, min:12, maxLines:3});
  var tot = ch * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch + g), col = k % 2 ? DK : CO;
    C.rect(x, y + 6, cw, ch, {fill:DK, op:0.18, r:18});
    C.rect(x, y, cw, ch, {fill:col, r:18});
    C.rect(x + vw, y + 12, cw - vw - 12, ch - 24, {fill:'#FFFFFF', r:12});
    C.text(x + 10, y, vw - 20, ch, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + vw + pad - 6, y + 12, tw, ch - 24, labs[k], {font:'head', size:ls, wt:700, color:INK, va:'m', lh:1.18, caps:true, ls:0.03, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
  });
}
/* timeline: diamond stops over a copper band */
function stops(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return checks(C, Z, o);
  var cw = Z.w / n, r = 40, by = Z.y + 26, tw = cw - 24;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), r * 1.3, r, {font:'head', max:18, min:10, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - r * 2 - 70, {font:'body', max:15, min:10.5, lh:1.4});
  C.rect(0, by + r - 20, W, 40, {grad:{ang:0, stops:[[0, DK], [100, CO]]}});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    K.poly(C, dia(cx, by + r, r + 7), {fill:CR}); K.poly(C, dia(cx, by + r, r), {fill:'#FFFFFF'}); diaLine(C, cx, by + r, r, k === n - 1 ? DK : CO, 3);
    C.text(cx - r, by, r * 2, r * 2, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:DK, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(cx - tw / 2, by + r * 2 + 30, tw, Z.h - r * 2 - 60, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF', r:14, shadow:0.08});
  K.poly(C, [[Z.x + Z.w - 60, Z.y], [Z.x + Z.w, Z.y], [Z.x + Z.w, Z.y + 60]], {fill:CR}); K.poly(C, [[Z.x + Z.w - 60, Z.y], [Z.x + Z.w - 60, Z.y + 60], [Z.x + Z.w, Z.y + 60]], {fill:LT});
  var pad = 46, y = Z.y + pad, w = Z.w - pad * 2 - 30;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:CO, lh:1.12, caps:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w + 30, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  folds(C, 'br', 330); folds(C, 'tr', 120);
  brand(C, 74, 56);
  if (photo) { diaPhoto(C, 940, 350, 168); if (o.two) diaPhoto(C, 738, 548, 76, DK); diaEdges(C); }
  else { diaLine(C, 940, 350, 220, CO, 2.5, 0.9); K.poly(C, dia(940, 350, 180), {fill:'#FFFFFF'}); K.poly(C, dia(940, 350, 150), {fill:CO}); K.poly(C, dia(940, 350, 90), {fill:DK}); diaLine(C, 1100, 210, 50, DK, 2); K.poly(C, dia(760, 520, 34), {fill:LT}); }
  var te = C.text(74, 190, 600, 240, C.s.title, {font:'head', fit:{max:72, min:32}, wt:800, color:DK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, caps:true, ls:-0.01});
  if (C.s.subtitle) C.text(74, 190 + te._h + 20, 540, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(560, who.length * 8.2 + 56); K.poly(C, [[74, 552], [74 + pw, 552], [74 + pw + 18, 574], [74 + pw, 596], [74, 596]], {fill:CO}); C.text(74 + 20, 552, pw - 30, 44, who, {font:'body', size:14, wt:700, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(74, 628, 500, 18, f.website, {font:'body', size:12.5, wt:600, color:CO, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  folds(C, 'br', 330); folds(C, 'tl', 110);
  brand(C, 150, 56);
  if (photo) { diaPhoto(C, 940, 340, 160); diaEdges(C); } else { diaLine(C, 940, 340, 200, CO, 2.5, 0.9); K.poly(C, dia(940, 340, 160), {fill:'#FFFFFF'}); K.poly(C, dia(940, 340, 130), {fill:CO}); C.icon(900, 300, 80, 'mdi:handshake-outline', '#FFFFFF'); }
  C.text(74, 150, 620, 180, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:800, color:DK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, caps:true});
  if (C.s.subtitle) C.text(74, 346, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 440 + k * 56;
    K.poly(C, dia(74 + 20, y + 20, 22), {fill:k % 2 ? DK : CO}); C.icon(74 + 10, y + 10, 20, c.icon, '#FFFFFF');
    C.text(74 + 60, y, 520, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  if ((C.o || o || {}).img === 'photo') { diaPhoto(C, 250, 430, 150); diaEdges(C); }
  else { diaLine(C, 250, 430, 210, CO, 2.5, 0.9); K.poly(C, dia(250, 430, 170), {fill:'#FFFFFF'}); K.poly(C, dia(250, 430, 140), {fill:CO}); C.icon(210, 390, 80, 'mdi:format-list-bulleted', '#FFFFFF'); }
  var te = C.text(520, 110, W - 520 - 74, 80, C.s.title || 'Agenda', {font:'head', fit:{max:54, min:30}, wt:800, color:DK, ed:{f:'title'}, maxLines:1, va:'t', lh:1.02, caps:true});
  if (C.s.subtitle) C.text(520, 110 + te._h + 8, W - 520 - 74, 30, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:MUT, ed:{f:'subtitle'}, lh:1.3, maxLines:1});
  checks(C, {x:520, y:230, w:W - 520 - 110, h:H - 330}, {cols:2, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CO, tc:INK}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 580 : W - 220;
  if (photo) { diaPhoto(C, 965, 360, 160); diaEdges(C); }
  K.poly(C, dia(x + 34, 174, 34), {fill:CO}); C.text(x, 152, 68, 60, '”', {font:'head', size:56, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x, 240, w, 280, C.s.title, {font:'head', fit:{max:photo ? 34 : 44, min:19}, wt:700, color:DK, lh:1.2, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, 240 + qe._h + 26, w, 24, bt, {font:'body', size:14.5, wt:700, color:CO, lh:1.1, caps:true, ls:0.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'copperdiamond', name:'Copper Diamond', tag:'Cream · copper folds',
  desc:'A cream page with layered copper corner folds and photos cut to a diamond: polished and warm for company profiles, real estate and services.',
  fonts:{head:'Josefin Sans', body:'Hind', headW:700, bodyW:400, headLh:1.1, gf:'Josefin+Sans:wght@500;600;700|Hind:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Diamond + corner folds', {}), V('v2', 'Photo in a diamond', {img:'photo'}), V('v3', 'Two photos in diamonds', {img:'photo', two:true})],
    closing:[V('v1', 'Diamond + contacts', {}), V('v2', 'Diamond photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Diamond + check list', {fn:agendaA}), V('n3', 'Diamond photo + check list', {fn:agendaA, img:'photo'}), V('n2', 'Fold cards', {body:foldCards}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1})],
    text:[V('v1', 'Fold statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Band cards', {body:bandCards}), V('n2', 'Fold cards', {body:foldCards, hl:true}), V('n3', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:1})],
    p3:[V('v1', 'Band cards', {body:bandCards}), V('n2', 'Fold cards', {body:foldCards, hl:true}), V('n3', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:1})],
    p4:[V('v1', 'Check list', {body:checks, cols:2}), V('n2', 'Band cards', {body:bandCards}), V('n3', 'Fold cards', {body:foldCards}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:2})],
    p56:[V('v1', 'Fold cards', {body:foldCards}), V('n2', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:2})],
    p78:[V('v1', 'Fold cards', {body:foldCards}), V('n2', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1})],
    stats:[V('v1', 'Copper badges', {body:badges}), V('n2', 'Progress rings', {body:'statRings', track:'#EAD9C8'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Badges + image right', {body:badges, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Diamond stops', {body:stops}), V('n2', 'Band cards', {body:bandCards}), V('n3', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1})],
    quote:[V('v1', 'Diamond quote', {}), V('v2', 'Quote + diamond photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

/* Forest Campaign (template 21): second road of the cover / closing decor was drawn in the panel colour (it only cut a gap into the first road) and ran past the slide edge. Give it a quiet lighter tone of the panel and keep it inside the panel. */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || D.__forestRoads) return; D.__forestRoads = 1;
function rgb(c){ c = String(c || '').replace('#', ''); if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2]; if (!/^[0-9a-fA-F]{6}$/.test(c)) return null; return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)]; }
function mix(a, b, t){ var x = rgb(a), y = rgb(b); if (!x || !y) return a; var h = '#'; for (var i = 0; i < 3; i++) { var v = Math.round(x[i] + (y[i] - x[i]) * t).toString(16); h += (v.length < 2 ? '0' : '') + v; } return h.toUpperCase(); }
function fix(page){
  var els = page.els || [], panel = null, roads = [], i, j;
  for (i = 0; i < els.length; i++) { var e = els[i]; if (!e) continue;
    if (!panel && e.t === 'rect' && e.h >= 700 && e.w > 400 && e.w < 800 && e.fill) panel = e;
    if (e.t === 'path' && e.stroke && !e.fill && (e.lw || 0) >= 18 && e.d && e.d.length > 10) roads.push(e); }
  if (!panel || roads.length !== 2) return;
  var b = roads[1], c = rgb(panel.fill); if (!c) return;
  var lum = (c[0] * 299 + c[1] * 587 + c[2] * 114) / 1000;
  b.stroke = lum < 150 ? mix(panel.fill, '#FFFFFF', 0.2) : mix(panel.fill, '#000000', 0.14);
  var mn = 1e9, mx = -1e9;
  for (i = 0; i < b.d.length; i++) { var s = b.d[i]; for (j = 1; j + 1 < s.length; j += 2) { if (s[j] < mn) mn = s[j]; if (s[j] > mx) mx = s[j]; } }
  var half = (b.lw || 0) / 2 + 4, dx = 0;
  if (mx + half > panel.x + panel.w) dx = panel.x + panel.w - (mx + half);
  if (mn + dx - half < panel.x) dx = panel.x - (mn - half);
  if (!dx) return;
  for (i = 0; i < b.d.length; i++) { var q = b.d[i]; for (j = 1; j + 1 < q.length; j += 2) q[j] += dx; }
  for (i = 0; i < els.length; i++) { var p = els[i]; if (p && p.t === 'ell' && p.w <= 60 && p.x + p.w / 2 >= mn - 30 && p.x + p.w / 2 <= mx + 30) p.x += dx; }
}
var rp = D.renderPage;
D.renderPage = function(T){
  var page = rp.apply(this, arguments);
  try { var id = T && (T.id || (T.__rec && T.__rec.id)); if (page && id === 'forest' && (page.kind === 'cover' || page.kind === 'closing')) fix(page); } catch (err) {}
  return page;
};
if (D.refreshEditor) { try { D.refreshEditor(); } catch (e2) {} }
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 51 - Saffron Study : white page, saffron-to-umber gradient blocks, clusters of rounded squares */
var SA = '#E8A317', UM = '#3A2200', MID = '#B86E00', INK = '#191510', MUT = '#6B6257', PALE = '#FFF4DC';
var G = {ang:120, stops:[[0, '#F2B32E'], [55, '#B86E00'], [100, '#2E1B00']]};
var G2 = {ang:160, stops:[[0, '#F2B32E'], [100, '#8A4F00']]};
var P0 = {bg:'#FFFFFF', text:INK, title:INK, muted:MUT, accent:SA, accent2:MID, card:PALE, line:'#EADFC8', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[SA, MID, UM, '#F6D27A', '#7A746B', '#191510'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:16, fill:PALE, line:null, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:MID, dark:true, tc:'#FFFFFF', mc:'#FFE9BF'}},
  icon:{kind:'square', bg:SA, fg:'#FFFFFF', r:10}, num:{kind:'big', fg:MID},
  conc:{kind:'bar', barC:SA, label:'Finding', lc:MID, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#F6D27A'], [100, '#B86E00']]}}
};
/* a cluster of rounded squares; the big one holds the photo */
function cluster(C, x, y, s, photo){
  C.rect(x + s * 0.62, y - s * 0.16, s * 0.5, s * 0.5, {grad:G2, r:s * 0.11});
  C.rect(x - s * 0.18, y + s * 0.6, s * 0.46, s * 0.46, {fill:UM, r:s * 0.1});
  C.rect(x - 10, y - 10, s + 20, s + 20, {fill:'#FFFFFF', r:s * 0.2 + 8});
  if (photo) D.imgBox(C, x, y, s, s, {r:s * 0.2}); else { C.rect(x, y, s, s, {grad:G, r:s * 0.2}); C.rect(x + s * 0.2, y + s * 0.2, s * 0.6, s * 0.6, {line:'#FFFFFF', lop:0.7, lw:2, r:s * 0.13}); C.rect(x + s * 0.36, y + s * 0.36, s * 0.28, s * 0.28, {fill:'#FFFFFF', op:0.9, r:s * 0.07}); }
  C.rect(x + s * 0.8, y + s * 0.72, s * 0.3, s * 0.3, {fill:'#FFFFFF', r:s * 0.075}); C.rect(x + s * 0.8 + 8, y + s * 0.72 + 8, s * 0.3 - 16, s * 0.3 - 16, {grad:G2, r:s * 0.06});
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  C.rect(x, y, 24, 24, {grad:G2, r:7}); C.rect(x + 7, y + 7, 10, 10, {fill:'#FFFFFF', r:3});
  C.text(x + 34, y, 420, 24, f.company, {font:'head', size:12.5, wt:800, color:light ? '#FFFFFF' : INK, va:'m', lh:1, caps:true, ls:0.12, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.rect(M, y + 1, 14, 14, {grad:G2, r:4});
  C.text(M + 24, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.06});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1), {font:'head', size:13, wt:800, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { C.rect(W - 120, -40, 110, 110, {grad:G2, r:26}); C.rect(W - 56, 44, 60, 60, {fill:UM, r:14}); }
  else if (k === 1) { C.rect(W - 86, -30, 80, 80, {fill:UM, r:20}); C.rect(W - 150, 26, 54, 54, {grad:G2, r:13}); }
  else { C.rect(W - 140, -50, 130, 130, {line:SA, lw:2.5, r:30}); C.rect(W - 70, 30, 54, 54, {grad:G2, r:13}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* gradient columns: one gradient band, columns that step down in rounded panels */
function gradCols(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return ladder(C, Z, o);
  var cw = Z.w / n, pad = 26, tw = cw - pad * 2, step = Math.min(26, Z.h * 0.06);
  var F = K.fitHD(C, it, tw, 50, Z.h - step * (n - 1) - pad * 2 - 30, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:G, r:22});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, y = Z.y + k * step;
    if (k) C.rect(x, y, Z.x + Z.w - x, Z.h - k * step, {fill:'#000000', op:0.13, r:22});
    C.text(x + pad, y + pad, tw, 16, 'POINT ' + (k + 1), {font:'head', size:11, wt:800, color:'#FFE9BF', lh:1, ls:0.16});
    K.hd(C, x + pad, y + pad + 30, tw, Z.h - k * step - pad * 2 - 30, t, F, {tc:'#FFFFFF', mc:'#FFF1D6'});
  });
}
/* ladder: rows inside one gradient panel, each row a darker rounded step */
function ladder(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 124), pad = 26, nw = 60, tw = Z.w - pad * 2 - nw;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x, y0, Z.w, tot, {grad:G, r:22});
  it.forEach(function(t, k){
    var y = y0 + k * rh, bh = blockH(C, t, tw, F);
    if (k) C.rect(Z.x, y, Z.w, tot - k * rh, {fill:'#000000', op:0.11, r:22});
    C.text(Z.x + pad, y, nw - 10, rh, pad2(k + 1), {font:'head', size:Math.min(28, rh * 0.32), wt:800, color:'#FFE9BF', va:'m', lh:1, caps:false});
    K.hd(C, Z.x + pad + nw, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:'#FFFFFF', mc:'#FFF1D6'});
  });
}
/* linked blocks: a small heading block joined by a bar to a wider text block */
function linked(C, Z, o){
  var it = C.it.slice(0, o.max || 5), n = it.length; if (!n) return;
  var g = 16, rh = Math.min((Z.h - g * (n - 1)) / n, 130), hasH = it.some(function(t){ return t.h; }), lw = hasH ? Math.min(280, Z.w * 0.3) : 90, link = 34, pad = 22, tw = Z.w - lw - link - pad * 2;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), lw - pad * 2, rh - 20, {font:'head', max:18, min:12, maxLines:3}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 22, {font:'body', max:hasH ? 15.5 : 19, min:10.5, lh:1.42});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    C.rect(Z.x + lw - 20, y + rh * 0.3, link + 40, rh * 0.4, {fill:MID});
    C.rect(Z.x, y + rh * 0.1, lw, rh * 0.8, {grad:G2, r:14});
    C.rect(Z.x + lw + link, y, Z.w - lw - link, rh, {grad:G, r:14});
    C.text(Z.x + pad, y + rh * 0.1, lw - pad * 2, rh * 0.8, hasH ? (t.h || '') : pad2(k + 1), {font:'head', size:hasH ? hs : 26, wt:800, color:'#FFFFFF', va:'m', align:hasH ? 'l' : 'c', lh:1.15, caps:hasH, ls:hasH ? 0.04 : 0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:3});
    C.text(Z.x + lw + link + pad, y, tw, rh, t.d, {font:'body', size:ds, color:'#FFFFFF', va:'m', lh:1.42, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* notes: pale rounded tiles with a small gradient square, for many points */
function notes(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, sq = 30, tw = cw - pad * 2 - sq - 12;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2, {hMax:19, dMax:14.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, Math.max(bmx, sq) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:PALE, r:16});
    C.rect(x + pad, y + pad, sq, sq, {grad:G2, r:8});
    C.text(x + pad, y + pad, sq, sq, String(k + 1), {font:'head', size:13, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
    K.hd(C, x + pad + sq + 12, y + pad, tw, ch2 - pad * 2, t, F, {tc:INK, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: a gradient rounded square per number, an outline square behind it */
function squares(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw - 50, Z.h * 0.52, 210), vals = st.map(sv), labs = st.map(sl), tw = cw - 30;
  var vs = C.groupFit(vals, s - 30, s * 0.5, {font:'head', max:56, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - s - 90, {font:'body', max:14, min:10.5, lh:1.4});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), x = cx - s / 2, y = Z.y + 14;
    C.rect(x + 16, y - 12, s, s, {line:SA, lw:2.5, r:s * 0.2});
    C.rect(x, y, s, s, {grad:k % 2 ? {ang:160, stops:[[0, '#8A4F00'], [100, '#2E1B00']]} : G2, r:s * 0.2});
    C.text(x + 15, y, s - 30, s, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, y + s + 20, tw, 48, labs[k], {font:'head', size:ls, wt:800, color:INK, align:'c', lh:1.14, caps:true, ls:0.03, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, y + s + 26 + le._h, tw, Z.y + Z.h - (y + s + 26 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: gradient columns that grow taller, each with its date on top */
function growth(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return ladder(C, Z, o);
  var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 16, tw = cw - pad * 2, minH = Z.h * 0.6;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 34, {font:'head', max:24, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, minH - 70, {font:'body', max:14.5, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var hh = minH + (Z.h - minH) * (n > 1 ? k / (n - 1) : 1), x = Z.x + k * (cw + g), y = Z.y + Z.h - hh;
    C.rect(x, y, cw, hh, {grad:k === n - 1 ? {ang:160, stops:[[0, '#8A4F00'], [100, '#2E1B00']]} : G2, r:16});
    C.text(x + pad, y + pad, tw, 34, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + 40, 30, 3, {fill:'#FFFFFF', op:0.8});
    C.text(x + pad, y + pad + 56, tw, hh - pad * 2 - 56, t.d, {font:'body', size:ds, color:'#FFFFFF', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:G, r:24});
  var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:'#FFE9BF', lh:1.12, caps:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:'#FFFFFF', lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 330, 760, 150, {grad:{ang:0, stops:[[0, '#2E1B00'], [45, '#B86E00'], [100, '#F2B32E']]}});
  cluster(C, 800, 190, 320, photo);
  brand(C, 72, 56);
  C.text(72, 110, 640, 210, C.s.title, {font:'head', fit:{max:74, min:32}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1, caps:true, ls:-0.01});
  if (C.s.subtitle) C.text(72, 344, 600, 122, C.s.subtitle, {font:'head', fit:{max:20, min:13}, wt:700, color:'#FFFFFF', va:'m', caps:true, ls:0.03, ed:{f:'subtitle'}, lh:1.35, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(72, 520, 140, 22, 'Presented by', {font:'body', size:13, wt:500, color:MUT, lh:1.1}); C.text(72, 544, 600, 26, who, {font:'head', size:17, wt:800, color:INK, lh:1.1, caps:true, ls:0.03, ed:{f:'tf-presenter'}, maxLines:1}); C.rect(72, 584, 300, 2, {fill:INK}); }
  if (f.website) { C.rect(72, 612, 16, 16, {grad:G2, r:5}); C.text(98, 610, 500, 20, f.website, {font:'body', size:13, wt:600, color:INK, va:'m', lh:1, ls:0.08, ed:{f:'tf-website'}}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(0, 250, 760, 130, {grad:{ang:0, stops:[[0, '#2E1B00'], [45, '#B86E00'], [100, '#F2B32E']]}});
  cluster(C, 810, 210, 300, photo);
  brand(C, 72, 56);
  C.text(72, 100, 660, 140, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:1, va:'b', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 262, 620, 106, C.s.subtitle, {font:'head', fit:{max:19, min:13}, wt:700, color:'#FFFFFF', va:'m', caps:true, ls:0.03, ed:{f:'subtitle'}, lh:1.35, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 430 + k * 54;
    C.rect(72, y, 38, 38, {grad:G2, r:10}); C.icon(72 + 9, y + 9, 20, c.icon, '#FFFFFF');
    C.text(72 + 54, y, 540, 38, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 470, H, {grad:{ang:160, stops:[[0, '#F2B32E'], [60, '#B86E00'], [100, '#2E1B00']]}});
  C.rect(300, 470, 240, 240, {fill:'#FFFFFF', r:52}); C.rect(318, 488, 204, 204, {grad:G2, r:42}); C.rect(60, 560, 110, 110, {line:'#FFFFFF', lop:0.6, lw:2, r:26});
  var te = C.text(72, 130, 360, 200, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:32}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 130 + te._h + 16, 340, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FFF1D6', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  notes(C, {x:560, y:90, w:W - 560 - 72, h:H - 190}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:MID, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 620 : W - 144;
  if (photo) cluster(C, 830, 200, 280, true);
  C.rect(x, 130, w, 400, {grad:G, r:26});
  C.rect(x + 40, 100, 70, 70, {fill:'#FFFFFF', r:18}); C.rect(x + 48, 108, 54, 54, {grad:G2, r:13}); C.text(x + 48, 118, 54, 54, '”', {font:'head', size:54, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 206, w - 92, 240, C.s.title, {font:'head', fit:{max:photo ? 30 : 38, min:18}, wt:700, color:'#FFFFFF', lh:1.24, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 206 + qe._h + 22, w - 92, 24, bt, {font:'head', size:13.5, wt:800, color:'#FFE9BF', lh:1.1, caps:true, ls:0.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'saffronstudy', name:'Saffron Study', tag:'White · saffron gradient',
  desc:'A white page with saffron-to-umber gradient blocks and clusters of rounded squares: clear and serious for research, studies, reports and findings.',
  fonts:{head:'Golos Text', body:'Golos Text', headW:800, bodyW:400, headLh:1.06, gf:'Golos+Text:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Gradient band + square cluster', {}), V('v2', 'Photo in the cluster', {img:'photo'})],
    closing:[V('v1', 'Gradient band + contacts', {}), V('v2', 'Photo cluster + contacts', {img:'photo'})],
    agenda:[V('v1', 'Gradient panel + notes', {fn:agendaA}), V('n2', 'Ladder', {body:ladder}), V('v2', 'Notes + image right', {body:notes, img:'right', cols:1})],
    text:[V('v1', 'Gradient statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Linked blocks', {body:linked}), V('n2', 'Gradient columns', {body:gradCols}), V('n3', 'Notes', {body:notes}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:1})],
    p3:[V('v1', 'Gradient columns', {body:gradCols}), V('n2', 'Ladder', {body:ladder}), V('n3', 'Linked blocks', {body:linked}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:1})],
    p4:[V('v1', 'Ladder', {body:ladder}), V('n2', 'Gradient columns', {body:gradCols}), V('n3', 'Notes', {body:notes}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:2})],
    p56:[V('v1', 'Notes', {body:notes}), V('n2', 'Ladder', {body:ladder}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:2})],
    p78:[V('v1', 'Notes', {body:notes}), V('v2', 'Notes + image right', {body:notes, img:'right', cols:2})],
    stats:[V('v1', 'Rounded squares', {body:squares}), V('n2', 'Progress rings', {body:'statRings', track:'#EADFC8'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Squares + image right', {body:squares, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Growing columns', {body:growth}), V('n2', 'Ladder', {body:ladder}), V('n3', 'Linked blocks', {body:linked}), V('v2', 'Ladder + image right', {body:ladder, img:'right'})],
    quote:[V('v1', 'Gradient quote', {}), V('v2', 'Quote + photo cluster', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

/* Picking a theme froze the page for seconds: every one of the 64 template cards asked for its fonts again, and
   each of those 64 requests redrew the whole template grid (or every slide thumbnail of an open deck) when it
   finished. Fonts are now requested once per font set, and all of them together cause at most ONE redraw -
   and none at all when the fonts were already loaded. */
(function(){
var D = window.DSTE; if (!D || !D.loadFonts || D.__fontRefresh) return; D.__fontRefresh = 1;
var req = {}, timer = null;
function redraw(){
  clearTimeout(timer);
  timer = setTimeout(function(){ try { if (D.clearFit) D.clearFit(); if (D.refreshEditor) D.refreshEditor(); } catch (e) {} }, 200);
}
function hasLink(href){
  var ls = document.getElementsByTagName('link');
  for (var i = 0; i < ls.length; i++) if (ls[i].href === href) return true;
  return false;
}
D.loadFonts = function(T){
  if (!T || !T.fonts || !T.fonts.gf) return;
  var key = T.fonts.head + '|' + T.fonts.body + '|' + T.fonts.gf;
  if (req[key]) return; req[key] = 1;
  T.fonts.gf.split('|').forEach(function(f){               /* one request per family, so one bad family never blocks the rest */
    if (!f || req['gf:' + f]) return; req['gf:' + f] = 1;
    var href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g, '+') + '&display=swap';
    if (hasLink(href)) return;
    var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = href;
    document.head.appendChild(l);
  });
  if (!document.fonts || !document.fonts.load) return;
  var specs = [];
  [400, 500, 600, 700, 800].forEach(function(w){ specs.push(w + ' 40px "' + T.fonts.head + '"'); specs.push(w + ' 16px "' + T.fonts.body + '"'); });
  specs.push('italic 400 16px "' + T.fonts.head + '"');
  var ready = true;
  try { for (var i = 0; i < specs.length; i++) if (!document.fonts.check(specs[i])) { ready = false; break; } } catch (e) { ready = false; }
  var jobs = specs.map(function(s){ return document.fonts.load(s); });
  if (ready) return;                                          /* already drawn with the right fonts: nothing to redraw */
  Promise.all(jobs).then(redraw).catch(function(){});
};
})();
/* Template grid: draw a card's preview only when the card is on screen. With "View All" open the grid has 65 cards
   and every theme pick redrew all of them at once, which blocked the page. Off-screen cards now cost nothing. */
(function(){
var D = window.DSTE; if (!D || !D.renderHTML || D.__lazyGrid || !window.IntersectionObserver) return; D.__lazyGrid = 1;
var real = D.renderHTML, jobs = {}, seq = 0, scanT = null;
var io = new IntersectionObserver(function(list){
  list.forEach(function(en){
    if (!en.isIntersecting) return;
    var el = en.target, k = el.getAttribute('data-dste-lazy'), a = jobs[k];
    io.unobserve(el); delete jobs[k];
    if (!a || !el.parentNode) return;
    try { el.outerHTML = real.apply(D, a); } catch (e) {}
  });
}, {rootMargin: '150px'});
function scan(){
  var live = {}, els = document.querySelectorAll('[data-dste-lazy]');
  for (var i = 0; i < els.length; i++) { var k = els[i].getAttribute('data-dste-lazy'); live[k] = 1; if (!els[i].__obs) { els[i].__obs = 1; io.observe(els[i]); } }
  for (var j in jobs) if (!live[j]) delete jobs[j];           /* cards of an older grid that is gone */
}
D.renderHTML = function(T, slide, vid, idx, deck, opts){
  var S = D.SAMPLE;
  if (T && S && slide === S.cover && idx === -1 && opts && opts.edit === false && deck && deck.slides && deck.slides.length === 1) {
    var k = 'g' + (++seq); jobs[k] = arguments;
    clearTimeout(scanT); scanT = setTimeout(scan, 0);
    var bg = (T.pal && T.pal.bg) || '#111';
    return '<div data-dste-lazy="' + k + '" style="position:absolute;inset:0;background:' + String(bg).replace(/[^#\w(),.% -]/g, '') + ';"></div>';
  }
  return real.apply(this, arguments);
};
})();
/* Editor: one action (a theme pick, a layout pick) made the editor work out the same slide several times in a row -
   thumbnail, preload, canvas - and every pass over a glass slide re-blurs its background. Identical passes that
   follow each other within the same action now reuse the first result. The memory is emptied on every click, key
   press, edit and redraw request, and after 0.8 s, so nothing old can be shown. */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || D.__memoRP) return; D.__memoRP = 1;
var real = D.renderPage, memo = {}, size = 0, born = 0;
function clear(){ if (size) { memo = {}; size = 0; } }
function rep(k, v){ return (typeof v === 'string' && v.length > 160) ? v.length + ':' + v.slice(0, 40) + v.slice(-40) : v; }
function sig(o){ try { return JSON.stringify(o, rep); } catch (e) { return null; } }
['pointerdown', 'keydown', 'input', 'change', 'drop'].forEach(function(ev){ document.addEventListener(ev, clear, true); });
['clearFit', 'refreshEditor'].forEach(function(n){
  var f = D[n]; if (typeof f !== 'function') return;
  D[n] = function(){ clear(); return f.apply(this, arguments); };
});
D.renderPage = function(T, slide, vid, idx, deck){
  var now = Date.now();
  if (now - born > 800) { clear(); born = now; }
  var key = null;
  if (T && slide && T.id && arguments.length <= 5) {
    var a = sig(slide), b = deck && deck.slides ? sig(deck.slides) : '', t = sig([T.pal, T.fonts, T.s, T.__rec ? 1 : 0]);
    if (a !== null && b !== null && t !== null) key = T.id + '|' + t + '|' + vid + '|' + idx + '|' + a + '|' + b;
  }
  var p = key !== null && memo.hasOwnProperty(key) ? memo[key] : null;
  if (!p) {
    p = real.apply(this, arguments);
    if (key !== null && p && p.els && p.els.slice) { memo[key] = p; size++; } else return p;
  }
  var copy = {}; for (var k in p) copy[k] = p[k]; copy.els = p.els.slice();      /* the caller gets its own list */
  return copy;
};
})();
/* Editor "Change layout" panel: a glass template has 17-25 layouts per slide and all of them were drawn on every
   slide change and theme pick. A layout card is now drawn when it scrolls into view. */
(function(){
var D = window.DSTE; if (!D || !D.renderHTML || D.__lazyVP || !window.IntersectionObserver) return; D.__lazyVP = 1;
var real = D.renderHTML, jobs = {}, seq = 0, scanT = null;
function tidy(h){
  return String(h).replace(/class="dsp-vp-ihover[^"]*"/g, 'class="dsp-vp-ihover" style="display:none!important;"')
    .replace(/class="dsp-vp-imgreset[^"]*"/g, 'style="display:none!important;"')
    .replace(/class="dsp-vp-cropbtn[^"]*"/g, 'style="display:none!important;"')
    .replace(/contenteditable="true"/g, 'contenteditable="false"');
}
var io = new IntersectionObserver(function(list){
  list.forEach(function(en){
    if (!en.isIntersecting) return;
    var el = en.target, k = el.getAttribute('data-dste-lazyvp'), a = jobs[k];
    io.unobserve(el); delete jobs[k];
    if (!a || !el.parentNode) return;
    try { el.outerHTML = tidy(real.apply(D, a)); } catch (e) {}
  });
}, {rootMargin: '250px'});
function scan(){
  var live = {}, els = document.querySelectorAll('[data-dste-lazyvp]');
  for (var i = 0; i < els.length; i++) { var k = els[i].getAttribute('data-dste-lazyvp'); live[k] = 1; if (!els[i].__obs) { els[i].__obs = 1; io.observe(els[i]); } }
  for (var j in jobs) if (!live[j]) delete jobs[j];
}
D.renderHTML = function(T, slide, vid, idx, deck, opts){
  if (T && slide && deck && deck.slides && idx >= 0 && deck.slides[idx] && deck.slides[idx] !== slide && deck.slides.indexOf(slide) < 0) {
    var st = ''; try { st = String(new Error().stack || ''); } catch (e) {}
    if (/\bvpRender\b/.test(st)) {                                /* only the layout cards of the right-hand panel */
      var k = 'v' + (++seq); jobs[k] = arguments;
      clearTimeout(scanT); scanT = setTimeout(scan, 0);
      var bg = (T.pal && T.pal.bg) || '#111';
      return '<div data-dste-lazyvp="' + k + '" style="position:absolute;inset:0;background:' + String(bg).replace(/[^#\w(),.% -]/g, '') + ';"></div>';
    }
  }
  return real.apply(this, arguments);
};
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 52 - Care Blue : white page, navy panels, plus signs, round photos with an offset ring, a heartbeat line */
var NV = '#0B2A5B', BL = '#1F6FEB', SKY = '#E6F0FF', INK = '#0F1B33', MUT = '#5A6B88', LN = '#C9D9F2';
var P0 = {bg:'#FFFFFF', text:INK, title:NV, muted:MUT, accent:BL, accent2:NV, card:SKY, line:LN, onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[BL, NV, '#7FB0FF', '#12B5A6', '#9AA9C4', '#0F1B33'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:14, fill:SKY, line:null, shadow:0, dark:false, tc:NV, mc:MUT,
        hl:{kind:'solid', fill:NV, dark:true, tc:'#FFFFFF', mc:'#C9D9F2'}},
  icon:{kind:'circle', bg:BL, fg:'#FFFFFF'}, num:{kind:'big', fg:BL},
  conc:{kind:'bar', barC:BL, label:'In short', lc:BL, tc:NV},
  footer:{fn:foot}, imgFramed:true, imgR:24, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#BBD4FF'], [100, '#1F6FEB']]}}
};
function plus(C, cx, cy, s, color, op){ var t = s * 0.3; C.rect(cx - s / 2, cy - t / 2, s, t, {fill:color, op:op, r:t * 0.2}); C.rect(cx - t / 2, cy - s / 2, t, s, {fill:color, op:op, r:t * 0.2}); }
/* heartbeat: a flat line with one pulse at px */
function beat(C, x0, x1, y, px, a, color, lw, op){
  var o = {color:color, lw:lw || 2, op:op == null ? 1 : op}, p = [[x0, y], [px - a * 1.1, y], [px - a * 0.7, y - a * 0.35], [px - a * 0.35, y + a * 0.3], [px, y - a], [px + a * 0.4, y + a * 0.7], [px + a * 0.75, y - a * 0.25], [px + a * 1.1, y], [x1, y]];
  for (var k = 0; k < p.length - 1; k++) C.line(p[k][0], p[k][1], p[k + 1][0], p[k + 1][1], o);
}
/* round photo with an offset ring and a plus badge */
function round(C, cx, cy, r, photo, dark){
  C.ell(cx - r - 16, cy - r + 14, r * 2, r * 2, {line:dark ? '#FFFFFF' : BL, lop:dark ? 0.5 : 1, lw:2.5});
  C.ell(cx - r - 9, cy - r - 9, r * 2 + 18, r * 2 + 18, {fill:'#FFFFFF'});
  if (photo) D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'});
  else { C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:140, stops:[[0, '#BBD4FF'], [100, '#1F6FEB']]}}); plus(C, cx, cy, r * 0.8, '#FFFFFF', 0.95); }
  var b = r * 0.2, bx = cx + r * 0.72, by = cy + r * 0.72;
  C.rect(bx - b - 5, by - b - 5, b * 2 + 10, b * 2 + 10, {fill:'#FFFFFF', r:b * 0.5 + 4}); C.rect(bx - b, by - b, b * 2, b * 2, {fill:NV, r:b * 0.5}); plus(C, bx, by, b * 1.1, '#FFFFFF');
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  plus(C, x + 11, y + 11, 22, light ? '#FFFFFF' : BL);
  C.text(x + 32, y, 420, 22, f.company, {font:'head', size:13, wt:700, color:light ? '#FFFFFF' : NV, va:'m', lh:1, ls:0.04, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  plus(C, M + 7, y + 8, 14, BL);
  C.text(M + 24, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1) + ' / ' + pad2(C.total || 1), {font:'body', size:11, wt:600, color:NV, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  C.rect(0, 62, 10, 58, {fill:BL});
  if (k === 0) { plus(C, W - 96, 70, 30, BL); plus(C, W - 58, 106, 16, NV); }
  else if (k === 1) beat(C, W - 250, W, 78, W - 140, 22, BL, 2.2);
  else { plus(C, W - 70, 66, 22, NV); plus(C, W - 108, 66, 22, BL, 0.55); plus(C, W - 146, 66, 22, BL, 0.25); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* quadrants: no boxes, thin divider lines that cross, a plus badge on every crossing */
function quads(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, n > 3 ? 2 : 1);
  var rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 26, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 54, ch - pad * 2 - 40, {hMax:21, dMax:15.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 46), tot = ch2 * rows, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3), c, r;
  for (c = 1; c < cols; c++) C.line(Z.x + c * cw, y0 + 6, Z.x + c * cw, y0 + tot - 6, {color:LN, lw:1.6});
  for (r = 1; r < rows; r++) C.line(Z.x + 6, y0 + r * ch2, Z.x + Z.w - 6, y0 + r * ch2, {color:LN, lw:1.6});
  for (c = 1; c < cols; c++) for (r = rows > 1 ? 1 : 0; r < (rows > 1 ? rows : 1); r++) { var px = Z.x + c * cw, py = rows > 1 ? y0 + r * ch2 : y0 + tot / 2; C.rect(px - 17, py - 17, 34, 34, {fill:'#FFFFFF'}); plus(C, px, py, 24, BL); }
  it.forEach(function(t, k){
    var rr = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * cw + pad, y = y0 + rr * ch2 + pad;
    C.text(x, y, 80, 30, pad2(k + 1), {font:'head', size:26, wt:800, color:BL, lh:1, caps:false});
    K.hd(C, x, y + 40, tw, ch2 - pad * 2 - 40, t, F, {tc:NV, mc:MUT});
  });
}
/* record: rows like a patient chart - navy header strip, striped rows, a number column */
function record(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), hd = 38, rh = Math.min((Z.h - hd) / n, 96), nw = 64, hw = hasH ? Math.min(300, Z.w * 0.3) : 0, pad = 18, tw = Z.w - nw - hw - pad * 2;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw - 14, rh - 14, {font:'head', max:17.5, min:11.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:hasH ? 15 : 19, min:10.5, lh:1.38});
  var tot = hd + rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x, y0, Z.w, hd, {fill:NV, r:10});
  C.text(Z.x, y0, nw, hd, 'No.', {font:'head', size:11.5, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:true, ls:0.1});
  if (hasH) C.text(Z.x + nw, y0, hw, hd, 'Topic', {font:'head', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.1});
  C.text(Z.x + nw + hw + (hasH ? pad : 0), y0, tw, hd, 'Details', {font:'head', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.1});
  it.forEach(function(t, k){
    var y = y0 + hd + k * rh;
    if (k % 2 === 0) C.rect(Z.x, y, Z.w, rh, {fill:SKY});
    C.line(Z.x, y + rh, Z.x + Z.w, y + rh, {color:LN, lw:1});
    C.text(Z.x, y, nw, rh, pad2(k + 1), {font:'head', size:Math.min(20, rh * 0.36), wt:800, color:BL, align:'c', va:'m', lh:1, caps:false});
    if (hasH) C.text(Z.x + nw, y, hw - 14, rh, t.h || '', {font:'head', size:hs, wt:700, color:NV, va:'m', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + (hasH ? pad : 0), y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* wards: outlined cards, a plus badge sits on the top edge of each */
function wards(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, top = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - top - (g + top) * (rows - 1)) / rows, pad = 18, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad - 30, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad + 40), tot = (ch2 + top) * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + top + r * (ch2 + top + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:LN, lw:1.6, r:14});
    C.rect(x + pad - 4, y - 16, 32, 32, {fill:k % 2 ? NV : BL, r:9}); plus(C, x + pad + 12, y, 16, '#FFFFFF');
    K.hd(C, x + pad, y + 28, tw, ch2 - pad - 28, t, F, {tc:NV, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* vitals: one heartbeat line crosses the slide, each number sits over its own pulse */
function vitals(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, tw = cw - 36, vals = st.map(sv), labs = st.map(sl), ly = Z.y + Math.min(Z.h * 0.52, 230);
  var vs = C.groupFit(vals, tw, ly - Z.y - 70, {font:'head', max:68, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.y + Z.h - ly - 110, {font:'body', max:14, min:10.5, lh:1.4});
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    beat(C, x, x + cw, ly, x + cw * 0.5, Math.min(38, cw * 0.14), k % 2 ? NV : BL, 2.6);
    C.text(x + 18, Z.y, tw, ly - Z.y - 50, vals[k], {font:'head', size:vs, wt:800, color:NV, align:'c', va:'b', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x + 18, ly + 52, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:BL, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 18, ly + 60 + le._h, tw, Z.y + Z.h - (ly + 60 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* course: a line with plus stops; steps sit above and below it in turn */
function course(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return record(C, Z, o);
  var cw = Z.w / n, tw = cw - 24, my = Z.y + Z.h / 2, half = Z.h / 2 - 34;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 30, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, half - 40, {font:'body', max:14.5, min:10.5, lh:1.4});
  C.line(Z.x, my, Z.x + Z.w, my, {color:NV, lw:2.4});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), x = cx - tw / 2, up = k % 2 === 0;
    C.rect(cx - 18, my - 18, 36, 36, {fill:up ? BL : NV, r:10}); plus(C, cx, my, 18, '#FFFFFF');
    C.line(cx, up ? my - 18 : my + 18, cx, up ? my - 34 : my + 34, {color:LN, lw:1.6});
    if (up) {
      var dh = C.measureText(t.d, tw, {font:'body', max:ds, min:ds, lh:1.4}).h, ty = my - 42 - dh;
      C.text(x, ty - 34, tw, 30, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:BL, align:'c', va:'b', lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, ty, tw, dh + 4, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      C.text(x, my + 42, tw, 30, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:NV, align:'c', lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, my + 42 + hs * 1.3 + 4, tw, half - 40, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    }
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:SKY, r:18}); C.rect(Z.x, Z.y, 12, Z.h, {fill:BL, r:6});
  plus(C, Z.x + Z.w - 60, Z.y + Z.h - 60, 54, BL, 0.16);
  var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:BL, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, 690, H, {fill:NV}); C.rect(690, 0, 12, H, {fill:BL});
  beat(C, 0, 690, 610, 470, 30, '#FFFFFF', 2, 0.22);
  plus(C, 610, 96, 44, BL); plus(C, 560, 140, 20, '#FFFFFF', 0.5);
  round(C, 930, 350, 215, photo, false);
  plus(C, 1180, 110, 34, BL); plus(C, 1140, 150, 16, NV); plus(C, 760, 620, 26, BL, 0.4);
  brand(C, 72, 60, true);
  C.text(72, 150, 540, 250, C.s.title, {font:'head', fit:{max:66, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  C.rect(72, 420, 84, 6, {fill:BL, r:3});
  if (C.s.subtitle) C.text(72, 446, 520, 90, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#C9D9F2', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) C.text(72, 640, 560, 24, who, {font:'head', size:14, wt:700, color:'#FFFFFF', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(590, 0, W - 590, H, {fill:NV}); C.rect(578, 0, 12, H, {fill:BL});
  round(C, 300, 350, 205, photo, false);
  plus(C, 80, 96, 34, BL); plus(C, 124, 132, 16, NV); plus(C, 500, 630, 26, BL, 0.4);
  beat(C, 590, W, 640, 1040, 30, '#FFFFFF', 2, 0.22);
  var x = 670, w = W - 670 - 72;
  var te = C.text(x, 110, w, 150, C.s.title || 'Thank You', {font:'head', fit:{max:70, min:32}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  C.rect(x, 280, 84, 6, {fill:BL, r:3});
  if (C.s.subtitle) C.text(x, 304, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:'#C9D9F2', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 410 + k * 56;
    C.ell(x, y, 40, 40, {fill:BL}); C.icon(x + 10, y + 10, 20, c.icon, '#FFFFFF');
    C.text(x + 56, y, w - 56, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 440, H, {fill:NV}); C.rect(440, 0, 10, H, {fill:BL});
  plus(C, 330, 560, 150, '#FFFFFF', 0.07); plus(C, 110, 600, 40, BL); beat(C, 0, 440, 470, 250, 26, '#FFFFFF', 2, 0.25);
  var te = C.text(72, 130, 320, 200, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1.02});
  if (C.s.subtitle) C.text(72, 130 + te._h + 16, 310, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#C9D9F2', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  record(C, {x:520, y:90, w:W - 520 - 72, h:H - 190}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BL, tc:NV}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 640 : W - 220;
  if (photo) round(C, 1010, 350, 180, true, false);
  plus(C, x + 22, 150, 44, BL);
  C.rect(x, 200, 6, 300, {fill:LN});
  var qe = C.text(x + 34, 200, w - 34, 280, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:700, color:NV, lh:1.22, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 34, 200 + qe._h + 24, w - 34, 24, bt, {font:'head', size:14, wt:700, color:BL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
  beat(C, x, x + w, 610, x + w * 0.7, 26, BL, 2, 0.5);
}
D.register({
  id:'careblue', name:'Care Blue', tag:'White · navy · plus signs',
  desc:'Clean white pages with navy panels, plus signs, a heartbeat line and round photos: built for healthcare, clinics, pharma, wellness and medical reports.',
  fonts:{head:'Figtree', body:'Figtree', headW:800, bodyW:400, headLh:1.06, gf:'Figtree:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Navy panel + round emblem', {}), V('v2', 'Navy panel + round photo', {img:'photo'})],
    closing:[V('v1', 'Round emblem + contacts', {}), V('v2', 'Round photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Navy panel + record', {fn:agendaA}), V('n2', 'Record rows', {body:record}), V('v2', 'Record + image right', {body:record, img:'right'})],
    text:[V('v1', 'Statement card', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Divided columns', {body:quads}), V('n2', 'Record rows', {body:record}), V('n3', 'Plus cards', {body:wards}), V('v2', 'Record + image right', {body:record, img:'right'}), V('v3', 'Image left + columns', {body:quads, img:'left', cols:1})],
    p3:[V('v1', 'Divided columns', {body:quads}), V('n2', 'Plus cards', {body:wards}), V('n3', 'Record rows', {body:record}), V('v2', 'Record + image right', {body:record, img:'right'}), V('v3', 'Image left + cards', {body:wards, img:'left', cols:1})],
    p4:[V('v1', 'Quadrants', {body:quads}), V('n2', 'Record rows', {body:record}), V('n3', 'Plus cards', {body:wards}), V('v2', 'Record + image right', {body:record, img:'right'}), V('v3', 'Image left + quadrants', {body:quads, img:'left', cols:2})],
    p56:[V('v1', 'Plus cards', {body:wards}), V('n2', 'Divided grid', {body:quads}), V('n3', 'Record rows', {body:record}), V('v2', 'Record + image right', {body:record, img:'right'})],
    p78:[V('v1', 'Plus cards', {body:wards}), V('n2', 'Record rows', {body:record}), V('v2', 'Cards + image right', {body:wards, img:'right', cols:2})],
    stats:[V('v1', 'Vitals', {body:vitals}), V('n2', 'Progress rings', {body:'statRings', track:LN}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Vitals + image right', {body:vitals, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Course line', {body:course}), V('n2', 'Record rows', {body:record}), V('n3', 'Plus cards', {body:wards}), V('v2', 'Record + image right', {body:record, img:'right'})],
    quote:[V('v1', 'Plain quote', {}), V('v2', 'Quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 53 - Silk Burgundy : deep burgundy page with flowing silk waves, gold arrows, tall pill photos, serif headings */
var BG = '#3D0A19', B2 = '#6E1A30', B3 = '#9C3350', GO = '#D9B26A', CR = '#F7ECDD', MU = '#D8BFC2';
var P0 = {bg:BG, text:CR, title:CR, muted:MU, accent:GO, accent2:B3, card:'#55142A', line:'#7A2B41', onAccent:'#3D0A19'};
var S = {
  margin:76, series:[GO, '#F7ECDD', B3, '#E58FA5', '#A88A4E', '#C96A84'],
  header:{kind:'left', top:64, tMax:48, tMin:28, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:0, fill:'#55142A', line:null, shadow:0, dark:true, tc:CR, mc:MU,
        hl:{kind:'solid', fill:GO, dark:false, tc:'#3D0A19', mc:'#5A2A1A'}},
  icon:{kind:'plain', fg:GO}, num:{kind:'big', fg:GO},
  conc:{kind:'bar', barC:GO, label:'In short', lc:GO, tc:CR},
  footer:{fn:foot}, imgFramed:true, imgR:120, bodyGap:26,
  imgPh:{grad:{ang:160, stops:[[0, '#9C3350'], [100, '#55142A']]}}
};
/* one silk fold: a band between two sine curves */
function silk(C, y, amp, ph, th, color, op, x0, x1){
  x0 = x0 == null ? 0 : x0; x1 = x1 == null ? W : x1;
  var d = [], n = 36, k, x, t;
  for (k = 0; k <= n; k++) { t = k / n; x = x0 + (x1 - x0) * t; d.push([k ? 'L' : 'M', x, y + Math.sin(t * 5.2 + ph) * amp]); }
  for (k = n; k >= 0; k--) { t = k / n; x = x0 + (x1 - x0) * t; d.push(['L', x, y + th * (0.55 + 0.45 * Math.sin(t * 3.1 + ph * 1.7)) + Math.sin(t * 4.3 + ph + 0.9) * amp * 1.15]); }
  d.push(['Z']); C.path(d, {fill:color, op:op});
}
function arrow(C, x, y, len, color, lw){ lw = lw || 2; C.line(x, y, x + len - 6, y, {color:color, lw:lw}); K.poly(C, [[x + len - 11, y - 6], [x + len, y], [x + len - 11, y + 6]], {fill:color}); }
function arrowUp(C, x, y, s, color){ C.line(x, y + s, x + s - 4, y + 4, {color:color, lw:2.4}); K.poly(C, [[x + s - 13, y + 1], [x + s, y], [x + s - 1, y + 13]], {fill:color}); }
/* tall pill photo with an offset gold outline */
function pill(C, x, y, w, h, photo){
  C.rect(x + 18, y - 18, w, h, {line:GO, lw:2, r:w / 2});
  if (photo) D.imgBox(C, x, y, w, h, {r:w / 2});
  else { C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#B24A66'], [100, '#55142A']]}, r:w / 2}); arrowUp(C, x + w / 2 - 34, y + h / 2 - 34, 68, GO); }
}
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  arrow(C, x, y + 11, 30, GO);
  C.text(x + 42, y, 420, 22, f.company, {font:'body', size:12.5, wt:500, color:CR, va:'m', lh:1, caps:true, ls:0.22, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 76, y = H - 38, f = C.cover;
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:400, color:MU, lh:1, va:'m', ls:0.1});
  arrow(C, W - M - 78, y + 8, 30, GO, 1.6);
  C.text(W - M - 40, y, 40, 16, pad2(C.i + 1), {font:'head', size:16, wt:700, color:CR, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:BG});
  var k = (C.i || 0) % 3;
  if (kind === 'cover' || kind === 'closing') { silk(C, 430, 60, 0.4, 250, B2, 0.75); silk(C, 520, 46, 1.6, 190, B3, 0.3); silk(C, 610, 34, 2.9, 170, '#1E040C', 0.5); return; }
  if (k === 0) { silk(C, 600, 26, 0.6, 150, B2, 0.7); silk(C, 650, 20, 2.2, 110, B3, 0.22); }
  else if (k === 1) { silk(C, -60, 24, 1.9, 130, B2, 0.6); silk(C, 640, 22, 3.4, 120, B2, 0.6); }
  else { silk(C, 590, 34, 3.1, 170, B2, 0.65); silk(C, 660, 16, 0.2, 90, '#1E040C', 0.45); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.45}).h : 0); }
/* arrow rows: gold arrow, serif heading on the left, text on the right, a hairline between rows */
function arrows(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), wide = Z.w > 760 && hasH, rh = Math.min(Z.h / n, 128), aw = 62;
  var hw = wide ? Math.min(330, Z.w * 0.32) : 0, tw = Z.w - aw - hw - (wide ? 28 : 0), F, hs = 0, ds = 0;
  if (wide) { hs = C.groupFit(it.map(function(t){ return t.h || ''; }), hw, rh - 20, {font:'head', max:25, min:14, maxLines:2}); ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 22, {font:'body', max:16, min:10.5, lh:1.45}); }
  else F = K.fitHD(C, it, tw, 30, rh - 20, {hMax:23, dMax:hasH ? 15 : 20, hMin:13, dMin:10.5});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    if (k) C.line(Z.x, y, Z.x + Z.w, y, {color:GO, lw:1, op:0.35});
    arrow(C, Z.x, y + rh / 2, 40, GO);
    if (wide) {
      C.text(Z.x + aw, y, hw, rh, t.h || '', {font:'head', size:hs, wt:700, color:CR, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
      C.text(Z.x + aw + hw + 28, y, tw, rh, t.d, {font:'body', size:ds, color:MU, va:'m', lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
    } else K.hd(C, Z.x + aw, y + Math.max(8, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:CR, mc:MU});
  });
}
/* pillars: columns split by gold hairlines, an italic numeral over each */
function pillars(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return arrows(C, Z, o);
  var cw = Z.w / n, pad = 26, tw = cw - pad * 2, top = 96;
  var F = K.fitHD(C, it, tw, 56, Z.h - top - 20, {hMax:25, dMax:15.5, hMin:14, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var tot = Math.min(Z.h, top + bmx + 24), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.line(x, y0, x, y0 + tot, {color:GO, lw:1, op:0.5});
    C.text(x + pad, y0, 110, 64, pad2(k + 1), {font:'head', size:48, wt:500, color:GO, lh:1, italic:true, caps:false});
    arrow(C, x + pad, y0 + 78, 46, GO, 1.6);
    K.hd(C, x + pad, y0 + top, tw, tot - top, t, F, {tc:CR, mc:MU});
  });
}
/* veils: sheer panels with a gold top line, for many points */
function veils(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 54, ch - pad * 2 - 26, {hMax:21, dMax:14.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 30), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', op:0.07}); C.rect(x, y, cw, 2.5, {fill:GO});
    C.text(x + pad, y + pad - 4, 60, 22, pad2(k + 1), {font:'head', size:19, wt:500, color:GO, lh:1, italic:true, caps:false});
    K.hd(C, x + pad, y + pad + 26, tw, ch2 - pad * 2 - 26, t, F, {tc:CR, mc:MU});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* rise: large serif numbers, a gold arrow rising beside each */
function rise(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, pad = 22, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, tw - 60, 120, {font:'head', max:70, min:24, maxLines:1});
  var ls = C.groupFit(labs, tw, 44, {font:'body', max:14, min:11, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - 230, {font:'body', max:14, min:10.5, lh:1.45});
  var y0 = Z.y + Math.max(0, (Z.h - 300) * 0.25);
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.line(x, y0 + 10, x, y0 + 250, {color:GO, lw:1, op:0.4});
    arrowUp(C, x + pad, y0 + 6, 34, GO);
    C.text(x + pad, y0 + 46, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:600, color:CR, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y0 + 46 + vs * 1.1 + 12;
    var le = C.text(x + pad, ly, tw, 44, labs[k], {font:'body', size:ls, wt:600, color:GO, lh:1.25, caps:true, ls:0.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + le._h + 10, tw, Z.y + Z.h - (ly + le._h + 10), t.x, {font:'body', size:ds, color:MU, lh:1.45, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* path: one long gold arrow, dates above it and notes below */
function path(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return arrows(C, Z, o);
  var cw = (Z.w - 30) / n, tw = cw - 26, ly = Z.y + Math.min(110, Z.h * 0.3);
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 50, {font:'head', max:32, min:15, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.y + Z.h - ly - 40, {font:'body', max:15, min:10.5, lh:1.45});
  arrow(C, Z.x, ly, Z.w, GO, 2);
  it.forEach(function(t, k){
    var x = Z.x + k * cw;
    C.line(x + 1, ly - 12, x + 1, ly + 12, {color:GO, lw:2});
    C.text(x, ly - 22 - hs * 1.1, tw, hs * 1.15, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:CR, lh:1, italic:true, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x, ly + 30, tw, Z.y + Z.h - ly - 30, t.d, {font:'body', size:ds, color:MU, lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.line(Z.x, Z.y + 4, Z.x, Z.y + Z.h - 4, {color:GO, lw:2});
  var pad = 40, y = Z.y + 10, w = Z.w - pad;
  arrow(C, Z.x + pad, y + 8, 46, GO); y += 34;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 90, it.h, {font:'head', fit:{max:38, min:20}, wt:700, color:GO, lh:1.08, italic:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - 10 - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:CR, lh:1.52, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  pill(C, 860, 96, 300, 528, photo);
  brand(C, 76, 64);
  C.text(76, 150, 700, 270, C.s.title, {font:'head', fit:{max:76, min:34}, wt:700, color:CR, ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  arrow(C, 76, 452, 120, GO, 2.4);
  if (C.s.subtitle) C.text(76, 478, 620, 90, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:CR, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  —  ');
  if (who) C.text(76, 626, 700, 24, who, {font:'body', size:13.5, wt:500, color:GO, lh:1.1, caps:true, ls:0.16, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  pill(C, 96, 96, 290, 528, photo);
  var x = 500, w = W - x - 76;
  C.text(x, 110, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:36}, wt:700, color:CR, ed:{f:'title'}, maxLines:2, va:'b', lh:1.04, italic:true});
  arrow(C, x, 306, 120, GO, 2.4);
  if (C.s.subtitle) C.text(x, 328, w, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:CR, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 436 + k * 54;
    C.icon(x, y + 8, 24, c.icon, GO);
    C.text(x + 44, y, w - 44, 40, c.val, {font:'body', fit:{max:18, min:12}, wt:500, color:CR, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    C.line(x, y + 47, x + Math.min(w, 520), y + 47, {color:GO, lw:1, op:0.35});
  });
}
function agendaA(C, o){
  var te = C.text(76, 120, 380, 240, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:700, color:CR, ed:{f:'title'}, maxLines:2, va:'t', lh:1.04, italic:true});
  arrow(C, 76, 120 + te._h + 22, 100, GO, 2.4);
  if (C.s.subtitle) C.text(76, 120 + te._h + 46, 360, 80, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.line(500, 90, 500, H - 110, {color:GO, lw:1, op:0.5});
  arrows(C, {x:548, y:90, w:W - 548 - 76, h:H - 200}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:GO, tc:CR}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 640 : W - 220;
  if (photo) pill(C, 900, 110, 260, 480, true);
  C.text(x - 6, 96, 120, 120, '“', {font:'head', size:170, wt:700, color:GO, lh:1, caps:false, ls:0});
  var qe = C.text(x, 210, w, 280, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:600, color:CR, lh:1.14, italic:true, ed:{f:'title'}, maxLines:5, va:'t'});
  arrow(C, x, 210 + qe._h + 30, 60, GO);
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 76, 210 + qe._h + 18, w - 76, 24, bt, {font:'body', size:13.5, wt:500, color:GO, lh:1.1, caps:true, ls:0.16, va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'silkburgundy', name:'Silk Burgundy', tag:'Dark · burgundy silk · gold',
  desc:'A deep burgundy page with flowing silk folds, fine gold arrows, tall pill photos and serif headings: rich and formal for proposals, luxury, hospitality and client pitches.',
  fonts:{head:'Lora', body:'Albert Sans', headW:700, bodyW:400, headLh:1.06, gf:'Lora:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700|Albert+Sans:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Silk + gold pill', {}), V('v2', 'Silk + pill photo', {img:'photo'})],
    closing:[V('v1', 'Gold pill + contacts', {}), V('v2', 'Pill photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + arrow rows', {fn:agendaA}), V('n2', 'Arrow rows', {body:arrows}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'})],
    text:[V('v1', 'Statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Pillars', {body:pillars}), V('n2', 'Arrow rows', {body:arrows}), V('n3', 'Veils', {body:veils}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:1})],
    p3:[V('v1', 'Pillars', {body:pillars}), V('n2', 'Arrow rows', {body:arrows}), V('n3', 'Veils', {body:veils}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:1})],
    p4:[V('v1', 'Arrow rows', {body:arrows}), V('n2', 'Pillars', {body:pillars}), V('n3', 'Veils', {body:veils}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:2})],
    p56:[V('v1', 'Veils', {body:veils}), V('n2', 'Arrow rows', {body:arrows}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:2})],
    p78:[V('v1', 'Veils', {body:veils}), V('v2', 'Veils + image right', {body:veils, img:'right', cols:2})],
    stats:[V('v1', 'Rising numbers', {body:rise}), V('n2', 'Progress rings', {body:'statRings', track:'#7A2B41'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + image right', {body:rise, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gold arrow path', {body:path}), V('n2', 'Arrow rows', {body:arrows}), V('n3', 'Pillars', {body:pillars}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'})],
    quote:[V('v1', 'Serif quote', {}), V('v2', 'Quote + pill photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 54 - Ribbon Pine : cream page, pine ribbons that fold round cards, hanging banners, an accordion-fold timeline */
var CRM = '#F5F1E6', PN = '#1E4A38', DP = '#102B20', MO = '#8DB27A', GD = '#D8A23A', GDD = '#9A6E1B', INK = '#17231C', MUT = '#5F6B62', LN = '#DDD6C2';
var P0 = {bg:CRM, text:INK, title:PN, muted:MUT, accent:PN, accent2:GD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[PN, GD, MO, '#3F7A5E', '#B9B29C', '#17231C'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:6, fill:'#FFFFFF', line:LN, shadow:0, dark:false, tc:PN, mc:MUT,
        hl:{kind:'solid', fill:PN, dark:true, tc:'#FFFFFF', mc:'#CFE0D2'}},
  icon:{kind:'square', bg:PN, fg:'#FFFFFF', r:4}, num:{kind:'big', fg:GD},
  conc:{kind:'bar', barC:GD, label:'Takeaway', lc:GDD, tc:PN},
  footer:{fn:foot}, imgFramed:true, imgR:8, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#8DB27A'], [100, '#1E4A38']]}}
};
/* a ribbon that wraps round the left edge of a card: the band plus the small dark fold under it */
function wrap(C, x, y, w, h, color, dark, f){ f = f || 12; K.poly(C, [[x, y + h], [x + f, y + h], [x + f, y + h + f]], {fill:dark}); C.rect(x, y, w, h, {fill:color}); }
/* a ribbon tail with a V cut at the right end */
function tail(C, x, y, w, h, color, n){ n = n || h * 0.4; K.poly(C, [[x, y], [x + w, y], [x + w - n, y + h / 2], [x + w, y + h], [x, y + h]], {fill:color}); }
/* a hanging banner with a V cut at the bottom */
function banner(C, x, y, w, h, color, n){ n = n || w * 0.32; K.poly(C, [[x, y], [x + w, y], [x + w, y + h], [x + w / 2, y + h - n], [x, y + h]], {fill:color}); }
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  banner(C, x, y - 2, 16, 26, GD);
  C.text(x + 28, y, 420, 22, f.company, {font:'head', size:14, wt:700, color:light ? '#FFFFFF' : PN, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.line(M, y - 12, W - M, y - 12, {color:LN, lw:1});
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11.5, wt:500, color:MUT, lh:1, va:'m'});
  tail(C, W - 60, y - 3, 60, 22, PN, 8);
  C.text(W - 60, y - 3, 46, 22, pad2(C.i + 1), {font:'head', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:CRM});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { banner(C, W - 132, 0, 34, 92, PN); banner(C, W - 90, 0, 22, 62, GD); }
  else if (k === 1) { wrap(C, W - 190, 66, 190, 26, GD, GDD, 0); K.poly(C, [[W - 190, 66], [W - 176, 79], [W - 190, 92]], {fill:CRM}); }
  else { banner(C, W - 118, 0, 26, 74, MO); banner(C, W - 84, 0, 26, 104, PN); }
  C.rect(0, 62, 8, 58, {fill:GD});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* wraps: white cards; the heading rides on a ribbon that folds round the card's left edge */
function wraps(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), g = 22, f = 12, rh = Math.min((Z.h - g * (n - 1) - f) / n, 124), lw = hasH ? Math.min(300, Z.w * 0.32) : 84, pad = 22, tw = Z.w - f - lw - pad * 2, bh = Math.min(rh - 22, 64);
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), lw - 30, bh - 8, {font:'head', max:18, min:11.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 18, {font:'body', max:hasH ? 15.5 : 19, min:10.5, lh:1.42});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot - f) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), alt = k % 2;
    C.rect(Z.x + f, y, Z.w - f, rh, {fill:'#FFFFFF', line:LN, lw:1, r:4});
    wrap(C, Z.x, y + (rh - bh) / 2, lw + f, bh, alt ? GD : PN, alt ? GDD : DP, f);
    C.text(Z.x + 18, y + (rh - bh) / 2, lw - 20, bh, hasH ? (t.h || '') : pad2(k + 1), {font:'head', size:hasH ? hs : 24, wt:700, color:alt ? DP : '#FFFFFF', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + f + lw + pad, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.42, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* bookmarks: white columns, a banner with the number hangs over the top edge of each */
function bookmarks(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return wraps(C, Z, o);
  var g = 22, cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 96, up = 14;
  var F = K.fitHD(C, it, tw, 56, Z.h - up - top - pad, {hMax:21, dMax:15.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - up, top + bmx + pad + 8), y0 = Z.y + up + Math.max(0, (Z.h - up - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), alt = k % 2;
    C.rect(x, y0, cw, ch, {fill:'#FFFFFF', line:LN, lw:1, r:4});
    banner(C, x + pad, y0 - up, 46, 88, alt ? GD : PN);
    C.text(x + pad, y0 - up + 6, 46, 50, pad2(k + 1), {font:'head', size:19, wt:700, color:alt ? DP : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad, y0 + top, tw, ch - top - pad, t, F, {tc:PN, mc:MUT});
  });
}
/* slips: small white cards with a pine edge and a little ribbon tail carrying the number */
function slips(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 18, tw = cw - pad * 2 - 6;
  var F = K.fitHD(C, it, tw, 50, ch - pad - 46, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad + 52), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:LN, lw:1, r:4}); C.rect(x, y, 6, ch2, {fill:PN});
    tail(C, x, y + 14, 58, 22, k % 2 ? GD : PN, 8);
    C.text(x + 6, y + 14, 40, 22, pad2(k + 1), {font:'head', size:11.5, wt:700, color:k % 2 ? DP : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad + 6, y + 46, tw, ch2 - pad - 46, t, F, {tc:PN, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: a wide hanging banner per number, hung from one rod */
function hung(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, bw = Math.min(cw - 44, 230), bh = Math.min(Z.h * 0.56, 230), tw = cw - 30, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, bw - 30, bh * 0.45, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 48, {font:'head', max:17.5, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - bh - 80, {font:'body', max:14, min:10.5, lh:1.4});
  C.rect(Z.x, Z.y + 4, Z.w, 6, {fill:DP, r:3});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), alt = k % 2;
    banner(C, cx - bw / 2, Z.y + 10, bw, bh, alt ? GD : PN, bw * 0.2);
    C.text(cx - bw / 2 + 15, Z.y + 10, bw - 30, bh - bw * 0.2, vals[k], {font:'head', size:vs, wt:700, color:alt ? DP : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = Z.y + bh + 26;
    var le = C.text(cx - tw / 2, ly, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:PN, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, ly + le._h + 8, tw, Z.y + Z.h - (ly + le._h + 8), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: one strip folded like an accordion, a date on every fold */
function accordion(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return wraps(C, Z, o);
  var cw = Z.w / n, d = 26, bh = 70, y0 = Z.y + 6, tw = cw - 26;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - 30, 40, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - bh - d - 50, {font:'body', max:15, min:10.5, lh:1.42});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, a = k % 2 ? d : 0, b = k % 2 ? 0 : d;
    K.poly(C, [[x, y0 + a], [x + cw, y0 + b], [x + cw, y0 + b + bh], [x, y0 + a + bh]], {fill:k % 2 ? DP : PN});
    C.text(x + 14, y0 + d / 2, cw - 28, bh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:k % 2 ? GD : '#FFFFFF', align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    banner(C, x + cw / 2 - 7, y0 + bh + d / 2 + 6, 14, 30, GD, 6);
    C.text(x + 13, y0 + bh + d + 34, tw, Z.y + Z.h - (y0 + bh + d + 34), t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.42, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var f = 12; C.rect(Z.x + f, Z.y, Z.w - f, Z.h, {fill:'#FFFFFF', line:LN, lw:1, r:4});
  var pad = 46, y = Z.y + 34, w = Z.w - f - pad * 2;
  if (it.h) { wrap(C, Z.x, y, Math.min(Z.w * 0.6, 460), 52, PN, DP, f); C.text(Z.x + 26, y, Math.min(Z.w * 0.6, 460) - 40, 52, it.h, {font:'head', fit:{max:22, min:13}, wt:700, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += 52 + 34; }
  C.text(Z.x + f + pad, y, w, Z.y + Z.h - 34 - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function photoCol(C, x, photo){
  if (photo) D.imgBox(C, x, 0, 330, H, {r:0}); else { C.rect(x, 0, 330, H, {grad:{ang:170, stops:[[0, '#8DB27A'], [100, '#1E4A38']]}}); banner(C, x + 120, 250, 90, 190, '#FFFFFF', 30); banner(C, x + 134, 250, 62, 150, GD, 22); }
  banner(C, x + 40, 0, 54, 150, GD); banner(C, x + 104, 0, 30, 96, CRM);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  photoCol(C, 880, photo);
  brand(C, 72, 60);
  K.poly(C, [[0, 430], [14, 430], [14, 444]], {fill:DP});
  tail(C, 0, 190, 960, 240, PN, 60);
  C.text(72, 206, 780, 208, C.s.title, {font:'head', fit:{max:68, min:30}, wt:700, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'m', lh:1.04});
  if (C.s.subtitle) C.text(72, 470, 700, 90, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:INK, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.rect(72, 612, 46, 4, {fill:GD}); C.text(72, 628, 700, 24, who, {font:'head', size:15, wt:700, color:PN, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  photoCol(C, 70, photo);
  K.poly(C, [[W, 330], [W - 14, 330], [W - 14, 344]], {fill:DP});
  K.poly(C, [[W, 150], [330, 150], [384, 240], [330, 330], [W, 330]], {fill:PN});
  var x = 470, w = W - x - 72;
  C.text(x, 162, w, 156, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:32}, wt:700, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(x, 366, w, 60, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:INK, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 456 + k * 54;
    tail(C, x, y, 58, 38, k % 2 ? GD : PN, 12); C.icon(x + 11, y + 9, 20, c.icon, k % 2 ? DP : '#FFFFFF');
    C.text(x + 76, y, w - 76, 38, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  K.poly(C, [[0, 310], [14, 310], [14, 324]], {fill:DP});
  tail(C, 0, 130, 470, 180, PN, 46);
  C.text(72, 140, 330, 160, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:700, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(72, 350, 340, 80, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  banner(C, 72, 470, 40, 120, GD); banner(C, 124, 470, 40, 84, MO);
  wraps(C, {x:520, y:80, w:W - 520 - 72, h:H - 180}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:GD, tc:PN}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 660 : W - 220;
  if (photo) { D.imgBox(C, 880, 110, 300, 480, {r:6}); banner(C, 910, 110, 40, 110, GD); }
  C.rect(x + 12, 150, w - 12, 400, {fill:'#FFFFFF', line:LN, lw:1, r:4});
  banner(C, x + 50, 130, 60, 110, PN); C.text(x + 50, 150, 60, 60, '”', {font:'head', size:54, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  var qe = C.text(x + 56, 264, w - 100, 210, C.s.title, {font:'head', fit:{max:photo ? 30 : 38, min:18}, wt:700, color:PN, lh:1.22, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 56, 264 + qe._h + 20, w - 100, 24, bt, {font:'body', size:14, wt:600, color:GDD, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'ribbonpine', name:'Ribbon Pine', tag:'Cream · pine ribbons · gold',
  desc:'A cream page with pine-green ribbons that fold round the cards, hanging gold banners and an accordion timeline: steady and trustworthy for business plans, strategy and finance.',
  fonts:{head:'Bitter', body:'Karla', headW:700, bodyW:400, headLh:1.08, gf:'Bitter:wght@500;600;700|Karla:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Title ribbon + pine column', {}), V('v2', 'Title ribbon + photo column', {img:'photo'})],
    closing:[V('v1', 'Ribbon + contacts', {}), V('v2', 'Photo column + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title ribbon + wrapped rows', {fn:agendaA}), V('n2', 'Wrapped rows', {body:wraps}), V('v2', 'Slips + image right', {body:slips, img:'right', cols:1})],
    text:[V('v1', 'Ribbon statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Bookmarks', {body:bookmarks}), V('n2', 'Wrapped rows', {body:wraps}), V('n3', 'Slips', {body:slips}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:1})],
    p3:[V('v1', 'Wrapped rows', {body:wraps}), V('n2', 'Bookmarks', {body:bookmarks}), V('n3', 'Slips', {body:slips}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:1})],
    p4:[V('v1', 'Bookmarks', {body:bookmarks}), V('n2', 'Wrapped rows', {body:wraps}), V('n3', 'Slips', {body:slips}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:2})],
    p56:[V('v1', 'Slips', {body:slips}), V('n2', 'Wrapped rows', {body:wraps}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:2})],
    p78:[V('v1', 'Slips', {body:slips}), V('v2', 'Slips + image right', {body:slips, img:'right', cols:2})],
    stats:[V('v1', 'Hanging banners', {body:hung}), V('n2', 'Progress rings', {body:'statRings', track:LN}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Banners + image right', {body:hung, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Accordion fold', {body:accordion}), V('n2', 'Wrapped rows', {body:wraps}), V('n3', 'Bookmarks', {body:bookmarks}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'})],
    quote:[V('v1', 'Banner quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 55 - Pulse Aqua : bright aqua-to-blue gradient, halftone dots, pill bars, a soft stage for a cut-out picture */
var AQ = '#00B8D9', BL = '#0A6CCF', DK = '#062A63', CO = '#FF6B5E', ICE = '#E4F6FB', INK = '#0C2140', MUT = '#587089', LN = '#BFE6F1';
var G = {ang:135, stops:[[0, '#12C8DC'], [100, '#0A5FCB']]};
var GP = {ang:0, stops:[[0, '#12C8DC'], [100, '#0A6CCF']]};
var P0 = {bg:'#FFFFFF', text:INK, title:DK, muted:MUT, accent:AQ, accent2:CO, card:ICE, line:LN, onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[AQ, BL, CO, DK, '#8ADFEE', '#FFB3AB'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:24, fill:ICE, line:null, shadow:0, dark:false, tc:DK, mc:MUT,
        hl:{kind:'solid', fill:BL, dark:true, tc:'#FFFFFF', mc:'#D5ECFF'}},
  icon:{kind:'circle', bg:AQ, fg:'#FFFFFF'}, num:{kind:'big', fg:BL},
  conc:{kind:'bar', barC:CO, label:'In short', lc:CO, tc:DK},
  footer:{fn:foot}, imgFramed:true, imgR:36, bodyGap:26,
  imgPh:{grad:G}
};
function pb(C, x, y, w, h, o){ o = o || {}; C.rect(x, y, w, h, {fill:o.fill, grad:o.grad, op:o.op, line:o.line, lw:o.lw, r:h / 2}); }
/* stage: a tall soft panel that a cut-out person or object can stand in */
function stage(C, x, y, w, h, photo, light){
  K.halftone(C, x + w - 70, y - 40, 9, 9, 18, 5, light ? '#FFFFFF' : AQ, light ? 0.5 : 0.6);
  C.rect(x, y, w, h, {fill:'#FFFFFF', op:light ? 0.2 : 1, grad:light ? null : G, r:44});
  if (photo) D.imgBox(C, x + 14, y + 14, w - 28, h - 28, {r:32});
  else { pb(C, x + w * 0.2, y + h * 0.3, w * 0.6, 34, {fill:'#FFFFFF', op:0.9}); pb(C, x + w * 0.2, y + h * 0.3 + 52, w * 0.42, 34, {fill:'#FFFFFF', op:0.55}); pb(C, x + w * 0.2, y + h * 0.3 + 104, w * 0.52, 34, {fill:CO}); }
  pb(C, x - 40, y + h - 120, 150, 40, {fill:CO}); pb(C, x - 10, y + h - 68, 96, 26, {fill:light ? '#FFFFFF' : DK});
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  pb(C, x, y + 3, 30, 16, {fill:light ? '#FFFFFF' : AQ}); C.ell(x + 16, y + 5, 12, 12, {fill:CO});
  C.text(x + 42, y, 420, 22, f.company, {font:'head', size:14, wt:700, color:light ? '#FFFFFF' : DK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  pb(C, M, y + 3, 44, 10, {grad:GP}); pb(C, M + 50, y + 3, 10, 10, {fill:CO});
  C.text(M + 72, y, 500, 16, f.website || f.company || '', {font:'body', size:11.5, wt:500, color:MUT, lh:1, va:'m'});
  pb(C, W - M - 54, y - 5, 54, 26, {fill:ICE});
  C.text(W - M - 54, y - 5, 54, 26, pad2(C.i + 1), {font:'head', size:12.5, wt:700, color:BL, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  if (kind === 'cover' || kind === 'closing') { C.bg({fill:'#0A6CCF'}); C.rect(0, 0, W, H, {grad:G}); return; }
  C.bg({fill:'#FFFFFF'});
  var k = (C.i || 0) % 3;
  if (k === 0) K.halftone(C, W - 150, 20, 8, 5, 18, 5, AQ, 0.55);
  else if (k === 1) { pb(C, W - 190, 40, 230, 30, {grad:GP}); pb(C, W - 120, 82, 160, 18, {fill:CO}); }
  else { K.halftone(C, W - 110, 24, 6, 4, 18, 4.5, BL, 0.4); pb(C, W - 250, 46, 110, 20, {fill:ICE}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* pill bars: every point is one long pill with a gradient cap for its number */
function bars(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 14, rh = Math.min((Z.h - g * (n - 1)) / n, 108), cap = Math.max(rh * 1.25, 84), tw = Z.w - cap - 24 - rh * 0.5;
  var F = K.fitHD(C, it, tw, 30, rh - 18, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 14.5 : 19, hMin:12.5, dMin:10.5});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    pb(C, Z.x, y, Z.w, rh, {fill:ICE});
    pb(C, Z.x, y, cap, rh, k % 3 === 2 ? {fill:CO} : {grad:GP});
    C.text(Z.x, y, cap, rh, pad2(k + 1), {font:'head', size:Math.min(28, rh * 0.36), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, Z.x + cap + 24, y + Math.max(7, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:DK, mc:MUT});
  });
}
/* capsules: soft columns that sit at alternating heights, a pill label on top of each */
function capsules(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return bars(C, Z, o);
  var g = 20, off = Math.min(26, Z.h * 0.07), cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 64;
  var F = K.fitHD(C, it, tw, 56, Z.h - off - top - pad - 18, {hMax:20, dMax:15, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - off - 18, top + bmx + pad + 10), y0 = Z.y + 18 + Math.max(0, (Z.h - off - 18 - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = y0 + (k % 2 ? off : 0);
    C.rect(x, y, cw, ch, {fill:ICE, r:32});
    pb(C, x + pad, y - 18, 86, 36, k % 3 === 2 ? {fill:CO} : {grad:GP});
    C.text(x + pad, y - 18, 86, 36, 'No. ' + (k + 1), {font:'head', size:13, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad, y + top - 24, tw, ch - top, t, F, {tc:DK, mc:MUT});
  });
}
/* bubbles: white tiles with an aqua outline and two small pills in the corner */
function bubbles(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 22, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 26), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:LN, lw:1.8, r:22});
    pb(C, x + pad, y + pad - 2, 34, 10, {grad:GP}); pb(C, x + pad + 40, y + pad - 2, 10, 10, {fill:CO});
    K.hd(C, x + pad, y + pad + 22, tw, ch2 - pad * 2 - 22, t, F, {tc:DK, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: an upright tube that fills from the bottom, the number beside it */
function tubes(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, tb = Math.min(64, cw * 0.2), th = Math.min(Z.h - 10, 300), tw = cw - tb - 46, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, tw, 80, {font:'head', max:60, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 52, {font:'head', max:17.5, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, th - 150, {font:'body', max:14, min:10.5, lh:1.4});
  var y0 = Z.y + Math.max(0, (Z.h - th) * 0.3), fr = [0.72, 0.5, 0.86, 0.62];
  st.forEach(function(t, k){
    var x = Z.x + k * cw + 8, fh = Math.max(tb, th * fr[k % 4]);
    C.rect(x, y0, tb, th, {fill:ICE, r:tb / 2});
    C.rect(x, y0 + th - fh, tb, fh, k % 3 === 2 ? {fill:CO, r:tb / 2} : {grad:{ang:90, stops:[[0, '#12C8DC'], [100, '#0A6CCF']]}, r:tb / 2});
    C.ell(x + tb * 0.25, y0 + th - fh + tb * 0.25, tb * 0.5, tb * 0.5, {fill:'#FFFFFF', op:0.9});
    var tx = x + tb + 22;
    C.text(tx, y0 + 6, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:DK, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(tx, y0 + 6 + vs * 1.1 + 10, tw, 52, labs[k], {font:'head', size:ls, wt:700, color:BL, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(tx, y0 + 6 + vs * 1.1 + 20 + le._h, tw, th - (vs * 1.1 + 30 + le._h), t.x, {font:'body', size:ds, color:MUT, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: pills that step to the right like a plan chart */
function plan(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2) return bars(C, Z, o);
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 92), step = Math.min(70, Z.w * 0.28 / (n - 1)), bw = Z.w - step * (n - 1), cap = Math.min(150, bw * 0.24), tw = bw - cap - 20 - rh * 0.5;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cap - 24, rh - 12, {font:'head', max:24, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 14, {font:'body', max:16, min:10.5, lh:1.38});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * step, y = y0 + k * (rh + g);
    if (k) C.line(x - step + rh / 2, y - g - rh / 2 + rh / 2, x - step + rh / 2, y + rh / 2, {color:LN, lw:2});
    if (k) C.line(x - step + rh / 2, y + rh / 2, x, y + rh / 2, {color:LN, lw:2});
    pb(C, x, y, bw, rh, {fill:ICE});
    pb(C, x, y, cap, rh, k === n - 1 ? {fill:CO} : {grad:GP});
    C.text(x + 12, y, cap - 24, rh, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + cap + 20, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:G, r:36});
  K.halftone(C, Z.x + Z.w - 150, Z.y + Z.h - 100, 8, 5, 18, 5, '#FFFFFF', 0.35);
  var pad = 50, y = Z.y + pad, w = Z.w - pad * 2;
  pb(C, Z.x + pad, y, 60, 12, {fill:'#FFFFFF'}); pb(C, Z.x + pad + 68, y, 12, 12, {fill:CO}); y += 34;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:'#FFFFFF', lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:'#FFFFFF', lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  K.halftone(C, 30, 520, 12, 10, 20, 6, '#FFFFFF', 0.22);
  pb(C, -60, 96, 190, 22, {fill:'#FFFFFF', op:0.25});
  stage(C, 790, 110, 400, 660, photo, true);
  brand(C, 72, 56, true);
  C.text(72, 150, 660, 260, C.s.title, {font:'head', fit:{max:76, min:32}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:1.02});
  pb(C, 72, 430, 90, 10, {fill:CO});
  if (C.s.subtitle) C.text(72, 460, 620, 90, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:'#FFFFFF', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var ww = Math.min(620, who.length * 8.6 + 56); pb(C, 72, 600, ww, 44, {fill:'#FFFFFF'}); C.text(72 + 24, 600, ww - 48, 44, who, {font:'head', size:14.5, wt:700, color:BL, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  K.halftone(C, W - 250, 30, 12, 8, 20, 6, '#FFFFFF', 0.22);
  stage(C, 110, 110, 380, 660, photo, true);
  var x = 590, w = W - x - 72;
  C.text(x, 110, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  pb(C, x, 300, 90, 10, {fill:CO});
  if (C.s.subtitle) C.text(x, 328, w, 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#FFFFFF', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 420 + k * 62;
    pb(C, x, y, Math.min(w, 520), 48, {fill:'#FFFFFF'});
    pb(C, x + 5, y + 5, 54, 38, {grad:GP}); C.icon(x + 22, y + 14, 20, c.icon, '#FFFFFF');
    C.text(x + 76, y, Math.min(w, 520) - 100, 48, c.val, {font:'body', fit:{max:16.5, min:12}, wt:600, color:DK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(40, 40, 400, H - 80, {grad:G, r:44});
  K.halftone(C, 80, 500, 9, 6, 20, 6, '#FFFFFF', 0.3);
  pb(C, 300, 430, 200, 44, {fill:CO}); pb(C, 360, 486, 120, 28, {fill:DK});
  var te = C.text(86, 120, 320, 200, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1.02});
  if (C.s.subtitle) C.text(86, 120 + te._h + 16, 300, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FFFFFF', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  bars(C, {x:540, y:80, w:W - 540 - 72, h:H - 180}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:AQ, tc:DK}); return; }
  var photo = o.img === 'photo', x = 100, w = photo ? 660 : W - 200;
  if (photo) { C.rect(860, 110, 320, 480, {grad:G, r:44}); D.imgBox(C, 874, 124, 292, 452, {r:32}); }
  pb(C, x, 130, 96, 56, {grad:GP}); C.text(x, 142, 96, 56, '”', {font:'head', size:56, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  pb(C, x + 106, 148, 20, 20, {fill:CO});
  var qe = C.text(x, 222, w, 260, C.s.title, {font:'head', fit:{max:photo ? 34 : 44, min:18}, wt:700, color:DK, lh:1.2, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d, bw = Math.min(w, bt.length * 8.4 + 52); pb(C, x, 222 + qe._h + 26, bw, 40, {fill:ICE}); C.text(x + 22, 222 + qe._h + 26, bw - 44, 40, bt, {font:'head', size:13.5, wt:700, color:BL, va:'m', lh:1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'pulseaqua', name:'Pulse Aqua', tag:'Aqua gradient · pills · dots',
  desc:'A bright aqua-to-blue gradient with halftone dots, pill bars and a soft stage made for cut-out people and objects: fresh and friendly for healthcare, wellness, apps and services.',
  fonts:{head:'Onest', body:'Onest', headW:800, bodyW:400, headLh:1.08, gf:'Onest:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Gradient + stage', {}), V('v2', 'Gradient + photo stage', {img:'photo'})],
    closing:[V('v1', 'Stage + contact pills', {}), V('v2', 'Photo stage + contact pills', {img:'photo'})],
    agenda:[V('v1', 'Gradient panel + pill bars', {fn:agendaA}), V('n2', 'Pill bars', {body:bars}), V('v2', 'Pill bars + image right', {body:bars, img:'right'})],
    text:[V('v1', 'Gradient statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Capsules', {body:capsules}), V('n2', 'Pill bars', {body:bars}), V('n3', 'Bubbles', {body:bubbles}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:1})],
    p3:[V('v1', 'Capsules', {body:capsules}), V('n2', 'Pill bars', {body:bars}), V('n3', 'Bubbles', {body:bubbles}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:1})],
    p4:[V('v1', 'Pill bars', {body:bars}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Bubbles', {body:bubbles}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:2})],
    p56:[V('v1', 'Bubbles', {body:bubbles}), V('n2', 'Pill bars', {body:bars}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:2})],
    p78:[V('v1', 'Bubbles', {body:bubbles}), V('n2', 'Pill bars', {body:bars}), V('v2', 'Bubbles + image right', {body:bubbles, img:'right', cols:2})],
    stats:[V('v1', 'Tubes', {body:tubes}), V('n2', 'Progress rings', {body:'statRings', track:LN}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Tubes + image right', {body:tubes, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Plan chart', {body:plan}), V('n2', 'Pill bars', {body:bars}), V('n3', 'Capsules', {body:capsules}), V('v2', 'Plan chart + image right', {body:plan, img:'right'})],
    quote:[V('v1', 'Pill quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 56 - Frost Orbit : near-black page, vivid orange and violet spheres with gold rings, frosted glass panels that blur whatever lies under them */
var BG = '#070716', OR = '#FF6A1F', OR2 = '#B81D05', VI = '#5A46FF', VI2 = '#150A9E', GO = '#F4B93A', LI = '#D6FF3F', TX = '#FFFFFF', MU = '#C3C5DC';
var P0 = {bg:BG, text:TX, title:TX, muted:MU, accent:LI, accent2:VI, card:'#17172E', line:'#34345A', onAccent:'#0A0A18'};
var S = {
  margin:72, series:[LI, OR, VI, '#8F84FF', '#FFB08A', '#FFFFFF'],
  header:{kind:'left', top:58, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'glass', r:26, fill:'#FFFFFF', op:0.09, line:'#FFFFFF', lop:0.45, lw:1.3, shadow:0, dark:true, tc:TX, mc:MU,
        hl:{kind:'glass', fill:'#FFFFFF', op:0.18, dark:true, tc:TX, mc:MU}},
  icon:{kind:'plain', fg:LI}, num:{kind:'big', fg:LI},
  conc:{kind:'box', fill:'#FFFFFF', op:0.09, r:22, line:'#FFFFFF', lw:1.2, label:'Takeaway', lc:LI, tc:TX},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:140, stops:[[0, '#3B2FBF'], [55, '#7A2A6B'], [100, '#E0561B']]}}
};
/* a lit sphere and a gold ring */
function ball(C, cx, cy, r, a, b){ C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:150, stops:[[0, a], [100, b]]}}); D.glow(C, cx - r * 0.3, cy - r * 0.34, r * 0.6, r * 0.48, '#FFFFFF', 0.5); }
function ring(C, cx, cy, r){ C.ell(cx - r, cy - r, r * 2, r * 2, {line:GO, lw:Math.max(5, r * 0.26)}); C.ell(cx - r * 0.82, cy - r * 0.82, r * 1.64, r * 1.64, {line:'#FFE9B0', lw:1.5, lop:0.85}); }
/* the objects sit on the corners of the glass box R: half outside (sharp), half under the glass (blurred) */
function orbs(C, R, k, small){
  var m = Math.min(R.w, R.h) * (small ? 0.6 : 1), f = (k || 0) % 3;
  var a = f === 1 ? [R.x + R.w * 0.9, R.y + R.h * 0.08] : (f === 2 ? [R.x + R.w * 0.08, R.y + R.h * 0.9] : [R.x + R.w * 0.1, R.y + R.h * 0.1]);
  var b = f === 1 ? [R.x + R.w * 0.1, R.y + R.h * 0.92] : (f === 2 ? [R.x + R.w * 0.9, R.y + R.h * 0.1] : [R.x + R.w * 0.9, R.y + R.h * 0.9]);
  ball(C, a[0], a[1], m * 0.3, '#FF9A4A', OR2);
  ball(C, b[0], b[1], m * 0.34, '#8C7BFF', VI2);
  ball(C, a[0] + m * 0.36, a[1] - m * 0.16 * (a[1] < R.y + R.h / 2 ? 1 : -1), m * 0.055, '#8C7BFF', VI2);
  ball(C, b[0] - m * 0.4, b[1] + m * 0.14 * (b[1] > R.y + R.h / 2 ? 1 : -1), m * 0.05, '#FF9A4A', OR2);
  ring(C, a[0] > R.x + R.w / 2 ? R.x + R.w : R.x, R.y + R.h * (f === 2 ? 0.34 : 0.56), m * 0.085);
  ring(C, b[0] > R.x + R.w / 2 ? R.x + R.w : R.x, R.y + R.h * (f === 2 ? 0.6 : 0.3), m * 0.07);
}
/* frosted glass: a dark veil (this is the layer that receives the blurred backdrop) and a bright rim */
function glass(C, x, y, w, h, r, o){
  o = o || {};
  var e = C.rect(x, y, w, h, {fill:'#080818', op:o.veil != null ? o.veil : 0.3, r:r}); e.frost = o.blur || 26;
  var f = C.rect(x, y, w, h, {fill:'#FFFFFF', op:o.op != null ? o.op : 0.07, line:'#FFFFFF', lop:0.5, lw:1.3, r:r}); f.frost = 0;
  var s = C.rect(x + r, y + 1, Math.max(10, w * 0.45 - r), 1.5, {fill:'#FFFFFF', op:0.55}); s.frost = 0;
}
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  ball(C, x + 11, y + 11, 11, '#8C7BFF', VI2);
  C.text(x + 32, y, 420, 22, f.company, {font:'head', size:14, wt:700, color:TX, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  ball(C, M + 8, y + 8, 8, '#8C7BFF', VI2);
  C.text(M + 26, y, 500, 16, f.company || f.website || '', {font:'head', size:12.5, wt:600, color:TX, lh:1, va:'m'});
  var e = C.rect(W - M - 62, y - 7, 62, 30, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.4, lw:1, r:15}); e.frost = 0;
  C.text(W - M - 62, y - 7, 62, 30, pad2(C.i + 1), {font:'head', size:12.5, wt:700, color:TX, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:BG});
  D.glow(C, 240, 80, 520, 380, '#2B2470', 0.5); D.glow(C, 1120, 700, 520, 360, '#5A1D10', 0.42);
  if (kind === 'cover' || kind === 'closing' || kind === 'quote' || (C.o && C.o.fn)) return;
  orbs(C, {x:120, y:190, w:1040, h:420}, C.i || 0, false);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function num(C, x, y, k, s){ C.text(x, y, 60, s * 1.2, pad2(k + 1), {font:'head', size:s, wt:800, color:LI, lh:1, caps:false}); }
/* ---------- bodies made of several glass boxes ---------- */
function cols(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
  var g = 20, off = Math.min(24, Z.h * 0.06), cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 50;
  var F = K.fitHD(C, it, tw, 54, Z.h - off - top - pad * 2, {hMax:20, dMax:15, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - off, top + bmx + pad * 2 + 8), y0 = Z.y + Math.max(0, (Z.h - off - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = y0 + (k % 2 ? off : 0);
    glass(C, x, y, cw, ch, 24);
    num(C, x + pad, y + pad - 2, k, 24);
    K.hd(C, x + pad, y + pad + top - 8, tw, ch - top - pad * 2 + 8, t, F, {tc:TX, mc:MU});
  });
}
function rows(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 112), nw = 70, pad = 24, tw = Z.w - nw - pad * 2;
  var F = K.fitHD(C, it, tw, 30, rh - 20, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 14.5 : 19, hMin:12.5, dMin:10.5});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    glass(C, Z.x, y, Z.w, rh, Math.min(24, rh / 2));
    C.text(Z.x + pad, y, nw - 10, rh, pad2(k + 1), {font:'head', size:Math.min(26, rh * 0.34), wt:800, color:LI, va:'m', lh:1, caps:false});
    K.hd(C, Z.x + pad + nw, y + Math.max(8, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:TX, mc:MU});
  });
}
function grid(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cl = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cl = Math.min(cl, 2);
  var rw = Math.ceil(n / cl), g = 16, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = 20, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 26, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 32), tot = ch2 * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cl), c = k % cl, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    glass(C, x, y, cw, ch2, 20);
    num(C, x + pad, y + pad - 3, k, 15);
    K.hd(C, x + pad, y + pad + 22, tw, ch2 - pad * 2 - 22, t, F, {tc:TX, mc:MU});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function statCards(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cl = Z.w < 760 ? (n > 2 ? 2 : 1) : n, rw = Math.ceil(n / cl), g = 20, cw = (Z.w - g * (cl - 1)) / cl, ch = Math.min((Z.h - g * (rw - 1)) / rw, 300), pad = 26, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, tw, ch * 0.4, {font:'head', max:68, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs * 1.1 - 110, {font:'body', max:14, min:10.5, lh:1.4});
  var tot = ch * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var r = Math.floor(k / cl), c = k % cl, x = Z.x + c * (cw + g), y = y0 + r * (ch + g);
    glass(C, x, y, cw, ch, 26);
    C.text(x + pad, y + pad, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:LI, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + vs * 1.1 + 10, 40, 3, {fill:'#FFFFFF', op:0.7});
    var le = C.text(x + pad, y + pad + vs * 1.1 + 26, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:TX, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, y + pad + vs * 1.1 + 34 + le._h, tw, ch - (pad * 2 + vs * 1.1 + 34 + le._h), t.x, {font:'body', size:ds, color:MU, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function steps(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
  var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 18, tw = cw - pad * 2, st = Math.min(26, Z.h * 0.32 / (n - 1)), ch = Z.h - st * (n - 1);
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 34, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - 84, {font:'body', max:14.5, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + k * st;
    glass(C, x, y, cw, ch, 22);
    C.text(x + pad, y + pad, tw, 34, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:LI, lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + 42, 34, 3, {fill:'#FFFFFF', op:0.7});
    C.text(x + pad, y + pad + 58, tw, ch - pad * 2 - 58, t.d, {font:'body', size:ds, color:TX, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  glass(C, Z.x, Z.y, Z.w, Z.h, 30);
  var pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
  C.rect(Z.x + pad, y, 46, 5, {fill:LI, r:3}); y += 28;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:LI, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:TX, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
/* ---------- one glass panel that holds the whole slide: it can sit anywhere, over a photo or over the spheres ---------- */
var RECTS = {l:[56, 56, 600, 596], r:[624, 56, 600, 596], t:[56, 48, 1168, 336], b:[56, 320, 1168, 332], c:[190, 76, 900, 556], tl:[56, 56, 680, 420], tr:[544, 56, 680, 420], bl:[56, 232, 680, 420], br:[544, 232, 680, 420]};
function place(C, o){
  var ph = o.ph, a = RECTS[o.pos || 'l'] || RECTS.l, R = {x:a[0], y:a[1], w:a[2], h:a[3]};
  if (ph === 'full') D.imgBox(C, 0, 0, W, H, {r:0});
  else if (ph === 'left') { D.imgBox(C, 0, 0, 600, H, {r:0}); R = {x:470, y:64, w:754, h:580}; }
  else if (ph === 'right') { D.imgBox(C, 680, 0, 600, H, {r:0}); R = {x:56, y:64, w:754, h:580}; }
  else if (ph === 'top') { D.imgBox(C, 0, 0, W, 350, {r:0}); R = {x:90, y:230, w:1100, h:422}; }
  else if (ph === 'bottom') { D.imgBox(C, 0, 370, W, 350, {r:0}); R = {x:90, y:56, w:1100, h:422}; }
  orbs(C, R, (C.i || 0) + (o.pos === 'r' || o.pos === 'tr' ? 1 : 0), !!ph);
  glass(C, R.x, R.y, R.w, R.h, 30, {veil:ph ? 0.42 : 0.3});
  return R;
}
function listIn(C, Z){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var wide = Z.w > 860, cl = wide ? (n <= 4 ? n : Math.ceil(n / 2)) : (n > 4 && Z.w > 520 ? 2 : 1), rw = Math.ceil(n / cl), gx = 28, cw = (Z.w - gx * (cl - 1)) / cl, ch = Math.min(Z.h / rw, cl === 1 ? 104 : 190);
  if (C.kind === 'stats') {
    var vals = it.map(sv), labs = it.map(sl), vs = C.groupFit(vals, cw, ch * 0.5, {font:'head', max:54, min:20, maxLines:1}), ls = C.groupFit(labs, cw, Math.max(24, ch - vs * 1.15 - 10), {font:'body', max:15, min:11, lh:1.3, maxLines:3});
    it.forEach(function(t, k){
      var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
      C.text(x, y, cw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:LI, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, y + vs * 1.1 + 4, cw, Math.max(24, ch - vs * 1.15 - 10), labs[k], {font:'body', size:ls, wt:500, color:TX, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
    });
    return;
  }
  var nw = 40, tw = cw - nw, F = K.fitHD(C, it, tw, 30, ch - 14, {hMax:18, dMax:it.some(function(t){ return t.h; }) ? 14 : 17, hMin:12, dMin:10.5});
  it.forEach(function(t, k){
    var c = k % cl, r = Math.floor(k / cl), x = Z.x + c * (cw + gx), y = Z.y + r * ch;
    if (r && cl === 1) C.rect(x, y - 1, cw, 1, {fill:'#FFFFFF', op:0.22});
    num(C, x, y + (cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8) + 1, k, 14);
    K.hd(C, x + nw, y + (cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8), tw, ch - 12, t, F, {tc:TX, mc:MU});
  });
}
function scene(C, o){
  o = o || C.o || {};
  var R = place(C, o), pad = Math.min(40, Math.max(28, R.w * 0.05)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, wide = R.w > 860;
  C.rect(x, y, 46, 5, {fill:LI, r:3}); y += 22;
  var te = C.text(x, y, wide ? w * 0.7 : w, 100, C.s.title || '', {font:'head', fit:{max:wide ? 38 : 33, min:19}, wt:700, color:TX, lh:1.1, ed:{f:'title'}, maxLines:2}); y += te._h + 6;
  if (C.s.subtitle) { var se = C.text(x, y, wide ? w * 0.7 : w, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:MU, lh:1.35, ed:{f:'subtitle'}, maxLines:2}); y += se._h + 6; }
  y += 12;
  if (C.s.conclusion && bot - y > 230) { bot -= 50; C.rect(x, bot + 12, w, 1, {fill:'#FFFFFF', op:0.25}); C.text(x, bot + 18, w, 32, C.s.conclusion, {font:'body', fit:{max:14, min:11}, wt:600, color:LI, va:'m', lh:1.25, ed:{f:'x-conc'}, maxLines:2}); }
  listIn(C, {x:x, y:y, w:w, h:bot - y});
}
function quoteScene(C, o){
  o = o || C.o || {};
  var R = place(C, o), by = C.it[0], pad = Math.min(54, Math.max(34, R.w * 0.06)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, mid = (o.pos || 'c') === 'c' || o.pos === 't' || o.pos === 'b';
  C.text(mid ? x + w / 2 - 40 : x - 4, y - 16, 80, 80, '“', {font:'head', size:92, wt:800, color:LI, lh:1, align:mid ? 'c' : 'l', caps:false, ls:0}); y += 62;
  C.text(x, y, w, R.y + R.h - pad - y - (by ? 46 : 0), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 40 : 32, min:18}, wt:600, color:TX, lh:1.2, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:6});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, R.y + R.h - pad - 26, w, 26, bt, {font:'body', size:14, wt:600, color:LI, lh:1.1, align:mid ? 'c' : 'l', va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, mid = o.pos === 'c';
  if (f.company) { if (mid) C.text(x, y, w, 22, f.company, {font:'head', size:14, wt:700, color:LI, align:'c', va:'m', lh:1, caps:true, ls:0.14, ed:{f:'tf-company'}, maxLines:1}); else brand(C, x, y); }
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  '), th = bot - y - 40 - (who ? 50 : 0) - (C.s.subtitle ? 70 : 0);
  var te = C.text(x, y + 40, w, Math.max(80, th), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 66 : 54, min:26}, wt:800, color:TX, lh:1.04, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:R.h < 360 ? 2 : 4});
  var sy = y + 40 + Math.max(80, th) + 8;
  if (C.s.subtitle) C.text(x, sy, w, 62, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, align:mid ? 'c' : 'l', ed:{f:'subtitle'}, maxLines:3});
  if (who) { if (!mid) C.rect(x, bot - 30, 34, 3, {fill:LI}); C.text(x + (mid ? 0 : 46), bot - 40, w - (mid ? 0 : 46), 24, who, {font:'head', size:14, wt:700, color:TX, lh:1, va:'m', align:mid ? 'c' : 'l', ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, wide = R.w > 860, cs = D.contacts(C), tw = wide ? w * 0.52 : w;
  C.rect(x, y, 46, 5, {fill:LI, r:3}); y += 24;
  var te = C.text(x, y, tw, wide ? R.h - pad * 2 - 110 : 150, C.s.title || 'Thank You', {font:'head', fit:{max:wide ? 70 : 58, min:28}, wt:800, color:TX, lh:1.04, va:wide ? 'm' : 't', ed:{f:'title'}, maxLines:2});
  var sy = wide ? R.y + R.h - pad - 70 : y + te._h + 10;
  if (C.s.subtitle) C.text(x, sy, tw, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cx = wide ? x + w * 0.58 : x, cw = wide ? w * 0.42 : w, n = cs.length, rh = Math.min(56, ((wide ? R.h - pad * 2 : R.y + R.h - pad - (sy + 80)) ) / Math.max(1, n)), cy = wide ? R.y + (R.h - rh * n) / 2 : R.y + R.h - pad - rh * n;
  cs.forEach(function(c, k){
    var yy = cy + k * rh;
    C.icon(cx, yy + rh / 2 - 11, 22, c.icon, LI);
    C.text(cx + 38, yy, cw - 38, rh, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:TX, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(cx, yy, cw, 1, {fill:'#FFFFFF', op:0.2});
  });
}
function quote(C, v){ quoteScene(C, C.o || {}); }
/* the same set of positions for every kind of slide */
function sc(fn, few){
  var a = [
    V('g1', 'Glass panel left', {fn:fn, pos:'l'}), V('g2', 'Glass panel right', {fn:fn, pos:'r'}), V('g3', 'Glass panel centre', {fn:fn, pos:'c'}),
    V('g4', 'Glass panel on top', {fn:fn, pos:'t'}), V('g5', 'Glass panel at the bottom', {fn:fn, pos:'b'}),
    V('f1', 'Full photo + glass left', {fn:fn, img:'photo', ph:'full', pos:'l'}), V('f2', 'Full photo + glass right', {fn:fn, img:'photo', ph:'full', pos:'r'}),
    V('f3', 'Full photo + glass at the bottom', {fn:fn, img:'photo', ph:'full', pos:'b'}), V('f4', 'Full photo + glass on top', {fn:fn, img:'photo', ph:'full', pos:'t'}),
    V('f5', 'Full photo + glass centre', {fn:fn, img:'photo', ph:'full', pos:'c'}),
    V('h1', 'Photo left half + glass', {fn:fn, img:'photo', ph:'left'}), V('h2', 'Photo right half + glass', {fn:fn, img:'photo', ph:'right'}),
    V('h3', 'Photo on top + glass below', {fn:fn, img:'photo', ph:'top'}), V('h4', 'Glass above + photo below', {fn:fn, img:'photo', ph:'bottom'})
  ];
  if (few) a = a.concat([
    V('c1', 'Full photo + glass top-left', {fn:fn, img:'photo', ph:'full', pos:'tl'}), V('c2', 'Full photo + glass top-right', {fn:fn, img:'photo', ph:'full', pos:'tr'}),
    V('c3', 'Full photo + glass bottom-left', {fn:fn, img:'photo', ph:'full', pos:'bl'}), V('c4', 'Full photo + glass bottom-right', {fn:fn, img:'photo', ph:'full', pos:'br'}),
    V('c5', 'Glass panel top-left', {fn:fn, pos:'tl'}), V('c6', 'Glass panel bottom-right', {fn:fn, pos:'br'})
  ]);
  return a;
}
function plain(list){ return list.map(function(v){ var o = {}; for (var k in v.o) if (k !== 'fn') o[k] = v.o[k]; return V(v.id, v.lbl, o); }); }
D.register({
  id:'frostorbit', name:'Frost Orbit', tag:'Dark · frosted glass · spheres', frost:true,
  desc:'Real frosted glass on a near-black page: orange and violet spheres and gold rings sit half under the glass and blur behind it, and a photo can fill the whole slide or half of it with the glass panel on any side or corner.',
  fonts:{head:'Gabarito', body:'Instrument Sans', headW:700, bodyW:400, headLh:1.08, gf:'Gabarito:wght@500;600;700;800|Instrument+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Glass panel left', {pos:'l'}), V('v2', 'Full photo + glass left', {img:'photo', ph:'full', pos:'l'})].concat(plain(sc(null, true).slice(1, 5)), plain(sc(null, true).slice(6))),
    closing:[V('v1', 'Glass panel centre', {pos:'c'}), V('v2', 'Full photo + glass centre', {img:'photo', ph:'full', pos:'c'})].concat(plain(sc(null, true).filter(function(v){ return v.id !== 'g3' && v.id !== 'f5'; }))),
    agenda:[V('v1', 'Glass rows', {body:rows}), V('n2', 'Glass grid', {body:grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene)),
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})].concat(sc(scene, true)),
    p2:[V('v1', 'Glass columns', {body:cols}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
    p3:[V('v1', 'Glass columns', {body:cols}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
    p4:[V('v1', 'Glass columns', {body:cols}), V('n2', 'Glass rows', {body:rows}), V('n3', 'Glass grid', {body:grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
    p56:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
    p78:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows, max:8}), V('v2', 'Glass grid + image right', {body:grid, img:'right', cols:2})].concat(sc(scene)),
    stats:[V('v1', 'Glass numbers', {body:statCards}), V('n2', 'Progress rings', {body:'statRings', track:'#34345A'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + image right', {body:statCards, img:'right'}), V('v3', 'Image left + numbers', {body:statCards, img:'left'})].concat(sc(scene, true)),
    timeline:[V('v1', 'Glass steps', {body:steps}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene)),
    quote:[V('v1', 'Glass quote centre', {pos:'c'}), V('v2', 'Full photo + glass centre', {img:'photo', ph:'full', pos:'c'}), V('v3', 'Image left + quote', {img:'photo', ph:'left'})].concat(plain(sc(null, true).filter(function(v){ return v.id !== 'g3' && v.id !== 'f5' && v.id !== 'h1'; })))
  }
});
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 57 - Glass Tower : dark navy, a skyline of steel-blue towers behind square-cut frosted glass */
D.glassKit({
  id:'glasstower', name:'Glass Tower', tag:'Dark navy · frosted glass · towers',
  desc:'Corporate dark navy with a skyline of steel-blue towers that blurs behind square-cut frosted glass panels; a photo can fill the slide or half of it behind the glass. For finance, real estate, consulting and company reports.',
  fonts:{head:'Hanken Grotesk', body:'Hanken Grotesk', headW:700, bodyW:400, headLh:1.1, gf:'Hanken+Grotesk:wght@400;500;600;700;800'},
  bg:'#08111F', tx:'#FFFFFF', mu:'#B4C2D6', ac:'#8FC1F2', ac2:'#3E6FA3', card:'#101D30', line:'#24364F', onAc:'#08111F',
  r:6, mark:'tick', num:'box', stag:false, cover:'l', close:'c', quote:'c', b3:'cols', veil:'#050B15', veilOp:0.36,
  ph:{ang:160, stops:[[0, '#3E6FA3'], [100, '#0B1A2E']]},
  scene:function(C, R, k, U){
    var W = U.W, H = U.H, n = 17, bw = W / n;
    U.D.glow(C, R.x + R.w * 0.7, 120, 560, 380, '#2B5E99', 0.4);
    for (var i = 0; i < n; i++) {
      var h = 150 + ((i * 37 + k * 53 + 5) % 11) / 10 * 380, x = i * bw + 5, y = H - h, hot = (i + k) % 5 === 2;
      C.rect(x, y, bw - 10, h, {grad:{ang:180, stops:[[0, hot ? '#8FC1F2' : '#3E6FA3', hot ? 0.85 : 0.75], [100, '#0B1A2E', 0.15]]}});
      C.rect(x, y, bw - 10, 3, {fill:'#CFE6FF', op:hot ? 0.95 : 0.5});
      if (i % 2 === 0) C.rect(x + (bw - 10) * 0.3, y + 26, (bw - 10) * 0.4, h * 0.5, {fill:'#CFE6FF', op:0.1});
    }
  }
});
})();

}catch(e){if(window.console)console.error(e);}
