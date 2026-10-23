
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2, RAD = Math.PI / 180;
/* Template 77 - Contour : sage-white page, topographic contour lines, survey pins, olive / moss / sand */
var BG = '#F1F3EE', OL = '#4E5B31', MO = '#7C8B4F', CH = '#262A22', SA = '#C9B58A', MU = '#5F6455', PA = '#E4E8DC';
var P0 = {bg:BG, text:CH, title:CH, muted:MU, accent:OL, accent2:SA, card:'#FFFFFF', line:'#D3D8C8', onAccent:'#FFFFFF'};
/* one closed contour: a wobbly ring around (cx,cy) */
function ringPath(cx, cy, rx, ry, seed, rot){
  var d = [], n = 40, cr = Math.cos(rot * RAD), sr = Math.sin(rot * RAD);
  for (var i = 0; i <= n; i++) { var a = i / n * Math.PI * 2, w = 1 + 0.08 * Math.sin(a * 3 + seed) + 0.05 * Math.sin(a * 5 + seed * 2.1), x = Math.cos(a) * rx * w, y = Math.sin(a) * ry * w; d.push([i ? 'L' : 'M', cx + x * cr - y * sr, cy + x * sr + y * cr]); }
  d.push(['Z']); return d;
}
function topo(C, cx, cy, n, step, c, op, seed){ for (var i = 0; i < n; i++) C.path(ringPath(cx, cy, 24 + i * step * 1.25, 16 + i * step, (seed || 1) + i * 0.35, -16), {stroke:c || MO, lw:1.4, lop:(op || 0.9) * (1 - i / (n + 2))}); }
function pin(C, x, y, s, c, txt){
  C.path([['M', x, y], ['C', x - s * 0.1, y - s * 0.35, x - s * 0.5, y - s * 0.55, x - s * 0.5, y - s * 0.95], ['C', x - s * 0.5, y - s * 1.25, x - s * 0.25, y - s * 1.45, x, y - s * 1.45], ['C', x + s * 0.25, y - s * 1.45, x + s * 0.5, y - s * 1.25, x + s * 0.5, y - s * 0.95], ['C', x + s * 0.5, y - s * 0.55, x + s * 0.1, y - s * 0.35, x, y], ['Z']], {fill:c || OL});
  if (txt != null) C.text(x - s * 0.5, y - s * 1.35, s, s * 0.8, txt, {font:'head', size:s * 0.36, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
  else C.ell(x - s * 0.18, y - s * 1.13, s * 0.36, s * 0.36, {fill:'#FFFFFF'});
}
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  topo(C, k ? W + 40 : W - 30, k ? H + 30 : -40, 9, 22, MO, 0.55, C.i || 1);
}
function head(C, A, o){
  var y = 50, f = C.cover;
  C.text(A.x, y, A.w, 16, (f.company ? f.company + '  ·  ' : '') + 'Sheet ' + pad2(C.i + 1), {font:'body', size:11.5, wt:700, color:MO, caps:true, ls:0.14, lh:1, va:'m', maxLines:1});
  y += 24;
  var te = C.text(A.x, y, Math.min(A.w, 860), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:CH, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:CH, mc:MU, ac:OL, markS:44, g:18, head:head, ptc:CH, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:'#FFFFFF', r:10, line:'#D3D8C8', lw:1.2}); return {tc:CH, mc:MU, ac:OL}; }
    if (o.hot || (k === 0 && o.stat)) { C.rect(x, y, w, h, {fill:OL, r:10}); return {tc:'#FFFFFF', mc:'#DCE3CC', ac:SA}; }
    C.rect(x, y, w, h, {fill:'#FFFFFF', r:10, line:'#D3D8C8', lw:1.2}); C.rect(x, y, w, 8, {fill:k % 2 ? SA : MO, r:4});
    return {tc:CH, mc:MU, ac:OL};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:'#D3D8C8'}); return {tc:CH, mc:MU, ac:OL}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.ell(x, y, s, s, {fill:on ? SA : PA}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? CH : OL); },
  rowMark:function(C, x, y, s, t, k){ pin(C, x + s / 2, y + s, s * 0.7, k % 2 ? MO : OL, String(k + 1)); },
  node:function(C, cx, cy, s, k){ pin(C, cx, cy + s * 0.5, s * 0.8, k % 2 ? MO : OL, String(k + 1)); },
  track:function(C, x1, y, x2){ C.path([['M', x1, y + 24], ['C', x1 + (x2 - x1) * 0.25, y - 10, x1 + (x2 - x1) * 0.5, y + 40, x1 + (x2 - x1) * 0.75, y + 10], ['L', x2, y + 20]], {stroke:SA, lw:3}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 0, Z.y, W, Z.h, {r:0}); topo(C, W * 0.8, Z.y + Z.h * 0.6, 6, 20, '#FFFFFF', 0.7, 2); return; }
    D.imgBox(C, Z.x, 0, Z.w, H, {r:0});
    topo(C, Z.x + Z.w * (side === 'right' ? 0.3 : 0.7), H * 0.55, 8, 26, '#FFFFFF', 0.75, 3);
    pin(C, Z.x + Z.w / 2, H * 0.55, 46, SA);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.website || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 160, y, 160, 16, 'N 12°52′ · ' + pad2(C.i + 1), {font:'head', size:11.5, wt:700, color:MO, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[OL, MO, SA, '#A9B48A', '#262A22', '#D9CBA8'], header:{kind:'left', top:50}, card:{kind:'outline', r:10}, icon:{kind:'plain', fg:OL}, num:{kind:'big', fg:OL},
  conc:{kind:'bar', barC:SA, label:'Survey note', lc:OL, tc:CH}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#A9B48A'], [100, '#4E5B31']]}}};
/* signature: elevation profile - a ridge line across the slide, one peak per number */
function profile(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, top = Z.y + 10, base = Z.y + Z.h * 0.5, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 24;
  var d = [['M', Z.x - 72, base]];
  st.forEach(function(t, k){ var px = Z.x + cw * (k + 0.5), py = top + 30 + (k % 2) * 40; d.push(['C', px - cw * 0.3, base - 10, px - cw * 0.2, py, px, py]); d.push(['C', px + cw * 0.2, py, px + cw * 0.3, base - 10, Z.x + cw * (k + 1), base - 20]); });
  d.push(['L', W + 10, base]); var fill = d.concat([['L', W + 10, base + 30], ['L', Z.x - 72, base + 30], ['Z']]);
  C.path(fill, {fill:MO, op:0.18}); C.path(d, {stroke:OL, lw:3});
  var vs = C.groupFit(vals, tw, 60, {font:'head', max:52, min:20, maxLines:1}), ls = C.groupFit(labs, tw, 48, {font:'head', max:18, min:12, maxLines:2});
  st.forEach(function(t, k){ var px = Z.x + cw * (k + 0.5), py = top + 30 + (k % 2) * 40;
    C.ell(px - 8, py - 8, 16, 16, {fill:SA, line:CH, lw:2});
    C.text(px - tw / 2, base + 44, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:CH, align:'c', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(px - tw / 2, base + 50 + vs * 1.1, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:OL, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(px - tw / 2, base + 104 + vs * 1.1, tw, Z.y + Z.h - (base + 104 + vs * 1.1), t.x, {font:'body', size:13.5, color:MU, align:'c', lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* signature: survey map - numbered pins on a contour map beside the list */
function survey(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n || Z.w < 760) return B.rows(C, Z, o);
  var mw = Math.min(460, Z.w * 0.42), mx = Z.x, my = Z.y, mh = Z.h;
  C.rect(mx, my, mw, mh, {fill:PA, r:12});
  topo(C, mx + mw * 0.45, my + mh * 0.5, 9, Math.min(mw, mh) / 22, MO, 0.9, 5);
  var spots = [[0.3, 0.3], [0.68, 0.24], [0.5, 0.58], [0.22, 0.72], [0.78, 0.66], [0.55, 0.86]];
  it.forEach(function(t, k){ pin(C, mx + mw * spots[k][0], my + mh * spots[k][1], 34, k % 2 ? MO : OL, String(k + 1)); });
  var lx = mx + mw + 36, lw = Z.x + Z.w - lx, rh = Z.h / n, s = 30, tw = lw - s - 16;
  var F = K.fitHD(C, it, tw, 34, rh - 8, {hMax:20, dMax:15.5, hMin:12, dMin:10, noBoost:n > 3});
  it.forEach(function(t, k){ var y = Z.y + k * rh, bh = K3.bH(C, t, tw, F); C.ell(lx, y + (rh - s) / 2, s, s, {fill:k % 2 ? MO : OL}); C.text(lx, y + (rh - s) / 2, s, s, String(k + 1), {font:'head', size:13, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); K.hd(C, lx + s + 16, y + (rh - bh) / 2, tw, rh, t, F, {tc:CH, mc:MU}); });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) { D.imgBox(C, 620, 0, 660, H, {r:0}); topo(C, 930, 360, 12, 24, '#FFFFFF', 0.85, 2); pin(C, 930, 380, 60, SA); }
  else { topo(C, 940, 340, 15, 22, MO, 1, 2); topo(C, 1180, 640, 5, 18, OL, 0.6, 7); pin(C, 940, 360, 60, OL); }
  if (f.company) C.text(72, 60, 520, 20, f.company, {font:'head', size:13, wt:800, color:OL, caps:true, ls:0.14, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 92, 520, 16, 'N 12°52′ · E 74°50′', {font:'body', size:12, wt:600, color:MO, lh:1});
  C.text(72, 140, 540, 260, C.s.title, {font:'head', fit:{max:64, min:28}, wt:800, color:CH, lh:1.04, va:'b', ed:{f:'title'}, maxLines:4});
  C.rect(72, 418, 70, 5, {fill:SA});
  if (C.s.subtitle) C.text(72, 440, 520, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 580, 520, 24, who, {font:'head', size:15, wt:700, color:CH, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
  if (f.website) C.text(72, 630, 400, 18, f.website, {font:'body', size:12.5, color:OL, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {};
  if (o.img === 'photo') { D.imgBox(C, 680, 0, 600, H, {r:0}); topo(C, 980, 330, 10, 24, '#FFFFFF', 0.85, 4); pin(C, 980, 350, 70, SA); }
  else { topo(C, 980, 330, 14, 22, MO, 1, 4); pin(C, 980, 350, 70, OL); C.text(900, 380, 160, 24, 'You are here', {font:'head', size:14, wt:800, color:CH, align:'c', lh:1}); }
  C.text(72, 130, 580, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:800, color:CH, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 350, 540, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 440 + k * 54; C.ell(72, y, 40, 40, {fill:k % 2 ? MO : OL}); C.icon(82, y + 10, 20, c.icon, '#FFFFFF'); C.text(128, y, 500, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:CH, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 620 : 900;
  topo(C, 640, 360, 14, 26, MO, 0.5, 6);
  if (photo) D.imgBox(C, 800, 0, 480, H, {r:0});
  C.rect(72, 140, w, 400, {fill:SA, r:14});
  C.text(110, 150, 100, 100, '“', {font:'head', size:120, wt:800, color:'#FFFFFF', lh:1, caps:false});
  var qe = C.text(110, 250, w - 76, 220, C.s.title, {font:'head', fit:{max:40, min:18}, wt:700, color:CH, lh:1.2, ed:{f:'title'}, maxLines:6});
  if (by) C.text(110, 488, w - 76, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:OL, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Survey cards', rows:'Pin rows', steps:'Trail waypoints', stmt:'Field note', stats:'Number cards', photo:'contour photo', track:'#E4E8DC'};
var VS = K4.vset(pg, B, L, {
  cover:[['Contour map + pin', {}], ['Contour photo', {img:'photo'}]],
  closing:[['You are here', {}], ['Contour photo', {img:'photo'}]],
  quote:[['Sand panel quote', {}], ['Quote + photo', {img:'photo'}]],
  agenda:[['Survey map', pg(survey)]], p3:[['Survey map', pg(survey)]], p4:[['Survey map', pg(survey)]], p56:[['Survey map', pg(survey)]],
  stats:[['Elevation profile', pg(profile)]]
});
D.register({
  id:'contour', name:'Contour', tag:'Sage white · topographic lines',
  desc:'Topographic contour lines, survey pins and an elevation-profile chart in olive, moss and sand. Grounded and professional for real estate, energy, agriculture, mining and infrastructure.',
  fonts:{head:'Bricolage Grotesque', body:'Public Sans', headW:800, bodyW:400, headLh:1.04, gf:'Bricolage+Grotesque:wght@600;700;800|Public+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

