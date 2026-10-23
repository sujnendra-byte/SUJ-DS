
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 121 - Abacus : wooden and navy beads slide along thin rods; progress and scores are counted in beads */
var NV = '#1E3A8A', WD = '#B45309', INK = '#1E2A4A', RODC = '#94A3B8', MU = '#5C6785', LN = '#D9DFEA', PALE = '#F8FAFC';
var P = {bg:'#FFFFFF', text:INK, title:INK, muted:MU, accent:NV, accent2:WD, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [NV, WD, '#64748B', '#D97706'];
function beads(C, x, y, n, rx, ry, col, dir){ var d = []; for (var i = 0; i < n; i++) Q.ell(d, x + (dir || 1) * i * rx * 2.08, y, rx, ry); K5.fill(C, d, col); }
function abacus(C, x, y, w, h, rows, seed, rx){
var R = K5.rnd(seed), ry = rx * 1.22, st = h / rows, rd = [], i, cap = Math.floor((w - 60) / (rx * 2.08)) - 2;
for (i = 0; i < rows; i++) Q.line(rd, x + 10, y + st * (i + 0.5), x + w - 10, y + st * (i + 0.5));
K5.stroke(C, rd, RODC, 3);
for (i = 0; i < rows; i++) { var a = 1 + Math.floor(R() * (cap - 1)), b = cap - a; beads(C, x + 26 + rx, y + st * (i + 0.5), a, rx, ry, WD); beads(C, x + w - 26 - rx, y + st * (i + 0.5), b, rx, ry, NV, -1); }
C.rect(x, y, w, h, {line:INK, lw:12, r:10});
}
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) { D.imgBox(C, 720, 120, 470, 230, {r:10}); abacus(C, 726, 380, 458, 216, 3, 5, 20); } else abacus(C, 726, 136, 458, 448, 6, 5, 20);
K5.coverText(C, 88, 130, 560, {max:54, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
if (photo) D.imgBox(C, 780, 110, 400, 250, {r:10});
abacus(C, 786, photo ? 400 : 190, 388, photo ? 190 : 340, photo ? 3 : 5, 9, 18);
K5.closeText(C, 88, 150, 620, {mark:function(C, x, y, k, c){ C.ell(x + 2, y - 2, 32, 40, {fill:k % 2 ? WD : NV}); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'abacus', name:'Abacus', tag:'White · beads on rods',
desc:'A counting frame: wooden and navy beads slide along thin rods inside a dark frame, and progress, scores and totals are shown as counted beads. Clear and numerate, for finance, accounting, education, audits and scorecards.',
fonts:{head:'Epilogue', body:'Epilogue', headW:700, bodyW:400, headLh:1.1, gf:'Epilogue:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:12, imgR:10, coverLbl:'Counting frame', closeLbl:'Counting frame',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.line(W - 200, 44, W + 10, 44, {color:RODC, lw:2.5}); beads(C, W - 182, 44, 2 + C.i % 3, 10, 13, WD); beads(C, W - 22, 44, 3 - C.i % 3 + 1, 10, 13, NV, -1); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.line(x, y + 5, x + 100, y + 5, {color:RODC, lw:2}); beads(C, x + 8, y + 5, 3, 6, 8, NV); beads(C, x + 92, y + 5, 2, 6, 8, WD, -1); return 26; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? NV : PALE, r:12});
if (!o.big) { C.line(x + w - 120, y + 26, x + w - 16, y + 26, {color:hot ? '#8FA3D8' : RODC, lw:2}); beads(C, x + w - 30, y + 26, 2 + k % 3, 8, 10, hot ? '#FFFFFF' : (k % 2 ? WD : NV), -1); }
return hot ? {tc:'#FFFFFF', mc:'#C7D2F0', ac:'#FFFFFF', hot:1} : {tc:INK, mc:MU, ac:NV};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; C.ell(x + s * 0.06, y - s * 0.04, s * 0.88, s * 1.08, {fill:hot ? WD : (k % 2 ? WD : NV)}); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ C.line(x - 8, y + s / 2, x + s + 8, y + s / 2, {color:RODC, lw:2.5}); C.ell(x + s * 0.08, y - s * 0.04, s * 0.84, s * 1.08, {fill:k % 2 ? WD : NV}); num(C, x, y, s, s, k, s * 0.36, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s * 0.44, cy - s * 0.56, s * 0.88, s * 1.12, {fill:k % 2 ? WD : NV}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.line(x1 - 20, y, x2 + 20, y, {color:RODC, lw:4}); },
L:{cards:'Frame cards', rows:'Rod rows', steps:'Bead steps', stmt:'Ledger note', stats:'Plain numbers'},
sig:function(pg, B){
function tally(C, Z, o){
var st = C.it.slice(0, 4), n = st.length; if (!n || Z.w < 900) return B.stats(C, Z, o);
var rh = Math.min(Z.h / n, 120), y0 = Z.y + (Z.h - rh * n) / 2, vals = st.map(K3.sv), labs = st.map(K3.sl), vw = 190, lw = 290, rx0 = Z.x + vw + lw + 50, rw = Z.x + Z.w - rx0, br = Math.min(17, rw / 22, rh * 0.2);
var vs = C.groupFit(vals, vw, rh * 0.6, {font:'head', max:50, min:20, maxLines:1}), ls = C.groupFit(labs, lw, 46, {font:'head', max:19, min:12, maxLines:2}), ds = C.groupFit(st.map(function(t){ return t.x || ''; }), lw, rh - 54, {font:'body', max:14, min:10, lh:1.34});
st.forEach(function(t, k){
var y = y0 + k * rh, cy = y + rh / 2, m = /^\s*([\d.]+)\s*%/.exec(vals[k]), cnt = m ? Math.max(1, Math.min(10, Math.round(parseFloat(m[1]) / 10))) : [7, 5, 8, 6][k];
if (k) C.rect(Z.x, y, Z.w, 1.5, {fill:LN});
C.text(Z.x, y, vw, rh, vals[k], {font:'head', size:vs, wt:800, color:k % 2 ? WD : NV, va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
var le = C.text(Z.x + vw + 16, y + (t.x ? rh * 0.16 : 0), lw, t.x ? 46 : rh, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.15, va:t.x ? 't' : 'm', ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
if (t.x) C.text(Z.x + vw + 16, y + rh * 0.16 + le._h + 6, lw, rh * 0.8 - le._h, t.x, {font:'body', size:ds, color:MU, lh:1.34, ed:{f:'x-part', i:t.i, p:2}});
C.line(rx0, cy, rx0 + rw, cy, {color:RODC, lw:3}); beads(C, rx0 + br + 6, cy, cnt, br, br * 1.26, k % 2 ? WD : NV); beads(C, rx0 + rw - br - 6, cy, 10 - cnt, br, br * 1.26, '#CBD5E1', -1);
});
}
function count(C, Z, o){
K5.cols(C, Z, {max:5, ah:160, gap:26, tg:14, fallback:B.rows}, function(C, k, t, b){
C.line(b.x + 30, b.y, b.x + 30, b.y + 150, {color:RODC, lw:3}); var d = [], e = [], c = Math.min(5, k + 1);
for (var i = 0; i < 5; i++) Q.ell(i < c ? d : e, b.x + 30, b.y + 138 - i * 27, 24, 12.5);
K5.fill(C, e, '#E2E8F0'); K5.fill(C, d, k % 2 ? WD : NV);
K.icon(C, b.x + 78, b.y + 8, 40, t, NV); C.text(b.x + 78, b.y + 84, 110, 64, pad2(k + 1), {font:'head', size:48, wt:800, color:k % 2 ? WD : NV, lh:1, va:'b'});
});
}
function rod(C, Z, o){
K5.alt(C, Z, {off:66, fallback:B.rows, pre:function(C, g){ C.line(g.x - 10, g.y, g.x + g.w + 10, g.y, {color:RODC, lw:4}); }}, function(C, k, t, b){
beads(C, b.cx - 54, b.cy, 1, 12, 15, '#CBD5E1'); beads(C, b.cx + 54, b.cy, 1, 12, 15, '#CBD5E1'); C.ell(b.cx - 32, b.cy - 40, 64, 80, {fill:k % 2 ? WD : NV}); num(C, b.cx - 32, b.cy - 40, 64, 80, k, 22, '#FFFFFF');
});
}
return {p2:[['Counting rods', pg(count)]], p3:[['Counting rods', pg(count)]], p4:[['Counting rods', pg(count)]], stats:[['Bead tally', pg(tally)]], timeline:[['Bead rod', pg(rod)]]};
}
});
})();
