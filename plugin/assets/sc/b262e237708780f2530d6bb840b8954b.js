
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 103 - Scallop : rows of overlapping rounded scales in teal and seafoam with coral accents */
var TE = '#006D77', SF = '#83C5BE', CO = '#E29578', DK = '#0B3B40', PALE = '#EDF6F9', MU = '#547176', LN = '#CFE5E8';
var P = {bg:'#FFFFFF', text:DK, title:DK, muted:MU, accent:TE, accent2:CO, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [TE, SF, CO, DK];
function lc(k){ return LC[k % 4]; }
function scales(C, x, y, w, rows, r, seed, bgc, down){
var R = K5.rnd(seed), st = r * 1.72;
for (var jj = 0; jj < rows; jj++) {
var j = down ? jj : rows - 1 - jj, cy = y + j * r * 0.86, wd = [], d = [[], [], []];
for (var cx = x - (j % 2 ? 0 : st / 2); cx < x + w + r; cx += st) { Q.circ(wd, cx, cy, r); var q = R(); Q.circ(d[q < 0.12 ? 2 : (q < 0.56 ? 0 : 1)], cx, cy, r - 3.5); }
K5.fill(C, wd, bgc); K5.fill(C, d[0], TE); K5.fill(C, d[1], SF); K5.fill(C, d[2], CO);
}
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:PALE}); scales(C, 0, 470, W, 6, 66, 5, PALE);
if (photo) { C.ell(906, 36, 308, 308, {fill:'#FFFFFF'}); D.imgBox(C, 920, 50, 280, 280, {shape:'ell'}); }
K5.coverText(C, 88, 62, photo ? 760 : 900, {max:56, th:130, gap:14, by:362});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:PALE}); scales(C, 0, -20, W, 4, 66, 9, PALE, true);
if (photo) { C.ell(866, 266, 328, 328, {fill:'#FFFFFF'}); D.imgBox(C, 880, 280, 300, 300, {shape:'ell'}); }
K5.closeText(C, 88, 290, 700, {mark:function(C, x, y, k, c){ C.ell(x + 10, y, 36, 36, {fill:SF}); C.ell(x, y, 36, 36, {fill:k % 2 ? CO : TE}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'scallop', name:'Scallop', tag:'Pale aqua · fish-scale rows',
desc:'Rows of overlapping rounded scales in teal, seafoam and coral, with scalloped header edges, overlapping disc rows and dome numbers. Soft, rounded and welcoming, for hospitality, wellness, water, coastal and consumer brands.',
fonts:{head:'Quicksand', body:'Nunito', headW:700, bodyW:400, headLh:1.1, gf:'Quicksand:wght@500;600;700|Nunito:wght@400;500;600;700'},
P:P, LC:LC, cardR:22, imgR:22, headTop:66, coverLbl:'Scale rows', closeLbl:'Scale rows',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; var d = []; for (var x = 16; x < W + 16; x += 32) Q.circ(d, x, 12, 16); C.rect(0, 0, W, 12, {fill:TE}); K5.fill(C, d, TE); },
cover:cover, closing:closing,
rule:function(C, x, y){ for (var i = 0; i < 4; i++) C.ell(x + i * 14, y - 2, 18, 18, {fill:LC[i === 3 ? 0 : i], line:'#FFFFFF', lw:2}); return 24; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? TE : PALE, r:22});
if (!o.big) for (var i = 0; i < 3; i++) C.ell(x + w - 30 - i * 16, y + 16, 20, 20, {fill:hot ? ['#FFFFFF', SF, CO][i] : [TE, SF, CO][i], line:hot ? TE : PALE, lw:2.5});
return hot ? {tc:'#FFFFFF', mc:'#CDE9E6', ac:'#FFFFFF', hot:1} : {tc:DK, mc:MU, ac:TE};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x + s * 0.3, y, s, s, {fill:hot ? CO : SF}); C.ell(x, y, s, s, {fill:hot ? '#FFFFFF' : (k % 2 ? CO : TE)}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? TE : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.ell(x + s * 0.3, y, s, s, {fill:SF}); C.ell(x, y, s, s, {fill:k % 2 ? CO : TE}); num(C, x, y, s, s, k, s * 0.38, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2 - 4, cy - s / 2 - 4, s + 8, s + 8, {fill:'#FFFFFF'}); C.ell(cx - s / 2, cy - s / 2, s, s, {fill:lc(k === 3 ? 0 : k)}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 4, x2 - x1, 8, {fill:PALE, r:4}); },
L:{cards:'Pebble cards', rows:'Scale rows', steps:'Disc steps', stmt:'Tide note', stats:'Pebble numbers'},
sig:function(pg, B){
function discs(C, Z, o){
var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var cw = Z.w / n, r = Math.min(cw * 0.56, Z.h * 0.27, 124), cy = Z.y + r + 4, tw = cw - 26, col = [TE, SF, CO, DK, TE];
var F = K.fitHD(C, it, tw, 46, Z.h - r * 2 - 34, {hMax:22, dMax:16, hMin:12, dMin:10});
it.forEach(function(t, k){
var cx = Z.x + cw * (k + 0.5), c = col[k], on = c === SF ? DK : '#FFFFFF';
C.ell(cx - r - 5, cy - r - 5, r * 2 + 10, r * 2 + 10, {fill:'#FFFFFF'}); C.ell(cx - r, cy - r, r * 2, r * 2, {fill:c});
num(C, cx - 40, cy - r * 0.62, 80, 30, k, 20, on); K.icon(C, cx - r * 0.24, cy - r * 0.1, r * 0.48, t, on);
K.hd(C, cx - tw / 2, cy + r + 24, tw, Z.y + Z.h - (cy + r + 24), t, F, {tc:DK, mc:MU, align:'c'});
});
}
function domes(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.48), tg:22, vMax:44, vw:function(cw, ah){ return Math.min(cw * 0.6, 220); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.44, b.ah - 30), by = b.y + b.ah, c = [TE, CO, DK, TE][k];
K5.fill(C, Q.sector([], b.cx, by, 0, r + 26, 180, 360), SF, 0.5); K5.fill(C, Q.sector([], b.cx, by, 0, r, 180, 360), c);
return {vc:'#FFFFFF', vx:b.cx - r * 0.8, vw:r * 1.6, vy:by - r * 0.36 - b.vs * 0.6};
});
}
function garland(C, Z, o){
K5.alt(C, Z, {same:true, at:0.16, off:86, fallback:B.rows, pre:function(C, g){
var d = []; for (var k = 0; k < g.n - 1; k++) { var x0 = g.x + g.cw * (k + 0.5), pts = []; for (var i = 0; i <= 20; i++) { var a = Math.PI * i / 20; pts.push([x0 + g.cw * (1 - Math.cos(a)) / 2, g.y + 46 * Math.sin(a)]); } Q.pts(d, pts); }
K5.stroke(C, d, SF, 5);
}}, function(C, k, t, b){
var c = [TE, CO, DK, TE, CO, DK][k]; C.ell(b.cx - 40, b.cy - 40, 80, 80, {fill:'#FFFFFF'}); C.ell(b.cx - 34, b.cy - 34, 68, 68, {fill:c}); num(C, b.cx - 34, b.cy - 34, 68, 68, k, 22, '#FFFFFF');
});
}
return {p2:[['Overlapping discs', pg(discs)]], p3:[['Overlapping discs', pg(discs)]], p4:[['Overlapping discs', pg(discs)]],
stats:[['Dome numbers', pg(domes)]], timeline:[['Garland', pg(garland)]]};
}
});
})();
