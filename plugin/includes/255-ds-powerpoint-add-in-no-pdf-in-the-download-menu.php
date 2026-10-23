<?php
/**
 * Code Snippets export — DS PowerPoint add-in - no PDF in the Download menu
 *
 * Snippet ID : 1052
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : office-addin
 * Modified   : 2026-10-04 13:17:02
 * Load order : 246 of 246
 */
/* PowerPoint add-in: no PDF in the Download menu. PowerPoint cannot insert a PDF file into a presentation, so inside
   the add-in (html.ds-office-mode) the PDF choice is hidden. The website in a normal browser is not changed. */
add_action('wp_head', function () {
    if ( is_admin() ) return;
    ?>
<style id="ds-office-no-pdf">
html.ds-office-mode #dsx-menu button[data-k="pdf"]{display:none!important;}
</style>
    <?php
}, 99);