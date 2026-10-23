
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 125 - Coin Stack : stacks of gold coins grow taller from left to right under a navy growth line */
var NV = '#132A4F', GD = '#E3B23C', DG = '#B8891E', BL = '#2F5597', MU = '#55637D', LN = '#D8DEE9', PALE = '#F4F6FA';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:NV, accent2:GD, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [NV, GD, BL, DG];
function stack(C, cx, yb, rx, n, th){
var ry = rx * 0.3, body = [], tops = [], i; th = th || rx * 0.44;
for (i = 0; i < n; i++) { var y = yb - i * th; Q.ell(body, cx, y, rx, ry); Q.rect(body, cx - rx, y - th, rx * 2, th); Q.ell(tops, cx, y - th, rx, ry); }
K5.fill(C, body, DG); K5.fill(C, tops, GD); K5.stroke(C, tops, DG, Math.max(1, rx * 0.03));
return yb - n * th - ry;
}
function coin(C, cx, cy, r){ C.ell(cx - r, cy - r, r * 2, r * 2, {fill:GD, line:DG, lw:Math.max(1.5, r * 0.09)}); C.ell(cx - r * 0.72, cy - r * 0.72, r * 1.44, r * 1.44, {line:DG, lw:1}); }
function arrowUp(C, x1, y1, x2, y2){ C.line(x1, y1, x2, y2, {color:NV, lw:5}); var a = Math.atan2(y2 - y1, x2 - x1), p = function(d, s){ return [x2 - Math.cos(a) * d - Math.sin(a) * s, y2 - Math.sin(a) * d + Math.cos(a) * s]; }; K5.fill(C, Q.poly([], [[x2 + Math.cos(a) * 12, y2 + Math.sin(a) * 12], p(22, 16), p(22, -16)]), NV); }
function cover(C){
var photo = (C.o || {}).img === 'photo', ks = photo ? [2, 3, 5, 7] : [3, 6, 9, 13];
C.bg({fill:PALE});
if (photo) D.imgBox(C, 720, 80, 480, 260, {r:12});
ks.forEach(function(k, i){ stack(C, 790 + i * 122, 610, 52, k); });
if (!photo) arrowUp(C, 730, 420, 1180, 130);
K5.coverText(C, 88, 130, 560, {max:54, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:PALE});
if (photo) D.imgBox(C, 800, 80, 400, 270, {r:12});
[2, 4, 6, 9].forEach(function(k, i){ stack(C, 850 + i * 100, 610, 42, photo ? Math.ceil(k / 2) : k); });
K5.closeText(C, 88, 150, 640, {mark:function(C, x, y, k, c){ coin(C, x + 18, y + 18, 18); C.icon(x + 10, y + 10, 16, c.icon, NV); }});
}
K5.make({
id:'coinstack', name:'Coin Stack', tag:'White · rising gold coins',
desc:'Stacks of gold coins grow taller from left to right under a navy growth line, and every figure is counted in coins: stack-height numbers, growing stacks and coin markers. For revenue, savings, investment, pricing and fundraising decks.',
fonts:{head:'Merriweather Sans', body:'Merriweather Sans', headW:700, bodyW:400, headLh:1.1, gf:'Merriweather+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:12, imgR:12, tMax:38, coverLbl:'Rising stacks', closeLbl:'Rising stacks',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; stack(C, W - 150, 76, 22, 2); stack(C, W - 100, 76, 22, 3); stack(C, W - 50, 76, 22, 5); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:GD, r:2.5}); return 16; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : PALE, r:12}); if (!o.big) coin(C, x + w - 32, y + 32, 15);
return hot ? {tc:'#FFFFFF', mc:'#C6D0E2', ac:GD, hot:1} : {tc:NV, mc:MU, ac:NV};
},
mark:function(C, x, y, s, t, k, cc){ coin(C, x + s / 2, y + s / 2, s / 2); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, NV); },
rowMark:function(C, x, y, s, t, k){ coin(C, x + s / 2, y + s / 2, s / 2); num(C, x, y, s, s, k, s * 0.34, NV); },
node:function(C, cx, cy, s, k){ coin(C, cx, cy, s / 2); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, NV); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:NV, r:2}); },
conc:{kind:'pill', fill:GD, op:0.22, label:'Bottom line', lc:'#7A5A0E', tc:NV},
L:{cards:'Mint cards', rows:'Coin rows', steps:'Coin steps', stmt:'Treasury note', stats:'Plain numbers'},
sig:function(pg, B){
function stacks(C, Z, o){
K5.stat(C, Z, {ah:Math.min(250, Z.h * 0.6), tg:22, vMax:40, vw:function(cw){ return cw - 40; }}, function(C, k, t, b){
var m = /^\s*([\d.]+)\s*%/.exec(b.val), n = m ? Math.max(2, Math.round(parseFloat(m[1]) / 100 * 8)) : [3, 5, 6, 8][k], rx = Math.min(58, b.w * 0.26), th = Math.min(rx * 0.44, (b.ah - 70) / 8), top = stack(C, b.cx, b.y + b.ah - rx * 0.3, rx, n, th);
return {vc:NV, vy:top - b.vs * 1.3};
});
}
function grow(C, Z, o){
K5.cols(C, Z, {max:5, ah:150, gap:26, tg:16, fallback:B.rows}, function(C, k, t, b){
stack(C, b.x + 44, b.y + 128, 42, Math.min(6, k + 2), 17); K.icon(C, b.x + 104, b.y + 16, 36, t, NV);
C.text(b.x + 104, b.y + 74, 100, 60, pad2(k + 1), {font:'head', size:40, wt:700, color:DG, lh:1, va:'b'}); C.rect(b.x, b.y + 144, b.w, 2, {fill:LN});
});
}
function savings(C, Z, o){
K5.alt(C, Z, {same:true, at:0.44, off:30, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y + 2, g.w, 4, {fill:NV, r:2}); }}, function(C, k, t, b){
var top = stack(C, b.cx, b.cy - 6, 36, k + 1, 16); num(C, b.cx - 30, top - 34, 60, 28, k, 16, NV);
});
}
return {p2:[['Growing stacks', pg(grow)]], p3:[['Growing stacks', pg(grow)]], p4:[['Growing stacks', pg(grow)]], stats:[['Coin stacks', pg(stacks)]], timeline:[['Savings line', pg(savings)]]};
}
});
})();
