
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K5) return;
var W = D.W, H = D.H, K = D.K2, K3 = D.K3, K5 = D.K5, Q = K5.p, pad2 = D.u.pad2, mix = D.u.mix;
function num(C, x, y, w, h, k, sz, col, f){ C.text(x, y, w, h, f || pad2(k + 1), {font:'head', size:sz, wt:800, color:col, align:'c', va:'m', lh:1, caps:false}); }
/* Template 95 - Meridian : a wire globe of latitude and longitude lines with location pins */
var OC = '#06283D', BL = '#1363DF', SK = '#47B5FF', MU = '#4F6B7C', LN = '#CFE4F2', ICE = '#DFF6FF';
var P = {bg:'#F3FAFF', text:OC, title:OC, muted:MU, accent:BL, accent2:SK, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var LC = [BL, SK, OC, '#0FA3B1'];
function globe(C, cx, cy, R, col, lw, op){
var d = []; Q.circ(d, cx, cy, R); [0.3, 0.62, 0.88].forEach(function(k){ Q.ell(d, cx, cy, R * k, R); }); Q.line(d, cx, cy - R, cx, cy + R);
[-0.66, -0.33, 0, 0.33, 0.66].forEach(function(k){ var hw = Math.sqrt(R * R - k * k * R * R); Q.line(d, cx - hw, cy + k * R, cx + hw, cy + k * R); });
K5.stroke(C, d, col, lw, op);
}
function pin(C, cx, cy, r, col){ K5.fill(C, Q.poly([], [[cx - r * 0.74, cy + r * 0.66], [cx + r * 0.74, cy + r * 0.66], [cx, cy + r * 1.9]]), col); C.ell(cx - r, cy - r, r * 2, r * 2, {fill:col}); }
function dot(C, x, y){ C.ell(x - 16, y - 16, 32, 32, {line:ICE, lw:2}); C.ell(x - 7, y - 7, 14, 14, {fill:ICE}); }
function cover(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:OC});
if (photo) D.imgBox(C, 720, 70, 580, 580, {shape:'ell'});
globe(C, 1010, 360, photo ? 290 : 330, SK, 2, photo ? 0.55 : 0.8);
if (!photo) { dot(C, 870, 250); dot(C, 1080, 470); dot(C, 1000, 150); }
K5.coverText(C, 96, 120, 600, {tc:'#FFFFFF', sc:'#B9DDF2', kc:SK, max:58, by:644});
}
function closing(C){
var photo = (C.o || {}).img === 'photo';
C.bg({fill:OC});
if (photo) D.imgBox(C, 800, 140, 440, 440, {shape:'ell'});
globe(C, photo ? 1020 : 1100, photo ? 360 : 620, photo ? 220 : 380, SK, 2, 0.7);
K5.closeText(C, 96, 150, 660, {tc:'#FFFFFF', sc:'#B9DDF2', mc:SK, mi:OC});
}
K5.make({
id:'meridian', name:'Meridian', tag:'Ocean navy · wire globe',
desc:'A wire globe of latitude and longitude lines with glowing location pins on an ocean-navy cover, and light pages where regions become columns and every point is pinned to the map. For global expansion, export, multi-country reports and international teams.',
fonts:{head:'Sora', body:'Sora', headW:700, bodyW:400, headLh:1.1, gf:'Sora:wght@300;400;500;600;700;800'},
P:P, LC:LC, cardR:16, imgR:16, coverLbl:'Wire globe', closeLbl:'Wire globe',
deco:function(C, kind){ if (kind === 'cover' || kind === 'closing') return; globe(C, W - 30, H + 30, 210, SK, 1.5, 0.55); },
cover:cover, closing:closing,
rule:function(C, x, y){ C.rect(x, y + 2, 70, 2, {fill:BL}); C.ell(x + 66, y - 3, 12, 12, {fill:SK}); return 18; },
card:function(C, x, y, w, h, k, o){
if (o.hot) { C.rect(x, y, w, h, {fill:OC, r:16}); return {tc:'#FFFFFF', mc:'#B9DDF2', ac:SK, hot:1}; }
C.rect(x, y, w, h, {fill:'#FFFFFF', r:16, line:LN, lw:1.5}); return {tc:OC, mc:MU, ac:BL};
},
mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.hot ? SK : BL; pin(C, x + s / 2, y + s * 0.42, s * 0.42, c); K.icon(C, x + s * 0.27, y + s * 0.19, s * 0.46, t, cc && cc.hot ? OC : '#FFFFFF'); },
rowMark:function(C, x, y, s, t, k){ pin(C, x + s / 2, y + s * 0.4, s * 0.4, k % 2 ? OC : BL); num(C, x, y, s, s * 0.8, k, s * 0.3, '#FFFFFF'); },
node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:'#FFFFFF'}); globe(C, cx, cy, s / 2, BL, 1.5, 1); },
track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:BL, lw:2, dash:true}); },
L:{cards:'Region cards', rows:'Pin rows', steps:'Globe steps', stmt:'Briefing note', stats:'Region numbers'},
sig:function(pg, B){
function regions(C, Z, o){
K5.cols(C, Z, {max:5, ah:112, gap:26, tg:16, fallback:B.rows, pre:function(C, g){ C.line(g.x, g.y + 34, g.x + g.w, g.y + 34, {color:SK, lw:2, dash:true}); }}, function(C, k, t, b){
C.ell(b.x - 4, b.y, 68, 68, {fill:'#F3FAFF'}); pin(C, b.x + 30, b.y + 30, 28, k % 2 ? OC : BL); K.icon(C, b.x + 16, b.y + 16, 28, t, '#FFFFFF');
C.rect(b.x, b.y + 104, b.w, 2, {fill:LN});
});
}
function worlds(C, Z, o){
K5.stat(C, Z, {ah:Math.min(230, Z.h * 0.56), tg:18, vMax:44, vw:function(cw, ah){ return Math.min(cw * 0.6, ah * 0.8); }}, function(C, k, t, b){
var r = Math.min(b.w * 0.4, b.ah * 0.48), cy = b.y + b.ah / 2, c = k % 2 ? OC : BL;
C.ell(b.cx - r, cy - r, r * 2, r * 2, {fill:c}); globe(C, b.cx, cy, r, '#FFFFFF', 1.5, 0.2);
return {vc:'#FFFFFF', vx:b.cx - r * 0.9, vw:r * 1.8, vy:cy - b.vs * 0.58};
});
}
function route(C, Z, o){
K5.alt(C, Z, {same:true, at:0.2, off:54, fallback:B.rows, pre:function(C, g){ C.line(g.x + g.cw / 2, g.y, g.x + g.w - g.cw / 2, g.y, {color:BL, lw:2, dash:true}); }}, function(C, k, t, b){
C.ell(b.cx - 34, b.cy - 34, 68, 68, {fill:'#FFFFFF'}); globe(C, b.cx, b.cy, 34, k % 2 ? OC : BL, 1.6, 1);
C.ell(b.cx + 12, b.cy - 40, 28, 28, {fill:SK}); num(C, b.cx + 12, b.cy - 40, 28, 28, k, 11.5, OC, String(k + 1));
});
}
function pins(C, Z, o){
K5.list(C, Z, {aw:84, pr:20, gap:8, rhMax:100}, function(C, k, t, b){
var s = Math.min(26, b.h * 0.3); pin(C, b.x + 34, b.cy - s * 0.3, s, k % 2 ? OC : BL); num(C, b.x + 34 - s, b.cy - s * 1.3, s * 2, s * 2, k, s * 0.74, '#FFFFFF');
if (!b.last) C.line(b.x + 34, b.cy + s * 1.7, b.x + 34, b.y + b.h + 8, {color:SK, lw:1.5, dash:true});
});
}
return {agenda:[['Pin list', pg(pins)]], p2:[['Region columns', pg(regions)]], p3:[['Region columns', pg(regions)]], p4:[['Region columns', pg(regions)]], p56:[['Pin list', pg(pins)]], p78:[['Pin list', pg(pins)]],
stats:[['Globe numbers', pg(worlds)]], timeline:[['Globe route', pg(route)]]};
}
});
})();
