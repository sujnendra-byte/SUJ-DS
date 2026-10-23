
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K5 = D.K5, pad2 = D.u.pad2;
/* Template 90 - Index Tabs : white sheet with coloured binder divider tabs along the right edge; the tab of the current section reaches into the page */
var INK = '#14213D', MU = '#5B6478', LN = '#DDE2EA', GR = '#E4E8F0', LC = ['#1B998B', '#F2A541', '#D7263D', '#2E86AB', '#14213D'];
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:'#1B998B', accent2:'#F2A541', card:'#F5F7FA', line:LN, onAccent:'#FFFFFF'};
function lc(k){ return LC[k % 5]; }
function tabs(C, edge, act){
C.bg({fill:GR});
for (var i = 0; i < 5; i++) if (i !== act) C.rect(edge - 20, 66 + i * 120, W - edge + 40, 100, {fill:LC[i], r:14});
C.rect(0, 0, edge, H, {fill:'#FFFFFF'}); C.rect(edge, 0, 1.5, H, {fill:'#C6CCD8'});
if (act >= 0) C.rect(edge - 26, 66 + act * 120, W - edge + 46, 100, {fill:LC[act], r:14});
for (var j = 0; j < 5; j++) C.text(edge + (j === act ? -16 : 2), 66 + j * 120, W - edge - 4 + (j === act ? 16 : 0), 100, pad2(j + 1), {font:'head', size:edge < 1200 ? 22 : 15, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.rect(96, 100, 64, 8, {fill:'#D7263D', r:4});
if (photo) { D.imgBox(C, 700, 96, 390, 528, {r:10}); C.rect(700, 96, 390, 10, {fill:LC[0]}); }
K5.coverText(C, 96, 142, photo ? 540 : 900, {max:photo ? 52 : 64, by:640});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.rect(96, 0, 210, 60, {fill:LC[0]}); C.text(96, 0, 210, 60, 'CONTACT', {font:'head', size:15, wt:800, color:'#FFFFFF', align:'c', va:'m', ls:0.14, lh:1});
if (photo) D.imgBox(C, 700, 110, 390, 500, {r:10});
K5.closeText(C, 96, 150, photo ? 560 : 880, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:lc(k), r:9}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'indextabs', name:'Index Tabs', tag:'White · binder divider tabs',
desc:'A clean white sheet with coloured file-divider tabs along the right edge; the tab for the current section reaches into the page. Folder cards, index rows and tabbed numbers. For board packs, policies, handbooks and annual reports.',
fonts:{head:'Archivo', body:'Archivo', headW:800, bodyW:400, headLh:1.06, gf:'Archivo:wght@400;500;600;700;800'},
P:P, LC:LC, coverLbl:'Divider tabs', closeLbl:'Divider tabs',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') tabs(C, 1150, -1); else tabs(C, 1236, C.i % 5); },
cover:cover, closing:closing,
card:function(C, x, y, w, h, k, o){
var c = lc(k);
if (o.big) { C.rect(x, y, w, h, {fill:'#F5F7FA', r:12}); C.rect(x, y, 10, h, {fill:LC[0], r:5}); return {tc:INK, mc:MU, ac:LC[0]}; }
C.rect(x, y, Math.min(150, w * 0.5), 32, {fill:c, r:9});
if (o.hot) { C.rect(x, y + 16, w, h - 16, {fill:INK, r:12}); return {tc:'#FFFFFF', mc:'#C3CAD9', ac:'#FFFFFF', hot:1}; }
C.rect(x, y + 16, w, h - 16, {fill:'#FFFFFF', r:12, line:LN, lw:1.5}); C.rect(x + 1.5, y + 17, w - 3, 6, {fill:c});
return {tc:INK, mc:MU, ac:c};
},
mark:function(C, x, y, s, t, k, cc){ var c = lc(k), hot = cc && cc.hot; C.rect(x, y, s, s, {fill:hot ? '#FFFFFF' : c, r:10}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? INK : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.rect(x, y, s, s, {fill:lc(k), r:10}); C.text(x, y, s, s, pad2(k + 1), {font:'head', size:s * 0.38, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:lc(k), r:12}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.36, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:GR, r:3}); },
photo:function(C, Z, side){
if (side === 'bottom') { D.imgBox(C, 72, Z.y + 8, 1136, Z.h - 26, {r:10}); return; }
var x = side === 'right' ? Z.x : 40, w = side === 'right' ? Z.w - 76 : Z.w - 40;
D.imgBox(C, x, 64, w, H - 150, {r:10}); C.rect(x, 64, w, 10, {fill:lc(C.i)});
},
L:{cards:'Folder cards', rows:'Tab rows', steps:'Tab steps', stmt:'Divider note', stats:'Number cards'},
sig:function(pg, B){
function tabRows(C, Z, o){
K5.list(C, Z, {aw:88, pr:28, gap:12, rhMax:104}, function(C, k, t, b){
var c = lc(k); C.rect(b.x, b.y, b.w, b.h, {fill:'#F5F7FA', r:12}); C.rect(b.x, b.y, 64, b.h, {fill:c, r:12}); C.rect(b.x + 50, b.y, 14, b.h, {fill:c});
C.text(b.x, b.y, 64, b.h, pad2(k + 1), {font:'head', size:Math.min(22, b.h * 0.3), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
});
}
function folders(C, Z, o){
K5.cols(C, Z, {max:4, ah:96, pad:24, tg:8, gap:20, fallback:B.rows}, function(C, k, t, b){
var c = lc(k), tw = Math.min(150, b.w * 0.42), tx = b.x + (b.w - tw) * (b.n > 1 ? k / (b.n - 1) : 0);
C.rect(tx, b.y, tw, 44, {fill:c, r:10}); C.text(tx, b.y, tw, 28, pad2(k + 1), {font:'head', size:14, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
C.rect(b.x, b.y + 28, b.w, b.h - 28, {fill:'#FFFFFF', r:12, line:LN, lw:1.5}); C.rect(b.x + 1.5, b.y + 29, b.w - 3, 6, {fill:c});
K.icon(C, b.x + 24, b.y + 52, 32, t, c);
});
}
function tabStats(C, Z, o){
K5.stat(C, Z, {ah:Math.min(180, Z.h * 0.46), tg:22, vMax:50, vw:function(cw){ return cw - 70; }}, function(C, k, t, b){
var c = lc(k); C.rect(b.x + 10, b.y, 110, 44, {fill:c, r:10}); C.rect(b.x + 10, b.y + 22, b.w - 20, b.ah - 22, {fill:c, r:14});
return {vc:'#FFFFFF', vy:b.y + 22 + (b.ah - 22 - b.vs * 1.15) / 2};
});
}
function tabLine(C, Z, o){
K5.alt(C, Z, {off:64, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 3, g.w, 6, {fill:GR, r:3}); }}, function(C, k, t, b){
var c = lc(k), y = b.up ? b.cy - 48 : b.cy + 4;
C.rect(b.cx - 36, y, 72, 44, {fill:c, r:10}); C.rect(b.cx - 36, b.cy - 10, 72, 20, {fill:c});
C.text(b.cx - 36, y, 72, 44, pad2(k + 1), {font:'head', size:17, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
});
}
return {agenda:[['Index rows', pg(tabRows)]], p2:[['Index folders', pg(folders)]], p3:[['Index folders', pg(folders)]], p4:[['Index folders', pg(folders)]],
p56:[['Index rows', pg(tabRows)]], p78:[['Index rows', pg(tabRows)]], stats:[['Tab numbers', pg(tabStats)]], timeline:[['Tab timeline', pg(tabLine)]]};
}
});
})();
