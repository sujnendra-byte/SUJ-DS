<?php
/**
 * Code Snippets export — DS Template 48 — Stack Glass
 *
 * Snippet ID : 1004
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:26:24
 * Load order : 211 of 218
 */
/**
 * DS Template 48 — Stack Glass
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl48_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl48_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('stackglass', array(
        'name'     => "Stack Glass",
        'fonts'    => array('head' => "Red Hat Display", 'body' => "Red Hat Text"),
        'pal'      => array('bg' => '#080C18', 'text' => '#EEF4FF', 'accent' => '#4D8DFF', 'accent2' => '#3DDBB4'),
        'voice'    => "Technical, confident voice: clear architecture language, concrete metrics, no hype.",
        'recipe'   => "Overview, the problem, architecture, how it works, security and scale, results, roadmap, thank you.",
        'generate' => 'ds_tpl48_generate',
        'build'    => 'ds_tpl48_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl48_generate') ) {
function ds_tpl48_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl48_build') ) {
function ds_tpl48_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-stackglass">
/* DesignStager template: stackglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 48 - Stack Glass : dark data-centre page, 3D isometric servers, chips and databases, glass rack units */
var BL = '#4D8DFF', MI = '#3DDBB4', INK = '#EEF4FF', MUT = '#9FB0CC';
var P0 = {bg:'#080C18', text:INK, title:INK, muted:MUT, accent:BL, accent2:MI, card:'#111B33', line:'#263759', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[BL, MI, '#9CC0FF', '#F5B84A', '#2E5FCC', '#C9D6EE'],
  header:{kind:'left', top:58, tMax:44, tMin:26, sMax:17, sMin:13, kicker:function(C){ return '> ' + (C.cover.company || 'deck').toLowerCase().replace(/\s+/g, '-') + ' / ' + pad2(C.i + 1); }, kc:MI, kGap:26, kSize:12},
  card:{kind:'glass', r:8, fill:'#FFFFFF', op:0.07, line:'#9CC0FF', lop:0.35, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:BL, dark:true, tc:'#FFFFFF', mc:'#DCE8FF'}},
  icon:{kind:'square', bg:BL, fg:'#FFFFFF', r:6}, num:{kind:'big', fg:BL},
  conc:{kind:'bar', barC:MI, label:'Output', lc:MI, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:8, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#1D3463'], [100, '#0C1530']]}}
};
function unit(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 8;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#5B9BFF'], [100, '#2C5FD6']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:175, stops:[[0, '#FFFFFF', 0.25], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#DCE8FF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.13], [100, '#FFFFFF', 0.03]]}, line:'#9CC0FF', lop:0.38, lw:1, r:r});
  return {tc:INK, mc:MUT, ac:BL};
}
/* isometric slab: top face and two sides */
function slab(C, cx, cy, s, h, c){
  var a = s * 0.866;
  K.poly(C, [[cx - a, cy], [cx, cy + s / 2], [cx, cy + s / 2 + h], [cx - a, cy + h]], {fill:c[1]});
  K.poly(C, [[cx + a, cy], [cx, cy + s / 2], [cx, cy + s / 2 + h], [cx + a, cy + h]], {fill:c[2]});
  K.poly(C, [[cx, cy - s / 2], [cx + a, cy], [cx, cy + s / 2], [cx - a, cy]], {fill:c[0]});
}
/* 3D server tower: stacked units with status lights and vents */
function server(C, cx, cy, s, n){
  var h = s * 0.32, gap = s * 0.1, a = s * 0.866;
  K.poly(C, [[cx, cy + s / 2 + h + 10], [cx + a * 1.25, cy + h + s * 0.12 + 10], [cx, cy - s * 0.4 + h + 40], [cx - a * 1.25, cy + h + s * 0.12 + 10]], {fill:'#000000', op:0.35});
  for (var k = 0; k < n; k++) {
    var y = cy - k * (h + gap);
    slab(C, cx, y, s, h, ['#3A4E7A', '#1B2848', '#111A32']);
    K.poly(C, [[cx - a * 0.86, y + h * 0.3 + s * 0.07], [cx - a * 0.66, y + h * 0.3 + s * 0.185], [cx - a * 0.66, y + h * 0.62 + s * 0.185], [cx - a * 0.86, y + h * 0.62 + s * 0.07]], {fill:k % 2 ? BL : MI});
    for (var q = 0; q < 4; q++) { var t0 = 0.2 + q * 0.18; K.poly(C, [[cx + a * t0, y + h * 0.3 + s * 0.5 * (1 - t0)], [cx + a * (t0 + 0.1), y + h * 0.3 + s * 0.5 * (1 - t0 - 0.1)], [cx + a * (t0 + 0.1), y + h * 0.7 + s * 0.5 * (1 - t0 - 0.1)], [cx + a * t0, y + h * 0.7 + s * 0.5 * (1 - t0)]], {fill:'#000000', op:0.45}); }
  }
  var ty = cy - (n - 1) * (h + gap);
  K.poly(C, [[cx, ty - s * 0.3], [cx + a * 0.6, ty], [cx, ty + s * 0.3], [cx - a * 0.6, ty]], {fill:BL, op:0.5});
}
/* 3D chip: a flat board with a raised glowing core and pins */
function chip(C, cx, cy, s){
  var a = s * 0.866;
  for (var q = -2; q <= 2; q++) { C.line(cx - a * (0.5 + q * 0.2) - a * 0.3, cy + s * (0.25 - q * 0.1) + s * 0.15 + 6, cx - a * (0.5 + q * 0.2), cy + s * (0.25 - q * 0.1) + 6, {color:MI, lw:2, op:0.8}); C.line(cx + a * (0.5 + q * 0.2) + a * 0.3, cy + s * (0.25 - q * 0.1) + s * 0.15 + 6, cx + a * (0.5 + q * 0.2), cy + s * (0.25 - q * 0.1) + 6, {color:BL, lw:2, op:0.8}); }
  slab(C, cx, cy, s, s * 0.12, ['#2B3C63', '#16213F', '#0E1730']);
  slab(C, cx, cy - s * 0.12, s * 0.55, s * 0.1, ['#6FA4FF', '#2C5FD6', '#1E46A8']);
  K.poly(C, [[cx, cy - s * 0.12 - s * 0.14], [cx + a * 0.28, cy - s * 0.12], [cx, cy - s * 0.12 + s * 0.14], [cx - a * 0.28, cy - s * 0.12]], {fill:'#FFFFFF', op:0.5});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.rect(M, y + 3, 8, 8, {fill:MI}); C.rect(M + 12, y + 3, 8, 8, {fill:BL}); C.rect(M + 24, y + 3, 8, 8, {fill:'#FFFFFF', op:0.3});
  C.text(M + 44, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 120, y, 120, 16, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:12, wt:700, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#0B1226'], [100, '#060912']]}});
  K.grid(C, 64, '#9CC0FF', 0.045);
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.9, W * 0.1, W * 0.6][k], [H * 0.95, H * 0.95, 0][k], 760, 480, BL, 0.26);
  D.glow(C, [W * 0.1, W * 0.9, W * 0.05][k], [0, 0, H * 0.6][k], 500, 360, MI, 0.14);
  if (kind === 'cover' || kind === 'closing') return;
  if (k === 0) server(C, W - 150, 92, 40, 3); else if (k === 1) chip(C, W - 160, 70, 64); else { slab(C, W - 150, 50, 40, 40, ['#6FA4FF', '#2C5FD6', '#1E46A8']); slab(C, W - 215, 84, 24, 24, ['#3A4E7A', '#1B2848', '#111A32']); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* rack: every point is a rack unit with status lights and vents */
function rack(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 10, rh = Math.min((Z.h - g * (n - 1)) / n, 124), lw = 92, vw = Z.w > 760 ? 86 : 0, pad = 20, tw = Z.w - lw - vw - pad * 2;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, 46) + 28); var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), hot = o.hl && k === 0, cc = unit(C, Z.x, y, Z.w, rh, {hot:hot, r:6});
    C.rect(Z.x + 16, y + rh / 2 - 13, 8, 8, {fill:hot ? '#FFFFFF' : MI}); C.rect(Z.x + 16, y + rh / 2 + 5, 8, 8, {fill:hot ? '#FFFFFF' : BL, op:hot ? 0.6 : 1});
    C.text(Z.x + 34, y, lw - 34, rh, 'U' + pad2(k + 1), {font:'head', size:Math.min(22, rh * 0.26), wt:700, color:cc.ac, va:'m', lh:1, caps:false});
    C.rect(Z.x + lw, y + 12, 1, rh - 24, {fill:'#FFFFFF', op:0.18});
    var bh = blockH(C, t, tw, F);
    K.hd(C, Z.x + lw + pad, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
    if (vw) for (var q = 0; q < 6; q++) C.rect(Z.x + Z.w - vw + q * 11, y + rh * 0.3, 4, rh * 0.4, {fill:'#FFFFFF', op:hot ? 0.35 : 0.12, r:2});
  });
}
/* chips: glass cards with pins along the top and bottom edges */
function chips(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 22, gy = 30, pin = 9, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - pin * 2 - gy * (rows - 1)) / rows, pad = 24, top = 40, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - top, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad * 2 + 4), tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + pin + Math.max(0, (Z.h - pin * 2 - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + gy), hot = o.hl && k === 0, np = Math.max(4, Math.floor((cw - 40) / 26));
    for (var q = 0; q < np; q++) { var px = x + 20 + q * ((cw - 40 - 8) / (np - 1)); C.rect(px, y - pin, 8, pin, {fill:q % 2 ? BL : MI, op:0.85}); C.rect(px, y + ch2, 8, pin, {fill:q % 2 ? MI : BL, op:0.85}); }
    var cc = unit(C, x, y, cw, ch2, {hot:hot, r:6});
    C.text(x + pad, y + pad - 4, 120, 24, 'IC-' + pad2(k + 1), {font:'head', size:13, wt:700, color:hot ? '#FFFFFF' : MI, lh:1, caps:false, ls:0.08});
    C.icon(x + cw - pad - 22, y + pad - 6, 22, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), cc.ac);
    K.hd(C, x + pad, y + pad + top - 8, tw, ch2 - pad * 2 - top + 8, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* network: a 3D core in the middle, the points wired to it from both sides */
function network(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 3 || Z.w < 800 || Z.h < 300) return chips(C, Z, o);
  var L = it.filter(function(t, k){ return k % 2 === 0; }), R = it.filter(function(t, k){ return k % 2 === 1; }), cwid = 190, cw = (Z.w - cwid) / 2 - 10, cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, pad = 20, tw = cw - pad * 2;
  var per = Math.max(L.length, R.length), g = 14, rh = (Z.h - g * (per - 1)) / per;
  var F = K.fitHD(C, it, tw, 30, rh - 24, {hMax:19, dMax:14.5, hMin:12.5, dMin:10.5});
  [L, R].forEach(function(side, si){
    var tot = rh * side.length + g * (side.length - 1), y0 = Z.y + (Z.h - tot) / 2;
    side.forEach(function(t, k){
      var x = si ? Z.x + Z.w - cw : Z.x, y = y0 + k * (rh + g), ey = y + rh / 2, ex = si ? x : x + cw;
      C.line(ex, ey, cx + (si ? 50 : -50), ey, {color:si ? BL : MI, lw:1.5, op:0.7}); C.line(cx + (si ? 50 : -50), ey, cx + (si ? 30 : -30), cy, {color:si ? BL : MI, lw:1.5, op:0.7});
      C.rect(ex - 4, ey - 4, 8, 8, {fill:si ? BL : MI});
      var cc = unit(C, x, y, cw, rh, {r:6}), bh = blockH(C, t, tw, F);
      K.hd(C, x + pad, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
    });
  });
  server(C, cx, cy + 34, 46, 3);
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: 3D database cylinders of growing height */
function drums(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, dw = Math.min(cw * 0.62, 190), eh = dw * 0.3, vals = st.map(sv), labs = st.map(sl), tw = cw - 26, lab = Math.min(120, Z.h * 0.34), hmax = Z.h - lab - eh;
  var vs = C.groupFit(vals, dw - 24, 60, {font:'head', max:46, min:18, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, lab - 56, {font:'body', max:13.5, min:10.5, lh:1.38});
  var base = Z.y + Z.h - lab;
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), bh = Math.max(vs + 26, hmax * (0.45 + 0.55 * (n > 1 ? k / (n - 1) : 1))), top = base - bh, hot = k === n - 1, c1 = hot ? '#5B9BFF' : '#2B3C63', c2 = hot ? '#2C5FD6' : '#141E3A', c3 = hot ? '#9CC0FF' : '#46598A';
    C.ell(cx - dw / 2, base - eh / 2, dw, eh, {fill:c2});
    C.rect(cx - dw / 2, top, dw, bh, {grad:{ang:90, stops:[[0, c1], [100, c2]]}});
    for (var q = 1; q < 3; q++) if (bh > 90 * q) C.ell(cx - dw / 2, top + bh * q / 3 - eh / 2, dw, eh, {line:'#FFFFFF', lop:0.18, lw:1});
    C.ell(cx - dw / 2, top - eh / 2, dw, eh, {fill:c3, line:'#FFFFFF', lop:0.35, lw:1});
    C.text(cx - dw / 2 + 12, top + eh / 2 + 4, dw - 24, vs + 14, vals[k], {font:'head', size:vs, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + eh / 2 + 14, tw, 46, labs[k], {font:'head', size:ls, wt:600, color:INK, align:'c', lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + eh / 2 + 20 + le._h, tw, Z.y + Z.h - base - eh / 2 - 20 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: build pipeline - stages joined by arrows, the last one live */
function pipeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rack(C, Z, o);
  var ar = 30, cw = (Z.w - ar * (n - 1)) / n, pad = 18, tw = cw - pad * 2, top = 54, ch = Math.min(Z.h, 320), y0 = Z.y + (Z.h - ch) * 0.3;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw - 22, 30, {font:'head', max:22, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - top - pad * 2, {font:'body', max:15, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + ar), last = k === n - 1, cc = unit(C, x, y0, cw, ch, {hot:last, r:6});
    C.rect(x + pad, y0 + 22, 12, 12, {fill:last ? '#FFFFFF' : MI});
    C.text(x + pad + 22, y0 + 14, tw - 22, 28, t.h || 'Stage ' + (k + 1), {font:'head', size:hs, wt:700, color:cc.tc, va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y0 + top - 2, tw, 1, {fill:'#FFFFFF', op:0.2});
    C.text(x + pad, y0 + top + 14, tw, ch - top - pad - 14, t.d, {font:'body', size:ds, color:last ? '#FFFFFF' : MUT, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    if (!last) { var ax = x + cw + 5, ay = y0 + ch / 2; C.rect(ax, ay - 1.5, ar - 16, 3, {fill:BL}); K.poly(C, [[ax + ar - 18, ay - 8], [ax + ar - 8, ay], [ax + ar - 18, ay + 8]], {fill:BL}); }
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  unit(C, Z.x, Z.y, Z.w, Z.h, {r:10}); C.rect(Z.x + 20, Z.y + 18, 10, 10, {fill:MI}); C.rect(Z.x + 36, Z.y + 18, 10, 10, {fill:BL}); C.rect(Z.x + 52, Z.y + 18, 10, 10, {fill:'#FFFFFF', op:0.3}); C.rect(Z.x, Z.y + 44, Z.w, 1, {fill:'#FFFFFF', op:0.18});
  var pad = 44, y = Z.y + 44 + 30, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:700, color:MI, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; slab(C, x + 12, y + 8, 13, 7, ['#6FA4FF', '#2C5FD6', '#1E46A8']); C.text(x + 36, y, 460, 24, f.company, {font:'head', size:15, wt:700, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function scene(C, cx, cy){
  K.poly(C, [[cx, cy - 60], [cx + 330, cy + 130], [cx, cy + 320], [cx - 330, cy + 130]], {fill:'#9CC0FF', op:0.06});
  K.poly(C, [[cx, cy - 20], [cx + 260, cy + 130], [cx, cy + 280], [cx - 260, cy + 130]], {fill:'#9CC0FF', op:0.07});
  server(C, cx - 120, cy + 90, 80, 4); server(C, cx + 60, cy + 30, 80, 5); chip(C, cx + 150, cy + 200, 76);
  slab(C, cx - 40, cy + 210, 34, 34, ['#7CF0D0', '#2CB894', '#1C8E72']);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) { unit(C, 700, 90, 508, 540, {r:10}); D.imgBox(C, 716, 134, 476, 480, {r:6}); C.rect(718, 106, 10, 10, {fill:MI}); C.rect(734, 106, 10, 10, {fill:BL}); C.rect(750, 106, 10, 10, {fill:'#FFFFFF', op:0.3}); }
  else scene(C, 960, 200);
  var tw = photo ? 570 : 600;
  C.text(72, 160, tw, 20, '> init presentation', {font:'head', size:14, wt:600, color:MI, lh:1, caps:false});
  C.text(72, 196, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 62 : 72, min:32}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 60); unit(C, 72, 566, pw, 48, {r:6}); C.rect(72 + 16, 566 + 19, 10, 10, {fill:MI}); C.text(72 + 38, 566, pw - 54, 48, who, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(72, 636, 500, 18, f.website, {font:'body', size:12.5, wt:500, color:BL, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 54);
  if (photo) D.imgBox(C, 760, 120, 448, 480, {r:8}); else scene(C, 980, 190);
  C.text(72, 130, 620, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 336, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 426 + k * 62;
    unit(C, 72, y, 560, 52, {r:6}); C.rect(72 + 16, y + 21, 10, 10, {fill:k % 2 ? BL : MI}); C.icon(72 + 40, y + 16, 20, c.icon, INK);
    C.text(72 + 74, y, 470, 52, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  server(C, 240, 520, 84, 4);
  var te = C.text(72, 110, 400, 200, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:700, color:INK, ed:{f:'title'}, maxLines:2, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 110 + te._h + 14, 380, 70, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  rack(C, {x:520, y:76, w:W - 520 - 72, h:H - 170}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BL, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 640 : W - 144;
  if (photo) D.imgBox(C, 770, 100, 438, 500, {r:8});
  unit(C, x, 100, w, 500, {r:10}); C.rect(x + 20, 118, 10, 10, {fill:MI}); C.rect(x + 36, 118, 10, 10, {fill:BL}); C.rect(x, 144, w, 1, {fill:'#FFFFFF', op:0.18});
  C.text(x + 44, 160, 100, 80, '“', {font:'head', size:110, wt:700, color:BL, lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 250, w - 92, 250, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:600, color:INK, lh:1.24, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 250 + qe._h + 24, w - 92, 24, bt, {font:'body', size:15, wt:600, color:MI, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'stackglass', name:'Stack Glass', tag:'Dark · 3D servers',
  desc:'A dark data-centre page with 3D isometric servers, chips and database drums, glass rack units and status lights: technical and modern for IT, cloud, security and software decks.',
  fonts:{head:'Red Hat Display', body:'Red Hat Text', headW:700, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Red+Hat+Display:wght@500;600;700;800|Red+Hat+Text:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', '3D server scene', {}), V('v2', 'Photo in glass window', {img:'photo'})],
    closing:[V('v1', '3D scene + contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Server + rack list', {fn:agendaA}), V('n2', 'Chips', {body:chips}), V('v2', 'Rack list + image right', {body:rack, img:'right'})],
    text:[V('v1', 'Terminal statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Chips', {body:chips, hl:true}), V('n2', 'Rack units', {body:rack}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + chips', {body:chips, img:'left', cols:1})],
    p3:[V('v1', 'Chips', {body:chips, hl:true}), V('n2', 'Rack units', {body:rack, hl:true}), V('n3', 'Network', {body:network}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + rack units', {body:rack, img:'left'})],
    p4:[V('v1', 'Network', {body:network}), V('n2', 'Chips', {body:chips}), V('n3', 'Rack units', {body:rack}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + chips', {body:chips, img:'left', cols:2})],
    p56:[V('v1', 'Chips', {body:chips}), V('n2', 'Network', {body:network}), V('n3', 'Rack units', {body:rack}), V('v2', 'Rack units + image right', {body:rack, img:'right'}), V('v3', 'Image left + rack units', {body:rack, img:'left'})],
    p78:[V('v1', 'Rack units', {body:rack, max:8}), V('v2', 'Rack units + image right', {body:rack, img:'right', max:8})],
    stats:[V('v1', 'Database drums', {body:drums}), V('n2', 'Progress rings', {body:'statRings', track:'#1E2C4D'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Drums + image right', {body:drums, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Pipeline', {body:pipeline}), V('n2', 'Rack units', {body:rack}), V('n3', 'Chips', {body:chips}), V('v2', 'Rack units + image right', {body:rack, img:'right'})],
    quote:[V('v1', 'Terminal quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1058);
