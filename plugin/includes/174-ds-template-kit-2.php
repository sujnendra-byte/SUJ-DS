<?php
/**
 * Code Snippets export — DS Template Kit 2 [CLONE] [Step pills: 8 points]
 *
 * Snippet ID : 955
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-10-02 01:02:59
 * Load order : 169 of 191
 */
/**
 * DS Template Kit 2
 * Run: Everywhere
 *
 * Shared drawing helpers and extra layouts for DS Templates 13-29.
 * Needs the "DS Template Engine (T00)" snippet. Changes nothing in the
 * engine or in templates 01-12. Undo: deactivate (templates 13-29 then hide).
 */
if ( ! defined('ABSPATH') ) exit;
add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-kit2">
/* ============================================================
   DS Template Kit 2 — shared infographic layouts + decorations for
   templates 13-29. Adds new body layouts to DSTE.B (all prefixed x…)
   and drawing helpers on DSTE.K2. Needs the DS Template Engine (T00).
   Nothing in the engine or older templates is changed.
   ============================================================ */
(function(){
var D = window.DSTE; if (!D || !D.B || D.K2) return;
var W = D.W, H = D.H, u = D.u, B = D.B, mix = u.mix, lum = u.lum, pad2 = u.pad2, clamp = u.clamp;
var K = D.K2 = {};

/* ---------- colour + text helpers ---------- */
function col(C, k){ var s = C.S.series || [C.P.accent, C.P.accent2 || C.P.accent]; return s[((k % s.length) + s.length) % s.length]; }
function onC(c){ return lum(c) > 0.62 ? '#15171C' : '#FFFFFF'; }
K.col = col; K.on = onC;
function heads(it){ return it.map(function(t){ return t.h || ''; }); }
function descs(it){ return it.map(function(t){ return t.d || ''; }); }
/* one common heading + description size for a group of equal boxes */
function fitHD(C, it, w, hBox, dBox, o){
  o = o || {};
  var n = it.length, bst = o.noBoost ? 1 : (n <= 2 ? 1.32 : n === 3 ? 1.22 : n === 4 ? 1.1 : 1);   /* fewer items → larger text */
  o.hMax = Math.round((o.hMax || 20) * bst); o.dMax = Math.round((o.dMax || 15) * bst * 2) / 2;
  var hs = C.groupFit(heads(it), w, hBox, {font:'head', max:o.hMax || 20, min:o.hMin || 11, maxLines:o.hLines || 2});
  var hh = 0; it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, w, {font:'head', max:hs, min:hs, maxLines:o.hLines || 2}).h); });
  var ds = C.groupFit(descs(it), w, Math.max(20, dBox - (hh ? hh + 6 : 0)), {font:'body', max:o.dMax || 15, min:o.dMin || 9.5, lh:1.36});
  return {hs:hs, hh:hh, ds:ds};
}
K.fitHD = fitHD;
/* heading + description block; returns bottom y */
function hd(C, x, y, w, h, t, F, o){
  o = o || {}; var P = C.P, al = o.align || 'l';
  var hh = t.h ? Math.min(F.hh || F.hs * 1.3, h) : 0;
  if (t.h) C.text(x, y, w, hh + 2, t.h, {font:'head', size:F.hs, color:o.tc || P.text, align:al, lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:o.hLines || 2});
  var dy = y + (hh ? hh + 6 : 0), dh = Math.max(14, h - (dy - y));
  C.text(x, dy, w, dh, t.d, {font:'body', size:F.ds, color:o.mc || P.muted, align:al, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  return dy + dh;
}
K.hd = hd;
function badge(C, x, y, d, n, fill, o){
  o = o || {};
  C.ell(x, y, d, d, {fill:fill, line:o.line, lw:o.lw});
  C.text(x, y, d, d, o.label != null ? o.label : pad2(n), {font:'head', size:Math.round(d * 0.4), wt:800, color:o.fg || onC(fill), align:'c', va:'m', lh:1, caps:false, ls:0});
}
K.badge = badge;
function icon(C, x, y, sz, t, color){ C.icon(x, y, sz, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), color); }
K.icon = icon;

/* ---------- geometry ---------- */
var RAD = Math.PI / 180;
function pol(cx, cy, r, a){ return [cx + r * Math.cos(a * RAD), cy + r * Math.sin(a * RAD)]; }
K.pol = pol;
function arcCmds(cx, cy, r, a0, a1, first){
  var n = Math.max(1, Math.ceil(Math.abs(a1 - a0) / 45)), st = (a1 - a0) / n, k = 4 / 3 * Math.tan(st * RAD / 4), d = [];
  var p0 = pol(cx, cy, r, a0); d.push([first || 'M', p0[0], p0[1]]);
  for (var i = 0; i < n; i++) {
    var s0 = (a0 + st * i) * RAD, s1 = (a0 + st * (i + 1)) * RAD;
    var x0 = cx + r * Math.cos(s0), y0 = cy + r * Math.sin(s0), x3 = cx + r * Math.cos(s1), y3 = cy + r * Math.sin(s1);
    d.push(['C', x0 - k * r * Math.sin(s0), y0 + k * r * Math.cos(s0), x3 + k * r * Math.sin(s1), y3 - k * r * Math.cos(s1), x3, y3]);
  }
  return d;
}
K.arcCmds = arcCmds;
/* ring segment (annulus slice) */
K.seg = function(C, cx, cy, r0, r1, a0, a1, o){
  var d = arcCmds(cx, cy, r1, a0, a1).concat(arcCmds(cx, cy, r0, a1, a0, 'L')); d.push(['Z']);
  return C.path(d, o);
};
K.poly = function(C, pts, o){ var d = pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; }); d.push(['Z']); return C.path(d, o); };
/* smooth curve through points (Catmull-Rom → cubic) */
K.curve = function(C, pts, o){
  var d = [['M', pts[0][0], pts[0][1]]];
  for (var i = 0; i < pts.length - 1; i++) {
    var p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2;
    d.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]);
  }
  return C.path(d, o);
};

/* ---------- decorations ---------- */
K.grid = function(C, step, color, op, x, y, w, h){
  x = x || 0; y = y || 0; w = w || W; h = h || H;
  for (var gx = x + step; gx < x + w; gx += step) C.rect(gx, y, 1, h, {fill:color, op:op});
  for (var gy = y + step; gy < y + h; gy += step) C.rect(x, gy, w, 1, {fill:color, op:op});
};
K.dots = function(C, x, y, cols, rows, gap, r, color, op){
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) C.ell(x + i * gap - r, y + j * gap - r, r * 2, r * 2, {fill:color, op:op});
};
K.halftone = function(C, x, y, cols, rows, gap, rMax, color, op, dir){
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) {
    var t = dir === 'x' ? 1 - i / cols : 1 - (i + j) / (cols + rows);
    var r = rMax * Math.max(0.12, t); C.ell(x + i * gap - r, y + j * gap - r, r * 2, r * 2, {fill:color, op:op});
  }
};
K.starburst = function(C, cx, cy, r, pts, fill, o){
  o = o || {}; var p = [], n = pts || 12;
  for (var k = 0; k < n * 2; k++) { var rr = k % 2 ? r * (o.inner || 0.62) : r, a = -90 + k * 180 / n; p.push(pol(cx, cy, rr, a)); }
  return K.poly(C, p, {fill:fill, stroke:o.line, lw:o.lw, op:o.op});
};
K.xmark = function(C, cx, cy, s, fill, o){
  o = o || {}; var t = s * 0.28, h = s / 2;
  var p = [[-h, -h + t], [-h + t, -h], [0, -t], [h - t, -h], [h, -h + t], [t, 0], [h, h - t], [h - t, h], [0, t], [-h + t, h], [-h, h - t], [-t, 0]];
  var r = (o.rot || 0) * RAD, cs = Math.cos(r), sn = Math.sin(r);
  return K.poly(C, p.map(function(q){ return [cx + q[0] * cs - q[1] * sn, cy + q[0] * sn + q[1] * cs]; }), {fill:fill, stroke:o.line, lw:o.lw});
};
K.target = function(C, cx, cy, r, c1, c2, n){
  n = n || 4;
  for (var k = 0; k < n; k++) { var rr = r * (1 - k / n); C.ell(cx - rr, cy - rr, rr * 2, rr * 2, {fill:k % 2 ? c2 : c1}); }
};
K.chev3 = function(C, x, y, s, color, o){
  o = o || {};
  for (var k = 0; k < 3; k++) {
    var xx = x + k * s * 0.9, pts = [[xx, y], [xx + s * 0.7, y + s / 2], [xx, y + s]];
    if (o.hollow && k === 1) K.poly(C, pts, {stroke:color, lw:2}); else K.poly(C, pts, {fill:color});
  }
};
K.pill = function(C, x, y, w, h, text, o){
  o = o || {};
  C.rect(x, y, w, h, {fill:o.fill, op:o.op, line:o.line, lw:o.lw || 1.2, r:h / 2});
  C.text(x, y, w, h, text, {font:o.font || 'body', size:o.size || Math.round(h * 0.42), wt:o.wt || 600, color:o.color || C.P.text, align:'c', va:'m', lh:1, caps:!!o.caps, ls:o.ls || 0, ed:o.ed || null, maxLines:1});
};
/* soft organic blob (for glass / neon looks) */
K.blob = function(C, cx, cy, r, color, op, seed){
  var p = [], n = 8, s = seed || 1;
  for (var k = 0; k < n; k++) { var a = k * 360 / n, rr = r * (0.78 + 0.22 * Math.sin(s * 2.3 + k * 1.7)); p.push(pol(cx, cy, rr, a)); }
  p.push(p[0], p[1], p[2]);
  var d = [['M', p[1][0], p[1][1]]];
  for (var i = 1; i < p.length - 2; i++) { var p0 = p[i - 1], p1 = p[i], p2 = p[i + 1], p3 = p[i + 2]; d.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]); }
  d.push(['Z']); return C.path(d, {fill:color, op:op});
};
/* rotated-square photo frame: image with the corners covered in the page colour, plus a white border */
K.diamondImg = function(C, cx, cy, r, bgc, o){
  o = o || {};
  D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {r:0});
  var e = r + 2;
  K.poly(C, [[cx - e, cy - e], [cx, cy - e], [cx - e, cy]], {fill:bgc}); K.poly(C, [[cx + e, cy - e], [cx + e, cy], [cx, cy - e]], {fill:bgc});
  K.poly(C, [[cx + e, cy + e], [cx, cy + e], [cx + e, cy]], {fill:bgc}); K.poly(C, [[cx - e, cy + e], [cx - e, cy], [cx, cy + e]], {fill:bgc});
  if (o.border) K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], {stroke:o.border, lw:o.bw || 6});
};
/* 4-point sparkle */
K.spark = function(C, cx, cy, r, color, op){ D.sparkle(C, cx, cy, r, color, op); };
/* torus / loop outline (drawn chrome loops) */
K.loop = function(C, cx, cy, rx, ry, rot, color, lw, op){
  var d = [], n = 48, r = rot * RAD;
  for (var k = 0; k <= n; k++) { var a = k / n * Math.PI * 2, x = rx * Math.cos(a), y = ry * Math.sin(a); d.push([k ? 'L' : 'M', cx + x * Math.cos(r) - y * Math.sin(r), cy + x * Math.sin(r) + y * Math.cos(r)]); }
  C.path(d, {stroke:color, lw:lw, lop:op});
};

/* ============================================================
   BODY LAYOUTS  B.x…  (C, Z, o)
   ============================================================ */

/* half-ring of coloured segments on the left, items on the right */
B.xArc = function(C, Z, o){
  var it = C.it.slice(0, 7), n = it.length; if (!n) return; var P = C.P;
  var R = Math.min(Z.h * 0.5, Z.w * 0.3), cx = Z.x + 6, cy = Z.y + Z.h / 2, r0 = R * 0.58;
  var gap = 1.6, step = 180 / n;
  it.forEach(function(t, k){
    var a0 = -90 + k * step + gap / 2, a1 = -90 + (k + 1) * step - gap / 2, c = col(C, k);
    K.seg(C, cx, cy, r0, R, a0, a1, {fill:c});
    var m = pol(cx, cy, (r0 + R) / 2, (a0 + a1) / 2), is = Math.min(30, (R - r0) * 0.5);
    icon(C, m[0] - is / 2, m[1] - is / 2, is, t, onC(c));
  });
  K.seg(C, cx, cy, 0, r0 * 0.8, -90, 90, {fill:C.S.hubFill || mix(P.bg, P.text, 0.06)});
  var lx = cx + R + 60, lw = Z.x + Z.w - lx, gy = 12, rh = (Z.h - gy * (n - 1)) / n, bd = Math.min(38, rh * 0.62);
  var F = fitHD(C, it, lw - bd - 22, rh * 0.45, rh - 4, {hMax:19, dMax:14.5, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k), mid = y + rh / 2;
    var a = -90 + (k + 0.5) * step, p = pol(cx, cy, R + 6, a);
    C.line(p[0], p[1], lx - 10, mid, {color:c, lw:1.5, op:0.7});
    C.ell(p[0] - 4, p[1] - 4, 8, 8, {fill:c});
    if (C.S.xRows) C.rect(lx, y, lw, rh, {fill:C.S.xRows, r:rh / 2 > 14 ? 12 : 6});
    badge(C, lx + 8, mid - bd / 2, bd, k + 1, c);
    hd(C, lx + bd + 22, y + Math.max(2, (rh - (F.hh + 6 + F.ds * 2.7)) / 2), lw - bd - 30, rh, t, F);
  });
};

/* winding road with pins; labels alternate above and below */
B.xRoad = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var road = C.S.road || '#2A2D34', mid = Z.y + Z.h * 0.52, A = Z.h * 0.16, pts = [], N = 60;
  for (var i = 0; i <= N; i++) { var t = i / N; pts.push([Z.x - 10 + t * (Z.w + 20), mid + A * Math.sin(t * Math.PI * (n <= 3 ? 2 : 3) + 0.4)]); }
  K.curve(C, pts, {stroke:road, lw:30});
  for (var j = 0; j < N; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:'#FFFFFF', lw:2.2, op:0.85});
  var cw = Z.w / n, F = fitHD(C, it, cw - 18, 40, Z.h * 0.26, {hMax:18, dMax:13.5});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), idx = Math.round((x - Z.x + 10) / (Z.w + 20) * N), p = pts[clamp(idx, 0, N)], c = col(C, k), up = k % 2 === 0, d = 50;
    C.ell(p[0] - d / 2 - 5, p[1] - d / 2 - 5, d + 10, d + 10, {fill:'#FFFFFF'});
    C.ell(p[0] - d / 2, p[1] - d / 2, d, d, {fill:c});
    icon(C, p[0] - 13, p[1] - 13, 26, t, onC(c));
    var ty = up ? Z.y : p[1] + d / 2 + 22, th = up ? p[1] - d / 2 - 22 - Z.y : Z.y + Z.h - ty;
    C.line(p[0], up ? p[1] - d / 2 - 5 : p[1] + d / 2 + 5, p[0], up ? p[1] - d / 2 - 16 : p[1] + d / 2 + 16, {color:c, lw:2});
    C.text(x - cw / 2 + 6, up ? ty + th - F.hh - F.ds * 4.2 - 30 : ty, cw - 12, 22, pad2(k + 1), {font:'head', size:15, wt:800, color:c, align:'c', lh:1, caps:false, ls:0});
    hd(C, x - cw / 2 + 9, up ? ty + th - F.hh - F.ds * 4.2 - 6 : ty + 24, cw - 18, F.hh + 6 + F.ds * 4.4, t, F, {align:'c'});
  });
};

/* isometric blocks rising left to right, text above each */
B.xStairs = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var gap = 14, cw = (Z.w - gap * (n - 1)) / n, depth = Math.min(28, cw * 0.18), baseY = Z.y + Z.h, minH = Z.h * 0.18, maxH = Z.h * 0.52;
  var F = fitHD(C, it, cw - 10, 44, Z.h - maxH - depth - 40, {hMax:18, dMax:13.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), h = minH + (maxH - minH) * (n === 1 ? 1 : k / (n - 1)), y = baseY - h, c = col(C, k), bw = cw - depth;
    C.rect(x, y, bw, h, {fill:c});
    K.poly(C, [[x, y], [x + depth, y - depth], [x + bw + depth, y - depth], [x + bw, y]], {fill:mix(c, '#FFFFFF', 0.35)});
    K.poly(C, [[x + bw, y], [x + bw + depth, y - depth], [x + bw + depth, baseY - depth], [x + bw, baseY]], {fill:mix(c, '#000000', 0.25)});
    C.text(x, y + 10, bw, 44, pad2(k + 1), {font:'head', size:30, wt:800, color:onC(c), align:'c', lh:1, caps:false, ls:0});
    icon(C, x + bw / 2 - 15, y + 58, 30, t, onC(c));
    var ty = y - depth - 14 - (F.hh + 6 + F.ds * 4.2);
    hd(C, x, Math.max(Z.y, ty), cw, F.hh + 6 + F.ds * 4.4, t, F, {align:'l'});
  });
};

/* funnel of stacked bands on the left, rows of text on the right */
B.xFunnel = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var fw = Math.min(Z.w * 0.42, 460), gy = 8, bh = (Z.h - gy * (n - 1)) / n, x0 = Z.x;
  var F = fitHD(C, it, Z.w - fw - 90, bh * 0.45, bh, {hMax:19, dMax:14.5, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (bh + gy), c = col(C, k), inset1 = fw * 0.36 * k / n, inset2 = fw * 0.36 * (k + 1) / n;
    K.poly(C, [[x0 + inset1, y], [x0 + fw - inset1, y], [x0 + fw - inset2, y + bh], [x0 + inset2, y + bh]], {fill:c});
    C.text(x0 + inset1, y, fw - inset1 * 2, bh, pad2(k + 1), {font:'head', size:Math.min(26, bh * 0.45), wt:800, color:onC(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    var tx = x0 + fw + 34;
    C.line(x0 + fw - inset1 - 4, y + bh / 2, tx - 8, y + bh / 2, {color:c, lw:1.5, dash:true, op:0.8});
    C.ell(tx - 10, y + bh / 2 - 5, 10, 10, {fill:c});
    hd(C, tx + 14, y + Math.max(0, (bh - (F.hh + 6 + F.ds * 2.6)) / 2), Z.x + Z.w - tx - 14, bh, t, F);
  });
};

/* chevron ribbon row with text + icon below each */
B.xChevrons = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var ov = 16, cw = (Z.w + ov * (n - 1)) / n, ch = 58;
  var F = fitHD(C, it, cw - 40, 1, Z.h - ch - 110, {dMax:14.5});
  var hs = C.groupFit(heads(it), cw - 56, ch - 12, {font:'head', max:17, min:10.5, maxLines:2});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw - ov), c = col(C, k);
    C.shape(k === 0 ? 'homePlate' : 'chevron', x, Z.y, cw, ch, {fill:c});
    C.text(x + (k ? 22 : 14), Z.y, cw - (k ? 44 : 34), ch, t.h || pad2(k + 1), {font:'head', size:hs, color:onC(c), align:'c', va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    var cx = x + cw / 2 - ov / 2;
    C.line(cx, Z.y + ch + 6, cx, Z.y + ch + 26, {color:c, lw:2, dash:true});
    C.ell(cx - 26, Z.y + ch + 28, 52, 52, {fill:mix(c, P.bg, 0.82), line:c, lw:1.5});
    icon(C, cx - 13, Z.y + ch + 41, 26, t, c);
    C.text(x + 14, Z.y + ch + 96, cw - 40, Z.h - ch - 96, t.d, {font:'body', size:F.ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
};

/* numbered step tabs: coloured label bar on the left, text on the right */
B.xTabs = function(C, Z, o){
  var it = C.it.slice(0, 7), n = it.length; if (!n) return; var P = C.P;
  var gy = 12, rh = (Z.h - gy * (n - 1)) / n, lw = Math.min(360, Z.w * 0.36);
  var hs = C.groupFit(heads(it), lw - 110, rh - 10, {font:'head', max:n <= 3 ? 22 : 18, min:11, maxLines:2});
  var ds = C.groupFit(descs(it), Z.w - lw - 110, rh - 6, {font:'body', max:n <= 3 ? 20 : (n <= 5 ? 17 : 15), min:9.5, lh:1.35});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k), sw = 74;
    C.shape('homePlate', Z.x, y, lw, rh, {fill:c});
    C.rect(Z.x, y, sw, rh, {fill:mix(c, '#000000', 0.28)});
    C.text(Z.x, y, sw, rh, (C.S.stepWord || 'Step') + ' ' + pad2(k + 1), {font:'body', size:Math.min(12, rh * 0.3), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1.1, caps:true, ls:0.04});
    if (t.h) C.text(Z.x + sw + 14, y, lw - sw - 40, rh, t.h, {font:'head', size:hs, color:onC(c), va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    var dx = Z.x + lw + 26;
    C.ell(dx, y + rh / 2 - 5, 10, 10, {fill:c});
    C.line(dx + 14, y + rh / 2, dx + 34, y + rh / 2, {color:c, lw:1.5, dash:true});
    icon(C, dx + 40, y + rh / 2 - 12, 24, t, c);
    C.text(dx + 76, y, Z.x + Z.w - dx - 76, rh, t.d, {font:'body', size:ds, color:P.muted, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
  });
};

/* big circle nodes joined by arrows, text below */
B.xFlow = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, d = Math.min(cw * 0.56, 128, Z.h * 0.36), cy = Z.y + d / 2 + 6;
  var F = fitHD(C, it, cw - 22, 44, Z.h - d - 90, {hMax:18, dMax:13.5});
  C.rect(Z.x, cy - 7, Z.w, 14, {fill:C.S.flowBar || mix(P.bg, P.text, 0.12), r:7});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = col(C, k);
    if (k < n - 1) C.shape('chevron', cx + d / 2 + 6, cy - 12, Math.max(16, cw - d - 12), 24, {fill:c, op:0.9});
    C.ell(cx - d / 2 - 6, cy - d / 2 - 6, d + 12, d + 12, {fill:'#FFFFFF', shadow:0.12});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    icon(C, cx - d * 0.22, cy - d * 0.22, d * 0.44, t, onC(c));
    badge(C, cx - 17, cy + d / 2 + 16, 34, k + 1, c);
    hd(C, cx - cw / 2 + 11, cy + d / 2 + 62, cw - 22, Z.y + Z.h - (cy + d / 2 + 62), t, F, {align:'c'});
  });
};

/* hexagon markers alternating up/down along a line */
B.xHex = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, hx = Math.min(124, cw * 0.66, Z.h * 0.34), mid = Z.y + Z.h / 2;
  C.rect(Z.x, mid - 1.5, Z.w, 3, {fill:mix(P.bg, P.text, 0.18)});
  var F = fitHD(C, it, cw - 16, 44, Z.h / 2 - 40, {hMax:19, dMax:15});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = col(C, k), up = k % 2 === 0, y = up ? mid - hx - 12 : mid + 12;
    C.shape('hexagon', cx - hx / 2 - 5, y - 5, hx + 10, hx * 0.88 + 10, {fill:'#FFFFFF', shadow:0.1});
    C.shape('hexagon', cx - hx / 2, y, hx, hx * 0.88, {fill:c});
    icon(C, cx - hx * 0.2, y + hx * 0.44 - hx * 0.2, hx * 0.4, t, onC(c));
    badge(C, cx - 13, mid - 13, 26, k + 1, c, {line:'#FFFFFF', lw:3});
    var ty = up ? mid + 26 : Z.y, th = up ? Z.y + Z.h - ty : y - 14 - Z.y;
    hd(C, cx - cw / 2 + 8, up ? ty : ty + Math.max(0, th - (F.hh + 6 + F.ds * 4.2)), cw - 16, Math.min(th, F.hh + 8 + F.ds * 4.6), t, F, {align:'c'});
  });
};

/* diamonds on a zig-zag line, text above / below */
B.xDiamonds = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, s = Math.min(70, cw * 0.46), mid = Z.y + Z.h / 2, pts = [];
  it.forEach(function(t, k){ pts.push([Z.x + cw * (k + 0.5), mid + (k % 2 ? 34 : -34)]); });
  C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; }), {stroke:mix(P.bg, P.text, 0.35), lw:2});
  var F = fitHD(C, it, cw - 14, 40, Z.h / 2 - s - 30, {hMax:17, dMax:13});
  it.forEach(function(t, k){
    var p = pts[k], c = col(C, k), up = k % 2 === 1;
    C.shape('diamond', p[0] - s / 2 - 6, p[1] - s / 2 - 6, s + 12, s + 12, {fill:'#FFFFFF', line:c, lw:2});
    C.shape('diamond', p[0] - s / 2, p[1] - s / 2, s, s, {fill:c});
    C.text(p[0] - s / 2, p[1] - s / 2, s, s, pad2(k + 1), {font:'head', size:Math.round(s * 0.3), wt:800, color:onC(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    var bh = F.hh + 8 + F.ds * 4.4;
    icon(C, p[0] - 13, up ? p[1] + s / 2 + 14 : p[1] - s / 2 - 40, 26, t, c);
    hd(C, p[0] - cw / 2 + 7, up ? p[1] + s / 2 + 46 : p[1] - s / 2 - 46 - bh, cw - 14, bh, t, F, {align:'c'});
  });
};

/* cards with an overlapping round badge on top */
B.xBadgeCards = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var gap = n > 4 ? 16 : 22, cw = (Z.w - gap * (n - 1)) / n, d = Math.min(76, cw * 0.46), top = Z.y + d / 2;
  var F = fitHD(C, it, cw - 30, 52, Z.h - d - 70, {hMax:20, dMax:16});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), c = col(C, k), cc = D.card(C, x, top, cw, Z.h - d / 2, {});
    C.rect(x, Z.y + Z.h - 8, cw, 8, {fill:c, r:3});
    C.ell(x + cw / 2 - d / 2 - 5, Z.y - 5, d + 10, d + 10, {fill:P.bg});
    C.ell(x + cw / 2 - d / 2, Z.y, d, d, {fill:c});
    icon(C, x + cw / 2 - d * 0.23, Z.y + d * 0.27, d * 0.46, t, onC(c));
    C.text(x, top + d / 2 + 10, cw, 22, (C.S.stepWord || 'Step') + ' ' + pad2(k + 1), {font:'body', size:12, wt:800, color:c, align:'c', caps:true, ls:0.1, lh:1});
    hd(C, x + 15, top + d / 2 + 40, cw - 30, Z.y + Z.h - (top + d / 2 + 40) - 14, t, F, {align:'c', tc:cc.tc, mc:cc.mc});
  });
};

/* two sides compared: first half left, second half right, VS disc between */
B.xVersus = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var L = it.slice(0, Math.ceil(n / 2)), R = it.slice(Math.ceil(n / 2)), cw = (Z.w - 150) / 2, rows = Math.max(L.length, R.length), gy = 12, rh = (Z.h - gy * (rows - 1)) / rows;
  var F = fitHD(C, it, cw - 70, rh * 0.45, rh - 8, {hMax:18, dMax:14, hLines:1});
  function side(list, x, k0, al){
    list.forEach(function(t, k){
      var y = Z.y + k * (rh + gy), c = col(C, al === 'r' ? 0 : 1);
      C.rect(x, y, cw, rh, {fill:C.S.xRows || mix(P.bg, P.text, 0.05), r:12});
      badge(C, al === 'r' ? x + cw - 46 : x + 12, y + rh / 2 - 17, 34, k0 + k + 1, c);
      hd(C, al === 'r' ? x + 16 : x + 58, y + Math.max(4, (rh - (F.hh + 6 + F.ds * 2.6)) / 2), cw - 74, rh - 6, t, F, {align:al});
    });
  }
  side(L, Z.x, 0, 'r'); side(R, Z.x + Z.w - cw, L.length, 'l');
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, d = 96;
  C.rect(cx - 1.5, Z.y, 3, Z.h, {fill:mix(P.bg, P.text, 0.2)});
  C.ell(cx - d / 2, cy - d / 2, d, d, {fill:P.accent, line:P.bg, lw:6});
  C.text(cx - d / 2, cy - d / 2, d, d, 'VS', {font:'head', size:28, wt:800, color:P.onAccent || '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0.04});
};

/* concentric target on the left, icon rows on the right */
B.xTarget = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var r = Math.min(Z.h * 0.46, Z.w * 0.2), cx = Z.x + r + 10, cy = Z.y + Z.h / 2;
  K.target(C, cx, cy, r, col(C, 0), mix(col(C, 0), '#FFFFFF', 0.82), 5);
  C.ell(cx - r * 0.16, cy - r * 0.16, r * 0.32, r * 0.32, {fill:col(C, 1)});
  C.line(cx + 4, cy - 4, cx + r * 1.15, cy - r * 0.95, {color:P.text, lw:4});
  K.poly(C, [[cx + r * 1.1, cy - r * 1.05], [cx + r * 1.32, cy - r * 1.02], [cx + r * 1.2, cy - r * 0.86]], {fill:col(C, 1)});
  var lx = cx + r + 70, lw = Z.x + Z.w - lx, gy = 12, rh = (Z.h - gy * (n - 1)) / n, ms = Math.min(50, rh - 10);
  var F = fitHD(C, it, lw - ms - 30, rh * 0.45, rh - 6, {hMax:19, dMax:14.5, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k);
    C.rect(lx, y, lw, rh, {fill:C.S.xRows || mix(P.bg, P.text, 0.05), r:12});
    C.rect(lx + 10, y + (rh - ms) / 2, ms, ms, {fill:c, r:12});
    icon(C, lx + 10 + ms * 0.22, y + (rh - ms) / 2 + ms * 0.22, ms * 0.56, t, onC(c));
    hd(C, lx + ms + 28, y + Math.max(4, (rh - (F.hh + 6 + F.ds * 2.6)) / 2), lw - ms - 40, rh - 6, t, F);
  });
};

/* ring of nodes around a centre title, text in columns left and right */
B.xCycle = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var R = Math.min(Z.h * 0.4, 170), cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, d = Math.min(62, R * 0.55);
  D.ring(C, cx, cy, R, {color:mix(P.bg, P.text, 0.25), lw:3});
  C.ell(cx - R * 0.58, cy - R * 0.58, R * 1.16, R * 1.16, {fill:P.accent});
  C.text(cx - R * 0.5, cy - R * 0.5, R, R, String(C.s.title || '').split(/\s+/).slice(0, 4).join(' '), {font:'head', fit:{max:20, min:11}, color:P.onAccent || '#FFFFFF', align:'c', va:'m', maxLines:3, lh:1.1});
  var left = Math.ceil(n / 2), sw = (Z.w - R * 2 - d - 80) / 2, rows = left, gy = 10, rh = (Z.h - gy * (rows - 1)) / rows;
  var F = fitHD(C, it, sw, rh * 0.42, rh, {hMax:18, dMax:13.5, hLines:1});
  it.forEach(function(t, k){
    var a = -90 + k * 360 / n, p = pol(cx, cy, R, a), c = col(C, k);
    C.ell(p[0] - d / 2 - 4, p[1] - d / 2 - 4, d + 8, d + 8, {fill:P.bg});
    C.ell(p[0] - d / 2, p[1] - d / 2, d, d, {fill:c});
    icon(C, p[0] - d * 0.24, p[1] - d * 0.24, d * 0.48, t, onC(c));
    var side = k < left ? 'r' : 'l', r = k < left ? k : k - left, y = Z.y + r * (rh + gy), x = side === 'r' ? Z.x + Z.w - sw : Z.x;
    C.text(side === 'r' ? x - 34 : x + sw + 8, y, 26, 22, pad2(k + 1), {font:'head', size:15, wt:800, color:c, align:'c', lh:1, caps:false, ls:0});
    hd(C, x, y, sw, rh, t, F, {align:side === 'r' ? 'l' : 'r'});
  });
};

/* rising bubbles on a diagonal (month-by-month plans) */
B.xRise = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var d = Math.min(62, Z.h / n - 6), stepX = (Z.w * 0.46) / Math.max(1, n - 1), stepY = (Z.h - d) / Math.max(1, n - 1);
  var x0 = Z.x + d / 2, y0 = Z.y + Z.h - d / 2;
  C.line(x0, y0, x0 + stepX * (n - 1) + 60, y0 - stepY * (n - 1) - 60, {color:mix(P.bg, P.text, 0.3), lw:2, dash:true});
  var F = fitHD(C, it, Z.w * 0.5 - d, 28, Math.max(24, stepY), {hMax:17, dMax:13.5, hLines:1});
  it.forEach(function(t, k){
    var cx = x0 + stepX * k, cy = y0 - stepY * k, c = col(C, k);
    C.ell(cx - d / 2 - 5, cy - d / 2 - 5, d + 10, d + 10, {fill:mix(c, P.bg, 0.75)});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    icon(C, cx - d * 0.24, cy - d * 0.24, d * 0.48, t, onC(c));
    K.pill(C, cx + d / 2 + 14, cy - 13, 74, 26, pad2(k + 1), {fill:c, color:onC(c), size:12, wt:800});
    hd(C, cx + d / 2 + 100, cy - Math.min(stepY, 64) / 2, Z.x + Z.w - (cx + d / 2 + 100), Math.max(40, Math.min(stepY + 10, 70)), t, F);
  });
};

/* horizontal pill phases on top, milestone columns below (testing timeline) */
B.xPhases = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, ph = 40;
  C.rect(Z.x, Z.y, Z.w, ph, {fill:mix(P.bg, P.text, 0.08), r:ph / 2});
  var F = fitHD(C, it, cw - 26, 44, Z.h - ph - 120, {hMax:17, dMax:13.5});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, c = col(C, k);
    C.shape(k === 0 ? 'homePlate' : 'chevron', x + (k ? -8 : 0), Z.y, cw + (k ? 8 : 0), ph, {fill:c});
    C.text(x + 12, Z.y, cw - 24, ph, (C.S.phaseWord || 'Phase') + ' ' + (k + 1), {font:'body', size:13, wt:800, color:onC(c), align:'c', va:'m', lh:1, maxLines:1});
    var cx = x + cw / 2;
    C.line(cx, Z.y + ph, cx, Z.y + ph + 34, {color:c, lw:2});
    C.ell(cx - 7, Z.y + ph + 32, 14, 14, {fill:c});
    var by = Z.y + ph + 58;
    D.card(C, x + 8, by, cw - 16, Z.y + Z.h - by, {});
    icon(C, cx - 14, by + 16, 28, t, c);
    hd(C, x + 13, by + 54, cw - 26, Z.y + Z.h - by - 62, t, F, {align:'c'});
  });
};

/* glass cards with a small glowing dot (dark glass templates) */
B.xGlass = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)), rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var F = fitHD(C, it, cw - 52, 60, ch - 76, {hMax:24, dMax:18});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy);
    C.rect(x, y, cw, ch, {fill:C.S.glassFill || '#FFFFFF', op:C.S.glassOp != null ? C.S.glassOp : 0.06, line:C.S.glassLine || '#FFFFFF', lop:0.35, lw:1.2, r:14});
    C.ell(x + cw - 30, y + 18, 11, 11, {fill:P.accent});
    D.glow(C, x + cw - 24.5, y + 23.5, 22, 22, P.accent, 0.35);
    C.text(x + 26, y + 20, cw - 70, 24, (C.S.cardWord || '') + (C.S.cardWord ? ' ' : '') + pad2(k + 1), {font:'body', size:13, wt:800, color:P.accent, caps:true, ls:0.12, lh:1});
    var bh = F.hh + 8 + C.measureText(t.d, cw - 52, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h;
    hd(C, x + 26, y + Math.max(56, (ch - bh) / 2 + 14), cw - 52, ch - 70, t, F);
  });
};

/* magazine: big photo + text columns (portfolio / editorial templates) */
B.xMagazine = function(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (!n) return; var P = C.P;
  var iw = Math.round(Z.w * (o.imgW || 0.46)), right = o.imgSide !== 'l';
  var ix = right ? Z.x + Z.w - iw : Z.x;
  D.imgBox(C, ix, Z.y, iw, Z.h, {r:C.S.imgR || 0});
  var tx = right ? Z.x : Z.x + iw + 40, tw = Z.w - iw - 40, gy = 18, rh = (Z.h - gy * (n - 1)) / n;
  var F = fitHD(C, it, tw, 48, rh - 10, {hMax:24, dMax:18});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy);
    if (k) C.rect(tx, y - gy / 2, tw, 1, {fill:P.line});
    hd(C, tx, y, tw, rh, t, F);
  });
};

/* stat bubbles: big coloured discs with the number inside, label below */
B.xStatDiscs = function(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, d = Math.min(cw * 0.7, Z.h * 0.58, 220);
  var vals = st.map(function(t){ return t.h || String(t.d).split(' ')[0]; }), labs = st.map(function(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); });
  var vs = C.groupFit(vals, d * 0.8, d * 0.4, {font:'head', max:56, min:20, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, Z.h - d - 30, {font:'body', max:18, min:11, lh:1.35});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = col(C, k), cy = Z.y + d / 2;
    C.ell(cx - d / 2 - 8, cy - d / 2 - 8, d + 16, d + 16, {fill:mix(c, P.bg, 0.78)});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    C.text(cx - d * 0.42, cy - d * 0.25, d * 0.84, d * 0.5, vals[k], {font:'head', size:vs, color:onC(c), align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + cw * k + 15, Z.y + d + 22, cw - 30, Z.h - d - 22, labs[k], {font:'body', size:ls, color:P.text, align:'c', lh:1.35, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
  });
};

/* vertical stepped pills (continuous improvement) */
B.xStack = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var gy = 10, rh = (Z.h - gy * (n - 1)) / n, indent = Math.min(40, Z.w * 0.04);
  var F = fitHD(C, it, Z.w - 260, rh * 0.45, rh - 4, {hMax:20, dMax:16, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k), x = Z.x + (k % 2) * indent, w = Z.w - indent;
    C.rect(x, y, w, rh, {fill:C.S.xRows || '#FFFFFF', line:c, lw:2, r:rh / 2 > 20 ? 14 : rh / 2, shadow:C.S.xShadow});
    C.rect(x, y, 110, rh, {fill:c, r:rh / 2 > 20 ? 14 : rh / 2});
    C.text(x, y, 110, rh, (C.S.stepWord || 'Step') + ' ' + pad2(k + 1), {font:'head', size:Math.min(18, rh * 0.36), wt:800, color:onC(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    icon(C, x + 128, y + rh / 2 - 13, 26, t, c);
    hd(C, x + 172, y + Math.max(2, (rh - (F.hh + 6 + F.ds * 2.6)) / 2), w - 190, rh - 4, t, F);
  });
};
})();

/* ---------- post-render helpers (used from T.decor) ---------- */
(function(){
var D = window.DSTE, K = D && D.K2; if (!K) return;
/* offset "pop" shadow behind the slide title (and cover title) */
K.popTitle = function(C, color, dx, dy, fields){
  fields = fields || {title:1};
  for (var i = C.els.length - 1; i >= 0; i--) {
    var e = C.els[i];
    if (e.t === 'text' && e.ed && fields[e.ed.f] && !e._pop) {
      var s = {}; for (var k in e) s[k] = e[k];
      s.x += dx; s.y += dy; s.color = color; s.ed = null; s._pop = 1; e._pop = 1;
      C.els.splice(i, 0, s);
    }
  }
};
/* solid offset shadow behind every rectangle that matches (sticker cards) */
K.offsetShadow = function(C, test, color, d){
  for (var i = C.els.length - 1; i >= 0; i--) {
    var e = C.els[i];
    if (e.t === 'rect' && !e._sh && test(e)) {
      e._sh = 1; C.els.splice(i, 0, {t:'rect', x:e.x + d, y:e.y + d, w:e.w, h:e.h, fill:color, r:e.r, _sh:1});
    }
  }
};
})();

</script>
<?php
}, 1006);
