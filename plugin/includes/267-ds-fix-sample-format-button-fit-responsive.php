<?php
/**
 * Code Snippets export — DS Fix - Sample format button fit (responsive)
 *
 * Snippet ID : 1097
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : 
 * Modified   : 2026-10-09 05:28:19
 * Load order : 267 of 296
 */
/**
 * DS Fix - Sample format button stays inside the Preserve My Text card
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;
add_action('wp_head', function () {
    echo '<style id="ds-sample-btn-fit">#dsp-mode-row .dsp-mode{min-width:0;}#dsp-mode-row .dsp-sample-dl{display:inline-flex!important;max-width:100%!important;box-sizing:border-box!important;white-space:normal!important;flex-wrap:wrap;justify-content:center;align-items:center;text-align:center;line-height:1.25;gap:4px;overflow-wrap:anywhere;}@media(max-width:1500px){#dsp-mode-row .dsp-sample-dl{font-size:12px!important;padding:5px 9px!important;border-radius:12px!important;}}</style>';
}, 99);
