
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 66 - Hex Clinic : clean white page, deep indigo hexagon rails, hexagon photo frames, indigo pill cards */
var BG = '#FFFFFF', IN = '#22137E', IN2 = '#3A28B8', LV = '#E9E6FA', TX = '#1A1446', MU = '#5B5878';
var P0 = {bg:BG, text:TX, title:IN, muted:MU, accent:IN, accent2:IN2, card:LV, line:'#D7D3F0', onAccent:'#FFFFFF'};
function rail(C, cx, cy, r, op){ K.poly(C, K3.hexF(cx, cy, r), {stroke:IN, lw:Math.max(10, r * 0.13), lop:op == null ? 1 : op}); }
function nav(C){
  var f = C.cover, x = W - 430;
  C.rect(x, 22, 430, 28, {fill:IN});
  C.text(x + 24, 22, 260, 28, (f.company || 'Overview'), {font:'head', size:10.5, wt:700, color:'#FFFFFF', va:'m', caps:true, ls:0.14, lh:1, maxLines:1});
  C.text(x + 290, 22, 70, 28, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:10.5, wt:700, color:'#FFFFFF', va:'m', lh:1});
  for (var k = 0; k < 3; k++) C.rect(W - 46, 29 + k * 6, 18, 2, {fill:'#FFFFFF'});
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  rail(C, k ? W + 30 : -40, H + 10, 130); rail(C, k ? W - 150 : 110, H + 70, 80);
  nav(C);
}
var X = {
  M:72, tc:'#FFFFFF', mc:'#D9D4FF', ac:'#FFFFFF', markS:42,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:LV, r:22}); C.rect(x, y, 8, h, {fill:IN, r:4}); return {tc:TX, mc:MU, ac:IN}; }
    var alt = k % 2 === 1 && !o.stat;
    C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, alt ? IN2 : IN], [100, alt ? '#2A1C9C' : '#170C5E']]}, r:20, shadow:0.12});
    return {tc:'#FFFFFF', mc:'#D9D4FF', ac:o.stat ? '#B9AEFF' : '#FFFFFF'};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : LV, r:h / 2, line:'#D7D3F0', lw:1}); return {tc:TX, mc:MU, ac:IN}; },
  mark:function(C, x, y, s, t, k, cc){ var r = s / 2; K.poly(C, K3.hexF(x + r, y + r, r), {fill:'#FFFFFF', op:0.16}); K.poly(C, K3.hexF(x + r, y + r, r), {stroke:'#FFFFFF', lw:1.6, lop:0.8}); K.icon(C, x + r - s * 0.24, y + r - s * 0.24, s * 0.48, t, '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ var r = s / 2; K.poly(C, K3.hexF(x + r, y + r, r), {fill:IN}); K.icon(C, x + r - s * 0.24, y + r - s * 0.24, s * 0.48, t, '#FFFFFF'); },
  node:function(C, cx, cy, s, k){ K.poly(C, K3.hexF(cx, cy, s / 2), {fill:k % 2 ? IN2 : IN}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.34, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:IN, op:0.25}); },
  head:function(C, A, o){
    var y = 78, w = A.w;
    var te = C.text(A.x, y, w, 96, C.s.title || '', {font:'head', fit:{max:42, min:24}, wt:800, color:IN, caps:true, lh:1.04, ed:{f:'title'}, maxLines:2});
    y += te._h + 10;
    if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(w, 720), 46, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
    return y;
  },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, Z.x, Z.y, Z.w, Z.h, {r:0}); C.rect(0, Z.y - 6, W, 6, {fill:IN}); return; }
    var r = Math.min(Z.w * 0.44, 230), cx = Z.x + Z.w / 2, cy = H * 0.52;
    K3.hexPhoto(C, cx, cy, r, BG, IN, 12);
    rail(C, side === 'right' ? cx - r * 0.7 : cx + r * 0.7, cy + r * 0.86, r * 0.32);
  }
};
var B = K3.make(X);
function foot(C){
  var a = K3.footArea(C), y = H - 36, f = C.cover;
  C.text(a.x, y, a.w * 0.6, 16, f.website || f.company || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'});
}
var S = {margin:72, series:[IN, IN2, '#7B6BEA', '#B9AEFF', '#4B4870', '#D7D3F0'], header:{kind:'left', top:80, tMax:40, tMin:24}, card:{kind:'solid', fill:IN, r:20, tc:'#FFFFFF', mc:'#D9D4FF'}, icon:{kind:'plain', fg:IN}, num:{kind:'big', fg:IN},
  conc:{kind:'pill', fill:IN, op:0.1, label:'Key point', lc:IN, tc:TX}, footer:{fn:foot}, imgFramed:true, imgR:18, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#8D80E8'], [100, '#22137E']]}}};
/* signature: honeycomb trio - photo hexagon with two solid hexagons, points on the right */
function honey(C, o){
  var cx = 270, cy = 380;
  K3.hexPhoto(C, cx, cy - 40, 150, BG, IN, 10);
  K.poly(C, K3.hexF(cx + 120, cy + 170, 86), {fill:IN}); K.poly(C, K3.hexF(cx - 120, cy + 170, 70), {stroke:IN, lw:8});
  C.icon(cx + 120 - 24, cy + 170 - 24, 48, D.iconFor(C.s, 0, C.s.title || ''), '#FFFFFF');
  var A = {x:520, w:W - 520 - 72};
  var y = X.head(C, A, o) + 22, bot = H - 70, concH = C.s.conclusion ? 52 : 0;
  var it = C.it.slice(0, 6), n = it.length, rh = (bot - concH - (concH ? 16 : 0) - y) / Math.max(1, n), tw = A.w - 40;
  var F = K.fitHD(C, it, tw, 30, rh - 10, {hMax:19, dMax:14.5, hMin:12, dMin:10, noBoost:n > 3});
  it.forEach(function(t, k){ var yy = y + k * rh, bh = K3.bH(C, t, tw, F), ty = yy + Math.max(4, (rh - bh) / 2); C.ell(A.x, ty + 5, 14, 14, {fill:IN}); K.hd(C, A.x + 32, ty, tw, rh - 6, t, F, {tc:TX, mc:MU}); });
  if (concH) D.conclusion(C, A.x, bot - concH, A.w, concH);
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  rail(C, W - 60, -20, 300, 1); rail(C, W - 330, H + 40, 160);
  if (photo) K3.hexPhoto(C, 930, 360, 250, BG, IN, 16); else { K.poly(C, K3.hexF(930, 360, 250), {fill:IN}); K.poly(C, K3.hexF(930, 360, 190), {stroke:'#FFFFFF', lw:3, lop:0.4}); K.poly(C, K3.hexF(930, 360, 120), {fill:'#FFFFFF', op:0.12}); }
  if (f.company) { K.poly(C, K3.hexF(84, 74, 12), {fill:IN}); C.text(106, 62, 400, 24, f.company, {font:'head', size:14, wt:700, color:IN, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  C.text(72, 170, 600, 230, C.s.title, {font:'head', fit:{max:66, min:30}, wt:800, color:IN, caps:true, lh:1.02, va:'b', ed:{f:'title'}, maxLines:4});
  if (C.s.subtitle) C.text(72, 414, 560, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, wt:600, color:TX, lh:1.38, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { C.text(72, 540, 120, 16, 'Presented by', {font:'body', size:11.5, color:MU, lh:1}); C.text(72, 560, 560, 24, who, {font:'head', size:15, wt:700, color:TX, lh:1, va:'m', ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(72, 640, 400, 18, f.website, {font:'body', size:12.5, wt:600, color:IN, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  rail(C, -40, -40, 220); rail(C, W - 80, H + 20, 200);
  if (photo) K3.hexPhoto(C, 960, 330, 220, BG, IN, 14); else K.poly(C, K3.hexF(960, 330, 220), {fill:IN});
  C.text(72, 120, 620, 220, C.s.title || 'Thank You', {font:'head', fit:{max:96, min:40}, wt:800, color:IN, caps:true, lh:0.98, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 352, 560, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 440 + k * 54; K.poly(C, K3.hexF(94, y + 20, 20), {fill:IN}); C.icon(84, y + 10, 20, c.icon, '#FFFFFF'); C.text(130, y, 520, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:TX, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 640 : W - 144;
  if (photo) K3.hexPhoto(C, 1010, 380, 200, BG, IN, 12);
  K.poly(C, K3.hexF(118, 170, 46), {fill:IN}); C.text(72, 124, 92, 92, '“', {font:'head', size:80, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  var qe = C.text(72, 250, w, 280, C.s.title, {font:'head', fit:{max:photo ? 38 : 46, min:20}, wt:700, color:TX, lh:1.18, ed:{f:'title'}, maxLines:6});
  if (by) C.text(72, 250 + qe._h + 26, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:IN, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'hexclinic', name:'Hex Clinic', tag:'White · indigo hexagons',
  desc:'Crisp white pages with deep indigo hexagon rails, hexagon photo frames and indigo pill cards. For healthcare, labs, clinics, pharma and wellness decks.',
  fonts:{head:'Montserrat', body:'Nunito Sans', headW:800, bodyW:400, headLh:1.05, gf:'Montserrat:wght@600;700;800|Nunito+Sans:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Indigo hexagon', {}), V('v2', 'Hexagon photo', {img:'photo'})],
    closing:[V('v1', 'Indigo hexagon', {}), V('v2', 'Hexagon photo', {img:'photo'})],
    agenda:[V('v1', 'Hex list rows', pg(R, {cols:1})), V('n2', 'Indigo cards', pg(CA)), V('v2', 'Rows + hex photo right', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Statement panel', pg(B.statement)), V('v2', 'Statement + hex photo right', pg(B.statement, {img:'right'})), V('v3', 'Hex photo left + statement', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Indigo cards', pg(CA)), V('n2', 'Hex list rows', pg(R)), V('v2', 'Honeycomb + points', {fn:honey, img:'left'}), V('v3', 'Cards + hex photo right', pg(CA, {img:'right', cols:1})), V('v4', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p3:[V('v1', 'Indigo cards', pg(CA)), V('n2', 'Hex steps', pg(ST)), V('n3', 'Hex list rows', pg(R)), V('v2', 'Honeycomb + points', {fn:honey, img:'left'}), V('v3', 'Rows + hex photo right', pg(R, {img:'right'})), V('v4', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Indigo cards', pg(CA)), V('n2', 'Hex list rows', pg(R)), V('n3', 'Hex steps', pg(ST)), V('v2', 'Honeycomb + points', {fn:honey, img:'left'}), V('v3', 'Rows + hex photo right', pg(R, {img:'right'}))],
    p56:[V('v1', 'Indigo cards', pg(CA)), V('n2', 'Hex list rows', pg(R)), V('v2', 'Honeycomb + points', {fn:honey, img:'left'}), V('v3', 'Rows + hex photo right', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Indigo cards', pg(CA)), V('n2', 'Hex list rows', pg(R)), V('v2', 'Rows + hex photo right', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Indigo number cards', pg(B.stats)), V('n2', 'Progress rings', {body:'statRings', track:'#E9E6FA'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + hex photo right', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Hex steps', pg(ST)), V('n2', 'Hex list rows', pg(R)), V('v2', 'Rows + hex photo right', pg(R, {img:'right', cols:1})), V('v3', 'Steps + photo strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Hex quote', {}), V('v2', 'Quote + hex photo', {img:'photo'})]
  }
});
})();

