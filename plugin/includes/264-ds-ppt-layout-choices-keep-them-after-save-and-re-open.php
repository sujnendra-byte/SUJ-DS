<?php
/**
 * Code Snippets export — DS PPT Layout Choices - keep them after save and re-open
 *
 * Snippet ID : 1080
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-ppt
 * Modified   : 2026-10-08 00:11:04
 * Load order : 255 of 257
 */
/**
 * DS PPT Layout Choices - keep them when a deck is saved and opened again
 * Run: Everywhere
 *
 * AI Presentation editor. The layout picked for each slide ("Change layout") is kept in S.choices and saved with
 * the deck as slide_variants. A deck saved without any picked layout comes back from the server with slide_variants
 * as an empty LIST instead of an empty object; the editor then keeps writing "slide_0", "slide_1" ... onto that list,
 * and a list written out as JSON loses them. Result: after the first save, layouts picked later were not saved - the
 * deck opened again with every slide on its first layout (and the PowerPoint download used the first layout too).
 * This turns the list back into an object as soon as it appears, keeping whatever was already put on it.
 * NEW SNIPPET. Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-ppt-choices-fix">
(function(){
  if (window.__dsChoicesFix) return; window.__dsChoicesFix = 1;
  function fix(){
    try {
      if (!window.S) return;
      var c = S.choices;
      if (Array.isArray(c)) {
        var o = {}, k;
        for (k in c) { if (/^slide_\d+$/.test(k) && typeof c[k] === 'string') o[k] = c[k]; }
        S.choices = o;
        try { sessionStorage.setItem('dspVPC', JSON.stringify(o)); } catch (e) {}
      } else if (c && typeof c === 'object') {
        var raw = null; try { raw = sessionStorage.getItem('dspVPC'); } catch (e2) {}
        if (raw === '[]') { try { sessionStorage.setItem('dspVPC', JSON.stringify(c)); } catch (e3) {} }
      }
    } catch (x) {}
  }
  fix(); setInterval(fix, 200);
})();
</script>
    <?php
}, 6);
