<?php
/**
 * Code Snippets export — DS Template — Text Fit Guard
 *
 * Snippet ID : 972
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-02 14:27:56
 * Load order : 188 of 192
 */
/**
 * DS Template — Text Fit Guard
 * Run: Everywhere
 *
 * Keeps every text of a design-template slide inside its own box, in the editor,
 * the thumbnails, the PDF / PNG / JPG export and the PowerPoint file.
 *
 * Before: single-line texts (rows, pills, labels, agenda lines) never wrapped, so a
 * long point ran across the picture or off the slide; long texts in small boxes ran
 * over the next row.
 * Now: a text that does not fit is allowed to wrap and, only if still needed, is made
 * smaller (down to 40% of its size, never under 7) until it fits its box. Texts that
 * already fit are not touched.
 *
 * Needs the "DS Template Engine (T00)" snippet. NEW SNIPPET — edits no other snippet.
 * Undo: deactivate.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-fit-guard">
(function(){
function install(){
  var D = window.DSTE;
  if (!D || !D.Ctx || !D.fit) return false;
  if (D.__fitGuard) return true;
  D.__fitGuard = 1;
  var CP = D.Ctx.prototype, orig = CP.text, cv = null;
  function lineW(e){
    try {
      cv = cv || document.createElement('canvas').getContext('2d');
      cv.font = (e.wt || 400) + ' ' + e.size + 'px "' + e.fam + '", Arial, sans-serif';
      var s = e.caps ? e.text.toUpperCase() : e.text;
      return cv.measureText(s).width + (e.ls || 0) * e.size * s.length;
    } catch (x) { return e.text.length * e.size * 0.52; }
  }
  function guard(e){
    if (!e || e.t !== 'text' || !e.text || !(e.w > 24) || !(e.h > 8) || !(e.size > 0)) return;
    var size = e.size, lh = e.lh || 1.2, fl = Math.min(size, Math.max(7, Math.round(size * 0.4)));
    var q = {text:e.text, w:e.w, font:e.fam, wt:e.wt, lh:lh, caps:e.caps, ls:e.ls};
    if (e.nw) { if (lineW(e) <= e.w * 1.02) return; }                 /* one line and it fits */
    else { q.h = 1e5; q.max = size; q.min = size; if (D.fit(q).n * size * lh <= e.h + size * 0.6) return; }  /* wrapped and it fits */
    q.h = Math.max(e.h, fl * lh); q.max = size; q.min = fl;
    var g = D.fit(q);
    e.nw = 0; e.size = g.s; e._n = g.n; e._h = Math.min(e.h, g.n * g.s * lh);
  }
  CP.text = function(){
    var e = orig.apply(this, arguments);
    if (!D.fitGuardOff) { try { guard(e); } catch (x) {} }
    return e;
  };
  try { if (D.clearFit) D.clearFit(); if (D.refreshEditor && window.S && S.data && S.data.tpl) D.refreshEditor(); } catch (x) {}
  return true;
}
if (!install()) { var n = 0, t = setInterval(function(){ if (install() || ++n > 80) clearInterval(t); }, 250); }
})();
</script>
    <?php
}, 1009);
