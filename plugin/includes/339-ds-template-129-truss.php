<?php
/**
 * Code Snippets export — DS Template 129 — Truss
 *
 * Snippet ID : 1171
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:31
 * Load order : 339 of 345
 */
/**
 * DS Template 129 — Truss
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl129_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl129_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('truss', array(
        'name'     => "Truss",
        'fonts'    => array('head' => "Chivo", 'body' => "Chivo"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0F172A', 'accent' => '#334155', 'accent2' => '#F97316'),
        'voice'    => "Engineered, safe and load-bearing: spans, capacities and tolerances. Firm sentences with tonnes, metres, dates and budgets.",
        'recipe'   => "The gap to bridge, the structural members of the plan (two to four points each), the load figures, the construction sequence and the sign-off we need.",
        'generate' => 'ds_tpl129_generate',
        'build'    => 'ds_tpl129_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl129_generate') ) {
    function ds_tpl129_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl129_build') ) {
    function ds_tpl129_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-truss">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 129 - Truss : a steel bridge truss of chords, posts and diagonals with bolted nodes, one member in safety orange */
var INK = '#0F172A', ST = '#334155', OR = '#F97316', GY = '#64748B', MU = '#56617A', LN = '#D5DCE6', PALE = '#F1F5F9';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:ST, accent2:OR, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [ST, OR, GY, INK];
function truss(C, x0, x1, yt, yb, k, lw, o){
o = o || {}; var bw = (x1 - x0) / k, ch = [], wb = [], nd = [], i;
Q.line(ch, x0, yt, x1, yt); Q.line(ch, x0, yb, x1, yb);
for (i = 0; i <= k; i++) { Q.line(wb, x0 + i * bw, yt, x0 + i * bw, yb); Q.circ(nd, x0 + i * bw, yt, lw * 0.9); Q.circ(nd, x0 + i * bw, yb, lw * 0.9); }
for (i = 0; i < k; i++) if (i !== o.hot) Q.line(wb, x0 + i * bw, i % 2 ? yt : yb, x0 + (i + 1) * bw, i % 2 ? yb : yt);
K5.stroke(C, wb, o.col || ST, lw * 0.6); K5.stroke(C, ch, o.col || ST, lw);
if (o.hot != null) C.line(x0 + o.hot * bw, o.hot % 2 ? yt : yb, x0 + (o.hot + 1) * bw, o.hot % 2 ? yb : yt, {color:OR, lw:lw * 0.75});
K5.fill(C, nd, o.col || ST);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 486, W, 234, {fill:PALE}); C.rect(0, 480, W, 8, {fill:LN});
if (photo) D.imgBox(C, 790, 70, 410, 230, {r:0});
truss(C, 760, 1330, 330, 470, 4, 10, {hot:1}); C.rect(744, 470, 32, 250, {fill:ST}); C.rect(730, 462, 60, 16, {fill:INK});
K5.coverText(C, 88, 110, 610, {max:56, by:650});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 656, W, 64, {fill:PALE}); truss(C, -40, 1320, 520, 640, 8, 9, {hot:5}); C.rect(280, 640, 28, 80, {fill:ST}); C.rect(972, 640, 28, 80, {fill:ST});
if (photo) D.imgBox(C, 800, 80, 392, 380, {r:0});
K5.closeText(C, 88, 80, 660, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:k === 0 ? OR : ST}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'truss', name:'Truss', tag:'White · steel bridge truss',
desc:'A steel bridge truss of chords, posts and diagonals with bolted nodes, one member picked out in safety orange: points sit on the truss nodes, cards carry a girder edge and numbers are riveted plates. For civil engineering, infrastructure, rail and bridging-the-gap plans.',
fonts:{head:'Chivo', body:'Chivo', headW:700, bodyW:400, headLh:1.08, gf:'Chivo:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Bridge', closeLbl:'Bridge',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; truss(C, W - 260, W + 20, 22, 62, 5, 3.5, {hot:1 + C.i % 3, col:GY}); },
cover:cover, closing:closing,
rule:function(C, x, y){ truss(C, x, x + 96, y - 2, y + 12, 4, 2.2, {hot:1}); return 28; },
pad:28,
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? ST : PALE}); if (!o.big) truss(C, x + 5, x + w - 5, y + 5, y + 21, Math.max(4, Math.round(w / 34)), 2.4, {hot:k % 3, col:hot ? '#FFFFFF' : ST}); else C.rect(x, y, 8, h, {fill:OR});
return hot ? {tc:'#FFFFFF', mc:'#CBD3DF', ac:OR, hot:1} : {tc:INK, mc:MU, ac:ST};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? OR : ST}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:'#FFFFFF', line:ST, lw:s * 0.14}); C.ell(x + s * 0.36, y + s * 0.36, s * 0.28, s * 0.28, {fill:OR}); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:k === 0 ? OR : ST}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ truss(C, x1, x2, y - 9, y + 9, Math.max(6, Math.round((x2 - x1) / 40)), 2.4, {col:GY}); },
conc:{kind:'box', fill:PALE, r:0, label:'Load-bearing point', lc:'#C2410C', tc:INK},
L:{cards:'Girder cards', rows:'Span rows', steps:'Bolt steps', stmt:'Site note', stats:'Plain numbers'},
sig:function(pg, B){
function span(C, Z, o){
K5.alt(C, Z, {off:84, fallback:B.rows, pre:function(C, g){ truss(C, g.x, g.x + g.w, g.y - 40, g.y + 40, g.n * 2, 6, {hot:null}); }}, function(C, k, t, b){
var y = b.cy + (b.up ? -40 : 40); C.ell(b.cx - 26, y - 26, 52, 52, {fill:k === b.n - 1 ? OR : ST, line:'#FFFFFF', lw:4}); num(C, b.cx - 26, y - 26, 52, 52, k, 18, '#FFFFFF');
});
}
function plates(C, Z, o){
K5.stat(C, Z, {ah:Math.min(200, Z.h * 0.5), tg:20, vMax:52, vw:function(cw){ return cw - 90; }}, function(C, k, t, b){
var w = b.w - 20, d = [], n = Math.max(4, Math.round(w / 40));
C.rect(b.x + 10, b.y, w, b.ah, {fill:k === 0 ? OR : ST}); C.rect(b.x + 10, b.y, w, 26, {fill:'#000000', op:0.2}); C.rect(b.x + 10, b.y + b.ah - 26, w, 26, {fill:'#000000', op:0.2});
for (var i = 0; i < n; i++) { Q.circ(d, b.x + 10 + w * (i + 0.5) / n, b.y + 13, 5); Q.circ(d, b.x + 10 + w * (i + 0.5) / n, b.y + b.ah - 13, 5); }
K5.fill(C, d, '#FFFFFF', 0.55);
return {vc:'#FFFFFF', vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function posts(C, Z, o){
K5.list(C, Z, {aw:92, pr:16, gap:8, rhMax:98, pre:function(C, g){ for (var c = 0; c < g.cl; c++) { var x = g.x + c * (g.cw + g.gx); K5.stroke(C, Q.line(Q.line([], x + 18, g.y, x + 18, g.y + g.h), x + 46, g.y, x + 46, g.y + g.h), ST, 4); } }}, function(C, k, t, b){
C.line(b.x + 18, b.y, b.x + 46, b.y + b.h, {color:k === 1 ? OR : GY, lw:2.5}); C.rect(b.x + 12, b.cy - 20, 40, 40, {fill:k === 1 ? OR : ST}); num(C, b.x + 12, b.cy - 20, 40, 40, k, 15, '#FFFFFF');
});
}
return {agenda:[['Tower list', pg(posts)]], p3:[['Truss span', pg(span)]], p4:[['Truss span', pg(span)]], p56:[['Tower list', pg(posts)], ['Truss span', pg(span)]], p78:[['Tower list', pg(posts)]],
stats:[['Riveted plates', pg(plates)]], timeline:[['Truss span', pg(span)]]};
}
});
})();
</script>
    <?php
}, 1179);
