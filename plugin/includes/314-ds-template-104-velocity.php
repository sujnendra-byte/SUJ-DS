<?php
/**
 * Code Snippets export — DS Template 104 — Velocity
 *
 * Snippet ID : 1146
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:49:30
 * Load order : 314 of 345
 */
/**
 * DS Template 104 — Velocity
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl104_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl104_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('velocity', array(
        'name'     => "Velocity",
        'fonts'    => array('head' => "Kanit", 'body' => "Barlow"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1E1E24', 'accent' => '#E10600', 'accent2' => '#1E1E24'),
        'voice'    => "Fast, competitive and energetic: pace, targets and winning. Punchy sentences with scores, times and percentages.",
        'recipe'   => "Where we stand in the race, what makes us faster (two to four points each), the scoreboard, the laps ahead and the finish line.",
        'generate' => 'ds_tpl104_generate',
        'build'    => 'ds_tpl104_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl104_generate') ) {
    function ds_tpl104_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl104_build') ) {
    function ds_tpl104_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-velocity">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 104 - Velocity : tall slanted strips in grey, graphite and racing red lean forward like speed lines */
var RD = '#E10600', GF = '#1E1E24', GY = '#C9CCD3', MU = '#5E6068', LN = '#D9DBE0', PALE = '#F1F2F4';
var P = {bg:'#FFFFFF', text:GF, title:GF, muted:MU, accent:RD, accent2:GF, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [RD, GF, '#8A8D96', '#FF6B5E'];
function para(C, x, y, w, h, sk, col, op){ K5.fill(C, Q.poly([], [[x + sk, y], [x + w + sk, y], [x + w, y + h], [x, y + h]]), col, op); }
function lines(C, x, y, col){ C.rect(x, y, 220, 5, {fill:col}); C.rect(x + 40, y + 14, 130, 3, {fill:col, op:0.6}); C.rect(x + 16, y + 26, 70, 3, {fill:col, op:0.35}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 790, 0, 350, H, {r:0}); K5.fill(C, Q.poly([], [[780, 0], [960, 0], [800, H], [780, H]]), '#FFFFFF'); } else para(C, 800, 0, 150, H, 160, GY);
para(C, 980, 0, 150, H, 160, GF); para(C, 1160, 0, 300, H, 160, RD);
K5.coverText(C, 88, 140, 660, {max:66, lh:1, by:644, rule:function(C, x, y){ lines(C, x, y, RD); }, ruleH:50});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:GF});
if (photo) { D.imgBox(C, 830, 0, 300, H, {r:0}); K5.fill(C, Q.poly([], [[820, 0], [1000, 0], [840, H], [820, H]]), GF); } else para(C, 840, 0, 130, H, 160, '#3A3A44');
para(C, 1000, 0, 130, H, 160, GY); para(C, 1160, 0, 300, H, 160, RD);
K5.closeText(C, 88, 150, 660, {tc:'#FFFFFF', sc:'#C7C9D1', max:84, mc:RD});
}
K5.make({
id:'velocity', name:'Velocity', tag:'White · slanted speed strips',
desc:'Tall slanted strips in grey, graphite and racing red lean forward like speed lines, with heavy capital headings, slanted number blocks and lane rows. Fast and competitive, for sales kick-offs, sport, automotive and performance reviews.',
fonts:{head:'Kanit', body:'Barlow', headW:700, bodyW:400, headLh:1.04, headCaps:true, gf:'Kanit:wght@500;600;700;800|Barlow:wght@400;500;600;700'},
P:P, LC:LC, cardR:0, tMax:42, coverLbl:'Speed strips', closeLbl:'Speed strips',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; para(C, W - 150, 0, 30, 70, 26, GY); para(C, W - 108, 0, 30, 70, 26, GF); para(C, W - 66, 0, 80, 70, 26, RD); },
cover:cover, closing:closing,
rule:function(C, x, y){ para(C, x, y, 70, 8, 6, RD); para(C, x + 78, y, 24, 8, 6, GF); return 20; },
pad:32,
card:function(C, x, y, w, h, k, o){
var hot = o.hot; para(C, x, y, w - 18, h, 18, hot ? GF : PALE); if (!o.big) para(C, x, y + h - 8, w - 18, 8, 1, hot ? RD : (k % 2 ? GF : RD));
return hot ? {tc:'#FFFFFF', mc:'#C7C9D1', ac:'#FFFFFF', hot:1} : {tc:GF, mc:MU, ac:RD};
},
row:function(C, x, y, w, h, k){ para(C, x, y + h - 3, w - 6, 3, 2, LN); return {tc:GF, mc:MU, ac:RD}; },
mark:function(C, x, y, s, t, k, cc){ para(C, x, y, s - 8, s, 8, RD); K.icon(C, x + s * 0.27, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ para(C, x, y, s - 8, s, 8, k % 2 ? GF : RD); num(C, x, y, s, s, k, s * 0.4, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ para(C, cx - s / 2, cy - s / 2, s - 8, s, 8, k % 2 ? GF : RD); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.38, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 5, x2 - x1, 10, {fill:PALE}); C.line(x1, y, x2, y, {color:GF, lw:2, dash:true}); },
conc:{kind:'box', fill:GF, r:0, label:'Finish line', lc:'#FF6B5E', tc:'#FFFFFF'},
L:{cards:'Slant cards', rows:'Grid rows', steps:'Lap steps', stmt:'Pace note', stats:'Plain numbers'},
sig:function(pg, B){
function slant(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.48), tg:22, vMax:56, vw:function(cw){ return cw - 90; }}, function(C, k, t, b){
var c = [GF, RD, '#8A8D96', RD][k]; para(C, b.x + 6, b.y, b.w - 46, b.ah, 30, c); para(C, b.x + 6, b.y + b.ah + 6, b.w - 46, 6, 0, c, 0.35);
return {vc:'#FFFFFF', vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function lanes(C, Z, o){
K5.list(C, Z, {aw:124, pr:44, gap:12, rhMax:104, cols:1, max:6}, function(C, k, t, b){
para(C, b.x, b.y, b.w - 26, b.h, 26, PALE); para(C, b.x, b.y, 78, b.h, 26, k % 2 ? GF : RD);
num(C, b.x + 12, b.y, 80, b.h, k, Math.min(28, b.h * 0.34), '#FFFFFF');
});
}
function laps(C, Z, o){
K5.alt(C, Z, {same:true, at:0.2, off:58, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 14, g.w, 28, {fill:GF}); C.line(g.x + 10, g.y, g.x + g.w - 10, g.y, {color:'#FFFFFF', lw:2, dash:true}); }}, function(C, k, t, b){
para(C, b.cx - 38, b.cy - 34, 60, 68, 16, k === b.n - 1 ? GF : RD); C.rect(b.cx - 38, b.cy - 34, 76, 0.1, {fill:RD}); num(C, b.cx - 34, b.cy - 34, 68, 68, k, 22, '#FFFFFF');
});
}
return {agenda:[['Lanes', pg(lanes)]], p3:[['Lanes', pg(lanes)]], p4:[['Lanes', pg(lanes)]], stats:[['Slanted blocks', pg(slant)]], timeline:[['Lap markers', pg(laps)]]};
}
});
})();
</script>
    <?php
}, 1154);
