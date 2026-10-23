<?php
/**
 * Code Snippets export — DS AI Video (R2 Retention) [CLONE] [CLONE] - Hide Expired [No Blank Slot] [Temp Icon] [Header Icons] [Saved Icon] [Check Icon] [In-Page Saved Items Modal] [DSMP_THEMES Ported] [CLONE] - Kling 3.0 4K + LTX-2.3 in Model Picker
 *
 * Snippet ID : 802
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-116
 * Modified   : 2026-09-09 02:45:16
 * Load order : 121 of 144
 */
/**
 * Snippet Name: DS AI Video
 * Shortcode: [ds_ai_video]
 * Run: Everywhere
 *
 * SELF-CONTAINED — does NOT touch or depend on any existing snippet's code.
 * It draws its OWN copy of the tab bar (Images / Presentation / Video) so
 * the page looks consistent, but that's a fresh block of markup here, not
 * a shared include — nothing in your existing "DS AI Studio" or "DS API
 * Keys Settings" snippets is read, called, or modified.
 *
 * Setup: create a new WP page (e.g. /ai-studio/ai-video/) whose content is
 * just [ds_ai_video]. Nothing to change on your existing AI Studio /
 * AI Images / AI Presentation pages.
 *
 * Same visual/interaction structure as "DS AI Images" (Snippet 10):
 * dark sidebar (Model / Prompt / References / options / Generate) on the
 * left, a "My Creations" grid feed on the right. Generation goes through
 * WaveSpeedAI's Seedance 1.5 Pro model via /ds/v1/generate-video and
 * /ds/v1/poll-video (see ds-ai-video-rest.php + ds-ai-video-api-callers.php).
 *
 * Requires:
 *   - ds_get_credits() (DS Credit Core — read-only, already on your site)
 *   - /ds/v1/generate-video, /ds/v1/poll-video (DS AI Video REST — new)
 *   - /ds/v1/download (reused as-is from DS AI Image REST — read-only, already on your site)
 */

if ( ! defined('ABSPATH') ) exit;

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

add_shortcode('ds_ai_video', 'ds_ai_video_shortcode');
function ds_ai_video_shortcode() {
    if (!is_user_logged_in()) return ds_guest_login_card('Login to Use AI Video', 'Create AI video clips from a text prompt.');

    $uid     = get_current_user_id();
    $credits = (int) ds_get_credits($uid);

    // ── Build "My Creations" groups from ds_ai_video_generations ──────
    $groups    = array();
    $group_idx = array();
    $seen_urls = array();
    $all_gens  = get_user_meta($uid, 'ds_ai_video_generations', true) ?: array();
    foreach ($all_gens as $gen) {
        if (!is_array($gen)) continue;
        $url = $gen['url'] ?? '';
        $pid = $gen['prediction_id'] ?? '';
        $isPending = ($gen['status'] ?? '') === 'pending';
        if (empty($url) && !$isPending) continue;
        if (!empty($url)) {
            if (isset($seen_urls[$url])) continue;
            $seen_urls[$url] = true;
        }
        $p   = $gen['prompt'] ?? '';
        $dt  = $gen['date']   ?? '';
        $key = md5($p . substr($dt, 0, 16));
        if (!isset($group_idx[$key])) {
            $groups[]        = array('items' => array(), 'meta' => $gen);
            $group_idx[$key] = count($groups) - 1;
        }
        $groups[$group_idx[$key]]['items'][] = $gen;
    }

    // Videos generated before the rehost fix (stuck on WaveSpeed's URL,
    // never got an attachment_id) — offered as a one-time "Fix Older Videos" backfill.
    $needs_backfill = 0;
    foreach ($all_gens as $gen) {
        if (is_array($gen) && ($gen['status'] ?? '') === 'succeeded' && empty($gen['attachment_id']) && !empty($gen['url']) && empty($gen['file_id'])) {
            $needs_backfill++;
        }
    }

    $nonce = wp_create_nonce('wp_rest');
    $rest  = trailingslashit(rest_url('ds/v1'));
    $rest_hub = trailingslashit(rest_url('ds-hub/v1'));

    // Attachment IDs already submitted to the marketplace (generic list —
    // shared with images, keyed only by attachment_id via /ds/v1/mark-submitted).
    $submitted_aids = get_user_meta($uid, 'ds_submitted_aids', true) ?: array();
    $submitted_aids = array_map('intval', $submitted_aids);

    ob_start();
?>
<!-- OWN COPY of the tab bar, so this page looks consistent with your
     existing AI Studio pages without touching that snippet's code.
     Class names (dstv-*) are unique to this file — no shared CSS. -->
<style>
#dsv-stu{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0a0a;padding:0 4px;}
#dsv-stu *{box-sizing:border-box;}
.dstv-bar{display:flex;gap:14px;margin:24px auto 28px;justify-content:center;flex-wrap:wrap;}
.dstv-btn{padding:10px 22px;border-radius:3px;border:none;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .15s;letter-spacing:.3px;text-decoration:none;text-transform:uppercase;}
.dstv-btn.on{background:#2563eb;color:#fff;}
.dstv-btn:not(.on){background:#161616;color:#9ca3af;border:1px solid #1e1e1e;}
.dstv-btn:not(.on):hover{color:#cbd5e1;background:#1a1a1a;}
</style>
<div id="dsv-stu">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0 18px;flex-wrap:wrap;gap:12px;">
    <div class="dstv-bar" style="margin:0;">
      <a href="<?php echo esc_url( home_url('/ai-studio/') ); ?>" class="dstv-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="2"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/></svg>
        AI Images
      </a>
      <a href="<?php echo esc_url( home_url('/ai-studio/ai-presentation/') ); ?>" class="dstv-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        AI Presentation
      </a>
      <span class="dstv-btn on">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polygon points="5 3 19 12 5 21 5 3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
        AI Video
      </span>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
      <a href="<?php echo esc_url( home_url('/my-generated-images/?ds_open=expired') ); ?>" title="Expired Files" style="width:34px;height:34px;border-radius:100px;background:#111;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </a>
      <a href="javascript:void(0)" onclick="if(window.dsmsOpenSaved){window.dsmsOpenSaved();}return false;" title="Saved Files" style="width:34px;height:34px;border-radius:100px;background:#111;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      </a>
      <div style="background:#111;border:1px solid #1e1e1e;border-radius:100px;padding:7px 14px;display:flex;align-items:center;gap:6px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="#e8442a"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <span style="font-weight:800;color:#e8442a;font-size:14px;"><?php echo number_format($credits); ?></span>
        <span style="font-size:11px;color:#374151;">cr</span>
      </div>
      <a href="<?php echo esc_url( home_url('/pricing') ); ?>" style="background:#2563eb;color:#fff;padding:8px 16px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">+ Buy</a>
      <a href="<?php echo esc_url( home_url('/my-generated-images') ); ?>" style="padding:8px 14px;border-radius:100px;border:1px solid #1e1e1e;background:#111;color:#6b7280;font-size:13px;font-weight:600;text-decoration:none;">My Creations</a>
      <?php if ($needs_backfill > 0): ?>
      <button id="dsvBackfillBtn" onclick="dsvBackfillStart()" style="padding:8px 14px;border-radius:100px;border:1px solid #d97706;background:transparent;color:#f59e0b;font-size:12px;font-weight:700;cursor:pointer;">
        Fix <?php echo (int)$needs_backfill; ?> Older Video<?php echo $needs_backfill === 1 ? '' : 's'; ?>
      </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- SHARE MODAL (video) -->
<div id="dsvShareModal" style="display:none;position:fixed;inset:0;z-index:9999999;background:rgba(0,0,0,.7);align-items:center;justify-content:center;">
  <div style="background:#1a1a2e;border-radius:18px;padding:24px;max-width:380px;width:90%;border:1px solid #2d2d4a;">
    <h3 style="color:#fff;font-size:15px;font-weight:800;margin:0 0 4px;">Share this Video</h3>
    <p style="color:#6b7280;font-size:12px;margin:0 0 14px;">Created with AI on Designstager</p>
    <div style="background:linear-gradient(135deg,#0f1f3d,#1e1040);border-radius:10px;padding:12px 14px;margin-bottom:14px;">
      <div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:3px;">Designstager AI Studio</div>
      <div style="font-size:11px;color:rgba(255,255,255,.4);">Create stunning AI videos for free</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
      <button onclick="dsvShareVia('whatsapp')" style="padding:9px;border-radius:8px;background:#25D366;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">WhatsApp</button>
      <button onclick="dsvShareVia('copy')" id="dsvShareCopyBtn" style="padding:9px;border-radius:8px;background:#6366f1;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">Copy Link</button>
      <button onclick="dsvShareVia('twitter')" style="padding:9px;border-radius:8px;background:#1da1f2;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">Twitter / X</button>
      <button onclick="dsvShareVia('facebook')" style="padding:9px;border-radius:8px;background:#1877f2;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">Facebook</button>
    </div>
    <button onclick="dsvShareClose()" style="width:100%;padding:9px;border-radius:8px;border:1px solid #2d2d4a;background:transparent;color:#6b7280;font-size:12px;cursor:pointer;">Close</button>
  </div>
</div>

<!-- MARKETPLACE SUBMIT MODAL (video) -->
<div id="dsvMktModal" style="display:none;position:fixed;inset:0;z-index:9999999;background:rgba(0,0,0,.7);align-items:center;justify-content:center;">
  <div style="background:#fdf9f3;border-radius:18px;padding:28px;max-width:420px;width:92%;border:1px solid #e7e0d3;box-shadow:0 20px 60px rgba(0,0,0,.4);">
    <h3 style="font-size:20px;font-weight:800;color:#1a1a2e;margin:0 0 8px;letter-spacing:-.3px;">Submit to Marketplace</h3><?php
$ds_user_obj = wp_get_current_user();
$ds_user_roles = (array) ($ds_user_obj->roles ?? []);
$ds_roles_str = implode(',', $ds_user_roles);
$ds_ls_plan = strtolower(
    get_user_meta(get_current_user_id(), 'ds_plan', true) ?:
    get_user_meta(get_current_user_id(), 'lsq_plan', true) ?:
    get_user_meta(get_current_user_id(), 'lemonsqueezy_plan', true) ?:
    get_user_meta(get_current_user_id(), '_lsq_variant', true) ?:
    (function_exists('ds_access_plan') ? ds_access_plan(get_current_user_id()) : (function_exists('ds_get_user_plan') ? ds_get_user_plan(get_current_user_id()) : '')) ?:
    ''
);
$ds_is_admin = in_array('administrator', $ds_user_roles) || in_array('editor', $ds_user_roles) || in_array('shop_manager', $ds_user_roles);
$ds_is_pro_or_business = $ds_is_admin
    || in_array('ds_pro', $ds_user_roles)
    || in_array('ds_business', $ds_user_roles)
    || in_array('pro_member', $ds_user_roles)
    || in_array('business_member', $ds_user_roles)
    || in_array('pmpro_3', $ds_user_roles)
    || in_array('pmpro_4', $ds_user_roles)
    || strpos($ds_roles_str, 'pro') !== false
    || strpos($ds_roles_str, 'business') !== false
    || strpos($ds_roles_str, 'premium_plus') !== false
    || in_array($ds_ls_plan, ['pro','business','enterprise'])
    || strpos($ds_ls_plan, 'pro') !== false
    || strpos($ds_ls_plan, 'business') !== false;
$ds_is_premium = $ds_is_pro_or_business
    || in_array('ds_premium', $ds_user_roles)
    || in_array('premium_member', $ds_user_roles)
    || in_array('pmpro_2', $ds_user_roles)
    || strpos($ds_roles_str, 'premium') !== false
    || in_array($ds_ls_plan, ['premium','standard'])
    || strpos($ds_ls_plan, 'premium') !== false;
if ($ds_is_premium):
?>
    <p style="font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;">Share your video and earn credits on approval.</p>
    <p style="font-size:12px;color:#94a3b8;margin:0 0 8px;line-height:1.5;">Your video will be submitted using the original prompt as the title.</p>
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:11px 13px;font-size:12px;color:#15803d;margin-bottom:14px;line-height:1.6;">
      Earn <strong>3 credits</strong> back when admin approves.
    </div>
    <label style="color:#3d4659;display:flex;width:100%;align-items:center;gap:10px;margin:14px 0 18px;font-size:13px;line-height:1.5;cursor:pointer;">
      <input type="checkbox" id="dsvMktConsent" style="width:18px;height:18px;flex-shrink:0;margin:0;accent-color:#d97706;cursor:pointer;"/>
      <span style="flex:1;color:#3d4659;">I confirm I have the right to submit this AI-generated video to the Designstager Marketplace and grant Designstager a license to display and distribute it.</span>
    </label>
    <div style="display:flex;gap:8px;">
      <button onclick="dsvMktClose()" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;">Cancel</button>
      <button id="dsvMktSubmitBtn" onclick="dsvMktSubmit()" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;opacity:.5;pointer-events:none;">Submit to Marketplace</button>
    </div>
  </div>
</div>

<?php else: ?>
    <p style="font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;">Submitting to the Marketplace and earning credit back is a Premium feature.</p>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:11px 13px;font-size:12px;color:#9a3412;margin-bottom:18px;line-height:1.6;">
      Upgrade to Premium to submit your videos and earn <strong>3 credits</strong> back when admin approves.
    </div>
    <div style="display:flex;gap:8px;">
      <button onclick="dsvMktClose()" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;">Maybe Later</button>
      <a href="/pricing" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;text-align:center;text-decoration:none;display:inline-block;box-sizing:border-box;">Upgrade to Premium</a>
    </div>
  </div>
</div>

<?php endif; ?><!-- MARKETPLACE SUCCESS MODAL (video) -->
<div id="dsvMktSuccess" style="display:none;position:fixed;inset:0;z-index:9999999;background:rgba(0,0,0,.75);align-items:center;justify-content:center;">
  <div style="background:#fdf9f3;border-radius:18px;padding:28px;max-width:380px;width:92%;border:1px solid #e7e0d3;box-shadow:0 20px 60px rgba(0,0,0,.4);text-align:center;">
    <div style="font-size:64px;text-align:center;margin:8px 0 12px;">🎉</div>
    <h3 style="font-size:20px;font-weight:800;color:#1a1a2e;margin:0 0 8px;">Submitted Successfully!</h3>
    <p style="font-size:13px;color:#5a6478;margin:0 0 20px;line-height:1.6;">Your video is awaiting review. You'll get 3 credits once it's approved.</p>
    <button onclick="dsvMktSuccessClose()" style="width:100%;padding:13px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:14px;font-weight:800;border:none;cursor:pointer;">Awesome!</button>
  </div>
</div>

<style>
#dsAIV,#dsAIV *{box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}#dsAIV{background:#0a0a0a;color:#fff;border-radius:10px;overflow:hidden;}.dsAIV-wrap{display:flex;align-items:stretch;min-height:600px;}.dsAIV-sb{width:270px;flex-shrink:0;background:#10131c;border-right:1px solid #1e2a40;display:flex;flex-direction:column;position:sticky;top:0;height:calc(100vh - 60px);overflow-y:auto;scrollbar-width:thin;}.dsAIV-sb::-webkit-scrollbar{width:3px;}.dsAIV-sb::-webkit-scrollbar-thumb{background:#1e1e1e;border-radius:3px;}.dsAIV-bc{padding:9px 12px;border-bottom:1px solid #141414;display:flex;align-items:center;justify-content:space-between;background:#080808;flex-shrink:0;}.sbv-sec{padding:12px 14px;border-bottom:1px solid #1e2a40;flex-shrink:0;}.sbv-lbl{color:#94a3b8;font-size:9.5px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;margin-bottom:6px;display:flex;align-items:center;gap:4px;}.sbv-sw{position:relative;}.sbv-sw::after{content:'v';position:absolute;right:9px;top:50%;transform:translateY(-50%);color:#6b7280;font-size:10px;pointer-events:none;}.sbv-sel{width:100%;background:#191c25 !important;border:1px solid #1f222b;border-radius:4px;color:#f8fafc !important;padding:9px 28px 9px 11px;font-size:12.5px;font-weight:500;appearance:none;-webkit-appearance:none;cursor:pointer;outline:none;}.sbv-sel option{background:#191c25 !important;color:#f8fafc !important;}
.sbv-prompt{padding:14px 14px 10px;border-bottom:1px solid #1e2a40;background:rgba(59,130,246,.06);border-left:3px solid #3b82f6;position:relative;}
.sbv-ta{width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#f1f5f9;font-size:13px;resize:none;outline:none;line-height:1.7;font-family:inherit;min-height:110px;margin-top:8px;padding:10px 12px;box-sizing:border-box;}.sbv-ta::placeholder{color:#4a6080;font-style:italic;}
.dsv-refs{display:flex;gap:8px;}.dsv-ref-box{position:relative;flex:1;background:#0d1420;border:1.5px dashed #2a3a55;border-radius:8px;padding:14px 8px;display:flex;flex-direction:column;align-items:center;gap:5px;cursor:pointer;text-align:center;}.dsv-ref-box:hover{border-color:#2563eb;}.dsv-ref-text{color:#94a3b8;font-size:10.5px;}.dsv-ref-preview{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:8px;}.dsv-ref-clear{position:absolute;top:2px;right:4px;background:none;border:none;color:#fff;font-size:16px;font-weight:400;line-height:1;cursor:pointer;padding:2px;text-shadow:0 1px 3px rgba(0,0,0,.9);}.dsv-ref-clear:hover{color:#f87171;}.dsv-ref-placeholder{display:flex;flex-direction:column;align-items:center;}
.sbv-opts-row{display:flex;gap:6px;padding:10px 12px;flex-wrap:wrap;border-bottom:1px solid #141414;}
.sbv-opt{background:#191c25;color:#8b98a8;border:1px solid #1f222b;border-radius:5px;padding:6px 10px;font-size:11px;font-weight:700;cursor:pointer;}
.sbv-opt.on{background:#2563eb;color:#fff;border-color:#2563eb;}
.sbv-gen{width:100%;background:linear-gradient(180deg,#f99c94 0%,#d2292c 100%);color:#fff;border:none;padding:15px;font-size:13.5px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;letter-spacing:.5px;text-transform:uppercase;flex-shrink:0;box-shadow:0 2px 12px rgba(210,41,44,.3);margin:12px 12px 4px;width:calc(100% - 24px);border-radius:8px;}
.sbv-gen:disabled{opacity:.4;cursor:default;box-shadow:none;}
.sbv-cost-note{font-size:10px;color:#4b5563;text-align:center;margin:0 12px 12px;}
.dsAIV-feed{flex:1;min-width:0;background:#080a10;padding:14px 16px;overflow-y:auto;max-height:calc(100vh - 60px);}
.dsv-grp{margin-bottom:20px;}.dsv-gmeta{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:7px;gap:8px;}.dsv-gp{color:#5a6a7a;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;}.dsv-gtags{display:flex;gap:4px;flex-shrink:0;}.dsv-tag{color:#60a5fa;font-size:10px;background:#0d1929;padding:1px 6px;border-radius:3px;border:1px solid #1e3a5f;}
.dsv-grid{display:grid !important;gap:8px;grid-template-columns:repeat(3,1fr) !important;width:100% !important;}
@media(max-width:900px){.dsv-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){.dsAIV-wrap{flex-direction:column;min-height:0;}.dsAIV-sb{width:100%!important;height:auto!important;max-height:none!important;position:static!important;border-right:none!important;border-bottom:1px solid #1e2a40;overflow-y:visible!important;}.dsAIV-feed{max-height:none!important;padding:14px 12px!important;}.dsv-grid{grid-template-columns:repeat(2,1fr)!important;}}
.dsv-card{position:relative;overflow:hidden;border-radius:7px;background:#111;aspect-ratio:16/9;}
.dsv-card video{width:100%;height:100%;object-fit:contain;background:#111;display:block;}
.dsv-card .dsv-ratio-badge{position:absolute;bottom:5px;left:5px;background:rgba(0,0,0,.75);padding:2px 7px;border-radius:4px;color:#60a5fa;font-size:9.5px;font-weight:700;backdrop-filter:blur(4px);}
.dsv-card .dsv-dur-badge{position:absolute;bottom:5px;left:52px;background:rgba(0,0,0,.75);padding:2px 7px;border-radius:4px;color:#9ca3af;font-size:9.5px;font-weight:700;backdrop-filter:blur(4px);}
.dsv-card .dsv-ctag{position:absolute;top:5px;left:5px;background:rgba(0,0,0,.7);padding:2px 6px;border-radius:3px;color:#6b7280;font-size:9.5px;backdrop-filter:blur(4px);}
.dsv-card-actions{position:absolute;bottom:5px;right:5px;display:flex;gap:2px;opacity:.9;}

.dsv-submitted-badge{position:absolute;bottom:5px;left:5px;background:rgba(37,99,235,.85);color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:2;}
.dsv-cdl,.dsv-csh,.dsv-cmk,.dsv-csav{width:26px !important;height:26px !important;border-radius:5px !important;background:transparent !important;border:none !important;padding:0 !important;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;filter:drop-shadow(0 1px 3px rgba(0,0,0,.85));}
.dsv-cdl:hover,.dsv-csh:hover,.dsv-cmk:hover,.dsv-csav:hover{background:rgba(255,255,255,.18) !important;}
.dsv-sub-badge{position:absolute;top:5px;right:5px;background:#2563eb;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:2;}
.dsv-card .dsv-exp-badge{position:absolute;top:5px;right:5px;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:2;}
.dsv-card .dsv-exp-expired{background:#7f1d1d;color:#f87171;}
.dsv-card .dsv-exp-temp,.dsv-card .dsv-exp-saved{background:transparent;padding:0;border-radius:0;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 1px 2px rgba(0,0,0,.7));}
.dsv-card-expired-ph{display:flex;align-items:center;justify-content:center;height:100%;color:#64748b;font-size:11px;text-align:center;padding:16px;background:#111;}
.dsv-q{border-radius:7px;background:#111;border:1px solid #161616;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;aspect-ratio:16/9;}
.dsv-spin{width:18px;height:18px;border:2px solid #1a1a1a;border-top-color:#2563eb;border-radius:50%;animation:dsvSpin .7s linear infinite;}
@keyframes dsvSpin{to{transform:rotate(360deg)}}
.dsv-ghost{aspect-ratio:16/9;border-radius:7px;background:#0a0a0a;border:1px dashed #161616;}
.dsv-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:300px;gap:12px;opacity:.2;}
.dsv-err{padding:12px 14px;background:#1a0808;border:1px solid #3d1010;border-radius:7px;color:#f87171;font-size:12px;line-height:1.6;grid-column:1/-1;}
#dsvShareModal.open{display:flex!important;}
</style>

<div id="dsAIV">
  <div class="dsAIV-bc">
    <span style="color:#64748b;font-size:11px;">AI Studio <span style="color:#334155;">/</span> <span style="color:#64748b;">Video Generator</span></span>
    <span style="display:flex;align-items:center;gap:5px;color:#e8442a;font-weight:800;font-size:13px;">&#9733; <?php echo (int)$credits; ?> cr</span>
  </div>

  <div class="dsAIV-wrap">
    <!-- SIDEBAR -->
    <div class="dsAIV-sb">

      <!-- Model -->
      <div class="sbv-sec">
        <span class="sbv-lbl">Model</span>
        <div class="sbv-sw">
          <select id="dsvModel" class="sbv-sel">
            <option value="seedance-1.5-pro" selected>⚡ Seedance 1.5 Pro</option>
            <option value="kling-v3.0-4k">🎬 Kling 3.0 (4K)</option>
            <option value="ltx-2.3">⚡ LTX-2.3</option>
          </select>
        </div>
      </div>

      <!-- Prompt -->
      <div class="sbv-prompt">
        <span class="sbv-lbl" style="margin-bottom:0;">Shot</span>
        <textarea id="dsvPmt" class="sbv-ta" rows="4" placeholder="Describe your video..."></textarea>
      </div>

      <!-- References -->
      <div class="sbv-sec">
        <span class="sbv-lbl">References <span style="color:#64748b;font-size:8.5px;font-weight:400;">(optional)</span></span>
        <div class="dsv-refs">
          <label class="dsv-ref-box" data-slot="start" onclick="document.getElementById('dsvStartInput').click()">
            <span class="dsv-ref-placeholder">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="opacity:.5;display:block;margin:0 auto 4px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><polyline points="17 8 12 3 7 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="3" x2="12" y2="15" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
  <span class="dsv-ref-text">Start image</span>
</span>
            <img class="dsv-ref-preview" alt="" hidden />
            <button type="button" class="dsv-ref-clear" data-slot="start" onclick="event.stopPropagation();dsvRefClear('start')" hidden>&times;</button>
          </label>
          <label class="dsv-ref-box" data-slot="end" onclick="document.getElementById('dsvEndInput').click()">
            <span class="dsv-ref-placeholder">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="opacity:.5;display:block;margin:0 auto 4px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><polyline points="17 8 12 3 7 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="3" x2="12" y2="15" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
  <span class="dsv-ref-text">End image</span>
</span>
            <img class="dsv-ref-preview" alt="" hidden />
            <button type="button" class="dsv-ref-clear" data-slot="end" onclick="event.stopPropagation();dsvRefClear('end')" hidden>&times;</button>
          </label>
        </div>
        <input type="file" id="dsvStartInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="dsvRefPicked('start',this)"/>
        <input type="file" id="dsvEndInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="dsvRefPicked('end',this)"/>
      </div>

      <!-- Options -->
      <div class="sbv-opts-row">
        <select id="dsvResolution" class="sbv-opt" style="border-radius:16px;">
           <option value="480p">480</option>
           <option value="720p">720</option>
           <option value="1080p" selected>1080</option>
           <option value="4k" style="display:none;">4K</option>
         </select>
        <select id="dsvDuration" class="sbv-opt" style="border-radius:16px;">
           <?php for ($s = 3; $s <= 20; $s++): ?>
             <option value="<?php echo $s;?>" <?php selected($s, 5); ?>><?php echo $s;?>&Prime;</option>
           <?php endfor; ?>
         </select>
        <select id="dsvRatio" class="sbv-opt" style="border-radius:16px;">
          <?php foreach (['16:9','9:16','1:1','4:3','3:4','21:9'] as $r): ?>
            <option value="<?php echo esc_attr($r); ?>" <?php selected($r, '16:9'); ?>><?php echo esc_html($r); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" id="dsvAudioBtn" class="sbv-opt" data-on="0" onclick="dsvToggleAudio()">🔊 OFF</button>
      </div>

      <!-- Generate -->
      <button id="dsvGB" class="sbv-gen" onclick="dsvGen()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#fff"/></svg>
        <span id="dsvGBL">Generate</span>
      </button>
      <p class="sbv-cost-note">Cost scales with resolution, duration &amp; audio.</p>
    </div>
    <!-- END SIDEBAR -->

    <!-- FEED -->
    <div class="dsAIV-feed" id="dsvF">
      <div id="dsvEmt" class="dsv-empty" <?php echo !empty($groups) ? 'style="display:none;"' : ''; ?>>
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none"><polygon points="5 3 19 12 5 21 5 3" stroke="#fff" stroke-width="1.5"/></svg>
        <p style="color:#6b7280;font-size:13px;margin:0;text-align:center;">Enter a prompt and hit Generate</p>
      </div>
      <div id="dsvLive"></div>
      <?php foreach ($groups as $grp):
        $m = $grp['meta']; $p = $m['prompt'] ?? ''; $r = $m['ratio'] ?? '16:9';
        $dur = $m['duration'] ?? 5; $md = $m['model'] ?? ''; $dt = $m['date'] ?? '';
        $items = array_values(array_filter($grp['items'], function($it){ return is_array($it); }));
        $items = array_values(array_filter($items, function($it){
            $__fid2 = (int) ($it['file_id'] ?? 0);
            if ($__fid2 && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_file_is_available')) {
                $__f_chk2 = ds_hub_get_generated_file($__fid2);
                if ($__f_chk2 && $__f_chk2->status !== 'saved' && !ds_hub_file_is_available($__f_chk2)) { return false; }
            }
            return true;
        }));
        if (empty($items)) continue;
      ?>
      <div class="dsv-grp">
        <div class="dsv-gmeta">
          <span class="dsv-gp" title="<?php echo esc_attr($p); ?>"><?php echo esc_html($p); ?></span>
          <div class="dsv-gtags">
            <span class="dsv-tag"><?php echo esc_html($r); ?></span>
            <span class="dsv-tag"><?php echo esc_html($dt ? date('d M', strtotime($dt)) : ''); ?></span>
          </div>
        </div>
        <div class="dsv-grid">
          <?php foreach ($items as $it):
            $url    = $it['url'] ?? '';
            $pid    = $it['prediction_id'] ?? '';
            $status = $it['status'] ?? 'succeeded';
            $idur   = $it['duration'] ?? $dur;
            $iratio = $it['ratio'] ?? $r;
            $aid    = (int) ($it['attachment_id'] ?? 0);
            $file_id = (int) ($it['file_id'] ?? 0);
            $needs_fix = $file_id && strpos($url, 'ds-hub/v1/file') !== false;
            $exp_badge = '';
            $exp_label = '';
            $is_expired_r2 = false;
            if ($file_id && function_exists('ds_hub_get_generated_file')) {
                $f = ds_hub_get_generated_file($file_id);
                if ($f) {
                    if ($f->status === 'saved') {
                        $exp_badge = 'saved';
                        $exp_label = $f->expires_at ? ('Saved to My Creations — expires ' . date_i18n('F j, Y', strtotime($f->expires_at . ' UTC')) . '.') : 'Saved to My Creations — kept permanently.';
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
            if ($exp_badge === 'expired') continue;
            $already_submitted = $aid && in_array($aid, $submitted_aids, true);
            $su = esc_attr($url); $sp = esc_attr($p);
          ?>
          <?php if ($status === 'pending'): ?>
            <div class="dsv-q" data-pid="<?php echo esc_attr($pid); ?>" data-prompt="<?php echo $sp; ?>" data-model="<?php echo esc_attr($md); ?>">
              <div class="dsv-spin"></div><span style="color:#64748b;font-size:10px;">Processing…</span>
            </div>
          <?php elseif ($status === 'failed'): ?>
            <div class="dsv-ghost" title="Generation failed"></div>
          <?php else: ?>
            <div class="dsv-card" data-card-key="<?php echo $aid ? (int)$aid : 'f'.(int)$file_id; ?>">
              <?php if ($is_expired_r2): ?>
              <div class="dsv-card-expired-ph">This file has expired and was permanently deleted.</div>
              <?php else: ?>
              <video <?php if ($needs_fix): ?>data-fid="<?php echo (int)$file_id; ?>"<?php else: ?>src="<?php echo esc_url($url); ?>"<?php endif; ?> controls preload="metadata" muted playsinline></video>
              <?php endif; ?>
              <span class="dsv-ctag"><?php echo esc_html($md ?: 'AI'); ?></span>
              <span class="dsv-ratio-badge"><?php echo esc_html($iratio); ?></span>
              <span class="dsv-dur-badge"><?php echo (int)$idur; ?>s</span>
              <?php if ($already_submitted): ?>
                <span class="dsv-sub-badge">Submitted</span>
              <?php endif; ?>
                <?php if (!$already_submitted): ?>
                <?php if ($exp_badge === 'temp'): ?>
                <span class="dsv-exp-badge dsv-exp-temp" title="Temporary — will expire soon">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                </span>
                <?php elseif ($exp_badge === 'saved'): ?>
                <span class="dsv-exp-badge dsv-exp-saved" title="Saved">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <?php elseif ($exp_badge === 'expired'): ?>
                <span class="dsv-exp-badge dsv-exp-expired">Expired</span>
                <?php endif; ?>
                <?php endif; ?>
              <?php if ($already_submitted): ?>
                <span class="dsv-submitted-badge" title="Already submitted to Marketplace">Submitted</span>
                <?php endif; ?>
              <div class="dsv-card-actions">
                <?php if (!$is_expired_r2): ?>
                <button class="dsv-cdl" title="Download" onclick="event.stopPropagation();dsvDL('<?php echo $su; ?>',<?php echo $needs_fix ? (int)$file_id : 0; ?>)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                </button>
                <button class="dsv-csh" title="Share" onclick="event.stopPropagation();dsvShareOpen('<?php echo $su; ?>','<?php echo $sp; ?>')">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2.2"/></svg>
                </button>
                <?php endif; ?>
                <?php if (($aid || $file_id) && !$already_submitted && !$is_expired_r2): ?>
                <button class="dsv-cmk" title="Submit to Marketplace" onclick="event.stopPropagation();dsvMktOpen(<?php echo (int)$aid; ?>,<?php echo (int)$file_id; ?>)">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <?php endif; ?>
                <?php if ($file_id && $exp_badge !== 'saved' && !$is_expired_r2): ?>
                <button class="dsv-csav" title="Save to My Creations" onclick="event.stopPropagation();dsvSave(<?php echo (int)$file_id; ?>, this)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- END FEED -->
  </div>
</div>

<script>
(function(){
var G = { busy:false, activeCount:0, maxConcurrent:6, credits:<?php echo (int)$credits; ?>, refStart:null, refEnd:null,
  costBase:{'seedance-1.5-pro':15,'kling-v3.0-4k':65,'ltx-2.3':6},
  submittedAids: <?php echo wp_json_encode( array_map( 'intval', $submitted_aids ) ); ?>,
  mktAid: 0, mktFileId: 0 };
var NONCE = '<?php echo esc_js($nonce); ?>';
var REST  = '<?php echo esc_js($rest); ?>';
var REST_HUB = '<?php echo esc_js($rest_hub); ?>';

function q(id){ return document.getElementById(id); }

function dsvApplyModelConstraints(){
  var model = q('dsvModel').value;
  var resEl = q('dsvResolution');
  var durEl = q('dsvDuration');
  var audioBtn = q('dsvAudioBtn');

  // Resolution: Kling 3.0 4K always outputs native 4K -- lock the dropdown.
  if (resEl) {
    var opts = Array.prototype.slice.call(resEl.options);
    if (model === 'kling-v3.0-4k') {
      opts.forEach(function(o){ if (o.value === '4k') { o.hidden = false; o.disabled = false; } else { o.hidden = true; o.disabled = true; } });
      resEl.value = '4k';
      resEl.disabled = true;
    } else {
      opts.forEach(function(o){ if (o.value === '4k') { o.hidden = true; o.disabled = true; } else { o.hidden = false; o.disabled = false; } });
      if (resEl.value === '4k') resEl.value = '1080p';
      resEl.disabled = false;
    }
  }

  // Duration: per-model ranges (Kling 3-15s, LTX 5-20s, others 4-12s).
  if (durEl) {
    var range = model === 'kling-v3.0-4k' ? [3,15] : (model === 'ltx-2.3' ? [5,20] : [4,12]);
    var durOpts = Array.prototype.slice.call(durEl.options);
    durOpts.forEach(function(o){
      var v = parseInt(o.value, 10);
      var inRange = v >= range[0] && v <= range[1];
      o.hidden = !inRange; o.disabled = !inRange;
    });
    var curV = parseInt(durEl.value, 10);
    if (isNaN(curV) || curV < range[0] || curV > range[1]) durEl.value = '5';
  }

  // Audio: LTX-2.3 generates audio automatically with no on/off control -- hide the toggle.
  if (audioBtn) {
    if (model === 'ltx-2.3') {
      audioBtn.style.display = 'none';
      audioBtn.dataset.on = '0';
      audioBtn.textContent = '\uD83D\uDD0A OFF';
      audioBtn.className = 'sbv-opt';
    } else {
      audioBtn.style.display = '';
    }
  }

  dsvUpdateCost();
}
function dsvCost(){
  var model = q('dsvModel').value;
  var dur = parseInt(q('dsvDuration').value, 10) || 5;

  if (model === 'kling-v3.0-4k') {
    // Flat $/sec regardless of audio -- linear interpolation between anchor points.
    var d = Math.max(3, Math.min(15, dur));
    var loD = 3, loC = 136, hiD = 15, hiC = 660;
    return Math.max(1, Math.ceil(loC + (d - loD) * (hiC - loC) / (hiD - loD)));
  }

  if (model === 'ltx-2.3') {
    // Audio is automatic (no price impact) -- interpolate per resolution tier.
    var res = q('dsvResolution').value;
    var tiers = {
      '480p':  {loD:5, loC:15, hiD:20, hiC:50},
      '720p':  {loD:5, loC:20, hiD:20, hiC:70},
      '1080p': {loD:5, loC:26, hiD:20, hiC:90}
    };
    var t = tiers[res] || tiers['720p'];
    var d = Math.max(t.loD, Math.min(t.hiD, dur));
    return Math.max(1, Math.ceil(t.loC + (d - t.loD) * (t.hiC - t.loC) / (t.hiD - t.loD)));
  }

  var base = G.costBase[model] || 15;
  var res = q('dsvResolution').value;
  var resMult = res === '480p' ? 0.6 : (res === '1080p' ? 1.8 : 1);
  var durMult = dur / 5;
  var audioMult = q('dsvAudioBtn').dataset.on === '1' ? 2 : 1;
  return Math.max(1, Math.ceil(base * resMult * durMult * audioMult));
}
function dsvUpdateCost(){
  var c = dsvCost();
  var lbl = q('dsvGBL');
  if (lbl) lbl.textContent = 'Generate (' + c + ' cr)';
}
document.addEventListener('DOMContentLoaded', function(){
  ['dsvResolution','dsvDuration'].forEach(function(id){
    var el = q(id); if (el) el.addEventListener('change', dsvUpdateCost);
  });
  var dsvModelEl = q('dsvModel');
  if (dsvModelEl) dsvModelEl.addEventListener('change', dsvApplyModelConstraints);
  dsvApplyModelConstraints();
  dsvUpdateCost();

  // Resume any generations still "processing" from before a page refresh.
  document.querySelectorAll('.dsv-q[data-pid]').forEach(function(qc){
    var pid = qc.dataset.pid;
    if (pid) dsvPoll(pid, qc.dataset.prompt || '', qc.dataset.model || 'seedance-1.5-pro', qc, null);
  });
});

window.dsvToggleAudio = function(){
  var btn = q('dsvAudioBtn');
  var on = btn.dataset.on === '1';
  btn.dataset.on = on ? '0' : '1';
  btn.textContent = on ? '🔊 OFF' : '🔊 ON';
  btn.className = on ? 'sbv-opt' : 'sbv-opt on';
  dsvUpdateCost();
};

window.dsvRefPicked = function(slot, input){
  var file = input.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(e){
    var dataUrl = e.target.result;
    if (slot === 'start') G.refStart = dataUrl; else G.refEnd = dataUrl;
    var box = document.querySelector('.dsv-ref-box[data-slot="' + slot + '"]');
    var preview = box.querySelector('.dsv-ref-preview');
    var clearBtn = box.querySelector('.dsv-ref-clear');
    var placeholder = box.querySelector('.dsv-ref-placeholder');
    preview.src = dataUrl; preview.hidden = false; clearBtn.hidden = false;
    if (placeholder) placeholder.hidden = true;
  };
  reader.readAsDataURL(file);
};
window.dsvRefClear = function(slot){
  if (slot === 'start') { G.refStart = null; q('dsvStartInput').value = ''; }
  else { G.refEnd = null; q('dsvEndInput').value = ''; }
  var box = document.querySelector('.dsv-ref-box[data-slot="' + slot + '"]');
  var placeholder = box.querySelector('.dsv-ref-placeholder');
  if (placeholder) placeholder.hidden = false;
  box.querySelector('.dsv-ref-preview').hidden = true;
  box.querySelector('.dsv-ref-clear').hidden = true;
};

window.dsvGen = function(){
  if (G.activeCount >= G.maxConcurrent) { alert('You can generate up to ' + G.maxConcurrent + ' videos at once — please wait for one to finish before starting another.'); return; }
  var prompt = q('dsvPmt').value.trim();
  if (!prompt) { alert('Please enter a prompt first.'); return; }
  var cost = dsvCost();
  if (G.credits < cost) { alert('Not enough credits. Need ' + cost + '.'); return; }

  var model = q('dsvModel').value;
  var ratio = q('dsvRatio').value;
  var duration = parseInt(q('dsvDuration').value, 10);
  var resolution = q('dsvResolution').value;
  var audio = q('dsvAudioBtn').dataset.on === '1';

  G.activeCount++;
  if (G.activeCount >= G.maxConcurrent) q('dsvGB').disabled = true;
  q('dsvEmt').style.display = 'none';

  var grp = document.createElement('div'); grp.className = 'dsv-grp';
  grp.innerHTML = '<div class="dsv-gmeta"><span class="dsv-gp">' + prompt.substring(0,120) + '</span>'
    + '<div class="dsv-gtags"><span class="dsv-tag">' + ratio + '</span><span class="dsv-tag dsv-gst">Queued</span></div></div>';
  var grid = document.createElement('div'); grid.className = 'dsv-grid';
  var qc = document.createElement('div'); qc.className = 'dsv-q';
  qc.innerHTML = '<div class="dsv-spin"></div><span style="color:#64748b;font-size:10px;">Queued</span>';
  grid.appendChild(qc);
  grp.appendChild(grid);
  var live = q('dsvLive'); live.insertBefore(grp, live.firstChild);
  q('dsvF').scrollTop = 0;

  var body = {
    model: model, prompt: prompt, ratio: ratio, duration: duration,
    resolution: resolution, audio: audio ? 1 : 0
  };
  if (G.refStart) body.start_image = G.refStart;
  if (G.refEnd)   body.end_image   = G.refEnd;

  fetch(REST + 'generate-video', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE}, body: JSON.stringify(body) })
    .then(function(r){ return r.json().then(function(j){ if(!r.ok) throw new Error(j && j.message ? j.message : 'HTTP ' + r.status); return j; }); })
    .then(function(d){
      if (d.error) { dsvErrGrid(grid, d.message || 'Generation failed.'); dsvReset(); return; }
      if (typeof d.credits_left === 'number') { G.credits = d.credits_left; }
      if (d.prediction_id) {
        qc.dataset.pid = d.prediction_id;
        dsvPoll(d.prediction_id, prompt, model, qc, { ratio: ratio, duration: duration });
      } else {
        dsvErrGrid(grid, 'No task ID returned.'); dsvReset();
      }
    })
    .catch(function(e){ dsvErrGrid(grid, e.message || 'Network error.'); dsvReset(); });
};

function dsvReset(){ G.activeCount = Math.max(0, G.activeCount - 1); q('dsvGB').disabled = (G.activeCount >= G.maxConcurrent); }

function dsvErrGrid(grid, msg){
  grid.innerHTML = '<div class="dsv-err">' + msg.replace(/</g,'&lt;') + '</div>';
}

function dsvPoll(pid, prompt, model, slotEl, meta){
  var attempts = 0;
  var maxAttempts = 90; // 90 x 5s = 7.5 min before giving up with a message
  var timer = setInterval(function(){
    attempts++;
    fetch(REST + 'poll-video', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
      body: JSON.stringify({ prediction_id: pid, prompt: prompt, model: model }) })
      .then(function(r){
        return r.json().catch(function(){ return null; }).then(function(j){
          return { ok: r.ok, status: r.status, body: j };
        });
      })
      .then(function(res){
        if (!res.ok || !res.body) {
          console.error('[DS Video] poll-video HTTP ' + res.status, res.body);
          if (attempts >= maxAttempts) {
            clearInterval(timer);
            if (slotEl) { slotEl.innerHTML = '<span style="color:#f87171;font-size:10px;text-align:center;padding:0 8px;">Server error while checking status — see console, or check My Creations shortly.</span>'; }
            dsvReset();
          }
          return; // keep retrying — could be a transient server hiccup
        }
        var d = res.body;
        if (d.status === 'succeeded' && d.videos && d.videos.length) {
          clearInterval(timer);
          var v = d.videos[0];
          var url = v.url || '';
          var su = url.replace(/'/g, "\\'");
          var sp = (prompt || '').replace(/'/g, "\\'");
          var ratio = (meta && meta.ratio) || '16:9';
          var dur   = (meta && meta.duration) || 5;
          var card = document.createElement('div'); card.className = 'dsv-card';
          card.innerHTML = '<video src="' + url + '" controls preload="metadata" muted playsinline></video>'
            + '<span class="dsv-ctag">' + model + '</span>'
            + '<span class="dsv-ratio-badge">' + ratio + '</span>'
            + '<span class="dsv-dur-badge">' + dur + 's</span>'
            + '<div class="dsv-card-actions">'
            + '<button class="dsv-cdl" title="Download" onclick="event.stopPropagation();dsvDL(\'' + su + '\',0,\'' + String(pid || '').replace(/[^A-Za-z0-9_.:-]/g, '') + '\')">'
            + '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>'
            + '</button>'
            + '<button class="dsv-csh" title="Share" onclick="event.stopPropagation();dsvShareOpen(\'' + su + '\',\'' + sp + '\')">'
            + '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2.2"/></svg>'
            + '</button></div>';
          if (slotEl && slotEl.parentNode) slotEl.parentNode.replaceChild(card, slotEl);
          /* the group's status tag said "Queued" for ever */
          var dsvGrp = card.closest ? card.closest('.dsv-grp') : null;
          var dsvTag = dsvGrp ? dsvGrp.querySelector('.dsv-gst') : null;
          if (dsvTag) { dsvTag.textContent = 'Done'; }
          dsvReset();
          // Fire-and-forget: copy the video into the Media Library now so it
          // gets an attachment_id (needed for the marketplace button, which
          // only shows up after a page refresh). Doesn't block the UI —
          // runs independently of WP-Cron, which isn't required for this.
          fetch(REST + 'rehost-video', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
            body: JSON.stringify({ prediction_id: pid }) }).catch(function(){});
        } else if (d.status === 'failed') {
          clearInterval(timer);
          if (slotEl) { slotEl.className = 'dsv-ghost'; slotEl.innerHTML = ''; slotEl.title = d.error || 'Generation failed'; }
          dsvReset();
        } else if (d.status === 'error') {
          console.warn('[DS Video] poll-video reported an error:', d.error);
          if (attempts >= maxAttempts) {
            clearInterval(timer);
            if (slotEl) { slotEl.innerHTML = '<span style="color:#f87171;font-size:10px;text-align:center;padding:0 8px;">' + (d.error || 'Error checking status') + '</span>'; }
            dsvReset();
          }
          // otherwise keep polling — could be a transient WaveSpeed hiccup
        } else if (attempts >= maxAttempts) {
          // still "processing" after 7.5 min — stop hammering the server,
          // but the job may still finish; a page refresh will resume polling.
          clearInterval(timer);
          if (slotEl) { slotEl.innerHTML = '<span style="color:#94a3b8;font-size:10px;text-align:center;padding:0 8px;">Taking longer than expected. Refresh the page in a bit — it\'ll pick up where it left off.</span>'; }
          dsvReset();
        }
        // else: still processing, keep polling
      })
      .catch(function(e){
        console.error('[DS Video] poll-video network error:', e);
        if (attempts >= maxAttempts) {
          clearInterval(timer);
          if (slotEl) { slotEl.innerHTML = '<span style="color:#f87171;font-size:10px;text-align:center;padding:0 8px;">Network error — see console.</span>'; }
          dsvReset();
        }
      });
  }, 5000);
}

/* ── Download (reuses the shared /ds/v1/download proxy) ─────────────── */
window.dsvDL = function(url, fid, pid){
  /* pid: a video made a moment ago is found by its generation id (its link changes when it is stored) */
  var proxyUrl = fid ? (REST + 'download-video?file_id=' + fid + '&_wpnonce=' + NONCE) : (REST + 'download-video?' + (pid ? 'prediction_id=' + encodeURIComponent(pid) + '&' : '') + 'url=' + encodeURIComponent(url) + '&_wpnonce=' + NONCE);
  var a = document.createElement('a'); a.href = proxyUrl; a.download = '';
  document.body.appendChild(a); a.click(); a.remove();
};

/* ── Share modal ──────────────────────────────────────────────────── */
window.dsvShareOpen = function(url, prompt){
  G.shareUrl = url; G.sharePrompt = prompt;
  q('dsvShareModal').classList.add('open');
  q('dsvShareModal').style.display = 'flex';
};
window.dsvShareClose = function(){
  q('dsvShareModal').classList.remove('open');
  q('dsvShareModal').style.display = 'none';
};
window.dsvShareVia = function(via){
  var url = G.shareUrl || '';
  var text = encodeURIComponent('Check out this AI video I made on Designstager!');
  var link = encodeURIComponent(url);
  if (via === 'whatsapp') window.open('https://wa.me/?text=' + text + '%20' + link, '_blank');
  else if (via === 'twitter') window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + link, '_blank');
  else if (via === 'facebook') window.open('https://www.facebook.com/sharer/sharer.php?u=' + link, '_blank');
  else if (via === 'copy') {
    navigator.clipboard.writeText(url).then(function(){
      var btn = q('dsvShareCopyBtn');
      if (btn) { var orig = btn.textContent; btn.textContent = 'Copied!'; setTimeout(function(){ btn.textContent = orig; }, 1500); }
    });
  }
};

window.dsvSave = function(fileId, btn){
  if (!fileId) return;
  if (btn) { btn.disabled = true; btn.style.opacity = '.5'; }
  fetch(REST_HUB + 'save/' + fileId, { method:'POST', headers:{'X-WP-Nonce':NONCE} })
    .then(function(r){ return r.json().catch(function(){ return null; }).then(function(j){ return { ok:r.ok, body:j }; }); })
    .then(function(res){
      if (!res.ok || !res.body || res.body.code) {
        var msg = (res.body && res.body.message) || 'Could not save — please try again.';
        alert(msg);
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
        return;
      }
      location.reload();
    })
    .catch(function(){
      alert('Network error — please try again.');
      if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
    });
};
/* ── Marketplace submit modal (video) ────────────────────────────── */
window.dsvMktOpen = function(aid, fileId){
  G.mktAid = aid;
  G.mktFileId = fileId || 0;
  var cb = q('dsvMktConsent'); if (cb) cb.checked = false;
  var btn = q('dsvMktSubmitBtn');
  btn.textContent = 'Submit to Marketplace'; btn.style.opacity = '.5'; btn.style.pointerEvents = 'none';
  q('dsvMktModal').style.display = 'flex';
};
window.dsvMktClose = function(){
  q('dsvMktModal').style.display = 'none';
};
window.dsvGenerateVideoWatermark = function(attachmentId){
  try {
    if (!attachmentId) return;
    if (!window.MediaRecorder || !HTMLCanvasElement.prototype.captureStream) return;
    fetch('/wp-json/wp/v2/media/' + attachmentId).then(function(r){ return r.ok ? r.json() : null; }).then(function(media){
      if (!media || !media.source_url) return;
      var vid = document.createElement('video');
      vid.crossOrigin = 'anonymous';
      vid.muted = true;
      vid.playsInline = true;
      vid.preload = 'auto';
      vid.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:1px;height:1px;';
      vid.src = media.source_url;
      document.body.appendChild(vid);
      var cleanup = function(){ try { vid.pause(); vid.removeAttribute('src'); vid.load(); vid.remove(); } catch(e){} };
      var killTimer = null;
      vid.addEventListener('loadedmetadata', function(){
        try {
          var maxSide = 960;
          var scale = Math.min(1, maxSide / Math.max(vid.videoWidth, vid.videoHeight));
          var w = Math.max(2, Math.round(vid.videoWidth * scale));
          var h = Math.max(2, Math.round(vid.videoHeight * scale));
          var canvas = document.createElement('canvas');
          canvas.width = w; canvas.height = h;
          var ctx = canvas.getContext('2d');
          var mimeCandidates = ['video/webm;codecs=vp9','video/webm;codecs=vp8','video/webm'];
          var mime = mimeCandidates.filter(function(m){ return window.MediaRecorder.isTypeSupported && window.MediaRecorder.isTypeSupported(m); })[0] || 'video/webm';
          var stream = canvas.captureStream(25);
          var recorder = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 2500000 });
          var chunks = [];
          recorder.ondataavailable = function(e){ if (e.data && e.data.size) chunks.push(e.data); };
          recorder.onstop = function(){
            cleanup();
            if (killTimer) clearTimeout(killTimer);
            if (!chunks.length) return;
            var blob = new Blob(chunks, { type: mime });
            var fd = new FormData();
            fd.append('attachment_id', attachmentId);
            fd.append('watermarked_video', blob, 'wm.webm');
            fetch(REST + 'mkt-upload-video-watermark', {
              method: 'POST',
              headers: { 'X-WP-Nonce': NONCE },
              body: fd
            }).catch(function(){});
          };
          var draw = function(){
            if (vid.paused || vid.ended) return;
            ctx.drawImage(vid, 0, 0, w, h);
            ctx.save();
            ctx.globalAlpha = 0.13;
            ctx.fillStyle = '#fff';
            var fontSize = Math.max(10, Math.round(w / 22));
            ctx.font = 'bold ' + fontSize + 'px sans-serif';
            ctx.translate(w/2, h/2);
            ctx.rotate(-28 * Math.PI / 180);
            var stepX = fontSize * 8, stepY = fontSize * 3.4;
            for (var y = -h; y < h * 1.5; y += stepY) {
              for (var x = -w; x < w * 1.5; x += stepX) {
                ctx.fillText('designstager.com', x - w/2, y - h/2);
              }
            }
            ctx.restore();
            requestAnimationFrame(draw);
          };
          vid.addEventListener('play', function(){ requestAnimationFrame(draw); }, { once: true });
          vid.addEventListener('ended', function(){ try { recorder.stop(); } catch(e){} });
          recorder.start();
          vid.play().catch(function(){ try { recorder.stop(); } catch(e){} cleanup(); });
          killTimer = setTimeout(function(){ try { recorder.stop(); } catch(e){} try { vid.pause(); } catch(e){} cleanup(); }, 45000);
        } catch(e) { cleanup(); }
      }, { once: true });
      vid.addEventListener('error', function(){ cleanup(); });
    }).catch(function(){});
  } catch(e) {}
};
window.dsvMktSubmit = function(){
  if (!G.mktAid && !G.mktFileId) return;
  var btn = q('dsvMktSubmitBtn');
  btn.textContent = 'Submitting…'; btn.style.pointerEvents = 'none'; btn.style.opacity = '.6';
  fetch(REST + 'creator-from-video', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body: JSON.stringify({ attachment_id: G.mktAid, file_id: G.mktFileId }) })
    .then(function(r){ return r.json().catch(function(){ return null; }).then(function(j){ return { ok:r.ok, body:j }; }); })
    .then(function(res){
      if (!res.ok || !res.body || res.body.code) {
        var msg = (res.body && (res.body.message || res.body.error)) || 'Could not submit — please try again.';
        alert(msg);
        btn.textContent = 'Submit to Marketplace'; btn.style.pointerEvents = 'auto'; btn.style.opacity = '1';
        return;
      }
      // Reuse the existing generic mark-submitted route (same one images use) --
      // use the materialized attachment_id from the response, NOT G.mktAid (which is 0 for R2-only videos).
      var realAid = res.body.attachment_id || G.mktAid;
      fetch(REST + 'mark-submitted', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
        body: JSON.stringify({ attachment_id: realAid }) }).catch(function(){});
      try { if (window.dsvGenerateVideoWatermark) window.dsvGenerateVideoWatermark(realAid); } catch(e){}
     
      G.submittedAids.push(realAid);
      q('dsvMktModal').style.display = 'none';
      dsvShowSuccessModal();
    })
    .catch(function(e){
      console.error('[DS Video] creator-from-video error:', e);
      alert('Network error — please try again.');
      btn.textContent = 'Submit to Marketplace'; btn.style.pointerEvents = 'auto'; btn.style.opacity = '1';
    });
};
window.dsvShowSuccessModal = function(){
  q('dsvMktSuccess').style.display = 'flex';
};
window.dsvMktSuccessClose = function(){
  q('dsvMktSuccess').style.display = 'none';
  location.reload();
};
document.addEventListener('change', function(e){
  if (e.target && e.target.id === 'dsvMktConsent') {
    var btn = q('dsvMktSubmitBtn');
    if (e.target.checked) { btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }
    else { btn.style.opacity = '.5'; btn.style.pointerEvents = 'none'; }
  }
});

/* ── One-time backfill for videos generated before the rehost fix ──── */
window.dsvBackfillStart = function(){
  var btn = q('dsvBackfillBtn');
  if (!btn || btn.dataset.running) return;
  btn.dataset.running = '1';
  btn.style.opacity = '.6'; btn.style.pointerEvents = 'none';

  function step(){
    fetch(REST + 'rehost-backfill', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE}, body: '{}' })
      .then(function(r){ return r.json().catch(function(){ return null; }); })
      .then(function(d){
        if (!d || !d.success) {
          btn.textContent = 'Could not fix videos — try again later';
          return;
        }
        if (d.remaining > 0) {
          btn.textContent = 'Fixing… ' + (d.total - d.remaining) + ' / ' + d.total;
          step();
        } else {
          btn.textContent = 'Done! Reloading…';
          setTimeout(function(){ location.reload(); }, 1200);
        }
      })
      .catch(function(e){
        console.error('[DS Video] rehost-backfill error:', e);
        btn.textContent = 'Network error — try again later';
      });
  }
  btn.textContent = 'Fixing…';
  step();
};
(function(){
  var vids = Array.prototype.slice.call(document.querySelectorAll('video[data-fid]'));
  if (!vids.length) return;
  var byFid = {}; var ids = [];
  vids.forEach(function(v){
    var fid = v.getAttribute('data-fid');
    if (fid && fid !== '0') {
      if (!byFid[fid]) { byFid[fid] = []; ids.push(fid); }
      byFid[fid].push(v);
    }
  });
  if (!ids.length) return;
  function chunk(arr,size){var out=[];for(var k=0;k<arr.length;k+=size)out.push(arr.slice(k,k+size));return out;}
  chunk(ids, 40).forEach(function(group){
    fetch(REST_HUB + 'files?ids=' + group.join(',') + '&_wpnonce=' + NONCE, {credentials:'include'})
      .then(function(r){return r.json();})
      .then(function(map){
        group.forEach(function(fid){
          var url = map && map[fid];
          (byFid[fid]||[]).forEach(function(v){
            if (url) { v.src = url; v.load(); }
          });
        });
      })
      .catch(function(){});
  });
})();
})();
</script>
<?php
$__dsq_rest = trailingslashit(rest_url('ds/v1'));
$__dsq_rest_hub = trailingslashit(rest_url('ds-hub/v1'));
$__dsq_nonce = wp_create_nonce('wp_rest');
$__dsq_site = esc_js(home_url());
?>
<script>
var DMC_REST = '<?php echo esc_js($__dsq_rest); ?>';
var DMC_HUB = '<?php echo esc_js($__dsq_rest_hub); ?>';
var DMC_NONCE = '<?php echo esc_js($__dsq_nonce); ?>';
var DMC_SITE = '<?php echo $__dsq_site; ?>';
function dmcEsc(s){ var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function dmcFmtTs(ts){ if (!ts) return ''; try { return new Date(ts * 1000).toLocaleDateString(); } catch(e){ return ''; } }
var DSMP_THEMES={'navy-pro':['#1B2A4A','#FFFFFF'],'dark-modern':['#0D1117','#FFFFFF'],'clean-corporate':['#FFFFFF','#1A365D'],'bold-red':['#1A0000','#FFFFFF'],'corporate-dark':['#111827','#F9FAFB'],'clean-white':['#F8FAFC','#0F172A'],'gradient-pro':['#0F0C29','#FFFFFF'],'bold-impact':['#111111','#FFFFFF'],'sunset-glow':['#2D1B2E','#FFFFFF'],'forest-green':['#0F2419','#FFFFFF'],'ocean-breeze':['#F0F9FF','#0C4A6E'],'golden-hour':['#1C1408','#FFFFFF'],'lavender-mist':['#F5F3FF','#3B0764'],'slate-minimal':['#F8F9FA','#1E293B'],'cherry-bold':['#FFF5F5','#7F1D1D'],'midnight-teal':['#042F2E','#FFFFFF'],'coral-sunset':['#FFF7ED','#7C2D12'],'royal-purple':['#1E0A3C','#FFFFFF'],'mint-fresh':['#F0FDF4','#14532D'],'rose-gold':['#FFF1F2','#881337'],'steel-blue':['#0F172A','#F1F5F9'],'amber-warm':['#1C1917','#FEF3C7'],'arctic-white':['#FFFFFF','#0F172A'],'crimson-night':['#1A0A0A','#FFFFFF'],'aurora':['linear-gradient(135deg,#0F2027,#1A3A4A)','#FFFFFF'],'sunset-blaze':['linear-gradient(135deg,#7B0000,#DD2476)','#FFFFFF'],'cosmic-purple':['linear-gradient(135deg,#1A0050,#4A00E0)','#FFFFFF'],'ocean-depth':['linear-gradient(135deg,#000028,#003366)','#FFFFFF'],'emerald-dusk':['linear-gradient(135deg,#0A2E1A,#134E5E)','#FFFFFF'],'rose-gold-glow':['linear-gradient(135deg,#0D0720,#1D2671)','#FFFFFF'],'amber-blaze':['linear-gradient(135deg,#1A0A00,#7A3800)','#FFFFFF'],'midnight-bloom':['linear-gradient(135deg,#0B0020,#0B3D47)','#FFFFFF'],'deep-navy':['linear-gradient(135deg,#0A1628,#0D3B6E)','#FFFFFF'],'dark-crimson':['linear-gradient(135deg,#1A0000,#5C0000)','#FFFFFF'],'dark-slate':['linear-gradient(135deg,#0F172A,#1E293B)','#F1F5F9'],'dark-forest':['linear-gradient(135deg,#052010,#0A3020)','#FFFFFF']};

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
      '#dsmc-capbar:hover{opacity:.82;text-decoration:underline;}' +
      '.dsmy-card{position:relative;border-radius:8px;overflow:hidden;background:#111;cursor:pointer;aspect-ratio:16/9;}' +
      '.dsmy-card img{width:100%;height:100%;object-fit:contain;background:#111;display:block;transition:transform .25s;}' +
      '.dsmy-card:hover img{transform:scale(1.03);}' +
      '.dsmy-card-tag{position:absolute;top:5px;left:5px;background:rgba(0,0,0,.7);color:#64748b;font-size:9px;padding:2px 6px;border-radius:3px;backdrop-filter:blur(4px);}' +
      '.dsmy-card-sub{position:absolute;top:5px;right:5px;background:rgba(37,99,235,.8);color:#fff;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;}' +
      '.dsmy-card-exp{position:absolute;bottom:5px;left:5px;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;color:#fff;}' +
      '.dsmy-card-exp-expired{background:rgba(220,38,38,.88);}' +
      '.dsmy-card-exp-temp,.dsmy-card-exp-saved{background:transparent;padding:0;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 1px 2px rgba(0,0,0,.7));}' +
      '.dsmy-card-expired-ph{display:flex;align-items:center;justify-content:center;height:100%;color:#64748b;font-size:11px;text-align:center;padding:10px;}' +
      '.dsmy-card-actions{position:absolute;bottom:5px;right:5px;display:flex;gap:2px;opacity:.9;transition:opacity .2s;}' +
      '.dsmy-card:hover .dsmy-card-actions{opacity:1;}' +
      '.dsmy-btn-sm{width:26px !important;height:26px !important;border-radius:5px !important;background:transparent !important;border:none !important;outline:none !important;box-shadow:none !important;padding:0 !important;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;filter:drop-shadow(0 1px 3px rgba(0,0,0,.85));}' +
      '.dsmy-btn-sm:hover{background:rgba(255,255,255,.18) !important;}' +
      '.ds-spin{width:18px;height:18px;border:2px solid #1a1a1a;border-top-color:#2563eb;border-radius:50%;animation:dsSpin .7s linear infinite;}' +
      '@keyframes dsSpin{to{transform:rotate(360deg)}}' +
      '.ds-img-spin{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1;pointer-events:none;}' +
      '.dsmy-card img[data-fid]{opacity:0;transition:opacity .25s;}' +
      '.dsmy-card img[data-fid].ds-img-loaded{opacity:1;}' +
      '.dsmv-card{background:#111;border-radius:8px;overflow:hidden;border:1px solid #1e1e1e;}' +
      '.dsmv-card video{width:100%;aspect-ratio:16/9;background:#000;display:block;}' +
      '.dsmv-meta{padding:8px 10px;}' +
      '.dsmv-prompt{font-size:11px;color:#cbd5e1;margin:0 0 6px;line-height:1.4;max-height:32px;overflow:hidden;}' +
      '.dsmv-badge{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px;margin-bottom:6px;}' +
      '.dsmv-badge.expired{background:#2a0505;color:#f87171;}' +
      '.dsmv-badge.saved,.dsmv-badge.temp{background:transparent;padding:0;display:inline-flex;align-items:center;justify-content:center;}' +
      '.dsmv-actions{display:flex;gap:5px;padding:0 10px 10px;}' +
      '.dsmv-btn{flex:1;text-align:center;padding:6px 4px;font-size:10.5px;font-weight:700;border-radius:6px;border:1px solid #262626;background:#1a1a1a;color:#cbd5e1;cursor:pointer;text-decoration:none;}' +
      '.dsmv-btn:hover{background:#242424;}' +
      '.dsmv-btn.save{color:#4ade80;}' +
      '.dsmv-btn.delete{color:#f87171;}' +
      '.dsmv-empty,.dsmv-loading{grid-column:1/-1;text-align:center;padding:60px 20px;color:#64748b;font-size:13px;}' +
      '.dsmp-section{padding:14px 16px;}' +
      '.dsmp-section h3{font-size:13px;color:#f1f5f9;margin:0 0 8px;font-weight:700;}' +
      '.dsmp-card{background:#111;border:1px solid #1e1e1e;border-radius:8px;overflow:hidden;}' +
      '.dsmp-thumb{aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;padding:12px;text-align:center;box-sizing:border-box;overflow:hidden;}' +
      '.dsmp-thumb-title{font-size:12px;font-weight:700;line-height:1.35;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}' +
      '.dsmp-thumb-sub{font-size:10px;line-height:1.3;margin-top:4px;opacity:.8;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}' +
      '.dsmp-thumb-generic{background:linear-gradient(135deg,#1e293b,#0f172a);}' +
      '.dsmp-thumb-generic .dsmp-thumb-title{color:#e2e8f0;}' +
      '.dsmp-thumb-photo{background-size:cover;background-position:center;position:relative;}' +
      '.dsmp-thumb-photo .dsmp-thumb-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 35%,rgba(0,0,0,.78) 100%);display:flex;align-items:flex-end;padding:10px;box-sizing:border-box;}' +
      '.dsmp-thumb-photo .dsmp-thumb-title{color:#fff;}' +
      '.dsmp-thumb-photo .dsmp-thumb-sub{color:#f1f5f9;}' +
      '.dsmp-thumb-icon{font-size:26px;margin-bottom:6px;opacity:.5;}' +
      '.dsmp-body{padding:8px 10px 0;}' +
      '.dsmp-card h4{font-size:12px;color:#f1f5f9;margin:0 0 4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}' +
      '.dsmp-card p{font-size:10.5px;color:#64748b;margin:0 0 8px;}';
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

  function dsmsSetImgSrc(img, url){
img.onload = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
img.onerror = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
img.src = url;
}
function dsmsResolveImgSrcs(root){
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
if (fb0) dsmsSetImgSrc(img, fb0);
}
});
if (!ids.length) return;
function chunk(arr,size){var out=[];for(var k=0;k<arr.length;k+=size)out.push(arr.slice(k,k+size));return out;}
chunk(ids, 40).forEach(function(group){
fetch(DMC_HUB + 'files?ids=' + group.join(',') + '&_wpnonce=' + DMC_NONCE, {credentials:'include'})
.then(function(r){return r.json();})
.then(function(map){
group.forEach(function(fid){
var url = map && map[fid];
(byFid[fid]||[]).forEach(function(img){
if (url) { dsmsSetImgSrc(img, url); } else { var fb = img.getAttribute('data-fallback'); if (fb) dsmsSetImgSrc(img, fb); }
});
});
})
.catch(function(){
group.forEach(function(fid){
(byFid[fid]||[]).forEach(function(img){ var fb = img.getAttribute('data-fallback'); if (fb) dsmsSetImgSrc(img, fb); });
});
});
});
}
function dsmsLoadImages(){
    var el = document.getElementById('dsms-images');
    fetch(DMC_REST + 'my-saved-images', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d || !d.success || !d.count) { el.innerHTML = '<div class="dsms-empty">No saved images.</div>'; return; }
      el.innerHTML = d.html;
      dsmsResolveImgSrcs(el);
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
</script>

    <?php
    return ob_get_clean();
}
