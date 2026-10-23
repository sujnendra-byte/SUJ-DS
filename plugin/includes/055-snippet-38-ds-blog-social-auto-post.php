<?php
/**
 * Code Snippets export — Snippet 38: DS Blog Social Auto-Post
 *
 * Snippet ID : 741
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-055
 * Modified   : 2026-09-09 02:41:13
 * Load order : 055 of 144
 */
/**
 * Snippet 38: DS Blog Social Auto-Post
 * Run: Everywhere
 *
 * Automatically posts new DS Blog posts to:
 *   - Facebook Page
 *   - X (Twitter)
 *   - LinkedIn Page
 *   - Instagram (via Facebook Graph API)
 *   - Pinterest
 *
 * SETUP: wp-admin -> Settings -> Social Auto-Post
 * Add your API keys there. Only platforms with keys configured will post.
 *
 * TRIGGERS: fires when a new ds_blog_post is published
 *           (both manual and auto-scheduled cron posts)
 */

if ( ! defined('ABSPATH') ) exit;

// ================================================================
// PART 1 -- Admin settings page
// ================================================================

add_action('admin_menu', function () {
    add_options_page(
        'Social Auto-Post',
        'Social Auto-Post',
        'manage_options',
        'ds-social-post',
        'ds_social_admin_page'
    );
});

add_action('admin_init', function () {
    if (
        isset($_POST['ds_social_save'])
        && check_admin_referer('ds_social_save_action', 'ds_social_nonce')
        && current_user_can('manage_options')
    ) {
        $fields = array(
            'ds_social_fb_page_id',
            'ds_social_fb_token',
            'ds_social_x_api_key',
            'ds_social_x_api_secret',
            'ds_social_x_access_token',
            'ds_social_x_access_secret',
            'ds_social_li_token',
            'ds_social_li_org_id',
            'ds_social_ig_user_id',
            'ds_social_pin_token',
            'ds_social_pin_board_id',
        );
        foreach ($fields as $f) {
            update_option($f, sanitize_text_field($_POST[$f] ?? ''));
        }
        update_option('ds_social_post_template', sanitize_textarea_field($_POST['ds_social_post_template'] ?? ''));
        update_option('ds_social_enabled_platforms', array_map('sanitize_text_field', $_POST['ds_social_platforms'] ?? array()));

        wp_redirect(admin_url('options-general.php?page=ds-social-post&saved=1'));
        exit;
    }
});

if ( ! function_exists('ds_social_admin_page') ) {
function ds_social_admin_page() {
    $saved     = isset($_GET['saved']);
    $platforms = get_option('ds_social_enabled_platforms', array('facebook','twitter','linkedin'));
    $template  = get_option('ds_social_post_template', "{title}\n\n{excerpt}\n\nRead more: {url}\n\n#DesignStager #AI #Presentation");
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
    #ds-social-wrap * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
    #ds-social-wrap { max-width: 820px; padding: 20px 0 60px; }
    #ds-social-wrap h1 { font-size: 22px; font-weight: 800; color: #1B2A4A; margin: 0 0 6px; }
    #ds-social-wrap h1 span { color: #E8481C; }
    .ds-s-saved { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 16px; color: #16a34a; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
    .ds-s-card { background: #fff; border: 1px solid #e8edf5; border-radius: 12px; padding: 22px 24px; margin-bottom: 16px; box-shadow: 0 2px 10px rgba(27,42,74,.06); }
    .ds-s-card h3 { font-size: 13px; font-weight: 800; color: #1B2A4A; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ds-s-card .sub { font-size: 11px; color: #9ca3af; margin: 0 0 16px; padding-bottom: 12px; border-bottom: 2px solid #e8edf5; }
    .ds-s-field { margin-bottom: 14px; }
    .ds-s-field label { display: block; font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px; }
    .ds-s-field input[type=text], .ds-s-field input[type=password], .ds-s-field textarea { width: 100%; padding: 9px 13px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #1a1a2e; }
    .ds-s-field input:focus, .ds-s-field textarea:focus { outline: none; border-color: #E8481C; }
    .ds-s-field .hint { font-size: 10px; color: #9ca3af; margin-top: 4px; line-height: 1.5; }
    .ds-s-field .hint a { color: #E8481C; }
    .ds-s-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ds-s-toggle { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .ds-s-toggle input[type=checkbox] { width: 16px; height: 16px; accent-color: #E8481C; }
    .ds-s-toggle label { font-size: 13px; color: #374151; font-weight: 600; }
    .ds-s-platform-badge { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 10px; font-weight: 800; color: #fff; margin-left: 8px; }
    .ds-s-save { background: linear-gradient(135deg, #E8481C, #c93a15); color: #fff; border: none; border-radius: 8px; padding: 12px 28px; font-size: 14px; font-weight: 800; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .ds-s-save:hover { opacity: .9; }
    .ds-s-disabled { opacity: .5; pointer-events: none; }
    </style>

    <div id="ds-social-wrap">
        <h1>Social <span>Auto-Post</span></h1>
        <p style="color:#6b7280;font-size:13px;margin:4px 0 20px;">When a new blog post is published, it automatically posts to all enabled platforms. Only platforms with API keys filled in will post.</p>

        <?php if ($saved): ?>
        <div class="ds-s-saved">✅ Social Auto-Post settings saved!</div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('ds_social_save_action', 'ds_social_nonce'); ?>
            <input type="hidden" name="ds_social_save" value="1">

            <!-- Post Template -->
            <div class="ds-s-card">
                <h3>Post Template</h3>
                <p class="sub">Available variables: {title} {excerpt} {url} {tags} — used for Facebook, X, LinkedIn</p>
                <div class="ds-s-field">
                    <label>Post Text Template</label>
                    <textarea name="ds_social_post_template" rows="5"><?php echo esc_textarea($template); ?></textarea>
                    <div class="hint">This text is posted to Facebook, X and LinkedIn. Keep X under 280 chars (it auto-truncates). Instagram and Pinterest use title + image only.</div>
                </div>
            </div>

            <!-- Enable/Disable platforms -->
            <div class="ds-s-card">
                <h3>Enabled Platforms</h3>
                <p class="sub">Only checked platforms will receive auto-posts.</p>
                <?php
                $all_platforms = array(
                    'facebook'  => array('label' => 'Facebook Page',  'color' => '#1877f2'),
                    'twitter'   => array('label' => 'X (Twitter)',     'color' => '#000'),
                    'linkedin'  => array('label' => 'LinkedIn Page',   'color' => '#0077b5'),
                    'instagram' => array('label' => 'Instagram',       'color' => '#e1306c'),
                    'pinterest' => array('label' => 'Pinterest',       'color' => '#e60023'),
                );
                foreach ($all_platforms as $key => $p): ?>
                <div class="ds-s-toggle">
                    <input type="checkbox" name="ds_social_platforms[]" id="ds_plat_<?php echo $key; ?>"
                        value="<?php echo $key; ?>" <?php checked(in_array($key, $platforms)); ?>>
                    <label for="ds_plat_<?php echo $key; ?>">
                        <?php echo $p['label']; ?>
                        <span class="ds-s-platform-badge" style="background:<?php echo $p['color']; ?>;"><?php echo strtoupper($key); ?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Facebook -->
            <div class="ds-s-card">
                <h3>Facebook Page <span class="ds-s-platform-badge" style="background:#1877f2;">FACEBOOK</span></h3>
                <p class="sub">Posts text + featured image + link to your Facebook Page. Also enables Instagram if you add Instagram User ID below.
                    <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Get token from Graph API Explorer</a>
                </p>
                <div class="ds-s-row">
                    <div class="ds-s-field">
                        <label>Facebook Page ID</label>
                        <input type="text" name="ds_social_fb_page_id" value="<?php echo esc_attr(get_option('ds_social_fb_page_id')); ?>" placeholder="123456789">
                        <div class="hint">Found at facebook.com/your-page -> About -> Page ID</div>
                    </div>
                    <div class="ds-s-field">
                        <label>Page Access Token (never expires)</label>
                        <input type="password" name="ds_social_fb_token" value="<?php echo esc_attr(get_option('ds_social_fb_token')); ?>" placeholder="EAAxxxxxxx...">
                        <div class="hint">Create a permanent Page Access Token via Graph API Explorer. Must have pages_manage_posts permission.</div>
                    </div>
                </div>
            </div>

            <!-- X (Twitter) -->
            <div class="ds-s-card">
                <h3>X (Twitter) <span class="ds-s-platform-badge" style="background:#000;">X</span></h3>
                <p class="sub">Posts tweet with title + link. Requires a free X Developer account at <a href="https://developer.twitter.com" target="_blank">developer.twitter.com</a></p>
                <div class="ds-s-row">
                    <div class="ds-s-field">
                        <label>API Key (Consumer Key)</label>
                        <input type="password" name="ds_social_x_api_key" value="<?php echo esc_attr(get_option('ds_social_x_api_key')); ?>" placeholder="xxxxxxxxxxxxxxxxx">
                    </div>
                    <div class="ds-s-field">
                        <label>API Secret (Consumer Secret)</label>
                        <input type="password" name="ds_social_x_api_secret" value="<?php echo esc_attr(get_option('ds_social_x_api_secret')); ?>" placeholder="xxxxxxxxxxxxxxxxx">
                    </div>
                </div>
                <div class="ds-s-row">
                    <div class="ds-s-field">
                        <label>Access Token</label>
                        <input type="password" name="ds_social_x_access_token" value="<?php echo esc_attr(get_option('ds_social_x_access_token')); ?>" placeholder="xxxxxxxxxxxxxxxxx">
                    </div>
                    <div class="ds-s-field">
                        <label>Access Token Secret</label>
                        <input type="password" name="ds_social_x_access_secret" value="<?php echo esc_attr(get_option('ds_social_x_access_secret')); ?>" placeholder="xxxxxxxxxxxxxxxxx">
                    </div>
                </div>
            </div>

            <!-- LinkedIn -->
            <div class="ds-s-card">
                <h3>LinkedIn Page <span class="ds-s-platform-badge" style="background:#0077b5;">LINKEDIN</span></h3>
                <p class="sub">Posts to your LinkedIn Company Page. Get token at <a href="https://www.linkedin.com/developers/" target="_blank">linkedin.com/developers</a> — needs w_organization_social permission.</p>
                <div class="ds-s-row">
                    <div class="ds-s-field">
                        <label>Organization ID (Company Page ID)</label>
                        <input type="text" name="ds_social_li_org_id" value="<?php echo esc_attr(get_option('ds_social_li_org_id')); ?>" placeholder="12345678">
                        <div class="hint">Found in your LinkedIn Company Page URL</div>
                    </div>
                    <div class="ds-s-field">
                        <label>Access Token</label>
                        <input type="password" name="ds_social_li_token" value="<?php echo esc_attr(get_option('ds_social_li_token')); ?>" placeholder="AQxxxxxxx...">
                    </div>
                </div>
            </div>

            <!-- Instagram -->
            <div class="ds-s-card">
                <h3>Instagram <span class="ds-s-platform-badge" style="background:#e1306c;">INSTAGRAM</span></h3>
                <p class="sub">Posts featured image + caption to Instagram Business account. Requires same Facebook token above + Instagram Business account connected to your Facebook Page.</p>
                <div class="ds-s-field">
                    <label>Instagram Business Account User ID</label>
                    <input type="text" name="ds_social_ig_user_id" value="<?php echo esc_attr(get_option('ds_social_ig_user_id')); ?>" placeholder="17841400000000000">
                    <div class="hint">Get from Graph API: GET /me/accounts -> GET /{page-id}?fields=instagram_business_account</div>
                </div>
            </div>

            <!-- Pinterest -->
            <div class="ds-s-card">
                <h3>Pinterest <span class="ds-s-platform-badge" style="background:#e60023;">PINTEREST</span></h3>
                <p class="sub">Pins featured image + title + link to your Pinterest board. Get token at <a href="https://developers.pinterest.com/" target="_blank">developers.pinterest.com</a></p>
                <div class="ds-s-row">
                    <div class="ds-s-field">
                        <label>Access Token</label>
                        <input type="password" name="ds_social_pin_token" value="<?php echo esc_attr(get_option('ds_social_pin_token')); ?>" placeholder="pina_xxxxxxxxx...">
                    </div>
                    <div class="ds-s-field">
                        <label>Board ID</label>
                        <input type="text" name="ds_social_pin_board_id" value="<?php echo esc_attr(get_option('ds_social_pin_board_id')); ?>" placeholder="your_username/board-name">
                        <div class="hint">Found in your Pinterest board URL</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="ds-s-save">💾 Save Social Settings</button>
        </form>
    </div>
    <?php
}
}

// ================================================================
// PART 2 -- Core posting functions
// ================================================================

// Build post text from template
if ( ! function_exists('ds_social_build_text') ) {
function ds_social_build_text( $post_id, $max_length = 0 ) {
    $template = get_option('ds_social_post_template',
        "{title}\n\n{excerpt}\n\nRead more: {url}\n\n#DesignStager #AI #Presentation"
    );
    $title   = get_the_title($post_id);
    $url     = get_permalink($post_id);
    $excerpt = get_the_excerpt($post_id);
    if ( empty($excerpt) ) {
        $excerpt = wp_trim_words(get_post_field('post_content', $post_id), 25, '...');
    }
    $tags = implode(' ', array_map(function($t) { return '#' . str_replace(' ', '', $t->name); },
        get_the_tags($post_id) ?: array()));

    $text = str_replace(
        array('{title}', '{excerpt}', '{url}', '{tags}'),
        array($title, $excerpt, $url, $tags),
        $template
    );

    if ( $max_length > 0 && strlen($text) > $max_length ) {
        $text = substr($text, 0, $max_length - 3) . '...';
    }
    return $text;
}
}

// ── Facebook ─────────────────────────────────────────────────────────────────

if ( ! function_exists('ds_social_post_facebook') ) {
function ds_social_post_facebook( $post_id ) {
    $page_id = get_option('ds_social_fb_page_id', '');
    $token   = get_option('ds_social_fb_token', '');
    if ( empty($page_id) || empty($token) ) return array('skipped' => true);

    $url     = get_permalink($post_id);
    $message = ds_social_build_text($post_id);
    $image   = get_the_post_thumbnail_url($post_id, 'large');

    // Post with image if available
    if ( $image ) {
        $resp = wp_remote_post("https://graph.facebook.com/v19.0/{$page_id}/photos", array(
            'timeout' => 30,
            'body'    => array(
                'url'          => $image,
                'caption'      => $message,
                'access_token' => $token,
            ),
        ));
    } else {
        $resp = wp_remote_post("https://graph.facebook.com/v19.0/{$page_id}/feed", array(
            'timeout' => 30,
            'body'    => array(
                'message'      => $message,
                'link'         => $url,
                'access_token' => $token,
            ),
        ));
    }

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    return array(
        'success' => ! empty($body['id']),
        'id'      => $body['id'] ?? '',
        'error'   => $body['error']['message'] ?? '',
    );
}
}

// ── X (Twitter) ──────────────────────────────────────────────────────────────

if ( ! function_exists('ds_social_post_twitter') ) {
function ds_social_post_twitter( $post_id ) {
    $api_key       = get_option('ds_social_x_api_key', '');
    $api_secret    = get_option('ds_social_x_api_secret', '');
    $access_token  = get_option('ds_social_x_access_token', '');
    $access_secret = get_option('ds_social_x_access_secret', '');
    if ( empty($api_key) || empty($api_secret) || empty($access_token) || empty($access_secret) ) {
        return array('skipped' => true);
    }

    $text     = ds_social_build_text($post_id, 270);
    $endpoint = 'https://api.twitter.com/2/tweets';
    $body     = wp_json_encode(array('text' => $text));

    // OAuth 1.0a signature
    $oauth_params = array(
        'oauth_consumer_key'     => $api_key,
        'oauth_nonce'            => wp_generate_password(32, false),
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp'        => time(),
        'oauth_token'            => $access_token,
        'oauth_version'          => '1.0',
    );
    ksort($oauth_params);
    $param_string = implode('&', array_map(function($k, $v) {
        return rawurlencode($k) . '=' . rawurlencode($v);
    }, array_keys($oauth_params), $oauth_params));

    $base_string = 'POST&' . rawurlencode($endpoint) . '&' . rawurlencode($param_string);
    $signing_key = rawurlencode($api_secret) . '&' . rawurlencode($access_secret);
    $signature   = base64_encode(hash_hmac('sha1', $base_string, $signing_key, true));

    $oauth_params['oauth_signature'] = $signature;
    $auth_header = 'OAuth ' . implode(', ', array_map(function($k, $v) {
        return rawurlencode($k) . '="' . rawurlencode($v) . '"';
    }, array_keys($oauth_params), $oauth_params));

    $resp = wp_remote_post($endpoint, array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => $auth_header,
            'Content-Type'  => 'application/json',
        ),
        'body' => $body,
    ));

    $rb = json_decode(wp_remote_retrieve_body($resp), true);
    return array(
        'success' => ! empty($rb['data']['id']),
        'id'      => $rb['data']['id'] ?? '',
        'error'   => $rb['detail'] ?? ($rb['errors'][0]['message'] ?? ''),
    );
}
}

// ── LinkedIn ─────────────────────────────────────────────────────────────────

if ( ! function_exists('ds_social_post_linkedin') ) {
function ds_social_post_linkedin( $post_id ) {
    $token  = get_option('ds_social_li_token', '');
    $org_id = get_option('ds_social_li_org_id', '');
    if ( empty($token) || empty($org_id) ) return array('skipped' => true);

    $url    = get_permalink($post_id);
    $title  = get_the_title($post_id);
    $text   = ds_social_build_text($post_id);
    $image  = get_the_post_thumbnail_url($post_id, 'large');
    $author = 'urn:li:organization:' . $org_id;

    $post_body = array(
        'author'         => $author,
        'lifecycleState' => 'PUBLISHED',
        'specificContent' => array(
            'com.linkedin.ugc.ShareContent' => array(
                'shareCommentary'  => array('text' => $text),
                'shareMediaCategory' => 'ARTICLE',
                'media'            => array(array(
                    'status'       => 'READY',
                    'description'  => array('text' => wp_trim_words(get_the_excerpt($post_id), 20, '...')),
                    'originalUrl'  => $url,
                    'title'        => array('text' => $title),
                )),
            ),
        ),
        'visibility' => array(
            'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
        ),
    );

    $resp = wp_remote_post('https://api.linkedin.com/v2/ugcPosts', array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ),
        'body' => wp_json_encode($post_body),
    ));

    $rb   = json_decode(wp_remote_retrieve_body($resp), true);
    $code = wp_remote_retrieve_response_code($resp);
    return array(
        'success' => ($code === 201),
        'id'      => $rb['id'] ?? '',
        'error'   => $rb['message'] ?? '',
    );
}
}

// ── Instagram ─────────────────────────────────────────────────────────────────

if ( ! function_exists('ds_social_post_instagram') ) {
function ds_social_post_instagram( $post_id ) {
    $ig_user_id = get_option('ds_social_ig_user_id', '');
    $token      = get_option('ds_social_fb_token', ''); // uses same FB token
    if ( empty($ig_user_id) || empty($token) ) return array('skipped' => true);

    $image = get_the_post_thumbnail_url($post_id, 'full');
    if ( empty($image) ) return array('skipped' => true, 'error' => 'No featured image — Instagram requires an image.');

    $caption = ds_social_build_text($post_id, 2000);

    // Step 1: Create media container
    $r1 = wp_remote_post("https://graph.facebook.com/v19.0/{$ig_user_id}/media", array(
        'timeout' => 30,
        'body'    => array(
            'image_url'    => $image,
            'caption'      => $caption,
            'access_token' => $token,
        ),
    ));
    $b1 = json_decode(wp_remote_retrieve_body($r1), true);
    $creation_id = $b1['id'] ?? '';
    if ( empty($creation_id) ) {
        return array('success' => false, 'error' => $b1['error']['message'] ?? 'Could not create media container.');
    }

    // Step 2: Publish
    $r2 = wp_remote_post("https://graph.facebook.com/v19.0/{$ig_user_id}/media_publish", array(
        'timeout' => 30,
        'body'    => array(
            'creation_id'  => $creation_id,
            'access_token' => $token,
        ),
    ));
    $b2 = json_decode(wp_remote_retrieve_body($r2), true);
    return array(
        'success' => ! empty($b2['id']),
        'id'      => $b2['id'] ?? '',
        'error'   => $b2['error']['message'] ?? '',
    );
}
}

// ── Pinterest ─────────────────────────────────────────────────────────────────

if ( ! function_exists('ds_social_post_pinterest') ) {
function ds_social_post_pinterest( $post_id ) {
    $token    = get_option('ds_social_pin_token', '');
    $board_id = get_option('ds_social_pin_board_id', '');
    if ( empty($token) || empty($board_id) ) return array('skipped' => true);

    $image = get_the_post_thumbnail_url($post_id, 'large');
    if ( empty($image) ) return array('skipped' => true, 'error' => 'No featured image.');

    $resp = wp_remote_post('https://api.pinterest.com/v5/pins', array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode(array(
            'board_id'  => $board_id,
            'title'     => get_the_title($post_id),
            'description' => wp_trim_words(get_the_excerpt($post_id), 30, '...'),
            'link'      => get_permalink($post_id),
            'media_source' => array(
                'source_type' => 'image_url',
                'url'         => $image,
            ),
        )),
    ));

    $rb   = json_decode(wp_remote_retrieve_body($resp), true);
    $code = wp_remote_retrieve_response_code($resp);
    return array(
        'success' => ($code === 201),
        'id'      => $rb['id'] ?? '',
        'error'   => $rb['message'] ?? '',
    );
}
}

// ================================================================
// PART 3 -- Main trigger: fire on new ds_blog_post publish
// ================================================================

if ( ! function_exists('ds_social_auto_post') ) {
function ds_social_auto_post( $post_id ) {
    if ( get_post_type($post_id) !== 'ds_blog_post' ) return;
    if ( get_post_status($post_id) !== 'publish' ) return;

    // Don't post twice
    if ( get_post_meta($post_id, '_ds_social_posted', true) ) return;
    update_post_meta($post_id, '_ds_social_posted', 1);

    $enabled  = get_option('ds_social_enabled_platforms', array('facebook','twitter','linkedin'));
    $results  = array();

    if ( in_array('facebook', $enabled) )  $results['facebook']  = ds_social_post_facebook($post_id);
    if ( in_array('twitter', $enabled) )   $results['twitter']   = ds_social_post_twitter($post_id);
    if ( in_array('linkedin', $enabled) )  $results['linkedin']  = ds_social_post_linkedin($post_id);
    if ( in_array('instagram', $enabled) ) $results['instagram'] = ds_social_post_instagram($post_id);
    if ( in_array('pinterest', $enabled) ) $results['pinterest'] = ds_social_post_pinterest($post_id);

    update_post_meta($post_id, '_ds_social_results', $results);
    update_post_meta($post_id, '_ds_social_posted_at', current_time('mysql'));
}
}

// Hook: when post transitions to published
add_action('transition_post_status', function($new, $old, $post) {
    if ( $new === 'publish' && $old !== 'publish' && $post->post_type === 'ds_blog_post' ) {
        // Wait 3 minutes so AI images + references finish generating first
        wp_schedule_single_event(time() + 180, 'ds_social_delayed_post', array($post->ID));
    }
}, 10, 3);

// Also fire after AI images/references are generated (cron posts)
add_action('ds_ai_blog_post_created', function($post_id, $data) {
    // Runs after images + references complete
    wp_schedule_single_event(time() + 60, 'ds_social_delayed_post', array($post_id));
}, 99, 2);

add_action('ds_social_delayed_post', 'ds_social_auto_post');

// ================================================================
// PART 4 -- Admin meta box: show posting results per post
// ================================================================

add_action('add_meta_boxes', function () {
    add_meta_box(
        'ds_social_results',
        'Social Auto-Post Status',
        'ds_social_results_box',
        'ds_blog_post',
        'side',
        'default'
    );
});

if ( ! function_exists('ds_social_results_box') ) {
function ds_social_results_box( $post ) {
    $results   = get_post_meta($post->ID, '_ds_social_results', true) ?: array();
    $posted_at = get_post_meta($post->ID, '_ds_social_posted_at', true);
    $nonce     = wp_create_nonce('wp_rest');
    ?>
    <style>
    #ds-social-box { font-family: 'Poppins', sans-serif; font-size: 12px; }
    .ds-sr-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0f0f0; }
    .ds-sr-row:last-child { border: none; }
    .ds-sr-platform { font-weight: 700; color: #1B2A4A; display: flex; align-items: center; gap: 6px; }
    .ds-sr-badge { padding: 2px 7px; border-radius: 99px; font-size: 10px; font-weight: 700; color: #fff; }
    .ds-sr-ok   { background: #16a34a; }
    .ds-sr-fail { background: #dc2626; }
    .ds-sr-skip { background: #9ca3af; }
    .ds-sr-btn { background: linear-gradient(135deg,#E8481C,#c93a15); color: #fff; border: none; border-radius: 6px; padding: 7px 12px; font-size: 11px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; font-family: 'Poppins', sans-serif; }
    .ds-sr-btn:hover { opacity: .9; }
    .ds-sr-msg { font-size: 10px; color: #9ca3af; margin-top: 8px; }
    </style>

    <div id="ds-social-box">
    <?php if ( ! empty($results) ) : ?>
        <?php if ($posted_at): ?>
        <p style="font-size:10px;color:#9ca3af;margin:0 0 10px;">Posted: <?php echo esc_html($posted_at); ?></p>
        <?php endif; ?>
        <?php
        $icons = array('facebook'=>'f','twitter'=>'X','linkedin'=>'in','instagram'=>'ig','pinterest'=>'P');
        foreach ($results as $platform => $result): ?>
        <div class="ds-sr-row">
            <span class="ds-sr-platform"><?php echo ucfirst($platform); ?></span>
            <?php if (!empty($result['skipped'])): ?>
                <span class="ds-sr-badge ds-sr-skip">SKIPPED</span>
            <?php elseif (!empty($result['success'])): ?>
                <span class="ds-sr-badge ds-sr-ok">POSTED</span>
            <?php else: ?>
                <span class="ds-sr-badge ds-sr-fail" title="<?php echo esc_attr($result['error'] ?? ''); ?>">FAILED</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color:#9ca3af;margin:0 0 10px;">Not posted yet.</p>
    <?php endif; ?>

        <!-- Manual re-post button -->
        <button class="ds-sr-btn" id="ds-repost-btn" onclick="dsRepost(<?php echo $post->ID; ?>)">
            Re-post to Social Media
        </button>
        <div id="ds-repost-msg" class="ds-sr-msg"></div>
    </div>

    <script>
    function dsRepost(postId) {
        var btn = document.getElementById('ds-repost-btn');
        var msg = document.getElementById('ds-repost-msg');
        btn.disabled = true;
        btn.textContent = 'Posting...';
        fetch('/wp-json/ds/v1/social-repost', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-WP-Nonce':'<?php echo $nonce; ?>'},
            body: JSON.stringify({post_id: postId})
        }).then(function(r){ return r.json(); }).then(function(d) {
            btn.disabled = false;
            btn.textContent = 'Re-post to Social Media';
            if (d.success) {
                msg.textContent = 'Posted! Refresh to see results.';
                msg.style.color = '#16a34a';
            } else {
                msg.textContent = 'Error: ' + (d.error || 'unknown');
                msg.style.color = '#dc2626';
            }
        }).catch(function(e) {
            btn.disabled = false;
            btn.textContent = 'Re-post to Social Media';
            msg.textContent = 'Network error.';
            msg.style.color = '#dc2626';
        });
    }
    </script>
    <?php
}
}

// REST endpoint for manual re-post
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/social-repost', array(
        'methods'             => 'POST',
        'callback'            => function(WP_REST_Request $req) {
            $post_id = intval($req->get_param('post_id'));
            if ( ! $post_id || get_post_type($post_id) !== 'ds_blog_post' ) {
                return rest_ensure_response(array('success' => false, 'error' => 'Invalid post ID.'));
            }
            // Allow re-posting by clearing the flag
            delete_post_meta($post_id, '_ds_social_posted');
            ds_social_auto_post($post_id);
            return rest_ensure_response(array('success' => true));
        },
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ));
});
