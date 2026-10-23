
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 110 - Helix : a twisting double helix with rungs; on content pages it stands upright as the spine of the story */
var NV = '#03045E', BL = '#0077B6', CY = '#00B4D8', PL = '#90E0EF', CO = '#FF6B6B', MU = '#566089', LN = '#CFE8F3', PALE = '#F0F9FD';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:BL, accent2:CO, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [BL, CY, CO, NV];
function helix(C, a, b, amp, fr, lw, o){
o = o || {}; var dx = b[0] - a[0], dy = b[1] - a[1], L = Math.hypot(dx, dy), nx = -dy / L, ny = dx / L, N = Math.max(40, Math.round(fr * 28)), s1 = [], s2 = [], ru = [];
function pt(q, sg){ var v = amp * Math.sin(q * Math.PI * 2 * fr) * sg; return [a[0] + dx * q + nx * v, a[1] + dy * q + ny * v]; }
for (var i = 0; i <= N; i++) { var q = i / N, u = pt(q, 1), v = pt(q, -1); s1.push(u); s2.push(v); if (i % 4 === 2 && Math.abs(Math.sin(q * Math.PI * 2 * fr)) > 0.3) Q.line(ru, u[0], u[1], v[0], v[1]); }
K5.stroke(C, ru, o.rc || PL, Math.max(1.5, lw * 0.42)); K5.stroke(C, Q.pts([], s2), o.c2 || CY, lw); K5.stroke(C, Q.pts([], s1), o.c1 || BL, lw);
return pt;
}
function bead(C, p, r){ C.ell(p[0] - r - 4, p[1] - r - 4, r * 2 + 8, r * 2 + 8, {fill:'#FFFFFF'}); C.ell(p[0] - r, p[1] - r, r * 2, r * 2, {fill:CO}); }
function cover(C){
var photo = (C.o || {}).img === 'photo', pt;
if (photo) { D.imgBox(C, 900, 0, 380, H, {r:0}); pt = helix(C, [880, -40], [880, 760], 64, 3, 12); }
else { pt = helix(C, [760, 770], [1230, -50], 96, 3, 13); bead(C, pt(0.42, 1), 13); bead(C, pt(0.75, -1), 10); }
K5.coverText(C, 88, 130, 600, {max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo', pt = helix(C, [-40, 590], [1320, 590], 64, 4, 12);
bead(C, pt(0.31, 1), 12); bead(C, pt(0.69, -1), 12);
if (photo) D.imgBox(C, 830, 70, 362, 370, {r:18});
K5.closeText(C, 88, 80, 680, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {fill:[BL, CY, CO][k % 3]}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'helix', name:'Helix', tag:'White · double helix',
desc:'A twisting double helix with rungs crosses the cover in blues with coral markers; on content pages it stands upright as the spine of a pipeline, with base-pair numbers and a helix timeline. For biotech, pharma, research and R&D pipelines.',
fonts:{head:'Albert Sans', body:'Albert Sans', headW:700, bodyW:400, headLh:1.08, gf:'Albert+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:18, imgR:18, coverLbl:'Double helix', closeLbl:'Double helix',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; helix(C, [1244, -20], [1244, 740], 14, 7, 3.5, {c1:PL, c2:LN, rc:LN}); },
cover:cover, closing:closing,
rule:function(C, x, y){ helix(C, [x, y + 5], [x + 84, y + 5], 6, 2, 2.5); return 24; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : PALE, r:18});
if (!o.big) { C.line(x + w - 56, y + 30, x + w - 30, y + 30, {color:hot ? PL : CY, lw:3}); C.ell(x + w - 64, y + 22, 16, 16, {fill:hot ? '#FFFFFF' : BL}); C.ell(x + w - 36, y + 22, 16, 16, {fill:CO}); }
return hot ? {tc:'#FFFFFF', mc:'#C5D6EE', ac:PL, hot:1} : {tc:NV, mc:MU, ac:BL};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? CO : [BL, CY, NV][k % 3]}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.line(x + s * 0.2, y + s / 2, x + s * 1.1, y + s / 2, {color:PL, lw:3}); C.ell(x, y + s * 0.1, s * 0.8, s * 0.8, {fill:k % 2 ? CY : BL}); C.ell(x + s * 0.9, y + s * 0.32, s * 0.36, s * 0.36, {fill:CO}); num(C, x, y + s * 0.1, s * 0.8, s * 0.8, k, s * 0.32, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2 - 4, cy - s / 2 - 4, s + 8, s + 8, {fill:'#FFFFFF'}); C.ell(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? CY : BL}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ helix(C, [x1, y], [x2, y], 7, Math.max(2, Math.round((x2 - x1) / 80)), 2.5, {c1:PL, c2:LN, rc:LN}); },
L:{cards:'Cell cards', rows:'Strand rows', steps:'Pair steps', stmt:'Lab note', stats:'Plain numbers'},
sig:function(pg, B){
function spine(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 700) return B.rows(C, Z, o);
var ax = Z.x + 44, rh = Z.h / n, lx = Z.x + 150, tw = Z.x + Z.w - lx, pt = helix(C, [ax, Z.y], [ax, Z.y + Z.h], 28, n / 2, 7);
var F = K.fitHD(C, it, tw, 34, rh - 14, {hMax:22, dMax:16.5, hMin:12, dMin:10, noBoost:n > 4});
it.forEach(function(t, k){
var q = (k + 0.5) / n, sg = Math.sin(q * Math.PI * n) > 0 ? -1 : 1, p = pt(q, sg), yi = Z.y + rh * (k + 0.5), bh = K3.bH(C, t, tw, F);
C.line(p[0], yi, lx - 20, yi, {color:CO, lw:2}); bead(C, [p[0], yi], 15); num(C, p[0] - 15, yi - 15, 30, 30, k, 12, '#FFFFFF', String(k + 1));
K.hd(C, lx, yi - bh / 2, tw, rh - 6, t, F, {tc:NV, mc:MU});
});
}
function pairs(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.52), tg:18, vMax:42, vw:function(cw, ah){ return Math.min(cw * 0.5, ah * 0.7); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.3, b.ah * 0.42), cy = b.y + b.ah * 0.46, cx = b.cx - r * 0.3;
C.line(cx, cy, cx + r * 1.6, cy + r * 0.6, {color:PL, lw:8}); C.ell(cx + r * 1.24, cy + r * 0.24, r * 0.72, r * 0.72, {fill:CO}); C.ell(cx - r, cy - r, r * 2, r * 2, {fill:k % 2 ? CY : BL});
return {vc:'#FFFFFF', vx:cx - r * 0.92, vw:r * 1.84, vy:cy - b.vs * 0.58};
});
}
function twist(C, Z, o){
var pt;
K5.alt(C, Z, {off:74, fallback:B.rows, pre:function(C, g){ pt = helix(C, [g.x, g.y], [g.x + g.w, g.y], 34, g.n / 2, 8); }}, function(C, k, t, b){
var y = b.cy + (b.up ? 34 : -34); bead(C, [b.cx, y], 20); num(C, b.cx - 20, y - 20, 40, 40, k, 15, '#FFFFFF');
});
}
return {agenda:[['Helix spine', pg(spine)]], p2:[['Helix spine', pg(spine)]], p3:[['Helix spine', pg(spine)]], p4:[['Helix spine', pg(spine)]], p56:[['Helix spine', pg(spine)]],
stats:[['Base pairs', pg(pairs)]], timeline:[['Helix line', pg(twist)]]};
}
});
})();
