<?php
/**
 * Code Snippets export — DS Template 135 — Spark Ideas
 *
 * Snippet ID : 1221
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:56
 * Load order : 359 of 369
 */
/**
 * DS Template 135 — Spark Ideas
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl135_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl135_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('sparkideas', array(
        'name'     => "Spark Ideas",
        'fonts'    => array('head' => "Plus Jakarta Sans", 'body' => "Plus Jakarta Sans"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1C2A5E', 'accent' => '#2E4FB0', 'accent2' => '#FFC83D'),
        'voice'    => "Playful, open and encouraging: questions, prompts and short idea statements.",
        'recipe'   => "The challenge, ground rules, idea prompts and techniques, top ideas grouped into themes, how we choose and the actions we take next.",
        'generate' => 'ds_tpl135_generate',
        'build'    => 'ds_tpl135_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl135_generate') ) {
    function ds_tpl135_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl135_build') ) {
    function ds_tpl135_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-sparkideas">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 135 - Spark Ideas : bright white, a royal-blue corner block, four-point sparkles and a sun-yellow idea bulb */
var RB = '#2E4FB0', NT = '#1C2A5E', YE = '#FFC83D', MU = '#5A678E', PALE = '#F4F6FC', LN = '#E0E5F3';
var P = {bg:'#FFFFFF', text:NT, title:RB, muted:MU, accent:RB, accent2:YE, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [RB, '#F28C28', '#2BB673', '#7B4FD0'];
function sparkD(d, cx, cy, r){ var k = r * 0.13; d.push(['M', cx, cy - r], ['C', cx + k, cy - k, cx + k, cy - k, cx + r, cy], ['C', cx + k, cy + k, cx + k, cy + k, cx, cy + r], ['C', cx - k, cy + k, cx - k, cy + k, cx - r, cy], ['C', cx - k, cy - k, cx - k, cy - k, cx, cy - r], ['Z']); return d; }
function spark(C, cx, cy, r, col, op){ return K5.fill(C, sparkD([], cx, cy, r), col, op); }
function sparkO(C, cx, cy, r, col, lw){ return K5.stroke(C, sparkD([], cx, cy, r), col, lw || 2); }
function dots(C, x, y, cols, rows, gap, col, op){ var d = []; for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) Q.circ(d, x + i * gap, y + j * gap, 3.4); K5.fill(C, d, col, op); }
function rays(C, cx, cy, r0, r1, col, lw){ var d = []; for (var i = 0; i < 8; i++) { var a = (i * 45 - 90) * RAD; Q.line(d, cx + Math.cos(a) * r0, cy + Math.sin(a) * r0, cx + Math.cos(a) * r1, cy + Math.sin(a) * r1); } K5.stroke(C, d, col, lw || 4); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
function stage(C, photo, close){
C.rect(860, -70, 500, 480, {fill:RB, r:72}); dots(C, 1110, 40, 4, 3, 22, '#FFFFFF', 0.75);
spark(C, 1210, 260, 24, '#FFFFFF'); sparkO(C, 1150, 330, 14, '#FFFFFF', 2);
rays(C, 950, 270, 130, 160, YE, 6); C.ell(830, 150, 240, 240, {fill:YE}); C.icon(880, 200, 140, close ? 'mdi:emoticon-happy-outline' : 'mdi:lightbulb-on-outline', NT);
spark(C, 760, 110, 22, NT); sparkO(C, 1180, 520, 16, NT, 2.4); spark(C, 700, 600, 12, YE);
if (photo) D.imgBox(C, 760, 440, 460, 240, {r:24});
else { [['mdi:target', '#F28C28'], ['mdi:account-group-outline', '#2BB673'], ['mdi:chart-line', '#7B4FD0']].forEach(function(e, i){ var x = 800 + i * 140; C.rect(x, 470, 112, 112, {fill:e[1], r:28}); C.icon(x + 30, 500, 52, e[0], '#FFFFFF'); }); }
}
function cover(C){ stage(C, (C.o || {}).img === 'photo'); K5.coverText(C, 88, 170, 600, {max:62, kc:RB, tc:RB, sc:MU, by:640, rule:function(C, x, y){ spark(C, x + 10, y + 8, 10, YE); C.rect(x + 28, y + 6, 70, 4, {fill:RB, r:2}); }, ruleH:26}); }
function closing(C){ stage(C, (C.o || {}).img === 'photo', true); K5.closeText(C, 88, 190, 600, {tc:RB, mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:LC[k % 4]}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }}); }
K5.make({
id:'sparkideas', name:'Spark Ideas', tag:'White · royal-blue block · sparkles',
desc:'Bright white with a royal-blue corner block, four-point sparkles, a sun-yellow idea bulb and playful colour tiles; ideas burst out of rayed circles and numbers sit on sparkle stars. For brainstorming, workshops, innovation sessions and creative briefs.',
fonts:{head:'Plus Jakarta Sans', body:'Plus Jakarta Sans', headW:800, bodyW:500, headLh:1.06, gf:'Plus+Jakarta+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:16, coverLbl:'Spark', closeLbl:'Spark',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.rect(W - 92, -24, 92, 82, {fill:RB, r:18}); spark(C, W - 46, 22, 13, '#FFFFFF'); spark(C, W - 130, 40, 8, YE); dots(C, 18, H - 70, 2, 3, 16, RB, 0.35); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y + 2, 56, 4, {fill:YE, r:2}); spark(C, x + 70, y + 4, 7, RB); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:PALE, r:16}); spark(C, x + w - 30, y + 30, 14, YE); return {tc:NT, mc:MU, ac:RB}; }
if (o.hot) { C.rect(x, y, w, h, {fill:RB, r:16}); spark(C, x + w - 22, y + 22, 9, YE); return {tc:'#FFFFFF', mc:'#D7E0FA', ac:YE, hot:1}; }
C.rect(x, y, w, h, {fill:PALE, r:16}); spark(C, x + w - 20, y + 20, 8, YE); return {tc:NT, mc:MU, ac:LC[k % 4]};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot, c = LC[k % 4]; C.ell(x, y, s, s, {fill:hot ? '#FFFFFF' : c}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? RB : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ var c = LC[k % 4]; C.rect(x, y, s, s, {fill:c, op:0.14, r:s * 0.3}); K.icon(C, x + s * 0.22, y + s * 0.22, s * 0.56, t, c); },
node:function(C, cx, cy, s, k){ spark(C, cx, cy, s * 0.95, k % 2 ? YE : RB); C.ell(cx - s * 0.27, cy - s * 0.27, s * 0.54, s * 0.54, {fill:'#FFFFFF'}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.3, RB); },
track:function(C, x1, y, x2){ var d = []; for (var x = x1; x < x2; x += 18) Q.rect(d, x, y - 2, 9, 4); K5.fill(C, d, RB, 0.35); },
conc:{kind:'pill', fill:YE, op:0.22, label:'Big idea', lc:'#8A6200', tc:NT},
L:{cards:'Idea cards', rows:'Idea rows', steps:'Spark steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function burst(C, Z, o){
K5.cols(C, Z, {align:'c', tg:22, ah:function(n, cw, Z){ return Math.min(Z.h * 0.48, cw * 0.7, 200); }, fallback:B.rows}, function(C, k, t, b){
var c = LC[k % 4], r = Math.min(b.ah, b.w) * 0.3, cy = b.y + b.ah / 2;
rays(C, b.cx, cy, r + 10, r + 26, c, 4); C.ell(b.cx - r, cy - r, r * 2, r * 2, {fill:c}); K.icon(C, b.cx - r * 0.5, cy - r * 0.5, r, t, '#FFFFFF'); spark(C, b.cx + r * 1.2, cy - r * 1.05, 10, YE);
});
}
function sparkstats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.55), tg:18, vMax:46, vw:function(cw, ah){ return Math.min(ah, cw) * 0.62; }}, function(C, k, t, b){
var c = LC[k % 4], d = Math.min(b.ah, b.w - 40), y = b.y + (b.ah - d) / 2;
C.ell(b.cx - d / 2, y, d, d, {fill:c, op:0.1, line:c, lw:3}); spark(C, b.cx + d * 0.38, y + d * 0.1, 16, YE); sparkO(C, b.cx - d * 0.42, y + d * 0.86, 9, c, 2);
return {vc:c, vy:y + (d - b.vs * 1.15) / 2};
});
}
return {p3:[['Idea burst', pg(burst)]], p4:[['Idea burst', pg(burst)]], stats:[['Spark numbers', pg(sparkstats)]]};
}
});
})();
</script>
    <?php
}, 1185);
