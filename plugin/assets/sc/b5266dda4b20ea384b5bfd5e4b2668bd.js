
/* Template 10 — Bronze Executive : espresso + bronze glow, frosted panels, tall condensed caps (Pitch Deck 2 sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#1C1815', text:'#FFFFFF', title:'#FFFFFF', muted:'#D2C7BC', accent:'#E8903F', accent2:'#F4B878', card:'#FFFFFF', line:'#5E544C', onAccent:'#1C1815'};
var S = {
  margin:72,
  header:{kind:'split', tMax:52, tMin:28, sMax:15, top:84},
  card:{kind:'glass', r:14, fill:'#FFFFFF', op:0.09, line:'#FFFFFF', lop:0.18, lw:1, dark:true, tc:'#FFFFFF', mc:'#D8CEC4', hl:{kind:'grad', grad:{ang:135, stops:[[0,'#E8903F'],[100,'#B8612A']]}, dark:true, tc:'#FFFFFF', mc:'#FFE9D6'}},
  icon:{kind:'plain', fg:'#FFFFFF'},
  num:{kind:'big', fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#FFFFFF', op:0.08, line:'#FFFFFF', lw:1, r:12, lc:'#F4B878', label:'Bottom line'},
  footer:{pagePrefix:'PAGE ', color:'#A89A8D'},
  bodyGap:26,
  imgFramed:true, imgR:12, imgPh:{grad:{ang:135, stops:[[0,'#6B4A33'],[100,'#2E2622']]}}
};
function frame(C){
  C.rect(28, 28, W - 56, H - 56, {fill:'#FFFFFF', op:0.05, line:'#FFFFFF', lop:0.14, lw:1, r:18});
  var name = C.cover.company || 'Company';
  C.path([['M',72,48],['L',72,62],['L',82,62]], {stroke:'#FFFFFF', lw:2.4});
  C.text(90, 44, 400, 20, name, {font:'body', size:13, color:'#FFFFFF', va:'m', lh:1});
  C.icon(W - 116, 46, 18, 'mdi:magnify', '#FFFFFF');
  C.icon(W - 88, 46, 18, 'mdi:menu', '#FFFFFF');
  C.rect(72, 72, W - 144, 1, {fill:'#FFFFFF', op:0.25});
}
function bg(C, kind){
  C.bg({fill:'#16120F'});
  var k = C.i % 2;
  D.glow(C, k ? 1100 : 180, 720, 620, 360, '#A2521B', 0.85);
  D.glow(C, k ? 200 : 1080, 0, 460, 260, '#6E3A18', 0.55);
  frame(C);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.text(72, 120, W - 144, 200, C.s.title, {font:'head', fit:{max:120, min:48}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, lh:0.98, va:'b', ls:0.01});
  if (C.s.subtitle) C.text(72, 330, W - 144, 60, C.s.subtitle, {font:'body', fit:{max:28, min:15}, color:'#EFE6DD', ed:{f:'subtitle'}, lh:1.25, maxLines:2});
  C.rect(92, 460, 560, 140, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  D.imgBox(C, 112, 486, 88, 88, {r:44, shape:'ell'});
  C.text(220, 486, 140, 16, 'Presented by', {font:'body', size:12, color:'#D2C7BC', lh:1});
  C.text(220, 506, 400, 22, f.presenter || f.company || '', {font:'body', size:17, wt:700, color:'#FFFFFF', lh:1, ed:{f:f.presenter ? 'tf-presenter' : 'tf-company'}});
  if (f.designation) C.text(220, 534, 400, 18, f.designation, {font:'body', size:13, color:'#D2C7BC', lh:1, ed:{f:'tf-designation'}});
  if (f.website) C.text(420, 486, 220, 16, f.website, {font:'body', size:12, wt:700, color:'#FFFFFF', lh:1, ed:{f:'tf-website'}});
  C.rect(690, 460, 300, 140, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  C.text(714, 480, 260, 60, f.date || f.tagline || 'Strategy briefing', {font:'head', fit:{max:32, min:16}, color:'#F4B878', lh:1, maxLines:1, va:'m'});
  C.text(714, 540, 260, 44, f.tagline && f.date ? f.tagline : 'Confidential board presentation', {font:'body', fit:{max:13, min:10}, color:'#D2C7BC', lh:1.3, maxLines:2});
}
function closing(C, v){
  var P = C.P, f = C.cover;
  C.text(72, 130, W - 144, 170, C.s.title || 'Thank You', {font:'head', fit:{max:140, min:56}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, lh:1, align:'c', va:'b'});
  if (C.s.subtitle) C.text(160, 310, W - 320, 50, C.s.subtitle, {font:'body', fit:{max:26, min:14}, color:'#EFE6DD', ed:{f:'subtitle'}, lh:1.25, align:'c', maxLines:2});
  C.rect(92, 450, 700, 150, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  D.contactList(C, 118, 470, 650, {gap:40, markO:{kind:'plain', fg:'#F4B878'}});
  C.rect(820, 450, 368, 150, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  if (C.s.conclusion) C.text(846, 470, 316, 110, C.s.conclusion, {font:'body', fit:{max:17, min:11}, color:'#FFFFFF', ed:{f:'x-conc'}, lh:1.35, maxLines:4, va:'m'});
}
function agenda(C, v){
  var P = C.P;
  C.text(72, 90, W - 144, 80, C.s.title || 'List of Contents', {font:'head', fit:{max:58, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  D.B.agTiles(C, {x:72, y:190, w:W - 144, h:430}, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#E8903F'}); return; }
  D.B.quote(C, {x:150, y:130, w:W - 300, h:H - 250}, {qc:'#E8903F', qFont:'head', qMax:44});
}
D.register({
  id:'bronze', name:'Bronze Executive', tag:'Espresso & bronze · executive', desc:'Espresso darkness with bronze light, frosted glass panels and tall condensed headlines.',
  fonts:{head:'Oswald', body:'Source Sans 3', headW:700, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Oswald:wght@500;600;700|Source Sans 3:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Glass cards',{})],
    agenda:[V('v1','Content tiles',{})],
    p2:[V('v1','Vision / mission panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Service panels',{body:'list', cards:true}), V('n2','Frosted columns',{body:'cols', hl:'first'}), V('n3','Numbered columns',{body:'numbered', lineC:'#5E544C'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true}), V('v4','Image top + columns',{body:'cols', img:'top'})],
    p4:[V('v1','Frosted grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', mark:'num'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Frosted grid',{body:'grid', hl:'first'}), V('n2','Two-column panels',{body:'list', cols:2, cards:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Frosted grid',{body:'grid'}), V('n2','Two-column panels',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Big numbers',{body:'statCols', vc:'#FFFFFF', vMax:104}), V('n2','Frosted stat panels',{body:'statCards', hl:'first'}), V('n3','Progress bars',{body:'statBars'}), V('v2','Image left + numbers',{body:'statBars', img:'left'})],
    timeline:[V('v1','Frosted milestones',{body:'tlCards', hlLast:true}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Bronze quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

