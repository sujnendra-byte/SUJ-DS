(function(){
if (window.DS_CHART && window.DS_CHART.core) return;
var DC = window.DS_CHART = window.DS_CHART || {};
DC.core = 1; DC._n = 0;

/* ---------- colour helpers ---------- */
function hx(c){
  c = String(c || '').trim();
  var m = c.match(/#([0-9a-f]{6}|[0-9a-f]{3})\b/i);
  if (!m) return '#4A9EFF';
  var h = m[1]; if (h.length === 3) h = h[0]+h[0]+h[1]+h[1]+h[2]+h[2];
  return '#' + h.toUpperCase();
}
function rgb(c){ c = hx(c); return [parseInt(c.substr(1,2),16), parseInt(c.substr(3,2),16), parseInt(c.substr(5,2),16)]; }
function lum(c){ var r = rgb(c); return (0.299*r[0] + 0.587*r[1] + 0.114*r[2]) / 255; }
function toHsl(c){
  var r = rgb(c).map(function(v){ return v/255; }), mx = Math.max.apply(null, r), mn = Math.min.apply(null, r), h = 0, s = 0, l = (mx+mn)/2, d = mx-mn;
  if (d) { s = l > 0.5 ? d/(2-mx-mn) : d/(mx+mn);
    if (mx === r[0]) h = (r[1]-r[2])/d + (r[1] < r[2] ? 6 : 0); else if (mx === r[1]) h = (r[2]-r[0])/d + 2; else h = (r[0]-r[1])/d + 4; h *= 60; }
  return {h:h, s:s, l:l};
}
function fromHsl(h, s, l){
  h = ((h % 360) + 360) % 360 / 360;
  function f(p, q, t){ if (t < 0) t += 1; if (t > 1) t -= 1; if (t < 1/6) return p+(q-p)*6*t; if (t < 1/2) return q; if (t < 2/3) return p+(q-p)*(2/3-t)*6; return p; }
  var r, g, b;
  if (!s) r = g = b = l; else { var q = l < 0.5 ? l*(1+s) : l+s-l*s, p = 2*l-q; r = f(p,q,h+1/3); g = f(p,q,h); b = f(p,q,h-1/3); }
  return '#' + [r,g,b].map(function(v){ var x = Math.round(Math.max(0, Math.min(1, v))*255).toString(16); return x.length < 2 ? '0'+x : x; }).join('').toUpperCase();
}
function mix(a, b, t){ var A = rgb(a), B = rgb(b); return '#' + [0,1,2].map(function(i){ var x = Math.round(A[i] + (B[i]-A[i])*t).toString(16); return x.length < 2 ? '0'+x : x; }).join('').toUpperCase(); }
function firstHex(bg){ var m = String(bg || '').match(/#[0-9a-f]{6}|#[0-9a-f]{3}\b/i); return m ? hx(m[0]) : '#1B2A4A'; }
DC.u = {hx:hx, lum:lum, mix:mix, toHsl:toHsl, fromHsl:fromHsl, firstHex:firstHex};

/* palette: the accent first, then harmonious hues around it.
   PHP: ds_chart_palette() — keep in step. */
var OFFS = [[0,0],[180,0],[0,-0.16],[40,0.02],[180,-0.14],[-40,0.04],[0,0.13],[90,0],[-90,0],[140,-0.05],[-140,0.05],[20,-0.1]];
DC.palette = function(ac, dark, n){
  var A = toHsl(hx(ac)), out = [hx(ac)];
  var s = Math.max(0.5, Math.min(dark ? 0.85 : 0.72, A.s || 0.6));
  var l = dark ? Math.max(0.55, Math.min(0.66, A.l)) : Math.max(0.42, Math.min(0.52, A.l));
  for (var i = 1; i < Math.max(n, 1); i++) {
    var o = OFFS[i % OFFS.length], lap = Math.floor(i / OFFS.length), hh = A.h + o[0] + lap*12;
    var li = Math.max(0.28, Math.min(0.8, l + o[1])), c = fromHsl(hh, s, li);
    for (var g = 0; g < 8 && !dark && lum(c) > 0.6; g++) { li -= 0.04; c = fromHsl(hh, s, li); }
    for (var g2 = 0; g2 < 8 && dark && lum(c) < 0.36; g2++) { li += 0.04; c = fromHsl(hh, s, li); }
    out.push(c);
  }
  return out.slice(0, Math.max(n, 1));
};

/* ---------- data ---------- */
var FAM = {pie:'pie', donut:'pie', exploded:'pie', half:'pie', ring:'pie', column:'bar', bar:'bar', line:'line', smooth:'line', area:'line', gauge:'gauge', progress:'gauge', rings:'gauge'};
DC.FAM = FAM;
DC.STYLES = {
  pie:[['pie','Full pie'],['donut','Donut'],['exploded','Exploded'],['half','Half donut'],['ring','Thin ring']],
  bar:[['column','Column'],['bar','Horizontal bar']],
  line:[['line','Line'],['smooth','Smooth line'],['area','Area']],
  gauge:[['gauge','Gauges'],['progress','Progress bars'],['rings','Radial rings']]
};
DC.FAMILIES = [['pie','Pie & Donut'],['bar','Bar & Column'],['line','Line & Area'],['gauge','Progress & Gauge']];
function num(v){ var n = parseFloat(String(v == null ? '' : v).replace(/[^0-9.\-]/g, '')); return isFinite(n) ? n : NaN; }
DC.norm = function(ch){
  ch = ch || {};
  var style = FAM[ch.style] ? ch.style : (ch.kind && DC.STYLES[ch.kind] ? DC.STYLES[ch.kind][0][0] : 'donut');
  var rows = [];
  var L = Array.isArray(ch.labels) ? ch.labels : [], Vs = Array.isArray(ch.values) ? ch.values : [];
  for (var i = 0; i < Math.max(L.length, Vs.length); i++) {
    var v = num(Vs[i]); if (!isFinite(v)) continue;
    rows.push({l: String(L[i] == null ? '' : L[i]).trim().slice(0, 60) || ('Item ' + (i+1)), v: Math.max(FAM[style] === 'pie' ? 0 : -1e12, v)});
  }
  rows = rows.slice(0, 12);
  var unit = String(ch.unit == null ? '%' : ch.unit).trim().slice(0, 8);
  var mx = num(ch.max);
  if (!isFinite(mx) || mx <= 0) { mx = 100; rows.forEach(function(r){ if (r.v > mx) mx = r.v; }); if (unit !== '%') { var m2 = 0; rows.forEach(function(r){ if (r.v > m2) m2 = r.v; }); mx = m2 > 0 ? m2 : 100; } }
  return {kind: FAM[style], style: style, angle: ((parseInt(ch.angle, 10) || 0) % 360 + 360) % 360, unit: unit, max: mx, rows: rows};
};
var PRE = /^[$€£₹¥]$/;
DC.fmt = function(v, unit){
  var a = Math.abs(v), s = (Math.round(v*100)/100);
  var str = a >= 1e9 ? (Math.round(v/1e8)/10) + 'B' : a >= 1e6 ? (Math.round(v/1e5)/10) + 'M' : a >= 1e4 ? (Math.round(v/100)/10) + 'K' : String(s);
  if (!unit) return str;
  if (PRE.test(unit)) return (v < 0 ? '-' : '') + unit + str.replace('-', '');
  if (unit === '%') return str + '%';
  return str + ' ' + unit;
};
function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function f1(n){ return Math.round(n*10)/10; }
function sumOf(rows){ var t = 0; rows.forEach(function(r){ t += Math.max(0, r.v); }); return t; }
function trunc(s, n){ s = String(s); return s.length > n ? s.slice(0, n-1) + '…' : s; }
/* nice axis max */
DC.niceMax = function(m){
  if (m <= 0) return 10;
  var p = Math.pow(10, Math.floor(Math.log10(m))), f = m / p, n = f <= 1 ? 1 : f <= 1.2 ? 1.2 : f <= 1.5 ? 1.5 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 3 ? 3 : f <= 4 ? 4 : f <= 5 ? 5 : f <= 6 ? 6 : f <= 8 ? 8 : 10;
  return n * p;
};

/* ---------- geometry (mirrored in PHP) ---------- */
/* pie family: legend 'right' | 'bottom' | 'none' */
DC.pieGeo = function(w, h, style, legend, n){
  var LW = legend === 'right' ? Math.min(w*0.42, 340) : 0, LH = legend === 'bottom' ? Math.min(h*0.26, 20 + Math.ceil(n/3)*36) : 0;
  var aw = w - LW, ah = h - LH, g = {LW:LW, LH:LH, aw:aw, ah:ah};
  if (style === 'half') { g.R = Math.min(aw/2, ah*0.92) * 0.94; g.cx = aw/2; g.cy = ah/2 + g.R*0.45; if (g.cy + 0.02*g.R > ah) g.cy = ah - 2; }
  else { g.R = Math.min(aw, ah)/2 * 0.92; g.cx = aw/2; g.cy = ah/2; }
  g.ri = style === 'donut' ? 0.58 : style === 'ring' ? 0.8 : style === 'half' ? 0.56 : 0;
  return g;
};
DC.gaugeGeo = function(w, h, n){
  n = Math.max(1, Math.min(4, n)); var cw = w/n, R = Math.min(cw*0.45, h*0.56);
  return {n:n, cw:cw, R:R, th:R*0.24, cy:h*0.5 + R*0.28};
};

/* arc path helpers (angle 0 = 12 o'clock, clockwise, degrees) */
function pt(cx, cy, r, a){ var t = (a - 90) * Math.PI/180; return [cx + r*Math.cos(t), cy + r*Math.sin(t)]; }
function sector(cx, cy, R, ri, a0, a1){
  if (a1 - a0 >= 359.99) a1 = a0 + 359.99;
  var large = (a1 - a0) > 180 ? 1 : 0, p0 = pt(cx,cy,R,a0), p1 = pt(cx,cy,R,a1);
  if (!ri) return 'M'+f1(cx)+' '+f1(cy)+' L'+f1(p0[0])+' '+f1(p0[1])+' A'+f1(R)+' '+f1(R)+' 0 '+large+' 1 '+f1(p1[0])+' '+f1(p1[1])+' Z';
  var q1 = pt(cx,cy,ri,a1), q0 = pt(cx,cy,ri,a0);
  return 'M'+f1(p0[0])+' '+f1(p0[1])+' A'+f1(R)+' '+f1(R)+' 0 '+large+' 1 '+f1(p1[0])+' '+f1(p1[1])+' L'+f1(q1[0])+' '+f1(q1[1])+' A'+f1(ri)+' '+f1(ri)+' 0 '+large+' 0 '+f1(q0[0])+' '+f1(q0[1])+' Z';
}
function arcStroke(cx, cy, r, a0, a1){
  if (a1 - a0 >= 359.99) a1 = a0 + 359.99;
  var p0 = pt(cx,cy,r,a0), p1 = pt(cx,cy,r,a1);
  return 'M'+f1(p0[0])+' '+f1(p0[1])+' A'+f1(r)+' '+f1(r)+' 0 '+((a1-a0) > 180 ? 1 : 0)+' 1 '+f1(p1[0])+' '+f1(p1[1]);
}
function onColor(c){ return lum(c) > 0.62 ? '#111827' : '#FFFFFF'; }
function txt(x, y, s, o){
  o = o || {};
  return '<text x="'+f1(x)+'" y="'+f1(y)+'" font-size="'+f1(o.size||16)+'" font-weight="'+(o.wt||400)+'" fill="'+(o.fill||'#FFFFFF')+'"'
    + (o.op != null ? ' fill-opacity="'+o.op+'"' : '') + ' text-anchor="'+(o.anchor||'start')+'" dominant-baseline="'+(o.base||'central')+'">'+esc(s)+'</text>';
}

/* legend block */
function legendSvg(rows, cols, x, y, w, h, mode, o, showVal, unit){
  var s = '', n = rows.length;
  if (mode === 'right') {
    var rh = Math.min(52, h / Math.max(n, 1)), fs = Math.max(14, Math.min(22, rh*0.46)), y0 = y + (h - rh*n)/2 + rh/2;
    rows.forEach(function(r, i){
      var yy = y0 + i*rh;
      s += '<rect x="'+f1(x+8)+'" y="'+f1(yy-fs*0.42)+'" width="'+f1(fs*0.84)+'" height="'+f1(fs*0.84)+'" rx="'+f1(fs*0.22)+'" fill="'+cols[i % cols.length]+'"/>';
      s += txt(x+8+fs*1.35, yy, trunc(r.l, 26), {size:fs, fill:o.tc, wt:500});
      if (showVal) s += txt(x+w-4, yy, DC.fmt(r.v, unit), {size:fs, fill:o.tc, wt:800, anchor:'end'});
    });
  } else if (mode === 'bottom') {
    var per = Math.min(n, 3), rows2 = Math.ceil(n/per), cw = w/per, lh = Math.min(36, h/Math.max(rows2,1)), fs2 = Math.max(14, Math.min(20, lh*0.6));
    rows.forEach(function(r, i){
      var cx = x + (i % per)*cw, yy = y + (h - lh*rows2)/2 + Math.floor(i/per)*lh + lh/2;
      s += '<circle cx="'+f1(cx+14)+'" cy="'+f1(yy)+'" r="'+f1(fs2*0.38)+'" fill="'+cols[i % cols.length]+'"/>';
      s += txt(cx+14+fs2*0.9, yy, trunc(r.l, 20) + (showVal ? '  ' + DC.fmt(r.v, unit) : ''), {size:fs2, fill:o.tc, wt:500});
    });
  }
  return s;
}

/* ---------- SVG ----------
   o: {w, h, colors[], tc (labels), mc (muted), legend, bgc (solid under colour), uid} */
DC.svg = function(chart, o){
  o = o || {};
  var d = DC.norm(chart), w = o.w || 640, h = o.h || 400, rows = d.rows, n = rows.length;
  var tc = hx(o.tc || '#FFFFFF'), mc = hx(o.mc || mix(tc, o.bgc || '#000000', 0.35)), bgc = hx(o.bgc || '#1B2A4A');
  var cols = (o.colors && o.colors.length ? o.colors : DC.palette(o.ac || '#4A9EFF', lum(bgc) < 0.5, Math.max(n, 1))).map(hx);
  while (cols.length < n) cols = cols.concat(cols);
  var uid = (o.uid || 'c') + '_' + (++DC._n);
  var fam = o.font ? ' font-family="' + esc(o.font) + ',Arial,sans-serif"' : ' font-family="Arial,sans-serif"';
  var S = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '+f1(w)+' '+f1(h)+'" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" style="display:block;overflow:visible;"'+fam+'>';
  if (!n) return S + txt(w/2, h/2, 'Add data to see the chart', {size:18, fill:mc, anchor:'middle'}) + '</svg>';
  var track = tc, trackOp = 0.12;

  if (d.kind === 'pie') {
    var legend = o.legend || 'right', g = DC.pieGeo(w, h, d.style, legend, n), tot = sumOf(rows) || 1;
    var span = d.style === 'half' ? 180 : 360, a = d.style === 'half' ? 270 : d.angle, R = g.R, ri = g.ri*R;
    var big = 0; rows.forEach(function(r, i){ if (r.v > rows[big].v) big = i; });
    var lbl = '';
    rows.forEach(function(r, i){
      var sw = Math.max(0, r.v)/tot*span, a0 = a, a1 = a + sw; a = a1;
      if (sw <= 0) return;
      var mid = (a0 + a1)/2, off = d.style === 'exploded' ? R*0.055 : 0, cx = g.cx, cy = g.cy;
      if (off) { var m = pt(0, 0, off, mid); cx += m[0]; cy += m[1]; }
      S += '<path d="'+sector(cx, cy, R, ri, a0, a1)+'" fill="'+cols[i]+'" stroke="'+bgc+'" stroke-width="'+(d.style === 'exploded' ? 0 : Math.max(1.5, R*0.012))+'" stroke-linejoin="round"/>';
      var share = r.v/tot*100, lab = d.unit === '%' ? DC.fmt(r.v, '%') : Math.round(share) + '%';
      if (d.style !== 'ring' && sw >= (d.style === 'half' ? 11 : 16)) {
        var lr = ri ? (R + ri)/2 : R*0.64, lp = pt(cx, cy, lr, mid), fs = Math.max(12, Math.min(26, R*(ri ? (R-ri)/R*0.34 : 0.13)));
        lbl += txt(lp[0], lp[1], lab, {size:fs, wt:800, fill:onColor(cols[i]), anchor:'middle'});
      }
    });
    S += lbl;
    if (ri) {
      var cr = rows[big], cfs = Math.max(16, ri*0.42);
      var cyv = d.style === 'half' ? g.cy - ri*0.32 : g.cy - ri*0.1;
      S += txt(g.cx, cyv, d.unit === '%' ? DC.fmt(cr.v, '%') : Math.round(cr.v/tot*100) + '%', {size:cfs, wt:900, fill:tc, anchor:'middle'});
      S += txt(g.cx, cyv + cfs*0.78, trunc(cr.l, 18), {size:Math.max(11, cfs*0.36), wt:600, fill:mc, anchor:'middle'});
    }
    if (legend === 'right') S += legendSvg(rows, cols, g.aw, 0, g.LW, h, 'right', {tc:tc}, true, d.unit === '%' ? '%' : d.unit);
    else if (legend === 'bottom') S += legendSvg(rows, cols, 0, g.ah, w, g.LH, 'bottom', {tc:tc}, false, d.unit);
    return S + '</svg>';
  }

  if (d.kind === 'bar') {
    var mxv = 0, mnv = 0; rows.forEach(function(r){ mxv = Math.max(mxv, r.v); mnv = Math.min(mnv, r.v); });
    var top = DC.niceMax(mxv), ac = cols[0], fs3 = Math.max(15, Math.min(21, h*0.046));
    var gid = uid + 'g';
    S += '<defs><linearGradient id="'+gid+'" x1="0" y1="0" x2="'+(d.style === 'bar' ? 1 : 0)+'" y2="'+(d.style === 'bar' ? 0 : 1)+'">'
       + (d.style === 'bar' ? '<stop offset="0" stop-color="'+mix(ac, bgc, 0.25)+'"/><stop offset="1" stop-color="'+ac+'"/>' : '<stop offset="0" stop-color="'+ac+'"/><stop offset="1" stop-color="'+mix(ac, bgc, 0.25)+'"/>')
       + '</linearGradient></defs>';
    if (d.style === 'bar') {
      var lw = Math.min(w*0.3, 230), px0 = lw + 10, pw = w - px0 - 70, rh = h/n, bh = Math.min(rh*0.58, 46);
      for (var gi = 0; gi <= 4; gi++) { var gx = px0 + pw*gi/4; S += '<line x1="'+f1(gx)+'" y1="0" x2="'+f1(gx)+'" y2="'+f1(h)+'" stroke="'+mc+'" stroke-opacity="0.22" stroke-dasharray="4 5"/>'; }
      rows.forEach(function(r, i){
        var yy = i*rh + rh/2, bw = Math.max(2, Math.max(0, r.v)/top*pw);
        S += txt(lw, yy, trunc(r.l, 24), {size:fs3, fill:tc, wt:600, anchor:'end'});
        S += '<rect x="'+f1(px0)+'" y="'+f1(yy-bh/2)+'" width="'+f1(bw)+'" height="'+f1(bh)+'" rx="'+f1(Math.min(8, bh/3))+'" fill="url(#'+gid+')"/>';
        S += txt(px0 + bw + 10, yy, DC.fmt(r.v, d.unit), {size:fs3, fill:tc, wt:800});
      });
    } else {
      var axw = 58, bot = 44, ph = h - bot - 26, pw2 = w - axw - 8, cw = pw2/n, bw2 = Math.min(cw*0.58, 90), y0 = 26;
      for (var gj = 0; gj <= 4; gj++) {
        var gy = y0 + ph - ph*gj/4;
        S += '<line x1="'+axw+'" y1="'+f1(gy)+'" x2="'+f1(w)+'" y2="'+f1(gy)+'" stroke="'+mc+'" stroke-opacity="'+(gj ? 0.22 : 0.5)+'"'+(gj ? ' stroke-dasharray="4 5"' : '')+'/>';
        S += txt(axw - 10, gy, DC.fmt(top*gj/4, d.unit), {size:fs3*0.85, fill:mc, anchor:'end'});
      }
      rows.forEach(function(r, i){
        var cx = axw + cw*i + cw/2, bh2 = Math.max(2, Math.max(0, r.v)/top*ph);
        S += '<path d="M'+f1(cx-bw2/2)+' '+f1(y0+ph)+' V'+f1(y0+ph-bh2+Math.min(8,bw2/4))+' Q'+f1(cx-bw2/2)+' '+f1(y0+ph-bh2)+' '+f1(cx-bw2/2+Math.min(8,bw2/4))+' '+f1(y0+ph-bh2)+' H'+f1(cx+bw2/2-Math.min(8,bw2/4))+' Q'+f1(cx+bw2/2)+' '+f1(y0+ph-bh2)+' '+f1(cx+bw2/2)+' '+f1(y0+ph-bh2+Math.min(8,bw2/4))+' V'+f1(y0+ph)+' Z" fill="url(#'+gid+')"/>';
        S += txt(cx, y0 + ph - bh2 - 13, DC.fmt(r.v, d.unit), {size:fs3, fill:tc, wt:800, anchor:'middle'});
        S += txt(cx, y0 + ph + 22, trunc(r.l, Math.max(6, Math.floor(cw/ (fs3*0.55)))), {size:fs3*0.9, fill:tc, wt:500, anchor:'middle', op:0.9});
      });
    }
    return S + '</svg>';
  }

  if (d.kind === 'line') {
    var mx3 = 0; rows.forEach(function(r){ mx3 = Math.max(mx3, r.v); });
    var top3 = DC.niceMax(mx3), axw3 = 58, bot3 = 44, y03 = 30, ph3 = h - bot3 - y03, pw3 = w - axw3 - 24, fs4 = Math.max(15, Math.min(21, h*0.046)), lc = cols[0];
    var step = n > 1 ? pw3/(n-1) : 0, P = rows.map(function(r, i){ return [axw3 + 12 + (n > 1 ? step*i : pw3/2), y03 + ph3 - Math.max(0, r.v)/top3*ph3]; });
    if (n > 1) { P.forEach(function(p){ p[0] = axw3 + 12 + (p[0] - axw3 - 12) * (pw3 - 12)/pw3; }); }
    for (var gk = 0; gk <= 4; gk++) {
      var gy3 = y03 + ph3 - ph3*gk/4;
      S += '<line x1="'+axw3+'" y1="'+f1(gy3)+'" x2="'+f1(w)+'" y2="'+f1(gy3)+'" stroke="'+mc+'" stroke-opacity="'+(gk ? 0.22 : 0.5)+'"'+(gk ? ' stroke-dasharray="4 5"' : '')+'/>';
      S += txt(axw3 - 10, gy3, DC.fmt(top3*gk/4, d.unit), {size:fs4*0.85, fill:mc, anchor:'end'});
    }
    var dl = '';
    if (d.style === 'smooth' && n > 2) {
      dl = 'M' + f1(P[0][0]) + ' ' + f1(P[0][1]);
      for (var k = 0; k < n-1; k++) {
        var p0 = P[Math.max(0,k-1)], p1 = P[k], p2 = P[k+1], p3 = P[Math.min(n-1,k+2)];
        dl += ' C' + f1(p1[0]+(p2[0]-p0[0])/6) + ' ' + f1(p1[1]+(p2[1]-p0[1])/6) + ' ' + f1(p2[0]-(p3[0]-p1[0])/6) + ' ' + f1(p2[1]-(p3[1]-p1[1])/6) + ' ' + f1(p2[0]) + ' ' + f1(p2[1]);
      }
    } else dl = P.map(function(p, i){ return (i ? 'L' : 'M') + f1(p[0]) + ' ' + f1(p[1]); }).join(' ');
    if (d.style === 'area') {
      var ag = uid + 'a';
      S += '<defs><linearGradient id="'+ag+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'+lc+'" stop-opacity="0.55"/><stop offset="1" stop-color="'+lc+'" stop-opacity="0.04"/></linearGradient></defs>';
      S += '<path d="'+dl+' L'+f1(P[n-1][0])+' '+f1(y03+ph3)+' L'+f1(P[0][0])+' '+f1(y03+ph3)+' Z" fill="url(#'+ag+')"/>';
    }
    S += '<path d="'+dl+'" fill="none" stroke="'+lc+'" stroke-width="'+f1(Math.max(3, h*0.011))+'" stroke-linecap="round" stroke-linejoin="round"/>';
    rows.forEach(function(r, i){
      var p = P[i];
      S += '<circle cx="'+f1(p[0])+'" cy="'+f1(p[1])+'" r="'+f1(Math.max(5, h*0.016))+'" fill="'+bgc+'" stroke="'+lc+'" stroke-width="'+f1(Math.max(2.5, h*0.008))+'"/>';
      S += txt(p[0], p[1] - 20, DC.fmt(r.v, d.unit), {size:fs4, fill:tc, wt:800, anchor:'middle'});
      S += txt(p[0], y03 + ph3 + 22, trunc(r.l, Math.max(6, Math.floor((n > 1 ? step : pw3)/(fs4*0.55)))), {size:fs4*0.9, fill:tc, wt:500, anchor:'middle', op:0.9});
    });
    return S + '</svg>';
  }

  /* gauge family */
  var mxg = d.max;
  if (d.style === 'progress') {
    var lw4 = Math.min(w*0.3, 240), bx = lw4 + 18, bw4 = w - bx - 90, rh4 = h/n, bh4 = Math.min(rh4*0.36, 26), fs5 = Math.max(15, Math.min(22, rh4*0.32));
    rows.forEach(function(r, i){
      var yy = i*rh4 + rh4/2, fw = Math.max(0, Math.min(1, r.v/mxg))*bw4;
      S += txt(lw4, yy, trunc(r.l, 24), {size:fs5, fill:tc, wt:600, anchor:'end'});
      S += '<rect x="'+f1(bx)+'" y="'+f1(yy-bh4/2)+'" width="'+f1(bw4)+'" height="'+f1(bh4)+'" rx="'+f1(bh4/2)+'" fill="'+track+'" fill-opacity="'+trackOp+'"/>';
      if (fw > 0) S += '<rect x="'+f1(bx)+'" y="'+f1(yy-bh4/2)+'" width="'+f1(Math.max(fw, bh4))+'" height="'+f1(bh4)+'" rx="'+f1(bh4/2)+'" fill="'+cols[i]+'"/>';
      S += txt(bx + bw4 + 12, yy, DC.fmt(r.v, d.unit), {size:fs5, fill:tc, wt:800});
    });
    return S + '</svg>';
  }
  if (d.style === 'rings') {
    var LW5 = Math.min(w*0.44, 320), aw5 = w - LW5, R5 = Math.min(aw5, h)/2*0.94, cx5 = aw5/2, cy5 = h/2, m5 = Math.min(n, 6);
    var band = R5*0.72/m5, th5 = band*0.74;
    rows.slice(0, m5).forEach(function(r, i){
      var rr = R5 - th5/2 - i*band, fr = Math.max(0, Math.min(1, r.v/mxg));
      S += '<circle cx="'+f1(cx5)+'" cy="'+f1(cy5)+'" r="'+f1(rr)+'" fill="none" stroke="'+track+'" stroke-opacity="'+trackOp+'" stroke-width="'+f1(th5)+'"/>';
      if (fr > 0) S += '<path d="'+arcStroke(cx5, cy5, rr, 0, fr*360)+'" fill="none" stroke="'+cols[i]+'" stroke-width="'+f1(th5)+'" stroke-linecap="round"/>';
    });
    S += legendSvg(rows.slice(0, m5), cols, aw5, 0, LW5, h, 'right', {tc:tc}, true, d.unit);
    return S + '</svg>';
  }
  var G = DC.gaugeGeo(w, h, n);
  rows.slice(0, G.n).forEach(function(r, i){
    var cx = G.cw*i + G.cw/2, rr = G.R - G.th/2, fr = Math.max(0, Math.min(1, r.v/mxg));
    S += '<path d="'+arcStroke(cx, G.cy, rr, 270, 450)+'" fill="none" stroke="'+track+'" stroke-opacity="'+trackOp+'" stroke-width="'+f1(G.th)+'" stroke-linecap="butt"/>';
    if (fr > 0) S += '<path d="'+arcStroke(cx, G.cy, rr, 270, 270 + fr*180)+'" fill="none" stroke="'+cols[i]+'" stroke-width="'+f1(G.th)+'" stroke-linecap="butt"/>';
    S += txt(cx, G.cy - G.R*0.2, DC.fmt(r.v, d.unit), {size:G.R*0.34, wt:900, fill:tc, anchor:'middle'});
    S += txt(cx, G.cy + G.R*0.22, trunc(r.l, 22), {size:Math.max(15, Math.min(24, G.R*0.17)), wt:600, fill:tc, anchor:'middle', op:0.85});
  });
  return S + '</svg>';
};

/* small icon for the wizard */
DC.icon = function(style, c){
  c = c || '#8AB4F8';
  var demo = {pie:[40,25,20,15], bar:[30,55,40,70], line:[20,45,35,70], gauge:[72,48]}[FAM[style] || 'pie'];
  return DC.svg({style:style, unit:'%', labels:demo.map(function(_, i){ return 'A'+i; }), values:demo}, {w:220, h:140, colors:DC.palette(c, true, 5), tc:'#E5E7EB', mc:'#9CA3AF', bgc:'#1d1f27', legend:'none'})
    .replace(/<text[^>]*>[^<]*<\/text>/g, '');
};

/* ---------- percentage detection (auto chart slides) ---------- */
DC.detect = function(slide){
  if (!slide || !Array.isArray(slide.content)) return null;
  var t = String(slide.type || '');
  if (t === 'title' || t === 'closing' || t === 'chart' || t === 'agenda' || t === 'quote') return null;
  var labels = [], values = [], structured = 0;
  slide.content.forEach(function(it){
    var s = String(it || ''), m = s.match(/(-?\d{1,3}(?:[.,]\d+)?)\s?%/);
    if (!m) return;
    var v = parseFloat(m[1].replace(',', '.')); if (!isFinite(v) || v < 0 || v > 1000) return;
    var ci = s.indexOf(':'); if (ci > 0 && ci < 70) structured++;
    var lab = ci > 0 && ci < 60 ? s.slice(0, ci) : s.replace(m[0], '').replace(/[|:–—-].*$/, '');
    lab = lab.replace(/\*\*/g, '').replace(/^\s*[\d.,]+\s?%\s*/, '').trim();
    if (/^-?\d+([.,]\d+)?\s?%$/.test(lab) || !lab) { var rest = s.slice(ci + 1).split('|')[0].trim(); lab = rest || lab; }
    lab = lab.split(/\s+/).slice(0, 5).join(' ').slice(0, 40);
    labels.push(lab || ('Item ' + (labels.length + 1))); values.push(v);
  });
  if (values.length < 2) return null;
  var sum = values.reduce(function(a, b){ return a + b; }, 0);
  if (structured < 2 && !(sum >= 95 && sum <= 105)) return null;
  var all100 = values.every(function(v){ return v <= 100; });
  var style = (sum >= 95 && sum <= 105) ? 'donut' : (all100 && values.length <= 4) ? 'gauge' : (all100 && values.length <= 6) ? 'progress' : 'column';
  return {style:style, unit:'%', labels:labels.slice(0, 8), values:values.slice(0, 8), angle:0};
};
})();

/* ============================================================
   DS Charts — Classic deck layouts (snippet 17 calls DS_CHART.renderClassic)
   Boxes are in a 1280 x 720 design frame; the PHP writer uses the same
   numbers (ds_chart_classic_boxes) for the PowerPoint file.
   ============================================================ */
(function(){
var DC = window.DS_CHART; if (!DC || DC.classic) return;
DC.classic = 1;
var u = DC.u, W = 1280, H = 720, EM = 56.25 / W;

DC.VARIANTS = [
  {id:'v1',  lbl:'Chart + insights'},
  {id:'n2',  lbl:'Insights + chart right'},
  {id:'n3',  lbl:'Centered chart'},
  {id:'n4',  lbl:'Big numbers + chart'},
  {id:'n5',  lbl:'Full-width chart'}
];
DC.variants = function(){ return DC.VARIANTS; };

function pts(s){
  return (Array.isArray(s.content) ? s.content : []).map(function(x){ return String(x || '').trim(); }).filter(Boolean).slice(0, 4);
}
DC.points = pts;

/* PHP twin: ds_chart_classic_boxes() */
DC.boxes = function(v, s){
  var d = DC.norm(s.chart), P = pts(s), has = P.length > 0, pie = d.kind === 'pie';
  var stripTxt = s.conclusion || '';
  var b = {v:v, title:{x:64, y:40, w:1152, align:'l'}, panel:null};
  if (v === 'n2') {
    b.chart = {x:560, y:168, w:680, h:500, legend: pie ? (has ? 'bottom' : 'none') : 'none'};
    b.panel = {x:64, y:182, w:468, h:476, mode: has ? 'points' : (pie || d.kind === 'gauge' ? 'legend' : 'kpi')};
  } else if (v === 'n3') {
    b.title.align = 'c';
    b.chart = {x:170, y:162, w:940, h: has ? 410 : 500, legend: pie ? 'right' : 'none'};
    if (has) b.panel = {x:64, y:596, w:1152, h:100, mode:'cols'};
  } else if (v === 'n4') {
    b.panel = {x:64, y:176, w:330, h:490, mode:'kpi'};
    b.chart = {x:430, y:168, w:800, h:500, legend: pie ? 'bottom' : 'none'};
  } else if (v === 'n5') {
    var st = stripTxt || (has ? P[0] : '');
    b.chart = {x:64, y:160, w:1152, h: st ? 456 : 520, legend: pie ? 'right' : 'none'};
    if (st) b.panel = {x:64, y:634, w:1152, h:58, mode:'strip', text:st, conc: !!stripTxt};
  } else {
    b.chart = {x:40, y:168, w:680, h:500, legend: pie ? (has ? 'bottom' : 'none') : 'none'};
    b.panel = {x:748, y:182, w:468, h:476, mode: has ? 'points' : (pie || d.kind === 'gauge' ? 'legend' : 'kpi')};
  }
  if (d.style === 'rings' || d.style === 'progress' || d.style === 'gauge') { if (b.chart.legend !== 'none') b.chart.legend = 'none'; }
  return b;
};

function pc(v, tot){ return (Math.round(v / tot * 10000) / 100) + '%'; }
function box(r){ return 'position:absolute;left:'+pc(r.x,W)+';top:'+pc(r.y,H)+';width:'+pc(r.w,W)+';'+(r.h != null ? 'height:'+pc(r.h,H)+';' : ''); }
function em(px){ return (Math.round(px * EM * 1000) / 1000) + 'em'; }
function esc(s){ return String(s == null ? '' : s).replace(/\*\*/g, '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function rgba(c, a){ c = u.hx(c); return 'rgba('+parseInt(c.substr(1,2),16)+','+parseInt(c.substr(3,2),16)+','+parseInt(c.substr(5,2),16)+','+a+')'; }
function split(x){ var s = String(x), i = s.indexOf(':'); return i > 0 && i < 80 ? [s.slice(0, i).trim(), s.slice(i+1).trim()] : [s.trim(), '']; }
function clamp(n){ return 'overflow:hidden;display:-webkit-box;-webkit-line-clamp:'+n+';-webkit-box-orient:vertical;'; }
function ed(idx, f, i){ return idx >= 0 ? ' contenteditable="true" spellcheck="false" data-ed-slide="'+idx+'" data-ed-field="'+f+'"'+(i != null ? ' data-ed-idx="'+i+'"' : '') : ''; }

DC.colorsFor = function(ac, bg, n){ return DC.palette(ac, u.lum(u.firstHex(bg)) < 0.5, Math.max(n, 1)); };

/* the chart itself, with the edit affordance (main canvas only) */
DC.chartHTML = function(s, idx, r, o){
  var svg = DC.svg(s.chart, {w:r.w, h:r.h, colors:o.colors, tc:o.tc, mc:o.mc, bgc:o.bgc, legend:r.legend, uid:'dsc'+idx+(o.tag||''), font:o.font});
  return '<div class="dsch-box" data-dsch-idx="'+idx+'" style="'+box(r)+'">'+svg
    + (idx >= 0 ? '<button type="button" class="dsch-edit" onclick="event.stopPropagation();window.DS_CHART&&DS_CHART.openEditor('+idx+')">&#9998; Edit chart</button>' : '')
    + '</div>';
};

DC.renderClassic = function(s, t, v, idx){
  var ff = '', fe = document.getElementById('dsp-font'); if (fe && fe.value) ff = "'" + fe.value + "',";
  var bg = t.bg || '#1B2A4A', bgc = u.firstHex(bg), tc = u.hx(t.tc || '#FFFFFF'), bc = u.hx(t.bc || tc), ac = u.hx(t.ac || '#4A9EFF');
  var d = DC.norm(s.chart), b = DC.boxes(v, s), cols = DC.colorsFor(ac, bg, Math.max(d.rows.length, 3));
  var mc = u.mix(bc, bgc, 0.25);
  var h = '<div class="dsch-slide" style="position:absolute;inset:0;background:'+bg+';overflow:hidden;font-family:'+ff+'Arial,sans-serif;">';
  h += '<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';pointer-events:none;"></div>';
  var al = b.title.align === 'c' ? 'center' : 'left';
  h += '<div style="'+box(b.title)+'text-align:'+al+';">'
     + '<div'+ed(idx,'title')+' style="outline:none;cursor:text;font-size:'+em(36)+';font-weight:800;color:'+tc+';line-height:1.12;'+clamp(2)+'">'+esc(s.title || '')+'</div>'
     + '<div'+ed(idx,'subtitle')+' style="outline:none;cursor:text;min-height:1em;margin-top:'+em(8)+';font-size:'+em(17)+';font-weight:600;color:'+ac+';line-height:1.3;'+clamp(2)+'">'+esc(s.subtitle || '')+'</div>'
     + '</div>';
  h += DC.chartHTML(s, idx, b.chart, {colors:cols, tc:tc, mc:mc, bgc:bgc, tag:v, font:fe && fe.value ? fe.value : ''});
  var p = b.panel;
  if (p) {
    var P = pts(s), card = rgba(tc, 0.06), line = rgba(tc, 0.14);
    if (p.mode === 'points') {
      var n = P.length, gap = 14, chh = (p.h - gap*(n-1)) / n;
      h += '<div style="'+box(p)+'display:flex;flex-direction:column;gap:'+pc(gap,H)+';">';
      P.forEach(function(x, i){
        var sp = split(x);
        h += '<div style="position:relative;flex:1;min-height:0;border-radius:'+em(12)+';background:'+card+';padding:'+em(14)+' '+em(18)+' '+em(14)+' '+em(26)+';display:flex;flex-direction:column;justify-content:center;overflow:hidden;">'
           + '<div style="position:absolute;left:0;top:18%;bottom:18%;width:'+em(6)+';border-radius:'+em(3)+';background:'+cols[i % cols.length]+';"></div>'
           + '<div'+ed(idx,'content-h',i)+' style="outline:none;cursor:text;font-size:'+em(n > 3 ? 19 : 22)+';font-weight:800;color:'+tc+';line-height:1.2;'+clamp(2)+'">'+esc(sp[0])+'</div>'
           + (sp[1] || idx >= 0 ? '<div'+ed(idx,'content-d',i)+' style="outline:none;cursor:text;min-height:1em;margin-top:'+em(5)+';font-size:'+em(n > 3 ? 15 : 16.5)+';color:'+bc+';line-height:1.38;'+clamp(n > 3 ? 2 : 3)+'">'+esc(sp[1])+'</div>' : '')
           + '</div>';
      });
      h += '</div>';
    } else if (p.mode === 'legend' || p.mode === 'kpi') {
      var rows = d.rows.map(function(r, i){ return {r:r, i:i}; });
      if (p.mode === 'kpi') rows = rows.slice().sort(function(a, c){ return c.r.v - a.r.v; }).slice(0, 3);
      else rows = rows.slice(0, 7);
      var m = rows.length || 1, tot = 0, one = d.kind === 'bar' || d.kind === 'line'; d.rows.forEach(function(r){ tot += Math.max(0, r.v); });
      h += '<div style="'+box(p)+'display:flex;flex-direction:column;justify-content:center;">';
      rows.forEach(function(o, k){
        var val = d.kind === 'pie' && d.unit !== '%' ? DC.fmt(o.r.v, d.unit) : DC.fmt(o.r.v, d.unit);
        if (p.mode === 'kpi') {
          h += '<div style="padding:'+em(14)+' 0;'+(k ? 'border-top:1px solid '+line+';' : '')+'">'
             + '<div style="font-size:'+em(m > 2 ? 46 : 54)+';font-weight:900;line-height:1;color:'+cols[one ? 0 : o.i % cols.length]+';">'+esc(val)+'</div>'
             + '<div style="margin-top:'+em(6)+';font-size:'+em(17)+';font-weight:600;color:'+bc+';line-height:1.3;'+clamp(2)+'">'+esc(o.r.l)+'</div></div>';
        } else {
          h += '<div style="display:flex;align-items:center;gap:'+em(14)+';padding:'+em(m > 5 ? 8 : 12)+' 0;'+(k ? 'border-top:1px solid '+line+';' : '')+'">'
             + '<span style="flex:0 0 auto;width:'+em(16)+';height:'+em(16)+';border-radius:'+em(5)+';background:'+cols[o.i % cols.length]+';"></span>'
             + '<span style="flex:1;min-width:0;font-size:'+em(18)+';font-weight:600;color:'+tc+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(o.r.l)+'</span>'
             + '<span style="flex:0 0 auto;font-size:'+em(m > 5 ? 22 : 26)+';font-weight:900;color:'+tc+';">'+esc(val)+'</span></div>';
        }
      });
      h += '</div>';
    } else if (p.mode === 'cols') {
      var P3 = P.slice(0, 3);
      h += '<div style="'+box(p)+'display:flex;gap:'+pc(28,W)+';">';
      P3.forEach(function(x, i){
        var sp = split(x);
        h += '<div style="flex:1;min-width:0;border-top:'+em(4)+' solid '+cols[i % cols.length]+';padding-top:'+em(10)+';">'
           + '<div'+ed(idx,'content-h',i)+' style="outline:none;cursor:text;font-size:'+em(19)+';font-weight:800;color:'+tc+';line-height:1.2;'+clamp(1)+'">'+esc(sp[0])+'</div>'
           + '<div'+ed(idx,'content-d',i)+' style="outline:none;cursor:text;min-height:1em;margin-top:'+em(4)+';font-size:'+em(15.5)+';color:'+bc+';line-height:1.35;'+clamp(2)+'">'+esc(sp[1])+'</div></div>';
      });
      h += '</div>';
    } else if (p.mode === 'strip') {
      h += '<div style="'+box(p)+'border-radius:'+em(12)+';background:'+rgba(ac, 0.14)+';display:flex;align-items:center;padding:0 '+em(22)+' 0 '+em(28)+';overflow:hidden;">'
         + '<div style="position:absolute;left:0;top:20%;bottom:20%;width:'+em(6)+';border-radius:'+em(3)+';background:'+ac+';"></div>'
         + '<div'+(p.conc ? ed(idx,'dsch-conc') : ed(idx,'content-plain',0))+' style="outline:none;cursor:text;font-size:'+em(18)+';font-weight:600;color:'+tc+';line-height:1.3;'+clamp(2)+'">'+esc(p.text)+'</div></div>';
    }
  }
  return h + '</div>';
};
})();

/* ============================================================
   DS Charts — editor UI: Add Slide → Generate chart wizard, chart editor,
   automatic chart slides for percentage data.
   CFG (printed by PHP): window.DS_CHART_CFG = {rest, nonce, cost}
   ============================================================ */
(function(){
var DC = window.DS_CHART; if (!DC || DC.ui) return;
DC.ui = 1;
var CFG = window.DS_CHART_CFG || {}, u = DC.u;
function $(id){ return document.getElementById(id); }
function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(m, k){ if (window.dspToast) window.dspToast(m, k || 'success'); }
function deckArrays(){
  var a = [];
  if (window.S && S.data && Array.isArray(S.data.slides)) a.push(S.data.slides);
  if (window.G && G.pptData && Array.isArray(G.pptData.slides) && a.indexOf(G.pptData.slides) < 0) a.push(G.pptData.slides);
  return a;
}
function tplActive(){ try { return window.DS_TPL_ACTIVE ? window.DS_TPL_ACTIVE() : null; } catch(e){ return null; } }
function theme(){
  var el = $('dsp-design'), k = el && el.value ? el.value : 'navy-pro', TH = window.TH || {};
  return TH[k] || TH['navy-pro'] || {bg:'#1B2A4A', tc:'#FFFFFF', bc:'#C8D8F0', ac:'#4A9EFF'};
}
function renderAny(s, v){
  var T = tplActive();
  if (T) { try { return T.render(s, v, -1); } catch(e){} }
  return DC.renderClassic(s, theme(), v, -1);
}
function cost(){
  var c = parseInt(CFG.cost, 10);
  if (!c) { var el = $('dsp-addslide-cost'); c = el ? parseInt(el.textContent, 10) : 0; }
  return c || 5;
}

/* ---------------- CSS ---------------- */
var CSS = ''
+ '#dsp-ppt-main-slide .dsch-box{cursor:pointer;}'
+ '.dsch-edit{position:absolute;top:6px;right:6px;z-index:5;display:none;padding:6px 12px;border-radius:999px;border:0;background:#1a73e8;color:#fff;font:600 12px/1 -apple-system,Segoe UI,Arial,sans-serif;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.35);}'
+ '#dsp-ppt-main-slide .dsch-box:hover .dsch-edit{display:block;}'
+ '#dsp-ppt-main-slide .dsch-box:hover{outline:2px dashed rgba(138,180,248,.55);outline-offset:4px;border-radius:6px;}'
+ '#dsp-ppt-thumbs .dsch-edit,#vp-list .dsch-edit,.dsp-thumb-box .dsch-edit,#dsch-prev .dsch-edit{display:none!important;}'
+ '#dsch-addbtn{margin-top:14px;width:100%;display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;border:1.5px dashed #3b4a6b;background:linear-gradient(135deg,rgba(26,115,232,.14),rgba(138,180,248,.06));color:#e8eaed;cursor:pointer;text-align:left;font-family:inherit;}'
+ '#dsch-addbtn:hover{border-color:#8ab4f8;background:linear-gradient(135deg,rgba(26,115,232,.24),rgba(138,180,248,.1));}'
+ '#dsch-addbtn b{display:block;font-size:13px;margin-bottom:2px;}#dsch-addbtn span{font-size:11px;color:#9ca3af;}'
+ '#dsch-addbtn i{font-style:normal;font-size:22px;line-height:1;}'
+ '#dsch-modal{position:fixed;inset:0;z-index:120000;background:rgba(0,0,0,.66);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}'
+ '#dsch-modal.on{display:flex;}'
+ '#dsch-addbtn,#dsch-addbtn *,#dsch-modal button,#dsch-modal select,#dsch-modal input,#dsch-modal textarea{text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsch-card{width:960px;max-width:96vw;max-height:94vh;display:flex;flex-direction:column;background:#1d1f27;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;box-shadow:0 12px 50px rgba(0,0,0,.6);color:#e8eaed;}'
+ '#dsch-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:#11131b;border-bottom:1px solid #2a2a2a;}'
+ '#dsch-head h3{margin:0;font-size:15px;font-weight:700;color:#e8eaed;}#dsch-head small{color:#8b93a7;font-size:12px;margin-left:8px;font-weight:500;}'
+ '#dsch-x{background:none;border:0;color:#9ca3af;font-size:22px;cursor:pointer;line-height:1;}'
+ '#dsch-body{flex:1;min-height:0;overflow:auto;padding:18px 20px;}'
+ '#dsch-foot{display:flex;align-items:center;gap:10px;padding:12px 20px;background:#11131b;border-top:1px solid #2a2a2a;}'
+ '.dsch-b{padding:9px 16px;border-radius:7px;border:1px solid #2f3340;background:#15171f;color:#c3c8d4;font:600 13px/1 inherit;font-family:inherit;cursor:pointer;}'
+ '.dsch-b:hover{border-color:#4b5563;color:#fff;}'
+ '.dsch-p{background:#1a73e8;border-color:#1a73e8;color:#fff;}.dsch-p:hover{background:#1765cc;}.dsch-p[disabled]{opacity:.6;cursor:wait;}'
+ '.dsch-fams{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;}'
+ '.dsch-fam{background:#15171f;border:2px solid #2a2d38;border-radius:12px;padding:12px;cursor:pointer;text-align:center;transition:border-color .15s,transform .15s;}'
+ '.dsch-fam:hover{border-color:#8ab4f8;transform:translateY(-2px);}'
+ '.dsch-fam .ic{height:96px;display:flex;align-items:center;justify-content:center;}.dsch-fam .ic svg{height:96px;}'
+ '.dsch-fam b{display:block;margin-top:8px;font-size:13px;color:#e8eaed;}.dsch-fam span{display:block;margin-top:3px;font-size:11px;color:#8b93a7;}'
+ '.dsch-q{font-size:13px;color:#c3c8d4;margin:0 0 14px;}'
+ '.dsch-grid{display:grid;grid-template-columns:minmax(0,1fr) 440px;gap:20px;}'
+ '@media(max-width:860px){.dsch-grid{grid-template-columns:1fr;}.dsch-fams{grid-template-columns:repeat(2,1fr);}}'
+ '.dsch-l{font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:#8b93a7;margin:14px 0 7px;display:flex;align-items:center;justify-content:space-between;}'
+ '.dsch-l:first-child{margin-top:0;}'
+ '.dsch-tabs{display:flex;gap:6px;flex-wrap:wrap;}'
+ '.dsch-tab{padding:6px 11px;border-radius:999px;border:1px solid #2f3340;background:#15171f;color:#aeb4c2;font:600 12px/1 inherit;font-family:inherit;cursor:pointer;}'
+ '.dsch-tab.on{background:#1a73e8;border-color:#1a73e8;color:#fff;}'
+ '.dsch-styles{display:grid;grid-template-columns:repeat(auto-fill,minmax(92px,1fr));gap:8px;}'
+ '.dsch-st{background:#15171f;border:2px solid #2a2d38;border-radius:9px;padding:6px 6px 7px;cursor:pointer;text-align:center;}'
+ '.dsch-st svg{height:46px;width:100%;}.dsch-st div{font-size:11px;color:#c3c8d4;margin-top:3px;font-weight:600;}'
+ '.dsch-st.on{border-color:#8ab4f8;background:#1b2436;}'
+ '.dsch-in{width:100%;box-sizing:border-box;background:#0f1117;border:1.5px solid #2a2d38;border-radius:7px;padding:8px 10px;color:#e8eaed;font:13px/1.3 inherit;font-family:inherit;outline:none;}'
+ '.dsch-in:focus{border-color:#1a73e8;}'
+ 'textarea.dsch-in{min-height:64px;resize:vertical;}'
+ '.dsch-rows{display:flex;flex-direction:column;gap:6px;}'
+ '.dsch-row{display:grid;grid-template-columns:14px minmax(0,1fr) 110px 30px;gap:8px;align-items:center;}'
+ '.dsch-row i{width:12px;height:12px;border-radius:4px;display:block;}'
+ '.dsch-del{width:30px;height:32px;border-radius:7px;border:1px solid #2f3340;background:#15171f;color:#9ca3af;cursor:pointer;font-size:14px;}'
+ '.dsch-del:hover{color:#f87171;border-color:#7f1d1d;}'
+ '.dsch-add{margin-top:8px;padding:8px 12px;border-radius:7px;border:1.5px dashed #3b4a6b;background:transparent;color:#8ab4f8;font:600 12px/1 inherit;font-family:inherit;cursor:pointer;width:100%;}'
+ '.dsch-add:hover{border-color:#8ab4f8;}'
+ '.dsch-inline{display:flex;gap:10px;align-items:center;}'
+ '.dsch-inline>*{flex:1;}'
+ '.dsch-ang{display:flex;align-items:center;gap:10px;}.dsch-ang input[type=range]{flex:1;accent-color:#1a73e8;}.dsch-ang b{width:44px;text-align:right;font-size:12px;color:#e8eaed;}'
+ '#dsch-prevwrap{position:sticky;top:0;}'
+ '#dsch-prev{position:relative;width:440px;max-width:100%;aspect-ratio:16/9;font-size:7.82px;border-radius:8px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.45);}'
+ '#dsch-prev *{pointer-events:none;}'
+ '.dsch-vars{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin-top:10px;}'
+ '.dsch-var{position:relative;aspect-ratio:16/9;font-size:1.45px;border-radius:4px;overflow:hidden;border:2px solid #2a2d38;cursor:pointer;}'
+ '.dsch-var *{pointer-events:none;}.dsch-var.on{border-color:#8ab4f8;}'
+ '.dsch-hint{font-size:11px;color:#8b93a7;margin-top:6px;line-height:1.45;}'
+ '.dsch-cost{margin-left:auto;font-size:11px;color:#9ca3af;background:#0f1117;border:1px solid #2a2d38;border-radius:999px;padding:5px 10px;}'
+ '.dsch-sample{position:absolute;top:8px;left:8px;z-index:3;font:700 10px/1 Arial;color:#111;background:#fbbf24;border-radius:999px;padding:4px 8px;letter-spacing:.4px;}';

function injectCss(){ if ($('dsch-css')) return; var st = document.createElement('style'); st.id = 'dsch-css'; st.textContent = CSS; document.head.appendChild(st); }

/* ---------------- state ---------------- */
var ST = null;   /* {mode:'new'|'edit', idx, step, fam, chart, topic, notes, v} */
var SAMPLE = {
  pie:{labels:['Enterprise','SMB','Consumer','Partners'], values:[45,30,15,10], unit:'%'},
  bar:{labels:['2022','2023','2024','2025'], values:[1.2,1.9,2.6,3.8], unit:'$'},
  line:{labels:['Q1','Q2','Q3','Q4','Q5'], values:[12,19,27,34,46], unit:'%'},
  gauge:{labels:['Retention','Satisfaction','On-time delivery'], values:[94,87,72], unit:'%'}
};
function rowsOf(ch){ var L = ch.labels || [], V = ch.values || [], out = []; for (var i = 0; i < Math.max(L.length, V.length); i++) out.push({l: L[i] == null ? '' : String(L[i]), v: V[i] == null ? '' : String(V[i])}); return out; }
function filled(ch){ return rowsOf(ch).filter(function(r){ return r.v !== '' && isFinite(parseFloat(r.v)); }).length; }
function previewChart(){
  var c = ST.chart;
  if (filled(c) >= 1) return c;
  var s = SAMPLE[DC.FAM[c.style]] || SAMPLE.pie;
  return {style:c.style, angle:c.angle, max:c.max, unit:c.unit || s.unit, labels:s.labels, values:s.values, _sample:true};
}
function previewSlide(){
  var base = ST.mode === 'edit' ? currentSlide(ST.idx) : null, s = {};
  if (base) for (var k in base) s[k] = base[k];
  s.type = 'chart'; s.chart = previewChart();
  if (!base) {
    s.title = ST.topic ? ST.topic.charAt(0).toUpperCase() + ST.topic.slice(1) : 'Your chart title';
    s.subtitle = 'AI writes the title, subtitle and insights';
    var extra = (ST.notes || '').split(/\n+/).map(function(x){ return x.trim(); }).filter(Boolean).slice(0, 3);
    s.content = extra.length ? extra : ['Key insight: AI explains what the numbers mean for your audience.', 'Trend: The biggest movement in the data, in one line.', 'Action: What to do next based on this chart.'];
  }
  return s;
}
function currentSlide(i){ var a = deckArrays(); return a.length ? a[0][i] : null; }

/* ---------------- modal ---------------- */
function modal(){
  var m = $('dsch-modal'); if (m) return m;
  injectCss();
  m = document.createElement('div'); m.id = 'dsch-modal';
  m.innerHTML = '<div id="dsch-card"><div id="dsch-head"><h3 id="dsch-title"></h3><button type="button" id="dsch-x">&#10005;</button></div><div id="dsch-body"></div><div id="dsch-foot"></div></div>';
  document.body.appendChild(m);
  m.addEventListener('mousedown', function(e){ if (e.target === m) close(); });
  $('dsch-x').onclick = close;
  m.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); e.stopPropagation(); });
  return m;
}
function close(){ var m = $('dsch-modal'); if (m) m.classList.remove('on'); ST = null; }

function famCard(f){
  var st = DC.STYLES[f[0]][0][0], desc = {pie:'Shares of a whole — %', bar:'Compare values', line:'Trends over time', gauge:'Progress towards 100%'}[f[0]];
  return '<div class="dsch-fam" data-fam="'+f[0]+'"><div class="ic">'+DC.icon(f[0] === 'pie' ? 'donut' : f[0] === 'gauge' ? 'gauge' : st, '#8ab4f8')+'</div><b>'+f[1]+'</b><span>'+desc+'</span></div>';
}

function draw(){
  var m = modal(), body = $('dsch-body'), foot = $('dsch-foot'), c = ST.chart;
  $('dsch-title').innerHTML = ST.mode === 'edit' ? '&#128202; Edit chart <small>changes are free</small>' : '&#128202; Generate chart slide <small>'+(ST.step === 1 ? 'Step 1 of 2' : 'Step 2 of 2')+'</small>';
  if (ST.step === 1) {
    body.innerHTML = '<p class="dsch-q">Which type of chart do you want?</p><div class="dsch-fams">'+DC.FAMILIES.map(famCard).join('')+'</div>'
      + '<div class="dsch-hint" style="margin-top:14px;">Next you can type your numbers (percentages, amounts…), any text, and choose the shape and angle. You can edit the chart any time after it is added.</div>';
    Array.prototype.forEach.call(body.querySelectorAll('.dsch-fam'), function(el){ el.onclick = function(){ setFam(el.getAttribute('data-fam')); ST.step = 2; draw(); }; });
    foot.innerHTML = '<button type="button" class="dsch-b" id="dsch-cancel">Cancel</button><span class="dsch-cost">'+cost()+' credits</span>';
    $('dsch-cancel').onclick = close;
    m.classList.add('on');
    return;
  }
  var fam = DC.FAM[c.style], rows = rowsOf(c), cols = DC.palette(theme().ac || '#4A9EFF', true, Math.max(rows.length, 3));
  var h = '<div class="dsch-grid"><div>';
  h += '<div class="dsch-l">Chart type</div><div class="dsch-tabs">' + DC.FAMILIES.map(function(f){ return '<button type="button" class="dsch-tab'+(f[0] === fam ? ' on' : '')+'" data-fam="'+f[0]+'">'+f[1]+'</button>'; }).join('') + '</div>';
  h += '<div class="dsch-l">Shape</div><div class="dsch-styles">' + DC.STYLES[fam].map(function(s){ return '<div class="dsch-st'+(s[0] === c.style ? ' on' : '')+'" data-st="'+s[0]+'">'+DC.icon(s[0], '#8ab4f8')+'<div>'+s[1]+'</div></div>'; }).join('') + '</div>';
  if (fam === 'pie' && c.style !== 'half') h += '<div class="dsch-l">Start angle</div><div class="dsch-ang"><input type="range" min="0" max="355" step="5" id="dsch-angle" value="'+(c.angle || 0)+'"/><b id="dsch-angv">'+(c.angle || 0)+'&deg;</b></div>';
  h += '<div class="dsch-l"><span>Data'+(fam === 'pie' ? ' (percentages or amounts)' : '')+'</span><span style="text-transform:none;letter-spacing:0;font-weight:500;">'+rows.length+' / 12</span></div><div class="dsch-rows" id="dsch-rows">';
  rows.forEach(function(r, i){
    h += '<div class="dsch-row"><i style="background:'+cols[(fam === 'bar' || fam === 'line') ? 0 : i % cols.length]+'"></i>'
      + '<input class="dsch-in" data-k="l" data-i="'+i+'" value="'+esc(r.l)+'" placeholder="'+esc((SAMPLE[fam].labels[i] || 'Label') )+'"/>'
      + '<input class="dsch-in" data-k="v" data-i="'+i+'" value="'+esc(r.v)+'" inputmode="decimal" placeholder="'+esc(SAMPLE[fam].values[i] != null ? SAMPLE[fam].values[i] : '0')+'"/>'
      + '<button type="button" class="dsch-del" data-del="'+i+'" title="Delete row">&#10005;</button></div>';
  });
  h += '</div>' + (rows.length < 12 ? '<button type="button" class="dsch-add" id="dsch-addrow">+ Add row</button>' : '');
  h += '<div class="dsch-inline" style="margin-top:12px;"><div><div class="dsch-l" style="margin-top:0;">Unit</div><select class="dsch-in" id="dsch-unit">'
    + [['%','% Percent'],['$','$ Dollar'],['€','€ Euro'],['£','£ Pound'],['₹','₹ Rupee'],['','None'],['_c','Custom…']].map(function(o){ var on = (c.unit === o[0]) || (o[0] === '_c' && ['%','$','€','£','₹',''].indexOf(c.unit) < 0); return '<option value="'+o[0]+'"'+(on ? ' selected' : '')+'>'+o[1]+'</option>'; }).join('')
    + '</select></div>'
    + (['%','$','€','£','₹',''].indexOf(c.unit) < 0 ? '<div><div class="dsch-l" style="margin-top:0;">Custom unit</div><input class="dsch-in" id="dsch-cunit" maxlength="8" value="'+esc(c.unit)+'" placeholder="e.g. users"/></div>' : '')
    + (fam === 'gauge' ? '<div><div class="dsch-l" style="margin-top:0;">Full scale (100%)</div><input class="dsch-in" id="dsch-max" inputmode="decimal" value="'+esc(c.max || '')+'" placeholder="100"/></div>' : '')
    + '</div>';
  if (ST.mode === 'new') {
    h += '<div class="dsch-l">What is this chart about?</div><input class="dsch-in" id="dsch-topic" value="'+esc(ST.topic)+'" placeholder="e.g. Market share by customer segment in 2025"/>';
    h += '<div class="dsch-l">Extra text or points <span style="text-transform:none;letter-spacing:0;font-weight:500;">optional · one per line</span></div><textarea class="dsch-in" id="dsch-notes" placeholder="e.g. Enterprise grew from 31% to 45% after the new sales team&#10;Focus on onboarding in 2026">'+esc(ST.notes)+'</textarea>';
    h += '<div class="dsch-hint">AI writes a title, subtitle and 3 insights from your data and text. No numbers yet? Leave the rows empty and AI will create realistic data from your topic.</div>';
  }
  h += '</div><div><div id="dsch-prevwrap"><div class="dsch-l" style="margin-top:0;">Preview</div><div id="dsch-prev"></div><div class="dsch-l">Layout</div><div class="dsch-vars" id="dsch-vars"></div></div></div></div>';
  body.innerHTML = h;
  foot.innerHTML = (ST.mode === 'new' ? '<button type="button" class="dsch-b" id="dsch-back">&#8592; Back</button>' : '')
    + '<button type="button" class="dsch-b" id="dsch-cancel">Cancel</button>'
    + (ST.mode === 'new' ? '<span class="dsch-cost">'+cost()+' credits</span>' : '<span style="margin-left:auto"></span>')
    + '<button type="button" class="dsch-b dsch-p" id="dsch-go">'+(ST.mode === 'new' ? '&#10024; Generate chart slide' : '&#10003; Apply changes')+'</button>';
  bind();
  preview();
  m.classList.add('on');
}
function setFam(f){
  var c = ST.chart;
  if (DC.FAM[c.style] === f) return;
  c.style = DC.STYLES[f][0][0];
  if (f === 'pie') c.style = 'donut';
  if (f === 'gauge' && !c.unit) c.unit = '%';
}
function bind(){
  var body = $('dsch-body'), c = ST.chart;
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-tab'), function(el){ el.onclick = function(){ setFam(el.getAttribute('data-fam')); draw(); }; });
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-st'), function(el){ el.onclick = function(){ c.style = el.getAttribute('data-st'); draw(); }; });
  var ang = $('dsch-angle'); if (ang) ang.oninput = function(){ c.angle = parseInt(ang.value, 10) || 0; $('dsch-angv').innerHTML = c.angle + '&deg;'; preview(); };
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-rows .dsch-in'), function(el){
    el.oninput = function(){
      var i = +el.getAttribute('data-i'), k = el.getAttribute('data-k');
      if (!c.labels) c.labels = []; if (!c.values) c.values = [];
      if (k === 'l') c.labels[i] = el.value; else c.values[i] = el.value.replace(/[^0-9.\-]/g, '') === '' ? '' : el.value.replace(/[^0-9.\-]/g, '');
      preview();
    };
    el.onkeydown = function(e){ if (e.key === 'Enter') { e.preventDefault(); var nx = el.parentNode.nextSibling; if (!nx && (c.labels || []).length < 12) { addRow(); return; } var f = nx && nx.querySelector('.dsch-in'); if (f) f.focus(); } };
  });
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-del'), function(el){ el.onclick = function(){
    var i = +el.getAttribute('data-del'), r = rowsOf(c); r.splice(i, 1);
    if (!r.length) r.push({l:'', v:''});
    c.labels = r.map(function(x){ return x.l; }); c.values = r.map(function(x){ return x.v; }); draw();
  }; });
  var ad = $('dsch-addrow'); if (ad) ad.onclick = addRow;
  var un = $('dsch-unit'); if (un) un.onchange = function(){ c.unit = un.value === '_c' ? 'units' : un.value; draw(); };
  var cu = $('dsch-cunit'); if (cu) cu.oninput = function(){ c.unit = cu.value.trim(); preview(); };
  var mx = $('dsch-max'); if (mx) mx.oninput = function(){ c.max = mx.value.replace(/[^0-9.]/g, ''); preview(); };
  var tp = $('dsch-topic'); if (tp) tp.oninput = function(){ ST.topic = tp.value; preview(); };
  var nt = $('dsch-notes'); if (nt) nt.oninput = function(){ ST.notes = nt.value; preview(); };
  var bk = $('dsch-back'); if (bk) bk.onclick = function(){ ST.step = 1; draw(); };
  $('dsch-cancel').onclick = close;
  $('dsch-go').onclick = ST.mode === 'new' ? generate : apply;
}
function addRow(){
  var c = ST.chart, r = rowsOf(c); if (r.length >= 12) return;
  r.push({l:'', v:''}); c.labels = r.map(function(x){ return x.l; }); c.values = r.map(function(x){ return x.v; });
  draw();
  var ins = $('dsch-rows').querySelectorAll('.dsch-in[data-k="l"]'); if (ins.length) ins[ins.length - 1].focus();
}
var _pt = 0;
function preview(){
  clearTimeout(_pt);
  _pt = setTimeout(function(){
    if (!ST) return;
    var s = previewSlide(), pv = $('dsch-prev'); if (!pv) return;
    pv.innerHTML = renderAny(s, ST.v) + (s.chart._sample ? '<span class="dsch-sample">SAMPLE DATA</span>' : '');
    var vs = $('dsch-vars'); if (!vs) return;
    var T = tplActive(), list = T ? (T.variants('chart', s) || DC.VARIANTS) : DC.VARIANTS;
    vs.innerHTML = list.slice(0, 10).map(function(v){ return '<div class="dsch-var'+(v.id === ST.v ? ' on' : '')+'" data-v="'+v.id+'" title="'+esc(v.lbl)+'">'+renderAny(s, v.id)+'</div>'; }).join('');
    Array.prototype.forEach.call(vs.querySelectorAll('.dsch-var'), function(el){ el.onclick = function(){ ST.v = el.getAttribute('data-v'); preview(); }; });
  }, 60);
}
function cleanChart(c){
  var r = rowsOf(c).filter(function(x){ return x.v !== '' && isFinite(parseFloat(x.v)); });
  return {style:c.style, angle:c.angle || 0, unit:c.unit == null ? '%' : c.unit, max: c.max ? parseFloat(c.max) || '' : '',
          labels:r.map(function(x, i){ return x.l.trim() || ('Item ' + (i+1)); }), values:r.map(function(x){ return parseFloat(x.v); })};
}

/* ---------------- deck updates ---------------- */
function refresh(idx){
  if (window.renderEditList) try { window.renderEditList(); } catch(e){}
  if (window.dspRenderThumbs) window.dspRenderThumbs();
  if (window.dspSelectThumb) window.dspSelectThumb(idx);
  setTimeout(function(){ if (window.applyCanvas && window.S) { S.idx = idx; window.applyCanvas(idx, S.choices['slide_'+idx] || 'v1'); } }, 120);
}
function setChoice(idx, v){
  if (!window.S) return;
  S.choices = S.choices || {};
  S.choices['slide_'+idx] = v || 'v1';
  try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch(e){}
}
function insertSlide(at, slide){
  var arrs = deckArrays();
  arrs.forEach(function(a){ a.splice(at, 0, at === arrs[0].length ? slide : slide); });
  if (window.S && S.choices) {
    var nc = {};
    Object.keys(S.choices).forEach(function(k){ var m = k.match(/^slide_(\d+)$/); if (!m) { nc[k] = S.choices[k]; return; } var n = +m[1]; nc['slide_' + (n >= at ? n + 1 : n)] = S.choices[k]; });
    S.choices = nc;
  }
}

function generate(){
  var c = cleanChart(ST.chart), btn = $('dsch-go');
  var topic = (ST.topic || '').trim();
  if (!topic && !c.values.length) { toast('Type what the chart is about, or add some data.', 'error'); var tp = $('dsch-topic'); if (tp) tp.focus(); return; }
  if (window.G && typeof G.credits === 'number' && G.credits < cost()) { toast('Not enough credits. Need ' + cost() + ' credits.', 'error'); return; }
  btn.disabled = true; btn.innerHTML = 'Generating…';
  var lang = $('dsp-lang'), ttl = $('dsp-etitle');
  fetch(CFG.rest || '/wp-json/ds/v1/chart-slide', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce': window.NONCE || window.DSP_NONCE || CFG.nonce || ''},
    body: JSON.stringify({topic: topic, notes: ST.notes || '', chart: c, language: lang ? lang.value : 'English', deck_title: ttl ? ttl.textContent.trim() : ''})})
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d && d.credits_balance !== undefined && window.G) { G.credits = d.credits_balance; var ce = $('ds-cr-display'); if (ce) ce.textContent = Number(G.credits).toLocaleString(); }
    if (!d || !d.success || !d.slide) { btn.disabled = false; btn.innerHTML = '&#10024; Generate chart slide'; toast((d && (d.message || d.error)) || 'Could not generate the chart. Please try again.', 'error'); return; }
    var arrs = deckArrays(); if (!arrs.length) { close(); return; }
    var at = arrs[0].length, v = ST.v;
    insertSlide(at, d.slide);
    setChoice(at, v);
    close();
    if (window.dspAddSlideClose) window.dspAddSlideClose();
    refresh(at);
    toast('Chart slide added! (' + (d.cost || cost()) + ' credits used)');
  })
  .catch(function(){ btn.disabled = false; btn.innerHTML = '&#10024; Generate chart slide'; toast('Network error. Try again.', 'error'); });
}
function apply(){
  var c = cleanChart(ST.chart);
  if (!c.values.length) { toast('Add at least one value.', 'error'); return; }
  var idx = ST.idx;
  deckArrays().forEach(function(a){ if (a[idx]) { a[idx].chart = c; a[idx].type = 'chart'; } });
  setChoice(idx, ST.v);
  close();
  refresh(idx);
  toast('Chart updated');
}

/* ---------------- entry points ---------------- */
DC.openWizard = function(topic){
  var fam = 'pie';
  ST = {mode:'new', step:1, idx:-1, topic: topic || '', notes:'', v:'v1', chart:{style:'donut', unit:'%', angle:0, labels:['','','',''], values:['','','','']}};
  draw();
  void fam;
};
DC.openEditor = function(idx){
  var s = currentSlide(idx); if (!s || !s.chart) return;
  var c = JSON.parse(JSON.stringify(s.chart));
  c.labels = (c.labels || []).map(String); c.values = (c.values || []).map(function(v){ return v == null ? '' : String(v); });
  if (!DC.FAM[c.style]) c.style = 'donut';
  ST = {mode:'edit', step:2, idx:idx, topic:'', notes:'', v:(window.S && S.choices && S.choices['slide_'+idx]) || 'v1', chart:c};
  draw();
};

/* Add Slide modal: keep the normal screen, add a "Generate chart" option */
function hookAddSlide(){
  var ce = $('dsp-addslide-cost'); if (ce && CFG.cost && ce.textContent !== CFG.cost + ' credits') ce.textContent = CFG.cost + ' credits';
  var body = $('dsp-addslide-body');
  if (!body || $('dsch-addbtn')) return;
  injectCss();
  var b = document.createElement('button'); b.type = 'button'; b.id = 'dsch-addbtn';
  b.innerHTML = '<i>&#128202;</i><div><b>Generate a chart slide</b><span>Pie, donut, bar, line, area, gauges &amp; progress — type your numbers or let AI create them</span></div>';
  b.onclick = function(){ var inp = $('dsp-addslide-input'), t = inp ? inp.value.trim() : ''; if (window.dspAddSlideClose) window.dspAddSlideClose(); DC.openWizard(t); };
  body.appendChild(b);
}
/* clicking the chart on the main canvas opens the editor */
document.addEventListener('click', function(e){
  var bx = e.target && e.target.closest ? e.target.closest('#dsp-ppt-main-slide .dsch-box') : null;
  if (!bx) return;
  var i = parseInt(bx.getAttribute('data-dsch-idx'), 10);
  if (i >= 0) { e.preventDefault(); DC.openEditor(i); }
}, true);
/* conclusion strip edits (Classic chart layout) */
document.addEventListener('focusout', function(e){
  var el = e.target; if (!el || !el.getAttribute || el.getAttribute('data-ed-field') !== 'dsch-conc') return;
  var i = parseInt(el.getAttribute('data-ed-slide'), 10), t = (el.innerText || '').trim();
  deckArrays().forEach(function(a){ if (a[i]) a[i].conclusion = t; });
}, true);

/* ---------------- automatic chart slides ----------------
   After a deck is generated (AI or Preserve My Text), slides whose points
   carry 2+ percentages get a chart slide right after them (max 2 per deck).
   Reopened drafts are left as they were. */
var _loadTs = 0, _of = window.fetch;
window.fetch = function(url){
  try { var us = typeof url === 'string' ? url : (url && url.url) || ''; if (us.indexOf('/ds/v1/load-ppt') !== -1) _loadTs = Date.now(); } catch(e){}
  return _of.apply(this, arguments);
};
var _seen = typeof WeakSet === 'function' ? new WeakSet() : null, _seenArr = [];
function seen(o){ return _seen ? _seen.has(o) : _seenArr.indexOf(o) >= 0; }
function mark(o){ if (_seen) _seen.add(o); else _seenArr.push(o); }
/* "94%: Retention | up from 81%" -> "Retention: 94%, up from 81%" (stats items read as insight points) */
function pointText(x){
  var t = String(x || ''), m = t.match(/^\s*([-+]?[\d.,]+\s?[%xX$€£₹KMB]*)\s*:\s*(.+)$/);
  if (!m) return t;
  var parts = m[2].split('|'), lab = parts[0].trim(), rest = parts.slice(1).join(' ').trim();
  return lab + ': ' + m[1].trim() + (rest ? ', ' + rest.charAt(0).toLowerCase() + rest.slice(1) : '');
}
function autoScan(){
  var fs = $('dsp-editor-fullscreen');
  if (!fs || !fs.classList.contains('dsp-open') || !window.G || !G.pptData || !Array.isArray(G.pptData.slides)) return;
  var pd = G.pptData; if (seen(pd)) return;
  var th = $('dsp-ppt-thumbs'); if (!th || th.children.length !== pd.slides.length) return;
  if (!window.S || !S.data || !Array.isArray(S.data.slides) || S.data.slides.length !== pd.slides.length) return;
  mark(pd);
  if (Date.now() - _loadTs < 30000 || pd.__dschAuto || CFG.auto === false) return;
  pd.__dschAuto = 1;
  var sl = S.data.slides, found = [];
  if (sl.some(function(s){ return s && s.type === 'chart'; })) return;
  /* the slide count the user asked for is kept: a slide with percentage data
     becomes a chart slide itself (its text stays as the insight points) */
  for (var i = 1; i < sl.length - 1 && found.length < 2; i++) {
    var src = sl[i]; if (!src || (src.content || []).length > 4) continue;
    var ch = DC.detect(src); if (!ch) continue;
    found.push({i: i, chart: ch, content: (src.content || []).map(pointText)});
  }
  if (!found.length) return;
  found.forEach(function(f){
    deckArrays().forEach(function(a){ var s = a[f.i]; if (!s) return; s.__origType = s.type; s.type = 'chart'; s.chart = JSON.parse(JSON.stringify(f.chart)); s.content = f.content.slice(); s.auto = 1; });
    setChoice(f.i, 'v1');
  });
  var cur = (window.G && G.activeSlideIdx) || 0;
  refresh(cur);
  toast('📊 ' + found.length + ' slide' + (found.length > 1 ? 's' : '') + ' with percentages now show' + (found.length > 1 ? '' : 's') + ' a chart — click a chart to edit it.');
}
/* keep chart slides on a chart layout (other tools may pick an image layout id for every slide) */
function fixChoices(){
  if (!window.S || !S.data || !Array.isArray(S.data.slides) || !S.choices) return;
  var T = tplActive(), ok = {}; (T ? (T.variants('chart', {type:'chart'}) || DC.VARIANTS) : DC.VARIANTS).forEach(function(v){ ok[v.id] = 1; });
  var ch = false;
  S.data.slides.forEach(function(s, i){ if (s && s.type === 'chart' && S.choices['slide_'+i] && !ok[S.choices['slide_'+i]]) { S.choices['slide_'+i] = 'v1'; ch = true; } });
  if (ch) try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch(e){}
}
var _t = setInterval(function(){ hookAddSlide(); try { autoScan(); fixChoices(); } catch(e){ if (window.console) console.warn('[DS Charts]', e); } }, 700);
void _t;
})();


