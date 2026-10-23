<?php
/**
 * Code Snippets export — DS Template 133 — Chapter Blaze
 *
 * Snippet ID : 1219
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:53
 * Load order : 357 of 369
 */
/**
 * DS Template 133 — Chapter Blaze
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl133_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl133_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('chapterblaze', array(
        'name'     => "Chapter Blaze",
        'fonts'    => array('head' => "Archivo", 'body' => "Archivo"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#151B3B', 'accent' => '#F0412B', 'accent2' => '#FF9A1F'),
        'voice'    => "Energetic trainer voice: clear learning objectives, practical steps and quick checks for understanding.",
        'recipe'   => "Learning objectives, numbered chapters of skills (two to four points each), practice activities, a recap of key takeaways and next steps.",
        'generate' => 'ds_tpl133_generate',
        'build'    => 'ds_tpl133_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl133_generate') ) {
    function ds_tpl133_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl133_build') ) {
    function ds_tpl133_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-chapterblaze">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 133 - Chapter Blaze : deep navy with a red-to-amber flame gradient, giant ghost chapter numerals and dot grids */
var NV = '#151B3B', OR = '#F0412B', AM = '#FF9A1F', MU = '#5E6382', PALE = '#F4F5F9', LN = '#E2E4EE', GHOST = '#EEF0F5';
var GO = {ang:0, stops:[[0, '#E8322A'], [100, AM]]}, GV = {ang:90, stops:[[0, '#E8322A'], [100, AM]]};
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:OR, accent2:AM, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [OR, NV, AM, '#2D3A78'];
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function grid(C, x, y, cols, rows, col, op){ var d = []; for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) Q.rect(d, x + i * 14, y + j * 14, 6, 6); K5.fill(C, d, col, op); }
function dashes(C, x, y, col){ C.rect(x, y, 46, 5, {fill:col, r:2.5}); C.rect(x + 56, y, 22, 5, {fill:col, r:2.5}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, H - 12, 520, 12, {grad:GO});
if (photo) { C.rect(660, 40, 580, 640, {fill:NV, r:40}); D.imgBox(C, 690, 70, 520, 580, {r:30}); C.rect(620, 560, 220, 64, {grad:GO, r:32}); C.ell(784, 572, 40, 40, {fill:'#FFFFFF'}); C.icon(794, 582, 20, 'mdi:arrow-right', OR); }
else {
C.rect(660, 40, 580, 640, {fill:NV, r:40}); grid(C, 1080, 80, 7, 3, '#FFFFFF', 0.25);
C.text(690, 120, 520, 300, pad2(1), {font:'head', size:300, wt:800, color:'#1F2750', align:'r', lh:1, caps:false});
C.ell(780, 250, 280, 280, {grad:{ang:135, stops:[[0, '#E8322A'], [100, AM]]}}); C.icon(855, 325, 130, 'mdi:presentation', '#FFFFFF');
C.rect(620, 560, 220, 64, {grad:GO, r:32}); C.ell(784, 572, 40, 40, {fill:'#FFFFFF'}); C.icon(794, 582, 20, 'mdi:arrow-right', OR);
}
grid(C, 88, 60, 4, 2, OR, 0.35);
K5.coverText(C, 88, 150, 520, {max:58, kc:OR, by:640, rule:function(C, x, y){ dashes(C, x, y, NV); }, ruleH:24});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 0, W, H, {fill:NV});
C.text(560, 40, 700, 360, pad2(C.i + 1), {font:'head', size:340, wt:800, color:'#1F2750', align:'r', lh:1, caps:false});
if (photo) { D.imgBox(C, 760, 120, 440, 480, {r:30}); C.rect(730, 540, 180, 56, {grad:GO, r:28}); }
else { C.rect(760, 210, 440, 300, {grad:GO, r:40}); C.icon(900, 280, 160, 'mdi:message-text-outline', '#FFFFFF'); }
grid(C, 88, 60, 4, 2, AM, 0.5);
K5.closeText(C, 88, 170, 600, {tc:'#FFFFFF', sc:'#C4C8DC', mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {grad:GO, r:10}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'chapterblaze', name:'Chapter Blaze', tag:'Navy · flame gradient · ghost numerals',
desc:'Deep navy and a red-to-amber flame gradient with giant ghost chapter numerals behind every title, gradient pill buttons and square dot grids; lists become numbered chapter bands. For training sessions, workshops, courses and onboarding.',
fonts:{head:'Archivo', body:'Archivo', headW:800, bodyW:400, headLh:1.04, gf:'Archivo:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:14, coverLbl:'Chapter', closeLbl:'Chapter',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.text(W - 340, -6, 300, 170, pad2(C.i + 1), {font:'head', size:168, wt:800, color:GHOST, align:'r', lh:1, caps:false}); grid(C, W - 140, 36, 6, 2, OR, 0.3); C.rect(0, H - 8, 300, 8, {grad:GO}); },
cover:cover, closing:closing,
rule:function(C, x, y){ dashes(C, x, y, OR); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:PALE, r:14}); C.rect(x + 16, y + h - 6, w - 32, 6, {grad:GO, r:3}); return {tc:NV, mc:MU, ac:OR}; }
if (o.hot) { C.rect(x, y, w, h, {fill:NV, r:14}); C.rect(x + 16, y + h - 6, w - 32, 6, {grad:GO, r:3}); return {tc:'#FFFFFF', mc:'#C4C8DC', ac:AM, hot:1}; }
C.rect(x, y, w, h, {fill:PALE, r:14}); C.rect(x + 16, y + h - 6, w - 32, 6, {grad:GO, r:3}); return {tc:NV, mc:MU, ac:OR};
},
mark:function(C, x, y, s, t, k, cc){ if (cc && cc.hot) { C.rect(x, y, s, s, {fill:'#FFFFFF', r:s * 0.28}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, OR); return; } C.rect(x, y, s, s, {grad:GV, r:s * 0.28}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:NV}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); C.ell(x + s * 0.74, y - s * 0.04, s * 0.3, s * 0.3, {grad:GO}); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {grad:GV}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {grad:GO, r:3}); },
conc:{kind:'pill', fill:OR, op:0.09, label:'Remember', lc:OR, tc:NV},
L:{cards:'Flame cards', rows:'Chapter rows', steps:'Flame steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function bands(C, Z, o){
K5.list(C, Z, {aw:136, pr:20, gap:12, rhMax:112, gx:24}, function(C, k, t, b){
C.rect(b.x, b.y, b.w, b.h, {fill:PALE, r:14}); C.rect(b.x, b.y, 112, b.h, {fill:NV, r:14}); C.rect(b.x + 96, b.y, 16, b.h, {fill:NV});
K.poly(C, [[b.x + 112, b.cy - 11], [b.x + 124, b.cy], [b.x + 112, b.cy + 11]], {fill:NV});
num(C, b.x, b.y, 112, b.h, k, Math.min(40, b.h * 0.46), k % 2 ? AM : OR);
});
}
function flames(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.5), tg:20, vMax:52, vw:function(cw){ return cw - 70; }}, function(C, k, t, b){
C.rect(b.x + 12, b.y, b.w - 24, b.ah, {grad:k % 2 ? {ang:135, stops:[[0, NV], [100, '#2D3A78']]} : {ang:135, stops:[[0, '#E8322A'], [100, AM]]}, r:24});
grid(C, b.x + 30, b.y + 20, 4, 1, '#FFFFFF', 0.4);
return {vc:'#FFFFFF', vy:b.y + (b.ah - b.vs * 1.15) / 2 + 8};
});
}
return {agenda:[['Chapter bands', pg(bands)]], p3:[['Chapter bands', pg(bands)]], p56:[['Chapter bands', pg(bands)]], timeline:[['Chapter bands', pg(bands)]], stats:[['Flame blocks', pg(flames)]]};
}
});
})();
</script>
    <?php
}, 1183);
