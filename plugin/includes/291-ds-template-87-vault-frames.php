<?php
/**
 * Code Snippets export — DS Template 87 — Vault Frames
 *
 * Snippet ID : 1121
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 09:43:31
 * Load order : 291 of 296
 */
/**
 * DS Template 87 — Vault Frames
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3" and "DS Template Kit 4" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl87_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl87_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('vault', array(
        'name'     => "Vault Frames",
        'fonts'    => array('head' => "Epilogue", 'body' => "Readex Pro"),
        'pal'      => array('bg' => '#E7EDF2', 'text' => '#1B2532', 'accent' => '#263342', 'accent2' => '#3FD0B0'),
        'voice'    => "Assurance voice: calm, precise and reassuring, risk-aware.",
        'recipe'   => "Threat landscape, current posture, layers of protection, controls, metrics, roadmap, assurance, contact.",
        'generate' => 'ds_tpl87_generate',
        'build'    => 'ds_tpl87_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl87_generate') ) {
    function ds_tpl87_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl87_build') ) {
    function ds_tpl87_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-vault">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 87 - Vault Frames : nested square frames receding like a vault door, slate and mint */
var BG = '#E7EDF2', SL = '#263342', SL2 = '#344558', MI = '#3FD0B0', WH = '#FFFFFF', GY = '#8FA0B3', INK = '#1B2532', MU = '#566476';
var P0 = {bg:BG, text:INK, title:SL, muted:MU, accent:SL, accent2:MI, card:WH, line:'#CBD5DF', onAccent:'#FFFFFF'};
/* nested frames toward a vanishing point */
function tunnel(C, x, y, w, h, n, c, core){
  var d = []; for (var i = 0; i < n; i++) { var t = i / n, ix = x + w * t * 0.5 * 0.86, iy = y + h * t * 0.5 * 0.86, iw = w * (1 - t * 0.86), ih = h * (1 - t * 0.86); d.push(['M', ix, iy], ['L', ix + iw, iy], ['L', ix + iw, iy + ih], ['L', ix, iy + ih], ['Z']); }
  C.path(d, {stroke:c || GY, lw:1.6});
  if (core) C.rect(x + w / 2 - w * 0.07, y + h / 2 - h * 0.07, w * 0.14, h * 0.14, {fill:core});
}
function corners(C, x, y, w, h, c, len){ len = len || 18; C.path([['M', x, y + len], ['L', x, y], ['L', x + len, y], ['M', x + w - len, y], ['L', x + w, y], ['L', x + w, y + len], ['M', x + w, y + h - len], ['L', x + w, y + h], ['L', x + w - len, y + h], ['M', x + len, y + h], ['L', x, y + h], ['L', x, y + h - len]], {stroke:c || MI, lw:3}); }
function shield(C, x, y, s, c){ C.path([['M', x + s / 2, y], ['L', x + s, y + s * 0.18], ['C', x + s, y + s * 0.62, x + s * 0.78, y + s * 0.86, x + s / 2, y + s], ['C', x + s * 0.22, y + s * 0.86, x, y + s * 0.62, x, y + s * 0.18], ['Z']], {fill:c || SL}); C.path([['M', x + s * 0.3, y + s * 0.5], ['L', x + s * 0.45, y + s * 0.64], ['L', x + s * 0.72, y + s * 0.36]], {stroke:MI, lw:Math.max(2, s * 0.08)}); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  tunnel(C, W - 150, -60, 220, 220, 6, '#C3CEDA', null);
}
function head(C, A, o){
  var y = 52;
  C.rect(A.x, y + 2, 12, 12, {fill:MI}); C.rect(A.x + 3, y + 5, 6, 6, {fill:SL});
  C.text(A.x + 22, y, A.w, 16, 'LEVEL ' + pad2(C.i + 1), {font:'head', size:12, wt:700, color:MU, ls:0.16, lh:1, va:'m'});
  y += 26;
  var te = C.text(A.x, y, Math.min(A.w, 880), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:SL, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:INK, mc:MU, ac:SL, markS:44, g:18, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:WH}); C.rect(x + 10, y + 10, w - 20, h - 20, {line:'#CBD5DF', lw:1.2}); C.rect(x + 20, y + 20, w - 40, h - 40, {line:'#DCE3EA', lw:1}); return {tc:INK, mc:MU, ac:SL}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:SL}); corners(C, x + 10, y + 10, w - 20, h - 20, MI, 22); return {tc:'#FFFFFF', mc:'#B9C6D4', ac:MI}; }
    C.rect(x, y, w, h, {fill:WH}); corners(C, x + 8, y + 8, w - 16, h - 16, k % 2 ? MI : SL, 18);
    return {tc:INK, mc:MU, ac:SL};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? BG : WH}); return {tc:INK, mc:MU, ac:SL}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.rect(x, y, s, s, {fill:on ? MI : SL}); C.rect(x + 4, y + 4, s - 8, s - 8, {line:on ? SL : MI, lw:1.2}); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, on ? SL : '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ shield(C, x + s * 0.1, y, s * 0.8, k % 2 ? SL2 : SL); },
  node:function(C, cx, cy, s, k){ var z = s * (1 - k * 0.08); C.rect(cx - z / 2, cy - z / 2, z, z, {fill:SL}); C.rect(cx - z / 2 + 5, cy - z / 2 + 5, z - 10, z - 10, {line:MI, lw:1.5}); C.text(cx - z / 2, cy - z / 2, z, z, pad2(k + 1), {font:'head', size:z * 0.32, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:SL, lw:2}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y + 8, W - 144, Z.h - 26, {r:0}); corners(C, 60, Z.y - 4, W - 120, Z.h - 2, MI, 26); return; }
    var x = Z.x + (side === 'right' ? 10 : 46), w = Z.w - 56, y = 60, h = H - 140;
    C.rect(x - 16, y - 16, w + 32, h + 32, {fill:SL}); C.rect(x - 8, y - 8, w + 16, h + 16, {line:MI, lw:1.5});
    D.imgBox(C, x, y, w, h, {r:0});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.company || '', {font:'head', size:12, wt:700, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 120, y, 120, 16, 'SECURE · ' + pad2(C.i + 1), {font:'head', size:11.5, wt:700, color:SL, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[SL, MI, GY, '#7EE3CC', '#566476', '#B9C6D4'], header:{kind:'left', top:52}, card:{kind:'solid', fill:WH, r:0}, icon:{kind:'square', bg:SL, fg:MI, r:0}, num:{kind:'big', fg:SL},
  conc:{kind:'band', fill:SL, r:0, label:'Assurance', tc:'#FFFFFF', lc:MI}, footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#8FA0B3'], [100, '#263342']]}}};
/* signature: layers of protection - points as nested boxes, outermost first */
function layers(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2) return B.cards(C, Z, o);
  var st = Math.min(66, Z.h / (n * 2 + 0.6)), tw = Z.w * 0.6;
  var F = K.fitHD(C, it, tw, 30, st * 1.5, {hMax:24, dMax:17, hMin:11, dMin:9.5, hLines:1, noBoost:true});
  it.forEach(function(t, k){ var ix = Z.x + k * st * 1.6, iy = Z.y + k * st, iw = Z.w - k * st * 3.2, ih = Z.h - k * st * 2, last = k === n - 1;
    C.rect(ix, iy, iw, ih, {fill:last ? SL : (k % 2 ? '#F4F7FA' : WH), line:k ? SL : GY, lw:1.4});
    var ty = iy + 8, th = last ? ih - 16 : st - 10;
    C.text(ix + 16, ty, 40, 22, pad2(k + 1), {font:'head', size:14, wt:800, color:last ? MI : SL, lh:1, va:'m'});
    if (last) { var bh = K3.bH(C, t, iw - 80, F); K.hd(C, ix + 56, iy + (ih - bh) / 2, iw - 80, ih - 10, t, F, {tc:'#FFFFFF', mc:'#B9C6D4'}); }
    else { C.text(ix + 56, ty, iw - 80, st - 12, (t.h ? t.h + ' — ' : '') + t.d, {font:'body', fit:{max:18, min:10}, color:INK, va:'m', lh:1.25, ed:{f:'x-part', i:t.i, p:t.h ? 1 : 1}, maxLines:2}); }
  });
}
/* signature: depth numbers - more frames = bigger value */
function depthStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 20, cw = (Z.w - g * (n - 1)) / n, s = Math.min(cw, Z.h * 0.62), vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 20;
  var vs = C.groupFit(vals, s * 0.6, s * 0.3, {font:'head', max:46, min:16, maxLines:1}), ls = C.groupFit(labs, tw, 46, {font:'body', max:16, min:11, maxLines:2});
  st.forEach(function(t, k){ var x = Z.x + k * (cw + g) + (cw - s) / 2;
    tunnel(C, x, Z.y, s, s, 4 + k, k % 2 ? GY : SL, null);
    C.rect(x + s * 0.25, Z.y + s * 0.32, s * 0.5, s * 0.36, {fill:k === 0 ? MI : SL});
    C.text(x + s * 0.2, Z.y + s * 0.32, s * 0.6, s * 0.36, vals[k], {font:'head', size:vs, wt:800, color:k === 0 ? SL : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + k * (cw + g) + 10, Z.y + s + 16, tw, 46, labs[k], {font:'body', size:ls, wt:700, color:INK, align:'c', lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  C.rect(0, 0, W, H, {fill:SL});
  if (o.img === 'photo') { D.imgBox(C, 720, 80, 480, 560, {r:0}); C.rect(712, 72, 496, 576, {line:MI, lw:2}); tunnel(C, 720, 80, 480, 560, 4, '#FFFFFF', null); }
  else tunnel(C, 690, 60, 520, 600, 9, '#5B6E84', MI);
  if (f.company) C.text(72, 70, 560, 20, f.company, {font:'head', size:14, wt:700, color:MI, caps:true, ls:0.14, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 140, 580, 260, C.s.title, {font:'head', fit:{max:64, min:28}, wt:800, color:'#FFFFFF', lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(72, 420, 24, 24, {fill:MI}); C.rect(78, 426, 12, 12, {fill:SL});
  if (C.s.subtitle) C.text(72, 462, 560, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:'#B9C6D4', lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 590, 560, 22, who, {font:'head', size:14, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  C.rect(0, 0, W, H, {fill:SL});
  if (o.img === 'photo') { D.imgBox(C, 0, 0, W, H, {r:0}); C.rect(0, 0, W, H, {fill:SL, op:0.75}); }
  var d = []; for (var i = 0; i < 6; i++) { var p = 40 + i * 34; d.push(['M', p, p * 0.56], ['L', W - p, p * 0.56], ['L', W - p, H - p * 0.56], ['L', p, H - p * 0.56], ['Z']); } C.path(d, {stroke:'#5B6E84', lw:1.5});
  C.text(260, 180, 760, 160, C.s.title || 'Thank you', {font:'head', fit:{max:76, min:32}, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(300, 340, 680, 40, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#B9C6D4', align:'c', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C), cw = 760 / Math.max(1, cs.length);
  cs.forEach(function(c, k){ var x = 260 + k * cw; C.rect(x + cw / 2 - 20, 410, 40, 40, {fill:MI}); C.icon(x + cw / 2 - 10, 420, 20, c.icon, SL); C.text(x, 460, cw, 50, c.val, {font:'body', fit:{max:15, min:10.5}, wt:600, color:'#FFFFFF', align:'c', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:2}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 680 : 980, x = photo ? 72 : 150;
  if (photo) { D.imgBox(C, 800, 70, 400, 580, {r:0}); C.rect(792, 62, 416, 596, {line:SL, lw:2}); }
  C.rect(x, 110, w, 480, {fill:WH}); C.rect(x + 20, 130, w - 40, 440, {line:'#CBD5DF', lw:1.2}); C.rect(x + 40, 150, w - 80, 400, {line:MI, lw:2});
  var qe = C.text(x + 80, 190, w - 160, 280, C.s.title, {font:'head', fit:{max:38, min:18}, wt:700, color:SL, align:'c', va:'m', lh:1.22, ed:{f:'title'}, maxLines:6});
  if (by) C.text(x + 80, 490, w - 160, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:MU, align:'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Locked cards', rows:'Shield checklist', steps:'Zoom-in steps', stmt:'Frame panel', stats:'Number cards', photo:'framed photo', track:'#CBD5DF'};
var VS = K4.vset(pg, B, L, {
  cover:[['Vault tunnel', {}], ['Vault + photo', {img:'photo'}]],
  closing:[['Opening frames', {}], ['Frames over photo', {img:'photo'}]],
  quote:[['Frame in a frame', {}], ['Frames + photo', {img:'photo'}]],
  p3:[['Layers of protection', pg(layers)]], p4:[['Layers of protection', pg(layers)]],
  stats:[['Depth numbers', pg(depthStats)]]
});
D.register({
  id:'vault', name:'Vault Frames', tag:'Slate · nested frames',
  desc:'Nested square frames receding like a vault door, locked corner brackets, shield checklists and layered boxes in slate and mint. Secure and trustworthy for security, insurance, compliance, risk and consulting.',
  fonts:{head:'Epilogue', body:'Readex Pro', headW:800, bodyW:400, headLh:1.04, gf:'Epilogue:wght@500;600;700;800|Readex+Pro:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

</script>
<?php
}, 1104);
