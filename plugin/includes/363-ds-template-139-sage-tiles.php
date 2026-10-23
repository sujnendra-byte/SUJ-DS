<?php
/**
 * Code Snippets export — DS Template 139 — Sage Tiles
 *
 * Snippet ID : 1225
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:54:02
 * Load order : 363 of 369
 */
/**
 * DS Template 139 — Sage Tiles
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl139_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl139_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('sagetiles', array(
        'name'     => "Sage Tiles",
        'fonts'    => array('head' => "Figtree", 'body' => "Figtree"),
        'pal'      => array('bg' => '#F6F7F1', 'text' => '#2C3627', 'accent' => '#5E7752', 'accent2' => '#A9BC97'),
        'voice'    => "Calm, warm and reassuring: steady progress, people and values in simple sentences.",
        'recipe'   => "Who we are, what we value, our team, what we offer, recent progress in numbers, upcoming projects and how to reach us.",
        'generate' => 'ds_tpl139_generate',
        'build'    => 'ds_tpl139_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl139_generate') ) {
    function ds_tpl139_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl139_build') ) {
    function ds_tpl139_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-sagetiles">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 139 - Sage Tiles : calm sage greens with soft raised rounded tiles that float with gentle shadows */
var SG = '#5E7752', SG2 = '#A9BC97', SG3 = '#7F9771', SGL = '#D8E1CC', INK = '#2C3627', MU = '#5F6A58', BG = '#F6F7F1', LN = '#DDE2D3';
var P = {bg:BG, text:INK, title:INK, muted:MU, accent:SG, accent2:SG2, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [SG, SG3, '#46603D', SG2];
var SH = {o:0.14, b:16, d:5};
function tile(C, x, y, s, col, op, sh){ return C.rect(x, y, s, s, {fill:col, op:op, r:s * 0.24, shadow:sh === false ? null : SH}); }
function field(C, x0, x1, y0, y1, cell, seed, skip){
var rn = K5.rnd(seed), tones = [SGL, '#C7D3B8', SG2, SG3, SG];
for (var gx = x0; gx < x1; gx += cell) for (var gy = y0; gy < y1; gy += cell) {
var f = (gx - x0) / (x1 - x0), r = rn(); if (r > 0.25 + f * 0.6) continue;
var s = cell * (0.4 + rn() * 0.55), x = gx + (cell - s) * rn(), y = gy + (cell - s) * rn();
if (skip && x + s > skip[0] && x < skip[0] + skip[2] && y + s > skip[1] && y < skip[1] + skip[3]) continue;
tile(C, x, y, s, tones[Math.min(4, Math.floor(f * 3.2 + rn() * 1.8))], 1);
}
}
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:700, color:col, align:'c', va:'m', lh:1, caps:false}); }
function stage(C, photo){
C.rect(0, 0, W, H, {grad:{ang:0, stops:[[0, '#FBFBF7'], [100, '#DCE4D2']]}});
var hole = photo ? [740, 130, 460, 460] : null;
field(C, 560, W, -20, H, 86, 4242, hole);
if (photo) { tile(C, 760, 150, 420, '#FFFFFF'); D.imgBox(C, 772, 162, 396, 396, {r:84}); }
}
function cover(C){ stage(C, (C.o || {}).img === 'photo'); K5.coverText(C, 88, 180, 520, {max:64, kc:SG, by:640, rule:function(C, x, y){ tile(C, x, y - 2, 14, SG, 1, false); tile(C, x + 20, y - 2, 14, SG2, 1, false); C.rect(x + 42, y + 5, 60, 2, {fill:SG2}); }, ruleH:26}); }
function closing(C){ stage(C, (C.o || {}).img === 'photo'); K5.closeText(C, 88, 190, 520, {mark:function(C, x, y, k, c){ tile(C, x, y, 36, LC[k % 3]); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }}); }
K5.make({
id:'sagetiles', name:'Sage Tiles', tag:'Sage green · soft raised tiles',
desc:'Calm sage greens where soft rounded tiles float with gentle shadows, thickening across the cover; icons and numbers sit on raised tiles and pillars stack like blocks. For company profiles, wellness, real estate, architecture and calm corporate reviews.',
fonts:{head:'Figtree', body:'Figtree', headW:800, bodyW:400, headLh:1.04, gf:'Figtree:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:20, coverLbl:'Tiles', closeLbl:'Tiles',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; tile(C, W - 70, 22, 46, SG2); tile(C, W - 118, 48, 30, SGL); tile(C, W - 40, 80, 24, SG3); tile(C, W - 160, 20, 18, SG2, 0.8); tile(C, 24, H - 58, 22, SGL, 1); },
cover:cover, closing:closing,
rule:function(C, x, y){ tile(C, x, y - 1, 10, SG, 1, false); tile(C, x + 14, y - 1, 10, SG2, 1, false); C.rect(x + 30, y + 3, 40, 2, {fill:SG2}); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {fill:SG, r:20, shadow:SH}); return {tc:'#FFFFFF', mc:'#E3EADB', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', r:20, shadow:SH}); return {tc:INK, mc:MU, ac:SG};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; tile(C, x, y, s, hot ? '#FFFFFF' : LC[k % 3]); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? SG : '#FFFFFF'); },
node:function(C, cx, cy, s, k){ tile(C, cx - s / 2, cy - s / 2, s, LC[k % 3]); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ for (var x = x1; x < x2; x += 26) C.rect(x, y - 4, 8, 8, {fill:SG2, r:2}); },
conc:{kind:'pill', fill:SG, op:0.12, label:'Takeaway', lc:SG, tc:INK},
L:{cards:'Raised cards', rows:'Tile rows', steps:'Tile steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function stacks(C, Z, o){
K5.cols(C, Z, {tg:22, align:'c', ah:function(n, cw, Z){ return Math.min(Z.h * 0.46, cw * 0.66, 190); }, fallback:B.rows}, function(C, k, t, b){
var s = Math.min(b.ah * 0.72, b.w * 0.5), x = b.cx - s / 2, y = b.y + (b.ah - s) / 2;
tile(C, x + s * 0.3, y - s * 0.12, s * 0.7, SGL); tile(C, x - s * 0.22, y + s * 0.42, s * 0.5, SG2); tile(C, x, y, s, LC[k % 3]); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, '#FFFFFF');
});
}
function tilestats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(200, Z.h * 0.52), tg:18, vMax:46, vw:function(cw, ah){ return Math.min(ah, cw - 40) * 0.74; }}, function(C, k, t, b){
var s = Math.min(b.ah, b.w - 40), x = b.cx - s / 2, y = b.y + (b.ah - s) / 2, c = [SG, SG3, '#46603D', SG2][k % 4];
tile(C, x + s * 0.72, y - 10, s * 0.3, SGL); tile(C, x, y, s, c);
return {vc:'#FFFFFF', vy:y + (s - b.vs * 1.15) / 2};
});
}
return {p3:[['Stacked tiles', pg(stacks)]], p4:[['Stacked tiles', pg(stacks)]], stats:[['Tile numbers', pg(tilestats)]]};
}
});
})();
</script>
    <?php
}, 1189);
