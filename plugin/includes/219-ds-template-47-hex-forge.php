<?php
/**
 * Code Snippets export — DS Template 47 — Hex Forge
 *
 * Snippet ID : 1003
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:26:18
 * Load order : 210 of 218
 */
/**
 * DS Template 47 — Hex Forge
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl47_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl47_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('hexforge', array(
        'name'     => "Hex Forge",
        'fonts'    => array('head' => "Rajdhani", 'body' => "Barlow"),
        'pal'      => array('bg' => '#E9EDF2', 'text' => '#18202B', 'accent' => '#E02D3C', 'accent2' => '#2A3442'),
        'voice'    => "Engineering voice: precise, practical and process-minded. Specific numbers and clear steps.",
        'recipe'   => "Overview, current state, the process, improvements, technology, results, roadmap, thank you.",
        'generate' => 'ds_tpl47_generate',
        'build'    => 'ds_tpl47_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl47_generate') ) {
function ds_tpl47_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl47_build') ) {
function ds_tpl47_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-hexforge">
/* DesignStager template: hexforge */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 47 - Hex Forge : light steel page, 3D gears and hex prisms, crimson accent, glass plates */
var RD = '#E02D3C', ST = '#2A3442', INK = '#18202B', MUT = '#586474', LT = '#E9EDF2';
var P0 = {bg:LT, text:INK, title:INK, muted:MUT, accent:RD, accent2:ST, card:'#FFFFFF', line:'#C7D0DB', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[RD, ST, '#F28A93', '#8A97A8', '#B01E2B', '#C7D0DB'],
  header:{kind:'left', top:58, tMax:48, tMin:26, sMax:17, sMin:13, kicker:function(C){ return (C.cover.company || '') + '  //  ' + pad2(C.i + 1); }, kc:RD, kGap:26, kSize:12},
  card:{kind:'glass', r:6, fill:'#FFFFFF', op:0.7, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.07, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:ST, dark:true, tc:'#FFFFFF', mc:'#C7D0DB'}},
  icon:{kind:'square', bg:RD, fg:'#FFFFFF', r:4}, num:{kind:'big', fg:RD},
  conc:{kind:'bar', barC:RD, label:'Result', lc:RD, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:6, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#C7D0DB'], [100, '#8A97A8']]}}
};
function hexP(cx, cy, r){ var a = r * 0.866; return [[cx, cy - r], [cx + a, cy - r / 2], [cx + a, cy + r / 2], [cx, cy + r], [cx - a, cy + r / 2], [cx - a, cy - r / 2]]; }
/* hexagonal prism: top face plus two visible side faces */
function prism(C, cx, cy, r, d, c){
  var a = r * 0.866;
  K.poly(C, [[cx - a, cy + r / 2], [cx, cy + r], [cx, cy + r + d], [cx - a, cy + r / 2 + d]], {fill:c[1]});
  K.poly(C, [[cx + a, cy + r / 2], [cx, cy + r], [cx, cy + r + d], [cx + a, cy + r / 2 + d]], {fill:c[2]});
  K.poly(C, hexP(cx, cy, r), {fill:c[0]});
}
var GI = 0;
function gearP(cx, cy, r, n, rot, ky){
  var p = [], w = Math.PI / n;
  for (var k = 0; k < n; k++) { var a = rot + k * 2 * Math.PI / n; [[0.8, -w * 0.64], [1, -w * 0.34], [1, w * 0.34], [0.8, w * 0.64]].forEach(function(q){ p.push([cx + Math.cos(a + q[1]) * r * q[0], cy + Math.sin(a + q[1]) * r * q[0] * ky]); }); }
  return p;
}
/* 3D gear seen at a tilt: a thick rim, a recessed web with holes, a raised round hub and a bore */
function gear(C, cx, cy, r, n, c, o){
  var hub = typeof o === 'string' ? o : ((o && o.hub) || '#FFFFFF'), solid = o && o.solid, rot = o && o.rot != null ? o.rot : (GI * 0.53 + cx * 0.011 + r * 0.02), ky = 0.84, d = Math.max(7, r * 0.2), L = Math.max(3, Math.round(d / 2.5));
  K.poly(C, gearP(cx + d * 0.5, cy + d * 1.5, r, n, rot, ky), {fill:'#18202B', op:0.14});
  for (var q = L; q >= 1; q--) K.poly(C, gearP(cx, cy + d * q / L, r, n, rot, ky), {fill:c[1]});
  K.poly(C, gearP(cx, cy, r, n, rot, ky), {fill:c[0]});
  C.ell(cx - r * 0.68, cy - r * 0.68 * ky, r * 1.36, r * 1.36 * ky, {grad:{ang:90, stops:[[0, c[1]], [100, c[0]]]}});
  C.ell(cx - r * 0.68, cy - r * 0.68 * ky, r * 1.36, r * 1.36 * ky, {line:'#FFFFFF', lop:0.35, lw:1});
  for (var h = 0; h < 5; h++) { var a = rot + 0.4 + h * 2 * Math.PI / 5, hx = cx + Math.cos(a) * r * 0.49, hy = cy + Math.sin(a) * r * 0.49 * ky; C.ell(hx - r * 0.1, hy - r * 0.1 * ky, r * 0.2, r * 0.2 * ky, {fill:'#11161D', op:0.55}); C.ell(hx - r * 0.1, hy - r * 0.1 * ky + r * 0.045, r * 0.2, r * 0.2 * ky - r * 0.045, {fill:c[1]}); }
  C.ell(cx - r * 0.34, cy - r * 0.34 * ky, r * 0.68, r * 0.68 * ky, {fill:c[1]});
  C.rect(cx - r * 0.34, cy - d * 0.4, r * 0.68, d * 0.4, {fill:c[1]});
  C.ell(cx - r * 0.34, cy - d * 0.4 - r * 0.34 * ky, r * 0.68, r * 0.68 * ky, {grad:{ang:90, stops:[[0, '#FFFFFF'], [100, hub]]}});
  if (!solid) { C.ell(cx - r * 0.15, cy - d * 0.4 - r * 0.15 * ky, r * 0.3, r * 0.3 * ky, {fill:'#11161D'}); C.ell(cx - r * 0.15, cy - d * 0.4 - r * 0.15 * ky + r * 0.07, r * 0.3, r * 0.3 * ky - r * 0.07, {fill:'#3A4654'}); }
}
function plate(C, x, y, w, h, o){
  o = o || {};
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#364252'], [100, '#1B232E']]}, r:6, shadow:0.14}); C.rect(x, y, w, 4, {fill:RD}); return {tc:'#FFFFFF', mc:'#C7D0DB', ac:'#FF6B77'}; }
  C.rect(x, y, w, h, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.5]]}, line:'#FFFFFF', lop:1, lw:1.5, r:6, shadow:0.07});
  if (o.bolts !== false) [[x + 12, y + 12], [x + w - 12, y + 12], [x + 12, y + h - 12], [x + w - 12, y + h - 12]].forEach(function(p){ K.poly(C, hexP(p[0], p[1], 4), {fill:'#B3BECB'}); });
  return {tc:INK, mc:MUT, ac:RD};
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  K.poly(C, hexP(M + 8, y + 8, 9), {fill:RD});
  C.text(M + 28, y, 500, 16, f.website || f.company || '', {font:'body', size:11.5, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:14, wt:700, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#F3F5F8'], [100, '#DCE2EA']]}}); GI = C.i || 0;
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { gear(C, W - 86, 62, 56, 10, ['#CBD3DD', '#98A5B4'], '#EEF1F5'); gear(C, W - 176, 50, 30, 8, ['#4A5869', '#232C38'], '#DDE3EA'); }
  else if (k === 1) { prism(C, W - 70, 70, 54, 16, ['#F06A75', '#C4232F', '#9C1924']); prism(C, W - 150, 26, 30, 10, ['#465466', '#2A3442', '#1B232E']); }
  else { gear(C, W - 60, 40, 80, 10, ['#CBD3DD', '#AEB9C6'], '#EEF1F5'); gear(C, W - 170, 88, 44, 8, ['#F06A75', '#C4232F'], '#EEF1F5'); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* honeycomb list: a 3D hex badge beside every point */
function honey(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 140), hr = Math.min(32, rh * 0.3), tw = cw - hr * 2 - 26;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx) + (cols === 1 && r % 2 ? 0 : 0), y = y0 + r * rh, cy = y + rh / 2 - 4, red = k % 2 === 0;
    prism(C, x + hr, cy, hr, hr * 0.3, red ? ['#F0505D', '#C4232F', '#9C1924'] : ['#465466', '#2A3442', '#1B232E']);
    C.text(x, cy - hr, hr * 2, hr * 2, pad2(k + 1), {font:'head', size:hr * 0.62, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + hr * 2 + 26, y + Math.max(8, (rh - bh) / 2), tw, rh - 8, t, F, {tc:INK, mc:MUT});
  });
}
/* bolted glass plates with an icon tab */
function plates(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26, ic = 44, side = rows > 1, tw = side ? cw - pad * 2 - ic - 16 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - ic - 18, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 18) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = plate(C, x, y, cw, ch2, {hot:hot});
    K.poly(C, hexP(x + pad + ic / 2, y + pad + ic / 2, ic / 2), {fill:RD});
    C.icon(x + pad + ic / 2 - 10, y + pad + ic / 2 - 10, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), '#FFFFFF');
    if (side) K.hd(C, x + pad + ic + 16, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
    else K.hd(C, x + pad, y + pad + ic + 18, tw, ch2 - pad * 2 - ic - 18, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* gear train: meshed 3D gears, one per step, with the text under each */
function train(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760 || Z.h < 300) return honey(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.5, Z.h * 0.24, 92), tw = cw - 26, gy = Z.y + r + 8;
  var F = K.fitHD(C, it, tw, 46, Z.h - r * 2 - 50, {hMax:19, dMax:14.5, hMin:12, dMin:10.5});
  for (var k = n - 1; k >= 0; k--) { var cx = Z.x + cw * (k + 0.5), red = k % 2 === 0; gear(C, cx, gy + (k % 2 ? r * 0.14 : 0), r, 10, red ? ['#F0505D', '#B01E2B'] : ['#4A5869', '#232C38'], {hub:red ? '#FBD0D4' : '#C9D2DC', solid:true, rot:(C.i || 0) * 0.41 + (k % 2 ? Math.PI / 10 : 0)}); }
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), y = gy + (k % 2 ? r * 0.14 : 0);
    C.text(cx - r * 0.38, y - r * 0.2 - r * 0.33, r * 0.76, r * 0.66, String(k + 1), {font:'head', size:r * 0.36, wt:700, color:INK, align:'c', va:'m', lh:1});
    K.hd(C, cx - tw / 2, Z.y + r * 2 + 46, tw, Z.h - r * 2 - 46, t, F, {tc:INK, mc:MUT, align:'c'});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: hex prisms of growing depth */
function hexStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.42, Z.h * 0.25, 104), vals = st.map(sv), labs = st.map(sl), tw = cw - 26, dmax = Math.min(70, Z.h * 0.16), base = Z.y + r * 2 + dmax + 6;
  var vs = C.groupFit(vals, r * 1.3, r * 0.7, {font:'head', max:50, min:18, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.y + Z.h - base - 70, {font:'body', max:13.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), d = dmax * (0.3 + 0.7 * (n > 1 ? k / (n - 1) : 1)), cy = base - d - r, red = k === n - 1;
    prism(C, cx, cy, r, d, red ? ['#F0505D', '#C4232F', '#9C1924'] : ['#FFFFFF', '#C7D0DB', '#AEB9C6']);
    C.text(cx - r * 0.75, cy - r * 0.4, r * 1.5, r * 0.8, vals[k], {font:'head', size:vs, wt:700, color:red ? '#FFFFFF' : RD, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + 18, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + 24 + le._h, tw, Z.y + Z.h - base - 24 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  plate(C, Z.x, Z.y, Z.w, Z.h, {}); C.rect(Z.x, Z.y + 30, 5, 70, {fill:RD});
  var pad = 46, y = Z.y + pad - 10, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, wt:700, color:RD, lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 12; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; prism(C, x + 12, y + 10, 12, 4, ['#F0505D', '#C4232F', '#9C1924']); C.text(x + 36, y, 460, 24, f.company, {font:'head', size:17, wt:700, color:INK, va:'m', lh:1, caps:true, ls:0.06, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function machine(C, cx, cy, s){
  gear(C, cx + s * 0.9, cy - s * 0.55, s * 0.62, 10, ['#4A5869', '#232C38'], '#DDE3EA');
  gear(C, cx - s * 0.2, cy + s * 0.1, s, 14, ['#F0505D', '#B01E2B'], '#FBE3E5');
  gear(C, cx + s * 0.95, cy + s * 0.95, s * 0.46, 8, ['#CBD3DD', '#98A5B4'], '#F3F5F8');
  prism(C, cx - s * 1.15, cy - s * 0.95, s * 0.3, s * 0.12, ['#FFFFFF', '#C7D0DB', '#AEB9C6']);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) { K.poly(C, hexP(960, 360, 300), {fill:'#FFFFFF'}); D.imgBox(C, 760, 160, 400, 400, {r:6}); prism(C, 760, 560, 54, 18, ['#F0505D', '#C4232F', '#9C1924']); gear(C, 1170, 170, 60, 10, ['#4A5869', '#232C38'], '#DDE3EA'); }
  else machine(C, 960, 350, 170);
  var tw = photo ? 600 : 620;
  C.rect(72, 176, 60, 6, {fill:RD});
  C.text(72, 200, tw, 250, C.s.title, {font:'head', fit:{max:84, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 462, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  |  ');
  if (who) { var pw = Math.min(tw, who.length * 8.4 + 50); C.rect(72, 570, pw, 46, {fill:ST, r:4}); C.rect(72, 570, 6, 46, {fill:RD}); C.text(72 + 26, 570, pw - 44, 46, who, {font:'body', size:14, wt:600, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(72, 640, 500, 18, f.website, {font:'body', size:12.5, wt:600, color:RD, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) { D.imgBox(C, 760, 130, 440, 440, {r:6}); gear(C, 770, 570, 60, 10, ['#F0505D', '#B01E2B'], '#FBE3E5'); } else machine(C, 980, 340, 150);
  C.text(72, 130, 620, 190, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:36}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 336, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 430 + k * 62;
    prism(C, 72 + 22, y + 20, 22, 6, k % 2 ? ['#465466', '#2A3442', '#1B232E'] : ['#F0505D', '#C4232F', '#9C1924']); C.icon(72 + 12, y + 10, 20, c.icon, '#FFFFFF');
    C.text(72 + 62, y, 520, 46, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  gear(C, 230, 520, 130, 12, ['#F0505D', '#B01E2B'], '#FBE3E5'); gear(C, 400, 420, 60, 8, ['#4A5869', '#232C38'], '#DDE3EA');
  C.rect(72, 110, 60, 6, {fill:RD});
  var te = C.text(72, 132, 400, 200, C.s.title || 'Agenda', {font:'head', fit:{max:80, min:36}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'t', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 132 + te._h + 12, 380, 70, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  plate(C, 530, 70, W - 530 - 72, H - 170, {});
  honey(C, {x:566, y:92, w:W - 566 - 108, h:H - 214}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RD, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 640 : W - 144;
  if (photo) D.imgBox(C, 770, 100, 438, 500, {r:6});
  plate(C, x, 100, w, 500, {});
  prism(C, x + 76, 150, 36, 10, ['#F0505D', '#C4232F', '#9C1924']); C.text(x + 40, 130, 72, 60, '”', {font:'head', size:64, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 232, w - 92, 270, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:600, color:INK, lh:1.16, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 232 + qe._h + 24, w - 92, 24, bt, {font:'body', size:15, wt:700, color:RD, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'hexforge', name:'Hex Forge', tag:'Light steel · 3D gears',
  desc:'A light steel page with 3D gears, hexagonal prisms and bolted glass plates in crimson and graphite: engineered and precise for manufacturing, operations, engineering and process decks.',
  fonts:{head:'Rajdhani', body:'Barlow', headW:700, bodyW:400, headLh:1.04, gf:'Rajdhani:wght@500;600;700|Barlow:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', '3D gear machine', {}), V('v2', 'Photo on hexagon', {img:'photo'})],
    closing:[V('v1', 'Gears + hex contacts', {}), V('v2', 'Photo + hex contacts', {img:'photo'})],
    agenda:[V('v1', 'Gears + honeycomb list', {fn:agendaA}), V('n2', 'Bolted plates', {body:plates}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1})],
    text:[V('v1', 'Plate statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Bolted plates', {body:plates, hl:true}), V('n2', 'Gear train', {body:train}), V('n3', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p3:[V('v1', 'Gear train', {body:train}), V('n2', 'Bolted plates', {body:plates, hl:true}), V('n3', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p4:[V('v1', 'Bolted plates', {body:plates}), V('n2', 'Gear train', {body:train}), V('n3', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p56:[V('v1', 'Bolted plates', {body:plates}), V('n2', 'Honeycomb list', {body:honey}), V('n3', 'Gear train', {body:train}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1}), V('v3', 'Image left + honeycomb', {body:honey, img:'left', cols:1})],
    p78:[V('v1', 'Honeycomb list', {body:honey}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1})],
    stats:[V('v1', 'Hex prisms', {body:hexStats}), V('n2', 'Progress rings', {body:'statRings', track:'#CBD3DD'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Hex prisms + image right', {body:hexStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gear train', {body:train}), V('n2', 'Honeycomb list', {body:honey}), V('n3', 'Bolted plates', {body:plates}), V('v2', 'Honeycomb list + image right', {body:honey, img:'right', cols:1})],
    quote:[V('v1', 'Plate quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1057);
