
/* Template 03 — Chrome Noir : black, silver tiles, chrome ribbons (Digital Marketing sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#0B0B0D', text:'#F2F2F4', title:'#FFFFFF', muted:'#A6A8AE', accent:'#5FD3E0', accent2:'#8FE9F2', card:'#26272B', line:'#3A3B40', onAccent:'#0B0B0D'};
var S = {
  margin:64,
  header:{kind:'left', tMax:48, tMin:26, sMax:17, top:48, kicker:function(C){ return C.cover.company || ''; }, kPill:'#C9CBD1', kc:'#E4E5E8', kSize:11, kGap:46},
  card:{kind:'solid', r:10, fill:'#232428', line:'#34353A', lw:1, hl:{kind:'solid', fill:'#F2F2F4', dark:false, tc:'#0B0B0D', mc:'#3A3B40'}},
  icon:{kind:'square', r:12, bg:'#3A3B41', fg:'#F2F2F4'},
  num:{kind:'pill', bg:'#3A3B41', fg:'#F2F2F4'},
  conc:{kind:'box', fill:'#1B1C20', line:'#3A3B40', lw:1, r:10, lc:'#5FD3E0', label:'Key takeaway'},
  footer:{brand:false, pagePrefix:''},
  imgFramed:true, imgR:12
};
/* clean concentric rings (true circles) with an optional round image in the middle */
function ringSet(C, cx, cy, o){
  o = o || {};
  C.ell(cx - 330, cy - 330, 660, 660, {fill:'#0B0B0D'});
  D.ring(C, cx, cy, 258, {color:'#EDEDF0', lw:3});
  D.ring(C, cx, cy, 316, {color:'#EDEDF0', lw:3});
  D.ring(C, cx, cy, 410, {color:'#F2F2F4', lw:8});
  if (o.img) D.imgBox(C, cx - 208, cy - 208, 416, 416, {shape:'ell'});
  else {
    C.ell(cx - 208, cy - 208, 416, 416, {grad:{type:'radial', stops:[[0,'#3A3B41'],[100,'#141417']]}});
    D.ring(C, cx, cy, 208, {color:'#5A5C62', lw:1.5, op:0.8});
  }
  var sa = (o.sparkA != null ? o.sparkA : -78) * Math.PI / 180;
  D.sparkle(C, cx + 316 * Math.cos(sa), cy + 316 * Math.sin(sa), 28, '#FFFFFF', 1);
}
/* small corner rings (content slides) */
function cornerRings(C, cx, cy, a0, a1, spark){
  C.ell(cx - 62, cy - 62, 124, 124, {fill:'#3A3B40'});
  [92, 116, 140].forEach(function(r, k){ D.arc(C, cx, cy, r, a0, a1, {color:'#CFD1D6', lw:k === 2 ? 2.2 : 1.6, op:0.85}); });
  if (spark) D.sparkle(C, spark[0], spark[1], 14, '#FFFFFF', 0.9);
}
function bg(C, kind){
  C.bg({grad:{type:'radial', stops:[[0,'#2A2B31'],[70,'#121215'],[100,'#08080A']]}});
  D.glow(C, 1180, 40, 300, 220, '#5FD3E0', 0.08);
  if (kind !== 'cover' && kind !== 'closing' && kind !== 'quote') cornerRings(C, W, 0, 90, 180, [W - 150, 152]);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  var right = o.side === 'r';
  if (right) ringSet(C, W - 130, 370, {img:!!o.img, sparkA:-102});
  else ringSet(C, 130, 370, {img:!!o.img});
  var x = right ? 64 : 610, w = right ? 600 : W - x - 60;
  if (f.company) { C.rect(x, 64, Math.min(260, f.company.length * 9 + 40), 28, {line:'#C9CBD1', lw:1.2, r:14}); C.text(x, 64, Math.min(260, f.company.length * 9 + 40), 28, f.company, {font:'body', size:11, wt:700, color:'#E4E5E8', align:'c', va:'m', lh:1, ed:{f:'tf-company'}}); }
  C.text(x, 130, w, 330, C.s.title, {font:'head', fit:{max:72, min:34}, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.02, ls:0.02});
  if (C.s.subtitle) C.text(x, 480, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var lines = [f.website, f.presenter ? f.presenter + (f.designation ? ', ' + f.designation : '') : '', f.date].filter(Boolean);
  lines.forEach(function(l, k){ C.text(x, 590 + k * 24, w, 20, '•  ' + l, {font:'body', size:13, color:'#D0D2D6', lh:1}); });
}
/* closing: rings (with a round image) anchored in one corner, text on the open side */
var CORNERS = {
  tr:{cx:W - 130, cy:130, sa:200, tx:64},
  tl:{cx:130, cy:130, sa:-20, tx:600},
  br:{cx:W - 130, cy:H - 130, sa:160, tx:64},
  bl:{cx:130, cy:H - 130, sa:20, tx:600},
  r:{cx:W - 130, cy:360, sa:-102, tx:64}
};
function closing(C, v){
  var P = C.P, o = C.o || {}, K = CORNERS[o.corner];
  var tx = 64, tw = 640, dy = o.corner === 'br' || o.corner === 'bl' ? -130 : 0;   /* rings at the bottom → lift the text so the slide balances */
  if (K) { ringSet(C, K.cx, K.cy, {img:!!o.img, sparkA:K.sa}); tx = K.tx; tw = K.tx > 64 ? W - K.tx - 64 : 640; }
  else ringSet(C, W - 130, 360, {sparkA:-102});
  C.text(tx, 170 + dy, tw, 300, C.s.title || 'Thank You', {font:'head', fit:{max:110, min:48}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1.0, ls:0.02});
  if (C.s.subtitle) C.text(tx, 490 + dy, Math.min(600, tw), 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactRow(C, tx, 580 + dy, K && K.tx > 64 ? tw : 690, {cards:true});
}
function agenda(C, v){
  var P = C.P, M = 64;
  C.text(M, 56, 800, 70, C.s.title || 'Table of Content', {font:'head', fit:{max:50, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, ls:0.04});
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols), cw = cols === 2 ? 560 : 900, rh = Math.min(84, (H - 260) / per), top = 150 + ((H - 260) - rh * per) / 2;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = M + c * (cw + 30), y = top + r * rh;
    C.rect(x, y, cw, rh - 12, {fill:'#2A2B30', r:6});
    C.text(x + 18, y, 60, rh - 12, D.u.pad2(k + 1), {font:'head', size:28, color:'#FFFFFF', va:'m', lh:1, ls:0});
    C.text(x + 80, y, cw - 96, rh - 12, t.h || t.d, {font:'body', fit:{max:21, min:11}, wt:700, color:'#F2F2F4', va:'m', lh:1.1, ls:0.03, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
  if (C.s.subtitle) C.text(M, H - 76, 900, 30, C.s.subtitle, {font:'body', fit:{max:15, min:11}, color:P.muted, ed:{f:'subtitle'}, maxLines:1});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#5FD3E0'}); return; }
  cornerRings(C, 0, H, 270, 360, [150, H - 152]);
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:'#C9CBD1'});
}
D.register({
  id:'chrome', name:'Chrome Noir', tag:'Black & silver · agency', desc:'Deep black canvas, graphite tiles, clean chrome rings and sparkle highlights.',
  fonts:{head:'Space Grotesk', body:'DM Sans', headW:700, bodyW:400, headLh:1.05, headLs:0.02, gf:'Space Grotesk:wght@500;600;700|DM Sans:wght@400;500;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v2','Rings + round image',{img:'ring', def:true}), V('v3','Image right + rings',{img:'ring', side:'r'}), V('v1','Chrome rings',{})],
    closing:[V('v2','Rings top right + image',{img:'ring', corner:'tr', def:true}), V('v3','Rings top left + image',{img:'ring', corner:'tl'}), V('v4','Rings bottom right + image',{img:'ring', corner:'br'}), V('v5','Rings bottom left + image',{img:'ring', corner:'bl'}), V('v6','Rings right + image',{img:'ring', corner:'r'})],
    agenda:[V('v1','Content tiles',{})],
    p2:[V('v1','Graphite panels',{body:'duo'}), V('n2','Panels + VS',{body:'duo', vs:true}), V('v2','Image left + tiles',{body:'list', img:'left', cards:true}), V('v3','Tiles + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Service tiles',{body:'list', cards:true}), V('n2','Three columns',{body:'cols'}), V('n3','Numbered columns',{body:'numbered', lineC:'#3A3B40'}), V('v2','Image left + tiles',{body:'list', img:'left', cards:true}), V('v3','Tiles + image right',{body:'list', img:'right', cards:true})],
    p4:[V('v1','Graphite grid',{body:'grid'}), V('n2','Four columns',{body:'cols', hl:'first'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + tiles',{body:'list', img:'left', cards:true}), V('v3','Tiles + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Graphite grid',{body:'grid'}), V('n2','Two-column tiles',{body:'list', cols:2, cards:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Graphite grid',{body:'grid'}), V('n2','Two-column tiles',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Ring tiles',{body:'statRings', cardBg:true, track:'#3A3B40'}), V('n2','Stat tiles',{body:'statCards', icons:true}), V('n3','Big numbers',{body:'statCols'}), V('n4','Progress bars',{body:'statBars'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Milestone tiles',{body:'tlCards'}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Chrome quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

