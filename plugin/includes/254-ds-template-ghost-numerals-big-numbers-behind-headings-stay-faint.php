<?php
/**
 * Code Snippets export — DS Template ghost numerals - big numbers behind headings stay faint in every theme
 *
 * Snippet ID : 1051
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 13:10:09
 * Load order : 245 of 246
 */
add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-ghost-numerals">
/* Some layouts (Mono Portfolio "Index rows", "Ghost columns" and similar) print a huge, very faint number BEHIND the
   heading. With a colour theme the number was recoloured to the full heading colour, so number and heading read as
   overlapping text. A big number that lies under other text is now kept faint in every theme. */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || D.__ghostNum) return; D.__ghostNum = 1;
var u = D.u, rp = D.renderPage;
function bgOf(page){
  var b = page.bg || {};
  if (typeof b.fill === 'string') return b.fill;
  if (b.grad && b.grad.stops && b.grad.stops.length) { var a = b.grad.stops[0][1], z = b.grad.stops[b.grad.stops.length - 1][1]; return typeof a === 'string' && typeof z === 'string' ? u.mix(a, z, 0.5) : null; }
  return null;
}
function hit(a, b){ return !(a.x >= b.x + b.w || a.x + a.w <= b.x || a.y >= b.y + b.h || a.y + a.h <= b.y); }
function fix(page){
  var els = page.els || [], bg = bgOf(page); if (!bg || !/^#[0-9a-fA-F]{6}$/.test(bg)) return;
  var texts = els.filter(function(e){ return e && e.t === 'text' && typeof e.text === 'string'; });
  texts.forEach(function(e){
    if (!(e.size >= 60) || !/^\(?\d{1,2}\)?$/.test(e.text.replace(/\s+/g, '')) || e.ed) return;
    if (typeof e.color !== 'string' || !/^#[0-9a-fA-F]{6}$/.test(e.color)) return;
    if (Math.abs(u.lum(e.color) - u.lum(bg)) < 0.3) return;                 /* already faint */
    var core = {x:e.x + e.size * 0.1, y:e.y + (e.h - e.size * 0.7) / 2, w:Math.min(e.w, e.size * 1.15), h:e.size * 0.7};
    var under = texts.some(function(o){ return o !== e && o.size < e.size * 0.6 && hit(core, o); });
    if (under) e.color = u.mix(bg, e.color, 0.14);
  });
}
D.renderPage = function(T){
  var page = rp.apply(this, arguments);
  try { if (page && T && T.__rec) fix(page); } catch (err) {}
  return page;
};
})();
</script>
    <?php
}, 1059);