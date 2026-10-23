
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 68 - Rhombus Proposal : white framed page, clusters of navy / steel / periwinkle diamonds, square navy panels */
var BG = '#FFFFFF', NV = '#0B1468', SL = '#2F5D93', PR = '#6E8EF5', IC = '#DCE5FF', INK = '#0B1468', MU = '#4D5677';
var P0 = {bg:BG, text:INK, title:NV, muted:MU, accent:NV, accent2:PR, card:IC, line:'#C9D5FB', onAccent:'#FFFFFF'};
function dia(C, cx, cy, s, o){ return K.poly(C, K3.sq(cx, cy, s, 0).map(function(p){ return p; }), o); }
function dmd(C, cx, cy, r, o){ return K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], o); }
function cluster(C, cx, cy, s, k){
  var f = [NV, SL, PR];
  dmd(C, cx, cy, s, {fill:f[k % 3]});
  dmd(C, cx + s * 0.95, cy - s * 0.55, s * 0.55, {fill:f[(k + 1) % 3]});
  dmd(C, cx - s * 0.5, cy + s * 0.95, s * 0.42, {fill:f[(k + 2) % 3]});
}
function frame(C){ C.rect(10, 10, W - 20, H - 20, {line:PR, lw:3}); }
function bg(C, kind){
  C.bg({fill:BG}); frame(C);
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 4;
  if (k === 0) { dmd(C, 12, 12, 90, {fill:PR}); dmd(C, 120, 40, 34, {fill:SL}); dmd(C, W - 12, H - 12, 110, {fill:NV}); }
  else if (k === 1) { dmd(C, W - 12, 12, 100, {fill:NV}); dmd(C, W - 150, 50, 30, {fill:PR}); dmd(C, 12, H - 12, 80, {fill:SL}); }
  else if (k === 2) { dmd(C, 12, H - 12, 110, {fill:NV}); dmd(C, 130, H - 40, 34, {fill:PR}); dmd(C, W - 12, 12, 70, {fill:SL}); }
  else { dmd(C, W - 12, H - 12, 100, {fill:PR}); dmd(C, W - 130, H - 34, 30, {fill:NV}); dmd(C, 12, 12, 76, {fill:NV}); }
}
function head(C, A, o){
  var y = 60, al = o.img ? 'l' : 'c';
  var te = C.text(A.x, y, A.w, 92, C.s.title || '', {font:'head', fit:{max:42, min:24}, wt:700, color:NV, align:al, lh:1.08, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x + (al === 'c' ? A.w * 0.12 : 0), y, al === 'c' ? A.w * 0.76 : A.w, 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, align:al, lh:1.4, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
function diaPhoto(C, cx, cy, r){
  K.diamondImg(C, cx, cy, r, BG, {border:NV, bw:10});
  dmd(C, cx + r * 0.78, cy - r * 0.78, r * 0.34, {fill:PR}); dmd(C, cx - r * 0.82, cy + r * 0.82, r * 0.3, {fill:SL});
}
var X = {
  M:72, tc:'#FFFFFF', mc:'#DCE5FF', ac:'#FFFFFF', markS:42, g:16, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:IC}); dmd(C, x + w, y, 46, {fill:NV}); return {tc:INK, mc:MU, ac:NV}; }
    if (o.stat && !o.hot) { C.rect(x, y, w, h, {line:'#C9D5FB', lw:1.5}); return {tc:INK, mc:MU, ac:NV}; }
    C.rect(x, y, w, h, {fill:o.stat ? NV : (k % 2 ? SL : NV)});
    return {tc:'#FFFFFF', mc:'#DCE5FF', ac:'#FFFFFF'};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : '#F1F4FF', line:'#C9D5FB', lw:1}); return {tc:INK, mc:MU, ac:NV}; },
  mark:function(C, x, y, s, t, k, cc){ var r = s / 2; dmd(C, x + r, y + r, r, {fill:cc && cc.tc === '#FFFFFF' ? PR : NV}); K.icon(C, x + r - s * 0.22, y + r - s * 0.22, s * 0.44, t, '#FFFFFF'); },
  node:function(C, cx, cy, s, k){ dmd(C, cx, cy, s / 2, {fill:[NV, SL, PR][k % 3]}); C.text(cx - s / 2, cy - s / 2, s, s, String(k + 1), {font:'head', size:s * 0.36, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:PR, lw:2, dash:true}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, Z.x + 40, Z.y, Z.w - 80, Z.h - 30, {r:0}); dmd(C, Z.x + 40, Z.y, 40, {fill:NV}); dmd(C, Z.x + Z.w - 40, Z.y + Z.h - 30, 40, {fill:PR}); return; }
    diaPhoto(C, Z.x + Z.w / 2, H / 2, Math.min(Z.w * 0.42, 230));
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 42; C.text(a.x, y, 300, 16, C.cover.company || '', {font:'body', size:11, wt:700, color:NV, caps:true, ls:0.12, lh:1, va:'m'}); C.text(a.x + a.w - 60, y, 60, 16, pad2(C.i + 1), {font:'head', size:12, wt:700, color:PR, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[NV, SL, PR, '#9FB3F8', IC, '#4D5677'], header:{kind:'center', top:60}, card:{kind:'solid', fill:NV, r:0}, icon:{kind:'plain', fg:NV}, num:{kind:'big', fg:NV},
  conc:{kind:'box', fill:IC, r:0, label:'Takeaway', lc:NV, tc:INK}, footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#9FB3F8'], [100, '#0B1468']]}}};
/* signature: focus figures - numbers in a row, the middle one in a navy square */
function figures(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, hot = Math.floor((n - 1) / 2), vals = st.map(K3.sv), labs = st.map(K3.sl);
  var vs = C.groupFit(vals, cw - 40, 70, {font:'head', max:56, min:22, maxLines:1}), ls = C.groupFit(labs, cw - 40, 80, {font:'body', max:17, min:11, lh:1.35, maxLines:3});
  st.forEach(function(t, k){
    var x = Z.x + k * cw, on = k === hot;
    if (on) C.rect(x + 6, Z.y, cw - 12, Z.h, {fill:NV});
    C.text(x + 20, Z.y + Z.h * 0.22, cw - 40, vs * 1.1, vals[k], {font:'head', size:vs, wt:700, color:on ? '#FFFFFF' : NV, align:'c', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    dmd(C, x + cw / 2, Z.y + Z.h * 0.22 + vs * 1.1 + 16, 7, {fill:on ? PR : PR});
    C.text(x + 20, Z.y + Z.h * 0.22 + vs * 1.1 + 34, cw - 40, 80, labs[k], {font:'body', size:ls, wt:600, color:on ? '#FFFFFF' : INK, align:'c', lh:1.35, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
    if (t.x) C.text(x + 20, Z.y + Z.h * 0.22 + vs * 1.1 + 120, cw - 40, Z.h * 0.3, t.x, {font:'body', size:13, color:on ? '#DCE5FF' : MU, align:'c', lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* signature: icon rhombus row - one diamond per point with text under, the middle one dark */
function rhombusRow(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.rows(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.26, 56), tw = cw - 20, hot = Math.floor((n - 1) / 2);
  var F = K.fitHD(C, it, tw, 50, Z.h - r * 2 - 40, {hMax:20, dMax:15.5, hMin:12, dMin:10});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = Z.y + r + 4, on = k === hot;
    if (on) C.rect(cx - cw / 2 + 8, Z.y - 8, cw - 16, Z.h + 8, {fill:NV});
    dmd(C, cx, cy, r, {fill:on ? PR : IC}); K.icon(C, cx - r * 0.42, cy - r * 0.42, r * 0.84, t, on ? '#FFFFFF' : NV);
    K.hd(C, cx - tw / 2 + 4, cy + r + 22, tw - 8, Z.h - r * 2 - 30, t, F, {tc:on ? '#FFFFFF' : INK, mc:on ? '#DCE5FF' : MU, align:'c'});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  dmd(C, W - 10, 10, 180, {fill:PR}); dmd(C, W - 10, H - 10, 150, {fill:SL});
  if (photo) diaPhoto(C, 900, 380, 230); else { cluster(C, 920, 360, 170, 0); dmd(C, 920, 360, 110, {line:'#FFFFFF', lw:3, lop:0.5}); }
  if (f.company) { var cw = Math.min(380, f.company.length * 11 + 40); C.rect(72, 66, cw, 34, {fill:NV}); C.text(72, 66, cw, 34, f.company, {font:'head', size:14, wt:700, color:'#FFFFFF', align:'c', va:'m', caps:true, ls:0.1, lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  C.text(72, 170, 600, 250, C.s.title, {font:'head', fit:{max:72, min:30}, wt:700, color:NV, lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  if (C.s.subtitle) C.text(72, 440, 540, 80, C.s.subtitle, {font:'body', fit:{max:19, min:13}, wt:500, color:NV, caps:false, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 580, 560, 24, who, {font:'body', size:15, wt:700, color:SL, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.website) C.text(72, 626, 400, 18, f.website, {font:'body', size:13, color:MU, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  dmd(C, 10, 10, 120, {fill:NV}); dmd(C, W - 10, H - 10, 160, {fill:PR});
  if (photo) diaPhoto(C, 920, 340, 210); else cluster(C, 930, 340, 150, 1);
  C.text(72, 130, 620, 210, C.s.title || 'Thank You', {font:'head', fit:{max:96, min:40}, wt:700, color:NV, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 356, 560, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 444 + k * 54; dmd(C, 92, y + 20, 20, {fill:NV}); C.icon(82, y + 10, 20, c.icon, '#FFFFFF'); C.text(128, y, 520, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 660 : W - 280, x = photo ? 72 : 140;
  if (photo) diaPhoto(C, 1000, 360, 190); else { dmd(C, 10, H / 2, 120, {fill:NV}); dmd(C, W - 10, H / 2, 120, {fill:PR}); }
  var qe = C.text(x, 150, w, 330, '“' + (C.s.title || '') + '”', {font:'head', fit:{max:photo ? 40 : 50, min:20}, wt:700, color:NV, align:photo ? 'l' : 'c', va:'m', lh:1.18, ed:{f:'title'}, maxLines:6});
  if (by) C.text(x, 510, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:600, color:PR, align:photo ? 'l' : 'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'rhombus', name:'Rhombus Proposal', tag:'Framed white · diamond clusters',
  desc:'A framed white page with clusters of navy, steel and periwinkle diamonds, diamond-cut photos and square navy panels. For project proposals, marketing plans and strategy decks.',
  fonts:{head:'Lexend', body:'Inter', headW:700, bodyW:400, headLh:1.08, gf:'Lexend:wght@400;500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Diamond cluster', {}), V('v2', 'Diamond photo', {img:'photo'})],
    closing:[V('v1', 'Diamond cluster', {}), V('v2', 'Diamond photo', {img:'photo'})],
    agenda:[V('v1', 'Square panels', pg(CA)), V('n2', 'Diamond rows', pg(R, {cols:2})), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Ice panel', pg(B.statement)), V('v2', 'Panel + diamond photo', pg(B.statement, {img:'right'})), V('v3', 'Diamond photo + panel', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Square panels', pg(CA)), V('n2', 'Rhombus row', pg(rhombusRow)), V('n3', 'Diamond rows', pg(R)), V('v2', 'Panels + diamond photo', pg(CA, {img:'right', cols:1})), V('v3', 'Panels + photo strip', pg(CA, {img:'bottom'}))],
    p3:[V('v1', 'Square panels', pg(CA)), V('n2', 'Rhombus row', pg(rhombusRow)), V('n3', 'Diamond steps', pg(ST)), V('v2', 'Rows + diamond photo', pg(R, {img:'right'})), V('v3', 'Diamond photo + rows', pg(R, {img:'left'})), V('v4', 'Panels + photo strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Rhombus row', pg(rhombusRow)), V('n2', 'Square panels', pg(CA)), V('n3', 'Diamond rows', pg(R)), V('v2', 'Rows + diamond photo', pg(R, {img:'right'}))],
    p56:[V('v1', 'Square panels', pg(CA)), V('n2', 'Rhombus row', pg(rhombusRow)), V('n3', 'Diamond rows', pg(R)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Square panels', pg(CA)), V('n2', 'Diamond rows', pg(R)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Focus figures', pg(figures)), V('n2', 'Outlined numbers', pg(B.stats, {hot:0, center:true})), V('n3', 'Progress rings', {body:'statRings', track:'#DCE5FF'}), V('v2', 'Numbers + diamond photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Diamond steps', pg(ST)), V('n2', 'Rhombus row', pg(rhombusRow)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1})), V('v3', 'Steps + photo strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Centred quote', {}), V('v2', 'Quote + diamond photo', {img:'photo'})]
  }
});
})();

