<?php
/**
 * Code Snippets export — DS Template — Preserve Mode Fix
 *
 * Snippet ID : 930
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:42:29
 * Load order : 187 of 191
 */
/**
 * DS Template — Preserve Mode Fix
 * Run: Everywhere
 *
 * "Preserve My Text" decks (typed text or an uploaded document) were always
 * built on the Classic design, even when a design template was picked,
 * because the preserve endpoints never carried the picked template.
 * This tags the preserved deck with the template picked on the home screen,
 * so the editor, layouts and PPTX download use that template.
 * New snippet — no other snippet is changed. Undo: deactivate.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-preserve-fix">
(function(){
  if (window.__dsTplPreserveFix) return; window.__dsTplPreserveFix = 1;
  var RX = /\/ds\/v1\/(preserve-confirm|doc-preserve-confirm)(\?|$)/;
  function picked(){
    var X = window.DSTE; if (!X || !X.reg) return null;
    return X.pick && X.pick !== 'classic' && X.reg[X.pick] ? X.pick : null;
  }
  var prev = window.fetch;
  window.fetch = function(url, opts){
    var us = typeof url === 'string' ? url : (url && url.url) || '';
    var p = prev.apply(this, arguments);
    if (!RX.test(us)) return p;
    var pk = picked(); if (!pk) return p;
    var theme = (window.DSTE && window.DSTE.homeTheme) || '';
    return p.then(function(res){
      if (!res || !res.ok) return res;
      return res.clone().json().then(function(d){
        if (!d || !d.ppt_data || typeof d.ppt_data !== 'object') return res;
        if (!d.ppt_data.tpl) { d.ppt_data.tpl = pk; if (d.ppt_data.tplTheme == null) d.ppt_data.tplTheme = theme; }
        var h = new Headers(res.headers); h.set('Content-Type', 'application/json');
        return new Response(JSON.stringify(d), {status: res.status, statusText: res.statusText, headers: h});
      }).catch(function(){ return res; });
    });
  };
})();
</script>
<?php
}, 1008);
