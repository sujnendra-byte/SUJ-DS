
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 88 - Swiss Index : white page, visible 12-column hairline grid, oversized index numerals, one red signal dot */
var BG = '#FFFFFF', INK = '#111111', RD = '#E8361E', GL = '#E4E4E4', MU = '#5C5C5C', PA = '#F4F4F4';
var M = 64, COLW = (W - 2 * M) / 12;
var P0 = {bg:BG, text:INK, title:INK, muted:MU, accent:RD, accent2:INK, card:PA, line:GL, onAccent:'#FFFFFF'};
function grid(C, c){ var d = []; for (var i = 0; i <= 12; i++) { var x = M + i * COLW; d.push(['M', x, 0], ['L', x, H]); } C.path(d, {stroke:c || GL, lw:0.8}); }
function bg(C, kind){ C.bg({fill:BG}); grid(C); }
function head(C, A, o){
  var y = 48;
  C.text(A.x, y, 120, 16, pad2(C.i + 1) + '.0', {font:'head', size:12, wt:700, color:INK, lh:1, va:'m'});
  C.ell(A.x + A.w - 14, y + 1, 14, 14, {fill:RD});
  y += 30;
  var te = C.text(A.x, y, Math.min(A.w, COLW * 9), 96, C.s.title || '', {font:'head', fit:{max:44, min:22}, wt:700, color:INK, lh:1.04, ed:{f:'title'}, maxLines:2});
  y += te._h + 12;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, COLW * 7), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  C.rect(A.x, y + 14, A.w, 1.5, {fill:INK});
  return y + 14;
}
var X = {
  M:M, tc:INK, mc:MU, ac:INK, markS:40, g:COLW * 0.2, head:head, ptc:INK, pmc:MU, gap:30,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { return {tc:INK, mc:MU, ac:RD}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:INK}); return {tc:'#FFFFFF', mc:'#BDBDBD', ac:RD}; }
    C.rect(x, y, w, 1.5, {fill:INK}); return {tc:INK, mc:MU, ac:INK};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, 1, {fill:INK}); return {tc:INK, mc:MU, ac:INK}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.text(x - 2, y - 8, s * 2.4, s + 12, (C.i + 1) + '.' + (k + 1), {font:'head', size:s * 0.95, wt:700, color:on ? RD : INK, lh:1, va:'m'}); },
  rowMark:function(C, x, y, s, t, k){ C.text(x - 2, y, s + 20, s, pad2(k + 1), {font:'head', size:s * 0.66, wt:700, color:k === 0 ? RD : INK, lh:1, va:'m'}); },
  node:function(C, cx, cy, s, k){ C.line(cx, cy - s * 0.5, cx, cy + s * 0.5, {color:INK, lw:1.5}); C.ell(cx - 7, cy - 7, 14, 14, {fill:k === 0 ? RD : INK}); },
  track:function(C, x1, y, x2){ C.line(M, y, W - M, y, {color:INK, lw:1.5}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, M, Z.y + 10, W - 2 * M, Z.h - 28, {r:0}); return; }
    var cols = Math.round(Z.w / COLW), x = side === 'right' ? W - M - COLW * (cols - 1) : M, w = COLW * (cols - 1) - COLW * 0.2;
    D.imgBox(C, x, 110, w, H - 200, {r:0});
    C.ell(side === 'right' ? x + w - 22 : x + 8, 124, 14, 14, {fill:RD});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.company || '', {font:'head', size:11.5, wt:700, color:INK, lh:1, va:'m'}); C.text(a.x + a.w - 100, y, 100, 16, 'p. ' + pad2(C.i + 1), {font:'head', size:11.5, wt:700, color:INK, align:'r', va:'m', lh:1}); }
var S = {margin:M, series:[INK, RD, '#8C8C8C', '#F29A8B', '#3F3F3F', '#C9C9C9'], header:{kind:'left', top:48}, card:{kind:'rule'}, icon:{kind:'plain', fg:INK}, num:{kind:'big', fg:INK},
  conc:{kind:'line', lineC:INK, label:'Conclusion', lc:RD, tc:INK}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:30, imgPh:{grad:{ang:135, stops:[[0, '#C9C9C9'], [100, '#3F3F3F']]}}};
/* signature: index columns - a giant numeral heads each column on the grid */
function indexCols(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 700) return B.rows(C, Z, o);
  var span = Math.floor(12 / n), cw = COLW * span, nh = Math.min(120, Z.h * 0.36), tw = cw - COLW * 0.4;
  var F = K.fitHD(C, it, tw, 54, Z.h - nh - 40, {hMax:22, dMax:16, hMin:12, dMin:10});
  it.forEach(function(t, k){ var x = M + k * cw;
    C.text(x, Z.y, cw, nh, (C.i + 1) + '.' + (k + 1), {font:'head', size:nh * 0.9, wt:700, color:k === 0 ? RD : INK, lh:1, va:'b'});
    C.rect(x, Z.y + nh + 10, cw - COLW * 0.3, 1.5, {fill:INK});
    K.hd(C, x, Z.y + nh + 26, tw, Z.h - nh - 26, t, F, {tc:INK, mc:MU});
  });
}
/* signature: index list - big number left, text spanning the grid */
function indexList(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cl = n > 5 && Z.w > 760 ? 2 : 1, per = Math.ceil(n / cl), cw = Z.w / cl, rh = Z.h / per, nw = COLW * 1.4, tw = cw - nw - COLW * 0.4;
  var F = K.fitHD(C, it, tw, 34, rh - 12, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12, dMin:10, noBoost:n > 4});
  it.forEach(function(t, k){ var c = Math.floor(k / per), r = k % per, x = Z.x + c * cw, y = Z.y + r * rh, bh = K3.bH(C, t, tw, F);
    C.rect(x, y, cw - COLW * 0.3, 1, {fill:INK});
    C.text(x, y + 6, nw, Math.min(rh - 10, 60), pad2(k + 1), {font:'head', size:Math.min(48, rh * 0.6), wt:700, color:k === 0 ? RD : INK, lh:1, va:'t'});
    K.hd(C, x + nw, y + Math.max(8, (rh - bh) / 2), tw, rh - 10, t, F, {tc:INK, mc:MU});
  });
}
/* signature: giant numerals on the grid */
function numerals(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var span = Math.floor(12 / n), cw = COLW * span, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - COLW * 0.4;
  var vs = C.groupFit(vals, tw, Z.h * 0.42, {font:'head', max:120, min:28, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'body', max:17, min:12, maxLines:2});
  st.forEach(function(t, k){ var x = M + k * cw, y = Z.y + Z.h * 0.08;
    C.text(x, y, tw, vs * 1.05, vals[k], {font:'head', size:vs, wt:700, color:k === 0 ? RD : INK, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x, y + vs * 1.05 + 12, cw - COLW * 0.3, 1.5, {fill:INK});
    var le = C.text(x, y + vs * 1.05 + 26, tw, 50, labs[k], {font:'body', size:ls, wt:700, color:INK, lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x, y + vs * 1.05 + 34 + le._h, tw, Z.y + Z.h - (y + vs * 1.05 + 34 + le._h), t.x, {font:'body', size:14, color:MU, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  if (o.img === 'photo') D.imgBox(C, M + COLW * 7, 0, W - M - COLW * 7, H, {r:0});
  else C.text(M + COLW * 5, 120, COLW * 7 + M + 60, 720, '01', {font:'head', size:640, wt:700, color:INK, lh:0.8, align:'r', va:'b', caps:false, maxLines:1});
  C.ell(M, 64, 18, 18, {fill:RD});
  if (f.company) C.text(M + 30, 62, COLW * 6, 22, f.company, {font:'head', size:14, wt:700, color:INK, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(M, 150, COLW * 6.6, 260, C.s.title, {font:'head', fit:{max:64, min:28}, wt:700, color:INK, lh:1.02, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(M, 430, COLW * 6.6, 1.5, {fill:INK});
  if (C.s.subtitle) C.text(M, 450, COLW * 6, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(M, 600, COLW * 6, 22, who, {font:'head', size:14, wt:700, color:INK, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.date) C.text(M, 632, COLW * 4, 18, f.date, {font:'body', size:13, color:MU, lh:1});
}
function closing(C, v){
  var o = C.o || {};
  if (o.img === 'photo') D.imgBox(C, M + COLW * 7, 0, W - M - COLW * 7, H, {r:0});
  else C.text(M + COLW * 5, 120, COLW * 7 + M + 60, 720, pad2(C.total || 12), {font:'head', size:640, wt:700, color:RD, lh:0.8, align:'r', va:'b', caps:false, maxLines:1});
  C.ell(M, 64, 18, 18, {fill:RD});
  C.text(M, 120, COLW * 6.6, 220, C.s.title || 'Thank you', {font:'head', fit:{max:96, min:36}, wt:700, color:INK, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(M, 356, COLW * 6, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C);
  C.rect(M, 440, COLW * 6.6, 1.5, {fill:INK});
  cs.forEach(function(c, k){ var y = 452 + k * 46; C.text(M, y, COLW, 36, pad2(k + 1), {font:'head', size:14, wt:700, color:k === 0 ? RD : INK, va:'m', lh:1}); C.text(M + COLW, y, COLW * 5.6, 36, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); C.rect(M, y + 42, COLW * 6.6, 1, {fill:GL}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? COLW * 7.4 : COLW * 10;
  if (photo) D.imgBox(C, M + COLW * 8, 0, W - M - COLW * 8, H, {r:0});
  C.ell(M, 120, 18, 18, {fill:RD});
  var qe = C.text(M, 170, w, 360, C.s.title, {font:'head', fit:{max:60, min:24}, wt:700, color:INK, lh:1.1, ed:{f:'title'}, maxLines:6});
  if (by) C.text(M, 190 + qe._h + 20, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:MU, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Ruled columns', rows:'Index rows', steps:'Axis timeline', stmt:'Grid statement', stats:'Ruled numbers', photo:'grid photo', track:'#E4E4E4'};
var VS = K4.vset(pg, B, L, {
  cover:[['Giant index numeral', {}], ['Grid + photo', {img:'photo'}]],
  closing:[['Index contacts', {}], ['Index + photo', {img:'photo'}]],
  quote:[['Grid quote', {}], ['Grid quote + photo', {img:'photo'}]],
  agenda:[['Index list', pg(indexList)]], p2:[['Index columns', pg(indexCols)]], p3:[['Index columns', pg(indexCols)]], p4:[['Index columns', pg(indexCols)]], p56:[['Index list', pg(indexList)]], p78:[['Index list', pg(indexList)]],
  stats:[['Giant numerals', pg(numerals)]]
});
D.register({
  id:'swissindex', name:'Swiss Index', tag:'White · 12-column grid',
  desc:'A strict modular page with a visible 12-column hairline grid, oversized index numerals, heavy rules and one red signal dot. For consulting, strategy, board decks and research summaries.',
  fonts:{head:'Schibsted Grotesk', body:'Atkinson Hyperlegible', headW:700, bodyW:400, headLh:1.04, gf:'Schibsted+Grotesk:wght@500;600;700;800|Atkinson+Hyperlegible:wght@400;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

