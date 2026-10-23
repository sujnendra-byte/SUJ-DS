
/* Template 18 — Lumen Gradient : light grey, lime → teal → blue → indigo gradient system, diamonds and rising paths */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var SER = ['#8FCB3A', '#2DBE8C', '#1FA6C9', '#3B7BE0', '#6A5AE0', '#9B6BE8'];
var P0 = {bg:'#F2F3F6', text:'#1B2033', title:'#1B2033', muted:'#5B6275', accent:'#3B7BE0', accent2:'#2DBE8C', card:'#FFFFFF', line:'#DDE0E7', onAccent:'#FFFFFF'};
var GR = {ang:90, stops:[[0, '#8FCB3A'], [35, '#2DBE8C'], [65, '#1FA6C9'], [100, '#6A5AE0']]};
var S = {
  margin:64, series:SER,
  header:{kind:'center', tMax:38, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'outline', r:16, fill:'#FFFFFF', line:'#DDE0E7', lw:1.5, hl:{kind:'grad', grad:{ang:135, stops:[[0, '#2DBE8C'], [100, '#3B7BE0']]}, dark:true}},
  icon:{kind:'circle', bg:'#3B7BE0', fg:'#FFFFFF', grad:{ang:135, stops:[[0, '#2DBE8C'], [100, '#3B7BE0']]}},
  num:{kind:'circle', bg:'#3B7BE0', fg:'#FFFFFF'},
  conc:{kind:'band', grad:GR, r:30, label:'Key result'},
  footer:{brand:false, page:true, color:'#9AA0AE'}, xRows:'#FFFFFF', hubFill:'#E6E9EF', road:'#1B2033', flowBar:'#E1E4EB',
  imgFramed:true, imgR:20
};
function bg(C, kind){
  C.bg({fill:'#F2F3F6'});
  C.rect(0, H - 8, W, 8, {grad:GR});
  if (kind !== 'cover' && kind !== 'closing') { D.glow(C, W - 60, 40, 220, 160, '#2DBE8C', 0.14); D.glow(C, 60, H - 40, 220, 160, '#6A5AE0', 0.1); }
}
function ribbon(C, x, y, w, h){
  for (var k = 0; k < 5; k++) K.poly(C, [[x + k * w * 0.2, y + h], [x + k * w * 0.2 + w * 0.12, y + h - (k + 1) * h * 0.18], [x + (k + 1) * w * 0.2 + w * 0.12, y + h - (k + 1) * h * 0.18], [x + (k + 1) * w * 0.2, y + h]], {fill:SER[k]});
  for (var j = 0; j < 5; j++) { var cx = x + j * w * 0.2 + w * 0.16, cy = y + h - (j + 1) * h * 0.18 - 44; C.shape('diamond', cx - 22, cy - 22, 44, 44, {fill:'#FFFFFF', line:SER[j], lw:3}); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) { D.imgBox(C, W - 560, 40, 520, H - 88, {r:26}); C.rect(W - 560, H - 60, 520, 12, {grad:GR, r:6}); }
  else ribbon(C, W * 0.5, 180, W * 0.46, 420);
  var x = 64, w = W * 0.5 - 100;
  if (f.company) K.pill(C, x, 90, Math.min(300, f.company.length * 10 + 50), 32, f.company, {fill:'#FFFFFF', line:'#DDE0E7', color:'#1B2033', size:12, wt:700, caps:true, ls:0.1, ed:{f:'tf-company'}});
  C.text(x, 150, w, 280, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  C.rect(x, 452, 140, 8, {grad:GR, r:4});
  if (C.s.subtitle) C.text(x, 480, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 610, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 480, H - 88, {r:26}); else ribbon(C, 40, 200, W * 0.42, 400);
  var x = o.img ? 580 : W * 0.5 + 20, w = W - x - 64;
  C.text(x, 170, w, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  C.rect(x, 380, 140, 8, {grad:GR, r:4});
  if (C.s.subtitle) C.text(x, 404, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 490, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#2DBE8C'}); return; }
  C.rect(W / 2 - 110, 120, 220, 8, {grad:GR, r:4});
  D.B.quote(C, {x:200, y:150, w:W - 400, h:H - 280}, {qc:'#2DBE8C'});
}

/* ---------- signature helpers ---------- */
var INK = '#1B2033', GS = [[0, '#8FCB3A'], [35, '#2DBE8C'], [65, '#1FA6C9'], [100, '#6A5AE0']];
function at(t){ t = Math.max(0, Math.min(1, t)) * 100; for (var i = 1; i < GS.length; i++) if (t <= GS[i][0]) { var a = GS[i - 1], b = GS[i]; return u.mix(a[1], b[1], (t - a[0]) / (b[0] - a[0])); } return GS[GS.length - 1][1]; }
function deep(t){ return u.mix(at(t), '#101633', 0.34); }
function tn(k, n){ return n <= 1 ? 0.5 : k / (n - 1); }
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function pct(v, k, n){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : 50 + 45 * tn(k, n); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + descH(C, t, w, F); }
function hdc(C, x, y, w, h, t, F, o){ var bh = blockH(C, t, w, F), yy = y + Math.max(0, (h - bh) / 2); K.hd(C, x, yy, w, h - (yy - y), t, F, o); }
function sdiam(C, cx, cy, s, c, o){
  o = o || {};
  if (o.glow !== false) D.glow(C, cx, cy, s * 1.1, s * 1.1, c, o.gop || 0.35);
  C.shape('diamond', cx - s / 2, cy - s / 2, s, s, {fill:o.fill || '#FFFFFF', line:c, lw:o.lw || 3});
  if (o.label != null) C.text(cx - s / 2, cy - s / 2, s, s, o.label, {font:'head', size:o.size || Math.round(s * 0.3), wt:800, color:o.tc || INK, align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1});
}

/* 1. rising light beams on a gradient baseline, diamond lamp on each */
function lightBeams(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, base = Z.y + Z.h * (n <= 3 ? 0.46 : 0.5), bw = Math.min(cw * 0.34, 84), ds = Math.min(52, cw * 0.36);
  var F = K.fitHD(C, it, cw - 22, 52, Z.y + Z.h - base - 26, {hMax:21, dMax:17, hMin:13, dMin:11});
  C.rect(Z.x, base - 2, Z.w, 4, {grad:GR, r:2});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = at(tn(k, n)), h = (base - Z.y - ds / 2 - 8) * (0.42 + 0.58 * tn(k, n));
    C.rect(cx - bw / 2, base - h, bw, h, {grad:{ang:0, stops:[[0, c, 0.85], [100, c, 0.04]]}, r:6});
    D.glow(C, cx, base, bw * 1.1, 16, c, 0.6);
    sdiam(C, cx, base - h, ds, c, {label:u.pad2(k + 1)});
    K.hd(C, Z.x + cw * k + 11, base + 22, cw - 22, Z.y + Z.h - base - 22, t, F, {align:'c', tc:INK});
  });
}

/* 2. tiles cut from one continuous gradient */
function spectrumTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : Math.ceil(n / 2))), rows = Math.ceil(n / cols);
  var g = 14, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = cw > 220 ? 24 : 18, top = rows > 1 ? 50 : 70;
  var H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, cw - pad * 2, 56, ch - top - pad, {hMax:21, dMax:15.5, hMin:13, dMin:11}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - top - pad, {font:'head', max:24, min:13, maxLines:2});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inLast = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + g) + (cols - inLast) * (cw + g) / 2, y = Z.y + r * (ch + g), t0 = k / n, t1 = (k + 1) / n;
    C.rect(x, y, cw, ch, {grad:{ang:120, stops:[[0, deep(t0)], [100, deep(t1)]]}, r:18});
    C.shape('diamond', x + cw - pad - 30, y + pad - 2, 30, 30, {line:'#FFFFFF', lw:2, lop:0.55});
    C.shape('diamond', x + cw - pad - 21, y + pad + 7, 12, 12, {fill:'#FFFFFF', op:0.85});
    C.text(x + pad, y + pad - 6, 120, 60, u.pad2(k + 1), {font:'head', size:rows > 1 ? 26 : 48, wt:800, color:'#FFFFFF', op:0.92, lh:1, caps:false, ls:0});
    if (H2) { var bh = blockH(C, t, cw - pad * 2, F); K.hd(C, x + pad, Math.max(y + top, y + ch - pad - bh - 4), cw - pad * 2, bh + 6, t, F, {tc:'#FFFFFF', mc:'#E6EEF6'}); }
    else C.text(x + pad, y + top, cw - pad * 2, ch - top - pad, t.d, {font:'head', size:ls, color:'#FFFFFF', lh:1.15, va:'b', ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
  });
}

/* 3. diamond of four diamonds with text in the four corners */
function diamondCluster(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4) return lightBeams(C, Z, o); var P = C.P;
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, s = Math.min(Z.h * 0.24, Z.w * 0.1, 116), a = s * 0.94;
  var pos = [[0, -s], [s, 0], [0, s], [-s, 0]], box = [[0, 0], [1, 0], [1, 1], [0, 1]];
  var colw = cx - Z.x - s - a - 44, bh = Z.h / 2 - 10;
  D.glow(C, cx, cy, s * 2.4, s * 2.2, '#2DBE8C', 0.16);
  var F = K.fitHD(C, it, colw, 56, bh - 30, {hMax:21, dMax:16, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var px = cx + pos[k][0], py = cy + pos[k][1], c = deep(k / 3);
    C.shape('diamond', px - a, py - a, a * 2, a * 2, {grad:{ang:135, stops:[[0, at(k / 4)], [100, c]]}});
    C.text(px - a / 2, py - a / 2, a, a, u.pad2(k + 1), {font:'head', size:Math.round(a * 0.42), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    var right = box[k][0] === 1, bx = right ? Z.x + Z.w - colw : Z.x, by = Z.y + box[k][1] * (bh + 20);
    var ty = by + Math.max(0, (bh - blockH(C, t, colw, F)) / 2), ly = ty + Math.min(F.hh || 20, 26) / 2;
    var ex = k === 0 ? px + (right ? a * 0.5 : -a * 0.5) : (k === 2 ? px + a * 0.5 : px + (right ? a : -a)), ey = k === 0 ? py - a * 0.5 : (k === 2 ? py + a * 0.5 : py);
    var tx = right ? bx - 16 : bx + colw + 16;
    C.path([['M', ex, ey], ['L', ex + (tx - ex) * 0.4, ly], ['L', tx, ly]], {stroke:at(k / 3), lw:1.6});
    C.ell(tx - 4, ly - 4, 8, 8, {fill:at(k / 3)});
    K.hd(C, bx, ty, colw, bh - (ty - by), t, F, {align:right ? 'l' : 'r', tc:INK});
  });
}

/* 4. equaliser level meters for stats */
function levelStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, iw = cw - 30, seg = 9;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, 70, {font:'head', max:n <= 2 ? 64 : 52, min:24, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 48, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 40, {font:'body', max:14, min:11, maxLines:2});
  var anyD = st.some(function(t){ return t.d; }), bot = ls * 1.2 * 2 + (anyD ? ds * 1.36 * 2 + 8 : 0) + 18;
  var mTop = Z.y + vs * 1.1 + 16, mH = Z.y + Z.h - bot - mTop, sh = (mH - (seg - 1) * 6) / seg, mw = Math.min(iw * 0.72, 150);
  st.forEach(function(t, k){
    var x = Z.x + cw * k, cx = x + cw / 2, lit = Math.max(1, Math.round(seg * pct(t.v, k, n) / 100));
    C.text(x + 15, Z.y, iw, vs * 1.1, t.v, {font:'head', size:vs, color:INK, align:'c', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    D.glow(C, cx, mTop + mH - (mH * lit / seg) / 2, mw * 0.8, mH * lit / seg * 0.6 + 10, at(tn(k, n)), 0.22);
    for (var j = 0; j < seg; j++) {
      var yy = mTop + mH - (j + 1) * sh - j * 6, on = j < lit;
      C.rect(cx - mw / 2, yy, mw, sh, {fill:on ? at(j / (seg - 1)) : '#E3E6EE', r:Math.min(8, sh / 2)});
    }
    var ly = Z.y + Z.h - bot + 14;
    C.text(x + 15, ly, iw, ls * 2.45, t.l, {font:'head', size:ls, color:INK, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x + 15, ly + ls * 2.45 + 4, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 5. numbers floating in soft halos */
function haloStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var narrow = Z.w < 820 && n > 2, cols = narrow ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, rh = Z.h / rows, iw = cw - 30;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, rh * 0.36, {font:'head', max:rows > 1 ? 50 : (n <= 2 ? 88 : 66), min:24, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 48, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 44, {font:'body', max:15, min:11, maxLines:2});
  var blk = 30 + vs * 1.08 + 22 + ls * 2.45 + (st.some(function(t){ return t.d; }) ? ds * 2.8 + 4 : 0);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * cw, cx = x + cw / 2, y = Z.y + r * rh + Math.max(0, (rh - blk) / 2), c = at(tn(k, n));
    D.glow(C, cx, y + 30 + vs * 0.55, Math.min(cw * 0.5, 170), vs * 1.3 + 20, c, 0.3);
    C.shape('diamond', cx - 9, y, 18, 18, {fill:c});
    C.text(x + 15, y + 30, iw, vs * 1.08, t.v, {font:'head', size:vs, color:INK, align:'c', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(cx - 36, y + 30 + vs * 1.08 + 8, 72, 5, {grad:{ang:90, stops:[[0, c], [100, at(Math.min(1, tn(k, n) + 0.3))]]}, r:3});
    C.text(x + 15, y + 30 + vs * 1.08 + 22, iw, ls * 2.45, t.l, {font:'head', size:ls, color:INK, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x + 15, y + 30 + vs * 1.08 + 22 + ls * 2.45, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 6. gradient spectrum line with glowing diamond stops, text alternating */
function spectrumLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, mid = Z.y + Z.h / 2, s = Math.min(58, cw * 0.4);
  C.rect(Z.x, mid - 5, Z.w, 10, {grad:GR, r:5});
  var F = K.fitHD(C, it, cw * (n > 3 ? 1.5 : 1) - 24, 48, Z.h / 2 - s / 2 - 34, {hMax:21, dMax:17, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = at(tn(k, n)), up = k % 2 === 0, tw = Math.min(cw * (n > 3 ? 1.5 : 1) - 24, Z.w - 8), tx = Math.max(Z.x, Math.min(Z.x + Z.w - tw, cx - tw / 2));
    sdiam(C, cx, mid, s, c, {label:u.pad2(k + 1), gop:0.45});
    var bh = blockH(C, t, tw, F);
    C.line(cx, up ? mid - s / 2 - 4 : mid + s / 2 + 4, cx, up ? mid - s / 2 - 18 : mid + s / 2 + 18, {color:c, lw:2});
    K.hd(C, tx, up ? mid - s / 2 - 24 - bh : mid + s / 2 + 24, tw, bh + 4, t, F, {align:'c', tc:INK});
  });
}

/* 7. lamp rows: glowing diamond lamps getting brighter down the list */
function lampRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = n > 5 && Z.w > 800 ? 2 : 1, per = Math.ceil(n / cols), gx = 48, cw = (Z.w - gx * (cols - 1)) / cols, gy = 10, rh = (Z.h - gy * (per - 1)) / per;
  var ls = Math.min(56, rh), tw = cw - ls - 30, H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, tw, rh * 0.45, rh - 8, {hMax:20, dMax:15.5, hMin:13, dMin:11, hLines:1}) : null;
  var lsz = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 6, {font:'head', max:n <= 4 ? 28 : 22, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * (rh + gy), cy = y + rh / 2, col = at(tn(k, n));
    D.glow(C, x + ls / 2, cy, ls * 0.8, ls * 0.8, col, 0.15 + 0.4 * tn(k, n));
    C.shape('diamond', x + ls * 0.2, cy - ls * 0.3, ls * 0.6, ls * 0.6, {fill:col});
    C.text(x + ls * 0.2, cy - ls * 0.3, ls * 0.6, ls * 0.6, String(k + 1), {font:'head', size:Math.max(11, Math.round(ls * 0.22)), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (H2) hdc(C, x + ls + 22, y, tw, rh, t, F, {tc:INK});
    else C.text(x + ls + 22, y, tw, rh, t.d, {font:'head', size:lsz, color:INK, va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    if (r < per - 1 && k < n - 1) C.rect(x + ls + 22, y + rh + gy / 2, tw, 1.5, {grad:{ang:90, stops:[[0, col, 0.9], [100, col, 0]]}});
  });
}

/* 8. before / after: outlined card, gradient card, diamond hinge */
function shiftDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var g = 60, cw = (Z.w - g) / 2, pad = cw > 400 ? 40 : 26, iw = cw - pad * 2;
  var F = K.fitHD(C, it, iw, 80, Z.h - pad * 2 - 56, {hMax:26, dMax:18, hMin:14, dMin:11.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), lit = k === 1;
    if (lit) { D.glow(C, x + cw / 2, Z.y + Z.h / 2, cw * 0.62, Z.h * 0.62, '#2DBE8C', 0.3); C.rect(x, Z.y, cw, Z.h, {grad:{ang:135, stops:[[0, deep(0.15)], [100, deep(0.95)]]}, r:22}); }
    else C.rect(x, Z.y, cw, Z.h, {fill:'#FFFFFF', line:'#DDE0E7', lw:1.5, r:22});
    K.pill(C, x + pad, Z.y + pad, 64, 30, u.pad2(k + 1), lit ? {fill:'#FFFFFF', color:INK, size:13, wt:800} : {fill:'#EEF0F4', color:INK, size:13, wt:800});
    hdc(C, x + pad, Z.y + pad + 50, iw, Z.h - pad * 2 - 50, t, F, {tc:lit ? '#FFFFFF' : INK, mc:lit ? '#E4ECF4' : P.muted});
  });
  if (n > 1) {
    var cx = Z.x + cw + g / 2, cy = Z.y + Z.h / 2, s = 92;
    C.shape('diamond', cx - s / 2, cy - s / 2, s, s, {fill:'#F2F3F6'});
    C.shape('diamond', cx - s / 2 + 10, cy - s / 2 + 10, s - 20, s - 20, {grad:GR});
    K.poly(C, [[cx - 9, cy - 13], [cx + 11, cy], [cx - 9, cy + 13]], {fill:'#FFFFFF'});
  }
}

/* 9. lit statement */
function glowStatement(C, Z, o){
  var P = C.P, t = C.it[0], dx = Z.x + Z.w - Math.min(260, Z.w * 0.24);
  D.glow(C, dx + 90, Z.y + Z.h / 2, 220, 200, '#2DBE8C', 0.25);
  [[0, 150, 0.9], [60, 90, 0.6], [-40, 60, 0.4]].forEach(function(q, j){ C.shape('diamond', dx + 90 + q[0] - q[1] / 2, Z.y + Z.h / 2 - q[0] * 0.9 - q[1] / 2, q[1], q[1], {line:at(j / 2), lw:3, lop:q[2]}); });
  C.shape('diamond', dx + 60, Z.y + Z.h / 2 - 30, 60, 60, {grad:GR});
  C.rect(Z.x, Z.y + 10, 8, Z.h - 20, {grad:{ang:180, stops:GS}, r:4});
  var x = Z.x + 44, w = dx - x - 40;
  if (!t) { C.text(x, Z.y, w, Z.h, C.s.subtitle || '', {font:'head', fit:{max:36, min:18}, color:INK, va:'m', lh:1.3, ed:{f:'subtitle'}}); return; }
  if (t.h) K.pill(C, x, Z.y + 20, Math.min(w, t.h.length * 11 + 50), 34, t.h, {fill:'#FFFFFF', line:'#DDE0E7', color:INK, size:13, wt:800, caps:true, ls:0.1, ed:{f:'x-part', i:t.i, p:0}});
  C.text(x, Z.y + (t.h ? 74 : 20), w, Z.h - (t.h ? 94 : 40), t.d, {font:'head', fit:{max:38, min:18}, color:INK, va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'lumen', name:'Lumen Gradient', tag:'Lime-to-indigo · frameworks', desc:'Light grey pages lit by a lime-to-indigo gradient, diamond milestones, rising paths and badge cards for frameworks and funnels.',
  fonts:{head:'Manrope', body:'Manrope', headW:800, bodyW:500, headLh:1.06, gf:'Manrope:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Gradient steps',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Gradient steps',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Lamp rows',{body:lampRows}), V('n2','Gradient tiles',{body:spectrumTiles}), V('v2','Lamps + image right',{body:lampRows, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Lit statement',{body:glowStatement}), V('n2','Statement card',{body:'text', box:true}), V('v2','Lit statement + image right',{body:glowStatement, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Before / after',{body:shiftDuo}), V('n2','Light beams',{body:lightBeams}), V('n3','Gradient tiles',{body:spectrumTiles}), V('v2','Image left + lamps',{body:lampRows, img:'left'}), V('v3','Tiles + image right',{body:spectrumTiles, img:'right'})],
    p3:[V('v1','Light beams',{body:lightBeams}), V('n2','Gradient tiles',{body:spectrumTiles}), V('n3','Spectrum line',{body:spectrumLine}), V('n4','Rising path',{body:'xRise'}), V('n5','Lamp rows',{body:lampRows}), V('v2','Image left + lamps',{body:lampRows, img:'left'}), V('v3','Beams + image right',{body:lightBeams, img:'right'})],
    p4:[V('v1','Diamond cluster',{body:diamondCluster}), V('n2','Light beams',{body:lightBeams}), V('n3','Gradient tiles',{body:spectrumTiles}), V('n4','Spectrum line',{body:spectrumLine}), V('n5','Rising path',{body:'xRise'}), V('v2','Lamps + image right',{body:lampRows, img:'right'}), V('v3','Image left + tiles',{body:spectrumTiles, img:'left'})],
    p56:[V('v1','Gradient tiles',{body:spectrumTiles}), V('n2','Light beams',{body:lightBeams}), V('n3','Lamp rows',{body:lampRows}), V('n4','Spectrum line',{body:spectrumLine}), V('n5','Card grid',{body:'grid'}), V('v2','Lamps + image right',{body:lampRows, img:'right'})],
    p78:[V('v1','Gradient tiles',{body:spectrumTiles}), V('n2','Lamp rows',{body:lampRows}), V('n3','Card grid',{body:'grid'}), V('v2','Lamps + image right',{body:lampRows, img:'right'})],
    stats:[V('v1','Level meters',{body:levelStats}), V('n2','Halo numbers',{body:haloStats}), V('n3','Progress rings',{body:'statRings'}), V('v2','Image left + halos',{body:haloStats, img:'left'}), V('v3','Meters + image right',{body:levelStats, img:'right'})],
    timeline:[V('v1','Spectrum line',{body:spectrumLine}), V('n2','Light beams',{body:lightBeams}), V('n3','Rising path',{body:'xRise'}), V('v2','Beams + image right',{body:lightBeams, img:'right'})],
    quote:[V('v1','Gradient quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

