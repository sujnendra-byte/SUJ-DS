<?php
/**
 * Code Snippets export — DS AI Audio — 40/60 Studio Layout
 *
 * Snippet ID : 819
 * Active     : yes
 * Priority   : 1004
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-09-09 05:47:32
 * Load order : 144 of 144
 */
/**
 * DS AI Audio — 40/60 Studio Layout
 *
 * The studio's left settings column is a fixed 360px, which works out at about
 * 30/70 on a desktop screen. This widens it to a true 40/60 split, leaving 60%
 * for the My Creations playlist.
 *
 * Desktop only: the studio's own max-width 980px rule still stacks the two
 * columns on tablet and mobile, and this override deliberately does not apply
 * below that breakpoint.
 *
 * Deactivate this snippet to go back to the original 360px sidebar.
 */

add_action(
	'wp_head',
	function () {

		if ( is_admin() ) {
			return;
		}

		// Only on the AI Audio studio. Reuses the Outpaint snippet's page check
		// when it is available, and falls back to a URL match when it is not.
		$is_audio = false;

		if ( function_exists( 'ds_op_is_audio_page' ) ) {
			$is_audio = ds_op_is_audio_page();
		} elseif ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$uri      = strtolower( (string) $_SERVER['REQUEST_URI'] );
			$is_audio = ( false !== strpos( $uri, 'ai-audio' ) || false !== strpos( $uri, 'ai-music' ) );
		}

		if ( ! $is_audio ) {
			return;
		}
		?>
<style id="dsaaLayout4060">
@media (min-width: 981px) {
	.dsaaGrid {
		grid-template-columns: minmax(0, 2fr) minmax(0, 3fr) !important;
	}
}
</style>
		<?php
	},
	99
);
