
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 72 - Capsule Corporate : white page, soft grey blobs, royal-blue capsule panels, round photos with blue rings, a slim nav header */
var BG = '#FFFFFF', BL = '#1E4FA3', BL2 = '#3D6FD1', SKY = '#E7EEFB', GB = '#E9EBEF', INK = '#16233D', MU = '#5B6475';
var P0 = {bg:BG, text:INK, title:BL, muted:MU, accent:BL, accent2:BL2, card:SKY, line:'#D6DEEC', onAccent:'#FFFFFF'};
function cube(C, x, y, s){ K.poly(C, [[x + s / 2, y], [x + s, y + s * 0.27], [x + s, y + s * 0.73], [x + s / 2, y + s], [x, y + s * 0.73], [x, y + s * 0.27]], {fill:BL}); K.poly(C, [[x + s / 2, y + s * 0.5], [x + s, y + s * 0.27], [x + s, y + s * 0.73], [x + s / 2, y + s]], {fill:BL2}); }
function nav(C, x0){
  var f = C.cover, y = 30; x0 = x0 || 72;
  cube(C, x0, y, 26); C.text(x0 + 36, y, 220, 26, f.company || '', {font:'head', size:12.5, wt:700, color:INK, va:'m', lh:1, maxLines:1});
  for (var k = 0; k < 3; k++) C.ell(x0 + 262 + k * 12, y + 10, 7, 7, {fill:BL});
  C.line(x0 + 320, y + 13, W - 72, y + 13, {color:'#D6DEEC', lw:1.2});
  C.rect(W - 160, y, 88, 26, {fill:BL, r:13}); C.text(W - 160, y, 88, 26, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'head', size:11, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:BG});
  var k = (C.i || 0) % 2;
  K.blob(C, k ? W - 60 : 80, H - 30, 170, GB, 1, 3 + k); K.blob(C, k ? 60 : W - 40, 120, 110, GB, 1, 7 + k);
  if (kind === 'cover' || kind === 'closing') return;
  nav(C);
}
function head(C, A, o){
  var y = 84;
  var te = C.text(A.x, y, A.w, 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:BL, caps:true, lh:1.04, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
function ringPhoto(C, cx, cy, r){ C.ell(cx - r - 14, cy - r - 14, r * 2 + 28, r * 2 + 28, {fill:BL}); C.ell(cx - r - 6, cy - r - 6, r * 2 + 12, r * 2 + 12, {fill:'#FFFFFF'}); D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'}); }
var X = {
  M:72, tc:'#FFFFFF', mc:'#DCE6FA', ac:'#FFFFFF', markS:46, g:18, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:SKY, r:Math.min(60, h / 2)}); return {tc:INK, mc:MU, ac:BL}; }
    C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, k % 2 ? BL2 : BL], [100, k % 2 ? '#2B5BC0' : '#163F87']]}, r:Math.min(34, w / 2, h / 2)});
    return {tc:'#FFFFFF', mc:'#DCE6FA', ac:'#FFFFFF'};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : SKY, r:h / 2, line:'#D6DEEC', lw:1}); return {tc:INK, mc:MU, ac:BL}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:on ? '#FFFFFF' : BL}); K.icon(C, x + s * 0.24, y + s * 0.24, s * 0.52, t, on ? BL : '#FFFFFF'); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2 - 5, cy - s / 2 - 5, s + 10, s + 10, {line:BL, lw:2}); C.ell(cx - s / 2, cy - s / 2, s, s, {fill:BL}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.34, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 3, x2 - x1, 6, {fill:SKY, r:3}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y, W - 144, Z.h - 18, {r:Math.min(70, (Z.h - 18) / 2)}); return; }
    var w = Z.w - 80, x = Z.x + 40, y = 96, h = H - 170;
    D.imgBox(C, x, y, w, h, {r:Math.min(w, h) / 2});
    ringPhoto(C, side === 'right' ? x + 10 : x + w - 10, y + h - 40, 52);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 42; C.text(a.x, y, 400, 16, C.cover.website || '', {font:'body', size:11.5, wt:500, color:MU, lh:1, va:'m'}); C.line(a.x + 200, y + 8, a.x + a.w - 80, y + 8, {color:'#D6DEEC', lw:1}); C.rect(a.x + a.w - 62, y - 4, 62, 24, {line:BL, lw:1.2, r:12}); C.text(a.x + a.w - 62, y - 4, 62, 24, 'Next', {font:'head', size:11, wt:700, color:BL, align:'c', va:'m', lh:1}); }
var S = {margin:72, series:[BL, BL2, '#86A7E8', SKY, '#5B6475', '#16233D'], header:{kind:'left', top:84}, card:{kind:'solid', fill:BL, r:34}, icon:{kind:'plain', fg:BL}, num:{kind:'big', fg:BL},
  conc:{kind:'pill', fill:BL, op:0.1, label:'Takeaway', lc:BL, tc:INK}, footer:{fn:foot}, imgFramed:true, imgR:60, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#86A7E8'], [100, '#1E4FA3']]}}};
/* signature: capsule + circles - a tall blue capsule holds the title, points sit in icon circles beside it */
function capsuleCircles(C, o){
  var cx = 72, cw = 430;
  C.rect(cx, 90, cw, H - 170, {grad:{ang:150, stops:[[0, BL2], [100, '#163F87']]}, r:150});
  C.text(cx + 56, 150, cw - 112, 210, C.s.title || '', {font:'head', fit:{max:44, min:22}, wt:800, color:'#FFFFFF', caps:true, lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  if (C.s.subtitle) C.text(cx + 56, 376, cw - 112, 120, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#DCE6FA', lh:1.42, ed:{f:'subtitle'}, maxLines:4});
  var it = C.it.slice(0, 6), n = it.length, Z = {x:560, y:100, w:W - 560 - 72, h:H - 190 - (C.s.conclusion ? 64 : 0)};
  var cl = n > 3 ? 2 : 1, per = Math.ceil(n / cl), cwid = (Z.w - 24 * (cl - 1)) / cl, rh = Z.h / per, s = Math.min(84, rh * 0.62), tw = cwid - s - 22;
  var F = K.fitHD(C, it, tw, 40, rh - 10, {hMax:24, dMax:18, hMin:12, dMin:10, noBoost:n > 4});
  it.forEach(function(t, k){ var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cwid + 24), y = Z.y + r * rh, bh = Math.max(K3.bH(C, t, tw, F), s), ty = y + (rh - bh) / 2;
    C.ell(x, ty, s, s, {line:BL, lw:3}); C.ell(x + 6, ty + 6, s - 12, s - 12, {fill:SKY}); K.icon(C, x + s * 0.27, ty + s * 0.27, s * 0.46, t, BL);
    K.hd(C, x + s + 22, ty + 4, tw, rh - 8, t, F, {tc:INK, mc:MU}); });
  if (C.s.conclusion) D.conclusion(C, 560, H - 136, W - 632, 52);
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  nav(C);
  C.rect(0, 110, 760, 420, {grad:{ang:150, stops:[[0, BL2], [100, '#163F87']]}, r:210});
  C.rect(-210, 110, 420, 420, {fill:BL2});
  if (photo) ringPhoto(C, 960, 330, 200); else { C.ell(760, 130, 400, 400, {fill:SKY}); C.ell(820, 190, 280, 280, {line:BL, lw:3}); C.icon(900, 270, 120, 'mdi:office-building-outline', BL); }
  C.text(72, 160, 600, 220, C.s.title, {font:'head', fit:{max:64, min:28}, wt:800, color:'#FFFFFF', caps:true, lh:1.02, va:'b', ed:{f:'title'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 394, 560, 24, 'Presented by ' + who, {font:'body', size:14.5, wt:600, color:'#DCE6FA', va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (C.s.subtitle) C.text(72, 432, 560, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FFFFFF', lh:1.42, ed:{f:'subtitle'}, maxLines:3});
  if (f.website) C.text(72, H - 60, 400, 18, f.website, {font:'body', size:12.5, color:MU, lh:1, ed:{f:'tf-website'}});
  for (var k = 0; k < 3; k++) C.ell(W - 150 + k * 16, H - 54, 9, 9, {fill:BL});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  nav(C);
  C.rect(72, 110, 640, 300, {grad:{ang:150, stops:[[0, BL2], [100, '#163F87']]}, r:150});
  if (photo) ringPhoto(C, 980, 340, 190); else { C.ell(800, 160, 360, 360, {fill:SKY}); C.icon(910, 270, 140, 'mdi:handshake-outline', BL); }
  C.text(130, 140, 540, 240, C.s.title || "Let's work together", {font:'head', fit:{max:58, min:26}, wt:800, color:'#FFFFFF', caps:true, lh:1.04, va:'m', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(72, 428, 640, 40, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 486 + k * 50; C.ell(72, y, 38, 38, {fill:BL}); C.icon(81, y + 9, 20, c.icon, '#FFFFFF'); C.text(124, y - 1, 560, 40, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 640 : W - 144;
  if (photo) ringPhoto(C, 1000, 380, 170);
  C.rect(72, 120, 120, 64, {fill:BL, r:32}); C.text(72, 120, 120, 64, '“', {font:'head', size:64, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  var qe = C.text(72, 220, w, 300, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:700, color:INK, lh:1.2, ed:{f:'title'}, maxLines:6});
  if (by) C.text(72, 220 + qe._h + 26, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:BL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'capsule', name:'Capsule Corporate', tag:'White · blue capsules',
  desc:'Clean white pages with soft grey blobs, royal-blue capsule panels, capsule and round photos with blue rings and a slim website-style nav header. For company profiles, services and team introductions.',
  fonts:{head:'Outfit', body:'DM Sans', headW:800, bodyW:400, headLh:1.04, gf:'Outfit:wght@500;600;700;800|DM+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Blue capsule', {}), V('v2', 'Capsule + round photo', {img:'photo'})],
    closing:[V('v1', 'Blue capsule', {}), V('v2', 'Capsule + round photo', {img:'photo'})],
    agenda:[V('v1', 'Capsule rows', pg(R, {cols:2})), V('n2', 'Capsule + circles', {fn:capsuleCircles}), V('v2', 'Rows + capsule photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Sky capsule', pg(B.statement)), V('v2', 'Statement + capsule photo', pg(B.statement, {img:'right'})), V('v3', 'Capsule photo + statement', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Capsule + circles', {fn:capsuleCircles}), V('n2', 'Blue capsules', pg(CA)), V('n3', 'Capsule rows', pg(R)), V('v2', 'Rows + capsule photo', pg(R, {img:'right'})), V('v3', 'Capsules + photo strip', pg(CA, {img:'bottom'}))],
    p3:[V('v1', 'Blue capsules', pg(CA, {center:true})), V('n2', 'Capsule + circles', {fn:capsuleCircles}), V('n3', 'Ring steps', pg(ST)), V('v2', 'Rows + capsule photo', pg(R, {img:'right'})), V('v3', 'Capsule photo + rows', pg(R, {img:'left'})), V('v4', 'Capsules + photo strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Capsule + circles', {fn:capsuleCircles}), V('n2', 'Blue capsules', pg(CA)), V('n3', 'Capsule rows', pg(R, {cols:2})), V('v2', 'Rows + capsule photo', pg(R, {img:'right'}))],
    p56:[V('v1', 'Capsule + circles', {fn:capsuleCircles}), V('n2', 'Blue capsules', pg(CA)), V('n3', 'Capsule rows', pg(R)), V('v2', 'Rows + capsule photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Blue capsules', pg(CA)), V('n2', 'Capsule rows', pg(R)), V('v2', 'Rows + capsule photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Capsule numbers', pg(B.stats, {center:true})), V('n2', 'Progress rings', {body:'statRings', track:'#E7EEFB'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + capsule photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Ring steps', pg(ST)), V('n2', 'Capsule rows', pg(R)), V('v2', 'Rows + capsule photo', pg(R, {img:'right', cols:1})), V('v3', 'Steps + photo strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Capsule quote', {}), V('v2', 'Quote + round photo', {img:'photo'})]
  }
});
})();

