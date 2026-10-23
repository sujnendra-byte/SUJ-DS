<?php
/**
 * Code Snippets export — DS Template Closing Contacts Fix
 *
 * Snippet ID : 1009
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 14:22:44
 * Load order : 215 of 218
 */
/**
 * DS Template Closing Contacts Fix
 * Run: Everywhere
 *
 * AI Presentation - design templates, closing slide: when the key-takeaway line (the text under
 * the picture on the Azure Corporate closing slide) is long enough to reach the row of contact
 * pills, the whole contact set (pills, icons, texts) is moved down so that nothing overlaps.
 * Closing slides where the two do not touch are not changed. Applies to the editor, the
 * thumbnails and the downloads. Needs the "DS Template Engine (T00)" snippet.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-closing-fix">
(function(){
(function(){
  var D = window.DSTE; if (!D || !D.renderPage || D.__closeFix) return; D.__closeFix = 1;
  var rp = D.renderPage, H = D.H || 720;
  function tb(e){ var h = e._h || e.h, y = e.y; if (e.va === 'm') y = e.y + (e.h - h) / 2; else if (e.va === 'b') y = e.y + e.h - h; return {x:e.x, y:y, w:e.w, h:h}; }
  D.renderPage = function(T, slide){
    var p = rp.apply(this, arguments);
    try {
      if (!slide || String(slide.type) !== 'closing' || !p || !p.els) return p;
      var conc = null, cons = [];
      p.els.forEach(function(e){ if (!e || e.t !== 'text' || !e.ed) return; if (e.ed.f === 'x-conc') conc = e; else if (e.ed.f === 'x-part' && e.ed.p === 1) cons.push(e); });
      if (!conc || !cons.length) return p;
      var c = tb(conc), hit = cons.filter(function(e){ return e.x < c.x + c.w && c.x < e.x + e.w && e.y < c.y + c.h + 6 && c.y < e.y + e.h; });
      if (!hit.length) return p;
      var top = Infinity, bot = 0; cons.forEach(function(e){ top = Math.min(top, e.y); bot = Math.max(bot, e.y + e.h); });
      var dy = Math.min(c.y + c.h + 16 - top, H - 44 - bot); if (dy <= 0) return p;
      /* the whole contact set: pills, icons and texts that sit in the contact rows */
      p.els.forEach(function(e){
        if (!e || e === conc || e.t === 'img' || e.y == null) return;
        var h = e.h || 0; if (h > 90) return;
        if (e.y >= top - 14 && e.y + h <= bot + 14) e.y += dy;
      });
    } catch (e) {}
    return p;
  };
})();
})();
</script>
<?php
}, 1103);
