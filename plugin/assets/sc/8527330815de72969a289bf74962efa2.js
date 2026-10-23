
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 107 - Guilloche : fine looping rosette lines like a share certificate or banknote, inside a double ruled border */
var GN = '#14532D', GD = '#B08D2F', IV = '#F4F7F2', MU = '#55705F', LN = '#D3DDCF';
var P = {bg:IV, text:GN, title:GN, muted:MU, accent:GN, accent2:GD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [GN, GD, '#2F7A4F', '#7A5F16'];
function ros(C, cx, cy, R, k, m, col, lw, op){
var d = [];
for (var j = 0; j < m; j++) { var pts = []; for (var i = 0; i <= 96; i++) { var a = i / 96 * Math.PI * 2, rr = R * (0.72 + 0.28 * Math.cos(k * a + j * Math.PI * 2 / m)); pts.push([cx + rr * Math.cos(a), cy + rr * Math.sin(a)]); } Q.pts(d, pts); }
K5.stroke(C, d, col, lw, op);
}
function frame(C, a, b){ C.rect(a, a, W - 2 * a, H - 2 * a, {line:GN, lw:2}); C.rect(b, b, W - 2 * b, H - 2 * b, {line:GD, lw:1}); }
function seal(C, cx, cy, r, k, f){ ros(C, cx, cy, r, 6, 3, GN, 1.2); C.ell(cx - r * 0.56, cy - r * 0.56, r * 1.12, r * 1.12, {fill:f || IV, line:GD, lw:1.2}); if (k != null) C.text(cx - r, cy - r, r * 2, r * 2, pad2(k + 1), {font:'head', size:r * 0.52, wt:700, color:GN, align:'c', va:'m', lh:1}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
frame(C, 22, 31); ros(C, 1000, 360, 250, 8, 7, GN, 1, 0.85); ros(C, 1000, 360, photo ? 150 : 108, 5, 5, GD, 1.3);
if (photo) { C.ell(884, 244, 232, 232, {fill:IV, line:GD, lw:2}); D.imgBox(C, 894, 254, 212, 212, {shape:'ell'}); }
K5.coverText(C, 92, 150, 600, {max:54, by:636, rule:function(C, x, y){ C.rect(x, y, 100, 2, {fill:GN}); C.rect(x, y + 5, 100, 1, {fill:GD}); }});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
frame(C, 22, 31); ros(C, 1000, 360, 230, 10, 6, GN, 1, 0.85); ros(C, 1000, 360, photo ? 150 : 96, 6, 4, GD, 1.3);
if (photo) { C.ell(884, 244, 232, 232, {fill:IV, line:GD, lw:2}); D.imgBox(C, 894, 254, 212, 212, {shape:'ell'}); }
K5.closeText(C, 92, 150, 600, {mark:function(C, x, y, k, c){ C.ell(x, y, 36, 36, {line:GD, lw:1.5, fill:'#FFFFFF'}); C.icon(x + 10, y + 10, 16, c.icon, GN); }});
}
K5.make({
id:'guilloche', name:'Guilloche', tag:'Pale green · banknote rosettes',
desc:'Fine looping rosette lines like a share certificate or banknote, inside a double ruled border, with rosette seals, ledger rows and serif headings in bank green and gold. For banking, treasury, audit, insurance and shareholder reports.',
fonts:{head:'Spectral', body:'IBM Plex Sans', headW:700, bodyW:400, headLh:1.12, gf:'Spectral:wght@500;600;700|IBM+Plex+Sans:wght@400;500;600;700'},
P:P, LC:LC, M:84, cardR:0, headTop:64, coverLbl:'Rosette', closeLbl:'Rosette',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; frame(C, 18, 25); ros(C, 1172, 104, 56, 6, 4, GD, 1, 0.9); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 80, 2, {fill:GN}); C.rect(x, y + 5, 80, 1, {fill:GD}); return 20; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? GN : '#FFFFFF', line:GN, lw:1.5}); C.rect(x + 6, y + 6, w - 12, h - 12, {line:GD, lw:1});
return hot ? {tc:'#FFFFFF', mc:'#C6DBCC', ac:GD, hot:1} : {tc:GN, mc:MU, ac:GN};
},
pad:28,
row:function(C, x, y, w, h, k){ C.line(x, y + h - 1, x + w, y + h - 1, {color:GN, lw:1, dash:true}); return {tc:GN, mc:MU, ac:GN}; },
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? GD : IV, line:GD, lw:1.5}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, GN); },
rowMark:function(C, x, y, s, t, k){ seal(C, x + s / 2, y + s / 2, s * 0.6, k); },
node:function(C, cx, cy, s, k){ seal(C, cx, cy, s * 0.62, k); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 2, {fill:GN}); C.rect(x1, y + 2, x2 - x1, 1, {fill:GD}); },
conc:{kind:'line', lineC:GN, label:'Resolved', lc:GD, tc:GN},
L:{cards:'Certificate cards', rows:'Ledger rows', steps:'Seal steps', stmt:'Charter note', stats:'Plain figures'},
sig:function(pg, B){
function certs(C, Z, o){
K5.cols(C, Z, {max:5, ah:150, gap:20, pad:22, tg:6, fallback:B.rows}, function(C, k, t, b){
C.rect(b.x, b.y, b.w, b.h, {fill:'#FFFFFF', line:GN, lw:1.5}); C.rect(b.x + 6, b.y + 6, b.w - 12, b.h - 12, {line:GD, lw:1});
seal(C, b.x + 72, b.y + 82, 50, k, '#FFFFFF'); K.icon(C, b.x + b.w - 62, b.y + 28, 30, t, GD);
});
}
function notes(C, Z, o){
K5.stat(C, Z, {ah:Math.min(200, Z.h * 0.5), tg:20, vMax:48, vw:function(cw){ return cw - 80; }}, function(C, k, t, b){
C.rect(b.x + 8, b.y, b.w - 16, b.ah, {fill:'#FFFFFF', line:GN, lw:1.5}); C.rect(b.x + 14, b.y + 6, b.w - 28, b.ah - 12, {line:GD, lw:1});
ros(C, b.cx, b.y + b.ah / 2, Math.min(b.ah * 0.42, b.w * 0.36), 7, 4, GD, 1, 0.5);
return {vc:GN, vy:b.y + (b.ah - b.vs * 1.15) / 2};
});
}
function seals(C, Z, o){
K5.alt(C, Z, {off:64, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 3, g.w, 2, {fill:GN}); C.rect(g.x, g.y + 2, g.w, 1, {fill:GD}); }}, function(C, k, t, b){ seal(C, b.cx, b.cy, 44, k); });
}
function ledger(C, Z, o){
K5.list(C, Z, {aw:92, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
seal(C, b.x + 36, b.cy, Math.min(36, b.h * 0.42), k); C.line(b.x, b.y + b.h + 3, b.x + b.w, b.y + b.h + 3, {color:GN, lw:1, dash:true});
});
}
return {agenda:[['Sealed ledger', pg(ledger)]], p2:[['Certificates', pg(certs)]], p3:[['Certificates', pg(certs)]], p4:[['Certificates', pg(certs)]], p56:[['Sealed ledger', pg(ledger)]], p78:[['Sealed ledger', pg(ledger)]],
stats:[['Banknote figures', pg(notes)]], timeline:[['Seal line', pg(seals)]]};
}
});
})();
