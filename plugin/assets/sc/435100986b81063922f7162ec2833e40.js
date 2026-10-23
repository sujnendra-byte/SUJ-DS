
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 67 - Crimson Tile : white page, deep crimson tilted rounded tiles, pale rose squares, red caps headings with a rule */
var BG = '#FFFFFF', RD = '#9B0F14', RD2 = '#C8262C', RS = '#F6D5D5', INK = '#222222', MU = '#5E5E5E';
var P0 = {bg:BG, text:INK, title:RD, muted:MU, accent:RD, accent2:RD2, card:'#FFF5F5', line:'#EBCACA', onAccent:'#FFFFFF'};
function tile(C, cx, cy, s, deg, o){ return K3.rpoly(C, K3.sq(cx, cy, s, deg), s * 0.14, o); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { tile(C, W - 40, H - 30, 220, 30, {fill:RS, op:0.55}); C.rect(W - 150, 0, 150, 22, {fill:RD, r:11}); }
  else if (k === 1) { tile(C, -30, H + 10, 200, 20, {fill:RS, op:0.55}); C.rect(W - 26, 90, 26, 150, {fill:RD, r:13}); }
  else { tile(C, W + 10, 40, 180, 36, {fill:RS, op:0.5}); C.rect(0, H - 22, 210, 22, {fill:RD, r:11}); }
}
function head(C, A, o){
  var y = 56, f = C.cover;
  C.text(A.x, y, A.w, 18, f.company || ('Section ' + pad2(C.i + 1)), {font:'body', size:13, wt:700, color:INK, lh:1, va:'m', maxLines:1});
  y += 24;
  var te = C.text(A.x, y, A.w, 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:RD, caps:true, ls:0.04, lh:1.04, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  C.rect(A.x, y, Math.min(A.w, 300), 2, {fill:INK}); C.rect(A.x, y - 1, 60, 4, {fill:RD});
  y += 14;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
function framed(C, x, y, w, h){
  tile(C, x + w * 0.85, y + h * 0.12, Math.min(w, h) * 0.5, 22, {fill:RS, op:0.8});
  C.rect(x - 8, y - 8, w + 16, h + 16, {fill:RD, r:22});
  D.imgBox(C, x, y, w, h, {r:16});
}
var X = {
  M:72, tc:INK, mc:MU, ac:RD, markS:44, g:20, head:head,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:'#FFF5F5', r:14, line:'#EBCACA', lw:1.2}); tile(C, x + w - 30, y + 30, 90, 24, {fill:RS, op:0.9}); return {tc:INK, mc:MU, ac:RD}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:RD, r:14, shadow:0.12}); return {tc:'#FFFFFF', mc:'#F6D5D5', ac:'#FFFFFF'}; }
    C.rect(x, y, w, h, {fill:'#FFFFFF', r:14, line:'#EBCACA', lw:1.4, shadow:0.06}); C.rect(x + 22, y, 54, 5, {fill:RD, r:2});
    return {tc:INK, mc:MU, ac:RD};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : '#FFF5F5', r:10}); C.rect(x, y + h - 1, w, 1, {fill:'#EBCACA'}); return {tc:INK, mc:MU, ac:RD}; },
  mark:function(C, x, y, s, t, k, cc){ var c = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:c ? '#FFFFFF' : RD}); C.text(x, y, s, s, pad2(k + 1), {font:'head', size:s * 0.38, wt:800, color:c ? RD : '#FFFFFF', align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ tile(C, cx, cy, s * 0.9, 45, {fill:k % 2 ? RD2 : RD}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.32, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 1, x2 - x1, 2, {fill:INK}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, Z.x + 72, Z.y, Z.w - 144, Z.h - 20, {r:16}); C.rect(Z.x + 72, Z.y - 4, 120, 6, {fill:RD, r:3}); return; }
    framed(C, Z.x + (side === 'right' ? 30 : 46), 90, Z.w - 76, H - 190);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 400, 16, C.cover.website || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 80, y, 80, 16, pad2(C.i + 1), {font:'head', size:13, wt:800, color:RD, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[RD, RD2, '#E57373', '#F6D5D5', '#5E5E5E', '#222222'], header:{kind:'left', top:60}, card:{kind:'outline', r:14}, icon:{kind:'plain', fg:RD}, num:{kind:'big', fg:RD},
  conc:{kind:'box', fill:'#FFF5F5', r:10, label:'In short', lc:RD, tc:INK}, footer:{fn:foot}, imgFramed:true, imgR:16, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#E7A3A3'], [100, '#7C0B10']]}}};
/* signature: process box - a red tab label on the left and an outlined box with numbered rows */
function processBox(C, o){
  var y0 = 60;
  C.text(72, y0, 400, 18, C.cover.company || '', {font:'body', size:13, wt:700, color:INK, lh:1, va:'m', maxLines:1});
  var te = C.text(72, y0 + 30, 360, 200, C.s.title || '', {font:'head', fit:{max:44, min:24}, wt:800, color:RD, caps:true, lh:1.04, ed:{f:'title'}, maxLines:4});
  if (C.s.subtitle) C.text(72, y0 + 44 + te._h, 330, 110, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:4});
  C.rect(0, 420, 330, 52, {fill:RD, r:26}); C.text(40, 420, 270, 52, (C.s.title || '').split(' ').slice(0, 3).join(' '), {font:'head', size:17, wt:800, color:'#FFFFFF', va:'m', lh:1, maxLines:1});
  C.line(330, 446, 420, 446, {color:INK, lw:2});
  var bx = 420, by = 70, bw = W - 72 - bx, bh = H - 150;
  C.rect(bx, by, bw, bh, {line:INK, lw:1.5, fill:'#FFFFFF'});
  var Z = {x:bx + 24, y:by + 20, w:bw - 48, h:bh - 40 - (C.s.conclusion ? 64 : 0)};
  X.rowsPlain(C, Z);
  if (C.s.conclusion) D.conclusion(C, bx + 24, by + bh - 70, bw - 48, 52);
}
X.rowsPlain = function(C, Z){
  var it = C.it.slice(0, 7), n = it.length; if (!n) return;
  var rh = Z.h / n, s = Math.min(36, rh * 0.6), tw = Z.w - s - 18;
  var F = K.fitHD(C, it, tw, 30, rh - 10, {hMax:20, dMax:16, hMin:12, dMin:10, noBoost:n > 4});
  it.forEach(function(t, k){ var y = Z.y + k * rh, bh = K3.bH(C, t, tw, F), ty = y + Math.max(4, (rh - bh) / 2); X.mark(C, Z.x, ty, s, t, k, null); K.hd(C, Z.x + s + 18, ty, tw, rh - 6, t, F, {tc:INK, mc:MU}); if (k) C.rect(Z.x + s + 18, y, tw, 1, {fill:'#EBCACA'}); });
};
/* signature: tilted tile holding the slide icon, points beside it */
function tileList(C, o){
  tile(C, 250, 400, 300, 18, {fill:RS, op:0.8}); tile(C, 230, 380, 250, 10, {fill:RD});
  C.icon(230 - 50, 380 - 50, 100, D.iconFor(C.s, 0, C.s.title || ''), '#FFFFFF');
  var A = {x:500, w:W - 572}, y = head(C, A, o) + 22, bot = H - 70 - (C.s.conclusion ? 68 : 0);
  X.rowsPlain(C, {x:A.x, y:y, w:A.w, h:bot - y});
  if (C.s.conclusion) D.conclusion(C, A.x, H - 122, A.w, 52);
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  tile(C, 1150, 120, 300, 24, {fill:RS, op:0.7}); tile(C, 860, 610, 200, 24, {fill:RS, op:0.6});
  if (photo) framed(C, 760, 150, 400, 420);
  else { tile(C, 980, 330, 330, 24, {fill:RD}); tile(C, 1150, 560, 180, 24, {fill:RD2}); tile(C, 800, 150, 120, 24, {fill:RD, op:0.85}); }
  if (f.company) { tile(C, 84, 70, 18, 24, {fill:RD}); C.text(104, 58, 400, 24, f.company, {font:'head', size:15, wt:800, color:INK, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  C.text(72, 180, 600, 250, C.s.title, {font:'head', fit:{max:70, min:30}, wt:800, color:RD, caps:true, ls:0.05, lh:1.02, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(72, 448, 420, 2, {fill:INK, dash:true});
  if (C.s.subtitle) C.text(72, 470, 560, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:INK, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 574, 560, 24, who, {font:'body', size:15, wt:700, color:RD, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.website) C.text(72, 630, 400, 18, f.website, {font:'body', size:13, color:MU, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  tile(C, 60, 80, 190, 24, {fill:RS, op:0.6}); tile(C, W - 70, H - 40, 260, 24, {fill:RS, op:0.6});
  if (photo) framed(C, 790, 140, 380, 400); else { tile(C, 980, 340, 300, 24, {fill:RD}); C.icon(930, 290, 100, 'mdi:handshake-outline', '#FFFFFF'); }
  C.text(72, 140, 620, 200, C.s.title || 'Thank You', {font:'head', fit:{max:88, min:36}, wt:800, color:RD, caps:true, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 356, 560, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 444 + k * 54; C.ell(72, y, 40, 40, {fill:RD}); C.icon(82, y + 10, 20, c.icon, '#FFFFFF'); C.text(128, y, 520, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 640 : W - 144;
  if (photo) framed(C, 820, 140, 360, 420);
  tile(C, 120, 160, 90, 24, {fill:RD}); C.text(75, 115, 90, 90, '“', {font:'head', size:84, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  var qe = C.text(72, 250, w, 280, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:700, color:INK, lh:1.2, ed:{f:'title'}, maxLines:6});
  if (by) C.text(72, 250 + qe._h + 26, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:RD, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'crimsontile', name:'Crimson Tile', tag:'White · crimson tiles',
  desc:'Bright white pages with deep crimson tilted rounded tiles, pale rose squares and red capital headings over a fine rule. For company profiles, agencies, consulting and retail decks.',
  fonts:{head:'Archivo', body:'Source Sans 3', headW:800, bodyW:400, headLh:1.04, gf:'Archivo:wght@600;700;800|Source+Sans+3:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Crimson tiles', {}), V('v2', 'Framed photo', {img:'photo'})],
    closing:[V('v1', 'Crimson tile', {}), V('v2', 'Framed photo', {img:'photo'})],
    agenda:[V('v1', 'Process box', {fn:processBox}), V('n2', 'Numbered rows', pg(R, {cols:2})), V('v2', 'Rows + framed photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Rose panel', pg(B.statement)), V('v2', 'Panel + framed photo', pg(B.statement, {img:'right'})), V('v3', 'Framed photo + panel', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Ruled cards', pg(CA, {hl:true})), V('n2', 'Tile + points', {fn:tileList}), V('n3', 'Process box', {fn:processBox}), V('v2', 'Cards + framed photo', pg(CA, {img:'right', cols:1})), V('v3', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p3:[V('v1', 'Ruled cards', pg(CA, {hl:true})), V('n2', 'Tile steps', pg(ST)), V('n3', 'Process box', {fn:processBox}), V('n4', 'Tile + points', {fn:tileList}), V('v2', 'Rows + framed photo', pg(R, {img:'right'})), V('v3', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Ruled cards', pg(CA)), V('n2', 'Process box', {fn:processBox}), V('n3', 'Tile steps', pg(ST)), V('n4', 'Tile + points', {fn:tileList}), V('v2', 'Rows + framed photo', pg(R, {img:'right'}))],
    p56:[V('v1', 'Ruled cards', pg(CA)), V('n2', 'Process box', {fn:processBox}), V('n3', 'Numbered rows', pg(R)), V('v2', 'Rows + framed photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Ruled cards', pg(CA)), V('n2', 'Numbered rows', pg(R)), V('v2', 'Rows + framed photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Number cards', pg(B.stats, {hot:0})), V('n2', 'Progress bars', {body:'statBars'}), V('n3', 'Progress rings', {body:'statRings', track:'#F6D5D5'}), V('v2', 'Numbers + framed photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Tile steps', pg(ST)), V('n2', 'Process box', {fn:processBox}), V('v2', 'Rows + framed photo', pg(R, {img:'right', cols:1})), V('v3', 'Steps + photo strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Crimson quote', {}), V('v2', 'Quote + framed photo', {img:'photo'})]
  }
});
})();

