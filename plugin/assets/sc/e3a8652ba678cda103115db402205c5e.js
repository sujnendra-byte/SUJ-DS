
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 105 - Venn : big translucent discs overlap and mix where they cross */
var IN = '#14143C', BL = '#4361EE', PK = '#F72585', CY = '#4CC9F0', VI = '#7209B7', MU = '#5A5C7E', LN = '#DEE1F3', PALE = '#F5F6FD';
var P = {bg:'#FFFFFF', text:IN, title:IN, muted:MU, accent:BL, accent2:PK, card:PALE, line:LN, onAccent:'#FFFFFF'};
var LC = [BL, PK, CY, VI];
function lc(k){ return LC[k % 4]; }
function disc(C, cx, cy, r, col, op){ C.ell(cx - r, cy - r, r * 2, r * 2, {fill:col, op:op == null ? 0.82 : op}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
if (photo) D.imgBox(C, 760, 40, 420, 420, {shape:'ell'}); else disc(C, 970, 250, 210, BL);
disc(C, 1120, 440, 210, PK); disc(C, 880, 480, 210, CY);
K5.coverText(C, 88, 130, 580, {max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
if (photo) D.imgBox(C, 800, 60, 360, 360, {shape:'ell'}); else disc(C, 1000, 220, 190, VI);
disc(C, 1140, 420, 190, BL); disc(C, 930, 500, 190, PK);
K5.closeText(C, 88, 150, 600, {mark:function(C, x, y, k, c){ disc(C, x + 28, y + 18, 16, lc(k + 1), 0.6); disc(C, x + 18, y + 18, 18, lc(k), 1); C.icon(x + 10, y + 10, 16, c.icon, '#FFFFFF'); }});
}
K5.make({
id:'venn', name:'Venn', tag:'White · overlapping colour discs',
desc:'Big translucent discs in blue, pink and cyan overlap and mix where they cross, so the shared ground between ideas is visible: disc chains, overlap numbers and linked rings. For strategy fit, positioning, alliances and joint ventures.',
fonts:{head:'Lexend', body:'Lexend', headW:700, bodyW:300, headLh:1.1, gf:'Lexend:wght@300;400;500;600;700;800'},
P:P, LC:LC, cardR:18, imgR:18, coverLbl:'Three discs', closeLbl:'Three discs',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; disc(C, W - 92, 34, 44, BL); disc(C, W - 38, 62, 44, PK); },
cover:cover, closing:closing,
rule:function(C, x, y){ disc(C, x + 9, y + 6, 9, BL); disc(C, x + 21, y + 6, 9, PK); disc(C, x + 33, y + 6, 9, CY); return 24; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot; C.rect(x, y, w, h, {fill:hot ? BL : PALE, r:18});
if (!o.big) { disc(C, x + w - 44, y + 28, 15, hot ? '#FFFFFF' : lc(k), 0.8); disc(C, x + w - 26, y + 28, 15, hot ? CY : lc(k + 1), 0.8); }
return hot ? {tc:'#FFFFFF', mc:'#D9E0FF', ac:'#FFFFFF', hot:1} : {tc:IN, mc:MU, ac:BL};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot; disc(C, x + s * 0.74, y + s * 0.62, s * 0.36, hot ? CY : lc(k + 1), 0.7); C.ell(x, y, s, s, {fill:hot ? '#FFFFFF' : lc(k)}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? BL : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ disc(C, x + s * 0.74, y + s * 0.62, s * 0.36, lc(k + 1), 0.7); C.ell(x, y, s, s, {fill:lc(k)}); num(C, x, y, s, s, k, s * 0.38, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ disc(C, cx + s * 0.3, cy, s * 0.5, lc(k + 1), 0.55); C.ell(cx - s / 2, cy - s / 2, s, s, {fill:lc(k)}); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, '#FFFFFF'); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:LN, r:3}); },
L:{cards:'Overlap cards', rows:'Pair rows', steps:'Disc steps', stmt:'Common ground', stats:'Plain numbers'},
sig:function(pg, B){
function chain(C, Z, o){
var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var cw = Z.w / n, r = Math.min(cw * 0.6, Z.h * 0.3, 132), cy = Z.y + r + 2, tw = cw - 28;
var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), r * 1.3, 58, {font:'head', max:22, min:12, maxLines:2});
var ds = C.groupFit(it.map(function(t){ return t.d || ''; }), tw, Z.h - r * 2 - 30, {font:'body', max:17, min:10, lh:1.4});
it.forEach(function(t, k){ disc(C, Z.x + cw * (k + 0.5), cy, r, lc(k)); });
it.forEach(function(t, k){
var cx = Z.x + cw * (k + 0.5);
K.icon(C, cx - 17, cy - r * 0.56, 34, t, '#FFFFFF');
if (t.h) C.text(cx - r * 0.66, cy - 6, r * 1.32, 60, t.h, {font:'head', size:hs, color:'#FFFFFF', align:'c', lh:1.14, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
C.text(cx - tw / 2, cy + r + 22, tw, Z.y + Z.h - (cy + r + 22), t.d, {font:'body', size:ds, color:MU, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
});
}
function overlap(C, Z, o){
K5.stat(C, Z, {ah:Math.min(220, Z.h * 0.54), tg:18, vMax:42, vw:function(cw, ah){ return Math.min(cw * 0.5, ah * 0.74); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.34, b.ah * 0.46), cy = b.y + b.ah / 2;
disc(C, b.cx + r * 0.5, cy + r * 0.12, r * 0.86, lc(k + 1), 0.7); disc(C, b.cx - r * 0.24, cy, r, lc(k), 0.92);
return {vc:'#FFFFFF', vx:b.cx - r * 1.14, vw:r * 1.8, vy:cy - b.vs * 0.58};
});
}
function rings(C, Z, o){
K5.alt(C, Z, {same:true, at:0.2, off:66, fallback:B.rows}, function(C, k, t, b){
C.ell(b.cx - b.cw * 0.56, b.cy - 46, b.cw * 1.12, 92, {line:lc(k), lw:6}); num(C, b.cx - 40, b.cy - 24, 80, 48, k, 26, lc(k));
});
}
function pairs(C, Z, o){
K5.list(C, Z, {aw:104, pr:16, gap:8, rhMax:98}, function(C, k, t, b){
var r = Math.min(27, b.h * 0.34); disc(C, b.x + r * 2.1, b.cy, r, lc(k + 1), 0.7); disc(C, b.x + r, b.cy, r, lc(k), 0.95); num(C, b.x, b.cy - r, r * 2, r * 2, k, r * 0.68, '#FFFFFF'); C.rect(b.x, b.y + b.h + 3, b.w, 1.5, {fill:LN});
});
}
return {agenda:[['Pair list', pg(pairs)]], p2:[['Disc chain', pg(chain)]], p3:[['Disc chain', pg(chain)]], p4:[['Disc chain', pg(chain)]], p56:[['Pair list', pg(pairs)]], p78:[['Pair list', pg(pairs)]],
stats:[['Overlap numbers', pg(overlap)]], timeline:[['Linked rings', pg(rings)]]};
}
});
})();
