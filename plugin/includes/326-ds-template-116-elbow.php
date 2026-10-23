<?php
/**
 * Code Snippets export — DS Template 116 — Elbow
 *
 * Snippet ID : 1158
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:18
 * Load order : 326 of 345
 */
/**
 * DS Template 116 — Elbow
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl116_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl116_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('elbow', array(
        'name'     => "Elbow",
        'fonts'    => array('head' => "Familjen Grotesk", 'body' => "Familjen Grotesk"),
        'pal'      => array('bg' => '#F5F5F5', 'text' => '#111111', 'accent' => '#111111', 'accent2' => '#FFD60A'),
        'voice'    => "Operational and flow-minded: inputs, stages, pressure and output. Direct sentences with throughput, uptime and cost figures.",
        'recipe'   => "The system today, the flow stage by stage (two to four points each), the gauges in numbers, the pipeline of work and the output we commit to.",
        'generate' => 'ds_tpl116_generate',
        'build'    => 'ds_tpl116_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl116_generate') ) {
    function ds_tpl116_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl116_build') ) {
    function ds_tpl116_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-elbow">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 116 - Elbow : thick rounded pipes run in from the edge and turn through smooth 90-degree bends */
var BK = '#111111', YL = '#FFD60A', GY = '#7A7A7A', MU = '#5C5C5C', LN = '#DCDCDC', BG = '#F5F5F5';
var P = {bg:BG, text:BK, title:BK, muted:MU, accent:BK, accent2:YL, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [BK, YL, GY, '#F5B700'];
function pipe(C, pts, r, col, lw){
var d = [['M', pts[0][0], pts[0][1]]];
for (var i = 1; i < pts.length - 1; i++) { var p0 = pts[i - 1], p1 = pts[i], p2 = pts[i + 1], l1 = Math.hypot(p1[0] - p0[0], p1[1] - p0[1]), l2 = Math.hypot(p2[0] - p1[0], p2[1] - p1[1]), rr = Math.min(r, l1 / 2, l2 / 2);
d.push(['L', p1[0] + (p0[0] - p1[0]) * rr / l1, p1[1] + (p0[1] - p1[1]) * rr / l1]); d.push(['Q', p1[0], p1[1], p1[0] + (p2[0] - p1[0]) * rr / l2, p1[1] + (p2[1] - p1[1]) * rr / l2]); }
d.push(['L', pts[pts.length - 1][0], pts[pts.length - 1][1]]); K5.stroke(C, d, col, lw);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:BK});
if (photo) D.imgBox(C, 860, 0, 420, H, {r:0});
pipe(C, [[W + 60, 150], [1000, 150], [1000, H + 60]], 100, YL, 62); pipe(C, [[W + 60, 340], [1150, 340], [1150, H + 60]], 80, '#FFFFFF', 62); pipe(C, [[850, H + 60], [850, 610], [690, 610]], 60, '#3A3A3A', 46);
K5.coverText(C, 88, 120, 640, {tc:'#FFFFFF', sc:'#CFCFCF', kc:YL, max:58, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:BK});
if (photo) D.imgBox(C, 830, 70, 362, 330, {r:16});
pipe(C, [[-60, 600], [860, 600], [860, H + 60]], 100, YL, 56); pipe(C, [[W + 60, 480], [1040, 480], [1040, H + 60]], 80, '#FFFFFF', 56);
K5.closeText(C, 88, 90, 660, {tc:'#FFFFFF', sc:'#CFCFCF', mc:YL, mi:BK});
}
K5.make({
id:'elbow', name:'Elbow', tag:'Black · yellow pipe bends',
desc:'Thick rounded pipes run in from the edge and turn through smooth 90-degree bends, yellow and white on a black cover and black and yellow on light pages, with a stepped flow joined by elbow connectors. For process flows, utilities, plant and operations.',
fonts:{head:'Familjen Grotesk', body:'Familjen Grotesk', headW:700, bodyW:400, headLh:1.06, gf:'Familjen+Grotesk:wght@400;500;600;700'},
P:P, LC:LC, cardR:16, imgR:16, coverLbl:'Pipe bends', closeLbl:'Pipe bends',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; pipe(C, [[W + 30, 78], [W - 130, 78], [W - 130, -30]], 34, BK, 16); pipe(C, [[W + 30, 42], [W - 94, 42], [W - 94, -30]], 26, YL, 16); },
cover:cover, closing:closing,
rule:function(C, x, y){ pipe(C, [[x + 3, y - 2], [x + 60, y - 2], [x + 60, y + 14]], 10, YL, 6); return 26; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? BK : '#FFFFFF', r:16});
if (!o.big) pipe(C, [[x + w - 84, y + 18], [x + w - 18, y + 18], [x + w - 18, y + 84]], 26, YL, 10);
return hot ? {tc:'#FFFFFF', mc:'#CFCFCF', ac:YL, hot:1} : {tc:BK, mc:MU, ac:BK};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? YL : BK}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? BK : YL); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:BK}); num(C, x, y, s, s, k, s * 0.38, YL); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:YL, line:BK, lw:5}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, BK); },
track:function(C, x1, y, x2){ C.rect(x1, y - 8, x2 - x1, 16, {fill:BK, r:8}); },
conc:{kind:'pill', fill:YL, op:0.9, label:'Output', lc:BK, tc:BK},
L:{cards:'Joint cards', rows:'Line rows', steps:'Valve steps', stmt:'Plant note', stats:'Plain numbers'},
sig:function(pg, B){
function flow(C, Z, o){
var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var g = 30, bw = (Z.w - g * (n - 1)) / n, st = Math.max(64, Z.h * 0.42 / (n - 1)), bh = Z.h - st * (n - 1), tw = bw - 48;
var F = K.fitHD(C, it, tw, 46, bh - 76, {hMax:22, dMax:16, hMin:12, dMin:10});
for (var k = 0; k < n - 1; k++) { var x = Z.x + k * (bw + g), y = Z.y + k * st; pipe(C, [[x + bw - 10, y + 34], [x + bw + g + bw / 2, y + 34], [x + bw + g + bw / 2, y + st + 10]], 28, YL, 14); }
it.forEach(function(t, k){
var x = Z.x + k * (bw + g), y = Z.y + k * st;
C.rect(x, y, bw, bh, {fill:k === n - 1 ? BK : '#FFFFFF', r:16, line:BK, lw:3});
var on = k === n - 1 ? '#FFFFFF' : BK; C.ell(x + 22, y + 16, 38, 38, {fill:k === n - 1 ? YL : BK}); num(C, x + 22, y + 16, 38, 38, k, 15, k === n - 1 ? BK : YL);
K.hd(C, x + 24, y + 66, tw, bh - 80, t, F, {tc:on, mc:k === n - 1 ? '#CFCFCF' : MU});
});
}
function gauges(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:16, vMax:40, vw:function(cw, ah){ return Math.min(cw * 0.5, ah * 0.62); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.34, b.ah * 0.42), cy = b.y + b.ah * 0.5, m = /^\s*([\d.]+)\s*%/.exec(b.val), pc = m ? Math.max(5, Math.min(100, parseFloat(m[1]))) : 60 + (k * 13) % 32;
K5.stroke(C, Q.arc([], b.cx, cy, r, 135, 405), BK, 20); K5.stroke(C, Q.arc([], b.cx, cy, r, 135, 135 + 270 * pc / 100), YL, 10);
return {vc:BK, vx:b.cx - r * 0.78, vw:r * 1.56, vy:cy - b.vs * 0.58};
});
}
function line(C, Z, o){
K5.alt(C, Z, {off:62, fallback:B.rows, pre:function(C, g){ C.rect(g.x - 20, g.y - 13, g.w + 40, 26, {fill:BK, r:13}); }}, function(C, k, t, b){
C.rect(b.cx - 36, b.cy - 20, 10, 40, {fill:YL, r:3}); C.rect(b.cx + 26, b.cy - 20, 10, 40, {fill:YL, r:3}); C.ell(b.cx - 27, b.cy - 27, 54, 54, {fill:YL, line:BK, lw:5}); num(C, b.cx - 27, b.cy - 27, 54, 54, k, 18, BK);
});
}
function riser(C, Z, o){
K5.list(C, Z, {aw:92, pr:16, gap:8, rhMax:98, pre:function(C, g){ for (var c = 0; c < g.cl; c++) C.rect(g.x + c * (g.cw + g.gx) + 24, g.y + g.rh * 0.3, 16, g.h - g.rh * 0.6, {fill:BK, r:8}); }}, function(C, k, t, b){
var s = Math.min(27, b.h * 0.34); C.ell(b.x + 32 - s, b.cy - s, s * 2, s * 2, {fill:YL, line:BK, lw:5}); num(C, b.x + 32 - s, b.cy - s, s * 2, s * 2, k, s * 0.64, BK);
});
}
return {agenda:[['Riser pipe', pg(riser)]], p2:[['Stepped flow', pg(flow)]], p3:[['Stepped flow', pg(flow)]], p4:[['Stepped flow', pg(flow)]], p56:[['Riser pipe', pg(riser)]], p78:[['Riser pipe', pg(riser)]],
stats:[['Pressure gauges', pg(gauges)]], timeline:[['Pipeline', pg(line)]]};
}
});
})();
</script>
    <?php
}, 1166);
