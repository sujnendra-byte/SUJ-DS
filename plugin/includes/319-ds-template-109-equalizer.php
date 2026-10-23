<?php
/**
 * Code Snippets export — DS Template 109 — Equalizer
 *
 * Snippet ID : 1151
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:29:02
 * Load order : 319 of 345
 */
/**
 * DS Template 109 — Equalizer
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl109_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl109_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('equalizer', array(
        'name'     => "Equalizer",
        'fonts'    => array('head' => "Chakra Petch", 'body' => "Inter"),
        'pal'      => array('bg' => '#F9FAFB', 'text' => '#111827', 'accent' => '#FF7A00', 'accent2' => '#111827'),
        'voice'    => "Lively, clear and on-air: audiences, reach and signal quality. Short sentences with listener numbers and levels.",
        'recipe'   => "The audience, the channels (two to four points each), the levels in numbers, the release schedule and the call to tune in.",
        'generate' => 'ds_tpl109_generate',
        'build'    => 'ds_tpl109_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl109_generate') ) {
    function ds_tpl109_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl109_build') ) {
    function ds_tpl109_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-equalizer">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 109 - Equalizer : a band of rounded orange bars rising and falling like an audio waveform */
var CH = '#111827', OR = '#FF7A00', PE = '#FDBA74', MU = '#5B6472', LN = '#E1E4E8', BG = '#F9FAFB', TR = '#E5E7EB';
var P = {bg:BG, text:CH, title:CH, muted:MU, accent:OR, accent2:CH, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [OR, CH, PE, '#6B7280'];
function eq(C, x, cy, n, gap, w, amp, col, ph, op){ var d = []; for (var i = 0; i < n; i++) { var h = Math.max(w, amp * (0.16 + 0.84 * Math.abs(Math.sin(i * 0.55 + (ph || 0))) * Math.abs(Math.cos(i * 0.21 + 1)))); Q.rrect(d, x + i * gap, cy - h / 2, w, h, w / 2); } K5.fill(C, d, col, op); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:CH});
if (photo) { D.imgBox(C, 800, 80, 392, 400, {r:14}); eq(C, 800, 590, 27, 15, 8, 140, OR); } else eq(C, 730, 360, 34, 16, 9, 460, OR);
K5.coverText(C, 88, 150, 580, {tc:'#FFFFFF', sc:'#C5CAD3', kc:OR, max:54, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:CH}); eq(C, 88, 590, 70, 16, 9, 180, OR, 0.8);
if (photo) D.imgBox(C, 830, 80, 362, 360, {r:14});
K5.closeText(C, 88, 90, 680, {tc:'#FFFFFF', sc:'#C5CAD3', mc:OR, mi:CH});
}
K5.make({
id:'equalizer', name:'Equalizer', tag:'Charcoal · orange sound bars',
desc:'A band of rounded orange bars rising and falling like an audio waveform on a charcoal cover, with light pages of channel strips, level meters and a waveform timeline. For media, telecom, voice, audio, podcasts and streaming.',
fonts:{head:'Chakra Petch', body:'Inter', headW:700, bodyW:400, headLh:1.08, gf:'Chakra+Petch:wght@500;600;700|Inter:wght@400;500;600;700'},
P:P, LC:LC, cardR:14, imgR:14, coverLbl:'Waveform', closeLbl:'Waveform',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; eq(C, W - 190, 44, 11, 11, 6, 56, OR, C.i * 0.7); },
cover:cover, closing:closing,
rule:function(C, x, y){ eq(C, x, y + 6, 9, 8, 5, 22, OR, 0.4); return 26; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? CH : '#FFFFFF', r:14, line:hot ? null : LN, lw:1.5});
if (!o.big) eq(C, x + w - 62, y + 30, 5, 9, 5, 26, OR, k * 1.3);
return hot ? {tc:'#FFFFFF', mc:'#C5CAD3', ac:OR, hot:1} : {tc:CH, mc:MU, ac:OR};
},
mark:function(C, x, y, s, t, k, cc){ C.rect(x, y, s, s, {fill:cc && cc.hot ? OR : CH, r:12}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, cc && cc.hot ? CH : OR); },
rowMark:function(C, x, y, s, t, k){ C.rect(x, y, s, s, {fill:CH, r:12}); num(C, x, y, s, s, k, s * 0.38, OR); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:CH}); C.ell(cx - s * 0.16, cy - s * 0.16, s * 0.32, s * 0.32, {fill:OR}); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:TR, r:3}); },
conc:{kind:'pill', fill:OR, op:0.14, label:'On air', lc:'#C2410C', tc:CH},
L:{cards:'Deck cards', rows:'Track rows', steps:'Beat steps', stmt:'Liner note', stats:'Plain numbers'},
sig:function(pg, B){
function strips(C, Z, o){
K5.cols(C, Z, {max:5, ah:164, gap:22, tg:14, fallback:B.rows}, function(C, k, t, b){
var pos = [0.2, 0.5, 0.32, 0.62, 0.42][k % 5];
C.rect(b.x + 20, b.y, 6, 148, {fill:TR, r:3}); C.rect(b.x + 20, b.y + 148 * pos, 6, 148 * (1 - pos), {fill:OR, r:3}); C.rect(b.x + 4, b.y + 148 * pos - 10, 38, 20, {fill:CH, r:5}); C.rect(b.x + 10, b.y + 148 * pos - 1, 26, 2, {fill:OR});
K.icon(C, b.x + 70, b.y + 8, 40, t, CH);
C.text(b.x + 70, b.y + 84, 110, 64, pad2(k + 1), {font:'head', size:50, wt:700, color:OR, lh:1, va:'b'});
});
}
function meters(C, Z, o){
K5.stat(C, Z, {ah:Math.min(170, Z.h * 0.44), tg:22, vMax:58, align:'l', pad:36}, function(C, k, t, b){
var m = /^\s*([\d.]+)\s*%/.exec(b.val), pc = m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : 58 + (k * 13) % 34, n = 14, sw = (b.w - 36 - (n - 1) * 5) / n, on = [], off = [];
for (var i = 0; i < n; i++) Q.rrect((i + 0.5) / n * 100 <= pc ? on : off, b.x + 18 + i * (sw + 5), b.y + b.ah - 34, sw, 30, 4);
K5.fill(C, off, TR); K5.fill(C, on, OR);
return {vc:CH, vy:b.y + 6, va:'l'};
});
}
function wave(C, Z, o){
K5.alt(C, Z, {off:66, fallback:B.rows, pre:function(C, g){ eq(C, g.x, g.y, Math.floor(g.w / 14), 14, 8, 96, PE, 0.3, 0.55); }}, function(C, k, t, b){
C.rect(b.cx - 2, b.cy - 54, 4, 108, {fill:CH}); C.ell(b.cx - 24, b.cy - 24, 48, 48, {fill:CH}); num(C, b.cx - 24, b.cy - 24, 48, 48, k, 17, OR);
});
}
function tracks(C, Z, o){
K5.list(C, Z, {aw:128, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
C.text(b.x, b.y, 48, b.h, pad2(k + 1), {font:'head', size:Math.min(26, b.h * 0.32), wt:700, color:CH, va:'m', lh:1}); eq(C, b.x + 52, b.cy, 6, 10, 6, Math.min(52, b.h * 0.6), OR, k * 0.9); C.rect(b.x, b.y + b.h + 3, b.w, 1.5, {fill:LN});
});
}
return {agenda:[['Track list', pg(tracks)]], p2:[['Channel strips', pg(strips)]], p3:[['Channel strips', pg(strips)]], p4:[['Channel strips', pg(strips)]], p56:[['Track list', pg(tracks)]], p78:[['Track list', pg(tracks)]],
stats:[['Level meters', pg(meters)]], timeline:[['Waveform', pg(wave)]]};
}
});
})();
</script>
    <?php
}, 1159);
