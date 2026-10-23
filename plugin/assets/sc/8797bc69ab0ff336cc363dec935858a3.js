
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 112 - Golden Section : the page is divided by the golden ratio into shrinking squares with one spiral line through them */
var INK = '#1C1C1C', OC = '#C99700', GY = '#E6E6E6', MU = '#5E5E5E', LN = '#DADADA', BG = '#FAFAFA';
var P = {bg:BG, text:INK, title:INK, muted:MU, accent:INK, accent2:OC, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [INK, OC, '#8C8C8C', '#E7C24A'];
function spiral(C, x, y, w, o){
o = o || {}; var h = w / 1.618, a = w - h, b = h - a, c = a - b, sq = [[x, y, h], [x + h, y, a], [x + h + a - b, y + a, b], [x + h, y + a + b - c, c]], f = o.fills || [], d = [], s = [];
sq.forEach(function(q, i){ if (f[i] === 'img') D.imgBox(C, q[0], q[1], q[2], q[2], {r:0}); else if (f[i]) C.rect(q[0], q[1], q[2], q[2], {fill:f[i]}); Q.rect(d, q[0], q[1], q[2], q[2]); });
Q.rect(d, x, y, w, h); K5.stroke(C, d, o.line || INK, o.lw || 1.5, o.op);
Q.arc(s, x + h, y + h, h, 180, 270); Q.arc(s, x + h, y + a, a, 270, 360, 'L'); Q.arc(s, x + h + a - b, y + a, b, 0, 90, 'L'); Q.arc(s, x + h + c, y + a + b - c, c, 90, 180, 'L');
K5.stroke(C, s, o.sc || INK, o.sw || 3, o.op);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
spiral(C, 560, 150, 660, {fills:[photo ? 'img' : GY, OC, INK, '#CFCFCF'], sc:photo ? '#FFFFFF' : INK});
K5.coverText(C, 88, 150, 430, {max:48, by:630, rule:function(C, x, y){ C.rect(x, y, 62, 4, {fill:INK}); C.rect(x + 62, y, 38, 4, {fill:OC}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
spiral(C, 700, 180, 500, {fills:[photo ? 'img' : INK, OC, GY, '#CFCFCF'], sc:photo ? '#FFFFFF' : OC});
K5.closeText(C, 88, 160, 560, {max:64, mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:k === 1 ? OC : INK}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'golden', name:'Golden Section', tag:'Off-white · ratio rectangles',
desc:'The page is divided by the golden ratio into shrinking squares with one spiral line through them, in ink, ochre and quiet greys, and block size shows importance. For design, architecture, brand strategy and prioritisation decks.',
fonts:{head:'Schibsted Grotesk', body:'Hanken Grotesk', headW:700, bodyW:400, headLh:1.08, gf:'Schibsted+Grotesk:wght@500;600;700;800|Hanken+Grotesk:wght@400;500;600;700'},
P:P, LC:LC, cardR:0, coverLbl:'Spiral', closeLbl:'Spiral',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; spiral(C, W - 196, 22, 160, {line:LN, sc:OC, sw:2, lw:1.2}); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 50, 4, {fill:INK}); C.rect(x + 50, y, 31, 4, {fill:OC}); return 18; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? INK : '#FFFFFF', line:INK, lw:1.5}); if (!o.big) C.rect(x + w - 26, y, 26, 26, {fill:OC});
return hot ? {tc:'#FFFFFF', mc:'#CFCFCF', ac:OC, hot:1} : {tc:INK, mc:MU, ac:INK};
},
row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1.5, w, 1.5, {fill:INK}); return {tc:INK, mc:MU, ac:INK}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? OC : INK}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.rect(x, y, s, s, {line:INK, lw:1.5}); K5.stroke(C, Q.arc([], x + s, y + s, s, 180, 270), OC, 2.5); num(C, x, y, s, s, k, s * 0.36, INK); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:BG, line:INK, lw:1.5}); K5.stroke(C, Q.arc([], cx + s / 2, cy + s / 2, s, 180, 270), OC, 2.5); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.34, INK); },
track:function(C, x1, y, x2){ C.rect(x1, y - 0.75, x2 - x1, 1.5, {fill:INK}); },
conc:{kind:'line', lineC:INK, label:'In proportion', lc:OC, tc:INK},
L:{cards:'Ratio cards', rows:'Measured rows', steps:'Square steps', stmt:'Proportion note', stats:'Plain numbers'},
sig:function(pg, B){
function blocks(C, Z, o){
var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var g = 10, R = {x:Z.x, y:Z.y, w:Z.w, h:Z.h}, boxes = [], st = [[INK, '#FFFFFF', '#CFCFCF'], [OC, INK, '#3A2E00'], ['#FFFFFF', INK, MU], [GY, INK, MU]];
for (var k = 0; k < n; k++) {
if (k === n - 1) boxes.push(R);
else if (k % 2 === 0) { var w1 = (R.w - g) * (k === 0 ? 0.42 : 0.5); boxes.push({x:R.x, y:R.y, w:w1, h:R.h}); R = {x:R.x + w1 + g, y:R.y, w:R.w - w1 - g, h:R.h}; }
else { var h1 = (R.h - g) * 0.56; boxes.push({x:R.x, y:R.y, w:R.w, h:h1}); R = {x:R.x, y:R.y + h1 + g, w:R.w, h:R.h - h1 - g}; }
}
boxes.forEach(function(b, k){
var t = it[k], s = st[k], pad = b.h < 150 ? 20 : 30, tw = b.w - pad * 2 - 54;
C.rect(b.x, b.y, b.w, b.h, {fill:s[0], line:k === 2 ? INK : null, lw:1.5});
C.text(b.x + b.w - pad - 60, b.y + pad - 4, 60, 30, pad2(k + 1), {font:'head', size:b.h < 150 ? 18 : 24, wt:700, color:k === 0 ? OC : s[1], align:'r', lh:1});
var F = K.fitHD(C, [t], tw, 60, b.h - pad * 2, {hMax:k === 0 ? 30 : 22, dMax:k === 0 ? 19 : 15.5, hMin:12, dMin:10, noBoost:true}), bh = K3.bH(C, t, tw, F);
K.hd(C, b.x + pad, b.y + Math.max(pad, (b.h - bh) / 2), tw, b.h - pad * 1.4, t, F, {tc:s[1], mc:s[2]});
});
}
function squares(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:20, vMax:44, vw:function(cw, ah){ return Math.min(cw - 70, ah * 0.5); }}, function(C, k, t, b){
var s = Math.min(b.w - 30, b.ah) * [1, 0.86, 0.74, 0.64][k], c = [INK, OC, '#8C8C8C', INK][k], x = b.cx - s / 2, y = b.y + b.ah - s;
C.rect(x, y, s, s, {fill:c}); K5.stroke(C, Q.arc([], x + s, y + s, s, 180, 270), k === 1 ? INK : OC, 2.5);
return {vc:'#FFFFFF', vx:x + 8, vw:s - 16, vy:y + (s - b.vs * 1.15) / 2};
});
}
function grow(C, Z, o){
K5.alt(C, Z, {same:true, at:0.28, off:64, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y + 44, g.w, 1.5, {fill:INK}); }}, function(C, k, t, b){
var s = 44 + 44 * (b.n > 1 ? k / (b.n - 1) : 1), x = b.cx - s / 2, y = b.cy + 44 - s, last = k === b.n - 1;
C.rect(x, y, s, s, {fill:last ? OC : BG, line:INK, lw:1.5}); K5.stroke(C, Q.arc([], x + s, y + s, s, 180, 270), last ? INK : OC, 2.5); num(C, x, y, s, s, k, 16, INK);
});
}
return {p2:[['Ratio blocks', pg(blocks)]], p3:[['Ratio blocks', pg(blocks)]], p4:[['Ratio blocks', pg(blocks)]], stats:[['Scaled squares', pg(squares)]], timeline:[['Growing squares', pg(grow)]]};
}
});
})();
