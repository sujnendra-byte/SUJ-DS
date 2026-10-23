
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
/* The editor has ONE settings popup (#dsel-panel) that every button reuses. Locking a popup turns it into its own
   window (a "pinned" copy) that stays open while the editor popup is used for other things. The pinned copy is kept
   up to date and passes every click / slider / box back to the editor, so it works like the real one. */
function isOpen(p){ return !!p && p.style.display !== 'none' && getComputedStyle(p).display !== 'none'; }
function real(){ return document.getElementById('dsel-panel'); }
function titleOf(p){ var b = p && p.querySelector('.dsel-ph b, .dsel-tp-h b, .dsel-ic-h b'); return b ? b.textContent.trim() : ''; }
function tabOf(p){ var t = p && p.querySelector('.dsel-tabs .dsel-on'); return t ? t.textContent.trim() : ''; }
function kindOf(p){ var t = titleOf(p); return t ? t + (tabOf(p) ? '|' + tabOf(p) : '') : ''; }
function nice(k){ return String(k || '').replace('|', ' · '); }
var opener = LS.get('opener2', {}) || {};          /* kind -> {box, a, tab} */
var lastOpen = null;

/* ---- theme / icons popups (already separate windows): lock = they do not close by themselves ---- */
function popLocked(p){ return !!LS.get('lock:' + p.id, false); }
function popLockBtn(p){
  var h = p.querySelector('.dsel-tp-h, .dsel-ic-h'); if (!h) return;
  var b = h.querySelector('.dsx-lock');
  if (!b) { b = document.createElement('button'); b.type = 'button'; b.className = 'dsx-lock'; var x = h.querySelector('.dsel-tp-x, .dsel-ic-x'); if (x) h.insertBefore(b, x); else h.appendChild(b); }
  var on = popLocked(p); b.classList.toggle('on', on); b.innerHTML = on ? ICON.lock : ICON.open;
  b.title = on ? 'Locked: stays open until you press x. Click to unlock.' : 'Lock this window so it stays open';
}
var closing = {};
function watchPop(p){
  if (!p || p.__dsx) return; p.__dsx = 1; p.classList.add('dsx-rs');
  var was = isOpen(p);
  new MutationObserver(function(){
    popLockBtn(p);
    var open = isOpen(p);
    if (was && !open && popLocked(p) && Date.now() - (closing[p.id] || 0) > 600) { p.style.display = 'flex'; open = true; }
    was = open;
  }).observe(p, {attributes: true, attributeFilter: ['style'], childList: true});
  popLockBtn(p);
}

/* ---- the editor popup: lock button on it ---- */
function realLockBtn(){
  var p = real(); if (!p) return;
  var h = p.querySelector('.dsel-ph'); if (!h || h.querySelector('.dsx-lock')) return;
  var b = document.createElement('button'); b.type = 'button'; b.className = 'dsx-lock'; b.innerHTML = ICON.open;
  b.title = 'Lock: keep this window open on its own while you use the other buttons';
  var x = h.querySelector('[data-p="close"]'); if (x) h.insertBefore(b, x); else h.appendChild(b);
}

/* ---- run something with the editor popup showing a given kind, then put the editor popup back as it was ----
   everything happens in one go, so nothing flickers on screen */
var busy = 0;
function saveReal(){ var p = real(); return {open: isOpen(p), kind: kindOf(p), css: p ? p.getAttribute('style') : ''}; }
function openKind(k){
  var p = real(); if (!p) return false;
  if (isOpen(p) && kindOf(p) === k) return true;
  var o = opener[k]; if (!o) return false;
  if (!(isOpen(p) && titleOf(p) === k.split('|')[0])) {
    var b = document.querySelector((o.box === 'dock' ? '#dsel-dock' : '#dsel-bar') + ' [data-a="' + o.a + '"]');
    if (!b) return false;
    b.click();
  }
  if (o.tab && kindOf(p) !== k) { var t = p.querySelector('[data-p="' + o.tab + '"]'); if (t) t.click(); }
  return isOpen(p) && kindOf(p) === k;
}
function restoreReal(st){
  var p = real(); if (!p) return;
  if (st.open && st.kind && !opener[st.kind] && lastOpen) opener[st.kind] = lastOpen;   /* a popup the user just opened */
  if (st.open) { if (kindOf(p) !== st.kind || !isOpen(p)) openKind(st.kind); if (st.css) p.setAttribute('style', st.css); }
  else if (isOpen(p)) { var x = p.querySelector('[data-p="close"]'); if (x) { closing.real = Date.now(); x.click(); } else p.style.display = 'none'; }
}
function withKind(k, fn){
  if (busy) return null;
  busy = 1; var st = saveReal(), out = null;
  try { if (openKind(k)) out = fn(real()); } catch (e) {}
  try { restoreReal(st); } catch (e) {}
  /* the changes made just now are ours, not the user's: do not let them start another refresh (that was the flashing loop) */
  try { barObs.takeRecords(); if (nlObs) nlObs.takeRecords(); } catch (e) {}
  quiet = Date.now() + 600;
  busy = 0;
  return out;
}

/* ---- pinned windows ---- */
var pins = {};                      /* kind -> {el, body} */
function savePins(){ var a = []; Object.keys(pins).forEach(function(k){ var r = pins[k].el.getBoundingClientRect(); a.push({k: k, x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height)}); }); LS.set('pins', a); LS.set('opener2', opener); }
function bodyHTML(p){
  var c = p.cloneNode(true);
  [].forEach.call(c.querySelectorAll('.dsel-ph, .dsx-lock'), function(n){ n.remove(); });
  [].forEach.call(c.querySelectorAll('.dsel-icons'), function(n){ if (n.querySelector('[data-p="newicon"]')) { var sb = n.previousElementSibling; if (sb && sb.classList.contains('dsel-sub')) sb.remove(); n.remove(); } });
  [].forEach.call(c.querySelectorAll('[draggable]'), function(n){ n.setAttribute('draggable', 'false'); });
  [].forEach.call(c.querySelectorAll('[id]'), function(n){ n.setAttribute('data-dsx-id', n.id); n.removeAttribute('id'); });
  return c.innerHTML;
}
function fill(pin){
  var html = withKind(pin.kind, function(p){ return bodyHTML(p); });
  if (html == null) {
    if (!pin.body.innerHTML) pin.body.innerHTML = '<p class="dsx-pin-wait">Select an element on the slide to use this window.</p>';
    pin.body.classList.add('dsx-pin-idle');
    return;
  }
  if (html === pin.last && !pin.body.classList.contains('dsx-pin-idle')) return;   /* nothing changed: leave the window alone */
  pin.last = html;
  var st = pin.body.scrollTop; pin.body.innerHTML = html; pin.body.scrollTop = st; pin.body.classList.remove('dsx-pin-idle');
}
function makePin(k, box){
  if (pins[k]) return pins[k];
  var el = document.createElement('div'); el.className = 'dsx-pin dsx-rs';
  el.innerHTML = '<div class="dsel-ph dsx-pin-h"><b></b><button type="button" class="dsx-lock on" title="Unlock: close this window">' + ICON.lock + '</button><button type="button" data-dsxp="x" title="Close">&times;</button></div><div class="dsx-pin-b"></div>';
  el.querySelector('b').textContent = nice(k);
  document.body.appendChild(el);
  var pin = {kind: k, el: el, body: el.querySelector('.dsx-pin-b')};
  pins[k] = pin;
  var w = box && box.w ? box.w : 260, h = box && box.h ? box.h : 0;
  el.style.width = Math.min(w, innerWidth - 16) + 'px'; if (h) el.style.height = Math.min(h, innerHeight - 16) + 'px';
  el.style.left = Math.max(8, Math.min(box ? box.x : 40, innerWidth - 120)) + 'px';
  el.style.top = Math.max(8, Math.min(box ? box.y : 80, innerHeight - 80)) + 'px';
  /* move by the title bar */
  el.querySelector('.dsx-pin-h').addEventListener('pointerdown', function(ev){
    if (ev.target.closest('button') || ev.button !== 0) return;
    var r = el.getBoundingClientRect(), dx = ev.clientX - r.left, dy = ev.clientY - r.top; ev.preventDefault();
    function mv(e){ el.style.left = Math.max(0, Math.min(e.clientX - dx, innerWidth - 60)) + 'px'; el.style.top = Math.max(0, Math.min(e.clientY - dy, innerHeight - 40)) + 'px'; }
    function up(){ document.removeEventListener('pointermove', mv, true); document.removeEventListener('pointerup', up, true); savePins(); }
    document.addEventListener('pointermove', mv, true); document.addEventListener('pointerup', up, true);
  });
  /* header buttons */
  el.querySelector('.dsx-pin-h').addEventListener('click', function(ev){
    var b = ev.target.closest('button'); if (!b) return;
    ev.preventDefault(); ev.stopPropagation(); unpin(k);
  });
  /* clicks in the window go to the editor */
  pin.body.addEventListener('click', function(ev){
    var b = ev.target.closest('[data-p]'); if (!b) return;
    ev.preventDefault(); ev.stopPropagation();
    var dp = b.getAttribute('data-p');
    if (dp === 'close') { unpin(k); return; }
    if (/^tab-/.test(dp)) {                                     /* switching tabs: the window shows the other tab */
      var title = k.split('|')[0], base = opener[k] || {};
      var nk = withKind(k, function(p){ var t = p.querySelector('[data-p="' + dp + '"]'); if (t) t.click(); return kindOf(p); });
      if (nk && nk !== k && !pins[nk]) {
        opener[nk] = {box: base.box, a: base.a, tab: dp};
        delete pins[k]; pin.kind = nk; pins[nk] = pin; el.querySelector('b').textContent = nice(nk); k = nk; savePins();
      }
      fill(pin); return;
    }
    var attrs = [].filter.call(b.attributes, function(a){ return /^data-/.test(a.name) && a.name !== 'data-dsx-id'; });
    withKind(k, function(p){
      var sel = attrs.map(function(a){ return '[' + a.name + '="' + String(a.value).replace(/"/g, '\\"') + '"]'; }).join('');
      var twin = null; try { twin = p.querySelector(sel); } catch (e) {}
      var tmp = null;
      if (!twin) { tmp = document.createElement('button'); tmp.type = 'button'; tmp.style.display = 'none'; attrs.forEach(function(a){ tmp.setAttribute(a.name, a.value); }); p.appendChild(tmp); twin = tmp; }
      twin.dispatchEvent(new MouseEvent('click', {bubbles: true, cancelable: true, shiftKey: ev.shiftKey, ctrlKey: ev.ctrlKey, metaKey: ev.metaKey, altKey: ev.altKey}));
      if (tmp && tmp.parentNode) tmp.remove();
    });
    setTimeout(function(){ fill(pin); }, 30);
  }, true);
  /* sliders, colour boxes, number boxes, checkboxes */
  function relay(ev){
    var t = ev.target; if (!t || !/^(INPUT|SELECT|TEXTAREA)$/.test(t.tagName)) return;
    var list = [].slice.call(pin.body.querySelectorAll('input,select,textarea')), i = list.indexOf(t); if (i < 0) return;
    withKind(k, function(p){
      var tw = p.querySelectorAll('input,select,textarea')[i]; if (!tw) return;
      if (t.type === 'checkbox' || t.type === 'radio') tw.checked = t.checked; else tw.value = t.value;
      tw.dispatchEvent(new Event(ev.type, {bubbles: true}));
      if (ev.type === 'keydown') tw.dispatchEvent(new KeyboardEvent('keydown', {key: ev.key, bubbles: true}));
    });
    if (ev.type === 'change') setTimeout(function(){ if (document.activeElement && pin.body.contains(document.activeElement) && /^(INPUT|TEXTAREA)$/.test(document.activeElement.tagName) && document.activeElement.type !== 'range') return; fill(pin); }, 30);
  }
  ['input', 'change'].forEach(function(t){ pin.body.addEventListener(t, relay, true); });
  pin.body.addEventListener('keydown', function(ev){ if (ev.key === 'Enter' && /^(INPUT)$/.test(ev.target.tagName)) { relay({type: 'change', target: ev.target}); } ev.stopPropagation(); }, true);
  ['mousedown', 'pointerdown', 'keyup'].forEach(function(t){ el.addEventListener(t, function(ev){ ev.stopPropagation(); }); });
  if (window.ResizeObserver) { var to; new ResizeObserver(function(){ clearTimeout(to); to = setTimeout(savePins, 300); }).observe(el); }
  fill(pin); savePins();
  return pin;
}
function unpin(k){ var p = pins[k]; if (!p) return; p.el.remove(); delete pins[k]; savePins(); }
function refreshPins(){ Object.keys(pins).forEach(function(k){ fill(pins[k]); }); }

document.addEventListener('click', function(ev){
  var t = ev.target; if (!t || !t.closest) return;
  var lb = t.closest('.dsx-lock');
  if (lb && !lb.closest('.dsx-pin')) {
    ev.preventDefault(); ev.stopPropagation();
    var pp = lb.closest('#dsel-thpop, #dsel-icpop');
    if (pp) { LS.set('lock:' + pp.id, !popLocked(pp)); popLockBtn(pp); return; }
    var p = real(); if (!p || !isOpen(p)) return;
    var k = kindOf(p); if (!k) return;
    if (lastOpen && !opener[k]) opener[k] = lastOpen;
    if (!opener[k]) return;
    var r = p.getBoundingClientRect();
    makePin(k, {x: r.left, y: r.top, w: r.width, h: Math.min(r.height, innerHeight - r.top - 10)});
    var x = p.querySelector('[data-p="close"]'); if (x) { closing.real = Date.now(); x.click(); }
    if (window.dspToast) dspToast('Locked: "' + nice(k) + '" stays open. Drag its title bar to move it.');
    return;
  }
  if (t.closest('#dsel-thpop .dsel-tp-x, .dsel-side-theme, #dsp-ed-theme-trigger')) closing['dsel-thpop'] = Date.now();
  if (t.closest('#dsel-icpop .dsel-ic-x, .dsel-iclib-b')) closing['dsel-icpop'] = Date.now();
  if (busy) return;
  var ob = t.closest('#dsel-dock [data-a], #dsel-bar [data-a]');
  if (ob) {
    lastOpen = {box: ob.closest('#dsel-dock') ? 'dock' : 'bar', a: ob.getAttribute('data-a')};
    var lo = lastOpen, rec = function(){ var p = real(); if (p && isOpen(p)) { var k = kindOf(p); if (k) { opener[k] = lo; LS.set('opener2', opener); } } };
    setTimeout(rec, 0); setTimeout(rec, 120);
  }
  var tb = t.closest('#dsel-panel [data-p^="tab-"]');
  if (tb) { var base = lastOpen; setTimeout(function(){ var p = real(); var k = kindOf(p); if (k && base) { opener[k] = {box: base.box, a: base.a, tab: tb.getAttribute('data-p')}; LS.set('opener2', opener); } }, 120); }
}, true);

/* keep pinned windows current when the selection or slide changes */
var rto = null, quiet = 0, nlObs = null;
function soon(){ if (busy || Date.now() < quiet) return; clearTimeout(rto); rto = setTimeout(refreshPins, 180); }
var barObs = new MutationObserver(soon);
function restorePins(){
  if (restorePins.done) return;
  if (!document.getElementById('dsel-dock') || !document.getElementById('dsel-panel')) return;
  restorePins.done = 1;
  (LS.get('pins', []) || []).forEach(function(b){ if (opener[b.k]) makePin(b.k, b); });
}

/* the pinned windows look like the editor popup: copy its styles */
function pinCss(){
  if (document.getElementById('dsx-pin-css2')) return true;
  var src = document.getElementById('ds-el-css'); if (!src || !src.sheet) return false;
  var out = [];
  function walk(rules, wrap){
    [].forEach.call(rules, function(r){
      if (r.cssRules && r.conditionText !== undefined) { walk(r.cssRules, '@media ' + r.conditionText); return; }
      var sel = r.selectorText; if (!sel || sel.indexOf('#dsel-panel') < 0) return;
      var parts = sel.split(',').map(function(x){ return x.trim(); }).filter(function(x){ return x.indexOf('#dsel-panel') >= 0 && x !== '#dsel-panel'; });
      if (!parts.length) return;
      var txt = parts.map(function(x){ return x.replace(/#dsel-panel/g, '.dsx-pin'); }).join(',') + '{' + r.style.cssText + '}';
      out.push(wrap ? wrap + '{' + txt + '}' : txt);
    });
  }
  try { walk(src.sheet.cssRules, ''); } catch (e) { return false; }
  var st = document.createElement('style'); st.id = 'dsx-pin-css2'; st.textContent = out.join('\n'); document.head.appendChild(st);
  return true;
}
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
  ['dsel-thpop', 'dsel-icpop'].forEach(function(id){ var p = document.getElementById(id); if (p) watchPop(p); });
  var rp = real(); if (rp) { rp.classList.add('dsx-rs'); realLockBtn(); if (!rp.__dsx) { rp.__dsx = 1; new MutationObserver(realLockBtn).observe(rp, {childList: true}); } }
  var bar = document.getElementById('dsel-bar'); if (bar && !bar.__dsx) { bar.__dsx = 1; barObs.observe(bar, {childList: true, subtree: true}); }
  var nl = document.getElementById('dsp-ppt-nav-lbl'); if (nl && !nl.__dsx) { nl.__dsx = 1; nlObs = new MutationObserver(soon); nlObs.observe(nl, {childList: true, characterData: true, subtree: true}); }
  var th = document.getElementById('dsp-ppt-thumbs');
  if (th && !th.__dsx) { th.__dsx = 1; new MutationObserver(function(){ clearTimeout(th.__t); th.__t = setTimeout(decorate, 30); }).observe(th, {childList: true}); }
  decorate(); wrapExport(); patchLibs(); pinCss(); restorePins();
  /* pinned windows only while the editor is open */
  var ed = !!document.getElementById('dsel-dock') && isOpen(document.getElementById('dsel-dock'));
  Object.keys(pins).forEach(function(k){ pins[k].el.style.display = ed ? 'flex' : 'none'; });
}
setInterval(tick, 1000); tick();
})();
