<?php
/**
 * Code Snippets export — DS Template 136 — Corner Shards
 *
 * Snippet ID : 1222
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ds-template-pack-130-141
 * Modified   : 2026-10-15 03:53:57
 * Load order : 360 of 369
 */
/**
 * DS Template 136 — Corner Shards
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl136_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl136_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('cornershards', array(
        'name'     => "Corner Shards",
        'fonts'    => array('head' => "Lexend", 'body' => "Lexend"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#163447', 'accent' => '#1F4A63', 'accent2' => '#F0532C'),
        'voice'    => "Precise academic voice: research questions, method, findings and contribution stated carefully.",
        'recipe'   => "Background and context, research problem and questions, methodology, data and sample, key findings, discussion and conclusions.",
        'generate' => 'ds_tpl136_generate',
        'build'    => 'ds_tpl136_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl136_generate') ) {
    function ds_tpl136_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl136_build') ) {
    function ds_tpl136_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-cornershards">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 136 - Corner Shards : clean white with slate and signal-orange triangular shards cutting the corners */
var SL = '#1F4A63', DK = '#163447', OR = '#F0532C', MU = '#5B7080', PALE = '#F3F5F7', LN = '#DFE5EA', LG = '#E6ECF0';
var P = {bg:'#FFFFFF', text:DK, title:SL, muted:MU, accent:SL, accent2:OR, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [SL, OR, '#3F7590', DK];
function tri(C, pts, col, op){ return K.poly(C, pts, {fill:col, op:op}); }
function num(C, x, y, w, h, k, sz, col){ C.text(x, y, w, h, pad2(k + 1), {font:'head', size:sz, wt:700, color:col, align:'c', va:'m', lh:1, caps:false}); }
function shards(C, photo, close){
if (photo) { D.imgBox(C, 620, 0, 660, 560, {r:0}); tri(C, [[618, -2], [830, -2], [618, 562]], '#FFFFFF'); }
else { K.poly(C, [[830, 0], [W, 0], [W, 560], [620, 560]], {fill:LG}); C.icon(900, 170, 200, close ? 'mdi:message-reply-text-outline' : 'mdi:school-outline', SL); }
tri(C, [[1040, 0], [W, 0], [W, 240]], OR);
tri(C, [[700, H], [W, 300], [W, H]], SL); tri(C, [[960, H], [W, 470], [W, H]], DK);
tri(C, [[520, H], [690, H], [610, 610]], OR, 0.9);
}
function cover(C){ shards(C, (C.o || {}).img === 'photo'); C.rect(88, 470, 460, 1.5, {fill:LN}); K5.coverText(C, 88, 120, 560, {max:64, kc:SL, by:640, rule:function(C, x, y){ C.rect(x, y, 70, 5, {fill:OR}); }, ruleH:24}); }
function closing(C){ shards(C, (C.o || {}).img === 'photo', true); K5.closeText(C, 88, 170, 540, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:SL}); tri(C, [[x + 22, y + 36], [x + 36, y + 22], [x + 36, y + 36]], OR); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }}); }
K5.make({
id:'cornershards', name:'Corner Shards', tag:'White · slate and orange triangle shards',
desc:'Clean white pages where slate and signal-orange triangular shards cut into the corners, photos get a diagonal edge and cards have a folded orange dog-ear; numbers sit on slanted slate blades. For academic research, dissertations, theses, reports and consulting.',
fonts:{head:'Lexend', body:'Lexend', headW:700, bodyW:400, headLh:1.08, gf:'Lexend:wght@400;500;600;700'},
P:P, LC:LC, cardR:0, coverLbl:'Shards', closeLbl:'Shards',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; tri(C, [[W - 160, 0], [W, 0], [W, 160]], SL); tri(C, [[W - 74, 0], [W, 0], [W, 74]], OR); tri(C, [[W - 160, 0], [W - 120, 0], [W - 140, 30]], LG); tri(C, [[0, H - 96], [0, H], [96, H]], OR); tri(C, [[0, H - 40], [0, H], [40, H]], SL); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:OR}); return 16; },
pad:26,
card:function(C, x, y, w, h, k, o){
var hot = o.hot;
C.rect(x, y, w, h, {fill:hot ? SL : PALE}); tri(C, [[x + w - 24, y - 0.5], [x + w + 0.5, y - 0.5], [x + w + 0.5, y + 24]], '#FFFFFF'); tri(C, [[x + w - 24, y], [x + w - 24, y + 24], [x + w, y + 24]], OR);
if (o.big) C.rect(x, y, 6, h, {fill:OR});
return hot ? {tc:'#FFFFFF', mc:'#C9D6DE', ac:OR, hot:1} : {tc:DK, mc:MU, ac:SL};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? '#FFFFFF' : SL}); tri(C, [[x + s * 0.6, y + s], [x + s, y + s * 0.6], [x + s, y + s]], OR); K.icon(C, x + s * 0.24, y + s * 0.24, s * 0.5, t, hot ? SL : '#FFFFFF'); },
node:function(C, cx, cy, s, k){ var r = s * 0.6; K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], {fill:k % 2 ? OR : SL}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.32, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 1, x2 - x1, 2, {fill:SL}); for (var x = x1; x <= x2; x += 40) C.rect(x - 1, y - 6, 2, 12, {fill:SL, op:0.5}); },
conc:{kind:'box', fill:PALE, r:0, label:'Finding', lc:OR, tc:DK},
L:{cards:'Dog-ear cards', rows:'Shard rows', steps:'Blade steps', stmt:'Statement', stats:'Numbers'},
sig:function(pg, B){
function blades(C, Z, o){
K5.cols(C, Z, {tg:20, pad:6, ah:function(n, cw, Z){ return Math.min(Z.h * 0.4, 150); }, fallback:B.rows}, function(C, k, t, b){
var sk = 28, c = k % 2 ? OR : SL;
K.poly(C, [[b.x + sk, b.y], [b.x + b.w, b.y], [b.x + b.w - sk, b.y + b.ah], [b.x, b.y + b.ah]], {fill:c});
tri(C, [[b.x + b.w - sk - 40, b.y + b.ah], [b.x + b.w - sk, b.y + b.ah], [b.x + b.w - sk * 0.3, b.y + b.ah - 28]], k % 2 ? SL : OR);
num(C, b.x + 40, b.y + 18, 90, b.ah * 0.5, k, Math.min(54, b.ah * 0.38), '#FFFFFF');
K.icon(C, b.x + b.w - sk - 70, b.y + 22, 40, t, '#FFFFFF');
});
}
function blocks(C, Z, o){
K5.stat(C, Z, {ah:Math.min(180, Z.h * 0.5), tg:18, vMax:52, vw:function(cw){ return cw - 70; }}, function(C, k, t, b){
var x = b.x + 10, w = b.w - 20; C.rect(x, b.y, w, b.ah, {fill:k === 0 ? SL : PALE}); tri(C, [[x + w - 46, b.y + b.ah], [x + w, b.y + b.ah - 46], [x + w, b.y + b.ah]], OR);
return {vc:k === 0 ? '#FFFFFF' : SL, vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
return {p3:[['Slanted blades', pg(blades)]], p4:[['Slanted blades', pg(blades)]], stats:[['Corner blocks', pg(blocks)]]};
}
});
})();
</script>
    <?php
}, 1186);
