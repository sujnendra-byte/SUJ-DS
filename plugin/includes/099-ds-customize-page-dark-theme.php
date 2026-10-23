<?php
/**
 * Code Snippets export — DS Customize Page Dark Theme
 *
 * Snippet ID : 780
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-094
 * Modified   : 2026-09-09 02:45:19
 * Load order : 099 of 144
 */
/**
 * Snippet Name: DS Customize Page Dark Theme
 * Run: Everywhere (scoped by REQUEST_URI check below)
 * Purpose: The "Design Solutions That Work for You" and "FAQ" sections on
 *          the /customize/ page were built with the default light Elementor
 *          theme (white background, near-black text), while the rest of
 *          the site (Home, Pricing, AI Studio, Marketplace, and the top
 *          "Start Your Project" panel on this same page) uses a dark theme.
 *          This makes those two sections dark to match, and fixes the text
 *          colors so everything stays readable against the dark background.
 */
add_action('wp_head', function () {
	$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	if (strpos($uri, '/customize') !== false) {
		echo '<style id="ds-customize-dark-theme">
			.elementor-4003 { background: #10131c !important; }
			body.page-template-elementor_header_footer,
			body.page.page-id-4003,
			html body.page-id-4003,
			body { background: #10131c !important; }

			.elementor-element-2b03c396 .elementor-heading-title,
			.elementor-element-7ec971ec .elementor-heading-title {
				color: #ffffff !important;
			}

			.elementor-element-2b03c396 p {
				color: #94a3b8 !important;
			}

			.elementor-element-2b03c396 .elementor-button-text {
				color: #ffffff !important;
			}
			.elementor-element-2b03c396 .elementor-button {
				border: 1px solid rgba(255,255,255,.25) !important;
			}

			.elementor-element-7ec971ec .elementor-toggle-title {
				color: #e5e7eb !important;
			}
			.elementor-element-7ec971ec .elementor-tab-title {
				border-bottom-color: rgba(255,255,255,.12) !important;
			}
			.elementor-element-7ec971ec .elementor-tab-content p {
				color: #94a3b8 !important;
			}
		</style>';
	}
}, 100);
