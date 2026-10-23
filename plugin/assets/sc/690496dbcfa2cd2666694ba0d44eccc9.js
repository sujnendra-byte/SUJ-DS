
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 114 - Perforation : cards have the punched, perforated edge of a postage stamp, with a postmark ring */
var NV = '#1D3557', RD = '#E63946', BG = '#E8EEF4', ST = '#457B9D', MU = '#566B85', LN = '#C9D5E1';
var P = {bg:BG, text:NV, title:NV, muted:MU, accent:NV, accent2:RD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [NV, RD, ST, '#A8DADC'];
function stamp(C, x, y, w, h, r, bgc){
var d = [], nx = Math.max(2, Math.round(w / (r * 3.1))), ny = Math.max(2, Math.round(h / (r * 3.1))), i;
C.rect(x, y, w, h, {fill:'#FFFFFF'});
for (i = 0; i <= nx; i++) { Q.circ(d, x + w * i / nx, y, r); Q.circ(d, x + w * i / nx, y + h, r); }
for (i = 1; i < ny; i++) { Q.circ(d, x, y + h * i / ny, r); Q.circ(d, x + w, y + h * i / ny, r); }
K5.fill(C, d, bgc || BG);
}
function postmark(C, cx, cy, r, op){
var d = []; Q.circ(d, cx, cy, r); Q.circ(d, cx, cy, r * 0.72);
for (var j = 0; j < 4; j++) { var pts = [], y0 = cy - r * 0.5 + j * r * 0.33; for (var i = 0; i <= 24; i++) pts.push([cx + r * 0.7 + i * r * 0.07, y0 + Math.sin(i * 0.8) * r * 0.07]); Q.pts(d, pts); }
K5.stroke(C, d, NV, 2.2, op == null ? 0.55 : op);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
stamp(C, 110, 90, 1060, 540, 9); C.rect(136, 116, 1008, 488, {line:NV, lw:1.5});
if (photo) D.imgBox(C, 770, 150, 340, 420, {r:0}); else postmark(C, 900, 430, 104);
K5.coverText(C, 190, 168, photo ? 540 : 640, {max:54, by:548, rule:function(C, x, y){ C.rect(x, y, 70, 5, {fill:RD}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
stamp(C, 110, 90, 1060, 540, 9); C.rect(136, 116, 1008, 488, {line:NV, lw:1.5});
if (photo) D.imgBox(C, 770, 150, 340, 420, {r:0}); else postmark(C, 900, 240, 96);
K5.closeText(C, 190, 170, 560, {max:64, mc:RD});
}
K5.make({
id:'perforation', name:'Perforation', tag:'Pale blue · postage stamp edges',
desc:'Every page is a white sheet with the punched, perforated edge of a postage stamp on a pale blue ground, with stamp cards, red denominations and postmark rings. For logistics, post, travel, export, e-commerce and membership decks.',
fonts:{head:'Zilla Slab', body:'Nunito Sans', headW:700, bodyW:400, headLh:1.08, gf:'Zilla+Slab:wght@500;600;700|Nunito+Sans:wght@400;600;700'},
P:P, LC:LC, cardR:0, headTop:66, coverLbl:'Stamp', closeLbl:'Stamp',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; stamp(C, 28, 28, W - 56, H - 56, 8); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 56, 5, {fill:RD}); return 16; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : '#F1F5F9'}); C.rect(x + 8, y + 8, w - 16, h - 16, {line:hot ? '#FFFFFF' : ST, lw:1.5, dash:true, lop:hot ? 0.6 : 1});
return hot ? {tc:'#FFFFFF', mc:'#C7D3E2', ac:'#FFFFFF', hot:1} : {tc:NV, mc:MU, ac:RD};
},
pad:30,
row:function(C, x, y, w, h, k){ C.line(x, y + h - 1, x + w, y + h - 1, {color:ST, lw:1.5, dash:true}); return {tc:NV, mc:MU, ac:RD}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {line:hot ? '#FFFFFF' : NV, lw:1.8}); C.ell(x + s * 0.12, y + s * 0.12, s * 0.76, s * 0.76, {fill:hot ? RD : NV}); K.icon(C, x + s * 0.28, y + s * 0.28, s * 0.44, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {line:NV, lw:1.8}); C.ell(x + s * 0.12, y + s * 0.12, s * 0.76, s * 0.76, {fill:k === 0 ? RD : NV}); num(C, x, y, s, s, k, s * 0.34, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:'#FFFFFF', line:NV, lw:1.8}); C.ell(cx - s * 0.38, cy - s * 0.38, s * 0.76, s * 0.76, {fill:k === 0 ? RD : NV}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.32, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:ST, lw:2, dash:true}); },
conc:{kind:'box', fill:'#F1F5F9', r:0, label:'Delivered', lc:RD, tc:NV},
L:{cards:'Coupon cards', rows:'Coupon rows', steps:'Postmark steps', stmt:'Postcard note', stats:'Plain numbers'},
sig:function(pg, B){
function stamps(C, Z, o){
K5.cols(C, Z, {max:5, ah:92, gap:0, pad:40, tg:8, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y, g.w, g.h, {fill:BG}); }}, function(C, k, t, b){
stamp(C, b.x + 16, b.y + 16, b.w - 32, b.h - 32, 6); C.rect(b.x + 28, b.y + 28, b.w - 56, b.h - 56, {line:NV, lw:1.2});
K.icon(C, b.x + 42, b.y + 44, 34, t, NV); C.text(b.x + b.w - 122, b.y + 40, 80, 40, pad2(k + 1), {font:'head', size:30, wt:700, color:RD, align:'r', va:'m', lh:1});
});
}
function denoms(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.52), tg:22, vMax:50, vw:function(cw){ return cw - 110; }, pre:function(C, g){ C.rect(g.x, g.y - 12, g.w, g.ah + 24, {fill:BG}); }}, function(C, k, t, b){
stamp(C, b.x + 14, b.y, b.w - 28, b.ah, 6); C.rect(b.x + 26, b.y + 12, b.w - 52, b.ah - 24, {line:NV, lw:1.2}); C.rect(b.cx - 22, b.y + b.ah - 40, 44, 4, {fill:RD});
return {vc:NV, vy:b.y + (b.ah - b.vs * 1.15) / 2 - 6};
});
}
function marks(C, Z, o){
K5.alt(C, Z, {off:66, fallback:B.rows, pre:function(C, g){ var d = []; for (var j = -1; j <= 1; j++) { var pts = []; for (var i = 0; i <= 120; i++) pts.push([g.x + g.w * i / 120, g.y + j * 11 + Math.sin(i * 0.55) * 5]); Q.pts(d, pts); } K5.stroke(C, d, NV, 2, 0.4); }}, function(C, k, t, b){
C.ell(b.cx - 44, b.cy - 44, 88, 88, {fill:'#FFFFFF', line:NV, lw:2.2}); C.ell(b.cx - 32, b.cy - 32, 64, 64, {line:NV, lw:1.5}); num(C, b.cx - 32, b.cy - 32, 64, 64, k, 22, k === 0 ? RD : NV);
});
}
return {p2:[['Stamp sheet', pg(stamps)]], p3:[['Stamp sheet', pg(stamps)]], p4:[['Stamp sheet', pg(stamps)]], stats:[['Denominations', pg(denoms)]], timeline:[['Postmarks', pg(marks)]]};
}
});
})();
