
/* DesignStager template: circuittide */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 33 - Circuit Tide : deep teal, thin circuit traces with ring nodes, teal glass panels, gauge stats */
var AQ = '#5FF0E0', BL = '#8FC4FF', MUTE = '#A9C9D4', INK = '#051821';
var BG = {ang:140, stops:[[0, '#051821'], [50, '#0A3040'], [100, '#12586A']]};
var P0 = {bg:'#0A3040', text:'#FFFFFF', title:'#FFFFFF', muted:MUTE, accent:AQ, accent2:BL, card:'#11465A', line:'#2F6F82', onAccent:INK};
var S = {
  margin:72, series:[AQ, BL, '#B6F7EE', '#3FA9C9', '#DFF7FF', '#2DD4BF'],
  header:{kind:'left', top:70, tMax:42, tMin:24, sMax:17, sMin:13, bar:true, barW:46, barH:3, barC:AQ, barGap:20},
  card:{kind:'glass', r:14, fill:'#FFFFFF', op:0.1, line:AQ, lop:0.35, lw:1.2, dark:true, tc:'#FFFFFF', mc:MUTE,
        hl:{kind:'solid', fill:AQ, dark:false, tc:INK, mc:'#12404C'}},
  icon:{kind:'ring', bg:AQ, fg:AQ}, num:{kind:'ring', bg:AQ, fg:AQ},
  conc:{kind:'box', fill:'#FFFFFF', op:0.08, line:AQ, lw:1, r:12, label:'Takeaway', lc:AQ, tc:'#FFFFFF'},
  footer:{fn:foot}, imgFramed:true, imgR:14, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#1C6E80'], [100, '#072733']]}}
};
/* a circuit trace: straight runs with right-angle turns, a ring node at the end */
function trace(C, pts, o){
  o = o || {}; var d = pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; });
  C.path(d, {stroke:o.color || AQ, lw:o.lw || 1.3, lop:o.op != null ? o.op : 0.55});
  var e = pts[pts.length - 1], s = pts[0];
  if (o.end !== false) C.ell(e[0] - 4.5, e[1] - 4.5, 9, 9, {fill:INK, line:o.color || AQ, lw:1.5, lop:o.op != null ? Math.min(1, o.op + 0.3) : 0.9});
  if (o.start) C.ell(s[0] - 3, s[1] - 3, 6, 6, {fill:o.color || AQ, op:o.op != null ? o.op : 0.8});
}
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 14;
  if (o.aq) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#8FF7EA'], [100, '#39C9D6']]}, r:r}); return {tc:INK, mc:'#12404C', ac:INK}; }
  C.rect(x, y, w, h, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.2], [60, '#FFFFFF', 0.07], [100, '#FFFFFF', 0.11]]}, line:'#FFFFFF', lop:0.3, lw:1.2, r:r});
  return {tc:'#FFFFFF', mc:MUTE, ac:AQ};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  trace(C, [[M, y + 9], [M + 26, y + 9]], {start:true, op:0.6});
  if (f.company) C.text(M + 42, y, 420, 18, f.company, {font:'body', size:11.5, wt:600, color:MUTE, lh:1, va:'m', caps:true, ls:0.12});
  C.text(W - M - 80, y, 80, 18, pad2(C.i + 1), {font:'head', size:13, wt:600, color:AQ, align:'r', lh:1, va:'m', caps:false, ls:0.08});
}
function bg(C, kind){
  C.bg({grad:BG});
  var odd = (C.i || 0) % 2;
  D.glow(C, odd ? W * 0.1 : W * 0.88, H * 0.3, 460, 300, '#3FD6D0', 0.22);
  D.glow(C, odd ? W * 0.9 : W * 0.15, H * 1.0, 480, 260, '#03121A', 0.5);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') { trace(C, [[W, 44], [W - 150, 44], [W - 176, 70], [W - 230, 70]], {op:0.5}); trace(C, [[W, 58], [W - 96, 58]], {op:0.35}); }
  if (im !== 'left' && im !== 'bottom') trace(C, [[0, H - 86], [34, H - 86], [50, H - 70]], {op:0.4});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* cards hanging from a shared bus line */
function busCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), gx = 18, gy = 44, top = 26, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - top - gy * (rows - 1)) / rows, pad = 22;
  var F = K.fitHD(C, it, cw - pad * 2, 54, ch - pad * 2, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 6);
  for (var r = 0; r < rows; r++) {
    var by = Z.y + r * (ch2 + gy) + 6, cnt = Math.min(cols, n - r * cols);
    C.path([['M', Z.x, by], ['L', Z.x + (cnt - 1) * (cw + gx) + cw / 2, by]], {stroke:AQ, lw:1.3, lop:0.55});
    C.ell(Z.x - 3, by - 3, 6, 6, {fill:AQ});
  }
  it.forEach(function(t, k){
    var r2 = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), by2 = Z.y + r2 * (ch2 + gy) + 6, y = by2 + top - 6, cc = glass(C, x, y, cw, ch2, {aq:o.hl && k === 0});
    C.line(x + cw / 2, by2, x + cw / 2, y, {color:AQ, lw:1.3, op:0.55});
    C.ell(x + cw / 2 - 5, by2 - 5, 10, 10, {fill:INK, line:AQ, lw:1.6});
    K.hd(C, x + pad, y + pad, cw - pad * 2, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* spec sheet: heading pill on the left, a trace to the explanation on the right */
function specRows(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var hasH = it.some(function(t){ return t.h; }), gy = 12, rh = Math.min((Z.h - gy * (n - 1)) / n, 120), pw = hasH ? Math.min(300, Z.w * 0.3) : 64, tx = Z.x + pw + 62, tw = Z.w - pw - 62;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), pw - 36, rh - 16, {font:'head', max:17, min:11.5, maxLines:2}) : 16;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 8, {font:'body', max:n <= 3 ? 18 : 15.5, min:10.5, lh:1.38});
  var tot = rh * n + gy * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.4);
  it.forEach(function(t, k){
    var y = y0 + k * (rh + gy), ph = Math.min(rh, 64), py = y + (rh - ph) / 2;
    glass(C, Z.x, py, pw, ph, {r:ph / 2, aq:o.hl && k === 0});
    C.text(Z.x + 18, py, pw - 36, ph, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:o.hl && k === 0 ? INK : '#FFFFFF', align:'c', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    trace(C, [[Z.x + pw, y + rh / 2], [tx - 16, y + rh / 2]], {op:0.6});
    C.text(tx, y, tw, rh, t.d, {font:'body', size:ds, color:'#E6F4F7', va:'m', lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* one glass panel cut into cells, icon on top of each */
function cells(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 24, ic = rows > 1 ? 34 : 44;
  glass(C, Z.x, Z.y, Z.w, Z.h, {});
  var F = K.fitHD(C, it, cw - pad * 2, 50, ch - pad * 2 - ic - 14, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * ch, bh = blockH(C, t, cw - pad * 2, F), oy = Math.max(pad, (ch - ic - 14 - bh) / 2);
    if (c) C.rect(x - 0.5, y + 18, 1, ch - 36, {fill:'#FFFFFF', op:0.18});
    if (r && !c) C.rect(Z.x + 18, y - 0.5, Z.w - 36, 1, {fill:'#FFFFFF', op:0.18});
    C.icon(x + (cw - ic) / 2, y + oy, ic, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), AQ);
    K.hd(C, x + pad, y + oy + ic + 14, cw - pad * 2, ch - oy - ic - 14 - 10, t, F, {tc:'#FFFFFF', mc:MUTE, align:'c'});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: half-ring gauges */
function gauges(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), gx = 20, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var r = Math.min(cw * 0.36, ch * 0.42, 120), vals = st.map(sv), labs = st.map(sl), pad = 16;
  var vs = C.groupFit(vals, r * 1.3, r * 0.5, {font:'head', max:42, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - pad * 2, 46, {font:'head', max:17, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - pad * 2, ch - r - 110, {font:'body', max:14.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var rr = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + rr * (ch + gy), cx = x + cw / 2, cy = y + r + 12, p = pct(vals[k], k);
    D.arc(C, cx, cy, r, 180, 360, {color:'#FFFFFF', lw:12, op:0.14});
    D.arc(C, cx, cy, r, 180, 180 + 180 * p / 100, {color:k % 2 ? BL : AQ, lw:12});
    var ex = cx + r * Math.cos((180 + 180 * p / 100) * Math.PI / 180), ey = cy + r * Math.sin((180 + 180 * p / 100) * Math.PI / 180);
    C.ell(ex - 8, ey - 8, 16, 16, {fill:INK, line:'#FFFFFF', lw:2});
    C.text(cx - r * 0.75, cy - r * 0.52, r * 1.5, r * 0.5, vals[k], {font:'head', size:vs, wt:600, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = cy + 18, lh = C.measureText(labs[k], cw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + pad, ly, cw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:600, color:AQ, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, ly + lh + 8, cw - pad * 2, y + ch - ly - lh - 8, t.x, {font:'body', size:ds, color:MUTE, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a stepped trace, glass tags alternating above and below */
function stepLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / n, my = Z.y + Z.h / 2, amp = 22, cw = Math.min(slot * 1.6 - 14, 320), ch = Z.h / 2 - 54, pad = 16;
  if (n <= 3) cw = slot - 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2, 30, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - hs * 1.2 - 8, {font:'body', max:15.5, min:10.5, lh:1.38});
  var d = [['M', Z.x, my + amp]];
  it.forEach(function(t, k){ var x0 = Z.x + slot * k, x1 = x0 + slot, yy = my + (k % 2 ? -amp : amp); d.push(['L', x0 + slot * 0.3, yy]); d.push(['L', x1 - slot * 0.3, yy]); if (k < n - 1) d.push(['L', x1 + slot * 0.0, my + ((k + 1) % 2 ? -amp : amp)]); else d.push(['L', x1, yy]); });
  C.path(d, {stroke:AQ, lw:1.6, lop:0.7});
  it.forEach(function(t, k){
    var cx = Z.x + slot * (k + 0.5), yy = my + (k % 2 ? -amp : amp), up = n <= 3 ? false : k % 2 === 1, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw / 2));
    var dh = C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(ch, pad * 2 + hs * 1.2 + 8 + dh + 4), y = up ? yy - 26 - hh : yy + 26;
    C.line(cx, yy, cx, up ? y + hh : y, {color:AQ, lw:1.2, op:0.6});
    C.ell(cx - 8, yy - 8, 16, 16, {fill:INK, line:AQ, lw:2}); C.ell(cx - 3, yy - 3, 6, 6, {fill:AQ});
    var cc = glass(C, x, y, cw, hh, {});
    C.text(x + pad, y + pad, cw - pad * 2, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:AQ, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + pad, y + pad + hs * 1.2 + 8, cw - pad * 2, hh - pad * 2 - hs * 1.2 - 8, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
  trace(C, [[Z.x + Z.w - 30, Z.y + Z.h], [Z.x + Z.w - 30, Z.y + Z.h - 60], [Z.x + Z.w - 90, Z.y + Z.h - 120]], {op:0.5});
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:26, min:16}, wt:600, color:AQ, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:'#FFFFFF', lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function rings(C, cx, cy, s){
  [150, 122, 96, 70].forEach(function(r, k){ D.arc(C, cx, cy, r * s, 100 + k * 26, 380 + k * 14, {color:k % 2 ? BL : '#FFFFFF', lw:k === 0 ? 5 : 3, op:0.85 - k * 0.12}); });
  C.ell(cx - 26 * s, cy - 26 * s, 52 * s, 52 * s, {fill:AQ}); D.glow(C, cx, cy, 120 * s, 120 * s, AQ, 0.3);
}
function brand(C, x, y){ var f = C.cover; if (!f.company) return; C.ell(x, y + 3, 16, 16, {line:AQ, lw:2}); C.ell(x + 5, y + 8, 6, 6, {fill:AQ}); C.text(x + 28, y, 420, 22, f.company, {font:'head', size:15, wt:600, color:'#FFFFFF', va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 72, 52);
  trace(C, [[0, 150], [46, 150], [46, 250], [20, 276]], {op:0.5}); trace(C, [[W, 620], [W - 120, 620], [W - 150, 650], [W - 240, 650]], {op:0.5}); trace(C, [[W, 640], [W - 70, 640]], {op:0.35});
  if (photo) { D.imgBox(C, 730, 0, W - 730, H, {r:0}); C.rect(730, 0, 3, H, {fill:AQ}); trace(C, [[730, 520], [680, 520], [656, 544]], {op:0.8}); }
  else { rings(C, 1010, 330, 1.1); trace(C, [[800, 560], [880, 560], [910, 530], [990, 530]], {op:0.6}); }
  var tw = photo ? 600 : 640;
  C.text(72, 190, tw, 240, C.s.title, {font:'head', fit:{max:66, min:30}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'m', lh:1.1});
  if (C.s.subtitle) { glass(C, 72, 452, tw, 64, {r:32}); C.text(100, 452, tw - 56, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6F4F7', ed:{f:'subtitle'}, lh:1.35, va:'m', maxLines:2}); }
  var who = [f.presenter, f.designation, f.date].filter(Boolean).join('   ·   ');
  if (who) C.text(72, 560, tw, 22, who, {font:'body', size:13.5, wt:600, color:MUTE, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', tw = photo ? 600 : 640;
  brand(C, 72, 52);
  if (photo) { D.imgBox(C, 730, 0, W - 730, H, {r:0}); C.rect(730, 0, 3, H, {fill:AQ}); }
  else rings(C, 1010, 350, 1);
  glass(C, 72, 150, tw, 400, {r:18});
  C.text(110, 180, tw - 76, 110, C.s.title || 'Thank You', {font:'head', fit:{max:60, min:30}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'m', lh:1.08});
  if (C.s.subtitle) C.text(110, 300, tw - 76, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MUTE, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 384 + k * 50;
    C.ell(110, y + 6, 34, 34, {line:AQ, lw:1.6}); C.icon(119, y + 15, 16, c.icon, AQ);
    C.text(160, y, tw - 130, 46, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(72, 150, 400, 220, C.s.title || 'Agenda', {font:'head', fit:{max:60, min:30}, wt:600, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1.08});
  C.rect(72, 150 + te._h + 18, 46, 3, {fill:AQ});
  if (C.s.subtitle) C.text(72, 150 + te._h + 40, 380, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  rings(C, 190, 590, 0.6);
  specRows(C, {x:520, y:80, w:W - 520 - 72, h:H - 170}, {max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:AQ, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', x = photo ? 72 : 150, w = photo ? 610 : W - 300;
  if (photo) { D.imgBox(C, 730, 0, W - 730, H, {r:0}); C.rect(730, 0, 3, H, {fill:AQ}); }
  trace(C, [[x - 40, 200], [x - 40, 150], [x, 150]], {op:0.7, end:false}); trace(C, [[x + w + 40, H - 200], [x + w + 40, H - 150], [x + w, H - 150]], {op:0.7, end:false});
  C.text(x, 170, 90, 80, '“', {font:'head', size:110, wt:600, color:AQ, lh:1, va:'t', caps:false});
  var qe = C.text(x, 250, w, H - 250 - 220, C.s.title, {font:'head', fit:{max:photo ? 32 : 38, min:18}, wt:500, color:'#FFFFFF', lh:1.32, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 250 + qe._h + 26, 40, 3, {fill:AQ}); C.text(x, 250 + qe._h + 42, w, 24, bt, {font:'body', size:15, wt:600, color:MUTE, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'circuittide', name:'Circuit Tide', tag:'Tech · teal glass',
  desc:'Deep teal pages wired with thin circuit traces and ring nodes, teal glass panels, bus-line cards and gauge stats for technology, AI and engineering decks.',
  fonts:{head:'Exo 2', body:'Inter', headW:600, bodyW:400, headLh:1.1, headLs:0.03, gf:'Exo 2:wght@400;500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Signal rings', {}), V('v2', 'Photo panel', {img:'photo'})],
    closing:[V('v1', 'Glass panel + rings', {}), V('v2', 'Glass panel + photo', {img:'photo'})],
    agenda:[V('v1', 'Title + spec rows', {fn:agendaA}), V('n2', 'Bus cards', {body:busCards}), V('v2', 'Spec rows + image right', {body:specRows, img:'right', max:8})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Bus cards', {body:busCards, hl:true}), V('n2', 'Spec rows', {body:specRows, hl:true}), V('n3', 'Glass cells', {body:cells}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + bus cards', {body:busCards, img:'left', cols:1})],
    p3:[V('v1', 'Bus cards', {body:busCards}), V('n2', 'Glass cells', {body:cells}), V('n3', 'Spec rows', {body:specRows}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + spec rows', {body:specRows, img:'left'})],
    p4:[V('v1', 'Glass cells', {body:cells}), V('n2', 'Bus cards', {body:busCards}), V('n3', 'Spec rows', {body:specRows}), V('n4', 'Four cells in a row', {body:cells, cols:4}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + cells', {body:cells, img:'left', cols:2})],
    p56:[V('v1', 'Bus cards', {body:busCards}), V('n2', 'Glass cells', {body:cells}), V('n3', 'Spec rows', {body:specRows}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'}), V('v3', 'Image left + cells', {body:cells, img:'left', cols:2})],
    p78:[V('v1', 'Glass cells', {body:cells}), V('n2', 'Bus cards', {body:busCards}), V('v2', 'Cells + image right', {body:cells, img:'right', cols:2})],
    stats:[V('v1', 'Gauges', {body:gauges}), V('n2', 'Stat cards', {body:'statCards', icons:true}), V('n3', 'Big numbers', {body:'statCols', vc:AQ}), V('v2', 'Gauges + image right', {body:gauges, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Stepped trace', {body:stepLine}), V('n2', 'Bus cards', {body:busCards}), V('n3', 'Spec rows', {body:specRows}), V('v2', 'Spec rows + image right', {body:specRows, img:'right'})],
    quote:[V('v1', 'Bracketed quote', {}), V('v2', 'Quote + photo panel', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

