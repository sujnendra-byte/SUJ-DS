
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 89 - Compass Point : deep teal and brass, a compass dial with degree ticks, bearings and route lines */
var BG = '#F4F7F7', TE = '#0F4C5C', TE2 = '#16627A', BR = '#C79A3B', INK = '#16262B', MU = '#4F6268', MI = '#D4E2E4', WH = '#FFFFFF';
var BEAR = ['000°', '045°', '090°', '135°', '180°', '225°', '270°', '315°'], CARD = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
var P0 = {bg:BG, text:INK, title:TE, muted:MU, accent:TE, accent2:BR, card:WH, line:'#CFDCDE', onAccent:'#FFFFFF'};
function needle(C, cx, cy, r, ang, c1, c2){ var a = ang * RAD, ux = Math.cos(a), uy = Math.sin(a), px = -uy * r * 0.14, py = ux * r * 0.14;
  K.poly(C, [[cx + ux * r, cy + uy * r], [cx + px, cy + py], [cx - px, cy - py]], {fill:c1 || BR});
  K.poly(C, [[cx - ux * r, cy - uy * r], [cx + px, cy + py], [cx - px, cy - py]], {fill:c2 || '#FFFFFF'}); C.ell(cx - r * 0.06, cy - r * 0.06, r * 0.12, r * 0.12, {fill:INK}); }
function dial(C, cx, cy, r, o){
  o = o || {}; var c = o.color || TE;
  C.ell(cx - r, cy - r, r * 2, r * 2, {fill:o.fill || null, line:c, lw:2});
  C.ell(cx - r * 0.8, cy - r * 0.8, r * 1.6, r * 1.6, {line:o.brass || BR, lw:3});
  var d = []; for (var i = 0; i < 72; i++) { var a = i * 5 * RAD, l = i % 18 === 0 ? r * 0.14 : (i % 2 ? r * 0.04 : r * 0.08); d.push(['M', cx + Math.cos(a) * r * 0.97, cy + Math.sin(a) * r * 0.97], ['L', cx + Math.cos(a) * (r * 0.97 - l), cy + Math.sin(a) * (r * 0.97 - l)]); }
  C.path(d, {stroke:c, lw:1.2});
  if (o.labels !== false) ['N', 'E', 'S', 'W'].forEach(function(t, k){ var a = (k * 90 - 90) * RAD; C.text(cx + Math.cos(a) * r * 0.66 - 20, cy + Math.sin(a) * r * 0.66 - 14, 40, 28, t, {font:'head', size:Math.max(12, r * 0.1), wt:700, color:o.lc || c, align:'c', va:'m', lh:1}); });
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var d = []; for (var i = 0; i < 48; i++) { var a = i * 7.5 * RAD, l = i % 6 === 0 ? 18 : 8; d.push(['M', W + Math.cos(a) * 170, Math.sin(a) * 170], ['L', W + Math.cos(a) * (170 - l), Math.sin(a) * (170 - l)]); } C.path(d, {stroke:TE, lw:1.2, lop:0.5});
  C.ell(W - 150, -150, 300, 300, {line:BR, lw:2});
}
function head(C, A, o){
  var y = 50;
  C.text(A.x, y, A.w, 16, 'BEARING ' + BEAR[(C.i || 0) % 8] + '  ·  ' + (C.cover.company || '').toUpperCase(), {font:'head', size:11.5, wt:700, color:BR, ls:0.14, lh:1, va:'m', maxLines:1});
  y += 26;
  var te = C.text(A.x, y, Math.min(A.w, 860), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:TE, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:INK, mc:MU, ac:TE, markS:44, g:18, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:WH, r:14}); dial(C, x + w - 70, y + h - 70, 52, {labels:false, color:MI}); return {tc:INK, mc:MU, ac:TE}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:TE, r:14}); return {tc:'#FFFFFF', mc:'#BFD3D6', ac:BR}; }
    C.rect(x, y, w, h, {fill:WH, r:14}); C.text(x + w - 80, y + 14, 64, 16, BEAR[(k * 2) % 8], {font:'head', size:12, wt:700, color:BR, align:'r', lh:1});
    return {tc:INK, mc:MU, ac:TE};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:'#CFDCDE'}); return {tc:INK, mc:MU, ac:TE}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:on ? BR : TE}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ C.ell(x, y, s, s, {line:TE, lw:2}); needle(C, x + s / 2, y + s / 2, s * 0.42, -90 + k * 45, BR, TE); },
  node:function(C, cx, cy, s, k){ C.ell(cx - s / 2, cy - s / 2, s, s, {fill:WH, line:TE, lw:3}); needle(C, cx, cy, s * 0.4, -90 + k * 30, BR, TE); },
  track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:TE, lw:2, dash:true}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y + 6, W - 144, Z.h - 24, {r:14}); return; }
    var cx = Z.x + Z.w / 2, cy = H / 2, r = Math.min(Z.w / 2 - 20, 260);
    D.imgBox(C, cx - r * 0.78, cy - r * 0.78, r * 1.56, r * 1.56, {shape:'ell'});
    dial(C, cx, cy, r, {labels:true, color:TE});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.website || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 120, y, 120, 16, 'WAYPOINT ' + pad2(C.i + 1), {font:'head', size:11.5, wt:700, color:TE, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[TE, BR, TE2, '#E3C98C', '#4F6268', '#9EC1C7'], header:{kind:'left', top:50}, card:{kind:'solid', fill:WH, r:14}, icon:{kind:'plain', fg:TE}, num:{kind:'big', fg:TE},
  conc:{kind:'bar', barC:BR, label:'Heading', lc:BR, tc:INK}, footer:{fn:foot}, imgFramed:true, imgR:14, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#9EC1C7'], [100, '#0F4C5C']]}}};
/* signature: four directions - points around a central compass */
function quadrants(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 3 || Z.w < 760) return B.cards(C, Z, o);
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r = Math.min(Z.h * 0.34, 120), tw = Z.w / 2 - r - 60, th = Z.h / 2 - 10;
  dial(C, cx, cy, r, {fill:WH}); needle(C, cx, cy, r * 0.62, -60, BR, TE);
  var F = K.fitHD(C, it, tw, 40, th - 30, {hMax:20, dMax:15, hMin:12, dMin:10});
  var pos = [[Z.x, Z.y, 'l'], [cx + r + 60, Z.y, 'l'], [cx + r + 60, cy + 10, 'l'], [Z.x, cy + 10, 'l']];
  it.forEach(function(t, k){ var p = pos[k], bh = K3.bH(C, t, tw, F);
    C.text(p[0], p[1] + 4, 120, 18, CARD[k * 2] + ' · ' + BEAR[k * 2], {font:'head', size:12, wt:800, color:BR, lh:1});
    K.hd(C, p[0], p[1] + 28, tw, th - 30, t, F, {tc:INK, mc:MU});
  });
}
/* signature: dial numbers - each figure on a small gauge dial */
function dials(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.36, Z.h * 0.3, 100), cy = Z.y + r + 6, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 24;
  var vs = C.groupFit(vals, r * 1.2, r * 0.5, {font:'head', max:40, min:16, maxLines:1}), ls = C.groupFit(labs, tw, 48, {font:'head', max:17, min:12, maxLines:2});
  st.forEach(function(t, k){ var cx = Z.x + cw * (k + 0.5); dial(C, cx, cy, r, {labels:false, fill:k === 0 ? TE : WH, color:k === 0 ? '#FFFFFF' : TE});
    C.text(cx - r * 0.6, cy - r * 0.3, r * 1.2, r * 0.6, vals[k], {font:'head', size:vs, wt:800, color:k === 0 ? '#FFFFFF' : TE, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, cy + r + 18, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, cy + r + 24 + le._h, tw, Z.y + Z.h - (cy + r + 24 + le._h), t.x, {font:'body', size:13.5, color:MU, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* signature: route with waypoints and bearings */
function routeLine(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.rows(C, Z, o);
  var cw = Z.w / n, tw = cw - 20, pts = it.map(function(t, k){ return [Z.x + cw * (k + 0.5), Z.y + (k % 2 ? Z.h * 0.6 : Z.h * 0.42)]; });
  C.path(pts.map(function(p, k){ return [k ? 'L' : 'M', p[0], p[1]]; }), {stroke:TE, lw:3});
  var F = K.fitHD(C, it, tw, 40, Z.h * 0.3, {hMax:20, dMax:15, hMin:12, dMin:10});
  it.forEach(function(t, k){ var p = pts[k]; C.ell(p[0] - 14, p[1] - 14, 28, 28, {fill:k === n - 1 ? BR : WH, line:TE, lw:3});
    var up = k % 2 === 0, bh = K3.bH(C, t, tw, F);
    C.text(p[0] + 20, p[1] - 9, 60, 18, BEAR[(k * 3) % 8], {font:'head', size:12, wt:700, color:BR, lh:1, va:'m'});
    K.hd(C, p[0] - tw / 2, up ? p[1] - 26 - bh : p[1] + 26, tw, bh + 8, t, F, {tc:INK, mc:MU, align:'c'});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  C.rect(0, 0, W, H, {fill:TE});
  var cx = 1080, cy = 360, r = 330;
  if (o.img === 'photo') D.imgBox(C, cx - r * 0.78, cy - r * 0.78, r * 1.56, r * 1.56, {shape:'ell'});
  dial(C, cx, cy, r, {color:MI, brass:BR, lc:MI});
  if (o.img !== 'photo') needle(C, cx, cy, r * 0.7, -140, BR, MI);
  if (f.company) C.text(72, 70, 540, 20, f.company, {font:'head', size:14, wt:700, color:BR, caps:true, ls:0.14, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 140, 640, 260, C.s.title, {font:'head', fit:{max:64, min:28}, wt:800, color:WH, lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(72, 420, 70, 4, {fill:BR});
  if (C.s.subtitle) C.text(72, 444, 600, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:'#BFD3D6', lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 590, 600, 22, who, {font:'head', size:14, wt:700, color:WH, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  C.rect(0, 0, W, H, {fill:TE});
  var cx = 940, cy = 360, r = 250;
  if (o.img === 'photo') D.imgBox(C, cx - r * 0.78, cy - r * 0.78, r * 1.56, r * 1.56, {shape:'ell'});
  dial(C, cx, cy, r, {color:MI, brass:BR, lc:MI}); if (o.img !== 'photo') needle(C, cx, cy, r * 0.7, -45, BR, MI);
  C.text(72, 120, 560, 200, C.s.title || 'Thank you', {font:'head', fit:{max:80, min:32}, wt:800, color:WH, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 336, 520, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#BFD3D6', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  C.text(72, 410, 300, 16, 'NEXT HEADING', {font:'head', size:12, wt:800, color:BR, ls:0.14, lh:1});
  D.contacts(C).forEach(function(c, k){ var y = 436 + k * 50; C.ell(72, y, 36, 36, {line:BR, lw:2}); C.icon(80, y + 8, 20, c.icon, WH); C.text(124, y - 2, 480, 40, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:WH, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo';
  var cx = photo ? 470 : 640, cy = 360, r = 290;
  dial(C, cx, cy, r, {labels:false, color:TE, fill:WH});
  if (photo) D.imgBox(C, 830, 90, 380, 540, {r:190});
  var qe = C.text(cx - r * 0.66, 220, r * 1.32, 230, C.s.title, {font:'head', fit:{max:30, min:15}, wt:700, color:TE, align:'c', va:'m', lh:1.22, ed:{f:'title'}, maxLines:7});
  if (by) C.text(cx - r * 0.66, 470, r * 1.32, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:14, wt:700, color:BR, align:'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Bearing cards', rows:'Needle rows', steps:'Heading steps', stmt:'Chart panel', stats:'Number cards', photo:'compass photo', track:'#D4E2E4'};
var VS = K4.vset(pg, B, L, {
  cover:[['Compass dial', {}], ['Compass + photo', {img:'photo'}]],
  closing:[['Next heading', {}], ['Compass + photo', {img:'photo'}]],
  quote:[['Quote in the dial', {}], ['Dial + photo', {img:'photo'}]],
  p4:[['Four directions', pg(quadrants)]],
  stats:[['Dial numbers', pg(dials)]], timeline:[['Route + bearings', pg(routeLine)]]
});
D.register({
  id:'compass', name:'Compass Point', tag:'Deep teal · compass dial',
  desc:'Direction and strategy: a large compass dial with degree ticks, a brass needle, bearings, waypoints and route lines in deep teal and brass. For strategy offsites, leadership, direction-setting and transformation plans.',
  fonts:{head:'Gantari', body:'Nunito', headW:800, bodyW:400, headLh:1.04, gf:'Gantari:wght@500;600;700;800|Nunito:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

