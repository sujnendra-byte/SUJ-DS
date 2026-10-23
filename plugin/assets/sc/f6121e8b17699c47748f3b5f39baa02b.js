
/* DesignStager template: sunsetglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 35 - Sunset Glass : warm peach-to-rose light page, clear glass discs and ribbon cards, serif headings */
var PLUM = '#3A1D3F', BODY = '#6B4A63', COR = '#F0603F', ROSE = '#E8497C', AMB = '#F5A524', VIO = '#8B5CF6';
var RIB = [COR, ROSE, AMB, VIO, '#2EB6A8'];
var P0 = {bg:'#FFF1E8', text:PLUM, title:PLUM, muted:BODY, accent:COR, accent2:ROSE, card:'#FFFFFF', line:'#F2CFC2', onAccent:'#FFFFFF'};
var S = {
  margin:74, series:[COR, ROSE, AMB, VIO, '#2EB6A8', '#F7B79F'],
  header:{kind:'left', top:66, tMax:48, tMin:26, sMax:17, sMin:13, bar:true, barW:54, barH:4, barC:COR, barR:2, barGap:22},
  card:{kind:'glass', r:20, fill:'#FFFFFF', op:0.55, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.07, dark:false, tc:PLUM, mc:BODY,
        hl:{kind:'grad', grad:{ang:135, stops:[[0, '#F7785A'], [100, '#E8497C']]}, dark:true, tc:'#FFFFFF', mc:'#FFE9E2'}},
  icon:{kind:'soft', bg:COR, fg:COR, op:0.15}, num:{kind:'circle', bg:COR, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#FFFFFF', op:0.7, line:'#FFFFFF', lw:1.5, label:'Takeaway', lc:COR, tc:PLUM},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#FFC7A8'], [100, '#F28BA8']]}}
};
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 20;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:135, stops:[[0, '#F7785A'], [100, '#E8497C']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.3], [50, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#FFE9E2', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.88], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.06});
  return {tc:PLUM, mc:BODY, ac:COR};
}
/* a clear glass disc: pale fill, bright rim, soft inner highlight */
function disc(C, cx, cy, r, k, o){
  o = o || {}; var c = RIB[((k % RIB.length) + RIB.length) % RIB.length];
  D.glow(C, cx, cy + r * 0.2, r * 1.25, r * 1.1, c, 0.2);
  C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.92], [100, '#FFFFFF', 0.35]]}, line:'#FFFFFF', lw:2, lop:1});
  C.ell(cx - r * 0.86, cy - r * 0.86, r * 1.72, r * 1.72, {line:c, lw:o.lw || 2, lop:0.55});
  C.ell(cx - r * 0.55, cy - r * 0.82, r * 0.8, r * 0.36, {fill:'#FFFFFF', op:0.7});
}
function sun(C, cx, cy, r){
  D.glow(C, cx, cy, r * 2.1, r * 2.1, '#FFB36B', 0.55);
  C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:160, stops:[[0, '#FFD08A'], [55, '#F7785A'], [100, '#E8497C']]}});
  C.ell(cx - r * 0.6, cy - r * 0.8, r * 0.9, r * 0.42, {fill:'#FFFFFF', op:0.35});
}
function foot(C){
  var M = 74, y = H - 40, f = C.cover;
  if (f.company) C.text(M, y, 420, 18, f.company, {font:'body', size:12, wt:700, color:PLUM, lh:1, va:'m'});
  C.ell(W - M - 30, y - 6, 30, 30, {grad:{ang:150, stops:[[0, '#FFFFFF', 0.92], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lw:1.5});
  C.text(W - M - 30, y - 6, 30, 30, String(C.i + 1), {font:'body', size:12, wt:700, color:COR, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:155, stops:[[0, '#FFF4EA'], [55, '#FFE3D6'], [100, '#F9D3E0']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.95, W * 0.05, W * 0.5][k], H * 0.0, 520, 340, '#FFC38F', 0.75);
  D.glow(C, [W * 0.0, W * 1.0, W * 0.05][k], H * 1.0, 500, 330, '#F6A5C1', 0.65);
  D.glow(C, [W * 0.6, W * 0.35, W * 0.95][k], H * 0.6, 340, 240, '#D9C2FF', 0.45);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'right' && im !== 'top') { sun(C, W - 100, 74, 22); disc(C, W - 150, 108, 15, C.i + 1, {lw:1.5}); }
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* glass cards with a coloured ribbon down the left edge */
function ribbonCards(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 20, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 26;
  var F = K.fitHD(C, it, cw - pad * 2 - 10, 54, ch - pad * 2 - 30, {hMax:20, dMax:15.5, hMin:13, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2 - 10, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 36), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), cc = glass(C, x, y, cw, ch2, {hot:o.hl && k === 0}), col = RIB[k % RIB.length];
    if (!(o.hl && k === 0)) C.rect(x + 12, y + 22, 5, ch2 - 44, {fill:col, r:3});
    C.text(x + pad + 6, y + pad - 4, 80, 24, pad2(k + 1), {font:'head', size:20, wt:600, color:o.hl && k === 0 ? '#FFFFFF' : col, lh:1, caps:false, ls:0, italic:true});
    K.hd(C, x + pad + 6, y + pad + 30, cw - pad * 2 - 10, ch2 - pad * 2 - 30, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* glass discs in a row: heading inside the disc, explanation under it */
function discRow(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  if (Z.w < 720 && n > 2) return ribbonCards(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.42, Z.h * 0.3, 130), cy = Z.y + r + 8, pad = 16;
  var hs = C.groupFit(it.map(function(t, k){ return t.h || pad2(k + 1); }), r * 1.36, r * 0.9, {font:'head', max:22, min:12, maxLines:3});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - r * 2 - 40, {font:'body', max:n <= 3 ? 17 : 15, min:10.5, lh:1.4});
  if (n > 1) C.rect(Z.x + cw / 2, cy - 1, Z.w - cw, 2, {fill:'#FFFFFF'});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    disc(C, cx, cy, r, k);
    C.text(cx - r * 0.68, cy - r * 0.5, r * 1.36, r, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:PLUM, align:'c', va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:3});
    C.text(cx - cw / 2 + pad, cy + r + 24, cw - pad * 2, Z.y + Z.h - cy - r - 24, t.d, {font:'body', size:ds, color:BODY, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* editorial list: serif numerals, hairlines, no cards */
function editorial(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 720 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), tw = cw - 76;
  var F = K.fitHD(C, it, tw, 34, rh - 26, {hMax:21, dMax:15.5, hMin:13, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(12, (rh - bh) / 2);
    C.text(x, y, 60, rh, pad2(k + 1), {font:'head', size:Math.min(40, rh * 0.42), wt:600, color:RIB[k % RIB.length], va:'m', lh:1, caps:false, ls:0, italic:true});
    K.hd(C, x + 76, y + oy, tw, rh - oy - 4, t, F, {tc:PLUM, mc:BODY});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:PLUM, op:0.14});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: glass columns whose height follows the value */
function columns(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  if (Z.w < 640 && n > 2) return discStats(C, Z, o);
  var g = 22, cw = (Z.w - g * (n - 1)) / n, vals = st.map(sv), labs = st.map(sl), labH = 86, maxH = Z.h - labH, pad = 18;
  var vs = C.groupFit(vals, cw - pad * 2, 70, {font:'head', max:58, min:20, maxLines:1});
  var ls = C.groupFit(labs.map(function(l, k){ return l; }), cw - 8, 44, {font:'body', max:15.5, min:11, lh:1.3, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 8, labH - 44, {font:'body', max:13, min:10, lh:1.35});
  C.rect(Z.x, Z.y + maxH, Z.w, 2, {fill:PLUM, op:0.25});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + g), h = Math.max(vs * 1.5 + pad, maxH * (0.38 + 0.62 * pct(vals[k], k) / 100)), y = Z.y + maxH - h, col = RIB[k % RIB.length];
    C.rect(x, y, cw, h, {grad:{ang:180, stops:[[0, col, 0.9], [100, col, 0.25]]}, r:18});
    C.rect(x, y, cw, h, {grad:{ang:160, stops:[[0, '#FFFFFF', 0.45], [60, '#FFFFFF', 0.05]]}, line:'#FFFFFF', lw:1.5, lop:0.9, r:18});
    C.text(x + pad, y + pad, cw - pad * 2, vs * 1.2, vals[k], {font:'head', size:vs, wt:600, color:'#FFFFFF', lh:1, caps:false, ls:-0.01, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(x + 4, Z.y + maxH + 14, cw - 8, 40, labs[k], {font:'body', size:ls, wt:700, color:PLUM, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 4, Z.y + maxH + 56, cw - 8, labH - 56, t.x, {font:'body', size:ds, color:BODY, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* stats: value inside a glass disc */
function discStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, r = Math.min(cw * 0.36, ch * 0.33, 120), vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, r * 1.4, r * 0.7, {font:'head', max:46, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, 46, {font:'body', max:16.5, min:11.5, lh:1.3, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 40, ch - r * 2 - 90, {font:'body', max:14, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var rr = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + rr * ch, cx = x + cw / 2, cy = y + r + 6;
    disc(C, cx, cy, r, k, {lw:3});
    C.text(cx - r * 0.72, cy - r * 0.38, r * 1.44, r * 0.76, vals[k], {font:'head', size:vs, wt:600, color:RIB[k % RIB.length], align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = cy + r + 20, lh = C.measureText(labs[k], cw - 30, {font:'body', max:ls, min:ls, lh:1.3, maxLines:2}).h;
    C.text(x + 15, ly, cw - 30, lh + 4, labs[k], {font:'body', size:ls, wt:700, color:PLUM, align:'c', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 20, ly + lh + 8, cw - 40, y + ch - ly - lh - 8, t.x, {font:'body', size:ds, color:BODY, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: a rising path of glass discs with tags */
function rise(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / n, r = Math.min(34, slot * 0.2), lift = Math.min(Z.h * 0.34, 150), cw = slot - 14, pad = 6;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), r * 1.5, r * 0.9, {font:'head', max:17, min:10, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - lift - r * 2 - 30, {font:'body', max:n <= 4 ? 16 : 14.5, min:10.5, lh:1.4});
  var pts = it.map(function(t, k){ return [Z.x + slot * (k + 0.5), Z.y + r + 6 + lift * (1 - (n > 1 ? k / (n - 1) : 0))]; });
  if (n > 1) K.curve(C, pts, {stroke:COR, lw:2.5, lop:0.55});
  it.forEach(function(t, k){
    var cx = pts[k][0], cy = pts[k][1];
    disc(C, cx, cy, r, k, {lw:2});
    C.text(cx - r * 0.8, cy - r * 0.5, r * 1.6, r, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, color:PLUM, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(cx - cw / 2 + pad, cy + r + 16, cw - pad * 2, Z.y + Z.h - cy - r - 16, t.d, {font:'body', size:ds, color:BODY, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {r:28}), pad = 48, y = Z.y + pad, w = Z.w - pad * 2 - (o.img ? 0 : 160);
  if (!o.img) { sun(C, Z.x + Z.w - 100, Z.y + Z.h - 96, 62); disc(C, Z.x + Z.w - 190, Z.y + Z.h - 50, 30, 3); }
  if (it.h) { var he = C.text(Z.x + pad, y, w, 76, it.h, {font:'head', fit:{max:30, min:17}, wt:600, color:COR, lh:1.15, italic:true, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:PLUM, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function scene(C, cx, cy, s){ sun(C, cx, cy, 130 * s); disc(C, cx + 150 * s, cy + 110 * s, 78 * s, 1, {lw:3}); disc(C, cx - 160 * s, cy - 90 * s, 50 * s, 3, {lw:2.5}); disc(C, cx - 70 * s, cy + 170 * s, 30 * s, 2); disc(C, cx + 160 * s, cy - 150 * s, 22 * s, 4); }
function brand(C, x, y){ var f = C.cover; if (!f.company) return; sun(C, x + 11, y + 12, 9); C.text(x + 32, y, 420, 24, f.company, {font:'body', size:15, wt:700, color:PLUM, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  brand(C, 74, 52);
  if (f.date) C.text(W - 374, 52, 300, 24, f.date, {font:'body', size:13, wt:600, color:BODY, align:'r', va:'m', lh:1});
  if (photo) { D.imgBox(C, 730, 110, 476, 500, {shape:'ell'}); C.ell(718, 98, 500, 524, {line:'#FFFFFF', lw:3}); sun(C, 760, 560, 44); disc(C, 1180, 150, 30, 3); }
  else scene(C, 960, 360, 1);
  C.text(74, 170, 620, 250, C.s.title, {font:'head', fit:{max:78, min:32}, wt:600, color:PLUM, ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  C.rect(74, 440, 70, 4, {fill:COR, r:2});
  if (C.s.subtitle) C.text(74, 464, 560, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(560, who.length * 8.4 + 48); glass(C, 74, 570, pw, 46, {r:23}); C.text(74 + 22, 570, pw - 44, 46, who, {font:'body', size:14, wt:700, color:PLUM, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 74, 52);
  if (photo) { D.imgBox(C, 730, 110, 476, 500, {shape:'ell'}); C.ell(718, 98, 500, 524, {line:'#FFFFFF', lw:3}); sun(C, 1170, 560, 40); }
  else scene(C, 980, 360, 0.92);
  C.text(74, 120, 620, 210, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:36}, wt:600, color:PLUM, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(74, 346, 580, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  glass(C, 74, 440, 600, 30 + cs.length * 56, {r:24});
  cs.forEach(function(c, k){
    var y = 455 + k * 56;
    C.ell(74 + 22, y + 8, 40, 40, {fill:RIB[k % RIB.length]}); C.icon(74 + 32, y + 18, 20, c.icon, '#FFFFFF');
    C.text(74 + 78, y, 500, 56, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:PLUM, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(74, 140, 430, 240, C.s.title || 'Agenda', {font:'head', fit:{max:80, min:36}, wt:600, color:PLUM, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02});
  C.rect(74, 140 + te._h + 18, 70, 4, {fill:COR, r:2});
  if (C.s.subtitle) C.text(74, 140 + te._h + 42, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  sun(C, 150, 590, 56); disc(C, 240, 630, 32, 3);
  glass(C, 540, 70, W - 540 - 74, H - 150, {r:28});
  editorial(C, {x:580, y:90, w:W - 580 - 114, h:H - 190}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:COR, tc:PLUM}); return; }
  var photo = o.img === 'photo', x = 74, w = photo ? 620 : W - 148 - 220;
  if (photo) { D.imgBox(C, 760, 120, 440, 480, {shape:'ell'}); C.ell(748, 108, 464, 504, {line:'#FFFFFF', lw:3}); }
  else { sun(C, W - 190, 200, 70); disc(C, W - 120, 330, 40, 3); }
  C.text(x, 110, 120, 110, '“', {font:'head', size:160, wt:600, color:COR, lh:1, va:'t', caps:false, ls:0, italic:true});
  var qe = C.text(x, 220, w, 330, C.s.title, {font:'head', fit:{max:photo ? 38 : 46, min:19}, wt:600, color:PLUM, lh:1.24, italic:true, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 220 + qe._h + 28, 50, 3, {fill:COR, r:2}); C.text(x, 220 + qe._h + 44, w, 24, bt, {font:'body', size:15, wt:700, color:BODY, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'sunsetglass', name:'Sunset Glass', tag:'Light · warm glass',
  desc:'Warm peach-to-rose pages with clear glass discs, ribbon glass cards, a glowing sun motif and serif headings: a soft, human look for stories, proposals and group work.',
  fonts:{head:'Fraunces', body:'Nunito Sans', headW:600, bodyW:400, headLh:1.1, gf:'Fraunces:ital,wght@0,500;0,600;0,700;1,500;1,600|Nunito Sans:wght@400;600;700;800'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Sun + glass discs', {}), V('v2', 'Round photo', {img:'photo'})],
    closing:[V('v1', 'Sun + contact glass', {}), V('v2', 'Round photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + editorial list', {fn:agendaA}), V('n2', 'Ribbon cards', {body:ribbonCards}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass discs', {body:discRow}), V('n2', 'Ribbon cards', {body:ribbonCards, hl:true}), V('n3', 'Editorial list', {body:editorial, cols:1}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + ribbon cards', {body:ribbonCards, img:'left', cols:1})],
    p3:[V('v1', 'Glass discs', {body:discRow}), V('n2', 'Ribbon cards', {body:ribbonCards}), V('n3', 'Editorial list', {body:editorial, cols:1}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + ribbon cards', {body:ribbonCards, img:'left', cols:1})],
    p4:[V('v1', 'Ribbon cards', {body:ribbonCards}), V('n2', 'Glass discs', {body:discRow}), V('n3', 'Editorial list', {body:editorial}), V('n4', 'Ribbon cards in a row', {body:ribbonCards, cols:4}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + ribbon cards', {body:ribbonCards, img:'left', cols:2})],
    p56:[V('v1', 'Ribbon cards', {body:ribbonCards}), V('n2', 'Editorial list', {body:editorial}), V('n3', 'Glass discs', {body:discRow}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1}), V('v3', 'Image left + editorial list', {body:editorial, img:'left', cols:1})],
    p78:[V('v1', 'Ribbon cards', {body:ribbonCards}), V('n2', 'Editorial list', {body:editorial}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1})],
    stats:[V('v1', 'Glass columns', {body:columns}), V('n2', 'Glass disc numbers', {body:discStats}), V('n3', 'Progress rings', {body:'statRings', track:'#F6D9CF'}), V('v2', 'Disc numbers + image right', {body:discStats, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Rising discs', {body:rise}), V('n2', 'Ribbon cards', {body:ribbonCards}), V('n3', 'Editorial list', {body:editorial}), V('v2', 'Editorial list + image right', {body:editorial, img:'right', cols:1})],
    quote:[V('v1', 'Serif quote + sun', {}), V('v2', 'Quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

