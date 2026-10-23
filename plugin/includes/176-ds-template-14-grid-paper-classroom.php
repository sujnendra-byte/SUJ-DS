<?php
/**
 * Code Snippets export — DS Template 14 — Grid Paper Classroom
 *
 * Snippet ID : 913
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:33:15
 * Load order : 171 of 191
 */
/**
 * DS Template 14 — Grid Paper Classroom
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 * Its own AI voice + storyline, its own signature layouts, its own PPTX build.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('classroom', array(
        'name'     => "Grid Paper Classroom",
        'fonts'    => array('head' => "Lilita One", 'body' => "Nunito"),
        'pal'      => array('bg' => '#F4F8FC', 'text' => '#1E2A47', 'accent' => '#E8453C', 'accent2' => '#FBC25A'),
        'voice'    => "Friendly teacher voice: simple words, clear steps, examples a student can relate to.",
        'recipe'   => "Learning goals, key concepts, step-by-step explanation, examples, activity, recap, quiz questions.",
        'generate' => 'ds_tpl14_generate',
        'build'    => 'ds_tpl14_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl14_generate') ) {
function ds_tpl14_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl14_build') ) {
function ds_tpl14_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-classroom">
/* Template 14 — Grid Paper Classroom : blue grid paper, red + sunflower blocks, navy outlines, drawn geometry doodles */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#1E2A47', RED = '#E8453C', SUN = '#FBC25A', SKY = '#6FA8F5', LEAF = '#7CC96A';
var P0 = {bg:'#F4F8FC', text:'#1E2A47', title:'#E8453C', muted:'#3E4A66', accent:'#E8453C', accent2:'#FBC25A', card:'#FBC25A', line:'#1E2A47', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[RED, SUN, SKY, LEAF, '#B384F0', '#F28BB6'],
  header:{kind:'left', tMax:44, tMin:26, sMax:17, sc:'#3E4A66', top:48},
  card:{kind:'outline', r:6, fill:'#FFE3A6', line:NAVY, lw:2.5, dark:false, tc:NAVY, mc:'#2E3A58', hl:{kind:'solid', fill:RED, dark:true, tc:'#FFFFFF', mc:'#FFE9E7'}},
  icon:{kind:'circle', bg:NAVY, fg:'#FFFFFF'},
  num:{kind:'circle', bg:RED, fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#FFFFFF', line:NAVY, lw:2, r:6, lc:RED, label:'Remember'},
  footer:{brand:false, page:true, color:NAVY}, xRows:'#FFFFFF', hubFill:'#FFE3A6', road:NAVY, flowBar:'#DCE6F2',
  imgFramed:true, imgR:8, stepWord:'Step'
};
/* ---- drawn doodles (all original shapes, navy outline + flat fill) ---- */
function setSquare(C, x, y, s, rot){
  var r = rot * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r);
  function T(p){ return [x + p[0] * cs - p[1] * sn, y + p[0] * sn + p[1] * cs]; }
  K.poly(C, [[0, 0], [s, s], [0, s]].map(T), {fill:SKY, stroke:NAVY, lw:3});
  K.poly(C, [[s * 0.2, s * 0.45], [s * 0.55, s * 0.8], [s * 0.2, s * 0.8]].map(T), {fill:'#F4F8FC', stroke:NAVY, lw:2.5});
}
function protractor(C, cx, cy, r){
  K.seg(C, cx, cy, r * 0.55, r, 180, 360, {fill:'#FFFFFF', stroke:NAVY, lw:3});
  for (var a = 190; a < 360; a += 20) { var p = K.pol(cx, cy, r, a), q = K.pol(cx, cy, r * 0.84, a); C.line(p[0], p[1], q[0], q[1], {color:NAVY, lw:2}); }
}
function pencil(C, x, y, len, rot){
  var r = rot * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r), w = 22;
  function T(p){ return [x + p[0] * cs - p[1] * sn, y + p[0] * sn + p[1] * cs]; }
  K.poly(C, [[0, 0], [len, 0], [len, w], [0, w]].map(T), {fill:SUN, stroke:NAVY, lw:2.5});
  K.poly(C, [[len, 0], [len + 30, w / 2], [len, w]].map(T), {fill:'#F6D9B0', stroke:NAVY, lw:2.5});
  K.poly(C, [[len + 20, w / 2 - 4], [len + 30, w / 2], [len + 20, w / 2 + 4]].map(T), {fill:NAVY});
  K.poly(C, [[-16, 0], [0, 0], [0, w], [-16, w]].map(T), {fill:'#F28BB6', stroke:NAVY, lw:2.5});
}
function cube(C, x, y, s){
  K.poly(C, [[x, y + s * 0.3], [x + s * 0.5, y], [x + s, y + s * 0.3], [x + s * 0.5, y + s * 0.6]], {fill:'#B7E3A8', stroke:NAVY, lw:2.5});
  K.poly(C, [[x, y + s * 0.3], [x + s * 0.5, y + s * 0.6], [x + s * 0.5, y + s * 1.1], [x, y + s * 0.8]], {fill:LEAF, stroke:NAVY, lw:2.5});
  K.poly(C, [[x + s, y + s * 0.3], [x + s * 0.5, y + s * 0.6], [x + s * 0.5, y + s * 1.1], [x + s, y + s * 0.8]], {fill:'#5AA84A', stroke:NAVY, lw:2.5});
}
function ball(C, cx, cy, r){ C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:RED, line:NAVY, lw:3}); C.ell(cx - r * 0.5, cy - r * 0.6, r * 0.45, r * 0.3, {fill:'#FFFFFF', op:0.6}); }
function spark(C, x, y, r){ K.spark(C, x, y, r, NAVY, 1); }
function bg(C, kind){
  C.bg({fill:'#F4F8FC'});
  K.grid(C, 36, '#C9DCEC', 0.9);
  var odd = (C.i || 0) % 2 === 1;
  if (kind === 'cover' || kind === 'closing') return;
  /* one bold red wedge on alternate sides + a couple of doodles in the free corner */
  if (odd) { K.poly(C, [[W, H - 150], [W, H], [W - 200, H]], {fill:RED}); }
  else { K.poly(C, [[0, H - 150], [0, H], [200, H]], {fill:RED}); ball(C, 46, H - 40, 18); }
  spark(C, W - 110, 64, 13); spark(C, W - 70, 120, 8);
}
function tag(C, x, y, w, h, text, ed){
  C.rect(x + 5, y + 5, w, h, {fill:NAVY});
  C.rect(x, y, w, h, {fill:SUN, line:NAVY, lw:2.5});
  C.text(x + 12, y, w - 24, h, text, {font:'body', fit:{max:18, min:11}, wt:700, color:NAVY, align:'c', va:'m', ed:ed, maxLines:2, lh:1.2});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) {
    C.rect(W - 548, 44, 510, H - 88, {fill:NAVY}); D.imgBox(C, W - 556, 36, 510, H - 88, {r:0});
    C.rect(W - 556, 36, 510, H - 88, {line:NAVY, lw:3});
    pencil(C, W - 600, H - 120, 180, -18); spark(C, 80, 80, 18); setSquare(C, 60, H - 200, 120, 0);
    var te = C.text(64, 150, 620, 300, C.s.title, {font:'head', fit:{max:78, min:36}, color:RED, ed:{f:'title'}, maxLines:4, va:'b', lh:1.02});
    if (C.s.subtitle) tag(C, 64, 480, 560, 66, C.s.subtitle, {f:'subtitle'});
    if (f.presenter || f.date) C.text(64, 580, 600, 24, [f.presenter, f.date].filter(Boolean).join('  ·  '), {font:'body', size:15, wt:700, color:P.muted});
    return;
  }
  /* centred title with doodles around the edges */
  setSquare(C, 70, 70, 150, 0); protractor(C, W - 150, 170, 110); pencil(C, W - 360, 70, 150, 20);
  cube(C, W - 200, H - 190, 110); ball(C, 170, H - 120, 46); K.poly(C, [[0, H - 170], [0, H], [260, H]], {fill:RED});
  spark(C, 330, 120, 16); spark(C, W - 330, H - 90, 14); spark(C, 540, H - 70, 10);
  C.text(160, 190, W - 320, 220, C.s.title, {font:'head', fit:{max:92, min:40}, color:RED, ed:{f:'title'}, maxLines:3, align:'c', va:'b', lh:1.02});
  if (C.s.subtitle) tag(C, W / 2 - 300, 440, 600, 62, C.s.subtitle, {f:'subtitle'});
  if (f.presenter || f.company) C.text(200, 540, W - 400, 24, [f.company, f.presenter, f.date].filter(Boolean).join('  ·  '), {font:'body', size:15, wt:700, color:P.muted, align:'c'});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) { C.rect(52, 52, 470, H - 104, {fill:NAVY}); D.imgBox(C, 44, 44, 470, H - 104, {r:0}); C.rect(44, 44, 470, H - 104, {line:NAVY, lw:3}); }
  else { protractor(C, 170, 190, 110); setSquare(C, W - 220, 60, 150, 0); cube(C, 90, H - 200, 100); ball(C, W - 140, H - 120, 44); }
  var x = o.img ? 580 : 160, w = o.img ? W - 640 : W - 320, al = o.img ? 'l' : 'c';
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:100, min:44}, color:RED, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:1});
  if (C.s.subtitle) C.text(x, 370, w, 64, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, x, 480, w, {cards:true});
  spark(C, W / 2 + 360, 110, 16);
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:RED}); return; }
  C.rect(206, 146, W - 400, H - 280, {fill:NAVY}); C.rect(200, 140, W - 400, H - 280, {fill:'#FFE3A6', line:NAVY, lw:3});
  D.B.quote(C, {x:250, y:170, w:W - 500, h:H - 340}, {qc:RED});
  setSquare(C, 40, H - 170, 120, 0); spark(C, W - 120, 90, 18);
}
/* ============================================================
   SIGNATURE BODIES — sticker notes, tape, pins, ruled paper, pencils
   ============================================================ */
var INK2 = '#2E3A58', RULE = '#C3D5EA', HOLE = '#DCE6F2';
var TINT = ['#FFE3A6', '#CFE2FF', '#D8F2CF', '#FFD6D3', '#E8DAFF', '#FFD9EA'];
function tint(k){ return TINT[k % TINT.length]; }
function ser(k){ return S.series[k % S.series.length]; }
function fitC(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
/* sticker card: navy outline; decor() adds the solid offset shadow */
function sticker(C, x, y, w, h, fill, r){ C.rect(x, y, w, h, {fill:fill, line:NAVY, lw:2.5, r:r == null ? 6 : r}); }
function tape(C, cx, y, w, rot){ C.shape('parallelogram', cx - w / 2, y - 13, w, 26, {fill:'#FFF6D6', op:0.82, rot:rot == null ? -3 : rot}); }
function pin(C, cx, cy, c){ C.ell(cx - 11, cy - 11, 22, 22, {fill:c, line:NAVY, lw:2.2}); C.ell(cx - 6, cy - 7, 7, 5, {fill:'#FFFFFF', op:0.75}); }
/* text with a navy "pop" shadow */
function pop(C, x, y, w, h, text, o, dd){
  var q = {}; for (var k in o) q[k] = o[k]; q.color = NAVY; q.ed = null; dd = dd || 3;
  C.text(x + dd, y + dd, w, h, text, q); return C.text(x, y, w, h, text, o);
}
function arrowHead(C, fx, fy, tx, ty, s, c){
  var a = Math.atan2(ty - fy, tx - fx), b = 0.5;
  K.poly(C, [[tx, ty], [tx - s * Math.cos(a - b), ty - s * Math.sin(a - b)], [tx - s * Math.cos(a + b), ty - s * Math.sin(a + b)]], {fill:c});
}
function dH(C, t, w, ds, lh){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:lh || 1.36}).h : 0; }
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dH(C, t, w, F.ds) : F.hs * 1.2 * Math.min(2, C.measureText(t.d, w, {font:'head', max:F.hs, min:F.hs}).n); }
/* heading + description; a headless item shows its text as the heading */
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || NAVY, align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || NAVY, mc:o.mc || INK2, hLines:o.hLines});
}

/* 1 — two taped sticky notes, staggered, joined by a drawn arrow (p2) */
function stickyPair(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var mid = n > 1 ? Math.min(110, Z.w * 0.12) : 0, nw = n > 1 ? (Z.w - mid) / 2 : Math.min(Z.w, 620), st = Math.min(34, Z.h * 0.08), nh = Z.h - st - 12;
  var pad = Math.min(34, nw * 0.08), d = 58, F = fitC(C, it, nw - pad * 2, 84, nh - pad * 2 - d - 22, {hMax:30, dMax:21, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * (nw + mid), y = Z.y + (k ? st : 0) + 6;
    sticker(C, x, y, nw, nh, k ? '#CFE2FF' : '#FFE3A6', 4);
    tape(C, x + nw / 2, y, 130, k ? 3 : -3);
    C.ell(x + pad, y + pad, d, d, {fill:k ? NAVY : RED, line:NAVY, lw:2.5});
    C.text(x + pad, y + pad, d, d, String(k + 1), {font:'head', size:32, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    var by = y + pad + d + 22;
    block(C, x + pad, by, nw - pad * 2, y + nh - pad - by, t, F);
  });
  if (n > 1) {
    var x1 = Z.x + nw + 12, x2 = Z.x + nw + mid - 8, y1 = Z.y + Z.h * 0.3, y2 = y1 + 40;
    C.path([['M', x1, y1], ['C', x1 + mid * 0.45, y1 - 70, x2 - mid * 0.1, y2 - 60, x2, y2]], {stroke:NAVY, lw:3});
    arrowHead(C, x2 - mid * 0.1, y2 - 60, x2, y2, 18, NAVY);
    K.spark(C, Z.x + nw + mid / 2, Z.y + Z.h * 0.72, 14, RED, 1);
  }
}

/* 2 — T-chart drawn on a taped white sheet (p2) */
function tChart(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return stickyPair(C, Z, o);
  sticker(C, Z.x, Z.y + 8, Z.w, Z.h - 8, '#FFFFFF', 4);
  tape(C, Z.x + 40, Z.y + 8, 110, -8); tape(C, Z.x + Z.w - 40, Z.y + 8, 110, 8);
  var hh = Math.min(100, Z.h * 0.27), cx = Z.x + Z.w / 2, bar = Z.y + 8 + hh, cw = Z.w / 2 - 70;
  for (var y = bar + 44; y < Z.y + Z.h - 14; y += 36) C.rect(Z.x + 16, y, Z.w - 32, 1.2, {fill:RULE});
  C.rect(Z.x + 28, bar, Z.w - 56, 5, {fill:NAVY, r:2}); C.rect(cx - 2.5, bar, 5, Z.y + Z.h - 22 - bar, {fill:NAVY, r:2});
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 50, hh - 26, {font:'head', max:36, min:16, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw, Z.y + Z.h - bar - 60, {font:'body', max:23, min:12, lh:1.5});
  it.forEach(function(t, k){
    var x = k ? cx + 40 : Z.x + 30, c = k ? SKY : RED;
    C.ell(x, Z.y + 8 + (hh - 40) / 2, 40, 40, {fill:c, line:NAVY, lw:2.5});
    C.text(x, Z.y + 8 + (hh - 40) / 2, 40, 40, String(k + 1), {font:'head', size:22, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    pop(C, x + 54, Z.y + 12, cw - 50, hh - 10, t.h || t.d, {font:'head', size:hs, color:RED, va:'m', lh:1.05, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2}, 2);
    if (t.h && t.d) C.text(x + 6, bar + 30, cw, Z.y + Z.h - bar - 50, t.d, {font:'body', size:ds, color:NAVY, lh:1.5, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 3 — pin board: staggered sticky cards pinned and strung together with red yarn */
function pinBoard(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = n <= 4 ? (Z.w < 800 && n === 4 ? 2 : n) : 3;
  var rows = Math.ceil(n / cols), gx = cols > 3 ? 24 : 34, gy = 36, stag = rows > 1 ? 10 : 22;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1) - stag - 8) / rows, pad = cw < 240 ? 18 : 24, nh = rows > 1 ? 34 : 44;
  var F = fitC(C, it, cw - pad * 2, 56, ch - pad * 2 - nh - 16, {hMax:rows > 1 ? 20 : 23, dMax:rows > 1 ? 15 : 17});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, cw - pad * 2, F)); });
  var pins = [];
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + 8 + r * (ch + gy) + (c % 2 ? stag : 0);
    sticker(C, x, y, cw, ch, tint(k), 4);
    pins.push([x + cw / 2, y + 2, r, k]);
    var top = y + pad + 10 + Math.max(0, (ch - pad * 2 - 10 - nh - 14 - bh) * 0.35);
    pop(C, x + pad, top, cw - pad * 2, nh, u.pad2(k + 1), {font:'head', size:nh - 4, color:RED, lh:1, caps:false, ls:0}, 2);
    block(C, x + pad, top + nh + 14, cw - pad * 2, y + ch - pad - (top + nh + 14), t, F);
  });
  for (var r = 0; r < rows; r++) {
    var row = pins.filter(function(p){ return p[2] === r; }); if (row.length < 2) continue;
    var pts = [[row[0][0] - cw * 0.5 - 10, row[0][1] - 14]];
    row.forEach(function(p, j){ if (j) pts.push([(row[j - 1][0] + p[0]) / 2, (row[j - 1][1] + p[1]) / 2 + 22]); pts.push([p[0], p[1]]); });
    pts.push([row[row.length - 1][0] + cw * 0.5 + 10, row[row.length - 1][1] - 14]);
    K.curve(C, pts, {stroke:RED, lw:2.5});
  }
  pins.forEach(function(p){ pin(C, p[0], p[1], ser(p[3] + 1)); });
}

/* 4 — worksheet: white binder sheet, punched holes, red margin, numbered ruled lines */
function worksheet(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  sticker(C, Z.x, Z.y, Z.w, Z.h, '#FFFFFF', 4);
  var ml = Z.x + 62; C.rect(ml, Z.y + 3, 2, Z.h - 6, {fill:RED, op:0.75});
  [0.18, 0.5, 0.82].forEach(function(f){ C.ell(Z.x + 18, Z.y + Z.h * f - 11, 22, 22, {fill:HOLE, line:'#9FB3C8', lw:1.5}); });
  var cols = n > 5 && (Z.w > 760 || n > 6) ? 2 : 1, per = Math.ceil(n / cols), padT = 18, padB = 14, gx = 44;
  var ax = ml + 26, aw = Z.x + Z.w - 26 - ax, cw = (aw - gx * (cols - 1)) / cols, rh = (Z.h - padT - padB) / per, nw = Math.min(50, rh * 0.8);
  var F = fitC(C, it, cw - nw - 8, Math.min(34, rh * 0.42), rh - 12, {hMax:per <= 3 ? 26 : (it.some(function(t){ return t.h; }) ? 22 : 25), dMax:per <= 3 ? 19 : 16, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = ax + c * (cw + gx), y = Z.y + padT + r * rh;
    C.rect(x, y + rh - 3, cw, 1.5, {fill:RULE});
    C.text(x, y, nw, rh - 4, (k + 1) + '.', {font:'head', size:Math.min(30, Math.max(18, rh * 0.42)), color:RED, va:'m', lh:1, caps:false, ls:0});
    var bh = Math.min(rh - 6, blockH(C, t, cw - nw - 8, F));
    block(C, x + nw, y + (rh - 3 - bh) / 2, cw - nw - 8, bh + 4, t, F, {hLines:1});
  });
}

/* 5 — index flashcards: numbered sticker dot, red header rule, blue ruled lines under the text */
function flashcards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 800 && cols > 2) cols = 2;
  var rows = Math.ceil(n / cols), gx = cols > 3 ? 20 : 26, gy = rows > 2 ? 20 : 28, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1) - 6) / rows;
  var pad = cw < 240 ? 16 : 22, head = Math.min(rows > 1 ? 58 : 76, ch * 0.34), bd = Math.min(36, head - 12);
  var bare = !it.some(function(t){ return t.h; });
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - pad * 2 - bd - 12, head - 10, {font:'head', max:cols >= 4 ? 20 : 26, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return bare ? '' : t.d; }), cw - pad * 2, ch - head - 20, {font:'body', max:rows > 1 ? (cols > 3 ? 17 : 19) : 21, min:11, lh:1.55});
  var bs = C.groupFit(it.map(function(t){ return bare ? t.d : ''; }), cw - pad * 2, ch - head - 24, {font:'head', max:30, min:13, maxLines:2});
  var need = 0; it.forEach(function(t){ need = Math.max(need, bare ? C.measureText(t.d, cw - pad * 2, {font:'head', max:bs, min:bs, lh:1.25, maxLines:2}).h : dH(C, t, cw - pad * 2, ds, 1.55)); });
  var ch2 = Math.min(ch, Math.max(ch * 0.62, head + 12 + need) + (bare ? bs * 1.25 : ds * 1.55) * 3), oy = Z.y + Math.max(0, (Z.h - (ch2 * rows + gy * (rows - 1))) / 2); ch = ch2;
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = oy + r * (ch + gy), c1 = ser(k);
    sticker(C, x, y, cw, ch, '#FFFFFF', 3);
    C.ell(x + pad, y + (head - bd) / 2, bd, bd, {fill:c1, line:NAVY, lw:2});
    C.text(x + pad, y + (head - bd) / 2, bd, bd, u.pad2(k + 1), {font:'head', size:Math.round(bd * 0.42), color:K.on(c1), align:'c', va:'m', lh:1, caps:false, ls:0});
    if (!bare && t.h) C.text(x + pad + bd + 12, y + 4, cw - pad * 2 - bd - 12, head - 8, t.h, {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.rect(x + 3, y + head, cw - 6, 2, {fill:RED, op:0.8});
    var ty = y + head + 12, step = (bare ? bs * 1.25 : ds * 1.55);
    for (var ly = ty + step; ly < y + ch - 6; ly += step) C.rect(x + 3, ly + 1, cw - 6, 1, {fill:RULE});
    if (bare) C.text(x + pad, ty, cw - pad * 2, ch - head - 18, t.d, {font:'head', size:bs, color:NAVY, lh:1.25, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    else C.text(x + pad, ty, cw - pad * 2, ch - head - 18, t.d, {font:'body', size:ds, color:INK2, lh:1.55, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 6 — ruler stats: a big wooden ruler, value stickers pointing at their marks */
function statsOf(C){ return C.it.slice(0, 4).map(function(t){ return {t:t, v:t.h || t.d, l:t.h ? t.d : '', x:t.x || ''}; }); }
function rulerStats(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var cw = Z.w / n, vh = Math.min(124, Z.h * 0.3), rh = 54, lw = cw - 24;
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 60, {font:'head', max:24, min:13, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Z.h - vh - rh - 64 - lh - 10, {font:'body', max:18, min:11, lh:1.4});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var tot = vh + 34 + rh + 30 + lh + (dh ? dh + 8 : 0), ry = Z.y + Math.max(0, (Z.h - tot) / 2) + vh + 34;
  sticker(C, Z.x, ry, Z.w, rh, SUN, 4);
  for (var i = 0, tx = Z.x + 16; tx < Z.x + Z.w - 8; tx += 16, i++) C.rect(tx, ry, 2, i % 5 === 4 ? 24 : 12, {fill:NAVY});
  var sw = Math.min(cw - 24, 270);
  var vs = C.groupFit(st.map(function(s){ return s.v; }), sw - 30, vh - 24, {font:'head', max:62, min:20, maxLines:1});
  var ly = ry + rh + 30;
  st.forEach(function(s, k){
    var cx = Z.x + cw * (k + 0.5), x = cx - sw / 2, y = ry - 30 - vh, c = ser(k), t = s.t;
    sticker(C, x, y, sw, vh, '#FFFFFF', 6);
    tape(C, x + 26, y + 4, 60, -30);
    K.poly(C, [[cx - 13, y + vh + 2], [cx + 13, y + vh + 2], [cx, ry - 3]], {fill:NAVY});
    pop(C, x + 15, y, sw - 30, vh, s.v, {font:'head', size:vs, color:RED, align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1}, 3);
    C.ell(cx - 12, ry + rh / 2 - 12, 24, 24, {fill:c, line:NAVY, lw:2.5});
    if (s.l) C.text(cx - lw / 2, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(cx - lw / 2, ly + lh + 8, lw, Z.y + Z.h - ly - lh - 8, s.x, {font:'body', size:ds, color:INK2, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — report card: spiral-bound sheet, teacher's red circled marks on the right */
function reportCard(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var top = Z.y + 16; sticker(C, Z.x, top, Z.w, Z.h - 16, '#FFFFFF', 4);
  for (var sx = Z.x + 34; sx < Z.x + Z.w - 20; sx += 44) { C.ell(sx - 6, top + 14, 12, 12, {fill:HOLE, line:'#9FB3C8', lw:1.2}); C.ell(sx - 4, top - 12, 8, 34, {line:NAVY, lw:2.2}); }
  var y0 = top + 40, rh = (Z.y + Z.h - 14 - y0) / n, gw = Math.min(290, Z.w * 0.34), ml = Z.x + 58;
  C.rect(ml, top + 34, 2, Z.h - 16 - 38, {fill:RED, op:0.75});
  var tx = ml + 26, tw = Z.x + Z.w - gw - 30 - tx;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), gw - 70, rh * 0.62, {font:'head', max:56, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), tw, rh * 0.46, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(st.map(function(s){ return s.x; }), tw, rh * 0.4, {font:'body', max:17, min:11, lh:1.35});
  st.forEach(function(s, k){
    var y = y0 + k * rh, t = s.t, gx = Z.x + Z.w - gw / 2 - 24, cy = y + rh / 2;
    if (k < n - 1) C.rect(Z.x + 12, y + rh - 1, Z.w - 24, 1.5, {fill:RULE});
    var lh = s.l ? ls * 1.2 : 0, dh = s.x ? C.measureText(s.x, tw, {font:'body', max:ds, min:ds, lh:1.35}).h : 0, by = cy - (lh + dh + (dh ? 6 : 0)) / 2;
    if (s.l) C.text(tx, by, tw, lh + 2, s.l, {font:'head', size:ls, color:NAVY, lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    if (s.x) C.text(tx, by + lh + 6, tw, dh + 4, s.x, {font:'body', size:ds, color:INK2, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
    var rx = gw / 2 - 12, ry = Math.min(rh * 0.42, 50);
    K.loop(C, gx, cy, rx, ry, -4, RED, 3, 0.95); K.loop(C, gx + 3, cy - 2, rx + 5, ry - 3, 3, RED, 1.5, 0.7);
    C.text(gx - rx + 20, cy - ry, rx * 2 - 40, ry * 2, s.v, {font:'head', size:vs, color:RED, align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}

/* 8 — pencil timeline: milestones taped above and below a long drawn pencil */
function pencilTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var ph = 46, py = Z.y + Z.h / 2 - ph / 2, x0 = Z.x + 18, x1 = Z.x + Z.w - 50;
  C.rect(x0 - 18, py, 22, ph, {fill:'#F28BB6', line:NAVY, lw:2.5, r:6});
  C.rect(x0 + 4, py, 22, ph, {fill:'#C9D3E0', line:NAVY, lw:2.5});
  C.rect(x0 + 26, py, x1 - x0 - 26, ph, {fill:SUN, line:NAVY, lw:2.5});
  C.rect(x0 + 26, py + ph / 2 - 2, x1 - x0 - 26, 4, {fill:'#E9A93A'});
  K.poly(C, [[x1, py], [x1 + 46, py + ph / 2], [x1, py + ph]], {fill:'#F6D9B0', stroke:NAVY, lw:2.5});
  K.poly(C, [[x1 + 30, py + ph / 2 - 7], [x1 + 46, py + ph / 2], [x1 + 30, py + ph / 2 + 7]], {fill:NAVY});
  var cw = (x1 - x0 - 40) / n, w = Math.min(n > 1 ? cw * 1.86 - 18 : 420, 330), avail = py - 34 - Z.y, pad = 16;
  var F = fitC(C, it, w - pad * 2, 44, avail - pad * 2 - 6, {hMax:30, dMax:19, hLines:1});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, w - pad * 2, F)); });
  var ch = Math.min(avail, bh + pad * 2 + 8);
  it.forEach(function(t, k){
    var cx = x0 + 40 + cw * (k + 0.5), up = k % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - w, cx - w / 2));
    var y = up ? py - 34 - ch : py + ph + 34;
    C.rect(cx - 1.5, up ? y + ch : py + ph, 3, 34, {fill:NAVY});
    C.ell(cx - 10, py + ph / 2 - 10, 20, 20, {fill:ser(k) === SUN ? RED : ser(k), line:NAVY, lw:2.5});
    sticker(C, x, y, w, ch, tint(k), 4);
    tape(C, x + w / 2, y + 2, 70, k % 2 ? 4 : -4);
    block(C, x + pad, y + pad + 4, w - pad * 2, ch - pad * 2, t, F, {tc:RED});
  });
}

/* 9 — calendar pages: ring-bound pages with a coloured header band and a big date number */
function calendarPages(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var rows = (Z.w / n < 170 && n > 3) ? 2 : 1, cols = Math.ceil(n / rows), gx = 22, gy = 30;
  var stag = rows === 1 ? 14 : 0, pw = (Z.w - gx * (cols - 1)) / cols, ph = (Z.h - gy * (rows - 1) - stag - 12) / rows;
  var band = Math.min(rows > 1 ? 56 : 74, ph * 0.3), num = Math.min(rows > 1 ? 36 : 54, ph * 0.2);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), pw - 24, band - 14, {font:'head', max:26, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), pw - 28, ph - band - num - 44, {font:'body', max:rows > 1 ? 15 : 17, min:11, lh:1.4});
  var need = 0; it.forEach(function(t){ if (t.h) need = Math.max(need, dH(C, t, pw - 28, ds, 1.4)); });
  var ph2 = Math.min(ph, band + num + 40 + need + 24), oy = Z.y + 12 + Math.max(0, (Z.h - 12 - stag - (ph2 * rows + gy * (rows - 1))) / 2); ph = ph2;
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (pw + gx), y = oy + r * (ph + gy) + (c % 2 ? stag : 0), c1 = ser(k);
    sticker(C, x, y, pw, ph, '#FFFFFF', 4);
    C.rect(x + 1.5, y + 1.5, pw - 3, band, {fill:c1, r:4});
    C.rect(x + 1.5, y + band - 2, pw - 3, 3.5, {fill:NAVY});
    [0.28, 0.72].forEach(function(f){ C.ell(x + pw * f - 5, y + 10, 10, 10, {fill:HOLE}); C.rect(x + pw * f - 4, y - 14, 8, 30, {fill:'#FFFFFF', line:NAVY, lw:2, r:4}); });
    C.text(x + 12, y + 18, pw - 24, band - 20, t.h || t.d, {font:'head', size:hs, color:K.on(c1), align:'c', va:'m', lh:1.05, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    pop(C, x, y + band + 12, pw, num, u.pad2(k + 1), {font:'head', size:num, color:RED, align:'c', lh:1, caps:false, ls:0}, 2);
    if (t.h && t.d) C.text(x + 14, y + band + num + 26, pw - 28, ph - band - num - 34, t.d, {font:'body', size:ds, color:INK2, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
D.register({
  id:'classroom', name:'Grid Paper Classroom', tag:'Illustrated · education', desc:'Blue grid paper, bold red titles with a navy pop shadow, sunflower sticker boxes and hand-drawn geometry doodles.',
  fonts:{head:'Lilita One', body:'Nunito', headW:400, bodyW:600, headLh:1.02, headLs:0.01, gf:'Lilita One|Nunito:wght@400;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  decor:function(C, kind){
    K.popTitle(C, NAVY, 3, 3, {title:1});
    K.offsetShadow(C, function(e){ return e.line === NAVY && e.fill && e.w > 60 && e.h > 40; }, NAVY, 6);
  },
  variants:{
    cover:[V('v1','Doodle frame',{}), V('v2','Title + framed image',{img:true})],
    closing:[V('v1','Doodle frame',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Worksheet',{body:worksheet}), V('n2','Flashcards',{body:flashcards}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Sticker box',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Sticker + image right',{body:'text', box:true, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Sticky notes',{body:stickyPair}), V('n2','T-chart',{body:tChart}), V('n3','Flashcards',{body:flashcards}), V('v2','Image left + sticky notes',{body:stickyPair, img:'left'}), V('v3','T-chart + image right',{body:tChart, img:'right'}), V('n4','Two sticker boxes',{body:'duo'})],
    p3:[V('v1','Pin board',{body:pinBoard}), V('n2','Calendar pages',{body:calendarPages}), V('n3','Flashcards',{body:flashcards}), V('n4','Step pills',{body:'xStack'}), V('v2','Image left + worksheet',{body:worksheet, img:'left'}), V('v3','Pin board + image right',{body:pinBoard, img:'right'}), V('n5','Three sticker cards',{body:'cols'})],
    p4:[V('v1','Flashcards',{body:flashcards}), V('n2','Pin board',{body:pinBoard}), V('n3','Calendar pages',{body:calendarPages}), V('n4','Step pills',{body:'xStack'}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('v3','Image left + flashcards',{body:flashcards, img:'left'}), V('n5','Sticker grid',{body:'grid'})],
    p56:[V('v1','Pin board',{body:pinBoard}), V('n2','Worksheet',{body:worksheet}), V('n3','Flashcards',{body:flashcards}), V('n4','Step pills',{body:'xStack'}), V('v2','Image left + worksheet',{body:worksheet, img:'left'}), V('v3','Pin board + image right',{body:pinBoard, img:'right'}), V('n5','Sticker grid',{body:'grid'})],
    p78:[V('v1','Flashcards',{body:flashcards}), V('n2','Worksheet',{body:worksheet}), V('n3','Step pills',{body:'xStack'}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('n4','Two-column list',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Ruler marks',{body:rulerStats}), V('n2','Report card',{body:reportCard}), V('v2','Image left + report card',{body:reportCard, img:'left'}), V('v3','Ruler + image right',{body:rulerStats, img:'right'}), V('n3','Sticker stats',{body:'statCards', icons:true})],
    timeline:[V('v1','Pencil timeline',{body:pencilTimeline}), V('n2','Calendar pages',{body:calendarPages}), V('n3','Step pills',{body:'xStack'}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('v3','Image left + calendar',{body:calendarPages, img:'left'}), V('n4','Milestone boxes',{body:'tlCards'})],
    quote:[V('v1','Sticker quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
