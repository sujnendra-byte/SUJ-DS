<?php
/**
 * Code Snippets export — DS Template speed - no freeze after a theme / template pick (home + editor)
 *
 * Snippet ID : 1048
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-08 10:21:52
 * Load order : 242 of 257
 */
add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-font-refresh">
/* Picking a theme froze the page for seconds: every one of the 64 template cards asked for its fonts again, and
   each of those 64 requests redrew the whole template grid (or every slide thumbnail of an open deck) when it
   finished. Fonts are now requested once per font set, and all of them together cause at most ONE redraw -
   and none at all when the fonts were already loaded. */
(function(){
var D = window.DSTE; if (!D || !D.loadFonts || D.__fontRefresh) return; D.__fontRefresh = 1;
var req = {}, timer = null;
function redraw(){
  clearTimeout(timer);
  timer = setTimeout(function(){ try { if (D.clearFit) D.clearFit(); if (D.refreshEditor) D.refreshEditor(); } catch (e) {} }, 200);
}
function hasLink(href){
  var ls = document.getElementsByTagName('link');
  for (var i = 0; i < ls.length; i++) if (ls[i].href === href) return true;
  return false;
}
D.loadFonts = function(T){
  if (!T || !T.fonts || !T.fonts.gf) return;
  var key = T.fonts.head + '|' + T.fonts.body + '|' + T.fonts.gf;
  if (req[key]) return; req[key] = 1;
  T.fonts.gf.split('|').forEach(function(f){               /* one request per family, so one bad family never blocks the rest */
    if (!f || req['gf:' + f]) return; req['gf:' + f] = 1;
    var href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g, '+') + '&display=swap';
    if (hasLink(href)) return;
    var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = href;
    document.head.appendChild(l);
  });
  if (!document.fonts || !document.fonts.load) return;
  var specs = [];
  [400, 500, 600, 700, 800].forEach(function(w){ specs.push(w + ' 40px "' + T.fonts.head + '"'); specs.push(w + ' 16px "' + T.fonts.body + '"'); });
  specs.push('italic 400 16px "' + T.fonts.head + '"');
  var ready = true;
  try { for (var i = 0; i < specs.length; i++) if (!document.fonts.check(specs[i])) { ready = false; break; } } catch (e) { ready = false; }
  var jobs = specs.map(function(s){ return document.fonts.load(s); });
  if (ready) return;                                          /* already drawn with the right fonts: nothing to redraw */
  Promise.all(jobs).then(redraw).catch(function(){});
};
})();
/* Template grid: draw a card's preview only when the card is on screen. With "View All" open the grid has 65 cards
   and every theme pick redrew all of them at once, which blocked the page. Off-screen cards now cost nothing. */
(function(){
var D = window.DSTE; if (!D || !D.renderHTML || D.__lazyGrid || !window.IntersectionObserver) return; D.__lazyGrid = 1;
var real = D.renderHTML, jobs = {}, seq = 0, scanT = null;
var io = new IntersectionObserver(function(list){
  list.forEach(function(en){
    if (!en.isIntersecting) return;
    var el = en.target, k = el.getAttribute('data-dste-lazy'), a = jobs[k];
    io.unobserve(el); delete jobs[k];
    if (!a || !el.parentNode) return;
    try { el.outerHTML = real.apply(D, a); } catch (e) {}
  });
}, {rootMargin: '150px'});
function scan(){
  var live = {}, els = document.querySelectorAll('[data-dste-lazy]');
  for (var i = 0; i < els.length; i++) { var k = els[i].getAttribute('data-dste-lazy'); live[k] = 1; if (!els[i].__obs) { els[i].__obs = 1; io.observe(els[i]); } }
  for (var j in jobs) if (!live[j]) delete jobs[j];           /* cards of an older grid that is gone */
}
D.renderHTML = function(T, slide, vid, idx, deck, opts){
  var S = D.SAMPLE;
  if (T && S && slide === S.cover && idx === -1 && opts && opts.edit === false && deck && deck.slides && deck.slides.length === 1) {
    var k = 'g' + (++seq); jobs[k] = arguments;
    clearTimeout(scanT); scanT = setTimeout(scan, 0);
    var bg = (T.pal && T.pal.bg) || '#111';
    return '<div data-dste-lazy="' + k + '" style="position:absolute;inset:0;background:' + String(bg).replace(/[^#\w(),.% -]/g, '') + ';"></div>';
  }
  return real.apply(this, arguments);
};
})();
/* Editor: one action (a theme pick, a layout pick) made the editor work out the same slide several times in a row -
   thumbnail, preload, canvas - and every pass over a glass slide re-blurs its background. Identical passes that
   follow each other within the same action now reuse the first result. The memory is emptied on every click, key
   press, edit and redraw request, and after 0.8 s, so nothing old can be shown. */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || D.__memoRP) return; D.__memoRP = 1;
var real = D.renderPage, memo = {}, size = 0, born = 0;
function clear(){ if (size) { memo = {}; size = 0; } }
function rep(k, v){ return (typeof v === 'string' && v.length > 160) ? v.length + ':' + v.slice(0, 40) + v.slice(-40) : v; }
function sig(o){ try { return JSON.stringify(o, rep); } catch (e) { return null; } }
['pointerdown', 'keydown', 'input', 'change', 'drop'].forEach(function(ev){ document.addEventListener(ev, clear, true); });
['clearFit', 'refreshEditor'].forEach(function(n){
  var f = D[n]; if (typeof f !== 'function') return;
  D[n] = function(){ clear(); return f.apply(this, arguments); };
});
D.renderPage = function(T, slide, vid, idx, deck){
  var now = Date.now();
  if (now - born > 800) { clear(); born = now; }
  var key = null;
  if (T && slide && T.id && arguments.length <= 5 && !slide.dg) {   /* not for chart / diagram slides: their layout is chosen outside this call, so a remembered page would show the same layout for every option */
    var a = sig(slide), b = deck && deck.slides ? sig(deck.slides) : '', t = sig([T.pal, T.fonts, T.s, T.__rec ? 1 : 0]);
    if (a !== null && b !== null && t !== null) key = T.id + '|' + t + '|' + vid + '|' + idx + '|' + a + '|' + b;
  }
  var p = key !== null && memo.hasOwnProperty(key) ? memo[key] : null;
  if (!p) {
    p = real.apply(this, arguments);
    if (key !== null && p && p.els && p.els.slice) { memo[key] = p; size++; } else return p;
  }
  var copy = {}; for (var k in p) copy[k] = p[k]; copy.els = p.els.slice();      /* the caller gets its own list */
  return copy;
};
})();
/* Editor "Change layout" panel: a glass template has 17-25 layouts per slide and all of them were drawn on every
   slide change and theme pick. A layout card is now drawn when it scrolls into view. */
(function(){
var D = window.DSTE; if (!D || !D.renderHTML || D.__lazyVP || !window.IntersectionObserver) return; D.__lazyVP = 1;
var real = D.renderHTML, jobs = {}, seq = 0, scanT = null;
function tidy(h){
  return String(h).replace(/class="dsp-vp-ihover[^"]*"/g, 'class="dsp-vp-ihover" style="display:none!important;"')
    .replace(/class="dsp-vp-imgreset[^"]*"/g, 'style="display:none!important;"')
    .replace(/class="dsp-vp-cropbtn[^"]*"/g, 'style="display:none!important;"')
    .replace(/contenteditable="true"/g, 'contenteditable="false"');
}
var io = new IntersectionObserver(function(list){
  list.forEach(function(en){
    if (!en.isIntersecting) return;
    var el = en.target, k = el.getAttribute('data-dste-lazyvp'), a = jobs[k];
    io.unobserve(el); delete jobs[k];
    if (!a || !el.parentNode) return;
    try { el.outerHTML = tidy(real.apply(D, a)); } catch (e) {}
  });
}, {rootMargin: '250px'});
function scan(){
  var live = {}, els = document.querySelectorAll('[data-dste-lazyvp]');
  for (var i = 0; i < els.length; i++) { var k = els[i].getAttribute('data-dste-lazyvp'); live[k] = 1; if (!els[i].__obs) { els[i].__obs = 1; io.observe(els[i]); } }
  for (var j in jobs) if (!live[j]) delete jobs[j];
}
D.renderHTML = function(T, slide, vid, idx, deck, opts){
  if (T && slide && deck && deck.slides && idx >= 0 && deck.slides[idx] && deck.slides[idx] !== slide && deck.slides.indexOf(slide) < 0) {
    var st = ''; try { st = String(new Error().stack || ''); } catch (e) {}
    if (/\bvpRender\b/.test(st)) {                                /* only the layout cards of the right-hand panel */
      var k = 'v' + (++seq); jobs[k] = arguments;
      clearTimeout(scanT); scanT = setTimeout(scan, 0);
      var bg = (T.pal && T.pal.bg) || '#111';
      return '<div data-dste-lazyvp="' + k + '" style="position:absolute;inset:0;background:' + String(bg).replace(/[^#\w(),.% -]/g, '') + ';"></div>';
    }
  }
  return real.apply(this, arguments);
};
})();
</script>
    <?php
}, 1061);