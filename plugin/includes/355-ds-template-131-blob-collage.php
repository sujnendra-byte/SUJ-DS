<?php
/**
 * Code Snippets export — DS Template 131 — Blob Collage
 *
 * Snippet ID : 1217
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:50
 * Load order : 355 of 369
 */
/**
 * DS Template 131 — Blob Collage
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl131_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl131_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('blobcollage', array(
        'name'     => "Blob Collage",
        'fonts'    => array('head' => "Sora", 'body' => "Sora"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#232327', 'accent' => '#4C2FD1', 'accent2' => '#8FD14F'),
        'voice'    => "Friendly, creative and persuasive: bold ideas in short punchy sentences with concrete benefits.",
        'recipe'   => "The opportunity, our idea, what makes it different, the proof points, how it rolls out and the ask.",
        'generate' => 'ds_tpl131_generate',
        'build'    => 'ds_tpl131_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl131_generate') ) {
    function ds_tpl131_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl131_build') ) {
    function ds_tpl131_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-blobcollage">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 131 - Blob Collage : charcoal and violet split panels with lime, sun-yellow and sky-cyan organic blobs */
var CH = '#232327', VI = '#4C2FD1', LI = '#8FD14F', YE = '#F6C343', CY = '#5CC8F2', MINT = '#6EE7C8', MU = '#63636E', PALE = '#F5F5F7', LN = '#E4E4EA';
var P = {bg:'#FFFFFF', text:CH, title:CH, muted:MU, accent:VI, accent2:LI, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [VI, LI, YE, CY];
function blobD(d, cx, cy, r, seed){
var rn = K5.rnd(seed * 7919 + 13), n = 8, p = [], i;
for (i = 0; i < n; i++) { var a = i / n * Math.PI * 2, rr = r * (0.82 + 0.26 * rn()); p.push([cx + Math.cos(a) * rr, cy + Math.sin(a) * rr]); }
for (i = 0; i < n; i++) { var p0 = p[(i - 1 + n) % n], p1 = p[i], p2 = p[(i + 1) % n], p3 = p[(i + 2) % n]; if (!i) d.push(['M', p1[0], p1[1]]); d.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]); }
d.push(['Z']); return d;
}
function blob(C, cx, cy, r, seed, col, op){ return K5.fill(C, blobD([], cx, cy, r, seed), col, op); }
function dots(C, x, y, cols, rows, gap, col, op){ var d = []; for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) Q.circ(d, x + i * gap, y + j * gap, 3.2); K5.fill(C, d, col, op); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function split(C, photo){
C.rect(0, 0, 770, H, {fill:CH}); C.rect(770, 0, W - 770, H, {fill:VI});
dots(C, 40, 44, 3, 6, 18, '#7A63E8', 0.9);
C.rect(88, H - 70, 260, 4, {fill:MINT, r:2});
if (photo) { blob(C, 880, 500, 150, 2, LI); blob(C, 1210, 120, 92, 5, YE); blob(C, 1150, 610, 130, 8, CY); D.imgBox(C, 830, 120, 380, 420, {r:28}); }
else { blob(C, 930, 520, 170, 2, LI); blob(C, 1170, 330, 120, 5, YE); blob(C, 1110, 640, 140, 8, CY); blob(C, 1000, 170, 80, 11, '#FFFFFF', 0.14); dots(C, 1150, 60, 4, 4, 18, '#FFFFFF', 0.5); }
}
function cover(C){ split(C, (C.o || {}).img === 'photo'); K5.coverText(C, 88, 180, 610, {tc:'#FFFFFF', sc:'#C9C9D3', kc:MINT, max:62, by:610}); }
function closing(C){ split(C, (C.o || {}).img === 'photo'); K5.closeText(C, 88, 190, 600, {tc:'#FFFFFF', sc:'#C9C9D3', mark:function(C, x, y, k, c){ blob(C, x + 18, y + 18, 19, k + 3, [LI, YE, CY][k % 3]); C.icon(x + 10, y + 10, 16, c.icon, CH); }}); }
K5.make({
id:'blobcollage', name:'Blob Collage', tag:'Charcoal · violet · organic colour blobs',
desc:'A charcoal and violet split cover with lime, sun-yellow and sky-cyan organic blobs that frame the photo; points sit on blob marks, cards are soft rounded tiles and a mint rule underlines titles. For proposals, agencies, product launches and creative pitches.',
fonts:{head:'Sora', body:'Sora', headW:700, bodyW:400, headLh:1.08, gf:'Sora:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:22, coverLbl:'Collage', closeLbl:'Collage',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; blob(C, W - 70, -14, 62, 5, VI); blob(C, W - 14, 18, 66, 3, YE, 0.95); blob(C, W + 4, 96, 40, 9, CY); dots(C, 26, 74, 1, 4, 18, VI, 0.5); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 52, 6, {fill:MINT, r:3}); return 18; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:PALE, r:22}); blob(C, x + w - 34, y + 30, 22, 7, LI); return {tc:CH, mc:MU, ac:VI}; }
if (o.hot) { C.rect(x, y, w, h, {fill:CH, r:22}); return {tc:'#FFFFFF', mc:'#C9C9D3', ac:LI, hot:1}; }
C.rect(x, y, w, h, {fill:PALE, r:22}); return {tc:CH, mc:MU, ac:LC[k % 4]};
},
mark:function(C, x, y, s, t, k, cc){ var c = LC[k % 4]; blob(C, x + s / 2, y + s / 2, s * 0.56, k + 2, c); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, c === VI ? '#FFFFFF' : CH); },
node:function(C, cx, cy, s, k){ var c = LC[k % 4]; blob(C, cx, cy, s * 0.58, k + 4, c); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, c === VI ? '#FFFFFF' : CH); },
track:function(C, x1, y, x2){ var d = []; for (var x = x1; x <= x2; x += 14) Q.circ(d, x, y, 2.6); K5.fill(C, d, CH, 0.45); },
conc:{kind:'pill', fill:LI, op:0.18, label:'Big idea', lc:'#3F7A12', tc:CH},
L:{cards:'Soft tiles', rows:'Blob rows', steps:'Blob steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function bubbles(C, Z, o){
K5.cols(C, Z, {align:'c', tg:20, ah:function(n, cw, Z){ return Math.min(Z.h * 0.5, cw * 0.62, 220); }, fallback:B.rows}, function(C, k, t, b){
var c = LC[k % 4], r = Math.min(b.ah, b.w) * 0.42, cy = b.y + b.ah / 2;
blob(C, b.cx + r * 0.8, cy - r * 0.7, r * 0.28, k + 20, LC[(k + 1) % 4], 0.9); blob(C, b.cx, cy, r, k + 9, c);
K.icon(C, b.cx - r * 0.38, cy - r * 0.38, r * 0.76, t, c === VI ? '#FFFFFF' : CH);
});
}
function blobstats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.55), tg:18, vMax:48, vw:function(cw, ah){ return Math.min(ah, cw) * 0.62; }}, function(C, k, t, b){
var c = LC[k % 4], r = Math.min(b.ah, b.w - 30) * 0.48, cy = b.y + b.ah / 2; blob(C, b.cx, cy, r, k + 30, c);
return {vc:c === VI ? '#FFFFFF' : CH, vy:cy - b.vs * 0.58};
});
}
return {p3:[['Blob bubbles', pg(bubbles)]], p4:[['Blob bubbles', pg(bubbles)]], stats:[['Blob numbers', pg(blobstats)]]};
}
});
})();
</script>
    <?php
}, 1181);
