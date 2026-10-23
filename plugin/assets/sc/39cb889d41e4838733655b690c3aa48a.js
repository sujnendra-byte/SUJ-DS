
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 63 - Glass Flow : near-black, one large glossy liquid loop in muted violet and green behind a wide pebbled-glass panel, big bold titles */
D.glassKit({
  id:'glassflow', name:'Glass Flow', tag:'Near-black · pebbled glass · liquid loop',
  desc:'Near-black with one large glossy liquid loop in muted violet and green that blurs behind a wide pebbled-glass panel; big bold titles lead the slide and a photo can fill the slide or half of it behind the glass. For launches, vision decks and keynotes.',
  fonts:{head:'Inter Tight', body:'Inter', headW:700, bodyW:400, headLh:1.02, gf:'Inter+Tight:wght@500;600;700;800|Inter:wght@400;500;600'},
  bg:'#09060F', tx:'#FFFFFF', mu:'#CFCBDA', ac:'#C9F2A6', ac2:'#8E7BD6', card:'#15111E', line:'#2C2540', onAc:'#09060F',
  r:28, mark:'line', num:'plain', caps:true, big:true, grain:14, calm:1, stag:false, cover:'c', close:'c', quote:'c', b3:'cols', veil:'#0B0812', veilOp:0.5,
  ph:{ang:140, stops:[[0, '#5B4A96'], [100, '#120D1E']]},
  scene:function(C, R, k, U){
    var BG = U.BG, f = k % 2, ax = R.x + R.w * (f ? 0.14 : 0.86), ay = R.y + R.h * 0.2, bx = R.x + R.w * (f ? 0.84 : 0.16), by = R.y + R.h * 0.84, m = Math.max(260, Math.min(R.w, R.h) * 0.62);
    U.D.glow(C, ax, ay, m * 1.5, m * 1.2, '#2F5A3A', 0.4); U.D.glow(C, bx, by, m * 1.6, m * 1.2, '#3A2A6E', 0.45);
    /* each loop: a lit disc with a page-coloured disc set off-centre inside it, which leaves a glossy crescent */
    C.ell(ax - m, ay - m * 0.9, m * 2, m * 1.8, {grad:{ang:f ? 220 : 140, stops:[[0, '#CDEFB0'], [30, '#5FA36C'], [70, '#1F5A4E'], [100, '#0C1F1C']]}});
    C.ell(ax - m * 0.86 + (f ? 40 : -40), ay - m * 0.66, m * 1.66, m * 1.5, {fill:BG});
    C.ell(bx - m * 1.1, by - m * 0.85, m * 2.2, m * 1.7, {grad:{ang:f ? 40 : 320, stops:[[0, '#D9C7FF'], [28, '#8E6BD0'], [68, '#3B2A78'], [100, '#120C24']]}});
    C.ell(bx - m * 0.92 + (f ? -46 : 46), by - m * 0.9, m * 1.84, m * 1.46, {fill:BG});
    U.D.glow(C, ax + (f ? -m * 0.5 : m * 0.5), ay - m * 0.5, m * 0.34, m * 0.16, '#FFFFFF', 0.4);
    U.D.glow(C, bx + (f ? m * 0.6 : -m * 0.6), by + m * 0.45, m * 0.4, m * 0.16, '#FFFFFF', 0.32);
  }
});
})();
