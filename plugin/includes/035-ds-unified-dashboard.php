<?php
/**
 * Code Snippets export — Ds unified dashboard
 *
 * Snippet ID : 721
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-035
 * Modified   : 2026-10-16 02:04:26 (V41 update from staging)
 * Load order : 035 of 144
 */
/**
 * Snippet Name: DS Admin: Unified User Activity Dashboard
 * Run: Everywhere
 * Description: Aggregates EXISTING per-user data (credits, generations,
 *              PPT drafts, marketplace submissions, download usage,
 *              public share links) into one admin-only lookup screen.
 *              Does NOT add new tracking and does NOT modify any
 *              existing snippet — it only reads usermeta/options that
 *              other snippets already write.
 *
 * Known limitations (by design, not bugs):
 *  - ds_ai_generations is capped at 200 entries per user (see DS AI
 *    Image Persistence) — "stored" counts below reflect that cap, not
 *    a lifetime total.
 *  - ds_ppt_drafts is capped at 20 entries per user.
 *  - Download usage is period counters only (see DS Marketplace Daily
 *    Download Limit API) — there is no record of which specific file
 *    was downloaded, only how many per period.
 *  - There is no login/logout tracking anywhere in the current
 *    snippet set, so it cannot be shown here.
 *  - The share-link lookup scans all `ds_share_*` rows in wp_options
 *    (there's no per-user index for these, since DS PPT Public Share
 *    stores them by token, not by user). Fine at small/medium scale;
 *    would need a dedicated table if share links become numerous.
 */
if ( ! defined('ABSPATH') ) exit;

// ── Core aggregation function — single source of truth ─────────────
if ( ! function_exists('ds_get_unified_user_activity') ) {
function ds_get_unified_user_activity($uid) {
    $user = get_userdata($uid);
    if (!$user) return null;

    $sub     = (int) get_user_meta($uid, 'ds_subscription_credits', true);
    $topup   = (int) get_user_meta($uid, 'ds_topup_credits', true);
    $carried = (int) get_user_meta($uid, 'ds_carried_credits', true);
    $credits = array(
        'subscription' => $sub,
        'topup'        => $topup,
        'carried'      => $carried,
        'total'        => function_exists('ds_get_credits') ? ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true),
    );

    $transactions = get_user_meta($uid, 'ds_credit_transactions', true) ?: array();

    $generations = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
    $gen_summary = array(
        'total_stored'    => count($generations),
        'submitted_count' => count(array_filter($generations, function($g){ return !empty($g['submitted']); })),
        'accepted_count'  => count(array_filter($generations, function($g){ return !empty($g['accepted']); })),
        'recent'          => array_slice($generations, 0, 10),
    );

    $drafts_raw = get_user_meta($uid, 'ds_ppt_drafts', true) ?: array();
    $drafts = array();
    foreach ($drafts_raw as $d) {
        $drafts[] = array(
            'id'           => $d['id'] ?? '',
            'title'        => $d['title'] ?? '',
            'slides_count' => $d['slides_count'] ?? 0,
            'updated'      => $d['updated'] ?? 0,
        );
    }
    usort($drafts, function($a, $b){ return $b['updated'] - $a['updated']; });

    $submitted_aids   = get_user_meta($uid, 'ds_submitted_aids', true) ?: array();
    $submitted_detail = array();
    foreach ($submitted_aids as $aid) {
        $submitted_detail[] = array(
            'attachment_id' => $aid,
            'title'         => get_the_title($aid) ?: '(untitled)',
            'edit_link'     => get_edit_post_link($aid, ''),
        );
    }

    $today      = gmdate('Y-m-d');
    $this_month = gmdate('Y-m');
    $download_usage = array(
        'free_today'      => (int) get_user_meta($uid, 'ds_dl_free_day_'   . $today, true),
        'pro_today'       => (int) get_user_meta($uid, 'ds_dl_pro_day_'    . $today, true),
        'free_this_month' => (int) get_user_meta($uid, 'ds_dl_free_month_' . $this_month, true),
        'pro_this_month'  => (int) get_user_meta($uid, 'ds_dl_pro_month_'  . $this_month, true),
    );
if (function_exists('ds_ua_dl_sum')) { /* daily log: counts every plan, on the site's own calendar */
$ua_t = ds_ua_today(); $ua_d = ds_ua_dl_sum($uid, $ua_t, $ua_t); $ua_m = ds_ua_dl_sum($uid, wp_date('Y-m-01'), $ua_t);
$download_usage = array(
'free_today' => max($ua_d['free'], $download_usage['free_today']),
'pro_today' => max($ua_d['pro'], $download_usage['pro_today']),
'free_this_month' => max($ua_m['free'], $download_usage['free_this_month']),
'pro_this_month' => max($ua_m['pro'], $download_usage['pro_this_month']),
);
}

    global $wpdb;
    $pattern    = $wpdb->esc_like('ds_share_') . '%';
    $share_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
        $pattern
    ));
    $share_links = array();
    foreach ($share_rows as $row) {
        $rec = maybe_unserialize($row->option_value);
        if (is_array($rec) && (int) ($rec['owner'] ?? 0) === (int) $uid) {
            $share_links[] = array(
                'token'   => str_replace('ds_share_', '', $row->option_name),
                'title'   => $rec['title']   ?? '',
                'created' => $rec['created'] ?? 0,
            );
        }
    }

    return array(
        'user' => array(
            'id'    => $uid,
            'login' => $user->user_login,
            'email' => $user->user_email,
            'roles' => $user->roles,
        ),
        'credits'        => $credits,
        'transactions'   => $transactions,
        'generations'    => $gen_summary,
        'drafts'         => $drafts,
        'submitted'      => $submitted_detail,
        'download_usage' => $download_usage,
        'share_links'    => $share_links,
    );
}
}

// ── REST wrapper around the same function, admin-only ───────────────
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/admin-user-activity/(?P<user_id>\d+)', array(
        'methods'             => 'GET',
        'permission_callback' => function () { return current_user_can('manage_options'); },
        'callback'            => function (WP_REST_Request $req) {
            $uid  = (int) $req->get_param('user_id');
            $data = ds_get_unified_user_activity($uid);
            if (!$data) return new WP_REST_Response(array('success' => false, 'error' => 'User not found'), 404);
            return rest_ensure_response(array_merge(array('success' => true), $data));
        },
    ));
});

// ── wp-admin page ─────────────────────────────────────────────────────
add_action('admin_menu', function () {
    add_menu_page(
        'DS User Activity',
        'DS User Activity',
        'manage_options',
        'ds-user-activity',
        'ds_render_user_activity_page',
        'dashicons-chart-area',
        58
    );
});

function ds_render_user_activity_page() {
    if (!current_user_can('manage_options')) return;

    $lookup = isset($_GET['ds_user']) ? sanitize_text_field(wp_unslash($_GET['ds_user'])) : '';
    $user = null;

    if ($lookup !== '') {
        if (is_numeric($lookup)) $user = get_user_by('id', (int) $lookup);
        if (!$user) $user = get_user_by('email', $lookup);
        if (!$user) $user = get_user_by('login', $lookup);
    }

    echo '<div class="wrap"><h1>DS User Activity</h1>';
    echo '<form method="get" style="margin:16px 0;">';
    echo '<input type="hidden" name="page" value="ds-user-activity" />';
    echo '<input type="text" name="ds_user" value="' . esc_attr($lookup) . '" placeholder="User ID, username, or email" style="width:280px;" />';
    echo '&nbsp;<button class="button button-primary">Look up</button>';
    echo '</form>';

    if ($lookup !== '' && !$user) {
        echo '<p style="color:#b91c1c;">No user found matching "' . esc_html($lookup) . '".</p>';
    }

    if ($user) {
        ds_render_activity_data(ds_get_unified_user_activity($user->ID));
    } elseif (function_exists('ds_ua_render_report')) {
ds_ua_render_report();
} else {
        $recent = get_users(array('number' => 20, 'orderby' => 'registered', 'order' => 'DESC'));
        echo '<h2>Recent users</h2><ul>';
        foreach ($recent as $ru) {
            $url = add_query_arg(array('page' => 'ds-user-activity', 'ds_user' => $ru->ID), admin_url('admin.php'));
            echo '<li><a href="' . esc_url($url) . '">' . esc_html($ru->user_login) . '</a> &mdash; ' . esc_html($ru->user_email) . '</li>';
        }
        echo '</ul>';
    }

    echo '</div>';
}

function ds_render_activity_data($d) {
    if (!$d) return;
    $u = $d['user'];

    echo '<h2>' . esc_html($u['login']) . ' (#' . esc_html($u['id']) . ') &mdash; ' . esc_html($u['email']) . '</h2>';
    echo '<p>Roles: ' . esc_html(implode(', ', $u['roles']) ?: 'none') . '</p>';

    echo '<h3>Credits</h3><table class="widefat striped" style="max-width:480px;"><tbody>';
    echo '<tr><td>Subscription</td><td>' . esc_html($d['credits']['subscription']) . '</td></tr>';
    echo '<tr><td>Topup</td><td>' . esc_html($d['credits']['topup']) . '</td></tr>';
    echo '<tr><td>Carried</td><td>' . esc_html($d['credits']['carried']) . '</td></tr>';
    echo '<tr><td><strong>Total</strong></td><td><strong>' . esc_html($d['credits']['total']) . '</strong></td></tr>';
    echo '</tbody></table>';

    echo '<h3>Download usage (current period)</h3><table class="widefat striped" style="max-width:480px;"><tbody>';
    echo '<tr><td>Free &mdash; today</td><td>' . esc_html($d['download_usage']['free_today']) . '</td></tr>';
    echo '<tr><td>Pro &mdash; today</td><td>' . esc_html($d['download_usage']['pro_today']) . '</td></tr>';
    echo '<tr><td>Free &mdash; this month</td><td>' . esc_html($d['download_usage']['free_this_month']) . '</td></tr>';
    echo '<tr><td>Pro &mdash; this month</td><td>' . esc_html($d['download_usage']['pro_this_month']) . '</td></tr>';
    echo '</tbody></table>';
    echo '<p><em>Counts only — no record of which specific file was downloaded.</em></p>';

    $g = $d['generations'];
    echo '<h3>AI generations</h3>';
    echo '<p>' . esc_html($g['total_stored']) . ' stored (most recent 200 kept), ' . esc_html($g['submitted_count']) . ' submitted to marketplace, ' . esc_html($g['accepted_count']) . ' accepted.</p>';
    if (!empty($g['recent'])) {
        echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Status</th><th>Prompt</th><th>Model</th></tr></thead><tbody>';
        foreach ($g['recent'] as $gen) {
            $prompt = isset($gen['prompt']) ? mb_strimwidth($gen['prompt'], 0, 80, '…') : '';
            echo '<tr><td>' . esc_html($gen['date'] ?? '') . '</td><td>' . esc_html($gen['status'] ?? '') . '</td><td>' . esc_html($prompt) . '</td><td>' . esc_html($gen['model'] ?? '') . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    echo '<h3>Saved PPT drafts</h3>';
    echo '<p>' . count($d['drafts']) . ' stored (capped at 20 most recent).</p>';
    if (!empty($d['drafts'])) {
        echo '<table class="widefat striped"><thead><tr><th>Title</th><th>Slides</th><th>Last updated</th></tr></thead><tbody>';
        foreach ($d['drafts'] as $dr) {
            $updated = $dr['updated'] ? date('Y-m-d H:i', $dr['updated']) : '—';
            echo '<tr><td>' . esc_html($dr['title']) . '</td><td>' . esc_html($dr['slides_count']) . '</td><td>' . esc_html($updated) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    echo '<h3>Marketplace submissions</h3>';
    if (empty($d['submitted'])) {
        echo '<p>None.</p>';
    } else {
        echo '<ul>';
        foreach ($d['submitted'] as $s) {
            echo '<li><a href="' . esc_url($s['edit_link']) . '">' . esc_html($s['title']) . '</a> (#' . esc_html($s['attachment_id']) . ')</li>';
        }
        echo '</ul>';
    }

    echo '<h3>Public share links</h3>';
    if (empty($d['share_links'])) {
        echo '<p>None found.</p>';
    } else {
        echo '<table class="widefat striped"><thead><tr><th>Title</th><th>Created</th><th>Token</th></tr></thead><tbody>';
        foreach ($d['share_links'] as $s) {
            $created = $s['created'] ? date('Y-m-d H:i', $s['created']) : '—';
            echo '<tr><td>' . esc_html($s['title']) . '</td><td>' . esc_html($created) . '</td><td><code>' . esc_html($s['token']) . '</code></td></tr>';
        }
        echo '</tbody></table>';
        echo '<p><em>These links never expire and need no login to view.</em></p>';
    }

    echo '<h3>Credit transaction history</h3>';
    if (empty($d['transactions'])) {
        echo '<p>None recorded. (Note: this log currently only captures earn-side events like marketplace cashback — credit spends on generations aren\'t separately logged here, though they do show up in the generations history above.)</p>';
    } else {
        echo '<table class="widefat striped"><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Label</th></tr></thead><tbody>';
        foreach (array_reverse($d['transactions']) as $t) {
            echo '<tr><td>' . esc_html($t['date'] ?? '') . '</td><td>' . esc_html($t['type'] ?? '') . '</td><td>' . esc_html($t['amount'] ?? '') . '</td><td>' . esc_html($t['label'] ?? '') . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    echo '<hr><p style="color:#666;"><strong>Not tracked anywhere yet:</strong> login/logout timestamps. No session log exists in the current snippet set.</p>';
}
