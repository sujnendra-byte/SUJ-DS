
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 75 - Bento Board : soft grey page, rounded tiles of different sizes in pastels, one tomato accent */
var BG = '#F3F4F6', INK = '#0F1115', MU = '#5B6170', WH = '#FFFFFF', BU = '#FFE9A8', SK = '#CFE3FF', MI = '#CDEFD9', LI = '#E7DDFB', TO = '#FF5A3C';
var PAST = [WH, BU, SK, MI, LI];
var P0 = {bg:BG, text:INK, title:INK, muted:MU, accent:TO, accent2:INK, card:WH, line:'#E1E3E8', onAccent:'#FFFFFF'};
function tile(C, x, y, w, h, c, o){ return C.rect(x, y, w, h, {fill:c, r:(o && o.r) || 22}); }
function chip(C, x, y, txt, c, tc){ var w = Math.max(54, String(txt).length * 7.6 + 24); C.rect(x, y, w, 26, {fill:c || TO, r:13}); C.text(x, y, w, 26, txt, {font:'head', size:11.5, wt:700, color:tc || '#FFFFFF', align:'c', va:'m', lh:1, maxLines:1}); return w; }
function bg(C, kind){ C.bg({fill:BG}); }
function head(C, A, o){
  var y = 50;
  var te = C.text(A.x, y, Math.min(A.w, 860), 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:INK, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 8;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:64, tc:INK, mc:MU, ac:INK, markS:44, g:14, rg:10, head:head, ptc:INK, pmc:MU, gap:22,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { tile(C, x, y, w, h, WH); return {tc:INK, mc:MU, ac:TO}; }
    if (o.hot) { tile(C, x, y, w, h, TO); return {tc:'#FFFFFF', mc:'#FFE3DC', ac:'#FFFFFF'}; }
    tile(C, x, y, w, h, PAST[k % 5]); return {tc:INK, mc:MU, ac:INK};
  },
  row:function(C, x, y, w, h, k){ tile(C, x, y, w, h, k % 2 ? WH : '#FAFAFB', {r:16}); return {tc:INK, mc:MU, ac:INK}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.rect(x, y, s, s, {fill:on ? '#FFFFFF' : INK, r:s * 0.3}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? TO : '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ C.rect(x, y, s, s, {fill:PAST[(k + 1) % 5] === WH ? BU : PAST[(k + 1) % 5], r:s * 0.3}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, INK); },
  node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:k === 0 ? TO : INK, r:s * 0.3}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.34, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:'#B9BEC8', lw:2, dash:true}); },
  stepCard:function(C, x, y, w, h, k){ tile(C, x, y, w, h, k === 0 ? '#FFE3DC' : WH, {r:18}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 64, Z.y + 4, W - 128, Z.h - 22, {r:22}); return; }
    D.imgBox(C, Z.x + (side === 'right' ? 10 : 24), 24, Z.w - 34, H - 48, {r:26});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 38; C.text(a.x, y, 400, 16, C.cover.company || '', {font:'head', size:11.5, wt:700, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 60, y, 60, 16, pad2(C.i + 1) + '/' + pad2(C.total), {font:'head', size:11.5, wt:700, color:MU, align:'r', va:'m', lh:1}); }
var S = {margin:64, series:[TO, INK, '#8FB8F2', '#7FCF9E', '#F2C94C', '#B79CF0'], header:{kind:'left', top:50}, card:{kind:'solid', fill:WH, r:22}, icon:{kind:'square', bg:INK, fg:'#FFFFFF', r:12}, num:{kind:'big', fg:TO},
  conc:{kind:'box', fill:WH, r:18, label:'TL;DR', lc:TO, tc:INK}, footer:{fn:foot}, imgFramed:true, imgR:22, bodyGap:22, imgPh:{grad:{ang:135, stops:[[0, '#CFE3FF'], [100, '#7C93C9']]}}};
/* signature: bento mosaic - one hero tile and smaller tiles in mixed sizes */
var MOS = {2:[[0, 0, 7, 2], [7, 0, 5, 2]], 3:[[0, 0, 6, 2], [6, 0, 6, 1], [6, 1, 6, 1]], 4:[[0, 0, 5, 2], [5, 0, 7, 1], [5, 1, 3, 1], [8, 1, 4, 1]], 5:[[0, 0, 4, 2], [4, 0, 4, 1], [8, 0, 4, 1], [4, 1, 5, 1], [9, 1, 3, 1]], 6:[[0, 0, 4, 2], [4, 0, 4, 1], [8, 0, 4, 1], [4, 1, 3, 1], [7, 1, 2, 1], [9, 1, 3, 1]]};
function mosaic(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2) return B.cards(C, Z, o); var M = MOS[n];
  var g = 14, cu = (Z.w - g * 11) / 12, ru = (Z.h - g) / 2;
  it.forEach(function(t, k){
    var m = M[k], x = Z.x + m[0] * (cu + g), y = Z.y + m[1] * (ru + g), w = m[2] * cu + (m[2] - 1) * g, h = m[3] * ru + (m[3] - 1) * g, hero = k === 0, c = hero ? INK : PAST[(k) % 5];
    tile(C, x, y, w, h, c);
    var tc = hero ? '#FFFFFF' : INK, mc = hero ? '#C9CDD6' : MU, pad = 22, s = Math.min(42, h * 0.3);
    C.rect(x + pad, y + pad, s, s, {fill:hero ? TO : (c === WH ? BU : '#FFFFFF'), r:s * 0.3}); K.icon(C, x + pad + s * 0.25, y + pad + s * 0.25, s * 0.5, t, hero ? '#FFFFFF' : INK);
    var tw = w - pad * 2, F = K.fitHD(C, [t], tw, hero ? 70 : 46, h - pad * 2 - s - 14, {hMax:hero ? 32 : 23, dMax:hero ? 19 : 16.5, hMin:12, dMin:9.5, noBoost:true});
    var bh = K3.bH(C, t, tw, F); K.hd(C, x + pad, Math.max(y + pad + s + 12, y + h - pad - bh), tw, bh + 6, t, F, {tc:tc, mc:mc});
  });
}
/* signature: KPI tiles with a sparkline and change pill */
function kpis(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 14, cw = (Z.w - g * (n - 1)) / n, vals = st.map(K3.sv), labs = st.map(K3.sl), vs = C.groupFit(vals, cw - 44, 80, {font:'head', max:60, min:22, maxLines:1});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y, hot = k === 0, c = hot ? TO : PAST[k % 5];
    tile(C, x, y, cw, Z.h, c);
    var tc = hot ? '#FFFFFF' : INK, mc = hot ? '#FFE3DC' : MU;
    C.text(x + 22, y + 22, cw - 44, 40, labs[k], {font:'head', size:15, wt:700, color:tc, lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    C.text(x + 22, y + Z.h * 0.3, cw - 44, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:tc, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var sy = y + Z.h * 0.3 + vs * 1.1 + 26, pts = [0, 0.4, 0.25, 0.6, 0.5, 0.9].map(function(v, i){ return [x + 22 + i * (cw - 44) / 5, sy + 50 - v * 46 * (k % 2 ? 0.7 : 1)]; });
    C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; }), {stroke:hot ? '#FFFFFF' : TO, lw:3});
    if (t.x) C.text(x + 22, sy + 68, cw - 44, y + Z.h - sy - 80, t.x, {font:'body', size:13.5, color:mc, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, g = 16;
  tile(C, 40, 40, 730, 430, WH);
  C.text(80, 90, 650, 250, C.s.title, {font:'head', fit:{max:66, min:28}, wt:800, color:INK, lh:1.02, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(80, 356, 620, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  if (o.img === 'photo') D.imgBox(C, 786, 40, 454, 430, {r:22}); else { tile(C, 786, 40, 454, 430, SK); C.ell(900, 140, 230, 230, {fill:'#FFFFFF', op:0.6}); C.rect(960, 200, 110, 110, {fill:TO, r:30}); }
  tile(C, 40, 486, 300, 194, BU); C.text(68, 510, 260, 30, f.date || 'This quarter', {font:'head', size:15, wt:700, color:INK, lh:1, va:'m', maxLines:1}); C.text(68, 560, 260, 90, f.company || '', {font:'head', fit:{max:34, min:16}, wt:800, color:INK, lh:1.05, ed:{f:'tf-company'}, maxLines:2});
  tile(C, 356, 486, 414, 194, INK); C.text(386, 520, 360, 20, 'Presented by', {font:'body', size:13, color:'#C9CDD6', lh:1}); C.text(386, 548, 360, 80, [f.presenter, f.designation].filter(Boolean).join(' · '), {font:'head', fit:{max:22, min:13}, wt:700, color:'#FFFFFF', lh:1.25, ed:{f:'tf-presenter'}, maxLines:3});
  tile(C, 786, 486, 454, 194, TO); C.text(816, 486, 400, 194, f.website || '', {font:'head', fit:{max:22, min:12}, wt:700, color:'#FFFFFF', va:'m', lh:1.2, ed:{f:'tf-website'}, maxLines:2});
}
function closing(C, v){
  var o = C.o || {}, cs = D.contacts(C);
  tile(C, 40, 40, 780, 400, INK);
  C.text(84, 80, 700, 230, C.s.title || 'Thank you', {font:'head', fit:{max:90, min:36}, wt:800, color:'#FFFFFF', lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(84, 330, 680, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:'#C9CDD6', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  if (o.img === 'photo') D.imgBox(C, 836, 40, 404, 400, {r:22}); else { tile(C, 836, 40, 404, 400, TO); C.icon(978, 180, 120, 'mdi:hand-wave-outline', '#FFFFFF'); }
  var n = Math.max(1, cs.length), cw = (1200 - 16 * (n - 1)) / n;
  cs.forEach(function(c, k){ var x = 40 + k * (cw + 16); tile(C, x, 456, cw, 224, PAST[(k + 1) % 5]); C.rect(x + 24, 480, 48, 48, {fill:INK, r:14}); C.icon(x + 36, 492, 24, c.icon, '#FFFFFF'); C.text(x + 24, 560, cw - 48, 90, c.val, {font:'head', fit:{max:20, min:12}, wt:700, color:INK, va:'b', lh:1.2, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:2}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 760 : 1200;
  tile(C, 40, 40, w, 640, WH);
  C.text(80, 70, 120, 120, '“', {font:'head', size:150, wt:800, color:TO, lh:1, caps:false});
  var qe = C.text(84, 200, w - 90, 330, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:700, color:INK, lh:1.18, ed:{f:'title'}, maxLines:6});
  if (by) chip(C, 84, 560, by.h ? by.h + ' — ' + by.d : by.d, INK);
  if (photo) D.imgBox(C, 816, 40, 424, 640, {r:22});
}
var L = {cards:'Pastel tiles', rows:'Tile rows', steps:'Step tiles', stmt:'Statement tile', stats:'Number tiles', photo:'photo tile', track:'#E1E3E8'};
var VS = K4.vset(pg, B, L, {
  cover:[['Bento cover', {}], ['Bento + photo tile', {img:'photo'}]],
  closing:[['Bento thank you', {}], ['Bento + photo tile', {img:'photo'}]],
  quote:[['Quote tile', {}], ['Quote + photo tile', {img:'photo'}]],
  agenda:[['Bento mosaic', pg(mosaic)]],
  p2:[['Bento mosaic', pg(mosaic)]], p3:[['Bento mosaic', pg(mosaic)]], p4:[['Bento mosaic', pg(mosaic)]], p56:[['Bento mosaic', pg(mosaic)]],
  stats:[['KPI tiles', pg(kpis)]], timeline:[['Bento mosaic', pg(mosaic)]]
});
D.register({
  id:'bento', name:'Bento Board', tag:'Soft grey · bento tiles',
  desc:'A modern product-style bento grid: rounded tiles of different sizes in soft pastels with one tomato accent, KPI tiles with sparklines and pill chips. For SaaS, product updates, startups and quarterly reviews.',
  fonts:{head:'Urbanist', body:'Figtree', headW:800, bodyW:400, headLh:1.04, gf:'Urbanist:wght@600;700;800|Figtree:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

