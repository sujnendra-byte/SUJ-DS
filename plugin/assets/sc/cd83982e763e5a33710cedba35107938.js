
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 113 - Library Spine : a shelf of book spines in different heights and colours; points become a stack of books */
var INK = '#264653', TE = '#2A9D8F', YL = '#E9C46A', OR = '#F4A261', CO = '#E76F51', MU = '#5C7178', LN = '#DDE3E0', BG = '#FDFDFB';
var P = {bg:BG, text:INK, title:INK, muted:MU, accent:TE, accent2:CO, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [INK, TE, YL, OR, CO];
function lc(k){ return LC[k % 5]; }
function book(C, x, yb, w, h, col){ C.rect(x, yb - h, w, h, {fill:col, r:3}); C.rect(x, yb - h + 22, w, 5, {fill:'#FFFFFF', op:0.6}); C.rect(x, yb - h + 34, w, 2.5, {fill:'#FFFFFF', op:0.6}); C.rect(x + w * 0.22, yb - 70, w * 0.56, 34, {fill:'#FFFFFF', op:0.22, r:2}); }
function shelf(C, x, yb, ws, hs, k0){ var xx = x; ws.forEach(function(w, i){ book(C, xx, yb, w, hs[i], lc(i + (k0 || 0))); xx += w + 6; }); return xx; }
function cover(C){
var photo = (C.o || {}).img === 'photo', xx;
if (photo) { C.rect(700, 232, 262, 368, {fill:INK}); D.imgBox(C, 712, 244, 238, 344, {r:0}); xx = shelf(C, 984, 600, [56, 78, 48, 70], [330, 410, 270, 380], 1); }
else xx = shelf(C, 700, 600, [56, 78, 48, 70, 62, 86, 54], [330, 410, 270, 380, 440, 310, 360]);
K5.fill(C, Q.poly([], [[xx + 6, 600], [xx + 96, 284], [xx + 150, 300], [xx + 60, 616]].map(function(p){ return [Math.min(p[0], W + 40), Math.min(p[1], 600)]; })), TE);
C.rect(660, 600, 620, 12, {fill:INK});
K5.coverText(C, 88, 130, 540, {max:52, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo', ws = [], hs = [], R = K5.rnd(12);
for (var i = 0; i < 17; i++) { ws.push(40 + R() * 40); hs.push(90 + R() * 110); }
shelf(C, 60, 660, ws, hs); C.rect(0, 660, W, 12, {fill:INK});
if (photo) { C.rect(820, 70, 372, 340, {fill:INK}); D.imgBox(C, 832, 82, 348, 316, {r:0}); }
K5.closeText(C, 88, 80, 680, {mark:function(C, x, y, k, c){ C.rect(x + 3, y, 30, 36, {fill:lc(k + 1), r:3}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'library', name:'Library Spine', tag:'Paper white · book spines',
desc:'A shelf of book spines in different heights and warm colours, with slab-serif headings; on content pages the points become a stack of books lying flat, book-cover numbers and chapters on a shelf. For training, knowledge, education and publishing.',
fonts:{head:'Bitter', body:'Source Sans 3', headW:700, bodyW:400, headLh:1.1, gf:'Bitter:wght@500;600;700;800|Source+Sans+3:wght@400;500;600;700'},
P:P, LC:LC, cardR:6, imgR:6, coverLbl:'Bookshelf', closeLbl:'Bookshelf',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; [46, 64, 38, 56, 44].forEach(function(h, i){ C.rect(W - 170 + i * 26, 0, 20, h, {fill:lc(i + C.i)}); }); },
cover:cover, closing:closing,
rule:function(C, x, y){ for (var i = 0; i < 5; i++) C.rect(x + i * 13, y - [0, 5, 2, 7, 3][i], 10, 10 + [0, 5, 2, 7, 3][i], {fill:LC[i]}); return 22; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? INK : '#FFFFFF', r:6, line:hot ? null : LN, lw:1.5}); if (!o.big) { C.rect(x, y, 14, h, {fill:hot ? CO : lc(k + 1), r:6}); C.rect(x + 8, y, 6, h, {fill:hot ? CO : lc(k + 1)}); }
return hot ? {tc:'#FFFFFF', mc:'#C5D2D6', ac:YL, hot:1} : {tc:INK, mc:MU, ac:TE};
},
pad:30,
mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.hot ? CO : lc(k + 1); C.rect(x + s * 0.1, y, s * 0.8, s, {fill:c, r:4}); C.rect(x + s * 0.1, y + s * 0.82, s * 0.8, 3, {fill:'#FFFFFF', op:0.6}); K.icon(C, x + s * 0.27, y + s * 0.2, s * 0.46, t, K.on(c)); },
rowMark:function(C, x, y, s, t, k){ var c = lc(k); C.rect(x + s * 0.1, y, s * 0.8, s, {fill:c, r:4}); C.rect(x + s * 0.1, y + s * 0.82, s * 0.8, 3, {fill:'#FFFFFF', op:0.6}); num(C, x, y, s, s * 0.84, k, s * 0.34, K.on(c)); },
node:function(C, cx, cy, s, k){ var c = lc(k); C.rect(cx - s * 0.4, cy - s * 0.56, s * 0.8, s * 1.12, {fill:c, r:4}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, K.on(c)); },
track:function(C, x1, y, x2){ C.rect(x1, y + 22, x2 - x1, 6, {fill:INK}); },
L:{cards:'Cover cards', rows:'Catalogue rows', steps:'Volume steps', stmt:'Foreword', stats:'Plain numbers'},
sig:function(pg, B){
function stack(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 760) return B.rows(C, Z, o);
var g = 6, bh = Math.min((Z.h - 10 - g * (n - 1)) / n, 110), y0 = Z.y + (Z.h - 10 - (bh * n + g * (n - 1))) / 2, hw = Math.min(280, Z.w * 0.25);
var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), hw, bh - 24, {font:'head', max:22, min:12, maxLines:2}), ds = C.groupFit(it.map(function(t){ return t.d || ''; }), Z.w - hw - 300, bh - 24, {font:'body', max:17, min:10, lh:1.34});
it.forEach(function(t, k){
var ind = [0, 34, 12, 46, 6, 28][k], w = Z.w - 60 - [20, 0, 40, 10, 30, 0][k], x = Z.x + ind, y = y0 + k * (bh + g), c = lc(k), on = K.on(c), d = [];
C.rect(x, y, w, bh, {fill:c, r:4}); C.rect(x + w - 34, y + 6, 30, bh - 12, {fill:BG, r:2});
for (var i = 1; i < 5; i++) Q.line(d, x + w - 34, y + 6 + (bh - 12) * i / 5, x + w - 4, y + 6 + (bh - 12) * i / 5); K5.stroke(C, d, '#C9CCD0', 1);
C.rect(x + 76, y, 3, bh, {fill:'#FFFFFF', op:0.5}); num(C, x, y, 76, bh, k, Math.min(26, bh * 0.34), on);
if (t.h) C.text(x + 100, y, hw, bh, t.h, {font:'head', size:hs, color:on, va:'m', lh:1.14, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
C.text(x + (t.h ? 100 + hw + 24 : 100), y, w - (t.h ? hw + 124 : 100) - 60, bh, t.d, {font:'body', size:ds, color:on, va:'m', lh:1.34, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
});
C.rect(Z.x, y0 + n * (bh + g) - g + 2, Z.w, 8, {fill:INK});
}
function covers(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:20, vMax:42, vw:function(cw){ return cw - 110; }}, function(C, k, t, b){
var w = Math.min(b.w - 40, 190), x = b.cx - w / 2, c = lc(k), on = K.on(c);
C.rect(x + 8, b.y + 8, w, b.ah - 8, {fill:BG, line:LN, lw:1.5, r:4}); C.rect(x, b.y, w, b.ah - 8, {fill:c, r:4}); C.rect(x + 16, b.y, 3, b.ah - 8, {fill:'#000000', op:0.18}); C.rect(x + 34, b.y + 22, w - 54, 3, {fill:on, op:0.6}); C.rect(x + 34, b.y + b.ah - 34, w - 54, 3, {fill:on, op:0.6});
return {vc:on, vx:x + 28, vw:w - 40, vy:b.y + (b.ah - 8 - b.vs * 1.15) / 2};
});
}
function chapters(C, Z, o){
K5.alt(C, Z, {same:true, at:0.42, off:34, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y, g.w, 8, {fill:INK}); }}, function(C, k, t, b){
var hh = [96, 124, 84, 112, 136, 100][k], c = lc(k); book(C, b.cx - 30, b.cy, 60, hh, c); book(C, b.cx + 36, b.cy, 26, hh * 0.7, lc(k + 2)); num(C, b.cx - 30, b.cy - hh + 44, 60, 30, k, 17, K.on(c));
});
}
return {agenda:[['Book stack', pg(stack)]], p2:[['Book stack', pg(stack)]], p3:[['Book stack', pg(stack)]], p4:[['Book stack', pg(stack)]], p56:[['Book stack', pg(stack)]],
stats:[['Book covers', pg(covers)]], timeline:[['Chapters on a shelf', pg(chapters)]]};
}
});
})();
