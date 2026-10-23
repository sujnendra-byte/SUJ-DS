<?php
/**
 * Code Snippets export — DS Template 98 — Sector
 *
 * Snippet ID : 1140
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:28:50
 * Load order : 309 of 345
 */
/**
 * DS Template 98 — Sector
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl98_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl98_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('sector', array(
        'name'     => "Sector",
        'fonts'    => array('head' => "DM Sans", 'body' => "DM Sans"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#2D1E4F', 'accent' => '#5B2A86', 'accent2' => '#D6336C'),
        'voice'    => "Analytical and proportion-minded: who holds what share and why it matters. Crisp sentences with percentages and totals.",
        'recipe'   => "The whole picture, the slices (two to four points each), the shares in numbers, how the mix shifts over time and the allocation we recommend.",
        'generate' => 'ds_tpl98_generate',
        'build'    => 'ds_tpl98_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl98_generate') ) {
    function ds_tpl98_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl98_build') ) {
    function ds_tpl98_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-sector">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 98 - Sector : large pie sectors sweep out of a corner in plum, magenta and amber; each point owns a slice */
var DK = '#2D1E4F', PL = '#5B2A86', MG = '#D6336C', AM = '#F59F00', MU = '#655C7A', LN = '#E2DDEB', PALE = '#F1EDF6';
var P = {bg:'#FFFFFF', text:DK, title:DK, muted:MU, accent:PL, accent2:MG, card:'#F7F5FA', line:LN, onAccent:'#FFFFFF'};
var LC = [DK, PL, MG, AM];
function lc(k){ return LC[k % 4]; }
function sec(C, cx, cy, r0, r1, a0, a1, col, op){ K5.fill(C, Q.sector([], cx, cy, r0, r1, a0, a1), col, op); }
function fan(C, cx, cy, r0, r1, a0){ var sp = [0, 26, 56, 76, 90]; for (var i = 0; i < 4; i++) sec(C, cx, cy, r0, r1, a0 + sp[i], a0 + sp[i + 1], LC[i]); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
fan(C, W, H, photo ? 330 : 250, 640, 180);
if (photo) D.imgBox(C, W - 330, H - 330, 660, 660, {shape:'ell'});
K5.coverText(C, 88, 110, 640, {max:58, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
fan(C, W, 0, photo ? 300 : 230, 600, 90);
if (photo) D.imgBox(C, W - 300, -300, 600, 600, {shape:'ell'});
K5.closeText(C, 88, 200, 640, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:lc(k + 1)}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'sector', name:'Sector', tag:'White · plum pie sectors',
desc:'Large pie sectors in deep plum, magenta and amber sweep out of a corner, and every point owns one slice: a half-dial with labelled slices, share rings and progress pies. For market share, budgets, segment reviews and portfolio decks.',
fonts:{head:'DM Sans', body:'DM Sans', headW:700, bodyW:400, headLh:1.08, gf:'DM+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Corner sectors', closeLbl:'Corner sectors',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; fan(C, W, 0, 54, 128, 90); },
cover:cover, closing:closing,
rule:function(C, x, y){ for (var i = 0; i < 4; i++) C.rect(x + i * 18, y, 18, 6, {fill:LC[i]}); return 18; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? DK : '#F7F5FA'});
if (!o.big) sec(C, x + w, y + h, 0, 46, 180, 270, hot ? AM : lc(k + 1)); else C.rect(x, y, 8, h, {fill:MG});
return hot ? {tc:'#FFFFFF', mc:'#CFC6E2', ac:AM, hot:1} : {tc:DK, mc:MU, ac:PL};
},
mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.hot ? AM : lc(k + 1); C.ell(x, y, s, s, {fill:c}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, K.on(c)); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:PALE}); sec(C, x + s / 2, y + s / 2, 0, s / 2, -90, -90 + 90 * ((k % 4) + 1), lc(k + 1)); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:lc(k + 1)}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:PALE, r:3}); },
L:{cards:'Corner cards', rows:'Slice rows', steps:'Disc steps', stmt:'Summary slice', stats:'Share numbers'},
sig:function(pg, B){
function dial(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 760) return B.rows(C, Z, o);
var R = Math.min(Z.h * 0.48, 190), cx = Z.x, cy = Z.y + Z.h / 2, lx = Z.x + R + 120, tw = Z.x + Z.w - lx, rh = Z.h / n, st = 180 / n;
var F = K.fitHD(C, it, tw, 34, rh - 14, {hMax:22, dMax:16.5, hMin:12, dMin:10, noBoost:n > 4});
it.forEach(function(t, k){
var a0 = -90 + k * st, c = lc(k), yi = Z.y + rh * (k + 0.5), m = K5.pol(cx, cy, R + 6, a0 + st / 2), q = K5.pol(cx, cy, R * 0.72, a0 + st / 2), bh = K3.bH(C, t, tw, F);
sec(C, cx, cy, R * 0.4, R, a0 + 0.8, a0 + st - 0.8, c);
C.line(m[0], m[1], lx - 30, yi, {color:c, lw:2}); C.ell(lx - 38, yi - 8, 16, 16, {fill:c});
num(C, q[0] - 20, q[1] - 16, 40, 32, k, n > 4 ? 15 : 18, K.on(c));
K.hd(C, lx, yi - bh / 2, tw, rh - 6, t, F, {tc:DK, mc:MU});
});
}
function shares(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:18, vMax:40, vw:function(cw, ah){ return Math.min(cw * 0.42, ah * 0.5); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.4, b.ah * 0.48), cy = b.y + b.ah / 2, m = /^\s*([\d.]+)\s*%/.exec(b.val), pc = m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : 62 + (k * 11) % 30;
C.ell(b.cx - r, cy - r, r * 2, r * 2, {fill:PALE});
if (pc >= 99.5) C.ell(b.cx - r, cy - r, r * 2, r * 2, {fill:lc(k + 1)}); else sec(C, b.cx, cy, 0, r, -90, -90 + pc * 3.6, lc(k + 1));
C.ell(b.cx - r * 0.62, cy - r * 0.62, r * 1.24, r * 1.24, {fill:'#FFFFFF'});
return {vc:DK, vx:b.cx - r * 0.58, vw:r * 1.16, vy:cy - b.vs * 0.58};
});
}
function pies(C, Z, o){
K5.alt(C, Z, {same:true, at:0.22, off:60, fallback:B.rows, pre:function(C, g){ C.rect(g.x + g.cw / 2, g.y - 2, g.w - g.cw, 4, {fill:PALE}); }}, function(C, k, t, b){
C.ell(b.cx - 44, b.cy - 44, 88, 88, {fill:'#FFFFFF'}); C.ell(b.cx - 40, b.cy - 40, 80, 80, {fill:PALE});
if (k === b.n - 1) C.ell(b.cx - 40, b.cy - 40, 80, 80, {fill:lc(k + 1)}); else sec(C, b.cx, b.cy, 0, 40, -90, -90 + 360 * (k + 1) / b.n, lc(k + 1));
C.ell(b.cx - 17, b.cy - 17, 34, 34, {fill:'#FFFFFF'}); num(C, b.cx - 17, b.cy - 17, 34, 34, k, 13, DK, String(k + 1));
});
}
return {agenda:[['Half dial', pg(dial)]], p2:[['Half dial', pg(dial)]], p3:[['Half dial', pg(dial)]], p4:[['Half dial', pg(dial)]], p56:[['Half dial', pg(dial)]],
stats:[['Share rings', pg(shares)]], timeline:[['Progress pies', pg(pies)]]};
}
});
})();
</script>
    <?php
}, 1148);
