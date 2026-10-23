<?php
/**
 * Code Snippets export — DS Template 42 — Citrus Pop
 *
 * Snippet ID : 998
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:16:10
 * Load order : 205 of 218
 */
/**
 * DS Template 42 — Citrus Pop
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl42_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl42_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('citruspop', array(
        'name'     => "Citrus Pop",
        'fonts'    => array('head' => "Bricolage Grotesque", 'body' => "Work Sans"),
        'pal'      => array('bg' => '#FFD60A', 'text' => '#111111', 'accent' => '#111111', 'accent2' => '#FF5A1F'),
        'voice'    => "Loud, playful voice: short punchy lines, bold claims, everyday words.",
        'recipe'   => "Hook, the big idea, why now, how it works, proof, the plan, call to action, thank you.",
        'generate' => 'ds_tpl42_generate',
        'build'    => 'ds_tpl42_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl42_generate') ) {
function ds_tpl42_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl42_build') ) {
function ds_tpl42_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-citruspop">
/* DesignStager template: citruspop */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 42 - Citrus Pop : lemon yellow page, black ink blocks with hard offset shadows, bold grotesque type */
var YE = '#FFD60A', BK = '#111111', WH = '#FFFFFF', OR = '#FF5A1F', MUT = '#3A3A3A';
var P0 = {bg:YE, text:BK, title:BK, muted:MUT, accent:BK, accent2:OR, card:WH, line:BK, onAccent:YE};
var S = {
  margin:70, series:[BK, OR, '#FFFFFF', '#7A5CFF', '#8A8A8A', '#FFB300'],
  header:{kind:'left', top:58, tMax:50, tMin:26, sMax:17, sMin:13, kicker:function(C){ return '#' + pad2(C.i + 1); }, kc:BK, kGap:26, kSize:13},
  card:{kind:'solid', r:0, fill:WH, line:BK, lw:2.5, shadow:0, dark:false, tc:BK, mc:MUT,
        hl:{kind:'solid', fill:BK, dark:true, tc:WH, mc:'#DADADA'}},
  icon:{kind:'square', bg:BK, fg:YE, r:0}, num:{kind:'big', fg:BK},
  conc:{kind:'bar', barC:BK, label:'So what', lc:BK, tc:BK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#2A2A2A'], [100, '#111111']]}}
};
/* sticker: a block with a hard offset shadow */
function stk(C, x, y, w, h, o){
  o = o || {}; var d = o.d != null ? o.d : 8, mode = o.mode || 'w';
  C.rect(x + d, y + d, w, h, {fill:mode === 'k' ? OR : BK});
  C.rect(x, y, w, h, {fill:mode === 'k' ? BK : (mode === 'o' ? OR : WH), line:BK, lw:2.5});
  return mode === 'k' ? {tc:WH, mc:'#DADADA', ac:YE} : (mode === 'o' ? {tc:WH, mc:'#FFE9DF', ac:BK} : {tc:BK, mc:MUT, ac:OR});
}
function foot(C){
  var M = 70, y = H - 38, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 2.5, {fill:BK});
  C.text(M, y, 600, 18, f.company || f.website || '', {font:'head', size:12, wt:800, color:BK, lh:1, va:'m', caps:true, ls:0.06});
  C.text(W - M - 120, y, 120, 18, pad2(C.i + 1) + '/' + pad2(C.total), {font:'head', size:12, wt:800, color:BK, align:'r', va:'m', lh:1});
}
function burst(C, cx, cy, r, color){
  var p = []; for (var k = 0; k < 24; k++) { var a = k * Math.PI / 12, rr = k % 2 ? r * 0.78 : r; p.push([cx + Math.cos(a) * rr, cy + Math.sin(a) * rr]); }
  K.poly(C, p, {fill:color});
}
function bg(C, kind){
  C.bg({fill:YE});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) burst(C, W - 90, 90, 70, OR);
  else if (k === 1) { C.rect(W - 150, 0, 150, 26, {fill:BK}); C.rect(W - 26, 0, 26, 150, {fill:BK}); }
  else K.dots(C, W - 190, 50, 6, 4, 24, 4, BK, 1);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* sticker cards with a black number tab */
function stickers(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 26, cw = (Z.w - 8 - g * (cols - 1)) / cols, ch = (Z.h - 8 - g * (rows - 1)) / rows, pad = 24, tab = 44, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - tab + 8, {hMax:22, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + tab + pad * 2), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = stk(C, x, y, cw, ch2, {mode:hot ? 'k' : 'w'});
    C.rect(x, y, 64, tab, {fill:hot ? YE : BK});
    C.text(x, y, 64, tab, pad2(k + 1), {font:'head', size:18, wt:800, color:hot ? BK : YE, align:'c', va:'m', lh:1});
    C.icon(x + cw - pad - 22, y + 11, 22, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? YE : BK);
    K.hd(C, x + pad, y + tab + pad - 6, tw, ch2 - tab - pad * 2 + 6, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* stripes: full-width rows that alternate black and white */
function stripes(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 130), nw = 90, pad = 22, hasH = it.some(function(t){ return t.h; }), hw = hasH ? Math.min(300, (Z.w - nw) * 0.34) : 0, tw = Z.w - nw - hw - pad * 2;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw - 10, rh - 16, {font:'head', max:22, min:12.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:hasH ? 16 : 21, min:10.5, lh:1.36});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x - 1, y0 - 1, Z.w + 2, tot + 2, {fill:BK});
  it.forEach(function(t, k){
    var y = y0 + k * rh, dk = k % 2 === 1;
    C.rect(Z.x + 1.5, y + 1.5, Z.w - 3, rh - 3, {fill:dk ? BK : WH});
    C.text(Z.x, y, nw, rh, pad2(k + 1), {font:'head', size:Math.min(40, rh * 0.42), wt:800, color:dk ? YE : OR, align:'c', va:'m', lh:1});
    if (hasH) C.text(Z.x + nw, y, hw - 10, rh, t.h || '', {font:'head', size:hs, wt:800, color:dk ? WH : BK, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + pad, y, tw, rh, t.d, {font:'body', size:ds, color:dk ? '#EDEDED' : BK, va:'m', lh:1.36, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* shout: one black sticker shouting the first point, the rest as arrow lines */
function shout(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return stickers(C, Z, o);
  var lw = Math.round(Z.w * 0.42), g = 40, pad = 30, rest = it.slice(1), rn = rest.length, rw = Z.w - lw - g, rh = Z.h / rn;
  var c1 = stk(C, Z.x, Z.y, lw - 8, Z.h - 8, {mode:'k', d:10});
  burst(C, Z.x + lw - 50, Z.y + 40, 46, YE); C.text(Z.x + lw - 96, Z.y - 6, 92, 92, '01', {font:'head', size:22, wt:800, color:BK, align:'c', va:'m', lh:1});
  var F1 = K.fitHD(C, [it[0]], lw - 8 - pad * 2, 90, Z.h - pad * 2 - 60, {hMax:32, dMax:18, hMin:16, dMin:11});
  K.hd(C, Z.x + pad, Z.y + 70, lw - 8 - pad * 2, Z.h - pad - 90, it[0], F1, {tc:c1.tc, mc:c1.mc});
  var F = K.fitHD(C, rest, rw - 54, 30, rh - 22, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var y = Z.y + k * rh, bh = blockH(C, t, rw - 54, F), oy = Math.max(8, (rh - bh) / 2);
    K.poly(C, [[Z.x + lw + g, y + oy + 2], [Z.x + lw + g + 22, y + oy + 2], [Z.x + lw + g + 34, y + oy + 12], [Z.x + lw + g + 22, y + oy + 22], [Z.x + lw + g, y + oy + 22]], {fill:BK});
    K.hd(C, Z.x + lw + g + 54, y + oy, rw - 54, rh - oy, t, F, {tc:BK, mc:MUT});
    if (k < rn - 1) C.rect(Z.x + lw + g, y + rh - 1, rw, 2.5, {fill:BK});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: black towers of growing height, the number in yellow on top */
function towers(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 30, cw = (Z.w - g * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 8, lab = Math.min(120, Z.h * 0.34), th = Z.h - lab;
  var vs = C.groupFit(vals, cw - 30, 80, {font:'head', max:60, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, lab - 50, {font:'body', max:13.5, min:10.5, lh:1.36});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + g), hh = Math.max(vs + 40, th * (0.5 + 0.5 * (n > 1 ? k / (n - 1) : 1))), y = Z.y + th - hh, mode = k === n - 1 ? 'o' : 'k';
    C.rect(x + 8, y + 8, cw - 8, hh - 8, {fill:mode === 'o' ? BK : OR}); C.rect(x, y, cw - 8, hh - 8, {fill:mode === 'o' ? OR : BK, line:BK, lw:2.5});
    C.text(x + 12, y + 12, cw - 32, vs + 12, vals[k], {font:'head', size:vs, wt:800, color:mode === 'o' ? WH : YE, lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x, Z.y + th + 16, tw, 46, labs[k], {font:'head', size:ls, wt:800, color:BK, lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x, Z.y + th + 22 + le._h, tw, lab - 22 - le._h, t.x, {font:'body', size:ds, color:MUT, lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
  C.rect(Z.x, Z.y + th, Z.w, 2.5, {fill:BK});
}
/* timeline: chevron chain */
function chevrons(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return stripes(C, Z, o);
  var cw = Z.w / n, ah = 64, nt = 22, tw = cw - 26;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - nt * 2 - 10, ah - 12, {font:'head', max:22, min:12, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - ah - 50, {font:'body', max:15.5, min:10.5, lh:1.4});
  var y = Z.y + 6;
  it.forEach(function(t, k){
    var x = Z.x + k * cw, dk = k % 2 === 0, last = k === n - 1;
    K.poly(C, [[x, y], [x + cw - nt, y], [x + cw, y + ah / 2], [x + cw - nt, y + ah], [x, y + ah], [k ? x + nt : x, y + ah / 2]], {fill:last ? OR : (dk ? BK : WH)});
    C.text(x + nt + 4, y, cw - nt * 2 - 8, ah, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:last ? WH : (dk ? YE : BK), align:'c', va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(x + 13, y + ah + 22, 2.5, Z.h - ah - 40, {fill:BK});
    C.text(x + 28, y + ah + 22, tw - 14, Z.h - ah - 40, t.d, {font:'body', size:ds, color:BK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  stk(C, Z.x, Z.y, Z.w - 10, Z.h - 10, {d:10}); var pad = 46, y = Z.y + pad, w = Z.w - 10 - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, wt:800, color:OR, lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - 10 - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:BK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, W, 26, {fill:BK}); 
  if (f.company) C.text(70, 56, 600, 24, f.company, {font:'head', size:16, wt:800, color:BK, lh:1, caps:true, ls:0.04, ed:{f:'tf-company'}, maxLines:1});
  if (photo) { C.rect(742, 122, 470, 470, {fill:BK}); D.imgBox(C, 726, 106, 470, 470, {r:0}); burst(C, 740, 560, 70, OR); }
  else { C.rect(810, 150, 380, 380, {fill:BK}); C.ell(770, 110, 380, 380, {fill:OR, line:BK, lw:2.5}); burst(C, 1130, 500, 90, WH); C.ell(830, 170, 260, 260, {line:BK, lw:2.5}); }
  var tw = photo ? 610 : 680;
  C.text(70, 150, tw, 300, C.s.title, {font:'head', fit:{max:photo ? 76 : 96, min:34}, wt:800, color:BK, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98, ls:-0.03});
  if (C.s.subtitle) { C.rect(70, 470, 60, 6, {fill:BK}); C.text(70, 492, tw - 100, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, wt:500, color:BK, ed:{f:'subtitle'}, lh:1.42, maxLines:3}); }
  var who = [f.presenter, f.designation].filter(Boolean).join(' / ');
  if (who) { var pw = Math.min(tw, who.length * 8.6 + 48); stk(C, 70, 596, pw, 46, {mode:'k', d:6}); C.text(70 + 22, 596, pw - 44, 46, who, {font:'body', size:14, wt:700, color:WH, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(W - 70 - 400, 644, 400, 18, f.website, {font:'head', size:13, wt:800, color:BK, align:'r', lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(0, 0, W, H, {fill:BK});
  burst(C, W - 200, 170, 120, YE);
  if (photo) { C.rect(796, 216, 400, 400, {fill:OR}); D.imgBox(C, 780, 200, 400, 400, {r:0}); }
  else C.text(W - 320, 110, 240, 120, ':)', {font:'head', size:64, wt:800, color:BK, align:'c', va:'m', lh:1, caps:false});
  C.text(70, 110, 700, 220, C.s.title || 'Thank You', {font:'head', fit:{max:110, min:38}, wt:800, color:YE, ed:{f:'title'}, maxLines:2, va:'b', lh:0.98, ls:-0.03});
  if (C.s.subtitle) C.text(70, 346, 640, 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:WH, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 440 + k * 62;
    C.rect(70, y, 46, 46, {fill:YE}); C.icon(70 + 12, y + 12, 22, c.icon, BK);
    C.text(70 + 64, y, 600, 46, c.val, {font:'head', fit:{max:20, min:12}, wt:700, color:WH, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 440, H, {fill:BK});
  burst(C, 380, 560, 90, OR);
  var te = C.text(70, 120, 320, 260, C.s.title || 'Agenda', {font:'head', fit:{max:76, min:34}, wt:800, color:YE, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98, ls:-0.03});
  if (C.s.subtitle) C.text(70, 120 + te._h + 18, 310, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:WH, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  stripes(C, {x:510, y:76, w:W - 510 - 70, h:H - 170}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BK, tc:BK}); return; }
  var photo = o.img === 'photo', x = 70, w = photo ? 640 : W - 150;
  if (photo) { C.rect(786, 116, 420, 480, {fill:BK}); D.imgBox(C, 770, 100, 420, 480, {r:0}); }
  stk(C, x, 100, w, 480, {mode:'w', d:10});
  C.rect(x + 40, 100, 90, 70, {fill:BK}); C.text(x + 40, 112, 90, 70, '“', {font:'head', size:90, wt:800, color:YE, align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 44, 206, w - 88, 280, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:800, color:BK, lh:1.14, ls:-0.02, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 44, 206 + qe._h + 24, w - 88, 24, bt, {font:'body', size:15, wt:700, color:OR, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'citruspop', name:'Citrus Pop', tag:'Lemon · bold blocks',
  desc:'A lemon-yellow page with black ink blocks, hard offset shadows and loud grotesque type: playful and punchy for marketing, creators, events and campaigns.',
  fonts:{head:'Bricolage Grotesque', body:'Work Sans', headW:800, bodyW:400, headLh:1.02, headLs:-0.02, gf:'Bricolage+Grotesque:wght@600;700;800|Work+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Big type + pop shapes', {}), V('v2', 'Photo sticker', {img:'photo'})],
    closing:[V('v1', 'Black page + burst', {}), V('v2', 'Black page + photo', {img:'photo'})],
    agenda:[V('v1', 'Black panel + stripes', {fn:agendaA}), V('n2', 'Sticker cards', {body:stickers}), V('v2', 'Stripes + image right', {body:stripes, img:'right'})],
    text:[V('v1', 'Sticker statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Sticker cards', {body:stickers, hl:true}), V('n2', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stickers', {body:stickers, img:'left', cols:1})],
    p3:[V('v1', 'Sticker cards', {body:stickers, hl:true}), V('n2', 'Shout + arrows', {body:shout}), V('n3', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stickers', {body:stickers, img:'left', cols:1})],
    p4:[V('v1', 'Shout + arrows', {body:shout}), V('n2', 'Sticker cards', {body:stickers}), V('n3', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stickers', {body:stickers, img:'left', cols:2})],
    p56:[V('v1', 'Sticker cards', {body:stickers}), V('n2', 'Stripes', {body:stripes}), V('n3', 'Shout + arrows', {body:shout}), V('v2', 'Stripes + image right', {body:stripes, img:'right'}), V('v3', 'Image left + stripes', {body:stripes, img:'left'})],
    p78:[V('v1', 'Stripes', {body:stripes}), V('v2', 'Stripes + image right', {body:stripes, img:'right'})],
    stats:[V('v1', 'Towers', {body:towers}), V('n2', 'Progress rings', {body:'statRings', track:'#E8BE00'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Towers + image right', {body:towers, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Chevron chain', {body:chevrons}), V('n2', 'Stripes', {body:stripes}), V('n3', 'Sticker cards', {body:stickers}), V('v2', 'Stripes + image right', {body:stripes, img:'right'})],
    quote:[V('v1', 'Sticker quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1052);
