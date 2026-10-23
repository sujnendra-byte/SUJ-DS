
/* DesignStager template: frostslate */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 36 - Frost Slate : cool light page, cobalt quarter-circles, frosted glass panels with a cobalt edge */
var CO = '#1747E0', CO2 = '#5B8CFF', INK = '#0F1B3D', BODY = '#4B5878', ICE = '#E8EEF9';
var P0 = {bg:'#EEF2F8', text:INK, title:INK, muted:BODY, accent:CO, accent2:CO2, card:'#FFFFFF', line:'#CAD5EA', onAccent:'#FFFFFF'};
var S = {
  margin:72, series:[CO, CO2, '#0B2A8F', '#9DB8FF', '#22B8CF', '#7C8DB5'],
  header:{kind:'left', top:64, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.website || C.cover.company || ''; }, kc:BODY, kGap:26, kSize:11.5},
  card:{kind:'glass', r:10, fill:'#FFFFFF', op:0.7, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.06, dark:false, tc:INK, mc:BODY,
        hl:{kind:'solid', fill:CO, dark:true, tc:'#FFFFFF', mc:'#DCE6FF'}},
  icon:{kind:'square', bg:CO, fg:'#FFFFFF', r:8}, num:{kind:'big', fg:CO},
  conc:{kind:'bar', barC:CO, label:'Takeaway', lc:CO, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:10, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#B9CCFF'], [100, '#5B8CFF']]}}
};
function quarter(C, corner, r, color, op){
  var x = corner.indexOf('r') >= 0 ? W - r : -r, y = corner.indexOf('b') >= 0 ? H - r : -r;
  C.ell(x, y, r * 2, r * 2, {fill:color, op:op});
}
function glass(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 10;
  if (o.co) { C.rect(x, y, w, h, {grad:{ang:140, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.22], [50, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#DCE6FF', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:165, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.55]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.06});
  if (o.edge !== false) C.rect(x + r, y, Math.min(64, w * 0.3), 3, {fill:CO});
  return {tc:INK, mc:BODY, ac:CO};
}
function foot(C){
  var M = 72, y = H - 38, f = C.cover;
  if (f.company) C.text(M, y, 420, 18, f.company, {font:'body', size:11.5, wt:700, color:INK, lh:1, va:'m', caps:true, ls:0.12});
  C.rect(W - M - 58, y - 4, 58, 26, {fill:CO, r:4});
  C.text(W - M - 58, y - 4, 58, 26, pad2(C.i + 1), {font:'body', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#F6F8FC'], [100, '#E3EAF6']]}});
  var k = (C.i || 0) % 4;
  if (kind === 'cover' || kind === 'closing') return;
  quarter(C, ['br', 'bl', 'tr', 'br'][k], 170, CO, 0.9);
  quarter(C, ['br', 'bl', 'tr', 'br'][k], 250, CO2, 0.18);
  D.glow(C, [W * 0.1, W * 0.9, W * 0.2, W * 0.5][k], H * 0.1, 420, 280, '#C9D8FF', 0.6);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }

/* glass panels with a cobalt square icon on the top-left */
function iconPanels(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : Math.ceil(n / 2))); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = 24, ic = 40;
  var side = rows > 1 || ch < 220;
  var tw = side ? cw - pad * 2 - ic - 16 : cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, side ? ch - pad * 2 : ch - pad * 2 - ic - 18, {hMax:19, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, (side ? Math.max(bmx, ic) : bmx + ic + 18) + pad * 2 + 6), tot = ch2 * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + g), hot = o.hl && k === 0, cc = glass(C, x, y, cw, ch2, {co:hot});
    C.rect(x + pad, y + pad, ic, ic, {fill:hot ? '#FFFFFF' : CO, r:9});
    C.icon(x + pad + 10, y + pad + 10, 20, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? CO : '#FFFFFF');
    if (side) K.hd(C, x + pad + ic + 16, y + pad, tw, ch2 - pad * 2, t, F, {tc:cc.tc, mc:cc.mc});
    else K.hd(C, x + pad, y + pad + ic + 18, tw, ch2 - pad * 2 - ic - 18, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* big cobalt numerals with text, hairlines between, no cards */
function numerals(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 3 && Z.w > 720 ? 2 : 1), per = Math.ceil(n / cols), gx = 50, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(Z.h / per, 150), nw = Math.min(96, rh * 0.8), tw = cw - nw - 16;
  var F = K.fitHD(C, it, tw, 34, rh - 26, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15.5 : 22, hMin:13, dMin:10.5, noBoost:n > 4});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, bh = blockH(C, t, tw, F), oy = Math.max(12, (rh - bh) / 2);
    C.text(x, y, nw, rh, pad2(k + 1), {font:'head', size:Math.min(54, rh * 0.5), wt:800, color:CO, va:'m', lh:1, caps:false, ls:-0.03});
    K.hd(C, x + nw + 16, y + oy, tw, rh - oy - 4, t, F, {tc:INK, mc:BODY});
    if (r < per - 1) C.rect(x, y + rh - 0.5, cw, 1, {fill:INK, op:0.14});
  });
}
/* a cobalt index panel on the left, the explanations on a glass sheet on the right */
function indexSheet(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760 || !it.some(function(t){ return t.h; })) return iconPanels(C, Z, o);
  var lw = Math.round(Z.w * 0.32), rh = Z.h / n, pad = 26, tw = Z.w - lw - pad * 2;
  glass(C, Z.x + lw - 10, Z.y, Z.w - lw + 10, Z.h, {edge:false});
  glass(C, Z.x, Z.y, lw, Z.h, {co:true});
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), lw - pad * 2 - 34, rh - 14, {font:'head', max:19, min:12, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw - 10, rh - 16, {font:'body', max:n <= 3 ? 17 : 15, min:10.5, lh:1.38});
  it.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.text(Z.x + pad, y, 30, rh, String(k + 1), {font:'head', size:15, wt:800, color:'#9DB8FF', va:'m', lh:1, caps:false});
    C.text(Z.x + pad + 34, y, lw - pad * 2 - 34, rh, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', va:'m', lh:1.14, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    C.text(Z.x + lw + pad, y, tw - 10, rh, t.d, {font:'body', size:ds, color:INK, va:'m', lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
    if (k) { C.rect(Z.x + pad, y - 0.5, lw - pad * 2, 1, {fill:'#FFFFFF', op:0.25}); C.rect(Z.x + lw + pad, y - 0.5, tw - 10, 1, {fill:INK, op:0.12}); }
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: big cobalt numbers beside their label, in rows */
function numberRows(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n > 2 && Z.w > 760 ? 2 : 1, per = Math.ceil(n / cols), gx = 30, g = 16, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min((Z.h - g * (per - 1)) / per, 190), vals = st.map(sv), labs = st.map(sl), vw = cw * 0.4, pad = 26;
  var vs = C.groupFit(vals, vw - pad, rh * 0.6, {font:'head', max:70, min:22, maxLines:1});
  var ls = C.groupFit(labs, cw - vw - pad * 2, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - vw - pad * 2, rh - 80, {font:'body', max:14.5, min:10.5, lh:1.38});
  var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
  st.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g);
    glass(C, x, y, cw, rh, {});
    C.text(x + pad, y, vw - pad, rh, vals[k], {font:'head', size:vs, wt:800, color:CO, va:'m', lh:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var lh = C.measureText(labs[k], cw - vw - pad * 2, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.x ? C.measureText(t.x, cw - vw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h : 0, by = y + Math.max(14, (rh - lh - (dh ? dh + 8 : 0)) / 2);
    C.text(x + vw + pad, by, cw - vw - pad * 2, lh + 4, labs[k], {font:'head', size:ls, wt:700, color:INK, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + vw + pad, by + lh + 8, cw - vw - pad * 2, dh + 4, t.x, {font:'body', size:ds, color:BODY, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var cc = glass(C, Z.x, Z.y, Z.w, Z.h, {}), pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:28, min:16}, wt:800, color:CO, lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 25 : 30, min:14}, color:INK, lh:1.45, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function brand(C, x, y, light){ var f = C.cover; if (!f.company) return; C.rect(x, y, 22, 22, {fill:light ? '#FFFFFF' : CO, r:5}); C.ell(x + 11, y + 11, 22, 22, {fill:light ? '#9DB8FF' : CO2, op:0.9}); C.text(x + 44, y, 420, 22, f.company, {font:'head', size:15, wt:800, color:light ? '#FFFFFF' : INK, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1}); }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) {
    C.rect(W - 420, 0, 420, H, {grad:{ang:150, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}});
    D.imgBox(C, 640, 110, 500, 500, {r:10});
    brand(C, 72, 52);
  } else {
    C.rect(0, 0, W, H, {grad:{ang:140, stops:[[0, '#2F63FF'], [100, '#0B2596']]}});
    C.ell(W - 420, -220, 700, 700, {fill:'#FFFFFF', op:0.08}); C.ell(W - 260, 300, 560, 560, {fill:'#FFFFFF', op:0.07}); C.ell(-160, H - 240, 420, 420, {fill:'#FFFFFF', op:0.06});
    brand(C, 72, 52, true);
  }
  var lt = !photo, tw = photo ? 520 : 800;
  if (f.website) C.text(72, 190, tw, 20, f.website, {font:'body', size:13, wt:600, color:lt ? '#DCE6FF' : BODY, lh:1, ed:{f:'tf-website'}});
  C.text(72, 220, tw, 220, C.s.title, {font:'head', fit:{max:photo ? 62 : 88, min:32}, wt:800, color:lt ? '#FFFFFF' : INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 456, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:lt ? '#DCE6FF' : BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) { var pw = Math.min(tw, who.length * 8.2 + 44); C.rect(72, 566, pw, 44, {fill:lt ? '#FFFFFF' : CO, r:6}); C.text(72 + 20, 566, pw - 40, 44, who, {font:'body', size:14, wt:700, color:lt ? CO : '#FFFFFF', va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  brand(C, 72, 52);
  C.rect(W - 420, 0, 420, H, {grad:{ang:150, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}});
  if (photo) D.imgBox(C, 640, 110, 500, 500, {r:10});
  else { C.ell(W - 520, 110, 500, 500, {fill:'#FFFFFF', op:0.1}); C.ell(W - 440, 190, 340, 340, {fill:'#FFFFFF', op:0.12}); C.ell(W - 360, 270, 180, 180, {fill:'#FFFFFF'}); C.icon(W - 310, 320, 80, 'mdi:hand-wave-outline', CO); }
  C.text(72, 140, 540, 190, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:34}, wt:800, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 346, 520, 70, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:BODY, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contacts(C).forEach(function(c, k){
    var y = 440 + k * 60;
    C.rect(72, y, 44, 44, {fill:CO, r:9}); C.icon(72 + 12, y + 12, 20, c.icon, '#FFFFFF');
    C.text(72 + 60, y, 460, 44, c.val, {font:'body', fit:{max:18, min:12}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  C.rect(0, 0, 470, H, {grad:{ang:150, stops:[[0, '#2F63FF'], [100, '#0E2FB8']]}});
  C.ell(-140, H - 300, 460, 460, {fill:'#FFFFFF', op:0.08});
  var te = C.text(72, 150, 340, 240, C.s.title || 'Agenda', {font:'head', fit:{max:70, min:34}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1.02, ls:-0.02});
  if (C.s.subtitle) C.text(72, 150 + te._h + 18, 330, 90, C.s.subtitle, {font:'body', fit:{max:17, min:13}, color:'#DCE6FF', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  numerals(C, {x:540, y:80, w:W - 540 - 72, h:H - 170}, {cols:1, max:8});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:CO, tc:INK}); return; }
  var photo = o.img === 'photo', x = 72, w = photo ? 620 : W - 144 - 80;
  if (photo) D.imgBox(C, 760, 100, 448, 520, {r:10});
  C.rect(x, 130, 64, 64, {fill:CO, r:10}); C.text(x, 142, 64, 64, '”', {font:'head', size:74, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x, 226, w, 320, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:19}, wt:700, color:INK, lh:1.22, ls:-0.01, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.rect(x, 226 + qe._h + 28, 50, 3, {fill:CO}); C.text(x, 226 + qe._h + 44, w, 24, bt, {font:'body', size:15, wt:700, color:BODY, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'frostslate', name:'Frost Slate', tag:'Light · cool glass',
  desc:'A cool light page with cobalt quarter-circles and frosted glass panels edged in cobalt: crisp and calm for services, IT and consulting decks.',
  fonts:{head:'Manrope', body:'Inter', headW:800, bodyW:400, headLh:1.08, headLs:-0.01, gf:'Manrope:wght@500;600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Cobalt cover', {}), V('v2', 'Photo on cobalt band', {img:'photo'})],
    closing:[V('v1', 'Cobalt band + contacts', {}), V('v2', 'Photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Cobalt panel + numerals', {fn:agendaA}), V('n2', 'Icon panels', {body:iconPanels}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1})],
    text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Icon panels', {body:iconPanels, hl:true}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals, cols:2}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + icon panels', {body:iconPanels, img:'left', cols:1})],
    p3:[V('v1', 'Icon panels', {body:iconPanels}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals, cols:1}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + icon panels', {body:iconPanels, img:'left', cols:1})],
    p4:[V('v1', 'Index sheet', {body:indexSheet}), V('n2', 'Icon panels', {body:iconPanels}), V('n3', 'Numerals', {body:numerals}), V('n4', 'Icon panels in a row', {body:iconPanels, cols:4}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + icon panels', {body:iconPanels, img:'left', cols:2})],
    p56:[V('v1', 'Icon panels', {body:iconPanels}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1}), V('v3', 'Image left + numerals', {body:numerals, img:'left', cols:1})],
    p78:[V('v1', 'Icon panels', {body:iconPanels}), V('n2', 'Numerals', {body:numerals}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1})],
    stats:[V('v1', 'Number rows', {body:numberRows}), V('n2', 'Progress rings', {body:'statRings', track:'#D5DEF2'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Number rows + image right', {body:numberRows, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'Milestone cards', {body:'tlCards'}), V('n2', 'Index sheet', {body:indexSheet}), V('n3', 'Numerals', {body:numerals}), V('v2', 'Numerals + image right', {body:numerals, img:'right', cols:1})],
    quote:[V('v1', 'Bold quote', {}), V('v2', 'Quote + photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

