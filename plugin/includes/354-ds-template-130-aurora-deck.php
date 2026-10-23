<?php
/**
 * Code Snippets export — DS Template 130 — Aurora Deck
 *
 * Snippet ID : 1216
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:03
 * Load order : 354 of 369
 */
/**
 * DS Template 130 — Aurora Deck
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl130_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl130_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('aurora', array(
        'name'     => "Aurora Deck",
        'fonts'    => array('head' => "Poppins", 'body' => "Poppins"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1E1B3A', 'accent' => '#6A3FB5', 'accent2' => '#12A58A'),
        'voice'    => "Confident, forward-looking and clear: momentum, milestones and measurable growth in plain business language.",
        'recipe'   => "Where we are, the pillars of the plan (two to four points each), the numbers that prove it, the road ahead as dated milestones and the decision we need.",
        'generate' => 'ds_tpl130_generate',
        'build'    => 'ds_tpl130_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl130_generate') ) {
    function ds_tpl130_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl130_build') ) {
    function ds_tpl130_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-aurora">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 130 - Aurora Deck : teal-to-violet gradient fields, outline rings, triangles and a winding road */
var INK = '#1E1B3A', VI = '#6A3FB5', TE = '#12A58A', MU = '#5D5A78', PALE = '#F5F3FB', LN = '#E4E0F1';
var GR = {ang:135, stops:[[0, TE], [100, VI]]}, GH = {ang:0, stops:[[0, TE], [100, VI]]};
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:VI, accent2:TE, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [VI, TE, '#3D7FD6', '#B0459E'];
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:700, color:col, align:'c', va:'m', lh:1, caps:false}); }
function ring(C, cx, cy, r, col, op, lw){ C.ell(cx - r, cy - r, r * 2, r * 2, {line:col, lw:lw || 2, lop:op}); }
function tri(C, x, y, s, col, op){ var d = []; Q.pts(d, [[x, y - s * 0.6], [x + s * 0.52, y + s * 0.3], [x - s * 0.52, y + s * 0.3], [x, y - s * 0.6]]); K5.stroke(C, d, col, 2, op); }
function plus(C, x, y, s, col, op){ var d = []; Q.line(d, x - s, y, x + s, y); Q.line(d, x, y - s, x, y + s); K5.stroke(C, d, col, 2.4, op); }
function wave(x0, x1, y, n, A){ var d = [['M', x0, y]], sw = (x1 - x0) / n; for (var i = 0; i < n; i++) { var a = x0 + i * sw, b = a + sw, s = i % 2 ? 1 : -1; d.push(['C', a + sw * 0.3, y + s * A * 1.33, a + sw * 0.7, y + s * A * 1.33, b, y]); } return d; }
function sky(C){
C.rect(0, 0, W, H, {grad:GR});
C.ell(-180, 430, 540, 540, {fill:'#FFFFFF', op:0.06});
ring(C, 1150, -30, 230, '#FFFFFF', 0.22); ring(C, 1150, -30, 160, '#FFFFFF', 0.14);
tri(C, 660, 86, 26, '#FFFFFF', 0.5); plus(C, 590, 650, 10, '#FFFFFF', 0.6); tri(C, 1196, 640, 20, '#FFFFFF', 0.45); plus(C, 1210, 300, 8, '#FFFFFF', 0.5);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
sky(C);
if (photo) { C.ell(770, 140, 440, 440, {fill:'#FFFFFF', op:0.16}); D.imgBox(C, 800, 170, 380, 380, {r:190}); ring(C, 990, 360, 236, '#FFFFFF', 0.5); }
else {
var d = wave(740, 1240, 470, 4, 60); K5.stroke(C, d, '#FFFFFF', 22, 0.22); K5.stroke(C, d, '#FFFFFF', 2, 0.8);
[0, 1, 2, 3, 4].forEach(function(i){ var cx = 740 + i * 125; C.ell(cx - 16, 454, 32, 32, {fill:'#FFFFFF'}); C.ell(cx - 7, 463, 14, 14, {fill:i === 4 ? TE : VI}); });
ring(C, 990, 300, 120, '#FFFFFF', 0.3); C.ell(930, 240, 120, 120, {fill:'#FFFFFF', op:0.12});
}
K5.coverText(C, 88, 150, photo ? 640 : 620, {tc:'#FFFFFF', sc:'#ECE8FC', kc:'#BDF4E4', max:58, by:640, rule:function(C, x, y){ C.rect(x, y, 64, 5, {fill:'#FFFFFF', r:2.5}); }, ruleH:24});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
sky(C);
if (photo) { D.imgBox(C, 800, 170, 380, 380, {r:190}); ring(C, 990, 360, 236, '#FFFFFF', 0.5); }
else { ring(C, 980, 360, 170, '#FFFFFF', 0.35); ring(C, 980, 360, 240, '#FFFFFF', 0.18); C.ell(880, 260, 200, 200, {fill:'#FFFFFF', op:0.14}); C.icon(930, 310, 100, 'mdi:hand-wave-outline', '#FFFFFF'); }
K5.closeText(C, 88, 170, 620, {tc:'#FFFFFF', sc:'#ECE8FC', mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:'#FFFFFF'}); C.icon(x + 10, y + 10, 16, c.icon, VI); }});
}
K5.make({
id:'aurora', name:'Aurora Deck', tag:'White · teal-to-violet gradient · road',
desc:'Teal-to-violet gradient covers with outline rings, triangles and plus marks; content slides carry a gradient corner orb, glowing card edges and a winding road timeline. For business decks, strategy reviews and roadmaps.',
fonts:{head:'Poppins', body:'Poppins', headW:600, bodyW:400, headLh:1.1, gf:'Poppins:wght@400;500;600;700'},
P:P, LC:LC, cardR:18, coverLbl:'Aurora', closeLbl:'Aurora',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.ell(W - 110, -110, 220, 220, {grad:GR}); ring(C, W, 0, 150, VI, 0.25); tri(C, W - 196, 46, 16, TE, 0.7); ring(C, 0, H, 66, VI, 0.2); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 60, 5, {grad:GH, r:2.5}); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:PALE, r:18}); C.rect(x + 22, y + 26, 6, h - 52, {grad:{ang:90, stops:[[0, TE], [100, VI]]}, r:3}); return {tc:INK, mc:MU, ac:VI}; }
if (o.hot) { C.rect(x, y, w, h, {grad:GR, r:18}); return {tc:'#FFFFFF', mc:'#EEEAFD', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', r:18, line:LN, lw:1.2, shadow:{o:0.08, b:20, d:6}}); C.rect(x + 18, y, w - 36, 5, {grad:GH, r:2.5});
return {tc:INK, mc:MU, ac:LC[k % 4]};
},
mark:function(C, x, y, s, t, k, cc){ if (cc && cc.hot) { C.ell(x, y, s, s, {fill:'#FFFFFF'}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, VI); return; } C.ell(x, y, s, s, {grad:GR}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ ring(C, x + s / 2, y + s / 2, s / 2 + 6, VI, 0.25, 1.5); C.ell(x, y, s, s, {grad:GR}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {grad:GR}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {grad:GH, r:3}); },
conc:{kind:'pill', fill:VI, op:0.08, label:'Key takeaway', lc:VI, tc:INK},
L:{cards:'Glow cards', rows:'Ribbon rows', steps:'Gradient steps', stmt:'Statement', stats:'Big numbers'},
sig:function(pg, B){
function road(C, Z, o){
K5.alt(C, Z, {off:88, fallback:B.rows, pre:function(C, g){
var A = Math.min(40, g.h * 0.1), d = [['M', g.x, g.y]], pts = [g.x], i;
for (i = 0; i < g.n; i++) pts.push(g.x + g.cw * (i + 0.5)); pts.push(g.x + g.w);
for (i = 0; i < pts.length - 1; i++) { var a = pts[i], b = pts[i + 1], s = i % 2 ? 1 : -1, aa = (i === 0 || i === pts.length - 2) ? A * 0.6 : A; d.push(['C', a + (b - a) * 0.3, g.y + s * aa * 1.33, a + (b - a) * 0.7, g.y + s * aa * 1.33, b, g.y]); }
K5.stroke(C, d, INK, 24); K5.stroke(C, d, '#FFFFFF', 2, 0.9);
}}, function(C, k, t, b){ C.ell(b.cx - 26, b.cy - 26, 52, 52, {grad:GR, line:'#FFFFFF', lw:4}); num(C, b.cx - 26, b.cy - 26, 52, 52, k, 18, '#FFFFFF'); });
}
function discs(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.55), tg:18, vMax:46, vw:function(cw, ah){ return Math.min(ah, cw - 40) * 0.72; }}, function(C, k, t, b){
var d = Math.min(b.ah, b.w - 40), x = b.cx - d / 2, y = b.y + (b.ah - d) / 2;
ring(C, b.cx, y + d / 2, d / 2 + 10, k % 2 ? TE : VI, 0.3, 2); C.ell(x, y, d, d, {grad:k % 2 ? {ang:135, stops:[[0, VI], [100, TE]]} : GR});
return {vc:'#FFFFFF', vy:y + (d - b.vs * 1.15) / 2};
});
}
return {p3:[['Winding road', pg(road)]], p4:[['Winding road', pg(road)]], timeline:[['Winding road', pg(road)]], stats:[['Gradient discs', pg(discs)]]};
}
});
})();
</script>
    <?php
}, 1180);
