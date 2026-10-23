
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 49 - Chevron Teal : white page, teal chevron bands that cut across the photo, pennant cards */
var TE = '#0E7C86', DK = '#0A4F5C', LT = '#7FD1D6', SAND = '#F4EDE2', INK = '#12343B', MUT = '#56707A';
var P0 = {bg:'#FFFFFF', text:INK, title:DK, muted:MUT, accent:TE, accent2:LT, card:'#F1F8F8', line:'#CFE3E5', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[TE, DK, LT, '#E0A458', '#9BB8BD', '#12343B'],
  header:{kind:'left', top:60, tMax:44, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'solid', r:10, fill:'#F1F8F8', line:null, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:TE, dark:true, tc:'#FFFFFF', mc:'#D9F1F2'}},
  icon:{kind:'square', bg:TE, fg:'#FFFFFF', r:8}, num:{kind:'big', fg:TE},
  conc:{kind:'bar', barC:TE, label:'Key point', lc:TE, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:0, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#B9DCDF'], [100, '#5FA9B0']]}}
};
/* a chevron band: a ">" shaped strip, tip at (tx, cy), arms going back to the left */
function chev(C, tx, cy, half, th, color, op){
  K.poly(C, [[tx - half - th, cy - half], [tx - half, cy - half], [tx, cy], [tx - half, cy + half], [tx - half - th, cy + half], [tx - th, cy]], {fill:color, op:op == null ? 1 : op});
}
/* the same strip pointing left: a "<" */
function chevL(C, tx, cy, half, th, color, op){
  K.poly(C, [[tx + half + th, cy - half], [tx + half, cy - half], [tx, cy], [tx + half, cy + half], [tx + half + th, cy + half], [tx + th, cy]], {fill:color, op:op == null ? 1 : op});
}
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  chev(C, x + 22, y + 11, 11, 7, light ? '#FFFFFF' : TE); chev(C, x + 12, y + 11, 11, 7, light ? '#BFE9EC' : LT);
  C.text(x + 34, y, 420, 22, f.company, {font:'body', size:13, wt:600, color:light ? '#FFFFFF' : INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 70, y = H - 36, f = C.cover;
  C.rect(M, y - 10, W - M * 2, 1, {fill:INK, op:0.12});
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
  chev(C, W - M - 30, y + 8, 8, 5, LT); chev(C, W - M - 40, y + 8, 8, 5, TE);
  C.text(W - M - 26, y, 26, 16, pad2(C.i + 1), {font:'head', size:12, wt:700, color:DK, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { chev(C, W + 40, 70, 150, 54, '#E3F3F4'); chev(C, W - 60, 70, 150, 26, '#C7E8EA'); }
  else if (k === 1) { chevL(C, -40, H - 90, 150, 54, '#E3F3F4'); chevL(C, 60, H - 90, 150, 26, '#C7E8EA'); }
  else { chev(C, W + 20, H - 110, 130, 46, '#E3F3F4'); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* pennant cards: a teal header that ends in a point, the text hangs below it */
function pennants(C, Z, o){
  var it = C.it.slice(0, o.max || 4), n = it.length; if (n < 2 || Z.w < 720) return tags(C, Z, o);
  var g = 20, cw = (Z.w - g * (n - 1)) / n, hh = 74, tip = 22, pad = 22, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, Z.h - hh - tip - pad * 2, {hMax:21, dMax:15.5, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h, hh + tip + bmx + pad * 2 + 6), y0 = Z.y + Math.max(0, (Z.h - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), col = k % 2 ? DK : TE;
    C.rect(x, y0, cw, ch, {fill:'#F1F8F8', r:0});
    K.poly(C, [[x, y0], [x + cw, y0], [x + cw, y0 + hh], [x + cw / 2, y0 + hh + tip], [x, y0 + hh]], {fill:col});
    C.text(x + pad, y0, 70, hh, pad2(k + 1), {font:'head', size:30, wt:800, color:'#FFFFFF', va:'m', lh:1, caps:false, op:0.95});
    C.icon(x + cw - pad - 28, y0 + hh / 2 - 14, 28, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), '#FFFFFF');
    K.hd(C, x + pad, y0 + hh + tip + pad - 4, tw, ch - hh - tip - pad * 2 + 4, t, F, {tc:INK, mc:MUT});
  });
}
/* arrow tags: the heading sits on a tag that points at its explanation */
function tags(C, Z, o){
  var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
  var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 110), hasH = it.some(function(t){ return t.h; }), tgw = hasH ? Math.min(300, Z.w * 0.34) : 74, tip = Math.min(24, rh / 2), tw = Z.w - tgw - tip - 26;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), tgw - 62, rh - 14, {font:'head', max:19, min:12, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 12, {font:'body', max:hasH ? 15.5 : 20, min:10.5, lh:1.38});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), col = k % 2 ? DK : TE;
    C.rect(Z.x + tgw, y, Z.w - tgw, rh, {fill:'#F1F8F8'});
    K.poly(C, [[Z.x, y], [Z.x + tgw, y], [Z.x + tgw + tip, y + rh / 2], [Z.x + tgw, y + rh], [Z.x, y + rh]], {fill:col});
    C.text(Z.x + 16, y, 40, rh, pad2(k + 1), {font:'head', size:Math.min(22, rh * 0.3), wt:800, color:'#FFFFFF', va:'m', lh:1, caps:false, op:0.8});
    if (hasH) C.text(Z.x + 58, y, tgw - 62, rh, t.h || '', {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + tgw + tip + 14, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* tiles: a grid of light tiles with a teal corner wedge and an icon */
function tiles(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22, ic = 40, side = rows > 1, tw = side ? cw - pad * 2 - ic - 14 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, side ? ch - pad * 2 : ch - pad * 2 - ic - 16, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 16) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0;
    C.rect(x, y, cw, ch2, {fill:hot ? TE : '#F1F8F8'});
    K.poly(C, [[x + cw - 44, y], [x + cw, y], [x + cw, y + 44]], {fill:hot ? '#FFFFFF' : LT, op:hot ? 0.3 : 0.9});
    C.rect(x + pad, y + pad, ic, ic, {fill:hot ? '#FFFFFF' : TE, r:8});
    C.icon(x + pad + 10, y + pad + 10, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? TE : '#FFFFFF');
    if (side) K.hd(C, x + pad + ic + 14, y + pad, tw, ch2 - pad * 2, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#D9F1F2' : MUT});
    else K.hd(C, x + pad, y + pad + ic + 16, tw, ch2 - pad * 2 - ic - 16, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#D9F1F2' : MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: each number sits between two chevron bands */
function chevStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 70, bh = Math.min(170, Z.h * 0.5), y0 = Z.y + Math.max(0, (Z.h - bh - 110) * 0.25);
  var vs = C.groupFit(vals, tw - 30, bh * 0.6, {font:'head', max:64, min:22, maxLines:1});
  var ls = C.groupFit(labs, cw - 40, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 40, Z.y + Z.h - y0 - bh - 70, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var x = Z.x + k * cw, cx = x + cw / 2, cy = y0 + bh / 2, col = k % 2 ? DK : TE;
    chevL(C, x + 10, cy, bh / 2, 20, LT, 0.9); chev(C, x + cw - 10, cy, bh / 2, 20, col);
    C.text(cx - tw / 2, y0, tw, bh, vals[k], {font:'head', size:vs, wt:800, color:col, align:'c', va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x + 20, y0 + bh + 18, cw - 40, 48, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 20, y0 + bh + 24 + le._h, cw - 40, Z.y + Z.h - (y0 + bh + 24 + le._h), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a teal rail, a square stop for each step, text in columns */
function rail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 720) return tags(C, Z, o);
  var cw = Z.w / n, sq = 54, y = Z.y + 6, tw = cw - 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 30, {font:'head', max:21, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - sq - 80, {font:'body', max:15, min:10.5, lh:1.4});
  C.rect(Z.x, y + sq / 2 - 3, Z.w, 6, {fill:'#D9EEF0'});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, last = k === n - 1, col = last ? DK : TE;
    C.rect(x, y + sq / 2 - 3, last ? cw * 0.5 : cw, 6, {fill:col, op:0.9});
    C.rect(x, y, sq, sq, {fill:col}); K.poly(C, [[x + sq, y], [x + sq + 16, y + sq / 2], [x + sq, y + sq]], {fill:col});
    C.text(x, y, sq, sq, pad2(k + 1), {font:'head', size:20, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1});
    C.text(x, y + sq + 20, tw, 30, t.h || '', {font:'head', size:hs, wt:800, color:DK, lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x, y + sq + 56, tw, Z.h - sq - 60, t.d, {font:'body', size:ds, color:INK, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#F1F8F8'}); chev(C, Z.x + 46, Z.y + 60, 30, 16, TE); chev(C, Z.x + 24, Z.y + 60, 30, 16, LT);
  var pad = 44, x = Z.x + 80, y = Z.y + pad - 12, w = Z.w - 80 - pad;
  if (it.h) { var he = C.text(x, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:TE, lh:1.12, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 12; }
  C.text(x, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.48, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
/* the photo side: the picture fills only the inside of the chevron; everything left of the bands stays white */
function photoSide(C, right, o){
  var pw = right ? 580 : 520, x = right ? W - pw : 0;
  if (o && o.img) D.imgBox(C, x, 0, pw, H, {r:0}); else C.rect(x, 0, pw, H, {grad:{ang:150, stops:[[0, '#17A2AD'], [100, DK]]}});
  if (right) { K.poly(C, [[x - 2, -2], [x, -2], [x + 362, H / 2], [x, H + 2], [x - 2, H + 2]], {fill:'#FFFFFF'}); chev(C, x + 360, H / 2, H / 2 + 40, 30, LT); chev(C, x + 296, H / 2, H / 2 + 40, 58, TE); chev(C, x + 206, H / 2, H / 2 + 40, 16, DK); }
  else { chevL(C, x + pw - 150, H / 2, H / 2 + 40, 200, '#FFFFFF'); chevL(C, x + pw - 230, H / 2, H / 2 + 40, 60, TE); chevL(C, x + pw - 300, H / 2, H / 2 + 40, 34, LT, 0.9); }
}
function cover(C, v){
  var o = C.o || {}, f = C.cover;
  photoSide(C, true, {img:o.img === 'photo'});
  brand(C, 70, 56);
  C.text(70, 180, 600, 250, C.s.title, {font:'head', fit:{max:70, min:32}, wt:800, color:DK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.02});
  if (C.s.subtitle) C.text(70, 444, 520, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(70, 560, 560, 22, 'Presentation by ' + who, {font:'body', size:15, wt:700, color:INK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) { var ww = Math.min(420, f.website.length * 8 + 60); C.rect(70, 606, ww, 38, {line:TE, lw:1.5, r:19}); C.text(70 + 22, 606, ww - 44, 38, f.website, {font:'body', size:13, wt:600, color:TE, va:'m', lh:1, ed:{f:'tf-website'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {};
  photoSide(C, true, {img:o.img === 'photo'});
  brand(C, 70, 56);
  C.text(70, 150, 600, 190, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:800, color:DK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(70, 356, 540, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 448 + k * 56;
    C.ell(70, y, 40, 40, {fill:k % 2 ? DK : TE}); C.icon(70 + 10, y + 10, 20, c.icon, '#FFFFFF');
    C.text(70 + 56, y, 500, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  photoSide(C, false, {img:false});
  C.text(70, 250, 330, 120, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:1.02});
  if (C.s.subtitle) C.text(70, 380, 280, 80, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:'#D9F1F2', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  tags(C, {x:590, y:80, w:W - 590 - 70, h:H - 180}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:TE, tc:INK}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 600 : W - 220;
  if (photo) D.imgBox(C, 800, 110, 410, 500, {r:0});
  chev(C, x + 30, 150, 34, 18, TE); chev(C, x + 4, 150, 34, 18, LT);
  var qe = C.text(x, 220, w, 300, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:700, color:DK, lh:1.2, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 220 + qe._h + 26, 60, 4, {fill:TE}); C.text(x, 220 + qe._h + 44, w, 24, bt, {font:'body', size:15, wt:700, color:MUT, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'chevronteal', name:'Chevron Teal', tag:'White · teal chevrons',
  desc:'A clean white page with a photo that fills the inside of a teal chevron, pennant cards and arrow tags: confident and friendly for partnerships, sales and company decks.',
  fonts:{head:'Be Vietnam Pro', body:'Be Vietnam Pro', headW:800, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Be+Vietnam+Pro:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Chevron bands', {}), V('v2', 'Photo behind the chevrons', {img:'photo'})],
    closing:[V('v1', 'Chevron bands + contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Chevron panel + arrow tags', {fn:agendaA}), V('n2', 'Tiles', {body:tiles}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'})],
    text:[V('v1', 'Statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Pennant cards', {body:pennants}), V('n2', 'Arrow tags', {body:tags}), V('n3', 'Tiles', {body:tiles, hl:true}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p3:[V('v1', 'Pennant cards', {body:pennants}), V('n2', 'Arrow tags', {body:tags}), V('n3', 'Tiles', {body:tiles, hl:true}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:1})],
    p4:[V('v1', 'Arrow tags', {body:tags}), V('n2', 'Pennant cards', {body:pennants}), V('n3', 'Tiles', {body:tiles}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p56:[V('v1', 'Tiles', {body:tiles}), V('n2', 'Arrow tags', {body:tags}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'}), V('v3', 'Image left + tiles', {body:tiles, img:'left', cols:2})],
    p78:[V('v1', 'Tiles', {body:tiles}), V('n2', 'Arrow tags', {body:tags, max:8}), V('v2', 'Tiles + image right', {body:tiles, img:'right', cols:2})],
    stats:[V('v1', 'Chevron numbers', {body:chevStats}), V('n2', 'Progress rings', {body:'statRings', track:'#D9EEF0'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Chevron numbers + image right', {body:chevStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Rail', {body:rail}), V('n2', 'Pennant cards', {body:pennants}), V('n3', 'Arrow tags', {body:tags}), V('v2', 'Arrow tags + image right', {body:tags, img:'right'})],
    quote:[V('v1', 'Chevron quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
