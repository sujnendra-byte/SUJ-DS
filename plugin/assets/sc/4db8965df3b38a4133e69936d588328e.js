
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 119 - Cargo : stacked shipping containers with ribbed steel sides, in blue, orange and black */
var INK = '#1B1B1E', BL = '#0B5394', OR = '#F26419', SK = '#2E86AB', MU = '#5A5F68', LN = '#D5DCE5', PALE = '#F1F5F9';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:BL, accent2:OR, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [BL, OR, INK, SK];
function lc(k){ return LC[k % 4]; }
function cont(C, x, y, w, h, col, plate){
var d = [], n = Math.max(3, Math.floor((w - 16) / 22)), st = (w - 16) / n, rw = Math.max(3, st * 0.34);
C.rect(x, y, w, h, {fill:col});
for (var i = 0; i < n; i++) Q.rect(d, x + 8 + i * st + (st - rw) / 2, y + h * 0.12, rw, h * 0.76);
K5.fill(C, d, '#000000', 0.2); C.rect(x, y, w, h * 0.09, {fill:'#000000', op:0.28}); C.rect(x, y + h * 0.91, w, h * 0.09, {fill:'#000000', op:0.28});
if (plate) C.rect(x + w * 0.5 - plate / 2, y + h * 0.2, plate, h * 0.6, {fill:col});
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:PALE});
cont(C, 700, 430, 260, 150, BL); cont(C, 970, 430, 260, 150, OR); cont(C, 780, 270, 260, 150, INK); cont(C, 1050, 270, 260, 150, BL);
if (photo) D.imgBox(C, 780, 70, 450, 190, {r:0}); else cont(C, 860, 110, 260, 150, OR);
C.rect(660, 580, 620, 12, {fill:INK});
K5.coverText(C, 88, 120, 580, {max:72, lh:0.98, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:PALE});
for (var i = 0; i < 5; i++) cont(C, 30 + i * 248, 520, 238, 130, lc(i)); cont(C, 154, 380, 238, 130, OR); cont(C, 898, 380, 238, 130, BL); C.rect(0, 650, W, 12, {fill:INK});
if (photo) D.imgBox(C, 800, 70, 392, 290, {r:0});
K5.closeText(C, 88, 70, 660, {max:88, mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:lc(k)}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'cargo', name:'Cargo', tag:'Steel grey · shipping containers',
desc:'Stacked shipping containers with ribbed steel sides in blue, orange and black, tall stencil-style capitals, container-topped columns, load numbers and a goods train timeline. For shipping, logistics, supply chain, warehousing and trade.',
fonts:{head:'Big Shoulders Display', body:'Roboto', headW:800, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Big+Shoulders+Display:wght@600;700;800;900|Roboto:wght@400;500;700'},
P:P, LC:LC, cardR:0, tMax:48, coverLbl:'Container yard', closeLbl:'Container yard',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; cont(C, W - 214, 18, 96, 40, BL); cont(C, W - 112, 18, 96, 40, OR); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 40, 8, {fill:BL}); C.rect(x + 44, y, 40, 8, {fill:OR}); return 20; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? INK : PALE}); if (!o.big) cont(C, x, y, w, 16, hot ? OR : lc(k)); else C.rect(x, y, 8, h, {fill:OR});
return hot ? {tc:'#FFFFFF', mc:'#C9CCD2', ac:OR, hot:1} : {tc:INK, mc:MU, ac:BL};
},
pad:28,
mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.hot ? OR : lc(k); C.rect(x, y, s, s, {fill:c}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ cont(C, x - 4, y + s * 0.14, s + 8, s * 0.72, lc(k), s * 0.6); num(C, x - 4, y, s + 8, s, k, s * 0.42, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ cont(C, cx - s * 0.8, cy - s * 0.4, s * 1.6, s * 0.8, lc(k), s * 0.7); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.42, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y + 24, x2 - x1, 6, {fill:INK}); },
conc:{kind:'box', fill:INK, r:0, label:'Delivered', lc:'#FF9B63', tc:'#FFFFFF'},
L:{cards:'Crate cards', rows:'Manifest rows', steps:'Crate steps', stmt:'Bill of lading', stats:'Plain numbers'},
sig:function(pg, B){
function stacks(C, Z, o){
K5.cols(C, Z, {max:5, ah:96, gap:14, pad:20, tg:20, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y + 96, g.w, g.h - 96, {fill:PALE}); }}, function(C, k, t, b){
cont(C, b.x, b.y, b.w, 92, lc(k), 0); C.rect(b.x + 14, b.y + 22, 86, 48, {fill:lc(k)}); C.text(b.x + 18, b.y + 22, 80, 48, pad2(k + 1), {font:'head', size:36, wt:800, color:'#FFFFFF', va:'m', lh:1});
C.rect(b.x + b.w - 64, b.y + 22, 50, 48, {fill:lc(k)}); K.icon(C, b.x + b.w - 56, b.y + 29, 34, t, '#FFFFFF');
});
}
function loads(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.48), tg:22, vMax:60, vw:function(cw){ return cw * 0.56; }}, function(C, k, t, b){
cont(C, b.x + 8, b.y, b.w - 16, b.ah, lc(k), b.w * 0.64); return {vc:'#FFFFFF', vx:b.cx - b.w * 0.3, vw:b.w * 0.6, vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function train(C, Z, o){
K5.alt(C, Z, {same:true, at:0.24, off:74, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y + 48, g.w, 6, {fill:INK}); C.rect(g.x, g.y - 2, g.w, 4, {fill:INK}); }}, function(C, k, t, b){
var w = b.cw - 18; cont(C, b.cx - w / 2, b.cy - 40, w, 70, lc(k), 70); num(C, b.cx - 35, b.cy - 40, 70, 70, k, 30, '#FFFFFF');
C.ell(b.cx - w / 2 + 14, b.cy + 28, 22, 22, {fill:INK}); C.ell(b.cx + w / 2 - 36, b.cy + 28, 22, 22, {fill:INK});
});
}
return {p2:[['Container columns', pg(stacks)]], p3:[['Container columns', pg(stacks)]], p4:[['Container columns', pg(stacks)]], stats:[['Load numbers', pg(loads)]], timeline:[['Goods train', pg(train)]]};
}
});
})();
