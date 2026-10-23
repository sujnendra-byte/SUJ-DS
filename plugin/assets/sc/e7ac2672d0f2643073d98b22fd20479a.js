
/* Template 28 — Violet Pitch : indigo→violet gradient pages alternating with lavender pages, pill tag rows, stacked rounded photo cards, glass cards, huge light titles */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2, mix = u.mix;
var IND = '#1A1046', VIO = '#5B2BD6', VIO2 = '#8A5CFF', LAV = '#EDE8FC', LAV2 = '#DCD2F7', LILAC = '#B9A6F2';
var DARKBG = {ang:135, stops:[[0, '#110A33'], [55, '#26146E'], [100, '#4521B0']]};
var LIGHTBG = {ang:135, stops:[[0, '#F7F4FF'], [100, '#E2D9FA']]};
var VGR = {ang:135, stops:[[0, '#4A22C2'], [100, '#7B4DF5']]};
var P0 = {bg:'#F1ECFD', text:'#221A4D', title:'#1A1046', muted:'#5C5390', accent:VIO, accent2:VIO2, card:'#FFFFFF', line:'#D4C9F3', onAccent:'#FFFFFF'};
var PD = {bg:'#1E1158', text:'#ECE8FF', title:'#FFFFFF', muted:'#C4B8F2', accent:'#A98BFF', accent2:VIO2, card:'#2B1C74', line:'#4C3A9E', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[VIO, VIO2, '#3D1FA0', LILAC, '#6E44E8', '#9E84F0'],
  header:{kind:'left', top:92, tMax:54, tMin:30, sMax:17, sMin:13, kicker:null, gap:14},
  card:{kind:'solid', r:20, fill:'#FFFFFF', op:0.75, line:LAV2, lw:1.2},
  icon:{kind:'circle', bg:VIO, fg:'#FFFFFF'}, num:{kind:'pill', bg:VIO, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:VIO, op:0.14, label:'Takeaway'},
  footer:{brand:false, page:true}, imgFramed:true, imgR:24, bodyGap:30,
  imgPh:{grad:{ang:135, stops:[[0, '#9C84F2'], [100, '#4A2BB0']]}}
};
function isDark(C, kind){ return kind === 'cover' || kind === 'closing' || (C.i || 0) % 2 === 0; }

/* ---------- motif helpers ---------- */
function pill(C, x, y, w, h, text, o){
  o = o || {}; var dk = C.dk;
  C.rect(x, y, w, h, {fill:o.fill || null, grad:o.grad || null, op:o.op, line:o.fill || o.grad ? null : (o.line || (dk ? '#FFFFFF' : VIO)), lop:dk ? 0.55 : 0.7, lw:1.2, r:h / 2});
  C.text(x + h * 0.4, y, w - h * 0.8, h, text, {font:o.font || 'body', fit:{max:o.size || Math.round(h * 0.44), min:o.min || 11}, wt:o.wt || 500, color:o.color || (o.fill || o.grad ? '#FFFFFF' : (dk ? '#FFFFFF' : VIO)), align:o.align || 'c', va:'m', lh:1.1, ed:o.ed || null, maxLines:1});
}
function pw(txt, size){ return String(txt || '').length * size * 0.56 + size * 2.4; }
/* glass (dark page) / lavender glass (light page) card; hl = violet gradient */
function gcard(C, x, y, w, h, hl, r){
  r = r == null ? 20 : r;
  if (hl) { C.rect(x, y, w, h, {grad:VGR, r:r, shadow:0.18}); return {tc:'#FFFFFF', mc:'#E4DCFF', pl:'#FFFFFF'}; }
  if (C.dk) { C.rect(x, y, w, h, {fill:'#FFFFFF', op:0.08, line:'#FFFFFF', lop:0.32, lw:1.2, r:r}); return {tc:'#FFFFFF', mc:'#CFC5F5', pl:'#FFFFFF'}; }
  C.rect(x, y, w, h, {fill:'#FFFFFF', op:0.78, line:LAV2, lw:1.2, r:r, shadow:0.06}); return {tc:IND, mc:'#5C5390', pl:VIO};
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  D.sparkle(C, x + 9, y + 11, 10, C.dk ? '#FFFFFF' : VIO);
  C.text(x + 26, y, 300, 22, f.company, {font:'body', size:14, wt:600, color:C.dk ? '#FFFFFF' : IND, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
}
function pillRow(C, y, right){
  var f = C.cover, ps = [], x = right;
  if (f.date) ps.push(f.date);
  if (f.presenter) ps.push('Presented by: ' + f.presenter);
  ps.forEach(function(t, k){
    var w = Math.min(300, pw(t, 12)); x -= w;
    pill(C, x, y, w, 28, t, k === ps.length - 1 && !C.dk ? {fill:IND, size:12} : {size:12, fill:C.dk && k === ps.length - 1 ? '#FFFFFF' : null, color:C.dk && k === ps.length - 1 ? IND : null});
    x -= 10;
  });
}

/* ---------- page background ---------- */
function bg(C, kind){
  var dk = C.dk = isDark(C, kind);
  var p = {}, k; for (k in C.T.pal) p[k] = C.T.pal[k];
  if (dk) for (k in PD) p[k] = PD[k];
  C.P = p;
  C.S = D.merge(C.T.s, {header:{tc:p.title, sc:p.muted}, footer:{color:dk ? '#A99BE0' : '#8A80B8'}});
  C.bg({grad:dk ? DARKBG : LIGHTBG});
  if (dk) { D.glow(C, W * 0.85, H * 0.1, 420, 300, '#7B4DF5', 0.35); D.glow(C, W * 0.1, H * 1.05, 380, 220, '#3B1BA8', 0.5); }
  else { D.glow(C, W * 0.92, H * 0.95, 420, 280, '#C9B8FA', 0.6); }
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'left' && im !== 'top') brand(C, 70, 30);
  if (im !== 'right' && im !== 'top') pillRow(C, 27, W - 70);
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* two cards overlapping; the front one violet */
function overlapPair(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return cascade(C, Z, o);
  var cw = Z.w * 0.56, ov = cw * 2 - Z.w, dy = 44, ch = Z.h - dy, pad = 36;
  var twA = cw - ov - pad * 2, twB = cw - pad * 2, top = pad + 36 + 22;
  var F = K.fitHD(C, it, twA, 80, ch - top - pad, {hMax:26, dMax:17, hMin:14, dMin:11.5});
  var cards = [[Z.x, Z.y + dy, twA, 0], [Z.x + Z.w - cw, Z.y, twB, 1]];
  cards.forEach(function(c){
    var t = it[c[3]], x = c[0], y = c[1], hl = c[3] === 1, cc = gcard(C, x, y, cw, ch, hl, 26);
    var tx = hl ? x + ov + pad - 20 : x + pad, tw = hl ? cw - ov - pad * 2 + 20 : c[2];
    pill(C, tx, y + pad, 96, 34, pad2(c[3] + 1) + ' / ' + pad2(2), hl ? {fill:'#FFFFFF', color:VIO, size:13, wt:600} : {size:13});
    var bh = blockH(C, t, tw, F), by = y + top + Math.max(0, (ch - top - pad - bh) * 0.35);
    K.hd(C, tx, by, tw, y + ch - by - pad + 10, t, F, {tc:cc.tc, mc:cc.mc});
  });
}

/* cascading cards, each lower and overlapping the previous */
function cascade(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var ov = n > 3 ? 26 : 40, cw = (Z.w + ov * (n - 1)) / n, step = Math.min(34, Z.h * 0.08), ch = Z.h - step * (n - 1), pad = n > 3 ? 22 : 28;
  var tw = cw - ov - pad * 2, top = pad + 32 + 20;
  var F = K.fitHD(C, it, tw, 70, ch - top - pad, {hMax:21, dMax:15.5, hMin:13, dMin:11});
  var hlk = o.hl != null ? o.hl : n - 1;
  it.forEach(function(t, k){
    var x = Z.x + k * (cw - ov), y = Z.y + k * step, cc = gcard(C, x, y, cw, ch, k === hlk, 24);
    var tx = x + (k ? ov : 0) + pad, w = k ? tw : cw - ov - pad * 2;
    pill(C, tx, y + pad, 64, 30, pad2(k + 1), k === hlk ? {fill:'#FFFFFF', color:VIO, size:13, wt:600} : {size:13});
    K.icon(C, x + cw - pad - 28, y + pad + 1, 28, t, cc.pl);
    K.hd(C, tx, y + top, w, ch - top - pad, t, F, {tc:cc.tc, mc:cc.mc});
  });
}

/* glass grid, heading in an outlined pill, faint giant number */
function pillGlass(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)), rows = Math.ceil(n / cols), gx = 22, gy = 20;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, pad = rows > 1 ? 22 : 28, pH = rows > 1 ? 34 : 38;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - pad * 2 - pH, pH - 6, {font:'head', max:18, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - pH - 16, {font:'body', max:n <= 3 ? 19 : (n <= 4 ? 17 : 15), min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2 - 20, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = Math.min(ch, Math.max(pad * 2 + pH + 16 + dmx + 70, ch * 0.55));
  if (ch2 < ch) { var tot = ch2 * rows + gy * (rows - 1); Z = {x:Z.x, y:Z.y + (Z.h - tot) * 0.4, w:Z.w, h:tot}; ch = ch2; }
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy), cc = gcard(C, x, y, cw, ch, k === 0 && o.hlFirst, 20);
    C.text(x + cw - 150, y + ch - 96, 136, 90, pad2(k + 1), {font:'head', size:84, wt:300, color:cc.pl, op:0.14, align:'r', va:'b', lh:1, caps:false, ls:0});
    var hw = t.h ? Math.min(cw - pad * 2, C.measureText(t.h, 2000, {font:'head', max:hs, min:hs}).h ? pw(t.h, hs) + 6 : 0) : 0;
    if (t.h) { C.rect(x + pad, y + pad, hw, pH, {line:cc.pl, lop:0.7, lw:1.2, r:pH / 2}); C.text(x + pad + 14, y + pad, hw - 28, pH, t.h, {font:'head', size:hs, color:cc.tc, align:'c', va:'m', lh:1, ed:{f:'x-part', i:t.i, p:0}, maxLines:1}); }
    C.text(x + pad, y + pad + pH + 16, cw - pad * 2 - 20, ch - pad * 2 - pH - 16, t.d, {font:'body', size:ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* rounded pill rows: number disc, heading, description in one capsule */
function capsules(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 26, cw = (Z.w - gx * (cols - 1)) / cols;
  var gy = per > 4 ? 10 : 14, rh = Math.min((Z.h - gy * (per - 1)) / per, 92), y0 = Z.y + Math.max(0, (Z.h - rh * per - gy * (per - 1)) / 2);
  var hasH = it.some(function(t){ return t.h; }), d = rh - 16, tx = d + 26;
  var stack = !hasH || cw < 560, hw = stack ? cw - tx - 24 : Math.min(230, cw * 0.32), dw = stack ? cw - tx - 24 : cw - tx - hw - 40;
  var F = stack && hasH ? K.fitHD(C, it, dw, rh * 0.45, rh - 10, {hMax:17, dMax:14, hMin:13, dMin:11, hLines:1, noBoost:true}) : null;
  var hs = !stack ? C.groupFit(it.map(function(t){ return t.h; }), hw, rh - 12, {font:'head', max:19, min:13, maxLines:2}) : 0;
  var ds = !stack ? C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 10, {font:'body', max:15.5, min:11, lh:1.35}) : C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 10, {font:'head', max:20, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    var cc = gcard(C, x, y, cw, rh, false, rh / 2);
    C.ell(x + 8, y + 8, d, d, {grad:VGR});
    C.text(x + 8, y + 8, d, d, pad2(k + 1), {font:'head', size:Math.round(d * 0.34), wt:500, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (!hasH) C.text(x + tx, y, dw, rh, t.d, {font:'head', size:ds, color:cc.tc, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    else if (stack) { var bh = blockH(C, t, dw, F); K.hd(C, x + tx, y + Math.max(2, (rh - bh) / 2), dw, rh - 4, t, F, {tc:cc.tc, mc:cc.mc}); }
    else {
      C.text(x + tx, y, hw, rh, t.h, {font:'head', size:hs, color:cc.tc, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
      C.rect(x + tx + hw + 14, y + rh * 0.25, 1.2, rh * 0.5, {fill:cc.pl, op:0.4});
      C.text(x + tx + hw + 28, y, dw, rh, t.d, {font:'body', size:ds, color:cc.mc, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
    }
  });
}

/* stacked rounded photo cards on the left, item cards overlapping the photo */
function photoStack(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (!n) return;
  var pwid = Math.min(Z.w * 0.44, 480), px = Z.x, py = Z.y + 24, ph = Z.h - 24;
  C.rect(px + 44, py - 24, pwid - 44, ph - 12, {fill:C.dk ? '#FFFFFF' : LILAC, op:C.dk ? 0.12 : 0.45, r:26});
  C.rect(px + 22, py - 12, pwid - 22, ph - 6, {fill:C.dk ? '#FFFFFF' : LILAC, op:C.dk ? 0.22 : 0.7, r:26});
  C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:28});
  D.imgBox(C, px, py, pwid, ph, {r:24});
  var ov = 70, lx = px + pwid - ov, lw = Z.x + Z.w - lx, gy = 14, ch = (Z.h - gy * (n - 1)) / n, pad = 22;
  var F = K.fitHD(C, it, lw - pad * 2 - 20, ch * 0.4, ch - pad * 2 + 8, {hMax:20, dMax:15, hMin:13, dMin:11, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (ch + gy), x = lx + (k % 2 ? 26 : 0), w = lw - 26;
    if (k) C.rect(x, y, w, ch, {fill:C.dk ? '#24166A' : '#FFFFFF', r:20});
    var cc = gcard(C, x, y, w, ch, k === 0, 20);
    var bh = blockH(C, t, w - pad * 2 - 20, F);
    K.hd(C, x + pad, y + Math.max(8, (ch - bh) / 2), w - pad * 2 - 20, ch - 12, t, F, {tc:cc.tc, mc:cc.mc});
    C.text(x + w - 44, y + 12, 32, 20, pad2(k + 1), {font:'body', size:12, wt:600, color:cc.pl, align:'r', lh:1, op:0.8});
  });
}

/* stats: huge light numerals, thin dividers, label pills */
function bigNumbers(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n > 2 && (Z.w / n) < 230 ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, rhh = Z.h / rows, iw = cw - 36;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, iw, rhh * 0.45, {font:'head', wt:300, max:rows > 1 ? 60 : (n >= 4 ? 76 : 96), min:28, maxLines:1});
  var ls = C.groupFit(labs, iw - 28, 20, {font:'body', max:14, min:11, maxLines:1});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, rhh * 0.25, {font:'body', max:15, min:11, lh:1.38});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * rhh, blk = vs * 1.05 + 18 + 32 + (t.x ? 12 + ds * 2.8 : 0), y0 = y + Math.max(0, (rhh - blk) / 2);
    if (c > 0) C.rect(x, y + rhh * 0.12, 1.2, rhh * 0.76, {fill:C.dk ? '#FFFFFF' : VIO, op:0.3});
    C.text(x + 18, y0, iw, vs * 1.05, vals[k], {font:'head', size:vs, wt:300, color:C.P.title, lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.02, maxLines:1});
    var lw = Math.min(iw, pw(labs[k], ls) + 8);
    pill(C, x + 18, y0 + vs * 1.05 + 18, lw, 32, labs[k], {size:ls, grad:k === n - 1 ? VGR : null, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 18, y0 + vs * 1.05 + 62, iw, ds * 2.9, t.x, {font:'body', size:ds, color:C.P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* stats: rising violet columns with a value pill on top */
function riseCols(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var gap = 30, cw = (Z.w - gap * (n - 1)) / n, bw = Math.min(cw * 0.62, 200), labH = 70, base = Z.y + Z.h - labH;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, bw - 30, 60, {font:'head', max:40, min:12, maxLines:1});
  var ls = C.groupFit(labs, cw - 10, labH - 18, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), bw - 28, 90, {font:'body', max:13.5, min:11, lh:1.35});
  C.rect(Z.x, base, Z.w, 1.5, {fill:C.dk ? '#FFFFFF' : VIO, op:0.35});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cx = x + cw / 2, h = (base - Z.y - 10) * (0.45 + 0.55 * (k + 1) / n), y = base - h;
    C.rect(cx - bw / 2, y, bw, h, {grad:{ang:180, stops:[[0, k === n - 1 ? '#8A5CFF' : (C.dk ? '#6B45E0' : '#7A5AE8')], [100, k === n - 1 ? '#4A22C2' : (C.dk ? '#2D1880' : '#C9B8FA')]]}, r:22});
    C.rect(cx - bw / 2 + 10, y + 12, bw - 20, vs * 1.3 + 12, {fill:'#FFFFFF', r:(vs * 1.3 + 12) / 2});
    C.text(cx - bw / 2 + 12, y + 12, bw - 24, vs * 1.3 + 12, vals[k], {font:'head', size:vs, wt:500, color:VIO, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:0, maxLines:1});
    if (t.x && h > vs * 1.3 + 90) C.text(cx - bw / 2 + 14, y + vs * 1.3 + 36, bw - 28, h - vs * 1.3 - 46, t.x, {font:'body', size:ds, color:'#FFFFFF', align:'c', lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
    C.text(x, base + 12, cw, labH - 12, labs[k], {font:'head', size:ls, wt:500, color:C.P.title, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
  });
}

/* timeline: year pills on a line, glass cards hanging above / below */
function pillLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, ly = Z.y + Z.h / 2, pH = 36, cardH = Z.h / 2 - pH / 2 - 26;
  C.rect(Z.x, ly - 1, Z.w, 2, {fill:C.dk ? '#FFFFFF' : VIO, op:0.4});
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - 60, pH - 8, {font:'head', max:18, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw * 1.5 - 40, cardH - 32, {font:'body', max:18, min:11, lh:1.38});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), up = k % 2 === 0, w = Math.min(cw * 1.6 - 20, 300), x = Math.max(Z.x, Math.min(Z.x + Z.w - w, cx - w / 2));
    var pwid = Math.min(cw - 16, 130);
    pill(C, cx - pwid / 2, ly - pH / 2, pwid, pH, t.h || pad2(k + 1), {font:'head', size:hs, wt:500, grad:k === n - 1 ? VGR : null, fill:k === n - 1 ? null : (C.dk ? '#1E1158' : '#FFFFFF'), color:k === n - 1 ? '#FFFFFF' : (C.dk ? '#FFFFFF' : VIO), ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    if (k !== n - 1) C.rect(cx - pwid / 2, ly - pH / 2, pwid, pH, {line:C.dk ? '#FFFFFF' : VIO, lop:0.6, lw:1.2, r:pH / 2});
    var cy = up ? Z.y : ly + pH / 2 + 26;
    C.line(cx, up ? cy + cardH : ly + pH / 2, cx, up ? ly - pH / 2 : cy, {color:C.dk ? '#FFFFFF' : VIO, lw:1.2, op:0.5, dash:true});
    var dh = C.measureText(t.d, w - 36, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(cardH, dh + 32), yy = up ? cy + cardH - hh : cy;
    var cc = gcard(C, x, yy, w, hh, false, 18);
    C.text(x + 18, yy + 16, w - 36, hh - 28, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}, va:'m'});
  });
}

/* timeline: staircase of stacked cards */
function stairStack(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var stepX = Math.min(Z.w * 0.1, 110, Z.w * 0.4 / Math.max(1, n - 1)), w = Z.w - stepX * (n - 1), band = Z.h / (n - 1 + 1.5), pad = 20;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), 120, 26, {font:'head', max:16, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), w - 190 - pad * 2, band - 12, {font:'body', max:16, min:11, lh:1.3});
  it.forEach(function(t, k){
    var x = Z.x + k * stepX, y = Z.y + k * band, h = Z.y + Z.h - y, last = k === n - 1;
    var cc = gcard(C, x, y, w, h, last, 22);
    if (!last && !C.dk) C.rect(x, y, w, h, {fill:k % 2 ? LAV : '#FFFFFF', r:22, line:LAV2, lw:1.2});
    var bh = last ? Math.min(h, band * 1.5) : band, my = y + Math.min(bh, band) / 2;
    pill(C, x + pad, my - 16, 140, 32, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, fill:last ? '#FFFFFF' : null, color:last ? VIO : null, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x + pad + 160, y + 4, w - 160 - pad * 2 - 40, Math.min(bh, band) - 8, t.d, {font:'body', size:ds, color:cc.mc, va:'m', lh:1.3, ed:{f:'x-part', i:t.i, p:1}});
    C.text(x + w - 60, y + 4, 44, Math.min(bh, band) - 8, pad2(k + 1), {font:'head', size:14, wt:500, color:cc.pl, op:0.6, align:'r', va:'m', lh:1, caps:false, ls:0});
  });
}

/* statement: pill label and huge light text, stacked card corner */
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var x = Z.x, w = Z.w * 0.8, y = Z.y;
  if (!o.img) {
    C.rect(Z.x + Z.w - 190, Z.y + Z.h - 150, 170, 140, {fill:C.dk ? '#FFFFFF' : LILAC, op:C.dk ? 0.1 : 0.4, r:22});
    C.rect(Z.x + Z.w - 160, Z.y + Z.h - 120, 170, 140, {grad:VGR, r:22});
    D.sparkle(C, Z.x + Z.w - 75, Z.y + Z.h - 50, 22, '#FFFFFF');
  }
  if (it.h) { pill(C, x, y, Math.min(w, pw(it.h, 16) + 20), 40, it.h, {size:16, grad:VGR, ed:{f:'x-part', i:it.i, p:0}}); y += 64; }
  C.text(x, y, o.img ? Z.w : w, Z.y + Z.h - y - (o.img ? 0 : 20), it.d, {font:'head', fit:{max:44, min:18}, wt:300, color:C.P.title, lh:1.25, va:'m', ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function coverPills(C, x, y){
  var f = C.cover, xx = x;
  [f.presenter ? 'Presented by: ' + f.presenter : '', f.date].filter(Boolean).forEach(function(t, k){
    var w = Math.min(340, pw(t, 14)); pill(C, xx, y, w, 36, t, k ? {size:14} : {size:14, fill:'#FFFFFF', color:IND}); xx += w + 12;
  });
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, img = o.img === 'stack';
  brand(C, 70, 40);
  if (img) {
    var x = 690, y = 110, w = 520, h = 500;
    C.rect(x + 60, y - 40, w - 60, h - 20, {fill:'#FFFFFF', op:0.12, r:30});
    C.rect(x + 30, y - 20, w - 30, h - 10, {fill:'#FFFFFF', op:0.22, r:30});
    C.rect(x - 6, y - 6, w + 12, h + 12, {fill:'#FFFFFF', r:32});
    D.imgBox(C, x, y, w, h, {r:28});
    pill(C, x - 40, y + h - 70, 220, 44, f.company || 'Pitch Deck', {size:15, grad:VGR});
    C.text(70, 150, 580, 300, C.s.title, {font:'head', fit:{max:76, min:34}, wt:300, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.02});
    if (C.s.subtitle) C.text(70, 474, 560, 80, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#CFC5F5', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
    coverPills(C, 70, 590);
    return;
  }
  var sx = 820, sy = 360;
  [0, 1, 2].forEach(function(k){ C.rect(sx + k * 40, sy - k * 44, 320, 220, {fill:k === 2 ? null : '#FFFFFF', grad:k === 2 ? VGR : null, op:k === 2 ? 1 : 0.08 + k * 0.08, line:'#FFFFFF', lop:0.35, lw:1.2, r:30}); });
  D.sparkle(C, sx + 80 + 260, sy - 88 + 160, 34, '#FFFFFF');
  C.text(70, 110, 740, 330, C.s.title, {font:'head', fit:{max:112, min:44}, wt:300, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:0.98});
  if (C.s.subtitle) C.text(70, 470, 640, 80, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:'#CFC5F5', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  coverPills(C, 70, 590);
}
function contactBar(C, x, y, w){
  var cs = D.contacts(C); if (!cs.length) return;
  C.rect(x, y, w, 64, {fill:'#FFFFFF', op:C.dk ? 0.1 : 0.8, line:C.dk ? '#FFFFFF' : LAV2, lop:0.4, lw:1.2, r:32});
  var cw = w / cs.length;
  cs.forEach(function(c, k){
    var xx = x + k * cw;
    if (k) C.rect(xx, y + 16, 1.2, 32, {fill:C.dk ? '#FFFFFF' : VIO, op:0.35});
    C.ell(xx + 22, y + 14, 36, 36, {grad:VGR}); C.icon(xx + 31, y + 23, 18, c.icon, '#FFFFFF');
    C.text(xx + 70, y, cw - 86, 64, c.val, {font:'body', fit:{max:16, min:11}, wt:500, color:C.dk ? '#FFFFFF' : IND, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function closing(C, v){
  var o = C.o || {};
  brand(C, 70, 40);
  if (o.img === 'stack') {
    var x = 70, y = 270, w = W - 140, h = 240;
    C.rect(x + 40, y - 30, w - 80, h, {fill:'#FFFFFF', op:0.14, r:28});
    C.rect(x - 5, y - 5, w + 10, h + 10, {fill:'#FFFFFF', r:30});
    D.imgBox(C, x, y, w, h, {r:26});
    C.text(70, 66, W - 140, 104, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:36}, wt:300, color:'#FFFFFF', align:'c', ed:{f:'title'}, maxLines:1, va:'b'});
    if (C.s.subtitle) C.text(200, 182, W - 400, 36, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#CFC5F5', align:'c', ed:{f:'subtitle'}, maxLines:1});
    contactBar(C, 170, 560, W - 340);
    return;
  }
  C.text(70, 150, W - 140, 220, C.s.title || 'Thank You', {font:'head', fit:{max:120, min:44}, wt:300, color:'#FFFFFF', align:'c', ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(220, 392, W - 440, 70, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:'#CFC5F5', align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  contactBar(C, 170, 520, W - 340);
}
function agendaA(C, o){
  var P = C.P;
  var te = C.text(70, 120, 440, 260, C.s.title || 'Agenda', {font:'head', fit:{max:92, min:40}, wt:300, color:P.title, ed:{f:'title'}, maxLines:3, va:'t', lh:1});
  if (C.s.subtitle) C.text(70, 120 + te._h + 24, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.rect(70, 470, 200, 150, {fill:LILAC, op:0.45, r:24}); C.rect(110, 500, 200, 150, {grad:VGR, r:24}); D.sparkle(C, 250, 560, 24, '#FFFFFF');
  var x = 540, y = 100, w = W - 540 - 70, h = H - 170;
  gcard(C, x, y, w, h, false, 28);
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols), cw = (w - 60 - 30 * (cols - 1)) / cols, rh = Math.min(76, (h - 60) / per);
  var fs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 70, rh - 10, {font:'head', max:21, min:13, maxLines:2});
  var y0 = y + 30 + (h - 60 - rh * per) / 2;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, xx = x + 30 + c * (cw + 30), yy = y0 + r * rh;
    pill(C, xx, yy + rh / 2 - 15, 52, 30, pad2(k + 1), {size:13, grad:k === 0 ? VGR : null});
    C.text(xx + 66, yy, cw - 70, rh, t.h || t.d, {font:'head', size:fs, wt:500, color:P.title, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    if (r < per - 1) C.rect(xx + 66, yy + rh, cw - 66, 1, {fill:P.line});
  });
}
function agendaPhoto(C, o){
  var P = C.P;
  C.text(70, 86, 560, 80, C.s.title || 'Agenda', {font:'head', fit:{max:54, min:30}, wt:300, color:P.title, ed:{f:'title'}, maxLines:1});
  var px = 70, py = 200, pwid = 400, ph = 440;
  C.rect(px + 40, py - 26, pwid - 40, ph - 10, {fill:LILAC, op:0.45, r:26});
  C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:28});
  D.imgBox(C, px, py, pwid, ph, {r:24});
  capsules(C, {x:520, y:180, w:W - 520 - 70, h:H - 250}, {cols:1});
}
function quote(C, v){
  var P = C.P, o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:C.dk ? '#A98BFF' : VIO, tc:P.title}); return; }
  var photo = o.img === 'stack', x = 70, w = photo ? 640 : W - 140;
  if (photo) {
    var px = 800, py = 130, pwid = 410, ph = 470;
    C.rect(px + 40, py - 30, pwid - 40, ph - 10, {fill:'#FFFFFF', op:C.dk ? 0.14 : 0.5, r:28});
    C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:30});
    D.imgBox(C, px, py, pwid, ph, {r:26});
  }
  C.text(x - 6, 70, 200, 170, '“', {font:'head', size:200, wt:300, color:C.dk ? '#A98BFF' : VIO, lh:1, caps:false, ls:0});
  var qe = C.text(x, 220, w, 280, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:300, color:P.title, lh:1.25, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ': ' + by.d : by.d; pill(C, x, 220 + qe._h + 36, Math.min(w, pw(bt, 15)), 42, bt, {size:15, grad:VGR, ed:{f:'content-plain', i:by.i}}); }
}
function statementPhoto(C, o){
  var P = C.P;
  var y = D.header(C, {x:70, w:600, top:92}, {});
  var px = 740, py = 110, pwid = 470, ph = 500;
  C.rect(px + 44, py - 30, pwid - 44, ph - 10, {fill:'#FFFFFF', op:C.dk ? 0.14 : 0.5, r:28});
  C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:30});
  D.imgBox(C, px, py, pwid, ph, {r:26});
  statement(C, {x:70, y:y + 40, w:600, h:H - 110 - y - 40}, {img:true});
}

D.register({
  id:'violetpitch', name:'Violet Pitch', tag:'Startup · pitch deck',
  desc:'Indigo-to-violet gradient pages alternating with soft lavender pages, pill tag header rows, stacked rounded photo cards, glass cards and huge light titles for startup pitches.',
  fonts:{head:'Inter Tight', body:'Inter', headW:400, bodyW:400, headLh:1.08, gf:'Inter Tight:wght@300;400;500;600|Inter:wght@400;500;600'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Huge title + card stack', {}), V('v2', 'Stacked photo card', {img:'stack'})],
    closing:[V('v1', 'Thank you + contact pill', {}), V('v2', 'Photo card + contact pill', {img:'stack'})],
    agenda:[V('v1', 'Title + glass list', {fn:agendaA}), V('n2', 'Capsule rows', {body:capsules, cols:2}), V('v2', 'Photo stack + capsules', {fn:agendaPhoto})],
    text:[V('v1', 'Huge light statement', {body:statement}), V('v2', 'Statement + photo stack', {fn:statementPhoto}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Overlapping cards', {body:overlapPair}), V('n2', 'Cascade cards', {body:cascade}), V('n3', 'Pill glass cards', {body:pillGlass}), V('v2', 'Photo stack + cards', {body:photoStack}), V('v3', 'Capsules + image right', {body:capsules, img:'right'})],
    p3:[V('v1', 'Cascade cards', {body:cascade}), V('n2', 'Pill glass cards', {body:pillGlass}), V('n3', 'Capsule rows', {body:capsules}), V('v2', 'Photo stack + cards', {body:photoStack}), V('v3', 'Image top + pill cards', {body:pillGlass, img:'top'})],
    p4:[V('v1', 'Pill glass grid', {body:pillGlass}), V('n2', 'Cascade cards', {body:cascade}), V('n3', 'Capsule rows', {body:capsules}), V('v2', 'Photo stack + cards', {body:photoStack}), V('v3', 'Image left + capsules', {body:capsules, img:'left'})],
    p56:[V('v1', 'Capsule rows', {body:capsules}), V('n2', 'Pill glass grid', {body:pillGlass}), V('v2', 'Capsules + image right', {body:capsules, img:'right', cols:1})],
    p78:[V('v1', 'Two-column capsules', {body:capsules, cols:2}), V('n2', 'Staircase cards', {body:stairStack}), V('v2', 'Capsules + image right', {body:capsules, img:'right', cols:2})],
    stats:[V('v1', 'Huge light numbers', {body:bigNumbers}), V('n2', 'Rising columns', {body:riseCols}), V('v2', 'Numbers + image right', {body:bigNumbers, img:'right'}), V('v3', 'Image left + columns', {body:riseCols, img:'left'})],
    timeline:[V('v1', 'Pill timeline', {body:pillLine}), V('n2', 'Staircase cards', {body:stairStack}), V('v2', 'Staircase + image right', {body:stairStack, img:'right'})],
    quote:[V('v1', 'Huge light quote', {}), V('v2', 'Quote + photo stack', {img:'stack'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

