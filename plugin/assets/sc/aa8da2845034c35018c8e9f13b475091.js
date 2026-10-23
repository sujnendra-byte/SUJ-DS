
(function(){
  var D = window.DSTE; if (!D || !D.renderPage || !D.toHTML || D.__frost) return; D.__frost = 1;
  var rp = D.renderPage, th = D.toHTML, W = 1280, H = 720, SC = 0.4, MAXC = 16, cache = {}, keys = [], imgs = {}, rfT = null;
  function rgb(c){ c = String(c || '').replace('#', ''); if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2]; if (!/^[0-9a-fA-F]{6}$/.test(c)) c = '000000'; return [parseInt(c.slice(0, 2), 16), parseInt(c.slice(2, 4), 16), parseInt(c.slice(4, 6), 16)]; }
  function col(c, op){ var x = rgb(c); return 'rgba(' + x[0] + ',' + x[1] + ',' + x[2] + ',' + (op == null ? 1 : op) + ')'; }
  function paint(x, g, bx, by, bw, bh, op){
    var gr, cx = bx + bw / 2, cy = by + bh / 2;
    if (g.type === 'radial') gr = x.createRadialGradient(cx, cy, 0, cx, cy, Math.max(1, Math.min(bw, bh) / 2));
    else { var a = (g.ang != null ? g.ang : 135) * Math.PI / 180, dx = Math.sin(a), dy = -Math.cos(a), L = (Math.abs(bw * dx) + Math.abs(bh * dy)) / 2; gr = x.createLinearGradient(cx - dx * L, cy - dy * L, cx + dx * L, cy + dy * L); }
    (g.stops || []).forEach(function(s){ try { gr.addColorStop(Math.max(0, Math.min(1, s[0] / 100)), col(s[1], (s[2] != null ? s[2] : 1) * (op != null ? op : 1))); } catch (e) {} });
    return gr;
  }
  function box(x, e){
    x.beginPath();
    if (e.t === 'ell' || e.shape === 'ell') x.ellipse(e.x + e.w / 2, e.y + e.h / 2, e.w / 2, e.h / 2, 0, 0, Math.PI * 2);
    else { var r = Math.max(0, Math.min(e.r || 0, e.w / 2, e.h / 2)); if (r && x.roundRect) x.roundRect(e.x, e.y, e.w, e.h, r); else x.rect(e.x, e.y, e.w, e.h); }
  }
  function ikey(s){ return s.length + ':' + s.slice(40, 120) + s.slice(-80); }
  function photo(slide){
    var s = slide && slide.image_data; if (typeof s !== 'string' || s.indexOf('data:image/') !== 0) return null;
    var k = ikey(s), o = imgs[k];
    if (!o) {
      o = imgs[k] = {im:new Image(), ok:false};
      o.im.onload = function(){ o.ok = true; clearTimeout(rfT); rfT = setTimeout(function(){ try { if (D.refreshEditor) D.refreshEditor(); } catch (e) {} try { document.dispatchEvent(new CustomEvent('ds-frost-ready')); } catch (e) {} }, 120); };
      o.im.src = s;
      var n = Object.keys(imgs); if (n.length > 40) delete imgs[n[0]];
    }
    return o.ok ? o.im : null;
  }
  function draw(x, e, slide){
    if (e.t === 'rect' || e.t === 'ell' || e.t === 'shape') {
      box(x, e);
      if (e.grad) { x.fillStyle = paint(x, e.grad, e.x, e.y, e.w, e.h, e.op); x.fill(); }
      else if (e.fill) { x.fillStyle = col(e.fill, e.op != null ? e.op : 1); x.fill(); }
      if (e.line && (e.lw || 1) >= 3) { x.strokeStyle = col(e.line, e.lop != null ? e.lop : 1); x.lineWidth = e.lw; x.stroke(); }
    } else if (e.t === 'path' && e.d) {
      x.beginPath();
      e.d.forEach(function(c){ if (c[0] === 'M') x.moveTo(c[1], c[2]); else if (c[0] === 'L') x.lineTo(c[1], c[2]); else if (c[0] === 'C') x.bezierCurveTo(c[1], c[2], c[3], c[4], c[5], c[6]); else if (c[0] === 'Q') x.quadraticCurveTo(c[1], c[2], c[3], c[4]); else if (c[0] === 'Z') x.closePath(); });
      if (e.fill) { x.fillStyle = col(e.fill, e.op != null ? e.op : 1); x.fill(); }
      if (e.stroke && (e.lw || 1) >= 3) { x.strokeStyle = col(e.stroke, e.lop != null ? e.lop : 1); x.lineWidth = e.lw; x.stroke(); }
    } else if (e.t === 'line' && (e.lw || 1) >= 3) {
      x.beginPath(); x.moveTo(e.x1, e.y1); x.lineTo(e.x2, e.y2); x.strokeStyle = col(e.color, e.op != null ? e.op : 1); x.lineWidth = e.lw; x.stroke();
    } else if (e.t === 'img' && e.slot !== 2) {
      var im = photo(slide);
      x.save(); box(x, e); x.clip();
      if (im && im.naturalWidth) {
        var p = (slide && slide.image_position) || {x:50, y:50}, k = Math.max(e.w / im.naturalWidth, e.h / im.naturalHeight), dw = im.naturalWidth * k, dh = im.naturalHeight * k;
        x.drawImage(im, e.x - (dw - e.w) * (p.x != null ? p.x : 50) / 100, e.y - (dh - e.h) * (p.y != null ? p.y : 50) / 100, dw, dh);
      } else if (e.ph && e.ph.grad) { x.fillStyle = paint(x, e.ph.grad, e.x, e.y, e.w, e.h); x.fillRect(e.x, e.y, e.w, e.h); }
      else { x.fillStyle = col((e.ph && e.ph.fill) || '#8899AA'); x.fillRect(e.x, e.y, e.w, e.h); }
      x.restore();
    }
  }
  function lum(c){ var x = rgb(c); return (0.2126 * x[0] + 0.7152 * x[1] + 0.0722 * x[2]) / 255; }
  function mx(a, b, t){ var A = rgb(a), B = rgb(b), o = '#'; for (var i = 0; i < 3; i++) o += ('0' + Math.round(A[i] + (B[i] - A[i]) * t).toString(16)).slice(-2); return o.toUpperCase(); }
  function hash(s){ var h = 5381; for (var i = 0; i < s.length; i++) h = ((h << 5) + h + s.charCodeAt(i)) | 0; return h; }
  function auto(T){ try { return !!(T && (T.frost || (T.s && T.s.card && T.s.card.kind === 'glass'))); } catch (e) { return false; } }
  function isGlass(e){ return e && e.t === 'rect' && e.fill && !e.grad && e.op != null && e.op > 0.02 && e.op < 0.6 && e.w >= 140 && e.h >= 44 && !(e.w >= W - 8 && e.h >= H - 8); }
  /* the blurred picture of everything that lies under the first glass box, cut out once for every glass box */
  function frostPage(p, T, slide){
    if (!p || !p.els || !p.els.length) return;
    var au = auto(T), idxs = [], dark = lum((p.bg && (p.bg.fill || (p.bg.grad && p.bg.grad.stops && p.bg.grad.stops[0] && p.bg.grad.stops[0][1]))) || '#FFFFFF') < 0.5;
    /* arrows and stage shapes of a frosted template become glass too: see-through, tinted with their own colour */
    if (T && T.frost) p.els.forEach(function(e, i){
      if (!(e && e.t === 'shape' && /^(chevron|homePlate|pentagon|rightArrow)$/.test(e.prst || '') && e.fill && !e.grad && (e.op == null || e.op >= 0.85) && e.w >= 100 && e.h >= 36 && e.frost == null)) return;
      e.op = 0.3; e.frost = 22;
      if (dark) for (var j = i + 1; j < p.els.length; j++) { var t = p.els[j]; if (t && t.t === 'text' && t.color && t.x + t.w / 2 > e.x && t.x + t.w / 2 < e.x + e.w && t.y + t.h / 2 > e.y && t.y + t.h / 2 < e.y + e.h && lum(t.color) < 0.45) t.color = '#FFFFFF'; }
    });
    p.els.forEach(function(e, i){ if (e && (e.frost || (au && e.frost !== 0 && isGlass(e))) && (e.t === 'rect' || e.t === 'shape') && idxs.length < MAXC) idxs.push(i); });
    if (!idxs.length) return;
    var first = idxs[0], lower = p.els.slice(0, first), im = null, hasImg = lower.some(function(e){ return e && e.t === 'img' && e.slot !== 2; });
    if (hasImg) im = photo(slide);
    var sig = JSON.stringify([p.bg, lower.map(function(e){ return e.t === 'text' || e.t === 'icon' ? 0 : e; }), idxs.map(function(i){ var e = p.els[i]; return [e.x, e.y, e.w, e.h, e.frost || 0]; }), hasImg ? [im ? 1 : 0, ikey(String((slide && slide.image_data) || '')), slide && slide.image_position] : 0]);
    var key = hash(sig) + ':' + sig.length + (T && T.grain ? 'g' : ''), pics = cache[key];
    if (!pics) {
      var cw = Math.round(W * SC), ch = Math.round(H * SC), a = document.createElement('canvas'); a.width = cw; a.height = ch;
      var x = a.getContext('2d'); x.scale(SC, SC);
      var bg = p.bg || {}; x.fillStyle = bg.grad ? paint(x, bg.grad, 0, 0, W, H) : col(bg.fill || '#FFFFFF'); x.fillRect(0, 0, W, H);
      lower.forEach(function(e){ try { if (e) draw(x, e, slide); } catch (er) {} });
      var rad = Math.max(4, (p.els[first].frost > 1 ? p.els[first].frost : 24) * SC), b = document.createElement('canvas'); b.width = cw; b.height = ch;
      var y = b.getContext('2d'); y.drawImage(a, 0, 0);
      if (typeof y.filter === 'string') { y.filter = 'blur(' + rad + 'px)'; y.drawImage(a, 0, 0); y.drawImage(a, 0, 0); y.filter = 'none'; }
      else { var f = Math.max(2, Math.round(rad / 1.6)), t = document.createElement('canvas'); t.width = Math.max(4, Math.round(cw / f)); t.height = Math.max(4, Math.round(ch / f)); var z = t.getContext('2d'); z.imageSmoothingEnabled = true; z.drawImage(a, 0, 0, t.width, t.height); y.imageSmoothingEnabled = true; y.drawImage(t, 0, 0, cw, ch); }
      if (T && T.grain) { try { var gd = y.getImageData(0, 0, cw, ch), dd = gd.data, sd = 7, ga = T.grain === true ? 12 : T.grain; for (var gi = 0; gi < dd.length; gi += 4) { sd = (sd * 16807) % 2147483647; var nz = ((sd & 255) - 128) / 128 * ga; dd[gi] += nz; dd[gi + 1] += nz; dd[gi + 2] += nz; } y.putImageData(gd, 0, 0); } catch (er) {} }   /* pebbled glass: a fine grain on the blurred backdrop */
      pics = idxs.map(function(i){
        var e = p.els[i], sx = Math.max(0, Math.floor(e.x * SC)), sy = Math.max(0, Math.floor(e.y * SC)), ex = Math.min(cw, Math.ceil((e.x + e.w) * SC)), ey = Math.min(ch, Math.ceil((e.y + e.h) * SC));
        if (ex - sx < 2 || ey - sy < 2) return null;
        var c = document.createElement('canvas'); c.width = ex - sx; c.height = ey - sy; c.getContext('2d').drawImage(b, sx, sy, ex - sx, ey - sy, 0, 0, ex - sx, ey - sy);
        return {src:c.toDataURL('image/jpeg', 0.82), x:sx / SC, y:sy / SC, w:(ex - sx) / SC, h:(ey - sy) / SC};
      });
      cache[key] = pics; keys.push(key); if (keys.length > 160) delete cache[keys.shift()];
    }
    for (var n = idxs.length - 1; n >= 0; n--) {
      var q = pics[n], g = p.els[idxs[n]];
            if (g.t === 'rect' && g.w >= 140 && g.h >= 60) {
        /* light on the glass. It is painted by a shape of exactly the size and corners of the box, so the shape has no edge of its
           own; the light is a gradient that is fully clear beside the band. Each template has its own kind of light: the angle,
           the width, where it sits, a single or a double band, a glow from the top, or light from a corner */
        var SH = [{a:118, k:0.62, c:36}, {a:135, k:0.8, c:30}, {a:62, k:0.62, c:38}, {a:104, k:0.42, c:34, twin:1}, {a:152, k:0.9, c:27}, {top:1}, {a:45, k:0.7, c:66}, {a:128, k:0.5, c:24, twin:1}, {corner:1, a:135}, {corner:1, a:225}];
        var PICK = {glasstower:3, glassbeam:1, glassarc:5, glassmesh:8, glasswave:4, glassslat:7, glassflow:6, glassflute:0, frostorbit:1, glassline:5};
        var tid = String((T && T.id) || ''), hsh = 0; for (var ci = 0; ci < tid.length; ci++) hsh = (hsh * 31 + tid.charCodeAt(ci)) % 9973;
        var st = (T && T.shine) || SH[PICK.hasOwnProperty(tid) ? PICK[tid] : hsh % SH.length], W0 = '#FFFFFF', stops;
        if (st.top) stops = [[0, W0, 0.15], [44, W0, 0], [100, W0, 0]];
        else if (st.corner) stops = [[0, W0, 0.17], [16, W0, 0.1], [42, W0, 0], [100, W0, 0]];      /* light falling in from one corner */
        else {
          var ra = st.a * Math.PI / 180, Lg = g.w * Math.abs(Math.sin(ra)) + g.h * Math.abs(Math.cos(ra));
          var bandPx = Math.max(44, Math.min(240, Math.min(g.w, g.h) * st.k)), hw = Math.min(28, bandPx / Lg * 50), ctr = st.c + (n % 2 === 1 ? 9 : 0);
          var t2 = st.twin ? Math.max(2.5, hw * 0.3) : 0, span = hw + (st.twin ? 3 + t2 * 2 : 0);
          ctr = Math.max(hw + 2, Math.min(98 - span, ctr));
          stops = [[0, W0, 0], [ctr - hw, W0, 0], [ctr - hw * 0.4, W0, 0.13], [ctr + hw * 0.4, W0, 0.13], [ctr + hw, W0, 0]];
          if (st.twin) stops.push([ctr + hw + 3, W0, 0], [ctr + hw + 3 + t2, W0, 0.09], [ctr + hw + 3 + t2 * 2, W0, 0]);
          stops.push([100, W0, 0]);
        }
        p.els.splice(idxs[n] + 1, 0, {t:'rect', x:g.x, y:g.y, w:g.w, h:g.h, r:g.r || 0, grad:{ang:st.top ? 180 : st.a, stops:stops}, frost:0});
      }
      if (q) p.els.splice(idxs[n], 0, {t:'pic', x:q.x, y:q.y, w:q.w, h:q.h, r:g.r || 0, src:q.src, prst:g.t === 'shape' ? g.prst : undefined});
    }
  }
  D.renderPage = function(T, slide){
    var p = rp.apply(this, arguments);
    /* charts on a frosted template use one colour family: the accent and its lighter and deeper tones (they follow the deck theme) */
    try { if (T && T.frost && p && p.els) p.els.forEach(function(e){ if (e && e.t === 'chart' && Array.isArray(e.colors) && e.colors.length) { var c0 = e.colors[0], bg = e.bgc || '#101010', o = [c0, mx(c0, '#FFFFFF', 0.55), mx(c0, bg, 0.45), mx(c0, '#FFFFFF', 0.8), mx(c0, bg, 0.65), mx('#FFFFFF', bg, 0.4)]; var old = e.colors, map = {}; e.colors = old.map(function(_, i){ return o[i % o.length]; }); old.forEach(function(c, i){ map[String(c).toUpperCase()] = e.colors[i]; }); p.els.forEach(function(x){ if (!x || x === e) return; ['fill', 'color', 'line'].forEach(function(k){ var v = typeof x[k] === 'string' ? map[x[k].toUpperCase()] : null; if (v) x[k] = v; }); }); } }); } catch (e) {}
    try { frostPage(p, T, slide); } catch (e) { if (window.console) console.warn('[frost]', e); }
    return p;
  };
  /* a 'pic' element is drawn in the page as a box that shows its own picture */
  D.toHTML = function(page, idx, opts){
    var n = 0, src = [];
    try { n = page && page.els ? page.els.filter(function(e){ return e && e.t === 'pic'; }).length : 0; } catch (e) {}
    if (!n) return th.apply(this, arguments);
    var cp = {}; for (var k in page) cp[k] = page[k];
    cp.els = page.els.map(function(e){
      if (!(e && e.t === 'pic')) return e;
      src.push(e.src); var v = 15 + src.length; return {t:e.prst ? 'shape' : 'rect', prst:e.prst, x:e.x, y:e.y, w:e.w, h:e.h, r:e.r || 0, fill:'#01FE' + (v < 16 ? '0' : '') + v.toString(16).toUpperCase()};
    });
    var a = Array.prototype.slice.call(arguments); a[0] = cp;
    var h = th.apply(this, a);
    src.forEach(function(s, i){ var m = 'background:rgba(1,254,' + (16 + i) + ',1);'; if (/^data:image\/(png|jpeg);base64,[A-Za-z0-9+\/=]+$/.test(s)) h = h.split(m).join('background-image:url(' + s + ');background-size:100% 100%;background-repeat:no-repeat;'); });
    return h;
  };
})();
