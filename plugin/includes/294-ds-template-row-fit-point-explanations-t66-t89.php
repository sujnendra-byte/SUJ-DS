<?php
/**
 * Code Snippets export — DS Template Row Fit (point explanations, T66-T89)
 *
 * Snippet ID : 1124
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 11:36:04
 * Load order : 294 of 296
 */
/**
 * DS Template Row Fit - keeps point explanations visible in row layouts
 * Run: Everywhere
 * When a row layout (e.g. Finance Prism 'Check pills') has too many points for one column, it uses two
 * columns so every heading keeps its explanation. Covers templates 66-89. Needs DS Template Kit 3.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-rowfit">
/* DS Template Row Fit — when a row layout has too many points for one column, it uses two columns
   so every heading keeps its explanation visible. Applies to the row layouts of templates 66-89. */
(function(){
var D = window.DSTE; if (!D || !D.reg || D.__rowFit) return; D.__rowFit = 1;
var SIG = 'per = Math.ceil(n / cl), g = X.rg || 12';
function wrap(fn){
  if (fn.__dsFit) return fn;
  var w = function(C, Z, o){
    o = o || {};
    var it = (C.it || []).slice(0, o.max || 8), n = it.length, hasD = it.some(function(t){ return t.h && t.d; });
    var cl = o.cols || (n > 4 && Z.w > 760 ? 2 : 1), per = Math.ceil(n / Math.max(1, cl)), rh = (Z.h - 12 * (per - 1)) / Math.max(1, per);
    if (hasD && cl === 1 && n > 3 && rh < 66) { var o2 = {}; for (var k in o) o2[k] = o[k]; o2.cols = 2; return fn(C, Z, o2); }
    return fn(C, Z, o);
  };
  w.__dsFit = 1; return w;
}
function patch(T){
  var vs = (T && T.variants) || {};
  Object.keys(vs).forEach(function(k){ (vs[k] || []).forEach(function(v){ var o = v && v.o; if (o && typeof o.body === 'function' && !o.body.__dsFit && String(o.body).indexOf(SIG) >= 0) o.body = wrap(o.body); }); });
}
Object.keys(D.reg).forEach(function(id){ try { patch(D.reg[id]); } catch(e){} });
var prev = D.onRegister; D.onRegister = function(T){ if (prev) try { prev(T); } catch(e){} try { patch(T); } catch(e){} };
})();

</script>
<?php
}, 1150);
