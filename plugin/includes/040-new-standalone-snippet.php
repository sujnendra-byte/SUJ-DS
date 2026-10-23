<?php
/**
 * Code Snippets export — NEW standalone snippet
 *
 * Snippet ID : 726
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-040
 * Modified   : 2026-09-09 02:43:34
 * Load order : 040 of 144
 */
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/user-credit-log', array(
        'methods'             => 'GET',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function() {
            $uid = get_current_user_id();
            $transactions = get_user_meta($uid, 'ds_credit_transactions', true) ?: array();
            
            foreach ($transactions as &$t) {
                if (empty($t['product_name'])) {
                    $t['product_name'] = $t['description'] ?? 'Marketplace Purchase';
                }
                if (empty($t['approved_date'])) {
                    $t['approved_date'] = $t['date'] ?? '';
                }
                if (empty($t['approved_time']) && !empty($t['timestamp'])) {
                    $t['approved_time'] = date('H:i', $t['timestamp']);
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'log'     => array_reverse($transactions)
            ));
        },
    ));
});
