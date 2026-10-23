(function(){
if (window.DS_DG) return;
var DG = window.DS_DG = {types:{}, cats:[], catMap:{}, designs:{}, order:[]};
var W = 1280, H = 720;

/* ---------------- colour helpers ---------------- */
function hx(c){
  c = String(c == null ? '' : c).trim();
  var m = c.match(/#([0-9a-f]{6}|[0-9a-f]{3})\b/i); if (!m) return '#888888';
  var h = m[1]; if (h.length === 3) h = h[0]+h[0]+h[1]+h[1]+h[2]+h[2];
  return '#' + h.toUpperCase();
}
function rgb(c){ c = hx(c); return [parseInt(c.substr(1,2),16), parseInt(c.substr(3,2),16), parseInt(c.substr(5,2),16)]; }
function lum(c){ var r = rgb(c); return (0.299*r[0] + 0.587*r[1] + 0.114*r[2]) / 255; }
function mix(a, b, t){ var A = rgb(a), B = rgb(b), o = '#'; for (var i = 0; i < 3; i++) { var v = Math.round(A[i] + (B[i]-A[i])*t); o += ('0' + Math.max(0, Math.min(255, v)).toString(16)).slice(-2); } return o.toUpperCase(); }
function toHsl(c){ var r = rgb(c).map(function(v){ return v/255; }), mx = Math.max.apply(null, r), mn = Math.min.apply(null, r), l = (mx+mn)/2, h = 0, s = 0, d = mx - mn;
  if (d) { s = l > 0.5 ? d/(2-mx-mn) : d/(mx+mn); if (mx === r[0]) h = (r[1]-r[2])/d + (r[1] < r[2] ? 6 : 0); else if (mx === r[1]) h = (r[2]-r[0])/d + 2; else h = (r[0]-r[1])/d + 4; h *= 60; }
  return {h:h, s:s, l:l}; }
function fromHsl(h, s, l){ h = ((h % 360) + 360) % 360 / 360; function f(p, q, t){ if (t < 0) t += 1; if (t > 1) t -= 1; if (t < 1/6) return p + (q-p)*6*t; if (t < 1/2) return q; if (t < 2/3) return p + (q-p)*(2/3-t)*6; return p; }
  var r, g, b; if (!s) { r = g = b = l; } else { var q = l < 0.5 ? l*(1+s) : l+s-l*s, p = 2*l-q; r = f(p,q,h+1/3); g = f(p,q,h); b = f(p,q,h-1/3); }
  return '#' + [r,g,b].map(function(v){ return ('0' + Math.round(v*255).toString(16)).slice(-2); }).join('').toUpperCase(); }
/* corporate palette around the accent: accent, muted complement, analogous shades */
var HUES = [[0, 1, 0], [160, 0.6, 0.02], [32, 0.85, -0.06], [-34, 0.8, 0.08], [0, 0.55, 0.2], [150, 0.5, -0.04], [-150, 0.5, 0.06], [64, 0.6, 0.12]];
function palette(ac, dark, n){
  var A = toHsl(hx(ac)), out = [], s0 = Math.max(0.42, Math.min(0.78, A.s || 0.6));
  var l0 = dark ? Math.max(0.56, Math.min(0.68, A.l)) : Math.max(0.36, Math.min(0.5, A.l));
  for (var i = 0; i < Math.max(n, 1); i++) {
    if (i === 0) { out.push(hx(ac)); continue; }
    var o = HUES[i % HUES.length], lap = Math.floor(i / HUES.length), l = l0 + o[2] * (dark ? -1 : 1) + lap*0.08;
    out.push(fromHsl(A.h + o[0] + lap*18, Math.max(0.2, s0 * o[1]), Math.max(0.24, Math.min(0.8, l))));
  }
  return out;
}
function on(c){ return lum(c) > 0.62 ? '#111827' : '#FFFFFF'; }
DG.u = {hx:hx, lum:lum, mix:mix, palette:palette, on:on, toHsl:toHsl, fromHsl:fromHsl};

/* ---------------- numbers ---------------- */
function num(v){ if (typeof v === 'number') return isFinite(v) ? v : null; var s = String(v == null ? '' : v).replace(/[^0-9.\-]/g, ''); if (s === '' || s === '-' || s === '.') return null; var n = parseFloat(s); return isFinite(n) ? n : null; }
function fmt(v, unit){
  if (v == null || v === '' || !isFinite(+v)) return '';
  v = +v; unit = unit || '';
  var a = Math.abs(v), s;
  if (a >= 1e9) s = (v/1e9).toFixed(a >= 1e10 ? 0 : 1).replace(/\.0$/, '') + 'B';
  else if (a >= 1e6) s = (v/1e6).toFixed(a >= 1e7 ? 0 : 1).replace(/\.0$/, '') + 'M';
  else if (a >= 1e4) s = (v/1e3).toFixed(a >= 1e5 ? 0 : 1).replace(/\.0$/, '') + 'K';
  else s = (Math.round(v*10)/10).toLocaleString('en-US');
  if (/^[$€£₹¥]$/.test(unit)) return (v < 0 ? '-' : '') + unit + s.replace(/^-/, '');
  if (unit === '%') return s + '%';
  return unit ? s + ' ' + unit : s;
}
function niceMax(m){ if (!(m > 0)) return 1; var p = Math.pow(10, Math.floor(Math.log10(m))), f = m / p; return (f <= 1 ? 1 : f <= 1.2 ? 1.2 : f <= 1.5 ? 1.5 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 3 ? 3 : f <= 4 ? 4 : f <= 5 ? 5 : f <= 6 ? 6 : f <= 8 ? 8 : 10) * p; }
DG.num = num; DG.fmt = fmt; DG.niceMax = niceMax;
function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
DG.esc = esc;

/* ---------------- registry ---------------- */
DG.cat = function(id, name, icon, desc){ var c = {id:id, name:name, icon:icon, desc:desc, types:[]}; DG.cats.push(c); DG.catMap[id] = c; return c; };
/* t: {id, cat, name, fields:[{k,n,num,opts,w}], series:bool, sample:{rows,cols,ax,unit,o}, ai:'...', min, max, designs:[{id,name,draw}]} */
DG.type = function(t){
  DG.types[t.id] = t; DG.order.push(t.id);
  if (DG.catMap[t.cat]) DG.catMap[t.cat].types.push(t.id);
  (t.designs || []).forEach(function(d){ d.type = t.id; d.full = 'dg_' + d.id; DG.designs[d.full] = d; });
  return t;
};
DG.isDesign = function(id){ return !!(id && DG.designs[id]); };
DG.designOf = function(s){
  var dg = s && s.dg; if (!dg) return null;
  var d = DG.designs[dg.v]; if (d && d.type === dg.t) return d;
  var t = DG.types[dg.t]; return t && t.designs && t.designs[0] ? DG.designs[t.designs[0].full] : null;
};
DG.is = function(s){ return !!(s && s.dg && DG.types[s.dg.t]); };
/* designs a slide may switch to without losing data: same type first, then same-field types in the same category */
DG.related = function(s){
  if (!DG.is(s)) return [];
  /* fixed category order (not "current type first"), so the layout cards never jump around
     after a pick that switches the diagram type (gauges -> target bars, SWOT -> PESTLE ...) */
  var t = DG.types[s.dg.t], sig = sigOf(t), out = [];
  var ids = (DG.catMap[t.cat] ? DG.catMap[t.cat].types : []).slice();
  if (ids.indexOf(t.id) < 0) ids.unshift(t.id);
  ids.forEach(function(id){
    var o = DG.types[id]; if (!o || (id !== t.id && sigOf(o) !== sig)) return;
    o.designs.forEach(function(d){ out.push(d.full); });
  });
  return out;
};
function sigOf(t){ return (t.series ? 'S:' : '') + (t.fields || []).map(function(f){ return f.k; }).join(','); }
DG.sig = sigOf;

/* ---------------- data model ---------------- */
function clone(o){ return JSON.parse(JSON.stringify(o)); }
DG.clone = clone;
DG.newData = function(typeId, designFull){
  var t = DG.types[typeId]; if (!t) return null;
  var smp = clone(t.sample || {});
  return {t:t.id, v:designFull || t.designs[0].full, rows:smp.rows || [], cols:smp.cols || [], ax:smp.ax || {}, unit:smp.unit || '', o:smp.o || {}};
};
/* clean, typed copy of the slide data */
DG.norm = function(dg){
  var t = DG.types[dg && dg.t]; if (!t) return null;
  var D = {t:t.id, v:dg.v, type:t, cols:Array.isArray(dg.cols) ? dg.cols.map(function(c){ return String(c == null ? '' : c); }) : [], ax:dg.ax || {}, unit:dg.unit == null ? '' : String(dg.unit), o:dg.o || {}, rows:[]};
  if (t.series && !D.cols.length) D.cols = ['Series 1'];
  (Array.isArray(dg.rows) ? dg.rows : []).forEach(function(r){
    if (!r || typeof r !== 'object') return;
    var o = {};
    (t.fields || []).forEach(function(f){
      var v = r[f.k];
      if (f.num) { var n = num(v); o[f.k] = n; }
      else o[f.k] = v == null ? '' : String(v).trim();
    });
    if (t.series) { var vs = Array.isArray(r.v) ? r.v : [r.v]; o.v = D.cols.map(function(_, i){ var n = num(vs[i]); return n == null ? 0 : n; }); }
    D.rows.push(o);
  });
  if (t.max) D.rows = D.rows.slice(0, t.max);
  return D;
};

/* ---------------- geometry ---------------- */
var K4 = 0.5522847498;
function arcPts(cx, cy, r, a0, a1){
  /* cubic segments approximating an arc (angles in degrees, 0 = up, clockwise) */
  var segs = [], n = Math.max(1, Math.ceil(Math.abs(a1 - a0) / 90)), da = (a1 - a0) / n;
  for (var i = 0; i < n; i++) {
    var s = (a0 + da*i - 90) * Math.PI/180, e = (a0 + da*(i+1) - 90) * Math.PI/180, k = 4/3 * Math.tan((e - s)/4);
    var x1 = cx + r*Math.cos(s), y1 = cy + r*Math.sin(s), x2 = cx + r*Math.cos(e), y2 = cy + r*Math.sin(e);
    segs.push(['C', r2(x1 - k*r*Math.sin(s)), r2(y1 + k*r*Math.cos(s)), r2(x2 + k*r*Math.sin(e)), r2(y2 - k*r*Math.cos(e)), r2(x2), r2(y2)]);
  }
  return segs;
}
function r2(v){ return Math.round(v*100)/100; }
function pol(cx, cy, r, a){ var t = (a - 90) * Math.PI/180; return [r2(cx + r*Math.cos(t)), r2(cy + r*Math.sin(t))]; }
DG.pol = pol;

/* ============================================================
   KIT — drawing helpers handed to every design
   ============================================================ */
function Kit(C, Z, D){
  var P = C.P || {};
  this.C = C; this.Z = Z; this.D = D; this.P = P;
  this.bg = hx(P.bg || '#FFFFFF'); this.dark = lum(this.bg) < 0.5;
  this.ac = hx(P.accent || '#2563EB');
  this.tc = hx(P.title || P.text || (this.dark ? '#FFFFFF' : '#111827'));
  this.txt = hx(P.text || this.tc);
  this.mc = hx(P.muted || mix(this.txt, this.bg, 0.35));
  this.ln = hx(P.line || mix(this.txt, this.bg, 0.8));
  this.cardc = hx(P.card || (this.dark ? mix(this.bg, '#FFFFFF', 0.08) : mix(this.bg, '#000000', 0.04)));
  this.pal = palette(this.ac, this.dark, 10);
  this.head = C.T && C.T.fonts ? C.T.fonts.head : 'Arial';
  this.body = C.T && C.T.fonts ? C.T.fonts.body : 'Arial';
}
var KP = Kit.prototype;
KP.col = function(i){ return this.pal[((i % this.pal.length) + this.pal.length) % this.pal.length]; };
KP.soft = function(i, t){ return mix(this.bg, this.col(i), t == null ? 0.18 : t); };
/* element makers: o.pi = palette index (re-coloured on theme change), o.soft = tint of it */
KP._pal = function(e, o, key){
  if (o && o.pi != null) { e.dgp = o.pi; e.dgk = key; if (o.soft != null) e.dgs = o.soft; }
  else if (o && o.fix) { e.dgfix = String(o.fix).replace('#', ''); e.dgk = key; if (o.soft != null) e.dgs = o.soft; }
  return e;
};
KP._fill = function(o){ if (!o) return null; if (o.pi != null) return o.soft != null ? this.soft(o.pi, o.soft) : this.col(o.pi); if (o.fix) return o.soft != null ? mix(this.bg, o.fix, o.soft) : o.fix; return o.fill || null; };
/* status colours that never follow the theme: good / warn / bad */
KP.good = function(){ return this.dark ? '#34D399' : '#16A34A'; };
KP.warn = function(){ return this.dark ? '#FBBF24' : '#D97706'; };
KP.bad  = function(){ return this.dark ? '#F87171' : '#DC2626'; };
/* n cells in a balanced grid; the last row is stretched to the full width */
KP.cells = function(n, x, y, w, h, gap, aspect){
  var best = null; aspect = aspect || 1.5;
  for (var c = 1; c <= n; c++) { var r = Math.ceil(n / c), cw = (w - gap*(c - 1)) / c, ch = (h - gap*(r - 1)) / r, sc = Math.min(cw / aspect, ch); if (!best || sc > best.sc + 0.5) best = {c:c, r:r, sc:sc}; }
  var out = [], ch2 = (h - gap*(best.r - 1)) / best.r;
  for (var i = 0; i < n; i++) {
    var row = Math.floor(i / best.c), inRow = row === best.r - 1 ? n - row*best.c : best.c, col = i - row*best.c, cw2 = (w - gap*(inRow - 1)) / inRow;
    out.push({x:x + col*(cw2 + gap), y:y + row*(ch2 + gap), w:cw2, h:ch2});
  }
  return out;
};
KP.rect = function(x, y, w, h, o){ o = o || {}; var e = this.C.rect(x, y, w, h, {fill:this._fill(o), op:o.op, r:o.r, line:o.line, lw:o.lw, lop:o.lop, grad:o.grad, shadow:o.shadow, dash:o.dash}); return this._pal(e, o, 'fill'); };
KP.ell = function(x, y, w, h, o){ o = o || {}; var e = this.C.ell(x, y, w, h, {fill:this._fill(o), op:o.op, line:o.line, lw:o.lw, lop:o.lop, grad:o.grad, shadow:o.shadow, dash:o.dash}); return this._pal(e, o, 'fill'); };
KP.circ = function(cx, cy, r, o){ return this.ell(cx - r, cy - r, 2*r, 2*r, o); };
KP.shape = function(prst, x, y, w, h, o){ o = o || {}; var e = this.C.shape(prst, x, y, w, h, {fill:this._fill(o), op:o.op, line:o.line, lw:o.lw, lop:o.lop, rot:o.rot}); return this._pal(e, o, 'fill'); };
KP.line = function(x1, y1, x2, y2, o){ o = o || {}; var c = o.pi != null ? this.col(o.pi) : (o.fix || o.color || this.ln); var e = this.C.line(x1, y1, x2, y2, {color:c, lw:o.lw || 1.5, op:o.op, dash:o.dash}); return this._pal(e, o, 'color'); };
KP.path = function(d, o){ o = o || {}; var e = this.C.path(d, {stroke:o.spi != null ? this.col(o.spi) : o.stroke, lw:o.lw, fill:this._fill(o), op:o.op, lop:o.lop}); this._pal(e, o, 'fill'); if (o.spi != null) { e.dgp2 = o.spi; } return e; };
KP.poly = function(pts, o){ var d = []; pts.forEach(function(p, i){ d.push([i ? 'L' : 'M', r2(p[0]), r2(p[1])]); }); if (!o || o.close !== false) d.push(['Z']); return this.path(d, o); };
KP.polyline = function(pts, o){ o = o || {}; var d = []; pts.forEach(function(p, i){ d.push([i ? 'L' : 'M', r2(p[0]), r2(p[1])]); }); return this.path(d, {stroke:o.stroke || this.ac, spi:o.spi, lw:o.lw || 3, lop:o.lop}); };
/* smooth curve through points (Catmull-Rom → cubic) */
KP.curve = function(pts, o){
  o = o || {}; if (pts.length < 2) return null;
  var d = [['M', r2(pts[0][0]), r2(pts[0][1])]];
  for (var i = 0; i < pts.length - 1; i++) {
    var p0 = pts[i-1] || pts[i], p1 = pts[i], p2 = pts[i+1], p3 = pts[i+2] || p2;
    d.push(['C', r2(p1[0] + (p2[0]-p0[0])/6), r2(p1[1] + (p2[1]-p0[1])/6), r2(p2[0] - (p3[0]-p1[0])/6), r2(p2[1] - (p3[1]-p1[1])/6), r2(p2[0]), r2(p2[1])]);
  }
  if (o.close) { d.push(['L', r2(o.close[1][0]), r2(o.close[1][1])]); d.push(['L', r2(o.close[0][0]), r2(o.close[0][1])]); d.push(['Z']); }
  return this.path(d, o);
};
/* ring / pie sector (angles in degrees, 0 = 12 o'clock, clockwise) */
KP.sector = function(cx, cy, r0, r1, a0, a1, o){
  if (a1 - a0 >= 359.9) a1 = a0 + 359.9;
  var p = pol(cx, cy, r1, a0), d = [['M', p[0], p[1]]].concat(arcPts(cx, cy, r1, a0, a1));
  if (r0 > 0.5) { var q = pol(cx, cy, r0, a1); d.push(['L', q[0], q[1]]); d = d.concat(arcPts(cx, cy, r0, a1, a0)); }
  else d.push(['L', r2(cx), r2(cy)]);
  d.push(['Z']);
  return this.path(d, o);
};
KP.arc = function(cx, cy, r, a0, a1, o){ var p = pol(cx, cy, r, a0); return this.path([['M', p[0], p[1]]].concat(arcPts(cx, cy, r, a0, a1)), o); };
/* arrow with a filled head */
KP.arrow = function(x1, y1, x2, y2, o){
  o = o || {}; var hd = o.head || 11, lw = o.lw || 2.2, ang = Math.atan2(y2 - y1, x2 - x1), c = o.pi != null ? this.col(o.pi) : (o.color || this.mc);
  var bx = x2 - Math.cos(ang)*hd, by = y2 - Math.sin(ang)*hd;
  if (Math.hypot(bx - x1, by - y1) > 1) this.line(x1, y1, bx, by, {color:c, lw:lw, pi:o.pi, fix:o.fix, dash:o.dash, op:o.op});
  var nx = -Math.sin(ang)*hd*0.55, ny = Math.cos(ang)*hd*0.55;
  if (!o.noHead) this.poly([[x2, y2], [bx + nx, by + ny], [bx - nx, by - ny]], {fill:c, pi:o.pi, fix:o.fix, op:o.op});
  if (o.both) { var ax = x1 + Math.cos(ang)*hd, ay = y1 + Math.sin(ang)*hd; this.poly([[x1, y1], [ax + nx, ay + ny], [ax - nx, ay - ny]], {fill:c, pi:o.pi, fix:o.fix, op:o.op}); }
};
/* elbow connector (right angles) */
KP.elbow = function(x1, y1, x2, y2, o){
  o = o || {}; var my = o.vert === false ? null : (y1 + y2) / 2;
  if (o.hfirst) { var mx = (x1 + x2)/2; this.line(x1, y1, mx, y1, o); this.line(mx, y1, mx, y2, o); if (o.arrow) this.arrow(mx, y2, x2, y2, o); else this.line(mx, y2, x2, y2, o); return; }
  this.line(x1, y1, x1, my, o); this.line(x1, my, x2, my, o);
  if (o.arrow) this.arrow(x2, my, x2, y2, o); else this.line(x2, my, x2, y2, o);
};
/* text: o {size | max/min fit, wt, color, pi (text in palette colour), on (fill it sits on → contrast), align, va, font, lh, lines, caps, ls, italic, op} */
KP.t = function(x, y, w, h, text, o){
  o = o || {}; text = text == null ? '' : String(text); if (!text.trim()) return null;
  var col = o.color || (o.pi != null ? this.col(o.pi) : (o.fix ? o.fix : (o.on ? on(o.on) : this.txt)));
  var oo = {font:o.font || 'body', color:col, align:o.align || 'l', va:o.va || 'm', lh:o.lh || (o.font === 'head' ? 1.12 : 1.25), wt:o.wt || (o.font === 'head' ? 700 : 400), caps:!!o.caps, ls:o.ls || 0, italic:!!o.italic, op:o.op, maxLines:o.lines};
  if (o.size && !o.fit) oo.size = o.size; else oo.fit = {max:o.max || o.size || 16, min:o.min || Math.max(9, Math.round((o.max || o.size || 16) * 0.6))};
  var e = this.C.text(x, y, w, h, text, oo);
  if (o.pi != null) { e.dgp = o.pi; e.dgk = 'color'; }
  else if (o.fix && !o.color) { e.dgfix = String(o.fix).replace('#', ''); e.dgk = 'color'; }
  else if (o.onPi != null) { e.dgon = o.onPi; if (o.onSoft != null) e.dgons = o.onSoft; }
  return e;
};
/* text on a palette-coloured shape: colour picked for contrast, re-picked on theme change */
KP.tOn = function(x, y, w, h, text, pi, o, soft){ o = o || {}; o.color = on(soft != null ? this.soft(pi, soft) : this.col(pi)); o.onPi = pi; if (soft != null) o.onSoft = soft; return this.t(x, y, w, h, text, o); };
KP.card = function(x, y, w, h, o){
  o = o || {};
  if (window.DSTE && window.DSTE.card && !o.plain) return window.DSTE.card(this.C, x, y, w, h, o);
  this.rect(x, y, w, h, {fill:this.cardc, r:o.r != null ? o.r : 14});
  return {tc:this.tc, mc:this.mc};
};
KP.fmt = function(v, u){ return fmt(v, u == null ? this.D.unit : u); };
KP.measure = function(text, w, o){ o = o || {}; return this.C.measureText(text, w, {font:o.font || 'body', max:o.max || 16, min:o.min || 9, h:o.h, maxLines:o.lines, wt:o.wt, lh:o.lh}); };
/* one common font size for a set of texts in equal boxes */
KP.fitAll = function(texts, w, h, o){ o = o || {}; return this.C.groupFit(texts.filter(Boolean), w, h, {font:o.font || 'body', max:o.max || 16, min:o.min || 9, maxLines:o.lines, wt:o.wt, lh:o.lh}); };
/* bullet list that shrinks to fit: o {max, min, gap, dot:false, pi, color, dotColor, va:'m', lh, wt, num:true} */
KP.list = function(x, y, w, h, items, o){
  o = o || {}; items = (items || []).map(function(t){ return String(t || '').trim(); }).filter(Boolean); if (!items.length) return 0;
  var self = this, max = o.max || 17, min = o.min || 10, gap = o.gap != null ? o.gap : 9, ind = o.dot === false ? 0 : (o.num ? 30 : 20), lh = o.lh || 1.3, s = max, hs = [], tot = 0;
  for (; s >= min; s -= 0.5) {
    /* browsers and PowerPoint break lines after a hyphen ("renewable-|energy"); measure the same way (and a hair
       narrower) so a measured height is never too small — too small makes items overlap in narrow columns */
    tot = 0; hs = items.map(function(t){ var m = self.measure(String(t).replace(/([A-Za-z])-(?=[A-Za-z])/g, '$1- '), Math.max(20, w - ind - 3), {max:s, min:s, lh:lh, wt:o.wt}); tot += m.h + gap; return m.h; });
    if (tot - gap <= h) break;
  }
  if (s < min) s = min;
  if (o.dry) return s;                                  /* measure only: the size that fits */
  var yy = y + (o.va === 'm' ? Math.max(0, (h - (tot - gap)) / 2) : 0);
  items.forEach(function(t, i){
    if (yy > y + h + 2) return;
    if (o.num) { self.circ(x + 11, yy + s*0.66, 11, {pi:o.pi != null ? o.pi : 0}); self.tOn(x, yy + s*0.66 - 11, 22, 22, String(i + 1), o.pi != null ? o.pi : 0, {size:11, wt:800, align:'c', lh:1}); }
    else if (o.dot !== false) self.circ(x + 5, yy + s*0.66, Math.max(2.6, s*0.17), {pi:o.pi, fill:o.dotColor || (o.pi == null ? self.ac : null)});
    self.t(x + ind, yy, w - ind, Math.min(hs[i] + 4, y + h - yy + 4), t, {size:s, color:o.color || self.txt, va:'t', lh:lh, wt:o.wt});
    yy += hs[i] + gap;
  });
  return s;
};
/* heading + body inside a box (auto-sized); returns bottom y */
KP.block = function(x, y, w, h, head, body, o){
  o = o || {}; var hs = o.hmax || 19, bs = o.bmax || 15;
  var hm = head ? this.measure(head, w, {font:'head', max:hs, min:o.hmin || 12, h:h*0.45, lines:2, wt:700, lh:1.15}) : {h:0, s:hs};
  var bm = body ? this.measure(body, w, {max:bs, min:o.bmin || 9.5, h:h - hm.h - 6, lh:1.3}) : {h:0, s:bs};
  var tot = hm.h + (body ? bm.h + 6 : 0), yy = y + (o.va === 't' ? 0 : Math.max(0, (h - tot) / 2));
  if (head) this.t(x, yy, w, hm.h + 2, head, {size:hm.s, font:'head', wt:700, color:o.hc || this.tc, align:o.align || 'l', va:'t', lh:1.15, lines:2});
  if (body) this.t(x, yy + hm.h + 6, w, bm.h + 4, body, {size:bm.s, color:o.bc || this.mc, align:o.align || 'l', va:'t', lh:1.3});
  return yy + tot;
};
/* split "Heading: detail" */
KP.split = function(s){ s = String(s || ''); var i = s.indexOf(':'); return i > 0 && i < 70 ? [s.slice(0, i).trim(), s.slice(i+1).trim()] : [s.trim(), '']; };
/* small numbered badge */
KP.badge = function(cx, cy, r, label, pi, o){ o = o || {}; this.circ(cx, cy, r, {pi:pi, fill:o.fill, line:o.line, lw:o.lw}); this.tOn(cx - r, cy - r, 2*r, 2*r, String(label), pi == null ? 0 : pi, {size:o.size || Math.round(r*0.95), wt:800, align:'c', va:'m', font:'head', lh:1}); };
/* legend row: items [{l, pi}] */
KP.legend = function(items, x, y, w, o){
  o = o || {}; var fs = o.size || 14, gap = 22, cx = x, self = this, rowH = fs * 1.6;
  var widths = items.map(function(it){ return Math.min(260, self.measure(it.l, 400, {max:fs, min:fs}).s * 0 + String(it.l).length * fs * 0.55 + fs + 18); });
  var total = widths.reduce(function(a, b){ return a + b + gap; }, -gap);
  if (o.align === 'c') cx = x + Math.max(0, (w - total) / 2);
  items.forEach(function(it, i){
    if (cx + widths[i] > x + w + 2) { cx = o.align === 'c' ? x : x; y += rowH; }
    self.rect(cx, y + rowH/2 - fs*0.36, fs*0.72, fs*0.72, {pi:it.pi, r:3});
    self.t(cx + fs + 6, y, widths[i] - fs - 6, rowH, it.l, {size:fs, color:self.mc, va:'m', lines:1});
    cx += widths[i] + gap;
  });
  return y + rowH;
};

/* ---------------- native (editable) PowerPoint charts ----------------
   The preview is drawn with shapes tagged dgc (dropped from the .pptx);
   one invisible marker text carries the chart spec, and the server swaps
   it for a real chart with its data sheet. */
var _cid = 0;
KP.nativeChart = function(x, y, w, h, spec){
  var id = ++_cid;
  var C = this.C, before = C.els.length;
  var self = this;
  spec.tc = this.txt; spec.mc = this.mc; spec.bgc = this.bg; spec.font = this.body;
  (spec.ser || []).forEach(function(se){ se.c = se.pi != null ? self.col(se.pi) : self.ac; });
  if (spec.k === 'pie' || spec.k === 'doughnut') spec.pc = (spec.cats || []).map(function(_, i){ return self.col(spec.pis ? spec.pis[i] : i); });
  DG.drawChart(this, x, y, w, h, spec);
  for (var i = before; i < C.els.length; i++) C.els[i].dgc = id;
  var m = C.text(x, y, w, h, '·', {size:10, color:this.bg, op:0});
  m.dgmark = spec; m.dgc = 0;
  return m;
};

/* ============================================================
   RENDERING — classic decks (Snippet 17 theme object) and templates
   ============================================================ */
DG.frameZone = null;
function classicFont(){ var f = document.getElementById('dsp-font'); return f && f.value ? f.value : 'Arial'; }
DG.classicT = function(t){
  t = t || {};
  var bg = hx(t.bg || '#1B2A4A'), tc = hx(t.tc || '#FFFFFF'), bc = hx(t.bc || tc), ac = hx(t.ac || '#4A9EFF'), dark = lum(bg) < 0.5, f = classicFont();
  return {id:'classic', pal:{bg:bg, text:bc, title:tc, muted:mix(bc, bg, 0.3), accent:ac, accent2:mix(ac, tc, 0.35), card:dark ? mix(bg, '#FFFFFF', 0.07) : mix(bg, '#0F172A', 0.045), line:mix(bc, bg, 0.78), onAccent:on(ac)},
          fonts:{head:f, body:f, headW:800, bodyW:400, headLh:1.12}, s:{margin:64, card:{kind:'solid', r:14}, num:{kind:'circle'}, icon:{kind:'circle'}}};
};
/* the classic slide frame: accent line, title, subtitle, optional takeaway strip */
function classicFrame(C, s){
  var P = C.P, top = 40, M = 64;
  C.rect(0, 0, W, 3, {fill:P.accent});
  var tt = C.text(M, top, W - 2*M, 96, s.title || '', {font:'head', fit:{max:36, min:24}, wt:800, color:P.title, lh:1.12, ed:{f:'title'}, maxLines:2});
  var y = top + (tt ? tt._h : 44) + 8;
  if (String(s.subtitle || '').trim()) { var st = C.text(M, y, W - 2*M, 50, s.subtitle, {font:'body', fit:{max:17, min:13}, wt:600, color:P.accent, lh:1.3, ed:{f:'subtitle'}, maxLines:2}); y += (st ? st._h : 22) + 6; }
  var bot = H - 36, conc = String(s.conclusion || '').trim();
  if (conc) {
    var cy = H - 36 - 56;
    C.rect(M, cy, W - 2*M, 56, {fill:P.accent, op:0.14, r:12});
    C.rect(M, cy + 12, 6, 32, {fill:P.accent, r:3});
    C.text(M + 26, cy, W - 2*M - 44, 56, conc, {font:'body', fit:{max:18, min:12}, wt:700, color:P.title, va:'m', ed:{f:'conclusion'}, maxLines:2});
    bot = cy - 18;
  }
  var zy = Math.max(150, y + 26);
  return {x:M, y:zy, w:W - 2*M, h:Math.max(120, bot - zy)};
}
/* design → els inside zone Z; insight panel when the slide has key points and the design wants one */
DG._zmap = typeof WeakMap === 'function' ? new WeakMap() : null; DG._zkey = {};
DG.zoneOf = function(s){ if (!s || !s.dg) return null; return (DG._zmap && DG._zmap.get(s)) || DG._zkey[(s.dg.v || '') + '|' + (s.title || '')] || null; };
DG.drawInto = function(C, Z, s, designFull){
  var dsg = designFull && DG.designs[designFull] && DG.designs[designFull].type === (s.dg && s.dg.t) ? DG.designs[designFull] : DG.designOf(s);
  if (!dsg) return;
  var dg = s.dg; if (DG.designs[designFull] && DG.designs[designFull].type !== dg.t) { dsg = DG.designs[designFull]; }
  var D = DG.norm({t:dsg.type, v:dsg.full, rows:dg.rows, cols:dg.cols, ax:dg.ax, unit:dg.unit, o:dg.o});
  if (!D) return;
  var K = new Kit(C, Z, D);
  var pts = (Array.isArray(s.content) ? s.content : []).map(function(x){ return String(x || '').trim(); }).filter(Boolean).slice(0, 4);
  var strip = dsg.strip && D.rows.length ? (pts.length ? pts : (typeof dsg.strip === 'function' ? [].concat(dsg.strip(D) || []) : [])) : null;
  var aside = !strip && dsg.aside && D.rows.length ? (pts.length ? pts : (typeof dsg.aside === 'function' ? [].concat(dsg.aside(D) || []) : [])) : null;
  var side = !strip && !aside && pts.length && (dsg.side || (D.o && D.o.side));
  var z = Z;
  if (aside && aside.length) {                               /* text box on the right: icon + title + text */
    var aw = Math.round(Z.w * 0.33), ag = 26;
    z = {x:Z.x, y:Z.y, w:Z.w - aw - ag, h:Z.h};
    insightAside(K, Z.x + Z.w - aw, Z.y, aw, Z.h, aside.slice(0, 3));
  } else if (strip && strip.length) {                               /* insight strip under the diagram: box + title + text */
    var sh = Math.round(Math.min(190, Math.max(120, Z.h * 0.32))), sg = 22;
    z = {x:Z.x, y:Z.y, w:Z.w, h:Z.h - sh - sg};
    insightStrip(K, Z.x, Z.y + Z.h - sh, Z.w, sh, strip.slice(0, 4));
  } else if (side) {
    var pw = Math.round(Z.w * 0.3), gap = 36;
    z = {x:Z.x, y:Z.y, w:Z.w - pw - gap, h:Z.h};
    insightPanel(K, Z.x + Z.w - pw, Z.y, pw, Z.h, pts);
  }
  K.Z = z;
  if (dsg.full === dg.v) try { var zc ={x:z.x, y:z.y, w:z.w, h:z.h}; if (DG._zmap) DG._zmap.set(s, zc); DG._zkey[(dg.v || '') + '|' + (s.title || '')] = zc; } catch(e){}
  if (!D.rows.length) { K.t(z.x, z.y, z.w, z.h, 'Add data to see the diagram', {size:18, color:K.mc, align:'c'}); return; }
  try { dsg.draw(K, z, D); }
  catch(err) { if (window.console) console.error('[DS Diagrams]', dsg.full, err); }
};
function insightStrip(K, x, y, w, h, pts){
  var n = pts.length, gap = 18, bw = (w - gap*(n - 1)) / n;
  pts.forEach(function(p, i){
    var xx = x + i*(bw + gap), sp = K.split(p), c = K.card(xx, y, bw, h, {});
    K.rect(xx, y, 6, h, {pi:i, r:3});
    var pad = n > 2 ? 18 : 26;
    K.block(xx + pad + 4, y + 14, bw - 2*pad - 4, h - 28, sp[1] ? sp[0] : '', sp[1] || sp[0], {hmax:n > 2 ? 18 : 22, bmax:n > 2 ? 15 : n > 1 ? 17 : 19, hmin:11, bmin:9.5, hc:c.tc, bc:c.mc});
  });
}
DG.insightStrip = insightStrip;
function insightAside(K, x, y, w, h, pts){
  var c = K.card(x, y, w, h, {}), n = pts.length, pad = 26, sh = (h - 2*pad) / n, ic = n > 1 ? 40 : 58;
  pts.forEach(function(p, i){
    var yy = y + pad + i*sh, sp = K.split(p);
    if (i) K.line(x + pad, yy - 6, x + w - pad, yy - 6, {fill:K.ln, lw:1, op:0.7});
    K.rect(x + pad, yy + (i ? 8 : 0), ic, ic, {pi:i, soft:0.2, r:10});
    var scx = x + pad + ic/2, scy = yy + (i ? 8 : 0) + ic/2, R = ic*0.32, st = [];     /* 4-point sparkle */
    for (var k = 0; k < 8; k++) { var rr = k % 2 ? R*0.3 : R, a = -Math.PI/2 + k*Math.PI/4; st.push([scx + rr*Math.cos(a), scy + rr*Math.sin(a)]); }
    K.poly(st, {pi:i});
    var ty = yy + ic + (i ? 20 : 16);
    K.block(x + pad, ty, w - 2*pad, yy + sh - ty - 10, sp[1] ? sp[0] : '', sp[1] || sp[0], {va:'t', hmax:n > 1 ? 19 : 24, bmax:n > 1 ? 15 : 19, hmin:11, bmin:9.5, hc:c.tc, bc:c.mc});
  });
}
DG.insightAside = insightAside;
function insightPanel(K, x, y, w, h, pts){
  var n = pts.length, gap = 14, ch = (h - gap*(n-1)) / n;
  pts.forEach(function(p, i){
    var yy = y + i*(ch + gap), sp = K.split(p), c = K.card(x, yy, w, ch, {});
    K.rect(x, yy + ch*0.2, 5, ch*0.6, {pi:i, r:2.5});
    var hs = n > 3 ? 17 : 19;
    if (sp[1]) {
      K.t(x + 22, yy + 10, w - 36, Math.min(48, ch*0.38), sp[0], {max:hs, min:12, wt:700, color:c.tc, font:'head', va:'b', lines:2});
      K.t(x + 22, yy + 10 + Math.min(48, ch*0.38) + 4, w - 36, ch - Math.min(48, ch*0.38) - 24, sp[1], {max:15, min:10, color:c.mc, va:'t', lh:1.32});
    } else K.t(x + 22, yy + 8, w - 36, ch - 16, sp[0], {max:hs, min:11, wt:600, color:c.tc, va:'m'});
  });
}
/* classic deck: full page */
DG.pageClassic = function(s, t, designFull, idx){
  var D = window.DSTE; if (!D || !D.Ctx) return null;
  var T = DG.classicT(t), C = new D.Ctx(T, s, idx, 'v1', null);
  C.bg({fill:T.pal.bg});
  var Z = classicFrame(C, s);
  DG.drawInto(C, Z, s, designFull);
  return {bg:C.bgFill, els:C.els, kind:'chart', vid:'v1'};
};
DG.renderClassic = function(s, t, v, idx){
  var d = DG.isDesign(v) ? v : (s.dg && s.dg.v);
  var page = DG.pageClassic(s, t, d, typeof idx === 'number' ? idx : -1);
  if (!page) return '<div style="position:absolute;inset:0;background:#1B2A4A"></div>';
  return window.DSTE.toHTML(page, typeof idx === 'number' ? idx : -1, {edit:idx >= 0, slide:s});
};

/* re-colour palette-tagged elements for an accent (theme on a template deck) */
DG.repalette = function(els, ac, bg, tc, mc){
  var dark = lum(bg) < 0.5, pal = palette(ac, dark, 10);
  function c(i){ return pal[((i % pal.length) + pal.length) % pal.length]; }
  els.forEach(function(e){
    if (e.dgp != null) { var col = e.dgs != null ? mix(bg, c(e.dgp), e.dgs) : c(e.dgp); if (e.dgk === 'color') e.color = col; else if (e.t === 'path') { if (e.fill) e.fill = col; } else e.fill = col; }
    if (e.dgfix) { var fx = '#' + String(e.dgfix).replace('#', ''), fc = e.dgs != null ? mix(bg, fx, e.dgs) : fx; if (e.dgk === 'color') e.color = fc; else e.fill = fc; }
    if (e.dgp2 != null && e.t === 'path') e.stroke = c(e.dgp2);
    if (e.dgon != null) e.color = on(e.dgons != null ? mix(bg, c(e.dgon), e.dgons) : c(e.dgon));
    if (e.dgmark) {
      var sp = e.dgmark; sp.tc = tc || sp.tc; sp.mc = mc || sp.mc; sp.bgc = bg;
      (sp.ser || []).forEach(function(se){ if (se.pi != null) se.c = c(se.pi); });
      if (sp.k === 'pie' || sp.k === 'doughnut') sp.pc = (sp.cats || []).map(function(_, i){ return c(sp.pis ? sp.pis[i] : i); });
    }
  });
};

/* ============================================================
   HOOKS into the editor (Snippet 17 / DS Charts / template engine)
   ============================================================ */
function curSlide(){ var S = window.S; return S && S.data && S.data.slides && S.idx >= 0 ? S.data.slides[S.idx] : null; }
function designList(s){
  var cur = DG.designOf(s), list = DG.related(s);
  return list.map(function(id){ var d = DG.designs[id]; return {id:cur && id === cur.full ? 'v1' : id, lbl:d.name}; });
}
DG.designList = designList;
var CHART_V1 = 'v1';
function hookChart(){
  var DC = window.DS_CHART; if (!DC || DC.__dg || !DC.renderClassic) return false;
  var rc = DC.renderClassic, vs = DC.variants;
  DC.renderClassic = function(s, t, v, idx){ return DG.is(s) ? DG.renderClassic(s, t, v, idx) : rc.apply(this, arguments); };
  DC.variants = function(){ var s = curSlide(); return DG.is(s) ? designList(s) : (vs ? vs.apply(this, arguments) : []); };
  DC.__dg = 1; return true;
}
function hookEngine(){
  var D = window.DSTE; if (!D || D.__dg || !D.renderPage || !D.B) return false;
  var rp = D.renderPage, ko = D.kindOf, vf = D.variantsFor, bc = D.B.chart;
  D.kindOf = function(slide){ return DG.is(slide) ? 'dg:' + (DG.designOf(slide) || {}).full : ko.apply(this, arguments); };
  D.variantsFor = function(T, kind){
    if (typeof kind === 'string' && kind.indexOf('dg:') === 0) {
      var s = curSlide(), cur = kind.slice(3);
      var list = DG.is(s) ? DG.related(s) : (DG.designs[cur] ? DG.types[DG.designs[cur].type].designs.map(function(d){ return d.full; }) : []);
      return list.map(function(id){ return D.V(id === cur ? 'v1' : id, DG.designs[id].name, {}); });
    }
    return vf.apply(this, arguments);
  };
  D.B.chart = function(C, Z, o){
    if (DG.is(C.s)) { DG.drawInto(C, Z, C.s, DG._cur); return; }
    return bc ? bc.apply(this, arguments) : undefined;
  };
  D.renderPage = function(T, slide, vid, idx, deck){
    if (!DG.is(slide)) return rp.apply(this, arguments);
    DG._cur = DG.isDesign(vid) ? vid : slide.dg.v;
    var list = vf.call(D, T, 'chart'), v1 = list && list[0] ? list[0].id : CHART_V1;
    var page;
    try { page = rp.call(this, T, slide, v1, idx, deck); } finally { var cur = DG._cur; DG._cur = null; }
    if (page) {
      page.vid = DG.isDesign(vid) ? vid : 'v1';
      if (T && T.__rec) { var R = T.__rec; DG.repalette(page.els, R.acx, R.bg, R.text, R.muted); }
    }
    return page;
  };
  D.__dg = 1; return true;
}
function hookPick(){
  if (typeof window.dspVPPick !== 'function' || window.dspVPPick.__dg) return !!(window.dspVPPick && window.dspVPPick.__dg);
  var pk = window.dspVPPick;
  window.dspVPPick = function(idx, vid){
    var S = window.S, s = S && S.data && S.data.slides ? S.data.slides[idx] : null;
    if (DG.is(s) && DG.isDesign(vid)) { DG.setDesign(idx, vid); vid = 'v1'; }
    return pk.call(this, idx, vid);
  };
  window.dspVPPick.__dg = 1; return true;
}
DG.deckArrays = function(){
  var a = [];
  if (window.S && S.data && Array.isArray(S.data.slides)) a.push(S.data.slides);
  if (window.G && G.pptData && Array.isArray(G.pptData.slides) && a.indexOf(G.pptData.slides) < 0) a.push(G.pptData.slides);
  return a;
};
/* switch a diagram's data to another design; when the TYPE changes and the new type has fixed
   groups (SWOT ↔ PESTLE ↔ Ansoff …) the points are re-labelled to the new groups in order,
   and switching back to the original type restores the original points */
/* points whose group belongs to another framework (e.g. SWOT points saved on a PESTLE slide) get this
   framework's free groups, in order of appearance — permanently, so editor + AI fill see real groups */
DG.fixGroups = function(dg){
  var t = dg && DG.types[dg.t]; if (!t || !t.groups || !t.groups.length || !Array.isArray(dg.rows)) return false;
  var names = dg.cols && dg.cols.length ? dg.cols : t.groups, low = names.map(function(n){ return String(n).toLowerCase(); }), unknown = [], used = {};
  dg.rows.forEach(function(r){
    if (!r || !r.g) return;
    if (names.indexOf(r.g) < 0) { var gl = String(r.g).toLowerCase(), k = low.findIndex(function(n){ return gl.length >= 4 && n.indexOf(gl.slice(0, 4)) === 0; }); if (k >= 0) r.g = names[k]; }
    if (names.indexOf(r.g) >= 0) used[r.g] = 1; else if (unknown.indexOf(r.g) < 0) unknown.push(r.g);
  });
  if (!unknown.length) return false;
  var free = names.filter(function(n){ return !used[n]; });
  dg.rows.forEach(function(r){ var j = r ? unknown.indexOf(r.g) : -1; if (j >= 0) r.g = free.length ? free[Math.min(j, free.length - 1)] : names[Math.min(j, names.length - 1)]; });
  return true;
};
/* group names of a framework diagram that have no points yet */
DG.emptyGroups = function(dg){
  var t = dg && DG.types[dg.t]; if (!t || !t.groups || !t.groups.length) return [];
  var names = dg.cols && dg.cols.length ? dg.cols : t.groups, have = {};
  (dg.rows || []).forEach(function(r){ if (r && r.g && String(r.l || '').trim()) have[r.g] = 1; });
  return names.filter(function(n){ return !have[n]; });
};
DG.retarget = function(dg, full){
  var d = DG.designs[full]; if (!d || !dg) return dg;
  var nt = DG.types[d.type], ot = DG.types[dg.t];
  if (ot && nt && ot.id !== nt.id && nt.groups && nt.groups.length) {
    if (dg.orig && dg.orig.t === nt.id) { dg.rows = clone(dg.orig.rows || []); dg.cols = clone(dg.orig.cols || []); delete dg.orig; }
    else {
      if (!dg.orig) dg.orig = {t:ot.id, rows:clone(dg.rows || []), cols:clone(dg.cols || [])};
      var old = (dg.cols && dg.cols.length ? dg.cols : (ot.groups || [])).slice();
      (dg.rows || []).forEach(function(r){ if (r && r.g && old.indexOf(r.g) < 0) old.push(r.g); });
      var nn = nt.groups;
      dg.rows = (dg.rows || []).map(function(r){ var o = clone(r || {}), i = old.indexOf(o.g); if (i < 0) i = 0; o.g = nn[Math.min(i, nn.length - 1)]; return o; });
      dg.cols = [];
      if (DG.emptyGroups(dg).length) dg._needFill = 1;      /* e.g. SWOT (4) → PESTLE (6): AI writes the 2 missing groups */
    }
  } else if (ot && nt && ot.id !== nt.id && dg.orig && dg.orig.t === nt.id) { delete dg.orig; }
  dg.t = d.type; dg.v = full;
  return dg;
};
DG.setDesign = function(idx, full){
  var d = DG.designs[full]; if (!d) return;
  DG.deckArrays().forEach(function(a){ var s = a[idx]; if (!s || !s.dg) return; DG.retarget(s.dg, full); });
};
var _hk = setInterval(function(){ var a = hookChart(), b = hookEngine(), c = hookPick(); if (a && b && c) clearInterval(_hk); }, 300);
DG.hookNow = function(){ hookChart(); hookEngine(); hookPick(); };
})();

/* ============================================================
   DS Diagrams — native chart preview (drawn with shapes; the .pptx gets a
   real PowerPoint chart built from the same spec on the server)
   spec: {k:'bar'|'line'|'area'|'pie'|'doughnut'|'combo'|'radar'|'scatter'|'bubble',
          dir:'col'|'bar', grp:'clustered'|'stacked'|'percentStacked'|'standard',
          cats:[..], ser:[{n, v:[..], pi, ty:'bar'|'line', ax2, x:[..], y:[..], z:[..]}],
          unit, unit2, lbl, leg:'t'|'b'|'r'|'none', gap, hole, smooth, mk, pis:[..] (pie colours)}
   ============================================================ */
(function(){
var DG = window.DS_DG; if (!DG || DG.drawChart) return;
var fmt = DG.fmt, niceMax = DG.niceMax, u = DG.u;

function ticks(lo, hi){
  if (hi <= lo) hi = lo + 1;
  var span = hi - lo, step = niceMax(span / 4.2) , n;
  if (step <= 0) step = 1;
  var a = Math.floor(lo / step) * step, b = Math.ceil(hi / step) * step, out = [];
  for (n = a; n <= b + step*0.001; n += step) out.push(Math.round(n / step) * step);
  return out;
}
DG.ticks = ticks;

DG.drawChart = function(K, x, y, w, h, sp){
  var k = sp.k, ser = sp.ser || [], cats = sp.cats || [], fs = Math.max(12, Math.min(16, h * 0.034));
  var leg = sp.leg || (ser.length > 1 && k !== 'pie' && k !== 'doughnut' ? 't' : 'none');
  if (leg !== 'none' && ser.length) {
    var items = ser.map(function(s){ return {l:s.n || '', pi:s.pi}; });
    if (leg === 't') { K.legend(items, x, y, w, {size:fs, align:'r'}); y += fs*2.1; h -= fs*2.1; }
  }
  if (k === 'pie' || k === 'doughnut') return pie(K, x, y, w, h, sp, fs);
  if (k === 'radar') return radar(K, x, y, w, h, sp, fs);
  if (k === 'scatter' || k === 'bubble') return scatter(K, x, y, w, h, sp, fs);
  return axes(K, x, y, w, h, sp, fs);
};

/* bar / line / area / combo on category axes */
function axes(K, x, y, w, h, sp, fs){
  var ser = sp.ser, cats = sp.cats, n = cats.length, hbar = sp.k === 'bar' && sp.dir === 'bar';
  var grp = sp.grp || 'clustered', pct = grp === 'percentStacked', stacked = grp === 'stacked' || pct;
  var main = ser.filter(function(s){ return !s.ax2; }), sec = ser.filter(function(s){ return s.ax2; });
  var lo = 0, hi = 0;
  if (stacked) {
    for (var i = 0; i < n; i++) { var p = 0, q = 0; main.forEach(function(s){ var v = +s.v[i] || 0; if (v >= 0) p += v; else q += v; }); hi = Math.max(hi, p); lo = Math.min(lo, q); }
    if (pct) { hi = 100; lo = 0; }
  } else main.forEach(function(s){ s.v.forEach(function(v){ v = +v || 0; hi = Math.max(hi, v); lo = Math.min(lo, v); }); });
  if (sp.max) hi = sp.max;
  var T = ticks(lo, hi); lo = T[0]; hi = T[T.length - 1];
  var T2 = null, hi2 = 1;
  if (sec.length) { var m2 = 0; sec.forEach(function(s){ s.v.forEach(function(v){ m2 = Math.max(m2, +v || 0); }); }); if (sp.max2) m2 = sp.max2; T2 = ticks(0, m2); hi2 = T2[T2.length - 1]; }
  var labW = hbar ? Math.min(w * 0.28, Math.max.apply(null, cats.map(function(c){ return String(c).length; }).concat([4])) * fs * 0.56 + 12) : Math.max(36, String(fmt(hi, pct ? '%' : sp.unit)).length * fs * 0.58 + 10);
  var r2w = T2 ? Math.max(36, String(fmt(hi2, sp.unit2 || '')).length * fs * 0.58 + 10) : 0;
  var px = x + labW, pw = w - labW - r2w - (hbar ? 44 : 6), py = y + 8, ph = h - (hbar ? 20 : fs * 2.6) - 8;
  var mc = K.mc, grid = K.ln;
  /* grid + value labels */
  T.forEach(function(t){
    var f = (t - lo) / (hi - lo);
    if (hbar) { var gx = px + f*pw; K.line(gx, py, gx, py + ph, {color:grid, lw:1, dash:t !== 0}); K.t(gx - 40, py + ph + 4, 80, fs*1.4, fmt(t, pct ? '%' : sp.unit), {size:fs*0.85, color:mc, align:'c', lines:1}); }
    else { var gy = py + ph - f*ph; K.line(px, gy, px + pw, gy, {color:grid, lw:1, dash:t !== 0}); K.t(x, gy - fs*0.7, labW - 10, fs*1.4, fmt(t, pct ? '%' : sp.unit), {size:fs*0.85, color:mc, align:'r', lines:1}); }
  });
  if (T2) T2.forEach(function(t){ var gy = py + ph - (t/hi2)*ph; K.t(px + pw + 8, gy - fs*0.7, r2w - 8, fs*1.4, fmt(t, sp.unit2 || ''), {size:fs*0.85, color:mc, lines:1}); });
  var band = (hbar ? ph : pw) / Math.max(1, n);
  /* category labels */
  cats.forEach(function(c, i){
    if (hbar) K.t(x, py + band*i, labW - 12, band, c, {max:fs, min:10, color:K.txt, wt:600, align:'r', lines:2});
    else K.t(px + band*i + 2, py + ph + 6, band - 4, fs*2.4, c, {max:fs*0.95, min:9, color:mc, align:'c', va:'t', lines:2});
  });
  var bars = main.filter(function(s){ return sp.k === 'bar' || s.ty === 'bar' || (sp.k === 'combo' && s.ty !== 'line'); });
  var linesS = ser.filter(function(s){ return (sp.k === 'line') || s.ty === 'line'; });
  var areas = sp.k === 'area' ? main : [];
  var zero = (0 - lo) / (hi - lo);
  function vy(v, ax2){ return ax2 ? py + ph - (v/hi2)*ph : py + ph - ((v - lo)/(hi - lo))*ph; }
  /* bars */
  if (bars.length) {
    var gapf = sp.gap != null ? sp.gap / 100 : 0.55, inner = band / (1 + gapf), m = stacked ? 1 : bars.length, bw = inner / m;
    for (var ci = 0; ci < n; ci++) {
      var tot = 0; if (pct) bars.forEach(function(s){ tot += Math.abs(+s.v[ci] || 0); });
      var accP = 0, accN = 0;
      bars.forEach(function(s, si){
        var v = +s.v[ci] || 0; if (pct) v = tot ? v / tot * 100 : 0;
        var base = stacked ? (v >= 0 ? accP : accN) : 0, top = base + v;
        if (stacked) { if (v >= 0) accP = top; else accN = top; }
        var f0 = (base - lo)/(hi - lo), f1 = (top - lo)/(hi - lo), off = band*ci + (band - inner)/2 + (stacked ? 0 : si*bw);
        if (hbar) {
          var bx = px + Math.min(f0, f1)*pw, bwid = Math.abs(f1 - f0)*pw;
          K.rect(bx, py + off + bw*0.06, Math.max(1, bwid), bw*0.88, {pi:s.pi});
          if (sp.lbl && !stacked) K.t(bx + bwid + 6, py + off, 90, bw, fmt(+s.v[ci], sp.unit), {max:fs, min:9, wt:700, color:K.txt, lines:1});
          else if (sp.lbl && bwid > 44) K.tOn(bx, py + off, bwid, bw, fmt(pct ? v : +s.v[ci], pct ? '%' : sp.unit), s.pi, {max:fs*0.9, min:8, wt:700, align:'c', lines:1});
        } else {
          var by = py + ph - Math.max(f0, f1)*ph, bh = Math.abs(f1 - f0)*ph;
          K.rect(px + off + bw*0.06, by, bw*0.88, Math.max(1, bh), {pi:s.pi});
          if (sp.lbl && !stacked) K.t(px + off - 10, by - fs*1.6, bw + 20, fs*1.4, fmt(+s.v[ci], sp.unit), {max:fs, min:8, wt:700, color:K.txt, align:'c', lines:1});
          else if (sp.lbl && bh > fs*1.6) K.tOn(px + off, by, bw, bh, fmt(pct ? v : +s.v[ci], pct ? '%' : sp.unit), s.pi, {max:fs*0.9, min:8, wt:700, align:'c', lines:1});
        }
      });
    }
  }
  /* areas */
  if (areas.length) {
    var accs = cats.map(function(){ return 0; });
    areas.forEach(function(s, si){
      var top = [], bot = [];
      cats.forEach(function(_, i){
        var v = +s.v[i] || 0, b = grp === 'stacked' ? accs[i] : 0, t = b + v; if (grp === 'stacked') accs[i] = t;
        var xx = px + (n > 1 ? pw * i/(n - 1) : pw/2);
        top.push([xx, vy(t)]); bot.push([xx, vy(b)]);
      });
      var d = top.concat(bot.slice().reverse());
      K.poly(d, {pi:s.pi, op:grp === 'stacked' ? 0.85 : 0.35});
      K.polyline(top, {spi:s.pi, lw:3});
    });
  }
  /* lines */
  linesS.forEach(function(s){
    var pts = cats.map(function(_, i){
      var xx = (sp.k === 'combo' || bars.length) ? px + band*i + band/2 : px + (n > 1 ? pw * i/(n - 1) : pw/2);
      return [xx, vy(+s.v[i] || 0, s.ax2)];
    });
    if (sp.smooth) K.curve(pts, {spi:s.pi, lw:3.5}); else K.polyline(pts, {spi:s.pi, lw:3.5});
    if (sp.mk !== false) pts.forEach(function(p, i){
      K.circ(p[0], p[1], 6, {pi:s.pi, line:K.bg, lw:2});
      if (sp.lbl) K.t(p[0] - 40, p[1] - fs*2, 80, fs*1.4, fmt(+s.v[i], s.ax2 ? (sp.unit2 || '') : sp.unit), {max:fs*0.9, min:8, wt:700, color:K.txt, align:'c', lines:1});
    });
  });
}

function pie(K, x, y, w, h, sp, fs){
  var s = sp.ser[0] || {v:[]}, vals = s.v.map(function(v){ return Math.max(0, +v || 0); }), tot = vals.reduce(function(a, b){ return a + b; }, 0) || 1;
  var legW = sp.leg === 'none' ? 0 : Math.min(w * 0.42, 330), R = Math.min((w - legW) / 2, h / 2) - 6, cx = x + (w - legW) / 2, cy = y + h / 2;
  if (legW) cx = x + R + 10;
  var hole = sp.k === 'doughnut' ? (sp.hole || 58) / 100 : 0, a = sp.angle || 0;
  vals.forEach(function(v, i){
    var sw = v / tot * 360; if (sw <= 0) return;
    var pi = sp.pis ? sp.pis[i] : i;
    K.sector(cx, cy, R*hole, R, a, a + sw, {pi:pi, stroke:K.bg, lw:3});
    if (sw > 18) { var mid = a + sw/2, rr = hole ? R*(1 + hole)/2 : R*0.64, p = DG.pol(cx, cy, rr, mid);
      K.tOn(p[0] - 44, p[1] - fs, 88, fs*2, Math.round(v / tot * 100) + '%', pi, {max:fs*1.1, min:9, wt:800, align:'c', lines:1}); }
    a += sw;
  });
  if (hole && sp.center) {
    K.t(cx - R*hole*0.9, cy - R*hole*0.42, R*hole*1.8, R*hole*0.5, sp.center[0], {max:R*0.26, min:14, wt:800, color:K.tc, align:'c', font:'head', lines:1});
    K.t(cx - R*hole*0.9, cy + R*hole*0.08, R*hole*1.8, R*hole*0.36, sp.center[1], {max:fs, min:9, color:K.mc, align:'c', lines:2});
  }
  if (legW) {
    var n = vals.length, rh = Math.min(54, h / Math.max(n, 1)), y0 = cy - rh*n/2, lx = x + w - legW + 10;
    (sp.cats || []).forEach(function(c, i){
      var yy = y0 + i*rh, pi = sp.pis ? sp.pis[i] : i;
      K.rect(lx, yy + rh/2 - 7, 14, 14, {pi:pi, r:4});
      K.t(lx + 24, yy, legW - 110, rh, c, {max:fs*1.05, min:10, color:K.txt, wt:600, lines:2});
      K.t(lx + legW - 90, yy, 80, rh, fmt(vals[i], sp.unit), {max:fs*1.2, min:10, color:K.tc, wt:800, align:'r', lines:1, font:'head'});
    });
  }
}

function radar(K, x, y, w, h, sp, fs){
  var cats = sp.cats, n = cats.length, R = Math.min(w, h) / 2 - fs*3.2, cx = x + w/2, cy = y + h/2 + fs*0.4;
  var mx = 0; sp.ser.forEach(function(s){ s.v.forEach(function(v){ mx = Math.max(mx, +v || 0); }); });
  var T = ticks(0, sp.max || mx), top = T[T.length - 1];
  T.forEach(function(t, ti){ if (!ti) return; var pts = cats.map(function(_, i){ return DG.pol(cx, cy, R*t/top, 360*i/n); }); K.poly(pts, {line:K.ln, lw:1, fill:null}); });
  cats.forEach(function(c, i){ var p = DG.pol(cx, cy, R, 360*i/n), q = DG.pol(cx, cy, R + fs*1.6, 360*i/n); K.line(cx, cy, p[0], p[1], {color:K.ln, lw:1});
    K.t(q[0] - 80, q[1] - fs*1.2, 160, fs*2.4, c, {max:fs, min:9, color:K.txt, wt:600, align:'c', lines:2}); });
  sp.ser.forEach(function(s){
    var pts = cats.map(function(_, i){ return DG.pol(cx, cy, R*(Math.max(0, +s.v[i] || 0)/top), 360*i/n); });
    K.poly(pts, {pi:s.pi, op:0.22}); K.poly(pts, {spi:s.pi, lw:3, fill:null});
    pts.forEach(function(p){ K.circ(p[0], p[1], 5, {pi:s.pi}); });
  });
}

function scatter(K, x, y, w, h, sp, fs){
  var xs = [], ys = [], zs = [];
  sp.ser.forEach(function(s){ (s.x || []).forEach(function(v, i){ xs.push(+v || 0); ys.push(+s.y[i] || 0); zs.push(Math.max(0, +(s.z || [])[i] || 0)); }); });
  var TX = ticks(Math.min(0, Math.min.apply(null, xs.concat([0]))), Math.max.apply(null, xs.concat([1]))), TY = ticks(Math.min(0, Math.min.apply(null, ys.concat([0]))), Math.max.apply(null, ys.concat([1])));
  var x0 = TX[0], x1 = TX[TX.length-1], y0 = TY[0], y1 = TY[TY.length-1], labW = 52, px = x + labW, pw = w - labW - 14, py = y + 8, ph = h - fs*3.4 - 8;
  TY.forEach(function(t){ var gy = py + ph - (t - y0)/(y1 - y0)*ph; K.line(px, gy, px + pw, gy, {color:K.ln, lw:1, dash:t !== 0}); K.t(x, gy - fs*0.7, labW - 8, fs*1.4, fmt(t, sp.unitY || ''), {size:fs*0.85, color:K.mc, align:'r', lines:1}); });
  TX.forEach(function(t){ var gx = px + (t - x0)/(x1 - x0)*pw; K.line(gx, py, gx, py + ph, {color:K.ln, lw:1, dash:t !== 0}); K.t(gx - 40, py + ph + 4, 80, fs*1.4, fmt(t, sp.unitX || ''), {size:fs*0.85, color:K.mc, align:'c', lines:1}); });
  if (sp.axX) K.t(px, py + ph + fs*1.6, pw, fs*1.5, sp.axX, {size:fs*0.9, color:K.mc, align:'c', wt:600, lines:1});
  var zmax = Math.max.apply(null, zs.concat([1])), rmax = Math.min(pw, ph) * 0.09;
  sp.ser.forEach(function(s){
    (s.x || []).forEach(function(v, i){
      var gx = px + ((+v || 0) - x0)/(x1 - x0)*pw, gy = py + ph - ((+s.y[i] || 0) - y0)/(y1 - y0)*ph;
      var r = sp.k === 'bubble' ? Math.max(8, Math.sqrt((+(s.z || [])[i] || 0) / zmax) * rmax) : 8;
      K.circ(gx, gy, r, {pi:s.pi, op:sp.k === 'bubble' ? 0.72 : 1, line:K.bg, lw:1.5});
      if (s.l && s.l[i]) K.t(gx + r + 4, gy - fs*0.7, 150, fs*1.4, s.l[i], {max:fs*0.9, min:9, color:K.txt, wt:600, lines:1});
    });
  });
}
})();

/* DS Diagrams — Data charts (real, editable PowerPoint charts in the download) */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('charts', 'Data Charts', '📊', 'Bar, column, line, area, pie, combo, radar, scatter');
var fmt = DG.fmt;

function spec(D, k, o){
  o = o || {};
  var ser = D.cols.map(function(n, i){ return {n:n, v:D.rows.map(function(r){ return r.v[i] || 0; }), pi:i}; });
  if (o.only1) ser = ser.slice(0, 1);
  var s = {k:k, cats:D.rows.map(function(r){ return r.l; }), ser:ser, unit:D.unit, lbl:o.lbl !== false};
  for (var key in o) if (key !== 'only1') s[key] = o[key];
  return s;
}
DG.serSpec = spec;
var SF = [{k:'l', n:'Category'}];
function sample(cols, rows, unit){ return {cols:cols, rows:rows.map(function(r){ return {l:r[0], v:r.slice(1)}; }), unit:unit || ''}; }
var REV = sample(['2025', '2026'], [['Q1', 42, 51], ['Q2', 48, 58], ['Q3', 55, 67], ['Q4', 61, 78]], '$');
var MIX = sample(['Product', 'Services', 'Licences'], [['North America', 48, 22, 14], ['Europe', 36, 18, 11], ['Asia Pacific', 29, 12, 9], ['Latin America', 12, 6, 4]], '$');

/* big number cards beside a chart */
function kpiCol(K, x, y, w, h, D){
  var s = D.rows.map(function(r){ return {l:r.l, v:r.v[D.cols.length - 1] || 0}; });
  var tot = s.reduce(function(a, b){ return a + b.v; }, 0), best = s.slice().sort(function(a, b){ return b.v - a.v; })[0];
  var first = D.rows[0] ? D.rows[0].v[D.cols.length - 1] || 0 : 0, last = D.rows.length ? D.rows[D.rows.length - 1].v[D.cols.length - 1] || 0 : 0;
  var growth = first ? Math.round((last - first) / Math.abs(first) * 100) : 0;
  var cards = [[fmt(tot, D.unit), 'Total ' + (D.cols[D.cols.length - 1] || '')], [(growth >= 0 ? '+' : '') + growth + '%', 'Change ' + (D.rows[0] ? D.rows[0].l : '') + ' → ' + (D.rows.length ? D.rows[D.rows.length - 1].l : '')], [best ? best.l : '', 'Highest · ' + (best ? fmt(best.v, D.unit) : '')]];
  var gap = 16, ch = (h - gap*2) / 3;
  cards.forEach(function(c, i){
    var yy = y + i*(ch + gap), cc = K.card(x, yy, w, ch, {hl:i === 0});
    K.t(x + 24, yy + 10, w - 48, ch*0.55, c[0], {max:44, min:18, font:'head', wt:800, color:i === 0 ? cc.tc : K.col(0), va:'b', lines:1});
    K.t(x + 24, yy + ch*0.58, w - 48, ch*0.36, c[1], {max:16, min:10, color:cc.mc, va:'t', lines:2});
  });
}

DG.type({id:'column', cat:'charts', name:'Column Chart', fields:SF, series:true, min:2, max:12, sample:REV,
  ai:'categories (time periods or items) with 1-3 series of numbers; cols = series names.',
  designs:[
    {id:'col1', name:'Column chart', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'col', grp:'clustered', gap:60})); }},
    {id:'col2', name:'Column + key numbers', draw:function(K, Z, D){ var kw = Math.round(Z.w*0.27); kpiCol(K, Z.x, Z.y, kw, Z.h, D); K.nativeChart(Z.x + kw + 36, Z.y, Z.w - kw - 36, Z.h, spec(D, 'bar', {dir:'col', grp:'clustered', gap:60})); }},
    {id:'col3', name:'Column + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'col', grp:'clustered', gap:60})); }}
  ]});
DG.type({id:'bar', cat:'charts', name:'Bar Chart', fields:SF, series:true, min:2, max:12,
  sample:sample(['Satisfaction'], [['Ease of use', 92], ['Support quality', 88], ['Reliability', 84], ['Integrations', 71], ['Pricing', 63]], '%'),
  ai:'items to compare with 1-2 series of numbers; cols = series names.',
  designs:[
    {id:'bar1', name:'Bar chart · ranked', draw:function(K, Z, D){ var d = DG.clone(D); d.rows.sort(function(a, b){ return (b.v[0] || 0) - (a.v[0] || 0); }); K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(d, 'bar', {dir:'bar', grp:'clustered', gap:45})); }},
    {id:'bar2', name:'Bar chart + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'bar', grp:'clustered', gap:45})); }}
  ]});
DG.type({id:'stackcol', cat:'charts', name:'Stacked Column Chart', fields:SF, series:true, min:2, max:12, sample:MIX,
  ai:'categories with 2-4 series that add up to a total; cols = series names.',
  designs:[
    {id:'stcol1', name:'Stacked column', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'col', grp:'stacked', gap:55})); }},
    {id:'stcol2', name:'100% stacked column', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'col', grp:'percentStacked', gap:55})); }}
  ]});
DG.type({id:'stackbar', cat:'charts', name:'Stacked Bar Chart', fields:SF, series:true, min:2, max:12, sample:MIX,
  ai:'categories with 2-4 series that add up to a total; cols = series names.',
  designs:[
    {id:'stbar1', name:'Stacked bar', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'bar', grp:'stacked', gap:45})); }},
    {id:'stbar2', name:'100% stacked bar', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'bar', {dir:'bar', grp:'percentStacked', gap:45})); }}
  ]});
DG.type({id:'line', cat:'charts', name:'Line Chart', fields:SF, series:true, min:3, max:16,
  sample:sample(['Revenue', 'Target'], [['Jan', 3.2, 3.0], ['Feb', 3.6, 3.3], ['Mar', 3.4, 3.6], ['Apr', 4.1, 3.9], ['May', 4.6, 4.2], ['Jun', 5.2, 4.5]], '$M'),
  ai:'time periods in order with 1-3 series; cols = series names.',
  designs:[
    {id:'line1', name:'Line chart', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'line', {smooth:true, lbl:D.rows.length <= 8 && D.cols.length === 1})); }},
    {id:'line2', name:'Line + growth callout', draw:function(K, Z, D){
      var kw = Math.round(Z.w*0.26), s0 = D.rows.map(function(r){ return r.v[0] || 0; }), a = s0[0] || 0, b = s0[s0.length - 1] || 0, g = a ? Math.round((b - a)/Math.abs(a)*100) : 0;
      var c = K.card(Z.x, Z.y, kw, Z.h, {hl:true});
      K.t(Z.x + 26, Z.y + 30, kw - 52, 30, (D.cols[0] || 'Growth').toUpperCase(), {size:14, wt:800, color:c.mc, ls:0.1, lines:1});
      K.t(Z.x + 26, Z.y + 64, kw - 52, 90, (g >= 0 ? '+' : '') + g + '%', {max:72, min:28, font:'head', wt:900, color:c.tc, lines:1});
      K.t(Z.x + 26, Z.y + 160, kw - 52, 60, (D.rows[0] ? D.rows[0].l : '') + ' → ' + (D.rows.length ? D.rows[D.rows.length - 1].l : ''), {max:18, min:11, color:c.mc, lines:2, va:'t'});
      K.t(Z.x + 26, Z.y + Z.h - 110, kw - 52, 44, K.fmt(b), {max:34, min:16, font:'head', wt:800, color:c.tc, lines:1});
      K.t(Z.x + 26, Z.y + Z.h - 62, kw - 52, 36, 'latest value', {size:14, color:c.mc, lines:1});
      K.nativeChart(Z.x + kw + 36, Z.y, Z.w - kw - 36, Z.h, spec(D, 'line', {smooth:true, lbl:D.cols.length === 1 && D.rows.length <= 8}));
    }},
    {id:'line3', name:'Line + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'line', {smooth:true, lbl:false})); }}
  ]});
DG.type({id:'area', cat:'charts', name:'Area Chart', fields:SF, series:true, min:3, max:16,
  sample:sample(['Organic', 'Paid', 'Referral'], [['Jan', 12, 8, 3], ['Feb', 14, 9, 4], ['Mar', 17, 9, 5], ['Apr', 19, 11, 5], ['May', 23, 12, 6], ['Jun', 27, 13, 8]], 'K'),
  ai:'time periods in order with 1-3 series; cols = series names.',
  designs:[
    {id:'area1', name:'Area chart', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'area', {grp:'standard', lbl:false})); }},
    {id:'area2', name:'Stacked area', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'area', {grp:'stacked', lbl:false})); }}
  ]});
DG.type({id:'pie', cat:'charts', name:'Pie Chart', fields:SF, series:true, min:2, max:8,
  sample:sample(['Share'], [['Enterprise', 46], ['Mid-market', 28], ['Small business', 17], ['Public sector', 9]], '%'),
  ai:'2-6 parts of a whole; values are percentages adding up to 100; one series.',
  designs:[
    {id:'pie1', name:'Pie + legend', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'pie', {only1:true, leg:'r'})); }},
    {id:'pie2', name:'Pie + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'pie', {only1:true, leg:'r'})); }}
  ]});
DG.type({id:'doughnut', cat:'charts', name:'Doughnut Chart', fields:SF, series:true, min:2, max:8,
  sample:sample(['Budget'], [['Salaries', 52], ['Marketing', 18], ['Technology', 16], ['Operations', 14]], '%'),
  ai:'2-6 parts of a whole; values are percentages adding up to 100; one series.',
  designs:[
    {id:'dn1', name:'Doughnut + total', draw:function(K, Z, D){ var t = 0; D.rows.forEach(function(r){ t += r.v[0] || 0; }); K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'doughnut', {only1:true, leg:'r', hole:60, center:[K.fmt(t), D.cols[0] || 'Total']})); }},
    {id:'dn2', name:'Doughnut + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'doughnut', {only1:true, leg:'r', hole:62})); }}
  ]});
DG.type({id:'combo', cat:'charts', name:'Combo Chart', fields:SF, series:true, min:3, max:12,
  sample:sample(['Revenue ($M)', 'Margin (%)'], [['2021', 12, 18], ['2022', 16, 21], ['2023', 21, 24], ['2024', 27, 26], ['2025', 34, 29]]),
  ai:'time periods with exactly 2 series: first an amount (bars), second a rate or percentage (line); cols = series names with units.',
  designs:[
    {id:'combo1', name:'Bars + line (two axes)', draw:function(K, Z, D){
      var s = spec(D, 'combo', {dir:'col', grp:'clustered', gap:70, leg:'t'});
      s.ser.forEach(function(se, i){ if (i === s.ser.length - 1 && s.ser.length > 1) { se.ty = 'line'; se.ax2 = true; } else se.ty = 'bar'; });
      K.nativeChart(Z.x, Z.y, Z.w, Z.h, s);
    }}
  ]});
DG.type({id:'radar', cat:'charts', name:'Radar Chart', fields:SF, series:true, min:3, max:10,
  sample:sample(['Our product', 'Competitor'], [['Price', 7, 8], ['Quality', 9, 6], ['Support', 8, 5], ['Features', 8, 9], ['Speed', 9, 7], ['Security', 7, 6]]),
  ai:'3-8 criteria scored 1-10 for 1-3 options; cols = option names.',
  designs:[
    {id:'radar1', name:'Radar comparison', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'radar', {lbl:false, max:10})); }},
    {id:'radar2', name:'Radar + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, spec(D, 'radar', {lbl:false, max:10})); }}
  ]});
var XYF = [{k:'l', n:'Label'}, {k:'x', n:'X value', num:1}, {k:'y', n:'Y value', num:1}];
DG.type({id:'scatter', cat:'charts', name:'Scatter Plot', fields:XYF, min:3, max:30,
  sample:{rows:[{l:'Berlin', x:12, y:64}, {l:'Paris', x:18, y:71}, {l:'Madrid', x:9, y:52}, {l:'Rome', x:14, y:58}, {l:'Vienna', x:7, y:49}, {l:'Warsaw', x:22, y:77}, {l:'Prague', x:16, y:60}], ax:{x:'Marketing spend ($K)', y:'Leads'}},
  ai:'points with a label and two numbers; ax.x and ax.y name the two measures.',
  designs:[
    {id:'sc1', name:'Scatter plot', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'scatter', ser:D.rows.map(function(r){ return {n:r.l, x:[r.x || 0], y:[r.y || 0], l:[r.l], pi:0}; }), axX:D.ax.x || '', axY:D.ax.y || '', leg:'none'}); }}
  ]});
DG.type({id:'bubble', cat:'charts', name:'Bubble Chart', fields:XYF.concat([{k:'z', n:'Size', num:1}]), min:3, max:20,
  sample:{rows:[{l:'Product A', x:20, y:35, z:120}, {l:'Product B', x:45, y:22, z:60}, {l:'Product C', x:62, y:48, z:200}, {l:'Product D', x:30, y:58, z:90}, {l:'Product E', x:75, y:15, z:40}], ax:{x:'Market growth %', y:'Profit margin %'}},
  ai:'items with x, y and a size value z; ax.x and ax.y name the measures.',
  designs:[
    {id:'bub1', name:'Bubble chart', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'bubble', ser:D.rows.map(function(r, i){ return {n:r.l, x:[r.x || 0], y:[r.y || 0], z:[r.z || 1], l:[r.l], pi:i}; }), axX:D.ax.x || '', axY:D.ax.y || '', leg:'none'}); }}
  ]});
})();

/* DS Diagrams — Advanced analytics: waterfall, pareto, histogram, box & whisker, treemap, sunburst, heatmap, cohort */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('analytics', 'Advanced Analytics', '🔬', 'Waterfall, pareto, histogram, box plot, treemap, sunburst, heatmap');
var fmt = DG.fmt, u = DG.u;
var LV = [{k:'l', n:'Label'}, {k:'v', n:'Value', num:1}];

/* ---------------- Waterfall ---------------- */
function wfBars(D){
  var run = 0, out = [], total = D.o.total !== false;
  D.rows.forEach(function(r, i){
    var v = r.v || 0;
    if (i === 0) { out.push({l:r.l, a:0, b:v, v:v, k:'start'}); run = v; return; }
    out.push({l:r.l, a:run, b:run + v, v:v, k:v >= 0 ? 'up' : 'down'}); run += v;
  });
  if (total && out.length > 1) out.push({l:D.o.totalLabel || 'Total', a:0, b:run, v:run, k:'total'});
  return out;
}
DG.type({id:'waterfall', cat:'analytics', name:'Waterfall Chart', fields:[{k:'l', n:'Step'}, {k:'v', n:'Change (+/-)', num:1}], min:3, max:12,
  sample:{rows:[{l:'2025 revenue', v:420}, {l:'New customers', v:96}, {l:'Upsell', v:58}, {l:'Price change', v:24}, {l:'Churn', v:-47}, {l:'FX impact', v:-18}], unit:'$', o:{totalLabel:'2026 revenue'}},
  ai:'first row = starting value, next rows = positive or negative changes (numbers only); the total is added automatically.',
  designs:[
    {id:'wf1', name:'Waterfall · columns', draw:function(K, Z, D){
      var B = wfBars(D), lo = 0, hi = 0; B.forEach(function(b){ lo = Math.min(lo, b.a, b.b); hi = Math.max(hi, b.a, b.b); });
      var T = DG.ticks(lo, hi); lo = T[0]; hi = T[T.length - 1];
      var lab = 58, px = Z.x + lab, pw = Z.w - lab, py = Z.y + 34, ph = Z.h - 34 - 50, n = B.length, band = pw / n, bw = band*0.6;
      function Y(v){ return py + ph - (v - lo)/(hi - lo)*ph; }
      T.forEach(function(t){ K.line(px, Y(t), px + pw, Y(t), {color:K.ln, lw:1, dash:t !== 0}); K.t(Z.x, Y(t) - 10, lab - 10, 20, fmt(t, D.unit), {size:13, color:K.mc, align:'r', lines:1}); });
      [['Increase', K.good()], ['Decrease', K.bad()], ['Total', null]].forEach(function(l, i){ K.rect(Z.x + Z.w - 330 + i*110, Z.y + 6, 12, 12, l[1] ? {fix:l[1], r:3} : {pi:0, r:3}); K.t(Z.x + Z.w - 312 + i*110, Z.y, 90, 24, l[0], {size:13, color:K.mc, lines:1}); });
      B.forEach(function(b, i){
        var x = px + band*i + (band - bw)/2, y1 = Y(Math.max(b.a, b.b)), y2 = Y(Math.min(b.a, b.b)), pi = b.k === 'down' ? 3 : (b.k === 'up' ? 2 : 0), fx = b.k === 'down' ? K.bad() : (b.k === 'up' ? K.good() : null);
        K.rect(x, y1, bw, Math.max(2, y2 - y1), fx ? {fix:fx, r:4} : {pi:0, r:4});
        if (i < n - 1) K.line(x + bw, Y(b.b), x + band + (band - bw)/2, Y(b.b), {color:K.mc, lw:1.2, dash:true});
        K.t(x - 20, y1 - 28, bw + 40, 24, (b.k === 'up' ? '+' : '') + fmt(b.v, D.unit), {max:16, min:10, wt:800, color:K.tc, align:'c', lines:1});
        K.t(px + band*i + 2, py + ph + 8, band - 4, 42, b.l, {max:14, min:9, color:K.txt, align:'c', va:'t', lines:2, wt:b.k === 'total' || b.k === 'start' ? 700 : 400});
      });
    }},
    {id:'wf2', name:'Waterfall · horizontal bridge', draw:function(K, Z, D){
      var B = wfBars(D), lo = 0, hi = 0; B.forEach(function(b){ lo = Math.min(lo, b.a, b.b); hi = Math.max(hi, b.a, b.b); });
      var lab = Math.min(260, Z.w*0.24), px = Z.x + lab, pw = Z.w - lab - 110, n = B.length, rh = Z.h / n, bh = Math.min(40, rh*0.62);
      function X(v){ return px + (v - lo)/(hi - lo || 1)*pw; }
      B.forEach(function(b, i){
        var y = Z.y + rh*i + (rh - bh)/2, x1 = X(Math.min(b.a, b.b)), x2 = X(Math.max(b.a, b.b)), fx = b.k === 'down' ? K.bad() : (b.k === 'up' ? K.good() : null), sty = fx ? {fix:fx} : {pi:0};
        if (i % 2 === 0) K.rect(Z.x, Z.y + rh*i, Z.w, rh, {fill:K.cardc, op:0.5, r:8});
        K.t(Z.x + 14, Z.y + rh*i, lab - 24, rh, b.l, {max:17, min:10, wt:b.k === 'total' || b.k === 'start' ? 800 : 500, color:K.tc, lines:2});
        K.rect(x1, y, Math.max(3, x2 - x1), bh, {fix:sty.fix, pi:sty.pi, r:5});
        if (i < n - 1) K.line(X(b.b), y + bh, X(b.b), y + rh, {color:K.mc, lw:1.2, dash:true});
        K.t(x2 + 10, Z.y + rh*i, 100, rh, (b.k === 'up' ? '+' : '') + fmt(b.v, D.unit), {max:17, min:10, wt:800, fix:sty.fix, pi:sty.pi, lines:1});
      });
    }}
  ]});

/* ---------------- Pareto (real chart: bars + cumulative % line) ---------------- */
DG.type({id:'pareto', cat:'analytics', name:'Pareto Chart', fields:LV, min:3, max:12,
  sample:{rows:[{l:'Late delivery', v:142}, {l:'Damaged goods', v:88}, {l:'Wrong item', v:51}, {l:'Billing error', v:32}, {l:'Poor packaging', v:18}, {l:'Other', v:9}], unit:''},
  ai:'causes or categories with counts (numbers).',
  designs:[
    {id:'par1', name:'Pareto · bars + cumulative line', draw:function(K, Z, D){
      var r = D.rows.slice().sort(function(a, b){ return (b.v || 0) - (a.v || 0); }), tot = 0; r.forEach(function(x){ tot += x.v || 0; });
      var acc = 0, cum = r.map(function(x){ acc += x.v || 0; return tot ? Math.round(acc / tot * 1000)/10 : 0; });
      K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'combo', dir:'col', grp:'clustered', gap:25, cats:r.map(function(x){ return x.l; }), leg:'t', lbl:true, unit:D.unit, unit2:'%', max2:100,
        ser:[{n:'Count', v:r.map(function(x){ return x.v || 0; }), pi:0, ty:'bar'}, {n:'Cumulative %', v:cum, pi:3, ty:'line', ax2:true}]});
    }},
    {id:'par2', name:'Pareto · 80/20 split', side:false, draw:function(K, Z, D){
      var r = D.rows.slice().sort(function(a, b){ return (b.v || 0) - (a.v || 0); }), tot = 0; r.forEach(function(x){ tot += x.v || 0; });
      var acc = 0, cut = -1; r.forEach(function(x, i){ acc += x.v || 0; if (cut < 0 && tot && acc / tot >= 0.8) cut = i; });
      var kw = Math.round(Z.w*0.28), c = K.card(Z.x, Z.y, kw, Z.h, {hl:true}), topN = cut + 1, sh = tot ? Math.round(r.slice(0, topN).reduce(function(a, b){ return a + (b.v || 0); }, 0) / tot * 100) : 0;
      K.t(Z.x + 26, Z.y + 30, kw - 52, 26, 'THE VITAL FEW', {size:14, wt:800, color:c.mc, ls:0.1, lines:1});
      K.t(Z.x + 26, Z.y + 60, kw - 52, 96, sh + '%', {max:78, min:30, font:'head', wt:900, color:c.tc, lines:1});
      K.t(Z.x + 26, Z.y + 160, kw - 52, 80, 'of the total comes from the top ' + topN + ' of ' + r.length + ' items', {max:19, min:11, color:c.mc, va:'t', lh:1.3});
      K.list(Z.x + 26, Z.y + 250, kw - 52, Z.h - 270, r.slice(0, topN).map(function(x){ return x.l; }), {max:17, min:10, color:c.tc, dotColor:c.tc});
      acc = 0;
      var cum = r.map(function(x){ acc += x.v || 0; return tot ? Math.round(acc / tot * 1000)/10 : 0; });
      K.nativeChart(Z.x + kw + 36, Z.y, Z.w - kw - 36, Z.h, {k:'combo', dir:'col', grp:'clustered', gap:25, cats:r.map(function(x){ return x.l; }), leg:'t', lbl:false, unit:D.unit, unit2:'%', max2:100,
        ser:[{n:'Count', v:r.map(function(x){ return x.v || 0; }), pi:0, ty:'bar'}, {n:'Cumulative %', v:cum, pi:3, ty:'line', ax2:true}]});
    }}
  ]});

/* ---------------- Histogram (real chart, no gaps) ---------------- */
DG.type({id:'histogram', cat:'analytics', name:'Histogram', fields:[{k:'l', n:'Range (bin)'}, {k:'v', n:'Count', num:1}], min:3, max:20,
  sample:{rows:[{l:'0-10', v:4}, {l:'10-20', v:11}, {l:'20-30', v:23}, {l:'30-40', v:37}, {l:'40-50', v:29}, {l:'50-60', v:16}, {l:'60-70', v:7}], ax:{x:'Order value ($)'}},
  ai:'value ranges in order (bins like "0-10") with the count of items in each.',
  designs:[
    {id:'his1', name:'Histogram', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'bar', dir:'col', grp:'clustered', gap:6, cats:D.rows.map(function(r){ return r.l; }), ser:[{n:'Count', v:D.rows.map(function(r){ return r.v || 0; }), pi:0}], lbl:true, leg:'none'}); }},
    {id:'his2', name:'Histogram + insights', side:true, draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'bar', dir:'col', grp:'clustered', gap:6, cats:D.rows.map(function(r){ return r.l; }), ser:[{n:'Count', v:D.rows.map(function(r){ return r.v || 0; }), pi:0}], lbl:true, leg:'none'}); }}
  ]});

/* ---------------- Box & whisker ---------------- */
DG.type({id:'boxplot', cat:'analytics', name:'Box & Whisker Chart', fields:[{k:'l', n:'Group'}, {k:'min', n:'Min', num:1}, {k:'q1', n:'Q1', num:1}, {k:'med', n:'Median', num:1}, {k:'q3', n:'Q3', num:1}, {k:'max', n:'Max', num:1}], min:1, max:10,
  sample:{rows:[{l:'Team A', min:12, q1:24, med:31, q3:38, max:52}, {l:'Team B', min:18, q1:28, med:35, q3:44, max:61}, {l:'Team C', min:9, q1:17, med:22, q3:30, max:41}, {l:'Team D', min:21, q1:33, med:40, q3:47, max:58}], unit:'min'},
  ai:'groups with five numbers each: min, q1, med (median), q3, max.',
  designs:[
    {id:'box1', name:'Box plot · vertical', draw:function(K, Z, D){
      var lo = 1e9, hi = -1e9; D.rows.forEach(function(r){ lo = Math.min(lo, r.min); hi = Math.max(hi, r.max); });
      var T = DG.ticks(Math.min(0, lo), hi); lo = T[0]; hi = T[T.length - 1];
      var lab = 60, px = Z.x + lab, pw = Z.w - lab, py = Z.y + 10, ph = Z.h - 60, n = D.rows.length, band = pw / n, bw = Math.min(110, band*0.45);
      function Y(v){ return py + ph - (v - lo)/(hi - lo)*ph; }
      T.forEach(function(t){ K.line(px, Y(t), px + pw, Y(t), {color:K.ln, lw:1, dash:true}); K.t(Z.x, Y(t) - 10, lab - 10, 20, fmt(t, D.unit), {size:13, color:K.mc, align:'r', lines:1}); });
      D.rows.forEach(function(r, i){
        var cx = px + band*i + band/2;
        K.line(cx, Y(r.max), cx, Y(r.q3), {pi:i, lw:2}); K.line(cx, Y(r.q1), cx, Y(r.min), {pi:i, lw:2});
        K.line(cx - bw*0.3, Y(r.max), cx + bw*0.3, Y(r.max), {pi:i, lw:2.5}); K.line(cx - bw*0.3, Y(r.min), cx + bw*0.3, Y(r.min), {pi:i, lw:2.5});
        K.rect(cx - bw/2, Y(r.q3), bw, Math.max(2, Y(r.q1) - Y(r.q3)), {pi:i, soft:0.35, r:6, line:K.col(i), lw:2});
        K.line(cx - bw/2, Y(r.med), cx + bw/2, Y(r.med), {pi:i, lw:4});
        K.t(cx + bw/2 + 6, Y(r.med) - 11, 70, 22, fmt(r.med, D.unit), {size:13, wt:800, pi:i, lines:1});
        K.t(px + band*i, py + ph + 10, band, 40, r.l, {max:16, min:10, wt:700, color:K.tc, align:'c', va:'t', lines:2});
      });
    }},
    {id:'box2', name:'Box plot · horizontal ranges', draw:function(K, Z, D){
      var lo = 1e9, hi = -1e9; D.rows.forEach(function(r){ lo = Math.min(lo, r.min); hi = Math.max(hi, r.max); });
      var T = DG.ticks(lo, hi); lo = T[0]; hi = T[T.length - 1];
      var lab = 200, px = Z.x + lab, pw = Z.w - lab - 20, n = D.rows.length, rh = (Z.h - 40) / n, bh = Math.min(38, rh*0.5);
      function X(v){ return px + (v - lo)/(hi - lo)*pw; }
      T.forEach(function(t){ K.line(X(t), Z.y, X(t), Z.y + Z.h - 34, {color:K.ln, lw:1, dash:true}); K.t(X(t) - 40, Z.y + Z.h - 28, 80, 24, fmt(t, D.unit), {size:13, color:K.mc, align:'c', lines:1}); });
      D.rows.forEach(function(r, i){
        var cy = Z.y + rh*i + rh/2;
        K.t(Z.x, cy - rh/2, lab - 20, rh, r.l, {max:18, min:10, wt:700, color:K.tc, align:'r', lines:2});
        K.line(X(r.min), cy, X(r.q1), cy, {pi:i, lw:2}); K.line(X(r.q3), cy, X(r.max), cy, {pi:i, lw:2});
        K.line(X(r.min), cy - bh*0.35, X(r.min), cy + bh*0.35, {pi:i, lw:2.5}); K.line(X(r.max), cy - bh*0.35, X(r.max), cy + bh*0.35, {pi:i, lw:2.5});
        K.rect(X(r.q1), cy - bh/2, Math.max(2, X(r.q3) - X(r.q1)), bh, {pi:i, soft:0.35, r:6, line:K.col(i), lw:2});
        K.line(X(r.med), cy - bh/2, X(r.med), cy + bh/2, {pi:i, lw:4});
      });
    }}
  ]});

/* ---------------- Treemap ---------------- */
function squarify(items, x, y, w, h){
  var tot = items.reduce(function(a, b){ return a + b.v; }, 0) || 1, out = [], rest = items.slice().sort(function(a, b){ return b.v - a.v; });
  var scale = w*h / tot;
  function worst(row, side){ var s = row.reduce(function(a, b){ return a + b.v*scale; }, 0), mx = 0, mn = 1e18; row.forEach(function(r){ mx = Math.max(mx, r.v*scale); mn = Math.min(mn, r.v*scale); }); return Math.max(side*side*mx/(s*s), s*s/(side*side*mn)); }
  while (rest.length) {
    var side = Math.min(w, h), row = [rest[0]], i = 1;
    while (i < rest.length && worst(row.concat([rest[i]]), side) <= worst(row, side)) { row.push(rest[i]); i++; }
    rest = rest.slice(i);
    var s = row.reduce(function(a, b){ return a + b.v*scale; }, 0);
    if (w >= h) { var cw = s / h, yy = y; row.forEach(function(r){ var rh = r.v*scale / cw; out.push({it:r, x:x, y:yy, w:cw, h:rh}); yy += rh; }); x += cw; w -= cw; }
    else { var chh = s / w, xx = x; row.forEach(function(r){ var rw = r.v*scale / chh; out.push({it:r, x:xx, y:y, w:rw, h:chh}); xx += rw; }); y += chh; h -= chh; }
  }
  return out;
}
DG.squarify = squarify;
var TR = [{k:'l', n:'Item'}, {k:'v', n:'Value', num:1}, {k:'p', n:'Group (optional)'}];
DG.type({id:'treemap', cat:'analytics', name:'Treemap', fields:TR, min:3, max:24,
  sample:{rows:[{l:'Cloud', v:38, p:'Software'}, {l:'Licences', v:21, p:'Software'}, {l:'Support', v:9, p:'Software'}, {l:'Consulting', v:14, p:'Services'}, {l:'Training', v:6, p:'Services'}, {l:'Devices', v:8, p:'Hardware'}, {l:'Accessories', v:4, p:'Hardware'}], unit:'$M'},
  ai:'items with a size value; p = optional group name shared by related items.',
  designs:[
    {id:'tm1', name:'Treemap · blocks', draw:function(K, Z, D){
      var items = D.rows.filter(function(r){ return (r.v || 0) > 0; }).map(function(r){ return {l:r.l, v:r.v, g:r.p}; }), tot = 0; items.forEach(function(r){ tot += r.v; });
      var G = []; items.forEach(function(r){ if (r.g && G.indexOf(r.g) < 0) G.push(r.g); });
      squarify(items, Z.x, Z.y, Z.w, Z.h).forEach(function(b, i){
        var pi = b.it.g ? G.indexOf(b.it.g) : i, g = 3;
        K.rect(b.x + g, b.y + g, b.w - 2*g, b.h - 2*g, {pi:pi, r:10, soft:b.it.g ? (0.55 + 0.45*(b.it.v / (tot || 1))*2 > 1 ? 1 : 0.55 + 0.9*(b.it.v / (tot || 1))) : null});
        var big = Math.min(b.w, b.h);
        if (b.w > 70 && b.h > 44) {
          K.tOn(b.x + 16, b.y + 12, b.w - 32, Math.min(34, b.h*0.4), b.it.l, pi, {max:Math.min(22, big*0.2), min:9, wt:800, lines:1, font:'head'}, b.it.g ? 0.8 : null);
          K.tOn(b.x + 16, b.y + 12 + Math.min(34, b.h*0.4), b.w - 32, Math.min(40, b.h*0.45), fmt(b.it.v, D.unit) + ' · ' + Math.round(b.it.v / (tot || 1) * 100) + '%', pi, {max:Math.min(18, big*0.16), min:8, lines:1}, b.it.g ? 0.8 : null);
        }
      });
    }},
    {id:'tm2', name:'Treemap · grouped', draw:function(K, Z, D){
      var items = D.rows.filter(function(r){ return (r.v || 0) > 0; }), G = {}, order = [];
      items.forEach(function(r){ var g = r.p || 'Other'; if (!G[g]) { G[g] = {l:g, v:0, kids:[]}; order.push(g); } G[g].v += r.v; G[g].kids.push({l:r.l, v:r.v}); });
      squarify(order.map(function(g){ return G[g]; }), Z.x, Z.y, Z.w, Z.h).forEach(function(b){
        var pi = order.indexOf(b.it.l), hh = 34;
        K.rect(b.x + 3, b.y + 3, b.w - 6, b.h - 6, {pi:pi, r:12});
        K.tOn(b.x + 16, b.y + 6, b.w - 32, hh, b.it.l.toUpperCase() + '  ' + fmt(b.it.v, D.unit), pi, {max:15, min:9, wt:800, ls:0.06, lines:1});
        squarify(b.it.kids, b.x + 10, b.y + hh + 6, b.w - 20, b.h - hh - 16).forEach(function(k){
          K.rect(k.x + 2, k.y + 2, k.w - 4, k.h - 4, {fill:K.bg, op:0.2, r:8});
          if (k.w > 60 && k.h > 36) K.tOn(k.x + 10, k.y + 6, k.w - 20, k.h - 12, k.it.l + '\n' + fmt(k.it.v, D.unit), pi, {max:17, min:8, wt:700, va:'t', lh:1.25});
        });
      });
    }}
  ]});

/* ---------------- Sunburst ---------------- */
DG.type({id:'sunburst', cat:'analytics', name:'Sunburst Chart', fields:TR, min:3, max:24,
  sample:{rows:[{l:'Online', v:0}, {l:'Stores', v:0}, {l:'Partners', v:0}, {l:'Web', v:34, p:'Online'}, {l:'App', v:22, p:'Online'}, {l:'Flagship', v:18, p:'Stores'}, {l:'Outlets', v:9, p:'Stores'}, {l:'Resellers', v:11, p:'Partners'}, {l:'Marketplaces', v:6, p:'Partners'}], unit:'%'},
  ai:'parent groups (rows without p) and their children (p = parent name) with values on the children.',
  designs:[
    {id:'sb1', name:'Sunburst + legend', draw:function(K, Z, D){
      var par = [], kids = {};
      D.rows.forEach(function(r){ if (r.p) { (kids[r.p] = kids[r.p] || []).push(r); if (par.indexOf(r.p) < 0) par.push(r.p); } });
      D.rows.forEach(function(r){ if (!r.p && par.indexOf(r.l) < 0 && (r.v || 0) > 0) { par.push(r.l); kids[r.l] = [{l:r.l, v:r.v, self:1}]; } });
      var tot = 0; par.forEach(function(p){ kids[p].forEach(function(k){ tot += k.v || 0; }); }); tot = tot || 1;
      var R = Math.min(Z.h/2, Z.w*0.28) - 4, cx = Z.x + R + 10, cy = Z.y + Z.h/2, a = 0;
      par.forEach(function(p, pi){
        var ps = 0; kids[p].forEach(function(k){ ps += k.v || 0; }); var sw = ps / tot * 360;
        K.sector(cx, cy, R*0.3, R*0.64, a, a + sw, {pi:pi, stroke:K.bg, lw:3});
        if (sw > 22) { var q = DG.pol(cx, cy, R*0.47, a + sw/2); K.tOn(q[0] - 46, q[1] - 12, 92, 24, p, pi, {max:14, min:8, wt:800, align:'c', lines:1}); }
        var b = a;
        kids[p].forEach(function(k, ki){
          var s2 = (k.v || 0) / tot * 360; if (k.self) { b += s2; return; }
          K.sector(cx, cy, R*0.66, R, b, b + s2, {pi:pi, soft:0.5 + 0.4*((ki % 2) ? 0.3 : 0.7), stroke:K.bg, lw:3});
          if (s2 > 16) { var q2 = DG.pol(cx, cy, R*0.83, b + s2/2); K.t(q2[0] - 40, q2[1] - 10, 80, 20, Math.round((k.v || 0)/tot*100) + '%', {size:12, wt:800, color:u.on(K.soft(pi, 0.5)), align:'c', lines:1}); }
          b += s2;
        });
        a += sw;
      });
      K.t(cx - R*0.28, cy - 16, R*0.56, 32, fmt(tot === 1 ? 0 : tot, D.unit), {max:20, min:10, wt:900, font:'head', color:K.tc, align:'c', lines:1});
      var lx = cx + R + 60, lw = Z.x + Z.w - lx, gh = Z.h / Math.max(1, par.length);
      par.forEach(function(p, pi){
        var y = Z.y + gh*pi, ps = 0; kids[p].forEach(function(k){ ps += k.v || 0; });
        K.rect(lx, y + 10, 6, gh - 20, {pi:pi, r:3});
        K.t(lx + 22, y + 6, lw - 120, 30, p, {max:20, min:11, font:'head', wt:800, color:K.tc, lines:1});
        K.t(lx + lw - 100, y + 6, 100, 30, fmt(ps, D.unit), {max:20, min:11, wt:800, pi:pi, align:'r', lines:1});
        K.t(lx + 22, y + 38, lw - 22, gh - 46, kids[p].filter(function(k){ return !k.self; }).map(function(k){ return k.l + ' ' + fmt(k.v, D.unit); }).join('  ·  '), {max:15, min:9, color:K.mc, va:'t', lh:1.3});
      });
    }}
  ]});

/* ---------------- Heatmap ---------------- */
DG.type({id:'heatmap', cat:'analytics', name:'Heatmap', fields:[{k:'l', n:'Row'}], series:true, min:2, max:12,
  sample:{cols:['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], rows:[{l:'Morning', v:[42, 55, 61, 58, 49, 22, 18]}, {l:'Midday', v:[71, 78, 83, 80, 74, 51, 40]}, {l:'Afternoon', v:[64, 69, 72, 75, 66, 58, 47]}, {l:'Evening', v:[38, 41, 45, 52, 60, 77, 69]}], unit:''},
  ai:'a grid: rows and columns (cols = column names) with a number in every cell.',
  designs:[
    {id:'hm1', name:'Heatmap · grid', draw:function(K, Z, D){
      var lo = 1e9, hi = -1e9; D.rows.forEach(function(r){ r.v.forEach(function(v){ lo = Math.min(lo, v); hi = Math.max(hi, v); }); });
      var lab = Math.min(200, Z.w*0.18), top = 40, nc = D.cols.length, nr = D.rows.length, cw = (Z.w - lab) / nc, ch = (Z.h - top - 30) / nr;
      D.cols.forEach(function(c, j){ K.t(Z.x + lab + cw*j, Z.y, cw, top - 6, c, {max:15, min:9, wt:700, color:K.mc, align:'c', lines:1}); });
      D.rows.forEach(function(r, i){
        K.t(Z.x, Z.y + top + ch*i, lab - 14, ch, r.l, {max:17, min:9, wt:700, color:K.tc, align:'r', lines:2});
        r.v.forEach(function(v, j){ var f = hi > lo ? (v - lo)/(hi - lo) : 0.5, soft = 0.12 + f*0.88;
          K.rect(Z.x + lab + cw*j + 3, Z.y + top + ch*i + 3, cw - 6, ch - 6, {pi:0, soft:soft, r:8});
          K.tOn(Z.x + lab + cw*j, Z.y + top + ch*i, cw, ch, fmt(v, D.unit), 0, {max:Math.min(18, ch*0.36), min:8, wt:700, align:'c', lines:1}, soft); });
      });
      var gw = Math.min(300, Z.w*0.3), gx = Z.x + Z.w - gw;
      for (var k = 0; k < 10; k++) K.rect(gx + gw*k/10, Z.y + Z.h - 16, gw/10, 12, {pi:0, soft:0.12 + k*0.098});
      K.t(gx - 80, Z.y + Z.h - 22, 74, 22, fmt(lo, D.unit), {size:12, color:K.mc, align:'r', lines:1}); K.t(gx - 150, Z.y + Z.h - 22, 60, 22, '', {size:12});
      K.t(gx + gw + 6, Z.y + Z.h - 22, 80, 22, fmt(hi, D.unit), {size:12, color:K.mc, lines:1});
    }}
  ]});

/* ---------------- Cohort ---------------- */
DG.type({id:'cohort', cat:'analytics', name:'Cohort Chart', fields:[{k:'l', n:'Cohort'}], series:true, min:3, max:10,
  sample:{cols:['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'], rows:[{l:'Jan', v:[100, 72, 61, 55, 51, 48]}, {l:'Feb', v:[100, 75, 64, 58, 53]}, {l:'Mar', v:[100, 78, 68, 60]}, {l:'Apr', v:[100, 80, 70]}, {l:'May', v:[100, 83]}, {l:'Jun', v:[100]}], unit:'%'},
  ai:'cohorts (start months) with retention % per period after start; later cohorts have fewer periods (triangle).',
  designs:[
    {id:'co1', name:'Cohort · retention triangle', draw:function(K, Z, D){
      var raw = {}; D.rows.forEach(function(r, i){ raw[i] = r; });
      var lab = 120, top = 40, nc = D.cols.length, nr = D.rows.length, cw = (Z.w - lab) / nc, ch = (Z.h - top) / nr;
      D.cols.forEach(function(c, j){ K.t(Z.x + lab + cw*j, Z.y, cw, top - 6, c, {max:15, min:9, wt:700, color:K.mc, align:'c', lines:1}); });
      D.rows.forEach(function(r, i){
        K.t(Z.x, Z.y + top + ch*i, lab - 14, ch, r.l, {max:17, min:9, wt:700, color:K.tc, align:'r', lines:1});
        var cnt = Math.max(1, nc - i);
        for (var j = 0; j < cnt && j < r.v.length; j++) {
          var v = r.v[j], soft = 0.1 + Math.max(0, Math.min(1, v/100))*0.9;
          K.rect(Z.x + lab + cw*j + 2, Z.y + top + ch*i + 2, cw - 4, ch - 4, {pi:0, soft:soft, r:6});
          K.tOn(Z.x + lab + cw*j, Z.y + top + ch*i, cw, ch, fmt(v, D.unit), 0, {max:Math.min(17, ch*0.4), min:8, wt:700, align:'c', lines:1}, soft);
        }
      });
    }}
  ]});
})();

/* DS Diagrams — KPI & dashboards: KPI tiles, gauges, growth, market share, pipeline, funnel, target vs actual */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('kpi', 'KPI & Dashboards', '📈', 'KPI tiles, gauges, growth, market share, pipeline, funnel');
var fmt = DG.fmt, u = DG.u;
var KF = [{k:'l', n:'Metric'}, {k:'v', n:'Value', num:1}, {k:'u', n:'Unit ($, %, K…)'}, {k:'t', n:'Target', num:1}, {k:'d', n:'Change (e.g. +12%)'}];
function grid(n, w, h){ var best = null; for (var c = 1; c <= n; c++) { var r = Math.ceil(n / c), cw = w / c, ch = h / r, sc = Math.min(cw / 1.5, ch) - (r*c - n)*4; if (!best || sc > best.sc) best = {c:c, r:r, sc:sc}; } return best; }
DG.grid = grid;
function val(r){ return fmt(r.v, r.u || ''); }
function up(d){ return !/^\s*-/.test(String(d || '')) && !/down|decline|drop/i.test(String(d || '')); }

DG.type({id:'kpi', cat:'kpi', name:'KPI Dashboard', fields:KF, min:1, max:9,
  sample:{rows:[{l:'Annual revenue', v:48200000, u:'$', t:50000000, d:'+18% YoY'}, {l:'Active customers', v:12480, u:'', t:12000, d:'+9% YoY'}, {l:'Net promoter score', v:62, u:'', t:60, d:'+5 pts'}, {l:'Gross margin', v:71, u:'%', t:70, d:'+2 pts'}, {l:'Churn rate', v:3.1, u:'%', t:3, d:'-0.4 pts'}, {l:'Avg. deal size', v:38500, u:'$', t:40000, d:'+6%'}]},
  ai:'2-8 key metrics: l name, v number, u unit ($ % K or empty), t target number, d change text like "+12% YoY".',
  designs:[
    {id:'kpi1', name:'KPI tiles', draw:function(K, Z, D){
      var cl = K.cells(D.rows.length, Z.x, Z.y, Z.w, Z.h, 18);
      D.rows.forEach(function(r, i){
        var x = cl[i].x, y = cl[i].y, cw = cl[i].w, ch = cl[i].h, c = K.card(x, y, cw, ch, {});
        K.rect(x, y, 6, ch, {pi:i, r:3});
        K.t(x + 28, y + 16, cw - 56, ch*0.2, r.l.toUpperCase(), {max:15, min:9, wt:800, color:c.mc, ls:0.06, lines:1});
        K.t(x + 28, y + ch*0.26, cw - 56, ch*0.4, val(r), {max:Math.min(64, ch*0.36), min:18, font:'head', wt:900, color:c.tc, lines:1});
        if (r.d) { var ok = up(r.d), sc = ok ? K.good() : K.bad(); K.rect(x + 28, y + ch*0.7, Math.min(cw - 56, String(r.d).length*9 + 34), 30, {fix:sc, soft:0.16, r:15}); K.t(x + 40, y + ch*0.7, cw - 80, 30, (ok ? '▲ ' : '▼ ') + r.d, {max:14, min:9, wt:800, fix:sc, lines:1}); }
      });
    }},
    {id:'kpi2', name:'KPI tiles + target bars', draw:function(K, Z, D){
      var cl = K.cells(D.rows.length, Z.x, Z.y, Z.w, Z.h, 18);
      D.rows.forEach(function(r, i){
        var x = cl[i].x, y = cl[i].y, cw = cl[i].w, ch = cl[i].h, c = K.card(x, y, cw, ch, {});
        K.t(x + 24, y + 14, cw - 48, ch*0.2, r.l, {max:17, min:10, wt:700, color:c.mc, lines:1});
        K.t(x + 24, y + ch*0.24, cw*0.6, ch*0.38, val(r), {max:Math.min(56, ch*0.34), min:16, font:'head', wt:900, color:c.tc, lines:1});
        if (r.d) K.t(x + cw*0.55, y + ch*0.24, cw*0.45 - 24, ch*0.38, r.d, {max:16, min:9, wt:800, fix:up(r.d) ? K.good() : K.bad(), align:'r', lines:2});
        if (r.t) { var f = Math.max(0, Math.min(1.15, (r.v || 0) / r.t)), bw = cw - 48, by = y + ch*0.72;
          K.rect(x + 24, by, bw, 10, {fill:K.ln, op:0.6, r:5}); K.rect(x + 24, by, bw*Math.min(1, f), 10, {pi:i, r:5});
          K.t(x + 24, by + 14, bw, ch*0.2, Math.round(f*100) + '% of target ' + fmt(r.t, r.u || ''), {max:13, min:8, color:c.mc, lines:1}); }
      });
    }},
    {id:'kpi3', name:'Hero metric + tiles', draw:function(K, Z, D){
      var h = D.rows[0], rest = D.rows.slice(1), hw = Math.round(Z.w*0.36), c = K.card(Z.x, Z.y, hw, Z.h, {hl:true});
      K.t(Z.x + 30, Z.y + 30, hw - 60, 30, h.l.toUpperCase(), {max:16, min:10, wt:800, color:c.mc, ls:0.08, lines:2});
      K.t(Z.x + 30, Z.y + Z.h*0.25, hw - 60, Z.h*0.3, val(h), {max:86, min:26, font:'head', wt:900, color:c.tc, lines:1});
      if (h.d) K.t(Z.x + 30, Z.y + Z.h*0.56, hw - 60, 36, (up(h.d) ? '▲ ' : '▼ ') + h.d, {max:22, min:12, wt:800, color:c.tc, lines:1});
      if (h.t) { var f = Math.min(1, (h.v || 0)/h.t); K.rect(Z.x + 30, Z.y + Z.h - 70, hw - 60, 12, {fill:c.tc, op:0.2, r:6}); K.rect(Z.x + 30, Z.y + Z.h - 70, (hw - 60)*f, 12, {fill:c.tc, r:6}); K.t(Z.x + 30, Z.y + Z.h - 50, hw - 60, 26, Math.round((h.v || 0)/h.t*100) + '% of target', {size:14, color:c.mc, lines:1}); }
      if (!rest.length) return;
      var cl = K.cells(rest.length, Z.x + hw + 24, Z.y, Z.w - hw - 24, Z.h, 16, 1.4);
      rest.forEach(function(r, i){
        var x = cl[i].x, y = cl[i].y, cw = cl[i].w, ch = cl[i].h, cc = K.card(x, y, cw, ch, {});
        K.t(x + 20, y + 12, cw - 40, ch*0.26, r.l, {max:15, min:9, wt:700, color:cc.mc, lines:2});
        K.t(x + 20, y + ch*0.36, cw - 40, ch*0.36, val(r), {max:Math.min(44, ch*0.32), min:14, font:'head', wt:900, color:cc.tc, lines:1});
        if (r.d) K.t(x + 20, y + ch*0.74, cw - 40, ch*0.2, (up(r.d) ? '▲ ' : '▼ ') + r.d, {max:13, min:8, wt:800, fix:up(r.d) ? K.good() : K.bad(), lines:1});
      });
    }},
    {id:'kpi4', name:'Scorecard table', draw:function(K, Z, D){
      var n = D.rows.length, hh = 44, rh = Math.min(84, (Z.h - hh) / n), cols = [0, 0.4, 0.58, 0.76, 0.9], W0 = Z.w;
      K.rect(Z.x, Z.y, W0, hh, {pi:0, r:10});
      ['Metric', 'Actual', 'Target', 'Change', 'Status'].forEach(function(h, j){ K.tOn(Z.x + 24 + cols[j]*W0, Z.y, (j < 4 ? (cols[j+1] - cols[j]) : 0.1)*W0 - 24, hh, h.toUpperCase(), 0, {size:13, wt:800, ls:0.08, lines:1, align:j ? 'l' : 'l'}); });
      D.rows.forEach(function(r, i){
        var y = Z.y + hh + i*rh; if (i % 2) K.rect(Z.x, y, W0, rh, {fill:K.cardc, r:6});
        var ok = r.t ? (r.v || 0) >= r.t * 0.98 : up(r.d), warn = r.t && !ok && (r.v || 0) >= r.t*0.9;
        K.t(Z.x + 24, y, cols[1]*W0 - 40, rh, r.l, {max:18, min:10, wt:700, color:K.tc, lines:2});
        K.t(Z.x + 24 + cols[1]*W0, y, 0.17*W0, rh, val(r), {max:20, min:10, font:'head', wt:800, color:K.tc, lines:1});
        K.t(Z.x + 24 + cols[2]*W0, y, 0.17*W0, rh, r.t ? fmt(r.t, r.u || '') : '—', {max:17, min:10, color:K.mc, lines:1});
        K.t(Z.x + 24 + cols[3]*W0, y, 0.13*W0, rh, r.d || '—', {max:16, min:9, wt:700, fix:up(r.d) ? K.good() : K.bad(), lines:1});
        var st = ok ? 'On track' : (warn ? 'At risk' : 'Behind'), sc = ok ? K.good() : (warn ? K.warn() : K.bad());
        K.rect(Z.x + cols[4]*W0 + 8, y + rh/2 - 15, 0.1*W0 - 8, 30, {fix:sc, soft:0.18, r:15});
        K.t(Z.x + cols[4]*W0 + 8, y + rh/2 - 15, 0.1*W0 - 8, 30, st, {max:13, min:8, wt:800, fix:sc, align:'c', lines:1});
      });
    }}
  ]});

/* ---------------- Gauges ---------------- */
/* text for the strip under the gauges when the slide has no key points of its own */
function gaugeSummary(D){
  var rows = D.rows.filter(function(r){ return r.t; }), hit = rows.filter(function(r){ return (r.v || 0) >= r.t; });
  if (!rows.length) return ['Summary: ' + D.rows.map(function(r){ return r.l + ' ' + fmt(r.v, r.u || ''); }).join(' · ') + '.'];
  var close = rows.filter(function(r){ return (r.v || 0) < r.t && (r.v || 0) >= r.t*0.9; }), low = rows.filter(function(r){ return (r.v || 0) < r.t*0.9; });
  var one = function(r){ return r.l + ' (' + fmt(r.v, r.u || '') + ' vs ' + fmt(r.t, r.u || '') + ')'; };
  var t = hit.length + ' of ' + rows.length + ' metrics reached their target' + (hit.length ? ': ' + hit.map(one).join(', ') : '') + '.';
  if (close.length) t += ' Close to target: ' + close.map(one).join(', ') + '.';
  if (low.length) t += ' Needs attention: ' + low.map(one).join(', ') + '.';
  return [(hit.length === rows.length ? 'All targets met' : hit.length ? 'Performance summary' : 'Below target') + ': ' + t];
}
DG.type({id:'gauge', cat:'kpi', name:'Gauge Chart', fields:[{k:'l', n:'Metric'}, {k:'v', n:'Value', num:1}, {k:'t', n:'Maximum', num:1}, {k:'u', n:'Unit'}], min:1, max:6,
  sample:{rows:[{l:'Customer satisfaction', v:86, t:100, u:'%'}, {l:'On-time delivery', v:93, t:100, u:'%'}, {l:'Capacity used', v:68, t:100, u:'%'}]},
  ai:'1-4 metrics with a value and a maximum (e.g. 100 for percentages).',
  designs:[
    {id:'ga1', name:'Semicircle gauges', strip:gaugeSummary, draw:function(K, Z, D){
      var n = D.rows.length, gap = 24, cw = (Z.w - gap*(n - 1)) / n;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(cw + gap), c = K.card(x, Z.y, cw, Z.h, {}), R = Math.min(cw*0.4, (Z.h - 110)*0.62, 150), cx = x + cw/2;
        var cy = Z.y + Math.max(18, (Z.h - R - 100) / 2) + R;              /* gauge + labels centred in the card */
        var f = Math.max(0, Math.min(1, (r.v || 0) / (r.t || 100)));
        K.sector(cx, cy, R*0.74, R, 270, 450, {fill:K.ln, op:0.55});
        if (f > 0) K.sector(cx, cy, R*0.74, R, 270, 270 + 180*f, {pi:i});
        var p = DG.pol(cx, cy, R*1.02, 270 + 180*f); K.circ(p[0], p[1], 7, {pi:i, line:K.bg, lw:3});
        K.t(cx - R*0.7, cy - R*0.5, R*1.4, R*0.46, fmt(r.v, r.u || ''), {max:R*0.36, min:14, font:'head', wt:900, color:c.tc, align:'c', va:'b', lines:1});
        K.t(cx - R, cy + 6, R*0.5, 22, fmt(0, r.u || ''), {size:12, color:c.mc, align:'c', lines:1}); K.t(cx + R*0.5, cy + 6, R*0.5, 22, fmt(r.t || 100, r.u || ''), {size:12, color:c.mc, align:'c', lines:1});
        K.t(x + 16, cy + 36, cw - 32, Math.min(64, Z.y + Z.h - cy - 44), r.l, {max:20, min:11, font:'head', wt:700, color:c.tc, align:'c', va:'t', lines:2});
      });
    }},
    {id:'ga2', name:'Ring gauges', strip:gaugeSummary, draw:function(K, Z, D){
      var n = D.rows.length, gap = 24, cw = (Z.w - gap*(n - 1)) / n, R0 = Math.min(cw*0.36, (Z.h - 110)*0.5, 130), top = Z.y + Math.max(6, (Z.h - 2*R0 - 106) / 2);
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(cw + gap), R = R0, cx = x + cw/2, cy = top + R, f = Math.max(0, Math.min(1, (r.v || 0) / (r.t || 100)));
        K.circ(cx, cy, R, {fill:K.ln, op:0.45}); K.circ(cx, cy, R*0.8, {fill:K.bg});
        if (f > 0) K.sector(cx, cy, R*0.8, R, 0, 360*f, {pi:i});
        K.t(cx - R*0.7, cy - R*0.3, R*1.4, R*0.6, fmt(r.v, r.u || ''), {max:R*0.42, min:14, font:'head', wt:900, color:K.tc, align:'c', lines:1});
        K.t(x + 10, cy + R + 22, cw - 20, 40, r.l, {max:20, min:11, font:'head', wt:800, color:K.tc, align:'c', va:'t', lines:2});
        K.t(x + 10, cy + R + 66, cw - 20, 30, 'of ' + fmt(r.t || 100, r.u || ''), {max:15, min:10, color:K.mc, align:'c', va:'t', lines:1});
      });
    }}
  ]});

/* ---------------- Growth ---------------- */
var LVD = [{k:'l', n:'Period'}, {k:'v', n:'Value', num:1}, {k:'d', n:'Note'}];
DG.type({id:'growth', cat:'kpi', name:'Growth Chart', fields:LVD, min:3, max:8,
  sample:{rows:[{l:'2022', v:4.2, d:'Launch in UK'}, {l:'2023', v:7.9, d:'First enterprise wins'}, {l:'2024', v:13.5, d:'Expansion to EU'}, {l:'2025', v:21.8, d:'AI product line'}, {l:'2026', v:34, d:'Target: global scale'}], unit:'$M'},
  ai:'3-6 periods in order with a growing value and a short note (max 6 words).',
  designs:[
    {id:'gr1', name:'Growth staircase', draw:function(K, Z, D){
      var n = D.rows.length, mx = Math.max.apply(null, D.rows.map(function(r){ return r.v || 0; }).concat([1])), gap = 14, bw = (Z.w - gap*(n - 1)) / n, top = 70;
      D.rows.forEach(function(r, i){
        var h = Math.max(60, (Z.h - top - 40) * (0.25 + 0.75*(r.v || 0)/mx)), x = Z.x + i*(bw + gap), y = Z.y + Z.h - h;
        K.rect(x, y, bw, h, {pi:0, soft:0.35 + 0.65*(i + 1)/n, r:12});
        K.tOn(x + 16, y + 14, bw - 32, 40, K.fmt(r.v), 0, {max:30, min:12, font:'head', wt:900, lines:1}, 0.35 + 0.65*(i + 1)/n);
        K.tOn(x + 16, y + 54, bw - 32, 26, r.l, 0, {max:16, min:9, wt:700, lines:1}, 0.35 + 0.65*(i + 1)/n);
        if (r.d && h > 130) K.tOn(x + 16, y + 86, bw - 32, h - 100, r.d, 0, {max:15, min:9, va:'t', lh:1.3}, 0.35 + 0.65*(i + 1)/n);
      });
      var a = D.rows[0].v || 0, b = D.rows[n - 1].v || 0, cg = a ? Math.round((Math.pow(b/a, 1/Math.max(1, n - 1)) - 1)*100) : 0;
      K.rect(Z.x, Z.y, 330, 52, {pi:0, soft:0.16, r:26});
      K.t(Z.x + 24, Z.y, 290, 52, '▲ ' + (cg >= 0 ? '+' : '') + cg + '% average growth per period', {max:17, min:10, wt:800, pi:0, lines:1});
    }},
    {id:'gr2', name:'Growth curve + milestones', draw:function(K, Z, D){
      var n = D.rows.length, mx = Math.max.apply(null, D.rows.map(function(r){ return r.v || 0; }).concat([1])), py = Z.y + 20, ph = Z.h - 120;
      var pts = D.rows.map(function(r, i){ return [Z.x + 40 + (Z.w - 80)*i/(n - 1 || 1), py + ph - (r.v || 0)/mx*ph*0.9]; });
      K.curve(pts, {pi:0, op:0.12, close:[[pts[0][0], py + ph], [pts[n - 1][0], py + ph]]});
      K.curve(pts, {spi:0, lw:4});
      pts.forEach(function(p, i){
        var r = D.rows[i];
        K.line(p[0], p[1], p[0], py + ph, {color:K.ln, lw:1, dash:true});
        K.circ(p[0], p[1], 11, {pi:0, line:K.bg, lw:4});
        K.t(p[0] - 80, p[1] - 44, 160, 30, K.fmt(r.v), {max:22, min:11, font:'head', wt:900, color:K.tc, align:'c', lines:1});
        K.t(p[0] - 90, py + ph + 12, 180, 26, r.l, {max:18, min:10, wt:800, pi:0, align:'c', lines:1});
        K.t(p[0] - 95, py + ph + 42, 190, 56, r.d, {max:15, min:9, color:K.mc, align:'c', va:'t', lines:2});
      });
    }}
  ]});

/* ---------------- Market share ---------------- */
DG.type({id:'share', cat:'kpi', name:'Market Share Chart', fields:[{k:'l', n:'Company'}, {k:'v', n:'Share %', num:1}], min:2, max:8,
  sample:{rows:[{l:'Our company', v:34}, {l:'Competitor A', v:26}, {l:'Competitor B', v:18}, {l:'Competitor C', v:12}, {l:'Others', v:10}], unit:'%'},
  ai:'2-6 companies with market share % adding to 100; put the presenting company first.',
  designs:[
    {id:'ms1', name:'Share bar + leaders', draw:function(K, Z, D){
      var tot = 0; D.rows.forEach(function(r){ tot += r.v || 0; }); tot = tot || 1;
      var x = Z.x, bh = 70, y = Z.y + 40;
      D.rows.forEach(function(r, i){ var w = (r.v || 0)/tot*Z.w; K.rect(x + 2, y, Math.max(2, w - 4), bh, {pi:i, r:8}); if (w > 60) K.tOn(x, y, w, bh, Math.round((r.v || 0)/tot*100) + '%', i, {max:26, min:10, wt:900, align:'c', font:'head', lines:1}); x += w; });
      var n = D.rows.length, gap = 16, cw = (Z.w - gap*(n - 1)) / n, cy = y + bh + 40;
      D.rows.forEach(function(r, i){
        var cx = Z.x + i*(cw + gap), c = K.card(cx, cy, cw, Z.y + Z.h - cy, {hl:i === 0});
        K.rect(cx + 20, cy + 22, 34, 8, {pi:i, r:4});
        K.t(cx + 20, cy + 42, cw - 40, 70, fmt(r.v, '%'), {max:48, min:16, font:'head', wt:900, color:c.tc, lines:1});
        K.t(cx + 20, cy + 116, cw - 40, 60, r.l, {max:18, min:10, wt:700, color:c.mc, va:'t', lines:2});
      });
    }},
    {id:'ms2', name:'Share bubbles', draw:function(K, Z, D){
      var tot = 0, mx = 0; D.rows.forEach(function(r){ tot += r.v || 0; mx = Math.max(mx, r.v || 0); });
      var n = D.rows.length, Rm = Math.min(Z.h*0.4, Z.w / n * 0.62), x = Z.x, sum = 0;
      var rs = D.rows.map(function(r){ var rr = Math.max(26, Rm*Math.sqrt((r.v || 0)/(mx || 1))); sum += rr*2; return rr; });
      var gap = Math.max(10, (Z.w - sum) / Math.max(1, n - 1)), cy = Z.y + Z.h*0.45;
      D.rows.forEach(function(r, i){
        var R = rs[i], cx = x + R;
        K.circ(cx, cy, R, {pi:i, op:i ? 0.85 : 1});
        K.tOn(cx - R, cy - R*0.3, 2*R, R*0.6, fmt(r.v, '%'), i, {max:R*0.5, min:10, font:'head', wt:900, align:'c', lines:1});
        K.t(cx - Math.max(R, 80), cy + Rm + 20, 2*Math.max(R, 80), 50, r.l, {max:18, min:10, wt:700, color:K.tc, align:'c', va:'t', lines:2});
        x += 2*R + gap;
      });
    }}
  ]});

/* ---------------- Sales pipeline ---------------- */
var PL = [{k:'l', n:'Stage'}, {k:'v', n:'Deals / value', num:1}, {k:'d', n:'Note'}];
DG.type({id:'pipeline', cat:'kpi', name:'Sales Pipeline Chart', fields:PL, min:3, max:7,
  sample:{rows:[{l:'Prospects', v:420, d:'Inbound + outbound'}, {l:'Qualified', v:186, d:'BANT confirmed'}, {l:'Proposal', v:74, d:'Pricing sent'}, {l:'Negotiation', v:31, d:'Legal review'}, {l:'Closed won', v:18, d:'$2.4M booked'}]},
  ai:'3-6 pipeline stages in order with the number of deals and a short note.',
  designs:[
    {id:'pl1', name:'Pipeline · chevron stages', draw:function(K, Z, D){
      var n = D.rows.length, ov = 26, w = (Z.w + ov*(n - 1)) / n, h = Math.min(150, Z.h*0.36), y = Z.y + 20;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w - ov);
        K.shape(i ? 'chevron' : 'homePlate', x, y, w, h, {pi:0, soft:0.4 + 0.6*(i + 1)/n});
        K.tOn(x + (i ? ov + 12 : 22), y + 14, w - ov - 44, h*0.5, K.fmt(r.v), 0, {max:40, min:14, font:'head', wt:900, lines:1}, 0.4 + 0.6*(i + 1)/n);
        K.tOn(x + (i ? ov + 12 : 22), y + h*0.55, w - ov - 44, h*0.35, r.l, 0, {max:17, min:9, wt:700, lines:1}, 0.4 + 0.6*(i + 1)/n);
        var cx = x + 8, cw = w - ov - 8, c = K.card(cx, y + h + 30, cw, Z.y + Z.h - (y + h + 30), {});
        if (i) { var conv = D.rows[i - 1].v ? Math.round((r.v || 0) / D.rows[i - 1].v * 100) : 0; K.t(cx + 16, y + h + 44, cw - 32, 40, conv + '%', {max:30, min:12, font:'head', wt:900, pi:0, lines:1}); K.t(cx + 16, y + h + 84, cw - 32, 22, 'from ' + D.rows[i - 1].l.toLowerCase(), {size:13, color:c.mc, lines:1}); }
        else { K.t(cx + 16, y + h + 44, cw - 32, 40, 'Start', {max:30, min:12, font:'head', wt:900, pi:0, lines:1}); K.t(cx + 16, y + h + 84, cw - 32, 22, 'top of pipeline', {size:13, color:c.mc, lines:1}); }
        K.t(cx + 16, y + h + 116, cw - 32, Z.y + Z.h - (y + h + 128), r.d, {max:15, min:9, color:c.tc, va:'t'});
      });
    }},
    {id:'pl2', name:'Pipeline · tapered bars', draw:function(K, Z, D){
      var n = D.rows.length, mx = Math.max.apply(null, D.rows.map(function(r){ return r.v || 0; }).concat([1])), rh = Z.h / n, lab = 220, bw0 = Z.w - lab - 250;
      D.rows.forEach(function(r, i){
        var y = Z.y + rh*i, w = Math.max(40, bw0 * (r.v || 0)/mx), bh = rh*0.7;
        K.t(Z.x, y, lab - 20, rh, r.l, {max:20, min:11, font:'head', wt:800, color:K.tc, align:'r', lines:2});
        var sf = 1 - 0.62*i/Math.max(1, n - 1);
        K.rect(Z.x + lab, y + (rh - bh)/2, w, bh, {pi:0, soft:sf, r:bh/2});
        K.tOn(Z.x + lab + 18, y + (rh - bh)/2, w - 30, bh, K.fmt(r.v), 0, {max:22, min:10, wt:900, lines:1}, sf);
        var conv = i && D.rows[i - 1].v ? Math.round((r.v || 0) / D.rows[i - 1].v * 100) + '% conversion' : '';
        K.t(Z.x + lab + w + 16, y, Z.w - lab - w - 16, rh, (conv ? conv + '\n' : '') + (r.d || ''), {max:15, min:9, color:K.mc, lh:1.3});
      });
    }}
  ]});

/* ---------------- Conversion funnel ---------------- */
DG.type({id:'funnel', cat:'kpi', name:'Conversion Funnel', fields:PL, min:3, max:7,
  sample:{rows:[{l:'Website visitors', v:120000, d:'All channels'}, {l:'Sign-ups', v:18400, d:'Free trial started'}, {l:'Activated', v:9200, d:'Used a key feature'}, {l:'Paying customers', v:2300, d:'Converted to a plan'}, {l:'Advocates', v:640, d:'Referred a friend'}]},
  ai:'3-6 funnel stages from widest to narrowest with a number each and a short note.',
  designs:[
    {id:'fu1', name:'Funnel · centred', draw:function(K, Z, D){
      var n = D.rows.length, fw = Z.w*0.56, rh = Z.h / n, top = fw, bot = fw*0.26, x0 = Z.x;
      D.rows.forEach(function(r, i){
        var w1 = top - (top - bot)*i/n, w2 = top - (top - bot)*(i + 1)/n, y = Z.y + rh*i, cx = x0 + fw/2;
        var sf = 1 - 0.62*i/Math.max(1, n - 1);
        K.poly([[cx - w1/2, y + 2], [cx + w1/2, y + 2], [cx + w2/2, y + rh - 2], [cx - w2/2, y + rh - 2]], {pi:0, soft:sf});
        K.tOn(cx - w2/2, y, w2, rh, K.fmt(r.v), 0, {max:30, min:10, font:'head', wt:900, align:'c', lines:1}, sf);
        var lx = x0 + fw + 30;
        K.line(cx + w1/2 - (w1 - w2)/4 + 6, y + rh/2, lx - 8, y + rh/2, {color:K.ln, lw:1.2, dash:true});
        K.t(lx, y + 4, Z.w - fw - 180, rh*0.5, r.l, {max:20, min:10, font:'head', wt:800, color:K.tc, va:'b', lines:1});
        K.t(lx, y + rh*0.52, Z.w - fw - 180, rh*0.44, r.d, {max:15, min:9, color:K.mc, va:'t', lines:2});
        if (i) { var cv = D.rows[i - 1].v ? ((r.v || 0) / D.rows[i - 1].v * 100) : 0; K.rect(Z.x + Z.w - 130, y + rh/2 - 18, 120, 36, {pi:0, soft:0.16, r:18}); K.t(Z.x + Z.w - 130, y + rh/2 - 18, 120, 36, '↓ ' + (cv >= 10 ? Math.round(cv) : Math.round(cv*10)/10) + '%', {max:17, min:9, wt:900, pi:0, align:'c', lines:1}); }
      });
    }},
    {id:'fu2', name:'Funnel · horizontal steps', draw:function(K, Z, D){
      var n = D.rows.length, gap = 10, w = (Z.w - gap*(n - 1)) / n, mx = D.rows[0].v || 1;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w + gap), h = Math.max(80, (Z.h - 150) * Math.sqrt((r.v || 0)/mx)), y = Z.y + (Z.h - 150 - h)/2;
        var sf = 1 - 0.62*i/Math.max(1, n - 1);
        K.rect(x, y, w, h, {pi:0, soft:sf, r:12});
        K.tOn(x, y, w, h, K.fmt(r.v), 0, {max:32, min:11, font:'head', wt:900, align:'c', lines:1}, sf);
        K.t(x, Z.y + Z.h - 140, w, 36, r.l, {max:18, min:10, font:'head', wt:800, color:K.tc, align:'c', lines:2});
        K.t(x + 6, Z.y + Z.h - 100, w - 12, 50, r.d, {max:14, min:9, color:K.mc, align:'c', va:'t', lines:2});
        if (i) { var cv = D.rows[i - 1].v ? Math.round((r.v || 0) / D.rows[i - 1].v * 100) : 0; K.t(x - gap - 30, Z.y + Z.h - 46, 60 + gap, 30, conv(cv), {size:13, wt:800, pi:0, align:'c', lines:1}); }
      });
      function conv(v){ return '→ ' + v + '%'; }
    }}
  ]});

/* ---------------- Target vs actual ---------------- */
DG.type({id:'bullet', cat:'kpi', name:'Performance Scorecard', fields:[{k:'l', n:'Measure'}, {k:'v', n:'Actual', num:1}, {k:'t', n:'Target', num:1}, {k:'u', n:'Unit'}], min:2, max:8,
  sample:{rows:[{l:'Revenue ($M)', v:48.2, t:50, u:''}, {l:'New customers', v:1320, t:1200, u:''}, {l:'Retention', v:91, t:94, u:'%'}, {l:'Employee engagement', v:78, t:75, u:'%'}, {l:'Cost per lead', v:42, t:40, u:'$'}]},
  ai:'2-7 measures with actual and target numbers.',
  designs:[
    {id:'bu1', name:'Target vs actual bars', aside:gaugeSummary, draw:function(K, Z, D){
      var n = D.rows.length, rh = Z.h / n, lab = Math.min(260, Z.w*0.3), vw = Z.w < 900 ? 120 : 150, bw = Z.w - lab - vw - 20;
      D.rows.forEach(function(r, i){
        var y = Z.y + rh*i, mx = Math.max(r.v || 0, r.t || 0) * 1.15 || 1, bh = Math.min(34, rh*0.4), cy = y + rh/2, ok = (r.v || 0) >= (r.t || 0);
        if (i % 2 === 0) K.rect(Z.x, y + 2, Z.w, rh - 4, {fill:K.cardc, r:10, op:0.6});
        K.t(Z.x + 20, y, lab - 30, rh, r.l, {max:19, min:10, font:'head', wt:800, color:K.tc, lines:2});
        K.rect(Z.x + lab, cy - bh/2, bw, bh, {fill:K.ln, op:0.45, r:bh/2});
        K.rect(Z.x + lab, cy - bh/2, bw*(r.v || 0)/mx, bh, {fix:ok ? K.good() : K.bad(), r:bh/2});
        var tx = Z.x + lab + bw*(r.t || 0)/mx; K.rect(tx - 2, cy - bh*0.9, 4, bh*1.8, {fill:K.tc, r:2});
        K.t(Z.x + lab + bw + 16, y, vw, rh*0.55, fmt(r.v, r.u || ''), {max:24, min:11, font:'head', wt:900, fix:ok ? K.good() : K.bad(), va:'b', lines:1});
        K.t(Z.x + lab + bw + 16, y + rh*0.55, vw, rh*0.4, 'target ' + fmt(r.t, r.u || ''), {max:13, min:8, color:K.mc, va:'t', lines:1});
      });
    }}
  ]});
})();

/* DS Diagrams — Timelines & roadmaps */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('timeline', 'Timelines & Roadmaps', '🗓️', 'Timelines, milestones, roadmaps, phases');
var TF = [{k:'s', n:'Date'}, {k:'l', n:'Title'}, {k:'d', n:'Description'}];
function items(D){ return D.rows.filter(function(r){ return r.l || r.s; }); }

DG.type({id:'timeline', cat:'timeline', name:'Timeline Chart', fields:TF, min:2, max:12,
  sample:{rows:[{s:'2019', l:'Company founded', d:'Two founders, one product idea'}, {s:'2021', l:'Seed round', d:'$4M raised to build the platform'}, {s:'2022', l:'First 100 clients', d:'Retail and logistics sectors'}, {s:'2024', l:'Series B', d:'Expansion to 6 countries'}, {s:'2025', l:'AI launch', d:'Smart forecasting for every plan'}, {s:'2026', l:'IPO ready', d:'Profitable, 400 employees'}]},
  ai:'3-8 events in date order: s = date or year, l = short title (max 5 words), d = one short sentence.',
  designs:[
    {id:'tl1', name:'Timeline · alternating cards', draw:function(K, Z, D){
      var R = items(D), n = R.length, cy = Z.y + Z.h/2, step = Z.w / n, cw = Math.min(step*1.7, 300), ch = Z.h/2 - 46;
      K.rect(Z.x, cy - 3, Z.w, 6, {pi:0, soft:0.35, r:3});
      R.forEach(function(r, i){
        var cx = Z.x + step*i + step/2, top = i % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw/2));
        K.circ(cx, cy, 15, {pi:i, line:K.bg, lw:4});
        K.line(cx, top ? cy - 18 : cy + 18, cx, top ? cy - 34 : cy + 34, {pi:i, lw:2});
        var y = top ? Z.y : cy + 34, c = K.card(x, y, cw, ch, {});
        K.t(x + 18, y + 12, cw - 36, 34, r.s, {max:26, min:12, font:'head', wt:900, pi:i, lines:1});
        K.block(x + 18, y + 50, cw - 36, ch - 60, r.l, r.d, {va:'t', hmax:18, bmax:15, hc:c.tc, bc:c.mc});
      });
    }},
    {id:'tl2', name:'Timeline · milestones row', draw:function(K, Z, D){
      var R = items(D), n = R.length, step = Z.w / n, ly = Z.y + 70;
      K.line(Z.x + step/2, ly, Z.x + Z.w - step/2, ly, {color:K.ln, lw:3});
      R.forEach(function(r, i){
        var cx = Z.x + step*i + step/2;
        K.t(cx - step/2, Z.y, step, 36, r.s, {max:24, min:11, font:'head', wt:900, pi:i, align:'c', lines:1});
        K.circ(cx, ly, 22, {pi:i}); K.tOn(cx - 22, ly - 22, 44, 44, String(i + 1), i, {size:17, wt:900, align:'c', font:'head'});
        var c = K.card(Z.x + step*i + 8, ly + 44, step - 16, Z.y + Z.h - ly - 44, {});
        K.rect(Z.x + step*i + 8, ly + 44, step - 16, 5, {pi:i, r:2});
        K.block(Z.x + step*i + 24, ly + 66, step - 48, Z.y + Z.h - ly - 84, r.l, r.d, {va:'t', hmax:19, bmax:15, hc:c.tc, bc:c.mc, align:'c'});
      });
    }},
    {id:'tl3', name:'Timeline · vertical spine', draw:function(K, Z, D){
      var R = items(D), n = R.length, cx = Z.x + Z.w/2, rh = Z.h / n;
      K.rect(cx - 2, Z.y, 4, Z.h, {pi:0, soft:0.35, r:2});
      R.forEach(function(r, i){
        var y = Z.y + rh*i, cy = y + rh/2, left = i % 2 === 0, bw = Z.w/2 - 70;
        K.circ(cx, cy, 13, {pi:i, line:K.bg, lw:4});
        K.t(left ? cx + 30 : cx - 30 - 200, y, 200, rh, r.s, {max:28, min:12, font:'head', wt:900, pi:i, align:left ? 'l' : 'r', lines:1});
        var bx = left ? cx - 44 - bw : cx + 44, c = K.card(bx, y + 4, bw, rh - 8, {});
        K.rect(left ? bx + bw - 5 : bx, y + 4, 5, rh - 8, {pi:i});
        K.block(bx + 20, y + 8, bw - 40, rh - 16, r.l, r.d, {hmax:18, bmax:14.5, hc:c.tc, bc:c.mc, align:left ? 'r' : 'l'});
      });
    }},
    {id:'tl4', name:'Timeline · snake', draw:function(K, Z, D){
      var R = items(D), n = R.length, per = Math.ceil(n / 2), step = Z.w / per, rows = [Z.y + Z.h*0.22, Z.y + Z.h*0.72];
      K.path([['M', Z.x + step/2, rows[0]], ['L', Z.x + Z.w - step/2, rows[0]], ['C', Z.x + Z.w + step*0.2, rows[0], Z.x + Z.w + step*0.2, rows[1], Z.x + Z.w - step/2, rows[1]], ['L', Z.x + step/2, rows[1]]], {stroke:K.ln, lw:5});
      R.forEach(function(r, i){
        var row = i < per ? 0 : 1, k = row ? per - 1 - (i - per) : i, cx = Z.x + step*k + step/2, cy = rows[row];
        K.circ(cx, cy, 20, {pi:i, line:K.bg, lw:4}); K.tOn(cx - 20, cy - 20, 40, 40, String(i + 1), i, {size:15, wt:900, align:'c'});
        K.t(cx - step/2 + 8, cy - 92, step - 16, 30, r.s, {max:20, min:10, font:'head', wt:900, pi:i, align:'c', lines:1});
        K.t(cx - step/2 + 8, cy - 62, step - 16, 36, r.l, {max:17, min:10, wt:800, color:K.tc, align:'c', va:'b', lines:2});
        K.t(cx - step/2 + 8, cy + 28, step - 16, Z.h*0.2, r.d, {max:14, min:9, color:K.mc, align:'c', va:'t', lines:3});
      });
    }}
  ]});

/* ---------------- Milestones ---------------- */
function st(d){ d = String(d || '').toLowerCase(); return /done|complete|achieved|live|✓/.test(d) ? 2 : (/progress|current|now|ongoing/.test(d) ? 1 : 0); }
DG.type({id:'milestone', cat:'timeline', name:'Milestone Chart', fields:[{k:'s', n:'Date'}, {k:'l', n:'Milestone'}, {k:'d', n:'Status (Done / In progress / Planned)'}], min:2, max:10,
  sample:{rows:[{s:'Jan', l:'Project kick-off', d:'Done'}, {s:'Mar', l:'Design approved', d:'Done'}, {s:'May', l:'Beta release', d:'In progress'}, {s:'Aug', l:'Customer pilot', d:'Planned'}, {s:'Oct', l:'General availability', d:'Planned'}, {s:'Dec', l:'1,000 customers', d:'Planned'}]},
  ai:'3-8 milestones in order: s = month or date, l = milestone (max 5 words), d = Done, In progress or Planned.',
  designs:[
    {id:'mi1', name:'Milestones · diamonds + flags', draw:function(K, Z, D){
      var R = items(D), n = R.length, step = Z.w / n, ly = Z.y + Z.h*0.56, done = -1;
      R.forEach(function(r, i){ if (st(r.d) >= 1) done = i; });
      K.rect(Z.x, ly - 5, Z.w, 10, {fill:K.ln, op:0.6, r:5});
      if (done >= 0) K.rect(Z.x, ly - 5, step*done + step/2, 10, {pi:0, r:5});
      R.forEach(function(r, i){
        var cx = Z.x + step*i + step/2, s = st(r.d), up = i % 2 === 0, sz = 34;
        K.shape('diamond', cx - sz/2, ly - sz/2, sz, sz, s ? {pi:0} : {fill:K.bg, line:K.col(0), lw:3});
        if (s === 1) K.circ(cx, ly, 6, {fill:K.bg});
        var fy = up ? ly - 150 : ly + 40;
        K.line(cx, up ? ly - sz/2 : ly + sz/2, cx, up ? fy + 110 : fy, {color:K.ln, lw:1.5, dash:true});
        K.t(cx - step/2 + 4, fy, step - 8, 30, r.s, {max:22, min:10, font:'head', wt:900, pi:0, align:'c', lines:1});
        K.t(cx - step/2 + 4, fy + 32, step - 8, 48, r.l, {max:17, min:10, wt:800, color:K.tc, align:'c', va:'t', lines:2});
        var lab = ['Planned', 'In progress', 'Done'][s], sc = [K.mc, K.warn(), K.good()][s];
        K.t(cx - step/2 + 4, fy + 82, step - 8, 24, lab.toUpperCase(), {size:12, wt:800, fix:s ? sc : null, color:s ? null : K.mc, align:'c', ls:0.08, lines:1});
      });
    }},
    {id:'mi2', name:'Milestones · status cards', draw:function(K, Z, D){
      var R = items(D), cl = K.cells(R.length, Z.x, Z.y, Z.w, Z.h, 16, 1.6);
      R.forEach(function(r, i){
        var b = cl[i], s = st(r.d), sc = [K.mc, K.warn(), K.good()][s], c = K.card(b.x, b.y, b.w, b.h, {});
        K.rect(b.x, b.y, b.w, 5, s ? {fix:sc} : {fill:K.ln});
        K.t(b.x + 20, b.y + 18, b.w*0.5, 34, r.s, {max:24, min:11, font:'head', wt:900, pi:0, lines:1});
        K.rect(b.x + b.w - 130, b.y + 20, 110, 28, {fix:s ? sc : K.mc, soft:0.18, r:14});
        K.t(b.x + b.w - 130, b.y + 20, 110, 28, ['Planned', 'In progress', 'Done'][s], {size:12.5, wt:800, fix:s ? sc : K.mc, align:'c', lines:1});
        K.t(b.x + 20, b.y + 60, b.w - 40, b.h - 76, r.l, {max:22, min:11, font:'head', wt:800, color:c.tc, va:'t', lines:3});
      });
    }}
  ]});

/* ---------------- Swimlane roadmap (period grid) ---------------- */
DG.type({id:'roadmap', cat:'timeline', name:'Roadmap', fields:[{k:'g', n:'Lane / team'}, {k:'l', n:'Initiative'}, {k:'s', n:'Start (period #)', num:1}, {k:'e', n:'End (period #)', num:1}], min:2, max:16,
  sample:{cols:['Q1', 'Q2', 'Q3', 'Q4'], rows:[{g:'Product', l:'Mobile app v2', s:1, e:2}, {g:'Product', l:'AI assistant', s:2, e:4}, {g:'Marketing', l:'Brand refresh', s:1, e:1}, {g:'Marketing', l:'Partner campaign', s:3, e:4}, {g:'Sales', l:'EU expansion', s:2, e:3}, {g:'Operations', l:'ISO 27001', s:1, e:3}, {g:'Operations', l:'New CRM', s:4, e:4}]},
  ai:'cols = 3-6 period names (e.g. Q1..Q4); rows: g = lane (team), l = initiative (max 4 words), s/e = start/end period numbers (1-based).',
  designs:[
    {id:'rm1', name:'Roadmap · swimlanes', draw:function(K, Z, D){
      var P = D.cols.length ? D.cols : ['Q1', 'Q2', 'Q3', 'Q4'], np = P.length, lanes = [];
      D.rows.forEach(function(r){ if (lanes.indexOf(r.g || 'General') < 0) lanes.push(r.g || 'General'); });
      var lab = 170, top = 44, cw = (Z.w - lab) / np, lh = (Z.h - top) / lanes.length;
      P.forEach(function(p, j){ K.rect(Z.x + lab + cw*j + 2, Z.y, cw - 4, top - 8, {pi:0, soft:j % 2 ? 0.22 : 0.3, r:8}); K.t(Z.x + lab + cw*j, Z.y, cw, top - 8, p, {max:17, min:10, wt:800, color:K.tc, align:'c', lines:1}); });
      lanes.forEach(function(ln, li){
        var y = Z.y + top + lh*li;
        if (li % 2 === 0) K.rect(Z.x, y, Z.w, lh, {fill:K.cardc, r:8, op:0.7});
        K.rect(Z.x + 10, y + 12, 6, lh - 24, {pi:li, r:3});
        K.t(Z.x + 26, y, lab - 34, lh, ln, {max:18, min:10, font:'head', wt:800, color:K.tc, lines:2});
        var rows = D.rows.filter(function(r){ return (r.g || 'General') === ln; }), slots = [];
        rows.forEach(function(r){ var s = Math.max(1, Math.min(np, r.s || 1)), e = Math.max(s, Math.min(np, r.e || s)), k = 0; while (slots[k] && slots[k].some(function(o){ return !(e < o[0] || s > o[1]); })) k++; (slots[k] = slots[k] || []).push([s, e]); r._k = k; r._s = s; r._e = e; });
        var nk = Math.max(1, slots.length), bh = Math.min(46, (lh - 16) / nk - 6);
        rows.forEach(function(r){
          var x = Z.x + lab + cw*(r._s - 1) + 6, w = cw*(r._e - r._s + 1) - 12, by = y + 8 + (lh - 16 - nk*(bh + 6))/2 + r._k*(bh + 6);
          K.rect(x, by, w, bh, {pi:li, r:bh/2});
          K.tOn(x + 16, by, w - 26, bh, r.l, li, {max:16, min:9, wt:700, lines:1});
        });
      });
    }},
    {id:'rm2', name:'Roadmap · period columns', draw:function(K, Z, D){
      var P = D.cols.length ? D.cols : ['Q1', 'Q2', 'Q3', 'Q4'], np = P.length, gap = 16, cw = (Z.w - gap*(np - 1)) / np, lanes = [];
      D.rows.forEach(function(r){ if (lanes.indexOf(r.g || 'General') < 0) lanes.push(r.g || 'General'); });
      P.forEach(function(p, j){
        var x = Z.x + j*(cw + gap), c = K.card(x, Z.y, cw, Z.h, {});
        K.rect(x, Z.y, cw, 52, {pi:0, soft:0.3 + 0.7*(j + 1)/np, r:12});
        K.tOn(x, Z.y, cw, 52, p, 0, {max:20, min:11, wt:900, font:'head', align:'c', lines:1}, 0.3 + 0.7*(j + 1)/np);
        var its = D.rows.filter(function(r){ return Math.max(1, Math.min(np, r.s || 1)) === j + 1; });
        var ih = Math.min(74, (Z.h - 70) / Math.max(1, its.length) - 10);
        its.forEach(function(r, k){
          var y = Z.y + 66 + k*(ih + 10), li = lanes.indexOf(r.g || 'General');
          K.rect(x + 14, y, cw - 28, ih, {pi:li, soft:0.16, r:10});
          K.rect(x + 14, y + 10, 5, ih - 20, {pi:li, r:2});
          K.t(x + 30, y + 6, cw - 52, ih*0.36, (r.g || '').toUpperCase(), {max:11.5, min:8, wt:800, pi:li, ls:0.06, lines:1});
          K.t(x + 30, y + ih*0.38, cw - 52, ih*0.56, r.l + (r.e && r.e > r.s ? '  →  ' + (P[Math.min(np, r.e) - 1] || '') : ''), {max:16, min:9, wt:700, color:c.tc, va:'t', lines:2});
        });
      });
    }}
  ]});

/* ---------------- Phases ---------------- */
function parts(d){ return String(d || '').split(/\s*[;|\n]\s*/).map(function(x){ return x.trim(); }).filter(Boolean); }
DG.type({id:'phases', cat:'timeline', name:'Phased Roadmap', fields:[{k:'l', n:'Phase'}, {k:'s', n:'Timing'}, {k:'d', n:'Key activities (separate with ;)'}], min:2, max:6,
  sample:{rows:[{l:'Discover', s:'Weeks 1-4', d:'Stakeholder interviews; Current-state audit; Success metrics'}, {l:'Design', s:'Weeks 5-10', d:'Target operating model; Solution blueprint; Business case'}, {l:'Build', s:'Weeks 11-20', d:'Agile sprints; Data migration; User testing'}, {l:'Launch', s:'Weeks 21-24', d:'Training; Go-live; Hypercare'}]},
  ai:'3-5 phases in order: l = phase name (1-2 words), s = timing, d = 2-4 activities separated by semicolons.',
  designs:[
    {id:'ph1', name:'Phases · chevrons + activities', draw:function(K, Z, D){
      var n = D.rows.length, ov = 30, w = (Z.w + ov*(n - 1)) / n, h = 104;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w - ov), sf = 0.45 + 0.55*(i + 1)/n;
        K.shape(i ? 'chevron' : 'homePlate', x, Z.y, w, h, {pi:0, soft:sf});
        K.tOn(x + (i ? ov + 14 : 24), Z.y + 14, w - ov - 50, 26, 'PHASE ' + (i + 1), 0, {size:12.5, wt:800, ls:0.1, lines:1}, sf);
        K.tOn(x + (i ? ov + 14 : 24), Z.y + 38, w - ov - 50, 50, r.l, 0, {max:26, min:12, font:'head', wt:900, lines:1}, sf);
        var cx = x + 4, cw = w - ov - 14,   /* every card starts under its own arrow: same left offset, same gap between cards */ y = Z.y + h + 22, c = K.card(cx, y, cw, Z.y + Z.h - y, {});
        K.t(cx + 18, y + 14, cw - 36, 26, r.s, {max:16, min:10, wt:800, pi:0, lines:1});
        K.list(cx + 18, y + 50, cw - 36, Z.y + Z.h - y - 64, parts(r.d), {max:17, min:10, pi:0, color:c.tc});
      });
    }},
    {id:'ph2', name:'Phases · road with pins', draw:function(K, Z, D){
      var n = D.rows.length, ry = Z.y + Z.h*0.52, amp = 46, pts = [];
      for (var k = 0; k <= 40; k++) { var t = k/40; pts.push([Z.x + Z.w*t, ry + Math.sin(t*Math.PI*2)*amp]); }
      K.curve(pts, {stroke:K.ln, lw:44});
      K.curve(pts, {stroke:K.bg, lw:3, lop:0.9});
      D.rows.forEach(function(r, i){
        var t = (i + 0.5)/n, px = Z.x + Z.w*t, py = ry + Math.sin(t*Math.PI*2)*amp, up = Math.sin(t*Math.PI*2) >= 0;
        K.circ(px, py - 52, 26, {pi:i}); K.poly([[px - 14, py - 34], [px + 14, py - 34], [px, py - 8]], {pi:i});
        K.tOn(px - 26, py - 78, 52, 52, String(i + 1), i, {size:20, wt:900, align:'c', font:'head'});
        var bw = Z.w / n - 16, bh = 150, by = up ? Math.max(Z.y, py - 96 - bh) : py + 36; if (!up) bh = Math.min(bh, Z.y + Z.h - by);
        K.t(px - bw/2, by, bw, 30, r.l, {max:22, min:11, font:'head', wt:900, pi:i, align:'c', lines:1});
        K.t(px - bw/2, by + 30, bw, 22, r.s, {size:13, wt:700, color:K.mc, align:'c', lines:1});
        K.t(px - bw/2, by + 56, bw, Math.max(30, bh - 60), parts(r.d).join(' · '), {max:14.5, min:9, color:K.txt, align:'c', va:'t', lh:1.3});
      });
    }},
    {id:'ph3', name:'Now · Next · Later', draw:function(K, Z, D){
      var n = D.rows.length, gap = 20, w = (Z.w - gap*(n - 1)) / n;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w + gap), c = K.card(x, Z.y, w, Z.h, {hl:i === 0});
        K.t(x + 26, Z.y + 24, w - 52, 22, r.s.toUpperCase(), {size:13, wt:800, color:c.mc, ls:0.1, lines:1});
        K.t(x + 26, Z.y + 50, w - 52, 50, r.l, {max:32, min:14, font:'head', wt:900, color:c.tc, lines:1});
        K.rect(x + 26, Z.y + 108, 44, 4, {pi:0, r:2});
        K.list(x + 26, Z.y + 132, w - 52, Z.h - 150, parts(r.d), {max:18, min:10, color:c.tc, dotColor:i === 0 ? c.tc : null, pi:i === 0 ? null : 0});
        if (i < n - 1) K.arrow(x + w + 2, Z.y + 74, x + w + gap - 2, Z.y + 74, {pi:0, lw:2, head:8});
      });
    }}
  ]});
})();

/* DS Diagrams — Project management: Gantt, schedule, PERT, burndown, burnup, Kanban */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('project', 'Project Management', '🧭', 'Gantt, schedule, PERT, burndown, burnup, Kanban');
var fmt = DG.fmt;

/* ---------------- Gantt ---------------- */
var GF = [{k:'l', n:'Task'}, {k:'g', n:'Phase'}, {k:'s', n:'Start (period #)', num:1}, {k:'e', n:'End (period #)', num:1}, {k:'v', n:'Progress %', num:1}];
function span(r, np){ var s = Math.max(1, Math.min(np, r.s || 1)), e = Math.max(s, Math.min(np, r.e || s)); return [s, e]; }
DG.type({id:'gantt', cat:'project', name:'Gantt Chart', fields:GF, min:2, max:16,
  sample:{cols:['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], o:{today:3.5}, rows:[
    {l:'Requirements', g:'Plan', s:1, e:1, v:100}, {l:'Solution design', g:'Plan', s:1, e:2, v:100}, {l:'Backend build', g:'Build', s:2, e:4, v:70}, {l:'Frontend build', g:'Build', s:3, e:4, v:45},
    {l:'Integration tests', g:'Test', s:4, e:5, v:10}, {l:'User acceptance', g:'Test', s:5, e:5, v:0}, {l:'Go-live & support', g:'Launch', s:6, e:6, v:0}]},
  ai:'cols = 4-8 period names (months or weeks); rows: l = task (max 4 words), g = phase, s/e = start/end period numbers (1-based), v = % complete.',
  designs:[
    {id:'gt1', name:'Gantt · classic', draw:function(K, Z, D){
      var P = D.cols.length ? D.cols : ['1', '2', '3', '4'], np = P.length, lab = Math.min(300, Z.w*0.26), top = 46, n = D.rows.length, cw = (Z.w - lab) / np, rh = (Z.h - top) / n, bh = Math.min(30, rh*0.6), phases = [];
      D.rows.forEach(function(r){ if (phases.indexOf(r.g || '') < 0) phases.push(r.g || ''); });
      P.forEach(function(p, j){ K.t(Z.x + lab + cw*j, Z.y, cw, top - 12, p, {max:16, min:9, wt:800, color:K.mc, align:'c', lines:1}); K.line(Z.x + lab + cw*j, Z.y + top - 6, Z.x + lab + cw*j, Z.y + Z.h, {color:K.ln, lw:1}); });
      K.line(Z.x, Z.y + top - 6, Z.x + Z.w, Z.y + top - 6, {color:K.ln, lw:1.5});
      D.rows.forEach(function(r, i){
        var y = Z.y + top + rh*i, sp = span(r, np), pi = phases.indexOf(r.g || ''), x = Z.x + lab + cw*(sp[0] - 1) + 4, w = cw*(sp[1] - sp[0] + 1) - 8, by = y + (rh - bh)/2, pr = Math.max(0, Math.min(100, r.v || 0));
        if (i % 2 === 0) K.rect(Z.x, y, Z.w, rh, {fill:K.cardc, op:0.55});
        K.rect(Z.x + 6, y + rh*0.25, 4, rh*0.5, {pi:pi, r:2});
        K.t(Z.x + 20, y, lab - 30, rh, r.l, {max:17, min:9, wt:700, color:K.tc, lines:1});
        K.rect(x, by, w, bh, {pi:pi, soft:0.3, r:bh/2});
        if (pr > 0) K.rect(x, by, Math.max(bh, w*pr/100), bh, {pi:pi, r:bh/2});
        K.tOn(x + 10, by, w - 20, bh, pr ? pr + '%' : '', pi, {max:13, min:8, wt:800, lines:1}, pr ? null : 0.3);
      });
      today(K, D, Z.x + lab, cw, Z.y + top - 6, Z.y + Z.h);
    }},
    {id:'gt2', name:'Gantt · phases', draw:function(K, Z, D){
      var P = D.cols.length ? D.cols : ['1', '2', '3', '4'], np = P.length, lab = Math.min(280, Z.w*0.24), top = 44, phases = [];
      D.rows.forEach(function(r){ if (phases.indexOf(r.g || 'Tasks') < 0) phases.push(r.g || 'Tasks'); });
      var lines = D.rows.length + phases.length, cw = (Z.w - lab) / np, rh = (Z.h - top) / lines, y = Z.y + top;
      P.forEach(function(p, j){ K.rect(Z.x + lab + cw*j + 1, Z.y, cw - 2, top - 8, {pi:0, soft:0.14, r:6}); K.t(Z.x + lab + cw*j, Z.y, cw, top - 8, p, {max:15, min:9, wt:800, color:K.tc, align:'c', lines:1}); });
      phases.forEach(function(ph, pi){
        var rows = D.rows.filter(function(r){ return (r.g || 'Tasks') === ph; }), s = np, e = 1;
        rows.forEach(function(r){ var sp = span(r, np); s = Math.min(s, sp[0]); e = Math.max(e, sp[1]); });
        K.rect(Z.x, y + 2, Z.w, rh - 4, {pi:pi, soft:0.12, r:6});
        K.t(Z.x + 14, y, lab - 20, rh, ph.toUpperCase(), {max:14, min:8, wt:900, pi:pi, ls:0.08, lines:1});
        var sx = Z.x + lab + cw*(s - 1) + 2, sw = cw*(e - s + 1) - 4;
        K.rect(sx, y + rh*0.36, sw, rh*0.28, {pi:pi, r:3});
        K.poly([[sx, y + rh*0.36], [sx + 10, y + rh*0.36], [sx, y + rh*0.9]], {pi:pi}); K.poly([[sx + sw, y + rh*0.36], [sx + sw - 10, y + rh*0.36], [sx + sw, y + rh*0.9]], {pi:pi});
        y += rh;
        rows.forEach(function(r){
          var sp = span(r, np), x = Z.x + lab + cw*(sp[0] - 1) + 6, w = cw*(sp[1] - sp[0] + 1) - 12, bh = Math.min(26, rh*0.56), pr = Math.max(0, Math.min(100, r.v || 0));
          K.t(Z.x + 30, y, lab - 36, rh, r.l, {max:15.5, min:8.5, color:K.txt, lines:1});
          K.rect(x, y + (rh - bh)/2, w, bh, {pi:pi, soft:0.28, r:6});
          if (pr) K.rect(x, y + (rh - bh)/2, w*pr/100, bh, {pi:pi, r:6});
          y += rh;
        });
      });
    }}
,
    {id:'gt3', name:'Gantt · milestones + bars', draw:function(K, Z, D){
      var P = D.cols.length ? D.cols : ['1', '2', '3', '4'], np = P.length, lab = Math.min(260, Z.w*0.24), n = D.rows.length, cw = (Z.w - lab) / np, top = 56, rh = (Z.h - top) / n;
      K.rect(Z.x + lab, Z.y, Z.w - lab, 40, {pi:0, r:20});
      P.forEach(function(p, j){ K.tOn(Z.x + lab + cw*j, Z.y, cw, 40, p, 0, {max:15, min:9, wt:800, align:'c', lines:1}); if (j) K.line(Z.x + lab + cw*j, Z.y + top, Z.x + lab + cw*j, Z.y + Z.h, {color:K.ln, lw:1, dash:true}); });
      D.rows.forEach(function(r, i){
        var y = Z.y + top + rh*i, sp = span(r, np), cy = y + rh/2, done = (r.v || 0) >= 100;
        K.t(Z.x, y, lab - 20, rh, r.l, {max:17, min:9, wt:700, color:K.tc, align:'r', lines:2});
        if (sp[0] === sp[1] && (r.g || '').toLowerCase().indexOf('mile') >= 0) { var mx = Z.x + lab + cw*(sp[0] - 0.5); K.shape('diamond', mx - 14, cy - 14, 28, 28, {fix:done ? K.good() : null, pi:done ? null : 1}); return; }
        var x = Z.x + lab + cw*(sp[0] - 1) + 6, w = cw*(sp[1] - sp[0] + 1) - 12, bh = Math.min(22, rh*0.5);
        K.rect(x, cy - bh/2, w, bh, {pi:0, soft:0.28, r:bh/2});
        K.rect(x, cy - bh/2, w*Math.max(0.02, Math.min(1, (r.v || 0)/100)), bh, {pi:0, r:bh/2});
        K.circ(x + w, cy, bh*0.62, {fill:K.bg, line:done ? K.good() : K.col(0), lw:3});
      });
    }},
    /* 4 — phase swimlanes: coloured phase band on the left spanning its tasks */
    {id:'gt4', name:'Gantt · phase swimlanes', draw:function(K, Z, D){
      var P = per(D), np = P.length, phases = phs(D), rows = byPhase(D, phases), n = rows.length;
      var band = Math.min(150, Z.w*0.12), lab = Math.min(240, Z.w*0.2), top = 44, x0 = Z.x + band + lab, cw = (Z.x + Z.w - x0) / np, rh = (Z.h - top) / n, bh = Math.min(24, rh*0.5);
      header(K, P, x0, Z.y, cw, top, Z.y + Z.h);
      var i = 0;
      phases.forEach(function(ph, pi){
        var cnt = rows.filter(function(r){ return (r.g || 'Tasks') === ph; }).length, y0 = Z.y + top + rh*i;
        K.rect(Z.x + band, y0 + 2, Z.w - band, rh*cnt - 4, {pi:pi, soft:0.1, r:8});
        K.rect(Z.x, y0 + 2, band - 8, rh*cnt - 4, {pi:pi, r:8});
        K.tOn(Z.x + 8, y0 + 2, band - 24, rh*cnt - 4, ph, pi, {max:16, min:9, wt:800, align:'c', lines:3});
        for (var k = 0; k < cnt; k++, i++) {
          var r = rows[i], y = Z.y + top + rh*i, sp = span(r, np), x = x0 + cw*(sp[0] - 1) + 5, w = cw*(sp[1] - sp[0] + 1) - 10, by = y + (rh - bh)/2, pr = pct(r);
          K.t(Z.x + band + 12, y, lab - 20, rh, r.l, {max:16, min:9, wt:600, color:K.tc, lines:1});
          K.rect(x, by, w, bh, {pi:pi, soft:0.35, r:bh/2});
          if (pr) K.rect(x, by, Math.max(bh, w*pr/100), bh, {pi:pi, r:bh/2});
        }
      });
      today(K, D, x0, cw, Z.y + top - 6, Z.y + Z.h);
    }},
    /* 5 — minimal: no label column, task name sits above a slim bar */
    {id:'gt5', name:'Gantt · minimal lines', draw:function(K, Z, D){
      var P = per(D), np = P.length, n = D.rows.length, top = 40, cw = Z.w / np, rh = (Z.h - top) / n, bh = Math.max(6, Math.min(12, rh*0.2)), phases = phs(D);
      P.forEach(function(p, j){ K.t(Z.x + cw*j, Z.y, cw, top - 12, p.toUpperCase(), {max:13, min:8, wt:800, color:K.mc, ls:0.08, lines:1}); K.line(Z.x + cw*j, Z.y + top - 4, Z.x + cw*j, Z.y + Z.h, {color:K.ln, lw:1, op:0.6}); });
      K.line(Z.x, Z.y + top - 4, Z.x + Z.w, Z.y + top - 4, {color:K.ln, lw:1.5});
      D.rows.forEach(function(r, i){
        var y = Z.y + top + rh*i, sp = span(r, np), pi = phases.indexOf(r.g || 'Tasks'), x = Z.x + cw*(sp[0] - 1) + 8, w = cw*(sp[1] - sp[0] + 1) - 16, by = y + rh*0.64, pr = pct(r);
        var tw = Math.min(360, Z.x + Z.w - x), tx = tw < 200 ? Math.max(Z.x, x + w - 360) : x;
        K.t(tx, y + rh*0.08, Math.max(tw, 200), rh*0.5, r.l + (pr ? '  ·  ' + pr + '%' : ''), {max:16, min:9, wt:700, color:K.tc, lines:1, align:tw < 200 ? 'r' : 'l', va:'b'});
        K.rect(x, by, w, bh, {pi:pi, soft:0.3, r:bh/2});
        if (pr) K.rect(x, by, Math.max(bh, w*pr/100), bh, {pi:pi, r:bh/2});
        K.circ(x, by + bh/2, bh*0.9, {pi:pi}); K.circ(x + w, by + bh/2, bh*0.9, {fill:K.bg, line:K.col(pi), lw:2.5});
      });
      today(K, D, Z.x, cw, Z.y + top - 4, Z.y + Z.h);
    }},
    /* 6 — status colours: done / in progress / not started, legend on top */
    {id:'gt6', name:'Gantt · status colours', draw:function(K, Z, D){
      var P = per(D), np = P.length, n = D.rows.length, lab = Math.min(280, Z.w*0.24), top = 76, cw = (Z.w - lab) / np, rh = (Z.h - top) / n, bh = Math.min(26, rh*0.56);
      var ST = [{l:'Done', c:K.good()}, {l:'In progress', c:K.col(0), pi:0}, {l:'Not started', c:K.mc}], lx = Z.x + Z.w;
      ST.slice().reverse().forEach(function(s){ var lw = s.l.length*8 + 34; lx -= lw + 12; K.rect(lx, Z.y + 6, 14, 14, s.pi != null ? {pi:0, r:4} : (s.l === 'Done' ? {fix:s.c, r:4} : {fill:K.cardc, line:K.mc, lw:1.2, r:4, dash:true})); K.t(lx + 20, Z.y, lw - 20, 26, s.l, {size:13, wt:600, color:K.mc, lines:1}); });
      header(K, P, Z.x + lab, Z.y + 32, cw, top - 32, Z.y + Z.h);
      D.rows.forEach(function(r, i){
        var y = Z.y + top + rh*i, sp = span(r, np), pr = pct(r), x = Z.x + lab + cw*(sp[0] - 1) + 5, w = cw*(sp[1] - sp[0] + 1) - 10, by = y + (rh - bh)/2;
        if (i % 2 === 0) K.rect(Z.x, y, Z.w, rh, {fill:K.cardc, op:0.5});
        K.t(Z.x + 12, y, lab - 20, rh*0.62, r.l, {max:16, min:9, wt:700, color:K.tc, lines:1, va:'b'});
        if (r.g) K.t(Z.x + 12, y + rh*0.55, lab - 20, rh*0.4, r.g, {max:12, min:8, color:K.mc, lines:1, va:'t'});
        if (pr >= 100) K.rect(x, by, w, bh, {fix:K.good(), r:6});
        else if (pr > 0) { K.rect(x, by, w, bh, {pi:0, soft:0.3, r:6}); K.rect(x, by, Math.max(12, w*pr/100), bh, {pi:0, r:6}); }
        else K.rect(x, by, w, bh, {fill:K.cardc, line:K.mc, lw:1.2, r:6, dash:true});
        var inside = w > 70, lbl = pr >= 100 ? '✓' : pr + '%';
        if (inside && pr >= 100) K.t(x, by, w, bh, lbl, {size:13, wt:800, color:'#FFFFFF', align:'c', lines:1});
        else if (x + w + 60 < Z.x + Z.w) K.t(x + w + 8, by, 52, bh, lbl, {size:12, wt:800, color:K.mc, lines:1}); else K.t(x + w - 60, by, 54, bh, lbl, {size:12, wt:800, color:K.tc, align:'r', lines:1});
      });
      today(K, D, Z.x + lab, cw, Z.y + top - 6, Z.y + Z.h);
    }},
    /* 7 — names inside the bars over shaded period columns */
    {id:'gt7', name:'Gantt · labels in bars', draw:function(K, Z, D){
      var P = per(D), np = P.length, n = D.rows.length, top = 48, cw = Z.w / np, rh = (Z.h - top) / n, bh = Math.min(38, rh*0.7), phases = phs(D);
      P.forEach(function(p, j){ K.rect(Z.x + cw*j + 2, Z.y, cw - 4, Z.h, {pi:0, soft:j % 2 ? 0.05 : 0.1, r:8}); K.t(Z.x + cw*j, Z.y, cw, top - 8, p, {max:16, min:9, wt:800, color:K.tc, align:'c', lines:1}); });
      D.rows.forEach(function(r, i){
        var y = Z.y + top + rh*i, sp = span(r, np), pi = phases.indexOf(r.g || 'Tasks'), x = Z.x + cw*(sp[0] - 1) + 8, w = cw*(sp[1] - sp[0] + 1) - 16, by = y + (rh - bh)/2, pr = pct(r);
        K.rect(x, by, w, bh, {pi:pi, r:8, shadow:true});
        var need = String(r.l).length*8.2 + 60;
        if (w >= need) { K.tOn(x + 14, by, w - 70, bh, r.l, pi, {max:16, min:9, wt:700, lines:1}); K.tOn(x + w - 58, by, 48, bh, pr + '%', pi, {size:13, wt:800, align:'r', lines:1}); }
        else if (x + w + need < Z.x + Z.w) K.t(x + w + 10, by, need, bh, r.l + '  ' + pr + '%', {max:15, min:9, wt:700, color:K.tc, lines:1});
        else K.t(Math.max(Z.x, x - need - 10), by, need, bh, r.l + '  ' + pr + '%', {max:15, min:9, wt:700, color:K.tc, align:'r', lines:1});
      });
      today(K, D, Z.x, cw, Z.y + top - 6, Z.y + Z.h);
    }},
    /* 8 — table (task / start / end / done) + timeline */
    {id:'gt8', name:'Gantt · table + timeline', draw:function(K, Z, D){
      var P = per(D), np = P.length, n = D.rows.length, hh = 44, C = [0.24, 0.08, 0.08, 0.08], tw = Z.w*(C[0] + C[1] + C[2] + C[3]), x0 = Z.x + tw + 16, cw = (Z.x + Z.w - x0) / np, rh = (Z.h - hh) / n, bh = Math.min(24, rh*0.5), phases = phs(D);
      K.rect(Z.x, Z.y, Z.w, hh, {pi:0, r:8});
      var hx = Z.x; ['Task', 'Start', 'End', 'Done'].forEach(function(h, j){ K.tOn(hx + 12, Z.y, Z.w*C[j] - 14, hh, h.toUpperCase(), 0, {size:12, wt:800, ls:0.06, lines:1}); hx += Z.w*C[j]; });
      P.forEach(function(p, j){ K.tOn(x0 + cw*j, Z.y, cw, hh, p, 0, {max:13, min:8, wt:800, align:'c', lines:1}); if (j) K.line(x0 + cw*j, Z.y + hh, x0 + cw*j, Z.y + Z.h, {color:K.ln, lw:1, dash:true}); });
      D.rows.forEach(function(r, i){
        var y = Z.y + hh + rh*i, sp = span(r, np), pi = phases.indexOf(r.g || 'Tasks'), pr = pct(r), cx = Z.x;
        if (i % 2) K.rect(Z.x, y, Z.w, rh, {fill:K.cardc, op:0.6});
        [r.l, P[sp[0] - 1], P[sp[1] - 1], pr + '%'].forEach(function(v, j){ K.t(cx + 12, y, Z.w*C[j] - 16, rh, v, {max:j ? 14 : 16, min:8, wt:j ? 500 : 700, color:j === 3 && pr >= 100 ? K.good() : (j ? K.mc : K.tc), lines:1}); cx += Z.w*C[j]; });
        var x = x0 + cw*(sp[0] - 1) + 4, w = cw*(sp[1] - sp[0] + 1) - 8, by = y + (rh - bh)/2;
        K.rect(x, by, w, bh, {pi:pi, soft:0.3, r:4}); if (pr) K.rect(x, by, Math.max(6, w*pr/100), bh, {pi:pi, r:4});
      });
      K.line(x0 - 8, Z.y + hh, x0 - 8, Z.y + Z.h, {color:K.ln, lw:1.5});
    }},
    /* 9 — numbered tasks with chevron (arrow) bars */
    {id:'gt9', name:'Gantt · chevron bars', draw:function(K, Z, D){
      var P = per(D), np = P.length, n = D.rows.length, lab = Math.min(270, Z.w*0.23), top = 46, cw = (Z.w - lab) / np, rh = (Z.h - top) / n, bh = Math.min(34, rh*0.66), phases = phs(D), br = Math.min(15, rh*0.32);
      header(K, P, Z.x + lab, Z.y, cw, top, Z.y + Z.h);
      D.rows.forEach(function(r, i){
        var y = Z.y + top + rh*i, sp = span(r, np), pi = phases.indexOf(r.g || 'Tasks'), pr = pct(r), x = Z.x + lab + cw*(sp[0] - 1) + 2, w = cw*(sp[1] - sp[0] + 1) - 4, by = y + (rh - bh)/2;
        K.badge(Z.x + br + 2, y + rh/2, br, i + 1, pi);
        K.t(Z.x + 2*br + 14, y, lab - 2*br - 22, rh, r.l, {max:16, min:9, wt:700, color:K.tc, lines:2});
        K.shape('homePlate', x, by, w, bh, {pi:pi, soft:pr >= 100 ? null : 0.55});
        if (pr > 0 && pr < 100) K.rect(x, by, Math.max(8, Math.min(w - bh*0.5, w*pr/100)), bh, {pi:pi});
        if (w > 60) K.tOn(x + 10, by, w - 26, bh, pr >= 100 ? 'Complete' : pr + '%', pi, {max:13, min:8, wt:800, lines:1}, pr >= 100 || pr > 40 ? null : 0.55);
      });
      today(K, D, Z.x + lab, cw, Z.y + top - 6, Z.y + Z.h);
    }},
    /* 10 — dashboard: KPI strip on top, compact Gantt below */
    {id:'gt10', name:'Gantt · dashboard', draw:function(K, Z, D){
      var P = per(D), np = P.length, n = D.rows.length, phases = phs(D), avg = Math.round(D.rows.reduce(function(a, r){ return a + pct(r); }, 0) / Math.max(1, n)), done = D.rows.filter(function(r){ return pct(r) >= 100; }).length;
      var kh = Math.min(96, Z.h*0.2), KP4 = [[String(n), 'Tasks'], [avg + '%', 'Overall progress'], [done + ' / ' + n, 'Completed'], [String(phases.length), 'Phases']];
      K.cells(4, Z.x, Z.y, Z.w, kh, 16, 3).forEach(function(c, j){
        var cc = K.card(c.x, c.y, c.w, c.h, {}); K.rect(c.x, c.y + c.h*0.2, 5, c.h*0.6, {pi:j, r:2.5});
        K.t(c.x + 22, c.y + 6, c.w - 30, c.h*0.58, KP4[j][0], {max:30, min:14, wt:800, font:'head', color:cc.tc, lines:1, va:'b'});
        K.t(c.x + 22, c.y + c.h*0.6, c.w - 30, c.h*0.34, KP4[j][1], {max:14, min:9, color:cc.mc, lines:1, va:'t'});
      });
      var y0 = Z.y + kh + 22, lab = Math.min(250, Z.w*0.22), top = 34, cw = (Z.w - lab) / np, rh = (Z.y + Z.h - y0 - top) / n, bh = Math.min(20, rh*0.55);
      header(K, P, Z.x + lab, y0, cw, top, Z.y + Z.h);
      D.rows.forEach(function(r, i){
        var y = y0 + top + rh*i, sp = span(r, np), pi = phases.indexOf(r.g || 'Tasks'), pr = pct(r), x = Z.x + lab + cw*(sp[0] - 1) + 4, w = cw*(sp[1] - sp[0] + 1) - 8, by = y + (rh - bh)/2;
        K.rect(Z.x + 4, y + rh*0.3, 4, rh*0.4, {pi:pi, r:2});
        K.t(Z.x + 16, y, lab - 24, rh, r.l, {max:15, min:8, wt:600, color:K.tc, lines:1});
        K.rect(x, by, w, bh, {pi:pi, soft:0.3, r:bh/2}); if (pr) K.rect(x, by, Math.max(bh, w*pr/100), bh, {pi:pi, r:bh/2});
      });
      today(K, D, Z.x + lab, cw, y0 + top - 6, Z.y + Z.h);
    }}
  ]});
function per(D){ return D.cols && D.cols.length ? D.cols : ['1', '2', '3', '4']; }
function phs(D){ var p = []; D.rows.forEach(function(r){ var g = r.g || 'Tasks'; if (p.indexOf(g) < 0) p.push(g); }); return p; }
function byPhase(D, phases){ var out = []; phases.forEach(function(ph){ D.rows.forEach(function(r){ if ((r.g || 'Tasks') === ph) out.push(r); }); }); return out; }
function pct(r){ return Math.max(0, Math.min(100, Math.round(+r.v || 0))); }
function header(K, P, x0, y, cw, top, yb){ P.forEach(function(p, j){ K.t(x0 + cw*j, y, cw, top - 10, p, {max:15, min:8, wt:800, color:K.mc, align:'c', lines:1}); K.line(x0 + cw*j, y + top - 6, x0 + cw*j, yb, {color:K.ln, lw:1, dash:true, op:0.7}); }); K.line(x0, y + top - 6, x0 + cw*P.length, y + top - 6, {color:K.ln, lw:1.5}); }
function today(K, D, x0, cw, y1, y2){ var td = D.o && +D.o.today; if (!td) return; var tx = x0 + cw*(td - 1); K.line(tx, y1, tx, y2, {fix:K.bad(), color:K.bad(), lw:2, dash:true}); K.rect(tx - 28, y2 - 20, 56, 20, {fix:K.bad(), r:10}); K.t(tx - 28, y2 - 20, 56, 20, 'Today', {size:11, wt:800, color:'#FFFFFF', align:'c', lines:1}); }

/* ---------------- Project schedule ---------------- */
function stc(K, d){ d = String(d || '').toLowerCase(); return /done|complete/.test(d) ? K.good() : (/risk|delay|late|block/.test(d) ? K.bad() : (/progress|active|ongoing|track/.test(d) ? K.warn() : K.mc)); }
DG.type({id:'schedule', cat:'project', name:'Project Schedule', fields:[{k:'l', n:'Deliverable'}, {k:'s', n:'Start'}, {k:'e', n:'End'}, {k:'g', n:'Owner'}, {k:'d', n:'Status'}], min:2, max:10,
  sample:{rows:[{l:'Business case sign-off', s:'06 Jan', e:'24 Jan', g:'CFO office', d:'Done'}, {l:'Vendor selection', s:'27 Jan', e:'28 Feb', g:'Procurement', d:'Done'}, {l:'Platform configuration', s:'03 Mar', e:'25 Apr', g:'IT', d:'In progress'}, {l:'Data migration', s:'14 Apr', e:'30 May', g:'Data team', d:'At risk'}, {l:'Staff training', s:'02 Jun', e:'20 Jun', g:'HR', d:'Not started'}, {l:'Go-live', s:'30 Jun', e:'30 Jun', g:'PMO', d:'Not started'}]},
  ai:'3-8 deliverables with start and end dates, owner and status (Done, In progress, At risk, Not started).',
  designs:[
    {id:'sc2', name:'Schedule · table', draw:function(K, Z, D){
      var n = D.rows.length, hh = 46, rh = Math.min(76, (Z.h - hh) / n), C = [0, 0.38, 0.52, 0.66, 0.84], W0 = Z.w;
      K.rect(Z.x, Z.y, W0, hh, {pi:0, r:10});
      ['Deliverable', 'Start', 'End', 'Owner', 'Status'].forEach(function(h, j){ K.tOn(Z.x + 22 + C[j]*W0, Z.y, 0.16*W0, hh, h.toUpperCase(), 0, {size:13, wt:800, ls:0.08, lines:1}); });
      D.rows.forEach(function(r, i){
        var y = Z.y + hh + i*rh, sc = stc(K, r.d); if (i % 2) K.rect(Z.x, y, W0, rh, {fill:K.cardc, r:6});
        K.circ(Z.x + 22, y + rh/2, 7, {fix:sc});
        K.t(Z.x + 40, y, C[1]*W0 - 50, rh, r.l, {max:18, min:10, wt:700, color:K.tc, lines:2});
        K.t(Z.x + 22 + C[1]*W0, y, 0.13*W0, rh, r.s, {max:16, min:9, color:K.txt, lines:1});
        K.t(Z.x + 22 + C[2]*W0, y, 0.13*W0, rh, r.e, {max:16, min:9, color:K.txt, lines:1});
        K.t(Z.x + 22 + C[3]*W0, y, 0.17*W0, rh, r.g, {max:16, min:9, color:K.mc, lines:1});
        K.rect(Z.x + C[4]*W0 + 10, y + rh/2 - 16, 0.16*W0 - 20, 32, {fix:sc, soft:0.18, r:16});
        K.t(Z.x + C[4]*W0 + 10, y + rh/2 - 16, 0.16*W0 - 20, 32, r.d || '—', {max:14, min:8, wt:800, fix:sc, align:'c', lines:1});
      });
    }}
  ]});

/* ---------------- PERT ---------------- */
DG.type({id:'pert', cat:'project', name:'PERT Chart', fields:[{k:'l', n:'Task'}, {k:'v', n:'Duration (days)', num:1}, {k:'p', n:'Depends on (tasks, comma separated)'}], min:3, max:14,
  sample:{rows:[{l:'Scope', v:5, p:''}, {l:'Design', v:10, p:'Scope'}, {l:'Procure', v:15, p:'Scope'}, {l:'Build', v:20, p:'Design'}, {l:'Install', v:8, p:'Procure, Build'}, {l:'Test', v:6, p:'Install'}, {l:'Train', v:4, p:'Design'}, {l:'Launch', v:2, p:'Test, Train'}]},
  ai:'4-10 tasks with duration in days and dependencies (names of earlier tasks, comma separated; empty for the first).',
  designs:[
    {id:'pe1', name:'PERT · network + critical path', draw:function(K, Z, D){
      var R = D.rows, by = {}, lvl = {}, es = {}, ef = {};
      R.forEach(function(r){ by[r.l] = r; });
      function deps(r){ return String(r.p || '').split(',').map(function(x){ return x.trim(); }).filter(function(x){ return by[x] && x !== r.l; }); }
      function L(r, seen){ if (lvl[r.l] != null) return lvl[r.l]; seen = seen || {}; if (seen[r.l]) return 0; seen[r.l] = 1; var m = -1; deps(r).forEach(function(d){ m = Math.max(m, L(by[d], seen)); }); return (lvl[r.l] = m + 1); }
      function EF(r, seen){ if (ef[r.l] != null) return ef[r.l]; seen = seen || {}; if (seen[r.l]) return 0; seen[r.l] = 1; var m = 0; deps(r).forEach(function(d){ m = Math.max(m, EF(by[d], seen)); }); es[r.l] = m; return (ef[r.l] = m + (r.v || 0)); }
      R.forEach(function(r){ L(r); EF(r); });
      var end = 0, last = null; R.forEach(function(r){ if (ef[r.l] >= end) { end = ef[r.l]; last = r; } });
      var crit = {}; (function mark(r){ if (!r) return; crit[r.l] = 1; var best = null; deps(r).forEach(function(d){ if (ef[d] === es[r.l] && (!best || ef[d] >= ef[best])) best = d; }); if (best) mark(by[best]); })(last);
      var cols = []; R.forEach(function(r){ (cols[lvl[r.l]] = cols[lvl[r.l]] || []).push(r); });
      var nc = cols.length, cw = Z.w / nc, bw = Math.min(190, cw - 44), mxr = Math.max.apply(null, cols.map(function(c){ return c.length; })), bh = Math.min(96, (Z.h - 40) / mxr - 22), pos = {};
      cols.forEach(function(c, ci){ var gh = Z.h / c.length; c.forEach(function(r, k){ pos[r.l] = {x:Z.x + cw*ci + (cw - bw)/2, y:Z.y + gh*k + (gh - bh)/2}; }); });
      R.forEach(function(r){ deps(r).forEach(function(d){ var a = pos[d], b = pos[r.l], cr = crit[d] && crit[r.l];
        K.arrow(a.x + bw, a.y + bh/2, b.x - 3, b.y + bh/2, cr ? {fix:K.bad(), color:K.bad(), lw:3, head:11} : {color:K.mc, lw:1.6, head:9}); }); });
      R.forEach(function(r){
        var p = pos[r.l], cr = crit[r.l];
        K.rect(p.x, p.y, bw, bh, cr ? {fix:K.bad(), soft:0.12, r:12, line:K.bad(), lw:2} : {fill:K.cardc, r:12, line:K.ln, lw:1.2});
        K.t(p.x + 12, p.y + 8, bw - 24, bh*0.5, r.l, {max:18, min:9, wt:800, font:'head', color:K.tc, align:'c', lines:2});
        K.line(p.x + 10, p.y + bh*0.6, p.x + bw - 10, p.y + bh*0.6, {color:K.ln, lw:1});
        K.t(p.x + 8, p.y + bh*0.62, bw/2 - 8, bh*0.36, (r.v || 0) + 'd', {max:14, min:8, wt:800, fix:cr ? K.bad() : null, color:cr ? null : K.ac, align:'c', lines:1});
        K.t(p.x + bw/2, p.y + bh*0.62, bw/2 - 8, bh*0.36, 'day ' + es[r.l] + '–' + ef[r.l], {max:12, min:7, color:K.mc, align:'c', lines:1});
      });
      K.rect(Z.x + Z.w - 260, Z.y - 2, 16, 4, {fix:K.bad()}); K.t(Z.x + Z.w - 236, Z.y - 14, 236, 28, 'Critical path · ' + end + ' days', {size:14, wt:800, fix:K.bad(), lines:1});
    }}
  ]});

/* ---------------- Burndown / burnup (real charts) ---------------- */
var SF = [{k:'l', n:'Day / sprint'}];
function ser(D){ return D.cols.map(function(n, i){ return {n:n, v:D.rows.map(function(r){ return r.v[i] || 0; }), pi:i}; }); }
DG.type({id:'burndown', cat:'project', name:'Burndown Chart', fields:SF, series:true, min:3, max:20,
  sample:{cols:['Ideal', 'Actual'], rows:[{l:'Day 1', v:[80, 80]}, {l:'Day 2', v:[71, 76]}, {l:'Day 3', v:[62, 70]}, {l:'Day 4', v:[53, 61]}, {l:'Day 5', v:[44, 50]}, {l:'Day 6', v:[36, 41]}, {l:'Day 7', v:[27, 30]}, {l:'Day 8', v:[18, 22]}, {l:'Day 9', v:[9, 11]}, {l:'Day 10', v:[0, 3]}], unit:'pts'},
  ai:'days of a sprint with two series: Ideal (straight line down to 0) and Actual remaining work.',
  designs:[
    {id:'bd1', name:'Burndown · ideal vs actual', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'line', cats:D.rows.map(function(r){ return r.l; }), ser:ser(D), unit:D.unit, lbl:false, leg:'t'}); }},
    {id:'bd2', name:'Burndown + sprint stats', draw:function(K, Z, D){
      var kw = Math.round(Z.w*0.26), a = D.rows.length ? D.rows[D.rows.length - 1].v : [0, 0], s0 = D.rows.length ? D.rows[0].v[D.cols.length - 1] || 0 : 0, rem = a[D.cols.length - 1] || 0, done = s0 - rem;
      [[fmt(rem, D.unit), 'remaining'], [fmt(done, D.unit), 'completed'], [(s0 ? Math.round(done/s0*100) : 0) + '%', 'of sprint scope done']].forEach(function(c, i, arr){
        var ch = (Z.h - 32) / 3, y = Z.y + i*(ch + 16), cc = K.card(Z.x, y, kw, ch, {hl:i === 0});
        K.t(Z.x + 24, y + 8, kw - 48, ch*0.6, c[0], {max:46, min:16, font:'head', wt:900, color:i === 0 ? cc.tc : K.col(0), va:'b', lines:1});
        K.t(Z.x + 24, y + ch*0.64, kw - 48, ch*0.3, c[1], {max:16, min:10, color:cc.mc, va:'t', lines:1});
      });
      K.nativeChart(Z.x + kw + 36, Z.y, Z.w - kw - 36, Z.h, {k:'line', cats:D.rows.map(function(r){ return r.l; }), ser:ser(D), unit:D.unit, lbl:false, leg:'t'});
    }}
  ]});
DG.type({id:'burnup', cat:'project', name:'Burnup Chart', fields:SF, series:true, min:3, max:20,
  sample:{cols:['Total scope', 'Completed'], rows:[{l:'Sprint 1', v:[120, 14]}, {l:'Sprint 2', v:[120, 31]}, {l:'Sprint 3', v:[132, 50]}, {l:'Sprint 4', v:[132, 72]}, {l:'Sprint 5', v:[140, 95]}, {l:'Sprint 6', v:[140, 118]}], unit:'pts'},
  ai:'sprints with two series: Total scope and Completed work (cumulative).',
  designs:[
    {id:'bup1', name:'Burnup · scope vs completed', draw:function(K, Z, D){
      var s = ser(D); K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'combo', dir:'col', grp:'clustered', gap:60, cats:D.rows.map(function(r){ return r.l; }), unit:D.unit, lbl:true, leg:'t',
        ser:s.map(function(x, i){ x.ty = i === 0 ? 'line' : 'bar'; return x; })});
    }},
    {id:'bup2', name:'Burnup · area', draw:function(K, Z, D){ K.nativeChart(Z.x, Z.y, Z.w, Z.h, {k:'area', grp:'standard', cats:D.rows.map(function(r){ return r.l; }), ser:ser(D), unit:D.unit, lbl:false, leg:'t'}); }}
  ]});

/* ---------------- Kanban ---------------- */
var KB = ['To do', 'In progress', 'Review', 'Done'];
DG.type({id:'kanban', cat:'project', name:'Kanban Board', fields:[{k:'g', n:'Column', group:1}, {k:'l', n:'Card'}, {k:'d', n:'Owner / tag'}], groups:KB, min:2, max:24,
  sample:{rows:[{g:'To do', l:'Pricing page copy', d:'Marketing'}, {g:'To do', l:'SSO for enterprise', d:'Platform'}, {g:'To do', l:'Q3 partner list', d:'Sales'}, {g:'In progress', l:'Onboarding emails', d:'Growth'}, {g:'In progress', l:'Usage dashboard', d:'Data'}, {g:'Review', l:'Security audit fixes', d:'Platform'}, {g:'Done', l:'New checkout flow', d:'Product'}, {g:'Done', l:'Help centre refresh', d:'Support'}]},
  ai:'cards in 3-5 columns (To do, In progress, Review, Done): l = task (max 5 words), d = owner or team.',
  designs:[
    {id:'kb1', name:'Kanban · board', draw:function(K, Z, D){
      var G = DG.groups(D, D.cols && D.cols.length ? D.cols : KB), nc = G.length, gap = 16, cw = (Z.w - gap*(nc - 1)) / nc, tags = [];
      D.rows.forEach(function(r){ if (r.d && tags.indexOf(r.d) < 0) tags.push(r.d); });
      G.forEach(function(g, i){
        var x = Z.x + i*(cw + gap);
        K.rect(x, Z.y, cw, Z.h, {fill:K.cardc, r:14});
        K.circ(x + 24, Z.y + 28, 7, {pi:i}); K.t(x + 40, Z.y + 12, cw - 100, 32, g.n, {max:18, min:10, wt:800, font:'head', color:K.tc, lines:1});
        K.rect(x + cw - 50, Z.y + 16, 34, 24, {pi:i, soft:0.25, r:12}); K.t(x + cw - 50, Z.y + 16, 34, 24, String(g.items.length), {size:13, wt:900, pi:i, align:'c', lines:1});
        var cards = D.rows.filter(function(r){ return g.items.indexOf(r.l) >= 0 && (r.g === g.n || DG.groups(D, D.cols && D.cols.length ? D.cols : KB)[i].items.indexOf(r.l) >= 0); });
        var chh = Math.min(96, (Z.h - 64) / Math.max(1, g.items.length) - 10);
        g.items.forEach(function(it, k){
          var r = cards.filter(function(c){ return c.l === it; })[0] || {}, y = Z.y + 58 + k*(chh + 10), ti = tags.indexOf(r.d);
          K.rect(x + 12, y, cw - 24, chh, {fill:K.bg, r:10, shadow:0.1});
          K.rect(x + 12, y + 10, 4, chh - 20, {pi:i, r:2});
          K.t(x + 28, y + 8, cw - 52, chh*0.55, it, {max:17, min:9, wt:700, color:K.tc, va:'t', lines:2});
          if (r.d) { K.rect(x + 28, y + chh - 32, Math.min(cw - 60, String(r.d).length*8 + 22), 22, {pi:ti >= 0 ? ti + 1 : 1, soft:0.2, r:11}); K.t(x + 36, y + chh - 32, cw - 76, 22, r.d, {size:12, wt:700, pi:ti >= 0 ? ti + 1 : 1, lines:1}); }
        });
      });
    }},
    {id:'kb2', name:'Kanban · columns with counts', draw:function(K, Z, D){
      var G = DG.groups(D, D.cols && D.cols.length ? D.cols : KB), nc = G.length, gap = 14, cw = (Z.w - gap*(nc - 1)) / nc, tot = D.rows.length || 1;
      G.forEach(function(g, i){
        var x = Z.x + i*(cw + gap), sf = 0.35 + 0.65*(i + 1)/nc;
        K.rect(x, Z.y, cw, 96, {pi:0, soft:sf, r:14});
        K.tOn(x + 20, Z.y + 10, cw - 40, 30, g.n.toUpperCase(), 0, {max:14, min:9, wt:800, ls:0.08, lines:1}, sf);
        K.tOn(x + 20, Z.y + 40, cw - 40, 48, String(g.items.length), 0, {max:40, min:14, wt:900, font:'head', lines:1}, sf);
        K.tOn(x + cw - 110, Z.y + 52, 90, 30, Math.round(g.items.length/tot*100) + '%', 0, {size:15, wt:800, align:'r', lines:1}, sf);
        K.list(x + 16, Z.y + 116, cw - 32, Z.h - 124, g.items, {max:17, min:9, pi:0, gap:14});
      });
    }}
  ]});
})();

/* DS Diagrams — Process & flow: process steps, flowchart, cycle, workflow */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('process', 'Process & Flow', '🔄', 'Process steps, flowcharts, cycles, workflows');
var LD = [{k:'l', n:'Step'}, {k:'d', n:'Description'}];
var STEPS = {rows:[{l:'Discover', d:'Understand customer needs and market gaps'}, {l:'Define', d:'Set goals, scope and success metrics'}, {l:'Design', d:'Shape the solution with users'}, {l:'Deliver', d:'Build, test and launch in sprints'}, {l:'Improve', d:'Measure results and iterate'}]};

DG.type({id:'process', cat:'process', name:'Process Flow', fields:LD, min:2, max:8, sample:STEPS,
  ai:'3-7 steps in order: l = step name (1-3 words), d = one short sentence.',
  designs:[
    {id:'pr1', name:'Process · chevrons', draw:function(K, Z, D){
      var n = D.rows.length, ov = 28, w = (Z.w + ov*(n - 1)) / n, h = Math.min(170, Z.h*0.42);
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w - ov), sf = 0.45 + 0.55*(i + 1)/n;
        K.shape(i ? 'chevron' : 'homePlate', x, Z.y, w, h, {pi:0, soft:sf});
        K.tOn(x + (i ? ov + 10 : 22), Z.y + h*0.14, 60, h*0.4, String(i + 1).padStart(2, '0'), 0, {max:30, min:12, font:'head', wt:900, lines:1}, sf);
        K.tOn(x + (i ? ov + 10 : 22), Z.y + h*0.52, w - ov - 46, h*0.36, r.l, 0, {max:20, min:10, wt:800, lines:1}, sf);
        K.t(x + (i ? ov/2 : 0) + 10, Z.y + h + 30, w - ov - 20, Z.h - h - 34, r.d, {max:21, min:10, color:K.txt, va:'t', lh:1.35});
        K.rect(x + (i ? ov/2 : 0) + 10, Z.y + h + 12, 36, 4, {pi:0, soft:sf, r:2});
      });
    }},
    {id:'pr2', name:'Process · numbered nodes', draw:function(K, Z, D){
      var n = D.rows.length, step = Z.w / n, R = Math.min(64, step*0.28), cy = Z.y + Math.max(R + 20, Z.h*0.22);
      K.line(Z.x + step/2, cy, Z.x + Z.w - step/2, cy, {color:K.ln, lw:3, dash:true});
      D.rows.forEach(function(r, i){
        var cx = Z.x + step*i + step/2;
        K.circ(cx, cy, R + 10, {pi:i, soft:0.2}); K.circ(cx, cy, R, {pi:i});
        K.tOn(cx - R, cy - R, 2*R, 2*R, String(i + 1), i, {size:R*0.8, wt:900, align:'c', font:'head'});
        K.t(cx - step/2 + 10, cy + R + 28, step - 20, 34, r.l, {max:22, min:11, font:'head', wt:800, color:K.tc, align:'c', lines:1});
        K.t(cx - step/2 + 14, cy + R + 70, step - 28, Z.y + Z.h - cy - R - 74, r.d, {max:20, min:10, color:K.mc, align:'c', va:'t', lh:1.35});
      });
    }},
    {id:'pr3', name:'Process · vertical steps', draw:function(K, Z, D){
      var n = D.rows.length, gap = 12, h = (Z.h - gap*(n - 1)) / n, bw = Math.min(320, Z.w*0.3);
      D.rows.forEach(function(r, i){
        var y = Z.y + i*(h + gap), sf = 0.45 + 0.55*(i + 1)/n;
        K.rect(Z.x, y, bw, h, {pi:0, soft:sf, r:12});
        K.tOn(Z.x + 18, y, 60, h, String(i + 1).padStart(2, '0'), 0, {max:Math.min(34, h*0.6), min:12, font:'head', wt:900, lines:1}, sf);
        K.tOn(Z.x + 80, y, bw - 96, h, r.l, 0, {max:21, min:10, wt:800, lines:1}, sf);
        K.poly([[Z.x + bw, y + h*0.3], [Z.x + bw + 18, y + h/2], [Z.x + bw, y + h*0.7]], {pi:0, soft:sf});
        var c = K.card(Z.x + bw + 30, y, Z.w - bw - 30, h, {});
        K.t(Z.x + bw + 54, y, Z.w - bw - 78, h, r.d, {max:19, min:10, color:c.tc, lh:1.3});
      });
    }},
    {id:'pr4', name:'Process · ascending stairs', draw:function(K, Z, D){
      var n = D.rows.length, gap = 12, w = (Z.w - gap*(n - 1)) / n, stepH = (Z.h - 150) / n;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w + gap), h = 150 + stepH*(i + 1) - 40, y = Z.y + Z.h - h;
        K.rect(x, y, w, h, {pi:i, soft:0.16, r:12});
        K.rect(x, y, w, 8, {pi:i, r:4});
        K.t(x + 18, y + 20, w - 36, 44, String(i + 1).padStart(2, '0'), {max:36, min:14, font:'head', wt:900, pi:i, lines:1});
        K.t(x + 18, y + 68, w - 36, 32, r.l, {max:20, min:10, font:'head', wt:800, color:K.tc, lines:2, va:'t'});
        K.t(x + 18, y + 108, w - 36, h - 120, r.d, {max:16, min:9, color:K.mc, va:'t', lh:1.35});
      });
    }},
    {id:'pr5', name:'Process · cards + arrows', draw:function(K, Z, D){
      var n = D.rows.length, ag = 40, w = (Z.w - ag*(n - 1)) / n, h = Math.min(Z.h, 330), y = Z.y + (Z.h - h)/2;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w + ag), c = K.card(x, y, w, h, {hl:i === n - 1});
        K.t(x + 22, y + 22, w - 44, 20, 'STEP ' + (i + 1), {size:13, wt:800, color:i === n - 1 ? c.mc : K.col(0), ls:0.1, lines:1});
        K.t(x + 22, y + 48, w - 44, 60, r.l, {max:24, min:11, font:'head', wt:900, color:c.tc, va:'t', lines:2});
        K.rect(x + 22, y + 116, 40, 4, {pi:0, r:2});
        K.t(x + 22, y + 136, w - 44, h - 152, r.d, {max:17, min:9, color:c.mc, va:'t', lh:1.35});
        if (i < n - 1) { K.circ(x + w + ag/2, y + h/2, 16, {pi:0}); K.arrow(x + w + ag/2 - 7, y + h/2, x + w + ag/2 + 8, y + h/2, {color:u_on(K), lw:2.5, head:7}); }
      });
      function u_on(K){ return DG.u.on(K.col(0)); }
    }}
  ]});

/* ---------------- Flowchart ---------------- */
DG.type({id:'flowchart', cat:'process', name:'Flowchart', fields:[{k:'l', n:'Box text'}, {k:'g', n:'Shape (Start / Step / Decision / End)'}, {k:'d', n:'Decision "No" goes to (box text)'}], min:3, max:10,
  sample:{rows:[{l:'Order received', g:'Start'}, {l:'Check stock', g:'Step'}, {l:'In stock?', g:'Decision', d:'Order from supplier'}, {l:'Pick and pack', g:'Step'}, {l:'Ship to customer', g:'Step'}, {l:'Order complete', g:'End'}, {l:'Order from supplier', g:'Step'}]},
  ai:'4-8 boxes in order: g = Start, Step, Decision or End; for a Decision, d = the text of the box its "No" answer goes to (add that box at the end).',
  designs:[
    {id:'fc1', name:'Flowchart · left to right', draw:function(K, Z, D){ flow(K, Z, D, false); }},
    {id:'fc2', name:'Flowchart · top to bottom', draw:function(K, Z, D){ flow(K, Z, D, true); }}
  ]});
function kind(g){ g = String(g || '').toLowerCase(); return /dec|\?/.test(g) ? 'dec' : (/start|begin/.test(g) ? 'start' : (/end|finish|stop/.test(g) ? 'end' : 'step')); }
function flow(K, Z, D, vert){
  var R = D.rows, byL = {}, main = [], side = [];
  R.forEach(function(r){ byL[r.l] = r; });
  var targets = {}; R.forEach(function(r){ if (kind(r.g) === 'dec' && r.d && byL[r.d]) targets[r.d] = r.l; });
  R.forEach(function(r){ if (targets[r.l]) side.push(r); else main.push(r); });
  var n = main.length, pos = {};
  var bw = vert ? Math.min(300, Z.w*0.34) : Math.min(210, (Z.w - 30*(n - 1)) / n), bh = vert ? Math.min(66, (Z.h - 18*(n - 1)) / n) : 86;
  var gap = vert ? (Z.h - bh*n) / Math.max(1, n - 1) : (Z.w - bw*n) / Math.max(1, n - 1);
  main.forEach(function(r, i){ pos[r.l] = vert ? {x:Z.x + (Z.w*0.42) - bw/2, y:Z.y + i*(bh + gap)} : {x:Z.x + i*(bw + gap), y:Z.y + Z.h*0.3 - bh/2}; });
  side.forEach(function(r){ var src = pos[targets[r.l]]; pos[r.l] = vert ? {x:src.x + bw + 120, y:src.y} : {x:src.x, y:src.y + bh + 120}; });
  main.forEach(function(r, i){ if (i === n - 1) return; var a = pos[r.l], b = pos[main[i + 1].l];
    if (vert) K.arrow(a.x + bw/2, a.y + bh, b.x + bw/2, b.y - 2, {color:K.mc, lw:2, head:10});
    else K.arrow(a.x + bw, a.y + bh/2, b.x - 2, b.y + bh/2, {color:K.mc, lw:2, head:10});
    if (kind(r.g) === 'dec') K.t(vert ? a.x + bw/2 + 8 : a.x + bw + 4, vert ? a.y + bh : a.y + bh/2 - 26, 50, 22, 'Yes', {size:13, wt:800, fix:K.good(), lines:1}); });
  R.forEach(function(r){ if (kind(r.g) !== 'dec' || !r.d || !pos[r.d]) return; var a = pos[r.l], b = pos[r.d];
    if (vert) { K.arrow(a.x + bw, a.y + bh/2, b.x - 2, b.y + bh/2, {fix:K.bad(), color:K.bad(), lw:2, head:10}); K.t(a.x + bw + 8, a.y + bh/2 - 26, 50, 22, 'No', {size:13, wt:800, fix:K.bad(), lines:1}); }
    else { K.arrow(a.x + bw/2, a.y + bh, b.x + bw/2, b.y - 2, {fix:K.bad(), color:K.bad(), lw:2, head:10}); K.t(a.x + bw/2 + 8, a.y + bh + 8, 50, 22, 'No', {size:13, wt:800, fix:K.bad(), lines:1}); } });
  R.forEach(function(r, i){
    var p = pos[r.l], k = kind(r.g); if (!p) return;
    if (k === 'dec') { K.shape('diamond', p.x + bw*0.08, p.y - bh*0.18, bw*0.84, bh*1.36, {pi:1}); K.tOn(p.x + bw*0.22, p.y, bw*0.56, bh, r.l, 1, {max:16, min:8, wt:800, align:'c', lines:3}); }
    else if (k === 'start' || k === 'end') { K.rect(p.x, p.y, bw, bh, {pi:0, r:bh/2}); K.tOn(p.x + 14, p.y, bw - 28, bh, r.l, 0, {max:17, min:8, wt:800, align:'c', lines:2}); }
    else { K.rect(p.x, p.y, bw, bh, {fill:K.cardc, r:10, line:K.col(0), lw:2}); K.t(p.x + 12, p.y, bw - 24, bh, r.l, {max:17, min:8, wt:700, color:K.tc, align:'c', lines:3}); }
  });
}

/* ---------------- Cycle ---------------- */
DG.type({id:'cycle', cat:'process', name:'Cycle Diagram', fields:LD, min:3, max:8,
  sample:{rows:[{l:'Plan', d:'Set objectives and priorities'}, {l:'Do', d:'Run the pilot in one region'}, {l:'Check', d:'Measure against targets'}, {l:'Act', d:'Scale what works, fix what does not'}], o:{center:'Continuous improvement'}},
  ai:'3-6 stages of a repeating cycle: l = stage (1-2 words), d = one short sentence; o.center = a 2-3 word name for the cycle.',
  designs:[
    {id:'cy1', name:'Cycle · ring segments', draw:function(K, Z, D){
      var n = D.rows.length, R = Math.min(Z.h/2 - 6, Z.w*0.2), cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, sw = 360 / n, gap = 3;
      D.rows.forEach(function(r, i){
        var a0 = i*sw + gap, a1 = (i + 1)*sw - gap;
        K.sector(cx, cy, R*0.62, R, a0, a1, {pi:i});
        var p = DG.pol(cx, cy, R*0.81, (a0 + a1)/2); K.tOn(p[0] - 30, p[1] - 18, 60, 36, String(i + 1), i, {size:22, wt:900, align:'c', font:'head'});
        var mid = (a0 + a1)/2, q = DG.pol(cx, cy, R + 34, mid), right = mid < 180, bw = Z.w/2 - R - 60;
        var bx = right ? q[0] : q[0] - bw, by = q[1] - 40;
        K.t(bx, by, bw, 32, r.l, {max:22, min:11, font:'head', wt:900, pi:i, align:right ? 'l' : 'r', lines:1});
        K.t(bx, by + 34, bw, 56, r.d, {max:16, min:9, color:K.mc, align:right ? 'l' : 'r', va:'t', lines:3});
      });
      K.t(cx - R*0.55, cy - R*0.3, R*1.1, R*0.6, (D.o && D.o.center) || '', {max:22, min:10, font:'head', wt:900, color:K.tc, align:'c', lines:3});
    }},
    {id:'cy2', name:'Cycle · nodes + arrows', draw:function(K, Z, D){
      var n = D.rows.length, R = Math.min(Z.h/2 - 58, Z.w*0.2), cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, nr = Math.min(62, R*0.42);
      K.circ(cx, cy, R, {line:K.ln, lw:2, dash:true});
      for (var i = 0; i < n; i++) { var a = 360*i/n + 360/n/2, p = DG.pol(cx, cy, R, a), p2 = DG.pol(cx, cy, R, a + 6); K.arrow(p[0], p[1], p2[0], p2[1], {color:K.mc, lw:2, head:12}); }
      D.rows.forEach(function(r, i){
        var a = 360*i/n, p = DG.pol(cx, cy, R, a);
        K.circ(p[0], p[1], nr + 8, {fill:K.bg}); K.circ(p[0], p[1], nr, {pi:i});
        K.tOn(p[0] - nr + 6, p[1] - nr*0.5, 2*nr - 12, nr, r.l, i, {max:18, min:8, wt:800, align:'c', lines:2});
        var right = p[0] >= cx - 10, bw = Z.w/2 - R - nr - 40, bx = right ? p[0] + nr + 18 : p[0] - nr - 18 - bw;
        if (Math.abs(p[0] - cx) < 10) bw = Math.min(bw, Z.w*0.3);
        K.t(bx, p[1] - 34, bw, 68, r.d, {max:17, min:9, color:K.mc, align:right ? 'l' : 'r', lines:3});
      });
      K.t(cx - R*0.6, cy - 40, R*1.2, 80, (D.o && D.o.center) || '', {max:24, min:10, font:'head', wt:900, color:K.tc, align:'c', lines:3});
    }}
  ]});

/* ---------------- Workflow ---------------- */
DG.type({id:'workflow', cat:'process', name:'Workflow Diagram', fields:[{k:'l', n:'Task'}, {k:'g', n:'Owner / role'}, {k:'d', n:'Output'}], min:3, max:12,
  sample:{rows:[{l:'Submit request', g:'Employee', d:'Request form'}, {l:'Manager approval', g:'Manager', d:'Approved request'}, {l:'Budget check', g:'Finance', d:'Budget code'}, {l:'Raise purchase order', g:'Procurement', d:'PO number'}, {l:'Receive goods', g:'Warehouse', d:'Delivery note'}, {l:'Pay invoice', g:'Finance', d:'Payment'}]},
  ai:'4-10 tasks in order: l = task (max 4 words), g = owner role, d = output of the task.',
  designs:[
    {id:'wk1', name:'Workflow · snake grid', draw:function(K, Z, D){
      var n = D.rows.length, cols = n <= 4 ? n : Math.ceil(n / 2), rows = Math.ceil(n / cols), gx = 60, gy = 50, w = (Z.w - gx*(cols - 1)) / cols, h = Math.min(170, (Z.h - gy*(rows - 1)) / rows), roles = [];
      D.rows.forEach(function(r){ if (roles.indexOf(r.g) < 0) roles.push(r.g); });
      var P = D.rows.map(function(r, i){ var row = Math.floor(i / cols), k = i % cols; if (row % 2) k = cols - 1 - k; return {x:Z.x + k*(w + gx), y:Z.y + row*(h + gy) + (Z.h - rows*h - gy*(rows - 1))/2}; });
      D.rows.forEach(function(r, i){ if (i === n - 1) return; var a = P[i], b = P[i + 1];
        if (Math.abs(a.y - b.y) < 1) K.arrow(a.x < b.x ? a.x + w : a.x, a.y + h/2, a.x < b.x ? b.x - 2 : b.x + w + 2, b.y + h/2, {color:K.mc, lw:2.4, head:11});
        else K.arrow(a.x + w/2, a.y + h, b.x + w/2, b.y - 2, {color:K.mc, lw:2.4, head:11}); });
      D.rows.forEach(function(r, i){
        var p = P[i], ri = roles.indexOf(r.g), c = K.card(p.x, p.y, w, h, {});
        K.rect(p.x, p.y, 6, h, {pi:ri, r:3});
        K.circ(p.x + 38, p.y + 34, 17, {pi:ri}); K.tOn(p.x + 21, p.y + 17, 34, 34, String(i + 1), ri, {size:14, wt:900, align:'c'});
        K.t(p.x + 64, p.y + 18, w - 80, 32, (r.g || '').toUpperCase(), {max:13, min:8, wt:800, pi:ri, ls:0.06, lines:1});
        K.t(p.x + 22, p.y + 60, w - 40, h*0.36, r.l, {max:21, min:10, font:'head', wt:800, color:c.tc, va:'t', lines:2});
        if (r.d) K.t(p.x + 22, p.y + h - 38, w - 40, 26, '→ ' + r.d, {max:15, min:8, color:c.mc, lines:1});
      });
    }},
    {id:'wk2', name:'Workflow · roles + hand-offs', draw:function(K, Z, D){
      var n = D.rows.length, roles = []; D.rows.forEach(function(r){ if (roles.indexOf(r.g) < 0) roles.push(r.g); });
      var lab = 170, rh = Z.h / roles.length, cw = (Z.w - lab) / n, bw = Math.min(170, cw - 24), bh = Math.min(80, rh - 20);
      roles.forEach(function(ro, i){ var y = Z.y + rh*i; K.rect(Z.x, y + 3, Z.w, rh - 6, {pi:i, soft:0.08, r:10}); K.rect(Z.x, y + 3, lab - 12, rh - 6, {pi:i, soft:0.22, r:10}); K.t(Z.x + 14, y, lab - 34, rh, ro, {max:17, min:9, wt:800, pi:i, lines:2}); });
      var P = D.rows.map(function(r, i){ var ri = roles.indexOf(r.g); return {x:Z.x + lab + cw*i + (cw - bw)/2, y:Z.y + rh*ri + (rh - bh)/2, ri:ri}; });
      P.forEach(function(p, i){ if (i === n - 1) return; var q = P[i + 1];
        if (q.y === p.y) K.arrow(p.x + bw, p.y + bh/2, q.x - 2, q.y + bh/2, {color:K.mc, lw:2, head:9});
        else { var mx = p.x + bw + (q.x - p.x - bw)/2; K.line(p.x + bw, p.y + bh/2, mx, p.y + bh/2, {color:K.mc, lw:2}); K.line(mx, p.y + bh/2, mx, q.y + bh/2, {color:K.mc, lw:2}); K.arrow(mx, q.y + bh/2, q.x - 2, q.y + bh/2, {color:K.mc, lw:2, head:9}); } });
      D.rows.forEach(function(r, i){ var p = P[i]; K.rect(p.x, p.y, bw, bh, {pi:p.ri, r:10}); K.tOn(p.x + 10, p.y, bw - 20, bh, r.l, p.ri, {max:16, min:8, wt:800, align:'c', lines:3}); });
    }}
  ]});
})();

/* DS Diagrams — Operations & customer: swimlane, decision tree, SIPOC, value stream map, customer journey */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('ops', 'Operations & Journey', '🏭', 'Swimlanes, decision trees, SIPOC, value stream, customer journey');
var fmt = DG.fmt;

/* ---------- tree helper: rows {l, p} -> roots with children, leaf counts, depth ---------- */
DG.tree = function(rows){
  var by = {}, roots = [];
  rows.forEach(function(r, i){ if (r.l && !by[r.l]) by[r.l] = {r:r, l:r.l, kids:[], i:i}; });
  rows.forEach(function(r){ var n = by[r.l]; if (!n || n._placed) return; n._placed = 1; var par = r.p && by[r.p] && r.p !== r.l ? by[r.p] : null; if (par) par.kids.push(n); else roots.push(n); });
  var maxd = 0;
  function walk(n, d){ n.d = d; maxd = Math.max(maxd, d); n.leaves = n.kids.length ? 0 : 1; n.kids.forEach(function(k){ walk(k, d + 1); n.leaves += k.leaves; }); }
  roots.forEach(function(n){ walk(n, 0); });
  return {roots:roots, depth:maxd + 1, leaves:roots.reduce(function(a, n){ return a + n.leaves; }, 0)};
};
/* place nodes: along = leaf axis position (0..1), level = depth */
DG.treePlace = function(T){
  var i = 0, out = [];
  function place(n){ if (!n.kids.length) { n.a = (i + 0.5) / T.leaves; i++; } else { n.kids.forEach(place); n.a = (n.kids[0].a + n.kids[n.kids.length - 1].a) / 2; } out.push(n); }
  T.roots.forEach(place);
  return out;
};

/* ---------------- Swimlane ---------------- */
DG.type({id:'swimlane', cat:'ops', name:'Swimlane Diagram', fields:[{k:'g', n:'Lane'}, {k:'l', n:'Step'}, {k:'s', n:'Order #', num:1}], min:3, max:14,
  sample:{rows:[{g:'Customer', l:'Places order', s:1}, {g:'Sales', l:'Confirms order', s:2}, {g:'Finance', l:'Checks credit', s:3}, {g:'Warehouse', l:'Picks & packs', s:4}, {g:'Logistics', l:'Ships parcel', s:5}, {g:'Customer', l:'Receives goods', s:6}, {g:'Finance', l:'Sends invoice', s:6}]},
  ai:'4-10 steps: g = lane (role or department), l = step (max 3 words), s = order number (steps can share a number when parallel).',
  designs:[
    {id:'sl1', name:'Swimlane · horizontal lanes', draw:function(K, Z, D){
      var lanes = []; D.rows.forEach(function(r){ if (lanes.indexOf(r.g) < 0) lanes.push(r.g); });
      var R = D.rows.map(function(r, i){ return {r:r, s:r.s != null ? r.s : i + 1}; }).sort(function(a, b){ return a.s - b.s; });
      var orders = []; R.forEach(function(o){ if (orders.indexOf(o.s) < 0) orders.push(o.s); });
      var lab = 170, lh = Z.h / lanes.length, cw = (Z.w - lab) / orders.length, bw = Math.min(170, cw - 30), bh = Math.min(72, lh - 22);
      lanes.forEach(function(ln, i){ var y = Z.y + lh*i; K.rect(Z.x, y + 2, Z.w, lh - 4, {fill:i % 2 ? K.bg : K.cardc, r:8}); K.rect(Z.x, y + 2, lab - 14, lh - 4, {pi:i, r:8}); K.tOn(Z.x + 14, y, lab - 42, lh, ln, i, {max:17, min:9, wt:800, lines:2}); });
      var P = R.map(function(o){ var li = lanes.indexOf(o.r.g), oi = orders.indexOf(o.s), same = R.filter(function(q){ return q.s === o.s && lanes.indexOf(q.r.g) === li; }), k = same.indexOf(o);
        return {o:o, li:li, x:Z.x + lab + cw*oi + (cw - bw)/2, y:Z.y + lh*li + (lh - bh)/2 + k*6}; });
      for (var i = 0; i < P.length; i++) for (var j = i + 1; j < P.length; j++) {
        if (orders.indexOf(P[j].o.s) !== orders.indexOf(P[i].o.s) + 1) continue;
        var a = P[i], b = P[j];
        if (a.y === b.y) K.arrow(a.x + bw, a.y + bh/2, b.x - 2, b.y + bh/2, {color:K.mc, lw:2, head:9});
        else { var mx = b.x - 16; K.line(a.x + bw, a.y + bh/2, mx, a.y + bh/2, {color:K.mc, lw:2}); K.line(mx, a.y + bh/2, mx, b.y + bh/2, {color:K.mc, lw:2}); K.arrow(mx, b.y + bh/2, b.x - 2, b.y + bh/2, {color:K.mc, lw:2, head:9}); }
      }
      P.forEach(function(p){ K.rect(p.x, p.y, bw, bh, {fill:K.bg, r:10, line:K.col(p.li), lw:2.5, shadow:0.08}); K.t(p.x + 10, p.y, bw - 20, bh, p.o.r.l, {max:16, min:8, wt:700, color:K.tc, align:'c', lines:3}); });
    }},
    {id:'sl2', name:'Swimlane · vertical lanes', draw:function(K, Z, D){
      var lanes = []; D.rows.forEach(function(r){ if (lanes.indexOf(r.g) < 0) lanes.push(r.g); });
      var R = D.rows.map(function(r, i){ return {r:r, s:r.s != null ? r.s : i + 1}; }).sort(function(a, b){ return a.s - b.s; });
      var orders = []; R.forEach(function(o){ if (orders.indexOf(o.s) < 0) orders.push(o.s); });
      var top = 56, lw = Z.w / lanes.length, rh = (Z.h - top) / orders.length, bw = Math.min(200, lw - 30), bh = Math.min(64, rh - 18);
      lanes.forEach(function(ln, i){ var x = Z.x + lw*i; K.rect(x + 3, Z.y, lw - 6, Z.h, {fill:i % 2 ? K.bg : K.cardc, r:10}); K.rect(x + 3, Z.y, lw - 6, top - 8, {pi:i, r:10}); K.tOn(x + 10, Z.y, lw - 20, top - 8, ln, i, {max:17, min:9, wt:800, align:'c', lines:2}); });
      var P = R.map(function(o){ var li = lanes.indexOf(o.r.g), oi = orders.indexOf(o.s); return {o:o, li:li, x:Z.x + lw*li + (lw - bw)/2, y:Z.y + top + rh*oi + (rh - bh)/2}; });
      for (var i = 0; i < P.length; i++) for (var j = 0; j < P.length; j++) {
        if (orders.indexOf(P[j].o.s) !== orders.indexOf(P[i].o.s) + 1) continue;
        var a = P[i], b = P[j];
        if (a.x === b.x) K.arrow(a.x + bw/2, a.y + bh, b.x + bw/2, b.y - 2, {color:K.mc, lw:2, head:9});
        else { var my = b.y - 12; K.line(a.x + bw/2, a.y + bh, a.x + bw/2, my, {color:K.mc, lw:2}); K.line(a.x + bw/2, my, b.x + bw/2, my, {color:K.mc, lw:2}); K.arrow(b.x + bw/2, my, b.x + bw/2, b.y - 2, {color:K.mc, lw:2, head:8}); }
      }
      P.forEach(function(p){ K.rect(p.x, p.y, bw, bh, {fill:K.bg, r:10, line:K.col(p.li), lw:2.5, shadow:0.08}); K.t(p.x + 10, p.y, bw - 20, bh, p.o.r.l, {max:16, min:8, wt:700, color:K.tc, align:'c', lines:3}); });
    }}
  ]});

/* ---------------- Decision tree ---------------- */
DG.type({id:'dtree', cat:'ops', name:'Decision Tree', fields:[{k:'l', n:'Node'}, {k:'p', n:'Parent node'}, {k:'d', n:'Branch label (e.g. Yes / No)'}], min:3, max:15,
  sample:{rows:[{l:'Expand to new market?', p:''}, {l:'Build local team', p:'Expand to new market?', d:'Yes · high demand'}, {l:'Partner with distributor', p:'Expand to new market?', d:'Yes · low demand'}, {l:'Stay focused on home', p:'Expand to new market?', d:'No'}, {l:'Hire 20 people · $2M', p:'Build local team', d:'Year 1'}, {l:'Margin share 25%', p:'Partner with distributor', d:'Year 1'}, {l:'Grow 12% organically', p:'Stay focused on home', d:'Year 1'}]},
  ai:'one root question, then options (p = parent node text, d = branch label like Yes/No) and outcomes; 2-3 levels deep, max 12 nodes.',
  designs:[
    {id:'dt1', name:'Decision tree · left to right', draw:function(K, Z, D){ drawTree(K, Z, D, true); }},
    {id:'dt2', name:'Decision tree · top down', draw:function(K, Z, D){ drawTree(K, Z, D, false); }}
  ]});
function drawTree(K, Z, D, horiz){
  var T = DG.tree(D.rows), N = DG.treePlace(T), L = T.depth;
  var bw = horiz ? Math.min(250, (Z.w - 70*(L - 1)) / L) : Math.min(210, Z.w / Math.max(1, T.leaves) - 20), bh = horiz ? Math.min(78, Z.h / Math.max(1, T.leaves) - 16) : Math.min(88, (Z.h - 50*(L - 1)) / L);
  function pos(n){ return horiz ? {x:Z.x + (L > 1 ? n.d*(Z.w - bw)/(L - 1) : 0), y:Z.y + n.a*Z.h - bh/2} : {x:Z.x + n.a*Z.w - bw/2, y:Z.y + (L > 1 ? n.d*(Z.h - bh)/(L - 1) : 0)}; }
  N.forEach(function(n){ var p = pos(n); n.x = p.x; n.y = p.y; });
  N.forEach(function(n){ n.kids.forEach(function(k){
    var d;
    if (horiz) { var x1 = n.x + bw, y1 = n.y + bh/2, x2 = k.x, y2 = k.y + bh/2, mx = (x1 + x2)/2; d = [['M', x1, y1], ['C', mx, y1, mx, y2, x2, y2]]; K.path(d, {stroke:K.mc, lw:2}); if (k.r.d) K.t(mx + 4, y2 - 26, x2 - mx - 8, 22, k.r.d, {max:13, min:8, wt:800, pi:0, lines:1}); }
    else { var ax = n.x + bw/2, ay = n.y + bh, bx = k.x + bw/2, by = k.y, my = (ay + by)/2; d = [['M', ax, ay], ['C', ax, my, bx, my, bx, by]]; K.path(d, {stroke:K.mc, lw:2}); if (k.r.d) K.t(bx - 80, my - 4, 160, 22, k.r.d, {max:13, min:8, wt:800, pi:0, align:'c', lines:1}); }
  }); });
  N.forEach(function(n){
    if (n.d === 0) { K.rect(n.x, n.y, bw, bh, {pi:0, r:12}); K.tOn(n.x + 12, n.y, bw - 24, bh, n.l, 0, {max:18, min:9, wt:800, align:'c', lines:3}); }
    else if (!n.kids.length) { K.rect(n.x, n.y, bw, bh, {pi:0, soft:0.18, r:bh/2}); K.t(n.x + 16, n.y, bw - 32, bh, n.l, {max:16, min:8, wt:700, color:K.tc, align:'c', lines:3}); }
    else { K.rect(n.x, n.y, bw, bh, {fill:K.cardc, r:12, line:K.col(0), lw:2}); K.t(n.x + 12, n.y, bw - 24, bh, n.l, {max:16, min:8, wt:800, color:K.tc, align:'c', lines:3}); }
  });
}
DG.drawTree = drawTree;

/* ---------------- SIPOC ---------------- */
var SP = ['Suppliers', 'Inputs', 'Process', 'Outputs', 'Customers'];
DG.type({id:'sipoc', cat:'ops', name:'SIPOC Diagram', fields:[{k:'g', n:'Column', group:1}, {k:'l', n:'Item'}], groups:SP, min:5, max:30,
  sample:{rows:[{g:'Suppliers', l:'Raw material vendors'}, {g:'Suppliers', l:'Logistics partner'}, {g:'Inputs', l:'Components'}, {g:'Inputs', l:'Customer orders'}, {g:'Inputs', l:'Production plan'}, {g:'Process', l:'Receive order'}, {g:'Process', l:'Assemble product'}, {g:'Process', l:'Quality check'}, {g:'Process', l:'Ship'}, {g:'Outputs', l:'Finished product'}, {g:'Outputs', l:'Invoice'}, {g:'Customers', l:'Retail stores'}, {g:'Customers', l:'Online buyers'}]},
  ai:'items for the 5 columns exactly named Suppliers, Inputs, Process, Outputs, Customers; 2-4 short items each (Process items are steps in order).',
  designs:[
    {id:'si1', name:'SIPOC · five columns', draw:function(K, Z, D){
      var G = DG.groups(D, SP), gap = 12, w = (Z.w - gap*4) / 5;
      G.forEach(function(g, i){
        var x = Z.x + i*(w + gap), hl = i === 2;
        K.shape(i ? 'chevron' : 'homePlate', x - (i ? 8 : 0), Z.y, w + (i ? 16 : 8), 90, {pi:0, soft:0.35 + 0.13*i});
        K.tOn(x + 14, Z.y, w - 20, 50, g.n.charAt(0), 0, {size:36, wt:900, font:'head', align:'c', lh:1}, 0.35 + 0.13*i);
        K.tOn(x + 14, Z.y + 48, w - 20, 34, g.n, 0, {max:17, min:9, wt:800, align:'c', lines:1}, 0.35 + 0.13*i);
        var c = K.card(x, Z.y + 104, w, Z.h - 104, {hl:hl});
        if (hl) K.list(x + 18, Z.y + 124, w - 36, Z.h - 140, g.items, {max:18, min:9, num:true, pi:0, color:c.tc, gap:12});
        else K.list(x + 18, Z.y + 124, w - 36, Z.h - 140, g.items, {max:18, min:9, pi:0, color:c.tc, gap:12});
      });
    }},
    {id:'si2', name:'SIPOC · flow band', draw:function(K, Z, D){
      var G = DG.groups(D, SP), gap = 14, w = (Z.w - gap*4) / 5, bandY = Z.y + Z.h*0.42, bandH = 70;
      K.rect(Z.x, bandY, Z.w, bandH, {pi:0, soft:0.14, r:35});
      K.arrow(Z.x + 30, bandY + bandH/2, Z.x + Z.w - 20, bandY + bandH/2, {pi:0, lw:3, head:16});
      G.forEach(function(g, i){
        var x = Z.x + i*(w + gap), cx = x + w/2;
        K.circ(cx, bandY + bandH/2, 40, {pi:i === 2 ? 0 : 0, soft:i === 2 ? null : 0.55}); K.circ(cx, bandY + bandH/2, 32, {pi:0});
        K.tOn(cx - 32, bandY + bandH/2 - 32, 64, 64, g.n.charAt(0), 0, {size:28, wt:900, align:'c', font:'head'});
        K.t(x, bandY - 64, w, 34, g.n, {max:20, min:10, font:'head', wt:900, color:K.tc, align:'c', lines:1});
        K.t(x, bandY - 32, w, 24, ['Who provides', 'What goes in', 'What we do', 'What comes out', 'Who receives'][i], {size:13, color:K.mc, align:'c', lines:1});
        var c = K.card(x, bandY + bandH + 26, w, Z.y + Z.h - bandY - bandH - 26, {});
        K.list(x + 16, bandY + bandH + 42, w - 32, Z.y + Z.h - bandY - bandH - 56, g.items, i === 2 ? {max:16, min:9, num:true, pi:0, color:c.tc} : {max:16, min:9, pi:0, color:c.tc});
        if (i === 0 || i === 4) void 0;
      });
      void bandY;
    }}
  ]});

/* ---------------- Value stream map ---------------- */
DG.type({id:'vsm', cat:'ops', name:'Value Stream Map', fields:[{k:'l', n:'Process step'}, {k:'v', n:'Process time', num:1}, {k:'t', n:'Wait before next', num:1}, {k:'d', n:'Note (e.g. 2 operators)'}], min:2, max:8,
  sample:{rows:[{l:'Order entry', v:0.5, t:2, d:'1 operator'}, {l:'Credit check', v:0.2, t:1.5, d:'Manual'}, {l:'Picking', v:1, t:0.5, d:'2 operators'}, {l:'Packing', v:0.5, t:1, d:'Batch of 20'}, {l:'Shipping', v:0.3, t:0, d:'Daily pickup'}], unit:'days'},
  ai:'3-7 process steps with process time and waiting time (same unit, e.g. days) and a short note.',
  designs:[
    {id:'vs1', name:'Value stream · steps + timeline', draw:function(K, Z, D){
      var n = D.rows.length, gap = 60, w = (Z.w - 200 - gap*(n - 1)) / n, bh = Math.min(170, Z.h*0.45), pt = 0, wt = 0;
      D.rows.forEach(function(r, i){
        var x = Z.x + i*(w + gap), c = K.card(x, Z.y, w, bh, {});
        K.rect(x, Z.y, w, 46, {pi:0, r:12}); K.rect(x, Z.y + 30, w, 16, {pi:0});
        K.tOn(x + 12, Z.y, w - 24, 46, r.l, 0, {max:17, min:9, wt:800, align:'c', lines:1});
        K.t(x + 14, Z.y + 58, w - 28, 26, 'Process time', {size:12, color:c.mc, lines:1});
        K.t(x + 14, Z.y + 80, w - 28, 34, fmt(r.v, D.unit), {max:24, min:11, font:'head', wt:900, color:c.tc, lines:1});
        K.t(x + 14, Z.y + 118, w - 28, bh - 124, r.d, {max:14, min:8, color:c.mc, va:'t', lines:2});
        if (i < n - 1) { K.poly([[x + w + gap/2 - 16, Z.y + bh/2 + 20], [x + w + gap/2 + 16, Z.y + bh/2 + 20], [x + w + gap/2, Z.y + bh/2 - 10]], {fix:K.warn()}); K.t(x + w, Z.y + bh/2 + 24, gap, 22, 'I', {size:12, wt:900, color:'#111827', align:'c', lines:1}); K.arrow(x + w + 4, Z.y + bh/2 - 30, x + w + gap - 4, Z.y + bh/2 - 30, {color:K.mc, lw:2, head:9}); }
        pt += r.v || 0; wt += r.t || 0;
      });
      var ly = Z.y + bh + 70, lw = Z.w - 200;
      var y1 = ly, y2 = ly + 44, pts = [];
      D.rows.forEach(function(r, i){ var x = Z.x + i*(w + gap);
        K.line(x, y2, x + w, y2, {pi:0, lw:4}); K.t(x, y2 + 6, w, 24, fmt(r.v, D.unit), {size:13, wt:800, pi:0, align:'c', lines:1});
        if (i < n - 1) { K.line(x + w, y2, x + w, y1, {color:K.mc, lw:1.5}); K.line(x + w, y1, x + w + gap, y1, {fix:K.warn(), color:K.warn(), lw:4}); K.line(x + w + gap, y1, x + w + gap, y2, {color:K.mc, lw:1.5}); K.t(x + w - 10, y1 - 28, gap + 20, 24, fmt(r.t, D.unit), {size:13, wt:800, fix:K.warn(), align:'c', lines:1}); }
      });
      var bx = Z.x + lw + 24, c2 = K.card(bx, Z.y + bh + 30, 176, Z.h - bh - 30, {hl:true}), lead = pt + wt;
      K.t(bx + 16, Z.y + bh + 44, 144, 20, 'LEAD TIME', {size:12, wt:800, color:c2.mc, ls:0.08, lines:1});
      K.t(bx + 16, Z.y + bh + 64, 144, 36, fmt(lead, D.unit), {max:26, min:12, font:'head', wt:900, color:c2.tc, lines:1});
      K.t(bx + 16, Z.y + bh + 104, 144, 20, 'VALUE-ADDED', {size:12, wt:800, color:c2.mc, ls:0.08, lines:1});
      K.t(bx + 16, Z.y + bh + 124, 144, 36, (lead ? Math.round(pt/lead*100) : 0) + '%', {max:26, min:12, font:'head', wt:900, color:c2.tc, lines:1});
    }}
  ]});

/* ---------------- Customer journey ---------------- */
DG.type({id:'journey', cat:'ops', name:'Customer Journey Map', fields:[{k:'l', n:'Stage'}, {k:'d', n:'Customer action'}, {k:'v', n:'Feeling (-2 to +2)', num:1}, {k:'g', n:'Touchpoint'}], min:3, max:7,
  sample:{rows:[{l:'Awareness', d:'Sees an ad on LinkedIn', v:1, g:'Social media'}, {l:'Consideration', d:'Compares plans and reviews', v:0, g:'Website'}, {l:'Purchase', d:'Signs up, confusing checkout', v:-1, g:'Checkout'}, {l:'Onboarding', d:'Guided set-up in 10 minutes', v:2, g:'App + email'}, {l:'Loyalty', d:'Renews and refers a colleague', v:2, g:'Account manager'}]},
  ai:'3-6 journey stages: l = stage, d = what the customer does (max 8 words), v = feeling from -2 (bad) to 2 (great), g = touchpoint.',
  designs:[
    {id:'cj1', name:'Journey · stages + emotion curve', draw:function(K, Z, D){
      var n = D.rows.length, gap = 12, w = (Z.w - 110 - gap*(n - 1)) / n, x0 = Z.x + 110, hh = 56, ey = Z.y + hh + 16, eh = Z.h*0.32, ty = ey + eh + 20;
      [['Stage', Z.y, hh], ['Feeling', ey, eh], ['Action', ty, (Z.y + Z.h - ty)*0.62], ['Touchpoint', ty + (Z.y + Z.h - ty)*0.62 + 10, (Z.y + Z.h - ty)*0.38 - 10]].forEach(function(r){ K.t(Z.x, r[1], 100, r[2], r[0].toUpperCase(), {size:12, wt:800, color:K.mc, ls:0.08, lines:1}); });
      var pts = [];
      D.rows.forEach(function(r, i){
        var x = x0 + i*(w + gap), sf = 0.45 + 0.55*(i + 1)/n;
        K.shape(i ? 'chevron' : 'homePlate', x - (i ? 10 : 0), Z.y, w + (i ? 20 : 10), hh, {pi:0, soft:sf});
        K.tOn(x + 16, Z.y, w - 20, hh, r.l, 0, {max:17, min:9, wt:800, lines:1, align:'c'}, sf);
        K.rect(x, ey, w, eh, {fill:K.cardc, r:10, op:0.6});
        var v = Math.max(-2, Math.min(2, r.v || 0)); pts.push([x + w/2, ey + eh/2 - v/2*(eh/2 - 24)]);
        var c = K.card(x, ty, w, (Z.y + Z.h - ty)*0.62, {});
        K.t(x + 14, ty + 10, w - 28, (Z.y + Z.h - ty)*0.62 - 20, r.d, {max:16, min:9, color:c.tc, va:'t', lh:1.3});
        var py = ty + (Z.y + Z.h - ty)*0.62 + 10;
        K.rect(x, py, w, (Z.y + Z.h - ty)*0.38 - 10, {pi:0, soft:0.12, r:10});
        K.t(x + 10, py, w - 20, (Z.y + Z.h - ty)*0.38 - 10, r.g, {max:15, min:8, wt:700, pi:0, align:'c', lines:2});
      });
      K.line(x0, ey + eh/2, x0 + Z.w - 110, ey + eh/2, {color:K.ln, lw:1, dash:true});
      K.curve(pts, {spi:0, lw:3.5});
      pts.forEach(function(p, i){ var v = D.rows[i].v || 0, sc = v > 0 ? K.good() : (v < 0 ? K.bad() : K.warn()); K.circ(p[0], p[1], 16, {fix:sc, line:K.bg, lw:3}); K.t(p[0] - 16, p[1] - 16, 32, 32, v > 0 ? '▲' : (v < 0 ? '▼' : '●'), {size:13, color:'#FFFFFF', align:'c', lines:1}); });
    }},
    {id:'cj2', name:'Journey · path + moments', draw:function(K, Z, D){
      var n = D.rows.length, cy = Z.y + Z.h*0.5, step = Z.w / n;
      K.rect(Z.x, cy - 6, Z.w, 12, {pi:0, soft:0.3, r:6});
      D.rows.forEach(function(r, i){
        var cx = Z.x + step*i + step/2, up = i % 2 === 0, v = r.v || 0, sc = v > 0 ? K.good() : (v < 0 ? K.bad() : K.warn());
        K.circ(cx, cy, 30, {pi:0}); K.tOn(cx - 30, cy - 30, 60, 60, String(i + 1), 0, {size:22, wt:900, align:'c', font:'head'});
        var by = up ? Z.y : cy + 50, bh = cy - 50 - Z.y, c = K.card(cx - step/2 + 8, by, step - 16, bh, {});
        K.rect(cx - step/2 + 8, up ? by + bh - 5 : by, step - 16, 5, {fix:sc});
        K.t(cx - step/2 + 24, by + 14, step - 48, 30, r.l, {max:20, min:10, font:'head', wt:900, pi:0, lines:1});
        K.t(cx - step/2 + 24, by + 46, step - 48, bh - 90, r.d, {max:16, min:9, color:c.tc, va:'t', lh:1.3});
        K.t(cx - step/2 + 24, by + bh - 38, step - 48, 26, '◉ ' + (r.g || ''), {max:13, min:8, wt:700, color:c.mc, lines:1});
      });
    }}
  ]});

/* ---------------- Fishbone ---------------- */
DG.type({id:'fishbone', cat:'ops', name:'Fishbone (Cause & Effect)', fields:[{k:'g', n:'Category'}, {k:'l', n:'Cause'}], min:3, max:24,
  sample:{ax:{x:'Late deliveries'}, rows:[{g:'People', l:'New drivers not trained'}, {g:'People', l:'Staff shortages on Mondays'}, {g:'Process', l:'Manual route planning'}, {g:'Process', l:'Orders released late'}, {g:'Technology', l:'No live tracking'}, {g:'Technology', l:'WMS outages'}, {g:'Materials', l:'Packaging out of stock'}, {g:'Environment', l:'City traffic peaks'}, {g:'Measurement', l:'No on-time KPI per driver'}]},
  ai:'ax.x = the problem (effect, max 4 words); rows: 4-6 categories (g, e.g. People, Process, Technology, Materials, Environment, Measurement) each with 1-3 causes (l, max 6 words).',
  designs:[
    {id:'fb1', name:'Fishbone · classic', draw:function(K, Z, D){
      var cats = []; D.rows.forEach(function(r){ if (r.g && cats.indexOf(r.g) < 0) cats.push(r.g); });
      var head = 210, sy = Z.y + Z.h/2, sx = Z.x, ex = Z.x + Z.w - head - 10, nTop = Math.ceil(cats.length/2), step = (ex - sx - 40) / Math.max(1, nTop);
      K.line(sx, sy, ex, sy, {pi:0, lw:5});
      K.poly([[ex, sy - 70], [ex + head, sy - 50], [ex + head, sy + 50], [ex, sy + 70]], {pi:0});
      K.tOn(ex + 16, sy - 50, head - 26, 100, D.ax.x || 'Problem', 0, {max:22, min:10, font:'head', wt:900, align:'c', lines:3});
      cats.forEach(function(c, i){
        var top = i % 2 === 0, k = Math.floor(i/2), bx = sx + 60 + step*k + step*0.75, by = sy, tx = bx - Z.h*0.32, ty = top ? Z.y + 38 : Z.y + Z.h - 38;
        K.line(tx, ty, bx, by, {pi:i + 1, lw:3});
        K.rect(tx - 90, top ? ty - 38 : ty, 180, 38, {pi:i + 1, r:19});
        K.tOn(tx - 90, top ? ty - 38 : ty, 180, 38, c, i + 1, {max:16, min:9, wt:800, align:'c', lines:1});
        var causes = D.rows.filter(function(r){ return r.g === c; }), m = causes.length;
        causes.forEach(function(r, j){
          var f = (j + 1) / (m + 1), px = tx + (bx - tx)*f, py = ty + (by - ty)*f;
          K.line(px - 150, py, px, py, {color:K.mc, lw:1.5});
          K.t(px - 250, py - 30, 240, 28, r.l, {max:14.5, min:8, color:K.txt, align:'r', va:'b', lines:2});
        });
      });
    }}
  ]});
})();

/* DS Diagrams — Strategy frameworks: SWOT, PESTLE, Porter's Five Forces, BCG, Ansoff */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('strategy', 'Strategy Frameworks', '🎯', 'SWOT, PESTLE, Porter, BCG, Ansoff');

/* rows grouped by g (group name) in the order of the type's groups */
function groups(D, names){
  var G = (D.cols && D.cols.length ? D.cols : names).slice(0, names.length);
  while (G.length < names.length) G.push(names[G.length]);
  var out = G.map(function(n){ return {n:n, items:[]}; }), lost = [];
  D.rows.forEach(function(r){
    var i = G.indexOf(r.g); if (i < 0) i = names.indexOf(r.g);
    if (i < 0) { var gl = String(r.g || '').toLowerCase(); for (var k = 0; k < G.length; k++) if (gl && G[k].toLowerCase().indexOf(gl.slice(0, 4)) === 0) { i = k; break; } }
    if (i >= 0 && r.l) out[i].items.push(r.l); else if (r.l) lost.push(r);
  });
  /* points whose group name belongs to another framework (e.g. SWOT points on a PESTLE design):
     place them by order of appearance into the groups that are still empty */
  if (lost.length) {
    var names2 = [], empty = [];
    lost.forEach(function(r){ if (names2.indexOf(r.g) < 0) names2.push(r.g); });
    out.forEach(function(g, k){ if (!g.items.length) empty.push(k); });
    lost.forEach(function(r){ var j = names2.indexOf(r.g), k = empty.length ? empty[Math.min(j, empty.length - 1)] : Math.min(j, out.length - 1); out[k].items.push(r.l); });
  }
  return out;
}
DG.groups = groups;
var GF = [{k:'g', n:'Group', group:1}, {k:'l', n:'Point'}];

/* ---------------- SWOT ---------------- */
var SW = ['Strengths', 'Weaknesses', 'Opportunities', 'Threats'];
DG.type({id:'swot', cat:'strategy', name:'SWOT Analysis', fields:GF, groups:SW, min:4, max:24,
  ai:'4 groups exactly named Strengths, Weaknesses, Opportunities, Threats; 2-4 short points (max 10 words) in each.',
  sample:{rows:[
    {g:'Strengths', l:'Trusted brand with 20 years in market'}, {g:'Strengths', l:'Proprietary AI analytics platform'}, {g:'Strengths', l:'Strong recurring revenue base'},
    {g:'Weaknesses', l:'Limited presence outside Europe'}, {g:'Weaknesses', l:'Long enterprise sales cycles'},
    {g:'Opportunities', l:'Mid-market demand growing 18% a year'}, {g:'Opportunities', l:'Partnerships with cloud providers'}, {g:'Opportunities', l:'New compliance rules drive adoption'},
    {g:'Threats', l:'Aggressive low-cost competitors'}, {g:'Threats', l:'Rising data-privacy regulation'}]},
  designs:[
    {id:'swot1', name:'SWOT · Quadrant cards', draw:function(K, Z, D){
      var G = groups(D, SW), gap = 20, w = (Z.w - gap)/2, h = (Z.h - gap)/2;
      G.forEach(function(g, i){
        var x = Z.x + (i % 2)*(w + gap), y = Z.y + Math.floor(i/2)*(h + gap), c = K.card(x, y, w, h, {});
        K.rect(x, y, w, 6, {pi:i, r:3});
        K.t(x + w - 110, y + 4, 100, h - 10, g.n.charAt(0), {size:Math.min(120, h*0.62), wt:900, font:'head', align:'r', va:'b', pi:i, op:0.14, lh:1});
        K.circ(x + 38, y + 44, 18, {pi:i});
        K.tOn(x + 20, y + 26, 36, 36, g.n.charAt(0), i, {size:18, wt:800, align:'c', font:'head'});
        K.t(x + 66, y + 26, w - 90, 36, g.n, {max:29, min:15, font:'head', wt:700, color:c.tc, lines:1});
        K.list(x + 26, y + 76, w - 52, h - 92, g.items, {max:23, min:11, pi:i, color:c.mc});
      });
    }},
    {id:'swot2', name:'SWOT · Colour quadrants + hub', draw:function(K, Z, D){
      var G = groups(D, SW), w = Z.w/2, h = Z.h/2, cx = Z.x + w, cy = Z.y + h, R = Math.min(w, h)*0.3;
      G.forEach(function(g, i){
        var x = Z.x + (i % 2)*w, y = Z.y + Math.floor(i/2)*h, left = i % 2 === 0, top = i < 2;
        K.rect(x + (left ? 0 : 3), y + (top ? 0 : 3), w - 3, h - 3, {pi:i, soft:0.16, r:16});
        var tx = left ? x + 28 : x + R + 40, tw = w - R - 70;
        K.t(tx, y + (top ? 22 : h*0.28), tw, 34, g.n, {max:29, min:15, font:'head', wt:800, pi:i, lines:1, align:left ? 'l' : 'l'});
        K.list(tx, y + (top ? 62 : h*0.28 + 40), tw, h*0.72 - 50, g.items, {max:23, min:11, pi:i});
      });
      K.circ(cx, cy, R + 8, {fill:K.bg});
      [0,1,2,3].forEach(function(i){ K.sector(cx, cy, R*0.52, R, [270,0,180,90][i], [360,90,270,180][i], {pi:i}); });
      K.circ(cx, cy, R*0.5, {fill:K.cardc});
      K.t(cx - R*0.5, cy - R*0.25, R, R*0.5, 'SWOT', {max:R*0.34, min:12, font:'head', wt:900, color:K.tc, align:'c', lines:1});
      ['S','W','O','T'].forEach(function(l, i){ var p = DG.pol(cx, cy, R*0.76, [315,45,225,135][i]); K.tOn(p[0] - 16, p[1] - 16, 32, 32, l, i, {size:R*0.2, wt:900, align:'c', font:'head'}); });
    }},
    {id:'swot3', name:'SWOT · Four columns', draw:function(K, Z, D){
      var G = groups(D, SW), gap = 18, w = (Z.w - gap*3)/4;
      G.forEach(function(g, i){
        var x = Z.x + i*(w + gap), c = K.card(x, Z.y + 34, w, Z.h - 34, {});
        K.circ(x + w/2, Z.y + 34, 34, {pi:i, line:K.bg, lw:4});
        K.tOn(x + w/2 - 34, Z.y, 68, 68, g.n.charAt(0), i, {size:30, wt:900, align:'c', font:'head'});
        K.t(x + 12, Z.y + 80, w - 24, 34, g.n, {max:27, min:13, font:'head', wt:800, color:c.tc, align:'c', lines:1});
        K.rect(x + w/2 - 22, Z.y + 118, 44, 3, {pi:i, r:1.5});
        K.list(x + 18, Z.y + 136, w - 36, Z.h - 150, g.items, {max:22, min:11, pi:i, color:c.mc});
      });
    }},
    {id:'swot4', name:'SWOT · Internal vs external matrix', draw:function(K, Z, D){
      var G = groups(D, SW), lw = 118, th = 40, gap = 14, x0 = Z.x + lw, y0 = Z.y + th, w = (Z.w - lw - gap)/2, h = (Z.h - th - gap)/2;
      ['Helpful', 'Harmful'].forEach(function(l, i){ K.t(x0 + i*(w + gap), Z.y, w, th - 8, l.toUpperCase(), {size:14, wt:800, color:K.mc, align:'c', ls:0.12, lines:1}); });
      ['Internal', 'External'].forEach(function(l, i){ var yy = y0 + i*(h + gap); K.rect(Z.x, yy, lw - 14, h, {fill:K.cardc, r:12}); K.t(Z.x + 8, yy, lw - 30, h, l.toUpperCase() + '\n' + (i ? 'environment' : 'organisation'), {max:15, min:10, wt:800, color:K.mc, align:'c', lh:1.3}); });
      G.forEach(function(g, i){
        var x = x0 + (i % 2)*(w + gap), y = y0 + Math.floor(i/2)*(h + gap);
        K.rect(x, y, w, h, {pi:i, soft:0.12, r:14, line:K.col(i), lw:1.2, lop:0.5});
        K.rect(x, y, w, 46, {pi:i, r:14}); K.rect(x, y + 30, w, 16, {pi:i});
        K.tOn(x + 20, y, w - 40, 46, g.n.toUpperCase(), i, {size:16, wt:800, ls:0.08, lines:1});
        K.list(x + 22, y + 62, w - 44, h - 76, g.items, {max:23, min:11, pi:i});
      });
    }}
  ]});

/* ---------------- PESTLE ---------------- */
var PE = ['Political', 'Economic', 'Social', 'Technological', 'Legal', 'Environmental'];
DG.type({id:'pestle', cat:'strategy', name:'PESTLE Analysis', fields:GF, groups:PE, min:6, max:24,
  ai:'6 groups exactly named Political, Economic, Social, Technological, Legal, Environmental; 1-3 short factors (max 9 words) each.',
  sample:{rows:[
    {g:'Political', l:'Stable trade policy across core markets'}, {g:'Political', l:'Public funding for digital programs'},
    {g:'Economic', l:'Inflation easing to 3% in 2026'}, {g:'Economic', l:'Tighter corporate IT budgets'},
    {g:'Social', l:'Hybrid work is now the norm'}, {g:'Social', l:'Customers expect instant service'},
    {g:'Technological', l:'Generative AI in every workflow'}, {g:'Technological', l:'Cloud costs falling each year'},
    {g:'Legal', l:'EU AI Act compliance from 2026'}, {g:'Legal', l:'Stricter data-residency rules'},
    {g:'Environmental', l:'Net-zero reporting required for suppliers'}, {g:'Environmental', l:'Energy-efficient data centres'}]},
  designs:[
    {id:'pestle1', name:'PESTLE · Six columns', draw:function(K, Z, D){
      var G = groups(D, PE), gap = 14, w = (Z.w - gap*5)/6, fs = 21;
      G.forEach(function(g){ if (g.items.length) fs = Math.min(fs, K.list(0, 0, w - 26, Z.h - 124, g.items, {max:21, min:10, gap:10, dry:true})); });   /* one text size for all columns */
      G.forEach(function(g, i){
        var x = Z.x + i*(w + gap);
        K.rect(x, Z.y, w, 92, {pi:i, r:14}); K.rect(x, Z.y + 70, w, 22, {pi:i});
        K.tOn(x, Z.y + 6, w, 52, g.n.charAt(0), i, {size:40, wt:900, align:'c', font:'head', lh:1});
        K.tOn(x + 6, Z.y + 56, w - 12, 28, g.n, i, {max:20, min:10, wt:700, align:'c', lines:1});
        var c = K.card(x, Z.y + 92, w, Z.h - 92, {kind:'solid'});
        K.list(x + 14, Z.y + 110, w - 26, Z.h - 124, g.items, {max:fs, min:Math.min(fs, 10), pi:i, color:c.mc, gap:10});
      });
    }},
    {id:'pestle2', name:'PESTLE · Hexagon wheel', draw:function(K, Z, D){
      var G = groups(D, PE), cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, R = Math.min(Z.h*0.34, 175), hw = R*0.62, bw = Z.w/2 - R - hw*0.5 - 40, rowH = Z.h/3;
      K.shape('hexagon', cx - R*0.5, cy - R*0.44, R, R*0.88, {fill:K.cardc});
      K.t(cx - R*0.46, cy - 22, R*0.92, 44, 'PESTLE', {max:26, min:13, font:'head', wt:900, color:K.tc, align:'c', lines:1});
      /* left column: 0,1,2 top→bottom; right column: 3,4,5 */
      var ang = [300, 240, 180, 60, 120, 0];
      ang = [315, 270, 225, 45, 90, 135];
      G.forEach(function(g, i){
        var right = i >= 3, row = i % 3, p = DG.pol(cx, cy, R*1.02, ang[i]);
        K.shape('hexagon', p[0] - hw/2, p[1] - hw*0.44, hw, hw*0.88, {pi:i});
        K.tOn(p[0] - hw/2, p[1] - hw*0.44, hw, hw*0.88, g.n.charAt(0), i, {size:hw*0.42, wt:900, align:'c', font:'head', lh:1});
        var bx = right ? Z.x + Z.w - bw : Z.x, by = Z.y + row*rowH + 6;
        var ay = by + rowH/2 - 4, ax = right ? bx - 14 : bx + bw + 14;
        K.line(right ? p[0] + hw*0.5 : p[0] - hw*0.5, p[1], ax, ay, {pi:i, lw:1.5, op:0.6});
        K.circ(ax, ay, 4, {pi:i});
        K.t(bx, by, bw, 30, g.n, {max:21, min:13, font:'head', wt:800, pi:i, align:right ? 'l' : 'r', lines:1});
        K.t(bx, by + 34, bw, rowH - 46, g.items.join(' · '), {max:20, min:10, color:K.mc, align:right ? 'l' : 'r', va:'t', lh:1.3});
      });
    }},
    {id:'pestle3', name:'PESTLE · Factor rows', draw:function(K, Z, D){
      var G = groups(D, PE), gap = 10, h = (Z.h - gap*5)/6;
      G.forEach(function(g, i){
        var y = Z.y + i*(h + gap), c = K.card(Z.x, y, Z.w, h, {});
        K.rect(Z.x, y, 240, h, {pi:i, r:12}); K.rect(Z.x + 220, y, 20, h, {pi:i});
        K.shape('homePlate', Z.x + 200, y, 60, h, {pi:i});
        K.tOn(Z.x + 18, y, 50, h, g.n.charAt(0), i, {size:Math.min(34, h*0.7), wt:900, font:'head', lh:1});
        K.tOn(Z.x + 64, y, 150, h, g.n, i, {max:20, min:12, wt:700, lines:1});
        K.t(Z.x + 280, y, Z.w - 300, h, g.items.join('   •   '), {max:19, min:10, color:c.tc, lines:2});
      });
    }}
  ]});

/* ---------------- Porter's Five Forces ---------------- */
var PF = ['Competitive Rivalry', 'Threat of New Entrants', 'Supplier Power', 'Buyer Power', 'Threat of Substitutes'];
DG.type({id:'porter', cat:'strategy', name:"Porter's Five Forces", fields:[{k:'g', n:'Force', group:1}, {k:'l', n:'Point'}, {k:'v', n:'Strength (1-5)', num:1}], groups:PF, min:5, max:20,
  ai:'5 groups exactly named Competitive Rivalry, Threat of New Entrants, Supplier Power, Buyer Power, Threat of Substitutes; 1-3 short points each (max 9 words); v = force strength 1-5 on the first point of each group.',
  sample:{rows:[
    {g:'Competitive Rivalry', l:'Six global players fight on price', v:4}, {g:'Competitive Rivalry', l:'Features copied within months'},
    {g:'Threat of New Entrants', l:'High compliance costs deter start-ups', v:2}, {g:'Threat of New Entrants', l:'Cloud lowers the tech barrier'},
    {g:'Supplier Power', l:'Few chip and cloud suppliers', v:3},
    {g:'Buyer Power', l:'Large clients negotiate multi-year deals', v:4}, {g:'Buyer Power', l:'Switching costs are moderate'},
    {g:'Threat of Substitutes', l:'In-house tools and open source', v:3}]},
  designs:[
    {id:'porter1', name:"Five Forces · Cross", draw:function(K, Z, D){
      var G = groups(D, PF), cw = Z.w*0.3, ch = Z.h*0.3, cx = Z.x + Z.w/2, cy = Z.y + Z.h/2;
      var pos = [[cx - cw/2, cy - ch/2], [cx - cw/2, Z.y], [Z.x, cy - ch/2], [Z.x + Z.w - cw, cy - ch/2], [cx - cw/2, Z.y + Z.h - ch]];
      var str = {}; D.rows.forEach(function(r){ var i = PF.indexOf(r.g); if (i >= 0 && r.v != null && str[i] == null) str[i] = r.v; });
      [[1, 0], [2, 0], [3, 0], [4, 0]].forEach(function(p){ var a = pos[p[0]], b = pos[0], ax = a[0] + cw/2, ay = a[1] + ch/2, bx = b[0] + cw/2, by = b[1] + ch/2;
        var dx = bx - ax, dy = by - ay, L = Math.hypot(dx, dy), sx = ax + dx/L*(Math.abs(dx) > 1 ? cw/2 : ch/2), sy = ay + dy/L*(Math.abs(dy) > 1 ? ch/2 : cw/2), ex = bx - dx/L*(Math.abs(dx) > 1 ? cw/2 + 6 : ch/2 + 6), ey = by - dy/L*(Math.abs(dy) > 1 ? ch/2 + 6 : cw/2 + 6);
        K.arrow(sx, sy, ex, ey, {pi:p[0], lw:3, head:14}); });
      G.forEach(function(g, i){
        var x = pos[i][0], y = pos[i][1], hl = i === 0;
        if (hl) K.rect(x, y, cw, ch, {pi:0, r:16}); else { K.card(x, y, cw, ch, {}); K.rect(x, y, 6, ch, {pi:i, r:3}); }
        K.t(x + 18, y + 10, cw - 36, 28, g.n, {max:20, min:12, font:'head', wt:800, lines:1, pi:hl ? null : i, color:hl ? DG.u.on(K.col(0)) : null});
        if (str[i] != null) for (var k = 0; k < 5; k++) K.rect(x + cw - 18 - (5 - k)*14, y + 18, 10, 10, {pi:hl ? null : i, fill:hl ? DG.u.on(K.col(0)) : null, r:2, op:k < str[i] ? 1 : 0.22});
        K.list(x + 18, y + 44, cw - 36, ch - 54, g.items, {max:20, min:10, dot:false, gap:4, color:hl ? DG.u.on(K.col(0)) : K.mc});
      });
    }},
    {id:'porter2', name:'Five Forces · Strength bars', draw:function(K, Z, D){
      var G = groups(D, PF), str = {}; D.rows.forEach(function(r){ var i = PF.indexOf(r.g); if (i >= 0 && r.v != null && str[i] == null) str[i] = r.v; });
      var gap = 12, h = (Z.h - gap*4)/5;
      G.forEach(function(g, i){
        var y = Z.y + i*(h + gap), c = K.card(Z.x, y, Z.w, h, {});
        K.badge(Z.x + 36, y + h/2, 20, i + 1, i);
        K.t(Z.x + 72, y, 280, h, g.n, {max:21, min:13, font:'head', wt:800, color:c.tc, lines:2});
        K.t(Z.x + 370, y, Z.w - 700, h, g.items.join('  ·  '), {max:21, min:10, color:c.mc, lines:2});
        var v = str[i] != null ? Math.max(0, Math.min(5, str[i])) : 3, bx = Z.x + Z.w - 310, bw = 230;
        K.rect(bx, y + h/2 - 7, bw, 14, {fill:K.ln, r:7, op:0.6}); K.rect(bx, y + h/2 - 7, bw*v/5, 14, {pi:i, r:7});
        K.t(bx + bw + 10, y, 60, h, ['','Low','Low','Medium','High','High'][Math.round(v)] || '', {size:13, wt:700, pi:i, lines:1});
      });
    }}
  ]});

/* ---------------- BCG ---------------- */
DG.type({id:'bcg', cat:'strategy', name:'BCG Matrix', fields:[{k:'l', n:'Product / unit'}, {k:'x', n:'Relative market share (0-2)', num:1}, {k:'y', n:'Market growth %', num:1}, {k:'z', n:'Revenue (size)', num:1}], min:2, max:12,
  ai:'products or business units: x = relative market share 0.1-2 (1 = equal to leader), y = market growth % 0-25, z = revenue in millions.',
  sample:{rows:[{l:'Cloud Suite', x:1.6, y:18, z:120}, {l:'Mobile App', x:0.5, y:21, z:40}, {l:'On-prem ERP', x:1.4, y:3, z:210}, {l:'Legacy Fax', x:0.3, y:2, z:15}, {l:'Analytics', x:0.8, y:14, z:60}], ax:{x:'Relative market share', y:'Market growth'}},
  designs:[
    {id:'bcg1', name:'BCG · Bubble matrix', draw:function(K, Z, D){ quad(K, Z, D, ['Stars', 'Question Marks', 'Cash Cows', 'Dogs'], true, 1, 10); }},
    {id:'bcg2', name:'BCG · Portfolio quadrants', draw:function(K, Z, D){ quadList(K, Z, D, ['Stars', 'Question Marks', 'Cash Cows', 'Dogs'], function(r){ return (r.y >= 10 ? 0 : 2) + (r.x >= 1 ? 0 : 1); }, ['High growth · high share', 'High growth · low share', 'Low growth · high share', 'Low growth · low share']); }}
  ]});

/* shared: quadrant chart with plotted bubbles. hiX: x split, hiY: y split; flipX puts high x on the left */
function quad(K, Z, D, names, flipX, xs, ys){
  var lw = 36, bh = 34, x0 = Z.x + lw, y0 = Z.y, w = Z.w - lw, h = Z.h - bh, hw = w/2, hh = h/2;
  names.forEach(function(n, i){
    var x = x0 + (i % 2)*hw, y = y0 + Math.floor(i/2)*hh;
    K.rect(x + 2, y + 2, hw - 4, hh - 4, {pi:i, soft:0.13, r:12});
    K.t(x + 16, y + (i < 2 ? 10 : hh - 42), hw - 32, 30, n.toUpperCase(), {size:15, wt:800, pi:i, ls:0.08, lines:1, align:i % 2 ? 'r' : 'l'});
  });
  K.t(x0, y0 + h + 4, w, bh - 4, (D.ax.x || 'Relative market share') + (flipX ? '  ←  high · low  →' : ''), {size:13, wt:700, color:K.mc, align:'c', lines:1});
  K.arrow(Z.x + 14, y0 + h - 10, Z.x + 14, y0 + 10, {color:K.mc, lw:2, head:10}); K.t(Z.x - 4, y0 - 30, 300, 24, '↑ ' + (D.ax.y || 'Market growth'), {size:13, wt:700, color:K.mc, lines:1});
  var mxX = Math.max(xs * 2, Math.max.apply(null, D.rows.map(function(r){ return r.x || 0; }))), mxY = Math.max(ys * 2, Math.max.apply(null, D.rows.map(function(r){ return r.y || 0; })));
  var zmax = Math.max.apply(null, D.rows.map(function(r){ return r.z || 1; }).concat([1]));
  D.rows.forEach(function(r, i){
    var fx = Math.min(1, Math.max(0, (r.x || 0) / mxX)), fy = Math.min(1, Math.max(0, (r.y || 0) / mxY));
    if (flipX) fx = 1 - fx;
    var cx = x0 + 30 + fx*(w - 60), cy = y0 + h - 30 - fy*(h - 60), rr = 14 + Math.sqrt((r.z || zmax*0.4)/zmax)*Math.min(w, h)*0.07;
    var qi = (fy >= 0.5 ? 0 : 2) + (fx >= 0.5 ? 1 : 0);
    K.circ(cx, cy, rr, {pi:qi, op:0.88, line:K.bg, lw:2});
    K.t(cx - 90, cy + rr + 2, 180, 22, r.l, {max:20, min:10, wt:700, color:K.tc, align:'c', lines:1});
  });
}
DG.quad = quad;
function quadList(K, Z, D, names, qOf, subs){
  var gap = 18, w = (Z.w - gap)/2, h = (Z.h - gap)/2, Q = [[], [], [], []];
  D.rows.forEach(function(r){ var q = qOf(r); if (q >= 0 && q < 4) Q[q].push(r.l); });
  names.forEach(function(n, i){
    var x = Z.x + (i % 2)*(w + gap), y = Z.y + Math.floor(i/2)*(h + gap), c = K.card(x, y, w, h, {});
    K.rect(x + 20, y + 22, 44, 44, {pi:i, r:12});
    K.tOn(x + 20, y + 22, 44, 44, String(i + 1), i, {size:20, wt:900, align:'c', font:'head'});
    K.t(x + 78, y + 18, w - 100, 30, n, {max:28, min:14, font:'head', wt:800, color:c.tc, lines:1});
    if (subs) K.t(x + 78, y + 46, w - 100, 22, subs[i], {size:13, color:c.mc, lines:1});
    K.list(x + 24, y + 86, w - 48, h - 100, Q[i], {max:23, min:11, pi:i, color:c.tc});
  });
}
DG.quadList = quadList;

/* ---------------- Ansoff ---------------- */
var AN = ['Market Penetration', 'Product Development', 'Market Development', 'Diversification'];
DG.type({id:'ansoff', cat:'strategy', name:'Ansoff Matrix', fields:GF, groups:AN, min:4, max:16,
  ai:'4 groups exactly named Market Penetration, Product Development, Market Development, Diversification; 1-3 short initiatives each (max 9 words).',
  sample:{rows:[{g:'Market Penetration', l:'Loyalty pricing for existing accounts'}, {g:'Market Penetration', l:'Upsell premium support'},
    {g:'Product Development', l:'Launch AI assistant add-on'}, {g:'Product Development', l:'Mobile-first reporting'},
    {g:'Market Development', l:'Enter Middle East via partners'}, {g:'Market Development', l:'Public-sector edition'},
    {g:'Diversification', l:'Data marketplace for partners'}]},
  designs:[
    {id:'ansoff1', name:'Ansoff · Growth matrix', draw:function(K, Z, D){
      var G = groups(D, AN), lw = 120, th = 44, x0 = Z.x + lw, y0 = Z.y + th, w = (Z.w - lw)/2, h = (Z.h - th)/2;
      ['Existing products', 'New products'].forEach(function(l, i){ K.t(x0 + i*w, Z.y, w, th - 8, l, {size:15, wt:800, color:K.mc, align:'c', lines:1}); });
      ['Existing markets', 'New markets'].forEach(function(l, i){ K.t(Z.x, y0 + i*h, lw - 14, h, l, {size:15, wt:800, color:K.mc, align:'r', lines:2}); });
      var risk = [0.14, 0.26, 0.26, 0.42];
      G.forEach(function(g, i){
        var x = x0 + (i % 2)*w, y = y0 + Math.floor(i/2)*h;
        K.rect(x + 4, y + 4, w - 8, h - 8, {pi:0, soft:risk[i] + (K.dark ? 0.1 : 0), r:14});
        K.t(x + 24, y + 18, w - 48, 30, g.n, {max:28, min:14, font:'head', wt:800, color:K.tc, lines:1});
        K.t(x + 24, y + 48, w - 48, 20, ['Lowest risk', 'Medium risk', 'Medium risk', 'Highest risk'][i], {size:12.5, wt:700, pi:0, lines:1});
        K.list(x + 24, y + 78, w - 48, h - 94, g.items, {max:23, min:11, pi:0, color:K.txt});
      });
      K.arrow(x0 + 20, y0 + 2*h - 14, x0 + 2*w - 20, y0 + 14, {pi:0, lw:2.5, dash:true, op:0.5, head:14});
    }},
    {id:'ansoff2', name:'Ansoff · Strategy cards', draw:function(K, Z, D){
      var G = groups(D, AN), gap = 18, w = (Z.w - gap*3)/4;
      G.forEach(function(g, i){
        var x = Z.x + i*(w + gap), c = K.card(x, Z.y, w, Z.h, {});
        K.t(x + 20, Z.y + 18, w - 40, 22, ['Existing · Existing', 'New product · Existing market', 'Existing product · New market', 'New · New'][i].toUpperCase(), {size:11, wt:800, pi:i, ls:0.06, lines:2});
        K.t(x + 20, Z.y + 50, w - 40, 64, g.n, {max:22, min:13, font:'head', wt:800, color:c.tc, lines:2, va:'t'});
        for (var k = 0; k < 4; k++) K.rect(x + 20 + k*22, Z.y + 124, 16, 8, {pi:i, r:4, op:k <= i ? 1 : 0.2});
        K.t(x + 20 + 92, Z.y + 116, w - 132, 24, 'risk', {size:12, color:c.mc, lines:1});
        K.list(x + 20, Z.y + 156, w - 40, Z.h - 172, g.items, {max:22, min:11, pi:i, color:c.mc});
      });
    }}
  ]});
})();

/* DS Diagrams — Matrices & prioritisation: 2x2, risk, impact-effort, prioritisation, 9-box, portfolio, maturity */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('matrix', 'Matrices & Prioritization', '🧮', '2×2, risk, impact–effort, 9-box, portfolio, maturity');
var XY = [{k:'l', n:'Item'}, {k:'x', n:'X score', num:1}, {k:'y', n:'Y score', num:1}];

/* plotted 2x2: x,y on 0..max scale; quadrant names TL,TR,BL,BR */
function plot2(K, Z, D, max, names, subs, sizes){
  var lab = 40, bot = 40, x0 = Z.x + lab, w = Z.w - lab, h = Z.h - bot, hw = w/2, hh = h/2;
  var tint = [0.22, 0.12, 0.08, 0.16];
  names.forEach(function(n, i){
    var x = x0 + (i % 2)*hw, y = Z.y + Math.floor(i/2)*hh;
    K.rect(x + 3, y + 3, hw - 6, hh - 6, {pi:i, soft:tint[i] + (K.dark ? 0.08 : 0), r:12});
    var right = i % 2 === 1, top = i < 2;
    K.t(x + 20, top ? y + 14 : y + hh - 64, hw - 40, 28, n, {max:19, min:11, font:'head', wt:900, pi:i, align:right ? 'r' : 'l', lines:1});
    if (subs) K.t(x + 20, top ? y + 42 : y + hh - 36, hw - 40, 22, subs[i], {size:13, color:K.mc, align:right ? 'r' : 'l', lines:1});
  });
  K.arrow(x0, Z.y + h, x0 + w, Z.y + h, {color:K.mc, lw:2, head:10}); K.arrow(x0, Z.y + h, x0, Z.y, {color:K.mc, lw:2, head:10});
  K.t(x0, Z.y + h + 6, w, bot - 6, (D.ax.x || 'X') + '  →', {size:14, wt:800, color:K.mc, align:'c', lines:1});
  K.t(Z.x - 20, Z.y - 32, 300, 24, '↑  ' + (D.ax.y || 'Y'), {size:14, wt:800, color:K.mc, lines:1});
  var zmax = 1; D.rows.forEach(function(r){ zmax = Math.max(zmax, r.z || 0); });
  D.rows.forEach(function(r, i){
    var fx = Math.max(0, Math.min(1, (r.x || 0)/max)), fy = Math.max(0, Math.min(1, (r.y || 0)/max));
    var cx = x0 + 24 + fx*(w - 48), cy = Z.y + h - 24 - fy*(h - 48), q = (fy >= 0.5 ? 0 : 2) + (fx >= 0.5 ? 1 : 0), R = sizes ? 14 + Math.sqrt((r.z || zmax*0.3)/zmax)*Math.min(w, h)*0.06 : 13;
    K.circ(cx, cy, R, {pi:q, line:K.bg, lw:2.5, op:sizes ? 0.9 : 1});
    if (!sizes) K.tOn(cx - R, cy - R, 2*R, 2*R, String(i + 1), q, {size:12, wt:900, align:'c'});
    var lx = cx + R + 6 + 170 > x0 + w ? cx - R - 176 : cx + R + 6;
    K.t(lx, cy - 13, 170, 26, r.l, {max:15, min:9, wt:700, color:K.tc, align:lx < cx ? 'r' : 'l', lines:1});
  });
}
DG.plot2 = plot2;

DG.type({id:'matrix2', cat:'matrix', name:'2×2 Matrix', fields:XY, min:1, max:14,
  sample:{cols:['Nurture', 'Invest', 'Monitor', 'Maintain'], rows:[{l:'Chatbot', x:3, y:8}, {l:'Mobile app', x:7, y:8}, {l:'Reporting', x:8, y:4}, {l:'Loyalty scheme', x:6, y:6.5}, {l:'Print catalogue', x:2, y:2}], ax:{x:'Capability', y:'Market attractiveness'}},
  ai:'items scored 0-10 on two measures (x and y); ax.x / ax.y name the measures; cols = 4 quadrant names in order top-left, top-right, bottom-left, bottom-right.',
  designs:[
    {id:'mx1', name:'2×2 · plotted items', draw:function(K, Z, D){ plot2(K, Z, D, 10, (D.cols.length === 4 ? D.cols : ['Nurture', 'Invest', 'Monitor', 'Maintain'])); }},
    {id:'mx2', name:'2×2 · quadrant lists', draw:function(K, Z, D){ var n = D.cols.length === 4 ? D.cols : ['Nurture', 'Invest', 'Monitor', 'Maintain']; DG.quadList(K, Z, D, n, function(r){ return ((r.y || 0) >= 5 ? 0 : 2) + ((r.x || 0) >= 5 ? 1 : 0); }, ['High ' + (D.ax.y || 'Y') + ' · low ' + (D.ax.x || 'X'), 'High ' + (D.ax.y || 'Y') + ' · high ' + (D.ax.x || 'X'), 'Low ' + (D.ax.y || 'Y') + ' · low ' + (D.ax.x || 'X'), 'Low ' + (D.ax.y || 'Y') + ' · high ' + (D.ax.x || 'X')]); }}
  ]});

DG.type({id:'impact', cat:'matrix', name:'Impact–Effort Matrix', fields:[{k:'l', n:'Idea'}, {k:'x', n:'Effort (0-10)', num:1}, {k:'y', n:'Impact (0-10)', num:1}], min:1, max:14,
  sample:{rows:[{l:'Self-service returns', x:3, y:8}, {l:'New ERP', x:9, y:9}, {l:'Email templates', x:2, y:3}, {l:'Rebrand website', x:8, y:4}, {l:'Live chat', x:4, y:7}, {l:'Price calculator', x:5, y:6}], ax:{x:'Effort', y:'Impact'}},
  ai:'ideas scored 0-10 for effort (x) and impact (y).',
  designs:[
    {id:'ie1', name:'Impact–effort · plotted', draw:function(K, Z, D){ D.ax = {x:D.ax.x || 'Effort', y:D.ax.y || 'Impact'}; plot2(K, Z, D, 10, ['Quick wins', 'Major projects', 'Fill-ins', 'Thankless tasks'], ['Do now', 'Plan carefully', 'Do if time allows', 'Avoid']); }},
    {id:'ie2', name:'Impact–effort · action lists', draw:function(K, Z, D){ DG.quadList(K, Z, D, ['Quick wins', 'Major projects', 'Fill-ins', 'Thankless tasks'], function(r){ return ((r.y || 0) >= 5 ? 0 : 2) + ((r.x || 0) >= 5 ? 1 : 0); }, ['High impact · low effort', 'High impact · high effort', 'Low impact · low effort', 'Low impact · high effort']); }}
  ]});

/* ---------------- Risk matrix ---------------- */
DG.type({id:'risk', cat:'matrix', name:'Risk Matrix', fields:[{k:'l', n:'Risk'}, {k:'x', n:'Likelihood (1-5)', num:1}, {k:'y', n:'Impact (1-5)', num:1}], min:1, max:14,
  sample:{rows:[{l:'Data breach', x:2, y:5}, {l:'Key supplier fails', x:3, y:4}, {l:'Budget overrun', x:4, y:3}, {l:'Staff turnover', x:3, y:2}, {l:'Regulation change', x:2, y:3}, {l:'Launch delay', x:4, y:4}]},
  ai:'risks with likelihood 1-5 (x) and impact 1-5 (y).',
  designs:[
    {id:'rk1', name:'Risk heat grid', draw:function(K, Z, D){
      var lab = 120, bot = 70, gw = Math.min(Z.w - lab - 330, (Z.h - bot) * 1.6), cw = gw / 5, ch = (Z.h - bot) / 5, x0 = Z.x + lab;
      for (var yy = 0; yy < 5; yy++) for (var xx = 0; xx < 5; xx++) {
        var score = (xx + 1)*(5 - yy), col = score >= 15 ? K.bad() : (score >= 8 ? K.warn() : K.good());
        K.rect(x0 + xx*cw + 2, Z.y + yy*ch + 2, cw - 4, ch - 4, {fix:col, soft:score >= 15 ? 0.75 : (score >= 8 ? 0.55 : 0.35), r:6});
      }
      ['Rare', 'Unlikely', 'Possible', 'Likely', 'Almost certain'].forEach(function(l, i){ K.t(x0 + i*cw, Z.y + Z.h - bot + 6, cw, 40, l, {max:13, min:8, color:K.mc, align:'c', va:'t', lines:2}); });
      ['Severe', 'Major', 'Moderate', 'Minor', 'Minimal'].forEach(function(l, i){ K.t(Z.x, Z.y + i*ch, lab - 12, ch, l, {max:13, min:8, color:K.mc, align:'r', lines:1}); });
      K.t(x0, Z.y + Z.h - 24, gw, 24, 'LIKELIHOOD →', {size:12, wt:800, color:K.mc, align:'c', ls:0.1, lines:1});
      var cellCount = {};
      D.rows.forEach(function(r, i){
        var xx = Math.max(1, Math.min(5, Math.round(r.x || 1))) - 1, yy = 5 - Math.max(1, Math.min(5, Math.round(r.y || 1))), key = xx + ',' + yy, k = cellCount[key] = (cellCount[key] || 0) + 1;
        var cx = x0 + xx*cw + cw/2 + (k - 1)*20 - 10*(k > 1 ? 1 : 0), cy = Z.y + yy*ch + ch/2;
        K.circ(cx, cy, 15, {fill:K.bg, line:K.tc, lw:2}); K.t(cx - 15, cy - 15, 30, 30, String(i + 1), {size:13, wt:900, color:K.tc, align:'c', lines:1});
      });
      var lx = x0 + gw + 36, lw = Z.x + Z.w - lx, rh = Math.min(52, (Z.h - 20) / D.rows.length);
      D.rows.forEach(function(r, i){
        var sc = (r.x || 0)*(r.y || 0), col = sc >= 15 ? K.bad() : (sc >= 8 ? K.warn() : K.good()), y = Z.y + i*rh;
        K.circ(lx + 14, y + rh/2, 14, {fix:col}); K.t(lx, y + rh/2 - 14, 28, 28, String(i + 1), {size:12, wt:900, color:'#FFFFFF', align:'c', lines:1});
        K.t(lx + 38, y, lw - 90, rh, r.l, {max:16, min:9, wt:700, color:K.tc, lines:2});
        K.t(lx + lw - 50, y, 50, rh, String(sc), {size:15, wt:900, fix:col, align:'r', lines:1});
      });
    }}
  ]});

/* ---------------- Prioritisation matrix (weighted scoring) ---------------- */
DG.type({id:'priority', cat:'matrix', name:'Prioritization Matrix', fields:[{k:'l', n:'Option'}], series:true, min:2, max:8,
  sample:{cols:['Value', 'Urgency', 'Feasibility', 'Cost (low=10)'], rows:[{l:'Customer portal', v:[9, 7, 8, 6]}, {l:'AI forecasting', v:[8, 6, 5, 4]}, {l:'Warehouse robots', v:[7, 4, 4, 2]}, {l:'Mobile checkout', v:[8, 8, 9, 7]}, {l:'Partner API', v:[6, 5, 8, 8]}]},
  ai:'2-6 options scored 1-10 against 3-5 criteria (cols = criteria names).',
  designs:[
    {id:'pm1', name:'Scoring table + ranking', draw:function(K, Z, D){
      var nc = D.cols.length, R = D.rows.map(function(r){ var t = 0; r.v.forEach(function(v){ t += v || 0; }); return {r:r, t:t}; }).sort(function(a, b){ return b.t - a.t; });
      var mxT = nc*10, lab = Math.min(260, Z.w*0.24), tw = Z.w - lab - 300, cw = tw / nc, hh = 52, rh = Math.min(80, (Z.h - hh) / R.length);
      D.cols.forEach(function(c, j){ K.t(Z.x + lab + cw*j, Z.y, cw, hh - 8, c, {max:14, min:8, wt:800, color:K.mc, align:'c', lines:2}); });
      K.t(Z.x + lab + tw + 20, Z.y, 280, hh - 8, 'TOTAL SCORE', {size:13, wt:800, color:K.mc, ls:0.08, lines:1});
      R.forEach(function(o, i){
        var y = Z.y + hh + i*rh; if (i % 2 === 0) K.rect(Z.x, y + 2, Z.w, rh - 4, {fill:K.cardc, r:10});
        K.badge(Z.x + 26, y + rh/2, 15, i + 1, 0);
        K.t(Z.x + 52, y, lab - 60, rh, o.r.l, {max:17, min:9, wt:800, color:K.tc, lines:2});
        o.r.v.forEach(function(v, j){ var sf = 0.12 + (v || 0)/10*0.78; K.rect(Z.x + lab + cw*j + 8, y + rh*0.2, cw - 16, rh*0.6, {pi:0, soft:sf, r:8}); K.tOn(Z.x + lab + cw*j, y, cw, rh, String(v || 0), 0, {size:16, wt:800, align:'c', lines:1}, sf); });
        var bx = Z.x + lab + tw + 20, bw = 200;
        K.rect(bx, y + rh/2 - 9, bw, 18, {fill:K.ln, op:0.5, r:9}); K.rect(bx, y + rh/2 - 9, bw*o.t/mxT, 18, {pi:i === 0 ? 1 : 0, r:9});
        K.t(bx + bw + 10, y, 70, rh, String(o.t), {size:18, wt:900, font:'head', color:K.tc, lines:1});
      });
    }}
  ]});

/* ---------------- 9-box ---------------- */
DG.type({id:'ninebox', cat:'matrix', name:'Performance Matrix (9-Box)', fields:[{k:'l', n:'Name'}, {k:'x', n:'Performance (1-3)', num:1}, {k:'y', n:'Potential (1-3)', num:1}], min:1, max:20,
  sample:{rows:[{l:'A. Patel', x:3, y:3}, {l:'J. Chen', x:2, y:3}, {l:'M. Garcia', x:3, y:2}, {l:'S. Okafor', x:2, y:2}, {l:'L. Novak', x:1, y:2}, {l:'R. Smith', x:2, y:1}, {l:'K. Tanaka', x:3, y:1}]},
  ai:'people or units with performance 1-3 (x) and potential 1-3 (y).',
  designs:[
    {id:'nb1', name:'9-box talent grid', draw:function(K, Z, D){
      var lab = 60, bot = 40, x0 = Z.x + lab, w = (Z.w - lab) / 3, h = (Z.h - bot) / 3;
      var names = [['Enigma', 'Growth employee', 'Future leader'], ['Dilemma', 'Core player', 'High performer'], ['Under-performer', 'Effective', 'Trusted professional']];
      for (var r = 0; r < 3; r++) for (var c = 0; c < 3; c++) {
        var sc = c + (2 - r), x = x0 + c*w, y = Z.y + r*h, col = sc >= 3 ? K.good() : (sc >= 2 ? null : K.warn());
        K.rect(x + 3, y + 3, w - 6, h - 6, col ? {fix:col, soft:0.1 + 0.08*sc, r:12} : {pi:0, soft:0.14, r:12});
        K.t(x + 16, y + 10, w - 32, 24, names[r][c], {max:16, min:9, wt:800, color:K.tc, lines:1});
        var who = D.rows.filter(function(o){ return Math.max(1, Math.min(3, Math.round(o.x || 1))) === c + 1 && Math.max(1, Math.min(3, Math.round(o.y || 1))) === 3 - r; }).map(function(o){ return o.l; });
        K.list(x + 16, y + 40, w - 32, h - 52, who, {max:15, min:8, pi:0, gap:4});
      }
      ['Low', 'Medium', 'High'].forEach(function(l, i){ K.t(x0 + i*w, Z.y + Z.h - bot + 6, w, bot - 8, l, {size:13, wt:700, color:K.mc, align:'c', lines:1}); K.t(Z.x, Z.y + (2 - i)*h, lab - 10, h, l, {size:13, wt:700, color:K.mc, align:'r', lines:1}); });
      K.t(x0, Z.y + Z.h - 18, w*3, 18, 'PERFORMANCE →', {size:11, wt:800, color:K.mc, align:'c', ls:0.1, lines:1});
      K.t(Z.x - 10, Z.y - 30, 240, 24, '↑ POTENTIAL', {size:12, wt:800, color:K.mc, ls:0.1, lines:1});
    }}
  ]});

/* ---------------- Portfolio (bubble quadrant) ---------------- */
DG.type({id:'portfolio', cat:'matrix', name:'Portfolio Chart', fields:[{k:'l', n:'Project / product'}, {k:'x', n:'Risk (0-10)', num:1}, {k:'y', n:'Value (0-10)', num:1}, {k:'z', n:'Investment (size)', num:1}], min:1, max:12,
  sample:{rows:[{l:'Cloud migration', x:3, y:8, z:4}, {l:'New market entry', x:8, y:9, z:9}, {l:'ERP upgrade', x:4, y:4, z:6}, {l:'AI pilot', x:7, y:6, z:2}, {l:'Office move', x:2, y:2, z:3}], ax:{x:'Risk', y:'Value'}},
  ai:'projects or products with risk 0-10 (x), value 0-10 (y) and investment size (z).',
  designs:[
    {id:'pf1', name:'Portfolio · bubble quadrants', draw:function(K, Z, D){ D.ax = {x:D.ax.x || 'Risk', y:D.ax.y || 'Value'}; plot2(K, Z, D, 10, ['Pearls', 'Oysters', 'Bread & butter', 'White elephants'], ['High value · low risk', 'High value · high risk', 'Low value · low risk', 'Low value · high risk'], true); }}
  ]});

/* ---------------- Maturity model ---------------- */
DG.type({id:'maturity', cat:'matrix', name:'Maturity Model', fields:[{k:'l', n:'Level'}, {k:'d', n:'Description'}], min:3, max:6,
  sample:{rows:[{l:'Initial', d:'Ad-hoc processes, heroics'}, {l:'Repeatable', d:'Basic processes, some reuse'}, {l:'Defined', d:'Standard ways of working'}, {l:'Managed', d:'Measured with clear KPIs'}, {l:'Optimising', d:'Continuous, data-driven improvement'}], o:{cur:3, target:4}},
  ai:'3-5 maturity levels from lowest to highest: l = level name, d = short description; o.cur = current level number, o.target = target level number.',
  designs:[
    {id:'mm1', name:'Maturity · staircase', draw:function(K, Z, D){
      var n = D.rows.length, gap = 10, w = (Z.w - gap*(n - 1)) / n, cur = (D.o && D.o.cur) || 0, tg = (D.o && D.o.target) || 0;
      D.rows.forEach(function(r, i){
        var h = 130 + (Z.h - 190)*(i + 1)/n, x = Z.x + i*(w + gap), y = Z.y + Z.h - h, isC = cur === i + 1, isT = tg === i + 1;
        K.rect(x, y, w, h, isC ? {pi:0, r:12} : {pi:0, soft:0.14 + 0.1*i, r:12});
        var tc = isC ? DG.u.on(K.col(0)) : K.tc;
        K.t(x + 18, y + 14, w - 36, 22, 'LEVEL ' + (i + 1), {size:12.5, wt:800, color:isC ? tc : K.col(0), ls:0.1, lines:1});
        K.t(x + 18, y + 38, w - 36, 36, r.l, {max:22, min:10, font:'head', wt:900, color:tc, lines:1});
        K.t(x + 18, y + 80, w - 36, h - 92, r.d, {max:16, min:9, color:isC ? tc : K.mc, va:'t', lh:1.3});
        if (isC || isT) { K.rect(x + 14, y - 44, w - 28, 32, {fix:isC ? K.good() : K.warn(), r:16}); K.t(x + 14, y - 44, w - 28, 32, isC ? 'We are here' : 'Target', {size:14, wt:900, color:'#FFFFFF', align:'c', lines:1}); }
      });
    }},
    {id:'mm2', name:'Maturity · level bars', draw:function(K, Z, D){
      var n = D.rows.length, gap = 12, h = (Z.h - gap*(n - 1)) / n, cur = (D.o && D.o.cur) || 0;
      D.rows.slice().reverse().forEach(function(r, k){
        var i = n - 1 - k, y = Z.y + k*(h + gap), w = Z.w*(0.45 + 0.55*(i + 1)/n), isC = cur === i + 1;
        K.rect(Z.x, y, w, h, isC ? {pi:0, r:12} : {pi:0, soft:0.14 + 0.1*i, r:12});
        var tc = isC ? DG.u.on(K.col(0)) : K.tc;
        K.t(Z.x + 20, y, 60, h, String(i + 1), {max:Math.min(36, h*0.6), min:12, font:'head', wt:900, color:isC ? tc : K.col(0), lines:1});
        K.t(Z.x + 80, y, 240, h, r.l, {max:20, min:10, font:'head', wt:900, color:tc, lines:1});
        K.t(Z.x + 330, y, w - 350, h, r.d, {max:16, min:9, color:isC ? tc : K.mc, lines:2});
        if (isC) K.t(Z.x + w + 16, y, 200, h, '◀ We are here', {size:16, wt:900, fix:K.good(), lines:1});
      });
    }}
  ]});
})();

/* DS Diagrams — Relationships & hierarchy: org chart, hierarchy, tree, Venn, mind map, concept map, network, relationship */
(function(){
var DG = window.DS_DG; if (!DG) return;
DG.cat('relations', 'Relationships & Hierarchy', '🧩', 'Org charts, trees, Venn, mind maps, networks');
var TF = [{k:'l', n:'Name'}, {k:'d', n:'Role / detail'}, {k:'p', n:'Reports to / parent'}];
function initials(s){ return String(s || '').split(/\s+/).filter(Boolean).slice(0, 2).map(function(w){ return w.charAt(0).toUpperCase(); }).join(''); }

/* ---------------- Org chart ---------------- */
DG.type({id:'org', cat:'relations', name:'Organization Chart', fields:TF, min:2, max:16,
  sample:{rows:[{l:'Sarah Johnson', d:'Chief Executive Officer', p:''}, {l:'David Lee', d:'Chief Operating Officer', p:'Sarah Johnson'}, {l:'Priya Nair', d:'Chief Financial Officer', p:'Sarah Johnson'}, {l:'Tom Becker', d:'Chief Technology Officer', p:'Sarah Johnson'},
    {l:'Ana Silva', d:'Head of Operations', p:'David Lee'}, {l:'Omar Aziz', d:'Head of Finance', p:'Priya Nair'}, {l:'Mia Wong', d:'Head of Engineering', p:'Tom Becker'}, {l:'Leo Martin', d:'Head of Data', p:'Tom Becker'}]},
  ai:'people with role and who they report to (p = manager name; empty for the top); max 3 levels, 4-12 people.',
  designs:[
    {id:'oc1', name:'Org chart · cards', draw:function(K, Z, D){ org(K, Z, D, false); }},
    {id:'oc2', name:'Org chart · avatars', draw:function(K, Z, D){ org(K, Z, D, true); }}
  ]});
function org(K, Z, D, avatar){
  var T = DG.tree(D.rows), N = DG.treePlace(T), L = T.depth;
  var bw = Math.min(230, Z.w / Math.max(1, T.leaves) - 18), bh = avatar ? Math.min(130, (Z.h - 40*(L - 1)) / L) : Math.min(92, (Z.h - 46*(L - 1)) / L), vg = L > 1 ? (Z.h - bh*L) / (L - 1) : 0;
  N.forEach(function(n){ n.x = Z.x + n.a*Z.w - bw/2; n.y = Z.y + n.d*(bh + vg); });
  N.forEach(function(n){ if (!n.kids.length) return; var my = n.y + bh + vg/2; K.line(n.x + bw/2, n.y + bh, n.x + bw/2, my, {color:K.mc, lw:2});
    var xs = n.kids.map(function(k){ return k.x + bw/2; }); K.line(Math.min.apply(null, xs), my, Math.max.apply(null, xs), my, {color:K.mc, lw:2});
    n.kids.forEach(function(k){ K.line(k.x + bw/2, my, k.x + bw/2, k.y, {color:K.mc, lw:2}); }); });
  N.forEach(function(n){
    var top = n.d === 0, pi = top ? 0 : (n.d === 1 ? 1 + (n.i % 3) : 0);
    if (avatar) {
      var c = K.card(n.x, n.y + 24, bw, bh - 24, {hl:top}), r = 24;
      K.circ(n.x + bw/2, n.y + 24, r + 4, {fill:K.bg}); K.circ(n.x + bw/2, n.y + 24, r, {pi:top ? 1 : 0});
      K.tOn(n.x + bw/2 - r, n.y, 2*r, 2*r, initials(n.l), top ? 1 : 0, {size:16, wt:900, align:'c'});
      K.t(n.x + 10, n.y + 54, bw - 20, (bh - 54)*0.45, n.l, {max:17, min:9, wt:800, font:'head', color:c.tc, align:'c', lines:1});
      K.t(n.x + 10, n.y + 54 + (bh - 54)*0.45, bw - 20, (bh - 54)*0.5, n.r.d, {max:14, min:8, color:c.mc, align:'c', va:'t', lines:2});
    } else {
      if (top) K.rect(n.x, n.y, bw, bh, {pi:0, r:12}); else { K.card(n.x, n.y, bw, bh, {}); K.rect(n.x, n.y, bw, 5, {pi:n.d === 1 ? pi : 0, r:2}); }
      var tc = top ? DG.u.on(K.col(0)) : K.tc, mc = top ? DG.u.on(K.col(0)) : K.mc;
      K.t(n.x + 12, n.y + 8, bw - 24, bh*0.48, n.l, {max:18, min:9, wt:800, font:'head', color:tc, align:'c', va:'b', lines:1});
      K.t(n.x + 12, n.y + bh*0.52 + 4, bw - 24, bh*0.44, n.r.d, {max:14, min:8, color:mc, align:'c', va:'t', lines:2, op:top ? 0.9 : 1});
    }
  });
}

/* ---------------- Hierarchy (pyramid of levels) ---------------- */
DG.type({id:'hierarchy', cat:'relations', name:'Hierarchy Chart', fields:[{k:'l', n:'Level'}, {k:'d', n:'Description'}], min:3, max:6,
  sample:{rows:[{l:'Vision', d:'Be the most trusted logistics partner in Europe'}, {l:'Strategy', d:'Digital-first service, 3 growth markets'}, {l:'Objectives', d:'+20% revenue, NPS 60, 98% on-time'}, {l:'Initiatives', d:'Tracking app, green fleet, partner network'}, {l:'Tasks', d:'Team plans, sprints and weekly reviews'}]},
  ai:'3-5 levels from top (most important) to bottom: l = level name, d = one short sentence.',
  designs:[
    {id:'hi1', name:'Hierarchy · pyramid', draw:function(K, Z, D){
      var n = D.rows.length, pw = Math.min(Z.w*0.5, Z.h*1.1), gap = 6, h = (Z.h - gap*(n - 1)) / n, cx = Z.x + pw/2;
      D.rows.forEach(function(r, i){
        var y = Z.y + i*(h + gap), w1 = pw*(i)/n, w2 = pw*(i + 1)/n, sf = 1 - 0.6*i/Math.max(1, n - 1);
        K.poly([[cx - w1/2, y], [cx + w1/2, y], [cx + w2/2, y + h], [cx - w2/2, y + h]], {pi:0, soft:sf});
        K.tOn(cx - w2/2 + 10, y + h*0.3, w2 - 20, h*0.7, r.l, 0, {max:18, min:8, wt:800, align:'c', lines:1}, sf);
        K.line(cx + (w1 + w2)/4 + 10, y + h/2, Z.x + pw + 40, y + h/2, {color:K.ln, lw:1.2, dash:true});
        K.circ(Z.x + pw + 44, y + h/2, 5, {pi:0});
        K.t(Z.x + pw + 60, y, Z.w - pw - 60, h*0.46, r.l, {max:20, min:10, font:'head', wt:900, pi:0, va:'b', lines:1});
        K.t(Z.x + pw + 60, y + h*0.5, Z.w - pw - 60, h*0.48, r.d, {max:16, min:9, color:K.mc, va:'t', lines:2});
      });
    }},
    {id:'hi2', name:'Hierarchy · stacked levels', draw:function(K, Z, D){
      var n = D.rows.length, gap = 10, h = (Z.h - gap*(n - 1)) / n;
      D.rows.forEach(function(r, i){
        var inset = (Z.w*0.18)*i/Math.max(1, n - 1), y = Z.y + i*(h + gap), w = Z.w - 2*inset, sf = 1 - 0.55*i/Math.max(1, n - 1);
        K.rect(Z.x + inset, y, w, h, {pi:0, soft:sf, r:12});
        K.tOn(Z.x + inset + 24, y, 220, h, r.l, 0, {max:21, min:10, font:'head', wt:900, lines:1}, sf);
        K.tOn(Z.x + inset + 250, y, w - 274, h, r.d, 0, {max:16, min:9, lines:2}, sf);
      });
    }}
  ]});

/* ---------------- Tree diagram ---------------- */
DG.type({id:'tree', cat:'relations', name:'Tree Diagram', fields:[{k:'l', n:'Node'}, {k:'p', n:'Parent node'}, {k:'d', n:'Label on branch'}], min:3, max:16,
  sample:{rows:[{l:'Grow revenue', p:''}, {l:'More customers', p:'Grow revenue'}, {l:'Higher value', p:'Grow revenue'}, {l:'New regions', p:'More customers'}, {l:'Referral scheme', p:'More customers'}, {l:'Premium tier', p:'Higher value'}, {l:'Add-on services', p:'Higher value'}, {l:'Annual billing', p:'Higher value'}]},
  ai:'a goal broken into branches: one root, children with p = parent node text; 2-3 levels, max 12 nodes.',
  designs:[
    {id:'tr1', name:'Tree · left to right', draw:function(K, Z, D){ DG.drawTree(K, Z, D, true); }},
    {id:'tr2', name:'Tree · top down', draw:function(K, Z, D){ DG.drawTree(K, Z, D, false); }}
  ]});

/* ---------------- Venn ---------------- */
DG.type({id:'venn', cat:'relations', name:'Venn Diagram', fields:[{k:'l', n:'Set'}, {k:'d', n:'Description'}], min:2, max:3,
  sample:{rows:[{l:'Desirable', d:'Customers want it'}, {l:'Feasible', d:'We can build it'}, {l:'Viable', d:'It makes money'}], ax:{x:'Innovation'}},
  ai:'2 or 3 overlapping sets: l = set name (1-2 words), d = short description; ax.x = what sits in the overlap (1-2 words).',
  designs:[
    {id:'vn1', name:'Venn · overlapping circles', draw:function(K, Z, D){
      var n = Math.min(3, D.rows.length), cx = Z.x + Z.w*0.42, cy = Z.y + Z.h/2, R = n === 3 ? Math.min(Z.h*0.3, Z.w*0.16) : Math.min(Z.h*0.42, Z.w*0.2);
      var C = n === 3 ? [[cx, cy - R*0.55], [cx - R*0.62, cy + R*0.5], [cx + R*0.62, cy + R*0.5]] : [[cx - R*0.55, cy], [cx + R*0.55, cy]];
      C.forEach(function(c, i){ K.circ(c[0], c[1], R, {pi:i, op:0.55}); });
      C.forEach(function(c, i){ var dx = c[0] - cx, dy = c[1] - (n === 3 ? cy + R*0.15 : cy), L = Math.hypot(dx, dy) || 1, px = c[0] + dx/L*R*0.45, py = c[1] + dy/L*R*0.45;
        K.t(px - R*0.6, py - 18, R*1.2, 36, D.rows[i].l, {max:20, min:10, font:'head', wt:900, color:K.tc, align:'c', lines:1}); });
      K.t(cx - R*0.45, (n === 3 ? cy + R*0.1 : cy) - 18, R*0.9, 36, D.ax.x || '', {max:17, min:9, wt:900, color:K.tc, align:'c', lines:2});
      var lx = Z.x + Z.w*0.72, lw = Z.x + Z.w - lx, rh = Z.h / n;
      D.rows.slice(0, n).forEach(function(r, i){ var y = Z.y + rh*i; K.circ(lx + 14, y + rh/2 - 10, 12, {pi:i});
        K.t(lx + 36, y + rh*0.2, lw - 36, 32, r.l, {max:20, min:10, font:'head', wt:900, pi:i, lines:1});
        K.t(lx + 36, y + rh*0.2 + 34, lw - 36, rh*0.6 - 34, r.d, {max:16, min:9, color:K.mc, va:'t', lines:3}); });
    }}
  ]});

/* ---------------- Mind map ---------------- */
DG.type({id:'mindmap', cat:'relations', name:'Mind Map', fields:[{k:'l', n:'Idea'}, {k:'p', n:'Parent idea'}], min:3, max:20,
  sample:{rows:[{l:'Product launch', p:''}, {l:'Audience', p:'Product launch'}, {l:'Channels', p:'Product launch'}, {l:'Message', p:'Product launch'}, {l:'Budget', p:'Product launch'}, {l:'SMEs', p:'Audience'}, {l:'IT managers', p:'Audience'}, {l:'LinkedIn', p:'Channels'}, {l:'Webinars', p:'Channels'}, {l:'Save 10 hours a week', p:'Message'}, {l:'$120K', p:'Budget'}]},
  ai:'one central idea, 3-6 branches (p = central idea) and 1-3 leaves per branch (p = branch).',
  designs:[
    {id:'mm3', name:'Mind map · radial', draw:function(K, Z, D){
      var T = DG.tree(D.rows), root = T.roots[0]; if (!root) return;
      var cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, B = root.kids, nb = B.length, Rbx = Z.w*0.24, Rby = Z.h*0.36;
      B.forEach(function(b, i){
        var a = 360*i/nb + (nb === 2 ? 90 : 0) + (nb === 4 ? 45 : 0), t0 = (a - 90)*Math.PI/180, p = [cx + Rbx*Math.cos(t0), cy + Rby*Math.sin(t0)], right = p[0] >= cx - 2;
        K.path([['M', cx, cy], ['Q', (cx + p[0])/2, cy, p[0], p[1]]], {spi:i, lw:4});
        var nl = b.kids.length;
        b.kids.forEach(function(l, k){
          var lx = right ? p[0] + 130 : p[0] - 130, ly = p[1] + (k - (nl - 1)/2)*44;
          K.path([['M', p[0], p[1]], ['C', (p[0] + lx)/2, p[1], (p[0] + lx)/2, ly, lx, ly]], {spi:i, lw:2});
          var tw = Math.min(260, right ? Z.x + Z.w - lx - 10 : lx - Z.x - 10);
          K.t(right ? lx + 8 : lx - 8 - tw, ly - 18, tw, 36, l.l, {max:16, min:9, color:K.txt, wt:600, align:right ? 'l' : 'r', lines:2});
          K.circ(lx, ly, 5, {pi:i});
        });
        var bw = 170, bh = 50;
        K.rect(p[0] - bw/2, p[1] - bh/2, bw, bh, {pi:i, r:bh/2});
        K.tOn(p[0] - bw/2 + 10, p[1] - bh/2, bw - 20, bh, b.l, i, {max:17, min:9, wt:800, align:'c', lines:2});
      });
      K.circ(cx, cy, 78, {pi:0, soft:0.25}); K.circ(cx, cy, 66, {pi:0});
      K.tOn(cx - 60, cy - 40, 120, 80, root.l, 0, {max:20, min:10, font:'head', wt:900, align:'c', lines:3});
    }}
  ]});

/* ---------------- Concept map / network / relationship (links) ---------------- */
var LF = [{k:'a', n:'From'}, {k:'b', n:'To'}, {k:'l', n:'Link label'}];
function nodesOf(D){ var N = []; D.rows.forEach(function(r){ [r.a, r.b].forEach(function(x){ if (x && N.indexOf(x) < 0) N.push(x); }); }); return N; }
DG.type({id:'concept', cat:'relations', name:'Concept Map', fields:LF, min:2, max:16,
  sample:{rows:[{a:'Customer data', b:'Personalisation', l:'enables'}, {a:'Personalisation', b:'Engagement', l:'drives'}, {a:'Engagement', b:'Revenue', l:'grows'}, {a:'Customer data', b:'Privacy rules', l:'must follow'}, {a:'Privacy rules', b:'Trust', l:'builds'}, {a:'Trust', b:'Engagement', l:'increases'}]},
  ai:'4-10 links between concepts: a = from concept, b = to concept, l = verb phrase (1-2 words).',
  designs:[
    {id:'cm1', name:'Concept map · labelled links', draw:function(K, Z, D){
      var N = nodesOf(D), n = N.length, cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, rx = Z.w/2 - 120, ry = Z.h/2 - 44, P = {}, bw = 200, bh = 58;
      N.forEach(function(x, i){ var a = 360*i/n; var t = (a - 90)*Math.PI/180; P[x] = [cx + rx*Math.cos(t), cy + ry*Math.sin(t)]; });
      D.rows.forEach(function(r){ var a = P[r.a], b = P[r.b]; if (!a || !b) return;
        var dx = b[0] - a[0], dy = b[1] - a[1], L = Math.hypot(dx, dy) || 1, ux = dx/L, uy = dy/L;
        var ex = Math.min(bw/2/Math.abs(ux || 1e-6), bh/2/Math.abs(uy || 1e-6)) + 4;
        K.arrow(a[0] + ux*ex, a[1] + uy*ex, b[0] - ux*ex, b[1] - uy*ex, {color:K.mc, lw:2, head:10});
        if (r.l) { var mx = (a[0] + b[0])/2, my = (a[1] + b[1])/2; K.rect(mx - 58, my - 14, 116, 28, {fill:K.bg, r:14}); K.t(mx - 58, my - 14, 116, 28, r.l, {max:14, min:8, wt:700, pi:0, align:'c', italic:true, lines:1}); } });
      N.forEach(function(x, i){ var p = P[x]; K.rect(p[0] - bw/2, p[1] - bh/2, bw, bh, {pi:i, soft:0.18, r:14, line:K.col(i), lw:2}); K.t(p[0] - bw/2 + 10, p[1] - bh/2, bw - 20, bh, x, {max:17, min:9, wt:800, color:K.tc, align:'c', lines:2}); });
    }}
  ]});
DG.type({id:'network', cat:'relations', name:'Network Diagram', fields:LF, min:2, max:20,
  sample:{rows:[{a:'Platform', b:'Payments'}, {a:'Platform', b:'Identity'}, {a:'Platform', b:'Analytics'}, {a:'Platform', b:'Messaging'}, {a:'Platform', b:'Storage'}, {a:'Payments', b:'Bank API'}, {a:'Identity', b:'SSO'}, {a:'Analytics', b:'Data lake'}, {a:'Messaging', b:'Email'}, {a:'Messaging', b:'SMS'}]},
  ai:'connections between systems or people: a = hub or node, b = connected node (l optional); one main hub works best.',
  designs:[
    {id:'nw1', name:'Network · hub and spokes', draw:function(K, Z, D){
      var N = nodesOf(D), deg = {}; D.rows.forEach(function(r){ deg[r.a] = (deg[r.a] || 0) + 1; deg[r.b] = (deg[r.b] || 0) + 1; });
      var hub = N.slice().sort(function(a, b){ return deg[b] - deg[a]; })[0], cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, P = {}; P[hub] = [cx, cy];
      var ring1 = N.filter(function(x){ return x !== hub && D.rows.some(function(r){ return (r.a === hub && r.b === x) || (r.b === hub && r.a === x); }); });
      var ring2 = N.filter(function(x){ return x !== hub && ring1.indexOf(x) < 0; });
      var R1 = Math.min(Z.h*0.24, Z.w*0.16), R2x = Z.w/2 - 90, R2y = Z.h/2 - 34, ang1 = {};
      ring1.forEach(function(x, i){ ang1[x] = 360*i/Math.max(1, ring1.length); P[x] = DG.pol(cx, cy, R1, ang1[x]); });
      var kids = {}, orphans = [];
      ring2.forEach(function(x){ var par = D.rows.filter(function(r){ return (r.b === x && ang1[r.a] != null) || (r.a === x && ang1[r.b] != null); })[0]; if (par) { var pn = par.a === x ? par.b : par.a; (kids[pn] = kids[pn] || []).push(x); } else orphans.push(x); });
      var spread = 360 / Math.max(1, ring1.length) * 0.8;
      Object.keys(kids).forEach(function(pn){ var ks = kids[pn], m = ks.length; ks.forEach(function(x, k){ var a = ang1[pn] + (m > 1 ? (k/(m - 1) - 0.5)*Math.min(spread, 26*m) : 0), t = (a - 90)*Math.PI/180; P[x] = [cx + R2x*Math.cos(t), cy + R2y*Math.sin(t)]; }); });
      orphans.forEach(function(x, k){ var t = ((360*k/Math.max(1, orphans.length) + 15) - 90)*Math.PI/180; P[x] = [cx + R2x*Math.cos(t), cy + R2y*Math.sin(t)]; });
      D.rows.forEach(function(r){ var a = P[r.a], b = P[r.b]; if (a && b) K.line(a[0], a[1], b[0], b[1], {color:K.ln, lw:2.5}); });
      N.forEach(function(x){ var p = P[x], isH = x === hub, r1 = ring1.indexOf(x) >= 0, R = isH ? 70 : (r1 ? 46 : 32), pi = isH ? 0 : (r1 ? 1 + ring1.indexOf(x) % 4 : 0);
        K.circ(p[0], p[1], R + 5, {fill:K.bg}); K.circ(p[0], p[1], R, isH || r1 ? {pi:pi} : {pi:0, soft:0.22});
        if (isH || r1) K.tOn(p[0] - R + 6, p[1] - R*0.6, 2*R - 12, R*1.2, x, pi, {max:isH ? 20 : 15, min:8, wt:800, align:'c', lines:2});
        else K.t(p[0] - 90, p[1] < cy - 5 ? p[1] - R - 28 : p[1] + R + 2, 180, 26, x, {max:14, min:8, wt:700, color:K.tc, align:'c', lines:1}); });
    }}
  ]});
DG.type({id:'relation', cat:'relations', name:'Relationship Diagram', fields:[{k:'l', n:'Entity'}, {k:'d', n:'Relationship to the centre'}], min:2, max:8,
  sample:{rows:[{l:'Customers', d:'Buy products, give feedback'}, {l:'Suppliers', d:'Provide parts on 30-day terms'}, {l:'Investors', d:'Fund growth, expect 20% return'}, {l:'Regulators', d:'Set safety and data rules'}, {l:'Employees', d:'Deliver service, share in profit'}, {l:'Community', d:'Local jobs and sponsorships'}], ax:{x:'Our company'}},
  ai:'3-7 stakeholders around a central organisation: l = stakeholder, d = the relationship (max 8 words); ax.x = the centre name.',
  designs:[
    {id:'rl1', name:'Relationship · stakeholders around centre', draw:function(K, Z, D){
      var n = D.rows.length, cx = Z.x + Z.w/2, cy = Z.y + Z.h/2, R = Math.min(Z.h*0.36, Z.w*0.18), nr = Math.min(56, R*0.4);
      D.rows.forEach(function(r, i){
        var a = 360*i/n, p = DG.pol(cx, cy, R, a), right = p[0] >= cx - 5;
        K.arrow(cx + (p[0] - cx)*0.36, cy + (p[1] - cy)*0.36, cx + (p[0] - cx)*0.76, cy + (p[1] - cy)*0.76, {pi:i, lw:2.5, head:10, both:true});
        K.circ(p[0], p[1], nr, {pi:i});
        K.tOn(p[0] - nr, p[1] - nr, 2*nr, 2*nr, initials(r.l), i, {size:nr*0.6, wt:900, align:'c', font:'head'});
        var bw = Z.w/2 - R - nr - 40, bx = right ? p[0] + nr + 16 : p[0] - nr - 16 - bw;
        K.t(bx, p[1] - 36, bw, 30, r.l, {max:19, min:10, font:'head', wt:900, pi:i, align:right ? 'l' : 'r', lines:1});
        K.t(bx, p[1] - 4, bw, 50, r.d, {max:15, min:9, color:K.mc, align:right ? 'l' : 'r', va:'t', lines:2});
      });
      var cr = R*0.34; K.circ(cx, cy, cr + 10, {pi:0, soft:0.2}); K.circ(cx, cy, cr, {pi:0});
      K.tOn(cx - cr + 8, cy - cr*0.6, 2*cr - 16, cr*1.2, D.ax.x || 'Us', 0, {max:20, min:9, font:'head', wt:900, align:'c', lines:2});
    }}
  ]});
})();

/* ============================================================
   DS Diagrams — editor UI
   - Add Slide → "See all categories" (gallery of every diagram in the deck's theme)
   - Diagram editor (data table, design switcher, live preview)
   - "Edit diagram" button on diagram slides
   - During generation: clear matches (SWOT, process, funnel…) become diagrams in place
   - Classic download: diagram shapes ride along with the slide
   CFG (printed by PHP): window.DS_DG_CFG = {rest, nonce, cost, auto}
   ============================================================ */
(function(){
var DG = window.DS_DG; if (!DG || DG.ui) return;
DG.ui = 1;
var CFG = window.DS_DG_CFG || {}, esc = DG.esc;
function $(id){ return document.getElementById(id); }
function toast(m, k){ if (window.dspToast) window.dspToast(m, k || 'success'); }
function cost(){ var c = parseInt(CFG.cost, 10); if (!c) { var el = $('dsp-addslide-cost'); c = el ? parseInt(el.textContent, 10) : 0; } return c || 5; }
function tplActive(){ try { return window.DS_TPL_ACTIVE ? window.DS_TPL_ACTIVE() : null; } catch(e){ return null; } }
function theme(){ var el = $('dsp-design'), k = el && el.value ? el.value : 'navy-pro', TH = window.TH || {}; return TH[k] || TH['navy-pro'] || {bg:'#1B2A4A', tc:'#FFFFFF', bc:'#C8D8F0', ac:'#4A9EFF'}; }
function renderAny(s, design){
  DG.hookNow();
  var T = tplActive();
  if (T) { try { return T.render(s, design, -1); } catch(e){ if (window.console) console.warn('[DS Diagrams]', e); } }
  return DG.renderClassic(s, theme(), design, -1);
}
function curIdx(){ var S = window.S; if (S && typeof S.idx === 'number' && S.idx >= 0) return S.idx; return (window.G && G.activeSlideIdx) || 0; }
function slideAt(i){ var a = DG.deckArrays(); return a.length ? a[0][i] : null; }
function refresh(idx){
  if (window.renderEditList) try { window.renderEditList(); } catch(e){}
  if (window.dspRenderThumbs) try { window.dspRenderThumbs(); } catch(e){}
  if (window.dspSelectThumb) try { window.dspSelectThumb(idx); } catch(e){}
  setTimeout(function(){ if (window.applyCanvas && window.S) { S.idx = idx; window.applyCanvas(idx, (S.choices && S.choices['slide_'+idx]) || 'v1'); } }, 120);
}
function setChoice(idx, v){ if (!window.S) return; S.choices = S.choices || {}; S.choices['slide_'+idx] = v || 'v1'; try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch(e){} }
function insertSlide(at, slide){
  DG.deckArrays().forEach(function(a, k){ a.splice(at, 0, k ? JSON.parse(JSON.stringify(slide)) : slide); });
  if (window.S && S.choices) {
    var nc = {};
    Object.keys(S.choices).forEach(function(key){ var m = key.match(/^slide_(\d+)$/); if (!m) { nc[key] = S.choices[key]; return; } var n = +m[1]; nc['slide_' + (n >= at ? n + 1 : n)] = S.choices[key]; });
    S.choices = nc;
  }
}
function sampleSlide(typeId, full){
  var t = DG.types[typeId];
  return {type:'chart', title:t.name, subtitle:'', content:DG.designs[full] && DG.designs[full].side ? ['Growth: Up 24% on last year', 'Focus: Mid-market and APAC', 'Risk: Pricing pressure'] : [], dg:DG.newData(typeId, full)};
}

/* ---------------- CSS ---------------- */
var CSS = ''
+ '#dsdg-addbtn{display:flex;align-items:center;gap:14px;width:100%;margin-top:10px;padding:14px 16px;border-radius:10px;border:1px dashed rgba(138,180,248,.55);background:rgba(26,115,232,.08);color:#e8eaed;text-align:left;cursor:pointer;font-family:inherit;text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsdg-addbtn:hover{background:rgba(26,115,232,.16);border-color:#8ab4f8;}#dsdg-addbtn i{font-style:normal;font-size:26px;}#dsdg-addbtn b{display:block;font-size:14px;}#dsdg-addbtn span{display:block;font-size:12px;color:#9ca3af;margin-top:2px;}'
+ '.dsdg-m{position:fixed;inset:0;z-index:120000;background:rgba(0,0,0,.66);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}'
+ '.dsdg-m.on{display:flex;}.dsdg-m *{box-sizing:border-box;text-transform:none!important;letter-spacing:normal!important;}'
+ '.dsdg-card{width:1180px;max-width:96vw;height:88vh;display:flex;flex-direction:column;background:#161821;border:1px solid #2a2a2a;border-radius:14px;overflow:hidden;box-shadow:0 14px 60px rgba(0,0,0,.6);color:#e8eaed;}'
+ '.dsdg-head{display:flex;align-items:center;gap:14px;padding:14px 20px;background:#0f1117;border-bottom:1px solid #262a36;}.dsdg-head h3{margin:0;font-size:16px;font-weight:700;flex:1;color:#e8eaed;}'
+ '.dsdg-x{background:none;border:0;color:#9ca3af;font-size:24px;cursor:pointer;line-height:1;padding:0 4px;}'
+ '.dsdg-search{width:260px;padding:8px 12px;border-radius:8px;border:1px solid #2f3340;background:#11131b;color:#e8eaed;font:13px inherit;font-family:inherit;}'
+ '.dsdg-main{flex:1;min-height:0;display:flex;}'
+ '.dsdg-nav{width:240px;flex:0 0 240px;overflow:auto;padding:12px;border-right:1px solid #262a36;background:#12141c;}'
+ '.dsdg-nav button{display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;margin-bottom:4px;border-radius:8px;border:0;background:transparent;color:#c3c8d4;font:600 13px/1.3 inherit;font-family:inherit;text-align:left;cursor:pointer;}'
+ '.dsdg-nav button:hover{background:#1c2030;}.dsdg-nav button.on{background:#1a73e8;color:#fff;}.dsdg-nav i{font-style:normal;font-size:17px;width:22px;text-align:center;}.dsdg-nav small{margin-left:auto;font-size:11px;opacity:.7;}'
+ '.dsdg-body{flex:1;min-width:0;overflow:auto;padding:18px 20px;}'
+ '.dsdg-sec h4{margin:4px 0 10px;font-size:14px;color:#e8eaed;}.dsdg-sec p{margin:-6px 0 12px;font-size:12px;color:#8b93a7;}'
+ '.dsdg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px;margin-bottom:22px;}'
+ '.dsdg-tile{cursor:pointer;border-radius:10px;overflow:hidden;border:1px solid #2a2d38;background:#0f1117;transition:transform .12s,border-color .12s;}'
+ '.dsdg-tile:hover{transform:translateY(-2px);border-color:#8ab4f8;}'
+ '.dsdg-thumb{position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;pointer-events:none;font-size:4px;}'
+ '.dsdg-tile b{display:block;padding:8px 10px;font-size:12.5px;font-weight:600;color:#c3c8d4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}'
+ '.dsdg-foot{display:flex;align-items:center;gap:10px;padding:12px 20px;background:#0f1117;border-top:1px solid #262a36;}'
+ '.dsdg-b{padding:10px 18px;border-radius:8px;border:1px solid #2f3340;background:#15171f;color:#c3c8d4;font:600 13px/1 inherit;font-family:inherit;cursor:pointer;}'
+ '.dsdg-p{background:#1a73e8;border-color:#1a73e8;color:#fff;}.dsdg-p[disabled]{opacity:.6;cursor:wait;}'
+ '.dsdg-cost{font-size:12px;color:#9ca3af;}'
+ '.dsdg-cfg{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr);gap:22px;}'
+ '.dsdg-big{position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;border-radius:10px;border:1px solid #2a2d38;font-size:7px;}'
+ '.dsdg-lbl{display:block;font-size:12px;font-weight:700;color:#9ca3af;margin:0 0 6px;}'
+ '.dsdg-in,.dsdg-ta{width:100%;padding:10px 12px;border-radius:8px;border:1px solid #2f3340;background:#11131b;color:#e8eaed;font:13px/1.45 inherit;font-family:inherit;margin-bottom:14px;}'
+ '.dsdg-ta{min-height:110px;resize:vertical;}'
+ '.dsdg-hint{font-size:12px;color:#8b93a7;line-height:1.5;margin:0 0 14px;}'
+ '.dsdg-tbl{width:100%;border-collapse:separate;border-spacing:0 6px;font-size:12.5px;}'
+ '.dsdg-tbl th{text-align:left;font-weight:700;color:#8b93a7;font-size:11.5px;padding:0 6px;white-space:nowrap;}'
+ '.dsdg-tbl td{padding:0 3px;}.dsdg-tbl input,.dsdg-tbl select{width:100%;min-width:60px;padding:7px 8px;border-radius:6px;border:1px solid #2f3340;background:#11131b;color:#e8eaed;font:12.5px inherit;font-family:inherit;}'
+ '.dsdg-tbl .n{width:84px;}.dsdg-ic{background:none;border:0;color:#8b93a7;cursor:pointer;font-size:15px;padding:4px 6px;}.dsdg-ic:hover{color:#fca5a5;}'
+ '.dsdg-mini{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;}'
+ '.dsdg-mini .dsdg-tile.on{border-color:#1a73e8;box-shadow:0 0 0 2px #1a73e8;}'
+ '.dsdg-tabs{display:flex;gap:6px;margin-bottom:14px;}.dsdg-tabs button{padding:8px 14px;border-radius:8px;border:1px solid #2f3340;background:#15171f;color:#c3c8d4;font:600 12.5px inherit;font-family:inherit;cursor:pointer;}.dsdg-tabs button.on{background:#1a73e8;border-color:#1a73e8;color:#fff;}'
+ '#dsdg-edit{position:absolute;top:12px;right:12px;z-index:50;padding:8px 14px;border-radius:999px;border:0;background:#1a73e8;color:#fff;font:700 12.5px/1 -apple-system,Segoe UI,Arial,sans-serif;cursor:pointer;box-shadow:0 6px 18px rgba(0,0,0,.35);text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsdg-edit:hover{background:#1765cc;}'
+ '.dsdg-fillbar{display:flex;align-items:center;gap:10px;justify-content:space-between;margin:0 0 12px;padding:10px 12px;border:1px dashed rgba(138,180,248,.5);border-radius:10px;background:rgba(26,115,232,.08);font:13px/1.35 -apple-system,Segoe UI,Arial,sans-serif;color:#c8d3e6;}.dsdg-fillbar .dsdg-b{padding:8px 14px;white-space:nowrap;}'
+ '#dsdg-box{position:absolute;z-index:40;cursor:pointer;border-radius:6px;}'
+ '#dsdg-box:hover{outline:2px dashed rgba(138,180,248,.55);outline-offset:4px;}'
+ '#dsdg-box .dsdg-ebtn{position:absolute;top:6px;right:6px;display:none;padding:6px 12px;border-radius:999px;border:0;background:#1a73e8;color:#fff;font:600 12px/1 -apple-system,Segoe UI,Arial,sans-serif;letter-spacing:.06em;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.35);}'
+ '#dsdg-box:hover .dsdg-ebtn{display:block;}#dsdg-box .dsdg-ebtn:hover{background:#1765cc;}';
function injectCss(){ if ($('dsdg-css')) return; var st = document.createElement('style'); st.id = 'dsdg-css'; st.textContent = CSS; document.head.appendChild(st); }

function modal(id){
  var m = $(id); if (m) return m;
  injectCss();
  m = document.createElement('div'); m.id = id; m.className = 'dsdg-m';
  m.innerHTML = '<div class="dsdg-card"><div class="dsdg-head"></div><div class="dsdg-main"></div><div class="dsdg-foot"></div></div>';
  m.addEventListener('mousedown', function(e){ if (e.target === m && !m.__busy) close(id); });
  document.body.appendChild(m);
  return m;
}
function close(id){ var m = $(id); if (m) m.classList.remove('on'); }
function part(m, cls){ return m.querySelector('.' + cls); }

/* ============================================================
   GALLERY
   ============================================================ */
var GAL = {cat:null, q:'', addText:''};
DG.openGallery = function(text){
  GAL.addText = text || ''; GAL.q = '';
  var m = modal('dsdg-gal');
  part(m, 'dsdg-head').innerHTML = '<h3>&#128450;&#65039; All diagram categories · ' + Object.keys(DG.designs).length + ' designs in your theme</h3><input class="dsdg-search" id="dsdg-q" placeholder="Search (e.g. SWOT, Gantt, funnel)"/><button type="button" class="dsdg-x" id="dsdg-gx">&#10005;</button>';
  $('dsdg-gx').onclick = function(){ close('dsdg-gal'); };
  $('dsdg-q').oninput = function(){ GAL.q = this.value.trim().toLowerCase(); drawGrid(); };
  var nav = '<div class="dsdg-nav">';
  DG.cats.forEach(function(c){ var n = 0; c.types.forEach(function(t){ n += DG.types[t].designs.length; }); nav += '<button type="button" data-cat="' + c.id + '"><i>' + c.icon + '</i>' + esc(c.name) + '<small>' + n + '</small></button>'; });
  nav += '</div><div class="dsdg-body" id="dsdg-gbody"></div>';
  part(m, 'dsdg-main').innerHTML = nav;
  Array.prototype.forEach.call(part(m, 'dsdg-nav').querySelectorAll('button'), function(b){ b.onclick = function(){ GAL.cat = b.getAttribute('data-cat'); GAL.q = ''; $('dsdg-q').value = ''; drawGrid(); }; });
  part(m, 'dsdg-foot').innerHTML = '<span class="dsdg-cost">Pick a design — it uses your current theme. AI writes the content for your topic (' + cost() + ' credits, same as Add Slide). Everything stays editable.</span>';
  GAL.cat = GAL.cat || DG.cats[0].id;
  m.classList.add('on');
  drawGrid();
};
function drawGrid(){
  var m = $('dsdg-gal'), body = $('dsdg-gbody'); if (!m || !body) return;
  Array.prototype.forEach.call(part(m, 'dsdg-nav').querySelectorAll('button'), function(b){ b.classList.toggle('on', !GAL.q && b.getAttribute('data-cat') === GAL.cat); });
  var h = '', q = GAL.q;
  var cats = q ? DG.cats : DG.cats.filter(function(c){ return c.id === GAL.cat; });
  cats.forEach(function(c){
    var tiles = '';
    c.types.forEach(function(tid){
      var t = DG.types[tid];
      t.designs.forEach(function(d){
        if (q && (t.name + ' ' + d.name + ' ' + c.name).toLowerCase().indexOf(q) < 0) return;
        tiles += '<div class="dsdg-tile" data-d="' + d.full + '"><div class="dsdg-thumb">' + renderAny(sampleSlide(tid, d.full), d.full) + '</div><b>' + esc(d.name) + '</b></div>';
      });
    });
    if (tiles) h += '<div class="dsdg-sec"><h4>' + c.icon + ' ' + esc(c.name) + '</h4><p>' + esc(c.desc) + '</p><div class="dsdg-grid">' + tiles + '</div></div>';
  });
  body.innerHTML = h || '<p style="color:#8b93a7">No designs match “' + esc(q) + '”.</p>';
  body.scrollTop = 0;
  Array.prototype.forEach.call(body.querySelectorAll('.dsdg-tile'), function(el){ el.onclick = function(){ configure(el.getAttribute('data-d')); }; });
}
function deckTitle(){ var t = $('dsp-etitle'); if (t && t.textContent.trim()) return t.textContent.trim(); var a = DG.deckArrays()[0]; return a && a[0] ? (a[0].title || '') : ''; }
function configure(full){
  var d = DG.designs[full], t = DG.types[d.type], m = $('dsdg-gal');
  var body = $('dsdg-gbody');
  var topic = GAL.addText || '';
  body.innerHTML = '<div class="dsdg-cfg"><div><div class="dsdg-big" id="dsdg-prev">' + renderAny(sampleSlide(t.id, full), full) + '</div><p class="dsdg-hint" style="margin-top:10px">Preview with sample content. The slide will use your deck’s theme; you can switch design and edit every value afterwards.</p></div>'
    + '<div><span class="dsdg-lbl">' + esc(t.name) + ' · ' + esc(d.name) + '</span>'
    + '<label class="dsdg-lbl" for="dsdg-topic">What should this slide show? <span style="font-weight:400;color:#8b93a7">(optional)</span></label><input class="dsdg-in" id="dsdg-topic" placeholder="Leave empty = a ' + esc(t.name) + ' about your deck’s topic" value="' + esc(topic) + '"/>'
    + '<label class="dsdg-lbl" for="dsdg-notes">Your facts, numbers or items (optional)</label><textarea class="dsdg-ta" id="dsdg-notes" placeholder="Paste anything the diagram must include — the AI keeps your facts and numbers."></textarea>'
    + '<p class="dsdg-hint">Leave both boxes empty and AI writes a ' + esc(t.name) + ' that fits your deck. Type a topic or facts only to steer it. Or skip AI: <b style="color:#c3c8d4">Insert &amp; edit myself</b> adds the diagram as shown (free, no AI) and opens the editor so you type your own values.</p>'
    + '<p class="dsdg-hint">Deck: <b style="color:#c3c8d4">' + esc(deckTitle() || '—') + '</b></p></div></div>';
  part(m, 'dsdg-foot').innerHTML = '<button type="button" class="dsdg-b" id="dsdg-back">&#8592; All designs</button><span style="flex:1"></span><button type="button" class="dsdg-b" id="dsdg-plain">&#9998; Insert &amp; edit myself</button><span class="dsdg-cost">' + cost() + ' credits</span><button type="button" class="dsdg-b dsdg-p" id="dsdg-go">&#10024; Generate with AI</button>';
  $('dsdg-back').onclick = function(){ openAgain(); };
  $('dsdg-go').onclick = function(){ generate(full); };
  $('dsdg-plain').onclick = function(){ insertPlain(full); };
  setTimeout(function(){ var el = $('dsdg-topic'); if (el) el.focus(); }, 50);
}
/* no AI: add the diagram with its sample values, named after the chart, and open the editor */
function insertPlain(full){
  var d = DG.designs[full], t = DG.types[d.type], arr = DG.deckArrays();
  if (!arr.length) { close('dsdg-gal'); return; }
  var s = sampleSlide(t.id, full), topic = ($('dsdg-topic') && $('dsdg-topic').value || '').trim();
  s.title = topic || t.name; s.subtitle = ''; s.content = []; s.dg.v = full; s.dg.t = t.id; s.dg.fillTried = 1;
  var at = Math.min(arr[0].length, curIdx() + 1);
  insertSlide(at, s); setChoice(at, 'v1');
  close('dsdg-gal');
  refresh(at);
  toast('🧩 ' + t.name + ' added — type your own values in the editor.');
  setTimeout(function(){ if (DG.is(slideAt(at))) DG.openEditor(at); }, 700);
}
function openAgain(){ var m = $('dsdg-gal'); part(m, 'dsdg-foot').innerHTML = '<span class="dsdg-cost">Pick a design — it uses your current theme. AI writes the content for your topic (' + cost() + ' credits, same as Add Slide). Everything stays editable.</span>'; drawGrid(); }
function generate(full){
  var d = DG.designs[full], t = DG.types[d.type], btn = $('dsdg-go'), m = $('dsdg-gal');
  var topic = ($('dsdg-topic').value || '').trim(), notes = ($('dsdg-notes').value || '').trim();
  if (!topic && !notes) topic = t.name + (deckTitle() ? ' for the presentation "' + deckTitle() + '"' : '');   /* nothing typed: fit the deck's own topic */
  if (window.G && typeof G.credits === 'number' && G.credits < cost()) { toast('Not enough credits. Need ' + cost() + ' credits.', 'error'); return; }
  btn.disabled = true; btn.innerHTML = 'Generating…'; m.__busy = true;
  var smp = DG.clone(t.sample || {}); smp.title = 'Slide title'; smp.subtitle = 'Key message in one line';
  var lang = $('dsp-lang');
  fetch(CFG.rest || '/wp-json/ds/v1/diagram-slide', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce': window.NONCE || window.DSP_NONCE || CFG.nonce || ''},
    body: JSON.stringify({type:t.id, design:d.id, name:t.name, fields:t.fields, series:!!t.series, groups:t.groups || [], ai:t.ai || '', sample:smp, min:t.min || 2, max:t.max || 12,
      topic:topic, notes:notes, deck_title:deckTitle(), language:lang ? lang.value : 'English'})})
  .then(function(r){ return r.json(); })
  .then(function(res){
    m.__busy = false;
    if (res && res.credits_balance !== undefined && window.G) { G.credits = res.credits_balance; var ce = $('ds-cr-display'); if (ce) ce.textContent = Number(G.credits).toLocaleString(); }
    if (!res || !res.success || !res.slide) { btn.disabled = false; btn.innerHTML = '&#10024; Generate with AI'; toast((res && (res.message || res.error)) || 'Could not create the diagram. Please try again.', 'error'); return; }
    var s = res.slide; s.dg.v = full; s.dg.t = t.id;
    var arr = DG.deckArrays(); if (!arr.length) { close('dsdg-gal'); return; }
    var at = Math.min(arr[0].length, curIdx() + 1);
    insertSlide(at, s); setChoice(at, 'v1');
    close('dsdg-gal');
    refresh(at);
    toast('🧩 ' + t.name + ' slide added (' + (res.cost || cost()) + ' credits used) — click “Edit diagram” to change it.');
  })
  .catch(function(){ m.__busy = false; btn.disabled = false; btn.innerHTML = '&#10024; Generate with AI'; toast('Network error. Try again.', 'error'); });
}

/* ============================================================
   EDITOR
   ============================================================ */
var ED = null;
var OPTS = {cycle:[['o', 'center', 'Centre text']], waterfall:[['o', 'totalLabel', 'Total bar label']], maturity:[['o', 'cur', 'Current level #', 1], ['o', 'target', 'Target level #', 1]], gantt:[['o', 'today', 'Today marker (period #, e.g. 3.5)', 1]],
  venn:[['ax', 'x', 'Overlap label']], relation:[['ax', 'x', 'Centre name']], fishbone:[['ax', 'x', 'Problem (effect)']]};
function usesAx(t){ return t.sample && t.sample.ax && (t.sample.ax.y != null) ; }
DG.openEditor = function(idx){
  var s = slideAt(idx); if (!DG.is(s)) return;
  ED = {idx:idx, dg:DG.clone(s.dg), content:(s.content || []).slice(), tab:'data'};
  DG.fixGroups(ED.dg);
  var m = modal('dsdg-ed');
  part(m, 'dsdg-head').innerHTML = '<h3>&#9998; Edit diagram</h3><button type="button" class="dsdg-x" id="dsdg-ex">&#10005;</button>';
  $('dsdg-ex').onclick = function(){ close('dsdg-ed'); };
  part(m, 'dsdg-main').innerHTML = '<div class="dsdg-body"><div class="dsdg-cfg"><div><div class="dsdg-big" id="dsdg-eprev"></div><p class="dsdg-hint" style="margin-top:10px">Add or remove rows and the diagram re-flows automatically. Title and subtitle are edited directly on the slide.</p></div><div id="dsdg-eform"></div></div></div>';
  part(m, 'dsdg-foot').innerHTML = '<button type="button" class="dsdg-b" id="dsdg-ecancel">Cancel</button><span style="flex:1"></span><button type="button" class="dsdg-b dsdg-p" id="dsdg-eapply">&#10003; Apply changes</button>';
  $('dsdg-ecancel').onclick = function(){ close('dsdg-ed'); };
  $('dsdg-eapply').onclick = applyEdit;
  m.classList.add('on');
  drawForm(); preview();
};
function edSlide(){ var s = slideAt(ED.idx) || {}; var o = {}; for (var k in s) o[k] = s[k]; o.dg = ED.dg; o.content = ED.content; return o; }
var _pv = 0;
function preview(){ clearTimeout(_pv); _pv = setTimeout(function(){ var el = $('dsdg-eprev'); if (el) el.innerHTML = renderAny(edSlide(), ED.dg.v); }, 120); }
function drawForm(){
  var t = DG.types[ED.dg.t], f = $('dsdg-eform'); if (!f || !t) return;
  var h = '<div class="dsdg-tabs"><button type="button" data-tab="data"' + (ED.tab === 'data' ? ' class="on"' : '') + '>Data</button><button type="button" data-tab="design"' + (ED.tab === 'design' ? ' class="on"' : '') + '>Design</button><button type="button" data-tab="points"' + (ED.tab === 'points' ? ' class="on"' : '') + '>Key points</button></div>';
  if (ED.tab === 'design') {
    h += '<div class="dsdg-mini">';
    DG.related({dg:ED.dg}).forEach(function(id){ var d = DG.designs[id]; h += '<div class="dsdg-tile' + (id === ED.dg.v ? ' on' : '') + '" data-d="' + id + '"><div class="dsdg-thumb">' + renderAny(edSlide(), id) + '</div><b>' + esc(d.name) + '</b></div>'; });
    h += '</div>';
  } else if (ED.tab === 'points') {
    h += '<p class="dsdg-hint">Designs marked “+ insights” show up to 4 key points beside the diagram (“Heading: detail”).</p>';
    for (var i = 0; i < 4; i++) h += '<input class="dsdg-in" data-pt="' + i + '" value="' + esc(ED.content[i] || '') + '" placeholder="Point ' + (i + 1) + '"/>';
    h += '<label class="dsdg-lbl">Takeaway strip (optional)</label><input class="dsdg-in" id="dsdg-conc" value="' + esc((slideAt(ED.idx) || {}).conclusion || '') + '"/>';
  } else {
    if (t.series) {
      h += '<label class="dsdg-lbl">Columns (series)</label><div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">';
      ED.dg.cols.forEach(function(c, j){ h += '<span style="display:flex;gap:2px"><input class="dsdg-in" style="width:130px;margin:0" data-col="' + j + '" value="' + esc(c) + '"/>' + (ED.dg.cols.length > 1 ? '<button type="button" class="dsdg-ic" data-delcol="' + j + '" title="Remove column">&#10005;</button>' : '') + '</span>'; });
      h += '<button type="button" class="dsdg-b" id="dsdg-addcol" style="padding:7px 12px">+ Column</button></div>';
    } else if (t.groups || (t.fields || []).some(function(x){ return x.group; })) {
      var gn = ED.dg.cols && ED.dg.cols.length ? ED.dg.cols : (t.groups || []);
      if (gn.length) { h += '<label class="dsdg-lbl">Group names</label><div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">'; gn.forEach(function(c, j){ h += '<input class="dsdg-in" style="width:150px;margin:0" data-gcol="' + j + '" value="' + esc(c) + '"/>'; }); h += '</div>'; }
      var eg = DG.emptyGroups(ED.dg);
      if (eg.length) h += '<div class="dsdg-fillbar"><span>No points yet for <b>' + eg.map(esc).join(', ') + '</b></span><button type="button" class="dsdg-b dsdg-p" id="dsdg-fill"' + (_filling ? ' disabled' : '') + '>' + (_filling ? 'Writing…' : '&#10024; Fill with AI') + '</button></div>';
    }
    if (t.id === 'roadmap' || t.id === 'gantt') {
      h += '<label class="dsdg-lbl">Periods (comma separated)</label><input class="dsdg-in" id="dsdg-periods" value="' + esc((ED.dg.cols || []).join(', ')) + '"/>';
    }
    var ax = [];
    if (usesAx(t)) ax = [['ax', 'x', 'X axis label'], ['ax', 'y', 'Y axis label']];
    (OPTS[t.id] || []).concat(ax).forEach(function(o){ var src = o[0] === 'ax' ? (ED.dg.ax || {}) : (ED.dg.o || {}); h += '<label class="dsdg-lbl">' + esc(o[2]) + '</label><input class="dsdg-in" data-opt="' + o[0] + '.' + o[1] + '"' + (o[3] ? ' data-num="1"' : '') + ' value="' + esc(src[o[1]] == null ? '' : src[o[1]]) + '"/>'; });
    if (hasUnit(t)) h += '<label class="dsdg-lbl">Unit ($, %, K, M, days…)</label><input class="dsdg-in" id="dsdg-unit" style="width:160px" value="' + esc(ED.dg.unit || '') + '"/>';
    h += '<div style="overflow:auto"><table class="dsdg-tbl"><tr><th></th>';
    (t.fields || []).forEach(function(x){ h += '<th>' + esc(x.n) + '</th>'; });
    if (t.series) ED.dg.cols.forEach(function(c){ h += '<th>' + esc(c) + '</th>'; });
    h += '<th></th></tr>';
    ED.dg.rows.forEach(function(r, i){
      h += '<tr><td style="color:#8b93a7;font-size:11px">' + (i + 1) + '</td>';
      (t.fields || []).forEach(function(x){
        var v = r[x.k] == null ? '' : r[x.k];
        if (x.group) { var gn2 = ED.dg.cols && ED.dg.cols.length ? ED.dg.cols : (t.groups || []); h += '<td><select data-r="' + i + '" data-k="' + x.k + '">' + gn2.map(function(g){ return '<option' + (String(v) === g ? ' selected' : '') + '>' + esc(g) + '</option>'; }).join('') + (gn2.indexOf(String(v)) < 0 && v ? '<option selected>' + esc(v) + '</option>' : '') + '</select></td>'; }
        else h += '<td><input' + (x.num ? ' class="n" inputmode="decimal"' : '') + ' data-r="' + i + '" data-k="' + x.k + '" value="' + esc(v) + '"/></td>';
      });
      if (t.series) ED.dg.cols.forEach(function(c, j){ h += '<td><input class="n" inputmode="decimal" data-r="' + i + '" data-sv="' + j + '" value="' + esc(r.v && r.v[j] != null ? r.v[j] : '') + '"/></td>'; });
      h += '<td style="white-space:nowrap"><button type="button" class="dsdg-ic" data-up="' + i + '" title="Move up">&#8593;</button><button type="button" class="dsdg-ic" data-del="' + i + '" title="Delete row">&#128465;</button></td></tr>';
    });
    h += '</table></div><button type="button" class="dsdg-b" id="dsdg-addrow" style="margin-top:6px">+ Add row</button>';
    if (t.max) h += '<span class="dsdg-cost" style="margin-left:10px">' + ED.dg.rows.length + ' of max ' + t.max + '</span>';
  }
  f.innerHTML = h;
  bindForm(f, t);
}
function hasUnit(t){ return t.series || (t.fields || []).some(function(x){ return x.num && /value|time|actual|deals|change|size/i.test(x.n); }) || (t.sample && t.sample.unit); }
function bindForm(f, t){
  Array.prototype.forEach.call(f.querySelectorAll('[data-tab]'), function(b){ b.onclick = function(){ ED.tab = b.getAttribute('data-tab'); drawForm(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('.dsdg-mini .dsdg-tile'), function(el){ el.onclick = function(){ var id = el.getAttribute('data-d'); DG.retarget(ED.dg, id); drawForm(); preview(); if (ED.dg._needFill) edFill(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-pt]'), function(el){ el.oninput = function(){ ED.content[+el.getAttribute('data-pt')] = el.value; ED.content = ED.content.map(function(x){ return x || ''; }); preview(); }; });
  var conc = $('dsdg-conc'); if (conc) conc.oninput = function(){ ED.conc = conc.value; };
  Array.prototype.forEach.call(f.querySelectorAll('[data-r][data-k]'), function(el){ var ev = el.tagName === 'SELECT' ? 'onchange' : 'oninput'; el[ev] = function(){ var r = ED.dg.rows[+el.getAttribute('data-r')]; r[el.getAttribute('data-k')] = el.value; preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-sv]'), function(el){ el.oninput = function(){ var r = ED.dg.rows[+el.getAttribute('data-r')]; r.v = r.v || []; r.v[+el.getAttribute('data-sv')] = el.value; preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-col]'), function(el){ el.oninput = function(){ ED.dg.cols[+el.getAttribute('data-col')] = el.value; preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-gcol]'), function(el){ el.onchange = function(){
    var j = +el.getAttribute('data-gcol'), gn = ED.dg.cols && ED.dg.cols.length ? ED.dg.cols : (t.groups || []).slice(), old = gn[j];
    gn[j] = el.value; ED.dg.cols = gn; ED.dg.rows.forEach(function(r){ if (r.g === old) r.g = el.value; }); drawForm(); preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-delcol]'), function(el){ el.onclick = function(){ var j = +el.getAttribute('data-delcol'); ED.dg.cols.splice(j, 1); ED.dg.rows.forEach(function(r){ if (r.v) r.v.splice(j, 1); }); drawForm(); preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-del]'), function(el){ el.onclick = function(){ ED.dg.rows.splice(+el.getAttribute('data-del'), 1); drawForm(); preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-up]'), function(el){ el.onclick = function(){ var i = +el.getAttribute('data-up'); if (i < 1) return; var a = ED.dg.rows; var x = a[i]; a[i] = a[i-1]; a[i-1] = x; drawForm(); preview(); }; });
  Array.prototype.forEach.call(f.querySelectorAll('[data-opt]'), function(el){ el.oninput = function(){ var p = el.getAttribute('data-opt').split('.'), obj = ED.dg[p[0]] = ED.dg[p[0]] || {}; obj[p[1]] = el.getAttribute('data-num') ? (el.value === '' ? null : parseFloat(el.value)) : el.value; preview(); }; });
  var u = $('dsdg-unit'); if (u) u.oninput = function(){ ED.dg.unit = u.value.trim(); preview(); };
  var pr = $('dsdg-periods'); if (pr) pr.oninput = function(){ ED.dg.cols = pr.value.split(',').map(function(x){ return x.trim(); }).filter(Boolean); preview(); };
  var ac = $('dsdg-addcol'); if (ac) ac.onclick = function(){ if (ED.dg.cols.length >= 8) return; ED.dg.cols.push('Series ' + (ED.dg.cols.length + 1)); ED.dg.rows.forEach(function(r){ r.v = r.v || []; r.v.push(0); }); drawForm(); preview(); };
  var fb = $('dsdg-fill'); if (fb) fb.onclick = function(){ edFill(); };
  var ar = $('dsdg-addrow'); if (ar) ar.onclick = function(){
    if (t.max && ED.dg.rows.length >= t.max) { toast('This diagram holds up to ' + t.max + ' rows.', 'error'); return; }
    var last = ED.dg.rows[ED.dg.rows.length - 1] || {}, r = {};
    (t.fields || []).forEach(function(x){ r[x.k] = x.group ? (last[x.k] || '') : (x.num ? (last[x.k] != null ? last[x.k] : 0) : ''); });
    if (t.series) r.v = ED.dg.cols.map(function(){ return 0; });
    ED.dg.rows.push(r); drawForm(); preview();
    setTimeout(function(){ var ins = f.querySelectorAll('input[data-r="' + (ED.dg.rows.length - 1) + '"]'); if (ins[0]) ins[0].focus(); }, 30);
  };
}
function edFill(){
  if (!ED || _filling) return;
  var dg = ED.dg, s = slideAt(ED.idx) || {};
  DG.fillGroups(dg, s, function(){ if (ED && ED.dg === dg) { drawForm(); preview(); } });
  drawForm();
}
/* AI writes points for the EMPTY groups of a framework diagram (free) */
var _filling = false;
DG.fillGroups = function(dg, slide, done){
  var t = DG.types[dg && dg.t], miss = DG.emptyGroups(dg);
  delete dg._needFill;
  if (!t || !miss.length || !CFG.fill) { if (done) done(0); return; }
  var names = dg.cols && dg.cols.length ? dg.cols : t.groups, lang = $('dsp-lang');
  _filling = true;
  fetch(CFG.fill, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce':CFG.nonce},
    body:JSON.stringify({name:t.name, groups:miss, all:names, title:(slide && slide.title) || '', subtitle:(slide && slide.subtitle) || '', deck_title:deckTitle(),
      language:lang ? lang.value : 'English', points:(dg.rows || []).filter(function(r){ return r && r.l; }).slice(0, 24).map(function(r){ return (r.g ? r.g + ': ' : '') + r.l; })})})
  .then(function(r){ return r.json(); })
  .then(function(res){
    _filling = false;
    if (!res || !res.success) { toast((res && res.message) || 'Could not fill the empty groups.', 'error'); if (done) done(-1); return; }
    var added = [];
    (res.rows || []).forEach(function(r){ if (miss.indexOf(r.g) >= 0 && r.l) { dg.rows.push({g:r.g, l:r.l}); if (added.indexOf(r.g) < 0) added.push(r.g); } });
    if (added.length) toast('✨ Added points for ' + added.join(' and '));
    if (done) done(added.length);
  })
  .catch(function(){ _filling = false; toast('Could not fill the empty groups.', 'error'); if (done) done(-1); });
};
/* after a layout-panel switch (SWOT → PESTLE …) fill the new empty groups once */
function autoFillDeck(){
  if (_filling) return;
  var i = curIdx(), s = slideAt(i); if (!DG.is(s)) return;
  if (!s.dg.fillTried) { var fixed = false; DG.deckArrays().forEach(function(a){ if (a[i] && a[i].dg && DG.fixGroups(a[i].dg)) fixed = true; }); if (fixed) refresh(i); }
  /* fill after a layout switch, or ONCE for a slide that already has empty groups next to filled ones
     (fillTried is saved with the deck, so points a user deletes on purpose are not written back) */
  var once = !s.dg.fillTried && (s.dg.rows || []).some(function(r){ return r && r.l; }) && DG.emptyGroups(s.dg).length;
  if (!s.dg._needFill && !once) return;
  var work = DG.clone(s.dg);
  DG.deckArrays().forEach(function(a){ if (a[i] && a[i].dg) { delete a[i].dg._needFill; a[i].dg.fillTried = 1; } });
  DG.fillGroups(work, s, function(n){
    if (n <= 0) return;
    DG.deckArrays().forEach(function(a){ var x = a[i]; if (!x || !x.dg || x.dg.t !== work.t) return; var have = {}; x.dg.rows.forEach(function(r){ have[r.g + '|' + r.l] = 1; }); work.rows.forEach(function(r){ if (!have[r.g + '|' + r.l]) x.dg.rows.push({g:r.g, l:r.l}); }); });
    refresh(i);
  });
}
function applyEdit(){
  var idx = ED.idx, dg = DG.clone(ED.dg); delete dg.orig; delete dg._needFill; dg.fillTried = 1; var content = ED.content.map(function(x){ return String(x || '').trim(); }).filter(Boolean), conc = ED.conc;
  DG.deckArrays().forEach(function(a){ var s = a[idx]; if (!s) return; s.type = 'chart'; s.dg = DG.clone(dg); s.content = content.slice(); if (conc != null) s.conclusion = conc; });
  setChoice(idx, 'v1');
  close('dsdg-ed');
  refresh(idx);
  toast('Diagram updated');
}

/* ---------------- Add Slide hook + Edit button ---------------- */
function hookAddSlide(){
  var body = $('dsp-addslide-body');
  if (!body || $('dsdg-addbtn')) return;
  injectCss();
  var b = document.createElement('button'); b.type = 'button'; b.id = 'dsdg-addbtn';
  b.innerHTML = '<i>&#128450;&#65039;</i><div><b>See all categories</b><span>' + Object.keys(DG.designs).length + ' diagram designs — SWOT, roadmaps, Gantt, funnels, org charts, matrices, KPI dashboards…</span></div>';
  b.onclick = function(){ var inp = $('dsp-addslide-input'), t = inp ? inp.value.trim() : ''; if (window.dspAddSlideClose) window.dspAddSlideClose(); DG.openGallery(t); };
  var ch = $('dsch-addbtn');
  if (ch && ch.parentNode === body) body.insertBefore(b, ch.nextSibling); else body.appendChild(b);
}
/* hover box over the diagram area (same behaviour as the chart snippet's "Edit chart" box) */
function hookEditBtn(){
  var main = $('dsp-ppt-main-slide'); if (!main) return;
  var s = slideAt(curIdx()), box = $('dsdg-box'), old = $('dsdg-edit');
  if (old) old.remove();
  if (!DG.is(s) || (window.S && S.data && S.data.slides && curIdx() >= S.data.slides.length)) { if (box) box.remove(); return; }
  var z = DG.zoneOf(s) || {x:64, y:170, w:1152, h:470};
  injectCss();
  /* the box lives NEXT TO the main slide (never inside it): the editor re-renders the slide
     whenever something is added inside it, which would wipe the box in an endless loop */
  var host = main.parentNode; if (!host) return;
  if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
  if (!box || box.parentNode !== host) {
    if (box) box.remove();
    box = document.createElement('div'); box.id = 'dsdg-box'; box.title = 'Click to edit this diagram';
    box.innerHTML = '<button type="button" class="dsdg-ebtn">&#9998; EDIT CHART</button>';
    box.onclick = function(e){ e.preventDefault(); e.stopPropagation(); DG.openEditor(curIdx()); };
    host.appendChild(box);
  }
  var hr = host.getBoundingClientRect(), mr = main.getBoundingClientRect();
  if (!mr.width || !mr.height) { box.style.display = 'none'; return; }
  var L = mr.left - hr.left + host.scrollLeft + z.x / 1280 * mr.width, T = mr.top - hr.top + host.scrollTop + z.y / 720 * mr.height;
  var st = 'left:' + L.toFixed(1) + 'px;top:' + T.toFixed(1) + 'px;width:' + (z.w / 1280 * mr.width).toFixed(1) + 'px;height:' + (z.h / 720 * mr.height).toFixed(1) + 'px';
  if (box.getAttribute('data-z') !== st) { box.setAttribute('data-z', st); box.style.cssText = st; }
}
document.addEventListener('dblclick', function(e){
  var main = e.target && e.target.closest ? e.target.closest('#dsp-ppt-main-slide') : null; if (!main) return;
  if (e.target.closest('[contenteditable="true"]')) return;
  var i = curIdx(); if (DG.is(slideAt(i))) DG.openEditor(i);
}, true);

/* ---------------- Classic download: send the diagram shapes ---------------- */
var _of = window.fetch;
window.fetch = function(url, opts){
  try {
    var us = typeof url === 'string' ? url : (url && url.url) || '';
    if (us.indexOf('/ds/v1/build-pptx') !== -1 && !tplActive()) {
      var stamped = [];
      DG.hookNow();
      var fam = ($('dsp-font') && $('dsp-font').value) || '';
      DG.deckArrays().forEach(function(a){ a.forEach(function(s, i){
        if (!DG.is(s)) return;
        var pg = DG.pageClassic(s, theme(), s.dg.v, i);
        if (pg) { s.dg_els = pg.els.map(function(e){ var o = {}; for (var k in e) if (k !== 'ed' && k.charAt(0) !== '_') o[k] = e[k]; return o; }); s.dg_fonts = {head:fam, body:fam}; stamped.push(s); }
      }); });
      if (stamped.length && opts && typeof opts.body === 'string') {
        try { var b = JSON.parse(opts.body); if (b && Array.isArray(b.slides)) { var src = DG.deckArrays()[0] || []; b.slides.forEach(function(sl, i){ var s = src[i]; if (DG.is(s) && s.dg_els) { sl.dg = s.dg; sl.dg_els = s.dg_els; sl.dg_fonts = s.dg_fonts; sl.type = 'chart'; } }); var o2 = {}; for (var k2 in opts) o2[k2] = opts[k2]; o2.body = JSON.stringify(b); opts = o2; } } catch(e){}
      }
      var p = _of.call(this, url, opts);
      var clean = function(){ stamped.forEach(function(s){ delete s.dg_els; delete s.dg_fonts; }); };
      p.then(clean, clean);
      return p;
    }
  } catch(e){ if (window.console) console.warn('[DS Diagrams]', e); }
  return _of.apply(this, arguments);
};

/* ============================================================
   AUTOMATIC DIAGRAMS during generation (AI or Preserve My Text)
   A slide is converted in place only when its text clearly is one of these;
   max 2 per deck, never the first or last slide, slide count unchanged.
   ============================================================ */
function split(x){ var t = String(x || '').replace(/\*\*/g, '').trim(), i = t.indexOf(':'); return i > 0 && i < 60 ? [t.slice(0, i).trim(), t.slice(i + 1).trim()] : [t, '']; }
function numIn(s){ var m = String(s || '').match(/([-+]?[$€£₹]?\s?\d[\d,.]*\s?(?:%|[KMB]\b|k\b|million|billion)?)/); if (!m) return null; var raw = m[1], n = parseFloat(raw.replace(/[^0-9.\-]/g, '')); if (!isFinite(n)) return null; if (/billion|B\b/.test(raw)) n *= 1e9; else if (/million|M\b/.test(raw)) n *= 1e6; else if (/K\b|k\b/.test(raw)) n *= 1e3; return {n:n, u:/%/.test(raw) ? '%' : (/[$€£₹]/.test(raw) ? raw.match(/[$€£₹]/)[0] : '')}; }
function labelsMatch(items, names){
  var hits = {}, rows = [];
  items.forEach(function(x){ var sp = split(x), key = sp[0].toLowerCase();
    names.forEach(function(n){ if (key.indexOf(n.toLowerCase().slice(0, 5)) === 0 || key === n.toLowerCase()) { hits[n] = 1; sp[1].split(/[;•]\s*|\.\s+(?=[A-Z])/).map(function(z){ return z.trim(); }).filter(Boolean).slice(0, 4).forEach(function(z){ rows.push({g:n, l:z.charAt(0).toUpperCase() + z.slice(1)}); }); } }); });
  return {n:Object.keys(hits).length, rows:rows};
}
var RULES = [
  function(s, items){ var m = labelsMatch(items, ['Strengths', 'Weaknesses', 'Opportunities', 'Threats']); if (m.n >= 3 || (/swot/i.test(s.title) && m.n >= 2)) return {t:'swot', v:'dg_swot1', rows:m.rows, name:'SWOT'}; },
  function(s, items){ var m = labelsMatch(items, ['Political', 'Economic', 'Social', 'Technological', 'Legal', 'Environmental']); if (m.n >= 4) return {t:'pestle', v:'dg_pestle1', rows:m.rows, name:'PESTLE'}; },
  function(s, items){ var m = labelsMatch(items, ['Competitive Rivalry', 'Threat of New Entrants', 'Supplier Power', 'Buyer Power', 'Threat of Substitutes']); if (m.n >= 3) return {t:'porter', v:'dg_porter1', rows:m.rows, name:"Porter's Five Forces"}; },
  function(s, items){ if (!/funnel|pipeline|conversion/i.test(s.title) || items.length < 3 || items.length > 6) return; var rows = []; items.forEach(function(x){ var sp = split(x), nm = numIn(x); if (nm) rows.push({l:sp[0], v:nm.n, d:sp[1].replace(/[-+]?[$€£₹]?\s?\d[\d,.]*\s?(%|[KMB]\b|k\b|million|billion)?/, '').trim()}); }); if (rows.length >= 3) return {t:'funnel', v:'dg_fu1', rows:rows, name:'Funnel'}; },
  function(s, items){ if (!/\b(kpis?|key metrics|scorecard|dashboard|by the numbers|at a glance|key figures)\b/i.test(s.title) || items.length < 3 || items.length > 8) return; var rows = []; items.forEach(function(x){ var sp = split(x), nm = numIn(sp[0]) || numIn(sp[1]); if (nm) rows.push({l:numIn(sp[0]) ? sp[1].split(/[|,(]/)[0].trim() : sp[0], v:nm.n, u:nm.u, t:null, d:''}); }); if (rows.length >= 3 && rows.length === items.length) return {t:'kpi', v:'dg_kpi1', rows:rows, name:'KPI dashboard'}; },
  function(s, items){ if (!/\bcycle\b|\bloop\b|continuous improvement|flywheel/i.test(s.title) || items.length < 3 || items.length > 6) return; return {t:'cycle', v:'dg_cy1', rows:items.map(function(x){ var sp = split(x); return {l:sp[0], d:sp[1]}; }), o:{center:s.title.split(/[:—-]/)[0].trim().slice(0, 40)}, name:'Cycle'}; },
  function(s, items){ if (!/maturity|levels? of|stages? of maturity/i.test(s.title) || items.length < 3 || items.length > 6) return; return {t:'maturity', v:'dg_mm1', rows:items.map(function(x){ var sp = split(x); return {l:sp[0].replace(/^level\s*\d+\s*[-–:]?\s*/i, ''), d:sp[1]}; }), name:'Maturity model'}; },
  function(s, items){ if (!/\b(pyramid|hierarchy)\b/i.test(s.title) || items.length < 3 || items.length > 6) return; return {t:'hierarchy', v:'dg_hi1', rows:items.map(function(x){ var sp = split(x); return {l:sp[0], d:sp[1]}; }), name:'Hierarchy'}; },
  function(s, items){ if (!/customer journey|user journey|journey map/i.test(s.title) || items.length < 3 || items.length > 6) return; return {t:'journey', v:'dg_cj2', rows:items.map(function(x){ var sp = split(x); return {l:sp[0], d:sp[1], v:1, g:''}; }), name:'Customer journey'}; },
  function(s, items){ if (!/roadmap|phases?\b|implementation plan|rollout|go-to-market plan/i.test(s.title) || items.length < 3 || items.length > 5) return; if (!items.every(function(x){ return split(x)[1]; })) return;
    return {t:'phases', v:'dg_ph1', rows:items.map(function(x){ var sp = split(x), m = sp[0].match(/^(.*?)\s*\(([^)]+)\)\s*$/); return {l:(m ? m[1] : sp[0]).replace(/^phase\s*\d+\s*[-–:]?\s*/i, '') || sp[0], s:m ? m[2] : '', d:sp[1].split(/[.;]\s+/).slice(0, 3).join('; ')}; }), name:'Phased roadmap'}; },
  function(s, items){ if (!(/\b(process|how it works|steps?|approach|methodology|workflow|our method|framework|onboarding)\b/i.test(s.title) || /\b(\d|three|four|five|six)[- ]step/i.test(s.sub || '')) || items.length < 3 || items.length > 6) return; if (!items.every(function(x){ var sp = split(x); return sp[0].split(' ').length <= 5; })) return;
    return {t:'process', v:'dg_pr1', rows:items.map(function(x){ var sp = split(x); return {l:sp[0].replace(/^(step\s*)?\d+[.)]?\s*/i, ''), d:sp[1]}; }), name:'Process'}; }
];
var _seen = typeof WeakSet === 'function' ? new WeakSet() : null, _seenArr = [], _loadTs = 0;
var _of2 = window.fetch;
window.fetch = function(url){ try { var us = typeof url === 'string' ? url : (url && url.url) || ''; if (us.indexOf('/ds/v1/load-ppt') !== -1) _loadTs = Date.now(); } catch(e){} return _of2.apply(this, arguments); };
function seen(o){ return _seen ? _seen.has(o) : _seenArr.indexOf(o) >= 0; }
function mark(o){ if (_seen) _seen.add(o); else _seenArr.push(o); }
var _pend = null;
function autoScan(){
  if (CFG.auto === false) return;
  var fs = $('dsp-editor-fullscreen');
  if (!fs || !fs.classList.contains('dsp-open') || !window.G || !G.pptData || !Array.isArray(G.pptData.slides)) return;
  var pd = G.pptData; if (seen(pd)) return;
  var th = $('dsp-ppt-thumbs'); if (!th || th.children.length !== pd.slides.length) return;
  if (!window.S || !S.data || !Array.isArray(S.data.slides) || S.data.slides.length !== pd.slides.length) return;
  if (!_pend || _pend.pd !== pd) { _pend = {pd:pd, t:Date.now()}; return; }
  if (Date.now() - _pend.t < 2500) return;                 /* let the chart snippet finish its own pass first */
  mark(pd); _pend = null;
  if (Date.now() - _loadTs < 30000 || pd.__dsdgAuto) return;
  pd.__dsdgAuto = 1;
  var sl = S.data.slides, found = [];
  if (sl.some(function(s){ return DG.is(s); })) return;
  for (var i = 1; i < sl.length - 1 && found.length < 2; i++) {
    var s = sl[i]; if (!s) continue; var ac = s.type === 'chart' && s.auto && s.chart && !s.dg;   /* slide the chart snippet auto-converted */
    if (!ac && /^(chart|title|closing|quote|stats|agenda|section)$/.test(s.type || '')) continue;
    var items = (s.content || []).map(function(x){ return String(x || '').trim(); }).filter(Boolean); if (items.length < 2) continue;
    for (var r = 0; r < RULES.length; r++) {
      var hit = null; try { if (ac && r > 2) break; hit = RULES[r]({title:String(s.title || ''), sub:String(s.subtitle || ''), type:s.type}, items); } catch(e){ hit = null; }
      if (hit && hit.rows && hit.rows.length >= 2 && DG.types[hit.t]) { found.push({i:i, hit:hit}); break; }
    }
  }
  if (!found.length) return;
  var names = [];
  found.forEach(function(f){
    var t = DG.types[f.hit.t], rows = f.hit.rows.slice(0, t.max || 12);
    DG.deckArrays().forEach(function(a){ var s = a[f.i]; if (!s) return; if (!s.__origType) s.__origType = s.type; s.type = 'chart'; s.content = []; s.auto = 1; delete s.chart;
      s.dg = {t:t.id, v:f.hit.v, rows:DG.clone(rows), cols:f.hit.cols || (t.groups ? t.groups.slice() : []), ax:{}, unit:f.hit.unit || '', o:f.hit.o || {}}; });
    setChoice(f.i, 'v1'); names.push(f.hit.name);
  });
  refresh((window.G && G.activeSlideIdx) || 0);
  toast('🧩 ' + found.length + ' slide' + (found.length > 1 ? 's' : '') + ' now show' + (found.length > 1 ? '' : 's') + ' a ' + names.join(' and ') + ' diagram — click “Edit diagram” to change it.');
}
setInterval(function(){ try { DG.hookNow(); hookAddSlide(); hookEditBtn(); autoScan(); autoFillDeck(); } catch(e){ if (window.console) console.warn('[DS Diagrams]', e); } }, 600);
})();


