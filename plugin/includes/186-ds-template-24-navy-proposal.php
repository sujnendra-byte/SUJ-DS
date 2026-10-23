<?php
/**
 * Code Snippets export — DS Template 24 — Navy Proposal
 *
 * Snippet ID : 923
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:33:25
 * Load order : 181 of 191
 */
/**
 * DS Template 24 — Navy Proposal
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
    ds_tpl_register('navyproposal', array(
        'name'     => "Navy Proposal",
        'fonts'    => array('head' => "Raleway", 'body' => "Open Sans"),
        'pal'      => array('bg' => '#F7F9FC', 'text' => '#17233D', 'accent' => '#0D2B5E', 'accent2' => '#3F5F93'),
        'voice'    => "Formal proposal voice: structured, persuasive, deliverables and value to the client.",
        'recipe'   => "Introduction, understanding of needs, objectives, proposed solution, scope, timeline, investment, why us.",
        'generate' => 'ds_tpl24_generate',
        'build'    => 'ds_tpl24_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl24_generate') ) {
function ds_tpl24_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl24_build') ) {
function ds_tpl24_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-navyproposal">
/* Template 24 — Navy Proposal : glossy white pages, navy gradient edge blocks, rotated-square photos, triple chevrons, dot grids */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#0D2B5E', MID = '#3F5F93', PALE = '#DCE5F2';
var FADE = {ang:90, stops:[[0, '#0D2B5E'], [100, '#0D2B5E', 0]]}, FADEV = {ang:180, stops:[[0, '#0D2B5E'], [100, '#0D2B5E', 0]]};
var P0 = {bg:'#F7F9FC', text:'#17233D', title:'#0D2B5E', muted:'#55627E', accent:'#0D2B5E', accent2:'#3F5F93', card:'#FFFFFF', line:'#D8E0EC', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[NAVY, MID, '#6F8CB8', '#1D4E8F', '#9DB2D3', '#2C4776'],
  header:{kind:'left', tMax:42, tMin:24, sMax:16, top:88, kicker:null},
  card:{kind:'solid', r:4, fill:'#FFFFFF', shadow:0.08, hl:{kind:'grad', grad:{ang:135, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}, dark:true}},
  icon:{kind:'square', r:6, bg:NAVY, fg:'#FFFFFF', grad:{ang:135, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}},
  num:{kind:'square', r:4, bg:NAVY, fg:'#FFFFFF'},
  conc:{kind:'band', grad:{ang:90, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}, r:4, label:'Summary'},
  footer:{brand:false, page:true, color:'#8C98B0'}, xRows:'#FFFFFF', hubFill:'#E6ECF5', road:NAVY, flowBar:'#DCE3EE',
  imgFramed:true, imgR:4
};
function logo(C, x, y){
  var f = C.cover; if (!f.company) return;
  C.shape('diamond', x, y + 2, 18, 18, {line:NAVY, lw:2}); C.shape('diamond', x + 11, y + 2, 18, 18, {line:MID, lw:2});
  C.text(x + 38, y, 300, 22, f.company, {font:'body', size:13, wt:700, color:NAVY, va:'m', lh:1, ed:{f:'tf-company'}});
}
function bg(C, kind){
  C.bg({grad:{ang:120, stops:[[0, '#FFFFFF'], [55, '#F1F4F9'], [100, '#E6EBF3']]}});
  K.poly(C, [[W * 0.35, 0], [W * 0.55, 0], [W * 0.25, H], [W * 0.05, H]], {fill:'#FFFFFF', op:0.5});
  if (kind === 'cover' || kind === 'closing') return;
  var odd = (C.i || 0) % 2;
  logo(C, 70, 34);
  K.chev3(C, W - 150, 38, 26, NAVY, {hollow:true});
  if (odd) { C.rect(0, H - 60, W * 0.5, 22, {grad:FADE}); C.rect(W - 26, 90, 26, 200, {grad:FADEV}); K.dots(C, W - 170, H - 54, 6, 2, 12, 2.2, NAVY, 0.55); }
  else { C.rect(W * 0.5, H - 60, W * 0.5, 22, {grad:{ang:270, stops:[[0, '#0D2B5E'], [100, '#0D2B5E', 0]]}}); C.rect(0, 120, 26, 220, {grad:FADEV}); K.dots(C, 90, H - 54, 6, 2, 12, 2.2, NAVY, 0.55); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  logo(C, 70, 40);
  C.rect(0, 250, 40, 300, {grad:FADEV});
  K.diamondImg(C, W - 330, 330, 250, '#EEF1F7', {border:'#FFFFFF', bw:8});
  K.poly(C, [[W - 330, 80], [W - 80, 330], [W - 330, 580], [W - 580, 330]], {stroke:NAVY, lw:2});
  K.chev3(C, 520, 110, 30, NAVY, {hollow:true});
  C.ell(W - 60, 70, 12, 12, {fill:NAVY}); C.ell(W - 60, 96, 12, 12, {fill:NAVY}); C.ell(W - 60, 122, 12, 12, {fill:NAVY});
  C.text(70, 180, 560, 240, C.s.title, {font:'head', fit:{max:66, min:32}, color:NAVY, ed:{f:'title'}, maxLines:3, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(70, 440, 520, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var ls = [f.website, f.email].filter(Boolean);
  ls.forEach(function(l, k){ C.ell(70, 560 + k * 30, 18, 18, {fill:NAVY}); C.text(98, 558 + k * 30, 420, 22, l, {font:'body', size:14, wt:600, color:NAVY, va:'m', lh:1}); });
  K.dots(C, 420, H - 70, 8, 3, 14, 2.4, NAVY, 0.6);
}
function closing(C, v){
  var P = C.P;
  logo(C, 70, 40);
  C.rect(0, 250, 40, 300, {grad:FADEV});
  K.diamondImg(C, W - 330, 330, 250, '#EEF1F7', {border:'#FFFFFF', bw:8});
  K.poly(C, [[W - 330, 80], [W - 80, 330], [W - 330, 580], [W - 580, 330]], {stroke:NAVY, lw:2});
  K.chev3(C, 520, 130, 30, NAVY, {hollow:true});
  C.text(70, 180, 560, 180, C.s.title || 'Thank You', {font:'head', fit:{max:72, min:34}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(70, 380, 520, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  D.contactList(C, 70, 470, 520, {gap:50});
}
function agenda(C, v){
  var P = C.P;
  D.imgBox(C, 120, 110, 360, 420, {r:4}); C.rect(100, 530, 380, 16, {grad:FADE});
  K.dots(C, 90, H - 110, 6, 4, 14, 2.4, NAVY, 0.6);
  C.text(560, 110, 600, 70, C.s.title || 'Content', {font:'head', fit:{max:46, min:28}, color:NAVY, ed:{f:'title'}, maxLines:1});
  var it = C.it.slice(0, 10), rh = Math.min(50, 400 / Math.max(1, it.length));
  it.forEach(function(t, k){
    var y = 200 + k * rh;
    C.rect(560, y + 8, 34, rh - 16, {grad:{ang:135, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}, r:3});
    C.text(560, y + 8, 34, rh - 16, u.pad2(k + 1), {font:'body', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
    C.text(612, y, 560, rh, t.h || t.d, {font:'body', fit:{max:20, min:12}, wt:600, color:P.text, va:'m', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:NAVY}); return; }
  D.B.quote(C, {x:200, y:140, w:W - 400, h:H - 280}, {qc:NAVY});
}
/* ================= signature bodies (navy proposal motif: rotated squares, triple chevrons, gradient edges, dot grids) ================= */
var GRAD = {ang:135, stops:[[0, NAVY], [100, MID]]}, TXT = '#17233D', MUT = '#55627E';
function fhd(C, it, w, hb, db, o){
  o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; var F = K.fitHD(C, it, w, hb, db, o), hh = 0;
  it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, w, {font:'head', max:F.hs, min:F.hs, lh:1.15, maxLines:o.hLines || 2}).h); });
  F.hh = hh; return F;
}
function blockH(C, t, F, w){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function hdn(C, x, y, w, h, t, F, o){ o = o || {}; o.tc = o.tc || NAVY; o.mc = o.mc || MUT; return K.hd(C, x, y, w, h, t, F, o); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
function dia(C, cx, cy, s, o){
  var h = s / 2;
  if (o.fill || o.grad) C.shape('diamond', cx - h, cy - h, s, s, {fill:o.fill, grad:o.grad, op:o.op});
  if (o.line) K.poly(C, [[cx, cy - h], [cx + h, cy], [cx, cy + h], [cx - h, cy]], {stroke:o.line, lw:o.lw || 1.5});
}
function diaNum(C, cx, cy, s, n, o){
  o = o || {};
  if (o.ring) dia(C, cx, cy, s + o.ring, {line:NAVY, lw:1.5});
  dia(C, cx, cy, s, {grad:GRAD});
  C.text(cx - s * 0.35, cy - s * 0.35, s * 0.7, s * 0.7, o.label != null ? o.label : u.pad2(n), {font:'head', size:Math.round(s * (o.fs || 0.3)), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.ed || null});
}
/* 1. diamond frames joined by triple chevrons */
function diamondFrames(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.42, Z.h * 0.34, 150), iw = cw - 30;
  var F = fhd(C, it, iw, 60, Z.h - s - 90, {hMax:22, dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, blockH(C, t, F, iw)); });
  var top = Z.y + Math.max(0, (Z.h - (s + 28 + 40 + th)) / 2), cy = top + s / 2 + 14;
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    diaNum(C, cx, cy, s, k + 1, {ring:28});
    if (k < n - 1) K.chev3(C, cx + cw / 2 - 16, cy - 8, 16, MID, {hollow:true});
    C.rect(cx - 18, cy + s / 2 + 26, 36, 3, {fill:NAVY});
    hdn(C, cx - iw / 2, cy + s / 2 + 42, iw, th + 4, t, F, {align:'c'});
  });
}
/* 2. rows with a gradient edge, big number and triple chevron */
function chevronRows(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 30, cw = (Z.w - gx * (cols - 1)) / cols, gy = per > 5 ? 8 : 14, rh = Math.min(130, (Z.h - gy * (per - 1)) / per);
  var y0 = Z.y + (Z.h - rh * per - gy * (per - 1)) / 2, ns = Math.min(34, rh * 0.5), tx = 22 + ns * 1.5 + 44, tw = cw - tx - 20;
  var F = fhd(C, it, tw, Math.min(34, rh * 0.42), rh - 12, {hMax:20, dMax:16, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    C.rect(x, y, cw, rh, {fill:'#FFFFFF', r:4, shadow:0.07});
    C.rect(x, y, 8, rh, {grad:{ang:180, stops:[[0, NAVY], [100, MID]]}});
    C.rect(x + 8, y, cw * 0.35, 3, {grad:{ang:90, stops:[[0, NAVY, 0.9], [100, NAVY, 0]]}});
    C.text(x + 22, y, ns * 1.5, rh, u.pad2(k + 1), {font:'head', size:ns, wt:800, color:NAVY, va:'m', lh:1, caps:false, ls:0});
    K.chev3(C, x + 22 + ns * 1.5 + 6, y + rh / 2 - 6, 12, MID, {hollow:true});
    var bhh = blockH(C, t, F, tw);
    hdn(C, x + tx, y + (rh - bhh) / 2, tw, bhh + 4, t, F, {hLines:1});
  });
}
/* 3. cards with a diamond badge, gradient top edge and dot grid */
function diamondCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4)), rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var s = Math.min(54, ch * 0.34), side = cw > 380, pad = 22, tx = side ? pad + s + 24 : pad, tw = cw - tx - pad;
  var F = fhd(C, it, tw, 48, ch - pad * 2 - (side ? 0 : s + 14), {hMax:21, dMax:16});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), shx = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + shx + c * (cw + gx), y = Z.y + r * (ch + gy), hl = k === 0;
    C.rect(x, y, cw, ch, hl ? {grad:GRAD, r:4} : {fill:'#FFFFFF', r:4, shadow:0.07});
    if (!hl) C.rect(x, y, cw * 0.6, 4, {grad:{ang:90, stops:[[0, NAVY], [100, NAVY, 0]]}});
    K.dots(C, x + cw - 58, y + ch - 30, 4, 2, 11, 1.8, hl ? '#FFFFFF' : NAVY, 0.35);
    var bhh = blockH(C, t, F, tw);
    if (side) {
      var cy = y + ch / 2; dia(C, x + pad + s / 2, cy, s + 12, {line:hl ? '#FFFFFF' : NAVY, lw:1.2});
      dia(C, x + pad + s / 2, cy, s, hl ? {fill:'#FFFFFF'} : {grad:GRAD});
      C.text(x + pad, cy - s / 2, s, s, u.pad2(k + 1), {font:'head', size:Math.round(s * 0.3), wt:800, color:hl ? NAVY : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
      hdn(C, x + tx, cy - bhh / 2, tw, bhh + 4, t, F, hl ? {tc:'#FFFFFF', mc:'#DCE5F2'} : {});
    } else {
      var blk = s + 14 + bhh, by = y + Math.max(pad, (ch - blk) / 2);
      dia(C, x + pad + s / 2, by + s / 2, s, hl ? {fill:'#FFFFFF'} : {grad:GRAD});
      C.text(x + pad, by, s, s, u.pad2(k + 1), {font:'head', size:Math.round(s * 0.3), wt:800, color:hl ? NAVY : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
      hdn(C, x + tx, by + s + 14, tw, bhh + 4, t, F, hl ? {tc:'#FFFFFF', mc:'#DCE5F2'} : {});
    }
  });
}
/* 4. proposal split: navy panel vs white panel, diamond hand-off */
function proposalSplit(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var cw = Z.w / n, pad = Math.min(48, cw * 0.09), iw = cw - pad * 2 - (n > 1 ? 30 : 0);
  var F = fhd(C, it, iw, 90, Z.h - pad * 2 - 40, {hMax:28, dMax:20, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, dark = k === 0, tx = x + pad + (k ? 30 : 0);
    C.rect(x, Z.y, cw, Z.h, dark ? {grad:GRAD, r:4} : {fill:'#FFFFFF', r:4, shadow:0.08});
    if (dark) K.dots(C, x + 26, Z.y + Z.h - 44, 6, 2, 12, 2, '#FFFFFF', 0.35); else K.dots(C, x + cw - 96, Z.y + Z.h - 44, 6, 2, 12, 2, NAVY, 0.4);
    var bhh = blockH(C, t, F, iw), by = Z.y + (Z.h - bhh - 40) / 2;
    C.text(tx, by, iw, 18, u.pad2(k + 1) + '  /  ' + (k ? 'Our answer' : 'The situation'), {font:'body', size:12, wt:700, color:dark ? '#C9D6EA' : MID, caps:true, ls:0.14, lh:1});
    C.rect(tx, by + 24, 44, 3, {fill:dark ? '#FFFFFF' : NAVY});
    hdn(C, tx, by + 40, iw, bhh + 4, t, F, dark ? {tc:'#FFFFFF', mc:'#DCE5F2'} : {});
  });
  if (n === 2) { var mx = Z.x + cw, my = Z.y + Z.h / 2; dia(C, mx, my, 104, {fill:'#FFFFFF', line:NAVY, lw:2}); K.chev3(C, mx - 24, my - 11, 22, NAVY, {hollow:true}); }
}
/* 5. stats inside rotated-square frames */
function diamondStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.74, Z.h * 0.58, 250), iw = cw - 24;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), s * 0.58, s * 0.3, {font:'head', max:Math.round(s * 0.24), min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 50, {font:'head', max:n <= 2 ? 22 : 18, min:13, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, 40, {font:'body', max:15, min:11, maxLines:2});
  var blk = s + 26 + ls * 1.2 * 2 + 8 + xs * 1.35 * 2, top = Z.y + Math.max(0, (Z.h - blk) / 2), cy = top + s / 2;
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), hl = k === 0;
    dia(C, cx, cy, s, {line:NAVY, lw:1.5});
    dia(C, cx, cy, s - 26, hl ? {grad:GRAD} : {fill:'#FFFFFF'});
    if (!hl) dia(C, cx, cy, s - 26, {line:'#C7D3E6', lw:1.5});
    C.text(cx - s * 0.29, cy - s * 0.15, s * 0.58, s * 0.3, t.v, {font:'head', size:vs, color:hl ? '#FFFFFF' : NAVY, align:'c', va:'m', lh:1, maxLines:1, caps:false, ls:0, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    var le = C.text(cx - iw / 2, cy + s / 2 + 22, iw, ls * 1.2 * 2 + 2, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(cx - iw / 2, cy + s / 2 + 22 + le._h + 8, iw, xs * 1.35 * 2 + 2, t.x, {font:'body', size:xs, color:MUT, align:'c', lh:1.35});
  });
}
/* 6. navy gradient band of figures, labels underneath */
function statBand(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, iw = cw - 30, bh = Math.min(Z.h * 0.42, 180);
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, bh * 0.55, {font:'head', max:72, min:20, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 80, {font:'head', max:21, min:13, maxLines:3, lh:1.2});
  var lh3 = 0; st.forEach(function(t){ lh3 = Math.max(lh3, C.measureText(t.l, iw, {font:'head', max:ls, min:ls, maxLines:3, lh:1.2}).h); });
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, 44, {font:'body', max:16, min:11, maxLines:2});
  var blk = bh + 30 + lh3 + 10 + xs * 1.35 * 2, top = Z.y + Math.max(0, (Z.h - blk) / 2);
  C.rect(Z.x, top, Z.w, bh, {grad:{ang:90, stops:[[0, NAVY], [100, MID]]}, r:4});
  K.chev3(C, Z.x + Z.w - 64, top + 16, 16, '#FFFFFF', {hollow:true});
  K.dots(C, Z.x + 20, top + bh - 26, 6, 2, 11, 1.8, '#FFFFFF', 0.35);
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.rect(x, top + 28, 1, bh - 56, {fill:'#FFFFFF', op:0.3});
    C.text(x + 15, top, iw, bh, t.v, {font:'head', size:vs, color:'#FFFFFF', align:'c', va:'m', lh:1, maxLines:1, caps:false, ls:0, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    dia(C, x + cw / 2, top + bh, 16, {fill:'#FFFFFF', line:NAVY, lw:2});
    var le = C.text(x + 15, top + bh + 26, iw, lh3 + 2, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 15, top + bh + 26 + le._h + 8, iw, xs * 1.35 * 2 + 2, t.x, {font:'body', size:xs, color:MUT, align:'c', lh:1.35});
  });
}
/* 7. diamond rail timeline: years above a fading rail, notes below */
function tlDiamondRail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, iw = cw - 26;
  var ys = C.groupFit(it.map(function(t){ return t.h || '00'; }), iw, 60, {font:'head', max:Math.min(54, cw * 0.22), min:14, maxLines:1});
  var F = fhd(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), iw - 24, 1, Z.h - ys - 110, {dMax:18.5});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw - 24, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var top = Z.y + Math.max(0, (Z.h - (ys + 40 + 44 + th + 40)) / 2), ry = top + ys + 40, cardY = ry + 40, ch = th + 40;
  C.rect(Z.x, ry - 1.5, Z.w, 3, {grad:{ang:90, stops:[[0, NAVY, 0], [12, NAVY], [88, NAVY], [100, NAVY, 0]]}});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    C.text(cx - iw / 2, top, iw, ys * 1.1, t.h || u.pad2(k + 1), {font:'head', size:ys, color:k === n - 1 ? MID : NAVY, align:'c', lh:1, maxLines:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    dia(C, cx, ry, 34, {fill:'#F7F9FC', line:NAVY, lw:1.5});
    dia(C, cx, ry, 18, {grad:GRAD});
    if (k < n - 1) K.chev3(C, cx + cw / 2 - 12, ry - 22, 10, MID);
    C.rect(cx - iw / 2, cardY, iw, ch, {fill:'#FFFFFF', r:4, shadow:0.07});
    C.rect(cx - iw / 2, cardY, iw, 4, {grad:{ang:90, stops:[[0, NAVY], [100, MID]]}});
    C.text(cx - iw / 2 + 12, cardY + 20, iw - 24, ch - 26, t.d, {font:'body', size:F.ds, color:TXT, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 8. vertical quarters: fading edge rail with diamond nodes */
function tlQuarters(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rh = Z.h / n, s = Math.min(30, rh * 0.5), rx = Z.x + 16, yw = Math.min(150, Z.w * 0.2), wide = Z.w > 760;
  var ys = C.groupFit(it.map(function(t){ return t.h || '00'; }), yw, rh * 0.7, {font:'head', max:40, min:14, maxLines:1});
  var tx = rx + s + 30, dx = wide ? tx + yw + 20 : tx, dw = Z.x + Z.w - dx;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw, wide ? rh - 12 : rh - ys * 1.1 - 12, {font:'body', max:20, min:11, lh:1.36});
  C.rect(rx - 2, Z.y, 4, Z.h, {grad:{ang:180, stops:[[0, NAVY, 0], [10, NAVY], [90, MID], [100, MID, 0]]}});
  it.forEach(function(t, k){
    var y = Z.y + k * rh, cy = y + rh / 2;
    dia(C, rx, cy, s + 12, {fill:'#F7F9FC', line:NAVY, lw:1.2});
    dia(C, rx, cy, s, {grad:GRAD});
    if (k) C.rect(tx, y, Z.x + Z.w - tx, 1, {fill:'#D8E0EC'});
    var dh = C.measureText(t.d, dw, {font:'body', max:ds, min:ds, lh:1.36}).h;
    if (wide) {
      C.text(tx, y, yw, rh, t.h || u.pad2(k + 1), {font:'head', size:ys, color:NAVY, va:'m', lh:1, maxLines:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      C.text(dx, cy - dh / 2, dw, dh + 4, t.d, {font:'body', size:ds, color:TXT, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      var by = cy - (ys * 1.1 + 4 + dh) / 2;
      C.text(tx, by, dw, ys * 1.1, t.h || u.pad2(k + 1), {font:'head', size:ys, color:NAVY, lh:1, maxLines:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      C.text(tx, by + ys * 1.1 + 4, dw, dh + 4, t.d, {font:'body', size:ds, color:TXT, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    }
  });
}
/* 9. agenda: diamond-numbered ruled list */
function agendaDiamond(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 60, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(90, Z.h / per);
  var y0 = Z.y + (Z.h - rh * per) / 2, s = Math.min(48, rh * 0.7), tx = s + 30;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - tx - 60, rh * 0.6, {font:'head', max:n <= 5 ? 26 : 21, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    diaNum(C, x + s / 2 + 4, y + rh / 2, s, k + 1, {fs:0.3});
    C.text(x + tx, y, cw - tx - 60, rh, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    K.chev3(C, x + cw - 34, y + rh / 2 - 6, 12, MID, {hollow:true});
    if (r < per - 1 && k < n - 1) C.rect(x + tx, y + rh - 0.5, cw - tx, 1, {fill:'#D8E0EC'});
  });
}
D.register({
  id:'navyproposal', name:'Navy Proposal', tag:'Glossy white · proposals', desc:'Glossy white pages, navy gradient edge blocks, rotated-square photo frames, triple chevrons and dot grids for business proposals.',
  fonts:{head:'Raleway', body:'Open Sans', headW:800, bodyW:400, headLh:1.02, headCaps:true, gf:'Raleway:wght@600;700;800|Open Sans:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v2','Diamond photo',{img:'diamond'})],
    closing:[V('v2','Diamond photo + contacts',{img:'diamond'})],
    agenda:[V('v2','Photo + contents',{}), V('n2','Diamond list',{body:agendaDiamond}), V('v3','Diamond list + image right',{body:agendaDiamond, img:'right', cols:1})],
    text:[V('v1','Statement',{body:'text'}), V('n2','Statement card',{body:'text', box:true}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Proposal split',{body:proposalSplit}), V('n2','Diamond frames',{body:diamondFrames}), V('n3','Chevron rows',{body:chevronRows}), V('v2','Image left + chevron rows',{body:chevronRows, img:'left'}), V('v3','Split + image right',{body:proposalSplit, img:'right'})],
    p3:[V('v1','Diamond frames',{body:diamondFrames}), V('n2','Chevron rows',{body:chevronRows}), V('n3','Diamond cards',{body:diamondCards}), V('n4','Diamond path',{body:'xDiamonds'}), V('v2','Image left + chevron rows',{body:chevronRows, img:'left'}), V('v3','Chevron rows + image right',{body:chevronRows, img:'right'})],
    p4:[V('v1','Diamond frames',{body:diamondFrames}), V('n2','Diamond cards',{body:diamondCards}), V('n3','Chevron rows',{body:chevronRows}), V('n4','Diamond path',{body:'xDiamonds'}), V('v2','Chevron rows + image right',{body:chevronRows, img:'right'})],
    p56:[V('v1','Diamond cards',{body:diamondCards}), V('n2','Chevron rows',{body:chevronRows}), V('n3','Diamond path',{body:'xDiamonds'}), V('v2','Chevron rows + image right',{body:chevronRows, img:'right'})],
    p78:[V('v1','Diamond cards',{body:diamondCards}), V('n2','Chevron rows',{body:chevronRows, cols:2}), V('v2','Chevron rows + image right',{body:chevronRows, img:'right', cols:1})],
    stats:[V('v1','Diamond figures',{body:diamondStats}), V('n2','Navy band',{body:statBand}), V('v2','Band + image right',{body:statBand, img:'right'}), V('v3','Image left + band',{body:statBand, img:'left'})],
    timeline:[V('v1','Diamond rail',{body:tlDiamondRail}), V('n2','Quarter rail',{body:tlQuarters}), V('n3','Diamond path',{body:'xDiamonds'}), V('v2','Quarter rail + image left',{body:tlQuarters, img:'left'}), V('v3','Quarter rail + image right',{body:tlQuarters, img:'right'})],
    quote:[V('v1','Navy quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
