<?php
/**
 * Code Snippets export — DS Template 52 — Care Blue
 *
 * Snippet ID : 1025
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 03:43:03
 * Load order : 223 of 242
 */
/**
 * DS Template 52 — Care Blue
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl52_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl52_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('careblue', array(
        'name'     => "Care Blue",
        'fonts'    => array('head' => "Figtree", 'body' => "Figtree"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0F1B33', 'accent' => '#1F6FEB', 'accent2' => '#0B2A5B'),
        'voice'    => "Calm, clear and caring, like a trusted clinician explaining things. Simple words, exact facts, no alarm and no jargon without a short explanation.",
        'recipe'   => "Open with the health topic and who it affects, explain the problem, describe the care or solution step by step, give outcomes and figures, close with what to do next and who to contact.",
        'generate' => 'ds_tpl52_generate',
        'build'    => 'ds_tpl52_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl52_generate') ) {
function ds_tpl52_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl52_build') ) {
function ds_tpl52_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-careblue">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 52 - Care Blue : white page, navy panels, plus signs, round photos with an offset ring, a heartbeat line */
var NV = '#0B2A5B', BL = '#1F6FEB', SKY = '#E6F0FF', INK = '#0F1B33', MUT = '#5A6B88', LN = '#C9D9F2';
var P0 = {bg:'#FFFFFF', text:INK, title:NV, muted:MUT, accent:BL, accent2:NV, card:SKY, line:LN, onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[BL, NV, '#7FB0FF', '#12B5A6', '#9AA9C4', '#0F1B33'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:14, fill:SKY, line:null, shadow:0, dark:false, tc:NV, mc:MUT,
        hl:{kind:'solid', fill:NV, dark:true, tc:'#FFFFFF', mc:'#C9D9F2'}},
  icon:{kind:'circle', bg:BL, fg:'#FFFFFF'}, num:{kind:'big', fg:BL},
  conc:{kind:'bar', barC:BL, label:'In short', lc:BL, tc:NV},
  footer:{fn:foot}, imgFramed:true, imgR:24, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#BBD4FF'], [100, '#1F6FEB']]}}
};
function plus(C, cx, cy, s, color, op){ var t = s * 0.3; C.rect(cx - s / 2, cy - t / 2, s, t, {fill:color, op:op, r:t * 0.2}); C.rect(cx - t / 2, cy - s / 2, t, s, {fill:color, op:op, r:t * 0.2}); }
/* heartbeat: a flat line with one pulse at px */
function beat(C, x0, x1, y, px, a, color, lw, op){
  var o = {color:color, lw:lw || 2, op:op == null ? 1 : op}, p = [[x0, y], [px - a * 1.1, y], [px - a * 0.7, y - a * 0.35], [px - a * 0.35, y + a * 0.3], [px, y - a], [px + a * 0.4, y + a * 0.7], [px + a * 0.75, y - a * 0.25], [px + a * 1.1, y], [x1, y]];
  for (var k = 0; k < p.length - 1; k++) C.line(p[k][0], p[k][1], p[k + 1][0], p[k + 1][1], o);
}
/* round photo with an offset ring and a plus badge */
function round(C, cx, cy, r, photo, dark){
  C.ell(cx - r - 16, cy - r + 14, r * 2, r * 2, {line:dark ? '#FFFFFF' : BL, lop:dark ? 0.5 : 1, lw:2.5});
  C.ell(cx - r - 9, cy - r - 9, r * 2 + 18, r * 2 + 18, {fill:'#FFFFFF'});
  if (photo) D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'});
  else { C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:140, stops:[[0, '#BBD4FF'], [100, '#1F6FEB']]}}); plus(C, cx, cy, r * 0.8, '#FFFFFF', 0.95); }
  var b = r * 0.2, bx = cx + r * 0.72, by = cy + r * 0.72;
  C.rect(bx - b - 5, by - b - 5, b * 2 + 10, b * 2 + 10, {fill:'#FFFFFF', r:b * 0.5 + 4}); C.rect(bx - b, by - b, b * 2, b * 2, {fill:NV, r:b * 0.5}); plus(C, bx, by, b * 1.1, '#FFFFFF');
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  plus(C, x + 11, y + 11, 22, light ? '#FFFFFF' : BL);
  C.text(x + 32, y, 420, 22, f.company, {font:'head', size:13, wt:700, color:light ? '#FFFFFF' : NV, va:'m', lh:1, ls:0.04, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  plus(C, M + 7, y + 8, 14, BL);
  C.text(M + 24, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1) + ' / ' + pad2(C.total || 1), {font:'body', size:11, wt:600, color:NV, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  C.rect(0, 62, 10, 58, {fill:BL});
  if (k === 0) { plus(C, W - 96, 70, 30, BL); plus(C, W - 58, 106, 16, NV); }
  else if (k === 1) beat(C, W - 250, W, 78, W - 140, 22, BL, 2.2);
  else { plus(C, W - 70, 66, 22, NV); plus(C, W - 108, 66, 22, BL, 0.55); plus(C, W - 146, 66, 22, BL, 0.25); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* quadrants: no boxes, thin divider lines that cross, a plus badge on every crossing */
function quads(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, n > 3 ? 2 : 1);
  var rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 26, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 54, ch - pad * 2 - 40, {hMax:21, dMax:15.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 46), tot = ch2 * rows, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3), c, r;
  for (c = 1; c < cols; c++) C.line(Z.x + c * cw, y0 + 6, Z.x + c * cw, y0 + tot - 6, {color:LN, lw:1.6});
  for (r = 1; r < rows; r++) C.line(Z.x + 6, y0 + r * ch2, Z.x + Z.w - 6, y0 + r * ch2, {color:LN, lw:1.6});
  for (c = 1; c < cols; c++) for (r = rows > 1 ? 1 : 0; r < (rows > 1 ? rows : 1); r++) { var px = Z.x + c * cw, py = rows > 1 ? y0 + r * ch2 : y0 + tot / 2; C.rect(px - 17, py - 17, 34, 34, {fill:'#FFFFFF'}); plus(C, px, py, 24, BL); }
  it.forEach(function(t, k){
    var rr = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * cw + pad, y = y0 + rr * ch2 + pad;
    C.text(x, y, 80, 30, pad2(k + 1), {font:'head', size:26, wt:800, color:BL, lh:1, caps:false});
    K.hd(C, x, y + 40, tw, ch2 - pad * 2 - 40, t, F, {tc:NV, mc:MUT});
  });
}
/* record: rows like a patient chart - navy header strip, striped rows, a number column */
function record(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), hd = 38, rh = Math.min((Z.h - hd) / n, 96), nw = 64, hw = hasH ? Math.min(300, Z.w * 0.3) : 0, pad = 18, tw = Z.w - nw - hw - pad * 2;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw - 14, rh - 14, {font:'head', max:17.5, min:11.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:hasH ? 15 : 19, min:10.5, lh:1.38});
  var tot = hd + rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x, y0, Z.w, hd, {fill:NV, r:10});
  C.text(Z.x, y0, nw, hd, 'No.', {font:'head', size:11.5, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:true, ls:0.1});
  if (hasH) C.text(Z.x + nw, y0, hw, hd, 'Topic', {font:'head', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.1});
  C.text(Z.x + nw + hw + (hasH ? pad : 0), y0, tw, hd, 'Details', {font:'head', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.1});
  it.forEach(function(t, k){
    var y = y0 + hd + k * rh;
    if (k % 2 === 0) C.rect(Z.x, y, Z.w, rh, {fill:SKY});
    C.line(Z.x, y + rh, Z.x + Z.w, y + rh, {color:LN, lw:1});
    C.text(Z.x, y, nw, rh, pad2(k + 1), {font:'head', size:Math.min(20, rh * 0.36), wt:800, color:BL, align:'c', va:'m', lh:1, caps:false});
    if (hasH) C.text(Z.x + nw, y, hw - 14, rh, t.h || '', {font:'head', size:hs, wt:700, color:NV, va:'m', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + (hasH ? pad : 0), y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* wards: outlined cards, a plus badge sits on the top edge of each */
function wards(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, top = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - top - (g + top) * (rows - 1)) / rows, pad = 18, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad - 30, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad + 40), tot = (ch2 + top) * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + top + r * (ch2 + top + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:LN, lw:1.6, r:14});
    C.rect(x + pad - 4, y - 16, 32, 32, {fill:k % 2 ? NV : BL, r:9}); plus(C, x + pad + 12, y, 16, '#FFFFFF');
    K.hd(C, x + pad, y + 28, tw, ch2 - pad - 28, t, F, {tc:NV, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* vitals: one heartbeat line crosses the slide, each number sits over its own pulse */
function vitals(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, tw = cw - 36, vals = st.map(sv), labs = st.map(sl), ly = Z.y + Math.min(Z.h * 0.52, 230);
  var vs = C.groupFit(vals, tw, ly - Z.y - 70, {font:'head', max:68, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.y + Z.h - ly - 110, {font:'body', max:14, min:10.5, lh:1.4});
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    beat(C, x, x + cw, ly, x + cw * 0.5, Math.min(38, cw * 0.14), k % 2 ? NV : BL, 2.6);
    C.text(x + 18, Z.y, tw, ly - Z.y - 50, vals[k], {font:'head', size:vs, wt:800, color:NV, align:'c', va:'b', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x + 18, ly + 52, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:BL, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 18, ly + 60 + le._h, tw, Z.y + Z.h - (ly + 60 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* course: a line with plus stops; steps sit above and below it in turn */
function course(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return record(C, Z, o);
  var cw = Z.w / n, tw = cw - 24, my = Z.y + Z.h / 2, half = Z.h / 2 - 34;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 30, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, half - 40, {font:'body', max:14.5, min:10.5, lh:1.4});
  C.line(Z.x, my, Z.x + Z.w, my, {color:NV, lw:2.4});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), x = cx - tw / 2, up = k % 2 === 0;
    C.rect(cx - 18, my - 18, 36, 36, {fill:up ? BL : NV, r:10}); plus(C, cx, my, 18, '#FFFFFF');
    C.line(cx, up ? my - 18 : my + 18, cx, up ? my - 34 : my + 34, {color:LN, lw:1.6});
    if (up) {
      var dh = C.measureText(t.d, tw, {font:'body', max:ds, min:ds, lh:1.4}).h, ty = my - 42 - dh;
      C.text(x, ty - 34, tw, 30, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:BL, align:'c', va:'b', lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, ty, tw, dh + 4, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      C.text(x, my + 42, tw, 30, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:NV, align:'c', lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, my + 42 + hs * 1.3 + 4, tw, half - 40, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    }
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:SKY, r:18}); C.rect(Z.x, Z.y, 12, Z.h, {fill:BL, r:6});
  plus(C, Z.x + Z.w - 60, Z.y + Z.h - 60, 54, BL, 0.16);
  var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:BL, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, 690, H, {fill:NV}); C.rect(690, 0, 12, H, {fill:BL});
  beat(C, 0, 690, 610, 470, 30, '#FFFFFF', 2, 0.22);
  plus(C, 610, 96, 44, BL); plus(C, 560, 140, 20, '#FFFFFF', 0.5);
  round(C, 930, 350, 215, photo, false);
  plus(C, 1180, 110, 34, BL); plus(C, 1140, 150, 16, NV); plus(C, 760, 620, 26, BL, 0.4);
  brand(C, 72, 60, true);
  C.text(72, 150, 540, 250, C.s.title, {font:'head', fit:{max:66, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  C.rect(72, 420, 84, 6, {fill:BL, r:3});
  if (C.s.subtitle) C.text(72, 446, 520, 90, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#C9D9F2', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) C.text(72, 640, 560, 24, who, {font:'head', size:14, wt:700, color:'#FFFFFF', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(590, 0, W - 590, H, {fill:NV}); C.rect(578, 0, 12, H, {fill:BL});
  round(C, 300, 350, 205, photo, false);
  plus(C, 80, 96, 34, BL); plus(C, 124, 132, 16, NV); plus(C, 500, 630, 26, BL, 0.4);
  beat(C, 590, W, 640, 1040, 30, '#FFFFFF', 2, 0.22);
  var x = 670, w = W - 670 - 72;
  var te = C.text(x, 110, w, 150, C.s.title || 'Thank You', {font:'head', fit:{max:70, min:32}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  C.rect(x, 280, 84, 6, {fill:BL, r:3});
  if (C.s.subtitle) C.text(x, 304, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:'#C9D9F2', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 410 + k * 56;
    C.ell(x, y, 40, 40, {fill:BL}); C.icon(x + 10, y + 10, 20, c.icon, '#FFFFFF');
    C.text(x + 56, y, w - 56, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 440, H, {fill:NV}); C.rect(440, 0, 10, H, {fill:BL});
  plus(C, 330, 560, 150, '#FFFFFF', 0.07); plus(C, 110, 600, 40, BL); beat(C, 0, 440, 470, 250, 26, '#FFFFFF', 2, 0.25);
  var te = C.text(72, 130, 320, 200, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1.02});
  if (C.s.subtitle) C.text(72, 130 + te._h + 16, 310, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#C9D9F2', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  record(C, {x:520, y:90, w:W - 520 - 72, h:H - 190}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BL, tc:NV}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 640 : W - 220;
  if (photo) round(C, 1010, 350, 180, true, false);
  plus(C, x + 22, 150, 44, BL);
  C.rect(x, 200, 6, 300, {fill:LN});
  var qe = C.text(x + 34, 200, w - 34, 280, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:700, color:NV, lh:1.22, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 34, 200 + qe._h + 24, w - 34, 24, bt, {font:'head', size:14, wt:700, color:BL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
  beat(C, x, x + w, 610, x + w * 0.7, 26, BL, 2, 0.5);
}
D.register({
  id:'careblue', name:'Care Blue', tag:'White · navy · plus signs',
  desc:'Clean white pages with navy panels, plus signs, a heartbeat line and round photos: built for healthcare, clinics, pharma, wellness and medical reports.',
  fonts:{head:'Figtree', body:'Figtree', headW:800, bodyW:400, headLh:1.06, gf:'Figtree:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Navy panel + round emblem', {}), V('v2', 'Navy panel + round photo', {img:'photo'})],
    closing:[V('v1', 'Round emblem + contacts', {}), V('v2', 'Round photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Navy panel + record', {fn:agendaA}), V('n2', 'Record rows', {body:record}), V('v2', 'Record + image right', {body:record, img:'right'})],
    text:[V('v1', 'Statement card', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Divided columns', {body:quads}), V('n2', 'Record rows', {body:record}), V('n3', 'Plus cards', {body:wards}), V('v2', 'Record + image right', {body:record, img:'right'}), V('v3', 'Image left + columns', {body:quads, img:'left', cols:1})],
    p3:[V('v1', 'Divided columns', {body:quads}), V('n2', 'Plus cards', {body:wards}), V('n3', 'Record rows', {body:record}), V('v2', 'Record + image right', {body:record, img:'right'}), V('v3', 'Image left + cards', {body:wards, img:'left', cols:1})],
    p4:[V('v1', 'Quadrants', {body:quads}), V('n2', 'Record rows', {body:record}), V('n3', 'Plus cards', {body:wards}), V('v2', 'Record + image right', {body:record, img:'right'}), V('v3', 'Image left + quadrants', {body:quads, img:'left', cols:2})],
    p56:[V('v1', 'Plus cards', {body:wards}), V('n2', 'Divided grid', {body:quads}), V('n3', 'Record rows', {body:record}), V('v2', 'Record + image right', {body:record, img:'right'})],
    p78:[V('v1', 'Plus cards', {body:wards}), V('n2', 'Record rows', {body:record}), V('v2', 'Cards + image right', {body:wards, img:'right', cols:2})],
    stats:[V('v1', 'Vitals', {body:vitals}), V('n2', 'Progress rings', {body:'statRings', track:LN}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Vitals + image right', {body:vitals, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Course line', {body:course}), V('n2', 'Record rows', {body:record}), V('n3', 'Plus cards', {body:wards}), V('v2', 'Record + image right', {body:record, img:'right'})],
    quote:[V('v1', 'Plain quote', {}), V('v2', 'Quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
</script>
<?php
}, 1062);
