<?php
/**
 * Code Snippets export — DS Template 123 — Crest
 *
 * Snippet ID : 1165
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:25
 * Load order : 333 of 345
 */
/**
 * DS Template 123 — Crest
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl123_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl123_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('crest', array(
        'name'     => "Crest",
        'fonts'    => array('head' => "Cormorant Garamond", 'body' => "Lato"),
        'pal'      => array('bg' => '#F7F4EC', 'text' => '#0C2340', 'accent' => '#0C2340', 'accent2' => '#B8922A'),
        'voice'    => "Dignified, principled and institutional: purpose, standards and honours. Formal but warm sentences with dates, memberships and results.",
        'recipe'   => "Our purpose, our commitments (two to four points each), the record in numbers, the calendar of the year and the resolution we invite.",
        'generate' => 'ds_tpl123_generate',
        'build'    => 'ds_tpl123_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl123_generate') ) {
    function ds_tpl123_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl123_build') ) {
    function ds_tpl123_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-crest">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 123 - Crest : a heraldic shield in gold line with a ribbon banner beneath it */
var NV = '#0C2340', GD = '#B8922A', CL = '#7A1F2B', IV = '#F7F4EC', MU = '#566176', LN = '#DCD5C3';
var P = {bg:IV, text:NV, title:NV, muted:MU, accent:NV, accent2:GD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [NV, GD, CL, '#2F4F7F'];
function sp(x, y, w, h){ return [['M', x, y], ['L', x + w, y], ['L', x + w, y + h * 0.55], ['Q', x + w, y + h * 0.9, x + w / 2, y + h], ['Q', x, y + h * 0.9, x, y + h * 0.55], ['Z']]; }
function shield(C, x, y, w, h, fill, line, lw, op){ if (fill) C.path(sp(x, y, w, h), {fill:fill, op:op}); if (line) C.path(sp(x, y, w, h), {stroke:line, lw:lw || 2}); }
function ribbon(C, x, y, w, h, col){ K5.fill(C, Q.poly([], [[x - h * 0.9, y + h * 0.3], [x + 4, y + h * 0.3], [x + 4, y + h * 1.3], [x - h * 0.9, y + h * 1.3], [x - h * 0.4, y + h * 0.8]]), col, 0.7); K5.fill(C, Q.poly([], [[x + w + h * 0.9, y + h * 0.3], [x + w - 4, y + h * 0.3], [x + w - 4, y + h * 1.3], [x + w + h * 0.9, y + h * 1.3], [x + w + h * 0.4, y + h * 0.8]]), col, 0.7); C.rect(x, y, w, h, {fill:col}); }
function arms(C, x, y, w, h, photo){
shield(C, x, y, w, h, GD, null, 0, 0.14); shield(C, x, y, w, h, null, GD, 4); shield(C, x + 14, y + 14, w - 28, h - 28, null, GD, 1.4);
if (photo) { C.ell(x + w / 2 - w * 0.34 - 5, y + h * 0.42 - w * 0.34 - 5, w * 0.68 + 10, w * 0.68 + 10, {fill:NV, line:GD, lw:2.5}); D.imgBox(C, x + w / 2 - w * 0.34, y + h * 0.42 - w * 0.34, w * 0.68, w * 0.68, {shape:'ell'}); return; }
C.line(x + w / 2, y + 14, x + w / 2, y + h - 18, {color:GD, lw:1.4}); C.line(x + 14, y + h * 0.4, x + w - 14, y + h * 0.4, {color:GD, lw:1.4});
K5.stroke(C, Q.pts([], [[x + w * 0.2, y + h * 0.72], [x + w / 2, y + h * 0.54], [x + w * 0.8, y + h * 0.72]]), GD, w * 0.04);
C.ell(x + w * 0.27 - w * 0.07, y + h * 0.21 - w * 0.07, w * 0.14, w * 0.14, {fill:GD}); K5.fill(C, Q.poly([], [[x + w * 0.66, y + h * 0.14], [x + w * 0.8, y + h * 0.14], [x + w * 0.73, y + h * 0.3]]), GD);
}
function cover(C){
var photo = (C.o || {}).img === 'photo', f = C.cover, tag = f.tagline || f.date || '';
C.bg({fill:NV}); arms(C, 810, 80, 350, 440, photo); ribbon(C, 800, 550, 370, 46, GD);
if (tag) C.text(810, 550, 350, 46, tag, {font:'body', size:14, wt:700, color:NV, align:'c', va:'m', caps:true, ls:0.14, lh:1, maxLines:1});
K5.coverText(C, 88, 140, 600, {tc:'#FFFFFF', sc:'#C9D2E0', kc:GD, max:60, by:644, rule:function(C, x, y){ C.rect(x, y, 100, 2, {fill:GD}); C.rect(x, y + 6, 100, 1, {fill:GD}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:NV}); arms(C, 850, 130, 300, 380, photo); ribbon(C, 840, 540, 320, 40, GD);
K5.closeText(C, 88, 150, 640, {tc:'#FFFFFF', sc:'#C9D2E0', mc:GD, mi:NV});
}
function badge(C, cx, cy, s, k, c){ shield(C, cx - s * 0.42, cy - s * 0.52, s * 0.84, s * 1.04, c || NV); shield(C, cx - s * 0.42 + 4, cy - s * 0.52 + 4, s * 0.84 - 8, s * 1.04 - 8, null, GD, 1.2); if (k != null) C.text(cx - s / 2, cy - s * 0.56, s, s, pad2(k + 1), {font:'head', size:s * 0.36, wt:700, color:c === GD ? NV : GD, align:'c', va:'m', lh:1}); }
K5.make({
id:'crest', name:'Crest', tag:'Navy · gold shield and banner',
desc:'A heraldic shield in gold line with a ribbon banner beneath it on a deep navy cover, and ivory pages where small shields number the points, with serif headings and double gold rules. For institutions, associations, clubs, awards, schools and legal bodies.',
fonts:{head:'Cormorant Garamond', body:'Lato', headW:700, bodyW:400, headLh:1.08, gf:'Cormorant+Garamond:wght@500;600;700|Lato:wght@400;700'},
P:P, LC:LC, cardR:0, tMax:46, coverLbl:'Coat of arms', closeLbl:'Coat of arms',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; badge(C, W - 86, 62, 66); C.rect(0, 0, W, 8, {fill:NV}); C.rect(0, 11, W, 2, {fill:GD}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 90, 2, {fill:GD}); C.rect(x, y + 6, 90, 1, {fill:GD}); return 20; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : '#FFFFFF', line:GD, lw:1.4}); if (!o.big) C.rect(x, y, w, 6, {fill:hot ? GD : NV});
return hot ? {tc:'#FFFFFF', mc:'#C9D2E0', ac:GD, hot:1} : {tc:NV, mc:MU, ac:NV};
},
row:function(C, x, y, w, h, k){ C.rect(x, y + h - 3, w, 1.4, {fill:GD}); C.rect(x, y + h - 0.6, w, 0.6, {fill:GD}); return {tc:NV, mc:MU, ac:NV}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; shield(C, x + s * 0.06, y - s * 0.04, s * 0.88, s * 1.08, hot ? GD : NV); K.icon(C, x + s * 0.28, y + s * 0.2, s * 0.44, t, hot ? NV : GD); },
rowMark:function(C, x, y, s, t, k){ badge(C, x + s / 2, y + s / 2, s, k); },
node:function(C, cx, cy, s, k){ badge(C, cx, cy, s * 1.1, k); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 2, {fill:GD}); C.rect(x1, y + 2, x2 - x1, 1, {fill:GD}); },
conc:{kind:'line', lineC:GD, label:'Resolved', lc:CL, tc:NV},
L:{cards:'Charter cards', rows:'Roll rows', steps:'Shield steps', stmt:'Motto', stats:'Plain numbers'},
sig:function(pg, B){
function shields(C, Z, o){
K5.cols(C, Z, {max:5, ah:176, gap:28, tg:14, align:'c', fallback:B.rows}, function(C, k, t, b){
shield(C, b.cx - 52, b.y, 104, 128, k % 2 ? CL : NV); shield(C, b.cx - 46, b.y + 6, 92, 116, null, GD, 1.4); K.icon(C, b.cx - 21, b.y + 30, 42, t, GD);
ribbon(C, b.cx - 44, b.y + 136, 88, 26, GD); C.text(b.cx - 44, b.y + 136, 88, 26, pad2(k + 1), {font:'body', size:13, wt:700, color:NV, align:'c', va:'m', lh:1});
});
}
function honours(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:18, vMax:46, vw:function(cw, ah){ return Math.min(cw - 110, 170); }}, function(C, k, t, b){
var w = Math.min(b.w - 40, 200); shield(C, b.cx - w / 2, b.y, w, b.ah, k % 2 ? CL : NV); shield(C, b.cx - w / 2 + 8, b.y + 8, w - 16, b.ah - 16, null, GD, 1.4);
return {vc:'#FFFFFF', vx:b.cx - w / 2 + 16, vw:w - 32, vy:b.y + b.ah * 0.4 - b.vs * 0.58};
});
}
function roll(C, Z, o){
K5.alt(C, Z, {off:66, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 3, g.w, 2, {fill:GD}); C.rect(g.x, g.y + 2, g.w, 1, {fill:GD}); }}, function(C, k, t, b){ C.ell(b.cx - 46, b.cy - 46, 92, 92, {fill:IV}); badge(C, b.cx, b.cy, 78, k, k % 2 ? CL : NV); });
}
return {p2:[['Shield columns', pg(shields)]], p3:[['Shield columns', pg(shields)]], p4:[['Shield columns', pg(shields)]], stats:[['Honours', pg(honours)]], timeline:[['Roll of honour', pg(roll)]]};
}
});
})();
</script>
    <?php
}, 1173);
