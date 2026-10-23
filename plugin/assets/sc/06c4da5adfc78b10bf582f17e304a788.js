
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 100 - Masonry : a wall of offset bricks; content pages are laid in the same bond */
var INK = '#101828', RD = '#B42318', MU = '#5D6675', LN = '#D0D5DD', PALE = '#F6F7F9';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:RD, accent2:INK, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [RD, INK, '#667085', '#E4703F'];
function brickwork(C, x, y, w, h, bw, bh, g, seed, dens){
var R = K5.rnd(seed), o = [], f1 = [], f2 = [], row = 0;
for (var yy = y; yy < y + h - 4; yy += bh + g, row++) {
for (var xx = x - (row % 2 ? (bw + g) / 2 : 0); xx < x + w; xx += bw + g) {
var a = Math.max(x, xx), b = Math.min(x + w, xx + bw), hh = Math.min(bh, y + h - yy); if (b - a < 8) continue;
var q = R(); if (q < dens) Q.rrect(f1, a, yy, b - a, hh, 4); else if (q < dens * 1.8) Q.rrect(f2, a, yy, b - a, hh, 4); else Q.rrect(o, a, yy, b - a, hh, 4);
}
}
K5.stroke(C, o, LN, 2); K5.fill(C, f1, RD); K5.fill(C, f2, INK);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
brickwork(C, 700, 14, 580, 692, 150, 62, 12, 4, 0.1);
if (photo) { C.rect(770, 150, 440, 420, {fill:'#FFFFFF'}); D.imgBox(C, 782, 162, 416, 396, {r:4}); }
K5.coverText(C, 88, 130, 560, {max:56, by:644, rule:function(C, x, y){ C.rect(x, y, 70, 14, {fill:RD, r:3}); C.rect(x + 78, y, 34, 14, {fill:INK, r:3}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
brickwork(C, 14, 520, W - 28, 190, 150, 54, 12, 9, 0.12);
if (photo) D.imgBox(C, 800, 80, 392, 400, {r:4});
K5.closeText(C, 88, 90, 660, {mark:function(C, x, y, k, c){ C.rect(x, y + 4, 40, 28, {fill:k % 2 ? INK : RD, r:3}); C.icon(x + 12, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'masonry', name:'Masonry', tag:'White · brick bond',
desc:'A wall of offset bricks: most are outlines and a few are filled brick red or ink, and content pages are laid in the same running bond with half-brick callouts. For construction, infrastructure, property and laying-the-foundation plans.',
fonts:{head:'Public Sans', body:'Public Sans', headW:800, bodyW:400, headLh:1.08, gf:'Public+Sans:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:4, imgR:4, coverLbl:'Brick wall', closeLbl:'Brick wall',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; brickwork(C, W - 214, 16, 200, 58, 62, 24, 8, 2 + (C.i % 4), 0.2); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 44, 10, {fill:RD, r:2}); C.rect(x + 50, y, 22, 10, {fill:INK, r:2}); return 20; },
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {fill:RD, r:4}); return {tc:'#FFFFFF', mc:'#F6D2CD', ac:'#FFFFFF', hot:1}; }
C.rect(x, y, w, h, {fill:o.big ? PALE : '#FFFFFF', r:4, line:LN, lw:2}); return {tc:INK, mc:MU, ac:RD};
},
row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {r:4, line:LN, lw:2}); return {tc:INK, mc:MU, ac:RD}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.rect(x, y + s * 0.12, s, s * 0.76, {fill:hot ? '#FFFFFF' : (k % 2 ? INK : RD), r:4}); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, hot ? RD : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.rect(x, y + s * 0.14, s, s * 0.72, {fill:k % 2 ? INK : RD, r:4}); num(C, x, y, s, s, k, s * 0.36, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.rect(cx - s * 0.7, cy - s * 0.36, s * 1.4, s * 0.72, {fill:k % 2 ? INK : RD, r:4}); num(C, cx - s * 0.7, cy - s * 0.36, s * 1.4, s * 0.72, k, s * 0.34, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:LN}); },
conc:{kind:'box', fill:PALE, r:4, label:'Foundation', lc:RD, tc:INK},
L:{cards:'Brick cards', rows:'Course rows', steps:'Brick steps', stmt:'Cornerstone note', stats:'Number cards'},
sig:function(pg, B){
function bond(C, Z, o){
var it = C.it.slice(0, 7), n = it.length; if (!n || Z.w < 700) return B.rows(C, Z, o);
var per = Math.min(3, Math.max(2, n - 1)), g = 12, bw = (Z.w - g * (per - 1)) / per, half = (bw - g) / 2, slots = [], rows = 0;
while (slots.length < n) { var odd = rows % 2, cnt = odd ? per - 1 : per; for (var i = 0; i < cnt; i++) slots.push({r:rows, x:Z.x + (odd ? half + g : 0) + i * (bw + g)}); rows++; }
if (rows === 1) rows = 2;
var bh = Math.min((Z.h - g * (rows - 1)) / rows, 210), y0 = Z.y + (Z.h - (bh * rows + g * (rows - 1))) / 2, tw = bw - 110;
var F = K.fitHD(C, it, tw, 44, bh - 40, {hMax:22, dMax:16, hMin:12, dMin:10, noBoost:n > 3});
for (var r = 0; r < rows; r++) if (r % 2) { var y = y0 + r * (bh + g); C.rect(Z.x, y, half, bh, {fill:RD, r:4}); C.rect(Z.x + Z.w - half, y, half, bh, {fill:INK, r:4}); }
slots.forEach(function(s, k){
var y = y0 + s.r * (bh + g), t = it[k];
if (!t) { C.rect(s.x, y, bw, bh, {fill:PALE, r:4, line:LN, lw:2}); return; }
C.rect(s.x, y, bw, bh, {fill:'#FFFFFF', r:4, line:LN, lw:2});
C.rect(s.x + 20, y + 22, 52, 36, {fill:k % 2 ? INK : RD, r:4}); K.icon(C, s.x + 35, y + 29, 22, t, '#FFFFFF');
var th = K3.bH(C, t, tw, F); K.hd(C, s.x + 90, y + Math.max(18, (bh - th) / 2), tw, bh - 30, t, F, {tc:INK, mc:MU});
});
if (slots.length === n && rows === 2 && n <= 2) { var y2 = y0 + bh + g; for (var j = 0; j < per - 1; j++) C.rect(Z.x + half + g + j * (bw + g), y2, bw, bh, {fill:PALE, r:4, line:LN, lw:2}); }
}
function bricks(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:20, vMax:50, vw:function(cw){ return cw - 70; }}, function(C, k, t, b){
var h1 = b.ah * 0.62, hw = (b.w - 28) / 2;
C.rect(b.x + 8, b.y, b.w - 16, h1, {fill:k % 2 ? INK : RD, r:4});
C.rect(b.x + 8 - 0, b.y + h1 + 12, hw * 0.5, b.ah - h1 - 12, {r:4, line:LN, lw:2}); C.rect(b.x + 20 + hw * 0.5, b.y + h1 + 12, hw, b.ah - h1 - 12, {r:4, line:LN, lw:2}); C.rect(b.x + 32 + hw * 1.5, b.y + h1 + 12, b.w - 40 - hw * 1.5, b.ah - h1 - 12, {r:4, line:LN, lw:2});
return {vc:'#FFFFFF', vy:b.y + (h1 - b.vs * 1.15) / 2};
});
}
function course(C, Z, o){
K5.alt(C, Z, {off:52, fallback:B.rows, pre:function(C, g){ brickwork(C, g.x, g.y - 18, g.w, 36, 96, 36, 10, 77, 0); }}, function(C, k, t, b){
C.rect(b.cx - 46, b.cy - 24, 92, 48, {fill:k % 2 ? INK : RD, r:4}); num(C, b.cx - 46, b.cy - 24, 92, 48, k, 19, '#FFFFFF');
});
}
return {agenda:[['Brick bond', pg(bond)]], p2:[['Brick bond', pg(bond)]], p3:[['Brick bond', pg(bond)]], p4:[['Brick bond', pg(bond)]], p56:[['Brick bond', pg(bond)]], p78:[['Brick bond', pg(bond)]],
stats:[['Number bricks', pg(bricks)]], timeline:[['Course by course', pg(course)]]};
}
});
})();
