<?php
/**
 * Code Snippets export — DS Template 82 — Caliper
 *
 * Snippet ID : 1116
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 09:43:27
 * Load order : 286 of 296
 */
/**
 * DS Template 82 — Caliper
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3" and "DS Template Kit 4" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl82_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl82_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('caliper', array(
        'name'     => "Caliper",
        'fonts'    => array('head' => "Chakra Petch", 'body' => "Titillium Web"),
        'pal'      => array('bg' => '#F5F6F7', 'text' => '#2B2F33', 'accent' => '#2B2F33', 'accent2' => '#FFD400'),
        'voice'    => "Engineering voice: precise, specification-led and practical, with exact measures.",
        'recipe'   => "Scope, current state, requirements, the solution and components, specifications, performance, schedule, approval.",
        'generate' => 'ds_tpl82_generate',
        'build'    => 'ds_tpl82_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl82_generate') ) {
    function ds_tpl82_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl82_build') ) {
    function ds_tpl82_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-caliper">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 82 - Caliper : light grey drawing sheet, ruler ticks, dimension arrows, title blocks, safety yellow */
var BG = '#F5F6F7', GF = '#2B2F33', YE = '#FFD400', ST = '#9AA3AD', WH = '#FFFFFF', MU = '#59616B', LN = '#D3D8DE';
var P0 = {bg:BG, text:GF, title:GF, muted:MU, accent:GF, accent2:YE, card:WH, line:LN, onAccent:GF};
function ruler(C, horiz, pos, from, to, step){ var d = []; for (var v = from, i = 0; v <= to; v += step, i++) { var l = i % 5 ? 7 : 14; if (horiz) { d.push(['M', v, pos]); d.push(['L', v, pos + l]); } else { d.push(['M', pos, v]); d.push(['L', pos + l, v]); } } C.path(d, {stroke:GF, lw:1}); }
function dim(C, x1, x2, y, label, c){ c = c || GF; C.line(x1, y, x2, y, {color:c, lw:1.2}); C.line(x1, y - 7, x1, y + 7, {color:c, lw:1.2}); C.line(x2, y - 7, x2, y + 7, {color:c, lw:1.2}); K.poly(C, [[x1, y], [x1 + 9, y - 4], [x1 + 9, y + 4]], {fill:c}); K.poly(C, [[x2, y], [x2 - 9, y - 4], [x2 - 9, y + 4]], {fill:c}); if (label) { var w = label.length * 7 + 16; C.rect((x1 + x2) / 2 - w / 2, y - 9, w, 18, {fill:BG}); C.text((x1 + x2) / 2 - w / 2, y - 9, w, 18, label, {font:'head', size:11, wt:600, color:c, align:'c', va:'m', lh:1}); } }
function hazard(C, x, y, w, h){ C.rect(x, y, w, h, {fill:YE}); var d = []; for (var i = -h; i < w; i += 16) { d.push(['M', x + Math.max(0, i), y + (i < 0 ? -i : 0)]); d.push(['L', x + Math.min(w, i + h), y + h - Math.max(0, i + h - w)]); d.push(['L', x + Math.min(w, i + h + 7), y + h - Math.max(0, i + h + 7 - w)]); d.push(['L', x + Math.max(0, i + 7), y + (i + 7 < 0 ? -(i + 7) : 0)]); d.push(['Z']); } C.path(d, {fill:GF}); }
function bg(C, kind){
  C.bg({fill:BG});
  ruler(C, true, 0, 0, W, 10); ruler(C, false, 0, 20, H, 10);
}
function head(C, A, o){
  var y = 50;
  C.text(A.x, y, A.w, 16, 'DWG ' + pad2(C.i + 1) + '  ·  ' + (C.cover.company || 'Technical').toUpperCase(), {font:'head', size:11.5, wt:700, color:MU, ls:0.12, lh:1, va:'m', maxLines:1});
  y += 24;
  var te = C.text(A.x, y, Math.min(A.w, 900), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:700, color:GF, caps:true, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 8;
  C.rect(A.x, y, 54, 8, {fill:YE}); y += 18;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 780), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:GF, mc:MU, ac:GF, markS:42, g:18, head:head, ptc:GF, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:WH, line:GF, lw:1.5}); C.rect(x, y + h - 40, w, 40, {line:GF, lw:1.5}); C.text(x + 16, y + h - 40, w - 32, 40, 'REV 03  ·  APPROVED  ·  SCALE 1:1', {font:'head', size:11, wt:700, color:MU, va:'m', lh:1}); return {tc:GF, mc:MU, ac:GF}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:GF}); C.rect(x, y, 6, h, {fill:YE}); return {tc:WH, mc:'#C5CBD2', ac:YE}; }
    C.rect(x, y, w, h, {fill:WH, line:LN, lw:1.2});
    K.poly(C, [[x, y], [x + 26, y], [x, y + 26]], {fill:YE}); K.poly(C, [[x + w, y + h], [x + w - 26, y + h], [x + w, y + h - 26]], {fill:YE});
    C.text(x + w - 80, y + 10, 66, 14, 'A-' + pad2(k + 1), {font:'head', size:11, wt:700, color:ST, align:'r', lh:1});
    return {tc:GF, mc:MU, ac:GF};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? BG : WH, line:LN, lw:1}); return {tc:GF, mc:MU, ac:GF}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === WH; C.rect(x, y, s, s, {fill:on ? YE : GF}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? GF : YE); },
  rowMark:function(C, x, y, s, t, k){ C.rect(x, y + s * 0.18, s + 10, s * 0.64, {fill:YE}); C.text(x, y + s * 0.18, s + 10, s * 0.64, 'A-' + pad2(k + 1), {font:'head', size:s * 0.3, wt:700, color:GF, align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ C.line(cx, cy - s * 0.5, cx, cy + s * 0.5, {color:GF, lw:3}); C.rect(cx - s * 0.36, cy - s * 0.18, s * 0.72, s * 0.36, {fill:YE}); C.text(cx - s * 0.36, cy - s * 0.18, s * 0.72, s * 0.36, pad2(k + 1), {font:'head', size:s * 0.24, wt:700, color:GF, align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1 - 70, y - 9, x2 - x1 + 140, 18, {fill:WH, line:GF, lw:1}); ruler(C, true, y - 9, x1 - 66, x2 + 66, 8); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y + 26, W - 144, Z.h - 44, {r:0}); dim(C, 72, W - 72, Z.y + 10, (W - 144) + ' mm'); return; }
    var x = Z.x + (side === 'right' ? 10 : 46), w = Z.w - 56, y = 80, h = H - 180;
    D.imgBox(C, x, y, w, h, {r:0}); C.rect(x, y, w, h, {line:GF, lw:1.5});
    dim(C, x, x + w, y - 24, Math.round(w) + ' mm'); hazard(C, x, y + h + 10, w, 14);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 42; C.rect(a.x, y - 6, a.w, 1, {fill:LN}); C.text(a.x, y, 420, 16, C.cover.website || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 200, y, 200, 16, 'SHEET ' + pad2(C.i + 1) + ' OF ' + pad2(C.total), {font:'head', size:11.5, wt:700, color:GF, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[GF, YE, ST, '#5B6670', '#FFE680', '#C5CBD2'], header:{kind:'left', top:50}, card:{kind:'outline', r:0}, icon:{kind:'square', bg:GF, fg:YE, r:0}, num:{kind:'big', fg:GF},
  conc:{kind:'box', fill:WH, r:0, line:GF, lw:1.2, label:'Note', lc:GF, tc:GF}, footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#C5CBD2'], [100, '#5B6670']]}}};
/* signature: gauges - semicircle dials with needles */
function gauges(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.38, Z.h * 0.38, 120), cy = Z.y + r + 20, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 24;
  var vs = C.groupFit(vals, tw, 60, {font:'head', max:46, min:18, maxLines:1}), ls = C.groupFit(labs, tw, 46, {font:'body', max:16, min:11, maxLines:2});
  st.forEach(function(t, k){ var cx = Z.x + cw * (k + 0.5), num = parseFloat(String(vals[k]).replace(/[^0-9.]/g, '')) || 50, pct = /%/.test(vals[k]) ? Math.min(100, Math.abs(num)) : 40 + (k * 17) % 50;
    var track = [], fill = [];
    for (var i = 0; i <= 24; i++) { var a = (180 + 180 * i / 24) * RAD; track.push([i ? 'L' : 'M', cx + Math.cos(a) * r, cy + Math.sin(a) * r]); }
    C.path(track, {stroke:LN, lw:r * 0.16});
    var m = Math.max(1, Math.round(24 * pct / 100)); for (var j = 0; j <= m; j++) { var b = (180 + 180 * j / 24) * RAD; fill.push([j ? 'L' : 'M', cx + Math.cos(b) * r, cy + Math.sin(b) * r]); }
    C.path(fill, {stroke:YE, lw:r * 0.16});
    var na = (180 + 180 * pct / 100) * RAD; C.line(cx, cy, cx + Math.cos(na) * r * 0.78, cy + Math.sin(na) * r * 0.78, {color:GF, lw:4}); C.ell(cx - 9, cy - 9, 18, 18, {fill:GF});
    C.text(cx - tw / 2, cy + 20, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:700, color:GF, align:'c', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(cx - tw / 2, cy + 26 + vs * 1.1, tw, 46, labs[k], {font:'body', size:ls, wt:600, color:MU, align:'c', lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
  });
}
/* signature: callouts - leader lines from a component block to each point */
function callouts(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
  var bw = Math.min(260, Z.w * 0.24), bx = Z.x + (Z.w - bw) / 2, by = Z.y + Z.h * 0.18, bh = Z.h * 0.64;
  C.rect(bx, by, bw, bh, {fill:WH, line:GF, lw:2}); hazard(C, bx, by + bh - 16, bw, 16);
  C.icon(bx + bw / 2 - 40, by + bh / 2 - 48, 80, D.iconFor(C.s, 0, C.s.title || ''), GF);
  dim(C, bx, bx + bw, by - 18, Math.round(bw) + ' mm');
  var tw = (Z.w - bw) / 2 - 60, F = K.fitHD(C, it, tw, 40, Z.h / Math.ceil(n / 2) - 20, {hMax:20, dMax:15, hMin:12, dMin:10});
  it.forEach(function(t, k){ var left = k % 2 === 0, row = Math.floor(k / 2), rows = Math.ceil(n / 2), y = Z.y + (row + 0.5) * Z.h / rows, tx = left ? Z.x : bx + bw + 60, ax = left ? bx : bx + bw, bh2 = K3.bH(C, t, tw, F);
    C.line(left ? Z.x + tw + 10 : tx - 10, y, ax, y, {color:GF, lw:1.2}); C.ell(ax - 5, y - 5, 10, 10, {fill:YE, line:GF, lw:1.5});
    C.text(left ? Z.x + tw - 50 : tx, y - bh2 / 2 - 22, 60, 16, 'A-' + pad2(k + 1), {font:'head', size:11, wt:700, color:ST, align:left ? 'r' : 'l', lh:1});
    K.hd(C, tx, y - bh2 / 2, tw, Z.h / rows, t, F, {tc:GF, mc:MU, align:left ? 'r' : 'l'});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function block(C, x, y, w, rows){ var h = rows.length * 30; C.rect(x, y, w, h, {fill:WH, line:GF, lw:1.5}); rows.forEach(function(r, k){ if (k) C.rect(x, y + k * 30, w, 1, {fill:GF}); C.rect(x + 110, y + k * 30, 1, 30, {fill:GF}); C.text(x + 10, y + k * 30, 96, 30, r[0], {font:'head', size:10.5, wt:700, color:MU, va:'m', lh:1}); C.text(x + 122, y + k * 30, w - 132, 30, r[1] || '', {font:'body', size:12.5, wt:600, color:GF, va:'m', lh:1, ed:r[2] || null, maxLines:1}); }); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) { D.imgBox(C, 760, 70, 450, 380, {r:0}); C.rect(760, 70, 450, 380, {line:GF, lw:1.5}); dim(C, 760, 1210, 50, '450 mm'); }
  else { C.rect(760, 70, 450, 380, {fill:WH, line:GF, lw:1.5}); C.ell(870, 150, 230, 230, {line:GF, lw:2}); C.ell(935, 215, 100, 100, {fill:YE, line:GF, lw:2}); C.line(820, 265, 1150, 265, {color:ST, lw:1, dash:true}); C.line(985, 100, 985, 430, {color:ST, lw:1, dash:true}); dim(C, 870, 1100, 50, 'Ø 230'); }
  C.text(72, 60, 640, 18, (f.company || '').toUpperCase(), {font:'head', size:13, wt:700, color:MU, ls:0.14, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 110, 650, 230, C.s.title, {font:'head', fit:{max:66, min:28}, wt:700, color:GF, caps:true, lh:1.02, va:'b', ed:{f:'title'}, maxLines:3});
  dim(C, 72, 700, 362, '');
  if (C.s.subtitle) C.text(72, 388, 620, 66, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  block(C, 72, 500, 640, [['PREPARED BY', [f.presenter, f.designation].filter(Boolean).join(' · '), {f:'tf-presenter'}], ['DATE', f.date || ''], ['WEB', f.website || '', {f:'tf-website'}], ['REVISION', 'REV 03 — ISSUED FOR REVIEW']]);
  hazard(C, 760, 500, 450, 120);
}
function closing(C, v){
  var o = C.o || {}, cs = D.contacts(C);
  if (o.img === 'photo') { D.imgBox(C, 760, 70, 450, 400, {r:0}); C.rect(760, 70, 450, 400, {line:GF, lw:1.5}); } else hazard(C, 760, 70, 450, 400);
  C.text(72, 90, 640, 200, C.s.title || 'Thank you', {font:'head', fit:{max:84, min:34}, wt:700, color:GF, caps:true, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 306, 620, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  block(C, 72, 400, 640, cs.map(function(c){ return [String(c.icon).replace(/^mdi:/, '').replace(/-.*/, '').toUpperCase(), c.val, c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null]; }).concat([['STATUS', 'APPROVED FOR NEXT STEPS']]));
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 680 : W - 144;
  if (photo) { D.imgBox(C, 800, 80, 410, H - 180, {r:0}); C.rect(800, 80, 410, H - 180, {line:GF, lw:1.5}); }
  C.rect(72, 120, w, 400, {fill:WH, line:GF, lw:1.5}); hazard(C, 72, 120, w, 16);
  C.rect(72, 460, w, 60, {line:GF, lw:1.5});
  var qe = C.text(110, 170, w - 76, 270, '“' + (C.s.title || '') + '”', {font:'head', fit:{max:40, min:18}, wt:600, color:GF, lh:1.2, va:'m', ed:{f:'title'}, maxLines:6});
  if (by) C.text(110, 460, w - 76, 60, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:GF, va:'m', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Spec cards', rows:'Spec rows', steps:'Ruler timeline', stmt:'Title block', stats:'Spec numbers', photo:'measured photo', track:'#D3D8DE'};
var VS = K4.vset(pg, B, L, {
  cover:[['Drawing sheet', {}], ['Drawing + photo', {img:'photo'}]],
  closing:[['Title block', {}], ['Title block + photo', {img:'photo'}]],
  quote:[['Title-block quote', {}], ['Quote + photo', {img:'photo'}]],
  p3:[['Callouts', pg(callouts)]], p4:[['Callouts', pg(callouts)]], p2:[['Callouts', pg(callouts)]],
  stats:[['Gauges', pg(gauges)]]
});
D.register({
  id:'caliper', name:'Caliper', tag:'Light grey · ruler + dimensions',
  desc:'Engineering precision on a light-grey drawing sheet: ruler ticks along the edges, dimension arrows, drawing title blocks, gauges and safety-yellow markers. For engineering, construction, manufacturing and technical proposals.',
  fonts:{head:'Chakra Petch', body:'Titillium Web', headW:700, bodyW:400, headLh:1.04, gf:'Chakra+Petch:wght@500;600;700|Titillium+Web:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

</script>
<?php
}, 1099);
