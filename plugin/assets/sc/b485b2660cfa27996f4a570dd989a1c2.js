
/* Template 25 — Retro Grid Pop : cream grid paper, chunky indigo headlines, charcoal pills with offset shadows, periwinkle starburst / X stickers, tilted tags, torn ticket stubs */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2;
var INK = '#2B2E6B', CHAR = '#2D2D2D', PERI = '#9DB3E6', CREAM = '#F1ECDD', WHITE = '#FFFFFF', SOFT = '#D9DCEB', MUT = '#4C4E66';
var P0 = {bg:'#F1ECDD', text:'#23243F', title:'#2B2E6B', muted:'#4C4E66', accent:'#2B2E6B', accent2:'#9DB3E6', card:'#2D2D2D', line:'#D6CFBC', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[INK, '#5C73C7', PERI, CHAR, '#7C8FD8', '#3E4390'],
  header:{kind:'left', tMax:44, tMin:26, sMax:16, top:62},
  card:{kind:'solid', r:22, fill:'#2D2D2D', dark:true, tc:'#FFFFFF', mc:'#D8D8D8', hl:{kind:'solid', fill:INK, dark:true}},
  icon:{kind:'circle', bg:PERI, fg:INK},
  num:{kind:'circle', bg:INK, fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#2D2D2D', r:22, tc:'#FFFFFF', lc:PERI, label:'Why it matters'},
  footer:{fn:function(C){ strip(C); }}, xRows:'#FFFFFF', hubFill:'#E7E1CF', road:CHAR, flowBar:'#E3DCC8',
  imgFramed:true, imgR:18
};

/* ---------- sticker kit ---------- */
function burst(C, cx, cy, r, fill){ K.starburst(C, cx, cy, r, 12, fill || PERI, {line:INK, lw:3, inner:0.62}); }
function cross(C, cx, cy, s, rot, fill){ K.xmark(C, cx, cy, s, fill || PERI, {line:INK, lw:Math.max(2, s * 0.05), rot:rot}); }
function ringDot(C, cx, cy, r){ C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:WHITE, line:INK, lw:3}); C.ell(cx - r * 0.55, cy - r * 0.55, r * 1.1, r * 1.1, {fill:CHAR}); C.ell(cx - r * 0.2, cy - r * 0.2, r * 0.4, r * 0.4, {fill:WHITE}); }
/* sticker slab: solid ink offset shadow + filled body with ink outline */
function slab(C, x, y, w, h, fill, r, d){
  d = d == null ? 7 : d;
  C.rect(x + d, y + d, w, h, {fill:INK, r:r});
  C.rect(x, y, w, h, {fill:fill, r:r, line:INK, lw:2.5});
}
/* rotated tag (rotation on the shape only; text stays level so it keeps reading) */
function tag(C, x, y, w, h, rot, fill, text, tc, o){
  o = o || {};
  C.shape('rect', x + 4, y + 4, w, h, {fill:INK, rot:rot});
  C.shape('rect', x, y, w, h, {fill:fill, line:INK, lw:2.5, rot:rot});
  C.text(x + 6, y, w - 12, h, text, {font:o.font || 'body', size:o.size || Math.round(h * 0.44), wt:800, color:tc, align:'c', va:'m', lh:1, caps:!!o.caps, ls:o.ls || 0, ed:o.ed || null, maxLines:1});
}
/* width a single line of text needs */
function lineW(C, s, size, font, wt, maxW, caps, ls){
  for (var w = 40; w < maxW; w += 8) { var m = C.measureText(s, w, {font:font, wt:wt, caps:caps, ls:ls, max:size, min:Math.max(6, size * 0.7), maxLines:1}); if (m.n === 1 && m.s >= size) return w; }
  return maxW;
}
function F2(C, it, w, hBox, dBox, hMax, dMax, hLines){ return K.fitHD(C, it, w, hBox, dBox, {hMax:hMax, dMax:dMax, hMin:13, dMin:11, hLines:hLines || 2}); }
function dH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + dH(C, t, w, F); }
var ROT = [-5, 4, -3, 6, -6, 3, -4, 5];

/* ---------- page chrome ---------- */
function strip(C){
  var f = C.cover;
  if (f.company) C.text(64, 24, 400, 18, f.company, {font:'body', size:12, wt:700, color:CHAR, lh:1, ed:{f:'tf-company'}});
  C.rect(W - 170, 20, 106, 26, {fill:CHAR, r:13});
  C.text(W - 170, 20, 58, 26, 'Page', {font:'body', size:11, wt:700, color:WHITE, align:'c', va:'m', lh:1});
  C.ell(W - 104, 22, 36, 22, {fill:PERI}); C.text(W - 104, 22, 36, 22, pad2(C.i + 1), {font:'body', size:11, wt:800, color:INK, align:'c', va:'m', lh:1});
  C.text(64, H - 36, 400, 18, f.website || '', {font:'body', size:12, wt:700, color:CHAR, lh:1});
  if (f.presenter) C.text(W - 464, H - 36, 400, 18, 'Presented by ' + f.presenter, {font:'body', size:12, wt:700, color:CHAR, align:'r', lh:1});
}
function bg(C, kind){
  C.bg({fill:CREAM}); K.grid(C, 44, '#D9D1BB', 0.9);
  if (kind !== 'cover' && kind !== 'closing' && kind !== 'quote') { var odd = (C.i || 0) % 2; if (odd) burst(C, W - 92, 96, 34); else cross(C, W - 92, 94, 46, 12); }
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* 1. charcoal pills with offset ink shadow, periwinkle X-sticker bullet */
function rpPills(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 34, gy = n > 5 ? 16 : 20, cw = (Z.w - gx * (cols - 1) - 8) / cols;
  var rh = Math.min(o.rMax || 150, (Z.h - 8 - gy * (per - 1)) / per), bs = Math.min(50, rh * 0.52), tx = bs + 44, tw = cw - tx - 30;
  var F = F2(C, it, tw, rh * 0.36, rh - 22, n <= 3 ? 20 : 18, n <= 3 ? 16 : 14.5, 1);
  var y0 = Z.y + Math.max(0, (Z.h - 8 - (rh * per + gy * (per - 1))) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    slab(C, x, y, cw, rh, CHAR, Math.min(rh / 2, 34), 6);
    cross(C, x + 20 + bs / 2, y + rh / 2, bs, ROT[k % 8] * 2);
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + tx, y + Math.max(8, (rh - bh) / 2), tw, rh - 12, t, F, {tc:PERI, mc:'#ECECEC', hLines:1});
  });
}

/* 2. outlined cream cards topped with a tilted periwinkle tag */
function rpTagCards(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : 3, rows = Math.ceil(n / cols), gx = n >= 4 ? 22 : 30, gy = 40, cw = (Z.w - gx * (cols - 1) - 8) / cols;
  var top = 20, ch = (Z.h - top - 8 - (gy - top) * (rows - 1) - top * (rows - 1)) / rows;
  ch = Math.min(ch, 330);
  var pad = 22, tw = cw - pad * 2, tagH = o.tagH ? 40 : (rows > 1 ? 32 : 38);
  var tagFs = o.tagH ? 19 : 15;
  var F = F2(C, o.tagH ? it.map(function(t){ return {i:t.i, h:'', d:t.d}; }) : it, tw, 60, ch - tagH - 30 - pad, n <= 3 ? 22 : 19, o.tagH ? 20 : (n <= 3 ? 16 : 14.5), 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + sh + c * (cw + gx), y = Z.y + top + r * (ch + gy);
    slab(C, x, y, cw, ch, WHITE, 20, 7);
    var lab = o.tagH && t.h ? t.h : 'No. ' + pad2(k + 1), tgw = Math.min(cw - 30, lineW(C, lab, tagFs, 'body', 800, cw) + 30);
    tag(C, x + 18, y - tagH / 2 - 4, tgw, tagH, ROT[k % 8], k % 2 ? CHAR : PERI, lab, k % 2 ? WHITE : INK, {size:tagFs, ed:o.tagH && t.h ? {f:'x-part', i:t.i, p:0} : null});
    var tt = o.tagH ? {i:t.i, h:'', d:t.d} : t, bh = blockH(C, tt, tw, F), ty = y + tagH / 2 + 18;
    K.hd(C, x + pad, ty + Math.max(0, (y + ch - pad - ty - bh) * 0.35), tw, y + ch - pad - ty, tt, F, {tc:INK, mc:MUT});
  });
}

/* 3. charcoal cards with a starburst number badge */
function rpBurst(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))), rows = Math.ceil(n / cols), gx = 24, gy = 22;
  var cw = (Z.w - gx * (cols - 1) - 8) / cols, ch = (Z.h - gy * (rows - 1) - 8) / rows, pad = 22;
  var br = Math.min(34, ch * 0.2), side = cols <= 2;                       /* wide cards: text beside the burst */
  var tx = side ? pad + br * 2 + 22 : pad, tw = cw - tx - pad;
  var hTop = side ? 0 : br * 2 + 16;
  var F = F2(C, it, tw, 60, ch - pad * 2 - hTop, n <= 3 ? 21 : 18, n <= 3 ? 16 : 14, 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + sh + c * (cw + gx), y = Z.y + r * (ch + gy);
    slab(C, x, y, cw, ch, CHAR, 22, 7);
    var bh = blockH(C, t, tw, F);
    var by = side ? y + Math.max(pad, (ch - Math.max(bh, br * 2)) / 2) : y + Math.max(pad, (ch - hTop - bh) / 2);
    burst(C, x + pad + br, by + br, br + 4, k % 2 ? WHITE : PERI);
    C.text(x + pad, by, br * 2, br * 2, pad2(k + 1), {font:'head', size:Math.round(br * 0.62), color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    var ty = side ? by + Math.max(0, (br * 2 - bh) / 2) : by + hTop;
    K.hd(C, x + tx, ty, tw, y + ch - pad - ty, t, F, {tc:WHITE, mc:'#DADADA'});
  });
}

/* 4. torn ticket stubs: periwinkle stub with number, perforation, torn right edge */
function rpTickets(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 ? 2 : 1), per = Math.ceil(n / cols), gx = 36, gy = n > 4 ? 16 : 22, cw = (Z.w - gx * (cols - 1) - 8) / cols;
  var rh = Math.min(o.rMax || 140, (Z.h - 8 - gy * (per - 1)) / per), stub = o.stubYear ? Math.min(150, cw * 0.26) : Math.min(104, cw * 0.2);
  var tx = stub + 26, tw = cw - tx - 34;
  var tt = it.map(function(t){ return o.stubYear ? {i:t.i, h:'', d:t.d} : t; });
  var F = F2(C, tt, tw, rh * 0.38, rh - 20, n <= 3 ? 21 : 18, o.stubYear ? 19 : (n <= 3 ? 16.5 : 15), 1);
  var ss = C.groupFit(it.map(function(t){ return o.stubYear ? (t.h || '') : '00'; }), stub - 20, rh - 20, {font:'head', max:o.stubYear ? 30 : 36, min:13, maxLines:1});
  var y0 = Z.y + Math.max(0, (Z.h - 8 - (rh * per + gy * (per - 1))) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    /* torn right edge: zig-zag */
    var pts = [[x, y], [x + cw - 12, y]], teeth = Math.max(4, Math.round(rh / 14)), th = rh / teeth;
    for (var j = 0; j < teeth; j++) { pts.push([x + cw, y + th * (j + 0.5)]); pts.push([x + cw - 12, y + th * (j + 1)]); }
    pts.push([x, y + rh]);
    K.poly(C, pts.map(function(p){ return [p[0] + 6, p[1] + 6]; }), {fill:INK});
    K.poly(C, pts, {fill:WHITE, stroke:INK, lw:2.5});
    C.rect(x + 1.5, y + 1.5, stub - 1.5, rh - 3, {fill:k % 2 ? CHAR : PERI});
    C.line(x + stub, y + 10, x + stub, y + rh - 10, {color:INK, lw:2, dash:true});
    C.ell(x + stub - 9, y - 9, 18, 18, {fill:CREAM, line:INK, lw:2.5}); C.ell(x + stub - 9, y + rh - 9, 18, 18, {fill:CREAM, line:INK, lw:2.5});
    C.rect(x + stub - 11, y - 12, 22, 10, {fill:CREAM}); C.rect(x + stub - 11, y + rh + 2, 22, 10, {fill:CREAM});
    var lab = o.stubYear ? (t.h || pad2(k + 1)) : pad2(k + 1);
    C.text(x + 6, y, stub - 12, rh, lab, {font:'head', size:ss, color:k % 2 ? PERI : INK, align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.stubYear && t.h ? {f:'x-part', i:t.i, p:0} : null});
    var tk = tt[k], bh = blockH(C, tk, tw, F);
    K.hd(C, x + tx, y + Math.max(8, (rh - bh) / 2), tw, rh - 12, tk, F, {tc:INK, mc:MUT, hLines:1});
  });
}

/* 5. open list with X-sticker bullets and dotted rules */
function rpXList(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 ? 2 : 1), per = Math.ceil(n / cols), gx = 44, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(130, Z.h / per);
  var bs = Math.min(44, rh * 0.56), tx = bs + 24, tw = cw - tx;
  var F = F2(C, it, tw, rh * 0.4, rh - 14, n <= 4 ? 21 : 18, n <= 4 ? 16 : 14.5, 1);
  var y0 = Z.y + Math.max(0, (Z.h - rh * per) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    cross(C, x + bs / 2, y + rh / 2, bs, ROT[k % 8] * 3, k % 3 === 1 ? WHITE : PERI);
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + tx, y + Math.max(4, (rh - bh) / 2), tw, rh - 8, t, F, {tc:INK, mc:MUT, hLines:1});
    if (r < per - 1) C.line(x + tx, y + rh, x + cw, y + rh, {color:INK, lw:1.5, dash:true, op:0.45});
  });
}

/* 6. two big sticker panels (charcoal + periwinkle) joined by an X sticker */
function rpDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = 70, cw = (Z.w - gap - 8) / 2, ch = Z.h - 8, pad = 36;
  var F = F2(C, it, cw - pad * 2, 80, ch - pad * 2 - 90, 26, 19, 2);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), y = Z.y, fill = k ? PERI : CHAR, tc = k ? INK : PERI, mc = k ? '#1F2150' : '#ECECEC';
    slab(C, x, y, cw, ch, fill, 30, 8);
    tag(C, x + pad, y + pad - 4, 118, 40, ROT[k] , k ? WHITE : PERI, 'No. ' + pad2(k + 1), INK, {size:16});
    var bh = blockH(C, t, cw - pad * 2, F), ty = y + pad + 70;
    K.hd(C, x + pad, ty + Math.max(0, (y + ch - pad - ty - bh) * 0.4), cw - pad * 2, y + ch - pad - ty, t, F, {tc:tc, mc:mc});
  });
  if (n === 2) { cross(C, Z.x + cw + gap / 2 + 4, Z.y + ch / 2, 74, 8, WHITE); }
}

/* 7. stats as big starburst stickers + a label pill sized to its text */
function rpStatStickers(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var vals = st.map(function(t){ return t.h || String(t.d).split(' ')[0]; }), labs = st.map(function(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); });
  var cw = Z.w / n, r = Math.min(cw * 0.36, Z.h * 0.3, 118), cy = Z.y + r + 10;
  var vs = C.groupFit(vals, r * 1.2, r * 0.6, {font:'head', max:52, min:18, maxLines:1});
  var lw = cw - 40, ls = C.groupFit(labs, lw - 36, 60, {font:'body', wt:700, max:n <= 2 ? 19 : 16, min:11.5, maxLines:2, lh:1.25});
  var xs = st.map(function(t){ return t.x || ''; }), xsz = C.groupFit(xs, cw - 30, Z.y + Z.h - (cy + r + 110), {font:'body', max:15, min:11, lh:1.35});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    K.starburst(C, cx + 6, cy + 6, r, 14, INK, {inner:0.8});
    K.starburst(C, cx, cy, r, 14, k % 2 ? WHITE : PERI, {line:INK, lw:3, inner:0.8});
    C.text(cx - r * 0.62, cy - r * 0.35, r * 1.24, r * 0.7, vals[k], {font:'head', size:vs, color:INK, align:'c', va:'m', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    var m = C.measureText(labs[k], lw - 36, {font:'body', wt:700, max:ls, min:ls, lh:1.25}), one = m.n === 1;
    var pw = one ? Math.min(lw, lineW(C, labs[k], ls, 'body', 700, lw) + 36) : lw, ph = m.h + 22, py = cy + r + 22;
    C.rect(cx - pw / 2, py, pw, ph, {fill:CHAR, r:Math.min(ph / 2, 22)});
    C.text(cx - pw / 2 + 18, py, pw - 36, ph, labs[k], {font:'body', size:ls, wt:700, color:WHITE, align:'c', va:'m', lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(Z.x + cw * k + 15, py + ph + 12, cw - 30, Z.y + Z.h - (py + ph + 12), t.x, {font:'body', size:xsz, color:MUT, align:'c', lh:1.35});
  });
}

/* 8. stats in tall charcoal pills (columns) or pill rows when the zone is narrow */
function rpStatPills(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var vals = st.map(function(t){ return t.h || String(t.d).split(' ')[0]; }), labs = st.map(function(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); });
  var rows = o.rows || Z.w / n < 230;
  if (rows) {
    var gy = 16, rh = Math.min(120, (Z.h - 8 - gy * (n - 1)) / n), vw = Math.min(230, Z.w * 0.4), y0 = Z.y + Math.max(0, (Z.h - 8 - rh * n - gy * (n - 1)) / 2);
    var vs = C.groupFit(vals, vw - 40, rh - 16, {font:'head', max:40, min:18, maxLines:1});
    var lsz = C.groupFit(labs, Z.w - vw - 60, rh * 0.5, {font:'body', wt:700, max:18, min:12, maxLines:2, lh:1.25});
    var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), Z.w - vw - 60, rh * 0.4, {font:'body', max:14, min:11, maxLines:2});
    st.forEach(function(t, k){
      var y = y0 + k * (rh + gy);
      slab(C, Z.x, y, Z.w - 8, rh, CHAR, rh / 2, 6);
      C.rect(Z.x + 8, y + 8, vw - 16, rh - 16, {fill:k % 2 ? WHITE : PERI, r:(rh - 16) / 2});
      C.text(Z.x + 16, y + 8, vw - 32, rh - 16, vals[k], {font:'head', size:vs, color:INK, align:'c', va:'m', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      var lh2 = C.measureText(labs[k], Z.w - vw - 60, {font:'body', wt:700, max:lsz, min:lsz, lh:1.25}).h, xh = t.x ? xs * 1.35 + 4 : 0;
      var ty = y + (rh - lh2 - xh) / 2;
      C.text(Z.x + vw + 12, ty, Z.w - vw - 60, lh2 + 2, labs[k], {font:'body', size:lsz, wt:700, color:WHITE, lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
      if (t.x) C.text(Z.x + vw + 12, ty + lh2 + 4, Z.w - vw - 60, xh, t.x, {font:'body', size:xs, color:'#CFCFCF', lh:1.3, maxLines:1});
    });
    return;
  }
  var gx = 26, cw = (Z.w - gx * (n - 1) - 8) / n, ch = Z.h - 8, pad = 24, tw = cw - pad * 2;
  var vs2 = C.groupFit(vals, tw, 90, {font:'head', max:n <= 2 ? 64 : 46, min:22, maxLines:1});
  var ls2 = C.groupFit(labs, tw, 70, {font:'body', wt:700, max:n <= 2 ? 22 : 18, min:12, maxLines:3, lh:1.25});
  var xs2 = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs2 * 1.1 - 150, {font:'body', max:15, min:11, lh:1.35});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), y = Z.y + 14;
    slab(C, x, y, cw, ch - 14, CHAR, Math.min(cw / 2, 60), 7);
    cross(C, x + cw - 18, y + 6, 46, ROT[k] * 3, k % 2 ? WHITE : PERI);
    var yy = y + Math.max(40, (ch - 14) * 0.2);
    C.text(x + pad, yy, tw, vs2 * 1.1, vals[k], {font:'head', size:vs2, color:k % 2 ? WHITE : PERI, align:'c', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    yy += vs2 * 1.1 + 16; C.rect(x + cw / 2 - 24, yy, 48, 4, {fill:PERI, r:2}); yy += 20;
    var lh3 = C.measureText(labs[k], tw, {font:'body', wt:700, max:ls2, min:ls2, lh:1.25}).h;
    C.text(x + pad, yy, tw, lh3 + 2, labs[k], {font:'body', size:ls2, wt:700, color:WHITE, align:'c', lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + pad, yy + lh3 + 10, tw, y + ch - 14 - pad - (yy + lh3 + 10), t.x, {font:'body', size:xs2, color:'#CFCFCF', align:'c', lh:1.35});
  });
}

/* 9. timeline: chunky charcoal track, ring stops, tilted year tags above, sticker notes below */
function rpTrack(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, ty = Z.y + 96, th = 26;
  C.rect(Z.x + 4 + 5, ty + 5, Z.w - 8, th, {fill:INK, r:th / 2});
  C.rect(Z.x + 4, ty, Z.w - 8, th, {fill:CHAR, r:th / 2, line:INK, lw:2});
  var ys = it.map(function(t){ return t.h || pad2(t.i + 1); }), yz = C.groupFit(ys, cw - 60, 40, {font:'head', max:24, min:13, maxLines:1});
  var tt = it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), ny = ty + th + 44, nh = Z.y + Z.h - 8 - ny;
  var F = F2(C, tt, cw - 58, 1, nh - 44, 16, 18, 1);
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    var tw = Math.min(cw - 20, lineW(C, ys[k], yz, 'head', 400, cw) + 34);
    tag(C, cx - tw / 2, Z.y + 8, tw, 50, ROT[k % 8], k % 2 ? WHITE : PERI, ys[k], INK, {font:'head', size:yz, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.line(cx, Z.y + 62, cx, ty - 4, {color:INK, lw:2.5});
    ringDot(C, cx, ty + th / 2, 17);
    var bh = dH(C, tt[k], cw - 58, F), h = Math.min(nh, Math.max(bh + 56, nh * 0.7));
    slab(C, cx - cw / 2 + 8, ny, cw - 22, h, WHITE, 18, 5);
    C.rect(cx - 16, ny - 26, 3, 26, {fill:INK});
    K.hd(C, cx - cw / 2 + 26, ny + (h - bh) / 2, cw - 58, h - 20, tt[k], F, {tc:INK, mc:CHAR, align:'c'});
  });
}

/* 10. agenda: white sticker tiles, periwinkle number stickers */
function rpAgendaTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : Math.ceil(n / 2), rows = Math.ceil(n / cols), gx = 20, gy = 24, cw = (Z.w - gx * (cols - 1) - 8) / cols, ch = Math.min(200, (Z.h - gy * (rows - 1) - 8) / rows);
  var d = Math.min(56, ch * 0.4), pad = 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - pad * 2, ch - d - pad * 2 - 12, {font:'head', max:n <= 4 ? 28 : 23, min:13, maxLines:2});
  var y0 = Z.y + Math.max(0, (Z.h - 8 - ch * rows - gy * (rows - 1)) / 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + sh + c * (cw + gx), y = y0 + r * (ch + gy);
    slab(C, x, y, cw, ch, k % 2 ? WHITE : '#FBF8F0', 18, 6);
    C.ell(x + pad, y + pad, d, d, {fill:k % 2 ? CHAR : PERI, line:INK, lw:2.5});
    C.text(x + pad, y + pad, d, d, pad2(k + 1), {font:'head', size:Math.round(d * 0.36), color:k % 2 ? WHITE : INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad, y + pad + d + 10, cw - pad * 2, ch - d - pad * 2 - 10, t.h || t.d, {font:'head', size:hs, color:INK, lh:1.12, va:'m', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
  });
}

/* 11. agenda: charcoal pill chips with starburst numbers */
function rpAgendaPills(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, gy = 12, cw = (Z.w - gx * (cols - 1) - 8) / cols, rh = Math.min(70, (Z.h - 8 - gy * (per - 1)) / per);
  var br = rh * 0.5, hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - br * 2 - 60, rh - 12, {font:'body', wt:700, max:20, min:12.5, maxLines:1});
  var y0 = Z.y + Math.max(0, (Z.h - 8 - rh * per - gy * (per - 1)) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    slab(C, x + br, y, cw - br, rh, CHAR, rh / 2, 5);
    burst(C, x + br, y + rh / 2, br + 2, k % 2 ? WHITE : PERI);
    C.text(x, y, br * 2, rh, pad2(k + 1), {font:'head', size:Math.round(br * 0.6), color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + br * 2 + 20, y, cw - br * 2 - 40, rh, t.h || t.d, {font:'body', size:hs, wt:700, color:WHITE, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}

/* 12. statement in a charcoal slab with sticker corners */
function rpStatement(C, Z, o){
  var t = C.it[0], txt = t ? t.d : (C.s.subtitle || ''), pad = Z.w > 800 ? 64 : 44;
  var h = Z.h - 16, y = Z.y + 8;
  slab(C, Z.x, y, Z.w - 8, h, CHAR, 30, 8);
  burst(C, Z.x + 14, y + 12, 38); cross(C, Z.x + Z.w - 34, y + h - 18, 52, 14, WHITE);
  var hy = y + pad - 8;
  if (t && t.h) { C.text(Z.x + pad, hy, Z.w - pad * 2, 40, t.h, {font:'head', fit:{max:30, min:16}, color:PERI, lh:1.1, maxLines:1, ed:{f:'x-part', i:t.i, p:0}}); hy += 54; }
  C.text(Z.x + pad, hy, Z.w - pad * 2 - 8, y + h - pad - hy, txt, {font:'body', fit:{max:30, min:16}, wt:600, color:WHITE, lh:1.42, va:'m', ed:t ? (t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}) : null});
}

/* 13. statement as a white note card with a tilted tag */
function rpNote(C, Z, o){
  var t = C.it[0], txt = t ? t.d : (C.s.subtitle || ''), pad = Z.w > 800 ? 60 : 40;
  var y = Z.y + 24, h = Z.h - 32;
  slab(C, Z.x, y, Z.w - 8, h, WHITE, 18, 8);
  var lab = t && t.h ? t.h : 'Note', tw = Math.min(Z.w * 0.6, lineW(C, lab, 17, 'body', 800, Z.w) + 36);
  tag(C, Z.x + pad - 10, y - 24, tw, 46, -4, PERI, lab, INK, {size:17, ed:t && t.h ? {f:'x-part', i:t.i, p:0} : null});
  ringDot(C, Z.x + Z.w - 44, y + 34, 20);
  C.text(Z.x + pad, y + 44, Z.w - pad * 2 - 8, h - 44 - pad * 0.7, txt, {font:'head', fit:{max:32, min:15}, color:INK, lh:1.3, va:'m', ed:t ? (t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}) : null});
}

/* ============================================================
   FULL SLIDES
   ============================================================ */
function subPill(C, x, y, maxW, text, center){
  var s = 18, m = C.measureText(text, maxW - 56, {font:'body', wt:700, max:s, min:13, maxLines:2, lh:1.3});
  s = m.s;
  var one = m.n <= 1, w = one ? Math.min(maxW, lineW(C, text, s, 'body', 700, maxW) + 56) : maxW, h = one ? 54 : Math.round(m.h + 30);
  var px = center ? x + (maxW - w) / 2 : x;
  C.rect(px + 6, y + 6, w, h, {fill:INK, r:Math.min(h / 2, 27)});
  C.rect(px, y, w, h, {fill:CHAR, r:Math.min(h / 2, 27)});
  C.text(px + 28, y, w - 56, h, text, {font:'body', size:s, wt:700, color:WHITE, align:'c', va:'m', lh:1.3, ed:{f:'subtitle'}, maxLines:2});
  return y + h;
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  strip(C);
  if (o.img) { C.rect(W - 492, 98, 440, H - 190, {fill:INK, r:26}); D.imgBox(C, W - 500, 90, 440, H - 190, {r:26}); C.rect(W - 500, 90, 440, H - 190, {line:INK, lw:3, r:26}); burst(C, W - 490, 104, 48); }
  var x = o.img ? 70 : 150, w = o.img ? 640 : W - 300;
  ringDot(C, x - 10, 130, 26); cross(C, o.img ? x + w - 50 : x + w - 10, o.img ? 480 : 330, 60, 10);
  var te = C.text(x, 100, w, 320, C.s.title, {font:'head', fit:{max:o.img ? 84 : 118, min:40}, color:INK, ed:{f:'title'}, maxLines:3, va:'b', lh:0.96, align:o.img ? 'l' : 'c'});
  var y = 440;
  if (C.s.subtitle) y = subPill(C, x, y, o.img ? Math.min(560, w) : 820, C.s.subtitle, !o.img) + 26;
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) {
    var mw = lineW(C, meta, 14, 'body', 700, w) + 40;
    tag(C, o.img ? x : W / 2 - mw / 2, Math.min(y, H - 110), mw, 38, -2, PERI, meta, INK, {size:14});
  }
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  strip(C);
  if (o.img) { C.rect(78, 98, 440, H - 190, {fill:INK, r:26}); D.imgBox(C, 70, 90, 440, H - 190, {r:26}); C.rect(70, 90, 440, H - 190, {line:INK, lw:3, r:26}); burst(C, 494, 106, 48); }
  var x = o.img ? 580 : 150, w = o.img ? W - 640 : W - 300, al = o.img ? 'l' : 'c';
  if (!o.img) { burst(C, 170, 170, 46); cross(C, W - 180, 190, 60, -10); ringDot(C, W - 250, H - 140, 28); }
  C.text(x, 110, w, 220, C.s.title || 'Thank You', {font:'head', fit:{max:108, min:44}, color:INK, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:0.96});
  var y = 360;
  if (C.s.subtitle) y = subPill(C, x, y, o.img ? w : 820, C.s.subtitle, !o.img) + 36;
  var cs = D.contacts(C); if (!cs.length) return;
  var gap = 20, cw = o.img ? w : Math.min(w, 960), n = cs.length, pw = (cw - gap * (n - 1) - 6) / n, cx0 = x + (w - cw) / 2;
  if (o.img) { pw = cw - 6; }
  cs.forEach(function(c, k){
    var px = o.img ? x : cx0 + k * (pw + gap), py = o.img ? y + k * 64 : Math.max(y, 470);
    slab(C, px, py, pw, 52, WHITE, 26, 5);
    C.ell(px + 8, py + 8, 36, 36, {fill:k % 2 ? CHAR : PERI});
    C.icon(px + 16, py + 16, 20, c.icon, k % 2 ? WHITE : INK);
    C.text(px + 54, py, pw - 70, 52, c.val, {font:'body', fit:{max:16, min:11}, wt:700, color:INK, va:'m', maxLines:1, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null});
  });
}
function quote(C, v){
  var o = C.o || {};
  var a = C.it[0], by = a ? (a.h ? a.h + ': ' + a.d : a.d) : (C.s.subtitle || '');
  if (o.img) {
    var iw = 400, ix = o.img === 'right' ? W - iw - 70 : 64, sx = o.img === 'right' ? 64 : iw + 120, sw = W - iw - 190;
    C.rect(ix + 8, 98, iw, H - 190, {fill:INK, r:26}); D.imgBox(C, ix, 90, iw, H - 190, {r:26}); C.rect(ix, 90, iw, H - 190, {line:INK, lw:3, r:26});
    cross(C, o.img === 'right' ? ix + iw - 10 : ix + 16, 104, 56, 12, WHITE);
    slab(C, sx, 110, sw, H - 230, CHAR, 30, 9);
    burst(C, sx + 40, 128, 50);
    C.text(sx, 90, 80, 76, '“', {font:'head', size:64, color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(sx + 50, 180, sw - 100, 230, C.s.title || '', {font:'head', fit:{max:32, min:17}, color:WHITE, align:'l', va:'m', lh:1.28, ed:{f:'title'}, maxLines:6});
    if (by) { var bw2 = Math.min(sw - 100, lineW(C, by, 14, 'body', 800, sw - 100, true, 0.06) + 44); tag(C, sx + 50, 440, bw2, 42, -3, PERI, by, INK, {size:14, caps:true, ls:0.06, ed:a ? {f:'content-plain', i:a.i} : null}); }
    return;
  }
  slab(C, 150, 110, W - 300, H - 230, CHAR, 34, 10);
  burst(C, 190, 130, 56);
  C.text(150, 94, 80, 80, '“', {font:'head', size:70, color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
  cross(C, W - 170, H - 130, 64, 12, WHITE);
  C.text(250, 170, W - 500, 260, C.s.title || '', {font:'head', fit:{max:36, min:18}, color:WHITE, align:'c', va:'m', lh:1.28, ed:{f:'title'}, maxLines:5});
  if (by) {
    var bw = Math.min(W - 520, lineW(C, by, 15, 'body', 800, W - 520, true, 0.06) + 44);
    tag(C, W / 2 - bw / 2, 470, bw, 44, -3, PERI, by, INK, {size:15, caps:true, ls:0.06, ed:a ? {f:'content-plain', i:a.i} : null});
  }
}

D.register({
  id:'retropop', name:'Retro Grid Pop', tag:'Playful · social media', desc:'Cream grid paper, chunky indigo headlines, charcoal pills with ink offset shadows, tilted sticker tags, starburst badges and torn ticket stubs — for social, marketing and creator decks.',
  fonts:{head:'Rammetto One', body:'Poppins', headW:400, bodyW:500, headLh:0.98, headLs:-0.01, gf:'Rammetto One|Poppins:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Giant title + stickers',{}), V('v2','Title + framed photo',{img:true})],
    closing:[V('v1','Stickers + contact pills',{}), V('v2','Framed photo left',{img:true})],
    agenda:[V('v1','Sticker tiles',{body:rpAgendaTiles}), V('n2','Starburst pills',{body:rpAgendaPills}), V('v2','Pills + image right',{body:rpAgendaPills, img:'right', cols:1})],
    text:[V('v1','Charcoal slab',{body:rpStatement}), V('n2','Tagged note',{body:rpNote}), V('v2','Note + image right',{body:rpNote, img:'right'}), V('v3','Image left + slab',{body:rpStatement, img:'left'})],
    p2:[V('v1','Sticker panels',{body:rpDuo}), V('n2','Ticket stubs',{body:rpTickets}), V('n3','Charcoal pills',{body:rpPills}), V('v2','Image left + pills',{body:rpPills, img:'left'}), V('v3','Tag cards + image right',{body:rpTagCards, img:'right'})],
    p3:[V('v1','Tilted tag cards',{body:rpTagCards}), V('n2','Starburst cards',{body:rpBurst}), V('n3','Ticket stubs',{body:rpTickets}), V('n4','Charcoal pills',{body:rpPills}), V('v2','Image left + pills',{body:rpPills, img:'left'}), V('v3','X list + image right',{body:rpXList, img:'right'})],
    p4:[V('v1','Starburst cards',{body:rpBurst}), V('n2','Tilted tag cards',{body:rpTagCards}), V('n3','Ticket stubs',{body:rpTickets}), V('n4','X-sticker list',{body:rpXList}), V('v2','Pills + image right',{body:rpPills, img:'right'})],
    p56:[V('v1','Ticket stubs',{body:rpTickets}), V('n2','Starburst cards',{body:rpBurst}), V('n3','X-sticker list',{body:rpXList}), V('n4','Charcoal pills',{body:rpPills}), V('v2','X list + image right',{body:rpXList, img:'right', cols:1})],
    p78:[V('v1','X-sticker list',{body:rpXList}), V('n2','Ticket stubs',{body:rpTickets}), V('n3','Charcoal pills',{body:rpPills}), V('v2','X list + image right',{body:rpXList, img:'right', cols:2})],
    stats:[V('v1','Starburst stickers',{body:rpStatStickers}), V('n2','Charcoal columns',{body:rpStatPills}), V('n3','Colour discs',{body:'xStatDiscs'}), V('v2','Image left + pill rows',{body:rpStatPills, img:'left', rows:true})],
    timeline:[V('v1','Sticker track',{body:rpTrack}), V('n2','Year tickets',{body:rpTickets, stubYear:true}), V('n3','Year tag cards',{body:rpTagCards, tagH:true}), V('v2','Year tickets + image right',{body:rpTickets, stubYear:true, img:'right', cols:1})],
    quote:[V('v1','Charcoal quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

