<?php
/**
 * Code Snippets export — DS Template 86 — Halftone Signal
 *
 * Snippet ID : 1120
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 09:43:30
 * Load order : 290 of 296
 */
/**
 * DS Template 86 — Halftone Signal
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3" and "DS Template Kit 4" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl86_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl86_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('halftone', array(
        'name'     => "Halftone Signal",
        'fonts'    => array('head' => "Familjen Grotesk", 'body' => "Commissioner"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#14212B', 'accent' => '#FF7A59', 'accent2' => '#0B4F5C'),
        'voice'    => "Insight voice: curious, sharp and evidence-led, turning data into headlines.",
        'recipe'   => "Key insight, who we asked, what they value, what changed, segments, numbers, implications, thank you.",
        'generate' => 'ds_tpl86_generate',
        'build'    => 'ds_tpl86_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl86_generate') ) {
    function ds_tpl86_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl86_build') ) {
    function ds_tpl86_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-halftone">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 86 - Halftone Signal : white page, print halftone dot gradients from coral to deep teal, dot-matrix bars */
var BG = '#FFFFFF', TE = '#0B4F5C', CO = '#FF7A59', INK = '#14212B', MU = '#56636C', PA = '#E6F0F1';
var P0 = {bg:BG, text:INK, title:INK, muted:MU, accent:CO, accent2:TE, card:PA, line:'#D6E2E4', onAccent:'#FFFFFF'};
var KC = 0.5523;
function dot(d, cx, cy, r){ var k = r * KC; d.push(['M', cx + r, cy], ['C', cx + r, cy + k, cx + k, cy + r, cx, cy + r], ['C', cx - k, cy + r, cx - r, cy + k, cx - r, cy], ['C', cx - r, cy - k, cx - k, cy - r, cx, cy - r], ['C', cx + k, cy - r, cx + r, cy - k, cx + r, cy], ['Z']); }
/* halftone field: dot size falls off with distance from a focus point; two colours split by a line */
function halftone(C, x, y, w, h, step, fx, fy, reach, split, cA, cB){
  var dA = [], dB = [];
  for (var py = y + step / 2; py < y + h; py += step) for (var px = x + step / 2; px < x + w; px += step) {
    var dd = Math.hypot(px - fx, py - fy) / reach, r = step * 0.46 * (1 - dd); if (r < 0.9) continue;
    dot(px > split ? dA : dB, px, py, r);
  }
  if (dA.length) C.path(dA, {fill:cA}); if (dB.length) C.path(dB, {fill:cB});
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  if (k) halftone(C, W - 260, H - 200, 260, 200, 16, W, H, 280, W - 120, CO, TE); else halftone(C, W - 260, 0, 260, 190, 16, W, 0, 280, W - 120, CO, TE);
}
function head(C, A, o){
  var y = 52;
  var te = C.text(A.x, y, Math.min(A.w, 880), 96, C.s.title || '', {font:'head', fit:{max:42, min:22}, wt:700, color:INK, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  var d = []; for (var i = 0; i < 9; i++) dot(d, A.x + 5 + i * 13, y + 5, 5 - i * 0.45); C.path(d, {fill:CO});
  y += 22;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:INK, mc:MU, ac:TE, markS:46, g:18, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:PA, r:14}); halftone(C, x + w - 180, y, 180, h, 14, x + w, y + h, 220, x + w - 90, CO, TE); return {tc:INK, mc:MU, ac:TE}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:TE, r:14}); return {tc:'#FFFFFF', mc:'#BFD9DD', ac:CO}; }
    C.rect(x, y, w, h, {fill:PA, r:14}); halftone(C, x + w - 70, y, 70, 60, 10, x + w, y, 80, x, CO, CO);
    return {tc:INK, mc:MU, ac:TE};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? BG : PA, r:12}); return {tc:INK, mc:MU, ac:TE}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:on ? CO : (k % 2 ? CO : TE)}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ var d = []; halftoneDisk(d, x + s / 2, y + s / 2, s / 2); C.path(d, {fill:k % 2 ? CO : TE}); C.ell(x + s * 0.22, y + s * 0.22, s * 0.56, s * 0.56, {fill:k % 2 ? CO : TE}); C.text(x, y, s, s, String(k + 1), {font:'head', size:s * 0.34, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? CO : TE}); C.text(cx - s / 2, cy - s / 2, s, s, String(k + 1), {font:'head', size:s * 0.38, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ var d = []; for (var x = x1; x <= x2; x += 14) dot(d, x, y, 2.6); C.path(d, {fill:TE}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 0, Z.y, W, Z.h, {r:0}); halftone(C, 0, Z.y, W, 60, 14, W / 2, Z.y - 40, 520, W / 2, CO, TE); return; }
    D.imgBox(C, Z.x, 0, Z.w, H, {r:0});
    var ex = side === 'right' ? Z.x : Z.x + Z.w;
    halftone(C, side === 'right' ? Z.x : Z.x + Z.w - 120, 0, 120, H, 16, ex, H / 2, 520, Z.x + Z.w / 2, CO, CO);
  }
};
function halftoneDisk(d, cx, cy, r){ for (var a = 0; a < 12; a++) { var ang = a * Math.PI / 6; dot(d, cx + Math.cos(ang) * r * 0.85, cy + Math.sin(ang) * r * 0.85, r * 0.1); } }
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.company || '', {font:'head', size:12, wt:700, color:TE, lh:1, va:'m'}); C.text(a.x + a.w - 60, y, 60, 16, pad2(C.i + 1), {font:'head', size:12, wt:700, color:CO, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[TE, CO, '#5C9BA6', '#FFB39F', '#14212B', '#A9C9CE'], header:{kind:'left', top:52}, card:{kind:'solid', fill:PA, r:14}, icon:{kind:'plain', fg:TE}, num:{kind:'big', fg:CO},
  conc:{kind:'pill', fill:CO, op:0.12, label:'Insight', lc:CO, tc:INK}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#5C9BA6'], [100, '#0B4F5C']]}}};
/* signature: dot-matrix bars - each value shown as filled dots out of twenty */
function dotBars(C, Z, o){
  var st = C.it.slice(0, 5), n = st.length; if (!n) return;
  var rh = Z.h / n, lw = Math.min(300, Z.w * 0.28), vals = st.map(K3.sv), labs = st.map(K3.sl), cells = 20, cw = (Z.w - lw - 140) / cells, r = Math.min(cw * 0.38, rh * 0.22);
  st.forEach(function(t, k){ var y = Z.y + k * rh, num = Math.abs(parseFloat(String(vals[k]).replace(/[^0-9.\-]/g, '')) || 0), pct = /%/.test(vals[k]) ? num / 100 : Math.min(1, 0.35 + 0.14 * (n - k));
    C.text(Z.x, y, lw - 20, rh, labs[k], {font:'head', fit:{max:17, min:11}, wt:700, color:INK, va:'m', lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    var on = Math.max(1, Math.round(pct * cells)), a = [], b = [];
    for (var c = 0; c < cells; c++) dot(c < on ? a : b, Z.x + lw + c * cw + cw / 2, y + rh / 2, r);
    C.path(a, {fill:k === 0 ? CO : TE}); if (b.length) C.path(b, {fill:PA});
    C.text(Z.x + Z.w - 120, y, 120, rh, vals[k], {font:'head', fit:{max:30, min:14}, wt:700, color:k === 0 ? CO : INK, align:'r', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
  });
}
/* signature: density circles - halftone circles that get denser point by point */
function density(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 700) return B.cards(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.36, Z.h * 0.28, 100), cy = Z.y + r + 6, tw = cw - 24;
  var F = K.fitHD(C, it, tw, 46, Z.h - r * 2 - 40, {hMax:21, dMax:15.5, hMin:12, dMin:10});
  it.forEach(function(t, k){ var cx = Z.x + cw * (k + 0.5), step = 15 - k * 2.2, d = [];
    for (var py = cy - r; py <= cy + r; py += step) for (var px = cx - r; px <= cx + r; px += step) { var dd = Math.hypot(px - cx, py - cy); if (dd < r) dot(d, px, py, step * (0.24 + 0.08 * k)); }
    C.path(d, {fill:k % 2 ? CO : TE});
    K.hd(C, cx - tw / 2, cy + r + 24, tw, Z.h - r * 2 - 30, t, F, {tc:INK, mc:MU, align:'c'});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  if (o.img === 'photo') { D.imgBox(C, 700, 0, 580, H, {r:0}); halftone(C, 620, 0, 260, H, 18, 620, H / 2, 300, 700, CO, TE); }
  else halftone(C, 560, 0, 720, H, 26, W - 60, H / 2, 740, 900, CO, TE);
  if (f.company) C.text(72, 70, 520, 20, f.company, {font:'head', size:14, wt:700, color:CO, caps:true, ls:0.12, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 140, 560, 260, C.s.title, {font:'head', fit:{max:66, min:28}, wt:700, color:INK, lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(72, 418, 60, 6, {fill:CO});
  if (C.s.subtitle) C.text(72, 444, 520, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 590, 520, 22, who, {font:'head', size:15, wt:700, color:TE, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  if (o.img === 'photo') D.imgBox(C, 0, 0, W, H, {r:0}); else C.rect(0, 0, W, H, {fill:TE});
  halftone(C, 0, 0, W, H, 34, W * 0.9, H * 0.9, 900, W * 0.62, CO, '#2A7F8C');
  C.rect(72, 120, 640, 470, {fill:TE, op:o.img === 'photo' ? 0.92 : 1});
  C.text(110, 150, 570, 180, C.s.title || 'Thank you', {font:'head', fit:{max:76, min:32}, wt:700, color:'#FFFFFF', lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(110, 344, 560, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#BFD9DD', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 420 + k * 50; C.ell(110, y, 36, 36, {fill:CO}); C.icon(118, y + 8, 20, c.icon, '#FFFFFF'); C.text(160, y - 2, 520, 40, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo';
  var cx = 300, cy = 360, r = 190;
  if (photo) { C.ell(cx - r - 10, cy - r - 10, r * 2 + 20, r * 2 + 20, {fill:CO}); D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'}); }
  else { var d = []; for (var py = cy - r; py <= cy + r; py += 16) for (var px = cx - r; px <= cx + r; px += 16) { var dd = Math.hypot(px - cx, py - cy) / r; if (dd < 1) dot(d, px, py, 7 * (1 - dd * 0.75)); } C.path(d, {fill:TE}); }
  C.text(560, 130, 120, 120, '“', {font:'head', size:150, wt:700, color:CO, lh:1, caps:false});
  var qe = C.text(560, 240, 650, 280, C.s.title, {font:'head', fit:{max:40, min:18}, wt:700, color:INK, lh:1.2, ed:{f:'title'}, maxLines:6});
  if (by) C.text(560, 250 + qe._h + 20, 650, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:TE, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Dot cards', rows:'Dot rows', steps:'Dotted path', stmt:'Halftone panel', stats:'Number cards', photo:'halftone photo', track:'#E6F0F1'};
var VS = K4.vset(pg, B, L, {
  cover:[['Halftone sweep', {}], ['Halftone + photo', {img:'photo'}]],
  closing:[['Halftone teal', {}], ['Halftone + photo', {img:'photo'}]],
  quote:[['Halftone portrait quote', {}], ['Round photo quote', {img:'photo'}]],
  p3:[['Density circles', pg(density)]], p4:[['Density circles', pg(density)]], p2:[['Density circles', pg(density)]],
  stats:[['Dot-matrix bars', pg(dotBars)]]
});
D.register({
  id:'halftone', name:'Halftone Signal', tag:'White · coral-teal halftone',
  desc:'Print-inspired halftone dot gradients sweeping from coral to deep teal, dot-matrix bars and density circles. Energetic decks for marketing insights, research findings and consumer reports.',
  fonts:{head:'Familjen Grotesk', body:'Commissioner', headW:700, bodyW:400, headLh:1.04, gf:'Familjen+Grotesk:wght@500;600;700|Commissioner:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

</script>
<?php
}, 1103);
