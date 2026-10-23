
/* DesignStager template: embernoir */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 37 - Ember Noir : warm near-black page, ember glow, smoked glass bands with an amber rim */
var AM = '#FFB347', CR = '#FF6B4A', INK = '#FFF6EC', MUT = '#C9B8A8', BG0 = '#140E0C';
var P0 = {bg:BG0, text:INK, title:INK, muted:MUT, accent:AM, accent2:CR, card:'#241A16', line:'#4A372E', onAccent:'#1A0F0A'};
var S = {
  margin:76, series:[AM, CR, '#FFD9A0', '#E0527A', '#F2E3D0', '#A8623C'],
  header:{kind:'left', top:62, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return pad2(C.i + 1) + '  /  ' + (C.cover.company || ''); }, kc:AM, kGap:26, kSize:11.5},
  card:{kind:'glass', r:18, fill:'#FFFFFF', op:0.07, line:'#FFFFFF', lop:0.22, lw:1, shadow:0, dark:true, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:AM, dark:false, tc:'#1A0F0A', mc:'#4A2E14'}},
  icon:{kind:'circle', bg:AM, fg:'#1A0F0A'}, num:{kind:'big', fg:AM},
  conc:{kind:'bar', barC:AM, label:'In short', lc:AM, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:18, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#5A3320'], [100, '#2A1810']]}}
};
function smoke(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 18;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:120, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}, r:r}); C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#FFFFFF', 0.35], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#1A0F0A', mc:'#4A2414', ac:'#1A0F0A'}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.13], [100, '#FFFFFF', 0.03]]}, line:'#FFD9A0', lop:0.28, lw:1, r:r});
  C.rect(x + r, y, w - r * 2, 1, {grad:{ang:0, stops:[[0, '#FFFFFF', 0], [50, '#FFFFFF', 0.7], [100, '#FFFFFF', 0]]}});
  return {tc:INK, mc:MUT, ac:AM};
}
function foot(C){
  var M = 76, y = H - 36, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 1, {fill:'#FFFFFF', op:0.12});
  if (f.website || f.company) C.text(M, y, 500, 16, f.website || f.company, {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.06});
  C.text(W - M - 120, y, 120, 16, pad2(C.i + 1) + ' / ' + pad2(C.total), {font:'body', size:11, wt:700, color:AM, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:155, stops:[[0, '#1E1410'], [100, '#0C0807']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.92, W * 0.05, W * 0.6][k], [H * 0.95, H * 0.9, -H * 0.05][k], 760, 520, CR, 0.34);
  D.glow(C, [W * 0.75, W * 0.25, W * 0.85][k], [H * 1.02, H * 1.05, H * 0.02][k], 520, 360, AM, 0.3);
  if (kind === 'cover' || kind === 'closing') return;
  var ex = [W - 150, -90, W - 210][k], ey = [H - 230, H - 210, -110][k];
  C.ell(ex, ey, 300, 300, {grad:{ang:135, stops:[[0, '#FFC978', 0.5], [100, '#FF6B4A', 0.05]]}, line:'#FFD9A0', lop:0.35, lw:1});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* full-width smoked glass bands, an amber numeral disc on the left edge */
function bands(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var g = 14, rh = Math.min((Z.h - g * (n - 1)) / n, 150), dd = Math.min(60, rh - 22), pad = 24, tw = Z.w - pad * 2 - dd - 22;
  var F = K.fitHD(C, it, tw, 30, rh - 24, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, dd) + 34); var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), hot = o.hl && k === 0, cc = smoke(C, Z.x, y, Z.w, rh, {hot:hot, r:rh / 2 > 40 ? 40 : rh / 2});
    C.ell(Z.x + pad - 6, y + (rh - dd) / 2, dd, dd, hot ? {fill:'#1A0F0A'} : {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}});
    C.text(Z.x + pad - 6, y + (rh - dd) / 2, dd, dd, pad2(k + 1), {font:'head', size:dd * 0.36, wt:700, color:hot ? AM : '#1A0F0A', align:'c', va:'m', lh:1, caps:false});
    var bh = blockH(C, t, tw, F);
    K.hd(C, Z.x + pad + dd + 16, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* staircase: glass cards that step down from left to right */
function stairs(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return tiles(C, Z, o);
  var g = 16, cw = (Z.w - g * (n - 1)) / n, step = Math.min(34, Z.h * 0.08), ch = Z.h - step * (n - 1), pad = 24, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - 66, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), y = Z.y + k * step, hot = o.hl && k === n - 1, cc = smoke(C, x, y, cw, ch, {hot:hot});
    C.text(x + pad, y + pad - 4, tw, 50, pad2(k + 1), {font:'head', size:42, wt:300, color:cc.ac, lh:1, caps:false, op:hot ? 1 : 0.95});
    C.rect(x + pad, y + pad + 52, 34, 2, {fill:cc.ac, op:0.8});
    K.hd(C, x + pad, y + pad + 70, tw, ch - pad * 2 - 70, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* glass tiles with an outlined amber icon ring */
function tiles(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22, ic = 44;
  var side = rows > 1 || ch < 230, tw = side ? cw - pad * 2 - ic - 16 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - ic - 18, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 18) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), cc = smoke(C, x, y, cw, ch2, {});
    C.ell(x + pad, y + pad, ic, ic, {fill:AM, op:0.14, line:AM, lop:0.9, lw:1.25});
    C.icon(x + pad + 12, y + pad + 12, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), AM);
    if (side) K.hd(C, x + pad + ic + 16, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
    else K.hd(C, x + pad, y + pad + ic + 18, tw, ch2 - pad * 2 - ic - 18, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: tall glass pillars of rising height, the number on top */
function pillars(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var g = 18, cw = (Z.w - g * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), pad = 20, tw = cw - pad * 2;
  var vs = C.groupFit(vals, tw, 70, {font:'head', max:58, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:17, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, 80, {font:'body', max:13.5, min:10.5, lh:1.36});
  var minH = Math.min(Z.h, Math.max(Z.h * 0.62, 210));
  st.forEach(function(t, k){
    var ph = minH + (Z.h - minH) * (n > 1 ? k / (n - 1) : 1), x = Z.x + k * (cw + g), y = Z.y + Z.h - ph, hot = k === n - 1, cc = smoke(C, x, y, cw, ph, {hot:hot});
    C.text(x + pad, y + pad, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:300, color:cc.ac, lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + vs * 1.1 + 10, 30, 2, {fill:cc.ac, op:0.8});
    var le = C.text(x + pad, y + pad + vs * 1.1 + 24, tw, 46, labs[k], {font:'head', size:ls, wt:600, color:cc.tc, lh:1.16, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, y + pad + vs * 1.1 + 30 + le._h, tw, ph - pad * 2 - vs * 1.1 - 30 - le._h, t.x, {font:'body', size:ds, color:cc.mc, lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: discs along a glowing arc */
function arcPath(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return bands(C, Z, o);
  var cw = Z.w / n, dd = 46, top = Z.y + 20, drop = Math.min(110, Z.h * 0.3), tw = cw - 22;
  function yy(k){ var p = n > 1 ? k / (n - 1) : 0; return top + drop * (1 - Math.sin(p * Math.PI)); }
  var F = K.fitHD(C, it, tw, 44, Z.h - drop - dd - 40, {hMax:18, dMax:14, hMin:12, dMin:10.5});
  for (var k = 0; k < n - 1; k++) { var x1 = Z.x + cw * (k + 0.5), x2 = Z.x + cw * (k + 1.5); C.line(x1, yy(k) + dd / 2, x2, yy(k + 1) + dd / 2, {color:AM, w:1.5, op:0.55}); }
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), y = yy(k);
    C.ell(cx - dd / 2 - 7, y - 7, dd + 14, dd + 14, {fill:AM, op:0.16});
    C.ell(cx - dd / 2, y, dd, dd, {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}});
    C.text(cx - dd / 2, y, dd, dd, String(k + 1), {font:'head', size:17, wt:700, color:'#1A0F0A', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, cx - tw / 2, y + dd + 20, tw, Z.y + Z.h - y - dd - 20, t, F, {tc:INK, mc:MUT, align:'c'});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  smoke(C, Z.x, Z.y, Z.w, Z.h, {r:26}); var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  C.rect(Z.x + pad, y, 40, 3, {fill:AM}); y += 24;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:600, color:AM, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; C.ell(x, y, 22, 22, {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}}); C.text(x + 32, y, 420, 22, f.company, {font:'head', size:15, wt:600, color:INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function ember(C, cx, cy, d){
  C.ell(cx - d * 0.7, cy - d * 0.7, d * 1.4, d * 1.4, {fill:CR, op:0.1});
  C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:135, stops:[[0, '#FFD08A'], [55, '#FF7A4D'], [100, '#8A2A1A']]}});
  C.ell(cx - d * 0.36, cy - d * 0.4, d * 0.42, d * 0.3, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.55], [100, '#FFFFFF', 0]]}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 76, 54);
  if (photo) { D.imgBox(C, 700, 90, 504, 540, {r:26}); smoke(C, 640, 470, 250, 110, {r:20}); if (f.website) C.text(662, 470, 206, 110, f.website, {font:'body', size:14, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  else { ember(C, 980, 330, 380); smoke(C, 760, 420, 300, 190, {r:26}); smoke(C, 1000, 130, 190, 150, {r:22}); if (f.website) C.text(786, 420, 248, 190, f.website, {font:'body', size:15, wt:600, color:INK, va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  var tw = photo ? 540 : 640;
  C.rect(76, 196, 46, 3, {fill:AM});
  C.text(76, 220, tw, 230, C.s.title, {font:'head', fit:{max:photo ? 62 : 76, min:32}, wt:300, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.02});
  if (C.s.subtitle) C.text(76, 466, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) C.text(76, 590, tw, 22, who, {font:'body', size:14, wt:600, color:AM, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 76, 54);
  if (photo) D.imgBox(C, 700, 90, 504, 540, {r:26}); else ember(C, 1010, 250, 300);
  C.text(76, 130, 580, 190, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:300, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(76, 336, 540, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var pw = photo ? 560 : W - 152, cols = photo ? 1 : n, g = 14, cw = (pw - g * (cols - 1)) / cols, rh = photo ? 58 : 84;
  cs.forEach(function(c, k){
    var x = 76 + (photo ? 0 : k * (cw + g)), y = (photo ? 426 : 500) + (photo ? k * (rh + 10) : 0);
    smoke(C, x, y, cw, rh, {r:rh / 2});
    C.ell(x + 12, y + (rh - 38) / 2, 38, 38, {grad:{ang:135, stops:[[0, '#FFC978'], [100, '#FF7A4D']]}}); C.icon(x + 12 + 10, y + (rh - 38) / 2 + 10, 18, c.icon, '#1A0F0A');
    C.text(x + 62, y, cw - 80, rh, c.val, {font:'body', fit:{max:17, min:11.5}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  ember(C, 250, 520, 300);
  C.rect(76, 150, 46, 3, {fill:AM});
  var te = C.text(76, 172, 400, 220, C.s.title || 'Agenda', {font:'head', fit:{max:68, min:34}, wt:300, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(76, 172 + te._h + 16, 380, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  bands(C, {x:540, y:76, w:W - 540 - 76, h:H - 160}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:AM, tc:INK}); return; }
  var photo = o.img === 'photo', x = 76, w = photo ? 600 : W - 152;
  if (photo) D.imgBox(C, 760, 100, 444, 520, {r:26});
  smoke(C, x, 110, w, 500, {r:28});
  C.text(x + 44, 120, 100, 100, '“', {font:'head', size:120, wt:600, color:AM, lh:1, caps:false, ls:0});
  var qe = C.text(x + 48, 224, w - 96, 280, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:300, color:INK, lh:1.26, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 48, 224 + qe._h + 26, w - 96, 24, bt, {font:'body', size:15, wt:700, color:AM, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'embernoir', name:'Ember Noir', tag:'Dark · smoked glass',
  desc:'A warm near-black page lit by an ember glow, with smoked glass bands and amber rims: dramatic and premium for launches, brand and investor decks.',
  fonts:{head:'Outfit', body:'Inter', headW:300, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Outfit:wght@300;400;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Ember + glass', {}), V('v2', 'Photo + glass tag', {img:'photo'})],
    closing:[V('v1', 'Ember + glass contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Ember + glass bands', {fn:agendaA}), V('n2', 'Glass tiles', {body:tiles}), V('v2', 'Bands + image right', {body:bands, img:'right'})],
    text:[V('v1', 'Smoked statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass bands', {body:bands, hl:true}), V('n2', 'Staircase', {body:stairs}), V('n3', 'Glass tiles', {body:tiles}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p3:[V('v1', 'Staircase', {body:stairs, hl:true}), V('n2', 'Glass bands', {body:bands}), V('n3', 'Glass tiles', {body:tiles}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p4:[V('v1', 'Glass tiles', {body:tiles}), V('n2', 'Staircase', {body:stairs, hl:true}), V('n3', 'Glass bands', {body:bands}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p56:[V('v1', 'Glass tiles', {body:tiles}), V('n2', 'Glass bands', {body:bands}), V('v2', 'Bands + image right', {body:bands, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p78:[V('v1', 'Glass tiles', {body:tiles}), V('v2', 'Tiles + image right', {body:tiles, img:'right', cols:2})],
    stats:[V('v1', 'Glass pillars', {body:pillars}), V('n2', 'Progress rings', {body:'statRings', track:'#3A2A22'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Pillars + image right', {body:pillars, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Ember arc', {body:arcPath}), V('n2', 'Staircase', {body:stairs}), V('n3', 'Glass bands', {body:bands}), V('v2', 'Bands + image right', {body:bands, img:'right'})],
    quote:[V('v1', 'Glass quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

