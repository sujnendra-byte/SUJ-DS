<?php
/**
 * Code Snippets export — DS Template 21 — Forest Campaign
 *
 * Snippet ID : 920
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:33:22
 * Load order : 178 of 191
 */
/**
 * DS Template 21 — Forest Campaign
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
    ds_tpl_register('forest', array(
        'name'     => "Forest Campaign",
        'fonts'    => array('head' => "Urbanist", 'body' => "Urbanist"),
        'pal'      => array('bg' => '#F1F2EF', 'text' => '#1E2A24', 'accent' => '#F2C230', 'accent2' => '#4E9A51'),
        'voice'    => "Campaign voice: purposeful, community-minded, clear phases and measurable goals.",
        'recipe'   => "Mission, the issue, campaign goals, audiences, phases, channels, impact metrics, get involved.",
        'generate' => 'ds_tpl21_generate',
        'build'    => 'ds_tpl21_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl21_generate') ) {
function ds_tpl21_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl21_build') ) {
function ds_tpl21_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-forest">
/* Template 21 — Forest Campaign : warm grey pages, forest green + mustard + teal, step bars and hexagon markers */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var FOREST = '#1F4D3A', MUST = '#F2C230', GREEN = '#4E9A51', TEAL = '#2E8C86', CHAR = '#232323';
var P0 = {bg:'#F1F2EF', text:'#1E2A24', title:'#1F4D3A', muted:'#56605A', accent:'#F2C230', accent2:'#4E9A51', card:'#FFFFFF', line:'#D9DCD5', onAccent:'#1E2A24'};
var S = {
  margin:64, series:[MUST, '#A5C23A', GREEN, TEAL, FOREST, CHAR],
  header:{kind:'left', tMax:40, tMin:24, sMax:16, top:50, bar:true, barW:44, barH:5, barC:MUST, barGap:16},
  card:{kind:'bar', r:10, fill:'#FFFFFF', shadow:0.06, barW:6, hl:{kind:'solid', fill:FOREST, dark:true}},
  icon:{kind:'hex', bg:FOREST, fg:'#FFFFFF'},
  num:{kind:'square', r:6, bg:MUST, fg:'#1E2A24'},
  conc:{kind:'band', fill:FOREST, r:10, label:'Goal', lc:MUST},
  footer:{brand:true, page:true, color:'#8A928C'}, xRows:'#FFFFFF', hubFill:'#E4E7E0', road:CHAR, flowBar:'#DEE1DA', stepWord:'Step',
  imgFramed:true, imgR:12
};
/* sweeping double road (decor) */
function roads(C, x0, y0, s){
  var a = [], b = [];
  for (var i = 0; i <= 40; i++) { var t = i / 40; a.push([x0 + t * 520 * s, y0 - Math.sin(t * Math.PI * 1.1) * 220 * s + t * 60 * s]); b.push([x0 + 60 * s + t * 520 * s, y0 + 40 * s - Math.sin(t * Math.PI * 1.05) * 200 * s + t * 60 * s]); }
  K.curve(C, a, {stroke:CHAR, lw:34 * s}); K.curve(C, b, {stroke:FOREST, lw:26 * s});
  for (var j = 0; j < 40; j += 2) C.line(a[j][0], a[j][1], a[j + 1][0], a[j + 1][1], {color:MUST, lw:2.5, op:0.9});
  [10, 22, 34].forEach(function(k, n){ var p = b[k]; C.ell(p[0] - 26, p[1] - 26, 52, 52, {fill:'#FFFFFF'}); C.ell(p[0] - 20, p[1] - 20, 40, 40, {fill:[MUST, GREEN, TEAL][n]}); });
}
function bg(C, kind){
  C.bg({fill:'#F1F2EF'});
  if (kind !== 'cover' && kind !== 'closing') { C.shape('hexagon', W - 120, 30, 80, 70, {fill:MUST, op:0.9}); C.shape('hexagon', W - 170, 76, 54, 47, {fill:FOREST, op:0.9}); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.rect(W * 0.55, 0, W * 0.45, H, {fill:FOREST});
  if (o.img) D.imgBox(C, W * 0.55 + 40, 40, W * 0.45 - 80, H - 80, {r:16}); else roads(C, W * 0.55 + 20, 520, 1);
  var x = 64, w = W * 0.55 - 120;
  if (f.company) C.text(x, 80, w, 22, f.company, {font:'body', size:14, wt:800, color:GREEN, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 130, w, 300, C.s.title, {font:'head', fit:{max:64, min:30}, color:FOREST, ed:{f:'title'}, maxLines:4, va:'b', lh:1.04});
  C.rect(x, 454, 70, 7, {fill:MUST});
  if (C.s.subtitle) C.text(x, 480, w, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 620, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  C.rect(0, 0, W * 0.45, H, {fill:FOREST});
  if (o.img) D.imgBox(C, 40, 40, W * 0.45 - 80, H - 80, {r:16}); else roads(C, 10, 520, 0.95);
  var x = W * 0.45 + 64, w = W * 0.55 - 128;
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:FOREST, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  C.rect(x, 370, 70, 7, {fill:MUST});
  if (C.s.subtitle) C.text(x, 396, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 490, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:MUST}); return; }
  C.rect(0, 0, 18, H, {fill:MUST});
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:GREEN});
}
/* ================= signature bodies (forest motif: honeycomb, leaves, vines, roads) ================= */
var FILLS = [FOREST, MUST, GREEN, TEAL, '#A5C23A', CHAR];
function fillK(k){ return FILLS[k % FILLS.length]; }
function numOn(c){ return c === MUST || c === '#A5C23A' ? FOREST : MUST; }
function fhd(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function blockH(C, t, F, w){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
/* rectangle with two opposite corners fully rounded (leaf card) */
function leafRect(C, x, y, w, h, r, o){
  r = Math.min(r, w / 2, h / 2); var k = 0.552 * r;
  C.path([['M', x + r, y], ['L', x + w, y], ['L', x + w, y + h - r], ['C', x + w, y + h - r + k, x + w - r + k, y + h, x + w - r, y + h], ['L', x, y + h], ['L', x, y + r], ['C', x, y + r - k, x + r - k, y, x + r, y], ['Z']], o);
}
/* pointed leaf from a base point along an angle */
function leaf(C, x0, y0, len, wid, ang, fill, op){
  var a = ang * Math.PI / 180, c = Math.cos(a), s = Math.sin(a);
  function T(px, py){ return [x0 + px * c - py * s, y0 + px * s + py * c]; }
  var p1 = T(len, 0), q1 = T(len * 0.5, -wid), q2 = T(len * 0.5, wid);
  C.path([['M', x0, y0], ['Q', q1[0], q1[1], p1[0], p1[1]], ['Q', q2[0], q2[1], x0, y0], ['Z']], {fill:fill, op:op});
}
/* pointy-top hexagon */
function phex(C, cx, cy, r, o){ var p = []; for (var k = 0; k < 6; k++) { var a = (-90 + 60 * k) * Math.PI / 180; p.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]); } return K.poly(C, p, o); }
function hexBadge(C, x, y, w, n, fill, o){
  o = o || {}; var h = w * 0.866;
  if (o.ring) C.shape('hexagon', x - 4, y - 3.5, w + 8, h + 7, {fill:o.ring});
  C.shape('hexagon', x, y, w, h, {fill:fill});
  C.text(x, y, w, h, o.label != null ? o.label : u.pad2(n), {font:'head', size:Math.round(w * 0.36), wt:800, color:o.fg || K.on(fill), align:'c', va:'m', lh:1, caps:false, ls:0});
}
/* staggered row of flat hexagons (honeycomb strip) */
function zigCells(Z, n){
  var g = 12, w = Math.min(400, (Z.w - g * (n - 1)) / (1 + 0.75 * (n - 1)), (Z.h - g) / (1.5 * 0.866)), h = w * 0.866;
  var tw = w + (n - 1) * (0.75 * w + g), th = h * 1.5 + g / 2, x0 = Z.x + (Z.w - tw) / 2, y0 = Z.y + (Z.h - th) / 2, out = [];
  for (var k = 0; k < n; k++) out.push({x:x0 + k * (0.75 * w + g), y:y0 + (k % 2 ? h / 2 + g / 2 : 0), w:w, h:h});
  return out;
}
function zigDecor(C, Z, cells){
  var a = cells[0], b = cells[cells.length - 1], w = a.w, h = a.h, g = 12;
  var px = a.x - 0.75 * w - g, py = a.y + h / 2 + g / 2;
  if (px >= Z.x - 30) C.shape('hexagon', px, py, w, h, {line:'#C9CFC4', lw:1.5});
  var qx = b.x + 0.75 * w + g, qy = cells.length % 2 ? b.y + h / 2 + g / 2 : b.y - h / 2 - g / 2;
  if (qx + w <= Z.x + Z.w + 30 && qy >= Z.y - 20) C.shape('hexagon', qx, qy, w, h, {line:'#C9CFC4', lw:1.5});
}
/* 1. honeycomb strip: items inside staggered hexagons */
function honeyRow(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cells = zigCells(Z, n), w = cells[0].w, h = cells[0].h, iw = w * 0.62, ih = h * 0.74 - 34;
  var F = fhd(C, it, iw, 60, ih - 10, {hMax:24, dMax:18, noBoost:true});
  zigDecor(C, Z, cells);
  it.forEach(function(t, k){
    var c = cells[k], f = fillK(k), tc = K.on(f), bhh = blockH(C, t, F, iw), top = c.y + (h - (bhh + 34)) / 2;
    C.shape('hexagon', c.x, c.y, w, h, {fill:f});
    C.text(c.x, top, w, 24, u.pad2(k + 1), {font:'head', size:20, wt:800, color:numOn(f), align:'c', lh:1, caps:false, ls:0});
    K.hd(C, c.x + (w - iw) / 2, top + 34, iw, bhh + 4, t, F, {align:'c', tc:tc, mc:tc === '#FFFFFF' ? '#E4EDE6' : '#2E3A33'});
  });
}
/* 2. two-row pointy honeycomb with text in every cell */
function honeycomb(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var a = Math.ceil(n / 2), b = n - a, g = 10;
  var r = Math.min((Z.h - g) / 3.5, ((Z.w + g) / (a + (b === a ? 0.5 : 0)) - g) / 1.732, 150), w = r * 1.732;
  var tw = (b === a ? a + 0.5 : a) * (w + g) - g, x0 = Z.x + (Z.w - tw) / 2, y0 = Z.y + (Z.h - (3.5 * r + g)) / 2;
  var iw = w * 0.78, ih = r * 1.22;
  var F = fhd(C, it, iw, 44, ih - 30, {hMax:20, dMax:16, noBoost:true, hLines:2});
  function pos(k){ return k < a ? [x0 + w / 2 + k * (w + g), y0 + r] : [x0 + w / 2 + (k - a + 0.5) * (w + g), y0 + r + 1.5 * r + g * 0.87]; }
  /* faded cells continuing the comb */
  var l = [x0 + w / 2 - 0.5 * (w + g), y0 + r + 1.5 * r + g * 0.87], rr = [x0 + w / 2 + a * (w + g), y0 + r];
  if (l[0] - w / 2 >= Z.x - 40) phex(C, l[0], l[1], r, {stroke:'#C9CFC4', lw:1.5});
  if (rr[0] + w / 2 <= Z.x + Z.w + 40) phex(C, rr[0], rr[1], r, {stroke:'#C9CFC4', lw:1.5});
  it.forEach(function(t, k){
    var p = pos(k), f = fillK(k === 0 ? 0 : k), tc = K.on(f), bhh = blockH(C, t, F, iw), top = p[1] - (bhh + 28) / 2;
    phex(C, p[0], p[1], r, {fill:f});
    C.text(p[0] - iw / 2, top, iw, 22, u.pad2(k + 1), {font:'head', size:17, wt:800, color:numOn(f), align:'c', lh:1, caps:false, ls:0});
    K.hd(C, p[0] - iw / 2, top + 28, iw, bhh + 4, t, F, {align:'c', tc:tc, mc:tc === '#FFFFFF' ? '#E4EDE6' : '#2E3A33'});
  });
}
/* 3. vine: curving stem with hexagon nodes, leaves and text rows */
function vine(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 6 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 48, cw = (Z.w - gx * (cols - 1)) / cols, rh = Z.h / per;
  var hw = Math.min(58, (rh - 8) / 0.866), tx0 = 26 + hw / 2 + 26, tw = cw - tx0 - 6;
  var F = fhd(C, it, tw, Math.min(34, rh * 0.4), rh - 10, {hMax:20, dMax:16, hLines:1});
  for (var c = 0; c < cols; c++) {
    var sx = Z.x + c * (cw + gx) + 26, cnt = Math.min(per, n - c * per), pts = [[sx, Z.y - 6]];
    for (var r = 0; r < cnt; r++) pts.push([sx + (r % 2 ? 7 : -7), Z.y + rh * (r + 0.5)]);
    pts.push([sx, Z.y + rh * cnt + 4]);
    K.curve(C, pts, {stroke:FOREST, lw:4});
    for (var q = 0; q < cnt; q++) {
      var ly = Z.y + rh * (q + 1) - (q === cnt - 1 ? rh * 0.3 : 0);
      if (q < cnt - 1 || rh > 90) leaf(C, sx, ly, Math.min(34, rh * 0.42), 20, q % 2 ? -150 : -30, q % 2 ? GREEN : '#A5C23A', 0.95);
    }
  }
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, cy = y + rh / 2, sx = x + 26 + (r % 2 ? 7 : -7);
    var f = [MUST, GREEN, TEAL, FOREST][k % 4];
    hexBadge(C, sx - hw / 2, cy - hw * 0.433, hw, k + 1, f, {ring:'#F1F2EF'});
    var bhh = blockH(C, t, F, tw);
    K.hd(C, x + tx0, cy - bhh / 2, tw, bhh + 4, t, F, {tc:FOREST, hLines:1});
    if (r < per - 1 && k < n - 1) C.rect(x + tx0, y + rh - 0.5, tw, 1, {fill:'#D9DCD5'});
  });
}
/* 4. two leaf-shaped panels with a hexagon hand-off */
function leafPanels(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = 44, cw = (Z.w - gap * (n - 1)) / n, pad = Math.min(40, cw * 0.08), bw = 62, iw = cw - pad * 2;
  var F = fhd(C, it, iw, 80, Z.h - pad * 2 - bw - 24, {hMax:26, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), dark = k === 0, f = dark ? FOREST : '#FFFFFF';
    leafRect(C, x, Z.y, cw, Z.h, 70, {fill:f, stroke:dark ? null : '#C9CFC4', lw:1.5});
    leaf(C, x + cw - 30, Z.y + Z.h - 40, 110, 70, -140, dark ? GREEN : '#E4E9DF', dark ? 0.55 : 1);
    hexBadge(C, x + pad, Z.y + pad, bw, k + 1, dark ? MUST : FOREST, {fg:dark ? FOREST : MUST});
    C.rect(x + pad + bw + 16, Z.y + pad + bw * 0.433 - 2, Math.min(120, cw - pad * 2 - bw - 16), 4, {fill:dark ? MUST : GREEN, r:2});
    var bhh = blockH(C, t, F, iw);
    K.hd(C, x + pad, Z.y + pad + bw + 24, iw, Math.max(bhh + 4, 40), t, F, {tc:dark ? '#FFFFFF' : FOREST, mc:dark ? '#D6E3D9' : '#56605A'});
  });
  if (n === 2) { var mx = Z.x + cw + gap / 2; hexBadge(C, mx - 30, Z.y + Z.h / 2 - 26, 60, 0, MUST, {ring:'#F1F2EF', label:'→', fg:FOREST}); }
}
/* 5. fork in the road: two routes, text above each destination */
function forkPath(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var roadH = Math.min(210, Z.h * 0.44), ny = Z.y + Z.h - roadH + 44, cx = Z.x + Z.w / 2, bot = Z.y + Z.h + 8;
  var nodes = n === 2 ? [Z.x + Z.w * 0.25, Z.x + Z.w * 0.75] : [cx];
  nodes.forEach(function(nx, k){
    var pts = [];
    for (var i = 0; i <= 24; i++) { var s = i / 24, a = 1 - s;
      pts.push([a * a * a * cx + 3 * a * a * s * cx + 3 * a * s * s * nx + s * s * s * nx, a * a * a * bot + 3 * a * a * s * (ny + 40) + 3 * a * s * s * (ny + 80) + s * s * s * ny]); }
    K.curve(C, pts, {stroke:CHAR, lw:30});
    for (var j = 1; j < 23; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:MUST, lw:2.5});
  });
  var colW = n === 2 ? Z.w / 2 - 50 : Z.w * 0.7;
  var F = fhd(C, it, colW - 40, 80, ny - 56 - Z.y - 20, {hMax:28, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var nx = nodes[k], f = k ? GREEN : MUST;
    hexBadge(C, nx - 44, ny - 38, 88, k + 1, f, {ring:'#FFFFFF'});
    var bx = nx - colW / 2 + 20, bhh = blockH(C, t, F, colW - 40), by = ny - 56 - bhh;
    C.rect(nx - 28, by - 20, 56, 5, {fill:f, r:2});
    K.hd(C, bx, by, colW - 40, bhh + 4, t, F, {align:'c', tc:FOREST});
  });
}
/* 6. stats in staggered hexagons */
function hexStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cells = zigCells(Z, n), w = cells[0].w, h = cells[0].h, iw = w * 0.62;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, h * 0.3, {font:'head', max:Math.round(w * 0.2), min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, h * 0.22, {font:'body', wt:700, max:17, min:12, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, h * 0.2, {font:'body', max:14, min:11, maxLines:2});
  zigDecor(C, Z, cells);
  st.forEach(function(t, k){
    var c = cells[k], f = fillK(k), tc = K.on(f), cx = c.x;
    C.shape('hexagon', c.x, c.y, w, h, {fill:f});
    var lh2 = C.measureText(t.l, iw, {font:'body', wt:700, max:ls, min:ls, maxLines:2}).h, xh = t.x ? C.measureText(t.x, iw, {font:'body', max:xs, min:xs}).h + 8 : 0;
    var tot = vs * 1.05 + 14 + lh2 + xh, y = c.y + (h - tot) / 2;
    C.text(cx + (w - iw) / 2, y, iw, vs * 1.1, t.v, {font:'head', size:vs, color:numOn(f) === MUST && tc === '#FFFFFF' ? '#FFFFFF' : tc, align:'c', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx + w / 2 - 20, y + vs * 1.05 + 4, 40, 4, {fill:numOn(f), r:2});
    C.text(cx + (w - iw) / 2, y + vs * 1.05 + 14, iw, lh2 + 2, t.l, {font:'body', size:ls, wt:700, color:tc, align:'c', lh:1.3, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(cx + (w - iw) / 2, y + vs * 1.05 + 14 + lh2 + 8, iw, xh, t.x, {font:'body', size:xs, color:tc === '#FFFFFF' ? '#DCE8DF' : '#2E3A33', align:'c', lh:1.3});
  });
}
/* 7. growth stems: rising stalks with leaves, hexagon value at the top */
function stemStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, hw = Math.min(cw * 0.78, 210, Z.h * 0.42), hh = hw * 0.866, iw = hw * 0.64;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), cw - 24, 52, {font:'body', wt:700, max:n <= 2 ? 21 : 18, min:12, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 24, 40, {font:'body', max:15, min:11, maxLines:2});
  var labH = ls * 1.3 * 2 + 6 + (st.some(function(t){ return t.x; }) ? xs * 1.3 * 2 + 6 : 0), gy = Z.y + Z.h - labH - 16;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, hh * 0.5, {font:'head', max:Math.round(hw * 0.26), min:20, maxLines:1});
  C.rect(Z.x, gy, Z.w, 4, {fill:FOREST, r:2});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), minS = 26, maxS = gy - Z.y - hh, sh = n === 1 ? maxS : minS + (maxS - minS) * k / (n - 1), top = gy - sh - hh, f = [MUST, GREEN, TEAL, FOREST][k % 4];
    C.rect(cx - 2.5, gy - sh - 4, 5, sh + 4, {fill:FOREST});
    for (var q = 0; q < Math.floor(sh / 46); q++) leaf(C, cx, gy - 18 - q * 46, 30, 18, q % 2 ? -140 : -40, q % 2 ? GREEN : '#A5C23A', 1);
    C.shape('hexagon', cx - hw / 2 - 5, top - 4.3, hw + 10, hh + 8.6, {fill:'#F1F2EF'});
    C.shape('hexagon', cx - hw / 2, top, hw, hh, {fill:f});
    C.text(cx - iw / 2, top, iw, hh, t.v, {font:'head', size:vs, color:K.on(f), align:'c', va:'m', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    var le = C.text(Z.x + cw * k + 12, gy + 16, cw - 24, ls * 1.3 * 2 + 2, t.l, {font:'body', size:ls, wt:700, color:FOREST, align:'c', lh:1.3, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(Z.x + cw * k + 12, gy + 16 + le._h + 6, cw - 24, xs * 1.3 * 2 + 2, t.x, {font:'body', size:xs, color:'#56605A', align:'c', lh:1.3});
  });
}
/* 8. hexagon chain timeline with hanging leaf cards */
function hexChain(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, hw = Math.min(170, cw - 26), hh = hw * 0.866, iw = cw - 50;
  var dd = it.map(function(t){ return {i:t.i, h:'', d:t.d}; });
  var F = fhd(C, dd, iw, 1, Z.h - hh - 46 - 70, {dMax:17});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var ch = Math.min(Z.h - hh - 46, th + 76), top = Z.y + Math.max(0, (Z.h - (hh + 46 + ch)) / 2), cy = top + hh / 2, cardY = top + hh + 46;
  C.rect(Z.x + cw / 2, cy - 4, Z.w - cw, 8, {fill:CHAR, r:4});
  for (var i = 0; i < n - 1; i++) C.rect(Z.x + cw * (i + 0.5) + hw / 2 + 8, cy - 1, cw - hw - 16, 2, {fill:MUST});
  var hs = C.groupFit(it.map(function(t){ return t.h; }), hw * 0.66, hh * 0.5, {font:'head', max:Math.round(hw * 0.22), min:13, maxLines:1});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), f = [FOREST, MUST, GREEN, TEAL][k % 4];
    C.shape('hexagon', cx - hw / 2 - 5, cy - hh / 2 - 4.3, hw + 10, hh + 8.6, {fill:'#F1F2EF'});
    C.shape('hexagon', cx - hw / 2, cy - hh / 2, hw, hh, {fill:f});
    C.text(cx - hw * 0.33, cy - hh / 2, hw * 0.66, hh, t.h || u.pad2(k + 1), {font:'head', size:hs, color:K.on(f), align:'c', va:'m', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.line(cx, cy + hh / 2 + 4, cx, cardY, {color:f === MUST ? '#C9A21F' : f, lw:2, dash:true});
    leafRect(C, cx - cw / 2 + 8, cardY, cw - 16, ch, 26, {fill:'#FFFFFF', stroke:'#D9DCD5', lw:1.2});
    leaf(C, cx - 13, cardY + 30, 26, 16, -35, f, 1);
    C.text(cx - iw / 2, cardY + 52, iw, ch - 60, t.d, {font:'body', size:F.ds, color:'#2E3A33', align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 9. agenda: zig-zag hexagon ladder */
function hexLadder(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 800 ? 2 : 1), per = Math.ceil(n / cols), gx = 60, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(n <= 4 ? 112 : 96, Z.h / per);
  var hw = Math.min(n <= 4 ? 84 : 70, (rh - 6) / 0.866), off = hw * 0.62, tx = off + hw + 22, tw = cw - tx;
  var y0 = Z.y + (Z.h - rh * per) / 2;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), tw, rh * 0.5, {font:'head', max:n <= 4 ? 30 : 26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), tw, rh * 0.4, {font:'body', max:17, min:11, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, hx = x + (r % 2 ? off : 0), f = [FOREST, MUST, GREEN, TEAL][k % 4];
    if (r > 0) C.line(x + (r % 2 ? 0 : off) + hw / 2, y - rh / 2 + hw * 0.3, hx + hw / 2, y + rh / 2 - hw * 0.3, {color:'#C9CFC4', lw:2});
    hexBadge(C, hx, y + (rh - hw * 0.866) / 2, hw, k + 1, f);
    var two = t.h && t.d;
    C.text(x + tx, y + (two ? rh * 0.1 : 0), tw, two ? rh * 0.48 : rh, t.h || t.d, {font:'head', size:hs, color:FOREST, va:two ? 'b' : 'm', lh:1.15, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    if (two) C.text(x + tx, y + rh * 0.6, tw, rh * 0.38, t.d, {font:'body', size:ds, color:'#56605A', lh:1.3, maxLines:1, ed:{f:'x-part', i:t.i, p:1}});
    C.rect(x + tx, y + rh - 1, tw, 1, {fill:'#D9DCD5'});
  });
}
/* 10. leaf tiles grid (agenda + many items) */
function leafTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : Math.ceil(n / 2), rows = Math.ceil(n / cols), gap = 18, cw = (Z.w - gap * (cols - 1)) / cols, ch = Math.min((Z.h - gap * (rows - 1)) / rows, cw * 0.95), pad = 20, iw = cw - pad * 2, oy = (Z.h - ch * rows - gap * (rows - 1)) / 2;
  var ns = Math.min(44, ch * 0.26), tBox = ch - pad * 2 - ns - 14;
  var F = fhd(C, it, iw, 56, tBox, {hMax:22, dMax:16.5});
  var ls = C.groupFit(it.map(function(t){ return t.h ? '' : t.d; }), iw, tBox, {font:'head', max:n <= 5 ? 26 : 21, min:13, maxLines:3});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gap) / 2;
    var x = Z.x + sh + c * (cw + gap), y = Z.y + oy + r * (ch + gap), dark = k % 3 === 0, f = dark ? FOREST : '#FFFFFF';
    leafRect(C, x, y, cw, ch, Math.min(44, ch * 0.3), {fill:f, stroke:dark ? null : '#D5DAD0', lw:1.2});
    leaf(C, x + cw - 16, y + 18, Math.min(52, ch * 0.3), 34, 150, dark ? GREEN : '#DDE4D6', dark ? 0.7 : 1);
    C.text(x + pad, y + pad - 4, iw, ns, u.pad2(k + 1), {font:'head', size:ns, wt:800, color:dark ? MUST : GREEN, lh:1, caps:false, ls:0});
    var tc = dark ? '#FFFFFF' : FOREST, by = y + ch - pad;
    if (!t.h) { var m = C.measureText(t.d, iw, {font:'head', max:ls, min:ls, maxLines:3}); C.text(x + pad, by - m.h, iw, m.h + 2, t.d, {font:'head', size:ls, color:tc, lh:m.lh, ed:{f:'x-part', i:t.i, p:1}, maxLines:3}); }
    else { var bhh = blockH(C, t, F, iw); K.hd(C, x + pad, by - bhh, iw, bhh + 2, t, F, {tc:tc, mc:dark ? '#D6E3D9' : '#56605A'}); }
    C.rect(x + pad, y + pad + ns + 6, 36, 4, {fill:dark ? MUST : GREEN, r:2});
  });
}
D.register({
  id:'forest', name:'Forest Campaign', tag:'Green · mustard · campaigns', desc:'Warm grey pages with forest green and mustard, sweeping roads, step bars and hexagon markers for campaigns and roadmaps.',
  fonts:{head:'Urbanist', body:'Urbanist', headW:800, bodyW:500, headLh:1.04, gf:'Urbanist:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Forest panel + roads',{}), V('v2','Forest panel + image',{img:true})],
    closing:[V('v1','Roads + forest panel',{}), V('v2','Image + forest panel',{img:true})],
    agenda:[V('v1','Hexagon ladder',{body:hexLadder}), V('n2','Leaf tiles',{body:leafTiles}), V('v2','Ladder + image right',{body:hexLadder, img:'right', cols:1})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Leaf panels',{body:leafPanels}), V('n2','Fork in the road',{body:forkPath}), V('n3','Honeycomb pair',{body:honeyRow}), V('v2','Image left + leaf panels',{body:leafPanels, img:'left'}), V('v3','Vine + image right',{body:vine, img:'right'})],
    p3:[V('v1','Honeycomb strip',{body:honeyRow}), V('n2','Vine',{body:vine}), V('n3','Leaf tabs',{body:'xTabs'}), V('n4','Hexagon path',{body:'xHex'}), V('v2','Image left + vine',{body:vine, img:'left'}), V('v3','Leaf tiles + image right',{body:leafTiles, img:'right'})],
    p4:[V('v1','Honeycomb strip',{body:honeyRow}), V('n2','Honeycomb',{body:honeycomb}), V('n3','Vine',{body:vine}), V('n4','Leaf tabs',{body:'xTabs'}), V('n5','Leaf tiles',{body:leafTiles}), V('v2','Vine + image right',{body:vine, img:'right'})],
    p56:[V('v1','Honeycomb',{body:honeycomb}), V('n2','Vine',{body:vine}), V('n3','Leaf tiles',{body:leafTiles}), V('n4','Leaf tabs',{body:'xTabs'}), V('v2','Vine + image right',{body:vine, img:'right'})],
    p78:[V('v1','Honeycomb',{body:honeycomb}), V('n2','Leaf tiles',{body:leafTiles}), V('n3','Two vines',{body:vine, cols:2}), V('v2','Vine + image right',{body:vine, img:'right', cols:1})],
    stats:[V('v1','Hexagon stats',{body:hexStats}), V('n2','Growth stems',{body:stemStats}), V('v2','Image left + stems',{body:stemStats, img:'left'})],
    timeline:[V('v1','Hexagon chain',{body:hexChain}), V('n2','Vine',{body:vine}), V('n3','Hexagon path',{body:'xHex'}), V('v2','Vine + image right',{body:vine, img:'right'})],
    quote:[V('v1','Mustard quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
