
/* DesignStager template: halotype */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 34 - Halo Type : oversized headings, giant faded page numerals, a curtain of light bars, white plates and charcoal tiles */
var EL = '#19B8FF', DEEPB = '#0A2A8C', INK = '#05070D', MUTE = '#B9C7DD', CH = '#141A26';
var P0 = {bg:'#05070D', text:'#FFFFFF', title:'#FFFFFF', muted:MUTE, accent:EL, accent2:'#6FE0FF', card:CH, line:'#2A3550', onAccent:INK};
var S = {
  margin:64, series:[EL, '#FFFFFF', '#6FE0FF', '#2D6BFF', '#BFEFFF', '#0FA3E0'],
  header:{kind:'left', top:54, tMax:64, tMin:30, sMax:17, sMin:13, gap:10, tH:140},
  card:{kind:'solid', r:6, fill:CH, op:0.92, line:'#FFFFFF', lop:0.12, lw:1, dark:true, tc:'#FFFFFF', mc:MUTE,
        hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:INK, mc:'#394357'}},
  icon:{kind:'plain', fg:EL}, num:{kind:'big', fg:EL},
  conc:{kind:'line', lineC:'#FFFFFF', label:'Takeaway', lc:EL, tc:'#FFFFFF'},
  footer:{fn:foot}, imgFramed:false, bodyGap:24,
  imgPh:{grad:{ang:135, stops:[[0, '#1F6FE0'], [100, '#07102A']]}}
};
function bars(C, x, w, top, h, seed){
  var n = Math.max(6, Math.round(w / 26)), bw = w / n;
  for (var k = 0; k < n; k++) { var t = Math.abs(Math.sin((k + 1) * 12.9898 + seed * 4.1)) , op = 0.05 + 0.3 * t * (1 - Math.abs(k / (n - 1) - 0.5) * 1.2);
    if (op > 0.02) C.rect(x + k * bw, top, bw * 0.62, h, {grad:{ang:180, stops:[[0, '#7FE3FF', 0], [45, '#7FE3FF', op], [100, '#1E5BFF', 0]]}}); }
}
function plus(C, x, y, s, color){ C.rect(x, y + s * 0.38, s, s * 0.24, {fill:color}); C.rect(x + s * 0.38, y, s * 0.24, s, {fill:color}); }
function foot(C){
  var M = 64, y = H - 36, f = C.cover;
  if (f.website || f.company) C.text(M, y, 420, 18, f.website || f.company, {font:'body', size:11.5, wt:600, color:MUTE, lh:1, va:'m'});
  plus(C, W - M - 12, y + 3, 12, EL);
}
function bg(C, kind){
  var k = (C.i || 0) % 4, light = kind === 'text' || kind === 'quote';
  C.bg({grad:{ang:[120, 200, 150, 60][k], stops:[[0, '#05070D'], [55, '#081A4A'], [100, light ? '#1D8FE6' : '#0E4FBF']]}});
  bars(C, [W * 0.45, 0, W * 0.2, W * 0.55][k], W * 0.55, 0, H, C.i || 0);
  D.glow(C, [W * 0.9, W * 0.1, W * 0.5, W * 0.85][k], H * 0.9, 460, 280, EL, 0.28);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img, n = pad2(C.i + 1);
  if (im !== 'right' && im !== 'top') C.text(W - 460, -40, 420, 300, n, {font:'head', size:300, wt:800, color:'#FFFFFF', op:0.07, align:'r', lh:1, va:'t', caps:false, ls:-0.04});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* white plates: plus-mark heading, black text */
function plates(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : Math.ceil(n / 2)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 14, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 22;
  var F = K.fitHD(C, it, cw - pad * 2 - 24, 52, ch - pad * 2, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, cw - pad * 2, F)); });
  var ch2 = Math.min(ch, bmx + pad * 2 + 8), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.25);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), dark = o.alt && (r + c) % 2 === 1;
    C.rect(x, y, cw, ch2, {fill:dark ? CH : '#FFFFFF', op:dark ? 0.92 : 0.96, r:6, line:dark ? '#FFFFFF' : null, lop:0.12});
    plus(C, x + pad, y + pad + 3, 14, EL);
    if (t.h) C.text(x + pad + 24, y + pad, cw - pad * 2 - 24, F.hh + 4, t.h, {font:'head', size:F.hs, color:dark ? '#FFFFFF' : INK, lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    var dy = y + pad + (t.h ? F.hh + 10 : 26);
    C.text(x + pad, dy, cw - pad * 2, y + ch2 - pad - dy + 6, t.d, {font:'body', size:F.ds, color:dark ? MUTE : '#2B3445', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* open plus list: no cards, plus marks, hairlines */
function plusList(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 720 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), tw = cw - 40;
  var F = K.fitHD(C, it, tw, 34, rh - 26, {hMax:22, dMax:15.5, hMin:13, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh;
    C.rect(x, y, cw, 1, {fill:'#FFFFFF', op:0.25});
    plus(C, x, y + 20, 16, EL);
    K.hd(C, x + 40, y + 16, tw, rh - 24, t, F, {tc:'#FFFFFF', mc:MUTE});
  });
}
/* poster split: first point huge on the left, the rest as charcoal tiles */
function poster(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 720) return plates(C, Z, o);
  var lw = Math.round(Z.w * 0.42), g = 14, rest = it.slice(1), m = rest.length, cols = m > 2 ? 2 : 1, rows = Math.ceil(m / cols), rw = (Z.w - lw - 30 - g * (cols - 1)) / cols, rh = (Z.h - g * (rows - 1)) / rows, pad = 22;
  var t0 = it[0];
  var he = C.text(Z.x, Z.y, lw, Z.h * 0.5, t0.h || t0.d, {font:'head', fit:{max:54, min:22}, wt:800, color:'#FFFFFF', lh:1.02, ed:{f:'x-part', i:t0.i, p:t0.h ? 0 : 1}, maxLines:4, va:'t'});
  C.rect(Z.x, Z.y + he._h + 18, 60, 6, {fill:EL});
  if (t0.h) C.text(Z.x, Z.y + he._h + 44, lw - 20, Z.h - he._h - 44, t0.d, {font:'body', fit:{max:18, min:12}, color:MUTE, lh:1.42, ed:{f:'x-part', i:t0.i, p:1}});
  var F = K.fitHD(C, rest, rw - pad * 2, 50, rh - pad * 2 - 30, {hMax:18, dMax:14.5, hMin:12.5, dMin:10.5, noBoost:true});
  rest.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + lw + 30 + c * (rw + g), y = Z.y + r * (rh + g), white = k === 0;
    C.rect(x, y, rw, rh, {fill:white ? '#FFFFFF' : CH, op:white ? 0.96 : 0.92, r:6, line:white ? null : '#FFFFFF', lop:0.12});
    C.text(x + pad, y + pad - 2, 80, 24, pad2(k + 2), {font:'head', size:18, wt:800, color:EL, lh:1, caps:false, ls:0});
    K.hd(C, x + pad, y + pad + 30, rw - pad * 2, rh - pad * 2 - 30, t, F, {tc:white ? INK : '#FFFFFF', mc:white ? '#2B3445' : MUTE});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function pct(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [78, 64, 86, 55][k % 4]; }
/* stats: thick rings, value inside */
function fatRings(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = Z.w < 640 && n > 2 ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, d = Math.min(cw * 0.62, ch * 0.62, 230), vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, d * 0.56, d * 0.3, {font:'head', max:44, min:14, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, 46, {font:'head', max:18, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 40, ch - d - 90, {font:'body', max:14.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * ch, cx = x + cw / 2;
    C.ring(cx - d / 2, y, d, pct(vals[k], k), {color:k % 2 ? '#FFFFFF' : EL, track:'#1B2C55', th:d * 0.16});
    C.text(cx - d * 0.3, y + d * 0.34, d * 0.6, d * 0.32, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ly = y + d + 16, lh = C.measureText(labs[k], cw - 30, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(x + 15, ly, cw - 30, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:'#FFFFFF', align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + 20, ly + lh + 8, cw - 40, y + ch - ly - lh - 8, t.x, {font:'body', size:ds, color:MUTE, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* stats: charcoal number tiles */
function numTiles(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n <= 2 || Z.w < 700 ? Math.min(n, 2) : (n === 4 ? 2 : 3), rows = Math.ceil(n / cols), g = 14, cw = (Z.w - g * (cols - 1)) / cols, ch = Math.min((Z.h - g * (rows - 1)) / rows, 260), pad = 24, vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, cw - pad * 2, ch * 0.42, {font:'head', max:72, min:22, maxLines:1});
  var ls = C.groupFit(labs.map(function(l, k){ return l + (st[k].x ? ' ' + st[k].x : ''); }), cw - pad * 2, ch * 0.34, {font:'body', max:15.5, min:10.5, lh:1.36});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), white = k === 0;
    C.rect(x, y, cw, ch, {fill:white ? '#FFFFFF' : CH, op:white ? 0.96 : 0.92, r:6, line:white ? null : '#FFFFFF', lop:0.12});
    C.text(x + pad, y + pad - 4, cw - pad * 2, ch * 0.46, vals[k], {font:'head', size:vs, wt:800, color:white ? DEEPB : '#FFFFFF', va:'m', lh:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x + pad, y + pad + ch * 0.46, 30, 3, {fill:EL});
    C.text(x + pad, y + pad + ch * 0.46 + 14, cw - pad * 2, ch - pad * 2 - ch * 0.46 - 12, labs[k], {font:'body', size:ls, wt:600, color:white ? '#2B3445' : MUTE, lh:1.36, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var y = Z.y;
  if (it.h) { plus(C, Z.x, y + 4, 16, EL); var he = C.text(Z.x + 30, y, Z.w - 30, 60, it.h, {font:'head', fit:{max:24, min:15}, wt:800, color:EL, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 22; }
  C.text(Z.x, y, Z.w * (o.img ? 1 : 0.86), Z.y + Z.h - y, it.d, {font:'head', fit:{max:it.h ? 40 : 46, min:16}, wt:700, color:'#FFFFFF', lh:1.2, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}, va:'t'});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo', yr = (String(f.date || '').match(/(20\d\d)/) || [])[1] || '';
  if (f.website || f.company) C.text(64, 44, 500, 20, f.website || f.company, {font:'body', size:12.5, wt:600, color:'#FFFFFF', lh:1, va:'m', ed:{f:f.website ? 'tf-website' : 'tf-company'}});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) C.text(W - 564, 44, 500, 20, 'Presented by ' + who, {font:'body', size:12.5, wt:600, color:'#FFFFFF', align:'r', lh:1, va:'m'});
  if (photo) D.imgBox(C, 700, 110, W - 700 - 64, 330, {r:6});
  else if (yr) C.text(W - 800, 70, 760, 280, yr, {font:'head', size:250, wt:800, color:'#FFFFFF', op:0.12, align:'r', lh:1, va:'t', caps:false, ls:-0.05, maxLines:1});
  if (C.s.subtitle) C.text(64, 120, photo ? 560 : 520, 150, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6F1FF', ed:{f:'subtitle'}, lh:1.45, maxLines:6, va:'t'});
  C.text(56, 330, W - 112 - (f.company ? 250 : 0), 330, C.s.title, {font:'head', fit:{max:150, min:44}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:0.94, ls:-0.03});
  if (f.company) { plus(C, W - 64 - 210, 600, 14, EL); C.text(W - 64 - 186, 594, 186, 60, f.company, {font:'body', size:13, wt:700, color:'#FFFFFF', caps:true, ls:0.3, lh:1.5, va:'t', ed:{f:'tf-company'}, maxLines:2}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo', cs = D.contacts(C), pw = 470, ph = Math.max(120, 36 + cs.length * 64);
  if (photo) D.imgBox(C, 64, 64, pw, 250, {r:6});
  C.rect(64, photo ? 340 : 110, pw, ph, {fill:'#FFFFFF', op:0.96, r:6});
  cs.forEach(function(c, k){
    var y = (photo ? 340 : 110) + 18 + k * 64;
    C.ell(64 + 24, y + 14, 36, 36, {line:DEEPB, lw:1.6}); C.icon(64 + 33, y + 23, 18, c.icon, DEEPB);
    C.text(64 + 78, y, pw - 100, 64, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
    if (k) C.rect(64 + 24, y, pw - 48, 1, {fill:'#C9D3E3'});
  });
  C.text(580, 250, W - 580 - 56, 400, C.s.title || 'Thank You', {font:'head', fit:{max:130, min:44}, wt:800, color:'#FFFFFF', align:'r', ed:{f:'title'}, maxLines:3, va:'b', lh:0.96, ls:-0.03});
  if (C.s.subtitle) C.text(580, 150, W - 580 - 64, 90, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#E6F1FF', align:'r', ed:{f:'subtitle'}, lh:1.45, maxLines:3, va:'b'});
}
function agendaA(C, o){
  C.text(56, 110, 520, 300, C.s.title || 'Agenda', {font:'head', fit:{max:120, min:44}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'t', lh:0.96, ls:-0.03});
  if (C.s.subtitle) C.text(64, 560, 440, 80, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3, va:'b'});
  plusList(C, {x:640, y:90, w:W - 640 - 64, h:H - 180}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:EL, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', w = photo ? 640 : W - 128;
  if (photo) D.imgBox(C, 760, 0, W - 760, H, {r:0});
  plus(C, 64, 110, 28, EL);
  var qe = C.text(64, 170, w, 360, C.s.title, {font:'head', fit:{max:photo ? 44 : 60, min:20}, wt:800, color:'#FFFFFF', lh:1.1, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(64, 170 + qe._h + 30, w, 1, {fill:'#FFFFFF', op:0.3}); C.text(64, 170 + qe._h + 46, w, 24, bt, {font:'body', size:15, wt:600, color:MUTE, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'halotype', name:'Halo Type', tag:'Big type · electric blue',
  desc:'Oversized headings, giant faded page numerals and a curtain of light bars on black-to-electric-blue pages, with white plates and charcoal tiles.',
  fonts:{head:'Archivo', body:'Inter', headW:800, bodyW:400, headLh:1.02, headLs:-0.02, gf:'Archivo:wght@500;600;700;800;900|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Giant title + year', {}), V('v2', 'Giant title + photo', {img:'photo'})],
    closing:[V('v1', 'Contact plate + giant title', {}), V('v2', 'Photo + contact plate', {img:'photo'})],
    agenda:[V('v1', 'Giant title + plus list', {fn:agendaA}), V('n2', 'White plates', {body:plates}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1})],
    text:[V('v1', 'Big statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'White plates', {body:plates}), V('n2', 'Poster split', {body:poster}), V('n3', 'Plus list', {body:plusList, cols:2}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:1})],
    p3:[V('v1', 'White plates', {body:plates}), V('n2', 'Poster split', {body:poster}), V('n3', 'Plus list', {body:plusList, cols:1}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plus list', {body:plusList, img:'left', cols:1})],
    p4:[V('v1', 'Poster split', {body:poster}), V('n2', 'Checker plates', {body:plates, cols:2, alt:true}), V('n3', 'Plus list', {body:plusList}), V('n4', 'White plates', {body:plates}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plates', {body:plates, img:'left', cols:2})],
    p56:[V('v1', 'Checker plates', {body:plates, alt:true}), V('n2', 'Plus list', {body:plusList}), V('n3', 'Poster split', {body:poster}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1}), V('v3', 'Image left + plus list', {body:plusList, img:'left', cols:1})],
    p78:[V('v1', 'Checker plates', {body:plates, alt:true}), V('n2', 'Plus list', {body:plusList}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1})],
    stats:[V('v1', 'Number tiles', {body:numTiles}), V('n2', 'Thick rings', {body:fatRings}), V('n3', 'Big numbers', {body:'statCols', vc:EL}), V('v2', 'Tiles + image right', {body:numTiles, img:'right'}), V('v3', 'Image left + rings', {body:fatRings, img:'left'})],
    timeline:[V('v1', 'Horizontal timeline', {body:'tlH'}), V('n2', 'White plates', {body:plates}), V('n3', 'Plus list', {body:plusList}), V('v2', 'Plus list + image right', {body:plusList, img:'right', cols:1})],
    quote:[V('v1', 'Big quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

