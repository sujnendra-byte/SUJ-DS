
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
