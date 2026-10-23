
(function(){
  /* ---- 1. zero-size pattern ---- */
  function fix(W){
    try {
      var P = W && W.CanvasRenderingContext2D && W.CanvasRenderingContext2D.prototype;
      if (!P || P.__dsZeroPattern) return;
      var orig = P.createPattern;
      P.createPattern = function(img, rep){
        try {
          if (img && img.tagName === 'CANVAS' && (!img.width || !img.height)) {
            var c = W.document.createElement('canvas'); c.width = 1; c.height = 1;
            return orig.call(this, c, rep);
          }
        } catch (e) {}
        return orig.call(this, img, rep);
      };
      P.__dsZeroPattern = 1;
    } catch (e) {}
  }
  fix(window);
  try {
    new MutationObserver(function(ms){
      ms.forEach(function(m){ [].forEach.call(m.addedNodes, function(n){
        if (n && n.tagName === 'IFRAME') { try { fix(n.contentWindow); n.addEventListener('load', function(){ try { fix(n.contentWindow); } catch (e) {} }); } catch (e) {} }
      }); });
    }).observe(document.documentElement, {childList: true, subtree: true});
  } catch (e) {}

  /* ---- 2. background photos at full quality ---- */
  function px(v, total){ v = String(v || '').trim(); if (/%$/.test(v)) return parseFloat(v) / 100 * total; return parseFloat(v) || 0; }
  function load(doc, src){
    return new Promise(function(res){
      var im = new (doc.defaultView || window).Image();
      im.onload = function(){ res(im); }; im.onerror = function(){ res(null); };
      im.src = src; if (im.complete && im.naturalWidth) res(im);
      setTimeout(function(){ res(im.naturalWidth ? im : null); }, 4000);
    });
  }
  /* where the photo is drawn inside the box (CSS background-size / background-position rules) */
  function rect(cs, W, H, iw, ih){
    var sz = (cs.backgroundSize || 'auto').split(',')[0].trim(), dw, dh;
    if (sz === 'cover' || sz === 'contain') { var s = sz === 'cover' ? Math.max(W / iw, H / ih) : Math.min(W / iw, H / ih); dw = iw * s; dh = ih * s; }
    else {
      var p = sz.split(/\s+/), a = p[0], b = p[1] || 'auto';
      dw = a === 'auto' ? null : px(a, W); dh = b === 'auto' ? null : px(b, H);
      if (dw == null && dh == null) { dw = iw; dh = ih; } else if (dw == null) dw = iw * dh / ih; else if (dh == null) dh = ih * dw / iw;
    }
    var pos = (cs.backgroundPosition || '0% 0%').split(',')[0].trim().split(/\s+/);
    var x = pos[0] || '0%', y = pos[1] || '50%';
    var ox = /%$/.test(x) ? (W - dw) * parseFloat(x) / 100 : px(x, W), oy = /%$/.test(y) ? (H - dh) * parseFloat(y) / 100 : px(y, H);
    return {x: ox, y: oy, w: dw, h: dh};
  }
  async function prep(root){
    var undo = [];
    try {
      var doc = root.ownerDocument, win = doc.defaultView || window;
      var els = [root].concat([].slice.call(root.querySelectorAll('*')));
      for (var i = 0; i < els.length; i++) {
        var el = els[i], cs = win.getComputedStyle(el), bi = cs.backgroundImage;
        if (!bi || bi === 'none' || bi.indexOf('url(') !== 0) continue;
        if (/\)\s*,/.test(bi) || /gradient\(/.test(bi)) continue;                       /* several layers: leave as is */
        if (cs.position === 'static' || el.tagName === 'IMG') continue;
        var sz = (cs.backgroundSize || '').trim(), rep = (cs.backgroundRepeat || '').trim();
        if (sz !== 'cover' && sz !== 'contain' && !/no-repeat/.test(rep)) continue;      /* tiled patterns: leave as is */
        var src = (bi.match(/^url\(["']?(.*?)["']?\)$/) || [])[1]; if (!src) continue;
        var W = el.clientWidth, H = el.clientHeight; if (W < 40 || H < 40) continue;      /* small decorations: leave */
        var im = await load(doc, src); if (!im || !im.naturalWidth) continue;
        var r = rect(cs, W, H, im.naturalWidth, im.naturalHeight);
        var box = doc.createElement('div');
        box.setAttribute('data-ds-xq', '1');
        box.style.cssText = 'position:absolute;left:' + (cs.borderLeftWidth || '0px') + ';top:' + (cs.borderTopWidth || '0px') + ';width:' + W + 'px;height:' + H + 'px;overflow:hidden;z-index:-1;pointer-events:none;border-radius:' + cs.borderRadius + ';margin:0;padding:0;';
        box.style.left = '0px'; box.style.top = '0px';
        var pic = doc.createElement('img');
        pic.src = src; pic.alt = '';
        pic.style.cssText = 'position:absolute;left:' + r.x + 'px;top:' + r.y + 'px;width:' + r.w + 'px;height:' + r.h + 'px;max-width:none;max-height:none;margin:0;padding:0;border:0;opacity:1;';
        box.appendChild(pic);
        undo.push({el: el, bi: el.style.backgroundImage, bip: el.style.getPropertyPriority('background-image'), z: el.style.zIndex, zp: el.style.getPropertyPriority('z-index'), box: box});
        el.style.setProperty('background-image', 'none', 'important');
        if (cs.zIndex === 'auto') el.style.setProperty('z-index', '0', 'important');   /* keeps the photo behind the box's own text */
        el.insertBefore(box, el.firstChild);
      }
    } catch (e) {}
    return function(){
      undo.forEach(function(u){
        try {
          if (u.box.parentNode) u.box.parentNode.removeChild(u.box);
          u.el.style.removeProperty('background-image'); if (u.bi) u.el.style.setProperty('background-image', u.bi, u.bip);
          u.el.style.removeProperty('z-index'); if (u.z) u.el.style.setProperty('z-index', u.z, u.zp);
        } catch (e) {}
      });
    };
  }
  function wrap(h){
    if (!h || h.__dsXQ) return h;
    var w = function(el, opts){
      var args = arguments, self = this;
      if (!el || !el.ownerDocument) return h.apply(self, args);
      return prep(el).then(function(restore){
        var p;
        try { p = h.apply(self, args); } catch (e) { restore(); throw e; }
        return Promise.resolve(p).then(function(v){ restore(); return v; }, function(e){ restore(); throw e; });
      });
    };
    w.__dsXQ = 1;
    for (var k in h) { try { w[k] = h[k]; } catch (e) {} }
    return w;
  }
  try {
    var cur = window.html2canvas;
    Object.defineProperty(window, 'html2canvas', {
      configurable: true, enumerable: true,
      get: function(){ return cur; },
      set: function(v){ cur = wrap(v); }
    });
    if (cur) cur = wrap(cur);
  } catch (e) {}
})();
