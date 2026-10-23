<?php
/**
 * Code Snippets export — DS Template 31 — Opal Glass
 *
 * Snippet ID : 987
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 12:40:00
 * Load order : 194 of 218
 */
/**
 * DS Template 31 — Opal Glass
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl31_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl31_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('opalglass', array(
        'name'     => "Opal Glass",
        'fonts'    => array('head' => "Sora", 'body' => "DM Sans"),
        'pal'      => array('bg' => '#F4F3FB', 'text' => '#1F1B4D', 'accent' => '#6C4CF1', 'accent2' => '#12B5A4'),
        'voice'    => "Friendly, clear project voice: plain words, short sentences, concrete outcomes and next steps.",
        'recipe'   => "Overview, background, the problem, goals, plan, progress with numbers, results, next steps.",
        'generate' => 'ds_tpl31_generate',
        'build'    => 'ds_tpl31_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl31_generate') ) {
function ds_tpl31_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl31_build') ) {
function ds_tpl31_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-opalglass">
/* DesignStager template: opalglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 31 - Opal Glass : light pastel page, frosted white glass cards, glossy gradient orbs */
var INK = '#1F1B4D', BODY = '#4A4873', VIO = '#6C4CF1', TEAL = '#12B5A4', PINK = '#FF7AA8', SKY = '#4FA8FF';
var ORB = [[ '#B9A6FF', '#5B3DF0'], ['#8FF0E2', '#0FA89A'], ['#FFC2D6', '#F2548B'], ['#B5DBFF', '#2F86F0'], ['#FFE0A8', '#F59E2A']];
var P0 = {bg:'#F4F3FB', text:INK, title:INK, muted:BODY, accent:VIO, accent2:TEAL, card:'#FFFFFF', line:'#D9D5F0', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[VIO, TEAL, PINK, SKY, '#F59E2A', '#8B7CF6'],
  header:{kind:'left', top:70, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return pad2(C.i + 1) + ' / ' + pad2(C.total || C.i + 1); }, kPill:true, kPillFill:'#FFFFFF', kPillOp:0.7, kc:VIO, kGap:42, kSize:11.5},
  card:{kind:'glass', r:22, fill:'#FFFFFF', op:0.62, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.07, dark:false, tc:INK, mc:BODY,
        hl:{kind:'grad', grad:{ang:135, stops:[[0, '#7B5CF5'], [100, '#4C2FD9']]}, dark:true, tc:'#FFFFFF', mc:'#E4DEFF'}},
  icon:{kind:'soft', bg:VIO, fg:VIO, op:0.14}, num:{kind:'circle', bg:VIO, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#FFFFFF', op:0.75, line:'#FFFFFF', lw:1.5, label:'Takeaway', lc:VIO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:26, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#CFC4FF'], [100, '#9FE9DF']]}}
};
function orb(C, cx, cy, r, k, op){
  var c = ORB[((k % ORB.length) + ORB.length) % ORB.length];
  D.glow(C, cx + r * 0.15, cy + r * 0.75, r * 1.1, r * 0.5, c[1], 0.28 * (op == null ? 1 : op));
  C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:140, stops:[[0, c[0]], [100, c[1]]]}, op:op});
  C.ell(cx - r * 0.62, cy - r * 0.78, r * 0.9, r * 0.52, {fill:'#FFFFFF', op:0.42 * (op == null ? 1 : op)});
  C.ell(cx + r * 0.2, cy + r * 0.42, r * 0.5, r * 0.3, {fill:'#FFFFFF', op:0.14 * (op == null ? 1 : op)});
}
function ringOrb(C, cx, cy, r, k){ var c = ORB[k % ORB.length]; C.ell(cx - r, cy - r, r * 2, r * 2, {line:c[1], lw:Math.max(3, r * 0.16), lop:0.55}); C.ell(cx - r, cy - r, r * 2, r * 2, {line:'#FFFFFF', lw:1.5, lop:0.9}); }
function glass(C, x, y, w, h, o){
  o = o || {};
  if (o.vio) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#7B5CF5'], [100, '#4C2FD9']]}, r:o.r || 22, shadow:0.14}); C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.22], [50, '#FFFFFF', 0]]}, r:o.r || 22}); return {tc:'#FFFFFF', mc:'#E4DEFF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.9], [100, '#FFFFFF', 0.48]]}, line:'#FFFFFF', lop:1, lw:1.5, r:o.r || 22, shadow:0.07});
  return {tc:INK, mc:BODY, ac:VIO};
}
function foot(C){
  var M = 72, y = H - 40, f = C.cover;
  if (f.company) { orb(C, M + 8, y + 9, 8, C.i); C.text(M + 26, y, 420, 18, f.company, {font:'body', size:12, wt:700, color:INK, lh:1, va:'m'}); }
  C.rect(W - M - 62, y - 4, 62, 26, {fill:'#FFFFFF', op:0.75, line:'#FFFFFF', lw:1.2, r:13});
  C.text(W - M - 62, y - 4, 62, 26, pad2(C.i + 1), {font:'body', size:12, wt:700, color:VIO, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#F4F3FB'});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.92, W * 0.08, W * 0.6][k], H * 0.05, 480, 330, '#C9B8FF', 0.75);
  D.glow(C, [W * 0.05, W * 0.95, W * 0.1][k], H * 0.98, 460, 320, '#A8F0E0', 0.7);
  D.glow(C, [W * 0.55, W * 0.4, W * 0.95][k], H * 0.72, 360, 240, '#FFD3C2', 0.55);
  D.glow(C, [W * 0.3, W * 0.7, W * 0.35][k], H * 0.2, 300, 200, '#B9DCFF', 0.5);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') { orb(C, W - 96, 70, 26, C.i + 1); ringOrb(C, W - 150, 104, 14, C.i + 2); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function numOrb(C, cx, cy, r, n, k){ orb(C, cx, cy, r, k); C.text(cx - r, cy - r, r * 2, r * 2, pad2(n), {font:'head', size:Math.round(r * 0.72), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0}); }

/* glass cards with a numbered orb sitting on the top-left corner */
function orbCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)), rows, gx = 22, gy = 34;
  if (Z.w < 760 && cols > 2) cols = 2;
  rows = Math.ceil(n / cols);
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - 20 - gy * (rows - 1)) / rows, pad = 24, top = 40;
  var F = K.fitHD(C, it, cw - pad * 2, 54, ch - top - pad, {hMax:19, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2, F)); });
  var ch2 = Math.min(ch, top + bmx + pad + 6), tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + 20 + Math.max(0, (Z.h - 20 - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = y0 + r * (ch2 + gy), cc = glass(C, x, y, cw, ch2, {vio:o.hl && k === 0});
    numOrb(C, x + 34, y + 2, 24, k + 1, k);
    K.hd(C, x + pad, y + top, cw - pad * 2, ch2 - top - pad, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* stacked glass rows, gently staggered, orb icon on the left */
function glassRows(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 24, gy = 14, cw = (Z.w - gx * (cols - 1)) / cols, rh = (Z.h - gy * (per - 1)) / per, st = cols === 1 && Z.w > 700 ? 26 : 0;
  var rw = cw - st, tw = rw - 96 - 24;
  var F = K.fitHD(C, it, tw, 28, rh - 22, {hMax:19, dMax:15.5, hMin:12.5, dMin:10.5, noBoost:n > 3, hLines:1});
  var rh2 = Math.min(rh, 108 + (n <= 3 ? 30 : 0)), tot = rh2 * per + gy * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx) + (r % 2 ? st : 0), y = y0 + r * (rh2 + gy), cc = glass(C, x, y, rw, rh2, {r:Math.min(22, rh2 / 2)});
    var orr = Math.min(22, rh2 / 2 - 12);
    orb(C, x + 26 + orr, y + rh2 / 2, orr, k);
    C.icon(x + 26 + orr - orr * 0.55, y + rh2 / 2 - orr * 0.55, orr * 1.1, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), '#FFFFFF');
    var bh = blockH(C, t, tw, F), oy = Math.max(8, (rh2 - bh) / 2);
    K.hd(C, x + 96, y + oy, tw, rh2 - oy - 6, t, F, {tc:cc.tc, mc:cc.mc, hLines:1});
  });
}
/* bento: one tall violet glass card + smaller white glass cards */
function bento(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 700) return orbCards(C, Z, o);
  var gap = 20, lw = Math.round(Z.w * 0.36), rest = it.slice(1), m = rest.length, cols = m <= 2 ? 1 : 2, rows = Math.ceil(m / cols), rw = (Z.w - lw - gap - gap * (cols - 1)) / cols, rh = (Z.h - gap * (rows - 1)) / rows, pad = 26;
  var c0 = glass(C, Z.x, Z.y, lw, Z.h, {vio:true});
  orb(C, Z.x + lw - 54, Z.y + Z.h - 54, 64, 1, 0.9); ringOrb(C, Z.x + lw - 130, Z.y + Z.h - 34, 20, 3);
  C.text(Z.x + pad, Z.y + pad, 80, 26, '01', {font:'head', size:20, wt:700, color:'#FFFFFF', lh:1, caps:false, ls:0});
  var FL = K.fitHD(C, [it[0]], lw - pad * 2, 80, Z.h - pad * 2 - 150, {hMax:26, dMax:17, hMin:15, dMin:11, noBoost:true});
  K.hd(C, Z.x + pad, Z.y + pad + 40, lw - pad * 2, Z.h - pad * 2 - 150, it[0], FL, {tc:c0.tc, mc:c0.mc});
  var F = K.fitHD(C, rest, rw - pad * 2, 50, rh - pad * 2 - 30, {hMax:18, dMax:15, hMin:12.5, dMin:10.5, noBoost:true});
  rest.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, w2 = (cols === 2 && k === m - 1 && m % 2) ? rw * 2 + gap : rw, x = Z.x + lw + gap + c * (rw + gap), y = Z.y + r * (rh + gap), cc = glass(C, x, y, w2, rh, {});
    C.text(x + pad, y + pad - 4, 60, 22, pad2(k + 2), {font:'head', size:16, wt:700, color:ORB[(k + 1) % ORB.length][1], lh:1, caps:false, ls:0});
    C.rect(x + pad + 34, y + pad + 6, w2 - pad * 2 - 34, 1.2, {fill:'#D9D5F0'});
    K.hd(C, x + pad, y + pad + 26, w2 - pad * 2, rh - pad * 2 - 26, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: value inside a glossy orb, label on a glass plate under it */
function orbStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var r = Math.min(cw * 0.3, ch * 0.3, 96), vals = st.map(sv), labs = st.map(sl), pad = 20;
  var vs = C.groupFit(vals, r * 1.5, r * 0.8, {font:'head', max:44, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - pad * 2, 48, {font:'head', max:18, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - pad * 2, ch - r * 1.2 - 96, {font:'body', max:14.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var rr = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + rr * (ch + gy), cx = x + cw / 2;
    glass(C, x, y + r, cw, ch - r, {});
    orb(C, cx, y + r, r, k);
    C.text(cx - r * 0.8, y + r * 0.55, r * 1.6, r * 0.9, vals[k], {font:'head', size:vs, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y + r * 2 + 18, lh = C.measureText(labs[k], cw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + pad, ly, cw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + lh + 8, cw - pad * 2, y + ch - pad - ly - lh - 8, t.x, {font:'body', size:ds, color:BODY, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: orb beads on a line, glass cards alternating above and below */
function beads(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / n, cw = Math.min(slot * 1.7 - 16, 330), my = Z.y + Z.h / 2, ch = Z.h / 2 - 40, pad = 18;
  if (n <= 3) cw = slot - 20;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2, 30, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - hs * 1.2 - 8, {font:'body', max:15.5, min:10.5, lh:1.38});
  C.rect(Z.x, my - 2, Z.w, 4, {fill:'#FFFFFF', r:2});
  C.rect(Z.x, my - 2, Z.w, 4, {grad:{ang:90, stops:[[0, VIO, 0.5], [100, TEAL, 0.5]]}, r:2});
  it.forEach(function(t, k){
    var cx = Z.x + slot * (k + 0.5), up = n <= 3 ? false : k % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw / 2));
    var dh = C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(ch, pad * 2 + hs * 1.2 + 8 + dh + 4), y = up ? my - 34 - hh : my + 34;
    var cc = glass(C, x, y, cw, hh, {r:18});
    orb(C, cx, my, 15, k);
    C.text(x + pad, y + pad, cw - pad * 2, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:ORB[k % ORB.length][1], lh:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, y + pad + hs * 1.2 + 8, cw - pad * 2, hh - pad * 2 - hs * 1.2 - 8, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2 - (o.img ? 0 : 150);
  if (!o.img) { orb(C, Z.x + Z.w - 90, Z.y + Z.h - 80, 78, C.i); orb(C, Z.x + Z.w - 190, Z.y + Z.h - 40, 30, C.i + 2); ringOrb(C, Z.x + Z.w - 60, Z.y + Z.h - 190, 22, C.i + 1); }
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:VIO, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cluster(C, cx, cy, s){
  orb(C, cx, cy, 150 * s, 0); orb(C, cx + 150 * s, cy - 130 * s, 70 * s, 1); orb(C, cx - 150 * s, cy + 120 * s, 54 * s, 2); orb(C, cx + 120 * s, cy + 150 * s, 36 * s, 3);
  ringOrb(C, cx - 120 * s, cy - 150 * s, 44 * s, 4); ringOrb(C, cx + 215 * s, cy + 40 * s, 22 * s, 2);
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; orb(C, x + 12, y + 12, 12, 0); C.text(x + 34, y, 420, 24, f.company, {font:'head', size:17, wt:700, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 52);
  if (f.date) C.text(W - 372, 52, 300, 24, f.date, {font:'body', size:13, wt:600, color:BODY, align:'r', va:'m', lh:1});
  if (photo) { D.imgBox(C, 720, 110, 488, 500, {r:36}); orb(C, 730, 580, 62, 1); orb(C, 1190, 130, 34, 2); ringOrb(C, 800, 640, 20, 0); }
  else cluster(C, 960, 370, 1);
  glass(C, 60, 170, 640, 380, {r:30});
  C.text(100, 200, 560, 210, C.s.title, {font:'head', fit:{max:66, min:30}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'m', lh:1.05});
  if (C.s.subtitle) C.text(100, 420, 560, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { C.rect(100, 500, 3, 26, {fill:VIO, r:2}); C.text(114, 500, 540, 26, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 52);
  if (photo) { D.imgBox(C, 720, 110, 488, 500, {r:36}); orb(C, 1196, 596, 54, 3); ringOrb(C, 742, 128, 22, 1); }
  else cluster(C, 980, 360, 0.92);
  glass(C, 60, 130, 640, 250, {r:30});
  C.text(100, 150, 560, 130, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(100, 290, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 410 + k * 66;
    glass(C, 60, y, 640, 54, {r:27});
    orb(C, 60 + 30, y + 27, 18, k); C.icon(60 + 30 - 10, y + 17, 20, c.icon, '#FFFFFF');
    C.text(60 + 66, y, 640 - 90, 54, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(72, 150, 430, 230, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.03});
  if (C.s.subtitle) C.text(72, 150 + te._h + 18, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  orb(C, 150, 590, 70, C.i); orb(C, 250, 640, 30, C.i + 1); ringOrb(C, 300, 560, 20, C.i + 2);
  glassRows(C, {x:540, y:70, w:W - 540 - 72, h:H - 150}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:VIO, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 60 : 130, w = photo ? 640 : W - 260;
  if (photo) { D.imgBox(C, 730, 100, 480, 520, {r:36}); orb(C, 740, 600, 44, 2); }
  glass(C, x, 130, w, H - 260, {r:30});
  orb(C, x + 70, 130, 40, 0); C.text(x + 30, 100, 80, 80, '”', {font:'head', size:70, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 50, 210, w - 100, H - 260 - 190, C.s.title, {font:'head', fit:{max:photo ? 34 : 40, min:18}, wt:600, color:INK, lh:1.28, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x + 50, 210 + qe._h + 24, 3, 24, {fill:VIO, r:2}); C.text(x + 64, 210 + qe._h + 24, w - 120, 24, bt, {font:'body', size:15, wt:600, color:BODY, va:'m', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'opalglass', name:'Opal Glass', tag:'Light · frosted glass',
  desc:'Soft pastel pages with frosted white glass cards and glossy gradient orbs: a light, friendly glass look for projects, products and reports.',
  fonts:{head:'Sora', body:'DM Sans', headW:700, bodyW:400, headLh:1.1, gf:'Sora:wght@400;600;700;800|DM Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Glass panel + orbs', {}), V('v2', 'Glass panel + photo', {img:'photo'})],
    closing:[V('v1', 'Orbs + contact pills', {}), V('v2', 'Photo + contact pills', {img:'photo'})],
    agenda:[V('v1', 'Title + glass rows', {fn:agendaA}), V('n2', 'Orb cards', {body:orbCards}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1, max:7})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Orb cards', {body:orbCards, hl:true}), V('n2', 'Glass rows', {body:glassRows}), V('n3', 'Two panels + VS', {body:'duo', vs:true, hl:'first'}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + orb cards', {body:orbCards, img:'left', cols:1})],
    p3:[V('v1', 'Bento', {body:bento}), V('n2', 'Orb cards', {body:orbCards}), V('n3', 'Glass rows', {body:glassRows}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + glass rows', {body:glassRows, img:'left', cols:1})],
    p4:[V('v1', 'Orb cards', {body:orbCards}), V('n2', 'Bento', {body:bento}), V('n3', 'Glass rows', {body:glassRows}), V('n4', '2 × 2 orb cards', {body:orbCards, cols:2}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + orb cards', {body:orbCards, img:'left', cols:2})],
    p56:[V('v1', 'Orb cards', {body:orbCards}), V('n2', 'Bento', {body:bento}), V('n3', 'Glass rows', {body:glassRows}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1}), V('v3', 'Image left + glass rows', {body:glassRows, img:'left', cols:1})],
    p78:[V('v1', 'Orb cards', {body:orbCards}), V('n2', 'Glass rows', {body:glassRows, max:8}), V('n3', 'Glass grid', {body:'grid'}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1, max:8})],
    stats:[V('v1', 'Orb numbers', {body:orbStats}), V('n2', 'Progress rings', {body:'statRings', track:'#E3DFF7'}), V('n3', 'Stat cards', {body:'statCards', icons:true}), V('v2', 'Orb numbers + image right', {body:orbStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Orb beads', {body:beads}), V('n2', 'Milestone cards', {body:'tlCards'}), V('n3', 'Glass rows', {body:glassRows}), V('v2', 'Glass rows + image right', {body:glassRows, img:'right', cols:1})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1041);
