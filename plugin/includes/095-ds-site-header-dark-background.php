<?php
/**
 * Code Snippets export — DS Site Header Dark Background
 *
 * Snippet ID : 776
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-090
 * Modified   : 2026-09-09 02:45:46
 * Load order : 095 of 144
 */
add_action('wp_head', function () {
	echo '<style id="ds-header-dark-bg">
		.ast-primary-header-bar,
		.ast-main-header-bar-wrap,
		.ast-sticky-header .ast-primary-header-bar,
		#ast-desktop-header {
			background-color: #0a0d14 !important;
		}
		.ast-mobile-header-content,
		.ast-mobile-popup-drawer .drawer-inner-content {
			background-color: #0a0d14 !important;
		}
	</style>';
}, 100);
