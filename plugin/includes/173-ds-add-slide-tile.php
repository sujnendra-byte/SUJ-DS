<?php
/**
 * Code Snippets export — DS Add-Slide Tile
 *
 * Snippet ID : 910
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-09-30 08:54:57
 * Load order : 173 of 168
 */
/**
 * DS Add-Slide Tile — a "+" tile after the last slide in the editor's slide list.
 * Clicking it shows a white "Add slide" screen on the main canvas; clicking the
 * "+" there opens the normal Add Slide popup and the new slide goes at the end.
 * The tile is drawn with CSS (::after of the slide list), so it is not a real
 * list item and never changes slide counts used by other snippets.
 * Stand-alone: does not change any other snippet.
 */
if ( ! function_exists('dsat_should_print') ) {
function dsat_should_print() {
    if ( ! is_user_logged_in() ) return false;
    if ( function_exists('ds_tpl_should_print') ) return ds_tpl_should_print();
    global $post;
    return is_a($post, 'WP_Post') && ( has_shortcode($post->post_content, 'ds_ai_ppt') || has_shortcode($post->post_content, 'ds_ai_studio') );
}
}
add_action('wp_footer', function () {
    if ( ! dsat_should_print() ) return;
    ?>
<style id="dsat-css">
#dsp-ppt-thumbs.dsat-on::after{content:'+';display:flex;flex:0 0 auto;align-items:center;justify-content:center;box-sizing:border-box;width:var(--dsat-w,100%);aspect-ratio:16/9;margin:18px 0 10px var(--dsat-l,0px);background:#fff;border:2px solid transparent;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.25);font:300 36px/1 -apple-system,Segoe UI,Arial,sans-serif;color:#5f6368;cursor:pointer;transition:border-color .15s,box-shadow .15s,color .15s;}
#dsp-ppt-thumbs.dsat-on.dsat-hov::after,#dsp-ppt-thumbs.dsat-on.dsat-sel::after{border-color:#1a73e8;color:#1a73e8;box-shadow:0 4px 16px rgba(26,115,232,.35);}
#dsp-ppt-thumbs.dsat-on.dsat-hov{cursor:pointer;}
#dsat-ph{position:absolute;z-index:60;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;cursor:default;box-shadow:0 8px 30px rgba(0,0,0,.35);}
#dsat-ph .dsat-ph-btn{width:17%;max-width:150px;min-width:70px;aspect-ratio:1/1;border:1px solid #dadce0;border-radius:6px;background:#fff;display:flex;align-items:center;justify-content:center;font:300 64px/1 -apple-system,Segoe UI,Arial,sans-serif;color:#5f6368;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.08);transition:border-color .15s,box-shadow .15s,color .15s;padding:0;}
#dsat-ph .dsat-ph-btn:hover{border-color:#1a73e8;color:#1a73e8;box-shadow:0 4px 14px rgba(26,115,232,.25);}
#dsat-ph .dsat-ph-t{font:700 26px/1.2 -apple-system,Segoe UI,Arial,sans-serif;color:#111;cursor:pointer;}
</style>
<script id="dsat-js">
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
</script>
<?php
}, 1030);
