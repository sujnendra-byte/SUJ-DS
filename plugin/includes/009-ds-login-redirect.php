<?php
/**
 * Code Snippets export — DS Login Redirect
 *
 * Snippet ID : 695
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-009
 * Modified   : 2026-09-09 02:44:21
 * Load order : 009 of 144
 */
// Fix 1: WordPress login redirect
add_filter('login_redirect', function($redirect_to, $request, $user) {
    if (!is_wp_error($user)) {
        if (isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to'])) {
            return esc_url_raw($_REQUEST['redirect_to']);
        }
        // Signed in from the PowerPoint add-in's side panel: go back to the add-in's start page there.
        if ( ! empty( $_REQUEST['ds_office'] ) || false !== strpos( (string) wp_get_referer(), 'ds_office=' ) ) {
            return home_url('/?ds_office=1');
        }
        return home_url('/marketplace');
    }
    return $redirect_to;
}, 10, 3);

// Fix 2: Theme My Login redirect
add_filter('tml_request_redirect_to', function($redirect_to) {
    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
        return esc_url_raw($_GET['redirect_to']);
    }
    return home_url('/marketplace');
});

// Fix 3: After TML login, redirect properly
add_action('tml_user_login', function($user) {
    $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';
    if (empty($redirect_to)) {
        $in_addin = ! empty($_REQUEST['ds_office']) || false !== strpos((string) wp_get_referer(), 'ds_office=');
        $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : ($in_addin ? home_url('/?ds_office=1') : home_url('/marketplace'));
    }
    wp_safe_redirect($redirect_to);
    exit;
});

// Fix 4: Pass redirect_to into the login form hidden field
add_action('tml_login_form_bottom', function() {
    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
        echo '<input type="hidden" name="redirect_to" value="' . esc_attr($_GET['redirect_to']) . '"/>';
    }
});

// Fix 5: DLM gate — login + tier + daily/monthly limit (server-side)
remove_all_filters('dlm_can_download');
add_filter('dlm_can_download', function($can_download, $download, $version) {
    // Not logged in → send to login
    if (!is_user_logged_in()) {
        $current = home_url(add_query_arg(array(), $_SERVER['REQUEST_URI']));
        wp_safe_redirect(home_url('/login/') . '?redirect_to=' . urlencode($current));
        exit;
    }

    $uid = get_current_user_id();

    // Figure out tier the SAME way the marketplace grid does — from the
    // dlm_download_tag taxonomy — not the unused ds_item_tier meta key.
    $dlm_id = 0;
    if (is_object($download)) {
        if (method_exists($download, 'get_id')) {
            $dlm_id = (int) $download->get_id();
        } elseif (isset($download->id)) {
            $dlm_id = (int) $download->id;
        } elseif (isset($download->ID)) {
            $dlm_id = (int) $download->ID;
        }
    }
    $item_tier = 'free';
    if ($dlm_id) {
        $tags      = get_the_terms($dlm_id, 'dlm_download_tag') ?: array();
        $tag_slugs = is_array($tags) ? array_map(function($t){ return strtolower($t->slug); }, $tags) : array();
        if (in_array('pro', $tag_slugs, true)) $item_tier = 'pro';
    }

    // This is now the ONE place that actually deducts quota.
    if (function_exists('ds_dl_check_core')) {
        $res = ds_dl_check_core($uid, $item_tier, true);
        if (empty($res['ok'])) {
            wp_safe_redirect(home_url('/pricing'));
            exit;
        }
    }
    return true;
}, 10, 3);
