<?php
/**
 * Code Snippets export — DS AI Audio Tab Injector
 *
 * Snippet ID : 786
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-100
 * Modified   : 2026-09-09 02:41:33
 * Load order : 105 of 144
 */
/**
* Snippet Name: DS AI Audio Tab Injector
* Run: Everywhere
*
* Adds an "AI Audio" button to the tab bar on your EXISTING AI Studio /
* AI Images / AI Presentation / AI Video pages — WITHOUT touching any
* existing snippet at all. This works purely client-side: after the page
* loads, it looks for the existing tab bar element (rendered by your
* current, unmodified snippets) and appends a new "AI Audio" tab that
* links to /ai-studio/ai-audio/.
*
* The AI Images and AI Presentation pages render their tab bar as
* .dst-bar > a.dst-btn. The AI Video page renders a DIFFERENT variant,
* .dstv-bar > a.dstv-btn. This script checks for both so the tab shows
* up correctly styled everywhere, and does nothing if neither is found.
*/

if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
var bar = document.querySelector('.dst-bar') || document.querySelector('.dstv-bar');
if (!bar) return;
if (bar.querySelector('[data-ds-audio-tab]')) return; // don't add twice

var usesVideoVariant = bar.classList.contains('dstv-bar');
var btnClass = usesVideoVariant ? 'dstv-btn' : 'dst-btn';

// Match an existing tab button's class list exactly, so the new tab
// picks up whatever spacing/utility classes the theme uses.
var sample = bar.querySelector('a.' + btnClass) || bar.querySelector('a');
var fullClass = sample ? sample.className : btnClass;

var isAudioPage = window.location.pathname.indexOf('/ai-studio/ai-audio') !== -1
|| window.location.pathname.indexOf('/ai-audio') !== -1;

var a = document.createElement('a');
a.href = '/ai-studio/ai-audio/';
a.setAttribute('data-ds-audio-tab', '1');
a.className = fullClass;
// The theme marks the active tab with an "on" class (not "active").
a.className = a.className.replace(/\bon\b/g, '').replace(/\s+/g, ' ').trim();
if (isAudioPage) {
a.className += ' on';
}
a.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18V5l12-2v13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="2"></circle><circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="2"></circle></svg> AI Audio';

bar.appendChild(a);
});
</script>
<?php
});
