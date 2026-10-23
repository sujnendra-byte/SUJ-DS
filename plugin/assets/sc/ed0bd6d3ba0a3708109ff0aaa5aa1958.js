
/* DesignStager template: slatebrief */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 45 - Slate Brief : white consulting page, petrol ink, rust accent, tables, matrices and hairlines */
var PE = '#0F4C5C', RU = '#E36414', INK = '#1F2933', MUT = '#5B6770', HL = '#D5DBE0', TINT = '#EEF3F5';
var P0 = {bg:'#FFFFFF', text:INK, title:PE, muted:MUT, accent:PE, accent2:RU, card:TINT, line:HL, onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[PE, RU, '#5F9EA0', '#9FB4BC', '#F2A65A', '#1F2933'],
  header:{kind:'left', top:54, tMax:38, tMin:24, sMax:16, sMin:12.5, kicker:null, bar:true},
  card:{kind:'solid', r:2, fill:TINT, line:HL, lw:1, shadow:0, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:PE, dark:true, tc:'#FFFFFF', mc:'#D2E3E7'}},
  icon:{kind:'none', fg:PE}, num:{kind:'big', fg:PE},
  conc:{kind:'bar', barC:RU, label:'Implication', lc:RU, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:2, bodyGap:24,
  imgPh:{grad:{ang:135, stops:[[0, '#C9D8DD'], [100, '#8FA9B2']]}}
};
function foot(C){
  var M = 70, y = H - 34, f = C.cover;
  C.rect(M, y - 12, W - M * 2, 1, {fill:HL});
  C.text(M, y, 600, 16, [f.company, 'Confidential'].filter(Boolean).join('  |  '), {font:'body', size:10.5, wt:500, color:MUT, lh:1, va:'m'});
  C.text(W - M - 100, y, 100, 16, String(C.i + 1), {font:'body', size:11, wt:700, color:PE, align:'r', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  C.rect(0, 0, 14, H, {fill:PE}); C.rect(14, 0, 4, H, {fill:RU});
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* table: header row and ruled rows */
function table(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var hh = 38, rh = Math.min((Z.h - hh) / n, 110), hasH = it.some(function(t){ return t.h; }), nw = 56, hw = hasH ? Math.min(300, (Z.w - nw) * 0.34) : 0, tw = Z.w - nw - hw - 36;
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw - 16, rh - 16, {font:'head', max:19, min:12.5, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 16, {font:'body', max:hasH ? 15.5 : 20, min:10.5, lh:1.38});
  var tot = hh + rh * n, y0 = Z.y + Math.max(0, (Z.h - tot) * 0.25);
  C.rect(Z.x, y0, Z.w, hh, {fill:PE});
  C.text(Z.x + 16, y0, nw, hh, '#', {font:'body', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1});
  if (hasH) C.text(Z.x + nw, y0, hw, hh, 'Topic', {font:'body', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.12});
  C.text(Z.x + nw + hw + 18, y0, tw, hh, hasH ? 'Detail' : 'Item', {font:'body', size:11.5, wt:700, color:'#FFFFFF', va:'m', lh:1, caps:true, ls:0.12});
  it.forEach(function(t, k){
    var y = y0 + hh + k * rh;
    if (k % 2 === 0) C.rect(Z.x, y, Z.w, rh, {fill:TINT});
    C.text(Z.x + 16, y, nw - 16, rh, pad2(k + 1), {font:'body', size:12.5, wt:700, color:RU, va:'m', lh:1});
    if (hasH) C.text(Z.x + nw, y, hw - 16, rh, t.h || '', {font:'head', size:hs, wt:600, color:PE, va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + nw + hw + 18, y, tw, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:hasH ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
    C.rect(Z.x, y + rh - 0.5, Z.w, 1, {fill:HL});
  });
}
/* matrix: a 2x2 quadrant with axes */
function matrix(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4 || Z.w < 720 || Z.h < 300) return notes(C, Z, o);
  var ax = 30, x0 = Z.x + ax, w = Z.w - ax, h = Z.h - ax, cw = w / 2, ch = h / 2, pad = 24, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 18, {hMax:21, dMax:15, hMin:12.5, dMin:10.5});
  it.forEach(function(t, k){
    var c = k % 2, r = Math.floor(k / 2), x = x0 + c * cw, y = Z.y + r * ch, hot = k === 1;
    C.rect(x + 3, y + 3, cw - 6, ch - 6, {fill:hot ? PE : TINT});
    C.text(x + pad, y + pad - 4, 60, 18, 'Q' + (k + 1), {font:'body', size:11.5, wt:700, color:hot ? '#F2A65A' : RU, lh:1, ls:0.14});
    K.hd(C, x + pad, y + pad + 20, tw, ch - pad * 2 - 20, t, F, {tc:hot ? '#FFFFFF' : PE, mc:hot ? '#D2E3E7' : MUT});
  });
  C.rect(x0 - 1, Z.y, 2, h, {fill:INK}); C.rect(x0, Z.y + h - 1, w, 2, {fill:INK});
  K.poly(C, [[x0, Z.y - 8], [x0 - 6, Z.y + 4], [x0 + 6, Z.y + 4]], {fill:INK}); K.poly(C, [[x0 + w + 8, Z.y + h], [x0 + w - 4, Z.y + h - 6], [x0 + w - 4, Z.y + h + 6]], {fill:INK});
  C.text(x0, Z.y + h + 8, w, 18, 'Low  →  High', {font:'body', size:10.5, wt:600, color:MUT, align:'c', lh:1, caps:true, ls:0.16});
}
/* notes: columns with a petrol top bar and a small label */
function notes(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? n : 3); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 22, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 20, top = 46, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - top - pad - 6, {hMax:21, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + top + pad + 10), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.25);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0;
    C.rect(x, y, cw, ch2, {fill:'#FFFFFF', line:HL, lw:1});
    C.rect(x, y, cw, 5, {fill:hot ? RU : PE});
    C.text(x + pad, y + 20, 160, 16, 'Point ' + (k + 1), {font:'body', size:10.5, wt:700, color:hot ? RU : MUT, lh:1, caps:true, ls:0.16});
    C.icon(x + cw - pad - 18, y + 18, 18, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? RU : PE);
    K.hd(C, x + pad, y + top, tw, ch2 - top - pad, t, F, {tc:PE, mc:INK});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: one ruled KPI strip */
function kpi(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), pad = 26, tw = cw - pad * 2, sh = Math.min(Z.h, 300), y0 = Z.y + (Z.h - sh) * 0.3;
  var vs = C.groupFit(vals, tw, 84, {font:'head', max:68, min:24, maxLines:1});
  var ls = C.groupFit(labs, tw, 50, {font:'body', max:16, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, sh - 190, {font:'body', max:13.5, min:10.5, lh:1.38});
  C.rect(Z.x, y0, Z.w, 3, {fill:PE}); C.rect(Z.x, y0 + sh - 1, Z.w, 1, {fill:PE});
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.rect(x, y0 + 20, 1, sh - 40, {fill:HL});
    C.text(x + pad, y0 + 26, tw, 16, 'KPI ' + (k + 1), {font:'body', size:10.5, wt:700, color:RU, lh:1, ls:0.16});
    C.text(x + pad, y0 + 50, tw, vs + 10, vals[k], {font:'head', size:vs, wt:600, color:PE, lh:1, caps:false, ls:-0.02, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(x + pad, y0 + 68 + vs, tw, 46, labs[k], {font:'body', size:ls, wt:700, color:INK, lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + pad, y0 + 76 + vs + le._h, tw, sh - 90 - vs - le._h, t.x, {font:'body', size:ds, color:MUT, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: gantt - bars stepping along a time axis */
function gantt(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return table(C, Z, o);
  var lw = Math.min(170, Z.w * 0.18), ax = 30, rh = Math.min((Z.h - ax) / n, 92), gw = Z.w - lw, seg = gw / n, bw = seg * 1.0;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), lw - 16, rh - 14, {font:'head', max:22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t, k){ return t.d; }), gw * 0.46, rh - 22, {font:'body', max:14.5, min:10.5, lh:1.34});
  var y0 = Z.y + ax;
  for (var q = 0; q <= n; q++) C.rect(Z.x + lw + q * seg, Z.y + 8, 1, ax - 8 + rh * n, {fill:HL});
  C.rect(Z.x, y0, Z.w, 1.5, {fill:PE});
  it.forEach(function(t, k){
    var y = y0 + k * rh, bx = Z.x + lw + k * seg, right = k < n / 2, tw = right ? Z.x + Z.w - bx - bw - 14 : bx - Z.x - lw - 14;
    C.text(Z.x, y, lw - 16, rh, t.h || 'Phase ' + (k + 1), {font:'head', size:hs, wt:600, color:PE, va:'m', lh:1.12, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.rect(bx, y + rh * 0.28, bw, rh * 0.44, {fill:k === n - 1 ? RU : PE, r:2});
    C.text(right ? bx + bw + 14 : Z.x + lw, y, Math.max(tw, 120), rh, t.d, {font:'body', size:ds, color:INK, va:'m', align:right ? 'l' : 'r', lh:1.34, ed:{f:'x-part', i:t.i, p:1}});
    C.rect(Z.x, y + rh - 0.5, Z.w, 1, {fill:HL});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:TINT}); C.rect(Z.x, Z.y, 6, Z.h, {fill:RU});
  var pad = 44, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:17}, wt:600, color:PE, lh:1.14, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 28, min:14}, color:INK, lh:1.48, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, photo ? 520 : 440, H, {fill:PE}); C.rect(photo ? 520 : 440, 0, 8, H, {fill:RU});
  if (photo) D.imgBox(C, 0, 0, 520, H, {r:0});
  else { K.grid(C, 44, '#FFFFFF', 0.08, 0, 0, 440, H); C.rect(70, 70, 54, 54, {fill:RU}); C.rect(124, 124, 54, 54, {fill:'#FFFFFF', op:0.9}); C.rect(70, 178, 54, 54, {fill:'#FFFFFF', op:0.3});
    if (f.website) C.text(70, H - 90, 320, 20, f.website, {font:'body', size:13, wt:600, color:'#FFFFFF', lh:1, ed:{f:'tf-website'}}); }
  var x = (photo ? 520 : 440) + 70, tw = W - x - 70;
  if (f.company) C.text(x, 70, tw, 20, f.company, {font:'body', size:13, wt:700, color:RU, lh:1, caps:true, ls:0.16, ed:{f:'tf-company'}, maxLines:1});
  C.text(x, 170, tw, 250, C.s.title, {font:'head', fit:{max:64, min:30}, wt:600, color:PE, ed:{f:'title'}, maxLines:4, va:'t', lh:1.06, ls:-0.01});
  if (C.s.subtitle) C.text(x, 440, tw - 40, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.rect(x, 560, tw, 1, {fill:HL});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { C.text(x, 578, tw, 16, 'Prepared by', {font:'body', size:10.5, wt:700, color:MUT, lh:1, caps:true, ls:0.16}); C.text(x, 600, tw, 24, who, {font:'body', size:16, wt:600, color:INK, lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(W - (photo ? 520 : 440), 0, photo ? 520 : 440, H, {fill:PE}); C.rect(W - (photo ? 520 : 440) - 8, 0, 8, H, {fill:RU});
  if (photo) D.imgBox(C, W - 520, 0, 520, H, {r:0}); else K.grid(C, 44, '#FFFFFF', 0.08, W - 440, 0, 440, H);
  var tw = W - (photo ? 520 : 440) - 150;
  C.text(70, 120, tw, 190, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:34}, wt:600, color:PE, ed:{f:'title'}, maxLines:2, va:'b', lh:1.04});
  if (C.s.subtitle) C.text(70, 326, tw, 66, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){
    var y = 420 + k * 58;
    C.rect(70, y, tw, 1, {fill:HL});
    C.icon(70, y + 19, 20, c.icon, RU);
    C.text(70 + 38, y, tw - 40, 58, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
  if (cs.length) C.rect(70, 420 + cs.length * 58, tw, 1, {fill:HL});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:RU, tc:PE}); return; }
  var photo = o.img === 'photo', x = 90, w = photo ? 620 : W - 180;
  if (photo) D.imgBox(C, 780, 110, 430, 480, {r:2});
  C.rect(x, 130, 6, 400, {fill:RU});
  var qe = C.text(x + 40, 130, w - 40, 320, C.s.title, {font:'head', fit:{max:photo ? 36 : 46, min:19}, wt:500, color:PE, italic:true, lh:1.22, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 40, 130 + qe._h + 28, w - 40, 24, bt, {font:'body', size:14, wt:700, color:MUT, lh:1.1, caps:true, ls:0.14, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'slatebrief', name:'Slate Brief', tag:'White · consulting',
  desc:'A clean white consulting page in petrol and rust with tables, a 2x2 matrix, KPI strips and a Gantt timeline: rigorous and quiet for strategy, audits and board papers.',
  fonts:{head:'Newsreader', body:'Public Sans', headW:600, bodyW:400, headLh:1.1, gf:'Newsreader:ital,wght@0,500;0,600;1,500|Public+Sans:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Petrol panel + grid', {}), V('v2', 'Photo panel', {img:'photo'})],
    closing:[V('v1', 'Contacts + petrol panel', {}), V('v2', 'Contacts + photo panel', {img:'photo'})],
    agenda:[V('v1', 'Table', {body:table}), V('n2', 'Note columns', {body:notes}), V('v2', 'Table + image right', {body:table, img:'right'})],
    text:[V('v1', 'Tinted statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Note columns', {body:notes, hl:true}), V('n2', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:1})],
    p3:[V('v1', 'Note columns', {body:notes, hl:true}), V('n2', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + table', {body:table, img:'left'})],
    p4:[V('v1', '2x2 matrix', {body:matrix}), V('n2', 'Note columns', {body:notes}), V('n3', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + notes', {body:notes, img:'left', cols:2})],
    p56:[V('v1', 'Table', {body:table}), V('n2', 'Note columns', {body:notes}), V('v2', 'Table + image right', {body:table, img:'right'}), V('v3', 'Image left + table', {body:table, img:'left'})],
    p78:[V('v1', 'Table', {body:table}), V('v2', 'Table + image right', {body:table, img:'right'})],
    stats:[V('v1', 'KPI strip', {body:kpi}), V('n2', 'Progress rings', {body:'statRings', track:HL}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'KPI strip + image right', {body:kpi, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Gantt', {body:gantt}), V('n2', 'Table', {body:table}), V('n3', 'Note columns', {body:notes}), V('v2', 'Table + image right', {body:table, img:'right'})],
    quote:[V('v1', 'Bar quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

