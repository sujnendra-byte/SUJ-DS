<?php
/**
 * Code Snippets export — DS Template 23 — Mono Portfolio
 *
 * Snippet ID : 922
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:38:30
 * Load order : 180 of 191
 */
/**
 * DS Template 23 — Mono Portfolio
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
    ds_tpl_register('mono', array(
        'name'     => "Mono Portfolio",
        'fonts'    => array('head' => "Syne", 'body' => "Inter"),
        'pal'      => array('bg' => '#0A0A0A', 'text' => '#E8E8E8', 'accent' => '#FFFFFF', 'accent2' => '#8C8C8C'),
        'voice'    => "Minimal editorial voice: few words, strong statements, portfolio and craft first.",
        'recipe'   => "Introduction, about me, selected work, approach, services, clients and results, contact.",
        'generate' => 'ds_tpl23_generate',
        'build'    => 'ds_tpl23_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl23_generate') ) {
function ds_tpl23_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl23_build') ) {
function ds_tpl23_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-mono">
/* Template 23 — Mono Portfolio : black editorial pages, oversized grey headlines, photo-led layouts, thin meta lines */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var P0 = {bg:'#0A0A0A', text:'#E8E8E8', title:'#D9D9D9', muted:'#9A9A9A', accent:'#FFFFFF', accent2:'#8C8C8C', card:'#151515', line:'#2C2C2C', onAccent:'#0A0A0A'};
var S = {
  margin:64, series:['#E8E8E8', '#9A9A9A', '#5C5C5C', '#C8C8C8', '#7A7A7A', '#B0B0B0'],
  header:{kind:'left', tMax:54, tMin:28, sMax:16, top:78, gap:14, tc:'#D9D9D9'},
  card:{kind:'rule', r:0, fill:'#151515', dark:true, tc:'#FFFFFF', mc:'#A5A5A5'},
  icon:{kind:'plain', bg:'#FFFFFF', fg:'#FFFFFF'},
  num:{kind:'big', fg:'#FFFFFF'},
  mark:'num',
  conc:{kind:'line', lineC:'#3A3A3A', lc:'#FFFFFF', label:'Note'},
  footer:{fn:function(C){ meta(C, true); }}, xRows:'#141414', hubFill:'#161616', road:'#2C2C2C', flowBar:'#1C1C1C',
  imgFramed:false, imgR:0, imgPct:0.46
};
function meta(C, bottom){
  var f = C.cover, fc = '#8C8C8C', o = C.o || {}, iw = Math.round(W * 0.46), x0 = 64, x1 = W - 64;
  if (C.kind !== 'cover' && C.kind !== 'closing') { if (o.img === 'left') x0 = iw + 48; if (o.img === 'right') x1 = W - iw - 48; }
  var mw = x1 - x0;
  if (o.img !== 'top' || C.kind === 'cover' || C.kind === 'closing') {
    C.text(x0, 26, mw / 2, 16, f.company || 'Creative Presentation', {font:'body', size:11, wt:700, color:fc, lh:1});
    C.text(x0 + mw / 2, 26, mw / 2, 16, f.date || u.pad2(C.i + 1), {font:'body', size:11, wt:700, color:fc, align:'r', lh:1});
  }
  if (bottom) {
    C.rect(x0, H - 44, mw, 1, {fill:'#2C2C2C'});
    var three = mw > 900;
    C.text(x0, H - 32, mw / 3, 16, f.website || '', {font:'body', size:11, color:fc, lh:1});
    if (three) C.text(x0 + mw / 3, H - 32, mw / 3, 16, f.email || f.presenter || '', {font:'body', size:11, color:fc, align:'c', lh:1});
    C.text(x1 - mw / 3, H - 32, mw / 3, 16, u.pad2(C.i + 1), {font:'body', size:11, wt:700, color:fc, align:'r', lh:1});
  }
}
function bg(C, kind){ C.bg({fill:'#0A0A0A'}); }
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  meta(C, true);
  if (o.img) { D.imgBox(C, W * 0.5, 70, W * 0.5 - 64, H - 150, {r:0}); }
  var w = o.img ? W * 0.5 - 100 : W - 128;
  C.text(56, 60, w, o.img ? 480 : 420, C.s.title, {font:'head', fit:{max:o.img ? 96 : 124, min:40}, color:'#D9D9D9', ed:{f:'title'}, maxLines:o.img ? 5 : 3, va:'t', lh:0.9, ls:-0.02});
  if (C.s.subtitle) C.text(64, H - 190, o.img ? w : 760, 110, C.s.subtitle, {font:'body', fit:{max:17, min:11}, color:'#BDBDBD', ed:{f:'subtitle'}, lh:1.5, maxLines:4});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  meta(C, true);
  if (o.img) D.imgBox(C, W * 0.55, 70, W * 0.45 - 64, H - 150, {r:0});
  C.text(56, 60, o.img ? W * 0.55 - 100 : W - 128, 440, C.s.title || 'Thank You.', {font:'head', fit:{max:132, min:44}, color:'#D9D9D9', ed:{f:'title'}, maxLines:2, va:'t', lh:0.88, ls:-0.02});
  if (C.s.subtitle) C.text(64, H - 190, 640, 60, C.s.subtitle, {font:'body', fit:{max:17, min:11}, color:'#BDBDBD', ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  D.contactList(C, o.img ? 64 : 780, o.img ? H - 120 : 480, 440, {vc:'#E8E8E8', gap:48, markO:{bg:'#2A2A2A'}});
}
/* photo mosaic agenda: title left, two photos, items list */
function agenda(C, v){
  var P = C.P;
  meta(C, true);
  C.text(56, 70, 600, 140, C.s.title || 'Table of Contents', {font:'head', fit:{max:64, min:32}, color:'#D9D9D9', ed:{f:'title'}, maxLines:2, lh:0.92});
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per;
    C.text(720 + c * 250, 80 + r * 30, 240, 26, '•  ' + (t.h || t.d), {font:'body', size:15, wt:700, color:'#E8E8E8', caps:true, ls:0.04, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
  D.imgBox(C, 64, 250, 560, H - 320, {r:0}); C.rect(640, 250, W - 704, H - 320, {fill:'#161616'});
  if (C.s.subtitle) C.text(670, 280, W - 764, H - 380, C.s.subtitle, {font:'body', fit:{max:20, min:12}, color:'#BDBDBD', ed:{f:'subtitle'}, lh:1.5, va:'b'});
}
function quote(C, v){
  var o = C.o || {};
  meta(C, true);
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#FFFFFF'}); return; }
  D.B.quote(C, {x:120, y:120, w:W - 240, h:H - 260}, {qc:'#FFFFFF'});
}
/* ================= signature bodies (mono motif: oversized grey type, thin rules, photo spreads) ================= */
var WH = '#F2F2F2', GR = '#A5A5A5', RULE = '#2C2C2C', GHOST = '#242424';
function fhd(C, it, w, hb, db, o){
  o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; var F = K.fitHD(C, it, w, hb, db, o), hh = 0;
  it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, w, {font:'head', max:F.hs, min:F.hs, lh:1.15, maxLines:o.hLines || 2}).h); });
  F.hh = hh; return F;
}
function blockH(C, t, F, w){ return (t.h ? F.hh + 8 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function hdm(C, x, y, w, h, t, F, o){ o = o || {}; o.tc = o.tc || WH; o.mc = o.mc || GR; return K.hd(C, x, y, w, h, t, F, o); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
function metaTxt(C, x, y, w, s, o){ o = o || {}; C.text(x, y, w, 16, s, {font:'body', size:11, wt:700, color:o.c || '#8C8C8C', align:o.align || 'l', ls:0.12, caps:true, lh:1}); }
/* 1. index rows: ghost numerals behind each row, thin rules */
function indexRows(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Z.h / per;
  var compact = rh < 76, ns = compact ? 17 : Math.min(rh * 0.95, 130), nw = ns * 0.9, wide = cw > 780;
  var hx = compact ? 46 : nw * 0.55, hw = wide ? (cw - hx) * 0.38 : cw - hx - 10, dx = wide ? hx + hw + 30 : hx, dw = wide ? cw - dx : hw;
  var F = wide ? {hs:C.groupFit(it.map(function(t){ return t.h; }), hw, rh - 16, {font:'head', max:28, min:13, maxLines:2}), hh:0, ds:C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 16, {font:'body', max:19, min:11, lh:1.36})}
               : fhd(C, it, hw, Math.min(40, rh * 0.36), rh - 14, {hMax:22, dMax:16.5, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh;
    C.rect(x, y, cw, 1, {fill:RULE});
    if (r === per - 1 || k === n - 1) C.rect(x, y + rh, cw, 1, {fill:RULE});
    C.text(x - (compact ? 0 : 4), y, compact ? 40 : nw * 1.4, rh, u.pad2(k + 1), {font:'head', size:ns, color:compact ? '#6E6E6E' : GHOST, va:'m', lh:1, caps:false, ls:-0.02, maxLines:1});
    metaTxt(C, x + cw - 120, y + 8, 120, 'N° ' + u.pad2(k + 1), {align:'r'});
    if (wide) {
      if (t.h) C.text(x + hx, y, hw, rh, t.h, {font:'head', size:F.hs, color:WH, va:'m', lh:1.05, maxLines:2, ed:{f:'x-part', i:t.i, p:0}});
      C.text(x + dx, y + 8, dw - 130 > 200 ? dw - 130 : dw, rh - 16, t.d, {font:'body', size:F.ds, color:GR, va:'m', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      var bhh = blockH(C, t, F, hw);
      hdm(C, x + hx, y + (rh - bhh) / 2 + 2, hw, bhh + 4, t, F, {hLines:1});
    }
  });
}
/* 2. ghost columns: giant numerals with the heading laid over their foot */
function ghostColumns(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var gap = 36, cw = (Z.w - gap * (n - 1)) / n, ns = Math.min(cw * 0.62, 190, Z.h * 0.42), iw = cw - 8;
  var F = fhd(C, it, iw, 70, Z.h - ns * 0.8 - 40, {hMax:26, dMax:18});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    if (k) C.rect(x - gap / 2, Z.y, 1, Z.h, {fill:RULE});
    metaTxt(C, x, Z.y, iw, '(' + u.pad2(k + 1) + ')  ' + (k === 0 ? '—  Index' : ''));
    C.text(x - 6, Z.y + 16, cw + 40, ns, u.pad2(k + 1), {font:'head', size:ns, color:k === 0 ? '#3A3A3A' : GHOST, lh:0.9, caps:false, ls:-0.03, maxLines:1});
    var hy = Z.y + 16 + ns * 0.78;
    hdm(C, x, hy, iw, Z.y + Z.h - hy, t, F);
  });
}
/* 3. film strip: one photo sliced into frames, captions under each */
function filmStrip(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var gap = 12, bh = Math.round(Z.h * 0.5), cw = (Z.w - gap * (n - 1)) / n;
  D.imgBox(C, Z.x, Z.y, Z.w, bh, {r:0});
  for (var k = 1; k < n; k++) C.rect(Z.x + k * (cw + gap) - gap, Z.y - 1, gap, bh + 2, {fill:'#0A0A0A'});
  var ty = Z.y + bh + 22, iw = cw - 6;
  var F = fhd(C, it, iw, 56, Z.y + Z.h - ty - 30, {hMax:22, dMax:16});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    C.rect(x, Z.y + bh - 30, 54, 30, {fill:'#0A0A0A'});
    C.text(x, Z.y + bh - 30, 54, 30, u.pad2(k + 1), {font:'body', size:13, wt:700, color:WH, align:'c', va:'m', lh:1});
    metaTxt(C, x, ty, iw, 'Frame ' + u.pad2(k + 1));
    hdm(C, x, ty + 24, iw, Z.y + Z.h - ty - 24, t, F);
  });
}
/* 4. two giant grey statements split by a hairline */
function bigSplit(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = Z.w > 900 ? 80 : 48, cw = (Z.w - gap * (n - 1)) / n;
  var hs = C.groupFit(it.map(function(t){ return t.h; }), cw, Z.h * 0.45, {font:'head', max:Z.w > 900 ? 84 : 52, min:22, maxLines:3});
  var hh = 0; it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, cw, {font:'head', max:hs, min:hs, maxLines:3}).h); });
  var dBox = Z.h - hh - 110;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw, dBox, {font:'body', max:24, min:12, lh:1.45});
  var dh = 0; it.forEach(function(t){ dh = Math.max(dh, C.measureText(t.d, cw, {font:'body', max:ds, min:ds, lh:1.45}).h); });
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    if (k) C.rect(x - gap / 2, Z.y, 1, Z.h, {fill:RULE});
    C.rect(x, Z.y, cw, 1, {fill:'#3A3A3A'});
    metaTxt(C, x, Z.y + 12, cw, '(' + u.pad2(k + 1) + ')');
    metaTxt(C, x, Z.y + 12, cw, k ? 'Answer' : 'Question', {align:'r'});
    if (t.h) C.text(x, Z.y + 44, cw, hh + 4, t.h, {font:'head', size:hs, color:k ? '#D9D9D9' : '#7A7A7A', lh:0.95, maxLines:3, ed:{f:'x-part', i:t.i, p:0}});
    C.rect(x, Z.y + Z.h - dh - 22, 40, 2, {fill:k ? '#FFFFFF' : '#7A7A7A'});
    C.text(x, Z.y + Z.h - dh - 4, cw, dh + 4, t.d, {font:'body', size:ds, color:'#BDBDBD', lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 5. mega stat: one oversized figure, the rest in a ruled column */
function megaStat(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  function big(t, x, w, y, h){
    var vs = C.measureText(t.v, w, {font:'head', max:Math.min(200, h * 0.6), min:30, maxLines:1}).s;
    C.text(x - 4, y, w + 8, vs * 1.02, t.v, {font:'head', size:vs, color:'#D9D9D9', lh:0.95, maxLines:1, caps:false, ls:-0.03, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(x, y + vs + 16, w, 1, {fill:'#3A3A3A'});
    var le = C.text(x, y + vs + 34, w, 70, t.l, {font:'head', fit:{max:28, min:14}, color:WH, lh:1.05, maxLines:2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x, y + vs + 34 + le._h + 12, w, 50, t.x, {font:'body', fit:{max:17, min:11}, color:GR, lh:1.4, maxLines:2});
  }
  if (n <= 2) { var cw = (Z.w - 80 * (n - 1)) / n; st.forEach(function(t, k){ if (k) C.rect(Z.x + k * (cw + 80) - 40, Z.y, 1, Z.h, {fill:RULE}); metaTxt(C, Z.x + k * (cw + 80), Z.y, cw, '(' + u.pad2(k + 1) + ')'); big(t, Z.x + k * (cw + 80), cw, Z.y + 26, Z.h - 26); }); return; }
  var lw = Z.w * 0.52, rx = Z.x + lw + 60, rw = Z.x + Z.w - rx, rest = st.slice(1), rh = Z.h / rest.length;
  metaTxt(C, Z.x, Z.y, lw, '(01)  —  Headline figure');
  big(st[0], Z.x, lw, Z.y + 30, Z.h - 30);
  C.rect(rx - 30, Z.y, 1, Z.h, {fill:RULE});
  var vs = C.groupFit(rest.map(function(t){ return t.v; }), rw * 0.5, rh * 0.5, {font:'head', max:60, min:22, maxLines:1});
  var ls = C.groupFit(rest.map(function(t){ return t.l; }), rw * 0.46, rh * 0.5, {font:'body', wt:600, max:17, min:11, maxLines:3});
  rest.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.rect(rx, y, rw, 1, {fill:RULE});
    metaTxt(C, rx, y + 10, rw, '(' + u.pad2(k + 2) + ')');
    C.text(rx, y + 26, rw * 0.52, rh - 34, t.v, {font:'head', size:vs, color:'#E8E8E8', va:'m', lh:1, maxLines:1, caps:false, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.text(rx + rw * 0.54, y + 26, rw * 0.46, rh - 34, t.l + (t.x ? '' : ''), {font:'body', size:ls, wt:600, color:GR, va:'m', lh:1.35, maxLines:3, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
  });
}
/* 6. ledger: ruled table rows, value flush right */
function statLedger(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var rh = Z.h / n, vw = Math.min(Z.w * 0.42, 420), lw = Z.w - vw - 80;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), vw, rh * 0.8, {font:'head', max:96, min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), lw, rh * 0.42, {font:'head', max:26, min:13, maxLines:2, lh:1.05});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), lw, rh * 0.36, {font:'body', max:16, min:11, maxLines:2});
  st.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.rect(Z.x, y, Z.w, 1, {fill:RULE}); if (k === n - 1) C.rect(Z.x, y + rh, Z.w, 1, {fill:RULE});
    C.text(Z.x, y, 60, rh, '(' + u.pad2(k + 1) + ')', {font:'body', size:13, wt:700, color:'#8C8C8C', va:'m', lh:1});
    var lh2 = C.measureText(t.l, lw, {font:'head', max:ls, min:ls, maxLines:2, lh:1.05}).h, xh = t.x ? C.measureText(t.x, lw, {font:'body', max:xs, min:xs}).h + 6 : 0, by = y + (rh - lh2 - xh) / 2;
    C.text(Z.x + 70, by, lw, lh2 + 2, t.l, {font:'head', size:ls, color:WH, lh:1.05, maxLines:2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(Z.x + 70, by + lh2 + 6, lw, xh, t.x, {font:'body', size:xs, color:GR, lh:1.35});
    C.text(Z.x + Z.w - vw, y, vw, rh, t.v, {font:'head', size:vs, color:k === 0 ? '#FFFFFF' : '#BDBDBD', align:'r', va:'m', lh:1, maxLines:1, caps:false, ls:-0.02, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
  });
}
/* 7. editorial timeline: oversized years standing on a hairline */
function tlEditorial(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, iw = cw - 22;
  var ys = C.groupFit(it.map(function(t){ return t.h || u.pad2(1); }), iw, 160, {font:'head', max:Math.min(150, cw * 0.4), min:20, maxLines:1});
  var F = fhd(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), iw, 1, Z.h - ys - 70, {dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var top = Z.y + Math.max(0, (Z.h - (ys + 60 + th)) / 2), ry = top + ys + 18;
  C.rect(Z.x, ry, Z.w, 1, {fill:'#4A4A4A'});
  var shades = ['#6E6E6E', '#808080', '#939393', '#A8A8A8', '#C4C4C4', '#E0E0E0'];
  it.forEach(function(t, k){
    var x = Z.x + k * cw, sh = shades[Math.max(0, 6 - n + k)], last = k === n - 1;
    C.text(x, top, iw, ys * 1.05, t.h || u.pad2(k + 1), {font:'head', size:ys, color:last ? '#FFFFFF' : sh, lh:1, va:'b', maxLines:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.ell(x - 1, ry - 4, 9, 9, {fill:last ? '#FFFFFF' : '#0A0A0A', line:'#FFFFFF', lw:1.5});
    metaTxt(C, x + 16, ry + 12, iw - 16, u.pad2(k + 1) + ' / ' + u.pad2(n));
    C.text(x, ry + 38, iw, Z.y + Z.h - ry - 38, t.d, {font:'body', size:F.ds, color:last ? '#E8E8E8' : GR, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 8. contents index: numbered ruled entries */
function agendaIndex(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 60, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(96, Z.h / per);
  var y0 = Z.y + (Z.h - rh * per) / 2, nw = Math.min(90, rh * 1.1);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - nw - 60, rh * 0.62, {font:'head', max:n <= 5 ? 34 : 26, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    C.rect(x, y + rh, cw, 1, {fill:RULE}); if (r === 0) C.rect(x, y, cw, 1, {fill:RULE});
    C.text(x, y, nw, rh, u.pad2(k + 1), {font:'head', size:Math.min(rh * 0.6, 44), color:'#5C5C5C', va:'m', lh:1, caps:false, ls:-0.02});
    C.text(x + nw, y, cw - nw - 50, rh, t.h || t.d, {font:'head', size:hs, color:WH, va:'m', lh:1.05, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    C.text(x + cw - 40, y, 40, rh, '→', {font:'body', size:18, color:'#8C8C8C', align:'r', va:'m', lh:1});
  });
}
/* 9. ruled grid: hairline cross-grid with ghost numerals */
function gridRules(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : (n <= 6 ? 3 : 4), rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 22, iw = cw - pad * 2;
  var F = fhd(C, it, iw, 50, ch - pad * 2 - 30, {hMax:21, dMax:15.5});
  for (var c = 1; c < cols; c++) C.rect(Z.x + c * cw, Z.y, 1, Z.h, {fill:RULE});
  for (var r = 0; r <= rows; r++) C.rect(Z.x, Z.y + r * ch, Z.w, 1, {fill:r === 0 || r === rows ? '#3A3A3A' : RULE});
  var gs = Math.min(ch * 0.7, cw * 0.5, 140);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * ch;
    C.text(x + cw - gs * 1.25 - 6, y + ch - gs - 2, gs * 1.25, gs, u.pad2(k + 1), {font:'head', size:gs, color:'#1C1C1C', align:'r', lh:1, caps:false, ls:-0.03, maxLines:1});
    metaTxt(C, x + pad, y + pad - 4, iw, 'N° ' + u.pad2(k + 1));
    hdm(C, x + pad, y + pad + 26, iw, ch - pad * 2 - 26, t, F);
  });
}
D.register({
  id:'mono', name:'Mono Portfolio', tag:'Black editorial · portfolio', desc:'Black editorial pages with oversized grey headlines, full-bleed photo layouts and thin meta lines — for portfolios and creative profiles.',
  fonts:{head:'Syne', body:'Inter', headW:800, bodyW:400, headLh:0.95, headCaps:true, headLs:-0.01, gf:'Syne:wght@600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Giant title',{}), V('v2','Title + photo right',{img:true})],
    closing:[V('v1','Giant thank you',{}), V('v2','Thank you + photo',{img:true})],
    agenda:[V('v1','Photo + contents',{}), V('n2','Contents index',{body:agendaIndex}), V('v2','Index + photo right',{body:agendaIndex, img:'right', cols:1})],
    text:[V('v2','Statement + photo right',{body:'text', img:'right'}), V('v1','Statement',{body:'text'}), V('v3','Photo left + statement',{body:'text', img:'left'}), V('v4','Photo top + statement',{body:'text', img:'top'})],
    p2:[V('v1','Big split',{body:bigSplit}), V('n2','Index rows',{body:indexRows}), V('v2','Film strip',{body:filmStrip}), V('v3','Photo left + split',{body:bigSplit, img:'left'}), V('v4','Photo right + index',{body:indexRows, img:'right'})],
    p3:[V('v1','Ghost columns',{body:ghostColumns}), V('n2','Index rows',{body:indexRows}), V('v2','Film strip',{body:filmStrip}), V('v3','Ghost columns + photo top',{body:ghostColumns, img:'top'}), V('v4','Photo left + index',{body:indexRows, img:'left'})],
    p4:[V('v1','Index rows',{body:indexRows}), V('n2','Ghost columns',{body:ghostColumns}), V('n3','Ruled grid',{body:gridRules}), V('v2','Film strip',{body:filmStrip}), V('v3','Photo left + index',{body:indexRows, img:'left'})],
    p56:[V('v1','Index rows',{body:indexRows}), V('n2','Ruled grid',{body:gridRules}), V('v2','Photo right + index',{body:indexRows, img:'right', cols:1})],
    p78:[V('v1','Ruled grid',{body:gridRules}), V('n2','Index rows',{body:indexRows, cols:2}), V('v2','Photo right + index',{body:indexRows, img:'right', cols:1})],
    stats:[V('v1','Mega figure',{body:megaStat}), V('n2','Ledger',{body:statLedger}), V('v2','Photo right + ledger',{body:statLedger, img:'right'}), V('v3','Photo left + ledger',{body:statLedger, img:'left'})],
    timeline:[V('v1','Oversized years',{body:tlEditorial}), V('n2','Index rows',{body:indexRows}), V('v2','Photo right + index',{body:indexRows, img:'right', cols:1})],
    quote:[V('v1','Mono quote',{}), V('v2','Quote + photo right',{img:'right'}), V('v3','Photo left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
