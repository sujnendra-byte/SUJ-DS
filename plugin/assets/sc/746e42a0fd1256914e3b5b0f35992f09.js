
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 69 - Finance Prism : pale page, deep-blue angled wedges, slanted photo panels, blue pill rows with check badges */
var BG = '#F4F8FD', BL = '#0A3F8C', DP = '#062A5E', SK = '#2F7DE1', PL = '#E3EEFB', INK = '#0E1E36', MU = '#4A5B75';
var P0 = {bg:BG, text:INK, title:DP, muted:MU, accent:BL, accent2:SK, card:'#FFFFFF', line:'#C9DAF0', onAccent:'#FFFFFF'};
function dots(C, x, y, c){ for (var r = 0; r < 3; r++) for (var q = 0; q < 3; q++) C.rect(x + q * 8, y + r * 8, 4, 4, {fill:c || DP}); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  if (k) { K.poly(C, [[W - 300, 0], [W, 0], [W, 120], [W - 200, 120]], {fill:DP}); K.poly(C, [[W - 330, 0], [W - 306, 0], [W - 206, 120], [W - 230, 120]], {fill:SK}); }
  else { K.poly(C, [[W - 170, 0], [W, 0], [W, 230]], {fill:DP}); K.poly(C, [[W - 210, 0], [W - 186, 0], [W, 260], [W, 292]], {fill:SK}); }
  C.rect(0, H - 34, W, 34, {fill:DP});
}
function head(C, A, o){
  var y = 44, tw = Math.min(A.w, 780);
  dots(C, A.x, y, SK); y += 32;
  var te = C.text(A.x, y, tw, 92, C.s.title || '', {font:'head', fit:{max:38, min:22}, wt:700, color:DP, lh:1.1, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, tw, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
/* photo cut on a slant, edged with a deep-blue stripe */
function slantPhoto(C, Z, side){
  D.imgBox(C, Z.x, Z.y, Z.w, Z.h, {r:0});
  var s = 110;
  if (side === 'right') { K.poly(C, [[Z.x - 2, Z.y - 2], [Z.x + s, Z.y - 2], [Z.x - 2, Z.y + Z.h + 2]], {fill:BG}); K.poly(C, [[Z.x + s, Z.y], [Z.x + s + 22, Z.y], [Z.x + 20, Z.y + Z.h], [Z.x - 2, Z.y + Z.h]], {fill:BL}); }
  else { var r = Z.x + Z.w; K.poly(C, [[r + 2, Z.y - 2], [r + 2, Z.y + Z.h + 2], [r - s, Z.y + Z.h + 2]], {fill:BG}); K.poly(C, [[r + 2, Z.y], [r - 20, Z.y], [r - s - 22, Z.y + Z.h], [r - s, Z.y + Z.h]], {fill:BL}); }
}
var X = {
  M:72, tc:INK, mc:MU, ac:BL, markS:42, g:18, head:head, footH:66, rg:10,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:'#FFFFFF', r:10, shadow:0.08}); C.rect(x, y, 6, h, {fill:BL}); return {tc:INK, mc:MU, ac:BL}; }
    if (o.hot || (o.stat && k % 2)) { C.rect(x, y, w, h, {fill:k % 2 ? BL : DP, r:12}); return {tc:'#FFFFFF', mc:'#CFE0F7', ac:'#9CC6FF'}; }
    C.rect(x, y, w, h, {fill:'#FFFFFF', r:12, shadow:0.08}); C.rect(x, y + h - 5, w, 5, {fill:SK});
    return {tc:INK, mc:MU, ac:BL};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? SK : BL, r:h / 2}); return {tc:'#FFFFFF', mc:'#DCEBFF', ac:'#FFFFFF'}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.rect(x, y, s, s, {fill:on ? '#FFFFFF' : BL, r:10}); K.icon(C, x + s * 0.24, y + s * 0.24, s * 0.52, t, on ? BL : '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {fill:'#FFFFFF'}); C.icon(x + s * 0.22, y + s * 0.22, s * 0.56, 'mdi:check-bold', k % 2 ? SK : BL); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:'#FFFFFF', line:BL, lw:4}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.34, wt:700, color:BL, align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:BL, r:3}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 0, Z.y, W, Z.h - 34, {r:0}); K.poly(C, [[0, Z.y - 2], [W, Z.y - 2], [W, Z.y + 30]], {fill:BG}); return; }
    slantPhoto(C, {x:Z.x, y:0, w:Z.w, h:H - 34}, side);
  }
};
var B = K3.make(X);
function foot(C){ var f = C.cover, y = H - 34; C.text(72, y, 600, 34, [f.company, f.website].filter(Boolean).join('   |   '), {font:'body', size:11.5, wt:600, color:'#FFFFFF', lh:1, va:'m', maxLines:1}); C.text(W - 172, y, 100, 34, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:12, wt:700, color:'#FFFFFF', align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[BL, SK, DP, '#7FB2F0', '#C9DAF0', '#4A5B75'], header:{kind:'left', top:56}, card:{kind:'solid', fill:'#FFFFFF', r:12}, icon:{kind:'square', bg:BL, fg:'#FFFFFF', r:10}, num:{kind:'big', fg:BL},
  conc:{kind:'band', fill:DP, r:26, label:'Bottom line', tc:'#FFFFFF', lc:'#9CC6FF'}, footer:{fn:foot}, footerH:70, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#7FB2F0'], [100, '#062A5E']]}}};
/* signature: pillar table - a deep-blue label pill joined to a sky-blue description pill on each row */
function pillars(C, Z, o){
  var it = C.it.slice(0, 7), n = it.length; if (!n) return;
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 84), lw = Math.min(300, Z.w * 0.32), dw = Z.w - lw - 14, tot = rh * n + g * (n - 1), y0 = Z.y + (Z.h - tot) / 2;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), lw - 40, rh - 12, {font:'head', max:19, min:11, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw - 48, rh - 10, {font:'body', max:17, min:10, lh:1.3, maxLines:3});
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    C.rect(Z.x, y, lw, rh, {fill:DP, r:rh / 2});
    C.text(Z.x + 22, y, lw - 40, rh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(Z.x + lw + 14, y, dw, rh, {fill:k % 2 ? '#3E8AEB' : SK, r:rh / 2});
    C.text(Z.x + lw + 38, y, dw - 48, rh, t.d, {font:'body', size:ds, wt:500, color:'#FFFFFF', va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}, maxLines:3});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) slantPhoto(C, {x:620, y:0, w:W - 620, h:H}, 'right');
  else { K.poly(C, [[700, 0], [W, 0], [W, H], [520, H]], {fill:DP}); K.poly(C, [[880, 0], [W, 0], [W, H], [760, H]], {fill:BL}); K.poly(C, [[672, 0], [700, 0], [520, H], [492, H]], {fill:SK}); for (var i = 0; i < 6; i++) C.rect(860 + i * 54, 440 - i * 46, 34, 160 + i * 46, {fill:'#FFFFFF', op:0.12 + i * 0.04}); }
  if (f.company) { C.ell(72, 60, 26, 26, {fill:BL}); C.text(108, 60, 400, 26, f.company, {font:'head', size:14, wt:600, color:DP, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  dots(C, 72, 150, BL);
  C.text(72, 180, 500, 230, C.s.title, {font:'head', fit:{max:60, min:28}, wt:700, color:DP, lh:1.06, va:'b', ed:{f:'title'}, maxLines:4});
  if (C.s.subtitle) C.text(72, 424, 460, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  if (f.date) { var dw = Math.min(300, f.date.length * 10 + 70); C.rect(72, 512, dw, 36, {fill:BL, r:18}); C.text(72, 512, dw, 36, f.date, {font:'head', size:14, wt:600, color:'#FFFFFF', align:'c', va:'m', lh:1, maxLines:1}); }
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 580, 440, 24, who, {font:'head', size:15, wt:600, color:INK, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  C.rect(0, H - 34, photo ? 620 : 500, 34, {fill:DP});
  if (f.website) C.text(72, H - 34, 400, 34, f.website, {font:'body', size:12, color:'#FFFFFF', va:'m', lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  if (photo) slantPhoto(C, {x:0, y:0, w:520, h:H}, 'left');
  else { K.poly(C, [[0, 0], [560, 0], [400, H], [0, H]], {fill:DP}); K.poly(C, [[560, 0], [590, 0], [430, H], [400, H]], {fill:SK}); C.icon(150, 260, 180, 'mdi:chart-line', '#FFFFFF'); }
  var x = 640;
  C.text(x, 150, W - x - 72, 190, C.s.title || 'Thank You', {font:'head', fit:{max:72, min:32}, wt:700, color:DP, lh:1.05, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(x, 356, W - x - 72, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 440 + k * 52; C.rect(x, y, W - x - 72, 42, {fill:k % 2 ? SK : BL, r:21}); C.ell(x + 4, y + 4, 34, 34, {fill:'#FFFFFF'}); C.icon(x + 12, y + 12, 18, c.icon, BL); C.text(x + 52, y, W - x - 140, 42, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 620 : W - 144;
  if (photo) slantPhoto(C, {x:760, y:0, w:W - 760, h:H}, 'right');
  C.rect(72, 140, 12, 300, {fill:BL}); dots(C, 100, 140, SK);
  var qe = C.text(110, 190, w - 40, 300, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:600, color:DP, lh:1.2, ed:{f:'title'}, maxLines:6});
  if (by) C.text(110, 190 + qe._h + 26, w - 40, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:SK, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'finprism', name:'Finance Prism', tag:'Pale blue · angled wedges',
  desc:'Pale blue pages with deep-blue angled wedges, photos cut on a slant, blue pill rows with check badges and a dark footer band. For finance, investment, banking and business strategy decks.',
  fonts:{head:'Poppins', body:'Open Sans', headW:700, bodyW:400, headLh:1.1, gf:'Poppins:wght@500;600;700|Open+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Blue wedges', {}), V('v2', 'Slanted photo', {img:'photo'})],
    closing:[V('v1', 'Blue wedge', {}), V('v2', 'Slanted photo', {img:'photo'})],
    agenda:[V('v1', 'Check pills', pg(R, {cols:1})), V('n2', 'Ruled cards', pg(CA)), V('v2', 'Pills + slanted photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'White panel', pg(B.statement)), V('v2', 'Panel + slanted photo', pg(B.statement, {img:'right'})), V('v3', 'Slanted photo + panel', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Ruled cards', pg(CA, {hl:true})), V('n2', 'Pillar table', pg(pillars)), V('n3', 'Check pills', pg(R)), V('v2', 'Pills + slanted photo', pg(R, {img:'right'})), V('v3', 'Slanted photo + pills', pg(R, {img:'left'}))],
    p3:[V('v1', 'Ruled cards', pg(CA)), V('n2', 'Pillar table', pg(pillars)), V('n3', 'Check pills', pg(R)), V('n4', 'Milestone line', pg(ST)), V('v2', 'Pills + slanted photo', pg(R, {img:'right'})), V('v3', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Pillar table', pg(pillars)), V('n2', 'Ruled cards', pg(CA)), V('n3', 'Check pills', pg(R)), V('v2', 'Pills + slanted photo', pg(R, {img:'right'})), V('v3', 'Slanted photo + pills', pg(R, {img:'left'}))],
    p56:[V('v1', 'Pillar table', pg(pillars)), V('n2', 'Ruled cards', pg(CA)), V('n3', 'Check pills', pg(R)), V('v2', 'Pills + slanted photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Pillar table', pg(pillars)), V('n2', 'Ruled cards', pg(CA)), V('v2', 'Pills + slanted photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Blue number cards', pg(B.stats)), V('n2', 'Progress bars', {body:'statBars'}), V('n3', 'Progress rings', {body:'statRings', track:'#E3EEFB'}), V('v2', 'Numbers + slanted photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Milestone line', pg(ST)), V('n2', 'Pillar table', pg(pillars)), V('v2', 'Pills + slanted photo', pg(R, {img:'right', cols:1})), V('v3', 'Line + photo strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Bar quote', {}), V('v2', 'Quote + slanted photo', {img:'photo'})]
  }
});
})();

