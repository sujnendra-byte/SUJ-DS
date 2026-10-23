<?php
/**
 * Code Snippets export — DS Template 91 — Stair Step
 *
 * Snippet ID : 1133
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:28:42
 * Load order : 302 of 345
 */
/**
 * DS Template 91 — Stair Step
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl91_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl91_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('stairstep', array(
        'name'     => "Stair Step",
        'fonts'    => array('head' => "Outfit", 'body' => "Outfit"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#12343D', 'accent' => '#0F4C5C', 'accent2' => '#FB8B24'),
        'voice'    => "Ambitious and step-by-step: every point is one step higher than the last. Concrete targets and measurable gains.",
        'recipe'   => "Where we stand today, the steps up (two to four points each), the numbers at each level, the climb plan and the next step.",
        'generate' => 'ds_tpl91_generate',
        'build'    => 'ds_tpl91_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl91_generate') ) {
    function ds_tpl91_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl91_build') ) {
    function ds_tpl91_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-stairstep">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
/* Template 91 - Stair Step : a stepped staircase edge climbs across the page; content rides the steps upward */
var PE = '#0F4C5C', OR = '#FB8B24', INK = '#12343D', MU = '#5E7278', LN = '#D5E2E5', PALE = '#CFE3E7';
var P = {bg:'#FFFFFF', text:INK, title:PE, muted:MU, accent:PE, accent2:OR, card:'#F1F6F7', line:LN, onAccent:'#FFFFFF'};
var LC = [PE, '#2F7585', OR, '#5FA0AE', '#9A031E'];
function shade(k, n){ return mix(PALE, PE, n > 1 ? k / (n - 1) : 1); }
function stairs(C, x0, yb, sw, sh, n, color, bottom){
var pts = [[x0, bottom]];
for (var i = 0; i < n; i++) { pts.push([x0 + i * sw, yb - i * sh]); pts.push([x0 + (i + 1) * sw, yb - i * sh]); }
pts.push([x0 + n * sw, bottom]); K5.fill(C, Q.poly([], pts), color);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:'#F4F7F7'});
if (photo) D.imgBox(C, 648, 0, 632, H, {r:0});
stairs(C, 440, 608, 208, 112, 5, PE, H); stairs(C, 648, 608, 208, 112, 4, OR, H);
K5.coverText(C, 80, 70, photo ? 520 : 640, {max:photo ? 50 : 58, sw:520, by:652});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:'#F4F7F7'});
if (photo) D.imgBox(C, 760, 0, 520, H, {r:0});
stairs(C, 700, 640, 150, 86, 5, PE, H); stairs(C, 850, 640, 150, 86, 4, OR, H);
K5.closeText(C, 80, 110, 600, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:k % 2 ? OR : PE}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'stairstep', name:'Stair Step', tag:'Mist white · petrol staircase',
desc:'A stepped staircase edge in petrol and orange climbs across the page, and content rides the steps upward: rising step columns, tread numbers and a stepped photo edge. For growth plans, maturity models, targets and career paths.',
fonts:{head:'Outfit', body:'Outfit', headW:700, bodyW:400, headLh:1.08, gf:'Outfit:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Staircase', closeLbl:'Staircase',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; for (var i = 0; i < 4; i++) C.rect(W - (4 - i) * 34, i * 22, (4 - i) * 34, 22, {fill:i === 3 ? OR : PE}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 28, 6, {fill:PE}); C.rect(x + 28, y - 6, 28, 6, {fill:OR}); return 16; },
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:'#F1F6F7'}); C.rect(x, y, w, 8, {fill:OR}); return {tc:INK, mc:MU, ac:PE}; }
if (o.hot) { C.rect(x, y, w, h, {fill:PE}); C.rect(x, y, w, 8, {fill:OR}); return {tc:'#FFFFFF', mc:'#C7DDE2', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:'#F1F6F7'}); C.rect(x, y, w, 8, {fill:k % 2 ? OR : PE}); return {tc:INK, mc:MU, ac:PE};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? OR : PE}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.rect(x, y + s * 0.5, s * 0.5, s * 0.5, {fill:OR}); C.rect(x + s * 0.5, y, s * 0.5, s, {fill:PE}); C.text(x - 4, y - 2, s * 0.56, s * 0.5, String(k + 1), {font:'head', size:s * 0.34, wt:800, color:PE, align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? OR : PE}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.36, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:PALE}); },
photoDeco:function(C, b, side){ if (side === 'bottom') return; var x = side === 'right' ? b.x : b.x + b.w - 90; for (var i = 0; i < 3; i++) C.rect(side === 'right' ? x : x + i * 30, b.y + b.h - (3 - i) * 26, side === 'right' ? (3 - i) * 30 : (3 - i) * 30, 26, {fill:i === 2 ? OR : '#FFFFFF'}); },
L:{cards:'Tread cards', rows:'Step rows', steps:'Square steps', stmt:'Landing note', stats:'Tread numbers'},
sig:function(pg, B){
function rise(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return B.rows(C, Z, o);
var g = 10, cw = (Z.w - g * (n - 1)) / n, minH = Z.h * 0.17, maxH = Z.h * 0.5, tw = cw - 10;
var F = K.fitHD(C, it, tw, 46, Z.h - maxH - 22, {hMax:21, dMax:15.5, hMin:12, dMin:10, noBoost:n > 3});
it.forEach(function(t, k){
var x = Z.x + k * (cw + g), bh = minH + (maxH - minH) * k / (n - 1), y = Z.y + Z.h - bh, c = shade(k, n), on = K.on(c);
C.rect(x, y, cw, bh, {fill:c}); C.rect(x, y, cw, 7, {fill:OR});
C.text(x + 16, y + 20, 80, 34, pad2(k + 1), {font:'head', size:28, wt:800, color:on, va:'m', lh:1});
if (bh > 70) K.icon(C, x + cw - 46, y + 22, 30, t, on);
var th = K3.bH(C, t, tw, F); K.hd(C, x, y - 16 - th, tw, th + 6, t, F, {tc:INK, mc:MU});
});
}
function stepStats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.52), tg:18, vMax:46, vw:function(cw){ return cw - 60; }}, function(C, k, t, b){
var bh = b.ah * (0.42 + 0.58 * (b.n > 1 ? k / (b.n - 1) : 1)), c = shade(k, b.n), y = b.y + b.ah - bh;
C.rect(b.x + 6, y, b.w - 12, bh, {fill:c}); C.rect(b.x + 6, y, b.w - 12, 7, {fill:OR});
return {vc:K.on(c), vy:y + 7 + (bh - 7 - b.vs * 1.15) / 2};
});
}
function stepList(C, Z, o){
K5.list(C, Z, {aw:170, pr:20, gap:6, rhMax:96, split:5}, function(C, k, t, b){
var w = 56 + b.r * 22, c = shade(b.r, b.per);
C.rect(b.x, b.y, w, b.h + (b.last ? 0 : 6), {fill:c}); C.rect(b.x, b.y, w, 6, {fill:OR});
C.text(b.x, b.y + 6, w - 12, b.h - 6, pad2(k + 1), {font:'head', size:Math.min(24, b.h * 0.34), wt:800, color:K.on(c), align:'r', va:'m', lh:1});
});
}
return {agenda:[['Step list', pg(stepList)]], p2:[['Rising steps', pg(rise)]], p3:[['Rising steps', pg(rise)]], p4:[['Rising steps', pg(rise)]],
p56:[['Rising steps', pg(rise)], ['Step list', pg(stepList)]], p78:[['Step list', pg(stepList)]], stats:[['Rising numbers', pg(stepStats)]], timeline:[['Rising steps', pg(rise)]]};
}
});
})();
</script>
    <?php
}, 1141);
