
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 78 - Pinstripe : private-bank plum, fine mustard pinstripes, serif headlines, ledger rows */
var BG = '#FFFFFF', PL = '#3B1E3F', PL2 = '#55305A', MS = '#D9A92B', BL = '#EFE9EE', INK = '#1E1220', MU = '#62546A';
var ROM = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
var P0 = {bg:BG, text:INK, title:PL, muted:MU, accent:PL, accent2:MS, card:BL, line:'#E2D9E3', onAccent:'#FFFFFF'};
/* all stripes of a band are one shape, so the editor and PowerPoint get a single object */
function stripes(C, x, y, w, h, c, step, op){ var d = []; for (var i = step / 2; i < w; i += step) { d.push(['M', x + i, y]); d.push(['L', x + i, y + h]); } C.path(d, {stroke:c || MS, lw:1.2, lop:op == null ? 0.55 : op}); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  C.rect(0, 0, 18, H, {fill:PL}); stripes(C, 18, 0, 22, H, PL, 7, 0.35);
}
function head(C, A, o){
  var y = 52;
  var te = C.text(A.x, y, Math.min(A.w, 880), 96, C.s.title || '', {font:'head', fit:{max:42, min:22}, wt:600, color:PL, lh:1.08, ed:{f:'title'}, maxLines:2});
  y += te._h + 12;
  C.rect(A.x, y, 80, 2, {fill:MS}); C.rect(A.x, y + 5, 80, 1, {fill:MS});
  y += 18;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:80, tc:INK, mc:MU, ac:PL, markS:44, g:18, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:BL}); stripes(C, x, y, w, 14, PL, 6, 0.5); return {tc:INK, mc:MU, ac:PL}; }
    if (o.hot || (o.stat && k % 2 === 0) || (!o.stat && k % 2 === 1)) { C.rect(x, y, w, h, {fill:PL}); stripes(C, x, y, w, 16, MS, 6, 0.7); return {tc:'#FFFFFF', mc:'#D8C9DA', ac:MS}; }
    C.rect(x, y, w, h, {fill:BL}); stripes(C, x, y, w, 16, PL, 6, 0.45);
    return {tc:INK, mc:MU, ac:PL};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:'#E2D9E3'}); return {tc:INK, mc:MU, ac:PL}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.text(x - 4, y - 6, s + 30, s + 6, ROM[k % 10], {font:'head', size:s * 0.8, wt:600, color:on ? MS : PL, lh:1, va:'m'}); },
  rowMark:function(C, x, y, s, t, k){ C.text(x - 4, y, s + 10, s, ROM[k % 10] + '.', {font:'head', size:s * 0.62, wt:600, color:MS, lh:1, va:'m'}); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:PL, line:MS, lw:2}); C.text(cx - s / 2, cy - s / 2, s, s, ROM[k % 10], {font:'head', size:s * 0.36, wt:600, color:MS, align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 1.5, {fill:MS}); C.rect(x1, y + 2, x2 - x1, 1.5, {fill:MS}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 80, Z.y + 6, W - 160, Z.h - 24, {r:0}); C.rect(80, Z.y, W - 160, 2, {fill:MS}); return; }
    var x = Z.x + (side === 'right' ? 0 : 40), w = Z.w - 40;
    C.rect(x, 0, w, H, {fill:PL}); stripes(C, x, 0, w, H, MS, 9, 0.35);
    D.imgBox(C, x + 30, 60, w - 60, H - 120, {r:0}); C.rect(x + 22, 52, w - 44, H - 104, {line:MS, lw:1.5});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, (C.cover.company || '').toUpperCase(), {font:'body', size:10.5, wt:700, color:MU, ls:0.18, lh:1, va:'m'}); C.text(a.x + a.w - 80, y, 80, 16, ROM[C.i % 10] || pad2(C.i + 1), {font:'head', size:14, wt:600, color:MS, align:'r', va:'m', lh:1}); }
var S = {margin:80, series:[PL, MS, '#8E6A92', '#E9CF86', '#62546A', '#C8B6CB'], header:{kind:'left', top:52}, card:{kind:'solid', fill:BL, r:0}, icon:{kind:'plain', fg:PL}, num:{kind:'big', fg:PL},
  conc:{kind:'line', lineC:MS, label:'In summary', lc:PL, tc:INK, italic:true}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#8E6A92'], [100, '#3B1E3F']]}}};
/* signature: ledger - dotted leaders to a right-aligned figure or keyword */
function ledger(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cl = n > 5 && Z.w > 760 ? 2 : 1, per = Math.ceil(n / cl), gx = 50, cw = (Z.w - gx * (cl - 1)) / cl, rh = Math.min(Z.h / per, 104), tw = cw - 70;
  var F = K.fitHD(C, it, tw - 10, 34, rh - 14, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15 : 19, hMin:12, dMin:10, noBoost:n > 4});
  it.forEach(function(t, k){ var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh;
    C.text(x, y + 6, 54, 32, ROM[k % 10], {font:'head', size:22, wt:600, color:MS, lh:1, va:'m'});
    K.hd(C, x + 62, y + 8, tw, rh - 14, t, F, {tc:INK, mc:MU});
    C.line(x, y + rh - 2, x + cw, y + rh - 2, {color:'#CDBFD0', lw:1, dash:true});
  });
}
/* signature: annual-report figures - big serif numbers with mustard double rules */
function figures(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 36;
  var vs = C.groupFit(vals, tw, Z.h * 0.3, {font:'head', max:72, min:24, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'body', max:17, min:12, maxLines:2});
  st.forEach(function(t, k){ var x = Z.x + k * cw + (k ? 18 : 0); if (k) C.rect(Z.x + k * cw, Z.y, 1, Z.h * 0.8, {fill:'#E2D9E3'});
    var y = Z.y + Z.h * 0.12;
    C.text(x, y, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:600, color:PL, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x, y + vs * 1.1 + 12, 60, 2, {fill:MS}); C.rect(x, y + vs * 1.1 + 17, 60, 1, {fill:MS});
    var le = C.text(x, y + vs * 1.1 + 32, tw, 50, labs[k], {font:'body', size:ls, wt:700, color:INK, lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x, y + vs * 1.1 + 40 + le._h, tw, Z.y + Z.h - (y + vs * 1.1 + 40 + le._h), t.x, {font:'body', size:14, color:MU, lh:1.4, italic:true, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, W, H, {fill:PL});
  if (photo) { D.imgBox(C, 820, 0, 460, H, {r:0}); C.rect(812, 0, 8, H, {fill:MS}); }
  else { stripes(C, 840, 0, 440, H, MS, 14, 0.5); C.rect(832, 0, 4, H, {fill:MS}); }
  if (f.company) C.text(80, 70, 600, 18, f.company.toUpperCase(), {font:'body', size:12, wt:700, color:MS, ls:0.24, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(80, 130, 700, 270, C.s.title, {font:'head', fit:{max:72, min:30}, wt:500, color:'#FFFFFF', lh:1.05, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(80, 424, 120, 2, {fill:MS}); C.rect(80, 430, 120, 1, {fill:MS});
  if (C.s.subtitle) C.text(80, 452, 660, 76, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:'#D8C9DA', lh:1.45, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(80, 590, 660, 22, who, {font:'body', size:14, wt:700, color:'#FFFFFF', ls:0.06, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.date) C.text(80, 626, 400, 18, f.date, {font:'head', size:15, color:MS, lh:1, italic:true});
}
function closing(C, v){
  var o = C.o || {};
  C.rect(0, 0, W, H, {fill:PL});
  C.rect(36, 36, W - 72, H - 72, {line:MS, lw:1.5}); stripes(C, 36, 36, W - 72, 26, MS, 9, 0.6); stripes(C, 36, H - 62, W - 72, 26, MS, 9, 0.6);
  var photo = o.img === 'photo';
  if (photo) D.imgBox(C, 820, 100, 360, 520, {r:0});
  var w = photo ? 680 : W - 200, x = photo ? 100 : 100;
  C.text(x, 130, w, 200, C.s.title || 'Thank you', {font:'head', fit:{max:86, min:34}, wt:500, color:'#FFFFFF', lh:1, align:photo ? 'l' : 'c', va:'b', italic:true, ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(x, 350, w, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#D8C9DA', lh:1.4, align:photo ? 'l' : 'c', ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){ var y = 450 + k * 44; C.text(x, y, w, 30, c.val, {font:'body', fit:{max:15, min:11}, wt:700, color:MS, ls:0.12, align:photo ? 'l' : 'c', va:'m', caps:true, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 680 : W - 260, x = photo ? 80 : 130;
  stripes(C, 0, 0, W, 40, PL, 8, 0.5); stripes(C, 0, H - 40, W, 40, PL, 8, 0.5);
  if (photo) D.imgBox(C, 820, 40, 460, H - 80, {r:0});
  var qe = C.text(x, 150, w, 330, '“' + (C.s.title || '') + '”', {font:'head', fit:{max:46, min:20}, wt:500, color:PL, italic:true, align:photo ? 'l' : 'c', va:'m', lh:1.25, ed:{f:'title'}, maxLines:6});
  C.rect(photo ? x : W / 2 - 40, 506, 80, 2, {fill:MS});
  if (by) C.text(x, 524, w, 24, by.h ? by.h + ', ' + by.d : by.d, {font:'body', size:14, wt:700, color:MU, ls:0.12, caps:true, align:photo ? 'l' : 'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Pinstripe cards', rows:'Numeral rows', steps:'Double-rule steps', stmt:'Blush panel', stats:'Figure cards', photo:'framed photo', track:'#EFE9EE'};
var VS = K4.vset(pg, B, L, {
  cover:[['Plum pinstripe', {}], ['Plum + photo', {img:'photo'}]],
  closing:[['Pinstripe frame', {}], ['Frame + photo', {img:'photo'}]],
  quote:[['Serif quote', {}], ['Quote + photo', {img:'photo'}]],
  agenda:[['Ledger', pg(ledger)]], p56:[['Ledger', pg(ledger)]], p78:[['Ledger', pg(ledger)]], p4:[['Ledger', pg(ledger)]],
  stats:[['Annual-report figures', pg(figures)]]
});
D.register({
  id:'pinstripe', name:'Pinstripe', tag:'Plum · mustard pinstripes',
  desc:'Private-bank elegance: deep plum, fine mustard pinstripes, serif headlines with roman numerals and ledger-style rows. For banking, wealth management, law, luxury and private equity.',
  fonts:{head:'Fraunces', body:'Libre Franklin', headW:600, bodyW:400, headLh:1.08, gf:'Fraunces:wght@500;600|Libre+Franklin:wght@400;500;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

