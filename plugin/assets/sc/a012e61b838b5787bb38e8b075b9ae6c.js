
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 73 - Tide Report : white page, layered blue swoosh waves, a "page x of y" tab, dot grids, wave-cut photos */
var BG = '#FFFFFF', NV = '#14327A', BL = '#2557D6', SK = '#6E93F0', LT = '#E3EBFC', INK = '#14213D', MU = '#56607A';
var P0 = {bg:BG, text:INK, title:BL, muted:MU, accent:BL, accent2:NV, card:LT, line:'#D3DEF7', onAccent:'#FFFFFF'};
function grid(C, x, y, c){ for (var r = 0; r < 4; r++) for (var q = 0; q < 4; q++) C.ell(x + q * 12, y + r * 12, 4, 4, {fill:c || SK}); }
/* swoosh band across a corner: region under a smooth curve */
function swoosh(C, x0, y0, x1, y1, bend, depth, fill, op){
  var mx = (x0 + x1) / 2, my = (y0 + y1) / 2 - bend;
  C.path([['M', x0, y0], ['C', x0 + (mx - x0) * 0.6, y0 - bend, mx + (x1 - mx) * 0.4, my, x1, y1], ['L', x1, y1 + depth], ['C', mx + (x1 - mx) * 0.4, my + depth, x0 + (mx - x0) * 0.6, y0 - bend + depth, x0, y0 + depth], ['Z']], {fill:fill, op:op});
}
function tab(C){
  var t = 'Page ' + pad2(C.i + 1) + ' of ' + pad2(C.total);
  K.poly(C, [[0, 0], [250, 0], [222, 34], [0, 34]], {fill:NV});
  C.text(72, 0, 160, 34, t, {font:'head', size:11, wt:800, color:'#FFFFFF', caps:true, ls:0.18, va:'m', lh:1, maxLines:1});
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  if (k) { swoosh(C, W - 420, 0, W, 150, -40, 26, BL, 1); swoosh(C, W - 360, -10, W, 110, -30, 14, SK, 0.7); grid(C, W - 120, 170); }
  else { swoosh(C, 820, H, W, H - 220, 60, 30, BL, 1); swoosh(C, 900, H + 10, W, H - 160, 50, 16, SK, 0.7); grid(C, W - 110, 60); }
  tab(C);
}
function head(C, A, o){
  var y = 66;
  var te = C.text(A.x, y, A.w, 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:700, color:BL, lh:1.08, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
/* photo filling a side, its inner edge cut by a blue wave */
function wavePhoto(C, Z, side){
  D.imgBox(C, Z.x, Z.y, Z.w, Z.h, {r:0});
  var e = side === 'right' ? Z.x : Z.x + Z.w, d = side === 'right' ? 1 : -1, b = 90;
  C.path([['M', e - d * 2, Z.y - 2], ['C', e + d * b, Z.y + Z.h * 0.3, e - d * 20, Z.y + Z.h * 0.6, e + d * b * 0.8, Z.y + Z.h + 2], ['L', e - d * 2, Z.y + Z.h + 2], ['Z']], {fill:BG});
  C.path([['M', e + d * 10, Z.y - 2], ['C', e + d * (b + 14), Z.y + Z.h * 0.3, e - d * 6, Z.y + Z.h * 0.6, e + d * (b * 0.8 + 14), Z.y + Z.h + 2], ['L', e + d * (b * 0.8), Z.y + Z.h + 2], ['C', e - d * 20, Z.y + Z.h * 0.6, e + d * b, Z.y + Z.h * 0.3, e - d * 2, Z.y - 2], ['Z']], {fill:BL});
}
function circlePhoto(C, cx, cy, r){ C.ell(cx - r - 12, cy - r - 12, r * 2 + 24, r * 2 + 24, {fill:LT}); C.ell(cx - r - 5, cy - r - 5, r * 2 + 10, r * 2 + 10, {fill:BL}); D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'}); }
var X = {
  M:72, tc:INK, mc:MU, ac:BL, markS:44, g:18, head:head, rg:12,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:LT, r:18}); grid(C, x + w - 70, y + 22, '#B9CBF6'); return {tc:INK, mc:MU, ac:BL}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:BL, r:16}); return {tc:'#FFFFFF', mc:'#DCE6FD', ac:'#FFFFFF'}; }
    C.rect(x, y, w, h, {fill:'#FFFFFF', r:16, line:'#D3DEF7', lw:1.4, shadow:0.06});
    return {tc:INK, mc:MU, ac:BL};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : '#F3F6FE', r:12}); return {tc:INK, mc:MU, ac:BL}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:on ? '#FFFFFF' : LT}); C.ell(x + 4, y + 4, s - 8, s - 8, {fill:on ? '#FFFFFF' : BL}); K.icon(C, x + s * 0.27, y + s * 0.27, s * 0.46, t, on ? BL : '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ C.rect(x, y, s, s, {fill:k % 2 ? NV : BL, r:6}); C.text(x, y, s, s, String(k + 1), {font:'head', size:s * 0.44, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? NV : BL}); C.icon(cx - s * 0.22, cy - s * 0.22, s * 0.44, 'mdi:check-bold', '#FFFFFF'); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 2, x2 - x1, 4, {fill:LT}); },
  stepCard:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : '#F3F6FE', r:12, line:'#D3DEF7', lw:1}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 0, Z.y, W, Z.h, {r:0}); swoosh(C, 0, Z.y - 10, W, Z.y - 10, -20, 22, BG, 1); swoosh(C, 0, Z.y + 8, W, Z.y + 8, -20, 6, BL, 1); return; }
    wavePhoto(C, {x:Z.x, y:0, w:Z.w, h:H}, side);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 500, 16, C.cover.company || '', {font:'body', size:11.5, wt:700, color:NV, lh:1, va:'m'}); }
var S = {margin:72, series:[BL, NV, SK, '#A9C0F6', LT, '#56607A'], header:{kind:'left', top:66}, card:{kind:'outline', r:16}, icon:{kind:'plain', fg:BL}, num:{kind:'big', fg:BL},
  conc:{kind:'pill', fill:BL, op:0.1, label:'Key message', lc:NV, tc:INK}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#A9C0F6'], [100, '#14327A']]}}};
/* signature: phased circles - three overlapping blue circles carry the points */
function phases(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 700) return B.cards(C, Z, o);
  var r = Math.min(Z.h / 2 - 6, Z.w / (2 + (n - 1) * 1.62), 175), step = r * 1.62, x0 = Z.x + (Z.w - (r * 2 + step * (n - 1))) / 2, cy = Z.y + Z.h / 2, tw = r * 1.25;
  var F = K.fitHD(C, it, tw, 50, r * 1.05, {hMax:22, dMax:16.5, hMin:12, dMin:9.5, noBoost:true});
  var fills = [NV, BL, SK, '#3F6FE0'];
  it.forEach(function(t, k){ var cx = x0 + r + k * step; C.ell(cx - r, cy - r, r * 2, r * 2, {fill:fills[k % 4], op:0.94, line:'#FFFFFF', lw:4}); });
  it.forEach(function(t, k){ var cx = x0 + r + k * step, bh = K3.bH(C, t, tw, F); C.text(cx - 40, cy - bh / 2 - 34, 80, 24, 'Phase ' + (k + 1), {font:'head', size:12, wt:800, color:'#FFFFFF', caps:true, ls:0.12, align:'c', va:'m', lh:1}); K.hd(C, cx - tw / 2, cy - bh / 2, tw, r * 1.2, t, F, {tc:'#FFFFFF', mc:'#E3EBFC', align:'c'}); });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  swoosh(C, -40, H - 60, 760, H - 30, 80, 70, BL, 1); swoosh(C, -40, H - 30, 820, H, 70, 26, SK, 0.75);
  swoosh(C, 700, 0, W + 20, 280, -60, 40, LT, 1);
  if (photo) circlePhoto(C, 940, 330, 210); else { C.ell(730, 120, 420, 420, {fill:LT}); C.ell(790, 180, 300, 300, {fill:BL}); C.icon(880, 270, 120, 'mdi:earth', '#FFFFFF'); }
  grid(C, 1150, 70);
  if (f.company) { C.ell(72, 60, 24, 24, {line:BL, lw:3}); C.text(106, 60, 400, 24, f.company, {font:'head', size:14, wt:700, color:INK, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  C.text(72, 150, 620, 220, C.s.title, {font:'head', fit:{max:64, min:28}, wt:800, color:BL, caps:true, lh:1.02, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(72, 384, 560, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, wt:700, color:INK, caps:true, ls:0.04, lh:1.35, ed:{f:'subtitle'}, maxLines:2});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { C.text(72, 478, 300, 16, 'Prepared by', {font:'body', size:12, wt:700, color:BL, lh:1}); C.text(72, 498, 560, 22, who, {font:'body', size:14.5, wt:600, color:INK, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  swoosh(C, 600, H + 20, W + 20, H - 260, 80, 60, BL, 1); swoosh(C, 680, H + 30, W + 20, H - 180, 60, 22, SK, 0.75);
  if (photo) circlePhoto(C, 1010, 250, 170); else { C.ell(860, 90, 300, 300, {fill:LT}); C.icon(950, 180, 120, 'mdi:hand-heart-outline', BL); }
  grid(C, 72, 70);
  C.text(72, 140, 680, 200, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:BL, caps:true, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 356, 600, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  C.rect(72, 430, 210, 28, {fill:BL, r:4}); C.text(72, 430, 210, 28, 'For more information', {font:'head', size:11, wt:800, color:'#FFFFFF', caps:true, ls:0.08, align:'c', va:'m', lh:1});
  D.contacts(C).forEach(function(c, k){ var y = 476 + k * 46; C.icon(72, y + 8, 20, c.icon, BL); C.text(104, y, 520, 36, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 640 : W - 144;
  if (photo) circlePhoto(C, 1000, 380, 170);
  C.text(60, 110, 120, 120, '“', {font:'head', size:150, wt:800, color:BL, lh:1, caps:false});
  var qe = C.text(72, 240, w, 280, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:600, color:INK, lh:1.22, ed:{f:'title'}, maxLines:6});
  if (by) C.text(72, 240 + qe._h + 26, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:BL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'tide', name:'Tide Report', tag:'White · blue swoosh waves',
  desc:'White pages swept by layered blue swoosh waves, a "page x of y" tab, dot grids, wave-cut and round photos and overlapping phase circles. For sustainability, climate, NGO, research and policy reports.',
  fonts:{head:'Plus Jakarta Sans', body:'Work Sans', headW:800, bodyW:400, headLh:1.08, gf:'Plus+Jakarta+Sans:wght@500;600;700;800|Work+Sans:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Wave + globe circle', {}), V('v2', 'Wave + round photo', {img:'photo'})],
    closing:[V('v1', 'Wave sweep', {}), V('v2', 'Wave + round photo', {img:'photo'})],
    agenda:[V('v1', 'Numbered rows', pg(R, {cols:2})), V('n2', 'Outlined cards', pg(CA)), V('v2', 'Rows + wave photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Dotted panel', pg(B.statement)), V('v2', 'Panel + wave photo', pg(B.statement, {img:'right'})), V('v3', 'Wave photo + panel', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Outlined cards', pg(CA, {hl:true})), V('n2', 'Phase circles', pg(phases)), V('n3', 'Numbered rows', pg(R)), V('v2', 'Rows + wave photo', pg(R, {img:'right'})), V('v3', 'Wave photo + rows', pg(R, {img:'left'}))],
    p3:[V('v1', 'Phase circles', pg(phases)), V('n2', 'Outlined cards', pg(CA, {hl:true, center:true})), V('n3', 'Check steps', pg(ST)), V('v2', 'Rows + wave photo', pg(R, {img:'right'})), V('v3', 'Wave photo + rows', pg(R, {img:'left'})), V('v4', 'Cards + wave strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Phase circles', pg(phases)), V('n2', 'Outlined cards', pg(CA)), V('n3', 'Numbered rows', pg(R, {cols:2})), V('v2', 'Rows + wave photo', pg(R, {img:'right'}))],
    p56:[V('v1', 'Outlined cards', pg(CA)), V('n2', 'Numbered rows', pg(R)), V('v2', 'Rows + wave photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Outlined cards', pg(CA)), V('n2', 'Numbered rows', pg(R)), V('v2', 'Rows + wave photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Outlined numbers', pg(B.stats, {hot:0})), V('n2', 'Progress rings', {body:'statRings', track:'#E3EBFC'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + wave photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Check steps', pg(ST)), V('n2', 'Phase circles', pg(phases)), V('v2', 'Rows + wave photo', pg(R, {img:'right', cols:1})), V('v3', 'Steps + wave strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Big mark quote', {}), V('v2', 'Quote + round photo', {img:'photo'})]
  }
});
})();

