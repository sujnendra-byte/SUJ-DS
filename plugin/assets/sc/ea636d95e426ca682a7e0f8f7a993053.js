
(function(){
if (window.__dsEditorExtras) return; window.__dsEditorExtras = 1;
var LS = { get: function(k, d){ try { var v = localStorage.getItem('dsx:' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } },
           set: function(k, v){ try { localStorage.setItem('dsx:' + k, JSON.stringify(v)); } catch (e) {} } };
var ICON = {
  lock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
  open: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>',
  copy: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>',
  del:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>',
  eye:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
  eyeoff:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18M10.6 5.1A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.1 6.1A17 17 0 0 0 2 12s3.5 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>',
  pic:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M21 16l-5-5-8 8"/></svg>'
};

/* ================= 3. popups: resize + lock ================= */
function titleOf(p){ var b = p.querySelector('.dsel-ph b, .dsel-tp-h b, .dsel-ic-h b'); return b ? b.textContent.trim() : ''; }
function keyOf(p){ return p.id === 'dsel-panel' ? 'panel:' + titleOf(p) : p.id; }
function isOpen(p){ return !!p && p.style.display !== 'none' && getComputedStyle(p).display !== 'none'; }
function locked(p){ return !!LS.get('lock:' + keyOf(p), false); }
function lockBtn(p){
  var h = p.querySelector('.dsel-ph, .dsel-tp-h, .dsel-ic-h'); if (!h) return;
  var b = h.querySelector('.dsx-lock');
  if (!b) {
    b = document.createElement('button'); b.type = 'button'; b.className = 'dsx-lock';
    var x = h.querySelector('[data-p="close"], .dsel-tp-x, .dsel-ic-x');
    if (x) h.insertBefore(b, x); else h.appendChild(b);
  }
  var on = locked(p);
  b.classList.toggle('on', on);
  b.title = on ? 'Locked: stays open until you press x. Click to unlock.' : 'Lock this window so it stays open';
  b.innerHTML = on ? ICON.lock : ICON.open;
}
function remember(p){ var r = p.getBoundingClientRect(); LS.set('box:' + keyOf(p), {x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height)}); }
function restore(p){
  var b = LS.get('box:' + keyOf(p), null); if (!b) return;
  var w = Math.min(b.w, innerWidth - 16), h = Math.min(b.h, innerHeight - 16);
  p.style.width = w + 'px'; p.style.height = h + 'px';
  p.style.left = Math.max(8, Math.min(b.x, innerWidth - w - 8)) + 'px';
  p.style.top = Math.max(8, Math.min(b.y, innerHeight - 60)) + 'px';
  p.style.right = 'auto'; p.style.bottom = 'auto'; p.style.maxHeight = 'none';
}
var closing = {};          /* popup id -> time the user pressed its x (or its own toggle button) */
var opener = {};           /* panel title -> {box:'dock'|'bar', a:'...'} */
var lastOpen = null, pendingPanel = null;
document.addEventListener('click', function(ev){
  var t = ev.target; if (!t || !t.closest) return;
  var lb = t.closest('.dsx-lock');
  if (lb) {
    ev.preventDefault(); ev.stopPropagation();
    var p = lb.closest('#dsel-panel, #dsel-thpop, #dsel-icpop'); if (!p) return;
    var on = !locked(p); LS.set('lock:' + keyOf(p), on);
    if (on) { remember(p); if (p.id === 'dsel-panel' && lastOpen) opener[titleOf(p)] = lastOpen; LS.set('opener', opener); }
    lockBtn(p); return;
  }
  if (t.closest('#dsel-panel [data-p="close"]')) { closing['dsel-panel'] = Date.now(); pendingPanel = null; return; }
  if (t.closest('#dsel-thpop .dsel-tp-x, .dsel-side-theme, #dsp-ed-theme-trigger')) closing['dsel-thpop'] = Date.now();
  if (t.closest('#dsel-icpop .dsel-ic-x, .dsel-iclib-b')) closing['dsel-icpop'] = Date.now();
  var ob = t.closest('#dsel-dock [data-a], #dsel-bar [data-a]');
  if (ob) { lastOpen = {box: ob.closest('#dsel-dock') ? 'dock' : 'bar', a: ob.getAttribute('data-a')}; closing['dsel-panel'] = Date.now(); /* a new panel replaces the old one */
    setTimeout(function(){ var p = document.getElementById('dsel-panel'); if (p && isOpen(p)) { var ti = titleOf(p); if (ti) { opener[ti] = lastOpen; LS.set('opener', opener); } } }, 120); }
}, true);
opener = LS.get('opener', {}) || {};
function reopenPanel(){
  var p = document.getElementById('dsel-panel'); if (!p || isOpen(p) || !pendingPanel) return;
  var o = opener[pendingPanel]; if (!o) { pendingPanel = null; return; }
  var b = document.querySelector((o.box === 'dock' ? '#dsel-dock' : '#dsel-bar') + ' [data-a="' + o.a + '"]');
  if (!b || !b.offsetParent) return;                 /* e.g. Fill needs an element: wait until one is selected */
  var want = pendingPanel; pendingPanel = null;
  b.click();
  setTimeout(function(){ var q = document.getElementById('dsel-panel'); if (q && isOpen(q) && titleOf(q) === want) restore(q); }, 80);
}
function watchPopup(p){
  if (!p || p.__dsx) return; p.__dsx = 1;
  p.classList.add('dsx-rs');
  var wasOpen = isOpen(p), title = p.id === 'dsel-panel' ? titleOf(p) : '';
  new MutationObserver(function(){
    lockBtn(p);
    var open = isOpen(p);
    if (wasOpen && !open) {
      var userClosed = Date.now() - (closing[p.id] || 0) < 600;
      if (p.id === 'dsel-panel') {
        if (!userClosed && title && LS.get('lock:panel:' + title, false)) { pendingPanel = title; setTimeout(reopenPanel, 60); }
      } else if (!userClosed && locked(p)) {
        p.style.display = 'flex';                        /* theme / icons popup: put it straight back */
        open = true;
      }
    }
    if (open) { var ti = p.id === 'dsel-panel' ? titleOf(p) : ''; if (ti) title = ti; }
    if (open && !wasOpen && locked(p)) restore(p);
    wasOpen = open;
  }).observe(p, {attributes: true, attributeFilter: ['style', 'class'], childList: true, subtree: false});
  /* keep the size the user drags to */
  var to = null;
  if (window.ResizeObserver) new ResizeObserver(function(){ clearTimeout(to); to = setTimeout(function(){ if (isOpen(p) && locked(p)) remember(p); }, 250); }).observe(p);
  lockBtn(p);
}
/* the element bar changes when an element is (de)selected: a locked settings popup comes back then */
var barObs = new MutationObserver(function(){ if (pendingPanel) setTimeout(reopenPanel, 60); });

/* ================= 4. thumbnails: copy / delete / hide ================= */
function deckS(){ return (window.S && S.data && S.data.slides) ? S.data : null; }
function deckG(){ return (window.G && G.pptData && G.pptData.slides) ? G.pptData : null; }
/* the mark is kept with the slide's element edits (dsel.L), so it is saved, copied and moved with the slide */
function hasMark(sl){ return !!(sl && (sl.ds_hidden || (sl.dsel && sl.dsel.L && sl.dsel.L.dsx_hidden))); }
function setMark(sl, on){
  if (!sl) return;
  if (on) { sl.ds_hidden = true; if (!sl.dsel || typeof sl.dsel !== 'object') sl.dsel = {}; if (!sl.dsel.L || typeof sl.dsel.L !== 'object') sl.dsel.L = {}; sl.dsel.L.dsx_hidden = {h: 1}; }
  else { delete sl.ds_hidden; if (sl.dsel && sl.dsel.L) delete sl.dsel.L.dsx_hidden; }
}
function hidden(i){ var a = deckS(), b = deckG(); return hasMark(a && a.slides[i]) || hasMark(b && b.slides[i]); }
function hiddenList(){ var d = deckS() || deckG(), out = []; if (!d) return out; for (var i = 0; i < d.slides.length; i++) if (hidden(i)) out.push(i); return out; }
function shiftKeys(obj, from, by, copyFrom){
  if (!obj || typeof obj !== 'object') return obj;
  var out = {};
  Object.keys(obj).forEach(function(k){
    var m = k.match(/^slide_(\d+)$/); if (!m) { out[k] = obj[k]; return; }
    var n = parseInt(m[1], 10); out['slide_' + (n >= from ? n + by : n)] = obj[k];
  });
  if (copyFrom != null && obj['slide_' + copyFrom] !== undefined) out['slide_' + from] = obj['slide_' + copyFrom];
  return out;
}
function clone(o){ try { return JSON.parse(JSON.stringify(o)); } catch (e) { return o; } }
function changed(){ try { if (window.DS_EL_DIRTY) window.DS_EL_DIRTY(); } catch (e) {} try { document.dispatchEvent(new CustomEvent('ds-deck-changed')); } catch (e) {} }
function dupSlide(i){
  var a = deckS(), b = deckG(); if (!a && !b) return;
  if (a) a.slides.splice(i + 1, 0, clone(a.slides[i]));
  if (b && b !== a) b.slides.splice(i + 1, 0, clone(b.slides[i]));
  if (window.S && S.choices) { S.choices = shiftKeys(S.choices, i + 1, 1, i); try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch (e) {} }
  if (a && a.slide_variants) a.slide_variants = shiftKeys(a.slide_variants, i + 1, 1, i);
  if (window.S) S.idx = i + 1;
  if (window.G) G.activeSlideIdx = i + 1;
  if (window.dspRenderThumbs) dspRenderThumbs();
  if (window.dspSelectThumb) dspSelectThumb(i + 1);
  changed();
  if (window.dspToast) dspToast('Slide ' + (i + 1) + ' copied');
}
function delSlide(i){
  if (typeof window.vpDeleteSlide === 'function') { window.vpDeleteSlide(i); changed(); return; }
  if (typeof window.dspEditorDeleteSlide === 'function' && confirm('Delete slide ' + (i + 1) + '?')) { dspEditorDeleteSlide(i); changed(); }
}
function hideSlide(i){
  var a = deckS(), b = deckG(), on = !hidden(i);
  [a, b].forEach(function(d){ if (d && d.slides[i]) setMark(d.slides[i], on); });
  decorate(); changed();
  if (window.dspToast) dspToast(on ? 'Slide ' + (i + 1) + ' is hidden: it stays here but is left out of downloads' : 'Slide ' + (i + 1) + ' is shown again');
}
function decorate(){
  var th = document.getElementById('dsp-ppt-thumbs'); if (!th) return;
  var items = th.querySelectorAll('.dsp-thumb-item');
  [].forEach.call(items, function(it, i){
    var bar = it.querySelector('.dsx-tact');
    if (!bar) {
      bar = document.createElement('div'); bar.className = 'dsx-tact';
      bar.innerHTML = '<button type="button" data-dsx="copy" title="Copy slide">' + ICON.copy + '</button><button type="button" data-dsx="del" title="Delete slide">' + ICON.del + '</button><button type="button" data-dsx="hide"></button>';
      it.appendChild(bar);
    }
    var h = hidden(i), hb = bar.querySelector('[data-dsx="hide"]');
    hb.innerHTML = h ? ICON.eyeoff : ICON.eye; hb.title = h ? 'Show slide (put it back in downloads)' : 'Hide slide (leave it out of downloads)'; hb.classList.toggle('dsx-on', h);
    it.classList.toggle('dsx-hid', h);
    var tag = it.querySelector('.dsx-hidtag');
    if (h && !tag) { tag = document.createElement('span'); tag.className = 'dsx-hidtag'; tag.textContent = 'Hidden'; (it.querySelector('.dsp-thumb-box') || it).appendChild(tag); }
    if (!h && tag) tag.remove();
    var box = it.querySelector('.dsp-thumb-box'); if (box && getComputedStyle(box).position === 'static') box.style.position = 'relative';
  });
}
document.addEventListener('click', function(ev){
  var b = ev.target.closest && ev.target.closest('.dsx-tact [data-dsx]'); if (!b) return;
  ev.preventDefault(); ev.stopPropagation();
  var it = b.closest('.dsp-thumb-item'), th = document.getElementById('dsp-ppt-thumbs');
  var i = [].indexOf.call(th.querySelectorAll('.dsp-thumb-item'), it); if (i < 0) return;
  var k = b.getAttribute('data-dsx');
  if (k === 'copy') dupSlide(i); else if (k === 'del') delSlide(i); else hideSlide(i);
}, true);
['mousedown', 'pointerdown', 'dragstart'].forEach(function(t){ document.addEventListener(t, function(ev){ if (ev.target.closest && ev.target.closest('.dsx-tact')) { ev.stopPropagation(); if (t === 'dragstart') ev.preventDefault(); } }, true); });

/* hidden slides are left out of the PowerPoint download (the request sent to the server) */
var of = window.fetch;
window.fetch = function(u, o){
  try {
    var url = String(u && u.url || u);
    if (o && typeof o.body === 'string' && /\/ds\/v1\//.test(url) && !/save|load|draft|autosave|share/i.test(url) && /build|pptx|export|download/i.test(url)) {
      var hid = hiddenList();
      if (hid.length) {
        var j = JSON.parse(o.body), n = (deckS() || deckG()).slides.length;
        if (Array.isArray(j.slides) && j.slides.length === n && hid.length < n) {
          var keep = []; for (var i = 0; i < n; i++) if (hid.indexOf(i) < 0) keep.push(i);
          j.slides = keep.map(function(i){ return j.slides[i]; });
          ['tpl_pages', 'pages', 'images'].forEach(function(k){ if (Array.isArray(j[k]) && j[k].length === n) j[k] = keep.map(function(i){ return j[k][i]; }); });
          if (j.slide_variants && typeof j.slide_variants === 'object' && !Array.isArray(j.slide_variants)) {
            var sv = {}; keep.forEach(function(oi, ni){ if (j.slide_variants['slide_' + oi] !== undefined) sv['slide_' + ni] = j.slide_variants['slide_' + oi]; });
            Object.keys(j.slide_variants).forEach(function(k){ if (!/^slide_\d+$/.test(k)) sv[k] = j.slide_variants[k]; });
            j.slide_variants = sv;
          }
          o = Object.assign({}, o, {body: JSON.stringify(j)});
          arguments[1] = o;
        }
      }
    }
  } catch (e) {}
  return of.apply(this, arguments);
};
/* ... and out of the PDF / PNG / JPG download (those slides are drawn but not put in the file) */
var xp = {on: false, cur: -1, hid: [], made: false, pending: false, n: 0};
function skipNow(){ return xp.on && xp.hid.indexOf(xp.cur) >= 0; }
function patchLibs(){
  try {
    var Z = window.JSZip;
    if (Z && Z.prototype && !Z.prototype.__dsx) {
      var zf = Z.prototype.file;
      Z.prototype.file = function(name, data){
        if (xp.on && arguments.length > 1 && /^slide-\d+\./.test(String(name))) {
          if (skipNow()) return this;
          xp.n++; name = String(name).replace(/^slide-\d+/, 'slide-' + String(xp.n).padStart(2, '0'));
          var a = [].slice.call(arguments); a[0] = name; return zf.apply(this, a);
        }
        return zf.apply(this, arguments);
      };
      Z.prototype.__dsx = 1;
    }
    var NS = window.jspdf, J = NS && NS.jsPDF;
    if (J && !J.__dsx) {
      var W = function(){
        var doc = (J.apply ? new (Function.prototype.bind.apply(J, [null].concat([].slice.call(arguments))))() : new J());
        var ai = doc.addImage, ap = doc.addPage;
        doc.__dsxBlank = false;
        doc.addImage = function(){ if (skipNow()) { if (!xp.made) doc.__dsxBlank = true; return doc; } xp.made = true; return ai.apply(doc, arguments); };
        doc.addPage = function(){ if (skipNow()) return doc; if (doc.__dsxBlank) { doc.__dsxBlank = false; return doc; } return ap.apply(doc, arguments); };
        return doc;
      };
      for (var k in J) { try { W[k] = J[k]; } catch (e) {} }
      W.API = J.API; W.prototype = J.prototype; W.__dsx = 1; J.__dsx = 1;
      try { NS.jsPDF = W; } catch (e) {}
    }
  } catch (e) {}
}
function wrapExport(){
  if (!window.DSX || typeof DSX.exportAs !== 'function' || DSX.exportAs.__dsx) return;
  var ex = DSX.exportAs;
  DSX.exportAs = async function(kind){
    var hid = hiddenList(), d = deckS() || deckG();
    if (!hid.length || !d) return ex.apply(this, arguments);
    if (hid.length >= d.slides.length) { if (window.dspToast) dspToast('Every slide is hidden. Show at least one slide to download.', 'error'); return; }
    xp = {on: true, cur: -1, hid: hid, made: false, n: 0};
    var sel = window.dspSelectThumb;
    window.dspSelectThumb = function(i){ xp.cur = i; patchLibs(); return sel.apply(this, arguments); };
    try { return await ex.apply(this, arguments); }
    finally { window.dspSelectThumb = sel; xp.on = false; }
  };
  DSX.exportAs.__dsx = 1;
}

/* ================= 2. Change image on covered pictures ================= */
var chip = null, chipIdx = -1;
function mkChip(){
  if (chip) return chip;
  chip = document.createElement('button'); chip.type = 'button'; chip.id = 'dsx-chimg';
  chip.innerHTML = ICON.pic + 'Change image';
  chip.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); if (chipIdx >= 0 && typeof window.vpImgOptionOpen === 'function') window.vpImgOptionOpen(chipIdx); chip.style.display = 'none'; });
  ['mousedown', 'pointerdown'].forEach(function(t){ chip.addEventListener(t, function(ev){ ev.stopPropagation(); }); });
  document.body.appendChild(chip); return chip;
}
function ownBtnShown(){ var b = document.querySelector('.dsel-imgbtn'); return !!(b && b.style.display !== 'none' && getComputedStyle(b).display !== 'none'); }
function hideChip(){ if (chip) chip.style.display = 'none'; }
document.addEventListener('mousemove', function(ev){
  var x = ev.clientX, y = ev.clientY, tgt = ev.target, btns = ev.buttons;
  setTimeout(function(){                               /* after the editor's own hover code has run */
    var m = document.getElementById('dsp-ppt-main-slide');
    if (!m || btns || !m.offsetParent) { hideChip(); return; }
    if (tgt === chip) return;
    if (document.getElementById('dsp-cre-fullslide') || ownBtnShown()) { hideChip(); return; }
    var hit = null, zs = m.querySelectorAll('.dsp-vp-imgzone');
    for (var i = 0; i < zs.length; i++) {
      var r = zs[i].getBoundingClientRect();
      if (r.width > 20 && x >= r.left && x <= r.right && y >= r.top && y <= r.bottom) { hit = {z: zs[i], r: r}; break; }
    }
    if (!hit) { hideChip(); return; }
    var c = mkChip(), si = parseInt(hit.z.getAttribute('data-slide-idx'), 10);
    chipIdx = isNaN(si) ? ((window.S && S.idx >= 0) ? S.idx : 0) : si;
    c.style.display = 'inline-flex';
    c.style.left = Math.round(hit.r.right - c.offsetWidth - 10) + 'px';
    var top = Math.max(hit.r.top + 10, 4), ctl = m.querySelector('.dsds-imctl');
    if (ctl && ctl.offsetParent) { var cr = ctl.getBoundingClientRect(); if (cr.bottom > top && cr.top < top + 30) top = cr.bottom + 8; }   /* below the Image size bar */
    c.style.top = Math.round(top) + 'px';
  }, 0);
}, {passive: true});

/* ================= wiring ================= */
function tick(){
  ['dsel-panel', 'dsel-thpop', 'dsel-icpop'].forEach(function(id){ var p = document.getElementById(id); if (p) watchPopup(p); });
  var bar = document.getElementById('dsel-bar'); if (bar && !bar.__dsx) { bar.__dsx = 1; barObs.observe(bar, {childList: true, subtree: true, attributes: true, attributeFilter: ['style']}); }
  var th = document.getElementById('dsp-ppt-thumbs');
  if (th && !th.__dsx) { th.__dsx = 1; new MutationObserver(function(){ clearTimeout(th.__t); th.__t = setTimeout(decorate, 30); }).observe(th, {childList: true}); }
  decorate(); wrapExport(); patchLibs();
  if (pendingPanel) reopenPanel();
}
setInterval(tick, 1000); tick();
})();
