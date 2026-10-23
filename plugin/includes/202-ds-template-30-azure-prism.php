<?php
/**
 * Code Snippets export — DS Template 30 — Azure Prism
 *
 * Snippet ID : 986
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 12:37:18
 * Load order : 193 of 218
 */
/**
 * DS Template 30 — Azure Prism
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl30_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl30_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('azureprism', array(
        'name'     => "Azure Prism",
        'fonts'    => array('head' => "Plus Jakarta Sans", 'body' => "Inter"),
        'pal'      => array('bg' => '#0B5BC2', 'text' => '#FFFFFF', 'accent' => '#FFFFFF', 'accent2' => '#4DB4FF'),
        'voice'    => "Corporate proposal voice: confident, structured and client-focused. Lead with outcomes and back every claim with a number.",
        'recipe'   => "Overview, the client need, our approach, team, track record, plan and timeline, investment, next steps.",
        'generate' => 'ds_tpl30_generate',
        'build'    => 'ds_tpl30_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl30_generate') ) {
function ds_tpl30_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl30_build') ) {
function ds_tpl30_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-azureprism">
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

</script>
<?php
}, 1040);
