
/* DesignStager template: linenglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 38 - Linen Glass : warm sand page, soft arches, milk-glass capsules, serif headings */
var CA = '#B5743B', RU = '#8C4A2F', INK = '#3B2F25', MUT = '#7A6A5A';
var P0 = {bg:'#F3EBDD', text:INK, title:INK, muted:MUT, accent:CA, accent2:RU, card:'#FBF6EC', line:'#DCCDB6', onAccent:'#FFFFFF'};
var S = {
  margin:80, series:[CA, RU, '#D9A66B', '#5E4B3A', '#E3C9A0', '#A0522D'],
  header:{kind:'center', top:60, tMax:48, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:CA, kGap:24, kSize:11.5},
  card:{kind:'glass', r:22, fill:'#FFFFFF', op:0.6, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:RU, dark:true, tc:'#FFFFFF', mc:'#F3DCCB'}},
  icon:{kind:'circle', bg:CA, fg:'#FFFFFF'}, num:{kind:'big', fg:CA},
  conc:{kind:'bar', barC:CA, label:'In essence', lc:CA, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:26, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#E6CFA9'], [100, '#C89B6A']]}}
};
function milk(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 22;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#A65E3C'], [100, '#7A3D25']]}, r:r, shadow:0.1}); return {tc:'#FFFFFF', mc:'#F3DCCB', ac:'#F0C89A'}; }
  C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.9], [100, '#FFFFFF', 0.42]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.05});
  return {tc:INK, mc:MUT, ac:CA};
}
function arch(C, x, y, w, h, color, op){ C.ell(x, y, w, w, {fill:color, op:op}); C.rect(x, y + w / 2, w, h - w / 2, {fill:color, op:op}); }
function foot(C){
  var y = H - 36;
  C.rect(W / 2 - 60, y - 14, 120, 1, {fill:INK, op:0.25});
  C.text(W / 2 - 200, y, 400, 16, (C.cover.website || C.cover.company || '') + '   ·   ' + pad2(C.i + 1), {font:'body', size:11, wt:500, color:MUT, align:'c', va:'m', lh:1, ls:0.08});
}
function bg(C, kind){
  C.bg({grad:{ang:170, stops:[[0, '#F8F1E4'], [100, '#EADDC8']]}});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  arch(C, [W - 200, -70, W - 150][k], [H - 250, H - 230, H - 330][k], [270, 230, 210][k], 400, '#D9B88A', 0.38);
  C.ell([-110, W - 170, -80][k], [-110, -120, 60][k], 270, 270, {fill:'#C8875A', op:0.16});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.38}).h : 0); }
/* tall milk-glass capsules, a caramel icon disc on top, text centred */
function capsules(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return diamonds(C, Z, o);
  var g = 20, cw = Math.min((Z.w - g * (n - 1)) / n, 330), tot = cw * n + g * (n - 1), x0 = Z.x + (Z.w - tot) / 2, r = Math.min(cw / 2, 110), pad = 26, tw = cw - pad * 2, ic = 56;
  var F = K.fitHD(C, it, tw, 54, Z.h - ic - 90 - r * 0.35, {hMax:23, dMax:15.5, hMin:13, dMin:10.5});
  it.forEach(function(t, k){
    var x = x0 + k * (cw + g), hot = o.hl && k === 1 && n === 3, cc = milk(C, x, Z.y, cw, Z.h, {r:r, hot:hot});
    C.ell(x + cw / 2 - ic / 2, Z.y + 30, ic, ic, {fill:hot ? '#FFFFFF' : CA});
    C.icon(x + cw / 2 - 12, Z.y + 30 + ic / 2 - 12, 24, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? RU : '#FFFFFF');
    K.hd(C, x + pad, Z.y + 30 + ic + 22, tw, Z.h - ic - 70 - r * 0.3, t, F, {tc:cc.tc, mc:cc.mc, align:'c'});
  });
}
/* open rows with a caramel diamond marker and a dotted leader, no boxes */
function diamonds(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 140), tw = cw - 44;
  var F = K.fitHD(C, it, tw, 34, rh - 22, {hMax:22, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 21, hMin:13, dMin:10.5, noBoost:n > 4});
  var tot = rh * per, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(10, (rh - bh) / 2);
    K.poly(C, [[x + 9, y + oy + 2], [x + 18, y + oy + 11], [x + 9, y + oy + 20], [x, y + oy + 11]], {fill:k % 2 ? RU : CA});
    if (r < per - 1) C.rect(x + 8.5, y + oy + 26, 1, rh - 30, {fill:CA, op:0.35});
    K.hd(C, x + 44, y + oy, tw, rh - oy - 4, t, F, {tc:INK, mc:MUT});
  });
}
/* two-column milk-glass panels, a serif numeral in the corner */
function panels(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 2 ? n : (n <= 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26, nw = 54, tw = cw - pad * 2 - nw;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2, {hMax:21, dMax:15, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, Math.max(bmx, 60) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = milk(C, x, y, cw, ch2, {hot:hot});
    C.text(x + pad, y + pad - 6, nw, 50, String(k + 1), {font:'head', size:46, wt:600, color:cc.ac, lh:1, caps:false, italic:true});
    K.hd(C, x + pad + nw, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: double-ring medallions */
function medallions(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, dd = Math.min(cw - 30, Z.h * 0.56, 230), vals = st.map(sv), labs = st.map(sl), tw = cw - 30;
  var vs = C.groupFit(vals, dd - 44, dd * 0.4, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 52, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - dd - 80, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), y = Z.y + 4;
    C.ell(cx - dd / 2, y, dd, dd, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.45]]}, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05});
    C.ell(cx - dd / 2 + 12, y + 12, dd - 24, dd - 24, {line:k % 2 ? RU : CA, lw:1.5, lop:0.9});
    C.text(cx - dd / 2 + 22, y, dd - 44, dd, vals[k], {font:'head', size:vs, wt:600, color:k % 2 ? RU : CA, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, y + dd + 18, tw, 50, labs[k], {font:'head', size:ls, wt:600, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, y + dd + 24 + le._h, tw, Z.y + Z.h - (y + dd + 24 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a centre thread, entries alternate above and below */
function thread(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return diamonds(C, Z, o);
  var cw = Z.w / n, my = Z.y + Z.h / 2, tw = cw - 20, half = Z.h / 2 - 34;
  var F = K.fitHD(C, it, tw, 44, half, {hMax:19, dMax:14, hMin:12, dMin:10.5});
  C.rect(Z.x, my - 0.75, Z.w, 1.5, {fill:CA, op:0.6});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), up = k % 2 === 0, bh = blockH(C, t, tw, F);
    C.ell(cx - 13, my - 13, 26, 26, {fill:'#FFFFFF', line:CA, lw:1.5}); C.ell(cx - 5, my - 5, 10, 10, {fill:up ? CA : RU});
    C.rect(cx - 0.5, up ? my - 30 : my + 13, 1, 17, {fill:CA, op:0.6});
    K.hd(C, cx - tw / 2, up ? my - 38 - bh : my + 38, tw, half, t, F, {tc:INK, mc:MUT, align:'c'});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  milk(C, Z.x, Z.y, Z.w, Z.h, {r:30}); var pad = 50, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:34, min:18}, wt:600, color:RU, italic:true, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) { arch(C, 730, 70, 440, 580, '#D9B88A', 0.5); C.ell(760, 100, 380, 380, {fill:'#FFFFFF', op:0.5}); D.imgBox(C, 770, 110, 360, 500, {r:180}); }
  else { arch(C, 800, 110, 360, 610, '#C8875A', 0.85); arch(C, 700, 300, 250, 420, '#E6CFA9', 0.9); C.ell(1020, 80, 150, 150, {grad:{ang:135, stops:[[0, '#F6D9A8'], [100, '#D99A5B']]}}); milk(C, 860, 430, 270, 150, {r:75}); if (f.website) C.text(884, 430, 222, 150, f.website, {font:'body', size:14, wt:600, color:INK, align:'c', va:'m', lh:1.3, ed:{f:'tf-website'}, maxLines:2}); }
  if (f.company) C.text(80, 62, 500, 20, f.company, {font:'body', size:13, wt:600, color:CA, lh:1, caps:true, ls:0.18, ed:{f:'tf-company'}, maxLines:1});
  C.text(80, 190, 600, 250, C.s.title, {font:'head', fit:{max:84, min:34}, wt:600, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1, ls:-0.01});
  if (C.s.subtitle) C.text(80, 458, 540, 80, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  ');
  if (who) { C.rect(80, 588, 44, 1.5, {fill:CA}); C.text(80, 604, 600, 22, who, {font:'body', size:14, wt:600, color:INK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  arch(C, W / 2 - 330, 90, 660, 700, '#E6CFA9', 0.55);
  if (photo) D.imgBox(C, W / 2 - 80, 60, 160, 160, {r:80}); else C.ell(W / 2 - 46, 96, 92, 92, {grad:{ang:135, stops:[[0, '#F6D9A8'], [100, '#D99A5B']]}});
  C.text(W / 2 - 400, 220, 800, 130, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:36}, wt:600, color:INK, align:'c', ed:{f:'title'}, maxLines:1, va:'m', lh:1, italic:true});
  if (C.s.subtitle) C.text(W / 2 - 300, 356, 600, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var cw = Math.min(330, (W - 200 - 16 * (n - 1)) / n), tot = cw * n + 16 * (n - 1), x0 = (W - tot) / 2;
  cs.forEach(function(c, k){
    var x = x0 + k * (cw + 16), y = 470;
    milk(C, x, y, cw, 70, {r:35}); C.ell(x + 14, y + 14, 42, 42, {fill:CA}); C.icon(x + 14 + 11, y + 14 + 11, 20, c.icon, '#FFFFFF');
    C.text(x + 68, y, cw - 86, 70, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  arch(C, 80, 110, 360, 620, '#C8875A', 0.85);
  var te = C.text(110, 300, 300, 200, C.s.title || 'Agenda', {font:'head', fit:{max:66, min:32}, wt:600, color:'#FFFFFF', align:'c', italic:true, ed:{f:'title'}, maxLines:2, va:'t', lh:1});
  if (C.s.subtitle) C.text(120, 300 + te._h + 14, 280, 90, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FBEBDD', align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  diamonds(C, {x:520, y:90, w:W - 520 - 90, h:H - 180}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CA, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 80 : 190, w = photo ? 620 : W - 380, al = photo ? 'l' : 'c';
  if (photo) D.imgBox(C, 800, 110, 380, 500, {r:190});
  C.text(x, 110, w, 90, '“', {font:'head', size:140, wt:600, color:CA, align:al, lh:1, caps:false, ls:0});
  var qe = C.text(x, 210, w, 300, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:600, color:INK, italic:true, align:al, lh:1.2, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, 210 + qe._h + 30, w, 24, bt, {font:'body', size:15, wt:600, color:RU, align:al, lh:1.1, caps:true, ls:0.12, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'linenglass', name:'Linen Glass', tag:'Warm sand · milk glass',
  desc:'A warm sand page with soft arches, milk-glass capsules and elegant serif headings: calm and refined for wellness, interiors, hospitality and brand stories.',
  fonts:{head:'Cormorant Garamond', body:'Jost', headW:600, bodyW:400, headLh:1.06, gf:'Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600|Jost:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Arches + glass', {}), V('v2', 'Arched photo', {img:'photo'})],
    closing:[V('v1', 'Arch + glass contacts', {}), V('v2', 'Round photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Arch + diamond list', {fn:agendaA}), V('n2', 'Numbered panels', {body:panels}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1})],
    text:[V('v1', 'Milk-glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Numbered panels', {body:panels, hl:true}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + panels', {body:panels, img:'left', cols:1})],
    p3:[V('v1', 'Capsules', {body:capsules, hl:true}), V('n2', 'Diamond list', {body:diamonds}), V('n3', 'Numbered panels', {body:panels, cols:3}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + panels', {body:panels, img:'left', cols:1})],
    p4:[V('v1', 'Numbered panels', {body:panels}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Diamond list', {body:diamonds, cols:2}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + panels', {body:panels, img:'left', cols:1})],
    p56:[V('v1', 'Numbered panels', {body:panels}), V('n2', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1}), V('v3', 'Image left + diamond list', {body:diamonds, img:'left', cols:1})],
    p78:[V('v1', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1})],
    stats:[V('v1', 'Medallions', {body:medallions}), V('n2', 'Progress rings', {body:'statRings', track:'#E3D4BC'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Medallions + image right', {body:medallions, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Thread', {body:thread}), V('n2', 'Capsules', {body:capsules}), V('n3', 'Diamond list', {body:diamonds}), V('v2', 'Diamond list + image right', {body:diamonds, img:'right', cols:1})],
    quote:[V('v1', 'Serif quote', {}), V('v2', 'Quote + arched photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

