<?php
/**
 * Code Snippets export — DS Mark Submitted [CLONE]
 *
 * Snippet ID : 771
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-085
 * Modified   : 2026-09-09 02:46:13
 * Load order : 090 of 144
 */
/**
 * Snippet Name: DS Mark Submitted
 * Run: Everywhere
 */
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/mark-submitted', array(
        'methods'             => 'POST',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function(WP_REST_Request $req) {
            $uid = get_current_user_id();
            $aid = (int) $req->get_param('attachment_id');
            $fid = (int) $req->get_param('file_id');
            // R2-stored images/videos never get a local attachment post
            // (attachment_id stays 0) so file_id is the only reliable
            // identifier for anything generated since the move to R2
            // storage. Accept either.
            if (!$aid && !$fid) return rest_ensure_response(array('success' => false));
            // Save to post meta only when we have a real attachment post to attach it to.
            if ($aid) {
                update_post_meta($aid, 'ds_marketplace_submitted', true);
                // Also save to user meta list for fast lookup
                $submitted = get_user_meta($uid, 'ds_submitted_aids', true) ?: array();
                if (!in_array($aid, $submitted)) {
                    $submitted[] = $aid;
                    update_user_meta($uid, 'ds_submitted_aids', $submitted);
                }
            }
            if ($fid) {
                $submitted_fids = get_user_meta($uid, 'ds_submitted_fids', true) ?: array();
                if (!in_array($fid, $submitted_fids)) {
                    $submitted_fids[] = $fid;
                    update_user_meta($uid, 'ds_submitted_fids', $submitted_fids);
                }
            }
            // Also mark submitted in ds_ai_generations array
$gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
foreach ($gens as $i => $gen) {
    $gen_aid = (int)($gen['attachment_id'] ?? 0);
    $gen_fid = (int)($gen['file_id'] ?? 0);
    if (($aid && $gen_aid === $aid) || ($fid && $gen_fid === $fid)) {
        $gens[$i]['submitted'] = true;
    }
}
update_user_meta($uid, 'ds_ai_generations', $gens);

return rest_ensure_response(array('success' => true));
        },
    ));
	// One-time migration
register_rest_route('ds/v1', '/fix-submitted', array(
    'methods'             => 'GET',
    'permission_callback' => function() { return is_user_logged_in(); },
    'callback'            => function() {
        global $wpdb;
        $users = get_users(array('fields' => array('ID')));
        $total_fixed = 0;
        foreach ($users as $user) {
            $uid  = $user->ID;
            $gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
            $changed = false;
            foreach ($gens as $i => $gen) {
                if (!is_array($gen)) continue;
                $aid = (int)($gen['attachment_id'] ?? 0);
                if (!$aid) continue;
                if (!empty($gen['submitted'])) continue;
                $sub = get_post_meta($aid, 'ds_marketplace_submitted', true);
                $did = get_post_meta($aid, 'ds_marketplace_download_id', true);
                if ($sub || $did) {
                    $gens[$i]['submitted'] = true;
                    $changed = true;
                    $total_fixed++;
                }
            }
            if ($changed) {
                update_user_meta($uid, 'ds_ai_generations', $gens);
            }
        }
        return rest_ensure_response(array('fixed' => $total_fixed, 'status' => 'done'));
    },
));
});
add_action( 'rest_api_init', function() {
    register_rest_route( 'ds/v1', '/approve-marketplace', [
        'methods'             => 'POST',
        'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        'callback'            => function( WP_REST_Request $req ) {
            $post_id = (int) $req->get_param( 'post_id' );
            if ( ! $post_id ) return [ 'success' => false, 'error' => 'Missing post_id' ];

            // LOCK: Prevent duplicate approvals with transient
            $lock_key = 'ds_approve_lock_' . $post_id;
            if ( get_transient( $lock_key ) ) {
                return [ 'success' => false, 'error' => 'Already processing' ];
            }
            set_transient( $lock_key, true, 30 ); // 30 second lock

            if ( get_post_meta( $post_id, 'ds_marketplace_cashback_paid', true ) ) {
                delete_transient( $lock_key );
                return [ 'success' => false, 'error' => 'Already paid' ];
            }

            // Get from creator upload (primary flow)
$creator_upload_id = (int) get_post_meta( $post_id, 'ds_creator_upload_id', true );
$submitter_id      = 0;
$att_id            = 0;

if ( $creator_upload_id ) {
    $submitter_id = (int) get_post( $creator_upload_id )->post_author;
    $att_id       = (int) get_post_meta( $creator_upload_id, 'ds_creator_thumbnail_id', true )
                 ?: (int) get_post_meta( $creator_upload_id, 'ds_creator_file_id', true );
}

// Fallback: old marketplace flow via attachment meta
if ( ! $submitter_id ) {
    $atts = get_posts( [
        'post_type'      => 'attachment',
        'meta_query'     => [ [ 'key' => 'ds_marketplace_download_id', 'value' => $post_id ] ],
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ] );
    if ( ! empty( $atts ) ) {
        $att_id       = $atts[0];
        $submitter_id = (int) get_post_meta( $att_id, 'ds_marketplace_submitter', true );
    }
}

            if ( ! $submitter_id ) return [ 'success' => false, 'error' => 'Cannot find submitter' ];

            // Prefer the amount set at submission time (e.g. 3 for AI Video, via
            // ds_creator_cashback_amount on the linked Creator Upload post) so this
            // independent DLM-side approval path pays the same amount as the
            // Creator Uploads approval flow, instead of silently defaulting to 1.
            $cashback = $creator_upload_id ? (int) get_post_meta( $creator_upload_id, 'ds_creator_cashback_amount', true ) : 0;
            if ( ! $cashback ) {
                $cashback = (int) get_post_meta( $post_id, 'ds_marketplace_cashback', true ) ?: 1;
            }
            // Use ds_add_credits so all pools stay in sync
if ( function_exists('ds_add_credits') ) {
    ds_add_credits( $submitter_id, $cashback );
} else {
    $current     = (int) get_user_meta( $submitter_id, 'ds_ai_credits', true );
    $new_balance = $current + $cashback;
    update_user_meta( $submitter_id, 'ds_ai_credits', $new_balance );
}
$new_balance = function_exists('ds_get_credits') ? ds_get_credits( $submitter_id ) : (int) get_user_meta( $submitter_id, 'ds_ai_credits', true );

            // Log to credit history
$product_title  = get_the_title( $post_id ) ?: 'Marketplace Purchase';
$transactions   = get_user_meta( $submitter_id, 'ds_credit_transactions', true ) ?: [];
$transactions[] = [
    'type'           => 'marketplace',
    'amount'         => $cashback,
    'product_name'   => $product_title,
    'approved_date'  => current_time( 'Y-m-d' ),
    'approved_time'  => wp_date( 'H:i', null, new DateTimeZone('Asia/Kolkata') ),
    'label'          => '🎉 Marketplace Cashback',
    'description'    => $product_title,
    'date'           => current_time( 'Y-m-d' ),
    'timestamp'      => time(),
];
            update_user_meta( $submitter_id, 'ds_credit_transactions', $transactions );

            // Mark paid
            update_post_meta( $post_id, 'ds_marketplace_cashback_paid', true );
            update_post_meta( $post_id, 'ds_marketplace_status', 'approved' );

            // Mark accepted in ds_ai_generations
            if ( $att_id ) {
                $gens = get_user_meta( $submitter_id, 'ds_ai_generations', true ) ?: [];
                foreach ( $gens as $i => $gen ) {
                    if ( (int)( $gen['attachment_id'] ?? 0 ) === $att_id ) {
                        $gens[$i]['accepted']  = true;
                        $gens[$i]['submitted'] = true;
                    }
                }
                update_user_meta( $submitter_id, 'ds_ai_generations', $gens );
            }

            $user = get_userdata( $submitter_id );
            return [
                'success'       => true,
                'credits_added' => $cashback,
                'user'          => $user ? $user->display_name : 'User',
                'new_balance'   => $new_balance,
            ];
        },
    ] );
} );
add_action( 'rest_api_init', function() {
    register_rest_route( 'ds/v1', '/check-generations/(?P<user_id>\d+)', [
        'methods'             => 'GET',
        'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        'callback'            => function( WP_REST_Request $req ) {
            $uid  = (int) $req->get_param( 'user_id' );
            $gens = get_user_meta( $uid, 'ds_ai_generations', true ) ?: [];
            return [
                'total'    => count( $gens ),
                'accepted' => array_values( array_filter( $gens, fn($g) => ! empty( $g['accepted'] ) ) ),
                'submitted'=> array_values( array_filter( $gens, fn($g) => ! empty( $g['submitted'] ) ) ),
                'all_ids'  => array_column( $gens, 'attachment_id' ),
            ];
        },
    ] );
} );

// ========== CREDIT LOG ENDPOINT ==========
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/credit-log', array(
        'methods'             => 'GET',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function() {
            $uid = get_current_user_id();
            $transactions = get_user_meta($uid, 'ds_credit_transactions', true) ?: array();
            
            foreach ($transactions as &$t) {
                if (!isset($t['product_name'])) {
                    $t['product_name'] = $t['description'] ?? 'Marketplace Purchase';
                }
                if (!isset($t['approved_date'])) {
                    $t['approved_date'] = $t['date'] ?? date('Y-m-d');
                }
                if (!isset($t['approved_time'])) {
                    $t['approved_time'] = isset($t['timestamp']) ? date('H:i', $t['timestamp']) : '';
                }
            }
            
            return rest_ensure_response(array('success' => true, 'log' => array_reverse($transactions)));
        },
    ));
});
// ONE-TIME FIX: Update old transactions missing product_name and approved_time
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/fix-credit-log', array(
        'methods'             => 'GET',
        'permission_callback' => function() { return current_user_can('manage_options'); },
        'callback'            => function() {
            global $wpdb;
            $users = get_users(array('fields' => array('ID')));
            $fixed = 0;
            foreach ($users as $user) {
                $uid = $user->ID;
                $transactions = get_user_meta($uid, 'ds_credit_transactions', true) ?: array();
                $changed = false;
                foreach ($transactions as $i => $t) {
                    if (empty($t['product_name'])) {
                        $transactions[$i]['product_name'] = $t['description'] ?? 'Marketplace Purchase';
                        $changed = true;
                    }
                    if (empty($t['approved_time']) && !empty($t['timestamp'])) {
                        $transactions[$i]['approved_time'] = date('H:i', $t['timestamp']);
                        $changed = true;
                    }
                    if (empty($t['approved_date'])) {
                        $transactions[$i]['approved_date'] = $t['date'] ?? date('Y-m-d');
                        $changed = true;
                    }
                }
                if ($changed) {
                    update_user_meta($uid, 'ds_credit_transactions', $transactions);
                    $fixed++;
                }
            }
            return rest_ensure_response(array('fixed' => $fixed, 'status' => 'done'));
        },
    ));
});
