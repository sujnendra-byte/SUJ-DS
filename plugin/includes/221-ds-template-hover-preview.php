<?php
/**
 * Code Snippets export — DS Template Hover Preview
 *
 * Snippet ID : 1005
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 13:33:09
 * Load order : 212 of 218
 */
/**
 * DS Template Hover Preview
 * Run: Everywhere
 *
 * AI Presentation - design template picker: moving the mouse over a template card shows its
 * 3-slide sample preview (cover, stats, timeline) in a small panel just below that card,
 * so a template does not have to be selected to see its sample. The panel never takes clicks.
 * Touch screens (no hover) are not changed. Needs the "DS Template Engine (T00)" snippet.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-hover-preview">
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
</script>
<?php
}, 1100);
