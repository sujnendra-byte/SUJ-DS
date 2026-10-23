
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 54 - Ribbon Pine : cream page, pine ribbons that fold round cards, hanging banners, an accordion-fold timeline */
var CRM = '#F5F1E6', PN = '#1E4A38', DP = '#102B20', MO = '#8DB27A', GD = '#D8A23A', GDD = '#9A6E1B', INK = '#17231C', MUT = '#5F6B62', LN = '#DDD6C2';
var P0 = {bg:CRM, text:INK, title:PN, muted:MUT, accent:PN, accent2:GD, card:'#FFFFFF', line:LN, onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[PN, GD, MO, '#3F7A5E', '#B9B29C', '#17231C'],
  header:{kind:'left', top:62, tMax:44, tMin:26, sMax:15, sMin:12, kicker:null},
  card:{kind:'solid', r:6, fill:'#FFFFFF', line:LN, shadow:0, dark:false, tc:PN, mc:MUT,
        hl:{kind:'solid', fill:PN, dark:true, tc:'#FFFFFF', mc:'#CFE0D2'}},
  icon:{kind:'square', bg:PN, fg:'#FFFFFF', r:4}, num:{kind:'big', fg:GD},
  conc:{kind:'bar', barC:GD, label:'Takeaway', lc:GDD, tc:PN},
  footer:{fn:foot}, imgFramed:true, imgR:8, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#8DB27A'], [100, '#1E4A38']]}}
};
/* a ribbon that wraps round the left edge of a card: the band plus the small dark fold under it */
function wrap(C, x, y, w, h, color, dark, f){ f = f || 12; K.poly(C, [[x, y + h], [x + f, y + h], [x + f, y + h + f]], {fill:dark}); C.rect(x, y, w, h, {fill:color}); }
/* a ribbon tail with a V cut at the right end */
function tail(C, x, y, w, h, color, n){ n = n || h * 0.4; K.poly(C, [[x, y], [x + w, y], [x + w - n, y + h / 2], [x + w, y + h], [x, y + h]], {fill:color}); }
/* a hanging banner with a V cut at the bottom */
function banner(C, x, y, w, h, color, n){ n = n || w * 0.32; K.poly(C, [[x, y], [x + w, y], [x + w, y + h], [x + w / 2, y + h - n], [x, y + h]], {fill:color}); }
function brand(C, x, y, light){
  var f = C.cover; if (!f.company) return;
  banner(C, x, y - 2, 16, 26, GD);
  C.text(x + 28, y, 420, 22, f.company, {font:'head', size:14, wt:700, color:light ? '#FFFFFF' : PN, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
}
function foot(C){
  var M = 72, y = H - 36, f = C.cover;
  C.line(M, y - 12, W - M, y - 12, {color:LN, lw:1});
  C.text(M, y, 500, 16, f.website || f.company || '', {font:'body', size:11.5, wt:500, color:MUT, lh:1, va:'m'});
  tail(C, W - 60, y - 3, 60, 22, PN, 8);
  C.text(W - 60, y - 3, 46, 22, pad2(C.i + 1), {font:'head', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:CRM});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { banner(C, W - 132, 0, 34, 92, PN); banner(C, W - 90, 0, 22, 62, GD); }
  else if (k === 1) { wrap(C, W - 190, 66, 190, 26, GD, GDD, 0); K.poly(C, [[W - 190, 66], [W - 176, 79], [W - 190, 92]], {fill:CRM}); }
  else { banner(C, W - 118, 0, 26, 74, MO); banner(C, W - 84, 0, 26, 104, PN); }
  C.rect(0, 62, 8, 58, {fill:GD});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h : 0); }
/* wraps: white cards; the heading rides on a ribbon that folds round the card's left edge */
function wraps(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), g = 22, f = 12, rh = Math.min((Z.h - g * (n - 1) - f) / n, 124), lw = hasH ? Math.min(300, Z.w * 0.32) : 84, pad = 22, tw = Z.w - f - lw - pad * 2, bh = Math.min(rh - 22, 64);
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), lw - 30, bh - 8, {font:'head', max:18, min:11.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 18, {font:'body', max:hasH ? 15.5 : 19, min:10.5, lh:1.42});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot - f) * 0.3);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g), alt = k % 2;
    C.rect(Z.x + f, y, Z.w - f, rh, {fill:'#FFFFFF', line:LN, lw:1, r:4});
    wrap(C, Z.x, y + (rh - bh) / 2, lw + f, bh, alt ? GD : PN, alt ? GDD : DP, f);
    C.text(Z.x + 18, y + (rh - bh) / 2, lw - 20, bh, hasH ? (t.h || '') : pad2(k + 1), {font:'head', size:hasH ? hs : 24, wt:700, color:alt ? DP : '#FFFFFF', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + f + lw + pad, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.42, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
  });
}
/* bookmarks: white columns, a banner with the number hangs over the top edge of each */
function bookmarks(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return wraps(C, Z, o);
  var g = 22, cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 96, up = 14;
  var F = K.fitHD(C, it, tw, 56, Z.h - up - top - pad, {hMax:21, dMax:15.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch = Math.min(Z.h - up, top + bmx + pad + 8), y0 = Z.y + up + Math.max(0, (Z.h - up - ch) * 0.3);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), alt = k % 2;
    C.rect(x, y0, cw, ch, {fill:'#FFFFFF', line:LN, lw:1, r:4});
    banner(C, x + pad, y0 - up, 46, 88, alt ? GD : PN);
    C.text(x + pad, y0 - up + 6, 46, 50, pad2(k + 1), {font:'head', size:19, wt:700, color:alt ? DP : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad, y0 + top, tw, ch - top - pad, t, F, {tc:PN, mc:MUT});
  });
}
/* slips: small white cards with a pine edge and a little ribbon tail carrying the number */
function slips(C, Z, o){
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 18, tw = cw - pad * 2 - 6;
  var F = K.fitHD(C, it, tw, 50, ch - pad - 46, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + pad + 52), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g);
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:LN, lw:1, r:4}); C.rect(x, y, 6, ch2, {fill:PN});
    tail(C, x, y + 14, 58, 22, k % 2 ? GD : PN, 8);
    C.text(x + 6, y + 14, 40, 22, pad2(k + 1), {font:'head', size:11.5, wt:700, color:k % 2 ? DP : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    K.hd(C, x + pad + 6, y + 46, tw, ch2 - pad - 46, t, F, {tc:PN, mc:MUT});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: a wide hanging banner per number, hung from one rod */
function hung(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, bw = Math.min(cw - 44, 230), bh = Math.min(Z.h * 0.56, 230), tw = cw - 30, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, bw - 30, bh * 0.45, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs, tw, 48, {font:'head', max:17.5, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - bh - 80, {font:'body', max:14, min:10.5, lh:1.4});
  C.rect(Z.x, Z.y + 4, Z.w, 6, {fill:DP, r:3});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), alt = k % 2;
    banner(C, cx - bw / 2, Z.y + 10, bw, bh, alt ? GD : PN, bw * 0.2);
    C.text(cx - bw / 2 + 15, Z.y + 10, bw - 30, bh - bw * 0.2, vals[k], {font:'head', size:vs, wt:700, color:alt ? DP : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = Z.y + bh + 26;
    var le = C.text(cx - tw / 2, ly, tw, 48, labs[k], {font:'head', size:ls, wt:700, color:PN, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, ly + le._h + 8, tw, Z.y + Z.h - (ly + le._h + 8), t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: one strip folded like an accordion, a date on every fold */
function accordion(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return wraps(C, Z, o);
  var cw = Z.w / n, d = 26, bh = 70, y0 = Z.y + 6, tw = cw - 26;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - 30, 40, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - bh - d - 50, {font:'body', max:15, min:10.5, lh:1.42});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, a = k % 2 ? d : 0, b = k % 2 ? 0 : d;
    K.poly(C, [[x, y0 + a], [x + cw, y0 + b], [x + cw, y0 + b + bh], [x, y0 + a + bh]], {fill:k % 2 ? DP : PN});
    C.text(x + 14, y0 + d / 2, cw - 28, bh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:k % 2 ? GD : '#FFFFFF', align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    banner(C, x + cw / 2 - 7, y0 + bh + d / 2 + 6, 14, 30, GD, 6);
    C.text(x + 13, y0 + bh + d + 34, tw, Z.y + Z.h - (y0 + bh + d + 34), t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.42, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var f = 12; C.rect(Z.x + f, Z.y, Z.w - f, Z.h, {fill:'#FFFFFF', line:LN, lw:1, r:4});
  var pad = 46, y = Z.y + 34, w = Z.w - f - pad * 2;
  if (it.h) { wrap(C, Z.x, y, Math.min(Z.w * 0.6, 460), 52, PN, DP, f); C.text(Z.x + 26, y, Math.min(Z.w * 0.6, 460) - 40, 52, it.h, {font:'head', fit:{max:22, min:13}, wt:700, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += 52 + 34; }
  C.text(Z.x + f + pad, y, w, Z.y + Z.h - 34 - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function photoCol(C, x, photo){
  if (photo) D.imgBox(C, x, 0, 330, H, {r:0}); else { C.rect(x, 0, 330, H, {grad:{ang:170, stops:[[0, '#8DB27A'], [100, '#1E4A38']]}}); banner(C, x + 120, 250, 90, 190, '#FFFFFF', 30); banner(C, x + 134, 250, 62, 150, GD, 22); }
  banner(C, x + 40, 0, 54, 150, GD); banner(C, x + 104, 0, 30, 96, CRM);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  photoCol(C, 880, photo);
  brand(C, 72, 60);
  K.poly(C, [[0, 430], [14, 430], [14, 444]], {fill:DP});
  tail(C, 0, 190, 960, 240, PN, 60);
  C.text(72, 206, 780, 208, C.s.title, {font:'head', fit:{max:68, min:30}, wt:700, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'m', lh:1.04});
  if (C.s.subtitle) C.text(72, 470, 700, 90, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:INK, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.rect(72, 612, 46, 4, {fill:GD}); C.text(72, 628, 700, 24, who, {font:'head', size:15, wt:700, color:PN, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  photoCol(C, 70, photo);
  K.poly(C, [[W, 330], [W - 14, 330], [W - 14, 344]], {fill:DP});
  K.poly(C, [[W, 150], [330, 150], [384, 240], [330, 330], [W, 330]], {fill:PN});
  var x = 470, w = W - x - 72;
  C.text(x, 162, w, 156, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:32}, wt:700, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(x, 366, w, 60, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:INK, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 456 + k * 54;
    tail(C, x, y, 58, 38, k % 2 ? GD : PN, 12); C.icon(x + 11, y + 9, 20, c.icon, k % 2 ? DP : '#FFFFFF');
    C.text(x + 76, y, w - 76, 38, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  K.poly(C, [[0, 310], [14, 310], [14, 324]], {fill:DP});
  tail(C, 0, 130, 470, 180, PN, 46);
  C.text(72, 140, 330, 160, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:700, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(72, 350, 340, 80, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  banner(C, 72, 470, 40, 120, GD); banner(C, 124, 470, 40, 84, MO);
  wraps(C, {x:520, y:80, w:W - 520 - 72, h:H - 180}, {max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:GD, tc:PN}); return; }
  var photo = o.img === 'photo', x = 110, w = photo ? 660 : W - 220;
  if (photo) { D.imgBox(C, 880, 110, 300, 480, {r:6}); banner(C, 910, 110, 40, 110, GD); }
  C.rect(x + 12, 150, w - 12, 400, {fill:'#FFFFFF', line:LN, lw:1, r:4});
  banner(C, x + 50, 130, 60, 110, PN); C.text(x + 50, 150, 60, 60, '”', {font:'head', size:54, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  var qe = C.text(x + 56, 264, w - 100, 210, C.s.title, {font:'head', fit:{max:photo ? 30 : 38, min:18}, wt:700, color:PN, lh:1.22, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 56, 264 + qe._h + 20, w - 100, 24, bt, {font:'body', size:14, wt:600, color:GDD, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'ribbonpine', name:'Ribbon Pine', tag:'Cream · pine ribbons · gold',
  desc:'A cream page with pine-green ribbons that fold round the cards, hanging gold banners and an accordion timeline: steady and trustworthy for business plans, strategy and finance.',
  fonts:{head:'Bitter', body:'Karla', headW:700, bodyW:400, headLh:1.08, gf:'Bitter:wght@500;600;700|Karla:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Title ribbon + pine column', {}), V('v2', 'Title ribbon + photo column', {img:'photo'})],
    closing:[V('v1', 'Ribbon + contacts', {}), V('v2', 'Photo column + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title ribbon + wrapped rows', {fn:agendaA}), V('n2', 'Wrapped rows', {body:wraps}), V('v2', 'Slips + image right', {body:slips, img:'right', cols:1})],
    text:[V('v1', 'Ribbon statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Bookmarks', {body:bookmarks}), V('n2', 'Wrapped rows', {body:wraps}), V('n3', 'Slips', {body:slips}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:1})],
    p3:[V('v1', 'Wrapped rows', {body:wraps}), V('n2', 'Bookmarks', {body:bookmarks}), V('n3', 'Slips', {body:slips}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:1})],
    p4:[V('v1', 'Bookmarks', {body:bookmarks}), V('n2', 'Wrapped rows', {body:wraps}), V('n3', 'Slips', {body:slips}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:2})],
    p56:[V('v1', 'Slips', {body:slips}), V('n2', 'Wrapped rows', {body:wraps}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'}), V('v3', 'Image left + slips', {body:slips, img:'left', cols:2})],
    p78:[V('v1', 'Slips', {body:slips}), V('v2', 'Slips + image right', {body:slips, img:'right', cols:2})],
    stats:[V('v1', 'Hanging banners', {body:hung}), V('n2', 'Progress rings', {body:'statRings', track:LN}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Banners + image right', {body:hung, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Accordion fold', {body:accordion}), V('n2', 'Wrapped rows', {body:wraps}), V('n3', 'Bookmarks', {body:bookmarks}), V('v2', 'Wrapped rows + image right', {body:wraps, img:'right'})],
    quote:[V('v1', 'Banner quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
