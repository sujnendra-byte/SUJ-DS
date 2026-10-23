<?php
/**
 * Code Snippets export — DS Template 43 — Mono Ink
 *
 * Snippet ID : 999
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:18:08
 * Load order : 206 of 218
 */
/**
 * DS Template 43 — Mono Ink
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl43_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl43_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('monoink', array(
        'name'     => "Mono Ink",
        'fonts'    => array('head' => "Playfair Display", 'body' => "Libre Franklin"),
        'pal'      => array('bg' => '#F4F1EA', 'text' => '#141414', 'accent' => '#D7261E', 'accent2' => '#141414'),
        'voice'    => "Authoritative editorial voice: factual, measured, headline-style statements with evidence.",
        'recipe'   => "Headline, context, key findings, analysis, evidence, implications, recommendations, thank you.",
        'generate' => 'ds_tpl43_generate',
        'build'    => 'ds_tpl43_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl43_generate') ) {
function ds_tpl43_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl43_build') ) {
function ds_tpl43_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-monoink">
/* DesignStager template: monoink */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 43 - Mono Ink : newsprint page, black ink rules, one red accent, editorial serif headlines */
var BK = '#141414', RD = '#D7261E', PA = '#F4F1EA', MUT = '#55524C';
var P0 = {bg:PA, text:BK, title:BK, muted:MUT, accent:RD, accent2:BK, card:'#FFFFFF', line:BK, onAccent:'#FFFFFF'};
var S = {
  margin:76, series:[BK, RD, '#8C8880', '#C9C4B8', '#5A1410', '#3A3A3A'],
  header:{kind:'left', top:86, tMax:48, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'outline', r:0, fill:'#FFFFFF', line:BK, lw:1, shadow:0, dark:false, tc:BK, mc:MUT,
        hl:{kind:'solid', fill:BK, dark:true, tc:'#FFFFFF', mc:'#D8D5CE'}},
  icon:{kind:'none', fg:RD}, num:{kind:'big', fg:RD},
  conc:{kind:'bar', barC:RD, label:'Bottom line', lc:RD, tc:BK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:24,
  imgPh:{grad:{ang:135, stops:[[0, '#CFCAC0'], [100, '#8C8880']]}}
};
function mast(C){
  var M = 76, f = C.cover;
  C.rect(M, 44, W - M * 2, 3, {fill:BK}); C.rect(M, 50, W - M * 2, 1, {fill:BK});
  C.text(M, 58, 500, 16, f.company || '', {font:'body', size:10.5, wt:700, color:BK, lh:1, caps:true, ls:0.18});
  C.text(W - M - 400, 58, 400, 16, 'No. ' + pad2(C.i + 1), {font:'body', size:10.5, wt:700, color:RD, align:'r', lh:1, caps:true, ls:0.18});
}
function foot(C){
  var M = 76, y = H - 34, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 1, {fill:BK});
  C.text(M, y, 500, 16, f.website || '', {font:'body', size:10.5, wt:500, color:MUT, lh:1, va:'m', ls:0.06});
  C.text(W - M - 200, y, 200, 16, 'Page ' + (C.i + 1) + ' of ' + C.total, {font:'body', size:10.5, wt:500, color:MUT, align:'r', va:'m', lh:1});
}
function bg(C, kind){ C.bg({fill:PA}); if (kind === 'cover' || kind === 'closing') return; mast(C); }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* newspaper columns with hairline rules between and a red numeral */
function columns(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : 3); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), gx = 44, gy = 26, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, top = 44;
  var F = K.fitHD(C, it, cw, 56, ch - top - 4, {hMax:24, dMax:15.5, hMin:13, dMin:10.5});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy);
    C.rect(x, y, cw, 3, {fill:BK});
    C.text(x, y + 12, 60, 24, pad2(k + 1), {font:'body', size:12, wt:700, color:RD, lh:1, ls:0.14});
    K.hd(C, x, y + top, cw, ch - top, t, F, {tc:BK, mc:MUT});
    if (c < cols - 1 && k < n - 1) C.rect(x + cw + gx / 2, y, 1, ch, {fill:BK, op:0.35});
  });
}
/* ledger: heading on the left, copy on the right, a rule above each row */
function ledger(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 130), hasH = it.some(function(t){ return t.h; }), nw = 50, hw = hasH ? Math.min(310, (Z.w - nw) * 0.36) : 0, tw = Z.w - nw - hw - (hasH ? 30 : 0);
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw, rh - 20, {font:'head', max:23, min:13, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 20, {font:'body', max:hasH ? 16 : 22, min:10.5, lh:1.4});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    C.rect(Z.x, y, Z.w, k ? 1 : 3, {fill:BK});
    C.text(Z.x, y + 4, nw, rh - 4, pad2(k + 1), {font:'body', size:12, wt:700, color:RD, va:'m', lh:1, ls:0.1});
    if (hasH) C.text(Z.x + nw, y + 4, hw, rh - 4, t.h || '', {font:'head', size:hs, wt:700, color:BK, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + (hasH ? 30 : 0), y + 4, tw, rh - 4, t.d, {font:'body', size:ds, color:hasH ? MUT : BK, va:'m', lh:1.4, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
  C.rect(Z.x, y0 + tot, Z.w, 1, {fill:BK});
}
/* front page: the lead story across the top, the briefs in columns below */
function front(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return columns(C, Z, o);
  var lh = Math.round(Z.h * 0.4), rest = it.slice(1), rn = rest.length, gx = 40, cw = (Z.w - gx * (rn - 1)) / rn, by = Z.y + lh + 26, bh = Z.h - lh - 26;
  C.rect(Z.x, Z.y, Z.w, 3, {fill:BK});
  C.rect(Z.x, Z.y + 16, 86, 24, {fill:RD}); C.text(Z.x, Z.y + 16, 86, 24, 'LEAD', {font:'body', size:11, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, ls:0.2});
  var hw = Z.w * 0.42, t0 = it[0];
  if (t0.h) C.text(Z.x, Z.y + 52, hw - 30, lh - 56, t0.h, {font:'head', fit:{max:38, min:18}, wt:800, color:BK, lh:1.06, ed:{f:'x-part', i:t0.i, p:0}, maxLines:3, va:'t'});
  C.text(t0.h ? Z.x + hw : Z.x, Z.y + 52, t0.h ? Z.w - hw : Z.w, lh - 56, t0.d, {font:'body', fit:{max:20, min:12}, color:BK, lh:1.42, ed:t0.h ? {f:'x-part', i:t0.i, p:1} : {f:'content-plain', i:t0.i}});
  var F = K.fitHD(C, rest, cw, 50, bh - 20, {hMax:20, dMax:14.5, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var x = Z.x + k * (cw + gx);
    C.rect(x, by, cw, 1, {fill:BK});
    K.hd(C, x, by + 16, cw, bh - 16, t, F, {tc:BK, mc:MUT});
    if (k < rn - 1) C.rect(x + cw + gx / 2, by, 1, bh, {fill:BK, op:0.35});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: huge serif figures over a double rule */
function figures(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var gx = 44, cw = (Z.w - gx * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), vh = Math.min(150, Z.h * 0.45);
  var vs = C.groupFit(vals, cw, vh, {font:'head', max:104, min:28, maxLines:1});
  var ls = C.groupFit(labs, cw, 54, {font:'head', max:21, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw, Z.h - vh - 90, {font:'body', max:14.5, min:10.5, lh:1.4});
  var y0 = Z.y + Math.max(0, (Z.h - vh - 150) * 0.25);
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gx);
    C.text(x, y0, cw, vh, vals[k], {font:'head', size:vs, wt:800, color:k % 2 ? RD : BK, va:'b', lh:0.95, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x, y0 + vh + 12, cw, 3, {fill:BK}); C.rect(x, y0 + vh + 18, cw, 1, {fill:BK});
    var le = C.text(x, y0 + vh + 32, cw, 52, labs[k], {font:'head', size:ls, wt:700, color:BK, lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x, y0 + vh + 40 + le._h, cw, Z.y + Z.h - (y0 + vh + 40 + le._h), t.x, {font:'body', size:ds, color:MUT, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: dateline - boxed dates on a rule, copy beneath */
function dateline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return ledger(C, Z, o);
  var gx = 26, cw = (Z.w - gx * (n - 1)) / n, bh = 46, y = Z.y + 10;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - 20, bh - 10, {font:'head', max:22, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw, Z.h - bh - 60, {font:'body', max:15.5, min:10.5, lh:1.42});
  C.rect(Z.x, y + bh / 2 - 0.5, Z.w, 1, {fill:BK});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), last = k === n - 1, bw = Math.min(cw, String(t.h || pad2(k + 1)).length * hs * 0.6 + 34);
    C.rect(x, y, bw, bh, {fill:last ? RD : BK});
    C.text(x, y, bw, bh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x, y + bh + 24, cw, Z.h - bh - 40, t.d, {font:'body', size:ds, color:BK, lh:1.42, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, 3, {fill:BK}); var y = Z.y + 30;
  if (it.h) { var he = C.text(Z.x, y, Z.w, 90, it.h, {font:'head', fit:{max:40, min:20}, wt:800, color:BK, italic:true, lh:1.08, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.rect(Z.x, y + 6, 4, Math.min(Z.y + Z.h - y - 20, 200), {fill:RD});
  C.text(Z.x + 28, y, Z.w - 28, Z.y + Z.h - y - 10, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:BK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', M = 76;
  C.rect(M, 56, W - M * 2, 5, {fill:BK}); C.rect(M, 66, W - M * 2, 1, {fill:BK});
  C.text(M, 82, W - M * 2, 46, f.company || 'The Presentation', {font:'head', size:34, wt:800, color:BK, align:'c', lh:1, ed:{f:'tf-company'}, maxLines:1});
  C.rect(M, 140, W - M * 2, 1, {fill:BK}); 
  C.text(M, 148, 500, 18, f.website || '', {font:'body', size:11, wt:600, color:MUT, lh:1, va:'m', caps:true, ls:0.14, ed:f.website ? {f:'tf-website'} : null});
  C.text(W - M - 300, 148, 300, 18, 'Special edition', {font:'body', size:11, wt:700, color:RD, align:'r', lh:1, va:'m', caps:true, ls:0.14});
  C.rect(M, 174, W - M * 2, 3, {fill:BK});
  var tw = photo ? 620 : W - M * 2;
  if (photo) D.imgBox(C, M + 660, 204, W - M * 2 - 660, 380, {r:0});
  C.text(M, 204, tw, 250, C.s.title, {font:'head', fit:{max:photo ? 72 : 100, min:34}, wt:800, color:BK, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98, ls:-0.02});
  if (C.s.subtitle) C.text(M, 476, Math.min(tw, 700), 76, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:MUT, italic:true, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.rect(M, 606, W - M * 2, 1, {fill:BK});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(M, 622, 60, 20, 'By', {font:'body', size:13, wt:700, color:RD, lh:1, caps:true, ls:0.14}); C.text(M + 40, 620, 700, 22, who, {font:'body', size:15, wt:600, color:BK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', M = 76;
  C.rect(0, 0, W, H, {fill:BK});
  C.rect(M, 56, W - M * 2, 5, {fill:'#FFFFFF'}); C.rect(M, 66, W - M * 2, 1, {fill:'#FFFFFF'});
  if (photo) D.imgBox(C, W - M - 400, 110, 400, 480, {r:0});
  var tw = photo ? 640 : W - M * 2;
  C.text(M, 110, tw, 220, C.s.title || 'Thank You', {font:'head', fit:{max:120, min:40}, wt:800, color:'#FFFFFF', italic:true, ed:{f:'title'}, maxLines:2, va:'b', lh:0.98, ls:-0.02});
  if (C.s.subtitle) C.text(M, 346, Math.min(tw, 680), 66, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#D8D5CE', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var cw = tw / n;
  C.rect(M, 450, tw, 1, {fill:'#FFFFFF'});
  cs.forEach(function(c, k){
    var x = M + k * cw;
    C.icon(x, 478, 22, c.icon, RD);
    C.text(x, 512, cw - 24, 30, c.val, {font:'body', fit:{max:18, min:11}, wt:600, color:'#FFFFFF', va:'t', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(x - 14, 466, 1, 80, {fill:'#FFFFFF', op:0.4});
  });
}
function agendaA(C, o){
  var M = 76;
  var te = C.text(M, 100, 400, 240, C.s.title || 'Agenda', {font:'head', fit:{max:84, min:36}, wt:800, color:BK, italic:true, ed:{f:'title'}, maxLines:3, va:'t', lh:0.98});
  C.rect(M, 100 + te._h + 16, 70, 5, {fill:RD});
  if (C.s.subtitle) C.text(M, 100 + te._h + 40, 380, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  ledger(C, {x:540, y:100, w:W - 540 - M, h:H - 190}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], M = 76;
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RD, tc:BK}); return; }
  var photo = o.img === 'photo', w = photo ? 640 : W - M * 2;
  if (photo) D.imgBox(C, W - M - 420, 100, 420, 500, {r:0});
  C.rect(M, 100, w, 3, {fill:BK});
  C.text(M, 110, 120, 110, '“', {font:'head', size:170, wt:800, color:RD, lh:1, caps:false, ls:0});
  var qe = C.text(M, 226, w, 290, C.s.title, {font:'head', fit:{max:photo ? 40 : 52, min:20}, wt:700, color:BK, italic:true, lh:1.16, ed:{f:'title'}, maxLines:6, va:'t'});
  C.rect(M, 226 + qe._h + 24, w, 1, {fill:BK});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(M, 226 + qe._h + 40, w, 24, bt, {font:'body', size:14, wt:700, color:BK, lh:1.1, caps:true, ls:0.14, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'monoink', name:'Mono Ink', tag:'Newsprint · editorial',
  desc:'A newsprint page with black ink rules, one red accent and big editorial serif headlines: authoritative and timeless for reports, research, journalism and thought leadership.',
  fonts:{head:'Playfair Display', body:'Libre Franklin', headW:800, bodyW:400, headLh:1.06, gf:'Playfair+Display:ital,wght@0,700;0,800;1,700;1,800|Libre+Franklin:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Front page', {}), V('v2', 'Front page + photo', {img:'photo'})],
    closing:[V('v1', 'Black back page', {}), V('v2', 'Back page + photo', {img:'photo'})],
    agenda:[V('v1', 'Headline + ledger', {fn:agendaA}), V('n2', 'Columns', {body:columns}), V('v2', 'Ledger + image right', {body:ledger, img:'right'})],
    text:[V('v1', 'Editorial statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Columns', {body:columns}), V('n2', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + columns', {body:columns, img:'left', cols:1})],
    p3:[V('v1', 'Columns', {body:columns}), V('n2', 'Front page', {body:front}), V('n3', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + ledger', {body:ledger, img:'left'})],
    p4:[V('v1', 'Front page', {body:front}), V('n2', 'Columns', {body:columns}), V('n3', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + columns', {body:columns, img:'left', cols:2})],
    p56:[V('v1', 'Columns', {body:columns}), V('n2', 'Ledger', {body:ledger}), V('n3', 'Front page', {body:front}), V('v2', 'Ledger + image right', {body:ledger, img:'right'}), V('v3', 'Image left + ledger', {body:ledger, img:'left'})],
    p78:[V('v1', 'Ledger', {body:ledger}), V('v2', 'Ledger + image right', {body:ledger, img:'right'})],
    stats:[V('v1', 'Big figures', {body:figures}), V('n2', 'Progress rings', {body:'statRings', track:'#D8D3C7'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Figures + image right', {body:figures, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Dateline', {body:dateline}), V('n2', 'Ledger', {body:ledger}), V('n3', 'Columns', {body:columns}), V('v2', 'Ledger + image right', {body:ledger, img:'right'})],
    quote:[V('v1', 'Pull quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

</script>
<?php
}, 1053);
