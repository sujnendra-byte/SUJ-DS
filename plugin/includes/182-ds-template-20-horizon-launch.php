<?php
/**
 * Code Snippets export — DS Template 20 — Horizon Launch
 *
 * Snippet ID : 919
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:33:21
 * Load order : 177 of 191
 */
/**
 * DS Template 20 — Horizon Launch
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
    ds_tpl_register('horizon', array(
        'name'     => "Horizon Launch",
        'fonts'    => array('head' => "Archivo", 'body' => "Archivo"),
        'pal'      => array('bg' => '#EEF3FB', 'text' => '#0F2557', 'accent' => '#F58A1F', 'accent2' => '#5AB4E5'),
        'voice'    => "Product-launch voice: energetic, forward-moving, milestones and momentum.",
        'recipe'   => "Vision, the problem, the product, launch plan, lifecycle stages, traction, roadmap, the ask.",
        'generate' => 'ds_tpl20_generate',
        'build'    => 'ds_tpl20_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl20_generate') ) {
function ds_tpl20_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl20_build') ) {
function ds_tpl20_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-horizon">
/* Template 20 — Horizon Launch : pale sky pages with faint four-point stars, navy + orange + sunflower + sky, uppercase heads */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#0F2557', ORNG = '#F58A1F', SUN = '#F7B928', SKY = '#5AB4E5';
var P0 = {bg:'#EEF3FB', text:'#0F2557', title:'#0F2557', muted:'#4C5B80', accent:'#F58A1F', accent2:'#5AB4E5', card:'#FFFFFF', line:'#D3DDEE', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[ORNG, SUN, NAVY, SKY, '#2E4D8F', '#F9A65A'],
  header:{kind:'center', tMax:40, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'solid', r:12, fill:'#FFFFFF', shadow:0.07, hl:{kind:'solid', fill:NAVY, dark:true}},
  icon:{kind:'circle', bg:ORNG, fg:'#FFFFFF'},
  num:{kind:'circle', bg:NAVY, fg:'#FFFFFF'},
  conc:{kind:'band', fill:NAVY, r:12, label:'Next move'},
  footer:{brand:true, page:true, color:'#7C8AAB'}, xRows:'#FFFFFF', hubFill:'#DFE8F5', road:NAVY, flowBar:'#D9E3F2',
  imgFramed:true, imgR:14
};
function stars(C, seed){
  var pts = [[90, 90, 40], [W - 120, 110, 52], [220, H - 90, 34], [W - 260, H - 70, 44], [W / 2 + 40, 40, 26], [40, H / 2, 30], [W - 40, H / 2 + 60, 28]];
  pts.forEach(function(p, k){ K.spark(C, p[0], p[1], p[2], '#D2DFF3', 1); });
}
function bg(C, kind){ C.bg({fill:'#EEF3FB'}); stars(C); }
function wave(C, y, amp, x0, x1, pins){
  var pts = [], N = 60;
  for (var i = 0; i <= N; i++) { var t = i / N; pts.push([x0 + t * (x1 - x0), y - amp * Math.abs(Math.sin(t * Math.PI * 3.2)) * (0.6 + 0.4 * t)]); }
  K.curve(C, pts, {stroke:NAVY, lw:26});
  (pins || []).forEach(function(pk, k){ var p = pts[pk]; C.ell(p[0] - 22, p[1] - 22, 44, 44, {fill:'#FFFFFF', line:[ORNG, SUN, SKY][k % 3], lw:6}); });
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, W - 520, 40, 480, H - 80, {r:18});
  else wave(C, 640, 150, 60, W - 40, [30, 42, 54]);
  var x = 64, w = o.img ? 620 : 700;
  if (f.company || f.date) C.text(x, 56, w, 20, [f.company, f.date].filter(Boolean).join('   ·   '), {font:'body', size:13, wt:700, color:P.muted, caps:true, ls:0.14});
  C.text(x, 100, w, o.img ? 300 : 230, C.s.title, {font:'head', fit:{max:64, min:30}, color:NAVY, ed:{f:'title'}, maxLines:4, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(x, o.img ? 420 : 344, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  if (f.website) C.text(x, H - 60, 400, 20, f.website, {font:'body', size:13, wt:700, color:NAVY});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 460, H - 80, {r:18}); else wave(C, 650, 90, 40, W - 40, [20, 40]);
  var x = o.img ? 560 : 120, w = o.img ? W - 620 : W - 240, al = o.img ? 'l' : 'c';
  C.text(x, 120, w, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:1});
  if (C.s.subtitle) C.text(x, 330, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, x, 420, w, {cards:true});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:ORNG}); return; }
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:ORNG});
}

/* ---------- signature helpers ---------- */
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + descH(C, t, w, F); }
function hdc(C, x, y, w, h, t, F, o){ var bh = blockH(C, t, w, F), yy = y + Math.max(0, (h - bh) / 2); K.hd(C, x, yy, w, h - (yy - y), t, F, o); }
var SKYS = ['#D6E6F8', '#A9D2F1', '#8CC8EC', '#D5D3EE', '#FBDDB0', '#F7C95A', '#F9A65A', '#F58A1F'];
function skyAt(t){ t = Math.max(0, Math.min(1, t)) * (SKYS.length - 1); var i = Math.floor(t), f = t - i; return i >= SKYS.length - 1 ? SKYS[SKYS.length - 1] : u.mix(SKYS[i], SKYS[i + 1], f); }
function sun(C, cx, cy, r, c, rays){
  K.seg(C, cx, cy, 0, r, 180, 360, {fill:c});
  if (rays) for (var a = 195; a <= 345; a += 25) { var p0 = K.pol(cx, cy, r + 8, a), p1 = K.pol(cx, cy, r + 8 + rays, a); C.line(p0[0], p0[1], p1[0], p1[1], {color:c, lw:3}); }
}
function starBadge(C, cx, cy, d, k, fill){
  C.ell(cx - d / 2, cy - d / 2, d, d, {fill:fill || NAVY});
  C.text(cx - d / 2, cy - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.38), wt:800, color:K.on(fill || NAVY), align:'c', va:'m', lh:1, caps:false, ls:0});
  K.spark(C, cx + d * 0.42, cy - d * 0.42, d * 0.3, SUN, 1);
}

/* 1. launch trajectory: stars along a rising arc, each text on its own altitude band */
function launchArc(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var bh = Z.h / n, aw = Math.min(Z.w * 0.4, 440), pts = [];
  for (var k = 0; k < n; k++) { var t = n === 1 ? 0.5 : k / (n - 1); pts.push([Z.x + 30 + aw * Math.pow(t, 0.55), Z.y + Z.h - bh * (k + 0.5)]); }
  var tr = [[Z.x + 4, Z.y + Z.h + 10]].concat(pts).concat([[Z.x + 30 + aw + 60, Z.y - 6]]);
  K.curve(C, tr, {stroke:ORNG, lw:3});
  K.curve(C, tr.map(function(p){ return [p[0] - 10, p[1] + 8]; }), {stroke:SUN, lw:1.5, lop:0.8});
  var tx = Z.x + 30 + aw + 90, tw = Z.x + Z.w - tx;
  var F = K.fitHD(C, it, tw - 70, bh * 0.45, bh - 8, {hMax:20, dMax:16, hMin:13, dMin:11, hLines:1});
  it.forEach(function(t, k){
    var p = pts[k], c = [ORNG, SUN, SKY, NAVY][k % 4], y = Z.y + Z.h - bh * (k + 1);
    K.spark(C, p[0], p[1], 20, c, 1); C.ell(p[0] - 4, p[1] - 4, 8, 8, {fill:'#FFFFFF'});
    C.line(p[0] + 24, p[1], tx - 12, p[1], {color:'#B8C6E0', lw:1.5, dash:true});
    starBadge(C, tx + 20, p[1], 40, k, k === n - 1 ? ORNG : NAVY);
    hdc(C, tx + 58, y, tw - 60, bh, t, F, {tc:NAVY});
  });
}

/* 2. star-badge cards */
function starGrid(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : Math.ceil(n / 2))), rows = Math.ceil(n / cols);
  var gx = 20, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, pad = 22, bd = rows > 1 ? 40 : 50;
  var H2 = hasH(it), top = pad + bd + 16;
  var F = H2 ? K.fitHD(C, it, cw - pad * 2, 56, ch - top - pad - 8, {hMax:19, dMax:15.5, hMin:13, dMin:11}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - top - pad, {font:'head', max:20, min:13, maxLines:2});
  var zy = Z.y;
  if (rows === 1 || !H2) { var mb = 0; it.forEach(function(t){ mb = Math.max(mb, H2 ? blockH(C, t, cw - pad * 2, F) : ls * 2.3); }); var need = top + mb + pad + 24; if (need < ch) { zy = Z.y + (ch - need) * rows * 0.4; ch = need; } }
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + gx) + (cols - inRow) * (cw + gx) / 2, y = zy + r * (ch + gy);
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:12, shadow:0.07});
    C.rect(x, y + ch - 6, cw, 6, {fill:[ORNG, SUN, SKY, NAVY][k % 4], r:3});
    K.spark(C, x + cw - 30, y + 28, 12, '#D2DFF3', 1);
    starBadge(C, x + pad + bd / 2, y + pad + bd / 2 + 4, bd, k, k % 2 ? ORNG : NAVY);
    if (H2) K.hd(C, x + pad, y + top, cw - pad * 2, ch - top - pad, t, F, {tc:NAVY});
    else C.text(x + pad, y + top, cw - pad * 2, ch - top - pad, t.d, {font:'head', size:ls, color:NAVY, lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
  });
}

/* 3. sunrise bands: rows shading from pale sky to orange, sun rising at the end */
function horizonBands(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = n > 6 && Z.w > 800 ? 2 : 1, per = Math.ceil(n / cols), gx = 24, cw = (Z.w - gx * (cols - 1)) / cols, gy = 6, rh = (Z.h - gy * (per - 1)) / per;
  var nw = Math.min(90, rh * 1.3), tw = cw - nw - 40, H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, tw, rh * 0.45, rh - 8, {hMax:19, dMax:15.5, hMin:13, dMin:11, hLines:1}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 6, {font:'head', max:n <= 4 ? 26 : 20, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * (rh + gy), col = skyAt(n === 1 ? 0 : k / (n - 1));
    C.rect(x, y, cw, rh, {fill:col, r:8});
    C.text(x + 10, y, nw, rh, u.pad2(k + 1), {font:'head', size:Math.min(34, rh * 0.5), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0, op:0.9});
    C.rect(x + nw + 14, y + rh * 0.22, 2, rh * 0.56, {fill:NAVY, op:0.35});
    if (H2) hdc(C, x + nw + 30, y, tw - 60, rh, t, F, {tc:NAVY, mc:'#27375E'});
    else C.text(x + nw + 30, y, tw - 60, rh, t.d, {font:'head', size:ls, color:NAVY, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    K.spark(C, x + cw - 30, y + rh / 2, Math.min(12, rh * 0.22), '#FFFFFF', 0.85);
  });
}

/* 4. half suns rising on a horizon, number inside */
function sunStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, iw = cw - 24;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 50, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 40, {font:'body', max:15, min:11, maxLines:2});
  var bot = ls * 1.1 * 2 + 16 + (st.some(function(t){ return t.d; }) ? ds * 2.8 + 6 : 0) + 20;
  var R = Math.min(cw * 0.42, Z.h - bot - 40, 190), hy = Z.y + Math.max(R + 36, (Z.h - bot + R + 36) / 2);
  var vs = C.groupFit(st.map(function(t){ return t.v; }), R * 1.4, R * 0.5, {font:'head', max:56, min:20, maxLines:1});
  C.rect(Z.x, hy, Z.w, 3, {fill:NAVY});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = [ORNG, SUN, SKY, '#2E4D8F'][k % 4];
    sun(C, cx, hy, R, c, 16);
    C.text(cx - R * 0.8, hy - R * 0.62, R * 1.6, R * 0.55, t.v, {font:'head', size:vs, color:K.on(c) === '#FFFFFF' && c !== SKY ? '#FFFFFF' : NAVY, align:'c', va:'m', lh:1, caps:false, ls:0, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + cw * k + 12, hy + 18, iw, ls * 2.3, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.1, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(Z.x + cw * k + 12, hy + 18 + ls * 2.3 + 4, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 5. departure board: split-flap value tiles on a navy board */
function boardStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:NAVY, r:14});
  var pad = 28, hy = 34, rh = (Z.h - pad * 2 - hy) / n, maxc = 1;
  st.forEach(function(t){ maxc = Math.max(maxc, String(t.v).length); });
  var tH = Math.min(rh - 16, 84), bw = Math.min(Z.w * 0.4, 340), tW = 0, lx = Z.x + pad, lw = Z.w - pad * 3 - bw;
  C.text(lx, Z.y + 20, 300, 18, 'Metric', {font:'body', size:12, wt:800, color:SUN, caps:true, ls:0.16, lh:1});
  C.text(Z.x + Z.w - pad - bw, Z.y + 20, bw, 18, 'Value', {font:'body', size:12, wt:800, color:SUN, caps:true, ls:0.16, lh:1, align:'r'});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), lw, rh * 0.5, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), lw, rh * 0.35, {font:'body', max:15, min:11, maxLines:1});
  var vs = C.groupFit(st.map(function(t){ return t.v; }), bw - 24, tH * 0.8, {font:'head', max:Math.round(tH * 0.62), min:18, maxLines:1, ls:0.04});
  st.forEach(function(t, k){
    var y = Z.y + pad + hy + k * rh, cy = y + rh / 2;
    if (k) C.rect(lx, y, Z.w - pad * 2, 1, {fill:'#FFFFFF', op:0.15});
    K.spark(C, lx + 8, cy, 8, k === 0 ? ORNG : SKY, 1);
    var two = !!t.d, lh = ls * 1.2;
    C.text(lx + 26, two ? cy - lh - 1 : cy - lh / 2, lw - 26, lh, t.l, {font:'head', size:ls, color:'#FFFFFF', lh:1.15, va:two ? 'b' : 'm', ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:1});
    if (two) C.text(lx + 26, cy + 4, lw - 26, ds * 1.5, t.d, {font:'body', size:ds, color:'#AFC0E0', lh:1.3, ed:{f:'x-part', i:t.i, p:2}, maxLines:1});
    var fx = Z.x + Z.w - pad - bw, fy = cy - tH / 2;
    C.rect(fx, fy, bw, tH, {fill:'#1C3673', r:6});
    C.rect(fx, fy, bw, tH / 2, {fill:'#23407F', r:6});
    C.rect(fx, fy + tH / 2 - 1, bw, 2, {fill:NAVY});
    C.rect(fx - 3, fy + tH / 2 - 5, 6, 10, {fill:'#0A1A40', r:2}); C.rect(fx + bw - 3, fy + tH / 2 - 5, 6, 10, {fill:'#0A1A40', r:2});
    C.text(fx + 8, fy, bw - 16, tH, t.v, {font:'head', size:vs, color:k === 0 ? ORNG : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0.04, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
  });
}

/* 6. sun path: stars on an arc over the horizon, notes below the horizon */
function skyArc(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var hy = Z.y + Z.h * 0.5, cx = Z.x + Z.w / 2, Rx = Z.w / 2 - 20, Ry = hy - Z.y - 10, cw = Z.w / n, pts = [];
  var arc = []; for (var a = 180; a <= 360; a += 6) arc.push([cx + Rx * Math.cos(a * Math.PI / 180), hy + Ry * Math.sin(a * Math.PI / 180)]);
  K.curve(C, arc, {stroke:'#F7C95A', lw:2.5});
  sun(C, cx, hy, Math.min(90, Ry * 0.55), '#FBD98A', 0);
  C.rect(Z.x, hy - 1.5, Z.w, 3, {fill:NAVY});
  var F = K.fitHD(C, it, cw - 20, 48, Z.y + Z.h - hy - 44, {hMax:20, dMax:16, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var ang = 180 + 180 * (k + 0.5) / n, px = cx + Rx * Math.cos(ang * Math.PI / 180), py = hy + Ry * Math.sin(ang * Math.PI / 180), colx = Z.x + cw * (k + 0.5), c = [ORNG, SUN, SKY, NAVY][k % 4];
    C.line(px, py + 20, colx, hy + 14, {color:'#B8C6E0', lw:1.5, dash:true});
    K.spark(C, px, py, 26, c, 1); C.ell(px - 5, py - 5, 10, 10, {fill:'#FFFFFF'});
    C.ell(colx - 7, hy - 7, 14, 14, {fill:'#FFFFFF', line:NAVY, lw:3});
    K.hd(C, Z.x + cw * k + 10, hy + 26, cw - 20, Z.y + Z.h - hy - 26, t, F, {align:'c', tc:k === n - 1 ? ORNG : NAVY});
  });
}

/* 7. day / night split */
function dayNight(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var g = 20, cw = (Z.w - g * (n - 1)) / n, pad = cw > 400 ? 40 : 26, iw = cw - pad * 2;
  var F = K.fitHD(C, it, iw, 80, Z.h - pad * 2 - 110, {hMax:26, dMax:18, hMin:14, dMin:11.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), night = k === 1;
    C.rect(x, Z.y, cw, Z.h, night ? {fill:NAVY, r:16} : {grad:{ang:180, stops:[[0, '#CFE4F7'], [100, '#FDE7C4']]}, r:16});
    if (night) { [[0.78, 0.18, 14], [0.9, 0.34, 8], [0.66, 0.1, 6], [0.84, 0.72, 9]].forEach(function(q){ K.spark(C, x + cw * q[0], Z.y + Z.h * q[1], q[2], q[2] > 10 ? SUN : '#FFFFFF', 0.9); }); C.ell(x + cw - 96, Z.y + 30, 50, 50, {fill:'#F4F1E4'}); C.ell(x + cw - 82, Z.y + 22, 50, 50, {fill:NAVY}); }
    else { sun(C, x + cw - 90, Z.y + 96, 52, ORNG, 14); }
    K.pill(C, x + pad, Z.y + pad, 96, 32, (night ? 'Next' : 'Now'), {fill:night ? ORNG : NAVY, color:'#FFFFFF', size:13, wt:800, caps:true, ls:0.1});
    hdc(C, x + pad, Z.y + pad + 80, iw, Z.h - pad * 2 - 80, t, F, {tc:night ? '#FFFFFF' : NAVY, mc:night ? '#C9D5EE' : '#34456E'});
  });
}

/* 8. vertical winding road with pins, rows alongside */
function roadList(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var rw = Math.min(170, Z.w * 0.17), gy = 8, rh = (Z.h - gy * (n - 1)) / n, x0 = Z.x + rw / 2, amp = rw * 0.28, pts = [];
  for (var i = 0; i <= 40; i++) { var t = i / 40; pts.push([x0 + amp * Math.sin(t * Math.PI * Math.max(2, n * 0.7)), Z.y - 10 + t * (Z.h + 20)]); }
  K.curve(C, pts, {stroke:NAVY, lw:24});
  for (var j = 0; j < 40; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:'#FFFFFF', lw:2, op:0.9});
  var tx = Z.x + rw + 30, tw = Z.x + Z.w - tx, H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, tw - 24, rh * 0.45, rh - 8, {hMax:20, dMax:16, hMin:13, dMin:11, hLines:1}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), tw - 24, rh - 6, {font:'head', max:n <= 4 ? 26 : 20, min:13, maxLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), cy = y + rh / 2, tt = (cy - Z.y + 10) / (Z.h + 20), px = x0 + amp * Math.sin(tt * Math.PI * Math.max(2, n * 0.7)), c = [ORNG, SUN, SKY][k % 3], d = Math.min(34, rh * 0.7);
    C.line(px + d / 2 + 4, cy, tx - 6, cy, {color:c, lw:2, dash:true});
    C.ell(px - d / 2 - 4, cy - d / 2 - 4, d + 8, d + 8, {fill:'#FFFFFF'});
    C.ell(px - d / 2, cy - d / 2, d, d, {fill:c});
    C.text(px - d / 2, cy - d / 2, d, d, String(k + 1), {font:'head', size:Math.round(d * 0.45), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.rect(tx, y, tw, rh, {fill:'#FFFFFF', r:10});
    C.rect(tx, y + 8, 4, rh - 16, {fill:c, r:2});
    if (H2) hdc(C, tx + 20, y, tw - 34, rh, t, F, {tc:NAVY});
    else C.text(tx + 20, y, tw - 34, rh, t.d, {font:'head', size:ls, color:NAVY, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
  });
}

/* 9. statement above the horizon with a rising sun */
function horizonStatement(C, Z, o){
  var P = C.P, t = C.it[0], hy = Z.y + Z.h - 30, R = Math.min(170, Z.h * 0.42, Z.w * 0.16), sx = Z.x + Z.w - R - 20;
  sun(C, sx, hy, R, SUN, 22); sun(C, sx, hy, R * 0.7, ORNG, 0);
  C.rect(Z.x, hy, Z.w, 4, {fill:NAVY});
  K.spark(C, sx - R - 60, hy - R - 10, 18, ORNG, 1); K.spark(C, sx - R - 20, hy - R - 50, 10, SKY, 1);
  var x = Z.x, w = sx - R - 90 - Z.x;
  if (!t) { C.text(x, Z.y, w, hy - Z.y - 30, C.s.subtitle || '', {font:'body', fit:{max:32, min:16}, wt:600, color:NAVY, va:'m', lh:1.35, ed:{f:'subtitle'}}); return; }
  if (t.h) K.pill(C, x, Z.y + 10, Math.min(w, t.h.length * 12 + 56), 36, t.h, {fill:ORNG, color:'#FFFFFF', size:14, wt:800, caps:true, ls:0.1, ed:{f:'x-part', i:t.i, p:0}});
  C.text(x, Z.y + (t.h ? 64 : 10), w, hy - Z.y - (t.h ? 64 : 10) - 30, t.d, {font:'body', fit:{max:34, min:16}, wt:600, color:NAVY, va:'m', lh:1.35, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'horizon', name:'Horizon Launch', tag:'Sky · orange · product', desc:'Pale sky pages with faint star shapes, navy roads and orange / sunflower tags for product lifecycles and launches.',
  fonts:{head:'Archivo', body:'Archivo', headW:800, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Archivo:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Winding lifecycle',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Centred + road',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Sunrise bands',{body:horizonBands}), V('n2','Winding road list',{body:roadList}), V('n3','Star cards',{body:starGrid}), V('v2','Road list + image right',{body:roadList, img:'right'}), V('n4','Numbered list',{body:'agList'})],
    text:[V('v1','Horizon statement',{body:horizonStatement}), V('n2','Statement card',{body:'text', box:true}), V('v2','Horizon + image right',{body:horizonStatement, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Now / next',{body:dayNight}), V('n2','Star cards',{body:starGrid}), V('n3','Sunrise bands',{body:horizonBands}), V('v2','Image left + road list',{body:roadList, img:'left'}), V('v3','Now / next + image right',{body:dayNight, img:'right'})],
    p3:[V('v1','Launch arc',{body:launchArc}), V('n2','Star cards',{body:starGrid}), V('n3','Sun path',{body:skyArc}), V('n4','Road',{body:'xRoad'}), V('n5','Sunrise bands',{body:horizonBands}), V('v2','Image left + road list',{body:roadList, img:'left'}), V('v3','Bands + image right',{body:horizonBands, img:'right'})],
    p4:[V('v1','Star cards',{body:starGrid}), V('n2','Launch arc',{body:launchArc}), V('n3','Sun path',{body:skyArc}), V('n4','Road',{body:'xRoad'}), V('n5','Sunrise bands',{body:horizonBands}), V('v2','Road list + image right',{body:roadList, img:'right'})],
    p56:[V('v1','Launch arc',{body:launchArc}), V('n2','Star cards',{body:starGrid}), V('n3','Sunrise bands',{body:horizonBands}), V('n4','Road',{body:'xRoad'}), V('n5','Winding road list',{body:roadList}), V('v2','Road list + image right',{body:roadList, img:'right'})],
    p78:[V('v1','Star cards',{body:starGrid}), V('n2','Sunrise bands',{body:horizonBands}), V('n3','Winding road list',{body:roadList}), V('v2','Bands + image right',{body:horizonBands, img:'right'})],
    stats:[V('v1','Rising suns',{body:sunStats}), V('n2','Departure board',{body:boardStats}), V('n3','Stat cards',{body:'statCards', icons:true}), V('v2','Image left + board',{body:boardStats, img:'left'}), V('v3','Suns + image right',{body:sunStats, img:'right'})],
    timeline:[V('v1','Sun path',{body:skyArc}), V('n2','Launch arc',{body:launchArc}), V('n3','Road',{body:'xRoad'}), V('v2','Road list + image right',{body:roadList, img:'right'})],
    quote:[V('v1','Starry quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
