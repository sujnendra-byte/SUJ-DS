
(function(){
  var st = null;                       /* {idx, n} while the "Add slide" screen is showing */
  function $(id){ return document.getElementById(id); }
  /* change a class ONLY when it really changes: the editor re-renders its layout panel on every
     attribute change of the slide list, even a no-op one */
  function cls(el, name, on){ if (el && el.classList.contains(name) !== !!on) el.classList.toggle(name, !!on); }
  function items(th){ return th.querySelectorAll('.dsp-thumb-item'); }
  function nSlides(){ var S = window.S; return S && S.data && S.data.slides ? S.data.slides.length : -1; }
  function curIdx(){ var S = window.S; if (S && typeof S.idx === 'number') return S.idx; return window.G && typeof G.activeSlideIdx === 'number' ? G.activeSlideIdx : -1; }
  function onTile(th, e){
    if (e.target !== th) return false;
    var it = items(th), last = it[it.length - 1]; if (!last) return false;
    return e.clientY > last.getBoundingClientRect().bottom + 10;
  }
  function selectLast(th){ var it = items(th), last = it[it.length - 1]; if (last) (last.querySelector('.dsp-thumb-box') || last).click(); }
  function openPopup(){
    var th = $('dsp-ppt-thumbs'); if (!th) return;
    if (curIdx() !== items(th).length - 1) selectLast(th);
    setTimeout(function(){ if (typeof window.dspAddSlide === 'function') window.dspAddSlide(); }, 120);
  }
  function hidePH(){ st = null; var p = $('dsat-ph'); if (p) p.remove(); var th = $('dsp-ppt-thumbs'); cls(th, 'dsat-sel', false); }
  /* the white screen sits NEXT TO the main slide (not inside it): the editor re-renders the
     slide whenever something is added inside it, which would loop forever */
  function drawPH(){
    var main = $('dsp-ppt-main-slide'); if (!main || !st) return;
    var host = main.parentNode; if (!host) return;
    var p = $('dsat-ph');
    if (!p || p.parentNode !== host) {
      if (p) p.remove();
      if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
      p = document.createElement('div'); p.id = 'dsat-ph';
      p.innerHTML = '<button type="button" class="dsat-ph-btn" title="Add a new slide">+</button><div class="dsat-ph-t">Add slide</div>';
      p.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); if (e.target.closest('.dsat-ph-btn,.dsat-ph-t')) openPopup(); });
      host.appendChild(p);
    }
    var hr = host.getBoundingClientRect(), mr = main.getBoundingClientRect();
    var css = 'left:' + (mr.left - hr.left + host.scrollLeft).toFixed(1) + 'px;top:' + (mr.top - hr.top + host.scrollTop).toFixed(1) + 'px;width:' + mr.width.toFixed(1) + 'px;height:' + mr.height.toFixed(1) + 'px';
    if (p.getAttribute('data-z') !== css) { p.setAttribute('data-z', css); p.style.cssText = css; }
  }
  function showPH(){
    var th = $('dsp-ppt-thumbs'); if (!th) return;
    var target = items(th).length - 1, tries = 0;
    cls(th, 'dsat-sel', true);
    setTimeout(function(){ if (curIdx() !== target) selectLast(th); }, 0);
    (function wait(){                                     /* show once the last slide is really selected */
      if (curIdx() === target) { st = {idx:target, n:nSlides()}; cls(th, 'dsat-sel', true); drawPH(); return; }
      if (++tries === 20) selectLast(th);
      if (tries < 60) setTimeout(wait, 50); else cls(th, 'dsat-sel', false);
    })();
  }
  function bind(th){
    if (th.__dsat) return; th.__dsat = 1;
    th.addEventListener('mousedown', function(e){ if (e.button === 0 && onTile(th, e)) { e.preventDefault(); e.stopPropagation(); showPH(); } }, true);
    th.addEventListener('click', function(e){
      if (onTile(th, e)) { e.preventDefault(); e.stopPropagation(); return; }
      if (st && e.target !== th) hidePH();                 /* a real slide was picked */
    }, true);
    th.addEventListener('mousemove', function(e){ cls(th, 'dsat-hov', onTile(th, e)); });
    th.addEventListener('mouseleave', function(){ cls(th, 'dsat-hov', false); });
  }
  function ensure(){
    var th = $('dsp-ppt-thumbs'); if (!th) { if (st) hidePH(); return; }
    var it = items(th);
    if (!it.length) { cls(th, 'dsat-on', false); return; }
    bind(th); cls(th, 'dsat-on', true);
    var box = it[0].querySelector('.dsp-thumb-box');
    if (box && box.offsetWidth) {
      var w = box.offsetWidth + 'px', l = (box.getBoundingClientRect().left - it[0].getBoundingClientRect().left) + 'px';
      if (th.style.getPropertyValue('--dsat-w') !== w) th.style.setProperty('--dsat-w', w);
      if (th.style.getPropertyValue('--dsat-l') !== l) th.style.setProperty('--dsat-l', l);
    }
    if (st) {
      if (nSlides() !== st.n || curIdx() !== st.idx) hidePH();   /* slide added, or user moved to another slide */
      else { cls(th, 'dsat-sel', true); drawPH(); }
    }
  }
  setInterval(ensure, 300);
  ensure();
})();
