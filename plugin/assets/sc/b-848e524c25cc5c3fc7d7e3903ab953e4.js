try{

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

}catch(e){if(window.console)console.error(e);}
try{

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

}catch(e){if(window.console)console.error(e);}
try{

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

}catch(e){if(window.console)console.error(e);}
try{

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

}catch(e){if(window.console)console.error(e);}
try{

(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 64 - Glass Flute : black fluted wall, two metallic plum spheres behind a pebbled-glass panel */
D.glassKit({
  id:'glassflute', name:'Glass Flute', tag:'Black · pebbled glass · fluted wall',
  desc:'A black fluted wall with two metallic plum spheres that blur behind a pebbled-glass panel; white text carries the slide and a photo can fill the slide or half of it behind the glass. For premium brands, events, product and portfolio decks.',
  fonts:{head:'Archivo', body:'Albert Sans', headW:800, bodyW:400, headLh:1.04, gf:'Archivo:wght@600;700;800;900|Albert+Sans:wght@400;500;600'},
  bg:'#0A0A0C', tx:'#FFFFFF', mu:'#CFCBD2', ac:'#E3B7DA', ac2:'#8E3F7A', card:'#161618', line:'#2A2A2E', onAc:'#0A0A0C',
  r:18, mark:'bar', num:'ring', big:true, grain:16, calm:1, stag:false, cover:'c', close:'l', quote:'c', b3:'cols', b4:'grid', veil:'#070708', veilOp:0.5,
  ph:{ang:150, stops:[[0, '#6E3A62'], [100, '#111113']]},
  scene:function(C, R, k, U){
    var W = U.W, H = U.H, n = 46, w = W / n, f = k % 2;
    for (var i = 0; i < n; i++) C.rect(i * w, 0, w - 3, H, {grad:{ang:90, stops:[[0, '#0C0C0E'], [45, '#2A2A30'], [100, '#0C0C0E']]}});
    function ball(cx, cy, r, a, b, c){ C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:150, stops:[[0, a], [45, b], [100, c]]}}); U.D.glow(C, cx - r * 0.32, cy - r * 0.36, r * 0.5, r * 0.4, '#FFFFFF', 0.6); }
    var x1 = f ? R.x : R.x + R.w, m = Math.max(90, Math.min(R.w, R.h) * 0.24);
    ball(x1 + (f ? -m * 0.1 : m * 0.1), R.y + R.h * 0.68, m, '#F4DDEE', '#93407F', '#1F0B1B');
    ball(x1 + (f ? m * 0.5 : -m * 0.5), R.y + R.h * 0.22, m * 0.42, '#F1F1F4', '#8B8B97', '#1A1A1F');
    ball(R.x + R.w * (f ? 0.72 : 0.28), R.y + R.h * 0.96, m * 0.6, '#EFD3E8', '#7A3569', '#190916');
  }
});
})();

}catch(e){if(window.console)console.error(e);}
