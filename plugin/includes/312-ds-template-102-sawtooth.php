<?php
/**
 * Code Snippets export — DS Template 102 — Sawtooth
 *
 * Snippet ID : 1144
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:28:55
 * Load order : 312 of 345
 */
/**
 * DS Template 102 — Sawtooth
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl102_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl102_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('sawtooth', array(
        'name'     => "Sawtooth",
        'fonts'    => array('head' => "Saira", 'body' => "Saira"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#003049', 'accent' => '#003049', 'accent2' => '#C1121F'),
        'voice'    => "Industrial, sharp and no-nonsense: output, uptime, cost per unit. Short declarative sentences with hard numbers.",
        'recipe'   => "The plant or operation today, the cutting issues (two to four points each), the output numbers, the improvement run and the commitments.",
        'generate' => 'ds_tpl102_generate',
        'build'    => 'ds_tpl102_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl102_generate') ) {
    function ds_tpl102_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl102_build') ) {
    function ds_tpl102_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-sawtooth">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 102 - Sawtooth : colour panels end in a sharp zigzag cut, with a thin red zigzag beside it */
var NV = '#003049', RD = '#C1121F', CR = '#FDF0D5', SK = '#669BBC', MU = '#52687A', LN = '#E6DCC6';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:NV, accent2:RD, card:CR, line:LN, onAccent:'#FFFFFF'};
var LC = [NV, RD, SK, '#780000'];
function zv(x, y0, y1, amp, st){ var p = [], n = Math.round((y1 - y0) / st); for (var i = 0; i <= n; i++) p.push([x + (i % 2 ? amp : 0), y0 + (y1 - y0) * i / n]); return p; }
function zh(x0, x1, y, amp, st){ var p = [], n = Math.max(2, Math.round((x1 - x0) / st / 2) * 2); for (var i = 0; i <= n; i++) p.push([x0 + (x1 - x0) * i / n, y + (i % 2 ? amp : 0)]); return p; }
function panelR(C, x, amp, st, col, x2){ var p = zv(x, 0, H, amp, st); p.push([x2 || W, H], [x2 || W, 0]); K5.fill(C, Q.poly([], p), col); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 900, 0, 380, H, {r:0}); panelR(C, 820, 44, 40, NV, 960); } else panelR(C, 820, 44, 40, NV);
K5.stroke(C, Q.pts([], zv(776, -10, H + 10, 44, 40)), RD, 5);
K5.coverText(C, 88, 130, 640, {max:58, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:NV}); panelR(C, 780, 44, 40, CR); K5.stroke(C, Q.pts([], zv(736, -10, H + 10, 44, 40)), RD, 5);
if (photo) D.imgBox(C, 900, 150, 300, 420, {r:0});
K5.closeText(C, 88, 150, 600, {tc:'#FFFFFF', sc:'#BFD3E0', mc:RD});
}
K5.make({
id:'sawtooth', name:'Sawtooth', tag:'White · navy zigzag edge',
desc:'Colour panels end in a sharp zigzag cut instead of a straight line, with a thin red zigzag running beside it: blade-edged headers, pinked cards, peak numbers and a saw-line roadmap. For manufacturing, energy, industrial and operations reports.',
fonts:{head:'Saira', body:'Saira', headW:700, bodyW:400, headLh:1.08, gf:'Saira:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, headTop:66, coverLbl:'Zigzag panel', closeLbl:'Zigzag panel',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; var p = zh(0, W, 16, 12, 16); p.push([W, 0], [0, 0]); K5.fill(C, Q.poly([], p), NV); },
cover:cover, closing:closing,
rule:function(C, x, y){ K5.stroke(C, Q.pts([], zh(x, x + 84, y, 8, 7)), RD, 3); return 22; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot, f = hot ? NV : CR, p = zh(x, x + w, y, 10, 13); p.push([x + w, y + h], [x, y + h]); K5.fill(C, Q.poly([], p), f);
return hot ? {tc:'#FFFFFF', mc:'#BFD3E0', ac:'#FFFFFF', hot:1} : {tc:NV, mc:MU, ac:RD};
},
pad:28,
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? RD : (k % 2 ? RD : NV)}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ K5.fill(C, Q.poly([], [[x, y], [x + s, y + s / 2], [x, y + s]]), k % 2 ? RD : NV); C.text(x, y, s * 0.6, s, String(k + 1), {font:'head', size:s * 0.34, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ K5.fill(C, Q.poly([], [[cx - s * 0.6, cy + s * 0.5], [cx, cy - s * 0.6], [cx + s * 0.6, cy + s * 0.5]]), k % 2 ? RD : NV); num(C, cx - s / 2, cy - s * 0.1, s, s * 0.6, k, s * 0.3, '#FFFFFF'); },
track:function(C, x1, y, x2){ K5.stroke(C, Q.pts([], zh(x1, x2, y + 14, 10, 12)), SK, 2); },
L:{cards:'Pinked cards', rows:'Tooth rows', steps:'Peak steps', stmt:'Cut note', stats:'Pinked numbers'},
sig:function(pg, B){
function blades(C, Z, o){
K5.cols(C, Z, {max:5, ah:104, gap:16, pad:18, tg:16, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y + 96, g.w, g.h - 96, {fill:CR}); }}, function(C, k, t, b){
var p = [[b.x, b.y], [b.x + b.w, b.y]].concat(zh(b.x, b.x + b.w, b.y + 76, 14, 14).reverse()); K5.fill(C, Q.poly([], p), k % 2 ? RD : NV);
C.text(b.x + 18, b.y, 80, 74, pad2(k + 1), {font:'head', size:30, wt:800, color:'#FFFFFF', va:'m', lh:1}); K.icon(C, b.x + b.w - 52, b.y + 20, 34, t, '#FFFFFF');
});
}
function peaks(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:20, vMax:40, vw:function(cw){ return cw * 0.46; }}, function(C, k, t, b){
K5.fill(C, Q.poly([], [[b.x + 4, b.y + b.ah], [b.cx, b.y], [b.x + b.w - 4, b.y + b.ah]]), k % 2 ? RD : NV);
return {vc:'#FFFFFF', vx:b.cx - b.w * 0.26, vw:b.w * 0.52, vy:b.y + b.ah * 0.62 - b.vs * 0.2};
});
}
function saw(C, Z, o){
K5.alt(C, Z, {same:true, at:0.36, off:26, fallback:B.rows, pre:function(C, g){
var p = [[g.x, g.y]]; for (var k = 0; k < g.n; k++) { p.push([g.x + g.cw * (k + 0.5), g.y - 84]); p.push([g.x + g.cw * (k + 1), g.y]); }
K5.fill(C, Q.poly([], p), CR); K5.stroke(C, Q.pts([], p), NV, 3);
}}, function(C, k, t, b){
C.ell(b.cx - 24, b.cy - 108, 48, 48, {fill:k % 2 ? RD : NV}); num(C, b.cx - 24, b.cy - 108, 48, 48, k, 17, '#FFFFFF');
});
}
function teeth(C, Z, o){
K5.list(C, Z, {aw:96, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
var s = Math.min(64, b.h - 10); K5.fill(C, Q.poly([], [[b.x, b.cy - s / 2], [b.x + s, b.cy], [b.x, b.cy + s / 2]]), k % 2 ? RD : NV);
C.text(b.x, b.cy - s / 2, s * 0.62, s, pad2(k + 1), {font:'head', size:s * 0.26, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); C.rect(b.x, b.y + b.h + 3, b.w, 1.5, {fill:LN});
});
}
return {agenda:[['Tooth list', pg(teeth)]], p2:[['Blade columns', pg(blades)]], p3:[['Blade columns', pg(blades)]], p4:[['Blade columns', pg(blades)]], p56:[['Tooth list', pg(teeth)]], p78:[['Tooth list', pg(teeth)]],
stats:[['Peak numbers', pg(peaks)]], timeline:[['Saw line', pg(saw)]]};
}
});
})();
</script>
    <?php
}, 1152);
