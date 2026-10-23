
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2;
/* Template 93 - Barcode : bands of black scan bars with one red bar; condensed capitals like a printed label */
var BK = '#111111', RD = '#E63946', MU = '#5F636B', LN = '#D4D4D8';
var P = {bg:'#FFFFFF', text:BK, title:BK, muted:MU, accent:BK, accent2:RD, card:'#F4F4F5', line:LN, onAccent:'#FFFFFF'};
var LC = [BK, RD, '#52525B', '#A1A1AA'];
function bars(C, x, y, w, h, seed, red, vert, col){
var R = K5.rnd(seed), d = [], p = 0, i = 0, rr = null, sc = Math.max(1, Math.min(w, 400) / 160);
while (p < w - 2) { var bw = Math.min((1.5 + R() * 5) * sc, w - p); if (i === red) rr = [p, bw]; else if (vert) Q.rect(d, x, y + p, h, bw); else Q.rect(d, x + p, y, bw, h); p += bw + (1.5 + R() * 3.5) * sc; i++; }
K5.fill(C, d, col || BK); if (rr) { if (vert) C.rect(x, y + rr[0], h, rr[1], {fill:RD}); else C.rect(x + rr[0], y, rr[1], h, {fill:RD}); }
}
function cover(C){
var photo = (C.o || {}).img === 'photo', f = C.cover;
if (photo) D.imgBox(C, 820, 84, 364, 320, {r:0});
bars(C, 96, 440, 1088, 168, 11, 31);
K5.coverText(C, 96, 74, photo ? 680 : 1060, {max:photo ? 62 : 78, th:170, lh:0.98, gap:14, smax:17, by:628});
if (f.date) C.text(700, 628, 484, 18, f.date, {font:'body', size:12.5, wt:600, color:BK, align:'r', lh:1, maxLines:1});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
bars(C, 96, 70, 1088, 96, 23, 12);
if (photo) D.imgBox(C, 820, 220, 364, 400, {r:0});
K5.closeText(C, 96, 220, photo ? 680 : 900, {max:84, mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {line:BK, lw:2}); C.icon(x + 10, y + 10, 16, c.icon, k === 0 ? RD : BK); }});
}
K5.make({
id:'barcode', name:'Barcode', tag:'White · black scan bars',
desc:'Bands of black scan bars in uneven widths with a single red bar, tall condensed capitals and label-style numbers, like a printed product label. For retail, inventory, FMCG, product ranges and pricing decks.',
fonts:{head:'Barlow Condensed', body:'Barlow', headW:700, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Barlow+Condensed:wght@500;600;700;800|Barlow:wght@400;500;600;700'},
P:P, LC:LC, cardR:0, tMax:46, coverLbl:'Scan band', closeLbl:'Scan band',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; bars(C, 0, 0, H, 30, 7 + (C.i % 5), 9 + C.i % 7, true); },
cover:cover, closing:closing,
rule:function(C, x, y){ bars(C, x, y, 120, 10, 5, 6); return 20; },
card:function(C, x, y, w, h, k, o){
if (o.big) { C.rect(x, y, w, h, {fill:'#F4F4F5'}); C.rect(x, y, 8, h, {fill:RD}); return {tc:BK, mc:MU, ac:BK}; }
if (o.hot) { C.rect(x, y, w, h, {fill:BK}); bars(C, x + 24, y + h - 34, Math.min(120, w - 48), 16, 3 + k, 4, false, '#FFFFFF'); return {tc:'#FFFFFF', mc:'#C8C8CC', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', line:BK, lw:2}); bars(C, x + 24, y + h - 34, Math.min(120, w - 48), 16, 3 + k, 4);
return {tc:BK, mc:MU, ac:BK};
},
row:function(C, x, y, w, h, k){ C.rect(x, y + h - 2, w, 2, {fill:BK}); return {tc:BK, mc:MU, ac:BK}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? RD : BK}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.text(x - 6, y, s + 12, s, pad2(k + 1), {font:'head', size:s * 0.8, wt:800, color:k === 0 ? RD : BK, align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:k === 0 ? RD : BK}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.46, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
track:function(C, x1, y, x2){ bars(C, x1, y - 8, x2 - x1, 16, 17, -1); },
conc:{kind:'box', fill:'#F4F4F5', r:0, label:'Bottom line', lc:RD, tc:BK},
L:{cards:'Label cards', rows:'Serial rows', steps:'Scan steps', stmt:'Label note', stats:'Label numbers'},
sig:function(pg, B){
function labelStats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:18, vMax:64, vw:function(cw){ return cw - 60; }}, function(C, k, t, b){
C.rect(b.x + 8, b.y, b.w - 16, b.ah, {line:BK, lw:2, fill:'#FFFFFF'});
bars(C, b.x + 28, b.y + b.ah - 62, b.w - 56, 40, 31 + k * 7, 5 + k);
C.text(b.x + 28, b.y + 14, b.w - 56, 16, 'NO. ' + pad2(k + 1), {font:'body', size:11, wt:700, color:MU, ls:0.14, lh:1});
return {vc:BK, vy:b.y + 34 + (b.ah - 104 - b.vs * 1.15) / 2};
});
}
function serial(C, Z, o){
K5.list(C, Z, {aw:176, pr:16, gap:0, rhMax:100, split:5}, function(C, k, t, b){
C.rect(b.x, b.y + b.h - 2, b.w, 2, {fill:BK});
C.text(b.x, b.y, 70, b.h, pad2(k + 1), {font:'head', size:Math.min(44, b.h * 0.5), wt:800, color:k === 0 ? RD : BK, va:'m', lh:1});
bars(C, b.x + 76, b.y + b.h * 0.26, 76, b.h * 0.48, 41 + k * 3, -1);
});
}
function scanLine(C, Z, o){
K5.alt(C, Z, {off:58, fallback:B.rows, pre:function(C, g){ bars(C, g.x, g.y - 14, g.w, 28, 53, -1); }}, function(C, k, t, b){
C.rect(b.cx - 4, b.up ? b.cy - 46 : b.cy - 14, 8, 60, {fill:RD});
C.rect(b.cx - 22, b.up ? b.cy - 50 : b.cy + 18, 44, 30, {fill:BK}); C.text(b.cx - 22, b.up ? b.cy - 50 : b.cy + 18, 44, 30, pad2(k + 1), {font:'head', size:18, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
});
}
return {agenda:[['Serial rows', pg(serial)]], p56:[['Serial rows', pg(serial)]], p78:[['Serial rows', pg(serial)]], stats:[['Scan labels', pg(labelStats)]], timeline:[['Scan line', pg(scanLine)]]};
}
});
})();
