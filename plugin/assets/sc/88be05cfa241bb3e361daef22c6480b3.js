
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 117 - Kanban : a work board with sticky notes in four pastel colours */
var INK = '#111827', MU = '#5B6472', LN = '#DDE0E5', BG = '#F3F4F6', LANE = '#E5E7EB', NC = ['#FDE68A', '#BFDBFE', '#FBCFE8', '#BBF7D0'];
var P = {bg:BG, text:INK, title:INK, muted:MU, accent:INK, accent2:'#F59E0B', card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = ['#F59E0B', '#3B82F6', '#EC4899', '#22C55E'];
function nc(k){ return NC[k % 4]; }
function rot(pts, cx, cy, a){ var c = Math.cos(a * Math.PI / 180), s = Math.sin(a * Math.PI / 180); return pts.map(function(p){ return [cx + (p[0] - cx) * c - (p[1] - cy) * s, cy + (p[0] - cx) * s + (p[1] - cy) * c]; }); }
function tnote(C, x, y, s, col, a, lines){
var cx = x + s / 2, cy = y + s / 2, R = function(p){ return rot(p, cx, cy, a); }, d = [];
K5.fill(C, Q.poly([], R([[x + 5, y + 8], [x + s + 5, y + 8], [x + s + 5, y + s + 8], [x + 5, y + s + 8]])), '#000000', 0.13); K5.fill(C, Q.poly([], R([[x, y], [x + s, y], [x + s, y + s], [x, y + s]])), col);
for (var i = 0; i < (lines || 3); i++) { var q = R([[x + s * 0.14, y + s * (0.3 + i * 0.16)], [x + s * (i === (lines || 3) - 1 ? 0.56 : 0.86), y + s * (0.3 + i * 0.16)]]); Q.line(d, q[0][0], q[0][1], q[1][0], q[1][1]); }
K5.stroke(C, d, INK, Math.max(2, s * 0.022), 0.45);
}
function snote(C, x, y, w, h, col){ C.rect(x + 4, y + 6, w, h, {fill:'#000000', op:0.1}); C.rect(x, y, w, h, {fill:col}); K5.fill(C, Q.poly([], [[x + w - 22, y + h], [x + w, y + h - 22], [x + w, y + h]]), '#000000', 0.12); C.rect(x + w / 2 - 34, y - 9, 68, 18, {fill:'#FFFFFF', op:0.7}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { C.rect(766, 126, 334, 392, {fill:'#000000', op:0.12}); C.rect(760, 118, 334, 392, {fill:'#FFFFFF'}); D.imgBox(C, 780, 138, 294, 294, {r:0}); tnote(C, 1040, 380, 170, NC[0], 6); tnote(C, 700, 470, 150, NC[2], -5); }
else { tnote(C, 740, 110, 200, NC[0], -5); tnote(C, 980, 170, 200, NC[1], 4); tnote(C, 790, 370, 200, NC[2], 3); tnote(C, 1020, 420, 200, NC[3], -6); }
K5.coverText(C, 88, 130, 580, {max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { C.rect(826, 126, 334, 392, {fill:'#000000', op:0.12}); C.rect(820, 118, 334, 392, {fill:'#FFFFFF'}); D.imgBox(C, 840, 138, 294, 294, {r:0}); }
else { tnote(C, 800, 130, 190, NC[3], 4, 2); tnote(C, 1010, 250, 190, NC[0], -4); tnote(C, 830, 400, 190, NC[1], -3, 2); }
K5.closeText(C, 88, 150, 620, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:nc(k)}); C.icon(x + 10, y + 10, 16, c.icon, INK); }});
}
K5.make({
id:'kanban', name:'Kanban', tag:'Soft grey · sticky-note board',
desc:'A work board with sticky notes in yellow, blue, pink and green: every card is a note with a strip of tape, points sit in board lanes and numbers are written on notes. For agile teams, sprints, workshops, retrospectives and project updates.',
fonts:{head:'Be Vietnam Pro', body:'Be Vietnam Pro', headW:700, bodyW:400, headLh:1.1, gf:'Be+Vietnam+Pro:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:0, imgR:6, coverLbl:'Note wall', closeLbl:'Note wall',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; for (var i = 0; i < 4; i++) { C.rect(W - 172 + i * 34 + 2, 26, 26, 26, {fill:'#000000', op:0.1}); C.rect(W - 172 + i * 34, 23, 26, 26, {fill:NC[i]}); } },
cover:cover, closing:closing,
rule:function(C, x, y){ for (var i = 0; i < 4; i++) C.rect(x + i * 18, y - 2, 14, 14, {fill:NC[i]}); return 26; },
pad:26,
card:function(C, x, y, w, h, k, o){
if (o.big) { snote(C, x, y, w, h, NC[0]); return {tc:INK, mc:'#3F4753', ac:INK}; }
snote(C, x, y, w, h, o.hot ? '#FCD34D' : nc(k)); return {tc:INK, mc:'#3F4753', ac:INK, hot:o.hot ? 1 : 0};
},
row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:'#FFFFFF', r:8}); C.rect(x, y, 8, h, {fill:nc(k), r:0}); return {tc:INK, mc:MU, ac:INK}; },
mark:function(C, x, y, s, t, k, cc){ C.ell(x, y, s, s, {fill:'#FFFFFF', op:0.75}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, INK); },
rowMark:function(C, x, y, s, t, k){ C.rect(x + 2, y + 3, s, s, {fill:'#000000', op:0.1}); C.rect(x, y, s, s, {fill:nc(k)}); num(C, x, y, s, s, k, s * 0.38, INK); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2 + 3, cy - s / 2 + 4, s, s, {fill:'#000000', op:0.1}); C.rect(cx - s / 2, cy - s / 2, s, s, {fill:nc(k)}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, INK); },
track:function(C, x1, y, x2){ C.rect(x1, y - 4, x2 - x1, 8, {fill:LANE, r:4}); },
conc:{kind:'box', fill:'#FFFFFF', r:8, label:'Done when', lc:'#B45309', tc:INK},
L:{cards:'Sticky notes', rows:'Board rows', steps:'Note steps', stmt:'Big note', stats:'Plain numbers'},
sig:function(pg, B){
function lanes(C, Z, o){
var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var g = 18, cw = (Z.w - g * (n - 1)) / n, tw = cw - 68, hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - 76, 44, {font:'head', max:20, min:12, maxLines:2});
var ds = C.groupFit(it.map(function(t){ return t.d || ''; }), tw, Z.h - 150, {font:'body', max:17, min:10, lh:1.38});
it.forEach(function(t, k){
var x = Z.x + k * (cw + g), dh = C.measureText(t.d || '', tw, {font:'body', max:ds, min:ds, lh:1.38}).h, nh = Math.min(Z.h - 96, Math.max(120, dh + 60));
C.rect(x, Z.y, cw, Z.h, {fill:LANE, r:14}); C.ell(x + cw - 42, Z.y + 20, 26, 26, {fill:'#FFFFFF'}); num(C, x + cw - 42, Z.y + 20, 26, 26, k, 11.5, INK, String(k + 1));
if (t.h) C.text(x + 18, Z.y + 10, cw - 70, 48, t.h, {font:'head', size:hs, color:INK, va:'m', lh:1.14, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
snote(C, x + 16, Z.y + 76, cw - 32, nh, nc(k)); K.icon(C, x + cw - 56, Z.y + 76 + nh - 40, 24, t, INK);
C.text(x + 34, Z.y + 100, tw, nh - 40, t.d, {font:'body', size:ds, color:INK, lh:1.38, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
if (Z.h - 96 - nh > 44) { C.rect(x + 16, Z.y + 92 + nh, cw - 32, Math.min(46, Z.h - 108 - nh), {fill:'#FFFFFF', op:0.7}); C.rect(x + 30, Z.y + 106 + nh, (cw - 60) * 0.6, 4, {fill:INK, op:0.18}); }
});
}
function stickies(C, Z, o){
K5.stat(C, Z, {ah:Math.min(200, Z.h * 0.5), tg:24, vMax:50, vw:function(cw){ return cw - 80; }}, function(C, k, t, b){
snote(C, b.x + 16, b.y + 8, b.w - 32, b.ah - 8, nc(k)); return {vc:INK, vy:b.y + 8 + (b.ah - 8 - b.vs * 1.15) / 2};
});
}
function sprint(C, Z, o){
K5.alt(C, Z, {same:true, at:0.22, off:62, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 5, g.w, 10, {fill:LANE, r:5}); }}, function(C, k, t, b){
tnote(C, b.cx - 40, b.cy - 40, 80, nc(k), k % 2 ? 5 : -5, 0); num(C, b.cx - 40, b.cy - 40, 80, 80, k, 26, INK);
});
}
return {p2:[['Board lanes', pg(lanes)]], p3:[['Board lanes', pg(lanes)]], p4:[['Board lanes', pg(lanes)]], stats:[['Number notes', pg(stickies)]], timeline:[['Sprint line', pg(sprint)]]};
}
});
})();
