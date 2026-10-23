<?php
/**
 * Code Snippets export — DS AI Video Tab Injector
 *
 * Snippet ID : 742
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-056
 * Modified   : 2026-09-09 02:46:43
 * Load order : 056 of 144
 */
/**
 * Snippet Name: DS AI Video Tab Injector
 * Run: Everywhere
 *
 * Adds an "AI Video" button to the tab bar on your EXISTING AI Studio /
 * AI Images / AI Presentation pages — WITHOUT touching the "DS AI Studio"
 * snippet at all. This works purely client-side: after the page loads, it
 * looks for the existing .dst-bar element (rendered by your current,
 * unmodified Snippet 5) and appends one more button to it via JavaScript.
 *
 * If .dst-bar isn't found on a page (e.g. it's not an AI Studio page),
 * this does nothing.
 */

if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var bar = document.querySelector('.dst-bar');
        if (!bar) return;
        if (bar.querySelector('[data-ds-video-tab]')) return; // don't add twice

        var isVideoPage = window.location.pathname.indexOf('/ai-studio/ai-video') !== -1;
        var a = document.createElement('a');
        a.href = '<?php echo esc_js( home_url('/ai-studio/ai-video/') ); ?>';
        a.setAttribute('data-ds-video-tab', '1');
        a.className = 'dst-btn' + (isVideoPage ? ' on' : '');
        a.style.textDecoration = 'none';
        a.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none">'
            + '<polygon points="5 3 19 12 5 21 5 3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>'
            + '</svg> AI Video';
        bar.appendChild(a);
    });
    </script>
    <?php
});
