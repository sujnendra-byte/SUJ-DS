
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 62 - Glass Slat : dark slate green, rows of lit slats with brass ones among them behind neat frosted glass */
D.glassKit({
  id:'glassslat', name:'Glass Slat', tag:'Slate green · frosted glass · slats',
  desc:'Corporate dark slate green with rows of softly lit slats, a few of them brass, that blur behind neat frosted glass; a photo can fill the slide or half of it behind the glass. For banking, architecture, hospitality and annual reviews.',
  fonts:{head:'Familjen Grotesk', body:'Familjen Grotesk', headW:700, bodyW:400, headLh:1.1, gf:'Familjen+Grotesk:wght@400;500;600;700'},
  bg:'#0B1512', tx:'#FFFFFF', mu:'#BFCDC6', ac:'#D2B272', ac2:'#3F6B5A', card:'#14211C', line:'#263A32', onAc:'#0B1512', rim:'#EDE2C6',
  r:10, mark:'tick', num:'ring', caps:true, stag:false, cover:'tl', close:'l', quote:'br', b3:'rows', b4:'grid', veil:'#050B09', veilOp:0.4,
  ph:{ang:150, stops:[[0, '#3F6B5A'], [100, '#0D1814']]},
  scene:function(C, R, k, U){
    var W = U.W, n = 13, sh = 720 / n;
    U.D.glow(C, R.x + R.w * (k % 2 ? 0.2 : 0.8), R.y + R.h * 0.5, 600, 420, '#2C5546', 0.45);
    for (var i = 0; i < n; i++) {
      var y = i * sh + 6, v = (i * 5 + k * 3) % 7, x0 = (i % 3) * 90 - 40, w = W - x0 + 40 - ((i + k) % 4) * 120;
      if (v === 1 || v === 4) C.rect(x0 + 200, y, w - 380, sh - 14, {grad:{ang:90, stops:[[0, '#D2B272', 0.15], [50, '#EBD39A', 0.9], [100, '#D2B272', 0.2]]}, r:(sh - 14) / 2});
      else C.rect(x0, y, w, sh - 14, {grad:{ang:90, stops:[[0, '#3F6B5A', 0.15], [45, '#7FA997', v === 2 ? 0.7 : 0.4], [100, '#3F6B5A', 0.1]]}, r:(sh - 14) / 2});
    }
  }
});
})();
