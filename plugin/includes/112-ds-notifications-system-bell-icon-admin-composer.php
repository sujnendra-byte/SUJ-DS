<?php
/**
 * Code Snippets export — DS Notifications System (Bell Icon + Admin Composer)
 *
 * Snippet ID : 793
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-107
 * Modified   : 2026-09-09 02:42:57
 * Load order : 112 of 144
 */
/*
 * Snippet Name: DS Notifications System (Bell Icon + Admin Composer)
 * Run: Everywhere
 * Adds a notification bell to the primary nav (before the account avatar),
 * a popup panel listing notifications, and a WP-admin "Notifications" CPT
 * for composing/targeting messages to Free / Premium / Pro / Business users.
 * Pure addition - does not modify any existing snippet or menu item.
 */

// ---------- 1. Custom Post Type: ds_notification (admin composer) ----------
add_action('init', function() {
    register_post_type('ds_notification', array(
        'label'        => 'Notifications',
        'labels'       => array(
            'name'          => 'Notifications',
            'singular_name' => 'Notification',
            'add_new_item'  => 'Add New Notification',
            'edit_item'     => 'Edit Notification',
            'all_items'     => 'All Notifications',
        ),
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-megaphone',
        'menu_position'=> 58,
        'supports'     => array('title'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ));
});

add_filter('enter_title_here', function($title, $post) {
    if ($post->post_type === 'ds_notification') {
        return 'Type the notification message here';
    }
    return $title;
}, 10, 2);

add_action('add_meta_boxes', function() {
    add_meta_box(
        'ds_notif_tiers',
        'Send To',
        function($post) {
            $targets = get_post_meta($post->ID, '_ds_notif_targets', true);
            if (!is_array($targets) || empty($targets)) {
                $targets = array('free', 'premium', 'pro', 'business');
            }
            wp_nonce_field('ds_notif_save', 'ds_notif_nonce');
            $tiers = array(
                'free'     => 'Free users',
                'premium'  => 'Premium users',
                'pro'      => 'Pro users',
                'business' => 'Business users',
            );
            echo '<p style="color:#666;font-size:12px;margin-top:0;">Choose which plans should see this notification.</p>';
            foreach ($tiers as $key => $label) {
                $checked = in_array($key, $targets) ? 'checked' : '';
                echo '<label style="display:block;margin-bottom:8px;font-size:13px;"><input type="checkbox" name="ds_notif_targets[]" value="' . esc_attr($key) . '" ' . $checked . '> ' . esc_html($label) . '</label>';
            }
            echo '<p style="color:#999;font-size:11px;margin-bottom:0;">Auto-deletes 30 days after publishing.</p>';
        },
        'ds_notification',
        'side',
        'high'
    );
});

add_action('save_post_ds_notification', function($post_id) {
    if (!isset($_POST['ds_notif_nonce']) || !wp_verify_nonce($_POST['ds_notif_nonce'], 'ds_notif_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $targets = isset($_POST['ds_notif_targets']) ? array_map('sanitize_text_field', (array) $_POST['ds_notif_targets']) : array();
    update_post_meta($post_id, '_ds_notif_targets', $targets);
});

// ---------- 2. Daily cleanup cron: delete notifications older than 30 days ----------
add_action('init', function() {
    if (!wp_next_scheduled('ds_notif_cleanup_cron')) {
        wp_schedule_event(time(), 'daily', 'ds_notif_cleanup_cron');
    }
});

add_action('ds_notif_cleanup_cron', function() {
    $old = get_posts(array(
        'post_type'      => 'ds_notification',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'date_query'     => array(array('column' => 'post_date', 'before' => '30 days ago')),
    ));
    foreach ($old as $pid) {
        wp_delete_post($pid, true);
    }
});

// ---------- 3. Tier detection helper (mirrors the site's established pattern) ----------
if (!function_exists('ds_notif_get_user_tier')) {
    function ds_notif_get_user_tier($user_id) {
        $user_obj = get_userdata($user_id);
        if (!$user_obj) return 'free';
        $roles     = (array) $user_obj->roles;
        $roles_str = implode(',', $roles);
        $is_admin  = in_array('administrator', $roles) || in_array('editor', $roles) || in_array('shop_manager', $roles);
        $ls_plan   = strtolower(
            get_user_meta($user_id, 'ds_plan', true) ?:
            get_user_meta($user_id, 'lemonsqueezy_plan', true) ?:
            get_user_meta($user_id, '_lsq_variant', true) ?:
            (function_exists('ds_get_user_plan') ? ds_get_user_plan($user_id) : '') ?:
            ''
        );

        if ($is_admin
            || in_array('ds_business', $roles) || in_array('business_member', $roles) || in_array('pmpro_4', $roles)
            || strpos($roles_str, 'business') !== false
            || in_array($ls_plan, array('business', 'enterprise')) || strpos($ls_plan, 'business') !== false) {
            return 'business';
        }
        if (in_array('ds_pro', $roles) || in_array('pro_member', $roles) || in_array('pmpro_3', $roles)
            || strpos($roles_str, 'pro') !== false
            || $ls_plan === 'pro' || strpos($ls_plan, 'pro') !== false) {
            return 'pro';
        }
        if (in_array('ds_premium', $roles) || in_array('premium_member', $roles) || in_array('pmpro_2', $roles)
            || strpos($roles_str, 'premium') !== false
            || in_array($ls_plan, array('premium', 'standard')) || strpos($ls_plan, 'premium') !== false) {
            return 'premium';
        }
        return 'free';
    }
}

// ---------- 4. REST endpoints ----------
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/notifications', array(
        'methods'             => 'GET',
        'callback'            => 'ds_notif_rest_get_list',
        'permission_callback' => function() { return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/notifications/read', array(
        'methods'             => 'POST',
        'callback'            => 'ds_notif_rest_mark_read',
        'permission_callback' => function() { return is_user_logged_in(); },
    ));
});

function ds_notif_rest_get_list($request) {
    $uid  = get_current_user_id();
    $tier = ds_notif_get_user_tier($uid);

    $read_ids = get_user_meta($uid, 'ds_notif_read_ids', true);
    if (!is_array($read_ids)) $read_ids = array();

    $posts = get_posts(array(
        'post_type'      => 'ds_notification',
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'date_query'     => array(array('column' => 'post_date', 'after' => '30 days ago')),
    ));

    $out = array();
    foreach ($posts as $p) {
        $targets = get_post_meta($p->ID, '_ds_notif_targets', true);
        if (!is_array($targets) || empty($targets)) {
            $targets = array('free', 'premium', 'pro', 'business');
        }
        if (!in_array($tier, $targets)) continue;

        $out[] = array(
            'id'      => $p->ID,
            'message' => html_entity_decode($p->post_title, ENT_QUOTES),
            'date'    => $p->post_date,
            'read'    => in_array($p->ID, $read_ids),
        );
    }

    $unread_count = 0;
    foreach ($out as $n) {
        if (!$n['read']) $unread_count++;
    }

    return new WP_REST_Response(array('notifications' => $out, 'unread_count' => $unread_count), 200);
}

function ds_notif_rest_mark_read($request) {
    $uid = get_current_user_id();
    if (!$uid) return new WP_REST_Response(array('success' => false), 401);

    $read_ids = get_user_meta($uid, 'ds_notif_read_ids', true);
    if (!is_array($read_ids)) $read_ids = array();

    $id = intval($request->get_param('id'));
    if ($id && !in_array($id, $read_ids)) {
        $read_ids[] = $id;
    }

    if ($request->get_param('all')) {
        $all_ids = get_posts(array(
            'post_type'      => 'ds_notification',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        $read_ids = array_values(array_unique(array_merge($read_ids, $all_ids)));
    }

    update_user_meta($uid, 'ds_notif_read_ids', $read_ids);
    return new WP_REST_Response(array('success' => true), 200);
}

// ---------- 5. Bell icon in primary nav (runs BEFORE the account snippet's priority-10 callback, so it lands before the avatar item) ----------
add_filter('wp_nav_menu_items', function($items, $args) {
    if (!isset($args->theme_location) || $args->theme_location !== 'primary') return $items;
    if (!is_user_logged_in()) return $items;

    $bell_svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';

    $bell_li = '
    <li class="menu-item ds-notif-nav-item" style="position:relative;list-style:none;display:flex;align-items:center;">
        <a href="#" id="ds-notif-trigger" onclick="dsNotifOpen(event)" style="position:relative;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;text-decoration:none;transition:background .15s;" onmouseover="this.style.background=\'rgba(245,158,11,.1)\'" onmouseout="this.style.background=\'transparent\'">
            ' . $bell_svg . '
            <span id="ds-notif-badge" style="display:none;position:absolute;top:1px;right:1px;min-width:16px;height:16px;padding:0 3px;border-radius:8px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;line-height:16px;text-align:center;border:2px solid #fff;box-sizing:content-box;">0</span>
        </a>
    </li>';

    return $items . $bell_li;
}, 9, 2);

// ---------- 6. Notification popup panel (markup + CSS + JS) ----------
add_action('wp_footer', function() {
    if (!is_user_logged_in()) return;
    $rest_list_url = esc_url_raw(rest_url('ds/v1/notifications'));
    $rest_read_url = esc_url_raw(rest_url('ds/v1/notifications/read'));
    $nonce         = wp_create_nonce('wp_rest');
    ?>
    <div id="ds-notif-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:999998;"></div>
    <div id="ds-notif-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:420px;max-width:calc(100vw - 20px);max-height:80vh;background:#fff;border-radius:24px;box-shadow:0 28px 90px rgba(0,0,0,.2);z-index:999999;overflow:hidden;flex-direction:column;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f1f1;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:16px;font-weight:800;color:#1a1a1a;">Notifications</h3>
            <button type="button" onclick="dsNotifClose()" style="background:none;border:none;font-size:22px;color:#9ca3af;cursor:pointer;line-height:1;padding:0;">&times;</button>
        </div>
        <div id="ds-notif-list" style="overflow-y:auto;flex:1;padding:8px 0;max-height:60vh;">
            <div style="padding:30px;text-align:center;color:#9ca3af;font-size:13px;">Loading...</div>
        </div>
    </div>
    <script>
    (function(){
        var LIST_URL = '<?php echo esc_js($rest_list_url); ?>';
        var READ_URL = '<?php echo esc_js($rest_read_url); ?>';
        var NONCE    = '<?php echo esc_js($nonce); ?>';

        function dsNotifOpen(e){
            if (e && e.preventDefault) e.preventDefault();
            var ov = document.getElementById('ds-notif-overlay');
            var m  = document.getElementById('ds-notif-modal');
            if (ov) ov.style.display = 'block';
            if (m)  m.style.display  = 'flex';
            dsNotifFetch(true);
        }
        function dsNotifClose(){
            var ov = document.getElementById('ds-notif-overlay');
            var m  = document.getElementById('ds-notif-modal');
            if (ov) ov.style.display = 'none';
            if (m)  m.style.display  = 'none';
        }
        function dsNotifFetch(markReadOnOpen){
            fetch(LIST_URL, {credentials:'include', headers:{'X-WP-Nonce':NONCE}})
                .then(function(r){ return r.json(); })
                .then(function(data){
                    var list = data.notifications || [];
                    var unread = data.unread_count || 0;
                    dsNotifRender(list);
                    dsNotifUpdateBadge(unread);
                    if (markReadOnOpen && unread > 0) {
                        fetch(READ_URL, {
                            method: 'POST',
                            credentials: 'include',
                            headers: {'X-WP-Nonce': NONCE, 'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'all=1'
                        }).then(function(){ dsNotifUpdateBadge(0); });
                    }
                })
                .catch(function(){});
        }
        function dsNotifRender(list){
            var el = document.getElementById('ds-notif-list');
            if (!el) return;
            if (!list.length) {
                el.innerHTML = '<div style="padding:30px;text-align:center;color:#9ca3af;font-size:13px;">No notifications yet.</div>';
                return;
            }
            var html = '';
            for (var i = 0; i < list.length; i++) {
                var n = list[i];
                var weight = n.read ? '400' : '700';
                var color  = n.read ? '#6b7280' : '#1a1a1a';
                var msg = String(n.message == null ? '' : n.message)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                html += '<div style="padding:14px 24px;border-bottom:1px solid #f7f7f7;font-size:13.5px;line-height:1.5;font-weight:' + weight + ';color:' + color + ';">' + msg + '</div>';
            }
            el.innerHTML = html;
        }
        function dsNotifUpdateBadge(count){
            var b = document.getElementById('ds-notif-badge');
            if (!b) return;
            if (count > 0) {
                b.style.display = 'block';
                b.textContent = count > 9 ? '9+' : String(count);
            } else {
                b.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            var ov = document.getElementById('ds-notif-overlay');
            if (ov) ov.addEventListener('click', dsNotifClose);
        });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') dsNotifClose(); });

        window.dsNotifOpen  = dsNotifOpen;
        window.dsNotifClose = dsNotifClose;

        // Load initial unread count on page load without marking anything read.
        dsNotifFetch(false);
    })();
    </script>
    <?php
}, 25);
