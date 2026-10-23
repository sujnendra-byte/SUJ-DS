
/* Template 17 — Isometric Slate : white pages, navy ribbon labels, orange signal colour, isometric blocks + targets */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#16284F', BLUE = '#3E5F9E', ORNG = '#F4843C', STEEL = '#B8C3D9';
var P0 = {bg:'#F3F5FA', text:'#16284F', title:'#16284F', muted:'#56627E', accent:'#F4843C', accent2:'#3E5F9E', card:'#FFFFFF', line:'#D5DBE8', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[NAVY, BLUE, ORNG, '#7F95C4', '#2B3F6E', '#F7A76E'],
  header:{kind:'left', tMax:40, tMin:24, sMax:16, top:50, kicker:function(C){ return u.pad2(C.i + 1) + ' / ' + (C.cover.company || 'Report'); }, kc:ORNG, kSize:12, kGap:30},
  card:{kind:'top', r:10, fill:'#FFFFFF', shadow:0.07, barW:5, hl:{kind:'solid', fill:NAVY, dark:true}},
  icon:{kind:'square', r:10, bg:NAVY, fg:'#FFFFFF'},
  num:{kind:'square', r:8, bg:NAVY, fg:'#FFFFFF'},
  conc:{kind:'band', fill:NAVY, r:10, label:'Bottom line'},
  footer:{brand:false, page:true, color:'#8A94AD'}, xRows:'#FFFFFF', hubFill:'#E4E9F3', road:NAVY, flowBar:'#DCE2EE', stepWord:'Step',
  imgFramed:true, imgR:12
};
/* isometric cube cluster (decoration) */
function iso(C, x, y, s, c){
  K.poly(C, [[x, y], [x + s, y - s * 0.55], [x + 2 * s, y], [x + s, y + s * 0.55]], {fill:u.mix(c, '#FFFFFF', 0.35)});
  K.poly(C, [[x, y], [x + s, y + s * 0.55], [x + s, y + s * 1.55], [x, y + s]], {fill:c});
  K.poly(C, [[x + 2 * s, y], [x + s, y + s * 0.55], [x + s, y + s * 1.55], [x + 2 * s, y + s]], {fill:u.mix(c, '#000000', 0.25)});
}
function bg(C, kind){
  C.bg({fill:'#F3F5FA'});
  if (kind !== 'cover' && kind !== 'closing') { C.rect(0, 0, 10, H, {fill:NAVY}); C.rect(0, 0, 10, 120, {fill:ORNG}); iso(C, W - 104, 40, 22, STEEL); iso(C, W - 60, 64, 16, ORNG); }
}
function stairsDecor(C, x0, y0, n, s){
  for (var k = 0; k < n; k++) iso(C, x0 + k * s * 1.6, y0 - k * s * 1.1, s, [NAVY, BLUE, STEEL, ORNG, BLUE][k % 5]);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.rect(0, 0, W * 0.5, H, {fill:NAVY});
  if (o.img) D.imgBox(C, W * 0.5, 0, W * 0.5, H, {r:0});
  else { stairsDecor(C, W * 0.56, H - 150, 5, 58); K.target(C, W - 150, 170, 90, ORNG, '#FFFFFF', 4); }
  var x = 64, w = W * 0.5 - 120;
  if (f.company) C.text(x, 70, w, 22, f.company, {font:'body', size:14, wt:800, color:ORNG, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 120, w, 300, C.s.title, {font:'head', fit:{max:62, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  C.rect(x, 446, 70, 6, {fill:ORNG});
  if (C.s.subtitle) C.text(x, 472, w, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#C9D2E6', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 620, w, 22, meta, {font:'body', size:14, wt:600, color:'#C9D2E6'});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  C.rect(W * 0.5, 0, W * 0.5, H, {fill:NAVY});
  if (o.img) D.imgBox(C, 0, 0, W * 0.5, H, {r:0}); else stairsDecor(C, 90, H - 140, 4, 60);
  var x = W * 0.5 + 64, w = W * 0.5 - 128;
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  C.rect(x, 370, 70, 6, {fill:ORNG});
  if (C.s.subtitle) C.text(x, 396, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#C9D2E6', ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 490, w, {vc:'#FFFFFF', markO:{bg:ORNG}});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:ORNG}); return; }
  K.target(C, 150, H - 140, 90, NAVY, '#FFFFFF', 4);
  D.B.quote(C, {x:260, y:130, w:W - 460, h:H - 260}, {qc:ORNG});
}

/* ---------- signature helpers ---------- */
function DK(c, t){ return u.mix(c, '#000000', t == null ? 0.28 : t); }
function LT(c, t){ return u.mix(c, '#FFFFFF', t == null ? 0.4 : t); }
/* oblique box: front face x,y,w,h; depth d up-right */
function obox(C, x, y, w, h, d, c, o){
  o = o || {}; var dy = d * 0.75;
  K.poly(C, [[x, y], [x + d, y - dy], [x + w + d, y - dy], [x + w, y]], {fill:o.top || LT(c, 0.38)});
  K.poly(C, [[x + w, y], [x + w + d, y - dy], [x + w + d, y + h - dy], [x + w, y + h]], {fill:o.side || DK(c, 0.25)});
  C.rect(x, y, w, h, {fill:c, line:o.line, lw:o.lw});
}
/* isometric slab: top rhombus centred cx,cy */
function slab(C, cx, cy, w, t, c){
  var hh = w * 0.29;
  K.poly(C, [[cx - w / 2, cy], [cx, cy + hh], [cx, cy + hh + t], [cx - w / 2, cy + t]], {fill:DK(c, 0.12)});
  K.poly(C, [[cx + w / 2, cy], [cx, cy + hh], [cx, cy + hh + t], [cx + w / 2, cy + t]], {fill:DK(c, 0.34)});
  K.poly(C, [[cx - w / 2, cy], [cx, cy - hh], [cx + w / 2, cy], [cx, cy + hh]], {fill:c});
}
/* slanted technical tag */
function tag(C, x, y, w, h, txt, fill, fg, sz){
  var k = h * 0.35;
  K.poly(C, [[x + k, y], [x + w, y], [x + w - k, y + h], [x, y + h]], {fill:fill});
  C.text(x, y, w, h, txt, {font:'body', size:sz || Math.min(13, Math.round(h * 0.5)), wt:800, color:fg || K.on(fill), align:'c', va:'m', caps:true, ls:0.06, lh:1, maxLines:1});
}
/* corner brackets */
function corners(C, x, y, w, h, c, s, lw){
  s = s || 14; lw = lw || 2;
  [[x, y, 1, 1], [x + w, y, -1, 1], [x, y + h, 1, -1], [x + w, y + h, -1, -1]].forEach(function(q){
    C.rect(q[2] > 0 ? q[0] : q[0] - s, q[3] > 0 ? q[1] : q[1] - lw, s, lw, {fill:c});
    C.rect(q[2] > 0 ? q[0] : q[0] - lw, q[3] > 0 ? q[1] : q[1] - s, lw, s, {fill:c});
  });
}
/* dimension line with arrow ends and end ticks */
function dim(C, x1, x2, y, c){
  C.line(x1, y - 8, x1, y + 8, {color:c, lw:1.5}); C.line(x2, y - 8, x2, y + 8, {color:c, lw:1.5});
  C.line(x1 + 2, y, x2 - 2, y, {color:c, lw:1.5});
  K.poly(C, [[x1 + 1, y], [x1 + 11, y - 4.5], [x1 + 11, y + 4.5]], {fill:c}); K.poly(C, [[x2 - 1, y], [x2 - 11, y - 4.5], [x2 - 11, y + 4.5]], {fill:c});
}
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
/* heading + description block, vertically centred in h */
function hdc(C, x, y, w, h, t, F, o){
  var bh = (t.h ? F.hh + 6 : 0) + descH(C, t, w, F);
  var yy = y + Math.max(0, (h - bh) / 2);
  return K.hd(C, x, yy, w, h - (yy - y), t, F, o);
}
/* single-line label items (agenda) */
function lab(C, x, y, w, h, t, sz, color, o){
  o = o || {};
  C.text(x, y, w, h, t.h || t.d, {font:'head', size:sz, color:color, va:'m', lh:1.15, align:o.align || 'l', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
}
function labFit(C, it, w, h){ return C.groupFit(it.map(function(t){ return t.h || t.d; }), w, h, {font:'head', max:it.length <= 4 ? 28 : 22, min:13, maxLines:2}); }

/* 1. exploded layer stack: isometric slabs on the left, leader lines to rows */
function slabStack(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var gy = n > 6 ? 6 : 12, rh = (Z.h - gy * (n - 1)) / n, sp = rh + gy, t = Math.max(8, Math.min(16, rh * 0.16));
  var sw = Math.max(96, Math.min(Z.w * 0.24, 280, (sp - t - 8) / 0.58)), open = sw * 0.58 + t + 6 <= sp;
  var cx = Z.x + sw / 2 + 4, lx = Z.x + sw + 70, tw = Z.x + Z.w - lx - 72;
  var cy0 = Z.y + rh / 2 - t / 2, cyN = cy0 + (n - 1) * sp;
  [cx - sw / 2, cx, cx + sw / 2].forEach(function(gx, j){ C.line(gx, cy0 + (j === 1 ? sw * 0.29 : 0), gx, cyN + (j === 1 ? sw * 0.29 : 0), {color:STEEL, lw:1.2, dash:true}); });
  for (var k = n - 1; k >= 0; k--) {
    var cy = cy0 + k * sp, c = K.col(C, k);
    slab(C, cx, cy, sw, t, c);
    if (open) C.text(cx - sw * 0.3, cy - sw * 0.14, sw * 0.6, sw * 0.28, u.pad2(k + 1), {font:'head', size:Math.min(26, Math.max(13, sw * 0.12)), wt:800, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0});
  }
  var H2 = hasH(it), F = H2 ? K.fitHD(C, it, tw, rh * 0.45, rh - 4, {hMax:20, dMax:15.5, hMin:13, dMin:11, hLines:1}) : null, ls = H2 ? 0 : labFit(C, it, tw, rh);
  it.forEach(function(t, k){
    var cy = cy0 + k * sp, y = Z.y + k * sp, c = K.col(C, k);
    C.line(cx + sw / 2 + 6, cy, lx - 10, cy, {color:c, lw:1.5});
    C.ell(lx - 13, cy - 4, 8, 8, {fill:c});
    tag(C, lx, cy - 13, 58, 26, 'L' + u.pad2(k + 1), k % 3 === 2 ? ORNG : NAVY);
    if (H2) hdc(C, lx + 72, y, tw, rh, t, F, {tc:NAVY}); else lab(C, lx + 72, y, tw, rh, t, ls, NAVY);
  });
}

/* 2. module grid: bracket-framed plates with an oblique numbered cube and grid coordinates */
function moduleGrid(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : 4)), rows = Math.ceil(n / cols);
  var gx = 22, gyy = 22, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gyy * (rows - 1)) / rows, pad = 22, cs = rows > 1 ? 38 : 48, d = cs * 0.28;
  var hw = cw - pad * 2 - cs - d - 16, dy0 = pad + d * 0.75 + cs + 14;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), hw, cs + 8, {font:'head', max:rows > 1 ? 19 : 22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - pad * 2, ch - dy0 - pad - 10, {font:'body', max:rows > 1 ? 17 : (n <= 3 ? 21 : 19), min:11, lh:1.38});
  var dmax = 0; it.forEach(function(t){ if (t.h) dmax = Math.max(dmax, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h); });
  var need = dy0 + dmax + pad + 30, zy = Z.y;
  if (rows === 1 && ch > need + 20) { zy = Z.y + (ch - need) * 0.4; ch = need; }
  var off = Math.max(0, (ch - pad - 14 - dy0 - dmax) * 0.42);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * (cw + gx) + (cols - Math.min(cols, n - r * cols)) * (cw + gx) / 2, y0 = zy + r * (ch + gyy), c = K.col(C, k), y = y0 + off;
    C.rect(x, y0, cw, ch, {fill:'#FFFFFF', r:4, shadow:0.06});
    corners(C, x - 4, y0 - 4, cw + 8, ch + 8, NAVY, 16, 2);
    obox(C, x + pad, y + pad + d * 0.75, cs, cs, d, c);
    C.text(x + pad, y + pad + d * 0.75, cs, cs, u.pad2(k + 1), {font:'head', size:Math.round(cs * 0.4), wt:800, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad + cs + d + 16, y + pad, hw, cs + d * 0.75, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    if (t.h) C.text(x + pad, y + dy0, cw - pad * 2, dmax + 6, t.d, {font:'body', size:ds, color:P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
    C.rect(x + pad, y0 + ch - 10, 36, 4, {fill:ORNG});
    C.text(x + cw - 70, y0 + ch - 24, 56, 16, String.fromCharCode(65 + r) + '·' + (cc + 1), {font:'body', size:11, wt:800, color:'#8A94AD', align:'r', lh:1, caps:false, ls:0.1});
  });
}

/* 3. two oblique boxes with an orange direction block between them */
function obliqueDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var d = 30, dy = d * 0.75, mid = n > 1 ? 96 : 0, bw = (Z.w - mid - d) / n, top = Z.y + dy + 4, bh = Z.y + Z.h - top - 6;
  var pad = bw > 400 ? 38 : 26, iw = bw - pad * 2;
  var F = K.fitHD(C, it, iw, 80, bh - pad * 2 - 44, {hMax:27, dMax:19, hMin:14, dMin:11.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (bw + mid), dark = k === 0, fill = dark ? NAVY : '#FFFFFF';
    obox(C, x, top, bw, bh, d, fill, dark ? {} : {top:'#D9DFEC', side:STEEL});
    if (!dark) C.rect(x, top, bw, bh, {line:'#C9D1E2', lw:1.5});
    corners(C, x + 12, top + 12, bw - 24, bh - 24, dark ? ORNG : NAVY, 18, 2);
    tag(C, x + pad, top + pad, 92, 28, (k ? 'Side B' : 'Side A'), dark ? ORNG : NAVY, '#FFFFFF', 12);
    hdc(C, x + pad, top + pad + 44, iw, bh - pad * 2 - 44, t, F, {tc:dark ? '#FFFFFF' : NAVY, mc:dark ? '#C9D2E6' : P.muted});
  });
  if (n > 1) {
    var ax = Z.x + bw + 14, ay = top + bh / 2 - 34;
    obox(C, ax, ay + 10, mid - 38, 48, 12, ORNG);
    K.poly(C, [[ax + mid - 38, ay - 2], [ax + mid - 12, ay + 34], [ax + mid - 38, ay + 70]], {fill:ORNG});
  }
}

/* 4. isometric prism chart for stats */
function prismStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, pw = Math.min(cw * 0.42, 118), d = pw * 0.32, dy = d * 0.75;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), cw - 24, 50, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), cw - 24, 44, {font:'body', max:14, min:11, maxLines:2});
  var anyD = st.some(function(t){ return t.d; }), below = ls * 1.2 * 2 + 12 + (anyD ? ds * 1.36 * 2 + 6 : 0) + 14;
  var by = Z.y + Z.h - below;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), cw - 16, 64, {font:'head', max:52, min:24, maxLines:1});
  var hMax = by - Z.y - vs * 1.1 - dy - 14, hMin = hMax * 0.42;
  K.poly(C, [[Z.x, by], [Z.x + 22, by - 18], [Z.x + Z.w, by - 18], [Z.x + Z.w - 22, by]], {fill:'#E1E6F0'});
  C.rect(Z.x, by, Z.w - 22, 5, {fill:NAVY});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = [BLUE, NAVY, '#2B3F6E', ORNG][k % 4], h = hMin + (hMax - hMin) * (n === 1 ? 1 : k / (n - 1));
    var x = cx - (pw + d) / 2, y = by - h - 4;
    obox(C, x, y, pw, h, d, c);
    for (var j = 1; j < 4; j++) C.rect(x + 10, y + h * j / 4, pw - 20, 1, {fill:'#FFFFFF', op:0.18});
    C.text(Z.x + cw * k + 8, y - dy - vs * 1.1 - 6, cw - 16, vs * 1.1, t.v, {font:'head', size:vs, color:k === n - 1 ? ORNG : NAVY, align:'c', lh:1, caps:false, ls:0, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + cw * k + 12, by + 16, cw - 24, ls * 2.5, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(Z.x + cw * k + 12, by + 16 + ls * 2.4 + 6, cw - 24, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 5. technical-drawing stats: code label, huge value, dimension line, label */
function dimStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var narrow = Z.w < 820 && n > 2, cols = narrow ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, rH = Z.h / rows, iw = cw - 44;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, rH * 0.4, {font:'head', max:rows > 1 ? 54 : (n <= 2 ? 96 : 76), min:26, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 52, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, rH * 0.22, {font:'body', max:15, min:11, lh:1.38});
  var blk = 30 + vs * 1.05 + 24 + ls * 2.45 + (st.some(function(t){ return t.d; }) ? ds * 1.38 * 2 + 8 : 0);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * cw + 22, y = Z.y + r * rH + Math.max(0, (rH - blk) / 2);
    if (cc) { C.line(Z.x + cc * cw, Z.y + r * rH + 10, Z.x + cc * cw, Z.y + (r + 1) * rH - 10, {color:STEEL, lw:1.2, dash:true}); }
    tag(C, x, y, 78, 22, 'Fig ' + u.pad2(k + 1), k === 0 ? ORNG : NAVY, '#FFFFFF', 11);
    C.text(x, y + 30, iw, vs * 1.05, t.v, {font:'head', size:vs, color:NAVY, lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    dim(C, x, x + iw, y + 30 + vs * 1.05 + 12, k === 0 ? ORNG : BLUE);
    C.text(x, y + 30 + vs * 1.05 + 24, iw, ls * 2.45, t.l, {font:'head', size:ls, color:NAVY, lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x, y + 30 + vs * 1.05 + 24 + ls * 2.45 + 6, iw, ds * 1.38 * 2 + 4, t.d, {font:'body', size:ds, color:P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 6. rising isometric rail of tiles (timeline) */
function isoRail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, tw = Math.min(cw * 0.78, 168), hh = tw * 0.29, t = 14, rise = Math.min(0.56, 0.3 + n * 0.05);
  var pts = it.map(function(q, k){ return [Z.x + cw * (k + 0.5), Z.y + Z.h * (0.5 + rise / 2 - rise * (n === 1 ? 0.5 : k / (n - 1)))]; });
  C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1] + hh * 0.5]; }), {stroke:STEEL, lw:12});
  C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1] + hh * 0.5]; }), {stroke:ORNG, lw:2});
  var room = Z.h * (0.5 - rise / 2) + Z.h * rise * 0.5 - hh - 30;
  var F = K.fitHD(C, it.map(function(q){ return {i:q.i, h:'', d:q.d}; }), cw - 16, 1, Math.max(60, room), {dMax:18, dMin:11, noBoost:true});
  var ys = C.groupFit(it.map(function(q){ return q.h || ''; }), tw * 0.6, hh * 0.8, {font:'head', max:24, min:13, maxLines:1});
  it.forEach(function(q, k){
    var p = pts[k], c = K.col(C, k), up = p[1] > Z.y + Z.h / 2 || (n === 1);
    slab(C, p[0], p[1], tw, t, c);
    C.text(p[0] - tw * 0.3, p[1] - hh * 0.45, tw * 0.6, hh * 0.9, q.h || u.pad2(k + 1), {font:'head', size:ys, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0, ed:q.h ? {f:'x-part', i:q.i, p:0} : null, maxLines:1});
    var dh = descH(C, q, cw - 16, F);
    if (up) {
      var yb = p[1] - hh - 16; C.line(p[0], p[1] - hh - 2, p[0], yb + 4, {color:c, lw:1.5, dash:true});
      C.text(Z.x + cw * k + 8, yb - dh - 6, cw - 16, dh + 4, q.d, {font:'body', size:F.ds, color:P.text, align:'c', lh:1.36, ed:{f:'x-part', i:q.i, p:1}});
    } else {
      var yt = p[1] + hh + t + 16; C.line(p[0], p[1] + hh + t + 2, p[0], yt - 4, {color:c, lw:1.5, dash:true});
      C.text(Z.x + cw * k + 8, yt + 2, cw - 16, dh + 4, q.d, {font:'body', size:F.ds, color:P.text, align:'c', lh:1.36, ed:{f:'x-part', i:q.i, p:1}});
    }
  });
}

/* 7. staggered oblique bars (gantt-style) with labels in slanted tags */
function isoGantt(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var d = 14, dy = d * 0.75, gy = 12, rh = (Z.h - dy - gy * (n - 1)) / n;
  var hw = 0; it.forEach(function(t){ if (t.h) hw = Math.max(hw, C.measureText(t.h, 900, {font:'head', max:18, min:18, maxLines:1}).h ? t.h.length : 0); });
  var lw = hw > 7 ? Math.min(230, Z.w * 0.26) : 128, x0 = Z.x + lw + 18, span = Z.x + Z.w - d - x0, step = n > 1 ? span * 0.34 / (n - 1) : 0;
  var hs = C.groupFit(it.map(function(t){ return t.h || u.pad2(1); }), lw - 30, rh - 6, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), span - step * (n - 1) - 40, rh - 12, {font:'body', max:19, min:11, lh:1.3});
  for (var g = 0; g <= 4; g++) { var gx = x0 + span * g / 4; C.line(gx, Z.y, gx, Z.y + Z.h, {color:'#DCE1EC', lw:1}); }
  it.forEach(function(t, k){
    var y = Z.y + dy + k * (rh + gy), c = k === n - 1 ? ORNG : [NAVY, BLUE, '#2B3F6E', '#4A6AA8'][k % 4], bx = x0 + step * k, bw = x0 + span - bx;
    C.shape('homePlate', Z.x, y + 4, lw, rh - 8, {fill:k === n - 1 ? ORNG : '#E4E9F3'});
    C.text(Z.x + 14, y + 4, lw - 36, rh - 8, t.h || u.pad2(k + 1), {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    obox(C, bx, y, bw, rh, d, c);
    C.text(bx + 18, y, bw - 34, rh, t.d, {font:'body', size:ds, wt:500, color:k === n - 1 ? NAVY : '#FFFFFF', va:'m', lh:1.3, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 8. data sheet: REF tags, item column, specification column, dotted rules */
function specSheet(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var H2 = hasH(it), hy = 26, rh = (Z.h - hy - 4) / n, refW = 64, iw = H2 ? Math.min(300, Z.w * 0.3) : Z.w - refW - 24, dx = Z.x + refW + 20 + iw + 20, dw = Z.x + Z.w - dx;
  C.text(Z.x, Z.y, refW, 16, 'Ref', {font:'body', size:11, wt:800, color:ORNG, caps:true, ls:0.14, lh:1});
  C.text(Z.x + refW + 20, Z.y, iw, 16, 'Item', {font:'body', size:11, wt:800, color:ORNG, caps:true, ls:0.14, lh:1});
  if (H2) C.text(dx, Z.y, dw, 16, 'Specification', {font:'body', size:11, wt:800, color:ORNG, caps:true, ls:0.14, lh:1});
  C.rect(Z.x, Z.y + 20, Z.w, 2.5, {fill:NAVY});
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), iw, rh - 6, {font:'head', max:H2 ? (n <= 4 ? 23 : 20) : 22, min:13, maxLines:H2 ? 2 : 1});
  var ds = H2 ? C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 6, {font:'body', max:n <= 2 ? 22 : (n <= 4 ? 20 : 17), min:11, lh:1.3}) : 0;
  it.forEach(function(t, k){
    var y = Z.y + hy + k * rh;
    if (k % 2 === 0) C.rect(Z.x, y, Z.w, rh, {fill:'#FFFFFF'});
    if (k < n - 1) C.line(Z.x, y + rh, Z.x + Z.w, y + rh, {color:STEEL, lw:1, dash:true});
    tag(C, Z.x + 8, y + rh / 2 - 12, refW - 8, 24, String.fromCharCode(65 + Math.floor(k / 4)) + '-' + (k % 4 + 1), k === 0 ? ORNG : NAVY, '#FFFFFF', 11);
    C.text(Z.x + refW + 20, y, iw, rh, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:H2 ? 2 : 1});
    if (H2) { C.rect(dx - 12, y + rh / 2 - 1, 4, 2, {fill:ORNG}); C.text(dx, y, dw, rh, t.d, {font:'body', size:ds, color:P.muted, va:'m', lh:1.3, ed:{f:'x-part', i:t.i, p:1}}); }
  });
}

/* 9. statement inside a large oblique box */
function isoStatement(C, Z, o){
  var P = C.P, t = C.it[0], d = 34, dy = d * 0.75, x = Z.x, y = Z.y + dy + 6, w = Z.w - d - 8, h = Z.h - dy - 10;
  obox(C, x, y, w, h, d, '#FFFFFF', {top:'#D9DFEC', side:STEEL});
  C.rect(x, y, 10, h, {fill:NAVY}); C.rect(x, y, 10, h * 0.3, {fill:ORNG});
  corners(C, x + 26, y + 18, w - 44, h - 36, NAVY, 20, 2);
  var pad = 58;
  if (!t) { C.text(x + pad, y + pad, w - pad * 2, h - pad * 2, C.s.subtitle || '', {font:'body', fit:{max:30, min:16}, color:NAVY, va:'m', lh:1.4, ed:{f:'subtitle'}}); return; }
  if (t.h) tag(C, x + pad, y + 44, Math.min(260, t.h.length * 11 + 50), 30, t.h, ORNG, '#FFFFFF', 13);
  var ty = y + (t.h ? 96 : 50);
  C.text(x + pad, ty, w - pad * 2, y + h - 44 - ty, t.d, {font:'head', fit:{max:36, min:17}, color:NAVY, va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'isometric', name:'Isometric Slate', tag:'Navy · orange · analysis', desc:'Crisp slate pages with a navy spine, orange signal colour, isometric blocks, targets and step tabs for risk and strategy decks.',
  fonts:{head:'Outfit', body:'Inter', headW:700, bodyW:400, headLh:1.06, gf:'Outfit:wght@400;500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Navy half + blocks',{}), V('v2','Navy half + image',{img:true})],
    closing:[V('v1','Blocks + navy half',{}), V('v2','Image + navy half',{img:true})],
    agenda:[V('v1','Exploded layers',{body:slabStack}), V('n2','Data sheet',{body:specSheet}), V('n3','Module grid',{body:moduleGrid}), V('v2','Layers + image right',{body:slabStack, img:'right'}), V('n4','Numbered list',{body:'agList'})],
    text:[V('v1','Oblique statement box',{body:isoStatement}), V('n2','Statement',{body:'text'}), V('v2','Box + image right',{body:isoStatement, img:'right'}), V('v3','Image left + box',{body:isoStatement, img:'left'})],
    p2:[V('v1','Oblique A / B boxes',{body:obliqueDuo}), V('n2','Module plates',{body:moduleGrid}), V('n3','Data sheet',{body:specSheet}), V('v2','Image left + layers',{body:slabStack, img:'left'}), V('v3','Plates + image right',{body:moduleGrid, img:'right'})],
    p3:[V('v1','Exploded layers',{body:slabStack}), V('n2','Module plates',{body:moduleGrid}), V('n3','Staggered bars',{body:isoGantt}), V('n4','Target + list',{body:'xTarget'}), V('n5','Isometric steps',{body:'xStairs'}), V('v2','Image left + layers',{body:slabStack, img:'left'}), V('v3','Data sheet + image right',{body:specSheet, img:'right'})],
    p4:[V('v1','Module plates',{body:moduleGrid}), V('n2','Exploded layers',{body:slabStack}), V('n3','Data sheet',{body:specSheet}), V('n4','Target + list',{body:'xTarget'}), V('n5','Isometric steps',{body:'xStairs'}), V('v2','Layers + image right',{body:slabStack, img:'right'}), V('v3','Image left + plates',{body:moduleGrid, img:'left'})],
    p56:[V('v1','Exploded layers',{body:slabStack}), V('n2','Module plates',{body:moduleGrid}), V('n3','Data sheet',{body:specSheet}), V('n4','Target + list',{body:'xTarget'}), V('n5','Isometric steps',{body:'xStairs'}), V('v2','Data sheet + image right',{body:specSheet, img:'right'})],
    p78:[V('v1','Module plates',{body:moduleGrid}), V('n2','Data sheet',{body:specSheet}), V('n3','Exploded layers',{body:slabStack}), V('v2','Data sheet + image right',{body:specSheet, img:'right'})],
    stats:[V('v1','Isometric prisms',{body:prismStats}), V('n2','Dimensioned figures',{body:dimStats}), V('n3','Stat cards',{body:'statCards', icons:true}), V('v2','Image left + figures',{body:dimStats, img:'left'}), V('v3','Prisms + image right',{body:prismStats, img:'right'})],
    timeline:[V('v1','Rising tile rail',{body:isoRail}), V('n2','Staggered bars',{body:isoGantt}), V('n3','Isometric steps',{body:'xStairs'}), V('v2','Bars + image right',{body:isoGantt, img:'right'})],
    quote:[V('v1','Target quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

