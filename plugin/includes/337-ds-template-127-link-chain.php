<?php
/**
 * Code Snippets export — DS Template 127 — Link Chain
 *
 * Snippet ID : 1169
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:44:29
 * Load order : 337 of 345
 */
/**
 * DS Template 127 — Link Chain
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl127_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl127_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('linkchain', array(
        'name'     => "Link Chain",
        'fonts'    => array('head' => "Assistant", 'body' => "Assistant"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#0B3B36', 'accent' => '#0F766E', 'accent2' => '#F59E0B'),
        'voice'    => "Connected and dependable: every link matters and the chain is as strong as its weakest one. Practical sentences with lead times, volumes and partners.",
        'recipe'   => "The chain end to end, the links (two to four points each), the numbers per link, how the chain strengthens over time and the commitment we ask of each partner.",
        'generate' => 'ds_tpl127_generate',
        'build'    => 'ds_tpl127_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl127_generate') ) {
    function ds_tpl127_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl127_build') ) {
    function ds_tpl127_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-linkchain">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 127 - Link Chain : heavy rounded chain links in teal and amber, each hooked through the next */
var DK = '#0B3B36', TE = '#0F766E', AM = '#F59E0B', MT = '#14B8A6', MU = '#4E6B67', LN = '#D0E3E0', PALE = '#F0F7F6';
var P = {bg:'#FFFFFF', text:DK, title:DK, muted:MU, accent:TE, accent2:AM, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [TE, AM, DK, MT];
function lk(k){ return k % 2 ? AM : TE; }
function link(C, x, y, w, h, col, lw){ C.rect(x, y, w, h, {line:col, lw:lw, r:h / 2}); }
function rotP(d, cx, cy, a){ var c = Math.cos(a * Math.PI / 180), s = Math.sin(a * Math.PI / 180); return d.map(function(q){ var o = [q[0]]; for (var i = 1; i + 1 < q.length; i += 2) o.push(cx + (q[i] - cx) * c - (q[i + 1] - cy) * s, cy + (q[i] - cx) * s + (q[i + 1] - cy) * c); return o; }); }
function dchain(C, cx, cy, n, w, h, lw, a){ var ov = h * 0.62, tot = n * w - (n - 1) * ov, x0 = cx - tot / 2; for (var k = 0; k < n; k++) C.path(rotP(Q.rrect([], x0 + k * (w - ov), cy - h / 2, w, h, h / 2), cx, cy, a), {stroke:lk(k), lw:lw}); }
function hchain(C, x, cy, n, w, h, lw){ var ov = h * 0.62; for (var k = 0; k < n; k++) link(C, x + k * (w - ov), cy - h / 2, w, h, lk(k), lw); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { C.ell(886, 46, 288, 288, {line:TE, lw:6}); D.imgBox(C, 900, 60, 260, 260, {shape:'ell'}); }
dchain(C, 980, photo ? 500 : 420, 5, 260, 112, 30, -24);
K5.coverText(C, 88, 110, 580, {max:54, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
hchain(C, -60, 590, 7, 250, 104, 28);
if (photo) D.imgBox(C, 830, 70, 362, 380, {r:18});
K5.closeText(C, 88, 80, 680, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {line:lk(k), lw:5}); C.icon(x + 10, y + 10, 16, c.icon, DK); }});
}
K5.make({
id:'linkchain', name:'Link Chain', tag:'White · interlocked links',
desc:'Heavy rounded chain links in teal and amber, each hooked through the next, run across the cover, and every link holds one step: linked steps, linked rings for numbers and a chain timeline. For supply chain, value chain, partnerships and blockchain.',
fonts:{head:'Assistant', body:'Assistant', headW:700, bodyW:400, headLh:1.08, gf:'Assistant:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:18, imgR:18, coverLbl:'Chain', closeLbl:'Chain',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; hchain(C, W - 170, 42, 3, 60, 28, 7); },
cover:cover, closing:closing,
rule:function(C, x, y){ hchain(C, x + 3, y + 7, 3, 34, 16, 4.5); return 28; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? TE : PALE, r:18});
if (!o.big) { link(C, x + w - 86, y + 20, 40, 20, hot ? '#FFFFFF' : TE, 5); link(C, x + w - 58, y + 20, 40, 20, AM, 5); }
return hot ? {tc:'#FFFFFF', mc:'#CDE7E3', ac:'#FFFFFF', hot:1} : {tc:DK, mc:MU, ac:TE};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {line:hot ? AM : lk(k), lw:s * 0.14, fill:hot ? TE : '#FFFFFF'}); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, hot ? '#FFFFFF' : DK); },
rowMark:function(C, x, y, s, t, k){ C.ell(x + s * 0.66, y + s * 0.2, s * 0.6, s * 0.6, {line:lk(k + 1), lw:s * 0.11}); C.ell(x, y, s, s, {line:lk(k), lw:s * 0.14, fill:'#FFFFFF'}); num(C, x, y, s, s, k, s * 0.36, DK); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {line:lk(k), lw:s * 0.14, fill:'#FFFFFF'}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, DK); },
track:function(C, x1, y, x2){ var n = Math.max(3, Math.round((x2 - x1) / 44)); for (var i = 0; i < n; i++) link(C, x1 + (x2 - x1) * i / n, y - 7, (x2 - x1) / n + 8, 14, i % 2 ? '#F7D08A' : '#9FD1CB', 3.5); },
L:{cards:'Clasp cards', rows:'Ring rows', steps:'Ring steps', stmt:'Bond note', stats:'Plain numbers'},
sig:function(pg, B){
function linked(C, Z, o){
var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var lh = Math.min(150, Z.h * 0.38), ov = lh * 0.42, lw = (Z.w - 14 + ov * (n - 1)) / n, y = Z.y + 8, tw = lw - ov - 36;
var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), lw - ov - lh * 0.5, lh - 70, {font:'head', max:22, min:12, maxLines:2}), ds = C.groupFit(it.map(function(t){ return t.d || ''; }), tw, Z.h - lh - 36, {font:'body', max:17, min:10, lh:1.4});
it.forEach(function(t, k){ link(C, Z.x + 7 + k * (lw - ov), y, lw, lh, lk(k), 14); });
it.forEach(function(t, k){
var x = Z.x + 7 + k * (lw - ov), cx = x + lw / 2;
C.text(cx - 40, y + 20, 80, 24, pad2(k + 1), {font:'head', size:16, wt:800, color:k % 2 ? '#B45309' : TE, align:'c', va:'m', lh:1});
if (t.h) C.text(cx - (lw - ov - lh * 0.5) / 2, y + 44, lw - ov - lh * 0.5, lh - 66, t.h, {font:'head', size:hs, color:DK, align:'c', va:'m', lh:1.14, ed:{f:'x-part', i:t.i, p:0}, maxLines:2}); else K.icon(C, cx - 20, y + lh / 2 - 12, 40, t, DK);
C.text(cx - tw / 2, y + lh + 26, tw, Z.y + Z.h - (y + lh + 26), t.d, {font:'body', size:ds, color:MU, align:'c', lh:1.4, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
});
}
function rings(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:18, vMax:40, vw:function(cw, ah){ return Math.min(cw * 0.46, ah * 0.6); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.3, b.ah * 0.4), cy = b.y + b.ah * 0.46, cx = b.cx - r * 0.24;
C.ell(cx + r * 0.62, cy + r * 0.1, r * 1.2, r * 1.2, {line:lk(k + 1), lw:12}); C.ell(cx - r, cy - r, r * 2, r * 2, {line:lk(k), lw:16, fill:'#FFFFFF'});
return {vc:DK, vx:cx - r * 0.8, vw:r * 1.6, vy:cy - b.vs * 0.58};
});
}
function chain(C, Z, o){
K5.alt(C, Z, {same:true, at:0.2, off:60, fallback:B.rows}, function(C, k, t, b){
link(C, b.cx - b.cw * 0.58, b.cy - 36, b.cw * 1.16, 72, lk(k), 11); num(C, b.cx - 40, b.cy - 20, 80, 40, k, 22, DK);
});
}
return {p2:[['Linked steps', pg(linked)]], p3:[['Linked steps', pg(linked)]], p4:[['Linked steps', pg(linked)]], stats:[['Linked rings', pg(rings)]], timeline:[['Chain line', pg(chain)]]};
}
});
})();
</script>
    <?php
}, 1177);
