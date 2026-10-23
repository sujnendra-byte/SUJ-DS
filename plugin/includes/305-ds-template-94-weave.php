<?php
/**
 * Code Snippets export — DS Template 94 — Weave
 *
 * Snippet ID : 1136
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:28:46
 * Load order : 305 of 345
 */
/**
 * DS Template 94 — Weave
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl94_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl94_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('weave', array(
        'name'     => "Weave",
        'fonts'    => array('head' => "Manrope", 'body' => "Manrope"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1F2A44', 'accent' => '#2A9D8F', 'accent2' => '#E76F51'),
        'voice'    => "Collaborative and joined-up: separate strands become one fabric. Clear, warm business language with concrete outcomes.",
        'recipe'   => "The strands we bring together, how they interlock (two to four points each), the numbers, the weaving plan and what each side does next.",
        'generate' => 'ds_tpl94_generate',
        'build'    => 'ds_tpl94_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl94_generate') ) {
    function ds_tpl94_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl94_build') ) {
    function ds_tpl94_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-weave">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 94 - Weave : teal and coral bands cross over and under each other like woven fabric */
var TE = '#2A9D8F', CO = '#E76F51', NV = '#1F2A44', MU = '#5F6B85', LN = '#DCE1EA', TL = '#DCEFEC';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:TE, accent2:CO, card:'#F2F4F8', line:LN, onAccent:'#FFFFFF'};
var LC = [TE, CO, NV, '#E9C46A'];
function weave(C, x, y, cols, rows, bw, gap){
var p = bw + gap, hd = [], vd = [], od = [], ed = [], i, j, x1 = x - gap, x2 = x + cols * p, y1 = y - gap, y2 = y + rows * p;
for (j = 0; j < rows; j++) Q.rect(hd, x1, y + j * p, x2 - x1, bw);
for (i = 0; i < cols; i++) Q.rect(vd, x + i * p, y1, bw, y2 - y1);
for (i = 0; i < cols; i++) for (j = 0; j < rows; j++) if ((i + j) % 2 === 0) { Q.rect(od, x + i * p - 1, y + j * p, bw + 2, bw); Q.line(ed, x + i * p - 1, y + j * p, x + i * p - 1, y + j * p + bw); Q.line(ed, x + i * p + bw + 1, y + j * p, x + i * p + bw + 1, y + j * p + bw); }
else { Q.line(ed, x + i * p, y + j * p, x + i * p + bw, y + j * p); Q.line(ed, x + i * p, y + j * p + bw, x + i * p + bw, y + j * p + bw); }
K5.fill(C, hd, TE); K5.fill(C, vd, CO); K5.fill(C, od, TE); K5.stroke(C, ed, NV, 1.5, 0.28);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:'#F2F4F8'});
if (photo) D.imgBox(C, 740, 0, 540, H, {r:0});
weave(C, 800, -40, 4, 6, 84, 52);
K5.coverText(C, 88, 120, 600, {max:58, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:'#F2F4F8'});
if (photo) D.imgBox(C, 820, 0, 460, H, {r:0});
weave(C, 900, -40, 3, 6, 84, 52);
K5.closeText(C, 88, 150, 680, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:k % 2 ? CO : TE}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'weave', name:'Weave', tag:'Cool grey · woven bands',
desc:'Teal and coral bands cross over and under each other like woven fabric, and the crossing points hold the content: threaded rows, woven numbers and a warp-and-weft timeline. For partnerships, integration, cross-team programmes and mergers.',
fonts:{head:'Manrope', body:'Manrope', headW:800, bodyW:400, headLh:1.08, gf:'Manrope:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, coverLbl:'Woven panel', closeLbl:'Woven panel',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; weave(C, W - 106, -14, 3, 2, 22, 14); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 64, 6, {fill:TE}); C.rect(x + 22, y - 5, 6, 16, {fill:CO}); return 18; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : '#F2F4F8'});
if (!o.big) { C.rect(x, y, 8, h, {fill:CO}); C.rect(x, y + h - 8, w, 8, {fill:TE}); } else C.rect(x, y, 8, h, {fill:TE});
return hot ? {tc:'#FFFFFF', mc:'#C5CCDB', ac:'#FFFFFF', hot:1} : {tc:NV, mc:MU, ac:TE};
},
mark:function(C, x, y, s, t, k, cc){ C.rect(x, y, s, s, {fill:k % 2 ? CO : TE}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.rect(x, y + s * 0.34, s, s * 0.32, {fill:TE}); C.rect(x + s * 0.34, y, s * 0.32, s, {fill:CO}); if (k % 2) C.rect(x, y + s * 0.34, s, s * 0.32, {fill:TE}); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? CO : TE}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 5, x2 - x1, 10, {fill:TL}); },
L:{cards:'Selvedge cards', rows:'Woven rows', steps:'Stitch steps', stmt:'Fabric note', stats:'Thread numbers'},
sig:function(pg, B){
function threaded(C, Z, o){
K5.list(C, Z, {aw:128, pr:22, gap:12, rhMax:96, pre:function(C, g){ for (var c = 0; c < g.cl; c++) C.rect(g.x + c * (g.cw + g.gx) + 28, g.y - 14, 30, g.h + 28, {fill:CO}); }}, function(C, k, t, b){
if (b.r % 2 === 0) C.rect(b.x, b.y, b.w, b.h, {fill:TL}); else { C.rect(b.x, b.y, 28, b.h, {fill:TL}); C.rect(b.x + 58, b.y, b.w - 58, b.h, {fill:TL}); }
num(C, b.x + 62, b.y, 54, b.h, k, Math.min(24, b.h * 0.32), TE);
});
}
function woven(C, Z, o){
K5.stat(C, Z, {ah:Math.min(200, Z.h * 0.5), tg:20, vMax:46, vw:function(cw){ return cw - 120; }}, function(C, k, t, b){
var by = b.y + b.ah / 2 - 38, vc = k % 2 ? NV : CO;
if (k % 2 === 0) C.rect(b.x + 30, b.y, 44, b.ah, {fill:vc});
C.rect(b.x + 6, by, b.w - 12, 76, {fill:TE});
if (k % 2) C.rect(b.x + 30, b.y, 44, b.ah, {fill:vc});
return {vc:'#FFFFFF', vx:b.x + 86, vw:b.w - 104, vy:by + (76 - b.vs * 1.15) / 2};
});
}
function warp(C, Z, o){
K5.alt(C, Z, {off:78, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 14, g.w, 28, {fill:TE}); }}, function(C, k, t, b){
C.rect(b.cx - 14, b.cy - 64, 28, 128, {fill:CO}); if (k % 2) C.rect(b.cx - 15, b.cy - 14, 30, 28, {fill:TE});
C.rect(b.cx - 22, b.up ? b.cy + 64 : b.cy - 100, 44, 36, {fill:NV}); num(C, b.cx - 22, b.up ? b.cy + 64 : b.cy - 100, 44, 36, k, 16, '#FFFFFF');
});
}
return {agenda:[['Threaded rows', pg(threaded)]], p3:[['Threaded rows', pg(threaded)]], p4:[['Threaded rows', pg(threaded)]], p56:[['Threaded rows', pg(threaded)]], p78:[['Threaded rows', pg(threaded)]],
stats:[['Woven numbers', pg(woven)]], timeline:[['Warp and weft', pg(warp)]]};
}
});
})();
</script>
    <?php
}, 1144);
