<?php
/**
 * Code Snippets export — Ds fix broken local font fallback.code snippets
 *
 * Snippet ID : 722
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-036
 * Modified   : 2026-09-09 02:42:26
 * Load order : 036 of 144
 */
/**
 * Snippet Name: DS Fix: Broken Local Font Fallback (v3)
 * Run: Everywhere
 *
 * v2 tried to win the CSS cascade against WordPress's broken local font
 * declarations by printing later in <head>. That worked for most weights
 * (or appeared to — more likely, disabling LiteSpeed's "Load CSS
 * Asynchronously" stopped it from eagerly fetching every declared weight
 * regardless of use). Either way, Open Sans weight 600 — confirmed via
 * the Network panel's Initiator tab to be pure CSS/parser resolution,
 * zero script involvement — kept losing the cascade regardless of
 * position.
 *
 * v3 stops trying to out-position the broken declarations and instead
 * removes them entirely from the rendered HTML, via output buffering on
 * template_redirect. The whole <style class="wp-fonts-local"> block
 * (Inter + Open Sans, every local file confirmed missing) is stripped
 * before the page reaches the browser. With nothing left to compete
 * against, there is no cascade order left to get wrong — deterministic
 * by construction, not by hoping a position wins. The replacement link
 * now also covers Inter, in case anything elsewhere on the site ever
 * references it, so nothing is silently left worse off by the removal.
 *
 * Replaces v2 entirely — deactivate/delete "DS Fix: Broken Local Font
 * Fallback (v2)" and use this one instead.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_head', function () {
    echo '<link rel="stylesheet" id="ds-font-override-css" href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" media="all" />';
}, PHP_INT_MAX);

add_action('template_redirect', function () {
    if ( is_admin() ) return;
    ob_start(function ($html) {
        return preg_replace(
            '/<style\b[^>]*\bclass=["\']wp-fonts-local["\'][^>]*>.*?<\/style>/is',
            '',
            $html
        );
    });
});
