<?php
/**
 * Code Snippets export — DS My Creations — hide Downloaded Presentations
 *
 * Snippet ID : 1019
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-ppt
 * Modified   : 2026-10-03 15:54:16
 * Load order : 218 of 218
 */
/**
 * DS My Creations - hide Downloaded Presentations
 * Run: Everywhere
 *
 * PowerPoint downloads are no longer kept on the server or counted as saved items, so the
 * "Downloaded Presentations" section of My Creations (Presentations tab) has nothing to show.
 * This snippet hides that section. Saved presentations above it are not touched.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_head', function () {
    if ( is_admin() ) return;
    ?>
<style id="ds-hide-built-ppts">.dsmp-section:has(> #dsmp-built){display:none!important}</style>
<?php
}, 99);

add_action('wp_footer', function () {
    if ( is_admin() ) return;
    ?>
<script id="ds-hide-built-ppts-js">(function(){function f(){var b=document.getElementById('dsmp-built');if(b&&b.parentNode&&b.parentNode.classList&&b.parentNode.classList.contains('dsmp-section'))b.parentNode.style.setProperty('display','none','important');}f();document.addEventListener('DOMContentLoaded',f);setTimeout(f,1500);})();</script>
<?php
}, 99);
