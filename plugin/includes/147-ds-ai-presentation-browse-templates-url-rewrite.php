<?php
/**
 * Code Snippets export — DS AI Presentation - Browse Templates URL (rewrite)
 *
 * Snippet ID : 857
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-147
 * Modified   : 2026-09-18 07:14:22
 * Load order : 147 of 147
 */
/**
 * Maps /ai-presentation/browse-templates/ onto the existing ai-presentation page
 * so the template picker survives a refresh. Purely additive: no other route
 * or query var is touched. Deactivate + re-save permalinks to fully revert.
 */
add_action( 'init', function () {
	add_rewrite_rule(
		'^ai-presentation/browse-templates/?$',
		'index.php?pagename=ai-presentation',
		'top'
	);
} );

/*
 * The rule above only works while it is in WordPress's saved list of address
 * rules. That list is rebuilt whenever something flushes it, and if that
 * happens while this code is not loaded (for example while one plugin version
 * is switched off and the next one is not on yet) the rule is dropped, and
 * /ai-presentation/browse-templates/ becomes "page not found" until
 * permalinks are saved again. So the address is also recognised here,
 * directly, without the saved list.
 */
add_filter( 'request', function ( $qv ) {
	if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
		return $qv;
	}
	$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ); // phpcs:ignore
	$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );
	if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	if ( 'ai-presentation/browse-templates' === trim( strtolower( $path ), '/' ) ) {
		return array( 'pagename' => 'ai-presentation' );
	}
	return $qv;
} );