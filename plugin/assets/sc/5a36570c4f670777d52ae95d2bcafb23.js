
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 61 - Glass Wave : midnight indigo, slow flowing bands behind pill-cornered frosted glass */
D.glassKit({
  id:'glasswave', name:'Glass Wave', tag:'Midnight indigo · frosted glass · waves',
  desc:'Corporate midnight indigo with slow flowing bands that blur behind pill-cornered frosted glass; a photo can fill the slide or half of it behind the glass. For software, telecom, insurance and investor decks.',
  fonts:{head:'Epilogue', body:'Epilogue', headW:700, bodyW:400, headLh:1.1, gf:'Epilogue:wght@400;500;600;700;800'},
  bg:'#0A0D26', tx:'#FFFFFF', mu:'#BEC3E6', ac:'#A9B4FF', ac2:'#4B58C8', card:'#141838', line:'#2A2F5E', onAc:'#0A0D26',
  r:40, mark:'dot', num:'plain', cover:'r', close:'c', quote:'c', b3:'cols', veil:'#060819', veilOp:0.36,
  ph:{ang:150, stops:[[0, '#4B58C8'], [100, '#0C1030']]},
  scene:function(C, R, k, U){
    var W = U.W;
    function band(y, amp, ph, th, color, op){
      var d = [], n = 40, i, t;
      for (i = 0; i <= n; i++) { t = i / n; d.push([i ? 'L' : 'M', W * t, y + Math.sin(t * 5.4 + ph) * amp]); }
      for (i = n; i >= 0; i--) { t = i / n; d.push(['L', W * t, y + th * (0.6 + 0.4 * Math.sin(t * 3.3 + ph * 1.6)) + Math.sin(t * 4.2 + ph + 1) * amp * 1.2]); }
      d.push(['Z']); C.path(d, {fill:color, op:op});
    }
    var y0 = R.y + R.h * 0.5, p = k * 1.3;
    band(y0 - 250, 70, 0.3 + p, 150, '#2E3AA0', 0.75);
    band(y0 - 110, 60, 1.7 + p, 110, '#A9B4FF', 0.5);
    band(y0 + 10, 80, 2.9 + p, 170, '#4B58C8', 0.6);
    band(y0 + 170, 50, 0.9 + p, 90, '#DDE1FF', 0.35);
    band(y0 + 250, 60, 2.1 + p, 200, '#1B2270', 0.8);
  }
});
})();
