<?php
/**
 * Code Snippets export — DS Template 138 — Nav Rail
 *
 * Snippet ID : 1224
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:54:00
 * Load order : 362 of 369
 */
/**
 * DS Template 138 — Nav Rail
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl138_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl138_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('navrail', array(
        'name'     => "Nav Rail",
        'fonts'    => array('head' => "DM Sans", 'body' => "DM Sans"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#121A3D', 'accent' => '#2443C9', 'accent2' => '#7C93FF'),
        'voice'    => "Organised and navigable: each section opens with a clear headline and stays on its topic.",
        'recipe'   => "Overview, introduction, about us, services, mission and vision, history, projects and get in touch - one clear section per slide.",
        'generate' => 'ds_tpl138_generate',
        'build'    => 'ds_tpl138_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl138_generate') ) {
    function ds_tpl138_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl138_build') ) {
    function ds_tpl138_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-navrail">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 138 - Nav Rail : a royal-blue navigation rail that lists the deck's own sections, the current one cut out as a white tab */
var NB = '#2443C9', DB = '#172C93', INK = '#121A3D', MU = '#5C6585', PALE = '#F3F5FD', LN = '#E1E5F5', RAIL = 250;
var GV = {ang:180, stops:[[0, NB], [100, DB]]}, GR = {ang:135, stops:[[0, NB], [100, DB]]};
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:NB, accent2:'#7C93FF', card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [NB, '#4C6EF5', DB, '#7C93FF'];
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:700, color:col, align:'c', va:'m', lh:1, caps:false}); }
function sections(C){
var sl = (C.deck && C.deck.slides) || [], out = [];
sl.forEach(function(s, i){ var k = D.kindOf(s); if (k === 'cover' || k === 'closing' || k === 'quote') return; out.push({i:i, t:String(s.title || 'Section')}); });
if (!out.length) out.push({i:C.i, t:String(C.s.title || 'Overview')});
return out;
}
function rail(C){
var it = sections(C), n = it.length, cur = 0, j;
for (j = 0; j < n; j++) if (it[j].i === C.i) cur = j;
var max = 8, s0 = Math.max(0, Math.min(cur - 3, n - max)); it = it.slice(s0, s0 + max); n = it.length;
C.rect(0, 0, RAIL, H, {grad:GV});
C.rect(28, 40, 26, 26, {line:'#FFFFFF', lw:3, r:7}); C.text(64, 40, RAIL - 84, 26, C.cover.company || 'Overview', {font:'head', size:13, wt:700, color:'#FFFFFF', caps:true, ls:0.08, va:'m', lh:1, maxLines:1});
var step = Math.min(58, (H - 230) / n), y0 = 130;
it.forEach(function(e, k){
var y = y0 + k * step, on = e.i === C.i;
if (on) C.rect(16, y, RAIL + 24 - 16, step - 8, {fill:'#FFFFFF', r:14});
C.text(36, y, RAIL - 60, step - 8, e.t, {font:'body', fit:{max:15, min:10.5}, wt:on ? 700 : 500, color:on ? NB : '#FFFFFF', op:on ? 1 : 0.8, va:'m', lh:1.15, maxLines:2});
});
C.rect(28, H - 96, RAIL - 56, 1, {fill:'#FFFFFF', op:0.3});
}
function head(C, A, o){
var nx = RAIL + 56; if (A.x < nx) { A.w -= nx - A.x; A.x = nx; }
var y = 58, te = C.text(A.x, y, Math.min(A.w, 860), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, color:INK, lh:1.08, ed:{f:'title'}, maxLines:2});
y += te._h + 12; C.rect(A.x, y, 56, 5, {fill:NB, r:2.5}); y += 16;
if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
return y;
}
function photo(C, Z, side){
if (side === 'bottom') return D.imgBox(C, RAIL + 56, Z.y + 8, W - RAIL - 56 - 72, Z.h - 26, {r:16});
if (side === 'left') return D.imgBox(C, RAIL, 0, Math.max(160, Z.w - RAIL), H, {r:0});
D.imgBox(C, Z.x, 0, Z.w, H, {r:0});
}
function foot(C){ C.text(28, H - 76, RAIL - 56, 18, C.cover.website || '', {font:'body', size:11.5, wt:600, color:'#FFFFFF', op:0.75, lh:1, va:'m', maxLines:1}); C.text(28, H - 52, 80, 18, pad2(C.i + 1), {font:'head', size:13, wt:700, color:'#FFFFFF', lh:1, va:'m'}); }
function quote(C){
var o = C.o || {}, by = C.it[0], ph = o.img === 'photo', x = RAIL + 80, w = ph ? 830 - x - 50 : W - x - 90;
if (ph) D.imgBox(C, 830, 0, 450, H, {r:0});
C.text(x, 92, 160, 160, '“', {font:'head', size:124, wt:800, color:NB, lh:1, caps:false});
C.text(x, 250, w, 250, C.s.title, {font:'head', fit:{max:ph ? 32 : 40, min:18}, wt:700, color:INK, lh:1.22, ed:{f:'title'}, maxLines:6});
if (by) C.text(x, 520, w, 26, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:NB, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
function stage(C, ph){
C.rect(0, 0, 560, H, {grad:GR}); C.ell(380, 480, 360, 360, {line:'#FFFFFF', lw:2, lop:0.18}); C.ell(440, 540, 240, 240, {line:'#FFFFFF', lw:2, lop:0.12});
if (ph) { D.imgBox(C, 560, 0, W - 560, H, {r:0}); return; }
var it = sections(C).slice(0, 7); C.text(640, 110, 520, 22, 'Inside this deck', {font:'head', size:14, wt:700, color:NB, caps:true, ls:0.12, va:'m', lh:1});
var step = Math.min(64, 470 / Math.max(1, it.length));
it.forEach(function(e, k){ var y = 160 + k * step; C.rect(640, y, 44, 44, {fill:k === 0 ? NB : PALE, r:12}); num(C, 640, y, 44, 44, k, 15, k === 0 ? '#FFFFFF' : NB); C.text(704, y, 500, 44, e.t, {font:'body', fit:{max:17, min:11}, wt:600, color:INK, va:'m', lh:1.15, maxLines:2}); if (k < it.length - 1) C.rect(704, y + step - 10, 470, 1, {fill:LN}); });
}
function cover(C){ stage(C, (C.o || {}).img === 'photo'); C.rect(88, 80, 26, 26, {line:'#FFFFFF', lw:3, r:7}); K5.coverText(C, 88, 150, 420, {tc:'#FFFFFF', sc:'#D3DBFF', kc:'#C7D2FF', max:50, by:640, rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:'#FFFFFF', r:2.5}); }, ruleH:22}); }
function closing(C){ var ph = (C.o || {}).img === 'photo'; C.rect(0, 0, 560, H, {grad:GR}); C.ell(380, 480, 360, 360, {line:'#FFFFFF', lw:2, lop:0.18}); if (ph) D.imgBox(C, 560, 0, W - 560, H, {r:0}); else { C.ell(760, 200, 320, 320, {fill:PALE}); C.icon(840, 280, 160, 'mdi:compass-outline', NB); } K5.closeText(C, 88, 180, 420, {tc:'#FFFFFF', sc:'#D3DBFF', mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:'#FFFFFF', r:10}); C.icon(x + 10, y + 10, 16, c.icon, NB); }}); }
K5.make({
id:'navrail', name:'Nav Rail', tag:'Royal blue · section menu rail',
desc:'A royal-blue navigation rail runs down every content slide listing the deck’s own section titles, with the current section cut out as a white tab; the cover previews what is inside the deck. For company profiles, corporate overviews, handbooks and long multi-section decks.',
fonts:{head:'DM Sans', body:'DM Sans', headW:700, bodyW:400, headLh:1.08, gf:'DM+Sans:wght@400;500;600;700'},
P:P, LC:LC, cardR:16, coverLbl:'Menu', closeLbl:'Menu', imgPct:0.32,
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; rail(C); },
head:head, photo:photo, foot:foot, quote:quote, cover:cover, closing:closing,
pad:24,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:PALE, r:16}); return {tc:INK, mc:MU, ac:NB}; }
if (o.hot) { C.rect(x, y, w, h, {grad:GR, r:16}); return {tc:'#FFFFFF', mc:'#D3DBFF', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', r:16, line:LN, lw:1.2, shadow:{o:0.07, b:18, d:5}}); return {tc:INK, mc:MU, ac:LC[k % 4]};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? '#FFFFFF' : NB, op:hot ? 1 : 0.1, r:s * 0.28}); K.icon(C, x + s * 0.24, y + s * 0.24, s * 0.52, t, NB); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {grad:GR, r:s * 0.3}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:NB, op:0.25, r:2}); },
conc:{kind:'pill', fill:NB, op:0.08, label:'Key point', lc:NB, tc:INK},
L:{cards:'Panel cards', rows:'Menu rows', steps:'Steps', stmt:'Statement', stats:'Numbers'}
});
})();
</script>
    <?php
}, 1188);
