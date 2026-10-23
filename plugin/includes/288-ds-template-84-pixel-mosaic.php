<?php
/**
 * Code Snippets export — DS Template 84 — Pixel Mosaic
 *
 * Snippet ID : 1118
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 09:43:29
 * Load order : 288 of 296
 */
/**
 * DS Template 84 — Pixel Mosaic
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3" and "DS Template Kit 4" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl84_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl84_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('pixelmosaic', array(
        'name'     => "Pixel Mosaic",
        'fonts'    => array('head' => "Chivo", 'body' => "Chivo Mono"),
        'pal'      => array('bg' => '#0E3B3A', 'text' => '#FFFFFF', 'accent' => '#FFB547', 'accent2' => '#7FD1B9'),
        'voice'    => "Technical product voice: precise, data-literate and forward-looking.",
        'recipe'   => "The problem, the platform, how it works, adoption data, security, roadmap, results, contact.",
        'generate' => 'ds_tpl84_generate',
        'build'    => 'ds_tpl84_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl84_generate') ) {
    function ds_tpl84_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl84_build') ) {
    function ds_tpl84_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-pixelmosaic">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 84 - Pixel Mosaic : deep green, square pixel mosaics dissolving in amber and mint */
var BG = '#0E3B3A', BG2 = '#0A2A29', PN = '#14504E', AM = '#FFB547', MI = '#7FD1B9', WH = '#FFFFFF', MU = '#A9CFC4';
var P0 = {bg:BG, text:WH, title:WH, muted:MU, accent:AM, accent2:MI, card:PN, line:'#1F6461', onAccent:'#0A2A29'};
function rnd(i, j, s){ var v = Math.sin(i * 12.9898 + j * 78.233 + s * 3.7) * 43758.5453; return v - Math.floor(v); }
/* pixel field: cells fade by distance from an edge; drawn as four shapes (one per tone) */
function mosaic(C, x, y, cols, rows, cell, dir, seed){
  var buckets = [[], [], [], []], g = Math.max(2, cell * 0.14);
  for (var r = 0; r < rows; r++) for (var c = 0; c < cols; c++) {
    var t = dir === 'r' ? c / (cols - 1) : dir === 'l' ? 1 - c / (cols - 1) : dir === 'b' ? r / (rows - 1) : 1 - r / (rows - 1);
    var p = rnd(c, r, seed || 1); if (p > t * 1.1 + 0.05) continue;
    var b = p < 0.18 ? 0 : p < 0.45 ? 1 : p < 0.75 ? 2 : 3, px = x + c * cell, py = y + r * cell;
    buckets[b].push(['M', px, py], ['L', px + cell - g, py], ['L', px + cell - g, py + cell - g], ['L', px, py + cell - g], ['Z']);
  }
  var cols4 = [[AM, 1], [MI, 0.95], [MI, 0.45], [AM, 0.35]];
  buckets.forEach(function(d, k){ if (d.length) C.path(d, {fill:cols4[k][0], op:cols4[k][1]}); });
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  mosaic(C, W - 200, 0, 10, 5, 20, 'r', C.i || 1);
}
function head(C, A, o){
  var y = 50;
  C.text(A.x, y, A.w, 16, '// ' + pad2(C.i + 1) + ' — ' + (C.cover.company || 'data').toLowerCase(), {font:'body', size:12, wt:500, color:MI, lh:1, va:'m', maxLines:1});
  y += 24;
  var te = C.text(A.x, y, Math.min(A.w, 880), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:WH, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  for (var i = 0; i < 6; i++) C.rect(A.x + i * 12, y, 9, 9, {fill:i < 4 ? AM : MI, op:i < 4 ? 1 : 0.5});
  y += 20;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 780), 44, C.s.subtitle, {font:'head', fit:{max:15.5, min:12}, wt:400, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:WH, mc:MU, ac:AM, markS:42, g:16, head:head, ptc:WH, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:PN}); mosaic(C, x + w - 120, y, 6, 4, 20, 'r', 7); return {tc:WH, mc:MU, ac:AM}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:AM}); return {tc:BG2, mc:'#3B3A2A', ac:BG2}; }
    C.rect(x, y, w, h, {fill:PN}); mosaic(C, x, y, Math.max(3, Math.floor(w / 16)), 1, 16, 'l', k + 2);
    return {tc:WH, mc:MU, ac:k % 2 ? MI : AM};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? BG : PN}); return {tc:WH, mc:MU, ac:AM}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === BG2; C.rect(x, y, s, s, {fill:on ? BG2 : (k % 2 ? MI : AM)}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? AM : BG2); },
  rowMark:function(C, x, y, s, t, k){ var q = s / 2 - 2; for (var i = 0; i < 4; i++) C.rect(x + (i % 2) * (q + 4), y + Math.floor(i / 2) * (q + 4), q, q, {fill:i <= k % 4 ? AM : PN}); },
  node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? MI : AM}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'body', size:s * 0.32, wt:700, color:BG2, align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ var d = []; for (var x = x1; x < x2; x += 14) { d.push(['M', x, y - 3], ['L', x + 8, y - 3], ['L', x + 8, y + 3], ['L', x, y + 3], ['Z']); } C.path(d, {fill:MI, op:0.6}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 0, Z.y, W, Z.h, {r:0}); mosaic(C, 0, Z.y, 64, 2, 20, 't', 3); return; }
    D.imgBox(C, Z.x, 0, Z.w, H, {r:0});
    mosaic(C, side === 'right' ? Z.x : Z.x + Z.w - 200, 0, 10, 36, 20, side === 'right' ? 'l' : 'r', 4);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.website || '', {font:'body', size:11.5, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 100, y, 100, 16, '[' + pad2(C.i + 1) + '/' + pad2(C.total) + ']', {font:'body', size:12, wt:700, color:AM, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[AM, MI, '#FFD89A', '#3FA48C', '#FFFFFF', '#B6E7D9'], header:{kind:'left', top:50}, card:{kind:'solid', fill:PN, r:0}, icon:{kind:'square', bg:AM, fg:BG2, r:0}, num:{kind:'big', fg:AM},
  conc:{kind:'box', fill:BG2, r:0, label:'// result', lc:AM, tc:WH}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#3FA48C'], [100, '#0A2A29']]}}};
/* signature: pixel bars - each value drawn as a row of squares */
function pixelBars(C, Z, o){
  var st = C.it.slice(0, 5), n = st.length; if (!n) return;
  var rh = Z.h / n, lw = Math.min(330, Z.w * 0.32), vals = st.map(K3.sv), labs = st.map(K3.sl), cells = 20, cw = (Z.w - lw - 150) / cells;
  st.forEach(function(t, k){ var y = Z.y + k * rh, num = Math.abs(parseFloat(String(vals[k]).replace(/[^0-9.\-]/g, '')) || 0), pct = /%/.test(vals[k]) ? num / 100 : Math.min(1, 0.3 + 0.15 * (n - k));
    C.text(Z.x, y, lw - 20, rh, labs[k], {font:'head', fit:{max:17, min:11}, wt:700, color:WH, va:'m', lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    var on = Math.max(1, Math.round(pct * cells)), d1 = [], d2 = [], sz = Math.min(cw - 3, rh * 0.5);
    for (var c = 0; c < cells; c++) { var px = Z.x + lw + c * cw, py = y + (rh - sz) / 2; (c < on ? d1 : d2).push(['M', px, py], ['L', px + sz, py], ['L', px + sz, py + sz], ['L', px, py + sz], ['Z']); }
    C.path(d1, {fill:k === 0 ? AM : MI}); if (d2.length) C.path(d2, {fill:PN});
    C.text(Z.x + Z.w - 130, y, 130, rh, vals[k], {font:'body', fit:{max:30, min:14}, wt:700, color:k === 0 ? AM : WH, align:'r', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
  });
}
/* signature: heat grid - points in a grid of cells that warm up in order */
function heat(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (n < 3) return B.cards(C, Z, o);
  var cl = n <= 4 ? n : (n <= 6 ? 3 : 4), rw = Math.ceil(n / cl), g = 6, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = 22, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 30, {hMax:20, dMax:15, hMin:12, dMin:10});
  var tones = [AM, '#F7C873', '#C9D8A6', MI, '#4FB39A', '#2F8E7C', '#1E6F64', PN];
  it.forEach(function(t, k){ var x = Z.x + (k % cl) * (cw + g), y = Z.y + Math.floor(k / cl) * (ch + g), c = tones[Math.round(k * 7 / Math.max(1, n - 1))], dark = k < n / 2;
    C.rect(x, y, cw, ch, {fill:c});
    C.text(x + pad, y + pad - 4, 60, 22, pad2(k + 1), {font:'body', size:14, wt:700, color:dark ? BG2 : WH, lh:1});
    K.hd(C, x + pad, y + pad + 26, tw, ch - pad * 2 - 26, t, F, {tc:dark ? BG2 : WH, mc:dark ? '#1F3B36' : MU});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  if (o.img === 'photo') { D.imgBox(C, 640, 0, 640, H, {r:0}); mosaic(C, 640, 0, 14, 36, 20, 'l', 9); }
  else mosaic(C, 600, 0, 34, 36, 20, 'r', 9);
  if (f.company) C.text(72, 70, 500, 18, '// ' + f.company.toLowerCase(), {font:'body', size:13, wt:500, color:MI, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 130, 560, 260, C.s.title, {font:'head', fit:{max:64, min:28}, wt:800, color:WH, lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  for (var i = 0; i < 8; i++) C.rect(72 + i * 16, 410, 12, 12, {fill:i < 5 ? AM : MI, op:i < 5 ? 1 : 0.45});
  if (C.s.subtitle) C.text(72, 440, 520, 80, C.s.subtitle, {font:'head', fit:{max:18, min:12.5}, wt:400, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 580, 520, 22, who, {font:'body', size:14, wt:700, color:WH, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.website) C.text(72, 620, 400, 18, f.website, {font:'body', size:12.5, color:AM, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {};
  if (o.img === 'photo') D.imgBox(C, 0, 0, W, H, {r:0});
  mosaic(C, 0, 0, 64, 6, 20, 't', 11); mosaic(C, 0, H - 120, 64, 6, 20, 'b', 12);
  C.rect(200, 150, 880, 420, {fill:BG2, op:o.img === 'photo' ? 0.9 : 1});
  C.text(240, 180, 800, 150, C.s.title || 'Thank you', {font:'head', fit:{max:76, min:32}, wt:800, color:WH, align:'c', va:'m', lh:1, ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(260, 330, 760, 40, C.s.subtitle, {font:'head', fit:{max:17, min:12}, wt:400, color:MU, align:'c', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C), cw = 800 / Math.max(1, cs.length);
  cs.forEach(function(c, k){ var x = 240 + k * cw; C.rect(x + cw / 2 - 18, 400, 36, 36, {fill:k % 2 ? MI : AM}); C.icon(x + cw / 2 - 10, 408, 20, c.icon, BG2); C.text(x, 450, cw, 60, c.val, {font:'body', fit:{max:15, min:10.5}, wt:600, color:WH, align:'c', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:2}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 640 : W - 144;
  if (photo) { D.imgBox(C, 800, 0, 480, H, {r:0}); mosaic(C, 800, 0, 8, 36, 20, 'l', 13); }
  C.text(72, 130, 200, 60, '>', {font:'body', size:54, wt:700, color:AM, lh:1});
  var qe = C.text(130, 140, w - 60, 330, C.s.title, {font:'head', fit:{max:42, min:20}, wt:700, color:WH, lh:1.22, ed:{f:'title'}, maxLines:6});
  C.rect(130 + Math.min(w - 80, 20), 150 + qe._h, 18, 30, {fill:AM});
  if (by) C.text(130, 210 + qe._h, w - 60, 24, '// ' + (by.h ? by.h + ', ' + by.d : by.d), {font:'body', size:15, wt:600, color:MI, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Pixel cards', rows:'Pixel rows', steps:'Pixel steps', stmt:'Pixel panel', stats:'Number cards', photo:'pixel photo', track:'#14504E'};
var VS = K4.vset(pg, B, L, {
  cover:[['Dissolving mosaic', {}], ['Mosaic + photo', {img:'photo'}]],
  closing:[['Mosaic frame', {}], ['Mosaic frame + photo', {img:'photo'}]],
  quote:[['Cursor quote', {}], ['Quote + photo', {img:'photo'}]],
  p56:[['Heat grid', pg(heat)]], p78:[['Heat grid', pg(heat)]], p4:[['Heat grid', pg(heat)]], p3:[['Heat grid', pg(heat)]],
  stats:[['Pixel bars', pg(pixelBars)]]
});
D.register({
  id:'pixelmosaic', name:'Pixel Mosaic', tag:'Deep green · pixel mosaic',
  desc:'A deep green tech look with square pixel mosaics that dissolve across the slide in amber and mint, pixel bar charts and heat grids. For tech, data, AI, fintech, cybersecurity and digital transformation.',
  fonts:{head:'Chivo', body:'Chivo Mono', headW:800, bodyW:400, headLh:1.04, gf:'Chivo:wght@400;600;700;800|Chivo+Mono:wght@400;500;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

</script>
<?php
}, 1101);
