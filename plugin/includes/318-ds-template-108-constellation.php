<?php
/**
 * Code Snippets export — DS Template 108 — Constellation
 *
 * Snippet ID : 1150
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 05:29:01
 * Load order : 318 of 345
 */
/**
 * DS Template 108 — Constellation
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Kit 3", "DS Template Kit 4" and "DS Template Kit 5" snippets to be active.
 * - Generation: its own AI prompt voice + storyline (ds_tpl108_generate)
 * - Layouts: its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 * - Download: ds_tpl108_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('constellation', array(
        'name'     => "Constellation",
        'fonts'    => array('head' => "Urbanist", 'body' => "Urbanist"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#1E1B4B', 'accent' => '#3730A3', 'accent2' => '#F97316'),
        'voice'    => "Connected and systems-minded: nodes, links and network effects. Clear, modern sentences with counts and growth rates.",
        'recipe'   => "The network today, the key nodes (two to four points each), the numbers, how the links grow and who we connect next.",
        'generate' => 'ds_tpl108_generate',
        'build'    => 'ds_tpl108_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl108_generate') ) {
    function ds_tpl108_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl108_build') ) {
    function ds_tpl108_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-constellation">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 108 - Constellation : dots joined by thin lines like a star map or a network; two nodes glow orange */
var IN = '#1E1B4B', IG = '#3730A3', OR = '#F97316', VI = '#6366F1', MU = '#5F5D82', LN = '#DADBEC', PALE = '#F5F5FC', GL = '#C7CBE6';
var P = {bg:'#FFFFFF', text:IN, title:IN, muted:MU, accent:IG, accent2:OR, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [IG, OR, VI, IN];
function net(C, x, y, w, h, n, seed, md, hot){
var R = K5.rnd(seed), pts = [], ld = [], nd = [], i, j;
for (i = 0; i < n; i++) pts.push([x + R() * w, y + R() * h, 4 + R() * 7]);
for (i = 0; i < n; i++) for (j = i + 1; j < n; j++) if (Math.hypot(pts[i][0] - pts[j][0], pts[i][1] - pts[j][1]) < md) Q.line(ld, pts[i][0], pts[i][1], pts[j][0], pts[j][1]);
K5.stroke(C, ld, GL, 1.6);
pts.forEach(function(p, k){ if (hot && (k === 3 || k === 11)) return; Q.circ(nd, p[0], p[1], p[2]); }); K5.fill(C, nd, IG);
if (hot) [3, 11].forEach(function(k){ var p = pts[k]; if (!p) return; C.ell(p[0] - 22, p[1] - 22, 44, 44, {line:OR, lw:2}); C.ell(p[0] - 11, p[1] - 11, 22, 22, {fill:OR}); });
return pts;
}
function star(C, cx, cy, r, col, k, on){ C.ell(cx - r - 7, cy - r - 7, r * 2 + 14, r * 2 + 14, {line:col, lw:1.5}); C.ell(cx - r, cy - r, r * 2, r * 2, {fill:col}); if (k != null) num(C, cx - r, cy - r, r * 2, r * 2, k, r * 0.74, on || '#FFFFFF'); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
net(C, 700, 40, 540, 640, 26, 21, 150, !photo);
if (photo) { C.ell(826, 206, 308, 308, {fill:'#FFFFFF', line:OR, lw:3}); D.imgBox(C, 840, 220, 280, 280, {shape:'ell'}); }
K5.coverText(C, 88, 130, 580, {max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
net(C, 760, 40, 480, 640, 22, 33, 150, !photo);
if (photo) { C.ell(846, 216, 288, 288, {fill:'#FFFFFF', line:OR, lw:3}); D.imgBox(C, 860, 230, 260, 260, {shape:'ell'}); }
K5.closeText(C, 88, 150, 620, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:k === 1 ? OR : IG}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'constellation', name:'Constellation', tag:'White · linked nodes',
desc:'Dots joined by thin lines like a star map or a network, with two nodes glowing orange: a hub with spokes, orbit numbers and a star-path timeline in deep indigo. For networks, ecosystems, partner programmes and platform strategies.',
fonts:{head:'Urbanist', body:'Urbanist', headW:800, bodyW:400, headLh:1.08, gf:'Urbanist:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:16, imgR:16, coverLbl:'Star map', closeLbl:'Star map',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; net(C, 1030, 10, 240, 110, 8, 5 + (C.i % 5), 110); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x + 6, y + 4, 60, 2, {fill:GL}); C.ell(x, y, 10, 10, {fill:IG}); C.ell(x + 30, y - 1, 12, 12, {fill:OR}); C.ell(x + 62, y, 10, 10, {fill:IG}); return 22; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? IG : PALE, r:16});
if (!o.big) { C.line(x + w - 54, y + 30, x + w - 26, y + 22, {color:hot ? '#A5A8F5' : GL, lw:1.6}); C.ell(x + w - 60, y + 24, 12, 12, {fill:hot ? '#FFFFFF' : IG}); C.ell(x + w - 32, y + 15, 14, 14, {fill:OR}); }
return hot ? {tc:'#FFFFFF', mc:'#D4D5FA', ac:'#FFFFFF', hot:1} : {tc:IN, mc:MU, ac:IG};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? '#FFFFFF' : IG}); C.ell(x + s * 0.74, y - s * 0.06, s * 0.3, s * 0.3, {fill:OR}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? IG : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ star(C, x + s / 2, y + s / 2, s * 0.38, k % 3 === 1 ? OR : IG, k); },
node:function(C, cx, cy, s, k){ C.ell(cx - s * 0.6, cy - s * 0.6, s * 1.2, s * 1.2, {fill:'#FFFFFF'}); star(C, cx, cy, s * 0.4, k % 3 === 1 ? OR : IG, k); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:GL, lw:2}); },
L:{cards:'Node cards', rows:'Link rows', steps:'Node steps', stmt:'Signal note', stats:'Plain numbers'},
sig:function(pg, B){
function hub(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 900) return B.rows(C, Z, o);
var mid = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, Ln = Math.ceil(n / 2), rows = Ln, rh = Z.h / rows, hr = Math.min(66, Z.h * 0.2), tw = Z.w / 2 - hr - 150;
var F = K.fitHD(C, it, tw, 40, rh - 16, {hMax:22, dMax:16, hMin:12, dMin:10, noBoost:n > 4});
var pos = it.map(function(t, k){ var left = k < Ln, r = left ? k : k - Ln, cnt = left ? Ln : n - Ln; return {left:left, x:left ? Z.x + 30 : mid + hr + 70, y:Z.y + (Z.h - cnt * rh) / 2 + rh * (r + 0.5)}; });
pos.forEach(function(p){ C.line(p.x, p.y, mid, cy, {color:GL, lw:1.8}); });
C.ell(mid - hr - 12, cy - hr - 12, hr * 2 + 24, hr * 2 + 24, {fill:'#FFFFFF', line:GL, lw:1.6}); C.ell(mid - hr, cy - hr, hr * 2, hr * 2, {fill:IG});
C.text(mid - hr * 0.8, cy - hr * 0.8, hr * 1.6, hr * 1.6, String(C.s.title || '').split(/\s+/).slice(0, 3).join(' '), {font:'head', fit:{max:19, min:11}, color:'#FFFFFF', align:'c', va:'m', maxLines:3, lh:1.1});
it.forEach(function(t, k){
var p = pos[k], bh = K3.bH(C, t, tw, F);
C.ell(p.x - 30, p.y - 30, 60, 60, {fill:'#FFFFFF'}); star(C, p.x, p.y, 20, k % 3 === 1 ? OR : IG, k);
K.hd(C, p.x + 44, p.y - bh / 2, tw, rh - 8, t, F, {tc:IN, mc:MU});
});
}
function orbits(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:18, vMax:42, vw:function(cw, ah){ return Math.min(cw * 0.5, ah * 0.66); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.3, b.ah * 0.38), cy = b.y + b.ah / 2, c = k % 2 ? IN : IG, p = K5.pol(b.cx, cy, r + 20, -40 + k * 70);
C.ell(b.cx - r - 20, cy - r - 20, r * 2 + 40, r * 2 + 40, {line:GL, lw:1.8}); C.ell(b.cx - r, cy - r, r * 2, r * 2, {fill:c}); C.ell(p[0] - 10, p[1] - 10, 20, 20, {fill:OR});
return {vc:'#FFFFFF', vx:b.cx - r * 0.92, vw:r * 1.84, vy:cy - b.vs * 0.58};
});
}
function path(C, Z, o){
K5.alt(C, Z, {off:78, fallback:B.rows, pre:function(C, g){ var pts = []; for (var k = 0; k < g.n; k++) pts.push([g.x + g.cw * (k + 0.5), g.y + (k % 2 === 0 ? -30 : 30)]); K5.stroke(C, Q.pts([], pts), GL, 2); }}, function(C, k, t, b){
var y = b.cy + (b.up ? -30 : 30); C.ell(b.cx - 34, y - 34, 68, 68, {fill:'#FFFFFF'}); star(C, b.cx, y, 22, k === b.n - 1 ? OR : IG, k);
});
}
function links(C, Z, o){
K5.list(C, Z, {aw:86, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
if (!b.last) C.line(b.x + 32, b.cy, b.x + 32, b.cy + b.h + 8, {color:GL, lw:1.8});
C.ell(b.x + 6, b.cy - 26, 52, 52, {fill:'#FFFFFF'}); star(C, b.x + 32, b.cy, Math.min(19, b.h * 0.24), k % 3 === 1 ? OR : IG, k);
});
}
return {agenda:[['Linked list', pg(links)]], p2:[['Hub and spokes', pg(hub)]], p3:[['Hub and spokes', pg(hub)]], p4:[['Hub and spokes', pg(hub)]], p56:[['Hub and spokes', pg(hub)], ['Linked list', pg(links)]], p78:[['Linked list', pg(links)]],
stats:[['Orbit numbers', pg(orbits)]], timeline:[['Star path', pg(path)]]};
}
});
})();
</script>
    <?php
}, 1158);
