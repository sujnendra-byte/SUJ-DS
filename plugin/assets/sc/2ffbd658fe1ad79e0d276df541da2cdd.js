
/* Template 29 — Neon Lime Glass : near-black green pages, lime glows and organic blobs, frosted glass cards, lime pill tags and dot labels */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2, mix = u.mix;
var LIME = '#7CFF4F', LIME2 = '#3FD62B', PALE = '#C9FFB5', INK = '#07120A', MUTE = '#A3BDA8';
var BG = {ang:160, stops:[[0, '#0B1A0F'], [55, '#060E08'], [100, '#0E2615']]};
var LGR = {ang:135, stops:[[0, '#A6FF7F'], [100, '#4FE02E']]};
var P0 = {bg:'#08120A', text:'#E6F4E8', title:'#FFFFFF', muted:MUTE, accent:LIME, accent2:LIME2, card:'#132418', line:'#28402D', onAccent:INK};
var S = {
  margin:70, series:[LIME, '#B6FF9A', LIME2, '#E4FFD8', '#5EEA3A', '#9CFF7A'],
  header:{kind:'left', top:92, tMax:50, tMin:28, sMax:17, sMin:13, kicker:null},
  card:{kind:'glass', r:16, fill:'#FFFFFF', op:0.05, line:'#FFFFFF', lop:0.22, dark:true},
  icon:{kind:'soft', bg:LIME, fg:LIME}, num:{kind:'ring', bg:LIME, fg:LIME},
  conc:{kind:'pill', fill:LIME, op:0.1, label:'Takeaway', lc:LIME, tc:'#E6F4E8'},
  footer:{brand:false, page:true, color:'#6F8A74'}, glassFill:'#FFFFFF', glassOp:0.05, glassLine:'#FFFFFF',
  imgFramed:true, imgR:20, bodyGap:30,
  imgPh:{grad:{ang:135, stops:[[0, '#1E4A26'], [100, '#0B1A0F']]}}
};

/* ---------- motif helpers ---------- */
function glass(C, x, y, w, h, o){
  o = o || {};
  if (o.lime) { C.rect(x, y, w, h, {grad:LGR, r:o.r || 16}); return {tc:INK, mc:'#1E3A22', ac:INK}; }
  C.rect(x, y, w, h, {fill:'#FFFFFF', op:o.op != null ? o.op : 0.05, line:'#FFFFFF', lop:0.22, lw:1.2, r:o.r || 16});
  if (o.dot !== false) { D.glow(C, x + w - 24, y + 24, 20, 20, LIME, 0.45); C.ell(x + w - 29, y + 19, 10, 10, {fill:LIME}); }
  return {tc:'#FFFFFF', mc:'#B7CCBB', ac:LIME};
}
function dotLabel(C, x, y, w, text, o){
  o = o || {};
  C.ell(x, y + 5, 10, 10, {fill:o.color || LIME});
  C.text(x + 20, y, w - 20, 20, text, {font:'body', size:o.size || 13, wt:700, color:o.tc || '#FFFFFF', caps:true, ls:0.1, lh:1, va:'m', ed:o.ed || null, maxLines:1});
}
function pill(C, x, y, w, h, text, o){
  o = o || {};
  C.rect(x, y, w, h, {fill:o.fill || null, grad:o.grad || null, line:o.fill || o.grad ? null : (o.line || LIME), lw:1.4, r:h / 2});
  C.text(x + h * 0.4, y, w - h * 0.8, h, text, {font:o.font || 'body', fit:{max:o.size || Math.round(h * 0.44), min:o.min || 11}, wt:o.wt || 600, color:o.color || (o.fill || o.grad ? INK : '#FFFFFF'), align:'c', va:'m', lh:1.1, ed:o.ed || null, maxLines:1});
}
function pw(t, s){ return String(t || '').length * s * 0.58 + s * 2.4; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  K.blob(C, x + 10, y + 11, 11, LIME, 1, 3);
  C.text(x + 30, y, 320, 22, f.company, {font:'head', size:16, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
}

/* ---------- page background ---------- */
function bg(C, kind){
  C.bg({grad:BG});
  var odd = (C.i || 0) % 2;
  D.glow(C, odd ? W * 0.05 : W * 0.97, H * 0.98, 380, 260, LIME, 0.2);
  D.glow(C, odd ? W * 0.98 : W * 0.02, H * 0.02, 360, 240, '#1F7A2E', 0.45);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'left' && im !== 'top') brand(C, 70, 32);
  if (im !== 'right' && im !== 'top' && C.cover.date) C.text(W - 370, 32, 300, 22, C.cover.date, {font:'body', size:13, wt:600, color:MUTE, align:'r', va:'m', lh:1});
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* open dot-label columns + the last item in a tall glowing glass card */
function dotCols(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var open = it.slice(0, n - 1), last = it[n - 1], narrow = Z.w < 820;
  if (narrow) {
    var gh = Math.min(Z.h * 0.42, 220), rows = open.length, ry = Z.y + gh + 22, rh = rows ? (Z.y + Z.h - ry - 14 * (rows - 1)) / rows : 0;
    var cc = glass(C, Z.x, Z.y, Z.w, gh, {});
    var FL = K.fitHD(C, [last], Z.w - 60, 30, gh - 76, {hMax:16, dMax:16, hMin:13, dMin:11, noBoost:true});
    dotLabel(C, Z.x + 28, Z.y + 26, Z.w - 80, last.h || pad2(n), {ed:last.h ? {f:'x-part', i:last.i, p:0} : null});
    C.text(Z.x + 28, Z.y + 60, Z.w - 60, gh - 76, last.d, {font:'body', size:FL.ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:last.i, p:1}});
    var F2 = K.fitHD(C, open.map(function(t){ return {i:t.i, h:'', d:t.d}; }), Z.w - 20, 1, rh - 30, {dMax:15, dMin:11});
    open.forEach(function(t, k){
      var y = ry + k * (rh + 14);
      dotLabel(C, Z.x, y, Z.w, t.h || pad2(k + 1), {ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      C.text(Z.x + 20, y + 28, Z.w - 20, rh - 28, t.d, {font:'body', size:F2.ds, color:MUTE, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    });
    return;
  }
  var gw = Math.min(Z.w * (n > 3 ? 0.3 : 0.36), 400), ow = Z.w - gw - 40, m = open.length, gx = 34, cw = m ? (ow - gx * (m - 1)) / m : 0;
  var F = K.fitHD(C, open.map(function(t){ return {i:t.i, h:'', d:t.d}; }), cw, 1, Z.h * 0.7 - 36, {dMax:19, dMin:11});
  var FL2 = K.fitHD(C, [{i:last.i, h:'', d:last.d}], gw - 56, 1, Z.h - 100, {dMax:19, dMin:11, noBoost:true});
  var odh = 0; open.forEach(function(t){ odh = Math.max(odh, C.measureText(t.d, cw, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h); });
  var ldh = C.measureText(last.d, gw - 56, {font:'body', max:FL2.ds, min:FL2.ds, lh:1.42}).h, gh = Math.min(Z.h, Math.max(Z.h * 0.6, 68 + ldh + 60));
  var gy0 = Z.y + (Z.h - gh) * 0.4, top = Math.max(Z.y + 20, gy0 + gh - odh - 34 - 10);
  open.forEach(function(t, k){
    var x = Z.x + k * (cw + gx);
    C.rect(x, top - 20, cw, 1, {fill:'#FFFFFF', op:0.18});
    dotLabel(C, x, top, cw, t.h || pad2(k + 1), {ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x, top + 34, cw, Z.y + Z.h - top - 34, t.d, {font:'body', size:F.ds, color:MUTE, lh:1.42, ed:{f:'x-part', i:t.i, p:1}});
  });
  var gx0 = Z.x + Z.w - gw, cc = glass(C, gx0, gy0, gw, gh, {});
  D.glow(C, gx0 + gw * 0.2, gy0 + gh * 0.9, gw * 0.6, 90, LIME, 0.12);
  dotLabel(C, gx0 + 28, gy0 + 30, gw - 80, last.h || pad2(n), {ed:last.h ? {f:'x-part', i:last.i, p:0} : null});
  C.text(gx0 + 28, gy0 + 68, gw - 56, gh - 96, last.d, {font:'body', size:FL2.ds, color:cc.mc, lh:1.42, ed:{f:'x-part', i:last.i, p:1}});
}

/* problem / solution: glass card and solid lime card with an arrow disc */
function limeDuo(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return dotCols(C, Z, o);
  var gap = 70, cw = (Z.w - gap) / 2, pad = 38, top = pad + 40;
  var F = K.fitHD(C, it, cw - pad * 2, 90, Z.h - top - pad, {hMax:28, dMax:18, hMin:15, dMin:12});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cc = glass(C, x, Z.y, cw, Z.h, {lime:k === 1, dot:k === 0});
    C.text(x + pad, Z.y + pad - 6, 200, 30, pad2(k + 1) + ' —', {font:'head', size:20, wt:700, color:k ? INK : LIME, lh:1, caps:false, ls:0});
    var bh = blockH(C, t, cw - pad * 2, F);
    K.hd(C, x + pad, Z.y + top + Math.max(0, (Z.h - top - pad - bh) * 0.4), cw - pad * 2, Z.h - top - pad, t, F, {tc:cc.tc, mc:cc.mc});
  });
  var cx = Z.x + cw + gap / 2, cy = Z.y + Z.h / 2;
  D.glow(C, cx, cy, 70, 70, LIME, 0.35);
  C.ell(cx - 28, cy - 28, 56, 56, {fill:INK, line:LIME, lw:2});
  C.icon(cx - 14, cy - 14, 28, 'mdi:arrow-right', LIME);
}

/* pill-tag grid: heading in lime outline pill, text below, no cards */
function pillGrid(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5))), rows = Math.ceil(n / cols), gx = 30, gy = 26;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var hasH = it.some(function(t){ return t.h; }), pH = hasH ? (cols <= 3 ? 42 : 36) : 48;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 70, pH - 8, {font:'body', wt:600, max:hasH ? (cols <= 3 ? 18 : 16) : 19, min:11, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - 4, ch - pH - 60, {font:'body', max:n <= 4 ? 21 : (n <= 6 ? 18 : 15.5), min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ if (t.h) dmx = Math.max(dmx, C.measureText(t.d, cw - 4, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = hasH ? Math.min(ch, 30 + pH + 16 + dmx + 16) : Math.min(ch, 30 + pH + 20);
  var tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.4);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = y0 + r * (ch2 + gy);
    C.text(x, y, 60, 22, pad2(k + 1), {font:'head', size:16, wt:700, color:LIME, lh:1, caps:false, ls:0});
    C.rect(x + 36, y + 10, cw - 36, 1, {fill:'#FFFFFF', op:0.18});
    var lab = t.h || t.d, w = Math.min(cw, pw(lab, hs) + 10);
    pill(C, x, y + 30, w, pH, lab, {size:hs, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, fill:k === 0 ? LIME : null});
    if (t.h) C.text(x, y + 30 + pH + 16, cw - 4, ch2 - pH - 46, t.d, {font:'body', size:ds, color:MUTE, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* one tall glass panel with numbered rows (index look) */
function glassIndex(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  glass(C, Z.x, Z.y, Z.w, Z.h, {});
  var pad = 30, cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - pad * 2 - gx * (cols - 1)) / cols, rh = (Z.h - pad * 2) / per;
  var hasH = it.some(function(t){ return t.h; }), hw = hasH ? Math.min(220, cw * 0.36) : 0, dw = cw - 60 - hw - (hasH ? 20 : 0);
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h; }), hw, rh - 8, {font:'head', max:19, min:13, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 8, {font:hasH ? 'body' : 'head', max:hasH ? 16 : 20, min:11, lh:1.35});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + pad + c * (cw + gx), y = Z.y + pad + r * rh;
    C.text(x, y, 50, rh, pad2(k + 1), {font:'head', size:18, wt:700, color:LIME, va:'m', lh:1, caps:false, ls:0});
    if (hasH) C.text(x + 60, y, hw, rh, t.h, {font:'head', size:hs, color:'#FFFFFF', va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.text(x + 60 + hw + (hasH ? 20 : 0), y, dw, rh, t.d, {font:hasH ? 'body' : 'head', size:ds, wt:hasH ? null : 500, color:hasH ? MUTE : '#FFFFFF', va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
    if (r < per - 1) C.rect(x, y + rh, cw, 1, {fill:'#FFFFFF', op:0.12});
  });
}

/* stats: glass cards with huge value left, label right, glowing dot */
function glassPct(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n <= 2 ? 1 : 2, rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var vals = st.map(sv), labs = st.map(sl), vw = cw * 0.46;
  var vs = C.groupFit(vals, vw - 30, ch * 0.6, {font:'head', max:72, min:24, maxLines:1});
  var lw = cw - vw - 50;
  var ls = C.groupFit(labs, lw, 48, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), lw, ch - 90, {font:'body', max:14.5, min:11, lh:1.38});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy), cc = glass(C, x, y, cw, ch, {});
    C.text(x + 28, y, vw - 30, ch, vals[k], {font:'head', size:vs, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.02, maxLines:1});
    C.rect(x + vw, y + ch * 0.22, 1, ch * 0.56, {fill:LIME, op:0.5});
    var lh = C.measureText(labs[k], lw, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.x ? C.measureText(t.x, lw, {font:'body', max:ds, min:ds, lh:1.38}).h : 0, by = y + Math.max(14, (ch - lh - (dh ? dh + 8 : 0)) / 2);
    C.text(x + vw + 22, by, lw, lh + 4, labs[k], {font:'head', size:ls, color:LIME, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + vw + 22, by + lh + 8, lw, dh + 4, t.x, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* stats: values sitting on lime blobs */
function blobStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  if (n > 2 && Z.w / n < 200) { var zh = (Z.h - 20) / 2, keep = C.it; [0, 1].forEach(function(q){ C.it = keep.slice(q * 2, q * 2 + 2); blobStats(C, {x:Z.x, y:Z.y + q * (zh + 20), w:Z.w, h:zh}, o); }); C.it = keep; return; }
  var cw = Z.w / n, r = Math.min(cw * 0.36, Z.h * 0.28, 110), cy = Z.y + r + 6;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, r * 1.2, r * 0.6, {font:'head', max:46, min:13, maxLines:1});
  var ls = C.groupFit(labs, cw - 24, 50, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 30, Z.h - r * 2 - 90, {font:'body', max:15, min:11, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    D.glow(C, cx, cy, r * 1.5, r * 1.3, LIME, 0.22);
    K.blob(C, cx, cy, r * 1.08, k % 2 ? '#B6FF9A' : LIME, 1, k + 2);
    C.ell(cx - r * 0.82, cy - r * 0.82, r * 1.64, r * 1.64, {fill:k % 2 ? '#B6FF9A' : LIME});
    C.text(cx - r * 0.66, cy - r * 0.4, r * 1.32, r * 0.8, vals[k], {font:'head', size:vs, wt:700, color:INK, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.01, maxLines:1});
    var ly = cy + r + 30, lh = C.measureText(labs[k], cw - 24, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(cx - cw / 2 + 12, ly, cw - 24, lh + 4, labs[k], {font:'head', size:ls, color:'#FFFFFF', align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - cw / 2 + 15, ly + lh + 10, cw - 30, Z.y + Z.h - ly - lh - 10, t.x, {font:'body', size:ds, color:MUTE, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* timeline: glass cards on a neon rail, glowing node under each */
function rail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var gap = 16, cw = (Z.w - gap * (n - 1)) / n, ry = Z.y + Z.h - 22, pad = 20;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2 - 20, 40, {font:'head', max:30, min:15, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - 70 - hs * 1.2 - pad * 2, {font:'body', max:n <= 4 ? 17 : 15, min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch = Math.min(Z.h - 60, pad * 2 + hs * 1.2 + 14 + dmx + 10);
  ry = Math.min(ry, Z.y + (Z.h + ch + 38) / 2 + 10); var cy = ry - 38 - ch;
  C.rect(Z.x, ry - 1.5, Z.w, 3, {fill:LIME, op:0.35});
  C.rect(Z.x, ry - 1.5, Z.w * 0.6, 3, {grad:{ang:90, stops:[[0, LIME, 0], [100, LIME]]}});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cx = x + cw / 2, cc = glass(C, x, cy, cw, ch, {});
    C.text(x + pad, cy + pad, cw - pad * 2 - 20, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:0, maxLines:1});
    C.text(x + pad, cy + pad + hs * 1.2 + 14, cw - pad * 2, ch - pad * 2 - hs * 1.2 - 14, t.d, {font:'body', size:ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    C.line(cx, cy + ch, cx, ry - 8, {color:LIME, lw:1.2, op:0.5, dash:true});
    D.glow(C, cx, ry, 22, 22, LIME, 0.5);
    C.ell(cx - 7, ry - 7, 14, 14, {fill:k === n - 1 ? LIME : INK, line:LIME, lw:2});
  });
}

/* timeline: two staggered rows of glass cards joined by a circuit line */
function circuit(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / (n + 1), cw = Math.min(slot * 2 - 20, 330), my = Z.y + Z.h / 2, ch = Z.h / 2 - 34, pad = 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), 110, 28, {font:'head', max:17, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - 44, {font:'body', max:16, min:11, lh:1.38});
  var pts = [];
  it.forEach(function(t, k){ pts.push([Z.x + slot * (k + 1), my]); });
  C.line(Z.x, my, Z.x + Z.w, my, {color:LIME, lw:2, op:0.45});
  it.forEach(function(t, k){
    var cx = pts[k][0], up = k % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw / 2));
    var dh = C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(ch, pad * 2 + 44 + dh), y = up ? my - 34 - hh : my + 34;
    var cc = glass(C, x, y, cw, hh, {dot:false});
    C.line(cx, up ? y + hh : my, cx, up ? my : y, {color:LIME, lw:2});
    D.glow(C, cx, my, 20, 20, LIME, 0.6);
    C.rect(cx - 7, my - 7, 14, 14, {fill:LIME, r:3});
    pill(C, x + pad, y + pad, Math.min(cw - pad * 2, pw(t.h || '00', hs) + 10), 30, t.h || pad2(k + 1), {font:'head', size:hs, fill:k === n - 1 ? LIME : null, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x + pad, y + pad + 44, cw - pad * 2, hh - pad * 2 - 44, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* statement: lime blob mark + big white text */
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var x = Z.x, w = Z.w, y = Z.y;
  if (!o.img) { D.glow(C, Z.x + Z.w - 120, Z.y + Z.h - 100, 200, 160, LIME, 0.25); K.blob(C, Z.x + Z.w - 120, Z.y + Z.h - 100, 90, LIME, 1, 5); K.blob(C, Z.x + Z.w - 40, Z.y + Z.h - 190, 34, '#B6FF9A', 1, 2); w = Z.w - 300; }
  if (it.h) { pill(C, x, y, Math.min(w, pw(it.h, 15) + 10), 38, it.h, {size:15, fill:LIME, ed:{f:'x-part', i:it.i, p:0}}); y += 60; }
  C.text(x, y, w, Z.y + Z.h - y, it.d, {font:'head', fit:{max:44, min:18}, wt:600, color:'#FFFFFF', lh:1.25, va:'m', ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function blobCluster(C, cx, cy, s){
  D.glow(C, cx, cy, 300 * s, 260 * s, LIME, 0.28);
  K.blob(C, cx, cy, 150 * s, LIME2, 1, 1);
  K.blob(C, cx + 40 * s, cy - 30 * s, 118 * s, LIME, 1, 4);
  K.blob(C, cx - 30 * s, cy + 40 * s, 60 * s, '#C9FFB5', 0.9, 7);
  K.blob(C, cx + 150 * s, cy - 150 * s, 36 * s, LIME, 1, 2);
  K.blob(C, cx - 170 * s, cy + 120 * s, 22 * s, '#B6FF9A', 1, 6);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, img = o.img === 'photo';
  brand(C, 70, 42);
  if (f.date) C.text(W - 370, 42, 300, 22, f.date, {font:'body', size:14, wt:600, color:MUTE, align:'r', va:'m', lh:1});
  if (img) {
    C.rect(700, 110, 510, 500, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26});
    D.imgBox(C, 716, 126, 478, 468, {r:20});
    D.glow(C, 710, 590, 160, 120, LIME, 0.35);
    K.blob(C, 712, 588, 70, LIME, 1, 3); K.blob(C, 780, 640, 26, '#B6FF9A', 1, 6);
  } else blobCluster(C, W - 300, H / 2 + 20, 1.2);
  var tw = img ? 580 : 720;
  C.text(70, 140, tw, 300, C.s.title, {font:'head', fit:{max:96, min:38}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:1});
  if (C.s.subtitle) C.text(70, 462, tw - 60, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var xx = 70;
  [f.presenter, f.designation].filter(Boolean).forEach(function(t, k){ var w = Math.min(320, pw(t, 15)); pill(C, xx, 570, w, 42, t, {size:15, fill:k ? null : LIME}); xx += w + 12; });
}
function closing(C, v){
  var o = C.o || {}, img = o.img === 'photo';
  brand(C, 70, 42);
  if (img) {
    C.rect(700, 110, 510, 500, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26});
    D.imgBox(C, 716, 126, 478, 468, {r:20});
    K.blob(C, 1190, 130, 60, LIME, 1, 3);
  } else blobCluster(C, W - 280, H / 2, 1.1);
  var tw = img ? 580 : 720;
  C.text(70, 130, tw, 220, C.s.title || 'Thank You', {font:'head', fit:{max:104, min:40}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(70, 370, tw - 60, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){
    var y = 470 + k * 56;
    C.ell(70, y, 40, 40, {fill:LIME}); C.icon(80, y + 10, 20, c.icon, INK);
    C.text(126, y, tw - 60, 40, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(70, 130, 440, 260, C.s.title || 'Agenda', {font:'head', fit:{max:84, min:38}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1});
  if (C.s.subtitle) C.text(70, 130 + te._h + 20, 400, 80, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  D.glow(C, 120, 640, 220, 160, LIME, 0.3);
  K.blob(C, 150, 640, 110, LIME2, 1, 1); K.blob(C, 180, 610, 84, LIME, 1, 4);
  glassIndex(C, {x:560, y:90, w:W - 560 - 70, h:H - 160}, {cols:1});
}
function agendaPhoto(C, o){
  C.text(70, 86, 560, 70, C.s.title || 'Agenda', {font:'head', fit:{max:50, min:28}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  D.imgBox(C, 70, 180, 400, 460, {r:20});
  D.glow(C, 470, 180, 90, 90, LIME, 0.4); K.blob(C, 466, 184, 40, LIME, 1, 2);
  glassIndex(C, {x:530, y:180, w:W - 530 - 70, h:460}, {cols:1});
}
function statementPhoto(C, o){
  var y = D.header(C, {x:70, w:600, top:92}, {});
  C.rect(730, 100, 480, 520, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26});
  D.imgBox(C, 746, 116, 448, 488, {r:20});
  D.glow(C, 740, 610, 130, 100, LIME, 0.35); K.blob(C, 740, 606, 54, LIME, 1, 3);
  statement(C, {x:70, y:y + 40, w:600, h:H - 110 - y - 40}, {img:true});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:LIME, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', x = 70, w = photo ? 600 : 900;
  if (photo) { C.rect(740, 110, 470, 500, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26}); D.imgBox(C, 756, 126, 438, 468, {r:20}); }
  else { D.glow(C, W - 200, H / 2, 260, 220, LIME, 0.22); K.blob(C, W - 190, H / 2, 110, LIME, 1, 4); C.text(W - 280, H / 2 - 70, 180, 180, '”', {font:'head', size:200, wt:800, color:INK, align:'c', va:'m', lh:1, caps:false, ls:0}); }
  C.rect(x, 150, 70, 6, {fill:LIME, r:3});
  var qe = C.text(x, 190, w, 300, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:600, color:'#FFFFFF', lh:1.25, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ': ' + by.d : by.d; pill(C, x, 190 + qe._h + 36, Math.min(w, pw(bt, 15)), 42, bt, {size:15, ed:{f:'content-plain', i:by.i}}); }
}

D.register({
  id:'neonlime', name:'Neon Lime Glass', tag:'Tech · dark glass',
  desc:'Near-black green pages lit by lime glows and organic blobs, frosted glass cards with glowing dots, lime pill tags and dot labels for tech and startup decks.',
  fonts:{head:'Outfit', body:'Inter', headW:700, bodyW:400, headLh:1.05, gf:'Outfit:wght@400;600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Lime blobs', {}), V('v2', 'Glass photo frame', {img:'photo'})],
    closing:[V('v1', 'Lime blobs + contacts', {}), V('v2', 'Glass photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + glass index', {fn:agendaA}), V('n2', 'Pill tag grid', {body:pillGrid}), V('v2', 'Photo + glass index', {fn:agendaPhoto})],
    text:[V('v1', 'Lime blob statement', {body:statement}), V('v2', 'Statement + glass photo', {fn:statementPhoto}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass vs lime', {body:limeDuo}), V('n2', 'Dot columns + glass', {body:dotCols}), V('n3', 'Glass cards', {body:'xGlass'}), V('v2', 'Image left + dot rows', {body:dotCols, img:'left'}), V('v3', 'Glass index + image right', {body:glassIndex, img:'right'})],
    p3:[V('v1', 'Dot columns + glass', {body:dotCols}), V('n2', 'Glass cards', {body:'xGlass'}), V('n3', 'Pill tag grid', {body:pillGrid, cols:3}), V('v2', 'Image left + dot rows', {body:dotCols, img:'left'}), V('v3', 'Glass index + image right', {body:glassIndex, img:'right'})],
    p4:[V('v1', 'Glass cards', {body:'xGlass'}), V('n2', 'Dot columns + glass', {body:dotCols}), V('n3', 'Pill tag grid', {body:pillGrid}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right'}), V('v3', 'Image left + dot rows', {body:dotCols, img:'left'})],
    p56:[V('v1', 'Pill tag grid', {body:pillGrid}), V('n2', 'Glass index', {body:glassIndex}), V('n3', 'Glass cards', {body:'xGlass'}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right', cols:1})],
    p78:[V('v1', 'Pill tag grid', {body:pillGrid}), V('n2', 'Glass index', {body:glassIndex}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right', cols:2})],
    stats:[V('v1', 'Glass numbers', {body:glassPct}), V('n2', 'Lime blob numbers', {body:blobStats}), V('v2', 'Glass numbers + image right', {body:glassPct, img:'right'}), V('v3', 'Image left + blobs', {body:blobStats, img:'left'})],
    timeline:[V('v1', 'Glass cards on rail', {body:rail}), V('n2', 'Circuit timeline', {body:circuit}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right'})],
    quote:[V('v1', 'Lime blob quote', {}), V('v2', 'Quote + glass photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

