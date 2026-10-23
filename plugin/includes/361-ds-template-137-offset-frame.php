<?php
/**
 * Code Snippets export — DS Template 137 — Offset Frame
 *
 * Snippet ID : 1223
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:59
 * Load order : 361 of 369
 */
/**
 * DS Template 137 — Offset Frame
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl137_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl137_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('offsetframe', array(
        'name'     => "Offset Frame",
        'fonts'    => array('head' => "Montserrat", 'body' => "Montserrat"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0E2A47', 'accent' => '#0E2A47', 'accent2' => '#E8A520'),
        'voice'    => "Trustworthy professional voice: expertise, process and client outcomes stated with authority.",
        'recipe'   => "Who we are, areas of expertise, how we work with clients, the team, a case study with results and how to contact us.",
        'generate' => 'ds_tpl137_generate',
        'build'    => 'ds_tpl137_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl137_generate') ) {
    function ds_tpl137_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl137_build') ) {
    function ds_tpl137_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-offsetframe">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 137 - Offset Frame : deep navy and mustard, photos and cards shifted out of thick offset frames */
var NV = '#0E2A47', GD = '#E8A520', MU = '#56677C', PALE = '#F4F6F9', LN = '#DDE3EA';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:NV, accent2:GD, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [NV, GD, '#2A4C70', '#B57F0E'];
function dots(C, x, y, cols, rows, gap, col, op){ var d = []; for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) Q.circ(d, x + i * gap, y + j * gap, 3); K5.fill(C, d, col, op); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function stage(C, photo, icon){
C.rect(0, 0, 84, 84, {fill:GD}); C.rect(860, 0, W - 860, H, {fill:NV}); dots(C, 1130, 60, 4, 4, 22, '#FFFFFF', 0.6); dots(C, 60, H - 110, 2, 4, 20, NV, 0.25);
C.rect(560, 150, 600, 410, {fill:GD});
if (photo) D.imgBox(C, 586, 176, 600, 410, {r:0});
else { C.rect(586, 176, 600, 410, {fill:'#163B61'}); C.rect(586, 176, 600, 410, {line:'#FFFFFF', lw:1.5, lop:0.25}); C.icon(800, 290, 170, icon, '#FFFFFF'); }
C.rect(1060, 500, 120, 120, {fill:GD});
}
function cover(C){ stage(C, (C.o || {}).img === 'photo', 'mdi:briefcase-variant-outline'); K5.coverText(C, 88, 180, 440, {max:56, kc:GD, by:640, rule:function(C, x, y){ C.rect(x, y, 60, 6, {fill:GD}); }, ruleH:26}); }
function closing(C){ stage(C, (C.o || {}).img === 'photo', 'mdi:handshake-outline'); K5.closeText(C, 88, 190, 440, {mark:function(C, x, y, k, c){ C.rect(x + 5, y + 5, 34, 34, {fill:GD}); C.rect(x, y, 34, 34, {fill:NV}); C.icon(x + 9, y + 9, 16, c.icon, '#FFFFFF'); }}); }
K5.make({
id:'offsetframe', name:'Offset Frame', tag:'Navy · mustard · offset frames',
desc:'Deep navy and mustard where every photo, card and number is shifted out of a thick offset frame, with solid mustard corner squares and quiet dot grids. For law firms, notaries, consultancies, company profiles and professional services.',
fonts:{head:'Montserrat', body:'Montserrat', headW:800, bodyW:400, headLh:1.06, gf:'Montserrat:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Frame', closeLbl:'Frame',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.rect(0, 0, 40, 40, {fill:GD}); C.rect(W - 200, H - 20, 200, 20, {fill:GD}); C.rect(W - 54, 46, 18, 18, {line:GD, lw:2}); C.rect(W - 62, 38, 18, 18, {fill:NV}); dots(C, W - 120, 40, 2, 2, 14, NV, 0.3); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 6, {fill:GD}); return 18; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x + 10, y + 10, w - 10, h - 10, {line:GD, lw:2.5}); C.rect(x, y, w - 10, h - 10, {fill:PALE}); return {tc:NV, mc:MU, ac:NV}; }
C.rect(x + 8, y + 8, w - 8, h - 8, {line:GD, lw:2.5}); C.rect(x, y, w - 8, h - 8, {fill:o.hot ? NV : PALE});
return o.hot ? {tc:'#FFFFFF', mc:'#C3CFDC', ac:GD, hot:1} : {tc:NV, mc:MU, ac:NV};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x + 6, y + 6, s, s, {fill:GD}); C.rect(x, y, s, s, {fill:hot ? '#FFFFFF' : NV}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? NV : '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2 + 6, cy - s / 2 + 6, s, s, {line:GD, lw:2.5}); C.rect(cx - s / 2, cy - s / 2, s, s, {fill:NV}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 1.5, x2 - x1, 3, {fill:NV, op:0.8}); },
conc:{kind:'box', fill:PALE, r:0, label:'In short', lc:'#9A6A00', tc:NV},
L:{cards:'Framed cards', rows:'Framed rows', steps:'Framed steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function frames(C, Z, o){
K5.cols(C, Z, {tg:24, pad:4, ah:function(n, cw, Z){ return Math.min(Z.h * 0.42, 170); }, fallback:B.rows}, function(C, k, t, b){
var w = b.w - 12, h = b.ah - 12; C.rect(b.x + 12, b.y + 12, w, h, {line:GD, lw:3}); C.rect(b.x, b.y, w, h, {fill:k === 0 ? GD : NV});
num(C, b.x + 20, b.y + 18, 100, h * 0.5, k, Math.min(48, h * 0.36), k === 0 ? NV : GD); K.icon(C, b.x + w - 64, b.y + h - 64, 40, t, k === 0 ? NV : '#FFFFFF');
});
}
function framestats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(180, Z.h * 0.5), tg:20, vMax:52, vw:function(cw){ return cw - 80; }}, function(C, k, t, b){
var x = b.x + 12, w = b.w - 36, h = b.ah - 12; C.rect(x + 12, b.y + 12, w, h, {line:GD, lw:3}); C.rect(x, b.y, w, h, {fill:NV});
return {vc:k === 0 ? GD : '#FFFFFF', vx:x, vw:w, vy:b.y + (h - b.vs * 1.15) / 2};
});
}
return {p3:[['Offset frames', pg(frames)]], p4:[['Offset frames', pg(frames)]], stats:[['Framed numbers', pg(framestats)]]};
}
});
})();
</script>
    <?php
}, 1187);
