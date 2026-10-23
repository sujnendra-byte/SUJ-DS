
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 96 - Tartan : a heritage plaid of forest green with fine gold and claret lines */
var GN = '#1B3A2F', GD = '#C8A951', CL = '#7A1F2B', IV = '#F6F3EA', MU = '#5E6E66', LN = '#DDD6C3', DK = '#0C1F18';
var P = {bg:IV, text:GN, title:GN, muted:MU, accent:GN, accent2:GD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [GN, GD, CL, '#3E6B58'];
function tartan(C, x, y, w, h, p){
p = p || 72; var v = [], hh = [], g = [], c = [], i, o;
C.rect(x, y, w, h, {fill:GN});
for (i = 0; i * p < w; i++) { o = i * p; if (o + p * 0.16 < w) Q.rect(v, x + o + p * 0.16, y, Math.min(p * 0.4, w - o - p * 0.16), h); if (o + p * 0.74 < w) Q.rect(g, x + o + p * 0.74, y, Math.min(2.4, w - o - p * 0.74), h); if (o + p * 0.86 < w) Q.rect(c, x + o + p * 0.86, y, Math.min(2, w - o - p * 0.86), h); }
for (i = 0; i * p < h; i++) { o = i * p; if (o + p * 0.16 < h) Q.rect(hh, x, y + o + p * 0.16, w, Math.min(p * 0.4, h - o - p * 0.16)); if (o + p * 0.74 < h) Q.rect(g, x, y + o + p * 0.74, w, Math.min(2.4, h - o - p * 0.74)); if (o + p * 0.86 < h) Q.rect(c, x, y + o + p * 0.86, w, Math.min(2, h - o - p * 0.86)); }
K5.fill(C, v, DK, 0.5); K5.fill(C, hh, DK, 0.5); K5.fill(C, g, GD); K5.fill(C, c, CL);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
tartan(C, 0, 0, 500, H, 96); C.rect(500, 0, 6, H, {fill:GD});
if (photo) { C.rect(62, 92, 376, 536, {fill:IV}); D.imgBox(C, 72, 102, 356, 516, {r:0}); }
K5.coverText(C, 580, 140, 610, {max:54, by:640, rule:function(C, x, y){ C.rect(x, y, 90, 2, {fill:GD}); C.rect(x, y + 5, 90, 1, {fill:GD}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
tartan(C, 800, 0, 480, H, 96); C.rect(794, 0, 6, H, {fill:GD});
if (photo) { C.rect(860, 110, 360, 500, {fill:IV}); D.imgBox(C, 870, 120, 340, 480, {r:0}); }
K5.closeText(C, 88, 150, 640, {mc:GN, mi:GD});
}
K5.make({
id:'tartan', name:'Tartan', tag:'Forest green · heritage plaid',
desc:'A heritage plaid of forest green with fine gold and claret lines, used as a side panel, a header strip and small swatches, with ivory pages and serif headings. For heritage brands, family businesses, hospitality, clubs and anniversary decks.',
fonts:{head:'Lora', body:'Mulish', headW:700, bodyW:400, headLh:1.12, gf:'Lora:wght@500;600;700|Mulish:wght@400;500;600;700'},
P:P, LC:LC, cardR:0, headTop:70, coverLbl:'Plaid panel', closeLbl:'Plaid panel',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; tartan(C, 0, 0, W, 26, 72); C.rect(0, 26, W, 3, {fill:GD}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 70, 2, {fill:GD}); C.rect(x, y + 5, 70, 1, {fill:GD}); return 18; },
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {fill:GN}); C.rect(x, y, w, 6, {fill:GD}); return {tc:'#FFFFFF', mc:'#C9D6CF', ac:GD, hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', line:LN, lw:1.2}); if (!o.big) tartan(C, x, y, w, 14, 48); else C.rect(x, y, 6, h, {fill:GD});
return {tc:GN, mc:MU, ac:GN};
},
row:function(C, x, y, w, h, k){ C.line(x, y + h - 1, x + w, y + h - 1, {color:GD, lw:1.2, dash:true}); return {tc:GN, mc:MU, ac:GN}; },
mark:function(C, x, y, s, t, k, cc){ if (cc && cc.hot) C.rect(x, y, s, s, {fill:GD}); else { tartan(C, x, y, s, s, s); C.rect(x, y, s, s, {line:GD, lw:1.5}); } K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, cc && cc.hot ? GN : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ tartan(C, x, y, s, s, s); C.rect(x, y, s, s, {line:GD, lw:1.5}); num(C, x, y, s, s, k, s * 0.4, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ K5.fill(C, Q.poly([], [[cx, cy - s * 0.62], [cx + s * 0.62, cy], [cx, cy + s * 0.62], [cx - s * 0.62, cy]]), GD); C.ell(cx - s * 0.32, cy - s * 0.32, s * 0.64, s * 0.64, {fill:IV}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.28, GN); },
track:function(C, x1, y, x2){ C.rect(x1, y - 1, x2 - x1, 2, {fill:GD}); },
conc:{kind:'box', fill:'#FFFFFF', r:0, line:GD, lw:1.2, label:'In short', lc:CL, tc:GN},
L:{cards:'Plaid cards', rows:'Ledger rows', steps:'Pin steps', stmt:'House note', stats:'Framed numbers'},
sig:function(pg, B){
function swatches(C, Z, o){
K5.cols(C, Z, {max:5, ah:128, gap:26, tg:18, fallback:B.rows}, function(C, k, t, b){
tartan(C, b.x, b.y, 108, 108, 54); C.rect(b.x, b.y, 108, 108, {line:GD, lw:2}); K.icon(C, b.x + 34, b.y + 34, 40, t, '#FFFFFF');
C.text(b.x + 124, b.y + 62, 80, 46, pad2(k + 1), {font:'head', size:38, wt:700, color:GD, lh:1, va:'b'});
});
}
function framed(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.52), tg:20, vMax:46, vw:function(cw){ return cw - 90; }}, function(C, k, t, b){
tartan(C, b.x + 8, b.y, b.w - 16, b.ah, 60); C.rect(b.x + 26, b.y + 18, b.w - 52, b.ah - 36, {fill:IV, line:GD, lw:1.5});
return {vc:k % 2 ? CL : GN, vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function band(C, Z, o){
K5.alt(C, Z, {off:66, fallback:B.rows, pre:function(C, g){ tartan(C, g.x, g.y - 18, g.w, 36, 48); }}, function(C, k, t, b){
K5.fill(C, Q.poly([], [[b.cx, b.cy - 40], [b.cx + 40, b.cy], [b.cx, b.cy + 40], [b.cx - 40, b.cy]]), GD);
C.ell(b.cx - 22, b.cy - 22, 44, 44, {fill:IV}); num(C, b.cx - 22, b.cy - 22, 44, 44, k, 16, GN);
});
}
function ledger(C, Z, o){
K5.list(C, Z, {aw:92, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
var s = Math.min(58, b.h - 16); tartan(C, b.x, b.cy - s / 2, s, s, s); C.rect(b.x, b.cy - s / 2, s, s, {line:GD, lw:1.5}); num(C, b.x, b.cy - s / 2, s, s, k, s * 0.36, '#FFFFFF');
C.line(b.x, b.y + b.h + 3, b.x + b.w, b.y + b.h + 3, {color:GD, lw:1.2, dash:true});
});
}
return {agenda:[['Ledger list', pg(ledger)]], p2:[['Swatch columns', pg(swatches)]], p3:[['Swatch columns', pg(swatches)]], p4:[['Swatch columns', pg(swatches)]], p56:[['Ledger list', pg(ledger)]], p78:[['Ledger list', pg(ledger)]],
stats:[['Plaid frames', pg(framed)]], timeline:[['Plaid band', pg(band)]]};
}
});
})();
