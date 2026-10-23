
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 85 - Botanical Line : sage-white page, fine line-drawn leaves and branches, arch-topped panels, olive and brass */
var BG = '#F2F4EF', OL = '#3F4A2A', BR = '#B08D3E', INK = '#1F231A', MU = '#5C6250', SG = '#DDE3D3', WH = '#FFFFFF';
var P0 = {bg:BG, text:INK, title:OL, muted:MU, accent:OL, accent2:BR, card:WH, line:'#D1D8C4', onAccent:'#FFFFFF'};
/* a leaf outline + midrib, pointing at angle a (degrees) from base (x,y) */
function leafD(d, x, y, len, a, wid){
  var ux = Math.cos(a * RAD), uy = Math.sin(a * RAD), px = -uy, py = ux, w = len * (wid || 0.32), tx = x + ux * len, ty = y + uy * len;
  d.push(['M', x, y], ['C', x + ux * len * 0.3 + px * w, y + uy * len * 0.3 + py * w, x + ux * len * 0.75 + px * w * 0.8, y + uy * len * 0.75 + py * w * 0.8, tx, ty]);
  d.push(['C', x + ux * len * 0.75 - px * w * 0.8, y + uy * len * 0.75 - py * w * 0.8, x + ux * len * 0.3 - px * w, y + uy * len * 0.3 - py * w, x, y]);
  d.push(['M', x, y], ['L', x + ux * len * 0.85, y + uy * len * 0.85]);
}
/* a branch: a curved stem with leaves alternating along it */
function branch(C, x0, y0, x1, y1, bend, n, len, c, lw){
  var mx = (x0 + x1) / 2 + bend, my = (y0 + y1) / 2, d = [['M', x0, y0], ['C', x0 + (mx - x0) * 0.66, y0 + (my - y0) * 0.66, x1 + (mx - x1) * 0.66, y1 + (my - y1) * 0.66, x1, y1]];
  var ang = Math.atan2(y1 - y0, x1 - x0) / RAD;
  for (var i = 1; i <= n; i++) {
    var t = i / (n + 1), it = 1 - t;
    var bx = it * it * x0 + 2 * it * t * mx + t * t * x1, by = it * it * y0 + 2 * it * t * my + t * t * y1;
    var tang = Math.atan2(2 * it * (my - y0) + 2 * t * (y1 - my), 2 * it * (mx - x0) + 2 * t * (x1 - mx)) / RAD;
    leafD(d, bx, by, len * (0.7 + 0.3 * Math.sin(i * 1.7)), tang + (i % 2 ? -48 : 48), 0.34);
  }
  leafD(d, x1, y1, len * 0.9, ang, 0.34);
  C.path(d, {stroke:c || OL, lw:lw || 1.6});
}
function sprig(C, x, y, s, a, c){ var d = []; leafD(d, x, y, s, a - 30, 0.34); leafD(d, x, y, s * 0.8, a + 30, 0.34); leafD(d, x, y, s * 1.1, a, 0.3); C.path(d, {stroke:c || BR, lw:1.4}); }
function arch(C, x, y, w, h, o){ var r = w / 2; var d = [['M', x, y + h], ['L', x, y + r]]; for (var i = 0; i <= 16; i++) { var a = (180 + 180 * i / 16) * RAD; d.push(['L', x + r + Math.cos(a) * r, y + r + Math.sin(a) * r]); } d.push(['L', x + w, y + h], ['Z']); C.path(d, o); }
function laurel(C, cx, cy, r, c){ var d = []; for (var s = -1; s <= 1; s += 2) for (var i = 0; i < 7; i++) { var a = (90 + s * (30 + i * 19)) * RAD, x = cx + Math.cos(a) * r, y = cy + Math.sin(a) * r; leafD(d, x, y, r * 0.32, (a / RAD) + s * 95, 0.36); } C.path(d, {stroke:c || BR, lw:1.4}); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  if (k) branch(C, W + 10, H - 30, W - 150, H - 260, -40, 4, 34, OL, 1.4); else branch(C, W - 30, -10, W - 200, 170, 50, 4, 30, OL, 1.4);
}
function head(C, A, o){
  var y = 52;
  var te = C.text(A.x, y, Math.min(A.w, 880), 96, C.s.title || '', {font:'head', fit:{max:44, min:24}, wt:400, color:OL, lh:1.08, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  sprig(C, A.x + 10, y + 12, 16, -90, BR); C.rect(A.x + 30, y + 10, 70, 1.5, {fill:BR});
  y += 26;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:INK, mc:MU, ac:OL, markS:42, g:20, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:WH, r:6}); sprig(C, x + w - 40, y + h - 20, 40, -120, BR); return {tc:INK, mc:MU, ac:OL}; }
    if (o.hot) { arch(C, x, y, w, h, {fill:OL}); return {tc:'#FFFFFF', mc:'#D7DDC9', ac:'#E6D3A3'}; }
    if (h > w * 0.75 && !o.stat) { arch(C, x, y, w, h, {fill:WH}); arch(C, x + 8, y + 8, w - 16, h - 16, {stroke:SG, lw:1.2}); return {tc:INK, mc:MU, ac:OL}; }
    C.rect(x, y, w, h, {fill:WH, r:6}); sprig(C, x + w - 26, y + 34, 22, -125, BR);
    return {tc:INK, mc:MU, ac:OL};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:'#D1D8C4'}); return {tc:INK, mc:MU, ac:OL}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {line:on ? '#E6D3A3' : BR, lw:1.5}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? '#FFFFFF' : OL); },
  rowMark:function(C, x, y, s, t, k){ sprig(C, x + s / 2, y + s * 0.9, s * 0.45, -90, k % 2 ? BR : OL); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? BG : OL, line:OL, lw:2}); var d = []; leafD(d, cx, cy + s * 0.18, s * 0.34, -90, 0.4); C.path(d, {stroke:k % 2 ? OL : '#FFFFFF', lw:1.4}); },
  track:function(C, x1, y, x2){ C.path([['M', x1 - 40, y + 6], ['C', x1 + (x2 - x1) * 0.3, y - 22, x1 + (x2 - x1) * 0.6, y + 24, x2 + 40, y - 4]], {stroke:OL, lw:2}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y + 8, W - 144, Z.h - 26, {r:6}); return; }
    var x = Z.x + (side === 'right' ? 16 : 50), w = Z.w - 66, y = 50, h = H - 120;
    D.imgBox(C, x, y, w, h, {r:w / 2});
    branch(C, side === 'right' ? x - 20 : x + w + 20, y + h, side === 'right' ? x + 40 : x + w - 40, y + h - 200, side === 'right' ? -30 : 30, 4, 30, OL, 1.5);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.company || '', {font:'body', size:12, wt:600, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 60, y, 60, 16, pad2(C.i + 1), {font:'head', size:15, color:BR, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[OL, BR, '#7E8C5D', '#D9C08A', '#A5B48A', '#5C6250'], header:{kind:'left', top:52}, card:{kind:'solid', fill:WH, r:6}, icon:{kind:'plain', fg:OL}, num:{kind:'big', fg:OL},
  conc:{kind:'line', lineC:BR, label:'In essence', lc:BR, tc:INK, italic:true}, footer:{fn:foot}, imgFramed:true, imgR:6, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#C9D3B5'], [100, '#3F4A2A']]}}};
/* signature: vine - points grow as leaves off one stem */
function vine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var sx = Z.x + 40, rh = Z.h / n, tw = Z.w - 120;
  C.path([['M', sx, Z.y - 10], ['C', sx + 30, Z.y + Z.h * 0.3, sx - 30, Z.y + Z.h * 0.7, sx, Z.y + Z.h + 10]], {stroke:OL, lw:2});
  var F = K.fitHD(C, it, tw, 36, rh - 10, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 20, hMin:12, dMin:10, noBoost:n > 3});
  it.forEach(function(t, k){ var y = Z.y + (k + 0.5) * rh, d = [], bh = K3.bH(C, t, tw, F); leafD(d, sx + 2, y, 46, -18, 0.4); C.path(d, {stroke:k % 2 ? BR : OL, lw:1.6}); K.hd(C, sx + 70, y - bh / 2, tw, rh, t, F, {tc:INK, mc:MU}); });
}
/* signature: laurel numbers */
function laurels(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.34, Z.h * 0.3, 104), cy = Z.y + r + 10, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 24;
  var vs = C.groupFit(vals, r * 1.3, r * 0.6, {font:'head', max:50, min:18, maxLines:1}), ls = C.groupFit(labs, tw, 48, {font:'body', max:17, min:12, maxLines:2});
  st.forEach(function(t, k){ var cx = Z.x + cw * (k + 0.5); laurel(C, cx, cy, r, k % 2 ? OL : BR);
    C.text(cx - r * 0.7, cy - r * 0.35, r * 1.4, r * 0.7, vals[k], {font:'head', size:vs, color:OL, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, cy + r + 18, tw, 48, labs[k], {font:'body', size:ls, wt:700, color:INK, align:'c', lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, cy + r + 24 + le._h, tw, Z.y + Z.h - (cy + r + 24 + le._h), t.x, {font:'body', size:13.5, color:MU, align:'c', lh:1.38, italic:true, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  if (o.img === 'photo') { D.imgBox(C, 820, 60, 380, 600, {r:190}); }
  else arch(C, 820, 60, 380, 600, {fill:SG});
  branch(C, 1260, 720, 1110, 120, -60, 7, 46, OL, 1.8); branch(C, 760, 720, 860, 360, 40, 4, 38, OL, 1.6);
  if (f.company) C.text(72, 70, 560, 20, f.company, {font:'body', size:13, wt:700, color:BR, caps:true, ls:0.16, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 140, 640, 260, C.s.title, {font:'head', fit:{max:68, min:30}, wt:400, color:OL, lh:1.06, va:'b', ed:{f:'title'}, maxLines:4});
  sprig(C, 84, 440, 20, -90, BR); C.rect(108, 436, 100, 1.5, {fill:BR});
  if (C.s.subtitle) C.text(72, 466, 620, 76, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.45, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 590, 620, 22, who, {font:'body', size:14, wt:700, color:INK, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  branch(C, -10, H + 10, 260, 400, 60, 6, 44, OL, 1.8); branch(C, W + 10, -10, W - 260, 300, 60, 6, 44, OL, 1.8);
  if (o.img === 'photo') D.imgBox(C, 860, 120, 320, 480, {r:160});
  var w = o.img === 'photo' ? 640 : 900, x = o.img === 'photo' ? 160 : 190;
  C.text(x, 150, w, 180, C.s.title || 'Thank you', {font:'head', fit:{max:88, min:36}, wt:400, color:OL, align:o.img === 'photo' ? 'l' : 'c', lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(x, 350, w, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, align:o.img === 'photo' ? 'l' : 'c', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 440 + k * 44; C.text(x, y, w, 32, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, align:o.img === 'photo' ? 'l' : 'c', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo';
  if (photo) D.imgBox(C, 880, 90, 320, 540, {r:160});
  var cx = photo ? 460 : 640, r = 250;
  laurel(C, cx, 360, r, BR);
  var qe = C.text(cx - r * 0.72, 220, r * 1.44, 240, C.s.title, {font:'head', fit:{max:34, min:16}, color:OL, align:'c', va:'m', lh:1.25, ed:{f:'title'}, maxLines:7});
  if (by) C.text(cx - r * 0.72, 480, r * 1.44, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:14, wt:700, color:BR, align:'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Arch cards', rows:'Leaf rows', steps:'Bud steps', stmt:'Leaf panel', stats:'Number cards', photo:'arched photo', track:'#DDE3D3'};
var VS = K4.vset(pg, B, L, {
  cover:[['Arch + branches', {}], ['Arched photo', {img:'photo'}]],
  closing:[['Branch frame', {}], ['Branches + photo', {img:'photo'}]],
  quote:[['Laurel quote', {}], ['Laurel + photo', {img:'photo'}]],
  agenda:[['Vine', pg(vine)]], p56:[['Vine', pg(vine)]], p78:[['Vine', pg(vine, {max:8})]],
  stats:[['Laurel numbers', pg(laurels)]]
});
D.register({
  id:'botanical', name:'Botanical Line', tag:'Sage white · line-drawn leaves',
  desc:'Calm and premium: fine line-drawn leaves and branches, arch-topped panels and photos, laurel numbers, olive and brass. For ESG and sustainability reports, HR, wellness, CSR and hospitality.',
  fonts:{head:'Gloock', body:'Albert Sans', headW:400, bodyW:400, headLh:1.08, gf:'Gloock|Albert+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

