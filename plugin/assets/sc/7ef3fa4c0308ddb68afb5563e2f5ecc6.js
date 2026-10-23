
/* Template 04 — Aqua Wave : white, cyan line waves, navy type (AI Business Solutions sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#FFFFFF', text:'#16325C', title:'#16325C', muted:'#4A5D78', accent:'#1FB5EC', accent2:'#16325C', card:'#EFF9FE', line:'#CFE8F5', onAccent:'#16325C'};
var S = {
  margin:64,
  header:{kind:'left', tMax:44, tMin:26, sMax:17, top:52},
  card:{kind:'solid', r:18, fill:'#F1FAFE', line:'#D6EEF9', lw:1, hl:{kind:'solid', fill:'#16325C', dark:true, tc:'#FFFFFF', mc:'#CFE3F5'}},
  icon:{kind:'circle', bg:'#5BD0F6', fg:'#16325C'},
  num:{kind:'circle', bg:'#5BD0F6', fg:'#16325C', font:'body'},
  conc:{kind:'box', fill:'#EAF7FD', r:14, lc:'#1592C4', label:'Key message'},
  footer:{pagePrefix:'PAGE '},
  imgFramed:true, imgR:24, imgPh:{grad:{ang:135, stops:[[0,'#BDEBFB'],[100,'#5BA7D9']]}}
};
function waves(C, x0, y0, x1, y1, n, amp, op){ D.waveBundle(C, {x0:x0, y0:y0, x1:x1, y1:y1, n:n || 16, amp:amp || 80, spread:5, color:'#34C1F2', lw:1.1, op:op || 0.55, fade:true}); }
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  var alt = C.i % 3;
  C.ell(W - 260, -220, 520, 520, {fill:'#D8F3FD', op:0.85});
  if (alt === 0) waves(C, 700, 720, 1300, 380, 18, 70, 0.5);
  else if (alt === 1) waves(C, -20, 760, 520, 560, 14, 60, 0.45);
  else waves(C, 900, -20, 1300, 260, 14, 50, 0.45);
  C.ell(-120, 600, 300, 300, {fill:'#E4F7FE', op:0.9});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.bg({grad:{ang:160, stops:[[0,'#F3FBFF'],[100,'#DDF3FC']]}});
  D.waveBundle(C, {x0:-40, y0:560, x1:1320, y1:300, n:26, amp:110, spread:6, color:'#34C1F2', lw:1.1, op:0.6, fade:true});
  C.ell(760, -240, 700, 700, {fill:'#BEEBFB', op:0.6});
  if (o.img) D.imgBox(C, 700, 60, 520, 440, {r:28});
  if (f.company) { C.rect(64, 330, 14, 14, {fill:'#16325C', r:3}); C.text(88, 326, 400, 22, f.company, {font:'head', size:16, color:'#16325C', va:'m', lh:1, ed:{f:'tf-company'}}); }
  C.text(64, 360, o.img ? 600 : 900, 180, C.s.title, {font:'head', fit:{max:62, min:30}, color:'#16325C', ed:{f:'title'}, maxLines:3, lh:1.12});
  if (C.s.subtitle) C.text(64, 560, 720, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var info = [f.presenter, f.designation, f.date].filter(Boolean).join('  |  ');
  if (info) C.text(64, 640, 800, 20, info, {font:'body', size:13, wt:600, color:'#1592C4', lh:1});
  if (f.website && !o.img) { C.text(W - 64 - 360, 48, 360, 18, 'Contact Information', {font:'head', size:13, color:'#16325C', align:'r', lh:1}); C.text(W - 64 - 360, 70, 360, 18, f.website, {font:'body', size:13, color:P.muted, align:'r', lh:1, ed:{f:'tf-website'}}); }
}
function closing(C, v){
  var P = C.P;
  C.bg({grad:{ang:180, stops:[[0,'#EEF6FA'],[100,'#DDEEF6']]}});
  D.waveBundle(C, {x0:-40, y0:700, x1:1320, y1:520, n:22, amp:90, spread:6, color:'#34C1F2', lw:1.1, op:0.55, fade:true});
  if (C.cover.company) C.text(64, 70, 400, 20, C.cover.company, {font:'head', size:15, color:'#16325C', lh:1});
  C.text(64, 150, 640, 120, C.s.title || 'Thank You!', {font:'head', fit:{max:76, min:36}, color:'#16325C', ed:{f:'title'}, maxLines:2, lh:1.05});
  if (C.s.subtitle) C.text(64, 290, 620, 60, C.s.subtitle, {font:'head', fit:{max:20, min:13}, color:'#1592C4', ed:{f:'subtitle'}, maxLines:2, lh:1.35});
  C.text(820, 150, 360, 20, 'Contact Information', {font:'head', size:15, color:'#16325C', lh:1});
  D.contactList(C, 820, 190, 400, {gap:58});
  if (C.s.conclusion) C.text(64, 380, 620, 60, C.s.conclusion, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'x-conc'}, lh:1.4, maxLines:2});
}
function agenda(C, v){
  var P = C.P;
  D.agendaSplit(C, {split:0.4, titleY:180, tMax:60, itemWt:600, rowMax:62, numC:'#1592C4', ruleC:'#CFE8F5'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#1FB5EC'}); return; }
  C.rect(140, 120, W - 280, H - 240, {fill:'#F1FAFE', r:28, line:'#D6EEF9', lw:1});
  D.B.quote(C, {x:200, y:150, w:W - 400, h:H - 300}, {qc:'#1FB5EC'});
}
D.register({
  id:'aqua', name:'Aqua Wave', tag:'Clean white · tech', desc:'Crisp white slides with flowing cyan line-waves, navy headlines and numbered bubbles.',
  fonts:{head:'Poppins', body:'Poppins', headW:700, bodyW:400, headLh:1.12, gf:'Poppins:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Wave cover',{}), V('v2','Wave cover + image',{img:'right'})],
    agenda:[V('v1','Split list',{}), V('n2','Numbered tiles',{body:'agTiles'})],
    p2:[V('v1','Two panels',{body:'duo'}), V('n2','Numbered bubbles',{body:'list', mark:'num'}), V('v2','Image left + bubbles',{body:'list', img:'left', mark:'num'}), V('v3','Bubbles + image right',{body:'list', img:'right', mark:'num'})],
    p3:[V('v1','Numbered bubbles',{body:'list', mark:'num'}), V('n2','Soft cards',{body:'cols', hl:'first'}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + bubbles',{body:'list', img:'left', mark:'num'}), V('v3','Bubbles + image right',{body:'list', img:'right', mark:'num'}), V('v4','Image cards row',{body:'cols', img:'top'})],
    p4:[V('v1','Bubble grid',{body:'grid', mark:'num', cards:false}), V('n2','Soft card grid',{body:'grid'}), V('n3','Four columns',{body:'cols', hl:'first'}), V('n4','Hub diagram',{body:'hub'}), V('v2','Image left + grid',{body:'list', img:'left', mark:'num'}), V('v3','Grid + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Soft card grid',{body:'grid'}), V('n2','Two-column bubbles',{body:'list', cols:2, mark:'num'}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1, mark:'num'})],
    p78:[V('v1','Soft card grid',{body:'grid'}), V('n2','Two-column bubbles',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Stat cards',{body:'statCards', icons:true, hl:'first'}), V('n2','Big numbers',{body:'statCols'}), V('n3','Progress rings',{body:'statRings', track:'#E1F4FC'}), V('n4','Progress bars',{body:'statBars'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Before/after steps',{body:'tlH'}), V('n2','Milestone cards',{body:'tlCards'}), V('n3','Arrow steps',{body:'tlChev'}), V('n4','Vertical timeline',{body:'tlV'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Soft quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

