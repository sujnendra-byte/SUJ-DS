<?php
/**
 * Code Snippets export — DS Template 118 — Planner
 *
 * Snippet ID : 1160
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:20
 * Load order : 328 of 345
 */
/**
 * DS Template 118 — Planner
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl118_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl118_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('planner', array(
        'name'     => "Planner",
        'fonts'    => array('head' => "Sofia Sans", 'body' => "Sofia Sans"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1F2937', 'accent' => '#EF4444', 'accent2' => '#1F2937'),
        'voice'    => "Organised and deadline-aware: what happens when, and who owns it. Clear, brisk sentences with dates, weeks and owners.",
        'recipe'   => "The goal and the deadline, the workstreams (two to four points each), the numbers, the calendar of milestones and the dates to hold.",
        'generate' => 'ds_tpl118_generate',
        'build'    => 'ds_tpl118_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl118_generate') ) {
    function ds_tpl118_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl118_build') ) {
    function ds_tpl118_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-planner">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 118 - Planner : a tear-off calendar leaf with a red header and binding rings; dates and deadlines are the main graphic */
var INK = '#1F2937', RD = '#EF4444', AM = '#F59E0B', MU = '#5E6877', LN = '#E2E5EA', PALE = '#F3F4F6';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:RD, accent2:INK, card:'#F9FAFB', line:LN, onAccent:'#FFFFFF'};
var LC = [RD, INK, AM, '#9CA3AF'];
function leaf(C, x, y, w, h, hh, o){
o = o || {}; var r = Math.min(14, w * 0.08);
C.rect(x, y, w, h, {fill:o.fill || '#FFFFFF', r:r, line:LN, lw:1.5, shadow:o.shadow});
C.rect(x, y, w, hh, {fill:o.head || RD, r:r}); C.rect(x, y + hh / 2, w, hh / 2, {fill:o.head || RD});
var rw = Math.max(5, w * 0.045); C.rect(x + w * 0.24 - rw / 2, y - rw * 1.3, rw, rw * 3, {fill:INK, r:rw / 2}); C.rect(x + w * 0.76 - rw / 2, y - rw * 1.3, rw, rw * 3, {fill:INK, r:rw / 2});
}
function year(C){ var m = /(\d{4})/.exec(C.cover.date || ''); return m ? m[1] : String(new Date().getFullYear()); }
function cover(C){
var photo = (C.o || {}).img === 'photo', f = C.cover;
C.bg({fill:'#F9FAFB'});
K5.fill(C, Q.poly([], [[826, 146], [1182, 176], [1150, 590], [794, 560]]), '#FFFFFF'); K5.stroke(C, Q.poly([], [[826, 146], [1182, 176], [1150, 590], [794, 560]]), LN, 1.5);
leaf(C, 790, 130, 360, 430, 74, {shadow:0.12});
C.text(790, 138, 360, 66, (f.date || 'This year').replace(/\s*\d{4}\s*$/, '') || 'This year', {font:'head', size:20, wt:800, color:'#FFFFFF', align:'c', va:'m', caps:true, ls:0.12, lh:1, maxLines:1});
if (photo) D.imgBox(C, 792, 204, 356, 354, {r:0}); else { C.text(800, 250, 340, 180, year(C), {font:'head', size:120, wt:800, color:INK, align:'c', va:'m', lh:1}); C.rect(930, 460, 80, 6, {fill:RD, r:3}); }
K5.coverText(C, 88, 130, 600, {max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:'#F9FAFB'}); leaf(C, 810, 150, 340, 400, 70, {shadow:0.12});
C.text(810, 158, 340, 62, 'Next steps', {font:'head', size:19, wt:800, color:'#FFFFFF', align:'c', va:'m', caps:true, ls:0.12, lh:1});
if (photo) D.imgBox(C, 812, 220, 336, 328, {r:0}); else C.icon(905, 300, 150, 'mdi:calendar-check', RD);
K5.closeText(C, 88, 150, 640, {});
}
K5.make({
id:'planner', name:'Planner', tag:'White · tear-off calendar',
desc:'A tear-off calendar leaf with a red header and binding rings leads the cover, and dates carry the content: a month grid with milestones, tear-off numbers and a row of day leaves. For project plans, schedules, event and launch calendars.',
fonts:{head:'Sofia Sans', body:'Sofia Sans', headW:800, bodyW:400, headLh:1.08, gf:'Sofia+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:12, imgR:12, coverLbl:'Calendar leaf', closeLbl:'Calendar leaf',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; var d = [], hot = (C.i * 3) % 15; for (var j = 0; j < 3; j++) for (var i = 0; i < 5; i++) if (j * 5 + i !== hot) Q.rrect(d, W - 150 + i * 18, 24 + j * 18, 13, 13, 3); K5.fill(C, d, '#E5E7EB'); C.rect(W - 150 + (hot % 5) * 18, 24 + Math.floor(hot / 5) * 18, 13, 13, {fill:RD, r:3}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:RD, r:2.5}); return 16; },
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:'#F9FAFB', r:12}); C.rect(x, y, 8, h, {fill:RD, r:4}); return {tc:INK, mc:MU, ac:RD}; }
leaf(C, x, y + 6, w, h - 6, 16, {fill:o.hot ? INK : '#FFFFFF'});
return o.hot ? {tc:'#FFFFFF', mc:'#C9CED6', ac:'#FFFFFF', hot:1} : {tc:INK, mc:MU, ac:RD};
},
pad:28,
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? RD : '#FEE2E2', r:10}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? '#FFFFFF' : RD); },
rowMark:function(C, x, y, s, t, k){ leaf(C, x, y + 3, s, s - 3, s * 0.28); num(C, x, y + s * 0.3, s, s * 0.68, k, s * 0.36, INK); },
node:function(C, cx, cy, s, k){ leaf(C, cx - s / 2, cy - s / 2 + 3, s, s - 3, s * 0.28); num(C, cx - s / 2, cy - s * 0.2, s, s * 0.68, k, s * 0.34, INK); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:'#E5E7EB', r:3}); },
conc:{kind:'pill', fill:RD, op:0.1, label:'Mark the date', lc:RD, tc:INK},
L:{cards:'Leaf cards', rows:'Diary rows', steps:'Leaf steps', stmt:'Memo note', stats:'Plain numbers'},
sig:function(pg, B){
function month(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 900) return B.rows(C, Z, o);
var gw = Math.min(360, Z.w * 0.32), cs = (gw - 6 * 6) / 7, rows = 5, gh = 44 + rows * (cs + 6), gy = Z.y + Math.max(0, (Z.h - gh) / 2), d = [], pos = [];
C.rect(Z.x, gy, gw, 36, {fill:RD, r:8}); C.text(Z.x, gy, gw, 36, String(C.s.title || '').split(/\s+/).slice(0, 3).join(' '), {font:'head', size:14, wt:800, color:'#FFFFFF', align:'c', va:'m', caps:true, ls:0.1, lh:1, maxLines:1});
for (var k = 0; k < n; k++) pos.push(Math.round(3 + k * 29 / Math.max(1, n - 1) * (n > 1 ? 1 : 0)));
for (var i = 0; i < 35; i++) { var x = Z.x + (i % 7) * (cs + 6), y = gy + 44 + Math.floor(i / 7) * (cs + 6), hi = pos.indexOf(i); if (hi < 0) Q.rrect(d, x, y, cs, cs, 6); else { C.rect(x, y, cs, cs, {fill:hi === n - 1 ? INK : RD, r:6}); num(C, x, y, cs, cs, hi, cs * 0.42, '#FFFFFF', String(hi + 1)); } }
K5.fill(C, d, PALE);
K5.list(C, {x:Z.x + gw + 50, y:Z.y, w:Z.w - gw - 50, h:Z.h}, {aw:84, pr:10, gap:8, rhMax:96, cols:1}, function(C, k, t, b){
var s = Math.min(54, b.h - 14); C.rect(b.x, b.cy - s / 2, s, s, {line:k === n - 1 ? INK : RD, lw:2, r:8, fill:'#FFFFFF'}); num(C, b.x, b.cy - s / 2, s, s, k, s * 0.4, k === n - 1 ? INK : RD);
if (!b.last) C.rect(b.x + 84, b.y + b.h + 3, b.w - 84, 1.5, {fill:LN});
});
}
function tear(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:20, vMax:50, vw:function(cw){ return cw - 80; }}, function(C, k, t, b){
leaf(C, b.x + 14, b.y + 12, b.w - 28, b.ah - 12, 44, {head:k % 2 ? INK : RD});
return {vc:INK, vy:b.y + 56 + (b.ah - 56 - b.vs * 1.15) / 2};
});
}
function days(C, Z, o){
K5.alt(C, Z, {same:true, at:0.24, off:68, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 3, g.w, 6, {fill:'#E5E7EB', r:3}); }}, function(C, k, t, b){
leaf(C, b.cx - 44, b.cy - 46, 88, 96, 28, {head:k === b.n - 1 ? INK : RD}); num(C, b.cx - 44, b.cy - 18, 88, 68, k, 28, INK);
});
}
return {agenda:[['Month and milestones', pg(month)]], p3:[['Month and milestones', pg(month)]], p4:[['Month and milestones', pg(month)]], p56:[['Month and milestones', pg(month)]],
stats:[['Tear-off numbers', pg(tear)]], timeline:[['Day leaves', pg(days)], ['Month and milestones', pg(month)]]};
}
});
})();
</script>
    <?php
}, 1168);
