<?php
/**
 * Code Snippets export — 🔒 DS Security Guard (Claude 2026-09-06)
 *
 * Snippet ID : 691
 * Active     : yes
 * Priority   : 5
 * Scope      : global
 * Tags       : designstager, ds-order-005
 * Modified   : 2026-09-09 02:44:10
 * Load order : 005 of 144
 */
/**
 * DS Security Guard — added by Claude, 2026-09-06
 * Isolated & reversible. Does NOT modify the DesignStager plugin.
 * 1) Blocks anonymous access to internal ds/v1/ppt-* debug/introspection/cache routes
 * 2) Requires login for the REST users listing (stops admin-email enumeration)
 * 3) Redirects the Creator Portal to /login/ for logged-out visitors (fixes the 500)
 * To undo: deactivate or delete this snippet.
 */

// (1) + (2) — gate REST routes before they run
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    if ($result !== null) { return $result; }
    $route = $request->get_route();

    // Internal PPT debug / introspection / cache tools -> admins only
    if (strpos($route, '/ds/v1/ppt-') === 0) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', 'Not allowed.', array('status' => 401));
        }
    }

    // Block anonymous user enumeration via the REST users collection
    if (preg_match('#^/wp/v2/users(/|$)#', $route)) {
        if (!is_user_logged_in()) {
            return new WP_Error('rest_forbidden', 'Authentication required.', array('status' => 401));
        }
    }

    return $result;
}, 5, 3);

// (3) — Creator Portal requires login; redirect guests instead of fataling
add_action('template_redirect', function () {
    if (is_user_logged_in()) { return; }
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path === 'creator-portal') {
        wp_safe_redirect(home_url('/login/?redirect_to=' . rawurlencode('/creator-portal/')));
        exit;
    }
}, 1);
