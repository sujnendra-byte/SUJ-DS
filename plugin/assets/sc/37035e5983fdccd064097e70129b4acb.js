
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

