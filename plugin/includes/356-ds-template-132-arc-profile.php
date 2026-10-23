<?php
/**
 * Code Snippets export — DS Template 132 — Arc Profile
 *
 * Snippet ID : 1218
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:51
 * Load order : 356 of 369
 */
/**
 * DS Template 132 — Arc Profile
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl132_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl132_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('arcprofile', array(
        'name'     => "Arc Profile",
        'fonts'    => array('head' => "Manrope", 'body' => "Manrope"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0F2A5C', 'accent' => '#1565E0', 'accent2' => '#8EC5FF'),
        'voice'    => "Polished corporate voice: who we are, what we deliver and the facts that back it up.",
        'recipe'   => "About the company, mission and services, key facts and figures, milestones over the years, the team and how to get in touch.",
        'generate' => 'ds_tpl132_generate',
        'build'    => 'ds_tpl132_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl132_generate') ) {
    function ds_tpl132_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl132_build') ) {
    function ds_tpl132_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-arcprofile">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 132 - Arc Profile : vivid corporate blue with thick quarter-ring arcs in the corners and progress dials */
var BL = '#1565E0', DB = '#0B3FA0', SKY = '#8EC5FF', INK = '#0F2A5C', MU = '#5B6B88', PALE = '#F2F7FF', LN = '#DCE7F7';
var GR = {ang:135, stops:[[0, BL], [100, DB]]};
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:BL, accent2:SKY, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [BL, DB, '#3B8BF5', '#5AA9FF'];
function band(C, cx, cy, r0, r1, a0, a1, col, op){ return K5.fill(C, Q.sector([], cx, cy, r0, r1, a0, a1), col, op); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function bars(C, x, y, col){ C.rect(x, y, 200, 4, {fill:col, r:2}); C.rect(x + 214, y, 40, 4, {fill:col, r:2}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 0, W, H, {grad:GR});
if (photo) { D.imgBox(C, 700, 0, W - 700, H, {r:0}); band(C, 700, H, 120, 200, 270, 360, BL, 0.85); }
band(C, W, H, 180, 300, 180, 270, '#FFFFFF', 0.12); band(C, W, H, 330, 360, 180, 270, '#FFFFFF', 0.1);
band(C, 0, 0, 90, 150, 0, 90, '#FFFFFF', 0.1);
if (!photo) { band(C, 1010, 300, 110, 150, -90, 180, '#FFFFFF', 0.85); band(C, 1010, 300, 110, 150, 180, 270, SKY, 0.9); C.ell(930, 220, 160, 160, {fill:'#FFFFFF', op:0.1}); C.icon(970, 260, 80, 'mdi:office-building-outline', '#FFFFFF'); }
bars(C, 88, 140, '#FFFFFF');
K5.coverText(C, 88, 180, photo ? 560 : 640, {tc:'#FFFFFF', sc:'#DCE9FF', kc:SKY, max:58, by:640, rule:function(C, x, y){ bars(C, x, y + 4, '#FFFFFF'); }, ruleH:28});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 0, W, H, {grad:GR});
if (photo) { D.imgBox(C, 760, 0, W - 760, H, {r:0}); band(C, 760, H, 120, 200, 270, 360, BL, 0.85); }
else { band(C, W, H, 180, 300, 180, 270, '#FFFFFF', 0.12); band(C, 1000, 330, 120, 160, 0, 290, '#FFFFFF', 0.85); band(C, 1000, 330, 120, 160, 290, 360, SKY); }
band(C, 0, 0, 90, 150, 0, 90, '#FFFFFF', 0.1);
K5.closeText(C, 88, 170, 600, {tc:'#FFFFFF', sc:'#DCE9FF', mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:'#FFFFFF'}); C.icon(x + 10, y + 10, 16, c.icon, BL); }});
}
K5.make({
id:'arcprofile', name:'Arc Profile', tag:'Vivid blue · corner ring arcs · dials',
desc:'Vivid corporate blue with thick quarter-ring arcs sweeping in from the corners, a two-bar title rule and progress-dial numbers; cards lift on soft shadows with a ring corner. For company profiles, annual overviews and corporate introductions.',
fonts:{head:'Manrope', body:'Manrope', headW:800, bodyW:500, headLh:1.08, gf:'Manrope:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:16, coverLbl:'Profile', closeLbl:'Profile',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; band(C, W, 0, 80, 124, 90, 180, SKY, 0.45); band(C, W, 0, 136, 146, 90, 180, BL, 0.35); band(C, 0, H, 40, 70, 270, 360, SKY, 0.4); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 48, 4, {fill:BL, r:2}); C.rect(x + 58, y, 14, 4, {fill:BL, r:2}); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:PALE, r:16}); band(C, x + w, y + h, 50, 70, 180, 270, SKY, 0.5); return {tc:INK, mc:MU, ac:BL}; }
if (o.hot) { C.rect(x, y, w, h, {grad:GR, r:16}); band(C, x + w - 2, y + 2, 26, 40, 90, 180, '#FFFFFF', 0.25); return {tc:'#FFFFFF', mc:'#DCE9FF', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', r:16, shadow:{o:0.12, b:24, d:6}}); band(C, x + w - 2, y + 2, 22, 32, 90, 180, SKY, 0.6);
return {tc:INK, mc:MU, ac:LC[k % 4]};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x - 4, y - 4, s + 8, s + 8, {line:hot ? '#FFFFFF' : SKY, lw:2}); C.ell(x, y, s, s, {fill:hot ? '#FFFFFF' : BL}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? BL : '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:'#FFFFFF', line:LN, lw:2}); band(C, cx, cy, s / 2 - 5, s / 2, -90, -90 + Math.min(359, (k + 1) * 72), BL); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, BL); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:LN, r:2}); },
conc:{kind:'pill', fill:BL, op:0.08, label:'Key fact', lc:BL, tc:INK},
L:{cards:'Lift cards', rows:'Ring rows', steps:'Dial steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function dials(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.56), tg:18, vMax:40, vw:function(cw, ah){ return Math.min(ah, cw - 40) * 0.6; }}, function(C, k, t, b){
var d = Math.min(b.ah, b.w - 40), x = b.cx - d / 2, y = b.y + (b.ah - d) / 2, m = String(b.val).match(/(\d+(\.\d+)?)\s*%/), pct = m ? Math.min(100, parseFloat(m[1])) : [78, 64, 86, 52][k % 4];
C.ring(x, y, d, pct, {color:k === 0 ? BL : LC[k % 4], track:PALE, th:Math.max(12, d * 0.09)});
return {vc:INK, vy:y + (d - b.vs * 1.15) / 2};
});
}
function arcs(C, Z, o){
K5.cols(C, Z, {tg:22, pad:22, ah:function(n, cw, Z){ return Math.min(Z.h * 0.42, 170); }, fallback:B.rows}, function(C, k, t, b){
C.rect(b.x, b.y, b.w, b.h, {fill:k === 0 ? BL : PALE, r:16});
band(C, b.x + b.w, b.y, 60, 96, 90, 180, k === 0 ? '#FFFFFF' : SKY, k === 0 ? 0.2 : 0.55);
C.ell(b.x + 22, b.y + 26, 56, 56, {fill:k === 0 ? '#FFFFFF' : BL}); K.icon(C, b.x + 36, b.y + 40, 28, t, k === 0 ? BL : '#FFFFFF');
num(C, b.x + 22, b.y + 96, 80, 50, k, 40, k === 0 ? '#FFFFFF' : BL);
return k === 0 ? {tc:'#FFFFFF', mc:'#DCE9FF'} : null;
});
}
return {p3:[['Arc tiles', pg(arcs)]], p4:[['Arc tiles', pg(arcs)]], stats:[['Dials', pg(dials)]]};
}
});
})();
</script>
    <?php
}, 1182);
