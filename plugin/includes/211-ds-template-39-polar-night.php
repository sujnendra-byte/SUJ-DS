<?php
/**
 * Code Snippets export — DS Template 39 — Polar Night
 *
 * Snippet ID : 995
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:11:53
 * Load order : 202 of 218
 */
/**
 * DS Template 39 — Polar Night
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl39_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl39_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('polarnight', array(
        'name'     => "Polar Night",
        'fonts'    => array('head' => "Space Grotesk", 'body' => "IBM Plex Sans"),
        'pal'      => array('bg' => '#071226', 'text' => '#F2FAFF', 'accent' => '#5FE1FF', 'accent2' => '#7C9BFF'),
        'voice'    => "Sharp, ambitious voice: direct statements, strong verbs, one proof point per idea.",
        'recipe'   => "Opening, the landscape, the challenge, strategy, execution, milestones, outcomes, thank you.",
        'generate' => 'ds_tpl39_generate',
        'build'    => 'ds_tpl39_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl39_generate') ) {
function ds_tpl39_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl39_build') ) {
function ds_tpl39_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-polarnight">
/* DesignStager template: polarnight */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 39 - Polar Night : deep navy sky, ice-glass shards with a cut corner, cyan light */
var CY = '#5FE1FF', IC = '#BFEFFF', INK = '#F2FAFF', MUT = '#A9BDD6';
var P0 = {bg:'#071226', text:INK, title:INK, muted:MUT, accent:CY, accent2:'#7C9BFF', card:'#10213F', line:'#2A4066', onAccent:'#04101F'};
var S = {
  margin:74, series:[CY, '#7C9BFF', '#BFEFFF', '#3AA0FF', '#E6F7FF', '#4C6FBF'],
  header:{kind:'left', top:60, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return (C.cover.company || '') ; }, kc:CY, kGap:26, kSize:11.5},
  card:{kind:'glass', r:4, fill:'#FFFFFF', op:0.08, line:'#BFEFFF', lop:0.4, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:CY, dark:false, tc:'#04101F', mc:'#12324A'}},
  icon:{kind:'square', bg:CY, fg:'#04101F', r:4}, num:{kind:'big', fg:CY},
  conc:{kind:'bar', barC:CY, label:'Key point', lc:CY, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:4, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#1C3D6E'], [100, '#0B1B36']]}}
};
/* ice shard: a glass panel with the top-right corner cut off */
function shard(C, x, y, w, h, o){
  o = o || {}; var c = o.cut != null ? o.cut : Math.min(34, w * 0.18, h * 0.3), pts = [[x, y], [x + w - c, y], [x + w, y + c], [x + w, y + h], [x, y + h]];
  if (o.hot) { K.poly(C, pts, {fill:'#6FDCFF'}); K.poly(C, [[x, y], [x + w - c, y], [x + w, y + c], [x + w, y + h * 0.45], [x, y + h * 0.2]], {fill:'#FFFFFF', op:0.22}); return {tc:'#04101F', mc:'#12324A', ac:'#04101F'}; }
  K.poly(C, pts, {fill:'#FFFFFF', op:0.07}); K.poly(C, [[x, y], [x + w - c, y], [x + w, y + c], [x + w, y + h * 0.4], [x, y + h * 0.15]], {fill:'#FFFFFF', op:0.06}); for (var q = 0; q < 5; q++) { var a = pts[q], b = pts[(q + 1) % 5]; C.line(a[0], a[1], b[0], b[1], {color:'#BFEFFF', lw:1, op:0.42}); }
  C.rect(x, y, 3, h, {fill:CY, op:0.9});
  return {tc:INK, mc:MUT, ac:CY};
}
function foot(C){
  var M = 74, y = H - 36, f = C.cover;
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.08});
  K.poly(C, [[W - M - 54, y - 6], [W - M - 8, y - 6], [W - M, y + 2], [W - M, y + 20], [W - M - 54, y + 20]], {fill:CY});
  C.text(W - M - 54, y - 6, 54, 26, pad2(C.i + 1), {font:'head', size:12, wt:700, color:'#04101F', align:'c', va:'m', lh:1});
}
function peaks(C, y0, op){
  K.poly(C, [[0, H], [0, y0 + 90], [180, y0 + 10], [330, y0 + 100], [520, y0 - 30], [700, y0 + 110], [880, y0 + 30], [1060, y0 + 120], [1200, y0 + 40], [W, y0 + 100], [W, H]], {fill:'#16305A', op:op});
  K.poly(C, [[0, H], [0, y0 + 150], [240, y0 + 90], [430, y0 + 160], [640, y0 + 80], [830, y0 + 170], [1040, y0 + 110], [W, y0 + 170], [W, H]], {fill:'#0D2040', op:op});
}
function bg(C, kind){
  C.bg({grad:{ang:180, stops:[[0, '#050D1E'], [100, '#0B1F40']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.8, W * 0.15, W * 0.5][k], -H * 0.05, 900, 420, CY, 0.22);
  D.glow(C, [W * 0.2, W * 0.85, W * 0.9][k], H * 0.2, 600, 400, '#7C9BFF', 0.2);
  K.dots(C, [W - 200, 60, W - 320][k], [40, 50, 30][k], 6, 3, 26, 1.4, '#FFFFFF', 0.5);
  if (kind === 'cover' || kind === 'closing') return;
  peaks(C, H - 150, 0.75);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
/* shard cards with a big numeral behind the text */
function shards(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26, tw = cw - pad * 2 - 6;
  var top = rows === 1 ? 64 : 0, F = K.fitHD(C, it, tw, 52, ch - pad * 2 - top, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad * 2 + 8), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = shard(C, x, y, cw, ch2, {hot:hot});
    if (top) { C.text(x + pad, y + pad - 4, tw, 50, pad2(k + 1), {font:'head', size:40, wt:700, color:cc.ac, lh:1, caps:false, op:hot ? 0.9 : 1}); }
    else C.text(x + cw - 92, y + ch2 - 62, 80, 56, pad2(k + 1), {font:'head', size:50, wt:700, color:cc.ac, align:'r', lh:1, caps:false, op:0.16});
    K.hd(C, x + pad, y + pad + top, tw, ch2 - pad * 2 - top, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* beacon list: cyan hex beacons joined by a light beam, text to the right */
function beacons(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 130), hx = Math.min(46, rh - 18), tw = Z.w - hx - 26;
  var F = K.fitHD(C, it, tw, 34, rh - 20, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x + hx / 2 - 0.75, y0 + rh / 2, 1.5, rh * (n - 1), {fill:CY, op:0.4});
  it.forEach(function(t, k){
    var y = y0 + k * rh, cy = y + rh / 2, cx = Z.x + hx / 2, r = hx / 2;
    K.poly(C, [[cx, cy - r], [cx + r * 0.87, cy - r / 2], [cx + r * 0.87, cy + r / 2], [cx, cy + r], [cx - r * 0.87, cy + r / 2], [cx - r * 0.87, cy - r / 2]], {fill:CY}); C.ell(cx - r * 0.66, cy - r * 0.66, r * 1.32, r * 1.32, {fill:'#0B1F40'});
    C.icon(cx - hx * 0.22, cy - hx * 0.22, hx * 0.44, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), CY);
    var bh = blockH(C, t, tw, F);
    K.hd(C, Z.x + hx + 26, y + Math.max(8, (rh - bh) / 2), tw, rh - 8, t, F, {tc:INK, mc:MUT});
  });
}
/* split: one large hot shard on the left, the rest stacked on the right */
function lead(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return shards(C, Z, o);
  var g = 18, lw = Math.round(Z.w * 0.4), pad = 30, rest = it.slice(1), rn = rest.length, rw = Z.w - lw - g, rh = (Z.h - g * (rn - 1)) / rn;
  var c1 = shard(C, Z.x, Z.y, lw, Z.h, {hot:true, cut:50});
  C.icon(Z.x + pad, Z.y + pad, 40, D.iconFor(C.s, it[0].i, (it[0].h || '') + ' ' + (it[0].d || '')), c1.ac);
  var F1 = K.fitHD(C, [it[0]], lw - pad * 2, 80, Z.h - pad * 2 - 70, {hMax:28, dMax:18, hMin:15, dMin:11});
  K.hd(C, Z.x + pad, Z.y + pad + 62, lw - pad * 2, Z.h - pad * 2 - 62, it[0], F1, {tc:c1.tc, mc:c1.mc});
  var F = K.fitHD(C, rest, rw - 48, 30, rh - 24, {hMax:19, dMax:14.5, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var y = Z.y + k * (rh + g), cc = shard(C, Z.x + lw + g, y, rw, rh, {cut:22}), bh = blockH(C, t, rw - 48, F);
    K.hd(C, Z.x + lw + g + 26, y + Math.max(10, (rh - bh) / 2), rw - 48, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: summit peaks - a triangle per number, rising */
function summits(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 26, base = Z.y + Z.h * 0.6;
  var vs = C.groupFit(vals, tw, 70, {font:'head', max:56, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h * 0.4 - 70, {font:'body', max:13.5, min:10.5, lh:1.36});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), ph = (Z.h * 0.6 - vs - 26) * (0.5 + 0.5 * (n > 1 ? k / (n - 1) : 1)), pw = Math.min(cw * 0.8, 220);
    K.poly(C, [[cx - pw / 2, base], [cx, base - ph], [cx + pw / 2, base]], {fill:CY, op:0.3}); K.poly(C, [[cx, base - ph], [cx + pw / 2, base], [cx + pw * 0.08, base]], {fill:'#FFFFFF', op:0.12});
    K.poly(C, [[cx, base - ph], [cx + pw * 0.12, base - ph * 0.72], [cx, base - ph * 0.62], [cx - pw * 0.1, base - ph * 0.74]], {fill:'#FFFFFF', op:0.85});
    C.text(cx - tw / 2, base - ph - vs - 16, tw, vs + 8, vals[k], {font:'head', size:vs, wt:700, color:CY, align:'c', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + 16, tw, 46, labs[k], {font:'head', size:ls, wt:600, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + 22 + le._h, tw, Z.y + Z.h - base - 22 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
  C.rect(Z.x, base, Z.w, 1, {fill:IC, op:0.5});
}
/* timeline: ascent - steps climbing a ridge line */
function ascent(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return beacons(C, Z, o);
  var g = 14, cw = (Z.w - g * (n - 1)) / n, rise = Math.min(40, Z.h * 0.5 / n), ch = Z.h - rise * (n - 1), pad = 18, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 44, ch - pad * 2 - 40, {hMax:18, dMax:13.5, hMin:12, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + (n - 1 - k) * rise, cc = shard(C, x, y, cw, Z.y + Z.h - y, {cut:20, hot:k === n - 1});
    C.text(x + pad, y + pad - 2, tw, 26, 'STEP ' + (k + 1), {font:'head', size:11.5, wt:700, color:cc.ac, lh:1, ls:0.16});
    K.hd(C, x + pad, y + pad + 30, tw, ch - pad * 2 - 30, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  shard(C, Z.x, Z.y, Z.w, Z.h, {cut:56}); var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w - 30, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:CY, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; K.poly(C, [[x, y + 22], [x + 12, y], [x + 24, y + 22]], {fill:CY}); C.text(x + 36, y, 420, 22, f.company, {font:'head', size:15, wt:700, color:INK, va:'m', lh:1, ls:0.04, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  peaks(C, H - 250, 1);
  brand(C, 74, 54);
  if (photo) { D.imgBox(C, 720, 100, 486, 420, {r:4}); shard(C, 660, 440, 300, 110, {cut:26}); if (f.website) C.text(686, 440, 250, 110, f.website, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  else { C.ell(900, 90, 220, 220, {grad:{ang:135, stops:[[0, '#FFFFFF'], [100, '#9DF0FF']]}}); C.ell(860, 50, 300, 300, {fill:CY, op:0.12}); shard(C, 800, 330, 380, 130, {cut:30}); if (f.website) C.text(830, 330, 320, 130, f.website, {font:'body', size:15, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  var tw = photo ? 560 : 660;
  C.text(74, 170, tw, 230, C.s.title, {font:'head', fit:{max:photo ? 62 : 76, min:32}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.03, ls:-0.02});
  if (C.s.subtitle) C.text(74, 412, tw - 80, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:IC, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  /  ');
  if (who) { C.rect(74, 510, 3, 26, {fill:CY}); C.text(90, 510, tw, 26, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  peaks(C, H - 210, 1); brand(C, 74, 54);
  if (photo) D.imgBox(C, 760, 100, 446, 380, {r:4});
  var tw = photo ? 640 : W - 148;
  C.text(74, 120, tw, 170, C.s.title || 'Thank You', {font:'head', fit:{max:88, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(74, 304, Math.min(tw, 640), 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:IC, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var g = 14, cw = Math.min(340, (tw - g * (n - 1)) / n);
  cs.forEach(function(c, k){
    var x = 74 + k * (cw + g), y = 400;
    shard(C, x, y, cw, 76, {cut:18}); C.icon(x + 22, y + 27, 22, c.icon, CY);
    C.text(x + 58, y, cw - 78, 76, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(74, 120, 380, 220, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  C.rect(74, 120 + te._h + 16, 60, 3, {fill:CY});
  if (C.s.subtitle) C.text(74, 120 + te._h + 36, 360, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:IC, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  shard(C, 500, 70, W - 500 - 74, H - 190, {cut:50});
  beacons(C, {x:536, y:92, w:W - 536 - 110, h:H - 234}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CY, tc:INK}); return; }
  var photo = o.img === 'photo', x = 74, w = photo ? 640 : W - 148;
  if (photo) D.imgBox(C, 780, 100, 426, 480, {r:4});
  shard(C, x, 100, w, 420, {cut:60});
  C.text(x + 44, 110, 100, 90, '“', {font:'head', size:120, wt:700, color:CY, lh:1, caps:false, ls:0});
  var qe = C.text(x + 48, 204, w - 110, 230, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:500, color:INK, lh:1.25, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 48, 204 + qe._h + 22, w - 110, 24, bt, {font:'body', size:15, wt:700, color:CY, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'polarnight', name:'Polar Night', tag:'Dark · ice glass',
  desc:'A deep navy night sky over a mountain ridge, with cut-corner ice-glass shards and cyan light: sharp and ambitious for strategy, tech and expedition-style stories.',
  fonts:{head:'Space Grotesk', body:'IBM Plex Sans', headW:700, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Space+Grotesk:wght@500;600;700|IBM+Plex+Sans:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Moon over the ridge', {}), V('v2', 'Photo + ice tag', {img:'photo'})],
    closing:[V('v1', 'Ridge + ice contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Beacon list in ice', {fn:agendaA}), V('n2', 'Ice shards', {body:shards}), V('v2', 'Beacons + image right', {body:beacons, img:'right'})],
    text:[V('v1', 'Ice statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Ice shards', {body:shards, hl:true}), V('n2', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:1})],
    p3:[V('v1', 'Ice shards', {body:shards}), V('n2', 'Lead shard + stack', {body:lead}), V('n3', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:1})],
    p4:[V('v1', 'Lead shard + stack', {body:lead}), V('n2', 'Ice shards', {body:shards}), V('n3', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:2})],
    p56:[V('v1', 'Ice shards', {body:shards}), V('n2', 'Lead shard + stack', {body:lead}), V('n3', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'}), V('v3', 'Image left + shards', {body:shards, img:'left', cols:2})],
    p78:[V('v1', 'Beacon list', {body:beacons}), V('v2', 'Beacons + image right', {body:beacons, img:'right'})],
    stats:[V('v1', 'Summits', {body:summits}), V('n2', 'Progress rings', {body:'statRings', track:'#1B3358'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Summits + image right', {body:summits, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Ascent', {body:ascent}), V('n2', 'Beacon list', {body:beacons}), V('n3', 'Ice shards', {body:shards}), V('v2', 'Beacons + image right', {body:beacons, img:'right'})],
    quote:[V('v1', 'Ice quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1049);
