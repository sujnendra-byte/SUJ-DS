
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 80 - Viewfinder : camera language - corner brackets, REC dot, timecode, storyboard frames, film strip */
var BG = '#F7F7F5', CH = '#1F2226', YE = '#FFC93C', RE = '#E5484D', GY = '#8B9099', FR = '#E3E4E1';
var P0 = {bg:BG, text:CH, title:CH, muted:'#5D626B', accent:RE, accent2:YE, card:'#FFFFFF', line:'#D9DAD6', onAccent:CH};
function brackets(C, x, y, w, h, c, len, lw){ len = len || 26; lw = lw || 3; c = c || CH; var d = [['M', x, y + len], ['L', x, y], ['L', x + len, y], ['M', x + w - len, y], ['L', x + w, y], ['L', x + w, y + len], ['M', x + w, y + h - len], ['L', x + w, y + h], ['L', x + w - len, y + h], ['M', x + len, y + h], ['L', x, y + h], ['L', x, y + h - len]]; C.path(d, {stroke:c, lw:lw}); }
function tc(i){ var s = 12 + i * 7; return '00:0' + (i % 6) + ':' + pad2(s % 60) + ':' + pad2((i * 13) % 25); }
function rec(C, x, y, c){ C.ell(x, y, 12, 12, {fill:RE}); C.text(x + 18, y - 3, 50, 18, 'REC', {font:'head', size:13, wt:700, color:c || CH, va:'m', lh:1}); }
function strip(C, y, h){ C.rect(0, y, W, h, {fill:CH}); var d = []; for (var x = 10; x < W; x += 32) { d.push(['M', x, y + h * 0.2]); d.push(['L', x + 14, y + h * 0.2]); d.push(['L', x + 14, y + h * 0.8]); d.push(['L', x, y + h * 0.8]); d.push(['Z']); } C.path(d, {fill:BG}); }
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  brackets(C, 24, 20, W - 48, H - 40, GY, 34, 2);
  C.text(W - 250, 28, 210, 18, tc(C.i || 0), {font:'body', size:12, wt:600, color:GY, align:'r', va:'m', lh:1});
}
function head(C, A, o){
  var y = 52;
  rec(C, A.x, y + 3);
  C.text(A.x + 80, y, 400, 18, 'SCENE ' + pad2(C.i + 1), {font:'head', size:14, wt:700, color:RE, ls:0.1, va:'m', lh:1});
  y += 30;
  var te = C.text(A.x, y, Math.min(A.w, 900), 96, C.s.title || '', {font:'head', fit:{max:46, min:24}, wt:800, color:CH, caps:true, lh:1.0, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:'#5D626B', lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:CH, mc:'#5D626B', ac:RE, markS:44, g:18, head:head, ptc:CH, pmc:'#5D626B', footH:70,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:'#FFFFFF'}); brackets(C, x + 12, y + 12, w - 24, h - 24, YE, 30, 4); return {tc:CH, mc:'#5D626B', ac:RE}; }
    if (o.hot) { C.rect(x, y, w, h, {fill:CH}); brackets(C, x + 10, y + 10, w - 20, h - 20, YE, 20, 3); return {tc:'#FFFFFF', mc:'#C7CAD0', ac:YE}; }
    C.rect(x, y, w, h, {fill:'#FFFFFF'}); brackets(C, x + 10, y + 10, w - 20, h - 20, CH, 18, 2.5);
    C.text(x + w - 120, y + 16, 104, 14, 'TAKE ' + (k + 1), {font:'head', size:10.5, wt:700, color:GY, align:'r', lh:1});
    return {tc:CH, mc:'#5D626B', ac:RE};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#FFFFFF' : FR}); return {tc:CH, mc:'#5D626B', ac:RE}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.rect(x, y, s, s, {fill:on ? YE : CH}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, on ? CH : YE); },
  rowMark:function(C, x, y, s, t, k){ C.text(x, y, s + 10, s, pad2(k + 1), {font:'head', size:s * 0.62, wt:800, color:RE, va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s * 0.35, s, s * 0.7, {fill:k === 0 ? RE : CH}); C.text(cx - s / 2, cy - s * 0.35, s, s * 0.7, pad2(k + 1), {font:'head', size:s * 0.32, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1 - 60, y - 14, x2 - x1 + 120, 28, {fill:FR}); C.rect(x1 + (x2 - x1) * 0.42, y - 26, 3, 52, {fill:RE}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { strip(C, Z.y - 10, 22); D.imgBox(C, 0, Z.y + 12, W, Z.h - 12, {r:0}); return; }
    D.imgBox(C, Z.x, 0, Z.w, H, {r:0});
    brackets(C, Z.x + 30, 40, Z.w - 60, H - 80, '#FFFFFF', 34, 4);
    rec(C, Z.x + 50, 60, '#FFFFFF');
    C.rect(Z.x + Z.w / 2 - 16, H / 2 - 16, 32, 32, {line:YE, lw:2});
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 46; C.text(a.x, y, 420, 16, C.cover.company || '', {font:'head', size:12, wt:700, color:GY, caps:true, ls:0.12, lh:1, va:'m'}); }
var S = {margin:72, series:[RE, YE, CH, '#8B9099', '#F28C8F', '#FFE49A'], header:{kind:'left', top:52}, card:{kind:'solid', fill:'#FFFFFF', r:0}, icon:{kind:'plain', fg:RE}, num:{kind:'big', fg:RE},
  conc:{kind:'band', fill:CH, r:0, label:'Cut to', tc:'#FFFFFF', lc:YE}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#9AA0A8'], [100, '#3A3F46']]}}};
/* signature: storyboard - a frame per point with the note under it */
function storyboard(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 700) return B.cards(C, Z, o);
  var g = 20, cw = (Z.w - g * (n - 1)) / n, fh = Math.min(cw * 0.56, Z.h * 0.46), tw = cw;
  var F = K.fitHD(C, it, tw, 44, Z.h - fh - 40, {hMax:20, dMax:15, hMin:12, dMin:10});
  it.forEach(function(t, k){ var x = Z.x + k * (cw + g);
    C.rect(x, Z.y, cw, fh, {fill:k % 2 ? FR : '#E9E7E1'});
    brackets(C, x + 8, Z.y + 8, cw - 16, fh - 16, CH, 16, 2.5);
    K.icon(C, x + cw / 2 - fh * 0.18, Z.y + fh / 2 - fh * 0.18, fh * 0.36, t, CH);
    C.text(x, Z.y + fh + 10, cw, 18, 'SCENE ' + pad2(k + 1), {font:'head', size:12, wt:800, color:RE, ls:0.1, lh:1});
    K.hd(C, x, Z.y + fh + 34, tw, Z.h - fh - 34, t, F, {tc:CH, mc:'#5D626B'});
  });
}
/* signature: focus numbers - each figure inside a yellow focus square */
function focus(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.78, Z.h * 0.56), vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 20;
  var vs = C.groupFit(vals, s * 0.8, s * 0.4, {font:'head', max:64, min:20, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'body', max:17, min:12, maxLines:2});
  st.forEach(function(t, k){ var cx = Z.x + cw * (k + 0.5), x = cx - s / 2;
    brackets(C, x, Z.y, s, s, YE, s * 0.22, 5);
    C.rect(cx - 1, Z.y + s * 0.12, 2, s * 0.1, {fill:YE}); C.rect(cx - 1, Z.y + s * 0.78, 2, s * 0.1, {fill:YE});
    C.text(x, Z.y, s, s, vals[k], {font:'head', size:vs, wt:800, color:CH, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(cx - tw / 2, Z.y + s + 16, tw, 50, labs[k], {font:'body', size:ls, wt:700, color:CH, align:'c', lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img !== 'plain';
  if (photo) D.imgBox(C, 0, 0, W, H, {r:0}); else C.rect(0, 0, W, H, {fill:CH});
  C.rect(0, H - 300, W, 300, {grad:{ang:90, stops:[[0, CH, 0], [100, CH, 0.85]]}});
  brackets(C, 40, 36, W - 80, H - 72, '#FFFFFF', 46, 4);
  rec(C, 72, 66, '#FFFFFF');
  C.text(W - 330, 62, 250, 20, tc(0), {font:'body', size:14, wt:600, color:'#FFFFFF', align:'r', va:'m', lh:1});
  C.rect(W / 2 - 22, H / 2 - 60, 44, 44, {line:YE, lw:2});
  if (f.company) C.text(72, 410, 600, 20, f.company, {font:'head', size:14, wt:700, color:YE, caps:true, ls:0.14, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 438, 900, 150, C.s.title, {font:'head', fit:{max:76, min:30}, wt:800, color:'#FFFFFF', caps:true, lh:0.98, va:'t', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 592, 760, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6E7E9', lh:1.35, ed:{f:'subtitle'}, maxLines:2});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(W - 520, 610, 440, 22, who, {font:'body', size:14, wt:700, color:'#FFFFFF', align:'r', va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  C.rect(0, 0, W, H, {fill:CH});
  if (o.img === 'photo') { D.imgBox(C, 700, 80, 500, 400, {r:0}); brackets(C, 690, 70, 520, 420, YE, 40, 4); }
  else { var cx = 900, cy = 250; K.poly(C, [[cx - 200, cy - 40], [cx + 220, cy - 120], [cx + 230, cy - 76], [cx - 190, cy + 4]], {fill:'#FFFFFF'}); var d = []; for (var i = 0; i < 6; i++) { var x = cx - 170 + i * 70; d.push(['M', x, cy - 34 - i * 14.5]); d.push(['L', x + 30, cy - 45 - i * 14.5]); d.push(['L', x + 40, cy - 2 - i * 14.5]); d.push(['L', x + 10, cy + 9 - i * 14.5]); d.push(['Z']); } C.path(d, {fill:CH}); C.rect(cx - 200, cy + 10, 430, 230, {fill:'#2C3036'}); C.text(cx - 180, cy + 30, 390, 40, 'SCENE · FINAL    TAKE · 1', {font:'head', size:18, wt:700, color:YE, va:'m', lh:1}); C.text(cx - 180, cy + 90, 390, 120, "THAT'S A WRAP", {font:'head', size:46, wt:800, color:'#FFFFFF', va:'m', lh:1}); }
  C.text(72, 100, 580, 220, C.s.title || 'Thank you', {font:'head', fit:{max:90, min:36}, wt:800, color:'#FFFFFF', caps:true, lh:0.98, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 340, 560, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#C7CAD0', lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 440 + k * 54; C.rect(72, y, 40, 40, {fill:YE}); C.icon(82, y + 10, 20, c.icon, CH); C.text(128, y, 500, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
  strip(C, H - 40, 40);
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  D.imgBox(C, 0, 0, W, H, {r:0});
  C.rect(0, H - 260, W, 260, {fill:CH, op:0.82});
  brackets(C, 40, 36, W - 80, H - 72, '#FFFFFF', 46, 3);
  var qe = C.text(120, H - 230, W - 240, 150, '“' + (C.s.title || '') + '”', {font:'body', fit:{max:32, min:16}, wt:600, color:'#FFFFFF', align:'c', va:'m', lh:1.3, ed:{f:'title'}, maxLines:4});
  if (by) C.text(120, H - 70, W - 240, 22, by.h ? by.h + ' — ' + by.d : by.d, {font:'head', size:15, wt:700, color:YE, align:'c', caps:true, ls:0.08, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Bracket frames', rows:'Shot list', steps:'Edit timeline', stmt:'Framed statement', stats:'Number frames', photo:'viewfinder photo', track:'#E3E4E1'};
var VS = K4.vset(pg, B, L, {
  cover:[['Viewfinder photo', {img:'photo'}], ['Viewfinder (no photo)', {img:'plain'}]],
  closing:[["That's a wrap", {}], ['Wrap + photo', {img:'photo'}]],
  quote:[['Subtitle over photo', {img:'photo'}]],
  p3:[['Storyboard', pg(storyboard)]], p4:[['Storyboard', pg(storyboard)]], p2:[['Storyboard', pg(storyboard)]],
  stats:[['Focus numbers', pg(focus)]]
});
D.register({
  id:'viewfinder', name:'Viewfinder', tag:'Camera brackets · storyboard',
  desc:'Camera language: viewfinder corner brackets, a REC dot and timecode, storyboard frames, a shot list and film strips in charcoal, warm yellow and red. For media, marketing, film, events and agencies.',
  fonts:{head:'Big Shoulders Display', body:'Rubik', headW:800, bodyW:400, headLh:1.0, gf:'Big+Shoulders+Display:wght@600;700;800|Rubik:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

