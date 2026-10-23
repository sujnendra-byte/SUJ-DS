
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 57 - Glass Tower : dark navy, a skyline of steel-blue towers behind square-cut frosted glass */
D.glassKit({
  id:'glasstower', name:'Glass Tower', tag:'Dark navy · frosted glass · towers',
  desc:'Corporate dark navy with a skyline of steel-blue towers that blurs behind square-cut frosted glass panels; a photo can fill the slide or half of it behind the glass. For finance, real estate, consulting and company reports.',
  fonts:{head:'Hanken Grotesk', body:'Hanken Grotesk', headW:700, bodyW:400, headLh:1.1, gf:'Hanken+Grotesk:wght@400;500;600;700;800'},
  bg:'#08111F', tx:'#FFFFFF', mu:'#B4C2D6', ac:'#8FC1F2', ac2:'#3E6FA3', card:'#101D30', line:'#24364F', onAc:'#08111F',
  r:6, mark:'tick', num:'box', stag:false, cover:'l', close:'c', quote:'c', b3:'cols', veil:'#050B15', veilOp:0.36,
  ph:{ang:160, stops:[[0, '#3E6FA3'], [100, '#0B1A2E']]},
  scene:function(C, R, k, U){
    var W = U.W, H = U.H, n = 17, bw = W / n;
    U.D.glow(C, R.x + R.w * 0.7, 120, 560, 380, '#2B5E99', 0.4);
    for (var i = 0; i < n; i++) {
      var h = 150 + ((i * 37 + k * 53 + 5) % 11) / 10 * 380, x = i * bw + 5, y = H - h, hot = (i + k) % 5 === 2;
      C.rect(x, y, bw - 10, h, {grad:{ang:180, stops:[[0, hot ? '#8FC1F2' : '#3E6FA3', hot ? 0.85 : 0.75], [100, '#0B1A2E', 0.15]]}});
      C.rect(x, y, bw - 10, 3, {fill:'#CFE6FF', op:hot ? 0.95 : 0.5});
      if (i % 2 === 0) C.rect(x + (bw - 10) * 0.3, y + 26, (bw - 10) * 0.4, h * 0.5, {fill:'#CFE6FF', op:0.1});
    }
  }
});
})();
