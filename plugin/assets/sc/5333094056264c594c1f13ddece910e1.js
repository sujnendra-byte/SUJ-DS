
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 51 - Saffron Study : white page, saffron-to-umber gradient blocks, clusters of rounded squares */
var SA = '#E8A317', UM = '#3A2200', MID = '#B86E00', INK = '#191510', MUT = '#6B6257', PALE = '#FFF4DC';
var G = {ang:120, stops:[[0, '#F2B32E'], [55, '#B86E00'], [100, '#2E1B00']]};
var G2 = {ang:160, stops:[[0, '#F2B32E'], [100, '#8A4F00']]};
var P0 = {bg:'#FFFFFF', text:INK, title:INK, muted:MUT, accent:SA, accent2:MID, card:PALE, line:'#EADFC8', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[SA, MID, UM, '#F6D27A', '#7A746B', '#191510'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:16, fill:PALE, line:null, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:MID, dark:true, tc:'#FFFFFF', mc:'#FFE9BF'}},
  icon:{kind:'square', bg:SA, fg:'#FFFFFF', r:10}, num:{kind:'big', fg:MID},
  conc:{kind:'bar', barC:SA, label:'Finding', lc:MID, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#F6D27A'], [100, '#B86E00']]}}
};
/* a cluster of rounded squares; the big one holds the photo */
function cluster(C, x, y, s, photo){
  C.rect(x + s * 0.62, y - s * 0.16, s * 0.5, s * 0.5, {grad:G2, r:s * 0.11});
  C.rect(x - s * 0.18, y + s * 0.6, s * 0.46, s * 0.46, {fill:UM, r:s * 0.1});
  C.rect(x - 10, y - 10, s + 20, s + 20, {fill:'#FFFFFF', r:s * 0.2 + 8});
  if (photo) D.imgBox(C, x, y, s, s, {r:s * 0.2}); else { C.rect(x, y, s, s, {grad:G, r:s * 0.2}); C.rect(x + s * 0.2, y + s * 0.2, s * 0.6, s * 0.6, {line:'#FFFFFF', lop:0.7, lw:2, r:s * 0.13}); C.rect(x + s * 0.36, y + s * 0.36, s * 0.28, s * 0.28, {fill:'#FFFFFF', op:0.9, r:s * 0.07}); }
  C.rect(x + s * 0.8, y + s * 0.72, s * 0.3, s * 0.3, {fill:'#FFFFFF', r:s * 0.075}); C.rect(x + s * 0.8 + 8, y + s * 0.72 + 8, s * 0.3 - 16, s * 0.3 - 16, {grad:G2, r:s * 0.06});
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  C.rect(x, y, 24, 24, {grad:G2, r:7}); C.rect(x + 7, y + 7, 10, 10, {fill:'#FFFFFF', r:3});
  C.text(x + 34, y, 420, 24, f.company, {font:'head', size:12.5, wt:800, color:light ? '#FFFFFF' : INK, va:'m', lh:1, caps:true, ls:0.12, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.rect(M, y + 1, 14, 14, {grad:G2, r:4});
  C.text(M + 24, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m', ls:0.06});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1), {font:'head', size:13, wt:800, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { C.rect(W - 120, -40, 110, 110, {grad:G2, r:26}); C.rect(W - 56, 44, 60, 60, {fill:UM, r:14}); }
  else if (k === 1) { C.rect(W - 86, -30, 80, 80, {fill:UM, r:20}); C.rect(W - 150, 26, 54, 54, {grad:G2, r:13}); }
  else { C.rect(W - 140, -50, 130, 130, {line:SA, lw:2.5, r:30}); C.rect(W - 70, 30, 54, 54, {grad:G2, r:13}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* gradient columns: one gradient band, columns that step down in rounded panels */
function gradCols(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return ladder(C, Z, o);
  var cw = Z.w / n, pad = 26, tw = cw - pad * 2, step = Math.min(26, Z.h * 0.06);
  var F = K.fitHD(C, it, tw, 50, Z.h - step * (n - 1) - pad * 2 - 30, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:G, r:22});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, y = Z.y + k * step;
    if (k) C.rect(x, y, Z.x + Z.w - x, Z.h - k * step, {fill:'#000000', op:0.13, r:22});
    C.text(x + pad, y + pad, tw, 16, 'POINT ' + (k + 1), {font:'head', size:11, wt:800, color:'#FFE9BF', lh:1, ls:0.16});
    K.hd(C, x + pad, y + pad + 30, tw, Z.h - k * step - pad * 2 - 30, t, F, {tc:'#FFFFFF', mc:'#FFF1D6'});
  });
}
/* ladder: rows inside one gradient panel, each row a darker rounded step */
function ladder(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var rh = Math.min(Z.h / n, 124), pad = 26, nw = 60, tw = Z.w - pad * 2 - nw;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var tot = rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  C.rect(Z.x, y0, Z.w, tot, {grad:G, r:22});
  it.forEach(function(t, k){
    var y = y0 + k * rh, bh = blockH(C, t, tw, F);
    if (k) C.rect(Z.x, y, Z.w, tot - k * rh, {fill:'#000000', op:0.11, r:22});
    C.text(Z.x + pad, y, nw - 10, rh, pad2(k + 1), {font:'head', size:Math.min(28, rh * 0.32), wt:800, color:'#FFE9BF', va:'m', lh:1, caps:false});
    K.hd(C, Z.x + pad + nw, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:'#FFFFFF', mc:'#FFF1D6'});
  });
}
/* linked blocks: a small heading block joined by a bar to a wider text block */
function linked(C, Z, o){
  var it = C.it.slice(0, o.max || 5), n = it.length; if (!n) return;
  var g = 16, rh = Math.min((Z.h - g * (n - 1)) / n, 130), hasH = it.some(function(t){ return t.h; }), lw = hasH ? Math.min(280, Z.w * 0.3) : 90, link = 34, pad = 22, tw = Z.w - lw - link - pad * 2;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), lw - pad * 2, rh - 20, {font:'head', max:18, min:12, maxLines:3}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 22, {font:'body', max:hasH ? 15.5 : 19, min:10.5, lh:1.42});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    C.rect(Z.x + lw - 20, y + rh * 0.3, link + 40, rh * 0.4, {fill:MID});
    C.rect(Z.x, y + rh * 0.1, lw, rh * 0.8, {grad:G2, r:14});
    C.rect(Z.x + lw + link, y, Z.w - lw - link, rh, {grad:G, r:14});
    C.text(Z.x + pad, y + rh * 0.1, lw - pad * 2, rh * 0.8, hasH ? (t.h || '') : pad2(k + 1), {font:'head', size:hasH ? hs : 26, wt:800, color:'#FFFFFF', va:'m', align:hasH ? 'l' : 'c', lh:1.15, caps:hasH, ls:hasH ? 0.04 : 0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:3});
    C.text(Z.x + lw + link + pad, y, tw, rh, t.d, {font:'body', size:ds, color:'#FFFFFF', va:'m', lh:1.42, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* notes: pale rounded tiles with a small gradient square, for many points */
function notes(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, sq = 30, tw = cw - pad * 2 - sq - 12;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2, {hMax:19, dMax:14.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, Math.max(bmx, sq) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:PALE, r:16});
    C.rect(x + pad, y + pad, sq, sq, {grad:G2, r:8});
    C.text(x + pad, y + pad, sq, sq, String(k + 1), {font:'head', size:13, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
    K.hd(C, x + pad + sq + 12, y + pad, tw, ch2 - pad * 2, t, F, {tc:INK, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: a gradient rounded square per number, an outline square behind it */
function squares(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw - 50, Z.h * 0.52, 210), vals = st.map(sv), labs = st.map(sl), tw = cw - 30;
  var vs = C.groupFit(vals, s - 30, s * 0.5, {font:'head', max:56, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - s - 90, {font:'body', max:14, min:10.5, lh:1.4});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), x = cx - s / 2, y = Z.y + 14;
    C.rect(x + 16, y - 12, s, s, {line:SA, lw:2.5, r:s * 0.2});
    C.rect(x, y, s, s, {grad:k % 2 ? {ang:160, stops:[[0, '#8A4F00'], [100, '#2E1B00']]} : G2, r:s * 0.2});
    C.text(x + 15, y, s - 30, s, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, y + s + 20, tw, 48, labs[k], {font:'head', size:ls, wt:800, color:INK, align:'c', lh:1.14, caps:true, ls:0.03, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, y + s + 26 + le._h, tw, Z.y + Z.h - (y + s + 26 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: gradient columns that grow taller, each with its date on top */
function growth(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return ladder(C, Z, o);
  var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 16, tw = cw - pad * 2, minH = Z.h * 0.6;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 34, {font:'head', max:24, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, minH - 70, {font:'body', max:14.5, min:10.5, lh:1.4});
  it.forEach(function(t, k){
    var hh = minH + (Z.h - minH) * (n > 1 ? k / (n - 1) : 1), x = Z.x + k * (cw + g), y = Z.y + Z.h - hh;
    C.rect(x, y, cw, hh, {grad:k === n - 1 ? {ang:160, stops:[[0, '#8A4F00'], [100, '#2E1B00']]} : G2, r:16});
    C.text(x + pad, y + pad, tw, 34, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + 40, 30, 3, {fill:'#FFFFFF', op:0.8});
    C.text(x + pad, y + pad + 56, tw, hh - pad * 2 - 56, t.d, {font:'body', size:ds, color:'#FFFFFF', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:G, r:24});
  var pad = 48, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:'#FFE9BF', lh:1.12, caps:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:'#FFFFFF', lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 330, 760, 150, {grad:{ang:0, stops:[[0, '#2E1B00'], [45, '#B86E00'], [100, '#F2B32E']]}});
  cluster(C, 800, 190, 320, photo);
  brand(C, 72, 56);
  C.text(72, 110, 640, 210, C.s.title, {font:'head', fit:{max:74, min:32}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1, caps:true, ls:-0.01});
  if (C.s.subtitle) C.text(72, 344, 600, 122, C.s.subtitle, {font:'head', fit:{max:20, min:13}, wt:700, color:'#FFFFFF', va:'m', caps:true, ls:0.03, ed:{f:'subtitle'}, lh:1.35, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(72, 520, 140, 22, 'Presented by', {font:'body', size:13, wt:500, color:MUT, lh:1.1}); C.text(72, 544, 600, 26, who, {font:'head', size:17, wt:800, color:INK, lh:1.1, caps:true, ls:0.03, ed:{f:'tf-presenter'}, maxLines:1}); C.rect(72, 584, 300, 2, {fill:INK}); }
  if (f.website) { C.rect(72, 612, 16, 16, {grad:G2, r:5}); C.text(98, 610, 500, 20, f.website, {font:'body', size:13, wt:600, color:INK, va:'m', lh:1, ls:0.08, ed:{f:'tf-website'}}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(0, 250, 760, 130, {grad:{ang:0, stops:[[0, '#2E1B00'], [45, '#B86E00'], [100, '#F2B32E']]}});
  cluster(C, 810, 210, 300, photo);
  brand(C, 72, 56);
  C.text(72, 100, 660, 140, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:1, va:'b', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 262, 620, 106, C.s.subtitle, {font:'head', fit:{max:19, min:13}, wt:700, color:'#FFFFFF', va:'m', caps:true, ls:0.03, ed:{f:'subtitle'}, lh:1.35, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 430 + k * 54;
    C.rect(72, y, 38, 38, {grad:G2, r:10}); C.icon(72 + 9, y + 9, 20, c.icon, '#FFFFFF');
    C.text(72 + 54, y, 540, 38, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 470, H, {grad:{ang:160, stops:[[0, '#F2B32E'], [60, '#B86E00'], [100, '#2E1B00']]}});
  C.rect(300, 470, 240, 240, {fill:'#FFFFFF', r:52}); C.rect(318, 488, 204, 204, {grad:G2, r:42}); C.rect(60, 560, 110, 110, {line:'#FFFFFF', lop:0.6, lw:2, r:26});
  var te = C.text(72, 130, 360, 200, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:32}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1, caps:true});
  if (C.s.subtitle) C.text(72, 130 + te._h + 16, 340, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#FFF1D6', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  notes(C, {x:560, y:90, w:W - 560 - 72, h:H - 190}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:MID, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 620 : W - 144;
  if (photo) cluster(C, 830, 200, 280, true);
  C.rect(x, 130, w, 400, {grad:G, r:26});
  C.rect(x + 40, 100, 70, 70, {fill:'#FFFFFF', r:18}); C.rect(x + 48, 108, 54, 54, {grad:G2, r:13}); C.text(x + 48, 118, 54, 54, '”', {font:'head', size:54, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 46, 206, w - 92, 240, C.s.title, {font:'head', fit:{max:photo ? 30 : 38, min:18}, wt:700, color:'#FFFFFF', lh:1.24, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 46, 206 + qe._h + 22, w - 92, 24, bt, {font:'head', size:13.5, wt:800, color:'#FFE9BF', lh:1.1, caps:true, ls:0.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'saffronstudy', name:'Saffron Study', tag:'White · saffron gradient',
  desc:'A white page with saffron-to-umber gradient blocks and clusters of rounded squares: clear and serious for research, studies, reports and findings.',
  fonts:{head:'Golos Text', body:'Golos Text', headW:800, bodyW:400, headLh:1.06, gf:'Golos+Text:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Gradient band + square cluster', {}), V('v2', 'Photo in the cluster', {img:'photo'})],
    closing:[V('v1', 'Gradient band + contacts', {}), V('v2', 'Photo cluster + contacts', {img:'photo'})],
    agenda:[V('v1', 'Gradient panel + notes', {fn:agendaA}), V('n2', 'Ladder', {body:ladder}), V('v2', 'Notes + image right', {body:notes, img:'right', cols:1})],
    text:[V('v1', 'Gradient statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Linked blocks', {body:linked}), V('n2', 'Gradient columns', {body:gradCols}), V('n3', 'Notes', {body:notes}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:1})],
    p3:[V('v1', 'Gradient columns', {body:gradCols}), V('n2', 'Ladder', {body:ladder}), V('n3', 'Linked blocks', {body:linked}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:1})],
    p4:[V('v1', 'Ladder', {body:ladder}), V('n2', 'Gradient columns', {body:gradCols}), V('n3', 'Notes', {body:notes}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:2})],
    p56:[V('v1', 'Notes', {body:notes}), V('n2', 'Ladder', {body:ladder}), V('v2', 'Ladder + image right', {body:ladder, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:2})],
    p78:[V('v1', 'Notes', {body:notes}), V('v2', 'Notes + image right', {body:notes, img:'right', cols:2})],
    stats:[V('v1', 'Rounded squares', {body:squares}), V('n2', 'Progress rings', {body:'statRings', track:'#EADFC8'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Squares + image right', {body:squares, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Growing columns', {body:growth}), V('n2', 'Ladder', {body:ladder}), V('n3', 'Linked blocks', {body:linked}), V('v2', 'Ladder + image right', {body:ladder, img:'right'})],
    quote:[V('v1', 'Gradient quote', {}), V('v2', 'Quote + photo cluster', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
