<?php
/**
 * Code Snippets export — DS Template 141 — Cloud Nine
 *
 * Snippet ID : 1227
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 04:19:32
 * Load order : 365 of 369
 */
/**
 * DS Template 141 — Cloud Nine
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl141_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl141_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('cloudnine', array(
        'name'     => "Cloud Nine",
        'fonts'    => array('head' => "Rubik", 'body' => "Rubik"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#22205A', 'accent' => '#4F46C8', 'accent2' => '#8A84F2'),
        'voice'    => "Optimistic and simple: big goals broken into small launch steps anyone can follow.",
        'recipe'   => "The mission, the problem we solve, the launch plan in stages, the numbers we aim for, the timeline to lift-off and the call to action.",
        'generate' => 'ds_tpl141_generate',
        'build'    => 'ds_tpl141_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl141_generate') ) {
    function ds_tpl141_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl141_build') ) {
    function ds_tpl141_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-cloudnine">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 141 - Cloud Nine : periwinkle sky with puffy clouds, a launching rocket and a cratered planet */
var PB = '#4F46C8', PB2 = '#8A84F2', LAV = '#EDECFD', LAV2 = '#D9D7FA', INK = '#22205A', MU = '#5E5C8A', PALE = '#F3F2FD', LN = '#E2E0F7';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:PB, accent2:PB2, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [PB, '#6C63E0', '#3A31A8', PB2];
function cloud(C, x, y, w, col){ var h = w * 0.36; C.rect(x, y + h * 0.5, w, h * 0.5, {fill:col, r:h * 0.25}); C.ell(x + w * 0.14, y + h * 0.3, w * 0.34, w * 0.34 * 0.8, {fill:col}); C.ell(x + w * 0.36, y, w * 0.4, h * 0.95, {fill:col}); C.ell(x + w * 0.62, y + h * 0.28, w * 0.28, h * 0.7, {fill:col}); }
function rocket(C, cx, cy, s){
var BODY = '#FFFFFF', RIM = LAV2, CAP = PB, FIN = PB, BAND = '#B4AFF8', GLASS = '#A5EEFF', DARK = '#4A4778';
function kp(e){ if (e) e.keep = 1; return e; } /* rocket parts keep their own colours on every theme */
function X(v){ return cx + v * s; } function Y(v){ return cy + v * s; }
function pt(a, b, c, d, t){ var m = 1 - t; return m * m * m * a + 3 * m * m * t * b + 3 * m * t * t * c + t * t * t * d; }
function cutAt(p, yv){ var lo = 0, hi = 1; for (var i = 0; i < 30; i++) { var md = (lo + hi) / 2; if (pt(p[0][1], p[1][1], p[2][1], p[3][1], md) < yv) lo = md; else hi = md; } var t = (lo + hi) / 2;
var a = [p[0][0] + (p[1][0] - p[0][0]) * t, p[0][1] + (p[1][1] - p[0][1]) * t], b = [p[1][0] + (p[2][0] - p[1][0]) * t, p[1][1] + (p[2][1] - p[1][1]) * t], c = [p[2][0] + (p[3][0] - p[2][0]) * t, p[2][1] + (p[3][1] - p[2][1]) * t];
var ab = [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t], bc = [b[0] + (c[0] - b[0]) * t, b[1] + (c[1] - b[1]) * t], e = [ab[0] + (bc[0] - ab[0]) * t, ab[1] + (bc[1] - ab[1]) * t];
return [p[0], a, ab, e]; }
function tongue(x, top, w, len, col){ kp(C.path([['M', x - w / 2, top], ['C', x - w / 2, top + len * 0.5, x - w * 0.16, top + len * 0.82, x, top + len], ['C', x + w * 0.16, top + len * 0.82, x + w / 2, top + len * 0.5, x + w / 2, top], ['Z']], {fill:col})); }
/* fire: three layered flames with side tongues */
var ft = Y(0.225);
[[0.30, 1, '#FFB070'], [0.22, 0.82, '#FF4D4D'], [0.12, 0.6, '#FFD166']].forEach(function(L){
var w = L[0] * s, k = L[1];
kp(C.rect(cx - w / 2 - s * 0.01, ft - s * 0.01, w + s * 0.02, s * 0.05, {fill:L[2], r:s * 0.02}));
tongue(X(-0.085 * k), ft, w * 0.42, s * 0.27 * k, L[2]); tongue(X(0.085 * k), ft, w * 0.42, s * 0.27 * k, L[2]);
tongue(cx, ft, w * 0.58, s * 0.40 * k, L[2]);
});
/* fins, behind the body */
C.path([['M', X(0.17), Y(-0.08)], ['C', X(0.30), Y(-0.03), X(0.34), Y(0.15), X(0.31), Y(0.36)], ['C', X(0.27), Y(0.25), X(0.21), Y(0.19), X(0.15), Y(0.17)], ['Z']], {fill:FIN});
C.path([['M', X(-0.17), Y(-0.08)], ['C', X(-0.30), Y(-0.03), X(-0.34), Y(0.15), X(-0.31), Y(0.36)], ['C', X(-0.27), Y(0.25), X(-0.21), Y(0.19), X(-0.15), Y(0.17)], ['Z']], {fill:FIN});
/* body: a bullet shape */
var R1 = [[cx, Y(-0.5)], [X(0.13), Y(-0.47)], [X(0.19), Y(-0.28)], [X(0.185), Y(-0.05)]];
kp(C.path([['M', cx, Y(-0.5)], ['C', X(0.13), Y(-0.47), X(0.19), Y(-0.28), X(0.185), Y(-0.05)], ['C', X(0.18), Y(0.08), X(0.165), Y(0.17), X(0.15), Y(0.22)], ['L', X(-0.15), Y(0.22)], ['C', X(-0.165), Y(0.17), X(-0.18), Y(0.08), X(-0.185), Y(-0.05)], ['C', X(-0.19), Y(-0.28), X(-0.13), Y(-0.47), cx, Y(-0.5)], ['Z']], {fill:BODY, stroke:RIM, lw:Math.max(2, s * 0.008)}));
/* nose cap: the top of the same curve, so it fits the body exactly */
var q = cutAt(R1, Y(-0.37)), ex = q[3][0] - cx, ey = q[3][1];
C.path([['M', cx, Y(-0.5)], ['C', q[1][0], q[1][1], q[2][0], q[2][1], q[3][0], ey], ['C', cx + ex * 0.4, ey + s * 0.03, cx - ex * 0.4, ey + s * 0.03, cx - ex, ey], ['C', 2 * cx - q[2][0], q[2][1], 2 * cx - q[1][0], q[1][1], cx, Y(-0.5)], ['Z']], {fill:CAP});
C.path([['M', cx - ex * 0.82, ey - s * 0.028], ['C', cx - ex * 0.3, ey - s * 0.005, cx + ex * 0.3, ey - s * 0.005, cx + ex * 0.82, ey - s * 0.028]], {stroke:DARK, lw:Math.max(1.5, s * 0.007), lop:0.55});
var rv = []; for (var i = 0; i < 5; i++) { var tx = -0.7 + i * 0.35; Q.circ(rv, cx + ex * tx, ey + s * 0.004 - Math.abs(tx) * Math.abs(tx) * s * 0.012 - s * 0.008, s * 0.011); } kp(K5.fill(C, rv, DARK, 0.85));
/* porthole: rim with bolts, dark ring, glass with two glints */
var pc = Y(-0.14), r = s * 0.112;
kp(C.ell(cx - r, pc - r, r * 2, r * 2, {fill:'#BFBCE8'}));
var bl = []; for (var j = 0; j < 6; j++) { var a = (j * 60 - 90) * Math.PI / 180; Q.circ(bl, cx + Math.cos(a) * r * 0.86, pc + Math.sin(a) * r * 0.86, s * 0.009); } kp(K5.fill(C, bl, DARK));
kp(C.ell(cx - r * 0.7, pc - r * 0.7, r * 1.4, r * 1.4, {fill:DARK}));
kp(C.ell(cx - r * 0.58, pc - r * 0.58, r * 1.16, r * 1.16, {fill:GLASS}));
kp(C.line(cx - r * 0.12, pc + r * 0.3, cx + r * 0.18, pc - r * 0.38, {color:'#FFFFFF', lw:Math.max(2, s * 0.01)}));
kp(C.line(cx + r * 0.1, pc + r * 0.36, cx + r * 0.32, pc - r * 0.12, {color:'#FFFFFF', lw:Math.max(2, s * 0.01)}));
/* ribbed base band */
C.path([['M', X(-0.174), Y(0.09)], ['C', X(-0.06), Y(0.125), X(0.06), Y(0.125), X(0.174), Y(0.09)], ['C', X(0.168), Y(0.15), X(0.16), Y(0.19), X(0.15), Y(0.22)], ['L', X(-0.15), Y(0.22)], ['C', X(-0.16), Y(0.19), X(-0.168), Y(0.15), X(-0.174), Y(0.09)], ['Z']], {fill:BAND});
var st = []; for (var k = -3; k <= 3; k++) Q.line(st, cx + k * s * 0.042, Y(0.112) - Math.abs(k) * s * 0.004, cx + k * s * 0.037, Y(0.215)); K5.stroke(C, st, PB, Math.max(1.5, s * 0.007));
cloud(C, X(-0.62), Y(0.5), s * 0.55, LAV2); cloud(C, X(0.12), Y(0.54), s * 0.5, '#FFFFFF');
}
function planet(C, x, y, d){ C.ell(x, y, d, d, {grad:{ang:135, stops:[[0, PB2], [100, '#3A31A8']]}}); C.ell(x + d * 0.22, y + d * 0.2, d * 0.18, d * 0.14, {fill:'#2E2790', op:0.5}); C.ell(x + d * 0.55, y + d * 0.42, d * 0.12, d * 0.1, {fill:'#2E2790', op:0.5}); C.ell(x + d * 0.3, y + d * 0.6, d * 0.1, d * 0.08, {fill:'#2E2790', op:0.5}); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function sky(C, photo){
C.rect(0, 0, W, H, {grad:{ang:180, stops:[[0, '#FFFFFF'], [100, LAV]]}});
cloud(C, 520, 70, 220, LAV); cloud(C, 1040, 110, 180, LAV2); cloud(C, -60, 600, 420, LAV2); cloud(C, 260, 640, 360, '#FFFFFF');
planet(C, 1050, 500, 320);
if (photo) { C.rect(706, 96, 368, 408, {fill:'#FFFFFF', r:34}); D.imgBox(C, 720, 110, 340, 380, {r:26}); cloud(C, 640, 440, 240, '#FFFFFF'); }
else rocket(C, 880, 262, 330);
cloud(C, 780, 600, 300, '#FFFFFF');
}
function cover(C){ sky(C, (C.o || {}).img === 'photo'); K5.coverText(C, 88, 190, 540, {max:62, tc:INK, kc:PB, by:560, rule:function(C, x, y){ C.rect(x, y, 70, 6, {fill:PB, r:3}); C.ell(x + 80, y - 1, 8, 8, {fill:PB2}); }, ruleH:24}); }
function closing(C){ sky(C, (C.o || {}).img === 'photo'); K5.closeText(C, 88, 200, 540, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:PB}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }}); }
K5.make({
id:'cloudnine', name:'Cloud Nine', tag:'Periwinkle sky · clouds · rocket',
desc:'A soft periwinkle sky with puffy clouds, a launching rocket and a cratered planet; icons float on cloud puffs, numbers sit inside clouds and timelines end in a rocket launch. For startups, product launches, growth plans, education and kids-friendly topics.',
fonts:{head:'Rubik', body:'Rubik', headW:700, bodyW:400, headLh:1.06, gf:'Rubik:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:20, coverLbl:'Launch', closeLbl:'Launch',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; cloud(C, W - 200, 20, 150, LAV2); cloud(C, W - 110, 56, 100, LAV); C.ell(W - 260, 40, 14, 14, {fill:PB2}); cloud(C, -40, H - 46, 150, LAV); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:PB, r:2.5}); C.ell(x + 64, y - 1, 7, 7, {fill:PB2}); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {fill:PB, r:20}); return {tc:'#FFFFFF', mc:'#DCDAFB', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:PALE, r:20}); return {tc:INK, mc:MU, ac:PB};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? '#FFFFFF' : LC[k % 4]}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? PB : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ cloud(C, x - s * 0.25, y + s * 0.05, s * 1.5, LAV2); K.icon(C, x + s * 0.24, y + s * 0.2, s * 0.52, t, PB); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:PB, line:LAV2, lw:5}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ var d = []; for (var x = x1; x <= x2; x += 16) Q.circ(d, x, y, 3); K5.fill(C, d, PB2, 0.8); },
conc:{kind:'pill', fill:PB, op:0.08, label:'Lift-off', lc:PB, tc:INK},
L:{cards:'Sky cards', rows:'Cloud rows', steps:'Launch steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function launch(C, Z, o){
K5.alt(C, Z, {off:64, fallback:B.rows, pre:function(C, g){ var d = []; for (var x = g.x; x <= g.x + g.w - 40; x += 16) Q.circ(d, x, g.y, 3.2); K5.fill(C, d, PB2); C.icon(g.x + g.w - 34, g.y - 22, 44, 'mdi:rocket-launch', PB); }}, function(C, k, t, b){
C.ell(b.cx - 26, b.cy - 26, 52, 52, {fill:LC[k % 4], line:LAV2, lw:5}); num(C, b.cx - 26, b.cy - 26, 52, 52, k, 18, '#FFFFFF');
});
}
function clouds(C, Z, o){
K5.stat(C, Z, {ah:Math.min(170, Z.h * 0.46), tg:18, vMax:46, vw:function(cw){ return cw * 0.55; }}, function(C, k, t, b){
var w = Math.min(b.w - 20, b.ah * 2.6), x = b.cx - w / 2, y = b.y + b.ah - w * 0.36 - 6; cloud(C, x, y, w, k % 2 ? LAV2 : PB);
return {vc:k % 2 ? PB : '#FFFFFF', vy:y + w * 0.36 * 0.5 - b.vs * 0.45};
});
}
return {timeline:[['Launch path', pg(launch)]], p4:[['Launch path', pg(launch)]], stats:[['Cloud numbers', pg(clouds)]]};
}
});
})();
</script>
    <?php
}, 1191);
