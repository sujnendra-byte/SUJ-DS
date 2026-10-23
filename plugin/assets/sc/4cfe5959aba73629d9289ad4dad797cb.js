
/* Template 09 — Midnight Neon : black, blue→violet gradient cards, arrow chips (AI & Tech pitch sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#060609', text:'#FFFFFF', title:'#FFFFFF', muted:'#A9ACBD', accent:'#6C5CFF', accent2:'#C45BFF', card:'#15151E', line:'#2A2A38', onAccent:'#FFFFFF'};
var GR = {ang:135, stops:[[0,'#2F63FF'],[100,'#B24DFF']]};
var S = {
  margin:68,
  header:{kind:'left', tMax:46, tMin:26, sMax:16, top:78},
  card:{kind:'solid', r:18, fill:'#14141C', line:'#2A2A38', lw:1, hl:{kind:'grad', grad:GR, dark:true, tc:'#FFFFFF', mc:'#EDE6FF'}},
  icon:{kind:'circle', grad:GR, bg:'#6C5CFF', fg:'#FFFFFF'},
  num:{kind:'circle', bg:'#6C5CFF', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#6C5CFF', op:0.16, line:'#6C5CFF', lw:1, label:'Takeaway', lc:'#B9A6FF'},
  footer:{pagePrefix:''},
  imgFramed:true, imgR:20, imgPh:{grad:{ang:135, stops:[[0,'#1F3FB8'],[100,'#7C2FD1']]}}
};
function brand(C){
  var name = C.cover.company || 'Company';
  C.path([['M',68,36],['L',82,50],['M',82,36],['L',68,50]], {stroke:'#B24DFF', lw:3});
  C.text(92, 32, 300, 22, name, {font:'body', size:14, wt:600, color:'#FFFFFF', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#060609'});
  var k = C.i % 3;
  if (k === 0) { D.glow(C, 1280, 0, 420, 320, '#2F63FF', 0.55); D.glow(C, 0, 720, 360, 260, '#7C2FD1', 0.35); }
  else if (k === 1) { D.glow(C, 0, 0, 420, 320, '#2F63FF', 0.5); D.glow(C, 1280, 720, 380, 260, '#B24DFF', 0.35); }
  else { D.glow(C, 1280, 720, 460, 320, '#2F63FF', 0.5); D.glow(C, 640, -40, 360, 160, '#7C2FD1', 0.35); }
  brand(C);
}
function chip(C, x, y, w, label){
  C.rect(x, y, w, 30, {line:'#B24DFF', lw:1.4, r:15});
  C.text(x + 14, y, w - 44, 30, label, {font:'body', size:12, wt:600, color:'#D9CCFF', va:'m', lh:1, maxLines:1});
  C.icon(x + w - 26, y + 7, 16, 'mdi:arrow-top-right', '#D9CCFF');
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  var cols = [[70,80],[70,250],[70,420],[230,80],[230,250],[230,420]];
  if (o.img) D.imgBox(C, 70, 80, 340, 520, {r:170});
  else cols.forEach(function(p, k){ C.ell(p[0], p[1], 150, 150, {grad:{ang:135 + k * 20, stops:[[0,'#2F63FF'],[100,'#B24DFF']]}, op:0.35 + (k % 3) * 0.2}); });
  C.text(470, 150, 740, 280, C.s.title, {font:'head', fit:{max:72, min:34}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.05, va:'b'});
  C.rect(472, 456, 210, 42, {grad:GR, r:21});
  C.text(472, 456, 210, 42, f.tagline || 'PITCH DECK', {font:'body', size:15, wt:600, color:'#FFFFFF', align:'c', va:'m', caps:true, ls:0.08, lh:1, ed:f.tagline ? {f:'tf-tagline'} : null, maxLines:1});
  if (C.s.subtitle) C.text(472, 530, 620, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#D2D4E0', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  C.icon(W - 110, H - 110, 34, 'mdi:arrow-top-right', '#FFFFFF');
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  D.imgBox(C, 68, 110, 560, 270, {r:18});
  D.imgBox(C, 650, 110, 560, 270, {r:18});
  C.text(68, 430, 420, 170, C.s.title || 'Thank You for Watching', {font:'head', fit:{max:56, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, lh:1.08, va:'t'});
  if (C.s.subtitle) C.text(520, 434, 280, 150, C.s.subtitle, {font:'body', fit:{max:15, min:11}, color:'#A9ACBD', ed:{f:'subtitle'}, lh:1.45, maxLines:6});
  D.contactList(C, 840, 430, 380, {gap:52, markO:{kind:'ring', bg:'#B24DFF', fg:'#D9CCFF'}});
}
function agenda(C, v){
  var P = C.P, it = C.it.slice(0, 8), n = it.length, M = 68;
  C.text(M, 90, 420, 200, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.05});
  if (C.s.subtitle) C.text(M, 300, 380, 110, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var cols = 2, per = Math.ceil(n / cols), x0 = 520, cw = 330, rh = Math.min(130, 520 / per);
  it.forEach(function(t, k){
    var c = k % 2, r = Math.floor(k / 2), x = x0 + c * (cw + 20), y = 100 + r * rh;
    C.rect(x, y, cw, rh - 14, {grad:k === 0 ? GR : null, fill:k === 0 ? null : '#14141C', line:'#2A2A38', lw:1, r:16});
    C.text(x + 20, y + 12, 80, 34, D.u.pad2(k + 1), {font:'head', size:26, color:k === 0 ? '#FFFFFF' : '#B9A6FF', lh:1, caps:false});
    C.icon(x + cw - 38, y + 16, 18, 'mdi:arrow-top-right', '#FFFFFF');
    C.text(x + 20, y + 50, cw - 40, rh - 72, t.h || t.d, {font:'head', fit:{max:19, min:12}, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#B24DFF'}); return; }
  C.rect(150, 140, W - 300, H - 280, {fill:'#14141C', line:'#2A2A38', lw:1, r:24});
  D.B.quote(C, {x:210, y:170, w:W - 420, h:H - 340}, {qc:'#B24DFF'});
}
D.register({
  id:'neon', name:'Midnight Neon', tag:'Black & neon · pitch', desc:'Midnight black with electric blue-to-violet gradient cards, glow corners and arrow chips.',
  fonts:{head:'Manrope', body:'Manrope', headW:700, bodyW:400, headLh:1.08, gf:'Manrope:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Neon circles',{}), V('v2','Arch image',{img:'left'})],
    agenda:[V('v1','Neon tiles',{})],
    p2:[V('v1','Gradient panels',{body:'duo', hl:true}), V('n2','Panels + VS',{body:'duo', vs:true}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Neon cards',{body:'cols', hl:'first'}), V('n2','Gradient bars',{body:'bars', grad:function(k){ return {ang:90, stops:[[0,['#2F63FF','#7C4DFF','#B24DFF'][k%3]],[100,'#14141C']]}; }}), V('n3','Numbered list',{body:'list', mark:'num', rules:true}), V('v2','Image left + cards',{body:'list', img:'left', cards:true}), V('v3','Cards + image right',{body:'list', img:'right', cards:true}), V('v4','Cards + image bottom',{body:'cols', img:'bottom'})],
    p4:[V('v1','Neon grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', hl:'alt'}), V('n3','Hub diagram',{body:'hub', hubGrad:GR}), V('v2','Image left + grid',{body:'list', img:'left', cards:true}), V('v3','Grid + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Neon grid',{body:'grid', hl:'first'}), V('n2','Gradient bars',{body:'bars'}), V('n3','Two-column list',{body:'list', cols:2, mark:'num'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Neon grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Gradient bars',{body:'statBars'}), V('n2','Neon stat cards',{body:'statCards', hl:'first'}), V('n3','Big numbers',{body:'statCols', vc:'#B9A6FF'}), V('n4','Progress rings',{body:'statRings', track:'#1E1E2A'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Neon milestones',{body:'tlCards', hlLast:true}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Card quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

