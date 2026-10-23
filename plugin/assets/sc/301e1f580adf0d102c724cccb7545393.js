
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 53 - Silk Burgundy : deep burgundy page with flowing silk waves, gold arrows, tall pill photos, serif headings */
var BG = '#3D0A19', B2 = '#6E1A30', B3 = '#9C3350', GO = '#D9B26A', CR = '#F7ECDD', MU = '#D8BFC2';
var P0 = {bg:BG, text:CR, title:CR, muted:MU, accent:GO, accent2:B3, card:'#55142A', line:'#7A2B41', onAccent:'#3D0A19'};
var S = {
  margin:76, series:[GO, '#F7ECDD', B3, '#E58FA5', '#A88A4E', '#C96A84'],
  header:{kind:'left', top:64, tMax:48, tMin:28, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:0, fill:'#55142A', line:null, shadow:0, dark:true, tc:CR, mc:MU,
        hl:{kind:'solid', fill:GO, dark:false, tc:'#3D0A19', mc:'#5A2A1A'}},
  icon:{kind:'plain', fg:GO}, num:{kind:'big', fg:GO},
  conc:{kind:'bar', barC:GO, label:'In short', lc:GO, tc:CR},
  footer:{fn:foot}, imgFramed:true, imgR:120, bodyGap:26,
  imgPh:{grad:{ang:160, stops:[[0, '#9C3350'], [100, '#55142A']]}}
};
/* one silk fold: a band between two sine curves */
function silk(C, y, amp, ph, th, color, op, x0, x1){
  x0 = x0 == null ? 0 : x0; x1 = x1 == null ? W : x1;
  var d = [], n = 36, k, x, t;
  for (k = 0; k <= n; k++) { t = k / n; x = x0 + (x1 - x0) * t; d.push([k ? 'L' : 'M', x, y + Math.sin(t * 5.2 + ph) * amp]); }
  for (k = n; k >= 0; k--) { t = k / n; x = x0 + (x1 - x0) * t; d.push(['L', x, y + th * (0.55 + 0.45 * Math.sin(t * 3.1 + ph * 1.7)) + Math.sin(t * 4.3 + ph + 0.9) * amp * 1.15]); }
  d.push(['Z']); C.path(d, {fill:color, op:op});
}
function arrow(C, x, y, len, color, lw){ lw = lw || 2; C.line(x, y, x + len - 6, y, {color:color, lw:lw}); K.poly(C, [[x + len - 11, y - 6], [x + len, y], [x + len - 11, y + 6]], {fill:color}); }
function arrowUp(C, x, y, s, color){ C.line(x, y + s, x + s - 4, y + 4, {color:color, lw:2.4}); K.poly(C, [[x + s - 13, y + 1], [x + s, y], [x + s - 1, y + 13]], {fill:color}); }
/* tall pill photo with an offset gold outline */
function pill(C, x, y, w, h, photo){
  C.rect(x + 18, y - 18, w, h, {line:GO, lw:2, r:w / 2});
  if (photo) D.imgBox(C, x, y, w, h, {r:w / 2});
  else { C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#B24A66'], [100, '#55142A']]}, r:w / 2}); arrowUp(C, x + w / 2 - 34, y + h / 2 - 34, 68, GO); }
}
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  arrow(C, x, y + 11, 30, GO);
  C.text(x + 42, y, 420, 22, f.company, {font:'body', size:12.5, wt:500, color:CR, va:'m', lh:1, caps:true, ls:0.22, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 76, y = H - 38, f = C.cover;
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:400, color:MU, lh:1, va:'m', ls:0.1});
  arrow(C, W - M - 78, y + 8, 30, GO, 1.6);
  C.text(W - M - 40, y, 40, 16, pad2(C.i + 1), {font:'head', size:16, wt:700, color:CR, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:BG});
  var k = (C.i || 0) % 3;
  if (kind === 'cover' || kind === 'closing') { silk(C, 430, 60, 0.4, 250, B2, 0.75); silk(C, 520, 46, 1.6, 190, B3, 0.3); silk(C, 610, 34, 2.9, 170, '#1E040C', 0.5); return; }
  if (k === 0) { silk(C, 600, 26, 0.6, 150, B2, 0.7); silk(C, 650, 20, 2.2, 110, B3, 0.22); }
  else if (k === 1) { silk(C, -60, 24, 1.9, 130, B2, 0.6); silk(C, 640, 22, 3.4, 120, B2, 0.6); }
  else { silk(C, 590, 34, 3.1, 170, B2, 0.65); silk(C, 660, 16, 0.2, 90, '#1E040C', 0.45); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.45}).h : 0); }
/* arrow rows: gold arrow, serif heading on the left, text on the right, a hairline between rows */
function arrows(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), wide = Z.w > 760 && hasH, rh = Math.min(Z.h / n, 128), aw = 62;
  var hw = wide ? Math.min(330, Z.w * 0.32) : 0, tw = Z.w - aw - hw - (wide ? 28 : 0), F, hs = 0, ds = 0;
  if (wide) { hs = C.groupFit(it.map(function(t){ return t.h || ''; }), hw, rh - 20, {font:'head', max:25, min:14, maxLines:2}); ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 22, {font:'body', max:16, min:10.5, lh:1.45}); }
  else F = K.fitHD(C, it, tw, 30, rh - 20, {hMax:23, dMax:hasH ? 15 : 20, hMin:13, dMin:10.5});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    if (k) C.line(Z.x, y, Z.x + Z.w, y, {color:GO, lw:1, op:0.35});
    arrow(C, Z.x, y + rh / 2, 40, GO);
    if (wide) {
      C.text(Z.x + aw, y, hw, rh, t.h || '', {font:'head', size:hs, wt:700, color:CR, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
      C.text(Z.x + aw + hw + 28, y, tw, rh, t.d, {font:'body', size:ds, color:MU, va:'m', lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
    } else K.hd(C, Z.x + aw, y + Math.max(8, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:CR, mc:MU});
  });
}
/* pillars: columns split by gold hairlines, an italic numeral over each */
function pillars(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return arrows(C, Z, o);
  var cw = Z.w / n, pad = 26, tw = cw - pad * 2, top = 96;
  var F = K.fitHD(C, it, tw, 56, Z.h - top - 20, {hMax:25, dMax:15.5, hMin:14, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var tot = Math.min(Z.h, top + bmx + 24), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.line(x, y0, x, y0 + tot, {color:GO, lw:1, op:0.5});
    C.text(x + pad, y0, 110, 64, pad2(k + 1), {font:'head', size:48, wt:500, color:GO, lh:1, italic:true, caps:false});
    arrow(C, x + pad, y0 + 78, 46, GO, 1.6);
    K.hd(C, x + pad, y0 + top, tw, tot - top, t, F, {tc:CR, mc:MU});
  });
}
/* veils: sheer panels with a gold top line, for many points */
function veils(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 54, ch - pad * 2 - 26, {hMax:21, dMax:14.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 30), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', op:0.07}); C.rect(x, y, cw, 2.5, {fill:GO});
    C.text(x + pad, y + pad - 4, 60, 22, pad2(k + 1), {font:'head', size:19, wt:500, color:GO, lh:1, italic:true, caps:false});
    K.hd(C, x + pad, y + pad + 26, tw, ch2 - pad * 2 - 26, t, F, {tc:CR, mc:MU});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* rise: large serif numbers, a gold arrow rising beside each */
function rise(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, pad = 22, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, tw - 60, 120, {font:'head', max:70, min:24, maxLines:1});
  var ls = C.groupFit(labs, tw, 44, {font:'body', max:14, min:11, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - 230, {font:'body', max:14, min:10.5, lh:1.45});
  var y0 = Z.y + Math.max(0, (Z.h - 300) * 0.25);
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.line(x, y0 + 10, x, y0 + 250, {color:GO, lw:1, op:0.4});
    arrowUp(C, x + pad, y0 + 6, 34, GO);
    C.text(x + pad, y0 + 46, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:600, color:CR, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y0 + 46 + vs * 1.1 + 12;
    var le = C.text(x + pad, ly, tw, 44, labs[k], {font:'body', size:ls, wt:600, color:GO, lh:1.25, caps:true, ls:0.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + le._h + 10, tw, Z.y + Z.h - (ly + le._h + 10), t.x, {font:'body', size:ds, color:MU, lh:1.45, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* path: one long gold arrow, dates above it and notes below */
function path(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return arrows(C, Z, o);
  var cw = (Z.w - 30) / n, tw = cw - 26, ly = Z.y + Math.min(110, Z.h * 0.3);
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 50, {font:'head', max:32, min:15, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.y + Z.h - ly - 40, {font:'body', max:15, min:10.5, lh:1.45});
  arrow(C, Z.x, ly, Z.w, GO, 2);
  it.forEach(function(t, k){
    var x = Z.x + k * cw;
    C.line(x + 1, ly - 12, x + 1, ly + 12, {color:GO, lw:2});
    C.text(x, ly - 22 - hs * 1.1, tw, hs * 1.15, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:CR, lh:1, italic:true, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x, ly + 30, tw, Z.y + Z.h - ly - 30, t.d, {font:'body', size:ds, color:MU, lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.line(Z.x, Z.y + 4, Z.x, Z.y + Z.h - 4, {color:GO, lw:2});
  var pad = 40, y = Z.y + 10, w = Z.w - pad;
  arrow(C, Z.x + pad, y + 8, 46, GO); y += 34;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 90, it.h, {font:'head', fit:{max:38, min:20}, wt:700, color:GO, lh:1.08, italic:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 16; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - 10 - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:CR, lh:1.52, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  pill(C, 860, 96, 300, 528, photo);
  brand(C, 76, 64);
  C.text(76, 150, 700, 270, C.s.title, {font:'head', fit:{max:76, min:34}, wt:700, color:CR, ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  arrow(C, 76, 452, 120, GO, 2.4);
  if (C.s.subtitle) C.text(76, 478, 620, 90, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:CR, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join('  —  ');
  if (who) C.text(76, 626, 700, 24, who, {font:'body', size:13.5, wt:500, color:GO, lh:1.1, caps:true, ls:0.16, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  pill(C, 96, 96, 290, 528, photo);
  var x = 500, w = W - x - 76;
  C.text(x, 110, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:36}, wt:700, color:CR, ed:{f:'title'}, maxLines:2, va:'b', lh:1.04, italic:true});
  arrow(C, x, 306, 120, GO, 2.4);
  if (C.s.subtitle) C.text(x, 328, w, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:CR, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 436 + k * 54;
    C.icon(x, y + 8, 24, c.icon, GO);
    C.text(x + 44, y, w - 44, 40, c.val, {font:'body', fit:{max:18, min:12}, wt:500, color:CR, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    C.line(x, y + 47, x + Math.min(w, 520), y + 47, {color:GO, lw:1, op:0.35});
  });
}
function agendaA(C, o){
  var te = C.text(76, 120, 380, 240, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:700, color:CR, ed:{f:'title'}, maxLines:2, va:'t', lh:1.04, italic:true});
  arrow(C, 76, 120 + te._h + 22, 100, GO, 2.4);
  if (C.s.subtitle) C.text(76, 120 + te._h + 46, 360, 80, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.line(500, 90, 500, H - 110, {color:GO, lw:1, op:0.5});
  arrows(C, {x:548, y:90, w:W - 548 - 76, h:H - 200}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:GO, tc:CR}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 640 : W - 220;
  if (photo) pill(C, 900, 110, 260, 480, true);
  C.text(x - 6, 96, 120, 120, '“', {font:'head', size:170, wt:700, color:GO, lh:1, caps:false, ls:0});
  var qe = C.text(x, 210, w, 280, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:600, color:CR, lh:1.14, italic:true, ed:{f:'title'}, maxLines:5, va:'t'});
  arrow(C, x, 210 + qe._h + 30, 60, GO);
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 76, 210 + qe._h + 18, w - 76, 24, bt, {font:'body', size:13.5, wt:500, color:GO, lh:1.1, caps:true, ls:0.16, va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'silkburgundy', name:'Silk Burgundy', tag:'Dark · burgundy silk · gold',
  desc:'A deep burgundy page with flowing silk folds, fine gold arrows, tall pill photos and serif headings: rich and formal for proposals, luxury, hospitality and client pitches.',
  fonts:{head:'Lora', body:'Albert Sans', headW:700, bodyW:400, headLh:1.06, gf:'Lora:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700|Albert+Sans:wght@400;500;600'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Silk + gold pill', {}), V('v2', 'Silk + pill photo', {img:'photo'})],
    closing:[V('v1', 'Gold pill + contacts', {}), V('v2', 'Pill photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + arrow rows', {fn:agendaA}), V('n2', 'Arrow rows', {body:arrows}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'})],
    text:[V('v1', 'Statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Pillars', {body:pillars}), V('n2', 'Arrow rows', {body:arrows}), V('n3', 'Veils', {body:veils}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:1})],
    p3:[V('v1', 'Pillars', {body:pillars}), V('n2', 'Arrow rows', {body:arrows}), V('n3', 'Veils', {body:veils}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:1})],
    p4:[V('v1', 'Arrow rows', {body:arrows}), V('n2', 'Pillars', {body:pillars}), V('n3', 'Veils', {body:veils}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:2})],
    p56:[V('v1', 'Veils', {body:veils}), V('n2', 'Arrow rows', {body:arrows}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'}), V('v3', 'Image left + veils', {body:veils, img:'left', cols:2})],
    p78:[V('v1', 'Veils', {body:veils}), V('v2', 'Veils + image right', {body:veils, img:'right', cols:2})],
    stats:[V('v1', 'Rising numbers', {body:rise}), V('n2', 'Progress rings', {body:'statRings', track:'#7A2B41'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + image right', {body:rise, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gold arrow path', {body:path}), V('n2', 'Arrow rows', {body:arrows}), V('n3', 'Pillars', {body:pillars}), V('v2', 'Arrow rows + image right', {body:arrows, img:'right'})],
    quote:[V('v1', 'Serif quote', {}), V('v2', 'Quote + pill photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
