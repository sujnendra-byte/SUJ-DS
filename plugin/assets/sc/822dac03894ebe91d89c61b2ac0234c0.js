
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 79 - Ticker : dark trading terminal, ticker band, green up / red down, mono numbers, sparklines */
var BG = '#121417', PN = '#1C1F24', PN2 = '#23272E', TX = '#E9EDF2', MU = '#8A93A3', UP = '#2BD67B', DN = '#FF5C5C', AM = '#FFC24B', LN = '#2C313A';
var P0 = {bg:BG, text:TX, title:TX, muted:MU, accent:UP, accent2:AM, card:PN, line:LN, onAccent:'#0B0D10'};
function spark(C, x, y, w, h, up, seed, c){ var p = [], n = 9; for (var i = 0; i < n; i++) { var v = 0.5 + 0.32 * Math.sin(i * 1.3 + seed) + (up ? 1 : -1) * (i / n) * 0.45; p.push([x + i * w / (n - 1), y + h - Math.max(0, Math.min(1, v)) * h]); } C.path(p.map(function(q, i){ return [i ? 'L' : 'M', q[0], q[1]]; }), {stroke:c || (up ? UP : DN), lw:2.5}); }
function band(C, y){
  C.rect(0, y, W, 34, {fill:PN});
  var f = C.cover, base = (f.company || 'MARKET').replace(/[^A-Za-z]/g, '').toUpperCase().slice(0, 4) || 'MKT';
  var items = [[base, '+2.4%', 1], ['GRW', '+1.1%', 1], ['RSK', '-0.8%', 0], ['EBIT', '+3.6%', 1], ['CASH', '+0.9%', 1], ['FX', '-0.3%', 0]];
  items.forEach(function(it, k){ var x = 30 + k * 205; C.text(x, y, 70, 34, it[0], {font:'body', size:12, wt:700, color:TX, va:'m', lh:1, maxLines:1}); C.text(x + 66, y, 100, 34, (it[2] ? '▲ ' : '▼ ') + it[1], {font:'body', size:12, wt:700, color:it[2] ? UP : DN, va:'m', lh:1, maxLines:1}); });
}
function bg(C, kind){ C.bg({fill:BG}); if (kind === 'cover' || kind === 'closing') return; band(C, 0); }
function head(C, A, o){
  var y = 62;
  C.text(A.x, y, A.w, 16, '> ' + (C.cover.company || 'report').toLowerCase() + ' / ' + pad2(C.i + 1), {font:'body', size:12, wt:600, color:AM, lh:1, va:'m', maxLines:1});
  y += 24;
  var te = C.text(A.x, y, Math.min(A.w, 900), 90, C.s.title || '', {font:'head', fit:{max:38, min:22}, wt:700, color:TX, lh:1.08, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 780), 44, C.s.subtitle, {font:'head', fit:{max:15, min:12}, wt:400, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:64, tc:TX, mc:MU, ac:UP, markS:42, g:14, rg:8, head:head, ptc:TX, pmc:MU, gap:22,
  card:function(C, x, y, w, h, k, o){
    C.rect(x, y, w, h, {fill:o.hot ? PN2 : PN, r:8, line:LN, lw:1});
    if (!o.big && !o.stat) spark(C, x + w - 90, y + 18, 70, 26, k % 3 !== 1, k);
    if (o.big) { C.ell(x + 16, y + 14, 9, 9, {fill:DN}); C.ell(x + 30, y + 14, 9, 9, {fill:AM}); C.ell(x + 44, y + 14, 9, 9, {fill:UP}); }
    return {tc:TX, mc:MU, ac:k % 3 === 1 && o.stat ? DN : UP};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? BG : PN, r:6}); spark(C, x + w - 110, y + h * 0.25, 90, h * 0.5, k % 3 !== 1, k + 2); return {tc:TX, mc:MU, ac:UP}; },
  mark:function(C, x, y, s, t, k, cc){ C.rect(x, y, s, s, {fill:PN2, r:6, line:LN, lw:1}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, k % 3 === 1 ? AM : UP); },
  rowMark:function(C, x, y, s, t, k){ var up = k % 3 !== 1; C.rect(x, y + s * 0.2, s, s * 0.6, {fill:up ? UP : DN, r:4, op:0.18}); C.text(x, y + s * 0.2, s, s * 0.6, (up ? '▲' : '▼'), {font:'body', size:s * 0.3, wt:700, color:up ? UP : DN, align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ var h = s * (0.5 + 0.12 * k), up = k % 3 !== 1; C.rect(cx - 2, cy - h / 2 - 10, 4, h + 20, {fill:up ? UP : DN}); C.rect(cx - s * 0.22, cy - h / 2, s * 0.44, h, {fill:up ? UP : DN}); },
  track:function(C, x1, y, x2){ C.line(x1 - 60, y, x2 + 60, y, {color:LN, lw:1.5, dash:true}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 64, Z.y + 6, W - 128, Z.h - 24, {r:8}); return; }
    var x = Z.x + (side === 'right' ? 0 : 30), w = Z.w - 30;
    C.rect(x, 50, w, H - 100, {fill:PN, r:10, line:LN, lw:1});
    C.ell(x + 16, 64, 9, 9, {fill:DN}); C.ell(x + 30, 64, 9, 9, {fill:AM}); C.ell(x + 44, 64, 9, 9, {fill:UP});
    D.imgBox(C, x + 12, 86, w - 24, H - 148, {r:6});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 36; C.text(a.x, y, 520, 16, (C.cover.website || '') , {font:'body', size:11, wt:600, color:MU, lh:1, va:'m'}); C.text(a.x + a.w - 120, y, 120, 16, 'p.' + pad2(C.i + 1) + ' ● LIVE', {font:'body', size:11, wt:700, color:UP, align:'r', va:'m', lh:1}); }
var S = {margin:64, series:[UP, AM, DN, '#5AA9FF', '#B48CFF', '#8A93A3'], header:{kind:'left', top:62}, card:{kind:'solid', fill:PN, r:8}, icon:{kind:'plain', fg:UP}, num:{kind:'big', fg:UP},
  conc:{kind:'box', fill:PN2, r:8, label:'Signal', lc:AM, tc:TX}, footer:{fn:foot}, imgFramed:true, imgR:6, bodyGap:22, imgPh:{grad:{ang:135, stops:[[0, '#2A3B4F'], [100, '#121417']]}}};
/* signature: KPI terminal tiles - mono number, change pill and sparkline */
function kpi(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 14, cw = (Z.w - g * (n - 1)) / n, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 40;
  var vs = C.groupFit(vals, tw, 80, {font:'body', max:54, min:20, maxLines:1});
  st.forEach(function(t, k){ var x = Z.x + k * (cw + g), up = !/^-/.test(vals[k]) && !/(down|fell|drop|lower)/i.test(t.x || '');
    C.rect(x, Z.y, cw, Z.h, {fill:PN, r:8, line:LN, lw:1});
    C.text(x + 20, Z.y + 20, tw, 36, labs[k].toUpperCase(), {font:'body', size:12, wt:700, color:MU, ls:0.08, lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    C.text(x + 20, Z.y + Z.h * 0.26, tw, vs * 1.1, vals[k], {font:'body', size:vs, wt:700, color:TX, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var py = Z.y + Z.h * 0.26 + vs * 1.1 + 14; C.rect(x + 20, py, 84, 26, {fill:up ? UP : DN, op:0.18, r:13}); C.text(x + 20, py, 84, 26, up ? '▲ UP' : '▼ DOWN', {font:'body', size:11.5, wt:700, color:up ? UP : DN, align:'c', va:'m', lh:1});
    spark(C, x + 20, py + 44, tw, Math.max(30, Z.h * 0.18), up, k + 1);
    if (t.x) C.text(x + 20, py + 56 + Math.max(30, Z.h * 0.18), tw, Z.y + Z.h - (py + 66 + Math.max(30, Z.h * 0.18)), t.x, {font:'head', size:13.5, wt:400, color:MU, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* signature: bull vs bear - two panels, up and down */
function bullbear(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return B.cards(C, Z, o);
  var g = 18, cw = (Z.w - g) / 2, tw = cw - 60;
  var F = K.fitHD(C, it, tw, 70, Z.h - 150, {hMax:28, dMax:18, hMin:14, dMin:11});
  it.forEach(function(t, k){ var x = Z.x + k * (cw + g), up = k === 0, c = up ? UP : DN;
    C.rect(x, Z.y, cw, Z.h, {fill:PN, r:10, line:c, lw:1.5});
    C.text(x + 30, Z.y + 24, 200, 60, up ? '▲' : '▼', {font:'body', size:46, wt:700, color:c, lh:1});
    spark(C, x + cw - 200, Z.y + 30, 170, 60, up, k + 3);
    K.hd(C, x + 30, Z.y + 110, tw, Z.h - 130, t, F, {tc:TX, mc:MU});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function candles(C, x0, base, n, w, seed){
  for (var i = 0; i < n; i++) { var up = (i * 7 + seed) % 3 !== 1, h = 40 + ((i * 37 + seed * 11) % 9) * 14 + i * 9, x = x0 + i * w, y = base - h; C.rect(x + w * 0.45, y - 16, w * 0.1, h + 32, {fill:up ? UP : DN}); C.rect(x + w * 0.2, y, w * 0.6, h * 0.62, {fill:up ? UP : DN}); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  band(C, 0);
  if (photo) { D.imgBox(C, 720, 70, 500, 400, {r:10}); C.rect(712, 62, 516, 416, {line:LN, lw:1, r:12}); }
  candles(C, photo ? 64 : 640, H - 50, photo ? 20 : 11, photo ? 58 : 54, 2);
  if (f.company) C.text(64, 82, 600, 18, '> ' + f.company.toLowerCase(), {font:'body', size:13, wt:600, color:AM, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(64, 116, photo ? 620 : 600, 220, C.s.title, {font:'head', fit:{max:62, min:28}, wt:700, color:TX, lh:1.04, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(64, 350, photo ? 600 : 560, 70, C.s.subtitle, {font:'head', fit:{max:18, min:12.5}, wt:400, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(64, 436, 560, 22, who, {font:'body', size:14, wt:700, color:TX, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  band(C, 0); band(C, H - 34);
  if (o.img === 'photo') D.imgBox(C, 760, 80, 460, 560, {r:10}); else candles(C, 760, H - 70, 8, 58, 5);
  C.text(64, 100, 640, 180, C.s.title || 'Thank you', {font:'head', fit:{max:76, min:32}, wt:700, color:TX, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(64, 296, 620, 50, C.s.subtitle, {font:'head', fit:{max:17, min:12}, wt:400, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  C.text(64, 372, 300, 16, 'ORDER BOOK', {font:'body', size:11, wt:700, color:MU, ls:0.12, lh:1});
  D.contacts(C).forEach(function(c, k){ var y = 396 + k * 48; C.rect(64, y, 620, 40, {fill:k % 2 ? BG : PN, r:6}); C.icon(80, y + 10, 20, c.icon, UP); C.text(116, y, 560, 40, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:TX, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 700 : W - 128;
  if (photo) D.imgBox(C, 800, 60, 416, H - 120, {r:10});
  C.rect(64, 80, w, H - 160, {fill:PN, r:10, line:LN, lw:1});
  C.ell(84, 98, 10, 10, {fill:DN}); C.ell(100, 98, 10, 10, {fill:AM}); C.ell(116, 98, 10, 10, {fill:UP});
  C.text(96, 150, 60, 40, '> _', {font:'body', size:22, wt:700, color:UP, lh:1});
  var qe = C.text(96, 210, w - 64, 250, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:600, color:TX, lh:1.25, ed:{f:'title'}, maxLines:6});
  if (by) C.text(96, H - 140, w - 64, 24, '— ' + (by.h ? by.h + ', ' + by.d : by.d), {font:'body', size:14, wt:700, color:AM, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Market cards', rows:'Watchlist rows', steps:'Candle steps', stmt:'Terminal panel', stats:'Terminal numbers', photo:'screen photo', track:'#23272E'};
var VS = K4.vset(pg, B, L, {
  cover:[['Ticker + candles', {}], ['Ticker + screen photo', {img:'photo'}]],
  closing:[['Order book', {}], ['Order book + photo', {img:'photo'}]],
  quote:[['Terminal quote', {}], ['Terminal + photo', {img:'photo'}]],
  p2:[['Bull vs bear', pg(bullbear)]],
  stats:[['KPI terminal', pg(kpi)]]
});
D.register({
  id:'ticker', name:'Ticker', tag:'Dark terminal · market signals',
  desc:'A trading-floor look: dark terminal panels, a live ticker band, green-up and red-down signals, sparklines and mono numbers. For fintech, trading, analytics, investor updates and market reports.',
  fonts:{head:'IBM Plex Sans', body:'IBM Plex Mono', headW:700, bodyW:500, headLh:1.08, gf:'IBM+Plex+Sans:wght@400;500;600;700|IBM+Plex+Mono:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

