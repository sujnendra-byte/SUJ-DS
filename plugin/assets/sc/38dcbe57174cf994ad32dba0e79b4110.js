
/* Template 19 — Mint & Lilac : airy off-white, mint → aqua → periwinkle → lilac pastels, soft 3D shapes, navy roads */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var SER = ['#5FD6B4', '#49BFE0', '#7F95F5', '#A081EE', '#62D3CF', '#8C7BF0'];
var NAVY = '#1B1F4B';
var P0 = {bg:'#F7F8FC', text:'#1B1F4B', title:'#1B1F4B', muted:'#5A5F80', accent:'#7F95F5', accent2:'#5FD6B4', card:'#FFFFFF', line:'#E1E4F0', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:SER,
  header:{kind:'left', tMax:40, tMin:24, sMax:16, top:50},
  card:{kind:'solid', r:22, fill:'#FFFFFF', shadow:0.06, hl:{kind:'grad', grad:{ang:135, stops:[[0, '#5FD6B4'], [100, '#A081EE']]}, dark:true}},
  icon:{kind:'soft', bg:'#7F95F5', op:0.18},
  num:{kind:'circle', bg:'#7F95F5', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#A081EE', op:0.14, lc:'#7F4FE0', label:'Takeaway'},
  footer:{brand:true, page:true, color:'#9A9EB8'}, xRows:'#FFFFFF', hubFill:'#EEF0FA', road:NAVY, flowBar:'#E6E8F4',
  imgFramed:true, imgR:26
};
/* stacked discs — drawn 1:1 from the supplied artwork (12 ellipses); on a colour theme the two light rings and their white tops use theme tints, so the top of the stack never blends into the background */
function coil(C, cx, cy, s){
  var f = s / 204.44, th = !!(C.T && C.T.__rec), PG = th ? '#C4C9F2' : '#FFFFFF', RG = th ? '#A3A3FF' : '#CCCCFF';
  /* on a colour theme the lower discs get clear dark / light steps, so each layer stays readable in one-colour themes (red, green ...) */
  var M = th ? {'#6633FF':'#3D1FCC', '#6666FF':'#6C6CFF', '#6699FF':'#5C85F0', '#66CCFF':'#8FD8FF', '#9933FF':'#7A29E6'} : {};
  [[325.19, 484.74, 204.44, 61.33, '#6633FF'], [325.19, 440.89, 204.44, 61.33, '#6666FF'], [323.11, 433.48, 131.36, 39.41, '#6633FF'],
   [325.19, 379.65, 204.44, 61.33, '#6699FF'], [325.19, 335.80, 204.44, 61.33, '#66CCFF'], [323.11, 328.40, 131.36, 39.41, '#9933FF'],
   [325.19, 274.57, 204.44, 61.33, RG], [325.19, 230.72, 204.44, 61.33, PG], [323.11, 223.31, 131.36, 39.41, '#9999FF'],
   [325.19, 169.48, 204.44, 61.33, RG], [325.19, 125.63, 204.44, 61.33, PG], [323.11, 118.22, 131.36, 39.41, '#9999FF']
  ].forEach(function(e){ C.ell(cx + (e[0] - 325.19 - e[2]) * f, cy + (e[1] - 305.2 - e[3]) * f, 2 * e[2] * f, 2 * e[3] * f, {fill:M[e[4]] || e[4]}); });
}
function bg(C, kind){
  C.bg({grad:{ang:135, stops:[[0, '#F7F8FC'], [100, '#EEF1FB']]}});
  D.glow(C, W - 80, 60, 240, 180, '#A081EE', 0.16); D.glow(C, 60, H - 40, 240, 180, '#5FD6B4', 0.14);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, W - 540, 40, 500, H - 80, {r:34}); else coil(C, W - 300, 360, 190);
  var x = 64, w = W * 0.52;
  if (f.company) C.text(x, 90, w, 22, f.company, {font:'body', size:14, wt:800, color:'#7F4FE0', caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 130, w, 300, C.s.title, {font:'head', fit:{max:68, min:32}, color:NAVY, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  if (C.s.subtitle) C.text(x, 456, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) K.pill(C, x, 600, Math.min(w, meta.length * 8 + 60), 40, meta, {fill:'#FFFFFF', color:NAVY, size:14, wt:600});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 500, H - 80, {r:34}); else coil(C, 300, 360, 180);
  var x = 600, w = W - 664;
  C.text(x, 170, w, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(x, 384, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 480, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#7F95F5'}); return; }
  C.rect(150, 100, W - 300, H - 200, {fill:'#FFFFFF', r:40, shadow:0.06});
  D.B.quote(C, {x:220, y:150, w:W - 440, h:H - 300}, {qc:'#7F95F5'});
}

/* ---------- signature helpers ---------- */
function LP(c, t){ return u.mix(c, '#FFFFFF', t == null ? 0.55 : t); }
function DP(c){ return u.mix(c, NAVY, 0.35); }
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function pct(v, k, n){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : 55 + 40 * (n <= 1 ? 1 : k / (n - 1)); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + descH(C, t, w, F); }
function hdc(C, x, y, w, h, t, F, o){ var bh = blockH(C, t, w, F), yy = y + Math.max(0, (h - bh) / 2); K.hd(C, x, yy, w, h - (yy - y), t, F, o); }
function numPill(C, x, y, k, c, o){ o = o || {}; K.pill(C, x, y, o.w || 54, o.h || 28, u.pad2(k + 1), {fill:o.fill || '#FFFFFF', color:NAVY, size:o.size || 13, wt:800, line:o.line, lw:1.5}); }

/* 1. two overlapping pastel circles (venn) */
function vennDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var d = Math.min(Z.h + 10, Z.w / 1.75), ov = d * 0.22, cy = Z.y + Z.h / 2, cx0 = Z.x + Z.w / 2 - d / 2 + ov / 2, cx1 = Z.x + Z.w / 2 + d / 2 - ov / 2;
  var tw = d * 0.56, th = d * 0.56;
  var F = K.fitHD(C, it, tw, 70, th - 40, {hMax:24, dMax:17, hMin:14, dMin:11.5});
  [cx0, cx1].slice(0, n).forEach(function(cx, k){
    var c = k ? '#A081EE' : '#5FD6B4';
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c, op:0.3});
    var tx = k ? cx - d / 2 + ov + 22 : cx - d * 0.4, t = it[k];
    numPill(C, tx, cy - th / 2 - 8, k, c, {fill:'#FFFFFF'});
    hdc(C, tx, cy - th / 2 + 32, tw, th - 32, t, F, {tc:NAVY, mc:'#3E4368'});
  });
  if (n > 1) { var mx = Z.x + Z.w / 2; C.ell(mx - 30, cy - 30, 60, 60, {fill:'#FFFFFF', shadow:0.08}); C.text(mx - 30, cy - 30, 60, 60, '&', {font:'head', size:26, wt:800, color:'#7F4FE0', align:'c', va:'m', lh:1, caps:false, ls:0}); }
}

/* 2. items inside soft organic blobs, gently bobbing */
function blobCluster(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; if (n > 4) return pillRows(C, Z, o); var P = C.P;
  var cw = Z.w / n, r = Math.min(cw / 2 - 4, Z.h / 2 - Z.h * 0.05), tw = r * 1.3, th = r * 1.08;
  var F = K.fitHD(C, it, tw, 60, th - 36, {hMax:20, dMax:15, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var c = SER[k % 4], cx = Z.x + cw * (k + 0.5), cy = Z.y + Z.h / 2 + (k % 2 ? 1 : -1) * Z.h * 0.04;
    K.blob(C, cx + r * 0.08, cy + r * 0.1, r * 0.98, c, 0.35, k + 2);
    K.blob(C, cx, cy, r, LP(c, 0.5), 1, k + 5);
    numPill(C, cx - 27, cy - th / 2 - 14, k, c, {fill:'#FFFFFF'});
    hdc(C, cx - tw / 2, cy - th / 2 + 24, tw, th - 24, t, F, {align:'c', tc:NAVY, mc:'#3E4368'});
  });
}

/* 3. soft rows led by a pastel pill tag */
function pillRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = n > 5 && Z.w > 800 ? 2 : 1, per = Math.ceil(n / cols), gx = 28, cw = (Z.w - gx * (cols - 1)) / cols, gy = n > 6 ? 10 : 14, rh = (Z.h - gy * (per - 1)) / per;
  var H2 = hasH(it), pw = H2 ? Math.min(250, cw * 0.36) : Math.min(64, rh * 1.3), ph = Math.min(rh - 12, 52), dx = pw + 34, dw = cw - dx - 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), pw - 34, ph - 6, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 10, H2 ? {font:'body', max:n <= 3 ? 19 : 16, min:11, lh:1.35} : {font:'head', max:n <= 4 ? 26 : 21, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * (rh + gy), col = SER[k % 4];
    C.rect(x, y, cw, rh, {fill:'#FFFFFF', r:Math.min(rh / 2, 26), shadow:0.05});
    C.rect(x + 8, y + (rh - ph) / 2, pw, ph, {fill:LP(col, 0.45), r:ph / 2});
    if (H2) C.text(x + 26, y + (rh - ph) / 2, pw - 34, ph, t.h || u.pad2(k + 1), {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    else C.text(x + 8, y + (rh - ph) / 2, pw, ph, u.pad2(k + 1), {font:'head', size:Math.min(20, ph * 0.42), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + dx, y, dw, rh, t.d, H2 ? {font:'body', size:ds, color:P.muted, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}} : {font:'head', size:ds, color:NAVY, va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
  });
}

/* 4. racetrack loop: cards ride a soft rounded track, arrows at the turns */
function loopTiles(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var a = Math.ceil(n / 2), b = n - a, gyy = 34, ch = (Z.h - gyy) / 2, y1 = Z.y + ch / 2, y2 = Z.y + Z.h - ch / 2, R = (y2 - y1) / 2, cy = (y1 + y2) / 2;
  var xl = Z.x + R, xr = Z.x + Z.w - R, g = 18, cw = (xr - xl + R * 0.9 - g * (a - 1)) / a, cx0 = xl - R * 0.45;
  var d = [['M', xl, y1], ['L', xr, y1]].concat(K.arcCmds(xr, cy, R, -90, 90, 'L')).concat([['L', xl, y2]]).concat(K.arcCmds(xl, cy, R, 90, 270, 'L'));
  C.path(d, {stroke:'#D9DDF7', lw:16});
  C.path(d, {stroke:'#A081EE', lw:2.5, lop:0.7});
  K.poly(C, [[xr + R - 13, cy - 10], [xr + R + 13, cy - 10], [xr + R, cy + 10]], {fill:'#7F95F5'});
  K.poly(C, [[xl - R - 13, cy + 10], [xl - R + 13, cy + 10], [xl - R, cy - 10]], {fill:'#5FD6B4'});
  var pad = 20, F = K.fitHD(C, it, cw - pad * 2, 48, ch - pad * 2 - 40, {hMax:20, dMax:16, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var top = k < a, slot = top ? k : (b - 1) - (k - a), x = cx0 + slot * (cw + g) + (top ? 0 : (a - b) * (cw + g) / 2), y = top ? Z.y : Z.y + ch + gyy, c = SER[k % 4];
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:24, shadow:0.07});
    C.rect(x, y, cw, 8, {fill:LP(c, 0.2), r:4});
    numPill(C, x + pad, y + pad, k, c, {fill:LP(c, 0.55)});
    hdc(C, x + pad, y + pad + 38, cw - pad * 2, ch - pad * 2 - 38, t, F, {tc:NAVY});
  });
}

/* 5. overlapping bubbles for stats */
function bubbleStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, iw = cw - 20;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 48, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 40, {font:'body', max:14, min:11, maxLines:2});
  var bot = ls * 2.45 + (st.some(function(t){ return t.d; }) ? ds * 2.8 + 6 : 0) + 20;
  var D = Math.min(Z.h - bot - 10, cw * 1.2, 300), top = Z.y + Math.max(0, (Z.h - bot - D - 10) / 2), cy = top + D / 2;
  var sz = st.map(function(t, k){ return D * (0.72 + 0.28 * pct(t.v, k, n) / 100); });
  var vs = C.groupFit(st.map(function(t){ return t.v; }), Math.min.apply(null, sz) * 0.72, 80, {font:'head', max:60, min:22, maxLines:1});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), d = sz[k], c = SER[k % 4];
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:LP(c, 0.3), op:0.92, line:'#FFFFFF', lw:6});
    C.text(cx - d * 0.4, cy - vs * 0.6, d * 0.8, vs * 1.2, t.v, {font:'head', size:vs, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
  });
  st.forEach(function(t, k){
    var x = Z.x + cw * k + 10, ly = cy + D / 2 + 22;
    C.text(x, ly, iw, ls * 2.45, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x, ly + ls * 2.45 + 2, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 6. stats as big soft pills with the number in a white disc */
function pillStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cols = n > 2 && Z.w > 700 ? 2 : 1, rows = Math.ceil(n / cols), gx = 26, gy = 22, cw = (Z.w - gx * (cols - 1)) / cols, rh = (Z.h - gy * (rows - 1)) / rows;
  var ph = Math.min(rh, 150), dd = ph - 20, tw = cw - dd - 60;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), dd * 0.8, dd * 0.5, {font:'head', max:44, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), tw, ph * 0.45, {font:'head', max:n <= 2 ? 28 : 22, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), tw, ph * 0.35, {font:'body', max:15, min:11, maxLines:2});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + gx) + (cols - inRow) * (cw + gx) / 2, y = Z.y + r * (rh + gy) + (rh - ph) / 2, c = SER[k % 4];
    C.rect(x, y, cw, ph, {fill:LP(c, 0.45), r:ph / 2});
    C.ell(x + 10, y + 10, dd, dd, {fill:'#FFFFFF', shadow:0.06});
    C.text(x + 10 + dd * 0.1, y + 10, dd * 0.8, dd, t.v, {font:'head', size:vs, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var lh = C.measureText(t.l, tw, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.d ? ds * 1.36 * Math.min(2, C.measureText(t.d, tw, {font:'body', max:ds, min:ds}).n) : 0, ty = y + (ph - lh - (dh ? dh + 6 : 0)) / 2;
    C.text(x + dd + 36, ty, tw, lh + 4, t.l, {font:'head', size:ls, color:NAVY, lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x + dd + 36, ty + lh + 6, tw, dh + 4, t.d, {font:'body', size:ds, color:'#3E4368', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 7. stepping-stone path: dotted wave with pebbles and pill tags */
function stonePath(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, mid = Z.y + Z.h / 2, A = Math.min(34, Z.h * 0.07), N = 90;
  function yAt(x){ return mid + A * Math.sin((x - Z.x) / Z.w * Math.PI * n); }
  for (var i = 0; i <= N; i++) { var x = Z.x + Z.w * i / N; C.ell(x - 3, yAt(x) - 3, 6, 6, {fill:'#B9C2F5'}); }
  var F = K.fitHD(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), cw - 16, 1, Z.h / 2 - A - 110, {dMax:19, dMin:11});
  var ps = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - 40, 22, {font:'head', max:17, min:13, maxLines:1});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = mid + A * Math.sin((k + 0.5) * Math.PI), c = SER[k % 4], r = Math.min(52, cw * 0.22), up = k % 2 === 0;
    K.blob(C, cx + 4, cy + 6, r, c, 0.35, k + 3);
    K.blob(C, cx, cy, r, LP(c, 0.2), 1, k + 7);
    C.text(cx - r, cy - r, r * 2, r * 2, u.pad2(k + 1), {font:'head', size:Math.round(r * 0.5), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    var pw = Math.min(cw - 16, Math.max(84, (t.h || '').length * ps * 0.7 + 34)), dh = descH(C, t, cw - 16, F);
    var py = up ? cy - r - 30 - 36 - dh : cy + r + 22;
    K.pill(C, cx - pw / 2, py, pw, 34, t.h || u.pad2(k + 1), {fill:'#FFFFFF', line:LP(c, 0.2), lw:2, color:NAVY, size:ps, wt:800, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(Z.x + cw * k + 8, py + 44, cw - 16, dh + 4, t.d, {font:'body', size:F.ds, color:P.text, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 8. rounded white tiles with a pastel number blob */
function softTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : Math.ceil(n / 2))), rows = Math.ceil(n / cols);
  var gx = 22, gyy = 22, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gyy * (rows - 1)) / rows, pad = 24, br = rows > 1 ? 24 : 32;
  var H2 = hasH(it), hw = cw - pad * 2 - br * 2 - 14, zy = Z.y;
  if (!H2 && ch > 150) { zy = Z.y + (ch - 150) * rows / 2 * 0.8; ch = 150; }
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), hw, br * 2, {font:'head', max:rows > 1 ? 19 : 22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - pad * 2, ch - pad * 2 - br * 2 - 16, {font:'body', max:rows > 1 ? 16 : 19, min:11, lh:1.38});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + gx) + (cols - inRow) * (cw + gx) / 2, y = zy + r * (ch + gyy), c = SER[k % 4];
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:26, shadow:0.06});
    C.ell(x + cw - 64, y + ch - 50, 44, 44, {fill:LP(c, 0.6)}); C.ell(x + cw - 40, y + ch - 70, 22, 22, {fill:LP(c, 0.75)});
    K.blob(C, x + pad + br, y + pad + br, br, LP(c, 0.35), 1, k + 1);
    C.text(x + pad, y + pad, br * 2, br * 2, u.pad2(k + 1), {font:'head', size:Math.round(br * 0.6), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad + br * 2 + 14, y + pad, hw, br * 2, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    if (t.h) C.text(x + pad, y + pad + br * 2 + 16, cw - pad * 2 - 30, ch - pad * 2 - br * 2 - 16, t.d, {font:'body', size:ds, color:P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 9. statement on a soft double blob */
function blobStatement(C, Z, o){
  var P = C.P, t = C.it[0], cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r = Math.min(Z.h * 0.62, Z.w * 0.42);
  K.blob(C, cx - r * 0.25, cy, r, '#5FD6B4', 0.22, 2); K.blob(C, cx + r * 0.3, cy + 10, r * 0.95, '#A081EE', 0.22, 5);
  var w = Math.min(Z.w - 80, r * 1.9), x = cx - w / 2;
  if (!t) { C.text(x, Z.y, w, Z.h, C.s.subtitle || '', {font:'head', fit:{max:36, min:18}, color:NAVY, align:'c', va:'m', lh:1.3, ed:{f:'subtitle'}}); return; }
  if (t.h) K.pill(C, cx - Math.min(w, t.h.length * 11 + 60) / 2, Z.y + 24, Math.min(w, t.h.length * 11 + 60), 36, t.h, {fill:'#FFFFFF', color:'#7F4FE0', size:14, wt:800, caps:true, ls:0.1, ed:{f:'x-part', i:t.i, p:0}});
  C.text(x, Z.y + (t.h ? 80 : 30), w, Z.h - (t.h ? 110 : 60), t.d, {font:'head', fit:{max:36, min:18}, color:NAVY, align:'c', va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'pastel', name:'Mint & Lilac', tag:'Soft pastel · marketing', desc:'Airy pages with mint, aqua, periwinkle and lilac pastels, rounded cards, soft 3D coils and navy roadmaps.',
  fonts:{head:'Plus Jakarta Sans', body:'Plus Jakarta Sans', headW:800, bodyW:500, headLh:1.06, gf:'Plus Jakarta Sans:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Pastel coil',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Pastel coil',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Pill rows',{body:pillRows}), V('n2','Soft tiles',{body:softTiles}), V('v2','Pill rows + image right',{body:pillRows, img:'right'}), V('n3','Numbered tiles',{body:'agTiles'})],
    text:[V('v1','Blob statement',{body:blobStatement}), V('n2','Statement card',{body:'text', box:true}), V('v2','Blob statement + image right',{body:blobStatement, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Venn circles',{body:vennDuo}), V('n2','Soft blobs',{body:blobCluster}), V('n3','Pill rows',{body:pillRows}), V('v2','Image left + pill rows',{body:pillRows, img:'left'}), V('v3','Tiles + image right',{body:softTiles, img:'right'})],
    p3:[V('v1','Soft blobs',{body:blobCluster}), V('n2','Loop track',{body:loopTiles}), V('n3','Pill rows',{body:pillRows}), V('n4','Soft tiles',{body:softTiles}), V('n5','Cycle',{body:'xCycle'}), V('v2','Image left + pill rows',{body:pillRows, img:'left'}), V('v3','Tiles + image right',{body:softTiles, img:'right'})],
    p4:[V('v1','Loop track',{body:loopTiles}), V('n2','Soft blobs',{body:blobCluster}), V('n3','Soft tiles',{body:softTiles}), V('n4','Pill rows',{body:pillRows}), V('n5','Cycle',{body:'xCycle'}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    p56:[V('v1','Loop track',{body:loopTiles}), V('n2','Soft tiles',{body:softTiles}), V('n3','Pill rows',{body:pillRows}), V('n4','Cycle',{body:'xCycle'}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    p78:[V('v1','Soft tiles',{body:softTiles}), V('n2','Pill rows',{body:pillRows}), V('n3','Loop track',{body:loopTiles}), V('n4','Cycle',{body:'xCycle'}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    stats:[V('v1','Bubble chain',{body:bubbleStats}), V('n2','Stat pills',{body:pillStats}), V('n3','Progress rings',{body:'statRings'}), V('v2','Image left + pills',{body:pillStats, img:'left'}), V('v3','Bubbles + image right',{body:bubbleStats, img:'right'})],
    timeline:[V('v1','Stepping stones',{body:stonePath}), V('n2','Pill rows',{body:pillRows}), V('n3','Loop track',{body:loopTiles}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    quote:[V('v1','Soft card quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

