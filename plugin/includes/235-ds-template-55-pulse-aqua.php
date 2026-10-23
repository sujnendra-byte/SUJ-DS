<?php
/**
 * Code Snippets export — DS Template 55 — Pulse Aqua
 *
 * Snippet ID : 1028
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 03:43:04
 * Load order : 226 of 242
 */
/**
 * DS Template 55 — Pulse Aqua
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl55_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl55_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('pulseaqua', array(
        'name'     => "Pulse Aqua",
        'fonts'    => array('head' => "Onest", 'body' => "Onest"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0C2140', 'accent' => '#00B8D9', 'accent2' => '#FF6B5E'),
        'voice'    => "Fresh, friendly and encouraging. Short upbeat sentences, everyday words, benefits before features.",
        'recipe'   => "Open with the benefit for people, name the problem simply, show how the service works in a few easy steps, share results and numbers, close with a friendly call to action.",
        'generate' => 'ds_tpl55_generate',
        'build'    => 'ds_tpl55_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl55_generate') ) {
function ds_tpl55_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl55_build') ) {
function ds_tpl55_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-pulseaqua">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 55 - Pulse Aqua : bright aqua-to-blue gradient, halftone dots, pill bars, a soft stage for a cut-out picture */
var AQ = '#00B8D9', BL = '#0A6CCF', DK = '#062A63', CO = '#FF6B5E', ICE = '#E4F6FB', INK = '#0C2140', MUT = '#587089', LN = '#BFE6F1';
var G = {ang:135, stops:[[0, '#12C8DC'], [100, '#0A5FCB']]};
var GP = {ang:0, stops:[[0, '#12C8DC'], [100, '#0A6CCF']]};
var P0 = {bg:'#FFFFFF', text:INK, title:DK, muted:MUT, accent:AQ, accent2:CO, card:ICE, line:LN, onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[AQ, BL, CO, DK, '#8ADFEE', '#FFB3AB'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:24, fill:ICE, line:null, shadow:0, dark:false, tc:DK, mc:MUT,
        hl:{kind:'solid', fill:BL, dark:true, tc:'#FFFFFF', mc:'#D5ECFF'}},
  icon:{kind:'circle', bg:AQ, fg:'#FFFFFF'}, num:{kind:'big', fg:BL},
  conc:{kind:'bar', barC:CO, label:'In short', lc:CO, tc:DK},
  footer:{fn:foot}, imgFramed:true, imgR:36, bodyGap:26,
  imgPh:{grad:G}
};
function pb(C, x, y, w, h, o){ o = o || {}; C.rect(x, y, w, h, {fill:o.fill, grad:o.grad, op:o.op, line:o.line, lw:o.lw, r:h / 2}); }
/* stage: a tall soft panel that a cut-out person or object can stand in */
function stage(C, x, y, w, h, photo, light){
  K.halftone(C, x + w - 70, y - 40, 9, 9, 18, 5, light ? '#FFFFFF' : AQ, light ? 0.5 : 0.6);
  C.rect(x, y, w, h, {fill:'#FFFFFF', op:light ? 0.2 : 1, grad:light ? null : G, r:44});
  if (photo) D.imgBox(C, x + 14, y + 14, w - 28, h - 28, {r:32});
  else { pb(C, x + w * 0.2, y + h * 0.3, w * 0.6, 34, {fill:'#FFFFFF', op:0.9}); pb(C, x + w * 0.2, y + h * 0.3 + 52, w * 0.42, 34, {fill:'#FFFFFF', op:0.55}); pb(C, x + w * 0.2, y + h * 0.3 + 104, w * 0.52, 34, {fill:CO}); }
  pb(C, x - 40, y + h - 120, 150, 40, {fill:CO}); pb(C, x - 10, y + h - 68, 96, 26, {fill:light ? '#FFFFFF' : DK});
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  pb(C, x, y + 3, 30, 16, {fill:light ? '#FFFFFF' : AQ}); C.ell(x + 16, y + 5, 12, 12, {fill:CO});
  C.text(x + 42, y, 420, 22, f.company, {font:'head', size:14, wt:700, color:light ? '#FFFFFF' : DK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  pb(C, M, y + 3, 44, 10, {grad:GP}); pb(C, M + 50, y + 3, 10, 10, {fill:CO});
  C.text(M + 72, y, 500, 16, f.website || f.company || '', {font:'body', size:11.5, wt:500, color:MUT, lh:1, va:'m'});
  pb(C, W - M - 54, y - 5, 54, 26, {fill:ICE});
  C.text(W - M - 54, y - 5, 54, 26, pad2(C.i + 1), {font:'head', size:12.5, wt:700, color:BL, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  if (kind === 'cover' || kind === 'closing') { C.bg({fill:'#0A6CCF'}); C.rect(0, 0, W, H, {grad:G}); return; }
  C.bg({fill:'#FFFFFF'});
  var k = (C.i || 0) % 3;
  if (k === 0) K.halftone(C, W - 150, 20, 8, 5, 18, 5, AQ, 0.55);
  else if (k === 1) { pb(C, W - 190, 40, 230, 30, {grad:GP}); pb(C, W - 120, 82, 160, 18, {fill:CO}); }
  else { K.halftone(C, W - 110, 24, 6, 4, 18, 4.5, BL, 0.4); pb(C, W - 250, 46, 110, 20, {fill:ICE}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* pill bars: every point is one long pill with a gradient cap for its number */
function bars(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 14, rh = Math.min((Z.h - g * (n - 1)) / n, 108), cap = Math.max(rh * 1.25, 84), tw = Z.w - cap - 24 - rh * 0.5;
  var F = K.fitHD(C, it, tw, 30, rh - 18, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 14.5 : 19, hMin:12.5, dMin:10.5});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    pb(C, Z.x, y, Z.w, rh, {fill:ICE});
    pb(C, Z.x, y, cap, rh, k % 3 === 2 ? {fill:CO} : {grad:GP});
    C.text(Z.x, y, cap, rh, pad2(k + 1), {font:'head', size:Math.min(28, rh * 0.36), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, Z.x + cap + 24, y + Math.max(7, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:DK, mc:MUT});
  });
}
/* capsules: soft columns that sit at alternating heights, a pill label on top of each */
function capsules(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return bars(C, Z, o);
  var g = 20, off = Math.min(26, Z.h * 0.07), cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 64;
  var F = K.fitHD(C, it, tw, 56, Z.h - off - top - pad - 18, {hMax:20, dMax:15, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - off - 18, top + bmx + pad + 10), y0 = Z.y + 18 + Math.max(0, (Z.h - off - 18 - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = y0 + (k % 2 ? off : 0);
    C.rect(x, y, cw, ch, {fill:ICE, r:32});
    pb(C, x + pad, y - 18, 86, 36, k % 3 === 2 ? {fill:CO} : {grad:GP});
    C.text(x + pad, y - 18, 86, 36, 'No. ' + (k + 1), {font:'head', size:13, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad, y + top - 24, tw, ch - top, t, F, {tc:DK, mc:MUT});
  });
}
/* bubbles: white tiles with an aqua outline and two small pills in the corner */
function bubbles(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 22, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 26), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:LN, lw:1.8, r:22});
    pb(C, x + pad, y + pad - 2, 34, 10, {grad:GP}); pb(C, x + pad + 40, y + pad - 2, 10, 10, {fill:CO});
    K.hd(C, x + pad, y + pad + 22, tw, ch2 - pad * 2 - 22, t, F, {tc:DK, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: an upright tube that fills from the bottom, the number beside it */
function tubes(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, tb = Math.min(64, cw * 0.2), th = Math.min(Z.h - 10, 300), tw = cw - tb - 46, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, tw, 80, {font:'head', max:60, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 52, {font:'head', max:17.5, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, th - 150, {font:'body', max:14, min:10.5, lh:1.4});
  var y0 = Z.y + Math.max(0, (Z.h - th) * 0.3), fr = [0.72, 0.5, 0.86, 0.62];
  st.forEach(function(t, k){
    var x = Z.x + k * cw + 8, fh = Math.max(tb, th * fr[k % 4]);
    C.rect(x, y0, tb, th, {fill:ICE, r:tb / 2});
    C.rect(x, y0 + th - fh, tb, fh, k % 3 === 2 ? {fill:CO, r:tb / 2} : {grad:{ang:90, stops:[[0, '#12C8DC'], [100, '#0A6CCF']]}, r:tb / 2});
    C.ell(x + tb * 0.25, y0 + th - fh + tb * 0.25, tb * 0.5, tb * 0.5, {fill:'#FFFFFF', op:0.9});
    var tx = x + tb + 22;
    C.text(tx, y0 + 6, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:DK, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(tx, y0 + 6 + vs * 1.1 + 10, tw, 52, labs[k], {font:'head', size:ls, wt:700, color:BL, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(tx, y0 + 6 + vs * 1.1 + 20 + le._h, tw, th - (vs * 1.1 + 30 + le._h), t.x, {font:'body', size:ds, color:MUT, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: pills that step to the right like a plan chart */
function plan(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2) return bars(C, Z, o);
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 92), step = Math.min(70, Z.w * 0.28 / (n - 1)), bw = Z.w - step * (n - 1), cap = Math.min(150, bw * 0.24), tw = bw - cap - 20 - rh * 0.5;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cap - 24, rh - 12, {font:'head', max:24, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 14, {font:'body', max:16, min:10.5, lh:1.38});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * step, y = y0 + k * (rh + g);
    if (k) C.line(x - step + rh / 2, y - g - rh / 2 + rh / 2, x - step + rh / 2, y + rh / 2, {color:LN, lw:2});
    if (k) C.line(x - step + rh / 2, y + rh / 2, x, y + rh / 2, {color:LN, lw:2});
    pb(C, x, y, bw, rh, {fill:ICE});
    pb(C, x, y, cap, rh, k === n - 1 ? {fill:CO} : {grad:GP});
    C.text(x + 12, y, cap - 24, rh, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + cap + 20, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:G, r:36});
  K.halftone(C, Z.x + Z.w - 150, Z.y + Z.h - 100, 8, 5, 18, 5, '#FFFFFF', 0.35);
  var pad = 50, y = Z.y + pad, w = Z.w - pad * 2;
  pb(C, Z.x + pad, y, 60, 12, {fill:'#FFFFFF'}); pb(C, Z.x + pad + 68, y, 12, 12, {fill:CO}); y += 34;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:'#FFFFFF', lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:'#FFFFFF', lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  K.halftone(C, 30, 520, 12, 10, 20, 6, '#FFFFFF', 0.22);
  pb(C, -60, 96, 190, 22, {fill:'#FFFFFF', op:0.25});
  stage(C, 790, 110, 400, 660, photo, true);
  brand(C, 72, 56, true);
  C.text(72, 150, 660, 260, C.s.title, {font:'head', fit:{max:76, min:32}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:1.02});
  pb(C, 72, 430, 90, 10, {fill:CO});
  if (C.s.subtitle) C.text(72, 460, 620, 90, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:'#FFFFFF', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var ww = Math.min(620, who.length * 8.6 + 56); pb(C, 72, 600, ww, 44, {fill:'#FFFFFF'}); C.text(72 + 24, 600, ww - 48, 44, who, {font:'head', size:14.5, wt:700, color:BL, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  K.halftone(C, W - 250, 30, 12, 8, 20, 6, '#FFFFFF', 0.22);
  stage(C, 110, 110, 380, 660, photo, true);
  var x = 590, w = W - x - 72;
  C.text(x, 110, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  pb(C, x, 300, 90, 10, {fill:CO});
  if (C.s.subtitle) C.text(x, 328, w, 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#FFFFFF', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 420 + k * 62;
    pb(C, x, y, Math.min(w, 520), 48, {fill:'#FFFFFF'});
    pb(C, x + 5, y + 5, 54, 38, {grad:GP}); C.icon(x + 22, y + 14, 20, c.icon, '#FFFFFF');
    C.text(x + 76, y, Math.min(w, 520) - 100, 48, c.val, {font:'body', fit:{max:16.5, min:12}, wt:600, color:DK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(40, 40, 400, H - 80, {grad:G, r:44});
  K.halftone(C, 80, 500, 9, 6, 20, 6, '#FFFFFF', 0.3);
  pb(C, 300, 430, 200, 44, {fill:CO}); pb(C, 360, 486, 120, 28, {fill:DK});
  var te = C.text(86, 120, 320, 200, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1.02});
  if (C.s.subtitle) C.text(86, 120 + te._h + 16, 300, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FFFFFF', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  bars(C, {x:540, y:80, w:W - 540 - 72, h:H - 180}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:AQ, tc:DK}); return; }
  var photo = o.img === 'photo', x = 100, w = photo ? 660 : W - 200;
  if (photo) { C.rect(860, 110, 320, 480, {grad:G, r:44}); D.imgBox(C, 874, 124, 292, 452, {r:32}); }
  pb(C, x, 130, 96, 56, {grad:GP}); C.text(x, 142, 96, 56, '”', {font:'head', size:56, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  pb(C, x + 106, 148, 20, 20, {fill:CO});
  var qe = C.text(x, 222, w, 260, C.s.title, {font:'head', fit:{max:photo ? 34 : 44, min:18}, wt:700, color:DK, lh:1.2, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d, bw = Math.min(w, bt.length * 8.4 + 52); pb(C, x, 222 + qe._h + 26, bw, 40, {fill:ICE}); C.text(x + 22, 222 + qe._h + 26, bw - 44, 40, bt, {font:'head', size:13.5, wt:700, color:BL, va:'m', lh:1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'pulseaqua', name:'Pulse Aqua', tag:'Aqua gradient · pills · dots',
  desc:'A bright aqua-to-blue gradient with halftone dots, pill bars and a soft stage made for cut-out people and objects: fresh and friendly for healthcare, wellness, apps and services.',
  fonts:{head:'Onest', body:'Onest', headW:800, bodyW:400, headLh:1.08, gf:'Onest:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Gradient + stage', {}), V('v2', 'Gradient + photo stage', {img:'photo'})],
    closing:[V('v1', 'Stage + contact pills', {}), V('v2', 'Photo stage + contact pills', {img:'photo'})],
    agenda:[V('v1', 'Gradient panel + pill bars', {fn:agendaA}), V('n2', 'Pill bars', {body:bars}), V('v2', 'Pill bars + image right', {body:bars, img:'right'})],
    text:[V('v1', 'Gradient statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Capsules', {body:capsules}), V('n2', 'Pill bars', {body:bars}), V('n3', 'Bubbles', {body:bubbles}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:1})],
    p3:[V('v1', 'Capsules', {body:capsules}), V('n2', 'Pill bars', {body:bars}), V('n3', 'Bubbles', {body:bubbles}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:1})],
    p4:[V('v1', 'Pill bars', {body:bars}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Bubbles', {body:bubbles}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:2})],
    p56:[V('v1', 'Bubbles', {body:bubbles}), V('n2', 'Pill bars', {body:bars}), V('v2', 'Pill bars + image right', {body:bars, img:'right'}), V('v3', 'Image left + bubbles', {body:bubbles, img:'left', cols:2})],
    p78:[V('v1', 'Bubbles', {body:bubbles}), V('n2', 'Pill bars', {body:bars}), V('v2', 'Bubbles + image right', {body:bubbles, img:'right', cols:2})],
    stats:[V('v1', 'Tubes', {body:tubes}), V('n2', 'Progress rings', {body:'statRings', track:LN}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Tubes + image right', {body:tubes, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Plan chart', {body:plan}), V('n2', 'Pill bars', {body:bars}), V('n3', 'Capsules', {body:capsules}), V('v2', 'Plan chart + image right', {body:plan, img:'right'})],
    quote:[V('v1', 'Pill quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
</script>
<?php
}, 1065);
