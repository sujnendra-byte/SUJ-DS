<?php
/**
 * Code Snippets export — DS Template 120 — Radar
 *
 * Snippet ID : 1162
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:22
 * Load order : 330 of 345
 */
/**
 * DS Template 120 — Radar
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl120_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl120_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('radar', array(
        'name'     => "Radar",
        'fonts'    => array('head' => "Oxanium", 'body' => "Mulish"),
        'pal'      => array('bg' => '#F6F8FB', 'text' => '#0F1E3D', 'accent' => '#1D4ED8', 'accent2' => '#F97316'),
        'voice'    => "Watchful, calm and precise: signals, threats and opportunities detected early. Short, exact sentences with probabilities, distances and dates.",
        'recipe'   => "What we are scanning for, the contacts on the scope (two to four points each), the readings in numbers, the watch schedule and the response we recommend.",
        'generate' => 'ds_tpl120_generate',
        'build'    => 'ds_tpl120_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl120_generate') ) {
    function ds_tpl120_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl120_build') ) {
    function ds_tpl120_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-radar">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 120 - Radar : a radar scope with range rings, cross hairs and a sweeping beam; each point is an orange blip */
var NV = '#0F1E3D', BL = '#1D4ED8', OR = '#F97316', SK = '#60A5FA', MU = '#55627C', LN = '#D7DEEA', BG = '#F6F8FB';
var P = {bg:BG, text:NV, title:NV, muted:MU, accent:BL, accent2:OR, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [BL, OR, NV, SK];
function scope(C, cx, cy, R, a, o){
o = o || {}; var d = [], t = [], i;
if (!o.noFill) C.ell(cx - R, cy - R, R * 2, R * 2, {fill:'#FFFFFF', op:o.fop == null ? 1 : o.fop});
for (i = 1; i <= 4; i++) if (!(o.skip && i <= o.skip)) Q.circ(d, cx, cy, R * i / 4);
Q.line(d, cx - R, cy, cx + R, cy); Q.line(d, cx, cy - R, cx, cy + R);
for (i = 0; i < 72; i++) { var p1 = K5.pol(cx, cy, R, i * 5), p2 = K5.pol(cx, cy, R + (i % 6 ? R * 0.025 : R * 0.055), i * 5); Q.line(t, p1[0], p1[1], p2[0], p2[1]); }
K5.stroke(C, d, BL, o.lw || 1.6, o.op); if (!o.noTicks) K5.stroke(C, t, BL, o.lw || 1.6, o.op);
if (a != null) { K5.fill(C, Q.sector([], cx, cy, 0, R, a, a + 46), BL, 0.16); var e = K5.pol(cx, cy, R, a + 46); C.line(cx, cy, e[0], e[1], {color:BL, lw:3}); }
}
function blip(C, x, y, r, k){ C.ell(x - r * 1.7, y - r * 1.7, r * 3.4, r * 3.4, {line:OR, lw:1.5}); C.ell(x - r, y - r, r * 2, r * 2, {fill:OR}); if (k != null) num(C, x - r, y - r, r * 2, r * 2, k, r * 0.9, '#FFFFFF', String(k + 1)); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { scope(C, 1010, 360, 300, 205, {skip:3}); D.imgBox(C, 800, 150, 420, 420, {shape:'ell'}); } else { scope(C, 1010, 360, 300, 205); blip(C, 900, 250, 10); blip(C, 1130, 470, 8); blip(C, 1060, 210, 6); }
K5.coverText(C, 88, 130, 560, {max:54, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { scope(C, 1010, 360, 280, 150, {skip:3}); D.imgBox(C, 815, 165, 390, 390, {shape:'ell'}); } else { scope(C, 1090, 580, 340, 190); blip(C, 960, 430, 10); blip(C, 1150, 380, 7); }
K5.closeText(C, 88, 150, 600, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:k === 0 ? OR : BL}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
var POS = [[0.5, -130], [0.8, -40], [0.62, 60], [0.86, 150], [0.36, 200], [0.7, 250]];
K5.make({
id:'radar', name:'Radar', tag:'Cool white · scanning scope',
desc:'A radar scope with range rings, cross hairs, degree ticks and a sweeping beam, where every point is an orange blip on the scope. Watchful and precise, for risk, market scanning, competitor reviews, security and early-warning reports.',
fonts:{head:'Oxanium', body:'Mulish', headW:700, bodyW:400, headLh:1.08, gf:'Oxanium:wght@500;600;700;800|Mulish:wght@400;500;600;700'},
P:P, LC:LC, cardR:14, imgR:14, coverLbl:'Scope', closeLbl:'Scope',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; scope(C, W - 20, 20, 110, 100 + C.i * 23, {noTicks:true, lw:1.2, fop:0}); blip(C, W - 70, 60, 5); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y + 2, 70, 2, {fill:BL}); C.ell(x + 46, y - 3, 12, 12, {fill:OR}); return 20; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : '#FFFFFF', r:14, line:hot ? null : LN, lw:1.5});
if (!o.big) { C.ell(x + w - 52, y + 16, 32, 32, {line:hot ? SK : BL, lw:1.5}); C.ell(x + w - 42, y + 26, 12, 12, {fill:OR}); }
return hot ? {tc:'#FFFFFF', mc:'#C3CEE4', ac:SK, hot:1} : {tc:NV, mc:MU, ac:BL};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {line:hot ? SK : BL, lw:2}); C.ell(x + s * 0.12, y + s * 0.12, s * 0.76, s * 0.76, {fill:hot ? OR : BL}); K.icon(C, x + s * 0.28, y + s * 0.28, s * 0.44, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ blip(C, x + s / 2, y + s / 2, s * 0.3, k); },
node:function(C, cx, cy, s, k){ C.ell(cx - s * 0.6, cy - s * 0.6, s * 1.2, s * 1.2, {fill:BG}); blip(C, cx, cy, s * 0.32, k); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:BL, lw:2}); },
L:{cards:'Target cards', rows:'Contact rows', steps:'Blip steps', stmt:'Briefing note', stats:'Plain numbers'},
sig:function(pg, B){
function contacts(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 900) return B.rows(C, Z, o);
var R = Math.min(Z.h * 0.47, 185), cx = Z.x + R + 14, cy = Z.y + Z.h / 2;
scope(C, cx, cy, R, -70 + C.i * 17);
it.forEach(function(t, k){ var p = K5.pol(cx, cy, R * POS[k][0], POS[k][1]); blip(C, p[0], p[1], 15, k); });
K5.list(C, {x:cx + R + 60, y:Z.y, w:Z.x + Z.w - cx - R - 60, h:Z.h}, {aw:60, pr:10, gap:6, rhMax:100, cols:1}, function(C, k, t, b){
C.text(b.x, b.y, 46, b.h, pad2(k + 1), {font:'head', size:Math.min(26, b.h * 0.34), wt:700, color:OR, va:'m', lh:1}); if (!b.last) C.rect(b.x, b.y + b.h + 2, b.w, 1.5, {fill:LN});
});
}
function rings(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:18, vMax:38, vw:function(cw, ah){ return Math.min(cw * 0.44, ah * 0.5); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.38, b.ah * 0.46), cy = b.y + b.ah / 2, p = K5.pol(b.cx, cy, r, -50 + k * 80);
scope(C, b.cx, cy, r, 20 + k * 85, {noTicks:true, skip:2}); C.ell(b.cx - r * 0.5, cy - r * 0.5, r, r, {fill:NV}); blip(C, p[0], p[1], 7);
return {vc:'#FFFFFF', vx:b.cx - r * 0.47, vw:r * 0.94, vy:cy - b.vs * 0.58};
});
}
function sweep(C, Z, o){
K5.alt(C, Z, {off:62, fallback:B.rows, pre:function(C, g){ var d = []; for (var x = g.x; x <= g.x + g.w; x += 20) Q.line(d, x, g.y - 7, x, g.y + 7); K5.stroke(C, d, BL, 1.5, 0.5); C.rect(g.x, g.y - 1.5, g.w, 3, {fill:BL}); }}, function(C, k, t, b){
C.ell(b.cx - 44, b.cy - 44, 88, 88, {fill:BG}); C.ell(b.cx - 40, b.cy - 40, 80, 80, {line:BL, lw:1.5}); blip(C, b.cx, b.cy, 17, k);
});
}
return {agenda:[['Scope and contacts', pg(contacts)]], p2:[['Scope and contacts', pg(contacts)]], p3:[['Scope and contacts', pg(contacts)]], p4:[['Scope and contacts', pg(contacts)]], p56:[['Scope and contacts', pg(contacts)]],
stats:[['Range rings', pg(rings)]], timeline:[['Sweep line', pg(sweep)]]};
}
});
})();
</script>
    <?php
}, 1170);
