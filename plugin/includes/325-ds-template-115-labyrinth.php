<?php
/**
 * Code Snippets export — DS Template 115 — Labyrinth
 *
 * Snippet ID : 1157
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:16
 * Load order : 325 of 345
 */
/**
 * DS Template 115 — Labyrinth
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl115_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl115_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('labyrinth', array(
        'name'     => "Labyrinth",
        'fonts'    => array('head' => "Onest", 'body' => "Onest"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1F2937', 'accent' => '#1F2937', 'accent2' => '#F59E0B'),
        'voice'    => "Clear-headed and methodical: a complicated problem made navigable step by step. Calm, exact sentences with rules, risks and deadlines.",
        'recipe'   => "The maze we face, the walls and the openings (two to four points each), the numbers, the route through and the exit we recommend.",
        'generate' => 'ds_tpl115_generate',
        'build'    => 'ds_tpl115_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl115_generate') ) {
    function ds_tpl115_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl115_build') ) {
    function ds_tpl115_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-labyrinth">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 115 - Labyrinth : thick right-angle maze walls with one amber route threading through the openings */
var INK = '#1F2937', AM = '#F59E0B', GY = '#6B7280', MU = '#5B6676', LN = '#DADDE2', PALE = '#F5F6F8';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:INK, accent2:AM, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [INK, AM, GY, '#B45309'];
function sp(cx, cy, st, n){ var p = [[cx, cy]], x = cx, y = cy, dr = [[1, 0], [0, 1], [-1, 0], [0, -1]]; for (var i = 0; i < n; i++) { var L = (Math.floor(i / 2) + 1) * st; x += dr[i % 4][0] * L; y += dr[i % 4][1] * L; p.push([x, y]); } return p; }
function maze(C, cx, cy, st, n, lw){ K5.stroke(C, Q.pts([], sp(cx, cy, st, n)), INK, lw); K5.stroke(C, Q.pts([], sp(cx + st / 2, cy + st / 2, st, n - 3)), AM, Math.max(3, lw * 0.3)); C.ell(cx + st / 2 - lw * 0.6, cy + st / 2 - lw * 0.6, lw * 1.2, lw * 1.2, {fill:AM}); }
function room(C, x, y, w, h, lw, dy, col){ var a = dy - 24, b = dy + 24; K5.stroke(C, Q.pts(Q.pts([], [[x + w, y + b], [x + w, y + h], [x, y + h], [x, y + b]]), [[x, y + a], [x, y], [x + w, y], [x + w, y + a]]), col || INK, lw); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 760, 130, 420, 460, {r:0}); room(C, 730, 100, 480, 520, 12, 260); C.line(690, 360, 1250, 360, {color:AM, lw:4, dash:true}); }
else maze(C, 950, 340, 54, 15, 15);
K5.coverText(C, 88, 130, 560, {max:56, by:644, rule:function(C, x, y){ K5.stroke(C, Q.pts([], [[x, y], [x + 50, y], [x + 50, y + 12], [x + 100, y + 12]]), AM, 4); }, ruleH:36});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 800, 150, 380, 420, {r:0}); room(C, 770, 120, 440, 480, 12, 240); } else maze(C, 980, 360, 38, 11, 12);
K5.closeText(C, 88, 150, 600, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:k === 0 ? AM : INK}); C.icon(x + 10, y + 10, 16, c.icon, k === 0 ? INK : '#FFFFFF'); }});
}
K5.make({
id:'labyrinth', name:'Labyrinth', tag:'White · maze walls',
desc:'Thick right-angle maze walls in dark ink with one amber route threading through the openings: every card is a room with a door, and the route leads from the entrance to the answer. For problem solving, compliance, regulation and change programmes.',
fonts:{head:'Onest', body:'Onest', headW:700, bodyW:400, headLh:1.08, gf:'Onest:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Maze', closeLbl:'Maze',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; K5.stroke(C, Q.pts([], [[W - 170, -6], [W - 170, 34], [W - 110, 34], [W - 110, 74], [W - 50, 74], [W - 50, 34], [W + 6, 34]]), INK, 8); C.ell(W - 88, 6, 16, 16, {fill:AM}); },
cover:cover, closing:closing,
rule:function(C, x, y){ K5.stroke(C, Q.pts([], [[x, y], [x + 34, y], [x + 34, y + 10], [x + 68, y + 10]]), AM, 4); return 26; },
pad:28,
card:function(C, x, y, w, h, k, o){
var hot = o.hot; if (o.big) { C.rect(x, y, w, h, {fill:PALE}); C.rect(x, y, 8, h, {fill:AM}); return {tc:INK, mc:MU, ac:INK}; }
if (hot) C.rect(x, y, w, h, {fill:INK}); room(C, x + 2, y + 2, w - 4, h - 4, 4, h * 0.5, hot ? AM : INK);
return hot ? {tc:'#FFFFFF', mc:'#C9CED6', ac:AM, hot:1} : {tc:INK, mc:MU, ac:INK};
},
mark:function(C, x, y, s, t, k, cc){ C.rect(x, y, s, s, {fill:AM}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, INK); },
rowMark:function(C, x, y, s, t, k){ K5.stroke(C, Q.pts([], [[x + s * 0.7, y], [x, y], [x, y + s], [x + s * 0.7, y + s]]), INK, 4); num(C, x + 4, y, s, s, k, s * 0.42, k === 0 ? '#B45309' : INK); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:INK}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, AM); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:AM, lw:4, dash:true}); },
conc:{kind:'box', fill:PALE, r:0, label:'Way out', lc:'#B45309', tc:INK},
L:{cards:'Room cards', rows:'Corridor rows', steps:'Square steps', stmt:'Way-out note', stats:'Plain numbers'},
sig:function(pg, B){
function rooms(C, Z, o){
var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var g = 22, cw = (Z.w - g * (n - 1)) / n, tw = cw - 52, dy = 52;
var F = K.fitHD(C, it, tw, 50, Z.h - 130, {hMax:22, dMax:16, hMin:12, dMin:10});
C.line(Z.x - 30, Z.y + dy, Z.x + Z.w + 30, Z.y + dy, {color:AM, lw:4, dash:true});
it.forEach(function(t, k){
var x = Z.x + k * (cw + g); room(C, x, Z.y, cw, Z.h, 5, dy);
C.rect(x + 26, Z.y + dy - 24, 48, 48, {fill:INK}); num(C, x + 26, Z.y + dy - 24, 48, 48, k, 18, AM); C.rect(x + cw - 76, Z.y + dy - 24, 50, 48, {fill:'#FFFFFF'}); K.icon(C, x + cw - 68, Z.y + dy - 17, 34, t, INK);
K.hd(C, x + 26, Z.y + 110, tw, Z.h - 130, t, F, {tc:INK, mc:MU});
});
}
function vaults(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:20, vMax:42, vw:function(cw, ah){ return Math.min(cw - 130, ah * 0.9); }}, function(C, k, t, b){
var s = Math.min(b.w - 36, b.ah), x = b.cx - s / 2;
K5.stroke(C, Q.pts([], [[x + s * 0.62, b.y], [x + s, b.y], [x + s, b.y + s], [x, b.y + s], [x, b.y], [x + s * 0.38, b.y]]), INK, 6);
K5.stroke(C, Q.pts([], [[x + s * 0.38, b.y + s - 22], [x + 22, b.y + s - 22], [x + 22, b.y + 22], [x + s - 22, b.y + 22], [x + s - 22, b.y + s - 22], [x + s * 0.62, b.y + s - 22]]), GY, 4);
C.line(b.cx, b.y - 8, b.cx, b.y + 30, {color:AM, lw:4, dash:true});
return {vc:INK, vx:x + 34, vw:s - 68, vy:b.y + (s - b.vs * 1.15) / 2};
});
}
function route(C, Z, o){
K5.alt(C, Z, {off:78, fallback:B.rows, pre:function(C, g){ var p = []; for (var k = 0; k < g.n; k++) { var yy = g.y + (k % 2 === 0 ? 30 : -30); p.push([g.x + g.cw * k, yy], [g.x + g.cw * (k + 1), yy]); } K5.stroke(C, Q.pts([], p), AM, 5); }}, function(C, k, t, b){
var y = b.cy + (b.up ? 30 : -30); C.rect(b.cx - 26, y - 26, 52, 52, {fill:INK}); num(C, b.cx - 26, y - 26, 52, 52, k, 19, AM);
});
}
return {p2:[['Rooms and doors', pg(rooms)]], p3:[['Rooms and doors', pg(rooms)]], p4:[['Rooms and doors', pg(rooms)]], stats:[['Vault numbers', pg(vaults)]], timeline:[['Amber route', pg(route)]]};
}
});
})();
</script>
    <?php
}, 1165);
