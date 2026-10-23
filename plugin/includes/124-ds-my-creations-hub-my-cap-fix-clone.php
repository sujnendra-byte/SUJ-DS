<?php
/**
 * Code Snippets export — DS My Creations Hub - my-cap fix [CLONE]
 *
 * Snippet ID : 805
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-119
 * Modified   : 2026-10-03 15:47:24
 * Load order : 119 of 218
 */
/**
 * Designstager - DS My Generated Images (R2 Storage)
 * Snippet Name: DS My Generated Images (R2 Storage)
 * Run: Everywhere
 * Shortcode: [ds_my_generated_images]
 *
 * Clone of the original "DS My Generated Images". Changes vs. the original:
 *  - Each card now carries the image's R2 file_id (if it has one) and shows a
 *    Temporary/Saved/Expired badge plus, in the lightbox, the exact expiry
 *    date and a "Save to My Creations" button — reusing the same retention
 *    concepts as every other Designstager generation flow.
 *  - Download and Delete now route through the R2/ds-hub endpoints when a
 *    file_id is present, instead of the old local-uploads-only endpoints
 *    (which reject R2 proxy URLs). Legacy local-file entries (no file_id)
 *    behave exactly as before — nothing changes for pre-migration images.
 *  - Expired R2 images no longer render a (now-dead) image src or a Download
 *    button — matches the "no download URL for expired files" requirement.
 * The original snippet is kept (deactivated) as a rollback backup — do not delete it.
 */

// ==== My Creations Hub: combined saved-items cap REST endpoint (additive) ====
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/my-cap', array(
        'methods'               => 'GET',
        'permission_callback'   => function() { return is_user_logged_in(); },
        'callback'              => function() {
            $uid = get_current_user_id();
            $used = 0;

            // Images and videos: count the SAME way they are actually displayed,
            // by walking this user's own generation-history meta and checking each
            // linked file's status via ds_hub_get_generated_file(), exactly like
            // /my-saved-images and /my-videos already do. A raw SQL count filtered
            // by ds_generated_files.user_id undercounts: some rows (created by the
            // R2 rehost/retention background job) end up with user_id = 0 instead
            // of the real owner, so they never match that filter even though they
            // belong to this user's own history and are shown to them as saved.
            if ( function_exists('ds_hub_get_user_generations') && function_exists('ds_hub_filter_valid_gens') && function_exists('ds_hub_get_generated_file') ) {
                $img_gens = ds_hub_get_user_generations($uid);
                $img_valid = ds_hub_filter_valid_gens($img_gens);
                foreach ($img_valid as $gen) {
                    $file_id = is_array($gen) ? (int) ( isset($gen['file_id']) ? $gen['file_id'] : 0 ) : 0;
                    if (!$file_id) continue;
                    $f = ds_hub_get_generated_file($file_id);
                    if ($f && $f->status === 'saved') $used++;
                }
            }
            if ( function_exists('ds_hub_get_generated_file') ) {
                $vid_gens = get_user_meta($uid, 'ds_ai_video_generations', true);
                if (is_array($vid_gens)) {
                    foreach ($vid_gens as $gen) {
                        $file_id = is_array($gen) ? (int) ( isset($gen['file_id']) ? $gen['file_id'] : 0 ) : 0;
                        if (!$file_id) continue;
                        $f = ds_hub_get_generated_file($file_id);
                        if ($f && $f->status === 'saved') $used++;
                    }
                }
            }

            // Audio saves and PPT drafts/built-files don't have the same per-item
            // display path available here, so keep counting those the original way.
            global $wpdb;
            $table = $wpdb->prefix . 'ds_generated_files';
            $used += (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND status = 'saved' AND file_type = 'audio'", $uid
            ));
            $drafts = get_user_meta($uid, 'ds_ppt_drafts', true);
            if (is_array($drafts)) $used += count($drafts);
            /* PowerPoint files left over from downloads are not saved items and are not counted */

            $total = function_exists('ds_get_user_saved_cap') ? ds_get_user_saved_cap($uid) : ( defined('DS_SAVED_ITEMS_CAP') ? DS_SAVED_ITEMS_CAP : 100 );
            return rest_ensure_response(array('success' => true, 'used' => $used, 'total' => $total));
        },
    ));

register_rest_route('ds/v1', '/my-images-page', array(
'methods' => 'GET',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
$user_id = get_current_user_id();
$offset = max(0, (int) $req->get_param('offset'));
$limit = 20;
$gens = ds_hub_get_user_generations($user_id);
$gens = function_exists('ds_hub_filter_out_expired') ? ds_hub_filter_out_expired($gens) : $gens;
$valid = ds_hub_filter_valid_gens($gens);
$nonce = wp_create_nonce('wp_rest');
$slice = array_slice($valid, $offset, $limit);
$html = '';
foreach ($slice as $gen) {
$html .= ds_hub_render_my_image_card($gen, $nonce);
}
return rest_ensure_response(array(
'success' => true,
'html' => $html,
'count' => count($slice),
'total' => count($valid),
'next_offset' => $offset + count($slice),
));
},
));

register_rest_route('ds/v1', '/my-saved-images', array(
'methods' => 'GET',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
$user_id = get_current_user_id();
$html = '';
$count = 0;
if (function_exists('ds_hub_get_user_generations') && function_exists('ds_hub_filter_valid_gens') && function_exists('ds_hub_render_my_image_card')) {
$gens = ds_hub_get_user_generations($user_id);
$valid = ds_hub_filter_valid_gens($gens);
$nonce = wp_create_nonce('wp_rest');
foreach ($valid as $gen) {
$file_id = is_array($gen) ? (int)($gen['file_id'] ?? 0) : 0;
if (!$file_id || !function_exists('ds_hub_get_generated_file')) continue;
$f = ds_hub_get_generated_file($file_id);
if (!$f || $f->status !== 'saved') continue;
$html .= ds_hub_render_my_image_card($gen, $nonce);
$count++;
}
}
return rest_ensure_response(array('success' => true, 'html' => $html, 'count' => $count));
},
));

register_rest_route('ds/v1', '/my-expired-items', array(
'methods' => 'GET',
'permission_callback' => function() { return is_user_logged_in(); },
'callback' => function(WP_REST_Request $req) {
$user_id = get_current_user_id();
$images = array();
$videos = array();
if (function_exists('ds_hub_get_user_generations') && function_exists('ds_hub_filter_valid_gens') && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_file_is_available')) {
$gens = ds_hub_filter_valid_gens(ds_hub_get_user_generations($user_id));
foreach ($gens as $gen) {
$file_id = is_array($gen) ? (int)($gen['file_id'] ?? 0) : 0;
if (!$file_id) continue;
$f = ds_hub_get_generated_file($file_id);
if ($f && $f->status !== 'saved' && !ds_hub_file_is_available($f)) {
$images[] = array('file_id' => $file_id, 'filename' => $f->original_filename, 'created_at' => $f->created_at, 'expires_at' => $f->expires_at);
}
}
$vgens = get_user_meta($user_id, 'ds_ai_video_generations', true);
if (is_array($vgens)) {
foreach ($vgens as $g) {
if (!is_array($g)) continue;
$vfile_id = (int)($g['file_id'] ?? 0);
if (!$vfile_id) continue;
$vf = ds_hub_get_generated_file($vfile_id);
if ($vf && $vf->status !== 'saved' && !ds_hub_file_is_available($vf)) {
$videos[] = array('file_id' => $vfile_id, 'filename' => $vf->original_filename, 'created_at' => $vf->created_at, 'expires_at' => $vf->expires_at);
}
}
}
$audio = array();
$agens = get_user_meta($user_id, 'ds_ai_audio_generations', true);
if (is_array($agens)) {
foreach ($agens as $g) {
if (!is_array($g)) continue;
$afile_id = (int)($g['file_id'] ?? 0);
if (!$afile_id) continue;
$af = ds_hub_get_generated_file($afile_id);
if ($af && $af->status !== 'saved' && !ds_hub_file_is_available($af)) {
$audio[] = array('file_id' => $afile_id, 'filename' => $af->original_filename, 'created_at' => $af->created_at, 'expires_at' => $af->expires_at);
}
}
}
}
return rest_ensure_response(array('success' => true, 'images' => $images, 'videos' => $videos, 'audio' => $audio, 'count' => count($images) + count($videos) + count($audio)));
},
));
});


// ============================================================
// Shareable AI image viewer + OG meta (?ds_share_img=ATTACHMENT_ID)
// ============================================================
add_action('wp_head', function() {
if (!isset($_GET['ds_share_img']) || !is_numeric($_GET['ds_share_img'])) return;
$aid = (int)$_GET['ds_share_img'];
$img = wp_get_attachment_url($aid);
if (!$img) return;
$is_ai = get_post_meta($aid, 'ds_ai_generated', true);
if (!$is_ai) return; // only allow AI-generated attachments
$prompt = (string)get_post_meta($aid, 'ds_ai_prompt', true);
$title = $prompt ? wp_trim_words($prompt, 12, '...') : 'AI-generated design';
$url = home_url('/?ds_share_img=' . $aid);
echo "\n<!-- DS AI Share OG -->\n";
echo '<meta property="og:type" content="website"/>' . "\n";
echo '<meta property="og:title" content="' . esc_attr($title) . ' - Designstager AI"/>' . "\n";
echo '<meta property="og:description" content="Created with Designstager AI Studio. Generate your own AI designs for free."/>' . "\n";
echo '<meta property="og:image" content="' . esc_url($img) . '"/>' . "\n";
echo '<meta property="og:image:secure_url" content="' . esc_url($img) . '"/>' . "\n";
echo '<meta property="og:url" content="' . esc_url($url) . '"/>' . "\n";
echo '<meta name="twitter:card" content="summary_large_image"/>' . "\n";
echo '<meta name="twitter:image" content="' . esc_url($img) . '"/>' . "\n";
echo '<meta name="twitter:title" content="' . esc_attr($title) . '"/>' . "\n";
}, 99);

// Render dedicated viewer page when ?ds_share_img= is in the URL
add_filter('the_content', function($content) {
if (!isset($_GET['ds_share_img']) || !is_numeric($_GET['ds_share_img'])) return $content;
if (!is_front_page() && !is_home()) return $content; // only override homepage
$aid = (int)$_GET['ds_share_img'];
$img = wp_get_attachment_url($aid);
if (!$img) return $content;
$is_ai = get_post_meta($aid, 'ds_ai_generated', true);
if (!$is_ai) return $content;
$prompt = (string)get_post_meta($aid, 'ds_ai_prompt', true);
$model = (string)get_post_meta($aid, 'ds_ai_model', true);
$title = $prompt ? esc_html(wp_trim_words($prompt, 14, '...')) : 'AI-generated design';
$studio_url = home_url('/ai-studio');
ob_start(); ?>
<div style="background:#0a0a0a;color:#fff;min-height:80vh;padding:32px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<div style="max-width:900px;margin:0 auto;text-align:center;">
<h1 style="font-size:clamp(22px,3vw,32px);font-weight:800;margin:0 0 8px;"><?php echo $title; ?></h1>
<?php if ($model): ?><p style="font-size:13px;color:#94a3b8;margin:0 0 24px;">Created with <strong style="color:#60a5fa;"><?php echo esc_html($model); ?></strong> on Designstager AI Studio</p><?php endif; ?>
<img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($prompt); ?>" style="max-width:100%;height:auto;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.5);margin-bottom:24px;"/>
<div style="margin-top:24px;">
<a href="<?php echo esc_url($studio_url); ?>" style="display:inline-block;background:#2563eb;color:#fff;padding:14px 32px;border-radius:100px;font-size:15px;font-weight:700;text-decoration:none;">Create Your Own AI Design - Free</a>
</div>
</div>
</div>
<?php return ob_get_clean();
}, 999);

if (!function_exists('ds_hub_get_user_generations')) {
function ds_hub_get_user_generations($user_id) {
$gens = get_user_meta($user_id, 'ds_ai_generations', true) ?: array();
$legacy = get_user_meta($user_id, 'ds_generated_images', true) ?: array();
if (is_array($legacy)) {
foreach ($legacy as $item) {
$u = is_array($item) ? ($item['url'] ?? '') : $item;
if ($u) {
$gens[] = is_array($item) ? $item : array('url'=>$u,'date'=>'','prompt'=>'','model'=>'');
}
}
}
if (is_array($gens) && count($gens) > 1) {
$seen = array();
$deduped = array();
foreach ($gens as $g) {
$u = is_array($g) ? ($g['url'] ?? '') : (string)$g;
if (!$u || isset($seen[$u])) continue;
$seen[$u] = true;
$deduped[] = $g;
}
if (count($deduped) !== count($gens)) {
update_user_meta($user_id, 'ds_ai_generations', $deduped);
delete_user_meta($user_id, 'ds_generated_images');
$gens = $deduped;
}
}
return $gens;
}
}

if (!function_exists('ds_hub_filter_valid_gens')) {
function ds_hub_filter_valid_gens($gens) {
$out = array();
foreach ($gens as $g) {
$u = is_array($g) ? ($g['url'] ?? '') : $g;
if (!empty($u)) $out[] = $g;
}
return $out;
}
}

if (!function_exists('ds_hub_filter_out_expired')) {
function ds_hub_filter_out_expired($gens) {
$out = array();
foreach ($gens as $gen) {
$file_id = is_array($gen) ? (int)($gen['file_id'] ?? 0) : 0;
if ($file_id && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_file_is_available')) {
$f = ds_hub_get_generated_file($file_id);
if ($f && $f->status !== 'saved' && !ds_hub_file_is_available($f)) {
continue;
}
}
$out[] = $gen;
}
return $out;
}
}

if (!function_exists('ds_hub_render_my_image_card')) {
function ds_hub_render_my_image_card($gen, $nonce) {
$url = is_array($gen) ? ($gen['url'] ?? '') : $gen;
if (empty($url)) return '';
$aid = is_array($gen) ? ($gen['attachment_id'] ?? 0) : 0;
$prompt = is_array($gen) ? ($gen['prompt'] ?? '') : '';
$model = is_array($gen) ? ($gen['model'] ?? '') : '';
$submitted = is_array($gen) && !empty($gen['submitted']);
$accepted = false;
if ($submitted && $aid) {
$dl_post_id = (int)get_post_meta($aid, 'ds_marketplace_download_id', true);
if ($dl_post_id) {
$dl_status = get_post_status($dl_post_id);
if ($dl_status === 'publish') $accepted = true;
}
}
$card_filter = $accepted ? 'accepted' : ($submitted ? 'submitted' : 'not-submitted');
$model_costs = [
'cloudflare' => 2,
'flux-schnell' => 4,
'flux-dev' => 7,
'seedream' => 8,
'flux-pro' => 10,
'nanobana' => 13,
'hf' => 3,
];
$cost = $model_costs[ strtolower($model) ] ?? 2;
$cashback = 1;
$cashback_pct = $cost > 0 ? round( (1 / $cost) * 100 ) : 50;
$file_id = is_array($gen) ? (int)($gen['file_id'] ?? 0) : 0;
$exp_badge = '';
$exp_label = '';
$is_expired_r2 = false;
if ($file_id && function_exists('ds_hub_get_generated_file')) {
$f = ds_hub_get_generated_file($file_id);
if ($f) {
if ($f->status === 'saved') {
$exp_badge = 'saved';
$exp_label = $f->expires_at
? ('Saved to My Creations — expires ' . date_i18n('F j, Y', strtotime($f->expires_at . ' UTC')) . '.')
: 'Saved to My Creations — kept permanently.';
} elseif (function_exists('ds_hub_file_is_available') && !ds_hub_file_is_available($f)) {
$exp_badge = 'expired';
$exp_label = 'This file has expired and was permanently deleted from storage.';
$is_expired_r2 = true;
} elseif ($f->expires_at) {
$days_left = max(0, ceil((strtotime($f->expires_at . ' UTC') - time()) / DAY_IN_SECONDS));
$exp_badge = 'temp';
$exp_label = 'Temporary storage — expires in ' . (int)$days_left . ' day' . ($days_left === 1 ? '' : 's') . ' (' . date_i18n('F j, Y', strtotime($f->expires_at . ' UTC')) . '). Download or save it before then.';
}
}
}
if ($file_id && function_exists('ds_hub_file_proxy_url')) {
$url = ds_hub_file_proxy_url($file_id) . '?_wpnonce=' . $nonce;
$thumb_url = ds_hub_file_proxy_url($file_id) . '?variant=thumb&_wpnonce=' . $nonce;
$disp_url = ds_hub_file_proxy_url($file_id) . '?variant=display&_wpnonce=' . $nonce;
} else {
$thumb_url = $url;
$disp_url = $url;
}
ob_start();
?>
<div class="dsmy-card" data-filter="<?php echo esc_attr($card_filter); ?>" data-card-key="<?php echo $aid ? (int)$aid : 'f' . (int)$file_id; ?>" onclick="dsmyOpenLB('<?php echo esc_js($disp_url); ?>','<?php echo esc_js($prompt); ?>',<?php echo (int)$aid; ?>,<?php echo $cashback; ?>,<?php echo ($submitted || $accepted) ? 1 : 0; ?>,<?php echo $cashback_pct; ?>,<?php echo $file_id; ?>,'<?php echo esc_js($exp_label); ?>')">
<?php if ($is_expired_r2): ?>
<div class="dsmy-card-expired-ph">This file has expired and was permanently deleted.</div>
<?php else: ?>
<img data-fid="<?php echo (int)$file_id; ?>" data-fallback="<?php echo esc_url($thumb_url); ?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="<?php echo esc_attr($prompt); ?>" loading="lazy"/>
<div class="ds-img-spin"><div class="ds-spin"></div></div>
<?php endif; ?>
<?php if ($model): ?><span class="dsmy-card-tag"><?php echo esc_html($model); ?></span><?php endif; ?>
<?php if ($accepted): ?><span class="dsmy-card-sub dsmy-card-acc">✓ Accepted</span><?php elseif ($submitted): ?><span class="dsmy-card-sub">Submitted</span><?php endif; ?>
<?php if ($exp_badge === 'temp'): ?><span class="dsmy-card-exp dsmy-card-exp-temp" title="Temporary — will expire soon"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg></span>
<?php elseif ($exp_badge === 'saved'): ?><span class="dsmy-card-exp dsmy-card-exp-saved" title="Saved"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
<?php elseif ($exp_badge === 'expired'): ?><span class="dsmy-card-exp dsmy-card-exp-expired">Expired</span>
<?php endif; ?>
<div class="dsmy-card-actions">
<?php if (!$is_expired_r2): ?>
<button class="dsmy-btn-sm dl" onclick="event.stopPropagation();dsmyDL('<?php echo esc_js($url); ?>',<?php echo $file_id; ?>)" title="Download">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
</button>
<button class="dsmy-btn-sm sh" onclick="event.stopPropagation();dsmyOpenShare('<?php echo esc_js($url); ?>','<?php echo esc_js($prompt); ?>',<?php echo (int)$aid; ?>)" title="Share">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2.2"/></svg>
</button>
<?php endif; ?>
<?php if ($file_id && $exp_badge !== 'saved' && !$is_expired_r2): ?>
<button class="dsmy-btn-sm dsmy-save" onclick="event.stopPropagation();dsmyCardSave(<?php echo (int)$file_id; ?>,this)" title="Save to My Creations">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="#4ade80" stroke-width="2.2" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="#4ade80" stroke-width="2.2" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="#4ade80" stroke-width="2.2" stroke-linejoin="round"/></svg>
</button>
<?php endif; ?>
<?php if (($aid || $file_id) && !$submitted && !$accepted): ?>
<button class="dsmy-btn-sm mk" onclick="event.stopPropagation();dsmyOpenMkt(<?php echo (int)$aid; ?>,<?php echo $cashback; ?>,<?php echo $cashback_pct; ?>,<?php echo $file_id; ?>)" title="Submit to Marketplace">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
</button>
<?php endif; ?>
<?php if (($aid || $file_id) && !$accepted): ?>
<button class="dsmy-btn-sm" onclick="event.stopPropagation();dsmyDelete(<?php echo (int)$aid; ?>,'<?php echo esc_js($url); ?>',<?php echo $file_id; ?>)" title="Delete">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="#f87171" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="#f87171" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="#f87171" stroke-width="2.2" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="#f87171" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
</button>
<?php endif; ?>
</div>
</div>
<?php
return ob_get_clean();
}
}

/* Signed-out visitors get the same sign-in card as the AI Studio page instead of
   a bare "Please log in." line. Shared by AI Video, AI Audio and My Creations. */
if ( ! function_exists( 'ds_guest_login_card' ) ) {
function ds_guest_login_card( $title, $text ) {
    $here = get_permalink();
    $back = $here ? $here : home_url( '/ai-studio/' );
    $url  = home_url( '/login?redirect_to=' . urlencode( $back ) );
    return '<div style="display:flex;align-items:center;justify-content:center;min-height:65vh;background:#0a0a0a;">'
        . '<div style="text-align:center;padding:52px 36px;background:#111;border-radius:24px;border:1px solid #1e1e1e;max-width:400px;">'
        . '<div style="width:60px;height:60px;background:#2563eb;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">'
        . '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#fff"/></svg>'
        . '</div>'
        . '<h3 style="font-size:22px;font-weight:800;color:#fff;margin:0 0 10px;">' . esc_html( $title ) . '</h3>'
        . '<p style="font-size:14px;color:#6b7280;margin:0 0 26px;line-height:1.6;">' . esc_html( $text ) . '</p>'
        . '<a href="' . esc_url( $url ) . '" style="display:inline-block;background:#2563eb;color:#fff;padding:13px 32px;border-radius:100px;font-size:14px;font-weight:700;text-decoration:none;">Login / Sign Up Free</a>'
        . '</div></div>';
}
}

add_shortcode('ds_my_generated_images', function() {
if (!is_user_logged_in()) {
return ds_guest_login_card('Login to See Your Creations', 'Everything you generate is saved here.');
}

$user_id = get_current_user_id();

// Tier detection (mirrors AI Images generator page) - free users cannot submit to Marketplace
$user_obj_dsmy   = wp_get_current_user();
$user_roles_dsmy = (array)($user_obj_dsmy->roles ?: []);
$roles_str_dsmy  = implode(',', $user_roles_dsmy);

$ls_plan_dsmy = strtolower(
    get_user_meta($user_id, 'ds_plan', true) ?:
    get_user_meta($user_id, 'lemonsqueezy_plan', true) ?:
    get_user_meta($user_id, '_lsq_variant', true) ?:
    (function_exists('ds_access_plan') ? ds_access_plan($user_id) : (function_exists('ds_get_user_plan') ? ds_get_user_plan($user_id) : '')) ?:
    ''
);

$is_admin_dsmy = in_array('administrator', $user_roles_dsmy) || in_array('editor', $user_roles_dsmy) || in_array('shop_manager', $user_roles_dsmy);

$is_pro_or_business_dsmy = $is_admin_dsmy
    || in_array('ds_pro', $user_roles_dsmy)
    || in_array('ds_business', $user_roles_dsmy)
    || in_array('pro_member', $user_roles_dsmy)
    || in_array('business_member', $user_roles_dsmy)
    || in_array('pmpro_3', $user_roles_dsmy)
    || in_array('pmpro_4', $user_roles_dsmy)
    || strpos($roles_str_dsmy, 'pro') !== false
    || strpos($roles_str_dsmy, 'business') !== false
    || strpos($roles_str_dsmy, 'premium_plus') !== false
    || in_array($ls_plan_dsmy, ['pro','business','enterprise'])
    || strpos($ls_plan_dsmy, 'pro') !== false
    || strpos($ls_plan_dsmy, 'business') !== false;

$is_premium = $is_pro_or_business_dsmy
    || in_array('ds_premium', $user_roles_dsmy)
    || in_array('premium_member', $user_roles_dsmy)
    || in_array('pmpro_2', $user_roles_dsmy)
    || strpos($roles_str_dsmy, 'premium') !== false
    || in_array($ls_plan_dsmy, ['premium','standard'])
    || strpos($ls_plan_dsmy, 'premium') !== false;

$gens = ds_hub_get_user_generations($user_id);
$gens = function_exists('ds_hub_filter_out_expired') ? ds_hub_filter_out_expired($gens) : $gens;
$nonce = wp_create_nonce('wp_rest');
$rest = trailingslashit(rest_url('ds/v1'));
$rest_hub = trailingslashit(rest_url('ds-hub/v1'));
$__dsmy_saved_icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAI40lEQVR42tVaW29cVxVe3977zMw5c2bGseNEiR21KU1bifQB5Q/UvNQBVUUF8Qd4AVShItomAgnbqmgQTS8CVF55QogHVEpQBEoZC3GnEhLq5SE4aRvbLXZr+9zPzNl7Lx7mjOtM7Vx8iWFJR3M02metb132WmtfQETEzBKAeXt5+eiI33wCAl/QhTlumSURgfaYQMRSyQ+kFK91i+4Pj9br//gFs/wyYG76bR/8u2HyqF+r/Nhx1Hje0VQUBd1JklKS61ao6OqsUxTPjPneOe4Z0ALgrb5TAMx7a/HDQ773SrcwFISJASD6lrleXRARiU9wYbbbMz3WeWmtOYq0FUK4o3X32YUoGQbwVLvdVsxstlICi4vhKDWcf1arzlia5UYKIa1lqxwlpFKDQKnb7Q5gADmVyrbwF90uMfOACGYAeth3nZU4fW6sUX+6zaweItpUCWV99dWWXxtbCxMjpZTGWPZ9TyRpFmmtV0EEhmAiSyAIZj667iGAmK3u5vn7DOKec27mjN4YMIFARwChNiqBHlNnJU71sO89tRhlfBQ402be1BMKoC92CsMAYK01Dd+VWaf7Sk3w46nnrRwjsu+88w6KouDmkRNDBunbSqkDRVGYarUq806+4GTxySAICsdxUBQF3wh+f0yr1XIKz3+jWqnclee57RuFmfvvaiXO9AHffXo+SmgcOMObKKGI+b6i0CgnErJON86j4GtHDh/+YFD43ArnNeeTM+DQoUPZ4cOHze2EDzPrhTjlgf+s67oiz3NmZgKgVqNUDze8pxejjAGcHfSEgBCutZaIiCuVCowxS3cfOrQ8NTUlppgFM4PLX0eQ3CqJbBx3k0cwM4iu48WVSoUAvJ/n2TNe3YUUwpZaqNU400N+7cx8lHx/AtCzpTwiIrEx/sp3QURyZmbGThNxqSkDYB7MShsAbBx3o2fj2I0MSiPWxxr176ZJ+o1mvSaFEIZ7oNRqnOlh3zuzGGXnJgDdz4ZiK0C3mVAEM4v+742ejWM3k7sYhqNjjfqPVuPsyWa9pgRgNyox5NfOLkbJOQCm3W4P5sltEQPobOO7znyU8KDdvEZDM7MA8Px7QUTDTf98mOTaWivRC6di1PfOXguipWOtxos7UoCZCQTnP1l2r81Jd4hQvYn3+mNEjZTW7AzWgSAgGhqCZWYHwPPzQUpDTfd8GGembG3UStaxSjnnFsLw4m0pYPk6cKLbK0RjRcFvkSRSRHSzVNQfY3qdiioLoxiUMU1kmFn1lIjRbNafi5PMMJMsCmObTa8axfZb6tYqPriM22AhoivVivOZottlAiQAQAhnWx60dr0VqVYcaK3n7h6ioAwhO0NkX2d2xoHzC1HiN+reVBinBiDR6WgG8cPiVoXNzs4KAEwW52uCBIRgZtbMbK0xZjsPM1tm1hCCXUGCDL0IgGdnZ9dxnSLSzCzI934QpdlHjuNIIqKiKMBMY7ccQhMTE/3J9fP5IHlwqOl92xKRNjtouJlISRKSiJbj/NnxIe9npQw9EAGWmYsF5kBKOVJWewAQtzUHSkYCwHcW4/iPjqx8xbD9lNEWTHxbaoDAUglmqebCPP/peMu/0A+dzca/SYShTVLvbWehDUpcJKKLu7UeuBF4IqJPE/HCFkmBtqmELN/NDoHviM+268AuAMdu8BG0D1SmZJTpWe6El9oP8Btj/f/KA+12WwGwV5fDB5byzqXVrn7jvSB+kohoqueV/10PtJnVBKCvLocP+I3aJa/qjGWFpZFm/bnFMP37UeAPt7qVcsc90Ad/ZTm4v96oXVJKja2Eic6zrNPrf3AvEdHoNkriTj2ADdnE3gx8o+leUlKNxUlmiEi4nqvCtLPCyv6u5GPu2BwoBTIA2y9um8V8D/zy/Y2Ge0lJNR6nmSFiuLWqsNasZVnnc8fq9fl+VrojHuht3fSERcwPrgRBCmBuY4Zpt9tqYmJCzy0F9/kN95JSarxneUatVhVEtBZF2eQ9B1t/6+8O3pFJ3A8ZZnYX4uyXhbaTdbfevRZEZwG8yMxqlogmAD23FNzXbJWWT1JDRD3wzEHWKfrg1WDztqchNEskAfB8lH5+1Hcn0yzX3UI7B5r+C+8G0TcB6HXwvZg/VsZ8aXkO4jibPNaq7xj8tjzwULlkBMuu6a9LjKEoyfXBpv/CfJjkxorfejX5mlJOafle2IA4iKJs8vjB1l93A/y2PADAMLMYa1YvrsbZqyMNzyFAG2tlmOQM4GUl+XUh1XicJPZj8BREcXZ6N8HvJAsxEemjvvulIM0vDDc8h5mNsba3w6fUgTTt7brVqlVBzGEUp6ePj7T+spvgt60AAJ6engYR6fm333wszLoXRpp1RUQFMbMuCgZga9WqIHAYd/TkXoDfUR2YmZmxRIRTp07p+bcqj4Vp/puRhudwrxhp161JAQ6TODt9vOXtCfgdtxJlzsepU6Q/vDr32FqSvzrU8FSr4TlszQdRrk/fPdL6816B35VmrqzCOHnyZJeIHl3Oss8qpVprK50/3XuksVQWqT0Bv2vdaLkwAQAedd3fD/T+hvaQdq2d7rUWDOb1sLQ3WqTvtQLb3OkBA7RXFt8Uk+odSXHfikS9Qy4zNTUlpj8+M9hPwtTUFEpctjwpXceL+TBJpZSuMYallESgJI+CE8c3OWLaT1oIw4OQzlUQ6sYYEkKA2RYKhH87jnPSGMPGGPbrrk809JMPk2T9kO/y5cv7AvrEiRN0jUh4aTqsoV6qVRw/SlIrhCDHcajb7VzBQpRMN31vauCYFUmaRcRcHrPSvsQRmMBETMABv+424jhlIQSMMabVrMsgTr+HhTA8SHD+Vas6R9IsN1IKac0WB937REZrKorCSiGEsda4tarsdIolkHsSRESLcTZZ92oXu4WhPM+3vmqwT8RlBmJmW6vVZNWRFEbJI+Mt/8J1lz3qVeflasU5mu3DZY+bkeM45FYVdbrFYtIpvn5Xs/4rZpZ9zSQAc3n+o2NDI/4TRHik0Pqe8u4A9tsBILKOUleI+NdrH8UvnRgfudbH/F8upNyvU9NFFQAAAABJRU5ErkJggg==';
$suburl = esc_js(rest_url('ds/v1/submit-to-marketplace'));
$site = esc_js(home_url());
$credits = function_exists('ds_get_credits') ? ds_get_credits($user_id) : (int) get_user_meta($user_id, 'ds_ai_credits', true);

$__cap_used  = function_exists('ds_hub_count_saved_items') ? ds_hub_count_saved_items($user_id) : 0;
$__cap_total = function_exists('ds_get_user_saved_cap') ? ds_get_user_saved_cap($user_id) : ( defined('DS_SAVED_ITEMS_CAP') ? DS_SAVED_ITEMS_CAP : 100 );

ob_start();
?>
<style>
#ds-myimgs,#ds-myimgs *{box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
#ds-myimgs{background:#0a0a0a;color:#fff;border-radius:10px;overflow:hidden;min-height:500px;}
.dsmy-header{padding:14px 16px;background:#080808;border-bottom:1px solid #141414;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.dsmy-header-left h2{font-size:18px;font-weight:800;color:#f1f5f9;margin:0 0 2px;}
.dsmy-header-left p{font-size:12px;color:#64748b;margin:0;}
.dsmy-credits{display:flex;align-items:center;gap:8px;font-size:13px;color:#f1f5f9;font-weight:700;}
.dsmy-credits a{color:#60a5fa;text-decoration:none;font-size:11px;font-weight:600;background:#0d1929;padding:3px 10px;border-radius:100px;border:1px solid #1e3a5f;}
.dsmy-credits a:hover{background:#1e3a5f;}
.dsmy-tabs{display:flex;border-bottom:1px solid #141414;background:#0d0d0d;}
.dsmy-tab{padding:10px 16px;border:none;background:transparent;color:#64748b;font-size:12px;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;font-family:inherit;transition:all .15s;}
.dsmy-tab.active{color:#60a5fa;border-bottom-color:#3b82f6;}
.dsmy-tab:hover:not(.active){color:#94a3b8;}
.dsmy-body{padding:14px 16px;}
.dsmy-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}
@media(max-width:900px){.dsmy-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:600px){.dsmy-grid{grid-template-columns:repeat(2,1fr);}}
.dsmy-card{position:relative;border-radius:8px;overflow:hidden;background:#111;cursor:pointer;aspect-ratio:16/9;}
.dsmy-card img{width:100%;height:100%;object-fit:contain;background:#111;display:block;transition:transform .25s;}
.dsmy-card:hover img{transform:scale(1.03);}
.dsmy-card-tag{position:absolute;top:5px;left:5px;background:rgba(0,0,0,.7);color:#64748b;font-size:9px;padding:2px 6px;border-radius:3px;backdrop-filter:blur(4px);}
.dsmy-card-sub{position:absolute;top:5px;right:5px;background:rgba(37,99,235,.8);color:#fff;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;}
.dsmy-card-exp{position:absolute;bottom:5px;left:5px;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;color:#fff;}
.dsmy-card-exp-expired{background:rgba(220,38,38,.88);}
.dsmy-card-exp-temp,.dsmy-card-exp-saved{background:transparent;padding:0;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 1px 2px rgba(0,0,0,.7));}
.dsmy-card-expired-ph{display:flex;align-items:center;justify-content:center;height:100%;color:#64748b;font-size:11px;text-align:center;padding:10px;}
.dsmy-card-actions{position:absolute;bottom:5px;right:5px;display:flex;gap:2px;opacity:.9;transition:opacity .2s;}
.dsmy-card:hover .dsmy-card-actions{opacity:1;}
.dsmy-btn-sm{width:26px !important;height:26px !important;border-radius:5px !important;background:transparent !important;border:none !important;outline:none !important;box-shadow:none !important;padding:0 !important;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;filter:drop-shadow(0 1px 3px rgba(0,0,0,.85));}
.dsmy-btn-sm:hover{background:rgba(255,255,255,.18) !important;}.ds-spin{width:18px;height:18px;border:2px solid #1a1a1a;border-top-color:#2563eb;border-radius:50%;animation:dsSpin .7s linear infinite;}@keyframes dsSpin{to{transform:rotate(360deg)}}.ds-img-spin{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1;pointer-events:none;}.dsmy-card img[data-fid]{opacity:0;transition:opacity .25s;}.dsmy-card img[data-fid].ds-img-loaded{opacity:1;}
.dsmy-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:340px;gap:14px;opacity:.3;}
.dsmy-empty svg{opacity:.5;}
/* Lightbox */
#dsmy-lb{display:none;position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.96);align-items:center;justify-content:center;flex-direction:column;}
#dsmy-lb.open{display:flex!important;}
#dsmy-lb img{max-width:92vw;max-height:78vh;border-radius:10px;object-fit:contain;box-shadow:0 8px 60px rgba(0,0,0,.9);}
#dsmy-lb-exp{color:#fbbf24;font-size:12px;max-width:600px;text-align:center;margin:8px 20px 0;display:none;}
.dsmy-lb-btns{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;justify-content:center;}
.dsmy-lb-btn{padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:flex;align-items:center;gap:7px;font-family:inherit;}
/* Modals */
.dsmy-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999999;align-items:center;justify-content:center;}
.dsmy-modal-overlay.open{display:flex;}
.dsmy-modal{background:#fdf9f3;border-radius:18px;padding:28px;max-width:420px;width:92%;border:1px solid #e7e0d3;box-shadow:0 20px 60px rgba(0,0,0,.4);}
..dsmy-modal h3{font-size:20px;font-weight:800;color:#1a1a2e;margin:0 0 8px;letter-spacing:-.3px;}
.dsmy-modal p{font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;}
.dsmy-modal input{width:100%;padding:11px 13px;border-radius:8px;border:1px solid #d8d0bf;background:#fff;color:#1a1a2e;font-size:13px;outline:none;box-sizing:border-box;margin-bottom:10px;font-family:inherit;}
.dsmy-modal label{color:#3d4659 !important;display:flex !important;width:100% !important;max-width:100% !important;box-sizing:border-box !important;align-items:center !important;gap:10px !important;margin:14px 0 18px !important;font-size:13px !important;line-height:1.5 !important;cursor:pointer !important;}
.dsmy-modal label input[type="checkbox"]{width:18px !important;height:18px !important;flex-shrink:0 !important;margin:0 !important;accent-color:#d97706 !important;cursor:pointer !important;}
.dsmy-modal label span{flex:1 !important;color:#3d4659 !important;}
.dsmy-modal-btns{display:flex;gap:8px;margin-top:6px;}
.dsmy-modal-btn{flex:1;padding:12px;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;}
.dsmy-cashback-note{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:11px 13px;font-size:12px;color:#15803d;margin-bottom:14px;line-height:1.6;}
.dsmy-cashback-note strong{color:#15803d;}
.dsmy-share-box{background:#fdf9f3 !important;border:1px solid #e7e0d3 !important;}
.dsmy-share-box h3{color:#1a1a2e !important;}
.dsmy-share-box p{color:#5a6478 !important;}
.dsmy-card-acc{background:#10b981 !important;color:#fff !important;}
.dsmy-success-modal{max-width:380px !important;text-align:center;position:relative;overflow:hidden;}
.dsmy-success-emoji{font-size:64px;text-align:center;margin:8px 0 12px;animation:dsmyBounce .6s ease-out;}
@keyframes dsmyBounce{0%{transform:scale(0) rotate(-180deg);opacity:0;}60%{transform:scale(1.2) rotate(10deg);}100%{transform:scale(1) rotate(0);opacity:1;}}
.dsmy-confetti{position:absolute;inset:0;pointer-events:none;overflow:hidden;}
.dsmy-confetti span{position:absolute;width:8px;height:14px;top:-20px;animation:dsmyFall linear forwards;}
@keyframes dsmyFall{0%{transform:translateY(0) rotate(0);opacity:1;}100%{transform:translateY(420px) rotate(720deg);opacity:0;}}
/* Share modal */
.dsmy-share-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;}
.dsmy-share-btn{padding:9px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:inherit;color:#fff;}
.dsmy-share-preview{width:100%;border-radius:10px;max-height:160px;object-fit:cover;background:#0d1424;margin-bottom:14px;display:block;}
</style>

<!-- LIGHTBOX -->
<div id="dsmy-lb" onclick="if(event.target===this)dsmyLBClose()">
<button onclick="dsmyLBClose()" style="position:absolute;top:16px;right:20px;width:40px;height:40px;min-width:40px;min-height:40px;padding:0;line-height:1;box-sizing:border-box;border-radius:50%;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;">X</button>
<img id="dsmy-lb-img" src="" alt=""/>
<p id="dsmy-lb-cap" style="color:#64748b;font-size:12px;max-width:600px;text-align:center;margin:10px 20px 0;"></p>
<p id="dsmy-lb-exp"></p>
<div class="dsmy-lb-btns">
<button class="dsmy-lb-btn" id="dsmy-lb-dl-btn" onclick="dsmyLBDL()" style="background:#2563eb;color:#fff;">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2"/></svg>
Download
</button>
<button class="dsmy-lb-btn" onclick="dsmyLBShare()" style="background:#7c3aed;color:#fff;">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2"/></svg>
Share
</button>
<button class="dsmy-lb-btn" id="dsmy-lb-mkt-btn" onclick="dsmyLBMarket()" style="background:#d97706;color:#fff;">+ Marketplace</button>
<button class="dsmy-lb-btn" id="dsmy-lb-save-btn" onclick="dsmyLBSave()" style="background:#059669;color:#fff;display:none;">Save to My Creations</button>
<button class="dsmy-lb-btn" onclick="dsmyLBClose()" style="background:#1e2d4a;color:#94a3b8;">Close</button>
<button class="dsmy-lb-btn" id="dsmy-lb-del-btn" onclick="dsmyLBDelete()" style="background:#7f1d1d;color:#fca5a5;">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="#fca5a5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="#fca5a5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="#fca5a5" stroke-width="2.2" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="#fca5a5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
Delete
</button>
</div>
</div>

<!-- SHARE MODAL -->
<div class="dsmy-modal-overlay" id="dsmy-share-modal">
<div class="dsmy-modal">
<h3>Share this Design</h3>
<p>Created with Designstager AI Studio</p>
<img id="dsmy-share-preview" class="dsmy-share-preview" src="" alt=""/>
<div style="background:linear-gradient(135deg,#0f1f3d,#1e1040);border-radius:10px;padding:12px 14px;margin-bottom:14px;">
<div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:3px;">Designstager AI Studio</div>
<div style="font-size:11px;color:rgba(255,255,255,.4);">Create stunning AI designs for free - designstager-com-895433.hostingersite.com</div>
</div>
<div class="dsmy-share-grid">
<button class="dsmy-share-btn" style="background:#25D366;" onclick="dsmyShareVia('whatsapp')">WhatsApp</button>
<button class="dsmy-share-btn" id="dsmy-copy-btn" style="background:#6366f1;" onclick="dsmyShareVia('copy')">Copy Link</button>
<button class="dsmy-share-btn" style="background:#1da1f2;" onclick="dsmyShareVia('twitter')">Twitter / X</button>
<button class="dsmy-share-btn" style="background:#1877f2;" onclick="dsmyShareVia('facebook')">Facebook</button>
</div>
<button onclick="dsmyShareClose()" style="width:100%;padding:9px;border-radius:8px;border:1.5px solid #2d3a55;background:transparent;color:#64748b;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;">Close</button>
</div>
</div>

<!-- SUCCESS MODAL -->
<div class="dsmy-modal-overlay" id="dsmy-success-modal">
<div class="dsmy-modal dsmy-success-modal">
<div class="dsmy-confetti" id="dsmy-confetti"></div>
<div class="dsmy-success-emoji"></div>
<h3 style="text-align:center;">Submitted Successfully!</h3>
<p style="text-align:center;" id="dsmy-success-msg">Your design is awaiting review.</p>
<div class="dsmy-modal-btns">
<button class="dsmy-modal-btn" onclick="dsmySuccessClose()" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;">Awesome!</button>
</div>
</div>
</div>

<!-- MARKETPLACE MODAL -->
<div class="dsmy-modal-overlay" id="dsmy-mkt-modal">
<div class="dsmy-modal"><?php if ($is_premium): ?>
<h3>Submit to Marketplace</h3>
<p>Share your design and earn credits on approval.</p>
<p style="font-size:12px;color:#94a3b8;margin:0 0 8px;line-height:1.5;">Your design will be submitted using the original prompt as the title.</p>
<div class="dsmy-cashback-note">Earn <strong id="dsmy-cb-amt"><?php echo $cashback_pct; ?>%</strong> (1 credit) back when admin approves.</div>
<label>
<input type="checkbox" id="dsmy-mkt-consent" style="margin-top:2px;flex-shrink:0;cursor:pointer;"/>
<span>I confirm I have the right to submit this AI-generated image to the Designstager Marketplace and grant Designstager a license to display and distribute it.</span>
</label>
<div class="dsmy-modal-btns">
<button class="dsmy-modal-btn" onclick="dsmyMktClose()" style="background:#ece6d6;color:#3d4659;">Cancel</button>
<button class="dsmy-modal-btn" id="dsmy-mkt-submit-btn" onclick="dsmyMktSubmit()" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;opacity:.5;pointer-events:none;">Submit to Marketplace</button>
<?php else: ?>
    <p style="font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;">Submitting to the Marketplace and earning credit back is a Premium feature.</p>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:11px 13px;font-size:12px;color:#9a3412;margin-bottom:18px;line-height:1.6;">
      Upgrade to Premium to submit your images and earn <strong>1 credit</strong> back when admin approves.
    </div>
    <div style="display:flex;gap:8px;">
      <button onclick="dsmyMktClose()" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;">Maybe Later</button>
      <a href="/pricing" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;text-align:center;text-decoration:none;display:inline-block;box-sizing:border-box;">Upgrade to Premium</a>
    </div>
<?php endif; ?>
</div>
</div>
</div>

<div id="dsmc-hub">
<style>
.dsmc-tabs{display:flex;gap:0;background:#080808;border-bottom:1px solid #141414;border-radius:10px 10px 0 0;overflow:hidden;}
.dsmc-tab{flex:1;padding:14px 10px;text-align:center;border:none;background:transparent;color:#64748b;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;border-bottom:2px solid transparent;transition:all .15s;}
.dsmc-tab.active{color:#60a5fa;border-bottom-color:#3b82f6;background:#0d1929;}
.dsmc-tab:hover:not(.active){color:#94a3b8;}
.dsmc-capbar{background:#0a0a0a;color:#94a3b8;font-size:12px;padding:8px 16px;text-align:right;border-bottom:1px solid #141414;}
.dsmc-capbar b{color:#f1f5f9;}
.dsmc-capbar.dsmc-capfull b{color:#f87171;}
.dsmc-panel{display:none;}
.dsmc-panel.active{display:block;}
.dsmv-grid,.dsmp-list{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:14px 16px;}
@media(max-width:900px){.dsmv-grid,.dsmp-list{grid-template-columns:repeat(3,1fr);}}
@media(max-width:600px){.dsmv-grid,.dsmp-list{grid-template-columns:repeat(2,1fr);}}
.dsmv-card{background:#111;border-radius:8px;overflow:hidden;border:1px solid #1e1e1e;}
.dsmv-card video{width:100%;aspect-ratio:16/9;background:#000;display:block;}
.dsmv-meta{padding:8px 10px;}
.dsmv-prompt{font-size:11px;color:#cbd5e1;margin:0 0 6px;line-height:1.4;max-height:32px;overflow:hidden;}
.dsmv-badge{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px;margin-bottom:6px;}
.dsmv-badge.expired{background:#2a0505;color:#f87171;}
.dsmv-badge.saved,.dsmv-badge.temp{background:transparent;padding:0;display:inline-flex;align-items:center;justify-content:center;}
.dsmv-actions{display:flex;gap:5px;padding:0 10px 10px;}
.dsmv-btn{flex:1;text-align:center;padding:6px 4px;font-size:10.5px;font-weight:700;border-radius:6px;border:1px solid #262626;background:#1a1a1a;color:#cbd5e1;cursor:pointer;text-decoration:none;}
.dsmv-btn:hover{background:#242424;}
.dsmv-btn.save{color:#4ade80;}
.dsmv-btn.delete{color:#f87171;}
.dsmv-empty,.dsmv-loading{grid-column:1/-1;text-align:center;padding:60px 20px;color:#64748b;font-size:13px;}
.dsmp-section{padding:14px 16px;}
.dsmp-section h3{font-size:13px;color:#f1f5f9;margin:0 0 8px;font-weight:700;}
.dsmp-card{background:#111;border:1px solid #1e1e1e;border-radius:8px;overflow:hidden;}
.dsmp-thumb{aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;padding:12px;text-align:center;box-sizing:border-box;overflow:hidden;}
.dsmp-thumb-title{font-size:12px;font-weight:700;line-height:1.35;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.dsmp-thumb-sub{font-size:10px;line-height:1.3;margin-top:4px;opacity:.8;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.dsmp-thumb-generic{background:linear-gradient(135deg,#1e293b,#0f172a);}
.dsmp-thumb-generic .dsmp-thumb-title{color:#e2e8f0;}
.dsmp-thumb-photo{background-size:cover;background-position:center;position:relative;}
.dsmp-thumb-photo .dsmp-thumb-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 35%,rgba(0,0,0,.78) 100%);display:flex;align-items:flex-end;padding:10px;box-sizing:border-box;}
.dsmp-thumb-photo .dsmp-thumb-title{color:#fff;}
.dsmp-thumb-photo .dsmp-thumb-sub{color:#f1f5f9;}
.dsmp-thumb-icon{font-size:26px;margin-bottom:6px;opacity:.5;}
.dsmp-body{padding:8px 10px 0;}
.dsmp-card h4{font-size:12px;color:#f1f5f9;margin:0 0 4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.dsmp-card p{font-size:10.5px;color:#64748b;margin:0 0 8px;}
#dsmc-topbar-wrap{display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:wrap;background:#0a0a0a;padding:10px 16px;border-bottom:1px solid #141414;}
#dsmc-capbar{background:#111827;border:1px solid #1f2937;border-radius:8px;padding:6px 14px;text-align:center;margin:0;}
#dsmc-capbar:hover{opacity:.85;text-decoration:none;}
#dsme-badge{display:inline-flex;align-items:center;background:#111827;border:1px solid #1f2937;border-radius:8px;padding:6px 14px;font-size:12px;color:#94a3b8;cursor:pointer;white-space:nowrap;}
#dsme-badge:hover{opacity:.85;}

#dsmc-toprow{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;background:#080808;padding:8px 16px;border-bottom:1px solid #141414;border-radius:10px 10px 0 0;}
.dsmc-tabs{background:transparent;border-bottom:none;border-radius:0;width:auto;gap:6px;}
.dsmc-tab{flex:0 0 auto;padding:8px 14px;font-size:12px;border:1px solid #1f2937;border-radius:6px;background:#111827;}
.dsmc-tab.active{background:#f97316;color:#fff;border-color:#f97316;}
#dsmc-topbar-wrap{background:transparent;padding:0;border-bottom:none;margin:0;}
</style>
<div class="dsmc-tabs">
<button type="button" class="dsmc-tab active" onclick="dsmcSwitch('images', this)">Images</button>
<button type="button" class="dsmc-tab" onclick="dsmcSwitch('videos', this)">Videos</button>
<button type="button" class="dsmc-tab" onclick="dsmcSwitch('presentations', this)">Presentations</button>
<button type="button" class="dsmc-tab" onclick="dsmcSwitch('audio', this)">Audio</button>
</div>
<div class="dsmc-capbar<?php echo ($__cap_used >= $__cap_total ? ' dsmc-capfull' : ''); ?>" id="dsmc-capbar"><b id="dsmc-capused"><?php echo (int)$__cap_used; ?></b> / <b id="dsmc-captotal"><?php echo (int)$__cap_total; ?></b> saved items used</div>
<div id="dsmc-panel-images" class="dsmc-panel active">
<div id="ds-myimgs">

<!-- HEADER -->
<div class="dsmy-header">
<div class="dsmy-header-left">
<h2>My Generated Images</h2>
<p><?php echo count($gens); ?> images generated</p>
</div>
<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
<div class="dsmy-credits">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#f59e0b"/></svg>
<span id="dsmy-cr-val"><?php echo number_format($credits); ?></span> Credits
<a href="<?php echo home_url('/pricing'); ?>">Buy More</a>
</div>
<a href="<?php echo home_url('/ai-studio'); ?>" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:600;background:#0d1929;padding:6px 14px;border-radius:100px;border:1px solid #1e3a5f;display:flex;align-items:center;gap:5px;">
<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#60a5fa"/></svg>
AI Studio
</a>
</div>
</div>

<?php if (empty($gens)) : ?>
<div class="dsmy-body">
<div class="dsmy-empty">
<svg width="56" height="56" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#fff" stroke-width="1.5"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="#fff" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" fill="#fff"/></svg>
<h3 style="font-size:18px;font-weight:800;color:#f1f5f9;margin:0;">No images yet</h3>
<p style="color:#64748b;font-size:13px;margin:0;text-align:center;">Generate your first AI image in the studio.</p>
<a href="<?php echo home_url('/ai-studio'); ?>" style="display:inline-block;padding:11px 28px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-weight:700;text-decoration:none;border-radius:10px;font-size:14px;">Open AI Studio</a>
</div>
</div>

<?php else : ?>

<!-- TABS -->
<div class="dsmy-tabs">
<?php
$count_submitted = 0; $count_accepted = 0;
foreach ($gens as $g) {
if (!is_array($g)) continue;
$sub_chk = !empty($g['submitted']);
$aid_chk = (int)($g['attachment_id'] ?? 0);
if ($sub_chk && $aid_chk) {
$dlid = (int)get_post_meta($aid_chk, 'ds_marketplace_download_id', true);
if ($dlid && get_post_status($dlid) === 'publish') { $count_accepted++; }
else { $count_submitted++; }
}
}
?>
<button class="dsmy-tab active" onclick="dsmyFilter('all',this)">All (<?php echo count($gens); ?>)</button>
<button class="dsmy-tab" onclick="dsmyFilter('submitted',this)">Submitted (<?php echo $count_submitted; ?>)</button>
</div>

<!-- GRID -->
<div class="dsmy-body">
<div class="dsmy-grid" id="dsmy-grid">
<?php
$ds_page_size = 20;
$ds_valid_gens = ds_hub_filter_valid_gens($gens);
$ds_initial = array_slice($ds_valid_gens, 0, $ds_page_size);
foreach ($ds_initial as $gen) {
echo ds_hub_render_my_image_card($gen, $nonce);
}
?>
</div>
<?php if (count($ds_valid_gens) > $ds_page_size): ?>
<div style="text-align:center;padding:4px 16px 18px;">
<button type="button" id="dsmy-loadmore-btn" class="dsmy-lb-btn" style="background:#1e2d4a;color:#93c5fd;display:inline-flex;" onclick="dsmyLoadMoreImages(this)" data-offset="<?php echo (int)$ds_page_size; ?>" data-total="<?php echo (int)count($ds_valid_gens); ?>">Load More (<?php echo (int)(count($ds_valid_gens) - $ds_page_size); ?> more)</button>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

</div>
</div><!-- /dsmc-panel-images -->

<script>
(function(){
var NONCE='<?php echo esc_js($nonce); ?>';
var HUBFILES='<?php echo esc_js( rest_url('ds-hub/v1/files') ); ?>';
var REST='<?php echo esc_js($rest); ?>';
var REST_HUB='<?php echo esc_js($rest_hub); ?>';
var SUBURL='<?php echo $suburl; ?>';
var SITE='<?php echo $site; ?>';
var G={lbUrl:'',lbPrompt:'',lbAid:0,lbCb:0,lbFileId:0,shareUrl:'',sharePrompt:'',mktAid:0,mktCb:0,curFilter:'all'};

function q(id){return document.getElementById(id);}

// TABS
window.dsmyFilter=function(f,btn){
document.querySelectorAll('.dsmy-tab').forEach(function(b){b.classList.remove('active');});
btn.classList.add('active');
G.curFilter=f;
var imgBody=document.querySelector('.dsmy-body:not(#dsmy-ppt-section)');
var pptBody=document.getElementById('dsmy-ppt-section');
if(f==='ppts'){
if(imgBody)imgBody.style.display='none';
if(pptBody)pptBody.style.display='';
} else {
if(imgBody)imgBody.style.display='';
if(pptBody)pptBody.style.display='none';
document.querySelectorAll('.dsmy-card').forEach(function(c){
c.style.display=(f==='all'||c.getAttribute('data-filter')===f)?'':'none';
});
}
};

// LIGHTBOX
window.dsmyOpenLB=function(url,prompt,aid,cb,locked,pct,fileId,expLabel){
G.lbUrl=url;G.lbPrompt=prompt;G.lbAid=aid;G.lbCb=cb;G.lbPct=pct||50;G.lbFileId=fileId||0;
q('dsmy-lb-img').src=url;
q('dsmy-lb-cap').textContent=prompt?'"'+prompt.slice(0,100)+'"':'';
var mktBtn=q('dsmy-lb-mkt-btn');if(mktBtn)mktBtn.style.display=(aid&&!locked)?'flex':'none';
var delBtn=q('dsmy-lb-del-btn');if(delBtn)delBtn.style.display=((aid||fileId)&&!locked)?'flex':'none';
var dlBtn=q('dsmy-lb-dl-btn');if(dlBtn)dlBtn.style.display=locked&&!aid&&!fileId?'flex':'flex';
var saveBtn=q('dsmy-lb-save-btn');
if(saveBtn){
saveBtn.textContent='Save to My Creations';
saveBtn.disabled=false;
saveBtn.style.display=(fileId&&(expLabel||'').indexOf('Saved')!==0)?'flex':'none';
}
var expEl=q('dsmy-lb-exp');
if(expEl){expEl.textContent=expLabel||'';expEl.style.display=expLabel?'block':'none';}
q('dsmy-lb').classList.add('open');
document.body.style.overflow='hidden';
};
window.dsmyLBClose=function(){q('dsmy-lb').classList.remove('open');q('dsmy-lb-img').src='';document.body.style.overflow='';};
window.dsmyLBDL=function(){if(G.lbUrl)dsmyDL(G.lbUrl,G.lbFileId);};
window.dsmyLBShare=function(){dsmyLBClose();dsmyOpenShare(G.lbUrl,G.lbPrompt,G.lbAid||0);};
window.dsmyLBMarket=function(){if(G.lbAid||G.lbFileId){dsmyLBClose();dsmyOpenMkt(G.lbAid,G.lbCb,G.lbPct,G.lbFileId);}};

// SAVE TO MY CREATIONS (R2 files only)
window.dsmyCardSave=function(fid,btn){
if(!fid)return;
if(btn){btn.disabled=true;btn.style.opacity='.45';}
fetch(REST_HUB+'save/'+fid,{method:'POST',headers:{'X-WP-Nonce':NONCE}})
.then(function(r){return r.json();})
.then(function(d){
if(d&&d.success){location.reload();return;}
if(btn){btn.disabled=false;btn.style.opacity='';}
alert('Could not save this file. Please try again.');
})
.catch(function(){if(btn){btn.disabled=false;btn.style.opacity='';}alert('Could not save this file. Please try again.');});
};
window.dsmyLBSave=function(){
if(!G.lbFileId)return;
var btn=q('dsmy-lb-save-btn');
if(btn){btn.disabled=true;btn.textContent='Saving...';}
fetch(REST_HUB+'save/'+G.lbFileId,{method:'POST',headers:{'X-WP-Nonce':NONCE}})
.then(function(r){return r.json();})
.then(function(d){
if(btn){btn.disabled=false;btn.textContent=d&&d.success?'Saved!':'Save to My Creations';}
var expEl=q('dsmy-lb-exp');
if(d&&d.success&&expEl){
expEl.style.display='block';
expEl.textContent=d.expires_at?('Saved to My Creations — expires '+d.expires_at+'.'):'Saved to My Creations — kept permanently.';
}
if(d&&d.success)setTimeout(function(){location.reload();},900);
})
.catch(function(){if(btn){btn.disabled=false;btn.textContent='Save to My Creations';}alert('Could not save this file. Please try again.');});
};

// DELETE
window.dsmyDelete=function(aid,url,fileId){
if(!aid&&!fileId)return;
if(!confirm('Delete this image permanently? This cannot be undone.'))return;
var req=fileId
? fetch(REST_HUB+'delete/'+fileId,{method:'POST',headers:{'X-WP-Nonce':NONCE}})
: fetch(REST+'delete-image',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify({attachment_id:aid})});
req.then(function(r){return r.json();})
.then(function(d){
if(d&&(d.success===true)){
// Remove card from grid
var cards=document.querySelectorAll('.dsmy-card');
cards.forEach(function(c){
if(c.getAttribute('onclick')&&c.getAttribute('onclick').indexOf('dsmyOpenLB(\''+url+'\'')!==-1){
c.remove();
}
});
dsmyLBClose();
} else {
alert('Could not delete: '+((d&&(d.error||d.message))||'Unknown error'));
}
})
.catch(function(){alert('Delete failed. Please try again.');});
};
window.dsmyLBDelete=function(){
if(G.lbAid||G.lbFileId)dsmyDelete(G.lbAid,G.lbUrl,G.lbFileId);
};
// DOWNLOAD
window.dsmyDL=function(url,fileId){
if(!url)return;
// R2-backed file — the URL is already our stable proxy, which redirects to a
// fresh short-lived presigned R2 URL with a forced "Save As" (Content-Disposition).
if(fileId){
var ra=document.createElement('a');ra.href=url;ra.target='_blank';ra.rel='noopener';
document.body.appendChild(ra);ra.click();setTimeout(function(){document.body.removeChild(ra);},200);
return;
}
var fn='designstager-ai-'+Date.now()+'.png';
if(url.indexOf('data:')===0){var a=document.createElement('a');a.href=url;a.download=fn;document.body.appendChild(a);a.click();setTimeout(function(){document.body.removeChild(a);},200);return;}
var proxy=REST+'download?url='+encodeURIComponent(url)+'&_wpnonce='+NONCE;
var a=document.createElement('a');a.href=proxy;a.download=fn;a.target='_self';document.body.appendChild(a);a.click();setTimeout(function(){document.body.removeChild(a);},500);
};

// SHARE
window.dsmyOpenShare=function(url,prompt,aid){
G.sharePrompt=prompt||'';
// If we have an attachment_id, share the dedicated viewer URL (gets proper OG preview)
// Otherwise fall back to the raw image URL.
G.shareUrl=(aid&&aid>0)?(SITE+'/?ds_share_img='+aid):url;
q('dsmy-share-preview').src=url;
q('dsmy-share-modal').classList.add('open');
};
window.dsmyShareClose=function(){q('dsmy-share-modal').classList.remove('open');};
window.dsmyShareVia=function(ch){
var url=G.shareUrl;
var msg='I created this with Designstager AI Studio! Create stunning AI designs for free at '+SITE;
var enc=encodeURIComponent(msg+'\n'+url);
if(ch==='whatsapp')window.open('https://wa.me/?text='+enc,'_blank');
else if(ch==='twitter')window.open('https://twitter.com/intent/tweet?text='+encodeURIComponent(msg)+'&url='+encodeURIComponent(url),'_blank');
else if(ch==='facebook')window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(url)+'&quote='+encodeURIComponent(msg),'_blank');
else if(ch==='copy'){
navigator.clipboard.writeText(msg+'\n'+url).then(function(){
var btn=q('dsmy-copy-btn');if(btn){btn.textContent='Copied!';setTimeout(function(){btn.textContent='Copy Link';},2000);}
}).catch(function(){});
}
};

// MARKETPLACE
window.dsmyOpenMkt=function(aid,cb,pct,fileId){
G.mktAid=aid;G.mktCb=cb;G.mktFileId=fileId||0;
var cbEl=q('dsmy-cb-amt');if(cbEl)cbEl.textContent=(pct||50)+'% (1 credit) back';
var consent=q('dsmy-mkt-consent');if(consent)consent.checked=false;
var sb=q('dsmy-mkt-submit-btn');if(sb){sb.style.opacity='.5';sb.style.pointerEvents='none';}
q('dsmy-mkt-modal').classList.add('open');
};
window.dsmyMktClose=function(){q('dsmy-mkt-modal').classList.remove('open');};
window.dsmySuccessOpen=function(){
q('dsmy-success-modal').classList.add('open');
var c=q('dsmy-confetti');if(!c)return;c.innerHTML='';
var colors=['#d97706','#f59e0b','#10b981','#3b82f6','#ec4899','#8b5cf6','#ef4444','#facc15'];
for(var i=0;i<40;i++){
var s=document.createElement('span');
s.style.left=Math.random()*100+'%';
s.style.background=colors[Math.floor(Math.random()*colors.length)];
s.style.animationDuration=(1.5+Math.random()*1.5)+'s';
s.style.animationDelay=Math.random()*0.4+'s';
s.style.transform='rotate('+Math.random()*360+'deg)';
c.appendChild(s);
}
setTimeout(function(){c.innerHTML='';},3500);
};
window.dsmySuccessClose=function(){q('dsmy-success-modal').classList.remove('open');location.reload();};
window.dsmyMktSubmit=function(){
var consent=q('dsmy-mkt-consent');
if(!consent||!consent.checked){alert('Please tick the consent checkbox to continue.');return;}
var t='auto';
var btn=q('dsmy-mkt-submit-btn');
if(btn){btn.textContent='Submitting...';btn.disabled=true;}
fetch(REST+'creator-from-image',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify({attachment_id:G.mktAid,file_id:G.mktFileId,consent:true})})
.then(function(r){return r.json();})
.then(function(d){
dsmyMktClose();
if(btn){btn.textContent='Submit to Marketplace';btn.disabled=false;}
if(d.success){
var msgEl=q('dsmy-success-msg');
if(msgEl)msgEl.textContent='Your design is submitted for review!';
dsmySuccessOpen();
// Save submitted status to ds_ai_generations so it persists after reload
fetch(REST+'mark-submitted',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify({attachment_id:d.attachment_id||G.mktAid,file_id:d.file_id||G.mktFileId})});
// Hide marketplace button on the card
document.querySelectorAll('.dsmy-card').forEach(function(card){
var oc=card.getAttribute('onclick')||'';
if(card.dataset.cardKey===(G.mktAid?String(G.mktAid):('f'+G.mktFileId))){
var mkBtn=card.querySelector('.mk');
if(mkBtn) mkBtn.style.display='none';
// Add submitted badge
var sub=document.createElement('span');
sub.className='dsmy-card-sub';
sub.textContent='Submitted';
card.appendChild(sub);
}
});
// Hide in lightbox
var lbMktBtn=q('dsmy-lb-mkt-btn');
if(lbMktBtn) lbMktBtn.style.display='none';
var lbDelBtn=q('dsmy-lb-del-btn');
if(lbDelBtn) lbDelBtn.style.display='none';
} else {
alert('Failed: '+(d.error||'Please try again.'));
}
}).catch(function(){dsmyMktClose();if(btn){btn.textContent='Submit to Marketplace';btn.disabled=false;}});
};

// Enable/disable submit button based on consent checkbox
document.addEventListener('change',function(e){
if(e.target&&e.target.id==='dsmy-mkt-consent'){
var sb=q('dsmy-mkt-submit-btn');
if(sb){
if(e.target.checked){sb.style.opacity='1';sb.style.pointerEvents='auto';}
else{sb.style.opacity='.5';sb.style.pointerEvents='none';}
}
}
});

// Overlay close
[q('dsmy-share-modal'),q('dsmy-mkt-modal')].forEach(function(m){
if(m)m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){dsmyLBClose();dsmyShareClose();dsmyMktClose();}});
// Refresh credit display from live DB on load
(function(){
fetch(REST+'credits',{headers:{'X-WP-Nonce':NONCE}})
.then(function(r){return r.json();})
.then(function(d){
if(d&&d.credits!==undefined){
var el=q('dsmy-cr-val');
if(el)el.textContent=d.credits.toLocaleString();
var hdr=document.getElementById('ds-cr-display');
if(hdr)hdr.textContent=d.credits.toLocaleString();
}
}).catch(function(){});
})();
function dsmySetImgSrc(img, url){
img.onload = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
img.onerror = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
img.src = url;
}
window.dsmyResolveImgSrcs = function(root){
root = root || document;
var els = Array.prototype.slice.call(root.querySelectorAll('img[data-fid]:not([data-resolved])'));
var byFid = {}; var ids = [];
els.forEach(function(img){
img.setAttribute('data-resolved','1');
var fid = img.getAttribute('data-fid');
if (fid && fid !== '0') {
if (!byFid[fid]) { byFid[fid] = []; ids.push(fid); }
byFid[fid].push(img);
} else {
var fb0 = img.getAttribute('data-fallback');
if (fb0) dsmySetImgSrc(img, fb0);
}
});
if (!ids.length) return;
function chunk(arr,size){var out=[];for(var k=0;k<arr.length;k+=size)out.push(arr.slice(k,k+size));return out;}
chunk(ids, 40).forEach(function(group){
fetch(HUBFILES+'?ids='+group.join(',')+'&variant=thumb&_wpnonce='+NONCE, {credentials:'include'})
.then(function(r){return r.json();})
.then(function(map){
group.forEach(function(fid){
var url = map && map[fid];
(byFid[fid]||[]).forEach(function(img){
if (url) { dsmySetImgSrc(img, url); } else { var fb = img.getAttribute('data-fallback'); if (fb) dsmySetImgSrc(img, fb); }
});
});
})
.catch(function(){
group.forEach(function(fid){
(byFid[fid]||[]).forEach(function(img){ var fb = img.getAttribute('data-fallback'); if (fb) dsmySetImgSrc(img, fb); });
});
});
});
};
dsmyResolveImgSrcs(document);

window.dsmyLoadMoreImages = function(btn){
if (!btn) btn = q('dsmy-loadmore-btn');
if (!btn) return;
var offset = parseInt(btn.getAttribute('data-offset'), 10) || 0;
var total = parseInt(btn.getAttribute('data-total'), 10) || 0;
btn.disabled = true;
var origText = btn.textContent;
btn.textContent = 'Loading...';
fetch(REST + 'my-images-page?offset=' + offset, { headers: {'X-WP-Nonce': NONCE}, credentials: 'include' })
.then(function(r){ return r.json(); })
.then(function(d){
if (!d || !d.success) { btn.disabled = false; btn.textContent = origText; return; }
var grid = q('dsmy-grid');
if (grid && d.html) {
var tmp = document.createElement('div');
tmp.innerHTML = d.html;
while (tmp.firstChild) { grid.appendChild(tmp.firstChild); }
}
dsmyResolveImgSrcs(grid);
if (G.curFilter && G.curFilter !== 'all') {
document.querySelectorAll('#dsmy-grid .dsmy-card').forEach(function(c){
c.style.display = (c.getAttribute('data-filter') === G.curFilter) ? '' : 'none';
});
}
var nextOffset = d.next_offset || (offset + (d.count || 0));
btn.setAttribute('data-offset', nextOffset);
if (nextOffset >= total) {
btn.remove();
} else {
btn.disabled = false;
btn.textContent = 'Load More (' + (total - nextOffset) + ' more)';
}
})
.catch(function(){ btn.disabled = false; btn.textContent = origText; });
};


})();
</script>
<div id="dsmc-panel-videos" class="dsmc-panel">
<div id="dsmv-body"><div class="dsmv-loading">Loading videos...</div></div>
</div>
<div id="dsmc-panel-presentations" class="dsmc-panel">
<div class="dsmp-section">
<h3>Saved Drafts</h3>
<div id="dsmp-drafts" class="dsmp-list"><div class="dsmv-loading">Loading...</div></div>
</div>
<div class="dsmp-section">
<h3>Downloaded Presentations</h3>
<div id="dsmp-built" class="dsmp-list"><div class="dsmv-loading">Loading...</div></div>
</div>
</div>
<div id="dsmc-panel-audio" class="dsmc-panel">
<div id="dsma-body"><div class="dsmv-loading">Loading audio...</div></div>
</div>
<script>
(function(){
var DMC_REST = '<?php echo esc_js($rest); ?>';
var DMC_HUB = '<?php echo esc_js($rest_hub); ?>';
var DSMV_SAVED_ICON = '<?php echo $__dsmy_saved_icon; ?>';
var DMC_NONCE = '<?php echo esc_js($nonce); ?>';
var DMC_SITE = '<?php echo esc_js($site); ?>';
var vLoaded = false, pLoaded = false, aLoaded = false;
var vOffset = 0, vTotal = 0, vCount = 0;
var pdOffset = 0, pdTotal = 0, pdCount = 0, pbOffset = 0, pbTotal = 0, pbCount = 0;

function dmcEsc(s){ var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
var DSMP_THEMES={'navy-pro':['#1B2A4A','#FFFFFF'],'dark-modern':['#0D1117','#FFFFFF'],'clean-corporate':['#FFFFFF','#1A365D'],'bold-red':['#1A0000','#FFFFFF'],'corporate-dark':['#111827','#F9FAFB'],'clean-white':['#F8FAFC','#0F172A'],'gradient-pro':['#0F0C29','#FFFFFF'],'bold-impact':['#111111','#FFFFFF'],'sunset-glow':['#2D1B2E','#FFFFFF'],'forest-green':['#0F2419','#FFFFFF'],'ocean-breeze':['#F0F9FF','#0C4A6E'],'golden-hour':['#1C1408','#FFFFFF'],'lavender-mist':['#F5F3FF','#3B0764'],'slate-minimal':['#F8F9FA','#1E293B'],'cherry-bold':['#FFF5F5','#7F1D1D'],'midnight-teal':['#042F2E','#FFFFFF'],'coral-sunset':['#FFF7ED','#7C2D12'],'royal-purple':['#1E0A3C','#FFFFFF'],'mint-fresh':['#F0FDF4','#14532D'],'rose-gold':['#FFF1F2','#881337'],'steel-blue':['#0F172A','#F1F5F9'],'amber-warm':['#1C1917','#FEF3C7'],'arctic-white':['#FFFFFF','#0F172A'],'crimson-night':['#1A0A0A','#FFFFFF'],'aurora':['linear-gradient(135deg,#0F2027,#1A3A4A)','#FFFFFF'],'sunset-blaze':['linear-gradient(135deg,#7B0000,#DD2476)','#FFFFFF'],'cosmic-purple':['linear-gradient(135deg,#1A0050,#4A00E0)','#FFFFFF'],'ocean-depth':['linear-gradient(135deg,#000028,#003366)','#FFFFFF'],'emerald-dusk':['linear-gradient(135deg,#0A2E1A,#134E5E)','#FFFFFF'],'rose-gold-glow':['linear-gradient(135deg,#0D0720,#1D2671)','#FFFFFF'],'amber-blaze':['linear-gradient(135deg,#1A0A00,#7A3800)','#FFFFFF'],'midnight-bloom':['linear-gradient(135deg,#0B0020,#0B3D47)','#FFFFFF'],'deep-navy':['linear-gradient(135deg,#0A1628,#0D3B6E)','#FFFFFF'],'dark-crimson':['linear-gradient(135deg,#1A0000,#5C0000)','#FFFFFF'],'dark-slate':['linear-gradient(135deg,#0F172A,#1E293B)','#F1F5F9'],'dark-forest':['linear-gradient(135deg,#052010,#0A3020)','#FFFFFF']};

window.dsmcSwitch = function(name, btn){
  document.querySelectorAll('#dsmc-hub .dsmc-tabs .dsmc-tab').forEach(function(b){ b.classList.remove('active'); });
  btn.classList.add('active');
  document.querySelectorAll('#dsmc-hub > .dsmc-panel').forEach(function(p){ p.classList.remove('active'); });
  var panel = document.getElementById('dsmc-panel-' + name);
  if (panel) panel.classList.add('active');
  if (name === 'videos') dsmcLoadVideos();
  if (name === 'presentations') dsmcLoadPresentations();
  if (name === 'audio') dsmcLoadAudio();
};

window.dsmcRefreshCap = function(){
  fetch(DMC_REST + 'my-cap', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (!d || !d.success) return;
    var bar = document.getElementById('dsmc-capbar');
    var u = document.getElementById('dsmc-capused');
    var t = document.getElementById('dsmc-captotal');
    if (u) u.textContent = d.used;
    if (t) t.textContent = d.total;
    if (bar) { if (d.used >= d.total) bar.classList.add('dsmc-capfull'); else bar.classList.remove('dsmc-capfull'); }
  })
  .catch(function(){});
};

window.dsmcLoadVideos = function(force){
  if (vLoaded && !force) return;
  vLoaded = true;
  vOffset = 0; vTotal = 0; vCount = 0;
  var body = document.getElementById('dsmv-body');
  body.innerHTML = '<div class="dsmv-loading">Loading videos...</div>';
  fetch(DMC_REST + 'my-videos?offset=0&limit=20', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (!d || !d.success) { body.innerHTML = '<div class="dsmv-empty">Could not load videos.</div>'; return; }
    vTotal = d.total || 0;
    vOffset = d.next_offset || 0;
    dsmcRenderVideos(d.videos || [], false);
  })
  .catch(function(){ body.innerHTML = '<div class="dsmv-empty">Network error.</div>'; });
};

window.dsmcLoadMoreVideos = function(btn){
  if (!btn) return;
  btn.disabled = true;
  var origText = btn.textContent;
  btn.textContent = 'Loading...';
  fetch(DMC_REST + 'my-videos?offset=' + vOffset + '&limit=20', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (!d || !d.success) { btn.disabled = false; btn.textContent = origText; return; }
    vTotal = d.total || 0;
    vOffset = d.next_offset || 0;
    dsmcRenderVideos(d.videos || [], true);
  })
  .catch(function(){ btn.disabled = false; btn.textContent = origText; });
};

function dsmcRenderVideos(list, append){
  var body = document.getElementById('dsmv-body');
  var oldBtnWrap = document.getElementById('dsmv-loadmore-wrap');
  if (oldBtnWrap) oldBtnWrap.remove();
  if (!append) {
    vCount = 0;
    if (!list.length) { body.innerHTML = '<div class="dsmv-empty"><svg width="60" height="60" viewBox="0 0 72 72" fill="none" style="margin:0 auto 14px;display:block;"><circle cx="24" cy="20" r="5" fill="#f59e0b"/><circle cx="38" cy="20" r="5" fill="#f59e0b"/><line x1="46" y1="8" x2="56" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><line x1="56" y1="8" x2="46" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><rect x="10" y="30" width="36" height="28" rx="6" fill="#7c3aed"/><path d="M46 38l14-7v22l-14-7z" fill="#7c3aed"/><path d="M22 38l10 6-10 6z" fill="#fff"/></svg>No videos generated yet</div>'; return; }
    body.innerHTML = '<div class="dsmv-grid" id="dsmv-grid"></div>';
  } else if (!list.length) {
    dsmcAppendVideosLoadMore(body);
    return;
  }
  var grid = document.getElementById('dsmv-grid');
  var startIndex = vCount;
  var html = '';
  list.forEach(function(v, li){
    var i = startIndex + li;
    var badge = '';
    if (v.exp_badge === 'saved') badge = '<span class="dsmv-badge saved" title="Saved"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>';
    else if (v.exp_badge === 'expired') badge = '<span class="dsmv-badge expired">Expired</span>';
    else if (v.exp_badge === 'temp') badge = '<span class="dsmv-badge temp" title="Temporary — will expire soon"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg></span>';
    html += '<div class="dsmv-card">';
    html += '<video controls preload="none" id="dsmv-vid-' + i + '"></video>';
    html += '<div class="dsmv-meta">' + badge + '<p class="dsmv-prompt">' + dmcEsc((v.prompt || '').substring(0, 80)) + '</p></div>';
    html += '<div class="dsmv-actions">';
    if (!v.is_expired) html += '<a class="dsmv-btn" id="dsmv-dl-' + i + '" href="#" target="_blank" rel="noopener">Download</a>';
    if (v.file_id && v.exp_badge !== 'saved' && !v.is_expired) html += '<button type="button" class="dsmv-btn save" onclick="dsmcSaveVideo(' + v.file_id + ', this)">Save</button>';
    if (v.file_id) html += '<button type="button" class="dsmv-btn delete" onclick="dsmcDeleteVideo(' + v.file_id + ', this)">Delete</button>';
    html += '</div></div>';
  });
  grid.insertAdjacentHTML('beforeend', html);
  vCount = startIndex + list.length;

  var ids = [];
  list.forEach(function(v){ if (v.file_id) ids.push(v.file_id); });
  if (ids.length) {
    fetch(DMC_HUB + 'files?ids=' + ids.join(','), { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      var files = d || {};
      list.forEach(function(v, li){
        var i = startIndex + li;
        var vv = document.getElementById('dsmv-vid-' + i);
        var dl = document.getElementById('dsmv-dl-' + i);
        var u = v.url;
        if (v.file_id && files[v.file_id]) u = files[v.file_id];
        if (vv && u) { vv.src = u; vv.preload = 'metadata'; }
        if (dl && u) { dl.href = u; }
      });
      dsmcAppendVideosLoadMore(body);
    })
    .catch(function(){
      list.forEach(function(v, li){
        var i = startIndex + li;
        var vv = document.getElementById('dsmv-vid-' + i);
        var dl = document.getElementById('dsmv-dl-' + i);
        if (!v.file_id) { if (vv) vv.src = v.url; if (dl) dl.href = v.url; }
      });
      dsmcAppendVideosLoadMore(body);
    });
  } else {
    list.forEach(function(v, li){
      var i = startIndex + li;
      var vv = document.getElementById('dsmv-vid-' + i);
      var dl = document.getElementById('dsmv-dl-' + i);
      if (vv) vv.src = v.url;
      if (dl) dl.href = v.url;
    });
    dsmcAppendVideosLoadMore(body);
  }
}

function dsmcAppendVideosLoadMore(body){
  var old = document.getElementById('dsmv-loadmore-wrap');
  if (old) old.remove();
  if (vOffset < vTotal) {
    var wrap = document.createElement('div');
    wrap.id = 'dsmv-loadmore-wrap';
    wrap.style.cssText = 'text-align:center;padding:4px 16px 18px;';
    wrap.innerHTML = '<button type="button" id="dsmv-loadmore-btn" class="dsmy-lb-btn" style="background:#1e2d4a;color:#93c5fd;" onclick="dsmcLoadMoreVideos(this)">Load More (' + (vTotal - vOffset) + ' more)</button>';
    body.appendChild(wrap);
  }
}

window.dsmcSaveVideo = function(fileId, btn){
  btn.disabled = true; btn.textContent = 'Saving...';
  fetch(DMC_HUB + 'save/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json().then(function(d){ return {ok: r.ok, d: d}; }); })
  .then(function(res){
    if (res.ok) { dsmcLoadVideos(true); dsmcRefreshCap(); }
    else { alert((res.d && res.d.message) || 'Could not save this video.'); btn.disabled = false; btn.textContent = 'Save'; }
  })
  .catch(function(){ btn.disabled = false; btn.textContent = 'Save'; });
};

window.dsmcDeleteVideo = function(fileId, btn){
  if (!confirm('Delete this video permanently?')) return;
  btn.disabled = true;
  fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(){ dsmcLoadVideos(true); dsmcRefreshCap(); })
  .catch(function(){ btn.disabled = false; });
};

function dsmcRenderAudio(list, files){
  var body = document.getElementById('dsma-body');
  if (!list.length) { body.innerHTML = '<div class="dsmv-empty"><svg width="60" height="60" viewBox="0 0 72 72" fill="none" style="margin:0 auto 14px;display:block;"><circle cx="24" cy="20" r="5" fill="#f59e0b"/><circle cx="38" cy="20" r="5" fill="#f59e0b"/><line x1="46" y1="8" x2="56" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><line x1="56" y1="8" x2="46" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><rect x="10" y="30" width="36" height="28" rx="6" fill="#7c3aed"/><path d="M18 44v-6M24 47v-12M30 44v-6M36 47v-12M42 44v-6" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>No audio generated yet</div>'; return; }
  var html = '<div class="dsmv-grid" id="dsma-grid">';
  list.forEach(function(a){
    var badge = '';
    if (a.exp_badge === 'saved') badge = '<span class="dsmv-badge saved" title="Saved"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>';
    else if (a.exp_badge === 'expired') badge = '<span class="dsmv-badge expired">Expired</span>';
    else if (a.exp_badge === 'temp') badge = '<span class="dsmv-badge temp" title="Temporary \u2014 will expire soon"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg></span>';
    var label = a.label || a.tags || (a.kind === 'speech' ? 'Text to Speech' : 'Music');
    html += '<div class="dsmv-card">';
    var _url = (files && files[a.file_id]) ? files[a.file_id] : a.url;
    html += '<audio controls preload="none" src="' + _url + '" style="width:100%;"></audio>';
    html += '<div class="dsmv-meta">' + badge + '<p class="dsmv-prompt">' + dmcEsc(String(label).substring(0,80)) + '</p></div>';
    html += '<div class="dsmv-actions">';
    if (!a.is_expired) html += '<a class="dsmv-btn" href="' + _url + '" target="_blank" rel="noopener">Download</a>';
    if (a.file_id && a.exp_badge !== 'saved' && !a.is_expired) html += '<button type="button" class="dsmv-btn save" onclick="dsmcSaveAudio(' + a.file_id + ', this)">Save</button>';
    if (a.file_id) html += '<button type="button" class="dsmv-btn delete" onclick="dsmcDeleteAudio(' + a.file_id + ', this)">Delete</button>';
    html += '</div></div>';
  });
  html += '</div>';
  body.innerHTML = html;
}

window.dsmcLoadAudio = function(force){
  if (aLoaded && !force) return;
  aLoaded = true;
  var body = document.getElementById('dsma-body');
  body.innerHTML = '<div class="dsmv-loading">Loading audio...</div>';
  fetch(DMC_REST + 'my-audio?offset=0&limit=200', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (!d || !d.success) { body.innerHTML = '<div class="dsmv-empty">Could not load audio.</div>'; return; }
    var list = d.audio || [];
    var ids = [];
    list.forEach(function(a){ if (a.file_id) ids.push(a.file_id); });
    if (!ids.length) { dsmcRenderAudio(list, {}); return; }
    fetch(DMC_HUB + 'files?ids=' + ids.join(','), { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(files){ dsmcRenderAudio(list, files || {}); })
    .catch(function(){ dsmcRenderAudio(list, {}); });
  })
  .catch(function(){ body.innerHTML = '<div class="dsmv-empty">Network error.</div>'; });
};

window.dsmcSaveAudio = function(fileId, btn){
  btn.disabled = true; btn.textContent = 'Saving...';
  fetch(DMC_HUB + 'save/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json().then(function(d){ return {ok: r.ok, d: d}; }); })
  .then(function(res){
    if (res.ok) { dsmcLoadAudio(true); dsmcRefreshCap(); }
    else { alert((res.d && res.d.message) || 'Could not save this audio.'); btn.disabled = false; btn.textContent = 'Save'; }
  })
  .catch(function(){ btn.disabled = false; btn.textContent = 'Save'; });
};

window.dsmcDeleteAudio = function(fileId, btn){
  if (!confirm('Delete this audio permanently?')) return;
  btn.disabled = true;
  fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(){ dsmcLoadAudio(true); dsmcRefreshCap(); })
  .catch(function(){ btn.disabled = false; });
};

window.dsmcLoadPresentations = function(force){
  if (pLoaded && !force) return;
  pLoaded = true;
  pdOffset = 0; pdTotal = 0; pdCount = 0;
  pbOffset = 0; pbTotal = 0; pbCount = 0;
  var draftsEl = document.getElementById('dsmp-drafts');
  var builtEl = document.getElementById('dsmp-built');
  draftsEl.innerHTML = '<div class="dsmv-loading">Loading...</div>';
  builtEl.innerHTML = '<div class="dsmv-loading">Loading...</div>';

  fetch(DMC_REST + 'my-ppts?offset=0&limit=20', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    var drafts = (d && d.success) ? (d.drafts || []) : [];
    pdTotal = (d && d.success) ? (d.total || 0) : 0;
    pdOffset = (d && d.success) ? (d.next_offset || 0) : 0;
    if (!drafts.length) { draftsEl.innerHTML = '<div class="dsmv-empty"><svg width="60" height="60" viewBox="0 0 72 72" fill="none" style="margin:0 auto 14px;display:block;"><circle cx="24" cy="20" r="5" fill="#f59e0b"/><circle cx="38" cy="20" r="5" fill="#f59e0b"/><line x1="46" y1="8" x2="56" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><line x1="56" y1="8" x2="46" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><rect x="10" y="30" width="36" height="28" rx="6" fill="#7c3aed"/><rect x="16" y="38" width="24" height="3" rx="1.5" fill="#fff"/><rect x="16" y="45" width="16" height="3" rx="1.5" fill="#fff"/></svg>No saved drafts</div>'; return; }
    draftsEl.innerHTML = '';
    dsmcAppendDrafts(drafts);
  })
  .catch(function(){ draftsEl.innerHTML = '<div class="dsmv-empty">Network error.</div>'; });

  fetch(DMC_REST + 'my-built-ppts?offset=0&limit=20', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    var files = (d && d.success) ? (d.files || []) : [];
    pbTotal = (d && d.success) ? (d.total || 0) : 0;
    pbOffset = (d && d.success) ? (d.next_offset || 0) : 0;
    if (!files.length) { builtEl.innerHTML = '<div class="dsmv-empty"><svg width="60" height="60" viewBox="0 0 72 72" fill="none" style="margin:0 auto 14px;display:block;"><circle cx="24" cy="20" r="5" fill="#f59e0b"/><circle cx="38" cy="20" r="5" fill="#f59e0b"/><line x1="46" y1="8" x2="56" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><line x1="56" y1="8" x2="46" y2="18" stroke="#c026d3" stroke-width="2.5" stroke-linecap="round"/><rect x="10" y="30" width="36" height="28" rx="6" fill="#7c3aed"/><rect x="16" y="38" width="24" height="3" rx="1.5" fill="#fff"/><rect x="16" y="45" width="16" height="3" rx="1.5" fill="#fff"/></svg>No downloaded presentations yet</div>'; return; }
    builtEl.innerHTML = '';
    dsmcAppendBuilt(files);
  })
  .catch(function(){ builtEl.innerHTML = '<div class="dsmv-empty">Network error.</div>'; });
};

function dsmcAppendDrafts(drafts){
  var listEl = document.getElementById('dsmp-drafts');
  var html = '';
  drafts.forEach(function(dr){
    var theme = DSMP_THEMES[dr.design] || DSMP_THEMES['dark-modern'];
    var fs = dr.first_slide || {};
    html += '<div class="dsmp-card">';
    if (fs.image_data) {
      html += '<div class="dsmp-thumb dsmp-thumb-photo" style="background-image:url(\'' + fs.image_data + '\');">';
      html += '<div class="dsmp-thumb-overlay"><div>';
      html += '<div class="dsmp-thumb-title">' + dmcEsc(fs.title || dr.title || 'Untitled') + '</div>';
      if (fs.subtitle) html += '<div class="dsmp-thumb-sub">' + dmcEsc(fs.subtitle) + '</div>';
      html += '</div></div></div>';
    } else {
      html += '<div class="dsmp-thumb" style="background:' + theme[0] + ';"><div>';
      html += '<div class="dsmp-thumb-title" style="color:' + theme[1] + ';">' + dmcEsc(fs.title || dr.title || 'Untitled') + '</div>';
      if (fs.subtitle) html += '<div class="dsmp-thumb-sub" style="color:' + theme[1] + ';">' + dmcEsc(fs.subtitle) + '</div>';
      html += '</div></div>';
    }
    html += '<div class="dsmp-body"><h4>' + dmcEsc(dr.title || 'Untitled') + '</h4><p>' + (dr.slides_count || 0) + ' slides &middot; ' + dmcFmtTs(dr.updated) + '</p></div>';
    html += '<div class="dsmv-actions">';
    html += '<a class="dsmv-btn" href="' + DMC_SITE + '/ai-presentation/?ds_load_ppt=' + encodeURIComponent(dr.id) + '">Open</a>';
    html += '<button type="button" class="dsmv-btn delete" onclick="dsmcDeleteDraft(\'' + dr.id + '\', this)">Delete</button>';
    html += '</div></div>';
  });
  listEl.insertAdjacentHTML('beforeend', html);
  pdCount += drafts.length;
  var old = document.getElementById('dsmp-drafts-loadmore-wrap');
  if (old) old.remove();
  if (pdOffset < pdTotal) {
    var wrap = document.createElement('div');
    wrap.id = 'dsmp-drafts-loadmore-wrap';
    wrap.style.cssText = 'grid-column:1/-1;text-align:center;padding:4px 16px 18px;';
    wrap.innerHTML = '<button type="button" class="dsmy-lb-btn" style="background:#1e2d4a;color:#93c5fd;" onclick="dsmcLoadMoreDrafts(this)">Load More (' + (pdTotal - pdOffset) + ' more)</button>';
    listEl.appendChild(wrap);
  }
}
function dsmcAppendBuilt(files){
  var listEl = document.getElementById('dsmp-built');
  var html = '';
  files.forEach(function(f){
    html += '<div class="dsmp-card">';
    html += '<div class="dsmp-thumb dsmp-thumb-generic"><div>';
    html += '<div class="dsmp-thumb-icon">&#128202;</div>';
    html += '<div class="dsmp-thumb-title">' + dmcEsc(f.title || f.filename || 'Presentation') + '</div>';
    html += '</div></div>';
    html += '<div class="dsmp-body"><h4>' + dmcEsc(f.title || f.filename || 'Presentation') + '</h4><p>' + dmcFmtSize(f.size) + ' &middot; ' + dmcFmtDateStr(f.date) + '</p></div>';
    html += '<div class="dsmv-actions">';
    html += '<a class="dsmv-btn" href="' + f.url + '" target="_blank" rel="noopener">Download</a>';
    html += '<button type="button" class="dsmv-btn delete" onclick="dsmcDeleteBuilt(\'' + f.id + '\', this)">Delete</button>';
    html += '</div></div>';
  });
  listEl.insertAdjacentHTML('beforeend', html);
  pbCount += files.length;
  var old = document.getElementById('dsmp-built-loadmore-wrap');
  if (old) old.remove();
  if (pbOffset < pbTotal) {
    var wrap = document.createElement('div');
    wrap.id = 'dsmp-built-loadmore-wrap';
    wrap.style.cssText = 'grid-column:1/-1;text-align:center;padding:4px 16px 18px;';
    wrap.innerHTML = '<button type="button" class="dsmy-lb-btn" style="background:#1e2d4a;color:#93c5fd;" onclick="dsmcLoadMoreBuilt(this)">Load More (' + (pbTotal - pbOffset) + ' more)</button>';
    listEl.appendChild(wrap);
  }
}

window.dsmcLoadMoreBuilt = function(btn){
  if (!btn) return;
  btn.disabled = true;
  var origText = btn.textContent;
  btn.textContent = 'Loading...';
  fetch(DMC_REST + 'my-built-ppts?offset=' + pbOffset + '&limit=20', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (!d || !d.success) { btn.disabled = false; btn.textContent = origText; return; }
    pbTotal = d.total || 0;
    pbOffset = d.next_offset || 0;
    dsmcAppendBuilt(d.files || []);
  })
  .catch(function(){ btn.disabled = false; btn.textContent = origText; });
};

function dmcFmtTs(ts){ if (!ts) return ''; try { return new Date(ts * 1000).toLocaleDateString(); } catch(e){ return ''; } }
function dmcFmtDateStr(s){ if (!s) return ''; try { var d = new Date(String(s).replace(' ', 'T') + 'Z'); return isNaN(d.getTime()) ? s : d.toLocaleDateString(); } catch(e){ return s; } }
function dmcFmtSize(b){ b = parseInt(b || 0, 10); if (!b) return '0 KB'; if (b < 1024*1024) return Math.round(b/1024) + ' KB'; return (b/1024/1024).toFixed(1) + ' MB'; }

window.dsmcDeleteDraft = function(id, btn){
  if (!confirm('Delete this draft permanently?')) return;
  btn.disabled = true;
  fetch(DMC_REST + 'delete-ppt', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-WP-Nonce': DMC_NONCE}, credentials: 'include', body: JSON.stringify({ ppt_id: id }) })
  .then(function(r){ return r.json(); })
  .then(function(){ dsmcLoadPresentations(true); dsmcRefreshCap(); })
  .catch(function(){ btn.disabled = false; });
};

window.dsmcDeleteBuilt = function(id, btn){
  if (!confirm('Delete this presentation file permanently?')) return;
  btn.disabled = true;
  fetch(DMC_REST + 'delete-built-ppt', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-WP-Nonce': DMC_NONCE}, credentials: 'include', body: JSON.stringify({ id: id }) })
  .then(function(r){ return r.json(); })
  .then(function(){ dsmcLoadPresentations(true); dsmcRefreshCap(); })
  .catch(function(){ btn.disabled = false; });
};

// ==== Saved Items Modal (added) ====
(function(){
  var dsmsModalBuilt = false;
  var dsmsCapDebounce = null;

  function dsmsScheduleCapRefresh(){
    if (dsmsCapDebounce) clearTimeout(dsmsCapDebounce);
    dsmsCapDebounce = setTimeout(function(){ if (window.dsmcRefreshCap) window.dsmcRefreshCap(); }, 1200);
  }

  function dsmsBuildModal(){
    if (dsmsModalBuilt) return;
    dsmsModalBuilt = true;
    var wrap = document.createElement('div');
    wrap.id = 'dsms-modal-overlay';
    wrap.innerHTML =
      '<div id="dsms-modal-panel">' +
        '<div id="dsms-modal-head"><h2>Saved Items</h2><button type="button" id="dsms-modal-close" aria-label="Close">&times;</button></div>' +
        '<div id="dsms-cap-line" style="display:flex;justify-content:flex-end;padding:0 24px 16px;">' +
          '<span style="display:inline-block;padding:6px 16px;border:1px solid #c026d3;border-radius:999px;font-size:13px;color:#f0abfc;background:rgba(192,38,211,0.08);white-space:nowrap;"><span id="dsms-cap-used">0</span> / <span id="dsms-cap-total">0</span> saved items used</span>' +
        '</div>' +
        '<div id="dsms-modal-body">' +
          '<div class="dsms-section"><h3>Images</h3><div id="dsms-images" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Videos</h3><div id="dsms-videos" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Audio</h3><div id="dsms-audio" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Saved Drafts</h3><div id="dsms-drafts" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);

    var style = document.createElement('style');
    style.textContent =
      '#dsms-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:100000;overflow:auto;padding:40px 16px;}' +
      '#dsms-modal-overlay.open{display:block;}' +
      '#dsms-modal-panel{max-width:1200px;margin:0 auto;background:#0d1117;border-radius:12px;padding:24px;color:#fff;}' +
      '#dsms-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}' +
      '#dsms-modal-head h2{margin:0;font-size:20px;color:#fff;}' +
      '#dsms-modal-close{background:none;border:none;color:#fff;font-size:28px;line-height:1;cursor:pointer;}' +
      '.dsms-section{margin-bottom:28px;}' +
      '.dsms-section h3{font-size:15px;color:#93c5fd;margin:0 0 10px;}' +
      '.dsms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;}' +
      '.dsms-empty{color:#94a3b8;font-size:13px;padding:8px 0;}' +
      '#dsmc-capbar:hover{opacity:.82;text-decoration:underline;}';
    document.head.appendChild(style);

    wrap.addEventListener('click', function(e){
      if (e.target === wrap) dsmsClose();
      dsmsScheduleCapRefresh();
    });
    document.getElementById('dsms-modal-close').addEventListener('click', dsmsClose);
  }

  function dsmsClose(){
    var wrap = document.getElementById('dsms-modal-overlay');
    if (wrap) wrap.classList.remove('open');
    if (window.dsmcRefreshCap) window.dsmcRefreshCap();
  }

  function dsmsLoadImages(){
    var el = document.getElementById('dsms-images');
    fetch(DMC_REST + 'my-saved-images', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d || !d.success || !d.count) { el.innerHTML = '<div class="dsms-empty">No saved images.</div>'; return; }
      el.innerHTML = d.html;
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load images.</div>'; });
  }

  function dsmsLoadVideos(){
    var el = document.getElementById('dsms-videos');
    fetch(DMC_REST + 'my-videos?offset=0&limit=1000', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      var all = (d && d.success) ? (d.videos || []) : [];
      var saved = all.filter(function(v){ return v.exp_badge === 'saved'; });
      if (!saved.length) { el.innerHTML = '<div class="dsms-empty">No saved videos.</div>'; return; }
      var html = '';
      saved.forEach(function(v){
        html += '<div class="dsmv-card">';
        html += '<video controls preload="none" src="' + v.url + '"></video>';
        html += '<div class="dsmv-meta"><span class="dsmv-badge saved">Saved</span><p class="dsmv-prompt">' + dmcEsc((v.prompt||'').substring(0,80)) + '</p></div>';
        html += '<div class="dsmv-actions">';
        html += '<a class="dsmv-btn" href="' + v.url + '" target="_blank" rel="noopener">Download</a>';
        if (v.file_id) html += '<button type="button" class="dsmv-btn delete" onclick="dsmsDeleteVideo(' + v.file_id + ', this)">Delete</button>';
        html += '</div></div>';
      });
      el.innerHTML = html;
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load videos.</div>'; });
  }

  function dsmsLoadDrafts(){
    var el = document.getElementById('dsms-drafts');
    fetch(DMC_REST + 'my-ppts?offset=0&limit=1000', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      var drafts = (d && d.success) ? (d.drafts || []) : [];
      if (!drafts.length) { el.innerHTML = '<div class="dsms-empty">No saved drafts.</div>'; return; }
      var html = '';
      drafts.forEach(function(dr){
        var theme = DSMP_THEMES[dr.design] || DSMP_THEMES['dark-modern'];
        var fs = dr.first_slide || {};
        html += '<div class="dsmp-card">';
        if (fs.image_data) {
          html += '<div class="dsmp-thumb dsmp-thumb-photo" style="background-image:url(\'' + fs.image_data + '\');">';
          html += '<div class="dsmp-thumb-overlay"><div>';
          html += '<div class="dsmp-thumb-title">' + dmcEsc(fs.title || dr.title || 'Untitled') + '</div>';
          if (fs.subtitle) html += '<div class="dsmp-thumb-sub">' + dmcEsc(fs.subtitle) + '</div>';
          html += '</div></div></div>';
        } else {
          html += '<div class="dsmp-thumb" style="background:' + theme[0] + ';"><div>';
          html += '<div class="dsmp-thumb-title" style="color:' + theme[1] + ';">' + dmcEsc(fs.title || dr.title || 'Untitled') + '</div>';
          if (fs.subtitle) html += '<div class="dsmp-thumb-sub" style="color:' + theme[1] + ';">' + dmcEsc(fs.subtitle) + '</div>';
          html += '</div></div>';
        }
        html += '<div class="dsmp-body"><h4>' + dmcEsc(dr.title || 'Untitled') + '</h4><p>' + (dr.slides_count || 0) + ' slides &middot; ' + dmcFmtTs(dr.updated) + '</p></div>';
        html += '<div class="dsmv-actions">';
        html += '<a class="dsmv-btn" href="' + DMC_SITE + '/ai-presentation/?ds_load_ppt=' + encodeURIComponent(dr.id) + '">Open</a>';
        html += '<button type="button" class="dsmv-btn delete" onclick="dsmsDeleteDraft(\'' + dr.id + '\', this)">Delete</button>';
        html += '</div></div>';
      });
      el.innerHTML = html;
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load drafts.</div>'; });
  }

function dsmsLoadAudio(){
    var el = document.getElementById('dsms-audio');
    fetch(DMC_REST + 'my-audio?offset=0&limit=1000', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      var all = (d && d.success) ? (d.audio || []) : [];
      var saved = all.filter(function(a){ return a.exp_badge === 'saved'; });
      if (!saved.length) { el.innerHTML = '<div class="dsms-empty">No saved audio.</div>'; return; }
      var ids = [];
      saved.forEach(function(a){ if (a.file_id) ids.push(a.file_id); });
      var resolve = ids.length ? fetch(DMC_HUB + 'files?ids=' + ids.join(','), { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' }).then(function(r){ return r.json(); }).catch(function(){ return {}; }) : Promise.resolve({});
      resolve.then(function(files){
        var html = '';
        saved.forEach(function(a){
          var _url = (files && files[a.file_id]) ? files[a.file_id] : a.url;
          html += '<div class="dsmv-card">';
          html += '<audio controls preload="none" src="' + _url + '" style="width:100%;"></audio>';
          html += '<div class="dsmv-meta"><span class="dsmv-badge saved">Saved</span><p class="dsmv-prompt">' + dmcEsc((a.label||a.tags||'').substring(0,80)) + '</p></div>';
          html += '<div class="dsmv-actions">';
          html += '<a class="dsmv-btn" href="' + _url + '" target="_blank" rel="noopener">Download</a>';
          if (a.file_id) html += '<button type="button" class="dsmv-btn delete" onclick="dsmsDeleteAudio(' + a.file_id + ', this)">Delete</button>';
          html += '</div></div>';
        });
        el.innerHTML = html;
      });
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load audio.</div>'; });
  }

    window.dsmsDeleteAudio = function(fileId, btn){
    if (!confirm('Delete this audio permanently?')) return;
    btn.disabled = true;
    fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(){
      var card = btn.closest('.dsmv-card'); if (card) card.remove();
      if (window.dsmcRefreshCap) window.dsmcRefreshCap();
    })
    .catch(function(){ btn.disabled = false; });
  };

    window.dsmsDeleteVideo = function(fileId, btn){
    if (!confirm('Delete this video permanently?')) return;
    btn.disabled = true;
    fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(){
      var card = btn.closest('.dsmv-card'); if (card) card.remove();
      vLoaded = false;
      if (window.dsmcRefreshCap) window.dsmcRefreshCap();
    })
    .catch(function(){ btn.disabled = false; });
  };

  window.dsmsDeleteDraft = function(id, btn){
    if (!confirm('Delete this draft permanently?')) return;
    btn.disabled = true;
    fetch(DMC_REST + 'delete-ppt', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-WP-Nonce': DMC_NONCE}, credentials: 'include', body: JSON.stringify({ ppt_id: id }) })
    .then(function(r){ return r.json(); })
    .then(function(){
      var card = btn.closest('.dsmp-card'); if (card) card.remove();
      pLoaded = false;
      if (window.dsmcRefreshCap) window.dsmcRefreshCap();
    })
    .catch(function(){ btn.disabled = false; });
  };

  function dsmsRefreshCapLine(){
    var u = document.getElementById('dsms-cap-used');
    var t = document.getElementById('dsms-cap-total');
    if (!u || !t) return;
    fetch(DMC_REST + 'my-cap', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d || !d.success) return;
      u.textContent = d.used;
      t.textContent = d.total;
    })
    .catch(function(){});
  }

  window.dsmsOpenSaved = function(){
    dsmsBuildModal();
    var wrap = document.getElementById('dsms-modal-overlay');
    wrap.classList.add('open');
    dsmsLoadImages();
    dsmsLoadVideos();
    dsmsLoadAudio();
    dsmsLoadDrafts();
    dsmsRefreshCapLine();
  };

  var capEl = document.getElementById('dsmc-capbar');
  if (capEl) {
    capEl.style.cursor = 'pointer';
    capEl.title = 'View your saved items';
    capEl.addEventListener('click', window.dsmsOpenSaved);
  }
})();


// ==== Expired Files Modal (added) ====
(function(){
  var dsmeModalBuilt = false;

  var DSME_PLACEHOLDER = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMTIwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iIzFlMjUzMCIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjQ2IiByPSIxOCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOTRhM2I4IiBzdHJva2Utd2lkdGg9IjMiLz48bGluZSB4MT0iMTAwIiB5MT0iNDYiIHgyPSIxMDAiIHkyPSIzNCIgc3Ryb2tlPSIjOTRhM2I4IiBzdHJva2Utd2lkdGg9IjMiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxsaW5lIHgxPSIxMDAiIHkxPSI0NiIgeDI9IjEwOSIgeTI9IjUyIiBzdHJva2U9IiM5NGEzYjgiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PHRleHQgeD0iMTAwIiB5PSI5MCIgZm9udC1zaXplPSIxMyIgZmlsbD0iI2NiZDVlMSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9IkFyaWFsLHNhbnMtc2VyaWYiPkV4cGlyZWQ8L3RleHQ+PC9zdmc+';

  function dsmeBuildModal(){
    if (dsmeModalBuilt) return;
    dsmeModalBuilt = true;
    var wrap = document.createElement('div');
    wrap.id = 'dsme-modal-overlay';
    wrap.innerHTML =
      '<div id="dsme-modal-panel">' +
        '<div id="dsme-modal-head"><h2>Expired Files</h2><button type="button" id="dsme-modal-close" aria-label="Close">&times;</button></div>' +
        '<div id="dsme-modal-body">' +
          '<div class="dsme-note">These are removed automatically once deleted from storage \u2014 nothing to do here.</div>' +
          '<div class="dsms-section"><h3>Expired Images</h3><div id="dsme-images" class="dsme-list"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Expired Videos</h3><div id="dsme-videos" class="dsme-list"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Expired Audio</h3><div id="dsme-audio" class="dsme-list"><div class="dsmv-loading">Loading...</div></div></div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);

    var style = document.createElement('style');
    style.textContent =
      '#dsme-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:100000;overflow:auto;padding:40px 16px;}' +
      '#dsme-modal-overlay.open{display:block;}' +
      '#dsme-modal-panel{max-width:700px;margin:0 auto;background:#0d1117;border-radius:12px;padding:24px;color:#fff;}' +
      '#dsme-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}' +
      '#dsme-modal-head h2{margin:0;font-size:20px;color:#fff;}' +
      '#dsme-modal-close{background:none;border:none;color:#fff;font-size:28px;line-height:1;cursor:pointer;}' +
      '.dsms-section h3{font-size:15px;color:#93c5fd;margin:0 0 10px;}' +
      '.dsme-note{font-size:11.5px;color:#94a3b8;margin:-4px 0 14px;}' +
      '.dsme-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;}' +
      '.dsme-row{display:flex;flex-direction:column;background:#161b22;border:1px solid #a21caf;border-radius:8px;overflow:hidden;}' +
      '.dsme-thumb{width:100%;height:70px;object-fit:cover;display:block;background:#1e2530;}' +
      '.dsme-info{padding:8px 10px 10px;}' +
      '.dsme-info div{font-size:10px;color:#e879f9;line-height:1.5;word-break:break-word;}' +
      '.dsme-info .dsmv-btn{margin-top:8px;width:100%;font-size:11px;padding:6px 0;}' +
      '#dsme-badge{cursor:pointer;color:#f59e0b;font-size:13px;margin-left:12px;}' +
      '#dsme-badge:hover{opacity:.8;text-decoration:underline;}';
    document.head.appendChild(style);

    wrap.addEventListener('click', function(e){ if (e.target === wrap) dsmeClose(); });
    document.getElementById('dsme-modal-close').addEventListener('click', dsmeClose);
  }

  function dsmeClose(){
    var wrap = document.getElementById('dsme-modal-overlay');
    if (wrap) wrap.classList.remove('open');
  }

  function dsmeRow(item){
    var fname = item.filename ? String(item.filename) : '(untitled)';
    var created = item.created_at ? dmcFmtDateStr(item.created_at) : '\u2014';
    var expired = item.expires_at ? dmcFmtDateStr(item.expires_at) : '\u2014';
    return '<div class="dsme-row">' +
      '<img class="dsme-thumb" src="' + DSME_PLACEHOLDER + '" alt="Expired" loading="lazy"/>' +
      '<div class="dsme-info">' +
        '<div>File name: ' + dmcEsc(fname) + '</div>' +
        '<div>Date of creation: ' + dmcEsc(created) + '</div>' +
        '<div>Expired date: ' + dmcEsc(expired) + '</div>' +
        '<button type="button" class="dsmv-btn delete" onclick="dsmeDelete(' + item.file_id + ', this)">Delete</button>' +
      '</div>' +
    '</div>';
  }

  function dsmeRender(data){
    var imgEl = document.getElementById('dsme-images');
    var vidEl = document.getElementById('dsme-videos');
    var audEl = document.getElementById('dsme-audio');
    var images = (data && data.images) || [];
    var videos = (data && data.videos) || [];
    var audio = (data && data.audio) || [];
    imgEl.innerHTML = images.length ? images.map(dsmeRow).join('') : '<div class="dsms-empty">No expired images.</div>';
    vidEl.innerHTML = videos.length ? videos.map(dsmeRow).join('') : '<div class="dsms-empty">No expired videos.</div>';
    if (audEl) audEl.innerHTML = audio.length ? audio.map(dsmeRow).join('') : '<div class="dsms-empty">No expired audio.</div>';
  }

  function dsmeFetch(){
    return fetch(DMC_REST + 'my-expired-items', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
      .then(function(r){ return r.json(); });
  }

  function dsmeUpdateBadge(count){
    var badge = document.getElementById('dsme-badge');
    if (!badge) {
      var capEl2 = document.getElementById('dsmc-capbar');
      if (capEl2 && capEl2.parentNode) {
        var hub2 = capEl2.parentNode;
        var toprow = document.getElementById('dsmc-toprow');
        if (!toprow) {
          var tabsEl2 = hub2.querySelector('.dsmc-tabs');
          toprow = document.createElement('div');
          toprow.id = 'dsmc-toprow';
          hub2.insertBefore(toprow, tabsEl2 || capEl2);
          if (tabsEl2) toprow.appendChild(tabsEl2);
          var rightWrap2 = document.createElement('div');
          rightWrap2.id = 'dsmc-topbar-wrap';
          toprow.appendChild(rightWrap2);
          rightWrap2.appendChild(capEl2);
        }
        var wrap2 = document.getElementById('dsmc-topbar-wrap');
        if (wrap2) {
          badge = document.createElement('span');
          badge.id = 'dsme-badge';
          wrap2.insertBefore(badge, capEl2);
          badge.addEventListener('click', window.dsmeOpen);
        }
      }
    }
    if (badge) badge.textContent = String.fromCharCode(9888) + ' ' + count + ' expired file' + (count === 1 ? '' : 's');
  }

  function dsmeUpdateBadge2(d){ dsmeUpdateBadge(d && d.count ? d.count : 0); }

  window.dsmeDelete = function(fileId, btn){
    if (!confirm('Delete this expired file permanently?')) return;
    btn.disabled = true;
    fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(){
      var row = btn.closest('.dsme-row'); if (row) row.remove();
      vLoaded = false; pLoaded = false;
      dsmeFetch().then(dsmeUpdateBadge2);
    })
    .catch(function(){ btn.disabled = false; });
  };

  window.dsmeOpen = function(){
    dsmeBuildModal();
    document.getElementById('dsme-modal-overlay').classList.add('open');
    dsmeFetch().then(function(d){
      if (d && d.success) dsmeRender(d);
    });
  };

  dsmeFetch().then(function(d){
    if (d && d.success) dsmeUpdateBadge(d.count || 0);
  });

  try {
    var __dsOpenParam = new URLSearchParams(window.location.search).get('ds_open');
    if (__dsOpenParam === 'saved' && window.dsmsOpenSaved) { window.dsmsOpenSaved(); }
    else if (__dsOpenParam === 'expired' && window.dsmeOpen) { window.dsmeOpen(); }
  } catch(e) {}
})();
})();
</script>
</div><!-- /dsmc-hub -->

<?php return ob_get_clean();
});
