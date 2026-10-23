try{

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

}catch(e){if(window.console)console.error(e);}
try{

(function(){
(function(){
  if (window.__dsSaveLimit) return; window.__dsSaveLimit = 1;
  var of = window.fetch, API = '/wp-json/', quietUntil = 0, open = false;
  function nonce(){ return window.DSP_NONCE || (window.wpApiSettings && wpApiSettings.nonce) || ''; }
  function api(path, method, body){ return of.call(window, API + path, {method:method || 'GET', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce':nonce()}, body:body ? JSON.stringify(body) : undefined}).then(function(r){ return r.json().catch(function(){ return {}; }); }); }
  function esc(s){ return String(s == null ? '' : s).replace(/[&<>"]/g, function(c){ return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;'}[c]; }); }
  function css(){
    if (document.getElementById('dssl2-css')) return; var s = document.createElement('style'); s.id = 'dssl2-css';
    s.textContent = '#dssl2{position:fixed;inset:0;z-index:1000000;background:rgba(4,7,14,.72);display:flex;align-items:center;justify-content:center;padding:18px;font-family:inherit}' +
      '#dssl2 .c{width:min(620px,100%);max-height:86vh;display:flex;flex-direction:column;background:#101627;border:1px solid rgba(255,255,255,.14);border-radius:16px;box-shadow:0 24px 70px rgba(0,0,0,.6);color:#e9ecf5;overflow:hidden}' +
      '#dssl2 .h{padding:20px 22px 6px;font-size:19px;font-weight:700;display:flex;gap:10px;align-items:center}' +
      '#dssl2 .p{padding:4px 22px 14px;font-size:14px;line-height:1.5;color:#b9c2d8}' +
      '#dssl2 .u{margin:0 22px 12px;padding:9px 12px;border-radius:10px;background:rgba(255,255,255,.06);font-size:13px;font-weight:600;display:flex;justify-content:space-between;gap:10px}' +
      '#dssl2 .u b{color:#ff8b8b}#dssl2 .u.ok b{color:#6fe3a5}' +
      '#dssl2 .l{flex:1;overflow:auto;padding:0 22px 6px;min-height:120px}' +
      '#dssl2 .g{margin:10px 0 6px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8e9ab8}' +
      '#dssl2 .r{display:flex;align-items:center;gap:12px;padding:9px 10px;border:1px solid rgba(255,255,255,.09);border-radius:10px;margin-bottom:7px;background:rgba(255,255,255,.03)}' +
      '#dssl2 .r img{width:54px;height:38px;object-fit:cover;border-radius:6px;background:#1c2438;flex:none}' +
      '#dssl2 .r .t{flex:1;min-width:0}#dssl2 .r .t div{font-size:13.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#dssl2 .r .t span{font-size:11.5px;color:#8e9ab8}' +
      '#dssl2 button{font:inherit;cursor:pointer;border-radius:9px;border:1px solid rgba(255,255,255,.18);background:transparent;color:#e9ecf5;padding:9px 18px;font-size:13.5px;font-weight:700}' +
      '#dssl2 .r button{padding:6px 12px;font-size:12px;color:#ff9c9c;border-color:rgba(255,120,120,.4);flex:none}#dssl2 .r button.sure{background:#d83a3a;color:#fff;border-color:#d83a3a}' +
      '#dssl2 .f{display:flex;justify-content:flex-end;gap:10px;padding:14px 22px 18px;border-top:1px solid rgba(255,255,255,.08)}' +
      '#dssl2 .f .ok{background:#2f6bff;border-color:#2f6bff;color:#fff}#dssl2 button:disabled{opacity:.45;cursor:not-allowed}' +
      '#dssl2 .e{padding:18px 0;text-align:center;color:#8e9ab8;font-size:13px}';
    document.head.appendChild(s);
  }
  function all(path, key){
    var out = [];
    function step(off, n){ return api(path + '?offset=' + off).then(function(d){ var a = (d && d[key]) || []; out = out.concat(a); if (a.length && d.next_offset && d.next_offset > off && n < 40) return step(d.next_offset, n + 1); return out; }); }
    return step(0, 0).catch(function(){ return out; });
  }
  /* returns a promise: true = room was made, save again; false = do not save */
  function ask(){
    if (open) return Promise.resolve(false); open = true; css();
    return new Promise(function(done){
      var o = document.createElement('div'); o.id = 'dssl2'; document.body.appendChild(o);
      var used = 0, total = 0;
      function close(v){ open = false; if (o.parentNode) o.parentNode.removeChild(o); done(v); }
      function step1(){
        o.innerHTML = '<div class="c"><div class="h">&#9888;&#65039; Saved items limit reached</div>' +
          '<div class="p">This presentation cannot be saved because your saved items are full. Delete some saved files to make room, then it will be saved.<br><br>You can still <b>download</b> this presentation without saving it.</div>' +
          '<div class="f"><button type="button" data-a="cancel">Cancel</button><button type="button" class="ok" data-a="ok">OK</button></div></div>';
      }
      function bar(){ var u = o.querySelector('.u'); if (!u) return; var room = total - used; u.className = 'u' + (room > 0 ? ' ok' : ''); u.innerHTML = '<span>Saved items used</span><b>' + used + ' of ' + total + (room > 0 ? ' &middot; room for ' + room : ' &middot; full') + '</b>'; var b = o.querySelector('[data-a="save"]'); if (b) b.disabled = !(room > 0); }
      function cap(){ return api('ds/v1/saved-limit-info').then(function(d){ if (d && d.success) { used = d.used; total = d.cap; } bar(); return d; }).catch(function(){}); }
      function row(kind, id, title, meta, thumb){ return '<div class="r" data-k="' + kind + '" data-id="' + esc(id) + '">' + (thumb ? '<img src="' + esc(thumb) + '" alt="" loading="lazy">' : '') + '<div class="t"><div>' + esc(title) + '</div><span>' + esc(meta) + '</span></div><button type="button" data-a="del">Delete</button></div>'; }
      function step2(){
        o.innerHTML = '<div class="c"><div class="h">Delete saved items</div><div class="p">Delete the files you no longer need, then press <b>OK</b> to save this presentation. Deleted files cannot be brought back.</div><div class="u"><span>Loading&hellip;</span></div><div class="l"><div class="e">Loading your saved items&hellip;</div></div>' +
          '<div class="f"><button type="button" data-a="cancel">Cancel</button><button type="button" class="ok" data-a="save" disabled>OK &ndash; save now</button></div></div>';
        Promise.all([cap(), all('ds/v1/my-ppts', 'drafts'), Promise.resolve([])]).then(function(r){
          var info = r[0] || {}, drafts = r[1] || [], built = r[2] || [], files = info.files || [], h = '';
          if (false) { h += '<div class="g">Downloaded PowerPoint files (' + built.length + ')</div>'; built.forEach(function(b){ h += row('built', b.id, b.title || b.filename, (b.date || '') + (b.size ? ' · ' + Math.round(b.size / 1024) + ' KB' : '')); }); }
          if (drafts.length) { h += '<div class="g">Saved presentations (' + drafts.length + ')</div>'; drafts.forEach(function(p){ h += row('draft', p.id, p.title, (p.slides_count ? p.slides_count + ' slides' : '') + (p.updated ? ' · ' + new Date(p.updated * 1000).toLocaleDateString() : '')); }); }
          if (files.length) { h += '<div class="g">Saved images, videos and audio (' + files.length + ')</div>'; files.forEach(function(f){ h += row('file', f.id, f.title || f.type || 'Saved file', (f.type || '') + (f.date ? ' · ' + f.date : ''), f.thumb); }); }
          var l = o.querySelector('.l'); if (l) l.innerHTML = h || '<div class="e">No saved items were found.</div>';
        });
      }
      o.addEventListener('click', function(e){
        var b = e.target.closest ? e.target.closest('button[data-a]') : null; if (!b) return;
        var a = b.getAttribute('data-a');
        if (a === 'cancel') return close(false);
        if (a === 'ok') return step2();
        if (a === 'save') return close(true);
        if (a === 'del') {
          var r = b.closest('.r'), kind = r.getAttribute('data-k'), id = r.getAttribute('data-id');
          if (!b.classList.contains('sure')) { b.classList.add('sure'); b.textContent = 'Delete?'; setTimeout(function(){ if (b.parentNode && !b.disabled) { b.classList.remove('sure'); b.textContent = 'Delete'; } }, 3500); return; }
          b.disabled = true; b.textContent = 'Deleting…';
          var q = kind === 'draft' ? api('ds/v1/delete-ppt', 'POST', {ppt_id:id}) : kind === 'built' ? api('ds/v1/delete-built-ppt', 'POST', {id:id}) : api('ds-hub/v1/delete/' + encodeURIComponent(id), 'POST', {});
          q.then(function(d){ if (d && (d.success || d.deleted)) { r.parentNode.removeChild(r); cap(); } else { b.disabled = false; b.classList.remove('sure'); b.textContent = 'Failed – retry'; } }).catch(function(){ b.disabled = false; b.classList.remove('sure'); b.textContent = 'Failed – retry'; });
        }
      });
      step1();
    });
  }
  window.dsSaveLimitAsk = ask;
  function fake(obj){ return new Response(JSON.stringify(obj), {status:200, headers:{'Content-Type':'application/json'}}); }
  var NOT = {success:false, error:'Not saved: saved items limit reached. You can still download this presentation.'};
  window.fetch = function(input, init){
    var url = typeof input === 'string' ? input : ((input && input.url) || '');
    if (url.indexOf('/ds/v1/save-ppt') < 0 || !init || typeof init.body !== 'string') return of.apply(this, arguments);
    var self = this;
    function attempt(n){
      return of.call(self, input, init).then(function(r){
        return r.clone().json().then(function(d){
          if (!(d && d.success === false && /saved-item limit/i.test(String(d.error || '')))) return r;
          if (Date.now() < quietUntil || n > 4) return fake(NOT);
          return ask().then(function(ok){ if (!ok) { quietUntil = Date.now() + 20000; return fake(NOT); } return attempt(n + 1); });
        }, function(){ return r; });
      });
    }
    return attempt(0);
  };
})();
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
  var D = window.DSTE; if (!D || !D.toHTML || D.__pngObj) return; D.__pngObj = 1;
  var th = D.toHTML, MAXN = 6, MAXPX = 1000, MAXLEN = 2800000;
  function ok(s){ return typeof s === 'string' && s.length < MAXLEN && /^data:image\/png;base64,[A-Za-z0-9+\/=]+$/.test(s); }
  function slides(){ return (window.S && S.data && S.data.slides) || null; }
  function list(sl){ return sl && Array.isArray(sl.png_objects) ? sl.png_objects : []; }
  function pc(v, t){ return (Math.round((+v || 0) / t * 100000) / 1000) + '%'; }
  function toast(m){ if (window.dspToast) window.dspToast(m); }
  /* 1. every PNG object of the slide is drawn on top of the layout - in the editor, the thumbnails and the picture / PDF downloads */
  D.toHTML = function(page, idx, opts){
    var h = th.apply(this, arguments);
    try {
      var o = opts || {}, sl = o.slide || {}, edit = o.edit !== false && idx >= 0, add = '';
      list(sl).slice(0, MAXN).forEach(function(b, k){
        if (!b || !ok(b.src)) return;
        add += '<div style="position:absolute;left:' + pc(b.x, 1280) + ';top:' + pc(b.y, 720) + ';width:' + pc(b.w, 1280) + ';height:' + pc(b.h, 720) + ';z-index:40;' +
          (edit ? 'pointer-events:auto;cursor:move;touch-action:none;--ds-obj:' + idx + '_' + k + ';' : 'pointer-events:none;') + '">' +
          '<img src="' + b.src + '" alt="" draggable="false" style="position:absolute;left:0;top:0;width:100%;height:100%;display:block;pointer-events:none;-webkit-user-select:none;user-select:none">' +
          (edit ? '<span data-dsobj="del" title="Remove this picture">×</span><span data-dsobj="size" title="Drag to resize"></span>' : '') + '</div>';
      });
      if (edit && o.slide && list(sl).length < MAXN) add += '<div data-dsobj="add" style="--ds-objadd:' + idx + ';" title="Add a PNG picture with a transparent background (a person, a product, any object)">+ Add PNG</div>';
      if (add) { var i = h.lastIndexOf('</div>'); if (i > 0) h = h.slice(0, i) + add + h.slice(i); }
    } catch (e) {}
    return h;
  };
  var st = document.createElement('style');
  st.textContent =
    '[style*="--ds-obj:"]>span[data-dsobj]{position:absolute;opacity:0;transition:opacity .12s;pointer-events:auto}' +
    '[style*="--ds-obj:"]:hover,[style*="--ds-obj:"].ds-obj-on{outline:1px dashed #0A6CCF;outline-offset:1px}' +
    '[style*="--ds-obj:"]:hover>span[data-dsobj],[style*="--ds-obj:"].ds-obj-on>span[data-dsobj]{opacity:1}' +
    'span[data-dsobj="del"]{right:-1.1cqw;top:-1.1cqw;width:2.2cqw;height:2.2cqw;min-width:16px;min-height:16px;border-radius:50%;background:#E5484D;color:#fff;font:700 1.5cqw/2.2cqw Arial,sans-serif;text-align:center;cursor:pointer}' +
    'span[data-dsobj="size"]{right:-.8cqw;bottom:-.8cqw;width:1.6cqw;height:1.6cqw;min-width:12px;min-height:12px;border-radius:3px;background:#0A6CCF;border:2px solid #fff;box-sizing:border-box;cursor:nwse-resize}' +
    '[data-dsobj="add"]{position:absolute;left:1.2cqw;bottom:1.2cqw;z-index:41;padding:.45cqw 1.1cqw;border-radius:99px;background:rgba(17,24,39,.88);color:#fff;font:600 1.1cqw/1.6 Arial,sans-serif;cursor:pointer;opacity:0;transition:opacity .15s;pointer-events:auto;white-space:nowrap}' +
    '*:hover>[data-dsobj="add"]{opacity:1}[data-dsobj="add"]:hover{background:#0A6CCF}';
  document.head.appendChild(st);
  function ref(el){ var m = /--ds-obj:(\d+)_(\d+)/.exec(el.getAttribute('style') || ''); if (!m) return null; var ss = slides(), i = +m[1], k = +m[2]; if (!ss || !ss[i]) return null; var b = list(ss[i])[k]; return b ? {i:i, k:k, b:b, sl:ss[i]} : null; }
  function refresh(){ try { D.refreshEditor(); } catch (e) {} }
  /* 2. add: pick a PNG, keep its transparency, place it in the middle of the slide */
  function pick(idx){
    var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/png,image/webp,image/gif'; inp.style.display = 'none'; document.body.appendChild(inp);
    inp.addEventListener('change', function(){
      var f = inp.files && inp.files[0]; inp.remove(); if (!f) return;
      var url = URL.createObjectURL(f), im = new Image();
      im.onload = function(){
        try {
          var nw = im.naturalWidth, nh = im.naturalHeight, data = '', m = MAXPX;
          for (var t = 0; t < 4; t++) {
            var s = Math.min(1, m / Math.max(nw, nh)), c = document.createElement('canvas');
            c.width = Math.max(1, Math.round(nw * s)); c.height = Math.max(1, Math.round(nh * s));
            c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
            data = c.toDataURL('image/png'); if (data.length < MAXLEN) break; m = Math.round(m * 0.7);
          }
          var ss = slides();
          if (!ok(data) || !ss || !ss[idx]) { toast('This picture could not be added.'); }
          else {
            var k = Math.min(420 / nw, 420 / nh), w = Math.round(nw * k), h = Math.round(nh * k);
            if (!Array.isArray(ss[idx].png_objects)) ss[idx].png_objects = [];
            if (ss[idx].png_objects.length >= MAXN) toast('A slide can hold ' + MAXN + ' PNG pictures.');
            else { ss[idx].png_objects.push({src:data, x:Math.round((1280 - w) / 2), y:Math.round((720 - h) / 2), w:w, h:h}); refresh(); toast('PNG added. Drag it into place, pull the blue corner to resize. Click Save to keep it.'); }
          }
        } catch (e) { toast('This picture could not be read.'); }
        URL.revokeObjectURL(url);
      };
      im.onerror = function(){ URL.revokeObjectURL(url); toast('This picture could not be read.'); };
      im.src = url;
    });
    inp.click();
  }
  window.dsPngObjAdd = pick;
  /* 3. move, resize, remove */
  var drag = null;
  document.addEventListener('pointerdown', function(e){
    var t = e.target; if (!t || !t.closest) return;
    var el = t.closest('[style*="--ds-obj:"]'); if (!el) return;
    var r = ref(el); if (!r) return;
    e.preventDefault(); e.stopPropagation();
    var mode = t.getAttribute && t.getAttribute('data-dsobj');
    if (mode === 'del') { drag = {del:r}; return; }
    var root = el.parentElement, rc = root.getBoundingClientRect(); if (!rc.width) return;
    drag = {el:el, r:r, mode:mode === 'size' ? 'size' : 'move', k:1280 / rc.width, x0:e.clientX, y0:e.clientY, bx:+r.b.x || 0, by:+r.b.y || 0, bw:+r.b.w || 10, bh:+r.b.h || 10, moved:false};
    el.classList.add('ds-obj-on');
    try { el.setPointerCapture(e.pointerId); } catch (x) {}
  }, true);
  document.addEventListener('pointermove', function(e){
    if (!drag || !drag.el) return;
    e.preventDefault();
    var dx = (e.clientX - drag.x0) * drag.k, dy = (e.clientY - drag.y0) * drag.k, b = drag.r.b;
    if (Math.abs(dx) + Math.abs(dy) > 2) drag.moved = true;
    if (drag.mode === 'move') {
      b.x = Math.round(Math.max(-drag.bw * 0.8, Math.min(1280 - drag.bw * 0.2, drag.bx + dx)));
      b.y = Math.round(Math.max(-drag.bh * 0.8, Math.min(720 - drag.bh * 0.2, drag.by + dy)));
    } else {
      var f = Math.max(24 / drag.bw, Math.min(1600 / drag.bw, (drag.bw + Math.max(dx, dy * drag.bw / drag.bh)) / drag.bw));
      b.w = Math.round(drag.bw * f); b.h = Math.round(drag.bh * f);
    }
    var s = drag.el.style; s.left = pc(b.x, 1280); s.top = pc(b.y, 720); s.width = pc(b.w, 1280); s.height = pc(b.h, 720);
  }, true);
  function end(e){
    if (!drag) return;
    var d = drag; drag = null;
    if (d.del) { var a = list(d.del.sl), k = a.indexOf(d.del.b); if (k >= 0) a.splice(k, 1); if (!a.length) delete d.del.sl.png_objects; refresh(); toast('Picture removed.'); return; }
    d.el.classList.remove('ds-obj-on');
    if (d.moved) refresh();
  }
  document.addEventListener('pointerup', end, true);
  document.addEventListener('pointercancel', end, true);
  document.addEventListener('click', function(e){
    var t = e.target; if (!t || !t.closest) return;
    var a = t.closest('[data-dsobj="add"]');
    if (a) { var m = /--ds-objadd:(\d+)/.exec(a.getAttribute('style') || ''); e.preventDefault(); e.stopPropagation(); if (m) pick(parseInt(m[1], 10)); return; }
    if (t.closest('[style*="--ds-obj:"]')) { e.preventDefault(); e.stopPropagation(); }
  }, true);
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
(function(){
  if (window.__dsDeckCovers) return; window.__dsDeckCovers = 1;
  var covers = null, loading = null, fonts = {}, timer = 0;
  function D(){ return window.DSTE; }
  function nonce(){ return window.DSP_NONCE || (window.wpApiSettings && wpApiSettings.nonce) || ''; }
  function load(){
    if (loading) return loading;
    loading = fetch('/wp-json/ds/v1/my-ppt-covers', {credentials:'same-origin', headers:{'X-WP-Nonce':nonce()}}).then(function(r){ return r.json(); }).then(function(d){ covers = (d && d.covers) || {}; loading = null; return covers; }).catch(function(){ covers = covers || {}; loading = null; return covers; });
    return loading;
  }
  function font(T){ var gf = T && T.fonts && T.fonts.gf; if (!gf || fonts[gf]) return; fonts[gf] = 1; var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = 'https://fonts.googleapis.com/css2?family=' + gf.split('|').join('&family=') + '&display=swap'; document.head.appendChild(l); }
  function paint(card){
    var id = card.id.slice(9), th = card.firstElementChild; if (!th) return true;
    var c = covers[id]; if (!c) return false;
    var d = D(), sig = id + '|' + (c.sig || '');
    if (th.getAttribute('data-ds-cov') === sig && (c.tpl === 'classic' || th.querySelector('.ds-cov'))) return true;
    if (c.tpl && c.tpl !== 'classic' && d && d.reg && d.reg[c.tpl] && d.renderHTML && c.slide) {
      try {
        var T = d.themed ? d.themed(d.reg[c.tpl], c.theme || '') : d.reg[c.tpl]; font(d.reg[c.tpl]);
        var h = d.renderHTML(T, c.slide, c.v || 'v1', 0, {slides:[c.slide], title:c.slide.title || ''}, {edit:false});
        var old = th.querySelector('.ds-cov'); if (old) old.remove();
        var layer = document.createElement('div'); layer.className = 'ds-cov'; layer.style.cssText = 'position:absolute;inset:0;z-index:1;pointer-events:none;font-size:initial;text-align:left;';
        layer.innerHTML = h; th.appendChild(layer);
      } catch (e) {}
    } else {
      /* Classic decks: the built-in cover preview was sized for small cards - scale it to the card */
      var w = th.offsetWidth; if (w > 0) th.style.fontSize = (w * 0.0146).toFixed(2) + 'px';
    }
    th.setAttribute('data-ds-cov', sig);
    return true;
  }
  function run(){
    var cards = document.querySelectorAll('[id^="dsp-card-ppt_"]'); if (!cards.length) return;
    if (!covers) { load().then(run); return; }
    var miss = false; for (var i = 0; i < cards.length; i++) if (!paint(cards[i])) miss = true;
    if (miss && !run._re) { run._re = 1; covers = null; load().then(function(){ run(); setTimeout(function(){ run._re = 0; }, 4000); }); }
  }
  function soon(){ clearTimeout(timer); timer = setTimeout(run, 120); }
  new MutationObserver(function(ms){ for (var i = 0; i < ms.length; i++) { var t = ms[i].target; if (t && t.closest && t.closest('.ds-cov')) continue; soon(); return; } }).observe(document.body, {childList:true, subtree:true});
  window.addEventListener('resize', function(){ var a = document.querySelectorAll('[id^="dsp-card-ppt_"] > [data-ds-cov]'); for (var i = 0; i < a.length; i++) if (!a[i].querySelector('.ds-cov')) a[i].removeAttribute('data-ds-cov'); soon(); });
  /* a save changes the cover: read the list again */
  var of = window.fetch; window.fetch = function(input){ var u = typeof input === 'string' ? input : ((input && input.url) || ''); var p = of.apply(this, arguments); if (u.indexOf('/ds/v1/save-ppt') >= 0) p.then(function(){ covers = null; soon(); }, function(){}); return p; };
  soon();
})();
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
(function(){
  var tries = 0;
  function install(){
    var DI = window.DS_DOCIMPORT;
    if (!DI || !DI.read || !DI.heuristic || !DI.toDeck) { if (++tries < 80) setTimeout(install, 250); return; }
    if (DI.__slideMarks) return; DI.__slideMarks = 1;
    var read = DI.read, heur = DI.heuristic, toDeck = DI.toDeck, EXACT = null;
    var MARK = /(?:^|\n)[ \t]*Slide\s+\d+\s*[:\-\u2013\u2014.]/gi;
    function lineOf(b){ return b && b.t ? (b.k === 'li' ? '- ' + b.t : b.t) : ''; }
    /* the author wrote "Slide 1: ...", "Slide 2: ..." - every marker starts its own slide */
    function exact(text){
      if (typeof window.dspPreserveParse !== 'function') return null;
      var t = String(text || '').replace(/\r/g, '');
      if ((t.match(MARK) || []).length < 2) return null;
      t = t.replace(/(^|\n)([ \t]*Slide\s+\d+)\s*[\-\u2013\u2014.]\s*/gi, '$1$2: ').replace(/\n[ \t]*(Slide\s+\d+\s*:)/gi, '\n\n$1');
      var s = null; try { s = window.dspPreserveParse(t); } catch (e) { s = null; }
      return s && s.length >= 2 ? s : null;
    }
    DI.read = async function(file){
      var got = await read.apply(this, arguments);
      try {
        if (got && !got.slides) {
          var ext = String((file && file.name) || '').split('.').pop().toLowerCase(), text = '';
          if (ext === 'txt' || ext === 'md' || ext === 'markdown') text = await file.text();
          else text = (got.blocks || []).map(lineOf).filter(Boolean).join('\n');
          var s = exact(text);
          if (s) {
            EXACT = {file: file, slides: s};
            return {info: got.info, slides: s.map(function(x){ return {title: x.title || '', sub: x.subtitle || '', lines: (x.content || []).slice(), img: null}; })};
          }
        }
        if (EXACT && EXACT.file === file) EXACT = null;
      } catch (e) {}
      return got;
    };
    DI.heuristic = function(secs){
      var r = heur.apply(this, arguments);
      try { var a = DI.attached && DI.attached(); if (EXACT && a && a.file === EXACT.file && secs === a.secs && r) r.__dsExact = EXACT.slides; } catch (e) {}
      return r;
    };
    DI.toDeck = function(slides){
      if (slides && slides.__dsExact) { try { return JSON.parse(JSON.stringify(slides.__dsExact)); } catch (e) {} }
      return toDeck.apply(this, arguments);
    };
  }
  install();
})();
})();

}catch(e){if(window.console)console.error(e);}
try{

(function () {
  'use strict';

  /* ──────────────────────────────────────────────────────────────────────
     DS PPT — Background autosave + no-op save guard

     Two problems this solves:

     1. Saving an unchanged deck. Opening a saved presentation and pressing
        Save re-encoded every image and pushed several MB back to the server
        for a deck that had not changed by a single character.

     2. Saving blocked the user. The Save button raises a full-screen
        "Saving..." overlay and the user waits behind it.

     After this snippet: the deck is fingerprinted whenever it is loaded or
     saved. A Save with no changes returns instantly and sends nothing. A
     real edit is written back quietly in the background a couple of seconds
     later, with a small status chip in the corner instead of a modal.
     ────────────────────────────────────────────────────────────────────── */

  var SAVED = null;      // fingerprint of what the server currently holds
  var timer = null;
  var saving = false;
  var queued = false;
  var watchdog = null;

  /* ── status chip (non-blocking) ─────────────────────────────────────── */

  var chipEl = null;
  var chipHide = null;

  function chip(text, tone) {
    if (!document.body) return;
    if (!chipEl) {
      chipEl = document.createElement('div');
      chipEl.id = 'ds-autosave-chip';
      chipEl.style.cssText =
        'position:fixed;right:18px;bottom:18px;z-index:2147483000;' +
        'padding:8px 14px;border-radius:999px;font:600 12px/1 -apple-system,' +
        'BlinkMacSystemFont,"Segoe UI",sans-serif;color:#fff;background:#111827;' +
        'border:1px solid #374151;box-shadow:0 6px 20px rgba(0,0,0,.35);' +
        'opacity:0;transition:opacity .2s;pointer-events:none;';
      document.body.appendChild(chipEl);
    }
    chipEl.textContent = text;
    chipEl.style.background = tone === 'error' ? '#7f1d1d' : '#111827';
    chipEl.style.opacity = '1';
    clearTimeout(chipHide);
    if (tone !== 'busy') {
      chipHide = setTimeout(function () {
        if (chipEl) chipEl.style.opacity = '0';
      }, 2200);
    }
  }

  /* ── fingerprint ────────────────────────────────────────────────────── */

  function slides() {
    if (window.S && window.S.data && window.S.data.slides && window.S.data.slides.length) {
      return window.S.data.slides;
    }
    if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides.length) {
      return window.G.pptData.slides;
    }
    return null;
  }

  function safe(v) {
    try { return JSON.stringify(v == null ? '' : v); } catch (e) { return ''; }
  }

  /* Images are megabytes each, so they are represented by their length plus
     their last 64 characters — enough to notice a swap, cheap to compute. */
  function imgKey(s) {
    var d = (s && s.image_data) || '';
    if (typeof d !== 'string' || !d) return '0';
    return d.length + ':' + d.slice(-64);
  }

  function fingerprint() {
    var sl = slides();
    if (!sl) return null;
    var design = document.getElementById('dsp-design');
    var parts = [
      (window.G && window.G.pptTitle) || '',
      design ? design.value : '',
      sl.length
    ];
    try { parts.push(sessionStorage.getItem('dspVPC') || ''); } catch (e) { parts.push(''); }
    for (var i = 0; i < sl.length; i++) {
      var s = sl[i] || {};
      parts.push([
        s.type || '',
        s.title || '',
        s.subtitle || '',
        safe(s.content),
        safe(s.icons || s.icon),
        safe(s.image_position),
        safe(s.ov), (i === 0 ? safe(window.dsDeckStyleGet && window.dsDeckStyleGet()) : ''),   /* DS Deck Style: deck style + per-object colours / sizes count as changes */
        s.image_market_id || '',
        imgKey(s)
      ].join(''));
    }
    return parts.join('');
  }

  function markClean() {
    var f = fingerprint();
    if (f !== null) SAVED = f;
  }

  function isDirty() {
    var f = fingerprint();
    return f !== null && SAVED !== null && f !== SAVED;
  }

  /* ── watch save-ppt / load-ppt without disturbing the other wrappers ─── */

  var _of = window.fetch;
  if (_of) {
    window.fetch = function (url, opts) {
      var us = typeof url === 'string' ? url : (url && url.url) || '';
      var p = _of.apply(this, arguments);

      if (us.indexOf('/ds/v1/load-ppt') !== -1) {
        /* Give the editor a moment to populate S.data / G.pptData, then
           treat what is on screen as exactly what the server holds. */
        p.then(function () {
          setTimeout(markClean, 2500);
          setTimeout(markClean, 6000);
        }).catch(function () {});
      }

      if (us.indexOf('/ds/v1/save-ppt') !== -1) {
        var snap = fingerprint();
        p.then(function (r) { return r.clone().json(); })
         .then(function (d) {
           if (d && d.success) {
             if (snap !== null) SAVED = snap;
             chip('Saved');
           } else {
             chip('Save failed', 'error');
           }
           finishSave();
         })
         .catch(function () { chip('Save failed', 'error'); finishSave(); });
      }

      return p;
    };
  }

  function finishSave() {
    saving = false;
    clearTimeout(watchdog);
    if (queued) { queued = false; schedule(); }
  }

  /* ── background autosave ────────────────────────────────────────────── */

  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(run, 2000);
  }

  function run() {
    if (!window.G || !window.G.pptId) return;     // never auto-create a draft
    if (saving) { queued = true; return; }
    if (!isDirty()) return;
    if (typeof window.dspSaveDraft !== 'function') return;
    saving = true;
    chip('Saving…', 'busy');
    /* Called directly, not via the button — the blocking overlay in the
       layout panel only listens for clicks on #dsp-savebtn. */
    try { window.dspSaveDraft(); } catch (e) { finishSave(); }
    clearTimeout(watchdog);
    watchdog = setTimeout(function () {
      if (saving) { saving = false; chip('Save failed', 'error'); }
    }, 90000);
  }

  setInterval(function () {
    if (!window.G || !window.G.pptId) return;
    if (SAVED === null) { markClean(); return; }
    if (saving) return;
    if (isDirty()) schedule();
  }, 3000);

  /* ── manual Save: do nothing when nothing changed ───────────────────── */

  function installSaveGuard() {
    if (typeof window.dspSaveDraft !== 'function') return;
    if (window.dspSaveDraft.__dsNoopGuard) return;
    var orig = window.dspSaveDraft;
    var guarded = function () {
      if (window.G && window.G.pptId && SAVED !== null && !isDirty()) {
        chip('No changes to save');
        if (typeof window.dspToast === 'function') {
          window.dspToast('Already saved — no changes', 'success');
        }
        return;
      }
      return orig.apply(this, arguments);
    };
    guarded.__dsNoopGuard = true;
    window.dspSaveDraft = guarded;
  }

  setInterval(installSaveGuard, 800);
  installSaveGuard();

  /* Don't lose an edit that is still pending when the tab closes. */
  window.addEventListener('beforeunload', function (e) {
    if (window.G && window.G.pptId && !saving && isDirty()) {
      e.preventDefault();
      e.returnValue = '';
      return '';
    }
  });
})();

}catch(e){if(window.console)console.error(e);}
