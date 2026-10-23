
/* Template 13 — Graphite Loop : charcoal canvas, drawn silver loops, glass tiles, cyan light */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var P0 = {bg:'#0E0F12', text:'#E9EBEE', title:'#FFFFFF', muted:'#A2A7B0', accent:'#58D6E8', accent2:'#A9B2BE', card:'#1A1C21', line:'#30333A', onAccent:'#0B0C0E'};
var S = {
  margin:64, series:['#58D6E8', '#A9B2BE', '#3C9FB0', '#DCE1E6', '#7FC8D6', '#6B7380'],
  header:{kind:'left', tMax:46, tMin:26, sMax:17, top:52, kicker:function(C){ return C.cover.company || ''; }, kPill:'#5A5F68', kc:'#C9CED6', kSize:11, kGap:44},
  card:{kind:'solid', r:14, fill:'#191B20', line:'#2E3138', lw:1, hl:{kind:'solid', fill:'#E9EBEE', dark:false, tc:'#0E0F12', mc:'#3A3E46'}},
  icon:{kind:'square', r:12, bg:'#2A2D34', fg:'#58D6E8'},
  num:{kind:'pill', bg:'#2A2D34', fg:'#E9EBEE'},
  conc:{kind:'box', fill:'#15171B', line:'#30333A', lw:1, r:12, lc:'#58D6E8', label:'Key takeaway'},
  footer:{brand:true, pagePrefix:''}, xRows:'#17191D', hubFill:'#15171B', road:'#2A2D34', flowBar:'#22252B',
  imgFramed:true, imgR:16, glassFill:'#FFFFFF', glassOp:0.05
};
/* silver loops: a bundle of thin tilted ellipses, lighter on the inside */
function loops(C, cx, cy, s, rot){
  var cols = ['#F3F5F7', '#C9CED6', '#8D939C', '#5C626B'];
  for (var k = 0; k < 7; k++) K.loop(C, cx + k * 3, cy - k * 2, 260 * s - k * 9 * s, 96 * s - k * 3 * s, rot + k * 2.2, cols[k % 4], k === 0 ? 5 : 2.2, 0.9 - k * 0.08);
  for (var j = 0; j < 5; j++) K.loop(C, cx - 40 * s, cy + 40 * s, 180 * s - j * 8 * s, 180 * s - j * 8 * s, 0, cols[(j + 1) % 4], j === 0 ? 4 : 1.6, 0.75 - j * 0.1);
  K.spark(C, cx + 150 * s, cy - 110 * s, 18 * s, '#FFFFFF', 0.95);
}
function bg(C, kind){
  C.bg({grad:{type:'radial', stops:[[0, '#23262C'], [60, '#121317'], [100, '#08090B']]}});
  D.glow(C, 1150, 60, 320, 240, '#58D6E8', 0.07);
  if (kind !== 'cover' && kind !== 'closing' && kind !== 'quote') {
    for (var k = 0; k < 4; k++) K.loop(C, W + 10, -10, 150 - k * 14, 70 - k * 6, 30, k ? '#8D939C' : '#DCE1E6', k ? 1.4 : 2.6, 0.55);
    K.spark(C, W - 170, 118, 11, '#FFFFFF', 0.85);
  }
}
function metaLines(C, x, y, w){
  var f = C.cover, lines = [f.website, f.presenter ? f.presenter + (f.designation ? ' · ' + f.designation : '') : '', f.date].filter(Boolean);
  lines.forEach(function(l, k){ C.text(x, y + k * 24, w, 20, l, {font:'body', size:13, color:'#C9CED6', lh:1}); });
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {}, side = o.side || 'l';
  if (o.img) {
    var ix = side === 'l' ? 40 : W - 560;
    D.imgBox(C, ix, 40, 520, H - 80, {r:22});
    K.loop(C, ix + 260, H / 2, 330, 120, -24, '#DCE1E6', 3, 0.8);
    K.spark(C, ix + 470, 90, 20, '#FFFFFF', 1);
  } else if (o.center) { for (var k = 0; k < 5; k++) { K.loop(C, 150, H - 60, 240 - k * 16, 90 - k * 6, -20, k ? '#8D939C' : '#DCE1E6', k ? 1.4 : 2.6, 0.6); K.loop(C, W - 130, 70, 220 - k * 16, 80 - k * 6, 25, k ? '#8D939C' : '#DCE1E6', k ? 1.4 : 2.6, 0.6); } K.spark(C, W - 260, 150, 16, '#FFFFFF', 0.9); }
  else loops(C, side === 'l' ? 330 : W - 330, 380, 1.15, -28);
  var x = o.img ? (side === 'l' ? 620 : 64) : (side === 'l' ? 660 : 64), w = o.img ? 590 : 560;
  if (o.center) { x = 140; w = W - 280; }
  if (f.company) K.pill(C, x, 70, Math.min(260, f.company.length * 9 + 44), 30, f.company, {line:'#5A5F68', color:'#C9CED6', size:11, wt:700, caps:true, ls:0.1, ed:{f:'tf-company'}});
  C.text(x, 130, w, 320, C.s.title, {font:'head', fit:{max:74, min:34}, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.02, align:o.center ? 'c' : 'l'});
  C.rect(o.center ? W / 2 - 40 : x, 470, 80, 4, {fill:P.accent, r:2});
  if (C.s.subtitle) C.text(x, 494, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3, align:o.center ? 'c' : 'l'});
  if (!o.center) metaLines(C, x, 600, w);
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) { D.imgBox(C, W - 520, 40, 480, H - 80, {r:22}); K.loop(C, W - 280, H / 2, 300, 110, 24, '#DCE1E6', 3, 0.7); }
  else if (!o.center) loops(C, W - 300, 380, 1.1, 24);
  var x = o.center ? 140 : 64, w = o.center ? W - 280 : 640, al = o.center ? 'c' : 'l';
  C.text(x, 130, w, 260, C.s.title || 'Thank You', {font:'head', fit:{max:104, min:44}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1, align:al});
  if (C.s.subtitle) C.text(x + (o.center ? 120 : 0), 410, w - (o.center ? 240 : 0), 64, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, o.center ? 140 : 64, 540, o.center ? W - 280 : 640, {cards:true});
}
function agenda(C, v){
  var P = C.P, M = 64;
  C.text(M, 60, 700, 70, C.s.title || 'Table of Content', {font:'head', fit:{max:48, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols), cw = cols === 2 ? 555 : 820, rh = Math.min(96, (H - 230) / per), top = 160;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = M + c * (cw + 26), y = top + r * rh;
    C.rect(x, y, cw, rh - 12, {fill:'#1A1C21', line:'#2E3138', lw:1, r:10});
    C.rect(x, y, 5, rh - 12, {fill:P.accent, r:2});
    C.text(x + 20, y, 56, rh - 12, u.pad2(k + 1) + '.', {font:'head', size:22, color:P.accent, va:'m', lh:1, ls:0});
    C.text(x + 78, y, cw - 96, rh - 12, t.h || t.d, {font:'body', fit:{max:22, min:11}, wt:700, color:'#EDEFF2', va:'m', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
  for (var k = 0; k < 4; k++) K.loop(C, W - 40, H + 10, 190 - k * 16, 80 - k * 6, -30, '#8D939C', 1.4, 0.5);
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#58D6E8'}); return; }
  loops(C, 160, 600, 0.7, -20);
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:'#58D6E8'});
}
/* ============================================================
   SIGNATURE BODIES — orbit lines, silver loops, one cyan light
   ============================================================ */
var CY = '#58D6E8', SV = '#C9CED6', SV2 = '#8D939C', INK = '#0E0F12';
function fit(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function arc(C, cx, cy, r, a0, a1, o){ C.path(K.arcCmds(cx, cy, r, a0, a1), {stroke:o.color || SV, lw:o.lw || 1.2, lop:o.op != null ? o.op : 0.6}); }
function dHeight(C, t, w, ds){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:1.36}).h : 0; }
/* heading + description; headless items (agenda) show their text as the heading */
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || '#FFFFFF', align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || '#FFFFFF', mc:o.mc || '#A2A7B0', hLines:o.hLines});
}
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dHeight(C, t, w, F.ds) : F.hs * 1.2; }
function node(C, cx, cy, d, k, hot){
  C.ell(cx - d / 2, cy - d / 2, d, d, {fill:hot ? CY : '#15171B', line:hot ? CY : SV, lw:1.4});
  C.text(cx - d / 2, cy - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.max(11, Math.round(d * 0.36)), color:hot ? INK : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
}

/* 1 — rows hung on a sweeping orbit arc (lists, agenda, vertical timeline) */
function orbitRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var a = Z.h / 2 + 10, b = Math.min(96, Z.w * 0.1), R = (a * a + b * b) / (2 * b), cy = Z.y + Z.h / 2, cx = Z.x + b - R;
  var th = Math.asin(Math.min(1, (a + 16) / (R + 30))) / Math.PI * 180;
  [-34, 0, 30].forEach(function(dr, j){ arc(C, cx, cy, R + dr, -th, th, {color:j === 1 ? SV : SV2, lw:j === 1 ? 1.8 : 1, op:j === 1 ? 0.8 : 0.45}); });
  D.glow(C, Z.x - 40, cy, 160, Z.h * 0.55, CY, 0.08);
  var rh = Z.h / n, d = Math.max(26, Math.min(44, rh - 12)), tx = Z.x + b + d / 2 + 46, tw = Z.x + Z.w - tx;
  var F = fit(C, it, tw, Math.min(34, rh * 0.5), rh - 6, {hMax:n > 6 ? 17 : 21, dMax:n > 6 ? 13 : 16, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * rh, my = y + rh / 2, dy = my - cy, nx = cx + Math.sqrt(Math.max(0, R * R - dy * dy));
    if (k) C.rect(tx, y, tw, 1, {fill:'#30333A'});
    C.line(nx + d / 2 + 4, my, tx - 12, my, {color:SV2, lw:1, op:0.7, dash:true});
    node(C, nx, my, d, k, k === 0);
    var bh = blockH(C, t, tw, F);
    block(C, tx, my - Math.min(bh, rh) / 2, tw, Math.min(rh, bh + 4), t, F, {hLines:1});
  });
}

/* 2 — two overlapping orbit circles (p2) */
function twinOrbits(C, Z, o){
  var it = C.it.slice(0, 2); if (!it.length) return;
  var ov = 70, d = Math.min(Z.h + 10, (Z.w + ov) / 2), cy = Z.y + Z.h / 2, x0 = Z.x + (Z.w - (d * 2 - ov)) / 2;
  var bw = d * 0.66, F = fit(C, it, bw, 70, d * 0.5, {hMax:24, dMax:17, noBoost:true});
  it.forEach(function(t, k){
    var cx = x0 + d / 2 + k * (d - ov), light = k === 1;
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:light ? '#E9EBEE' : '#16181C', line:light ? '#E9EBEE' : SV2, lw:1.2});
    for (var j = 1; j < 4; j++) K.loop(C, cx + j * 2, cy - j * 3, d / 2 + j * 9, d / 2 + j * 5, 12 + j * 6, j === 1 ? SV : SV2, j === 1 ? 1.6 : 1, 0.55 - j * 0.1);
    var bh = blockH(C, t, bw, F), by = cy - (bh + 44) / 2;
    C.text(cx - bw / 2, by, bw, 26, u.pad2(k + 1), {font:'head', size:20, color:light ? '#1E7F8E' : CY, align:'c', lh:1, caps:false, ls:0});
    block(C, cx - bw / 2, by + 40, bw, bh + 6, t, F, {align:'c', tc:light ? INK : '#FFFFFF', mc:light ? '#3A3E46' : '#A2A7B0'});
  });
  var ix = x0 + d - ov / 2, iy = cy - Math.sqrt(Math.max(0, d * d / 4 - (ov / 2) * (ov / 2)));
  K.spark(C, ix, iy, 16, CY, 1); D.glow(C, ix, iy, 40, 40, CY, 0.3);
}

/* 3 — row of orbit planets: each ring carries an offset eclipse halo, joined by one orbit line; text below */
function eclipseRow(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var pitch = Z.w / n, cw = pitch - 28, d = Math.min(Z.h * 0.52, 250, pitch * 0.78), cy = Z.y + d / 2 + 4;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), d * 0.7, d * 0.34, {font:'head', max:n <= 3 ? 26 : 21, min:13, maxLines:2});
  var ty = Z.y + d + 34, ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw, Z.y + Z.h - ty, {font:'body', max:n <= 3 ? 18 : 16, min:11, lh:1.4});
  C.line(Z.x - 20, cy, Z.x + Z.w + 20, cy, {color:SV2, lw:1, op:0.4});
  it.forEach(function(t, k){
    var cx = Z.x + pitch * (k + 0.5), hot = k === n - 1;
    C.ell(cx - d / 2 + 14, cy - d / 2 - 6, d, d, {line:hot ? CY : SV2, lw:1, lop:hot ? 0.6 : 0.35});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:'#15171B', line:hot ? CY : SV, lw:hot ? 2 : 1.3, lop:hot ? 1 : 0.75});
    if (hot) D.glow(C, cx + d * 0.3, cy - d * 0.42, 50, 36, CY, 0.4);
    var sp = K.pol(cx, cy, d / 2, -50); C.ell(sp[0] - 5, sp[1] - 5, 10, 10, {fill:hot ? CY : SV});
    C.text(cx - d * 0.35, cy - d * 0.3, d * 0.7, 26, u.pad2(k + 1), {font:'head', size:Math.max(16, Math.round(d * 0.09)), color:CY, align:'c', lh:1, caps:false, ls:0});
    C.text(cx - d * 0.35, cy - d * 0.3 + 34, d * 0.7, d * 0.45, t.h || t.d, {font:'head', size:hs, color:'#FFFFFF', align:'c', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    C.line(cx, cy + d / 2 + 6, cx, ty - 8, {color:hot ? CY : SV2, lw:1, op:0.6, dash:true});
    if (t.h && t.d) C.text(cx - cw / 2, ty, cw, Z.y + Z.h - ty, t.d, {font:'body', size:ds, color:'#A2A7B0', align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 4 — constellation: open grid, star nodes joined in a snake line */
function constellation(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n === 3 ? 3 : (n <= 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 800 && cols > 2) cols = 2;
  var rows = Math.ceil(n / cols), gx = 40, gy = 26, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var F = fit(C, it, cw - 6, 50, ch - 50, {hMax:cols >= 4 ? 20 : 23, dMax:cols >= 4 ? 15 : 17});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, cw - 6, F)); });
  var cell = Math.min(ch, 44 + bh + 10), gy2 = rows > 1 ? Math.min(gy + (ch - cell) * 0.8, 90) : 0, tot = rows * cell + (rows - 1) * gy2, oy = Z.y + Math.max(0, (Z.h - tot) / 2);
  var pts = [];
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols; if (r % 2) c = Math.min(cols, n - r * cols) - 1 - c;
    pts.push([Z.x + c * (cw + gx) + 12, oy + r * (cell + gy2) + 12, t, k]);
  });
  for (var j = 1; j < pts.length; j++) C.line(pts[j - 1][0], pts[j - 1][1], pts[j][0], pts[j][1], {color:SV2, lw:1, op:0.55, dash:true});
  pts.forEach(function(p){
    var x = p[0] - 12, y = p[1] - 12, t = p[2], k = p[3], hot = k === 0;
    C.rect(x + 34, y + 11, cw - 90, 1, {fill:'#30333A'});
    C.ell(p[0] - 14, p[1] - 14, 28, 28, {fill:INK, line:hot ? CY : SV, lw:1.3});
    K.spark(C, p[0], p[1], 9, hot ? CY : '#FFFFFF', 1);
    if (hot) D.glow(C, p[0], p[1], 34, 34, CY, 0.35);
    C.text(x + cw - 52, y - 2, 50, 26, u.pad2(k + 1), {font:'head', size:15, color:hot ? CY : SV, align:'r', va:'m', lh:1, caps:false, ls:0});
    block(C, x, y + 42, cw - 6, cell - 42, t, F);
  });
}

/* 5 — four quadrants around a small loop bundle (p4) */
function quadOrbit(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4) return constellation(C, Z, o);
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r = Math.min(64, Z.h * 0.16);
  C.line(Z.x, cy, cx - r - 22, cy, {color:'#3A3E46', lw:1}); C.line(cx + r + 22, cy, Z.x + Z.w, cy, {color:'#3A3E46', lw:1});
  C.line(cx, Z.y, cx, cy - r - 22, {color:'#3A3E46', lw:1}); C.line(cx, cy + r + 22, cx, Z.y + Z.h, {color:'#3A3E46', lw:1});
  for (var j = 0; j < 4; j++) K.loop(C, cx, cy, r + 16 - j * 5, r * 0.5 - j * 2, -28 + j * 30, j ? SV2 : '#DCE1E6', j ? 1.2 : 2, 0.7);
  C.ell(cx - r * 0.42, cy - r * 0.42, r * 0.84, r * 0.84, {fill:CY}); D.glow(C, cx, cy, r * 1.2, r * 1.2, CY, 0.25);
  var qw = Z.w / 2 - r - 50, qh = Z.h / 2 - 22;
  var F = fit(C, it, qw, 64, qh - 50, {hMax:26, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var right = k % 2 === 1, low = k >= 2, x = right ? cx + r + 50 : Z.x, al = right ? 'l' : 'r';
    var bh = blockH(C, t, qw, F) + 40, y = low ? cy + 22 : Math.max(Z.y, cy - 22 - bh);
    C.text(x, y, qw, 30, u.pad2(k + 1), {font:'head', size:28, color:k === 0 ? CY : SV, align:al, lh:1, caps:false, ls:0});
    block(C, x, y + 40, qw, Math.min(qh - 40, bh), t, F, {align:al});
  });
}

/* 6 — orbit dials: thin track, cyan progress arc, satellite dot (2 × 2 dial + text when narrow) */
function dial(C, cx, cy, d, pct, val, vs, t){
  var r = d / 2;
  C.ell(cx - r, cy - r, d, d, {fill:'#15171B', line:SV2, lw:1, lop:0.5});
  K.loop(C, cx, cy, r + 12, r * 0.34, -18, SV2, 1, 0.45);
  var a1 = -90 + pct * 3.6; arc(C, cx, cy, r, -90, a1, {color:CY, lw:4, op:1});
  var e = K.pol(cx, cy, r, a1); C.ell(e[0] - 7, e[1] - 7, 14, 14, {fill:CY}); D.glow(C, e[0], e[1], 26, 26, CY, 0.4);
  C.text(cx - d * 0.38, cy - d * 0.2, d * 0.76, d * 0.4, val, {font:'head', size:vs, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
}
function orbitStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var vals = st.map(function(t){ return t.h || t.d; }), labs = st.map(function(t){ return t.h ? t.d : ''; });
  function pctOf(k){ var m = String(vals[k]).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.min(100, +m[1]) : [72, 58, 84, 64][k % 4]; }
  if (Z.w / n < 230 && n > 2) {
    var cols = 2, rows = Math.ceil(n / 2), gx = 30, gy = 24, cw = (Z.w - gx) / 2, ch = (Z.h - gy * (rows - 1)) / rows, d = Math.min(ch - 30, cw * 0.46, 170);
    var vs2 = C.groupFit(vals, d * 0.72, d * 0.36, {font:'head', max:40, min:16, maxLines:1}), tw = cw - d - 40;
    var ls2 = C.groupFit(labs, tw, ch * 0.4, {font:'head', max:19, min:13, maxLines:3}), ds2 = C.groupFit(st.map(function(t){ return t.x; }), tw, ch * 0.45, {font:'body', max:15, min:11, lh:1.36});
    st.forEach(function(t, k){
      var x = Z.x + (k % 2) * (cw + gx), y = Z.y + Math.floor(k / 2) * (ch + gy), cy = y + ch / 2;
      dial(C, x + d / 2 + 12, cy, d, pctOf(k), vals[k], vs2, t);
      var lh = labs[k] ? C.measureText(labs[k], tw, {font:'head', max:ls2, min:ls2, maxLines:3}).h : 0, dh = t.x ? C.measureText(t.x, tw, {font:'body', max:ds2, min:ds2, lh:1.36}).h : 0, by = cy - (lh + dh + 6) / 2;
      if (labs[k]) C.text(x + d + 40, by, tw, lh + 2, labs[k], {font:'head', size:ls2, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:3});
      if (t.x) C.text(x + d + 40, by + lh + 6, tw, dh + 4, t.x, {font:'body', size:ds2, color:'#A2A7B0', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
    });
    return;
  }
  var cw = Z.w / n, d = Math.min(cw - 44, Z.h * 0.56, 230);
  var vs = C.groupFit(vals, d * 0.72, d * 0.36, {font:'head', max:n <= 2 ? 60 : 50, min:22, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, 56, {font:'head', max:22, min:13, maxLines:2});
  var lh = 0; labs.forEach(function(l){ if (l) lh = Math.max(lh, C.measureText(l, cw - 30, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(t){ return t.x; }), cw - 30, Z.h - d - lh - 70, {font:'body', max:17, min:11, lh:1.38});
  var dh = 0; st.forEach(function(t){ if (t.x) dh = Math.max(dh, C.measureText(t.x, cw - 30, {font:'body', max:ds, min:ds, lh:1.38}).h); });
  var tot = d + 36 + lh + (dh ? dh + 10 : 0), y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = y0 + d / 2;
    dial(C, cx, cy, d, pctOf(k), vals[k], vs, t);
    var ly = cy + d / 2 + 30;
    if (labs[k]) C.text(cx - cw / 2 + 15, ly, cw - 30, lh + 2, labs[k], {font:'head', size:ls, color:'#FFFFFF', align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (t.x) C.text(cx - cw / 2 + 15, ly + lh + 10, cw - 30, Z.y + Z.h - ly - lh - 10, t.x, {font:'body', size:ds, color:'#A2A7B0', align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — ledger: oversized values on thin rules, meta text to the right */
function ledgerStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var rh = Z.h / n, vw = Math.round(Z.w * (Z.w < 800 ? 0.4 : 0.36)), tx = Z.x + vw + 40, tw = Z.x + Z.w - tx;
  var vals = st.map(function(t){ return t.h || t.d; });
  var vs = C.groupFit(vals, vw - 60, rh * 0.8, {font:'head', max:Math.min(92, rh * 0.7), min:24, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.h ? t.d : ''; }), tw, rh * 0.4, {font:'head', max:22, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x; }), tw, rh * 0.45, {font:'body', max:16, min:11, lh:1.38});
  st.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.rect(Z.x, y + rh - 1, Z.w, 1, {fill:k === n - 1 ? SV2 : '#30333A'});
    C.text(Z.x, y, 50, rh, u.pad2(k + 1), {font:'head', size:13, color:k === 0 ? CY : SV2, va:'m', lh:1, caps:false, ls:0});
    C.text(Z.x + 54, y, vw - 54, rh, vals[k], {font:'head', size:vs, color:k === 0 ? CY : '#FFFFFF', va:'m', lh:1, caps:false, ls:-0.01, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    K.loop(C, tx - 20, y + rh / 2, 6, 6, 0, SV, 1.4, 0.8);
    var lh = t.h ? C.measureText(t.d, tw, {font:'head', max:ls, min:ls, maxLines:2}).h : 0, dh = t.x ? C.measureText(t.x, tw, {font:'body', max:ds, min:ds, lh:1.38}).h : 0;
    var by = y + Math.max(4, (rh - lh - dh - (dh ? 6 : 0)) / 2);
    if (t.h) C.text(tx, by, tw, lh + 2, t.d, {font:'head', size:ls, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (t.x) C.text(tx, by + lh + 6, tw, Math.min(dh + 4, rh - lh - 10), t.x, {font:'body', size:ds, color:'#A2A7B0', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 8 — timeline on the front arc of a wide flat orbit */
function orbitTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, rx = Z.w / 2 + 30, ry = Math.min(70, Z.h * 0.16), cx = Z.x + Z.w / 2, cy = Z.y + ry + 20;
  K.loop(C, cx, cy, rx, ry, 0, SV, 1.6, 0.75); K.loop(C, cx, cy + 6, rx - 30, ry - 10, 0, SV2, 1, 0.4);
  C.ell(cx - 30, cy - 30, 60, 60, {fill:'#1A1C21', line:SV2, lw:1}); D.glow(C, cx, cy, 70, 50, CY, 0.18); K.spark(C, cx, cy, 12, CY, 1);
  var ys = [], d = 24;
  it.forEach(function(t, k){ var x = Z.x + cw * (k + 0.5), q = (x - cx) / rx; ys.push(cy + ry * Math.sqrt(Math.max(0, 1 - q * q))); });
  var ty = cy + ry + 44, F = fit(C, it, cw - 22, 40, Z.y + Z.h - ty - 6, {hMax:30, dMax:18.5, hLines:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), y = ys[k], hot = k === n - 1;
    C.line(x, y + d / 2, x, ty - 8, {color:hot ? CY : SV2, lw:1, op:0.7, dash:true});
    C.ell(x - d / 2, y - d / 2, d, d, {fill:hot ? CY : INK, line:hot ? CY : '#FFFFFF', lw:2});
    if (hot) D.glow(C, x, y, 34, 34, CY, 0.4);
    if (k) C.rect(x - cw / 2, ty, 1, Z.y + Z.h - ty, {fill:'#30333A'});
    block(C, x - cw / 2 + 11, ty, cw - 22, Z.y + Z.h - ty, t, F, {align:'c', tc:hot ? CY : '#FFFFFF'});
  });
}

/* 9 — trajectory hops: arcs leap from milestone to milestone */
function hopTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw / 2, Z.h * 0.26), base = Z.y + r + 12, d = 22;
  C.line(Z.x, base, Z.x + Z.w, base, {color:'#3A3E46', lw:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5);
    if (k < n - 1) { arc(C, x + cw / 2, base, cw / 2 - 2, 180, 360, {color:k === n - 2 ? CY : SV, lw:k === n - 2 ? 2 : 1.3, op:0.85}); K.spark(C, x + cw / 2, base - cw / 2 + 2, 7, k === n - 2 ? CY : '#FFFFFF', 1); }
  });
  var ty = base + 34, F = fit(C, it, cw - 24, 44, Z.y + Z.h - ty, {hMax:30, dMax:18.5, hLines:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), hot = k === n - 1;
    C.ell(x - d / 2, base - d / 2, d, d, {fill:hot ? CY : INK, line:hot ? CY : '#FFFFFF', lw:2});
    C.ell(x - 4, base - 4, 8, 8, {fill:hot ? INK : '#FFFFFF'});
    block(C, x - cw / 2 + 12, ty, cw - 24, Z.y + Z.h - ty, t, F, {align:'c', tc:hot ? CY : '#FFFFFF'});
  });
}

D.register({
  id:'graphite', name:'Graphite Loop', tag:'Charcoal & silver · agency', desc:'Charcoal canvas, hand-drawn silver loops, glass tiles and a cool cyan light.',
  fonts:{head:'Sora', body:'Inter', headW:700, bodyW:400, headLh:1.05, headLs:0, gf:'Sora:wght@500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Silver loops',{}), V('v2','Image left + loops',{img:true, side:'l'}), V('v3','Title left + image',{img:true, side:'r'}), V('n2','Centered title',{center:true})],
    closing:[V('v1','Loops right',{}), V('v2','Image right',{img:true}), V('n2','Centered',{center:true})],
    agenda:[V('v1','Content tiles',{}), V('n2','Orbit list',{body:orbitRows}), V('v2','Orbit list + image right',{body:orbitRows, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Statement',{body:'text'}), V('n2','Statement in glass card',{body:'text', box:true}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Twin orbits',{body:twinOrbits}), V('n2','Eclipse rings',{body:eclipseRow}), V('n3','Orbit list',{body:orbitRows}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Orbit list + image right',{body:orbitRows, img:'right'}), V('n4','Graphite panels',{body:'duo'})],
    p3:[V('v1','Eclipse rings',{body:eclipseRow}), V('n2','Orbit list',{body:orbitRows}), V('n3','Arc segments',{body:'xArc'}), V('n4','Constellation',{body:constellation}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Constellation + image right',{body:constellation, img:'right', cols:1}), V('n5','Numbered columns',{body:'numbered', lineC:'#30333A'})],
    p4:[V('v1','Orbit quadrants',{body:quadOrbit}), V('n2','Eclipse rings',{body:eclipseRow}), V('n3','Arc segments',{body:'xArc'}), V('n4','Constellation',{body:constellation}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Quadrants + image right',{body:quadOrbit, img:'right'}), V('n5','Graphite grid',{body:'grid'})],
    p56:[V('v1','Constellation',{body:constellation}), V('n2','Orbit list',{body:orbitRows}), V('n3','Arc segments',{body:'xArc'}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Constellation + image right',{body:constellation, img:'right'}), V('n4','Graphite grid',{body:'grid'})],
    p78:[V('v1','Constellation',{body:constellation}), V('n2','Orbit list',{body:orbitRows}), V('v2','Constellation + image right',{body:constellation, img:'right'}), V('n3','Two-column tiles',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Orbit dials',{body:orbitStats}), V('n2','Ledger',{body:ledgerStats}), V('v2','Image left + ledger',{body:ledgerStats, img:'left'}), V('v3','Orbit dials + image right',{body:orbitStats, img:'right'}), V('n3','Big numbers',{body:'statCols'})],
    timeline:[V('v1','Orbit timeline',{body:orbitTimeline}), V('n2','Trajectory hops',{body:hopTimeline}), V('v2','Orbit list + image right',{body:orbitRows, img:'right'}), V('v3','Image left + orbit list',{body:orbitRows, img:'left'}), V('n3','Vertical timeline',{body:'tlV'})],
    quote:[V('v1','Loop quote',{foot:false}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

