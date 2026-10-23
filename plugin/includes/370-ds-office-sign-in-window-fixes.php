<?php
/**
 * Code Snippets export — DS Office - sign-in window fixes (already signed in skips the login form, no Skip to content)
 *
 * Snippet ID : 1250
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-17
 * Load order : 370 of 370
 */
/**
 * DS Office - sign-in window fixes (PowerPoint add-in)
 * - Already signed in on the website: the add-in's Sign in / Sign up window skips the login form and goes straight
 *   to the add-in start page (?ds_office=1), which hands the sign-in to the side panel. It used to show the login
 *   form (without the Google / Microsoft buttons, which only show to signed-out visitors) to a signed-in user.
 * - "Skip to content" (the theme's keyboard link) no longer shows when the PowerPoint window opens; it still
 *   appears for people who move through the page with the Tab key.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'template_redirect', function () {
	if ( empty( $_GET['ds_office'] ) || ! is_user_logged_in() ) return; // phpcs:ignore
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ); // phpcs:ignore
	if ( ! preg_match( '#/(login|register|signup|sign-up)/?$#i', $path ) ) return;
	nocache_headers();
	wp_safe_redirect( home_url( '/?ds_office=1' ) );
	exit;
}, 1 );

add_action( 'wp_head', function () {
	?>
<style id="ds-office-skip">html.ds-office-mode .skip-link:not(:focus-visible),html.ds-office-mode .screen-reader-shortcut:not(:focus-visible){position:absolute!important;left:-9999px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(1px,1px,1px,1px)!important}</style>
<script>(function(){function off(){var a=document.activeElement;if(document.documentElement.classList.contains('ds-office-mode')&&a&&a.classList&&(a.classList.contains('skip-link')||a.classList.contains('screen-reader-shortcut'))){try{a.blur();}catch(e){}}}document.addEventListener('DOMContentLoaded',off);window.addEventListener('load',function(){off();setTimeout(off,300);});window.addEventListener('focus',function(){setTimeout(off,0);});})();</script>
	<?php
}, 99 );
