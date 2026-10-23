
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 126 - Herringbone : the slanted zigzag weave of a tailored suit, tone on tone in graphite, cut by one thin red line */
var GF = '#2B2D42', G2 = '#3A3D5C', RD = '#EF233C', SL = '#8D99AE', BG = '#EDF2F4', MU = '#5F6477', LN = '#D3D9DE';
var P = {bg:BG, text:GF, title:GF, muted:MU, accent:GF, accent2:RD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [GF, RD, SL, '#D90429'];
function herr(C, x, w, cw, col, op){
var d = [], n = Math.round(w / cw), st = cw * 0.62, th = cw * 0.36, sl = cw * 0.5; cw = w / n;
for (var i = 0; i < n; i++) for (var y = -sl - st; y < H + st; y += st) { var x0 = x + i * cw; if (i % 2) Q.poly(d, [[x0, y], [x0 + cw, y + sl], [x0 + cw, y + sl + th], [x0, y + th]]); else Q.poly(d, [[x0, y + sl], [x0 + cw, y], [x0 + cw, y + th], [x0, y + sl + th]]); }
K5.fill(C, d, col, op);
}
function vee(C, x, y, w, col, n, st){ var d = [], h = w * 0.3, th = w * 0.2; for (var i = 0; i < (n || 1); i++) { var yy = y + i * (st || th * 1.7); Q.poly(d, [[x, yy], [x + w / 2, yy + h], [x + w / 2, yy + h + th], [x, yy + th]]); Q.poly(d, [[x + w / 2, yy + h], [x + w, yy], [x + w, yy + th], [x + w / 2, yy + h + th]]); } K5.fill(C, d, col); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 0, 500, H, {fill:GF}); herr(C, 0, 500, 50, G2); C.rect(500, 0, 8, H, {fill:RD});
if (photo) { C.rect(64, 94, 372, 532, {fill:BG}); D.imgBox(C, 76, 106, 348, 508, {r:0}); }
K5.coverText(C, 584, 150, 610, {max:54, by:640, rule:function(C, x, y){ C.rect(x, y, 80, 4, {fill:RD}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.rect(800, 0, 480, H, {fill:GF}); herr(C, 800, 480, 48, G2); C.rect(792, 0, 8, H, {fill:RD});
if (photo) { C.rect(860, 110, 360, 500, {fill:BG}); D.imgBox(C, 872, 122, 336, 476, {r:0}); }
K5.closeText(C, 88, 150, 640, {mc:GF});
}
K5.make({
id:'herringbone', name:'Herringbone', tag:'Graphite · tailored herringbone',
desc:'The slanted zigzag weave of a tailored suit, tone on tone in deep graphite, cut by one thin red line, with cool grey pages, chevron markers and sharp square cards. Understated and senior, for executive briefings, law, consulting and luxury retail.',
fonts:{head:'Libre Franklin', body:'Libre Franklin', headW:700, bodyW:400, headLh:1.08, gf:'Libre+Franklin:wght@400;500;600;700;800'},
P:P, LC:LC, M:92, cardR:0, coverLbl:'Woven panel', closeLbl:'Woven panel',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.rect(0, 0, 44, H, {fill:GF}); herr(C, 0, 44, 22, G2); C.rect(44, 0, 4, H, {fill:RD}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 64, 4, {fill:RD}); return 16; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? GF : '#FFFFFF', line:hot ? null : LN, lw:1.5}); if (!o.big) vee(C, x + w - 62, y + 18, 40, hot ? RD : SL, 2); else C.rect(x, y, 6, h, {fill:RD});
return hot ? {tc:'#FFFFFF', mc:'#C9CEDB', ac:'#FFFFFF', hot:1} : {tc:GF, mc:MU, ac:RD};
},
row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1.5, w, 1.5, {fill:SL}); return {tc:GF, mc:MU, ac:RD}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? RD : GF}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.text(x - 8, y, s + 16, s, pad2(k + 1), {font:'head', size:s * 0.62, wt:800, color:RD, align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:GF}); C.rect(cx - s / 2, cy + s / 2 - 5, s, 5, {fill:RD}); num(C, cx - s / 2, cy - s / 2, s, s - 4, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 1, x2 - x1, 2, {fill:SL}); },
conc:{kind:'box', fill:GF, r:0, label:'The point', lc:'#FF8A97', tc:'#FFFFFF'},
L:{cards:'Lapel cards', rows:'Ruled rows', steps:'Cuff steps', stmt:'Tailor note', stats:'Plain numbers'},
sig:function(pg, B){
function tailored(C, Z, o){
K5.cols(C, Z, {max:5, ah:146, gap:30, tg:18, fallback:B.rows}, function(C, k, t, b){
vee(C, b.x, b.y, 76, GF, 5, 24); vee(C, b.x, b.y + 48, 76, RD, 1); K.icon(C, b.x + 96, b.y + 6, 38, t, GF);
C.text(b.x + 94, b.y + 70, 100, 62, pad2(k + 1), {font:'head', size:46, wt:800, color:RD, lh:1, va:'b'}); C.rect(b.x, b.y + 142, b.w, 1.5, {fill:SL});
});
}
function stitched(C, Z, o){
K5.stat(C, Z, {ah:Math.min(200, Z.h * 0.5), tg:20, vMax:52, vw:function(cw){ return cw - 70; }}, function(C, k, t, b){
var w = b.w - 16, n = Math.max(3, Math.floor((w - 20) / 34)), vw = (w - 20) / n, i;
C.rect(b.x + 8, b.y, w, b.ah, {fill:GF}); for (i = 0; i < n; i++) vee(C, b.x + 18 + i * vw, b.y + 12, vw, G2, 1); for (i = 0; i < n; i++) vee(C, b.x + 18 + i * vw, b.y + b.ah - 12 - vw * 0.5, vw, G2, 1); C.rect(b.x + 8, b.y + b.ah - 5, w, 5, {fill:RD});
return {vc:'#FFFFFF', vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function seam(C, Z, o){
K5.alt(C, Z, {off:58, fallback:B.rows, pre:function(C, g){ var n = Math.floor(g.w / 36), vw = g.w / n; for (var i = 0; i < n; i++) vee(C, g.x + i * vw, g.y - vw * 0.25, vw, i % 2 ? '#C5CCD6' : SL, 1); }}, function(C, k, t, b){
C.rect(b.cx - 28, b.cy - 28, 56, 56, {fill:GF}); C.rect(b.cx - 28, b.cy + 23, 56, 5, {fill:RD}); num(C, b.cx - 28, b.cy - 28, 56, 52, k, 19, '#FFFFFF');
});
}
return {p2:[['Tailored columns', pg(tailored)]], p3:[['Tailored columns', pg(tailored)]], p4:[['Tailored columns', pg(tailored)]], stats:[['Stitched numbers', pg(stitched)]], timeline:[['Seam line', pg(seam)]]};
}
});
})();
