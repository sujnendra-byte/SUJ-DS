<?php
/**
 * Code Snippets export — DS Pricing Page Dark Background
 *
 * Snippet ID : 777
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-091
 * Modified   : 2026-09-09 02:43:39
 * Load order : 096 of 144
 */
/**
 * Snippet Name: DS Pricing Page Dark Background
 * Run: Everywhere
 * Purpose: Removes the white Astra content wrapper background on the Pricing page (ID 3961) so it matches the dark redesign.
 */
add_action('wp_head', function () {
	if (is_page(3961)) {
		echo '<style id="ds-pricing-dark-bg">
			.entry-content.clear, main.site-main {
				background-color: #0a0d14 !important;
			}

			/* Header account greeting text */
			#ds-avatar-trigger span { color: #fff !important; }
		</style>';
	}
}, 100);
