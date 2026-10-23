
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 76 - Bauhaus Studio : white page, quarter circles, half circles, squares and triangles in ochre, brick and navy */
var BG = '#FFFFFF', OC = '#E0A526', BR = '#C1432E', NV = '#1F2F5C', BK = '#141414', MU = '#5E5A55', PA = '#F1EEE8';
var SH = [OC, BR, NV];
var P0 = {bg:BG, text:BK, title:BK, muted:MU, accent:BR, accent2:NV, card:PA, line:'#E2DED6', onAccent:'#FFFFFF'};
/* quarter circle with its corner at (x,y), radius r, opening toward quadrant q (0 = down-right, 1 = down-left, 2 = up-left, 3 = up-right) */
function quarter(C, x, y, r, q, c){
  var a0 = [0, 90, 180, 270][q], d = [['M', x, y]], n = 8;
  for (var i = 0; i <= n; i++) { var a = (a0 + 90 * i / n) * RAD; d.push(['L', x + Math.cos(a) * r, y + Math.sin(a) * r]); }
  d.push(['Z']); C.path(d, {fill:c});
}
function half(C, cx, cy, r, up, c){ var d = [['M', cx - r, cy]], n = 16; for (var i = 0; i <= n; i++) { var a = (180 + 180 * i / n) * RAD * (up ? 1 : -1); d.push(['L', cx + Math.cos(a) * r * (up ? 1 : 1), cy + Math.sin(a) * r]); } d.push(['Z']); C.path(d, {fill:c}); }
function shape(C, kind, x, y, s, c){
  if (kind === 0) C.ell(x, y, s, s, {fill:c});
  else if (kind === 1) C.rect(x, y, s, s, {fill:c});
  else K.poly(C, [[x + s / 2, y], [x + s, y + s], [x, y + s]], {fill:c});
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing' || kind === 'quote') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { quarter(C, W, 0, 120, 1, OC); C.ell(W - 70, 128, 34, 34, {fill:BR}); }
  else if (k === 1) { C.rect(W - 70, 0, 70, 70, {fill:NV}); quarter(C, W - 70, 70, 70, 3, OC); }
  else { half(C, W - 110, 0, 80, false, BR); C.rect(W - 32, 90, 32, 32, {fill:BK}); }
}
function head(C, A, o){
  var y = 54;
  var te = C.text(A.x, y, Math.min(A.w, 900), 96, C.s.title || '', {font:'head', fit:{max:42, min:22}, wt:800, color:BK, caps:true, lh:1.04, ed:{f:'title'}, maxLines:2});
  y += te._h + 12;
  C.rect(A.x, y, 18, 18, {fill:BR}); C.ell(A.x + 26, y, 18, 18, {fill:OC}); K.poly(C, [[A.x + 61, y], [A.x + 70, y + 18], [A.x + 52, y + 18]], {fill:NV});
  y += 30;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:BK, mc:MU, ac:BR, markS:46, g:16, head:head, ptc:BK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:PA}); half(C, x + w - 120, y + h, 90, true, BR); return {tc:BK, mc:MU, ac:BR}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:NV}); quarter(C, x + w, y, Math.min(70, h * 0.4), 1, OC); return {tc:'#FFFFFF', mc:'#C9D0E3', ac:OC}; }
    C.rect(x, y, w, h, {fill:PA}); var q = k % 4, r = Math.min(56, h * 0.32, w * 0.2);
    quarter(C, q === 0 || q === 3 ? x + w : x, q < 2 ? y : y + h, r, [1, 0, 3, 2][q], SH[k % 3]);
    return {tc:BK, mc:MU, ac:SH[k % 3]};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 2, w, 2, {fill:BK}); return {tc:BK, mc:MU, ac:BR}; },
  mark:function(C, x, y, s, t, k, cc){ shape(C, k % 3, x, y, s, cc && cc.tc === '#FFFFFF' ? OC : SH[k % 3]); K.icon(C, x + s * 0.27, y + s * (k % 3 === 2 ? 0.4 : 0.27), s * 0.46, t, '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ shape(C, k % 3, x, y, s, SH[k % 3]); C.text(x, y + (k % 3 === 2 ? s * 0.15 : 0), s, s, String(k + 1), {font:'head', size:s * 0.42, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ var z = s * (0.75 + 0.1 * k); shape(C, k % 3, cx - z / 2, cy - z / 2, z, SH[k % 3]); C.text(cx - z / 2, cy - z / 2 + (k % 3 === 2 ? z * 0.15 : 0), z, z, String(k + 1), {font:'head', size:z * 0.4, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:BK}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 0, Z.y, W, Z.h, {r:0}); quarter(C, 0, Z.y, 90, 0, OC); C.rect(W - 90, Z.y, 90, 90, {fill:BR}); return; }
    D.imgBox(C, Z.x, 0, Z.w, H, {r:0});
    if (side === 'right') { quarter(C, Z.x, H, 160, 3, OC); C.rect(Z.x + Z.w - 80, 0, 80, 80, {fill:NV}); } else { quarter(C, Z.x + Z.w, H, 160, 2, BR); C.ell(Z.x + 30, 30, 60, 60, {fill:OC}); }
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 400, 16, C.cover.company || '', {font:'head', size:11, wt:700, color:BK, caps:true, ls:0.14, lh:1, va:'m'}); C.text(a.x + a.w - 60, y, 60, 16, pad2(C.i + 1), {font:'head', size:13, wt:800, color:BR, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[BR, OC, NV, BK, '#E6B9AE', '#9AA7C7'], header:{kind:'left', top:54}, card:{kind:'solid', fill:PA, r:0}, icon:{kind:'plain', fg:BR}, num:{kind:'big', fg:BR},
  conc:{kind:'band', fill:BK, r:0, label:'Point', tc:'#FFFFFF', lc:OC}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#E0A526'], [100, '#1F2F5C']]}}};
/* signature: shape columns - a big circle, square or triangle crowns each column */
function crowns(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 700) return B.cards(C, Z, o);
  var cw = Z.w / n, s = Math.min(cw * 0.42, Z.h * 0.36, 120), tw = cw - 30;
  var F = K.fitHD(C, it, tw, 50, Z.h - s - 40, {hMax:22, dMax:16.5, hMin:12, dMin:10});
  it.forEach(function(t, k){ var x = Z.x + k * cw; shape(C, k % 3, x + 10, Z.y, s, SH[k % 3]); C.rect(x + 10, Z.y + s + 16, 44, 5, {fill:BK}); K.hd(C, x + 10, Z.y + s + 34, tw, Z.h - s - 34, t, F, {tc:BK, mc:MU}); });
}
/* signature: big shape numbers */
function shapeStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.8, Z.h * 0.56, 200), vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 24;
  var vs = C.groupFit(vals, s * 0.8, s * 0.36, {font:'head', max:52, min:18, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12, maxLines:2});
  st.forEach(function(t, k){ var cx = Z.x + cw * (k + 0.5), x = cx - s / 2, kind = k % 3 === 2 ? 1 : k % 3;
    shape(C, kind, x, Z.y, s, SH[k % 3]);
    C.text(x, Z.y, s, s, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, Z.y + s + 18, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:BK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, Z.y + s + 24 + le._h, tw, Z.y + Z.h - (Z.y + s + 24 + le._h), t.x, {font:'body', size:14, color:MU, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function comp(C, x0, photo){
  var w = W - x0;
  C.rect(x0, 0, w, H, {fill:NV});
  if (photo) D.imgBox(C, x0, 0, w, 420, {r:0});
  quarter(C, x0, H, 300, 3, OC);
  C.rect(W - 160, H - 220, 160, 220, {fill:BK});
  if (!photo) { C.ell(x0 + w * 0.45, 70, 170, 170, {fill:BR}); quarter(C, W, 0, 150, 1, OC); quarter(C, x0, 420, 130, 0, '#FFFFFF'); }
  else { quarter(C, W, 0, 120, 1, BR); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  comp(C, 700, o.img === 'photo');
  if (f.company) C.text(72, 60, 560, 20, f.company, {font:'head', size:13, wt:800, color:BR, caps:true, ls:0.16, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 120, 600, 300, C.s.title, {font:'head', fit:{max:76, min:30}, wt:800, color:BK, caps:true, lh:0.98, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(72, 440, 90, 10, {fill:BR});
  if (C.s.subtitle) C.text(72, 470, 560, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 590, 560, 24, who, {font:'head', size:15, wt:700, color:BK, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.website) C.text(72, 640, 400, 18, f.website, {font:'body', size:12.5, color:MU, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {};
  if (o.img === 'photo') { D.imgBox(C, 640, 0, 640, H, {r:0}); quarter(C, 640, H, 220, 3, OC); C.rect(W - 120, 0, 120, 120, {fill:BR}); }
  else { C.rect(640, 0, 640, H, {fill:BK}); C.ell(760, 100, 260, 260, {fill:OC}); C.rect(1020, 360, 260, 360, {fill:BR}); quarter(C, 640, H, 260, 3, NV); half(C, 1150, 360, 110, true, '#FFFFFF'); }
  C.text(72, 120, 540, 220, C.s.title || 'Thank You', {font:'head', fit:{max:90, min:36}, wt:800, color:BK, caps:true, lh:0.98, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 356, 520, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 444 + k * 56; shape(C, k % 3, 72, y, 40, SH[k % 3]); C.icon(82, y + (k % 3 === 2 ? 14 : 10), 20, c.icon, '#FFFFFF'); C.text(128, y, 480, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:BK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 620 : 780;
  C.bg({fill:PA});
  if (photo) { D.imgBox(C, 760, 0, 520, H, {r:0}); quarter(C, 760, H, 160, 3, BR); } else { C.ell(900, 160, 400, 400, {fill:OC}); quarter(C, W, H, 200, 2, NV); }
  C.text(72, 90, 160, 160, '“', {font:'head', size:200, wt:800, color:BR, lh:1, caps:false});
  var qe = C.text(72, 230, w, 300, C.s.title, {font:'head', fit:{max:42, min:20}, wt:800, color:BK, lh:1.15, ed:{f:'title'}, maxLines:6});
  if (by) C.text(72, 230 + qe._h + 26, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:BR, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Shape-cut cards', rows:'Shape rows', steps:'Growing shapes', stmt:'Statement panel', stats:'Number cards', photo:'photo', track:'#F1EEE8'};
var VS = K4.vset(pg, B, L, {
  cover:[['Shape composition', {}], ['Shapes + photo', {img:'photo'}]],
  closing:[['Shape mosaic', {}], ['Shapes + photo', {img:'photo'}]],
  quote:[['Ochre circle quote', {}], ['Quote + photo', {img:'photo'}]],
  p2:[['Shape columns', pg(crowns)]], p3:[['Shape columns', pg(crowns)]], p4:[['Shape columns', pg(crowns)]],
  stats:[['Shape numbers', pg(shapeStats)]]
});
D.register({
  id:'bauhaus', name:'Bauhaus Studio', tag:'White · modernist shapes',
  desc:'Bold modernist compositions of quarter circles, half circles, squares and triangles in ochre, brick and navy with heavy capital headings. For agencies, architecture, design studios and creative pitches.',
  fonts:{head:'League Spartan', body:'Karla', headW:800, bodyW:400, headLh:1.0, gf:'League+Spartan:wght@600;700;800|Karla:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

