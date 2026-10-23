<?php
/**
 * Code Snippets export — Snippet 5 earnings
 *
 * Snippet ID : 713
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-027
 * Modified   : 2026-09-09 02:40:54
 * Load order : 027 of 144
 */
/**
 * DS Creator Portal — Snippet 5: Earnings Tracking
 * WHERE TO ADD: Paste this into functions.php AFTER Snippets 1–4.
 *
 * WHAT THIS DOES:
 *  - Hooks into WooCommerce order completion to log paid sales
 *  - Hooks into DLM's download event (free downloads already in Snippet 4)
 *  - Adds a "Creator Earnings" report page in WP admin
 *  - Provides a filter 'ds_download_sale_price' so you can plug in the
 *    real price from WooCommerce or any other payment gateway
 *  - Adds an admin widget: Platform earnings summary (all creators)
 *
 * HOW PAID DOWNLOADS WORK WITH DLM:
 *  If you use DLM + WooCommerce to sell files, the flow is:
 *  1. Customer buys via WooCommerce → order completes
 *  2. WooCommerce triggers 'woocommerce_order_status_completed'
 *  3. We look up which DLM download was purchased
 *  4. We log the earnings split (70/30) for the creator
 */

// ─────────────────────────────────────────────
// 1. WOOCOMMERCE INTEGRATION
//    Logs earnings when a paid order completes
// ─────────────────────────────────────────────
add_action( 'woocommerce_order_status_completed', 'ds_log_woo_sale_earnings', 10, 1 );
function ds_log_woo_sale_earnings( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    foreach ( $order->get_items() as $item ) {
        // Get the product
        $product_id = $item->get_product_id();

        // Check if this product is linked to a DLM download
        // DLM stores the download ID in product meta '_dlm_download_id'
        $dlm_post_id = (int) get_post_meta( $product_id, '_dlm_download_id', true );
        if ( ! $dlm_post_id ) {
            // Try the reverse: product linked via DLM meta
            $dlm_post_id = (int) get_post_meta( $product_id, 'dlm_download_id', true );
        }
        if ( ! $dlm_post_id ) continue;

        $creator_upload_id = (int) get_post_meta( $dlm_post_id, 'ds_creator_upload_id', true );
        if ( ! $creator_upload_id ) continue;

        $creator_id   = (int) get_post_meta( $dlm_post_id, 'ds_creator_id', true );
        if ( ! $creator_id ) continue;

        $sale_price   = (float) $item->get_total(); // actual paid amount
        $creator_cut  = round( $sale_price * 0.70, 2 );
        $platform_cut = round( $sale_price * 0.30, 2 );

        // Avoid double-logging if hook fires twice
        $already_logged = get_post_meta( $creator_upload_id, '_ds_order_' . $order_id, true );
        if ( $already_logged ) continue;
        update_post_meta( $creator_upload_id, '_ds_order_' . $order_id, 1 );

        // Log to earnings table
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'ds_creator_earnings',
            [
                'file_id'      => $creator_upload_id,
                'creator_id'   => $creator_id,
                'download_id'  => $dlm_post_id,
                'amount'       => $creator_cut,
                'platform_cut' => $platform_cut,
                'sale_price'   => $sale_price,
                'created_at'   => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%d', '%f', '%f', '%f', '%s' ]
        );

        // Update creator upload totals
        $old_earn    = (float) get_post_meta( $creator_upload_id, 'ds_creator_total_earnings', true );
        $old_revenue = (float) get_post_meta( $creator_upload_id, 'ds_creator_total_revenue',  true );
        $old_count   = (int)   get_post_meta( $creator_upload_id, 'ds_creator_download_count',  true );
        update_post_meta( $creator_upload_id, 'ds_creator_total_earnings', $old_earn    + $creator_cut );
        update_post_meta( $creator_upload_id, 'ds_creator_total_revenue',  $old_revenue + $sale_price  );
        update_post_meta( $creator_upload_id, 'ds_creator_download_count', $old_count   + 1 );

        // Update user-level totals
        $u_earn = (float) get_user_meta( $creator_id, 'ds_creator_total_earnings',  true );
        $u_dl   = (int)   get_user_meta( $creator_id, 'ds_creator_total_downloads', true );
        update_user_meta( $creator_id, 'ds_creator_total_earnings',  $u_earn + $creator_cut );
        update_user_meta( $creator_id, 'ds_creator_total_downloads', $u_dl + 1 );
    }
}

// ─────────────────────────────────────────────
// 2. FILTER: PROVIDE SALE PRICE TO DLM FREE DOWNLOAD HOOK
//    If you want to track a "price" for free downloads, hook here
//    Default: 0 (free). Override if you add paid downloads later.
// ─────────────────────────────────────────────
add_filter( 'ds_download_sale_price', function( $price, $dlm_post_id ) {
    // Example: get price from a WooCommerce product linked to this DLM post
    // $product_id = get_post_meta($dlm_post_id, '_wc_product_id', true);
    // if($product_id){ $product = wc_get_product($product_id); return $product ? $product->get_price() : 0; }
    return $price; // Default: 0
}, 10, 2 );

// ─────────────────────────────────────────────
// 3. ADMIN EARNINGS REPORT PAGE
// ─────────────────────────────────────────────
add_action( 'admin_menu', 'ds_add_earnings_report_menu' );
function ds_add_earnings_report_menu() {
    add_submenu_page(
        'edit.php?post_type=ds_creator_upload',
        'Earnings Report',
        '💰 Earnings Report',
        'manage_options',
        'ds-earnings-report',
        'ds_render_earnings_report_page'
    );
}

function ds_render_earnings_report_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ds_creator_earnings';

    // Summary by creator
    $by_creator = $wpdb->get_results(
        "SELECT e.creator_id, SUM(e.amount) AS total_earned, SUM(e.platform_cut) AS platform_total,
                SUM(e.sale_price) AS gross_revenue, COUNT(e.id) AS total_downloads,
                u.display_name, u.user_email
         FROM {$table} e
         LEFT JOIN {$wpdb->users} u ON u.ID = e.creator_id
         GROUP BY e.creator_id
         ORDER BY total_earned DESC"
    );

    // Summary by file
    $by_file = $wpdb->get_results(
        "SELECT e.file_id, SUM(e.amount) AS total_earned, SUM(e.platform_cut) AS platform_total,
                SUM(e.sale_price) AS gross_revenue, COUNT(e.id) AS total_downloads,
                p.post_title AS file_title
         FROM {$table} e
         LEFT JOIN {$wpdb->posts} p ON p.ID = e.file_id
         GROUP BY e.file_id
         ORDER BY total_earned DESC
         LIMIT 50"
    );

    $platform_total = $wpdb->get_var( "SELECT SUM(platform_cut) FROM {$table}" );
    $gross_total    = $wpdb->get_var( "SELECT SUM(sale_price) FROM {$table}" );
    $creator_total  = $wpdb->get_var( "SELECT SUM(amount) FROM {$table}" );
    $dl_total       = $wpdb->get_var( "SELECT COUNT(id) FROM {$table}" );
    ?>
    <div class="wrap">
      <h1>Creator Portal — Earnings Report</h1>

      <div style="display:flex;gap:16px;flex-wrap:wrap;margin:20px 0">
        <?php foreach([
          ['Gross Revenue', '₹'.number_format((float)$gross_total,2)],
          ['Creator Earnings (70%)', '₹'.number_format((float)$creator_total,2)],
          ['Platform Earnings (30%)', '₹'.number_format((float)$platform_total,2)],
          ['Total Downloads', (int)$dl_total],
        ] as [$label,$val]): ?>
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px 20px;min-width:180px">
          <div style="color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em"><?php echo esc_html($label); ?></div>
          <div style="font-size:22px;font-weight:700;margin-top:4px"><?php echo esc_html($val); ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <h2>By Creator</h2>
      <table class="widefat striped">
        <thead><tr>
          <th>Creator</th><th>Email</th>
          <th>Downloads</th><th>Gross Revenue</th>
          <th>Creator Earned (70%)</th><th>Platform (30%)</th>
        </tr></thead>
        <tbody>
        <?php if($by_creator): foreach($by_creator as $r): ?>
          <tr>
            <td><?php echo esc_html($r->display_name ?: 'User #'.$r->creator_id); ?></td>
            <td><?php echo esc_html($r->user_email); ?></td>
            <td><?php echo (int)$r->total_downloads; ?></td>
            <td>₹<?php echo number_format((float)$r->gross_revenue,2); ?></td>
            <td>₹<?php echo number_format((float)$r->total_earned,2); ?></td>
            <td>₹<?php echo number_format((float)$r->platform_total,2); ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6">No earnings yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <h2 style="margin-top:28px">By File (Top 50)</h2>
      <table class="widefat striped">
        <thead><tr>
          <th>File</th>
          <th>Downloads</th><th>Gross Revenue</th>
          <th>Creator Earned (70%)</th><th>Platform (30%)</th>
        </tr></thead>
        <tbody>
        <?php if($by_file): foreach($by_file as $r): ?>
          <tr>
            <td><?php echo esc_html($r->file_title ?: 'File #'.$r->file_id); ?></td>
            <td><?php echo (int)$r->total_downloads; ?></td>
            <td>₹<?php echo number_format((float)$r->gross_revenue,2); ?></td>
            <td>₹<?php echo number_format((float)$r->total_earned,2); ?></td>
            <td>₹<?php echo number_format((float)$r->platform_total,2); ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5">No earnings yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php
}

// ─────────────────────────────────────────────
// 4. DASHBOARD WIDGET: PLATFORM EARNINGS SUMMARY
// ─────────────────────────────────────────────
add_action( 'wp_dashboard_setup', 'ds_add_earnings_dashboard_widget' );
function ds_add_earnings_dashboard_widget() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    wp_add_dashboard_widget(
        'ds_creator_earnings_widget',
        '💰 Creator Portal Earnings',
        'ds_render_earnings_dashboard_widget'
    );
}

function ds_render_earnings_dashboard_widget() {
    global $wpdb;
    $table   = $wpdb->prefix . 'ds_creator_earnings';
    $gross   = (float) $wpdb->get_var( "SELECT SUM(sale_price) FROM {$table}" );
    $earned  = (float) $wpdb->get_var( "SELECT SUM(amount) FROM {$table}" );
    $platform= (float) $wpdb->get_var( "SELECT SUM(platform_cut) FROM {$table}" );
    $dls     = (int)   $wpdb->get_var( "SELECT COUNT(id) FROM {$table}" );
    $pending = (int) count( get_posts([
        'post_type'=>'ds_creator_upload','posts_per_page'=>-1,'post_status'=>'publish',
        'meta_key'=>'ds_creator_status','meta_value'=>'pending','fields'=>'ids'
    ]) );

    echo '<table style="width:100%;font-size:13px"><tbody>';
    foreach([
        ['Gross Revenue',       '₹'.number_format($gross,2)],
        ['Creator Earnings',    '₹'.number_format($earned,2)],
        ['Platform Earnings',   '₹'.number_format($platform,2)],
        ['Total Downloads',     $dls],
        ['Pending Approvals',   $pending],
    ] as [$label,$val]){
        echo '<tr><td><strong>'.esc_html($label).'</strong></td><td style="text-align:right">'.esc_html($val).'</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p><a href="'.esc_url(admin_url('edit.php?post_type=ds_creator_upload&page=ds-earnings-report')).'">View Full Report →</a></p>';
}

// ─────────────────────────────────────────────
// 5. MANUALLY LOG A DOWNLOAD (ADMIN UTILITY)
//    Use this if you need to backfill earnings for a specific download.
//    Example: ds_manual_log_download($creator_upload_id, $sale_price);
// ─────────────────────────────────────────────
function ds_manual_log_download( $creator_upload_id, $sale_price = 0 ) {
    $creator_id   = (int) get_post_field( 'post_author', $creator_upload_id );
    $dlm_id       = (int) get_post_meta( $creator_upload_id, 'ds_creator_dlm_link', true );
    $creator_cut  = round( $sale_price * 0.70, 2 );
    $platform_cut = round( $sale_price * 0.30, 2 );

    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'ds_creator_earnings',
        [
            'file_id'      => $creator_upload_id,
            'creator_id'   => $creator_id,
            'download_id'  => $dlm_id,
            'amount'       => $creator_cut,
            'platform_cut' => $platform_cut,
            'sale_price'   => $sale_price,
            'created_at'   => current_time( 'mysql' ),
        ],
        [ '%d', '%d', '%d', '%f', '%f', '%f', '%s' ]
    );

    $old_earn    = (float) get_post_meta( $creator_upload_id, 'ds_creator_total_earnings', true );
    $old_revenue = (float) get_post_meta( $creator_upload_id, 'ds_creator_total_revenue',  true );
    $old_count   = (int)   get_post_meta( $creator_upload_id, 'ds_creator_download_count',  true );
    update_post_meta( $creator_upload_id, 'ds_creator_total_earnings', $old_earn + $creator_cut );
    update_post_meta( $creator_upload_id, 'ds_creator_total_revenue',  $old_revenue + $sale_price );
    update_post_meta( $creator_upload_id, 'ds_creator_download_count', $old_count + 1 );
}
