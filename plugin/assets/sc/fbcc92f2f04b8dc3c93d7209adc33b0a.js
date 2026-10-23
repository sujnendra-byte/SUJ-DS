
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V, u = D.u, mix = u.mix, pad2 = u.pad2;
/* Classic (editable): the Classic look - a thin accent bar along the top, a bold left title with an accent subtitle,
   soft tinted cards with a round icon, the card title and an accent rule - drawn as elements, so every editor tool works on it.
   Every colour comes from the chosen colour theme (C.P), like Classic. */
var P0 = {bg:'#FFFFFF', text:'#1E293B', title:'#0F2A4A', muted:'#475569', accent:'#2563EB', accent2:'#0EA5E9', card:'#EEF4FD', line:'#D6E2F3', onAccent:'#FFFFFF'};
var S = {
  margin:64,
  header:{kind:'left', top:50, tMax:42, tMin:24, sMax:17, sMin:12, gap:8},
  card:{kind:'solid', r:18},
  icon:{kind:'circle', scale:0.54},
  num:{kind:'circle'},
  conc:{kind:'box', label:'Key takeaway', r:12, max:16},
  footer:false, footerH:40,
  imgFramed:false, imgPct:0.4, bodyGap:24, concH:58
};
function tint(P, t){ return mix(P.accent, P.bg, t == null ? 0.9 : t); }
function dyn(C){
  var P = C.P;
  C.S = D.merge(S, {header:{sc:P.accent}, card:{fill:tint(P)}, conc:{fill:tint(P, 0.93), line:tint(P, 0.7), lw:1, lc:P.accent, tc:P.text}});
}
function bg(C, kind){
  dyn(C); var P = C.P;
  C.bg({fill:P.bg});
  if (kind !== 'cover' && kind !== 'closing') C.rect(0, 0, W, 6, {fill:P.accent});
}
function heads(it){ return it.map(function(t){ return t.h || ''; }); }
function descs(it){ return it.map(function(t){ return t.d || ''; }); }
/* one Classic card: round icon + card title on one line, an accent rule under it, then the text */
function ccard(C, x, y, w, h, t, F, o){
  var P = C.P, pad = F.pad, ms = F.ms, hx = x + pad + ms + 12, hw = w - pad * 2 - ms - 12;
  C.rect(x, y, w, h, {fill:tint(P), r:18});
  D.mark(C, x + pad, y + pad + Math.max(0, (F.hb - ms) / 2), ms, D.iconFor(C.s, t.i, (t.h || '') + ' ' + t.d), {});
  if (t.h) C.text(hx, y + pad, hw, F.hb, t.h, {font:'head', size:F.hs, color:P.title || P.text, va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
  var ry = y + pad + F.hb + 10;
  C.rect(x + pad, ry, w - pad * 2, 2.5, {fill:P.accent});
  C.text(x + pad, ry + 14, w - pad * 2, Math.max(16, h - (ry + 14 - y) - pad), t.d, {font:'body', size:F.ds, color:P.text, lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
}
function fitCards(C, it, tw, avail, o){
  var ms = o.ms || 36, hw = tw - ms - 12;
  var hs = C.groupFit(heads(it), hw, 64, {font:'head', max:o.hMax || 22, min:12, maxLines:2});
  var hh = 0; it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, hw, {font:'head', max:hs, min:hs, maxLines:2}).h); });
  var hb = Math.max(ms, hh);
  var ds = C.groupFit(descs(it), tw, Math.max(20, avail - hb - 24), {font:'body', max:o.dMax || 18, min:10, lh:1.45});
  return {ms:ms, hs:hs, hb:hb, ds:ds, pad:o.pad || 22};
}
/* cards in columns (2-4 across), more items wrap to a second row that fills the width */
function cards(C, Z, o){
  var it = C.it.slice(0, o.max || 16), n = it.length; if (!n) return;   /* up to 16 points (Edit Slide allows 16) */
  var cols = o.cols || (n <= 4 ? n : (n <= 10 ? Math.ceil(n / 2) : 4)); cols = Math.min(cols, n);
  var rows = Math.ceil(n / cols), g = o.gap || 20, ch = (Z.h - g * (rows - 1)) / rows, pad = 22;
  var cwMin = (Z.w - g * (cols - 1)) / cols;
  var F = fitCards(C, it, cwMin - pad * 2, ch - pad * 2, {hMax:n <= 2 ? 28 : (n <= 3 ? 25 : (n <= 4 ? 22 : 21)), dMax:n <= 2 ? 25 : (n <= 3 ? 22 : (n <= 4 ? 19 : 18)), ms:n <= 3 ? 40 : 36});   /* big enough to fill the card - no empty card space */
  for (var r = 0; r < rows; r++) {
    var row = it.slice(r * cols, r * cols + cols), m = row.length, cw = (Z.w - g * (m - 1)) / m;
    row.forEach(function(t, k){ ccard(C, Z.x + k * (cw + g), Z.y + r * (ch + g), cw, ch, t, F, o); });
  }
}
/* long cards: one full-width row per point - icon and card title on the left, the text on the right */
function rows(C, Z, o){
  if (C.it.length > 10) return cards(C, Z, {cols:4});   /* too many for one row each: a card grid */
  var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
  var P = C.P, g = n > 6 ? 8 : 14, rh = Math.min((Z.h - g * (n - 1)) / n, 132), pad = 18, ms = Math.min(40, rh - 24), lw = Math.round(Z.w * (o.lw || 0.32));
  var hs = C.groupFit(heads(it), lw - ms - 30, rh - 20, {font:'head', max:21, min:12, maxLines:2});
  var ds = C.groupFit(descs(it), Z.w - lw - pad * 2 - 20, rh - 16, {font:'body', max:20, min:10, lh:1.4});
  var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + g);
    C.rect(Z.x, y, Z.w, rh, {fill:tint(P), r:16});
    C.rect(Z.x, y, 6, rh, {fill:P.accent, r:0});
    D.mark(C, Z.x + pad + 6, y + (rh - ms) / 2, ms, D.iconFor(C.s, t.i, (t.h || '') + ' ' + t.d), {});
    if (t.h) C.text(Z.x + pad + 6 + ms + 14, y + 6, lw - ms - 34, rh - 12, t.h, {font:'head', size:hs, color:P.title || P.text, va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.rect(Z.x + lw, y + 16, 2, rh - 32, {fill:P.accent, op:0.5});
    C.text(Z.x + lw + 20, y + 6, Z.w - lw - pad - 20, rh - 12, t.d, {font:'body', size:ds, color:P.text, va:'m', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* numbered rows (agenda): a number disc, the item, a soft rule */
function numrows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var P = C.P, cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 36, cw = (Z.w - gx * (cols - 1)) / cols, g = 12, rh = Math.min(84, (Z.h - g * (per - 1)) / per);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 110, rh * 0.5, {font:'head', max:22, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - 110, rh * 0.4, {font:'body', max:15, min:10, maxLines:1});
  var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g), two = t.h && t.d, ns = Math.min(46, rh - 18);
    C.rect(x, y, cw, rh, {fill:tint(P), r:14});
    C.ell(x + 16, y + (rh - ns) / 2, ns, ns, {fill:P.accent});
    C.text(x + 16, y + (rh - ns) / 2, ns, ns, pad2(k + 1), {font:'head', size:Math.round(ns * 0.4), wt:800, color:P.onAccent || '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    C.text(x + ns + 34, y + (two ? rh * 0.12 : 0), cw - ns - 50, two ? rh * 0.46 : rh, t.h || t.d, {font:'head', size:hs, color:P.title || P.text, va:two ? 'b' : 'm', lh:1.15, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    if (two) C.text(x + ns + 34, y + rh * 0.6, cw - ns - 50, rh * 0.36, t.d, {font:'body', size:ds, color:P.muted, lh:1.3, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats, Classic style: big numbers centred in columns with thin dividers, the label under each */
function stats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var P = C.P, cw = Z.w / n, iw = cw - 36, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, iw, 110, {font:'head', max:n >= 4 ? 64 : 78, min:26, maxLines:1});
  var ls = C.groupFit(labs, iw, Z.h * 0.42, {font:'body', max:n >= 4 ? 16.5 : 18, min:10.5, lh:1.4});
  var lh = 0; labs.forEach(function(l){ lh = Math.max(lh, C.measureText(l, iw, {font:'body', max:ls, min:ls, lh:1.4}).h); });
  var xs = st.map(function(t){ return t.x || ''; }), xh = 0, xsz = 0;
  if (xs.some(Boolean)) { xsz = C.groupFit(xs, iw, Z.h * 0.3, {font:'body', max:14.5, min:10, lh:1.4}); xs.forEach(function(s){ if (s) xh = Math.max(xh, C.measureText(s, iw, {font:'body', max:xsz, min:xsz, lh:1.4}).h); }); }
  var blockH = vs * 1.1 + 14 + 3 + 14 + lh + (xh ? xh + 10 : 0), y0 = Z.y + Math.max(0, (Z.h - blockH) / 2);
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k > 0) C.rect(x, y0 - 6, 1.5, blockH + 12, {fill:P.line});
    C.text(x + 18, y0, iw, vs * 1.1, vals[k], {font:'head', size:vs, color:P.title || P.accent, align:'c', lh:1.05, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + cw / 2 - 22, y0 + vs * 1.1 + 12, 44, 3, {fill:P.accent, r:2});
    C.text(x + 18, y0 + vs * 1.1 + 29, iw, lh + 4, labs[k], {font:'body', size:ls, color:P.text, align:'c', lh:1.4, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 18, y0 + vs * 1.1 + 39 + lh, iw, xh + 4, t.x, {font:'body', size:xsz, color:P.muted, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* stat cards: the same numbers, each in a soft card */
function statCards(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var P = C.P, g = 20, cw = (Z.w - g * (n - 1)) / n, pad = 24, iw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, iw, 100, {font:'head', max:n >= 4 ? 56 : 68, min:24, maxLines:1});
  var ls = C.groupFit(labs, iw, Z.h - pad * 2 - vs * 1.1 - 60, {font:'body', max:17, min:10.5, lh:1.4});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + g);
    C.rect(x, Z.y, cw, Z.h, {fill:tint(P), r:18});
    C.rect(x, Z.y, cw, 6, {fill:P.accent, r:0});
    D.mark(C, x + pad, Z.y + pad + 6, 40, D.iconFor(C.s, t.i, labs[k]), {});
    C.text(x + pad, Z.y + pad + 62, iw, vs * 1.1, vals[k], {font:'head', size:vs, color:P.title || P.accent, lh:1.05, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, Z.y + pad + 72 + vs * 1.1, iw, Z.h - pad * 2 - 72 - vs * 1.1, labs[k], {font:'body', size:ls, color:P.text, lh:1.4, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
  });
}
/* the plain-text slide: one soft panel with an accent edge */
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var P = C.P;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:tint(P), r:18});
  C.rect(Z.x, Z.y, 8, Z.h, {fill:P.accent, r:0});
  var pad = 44, y = Z.y + pad, w = Z.w - pad * 2 - 8;
  if (it.h) { var he = C.text(Z.x + pad + 8, y, w, 80, it.h, {font:'head', fit:{max:30, min:16}, color:P.title || P.text, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad + 8, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 22 : 26, min:13}, color:P.text, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function companyChip(C, x, y, al, w){
  var P = C.P, f = C.cover; if (!f.company) return;
  var tw = Math.min(w - 40, f.company.length * 9.5 + 10), tot = 30 + 10 + tw, x0 = al === 'c' ? x + (w - tot) / 2 : x;
  C.rect(x0, y, 30, 30, {fill:P.accent, r:7});
  C.text(x0, y, 30, 30, String(f.company).charAt(0).toUpperCase(), {font:'head', size:15, wt:800, color:P.onAccent || '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  C.text(x0 + 40, y, tw, 30, f.company, {font:'head', size:15, wt:700, color:P.title || P.text, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
}
function meta(C, x, y, w, al){
  var P = C.P, f = C.cover, cells = [];
  if (f.presenter) cells.push({ic:'mdi:account', lb:'Presented by', v:f.presenter, f:'tf-presenter', sub:f.designation});
  if (f.date) cells.push({ic:'mdi:calendar-blank', lb:'Date', v:f.date, f:'tf-date'});
  if (!cells.length) return;
  var cw = Math.min(260, w / cells.length), x0 = al === 'c' ? x + (w - cw * cells.length) / 2 : x;
  cells.forEach(function(c, k){
    var cx = x0 + k * cw;
    C.ell(cx, y, 34, 34, {fill:P.accent}); C.icon(cx + 8, y + 8, 18, c.ic, P.onAccent || '#FFFFFF');
    C.text(cx + 44, y - 1, cw - 50, 14, c.lb, {font:'body', size:10.5, wt:700, color:P.muted, caps:true, ls:0.12, lh:1});
    C.text(cx + 44, y + 15, cw - 50, 22, c.v, {font:'head', size:15, wt:700, color:P.title || P.text, lh:1.1, ed:{f:c.f}, maxLines:1});
    if (c.sub) C.text(cx + 44, y + 38, cw - 50, 16, c.sub, {font:'body', size:11.5, color:P.muted, lh:1.1, maxLines:1});
  });
}
/* cover, Classic style: centred title with a short accent rule; picture behind, beside or under it */
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {}, m = o.img, x = 72, w = W - 144, al = 'c', top = 0, sc = 1;
  if (m === 'bg') { D.imgBox(C, 0, 0, W, H, {}); C.rect(0, 0, W, H, {fill:P.bg, op:0.84}); }
  else if (m === 'right') { D.imgBox(C, Math.round(W * 0.5), 0, Math.round(W * 0.5), H, {}); x = 64; w = Math.round(W * 0.5) - 112; al = 'l'; }
  else if (m === 'left') { D.imgBox(C, 0, 0, Math.round(W * 0.5), H, {}); x = Math.round(W * 0.5) + 56; w = Math.round(W * 0.5) - 120; al = 'l'; }
  else if (m === 'bottom') { D.imgBox(C, 0, Math.round(H * 0.6), W, H - Math.round(H * 0.6), {}); sc = 0.62; top = -40; }
  if (!m) C.rect(0, 0, W, 6, {fill:P.accent});
  var Y = function(y){ return Math.round(top + y * sc); };
  companyChip(C, x, Y(130), al, w);
  var te = C.text(x, Y(178), w, Math.round(220 * sc), C.s.title, {font:'head', fit:{max:m === 'bottom' ? 60 : (al === 'c' ? 82 : 66), min:30}, color:P.title || P.text, align:al, ed:{f:'title'}, maxLines:m === 'bottom' ? 2 : 3, va:'b', lh:1.05});
  C.rect(al === 'c' ? x + (w - 90) / 2 : x, Y(412), 90, 5, {fill:P.accent, r:3});
  if (C.s.subtitle) C.text(x, Y(436), w, Math.round(88 * sc) + 10, C.s.subtitle, {font:'body', fit:{max:m === 'bottom' ? 17 : 21, min:12}, color:P.text, align:al, ed:{f:'subtitle'}, lh:1.42, maxLines:3});
  if (m !== 'bottom') meta(C, x, Y(552), w, al);
  if (f.website && m !== 'bottom') {
    var ww = Math.min(w, f.website.length * 8 + 30), wx = al === 'c' ? x + (w - ww) / 2 : x;
    C.icon(wx, Y(640), 16, 'mdi:web', P.accent);
    C.text(wx + 22, Y(638), ww - 22, 20, f.website, {font:'body', size:12.5, color:P.muted, lh:1, va:'m', ed:{f:'tf-website'}, maxLines:1});
  }
}
function closing(C, v){
  var P = C.P, o = C.o || {}, m = o.img, x = 72, w = W - 144, al = 'c';
  if (m === 'right') { D.imgBox(C, Math.round(W * 0.5), 0, Math.round(W * 0.5), H, {}); x = 64; w = Math.round(W * 0.5) - 112; al = 'l'; }
  else if (m === 'left') { D.imgBox(C, 0, 0, Math.round(W * 0.5), H, {}); x = Math.round(W * 0.5) + 56; w = Math.round(W * 0.5) - 120; al = 'l'; }
  else if (m === 'bg') { D.imgBox(C, 0, 0, W, H, {}); C.rect(0, 0, W, H, {fill:P.bg, op:0.86}); }
  if (!m) C.rect(0, 0, W, 6, {fill:P.accent});
  C.text(x, 150, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:al === 'c' ? 90 : 72, min:34}, color:P.title || P.text, align:al, ed:{f:'title'}, maxLines:2, va:'b', lh:1.05});
  C.rect(al === 'c' ? x + (w - 90) / 2 : x, 340, 90, 5, {fill:P.accent, r:3});
  if (C.s.subtitle) C.text(x, 366, w, 70, C.s.subtitle, {font:'body', fit:{max:21, min:13}, color:P.text, align:al, ed:{f:'subtitle'}, lh:1.42, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  if (al === 'c') { DSTE.contactRow(C, x, 480, w, {cards:true}); return; }
  cs.forEach(function(c, k){
    var y = 470 + k * 66;
    C.rect(x, y, Math.min(w, 460), 54, {fill:tint(P), r:12});
    D.mark(C, x + 10, y + 9, 36, c.icon, {});
    C.text(x + 58, y + 6, Math.min(w, 460) - 70, 16, c.label || '', {font:'body', size:10.5, wt:700, color:P.accent, caps:true, ls:0.12, lh:1});
    C.text(x + 58, y + 22, Math.min(w, 460) - 70, 26, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:P.text, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function quote(C, v){
  var P = C.P, o = C.o || {}, by = C.it[0];
  if (o.img === 'left' || o.img === 'right') { D.quoteSlide(C, {img:o.img, align:'l', qc:P.accent, tc:P.title}); return; }
  C.rect(0, 0, W, 6, {fill:P.accent});
  C.rect(150, 120, W - 300, 440, {fill:tint(P), r:24});
  C.text(150, 150, W - 300, 90, '“', {font:'head', size:110, wt:800, color:P.accent, align:'c', va:'t', lh:1, caps:false});
  var qe = C.text(210, 250, W - 420, 200, C.s.title, {font:'head', fit:{max:38, min:18}, color:P.title || P.text, align:'c', lh:1.25, ed:{f:'title'}, maxLines:5, va:'m'});
  if (by) C.text(210, 470, W - 420, 40, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:16, wt:700, color:P.accent, align:'c', va:'m', lh:1.2, ed:{f:'content-plain', i:by.i}, maxLines:2});
}
function agendaFn(C, o){ D.std(C, {body:numrows, img:o.img}); }
D.register({
  id:'classicx', name:'Classic', tag:'Original look · every editor tool',
  desc:'The Classic look - accent top bar, bold left titles, soft cards with round icons - rebuilt so every element can be moved, restyled and downloaded exactly as shown.',
  fonts:{head:'Montserrat', body:'Inter', headW:800, bodyW:400, headLh:1.1, gf:'Montserrat:wght@500;600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Text only', {}), V('v2', 'Full background image', {img:'bg'}), V('v3', 'Image right (full bleed)', {img:'right'}), V('v4', 'Image left (full bleed)', {img:'left'}), V('v5', 'Image bottom (full bleed)', {img:'bottom'})],
    closing:[V('v1', 'Text only', {}), V('v2', 'Image right (full bleed)', {img:'right'}), V('v3', 'Image left (full bleed)', {img:'left'}), V('v4', 'Full background image', {img:'bg'})],
    agenda:[V('v1', 'List only', {fn:agendaFn}), V('v2', 'List + image right (full bleed)', {fn:function(C){ D.std(C, {body:numrows, img:'right', cols:1}); }}), V('v3', 'Image left + list (full bleed)', {fn:function(C){ D.std(C, {body:numrows, img:'left', cols:1}); }})],
    text:[V('v1', 'Statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', '2 columns', {body:cards}), V('n2', 'Long cards', {body:rows}), V('v2', 'Columns + image right (full bleed)', {body:cards, img:'right', cols:1}), V('v3', 'Image left + columns (full bleed)', {body:cards, img:'left', cols:1}), V('v4', 'Image bottom (full bleed)', {body:cards, img:'bottom'})],
    p3:[V('v1', '3 columns', {body:cards}), V('n2', 'Long cards', {body:rows}), V('v2', 'Columns + image right (full bleed)', {body:rows, img:'right', lw:0.4}), V('v3', 'Image left + columns (full bleed)', {body:rows, img:'left', lw:0.4}), V('v4', 'Image bottom (full bleed)', {body:cards, img:'bottom'})],
    p4:[V('v1', '4 columns', {body:cards}), V('n2', 'Card grid', {body:cards, cols:2}), V('n3', 'Long cards', {body:rows}), V('v2', 'Cards + image right (full bleed)', {body:cards, img:'right', cols:2}), V('v3', 'Image left + cards (full bleed)', {body:cards, img:'left', cols:2})],
    p56:[V('v1', 'Cards', {body:cards}), V('n2', 'Long cards', {body:rows}), V('v2', 'Cards + image right (full bleed)', {body:cards, img:'right', cols:2}), V('v3', 'Image left + cards (full bleed)', {body:cards, img:'left', cols:2})],
    p78:[V('v1', 'Cards', {body:cards}), V('n2', 'Card grid', {body:cards, cols:2}), V('n3', 'Long cards', {body:rows}), V('v2', 'Cards + image right (full bleed)', {body:cards, img:'right', cols:2})],
    p9:[V('v1', 'Cards', {body:cards}), V('n2', 'Card grid', {body:cards, cols:3}), V('n3', 'Long cards', {body:rows})],   /* 9-10 points */
    stats:[V('v1', 'Stats centred', {body:stats}), V('n2', 'Stat cards', {body:statCards}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Stats + image right (full bleed)', {body:stats, img:'right'}), V('v3', 'Image left + stats (full bleed)', {body:stats, img:'left'}), V('v4', 'Stats + image bottom (full bleed)', {body:stats, img:'bottom'})],
    timeline:[V('v1', 'Arrow steps', {body:'tlChev'}), V('n2', 'Milestone line', {body:'tlH'}), V('n3', 'Milestone cards', {body:'tlCards'}), V('v2', 'Vertical + image right (full bleed)', {body:'tlV', img:'right'})],
    quote:[V('v1', 'Centred quote', {}), V('v2', 'Quote + image right', {img:'right'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();
