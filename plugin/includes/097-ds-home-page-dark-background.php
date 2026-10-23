<?php
/**
 * Code Snippets export — DS Home Page Dark Background
 *
 * Snippet ID : 778
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-092
 * Modified   : 2026-09-09 02:40:45
 * Load order : 097 of 144
 */
/**
 * Snippet Name: DS Home Page Dark Background
 * Run: Everywhere
 * Purpose: Applies the same dark theme (used on the Pricing page) to the Home page sections:
 *          Marketplace cards, Why Choose Us cards, About Our Company, and Client Testimonials.
 */
add_action('wp_head', function () {
	if (is_front_page() || is_page(3753)) {
		echo '<style id="ds-home-dark-bg">
			html body.page-id-3753 { background-color: #0a0d14 !important; }

			/* Marketplace section */
			.elementor-element-76b52626 h2.elementor-heading-title { color: #fff !important; }
			.elementor-element-76b52626 h5.elementor-heading-title { color: rgba(255,255,255,.55) !important; }
			.elementor-element-76b52626 .elementor-widget-image-box {
				background: #161b30;
				border: 1px solid rgba(255,255,255,.1);
				border-radius: 16px;
				overflow: hidden;
			}
			.elementor-element-76b52626 .elementor-image-box-content { padding: 20px; }
			.elementor-element-76b52626 .elementor-image-box-title,
			.elementor-element-76b52626 .elementor-image-box-title a { color: #fff !important; }
			.elementor-element-76b52626 .elementor-image-box-description { color: rgba(255,255,255,.55) !important; }

			/* Why Choose Us section */
			.elementor-element-2b03c396 h2.elementor-heading-title { color: #fff !important; }
			.elementor-element-2230900 > .e-con {
				background: #161b30 !important;
				border-radius: 16px;
			}
			.elementor-element-2230900 h4.elementor-heading-title { color: #fff !important; }
			.elementor-element-2230900 .elementor-widget-text-editor p { color: rgba(255,255,255,.55) !important; }
			.elementor-element-2230900 .elementor-divider-separator { border-color: rgba(255,255,255,.2) !important; }

			/* About Our Company section */
			.elementor-element-4c3cac6b h2.elementor-heading-title { color: #fff !important; }
			.elementor-element-4c3cac6b .elementor-widget-text-editor p { color: rgba(255,255,255,.55) !important; }

			/* Client Testimonials section */
			.elementor-element-7ec971ec h2.elementor-heading-title { color: #fff !important; }
			.elementor-element-7ec971ec .elementor-testimonial-content { color: rgba(255,255,255,.65) !important; }
			.elementor-element-7ec971ec .elementor-testimonial-name { color: #fff !important; }
			.elementor-element-7ec971ec .elementor-testimonial-image img {
				filter: brightness(0) invert(1);
			}

			/* Header account greeting text */
			#ds-avatar-trigger span { color: #fff !important; }
		</style>';
	}
}, 100);
