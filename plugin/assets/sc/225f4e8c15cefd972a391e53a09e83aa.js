
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 99 - Terrazzo : a floor of small scattered stone chips in five muted colours */
var INK = '#2F3E46', BL = '#3D5A80', CO = '#E07A5F', SG = '#81B29A', SD = '#F2CC8F', ST = '#ECEEF0', MU = '#66747B', LN = '#DCDFE2';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:BL, accent2:CO, card:'#F3F4F5', line:LN, onAccent:'#FFFFFF'};
var LC = [BL, CO, SG, SD, INK];
function lc(k){ return LC[k % 5]; }
function chipPts(cx, cy, r, R){ var n = 4 + Math.floor(R() * 3), a0 = R() * 360, pts = []; for (var j = 0; j < n; j++) pts.push(K5.pol(cx, cy, r * (0.6 + R() * 0.5), a0 + j * 360 / n + R() * 24)); return pts; }
function chips(C, x, y, w, h, count, seed, big){
var R = K5.rnd(seed), d = [[], [], [], [], []];
for (var i = 0; i < count; i++) Q.poly(d[i % 5], chipPts(x + 10 + R() * (w - 20), y + 10 + R() * (h - 20), (big || 1) * (7 + R() * 15), R));
for (var c = 0; c < 5; c++) K5.fill(C, d[c], LC[c]);
}
function chip(C, cx, cy, r, col, seed){ K5.fill(C, Q.poly([], chipPts(cx, cy, r * 1.25, K5.rnd(seed))), col); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.rect(0, 0, 500, H, {fill:ST}); chips(C, 0, 0, 500, H, 86, 3, 1.25);
if (photo) { C.rect(64, 94, 372, 532, {fill:'#FFFFFF'}); D.imgBox(C, 76, 106, 348, 508, {r:0}); }
K5.coverText(C, 580, 140, 610, {max:54, by:640});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.rect(800, 0, 480, H, {fill:ST}); chips(C, 800, 0, 480, H, 80, 8, 1.25);
if (photo) { C.rect(860, 110, 360, 500, {fill:'#FFFFFF'}); D.imgBox(C, 872, 122, 336, 476, {r:0}); }
K5.closeText(C, 88, 150, 640, {mark:function(C, x, y, k, c){ chip(C, x + 18, y + 18, 18, lc(k), 5 + k); C.icon(x + 10, y + 10, 16, c.icon, k === 3 ? INK : '#FFFFFF'); }});
}
K5.make({
id:'terrazzo', name:'Terrazzo', tag:'Stone grey · scattered chips',
desc:'A terrazzo floor of small scattered stone chips in slate blue, clay, sage and sand on pale stone, used as a side panel and as tiles, with calm white pages. For architecture, interiors, real estate, materials and design studios.',
fonts:{head:'Jost', body:'Jost', headW:600, bodyW:400, headLh:1.1, gf:'Jost:wght@400;500;600;700'},
P:P, LC:LC, cardR:10, imgR:10, coverLbl:'Stone panel', closeLbl:'Stone panel',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.rect(0, 0, 40, H, {fill:ST}); chips(C, -6, 0, 52, H, 22, 11 + (C.i % 6), 0.8); },
cover:cover, closing:closing,
rule:function(C, x, y){ for (var i = 0; i < 4; i++) chip(C, x + 8 + i * 22, y + 4, 7, LC[i], 20 + i); return 20; },
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {fill:INK, r:10}); return {tc:'#FFFFFF', mc:'#C9D1D5', ac:SD, hot:1}; }
C.rect(x, y, w, h, {fill:o.big ? ST : '#FFFFFF', r:10, line:o.big ? null : LN, lw:1.5}); return {tc:INK, mc:MU, ac:BL};
},
mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.hot ? SD : lc(k); chip(C, x + s / 2, y + s / 2, s / 2, c, 30 + k); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, K.on(c)); },
rowMark:function(C, x, y, s, t, k){ var c = lc(k); chip(C, x + s / 2, y + s / 2, s / 2, c, 40 + k); num(C, x, y, s, s, k, s * 0.34, K.on(c)); },
node:function(C, cx, cy, s, k){ var c = lc(k); chip(C, cx, cy, s * 0.56, c, 50 + k); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, K.on(c)); },
track:function(C, x1, y, x2){ C.rect(x1, y - 5, x2 - x1, 10, {fill:ST, r:5}); },
L:{cards:'Slab cards', rows:'Chip rows', steps:'Chip steps', stmt:'Stone note', stats:'Tile numbers'},
sig:function(pg, B){
function chipCols(C, Z, o){
K5.cols(C, Z, {max:5, ah:126, gap:28, tg:20, fallback:B.rows}, function(C, k, t, b){
var c = lc(k); chip(C, b.x + 52, b.y + 54, 50, c, 60 + k); K.icon(C, b.x + 33, b.y + 35, 38, t, K.on(c));
chip(C, b.x + 118, b.y + 26, 9, lc(k + 1), 70 + k); chip(C, b.x + 128, b.y + 78, 13, lc(k + 2), 80 + k); C.rect(b.x, b.y + 122, b.w, 2, {fill:LN});
});
}
function tiles(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.52), tg:20, vMax:46, vw:function(cw){ return cw - 110; }}, function(C, k, t, b){
C.rect(b.x + 8, b.y, b.w - 16, b.ah, {fill:ST, r:10}); chips(C, b.x + 8, b.y, b.w - 16, b.ah, 16, 90 + k * 5, 0.8);
C.rect(b.x + 34, b.y + b.ah / 2 - 44, b.w - 68, 88, {fill:'#FFFFFF', r:8});
return {vc:INK, vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function slab(C, Z, o){
K5.alt(C, Z, {off:58, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 22, g.w, 44, {fill:ST, r:8}); chips(C, g.x, g.y - 24, g.w, 48, 34, 120, 0.6); }}, function(C, k, t, b){
C.ell(b.cx - 26, b.cy - 26, 52, 52, {fill:'#FFFFFF', line:INK, lw:2}); num(C, b.cx - 26, b.cy - 26, 52, 52, k, 17, INK);
});
}
function chipRows(C, Z, o){
K5.list(C, Z, {aw:92, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
var c = lc(k), r = Math.min(30, b.h * 0.36); chip(C, b.x + 34, b.cy, r, c, 140 + k); num(C, b.x + 34 - r, b.cy - r, r * 2, r * 2, k, r * 0.62, K.on(c)); C.rect(b.x, b.y + b.h + 3, b.w, 1.5, {fill:LN});
});
}
return {agenda:[['Chip list', pg(chipRows)]], p2:[['Chip columns', pg(chipCols)]], p3:[['Chip columns', pg(chipCols)]], p4:[['Chip columns', pg(chipCols)]], p56:[['Chip list', pg(chipRows)]], p78:[['Chip list', pg(chipRows)]],
stats:[['Stone tiles', pg(tiles)]], timeline:[['Stone slab', pg(slab)]]};
}
});
})();
