<?php
/**
 * Code Snippets export — DS Template 74 — Metro Line
 *
 * Snippet ID : 1108
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 09:43:20
 * Load order : 278 of 296
 */
/**
 * DS Template 74 — Metro Line
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3" and "DS Template Kit 4" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl74_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl74_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('metroline', array(
        'name'     => "Metro Line",
        'fonts'    => array('head' => "Red Hat Display", 'body' => "Red Hat Text"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#111111', 'accent' => '#36339A', 'accent2' => '#009966'),
        'voice'    => "Clear, structured and journey-minded: every point is a step on a route. Plain business language and concrete milestones.",
        'recipe'   => "Where we start, the route ahead, key stops (two to four points each), the numbers, the roadmap, the destination and next steps.",
        'generate' => 'ds_tpl74_generate',
        'build'    => 'ds_tpl74_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl74_generate') ) {
    function ds_tpl74_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl74_build') ) {
    function ds_tpl74_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-metroline">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 74 - Metro Line : white page, parallel route lines with exactly equal spacing through every bend, station rings, a target for goals */
var BG = '#FFFFFF', IN = '#36339A', GR = '#009966', OR = '#FF9A00', MG = '#C2185B', AR = '#1F3A8A', INK = '#111111', MU = '#5A6070', LN = '#E3E5EC';
var LC = [IN, GR, OR, MG];
var P0 = {bg:BG, text:INK, title:INK, muted:MU, accent:IN, accent2:GR, card:'#F6F7FB', line:LN, onAccent:'#FFFFFF'};
function ring(C, cx, cy, r, c, lw){ C.ell(cx - r, cy - r, r * 2, r * 2, {fill:'#FFFFFF', line:c, lw:lw || Math.max(4, r * 0.32)}); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  K4.lanes(C, [[W + 20, 70], [W - 110, 70], [W - 160, 20], [W - 160, -30]], {colors:[IN, GR, OR], gap:16, lw:7, r:30});
}
function head(C, A, o){
  var y = 58;
  var te = C.text(A.x, y, Math.min(A.w, 900), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:INK, lh:1.08, ed:{f:'title'}, maxLines:2});
  y += te._h + 12;
  for (var i = 0; i < 3; i++) C.rect(A.x + i * 30, y, 24, 6, {fill:LC[i], r:3});
  y += 16;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:INK, mc:MU, ac:IN, markS:44, g:18, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    var c = LC[k % 4];
    if (o.big) { C.rect(x, y, w, h, {fill:'#F6F7FB', r:16}); C.rect(x, y, w, 10, {fill:IN, r:5}); return {tc:INK, mc:MU, ac:IN}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:IN, r:16}); return {tc:'#FFFFFF', mc:'#D8D7F2', ac:'#FFFFFF'}; }
    C.rect(x, y, w, h, {fill:'#FFFFFF', r:16, line:LN, lw:1.5}); C.rect(x + 1, y + 1, w - 2, 9, {fill:c, r:4});
    return {tc:INK, mc:MU, ac:c};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:LN}); return {tc:INK, mc:MU, ac:IN}; },
  mark:function(C, x, y, s, t, k, cc){ var c = LC[k % 4], on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:on ? '#FFFFFF' : c}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? IN : '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ ring(C, x + s / 2, y + s / 2, s / 2, LC[k % 4], 6); C.text(x, y, s, s, String(k + 1), {font:'head', size:s * 0.38, wt:800, color:INK, align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ ring(C, cx, cy, s / 2, LC[k % 4], 7); C.text(cx - s / 2, cy - s / 2, s, s, String(k + 1), {font:'head', size:s * 0.34, wt:800, color:INK, align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:IN, lw:10}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y + 8, W - 144, Z.h - 26, {r:18}); C.line(0, Z.y, W, Z.y, {color:GR, lw:8}); ring(C, 72, Z.y, 13, GR, 6); ring(C, W - 72, Z.y, 13, GR, 6); return; }
    var x = Z.x + 30, w = Z.w - 60, y = 70, h = H - 160;
    D.imgBox(C, x, y, w, h, {r:24});
    var ex = side === 'right' ? x : x + w;
    C.line(ex, -10, ex, H + 10, {color:OR, lw:10}); ring(C, ex, y + h, 16, OR, 7);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.website || C.cover.company || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'}); ring(C, a.x + a.w - 14, y + 8, 14, IN, 4); C.text(a.x + a.w - 28, y - 6, 28, 28, String(C.i + 1), {font:'head', size:11, wt:800, color:INK, align:'c', va:'m', lh:1}); }
var S = {margin:72, series:[IN, GR, OR, MG, '#8C8AD0', '#7FD3B6'], header:{kind:'left', top:58}, card:{kind:'outline', r:16}, icon:{kind:'plain', fg:IN}, num:{kind:'big', fg:IN},
  conc:{kind:'pill', fill:IN, op:0.08, label:'Next stop', lc:IN, tc:INK}, footer:{fn:foot}, imgFramed:true, imgR:24, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#8C8AD0'], [100, '#36339A']]}}};
/* signature: route map - one thick line, every point a station, labels alternate above and below */
function route(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return B.rows(C, Z, o);
  var ly = Z.y + Z.h / 2, cw = Z.w / n, tw = cw - 16, hh = Z.h / 2 - 50;
  var F = K.fitHD(C, it, tw, 44, hh, {hMax:22, dMax:16.5, hMin:12, dMin:10, noBoost:n > 3});
  K4.lanes(C, [[Z.x - 10, ly], [Z.x + Z.w + 10, ly]], {colors:[IN], gap:0, lw:14});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), up = k % 2 === 0, w2 = tw, bh = K3.bH(C, t, w2, F);
    ring(C, cx, ly, 17, LC[k % 4], 8);
    C.line(cx, up ? ly - 22 : ly + 22, cx, up ? ly - 40 : ly + 40, {color:LC[k % 4], lw:3});
    K.hd(C, cx - w2 / 2, up ? ly - 46 - bh : ly + 46, w2, hh, t, F, {tc:INK, mc:MU, align:'c'});
  });
}
/* signature: branch lines - one trunk splits into a line per point, each ending at a station */
function branches(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (!n) return;
  var x0 = Z.x, yc = Z.y + Z.h / 2, xs = Z.x + 70, rh = Z.h / n, xt = Z.x + Math.min(330, Z.w * 0.32), tw = Z.x + Z.w - xt - 40;
  var F = K.fitHD(C, it, tw, 40, rh - 12, {hMax:22, dMax:16.5, hMin:12, dMin:10});
  it.forEach(function(t, k){
    var yi = Z.y + rh * (k + 0.5), dy = yi - yc, xb = xs + Math.abs(dy);
    var pts = Math.abs(dy) < 2 ? [[x0 - 80, yc], [xt, yc]] : [[x0 - 80, yc], [xs, yc], [xb, yi], [xt, yi]];
    if (xb > xt - 20) pts = [[x0 - 80, yc], [xs, yc], [xt - 20, yi], [xt, yi]];
    K4.lanes(C, pts, {colors:[LC[k % 4]], gap:0, lw:10, r:40});
  });
  it.forEach(function(t, k){ var yi = Z.y + rh * (k + 0.5), bh = K3.bH(C, t, tw, F); ring(C, xt, yi, 18, LC[k % 4], 8); K.hd(C, xt + 40, yi - bh / 2, tw, rh - 8, t, F, {tc:INK, mc:MU}); });
}
/* signature: station list - one vertical line, a station per topic */
function stations(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cl = n > 5 && Z.w > 760 ? 2 : 1, per = Math.ceil(n / cl), cw = Z.w / cl, rh = Math.min(Z.h / per, 118), tw = cw - 90;
  var F = K.fitHD(C, it, tw, 40, rh - 10, {hMax:23, dMax:it.some(function(t){ return t.h; }) ? 17 : 24, hMin:12, dMin:10, noBoost:true});
  for (var c = 0; c < cl; c++) { var cnt = Math.min(per, n - c * per); if (cnt > 1) C.line(Z.x + c * cw + 22, Z.y + rh / 2, Z.x + c * cw + 22, Z.y + rh * (cnt - 0.5), {color:IN, lw:10}); }
  it.forEach(function(t, k){ var c = Math.floor(k / per), r = k % per, x = Z.x + c * cw, y = Z.y + r * rh, bh = K3.bH(C, t, tw, F); ring(C, x + 22, y + rh / 2, 16, LC[k % 4], 7); K.hd(C, x + 62, y + (rh - bh) / 2, tw, rh, t, F, {tc:INK, mc:MU}); });
}
/* signature: transit roundels for numbers */
function roundels(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.3, Z.h * 0.26, 92), cy = Z.y + r + 14, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 30;
  var vs = C.groupFit(vals, r * 2.2, r * 0.5, {font:'head', max:40, min:16, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12, maxLines:2});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = LC[k % 4];
    C.ell(cx - r, cy - r, r * 2, r * 2, {line:c, lw:r * 0.26});
    C.rect(cx - r * 1.25, cy - r * 0.3, r * 2.5, r * 0.6, {fill:AR});
    C.text(cx - r * 1.2, cy - r * 0.3, r * 2.4, r * 0.6, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, cy + r + 24, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, cy + r + 30 + le._h, tw, Z.y + Z.h - (cy + r + 30 + le._h), t.x, {font:'body', size:14, color:MU, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
var LANES = [[-30, 330], [540, 330], [770, 560], [W + 30, 560]];
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  K4.lanes(C, LANES, {colors:[IN, GR, OR], gap:54, lw:23, r:120});
  if (photo) { var cx = 1000, cy = 300, r = 128; C.ell(cx - r - 14, cy - r - 14, (r + 14) * 2, (r + 14) * 2, {fill:'#FFFFFF', line:INK, lw:12}); D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'}); }
  else K4.target(C, 993, 300, 119, {ink:INK, arrow:AR});
  if (f.company) C.text(72, 48, 500, 22, f.company, {font:'head', size:14, wt:800, color:IN, caps:true, ls:0.12, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 84, 620, 160, C.s.title, {font:'head', fit:{max:54, min:26}, wt:800, color:INK, lh:1.04, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(72, 432, 470, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 540, 470, 24, who, {font:'head', size:15, wt:700, color:INK, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.website) C.text(72, 640, 400, 18, f.website, {font:'body', size:12.5, wt:600, color:IN, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  K4.lanes(C, [[-30, 640], [380, 640], [600, 420], [1000, 420]], {colors:[IN, GR, OR], gap:40, lw:16, r:90});
  if (photo) { C.ell(866, 266, 308, 308, {fill:'#FFFFFF', line:INK, lw:12}); D.imgBox(C, 880, 280, 280, 280, {shape:'ell'}); }
  else { ring(C, 1020, 420, 120, INK, 24); C.text(900, 360, 240, 120, 'END OF LINE', {font:'head', size:16, wt:800, color:INK, align:'c', va:'m', caps:true, ls:0.12, lh:1}); }
  C.text(72, 60, 760, 130, C.s.title || 'Thank You', {font:'head', fit:{max:74, min:32}, wt:800, color:INK, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 200, 680, 50, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 272 + k * 50; ring(C, 92, y + 18, 18, LC[k % 4], 7); C.icon(84, y + 10, 16, c.icon, INK); C.text(128, y, 440, 36, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 700 : W - 144;
  if (photo) { D.imgBox(C, 830, 0, 450, H, {r:0}); C.line(830, -10, 830, H + 10, {color:OR, lw:12}); }
  C.rect(72, 110, w, 420, {fill:'#1C1F2B', r:18});
  C.text(116, 132, 90, 90, '“', {font:'head', size:110, wt:800, color:OR, lh:1, caps:false});
  var qe = C.text(120, 230, w - 96, 230, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:700, color:'#FFFFFF', lh:1.22, ed:{f:'title'}, maxLines:6});
  if (by) C.text(120, 476, w - 96, 26, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:OR, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Platform signs', rows:'Station rows', steps:'Route steps', stmt:'Statement card', stats:'Number signs', photo:'photo', track:'#E3E5EC'};
var VS = K4.vset(pg, B, L, {
  cover:[['Lines + target', {}], ['Lines + round photo', {img:'photo'}]],
  closing:[['Lines to the terminus', {}], ['Lines + round photo', {img:'photo'}]],
  quote:[['Platform board', {}], ['Board + photo', {img:'photo'}]],
  agenda:[['Station list', pg(stations)]],
  p2:[['Branch lines', pg(branches)]], p3:[['Branch lines', pg(branches)], ['Route map', pg(route)]], p4:[['Route map', pg(route)], ['Branch lines', pg(branches)]],
  p56:[['Station list', pg(stations)], ['Route map', pg(route)]], p78:[['Station list', pg(stations)]],
  stats:[['Transit roundels', pg(roundels)]], timeline:[['Route map', pg(route)], ['Station list', pg(stations)]]
});
D.register({
  id:'metroline', name:'Metro Line', tag:'White · transit route lines',
  desc:'Your deck drawn as a transit map: parallel route lines that bend together with equal spacing, station rings, platform-sign cards and a target for goals. For operations, logistics, transport, roadmaps and onboarding journeys.',
  fonts:{head:'Red Hat Display', body:'Red Hat Text', headW:800, bodyW:400, headLh:1.06, gf:'Red+Hat+Display:wght@600;700;800|Red+Hat+Text:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

</script>
<?php
}, 1091);
