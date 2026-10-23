<?php
/**
 * Code Snippets export — DS Template 56 — Frost Orbit
 *
 * Snippet ID : 1033
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:01:12
 * Load order : 230 of 242
 */
/**
 * DS Template 56 — Frost Orbit
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2" and "DS Template Frosted Glass" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl56_generate)
 *  - Layouts:    frosted glass panels on any side or corner, over spheres or over a full / half photo (JS below)
 *  - Download:   ds_tpl56_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('frostorbit', array(
        'name'     => "Frost Orbit",
        'fonts'    => array('head' => "Gabarito", 'body' => "Instrument Sans"),
        'pal'      => array('bg' => '#070716', 'text' => '#FFFFFF', 'accent' => '#D6FF3F', 'accent2' => '#5A46FF'),
        'voice'    => "Bold, modern and precise, with a design-studio confidence. Short punchy sentences, one clear idea per point, no filler.",
        'recipe'   => "Open with a striking statement, name the challenge, present the idea and how it works in a few clear moves, prove it with numbers, close with a confident next step.",
        'generate' => 'ds_tpl56_generate',
        'build'    => 'ds_tpl56_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl56_generate') ) {
function ds_tpl56_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl56_build') ) {
function ds_tpl56_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-frostorbit">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 56 - Frost Orbit : near-black page, vivid orange and violet spheres with gold rings, frosted glass panels that blur whatever lies under them */
var BG = '#070716', OR = '#FF6A1F', OR2 = '#B81D05', VI = '#5A46FF', VI2 = '#150A9E', GO = '#F4B93A', LI = '#D6FF3F', TX = '#FFFFFF', MU = '#C3C5DC';
var P0 = {bg:BG, text:TX, title:TX, muted:MU, accent:LI, accent2:VI, card:'#17172E', line:'#34345A', onAccent:'#0A0A18'};
var S = {
  margin:72, series:[LI, OR, VI, '#8F84FF', '#FFB08A', '#FFFFFF'],
  header:{kind:'left', top:58, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'glass', r:26, fill:'#FFFFFF', op:0.09, line:'#FFFFFF', lop:0.45, lw:1.3, shadow:0, dark:true, tc:TX, mc:MU,
        hl:{kind:'glass', fill:'#FFFFFF', op:0.18, dark:true, tc:TX, mc:MU}},
  icon:{kind:'plain', fg:LI}, num:{kind:'big', fg:LI},
  conc:{kind:'box', fill:'#FFFFFF', op:0.09, r:22, line:'#FFFFFF', lw:1.2, label:'Takeaway', lc:LI, tc:TX},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:140, stops:[[0, '#3B2FBF'], [55, '#7A2A6B'], [100, '#E0561B']]}}
};
/* a lit sphere and a gold ring */
function ball(C, cx, cy, r, a, b){ C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:150, stops:[[0, a], [100, b]]}}); D.glow(C, cx - r * 0.3, cy - r * 0.34, r * 0.6, r * 0.48, '#FFFFFF', 0.5); }
function ring(C, cx, cy, r){ C.ell(cx - r, cy - r, r * 2, r * 2, {line:GO, lw:Math.max(5, r * 0.26)}); C.ell(cx - r * 0.82, cy - r * 0.82, r * 1.64, r * 1.64, {line:'#FFE9B0', lw:1.5, lop:0.85}); }
/* the objects sit on the corners of the glass box R: half outside (sharp), half under the glass (blurred) */
function orbs(C, R, k, small){
  var m = Math.min(R.w, R.h) * (small ? 0.6 : 1), f = (k || 0) % 3;
  var a = f === 1 ? [R.x + R.w * 0.9, R.y + R.h * 0.08] : (f === 2 ? [R.x + R.w * 0.08, R.y + R.h * 0.9] : [R.x + R.w * 0.1, R.y + R.h * 0.1]);
  var b = f === 1 ? [R.x + R.w * 0.1, R.y + R.h * 0.92] : (f === 2 ? [R.x + R.w * 0.9, R.y + R.h * 0.1] : [R.x + R.w * 0.9, R.y + R.h * 0.9]);
  ball(C, a[0], a[1], m * 0.3, '#FF9A4A', OR2);
  ball(C, b[0], b[1], m * 0.34, '#8C7BFF', VI2);
  ball(C, a[0] + m * 0.36, a[1] - m * 0.16 * (a[1] < R.y + R.h / 2 ? 1 : -1), m * 0.055, '#8C7BFF', VI2);
  ball(C, b[0] - m * 0.4, b[1] + m * 0.14 * (b[1] > R.y + R.h / 2 ? 1 : -1), m * 0.05, '#FF9A4A', OR2);
  ring(C, a[0] > R.x + R.w / 2 ? R.x + R.w : R.x, R.y + R.h * (f === 2 ? 0.34 : 0.56), m * 0.085);
  ring(C, b[0] > R.x + R.w / 2 ? R.x + R.w : R.x, R.y + R.h * (f === 2 ? 0.6 : 0.3), m * 0.07);
}
/* frosted glass: a dark veil (this is the layer that receives the blurred backdrop) and a bright rim */
function glass(C, x, y, w, h, r, o){
  o = o || {};
  var e = C.rect(x, y, w, h, {fill:'#080818', op:o.veil != null ? o.veil : 0.3, r:r}); e.frost = o.blur || 26;
  var f = C.rect(x, y, w, h, {fill:'#FFFFFF', op:o.op != null ? o.op : 0.07, line:'#FFFFFF', lop:0.5, lw:1.3, r:r}); f.frost = 0;
  var s = C.rect(x + r, y + 1, Math.max(10, w * 0.45 - r), 1.5, {fill:'#FFFFFF', op:0.55}); s.frost = 0;
}
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  ball(C, x + 11, y + 11, 11, '#8C7BFF', VI2);
  C.text(x + 32, y, 420, 22, f.company, {font:'head', size:14, wt:700, color:TX, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  ball(C, M + 8, y + 8, 8, '#8C7BFF', VI2);
  C.text(M + 26, y, 500, 16, f.company || f.website || '', {font:'head', size:12.5, wt:600, color:TX, lh:1, va:'m'});
  var e = C.rect(W - M - 62, y - 7, 62, 30, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.4, lw:1, r:15}); e.frost = 0;
  C.text(W - M - 62, y - 7, 62, 30, pad2(C.i + 1), {font:'head', size:12.5, wt:700, color:TX, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:BG});
  D.glow(C, 240, 80, 520, 380, '#2B2470', 0.5); D.glow(C, 1120, 700, 520, 360, '#5A1D10', 0.42);
  if (kind === 'cover' || kind === 'closing' || kind === 'quote' || (C.o && C.o.fn)) return;
  orbs(C, {x:120, y:190, w:1040, h:420}, C.i || 0, false);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function num(C, x, y, k, s){ C.text(x, y, 60, s * 1.2, pad2(k + 1), {font:'head', size:s, wt:800, color:LI, lh:1, caps:false}); }
/* ---------- bodies made of several glass boxes ---------- */
function cols(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
  var g = 20, off = Math.min(24, Z.h * 0.06), cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 50;
  var F = K.fitHD(C, it, tw, 54, Z.h - off - top - pad * 2, {hMax:20, dMax:15, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - off, top + bmx + pad * 2 + 8), y0 = Z.y + Math.max(0, (Z.h - off - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = y0 + (k % 2 ? off : 0);
    glass(C, x, y, cw, ch, 24);
    num(C, x + pad, y + pad - 2, k, 24);
    K.hd(C, x + pad, y + pad + top - 8, tw, ch - top - pad * 2 + 8, t, F, {tc:TX, mc:MU});
  });
}
function rows(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 112), nw = 70, pad = 24, tw = Z.w - nw - pad * 2;
  var F = K.fitHD(C, it, tw, 30, rh - 20, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 14.5 : 19, hMin:12.5, dMin:10.5});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    glass(C, Z.x, y, Z.w, rh, Math.min(24, rh / 2));
    C.text(Z.x + pad, y, nw - 10, rh, pad2(k + 1), {font:'head', size:Math.min(26, rh * 0.34), wt:800, color:LI, va:'m', lh:1, caps:false});
    K.hd(C, Z.x + pad + nw, y + Math.max(8, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:TX, mc:MU});
  });
}
function grid(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cl = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cl = Math.min(cl, 2);
  var rw = Math.ceil(n / cl), g = 16, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = 20, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 26, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 32), tot = ch2 * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cl), c = k % cl, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    glass(C, x, y, cw, ch2, 20);
    num(C, x + pad, y + pad - 3, k, 15);
    K.hd(C, x + pad, y + pad + 22, tw, ch2 - pad * 2 - 22, t, F, {tc:TX, mc:MU});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function statCards(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cl = Z.w < 760 ? (n > 2 ? 2 : 1) : n, rw = Math.ceil(n / cl), g = 20, cw = (Z.w - g * (cl - 1)) / cl, ch = Math.min((Z.h - g * (rw - 1)) / rw, 300), pad = 26, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, tw, ch * 0.4, {font:'head', max:68, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs * 1.1 - 110, {font:'body', max:14, min:10.5, lh:1.4});
  var tot = ch * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var r = Math.floor(k / cl), c = k % cl, x = Z.x + c * (cw + g), y = y0 + r * (ch + g);
    glass(C, x, y, cw, ch, 26);
    C.text(x + pad, y + pad, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:LI, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + vs * 1.1 + 10, 40, 3, {fill:'#FFFFFF', op:0.7});
    var le = C.text(x + pad, y + pad + vs * 1.1 + 26, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:TX, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, y + pad + vs * 1.1 + 34 + le._h, tw, ch - (pad * 2 + vs * 1.1 + 34 + le._h), t.x, {font:'body', size:ds, color:MU, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function steps(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
  var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 18, tw = cw - pad * 2, st = Math.min(26, Z.h * 0.32 / (n - 1)), ch = Z.h - st * (n - 1);
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 34, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - 84, {font:'body', max:14.5, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + k * st;
    glass(C, x, y, cw, ch, 22);
    C.text(x + pad, y + pad, tw, 34, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:LI, lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + 42, 34, 3, {fill:'#FFFFFF', op:0.7});
    C.text(x + pad, y + pad + 58, tw, ch - pad * 2 - 58, t.d, {font:'body', size:ds, color:TX, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  glass(C, Z.x, Z.y, Z.w, Z.h, 30);
  var pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
  C.rect(Z.x + pad, y, 46, 5, {fill:LI, r:3}); y += 28;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:LI, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:TX, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
/* ---------- one glass panel that holds the whole slide: it can sit anywhere, over a photo or over the spheres ---------- */
var RECTS = {l:[56, 56, 600, 596], r:[624, 56, 600, 596], t:[56, 48, 1168, 336], b:[56, 320, 1168, 332], c:[190, 76, 900, 556], tl:[56, 56, 680, 420], tr:[544, 56, 680, 420], bl:[56, 232, 680, 420], br:[544, 232, 680, 420]};
function place(C, o){
  var ph = o.ph, a = RECTS[o.pos || 'l'] || RECTS.l, R = {x:a[0], y:a[1], w:a[2], h:a[3]};
  if (ph === 'full') D.imgBox(C, 0, 0, W, H, {r:0});
  else if (ph === 'left') { D.imgBox(C, 0, 0, 600, H, {r:0}); R = {x:470, y:64, w:754, h:580}; }
  else if (ph === 'right') { D.imgBox(C, 680, 0, 600, H, {r:0}); R = {x:56, y:64, w:754, h:580}; }
  else if (ph === 'top') { D.imgBox(C, 0, 0, W, 350, {r:0}); R = {x:90, y:230, w:1100, h:422}; }
  else if (ph === 'bottom') { D.imgBox(C, 0, 370, W, 350, {r:0}); R = {x:90, y:56, w:1100, h:422}; }
  orbs(C, R, (C.i || 0) + (o.pos === 'r' || o.pos === 'tr' ? 1 : 0), !!ph);
  glass(C, R.x, R.y, R.w, R.h, 30, {veil:ph ? 0.42 : 0.3});
  return R;
}
function listIn(C, Z){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var wide = Z.w > 860, cl = wide ? (n <= 4 ? n : Math.ceil(n / 2)) : (n > 4 && Z.w > 520 ? 2 : 1), rw = Math.ceil(n / cl), gx = 28, cw = (Z.w - gx * (cl - 1)) / cl, ch = Math.min(Z.h / rw, cl === 1 ? 104 : 190);
  if (C.kind === 'stats') {
    var vals = it.map(sv), labs = it.map(sl), vs = C.groupFit(vals, cw, ch * 0.5, {font:'head', max:54, min:20, maxLines:1}), ls = C.groupFit(labs, cw, Math.max(24, ch - vs * 1.15 - 10), {font:'body', max:15, min:11, lh:1.3, maxLines:3});
    it.forEach(function(t, k){
      var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
      C.text(x, y, cw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:LI, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, y + vs * 1.1 + 4, cw, Math.max(24, ch - vs * 1.15 - 10), labs[k], {font:'body', size:ls, wt:500, color:TX, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
    });
    return;
  }
  var nw = 40, tw = cw - nw, F = K.fitHD(C, it, tw, 30, ch - 14, {hMax:18, dMax:it.some(function(t){ return t.h; }) ? 14 : 17, hMin:12, dMin:10.5});
  it.forEach(function(t, k){
    var c = k % cl, r = Math.floor(k / cl), x = Z.x + c * (cw + gx), y = Z.y + r * ch;
    if (r && cl === 1) C.rect(x, y - 1, cw, 1, {fill:'#FFFFFF', op:0.22});
    num(C, x, y + (cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8) + 1, k, 14);
    K.hd(C, x + nw, y + (cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8), tw, ch - 12, t, F, {tc:TX, mc:MU});
  });
}
function scene(C, o){
  o = o || C.o || {};
  var R = place(C, o), pad = Math.min(40, Math.max(28, R.w * 0.05)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, wide = R.w > 860;
  C.rect(x, y, 46, 5, {fill:LI, r:3}); y += 22;
  var te = C.text(x, y, wide ? w * 0.7 : w, 100, C.s.title || '', {font:'head', fit:{max:wide ? 38 : 33, min:19}, wt:700, color:TX, lh:1.1, ed:{f:'title'}, maxLines:2}); y += te._h + 6;
  if (C.s.subtitle) { var se = C.text(x, y, wide ? w * 0.7 : w, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:MU, lh:1.35, ed:{f:'subtitle'}, maxLines:2}); y += se._h + 6; }
  y += 12;
  if (C.s.conclusion && bot - y > 230) { bot -= 50; C.rect(x, bot + 12, w, 1, {fill:'#FFFFFF', op:0.25}); C.text(x, bot + 18, w, 32, C.s.conclusion, {font:'body', fit:{max:14, min:11}, wt:600, color:LI, va:'m', lh:1.25, ed:{f:'x-conc'}, maxLines:2}); }
  listIn(C, {x:x, y:y, w:w, h:bot - y});
}
function quoteScene(C, o){
  o = o || C.o || {};
  var R = place(C, o), by = C.it[0], pad = Math.min(54, Math.max(34, R.w * 0.06)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, mid = (o.pos || 'c') === 'c' || o.pos === 't' || o.pos === 'b';
  C.text(mid ? x + w / 2 - 40 : x - 4, y - 16, 80, 80, '“', {font:'head', size:92, wt:800, color:LI, lh:1, align:mid ? 'c' : 'l', caps:false, ls:0}); y += 62;
  C.text(x, y, w, R.y + R.h - pad - y - (by ? 46 : 0), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 40 : 32, min:18}, wt:600, color:TX, lh:1.2, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:6});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, R.y + R.h - pad - 26, w, 26, bt, {font:'body', size:14, wt:600, color:LI, lh:1.1, align:mid ? 'c' : 'l', va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, mid = o.pos === 'c';
  if (f.company) { if (mid) C.text(x, y, w, 22, f.company, {font:'head', size:14, wt:700, color:LI, align:'c', va:'m', lh:1, caps:true, ls:0.14, ed:{f:'tf-company'}, maxLines:1}); else brand(C, x, y); }
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  '), th = bot - y - 40 - (who ? 50 : 0) - (C.s.subtitle ? 70 : 0);
  var te = C.text(x, y + 40, w, Math.max(80, th), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 66 : 54, min:26}, wt:800, color:TX, lh:1.04, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:R.h < 360 ? 2 : 4});
  var sy = y + 40 + Math.max(80, th) + 8;
  if (C.s.subtitle) C.text(x, sy, w, 62, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, align:mid ? 'c' : 'l', ed:{f:'subtitle'}, maxLines:3});
  if (who) { if (!mid) C.rect(x, bot - 30, 34, 3, {fill:LI}); C.text(x + (mid ? 0 : 46), bot - 40, w - (mid ? 0 : 46), 24, who, {font:'head', size:14, wt:700, color:TX, lh:1, va:'m', align:mid ? 'c' : 'l', ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, wide = R.w > 860, cs = D.contacts(C), tw = wide ? w * 0.52 : w;
  C.rect(x, y, 46, 5, {fill:LI, r:3}); y += 24;
  var te = C.text(x, y, tw, wide ? R.h - pad * 2 - 110 : 150, C.s.title || 'Thank You', {font:'head', fit:{max:wide ? 70 : 58, min:28}, wt:800, color:TX, lh:1.04, va:wide ? 'm' : 't', ed:{f:'title'}, maxLines:2});
  var sy = wide ? R.y + R.h - pad - 70 : y + te._h + 10;
  if (C.s.subtitle) C.text(x, sy, tw, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cx = wide ? x + w * 0.58 : x, cw = wide ? w * 0.42 : w, n = cs.length, rh = Math.min(56, ((wide ? R.h - pad * 2 : R.y + R.h - pad - (sy + 80)) ) / Math.max(1, n)), cy = wide ? R.y + (R.h - rh * n) / 2 : R.y + R.h - pad - rh * n;
  cs.forEach(function(c, k){
    var yy = cy + k * rh;
    C.icon(cx, yy + rh / 2 - 11, 22, c.icon, LI);
    C.text(cx + 38, yy, cw - 38, rh, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:TX, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(cx, yy, cw, 1, {fill:'#FFFFFF', op:0.2});
  });
}
function quote(C, v){ quoteScene(C, C.o || {}); }
/* the same set of positions for every kind of slide */
function sc(fn, few){
  var a = [
    V('g1', 'Glass panel left', {fn:fn, pos:'l'}), V('g2', 'Glass panel right', {fn:fn, pos:'r'}), V('g3', 'Glass panel centre', {fn:fn, pos:'c'}),
    V('g4', 'Glass panel on top', {fn:fn, pos:'t'}), V('g5', 'Glass panel at the bottom', {fn:fn, pos:'b'}),
    V('f1', 'Full photo + glass left', {fn:fn, img:'photo', ph:'full', pos:'l'}), V('f2', 'Full photo + glass right', {fn:fn, img:'photo', ph:'full', pos:'r'}),
    V('f3', 'Full photo + glass at the bottom', {fn:fn, img:'photo', ph:'full', pos:'b'}), V('f4', 'Full photo + glass on top', {fn:fn, img:'photo', ph:'full', pos:'t'}),
    V('f5', 'Full photo + glass centre', {fn:fn, img:'photo', ph:'full', pos:'c'}),
    V('h1', 'Photo left half + glass', {fn:fn, img:'photo', ph:'left'}), V('h2', 'Photo right half + glass', {fn:fn, img:'photo', ph:'right'}),
    V('h3', 'Photo on top + glass below', {fn:fn, img:'photo', ph:'top'}), V('h4', 'Glass above + photo below', {fn:fn, img:'photo', ph:'bottom'})
  ];
  if (few) a = a.concat([
    V('c1', 'Full photo + glass top-left', {fn:fn, img:'photo', ph:'full', pos:'tl'}), V('c2', 'Full photo + glass top-right', {fn:fn, img:'photo', ph:'full', pos:'tr'}),
    V('c3', 'Full photo + glass bottom-left', {fn:fn, img:'photo', ph:'full', pos:'bl'}), V('c4', 'Full photo + glass bottom-right', {fn:fn, img:'photo', ph:'full', pos:'br'}),
    V('c5', 'Glass panel top-left', {fn:fn, pos:'tl'}), V('c6', 'Glass panel bottom-right', {fn:fn, pos:'br'})
  ]);
  return a;
}
function plain(list){ return list.map(function(v){ var o = {}; for (var k in v.o) if (k !== 'fn') o[k] = v.o[k]; return V(v.id, v.lbl, o); }); }
D.register({
  id:'frostorbit', name:'Frost Orbit', tag:'Dark · frosted glass · spheres', frost:true,
  desc:'Real frosted glass on a near-black page: orange and violet spheres and gold rings sit half under the glass and blur behind it, and a photo can fill the whole slide or half of it with the glass panel on any side or corner.',
  fonts:{head:'Gabarito', body:'Instrument Sans', headW:700, bodyW:400, headLh:1.08, gf:'Gabarito:wght@500;600;700;800|Instrument+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Glass panel left', {pos:'l'}), V('v2', 'Full photo + glass left', {img:'photo', ph:'full', pos:'l'})].concat(plain(sc(null, true).slice(1, 5)), plain(sc(null, true).slice(6))),
    closing:[V('v1', 'Glass panel centre', {pos:'c'}), V('v2', 'Full photo + glass centre', {img:'photo', ph:'full', pos:'c'})].concat(plain(sc(null, true).filter(function(v){ return v.id !== 'g3' && v.id !== 'f5'; }))),
    agenda:[V('v1', 'Glass rows', {body:rows}), V('n2', 'Glass grid', {body:grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene)),
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})].concat(sc(scene, true)),
    p2:[V('v1', 'Glass columns', {body:cols}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
    p3:[V('v1', 'Glass columns', {body:cols}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
    p4:[V('v1', 'Glass columns', {body:cols}), V('n2', 'Glass rows', {body:rows}), V('n3', 'Glass grid', {body:grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
    p56:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
    p78:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows, max:8}), V('v2', 'Glass grid + image right', {body:grid, img:'right', cols:2})].concat(sc(scene)),
    stats:[V('v1', 'Glass numbers', {body:statCards}), V('n2', 'Progress rings', {body:'statRings', track:'#34345A'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + image right', {body:statCards, img:'right'}), V('v3', 'Image left + numbers', {body:statCards, img:'left'})].concat(sc(scene, true)),
    timeline:[V('v1', 'Glass steps', {body:steps}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene)),
    quote:[V('v1', 'Glass quote centre', {pos:'c'}), V('v2', 'Full photo + glass centre', {img:'photo', ph:'full', pos:'c'}), V('v3', 'Image left + quote', {img:'photo', ph:'left'})].concat(plain(sc(null, true).filter(function(v){ return v.id !== 'g3' && v.id !== 'f5' && v.id !== 'h1'; })))
  }
});
})();
</script>
<?php
}, 1066);
