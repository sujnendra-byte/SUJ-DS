
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3 || !D.K4) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, K4 = D.K4, pad2 = D.u.pad2;
/* Template 81 - Duotone Pop : photos tinted magenta and navy, poster-size condensed headlines */
var BG = '#FFFFFF', NV = '#18204A', MG = '#E0287A', PK = '#FCE4EE', INK = '#18204A', MU = '#5A5F78';
var P0 = {bg:BG, text:INK, title:NV, muted:MU, accent:MG, accent2:NV, card:PK, line:'#EBD3DE', onAccent:'#FFFFFF'};
/* duotone: photo plus a colour wash */
function duo(C, x, y, w, h, c, op){ D.imgBox(C, x, y, w, h, {r:0}); C.rect(x, y, w, h, {fill:c, op:op == null ? 0.55 : op}); }
function bg(C, kind){ C.bg({fill:BG}); if (kind === 'cover' || kind === 'closing' || kind === 'quote') return; C.rect(0, 0, 14, H, {fill:MG}); }
function head(C, A, o){
  var y = 44;
  var te = C.text(A.x, y, Math.min(A.w, 1000), 110, (C.s.title || ''), {font:'head', fit:{max:62, min:28}, wt:400, color:NV, caps:true, lh:0.98, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:16, min:12}, wt:600, color:MG, lh:1.4, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
var X = {
  M:72, tc:'#FFFFFF', mc:'#F6D5E4', ac:'#FFFFFF', markS:46, g:16, head:head, ptc:INK, pmc:MU,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:PK}); C.rect(x, y, 10, h, {fill:MG}); return {tc:INK, mc:MU, ac:MG}; }
    if (o.stat) { C.rect(x, y, w, h, {fill:k % 2 ? MG : NV}); return {tc:'#FFFFFF', mc:'#F6D5E4', ac:'#FFFFFF'}; }
    C.rect(x, y, w, h, {fill:k % 2 ? MG : NV}); return {tc:'#FFFFFF', mc:k % 2 ? '#FFE3EF' : '#C9CDE6', ac:'#FFFFFF'};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, 8, h, {fill:k % 2 ? NV : MG}); C.rect(x + 8, y, w - 8, h, {fill:k % 2 ? '#F3F4F9' : PK}); return {tc:INK, mc:MU, ac:MG}; },
  mark:function(C, x, y, s, t, k, cc){ C.text(x - 4, y - 8, s + 30, s + 12, pad2(k + 1), {font:'head', size:s * 1.05, wt:400, color:'#FFFFFF', lh:1, va:'m'}); },
  rowMark:function(C, x, y, s, t, k){ C.text(x, y - 4, s + 20, s + 8, pad2(k + 1), {font:'head', size:s * 0.9, wt:400, color:k % 2 ? NV : MG, lh:1, va:'m'}); },
  node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:k % 2 ? MG : NV}); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.5, wt:400, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 4, x2 - x1, 8, {fill:PK}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { duo(C, 0, Z.y, W, Z.h, NV, 0.5); return; }
    duo(C, Z.x, 0, Z.w, H, side === 'right' ? MG : NV, 0.5);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.company || '', {font:'body', size:12, wt:700, color:NV, caps:true, ls:0.1, lh:1, va:'m'}); C.text(a.x + a.w - 60, y - 6, 60, 26, pad2(C.i + 1), {font:'head', size:22, color:MG, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[MG, NV, '#F48BB6', '#5D6AA8', PK, '#2F3A73'], header:{kind:'left', top:44}, card:{kind:'solid', fill:NV, r:0}, icon:{kind:'plain', fg:MG}, num:{kind:'big', fg:MG},
  conc:{kind:'band', fill:MG, r:0, label:'Bottom line', tc:'#FFFFFF', lc:'#FFFFFF'}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#E0287A'], [100, '#18204A']]}}};
/* signature: duotone strips - one tall tinted photo strip per point with the text over its foot */
function strips(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 700) return B.cards(C, Z, o);
  var g = 12, cw = (Z.w - g * (n - 1)) / n, tw = cw - 40, th = Z.h * 0.52;
  var F = K.fitHD(C, it, tw, 70, th - 40, {hMax:30, dMax:15.5, hMin:14, dMin:10});
  it.forEach(function(t, k){ var x = Z.x + k * (cw + g);
    duo(C, x, Z.y, cw, Z.h, k % 2 ? MG : NV, 0.62);
    C.rect(x, Z.y + Z.h - th, cw, th, {fill:k % 2 ? MG : NV, op:0.9});
    K.hd(C, x + 20, Z.y + Z.h - th + 20, tw, th - 30, t, F, {tc:'#FFFFFF', mc:'#FCE4EE'});
  });
}
/* signature: big outline numbers list */
function bigList(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cl = n > 3 && Z.w > 760 ? 2 : 1, per = Math.ceil(n / cl), gx = 40, cw = (Z.w - gx * (cl - 1)) / cl, rh = Z.h / per, s = Math.min(90, rh * 0.85), tw = cw - s - 20;
  var F = K.fitHD(C, it, tw, 40, rh - 10, {hMax:24, dMax:16, hMin:12, dMin:10, noBoost:n > 3});
  it.forEach(function(t, k){ var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, bh = K3.bH(C, t, tw, F);
    C.text(x, y + (rh - s) / 2, s + 10, s, String(k + 1), {font:'head', size:s, wt:400, color:k % 2 ? NV : MG, lh:1, va:'m'});
    K.hd(C, x + s + 20, y + (rh - bh) / 2, tw, rh, t, F, {tc:INK, mc:MU});
  });
}
/* signature: numbers over a duotone band */
function bandStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  duo(C, 0, Z.y, W, Z.h * 0.62, NV, 0.62);
  var cw = Z.w / n, vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 40;
  var vs = C.groupFit(vals, tw, Z.h * 0.4, {font:'head', max:92, min:30, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'body', max:17, min:12, maxLines:2});
  st.forEach(function(t, k){ var x = Z.x + k * cw;
    C.text(x, Z.y + Z.h * 0.31 - vs * 0.55, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:400, color:'#FFFFFF', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(x, Z.y + Z.h * 0.62 + 16, 50, 6, {fill:MG});
    C.text(x, Z.y + Z.h * 0.62 + 34, tw, 50, labs[k], {font:'body', size:ls, wt:700, color:INK, lh:1.2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, split = o.split;
  if (split) { duo(C, 560, 0, 720, H, MG, 0.55); C.rect(0, 0, 560, H, {fill:NV}); }
  else { duo(C, 0, 0, W, H, NV, 0.58); C.rect(0, 0, W * 0.5, H, {fill:MG, op:0.45}); }
  if (f.company) C.text(72, 60, 600, 20, f.company, {font:'body', size:14, wt:700, color:'#FFFFFF', caps:true, ls:0.18, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 100, split ? 460 : 900, 400, C.s.title, {font:'head', fit:{max:140, min:40}, wt:400, color:'#FFFFFF', caps:true, lh:0.92, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(72, 520, split ? 440 : 700, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, wt:600, color:PK, lh:1.35, ed:{f:'subtitle'}, maxLines:2});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(72, 620, 700, 22, who, {font:'body', size:14, wt:700, color:'#FFFFFF', caps:true, ls:0.1, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {};
  if (o.img === 'photo') duo(C, 0, 0, W, H, MG, 0.62); else C.rect(0, 0, W, H, {fill:MG});
  var t = C.s.title || 'Thank you';
  C.text(72, 30, W - 144, 170, t, {font:'head', fit:{max:150, min:50}, wt:400, color:NV, caps:true, lh:0.9, op:0.35, maxLines:1});
  C.text(72, 206, W - 144, 170, t, {font:'head', fit:{max:150, min:50}, wt:400, color:'#FFFFFF', caps:true, lh:0.9, ed:{f:'title'}, maxLines:1});
  if (C.s.subtitle) C.text(72, 410, 760, 50, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, wt:600, color:'#FFFFFF', lh:1.35, ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C), cw = (W - 144) / Math.max(1, cs.length);
  cs.forEach(function(c, k){ var x = 72 + k * cw; C.rect(x, 520, cw - 16, 110, {fill:NV}); C.icon(x + 20, 540, 22, c.icon, MG); C.text(x + 20, 574, cw - 56, 46, c.val, {font:'body', fit:{max:16, min:11}, wt:700, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:2}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  duo(C, 0, 0, W, H, NV, 0.66);
  C.rect(72, 120, 16, 360, {fill:MG});
  var qe = C.text(120, 110, W - 220, 400, C.s.title, {font:'head', fit:{max:76, min:28}, wt:400, color:'#FFFFFF', caps:true, lh:1.0, va:'m', ed:{f:'title'}, maxLines:5});
  if (by) C.text(120, 540, W - 220, 26, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:17, wt:700, color:PK, caps:true, ls:0.1, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var L = {cards:'Colour blocks', rows:'Big-number rows', steps:'Square steps', stmt:'Pink panel', stats:'Colour numbers', photo:'duotone photo', track:'#FCE4EE'};
var VS = K4.vset(pg, B, L, {
  cover:[['Full duotone poster', {img:'photo'}], ['Navy block + duotone', {img:'photo', split:true}]],
  closing:[['Magenta thank you', {}], ['Duotone thank you', {img:'photo'}]],
  quote:[['Poster quote', {img:'photo'}]],
  agenda:[['Big numbers', pg(bigList)]], p56:[['Big numbers', pg(bigList)]], p78:[['Big numbers', pg(bigList, {max:8})]],
  p2:[['Duotone strips', pg(strips)]], p3:[['Duotone strips', pg(strips)]], p4:[['Duotone strips', pg(strips)]],
  stats:[['Numbers on duotone band', pg(bandStats)]]
});
D.register({
  id:'duotone', name:'Duotone Pop', tag:'Magenta + navy duotone',
  desc:'Photos tinted magenta and navy with giant poster-size condensed headlines, colour blocks and outlined numbers. Loud and fashionable for brand launches, fashion, events and marketing campaigns.',
  fonts:{head:'Anton', body:'Hind', headW:400, bodyW:400, headLh:0.98, gf:'Anton|Hind:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote, variants:VS
});
})();

