<?php
/**
 * Code Snippets export — download with log in
 *
 * Snippet ID : 692
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-006
 * Modified   : 2026-09-09 02:41:20
 * Load order : 006 of 144
 */
/**
 * Designstager — DLM Download Gate (Safe Version)
 * Add this in Code Snippets → Run everywhere → Save & Activate
 * 
 * IMPORTANT: Make sure the old "DLM Allow Downloads" snippet is deleted/deactivated first.
 */

add_filter( 'dlm_can_download', function( $can_download, $download, $version ) {

    // Guest? Redirect to login page
    if ( ! is_user_logged_in() ) {
        $redirect_after_login = urlencode( home_url( add_query_arg( [] ) ) );
        $login_url = home_url( '/login/?redirect_to=' . $redirect_after_login );
        wp_safe_redirect( $login_url );
        exit;
    }

    // Logged in — allow download
    return true;

}, 10, 3 );
