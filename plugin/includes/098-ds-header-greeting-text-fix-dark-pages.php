<?php
/**
 * Code Snippets export — DS Header Greeting Text Fix (Dark Pages)
 *
 * Snippet ID : 779
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-093
 * Modified   : 2026-09-09 02:46:59
 * Load order : 098 of 144
 */
/**
 * Snippet Name: DS Header Greeting Text Fix (Dark Pages)
 * Run: Everywhere
 * Purpose: The "Hi, {name}" account greeting text in the header renders
 *          in a near-black color, which is invisible against the dark
 *          page backgrounds used on AI Studio, AI Presentation, AI Video,
 *          and Marketplace. This forces it white on those pages only,
 *          matching the same fix already applied to Home and Pricing.
 */
add_action('wp_head', function () {
	$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$dark_paths = array('/ai-studio', '/ai-presentation', '/ai-video', '/ai-audio', '/my-generated-images', '/marketplace', '/customize', '/blog', '/contact', '/copyright', '/privacy-policy', '/login', '/register', '/lostpassword');
	$is_dark_page = false;
	foreach ($dark_paths as $path) {
		if (strpos($uri, $path) !== false) {
			$is_dark_page = true;
			break;
		}
	}
	if ($is_dark_page) {
		echo '<style id="ds-header-greeting-fix">
			#ds-avatar-trigger span { color: #fff !important; }
		</style>';
	}
}, 100);
