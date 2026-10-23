<?php
/**
 * Code Snippets export — DS Template 40 — Garnet Glass
 *
 * Snippet ID : 996
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:13:23
 * Load order : 203 of 218
 */
/**
 * DS Template 40 — Garnet Glass
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl40_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl40_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('garnetglass', array(
        'name'     => "Garnet Glass",
        'fonts'    => array('head' => "Marcellus", 'body' => "Mulish"),
        'pal'      => array('bg' => '#1D0712', 'text' => '#FFF3EE', 'accent' => '#F2B8A0', 'accent2' => '#FF7A9C'),
        'voice'    => "Elegant, assured voice: polished and evocative, few words, rich detail.",
        'recipe'   => "Welcome, the brand, the collection, craftsmanship, the experience, highlights, invitation, thank you.",
        'generate' => 'ds_tpl40_generate',
        'build'    => 'ds_tpl40_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl40_generate') ) {
function ds_tpl40_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl40_build') ) {
function ds_tpl40_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-garnetglass">
/* DesignStager template: garnetglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 40 - Garnet Glass : deep wine page, rose-gold light, rosy glass sheets and gems */
var RG = '#F2B8A0', PK = '#FF7A9C', INK = '#FFF3EE', MUT = '#D9B5B8';
var ROM = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
var P0 = {bg:'#1D0712', text:INK, title:INK, muted:MUT, accent:RG, accent2:PK, card:'#32101F', line:'#5A2A3A', onAccent:'#2A0A16'};
var S = {
  margin:78, series:[RG, PK, '#FFD9C8', '#C2476B', '#F7E3DA', '#8E2F4C'],
  header:{kind:'left', top:62, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return (C.cover.company || '') + '  —  ' + (ROM[C.i] || pad2(C.i + 1)); }, kc:RG, kGap:26, kSize:11.5},
  card:{kind:'glass', r:16, fill:'#FFFFFF', op:0.08, line:'#F2B8A0', lop:0.4, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:RG, dark:false, tc:'#2A0A16', mc:'#5A2634'}},
  icon:{kind:'circle', bg:RG, fg:'#2A0A16'}, num:{kind:'big', fg:RG},
  conc:{kind:'bar', barC:RG, label:'The point', lc:RG, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:16, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#6A2440'], [100, '#2E0D1C']]}}
};
function rosy(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 16;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#FFD9C8'], [100, '#F09A8C']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:175, stops:[[0, '#FFFFFF', 0.4], [40, '#FFFFFF', 0]]}, r:r}); return {tc:'#2A0A16', mc:'#5A2634', ac:'#7A1F3C'}; }
  C.rect(x, y, w, h, {grad:{ang:140, stops:[[0, '#FFC9D6', 0.2], [100, '#FFFFFF', 0.04]]}, line:'#F2B8A0', lop:0.42, lw:1, r:r});
  return {tc:INK, mc:MUT, ac:RG};
}
function gem(C, cx, cy, r, hot){
  K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], {fill:hot ? '#F6B9A6' : '#FFFFFF', op:hot ? 1 : 0.1});
  K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy]], {fill:'#FFFFFF', op:hot ? 0.35 : 0.08});
  K.poly(C, [[cx, cy - r], [cx - r, cy], [cx, cy]], {fill:'#FFFFFF', op:hot ? 0.15 : 0.03});
  var p = [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]];
  for (var q = 0; q < 4; q++) C.line(p[q][0], p[q][1], p[(q + 1) % 4][0], p[(q + 1) % 4][1], {color:RG, lw:1, op:0.7});
}
function foot(C){
  var M = 78, y = H - 36, f = C.cover;
  K.poly(C, [[M + 5, y + 3], [M + 10, y + 8], [M + 5, y + 13], [M, y + 8]], {fill:RG});
  C.text(M + 20, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.08});
  C.text(W - M - 120, y, 120, 16, pad2(C.i + 1), {font:'head', size:14, color:RG, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:150, stops:[[0, '#2C0A1A'], [100, '#12040B']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.95, W * 0.02, W * 0.7][k], [H * 0.05, H * 0.1, H * 1.0][k], 760, 520, PK, 0.26);
  D.glow(C, [W * 0.1, W * 0.9, W * 0.1][k], [H * 0.95, H * 0.9, H * 0.1][k], 600, 420, RG, 0.2);
  if (kind === 'cover' || kind === 'closing') return;
  gem(C, [W - 70, 60, W - 90][k], [H - 90, H - 80, 80][k], 110, false);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* zigzag: glass cards that alternate high and low */
function zigzag(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return sheet(C, Z, o);
  var g = 16, cw = (Z.w - g * (n - 1)) / n, off = Math.min(46, Z.h * 0.12), ch = Z.h - off, pad = 24, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - 58, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + (k % 2 ? off : 0), hot = o.hl && k === 0, cc = rosy(C, x, y, cw, ch, {hot:hot});
    C.ell(x + pad, y + pad, 42, 42, {fill:hot ? '#7A1F3C' : RG, op:hot ? 1 : 0.16, line:hot ? null : RG, lop:0.8, lw:1});
    C.icon(x + pad + 11, y + pad + 11, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? '#FFD9C8' : RG);
    K.hd(C, x + pad, y + pad + 60, tw, ch - pad * 2 - 60, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* one rosy glass sheet, items in ruled columns with roman numerals */
function sheet(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), pad = 30, gx = 44, cw = (Z.w - pad * 2 - gx * (cols - 1)) / cols, rh = Math.min((Z.h - pad * 2) / per, 150), nw = 50, tw = cw - nw;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 20, hMin:13, dMin:10.5, noBoost:n > 4});
  var sh = rh * per + pad * 2, y0 = Z.y + Math.max(0, (Z.h - sh) * 0.3);
  rosy(C, Z.x, y0, Z.w, sh, {r:20});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + pad + c * (cw + gx), y = y0 + pad + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(8, (rh - bh) / 2);
    C.text(x, y + oy - 2, nw - 8, 30, ROM[k], {font:'head', size:22, color:RG, lh:1, caps:false});
    K.hd(C, x + nw, y + oy, tw, rh - oy - 2, t, F, {tc:INK, mc:MUT});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:RG, op:0.28});
  });
  if (cols > 1) C.rect(Z.x + pad + cw + gx / 2, y0 + pad, 1, sh - pad * 2, {fill:RG, op:0.28});
}
/* glass tiles with a gem marker */
function gems(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 24, gm = 26, tw = cw - pad * 2 - gm * 2 - 14;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, Math.max(bmx, gm * 2) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), cc = rosy(C, x, y, cw, ch2, {});
    gem(C, x + pad + gm, y + pad + gm, gm, true);
    C.text(x + pad, y + pad, gm * 2, gm * 2, String(k + 1), {font:'head', size:17, color:'#2A0A16', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad + gm * 2 + 14, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: a big gem behind each number */
function gemStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw / 2 - 16, Z.h * 0.3, 130), vals = st.map(sv), labs = st.map(sl), tw = cw - 28;
  var vs = C.groupFit(vals, r * 1.15, r * 0.6, {font:'head', max:50, min:18, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - r * 2 - 80, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = Z.y + r + 4;
    gem(C, cx, cy, r, false);
    C.text(cx - r * 0.6, cy - r * 0.4, r * 1.2, r * 0.8, vals[k], {font:'head', size:vs, color:RG, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, cy + r + 18, tw, 50, labs[k], {font:'head', size:ls, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, cy + r + 24 + le._h, tw, Z.y + Z.h - (cy + r + 24 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: ladder of glass bars, each starting further right */
function ladder(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return sheet(C, Z, o);
  var g = 10, rh = Math.min((Z.h - g * (n - 1)) / n, 96), shift = Math.min(70, (Z.w * 0.34) / (n - 1)), bw = Z.w - shift * (n - 1), pad = 22, hw = Math.min(170, bw * 0.24), tw = bw - pad * 2 - hw - 16;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), hw, rh - 16, {font:'head', max:22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:15, min:10.5, lh:1.36});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * shift, y = y0 + k * (rh + g), hot = k === n - 1, cc = rosy(C, x, y, bw, rh, {hot:hot, r:rh / 2 > 30 ? 30 : rh / 2});
    C.text(x + pad, y, hw, rh, t.h || pad2(k + 1), {font:'head', size:hs, color:cc.ac, va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(x + pad + hw + 2, y + rh * 0.25, 1, rh * 0.5, {fill:cc.ac, op:0.5});
    C.text(x + pad + hw + 16, y, tw, rh, t.d, {font:'body', size:ds, color:cc.tc, va:'m', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  rosy(C, Z.x, Z.y, Z.w, Z.h, {r:22}); var pad = 50, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, color:RG, lh:1.14, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; K.poly(C, [[x + 10, y], [x + 20, y + 11], [x + 10, y + 22], [x, y + 11]], {fill:RG}); C.text(x + 32, y, 460, 22, f.company, {font:'body', size:13, wt:600, color:INK, va:'m', lh:1, caps:true, ls:0.16, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 78, 54);
  if (photo) { D.imgBox(C, 740, 90, 462, 540, {r:231}); gem(C, 760, 540, 70, true); }
  else { gem(C, 980, 350, 250, false); gem(C, 980, 350, 150, true); gem(C, 800, 520, 70, false); gem(C, 1150, 170, 50, false); }
  var tw = photo ? 600 : 660;
  C.text(78, 180, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 66 : 78, min:32}, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.06});
  if (C.s.subtitle) C.text(78, 448, tw - 80, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var pw = Math.min(tw, who.length * 8 + 56); rosy(C, 78, 560, pw, 48, {r:24}); C.text(78 + 26, 560, pw - 52, 48, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(78, 632, 500, 18, f.website, {font:'body', size:12.5, wt:500, color:RG, lh:1, ls:0.06, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 78, 54);
  if (photo) D.imgBox(C, 780, 110, 420, 500, {r:210}); else { gem(C, 1000, 360, 230, false); gem(C, 1000, 360, 130, true); }
  C.text(78, 130, 640, 190, C.s.title || 'Thank You', {font:'head', fit:{max:86, min:34}, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.04});
  if (C.s.subtitle) C.text(78, 336, 580, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  rosy(C, 78, 430, 620, n * 58 + 24, {r:20});
  cs.forEach(function(c, k){
    var y = 442 + k * 58;
    C.icon(78 + 28, y + 18, 22, c.icon, RG);
    C.text(78 + 70, y, 520, 58, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(78 + 28, y - 0.5, 564, 1, {fill:RG, op:0.28});
  });
}
function agendaA(C, o){
  gem(C, 250, 500, 190, false); gem(C, 250, 500, 100, true);
  var te = C.text(78, 110, 400, 200, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, color:INK, ed:{f:'title'}, maxLines:2, va:'t', lh:1.04});
  if (C.s.subtitle) C.text(78, 110 + te._h + 14, 380, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  sheet(C, {x:540, y:80, w:W - 540 - 78, h:H - 170}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RG, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 78 : 170, w = photo ? 620 : W - 340, al = photo ? 'l' : 'c';
  if (photo) D.imgBox(C, 800, 110, 400, 500, {r:200});
  if (!photo) { gem(C, W / 2, 150, 44, true); C.text(W / 2 - 44, 122, 88, 88, '”', {font:'head', size:64, color:'#2A0A16', align:'c', va:'m', lh:1, caps:false, ls:0}); }
  else C.text(x, 110, 100, 90, '“', {font:'head', size:130, color:RG, lh:1, caps:false, ls:0});
  var qe = C.text(x, 226, w, 290, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:19}, color:INK, align:al, lh:1.24, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, 226 + qe._h + 28, w, 24, bt, {font:'body', size:14, wt:600, color:RG, align:al, lh:1.1, caps:true, ls:0.14, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'garnetglass', name:'Garnet Glass', tag:'Dark · rose glass',
  desc:'A deep wine page with rose-gold light, rosy glass sheets and faceted gems: rich and elegant for luxury, beauty, events and premium brand decks.',
  fonts:{head:'Marcellus', body:'Mulish', headW:400, bodyW:400, headLh:1.1, gf:'Marcellus|Mulish:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Faceted gems', {}), V('v2', 'Oval photo + gem', {img:'photo'})],
    closing:[V('v1', 'Gem + glass contacts', {}), V('v2', 'Oval photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Gem + ruled sheet', {fn:agendaA}), V('n2', 'Gem tiles', {body:gems}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1})],
    text:[V('v1', 'Rosy statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Gem tiles', {body:gems}), V('n2', 'Zigzag cards', {body:zigzag, hl:true}), V('n3', 'Ruled sheet', {body:sheet, cols:2}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + gem tiles', {body:gems, img:'left', cols:1})],
    p3:[V('v1', 'Zigzag cards', {body:zigzag, hl:true}), V('n2', 'Gem tiles', {body:gems}), V('n3', 'Ruled sheet', {body:sheet, cols:1}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + gem tiles', {body:gems, img:'left', cols:1})],
    p4:[V('v1', 'Zigzag cards', {body:zigzag}), V('n2', 'Ruled sheet', {body:sheet}), V('n3', 'Gem tiles', {body:gems}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + gem tiles', {body:gems, img:'left', cols:1})],
    p56:[V('v1', 'Ruled sheet', {body:sheet}), V('n2', 'Gem tiles', {body:gems}), V('n3', 'Zigzag cards', {body:zigzag}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1}), V('v3', 'Image left + sheet', {body:sheet, img:'left', cols:1})],
    p78:[V('v1', 'Ruled sheet', {body:sheet}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1})],
    stats:[V('v1', 'Gem stats', {body:gemStats}), V('n2', 'Progress rings', {body:'statRings', track:'#4A1C2E'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Gem stats + image right', {body:gemStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Ladder', {body:ladder}), V('n2', 'Zigzag cards', {body:zigzag}), V('n3', 'Ruled sheet', {body:sheet}), V('v2', 'Ruled sheet + image right', {body:sheet, img:'right', cols:1})],
    quote:[V('v1', 'Gem quote', {}), V('v2', 'Quote + oval photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1050);
