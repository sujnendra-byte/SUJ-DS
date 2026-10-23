
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 106 - Strata : tilted bands stack like layers of rock, light at the top and deep at the base, with one orange seam */
var S = ['#C9CED6', '#778DA9', '#415A77', '#1B263B', '#0D1B2A'], OR = '#E07A1F', INK = '#0D1B2A', MU = '#55657A', LN = '#D5DAE1', PALE = '#EEF0F3';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:'#415A77', accent2:OR, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = ['#415A77', OR, '#778DA9', '#1B263B'];
function band(C, yl, yr, col){ K5.fill(C, Q.poly([], [[0, yl], [W, yr], [W, H], [0, H]]), col); }
function sh(k, n){ return mix(S[0], S[4], n > 1 ? k / (n - 1) : 0.6); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:'#E0E1DD'});
if (photo) D.imgBox(C, 800, 0, 480, 440, {r:0});
band(C, 500, 340, S[1]); band(C, 566, 430, S[2]); band(C, 628, 520, S[3]); band(C, 680, 610, S[4]); C.line(0, 500, W, 340, {color:OR, lw:5});
K5.coverText(C, 88, 76, photo ? 660 : 760, {max:58, th:140, gap:14, sw:600, by:436});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:INK});
K5.fill(C, Q.poly([], [[0, 0], [W, 0], [W, 70], [0, 190]]), S[2]); K5.fill(C, Q.poly([], [[0, 0], [W, 0], [W, 28], [0, 120]]), S[1]); K5.fill(C, Q.poly([], [[0, 0], [W, 0], [W, 0], [0, 56]]), S[0]); C.line(0, 190, W, 70, {color:OR, lw:5});
if (photo) D.imgBox(C, 830, 240, 362, 380, {r:0});
K5.closeText(C, 88, 250, 680, {tc:'#FFFFFF', sc:'#B8C4D4', mc:OR});
}
K5.make({
id:'strata', name:'Strata', tag:'Slate blue · tilted rock layers',
desc:'Tilted bands in slate blues stack like layers of rock, light at the top and deep at the base, cut by one orange seam: layer stacks, core-sample numbers and a seam timeline. For mining, geology, energy, layered frameworks and technology stacks.',
fonts:{head:'Figtree', body:'Figtree', headW:800, bodyW:400, headLh:1.08, gf:'Figtree:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, headTop:70, coverLbl:'Rock layers', closeLbl:'Rock layers',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; K5.fill(C, Q.poly([], [[0, 0], [W, 0], [W, 10], [0, 38]]), S[1]); K5.fill(C, Q.poly([], [[0, 0], [W, 0], [W, 4], [0, 20]]), S[3]); C.line(0, 38, W, 10, {color:OR, lw:2.5}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 60, 4, {fill:S[3]}); C.rect(x, y + 6, 44, 4, {fill:S[1]}); C.rect(x, y + 12, 28, 4, {fill:OR}); return 26; },
pad:26,
card:function(C, x, y, w, h, k, o){
var hot = o.hot; K5.fill(C, Q.poly([], [[x, y + 14], [x + w, y], [x + w, y + h], [x, y + h]]), hot ? S[3] : PALE); if (!o.big) C.line(x, y + 14, x + w, y, {color:hot ? OR : S[k % 3 + 1], lw:4});
return hot ? {tc:'#FFFFFF', mc:'#B8C4D4', ac:OR, hot:1} : {tc:INK, mc:MU, ac:'#415A77'};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? OR : S[k % 3 + 1]}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ for (var i = 0; i < 3; i++) C.rect(x, y + i * s / 3, s, s / 3 - 2, {fill:i === k % 3 ? OR : S[i + 1]}); num(C, x + s + 6, y, s * 0.8, s, k, s * 0.4, INK); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:sh(k + 1, 6)}); C.rect(cx - s / 2, cy - s / 2, s, 5, {fill:OR}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 4, x2 - x1, 8, {fill:S[0]}); },
L:{cards:'Slab cards', rows:'Bed rows', steps:'Level steps', stmt:'Bedrock note', stats:'Slab numbers'},
sig:function(pg, B){
function stack(C, Z, o){
var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 760) return B.rows(C, Z, o);
var g = 8, bh = Math.min((Z.h - g * (n - 1)) / n, 120), y0 = Z.y + (Z.h - (bh * n + g * (n - 1))) / 2, hw = Math.min(300, Z.w * 0.27), dx = Z.x + 104 + hw + 28, dw = Z.x + Z.w - dx - 28;
var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), hw, bh - 26, {font:'head', max:23, min:12, maxLines:2}), ds = C.groupFit(it.map(function(t){ return t.d || ''; }), dw, bh - 26, {font:'body', max:17, min:10, lh:1.36});
it.forEach(function(t, k){
var y = y0 + k * (bh + g), c = sh(k, Math.max(n, 4)), on = K.on(c);
K5.fill(C, Q.poly([], [[Z.x, y + 10], [Z.x + Z.w, y], [Z.x + Z.w, y + bh], [Z.x, y + bh]]), c);
C.text(Z.x + 26, y + 8, 70, bh - 8, pad2(k + 1), {font:'head', size:Math.min(30, bh * 0.36), wt:800, color:k === 0 ? OR : on, va:'m', lh:1});
if (t.h) C.text(Z.x + 104, y + 8, hw, bh - 8, t.h, {font:'head', size:hs, color:on, va:'m', lh:1.14, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
C.text(t.h ? dx : Z.x + 104, y + 8, t.h ? dw : dw + hw + 28, bh - 8, t.d, {font:'body', size:ds, color:on, va:'m', lh:1.36, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
});
}
function cores(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:22, vMax:48, align:'l', pad:36, vw:function(cw){ return cw - 130; }}, function(C, k, t, b){
var x = b.x + 18, w = 66, R = K5.rnd(7 + k * 13), y = b.y + 12, hT = b.ah - 24, cuts = [0, 0.18 + R() * 0.1, 0.42 + R() * 0.12, 0.7 + R() * 0.1, 1];
for (var i = 0; i < 4; i++) C.rect(x, y + hT * cuts[i], w, hT * (cuts[i + 1] - cuts[i]), {fill:S[i + (k % 2 ? 1 : 0)] || S[4]});
C.rect(x, y + hT * cuts[1 + k % 3] - 3, w, 6, {fill:OR}); C.ell(x, y - 11, w, 22, {fill:S[k % 2 ? 1 : 0], line:'#FFFFFF', lw:2}); C.ell(x, y + hT - 11, w, 22, {fill:S[k % 2 ? 4 : 3]});
return {vc:INK, vx:x + w + 20, vw:b.w - w - 50, vy:b.y + b.ah * 0.5 - b.vs * 0.58, va:'l'};
});
}
function seam(C, Z, o){
K5.alt(C, Z, {same:true, at:0.3, off:62, fallback:B.rows, pre:function(C, g){
K5.fill(C, Q.poly([], [[g.x, g.y + 34], [g.x + g.w, g.y - 34], [g.x + g.w, g.y + 14], [g.x, g.y + 46]]), S[0]); C.line(g.x, g.y + 34, g.x + g.w, g.y - 34, {color:OR, lw:4});
}}, function(C, k, t, b){
var y = b.cy + 34 - 68 * ((b.cx - (b.cx - b.cw * (k + 0.5))) / (b.cw * b.n));
C.rect(b.cx - 26, y - 52, 52, 52, {fill:sh(k + 1, b.n + 1)}); num(C, b.cx - 26, y - 52, 52, 52, k, 19, '#FFFFFF');
});
}
return {agenda:[['Layer stack', pg(stack)]], p2:[['Layer stack', pg(stack)]], p3:[['Layer stack', pg(stack)]], p4:[['Layer stack', pg(stack)]], p56:[['Layer stack', pg(stack)]],
stats:[['Core samples', pg(cores)]], timeline:[['Orange seam', pg(seam)]]};
}
});
})();
