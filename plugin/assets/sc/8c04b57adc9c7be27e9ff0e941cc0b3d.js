
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 60 - Glass Mesh : graphite, a honeycomb of blue and silver cells behind softly rounded frosted glass */
D.glassKit({
  id:'glassmesh', name:'Glass Mesh', tag:'Graphite · frosted glass · honeycomb',
  desc:'Corporate graphite with a honeycomb of blue and silver cells that blurs behind softly rounded frosted glass; a photo can fill the slide or half of it behind the glass. For engineering, data, networks and operations decks.',
  fonts:{head:'Wix Madefor Display', body:'Wix Madefor Text', headW:700, bodyW:400, headLh:1.1, gf:'Wix+Madefor+Display:wght@500;600;700;800|Wix+Madefor+Text:wght@400;500;600;700'},
  bg:'#111319', tx:'#FFFFFF', mu:'#BCC4D2', ac:'#7FA8FF', ac2:'#C9D3E0', card:'#1A1D26', line:'#2C3140', onAc:'#0B0D12',
  r:14, mark:'bar', num:'box', cover:'l', close:'r', quote:'l', b3:'cols', b4:'grid', stag:false, veil:'#07080C', veilOp:0.38,
  ph:{ang:150, stops:[[0, '#44609E'], [100, '#12141B']]},
  scene:function(C, R, k, U){
    var K = U.K, s = 62, hx = function(cx, cy, r){ var p = []; for (var a = 0; a < 6; a++) p.push([cx + r * Math.cos(a * Math.PI / 3), cy + r * Math.sin(a * Math.PI / 3)]); return p; };
    U.D.glow(C, R.x + R.w * 0.6, R.y + R.h * 0.5, 600, 420, '#2A3F7A', 0.42);
    for (var q = 0; q < 15; q++) for (var r = 0; r < 8; r++) {
      var cx = 20 + q * s * 1.5, cy = 10 + r * s * 1.732 + (q % 2 ? s * 0.866 : 0), v = (q * 7 + r * 13 + k * 5 + q * r) % 7;
      if (v === 0) K.poly(C, hx(cx, cy, s - 5), {fill:'#5B8DEF', op:0.6});
      else if (v === 1) K.poly(C, hx(cx, cy, s - 5), {fill:'#C9D3E0', op:0.2});
      else if (v === 2) K.poly(C, hx(cx, cy, s - 5), {fill:'#2C4A9A', op:0.55});
      else if (v === 3) K.poly(C, hx(cx, cy, s - 5), {stroke:'#7FA8FF', lw:1.5, lop:0.35});
    }
  }
});
})();
