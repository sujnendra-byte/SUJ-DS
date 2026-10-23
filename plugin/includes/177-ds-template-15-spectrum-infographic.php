<?php
/**
 * Code Snippets export — DS Template 15 — Spectrum Infographic
 *
 * Snippet ID : 914
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:33:16
 * Load order : 172 of 191
 */
/**
 * DS Template 15 — Spectrum Infographic
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
    ds_tpl_register('spectrum', array(
        'name'     => "Spectrum Infographic",
        'fonts'    => array('head' => "Poppins", 'body' => "Poppins"),
        'pal'      => array('bg' => '#F6F7F9', 'text' => '#1E1E2E', 'accent' => '#5B3FA8', 'accent2' => '#2AA7B8'),
        'voice'    => "Clear infographic voice: one idea per point, comparisons and numbers made visual.",
        'recipe'   => "Overview, breakdown by category, process stages, comparison, key figures, timeline, takeaways.",
        'generate' => 'ds_tpl15_generate',
        'build'    => 'ds_tpl15_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl15_generate') ) {
function ds_tpl15_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl15_build') ) {
function ds_tpl15_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-spectrum">
/* Template 15 — Spectrum Infographic : white canvas, halftone corners, five-colour infographic system */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var SER = ['#5B3FA8', '#2AA7B8', '#6CC24A', '#F29A38', '#3B82C4', '#E0558A'];
var P0 = {bg:'#F6F7F9', text:'#1E1E2E', title:'#1E1E2E', muted:'#5A5F6E', accent:'#5B3FA8', accent2:'#2AA7B8', card:'#FFFFFF', line:'#DCDFE6', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:SER,
  header:{kind:'center', tMax:38, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'solid', r:14, fill:'#FFFFFF', shadow:0.08, hl:{kind:'solid', fill:'#5B3FA8', dark:true}},
  icon:{kind:'circle', bg:'#5B3FA8', fg:'#FFFFFF'},
  num:{kind:'circle', bg:'#5B3FA8', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#5B3FA8', op:0.1, lc:'#5B3FA8', label:'In short'},
  footer:{brand:false, page:true, color:'#9AA0AE'}, xRows:'#FFFFFF', hubFill:'#EEF0F4', road:'#2E3140', flowBar:'#E6E8EE', xShadow:0.06,
  imgFramed:true, imgR:16
};
function halftone(C){
  K.halftone(C, W - 250, 20, 14, 7, 18, 5, '#C9CDD8', 0.8);
  K.halftone(C, 20, H - 150, 14, 7, 18, 5, '#C9CDD8', 0.8);
}
function bg(C, kind){ C.bg({fill:'#F6F7F9'}); halftone(C); }
/* decorative five-colour half ring (cover / closing) */
function bigArc(C, cx, cy, R, flip){
  for (var k = 0; k < 5; k++) {
    var a0 = (flip ? 90 : -90) + k * 36 + 1, a1 = a0 + 34;
    K.seg(C, cx, cy, R * 0.58, R, a0, a1, {fill:SER[k]});
    var m = K.pol(cx, cy, R * 0.79, (a0 + a1) / 2);
    C.ell(m[0] - 20, m[1] - 20, 40, 40, {fill:'#FFFFFF', op:0.25});
  }
  C.ell(cx - R * 0.46, cy - R * 0.46, R * 0.92, R * 0.92, {fill:'#FFFFFF', shadow:0.1});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) { D.imgBox(C, W - 560, 40, 520, H - 80, {r:20}); for (var k = 0; k < 5; k++) C.rect(W - 560 + k * 104, H - 40, 104, 10, {fill:SER[k]}); }
  else bigArc(C, 30, H / 2, 330, false);
  var x = o.img ? 64 : 440, w = o.img ? 600 : W - 504;
  if (f.company) C.text(x, 110, w, 22, f.company, {font:'body', size:14, wt:800, color:SER[0], caps:true, ls:0.18, ed:{f:'tf-company'}});
  C.text(x, 150, w, 280, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  for (var j = 0; j < 5; j++) C.rect(x + j * 34, 452, 28, 6, {fill:SER[j], r:3});
  if (C.s.subtitle) C.text(x, 478, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 600, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 480, H - 80, {r:20}); else bigArc(C, W - 30, H / 2, 330, true);
  var x = o.img ? 580 : 64, w = o.img ? W - 640 : 720;
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:40}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  for (var j = 0; j < 5; j++) C.rect(x + j * 34, 370, 28, 6, {fill:SER[j], r:3});
  if (C.s.subtitle) C.text(x, 396, w, 64, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  if (o.img) D.contactList(C, x, 480, w, {gap:54}); else D.contactRow(C, x, 500, w, {cards:true});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:SER[0]}); return; }
  for (var k = 0; k < 5; k++) C.rect(200 + k * 176, 130, 170, 8, {fill:SER[k], r:4});
  D.B.quote(C, {x:200, y:160, w:W - 400, h:H - 290}, {qc:SER[0]});
}
/* ============================================================
   SIGNATURE BODIES — segmented rings, colour bands, ribbons, rainbow waves
   ============================================================ */
var INK = '#1E1E2E', MUT = '#5A5F6E', TRACK = '#E6E8EE';
function sc(k){ return SER[((k % SER.length) + SER.length) % SER.length]; }
function onS(c){ return u.lum(c) > 0.55 ? INK : '#FFFFFF'; }
function soft(c, t){ return u.mix(c, '#FFFFFF', t == null ? 0.88 : t); }
function fitS(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function dH(C, t, w, ds){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dH(C, t, w, F.ds) : F.hs * 1.2 * Math.min(2, C.measureText(t.d, w, {font:'head', max:F.hs, min:F.hs}).n); }
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || INK, align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || INK, mc:o.mc || MUT, hLines:o.hLines});
}
function numDot(C, x, y, d, k, c){ C.ell(x, y, d, d, {fill:c}); C.text(x, y, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.4), wt:700, color:onS(c), align:'c', va:'m', lh:1, caps:false, ls:0}); }
/* half ellipse as a thick stroked curve (up = bulges upward) */
function halfEll(C, x0, x1, y, ry, up, color, lw){
  var rx = (x1 - x0) / 2, cx = x0 + rx, k = 0.5523, s = up ? -1 : 1;
  C.path([['M', x0, y], ['C', x0, y + s * ry * k, cx - rx * k, y + s * ry, cx, y + s * ry], ['C', cx + rx * k, y + s * ry, x1, y + s * ry * k, x1, y]], {stroke:color, lw:lw});
}

/* 1 — split ring: one ring cut in two colour halves, each side carries its text (p2) */
function splitRing(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var R = Math.min(Z.h * 0.48, 200, Z.w * 0.2), cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r0 = R * 0.6;
  var sw = (Z.w - 2 * R) / 2 - 60, F = fitS(C, it, sw, 80, Z.h - 60, {hMax:30, dMax:21, noBoost:true});
  it.forEach(function(t, k){
    var c = sc(k), a0 = k ? -88 : 92, a1 = k ? 88 : 268;
    K.seg(C, cx, cy, r0, R, a0, a1, {fill:c});
    var m = K.pol(cx, cy, (r0 + R) / 2, k ? 0 : 180);
    C.text(m[0] - 40, m[1] - 24, 80, 48, u.pad2(k + 1), {font:'head', size:Math.round(R * 0.2), wt:800, color:onS(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    var bx = k ? cx + R + 60 : Z.x, bh = blockH(C, t, sw, F), al = k ? 'l' : 'r', by = cy - (bh + 26) / 2;
    C.line(k ? cx + R + 8 : cx - R - 8, cy, k ? bx - 14 : bx + sw + 14, cy, {color:c, lw:2});
    C.ell(k ? bx - 20 : bx + sw + 8, cy - 6, 12, 12, {fill:c});
    C.rect(k ? bx : bx + sw - 60, by, 60, 6, {fill:c, r:3});
    block(C, bx, by + 22, sw, bh + 6, t, F, {align:al, tc:u.mix(c, INK, 0.25)});
  });
  C.ell(cx - r0 + 14, cy - r0 + 14, (r0 - 14) * 2, (r0 - 14) * 2, {fill:'#FFFFFF', shadow:0.1});
  for (var j = 0; j < 5; j++) C.rect(cx - 44 + j * 18, cy - 3, 12, 6, {fill:sc(j), r:3});
}

/* 2 — ribbon cards: white cards with a folded colour ribbon carrying the number */
function ribbonCards(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : 3; if (Z.w < 800 && n === 4) cols = 2; if (Z.w < 800 && n > 4) cols = 3;
  var rows = Math.ceil(n / cols), gx = 26, gy = 28, cw = (Z.w - gx * (cols - 1) - 14) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var rb = Math.min(rows > 1 ? 40 : 52, ch * 0.22), pad = cw < 240 ? 18 : 24, top = 20 + rb + 22;
  var F = fitS(C, it, cw - pad * 2, 60, ch - top - pad - 8, {hMax:rows > 1 ? 21 : 25, dMax:rows > 1 ? 16 : 19.5});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, cw - pad * 2, F)); });
  ch = Math.min(ch, Math.max(ch * 0.72, top + bh + pad + 40)); var oy = Z.y + Math.max(0, (Z.h - (ch * rows + gy * (rows - 1))) / 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + 14 + c * (cw + gx), y = oy + r * (ch + gy), col = sc(k);
    D.card(C, x, y, cw, ch, {});
    C.rect(x, y + ch - 6, cw, 6, {fill:col});
    var rw = Math.min(cw * 0.62, 150);
    C.rect(x - 14, y + 20, rw, rb, {fill:col});
    K.poly(C, [[x - 14, y + 20 + rb], [x, y + 20 + rb], [x, y + 20 + rb + 12]], {fill:u.mix(col, '#000000', 0.35)});
    K.poly(C, [[x - 14 + rw, y + 20], [x - 14 + rw + rb * 0.4, y + 20], [x - 14 + rw, y + 20 + rb / 2], [x - 14 + rw + rb * 0.4, y + 20 + rb], [x - 14 + rw, y + 20 + rb]], {fill:col});
    C.text(x + 4, y + 20, rw - 24, rb, u.pad2(k + 1), {font:'head', size:Math.round(rb * 0.56), wt:800, color:onS(col), va:'m', lh:1, caps:false, ls:0.02});
    var by = y + top + Math.max(0, (ch - top - pad - bh) * 0.3);
    block(C, x + pad, by, cw - pad * 2, y + ch - pad - by, t, F, {tc:u.mix(col, INK, 0.3)});
  });
}

/* 3 — segmented donut: one colour segment per item, labels fanned left and right */
function segDonut(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var R = Math.min(Z.h * 0.5, 200, Z.w * 0.19), r0 = R * 0.58, cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, step = 360 / n;
  var nr = Math.ceil(n / 2), nl = n - nr, sw = (Z.w - 2 * R) / 2 - 70, rows = Math.max(nr, 1), gy = 12, rh = Math.min(120, (Z.h - gy * (rows - 1)) / rows);
  var F = fitS(C, it, sw - 44, Math.min(34, rh * 0.45), rh - 6, {hMax:n <= 4 ? 23 : 20, dMax:n <= 4 ? 17 : 15, hLines:1});
  it.forEach(function(t, k){
    var c = sc(k), a0 = -90 + k * step + 1.2, a1 = -90 + (k + 1) * step - 1.2, am = (a0 + a1) / 2;
    K.seg(C, cx, cy, r0, R, a0, a1, {fill:c});
    var m = K.pol(cx, cy, (r0 + R) / 2, am), is = Math.min(34, (R - r0) * 0.45);
    K.icon(C, m[0] - is / 2, m[1] - is / 2, is, t, onS(c));
    var right = k < nr, idx = right ? k : n - 1 - k, cnt = right ? nr : nl;
    var y0 = cy - (cnt * rh + (cnt - 1) * gy) / 2, y = y0 + idx * (rh + gy), my = y + rh / 2;
    var bx = right ? cx + R + 70 : Z.x, e = K.pol(cx, cy, R + 6, am), ex = right ? bx - 16 : bx + sw + 16;
    C.path([['M', e[0], e[1]], ['L', right ? Math.max(e[0] + 12, ex - 26) : Math.min(e[0] - 12, ex + 26), my], ['L', ex, my]], {stroke:c, lw:1.8});
    C.ell(e[0] - 4, e[1] - 4, 8, 8, {fill:c});
    numDot(C, right ? bx : bx + sw - 34, my - 17, 34, k, c);
    var tx = right ? bx + 44 : bx, tw = sw - 44, bh = Math.min(rh, blockH(C, t, tw, F));
    block(C, tx, my - bh / 2, tw, bh + 4, t, F, {align:right ? 'l' : 'r', hLines:1});
  });
  C.ell(cx - r0 + 10, cy - r0 + 10, (r0 - 10) * 2, (r0 - 10) * 2, {fill:'#FFFFFF', shadow:0.1});
  var tt = String(C.s.title || '').split(/\s+/).slice(0, 3).join(' ');
  C.text(cx - r0 + 26, cy - r0 * 0.5, (r0 - 26) * 2, r0, tt, {font:'head', fit:{max:22, min:12}, color:INK, align:'c', va:'m', lh:1.1, maxLines:3});
}

/* 4 — colour bands: numbered colour tab + tinted band per item (one or two columns) */
function colorBands(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = n > 5 && (Z.w > 760 || n > 6) ? 2 : 1, per = Math.ceil(n / cols), gx = 28, gy = per > 4 ? 10 : 14;
  var cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(110, (Z.h - gy * (per - 1)) / per), lw = o.stat ? Math.min(190, cw * 0.36) : Math.min(cols > 1 ? 86 : 110, cw * 0.2), tx0 = lw + 22, tw = cw - tx0 - 26;
  var oy = Z.y + Math.max(0, (Z.h - (rh * per + gy * (per - 1))) / 2);
  var F = fitS(C, it, tw, Math.min(34, rh * 0.45), rh - 10, {hMax:per <= 3 ? 26 : 21, dMax:per <= 3 ? 19 : 15.5, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = oy + r * (rh + gy), col = sc(k);
    C.rect(x, y, cw, rh, {fill:soft(col), r:rh / 2 > 16 ? 12 : 6});
    C.shape('homePlate', x, y, lw + 12, rh, {fill:col});
    C.rect(x + cw - 8, y + rh * 0.2, 4, rh * 0.6, {fill:col, r:2});
    if (o.stat && t.h) {
      C.text(x + 8, y, lw - 12, rh, t.h, {font:'head', fit:{max:Math.min(40, rh * 0.5), min:14}, wt:800, color:onS(col), align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:0}, maxLines:1});
      var sl = C.measureText(t.d, tw, {font:'head', max:F.hs + 2, min:13, maxLines:1}), sx = t.x ? C.measureText(t.x, tw, {font:'body', max:F.ds, min:11, h:rh - sl.h - 14}) : null, sh = sl.h + (sx ? sx.h + 4 : 0), sy = y + (rh - sh) / 2;
      C.text(x + tx0, sy, tw, sl.h + 2, t.d, {font:'head', size:sl.s, color:u.mix(col, INK, 0.45), lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
      if (sx) C.text(x + tx0, sy + sl.h + 4, tw, sx.h + 4, t.x, {font:'body', size:sx.s, color:MUT, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
      return;
    }
    C.text(x, y, lw - 6, rh, u.pad2(k + 1), {font:'head', size:Math.min(34, rh * 0.46), wt:800, color:onS(col), align:'c', va:'m', lh:1, caps:false, ls:0});
    var bh = Math.min(rh - 6, blockH(C, t, tw, F));
    block(C, x + tx0, y + (rh - bh) / 2, tw, bh + 4, t, F, {hLines:1, tc:u.mix(col, INK, 0.45)});
  });
}

/* 5 — spectrum strip: one thick multi-colour bar holding the values, notes hanging below */
function statsOf(C){ return C.it.slice(0, 4).map(function(t){ return {t:t, v:t.h || t.d, l:t.h ? t.d : '', x:t.x || ''}; }); }
function spectrumStrip(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var cw = Z.w / n, bh = Math.min(150, Z.h * 0.36), lw = cw - 28;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), cw - 36, bh * 0.55, {font:'head', max:64, min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 60, {font:'head', max:22, min:13, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Z.h - bh - lh - 70, {font:'body', max:17, min:11, lh:1.38});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.38}).h); });
  var tot = bh + 44 + lh + (dh ? dh + 8 : 0), y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
  C.rect(Z.x, y0 + 8, Z.w, bh, {fill:'#000000', op:0.06, r:bh / 2});
  st.forEach(function(s, k){
    var x = Z.x + k * cw, c = sc(k), t = s.t, first = k === 0, last = k === n - 1;
    C.rect(x, y0, cw, bh, {fill:c, r:first || last ? bh / 2 : 0});
    if (first && !last) C.rect(x + cw / 2, y0, cw / 2, bh, {fill:c});
    if (last && !first) C.rect(x, y0, cw / 2, bh, {fill:c});
    C.text(x + 18, y0 + bh * 0.16, cw - 36, bh * 0.5, s.v, {font:'head', size:vs, wt:800, color:onS(c), align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    C.rect(x + cw / 2 - 18, y0 + bh * 0.72, 36, 4, {fill:onS(c), op:0.6, r:2});
    K.poly(C, [[x + cw / 2 - 14, y0 + bh], [x + cw / 2 + 14, y0 + bh], [x + cw / 2, y0 + bh + 14]], {fill:c});
    var ly = y0 + bh + 34;
    if (s.l) C.text(x + 14, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:u.mix(c, INK, 0.5), align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(x + 14, ly + lh + 8, lw, Z.y + Z.h - ly - lh - 8, s.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 6 — segmented gauges: half-rings of ten ticks lit in the item colour */
function pctOf(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(5, Math.min(100, +m[1])) : [70, 55, 85, 60][k % 4]; }
function segGauges(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var grid = Z.w / n < 230 && n > 2, cols = grid ? 2 : n, rows = Math.ceil(n / cols), gy = 24;
  var cw = Z.w / cols, chh = (Z.h - gy * (rows - 1)) / rows;
  var R = Math.min(cw * 0.42, grid ? chh * 0.6 : Z.h * 0.5, 175), r0 = R * 0.66, lw = cw - 30;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), r0 * 1.6, r0 * 0.6, {font:'head', max:52, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 56, {font:'head', max:grid ? 17 : 21, min:12, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Math.max(20, chh - R - lh - 40), {font:'body', max:grid ? 14 : 16, min:11, lh:1.36});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.36}).h); });
  var tot = R + 22 + lh + (dh ? dh + 6 : 0), oy = Math.max(0, (chh - tot) / 2);
  st.forEach(function(s, k){
    var cx = Z.x + cw * (k % cols + 0.5), top = Z.y + Math.floor(k / cols) * (chh + gy) + oy, cy = top + R, c = sc(k), t = s.t, lit = Math.round(pctOf(s.v, k) / 10);
    for (var j = 0; j < 10; j++) K.seg(C, cx, cy, r0, R, 180 + j * 18 + 1.5, 180 + (j + 1) * 18 - 1.5, {fill:j < lit ? c : TRACK});
    C.text(cx - r0 * 0.85, cy - r0 * 0.62, r0 * 1.7, r0 * 0.62, s.v, {font:'head', size:vs, wt:800, color:u.mix(c, INK, 0.2), align:'c', va:'b', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    C.rect(cx - R, cy + 2, R * 2, 2, {fill:TRACK});
    var ly = cy + 20;
    if (s.l) C.text(cx - lw / 2, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:INK, align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(cx - lw / 2, ly + lh + 6, lw, dh + 4, s.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — rainbow wave: thick colour half-loops swing between milestones, text above / below */
function waveTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, ry = Math.min(92, Z.h * 0.2), base = Z.y + Z.h / 2, d = Math.min(62, cw * 0.36);
  C.line(Z.x, base, Z.x + cw / 2 - d / 2, base, {color:sc(0), lw:3}); C.line(Z.x + Z.w - cw / 2 + d / 2, base, Z.x + Z.w, base, {color:sc(n - 1), lw:3});
  for (var k = 0; k < n - 1; k++) halfEll(C, Z.x + cw * (k + 0.5), Z.x + cw * (k + 1.5), base, ry, k % 2 === 0, sc(k), 16);
  var tw = Math.min(cw * 1.7, 340), th = base - ry - 22 - Z.y;
  var F = fitS(C, it, tw - 10, 44, th - 8, {hMax:30, dMax:19, hLines:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), c = sc(k), up = k % 2 === 1;
    C.ell(x - d / 2, base - d / 2, d, d, {fill:'#FFFFFF', line:c, lw:5, shadow:0.1});
    C.text(x - d / 2, base - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.36), wt:800, color:c, align:'c', va:'m', lh:1, caps:false, ls:0});
    var bx = Math.max(Z.x, Math.min(Z.x + Z.w - tw, x - tw / 2)), bh = Math.min(th, blockH(C, t, tw - 10, F));
    var by = up ? base - ry - 22 - bh : base + ry + 22;
    C.line(x, up ? by + bh + 4 : base + d / 2 + 4, x, up ? base - d / 2 - 4 : by - 4, {color:c, lw:1.5, dash:true});
    block(C, bx + 5, by, tw - 10, bh + 4, t, F, {align:'c', tc:u.mix(c, INK, 0.25)});
  });
}

/* 8 — colour columns: tall tinted columns with solid heads, a dotted rail through them */
function bandColumns(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var gx = n > 4 ? 14 : 20, cw = (Z.w - gx * (n - 1)) / n, hb = Math.min(110, Z.h * 0.26), pad = cw < 180 ? 12 : 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - pad * 2, hb - 16, {font:'head', max:n <= 3 ? 32 : 27, min:13, maxLines:2});
  var ty = hb + 58, ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - pad * 2, Z.h - ty - pad, {font:'body', max:n <= 3 ? 21 : 18, min:11, lh:1.4});
  var dh = 0; it.forEach(function(t){ if (t.h) dh = Math.max(dh, dH(C, t, cw - pad * 2, ds)); });
  var ch = Math.min(Z.h, Math.max(Z.h * 0.78, ty + dh + 30)), oy = Z.y + (Z.h - ch) / 2, rail = oy + hb + 28;
  C.rect(Z.x, rail - 1.5, Z.w, 3, {fill:TRACK});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), c = sc(k);
    C.rect(x, oy, cw, ch, {grad:{ang:180, stops:[[0, soft(c, 0.82)], [100, soft(c, 0.97)]]}, r:14});
    C.rect(x, oy, cw, hb, {fill:c, r:14}); C.rect(x, oy + hb - 14, cw, 14, {fill:c});
    K.poly(C, [[x + cw / 2 - 12, oy + hb], [x + cw / 2 + 12, oy + hb], [x + cw / 2, oy + hb + 12]], {fill:c});
    C.text(x + pad, oy, cw - pad * 2, hb, t.h || t.d, {font:'head', size:hs, color:onS(c), align:'c', va:'m', lh:1.08, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    C.ell(x + cw / 2 - 9, rail - 9, 18, 18, {fill:'#FFFFFF', line:c, lw:4});
    if (t.h && t.d) C.text(x + pad, oy + ty, cw - pad * 2, ch - ty - 10, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
D.register({
  id:'spectrum', name:'Spectrum Infographic', tag:'Five-colour · infographic', desc:'Clean white canvas, halftone corners and a five-colour infographic system: arcs, funnels, cycles and hexagon paths.',
  fonts:{head:'Poppins', body:'Poppins', headW:700, bodyW:400, headLh:1.08, gf:'Poppins:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Colour arc',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Colour arc',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Colour bands',{body:colorBands}), V('n2','Segmented donut',{body:segDonut}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Split ring',{body:splitRing}), V('n2','Ribbon cards',{body:ribbonCards}), V('n3','Colour columns',{body:bandColumns}), V('v2','Image left + bands',{body:colorBands, img:'left'}), V('v3','Ribbons + image right',{body:ribbonCards, img:'right'}), V('n4','Two panels',{body:'duo'})],
    p3:[V('v1','Segmented donut',{body:segDonut}), V('n2','Ribbon cards',{body:ribbonCards}), V('n3','Funnel',{body:'xFunnel'}), V('n4','Colour columns',{body:bandColumns}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('v3','Image left + ribbons',{body:ribbonCards, img:'left'}), V('n5','Three cards',{body:'cols'})],
    p4:[V('v1','Ribbon cards',{body:ribbonCards}), V('n2','Segmented donut',{body:segDonut}), V('n3','Funnel',{body:'xFunnel'}), V('n4','Colour columns',{body:bandColumns}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('v3','Image left + ribbons',{body:ribbonCards, img:'left'}), V('n5','Card grid',{body:'grid'})],
    p56:[V('v1','Segmented donut',{body:segDonut}), V('n2','Colour bands',{body:colorBands}), V('n3','Ribbon cards',{body:ribbonCards}), V('n4','Funnel',{body:'xFunnel'}), V('v2','Image left + bands',{body:colorBands, img:'left'}), V('n5','Card grid',{body:'grid'})],
    p78:[V('v1','Colour bands',{body:colorBands}), V('n2','Segmented donut',{body:segDonut}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('n3','Card grid',{body:'grid'})],
    stats:[V('v1','Segment gauges',{body:segGauges}), V('n2','Spectrum strip',{body:spectrumStrip}), V('v2','Image left + bands',{body:colorBands, img:'left', stat:true}), V('v3','Gauges + image right',{body:segGauges, img:'right'}), V('n3','Stat cards',{body:'statCards', icons:true})],
    timeline:[V('v1','Rainbow wave',{body:waveTimeline}), V('n2','Colour columns',{body:bandColumns}), V('n3','Phases',{body:'xPhases'}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('v3','Image left + wave',{body:waveTimeline, img:'left'}), V('n4','Horizontal line',{body:'tlH'})],
    quote:[V('v1','Colour quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
