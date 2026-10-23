
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 83 - Colonnade : limestone page, slim classical columns, inscription capitals, oxblood seal */
var BG = '#EAE6DF', ST = '#BFB6A8', ST2 = '#D6CFC3', WH = '#FBF9F5', OX = '#6E1F2A', CH = '#2A2A2E', MU = '#5F5A53';
var ROM = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
var P0 = {bg:BG, text:CH, title:CH, muted:MU, accent:OX, accent2:ST, card:WH, line:'#CFC6B8', onAccent:'#FFFFFF'};
/* one column: capital, fluted shaft, base */
function column(C, x, y, w, h, c){
  C.rect(x - w * 0.18, y, w * 1.36, h * 0.05, {fill:c || ST}); C.rect(x - w * 0.08, y + h * 0.05, w * 1.16, h * 0.025, {fill:c || ST});
  C.rect(x, y + h * 0.075, w, h * 0.85, {fill:WH});
  var d = []; for (var i = 1; i < 4; i++) { d.push(['M', x + w * i / 4, y + h * 0.1]); d.push(['L', x + w * i / 4, y + h * 0.9]); } C.path(d, {stroke:ST, lw:1});
  C.rect(x - w * 0.08, y + h * 0.925, w * 1.16, h * 0.025, {fill:c || ST}); C.rect(x - w * 0.18, y + h * 0.95, w * 1.36, h * 0.05, {fill:c || ST});
}
function seal(C, cx, cy, r, txt){ C.ell(cx - r, cy - r, r * 2, r * 2, {fill:OX}); C.ell(cx - r * 0.8, cy - r * 0.8, r * 1.6, r * 1.6, {line:'#E9C9CF', lw:1.2}); C.text(cx - r, cy - r, r * 2, r * 2, txt || '', {font:'head', size:r * 0.6, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1}); }
function initials(C){ var n = (C.cover.company || 'Co').split(/\s+/).map(function(w){ return w.charAt(0); }).join('').slice(0, 2).toUpperCase(); return n; }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  C.rect(0, 0, W, 12, {fill:ST}); C.rect(0, 14, W, 3, {fill:ST2});
  C.rect(0, H - 14, W, 14, {fill:CH});
}
function head(C, A, o){
  var y = 50, al = o.img ? 'l' : 'c';
  var te = C.text(A.x, y, A.w, 92, C.s.title || '', {font:'head', fit:{max:38, min:22}, wt:700, color:CH, caps:true, ls:0.04, align:al, lh:1.12, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  var cx = al === 'c' ? A.x + A.w / 2 : A.x + 70;
  C.rect(cx - 70, y + 4, 52, 1.5, {fill:OX}); K.poly(C, [[cx, y], [cx + 6, y + 5], [cx, y + 10], [cx - 6, y + 5]], {fill:OX}); C.rect(cx + 18, y + 4, 52, 1.5, {fill:OX});
  y += 22;
  if (C.s.subtitle) { var se = C.text(A.x + (al === 'c' ? A.w * 0.12 : 0), y, al === 'c' ? A.w * 0.76 : A.w, 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, align:al, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:80, tc:CH, mc:MU, ac:OX, markS:42, g:20, head:head, ptc:CH, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:WH}); C.rect(x + 10, y + 10, w - 20, h - 20, {line:ST, lw:1.2}); return {tc:CH, mc:MU, ac:OX}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:OX}); C.rect(x + 8, y + 8, w - 16, h - 16, {line:'#E9C9CF', lw:1}); return {tc:'#FFFFFF', mc:'#EBD5D9', ac:'#FFFFFF'}; }
    C.rect(x - 8, y, w + 16, 12, {fill:ST}); C.rect(x, y + 12, w, h - 24, {fill:WH}); C.rect(x - 8, y + h - 12, w + 16, 12, {fill:ST});
    return {tc:CH, mc:MU, ac:OX};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:'#CFC6B8'}); return {tc:CH, mc:MU, ac:OX}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.text(x - 6, y - 4, s + 40, s + 8, ROM[k % 10], {font:'head', size:s * 0.7, wt:700, color:on ? '#FFFFFF' : OX, va:'m', lh:1}); },
  rowMark:function(C, x, y, s, t, k){ C.text(x, y, s + 20, s, ROM[k % 10], {font:'head', size:s * 0.56, wt:700, color:OX, va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ C.rect(cx - s * 0.7, cy - s * 0.4, s * 1.4, s * 0.8, {fill:WH, line:ST, lw:2}); C.text(cx - s * 0.7, cy - s * 0.4, s * 1.4, s * 0.8, ROM[k % 10], {font:'head', size:s * 0.36, wt:700, color:OX, align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1 - 80, y - 4, x2 - x1 + 160, 8, {fill:ST}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 80, Z.y + 12, W - 160, Z.h - 30, {r:0}); C.rect(70, Z.y, W - 140, 12, {fill:ST}); return; }
    var x = Z.x + (side === 'right' ? 10 : 50), w = Z.w - 60;
    K.poly(C, [[x - 20, 110], [x + w / 2, 50], [x + w + 20, 110]], {fill:ST});
    C.rect(x - 20, 110, w + 40, 14, {fill:ST2});
    D.imgBox(C, x, 124, w, H - 200, {r:0});
    C.rect(x - 20, H - 76, w + 40, 16, {fill:ST});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 44; C.text(a.x, y, 420, 16, (C.cover.company || '').toUpperCase(), {font:'head', size:11, wt:700, color:MU, ls:0.14, lh:1, va:'m'}); C.text(a.x + a.w - 80, y, 80, 16, ROM[C.i % 10] || pad2(C.i + 1), {font:'head', size:14, wt:700, color:OX, align:'r', va:'m', lh:1}); }
var S = {margin:80, series:[OX, CH, ST, '#A35A65', '#8B8478', '#D6CFC3'], header:{kind:'center', top:50}, card:{kind:'solid', fill:WH, r:0}, icon:{kind:'plain', fg:OX}, num:{kind:'big', fg:OX},
  conc:{kind:'line', lineC:OX, label:'Resolution', lc:OX, tc:CH}, footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26, imgPh:{grad:{ang:135, stops:[[0, '#D6CFC3'], [100, '#8B8478']]}}};
/* signature: pillars - each point is written inside a column */
function pillars(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 700) return B.cards(C, Z, o);
  var g = 24, cw = (Z.w - g * (n - 1)) / n, tw = cw - 36, top = Z.y + 6, h = Z.h - 6;
  C.rect(Z.x - 30, top - 30, Z.w + 60, 14, {fill:ST}); K.poly(C, [[Z.x - 30, top - 30], [Z.x + Z.w / 2, top - 58], [Z.x + Z.w + 30, top - 30]], {fill:ST2});
  var F = K.fitHD(C, it, tw, 60, h - 110, {hMax:23, dMax:17.5, hMin:12, dMin:10});
  it.forEach(function(t, k){ var x = Z.x + k * (cw + g); column(C, x + 8, top - 14, cw - 16, h + 22, ST);
    C.text(x, top + 24, cw, 30, ROM[k], {font:'head', size:22, wt:700, color:OX, align:'c', lh:1});
    K.hd(C, x + 18, top + 66, tw, h - 100, t, F, {tc:CH, mc:MU, align:'c'});
  });
  C.rect(Z.x - 30, top + h + 8, Z.w + 60, 10, {fill:CH});
}
/* signature: plaques - numbers engraved on stone tablets */
function plaques(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 22, cw = (Z.w - g * (n - 1)) / n, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 50;
  var vs = C.groupFit(vals, tw, Z.h * 0.3, {font:'head', max:60, min:22, maxLines:1}), ls = C.groupFit(labs, tw, 46, {font:'head', max:16, min:11, maxLines:2});
  st.forEach(function(t, k){ var x = Z.x + k * (cw + g);
    C.rect(x, Z.y, cw, Z.h, {fill:WH}); C.rect(x + 10, Z.y + 10, cw - 20, Z.h - 20, {line:ST, lw:2}); C.rect(x + 16, Z.y + 16, cw - 32, Z.h - 32, {line:ST2, lw:1});
    C.text(x + 25, Z.y + Z.h * 0.24, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:700, color:OX, align:'c', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + cw / 2 - 30, Z.y + Z.h * 0.24 + vs * 1.1 + 14, 60, 1.5, {fill:OX});
    C.text(x + 25, Z.y + Z.h * 0.24 + vs * 1.1 + 30, tw, 46, labs[k], {font:'head', size:ls, wt:700, color:CH, align:'c', caps:true, ls:0.06, lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 25, Z.y + Z.h * 0.24 + vs * 1.1 + 84, tw, Z.h * 0.3, t.x, {font:'body', size:13.5, color:MU, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* signature: staircase - stone steps rising with the timeline */
function stairs(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 700) return B.rows(C, Z, o);
  var cw = Z.w / n, stp = Z.h * 0.5 / n, tw = cw - 24;
  var F = K.fitHD(C, it, tw, 50, Z.h * 0.4, {hMax:22, dMax:16.5, hMin:12, dMin:10});
  it.forEach(function(t, k){ var x = Z.x + k * cw, top = Z.y + Z.h - stp * (k + 1) - 30;
    C.rect(x, top, cw, Z.y + Z.h - top, {fill:k % 2 ? WH : ST2}); C.rect(x, top, cw, 6, {fill:ST});
    C.text(x + 12, top + 14, tw, 26, ROM[k], {font:'head', size:20, wt:700, color:OX, lh:1});
    var bh = K3.bH(C, t, tw, F); K.hd(C, x + 12, top - bh - 14, tw, bh + 10, t, F, {tc:CH, mc:MU});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function portico(C, x0, w, photo){
  K.poly(C, [[x0, 120], [x0 + w / 2, 40], [x0 + w, 120]], {fill:ST}); K.poly(C, [[x0 + 40, 112], [x0 + w / 2, 58], [x0 + w - 40, 112]], {fill:ST2});
  C.rect(x0, 120, w, 18, {fill:ST}); C.rect(x0 + 10, 138, w - 20, 8, {fill:ST2});
  if (photo) D.imgBox(C, x0 + 20, 146, w - 40, H - 236, {r:0});
  else { var n = 5, cw = 46, gap = (w - 40 - n * cw) / (n - 1); for (var i = 0; i < n; i++) column(C, x0 + 20 + i * (cw + gap), 146, cw, H - 236, ST); }
  C.rect(x0 - 10, H - 90, w + 20, 14, {fill:ST}); C.rect(x0 - 24, H - 76, w + 48, 14, {fill:ST2}); C.rect(x0 - 38, H - 62, w + 76, 62, {fill:CH});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  portico(C, 760, 420, o.img === 'photo');
  seal(C, 112, 90, 34, initials(C));
  if (f.company) C.text(160, 72, 560, 36, f.company.toUpperCase(), {font:'head', size:15, wt:700, color:CH, ls:0.14, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
  C.text(80, 170, 620, 250, C.s.title, {font:'head', fit:{max:58, min:26}, wt:700, color:CH, caps:true, ls:0.03, lh:1.1, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(80, 440, 100, 2, {fill:OX});
  if (C.s.subtitle) C.text(80, 462, 600, 76, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.45, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(80, 580, 600, 22, who, {font:'head', size:14, wt:700, color:OX, caps:true, ls:0.08, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.date) C.text(80, 616, 400, 18, f.date, {font:'body', size:13, color:MU, lh:1});
}
function closing(C, v){
  var o = C.o || {};
  portico(C, 140, 1000, o.img === 'photo');
  if (o.img === 'photo') C.rect(160, 146, 960, H - 236, {fill:CH, op:0.55});
  else { C.rect(300, 200, 680, 330, {fill:BG}); C.rect(310, 210, 660, 310, {line:ST, lw:1.5}); }
  C.text(320, 230, 640, 120, C.s.title || 'Thank you', {font:'head', fit:{max:64, min:30}, wt:700, color:o.img === 'photo' ? '#FFFFFF' : CH, caps:true, ls:0.06, align:'c', va:'m', lh:1.05, ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(340, 352, 600, 40, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:o.img === 'photo' ? '#EDE7DD' : MU, align:'c', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C), cw = 1000 / Math.max(1, cs.length);
  cs.forEach(function(c, k){ C.text(140 + k * cw, H - 56, cw, 50, c.val, {font:'body', fit:{max:15, min:10.5}, wt:700, color:'#FFFFFF', align:'c', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
  seal(C, 640, 440, 36, initials(C));
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo';
  column(C, 90, 80, 70, H - 160, ST); column(C, W - 160, 80, 70, H - 160, ST);
  if (photo) { D.imgBox(C, 220, 80, 840, 200, {r:0}); }
  C.rect(220, photo ? 300 : 150, 840, photo ? 200 : 300, {fill:WH}); C.rect(230, photo ? 310 : 160, 820, photo ? 180 : 280, {line:ST, lw:1.5});
  var qe = C.text(260, photo ? 320 : 180, 760, photo ? 160 : 240, C.s.title, {font:'head', fit:{max:photo ? 26 : 34, min:15}, wt:700, color:CH, caps:true, ls:0.04, align:'c', va:'m', lh:1.25, ed:{f:'title'}, maxLines:6});
  if (by) C.text(220, photo ? 520 : 480, 840, 26, '— ' + (by.h ? by.h + ', ' + by.d : by.d) + ' —', {font:'body', size:15, wt:700, color:OX, align:'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Stone cards', rows:'Article rows', steps:'Tablet steps', stmt:'Inscription', stats:'Stone cards', photo:'portico photo', track:'#D6CFC3'};
var VS = K4.vset(pg, B, L, {
  cover:[['Portico columns', {}], ['Portico photo', {img:'photo'}]],
  closing:[['Full portico', {}], ['Portico photo', {img:'photo'}]],
  quote:[['Frieze inscription', {}], ['Frieze + photo', {img:'photo'}]],
  p2:[['Pillars', pg(pillars)]], p3:[['Pillars', pg(pillars)]], p4:[['Pillars', pg(pillars)]], p56:[['Pillars', pg(pillars)]],
  stats:[['Stone plaques', pg(plaques)]], timeline:[['Staircase', pg(stairs)]]
});
D.register({
  id:'colonnade', name:'Colonnade', tag:'Limestone · classical columns',
  desc:'Institutional and classical: limestone pages, slim fluted columns and porticos, inscription-style capitals, roman numerals and an oxblood seal. For law firms, government, universities, governance and heritage institutions.',
  fonts:{head:'Cinzel', body:'Assistant', headW:700, bodyW:400, headLh:1.1, gf:'Cinzel:wght@500;600;700|Assistant:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

