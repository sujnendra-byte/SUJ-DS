<?php
/**
 * Code Snippets export — DS Template Cycle layout - each text beside its own icon
 *
 * Snippet ID : 1050
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 13:05:39
 * Load order : 244 of 246
 */
add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-cycle-fix">
/* "Cycle" layout: each text now sits at the height of its own icon. Before, the left column was filled from the top,
   so with 3 points the third text stood top-left while its icon was bottom-left and the bottom-left stayed empty. */
(function(){
var D = window.DSTE; if (!D || !D.B || !D.B.xCycle || !D.K2 || D.__cycleFix) return; D.__cycleFix = 1;
var K = D.K2, u = D.u;
D.B.xCycle = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var R = Math.min(Z.h * 0.4, 170), cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, d = Math.min(62, R * 0.55);
  D.ring(C, cx, cy, R, {color:u.mix(P.bg, P.text, 0.25), lw:3});
  C.ell(cx - R * 0.58, cy - R * 0.58, R * 1.16, R * 1.16, {fill:P.accent});
  C.text(cx - R * 0.5, cy - R * 0.5, R, R, String(C.s.title || '').split(/\s+/).slice(0, 4).join(' '), {font:'head', fit:{max:20, min:11}, color:P.onAccent || '#FFFFFF', align:'c', va:'m', maxLines:3, lh:1.1});
  var left = Math.ceil(n / 2), sw = (Z.w - R * 2 - d - 80) / 2, rows = left, gy = 10, rh = (Z.h - gy * (rows - 1)) / rows;
  var F = K.fitHD(C, it, sw, rh * 0.42, rh, {hMax:18, dMax:13.5, hLines:1});
  var pts = it.map(function(t, k){ return K.pol(cx, cy, R, -90 + k * 360 / n); });
  /* one row slot per text, chosen by the height of its icon; texts of one side never share a slot */
  var slot = [];
  ['r', 'l'].forEach(function(side){
    var ks = []; it.forEach(function(t, k){ if ((k < left ? 'r' : 'l') === side) ks.push(k); });
    ks.sort(function(a, b){ return pts[a][1] - pts[b][1]; });
    var used = -1;
    ks.forEach(function(k, j){
      var want = Math.round((pts[k][1] - Z.y - rh / 2) / (rh + gy));
      want = Math.max(used + 1, Math.min(rows - (ks.length - j), want));
      slot[k] = want; used = want;
    });
  });
  it.forEach(function(t, k){
    var p = pts[k], c = K.col(C, k);
    C.ell(p[0] - d / 2 - 4, p[1] - d / 2 - 4, d + 8, d + 8, {fill:P.bg});
    C.ell(p[0] - d / 2, p[1] - d / 2, d, d, {fill:c});
    K.icon(C, p[0] - d * 0.24, p[1] - d * 0.24, d * 0.48, t, K.on(c));
    var side = k < left ? 'r' : 'l', y = Z.y + slot[k] * (rh + gy), x = side === 'r' ? Z.x + Z.w - sw : Z.x;
    C.text(side === 'r' ? x - 34 : x + sw + 8, y, 26, 22, u.pad2(k + 1), {font:'head', size:15, wt:800, color:c, align:'c', lh:1, caps:false, ls:0});
    K.hd(C, x, y, sw, rh, t, F, {align:side === 'r' ? 'l' : 'r'});
  });
};
})();
</script>
    <?php
}, 1056);