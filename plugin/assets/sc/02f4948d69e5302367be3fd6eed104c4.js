
/* DS Template Scenario Layouts - every design template (not only the glass ones) gets the same set of "where does the
   text go / where does the photo go" layouts: one text panel left, right, centre, top, bottom or in a corner, over the
   page, over a full photo or beside a half photo. The panel, colours, fonts and marks are the template's own. */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || D.__scn) return; D.__scn = 1;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
function con(a, b){ return Math.abs(u.lum(a) - u.lum(b)); }
/* the text panel: the template's own card style, made solid enough to read on a photo */
function panel(C, R, onPhoto){
  var S = C.S.card || {}, P = C.P, k = S.kind || 'solid', r = Math.min(S.r != null ? S.r : 16, 28), base = null;
  if (k === 'glass') { if (!C.T.frost && onPhoto) C.rect(R.x, R.y, R.w, R.h, {fill:P.bg, op:0.55, r:r}); }
  else if (k === 'none' || k === 'rule' || (k === 'outline' && !S.fill) || (S.op != null && S.op < 0.9)) { base = P.bg; C.rect(R.x, R.y, R.w, R.h, {fill:P.bg, op:onPhoto ? 0.94 : 1, r:k === 'none' || k === 'rule' ? Math.min(r, 14) : r}); }
  var q = D.card(C, R.x, R.y, R.w, R.h, {});
  var fill = k === 'glass' ? (onPhoto ? P.bg : P.bg) : (base || S.fill || P.card || P.bg);
  var tc = q.tc || P.text, mc = q.mc || P.muted;
  if (base || k === 'glass') { tc = P.title || P.text; mc = P.muted || P.text; if (k === 'glass' && S.tc) { tc = S.tc; mc = S.mc || S.tc; } }
  var ac = P.accent; if (con(ac, fill) < 0.22) ac = (P.accent2 && con(P.accent2, fill) >= 0.22) ? P.accent2 : tc;
  var inset = (k === 'bar' ? (S.barW || 6) : 0);
  return {tc:tc, mc:mc, ac:ac, fill:fill, inset:inset, top:(k === 'top' ? (S.barW || 5) : 0)};
}
/* nothing stays blank: every area the panel leaves free gets a quiet tinted field with a graphic made from the slide's
   own topic - the icon of the slide in the middle and the icons of its points around it, joined by lines */
function zones(R, o){
  var pos = o.pos || 'l', Z = [], g = 24, L = 56, T0 = 56, RT = W - 56, B = H - 68;
  function z(x, y, w, h){ if (w >= 90 && h >= 90) Z.push({x:x, y:y, w:w, h:h}); }
  if (pos === 'l') z(R.x + R.w + g, R.y, RT - (R.x + R.w + g), R.h);
  else if (pos === 'r') z(L, R.y, R.x - g - L, R.h);
  else if (pos === 't') z(R.x, R.y + R.h + g, R.w, B - (R.y + R.h + g));
  else if (pos === 'b') z(R.x, T0, R.w, R.y - g - T0);
  else if (pos === 'c') { z(L, R.y, R.x - g - L, R.h); z(R.x + R.w + g, R.y, RT - (R.x + R.w + g), R.h); }
  else if (pos === 'tl') { z(R.x + R.w + g, T0, RT - (R.x + R.w + g), B - T0); z(R.x, R.y + R.h + g, R.w, B - (R.y + R.h + g)); }
  else if (pos === 'bl') { z(R.x + R.w + g, T0, RT - (R.x + R.w + g), R.y + R.h - T0); z(R.x, T0, R.w, R.y - g - T0); }
  else if (pos === 'tr') { z(L, T0, R.x - g - L, B - T0); z(R.x, R.y + R.h + g, R.w, B - (R.y + R.h + g)); }
  else if (pos === 'br') { z(L, T0, R.x - g - L, R.y + R.h - T0); z(R.x, T0, R.w, R.y - g - T0); }
  return Z;
}
function icons(C, n){
  var a = [], seen = {}, i, ic;
  for (i = 0; i < C.it.length && a.length < n; i++) { ic = D.iconFor(C.s, C.it[i].i, (C.it[i].h || '') + ' ' + (C.it[i].d || '')); if (!seen[ic]) { seen[ic] = 1; a.push(ic); } }
  var more = [C.s.title, C.s.subtitle, C.s.conclusion, 'idea vision', 'growth scale', 'goal target', 'data insight', 'team people'];
  for (i = 0; i < more.length && a.length < n; i++) { if (!more[i]) continue; ic = D.iconFor({}, 0, more[i]); if (!seen[ic]) { seen[ic] = 1; a.push(ic); } }
  return a;
}
function tile(C, cx, cy, s, ic, ac, r, strong){
  C.rect(cx - s / 2, cy - s / 2, s, s, {fill:u.mix(C.P.bg, ac, 0.1), r:Math.min(r, s * 0.28)});      /* hides the joining line behind the tile */
  C.rect(cx - s / 2, cy - s / 2, s, s, {fill:ac, op:strong ? 0.26 : 0.16, r:Math.min(r, s * 0.28)});
  C.icon(cx - s * 0.29, cy - s * 0.29, s * 0.58, ic, ac);
}
function graphic(C, F, ac, r, plain){
  C.rect(F.x, F.y, F.w, F.h, {fill:ac, op:0.1, r:r});
  var wide = F.w >= F.h * 1.5, tall = F.h >= F.w * 1.5, i;
  if (plain) {                                                    /* cover, closing, quote: calm stepped bars */
    var n = wide ? 5 : 3, g = 14, p = Math.min(34, Math.min(F.w, F.h) * 0.16), bw = wide ? (F.w - p * 2 - g * (n - 1)) / n : F.w - p * 2, bh = wide ? F.h - p * 2 : (F.h - p * 2 - g * (n - 1)) / n;
    for (i = 0; i < n; i++) C.rect(F.x + p + (wide ? i * (bw + g) : 0), F.y + p + (wide ? 0 : i * (bh + g)), bw, bh, {fill:ac, op:0.1 + 0.06 * i, r:Math.min(r, 12)});
    var sz = Math.min(F.w, F.h) * (wide || tall ? 0.42 : 0.3);
    C.icon(F.x + (F.w - sz) / 2, F.y + (F.h - sz) / 2, sz, D.iconFor(C.s, 0, (C.s.title || '') + ' ' + (C.s.subtitle || '')), ac);
    return;
  }
  if (wide || tall) {                                             /* a chain of the points' icons */
    var len = wide ? F.w : F.h, th = wide ? F.h : F.w, m = Math.max(3, Math.min(5, C.it.length || 3)), ts = Math.min(th * 0.56, 104, (len - 40) / m * 0.7), ic = icons(C, m), step = (len - 40 - ts) / Math.max(1, m - 1), c0 = 20 + ts / 2;
    if (wide) C.line(F.x + c0, F.y + F.h / 2, F.x + c0 + step * (m - 1), F.y + F.h / 2, {color:ac, lw:2, op:0.45});
    else C.line(F.x + F.w / 2, F.y + c0, F.x + F.w / 2, F.y + c0 + step * (m - 1), {color:ac, lw:2, op:0.45});
    for (i = 0; i < m; i++) tile(C, wide ? F.x + c0 + step * i : F.x + F.w / 2, wide ? F.y + F.h / 2 : F.y + c0 + step * i, ts, ic[i] || ic[0], ac, r, i === 0);
    return;
  }
  var cx = F.x + F.w / 2, cy = F.y + F.h / 2, mn = Math.min(F.w, F.h), cs = mn * 0.34, ss = mn * 0.19, k = Math.max(2, Math.min(4, C.it.length || 3)), all = icons(C, k + 1);
  var rx = F.w / 2 - ss / 2 - 22, ry = F.h / 2 - ss / 2 - 22, ang = k === 2 ? [-45, 135] : k === 3 ? [-90, 30, 150] : [-135, -45, 45, 135], pts = [];
  for (i = 0; i < k; i++) { var q = ang[i] * Math.PI / 180, d = Math.min(rx / Math.max(0.01, Math.abs(Math.cos(q))), ry / Math.max(0.01, Math.abs(Math.sin(q)))); d = Math.min(d, mn * 0.44); pts.push([cx + Math.cos(q) * d, cy + Math.sin(q) * d]); }
  for (i = 0; i < k; i++) C.line(cx, cy, pts[i][0], pts[i][1], {color:ac, lw:2, op:0.45});
  tile(C, cx, cy, cs, D.iconFor({}, 0, (C.s.title || '') + ' ' + (C.s.subtitle || '')), ac, r, true);
  for (i = 0; i < k; i++) tile(C, pts[i][0], pts[i][1], ss, all[i] || all[0], ac, r, false);
}
function field(C, R, o){
  var P = C.P, ac = P.accent2 && con(P.accent2, P.bg) > 0.12 ? P.accent2 : P.accent, r = Math.min((C.S.card || {}).r != null ? C.S.card.r : 16, 28);
  var plain = C.kind === 'cover' || C.kind === 'closing' || C.kind === 'quote';
  zones(R, o).forEach(function(F){ graphic(C, F, ac, r, plain); });
}
var RECTS = {l:[56, 56, 600, 596], r:[624, 56, 600, 596], t:[56, 48, 1168, 336], b:[56, 320, 1168, 332], c:[190, 76, 900, 556], tl:[56, 56, 680, 420], tr:[544, 56, 680, 420], bl:[56, 232, 680, 420], br:[544, 232, 680, 420], w:[56, 48, 1168, 604]};
function hit(e, B){
  if (e.x == null || e.w == null) return true;                  /* lines, paths: cannot tell, so they go */
  return !(e.x >= B.x + B.w || e.x + e.w <= B.x || e.y >= B.y + B.h || e.y + e.h <= B.y);
}
/* the template's own background decoration stays, except where the panel, the colour field or the photo will sit */
function clearUnder(C, boxes){
  C.els = C.els.filter(function(e){ for (var i = 0; i < boxes.length; i++) if (boxes[i] && hit(e, boxes[i])) return false; return true; });
}
function place(C, o){
  var ph = o.ph, a = RECTS[o.pos || 'l'] || RECTS.l, R = {x:a[0], y:a[1], w:a[2], h:a[3]};
  if (ph === 'left') R = {x:470, y:64, w:754, h:580}; else if (ph === 'right') R = {x:56, y:64, w:754, h:580}; else if (ph === 'top') R = {x:90, y:230, w:1100, h:422}; else if (ph === 'bottom') R = {x:90, y:56, w:1100, h:422};
  var IM = ph === 'full' ? {x:0, y:0, w:W, h:H} : ph === 'left' ? {x:0, y:0, w:600, h:H} : ph === 'right' ? {x:680, y:0, w:600, h:H} : ph === 'top' ? {x:0, y:0, w:W, h:350} : ph === 'bottom' ? {x:0, y:370, w:W, h:350} : null;
  clearUnder(C, [R, IM].concat(ph ? [] : zones(R, o)));
  if (ph === 'full') D.imgBox(C, 0, 0, W, H, {r:0});
  else if (ph === 'left') D.imgBox(C, 0, 0, 600, H, {r:0}); else if (ph === 'right') D.imgBox(C, 680, 0, 600, H, {r:0}); else if (ph === 'top') D.imgBox(C, 0, 0, W, 350, {r:0}); else if (ph === 'bottom') D.imgBox(C, 0, 370, W, 350, {r:0});
  else field(C, R, o);
  var q = panel(C, R, !!ph); q.R = R; return q;
}
function mark(C, x, y, q){ C.rect(x, y, 46, 5, {fill:q.ac}); }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function listIn(C, Z, q){
  var it = C.it.slice(0, 16), n = it.length; if (!n) return;
  var gx = 28, mxc = Math.max(1, Math.floor((Z.w + gx) / (236 + gx))), rw, cl;
  if (Z.w <= 860 && n <= 4) { cl = 1; rw = n; } else { rw = Math.ceil(n / mxc); cl = Math.ceil(n / rw); }
  var cw = (Z.w - gx * (cl - 1)) / cl, ch = Math.min(Z.h / rw, cl === 1 ? 104 : 190);
  if (C.kind === 'stats') {
    var vals = it.map(sv), labs = it.map(sl), vs = C.groupFit(vals, cw, ch * 0.5, {font:'head', max:54, min:20, maxLines:1}), ls = C.groupFit(labs, cw, Math.max(24, ch - vs * 1.15 - 10), {font:'body', max:15, min:11, lh:1.3, maxLines:3});
    it.forEach(function(t, k){
      var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
      C.text(x, y, cw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:q.ac, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.text(x, y + vs * 1.1 + 4, cw, Math.max(24, ch - vs * 1.15 - 10), labs[k], {font:'body', size:ls, wt:500, color:q.tc, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
    });
    return;
  }
  var simple = !it.some(function(t){ return t.h && t.d; });
  if (simple) {
    if (Z.w <= 860 && n <= 7) { cl = 1; rw = n; cw = Z.w; ch = Math.min(Z.h / rw, 76); }
    var tx = it.map(function(t){ return t.h || t.d || ''; }), fs = C.groupFit(tx, cw - 50, ch - 12, {font:'body', max:20, min:12, lh:1.25, maxLines:2});
    it.forEach(function(t, k){
      var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
      if (Math.floor(k / cl)) C.rect(x, y - 1, cw, 1, {fill:q.tc, op:0.18});
      C.text(x, y, 44, ch, pad2(k + 1), {font:'head', size:Math.min(20, fs + 2), wt:800, color:q.ac, va:'m', lh:1, caps:false});
      C.text(x + 50, y, cw - 50, ch, tx[k], {font:'body', size:fs, wt:600, color:q.tc, va:'m', lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:0} : {f:'content-plain', i:t.i}, maxLines:2});
    });
    return;
  }
  var nw = 44, tw = cw - nw, F = K.fitHD(C, it, tw, 30, ch - 14, {hMax:18, dMax:it.some(function(t){ return t.h; }) ? 14 : 17, hMin:11.5, dMin:10});
  it.forEach(function(t, k){
    var cc = k % cl, r = Math.floor(k / cl), x = Z.x + cc * (cw + gx), y = Z.y + r * ch, oy = cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8;
    if (r && cl === 1) C.rect(x, y - 1, cw, 1, {fill:q.tc, op:0.18});
    C.text(x, y + oy + 1, 40, 22, pad2(k + 1), {font:'head', size:Math.max(13, Math.min(18, F.hs || 14)), wt:800, color:q.ac, lh:1, caps:false});
    K.hd(C, x + nw, y + oy, tw, ch - 12, t, F, {tc:q.tc, mc:q.mc});
  });
}
function scene(C, o){
  o = o || C.o || {};
  var q = place(C, o), R = q.R, pad = Math.min(40, Math.max(28, R.w * 0.05)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad + q.top, bot = R.y + R.h - pad, wide = R.w > 860;
  mark(C, x, y, q); y += 22;
  var te = C.text(x, y, wide ? w * 0.7 : w, 100, C.s.title || '', {font:'head', fit:{max:wide ? 36 : 31, min:19}, color:q.tc, lh:1.1, ed:{f:'title'}, maxLines:2}); y += te._h + 6;
  if (C.s.subtitle) { var se = C.text(x, y, wide ? w * 0.7 : w, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:q.mc, lh:1.35, ed:{f:'subtitle'}, maxLines:2}); y += se._h + 6; }
  y += 12;
  if (C.kind === 'text') {
    var it = C.it[0]; if (!it) return;
    if (it.h) { var he = C.text(x, y, w, 60, it.h, {font:'head', fit:{max:24, min:15}, color:q.ac, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 10; }
    C.text(x, y, w, bot - y, it.d, {font:'body', fit:{max:it.h ? 21 : 24, min:13}, color:q.tc, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
    return;
  }
  if (C.s.conclusion && bot - y > 230) { bot -= 50; C.rect(x, bot + 12, w, 1, {fill:q.tc, op:0.22}); C.text(x, bot + 18, w, 32, C.s.conclusion, {font:'body', fit:{max:14, min:11}, wt:600, color:q.ac, va:'m', lh:1.25, ed:{f:'x-conc'}, maxLines:2}); }
  listIn(C, {x:x, y:y, w:w, h:bot - y}, q);
}
function quote(C, o){
  o = o || C.o || {};
  var q = place(C, o), R = q.R, by = C.it[0], pad = Math.min(54, Math.max(34, R.w * 0.06)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad, mid = (o.pos || 'c') === 'c' || o.pos === 't' || o.pos === 'b';
  C.text(mid ? x + w / 2 - 40 : x - 4, y - 16, 80, 80, '“', {font:'head', size:92, wt:800, color:q.ac, lh:1, align:mid ? 'c' : 'l', caps:false, ls:0}); y += 62;
  C.text(x, y, w, R.y + R.h - pad - y - (by ? 46 : 0), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 38 : 31, min:18}, wt:600, color:q.tc, lh:1.22, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:6});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, R.y + R.h - pad - 26, w, 26, bt, {font:'body', size:14, wt:600, color:q.ac, lh:1.1, align:mid ? 'c' : 'l', va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
function cover(C){
  var o = C.o || {}, f = C.cover, q = place(C, o), R = q.R, pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad + q.top, bot = R.y + R.h - pad, mid = o.pos === 'c';
  if (f.company) C.text(x, y, w, 22, f.company, {font:'body', size:13.5, wt:700, color:q.ac, align:mid ? 'c' : 'l', va:'m', lh:1, caps:true, ls:0.14, ed:{f:'tf-company'}, maxLines:1});
  var who = [f.presenter, f.designation].filter(Boolean).join('  ·  '), th = bot - y - 40 - (who ? 50 : 0) - (C.s.subtitle ? 70 : 0);
  C.text(x, y + 40, w, Math.max(80, th), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 62 : 50, min:26}, color:q.tc, lh:1.06, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:R.h < 360 ? 2 : 4});
  if (C.s.subtitle) C.text(x, y + 48 + Math.max(80, th), w, 62, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:q.mc, lh:1.4, align:mid ? 'c' : 'l', ed:{f:'subtitle'}, maxLines:3});
  if (who) { if (!mid) C.rect(x, bot - 30, 34, 3, {fill:q.ac}); C.text(x + (mid ? 0 : 46), bot - 40, w - (mid ? 0 : 46), 24, who, {font:'body', size:14, wt:700, color:q.tc, lh:1, va:'m', align:mid ? 'c' : 'l', ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C){
  var o = C.o || {}, q = place(C, o), R = q.R, pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad + q.inset, w = R.w - pad * 2 - q.inset, y = R.y + pad + q.top, wide = R.w > 860, cs = D.contacts(C), tw = wide ? w * 0.52 : w;
  mark(C, x, y, q); y += 24;
  var te = C.text(x, y, tw, wide ? R.h - pad * 2 - 110 : 150, C.s.title || 'Thank You', {font:'head', fit:{max:wide ? 66 : 54, min:28}, color:q.tc, lh:1.06, va:wide ? 'm' : 't', ed:{f:'title'}, maxLines:2});
  var sy = wide ? R.y + R.h - pad - 70 : y + te._h + 10;
  if (C.s.subtitle) C.text(x, sy, tw, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:q.mc, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var cx = wide ? x + w * 0.58 : x, cw = wide ? w * 0.42 : w, n = cs.length, rh = Math.min(56, (wide ? R.h - pad * 2 : R.y + R.h - pad - (sy + 80)) / Math.max(1, n)), cy = wide ? R.y + (R.h - rh * n) / 2 : R.y + R.h - pad - rh * n;
  cs.forEach(function(c, k){
    var yy = cy + k * rh;
    C.icon(cx, yy + rh / 2 - 11, 22, c.icon, q.ac);
    C.text(cx + 38, yy, cw - 38, rh, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:q.tc, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(cx, yy, cw, 1, {fill:q.tc, op:0.18});
  });
}
/* the layout list. few = slides with little text also get the corner panels; many = 9-16 points only get the wide panels */
function sc(fn, mode){
  function v(id, lbl, o){ o.fn = fn; o.scn = 1; if (o.ph === 'full' || o.ph === 'bottom' || o.pos === 'b' || o.pos === 'w' || o.pos === 'bl' || o.pos === 'br') o.foot = false; return V(id, lbl, o); }
  if (mode === 'many') return [
    v('s3', 'Text panel centre', {pos:'c'}), v('s15', 'Text panel full width', {pos:'w'}),
    v('s10', 'Full photo + text centre', {img:'photo', ph:'full', pos:'c'}), v('s16', 'Full photo + wide text panel', {img:'photo', ph:'full', pos:'w'}),
    v('s13', 'Photo on top + text below', {img:'photo', ph:'top'}), v('s14', 'Text above + photo below', {img:'photo', ph:'bottom'})
  ];
  var a = [
    v('s1', 'Text panel left', {pos:'l'}), v('s2', 'Text panel right', {pos:'r'}), v('s3', 'Text panel centre', {pos:'c'}),
    v('s4', 'Text panel on top', {pos:'t'}), v('s5', 'Text panel at the bottom', {pos:'b'}),
    v('s6', 'Full photo + text left', {img:'photo', ph:'full', pos:'l'}), v('s7', 'Full photo + text right', {img:'photo', ph:'full', pos:'r'}),
    v('s8', 'Full photo + text at the bottom', {img:'photo', ph:'full', pos:'b'}), v('s9', 'Full photo + text on top', {img:'photo', ph:'full', pos:'t'}),
    v('s10', 'Full photo + text centre', {img:'photo', ph:'full', pos:'c'}),
    v('s11', 'Photo left half + text', {img:'photo', ph:'left'}), v('s12', 'Photo right half + text', {img:'photo', ph:'right'}),
    v('s13', 'Photo on top + text below', {img:'photo', ph:'top'}), v('s14', 'Text above + photo below', {img:'photo', ph:'bottom'})
  ];
  if (mode === 'few') a = a.concat([
    v('s17', 'Full photo + text top-left', {img:'photo', ph:'full', pos:'tl'}), v('s18', 'Full photo + text top-right', {img:'photo', ph:'full', pos:'tr'}),
    v('s19', 'Full photo + text bottom-left', {img:'photo', ph:'full', pos:'bl'}), v('s20', 'Full photo + text bottom-right', {img:'photo', ph:'full', pos:'br'}),
    v('s21', 'Text panel top-left', {pos:'tl'}), v('s22', 'Text panel bottom-right', {pos:'br'})
  ]);
  return a;
}
var MODE = {agenda:'', text:'few', p2:'few', p3:'few', p4:'', p56:'', p78:'', p9:'many', stats:'few', timeline:''};
function extend(T){
  if (!T || !T.id || T.frost || T.__scnDone || T.noScenario) return;
  T.__scnDone = 1;
  var tv = T.variants = T.variants || {}, k;
  for (k in MODE) { var base = D.variantsFor(T, k).slice(); tv[k] = base.concat(sc(scene, MODE[k])); }
  tv.quote = D.variantsFor(T, 'quote').slice().concat(sc(quote, 'few'));
  tv.cover = D.variantsFor(T, 'cover').slice().concat(sc(null, 'few'));
  tv.closing = D.variantsFor(T, 'closing').slice().concat(sc(null, 'few'));
  delete T.__agx;
  var oc = T.cover, ol = T.closing;
  T.cover = function(C, v){ if (C.o && C.o.scn) return cover(C); return (oc || D.genericCover).apply(this, arguments); };
  T.closing = function(C, v){ if (C.o && C.o.scn) return closing(C); return (ol || D.genericClosing).apply(this, arguments); };
}
Object.keys(D.reg || {}).forEach(function(id){ extend(D.reg[id]); });
var reg = D.register;
D.register = function(T){ var r = reg.apply(this, arguments); try { extend(D.reg[T && T.id]); } catch (e) {} return r; };
D.scenarioLayouts = {extend:extend};
})();
