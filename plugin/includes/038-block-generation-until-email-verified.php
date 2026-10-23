<?php
/**
 * Code Snippets export — Block generation until email verified
 *
 * Snippet ID : 724
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-038
 * Modified   : 2026-09-09 02:46:29
 * Load order : 038 of 144
 */
add_filter('rest_pre_dispatch', function($result, $server, $request){
    $route = $request->get_route();
    if (strpos($route, '/ds/v1/generate-image') !== false ||
        strpos($route, '/ds/v1/generate-ppt')   !== false) {
        $uid = get_current_user_id();
        if ($uid && !get_user_meta($uid,'ds_email_verified',true)
                 && !current_user_can('manage_options')) {
            // treat existing older accounts as verified if you want:
            // return $result; // <-- uncomment to grandfather everyone in
            return new WP_REST_Response(array('error'=>true,
                'message'=>'Please verify your email before generating.'), 403);
        }
    }
    return $result;
}, 10, 3);
