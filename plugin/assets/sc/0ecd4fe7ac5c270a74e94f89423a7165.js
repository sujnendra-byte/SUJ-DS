
/* Template 27 — Emerald Corporate : white pages, emerald gradient diagonal shards, diamond photo frames with white borders, arrow-tag buttons */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2, mix = u.mix;
var E1 = '#0B8A5C', E2 = '#12B886', DEEP = '#075E3F', INK = '#0A3D2B', MINT = '#E9F7F0', MINT2 = '#D3EFE2';
var GR = {ang:135, stops:[[0, E1], [100, E2]]}, GRV = {ang:160, stops:[[0, E2], [100, E1]]}, GRD = {ang:135, stops:[[0, DEEP], [100, E1]]};
var P0 = {bg:'#FFFFFF', text:'#1F2F28', title:INK, muted:'#56695F', accent:E1, accent2:E2, card:'#F1FAF5', line:'#D6EBE1', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[E1, E2, DEEP, '#34C38F', '#0E7A52', '#5AD1A3'],
  header:{kind:'left', top:96, tMax:44, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'solid', r:4, fill:'#FFFFFF', line:'#D6EBE1', lw:1.2},
  icon:{kind:'square', r:4, bg:E1, fg:'#FFFFFF'}, num:{kind:'square', r:4, bg:E1, fg:'#FFFFFF'},
  conc:{kind:'band', grad:GR, r:4, label:'Key point'},
  footer:{brand:false, page:true, color:'#7A9187'}, bodyGap:28
};

/* ---------- motif helpers ---------- */
/* arrow-tag button (home-plate) */
function tag(C, x, y, w, h, text, o){
  o = o || {};
  C.shape('homePlate', x, y, w, h, o.fill ? {fill:o.fill, line:o.line, lw:o.lw} : {grad:o.grad || GR});
  if (text != null && text !== '') C.text(x + (o.padL || 14), y, w - (o.padL || 14) - w * 0.18 - 4, h, text, {font:o.font || 'body', fit:{max:o.size || Math.round(h * 0.42), min:o.min || 11}, wt:o.wt || 600, color:o.color || '#FFFFFF', va:'m', lh:1.1, ed:o.ed || null, maxLines:o.lines || 1, caps:!!o.caps, ls:o.caps ? 0.08 : 0});
}
/* gradient diamond with an icon or number */
function gem(C, cx, cy, s, t, label){
  C.shape('diamond', cx - s / 2 - 5, cy - s / 2 - 5, s + 10, s + 10, {fill:'#FFFFFF', line:MINT2, lw:1.5});
  C.shape('diamond', cx - s / 2, cy - s / 2, s, s, {grad:GR});
  if (label != null) C.text(cx - s / 2, cy - s / 2, s, s, label, {font:'head', size:Math.round(s * 0.28), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  else K.icon(C, cx - s * 0.2, cy - s * 0.2, s * 0.4, t, '#FFFFFF');
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function logo(C, x, y){
  var f = C.cover; if (!f.company) return;
  var w = Math.min(260, f.company.length * 8.6 + 58);
  tag(C, x, y, w, 30, f.company, {size:12.5, wt:700, caps:true, ed:{f:'tf-company'}});
}

/* ---------- page background: white + emerald shards ---------- */
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var odd = (C.i || 0) % 2;
  C.shape('rtTriangle', W - 430, 0, 430, 118, {fill:MINT, rot:180});
  C.shape('rtTriangle', W - 250, 0, 250, 150, {grad:GR, rot:180});
  C.shape('rtTriangle', W - 104, 0, 104, 210, {grad:GRD, rot:180});
  if (odd) C.line(W - 560, 0, W - 250, 150, {color:E2, lw:1.2, op:0.6});
  C.shape('rtTriangle', 0, H - 34, 460, 34, {fill:MINT2});
  C.shape('rtTriangle', 0, H - 52, 240, 52, {grad:GR});
  logo(C, 70, 34);
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* columns: white cards, gradient shard in the corner with icon, big number */
function shardCols(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var gap = n > 3 ? 20 : 28, cw = (Z.w - gap * (n - 1)) / n, pad = n > 3 ? 22 : 30, iw = cw - pad * 2;
  var numH = n > 3 ? 40 : 50, top = pad + numH + 20;
  var F = K.fitHD(C, it, iw, 70, Z.h - top - pad - 8, {hMax:21, dMax:15.5, hMin:13, dMin:11});
  var bmax = 0; it.forEach(function(t){ bmax = Math.max(bmax, blockH(C, t, iw, F)); });
  var chh = Math.min(Z.h, Math.max(Z.h * 0.62, top + bmax + pad + 36)), off = Math.round((chh - top - pad - 8 - bmax) * 0.35), zy = Z.y + (Z.h - chh) / 2;
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), y = zy;
    C.rect(x, y, cw, chh, {fill:'#FFFFFF', line:'#D6EBE1', lw:1.2, r:4, shadow:0.05});
    var sw = Math.min(96, cw * 0.4);
    C.shape('rtTriangle', x + cw - sw, y, sw, sw * 0.72, {grad:GR, rot:180});
    K.icon(C, x + cw - 36, y + 9, 24, t, '#FFFFFF');
    C.text(x + pad, y + pad + off, iw * 0.6, numH, pad2(k + 1), {font:'head', size:numH, wt:700, color:E1, lh:1, caps:false, ls:0});
    C.rect(x + pad, y + pad + off + numH + 8, 34, 3, {fill:E2});
    K.hd(C, x + pad, y + top + off, iw, chh - top - off - pad - 8, t, F, {tc:INK});
    C.rect(x, y + chh - 5, cw, 5, {grad:{ang:90, stops:[[0, E1], [100, E2]]}});
  });
}

/* diamonds on one line, text centred below */
function diamondChain(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.52, 118, Z.h * 0.3), cy = Z.y + s / 2 + 8;
  var F0 = K.fitHD(C, it, cw - 28, 60, Z.h - s - 60, {hMax:20, dMax:15, hMin:13, dMin:11}), bm = 0;
  it.forEach(function(t){ bm = Math.max(bm, blockH(C, t, cw - 28, F0)); });
  var used = s + 60 + bm; if (used < Z.h) { cy += (Z.h - used) * 0.4; Z = {x:Z.x, y:Z.y + (Z.h - used) * 0.4, w:Z.w, h:Z.h - (Z.h - used) * 0.4}; }
  C.line(Z.x, cy, Z.x + Z.w, cy, {color:E2, lw:2, op:0.55});
  C.rect(Z.x, cy - 1, cw * 0.5, 2, {fill:E1});
  var ty = cy + s / 2 + 26, nh = 22;
  var F = K.fitHD(C, it, cw - 28, 60, Z.y + Z.h - ty - nh - 6, {hMax:20, dMax:15, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    gem(C, cx, cy, s, t);
    C.text(cx - 40, ty, 80, nh, pad2(k + 1), {font:'head', size:15, wt:700, color:E1, align:'c', lh:1, caps:false, ls:0.1});
    K.hd(C, cx - cw / 2 + 14, ty + nh + 6, cw - 28, Z.y + Z.h - ty - nh - 6, t, F, {align:'c', tc:INK});
  });
}

/* grid of arrow tags carrying the headings, text under a green rule */
function tagGrid(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? 2 : (n <= 6 ? 3 : 4)), rows = Math.ceil(n / cols), gx = 26, gy = 24;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, tH = Math.min(50, Math.max(38, ch * 0.3));
  var nb = tH, twid = cw * 0.94 - nb - 14 - cw * 0.94 * 0.18;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), twid, tH - 6, {font:'head', max:cols >= 4 ? 17 : 20, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d || ''; }), cw - 22, ch - tH - 22, {font:'body', max:n <= 4 ? 20 : (n <= 6 ? 18 : 16), min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d || '', cw - 22, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = Math.min(ch, tH + 12 + dmx + 30); if (ch2 < ch) { var tot = ch2 * rows + gy * (rows - 1) + 20 * (rows - 1); Z = {x:Z.x, y:Z.y + (Z.h - tot) / 2, w:Z.w, h:tot}; gy += 20; ch = ch2; }
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), shift = inRow < cols ? (cols - inRow) * (cw + gx) / 2 : 0;
    var x = Z.x + shift + c * (cw + gx), y = Z.y + r * (ch + gy), tw = cw * 0.94;
    C.shape('homePlate', x, y, tw, tH, {grad:k % 2 ? GRV : GR});
    C.rect(x, y, nb, tH, {fill:DEEP});
    C.text(x, y, nb, tH, pad2(k + 1), {font:'head', size:Math.round(tH * 0.38), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (t.h) C.text(x + nb + 14, y, twid, tH, t.h, {font:'head', size:hs, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:1});
    C.rect(x + 2, y + tH + 12, 2, ch - tH - 14, {fill:MINT2});
    C.text(x + 18, y + tH + 12, cw - 22, ch - tH - 12, t.d, {font:'body', size:ds, color:P0.muted, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* rows: arrow tag (number + heading), diamond bullet, text */
function tagRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 6 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols;
  var gy = per > 5 ? 10 : 16, rh = Math.min((Z.h - gy * (per - 1)) / per, 104), y0 = Z.y + Math.max(0, (Z.h - (rh * per + gy * (per - 1))) / 2);
  var hasH = it.some(function(t){ return t.h; }), tH = Math.min(rh - 6, 56), nb = Math.min(tH, 52);
  var compact = hasH && cw < 520;
  var tw = hasH && !compact ? Math.min(cw * 0.38, 290) : nb + 30, hw = tw - nb - 14 - tw * 0.18;
  if (compact) {
    var dx0 = tw + 34, F = K.fitHD(C, it, cw - dx0, rh * 0.5, rh - 2, {hMax:17, dMax:14, hMin:13, dMin:11, hLines:1, noBoost:per > 4});
    it.forEach(function(t, k){
      var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), my = y + rh / 2;
      C.shape('homePlate', x, my - tH / 2, tw, tH, {grad:GR});
      C.rect(x, my - tH / 2, nb, tH, {fill:DEEP});
      C.text(x, my - tH / 2, nb, tH, pad2(k + 1), {font:'head', size:Math.round(nb * 0.36), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
      var bh = blockH(C, t, cw - dx0, F);
      K.hd(C, x + dx0, y + Math.max(0, (rh - bh) / 2), cw - dx0, rh, t, F, {tc:INK});
      if (r < per - 1 && k < n - 1) C.rect(x + dx0, y + rh + gy / 2, cw - dx0, 1, {fill:P0.line});
    });
    return;
  }
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw, tH - 6, {font:'head', max:19, min:13, maxLines:2}) : 0;
  var dx = tw + 44, dw = cw - dx;
  var ds = C.groupFit(it.map(function(t){ return t.d || ''; }), dw, rh - 4, {font:hasH ? 'body' : 'head', max:hasH ? (per <= 4 ? 18 : 16) : 20, min:hasH ? 11 : 13, lh:1.35});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), my = y + rh / 2;
    C.shape('homePlate', x, my - tH / 2, tw, tH, {grad:GR});
    C.rect(x, my - tH / 2, nb, tH, {fill:DEEP});
    C.text(x, my - tH / 2, nb, tH, pad2(k + 1), {font:'head', size:Math.round(nb * 0.36), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (hasH && t.h) C.text(x + nb + 12, my - tH / 2, hw, tH, t.h, {font:'head', size:hs, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.shape('diamond', x + tw + 14, my - 7, 14, 14, {fill:E2});
    C.text(x + dx, y, dw, rh, t.d, {font:hasH ? 'body' : 'head', size:ds, wt:hasH ? null : 500, color:hasH ? P0.muted : INK, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
    if (r < per - 1 && k < n - 1) C.rect(x + dx, y + rh + gy / 2, dw, 1, {fill:P0.line});
  });
}

/* two panels split by a diagonal: mint left, emerald gradient right */
function splitDiag(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (n < 2) return shardCols(C, Z, o);
  var mid = Z.x + Z.w / 2, sl = Math.min(64, Z.h * 0.14), pad = 44;
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:{ang:120, stops:[[0, E1], [100, E2]]}});
  K.poly(C, [[Z.x, Z.y], [mid + sl, Z.y], [mid - sl, Z.y + Z.h], [Z.x, Z.y + Z.h]], {fill:MINT});
  var lw = mid - sl - Z.x - pad * 2, rx = mid + sl + pad, rw = Z.x + Z.w - rx - pad;
  var w = Math.min(lw, rw), top = 40 + 34 + 22;
  var F = K.fitHD(C, it, w, 80, Z.h - top - pad, {hMax:24, dMax:17, hMin:14, dMin:11.5});
  [[Z.x + pad, 0], [rx, 1]].forEach(function(p){
    var t = it[p[1]], x = p[0], dark = p[1] === 1, bh = blockH(C, t, w, F), y = Z.y + Math.max(pad, (Z.h - bh - top + 40) / 2 - 10);
    C.text(x + w - 120, Z.y + 16, 120, 80, pad2(p[1] + 1), {font:'head', size:72, wt:700, color:dark ? '#FFFFFF' : E1, op:0.18, align:'r', lh:1, caps:false, ls:0});
    tag(C, x, y, 118, 34, 'Option ' + pad2(p[1] + 1), dark ? {fill:'#FFFFFF', color:E1, size:13, wt:700} : {size:13, wt:700});
    K.hd(C, x, y + 34 + 22, w, Z.y + Z.h - (y + 56) - 20, t, F, dark ? {tc:'#FFFFFF', mc:'#E6FFF3'} : {tc:INK});
  });
  var d = 74, cy = Z.y + Z.h / 2;
  C.shape('diamond', mid - d / 2, cy - d / 2, d, d, {fill:'#FFFFFF', line:E1, lw:2});
  C.icon(mid - 16, cy - 16, 32, 'mdi:arrow-right', E1);
}

/* diamond photo on the left, list on the right */
function diamondPhotoList(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var r = Math.min(Z.h / 2 - 6, 205, Z.w * 0.2), cx = Z.x + r + 16, cy = Z.y + Z.h / 2;
  K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
  K.poly(C, [[cx, cy - r - 16], [cx + r + 16, cy], [cx, cy + r + 16], [cx - r - 16, cy]], {stroke:E2, lw:1.5});
  C.shape('diamond', cx + r * 0.5, cy + r * 0.42, 58, 58, {grad:GR});
  var lx = cx + r + 64, lw = Z.x + Z.w - lx, gy = n > 4 ? 8 : 14, rh = (Z.h - gy * (n - 1)) / n;
  var F = K.fitHD(C, it, lw - 64, rh * 0.5, rh - 6, {hMax:20, dMax:15.5, hMin:13, dMin:11, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), bh = blockH(C, t, lw - 64, F);
    C.shape('diamond', lx, y + rh / 2 - 19, 38, 38, {grad:k % 2 ? GRV : GR});
    C.text(lx, y + rh / 2 - 19, 38, 38, pad2(k + 1), {font:'head', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    K.hd(C, lx + 58, y + Math.max(0, (rh - bh) / 2), lw - 64, rh, t, F, {tc:INK});
    if (k < n - 1) C.rect(lx + 58, y + rh + gy / 2, lw - 58, 1, {fill:P0.line});
  });
}

/* stats: huge value, arrow-tag label, rising mint shard behind */
function statShards(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = (Z.w - 26 * (n - 1)) / n < 230 && n > 2 ? 2 : n, rows = Math.ceil(n / cols);
  if (rows > 1) { var zh = (Z.h - 24) / 2; [0, 1].forEach(function(r){ var sub = {x:Z.x, y:Z.y + r * (zh + 24), w:Z.w, h:zh}, keep = C.it; C.it = keep.slice(r * cols, r * cols + cols); statShards(C, sub, o); C.it = keep; }); return; }
  var gap = 26, cw = (Z.w - gap * (n - 1)) / n, iw = cw - 12;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, iw, Z.h * 0.3, {font:'head', max:n >= 4 ? 60 : 76, min:26, maxLines:1});
  var tH = Z.h < 300 ? 46 : 54, lsz = C.groupFit(labs, cw * 0.8 - 30, tH - 8, {font:'body', wt:600, max:16, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, Z.h * 0.3, {font:'body', max:15, min:11, lh:1.4});
  var dh = 0; st.forEach(function(t){ if (t.x) dh = Math.max(dh, C.measureText(t.x, iw, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var blk = vs * 1.1 + 16 + tH + (dh ? 14 + dh : 0), y0 = Z.y + Math.max(10, (Z.h - blk) / 2);
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    C.shape('rtTriangle', x, Z.y, cw * 0.8, Z.h, {fill:k % 2 ? MINT2 : MINT, op:0.9});
    C.text(x + 6, y0, iw, vs * 1.1, vals[k], {font:'head', size:vs, wt:700, color:k % 2 ? E1 : INK, lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.01, maxLines:1});
    tag(C, x + 6, y0 + vs * 1.1 + 16, cw * 0.86, tH, labs[k], {size:lsz, min:11, lines:2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 6, y0 + vs * 1.1 + 16 + tH + 14, iw, dh + 4, t.x, {font:'body', size:ds, color:P0.muted, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* stats: values inside outlined diamonds */
function statDiamonds(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, d = Math.min(cw - 18, Z.h * 0.62, 236);
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, d * 0.56, d * 0.26, {font:'head', max:48, min:18, maxLines:1});
  var ls = C.groupFit(labs, cw - 24, 50, {font:'head', max:19, min:13, maxLines:2});
  var lh = 0; labs.forEach(function(l){ lh = Math.max(lh, C.measureText(l, cw - 24, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var room = Z.h - d - 18 - lh - 10;
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 30, room, {font:'body', max:14.5, min:11, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = Z.y + d / 2;
    C.shape('diamond', cx - d / 2, cy - d / 2, d, d, {line:E1, lw:2.5});
    C.shape('diamond', cx - d * 0.42, cy - d * 0.42, d * 0.84, d * 0.84, {fill:k % 2 ? MINT2 : MINT});
    C.shape('diamond', cx - 11, Z.y - 11, 22, 22, {grad:GR});
    C.text(cx - d * 0.3, cy - d * 0.15, d * 0.6, d * 0.3, vals[k], {font:'head', size:vs, wt:700, color:INK, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:0, maxLines:1});
    C.text(cx - cw / 2 + 12, Z.y + d + 18, cw - 24, lh + 4, labs[k], {font:'head', size:ls, color:E1, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - cw / 2 + 15, Z.y + d + 18 + lh + 10, cw - 30, room, t.x, {font:'body', size:ds, color:P0.muted, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* timeline: rising diagonal shard band with diamond nodes, tags + text below */
function riseLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, yL = Z.y + Z.h * 0.4, yR = Z.y + Z.h * 0.06, step = n > 1 ? (yL - yR) / (n - 1) : 0;
  var ya = yL + step * 0.5, yb = yR - step * 0.5;
  K.poly(C, [[Z.x, ya], [Z.x + Z.w, yb], [Z.x + Z.w, Z.y + Z.h], [Z.x, Z.y + Z.h]], {fill:MINT, op:0.7});
  K.poly(C, [[Z.x, ya - 5], [Z.x + Z.w, yb - 5], [Z.x + Z.w, yb + 5], [Z.x, ya + 5]], {fill:E2});
  K.poly(C, [[Z.x, ya - 5], [Z.x + Z.w * 0.5, (ya + yb) / 2 - 5], [Z.x + Z.w * 0.5, (ya + yb) / 2 + 5], [Z.x, ya + 5]], {fill:E1});
  var off = Math.max(30, step / 2 + 16), tH = 34;
  var room = Z.y + Z.h - (yL + off + tH + 12);
  var hs = C.groupFit(it.map(function(t){ return t.h || pad2(1); }), cw * 0.86 - 40, tH - 6, {font:'head', max:18, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - 24, room, {font:'body', max:n <= 4 ? 17 : 15, min:11, lh:1.38});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = yL - step * k, x = cx - cw / 2 + 12;
    C.shape('diamond', cx - 18, cy - 18, 36, 36, {fill:'#FFFFFF', line:E1, lw:2});
    C.shape('diamond', cx - 10, cy - 10, 20, 20, {grad:GRD});
    tag(C, x, cy + off, cw * 0.86, tH, t.h || pad2(k + 1), {font:'head', size:hs, min:12, wt:700, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x, cy + off + tH + 12, cw - 24, Z.y + Z.h - (cy + off + tH + 12), t.d, {font:'body', size:ds, color:P0.text, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* statement: tall gradient shard + large text */
function statement(C, Z, o){
  var it = C.it[0], P = C.P; if (!it) return;
  var x = Z.x, w = Z.w, y = Z.y, h = Z.h;
  if (o.card) {
    C.rect(Z.x, Z.y, Z.w, Z.h, {fill:MINT, r:4});
    C.shape('rtTriangle', Z.x + Z.w - 200, Z.y, 200, 130, {grad:GR, rot:180});
    x += 48; w -= 260; y += 36; h -= 72;
  } else {
    C.shape('parallelogram', Z.x, Z.y, 72, Z.h, {grad:GRV});
    C.shape('parallelogram', Z.x + 50, Z.y + Z.h * 0.35, 40, Z.h * 0.65, {fill:MINT2});
    x += 128; w -= 128;
  }
  var ty = y;
  if (it.h) { tag(C, x, y, Math.min(w, Math.max(160, it.h.length * 10 + 70)), 38, it.h, {font:'head', size:17, wt:700, ed:{f:'x-part', i:it.i, p:0}}); ty += 58; }
  C.text(x, ty, w, y + h - ty, it.d, {font:'head', fit:{max:36, min:17}, wt:400, color:INK, lh:1.35, va:'m', ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function crystal(C){
  C.shape('rtTriangle', W - 600, 0, 600, H, {fill:MINT, rot:180});
  C.shape('rtTriangle', W - 330, 0, 330, 440, {grad:GR, rot:180});
  K.poly(C, [[W - 470, H], [W, H], [W, 300]], {fill:E2, op:0.9});
  K.poly(C, [[W - 220, H], [W, H], [W, 470]], {fill:DEEP});
  C.shape('parallelogram', W - 720, 520, 300, 96, {grad:GR, op:0.95});
  C.shape('parallelogram', W - 560, 140, 180, 56, {fill:MINT2});
  C.line(W - 780, 0, W - 330, 440, {color:E1, lw:1.5, op:0.5});
}
function slivers(C){
  C.shape('rtTriangle', 0, H - 40, 560, 40, {fill:MINT2});
  C.shape('rtTriangle', 0, H - 64, 300, 64, {grad:GR});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {}, img = o.img === 'diamond';
  if (img) {
    var cx = W - 330, cy = H / 2 + 10, r = 250;
    C.shape('rtTriangle', W - 470, 0, 470, 300, {fill:MINT, rot:180});
    K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
    K.poly(C, [[cx, cy - r - 22], [cx + r + 22, cy], [cx, cy + r + 22], [cx - r - 22, cy]], {stroke:E1, lw:2});
    C.shape('rtTriangle', W - 250, 0, 250, 190, {grad:GR, rot:180});
    C.shape('diamond', cx - r - 50, cy + 120, 70, 70, {grad:GR});
    C.shape('diamond', cx - r - 10, cy + 200, 36, 36, {fill:MINT2});
  } else crystal(C);
  slivers(C);
  logo(C, 70, 50);
  var tw = img ? 560 : 640;
  C.text(70, 150, tw, 270, C.s.title, {font:'head', fit:{max:68, min:32}, color:INK, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  C.rect(70, 440, 90, 5, {grad:{ang:90, stops:[[0, E1], [100, E2]]}});
  if (C.s.subtitle) C.text(70, 462, tw - 40, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) tag(C, 70, 566, Math.min(tw - 20, who.length * 8.2 + 64), 40, who, {size:14, wt:600});
  if (f.date) C.text(70, 616, 400, 24, f.date, {font:'body', size:14, wt:600, color:E1, va:'m', lh:1});
}
function closing(C, v){
  var P = C.P, o = C.o || {}, img = o.img === 'diamond';
  if (img) {
    var cx = W - 330, cy = H / 2, r = 230;
    K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
    K.poly(C, [[cx, cy - r - 22], [cx + r + 22, cy], [cx, cy + r + 22], [cx - r - 22, cy]], {stroke:E1, lw:2});
    C.shape('rtTriangle', W - 250, 0, 250, 190, {grad:GR, rot:180});
    C.shape('diamond', cx + r - 20, cy + r - 90, 80, 80, {grad:GR});
  } else crystal(C);
  slivers(C);
  logo(C, 70, 50);
  var tw = img ? 560 : 640;
  C.text(70, 150, tw, 200, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:36}, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(70, 372, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){
    var y = 476 + k * 50;
    C.shape('homePlate', 70, y, 50, 38, {grad:GR});
    C.icon(80, y + 8, 22, c.icon, '#FFFFFF');
    C.text(136, y, tw - 80, 38, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaList(C, x, y, w, h){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var rh = Math.min(62, h / n), y0 = y + (h - rh * n) / 2, s = Math.min(40, rh - 10);
  var fs = C.groupFit(it.map(function(t){ return t.h || t.d; }), w - s - 40, rh - 8, {font:'head', max:22, min:13, maxLines:1});
  it.forEach(function(t, k){
    var yy = y0 + k * rh;
    C.shape('diamond', x, yy + (rh - s) / 2, s, s, {grad:k % 2 ? GRV : GR});
    C.text(x, yy + (rh - s) / 2, s, s, pad2(k + 1), {font:'head', size:Math.round(s * 0.32), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + s + 24, yy, w - s - 24, rh, t.h || t.d, {font:'head', size:fs, wt:500, color:INK, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    if (k < n - 1) C.rect(x + s + 24, yy + rh - 0.5, w - s - 24, 1, {fill:P0.line});
  });
}
function agendaA(C, o){
  var P = C.P;
  C.shape('rtTriangle', 0, H - 300, 420, 300, {fill:MINT});
  C.shape('rtTriangle', 0, H - 190, 250, 190, {grad:GR});
  var te = C.text(70, 150, 420, 200, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:32}, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.05});
  C.rect(70, 150 + te._h + 20, 80, 5, {grad:{ang:90, stops:[[0, E1], [100, E2]]}});
  if (C.s.subtitle) C.text(70, 150 + te._h + 44, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  agendaList(C, 560, 110, W - 560 - 90, H - 200);
}
function agendaPhoto(C, o){
  var P = C.P;
  C.text(70, 96, 700, 60, C.s.title || 'Agenda', {font:'head', fit:{max:44, min:26}, color:INK, ed:{f:'title'}, maxLines:1});
  var cx = 290, cy = 420, r = 196;
  K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
  K.poly(C, [[cx, cy - r - 18], [cx + r + 18, cy], [cx, cy + r + 18], [cx - r - 18, cy]], {stroke:E2, lw:1.5});
  C.shape('diamond', cx + r * 0.5, cy + r * 0.45, 54, 54, {grad:GR});
  agendaList(C, 590, 180, W - 590 - 80, H - 250);
}
function statementPhoto(C, o){
  var P = C.P, it = C.it[0];
  var cx = W - 340, cy = H / 2 + 20, r = 240;
  K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
  K.poly(C, [[cx, cy - r - 20], [cx + r + 20, cy], [cx, cy + r + 20], [cx - r - 20, cy]], {stroke:E1, lw:2});
  var y = D.header(C, {x:70, w:560, top:96}, {});
  C.shape('parallelogram', 70, y + 40, 44, H - 120 - y - 40, {grad:GRV});
  if (it) C.text(146, y + 40, 470, H - 120 - y - 40, it.h ? it.h + ': ' + it.d : it.d, {font:'head', fit:{max:30, min:16}, wt:400, color:INK, lh:1.38, va:'m', ed:{f:'content-plain', i:it.i}});
}
function quote(C, v){
  var P = C.P, o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:E1}); return; }
  var photo = o.img === 'diamond', x = photo ? 70 : 330, w = photo ? 580 : W - 330 - 100;
  if (photo) {
    var cx = W - 320, cy = H / 2, r = 240;
    K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
    K.poly(C, [[cx, cy - r - 20], [cx + r + 20, cy], [cx, cy + r + 20], [cx - r - 20, cy]], {stroke:E1, lw:2});
    C.shape('diamond', 70, 110, 90, 90, {grad:GR});
    C.icon(95, 135, 40, 'mdi:format-quote-open', '#FFFFFF');
  } else {
    C.shape('diamond', 90, 200, 190, 190, {grad:GR});
    C.shape('diamond', 190, 330, 90, 90, {fill:MINT2});
    C.icon(140, 250, 90, 'mdi:format-quote-open', '#FFFFFF');
  }
  var qy = photo ? 240 : 170;
  var qe = C.text(x, qy, w, 290, C.s.title, {font:'head', fit:{max:40, min:20}, wt:400, color:INK, lh:1.3, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ': ' + by.d : by.d; tag(C, x, qy + qe._h + 34, Math.min(w, bt.length * 8.4 + 60), 40, bt, {size:14, wt:600, ed:{f:'content-plain', i:by.i}}); }
}

D.register({
  id:'emerald', name:'Emerald Corporate', tag:'Corporate · emerald',
  desc:'Crisp white pages cut by emerald gradient diagonal shards, diamond photo frames with white borders and arrow-tag buttons for corporate decks.',
  fonts:{head:'Lexend', body:'Lexend', headW:600, bodyW:300, headLh:1.1, gf:'Lexend:wght@300;400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Emerald crystal', {}), V('v2', 'Diamond photo', {img:'diamond'})],
    closing:[V('v1', 'Emerald crystal', {}), V('v2', 'Diamond photo + contacts', {img:'diamond'})],
    agenda:[V('v1', 'Title + diamond list', {fn:agendaA}), V('n2', 'Arrow-tag rows', {body:tagRows, cols:2}), V('v2', 'Diamond photo + list', {fn:agendaPhoto})],
    text:[V('v1', 'Shard statement', {body:statement}), V('n2', 'Statement card', {body:statement, card:true}), V('v2', 'Statement + diamond photo', {fn:statementPhoto}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Diagonal split', {body:splitDiag}), V('n2', 'Shard cards', {body:shardCols}), V('n3', 'Diamond chain', {body:diamondChain}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Tag rows + image right', {body:tagRows, img:'right'})],
    p3:[V('v1', 'Shard cards', {body:shardCols}), V('n2', 'Diamond chain', {body:diamondChain}), V('n3', 'Arrow-tag rows', {body:tagRows}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Image top + shard cards', {body:shardCols, img:'top'}), V('v4', 'Tag rows + image right', {body:tagRows, img:'right'})],
    p4:[V('v1', 'Diamond chain', {body:diamondChain}), V('n2', 'Shard cards', {body:shardCols}), V('n3', 'Arrow-tag grid', {body:tagGrid}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Image left + tag rows', {body:tagRows, img:'left'})],
    p56:[V('v1', 'Arrow-tag grid', {body:tagGrid}), V('n2', 'Arrow-tag rows', {body:tagRows}), V('n3', 'Diamond chain', {body:diamondChain}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Tag rows + image right', {body:tagRows, img:'right'})],
    p78:[V('v1', 'Arrow-tag grid', {body:tagGrid}), V('n2', 'Two-column tag rows', {body:tagRows, cols:2}), V('v2', 'Tag rows + image right', {body:tagRows, img:'right', cols:2})],
    stats:[V('v1', 'Shard numbers', {body:statShards}), V('n2', 'Diamond numbers', {body:statDiamonds}), V('v2', 'Shard numbers + image right', {body:statShards, img:'right'}), V('v3', 'Image left + shard numbers', {body:statShards, img:'left'})],
    timeline:[V('v1', 'Rising shard line', {body:riseLine}), V('n2', 'Arrow-tag rows', {body:tagRows}), V('n3', 'Diamond chain', {body:diamondChain}), V('v2', 'Tag rows + image right', {body:tagRows, img:'right'})],
    quote:[V('v1', 'Diamond quote', {}), V('v2', 'Quote + diamond photo', {img:'diamond'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

