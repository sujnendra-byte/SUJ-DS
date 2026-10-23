<?php
/**
 * Code Snippets export — DS AI Studio Images (Save to My Creations) - Hide Expired [No Blank Slot] [Temp Icon] [Saved Icon] [Check Icon] [Loading Spinner] [Live Admin Credit Prices] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE] [CLONE]
 *
 * Snippet ID : 774
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-088
 * Modified   : 2026-09-09 02:42:52
 * Load order : 093 of 144
 */
/**
 * Snippet Name: DS AI Images
 * Shortcode: [ds_ai_images]
 * Run: Everywhere
 * NOTE: NO tabs here - tabs are handled by Snippet 5 (DS AI Studio)
 */
add_shortcode('ds_ai_images', 'ds_ai_images_shortcode');
function ds_ai_images_shortcode() {
    if (!is_user_logged_in()) return '<div style="text-align:center;padding:40px;color:#888;">Please <a href="'.esc_url(wp_login_url(get_permalink())).'" style="color:#2563eb;">log in</a>.</div>';
    $uid     = get_current_user_id();
    $credits = (int)ds_get_credits($uid);
    $armap   = ['1:1'=>'1/1','16:9'=>'16/9','9:16'=>'9/16','4:3'=>'4/3','3:4'=>'3/4','3:2'=>'3/2','2:3'=>'2/3','21:9'=>'21/9'];

    $user_obj   = wp_get_current_user();
    $user_roles = (array)($user_obj->roles ?? []);
    $roles_str  = implode(',', $user_roles);

    $ls_plan = strtolower(
        get_user_meta($uid, 'ds_plan', true) ?:
        get_user_meta($uid, 'lsq_plan', true) ?:
        get_user_meta($uid, 'lemonsqueezy_plan', true) ?:
        get_user_meta($uid, '_lsq_variant', true) ?:
        (function_exists('ds_access_plan') ? ds_access_plan($uid) : (function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : '')) ?:
        ''
    );

    $is_admin = in_array('administrator', $user_roles) || in_array('editor', $user_roles) || in_array('shop_manager', $user_roles);

    $is_pro_or_business = $is_admin
        || in_array('ds_pro', $user_roles)
        || in_array('ds_business', $user_roles)
        || in_array('pro_member', $user_roles)
        || in_array('business_member', $user_roles)
        || in_array('pmpro_3', $user_roles)
        || in_array('pmpro_4', $user_roles)
        || strpos($roles_str, 'pro') !== false
        || strpos($roles_str, 'business') !== false
        || strpos($roles_str, 'premium_plus') !== false
        || in_array($ls_plan, ['pro','business','enterprise'])
        || strpos($ls_plan, 'pro') !== false
        || strpos($ls_plan, 'business') !== false;

    $is_premium = $is_pro_or_business
    || in_array('ds_premium', $user_roles)
    || in_array('premium_member', $user_roles)
        || in_array('pmpro_2', $user_roles)
        || strpos($roles_str, 'premium') !== false
        || in_array($ls_plan, ['premium','standard'])
        || strpos($ls_plan, 'premium') !== false;

    if ($is_pro_or_business) {
        $user_plan     = (strpos($roles_str,'business') !== false || strpos($ls_plan,'business') !== false) ? 'business' : 'pro';
        $allowed_sizes = ['HD','FHD','2K','3K','4K'];
    } elseif ($is_premium) {
        $user_plan     = 'premium';
        $allowed_sizes = ['HD','FHD'];
    } else {
        $user_plan     = 'free';
        $allowed_sizes = ['HD'];
    }
	$free_tier = function_exists('ds_user_is_free_tier') ? ds_user_is_free_tier($uid) : ($user_plan === 'free');
    $allowed_sizes_json = json_encode($allowed_sizes);
    $is_admin_debug = $is_admin;

    $groups    = array();
$group_idx = array();
$seen_urls = array(); // BUGFIX: prevents the same generated image being shown twice

$all_gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
foreach ($all_gens as $gen) {
    if (!is_array($gen)) continue;
    $url = $gen['url'] ?? '';
    if (empty($url)) continue;
    if (isset($seen_urls[$url])) continue; // BUGFIX: skip exact duplicate image
    $seen_urls[$url] = true;
    $p   = $gen['prompt'] ?? '';
    $dt  = $gen['date']   ?? '';
    $key = md5($p . substr($dt, 0, 16));
    if (!isset($group_idx[$key])) {
        $groups[]          = array('items' => array(), 'meta' => $gen);
        $group_idx[$key]   = count($groups) - 1;
    }
    $groups[$group_idx[$key]]['items'][] = $gen;
}

// Old format fallback — only add if not already in new gens
$old_history = get_user_meta($uid, 'ds_generated_images', true) ?: array();
foreach ($old_history as $item) {
    if (!is_array($item)) continue;
    $url = $item['url'] ?? '';
    if (empty($url)) continue;
    if (isset($seen_urls[$url])) continue; // BUGFIX: skip exact duplicate image
    $seen_urls[$url] = true;
    $p   = $item['prompt'] ?? '';
    $dt  = $item['date']   ?? '';
    $key = md5($p . substr($dt, 0, 16));
    if (!isset($group_idx[$key])) {
        $groups[]        = array('items' => array($item), 'meta' => $item);
        $group_idx[$key] = count($groups) - 1;
    }
}
    $nonce   = wp_create_nonce('wp_rest');
    $rest    = trailingslashit(rest_url('ds/v1'));
    $site    = esc_js(home_url());

    $all_sizes   = ['HD'=>'1280px','FHD'=>'1920px','2K'=>'2048px','3K'=>'3072px','4K'=>'4096px'];
    $default_size = in_array('2K',$allowed_sizes) ? '2K' : $allowed_sizes[0];

    ob_start();
?>
<!-- LIGHTBOX -->
<div id="dsLB" onclick="if(event.target===this)dsLBClose()" style="display:none;position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.96);align-items:center;justify-content:center;flex-direction:column;">
  <button onclick="dsLBClose()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:rgba(255,255,255,.5);font-size:24px;cursor:pointer;line-height:1;padding:4px;transition:color .15s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">&#x2715;</button>
  <button id="dsLBPrev" onclick="dsLBNav(-1)" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.4);font-size:42px;cursor:pointer;line-height:1;padding:4px;transition:color .15s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.4)'">&#8249;</button>
  <button id="dsLBNext" onclick="dsLBNav(1)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.4);font-size:42px;cursor:pointer;line-height:1;padding:4px;transition:color .15s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.4)'">&#8250;</button>
  <img id="dsLBImg" src="" alt="" style="max-width:88vw;max-height:78vh;border-radius:10px;object-fit:contain;display:block;box-shadow:0 8px 60px rgba(0,0,0,.9);"/>
  <p id="dsLBCap" style="color:#6b7280;font-size:12px;max-width:600px;text-align:center;margin:10px 20px;line-height:1.5;"></p>
  <div style="display:flex;gap:10px;margin-top:4px;flex-wrap:wrap;justify-content:center;">
    <button onclick="dsLBDL()" style="background:#2563eb;color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
      Download Full Resolution
    </button>
    <button onclick="dsLBShare()" style="background:#7c3aed;color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2"/></svg>
      Share
    </button>
    <button onclick="dsLBMarket()" id="dsLBMktBtn" style="background:#d97706;color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
      + Marketplace
    </button>
  </div>
</div>

<!-- SHARE MODAL -->
<div id="dsShareModal" style="display:none;position:fixed;inset:0;z-index:9999999;background:rgba(0,0,0,.7);align-items:center;justify-content:center;">
  <div style="background:#1a1a2e;border-radius:18px;padding:24px;max-width:380px;width:90%;border:1px solid #2d2d4a;">
    <h3 style="color:#fff;font-size:15px;font-weight:800;margin:0 0 4px;">Share this Design</h3>
    <p style="color:#6b7280;font-size:12px;margin:0 0 14px;">Created with AI on Designstager</p>
    <img id="dsSharePreview" src="" style="width:100%;border-radius:10px;max-height:180px;object-fit:contain;background:#0a0a0a;margin-bottom:14px;"/>
    <div style="background:linear-gradient(135deg,#0f1f3d,#1e1040);border-radius:10px;padding:12px 14px;margin-bottom:14px;">
      <div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:3px;">Designstager AI Studio</div>
      <div style="font-size:11px;color:rgba(255,255,255,.4);">Create stunning AI designs for free &bull; designstager-com-895433.hostingersite.com</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
      <button onclick="dsShareVia('whatsapp')" style="padding:9px;border-radius:8px;background:#25D366;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">WhatsApp</button>
      <button onclick="dsShareVia('copy')" id="dsShareCopyBtn" style="padding:9px;border-radius:8px;background:#6366f1;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">Copy Link</button>
      <button onclick="dsShareVia('twitter')" style="padding:9px;border-radius:8px;background:#1da1f2;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">Twitter / X</button>
      <button onclick="dsShareVia('facebook')" style="padding:9px;border-radius:8px;background:#1877f2;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;">Facebook</button>
    </div>
    <button onclick="dsShareClose()" style="width:100%;padding:9px;border-radius:8px;border:1px solid #2d2d4a;background:transparent;color:#6b7280;font-size:12px;cursor:pointer;">Close</button>
  </div>
</div>

<!-- MARKETPLACE SUBMIT MODAL -->
<div id="dsMktModal" style="display:none;position:fixed;inset:0;z-index:9999999;background:rgba(0,0,0,.7);align-items:center;justify-content:center;">
  <div style="background:#fdf9f3;border-radius:18px;padding:28px;max-width:420px;width:92%;border:1px solid #e7e0d3;box-shadow:0 20px 60px rgba(0,0,0,.4);">
    <h3 style="font-size:20px;font-weight:800;color:#1a1a2e;margin:0 0 8px;letter-spacing:-.3px;">Submit to Marketplace</h3><?php if ($is_premium): ?>
    <p style="font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;">Share your design and earn credits on approval.</p>
    <p style="font-size:12px;color:#94a3b8;margin:0 0 8px;line-height:1.5;">Your design will be submitted using the original prompt as the title.</p>
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:11px 13px;font-size:12px;color:#15803d;margin-bottom:14px;line-height:1.6;">
      Earn <strong id="dsMktCbAmt">50%</strong> (1 credit) back when admin approves.
    </div>
    <label style="color:#3d4659;display:flex;width:100%;align-items:center;gap:10px;margin:14px 0 18px;font-size:13px;line-height:1.5;cursor:pointer;">
      <input type="checkbox" id="dsMktConsent" style="width:18px;height:18px;flex-shrink:0;margin:0;accent-color:#d97706;cursor:pointer;"/>
      <span style="flex:1;color:#3d4659;">I confirm I have the right to submit this AI-generated image to the Designstager Marketplace and grant Designstager a license to display and distribute it.</span>
    </label>
    <div style="display:flex;gap:8px;">
      <button onclick="dsMktClose()" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;">Cancel</button>
      <button id="dsMktSubmitBtn" onclick="dsMktSubmit()" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;opacity:.5;pointer-events:none;">Submit to Marketplace</button>
    </div>
  </div>
</div>
		  <?php else: ?>
    <p style="font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;">Submitting to the Marketplace and earning credit back is a Premium feature.</p>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:11px 13px;font-size:12px;color:#9a3412;margin-bottom:18px;line-height:1.6;">
      Upgrade to Premium to submit your images and earn <strong>1 credit</strong> back when admin approves.
    </div>
    <div style="display:flex;gap:8px;">
      <button onclick="dsMktClose()" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;">Maybe Later</button>
      <a href="/pricing" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;text-align:center;text-decoration:none;display:inline-block;box-sizing:border-box;">Upgrade to Premium</a>
    </div>
  </div>
</div>
		  <?php endif; ?><!-- SUCCESS MODAL -->
<div id="dsMktSuccess" style="display:none;position:fixed;inset:0;z-index:9999999;background:rgba(0,0,0,.75);align-items:center;justify-content:center;">
  <div style="background:#fdf9f3;border-radius:18px;padding:28px;max-width:380px;width:92%;border:1px solid #e7e0d3;box-shadow:0 20px 60px rgba(0,0,0,.4);text-align:center;position:relative;overflow:hidden;">
    <div id="dsMktConfetti" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;"></div>
    <div style="font-size:64px;text-align:center;margin:8px 0 12px;animation:dsBounce .6s ease-out;">🎉</div>
    <h3 style="font-size:20px;font-weight:800;color:#1a1a2e;margin:0 0 8px;">Submitted Successfully!</h3>
    <p id="dsMktSuccessMsg" style="font-size:13px;color:#5a6478;margin:0 0 20px;line-height:1.6;">Your design is awaiting review.</p>
    <button onclick="dsHideSuccessModal()" style="width:100%;padding:13px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:14px;font-weight:800;border:none;cursor:pointer;">Awesome!</button>
  </div>
</div>

<style>
#dsAI,#dsAI *{box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}#dsAI{background:#0a0a0a;color:#fff;border-radius:10px;overflow:hidden;}.dsAI-wrap{display:flex;align-items:stretch;min-height:600px;}.dsAI-sb{width:270px;flex-shrink:0;background:#10131c;border-right:1px solid #1e2a40;display:flex;flex-direction:column;position:sticky;top:0;height:calc(100vh - 60px);overflow-y:auto;scrollbar-width:thin;scrollbar-color:#1a1a1a transparent;}.dsAI-sb::-webkit-scrollbar{width:3px;}.dsAI-sb::-webkit-scrollbar-thumb{background:#1e1e1e;border-radius:3px;}.dsAI-bc{padding:9px 12px;border-bottom:1px solid #141414;display:flex;align-items:center;justify-content:space-between;background:#080808;flex-shrink:0;}.sb-sec{padding:12px 14px;border-bottom:1px solid #1e2a40;flex-shrink:0;}.sb-lbl{color:#94a3b8;font-size:9.5px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;margin-bottom:6px;display:flex;align-items:center;gap:4px;}.sb-sw{position:relative;}.sb-sw::after{content:'v';position:absolute;right:9px;top:50%;transform:translateY(-50%);color:#6b7280;font-size:10px;pointer-events:none;}.sb-sel{width:100%;background:#191c25 !important;background-color:#191c25 !important;border:1px solid #1f222b;border-radius:4px;color:#f8fafc !important;padding:9px 28px 9px 11px;font-size:12.5px;font-weight:500;appearance:none;-webkit-appearance:none;-moz-appearance:none;cursor:pointer;outline:none;transition:all .15s;letter-spacing:.1px;}.sb-sel:focus,.sb-sel:active,.sb-sel:hover{border-color:#3b82f6;background:#191c25 !important;color:#f8fafc !important;}.sb-sel option{background:#191c25 !important;color:#f8fafc !important;}.sb-prompt{padding:14px 14px 10px;border-bottom:1px solid #1e2a40;display:flex;flex-direction:column;flex:1;background:rgba(59,130,246,.06);border-left:3px solid #3b82f6;position:relative;}.sb-prompt::before{content:'PROMPT';position:absolute;top:10px;right:12px;font-size:9px;font-weight:700;letter-spacing:1.5px;color:#60a5fa;opacity:.85;}.sb-ta{width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#f1f5f9;font-size:13px;resize:none;outline:none;line-height:1.7;font-family:inherit;flex:1;min-height:130px;max-height:220px;margin-top:8px;padding:10px 12px;box-sizing:border-box;}.sb-ta::placeholder{color:#4a6080;font-style:italic;}.sb-cr-row{padding:8px 12px;border-bottom:1px solid #141414;display:flex;align-items:center;gap:6px;flex-shrink:0;}.sb-cb{width:26px;height:26px;border-radius:6px;border:1px solid #1f222b;background:#191c25;color:#94a3b8;font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s;}.sb-cb:hover{background:#263a5a;color:#fff;border-color:#3b82f6;}.sb-cnt-num{color:#f1f5f9;font-size:13px;font-weight:700;min-width:14px;text-align:center;}.sb-gen{width:70%;margin:0 auto;background:linear-gradient(180deg,#f99c94 0%,#d2292c 100%);color:#fff;border:none;padding:15px;font-size:13.5px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;letter-spacing:.5px;text-transform:uppercase;transition:all .2s;flex-shrink:0;box-shadow:0 2px 12px rgba(210,41,44,.3);}.sb-gen:disabled{opacity:.4;cursor:default;box-shadow:none;}.sb-gen:not(:disabled):hover{background:linear-gradient(180deg,#d2292c 0%,#b91c1c 100%);box-shadow:0 4px 20px rgba(210,41,44,.5);transform:translateY(-1px);}.dsAI-feed{flex:1;min-width:0;background:#080a10;padding:14px 16px;overflow-y:auto;max-height:calc(100vh - 60px);}.ds-grp{margin-bottom:20px;}.ds-gmeta{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:7px;gap:8px;}.ds-gp{color:#5a6a7a;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;}.ds-gtags{display:flex;gap:4px;flex-shrink:0;}.ds-tag{color:#60a5fa;font-size:10px;background:#0d1929;padding:1px 6px;border-radius:3px;border:1px solid #1e3a5f;}.ds-grid{display:grid !important;gap:6px;grid-template-columns:repeat(4,1fr) !important;width:100% !important;}@media(max-width:900px){.ds-grid{grid-template-columns:repeat(2,1fr);}}@media(max-width:768px){.dsAI-wrap{flex-direction:column;min-height:0;}.dsAI-sb{width:100%!important;height:auto!important;max-height:none!important;position:static!important;border-right:none!important;border-bottom:1px solid #1e2a40;overflow-y:visible!important;}.dsAI-feed{max-height:none!important;padding:14px 12px!important;}.ds-grid{grid-template-columns:repeat(2,1fr)!important;}}.ds-card{position:relative;overflow:hidden;border-radius:7px;cursor:pointer;background:#111;aspect-ratio:16/9;}.ds-card img{width:100%;height:100%;object-fit:contain;background:#111;display:block;transition:transform .25s;}.ds-card:hover img{transform:scale(1.03);}.ds-size-btn.model-locked{opacity:.3;border-style:dashed;border-color:#ef4444 !important;color:#6b7280 !important;}.ds-size-btn.model-locked:hover{border-color:#ef4444 !important;background:#1a0808 !important;}.ds-size-lock{font-size:6.5px;font-weight:800;display:block;color:#f59e0b;margin-top:1px;letter-spacing:.3px;}.ds-size-btn.disabled{opacity:.45;cursor:not-allowed;border-style:dashed;border-color:#2a3348;}.ds-ghost{aspect-ratio:16/9;border-radius:7px;background:#0a0a0a;border:1px dashed #161616;}.ds-card .ds-ratio-badge{position:absolute;bottom:5px;left:5px;background:rgba(0,0,0,.75);padding:2px 7px;border-radius:4px;color:#60a5fa;font-size:9.5px;font-weight:700;backdrop-filter:blur(4px);letter-spacing:.3px;}.ds-card .ds-ctag{position:absolute;top:5px;left:5px;background:rgba(0,0,0,.7);padding:2px 6px;border-radius:3px;color:#6b7280;font-size:9.5px;backdrop-filter:blur(4px);}.ds-card-actions{position:static;display:flex;gap:2px;opacity:.9;transition:opacity .2s;}
.ds-card .ds-sub-badge{position:absolute !important;top:5px !important;right:5px !important;bottom:auto !important;background:#2563eb;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:10;}
.ds-sub-badge{position:absolute;top:5px;right:5px;background:#2563eb;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:2;}.ds-card-actions{position:absolute;bottom:5px;right:5px;display:flex;gap:2px;opacity:.9;transition:opacity .2s;overflow:visible;}
.ds-card-actions .ds-sub-badge{position:fixed;}.ds-cdl,.ds-csh,.ds-cmk,.ds-csave{width:26px !important;height:26px !important;border-radius:5px !important;background:transparent !important;border:none !important;outline:none !important;box-shadow:none !important;padding:0 !important;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;filter:drop-shadow(0 1px 3px rgba(0,0,0,.85));}.ds-cdl:hover,.ds-csh:hover,.ds-cmk:hover,.ds-csave:hover{background:rgba(255,255,255,.18) !important;}.ds-card .ds-exp-badge{position:absolute;top:5px;right:5px;padding:2px 7px;border-radius:4px;font-size:8.5px;font-weight:700;backdrop-filter:blur(4px);color:#fff;z-index:2;}.ds-card .ds-exp-badge.ds-exp-expired{background:rgba(220,38,38,.85);}.ds-card .ds-exp-badge.ds-exp-temp,.ds-card .ds-exp-badge.ds-exp-saved{background:transparent;backdrop-filter:none;padding:0;border-radius:0;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 1px 2px rgba(0,0,0,.7));}.ds-q{border-radius:7px;background:#111;border:1px solid #161616;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;aspect-ratio:16/9;}.ds-spin{width:18px;height:18px;border:2px solid #1a1a1a;border-top-color:#2563eb;border-radius:50%;animation:dsSpin .7s linear infinite;}@keyframes dsSpin{to{transform:rotate(360deg)}}.ds-img-spin{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1;pointer-events:none;}.ds-card img[data-fid]{opacity:0;transition:opacity .25s;}.ds-card img[data-fid].ds-img-loaded{opacity:1;}#dsLB.open{display:flex!important;}#dsShareModal.open,#dsMktModal.open{display:flex!important;}.ds-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:300px;gap:12px;opacity:.2;}.ds-err{padding:12px 14px;background:#1a0808;border:1px solid #3d1010;border-radius:7px;color:#f87171;font-size:12px;line-height:1.6;grid-column:1/-1;}.ds-size-btn{flex:1;padding:5px 0;border-radius:5px;border:1px solid #252d3a;background:#141414;color:#8b98a8;font-size:10px;font-weight:700;cursor:pointer;transition:all .15s;letter-spacing:.3px;text-align:center;}.ds-size-btn.on{background:#2563eb;color:#fff;border-color:#2563eb;}.ds-size-btn:hover:not(.on){background:#1e1e1e;color:#9ca3af;}#dsRefDrop:hover,#dsRefDrop.drag-over{border-color:#2563eb!important;background:#0a1220!important;}@keyframes dsBounce{0%{transform:scale(0) rotate(-180deg);opacity:0;}60%{transform:scale(1.2) rotate(10deg);}100%{transform:scale(1) rotate(0);opacity:1;}}
@keyframes dsMktFall{0%{transform:translateY(0) rotate(0);opacity:1;}100%{transform:translateY(420px) rotate(720deg);opacity:0;}}
#dsMktSuccess.open{display:flex!important;}
</style>

<div id="dsAI">
  <div class="dsAI-bc">
    <span style="color:#64748b;font-size:11px;">AI Studio <span style="color:#334155;">/</span> <span style="color:#64748b;">Image Generator</span></span>

    <span id="dsCrEl" style="display:none;">&#9733; <?php echo $credits; ?> cr</span>
  </div>

  <div class="dsAI-wrap">
    <!-- SIDEBAR -->
    <div class="dsAI-sb">

      <!-- Model -->
      <div class="sb-sec">
        <span class="sb-lbl">Model</span>
        <div class="sb-sw">
          <?php
$__img_costs = function_exists('ds_get_model_costs') ? ds_get_model_costs() : array('cloudflare'=>2,'cloudflare-klein'=>3,'flux-schnell'=>4,'flux-dev'=>7,'qwen-image'=>8,'qwen-edit'=>10,'seedream'=>8,'flux-pro'=>10,'nanobana'=>13);
$__img_dd = array(
    array('cloudflare','Cloudflare FLUX-1-Schnell',' FREE/day',false),
    array('cloudflare-klein','Cloudflare FLUX.2 Klein 9B','',true),
    array('flux-schnell','Flux Schnell','',false),
    array('flux-dev','Flux Dev +ref','',false),
    array('qwen-image','Qwen Image +ref','',false),
    array('qwen-edit','Qwen Image Edit +ref','',false),
    array('seedream','Seedream 5 +ref','',false),
    array('flux-pro','Flux Pro','',false),
    array('nanobana','NanoBanana 2','',false),
);
?>
<select id="dsModel" class="sb-sel">
<?php foreach ($__img_dd as $__row): list($__mk,$__lbl,$__suf,$__isSel) = $__row; $__mc = isset($__img_costs[$__mk]) ? (int)$__img_costs[$__mk] : 4; ?>
            <option value="<?php echo esc_attr($__mk); ?>" data-cost="<?php echo esc_attr($__mc); ?>"<?php echo $__isSel ? ' selected' : ''; ?>><?php echo esc_html($__lbl); ?><?php echo esc_html($__suf); ?></option>
<?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Style -->
      <div class="sb-sec">
        <span class="sb-lbl">Style</span>
        <div class="sb-sw">
          <select id="dsStyle" class="sb-sel">
            <option value="">Auto</option>
            <option value="photorealistic, DSLR, ultra sharp">Photorealistic</option>
            <option value="cinematic, dramatic lighting, film grain">Cinematic</option>
            <option value="anime, vibrant, sharp linework">Anime</option>
            <option value="oil painting, classical, textured canvas">Oil Painting</option>
            <option value="watercolor, soft washes, artistic">Watercolor</option>
            <option value="minimalist, clean, simple">Minimalist</option>
            <option value="3D render, octane, volumetric light">3D Render</option>
            <option value="pencil sketch, detailed, graphite">Sketch</option>
            <option value="neon lights, cyberpunk, dark city">Neon Cyberpunk</option>
            <option value="digital art, concept art, artstation">Digital Art</option>
          </select>
        </div>
      </div>

      <!-- Prompt -->
      <div class="sb-sec" style="padding-bottom:8px;">
        <span class="sb-lbl">Prompt</span>
        <textarea id="dsPmt" class="sb-ta" rows="3" placeholder="Describe your image..."></textarea>
        <div style="text-align:right;margin-top:2px;"><span id="dsCC" style="color:#64748b;font-size:9px;">0/500</span></div>
      </div>

      <!-- Reference Image -->
      <div class="sb-sec" id="dsRefSec">
        <span class="sb-lbl">Reference Image <span id="dsRefLbl" style="color:#64748b;font-size:8.5px;font-weight:400;">(optional)</span></span>
        <div id="dsRefDrop" onclick="document.getElementById('dsRefInput').click()" style="border:1.5px dashed #2a3a55;border-radius:7px;padding:10px 8px;text-align:center;cursor:pointer;background:#0d1420;transition:border-color .15s;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="opacity:.25;display:block;margin:0 auto 5px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><polyline points="17 8 12 3 7 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="3" x2="12" y2="15" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
          <span id="dsRefTxt" style="color:#94a3b8;font-size:11px;line-height:1.5;">Click or drag to upload<br/>JPG, PNG, WEBP</span>
        </div>
        <input type="file" id="dsRefInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;" onchange="dsRefPicked(this)"/>
        <div id="dsRefPreview" style="display:none;margin-top:7px;">
          <div style="position:relative;">
            <img id="dsRefImg" src="" alt="Reference" style="width:100%;border-radius:6px;max-height:140px;object-fit:contain;background:#0d1420;border:1px solid #1e1e1e;display:block;"/>
            <button onclick="dsRefClear()" style="position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,.8);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;">&#x2715;</button>
          </div>
          <div style="margin-top:6px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
              <span style="color:#64748b;font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;">Influence Strength</span>
              <span id="dsRefStrVal" style="color:#2563eb;font-size:9px;font-weight:700;">75%</span>
            </div>
            <input type="range" id="dsRefStrength" min="10" max="95" value="75" step="5" oninput="document.getElementById('dsRefStrVal').textContent=this.value+'%'" style="width:100%;height:3px;accent-color:#2563eb;cursor:pointer;"/>
            <div style="display:flex;justify-content:space-between;margin-top:2px;">
              <span style="color:#64748b;font-size:8.5px;">Follow ref</span>
              <span style="color:#64748b;font-size:8.5px;">Follow prompt</span>
            </div>
          </div>
          <p style="color:#4b5563;font-size:9px;margin:4px 0 0;line-height:1.4;">Works with Flux Dev, Seedream and Cloudflare Klein</p>
        </div>
        <div id="dsRefErr" style="display:none;color:#f87171;font-size:10px;margin-top:4px;"></div>
      </div>

      <!-- Count + Ratio -->
      <div class="sb-cr-row">
        <button class="sb-cb" onclick="dsChC(-1)">&#x2212;</button>
        <span id="dsCN" class="sb-cnt-num">1</span>
        <button class="sb-cb" onclick="dsChC(1)">&#x2B;</button>
        <div style="flex:1;"></div>
        <div class="sb-sw" style="width:auto;">
          <select id="dsRatio" class="sb-sel" style="padding:5px 22px 5px 7px;font-size:11.5px;width:auto;">
            <option value="1:1">1:1</option>
            <option value="16:9" selected>16:9</option>
            <option value="9:16">9:16</option>
            <option value="4:3">4:3</option>
            <option value="3:4">3:4</option>
            <option value="3:2">3:2</option>
            <option value="2:3">2:3</option>
            <option value="21:9">21:9</option>
          </select>
        </div>
      </div>

      <!-- Output Size -->
      <div style="padding:7px 12px;border-bottom:1px solid #141414;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:5px;margin-bottom:5px;">
          <span style="color:#94a3b8;font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">OUTPUT SIZE</span>
          <span id="dsSizeInfo" style="color:#64748b;font-size:9.5px;margin-left:auto;">1280px</span>
        </div>
        <div style="display:flex;gap:3px;">
          <?php foreach($all_sizes as $sz=>$px):
            $allowed = in_array($sz,$allowed_sizes);
            $on      = ($sz===$default_size) ? ' on' : '';
            $dis_cls = $allowed ? '' : ' disabled';
            $plan_tip = !$allowed ? ' title="'.(in_array($sz,['2K','3K','4K'])?'Pro/Business plan required':'Premium plan required').'"' : '';
          ?>
          <button class="ds-size-btn<?php echo $on.$dis_cls; ?>" data-size="<?php echo $sz; ?>" onclick="dsSetSize(this)"<?php echo $plan_tip; ?>><?php echo $sz; ?><?php if(!$allowed): ?><span class="ds-size-lock"><?php echo in_array($sz,['2K','3K','4K'])?'PRO+':'PRE'; ?></span><?php endif; ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Free daily note -->
      <div id="dsFreeDayNote" style="display:none;padding:6px 12px;background:#1a1200;border-top:1px solid #2a2000;font-size:10px;color:#f59e0b;text-align:center;flex-shrink:0;">Free plan: 2 images/day free on Cloudflare FLUX-1-Schnell</div>
      <!-- Generate -->
      <button id="dsGB" class="sb-gen" onclick="dsGen()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#fff"/></svg>
        <span id="dsGBL">Generate (2 cr)</span>
      </button>
    </div>
    <!-- END SIDEBAR -->

    <!-- FEED -->
    <div class="dsAI-feed" id="dsF">
      <div id="dsEmt" class="ds-empty" <?php echo !empty($groups)?'style="display:none;"':''; ?>>
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#fff" stroke-width="1.5"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="#fff" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" fill="#fff"/></svg>
        <p style="color:#6b7280;font-size:13px;margin:0;text-align:center;">Enter a prompt and hit Generate</p>
      </div>
      <div id="dsLive"></div>
      <?php foreach($groups as $grp):
        $m=$grp['meta']; $p=$m['prompt']??''; $r=$m['ratio']??'1:1';
        $md=$m['model']??''; $dt=$m['date']??'';
        $items = array_filter($grp['items'], function($it){ $u=is_array($it)?($it['url']??''):$it; return !empty($u); });
        $items = array_values($items);
        $items = array_filter($items, function($it){
            $__fid2 = is_array($it) ? (int)($it['file_id'] ?? 0) : 0;
            if ($__fid2 && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_file_is_available')) {
                $__f_chk2 = ds_hub_get_generated_file($__fid2);
                if ($__f_chk2 && $__f_chk2->status !== 'saved' && !ds_hub_file_is_available($__f_chk2)) { return false; }
            }
            return true;
        });
        $items = array_values($items);
        if(empty($items)) continue;
      ?>
      <div class="ds-grp">
        <div class="ds-gmeta">
          <span class="ds-gp" title="<?php echo esc_attr($p);?>"><?php echo esc_html($p);?></span>
          <div class="ds-gtags">
            <span class="ds-tag"><?php echo esc_html($r);?></span>
            <span class="ds-tag"><?php echo esc_html($dt?date('d M',strtotime($dt)):'');?></span>
          </div>
        </div>
        <div class="ds-grid">
          <?php for($si=0;$si<4;$si++): if(isset($items[$si])):
            $it=$items[$si]; $u=is_array($it)?($it['url']??''):$it;
            $aid=is_array($it)?($it['attachment_id']??0):0;
            $fid = is_array($it) ? (int)($it['file_id'] ?? 0) : 0;
            if ($fid && function_exists('ds_hub_file_proxy_url')) { $u = ds_hub_file_proxy_url($fid) . '?_wpnonce=' . $nonce; $u_thumb = ds_hub_file_proxy_url($fid) . '?variant=thumb&_wpnonce=' . $nonce; $u_disp = ds_hub_file_proxy_url($fid) . '?variant=display&_wpnonce=' . $nonce; }
            if ($fid && function_exists('ds_hub_get_generated_file') && function_exists('ds_hub_file_is_available')) { $__f_chk = ds_hub_get_generated_file($fid); if ($__f_chk && $__f_chk->status !== 'saved' && !ds_hub_file_is_available($__f_chk)) { continue; } }
            $ir=$m['ratio']??'1:1'; $su=esc_attr($u); $su_disp=esc_attr(!empty($u_disp)?$u_disp:$u); $sp=esc_attr($p);
            $submitted = false;
          ?>
          <div class="ds-card" onclick="dsLBO('<?php echo $su_disp;?>','<?php echo $sp;?>',<?php echo (int)$aid;?>)">
            <img data-fid="<?php echo (int)$fid;?>" data-fallback="<?php echo esc_url(!empty($u_thumb)?$u_thumb:$u);?>" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="<?php echo $sp;?>" loading="lazy"/>
            <div class="ds-img-spin"><div class="ds-spin"></div></div>
            <span class="ds-ctag"><?php echo esc_html($md?:'AI');?></span>
            <span class="ds-ratio-badge"><?php echo esc_html($ir);?></span>
            <?php
            $__fexp = '';
            if ($fid && function_exists('ds_hub_get_generated_file')) {
                $__f = ds_hub_get_generated_file($fid);
                if ($__f) {
                    if ($__f->status === 'saved') { $__fexp = 'saved'; }
                    elseif (function_exists('ds_hub_file_is_available') && !ds_hub_file_is_available($__f)) { $__fexp = 'expired'; }
                    elseif ($__f->expires_at) { $__fexp = 'temp'; }
                }
            }
            ?>
            <?php if ($__fexp) : ?>
            <?php if ($__fexp === 'temp') : ?>
            <span class="ds-exp-badge ds-exp-temp" title="Temporary — will expire soon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
            </span>
            <?php elseif ($__fexp === 'saved') : ?>
            <span class="ds-exp-badge ds-exp-saved" title="Saved">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <?php else : ?>
            <span class="ds-exp-badge ds-exp-<?php echo esc_attr($__fexp); ?>">Expired</span>
            <?php endif; ?>
            <?php endif; ?>
            <?php if($aid && $submitted): ?>
              <span class="ds-sub-badge">Submitted</span>
            <?php endif; ?>
            <div class="ds-card-actions">
              <button class="ds-cdl" title="Download" onclick="event.stopPropagation();dsDL('<?php echo $su;?>')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
              </button>
              <button class="ds-csh" title="Share" onclick="event.stopPropagation();dsShareOpen('<?php echo $su;?>','<?php echo $sp;?>')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2.2"/></svg>
              </button>
              <?php if ($fid && !empty($__fexp) && $__fexp !== 'saved' && $__fexp !== 'expired') : ?>
              <button class="ds-csave" title="Save to My Creations" onclick="event.stopPropagation();dsHubSaveCard(<?php echo (int)$fid; ?>, this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/></svg>
              </button>
              <?php endif; ?>
              <?php
              $submitted = false;
              $submitted = is_array($it) && !empty($it['submitted']);
              if(!$submitted && $aid) $submitted = (bool)get_post_meta($aid,'ds_marketplace_submitted',true);
              $cost_map  = ['cloudflare'=>2,'cloudflare-klein'=>3,'flux-schnell'=>4,'flux-dev'=>7,'seedream'=>8,'flux-pro'=>10,'nanobana'=>13,'qwen-image'=>8];
              $card_cost = $cost_map[strtolower($md)] ?? 2;
              $card_pct  = $card_cost > 0 ? round((1/$card_cost)*100) : 50;
              ?>
              <?php if(($aid || $fid) && !$submitted): ?>
              <button class="ds-cmk" title="Submit to Marketplace" onclick="event.stopPropagation();dsMktOpen(<?php echo (int)$aid;?>,<?php echo (int)$fid;?>,1,<?php echo $card_pct;?>)">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
              </button>
              <?php endif; ?>
            </div>
            <?php if($aid && $submitted): ?>
            <span class="ds-sub-badge">Submitted</span>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="ds-ghost"></div>
          <?php endif; endfor; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- END FEED -->
  </div>
</div>

<script>
(function(){
var G={cnt:1,busy:false,pid:null,poll:null,size:'<?php echo esc_js($default_size);?>',lbList:[],lbIdx:0,
  costs:<?php echo wp_json_encode( function_exists('ds_get_model_costs') ? ds_get_model_costs() : array('cloudflare'=>2,'cloudflare-klein'=>3,'flux-schnell'=>4,'flux-dev'=>7,'flux-pro'=>10,'seedream'=>8,'nanobana'=>13,'qwen-image'=>8,'qwen-edit'=>10) ); ?>,resOv:<?php $__img_opt2=get_option('ds_image_credit_settings');$__img_ov2=(is_array($__img_opt2)&&isset($__img_opt2['resolution_overrides'])&&is_array($__img_opt2['resolution_overrides']))?$__img_opt2['resolution_overrides']:array();if(empty($__img_opt2)&&empty($__img_ov2)){$__img_ov2=array('nanobana'=>array('4K'=>19));}echo wp_json_encode((object)$__img_ov2); ?>,
  allowedSizes:<?php echo $allowed_sizes_json;?>,
  plan:'<?php echo esc_js($user_plan);?>',
  freeTier:<?php echo $free_tier ? 'true' : 'false';?>,
  refImage:null,
	   refImage:null,freeUsed:0,submittedAids:[],
  lbUrl:'',lbPrompt:'',lbAid:0,
  mktAid:0,mktCb:0,shareUrl:'',sharePrompt:''
};
var NONCE='<?php echo esc_js($nonce);?>';
var HUBFILES='<?php echo esc_js( rest_url('ds-hub/v1/files') );?>';
var HUBSAVE = '<?php echo esc_js( rest_url('ds-hub/v1/save/') ); ?>';
window.dsHubSaveCard = function(fid, btn){
  if (!fid || !btn) return;
  btn.disabled = true;
  var svg = btn.innerHTML;
  fetch(HUBSAVE + fid, { method: 'POST', headers: {'X-WP-Nonce': NONCE}, credentials: 'include' })
  .then(function(r){ return r.json().then(function(d){ return {ok: r.ok, d: d}; }); })
  .then(function(res){
    if (res.ok) {
      var card = btn.closest('.ds-card');
      if (card) {
        var badge = card.querySelector('.ds-exp-badge');
        if (badge) { badge.textContent = 'Saved'; badge.className = 'ds-exp-badge ds-exp-saved'; }
      }
      btn.remove();
    } else {
      alert((res.d && res.d.message) || 'Could not save this image.');
      btn.disabled = false;
      btn.innerHTML = svg;
    }
  })
  .catch(function(){ btn.disabled = false; btn.innerHTML = svg; });
};
	<?php
$submitted_aids = array();
$all_gens = get_user_meta($uid, 'ds_ai_generations', true) ?: array();
foreach ($all_gens as $gen) {
    $aid = (int)($gen['attachment_id'] ?? 0);
    if ($aid && get_post_meta($aid, 'ds_marketplace_submitted', true)) {
        $submitted_aids[] = $aid;
    }
}
?>
<?php
$submitted_aids = get_user_meta($uid, 'ds_submitted_aids', true) ?: array();
$submitted_aids = array_map('intval', $submitted_aids);
?>
G.submittedAids = <?php echo json_encode($submitted_aids); ?>;
var REST='<?php echo esc_js($rest);?>';
var SUBURL='<?php echo esc_js( rest_url("ds/v1/creator-from-image") ); ?>';
var SITE='<?php echo $site;?>';
	window.dsAI = window.dsAI || {};
window.dsAI.credits = <?php echo (int)ds_get_credits(get_current_user_id()); ?>;
window.dsAI.plan = '<?php echo esc_js($user_plan); ?>';
	var MODEL_SIZES={
  'cloudflare':['HD','4K'],'cloudflare-klein':['HD','4K'],'flux-schnell':['HD','FHD','4K'],
  'flux-dev':['HD','FHD','4K'],'flux-pro':['HD','FHD','4K'],
  'seedream':['HD','FHD','2K','3K','4K'],'nanobana':['HD','FHD','2K','4K'],
  'qwen-image':['HD','FHD','4K'],'hidream-fast':['HD','FHD','4K'],
  'qwen-edit':['HD','FHD','4K'],
};
function q(id){return document.getElementById(id);}
	function dsCostEach(m,size){
  var base=G.costs[m]||2;
  var szKey=String(size||'').toUpperCase();
  if(G.resOv&&G.resOv[m]&&G.resOv[m][szKey]!==undefined)return G.resOv[m][szKey];
  return base;
}
function dsCalc(){
  var m=q('dsModel').value;
  var c=dsCostEach(m,G.size)*G.cnt;
  var gbl = q('dsGBL');
if(gbl) gbl.textContent='Generate ('+c+' cr)';
  var fn=q('dsFreeDayNote');
  if(fn){var fm=['cloudflare'];fn.style.display=(fm.indexOf(m)!==-1&&G.freeTier)?'flex':'none';}
}

document.addEventListener('DOMContentLoaded',function(){
	setTimeout(function(){if(typeof dsSTab==='function')dsSTab('img');},100);
  q('dsModel').addEventListener('change',function(){dsCalc();dsUpdateSizeBtns(this.value);window.dsModelChanged();});
  q('dsRatio').addEventListener('change',function(){dsCalc();dsSizeInfo();});
  q('dsPmt').addEventListener('input',function(){var cc=q('dsCC');if(cc)cc.textContent=Math.min(this.value.length,500)+'/500';});
  dsUpdateSizeBtns(q('dsModel').value);
  dsSizeInfo();
  dsCalc();
  window.dsModelChanged();

  // Lock paid models for free users
  if (G.freeTier) {
    var sel = q('dsModel');
    Array.prototype.forEach.call(sel.options, function(opt) {
      if (opt.value !== 'cloudflare') {
        opt.disabled = true;
        opt.text = '🔒 ' + opt.text + ' — Upgrade';
      }
    });
    if (sel.value !== 'cloudflare') {
      sel.value = 'cloudflare';
      dsCalc();
      dsUpdateSizeBtns('cloudflare');
      window.dsModelChanged();
    }
  }  // ← this was missing in your version

  // Lock count to 1 for free users
  if (G.freeTier) {
    G.cnt = 1;
    var cn = q('dsCN'); if(cn) cn.textContent = '1';
    var cbPlus = document.querySelector('.sb-cb[onclick="dsChC(1)"]');
    var cbMin  = document.querySelector('.sb-cb[onclick="dsChC(-1)"]');
    if (cbPlus) { cbPlus.disabled = true; cbPlus.style.opacity = '0.3'; cbPlus.style.cursor = 'not-allowed'; cbPlus.title = 'Upgrade to generate multiple images'; }
    if (cbMin)  { cbMin.disabled  = true; cbMin.style.opacity  = '0.3'; cbMin.style.cursor  = 'not-allowed'; }
  }

  // Lock size to HD only for free users
  if (G.freeTier) {
    document.querySelectorAll('.ds-size-btn').forEach(function(btn){
      if (btn.dataset.size !== 'HD') {
        btn.disabled = true;
        btn.classList.add('disabled');
        btn.style.opacity = '0.3';
        btn.style.cursor = 'not-allowed';
        btn.title = 'Upgrade to unlock higher resolutions';
      }
    });
    G.size = 'HD';
    var hdBtn = document.querySelector('.ds-size-btn[data-size="HD"]');
    if (hdBtn) { hdBtn.classList.add('on'); }
  }

});

var sizeMap={'HD':1280,'FHD':1920,'2K':2048,'3K':3072,'4K':4096};
function dsSizeInfo(){
  var long=sizeMap[G.size]||1280;
  var r=q('dsRatio').value;
  var rs={'1:1':[1,1],'16:9':[16,9],'9:16':[9,16],'4:3':[4,3],'3:4':[3,4],'3:2':[3,2],'2:3':[2,3],'21:9':[21,9]};
  var rv=rs[r]||[1,1];
  var w,h;
  if(rv[0]>=rv[1]){w=long;h=Math.round(long*rv[1]/rv[0]);}
  else{h=long;w=Math.round(long*rv[0]/rv[1]);}
  w=Math.round(w/8)*8;h=Math.round(h/8)*8;
  var el=q('dsSizeInfo');if(el)el.textContent=w+'x'+h+'px';
}

function dsUpdateSizeBtns(model){
  var modSizes=MODEL_SIZES[model]||['HD'];
  document.querySelectorAll('.ds-size-btn').forEach(function(btn){
    var sz=btn.dataset.size;
    var planOk=G.allowedSizes.indexOf(sz)!==-1;
    var modelOk=modSizes.indexOf(sz)!==-1;
    btn.classList.remove('model-locked');
    if(!planOk){btn.classList.add('disabled');btn.classList.remove('model-locked');}
    else if(!modelOk){btn.classList.add('disabled','model-locked');}
    else{btn.classList.remove('disabled','model-locked');}
  });
  if(modSizes.indexOf(G.size)===-1){
    var best=modSizes.filter(function(s){return G.allowedSizes.indexOf(s)!==-1;});
    if(best.length){
      G.size=best[best.length-1];
      document.querySelectorAll('.ds-size-btn').forEach(function(b){b.classList.remove('on');});
      var t=document.querySelector('.ds-size-btn[data-size="'+G.size+'"]');
      if(t)t.classList.add('on');
      dsSizeInfo();
    }
  }
}

window.dsSetSize=function(btn){
  if(btn.classList.contains('model-locked')){
    var m=q('dsModel').value;
    alert((MODEL_SIZES[m]||['HD']).join(', ')+' supported by this model. Choose Seedream or NanoBanana for 3K.');
    return;
  }
  if(btn.classList.contains('disabled')){
    var sz=btn.dataset.size;
    var msg=(sz==='FHD'?'FHD requires Premium plan.':(sz==='2K'||sz==='3K'||sz==='4K'?sz+' requires Pro/Business plan.':'Upgrade required.'));
    if(confirm(msg+' Go to pricing?'))window.location.href='/pricing';
    return;
  }
  document.querySelectorAll('.ds-size-btn').forEach(function(b){b.classList.remove('on');});
  btn.classList.add('on');G.size=btn.dataset.size;dsSizeInfo();dsCalc();
};

window.dsSingleOnly=function(mv){return mv==='nanobana'||mv==='qwen-edit'||mv==='qwen-image';};
window.dsChC=function(d){var mv=q('dsModel').value;var max=dsSingleOnly(mv)?1:4;G.cnt=Math.max(1,Math.min(max,G.cnt+d));var cn=q('dsCN');if(cn)cn.textContent=G.cnt;dsCalc();};
window.dsModelChanged=function(){
    var mv=q('dsModel').value;
    var single=dsSingleOnly(mv);
    if(single&&G.cnt>1){G.cnt=1;var cnEl=q('dsCN');if(cnEl)cnEl.textContent='1';dsCalc();}
    if(G.plan!=='free'){   // free users are already locked at 1 further down — don't fight that
        var pB=document.querySelector('.sb-cb[onclick="dsChC(1)"]');
        var mB=document.querySelector('.sb-cb[onclick="dsChC(-1)"]');
        [pB,mB].forEach(function(b){
            if(!b)return;
            b.disabled     = single;
            b.style.opacity= single?'0.3':'';
            b.style.cursor = single?'not-allowed':'';
            b.title        = single?'This model generates one image per prompt':'';
        });
    }
    // Show/hide reference image section based on model support
    var refSec=q('dsRefSec');
    if(refSec){
        var supportsRef=(mv==='flux-dev'||mv==='seedream'||mv==='qwen-edit'||mv==='nanobana');
        refSec.style.display=supportsRef?'':'none';
        if(!supportsRef){dsRefClear();}
        var inp=q('dsRefInput');
        var lbl=q('dsRefLbl');
        if(mv==='qwen-edit'||mv==='qwen-image'){
          if(inp)inp.setAttribute('multiple','true');
          if(lbl)lbl.textContent='up to 3 images';
        } else {
          if(inp)inp.removeAttribute('multiple');
          if(lbl)lbl.textContent='(optional)';
        }
    }
};
document.addEventListener('change',function(e){if(e.target&&e.target.id==='dsModel')window.dsModelChanged();});

window.dsGen=function(){
  G.busyCount=G.busyCount||0;
  if(G.busyCount>=5){alert('You already have 5 generations in progress. Please wait for one to finish before starting another.');return;}
  var prompt=q('dsPmt').value.trim();
  var model=q('dsModel').value;
  var style=q('dsStyle').value;
  var ratio=q('dsRatio').value;
  if(!prompt){alert('Please enter a prompt first.');return;}
if(model==='qwen-edit'&&!G.refImage){alert('Qwen Image Edit requires a reference image. Please upload one first.');return;}
  var cost=dsCostEach(model,G.size)*G.cnt;
  console.log('DEBUG plan='+G.plan+' model='+model+' isFree='+((['cloudflare','Cloudflare FLUX.2 Klein 9B'].indexOf(model)!==-1))+' credits='+window.dsAI.credits);
  var freeModels=['cloudflare'];
var isFreeModel=freeModels.indexOf(model)!==-1;
var skipCreditCheck=(G.freeTier&&isFreeModel);
if(!skipCreditCheck){
  var currentCredits=(window.dsAI&&window.dsAI.credits!==undefined)?window.dsAI.credits:0;
  if(currentCredits<cost){alert('Not enough credits. Need '+cost+'.');return;}
}
  G.busyCount=(G.busyCount||0)+1;
  q('dsGB').disabled=(G.busyCount>=5);
  q('dsEmt').style.display='none';
  var grp=document.createElement('div');grp.className='ds-grp';
  grp.innerHTML='<div class="ds-gmeta"><span class="ds-gp">'+prompt.substring(0,120)+'</span><div class="ds-gtags"><span class="ds-tag">'+ratio+'</span><span class="ds-tag ds-gst">Queued</span></div></div>';
  var grid=document.createElement('div');grid.className='ds-grid';
  for(var i=0;i<4;i++){
    var qc=document.createElement('div');
    if(i<G.cnt){qc.className='ds-q';qc.innerHTML='<div class="ds-spin"></div><span style="color:#64748b;font-size:10px;">Queued</span>';}
    else{qc.className='ds-ghost';}
    grid.appendChild(qc);
  }
  grp.appendChild(grid);
  var live=q('dsLive');live.insertBefore(grp,live.firstChild);
  q('dsF').scrollTop=0;
  var body={model:model,prompt:prompt,style:style,ratio:ratio,count:(model==='qwen-edit'?1:G.cnt),image_size:G.size};
  if(G.refImage&&(model==='flux-dev'||model==='seedream'||model==='cloudflare-klein'||model==='nanobana')){
    body.reference_image=G.refImage;
    body.ref_strength=parseFloat((parseInt(q('dsRefStrength').value)/100).toFixed(2));
  }
  if(model==='qwen-edit'||model==='qwen-image'){
    if(G.refImages&&G.refImages.length>0){
      body.reference_image=G.refImages; // array of up to 3
    } else if(G.refImage){
      body.reference_image=[G.refImage];
    }
  }
  fetch(REST+'generate-image',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify(body)})
  .then(function(r){return r.json().then(function(j){if(!r.ok)throw new Error(j&&j.message?j.message:'HTTP '+r.status);return j;});})
  .then(function(d){
    if(d.error){
  var dmsg=(typeof d.message==='string')?d.message:(d.message?JSON.stringify(d.message):'');
  if(d.status===429){
    dsFreeHardLock();
  } else if(d.status===403){
    errGrid(grid,d.message||'Model locked.');
  } else {
    errGrid(grid,d.message||'Generation failed.');
  }
  resetBtn();return;
}
    updCr(d.credits_left);
    if(d.prediction_ids && Array.isArray(d.prediction_ids)){
      // Batch: poll each prediction in parallel, reveal images as they finish
      setSt(grp,'0/'+d.prediction_ids.length+' done');
      var batchPids=d.prediction_ids;
      var batchDone=0;
      batchPids.forEach(function(pid,idx){
        // NOTE: no dsSaveGen('pending') here on purpose — the server
        // (DS AI Image REST) already writes this exact pending placeholder
        // before returning prediction_ids, so re-saving it here from the
        // client only creates a duplicate/ghost entry with no batch_id
        // that the real resolve step can never match, and which can
        // shadow the real one when polling. See DS AI Image Persistence.
        (function pollOne(pid,slotIdx){
          var timer=setInterval(function(){
            fetch(REST+'poll-image',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
              body:JSON.stringify({prediction_id:pid,prompt:prompt,ratio:ratio,model:model,style:style,image_size:G.size})})
            .then(function(r){return r.json();})
            .then(function(d2){
              if(d2.status==='succeeded'&&d2.images&&d2.images.length){
                clearInterval(timer);
                batchDone++;
                setSt(grp,batchDone+'/'+batchPids.length+' done');
                // Replace the loading slot for this index with the real image
                var slot=grid.children[slotIdx];
                if(slot){
                  var imgData=d2.images[0];
                  var url=typeof imgData==='object'?(imgData.url||''):imgData;
                  if (url && url.indexOf('ds-hub/v1/file/') !== -1 && url.indexOf('_wpnonce') === -1) { url += (url.indexOf('?')===-1?'?':'&') + '_wpnonce=' + NONCE; }
                  var aid=typeof imgData==='object'?(imgData.attachment_id||0):0;
var fid=typeof imgData==='object'?(imgData.file_id||0):0;
                  var su=url.replace(/'/g,"\\'");
                  var sp=(prompt||'').replace(/'/g,"\\'");
                  var modelCosts={'cloudflare':2,'cloudflare-klein':3,'flux-schnell':4,'flux-dev':7,'seedream':8,'flux-pro':10,'nanobana':13,'qwen-image':8};
                  var cardCost=modelCosts[model]||2;
                  var cardPct=Math.round((1/cardCost)*100);
                  var card=document.createElement('div');card.className='ds-card';
                  card.onclick=(function(u,p,a,pct){return function(){dsLBO(u,p,a,pct);};})(url,prompt,aid,cardPct);
                  var actBtns='<div class="ds-card-actions">'
                    +'<button class="ds-cdl" title="Download" onclick="event.stopPropagation();dsDL(\''+su+'\')">'
                    +'<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>'
                    +'</button>'
                    +'<button class="ds-csh" title="Share" onclick="event.stopPropagation();dsShareOpen(\''+su+'\',\''+sp+'\')">'
                    +'<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2.2"/></svg>'
                    +'</button>';
                  if(fid){actBtns+='<button class="ds-csave" title="Save to My Creations" onclick="event.stopPropagation();dsHubSaveCard('+fid+',this)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/></svg></button>';}
if((aid||fid)&&G.submittedAids.indexOf(aid)===-1){
                    actBtns+='<button class="ds-cmk" title="Submit to Marketplace" onclick="event.stopPropagation();dsMktOpen('+aid+','+(fid||1)+','+cardPct+')">'
                      +'<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>'
                      +'</button>';
                  }
                  actBtns+='</div>';
                  card.innerHTML='<img src="'+url+'" loading="lazy" alt="'+sp+'"/>'
                    +'<span class="ds-ctag">'+model+'</span>'
                    +'<span class="ds-ratio-badge">'+ratio+'</span>'
                    +actBtns;
                  slot.parentNode.replaceChild(card,slot);
                }
                dsSaveGen({status:'succeeded',prediction_id:pid,images:d2.images,prompt:prompt,model:model,ratio:ratio,style:style,image_size:G.size});
                if(batchDone>=batchPids.length){setSt(grp,'Done');resetBtn();}
              } else if(d2.status==='failed'){
                clearInterval(timer);
                batchDone++;
                var slot2=grid.children[slotIdx];
                if(slot2){slot2.className='ds-ghost';}
                if(batchDone>=batchPids.length){setSt(grp,'Done');resetBtn();}
              } else {
                setSt(grp,batchDone+'/'+batchPids.length+' done');
              }
            }).catch(function(){});
          },5000);
        })(pid,idx);
      });
    }
    else if(d.prediction_id){
      setSt(grp,'Processing...');G.pid=d.prediction_id;
      // NOTE: no dsSaveGen('pending') here on purpose — see comment at the
      // batch-prediction_ids branch above; the server already persisted
      // this placeholder before responding.
      doPoll(grid,grp,prompt,ratio,model,style,d.prediction_id);
      
    }
    else{
      resolveGrid(grid,d.images,prompt,ratio,model);setSt(grp,'Done');resetBtn();
    }
  })
  .catch(function(e){
  if(e.message&&e.message.indexOf('429')!==-1){dsFreeHardLock();}
  else{errGrid(grid,e.message||'Network error.');}
  resetBtn();
});
};

function dsSaveGen(payload){
  fetch(REST+'save-generation',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify(payload)}).catch(function(){});
}

// On page load: resume any predictions that were still running when
// the user last left or refreshed — works for ALL async models.
(function resumePending(){
  fetch(REST+'pending-generations',{headers:{'X-WP-Nonce':NONCE}})
  .then(function(r){return r.json();})
  .then(function(d){
    if(!d.pending||!d.pending.length)return;
    // Group by prompt+date so a 4-image batch shows 4 loading slots not 4 cards
    var groups={};
    // Skip cloudflare and cloudflare-klein — they're synchronous models that
    // complete in seconds. A pending entry for them means the generation
    // actually failed, not that it's still running on Replicate.
    d.pending.forEach(function(gen){
      var key=(gen.prompt||'')+'|'+(gen.date||'').substring(0,16);
      if(!groups[key])groups[key]=[];
      groups[key].push(gen);
    });
    Object.keys(groups).forEach(function(key){
      var batch=groups[key];
      var first=batch[0];
      var prompt=first.prompt||'';
      var model=first.model||'unknown';
      var ratio=first.ratio||'16:9';
      var style=first.style||'';
      var grp=document.createElement('div');grp.className='ds-grp';
      grp.innerHTML='<div class="ds-gmeta"><span class="ds-gp">&#8635; '+prompt.substring(0,80)+'</span>'
        +'<div class="ds-gtags"><span class="ds-tag">'+ratio+'</span>'
        +'<span class="ds-tag ds-gst">Resuming...</span></div></div>';
      var grid=document.createElement('div');grid.className='ds-grid';
      for(var i=0;i<4;i++){
        var qc=document.createElement('div');
        if(i<batch.length){qc.className='ds-q';qc.innerHTML='<div class="ds-spin"></div><span style="color:#64748b;font-size:10px;">Resuming...</span>';}
        else{qc.className='ds-ghost';}
        grid.appendChild(qc);
      }
      grp.appendChild(grid);
      var live=q('dsLive');
      if(live)live.insertBefore(grp,live.firstChild);
      var batchDone=0;
      batch.forEach(function(gen,slotIdx){
        doPoll(grid,grp,prompt,ratio,model,style,gen.prediction_id);
      });
    });
  }).catch(function(){});
})();
				
function doPoll(grid,grp,prompt,ratio,model,style,pid){
  pid=pid||G.pid;
  var timer=setInterval(function(){
    var pollBody={prediction_id:pid,prompt:prompt,ratio:ratio,model:model,style:style,image_size:G.size};
    if(G.refImage&&(model==='qwen-edit'||model==='qwen-image')){pollBody.reference_image=G.refImage;}
    fetch(REST+'poll-image',{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
      body:JSON.stringify(pollBody)})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.status==='succeeded'){
        clearInterval(timer);resolveGrid(grid,d.images,prompt,ratio,model);setSt(grp,'Done');resetBtn();
        dsSaveGen({status:'succeeded',prediction_id:pid,images:d.images,prompt:prompt,model:model,ratio:ratio,style:style,image_size:G.size});
      }
      else if(d.status==='failed'){
        clearInterval(timer);errGrid(grid,d.error||'Failed.');resetBtn();
        dsSaveGen({status:'failed',prediction_id:pid,error:d.error||'Failed'});
      }
      else setSt(grp,d.status||'processing');
    }).catch(function(){});
  },5000);
}

function resolveGrid(grid,urls,prompt,ratio,model){
  grid.innerHTML='';grid.className='ds-grid';
  var maxSlots=Math.max(urls.length,G.cnt);if(maxSlots>4)maxSlots=4;
  for(var i=0;i<maxSlots;i++){
    if(i<urls.length){
      var imgData=urls[i];
      var url=typeof imgData==='object'?(imgData.url||''):imgData;
      if (url && url.indexOf('ds-hub/v1/file/') !== -1 && url.indexOf('_wpnonce') === -1) { url += (url.indexOf('?')===-1?'?':'&') + '_wpnonce=' + NONCE; }
      var aid=typeof imgData==='object'?(imgData.attachment_id||0):0;
var fid=typeof imgData==='object'?(imgData.file_id||0):0;
      var su=url.replace(/'/g,"\\'");
      var sp=(prompt||'').replace(/'/g,"\\'");
      var card=document.createElement('div');card.className='ds-card';
      var modelCosts={'cloudflare':2,'Cloudflare FLUX.2 Klein 9B':3,'flux-schnell':4,'flux-dev':7,'seedream':8,'flux-pro':10,'nanobana':13};
      var cardCost=modelCosts[model]||2;
      var cardPct=Math.round((1/cardCost)*100);
      card.onclick=(function(u,p,a,pct){return function(){dsLBO(u,p,a,pct);};})(url,prompt,aid,cardPct);
      var actBtns='<div class="ds-card-actions">'
        +'<button class="ds-cdl" title="Download" onclick="event.stopPropagation();dsDL(\''+su+'\')">'
        +'<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>'
        +'</button>'
        +'<button class="ds-csh" title="Share" onclick="event.stopPropagation();dsShareOpen(\''+su+'\',\''+sp+'\')">'
        +'<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="#fff" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="#fff" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="#fff" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="#fff" stroke-width="2.2"/></svg>'
        +'</button>';
      if(fid){actBtns+='<button class="ds-csave" title="Save to My Creations" onclick="event.stopPropagation();dsHubSaveCard('+fid+',this)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/></svg></button>';}
if((aid || fid) && G.submittedAids.indexOf(aid) === -1) {
        actBtns+='<button class="ds-cmk" title="Submit to Marketplace" onclick="event.stopPropagation();dsMktOpen('+aid+','+(fid||1)+','+cardPct+')">'
          +'<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>'
          +'</button>';
      } else if(aid) {
        actBtns+='<span style="position:absolute;top:5px;right:5px;background:#2563eb;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:2;">Submitted</span>';
      }
      actBtns+='</div>';
      card.innerHTML='<img src="'+url+'" loading="lazy" alt="'+sp+'"/>'
        +'<span class="ds-ctag">'+model+'</span>'
        +'<span class="ds-ratio-badge">'+ratio+'</span>'
        +actBtns;
      grid.appendChild(card);
    } else {
      var ghost=document.createElement('div');ghost.className='ds-ghost';grid.appendChild(ghost);
    }
  }
}

function errGrid(g,m){g.innerHTML='<div class="ds-err">'+m+'</div>';}
function setSt(grp,s){var el=grp.querySelector('.ds-gst');if(el)el.textContent=s;}
function updCr(n){if(n!==undefined){window.dsAI.credits=n;var el=q('dsCrEl');if(el)el.innerHTML='&#9733; '+n+' cr';}}
function resetBtn(){G.busyCount=Math.max(0,(G.busyCount||0)-1);q('dsGB').disabled=(G.busyCount>=5);dsCalc();}
// Build flat image list for prev/next navigation
function dsLBBuildList(){
  G.lbList=[];
  document.querySelectorAll('.ds-card img').forEach(function(img){
    var card=img.parentElement;
    var onclick=card.getAttribute('onclick')||'';
    var m=onclick.match(/dsLBO\('([^']+)','([^']*)',(\d+)\)/);
    if(m)G.lbList.push({url:m[1],prompt:m[2],aid:parseInt(m[3])||0});
    // also handle dynamically generated cards (closure-based onclick)
    else if(img.src){
      // fallback: just store url
      G.lbList.push({url:img.src,prompt:'',aid:0});
    }
  });
}
window.dsLBO=function(url,p,aid,pct){
  dsLBBuildList();
  G.lbUrl=url;G.lbPrompt=p||'';G.lbAid=aid||0;G.lbPct=pct||50;
  G.lbIdx=0;
  for(var i=0;i<G.lbList.length;i++){if(G.lbList[i].url===url){G.lbIdx=i;break;}}
  dsLBRender();
  q('dsLB').classList.add('open');
  document.body.style.overflow='hidden';
};
function dsLBRender(){
  var item=G.lbList[G.lbIdx]||{url:G.lbUrl,prompt:G.lbPrompt,aid:G.lbAid};
  G.lbUrl=item.url;G.lbPrompt=item.prompt;G.lbAid=item.aid;
  q('dsLBImg').src=item.url;
  q('dsLBCap').textContent=item.prompt?'"'+item.prompt.slice(0,100)+'"':'';
  var mktBtn=q('dsLBMktBtn');if(mktBtn)mktBtn.style.display=item.aid?'flex':'none';
  var prev=q('dsLBPrev');var next=q('dsLBNext');
  if(prev)prev.style.opacity=G.lbIdx>0?'1':'.2';
  if(next)next.style.opacity=G.lbIdx<G.lbList.length-1?'1':'.2';
}
window.dsLBNav=function(dir){
  var newIdx=G.lbIdx+dir;
  if(newIdx<0||newIdx>=G.lbList.length)return;
  G.lbIdx=newIdx;
  dsLBRender();
};
window.dsLBClose=function(){q('dsLB').classList.remove('open');q('dsLBImg').src='';document.body.style.overflow='';};
window.dsLBDL=function(){if(G.lbUrl)dsDL(G.lbUrl);};
window.dsLBShare=function(){dsLBClose();dsShareOpen(G.lbUrl,G.lbPrompt);};
window.dsLBMarket=function(){if(G.lbAid){dsLBClose();dsMktOpen(G.lbAid,1,G.lbPct||50);}};
document.addEventListener('keydown',function(e){
  if(e.key==='Escape'){dsLBClose();dsShareClose();dsMktClose();}
  if(e.key==='ArrowLeft'&&q('dsLB').classList.contains('open'))dsLBNav(-1);
  if(e.key==='ArrowRight'&&q('dsLB').classList.contains('open'))dsLBNav(1);
});
window.dsDL=function(url){
  if(!url)return;
  var fn='designstager-ai-'+Date.now()+'.png';
  if(url.startsWith('data:')){var a=document.createElement('a');a.href=url;a.download=fn;document.body.appendChild(a);a.click();setTimeout(function(){document.body.removeChild(a);},200);return;}
  var proxyUrl=REST+'download?url='+encodeURIComponent(url)+'&_wpnonce='+NONCE;
  var a=document.createElement('a');a.href=proxyUrl;a.download=fn;a.target='_self';
  document.body.appendChild(a);a.click();setTimeout(function(){document.body.removeChild(a);},500);
};
window.dsShareOpen=function(url,prompt){
  G.shareUrl=url;G.sharePrompt=prompt||'';
  q('dsSharePreview').src=url;
  q('dsShareModal').classList.add('open');
};
window.dsShareClose=function(){q('dsShareModal').classList.remove('open');};
window.dsShareVia=function(ch){
  var url=G.shareUrl;
  var tagline='I created this with Designstager AI Studio! Design smarter, faster, for free. Try it at '+SITE;
  var encoded=encodeURIComponent(tagline+' '+url);
  if(ch==='whatsapp')window.open('https://wa.me/?text='+encoded,'_blank');
  else if(ch==='twitter')window.open('https://twitter.com/intent/tweet?text='+encodeURIComponent(tagline)+'&url='+encodeURIComponent(url),'_blank');
  else if(ch==='facebook')window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(url)+'&quote='+encodeURIComponent(tagline),'_blank');
  else if(ch==='copy'){
    navigator.clipboard.writeText(tagline+'\n'+url).then(function(){
      var btn=q('dsShareCopyBtn');if(btn){btn.textContent='Copied!';setTimeout(function(){btn.textContent='Copy Link';},2000);}
    }).catch(function(){});
  }
};
window.dsMktOpen=function(aid,fid,cb,pct){
  if(!aid&&!fid)return;
  G.mktAid=aid;G.mktFid=fid||0;G.mktCb=cb||1;
  var cbEl=q('dsMktCbAmt');
  if(cbEl)cbEl.textContent=(pct||50)+'% (1 credit)';
  var consent=q('dsMktConsent');if(consent)consent.checked=false;
  var sb=q('dsMktSubmitBtn');if(sb){sb.style.opacity='.5';sb.style.pointerEvents='none';}
  q('dsMktModal').classList.add('open');
};
window.dsMktClose=function(){q('dsMktModal').classList.remove('open');};
document.addEventListener('change',function(e){
  if(e.target&&e.target.id==='dsMktConsent'){
    var sb=q('dsMktSubmitBtn');
    if(sb){if(e.target.checked){sb.style.opacity='1';sb.style.pointerEvents='auto';}
    else{sb.style.opacity='.5';sb.style.pointerEvents='none';}}
  }
});
window.dsMktSubmit=function(){
  var consent=q('dsMktConsent');
  if(!consent||!consent.checked){alert('Please tick the consent checkbox to continue.');return;}
  var sb=q('dsMktSubmitBtn');
  if(sb){sb.textContent='Submitting...';sb.disabled=true;}
  fetch(SUBURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify(G.mktAid?{attachment_id:G.mktAid,title:'auto',consent:true}:{file_id:G.mktFid,title:'auto',consent:true})})
  .then(function(r){return r.json();})
  .then(function(d){
    dsMktClose();
    if(sb){sb.textContent='Submit to Marketplace';sb.disabled=false;sb.style.opacity='.5';sb.style.pointerEvents='none';}
    if(d.success){
  var mktKey = G.mktAid ? String(G.mktAid) : ('f'+G.mktFid);
  if((G.mktAid || G.mktFid) && G.submittedAids.indexOf(mktKey) === -1) {
    G.submittedAids.push(mktKey);
    // Save to user meta so it persists after refresh. R2 images never
    // get a local attachment_id, so also send file_id as a fallback key.
    fetch(REST+'mark-submitted',{method:'POST',
      headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
      body:JSON.stringify({attachment_id:G.mktAid,file_id:G.mktFid})});
  }
  dsShowSuccessModal(d.pending_cashback||0);
}
    else{
    if(d.message && d.message.indexOf('already')!==-1){
        alert('This image has already been submitted for review.');
        // Hide the button since it was already submitted
        document.querySelectorAll('.ds-cmk').forEach(function(btn){
            if((btn.getAttribute('onclick')||'').indexOf('dsMktOpen('+G.mktAid+')')!==-1){
                btn.style.display='none';
            }
        });
        var lbBtn=q('dsLBMktBtn');if(lbBtn)lbBtn.style.display='none';
    } else {
    if(d.message && d.message.indexOf('already')!==-1){
        alert('This image has already been submitted for review.');
        document.querySelectorAll('.dsmy-card').forEach(function(card){
            var oc=card.getAttribute('onclick')||'';
            if(oc.indexOf(G.mktAid)!==-1){
                var mkBtn=card.querySelector('.mk');
                if(mkBtn) mkBtn.style.display='none';
            }
        });
        var lbMktBtn=q('dsmy-lb-mkt-btn');
        if(lbMktBtn) lbMktBtn.style.display='none';
    } else {
        alert('Failed: '+(d.error||'Please try again.'));
    }
}
}
  }).catch(function(){
    dsMktClose();
    if(sb){sb.textContent='Submit to Marketplace';sb.disabled=false;}
  });
};
window.dsShowSuccessModal=function(cb){
  q('dsMktSuccess').classList.add('open');
  var m=q('dsMktSuccessMsg');if(m)m.textContent='Your design is submitted for review!';
  dsRunConfetti();
  // Hide marketplace button on all matching cards
  document.querySelectorAll('.ds-cmk').forEach(function(btn){
    var oc=btn.getAttribute('onclick')||btn.onclick||'';
    if((oc+'').indexOf('dsMktOpen('+G.mktAid+')')!==-1){
      var card=btn.closest('.ds-card');
      btn.style.display='none';
      if(card){
        var badge=document.createElement('span');
        badge.style.cssText='position:absolute;top:5px;right:5px;background:#2563eb;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;z-index:2;';
        badge.textContent='Submitted';
        card.appendChild(badge);
      }
    }
  });
  // Hide in lightbox too
  var lbBtn=q('dsLBMktBtn');
  if(lbBtn) lbBtn.style.display='none';
};
window.dsHideSuccessModal=function(){q('dsMktSuccess').classList.remove('open');};
window.dsRunConfetti=function(){
  var c=q('dsMktConfetti');if(!c)return;c.innerHTML='';
  var colors=['#d97706','#f59e0b','#10b981','#3b82f6','#ec4899','#8b5cf6','#ef4444','#facc15'];
  for(var i=0;i<40;i++){
    var s=document.createElement('span');
    s.style.cssText='position:absolute;width:8px;height:14px;top:-20px;animation:dsMktFall linear forwards;';
    s.style.left=Math.random()*100+'%';
    s.style.background=colors[Math.floor(Math.random()*colors.length)];
    s.style.animationDuration=(1.5+Math.random()*1.5)+'s';
    s.style.animationDelay=Math.random()*0.4+'s';
    s.style.transform='rotate('+Math.random()*360+'deg)';
    c.appendChild(s);
  }
  setTimeout(function(){c.innerHTML='';},3500);
};
q('dsShareModal').addEventListener('click',function(e){if(e.target===this)dsShareClose();});
q('dsMktModal').addEventListener('click',function(e){if(e.target===this)dsMktClose();});
document.getElementById('dsMktSuccess').addEventListener('click',function(e){if(e.target===this)dsHideSuccessModal();});
window.dsRefPicked=function(input){
  var files=Array.from(input.files||[]).slice(0,3);
  if(!files.length)return;
  for(var fi=0;fi<files.length;fi++){
    if(!files[fi].type.match(/^image\/(jpeg|png|webp|gif)$/)){q('dsRefErr').textContent='Only JPG, PNG, WEBP or GIF allowed.';q('dsRefErr').style.display='block';input.value='';return;}
    if(files[fi].size>10*1024*1024){q('dsRefErr').textContent='Each image must be under 10MB.';q('dsRefErr').style.display='block';input.value='';return;}
  }
  G.refImages=[];G.refImage=null;
  var preview=q('dsRefPreview');var drop=q('dsRefDrop');
  var imgEl=q('dsRefImg');
  // Show first image in existing preview slot
  var loaded=0;
  files.forEach(function(file,i){
    var reader=new FileReader();
    reader.onload=function(e){
      G.refImages[i]=e.target.result;
      if(i===0){G.refImage=e.target.result;imgEl.src=e.target.result;}
      loaded++;
      if(loaded===files.length){
        // Update preview label to show count
        if(files.length>1){imgEl.title=files.length+' images selected';}
        if(preview)preview.style.display='block';
        if(drop)drop.style.display='none';
        q('dsRefErr').style.display='none';
        // Show count badge
        var badge=q('dsRefCountBadge');
        if(!badge){badge=document.createElement('div');badge.id='dsRefCountBadge';badge.style.cssText='margin-top:4px;font-size:10px;color:#60a5fa;font-weight:700;text-align:center;';if(preview)preview.appendChild(badge);}
        badge.textContent=files.length>1?files.length+' images selected':'';
      }
    };
    reader.onerror=function(){q('dsRefErr').textContent='Failed to read file.';q('dsRefErr').style.display='block';};
    reader.readAsDataURL(file);
  });
};
											 
function dsFreeHardLock() {
  var btn = q('dsGB');
  if (btn) {
    btn.disabled = true;
    btn.style.background = '#111';
    btn.style.boxShadow = 'none';
    btn.style.cursor = 'not-allowed';
    btn.style.color = '#4b5563';
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Daily limit reached (2/2)';
  }
  if (document.getElementById('ds-free-lock')) return;
  var banner = document.createElement('div');
  banner.id = 'ds-free-lock';
  banner.style.cssText = 'padding:16px 14px;background:#0c0800;border-top:2px solid #f59e0b;text-align:center;flex-shrink:0;';
  banner.innerHTML = '<p style="margin:0 0 5px;font-size:13px;font-weight:800;color:#f59e0b;">🔒 2/2 free images used today</p>'
    + '<p style="margin:0 0 12px;font-size:11px;color:#78716c;line-height:1.5;">Resets at midnight &bull; upgrade to generate with all models</p>'
    + '<a href="/pricing" style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#000;padding:9px 24px;border-radius:100px;font-size:12px;font-weight:800;text-decoration:none;">⭐ Upgrade Now</a>'
    + '<p style="margin:8px 0 0;font-size:10px;color:#44403c;">Resets in <span id="ds-free-timer">--</span></p>';
  var sb = document.querySelector('.dsAI-sb');
  if (sb) sb.appendChild(banner);
  (function tick() {
    var now = new Date(), midnight = new Date(now);
    midnight.setHours(24,0,0,0);
    var s = Math.floor((midnight - now) / 1000);
    var el = document.getElementById('ds-free-timer');
    if (el) el.textContent = Math.floor(s/3600)+'h '+Math.floor((s%3600)/60)+'m '+(s%60)+'s';
    if (s > 0) setTimeout(tick, 1000);
  })();
}
											 
window.dsRefClear=function(){G.refImage=null;G.refImages=[];q('dsRefImg').src='';q('dsRefPreview').style.display='none';q('dsRefDrop').style.display='block';q('dsRefInput').value='';q('dsRefErr').style.display='none';var b=q('dsRefCountBadge');if(b)b.textContent='';};
(function(){
  var drop=q('dsRefDrop');if(!drop)return;
  drop.addEventListener('dragover',function(e){e.preventDefault();drop.classList.add('drag-over');});
  drop.addEventListener('dragleave',function(){drop.classList.remove('drag-over');});
  drop.addEventListener('drop',function(e){
    e.preventDefault();drop.classList.remove('drag-over');
    var files=e.dataTransfer.files;if(files&&files[0]){var inp=q('dsRefInput');var dt=new DataTransfer();dt.items.add(files[0]);inp.files=dt.files;dsRefPicked(inp);}
  });
})();

function dsSetImgSrc(img, url){
  img.onload = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
  img.onerror = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
  img.src = url;
}
(function(){
  var els = Array.prototype.slice.call(document.querySelectorAll('img[data-fid]'));
  var byFid = {}; var ids = [];
  els.forEach(function(img){
    var fid = img.getAttribute('data-fid');
    if (fid && fid !== '0') {
      if (!byFid[fid]) { byFid[fid] = []; ids.push(fid); }
      byFid[fid].push(img);
    } else {
      var fb0 = img.getAttribute('data-fallback');
      if (fb0) dsSetImgSrc(img, fb0);
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
            if (url) { dsSetImgSrc(img, url); } else { var fb = img.getAttribute('data-fallback'); if (fb) dsSetImgSrc(img, fb); }
          });
        });
      })
      .catch(function(){
        group.forEach(function(fid){
          (byFid[fid]||[]).forEach(function(img){ var fb = img.getAttribute('data-fallback'); if (fb) dsSetImgSrc(img, fb); });
        });
      });
  });
})();

})();
</script>
<?php
    return ob_get_clean();
}

add_action('rest_api_init', function () {
	register_rest_route('ds/v1', '/dbg-force-active', array(
		'methods' => 'GET',
		'callback' => function() {
			global $wpdb;
			$table = $wpdb->prefix . 'snippets';
			$before = $wpdb->get_row("SELECT id, active FROM {$table} WHERE id = 578", ARRAY_A);
			$upd = $wpdb->update($table, array('active' => 1), array('id' => 578));
			$after = $wpdb->get_row("SELECT id, active FROM {$table} WHERE id = 578", ARRAY_A);
			return rest_ensure_response(array('before' => $before, 'update_result' => $upd, 'after' => $after, 'db_error' => $wpdb->last_error));
		},
		// Debug tools: they change / read the database, so site admins only.
		'permission_callback' => function () { return current_user_can('manage_options'); },
	));
	register_rest_route('ds/v1', '/dbg-check-active', array(
		'methods' => 'GET',
		'callback' => function() {
			global $wpdb;
			$table = $wpdb->prefix . 'snippets';
			$rows = $wpdb->get_results("SELECT id, active FROM {$table} WHERE id IN (572,576,578,579,580)", ARRAY_A);
			return rest_ensure_response(array('rows' => $rows));
		},
		'permission_callback' => function () { return current_user_can('manage_options'); },
	));
});
