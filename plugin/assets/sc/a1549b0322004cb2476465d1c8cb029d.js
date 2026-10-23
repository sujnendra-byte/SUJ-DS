
/* DS Template Kit 4 — shared helpers for templates 74-89 (new snippet, changes nothing else) */
(function(){
var D = window.DSTE; if (!D || !D.K2 || !D.K3 || D.K4) return;
var K = D.K2, K3 = D.K3, V = D.V, W = D.W, H = D.H, RAD = Math.PI / 180;
function unit(a, b){ var dx = b[0] - a[0], dy = b[1] - a[1], l = Math.hypot(dx, dy) || 1; return [dx / l, dy / l]; }
/* parallel route lines: one centre polyline, every lane offset by the same perpendicular distance,
   corners rounded with concentric arcs so the gap stays exactly equal through every bend */
function lanes(C, pts, o){
  var n = o.colors.length, gap = o.gap, lw = o.lw, R = o.r || gap * 2.2, mid = (n - 1) / 2;
  for (var j = 0; j < n; j++) {
    var off = (j - mid) * gap, P = [];
    for (var i = 0; i < pts.length; i++) {
      var a = i ? unit(pts[i - 1], pts[i]) : unit(pts[0], pts[1]), b = i < pts.length - 1 ? unit(pts[i], pts[i + 1]) : a;
      var na = [-a[1], a[0]], nb = [-b[1], b[0]], m = [na[0] + nb[0], na[1] + nb[1]], ml = Math.hypot(m[0], m[1]) || 1;
      m = [m[0] / ml, m[1] / ml]; var c = m[0] * na[0] + m[1] * na[1] || 1;
      P.push([pts[i][0] + m[0] * off / c, pts[i][1] + m[1] * off / c]);
    }
    var d = [['M', P[0][0], P[0][1]]];
    for (var k = 1; k < P.length - 1; k++) {
      var u1 = unit(P[k - 1], P[k]), u2 = unit(P[k], P[k + 1]);
      var cross = u1[0] * u2[1] - u1[1] * u2[0], dot = u1[0] * u2[0] + u1[1] * u2[1], phi = Math.acos(Math.max(-1, Math.min(1, dot)));
      var r = Math.max(4, R + (cross > 0 ? -off : off));
      var t = r * Math.tan(phi / 2), kk = 4 / 3 * Math.tan(phi / 4) * r;
      var A = [P[k][0] - u1[0] * t, P[k][1] - u1[1] * t], Bp = [P[k][0] + u2[0] * t, P[k][1] + u2[1] * t];
      d.push(['L', A[0], A[1]]); d.push(['C', A[0] + u1[0] * kk, A[1] + u1[1] * kk, Bp[0] - u2[0] * kk, Bp[1] - u2[1] * kk, Bp[0], Bp[1]]);
    }
    d.push(['L', P[P.length - 1][0], P[P.length - 1][1]]);
    C.path(d, {stroke:o.colors[j], lw:lw});
  }
}
/* bullseye with an arrow whose tip sits exactly on the centre */
function target(C, cx, cy, r, o){
  o = o || {}; var ink = o.ink || '#111111', ac = o.arrow || '#1F3A8A', lw = Math.max(4, r * 0.11), ang = (o.ang != null ? o.ang : -147) * RAD;
  C.ell(cx - r, cy - r, r * 2, r * 2, {line:ink, lw:lw, fill:o.fill || null});
  C.ell(cx - r * 0.56, cy - r * 0.56, r * 1.12, r * 1.12, {line:ink, lw:lw});
  C.ell(cx - r * 0.18, cy - r * 0.18, r * 0.36, r * 0.36, {fill:ink});
  var L = r * 1.9, ux = Math.cos(ang), uy = Math.sin(ang), px = -uy, py = ux, hl = r * 0.42, hw = r * 0.2;
  var tail = [cx + ux * L, cy + uy * L], neck = [cx + ux * hl, cy + uy * hl];
  C.path([['M', neck[0], neck[1]], ['L', tail[0], tail[1]]], {stroke:ac, lw:Math.max(3, r * 0.1)});
  K.poly(C, [[cx, cy], [neck[0] + px * hw, neck[1] + py * hw], [neck[0] - px * hw, neck[1] - py * hw]], {fill:ac});
  var f0 = [cx + ux * (L - r * 0.3), cy + uy * (L - r * 0.3)], fw = r * 0.13;
  K.poly(C, [[f0[0] + px * fw * 0.3, f0[1] + py * fw * 0.3], [tail[0] + ux * r * 0.08 + px * fw * 1.5, tail[1] + uy * r * 0.08 + py * fw * 1.5], [tail[0] + ux * r * 0.3 + px * fw * 0.9, tail[1] + uy * r * 0.3 + py * fw * 0.9], [tail[0] + ux * r * 0.08, tail[1] + uy * r * 0.08]], {fill:ac});
  K.poly(C, [[f0[0] - px * fw * 0.3, f0[1] - py * fw * 0.3], [tail[0] + ux * r * 0.08 - px * fw * 1.5, tail[1] + uy * r * 0.08 - py * fw * 1.5], [tail[0] + ux * r * 0.3 - px * fw * 0.9, tail[1] + uy * r * 0.3 - py * fw * 0.9], [tail[0] + ux * r * 0.08, tail[1] + uy * r * 0.08]], {fill:ac});
}
/* tick ring (compass / dial) */
function ticks(C, cx, cy, r, n, len, o){ o = o || {}; for (var i = 0; i < n; i++) { var a = (i * 360 / n + (o.a0 || 0)) * RAD, l = (o.major && i % o.major === 0) ? len * 1.8 : len; C.line(cx + Math.cos(a) * r, cy + Math.sin(a) * r, cx + Math.cos(a) * (r - l), cy + Math.sin(a) * (r - l), {color:o.color || '#000', lw:o.lw || 1.2, op:o.op}); } }
/* standard variant sets from a template's skinned bodies; sig adds the template's own layouts first */
function vset(pg, B, L, sig){
  sig = sig || {}; var P = L.photo, base = B.steps;
  /* steps sit in a centred band so short timelines do not leave the lower half empty */
  B = {cards:B.cards, rows:B.rows, stats:B.stats, statement:B.statement, steps:function(C, Z, o){ var h = Math.min(Z.h, Math.max(250, Z.h * 0.68)); return base(C, {x:Z.x, y:Z.y + (Z.h - h) * 0.42, w:Z.w, h:h}, o); }};
  var d = {
    agenda:[[L.rows, pg(B.rows, {cols:2})], [L.cards, pg(B.cards)], [L.rows + ' + ' + P, pg(B.rows, {img:'right', cols:1})]],
    text:[[L.stmt, pg(B.statement)], [L.stmt + ' + ' + P, pg(B.statement, {img:'right'})], [P + ' + ' + L.stmt.toLowerCase(), pg(B.statement, {img:'left'})]],
    p2:[[L.cards, pg(B.cards, {hl:true})], [L.rows, pg(B.rows)], [L.rows + ' + ' + P, pg(B.rows, {img:'right'})], [L.cards + ' + photo strip', pg(B.cards, {img:'bottom'})]],
    p3:[[L.cards, pg(B.cards)], [L.steps, pg(B.steps)], [L.rows, pg(B.rows)], [L.rows + ' + ' + P, pg(B.rows, {img:'right'})], [P + ' + ' + L.rows.toLowerCase(), pg(B.rows, {img:'left'})], [L.cards + ' + photo strip', pg(B.cards, {img:'bottom'})]],
    p4:[[L.cards, pg(B.cards)], [L.rows, pg(B.rows, {cols:2})], [L.steps, pg(B.steps)], [L.rows + ' + ' + P, pg(B.rows, {img:'right'})]],
    p56:[[L.cards, pg(B.cards)], [L.rows, pg(B.rows)], [L.rows + ' + ' + P, pg(B.rows, {img:'right', cols:1})]],
    p78:[[L.cards, pg(B.cards)], [L.rows, pg(B.rows)], [L.rows + ' + ' + P, pg(B.rows, {img:'right', cols:1, max:8})]],
    stats:[[L.stats, pg(B.stats, {center:true})], ['Progress rings', {body:'statRings', track:L.track}], ['Progress bars', {body:'statBars'}], [L.stats + ' + ' + P, pg(B.stats, {img:'right'})]],
    timeline:[[L.steps, pg(B.steps)], [L.rows, pg(B.rows)], [L.rows + ' + ' + P, pg(B.rows, {img:'right', cols:1})], [L.steps + ' + photo strip', pg(B.steps, {img:'bottom'})]]
  };
  var out = {};
  function ids(list){ var seen = {}, vi = 1, ni = 1, r = []; list.forEach(function(e, k){ if (seen[e[0]]) return; seen[e[0]] = 1; var id = !r.length ? 'v1' : (e[1].img ? 'v' + (++vi) : 'n' + (++ni)); r.push(V(id, e[0], e[1])); }); return r; }
  Object.keys(d).forEach(function(k){ out[k] = ids((sig[k] || []).concat(d[k])); });
  ['cover', 'closing', 'quote'].forEach(function(k){ out[k] = ids(sig[k] || [[k, {}]]); });
  return out;
}
D.K4 = {lanes:lanes, target:target, ticks:ticks, vset:vset, unit:unit};
})();

