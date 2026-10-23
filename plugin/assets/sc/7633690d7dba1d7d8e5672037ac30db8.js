
/* Template 06 — Noir Gold : jet black, gold + violet accents, orbit lines & sparkles (Artificial Intelligence sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#050505', text:'#FFFFFF', title:'#FFFFFF', muted:'#B9B9BD', accent:'#F5C542', accent2:'#A63CF2', card:'#121214', line:'#2E2E33', onAccent:'#111111'};
var S = {
  margin:64,
  header:{kind:'left', tMax:50, tMin:26, sMax:16, top:96},
  card:{kind:'solid', r:14, fill:'#141416', line:'#2C2C31', lw:1, hl:{kind:'solid', fill:'#F5C542', dark:false, tc:'#111111', mc:'#3A3320'}},
  icon:{kind:'circle', bg:'#A63CF2', fg:'#FFFFFF'},
  num:{kind:'circle', bg:'#F5C542', fg:'#111111'},
  conc:{kind:'pill', fill:'#F5C542', op:1, label:'Key insight', lc:'#111111', tc:'#111111', chip:false},
  footer:{fn:foot},
  imgFramed:true, imgR:16, imgPh:{grad:{ang:135, stops:[[0,'#2A1A45'],[100,'#6B4A12']]}}
};
function foot(C){
  var P = C.P;
  if (C.cover.website) C.text(64, H - 40, 400, 16, C.cover.website, {font:'body', size:11.5, color:'#9A9AA0', lh:1});
  C.ell(W - 64 - 22, H - 46, 22, 22, {fill:'#F5C542'});
  C.text(W - 64 - 22, H - 46, 22, 22, '→', {font:'body', size:13, wt:700, color:'#111111', align:'c', va:'m', lh:1});
}
function nav(C){
  var name = C.cover.company || 'Company';
  C.text(64, 34, 220, 22, name, {font:'head', size:15, wt:600, color:'#FFFFFF', caps:true, lh:1, va:'m', ls:0.04});
  C.text(W - 480, 34, 80, 22, 'About', {font:'body', size:12, color:'#8E8E94', va:'m', lh:1});
  C.text(W - 400, 34, 70, 22, 'Home', {font:'body', size:12, wt:700, color:'#FFFFFF', va:'m', lh:1});
  C.text(W - 330, 34, 80, 22, 'Contact', {font:'body', size:12, color:'#8E8E94', va:'m', lh:1});
  C.rect(W - 230, 30, 90, 28, {fill:'#A63CF2', r:14});
  C.text(W - 230, 30, 90, 28, 'Join Us', {font:'body', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
  C.icon(W - 124, 34, 20, 'mdi:magnify', '#FFFFFF');
  C.icon(W - 92, 34, 20, 'mdi:menu', '#FFFFFF');
}
function orbit(C, k){
  if (k % 2 === 0) { D.arc(C, 1180, -40, 300, 95, 200, {color:'#FFFFFF', lw:1.4, op:0.7}); D.arc(C, 200, 800, 260, 250, 340, {color:'#FFFFFF', lw:1.4, op:0.55}); }
  else { D.arc(C, 90, -60, 280, 20, 120, {color:'#FFFFFF', lw:1.4, op:0.7}); D.arc(C, 1150, 820, 240, 190, 290, {color:'#FFFFFF', lw:1.4, op:0.55}); }
  D.sparkle(C, 1060 - (k % 3) * 40, 150 + (k % 2) * 380, 16, '#F5C542', 1);
  D.sparkle(C, 640 + (k % 4) * 30, 690, 14, '#F5C542', 1);
  D.sparkle(C, 40 + (k % 2) * 1180, 380, 12, '#A63CF2', 1);
}
function bg(C, kind){
  C.bg({fill:'#050505'});
  orbit(C, C.i);
  nav(C);
}
function twoTone(C, x, y, w, h, title, max, al){
  var words = String(title || '').split(/\s+/), cut = Math.max(1, Math.floor(words.length / 2));
  var a = words.slice(0, cut).join(' '), b = words.slice(cut).join(' ');
  var r = C.measureText(title, w, {font:'head', max:max, min:30, h:h, maxLines:3});
  C.text(x, y, w, h / 2, a, {font:'head', size:r.s, color:'#FFFFFF', lh:1.05, maxLines:2, align:al || 'l', va:'b', ed:{f:'x-tpart', p:0}});
  C.text(x, y + h / 2 + 4, w, h / 2, b, {font:'head', size:r.s, color:'#F5C542', lh:1.05, maxLines:2, ed:{f:'x-tpart', p:1}, align:al || 'l'});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, 780, 110, 420, 520, {r:200});
  C.text(40, 90, o.img ? 720 : 1200, 390, C.s.title, {font:'head', fit:{max:132, min:44}, wt:400, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:0.95, va:'b'});
  C.rect(40, 520, 30, 3, {fill:'#F5C542'});
  if (C.s.subtitle) C.text(84, 506, 640, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#D8D8DC', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(64, 600, 260, 36, {fill:'#F5C542', r:18});
  C.text(64, 600, 220, 36, 'START PRESENTATION', {font:'body', size:13, wt:700, color:'#111111', align:'c', va:'m', lh:1});
  C.ell(292, 602, 32, 32, {fill:'#A63CF2'});
  C.text(292, 602, 32, 32, '→', {font:'body', size:16, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function closing(C, v){
  var P = C.P;
  C.text(64, 150, W - 128, 190, C.s.title || 'Thank You', {font:'head', fit:{max:140, min:50}, wt:400, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, align:'c', lh:1, va:'m'});
  if (C.s.subtitle) C.text(200, 350, W - 400, 60, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:'#D8D8DC', ed:{f:'subtitle'}, align:'c', lh:1.45, maxLines:2});
  D.contactRow(C, 140, 470, W - 280, {cards:true, lc:'#F5C542'});
}
function agenda(C, v){
  var P = C.P, M = 64, it = C.it.slice(0, 8), n = it.length;
  twoTone(C, M, 100, 520, 200, C.s.title || 'Agenda Overview', 60);
  if (C.s.subtitle) C.text(M, 320, 480, 100, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var x = 640, w = W - 64 - x, rh = Math.min(64, 520 / Math.max(1, n)), y0 = 110 + (520 - rh * n) / 2;
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    D.num(C, x, y + (rh - 40) / 2, 40, k + 1, {});
    C.text(x + 58, y, w - 60, rh, t.h || t.d, {font:'head', fit:{max:20, min:12}, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#F5C542'}); return; }
  D.B.quote(C, {x:180, y:140, w:W - 360, h:H - 280}, {qc:'#F5C542', qFont:'head'});
}
D.register({
  id:'noirgold', name:'Noir Gold', tag:'Black & gold · tech', desc:'Jet-black canvas with gold and violet highlights, orbit lines, sparkles and a web-style header.',
  fonts:{head:'Jost', body:'Jost', headW:500, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Jost:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Giant headline',{}), V('v2','Headline + arch image',{img:'right'})],
    agenda:[V('v1','Gold numbered list',{})],
    p2:[V('v1','Two panels',{body:'duo', hl:true}), V('n2','Numbered list',{body:'list', mark:'num'}), V('v2','Images right + list',{body:'list', img:'right', mark:'num'}), V('v3','Image left + list',{body:'list', img:'left', mark:'num'})],
    p3:[V('v1','Icon columns',{body:'cols', center:true, pad:22}), V('n2','Gold card + list',{body:'cols', hl:'first'}), V('n3','Numbered list',{body:'list', mark:'num', rules:true}), V('v2','Image right + list',{body:'list', img:'right'}), V('v3','Image left + cards',{body:'list', img:'left', cards:true}), V('v4','Columns + image bottom',{body:'cols', img:'bottom', center:true})],
    p4:[V('v1','Dark grid',{body:'grid', hl:'first'}), V('n2','Four icon columns',{body:'cols', center:true}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + list',{body:'list', img:'left'}), V('v3','List + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Dark grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Dark grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Gold numbers',{body:'statCols'}), V('n2','Stat cards',{body:'statCards', icons:true, hl:'first'}), V('n3','Progress rings',{body:'statRings', track:'#2A2A2F'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Orbit line',{body:'tlH'}), V('n2','Milestone cards',{body:'tlCards', hlLast:true}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Gold quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

