
/* Template 22 — Terracotta & Teal : warm paper with soft brush sweeps, teal / rust / amber badges and flows */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var TEAL = '#2F7E87', RUST = '#A63D2B', AMBER = '#E0952C', DEEP = '#1F4E5F';
var P0 = {bg:'#FAF6F1', text:'#2B2622', title:'#2B2622', muted:'#6A5F57', accent:'#2F7E87', accent2:'#A63D2B', card:'#FFFFFF', line:'#E9E0D6', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[TEAL, RUST, AMBER, DEEP, '#C0674F', '#5FA5A8'],
  header:{kind:'center', tMax:38, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'solid', r:18, fill:'#FFFFFF', shadow:0.07, hl:{kind:'solid', fill:TEAL, dark:true}},
  icon:{kind:'circle', bg:TEAL, fg:'#FFFFFF'},
  num:{kind:'circle', bg:RUST, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:TEAL, op:0.1, lc:TEAL, label:'Focus'},
  footer:{brand:false, page:true, color:'#A3968B'}, xRows:'#FFFFFF', hubFill:'#F1EAE1', road:DEEP, flowBar:'#EDE4D9',
  imgFramed:true, imgR:20
};
/* soft brush sweeps (layered translucent curves) */
function sweeps(C, x, y, w, c){
  for (var k = 0; k < 6; k++) {
    var pts = []; for (var i = 0; i <= 12; i++) { var t = i / 12; pts.push([x + t * w, y + k * 7 + Math.sin(t * Math.PI + k) * 16]); }
    K.curve(C, pts, {stroke:c, lw:16 - k * 2, lop:0.07});
  }
}
function badgeArc(C, cx, cy, r, c, ic){
  K.seg(C, cx, cy, r + 8, r + 20, 200, 340, {fill:c});
  C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:'#FFFFFF', shadow:0.12});
  C.ell(cx - r * 0.78, cy - r * 0.78, r * 1.56, r * 1.56, {fill:c});
  C.icon(cx - r * 0.4, cy - r * 0.4, r * 0.8, ic, '#FFFFFF');
}
function bg(C, kind){
  C.bg({fill:'#FAF6F1'});
  sweeps(C, -40, 40, 460, RUST); sweeps(C, W - 480, H - 90, 520, TEAL);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, W - 540, 40, 500, H - 80, {r:26});
  else { badgeArc(C, W - 380, 250, 80, TEAL, 'mdi:account-group-outline'); badgeArc(C, W - 170, 330, 66, RUST, 'mdi:chart-line'); badgeArc(C, W - 330, 500, 58, AMBER, 'mdi:handshake-outline'); }
  var x = 64, w = W * 0.52;
  if (f.company) C.text(x, 90, w, 22, f.company, {font:'body', size:14, wt:800, color:RUST, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 130, w, 300, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  [TEAL, RUST, AMBER].forEach(function(c, k){ C.rect(x + k * 46, 454, 40, 6, {fill:c, r:3}); });
  if (C.s.subtitle) C.text(x, 480, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 610, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 480, H - 80, {r:26}); else { badgeArc(C, 260, 300, 90, TEAL, 'mdi:hand-wave-outline'); badgeArc(C, 440, 470, 60, AMBER, 'mdi:email-outline'); }
  var x = 580, w = W - 644;
  C.text(x, 160, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(x, 384, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 480, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:RUST}); return; }
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:RUST});
}
/* ================= signature bodies (terracotta motif: brush strokes, badge arcs, flow circles) ================= */
var CYC = [TEAL, RUST, AMBER, DEEP];
function cK(k){ return CYC[k % CYC.length]; }
function fhd(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function blockH(C, t, F, w){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
function pctOf(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [72, 84, 64, 90][k % 4]; }
/* painted brush band */
function brush(C, x, y, w, h, c, op){
  op = op || 0.14; var lw = h * 0.36;
  for (var k = 0; k < 4; k++) {
    var yy = y + h * (0.24 + 0.17 * k), pts = [], x0 = x + lw / 2 + (k % 2 ? 10 : 0), x1 = x + w - lw / 2 - (k % 3 ? 14 : 0);
    for (var i = 0; i <= 8; i++) { var t = i / 8; pts.push([x0 + t * (x1 - x0), yy + Math.sin(t * Math.PI * 1.4 + k * 1.3) * h * 0.05]); }
    K.curve(C, pts, {stroke:c, lw:lw, lop:op});
  }
}
/* circle with a badge arc hugging its top-left */
function arcBadge(C, cx, cy, r, c, label, o){
  o = o || {};
  K.seg(C, cx, cy, r + 5, r + 13, 190, 330, {fill:c});
  C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:'#FFFFFF', shadow:0.1});
  C.ell(cx - r * 0.8, cy - r * 0.8, r * 1.6, r * 1.6, {fill:c});
  C.text(cx - r * 0.72, cy - r * 0.72, r * 1.44, r * 1.44, label, {font:'head', fit:{max:Math.round(r * (o.big ? 0.62 : 0.7)), min:12}, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.ed || null});
}
function dashCurve(C, pts, c, lw){ for (var j = 0; j < pts.length - 1; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:c, lw:lw || 2.5}); }
/* 1. brush rows: every item painted on its own stroke */
function brushRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols, gy = per > 5 ? 8 : 14, rh = Math.min(140, (Z.h - gy * (per - 1)) / per);
  var y0 = Z.y + (Z.h - (rh * per + gy * (per - 1))) / 2, d = Math.min(62, rh * 0.74), tx = d + 34, tw = cw - tx - 24;
  var agendaLike = it.every(function(t){ return !t.h; }) && n > 2;
  var F = fhd(C, it, tw, Math.min(34, rh * 0.42), rh - 12, {hMax:21, dMax:16.5, hLines:1});
  var ls = agendaLike ? C.groupFit(it.map(function(t){ return t.d; }), tw, rh * 0.7, {font:'head', max:n <= 5 ? 28 : 24, min:13, maxLines:1}) : 0;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), col = cK(k);
    brush(C, x + d * 0.4, y, cw - d * 0.4, rh, col, 0.1);
    arcBadge(C, x + d / 2 + 6, y + rh / 2, d / 2, col, u.pad2(k + 1));
    if (agendaLike) C.text(x + tx, y, tw, rh, t.d, {font:'head', size:ls, color:'#2B2622', va:'m', lh:1.15, maxLines:1, ed:{f:'x-part', i:t.i, p:1}});
    else { var bhh = blockH(C, t, F, tw); K.hd(C, x + tx, y + (rh - bhh) / 2, tw, bhh + 4, t, F, {tc:'#2B2622', mc:'#4F4640', hLines:1}); }
  });
}
/* 2. badge arcs row joined by dotted bridges */
function arcBadges(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(80, cw * 0.26, Z.h * 0.18), iw = cw - 36;
  var F = fhd(C, it, iw, 60, Z.h - 2 * r - 70, {hMax:23, dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, blockH(C, t, F, iw)); });
  var top = Z.y + Math.max(0, (Z.h - (2 * r + 60 + th)) / 2), cy = top + r + 16, ty = cy + r + 44;
  for (var k = 0; k < n - 1; k++) {
    var ax = Z.x + cw * (k + 0.5), bx = ax + cw, pts = [];
    for (var j = 0; j <= 16; j++) { var tt = j / 16; pts.push([ax + r + 14 + tt * (bx - ax - 2 * r - 28), cy - 10 - Math.sin(tt * Math.PI) * r * 0.8]); }
    dashCurve(C, pts, cK(k), 2.5);
    K.poly(C, [[bx - r - 16, cy - 20], [bx - r - 4, cy - 9], [bx - r - 18, cy - 5]], {fill:cK(k)});
  }
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), col = cK(k);
    arcBadge(C, cx, cy, r, col, u.pad2(k + 1), {big:true});
    C.rect(cx - 22, cy + r + 24, 44, 4, {fill:col, r:2});
    K.hd(C, cx - iw / 2, ty, iw, th + 4, t, F, {align:'c', tc:'#2B2622', mc:'#5E544C'});
  });
}
/* 3. serpentine flow: circles on an S-shaped path, text under each */
function serpentine(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rows = n <= 3 ? 1 : 2, per = Math.ceil(n / rows), step0 = Z.h / rows, d = Math.min(80, step0 * 0.34);
  var uw0 = Z.w - (rows > 1 ? step0 / 2 : 0) - 8, iw = uw0 / per - 22;
  var F, th, step, turnR, ext, uw, cw;
  for (var pass = 0; pass < 4; pass++) { /* keep each text block inside its own column (no overlap with its neighbour) */
    F = fhd(C, it, iw, 48, step0 - d - 36, {hMax:20, dMax:15.5});
    th = 0; it.forEach(function(t){ th = Math.max(th, blockH(C, t, F, iw)); });
    step = Math.min(step0, d + 30 + th + 44); turnR = rows > 1 ? step / 2 : 0; ext = rows > 1 ? Math.max(0, iw / 2 + 14 - turnR * 0.55) : 0; uw = Z.w - turnR - ext - 8; cw = uw / per;
    if (cw - 22 >= iw - 0.5) break;
    iw = cw - 22;
  }
  var top = Z.y + Math.max(0, (Z.h - (step * (rows - 1) + d + 30 + th)) / 2);
  var ys = []; for (var r = 0; r < rows; r++) ys.push(top + d / 2 + 4 + r * step);
  function pos(k){ var r = Math.floor(k / per), j = k % per; return [r % 2 ? Z.x + uw - cw * (j + 0.5) : Z.x + cw * (j + 0.5), ys[r]]; }
  var xR = Z.x + uw - cw * 0.5;
  for (var q = 0; q < rows; q++) { var last = pos(Math.min(n - 1, (q + 1) * per - 1)), first = pos(q * per), x0 = Math.min(first[0], last[0]); C.rect(x0, ys[q] - 5, xR + ext - x0, 10, {fill:'#EDE4D9', r:5}); }
  if (rows > 1) {
    var cx = xR + ext, cy = (ys[0] + ys[1]) / 2, rr = (ys[1] - ys[0]) / 2;
    D.arc(C, cx, cy, rr, -90, 90, {color:'#EDE4D9', lw:10, op:1});
    var pts = []; for (var i = 0; i <= 18; i++) { var a = (-90 + 180 * i / 18) * Math.PI / 180; pts.push([cx + rr * Math.cos(a), cy + rr * Math.sin(a)]); }
    dashCurve(C, pts, AMBER, 2.5);
  }
  it.forEach(function(t, k){
    var p = pos(k), col = cK(k);
    if (k < n - 1 && Math.floor(k / per) === Math.floor((k + 1) / per)) { var p2 = pos(k + 1), dir = p2[0] > p[0] ? 1 : -1; C.shape('chevron', (p[0] + p2[0]) / 2 - 9, p[1] - 9, 18, 18, {fill:col, rot:dir < 0 ? 180 : 0}); }
    C.ell(p[0] - d / 2 - 6, p[1] - d / 2 - 6, d + 12, d + 12, {fill:'#FAF6F1'});
    arcBadge(C, p[0], p[1], d / 2, col, u.pad2(k + 1), {big:true});
    K.hd(C, p[0] - iw / 2, p[1] + d / 2 + 24, iw, th + 4, t, F, {align:'c', tc:'#2B2622', mc:'#5E544C'});
  });
}
/* 4. two notes with badge arcs and a flow disc between */
function twoNotes(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = 64, cw = (Z.w - gap * (n - 1)) / n, pad = Math.min(40, cw * 0.08), r = 38, iw = cw - pad * 2;
  var F = fhd(C, it, iw, 80, Z.h - pad * 2 - r * 2 - 30, {hMax:26, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), dark = k === 1, col = k ? TEAL : RUST;
    C.rect(x, Z.y, cw, Z.h, {fill:dark ? TEAL : '#FFFFFF', r:22, shadow:0.08});
    if (!dark) brush(C, x + 20, Z.y + Z.h - 110, cw - 40, 90, RUST, 0.06); else brush(C, x + 20, Z.y + Z.h - 110, cw - 40, 90, '#FFFFFF', 0.07);
    if (dark) { K.seg(C, x + pad + r, Z.y + pad + r, r + 5, r + 13, 190, 330, {fill:AMBER}); C.ell(x + pad, Z.y + pad, 2 * r, 2 * r, {fill:'#FFFFFF'}); C.text(x + pad, Z.y + pad, 2 * r, 2 * r, u.pad2(k + 1), {font:'head', size:28, wt:700, color:TEAL, align:'c', va:'m', lh:1, caps:false, ls:0}); }
    else arcBadge(C, x + pad + r, Z.y + pad + r, r, col, u.pad2(k + 1), {big:true});
    var bhh = blockH(C, t, F, iw), by = Math.max(Z.y + pad + r * 2 + 30, Z.y + (Z.h - bhh) / 2 - 10);
    K.hd(C, x + pad, by, iw, Math.max(bhh + 4, 40), t, F, {tc:dark ? '#FFFFFF' : '#2B2622', mc:dark ? '#DCEFF0' : '#5E544C'});
  });
  if (n === 2) {
    var mx = Z.x + cw + gap / 2, my = Z.y + Z.h / 2;
    C.ell(mx - 30, my - 30, 60, 60, {fill:AMBER, line:'#FAF6F1', lw:5});
    C.shape('chevron', mx - 10, my - 11, 22, 22, {fill:'#FFFFFF'});
  }
}
/* 5. half-gauge stats */
function gaugeStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, R = Math.min(cw * 0.4, Z.h * 0.4, 160), r0 = R * 0.76, iw = cw - 30;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 52, {font:'head', max:n <= 2 ? 24 : 20, min:13, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, 44, {font:'body', max:16, min:11, maxLines:2});
  var vs = C.groupFit(st.map(function(t){ return t.v; }), r0 * 1.6, r0 * 0.7, {font:'head', max:Math.round(R * 0.5), min:20, maxLines:1});
  var blk = R + 24 + ls * 1.2 * 2 + 10 + xs * 1.35 * 2, top = Z.y + Math.max(0, (Z.h - blk) / 2), cy = top + R;
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), col = cK(k), p = pctOf(t.v, k), a1 = 180 + 180 * p / 100;
    K.seg(C, cx, cy, r0, R, 180, 360, {fill:'#EDE4D9'});
    K.seg(C, cx, cy, r0, R, 180, a1, {fill:col});
    var e = K.pol(cx, cy, (R + r0) / 2, a1); C.ell(e[0] - (R - r0) * 0.62, e[1] - (R - r0) * 0.62, (R - r0) * 1.24, (R - r0) * 1.24, {fill:col, line:'#FAF6F1', lw:3});
    for (var q = 0; q <= 4; q++) { var a = 180 + 45 * q, p1 = K.pol(cx, cy, r0 - 6, a), p2 = K.pol(cx, cy, r0 - 16, a); C.line(p1[0], p1[1], p2[0], p2[1], {color:'#C9B9A8', lw:2}); }
    C.text(cx - r0 * 0.8, cy - vs * 1.1, r0 * 1.6, vs * 1.1, t.v, {font:'head', size:vs, color:col, align:'c', va:'b', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx - R, cy + 8, R * 2, 2, {fill:'#E2D6C8'});
    var le = C.text(cx - iw / 2, cy + 24, iw, ls * 1.2 * 2 + 2, t.l, {font:'head', size:ls, color:'#2B2622', align:'c', lh:1.2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(cx - iw / 2, cy + 24 + le._h + 10, iw, xs * 1.35 * 2 + 2, t.x, {font:'body', size:xs, color:'#6A5F57', align:'c', lh:1.35});
  });
}
/* 6. stats painted on brush strokes */
function brushStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var gy = 16, rh = Math.min(150, (Z.h - gy * (n - 1)) / n), y0 = Z.y + (Z.h - rh * n - gy * (n - 1)) / 2, vw = Math.min(Z.w * 0.42, 380), tw = Z.w - vw - 30;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), vw - 50, rh * 0.72, {font:'head', max:84, min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), tw, rh * 0.5, {font:'head', max:26, min:13, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, rh * 0.36, {font:'body', max:17, min:11, maxLines:2});
  st.forEach(function(t, k){
    var y = y0 + k * (rh + gy), col = cK(k);
    brush(C, Z.x, y, vw, rh, col, 0.2);
    C.text(Z.x + 26, y, vw - 50, rh, t.v, {font:'head', size:vs, color:col === AMBER ? '#9A5F12' : col, va:'m', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    var lh2 = C.measureText(t.l, tw, {font:'head', max:ls, min:ls, maxLines:2}).h, xh = t.x ? C.measureText(t.x, tw, {font:'body', max:xs, min:xs}).h + 6 : 0, by = y + (rh - lh2 - xh) / 2;
    C.text(Z.x + vw + 30, by, tw, lh2 + 2, t.l, {font:'head', size:ls, color:'#2B2622', lh:1.1, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(Z.x + vw + 30, by + lh2 + 6, tw, xh, t.x, {font:'body', size:xs, color:'#6A5F57', lh:1.35});
    if (k < n - 1) C.rect(Z.x + vw + 30, y + rh + gy / 2, tw, 1, {fill:'#E2D6C8'});
  });
}
/* 7. milestones on a rising arch */
function archTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, d = Math.min(104, cw * 0.58), iw = cw - 22, A0 = Math.min(90, Z.h * 0.2);
  var F = fhd(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), iw, 1, Z.h - d - A0 - 40, {dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var A = Math.min(A0 + Math.max(0, (Z.h - (d + A0 + 30 + th)) * 0.35), Z.h * 0.3), tot = d + A + 30 + th, top = Z.y + Math.max(0, (Z.h - tot) / 2), base = top + d / 2 + 8 + A;
  function yAt(x){ var t = (x - Z.x) / Z.w; return base - A * Math.sin(Math.PI * t); }
  var pts = []; for (var i = 0; i <= 48; i++) { var x = Z.x + Z.w * i / 48; pts.push([x, yAt(x)]); }
  K.curve(C, pts, {stroke:'#EDE4D9', lw:16});
  dashCurve(C, pts, DEEP, 2);
  var hs = C.groupFit(it.map(function(t){ return t.h; }), d * 0.8, d * 0.5, {font:'head', max:Math.round(d * 0.3), min:13, maxLines:1});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = yAt(cx), col = cK(k), ty = cy + d / 2 + 26;
    C.ell(cx - d / 2 - 6, cy - d / 2 - 6, d + 12, d + 12, {fill:'#FAF6F1'});
    K.seg(C, cx, cy, d / 2 + 5, d / 2 + 13, 190, 330, {fill:col});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:col});
    C.text(cx - d * 0.42, cy - d / 2, d * 0.84, d, t.h || u.pad2(k + 1), {font:'head', size:hs, color:'#FFFFFF', align:'c', va:'m', lh:1, maxLines:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx - 1, cy + d / 2 + 4, 2, 16, {fill:col});
    C.text(cx - iw / 2, ty, iw, Math.min(th + 6, Z.y + Z.h - ty), t.d, {font:'body', size:F.ds, color:'#4F4640', align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 8. centred flow spine, items alternating left / right */
function spineFlow(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rh = Z.h / n, d = Math.min(58, rh * 0.78), cx = Z.x + Z.w / 2, sw = Z.w / 2 - d / 2 - 44;
  var F = fhd(C, it, sw, Math.min(34, rh * 0.42), rh - 8, {hMax:22, dMax:17, hLines:1});
  C.rect(cx - 5, Z.y, 10, Z.h, {fill:'#EDE4D9', r:5});
  var pts = []; for (var i = 0; i <= 2 * n; i++) pts.push([cx, Z.y + Z.h * i / (2 * n)]);
  it.forEach(function(t, k){
    var y = Z.y + k * rh, cy = y + rh / 2, col = cK(k), left = k % 2 === 0;
    C.ell(cx - d / 2 - 5, cy - d / 2 - 5, d + 10, d + 10, {fill:'#FAF6F1'});
    arcBadge(C, cx, cy, d / 2, col, u.pad2(k + 1));
    var bx = left ? cx - d / 2 - 36 - sw : cx + d / 2 + 36;
    C.line(left ? cx - d / 2 - 30 : cx + d / 2 + 14, cy, left ? cx - d / 2 - 14 : cx + d / 2 + 30, cy, {color:col, lw:3});
    var bhh = blockH(C, t, F, sw), bb = Math.min(rh - 8, bhh + 34);
    brush(C, bx - 12, cy - bb / 2, sw + 24, bb, col, 0.08);
    K.hd(C, bx, cy - bhh / 2, sw, bhh + 4, t, F, {align:left ? 'r' : 'l', tc:'#2B2622', mc:'#5E544C', hLines:1});
  });
}
/* 9. agenda on a curved arc */
function agendaArc(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var R = Z.h * 0.78, cy = Z.y + Z.h / 2, mid = Z.x + (Z.w > 900 ? Z.w * 0.24 : 130), cx = mid - R, rh = Z.h / n, d = Math.min(64, rh * 0.8);
  var am = Math.asin(Math.min(0.98, (Z.h / 2 + 10) / R)) * 180 / Math.PI;
  D.arc(C, cx, cy, R, -am, am, {color:'#EDE4D9', lw:14, op:1});
  D.arc(C, cx, cy, R, -am, am, {color:DEEP, lw:2, op:0.6});
  D.arc(C, cx, cy, R - 40, -am * 0.8, am * 0.8, {color:AMBER, lw:3, op:0.5});
  var ls = C.groupFit(it.map(function(t){ return t.h || t.d; }), Z.x + Z.w - mid - d / 2 - 30, rh * 0.62, {font:'head', max:n <= 5 ? 32 : 24, min:13, maxLines:1});
  it.forEach(function(t, k){
    var y = Z.y + rh * (k + 0.5), dy = y - cy, x = cx + Math.sqrt(Math.max(0, R * R - dy * dy)), col = cK(k);
    C.ell(x - d / 2 - 5, y - d / 2 - 5, d + 10, d + 10, {fill:'#FAF6F1'});
    arcBadge(C, x, y, d / 2, col, u.pad2(k + 1));
    var tx = x + d / 2 + 28;
    C.text(tx, y - rh / 2, Z.x + Z.w - tx, rh, t.h || t.d, {font:'head', size:ls, color:'#2B2622', va:'m', lh:1.1, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
  });
}
D.register({
  id:'terracotta', name:'Terracotta & Teal', tag:'Warm · people & process', desc:'Warm paper with soft brush sweeps, teal, rust and amber badges, arrows and cycles for HR, people and process decks.',
  fonts:{head:'Rubik', body:'Rubik', headW:700, bodyW:400, headLh:1.06, gf:'Rubik:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Badge trio',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Badges left',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Curved agenda',{body:agendaArc}), V('n2','Brush rows',{body:brushRows}), V('v2','Curve + image right',{body:agendaArc, img:'right'})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Two notes',{body:twoNotes}), V('n2','Badge arcs',{body:arcBadges}), V('n3','Brush rows',{body:brushRows}), V('v2','Image left + brush rows',{body:brushRows, img:'left'}), V('v3','Notes + image right',{body:twoNotes, img:'right'})],
    p3:[V('v1','Badge arcs',{body:arcBadges}), V('n2','Brush rows',{body:brushRows}), V('n3','Flow circles',{body:'xFlow'}), V('v2','Image left + brush rows',{body:brushRows, img:'left'}), V('v3','Flow spine + image right',{body:spineFlow, img:'right'})],
    p4:[V('v1','Serpentine flow',{body:serpentine}), V('n2','Badge arcs',{body:arcBadges}), V('n3','Brush rows',{body:brushRows}), V('v2','Brush rows + image right',{body:brushRows, img:'right'})],
    p56:[V('v1','Serpentine flow',{body:serpentine}), V('n2','Brush rows',{body:brushRows}), V('n3','Flow spine',{body:spineFlow}), V('n4','Flow circles',{body:'xFlow'}), V('v2','Brush rows + image right',{body:brushRows, img:'right'})],
    p78:[V('v1','Serpentine flow',{body:serpentine}), V('n2','Brush rows',{body:brushRows, cols:2}), V('n3','Flow spine',{body:spineFlow}), V('v2','Brush rows + image right',{body:brushRows, img:'right', cols:1})],
    stats:[V('v1','Half gauges',{body:gaugeStats}), V('n2','Brush numbers',{body:brushStats}), V('v2','Image left + brush numbers',{body:brushStats, img:'left'})],
    timeline:[V('v1','Rising arch',{body:archTimeline}), V('n2','Flow spine',{body:spineFlow}), V('n3','Flow circles',{body:'xFlow'}), V('v2','Flow spine + image right',{body:spineFlow, img:'right'})],
    quote:[V('v1','Warm quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

