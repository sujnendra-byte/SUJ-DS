<?php
/**
 * Code Snippets export — DS Template 140 — Plum Ledger
 *
 * Snippet ID : 1226
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:54:03
 * Load order : 364 of 369
 */
/**
 * DS Template 140 — Plum Ledger
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl140_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl140_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('plumledger', array(
        'name'     => "Plum Ledger",
        'fonts'    => array('head' => "Barlow", 'body' => "Barlow"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1C1226', 'accent' => '#4A1D6E', 'accent2' => '#C2185B'),
        'voice'    => "Numerate and exact: revenue, margins, costs and ratios with periods and percentages.",
        'recipe'   => "Executive summary, revenue breakdown, expense analysis, profit and cash flow, key ratios and KPIs, year-over-year comparison and the outlook.",
        'generate' => 'ds_tpl140_generate',
        'build'    => 'ds_tpl140_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl140_generate') ) {
    function ds_tpl140_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl140_build') ) {
    function ds_tpl140_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-plumledger">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 140 - Plum Ledger : plum-to-magenta gradient blocks with chamfered corners, skyline bar charts, slashes and dot grids */
var PL = '#4A1D6E', MG = '#C2185B', INK = '#1C1226', MU = '#615A6E', PALE = '#F5F3F7', LN = '#E6E1EC';
var GR = {ang:0, stops:[[0, PL], [100, MG]]}, GD = {ang:135, stops:[[0, PL], [100, MG]]};
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:PL, accent2:MG, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [PL, MG, '#7A3A9E', '#8E1A4C'];
function cut(C, x, y, w, h, c, bgc){ K.poly(C, [[x + w - c, y - 0.6], [x + w + 0.6, y - 0.6], [x + w + 0.6, y + c]], {fill:bgc || '#FFFFFF'}); }
function dots(C, x, y, cols, rows, col, op){ var d = []; for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) Q.circ(d, x + i * 18, y + j * 18, 3.2); K5.fill(C, d, col, op); }
function slash(C, x, y, h, col){ C.line(x, y + h, x + h * 0.35, y, {color:col, lw:2}); C.line(x + 14, y + h, x + 14 + h * 0.35, y, {color:col, lw:2}); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function skyline(C, x, y, w, h){
var rn = K5.rnd(777), n = 22, bw = w / n;
C.rect(x, y, w, h, {grad:{ang:180, stops:[[0, '#2A0F40'], [100, PL]]}});
for (var i = 0; i < n; i++) { var bh = h * (0.25 + rn() * 0.6 + i / n * 0.15); C.rect(x + i * bw + 3, y + h - bh, bw - 6, bh, {fill:i % 5 === 3 ? MG : '#FFFFFF', op:i % 5 === 3 ? 0.9 : 0.12 + rn() * 0.1}); }
}
function stage(C, photo){
if (photo) D.imgBox(C, 40, 32, W - 80, 300, {r:0}); else skyline(C, 40, 32, W - 80, 300);
C.rect(40, 290, 560, 60, {grad:GR}); cut(C, 40, 290, 560, 60, 22); C.rect(60, 318, 120, 3, {fill:'#FFFFFF', op:0.8}); dots(C, 1090, 52, 6, 2, '#FFFFFF', 0.7);
slash(C, 760, 400, 220, LN); dots(C, 1130, 640, 4, 2, PL, 0.5);
C.rect(900, 400, 300, 210, {grad:GD}); cut(C, 900, 400, 300, 210, 34); C.rect(926, 432, 54, 3, {fill:'#FFFFFF', op:0.7});
}
function cover(C){ stage(C, (C.o || {}).img === 'photo'); C.icon(1010, 470, 80, 'mdi:finance', '#FFFFFF'); K5.coverText(C, 88, 382, 640, {max:50, kc:MG, kGap:34, th:120, by:672}); }
function closing(C){ stage(C, (C.o || {}).img === 'photo'); C.icon(1010, 470, 80, 'mdi:chart-timeline-variant-shimmer', '#FFFFFF'); K5.closeText(C, 88, 390, 600, {max:52, mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {grad:GD}); cut(C, x, y, 36, 36, 10); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }}); }
K5.make({
id:'plumledger', name:'Plum Ledger', tag:'White · plum-magenta gradient · chamfered blocks',
desc:'Crisp white pages with plum-to-magenta gradient blocks whose corners are chamfered like ledger tabs, a skyline bar-chart cover, double slashes and dot grids; numbers sit on gradient tiles. For financial reports, annual results, investor updates and budgets.',
fonts:{head:'Barlow', body:'Barlow', headW:800, bodyW:400, headLh:1.02, gf:'Barlow:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Ledger', closeLbl:'Ledger',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; dots(C, W - 120, 36, 4, 3, PL, 0.55); slash(C, W - 190, 30, 50, MG); K.poly(C, [[W - 46, H], [W, H - 46], [W, H]], {fill:MG}); C.rect(0, 0, 8, 120, {grad:{ang:180, stops:[[0, PL], [100, MG]]}}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 70, 5, {grad:GR}); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {grad:GD}); cut(C, x, y, w, h, 24); return {tc:'#FFFFFF', mc:'#F1DDEA', ac:'#FFFFFF', hot:1, bgc:MG}; }
C.rect(x, y, w, h, {fill:PALE}); cut(C, x, y, w, h, 20); C.rect(x, y, 5, h, {fill:k % 2 ? MG : PL});
return {tc:INK, mc:MU, ac:PL, bgc:PALE};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, hot ? {fill:'#FFFFFF'} : {grad:GD}); cut(C, x, y, s, s, s * 0.26, (cc && cc.bgc) || '#FFFFFF'); K.icon(C, x + s * 0.24, y + s * 0.26, s * 0.5, t, hot ? PL : '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {grad:GD}); cut(C, cx - s / 2, cy - s / 2, s, s, s * 0.28); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {grad:GR}); },
conc:{kind:'box', fill:PALE, r:0, label:'Key insight', lc:MG, tc:INK},
L:{cards:'Ledger cards', rows:'Ledger rows', steps:'Tab steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function ledger(C, Z, o){
K5.list(C, Z, {aw:98, pr:20, gap:8, rhMax:96, gx:26}, function(C, k, t, b){
C.rect(b.x, b.y, b.w, b.h, {fill:b.r % 2 ? '#FFFFFF' : PALE}); C.rect(b.x, b.y + b.h - 1, b.w, 1, {fill:LN});
C.rect(b.x, b.y, 78, b.h, {grad:GD}); cut(C, b.x, b.y, 78, b.h, 16, b.r % 2 ? '#FFFFFF' : PALE); num(C, b.x, b.y, 78, b.h, k, Math.min(30, b.h * 0.4), '#FFFFFF');
});
}
function tiles(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.5), tg:18, vMax:54, vw:function(cw){ return cw - 70; }}, function(C, k, t, b){
var x = b.x + 10, w = b.w - 20; C.rect(x, b.y, w, b.ah, k % 2 ? {fill:PL} : {grad:GD}); cut(C, x, b.y, w, b.ah, 30); dots(C, x + 20, b.y + 20, 3, 1, '#FFFFFF', 0.5);
return {vc:'#FFFFFF', vy:b.y + (b.ah - b.vs * 1.15) / 2 + 6};
});
}
return {p56:[['Ledger rows', pg(ledger)]], p3:[['Ledger rows', pg(ledger)]], agenda:[['Ledger rows', pg(ledger)]], stats:[['Gradient tiles', pg(tiles)]]};
}
});
})();
</script>
    <?php
}, 1190);
