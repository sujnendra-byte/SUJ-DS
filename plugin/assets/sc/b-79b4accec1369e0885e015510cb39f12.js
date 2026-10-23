try{

(function(){
(function(){
  if (window.__dsTplHover) return; window.__dsTplHover = 1;
  var pop = null, cur = '', timer = 0, cache = {}, fonts = {};
  function D(){ return window.DSTE; }
  function css(){
    if (document.getElementById('ds-tplhov-css')) return;
    var s = document.createElement('style'); s.id = 'ds-tplhov-css';
    s.textContent = '#ds-tplhov{position:fixed;z-index:99990;pointer-events:none;box-sizing:border-box;padding:12px 14px 14px;border-radius:14px;background:#0f1422;border:1px solid rgba(255,255,255,.14);box-shadow:0 18px 50px rgba(0,0,0,.55);opacity:0;transform:translateY(4px);transition:opacity .14s ease,transform .14s ease}' +
      '#ds-tplhov.on{opacity:1;transform:none}' +
      '#ds-tplhov .h{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:0 0 9px;font:700 11px/1.2 system-ui,-apple-system,Segoe UI,sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#c9d3ea}' +
      '#ds-tplhov .h span:last-child{color:#6ea8ff;letter-spacing:0;text-transform:none;font-weight:600;font-size:12px}' +
      '#ds-tplhov .g{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}' +
      '#ds-tplhov .t{position:relative;aspect-ratio:16/9;border-radius:8px;overflow:hidden;background:#1a2030}' +
      '@media (hover:none){#ds-tplhov{display:none!important}}';
    document.head.appendChild(s);
  }
  function loadFont(T){
    var gf = T && T.fonts && T.fonts.gf; if (!gf || fonts[gf]) return; fonts[gf] = 1;
    var l = document.createElement('link'); l.rel = 'stylesheet';
    l.href = 'https://fonts.googleapis.com/css2?family=' + gf.split('|').join('&family=') + '&display=swap';
    document.head.appendChild(l);
  }
  function html(id){
    var d = D(); if (!d || !d.reg || !d.reg[id] || !d.SAMPLE || !d.renderHTML) return '';
    var key = id + '|' + (d.homeTheme || ''); if (cache[key]) return cache[key];
    try {
      var T = d.themed ? d.themed(d.reg[id], d.homeTheme || '') : d.reg[id], S = d.SAMPLE, sl = [S.cover, S.stats || S.p3, S.timeline || S.p3], deck = {slides:sl}, h = '';
      loadFont(d.reg[id]);
      sl.forEach(function(s, i){ h += '<div class="t">' + d.renderHTML(T, s, 'v1', i, deck, {edit:false}) + '</div>'; });
      cache[key] = h; return h;
    } catch (e) { return ''; }
  }
  function hide(){ clearTimeout(timer); cur = ''; if (pop) pop.classList.remove('on'); }
  function show(card){
    var id = card.getAttribute('data-id'); if (!id || id === 'classic') { hide(); return; }
    var h = html(id); if (!h) { hide(); return; }
    css();
    if (!pop) { pop = document.createElement('div'); pop.id = 'ds-tplhov'; document.body.appendChild(pop); }
    var d = D(), nm = (d.reg[id] && d.reg[id].name) || '';
    pop.innerHTML = '<div class="h"><span>' + String(nm).replace(/[<>&]/g, '') + '</span><span>3-Slide Sample Preview</span></div><div class="g">' + h + '</div>';
    var r = card.getBoundingClientRect(), vw = window.innerWidth, vh = window.innerHeight, w = Math.min(720, vw - 24), ph = w / 3 * 9 / 16 + 62;
    var left = Math.max(12, Math.min(vw - w - 12, r.left + r.width / 2 - w / 2)), top = r.bottom + 8;
    if (top + ph > vh - 8 && r.top - ph - 8 > 8) top = r.top - ph - 8;
    pop.style.width = w + 'px'; pop.style.left = left + 'px'; pop.style.top = top + 'px';
    pop.classList.add('on'); cur = id;
  }
  document.addEventListener('mouseover', function(e){
    var t = e.target, card = t && t.closest ? t.closest('#dste-grid .dste-card') : null;
    if (!card) { if (cur || timer) hide(); return; }
    var id = card.getAttribute('data-id'); if (id === cur) return;
    clearTimeout(timer); timer = setTimeout(function(){ show(card); }, 140);
  }, true);
  document.addEventListener('scroll', hide, true);
  document.addEventListener('mousedown', hide, true);
  window.addEventListener('blur', hide);
})();
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
(function(){
  var D = window.DSTE; if (!D || !D.kindOf || !D.renderPage || !D.items || D.__ptOverflow2) return; D.__ptOverflow2 = 1;
  var KINDS = {agenda:1, timeline:1, stats:1}, flags = {}, curId = '', ko = D.kindOf, rp = D.renderPage;
  function byCount(n){ return n <= 1 ? 'text' : n === 2 ? 'p2' : n === 3 ? 'p3' : n === 4 ? 'p4' : n <= 6 ? 'p56' : n <= 8 ? 'p78' : 'p9'; }
  function shown(p){ var m = {}, c = 0; (p && p.els || []).forEach(function(e){ if (e && e.ed && (e.ed.f === 'x-part' || e.ed.f === 'content-plain') && !m[e.ed.i]) { m[e.ed.i] = 1; c++; } }); return c; }
  function plain(slide){ var c = {}; for (var k in slide) c[k] = slide[k]; c.type = 'bullet'; return c; }
  /* the layout picker asks for the kind: answer with the many-point kind once a slide is known to overflow */
  D.kindOf = function(slide){
    var k = ko.apply(this, arguments);
    if (KINDS[k]) { var n = 0; try { n = D.items(slide).length; } catch (e) {} if (flags[curId + '|' + k + '|' + n]) return byCount(n); }
    return k;
  };
  D.renderPage = function(T, slide){
    if (T && T.id) curId = T.id;
    var a = Array.prototype.slice.call(arguments), k = '', n = 0, key = '';
    try { k = ko.call(D, slide); if (KINDS[k]) { n = D.items(slide).length; key = curId + '|' + k + '|' + n; } } catch (e) { k = ''; }
    if (key && flags[key]) { a[1] = plain(slide); return rp.apply(this, a); }
    var p = rp.apply(this, a);
    if (key && n >= 2) { try { if (shown(p) < n) { a[1] = plain(slide); var p2 = rp.apply(this, a); if (shown(p2) > shown(p)) { flags[key] = 1; p = p2; } } } catch (e) {} }
    return p;
  };
})();
})();

}catch(e){if(window.console)console.error(e);}
try{

(function(){
(function(){
  var D = window.DSTE; if (!D || !D.renderPage || !D.toHTML || D.__img2) return; D.__img2 = 1;
  var rp = D.renderPage, th = D.toHTML, MK = '#01FE02', MKCSS = 'background:rgba(1,254,2,1);';
  /* 1. the second picture box of a layout becomes picture slot 2 */
  D.renderPage = function(){
    var p = rp.apply(this, arguments);
    try { var n = 0; (p && p.els || []).forEach(function(e){ if (e && e.t === 'img') { n++; if (n === 2) e.slot = 2; } }); } catch (e) {}
    return p;
  };
  function okData(s){ return typeof s === 'string' && /^data:image\/(png|jpe?g);base64,[A-Za-z0-9+\/=]+$/.test(s); }
  /* 2. slot 2 is drawn from the slide's own second picture (image2_data), not from the first one */
  D.toHTML = function(page, idx, opts){
    var has = false; try { has = !!(page && page.els && page.els.some(function(e){ return e && e.t === 'img' && e.slot === 2; })); } catch (e) {}
    if (!has) return th.apply(this, arguments);
    var ph = null, els = page.els.map(function(e){
      if (!(e && e.t === 'img' && e.slot === 2)) return e;
      ph = e.ph; return {t:e.shape === 'ell' ? 'ell' : 'rect', x:e.x, y:e.y, w:e.w, h:e.h, r:e.r || 0, fill:MK};
    });
    var cp = {}; for (var k in page) cp[k] = page[k]; cp.els = els;
    var a = Array.prototype.slice.call(arguments); a[0] = cp;
    var h = th.apply(this, a), o = opts || {}, sl = o.slide || {}, edit = o.edit !== false && idx >= 0, img = okData(sl.image2_data) ? sl.image2_data : '';
    var bg = img ? 'background-image:url(' + img + ');background-size:cover;background-repeat:no-repeat;background-position:50% 50%;'
                 : 'background:' + (ph && ph.grad && D.cssGrad ? D.cssGrad(ph.grad) : '#8899AA') + ';';
    var i = h.indexOf(MKCSS); if (i < 0) return h;
    var j = h.indexOf('"', i), rest = h.slice(i + MKCSS.length, j);
    if (edit) rest = rest.replace('pointer-events:none;', '') + 'pointer-events:auto;cursor:pointer;--ds-img2' + (img ? '' : '-empty') + ':' + idx + ';';
    return h.slice(0, i) + bg + rest + h.slice(j);
  };
  /* 3. editor: click the second box to upload its own picture */
  var st = document.createElement('style');
  st.textContent = '[style*="--ds-img2"]{overflow:hidden}' +
    '[style*="--ds-img2"]::after{content:"Change picture 2";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font:600 1.15cqw Arial,sans-serif;background:rgba(0,0,0,.5);opacity:0;transition:opacity .15s;border-radius:inherit}' +
    '[style*="--ds-img2"]:hover::after{opacity:1}' +
    '[style*="--ds-img2-empty"]::after{content:"Click to add picture 2";background:transparent;opacity:.85}';
  document.head.appendChild(st);
  function slides(){ return (window.S && S.data && S.data.slides) || null; }
  function setImg(idx, data){
    var ss = slides(); if (!ss || !ss[idx]) return false;
    if (data) ss[idx].image2_data = data; else delete ss[idx].image2_data;
    try { D.refreshEditor(); } catch (e) {}
    return true;
  }
  window.dsImg2Set = setImg;
  function pick(idx){
    var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/*'; inp.style.display = 'none'; document.body.appendChild(inp);
    inp.addEventListener('change', function(){
      var f = inp.files && inp.files[0]; inp.remove(); if (!f) return;
      var url = URL.createObjectURL(f), im = new Image();
      im.onload = function(){
        try {
          var m = 1600, s = Math.min(1, m / Math.max(im.naturalWidth, im.naturalHeight)), c = document.createElement('canvas');
          c.width = Math.max(1, Math.round(im.naturalWidth * s)); c.height = Math.max(1, Math.round(im.naturalHeight * s));
          var x = c.getContext('2d'); x.fillStyle = '#FFFFFF'; x.fillRect(0, 0, c.width, c.height); x.drawImage(im, 0, 0, c.width, c.height);
          setImg(idx, c.toDataURL('image/jpeg', 0.86));
          if (window.dspToast) window.dspToast('Picture 2 added. Click Save to keep it.');
        } catch (e) { if (window.dspToast) window.dspToast('This picture could not be read.'); }
        URL.revokeObjectURL(url);
      };
      im.onerror = function(){ URL.revokeObjectURL(url); if (window.dspToast) window.dspToast('This picture could not be read.'); };
      im.src = url;
    });
    inp.click();
  }
  document.addEventListener('click', function(e){
    var t = e.target && e.target.closest ? e.target.closest('[style*="--ds-img2"]') : null; if (!t) return;
    var m = /--ds-img2(?:-empty)?:(\d+)/.exec(t.getAttribute('style') || ''); if (!m) return;
    e.preventDefault(); e.stopPropagation(); pick(parseInt(m[1], 10));
  }, true);
})();
})();

}catch(e){if(window.console)console.error(e);}
