<?php
/**
 * Code Snippets export — DS Template Kit 3 (shared for T66-T73)
 *
 * Snippet ID : 1098
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 05:41:09
 * Load order : 268 of 296
 */
/**
 * DS Template Kit 3 - shared drawing helpers for design templates 66-73
 * Run: Everywhere
 * Needs "DS Template Engine (T00)" and "DS Template Kit 2". NEW SNIPPET (nothing existing is changed).
 * Undo: deactivate this snippet together with templates 66-73.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-kit3">
/* DS Template Kit 3 — shared helpers for templates 66-73 (new snippet, changes nothing else) */
(function(){
var D = window.DSTE; if (!D || !D.K2 || D.K3) return;
var K = D.K2, W = D.W, H = D.H, u = D.u, pad2 = u.pad2, RAD = Math.PI / 180;
function bH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* rounded polygon (corner radius r) as a path */
function rpoly(C, pts, r, o){
  var n = pts.length, d = [];
  for (var i = 0; i < n; i++) {
    var p0 = pts[(i - 1 + n) % n], p1 = pts[i], p2 = pts[(i + 1) % n];
    var l1 = Math.hypot(p1[0] - p0[0], p1[1] - p0[1]), l2 = Math.hypot(p2[0] - p1[0], p2[1] - p1[1]), rr = Math.min(r, l1 / 2, l2 / 2);
    var a = [p1[0] + (p0[0] - p1[0]) * rr / l1, p1[1] + (p0[1] - p1[1]) * rr / l1], b = [p1[0] + (p2[0] - p1[0]) * rr / l2, p1[1] + (p2[1] - p1[1]) * rr / l2];
    d.push([i ? 'L' : 'M', a[0], a[1]]); d.push(['C', p1[0], p1[1], p1[0], p1[1], b[0], b[1]]);
  }
  d.push(['Z']); return C.path(d, o);
}
/* square of side s centred on cx,cy rotated by deg */
function sq(cx, cy, s, deg){ var r = s / Math.SQRT2, a = (deg || 0) + 45, p = []; for (var k = 0; k < 4; k++) p.push([cx + r * Math.cos((a + 90 * k) * RAD), cy + r * Math.sin((a + 90 * k) * RAD)]); return p; }
/* flat-top hexagon points */
function hexF(cx, cy, r){ var p = []; for (var k = 0; k < 6; k++) p.push([cx + r * Math.cos(60 * k * RAD), cy + r * Math.sin(60 * k * RAD)]); return p; }
/* photo cut to a flat-top hexagon: photo box + corner masks in the page colour + rim */
function hexPhoto(C, cx, cy, r, bgc, rim, lw){
  var a = r * 0.866;
  D.imgBox(C, cx - r, cy - a, r * 2, a * 2, {r:0});
  var e = 2;
  K.poly(C, [[cx - r - e, cy - a - e], [cx - r / 2, cy - a - e], [cx - r - e, cy]], {fill:bgc});
  K.poly(C, [[cx + r / 2, cy - a - e], [cx + r + e, cy - a - e], [cx + r + e, cy]], {fill:bgc});
  K.poly(C, [[cx + r + e, cy], [cx + r + e, cy + a + e], [cx + r / 2, cy + a + e]], {fill:bgc});
  K.poly(C, [[cx - r / 2, cy + a + e], [cx - r - e, cy + a + e], [cx - r - e, cy]], {fill:bgc});
  if (rim) K.poly(C, hexF(cx, cy, r), {stroke:rim, lw:lw || 8});
}
/* page composition: own header, own photo frame, body, takeaway */
function page(C, o, X){
  o = o || {}; var M = X.M || 72, A = {x:M, w:W - 2 * M}, bottom = H - (X.footH || 64), ip = o.img;
  if (ip === 'right' || ip === 'left') {
    var iw = Math.round(W * (o.imgPct || X.imgPct || 0.38)), Zi = {x:ip === 'right' ? W - iw : 0, y:0, w:iw, h:H};
    X.photo(C, Zi, ip);
    A.x = ip === 'right' ? M : iw + 48; A.w = W - iw - M - 48;
  }
  var top = X.head(C, A, o);
  if (ip === 'bottom') { var bh = Math.round(H * 0.27); X.photo(C, {x:0, y:H - bh, w:W, h:bh}, 'bottom'); bottom = H - bh - 22; }
  var concH = C.s.conclusion && o.conc !== false ? 52 : 0;
  var zTop = top + (X.gap || 26), zBot = bottom - (concH ? concH + 16 : 0);
  var Z = {x:A.x, y:zTop, w:A.w, h:Math.max(80, zBot - zTop)};
  var fn = typeof o.body === 'function' ? o.body : (D.B[o.body || 'cols']);
  if (fn) fn(C, Z, o);
  if (concH) D.conclusion(C, A.x, zBot + 16, A.w, concH);
  return Z;
}
/* area the footer may use (skips a full-height photo) */
function footArea(C){ var o = C.o || {}, iw = Math.round(W * (o.imgPct || 0.38)), M = 72; if (o.img === 'right') return {x:M, w:W - iw - M - 40}; if (o.img === 'left') return {x:iw + 40, w:W - iw - 40 - M}; return {x:M, w:W - 2 * M}; }

/* skinned body layouts. X: card(C,x,y,w,h,k,o)->{tc,mc,ac}; mark(C,x,y,s,t,k,cc); num(C,x,y,s,k,cc) */
function make(X){
  var ms = X.markS || 46;
  function cc0(C){ return {tc:X.tc || C.P.text, mc:X.mc || C.P.muted, ac:X.ac || C.P.accent}; }
  function cards(C, Z, o){
    var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
    var cl = o.cols || (n <= 3 ? n : (n === 4 ? (Z.w > 900 ? 4 : 2) : (n <= 6 ? 3 : 4))); if (Z.w < 720) cl = Math.min(cl, 2);
    var rw = Math.ceil(n / cl), g = X.g || 18, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = X.pad || 24, side = rw > 1 && cw > 300, s = Math.min(ms, ch * 0.34);
    var tw = side ? cw - pad * 2 - s - 16 : cw - pad * 2;
    var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - s - 16, {hMax:o.hMax || 26, dMax:o.dMax || (it.some(function(t){ return t.h; }) ? 20 : 24), hMin:12, dMin:10});
    it.forEach(function(t, k){
      var r = Math.floor(k / cl), c = k % cl, rowN = Math.min(cl, n - r * cl), off = (cl - rowN) * (cw + g) / 2;
      var x = Z.x + off + c * (cw + g), y = Z.y + r * (ch + g), cc = X.card(C, x, y, cw, ch, k, {hot:o.hl && k === 0}) || cc0(C);
      var al = o.center && !side ? 'c' : 'l';
      if (side) { var bh = Math.max(bH(C, t, tw, F), s), yy = y + Math.max(pad, (ch - bh) / 2); X.mark(C, x + pad, yy, s, t, k, cc); K.hd(C, x + pad + s + 16, yy, tw, ch - (yy - y) - pad / 2, t, F, {tc:cc.tc, mc:cc.mc}); }
      else { var bh2 = bH(C, t, tw, F) + s + 16, y2 = y + Math.max(pad, (ch - bh2) / 2); X.mark(C, al === 'c' ? x + (cw - s) / 2 : x + pad, y2, s, t, k, cc); K.hd(C, x + pad, y2 + s + 16, tw, ch - (y2 - y) - s - 16 - pad / 2, t, F, {tc:cc.tc, mc:cc.mc, align:al}); }
    });
  }
  function rows(C, Z, o){
    var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
    var cl = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cl), g = X.rg || 12, gx = 22, cw = (Z.w - gx * (cl - 1)) / cl;
    var rh = Math.min((Z.h - g * (per - 1)) / per, 150), s = Math.min(ms, rh * 0.62), pad = 20, tw = cw - pad * 2 - s - 18;
    var F = K.fitHD(C, it, tw, 34, rh - 16, {hMax:22, dMax:it.some(function(t){ return t.h; }) ? 17 : 21, hMin:12, dMin:10, noBoost:n > 5});
    var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
    it.forEach(function(t, k){
      var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g), cc = X.row ? X.row(C, x, y, cw, rh, k) : X.card(C, x, y, cw, rh, k, {});
      cc = cc || cc0(C);
      (X.rowMark || X.mark)(C, x + pad, y + (rh - s) / 2, s, t, k, cc);
      var bh = bH(C, t, tw, F); K.hd(C, x + pad + s + 18, y + Math.max(6, (rh - bh) / 2), tw, rh - 8, t, F, {tc:cc.tc, mc:cc.mc});
    });
  }
  function stats(C, Z, o){
    var st = C.it.slice(0, 4), n = st.length; if (!n) return;
    var cl = Z.w < 760 ? (n > 2 ? 2 : 1) : n, rw = Math.ceil(n / cl), g = 20, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = 26, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
    var vs = C.groupFit(vals, tw, ch * 0.36, {font:'head', max:64, min:22, maxLines:1});
    var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12, maxLines:2});
    var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs * 1.1 - 120, {font:'body', max:14, min:10, lh:1.38});
    st.forEach(function(t, k){
      var r = Math.floor(k / cl), c = k % cl, x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), cc = X.card(C, x, y, cw, ch, k, {hot:k === (o.hot != null ? o.hot : -1), stat:true}) || cc0(C);
      var al = o.center ? 'c' : 'l', yy = y + Math.max(pad, (ch - vs * 1.1 - 80) / 2.6);
      C.text(x + pad, yy, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:cc.ac, lh:1, caps:false, align:al, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.rect(al === 'c' ? x + cw / 2 - 20 : x + pad, yy + vs * 1.1 + 10, 40, 3, {fill:cc.ac, op:0.8});
      var le = C.text(x + pad, yy + vs * 1.1 + 24, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:cc.tc, lh:1.15, align:al, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
      if (t.x) C.text(x + pad, yy + vs * 1.1 + 32 + le._h, tw, y + ch - pad - (yy + vs * 1.1 + 32 + le._h), t.x, {font:'body', size:ds, color:cc.mc, lh:1.38, align:al, ed:{f:'x-part', i:t.i, p:2}});
    });
  }
  function steps(C, Z, o){
    var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
    var cw = Z.w / n, s = Math.min(64, cw * 0.36), ly = Z.y + s / 2 + 4, tw = cw - 24;
    var F = K.fitHD(C, it, tw, 50, Z.h - s - 40, {hMax:22, dMax:17, hMin:12, dMin:10});
    var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, bH(C, t, tw, F)); });
    if (X.track) X.track(C, Z.x + cw / 2, ly, Z.x + Z.w - cw / 2, ly); else C.rect(Z.x + cw / 2, ly - 1.5, Z.w - cw, 3, {fill:X.ac || C.P.accent, op:0.5});
    it.forEach(function(t, k){
      var cx = Z.x + cw * (k + 0.5), cc = {tc:X.ptc || C.P.text, mc:X.pmc || C.P.muted};
      (X.node || function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:X.ac || C.P.accent}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.36, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); })(C, cx, ly, s, k);
      if (X.stepCard) X.stepCard(C, cx - cw / 2 + 6, Z.y + s + 22, cw - 12, Math.min(Z.h - s - 22, bmx + 44), k);
      K.hd(C, cx - tw / 2, Z.y + s + 40, tw, Z.h - s - 50, t, F, {tc:cc.tc, mc:cc.mc, align:'c'});
    });
  }
  function statement(C, Z, o){
    var it = C.it[0]; if (!it) return;
    var cc = X.card(C, Z.x, Z.y, Z.w, Z.h, 0, {big:true}) || cc0(C), pad = 50, w = Z.w - pad * 2;
    var hs = it.h ? C.measureText(it.h, w, {font:'head', max:38, min:18, h:84, maxLines:2}) : null;
    var avail = Z.h - pad * 2 - (hs ? hs.h + 16 : 0);
    var bs = C.measureText(it.d, w, {font:'body', max:it.h ? 32 : 36, min:14, h:avail, lh:1.48});
    var y = Z.y + Math.max(pad - 8, (Z.h - (hs ? hs.h + 16 : 0) - bs.h) / 2);
    if (hs) { C.text(Z.x + pad, y, w, hs.h + 4, it.h, {font:'head', size:hs.s, wt:800, color:cc.ac, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += hs.h + 16; }
    C.text(Z.x + pad, y, w, Math.min(avail, bs.h + 8), it.d, {font:'body', size:bs.s, color:cc.tc, lh:1.48, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
  }
  return {cards:cards, rows:rows, stats:stats, steps:steps, statement:statement};
}
D.K3 = {bH:bH, sv:sv, sl:sl, rpoly:rpoly, sq:sq, hexF:hexF, hexPhoto:hexPhoto, page:page, make:make, footArea:footArea};
})();

</script>
<?php
}, 1080);
