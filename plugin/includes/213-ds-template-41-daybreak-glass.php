<?php
/**
 * Code Snippets export — DS Template 41 — Daybreak Glass
 *
 * Snippet ID : 997
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:17:59
 * Load order : 204 of 218
 */
/**
 * DS Template 41 — Daybreak Glass
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl41_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl41_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('daybreakglass', array(
        'name'     => "Daybreak Glass",
        'fonts'    => array('head' => "Urbanist", 'body' => "Figtree"),
        'pal'      => array('bg' => '#DCEBFF', 'text' => '#1B2547', 'accent' => '#FF6F4E', 'accent2' => '#3D6BF2'),
        'voice'    => "Fresh, optimistic voice: friendly, simple and energetic. Benefits first.",
        'recipe'   => "Hello, the problem, our product, how it works, features, traction, pricing or plan, thank you.",
        'generate' => 'ds_tpl41_generate',
        'build'    => 'ds_tpl41_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl41_generate') ) {
function ds_tpl41_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl41_build') ) {
function ds_tpl41_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-daybreakglass">
/* DesignStager template: daybreakglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 41 - Daybreak Glass : sky-to-peach morning gradient, clear glass windows, coral and blue chips */
var CO = '#FF6F4E', BL = '#3D6BF2', INK = '#1B2547', MUT = '#55607F';
var P0 = {bg:'#DCEBFF', text:INK, title:INK, muted:MUT, accent:CO, accent2:BL, card:'#FFFFFF', line:'#C9D6EE', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[CO, BL, '#FFB199', '#8FB0FF', '#F7C948', '#1B2547'],
  header:{kind:'left', top:60, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:BL, kPill:true, kGap:28, kSize:11.5},
  card:{kind:'glass', r:20, fill:'#FFFFFF', op:0.5, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.06, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:CO, dark:true, tc:'#FFFFFF', mc:'#FFE6DE'}},
  icon:{kind:'circle', bg:BL, fg:'#FFFFFF'}, num:{kind:'big', fg:CO},
  conc:{kind:'bar', barC:CO, label:'Takeaway', lc:CO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:20, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#BCD4FF'], [100, '#FFC9B5']]}}
};
function pane(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 20;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#FF8A63'], [100, '#F2553A']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#FFFFFF', 0.3], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#FFE6DE', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.82], [100, '#FFFFFF', 0.36]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.06});
  return {tc:INK, mc:MUT, ac:CO};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover, t = f.website || f.company || '';
  if (t) C.text(M, y, 500, 18, t, {font:'body', size:11.5, wt:600, color:MUT, lh:1, va:'m'});
  C.rect(W - M - 64, y - 5, 64, 28, {fill:'#FFFFFF', op:0.75, r:14, line:'#FFFFFF', lw:1});
  C.text(W - M - 64, y - 5, 64, 28, pad2(C.i + 1), {font:'head', size:12, wt:700, color:BL, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:155, stops:[[0, '#CFE4FF'], [55, '#EEF0FB'], [100, '#FFDCCB']]}});
  var k = (C.i || 0) % 3;
  C.ell([W - 260, -120, W - 200][k], [H - 240, H - 220, -140][k], 380, 380, {grad:{ang:135, stops:[[0, '#FFD3A8', 0.95], [100, '#FF8A63', 0.55]]}});
  C.ell([-140, W - 220, -100][k], [-150, -160, H - 200][k], 340, 340, {grad:{ang:135, stops:[[0, '#A9C7FF', 0.8], [100, '#6E93FF', 0.35]]}});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* glass windows with a title bar of three dots */
function windows(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 24, bar = 34, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - bar, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + bar + pad * 2 + 4), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = pane(C, x, y, cw, ch2, {hot:hot});
    [CO, '#F7C948', BL].forEach(function(col, q){ C.ell(x + pad + q * 16, y + 14, 9, 9, {fill:hot ? '#FFFFFF' : col, op:hot ? 0.9 - q * 0.25 : 1}); });
    C.rect(x + 1, y + bar, cw - 2, 1, {fill:hot ? '#FFFFFF' : INK, op:hot ? 0.35 : 0.1});
    C.icon(x + cw - pad - 16, y + 9, 16, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? '#FFFFFF' : BL);
    K.hd(C, x + pad, y + bar + pad - 4, tw, ch2 - bar - pad * 2 + 4, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* chip list: every heading sits in a coloured chip, the text runs under it, no boxes */
function chips(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 46, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), tw = cw - 8;
  var hasH = it.some(function(t){ return t.h; });
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), tw - 60, 26, {font:'head', max:16, min:11.5, maxLines:1}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), hasH ? tw : tw - 60, rh - (hasH ? 54 : 20), {font:'body', max:hasH ? (cols === 1 ? 18.5 : 15.5) : 21, min:10.5, lh:1.4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, col = k % 2 ? BL : CO;
    if (hasH) {
      var pw = Math.min(tw, String(t.h || pad2(k + 1)).length * hs * 0.6 + 70);
      C.rect(x, y + 6, pw, 32, {fill:col, r:16});
      C.text(x + 12, y + 6, 26, 32, pad2(k + 1), {font:'head', size:11.5, wt:800, color:'#FFFFFF', va:'m', lh:1, op:0.85});
      C.text(x + 40, y + 6, pw - 54, 32, t.h || '', {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x + 4, y + 48, tw, rh - 54, t.d, {font:'body', size:ds, color:INK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      C.rect(x, y + (rh - 34) / 2, 46, 34, {fill:col, r:17});
      C.text(x, y + (rh - 34) / 2, 46, 34, pad2(k + 1), {font:'head', size:13, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
      C.text(x + 62, y, tw - 60, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.4, ed:{f:'content-plain', i:t.i}});
    }
  });
}
/* hub: four panes around a centre disc */
function hub(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4 || Z.w < 760 || Z.h < 300) return windows(C, Z, o);
  var g = 20, cw = (Z.w - g) / 2, ch = (Z.h - g) / 2, pad = 26, hd = Math.min(130, ch * 0.7), tw = cw - pad * 2 - hd / 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2, {hMax:21, dMax:15, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var c = k % 2, r = Math.floor(k / 2), x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), cc = pane(C, x, y, cw, ch, {});
    K.hd(C, c ? x + pad + hd / 2 : x + pad, y + pad, tw, ch - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2;
  C.ell(cx - hd / 2 - 8, cy - hd / 2 - 8, hd + 16, hd + 16, {fill:'#FFFFFF', op:0.9});
  C.ell(cx - hd / 2, cy - hd / 2, hd, hd, {grad:{ang:135, stops:[[0, '#FF8A63'], [100, '#3D6BF2']]}});
  C.icon(cx - hd * 0.2, cy - hd * 0.2, hd * 0.4, D.iconFor(C.s, 0, C.s.title || ''), '#FFFFFF');
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: glass capsules with the number in a coloured end cap */
function caps(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 16, rh = Math.min((Z.h - g * (n - 1)) / n, 124), vals = st.map(sv), labs = st.map(sl), vw = Math.min(250, Z.w * 0.3), tw = Z.w - vw - 60;
  var vs = C.groupFit(vals, vw - 40, rh - 24, {font:'head', max:52, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, rh - 50, {font:'body', max:14, min:10.5, lh:1.36, maxLines:2});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var y = y0 + k * (rh + g), col = k % 2 ? BL : CO;
    pane(C, Z.x, y, Z.w, rh, {r:rh / 2});
    C.rect(Z.x + 6, y + 6, vw, rh - 12, {fill:col, r:(rh - 12) / 2});
    C.text(Z.x + 6 + 20, y + 6, vw - 40, rh - 12, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var lh = C.measureText(labs[k], tw, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.x ? C.measureText(t.x, tw, {font:'body', max:ds, min:ds, lh:1.36, maxLines:2}).h : 0, by = y + Math.max(8, (rh - lh - (dh ? dh + 6 : 0)) / 2);
    C.text(Z.x + vw + 30, by, tw, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(Z.x + vw + 30, by + lh + 6, tw, dh + 4, t.x, {font:'body', size:ds, color:MUT, lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}
/* timeline: calendar pages, the heading on a coloured tab */
function pages(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return chips(C, Z, o);
  var g = 16, cw = (Z.w - g * (n - 1)) / n, tab = 54, pad = 18, tw = cw - pad * 2, ch = Math.min(Z.h, 330), y0 = Z.y + (Z.h - ch) * 0.3;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, tab - 12, {font:'head', max:22, min:12, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - tab - pad * 2, {font:'body', max:15.5, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), col = k % 2 ? BL : CO;
    pane(C, x, y0, cw, ch, {r:16});
    C.rect(x, y0, cw, tab, {fill:col, r:16}); C.rect(x, y0 + tab - 16, cw, 16, {fill:col});
    C.ell(x + cw * 0.28 - 5, y0 - 6, 10, 18, {fill:'#FFFFFF', line:col, lw:1.5}); C.ell(x + cw * 0.72 - 5, y0 - 6, 10, 18, {fill:'#FFFFFF', line:col, lw:1.5});
    C.text(x + pad, y0 + 4, tw, tab - 4, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(x + pad, y0 + tab + pad, tw, ch - tab - pad * 2, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  pane(C, Z.x, Z.y, Z.w, Z.h, {r:26}); var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:CO, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; C.ell(x, y, 24, 24, {grad:{ang:135, stops:[[0, '#FF8A63'], [100, '#3D6BF2']]}}); C.text(x + 34, y, 440, 24, f.company, {font:'head', size:15, wt:800, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function sun(C, cx, cy, d){
  C.ell(cx - d * 0.75, cy - d * 0.75, d * 1.5, d * 1.5, {fill:'#FFFFFF', op:0.35});
  C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:160, stops:[[0, '#FFE2A8'], [60, '#FF8A63'], [100, '#F2553A']]}});
  C.ell(cx - d * 0.32, cy - d * 0.4, d * 0.4, d * 0.26, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.6], [100, '#FFFFFF', 0]]}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 52);
  if (photo) { pane(C, 690, 80, 520, 560, {r:30}); D.imgBox(C, 710, 100, 480, 520, {r:20}); }
  else { sun(C, 960, 300, 300); pane(C, 740, 380, 460, 220, {r:30}); [CO, '#F7C948', BL].forEach(function(col, q){ C.ell(770 + q * 18, 400, 10, 10, {fill:col}); }); if (f.website) C.text(770, 440, 400, 130, f.website, {font:'head', size:20, wt:700, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  var tw = photo ? 560 : 620;
  C.text(72, 170, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 64 : 76, min:32}, wt:800, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.03, ls:-0.02});
  if (C.s.subtitle) C.text(72, 436, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 52); C.rect(72, 556, pw, 48, {fill:INK, r:24}); C.text(72 + 26, 556, pw - 52, 48, who, {font:'body', size:14, wt:700, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 52);
  pane(C, 72, 120, W - 144, 500, {r:34});
  if (photo) D.imgBox(C, W - 72 - 420, 150, 390, 440, {r:22}); else sun(C, W - 300, 370, 250);
  var tw = 620;
  C.text(120, 160, tw, 150, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(120, 324, tw - 40, 62, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 410 + k * 60, col = k % 2 ? BL : CO;
    C.ell(120, y, 44, 44, {fill:col}); C.icon(120 + 12, y + 12, 20, c.icon, '#FFFFFF');
    C.text(120 + 60, y, tw - 80, 44, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  sun(C, 230, 520, 230);
  var te = C.text(72, 110, 400, 220, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 110 + te._h + 14, 380, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  pane(C, 520, 70, W - 520 - 72, H - 170, {r:28});
  chips(C, {x:556, y:92, w:W - 556 - 108, h:H - 214}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CO, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 640 : W - 144;
  if (photo) D.imgBox(C, 770, 100, 438, 500, {r:22});
  pane(C, x, 100, w, 500, {r:30});
  C.ell(x + 44, 136, 64, 64, {fill:CO}); C.text(x + 44, 148, 64, 64, '”', {font:'head', size:70, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 230, w - 92, 270, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:700, color:INK, lh:1.24, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 230 + qe._h + 24, w - 92, 24, bt, {font:'body', size:15, wt:700, color:BL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'daybreakglass', name:'Daybreak Glass', tag:'Light · clear glass',
  desc:'A sky-to-peach morning gradient with clear glass windows and coral and blue chips: fresh and optimistic for product, SaaS and startup decks.',
  fonts:{head:'Urbanist', body:'Figtree', headW:800, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Urbanist:wght@600;700;800|Figtree:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Sun + glass window', {}), V('v2', 'Photo in glass frame', {img:'photo'})],
    closing:[V('v1', 'Glass sheet + sun', {}), V('v2', 'Glass sheet + photo', {img:'photo'})],
    agenda:[V('v1', 'Sun + chip list in glass', {fn:agendaA}), V('n2', 'Glass windows', {body:windows}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass windows', {body:windows, hl:true}), V('n2', 'Chip list', {body:chips, cols:2}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + windows', {body:windows, img:'left', cols:1})],
    p3:[V('v1', 'Glass windows', {body:windows, hl:true}), V('n2', 'Chip list', {body:chips, cols:1}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + windows', {body:windows, img:'left', cols:1})],
    p4:[V('v1', 'Hub', {body:hub}), V('n2', 'Glass windows', {body:windows}), V('n3', 'Chip list', {body:chips}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + windows', {body:windows, img:'left', cols:2})],
    p56:[V('v1', 'Glass windows', {body:windows}), V('n2', 'Chip list', {body:chips}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1}), V('v3', 'Image left + chip list', {body:chips, img:'left', cols:1})],
    p78:[V('v1', 'Chip list', {body:chips}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1})],
    stats:[V('v1', 'Glass capsules', {body:caps}), V('n2', 'Progress rings', {body:'statRings', track:'#D6E0F5'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Capsules + image right', {body:caps, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Calendar pages', {body:pages}), V('n2', 'Chip list', {body:chips}), V('n3', 'Glass windows', {body:windows}), V('v2', 'Chip list + image right', {body:chips, img:'right', cols:1})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1051);
