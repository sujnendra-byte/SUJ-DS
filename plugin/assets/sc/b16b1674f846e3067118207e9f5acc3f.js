
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 124 - Dash Code : rows of rounded dashes in different lengths, like signal code or a data stream */
var INK = '#0F172A', SK = '#0EA5E9', RS = '#F43F5E', GY = '#E2E8F0', MID = '#94A3B8', MU = '#56617A', PALE = '#F8FAFC';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:SK, accent2:RS, card:PALE, line:GY, onAccent:'#FFFFFF'};
var LC = [SK, INK, RS, MID];
function dashes(C, x, y, w, rows, seed, pitch, h, pal){
var R = K5.rnd(seed), d = [[], [], [], []], pick = [0, 0, 0, 1, 2, 0, 1, 3];
for (var j = 0; j < rows; j++) { var xc = x; while (xc < x + w - h * 2) { var L = Math.min(h * (1.2 + R() * 7), x + w - xc); Q.rrect(d[pick[Math.floor(R() * 8)]], xc, y + j * pitch, L, h, h / 2); xc += L + h; } }
pal = pal || [GY, SK, INK, RS]; for (var c = 0; c < 4; c++) K5.fill(C, d[c], pal[c]);
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 740, 90, 460, 350, {r:10}); dashes(C, 740, 474, 460, 7, 9, 20, 9); } else dashes(C, 720, 96, 480, 27, 7, 20, 9);
K5.coverText(C, 88, 130, 560, {max:46, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 780, 110, 420, 320, {r:10}); dashes(C, 780, 464, 420, 7, 13, 20, 9); } else dashes(C, 780, 130, 420, 23, 13, 20, 9);
K5.closeText(C, 88, 150, 620, {max:60, mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:[SK, INK, RS][k % 3], r:18}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'dashcode', name:'Dash Code', tag:'White · coded dashes',
desc:'Rows of rounded dashes in different lengths, like signal code or a data stream, mostly grey with sky blue, ink and rose, and monospaced headings. Clean and technical, for data, analytics, telecom, APIs and software updates.',
fonts:{head:'IBM Plex Mono', body:'IBM Plex Sans', headW:600, bodyW:400, headLh:1.14, gf:'IBM+Plex+Mono:wght@400;500;600;700|IBM+Plex+Sans:wght@400;500;600;700'},
P:P, LC:LC, cardR:10, imgR:10, tMax:34, coverLbl:'Data stream', closeLbl:'Data stream',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; dashes(C, W - 236, 26, 204, 3, 3 + (C.i % 7), 13, 6); },
cover:cover, closing:closing,
rule:function(C, x, y){ dashes(C, x, y, 130, 1, 4, 10, 6, [SK, SK, INK, RS]); return 20; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? INK : PALE, r:10}); if (!o.big) dashes(C, x + 24, y + h - 24, w - 48, 1, 5 + k, 10, 6, hot ? ['#334155', SK, '#FFFFFF', RS] : null);
return hot ? {tc:'#FFFFFF', mc:'#C3CBD9', ac:SK, hot:1} : {tc:INK, mc:MU, ac:SK};
},
mark:function(C, x, y, s, t, k, cc){ C.rect(x, y, s, s, {fill:[SK, INK, RS][k % 3], r:s / 2}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.rect(x - 4, y + s * 0.18, s + 8, s * 0.64, {fill:[SK, INK, RS][k % 3], r:s * 0.32}); num(C, x - 4, y, s + 8, s, k, s * 0.34, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.rect(cx - s * 0.7, cy - s * 0.32, s * 1.4, s * 0.64, {fill:[SK, INK, RS][k % 3], r:s * 0.32}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.32, '#FFFFFF'); },
track:function(C, x1, y, x2){ dashes(C, x1, y - 4, x2 - x1, 1, 21, 10, 8, [GY, GY, MID, GY]); },
conc:{kind:'pill', fill:SK, op:0.12, label:'Output', lc:'#0369A1', tc:INK},
L:{cards:'Block cards', rows:'Log rows', steps:'Pill steps', stmt:'Readme', stats:'Plain numbers'},
sig:function(pg, B){
function streams(C, Z, o){
K5.cols(C, Z, {max:5, ah:142, gap:28, tg:16, fallback:B.rows}, function(C, k, t, b){
dashes(C, b.x, b.y + 58, b.w, 5, 30 + k * 7, 15, 7); C.rect(b.x, b.y, 50, 44, {fill:[SK, INK, RS][k % 3], r:22}); K.icon(C, b.x + 13, b.y + 10, 24, t, '#FFFFFF');
C.text(b.x + 62, b.y, 90, 44, pad2(k + 1), {font:'head', size:30, wt:600, color:INK, va:'m', lh:1});
});
}
function signals(C, Z, o){
K5.stat(C, Z, {ah:Math.min(190, Z.h * 0.48), tg:20, vMax:50, align:'l', pad:36}, function(C, k, t, b){
var m = /^\s*([\d.]+)\s*%/.exec(b.val), pc = m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : 56 + (k * 13) % 36, w = b.w - 36, on = [], off = [], lens = [1, 0.82, 0.64, 0.46];
for (var j = 0; j < 4; j++) { var L = w * lens[j] * pc / 100; Q.rrect(off, b.x + 18, b.y + b.ah - 70 + j * 17, w, 9, 4.5); Q.rrect(on, b.x + 18, b.y + b.ah - 70 + j * 17, Math.max(12, L), 9, 4.5); }
K5.fill(C, off, GY); K5.fill(C, on, [SK, INK, RS, SK][k]);
return {vc:INK, vy:b.y + 4, va:'l'};
});
}
function pulse(C, Z, o){
K5.alt(C, Z, {off:54, fallback:B.rows, pre:function(C, g){ dashes(C, g.x, g.y - 5, g.w, 1, 44, 10, 10, [GY, GY, MID, GY]); }}, function(C, k, t, b){
C.rect(b.cx - 48, b.cy - 22, 96, 44, {fill:[SK, INK, RS][k % 3], r:22, line:'#FFFFFF', lw:4}); num(C, b.cx - 48, b.cy - 22, 96, 44, k, 18, '#FFFFFF');
});
}
function log(C, Z, o){
K5.list(C, Z, {aw:138, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
C.text(b.x, b.y, 50, b.h, pad2(k + 1), {font:'head', size:Math.min(24, b.h * 0.3), wt:600, color:[SK, INK, RS][k % 3], va:'m', lh:1}); dashes(C, b.x + 52, b.cy - 14, 70, 3, 50 + k * 3, 11, 5); C.rect(b.x, b.y + b.h + 3, b.w, 1.5, {fill:GY});
});
}
return {agenda:[['Log lines', pg(log)]], p2:[['Stream columns', pg(streams)]], p3:[['Stream columns', pg(streams)]], p4:[['Stream columns', pg(streams)]], p56:[['Log lines', pg(log)]], p78:[['Log lines', pg(log)]],
stats:[['Signal bars', pg(signals)]], timeline:[['Pulse line', pg(pulse)]]};
}
});
})();
