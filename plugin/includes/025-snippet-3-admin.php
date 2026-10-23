<?php
/**
 * Code Snippets export — Snippet 3 admin
 *
 * Snippet ID : 711
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-025
 * Modified   : 2026-09-09 02:42:35
 * Load order : 025 of 144
 */
/**
 * DS Creator Portal — Snippet 3: Admin Approval UI
 * WHERE TO ADD: Paste this into functions.php AFTER Snippet 1 and 2.
 *
 * WHAT THIS DOES:
 *  - Adds Approve / Reject buttons on the Creator Upload admin list
 *  - Adds a detailed review meta box on each upload's edit screen
 *  - Handles the approve action → triggers Snippet 4 (DLM creation)
 *  - Handles the reject action → updates status to 'rejected'
 *  - Shows a "Pending" filter count in the admin menu
 *  - Adds a custom Status column to the uploads list table
 */

// Builds [category_term_id => ['name'=>.., 'subs'=>[sub_id=>sub_name,...]]] from the real
// dlm_download_category taxonomy under "Marketplace". Duplicated from the Admin-Ajax
// snippet on purpose, so this file works even if that one is ever deactivated.
if (!function_exists('ds_get_category_tree')) {
function ds_get_category_tree() {
    $tree = array();
    $marketplace = get_term_by('name', 'Marketplace', 'dlm_download_category');
    if (!$marketplace || is_wp_error($marketplace)) return $tree;

    $cats = get_terms(array(
        'taxonomy'   => 'dlm_download_category',
        'hide_empty' => false,
        'parent'     => $marketplace->term_id,
    ));
    if (is_wp_error($cats)) return $tree;

    foreach ($cats as $cat) {
        $subs = get_terms(array(
            'taxonomy'   => 'dlm_download_category',
            'hide_empty' => false,
            'parent'     => $cat->term_id,
        ));
        $sub_list = array();
        if (!is_wp_error($subs)) {
            foreach ($subs as $sub) {
                $sub_list[$sub->term_id] = wp_specialchars_decode($sub->name, ENT_QUOTES);
            }
        }
        $tree[$cat->term_id] = array('name' => wp_specialchars_decode($cat->name, ENT_QUOTES), 'subs' => $sub_list);
    }
    // "Audio" is its own top-level term (a sibling of "Marketplace"), not nested
    // under it, so it has to be merged in separately.
    $audio_term = get_term_by('name', 'Audio', 'dlm_download_category');
    if ($audio_term && !is_wp_error($audio_term) && !$audio_term->parent) {
        $audio_subs = get_terms(array(
            'taxonomy'   => 'dlm_download_category',
            'hide_empty' => false,
            'parent'     => $audio_term->term_id,
        ));
        $audio_sub_list = array();
        if (!is_wp_error($audio_subs)) {
            foreach ($audio_subs as $sub) {
                $audio_sub_list[$sub->term_id] = wp_specialchars_decode($sub->name, ENT_QUOTES);
            }
        }
        $tree[$audio_term->term_id] = array('name' => wp_specialchars_decode($audio_term->name, ENT_QUOTES), 'subs' => $audio_sub_list);
    }

    return $tree;
}
}

// ─────────────────────────────────────────────
// 1. ADD CUSTOM COLUMNS TO ADMIN LIST TABLE
// ─────────────────────────────────────────────
add_filter( 'manage_ds_creator_upload_posts_columns', 'ds_admin_upload_columns' );
function ds_admin_upload_columns( $cols ) {
    return [
        'cb'          => $cols['cb'],
        'thumbnail'   => 'Thumbnail',
        'title'       => 'File Title',
        'creator'     => 'Creator',
        'category'    => 'Category',
        'ds_status'   => 'Status',
        'ds_earnings' => 'Downloads / Earnings',
        'date'        => 'Date',
        'ds_actions'  => 'Actions',
    ];
}

add_action( 'manage_ds_creator_upload_posts_custom_column', 'ds_admin_upload_column_content', 10, 2 );
function ds_admin_upload_column_content( $col, $post_id ) {
    switch ( $col ) {

        case 'thumbnail':
    $thumb_id   = ds_get_preview_attachment_id( $post_id );
    $thumb_mime = $thumb_id ? get_post_mime_type( $thumb_id ) : '';
    if ( $thumb_id && strpos( $thumb_mime, 'video/' ) === 0 ) {
        $video_url = wp_get_attachment_url( $thumb_id );
        echo '<video src="' . esc_url( $video_url ) . '" style="width:60px;height:40px;object-fit:cover;border-radius:4px;background:#000" muted autoplay loop playsinline></video>';
    } else {
        // Full attachment URL + CSS sizing, instead of an arbitrary [60,40]
        // registered-size lookup that returns nothing for almost every image.
        $thumb_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';
        if ( $thumb_url ) {
            echo '<img src="' . esc_url( $thumb_url ) . '" style="width:60px;height:40px;object-fit:cover;border-radius:4px">';
        } else {
            echo '—';
        }
    }
    break;

        case 'creator':
            $post = get_post( $post_id );
            $user = get_user_by( 'id', $post->post_author );
            echo $user ? esc_html( $user->display_name . ' (' . $user->user_email . ')' ) : '—';
            break;

        case 'category':
            echo esc_html( get_post_meta( $post_id, 'ds_creator_category', true ) ?: '—' );
            break;

        case 'ds_status':
            $status = get_post_meta( $post_id, 'ds_creator_status', true ) ?: 'pending';
            $labels = [
                'pending'   => [ 'Pending Review', '#f59e0b' ],
                'published' => [ 'Published',      '#10b981' ],
                'rejected'  => [ 'Rejected',       '#ef4444' ],
                'draft'     => [ 'Draft',           '#94a3b8' ],
            ];
            $l = $labels[ $status ] ?? [ ucfirst($status), '#94a3b8' ];
            echo '<span style="background:' . $l[1] . '22;color:' . $l[1] . ';padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700">' . esc_html( $l[0] ) . '</span>';
            break;

        case 'ds_earnings':
            $dl   = (int) get_post_meta( $post_id, 'ds_creator_download_count', true );
            $earn = (float) get_post_meta( $post_id, 'ds_creator_total_earnings', true );
            echo esc_html( $dl . ' downloads / ₹' . number_format( $earn, 2 ) );
            break;

        case 'ds_actions':
            $status  = get_post_meta( $post_id, 'ds_creator_status', true ) ?: 'pending';
            $nonce   = wp_create_nonce( 'ds_creator_action_' . $post_id );
            $approve = esc_url( add_query_arg( [ 'ds_action' => 'approve', 'post_id' => $post_id, '_wpnonce' => $nonce ], admin_url( 'edit.php?post_type=ds_creator_upload' ) ) );
            $reject  = esc_url( add_query_arg( [ 'ds_action' => 'reject',  'post_id' => $post_id, '_wpnonce' => $nonce ], admin_url( 'edit.php?post_type=ds_creator_upload' ) ) );

            if ( $status === 'pending' ) {
                echo '<a href="#" class="button button-primary ds-approve-link" data-approve-url="' . $approve . '" style="margin-right:4px">✅ Approve</a>';
                echo '<a href="#" class="button ds-reject-link" data-reject-url="' . $reject . '">❌ Reject</a>';
            } elseif ( $status === 'published' ) {
                echo '<span style="color:#10b981">✅ Approved</span>';
                $dlm_id = (int) get_post_meta( $post_id, 'ds_creator_dlm_link', true );
                if ( $dlm_id ) echo ' <a href="' . esc_url( get_edit_post_link( $dlm_id ) ) . '" style="margin-left:6px">View DLM →</a>';
            } elseif ( $status === 'rejected' ) {
                echo '<span style="color:#ef4444">❌ Rejected</span>';
                echo ' <a href="#" class="button ds-approve-link" data-approve-url="' . $approve . '" style="margin-left:4px">Re-approve</a>';
            } else {
                echo '<span style="color:#94a3b8">Draft</span>';
                echo ' <a href="#" class="button ds-approve-link" data-approve-url="' . $approve . '" style="margin-left:4px">Approve</a>';
            }
            break;
    }
}

// ─────────────────────────────────────────────
// 2. HANDLE APPROVE / REJECT ACTIONS
// ─────────────────────────────────────────────
add_action( 'admin_init', 'ds_handle_creator_approval_action' );
// Hide published/approved uploads from admin list — they live in DLM now
add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() ) return;
    if ( $query->get('post_type') !== 'ds_creator_upload' ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    // Only show pending and rejected — not published/approved
    $meta_query = $query->get('meta_query') ?: [];
    $meta_query[] = [
        'relation' => 'OR',
        [
            'key'     => 'ds_creator_status',
            'value'   => ['pending', 'rejected', 'draft'],
            'compare' => 'IN',
        ],
        [
            'key'     => 'ds_creator_status',
            'compare' => 'NOT EXISTS',
        ],
    ];
    $query->set('meta_query', $meta_query);
});
function ds_handle_creator_approval_action() {
    if ( ! isset( $_GET['ds_action'], $_GET['post_id'], $_GET['_wpnonce'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed.' );

    $post_id = (int) $_GET['post_id'];
    $action  = sanitize_key( $_GET['ds_action'] );

    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ds_creator_action_' . $post_id ) ) {
        wp_die( 'Security check failed.' );
    }

    if ( $action === 'approve' ) {
    update_post_meta( $post_id, 'ds_creator_status', 'published' );
    ds_create_dlm_from_creator_upload( $post_id );
		// Save submitter meta on DLM post so Marketplace Approval box shows correctly
$dlm_id       = (int) get_post_meta( $post_id, 'ds_creator_dlm_link', true );
$creator_id   = (int) get_post( $post_id )->post_author;
// 3 credits for AI Video submissions, 1 credit for everything else — read
// from ds_creator_cashback_amount (set at submission time), default 1 for
// older submissions that never set that meta.
$cashback = (int) get_post_meta( $post_id, 'ds_creator_cashback_amount', true ) ?: 1;
if ( $dlm_id && $creator_id ) {
    update_post_meta( $dlm_id, 'ds_marketplace_submitter', $creator_id );
    update_post_meta( $dlm_id, 'ds_marketplace_cashback',  $cashback );
}

    // ── PAY CASHBACK CREDIT TO CREATOR ──────────────────────────────
    $creator_id = (int) get_post( $post_id )->post_author;
    if ( $creator_id && ! get_post_meta( $post_id, 'ds_marketplace_cashback_paid', true ) ) {
        $current_credits = (int) get_user_meta( $creator_id, 'ds_ai_credits', true );
update_user_meta( $creator_id, 'ds_ai_credits', $current_credits + $cashback );
        $transactions   = get_user_meta( $creator_id, 'ds_credit_transactions', true ) ?: [];
        $transactions[] = [
            'type'        => 'marketplace',
            'amount'      => $cashback,
            'label'       => '🎉 Marketplace Cashback',
            'description' => get_the_title( $post_id ),
            'date'        => current_time( 'Y-m-d' ),
            'timestamp'   => time(),
        ];
        update_user_meta( $creator_id, 'ds_credit_transactions', $transactions );
        update_post_meta( $post_id, 'ds_marketplace_cashback_paid', true );
		// Also mark on the DLM post so the DLM approval box shows "Already paid"
$dlm_id = (int) get_post_meta( $post_id, 'ds_creator_dlm_link', true );
if ( $dlm_id ) {
    update_post_meta( $dlm_id, 'ds_marketplace_cashback_paid', true );
    update_post_meta( $dlm_id, 'ds_marketplace_status', 'approved' );
}
        update_post_meta( $post_id, 'ds_marketplace_status', 'approved' );
        // Try both thumbnail and file IDs to find matching generation
$possible_ids = array_filter( [
    (int) get_post_meta( $post_id, 'ds_creator_thumbnail_id', true ),
    (int) get_post_meta( $post_id, 'ds_creator_file_id',      true ),
] );
if ( $possible_ids ) {
    $gens    = get_user_meta( $creator_id, 'ds_ai_generations', true ) ?: [];
    $changed = false;
    foreach ( $gens as $i => $gen ) {
        $gen_att_id = (int)( $gen['attachment_id'] ?? 0 );
        if ( in_array( $gen_att_id, $possible_ids, true ) ) {
            $gens[$i]['accepted']  = true;
            $gens[$i]['submitted'] = true;
            $changed = true;
        }
    }
    if ( $changed ) {
        update_user_meta( $creator_id, 'ds_ai_generations', $gens );
    }
}
    }
    // ────────────────────────────────────────────────────────────────

    ds_notify_creator( $post_id, 'approved' );
    wp_redirect( add_query_arg( [ 'ds_msg' => 'approved' ], admin_url( 'edit.php?post_type=ds_creator_upload' ) ) );
    exit;

    } elseif ( $action === 'reject' ) {
        update_post_meta( $post_id, 'ds_creator_status', 'rejected' );
$reason = isset( $_GET['reason'] ) ? sanitize_textarea_field( wp_unslash( $_GET['reason'] ) ) : '';
if ( $reason ) update_post_meta( $post_id, 'ds_creator_reject_reason', $reason );

        ds_notify_creator( $post_id, 'rejected', $reason );
        wp_redirect( add_query_arg( [ 'ds_msg' => 'rejected' ], admin_url( 'edit.php?post_type=ds_creator_upload' ) ) );
    }

    exit;
}

// ─────────────────────────────────────────────
// 3. ADMIN NOTICE AFTER ACTION
// ─────────────────────────────────────────────
add_action( 'admin_notices', 'ds_admin_action_notice' );
function ds_admin_action_notice() {
    if ( ! isset( $_GET['ds_msg'] ) ) return;
    $msg = sanitize_key( $_GET['ds_msg'] );
    if ( $msg === 'approved' ) {
        echo '<div class="notice notice-success is-dismissible"><p>✅ File approved and published to DLM.</p></div>';
    } elseif ( $msg === 'rejected' ) {
        echo '<div class="notice notice-warning is-dismissible"><p>❌ File rejected. Creator has been notified.</p></div>';
    }
}

// ─────────────────────────────────────────────
// 4. META BOX: FULL FILE DETAILS IN ADMIN
// ─────────────────────────────────────────────
add_action( 'add_meta_boxes', 'ds_add_creator_upload_meta_box' );
function ds_add_creator_upload_meta_box() {
    add_meta_box(
        'ds_creator_upload_details',
        'Creator Upload Details',
        'ds_render_creator_upload_meta_box',
        'ds_creator_upload',
        'normal',
        'high'
    );
}

function ds_render_creator_upload_meta_box( $post ) {
    $id          = $post->ID;
    $status      = get_post_meta( $id, 'ds_creator_status', true ) ?: 'pending';
    $category    = get_post_meta( $id, 'ds_creator_category', true );
    $short_desc  = get_post_meta( $id, 'ds_creator_short_description', true );
    $long_desc   = get_post_meta( $id, 'ds_creator_full_description', true );
    $keywords    = json_decode( get_post_meta( $id, 'ds_creator_keywords', true ) ?: '[]', true );
    $tags        = json_decode( get_post_meta( $id, 'ds_creator_tags', true ) ?: '[]', true );
    $file_att_id = (int) get_post_meta( $id, 'ds_creator_file_id', true );
    $thumb_id    = (int) get_post_meta( $id, 'ds_creator_thumbnail_id', true );
    $file_name   = get_post_meta( $id, 'ds_creator_file_name', true );
    $file_url    = $file_att_id ? wp_get_attachment_url( $file_att_id ) : '';
    $thumb_url   = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';
    $dlm_id      = (int) get_post_meta( $id, 'ds_creator_dlm_link', true );
    $downloads   = (int) get_post_meta( $id, 'ds_creator_download_count', true );
    $earnings    = (float) get_post_meta( $id, 'ds_creator_total_earnings', true );
    $creator     = get_user_by( 'id', $post->post_author );
    $nonce       = wp_create_nonce( 'ds_creator_action_' . $id );
    $approve_url = esc_url( add_query_arg( [ 'ds_action' => 'approve', 'post_id' => $id, '_wpnonce' => $nonce ], admin_url( 'edit.php?post_type=ds_creator_upload' ) ) );
    $reject_url  = esc_url( add_query_arg( [ 'ds_action' => 'reject',  'post_id' => $id, '_wpnonce' => $nonce ], admin_url( 'edit.php?post_type=ds_creator_upload' ) ) );
    ?>
    <table class="form-table" style="font-size:14px">
      <tr><th style="width:140px">Creator</th><td><?php echo $creator ? esc_html( $creator->display_name . ' — ' . $creator->user_email ) : '—'; ?></td></tr>
      <tr><th>Status</th><td><strong><?php echo esc_html( ucfirst( $status ) ); ?></strong></td></tr>
      <tr><th>Category</th><td><?php echo esc_html( $category ?: '—' ); ?></td></tr>
      <tr><th>Short Description</th><td><?php echo esc_html( $short_desc ?: '—' ); ?></td></tr>
      <tr><th>Full Description</th><td style="white-space:pre-wrap"><?php echo esc_html( $long_desc ?: '—' ); ?></td></tr>
      <tr><th>Keywords</th><td><?php echo esc_html( implode( ', ', $keywords ) ?: '—' ); ?></td></tr>
      <tr><th>Tags</th><td><?php echo esc_html( implode( ', ', $tags ) ?: '—' ); ?></td></tr>
      <tr>
        <th>Design File</th>
        <td>
          <?php if ( $file_url ): ?>
            <a href="<?php echo esc_url( $file_url ); ?>" target="_blank"><?php echo esc_html( $file_name ?: 'Download' ); ?></a>
          <?php else: echo 'No file'; endif; ?>
        </td>
      </tr>
      <tr>
        <th>Thumbnail</th>
        <td>
          <?php if ( $thumb_url ): ?>
            <img src="<?php echo esc_url( $thumb_url ); ?>" style="max-width:200px;border-radius:6px">
          <?php else: echo '—'; endif; ?>
        </td>
      </tr>
      <tr><th>Downloads</th><td><?php echo esc_html( $downloads ); ?></td></tr>
      <tr><th>Earnings (Creator)</th><td>₹<?php echo esc_html( number_format( $earnings, 2 ) ); ?></td></tr>
      <?php if ( $dlm_id ): ?>
      <tr><th>DLM Download</th><td><a href="<?php echo esc_url( get_edit_post_link( $dlm_id ) ); ?>" target="_blank">Edit DLM Post #<?php echo $dlm_id; ?></a></td></tr>
      <?php endif; ?>
    </table>

    <?php if ( $status === 'pending' || $status === 'rejected' ): ?>
    <div style="margin-top:16px;display:flex;gap:8px">
      <a href="<?php echo $approve_url; ?>" class="button button-primary button-large">✅ Approve &amp; Publish to DLM</a>
      <?php if ( $status === 'pending' ): ?>
      <a href="#" class="button button-large ds-reject-link" data-reject-url="<?php echo $reject_url; ?>"> Reject</a>
      <?php endif; ?>
    </div>
    <?php elseif ( $status === 'published' ): ?>
    <div style="margin-top:16px;color:#10b981;font-weight:700">✅ This file has been published to DLM.</div>
    <?php endif; ?>
    <?php
}

// ─────────────────────────────────────────────
// 5. EMAIL NOTIFICATION TO CREATOR
// ─────────────────────────────────────────────
function ds_notify_creator( $post_id, $action, $reason = '' ) {
$post = get_post( $post_id );
$creator = get_user_by( 'id', $post->post_author );
if ( ! $creator ) return;

$title = get_the_title( $post );
$site = get_bloginfo( 'name' );
$to = $creator->user_email;
$portal_url = home_url( '/creator-portal/' );

if ( $action === 'approved' ) {
$subject = "[{$site}] Your design has been approved! ";
$message = "Hi {$creator->display_name},\n\n"
. "Great news! Your design \"{$title}\" has been approved and is now live on the marketplace.\n\n"
. "Buyers can now discover and download your file.\n\n"
. "— The {$site} Team";
if ( function_exists( 'ds_notify_user' ) ) {
ds_notify_user( $creator->ID, 'approved', 'Product approved', "Your \"{$title}\" has been approved and is now live in the marketplace.", $portal_url );
}
} else {
$subject = "[{$site}] Your design submission was not approved";
$extra = $reason ? ("\n\nReviewer note: {$reason}\n") : '';
$message = "Hi {$creator->display_name},\n\n"
. "Unfortunately, your design \"{$title}\" was not approved at this time.{$extra}\n"
. "Please log in to your Creator Portal to review and re-submit with any changes.\n\n"
. "— The {$site} Team";
if ( function_exists( 'ds_notify_user' ) ) {
$notif_msg = $reason ? "Your \"{$title}\" was not approved. Reason: {$reason}" : "Your \"{$title}\" was not approved. Please review and resubmit.";
ds_notify_user( $creator->ID, 'rejected', 'Changes required', $notif_msg, $portal_url );
}
}

wp_mail( $to, $subject, $message );
}

// ─────────────────────────────────────────────
// 6. PENDING COUNT IN ADMIN MENU BUBBLE
// ─────────────────────────────────────────────
add_action( 'admin_menu', 'ds_admin_menu_pending_count' );
function ds_admin_menu_pending_count() {
    global $menu;
    $pending = get_posts( [
        'post_type'      => 'ds_creator_upload',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'ds_creator_status',
        'meta_value'     => 'pending',
        'fields'         => 'ids',
    ] );
    $count = count( $pending );
    if ( $count < 1 ) return;
    foreach ( $menu as &$item ) {
        if ( isset( $item[2] ) && $item[2] === 'edit.php?post_type=ds_creator_upload' ) {
            $item[0] .= ' <span class="awaiting-mod">' . $count . '</span>';
            break;
        }
    }
}

// ─────────────────────────────────────────────
// 7. MAKE STATUS COLUMN SORTABLE
// ─────────────────────────────────────────────
add_filter( 'manage_edit-ds_creator_upload_sortable_columns', function($c){
    $c['ds_status'] = 'ds_status'; return $c;
});
// ─────────────────────────────────────────────
// APPROVAL POPUP WITH EDITABLE FIELDS
// ─────────────────────────────────────────────
add_action( 'admin_footer', 'ds_approval_popup_html' );
function ds_approval_popup_html() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'ds_creator_upload' ) return;
    ?>
    <div id="ds-approve-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999999;align-items:center;justify-content:center;">
      <div style="background:#fff;border-radius:12px;padding:28px;max-width:920px;width:95%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.5);">
        <h2 style="margin:0 0 6px;font-size:20px">✅ Approve &amp; Edit Before Publishing</h2>
        <p style="color:#666;margin:0 0 20px;font-size:13px">Edit any fields before the DLM post is created. All changes save to the creator upload too.</p>

        <input type="hidden" id="ds-approve-post-id" value="">

        <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap">
        <div style="flex:1;min-width:300px">
        <table class="form-table" style="font-size:13px">
          <tr>
            <th style="width:160px;padding:8px 0"><label for="ds-ap-title">Title</label></th>
            <td style="padding:8px 0"><input type="text" id="ds-ap-title" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px"></td>
          </tr>
          <tr>
            <th style="padding:8px 0"><label for="ds-ap-cat">Category</label></th>
<td style="padding:8px 0">
<select id="ds-ap-cat" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;background:#fff;">
<option value="">— Select category —</option>
<?php
$ds_category_tree = ds_get_category_tree();
foreach ($ds_category_tree as $cat_id => $cat) {
    echo '<option value="' . esc_attr($cat_id) . '">' . esc_html($cat['name']) . '</option>';
}
?>
</select>
</td>
          </tr>
          <tr>
            <th style="padding:8px 0"><label for="ds-ap-subcat">Subcategory</label></th>
<td style="padding:8px 0">
<select id="ds-ap-subcat" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;background:#fff;">
<option value="">— Select category first —</option>
</select>
</td>
          </tr>
          <tr>
            <th style="padding:8px 0"><label for="ds-ap-short">Short Description</label></th>
            <td style="padding:8px 0"><input type="text" id="ds-ap-short" maxlength="150" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px"></td>
          </tr>
          <tr>
            <th style="padding:8px 0"><label for="ds-ap-meta">Meta Description</label></th>
            <td style="padding:8px 0"><textarea id="ds-ap-meta" rows="2" maxlength="160" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;resize:vertical"></textarea></td>
          </tr>
          <tr>
            <th style="padding:8px 0"><label for="ds-ap-long">Full Description</label></th>
            <td style="padding:8px 0"><textarea id="ds-ap-long" rows="4" maxlength="2000" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;resize:vertical"></textarea></td>
          </tr>
          <tr>
            <th style="padding:8px 0"><label for="ds-ap-kw">Keywords</label></th>
            <td style="padding:8px 0">
              <input type="text" id="ds-ap-kw" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px" placeholder="Comma separated: nature, green, plant">
              <p style="color:#999;font-size:11px;margin:4px 0 0">Separate with commas</p>
            </td>
          </tr>
          <tr>
            <tr>
            <th style="padding:8px 0"><label for="ds-ap-tags">Tags</label></th>
            <td style="padding:8px 0">
              <input type="text" id="ds-ap-tags" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px" placeholder="Comma separated: #template, #free">
              <p style="color:#999;font-size:11px;margin:4px 0 0">Separate with commas</p>
            </td>
          </tr>
        </table>

        <label style="display:block;margin-top:14px;font-weight:600;">
          <input type="checkbox" id="ds-ap-show-template" style="width:18px;height:18px;margin-right:6px;vertical-align:middle;" />
          Show this template in AI Studio's "Pick a Design" matches
        </label>

        <label for="ds-ap-shape" style="font-weight:600;display:block;margin:12px 0 4px;">Content shape</label>
        <input type="text" id="ds-ap-shape" list="ds-ap-shape-options" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px" placeholder="e.g. steps_4, timeline, swot, pros_cons, header" />
        <datalist id="ds-ap-shape-options">
          <option value="header"></option>
          <option value="swot"></option>
          <option value="pros_cons"></option>
          <option value="pestle"></option>
          <option value="business_model_canvas"></option>
          <option value="steps_1"></option>
          <option value="steps_2"></option>
          <option value="steps_3"></option>
          <option value="steps_4"></option>
          <option value="steps_5"></option>
          <option value="steps_6"></option>
          <option value="steps_7"></option>
          <option value="steps_8"></option>
          <option value="steps_9"></option>
          <option value="steps_10"></option>
          <option value="steps_11"></option>
          <option value="steps_12"></option>
          <option value="steps_13"></option>
          <option value="steps_14"></option>
          <option value="timeline"></option>
          <option value="generic_grid_1"></option>
          <option value="generic_grid_2"></option>
          <option value="generic_grid_3"></option>
          <option value="generic_grid_4"></option>
          <option value="generic_grid_5"></option>
          <option value="generic_grid_6"></option>
          <option value="generic_grid_7"></option>
          <option value="generic_grid_8"></option>
          <option value="generic_grid_9"></option>
          <option value="generic_grid_10"></option>
          <option value="generic_grid_11"></option>
          <option value="generic_grid_12"></option>
          <option value="generic_grid_13"></option>
          <option value="generic_grid_14"></option>
        </datalist>

        <label style="display:block;margin:10px 0 4px;">
          <input type="checkbox" id="ds-ap-semantic" style="width:18px;height:18px;margin-right:6px;vertical-align:middle;" />
          Semantic (fixed meaningful labels baked in — e.g. STRENGTHS / WEAKNESSES)
        </label>

        <label for="ds-ap-template-kw" style="font-weight:600;display:block;margin:10px 0 4px;">Notes / keywords (optional, for your own reference)</label>
        <input type="text" id="ds-ap-template-kw" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px" placeholder="strength, weakness, opportunity, threat" />

        <p style="color:#777;font-size:11px;line-height:1.5;margin-top:10px;">
          The .pptx file's fillable text boxes must use the exact labels:
          <code>Title Header</code>, <code>Sub Header</code>, <code>Explanation</code>,
          <code>Point 1 Heading</code>, <code>Point 1 Explanation</code>, <code>Point 2 Heading</code>… —
          matching is case-insensitive. For shape <code>header</code> (cover/title-slide templates), use
          <code>Presentation Title</code>, <code>Subtitle</code>, <code>Presenter Name</code>,
          <code>Job Title / Designation</code>, <code>Company Name</code>, <code>Date</code>,
          <code>Website</code>, <code>Email</code> instead.
        </p>
        </div>

        <div style="flex:0 0 260px;min-width:220px">
          <p style="margin:0 0 8px;font-weight:600;font-size:13px;color:#444">Uploaded Image</p>
          <img id="ds-ap-preview-img" src="" alt="Preview" style="width:100%;border-radius:8px;border:1px solid #ddd;display:block;object-fit:contain;max-height:480px;background:#f5f5f5;">
          <video id="ds-ap-preview-video" src="" style="width:100%;border-radius:8px;border:1px solid #ddd;display:none;object-fit:contain;max-height:480px;background:#000;" muted autoplay loop playsinline controls></video>
          <audio id="ds-ap-preview-audio" src="" style="width:100%;display:none;margin-top:4px;" controls preload="none"></audio>
        </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;flex-wrap:wrap">
          <button onclick="dsCloseApprove()" style="padding:10px 20px;border-radius:8px;border:1px solid #ddd;background:#f5f5f5;cursor:pointer;font-weight:600;font-size:13px">Cancel</button>
          <button onclick="dsSubmitApprove()" id="ds-ap-submit-btn" style="padding:10px 24px;border-radius:8px;border:none;background:#10b981;color:#fff;cursor:pointer;font-weight:700;font-size:13px">✅ Approve &amp; Publish to DLM</button>
        </div>
      </div>
    </div>

    <script>
    // Real category/subcategory data, shared with the Admin-Ajax snippet's AI fill-in logic.
    window.dsCategoryTree = <?php echo wp_json_encode($ds_category_tree); ?>;

    function dsPopulateSubcategories(selectedCatId, preselectSubId) {
        var subSelect = document.getElementById('ds-ap-subcat');
        if (!subSelect) return;
        var tree = window.dsCategoryTree || {};
        var subs = (tree[selectedCatId] && tree[selectedCatId].subs) ? tree[selectedCatId].subs : {};

        subSelect.innerHTML = '';
        var keys = Object.keys(subs);
        if (!keys.length) {
            subSelect.innerHTML = '<option value="">— No subcategories for this category —</option>';
            return;
        }
        subSelect.innerHTML = '<option value="">— Select subcategory —</option>';
        keys.forEach(function(subId) {
            var opt = document.createElement('option');
            opt.value = subId;
            opt.textContent = subs[subId];
            subSelect.appendChild(opt);
        });
        if (preselectSubId) subSelect.value = preselectSubId;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var catSelect = document.getElementById('ds-ap-cat');
        if (catSelect) {
            catSelect.addEventListener('change', function() {
                dsPopulateSubcategories(catSelect.value, null);
            });
        }
    });

    // Single delegated listener — catches every Approve/Re-approve link on the page,
    // including any added after page load. There is no working href to fall back to,
    // so a missed bind can no longer cause a silent direct-publish.
    document.addEventListener('click', function(e) {
        var link = e.target.closest('.ds-approve-link');
        if (!link) return;
        e.preventDefault();
        var href = link.getAttribute('data-approve-url');
        var match = href.match(/post_id=(\d+)/);
        if (!match) return;
        var postId = match[1];
        dsOpenApprove(postId, href);
    });

document.addEventListener('click', function(e) {
var rlink = e.target.closest('.ds-reject-link');
if (!rlink) return;
e.preventDefault();
var reason = prompt('Reason for rejecting this file (shown to the creator, optional):', '');
if (reason === null) return;
var rurl = rlink.getAttribute('data-reject-url');
rurl += (rurl.indexOf('?') > -1 ? '&' : '?') + 'reason=' + encodeURIComponent(reason);
window.location.href = rurl;
});


    function dsOpenApprove(postId, originalHref) {
        document.getElementById('ds-approve-post-id').value = postId;
        // Store original href for fallback
        document.getElementById('ds-ap-submit-btn').setAttribute('data-href', originalHref);

        // Load existing meta via AJAX
        fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=ds_get_upload_meta&post_id=' + postId + '&nonce=<?php echo wp_create_nonce("ds_admin_nonce"); ?>'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.getElementById('ds-ap-title').value  = d.data.title       || '';
                var prevImg = document.getElementById('ds-ap-preview-img');
                var prevVid = document.getElementById('ds-ap-preview-video');
                var prevAud = document.getElementById('ds-ap-preview-audio');
                if (d.data.isAudio) {
                    prevImg.style.display = 'none';
                    prevImg.src = '';
                    prevVid.style.display = 'none';
                    prevVid.src = '';
                    prevAud.src = d.data.imageUrl || '';
                    prevAud.style.display = 'block';
                } else if (d.data.isVideo) {
                    prevImg.style.display = 'none';
                    prevImg.src = '';
                    prevAud.style.display = 'none';
                    prevAud.src = '';
                    prevVid.src = d.data.imageUrl || '';
                    prevVid.style.display = 'block';
                } else {
                    prevVid.style.display = 'none';
                    prevVid.src = '';
                    prevAud.style.display = 'none';
                    prevAud.src = '';
                    prevImg.src = d.data.imageUrl || '';
                    prevImg.style.display = 'block';
                }
                document.getElementById('ds-ap-cat').value    = d.data.categoryId  || '';
                dsPopulateSubcategories(d.data.categoryId || '', d.data.subcategoryId || '');
                document.getElementById('ds-ap-short').value  = d.data.shortDesc   || '';
                document.getElementById('ds-ap-meta').value   = d.data.metaDesc    || '';
                document.getElementById('ds-ap-long').value   = d.data.longDesc    || '';
                document.getElementById('ds-ap-kw').value     = d.data.keywords    || '';
                document.getElementById('ds-ap-tags').value   = d.data.tags        || '';
                document.getElementById('ds-ap-shape').value       = d.data.contentShape     || '';
                document.getElementById('ds-ap-semantic').checked  = !!d.data.semantic;
                document.getElementById('ds-ap-template-kw').value = d.data.templateKeywords || '';
                document.getElementById('ds-ap-show-template').checked = !!d.data.showInPicker;
            }
        });

        var overlay = document.getElementById('ds-approve-overlay');
        overlay.style.display = 'flex';
    }

    function dsCloseApprove() {
        document.getElementById('ds-approve-overlay').style.display = 'none';
    }

    function dsSubmitApprove() {
        var postId  = document.getElementById('ds-approve-post-id').value;
        var btn     = document.getElementById('ds-ap-submit-btn');
        btn.textContent = 'Saving…';
        btn.disabled    = true;

        var data = new URLSearchParams();
        data.append('action',        'ds_save_and_approve');
        data.append('nonce',         '<?php echo wp_create_nonce("ds_admin_nonce"); ?>');
        data.append('post_id',       postId);
        data.append('title',         document.getElementById('ds-ap-title').value);
        data.append('category_id',    document.getElementById('ds-ap-cat').value);
        data.append('subcategory_id', document.getElementById('ds-ap-subcat').value);
        data.append('shortDesc',     document.getElementById('ds-ap-short').value);
        data.append('metaDesc',      document.getElementById('ds-ap-meta').value);
        data.append('longDesc',      document.getElementById('ds-ap-long').value);
        data.append('keywords',      document.getElementById('ds-ap-kw').value);
        data.append('tags',          document.getElementById('ds-ap-tags').value);
        data.append('content_shape',     document.getElementById('ds-ap-shape').value);
        data.append('semantic',          document.getElementById('ds-ap-semantic').checked ? '1' : '0');
        data.append('template_keywords', document.getElementById('ds-ap-template-kw').value);
        data.append('show_in_picker',    document.getElementById('ds-ap-show-template').checked ? '1' : '0');

        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                dsCloseApprove();
                // Redirect to the original approve URL which handles DLM creation
                window.location.href = btn.getAttribute('data-href');
            } else {
                alert('Error: ' + (d.data || 'Could not save'));
                btn.textContent = '✅ Approve & Publish to DLM';
                btn.disabled    = false;
            }
        })
        .catch(function() {
            alert('Network error. Please try again.');
            btn.textContent = '✅ Approve & Publish to DLM';
            btn.disabled    = false;
        });
    }

    // Close on overlay click
    document.getElementById('ds-approve-overlay').addEventListener('click', function(e) {
        if (e.target === this) dsCloseApprove();
    });
    </script>
    <?php
}

// ─────────────────────────────────────────────
// AJAX: GET UPLOAD META FOR POPUP
// ─────────────────────────────────────────────
add_action( 'wp_ajax_ds_get_upload_meta', 'ds_ajax_get_upload_meta' );
function ds_ajax_get_upload_meta() {
    check_ajax_referer( 'ds_admin_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Not allowed' );

    $post_id  = (int) $_POST['post_id'];
    $keywords = json_decode( get_post_meta( $post_id, 'ds_creator_keywords', true ) ?: '[]', true );
    $tags     = json_decode( get_post_meta( $post_id, 'ds_creator_tags',     true ) ?: '[]', true );

    $image_url = '';
    $is_video  = false;
    $is_audio  = false;
    $thumb_id  = (int) get_post_meta( $post_id, 'ds_creator_thumbnail_id', true );
    if ( $thumb_id ) {
        $thumb_mime = get_post_mime_type( $thumb_id );
        if ( strpos( $thumb_mime, 'video/' ) === 0 ) {
            $image_url = wp_get_attachment_url( $thumb_id ); // raw file URL — videos have no "image size"
            $is_video  = true;
        } elseif ( strpos( $thumb_mime, 'audio/' ) === 0 ) {
            $image_url = wp_get_attachment_url( $thumb_id ); // raw file URL — audio has no "image size" either
            $is_audio  = true;
        } else {
            $image_url = wp_get_attachment_image_url( $thumb_id, 'full' );
        }
    }
    if ( ! $image_url ) {
        $file_id = (int) get_post_meta( $post_id, 'ds_creator_file_id', true );
        if ( $file_id ) $image_url = wp_get_attachment_image_url( $file_id, 'full' );
    }

    wp_send_json_success( [
        'title'         => get_the_title( $post_id ),
        'isVideo'       => $is_video,
        'isAudio'       => $is_audio,
        'imageUrl'      => $image_url ?: '',
        'categoryId'    => (int) get_post_meta( $post_id, 'ds_creator_category_id',    true ),
        'subcategoryId' => (int) get_post_meta( $post_id, 'ds_creator_subcategory_id', true ),
        'shortDesc'     => get_post_meta( $post_id, 'ds_creator_short_description',  true ),
        'metaDesc'      => get_post_meta( $post_id, 'ds_creator_meta_description',   true ),
        'longDesc'      => get_post_meta( $post_id, 'ds_creator_full_description',   true ),
        'keywords'      => is_array( $keywords ) ? implode( ', ', $keywords ) : '',
        'tags'          => is_array( $tags )     ? implode( ', ', $tags )     : '',
        'contentShape'     => get_post_meta( $post_id, 'ds_creator_content_shape',     true ),
        'semantic'         => (bool) get_post_meta( $post_id, 'ds_creator_semantic',   true ),
        'templateKeywords' => get_post_meta( $post_id, 'ds_creator_template_keywords', true ),
        'showInPicker'     => (bool) get_post_meta( $post_id, 'ds_creator_show_in_picker', true ),
    ] );
}

// ─────────────────────────────────────────────
// AJAX: SAVE EDITED FIELDS THEN APPROVE
// ─────────────────────────────────────────────
add_action( 'wp_ajax_ds_save_and_approve', 'ds_ajax_save_and_approve' );
function ds_ajax_save_and_approve() {
    check_ajax_referer( 'ds_admin_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Not allowed' );

    $post_id = (int) $_POST['post_id'];

    // Update post title
    $title = sanitize_text_field( $_POST['title'] ?? '' );
    if ( $title ) {
        wp_update_post( [ 'ID' => $post_id, 'post_title' => $title ] );
    }

    // Update all meta
    $keywords = array_map( 'trim', explode( ',', sanitize_text_field( $_POST['keywords'] ?? '' ) ) );
    $keywords = array_filter( $keywords );
    $tags     = array_map( 'trim', explode( ',', sanitize_text_field( $_POST['tags'] ?? '' ) ) );
    $tags     = array_filter( $tags );

    $category_id    = (int) ( $_POST['category_id']    ?? 0 );
    $subcategory_id = (int) ( $_POST['subcategory_id'] ?? 0 );
    $category_name    = $category_id    ? get_term( $category_id,    'dlm_download_category' ) : null;
    $subcategory_name = $subcategory_id ? get_term( $subcategory_id, 'dlm_download_category' ) : null;

    update_post_meta( $post_id, 'ds_creator_category_id',        $category_id );
    update_post_meta( $post_id, 'ds_creator_subcategory_id',     $subcategory_id );
    update_post_meta( $post_id, 'ds_creator_category',           ( $category_name    && ! is_wp_error( $category_name ) )    ? $category_name->name    : '' );
    update_post_meta( $post_id, 'ds_creator_subcategory',        ( $subcategory_name && ! is_wp_error( $subcategory_name ) ) ? $subcategory_name->name : '' );
    update_post_meta( $post_id, 'ds_creator_short_description', sanitize_text_field( $_POST['shortDesc'] ?? '' ) );
    update_post_meta( $post_id, 'ds_creator_meta_description',  sanitize_textarea_field( $_POST['metaDesc']  ?? '' ) );
    update_post_meta( $post_id, 'ds_creator_full_description',  sanitize_textarea_field( $_POST['longDesc']  ?? '' ) );
    update_post_meta( $post_id, 'ds_creator_keywords',          wp_json_encode( array_values( $keywords ) ) );
    update_post_meta( $post_id, 'ds_creator_tags',              wp_json_encode( array_values( $tags ) ) );
    update_post_meta( $post_id, 'ds_creator_content_shape',     sanitize_text_field( $_POST['content_shape']     ?? '' ) );
    update_post_meta( $post_id, 'ds_creator_semantic',          ( $_POST['semantic']       ?? '0' ) === '1' ? '1' : '' );
    update_post_meta( $post_id, 'ds_creator_template_keywords', sanitize_text_field( $_POST['template_keywords'] ?? '' ) );
    update_post_meta( $post_id, 'ds_creator_show_in_picker',    ( $_POST['show_in_picker'] ?? '0' ) === '1' ? '1' : '' );

    if ( $dlm_id && ( $meta_desc || ! empty( $kws ) ) ) {
    global $wpdb;
    $aioseo_table = $wpdb->prefix . 'aioseo_posts';
    $focus_kw = ! empty( $kws ) ? array_values( $kws )[0] : '';
    if ( $wpdb->get_var("SHOW TABLES LIKE '$aioseo_table'") === $aioseo_table ) {
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $aioseo_table WHERE post_id = %d", $dlm_id
        ));
        if ( $existing ) {
            $wpdb->update(
                $aioseo_table,
                [
                    'description' => $meta_desc,
                    'keyphrases'  => $focus_kw ? wp_json_encode(['focus'=>['keyphrase'=>$focus_kw,'score'=>0,'analysis'=>[]]]) : null,
                    'updated'     => current_time('mysql'),
                ],
                [ 'post_id' => $dlm_id ],
                [ '%s', '%s', '%s' ],
                [ '%d' ]
            );
        } else {
            $wpdb->insert(
                $aioseo_table,
                [
                    'post_id'     => $dlm_id,
                    'description' => $meta_desc,
                    'keyphrases'  => $focus_kw ? wp_json_encode(['focus'=>['keyphrase'=>$focus_kw,'score'=>0,'analysis'=>[]]]) : null,
                    'created'     => current_time('mysql'),
                    'updated'     => current_time('mysql'),
                ],
                [ '%d', '%s', '%s', '%s', '%s' ]
            );
        }
    }
}

// ===== ADD CREDIT BACK TO SUBMITTER =====
    $submitter_id = (int) get_post( $post_id )->post_author;
    if ( $submitter_id ) {
        // Prevent duplicate credit on re-approve
        $already_paid = get_post_meta( $post_id, 'ds_marketplace_cashback_paid', true );
        if ( ! $already_paid ) {
            // Prefer the amount recorded at submission time (e.g. 3 for AI Video,
            // via ds_creator_cashback_amount) so this Save & Approve path pays the
            // same amount as the other approval flows, instead of silently
            // defaulting to 1 when ds_marketplace_cashback was never set here.
            $cashback = (int) get_post_meta( $post_id, 'ds_creator_cashback_amount', true );
            if ( ! $cashback ) {
                $linked_upload_id = (int) get_post_meta( $post_id, 'ds_creator_upload_id', true );
                if ( $linked_upload_id ) {
                    $cashback = (int) get_post_meta( $linked_upload_id, 'ds_creator_cashback_amount', true );
                }
            }
            if ( ! $cashback ) {
                $cashback = (int) get_post_meta( $post_id, 'ds_marketplace_cashback', true ) ?: 1;
            }
            $product_title = sanitize_text_field( $_POST['title'] ?? '' ) ?: get_the_title( $post_id ) ?: 'Marketplace Approval';

            // Add credits
            if ( function_exists('ds_add_creditback_credits') ) {
    ds_add_creditback_credits( $submitter_id, $cashback );
} elseif ( function_exists('ds_add_credits') ) {
    ds_add_credits( $submitter_id, $cashback );
            } else {
                $current = (int) get_user_meta( $submitter_id, 'ds_ai_credits', true );
                update_user_meta( $submitter_id, 'ds_ai_credits', $current + $cashback );
            }

            // Log transaction
            $transactions   = get_user_meta( $submitter_id, 'ds_credit_transactions', true ) ?: [];
            $transactions[] = [
                'type'          => 'marketplace',
                'amount'        => $cashback,
                'product_name'  => $product_title,
                'approved_date' => current_time( 'Y-m-d' ),
                'approved_time' => current_time( 'H:i' ),
                'label'         => '🎉 Marketplace Cashback',
                'description'   => $product_title,
                'date'          => current_time( 'Y-m-d' ),
                'timestamp'     => time(),
            ];
            update_user_meta( $submitter_id, 'ds_credit_transactions', $transactions );

            // Mark paid to prevent duplicates
            update_post_meta( $post_id, 'ds_marketplace_cashback_paid', true );
        }
    }

    wp_send_json_success( 'Saved' );
}
// =====================================================
// DS ADMIN PAGES: Policy Documents + Send Notification
// =====================================================
add_action( 'admin_menu', 'ds_register_extra_admin_pages' );
function ds_register_extra_admin_pages() {
	add_submenu_page(
		'edit.php?post_type=ds_creator_upload',
		'Policy Documents',
		'Policy Documents',
		'manage_options',
		'ds-policy-documents',
		'ds_render_policy_documents_page'
	);
	add_submenu_page(
		'edit.php?post_type=ds_creator_upload',
		'Send Notification',
		'Send Notification',
		'manage_options',
		'ds-send-notification',
		'ds_render_send_notification_page'
	);
}

if ( ! function_exists( 'ds_admin_policy_doc_keys' ) ) {
function ds_admin_policy_doc_keys() {
	return [
		'creator_terms'     => 'Creator Terms',
		'marketplace_terms' => 'Marketplace Terms',
		'content_license'   => 'Content License',
		'copyright_policy'  => 'Copyright Policy',
		'payment_revenue'   => 'Payment & Revenue Policy',
		'ai_content_policy' => 'AI Content Policy',
	];
}
}

function ds_render_policy_documents_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed.' );
	if ( isset( $_POST['ds_policy_nonce'] ) && wp_verify_nonce( $_POST['ds_policy_nonce'], 'ds_policy_docs' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		foreach ( ds_admin_policy_doc_keys() as $key => $label ) {
			$field = 'ds_policy_file_' . $key;
			if ( ! empty( $_FILES[ $field ]['name'] ) ) {
				$att_id = media_handle_upload( $field, 0 );
				if ( ! is_wp_error( $att_id ) ) {
					update_option( 'ds_policy_doc_' . $key, $att_id );
				}
			}
			if ( ! empty( $_POST[ 'ds_policy_remove_' . $key ] ) ) {
				delete_option( 'ds_policy_doc_' . $key );
			}
		}
		echo '<div class="notice notice-success"><p>Saved.</p></div>';
	}
	?>
	<div class="wrap">
		<h1>Policy Documents</h1>
		<p>Upload the file for each policy below. The Creator Portal's Creator Agreement page links directly to whichever file is uploaded here — leave any of them blank to show "Not yet available" on the portal.</p>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'ds_policy_docs', 'ds_policy_nonce' ); ?>
			<table class="form-table">
			<?php foreach ( ds_admin_policy_doc_keys() as $key => $label ):
				$att_id = (int) get_option( 'ds_policy_doc_' . $key );
				$url = $att_id ? wp_get_attachment_url( $att_id ) : '';
			?>
				<tr>
					<th style="width:220px"><?php echo esc_html( $label ); ?></th>
					<td>
						<?php if ( $url ): ?>
							<p style="margin:0 0 6px;"><a href="<?php echo esc_url( $url ); ?>" target="_blank">Current file ↗</a>
							&nbsp; <label style="font-weight:normal;"><input type="checkbox" name="ds_policy_remove_<?php echo esc_attr( $key ); ?>" value="1"> Remove</label></p>
						<?php else: ?>
							<p style="margin:0 0 6px;color:#a00;">Not uploaded yet.</p>
						<?php endif; ?>
						<input type="file" name="ds_policy_file_<?php echo esc_attr( $key ); ?>">
					</td>
				</tr>
			<?php endforeach; ?>
			</table>
			<?php submit_button( 'Save Policy Documents' ); ?>
		</form>
	</div>
	<?php
}
function ds_render_send_notification_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Not allowed.' );
	$sent = false; $count = 0;
	if ( isset( $_POST['ds_notif_nonce'] ) && wp_verify_nonce( $_POST['ds_notif_nonce'], 'ds_send_notification' ) ) {
		$title = sanitize_text_field( $_POST['ds_notif_title'] ?? '' );
		$message = sanitize_textarea_field( $_POST['ds_notif_message'] ?? '' );
		$audience = sanitize_key( $_POST['ds_notif_audience'] ?? 'all' );
		$link = ! empty( $_POST['ds_notif_link'] ) ? esc_url_raw( $_POST['ds_notif_link'] ) : '';
		if ( $title && $message && function_exists( 'ds_notify_user' ) ) {
			$args = [ 'fields' => 'ID' ];
			if ( $audience === 'creators' ) $args['role'] = 'ds_creator';
			$user_ids = get_users( $args );
			foreach ( $user_ids as $uid ) {
				ds_notify_user( $uid, 'broadcast', $title, $message, $link );
			}
			$count = count( $user_ids );
			$sent = true;
		}
	}
	?>
	<div class="wrap">
		<h1>Send Notification</h1>
		<p>Send a real notification straight to creators' Notifications tab in the Creator Portal (product announcements, contests, policy changes, etc).</p>
		<?php if ( $sent ): ?>
			<div class="notice notice-success"><p>Sent to <?php echo (int) $count; ?> user(s).</p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'ds_send_notification', 'ds_notif_nonce' ); ?>
			<table class="form-table">
				<tr><th><label for="ds_notif_title">Title</label></th><td><input type="text" id="ds_notif_title" name="ds_notif_title" class="regular-text" required></td></tr>
				<tr><th><label for="ds_notif_message">Message</label></th><td><textarea id="ds_notif_message" name="ds_notif_message" rows="4" class="large-text" required></textarea></td></tr>
				<tr><th><label for="ds_notif_link">Link (optional)</label></th><td><input type="url" id="ds_notif_link" name="ds_notif_link" class="regular-text" placeholder="https://designstager.com/..."></td></tr>
				<tr><th>Audience</th><td>
					<label><input type="radio" name="ds_notif_audience" value="all" checked> All Users</label><br>
					<label><input type="radio" name="ds_notif_audience" value="creators"> Creators Only</label>
				</td></tr>
			</table>
			<?php submit_button( 'Send Notification' ); ?>
		</form>
	</div>
	<?php
}
