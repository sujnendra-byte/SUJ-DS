
/* Template 07 — Violet Horizon : deep indigo gradients, glowing violet panels, flowing lines (AI sample 1) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#1B1060', text:'#FFFFFF', title:'#FFFFFF', muted:'#C9C3F2', accent:'#8E7CFF', accent2:'#D07BFF', card:'#3A2BB0', line:'#4B3CB8', onAccent:'#FFFFFF'};
var S = {
  margin:66,
  header:{kind:'left', tMax:48, tMin:26, sMax:17, top:60},
  card:{kind:'grad', r:6, grad:{ang:120, stops:[[0,'#5C45E6',0.85],[100,'#3A2BB0',0.55]]}, line:'#8E7CFF', lw:1, lop:0.6, dark:true, tc:'#FFFFFF', mc:'#DCD7FF', hl:{kind:'grad', grad:{ang:120, stops:[[0,'#8E7CFF'],[100,'#C07BFF']]}, dark:true, tc:'#FFFFFF', mc:'#F4EEFF'}},
  icon:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF', lw:1.6},
  num:{kind:'square', bg:'#8E7CFF', fg:'#FFFFFF', r:4},
  conc:{kind:'box', fill:'#2C1F8F', line:'#8E7CFF', lw:1, r:6, lc:'#C9B8FF', label:'Key takeaway'},
  footer:{pagePrefix:'PAGE '},
  imgFramed:true, imgR:14, imgPh:{grad:{ang:135, stops:[[0,'#3B2DC2'],[100,'#C04FE8']]}}
};
function logo(C, x, y, s){
  C.rect(x, y, s * 0.46, s * 0.46, {fill:'#A79BFF', r:s * 0.12});
  C.rect(x + s * 0.54, y + s * 0.2, s * 0.46, s * 0.46, {fill:'#A79BFF', op:0.75, r:s * 0.12});
  C.rect(x + s * 0.2, y + s * 0.54, s * 0.46, s * 0.46, {fill:'#A79BFF', op:0.55, r:s * 0.12});
}
function flow(C, y0, y1, op){ D.waveBundle(C, {x0:-20, y0:y0, x1:1300, y1:y1, n:22, amp:70, spread:4, color:'#9C7CFF', lw:1, op:op || 0.45, fade:true, phase:C.i}); }
function bg(C, kind){
  C.bg({grad:{ang:135, stops:[[0,'#120A4A'],[55,'#23158A'],[100,'#3A1FA8']]}});
  D.glow(C, 1150, 120, 380, 300, '#6A4CFF', 0.45);
  D.glow(C, 160, 690, 420, 240, '#C04FE8', 0.25);
  flow(C, 640, 520, 0.35);
  logo(C, W - 110, 40, 46);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  D.glow(C, 300, 360, 380, 380, '#3F7BFF', 0.55);
  if (o.img) D.imgBox(C, 64, 70, 480, 580, {r:22});
  else {
    for (var r = 90; r <= 300; r += 30) C.ell(300 - r, 360 - r, r * 2, r * 2, {line:'#7FB6FF', lw:1.2, lop:0.18 + (300 - r) / 900});
    C.icon(190, 250, 220, 'mdi:brain', '#9CC8FF');
  }
  C.rect(560, 150, 660, 330, {grad:{ang:90, stops:[[0,'#5C45E6',0.55],[100,'#5C45E6',0]]}});
  if (f.company) { logo(C, 1040, 60, 26); C.text(1074, 60, 180, 26, f.company, {font:'body', size:13, color:'#FFFFFF', va:'m', lh:1, ed:{f:'tf-company'}}); }
  C.text(590, 170, 610, 230, C.s.title, {font:'head', fit:{max:68, min:32}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.05, va:'m'});
  if (C.s.subtitle) C.text(592, 400, 600, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, italic:true, color:'#DCD7FF', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var meta = [f.presenter, f.date, f.website].filter(Boolean).join('   •   ');
  if (meta) C.text(590, 610, 620, 20, meta, {font:'body', size:13, color:'#DCD7FF', lh:1, align:'r'});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  D.imgBox(C, 830, 0, 450, H, {});
  C.text(90, 120, 640, 250, C.s.title || 'Thank You for Your Attention', {font:'head', fit:{max:66, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.08, va:'b'});
  if (C.s.subtitle) C.text(92, 390, 600, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#DCD7FF', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(90, 470, 640, 180, {grad:{ang:120, stops:[[0,'#5C45E6',0.85],[100,'#3A2BB0',0.55]]}, line:'#8E7CFF', lw:1, lop:0.6, r:4});
  D.contactList(C, 120, 494, 580, {gap:50, markO:{kind:'plain', fg:'#FFFFFF'}});
}
function agenda(C, v){
  D.agendaSplit(C, {split:0.4, titleY:220, tMax:66, numC:'#C9B8FF', itemWt:600, rowMax:60, ruleC:'#4B3CB8'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#C9B8FF'}); return; }
  C.rect(160, 130, W - 320, H - 260, {grad:{ang:120, stops:[[0,'#5C45E6',0.6],[100,'#3A2BB0',0.35]]}, line:'#8E7CFF', lw:1, lop:0.5, r:6});
  D.B.quote(C, {x:220, y:160, w:W - 440, h:H - 320}, {qc:'#C9B8FF'});
}
D.register({
  id:'violet', name:'Violet Horizon', tag:'Indigo glow · AI', desc:'Deep indigo gradients, translucent violet panels, glowing orbits and flowing light lines.',
  fonts:{head:'Plus Jakarta Sans', body:'Plus Jakarta Sans', headW:800, bodyW:400, headLh:1.06, gf:'Plus Jakarta Sans:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Glowing orbit',{}), V('v2','Image panel',{img:'left'})],
    agenda:[V('v1','Split list',{}), V('n2','Numbered tiles',{body:'agTiles'})],
    p2:[V('v1','Glow panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Glow cards',{body:'cols', hl:'first'}), V('n2','Icon list',{body:'list', rules:true}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true}), V('v4','Image bottom + cards',{body:'cols', img:'bottom'})],
    p4:[V('v1','Glow grid',{body:'grid'}), V('n2','Four columns',{body:'cols', center:true}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + grid',{body:'grid', img:'left', cols:2}), V('v3','Grid + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Glow grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Glow grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, rules:true})],
    stats:[V('v1','Glow stat panels',{body:'statCards', icons:true, hl:'first'}), V('n2','Big numbers',{body:'statCols', vc:'#C9B8FF'}), V('n3','Progress rings',{body:'statRings', track:'#3A2BB0'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Glow milestones',{body:'tlCards', hlLast:true}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Glow panel quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

