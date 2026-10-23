<?php
/**
 * Code Snippets export — DS Template 101 — String Art
 *
 * Snippet ID : 1143
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:28:54
 * Load order : 311 of 345
 */
/**
 * DS Template 101 — String Art
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl101_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl101_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('stringart', array(
        'name'     => "String Art",
        'fonts'    => array('head' => "Marcellus", 'body' => "Karla"),
        'pal'      => array('bg' => '#F8F7F4', 'text' => '#0B1F3A', 'accent' => '#0B1F3A', 'accent2' => '#C99A2E'),
        'voice'    => "Refined, measured and discreet: careful stewardship and precise thinking. Elegant sentences with exact figures.",
        'recipe'   => "The situation, our view, the considered moves (two to four points each), the figures, the path and the recommendation.",
        'generate' => 'ds_tpl101_generate',
        'build'    => 'ds_tpl101_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl101_generate') ) {
    function ds_tpl101_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl101_build') ) {
    function ds_tpl101_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-stringart">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 101 - String Art : straight gold threads pinned between two edges form a smooth curve */
var NV = '#0B1F3A', GD = '#C99A2E', IV = '#F8F7F4', MU = '#56627A', LN = '#E0DCD0';
var P = {bg:IV, text:NV, title:NV, muted:MU, accent:NV, accent2:GD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [NV, GD, '#3F5E8C', '#8A6A1F'];
function strings(C, a, b, c, k, col, lw, op){ var d = []; for (var i = 0; i <= k; i++) { var q = i / k; Q.line(d, a[0] + (b[0] - a[0]) * q, a[1] + (b[1] - a[1]) * q, b[0] + (c[0] - b[0]) * q, b[1] + (c[1] - b[1]) * q); } K5.stroke(C, d, col, lw, op); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:NV});
strings(C, [520, H], [W, H], [W, 30], 36, GD, 1.6); strings(C, [0, 250], [0, 0], [430, 0], 18, '#FFFFFF', 1.2, 0.35);
if (photo) { C.ell(826, 116, 328, 328, {line:GD, lw:3}); D.imgBox(C, 840, 130, 300, 300, {shape:'ell'}); }
K5.coverText(C, 96, 200, 640, {tc:'#FFFFFF', sc:'#C5CEDF', kc:GD, max:60, wt:400, by:644, rule:function(C, x, y){ C.rect(x, y, 80, 1.5, {fill:GD}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:NV});
strings(C, [700, H], [0, H], [0, 60], 34, GD, 1.6);
if (photo) { C.ell(906, 56, 268, 268, {line:GD, lw:3}); D.imgBox(C, 920, 70, 240, 240, {shape:'ell'}); }
K5.closeText(C, 560, 150, 620, {tc:'#FFFFFF', sc:'#C5CEDF', wt:400, mc:GD, mi:NV});
}
K5.make({
id:'stringart', name:'String Art', tag:'Navy · gold thread curves',
desc:'Straight gold threads pinned between two edges build smooth curves, like string art, on a deep navy cover and soft ivory pages with fine classical headings. Precise and premium, for wealth, advisory, private clients and annual letters.',
fonts:{head:'Marcellus', body:'Karla', headW:400, bodyW:400, headLh:1.12, gf:'Marcellus|Karla:wght@400;500;600;700'},
P:P, LC:LC, cardR:4, imgR:4, coverLbl:'Thread curve', closeLbl:'Thread curve',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; strings(C, [1040, 0], [W, 0], [W, 220], 16, NV, 1, 0.5); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y + 2, 90, 1.5, {fill:GD}); C.ell(x - 3, y - 1, 8, 8, {fill:GD}); C.ell(x + 86, y - 1, 8, 8, {fill:GD}); return 18; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : '#FFFFFF', r:4, line:hot ? null : LN, lw:1.2});
if (!o.big) strings(C, [x + w - 70, y + h - 6], [x + w - 6, y + h - 6], [x + w - 6, y + h - 70], 8, GD, 1.2);
return hot ? {tc:'#FFFFFF', mc:'#C5CEDF', ac:GD, hot:1} : {tc:NV, mc:MU, ac:NV};
},
row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:GD}); return {tc:NV, mc:MU, ac:NV}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {line:GD, lw:1.5}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? '#FFFFFF' : NV); },
rowMark:function(C, x, y, s, t, k){ C.text(x - 6, y, s + 12, s, pad2(k + 1), {font:'head', size:s * 0.66, wt:400, color:GD, align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:IV, line:GD, lw:2}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.38, wt:400, color:NV, align:'c', va:'m', lh:1}); },
track:function(C, x1, y, x2){ C.rect(x1, y - 0.75, x2 - x1, 1.5, {fill:GD}); },
conc:{kind:'line', lineC:GD, label:'In essence', lc:GD, tc:NV},
L:{cards:'Thread-corner cards', rows:'Fine rows', steps:'Pin steps', stmt:'Fine note', stats:'Plain numbers'},
sig:function(pg, B){
function fans(C, Z, o){
K5.cols(C, Z, {max:5, ah:134, gap:28, tg:18, fallback:B.rows}, function(C, k, t, b){
C.rect(b.x, b.y, 116, 116, {fill:NV, r:4}); strings(C, [b.x + 10, b.y + 34], [b.x + 10, b.y + 106], [b.x + 82, b.y + 106], 10, GD, 1.2);
K.icon(C, b.x + 72, b.y + 14, 30, t, '#FFFFFF');
C.text(b.x + 132, b.y + 60, 90, 56, pad2(k + 1), {font:'head', size:44, wt:400, color:GD, lh:1, va:'b'});
});
}
function strung(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.48), tg:20, vMax:54, align:'l'}, function(C, k, t, b){
strings(C, [b.x + 17, b.y + 6], [b.x + 17, b.y + b.ah - 4], [b.x + b.w - 20, b.y + b.ah - 4], 16, k % 2 ? NV : GD, 1.2);
return {vc:NV, vx:b.x + b.w * 0.3, vw:b.w * 0.7 - 20, vy:b.y + 8, va:'r'};
});
}
function thread(C, Z, o){
K5.alt(C, Z, {off:74, fallback:B.rows, pre:function(C, g){ var pts = []; for (var k = 0; k < g.n; k++) pts.push([g.x + g.cw * (k + 0.5), g.y + (k % 2 === 0 ? -30 : 30)]); K5.stroke(C, Q.pts([], pts), GD, 1.6); K5.stroke(C, Q.pts([], pts.map(function(p){ return [p[0], g.y * 2 - p[1]]; })), NV, 1, 0.35); }}, function(C, k, t, b){
var y = b.cy + (b.up ? -30 : 30); C.ell(b.cx - 24, y - 24, 48, 48, {fill:IV, line:GD, lw:2}); C.text(b.cx - 24, y - 24, 48, 48, pad2(k + 1), {font:'head', size:18, wt:400, color:NV, align:'c', va:'m', lh:1});
});
}
function fine(C, Z, o){
K5.list(C, Z, {aw:128, pr:16, gap:8, rhMax:100}, function(C, k, t, b){
var s = Math.min(54, b.h - 22); strings(C, [b.x, b.cy - s / 2], [b.x, b.cy + s / 2], [b.x + s, b.cy + s / 2], 7, GD, 1.2);
C.text(b.x + s + 4, b.y, 60, b.h, pad2(k + 1), {font:'head', size:Math.min(34, b.h * 0.4), wt:400, color:NV, va:'m', lh:1});
C.rect(b.x, b.y + b.h + 3, b.w, 1, {fill:LN});
});
}
return {agenda:[['Thread list', pg(fine)]], p2:[['Thread fans', pg(fans)]], p3:[['Thread fans', pg(fans)]], p4:[['Thread fans', pg(fans)]], p56:[['Thread list', pg(fine)]], p78:[['Thread list', pg(fine)]],
stats:[['Strung numbers', pg(strung)]], timeline:[['Pins and thread', pg(thread)]]};
}
});
})();
</script>
    <?php
}, 1151);
