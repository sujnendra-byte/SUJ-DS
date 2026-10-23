
/* DesignStager template: tangerinesplit */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 46 - Tangerine Split : cream and tangerine split screens, cocoa ink, checker blocks and ticket stubs */
var TA = '#FF7A1A', CR = '#FFF4E6', INK = '#2B1B12', MUT = '#6E5A4C', DK = '#3A2416';
var P0 = {bg:CR, text:INK, title:INK, muted:MUT, accent:TA, accent2:DK, card:'#FFFFFF', line:'#EBD9C3', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[TA, DK, '#FFB37A', '#A8876E', '#FFD9B8', '#C2500A'],
  header:{kind:'left', top:60, tMax:48, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:TA, kGap:26, kSize:11.5},
  card:{kind:'solid', r:0, fill:'#FFFFFF', line:null, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:TA, dark:true, tc:'#FFFFFF', mc:'#FFE9D6'}},
  icon:{kind:'circle', bg:TA, fg:'#FFFFFF'}, num:{kind:'big', fg:TA},
  conc:{kind:'bar', barC:TA, label:'In short', lc:TA, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#FFC79A'], [100, '#FF8E3C']]}}
};
/* 3D cube drawn from three faces */
function cube(C, cx, cy, s, c){
  var a = s * 0.866;
  K.poly(C, [[cx - a, cy - s / 2], [cx, cy], [cx, cy + s], [cx - a, cy + s / 2]], {fill:c[1]});
  K.poly(C, [[cx + a, cy - s / 2], [cx, cy], [cx, cy + s], [cx + a, cy + s / 2]], {fill:c[2]});
  K.poly(C, [[cx, cy - s], [cx + a, cy - s / 2], [cx, cy], [cx - a, cy - s / 2]], {fill:c[0]});
}
function cubes(C, cx, cy, s){
  var a = s * 0.866;
  K.poly(C, [[cx - a * 2.2, cy + s * 1.55], [cx, cy + s * 2.5], [cx + a * 2.2, cy + s * 1.55], [cx, cy + s * 0.6]], {fill:DK, op:0.18});
  cube(C, cx - a, cy + s / 2, s, [CR, '#FFD9B8', '#E8B98C']);
  cube(C, cx + a, cy + s / 2, s, ['#5A3A26', DK, '#24140B']);
  cube(C, cx, cy + s, s, ['#FFFFFF', '#FFE3C7', '#F2C49A']);
  cube(C, cx, cy - s, s, [CR, '#FFD9B8', DK]);
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.rect(M, y + 1, 14, 14, {fill:TA}); C.rect(M + 14, y + 1, 14, 14, {fill:DK});
  C.text(M + 40, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:600, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, pad2(C.i + 1), {font:'head', size:14, wt:800, color:INK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:CR});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { C.rect(W - 120, 0, 120, 120, {fill:TA}); C.rect(W - 180, 120, 60, 60, {fill:DK}); }
  else if (k === 1) { C.rect(W - 46, 0, 46, H, {fill:TA}); }
  else { K.poly(C, [[W - 190, 0], [W, 0], [W, 190]], {fill:TA}); K.poly(C, [[W - 90, 0], [W, 0], [W, 90]], {fill:DK}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* checker: cells with no gaps, alternating tangerine, white and cocoa */
function checker(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 26, tw = cw - pad * 2, top = rows === 1 ? 62 : 40;
  var F = K.fitHD(C, it, tw, 52, ch - pad * 2 - top, {hMax:22, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad * 2 + 6), tot = ch2 * rows, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = y0 + r * ch2, m = (r + c) % 3, fill = m === 0 ? TA : (m === 1 ? '#FFFFFF' : DK), lt = m !== 1;
    C.rect(x, y, cw + 0.5, ch2 + 0.5, {fill:fill});
    C.text(x + pad, y + pad - 4, 160, 44, pad2(k + 1), {font:'head', size:rows === 1 ? 38 : 26, wt:800, color:m === 0 ? DK : TA, lh:1, caps:false, maxLines:1});
    C.icon(x + cw - pad - 24, y + pad - 2, 24, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), lt ? '#FFFFFF' : TA);
    K.hd(C, x + pad, y + pad + top, tw, ch2 - pad * 2 - top, t, F, {tc:lt ? '#FFFFFF' : INK, mc:m === 0 ? '#FFF1E4' : (m === 1 ? MUT : '#E8D6C6')});
  });
}
/* tickets: stubs with a notch on each side and a dashed tear line */
function tickets(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 22, g = 14, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min((Z.h - g * (per - 1)) / per, 136), sw = Math.min(110, cw * 0.2), pad = 22, tw = cw - sw - pad * 2;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, 54) + 30); var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g);
    C.rect(x, y, cw, rh, {fill:'#FFFFFF', r:10}); C.rect(x, y, sw, rh, {fill:k % 2 ? DK : TA, r:10}); C.rect(x + sw - 10, y, 10, rh, {fill:k % 2 ? DK : TA});
    C.ell(x + sw - 9, y - 9, 18, 18, {fill:CR}); C.ell(x + sw - 9, y + rh - 9, 18, 18, {fill:CR});
    for (var q = 0; q < Math.floor((rh - 26) / 10); q++) C.rect(x + sw - 1, y + 14 + q * 10, 2, 5, {fill:CR});
    C.text(x, y, sw, rh, pad2(k + 1), {font:'head', size:Math.min(34, rh * 0.36), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + sw + pad, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:INK, mc:MUT});
  });
}
/* half and half: the first point on a tangerine half, the others on the cream half */
function halves(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 3 || Z.w < 760) return checker(C, Z, o);
  var lw = Math.round(Z.w * 0.44), pad = 34, rest = it.slice(1), rn = rest.length, rw = Z.w - lw - 40, rh = Z.h / rn;
  C.rect(Z.x, Z.y, lw, Z.h, {fill:TA});
  C.text(Z.x + pad, Z.y + pad - 6, 120, 70, '01', {font:'head', size:64, wt:800, color:DK, lh:1, caps:false});
  var F1 = K.fitHD(C, [it[0]], lw - pad * 2, 90, Z.h - pad * 2 - 80, {hMax:30, dMax:18, hMin:16, dMin:11});
  K.hd(C, Z.x + pad, Z.y + pad + 76, lw - pad * 2, Z.h - pad * 2 - 76, it[0], F1, {tc:'#FFFFFF', mc:'#FFF1E4'});
  var F = K.fitHD(C, rest, rw - 70, 30, rh - 22, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  rest.forEach(function(t, k){
    var y = Z.y + k * rh, bh = blockH(C, t, rw - 70, F), oy = Math.max(8, (rh - bh) / 2);
    C.text(Z.x + lw + 40, y + oy - 4, 60, 40, pad2(k + 2), {font:'head', size:28, wt:800, color:TA, lh:1, caps:false});
    K.hd(C, Z.x + lw + 110, y + oy, rw - 70, rh - oy, t, F, {tc:INK, mc:MUT});
    if (k < rn - 1) C.rect(Z.x + lw + 40, y + rh - 1, rw, 2, {fill:INK, op:0.12});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: split blocks - number half in cocoa or tangerine, label half in white */
function splitStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n === 4 ? 2 : (Z.w < 760 ? 1 : n), rows = Math.ceil(n / cols), g = 20, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, rows === 1 ? 330 : 190), vals = st.map(sv), labs = st.map(sl), pad = 22, vert = rows === 1 && cols > 1;
  var vw = vert ? cw : cw * 0.42, vh = vert ? ch * 0.46 : ch, tw = (vert ? cw : cw - vw) - pad * 2;
  var vs = C.groupFit(vals, vw - pad * 2, vh - 20, {font:'head', max:66, min:22, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, (vert ? ch - vh : ch) - 76, {font:'body', max:13.5, min:10.5, lh:1.38});
  var tot = ch * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch + g), dk = k % 2 === 1;
    C.rect(x, y, cw, ch, {fill:'#FFFFFF'}); C.rect(x, y, vw, vh, {fill:dk ? DK : TA});
    C.text(x + pad, y, vw - pad * 2, vh, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:vert ? 'l' : 'c', va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var tx = vert ? x + pad : x + vw + pad, ty = vert ? y + vh + 18 : y + 20;
    var le = C.text(tx, ty, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(tx, ty + le._h + 8, tw, y + ch - ty - le._h - 20, t.x, {font:'body', size:ds, color:MUT, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: flags on poles of rising height */
function flags(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return tickets(C, Z, o);
  var cw = Z.w / n, fh = 46, fw = Math.min(cw - 30, 150), base = Z.y + Math.min(Z.h * 0.5, 200), tw = cw - 24;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), fw - 26, fh - 10, {font:'head', max:21, min:11.5, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.y + Z.h - base - 26, {font:'body', max:15.5, min:10.5, lh:1.4});
  C.rect(Z.x, base, Z.w, 4, {fill:INK});
  it.forEach(function(t, k){
    var x = Z.x + k * cw + 10, ph = (base - Z.y - 6) * (0.5 + 0.5 * (n > 1 ? k / (n - 1) : 1)), top = base - ph, last = k === n - 1, col = last ? DK : TA;
    C.rect(x, top, 4, ph, {fill:INK});
    K.poly(C, [[x + 4, top], [x + 4 + fw, top], [x + 4 + fw - 14, top + fh / 2], [x + 4 + fw, top + fh], [x + 4, top + fh]], {fill:col});
    C.text(x + 12, top, fw - 30, fh, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:'#FFFFFF', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.ell(x - 5, base - 5, 14, 14, {fill:col});
    C.text(x, base + 22, tw, Z.y + Z.h - base - 24, t.d, {font:'body', size:ds, color:INK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF'}); C.rect(Z.x, Z.y, 90, 90, {fill:TA}); C.text(Z.x, Z.y + 10, 90, 90, '“', {font:'head', size:90, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var pad = 46, y = Z.y + pad, x = Z.x + 90 + 36, w = Z.w - 90 - 36 - pad;
  if (it.h) { var he = C.text(x, y, w, 76, it.h, {font:'head', fit:{max:32, min:17}, wt:800, color:TA, lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(x, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', sx = 700;
  C.rect(sx, 0, W - sx, H, {fill:TA});
  if (photo) D.imgBox(C, sx + 60, 110, W - sx - 120, 500, {r:0});
  else { cubes(C, sx + (W - sx) / 2, 370, 118); }
  if (f.company) { C.rect(72, 60, 14, 14, {fill:TA}); C.rect(86, 60, 14, 14, {fill:DK}); C.text(112, 56, 500, 22, f.company, {font:'head', size:15, wt:800, color:INK, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  var tw = sx - 72 - 50;
  C.text(72, 170, tw, 270, C.s.title, {font:'head', fit:{max:78, min:32}, wt:800, color:INK, ed:{f:'title'}, maxLines:4, va:'t', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 40, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { C.rect(72, 574, 5, 44, {fill:TA}); C.text(92, 574, tw, 44, who, {font:'body', size:15, wt:700, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(sx + 60, H - 60, W - sx - 120, 18, f.website, {font:'body', size:12.5, wt:700, color:photo ? '#FFFFFF' : DK, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', sx = 560;
  C.rect(0, 0, sx, H, {fill:TA});
  if (photo) D.imgBox(C, 60, 110, sx - 120, 500, {r:0}); else { cubes(C, sx / 2, 370, 110); }
  var x = sx + 70, tw = W - x - 72;
  C.text(x, 110, tw, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(x, 326, tw, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 420 + k * 62;
    C.rect(x, y, 46, 46, {fill:k % 2 ? DK : TA}); C.icon(x + 12, y + 12, 22, c.icon, '#FFFFFF');
    C.text(x + 62, y, tw - 62, 46, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 460, H, {fill:TA}); C.rect(0, H - 130, 130, 130, {fill:DK}); cube(C, 340, H - 190, 70, [CR, '#FFD9B8', DK]);
  var te = C.text(72, 120, 340, 260, C.s.title || 'Agenda', {font:'head', fit:{max:72, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1, ls:-0.02});
  if (C.s.subtitle) C.text(72, 120 + te._h + 18, 320, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:'#FFF1E4', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  tickets(C, {x:530, y:76, w:W - 530 - 72, h:H - 170}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:TA, tc:INK}); return; }
  var photo = o.img === 'photo';
  C.rect(0, 0, 330, H, {fill:TA}); C.text(60, 90, 240, 240, '“', {font:'head', size:300, wt:800, color:'#FFFFFF', lh:1, caps:false, ls:0});
  if (photo) D.imgBox(C, 60, 330, 210, 210, {r:105});
  var x = 400, w = W - x - 80;
  var qe = C.text(x, 150, w, 330, C.s.title, {font:'head', fit:{max:46, min:20}, wt:700, color:INK, lh:1.16, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 150 + qe._h + 26, 60, 5, {fill:TA}); C.text(x, 150 + qe._h + 46, w, 24, bt, {font:'body', size:15, wt:700, color:MUT, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'tangerinesplit', name:'Tangerine Split', tag:'Cream · split screen',
  desc:'Cream and tangerine split screens with cocoa ink, checker blocks, ticket stubs and flags: warm and energetic for retail, food, travel and community decks.',
  fonts:{head:'Syne', body:'Karla', headW:800, bodyW:400, headLh:1.04, headLs:-0.01, gf:'Syne:wght@600;700;800|Karla:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Split + circles', {}), V('v2', 'Split + photo', {img:'photo'})],
    closing:[V('v1', 'Split + contacts', {}), V('v2', 'Photo split + contacts', {img:'photo'})],
    agenda:[V('v1', 'Tangerine half + tickets', {fn:agendaA}), V('n2', 'Checker', {body:checker}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1})],
    text:[V('v1', 'White statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Checker', {body:checker}), V('n2', 'Tickets', {body:tickets, cols:1}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + tickets', {body:tickets, img:'left', cols:1})],
    p3:[V('v1', 'Checker', {body:checker}), V('n2', 'Half and half', {body:halves}), V('n3', 'Tickets', {body:tickets, cols:1}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + tickets', {body:tickets, img:'left', cols:1})],
    p4:[V('v1', 'Half and half', {body:halves}), V('n2', 'Checker', {body:checker}), V('n3', 'Tickets', {body:tickets}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + checker', {body:checker, img:'left', cols:2})],
    p56:[V('v1', 'Checker', {body:checker}), V('n2', 'Tickets', {body:tickets}), V('n3', 'Half and half', {body:halves}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1}), V('v3', 'Image left + tickets', {body:tickets, img:'left', cols:1})],
    p78:[V('v1', 'Tickets', {body:tickets, max:8}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1, max:8})],
    stats:[V('v1', 'Split blocks', {body:splitStats}), V('n2', 'Progress rings', {body:'statRings', track:'#F1DDC7'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Split blocks + image right', {body:splitStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Flags', {body:flags}), V('n2', 'Tickets', {body:tickets}), V('n3', 'Checker', {body:checker}), V('v2', 'Tickets + image right', {body:tickets, img:'right', cols:1})],
    quote:[V('v1', 'Split quote', {}), V('v2', 'Split quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

