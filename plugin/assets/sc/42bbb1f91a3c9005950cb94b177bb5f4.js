
/* Template 16 — Navy & Coral Flow : soft lavender-grey, navy + coral + sand, roads and flow diagrams */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#1E2F6B', CORAL = '#F07B3F', SLATE = '#7D8BB5', SAND = '#E6DCCB';
var P0 = {bg:'#E9ECF4', text:'#1C2447', title:'#1E2F6B', muted:'#4F5878', accent:'#1E2F6B', accent2:'#F07B3F', card:'#FFFFFF', line:'#CDD3E3', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[NAVY, CORAL, SLATE, '#2F4A9A', '#C9B79A', '#F2A477'],
  header:{kind:'center', tMax:40, tMin:24, sMax:16, top:46, gap:8, bar:true, barW:46, barH:4, barC:CORAL, barGap:14},
  card:{kind:'solid', r:16, fill:'#FFFFFF', shadow:0.08, hl:{kind:'solid', fill:NAVY, dark:true}},
  icon:{kind:'circle', bg:NAVY, fg:'#FFFFFF'},
  num:{kind:'circle', bg:CORAL, fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#FFFFFF', r:14, lc:CORAL, label:'Takeaway'},
  footer:{brand:true, page:true, color:'#7D86A6'}, xRows:'#FFFFFF', hubFill:'#DDE2EE', road:'#262B3A', flowBar:'#D6DBE8',
  imgFramed:true, imgR:18
};
function road(C, y, amp, x0, x1){
  var pts = [], N = 48;
  for (var i = 0; i <= N; i++) { var t = i / N; pts.push([x0 + t * (x1 - x0), y + amp * Math.sin(t * Math.PI * 2.6 + 0.6)]); }
  /* flowing navy ribbon with a coral thread and a soft echo curve (no road markings) */
  K.curve(C, pts.map(function(p){ return [p[0], p[1] + 34]; }), {stroke:SLATE, lw:3, lop:0.45});
  K.curve(C, pts, {stroke:NAVY, lw:22});
  K.curve(C, pts.map(function(p){ return [p[0], p[1] - 2]; }), {stroke:CORAL, lw:3});
  return pts;
}
function pin(C, x, y, c, ic){
  C.ell(x - 30, y - 30, 60, 60, {fill:'#FFFFFF', shadow:0.15}); C.ell(x - 24, y - 24, 48, 48, {fill:c});
  C.icon(x - 12, y - 12, 24, ic, '#FFFFFF');
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#EEF1F8'], [100, '#E3E7F1']]}});
  if (kind !== 'cover' && kind !== 'closing') { C.ell(W - 90, -90, 180, 180, {fill:SAND, op:0.7}); C.ell(-60, H - 60, 120, 120, {fill:SLATE, op:0.25}); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) {
    D.imgBox(C, W - 540, 0, 540, H, {r:0});
    C.rect(W - 548, 0, 8, H, {fill:CORAL});
  } else {
    var pts = road(C, H - 130, 60, -20, W + 20);
    pin(C, pts[12][0], pts[12][1], CORAL, 'mdi:flag-outline'); pin(C, pts[24][0], pts[24][1], NAVY, 'mdi:rocket-launch-outline'); pin(C, pts[36][0], pts[36][1], SLATE, 'mdi:trophy-outline');
  }
  var x = 64, w = o.img ? 620 : 900;
  if (f.company) C.text(x, 70, w, 22, f.company, {font:'body', size:14, wt:800, color:CORAL, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 110, w, o.img ? 300 : 230, C.s.title, {font:'head', fit:{max:70, min:34}, color:NAVY, ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  var sy = o.img ? 430 : 360;
  C.rect(x, sy, 60, 5, {fill:CORAL, r:2});
  if (C.s.subtitle) C.text(x, sy + 22, w, 64, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta && o.img) C.text(x, 600, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 0, 0, 480, H, {r:0});
  else { var pts = road(C, 110, 40, -20, W + 20); pin(C, pts[40][0], pts[40][1], CORAL, 'mdi:flag-checkered'); }
  var x = o.img ? 540 : 120, w = o.img ? W - 600 : W - 240, al = o.img ? 'l' : 'c';
  C.text(x, 220, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:90, min:40}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:1});
  if (C.s.subtitle) C.text(x, 404, w, 60, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, x, 510, w, {cards:true});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:CORAL}); return; }
  C.rect(160, 110, W - 320, H - 220, {fill:'#FFFFFF', r:24, shadow:0.08});
  C.rect(160, 110, 12, H - 220, {fill:CORAL, r:6});
  D.B.quote(C, {x:220, y:150, w:W - 440, h:H - 300}, {qc:CORAL});
}
/* ============================================================
   SIGNATURE BODIES — curved split panels, flowing connectors, versus
   ============================================================ */
var INKN = '#1C2447', MUTN = '#4F5878', LITE = '#C9D1EE';
function fitN(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function dH(C, t, w, ds){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dH(C, t, w, F.ds) : F.hs * 1.2 * Math.min(2, C.measureText(t.d, w, {font:'head', max:F.hs, min:F.hs}).n); }
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || INKN, align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || NAVY, mc:o.mc || MUTN, hLines:o.hLines});
}
function nc(k){ return k % 2 ? CORAL : NAVY; }
function onN(c){ return c === CORAL ? INKN : '#FFFFFF'; }
function dot(C, x, y, d, k, c, o){ o = o || {}; C.ell(x - d / 2, y - d / 2, d, d, {fill:o.fill || c, line:o.line, lw:o.lw}); C.text(x - d / 2, y - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.38), wt:800, color:o.fg || onN(c), align:'c', va:'m', lh:1, caps:false, ls:0}); }
function arrowHead(C, fx, fy, tx, ty, s, c){
  var a = Math.atan2(ty - fy, tx - fx), b = 0.5;
  K.poly(C, [[tx, ty], [tx - s * Math.cos(a - b), ty - s * Math.sin(a - b)], [tx - s * Math.cos(a + b), ty - s * Math.sin(a + b)]], {fill:c});
}
/* panel with a flowing S-curved right edge */
function curvedPanel(C, x, y, w, h, b, fill){
  var r = 18, mx = x + w;
  C.path([['M', x + r, y], ['L', mx + b, y], ['C', mx + b, y + h * 0.45, mx - b, y + h * 0.55, mx - b, y + h], ['L', x + r, y + h], ['Q', x, y + h, x, y + h - r], ['L', x, y + r], ['Q', x, y, x + r, y], ['Z']], {fill:fill});
  C.path([['M', mx + b, y], ['C', mx + b, y + h * 0.45, mx - b, y + h * 0.55, mx - b, y + h]], {stroke:CORAL, lw:6});
}

/* 1 — curved split: one card, navy half with an S-curved edge, coral seam, VS on the seam (p2) */
function splitPanel(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var b = Math.min(46, Z.w * 0.04), pw = Z.w / 2, pad = Math.min(48, Z.w * 0.045);
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF', r:18, shadow:0.08});
  curvedPanel(C, Z.x, Z.y, pw, Z.h, b, NAVY);
  var tw = pw - b - pad * 2, F = fitN(C, it, tw, 80, Z.h - pad * 2 - 70, {hMax:32, dMax:22, noBoost:true});
  it.forEach(function(t, k){
    var x = k ? Z.x + pw + b + pad : Z.x + pad, bh = blockH(C, t, tw, F), y = Z.y + Math.max(pad, (Z.h - bh - 70) / 2);
    C.text(x, y, 120, 50, u.pad2(k + 1), {font:'head', size:46, wt:800, color:CORAL, lh:1, caps:false, ls:0});
    block(C, x, y + 66, tw, bh + 6, t, F, {tc:k ? NAVY : '#FFFFFF', mc:k ? MUTN : LITE});
  });
  if (n > 1) { var cx = Z.x + pw, cy = Z.y + Z.h / 2, d = 76; C.ell(cx - d / 2, cy - d / 2, d, d, {fill:'#FFFFFF', line:CORAL, lw:5, shadow:0.12}); C.text(cx - d / 2, cy - d / 2, d, d, 'VS', {font:'head', size:24, wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0.04}); }
}

/* 2 — versus blades: two slanted panels, navy against coral, VS disc in the gap (p2) */
function vsPanels(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return splitPanel(C, Z, o);
  var s = Math.min(40, Z.w * 0.035), g = 26, mid = Z.x + Z.w / 2, pad = Math.min(44, Z.w * 0.04);
  K.poly(C, [[Z.x, Z.y], [mid - g / 2 + s, Z.y], [mid - g / 2 - s, Z.y + Z.h], [Z.x, Z.y + Z.h]], {fill:NAVY});
  K.poly(C, [[mid + g / 2 + s, Z.y], [Z.x + Z.w, Z.y], [Z.x + Z.w, Z.y + Z.h], [mid + g / 2 - s, Z.y + Z.h]], {fill:CORAL});
  var tw = Z.w / 2 - s - g / 2 - pad * 2 - 20, F = fitN(C, it, tw, 80, Z.h - pad * 2 - 80, {hMax:32, dMax:22, noBoost:true});
  it.forEach(function(t, k){
    var x = k ? mid + g / 2 + s + pad + 20 : Z.x + pad, bh = blockH(C, t, tw, F), y = Z.y + Math.max(pad, (Z.h - bh - 80) / 2);
    C.rect(x, y, 46, 5, {fill:k ? NAVY : CORAL, r:2});
    C.text(x, y + 16, tw, 50, u.pad2(k + 1), {font:'head', size:44, wt:800, color:k ? '#FFFFFF' : CORAL, lh:1, caps:false, ls:0});
    block(C, x, y + 78, tw, bh + 6, t, F, {tc:k ? INKN : '#FFFFFF', mc:k ? '#2A2F4A' : LITE});
  });
  var cy = Z.y + Z.h / 2, d = 84;
  C.ell(mid - d / 2, cy - d / 2, d, d, {fill:'#FFFFFF', shadow:0.15}); C.ell(mid - d / 2 + 7, cy - d / 2 + 7, d - 14, d - 14, {line:NAVY, lw:2});
  C.text(mid - d / 2, cy - d / 2, d, d, 'VS', {font:'head', size:26, wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0.04});
}

/* 3 — curved flow: cards zig-zag across two rows, joined by flowing S connectors */
function curvedFlow(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  if (n < 3) return splitPanel(C, Z, o);
  var cw = Math.min(n === 3 ? 440 : 380, (2 * Z.w - 24 * (n - 1)) / (n + 1)), step = (Z.w - cw) / (n - 1), ch = Math.min(Z.h * 0.44, 240), pad = cw < 240 ? 18 : 24;
  var F = fitN(C, it, cw - pad * 2 - 50, 50, ch - pad * 2 - 4, {hMax:21, dMax:16, hLines:2});
  var cards = [];
  it.forEach(function(t, k){
    var x = Z.x + k * step, y = k % 2 ? Z.y + Z.h - ch : Z.y; cards.push([x, y]);
  });
  for (var k = 0; k < n - 1; k++) {
    var a = cards[k], b = cards[k + 1], down = k % 2 === 0, x1 = a[0] + cw * 0.62, x2 = b[0] + cw * 0.38;
    var y1 = down ? a[1] + ch : a[1], y2 = down ? b[1] - 6 : b[1] + ch + 6, dy = (y2 - y1) * 0.55;
    C.path([['M', x1, y1], ['C', x1, y1 + dy, x2, y2 - dy, x2, y2 - (down ? 10 : -10)]], {stroke:k % 2 ? CORAL : NAVY, lw:3});
    arrowHead(C, x2, y2 - dy, x2, y2, 16, k % 2 ? CORAL : NAVY);
    C.ell(x1 - 6, y1 - 6, 12, 12, {fill:k % 2 ? CORAL : NAVY});
  }
  it.forEach(function(t, k){
    var x = cards[k][0], y = cards[k][1], c = nc(k);
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:16, shadow:0.08});
    C.rect(x, y + 18, 5, ch - 36, {fill:c, r:2});
    dot(C, x + pad + 18, y + pad + 18, 36, k, c);
    var bh = Math.min(ch - pad * 2, blockH(C, t, cw - pad * 2 - 50, F));
    block(C, x + pad + 50, y + Math.max(pad, (ch - bh) / 2), cw - pad * 2 - 50, bh + 4, t, F);
  });
}

/* 4 — branch tree: a navy source disc fans flowing curves out to every item */
function branchTree(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var d = Math.min(Z.h * 0.52, Z.w * 0.24, 230), sx = Z.x + d / 2 + 10, sy = Z.y + Z.h / 2;
  var x0 = Z.x + d + Math.min(150, Z.w * 0.12), lw = Z.x + Z.w - x0, gy = n > 6 ? 8 : 14, rh = Math.min(120, (Z.h - gy * (n - 1)) / n), oy = Z.y + (Z.h - (rh * n + gy * (n - 1))) / 2;
  var bd = Math.min(40, rh - 10), tx = bd + 30, tw = lw - tx - 20;
  var F = fitN(C, it, tw, Math.min(32, rh * 0.45), rh - 8, {hMax:n > 5 ? 19 : 22, dMax:n > 5 ? 14.5 : 16.5, hLines:1});
  C.ell(sx - d / 2 - 12, sy - d / 2 - 12, d + 24, d + 24, {line:CORAL, lw:2, lop:0.7});
  it.forEach(function(t, k){
    var y = oy + k * (rh + gy), my = y + rh / 2, c = nc(k), a = -60 + 120 * (n > 1 ? k / (n - 1) : 0.5), p = K.pol(sx, sy, d / 2 + 12, a);
    C.path([['M', p[0], p[1]], ['C', p[0] + 70, p[1], x0 - 90, my, x0 - 8, my]], {stroke:c, lw:2.5, lop:0.9});
    C.ell(p[0] - 5, p[1] - 5, 10, 10, {fill:c});
    C.rect(x0, y, lw, rh, {fill:'#FFFFFF', r:Math.min(16, rh / 2), shadow:0.06});
    dot(C, x0 + 12 + bd / 2, my, bd, k, c);
    var bh = Math.min(rh - 6, blockH(C, t, tw, F));
    block(C, x0 + tx, my - bh / 2, tw, bh + 4, t, F, {hLines:1});
  });
  C.ell(sx - d / 2, sy - d / 2, d, d, {fill:NAVY, shadow:0.12});
  var tt = String(C.s.title || '').split(/\s+/).slice(0, 4).join(' ');
  C.text(sx - d * 0.36, sy - d * 0.3, d * 0.72, d * 0.6, tt, {font:'head', fit:{max:26, min:12}, color:'#FFFFFF', align:'c', va:'m', lh:1.1, maxLines:3});
  C.rect(sx - 18, sy + d * 0.3, 36, 4, {fill:CORAL, r:2});
}

/* 5 — serpentine: one flowing ribbon runs across, turns, and runs back; numbered stops on it */
function serpentine(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rows = n > 3 ? 2 : 1, m = Math.ceil(n / rows), bh = Z.h / rows, y1 = Z.y + (rows > 1 ? 26 : Z.h * 0.26), y2 = rows > 1 ? Z.y + bh + 26 : y1, r = (y2 - y1) / 2;
  var xL = Z.x + 6, xR = Z.x + Z.w - (rows > 1 ? r + 6 : 30), seg = (xR - xL) / m, d = Math.min(46, seg * 0.36);
  var dpath = [['M', xL, y1], ['L', xR, y1]];
  if (rows > 1) { dpath = dpath.concat(K.arcCmds(xR, y1 + r, r, -90, 90, 'L')); dpath.push(['L', xL + seg * 0.3, y2]); }
  C.path(dpath, {stroke:NAVY, lw:14}); C.path(dpath, {stroke:CORAL, lw:2.5});
  var ex = rows > 1 ? xL + seg * 0.3 : xR, ey = rows > 1 ? y2 : y1;
  arrowHead(C, rows > 1 ? ex + 10 : ex - 10, ey, rows > 1 ? ex - 22 : ex + 22, ey, 30, CORAL);
  var tw = seg - 18, F = fitN(C, it, tw, 44, (rows > 1 ? bh : Z.h * 0.6) - d - 40, {hMax:n > 6 ? 18 : 22, dMax:n > 6 ? 14 : 17});
  it.forEach(function(t, k){
    var r2 = Math.floor(k / m), j = k % m, x = r2 ? xR - seg * (j + 0.5) : xL + seg * (j + 0.5), y = r2 ? y2 : y1, c = nc(k);
    C.ell(x - d / 2 - 5, y - d / 2 - 5, d + 10, d + 10, {fill:'#FFFFFF', shadow:0.12});
    dot(C, x, y, d, k, c);
    block(C, x - tw / 2, y + d / 2 + 18, tw, Z.y + (r2 + 1) * bh - (y + d / 2 + 18) - 8, t, F, {align:'c'});
  });
}

/* 6 — split stats: headline figure on a curved navy panel, the rest listed beside it */
function statsOf(C){ return C.it.slice(0, 4).map(function(t){ return {t:t, v:t.h || t.d, l:t.h ? t.d : '', x:t.x || ''}; }); }
function splitStats(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var b = 30, pw = n > 1 ? Z.w * (Z.w < 800 ? 0.46 : 0.4) : Z.w, pad = 40;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF', r:18, shadow:0.08});
  if (n > 1) curvedPanel(C, Z.x, Z.y, pw, Z.h, b, NAVY); else C.rect(Z.x, Z.y, Z.w, Z.h, {fill:NAVY, r:18});
  var s0 = st[0], t0 = s0.t, lw = pw - b - pad * 2;
  var v0 = C.measureText(s0.v, lw, {font:'head', max:Math.min(120, Z.h * 0.32), min:30, maxLines:1});
  var l0 = s0.l ? C.measureText(s0.l, lw, {font:'head', max:26, min:14, maxLines:2}) : {h:0, s:14}, x0 = s0.x ? C.measureText(s0.x, lw, {font:'body', max:18, min:11, h:Z.h * 0.3}) : {h:0, s:11};
  var tot = v0.h + 20 + l0.h + (x0.h ? x0.h + 10 : 0), y = Z.y + (Z.h - tot) / 2;
  C.text(Z.x + pad, y, lw, v0.h + 4, s0.v, {font:'head', size:v0.s, wt:800, color:'#FFFFFF', lh:1, caps:false, ls:-0.01, ed:{f:'x-part', i:t0.i, p:t0.h ? 0 : 1}, maxLines:1});
  C.rect(Z.x + pad, y + v0.h + 8, 50, 5, {fill:CORAL, r:2});
  if (s0.l) C.text(Z.x + pad, y + v0.h + 20, lw, l0.h + 2, s0.l, {font:'head', size:l0.s, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t0.i, p:1}, maxLines:2});
  if (s0.x) C.text(Z.x + pad, y + v0.h + 20 + l0.h + 10, lw, x0.h + 4, s0.x, {font:'body', size:x0.s, color:LITE, lh:1.36, ed:{f:'x-part', i:t0.i, p:2}});
  var rest = st.slice(1); if (!rest.length) return;
  var rx = Z.x + pw + b + 36, rw = Z.x + Z.w - 32 - rx, rh = (Z.h - 40) / rest.length, vw = Math.min(210, rw * 0.42);
  var vs = C.groupFit(rest.map(function(s){ return s.v; }), vw, rh * 0.62, {font:'head', max:60, min:20, maxLines:1});
  var ls = C.groupFit(rest.map(function(s){ return s.l; }), rw - vw - 20, rh * 0.5, {font:'head', max:21, min:13, maxLines:2});
  var ds = C.groupFit(rest.map(function(s){ return s.x; }), rw - vw - 20, rh * 0.4, {font:'body', max:16, min:11, lh:1.36});
  rest.forEach(function(s, k){
    var y = Z.y + 20 + k * rh, t = s.t, c = k % 2 ? NAVY : CORAL;
    if (k) C.rect(rx, y, rw, 1.5, {fill:'#E3E7F1'});
    C.text(rx, y, vw, rh, s.v, {font:'head', size:vs, wt:800, color:c, va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    var lh = s.l ? C.measureText(s.l, rw - vw - 20, {font:'head', max:ls, min:ls, maxLines:2}).h : 0, dh = s.x ? C.measureText(s.x, rw - vw - 20, {font:'body', max:ds, min:ds, lh:1.36}).h : 0, by = y + (rh - lh - dh - (dh ? 4 : 0)) / 2;
    if (s.l) C.text(rx + vw + 20, by, rw - vw - 20, lh + 2, s.l, {font:'head', size:ls, color:INKN, lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(rx + vw + 20, by + lh + 4, rw - vw - 20, dh + 4, s.x, {font:'body', size:ds, color:MUTN, lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — flow stats: value discs riding a flowing curve, alternating navy and coral */
function flowStats(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var cw = Z.w / n, d = Math.min(cw * 0.66, Z.h * 0.44, 200), off = Math.min(46, Z.h * 0.1), lw = cw - 20;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), d * 0.78, d * 0.4, {font:'head', max:54, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 56, {font:'head', max:21, min:13, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Math.max(20, Z.h - d - off - lh - 50), {font:'body', max:16, min:11, lh:1.36});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.36}).h); });
  var oy = Math.max(0, (Z.h - (d + off + 24 + lh + (dh ? dh + 6 : 0))) / 2); Z = {x:Z.x, y:Z.y + oy, w:Z.w, h:Z.h - oy};
  var pts = [[Z.x - 40, Z.y + d / 2 + off]];
  st.forEach(function(s, k){ pts.push([Z.x + cw * (k + 0.5), Z.y + d / 2 + (k % 2 ? off : 0)]); });
  pts.push([Z.x + Z.w + 40, Z.y + d / 2 + (n % 2 ? off : 0)]);
  K.curve(C, pts, {stroke:CORAL, lw:3, lop:0.8});
  st.forEach(function(s, k){
    var cx = pts[k + 1][0], cy = pts[k + 1][1], c = nc(k), t = s.t;
    C.ell(cx - d / 2 - 8, cy - d / 2 - 8, d + 16, d + 16, {fill:'#FFFFFF', shadow:0.12});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    C.text(cx - d * 0.39, cy - d * 0.2, d * 0.78, d * 0.4, s.v, {font:'head', size:vs, wt:800, color:onN(c), align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    var ly = cy + d / 2 + 24;
    if (s.l) C.text(cx - lw / 2, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(cx - lw / 2, ly + lh + 6, lw, Math.max(16, Z.y + Z.h - ly - lh - 6), s.x, {font:'body', size:ds, color:MUTN, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 8 — split timeline: navy band with a flowing lower edge; stops sit on the wave */
function splitTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, A = Math.min(22, Z.h * 0.05), bandH = Math.min(Z.h * 0.42, 190);
  var ds0 = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - 24, Z.h - bandH - A - 50, {font:'body', max:n <= 4 ? 22 : 19, min:11, lh:1.4});
  var dh0 = 0; it.forEach(function(t){ if (t.h) dh0 = Math.max(dh0, dH(C, t, cw - 24, ds0) * 1.03); });
  var oy = Math.max(0, (Z.h - (bandH + A + 44 + dh0)) / 2), mid = Z.y + oy + bandH; Z = {x:Z.x, y:Z.y + oy, w:Z.w, h:Z.h - oy};
  function wy(x){ return mid + A * Math.sin((x - Z.x) / Z.w * Math.PI * 2.4 + 0.5); }
  C.rect(Z.x, Z.y, Z.w, mid - Z.y - 10, {fill:NAVY, r:18});
  var wp = []; for (var i = 0; i <= 30; i++) { var xx = Z.x + Z.w * i / 30; wp.push([xx, wy(xx)]); }
  var dd = [['M', Z.x, mid - 40], ['L', Z.x, wp[0][1]]];
  for (var j = 0; j < wp.length - 1; j++) { var p0 = wp[j - 1] || wp[j], p1 = wp[j], p2 = wp[j + 1], p3 = wp[j + 2] || p2; dd.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]); }
  dd.push(['L', Z.x + Z.w, mid - 40]); dd.push(['Z']);
  C.path(dd, {fill:NAVY});
  K.curve(C, wp, {stroke:CORAL, lw:4});
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 24, mid - Z.y - 60, {font:'head', max:n <= 4 ? 40 : 34, min:14, maxLines:2});
  var ty = mid + A + 40, ds = ds0;
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), y = wy(x), d = 34;
    if (k) C.rect(Z.x + cw * k, Z.y + 24, 1, mid - Z.y - 70, {fill:'#FFFFFF', op:0.15});
    C.text(x - cw / 2 + 12, Z.y + 18, cw - 24, mid - Z.y - 60, t.h || t.d, {font:'head', size:hs, color:'#FFFFFF', align:'c', va:'m', lh:1.08, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    C.line(x, y - d / 2 - 22, x, y - d / 2 - 4, {color:CORAL, lw:2});
    C.ell(x - d / 2 - 5, y - d / 2 - 5, d + 10, d + 10, {fill:'#FFFFFF', shadow:0.15});
    dot(C, x, y, d, k, CORAL);
    if (t.h && t.d) C.text(x - cw / 2 + 12, ty, cw - 24, Z.y + Z.h - ty, t.d, {font:'body', size:ds, color:INKN, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
D.register({
  id:'navycoral', name:'Navy & Coral Flow', tag:'Navy · coral · process', desc:'Soft lavender-grey pages, navy and coral accents, flowing curved connectors, split panels and versus layouts for processes and transformations.',
  fonts:{head:'DM Sans', body:'DM Sans', headW:700, bodyW:400, headLh:1.06, gf:'DM Sans:wght@400;500;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Flowing ribbon',{}), V('v2','Title + image right',{img:true})],
    closing:[V('v1','Ribbon + centred',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Branch tree',{body:branchTree}), V('n2','Serpentine',{body:serpentine}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Curved split',{body:splitPanel}), V('n2','Versus blades',{body:vsPanels}), V('n3','Branch tree',{body:branchTree}), V('v2','Image left + curved split',{body:splitPanel, img:'left'}), V('v3','Versus + image right',{body:vsPanels, img:'right'}), V('n4','Two panels',{body:'duo'})],
    p3:[V('v1','Curved flow',{body:curvedFlow}), V('n2','Branch tree',{body:branchTree}), V('n3','Serpentine',{body:serpentine}), V('n4','Chevron ribbon',{body:'xChevrons'}), V('v2','Image left + branches',{body:branchTree, img:'left'}), V('v3','Curved flow + image right',{body:curvedFlow, img:'right'}), V('n5','Three cards',{body:'cols'})],
    p4:[V('v1','Branch tree',{body:branchTree}), V('n2','Curved flow',{body:curvedFlow}), V('n3','Versus',{body:'xVersus'}), V('n4','Serpentine',{body:serpentine}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n5','Card grid',{body:'grid'})],
    p56:[V('v1','Serpentine',{body:serpentine}), V('n2','Curved flow',{body:curvedFlow}), V('n3','Benefits vs challenges',{body:'xVersus'}), V('n4','Branch tree',{body:branchTree}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n5','Card grid',{body:'grid'})],
    p78:[V('v1','Serpentine',{body:serpentine}), V('n2','Benefits vs challenges',{body:'xVersus'}), V('n3','Branch tree',{body:branchTree}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n4','Card grid',{body:'grid'})],
    stats:[V('v1','Split headline',{body:splitStats}), V('n2','Flow discs',{body:flowStats}), V('v2','Image left + split',{body:splitStats, img:'left'}), V('v3','Flow discs + image right',{body:flowStats, img:'right'}), V('n3','Stat cards',{body:'statCards', icons:true})],
    timeline:[V('v1','Split wave',{body:splitTimeline}), V('n2','Serpentine',{body:serpentine}), V('n3','Chevron ribbon',{body:'xChevrons'}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('v3','Image left + serpentine',{body:serpentine, img:'left'}), V('n4','Milestone cards',{body:'tlCards'})],
    quote:[V('v1','Card quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

