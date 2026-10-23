<?php
/**
 * Code Snippets export — DS Template 128 — Flight Path
 *
 * Snippet ID : 1170
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:30
 * Load order : 338 of 345
 */
/**
 * DS Template 128 — Flight Path
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl128_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl128_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('flightpath', array(
        'name'     => "Flight Path",
        'fonts'    => array('head' => "Josefin Sans", 'body' => "Open Sans"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0B2545', 'accent' => '#1F7A8C', 'accent2' => '#FF6F59'),
        'voice'    => "Well-travelled, optimistic and route-minded: where we start, the stops and the destination. Clear sentences with distances, dates and passenger or customer numbers.",
        'recipe'   => "Where we depart from, the stops on the route (two to four points each), the numbers, the itinerary and the destination we are booking.",
        'generate' => 'ds_tpl128_generate',
        'build'    => 'ds_tpl128_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl128_generate') ) {
    function ds_tpl128_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl128_build') ) {
    function ds_tpl128_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-flightpath">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 128 - Flight Path : dashed arcs curve between city dots like an airline route map */
var NV = '#0B2545', TE = '#1F7A8C', CO = '#FF6F59', SK = '#EAF4FF', MID = '#8DA9C4', MU = '#53647E', LN = '#CFE0F0';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:TE, accent2:CO, card:SK, line:LN, onAccent:'#FFFFFF'};
var LC = [TE, CO, NV, MID];
function qb(a, b, c, t){ return [(1 - t) * (1 - t) * a[0] + 2 * t * (1 - t) * b[0] + t * t * c[0], (1 - t) * (1 - t) * a[1] + 2 * t * (1 - t) * b[1] + t * t * c[1]]; }
function arc(C, a, b, c, col, lw, n){ var d = []; n = n || 40; for (var i = 0; i < n; i++) { var p = qb(a, b, c, i / n), q = qb(a, b, c, (i + 0.55) / n); Q.line(d, p[0], p[1], q[0], q[1]); } K5.stroke(C, d, col, lw); }
function plane(C, x, y, ang, s, col){ var c = Math.cos(ang), sn = Math.sin(ang), pts = [[1.1, 0], [-0.2, -0.16], [-0.5, -0.9], [-0.72, -0.9], [-0.5, -0.14], [-0.95, -0.1], [-1.12, -0.42], [-1.26, -0.42], [-1.16, 0], [-1.26, 0.42], [-1.12, 0.42], [-0.95, 0.1], [-0.5, 0.14], [-0.72, 0.9], [-0.5, 0.9], [-0.2, 0.16]]; K5.fill(C, Q.poly([], pts.map(function(p){ return [x + (p[0] * c - p[1] * sn) * s, y + (p[0] * sn + p[1] * c) * s]; })), col); }
function city(C, x, y, r, col){ C.ell(x - r - 4, y - r - 4, r * 2 + 8, r * 2 + 8, {fill:'#FFFFFF'}); C.ell(x - r, y - r, r * 2, r * 2, {fill:col || NV}); }
function onArc(C, a, b, c, t, s){ var p = qb(a, b, c, t), q = qb(a, b, c, t + 0.02); plane(C, p[0], p[1], Math.atan2(q[1] - p[1], q[0] - p[0]), s, CO); }
function cover(C){
var photo = (C.o || {}).img === 'photo', A = [710, 600], B1 = [860, 110], D1 = [1190, 200];
C.bg({fill:SK});
arc(C, A, B1, D1, TE, 4, 46); arc(C, [790, 210], [1060, 420], [1220, 600], TE, 4, 40); arc(C, A, [960, 720], [1220, 600], MID, 4, 40);
city(C, 710, 600, 12); city(C, 790, 210, 10); city(C, 1220, 600, 12);
if (photo) { C.ell(1046, 56, 288, 288, {fill:'#FFFFFF', line:CO, lw:4}); D.imgBox(C, 1060, 70, 260, 260, {shape:'ell'}); } else { C.ell(1166, 176, 48, 48, {line:CO, lw:3}); city(C, 1190, 200, 14, CO); }
onArc(C, A, B1, D1, 0.52, 26);
K5.coverText(C, 88, 130, 580, {max:54, by:650});
}
function closing(C){
var photo = (C.o || {}).img === 'photo', A = [700, 560], B1 = [940, 60], D1 = [1210, 520];
C.bg({fill:SK}); arc(C, A, B1, D1, TE, 4, 50); city(C, A[0], A[1], 12); C.ell(D1[0] - 24, D1[1] - 24, 48, 48, {line:CO, lw:3}); city(C, D1[0], D1[1], 14, CO); onArc(C, A, B1, D1, 0.44, 26);
if (photo) { C.ell(826, 316, 268, 268, {fill:'#FFFFFF', line:TE, lw:4}); D.imgBox(C, 840, 330, 240, 240, {shape:'ell'}); }
K5.closeText(C, 88, 150, 580, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:k === 0 ? CO : NV}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'flightpath', name:'Flight Path', tag:'Sky · dashed route arcs',
desc:'Dashed arcs curve between city dots like an airline route map, with a small aircraft on the way and a coral destination pin: stops along one arc, itinerary rows and departure-board numbers. For travel, aviation, expansion plans and customer journeys.',
fonts:{head:'Josefin Sans', body:'Open Sans', headW:700, bodyW:400, headLh:1.1, gf:'Josefin+Sans:wght@400;500;600;700|Open+Sans:wght@400;500;600;700'},
P:P, LC:LC, cardR:16, imgR:16, coverLbl:'Route map', closeLbl:'Route map',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; var A = [W - 250, 90], B1 = [W - 150, -20], D1 = [W - 30, 60]; arc(C, A, B1, D1, MID, 2.5, 16); city(C, A[0], A[1], 5); city(C, D1[0], D1[1], 6, CO); onArc(C, A, B1, D1, 0.48, 12); },
cover:cover, closing:closing,
rule:function(C, x, y){ arc(C, [x, y + 8], [x + 45, y - 8], [x + 90, y + 8], TE, 2.5, 9); C.ell(x + 85, y + 3, 10, 10, {fill:CO}); return 26; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : SK, r:16}); if (!o.big) plane(C, x + w - 40, y + 34, -0.5, 13, hot ? CO : TE);
return hot ? {tc:'#FFFFFF', mc:'#C5D3E6', ac:CO, hot:1} : {tc:NV, mc:MU, ac:TE};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? CO : NV}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:k === 0 ? CO : NV}); num(C, x, y, s, s, k, s * 0.38, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ city(C, cx, cy, s / 2, k === 0 ? CO : NV); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:TE, lw:3, dash:true}); },
conc:{kind:'pill', fill:TE, op:0.12, label:'Destination', lc:TE, tc:NV},
L:{cards:'Lounge cards', rows:'Stop rows', steps:'Stopover steps', stmt:'Travel note', stats:'Plain numbers'},
sig:function(pg, B){
function route(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return B.rows(C, Z, o);
var cw = Z.w / n, A = [Z.x + cw / 2, Z.y + Z.h * 0.44], D1 = [Z.x + Z.w - cw / 2, Z.y + Z.h * 0.44], B1 = [Z.x + Z.w / 2, Z.y - Z.h * 0.3], tw = cw - 22;
var F = K.fitHD(C, it, tw, 44, Z.h * 0.56 - 44, {hMax:21, dMax:16, hMin:12, dMin:10, noBoost:n > 3});
arc(C, A, B1, D1, TE, 4, 56);
it.forEach(function(t, k){
var cx = Z.x + cw * (k + 0.5), p = qb(A, B1, D1, n > 1 ? k / (n - 1) : 0.5), last = k === n - 1;
C.line(cx, p[1] + 26, cx, Z.y + Z.h * 0.44 + 22, {color:MID, lw:1.5, dash:true});
if (last) C.ell(p[0] - 36, p[1] - 36, 72, 72, {line:CO, lw:3}); city(C, p[0], p[1], 23, last ? CO : NV); num(C, p[0] - 23, p[1] - 23, 46, 46, k, 17, '#FFFFFF');
K.hd(C, cx - tw / 2, Z.y + Z.h * 0.44 + 34, tw, Z.h * 0.56 - 38, t, F, {tc:NV, mc:MU, align:'c'});
});
onArc(C, A, B1, D1, n > 2 ? 0.5 + 0.5 / (n - 1) * (n % 2 ? 1 : 0) : 0.5, 20);
}
function board(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.48), tg:22, vMax:52, vw:function(cw){ return cw - 80; }}, function(C, k, t, b){
C.rect(b.x + 10, b.y, b.w - 20, b.ah, {fill:NV, r:14}); C.rect(b.x + 10, b.y + b.ah / 2 - 1, b.w - 20, 2, {fill:'#000000', op:0.35});
for (var i = 0; i < 3; i++) C.ell(b.x + 30 + i * 18, b.y + 20, 10, 10, {fill:i === k % 3 ? CO : '#3A5578'}); plane(C, b.x + b.w - 44, b.y + 26, 0, 11, MID);
return {vc:'#FFFFFF', vy:b.y + 14 + (b.ah - 14 - b.vs * 1.15) / 2};
});
}
function trip(C, Z, o){
K5.list(C, Z, {aw:88, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
var s = Math.min(22, b.h * 0.28); if (!b.last) C.line(b.x + 32, b.cy + s + 6, b.x + 32, b.cy + b.h + 8 - s - 6, {color:TE, lw:3, dash:true});
city(C, b.x + 32, b.cy, s, k === 0 ? CO : NV); num(C, b.x + 32 - s, b.cy - s, s * 2, s * 2, k, s * 0.74, '#FFFFFF');
});
}
return {agenda:[['Itinerary', pg(trip)]], p2:[['Route arc', pg(route)]], p3:[['Route arc', pg(route)]], p4:[['Route arc', pg(route)]], p56:[['Itinerary', pg(trip)], ['Route arc', pg(route)]], p78:[['Itinerary', pg(trip)]],
stats:[['Departure board', pg(board)]], timeline:[['Route arc', pg(route)]]};
}
});
})();
</script>
    <?php
}, 1178);
