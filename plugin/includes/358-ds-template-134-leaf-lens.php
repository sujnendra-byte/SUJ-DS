<?php
/**
 * Code Snippets export — DS Template 134 — Leaf Lens
 *
 * Snippet ID : 1220
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:54
 * Load order : 358 of 369
 */
/**
 * DS Template 134 — Leaf Lens
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl134_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl134_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('leaflens', array(
        'name'     => "Leaf Lens",
        'fonts'    => array('head' => "Outfit", 'body' => "Outfit"),
        'pal'      => array('bg' => '#F5F8EF', 'text' => '#12301F', 'accent' => '#1C4A33', 'accent2' => '#8CC63F'),
        'voice'    => "Responsible and evidence-led: targets, impact metrics and commitments stated plainly.",
        'recipe'   => "Our commitment, environmental, social and governance pillars, impact numbers, progress against targets over time and the roadmap ahead.",
        'generate' => 'ds_tpl134_generate',
        'build'    => 'ds_tpl134_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl134_generate') ) {
    function ds_tpl134_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl134_build') ) {
    function ds_tpl134_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-leaflens">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 134 - Leaf Lens : deep forest green with lime, big overlapping discs and lens-shaped leaves */
var FG = '#143826', GN = '#1C4A33', LI = '#8CC63F', LI2 = '#C5E86C', INK = '#12301F', MU = '#4D6355', BG = '#F5F8EF', LN = '#DCE5D3';
var P = {bg:BG, text:INK, title:INK, muted:MU, accent:GN, accent2:LI, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [GN, '#4E8A2E', '#2F6B4F', LI];
function lensD(d, cx, cy, s, ang){
var a = ang * RAD, ux = Math.cos(a), uy = Math.sin(a), nx = -uy, ny = ux, x1 = cx - ux * s / 2, y1 = cy - uy * s / 2, x2 = cx + ux * s / 2, y2 = cy + uy * s / 2, b = s * 0.36, dx = x2 - x1, dy = y2 - y1;
d.push(['M', x1, y1], ['C', x1 + dx * 0.2 + nx * b, y1 + dy * 0.2 + ny * b, x1 + dx * 0.8 + nx * b, y1 + dy * 0.8 + ny * b, x2, y2], ['C', x1 + dx * 0.8 - nx * b, y1 + dy * 0.8 - ny * b, x1 + dx * 0.2 - nx * b, y1 + dy * 0.2 - ny * b, x1, y1], ['Z']); return d;
}
function leaf(C, cx, cy, s, ang, col, op){ return K5.fill(C, lensD([], cx, cy, s, ang), col, op); }
function vein(C, cx, cy, s, ang, col, op){ var a = ang * RAD; C.line(cx - Math.cos(a) * s * 0.4, cy - Math.sin(a) * s * 0.4, cx + Math.cos(a) * s * 0.4, cy + Math.sin(a) * s * 0.4, {color:col, lw:Math.max(1.2, s * 0.02), op:op}); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:700, color:col, align:'c', va:'m', lh:1, caps:false}); }
function forest(C, photo){
C.rect(0, 0, W, H, {fill:FG});
C.ell(700, -160, 720, 720, {grad:{ang:135, stops:[[0, LI2], [100, '#3E8E3A']]}});
C.ell(540, 330, 520, 520, {fill:GN, op:0.92});
if (photo) { C.ell(756, 126, 468, 468, {fill:FG}); D.imgBox(C, 770, 140, 440, 440, {r:220}); C.ell(756, 126, 468, 468, {line:LI, lw:4}); }
else { leaf(C, 1010, 300, 360, -40, FG, 0.22); vein(C, 1010, 300, 360, -40, FG, 0.3); leaf(C, 860, 560, 200, -60, LI, 0.85); vein(C, 860, 560, 200, -60, FG, 0.4); }
leaf(C, 1180, 640, 120, -25, LI, 0.6);
}
function cover(C){ forest(C, (C.o || {}).img === 'photo'); K5.coverText(C, 88, 170, 560, {tc:'#FFFFFF', sc:'#CFE0D4', kc:LI, max:60, by:640, rule:function(C, x, y){ C.rect(x, y, 90, 4, {fill:LI, r:2}); }, ruleH:22}); }
function closing(C){ forest(C, (C.o || {}).img === 'photo'); K5.closeText(C, 88, 190, 560, {tc:'#FFFFFF', sc:'#CFE0D4', mark:function(C, x, y, k, c){ leaf(C, x + 18, y + 18, 44, -45, LI); C.icon(x + 10, y + 10, 16, c.icon, FG); }}); }
K5.make({
id:'leaflens', name:'Leaf Lens', tag:'Forest green · lime · leaves and discs',
desc:'Deep forest green with lime gradients, big overlapping discs and lens-shaped leaves; points sit on leaf marks, timelines grow along a stem and numbers live inside leaf lenses. For ESG, sustainability, impact reports and agriculture.',
fonts:{head:'Outfit', body:'Outfit', headW:700, bodyW:400, headLh:1.06, gf:'Outfit:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:18, coverLbl:'Canopy', closeLbl:'Canopy',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.ell(W - 120, -130, 250, 250, {fill:LI, op:0.28}); C.ell(W - 60, -170, 240, 240, {fill:GN, op:0.16}); leaf(C, W - 190, 50, 44, -35, GN); leaf(C, 34, H - 40, 46, -50, LI, 0.5); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 60, 4, {fill:LI, r:2}); leaf(C, x + 76, y + 2, 20, -30, GN); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:'#FFFFFF', r:18, line:LN, lw:1.2}); leaf(C, x + w - 40, y + 34, 40, -40, LI, 0.5); return {tc:INK, mc:MU, ac:GN}; }
if (o.hot) { C.rect(x, y, w, h, {fill:GN, r:18}); leaf(C, x + w - 30, y + 26, 30, -40, LI); return {tc:'#FFFFFF', mc:'#CFE0D4', ac:LI, hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', r:18, line:LN, lw:1.2}); leaf(C, x + w - 26, y + 22, 22, -40, LI, 0.8); return {tc:INK, mc:MU, ac:GN};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; leaf(C, x + s / 2, y + s / 2, s * 1.3, -45, hot ? LI : LC[k % 3]); K.icon(C, x + s * 0.29, y + s * 0.29, s * 0.42, t, hot ? FG : '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:'#FFFFFF', line:LI, lw:4}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, GN); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:GN, lw:3}); var n = Math.floor((x2 - x1) / 70); for (var i = 1; i < n; i++) leaf(C, x1 + i * 70, y + (i % 2 ? -9 : 9), 22, i % 2 ? -50 : 50, LI); },
conc:{kind:'pill', fill:LI, op:0.2, label:'Impact', lc:'#3E6B12', tc:INK},
L:{cards:'Leaf cards', rows:'Leaf rows', steps:'Stem steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function stem(C, Z, o){
K5.alt(C, Z, {off:84, fallback:B.rows, pre:function(C, g){ C.line(g.x, g.y, g.x + g.w, g.y, {color:GN, lw:4}); }}, function(C, k, t, b){
var up = b.up; leaf(C, b.cx, b.cy + (up ? -34 : 34), 76, up ? -90 : 90, k % 2 ? LI : GN); C.ell(b.cx - 9, b.cy - 9, 18, 18, {fill:'#FFFFFF', line:GN, lw:3});
});
}
function lenses(C, Z, o){
K5.stat(C, Z, {ah:Math.min(180, Z.h * 0.5), tg:18, vMax:44, vw:function(cw){ return cw * 0.5; }}, function(C, k, t, b){
var s = Math.min(b.w - 20, b.ah * 2.4), cy = b.y + b.ah / 2; leaf(C, b.cx, cy, s, 0, k % 2 ? LI : GN); vein(C, b.cx, cy, s, 0, '#FFFFFF', 0.25);
return {vc:k % 2 ? FG : '#FFFFFF', vy:cy - b.vs * 0.58};
});
}
return {p3:[['Leaf stem', pg(stem)]], p4:[['Leaf stem', pg(stem)]], timeline:[['Leaf stem', pg(stem)]], stats:[['Leaf lenses', pg(lenses)]]};
}
});
})();
</script>
    <?php
}, 1184);
