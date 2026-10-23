
/* DesignStager template: rosewaterglass */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* Template 44 - Rosewater Glass : blush page, pearly glass bubbles and petals, berry ink */
var BE = '#B4235A', PL = '#F28CA8', INK = '#4A1230', MUT = '#8A5A6E';
var P0 = {bg:'#FCE9EE', text:INK, title:INK, muted:MUT, accent:BE, accent2:PL, card:'#FFFFFF', line:'#F2C9D5', onAccent:'#FFFFFF'};
var S = {
  margin:76, series:[BE, PL, '#7A1640', '#FFC2D1', '#D96C8E', '#4A1230'],
  header:{kind:'center', top:58, tMax:46, tMin:26, sMax:17, sMin:13, kicker:function(C){ return C.cover.company || ''; }, kc:BE, kGap:24, kSize:11.5},
  card:{kind:'glass', r:28, fill:'#FFFFFF', op:0.55, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05, dark:false, tc:INK, mc:MUT,
        hl:{kind:'solid', fill:BE, dark:true, tc:'#FFFFFF', mc:'#FFDCE6'}},
  icon:{kind:'circle', bg:BE, fg:'#FFFFFF'}, num:{kind:'big', fg:BE},
  conc:{kind:'bar', barC:BE, label:'Remember', lc:BE, tc:INK},
  footer:{fn:foot}, imgFramed:true, imgR:28, bodyGap:26,
  imgPh:{grad:{ang:135, stops:[[0, '#FFD0DC'], [100, '#E88BA8']]}}
};
function pearl(C, x, y, w, h, o){
  o = o || {}; var r = o.r != null ? o.r : 28;
  if (o.hot) { C.rect(x, y, w, h, {grad:{ang:140, stops:[[0, '#D4447A'], [100, '#9A1B4C']]}, r:r, shadow:0.12}); C.rect(x, y, w, h, {grad:{ang:170, stops:[[0, '#FFFFFF', 0.28], [45, '#FFFFFF', 0]]}, r:r}); return {tc:'#FFFFFF', mc:'#FFDCE6', ac:'#FFFFFF'}; }
  C.rect(x, y, w, h, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.9], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lop:1, lw:1.5, r:r, shadow:0.05});
  return {tc:INK, mc:MUT, ac:BE};
}
function bubble(C, cx, cy, d, deep){
  C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:140, stops:deep ? [[0, '#F7A8BE'], [100, '#C73A72']] : [[0, '#FFFFFF', 0.95], [100, '#FFC2D1', 0.55]]}, line:'#FFFFFF', lop:0.9, lw:1.5});
  C.ell(cx - d * 0.3, cy - d * 0.36, d * 0.34, d * 0.2, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.75], [100, '#FFFFFF', 0]]}});
}
function foot(C){
  var y = H - 36, f = C.cover;
  C.ell(W / 2 - 18, y - 6, 36, 28, {fill:'#FFFFFF', op:0.8});
  C.text(W / 2 - 18, y - 6, 36, 28, String(C.i + 1), {font:'head', size:12, wt:700, color:BE, align:'c', va:'m', lh:1});
  C.text(76, y, 400, 16, f.website || f.company || '', {font:'body', size:11, wt:500, color:MUT, lh:1, va:'m'});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#FFF3F5'], [100, '#F9D6E0']]}});
  var k = (C.i || 0) % 3;
  D.glow(C, [W * 0.9, W * 0.1, W * 0.5][k], [H * 0.9, H * 0.9, 0][k], 700, 480, '#F7A8BE', 0.5);
  if (kind === 'cover' || kind === 'closing') return;
  bubble(C, [W - 70, 60, W - 80][k], [H - 80, H - 70, 70][k], 190, false);
  bubble(C, [W - 190, 180, W - 200][k], [H - 40, H - 30, 40][k], 80, true);
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.4}).h : 0); }
/* bubbles: each point lives inside a pearly glass circle */
function bubbles(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760 || Z.h < 280) return petals(C, Z, o);
  var g = 18, d = Math.min((Z.w - g * (n - 1)) / n, Z.h), tot = d * n + g * (n - 1), x0 = Z.x + (Z.w - tot) / 2, y0 = Z.y + (Z.h - d) / 2, tw = d * 0.68;
  var F = K.fitHD(C, it, tw, 50, d * 0.62 - 44, {hMax:20, dMax:14.5, hMin:12, dMin:10});
  it.forEach(function(t, k){
    var cx = x0 + k * (d + g) + d / 2, cy = y0 + d / 2, hot = o.hl && k === 0;
    if (hot) { C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:140, stops:[[0, '#D4447A'], [100, '#9A1B4C']]}, shadow:0.12}); C.ell(cx - d * 0.3, cy - d * 0.42, d * 0.5, d * 0.22, {grad:{ang:90, stops:[[0, '#FFFFFF', 0.3], [100, '#FFFFFF', 0]]}}); }
    else C.ell(cx - d / 2, cy - d / 2, d, d, {grad:{ang:155, stops:[[0, '#FFFFFF', 0.95], [100, '#FFFFFF', 0.4]]}, line:'#FFFFFF', lop:1, lw:1.5, shadow:0.05});
    C.icon(cx - 13, cy - d * 0.36, 26, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? '#FFFFFF' : BE);
    var bh = blockH(C, t, tw, F);
    K.hd(C, cx - tw / 2, cy - d * 0.36 + 40, tw, d * 0.62, t, F, {tc:hot ? '#FFFFFF' : INK, mc:hot ? '#FFDCE6' : MUT, align:'c'});
  });
}
/* petals: soft glass rows with a berry bubble number */
function petals(C, Z, o){
  var it = C.it.slice(0, o.max || 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cols), gx = 18, g = 12, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min((Z.h - g * (per - 1)) / per, 130), bd = Math.min(52, rh - 20), pad = 18, tw = cw - pad * 2 - bd - 16;
  var F = K.fitHD(C, it, tw, 30, rh - 22, {hMax:20, dMax:it.some(function(t){ return t.h; }) ? 15 : 20, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  rh = Math.min(rh, Math.max(bmx, bd) + 30); var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g), cc = pearl(C, x, y, cw, rh, {r:Math.min(rh / 2, 40)});
    bubble(C, x + pad + bd / 2, y + rh / 2, bd, true);
    C.text(x + pad, y + rh / 2 - bd / 2, bd, bd, String(k + 1), {font:'head', size:bd * 0.38, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + pad + bd + 16, y + Math.max(10, (rh - bh) / 2), tw, rh - 12, t, F, {tc:cc.tc, mc:cc.mc});
  });
}
/* scallop cards: glass cards with a bubble sitting half over the top edge */
function scallops(C, Z, o){
  var it = C.it.slice(0, o.max || 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 4 : 3)); if (Z.w < 720) cols = Math.min(cols, 2);
  var rows = Math.ceil(n / cols), g = 18, bd = 56, gy = bd / 2 + 16, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - bd / 2 - gy * (rows - 1)) / rows, pad = 22, tw = cw - pad * 2;
  var F = K.fitHD(C, it, tw, 52, ch - pad - bd / 2 - 20, {hMax:20, dMax:15, hMin:12.5, dMin:10.5});
  var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
  var ch2 = Math.min(ch, bmx + bd / 2 + pad + 26), tot = bd / 2 + ch2 * rows + gy * (rows - 1), y0 = Z.y + bd / 2 + Math.max(0, (Z.h - tot) * 0.3);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = y0 + r * (ch2 + gy), hot = o.hl && k === 1, cc = pearl(C, x, y, cw, ch2, {hot:hot});
    bubble(C, x + cw / 2, y, bd, !hot);
    C.icon(x + cw / 2 - 11, y - 11, 22, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), hot ? BE : '#FFFFFF');
    K.hd(C, x + pad, y + bd / 2 + 14, tw, ch2 - bd / 2 - pad - 8, t, F, {tc:cc.tc, mc:cc.mc, align:'c'});
  });
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
/* stats: bubbles of growing size */
function growing(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, vals = st.map(sv), labs = st.map(sl), tw = cw - 26, dmax = Math.min(cw - 24, Z.h * 0.58, 240), base = Z.y + dmax;
  var ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12.5, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Z.h - dmax - 76, {font:'body', max:13.5, min:10.5, lh:1.38});
  st.forEach(function(t, k){
    var d = dmax * (0.66 + 0.34 * (n > 1 ? k / (n - 1) : 1)), cx = Z.x + cw * (k + 0.5), cy = base - d / 2, deep = k === n - 1;
    bubble(C, cx, cy, d, deep);
    C.text(cx - d * 0.4, cy - d * 0.25, d * 0.8, d * 0.5, vals[k], {font:'head', fit:{max:54, min:18}, wt:700, color:deep ? '#FFFFFF' : BE, align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, base + 18, tw, 46, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, base + 24 + le._h, tw, Z.y + Z.h - base - 24 - le._h, t.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* timeline: string of pearls */
function pearls(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return petals(C, Z, o);
  var cw = Z.w / n, bd = 74, y = Z.y + 6, tw = cw - 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), bd - 14, bd * 0.5, {font:'head', max:19, min:10, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, Z.h - bd - 40, {font:'body', max:15.5, min:10.5, lh:1.4});
  C.rect(Z.x + cw / 2, y + bd / 2 - 1, Z.w - cw, 2, {fill:PL, op:0.8});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), deep = k === n - 1;
    bubble(C, cx, y + bd / 2, bd, deep);
    C.text(cx - bd / 2 + 6, y, bd - 12, bd, t.h || String(k + 1), {font:'head', size:hs, wt:700, color:deep ? '#FFFFFF' : BE, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var ph = Math.min(Z.h - bd - 30, C.measureText(t.d, tw - 28, {font:'body', max:ds, min:ds, lh:1.4}).h + 34); pearl(C, cx - tw / 2, y + bd + 22, tw, ph, {r:20});
    C.text(cx - tw / 2 + 14, y + bd + 36, tw - 28, ph - 22, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  pearl(C, Z.x, Z.y, Z.w, Z.h, {r:36}); var pad = 52, y = Z.y + pad, w = Z.w - pad * 2;
  if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:700, color:BE, align:'c', lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
  C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:INK, align:'c', lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  if (photo) { bubble(C, 960, 360, 560, false); D.imgBox(C, 730, 130, 460, 460, {r:230}); bubble(C, 760, 560, 110, true); bubble(C, 1180, 170, 70, true); }
  else { bubble(C, 980, 360, 480, false); bubble(C, 880, 300, 230, true); bubble(C, 1100, 480, 150, false); bubble(C, 1150, 190, 80, true); bubble(C, 790, 560, 60, true); }
  if (f.company) C.text(76, 60, 500, 20, f.company, {font:'body', size:13, wt:700, color:BE, lh:1, caps:true, ls:0.16, ed:{f:'tf-company'}, maxLines:1});
  C.text(76, 170, 620, 260, C.s.title, {font:'head', fit:{max:76, min:32}, wt:700, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.04, ls:-0.01});
  if (C.s.subtitle) C.text(76, 446, 540, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUT, ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) { var pw = Math.min(600, who.length * 8.2 + 56); pearl(C, 76, 556, pw, 50, {r:25}); C.text(76 + 28, 556, pw - 56, 50, who, {font:'body', size:14, wt:700, color:INK, va:'m', lh:1.1, ed:{f:'tf-presenter'}, maxLines:1}); }
  if (f.website) C.text(76, 630, 500, 18, f.website, {font:'body', size:12.5, wt:600, color:BE, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  bubble(C, W / 2, 330, 620, false); bubble(C, 250, 560, 130, true); bubble(C, W - 230, 150, 100, true); bubble(C, W - 150, 520, 60, false);
  if (photo) D.imgBox(C, W / 2 - 70, 70, 140, 140, {r:70});
  C.text(W / 2 - 280, 210, 560, 130, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:34}, wt:700, color:INK, align:'c', ed:{f:'title'}, maxLines:1, va:'m', lh:1});
  if (C.s.subtitle) C.text(W / 2 - 230, 344, 460, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:MUT, align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C), n = cs.length; if (!n) return;
  var cw = Math.min(320, (W - 200 - 14 * (n - 1)) / n), tot = cw * n + 14 * (n - 1), x0 = (W - tot) / 2;
  cs.forEach(function(c, k){
    var x = x0 + k * (cw + 14), y = 560;
    pearl(C, x, y, cw, 64, {r:32}); bubble(C, x + 32, y + 32, 44, true); C.icon(x + 22, y + 22, 20, c.icon, '#FFFFFF');
    C.text(x + 64, y, cw - 82, 64, c.val, {font:'body', fit:{max:16, min:11}, wt:700, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  bubble(C, 250, 400, 420, false); bubble(C, 120, 600, 110, true);
  var te = C.text(90, 300, 320, 130, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:32}, wt:700, color:INK, align:'c', ed:{f:'title'}, maxLines:2, va:'m', lh:1.02});
  if (C.s.subtitle) C.text(110, 436, 280, 70, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:MUT, align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  petals(C, {x:540, y:76, w:W - 540 - 76, h:H - 170}, {cols:1, max:7});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:BE, tc:INK}); return; }
  var photo = o.img === 'photo', x = photo ? 76 : 150, w = photo ? 640 : W - 300, al = photo ? 'l' : 'c';
  if (photo) D.imgBox(C, 790, 130, 420, 420, {r:210});
  pearl(C, x - (photo ? 0 : 40), 110, w + (photo ? 0 : 80), 470, {r:44});
  bubble(C, photo ? x + 76 : W / 2, 110, 76, true); C.text((photo ? x + 76 : W / 2) - 38, 84, 76, 76, '”', {font:'head', size:60, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  var qe = C.text(x + 44, 184, w - 88, 290, C.s.title, {font:'head', fit:{max:photo ? 32 : 40, min:18}, wt:600, color:INK, align:al, lh:1.26, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x + 44, 184 + qe._h + 24, w - 88, 24, bt, {font:'body', size:15, wt:700, color:BE, align:al, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1}); }
}
D.register({
  id:'rosewaterglass', name:'Rosewater Glass', tag:'Light · blush glass',
  desc:'A blush page with pearly glass bubbles, soft petal rows and berry ink: gentle and modern for beauty, wellness, lifestyle, weddings and personal brands.',
  fonts:{head:'Quicksand', body:'Nunito', headW:700, bodyW:400, headLh:1.1, gf:'Quicksand:wght@500;600;700|Nunito:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Glass bubbles', {}), V('v2', 'Round photo in bubble', {img:'photo'})],
    closing:[V('v1', 'Big bubble + contacts', {}), V('v2', 'Bubble + small photo', {img:'photo'})],
    agenda:[V('v1', 'Bubble + petal rows', {fn:agendaA}), V('n2', 'Scallop cards', {body:scallops}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1})],
    text:[V('v1', 'Pearl statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Scallop cards', {body:scallops}), V('n2', 'Bubbles', {body:bubbles, hl:true}), V('n3', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p3:[V('v1', 'Bubbles', {body:bubbles, hl:true}), V('n2', 'Scallop cards', {body:scallops, hl:true}), V('n3', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p4:[V('v1', 'Scallop cards', {body:scallops}), V('n2', 'Bubbles', {body:bubbles}), V('n3', 'Petal rows', {body:petals, cols:2}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p56:[V('v1', 'Scallop cards', {body:scallops}), V('n2', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1}), V('v3', 'Image left + petal rows', {body:petals, img:'left', cols:1})],
    p78:[V('v1', 'Petal rows', {body:petals}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1})],
    stats:[V('v1', 'Growing bubbles', {body:growing}), V('n2', 'Progress rings', {body:'statRings', track:'#F6CFDA'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Bubbles + image right', {body:growing, img:'right'}), V('v3', 'Image left + bars', {body:'statBars', img:'left'})],
    timeline:[V('v1', 'String of pearls', {body:pearls}), V('n2', 'Petal rows', {body:petals}), V('n3', 'Scallop cards', {body:scallops}), V('v2', 'Petal rows + image right', {body:petals, img:'right', cols:1})],
    quote:[V('v1', 'Pearl quote', {}), V('v2', 'Quote + round photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();

