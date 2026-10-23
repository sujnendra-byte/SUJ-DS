
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 92 - Sunburst : wide rays fan out from a sun in one corner; on content pages the rays carry each point */
var NV = '#102A54', NV2 = '#1B4282', GD = '#FFB703', MU = '#5A6A85', LN = '#D9E0EC', PALE = '#F1F5FB';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:NV, accent2:GD, card:'#F3F6FB', line:LN, onAccent:'#FFFFFF'};
var LC = [NV, GD, NV2, '#F08A00', '#5C7CB8'];
function rays(C, cx, cy, R, a0, a1, n, color, op){ var d = [], st = (a1 - a0) / n; for (var i = 0; i < n; i++) { var a = a0 + st * i; Q.poly(d, [[cx, cy], K5.pol(cx, cy, R, a), K5.pol(cx, cy, R, a + st / 2)]); } K5.fill(C, d, color, op); }
function sun(C, cx, cy, r){ C.ell(cx - r * 1.3, cy - r * 1.3, r * 2.6, r * 2.6, {line:GD, lw:Math.max(3, r * 0.03)}); C.ell(cx - r, cy - r, r * 2, r * 2, {fill:GD}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:NV}); rays(C, W, H, 1700, 180, 270, 9, NV2);
if (photo) { C.ell(846, 266, 368, 368, {line:GD, lw:6}); D.imgBox(C, 870, 290, 320, 320, {shape:'ell'}); } else sun(C, W, H, 190);
K5.coverText(C, 96, 100, photo ? 660 : 760, {tc:'#FFFFFF', sc:'#C9D6EE', kc:GD, max:photo ? 54 : 62, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:NV}); rays(C, W, 0, 1700, 90, 180, 9, NV2);
if (photo) { C.ell(866, 186, 348, 348, {line:GD, lw:6}); D.imgBox(C, 890, 210, 300, 300, {shape:'ell'}); } else sun(C, W, 0, 170);
K5.closeText(C, 96, 200, 700, {tc:'#FFFFFF', sc:'#C9D6EE', mc:GD, mi:NV});
}
K5.make({
id:'sunburst', name:'Sunburst', tag:'Ink blue · corner sun rays',
desc:'Wide rays fan out from a golden sun in one corner of an ink-blue cover; on white content pages the rays become the lines that carry each point, with rising-sun numbers and a sun path timeline. For vision, launch, kick-off and new-year plans.',
fonts:{head:'Plus Jakarta Sans', body:'Plus Jakarta Sans', headW:800, bodyW:400, headLh:1.08, gf:'Plus+Jakarta+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:6, coverLbl:'Corner sun', closeLbl:'Corner sun',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; rays(C, W, 0, 360, 90, 180, 7, PALE); C.ell(W - 104, -104, 208, 208, {line:GD, lw:3}); C.ell(W - 74, -74, 148, 148, {fill:GD}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:GD, r:2.5}); return 16; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot, f = hot ? NV : '#F3F6FB';
C.rect(x, y, w, h, {fill:f, r:6});
if (!o.big) K5.fill(C, Q.sector([], x + w, y, 0, 44, 90, 180), GD);
return hot ? {tc:'#FFFFFF', mc:'#C9D6EE', ac:GD, hot:1} : {tc:NV, mc:MU, ac:NV};
},
mark:function(C, x, y, s, t, k, cc){ C.ell(x, y, s, s, {fill:GD}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, NV); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:GD}); C.text(x, y, s, s, pad2(k + 1), {font:'head', size:s * 0.36, wt:800, color:NV, align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2 - 6, cy - s / 2 - 6, s + 12, s + 12, {line:GD, lw:2}); C.ell(cx - s / 2, cy - s / 2, s, s, {fill:GD}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.36, wt:800, color:NV, align:'c', va:'m', lh:1}); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:NV, r:2}); },
imgR:8,
L:{cards:'Sunrise cards', rows:'Sun rows', steps:'Sun steps', stmt:'Daybreak note', stats:'Bright numbers'},
sig:function(pg, B){
function rayList(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 760) return B.rows(C, Z, o);
var R = Math.min(Z.h * 0.3, 118), cx = Z.x + R * 1.3, cy = Z.y + Z.h / 2, lx = cx + R * 1.3 + 110, tw = Z.x + Z.w - lx, rh = Z.h / n, sp = Math.min(62, 14 * n);
var F = K.fitHD(C, it, tw, 34, rh - 14, {hMax:22, dMax:16.5, hMin:12, dMin:10, noBoost:n > 4});
rays(C, cx, cy, R * 1.7, 0, 360, 16, PALE);
it.forEach(function(t, k){
var yi = Z.y + rh * (k + 0.5), a = n > 1 ? -sp + 2 * sp * k / (n - 1) : 0, p = K5.pol(cx, cy, R * 1.34, a), bh = K3.bH(C, t, tw, F);
C.line(p[0], p[1], lx - 30, yi, {color:NV, lw:2}); C.ell(lx - 38, yi - 8, 16, 16, {fill:GD});
K.hd(C, lx, yi - bh / 2, tw, rh - 6, t, F, {tc:NV, mc:MU});
});
sun(C, cx, cy, R); C.text(cx - R, cy - R, R * 2, R * 2, pad2(n), {font:'head', size:R * 0.62, wt:800, color:NV, align:'c', va:'m', lh:1});
}
function sunStats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.5), tg:22, vMax:44, vw:function(cw, ah){ return Math.min(cw * 0.5, 200); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.36, b.ah - 44), by = b.y + b.ah, d = [];
for (var a = 180; a <= 360; a += 22.5) { var p1 = K5.pol(b.cx, by, r + 12, a), p2 = K5.pol(b.cx, by, r + 30, a); Q.line(d, p1[0], p1[1], p2[0], p2[1]); }
K5.stroke(C, d, NV, 4); K5.fill(C, Q.sector([], b.cx, by, 0, r, 180, 360), GD); C.rect(b.x + 12, by - 2, b.w - 24, 4, {fill:NV, r:2});
return {vc:NV, vx:b.cx - r * 0.8, vw:r * 1.6, vy:by - r * 0.36 - b.vs * 0.62};
});
}
function sunPath(C, Z, o){
var mid = Z.x + Z.w / 2, rx = Z.w / 2 - 10, ry = Z.h * 0.3;
K5.alt(C, Z, {same:true, at:0.42, off:26, fallback:B.rows, pre:function(C, g){
var pts = []; for (var i = 0; i <= 48; i++) { var a = Math.PI * (1 + i / 48); pts.push([mid + rx * Math.cos(a), g.y + ry * Math.sin(a)]); }
K5.stroke(C, Q.pts([], pts), GD, 4); C.rect(g.x, g.y - 2, g.w, 4, {fill:NV, r:2});
}}, function(C, k, t, b){
var uu = Math.max(-0.98, Math.min(0.98, (b.cx - mid) / rx)), ny = b.cy - ry * Math.sqrt(1 - uu * uu);
C.line(b.cx, ny, b.cx, b.cy, {color:NV, lw:1.5, dash:true});
C.ell(b.cx - 22, ny - 22, 44, 44, {fill:GD}); C.text(b.cx - 22, ny - 22, 44, 44, pad2(k + 1), {font:'head', size:16, wt:800, color:NV, align:'c', va:'m', lh:1});
});
}
return {agenda:[['Ray list', pg(rayList)]], p2:[['Ray list', pg(rayList)]], p3:[['Ray list', pg(rayList)]], p4:[['Ray list', pg(rayList)]], p56:[['Ray list', pg(rayList)]],
stats:[['Rising suns', pg(sunStats)]], timeline:[['Sun path', pg(sunPath)]]};
}
});
})();
