
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 50 - Copper Diamond : cream page, layered copper corner folds, diamond outlines round the photo */
var CO = '#B8642F', DK = '#6E3414', LT = '#E7B48E', CR = '#FBF6EF', INK = '#2E1D12', MUT = '#7A6455';
var P0 = {bg:CR, text:INK, title:DK, muted:MUT, accent:CO, accent2:DK, card:'#FFFFFF', line:'#EAD9C8', onAccent:'#FFFFFF'};
var S = {
  margin:74, series:[CO, DK, LT, '#3F6B5E', '#D9C2AE', '#2E1D12'],
  header:{kind:'left', top:92, tMax:44, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'solid', r:14, fill:'#FFFFFF', line:null, shadow:0.08, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:CO, dark:true, tc:'#FFFFFF', mc:'#FBE7D8'}},
  icon:{kind:'circle', bg:CO, fg:'#FFFFFF'}, num:{kind:'big', fg:CO},
  conc:{kind:'bar', barC:CO, label:'In short', lc:CO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:18, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#EBCBB0'], [100, '#B8642F']]}}
};
function dia(cx, cy, r){ return [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]]; }
function diaLine(C, cx, cy, r, color, lw, op){ var p = dia(cx, cy, r); for (var q = 0; q < 4; q++) C.line(p[q][0], p[q][1], p[(q + 1) % 4][0], p[(q + 1) % 4][1], {color:color, lw:lw || 2, op:op == null ? 1 : op}); }
/* layered folds in a corner: three triangles, light to dark */
function folds(C, corner, s){
  var r = corner.indexOf('r') >= 0, b = corner.indexOf('b') >= 0, x = r ? W : 0, y = b ? H : 0, dx = r ? -1 : 1, dy = b ? -1 : 1;
  K.poly(C, [[x, y], [x + dx * s, y], [x, y + dy * s]], {fill:LT, op:0.55});
  K.poly(C, [[x, y], [x + dx * s * 0.74, y], [x, y + dy * s * 0.74]], {fill:CO});
  K.poly(C, [[x, y], [x + dx * s * 0.44, y], [x, y + dy * s * 0.44]], {fill:DK});
}
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  K.poly(C, dia(x + 10, y + 11, 10), {fill:CO}); K.poly(C, dia(x + 20, y + 11, 10), {fill:DK, op:0.85});
  C.text(x + 40, y, 420, 22, f.company, {font:'head', size:12.5, wt:800, color:DK, va:'m', lh:1, caps:true, ls:0.1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 74, y = H - 36, f = C.cover;
  C.text(M, y, 500, 16, f.website || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  K.poly(C, dia(W - M - 14, y + 8, 15), {fill:CO});
  C.text(W - M - 29, y - 7, 30, 30, String(C.i + 1), {font:'head', size:11.5, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:CR});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  folds(C, 'tr', [150, 110, 190][k]);
  brand(C, 74, 46);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* a photo cut to a diamond: the square picture first, then its four corners are covered in the page colour */
function diaPhoto(C, cx, cy, r, col, ring){
  D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {r:0});
  [[-1, -1], [1, -1], [1, 1], [-1, 1]].forEach(function(q){ K.poly(C, [[cx + q[0] * (r + 2), cy + q[1] * (r + 2)], [cx + q[0] * (r + 2), cy], [cx, cy + q[1] * (r + 2)]], {fill:CR}); });
  C._dia = (C._dia || []).concat([[cx, cy, r, col || CO, ring || LT]]);
}
/* the outlines go on last, so no cover triangle can hide them */
function diaEdges(C){
  (C._dia || []).forEach(function(d){ diaLine(C, d[0], d[1], d[2] - 2, d[3], 7); diaLine(C, d[0], d[1], d[2] * 1.3, d[3], 2.5, 0.9); diaLine(C, d[0] + d[2] * 0.14, d[1] + d[2] * 0.1, d[2] * 1.16, d[4], 2); });
  C._dia = null;
}
/* band cards: a copper band runs across the slide and white cards sit half over it */
function bandCards(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return checks(C, Z, o);
  var g = 22, cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 54;
  var F = K.fitHD(C, it, tw, 52, Z.h - top - pad * 2 - 30, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - 20, bmx + top + pad * 2), y0 = Z.y + Math.max(10, (Z.h - ch) * 0.35);
  C.rect(0, y0 + ch * 0.38, W, ch * 0.74, {grad:{ang:0, stops:[[0, DK], [100, CO]]}});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g);
    C.rect(x, y0, cw, ch, {fill:'#FFFFFF', r:14, shadow:0.12});
    K.poly(C, dia(x + pad + 18, y0 + pad + 14, 20), {fill:k % 2 ? DK : CO});
    C.text(x + pad - 2, y0 + pad - 6, 40, 40, String(k + 1), {font:'head', size:15, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
    C.icon(x + cw - pad - 24, y0 + pad + 2, 24, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), CO);
    K.hd(C, x + pad, y0 + pad + top, tw, ch - top - pad * 2, t, F, {tc:INK, mc:MUT});
  });
}
/* check list: a diamond bullet, heading and text, in one or two columns, no boxes */
function checks(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 50, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 136), tw = cw - 46;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:21, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(10, (rh - bh) / 2);
    K.poly(C, dia(x + 13, y + oy + 12, 13), {fill:k % 2 ? DK : CO}); K.poly(C, dia(x + 13, y + oy + 12, 5), {fill:'#FFFFFF'});
    K.hd(C, x + 46, y + oy, tw, rh - oy - 4, t, F, {tc:INK, mc:MUT});
    if (r < per - 1) C.rect(x + 46, y + rh - 0.5, tw, 1, {fill:DK, op:0.14});
  });
}
/* fold cards: white cards with a turned-down copper corner */
function foldCards(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22, fs = 34, tw = cw - pad * 2 - 8;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 8), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0;
    C.rect(x, y, cw, ch2, {fill:hot ? CO : '#FFFFFF', r:6, shadow:0.08});
    K.poly(C, [[x + cw - fs, y], [x + cw, y], [x + cw, y + fs]], {fill:CR});
    K.poly(C, [[x + cw - fs, y], [x + cw - fs, y + fs], [x + cw, y + fs]], {fill:hot ? DK : LT});
    C.rect(x, y + 14, 5, ch2 - 28, {fill:hot ? '#FFFFFF' : CO, op:hot ? 0.6 : 1});
    K.hd(C, x + pad + 4, y + pad, tw, ch2 - pad * 2, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#FBE7D8' : MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: copper badges - the number and its label side by side in one rounded tag */
function badges(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n === 4 ? 2 : (Z.w < 760 ? 1 : n), rows = Math.ceil(n / cols), g = 20, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, 170), vals = st.map(sv), labs = st.map(sl), pad = 22, vw = cw * 0.46, tw = cw - vw - pad * 2;
  var vs = C.groupFit(vals, vw - pad, ch - 30, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, ch - 30, {font:'head', max:18, min:12, maxLines:3});
  var tot = ch * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch + g), col = k % 2 ? DK : CO;
    C.rect(x, y + 6, cw, ch, {fill:DK, op:0.18, r:18});
    C.rect(x, y, cw, ch, {fill:col, r:18});
    C.rect(x + vw, y + 12, cw - vw - 12, ch - 24, {fill:'#FFFFFF', r:12});
    C.text(x + 10, y, vw - 20, ch, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + vw + pad - 6, y + 12, tw, ch - 24, labs[k], {font:'head', size:ls, wt:700, color:INK, va:'m', lh:1.18, caps:true, ls:0.03, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
  });
}
/* timeline: diamond stops over a copper band */
function stops(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return checks(C, Z, o);
  var cw = Z.w / n, r = 40, by = Z.y + 26, tw = cw - 24;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), r * 1.3, r, {font:'head', max:18, min:10, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - r * 2 - 70, {font:'body', max:15, min:10.5, lh:1.4});
  C.rect(0, by + r - 20, W, 40, {grad:{ang:0, stops:[[0, DK], [100, CO]]}});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    K.poly(C, dia(cx, by + r, r + 7), {fill:CR}); K.poly(C, dia(cx, by + r, r), {fill:'#FFFFFF'}); diaLine(C, cx, by + r, r, k === n - 1 ? DK : CO, 3);
    C.text(cx - r, by, r * 2, r * 2, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:DK, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(cx - tw / 2, by + r * 2 + 30, tw, Z.h - r * 2 - 60, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF', r:14, shadow:0.08});
  K.poly(C, [[Z.x + Z.w - 60, Z.y], [Z.x + Z.w, Z.y], [Z.x + Z.w, Z.y + 60]], {fill:CR}); K.poly(C, [[Z.x + Z.w - 60, Z.y], [Z.x + Z.w - 60, Z.y + 60], [Z.x + Z.w, Z.y + 60]], {fill:LT});
  var pad = 46, y = Z.y + pad, w = Z.w - pad * 2 - 30;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:CO, lh:1.12, caps:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w + 30, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  folds(C, 'br', 330); folds(C, 'tr', 120);
  brand(C, 74, 56);
  if (photo) { diaPhoto(C, 940, 350, 168); if (o.two) diaPhoto(C, 738, 548, 76, DK); diaEdges(C); }
  else { diaLine(C, 940, 350, 220, CO, 2.5, 0.9); K.poly(C, dia(940, 350, 180), {fill:'#FFFFFF'}); K.poly(C, dia(940, 350, 150), {fill:CO}); K.poly(C, dia(940, 350, 90), {fill:DK}); diaLine(C, 1100, 210, 50, DK, 2); K.poly(C, dia(760, 520, 34), {fill:LT}); }
  var te = C.text(74, 190, 600, 240, C.s.title, {font:'head', fit:{max:72, min:32}, wt:800, color:DK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, caps:true, ls:-0.01});
  if (C.s.subtitle) C.text(74, 190 + te._h + 20, 540, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(560, who.length * 8.2 + 56); K.poly(C, [[74, 552], [74 + pw, 552], [74 + pw + 18, 574], [74 + pw, 596], [74, 596]], {fill:CO}); C.text(74 + 20, 552, pw - 30, 44, who, {font:'body', size:14, wt:700, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(74, 628, 500, 18, f.website, {font:'body', size:12.5, wt:600, color:CO, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  folds(C, 'br', 330); folds(C, 'tl', 110);
  brand(C, 150, 56);
  if (photo) { diaPhoto(C, 940, 340, 160); diaEdges(C); } else { diaLine(C, 940, 340, 200, CO, 2.5, 0.9); K.poly(C, dia(940, 340, 160), {fill:'#FFFFFF'}); K.poly(C, dia(940, 340, 130), {fill:CO}); C.icon(900, 300, 80, 'mdi:handshake-outline', '#FFFFFF'); }
  C.text(74, 150, 620, 180, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:800, color:DK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, caps:true});
  if (C.s.subtitle) C.text(74, 346, 560, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 440 + k * 56;
    K.poly(C, dia(74 + 20, y + 20, 22), {fill:k % 2 ? DK : CO}); C.icon(74 + 10, y + 10, 20, c.icon, '#FFFFFF');
    C.text(74 + 60, y, 520, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  if ((C.o || o || {}).img === 'photo') { diaPhoto(C, 250, 430, 150); diaEdges(C); }
  else { diaLine(C, 250, 430, 210, CO, 2.5, 0.9); K.poly(C, dia(250, 430, 170), {fill:'#FFFFFF'}); K.poly(C, dia(250, 430, 140), {fill:CO}); C.icon(210, 390, 80, 'mdi:format-list-bulleted', '#FFFFFF'); }
  var te = C.text(520, 110, W - 520 - 74, 80, C.s.title || 'Agenda', {font:'head', fit:{max:54, min:30}, wt:800, color:DK, ed:{f:'title'}, maxLines:1, va:'t', lh:1.02, caps:true});
  if (C.s.subtitle) C.text(520, 110 + te._h + 8, W - 520 - 74, 30, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:MUT, ed:{f:'subtitle'}, lh:1.3, maxLines:1});
  checks(C, {x:520, y:230, w:W - 520 - 110, h:H - 330}, {cols:2, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CO, tc:INK}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 580 : W - 220;
  if (photo) { diaPhoto(C, 965, 360, 160); diaEdges(C); }
  K.poly(C, dia(x + 34, 174, 34), {fill:CO}); C.text(x, 152, 68, 60, '”', {font:'head', size:56, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x, 240, w, 280, C.s.title, {font:'head', fit:{max:photo ? 34 : 44, min:19}, wt:700, color:DK, lh:1.2, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, 240 + qe._h + 26, w, 24, bt, {font:'body', size:14.5, wt:700, color:CO, lh:1.1, caps:true, ls:0.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'copperdiamond', name:'Copper Diamond', tag:'Cream · copper folds',
  desc:'A cream page with layered copper corner folds and photos cut to a diamond: polished and warm for company profiles, real estate and services.',
  fonts:{head:'Josefin Sans', body:'Hind', headW:700, bodyW:400, headLh:1.1, gf:'Josefin+Sans:wght@500;600;700|Hind:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Diamond + corner folds', {}), V('v2', 'Photo in a diamond', {img:'photo'}), V('v3', 'Two photos in diamonds', {img:'photo', two:true})],
    closing:[V('v1', 'Diamond + contacts', {}), V('v2', 'Diamond photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Diamond + check list', {fn:agendaA}), V('n3', 'Diamond photo + check list', {fn:agendaA, img:'photo'}), V('n2', 'Fold cards', {body:foldCards}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1})],
    text:[V('v1', 'Fold statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Band cards', {body:bandCards}), V('n2', 'Fold cards', {body:foldCards, hl:true}), V('n3', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:1})],
    p3:[V('v1', 'Band cards', {body:bandCards}), V('n2', 'Fold cards', {body:foldCards, hl:true}), V('n3', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:1})],
    p4:[V('v1', 'Check list', {body:checks, cols:2}), V('n2', 'Band cards', {body:bandCards}), V('n3', 'Fold cards', {body:foldCards}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:2})],
    p56:[V('v1', 'Fold cards', {body:foldCards}), V('n2', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1}), V('v3', 'Image left + fold cards', {body:foldCards, img:'left', cols:2})],
    p78:[V('v1', 'Fold cards', {body:foldCards}), V('n2', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1})],
    stats:[V('v1', 'Copper badges', {body:badges}), V('n2', 'Progress rings', {body:'statRings', track:'#EAD9C8'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Badges + image right', {body:badges, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Diamond stops', {body:stops}), V('n2', 'Band cards', {body:bandCards}), V('n3', 'Check list', {body:checks}), V('v2', 'Check list + image right', {body:checks, img:'right', cols:1})],
    quote:[V('v1', 'Diamond quote', {}), V('v2', 'Quote + diamond photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
