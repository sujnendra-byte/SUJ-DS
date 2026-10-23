<?php
/**
 * Code Snippets export — DS Mobile Footer Fix - no blank strip on the right on phones
 *
 * Snippet ID : 1130
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : 
 * Modified   : 2026-10-10 04:23:12
 * Load order : 300 of 300
 */
/**
 * DS Mobile Footer Fix - no blank strip on the right side on phones
 *
 * In the theme's footer settings, footer widget 3 (the "Business" links) has a 150 px left margin, and widgets 1 and 4
 * have 20 px side margins. On a phone the footer columns sit under each other at full width, so that margin pushes the
 * column past the right edge of the screen. That makes the whole page wider than the phone, and the browser shows an
 * empty strip on the right of every page. On tablets and phones (921 px and narrower) those side margins are removed;
 * computers keep them as they are.
 * NEW SNIPPET - nothing existing is changed. Undo: deactivate this snippet.
 */
add_action('wp_head', function () {
	if ( is_admin() ) return;
	echo '<style id="ds-mobile-footer-fix">@media (max-width:921px){.site-footer .footer-widget-area[data-section^="sidebar-widgets-footer-widget-"]{margin-left:0!important;margin-right:0!important;max-width:100%}}</style>' . "\n";
}, 99);
