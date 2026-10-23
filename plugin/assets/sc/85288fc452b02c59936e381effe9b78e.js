
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 111 - Low-Poly : the page breaks into flat triangle facets in many shades of one steel teal */
var TE = '#0B4F6C', CY = '#01BAEF', DK = '#06222E', MID = '#147A9C', MU = '#52707D', LN = '#D6E4EA', BG = '#FBFBFF';
var P = {bg:BG, text:DK, title:DK, muted:MU, accent:TE, accent2:CY, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [TE, CY, MID, DK];
function mesh(C, x, y, w, h, cs, rs, seed, base){
var R = K5.rnd(seed), G = [], i, j, wd = [[], [], [], []], bd = [[], [], [], []];
C.rect(x, y, w, h, {fill:base || TE});
for (j = 0; j <= rs; j++) { G.push([]); for (i = 0; i <= cs; i++) G[j].push([x + w * i / cs + (i && i < cs ? (R() - 0.5) * w / cs * 0.7 : 0), y + h * j / rs + (j && j < rs ? (R() - 0.5) * h / rs * 0.7 : 0)]); }
for (j = 0; j < rs; j++) for (i = 0; i < cs; i++) { var a = G[j][i], b = G[j][i + 1], c = G[j + 1][i], d = G[j + 1][i + 1]; [[a, b, c], [b, d, c]].forEach(function(T){ Q.poly((R() > 0.5 ? wd : bd)[Math.floor(R() * 4)], T); }); }
for (i = 0; i < 4; i++) { K5.fill(C, wd[i], '#FFFFFF', 0.05 + i * 0.065); K5.fill(C, bd[i], '#000000', 0.05 + i * 0.065); }
}
function gem(C, cx, cy, r, col){
C.shape('hexagon', cx - r, cy - r * 0.88, r * 2, r * 1.76, {fill:col || TE});
var v = []; for (var k = 0; k < 6; k++) v.push(K5.pol(cx, cy, r, k * 60));
K5.fill(C, Q.poly([], [[cx, cy], v[4], v[5]]), '#FFFFFF', 0.28); K5.fill(C, Q.poly([], [[cx, cy], v[5], v[0]]), '#FFFFFF', 0.12); K5.fill(C, Q.poly([], [[cx, cy], v[1], v[2]]), '#000000', 0.22); K5.fill(C, Q.poly([], [[cx, cy], v[2], v[3]]), '#000000', 0.1);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
mesh(C, 0, 0, W, H, 10, 6, 7); C.rect(0, 0, 740, H, {grad:{ang:90, stops:[[0, DK, 0.72], [100, DK, 0]]}});
if (photo) { C.rect(808, 98, 396, 524, {fill:'#FFFFFF'}); D.imgBox(C, 820, 110, 372, 500, {r:0}); }
C.rect(88, 108, 64, 7, {fill:CY});
K5.coverText(C, 88, 140, 620, {tc:'#FFFFFF', sc:'#CFE6EF', kc:CY, max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
mesh(C, 0, 0, W, H, 10, 6, 19, '#094259'); C.rect(0, 0, 740, H, {grad:{ang:90, stops:[[0, DK, 0.72], [100, DK, 0]]}});
if (photo) { C.rect(808, 118, 396, 484, {fill:'#FFFFFF'}); D.imgBox(C, 820, 130, 372, 460, {r:0}); }
K5.closeText(C, 88, 150, 640, {tc:'#FFFFFF', sc:'#CFE6EF', mc:CY, mi:DK});
}
K5.make({
id:'lowpoly', name:'Low-Poly', tag:'Steel teal · triangle facets',
desc:'The page breaks into flat triangle facets in many shades of one steel teal, like cut crystal or folded metal, with faceted gems, peak columns and a ridge-line roadmap. For technology, engineering, materials, gaming and hardware.',
fonts:{head:'Titillium Web', body:'Titillium Web', headW:700, bodyW:400, headLh:1.08, gf:'Titillium+Web:wght@400;600;700'},
P:P, LC:LC, M:92, cardR:0, coverLbl:'Facet field', closeLbl:'Facet field',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; mesh(C, 0, 0, 48, H, 1, 6, 3 + (C.i % 5)); },
cover:cover, closing:closing,
rule:function(C, x, y){ K5.fill(C, Q.poly([], [[x, y + 10], [x + 12, y - 4], [x + 24, y + 10]]), TE); K5.fill(C, Q.poly([], [[x + 20, y + 10], [x + 32, y - 4], [x + 44, y + 10]]), CY); K5.fill(C, Q.poly([], [[x + 40, y + 10], [x + 52, y - 4], [x + 64, y + 10]]), MID); return 26; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? TE : '#FFFFFF', line:hot ? null : LN, lw:1.5});
if (!o.big) { K5.fill(C, Q.poly([], [[x + w, y], [x + w - 64, y], [x + w, y + 64]]), hot ? CY : TE); K5.fill(C, Q.poly([], [[x + w - 64, y], [x + w - 32, y + 32], [x + w - 96, y]]), hot ? '#FFFFFF' : CY, 0.5); }
return hot ? {tc:'#FFFFFF', mc:'#CFE6EF', ac:CY, hot:1} : {tc:DK, mc:MU, ac:TE};
},
mark:function(C, x, y, s, t, k, cc){ gem(C, x + s / 2, y + s / 2, s * 0.56, cc && cc.hot ? '#0A93BD' : [TE, MID, '#0A93BD'][k % 3]); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ gem(C, x + s / 2, y + s / 2, s * 0.56, [TE, MID, '#0A93BD'][k % 3]); num(C, x, y, s, s, k, s * 0.34, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ gem(C, cx, cy, s * 0.6, [TE, MID, '#0A93BD'][k % 3]); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:LN}); },
L:{cards:'Facet cards', rows:'Gem rows', steps:'Gem steps', stmt:'Crystal note', stats:'Plain numbers'},
sig:function(pg, B){
function peaks(C, Z, o){
K5.cols(C, Z, {max:5, ah:140, gap:26, tg:16, fallback:B.rows}, function(C, k, t, b){
var w = Math.min(150, b.w * 0.6), x = b.x, by = b.y + 122;
K5.fill(C, Q.poly([], [[x, by], [x + w / 2, b.y], [x + w, by]]), TE); K5.fill(C, Q.poly([], [[x + w / 2, b.y], [x + w, by], [x + w / 2, by - 28]]), CY); K5.fill(C, Q.poly([], [[x, by], [x + w / 2, by - 28], [x + w / 2, b.y]]), '#000000', 0.12);
K.icon(C, x + w + 12, b.y + 14, 34, t, TE); C.text(x + w + 10, b.y + 62, 80, 60, pad2(k + 1), {font:'head', size:40, wt:700, color:CY, lh:1, va:'b'}); C.rect(b.x, by + 6, b.w, 2, {fill:LN});
});
}
function gems(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:18, vMax:40, vw:function(cw, ah){ return Math.min(cw * 0.56, ah * 0.82); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.42, b.ah * 0.56), cy = b.y + b.ah / 2; gem(C, b.cx, cy, r, [TE, MID, '#0A93BD', DK][k]);
return {vc:'#FFFFFF', vx:b.cx - r * 0.82, vw:r * 1.64, vy:cy - b.vs * 0.58};
});
}
function ridge(C, Z, o){
K5.alt(C, Z, {same:true, at:0.4, off:28, fallback:B.rows, pre:function(C, g){
var l = [], r = [];
for (var k = 0; k < g.n; k++) { var x0 = g.x + g.cw * k, hh = 70 + ((k * 37) % 50); Q.poly(l, [[x0, g.y], [x0 + g.cw / 2, g.y - hh], [x0 + g.cw / 2, g.y]]); Q.poly(r, [[x0 + g.cw / 2, g.y - hh], [x0 + g.cw, g.y], [x0 + g.cw / 2, g.y]]); }
K5.fill(C, l, TE); K5.fill(C, r, MID); C.rect(g.x, g.y, g.w, 4, {fill:DK});
}}, function(C, k, t, b){
var hh = 70 + ((k * 37) % 50); C.ell(b.cx - 22, b.cy - hh - 46, 44, 44, {fill:CY}); num(C, b.cx - 22, b.cy - hh - 46, 44, 44, k, 16, DK);
});
}
return {p2:[['Facet peaks', pg(peaks)]], p3:[['Facet peaks', pg(peaks)]], p4:[['Facet peaks', pg(peaks)]], stats:[['Gem numbers', pg(gems)]], timeline:[['Ridge line', pg(ridge)]]};
}
});
})();
