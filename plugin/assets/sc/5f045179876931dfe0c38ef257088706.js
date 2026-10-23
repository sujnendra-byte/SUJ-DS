
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 97 - Jigsaw : interlocking puzzle pieces; each point is a piece that locks into the next */
var BL = '#2563EB', CY = '#06B6D4', NV = '#0F172A', YL = '#FACC15', MU = '#56607A', LN = '#D9E0EA', PALE = '#EEF2F7';
var P = {bg:'#FFFFFF', text:NV, title:NV, muted:MU, accent:BL, accent2:CY, card:'#F1F5F9', line:LN, onAccent:'#FFFFFF'};
var LC = [BL, CY, NV, YL];
function lc(k){ return LC[k % 4]; }
function knob(C, cx, cy, r, c){ C.ell(cx - r, cy - r, r * 2, r * 2, {fill:c}); }
function cover(C){
var photo = (C.o || {}).img === 'photo', s = 190, x = 740, y = 100, r = 32;
C.rect(x, y, s, s, {fill:BL}); C.rect(x + s, y, s, s, {fill:CY}); C.rect(x, y + s, s, s, {fill:NV}); C.rect(x + s, y + s, s, s, {fill:PALE});
if (photo) D.imgBox(C, x + s, y, s, s, {r:0});
knob(C, x + s, y + s / 2, r, BL); knob(C, x + s / 2, y + s, r, NV); knob(C, x + s * 1.5, y + s, r, photo ? PALE : CY); knob(C, x + s, y + s * 1.5, r, NV);
C.rect(x + s + 62, y + s + 58, s, s, {fill:YL, shadow:0.22}); knob(C, x + s * 2 + 62, y + s * 1.5 + 58, r, YL); knob(C, x + s * 1.5 + 62, y + s * 2 + 58, r, YL);
K5.coverText(C, 88, 130, 580, {max:56, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo', s = 170, x = 720, y = 420, r = 28;
if (photo) D.imgBox(C, 760, 80, 430, 300, {r:14});
C.rect(x, y, s, s, {fill:BL}); C.rect(x + s, y, s, s, {fill:CY}); C.rect(x + s * 2, y, s, s, {fill:YL});
knob(C, x + s, y + s / 2, r, BL); knob(C, x + s * 2, y + s / 2, r, YL); knob(C, x + s * 0.5, y, r, BL); knob(C, x + s * 2.5, y, r, YL);
K5.closeText(C, 88, 130, 600, {mark:function(C, x, y, k, c){ C.rect(x, y, 36, 36, {fill:lc(k), r:6}); knob(C, x + 36, y + 18, 7, lc(k)); C.icon(x + 10, y + 10, 16, c.icon, k === 3 ? NV : '#FFFFFF'); }});
}
K5.make({
id:'jigsaw', name:'Jigsaw', tag:'White · puzzle pieces',
desc:'Interlocking puzzle pieces in blue, cyan, navy and yellow: each point is a piece that locks into the next, and the cover leaves one piece about to drop in. For solutions, problem-and-fit stories, team roles and integration plans.',
fonts:{head:'Rubik', body:'Rubik', headW:700, bodyW:400, headLh:1.08, gf:'Rubik:wght@400;500;600;700;800'},
P:P, LC:LC, cardR:10, imgR:14, coverLbl:'Puzzle', closeLbl:'Puzzle',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; C.rect(W - 132, 0, 66, 60, {fill:BL}); C.rect(W - 66, 0, 66, 60, {fill:CY}); knob(C, W - 66, 30, 11, BL); knob(C, W - 33, 60, 11, CY); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y, 30, 6, {fill:BL}); C.rect(x + 30, y, 30, 6, {fill:CY}); knob(C, x + 30, y + 3, 6, BL); return 18; },
card:function(C, x, y, w, h, k, o){
var hot = o.hot, f = hot ? BL : '#F1F5F9';
C.rect(x, y, w, h, {fill:f, r:10}); if (!o.big) knob(C, x + w, y + h / 2, 15, f);
return hot ? {tc:'#FFFFFF', mc:'#D4E0FF', ac:'#FFFFFF', hot:1} : {tc:NV, mc:MU, ac:lc(k)};
},
mark:function(C, x, y, s, t, k, cc){ var hot = cc && cc.hot, c = hot ? YL : lc(k); C.rect(x, y, s, s, {fill:c, r:8}); knob(C, x + s, y + s / 2, s * 0.18, c); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, K.on(c)); },
rowMark:function(C, x, y, s, t, k){ var c = lc(k); C.rect(x, y, s, s, {fill:c, r:8}); knob(C, x + s / 2, y + s, s * 0.18, c); num(C, x, y, s, s, k, s * 0.38, K.on(c)); },
node:function(C, cx, cy, s, k){ var c = lc(k); C.rect(cx - s / 2, cy - s / 2, s, s, {fill:c, r:8}); knob(C, cx + s / 2, cy, s * 0.2, c); num(C, cx - s / 2, cy - s / 2, s, s, k, s * 0.36, K.on(c)); },
track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:PALE, r:3}); },
L:{cards:'Tab cards', rows:'Piece rows', steps:'Piece steps', stmt:'Missing piece', stats:'Number tiles'},
sig:function(pg, B){
function locked(C, Z, o){
var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
var cw = Z.w / n, ph = Math.min(Z.h, 340), y = Z.y + (Z.h - ph) / 2, r = Math.min(34, cw * 0.12), tw = cw - r - 50;
var F = K.fitHD(C, it, tw, 50, ph - 150, {hMax:24, dMax:16.5, hMin:12, dMin:10});
it.forEach(function(t, k){ C.rect(Z.x + k * cw, y, cw + (k < n - 1 ? 1 : 0), ph, {fill:lc(k)}); });
it.forEach(function(t, k){
var x = Z.x + k * cw, c = lc(k), on = K.on(c), tx = x + (k ? r + 22 : 28);
if (k < n - 1) knob(C, x + cw, y + ph * 0.5, r, c);
C.text(tx, y + 24, 90, 40, pad2(k + 1), {font:'head', size:32, wt:800, color:on, lh:1, va:'m'}); K.icon(C, x + cw - 62, y + 28, 32, t, on);
K.hd(C, tx, y + 96, tw, ph - 116, t, F, {tc:on, mc:on});
});
}
function tiles(C, Z, o){
K5.stat(C, Z, {ah:Math.min(210, Z.h * 0.52), tg:30, vMax:46, vw:function(cw){ return cw - 80; }}, function(C, k, t, b){
var c = lc(k), s = Math.min(b.w - 44, b.ah - 10), x = b.cx - s / 2;
C.rect(x, b.y, s, s, {fill:c, r:10}); knob(C, x + s, b.y + s / 2, s * 0.13, c); knob(C, b.cx, b.y + s, s * 0.13, c);
return {vc:K.on(c), vx:x + 10, vw:s - 20, vy:b.y + (s - b.vs * 1.15) / 2};
});
}
function chain(C, Z, o){
K5.alt(C, Z, {off:58, fallback:B.rows, pre:function(C, g){ C.rect(g.x, g.y - 3, g.w, 6, {fill:PALE, r:3}); }}, function(C, k, t, b){
var c = lc(k); C.rect(b.cx - 36, b.cy - 36, 72, 72, {fill:c, r:8}); knob(C, b.cx + 36, b.cy, 13, c); knob(C, b.cx, b.up ? b.cy - 36 : b.cy + 36, 13, c); num(C, b.cx - 36, b.cy - 36, 72, 72, k, 22, K.on(c));
});
}
function pieces(C, Z, o){
K5.list(C, Z, {aw:92, pr:16, gap:14, rhMax:96}, function(C, k, t, b){
var c = lc(k), s = Math.min(62, b.h - 8); C.rect(b.x, b.cy - s / 2, s, s, {fill:c, r:8}); knob(C, b.x + s / 2, b.cy + s / 2, s * 0.18, c); num(C, b.x, b.cy - s / 2, s, s, k, s * 0.38, K.on(c));
});
}
return {agenda:[['Piece list', pg(pieces)]], p2:[['Locked pieces', pg(locked)]], p3:[['Locked pieces', pg(locked)]], p4:[['Locked pieces', pg(locked)]], p56:[['Piece list', pg(pieces)]], p78:[['Piece list', pg(pieces)]],
stats:[['Number pieces', pg(tiles)]], timeline:[['Piece chain', pg(chain)]]};
}
});
})();
