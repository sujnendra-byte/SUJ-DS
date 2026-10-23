<?php
/**
 * Code Snippets export — DS Template 122 — Tangram
 *
 * Snippet ID : 1164
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:24
 * Load order : 332 of 345
 */
/**
 * DS Template 122 — Tangram
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl122_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl122_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('tangram', array(
        'name'     => "Tangram",
        'fonts'    => array('head' => "Readex Pro", 'body' => "Readex Pro"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#073B4C', 'accent' => '#118AB2', 'accent2' => '#EF476F'),
        'voice'    => "Inventive, playful and constructive: the same pieces arranged a better way. Bright, clear sentences with concrete examples and figures.",
        'recipe'   => "The challenge, the pieces we have (two to four points each), the numbers, how we rearrange them and the first move.",
        'generate' => 'ds_tpl122_generate',
        'build'    => 'ds_tpl122_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl122_generate') ) {
    function ds_tpl122_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl122_build') ) {
    function ds_tpl122_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-tangram">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 122 - Tangram : the seven tangram shapes, pulled slightly apart; each point takes one piece as its marker */
var INK = '#073B4C', PK = '#EF476F', YL = '#FFD166', GN = '#06D6A0', BL = '#118AB2', MU = '#4F6B75', LN = '#D6E3E8', PALE = '#F4F8FA';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:BL, accent2:PK, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [PK, YL, GN, BL, INK];
function lc(k){ return LC[k % 5]; }
var TG = [[[0, 0], [4, 0], [2, 2]], [[0, 0], [0, 4], [2, 2]], [[4, 4], [2, 4], [4, 2]], [[4, 0], [4, 2], [3, 1]], [[2, 2], [3, 3], [1, 3]], [[2, 2], [3, 1], [4, 2], [3, 3]], [[0, 4], [2, 4], [3, 3], [1, 3]]], TC = [BL, INK, PK, YL, GN, YL, PK];
function tang(C, i, x, y, s, col, ex){ var p = TG[i], mx = 0, my = 0; p.forEach(function(v){ mx += v[0]; my += v[1]; }); mx = mx / p.length - 2; my = my / p.length - 2; K5.fill(C, Q.poly([], p.map(function(v){ return [x + v[0] * s + mx * (ex || 0), y + v[1] * s + my * (ex || 0)]; })), col); }
var SH = [[[0, 1], [0.5, 0], [1, 1]], [[0.5, 0], [1, 0.5], [0.5, 1], [0, 0.5]], [[0, 1], [0.34, 0.2], [1, 0.2], [0.66, 1]], [[0, 0], [1, 0.5], [0, 1]], [[0, 0], [1, 0], [1, 1]]];
function piece(C, x, y, s, k, col){ K5.fill(C, Q.poly([], SH[k % 5].map(function(v){ return [x + v[0] * s, y + v[1] * s]; })), col); }
var IC = [[0.5, 0.64], [0.5, 0.5], [0.5, 0.6], [0.36, 0.5], [0.66, 0.36]];
function cover(C){
var photo = (C.o || {}).img === 'photo', i;
if (photo) { D.imgBox(C, 740, 120, 440, 440, {r:0}); tang(C, 0, 740, 120, 110, BL, 0); tang(C, 1, 740, 120, 110, INK, 0); tang(C, 4, 740, 120, 110, GN, 0); }
else for (i = 0; i < 7; i++) tang(C, i, 740, 120, 110, TC[i], 12);
K5.coverText(C, 88, 130, 560, {max:54, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo', i;
if (photo) { D.imgBox(C, 780, 150, 400, 400, {r:0}); tang(C, 2, 780, 150, 100, PK, 0); tang(C, 3, 780, 150, 100, YL, 0); tang(C, 6, 780, 150, 100, PK, 0); }
else for (i = 0; i < 7; i++) tang(C, i, 780, 150, 100, TC[i], 30);
K5.closeText(C, 88, 150, 600, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:lc(k === 1 ? 3 : k)}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'tangram', name:'Tangram', tag:'White · seven-piece puzzle',
desc:'The seven tangram shapes in pink, yellow, green, blue and deep teal, pulled slightly apart on the cover; every point takes one piece as its marker, with diamond numbers and an arrow of pieces. For creative strategy, innovation, workshops and education.',
fonts:{head:'Readex Pro', body:'Readex Pro', headW:600, bodyW:400, headLh:1.1, gf:'Readex+Pro:wght@300;400;500;600;700'},
P:P, LC:LC, cardR:0, coverLbl:'Seven pieces', closeLbl:'Seven pieces',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; K5.fill(C, Q.poly([], [[W, 0], [W - 96, 0], [W, 96]]), BL); K5.fill(C, Q.poly([], [[W - 104, 0], [W - 152, 0], [W - 104, 48]]), YL); K5.fill(C, Q.poly([], [[W, 104], [W, 152], [W - 48, 104]]), PK); },
cover:cover, closing:closing,
rule:function(C, x, y){ piece(C, x, y - 4, 16, 0, PK); piece(C, x + 20, y - 4, 16, 1, YL); piece(C, x + 40, y - 4, 16, 2, GN); piece(C, x + 60, y - 4, 16, 3, BL); return 26; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? INK : PALE}); if (!o.big) K5.fill(C, Q.poly([], [[x + w, y], [x + w - 54, y], [x + w, y + 54]]), hot ? YL : lc(k)); else C.rect(x, y, 8, h, {fill:PK});
return hot ? {tc:'#FFFFFF', mc:'#BFD3DA', ac:YL, hot:1} : {tc:INK, mc:MU, ac:BL};
},
mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.hot ? YL : lc(k); C.rect(x, y, s, s, {fill:c}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, K.on(c)); },
rowMark:function(C, x, y, s, t, k){ var c = lc(k); piece(C, x - s * 0.1, y - s * 0.1, s * 1.2, 1, c); num(C, x, y, s, s, k, s * 0.34, K.on(c)); },
node:function(C, cx, cy, s, k){ var c = lc(k); piece(C, cx - s * 0.65, cy - s * 0.65, s * 1.3, 1, c); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, K.on(c)); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:LN}); },
L:{cards:'Corner cards', rows:'Diamond rows', steps:'Diamond steps', stmt:'Seven-piece note', stats:'Plain numbers'},
sig:function(pg, B){
function shapes(C, Z, o){
K5.cols(C, Z, {max:5, ah:150, gap:26, tg:16, fallback:B.rows}, function(C, k, t, b){
var c = lc(k), s = 128, q = IC[k % 5]; piece(C, b.x, b.y, s, k, c); K.icon(C, b.x + s * q[0] - 19, b.y + s * q[1] - 19, 38, t, K.on(c));
C.text(b.x + s + 12, b.y + 66, 80, 60, pad2(k + 1), {font:'head', size:38, wt:600, color:c === YL ? '#C99A1A' : c, lh:1, va:'b'}); C.rect(b.x, b.y + 140, b.w, 2, {fill:LN});
});
}
function diamonds(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:16, vMax:38, vw:function(cw, ah){ return Math.min(cw * 0.5, ah * 0.56); }}, function(C, k, t, b){
var s = Math.min(b.w - 20, b.ah), c = [BL, PK, GN, INK][k]; piece(C, b.cx - s / 2, b.y + (b.ah - s) / 2, s, 1, c); K5.fill(C, Q.poly([], [[b.cx - s / 2, b.y + b.ah / 2], [b.cx, b.y + (b.ah - s) / 2], [b.cx, b.y + b.ah / 2]]), '#FFFFFF', 0.16);
return {vc:c === GN ? INK : '#FFFFFF', vx:b.cx - s * 0.3, vw:s * 0.6, vy:b.y + b.ah / 2 - b.vs * 0.58};
});
}
function arrow(C, Z, o){
K5.alt(C, Z, {same:true, at:0.24, off:68, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 2, g.w, 4, {fill:LN}); }}, function(C, k, t, b){
var c = lc(k), w = Math.min(b.cw - 16, 150); K5.fill(C, Q.poly([], [[b.cx - w / 2, b.cy - 44], [b.cx + w / 2, b.cy], [b.cx - w / 2, b.cy + 44]]), c); num(C, b.cx - w / 2, b.cy - 22, w * 0.6, 44, k, 19, K.on(c));
});
}
return {p2:[['Piece columns', pg(shapes)]], p3:[['Piece columns', pg(shapes)]], p4:[['Piece columns', pg(shapes)]], stats:[['Diamond numbers', pg(diamonds)]], timeline:[['Arrow of pieces', pg(arrow)]]};
}
});
})();
</script>
    <?php
}, 1172);
