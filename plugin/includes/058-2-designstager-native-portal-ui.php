<?php
/**
 * Code Snippets export — 2 Designstager — Native Portal UI
 *
 * Snippet ID : 744
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-058
 * Modified   : 2026-09-09 02:43:56
 * Load order : 058 of 144
 */
/**
 * Designstager — Native Portal UI (single WordPress page) — snippet.
 *
 * This is now the MAIN Creator Page UI (replaces [ds_creator_portal] /
 * Snippet 2 as the front-end). It is wired to the real backend from
 * "Designstager Plugin V6": Snippet 1 (REST API + custom post type),
 * Snippet 3 (admin approval), Snippet 4-PATCHED (DLM download tracking +
 * ₹2/download earning), Snippet 5 (earnings dashboard). Keep those 4
 * snippets active for this page to work.
 *
 * REAL (backed by the ds/v1 REST API):
 *   - Login gate (must be a logged-in WP user — standard WP registration
 *     is the "creator signup")
 *   - Upload Product wizard's final submit -> POST /ds/v1/creator/upload
 *   - My Products list, Edit, Delete -> GET/POST /ds/v1/creator/upload...
 *   - Earnings tab + Dashboard stats -> GET /ds/v1/creator/earnings
 *   - Category dropdown -> GET /ds/v1/creator/categories
 *
 * STILL COSMETIC (no endpoint exists yet in Snippets 1/3/4/5 for these —
 * would need a separate small backend addition if you want them real):
 *   Withdrawals, Creator Profile edit-save, Payment Details save,
 *   Notifications, onboarding "Personal/Creator Profile/Payment" steps.
 *   The Creator Agreement view is informational-only, no save needed.
 *
 * INSTALL: paste into Code Snippets -> Add New -> Run everywhere, activate.
 * Also keep Snippet 1 (backend), Snippet 3 (admin), Snippet 4-PATCHED
 * (DLM), Snippet 5 (earnings) active, and the Download Monitor plugin
 * installed. Put shortcode  [designstager_portal_ui]  on a full-width page
 * — that page becomes the Creator Portal / Creator Page.
 */

if (!defined('ABSPATH')) { exit; }

define('DS_PORTAL_UI_VER', '2.1.0');

// ─────────────────────────────────────────────
// CREATOR ROLE — a separate login from normal WP users.
// Anyone can still register a WP account (Settings -> General -> "Anyone
// can register"), but only accounts with the "Designstager Creator" role
// (or site admins) can open the Creator Page below. New self-registrations
// on this site are automatically made Creators, since that's this site's
// whole purpose — if you also have non-creator WP users (e.g. staff
// accounts), just don't give them this role.
// ─────────────────────────────────────────────
add_action('init', 'ds_portal_ui_register_role');
function ds_portal_ui_register_role() {
    if (!get_role('ds_creator')) {
        add_role('ds_creator', 'Designstager Creator', array('read' => true, 'upload_files' => true));
    }
}

add_action('user_register', 'ds_portal_ui_set_creator_role');
function ds_portal_ui_set_creator_role($user_id) {
    $user = new WP_User($user_id);
    $user->set_role('ds_creator');
}

function ds_portal_ui_is_creator() {
    return is_user_logged_in() && (current_user_can('ds_creator') || current_user_can('manage_options'));
}

function ds_portal_ui_assets() {
    wp_register_style(
        'ds-portal-ui-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'ds_portal_ui_assets');

// Simple initials helper for the sidebar avatar, e.g. "Suj Bhat" -> "SB".
function ds_portal_ui_initials($name) {
    $name  = trim((string) $name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name);
    $ini   = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $ini ?: '?';
}

// Shown instead of the portal when the visitor is not logged in. Creator
// "signup" is just standard WordPress user registration — make sure
// Settings -> General -> "Anyone can register" is checked, or invite
// creators as Subscribers from wp-admin.
function ds_portal_ui_login_gate() {
    wp_enqueue_style('ds-portal-ui-fonts');
    $current_url = esc_url( home_url( add_query_arg( null, null ) ) );
    $login_url   = esc_url( wp_login_url( $current_url ) );
    $reg_url     = get_option( 'users_can_register' ) ? esc_url( wp_registration_url() ) : '';
    ob_start();
    ?>
<div class="dsx" style="min-height:60vh;">
<style>
.dsx{--bg:#0b1120;--bg2:#0a0e1a;--surface:#111827;--surface2:#1e293b;--card:#151d30;--border:rgba(255,255,255,.08);--text:#f3f5f9;--muted:#9ba6bc;--blue:#3b82f6;--blue2:#2563eb;--pink:#ec4899;--green:#22c55e;--amber:#f59e0b;--red:#ef4444;--teal:#14b8a6;
  position:relative;min-height:60vh;background:radial-gradient(circle at 15% 10%,#16213e 0%,var(--bg) 45%,var(--bg2) 100%);color:var(--text);font-family:'Poppins',sans-serif;border-radius:1rem;overflow:hidden;isolation:isolate;display:flex;align-items:center;justify-content:center;padding:2rem;}
.dsx *{box-sizing:border-box;}
.dsx h1,.dsx h2,.dsx h3{font-family:'Montserrat',sans-serif;font-weight:800;color:var(--text) !important;margin:0;}
.dsx a{color:inherit !important;}
.dsx-btn{display:inline-flex;align-items:center;gap:.4rem;background:#2563eb;color:#fff;border:0;border-radius:10px;padding:.7rem 1.2rem;font-weight:600;font-size:.9rem;text-decoration:none;}
.dsx-btn.outline{background:transparent;border:1px solid var(--border);color:var(--text);}
</style>
<div style="width:420px;max-width:90vw;background:var(--card);border:1px solid var(--border);border-radius:18px;padding:2rem;text-align:center;">
  <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#2563eb,#ec4899);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;">🔒</div>
  <h2 style="font-size:1.3rem;margin-bottom:.6rem;">Sign in to your Creator account</h2>
  <p style="color:var(--muted);font-size:.88rem;margin-bottom:1.4rem;">Log in to upload products, track downloads and see your earnings.</p>
  <a href="<?php echo $login_url; ?>" class="dsx-btn" style="width:100%;justify-content:center;margin-bottom:.7rem;">Log In</a>
  <?php if ($reg_url): ?>
  <a href="<?php echo $reg_url; ?>" class="dsx-btn outline" style="width:100%;justify-content:center;">Create a Creator Account</a>
  <?php endif; ?>
</div>
</div>
    <?php
    return ob_get_clean();
}

// Shown when the visitor IS logged in, but their account isn't a Creator.
function ds_portal_ui_not_creator_notice() {
    wp_enqueue_style('ds-portal-ui-fonts');
    $logout_url = esc_url( wp_logout_url( home_url('/') ) );
    ob_start();
    ?>
<div class="dsx" style="min-height:60vh;">
<style>
.dsx{--bg:#0b1120;--bg2:#0a0e1a;--card:#151d30;--border:rgba(255,255,255,.08);--text:#f3f5f9;--muted:#9ba6bc;
  position:relative;min-height:60vh;background:radial-gradient(circle at 15% 10%,#16213e 0%,var(--bg) 45%,var(--bg2) 100%);color:var(--text);font-family:'Poppins',sans-serif;border-radius:1rem;overflow:hidden;isolation:isolate;display:flex;align-items:center;justify-content:center;padding:2rem;}
.dsx *{box-sizing:border-box;}
.dsx h2{font-family:'Montserrat',sans-serif;font-weight:800;color:var(--text) !important;margin:0;}
.dsx a{color:inherit !important;}
.dsx-btn{display:inline-flex;align-items:center;gap:.4rem;background:#2563eb;color:#fff;border:0;border-radius:10px;padding:.7rem 1.2rem;font-weight:600;font-size:.9rem;text-decoration:none;}
</style>
<div style="width:420px;max-width:90vw;background:var(--card);border:1px solid var(--border);border-radius:18px;padding:2rem;text-align:center;">
  <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#ef4444);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;">⚠️</div>
  <h2 style="font-size:1.3rem;margin-bottom:.6rem;">This account isn't a Creator account</h2>
  <p style="color:var(--muted);font-size:.88rem;margin-bottom:1.4rem;">You're logged in, but this WordPress account doesn't have the Creator role. Log out and register a new account, or ask the site admin to switch your role to "Designstager Creator" in wp-admin -> Users.</p>
  <a href="<?php echo $logout_url; ?>" class="dsx-btn" style="width:100%;justify-content:center;">Log Out</a>
</div>
</div>
    <?php
    return ob_get_clean();
}

function ds_portal_ui_shortcode($atts) {
    wp_enqueue_style('ds-portal-ui-fonts');

    if ( ! is_user_logged_in() ) {
        return ds_portal_ui_login_gate();
    }
    if ( ! ds_portal_ui_is_creator() ) {
        return ds_portal_ui_not_creator_notice();
    }

    $ds_user         = wp_get_current_user();
    $ds_display_name = $ds_user->display_name ? $ds_user->display_name : $ds_user->user_login;
    $ds_initials     = ds_portal_ui_initials($ds_display_name);
    $ds_rest_base    = esc_url_raw( rest_url('ds/v1/creator') );
    $ds_nonce        = wp_create_nonce('wp_rest');
    $ds_logout_url   = esc_url( wp_logout_url( home_url('/') ) );

    ob_start();
    ?>
<div class="dsx" data-api="<?php echo esc_attr($ds_rest_base); ?>" data-nonce="<?php echo esc_attr($ds_nonce); ?>" data-logout="<?php echo esc_attr($ds_logout_url); ?>">
<style>
.dsx{--bg:#0b1120;--bg2:#0a0e1a;--surface:#111827;--surface2:#1e293b;--card:#151d30;--border:rgba(255,255,255,.08);--text:#f3f5f9;--muted:#9ba6bc;--blue:#3b82f6;--blue2:#2563eb;--pink:#ec4899;--green:#22c55e;--amber:#f59e0b;--red:#ef4444;--teal:#14b8a6;
  position:relative;min-height:100vh;background:radial-gradient(circle at 15% 10%,#16213e 0%,var(--bg) 45%,var(--bg2) 100%);color:var(--text);font-family:'Poppins',sans-serif;border-radius:1rem;overflow:hidden;isolation:isolate;}
.dsx *{box-sizing:border-box;}
/* Force our own colors everywhere — WordPress themes often set global
   h1/h2/h3/p/a colors meant for a light page background, which otherwise
   silently override these and turn text near-invisible on our dark panel. */
.dsx h1,.dsx h2,.dsx h3,.dsx h4,.dsx h5,.dsx h6{font-family:'Montserrat',sans-serif;font-weight:800;letter-spacing:-.02em;margin:0;color:var(--text) !important;background:none !important;text-shadow:none !important;}
.dsx p,.dsx span,.dsx label,.dsx div,.dsx li,.dsx td,.dsx th,.dsx strong,.dsx b{color:inherit;}
.dsx a{color:inherit !important;text-decoration:none;}
.dsx button{font-family:inherit;cursor:pointer;color:inherit;}
.dsx input,.dsx textarea,.dsx select{color:var(--text) !important;}
.dsx .hide{display:none !important;}

/* ---- shell ---- */
.dsx-shell{display:flex;min-height:100vh;}
.dsx-side{width:260px;flex-shrink:0;background:rgba(10,14,26,.6);border-right:1px solid var(--border);padding:1.5rem 1rem;display:flex;flex-direction:column;gap:1.5rem;}
.dsx-brand{display:flex;align-items:center;gap:.6rem;padding:0 .25rem;}
.dsx-brand .mark{width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--blue2),var(--pink));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;}
.dsx-brand .word{font-family:'Montserrat',sans-serif;font-weight:800;font-size:1.15rem;}
.dsx-brand .word b{background:linear-gradient(90deg,var(--blue),var(--pink));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.dsx-user{display:flex;align-items:center;gap:.75rem;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.75rem;}
.dsx-user .av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--blue2),var(--pink));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;}
.dsx-user .nm{font-weight:600;font-size:.9rem;}
.dsx-user .vf{color:var(--green);font-size:.75rem;display:flex;align-items:center;gap:.25rem;}
.dsx-nav{display:flex;flex-direction:column;gap:.15rem;flex:1;overflow-y:auto;}
.dsx-nav a{display:flex;align-items:center;justify-content:space-between;gap:.6rem;padding:.6rem .75rem;border-radius:10px;color:var(--muted);font-size:.9rem;font-weight:500;transition:.15s;}
.dsx-nav a:hover{color:var(--text);background:rgba(255,255,255,.04);}
.dsx-nav a.active{color:#fff;background:linear-gradient(90deg,rgba(59,130,246,.25),rgba(59,130,246,.05));}
.dsx-nav a .ic{width:18px;text-align:center;opacity:.9;}
.dsx-nav a .lb{flex:1;}
.dsx-badge{background:var(--red);color:#fff;font-size:.65rem;font-weight:700;border-radius:99px;padding:.1rem .4rem;}
.dsx-main{flex:1;min-width:0;display:flex;flex-direction:column;}
.dsx-topbar{display:flex;align-items:center;justify-content:space-between;padding:1.1rem 2rem;border-bottom:1px solid var(--border);background:rgba(10,14,26,.4);}
.dsx-crumb{color:var(--muted);font-size:.9rem;}
.dsx-crumb b{color:var(--text);}
.dsx-top-actions{display:flex;align-items:center;gap:1rem;}
.dsx-content{padding:2rem;flex:1;}

/* ---- generic ---- */
.dsx-h1{font-size:1.9rem;margin-bottom:.35rem;}
.dsx-sub{color:var(--muted);font-size:.92rem;margin-bottom:1.5rem;}
.dsx-row{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.dsx-grid{display:grid;gap:1rem;}
.g2{grid-template-columns:repeat(2,1fr);}
.g3{grid-template-columns:repeat(3,1fr);}
.g6{grid-template-columns:repeat(6,1fr);}
@media(max-width:900px){.g2,.g3,.g6{grid-template-columns:1fr;}}
.dsx-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.4rem;}
.dsx-stat{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.1rem 1.3rem;position:relative;}
.dsx-stat .lbl{font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:.6rem;}
.dsx-stat .val{font-family:'Montserrat',sans-serif;font-weight:800;font-size:1.5rem;}
.dsx-stat .sub{color:var(--muted);font-size:.78rem;margin-top:.25rem;}
.dsx-btn{display:inline-flex;align-items:center;gap:.4rem;background:var(--blue2);color:#fff;border:0;border-radius:10px;padding:.6rem 1.1rem;font-weight:600;font-size:.88rem;}
.dsx-btn.outline{background:transparent;border:1px solid var(--border);color:var(--text);}
.dsx-btn.accent{background:var(--pink);}
.dsx-btn.sm{padding:.4rem .8rem;font-size:.8rem;}
.dsx-btn:disabled{opacity:.5;cursor:not-allowed;}
.dsx-badge2{display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;padding:.2rem .6rem;border-radius:99px;}
.b-green{background:rgba(34,197,94,.15);color:#4ade80;}
.b-amber{background:rgba(245,158,11,.15);color:#fbbf24;}
.b-red{background:rgba(239,68,68,.15);color:#f87171;}
.b-gray{background:rgba(148,163,184,.15);color:#94a3b8;}
.dsx-chip{display:inline-flex;align-items:center;padding:.5rem 1rem;border-radius:99px;border:1px solid var(--border);background:var(--surface);color:var(--muted);font-size:.85rem;cursor:pointer;transition:.15s;}
.dsx-chip.on{border-color:var(--blue);color:var(--blue);background:rgba(59,130,246,.1);}
.dsx-field{margin-bottom:1rem;}
.dsx-field label{display:block;font-size:.82rem;color:var(--muted);margin-bottom:.4rem;}
.dsx-field input,.dsx-field textarea,.dsx-field select{width:100%;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.7rem .9rem;color:var(--text);font-family:inherit;font-size:.9rem;}
.dsx-field input:focus,.dsx-field textarea:focus,.dsx-field select:focus{outline:2px solid var(--blue);outline-offset:1px;}
.dsx-table{width:100%;border-collapse:collapse;font-size:.85rem;}
.dsx-table th{text-align:left;color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;padding:.6rem;border-bottom:1px solid var(--border);}
.dsx-table td{padding:.75rem .6rem;border-bottom:1px solid var(--border);}
.dsx-progress{height:8px;border-radius:99px;background:var(--surface2);overflow:hidden;}
.dsx-progress i{display:block;height:100%;background:linear-gradient(90deg,var(--blue2),var(--blue));}
.dsx-panel{display:none;}
.dsx-panel.active{display:block;}
#dsx-view-portal.active{display:flex !important;}
.dsx-tab{display:none;}
.dsx-tab.active{display:block;}
.dsx-checklabel{display:flex;align-items:flex-start;gap:.6rem;font-size:.88rem;color:var(--muted);margin-bottom:.7rem;cursor:pointer;}
.dsx-checklabel input{margin-top:.2rem;accent-color:var(--blue);}
.dsx-note{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#f87171;font-size:.8rem;padding:.6rem .8rem;border-radius:8px;margin-top:.6rem;}

/* ---- onboarding shell ---- */
.dsx-ob{display:none;min-height:100vh;padding:0 0 3rem;}
.dsx-ob.active{display:block;}
.dsx-ob-top{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 2rem;}
.dsx-ob-stepper{padding:0 2rem 1rem;max-width:1000px;margin:0 auto;}
.dsx-ob-steps{display:flex;align-items:center;justify-content:space-between;position:relative;margin-bottom:.75rem;}
.dsx-ob-step{display:flex;align-items:center;gap:.5rem;background:transparent;position:relative;z-index:1;font-size:.85rem;color:var(--muted);}
.dsx-ob-step .c{width:28px;height:28px;border-radius:50%;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;}
.dsx-ob-step.done .c{background:var(--blue2);color:#fff;border-color:var(--blue2);}
.dsx-ob-step.now .c{background:var(--blue);color:#fff;border-color:var(--blue);}
.dsx-ob-step.now{color:#fff;font-weight:700;}
.dsx-ob-body{max-width:760px;margin:0 auto;padding:1.5rem 2rem;}
.dsx-ob-actions{max-width:760px;margin:1.5rem auto 0;padding:0 2rem;display:flex;justify-content:space-between;align-items:center;}
.dsx-verify{min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:2rem;}
.dsx-verify-card{width:420px;max-width:90vw;background:var(--card);border:1px solid var(--border);border-radius:18px;padding:2rem;text-align:center;}
.dsx-icon-round{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--blue2),var(--pink));display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;}
.dsx-thumb{border-radius:12px;height:110px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;letter-spacing:.08em;color:#fff;}
</style>

<!-- ===================== ONBOARDING: VERIFY EMAIL ===================== -->
<div class="dsx-verify" id="dsx-ob-verify">
  <div class="dsx-verify-card">
    <div class="dsx-icon-round">✉️</div>
    <h2 style="font-size:1.3rem;margin-bottom:.6rem;">Verify your email address</h2>
    <p style="color:var(--muted);font-size:.88rem;margin-bottom:1rem;">Check your inbox for a verification link. Verify your email to continue setting up your creator account.</p>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.7rem;font-size:.78rem;color:var(--muted);margin-bottom:1.2rem;">Your creator account is not fully activated until email verification is complete.</div>
    <button class="dsx-btn" style="width:100%;justify-content:center;margin-bottom:.6rem;" onclick="dsxGo('ob-1')">I've verified — continue setup</button>
    <button class="dsx-btn outline" style="width:100%;justify-content:center;margin-bottom:.6rem;" onclick="dsxToast('Verification email resent')">↻ Resend verification email</button>
    <a href="#" style="font-size:.8rem;color:var(--muted);">Use a different email</a>
  </div>
</div>

<!-- ===================== ONBOARDING: STEPPER FLOW ===================== -->
<div class="dsx-ob" id="dsx-view-onboarding">
  <div class="dsx-ob-top" style="justify-content:flex-end;">
    <div id="dsx-ob-pct" style="color:var(--muted);font-size:.85rem;">0% complete</div>
  </div>
  <div class="dsx-ob-stepper">
    <div class="dsx-ob-steps" id="dsx-ob-steps-row">
      <div class="dsx-ob-step" data-step="1"><span class="c">1</span><span>Personal Information</span></div>
      <div class="dsx-ob-step" data-step="2"><span class="c">2</span><span>Creator Profile</span></div>
      <div class="dsx-ob-step" data-step="3"><span class="c">3</span><span>Payment Details</span></div>
      <div class="dsx-ob-step" data-step="4"><span class="c">4</span><span>Creator Agreement</span></div>
      <div class="dsx-ob-step" data-step="5"><span class="c">5</span><span>Complete</span></div>
    </div>
    <div class="dsx-progress"><i id="dsx-ob-bar" style="width:0%"></i></div>
  </div>

  <!-- Step 1: Personal Information -->
  <div class="dsx-ob-panel dsx-panel active" data-obstep="1">
    <div class="dsx-ob-body">
      <h1 class="dsx-h1">Complete your creator profile</h1>
      <p class="dsx-sub">Before uploading your first product, please complete the following information.</p>
      <div class="dsx-card" style="margin-bottom:1rem;">
        <h3 style="margin-bottom:.2rem;">Personal Information</h3>
        <p class="dsx-sub" style="margin-bottom:1rem;">Your display name is the public creator name shown on Designstager. Legal details stay private.</p>
        <button class="dsx-btn outline sm" style="margin-bottom:1.2rem;" onclick="dsxToast('Photo upload is cosmetic in this preview')">⬆ Upload profile photo</button>
        <div class="dsx-grid g2">
          <div class="dsx-field"><label>Full Legal Name *</label><input value="Sujnendra Bhat"></div>
          <div class="dsx-field"><label>Display Name *</label><input value="Suj Bhat Design"></div>
          <div class="dsx-field"><label>Country *</label><input value="India"></div>
          <div class="dsx-field"><label>Phone Number *</label><input value="+91 98•• ••••"></div>
          <div class="dsx-field"><label>Address *</label><input value="12 Residency Road"></div>
          <div class="dsx-field"><label>City *</label><input value="Bengaluru"></div>
          <div class="dsx-field"><label>State / Province *</label><input value="Karnataka"></div>
          <div class="dsx-field"><label>Postal / ZIP Code *</label><input value="560025"></div>
        </div>
      </div>
      <div class="dsx-card">
        <h3 style="margin-bottom:.2rem;">Links &amp; social profiles</h3>
        <p class="dsx-sub" style="margin-bottom:1rem;">Optional, but they help buyers trust your work.</p>
        <div class="dsx-grid g2">
          <div class="dsx-field"><label>Website</label><input placeholder="https://"></div>
          <div class="dsx-field"><label>Portfolio URL</label><input placeholder="https://"></div>
          <div class="dsx-field"><label>LinkedIn</label><input placeholder="https://"></div>
          <div class="dsx-field"><label>Instagram</label><input placeholder="https://"></div>
          <div class="dsx-field"><label>Behance</label><input placeholder="https://"></div>
          <div class="dsx-field"><label>Dribbble</label><input placeholder="https://"></div>
        </div>
        <div class="dsx-field"><label>Other social profile</label><input placeholder="https://"></div>
      </div>
    </div>
    <div class="dsx-ob-actions">
      <a href="#" onclick="dsxToast('Saved (cosmetic)');return false;" style="font-size:.85rem;color:var(--muted);">💾 Save &amp; continue later</a>
      <button class="dsx-btn" onclick="dsxObStep(2)">Next: Creator Profile →</button>
    </div>
  </div>

  <!-- Step 2: Creator Profile -->
  <div class="dsx-ob-panel dsx-panel" data-obstep="2">
    <div class="dsx-ob-body">
      <h1 class="dsx-h1">Tell us about your creative work</h1>
      <p class="dsx-sub">This information powers your public creator profile and marketplace discovery.</p>
      <div class="dsx-card" style="margin-bottom:1rem;">
        <h3>Creator Type</h3>
        <p class="dsx-sub" style="margin-bottom:.8rem;">Select all that apply.</p>
        <div class="dsx-chipwrap" id="dsx-creatortype"></div>
      </div>
      <div class="dsx-card" style="margin-bottom:1rem;">
        <h3 style="margin-bottom:.8rem;">Skills</h3>
        <div class="dsx-field"><input placeholder="Search skills…"></div>
        <div class="dsx-chipwrap" id="dsx-skills"></div>
      </div>
      <div class="dsx-card" style="margin-bottom:1rem;">
        <h3 style="margin-bottom:.8rem;">Creator Bio</h3>
        <textarea rows="4" placeholder="Tell customers about your design experience, creative style, and areas of expertise." maxlength="600" oninput="document.getElementById('dsx-biocount').textContent=this.value.length"></textarea>
        <div style="text-align:right;color:var(--muted);font-size:.75rem;margin-top:.3rem;"><span id="dsx-biocount">0</span>/600 characters</div>
      </div>
      <div class="dsx-card">
        <div class="dsx-grid g2">
          <div>
            <label style="display:block;font-size:.82rem;color:var(--muted);margin-bottom:.4rem;">Years of experience (optional)</label>
            <input value="9" style="width:100%;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:.7rem .9rem;color:var(--text);">
          </div>
          <div>
            <label style="display:block;font-size:.82rem;color:var(--muted);margin-bottom:.4rem;">Languages</label>
            <div class="dsx-chipwrap" id="dsx-langs"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="dsx-ob-actions">
      <div style="display:flex;gap:.6rem;"><button class="dsx-btn outline" onclick="dsxObStep(1)">← Back</button><button class="dsx-btn outline" onclick="dsxToast('Saved (cosmetic)')">💾 Save</button></div>
      <button class="dsx-btn" onclick="dsxObStep(3)">Next: Payment Details →</button>
    </div>
  </div>

  <!-- Step 3: Payment Details -->
  <div class="dsx-ob-panel dsx-panel" data-obstep="3">
    <div class="dsx-ob-body">
      <h1 class="dsx-h1">Add your payout method</h1>
      <p class="dsx-sub">Designstager stores payout details with a tokenized payment provider — full account numbers are never shown.</p>
      <div class="dsx-card">
        <div class="dsx-grid g2">
          <div class="dsx-field"><label>Account holder</label><input value="Sujnendra Bhat"></div>
          <div class="dsx-field"><label>Method</label><select><option>Bank Transfer (IMPS/NEFT)</option><option>UPI</option><option>PayPal</option></select></div>
          <div class="dsx-field"><label>Bank name</label><input value="HDFC Bank"></div>
          <div class="dsx-field"><label>Account number</label><input value="•••• •••• 4821"></div>
          <div class="dsx-field"><label>UPI ID (optional)</label><input value="sujbhat@okhdfc"></div>
          <div class="dsx-field"><label>Currency</label><select><option>INR</option><option>USD</option></select></div>
        </div>
      </div>
    </div>
    <div class="dsx-ob-actions">
      <button class="dsx-btn outline" onclick="dsxObStep(2)">← Back</button>
      <button class="dsx-btn" onclick="dsxObStep(4)">Next: Creator Agreement →</button>
    </div>
  </div>

  <!-- Step 4: Creator Agreement -->
  <div class="dsx-ob-panel dsx-panel" data-obstep="4">
    <div class="dsx-ob-body">
      <h1 class="dsx-h1">Creator Agreement</h1>
      <p class="dsx-sub">Agreement version 3.2 · Acceptance is recorded with your creator ID and timestamp.</p>
      <div class="dsx-card" style="margin-bottom:1rem;">
        <h3 style="margin-bottom:.8rem;">Policies</h3>
        <div class="dsx-grid g2" id="dsx-ob-policies"></div>
      </div>
      <div class="dsx-card">
        <h3 style="margin-bottom:.8rem;">Required confirmations</h3>
        <label class="dsx-checklabel"><input type="checkbox" checked> I confirm that I own or have the necessary rights to the content I upload.</label>
        <label class="dsx-checklabel"><input type="checkbox" checked> I confirm that I have the appropriate licenses for third-party fonts, images, icons, templates, music, or other assets included in my content.</label>
        <label class="dsx-checklabel"><input type="checkbox"> I agree to the Designstager Creator Terms.</label>
        <label class="dsx-checklabel"><input type="checkbox"> I understand Designstager's revenue-sharing and payment policies.</label>
        <label class="dsx-checklabel"><input type="checkbox"> I understand that submitted content may be reviewed before publication.</label>
      </div>
    </div>
    <div class="dsx-ob-actions">
      <button class="dsx-btn outline" onclick="dsxObStep(3)">← Back</button>
      <button class="dsx-btn" onclick="dsxObStep(5)">Agree &amp; Continue →</button>
    </div>
  </div>

  <!-- Step 5: Complete -->
  <div class="dsx-ob-panel dsx-panel" data-obstep="5">
    <div class="dsx-ob-body" style="text-align:center;padding-top:2rem;">
      <div class="dsx-card">
        <div class="dsx-icon-round">🎉</div>
        <h2 style="font-size:1.5rem;margin-bottom:.5rem;">Your Creator Account is Ready</h2>
        <p class="dsx-sub">Everything is in place. You can start publishing to the Designstager marketplace.</p>
      </div>
      <div class="dsx-card" style="margin-top:1rem;text-align:left;">
        <div style="display:flex;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Email</span><span class="dsx-badge2 b-green">✓ Verified</span></div>
        <div style="display:flex;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Personal Profile</span><span class="dsx-badge2 b-green">✓ Complete</span></div>
        <div style="display:flex;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Creator Profile</span><span class="dsx-badge2 b-green">✓ Complete</span></div>
        <div style="display:flex;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Payment Details</span><span class="dsx-badge2 b-green">✓ Verified</span></div>
        <div style="display:flex;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Creator Agreement</span><span class="dsx-badge2 b-green">✓ Accepted</span></div>
        <div style="display:flex;justify-content:space-between;padding:.7rem 0;"><span class="dsx-sub" style="margin:0;">Creator Status</span><span class="dsx-badge2 b-green">● Published</span></div>
      </div>
      <div style="margin-top:1.2rem;display:flex;justify-content:center;gap:.8rem;">
        <button class="dsx-btn outline" onclick="dsxGo('portal');dsxNav('dashboard')">Go to Dashboard</button>
        <button class="dsx-btn" onclick="dsxGo('portal');dsxNav('upload')">Upload Your First Product</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== MAIN PORTAL ===================== -->
<div class="dsx-shell dsx-panel" id="dsx-view-portal">
  <aside class="dsx-side">
    <div class="dsx-user"><div class="av"><?php echo esc_html($ds_initials); ?></div><div><div class="nm"><?php echo esc_html($ds_display_name); ?></div><div class="vf">✓ Signed in</div></div></div>
    <nav class="dsx-nav" id="dsx-nav">
      <a data-tab="dashboard" class="active"><span class="ic">▦</span><span class="lb">Dashboard</span></a>
      <a data-tab="upload"><span class="ic">⬆</span><span class="lb">Upload Product</span></a>
      <a data-tab="products"><span class="ic">◧</span><span class="lb">My Products</span></a>
      <a data-tab="analytics"><span class="ic">📈</span><span class="lb">Analytics</span></a>
      <a data-tab="earnings"><span class="ic">▤</span><span class="lb">Earnings</span></a>
      <a data-tab="withdrawals"><span class="ic">▣</span><span class="lb">Withdrawals</span></a>
      <a data-tab="profile"><span class="ic">◎</span><span class="lb">Creator Profile</span></a>
      <a data-tab="payment"><span class="ic">▭</span><span class="lb">Payment Details</span></a>
      <a data-tab="agreement"><span class="ic">▤</span><span class="lb">Creator Agreement</span></a>
      <a data-tab="notifications"><span class="ic">🔔</span><span class="lb">Notifications</span><span class="dsx-badge" id="dsx-notif-badge">3</span></a>
      <a data-tab="help"><span class="ic">◈</span><span class="lb">Help &amp; Support</span></a>
      <a href="#" onclick="dsxGo('ob-1');return false;"><span class="ic">✎</span><span class="lb">Account Setup Wizard</span></a>
      <a href="<?php echo esc_url($ds_logout_url); ?>"><span class="ic">⎋</span><span class="lb">Logout</span></a>
    </nav>
  </aside>

  <div class="dsx-main">
    <div class="dsx-topbar">
      <div class="dsx-crumb">Designstager Creator Portal · <b><?php echo esc_html($ds_display_name); ?></b></div>
      <div class="dsx-top-actions"><span>🔔</span><button class="dsx-btn sm" onclick="dsxNav('upload')">⬆ Upload</button></div>
    </div>
    <div class="dsx-content">

      <!-- DASHBOARD -->
      <section class="dsx-tab active" data-tab="dashboard">
        <h1 class="dsx-h1">Welcome back, Suj Bhat Design</h1>
        <p class="dsx-sub">Here's how your catalog performed over the last 30 days.</p>
        <div class="dsx-grid g3" style="margin-bottom:1rem;">
          <div class="dsx-stat"><div class="lbl">Total Products</div><div class="val">…</div><div class="sub">Across all statuses</div></div>
          <div class="dsx-stat"><div class="lbl">Total Downloads</div><div class="val">…</div><div class="sub">All published products</div></div>
          <div class="dsx-stat"><div class="lbl">Total Earnings</div><div class="val">…</div><div class="sub">Lifetime creator earnings (₹2/download)</div></div>
          <div class="dsx-stat"><div class="lbl">Pending Earnings</div><div class="val">…</div><div class="sub">Not tracked separately yet</div></div>
          <div class="dsx-stat"><div class="lbl">Available Balance</div><div class="val">…</div><div class="sub">Withdrawals not tracked yet</div></div>
          <div class="dsx-stat"><div class="lbl">This Month</div><div class="val">…</div><div class="sub">Downloads · earnings</div></div>
        </div>
        <div class="dsx-card" style="margin-bottom:1rem;">
          <div class="dsx-row" style="margin-bottom:.5rem;">
            <div><h3>Your creator profile is 85% complete</h3><p class="dsx-sub" style="margin-bottom:0;">Publishing is blocked until mandatory information is complete.</p></div>
            <button class="dsx-btn sm" onclick="dsxNav('profile')">Complete Profile ↗</button>
          </div>
          <div class="dsx-progress" style="margin-bottom:.7rem;"><i style="width:85%"></i></div>
          <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <span class="dsx-chip on" style="cursor:pointer;" onclick="dsxNav('profile');dsxProfileToggleEdit(true);">Add portfolio</span>
<span class="dsx-chip on" style="cursor:pointer;" onclick="dsxNav('payment')">Complete payment verification</span>
<span class="dsx-chip on" style="cursor:pointer;" onclick="dsxNav('profile');dsxProfileToggleEdit();">Add creator bio</span></div>
        </div>
        <div class="dsx-grid g2" style="grid-template-columns:1.4fr 1fr;margin-bottom:1rem;">
          <div class="dsx-card"><div class="dsx-row" style="margin-bottom:.4rem;align-items:center;"><div><h3 style="margin-bottom:.2rem;">Downloads &amp; earnings</h3><p class="dsx-sub" id="dsx-dash-chart-sub" style="margin-bottom:0;">Loading…</p></div><div style="display:flex;gap:.4rem;flex-wrap:wrap;" id="dsx-dash-range"></div></div><canvas id="dsx-chart-dash" height="150"></canvas></div>
          <div class="dsx-card"><h3 style="margin-bottom:.8rem;">Recent products</h3><div id="dsx-recent-products"></div></div>
        </div>
        <div class="dsx-card">
          <div class="dsx-row" style="margin-bottom:0;"><div><h3>New here?</h3><p class="dsx-sub" style="margin-bottom:0;">A quick path to your first published product.</p></div><button class="dsx-btn" onclick="dsxNav('upload')">✨ Upload Your First Product</button></div>
        </div>
      </section>

      <!-- UPLOAD WIZARD -->
      <section class="dsx-tab" data-tab="upload">
        <h1 class="dsx-h1">Upload Product</h1>
        <p class="dsx-sub">Seven quick steps from file to marketplace submission.</p>
        <div class="dsx-card" style="margin-bottom:1.2rem;">
          <div class="dsx-ob-steps" id="dsx-up-steps" style="flex-wrap:wrap;gap:.5rem;">
            <div class="dsx-ob-step" data-ustep="1"><span class="c">1</span><span>Content Type</span></div>
            <div class="dsx-ob-step" data-ustep="2"><span class="c">2</span><span>Upload Files</span></div>
            <div class="dsx-ob-step" data-ustep="3"><span class="c">3</span><span>Product Info</span></div>
            <div class="dsx-ob-step" data-ustep="4"><span class="c">4</span><span>Preview</span></div>
            <div class="dsx-ob-step" data-ustep="5"><span class="c">5</span><span>Technical</span></div>
            <div class="dsx-ob-step" data-ustep="6"><span class="c">6</span><span>Rights &amp; License</span></div>
            <div class="dsx-ob-step" data-ustep="7"><span class="c">7</span><span>Review</span></div>
          </div>
          <div class="dsx-progress" style="margin-top:.7rem;"><i id="dsx-up-bar" style="width:14%"></i></div>
        </div>

        <div class="dsx-up-panel dsx-panel active" data-ustep="1">
          <div class="dsx-card"><h3 style="margin-bottom:.2rem;">Content Type</h3><p class="dsx-sub">Fields adapt to the type you pick.</p><div class="dsx-chipwrap" id="dsx-contenttype"></div></div>
        </div>
        <div class="dsx-up-panel dsx-panel" data-ustep="2">
          <div class="dsx-card">
            <h3 style="margin-bottom:.2rem;">Upload Files</h3><p class="dsx-sub">Your main design file — the deliverable buyers download.</p>
            <div id="dsx-dz-design" style="border:2px dashed var(--border);border-radius:12px;padding:2.5rem;text-align:center;color:var(--muted);margin-bottom:1rem;cursor:pointer;">⬆<br>Drag &amp; drop your file here<br><span style="color:var(--blue);">or Browse Files</span><br><span style="font-size:.75rem;">PPTX, PDF, ZIP, PNG, JPG, MP4, AI, PSD, FIG</span>
              <input type="file" id="dsx-file-design" style="display:none;">
            </div>
            <div id="dsx-filelist"></div>
          </div>
        </div>
        <div class="dsx-up-panel dsx-panel" data-ustep="3">
          <div class="dsx-card">
            <div class="dsx-row"><h3>Product Information</h3></div>
            <div class="dsx-field"><label>Product Title</label><input id="dsx-up-title" placeholder="e.g. AI Business Presentation Suite"></div>
            <div class="dsx-field"><label>Short Description</label><input id="dsx-up-short" placeholder="One line that sells the product"></div>
            <div class="dsx-field"><label>Full Description</label><textarea id="dsx-up-long" rows="4"></textarea></div>
            <div class="dsx-field"><label>Tags / keywords (comma separated)</label><input id="dsx-up-tags" placeholder="business, corporate, dark"></div>
            <div class="dsx-grid g2">
              <div class="dsx-field"><label>Category</label><select id="dsx-up-category"><option value="">Loading categories…</option></select></div>
              <div class="dsx-field" id="dsx-up-newcat-wrap" style="display:none;"><label>New category name</label><input id="dsx-up-newcat" placeholder="e.g. Presentations"></div>
            </div>
          </div>
        </div>
        <div class="dsx-up-panel dsx-panel" data-ustep="4">
          <div class="dsx-grid g2">
            <div class="dsx-card"><h3 style="margin-bottom:.2rem;">Preview assets</h3><p class="dsx-sub">Thumbnail (16:9 recommended) — shown on the product card and marketplace listing.</p>
              <div id="dsx-dz-thumb" style="border:2px dashed var(--border);border-radius:10px;padding:1rem;text-align:center;color:var(--muted);margin-bottom:.6rem;cursor:pointer;">⬆ Thumbnail (16:9)
                <input type="file" id="dsx-file-thumb" accept="image/*" style="display:none;">
              </div>
              <div id="dsx-thumb-info"></div>
            </div>
            <div class="dsx-card"><h3 style="margin-bottom:.8rem;">How your product will appear</h3>
<div id="dsx-preview-thumb" style="width:100%;aspect-ratio:16/9;border-radius:12px;background:linear-gradient(135deg,#2563eb,#7c3aed) center/cover no-repeat;margin-bottom:.8rem;"></div>
<div id="dsx-preview-title" style="font-weight:600;">Your product title</div><div id="dsx-preview-meta" class="dsx-sub" style="margin:.2rem 0;">by <?php echo esc_js($ds_display_name); ?></div>
</div>
          </div>
        </div>
        <div class="dsx-up-panel dsx-panel" data-ustep="5">
<div class="dsx-card"><h3 style="margin-bottom:.2rem;">Technical Details</h3><p class="dsx-sub" id="dsx-tech-sub">Fields shown for your selected content type.</p>
<div class="dsx-grid g2" id="dsx-tech-fields"></div>
</div>
</div>
<div class="dsx-up-panel dsx-panel" data-ustep="6">
          <div class="dsx-card" style="margin-bottom:1rem;"><h3>Rights &amp; Copyright</h3>
            <label class="dsx-checklabel"><input type="checkbox" id="dsx-rights-owner"> I confirm that I own or have the necessary rights to distribute this product through Designstager.</label>
            <p style="margin:1rem 0 .5rem;font-weight:600;font-size:.9rem;">Does this product contain AI-generated or AI-assisted content?</p>
            <label class="dsx-checklabel"><input type="radio" name="ai" value="none" checked> No</label>
            <label class="dsx-checklabel"><input type="radio" name="ai" value="ai-assisted"> AI-assisted</label>
            <label class="dsx-checklabel"><input type="radio" name="ai" value="ai-generated"> AI-generated</label>
          </div>
          <div class="dsx-card"><h3 style="margin-bottom:.6rem;">Licensing confirmations</h3><p class="dsx-sub">Confirm you hold the rights for every included asset. Leave unchecked whatever doesn't apply.</p>
            <label class="dsx-checklabel"><input type="checkbox" class="dsx-licensed" value="Fonts"> Licensed: Fonts</label>
            <label class="dsx-checklabel"><input type="checkbox" class="dsx-licensed" value="Images"> Licensed: Images</label>
            <label class="dsx-checklabel"><input type="checkbox" class="dsx-licensed" value="Icons"> Licensed: Icons</label>
            <label class="dsx-checklabel"><input type="checkbox" class="dsx-licensed" value="Music"> Licensed: Music</label>
            <label class="dsx-checklabel"><input type="checkbox" class="dsx-licensed" value="Stock assets"> Licensed: Stock assets</label>
            <label class="dsx-checklabel"><input type="checkbox" class="dsx-licensed" value="Third-party resources"> Licensed: Third-party resources</label>
          </div>
        </div>
        <div class="dsx-up-panel dsx-panel" data-ustep="7">
          <div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.5rem;">Review everything before you submit</h3><p class="dsx-sub" style="margin-bottom:0;">This is exactly what will be saved to your upload — check it over.</p></div>
          <div id="dsx-up-review"></div>
          <div class="dsx-card"><h3 style="margin-bottom:.5rem;">Submit</h3><p class="dsx-sub">Most submissions are reviewed within 2–3 business days.</p>
            <div id="dsx-up-error" class="dsx-note" style="display:none;"></div>
            <button class="dsx-btn" id="dsx-up-submit" onclick="dsxSubmitUpload(false)">Submit for Review</button>
            <button class="dsx-btn outline" onclick="dsxSubmitUpload(true)">Save as Draft</button>
          </div>
        </div>
        <div class="dsx-ob-actions" style="max-width:none;padding:0;margin-top:1.2rem;">
          <button class="dsx-btn outline" onclick="dsxUpStep(-1)">← Back</button>
          <button class="dsx-btn" onclick="dsxUpStep(1)">Continue →</button>
        </div>
      </section>

      <!-- MY PRODUCTS -->
      <section class="dsx-tab" data-tab="products">
        <div class="dsx-row"><div><h1 class="dsx-h1">My Products</h1><p class="dsx-sub" style="margin-bottom:0;">Everything you've uploaded to Designstager, with live review state.</p></div><button class="dsx-btn" onclick="dsxNav('upload')">Upload Product</button></div>
        <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;">
          <span class="dsx-chip on">All</span><span class="dsx-chip">Published</span><span class="dsx-chip">Pending Review</span><span class="dsx-chip">Drafts</span><span class="dsx-chip">Rejected</span><span class="dsx-chip">Suspended</span>
        </div>
        <div id="dsx-products-list"></div>
      </section>

      <!-- ANALYTICS -->
      <section class="dsx-tab" data-tab="analytics">
        <div class="dsx-row"><div><h1 class="dsx-h1">Analytics</h1><p class="dsx-sub" style="margin-bottom:0;">Performance across your entire Designstager catalog.</p></div>
          <div style="display:flex;gap:.4rem;"><span class="dsx-chip">7 Days</span><span class="dsx-chip on">30 Days</span><span class="dsx-chip">90 Days</span><span class="dsx-chip">1 Year</span><span class="dsx-chip">All Time</span></div>
        </div>
        <div class="dsx-grid g6" style="grid-template-columns:repeat(4,1fr);margin-bottom:1rem;">
          <div class="dsx-stat"><div class="lbl">Total Products</div><div class="val">0</div></div>
          <div class="dsx-stat"><div class="lbl">Total Downloads</div><div class="val">0</div></div>
          <div class="dsx-stat"><div class="lbl">Published</div><div class="val">0</div></div>
          <div class="dsx-stat"><div class="lbl">Total Earnings</div><div class="val">₹0.00</div></div>
        </div>
        <p class="dsx-sub" style="margin:-.6rem 0 1rem;">View counts and conversion tracking aren't wired up yet — only real downloads and earnings from Download Monitor are shown here.</p>
        <div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.2rem;">Downloads &amp; earnings</h3><p class="dsx-sub">Trend for the last 7 months</p><canvas id="dsx-chart-analytics" height="90"></canvas></div>
        <div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.8rem;">Top performing products</h3><p class="dsx-sub">Ranked by downloads</p><div id="dsx-top-products"></div></div>
        <div class="dsx-card"><h3 style="margin-bottom:.2rem;">Every product — full detail</h3><p class="dsx-sub">Everything captured in the Upload wizard, per product.</p>
          <table class="dsx-table"><thead><tr><th>Product</th><th>Content type</th><th>Status</th><th>Downloads</th><th>Earnings</th><th>Rights</th><th>AI content</th></tr></thead><tbody id="dsx-analytics-full"></tbody></table>
        </div>
      </section>

      <!-- EARNINGS -->
      <section class="dsx-tab" data-tab="earnings">
        <div class="dsx-row"><div><h1 class="dsx-h1">Earnings</h1><p class="dsx-sub" style="margin-bottom:0;">Revenue from downloads, bundles and marketplace events.</p></div><button class="dsx-btn" onclick="dsxNav('withdrawals')">↓ Request Withdrawal</button></div>
        <div class="dsx-grid g3" style="margin-bottom:1rem;">
          <div class="dsx-stat"><div class="lbl">Total Earnings</div><div class="val">…</div></div>
          <div class="dsx-stat"><div class="lbl">This Month</div><div class="val">…</div></div>
          <div class="dsx-stat"><div class="lbl">Last Month</div><div class="val">…</div></div>
          <div class="dsx-stat"><div class="lbl">Pending Earnings</div><div class="val">…</div></div>
          <div class="dsx-stat"><div class="lbl">Available Balance</div><div class="val">…</div></div>
          <div class="dsx-stat"><div class="lbl">Total Withdrawn</div><div class="val">…</div></div>
        </div>
        <div class="dsx-card"><h3 style="margin-bottom:.2rem;">Transaction history</h3><p class="dsx-sub">₹2 credited to you per real download, logged automatically by Download Monitor.</p>
          <table class="dsx-table"><thead><tr><th>Date</th><th>Product</th><th>Event</th><th>Earnings</th><th>Status</th></tr></thead><tbody id="dsx-earnings-rows"></tbody></table>
        </div>
      </section>

      <!-- WITHDRAWALS -->
      <section class="dsx-tab" data-tab="withdrawals">
        <h1 class="dsx-h1">Withdrawals</h1><p class="dsx-sub">Move your available balance to your verified payout method.</p>
        <div class="dsx-note" style="margin-bottom:1rem;">Withdrawal processing isn't wired to a real payout system yet — Snippets 1/3/4/5 track uploads and earnings only. Your balance below is real; the request button is a placeholder until a payout flow is added.</div>
        <div class="dsx-grid g3" style="margin-bottom:1rem;">
          <div class="dsx-stat"><div class="lbl">Available Balance</div><div class="val">…</div></div>
          <div class="dsx-stat"><div class="lbl">Minimum Withdrawal</div><div class="val">₹500.00</div></div>
          <div class="dsx-stat"><div class="lbl">Total Withdrawn</div><div class="val">₹0.00</div></div>
        </div>
        <div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.2rem;">Request a withdrawal</h3><p class="dsx-sub">Payouts are processed within 3–5 business days once this is connected to a payout provider.</p>
          <div class="dsx-grid g2">
            <div class="dsx-field"><label>Payment Method</label><select><option>HDFC Bank •••• 4821</option></select></div>
            <div class="dsx-field"><label>Amount to Withdraw (INR)</label><input value="500"></div>
          </div>
          <div class="dsx-row" style="margin-bottom:0;align-items:center;"><span>Payout method status: <span class="dsx-badge2 b-amber">● Pending Verification</span></span><button class="dsx-btn" onclick="dsxToast('Withdrawal requests need a payout backend — not part of Snippets 1/3/4/5 yet')">Request Withdrawal</button></div>
        </div>
        <div class="dsx-card"><div class="dsx-row" style="margin-bottom:.6rem;"><h3>Withdrawal history</h3><a href="#" onclick="dsxNav('payment');return false;" style="font-size:.85rem;color:var(--blue);">Manage payout method</a></div>
          <table class="dsx-table"><thead><tr><th>Date</th><th>Amount</th><th>Payment Method</th><th>Status</th><th>Reference ID</th></tr></thead><tbody id="dsx-withdrawal-rows"><tr><td colspan="5" class="dsx-sub" style="text-align:center;padding:1rem;">No withdrawal system connected yet.</td></tr></tbody></table>
        </div>
      </section>

      <!-- CREATOR PROFILE -->
<section class="dsx-tab" data-tab="profile">
<div class="dsx-row"><div><h1 class="dsx-h1">Creator Profile</h1><p class="dsx-sub" style="margin-bottom:0;">This is the public profile shown on the Designstager marketplace.</p></div><button class="dsx-btn outline sm" id="dsx-profile-edit-btn" onclick="dsxProfileToggleEdit()">✎ Edit profile</button></div>
<div id="dsx-profile-view">
<div class="dsx-card" style="margin-bottom:1rem;display:flex;gap:1rem;align-items:flex-start;">
<div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--blue2),var(--pink));display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;"><?php echo esc_html($ds_initials); ?></div>
<div><div style="display:flex;align-items:center;gap:.5rem;"><h3 id="dsx-profile-name"><?php echo esc_html($ds_display_name); ?></h3><span class="dsx-badge2 b-green">✓ Verified</span></div>
<p class="dsx-sub" id="dsx-profile-bio-view" style="margin:.4rem 0;">Loading…</p>
<p class="dsx-sub" id="dsx-profile-meta-view" style="margin:0;font-size:.8rem;">—</p>
</div>
</div>
<div class="dsx-card" style="margin-bottom:1rem;">
<div class="dsx-row" style="margin-bottom:.5rem;"><h3>Profile completion</h3><span id="dsx-profile-pct">0%</span></div>
<div class="dsx-progress" style="margin-bottom:.7rem;"><i id="dsx-profile-pct-bar" style="width:0%"></i></div>
<div style="display:flex;gap:.5rem;flex-wrap:wrap;" id="dsx-profile-checklist"></div>
</div>
<div class="dsx-grid g2" style="margin-bottom:1rem;">
<div class="dsx-card"><h3 style="margin-bottom:.7rem;">Skills</h3><div class="dsx-chipwrap" id="dsx-profile-skills-view"></div></div>
<div class="dsx-card"><h3 style="margin-bottom:.7rem;">Portfolio links</h3><div id="dsx-profile-links-view"></div></div>
</div>
<div class="dsx-card"><h3 style="margin-bottom:.3rem;">Published products</h3><p class="dsx-sub">Private information such as legal name, address, phone, bank and tax data is never shown publicly.</p>
<div class="dsx-grid g3" id="dsx-profile-products"></div>
</div>
</div>
<div id="dsx-profile-edit" class="hide">
<div class="dsx-card" style="margin-bottom:1rem;">
<h3 style="margin-bottom:.2rem;">Basic Info</h3>
<div class="dsx-field"><label>Display Name *</label><input id="dsx-pf-display-name"></div>
<div class="dsx-field"><label>Creator Bio *</label><textarea id="dsx-pf-bio" rows="4" placeholder="Tell customers about your design experience, creative style, and areas of expertise."></textarea></div>
<div class="dsx-field"><label>Years of experience</label><input id="dsx-pf-years"></div>
</div>
<div class="dsx-card" style="margin-bottom:1rem;">
<h3>Creator Type *</h3>
<div class="dsx-chipwrap" id="dsx-pf-creatortype"></div>
</div>
<div class="dsx-card" style="margin-bottom:1rem;">
<h3>Skills *</h3>
<div class="dsx-chipwrap" id="dsx-pf-skills"></div>
</div>
<div class="dsx-card" style="margin-bottom:1rem;">
<h3>Languages *</h3>
<div class="dsx-chipwrap" id="dsx-pf-langs"></div>
</div>
<div class="dsx-card" id="dsx-pf-portfolio-card" style="margin-bottom:1rem;">
<h3 style="margin-bottom:.8rem;">Portfolio &amp; social links</h3>
<div class="dsx-grid g2">
<div class="dsx-field"><label>Website</label><input id="dsx-pf-website" placeholder="https://"></div>
<div class="dsx-field"><label>Portfolio URL</label><input id="dsx-pf-portfolio" placeholder="https://"></div>
<div class="dsx-field"><label>LinkedIn</label><input id="dsx-pf-linkedin" placeholder="https://"></div>
<div class="dsx-field"><label>Instagram</label><input id="dsx-pf-instagram" placeholder="https://"></div>
<div class="dsx-field"><label>Behance</label><input id="dsx-pf-behance" placeholder="https://"></div>
<div class="dsx-field"><label>Dribbble</label><input id="dsx-pf-dribbble" placeholder="https://"></div>
</div>
<div class="dsx-field"><label>Other social profile</label><input id="dsx-pf-other" placeholder="https://"></div>
</div>
<div id="dsx-profile-error" class="dsx-note" style="display:none;"></div>
<div style="display:flex;gap:.6rem;">
<button class="dsx-btn" id="dsx-profile-save-btn" onclick="dsxProfileSave()">Save Profile</button>
<button class="dsx-btn outline" onclick="dsxProfileToggleEdit()">Cancel</button>
</div>
</div>
</section>
<!-- PAYMENT DETAILS -->
      <section class="dsx-tab" data-tab="payment">
        <div class="dsx-row"><div><h1 class="dsx-h1">Payment Details</h1><p class="dsx-sub" style="margin-bottom:0;">Designstager stores payout details with a tokenized payment provider — full account numbers are never shown.</p></div><button class="dsx-btn" onclick="dsxToast('Verification started (cosmetic)')">Verify payout method</button></div>
        <div class="dsx-card" style="margin-bottom:1rem;">
          <div class="dsx-row" style="margin-bottom:.8rem;"><h3>Payout method</h3><span class="dsx-badge2 b-amber">● Pending Verification</span></div>
          <div class="dsx-grid g2">
            <div class="dsx-field"><label>Account</label><input value="HDFC Bank •••• 4821" readonly></div>
            <div class="dsx-field"><label>Account holder</label><input value="Sujnendra Bhat" readonly></div>
            <div class="dsx-field"><label>Method</label><input value="Bank Transfer (IMPS/NEFT)" readonly></div>
            <div class="dsx-field"><label>UPI ID</label><input value="sujbhat@okhdfc" readonly></div>
            <div class="dsx-field"><label>Currency</label><input value="INR" readonly></div>
          </div>
          <div style="display:flex;gap:.8rem;"><button class="dsx-btn outline sm" onclick="dsxToast('Update flow is cosmetic in this preview')">Update payout details</button><a href="#" onclick="dsxToast('Removal is cosmetic in this preview');return false;" style="color:var(--red);font-size:.85rem;align-self:center;">Remove method</a></div>
        </div>
        <div class="dsx-card"><h3 style="margin-bottom:.7rem;">Security</h3>
          <p class="dsx-sub">🛡 Account numbers are masked everywhere in the dashboard after saving.</p>
          <p class="dsx-sub">🛡 Only you can view or change your payout method — creators cannot access each other's data.</p>
          <p class="dsx-sub">🛡 Every change to payment details is recorded in the account audit log.</p>
          <p class="dsx-sub" style="margin-bottom:0;">🔒 Sensitive financial data beyond payout requirements is never collected.</p>
        </div>
      </section>

      <!-- CREATOR AGREEMENT (portal) -->
      <section class="dsx-tab" data-tab="agreement">
        <div class="dsx-row"><div><h1 class="dsx-h1">Creator Agreement</h1><p class="dsx-sub" style="margin-bottom:0;">Accepted on 12 March 2024 · Version 3.2 · Creator ID DS-CR-208471</p></div><button class="dsx-btn outline sm" onclick="dsxToast('Download is cosmetic in this preview')">⬇ Download copy</button></div>
        <div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.8rem;">Policies</h3><div class="dsx-grid g2" id="dsx-portal-policies"></div></div>
        <div class="dsx-card"><h3 style="margin-bottom:.8rem;">Your accepted confirmations</h3>
          <p class="dsx-sub">✓ I confirm that I own or have the necessary rights to the content I upload.</p>
          <p class="dsx-sub">✓ I confirm that I have the appropriate licenses for third-party fonts, images, icons, templates, music, or other assets.</p>
          <p class="dsx-sub">✓ I agree to the Designstager Creator Terms.</p>
          <p class="dsx-sub">✓ I understand Designstager's revenue-sharing and payment policies.</p>
          <p class="dsx-sub" style="margin-bottom:0;">✓ I understand that submitted content may be reviewed before publication.</p>
        </div>
      </section>

      <!-- NOTIFICATIONS -->
      <section class="dsx-tab" data-tab="notifications">
        <div class="dsx-row"><div><h1 class="dsx-h1">Notifications</h1><p class="dsx-sub" style="margin-bottom:0;"><span id="dsx-unread-count">3</span> unread</p></div><button class="dsx-btn outline sm" onclick="dsxMarkAllRead()">✓ Mark all as read</button></div>
        <div id="dsx-notif-list"></div>
      </section>

      <!-- HELP & SUPPORT -->
      <section class="dsx-tab" data-tab="help">
        <h1 class="dsx-h1">Help &amp; Support</h1><p class="dsx-sub">Guides, policies and a direct line to the creator support team.</p>
        <div class="dsx-grid g3" style="margin-bottom:1rem;">
          <div class="dsx-card"><div style="color:var(--blue);margin-bottom:.5rem;">📖</div><h3 style="font-size:1rem;">Creator guidelines</h3><p class="dsx-sub" style="margin-bottom:0;">Quality, licensing and metadata standards.</p></div>
          <div class="dsx-card"><div style="color:var(--blue);margin-bottom:.5rem;">🛡</div><h3 style="font-size:1rem;">Security &amp; privacy</h3><p class="dsx-sub" style="margin-bottom:0;">How your files and payouts are protected.</p></div>
          <div class="dsx-card"><div style="color:var(--blue);margin-bottom:.5rem;">💬</div><h3 style="font-size:1rem;">Contact support</h3><p class="dsx-sub" style="margin-bottom:0;">Typical reply within one business day.</p></div>
        </div>
        <div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.8rem;">Frequently asked questions</h3><div id="dsx-faq"></div></div>
        <div class="dsx-card"><div class="dsx-row" style="margin-bottom:0;align-items:center;"><span>◎ Still stuck? Our creator team can help.</span><div style="display:flex;gap:.6rem;"><button class="dsx-btn outline sm" onclick="dsxNav('agreement')">Review agreement</button><button class="dsx-btn sm" onclick="dsxToast('Support ticket flow is cosmetic in this preview')">Contact support</button></div></div></div>
      </section>

    </div>
  </div>
</div>

<script>
(function(){
  var root = document.currentScript.closest('.dsx');

  function $(sel, ctx){ return (ctx||root).querySelector(sel); }
  function $all(sel, ctx){ return Array.prototype.slice.call((ctx||root).querySelectorAll(sel)); }

  window.dsxToast = function(msg){
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;padding:.7rem 1.2rem;border-radius:10px;font-family:Poppins,sans-serif;font-size:.85rem;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);';
    document.body.appendChild(t);
    setTimeout(function(){ t.remove(); }, 2200);
  };

  window.dsxGo = function(view){
    $('#dsx-ob-verify').classList.toggle('hide', view !== 'verify');
    $('#dsx-view-onboarding').classList.toggle('active', view.indexOf('ob-')===0 || view==='onboarding');
    $('#dsx-view-portal').classList.toggle('active', view === 'portal');
    if (view.indexOf('ob-') === 0) { dsxObStep(parseInt(view.split('-')[1],10)); }
  };

  window.dsxNav = function(tab){
    $all('.dsx-nav a').forEach(function(a){ a.classList.toggle('active', a.dataset.tab === tab); });
    $all('.dsx-tab').forEach(function(s){ s.classList.toggle('active', s.dataset.tab === tab); });
    if (tab === 'dashboard') renderDashChart();
    if (tab === 'analytics') renderAnalyticsChart();
  };
  $all('.dsx-nav a[data-tab]').forEach(function(a){
    a.addEventListener('click', function(e){ if(a.dataset.tab==='logout') return; e.preventDefault(); dsxNav(a.dataset.tab); });
  });

  var obPct = {1:0,2:25,3:50,4:75,5:100};
function dsxValidateObStep(step){
var panel = document.querySelector('.dsx-ob-panel[data-obstep="'+step+'"]');
if (!panel) return null;
var fields = $all('.dsx-field', panel);
for (var i=0;i<fields.length;i++){
var f = fields[i];
var label = f.querySelector('label');
var isRequired = label && label.textContent.indexOf('*') > -1;
if (!isRequired) continue;
var input = f.querySelector('input, textarea, select');
if (!input) continue;
if (!input.value || !String(input.value).trim()) return 'Please fill in all required fields (marked *) before continuing.';
}
if (step === 2) {
if (!panel.querySelector('#dsx-creatortype .dsx-chip.on')) return 'Select at least one Creator Type.';
if (!panel.querySelector('#dsx-skills .dsx-chip.on')) return 'Select at least one Skill.';
var bio = panel.querySelector('textarea');
if (!bio || !bio.value.trim()) return 'Add a Creator Bio before continuing.';
if (!panel.querySelector('#dsx-langs .dsx-chip.on')) return 'Select at least one Language.';
}
if (step === 3) {
var selects = $all('select', panel);
for (var s=0;s<selects.length;s++){ if (!selects[s].value) return 'Please fill in all payment fields.'; }
}
if (step === 4) {
var boxes = $all('.dsx-checklabel input[type="checkbox"]', panel);
for (var m=0;m<boxes.length;m++){ if (!boxes[m].checked) return 'You must accept all confirmations to continue.'; }
}
return null;
}
var obStep = 1;
window.dsxObStep = function(n){
if (n > obStep) {
var err = dsxValidateObStep(obStep);
if (err) { dsxToast(err); return; }
}
obStep = n;
$all('.dsx-ob-panel').forEach(function(p){ p.classList.toggle('active', parseInt(p.dataset.obstep,10) === n); });
$all('.dsx-ob-step', $('#dsx-ob-steps-row')).forEach(function(s){
var step = parseInt(s.dataset.step,10);
s.classList.toggle('done', step < n);
s.classList.toggle('now', step === n);
s.querySelector('.c').textContent = step < n ? '✓' : step;
});
$('#dsx-ob-bar').style.width = obPct[n] + '%';
$('#dsx-ob-pct').textContent = obPct[n] + '% complete';
window.scrollTo({top:0,behavior:'smooth'});
};
var upStep = 1;
  function dsxValidateStep(step){
if (step === 1) {
if (!chipsOnValues('#dsx-contenttype').length) return 'Pick a content type first.';
}
if (step === 2 && !dsxEditingId) {
if (!dsxDesignFile) return 'Add your design file before continuing.';
}
if (step === 3) {
if (!$('#dsx-up-title').value.trim()) return 'Product Title is required.';
if (!$('#dsx-up-short').value.trim()) return 'Short Description is required.';
if (!$('#dsx-up-long').value.trim()) return 'Full Description is required.';
if (!$('#dsx-up-tags').value.trim()) return 'Add at least one tag or keyword.';
if (!dsxUploadCategoryValue()) return 'Pick or create a category.';
}
if (step === 4 && !dsxEditingId) {
if (!dsxThumbFile) return 'Add a thumbnail image before continuing.';
}
if (step === 5) {
var techFields = $all('.dsx-tech-field');
var missingTech = techFields.some(function(f){ return !String(f.value||'').trim(); });
if (techFields.length && missingTech) return 'Please fill in all Technical Details fields.';
}
if (step === 6) {
if (!$('#dsx-rights-owner').checked) return 'You must confirm you own or have the rights to distribute this product.';
}
return null;
}
window.dsxUpStep = function(delta){
    if (delta > 0) {
      var err = dsxValidateStep(upStep);
      if (err) { dsxToast(err); return; }
    }
    upStep = Math.min(7, Math.max(1, upStep + delta));
    $all('.dsx-up-panel').forEach(function(p){ p.classList.toggle('active', parseInt(p.dataset.ustep,10) === upStep); });
    $all('.dsx-ob-step', $('#dsx-up-steps')).forEach(function(s){
      var step = parseInt(s.dataset.ustep,10);
      s.classList.toggle('done', step < upStep);
      s.classList.toggle('now', step === upStep);
      s.querySelector('.c').textContent = step < upStep ? '✓' : step;
    });
    $('#dsx-up-bar').style.width = Math.round(upStep/7*100) + '%';
    if (upStep === 4) renderPreviewCard();
    if (upStep === 5) renderTechnicalFields();
    if (upStep === 7) dsxRenderReview();
    window.scrollTo({top:0,behavior:'smooth'});
  };

  function chips(container, items, single){
    var el = $(container);
    if(!el) return;
    el.innerHTML = items.map(function(it){ return '<span class="dsx-chip'+(it.on?' on':'')+'">'+esc(it.label)+'</span>'; }).join('');
    $all('.dsx-chip', el).forEach(function(c){
      c.addEventListener('click', function(){
        if (single) { $all('.dsx-chip', el).forEach(function(o){ o.classList.remove('on'); }); c.classList.add('on'); }
        else { c.classList.toggle('on'); }
      });
    });
  }
  function chipsOnValues(container){
    return $all('.dsx-chip.on', $(container)).map(function(c){ return c.textContent.trim(); });
  }
  chips('#dsx-creatortype', [{label:'Graphic Designer'},{label:'Presentation Designer',on:true},{label:'UI/UX Designer'},{label:'Illustrator'},{label:'Motion Designer'},{label:'Video Editor'},{label:'3D Artist'},{label:'Photographer'},{label:'AI Creator'},{label:'Other'}]);
  chips('#dsx-skills', [{label:'Adobe Photoshop'},{label:'Adobe Illustrator'},{label:'After Effects'},{label:'Premiere Pro'},{label:'Figma',on:true},{label:'PowerPoint',on:true},{label:'Canva'},{label:'Blender'},{label:'3D Design'},{label:'Motion Graphics'},{label:'AI Image Generation'},{label:'AI Video Generation'}]);
  chips('#dsx-langs', [{label:'English',on:true},{label:'Hindi'},{label:'Kannada'},{label:'Spanish'},{label:'French'},{label:'German'},{label:'Japanese'}]);
  var DSX_LEGACY_TYPE_MAP = {'PowerPoint Template':'Presentation Templates','Presentation Slides':'Presentation Templates'};
var DSX_TECH_FIELDS = {
'Presentation Templates': [
{key:'slides',label:'Number of slides',ph:'e.g. 30'},
{key:'aspectRatio',label:'Aspect ratio',ph:'e.g. 16:9'},
{key:'editable',label:'Editable',ph:'e.g. Yes, fully editable'},
{key:'software',label:'Software/version',ph:'e.g. PowerPoint 2016+, Keynote, Google Slides'},
{key:'fonts',label:'Fonts included',ph:'e.g. Poppins, Montserrat'},
{key:'icons',label:'Icons included',ph:'e.g. 40 custom icons'},
{key:'images',label:'Images included',ph:'e.g. 20 stock photos'},
{key:'animations',label:'Animations',ph:'e.g. Subtle fade/slide'},
{key:'transitions',label:'Transitions',ph:'e.g. Morph'}
],
'Infographic': [
{key:'dimensions',label:'Dimensions',ph:'e.g. 1080x1920px'},
{key:'orientation',label:'Orientation',ph:'Portrait / Landscape / Square'},
{key:'fileFormat',label:'File format',ph:'PNG, JPG, PDF, AI, PSD, Figma'},
{key:'editable',label:'Editable',ph:'Yes / No'},
{key:'colorMode',label:'Color mode',ph:'RGB / CMYK'},
{key:'fonts',label:'Fonts included',ph:'e.g. Poppins, Montserrat'}
],
'Flyer': [
{key:'dimensions',label:'Dimensions',ph:'e.g. A4, US Letter, Custom'},
{key:'orientation',label:'Orientation',ph:'Portrait / Landscape'},
{key:'useType',label:'Print or Digital',ph:'Print / Digital / Both'},
{key:'bleed',label:'Bleed \u0026 margins included',ph:'Yes / No'},
{key:'colorMode',label:'Color mode',ph:'RGB / CMYK'},
{key:'fileFormat',label:'File format',ph:'PDF, PSD, AI, Canva, PNG'},
{key:'editable',label:'Editable',ph:'Yes / No'},
{key:'fonts',label:'Fonts included',ph:'e.g. Poppins, Montserrat'}
],
'Poster': [
{key:'dimensions',label:'Dimensions',ph:'e.g. 18x24in, A2, A1'},
{key:'orientation',label:'Orientation',ph:'Portrait / Landscape'},
{key:'useType',label:'Print or Digital',ph:'Print / Digital / Both'},
{key:'resolution',label:'Resolution/DPI',ph:'e.g. 300 DPI'},
{key:'colorMode',label:'Color mode',ph:'RGB / CMYK'},
{key:'fileFormat',label:'File format',ph:'PDF, PSD, AI, PNG'},
{key:'editable',label:'Editable',ph:'Yes / No'}
],
'Brochure': [
{key:'foldType',label:'Fold type',ph:'Bi-fold / Tri-fold / Z-fold / None'},
{key:'dimensions',label:'Flat \u0026 folded dimensions',ph:'e.g. 11x8.5in flat, 3.67x8.5in folded'},
{key:'panels',label:'Number of panels',ph:'e.g. 6'},
{key:'useType',label:'Print or Digital',ph:'Print / Digital / Both'},
{key:'colorMode',label:'Color mode',ph:'RGB / CMYK'},
{key:'fileFormat',label:'File format',ph:'PDF, PSD, AI, INDD'},
{key:'editable',label:'Editable',ph:'Yes / No'}
],
'Social Media Design': [
{key:'platform',label:'Platform',ph:'Instagram / Facebook / LinkedIn / X / YouTube thumbnail / Other'},
{key:'dimensions',label:'Dimensions/aspect ratio',ph:'e.g. 1080x1080'},
{key:'format',label:'Format',ph:'Static / Carousel / Story / Reel cover'},
{key:'variations',label:'Number of variations',ph:'e.g. 5 slides (for carousels)'},
{key:'fileFormat',label:'File format',ph:'PNG, JPG, PSD, Canva'},
{key:'editable',label:'Editable',ph:'Yes / No'}
],
'Invitation': [
{key:'dimensions',label:'Dimensions',ph:'e.g. 5x7in'},
{key:'orientation',label:'Orientation',ph:'Portrait / Landscape'},
{key:'useType',label:'Print or Digital',ph:'Print / Digital / Both'},
{key:'theme',label:'Occasion/theme',ph:'e.g. Wedding, Birthday, Corporate'},
{key:'fileFormat',label:'File format',ph:'PDF, PSD, Canva, PNG'},
{key:'editable',label:'Editable',ph:'Yes / No'},
{key:'fonts',label:'Fonts included',ph:'e.g. Poppins, Montserrat'}
],
'Certificate': [
{key:'dimensions',label:'Dimensions',ph:'e.g. A4, Letter'},
{key:'orientation',label:'Orientation',ph:'Portrait / Landscape'},
{key:'placeholders',label:'Editable placeholder fields',ph:'e.g. Name, Date, Signature'},
{key:'fileFormat',label:'File format',ph:'PDF, PSD, AI, DOCX'},
{key:'useType',label:'Print or Digital',ph:'Print / Digital / Both'},
{key:'fonts',label:'Fonts included',ph:'e.g. Poppins, Montserrat'}
],
'Motion Graphics': [
{key:'duration',label:'Duration',ph:'e.g. 15s'},
{key:'resolution',label:'Resolution',ph:'1080p / 4K'},
{key:'frameRate',label:'Frame rate',ph:'e.g. 30fps, 60fps'},
{key:'fileFormat',label:'File format',ph:'MP4, MOV, AE project'},
{key:'sourceIncluded',label:'Source project included',ph:'Yes / No'},
{key:'hasAudio',label:'Has audio',ph:'Yes / No'},
{key:'transparentBg',label:'Transparent background',ph:'Yes / No'}
],
'Video': [
{key:'duration',label:'Duration',ph:'e.g. 30s'},
{key:'resolution',label:'Resolution',ph:'1080p / 4K'},
{key:'frameRate',label:'Frame rate',ph:'e.g. 30fps, 60fps'},
{key:'fileFormat',label:'File format',ph:'MP4, MOV'},
{key:'hasAudio',label:'Has audio',ph:'Yes / No'},
{key:'aspectRatio',label:'Aspect ratio',ph:'e.g. 16:9, 9:16'}
],
'Illustration': [
{key:'dimensions',label:'Dimensions/resolution',ph:'e.g. 4000x4000px'},
{key:'fileFormat',label:'File format',ph:'PNG, SVG, AI, PSD, EPS'},
{key:'vectorRaster',label:'Vector or Raster',ph:'Vector / Raster'},
{key:'editable',label:'Editable',ph:'Yes / No'},
{key:'colorMode',label:'Color mode',ph:'RGB / CMYK'}
],
'Icons': [
{key:'iconCount',label:'Number of icons in pack',ph:'e.g. 50'},
{key:'fileFormat',label:'Format',ph:'SVG, PNG, ICO, Figma'},
{key:'style',label:'Style',ph:'Outline / Filled / Duotone / 3D'},
{key:'sizes',label:'Sizes included',ph:'e.g. 24px, 48px, multiple'},
{key:'editable',label:'Vector/Editable',ph:'Yes / No'}
],
'Background': [
{key:'dimensions',label:'Dimensions/resolution',ph:'e.g. 3840x2160px'},
{key:'orientation',label:'Orientation',ph:'Landscape / Portrait / Square'},
{key:'fileFormat',label:'File format',ph:'JPG, PNG, PSD'},
{key:'seamless',label:'Seamless/tileable',ph:'Yes / No'},
{key:'colorMode',label:'Color mode',ph:'RGB / CMYK'}
],
'Other': [
{key:'fileFormat',label:'File format',ph:'Describe the format'},
{key:'specs',label:'Dimensions/specs',ph:'Describe dimensions or specs'},
{key:'notes',label:'Additional notes',ph:'Anything else buyers should know'}
]
};
function dsxTechFieldsFor(type){
type = DSX_LEGACY_TYPE_MAP[type] || type;
return DSX_TECH_FIELDS[type] || DSX_TECH_FIELDS['Other'];
}
function renderTechnicalFields(values){
var el = $('#dsx-tech-fields'); if(!el) return;
var type = chipsOnValues('#dsx-contenttype')[0] || '';
var fields = dsxTechFieldsFor(type);
values = values || {};
el.innerHTML = fields.map(function(f){
return '<div class="dsx-field"><label>'+esc(f.label)+'</label><input class="dsx-tech-field" data-key="'+esc(f.key)+'" placeholder="'+esc(f.ph)+'" value="'+esc(values[f.key]||'')+'"></div>';
}).join('');
}
chips('#dsx-contenttype', [{label:'Presentation Templates',on:true},{label:'Infographic'},{label:'Flyer'},{label:'Poster'},{label:'Brochure'},{label:'Social Media Design'},{label:'Invitation'},{label:'Certificate'},{label:'Motion Graphics'},{label:'Video'},{label:'Illustration'},{label:'Icons'},{label:'Background'},{label:'Other'}], true);
$all('.dsx-chip', $('#dsx-contenttype')).forEach(function(c){
c.addEventListener('click', function(){ renderCategorySelect(); renderTechnicalFields(); });
});

  /* ══════════════════ REAL BACKEND WIRING (ds/v1 REST API) ══════════════════ */
  var DSX_API    = root.dataset.api;
  var DSX_NONCE  = root.dataset.nonce;
  var dsxUploads = [];
  var dsxCats    = [];
  var dsxEarnData = null;
  var dsxEditingId = null;
  var dsxDesignFile = null, dsxThumbFile = null;

  function esc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c];}); }
  function fmtRs(n){ return '₹'+(+(n||0)).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
  function fmtSize(b){ if(!b) return ''; var u=['B','KB','MB','GB'],i=0; b=+b; while(b>=1024&&i<3){b/=1024;i++;} return b.toFixed(1)+' '+u[i]; }
  var STATUS_MAP = {
    pending:  {label:'Pending Review', badge:'b-amber'},
    published:{label:'Published',      badge:'b-green'},
    rejected: {label:'Rejected',       badge:'b-red'},
    draft:    {label:'Draft',          badge:'b-gray'}
  };

  async function dsxApi(path, opts){
    opts = opts || {};
    var headers = Object.assign({'X-WP-Nonce': DSX_NONCE, 'Accept':'application/json'}, opts.headers||{});
    var res = await fetch(DSX_API.replace(/\/$/,'') + path, Object.assign({}, opts, {headers: headers}));
    var text = await res.text();
    var data;
    try { data = JSON.parse(text); } catch(e){ throw new Error('Server returned an unexpected response (check the site is reachable and REST API is enabled).'); }
    if (!res.ok) throw new Error(data.message || ('API error ' + res.status));
    return data;
  }

  async function loadUploads(){
    try { dsxUploads = await dsxApi('/uploads'); } catch(e){ dsxUploads = []; dsxToast('Could not load your products: '+e.message); }
    renderProducts();
    renderRecentProducts();
    renderProfileProducts();
    renderTopProducts();
    renderDashboardStats();
    renderAnalytics();
  }
  async function loadCategories(){
    try { dsxCats = await dsxApi('/categories'); } catch(e){ dsxCats = []; }
    renderCategorySelect();
  }
  async function loadEarnings(){
    try { dsxEarnData = await dsxApi('/earnings'); } catch(e){ dsxEarnData = {earnings:[], totals:{earnings:0,revenue:0,platform:0}}; }
    renderEarningsTab();
    renderDashboardStats();
renderDashChart();
  }

  var DSX_CATEGORY_ALIASES = { 'presentation templates': ['powerpoint templates','powerpoint template','presentation slides','presentations'] };
function dsxCategoryCandidates(type){
var t = (type||'').toLowerCase().trim();
if (!t) return [];
var list = [t];
if (DSX_CATEGORY_ALIASES[t]) list = list.concat(DSX_CATEGORY_ALIASES[t]);
var extra = [];
list.forEach(function(n){ extra.push(n.charAt(n.length-1)==='s' ? n.slice(0,-1) : n + 's'); });
return list.concat(extra);
}
function renderCategorySelect(){
var sel = $('#dsx-up-category'); if(!sel) return;
var cur = sel.value;
var type = chipsOnValues('#dsx-contenttype')[0] || '';
var candidates = dsxCategoryCandidates(type);
var matched = dsxCats.find(function(c){ return candidates.indexOf((c.name||'').toLowerCase().trim()) > -1; });
var list;
if (matched) {
var children = dsxCats.filter(function(c){ return c.parent === matched.id; });
list = children.length ? children : [matched];
} else {
list = dsxCats;
}
sel.innerHTML = '<option value="">Select category…</option>';
list.forEach(function(c){ var o=document.createElement('option'); o.value=c.name; o.textContent=c.name; sel.appendChild(o); });
var newOpt = document.createElement('option'); newOpt.value='__new'; newOpt.textContent='+ Create New Category'; sel.appendChild(newOpt);
if (cur) sel.value = cur;
sel.onchange = function(){ $('#dsx-up-newcat-wrap').style.display = (sel.value==='__new') ? '' : 'none'; };
}
function dsxUploadCategoryValue(){
    var sel = $('#dsx-up-category');
    if (sel && sel.value === '__new') { var v=($('#dsx-up-newcat').value||'').trim(); return v; }
    return sel ? sel.value : '';
  }

  function catColor(cat){
    var palette = ['#2563eb,#7c3aed','#dc2626,#7c2d12','#0d9488,#155e75','#a16207,#78350f','#be123c,#7c2d12','#b45309,#7c2d12'];
    var h = 0; (cat||'').split('').forEach(function(c){ h = (h + c.charCodeAt(0)) % palette.length; });
    return palette[h];
  }

  function productRow(u){
    var st = STATUS_MAP[u.status] || STATUS_MAP.pending;
    var catLabel = (u.category || 'Uncategorized').toUpperCase();
    var meta = (u.category||'Uncategorized') + (u.contentType ? ' · ' + esc(u.contentType) : '') + ' · Uploaded ' + new Date(u.date).toLocaleDateString('en-IN') +
      (u.history && u.history.length ? ' · Updated ' + new Date(u.history[u.history.length-1]).toLocaleDateString('en-IN') : '');
    var btns = '<button class="dsx-btn sm outline" onclick="dsxEditProduct('+u.id+')">Edit</button>' +
      '<button class="dsx-btn sm outline" onclick="dsxNav(\'analytics\')">Analytics</button>' +
      '<button class="dsx-btn sm outline" onclick="var d=this.closest(\'.dsx-card\').querySelector(\'.dsx-details\');d.style.display=d.style.display===\'none\'?\'\':\'none\';">Details</button>';
    var t = u.technical || {};
    var fieldDefs = dsxTechFieldsFor(u.contentType);
var techRows = fieldDefs.map(function(f){ return t[f.key] ? '<div style="display:flex;justify-content:space-between;padding:.25rem 0;"><span class="dsx-sub" style="margin:0;">'+esc(f.label)+'</span><span>'+esc(t[f.key])+'</span></div>' : ''; }).join('');
    var licensed = u.licensed || [];
    var details = '<div class="dsx-details" style="display:none;margin-top:.7rem;padding-top:.7rem;border-top:1px solid var(--border);font-size:.82rem;">' +
      (techRows ? techRows : '<div class="dsx-sub">No technical details provided.</div>') +
      '<div style="display:flex;justify-content:space-between;padding:.25rem 0;"><span class="dsx-sub" style="margin:0;">Rights confirmed</span><span class="dsx-badge2 '+(u.rightsOwner?'b-green':'b-red')+'">'+(u.rightsOwner?'✓ Confirmed':'Not confirmed')+'</span></div>' +
      '<div style="display:flex;justify-content:space-between;padding:.25rem 0;"><span class="dsx-sub" style="margin:0;">AI content</span><span>'+esc(u.aiDisclosure||'none')+'</span></div>' +
      '<div style="padding:.25rem 0;"><span class="dsx-sub">Licensed assets: </span>'+(licensed.length?esc(licensed.join(', ')):'<span class="dsx-sub">none marked</span>')+'</div>' +
      '</div>';
    return '<div class="dsx-card" style="margin-bottom:.8rem;">' +
      '<div style="display:flex;gap:1rem;">' +
      (u.thumb ? '<img src="'+esc(u.thumb)+'" style="width:64px;height:64px;flex-shrink:0;border-radius:10px;object-fit:cover;">' :
        '<div class="dsx-thumb" style="width:64px;height:64px;flex-shrink:0;background:linear-gradient(135deg,'+catColor(u.category)+');font-size:.55rem;">'+esc(catLabel)+'</div>') +
      '<div style="flex:1;min-width:0;">' +
      '<div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;"><strong>'+esc(u.title)+'</strong><span class="dsx-badge2 '+st.badge+'">● '+st.label+'</span></div>' +
      '<div class="dsx-sub" style="margin:.3rem 0;">'+meta+'</div>' +
      '<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem;">'+btns+' <a href="#" onclick="dsxDeleteProduct('+u.id+',\''+esc(u.title).replace(/'/g,"\\'")+'\');return false;" style="color:var(--red);font-size:.8rem;align-self:center;margin-left:auto;">Delete</a></div>' +
      '</div>' +
      '<div style="text-align:right;flex-shrink:0;font-size:.8rem;color:var(--muted);white-space:nowrap;">DOWNLOADS<br><b style="color:var(--text);">'+(u.downloads||0)+'</b><br><br>EARNINGS<br><b style="color:var(--text);">'+fmtRs(u.earnings)+'</b></div>' +
      '</div>' + details +
      '</div>';
  }
  function renderProducts(){
    var el = $('#dsx-products-list'); if(!el) return;
    el.innerHTML = dsxUploads.length
      ? dsxUploads.map(productRow).join('')
      : '<div class="dsx-card" style="text-align:center;color:var(--muted);">No products yet — click "Upload Product" to publish your first one.</div>';
  }
  window.dsxDeleteProduct = async function(id, title){
    if (!window.confirm('Delete "'+title+'"? This cannot be undone.')) return;
    try {
      await dsxApi('/upload/'+id+'/delete', {method:'POST', headers:{'Content-Type':'application/json'}});
      dsxUploads = dsxUploads.filter(function(u){ return u.id !== id; });
      renderProducts(); renderRecentProducts(); renderProfileProducts(); renderTopProducts(); renderDashboardStats(); renderAnalytics();
      dsxToast('Deleted ✓');
    } catch(e){ dsxToast('Delete failed: '+e.message); }
  };
  window.dsxEditProduct = function(id){
    var u = dsxUploads.find(function(x){ return x.id === id; });
    if (!u) return;
    dsxEditingId = id;
    dsxNav('upload');
    $('#dsx-up-title').value = u.title || '';
    $('#dsx-up-short').value = u.shortDesc || '';
    $('#dsx-up-long').value  = u.longDesc || '';
    $('#dsx-up-tags').value  = (u.tags||[]).join(', ');
    if (u.contentType) {
var wantType = DSX_LEGACY_TYPE_MAP[u.contentType] || u.contentType;
$all('.dsx-chip', $('#dsx-contenttype')).forEach(function(c){ c.classList.toggle('on', c.textContent.trim() === wantType); });
}
renderCategorySelect();
if (u.category) { $('#dsx-up-category').value = u.category; }
renderTechnicalFields(u.technical || {});
    $('#dsx-rights-owner').checked = !!u.rightsOwner;
    $all('input[name="ai"]').forEach(function(r){ r.checked = (r.value === (u.aiDisclosure || 'none')); });
    var licensedSet = u.licensed || [];
    $all('.dsx-licensed').forEach(function(c){ c.checked = licensedSet.indexOf(c.value) > -1; });
    $('#dsx-up-submit').textContent = 'Save Changes';
    upStep = 3; // jump straight to step 3 (product info) since files already exist
    $all('.dsx-up-panel').forEach(function(p){ p.classList.toggle('active', parseInt(p.dataset.ustep,10) === 3); });
    dsxToast('Editing "'+u.title+'" — update the fields, then submit.');
  };

  function renderRecentProducts(){
    var el = $('#dsx-recent-products'); if(!el) return;
    var list = dsxUploads.slice(0,5);
    el.innerHTML = list.length ? list.map(function(u){
      var st = STATUS_MAP[u.status] || STATUS_MAP.pending;
      return '<div style="display:flex;align-items:center;gap:.6rem;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;">' +
        '<div class="dsx-thumb" style="width:32px;height:32px;font-size:.4rem;background:linear-gradient(135deg,'+catColor(u.category)+');flex-shrink:0;">'+esc((u.category||'—').slice(0,4).toUpperCase())+'</div>' +
        '<div style="flex:1;min-width:0;"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(u.title)+'</div><div class="dsx-sub" style="margin:0;">'+(u.downloads||0)+' downloads · '+fmtRs(u.earnings)+'</div></div>' +
        '<span class="dsx-badge2 '+st.badge+'">'+st.label+'</span></div>';
    }).join('') : '<p class="dsx-sub">Nothing uploaded yet.</p>';
  }
  function renderProfileProducts(){
    var el = $('#dsx-profile-products'); if(!el) return;
    var list = dsxUploads.filter(function(u){ return u.status==='published'; }).slice(0,3);
    el.innerHTML = list.map(function(u){
      return '<div class="dsx-card" style="padding:0;overflow:hidden;">' +
        (u.thumb ? '<img src="'+esc(u.thumb)+'" style="width:100%;height:90px;object-fit:cover;display:block;">' : '<div class="dsx-thumb" style="border-radius:0;background:linear-gradient(135deg,'+catColor(u.category)+');">'+esc((u.category||'').toUpperCase())+'</div>') +
        '<div style="padding:.8rem;"><strong style="font-size:.9rem;">'+esc(u.title)+'</strong><div class="dsx-sub" style="margin:.3rem 0;">'+(u.downloads||0)+' downloads</div><span class="dsx-badge2 b-green">● Published</span></div></div>';
    }).join('');
  }
  function renderTopProducts(){
    var el = $('#dsx-top-products'); if(!el) return;
    var ranked = dsxUploads.slice().sort(function(a,b){ return (b.downloads||0)-(a.downloads||0); }).slice(0,5);
    el.innerHTML = ranked.length ? ranked.map(function(u,i){
      return '<div style="display:flex;align-items:center;gap:.8rem;padding:.6rem 0;border-bottom:1px solid var(--border);">' +
        '<span style="width:20px;color:var(--muted);">'+(i+1)+'</span>' +
        '<div class="dsx-thumb" style="width:40px;height:40px;font-size:.45rem;background:linear-gradient(135deg,'+catColor(u.category)+');flex-shrink:0;">'+esc((u.category||'—').slice(0,7).toUpperCase())+'</div>' +
        '<div style="flex:1;"><div>'+esc(u.title)+'</div><div class="dsx-sub" style="margin:0;">'+(u.downloads||0)+' downloads</div></div>' +
        '<strong>'+fmtRs(u.earnings)+'</strong></div>';
    }).join('') : '<p class="dsx-sub">No products yet.</p>';
  }

  function renderDashboardStats(){
    var totals = (dsxEarnData && dsxEarnData.totals) || {earnings:0,revenue:0,platform:0};
    var totalDownloads = dsxUploads.reduce(function(s,u){ return s + (+u.downloads||0); }, 0);
    var now = new Date(), thisMonth = 0, thisMonthDl = 0;
    (dsxEarnData && dsxEarnData.earnings || []).forEach(function(r){
      var d = new Date(r.created_at);
      if (d.getFullYear()===now.getFullYear() && d.getMonth()===now.getMonth()){ thisMonth += (+r.amount||0); thisMonthDl++; }
    });
    var vals = $all('.dsx-stat .val', $('section[data-tab="dashboard"]'));
    if (vals[0]) vals[0].textContent = dsxUploads.length;
    if (vals[1]) vals[1].textContent = totalDownloads.toLocaleString('en-IN');
    if (vals[2]) vals[2].textContent = fmtRs(totals.earnings);
    if (vals[3]) vals[3].textContent = fmtRs(0); // "pending" clearing period not tracked by the backend yet
    if (vals[4]) vals[4].textContent = fmtRs(totals.earnings); // available balance = total earnings (no withdrawals tracked yet)
    if (vals[5]) vals[5].textContent = thisMonthDl.toLocaleString('en-IN') + ' · ' + fmtRs(thisMonth);
  }

  function renderEarningsTab(){
    var data = dsxEarnData || {earnings:[], totals:{earnings:0,revenue:0,platform:0}};
    var vals = $all('.dsx-stat .val', $('section[data-tab="earnings"]'));
    var now = new Date(), lastM = new Date(now.getFullYear(), now.getMonth()-1, 1);
    var thisMonth=0,lastMonth=0;
    data.earnings.forEach(function(r){
      var d=new Date(r.created_at);
      if (d.getFullYear()===now.getFullYear() && d.getMonth()===now.getMonth()) thisMonth += (+r.amount||0);
      if (d.getFullYear()===lastM.getFullYear() && d.getMonth()===lastM.getMonth()) lastMonth += (+r.amount||0);
    });
    if (vals[0]) vals[0].textContent = fmtRs(data.totals.earnings);
    if (vals[1]) vals[1].textContent = fmtRs(thisMonth);
    if (vals[2]) vals[2].textContent = fmtRs(lastMonth);
    if (vals[3]) vals[3].textContent = fmtRs(0);
    if (vals[4]) vals[4].textContent = fmtRs(data.totals.earnings);
    if (vals[5]) vals[5].textContent = fmtRs(0);
    var wrap = $('#dsx-earnings-rows');
    if (wrap) {
      wrap.innerHTML = data.earnings.length ? data.earnings.map(function(r){
        return '<tr><td>'+new Date(r.created_at).toLocaleDateString('en-IN')+'</td><td>'+esc(r.file_title||'—')+'</td><td>Download</td><td><b>'+fmtRs(r.amount)+'</b></td><td><span class="dsx-badge2 b-green">● Paid</span></td></tr>';
      }).join('') : '<tr><td colspan="5" class="dsx-sub" style="text-align:center;padding:1rem;">No downloads yet.</td></tr>';
    }
    var wVal = $('.dsx-stat .val', $('section[data-tab="withdrawals"]'));
    if (wVal) wVal.textContent = fmtRs(data.totals.earnings);
  }

  function renderAnalytics(){
    var totalDl = dsxUploads.reduce(function(s,u){return s+(+u.downloads||0);},0);
    var totalEarn = dsxUploads.reduce(function(s,u){return s+(+u.earnings||0);},0);
    var vals = $all('.dsx-stat .val', $('section[data-tab="analytics"]'));
    if (vals[0]) vals[0].textContent = dsxUploads.length + ' products';
    if (vals[1]) vals[1].textContent = totalDl.toLocaleString('en-IN');
    if (vals[2]) vals[2].textContent = dsxUploads.filter(function(u){return u.status==='published';}).length + ' published';
    if (vals[3]) vals[3].textContent = fmtRs(totalEarn);

    var wrap = $('#dsx-analytics-full');
    if (wrap) {
      wrap.innerHTML = dsxUploads.length ? dsxUploads.map(function(u){
        var st = STATUS_MAP[u.status] || STATUS_MAP.pending;
        return '<tr><td>'+esc(u.title)+'</td><td>'+(esc(u.contentType)||'—')+'</td>' +
          '<td><span class="dsx-badge2 '+st.badge+'">● '+st.label+'</span></td>' +
          '<td>'+(u.downloads||0)+'</td><td>'+fmtRs(u.earnings)+'</td>' +
          '<td><span class="dsx-badge2 '+(u.rightsOwner?'b-green':'b-red')+'">'+(u.rightsOwner?'✓':'—')+'</span></td>' +
          '<td>'+esc(u.aiDisclosure||'none')+'</td></tr>';
      }).join('') : '<tr><td colspan="7" class="dsx-sub" style="text-align:center;padding:1rem;">No products yet.</td></tr>';
    }
  }

  /* ---- Upload wizard: real file inputs + real submit ---- */
  function setupDrop(zoneId, inputId, onFile){
    var zone = $(zoneId), input = $(inputId);
    if (!zone || !input) return;
    zone.addEventListener('click', function(){ input.click(); });
    zone.addEventListener('dragover', function(e){ e.preventDefault(); zone.style.borderColor = 'var(--blue)'; });
    zone.addEventListener('dragleave', function(){ zone.style.borderColor = ''; });
    zone.addEventListener('drop', function(e){ e.preventDefault(); zone.style.borderColor=''; if (e.dataTransfer.files[0]) onFile(e.dataTransfer.files[0]); });
    input.addEventListener('change', function(e){ if (e.target.files[0]) onFile(e.target.files[0]); });
  }
  setupDrop('#dsx-dz-design', '#dsx-file-design', function(f){
    dsxDesignFile = f;
    $('#dsx-filelist').innerHTML = '<div style="border-bottom:1px solid var(--border);padding:.7rem 0;"><div style="display:flex;justify-content:space-between;font-size:.85rem;"><span>📄 '+esc(f.name)+'</span><span class="dsx-sub" style="margin:0;">'+fmtSize(f.size)+'</span></div></div>';
  });
  setupDrop('#dsx-dz-thumb', '#dsx-file-thumb', function(f){
dsxThumbFile = f;
$('#dsx-thumb-info').innerHTML = '<div class="dsx-sub"> '+esc(f.name)+' ('+fmtSize(f.size)+')</div>';
renderPreviewCard();
});
function renderPreviewCard(){
var box = $('#dsx-preview-thumb'); if(!box) return;
if (dsxThumbFile) {
if (box.dataset.blobUrl) URL.revokeObjectURL(box.dataset.blobUrl);
var url = URL.createObjectURL(dsxThumbFile);
box.dataset.blobUrl = url;
box.style.background = 'url("'+url+'") center/cover no-repeat';
} else {
box.style.background = 'linear-gradient(135deg,#2563eb,#7c3aed)';
}
var titleEl = $('#dsx-preview-title'); if (titleEl) titleEl.textContent = ($('#dsx-up-title').value||'').trim() || 'Your product title';
var metaEl = $('#dsx-preview-meta'); if (metaEl) {
var base = metaEl.textContent.split('\u00b7')[0].replace(/\s+$/,'');
var cat = dsxUploadCategoryValue() || chipsOnValues('#dsx-contenttype')[0] || '';
metaEl.textContent = cat ? (base + ' \u00b7 ' + cat) : base;
}
}

  /* ---- Collect every wizard field, used by both Review (step 7) and Submit ---- */
  function dsxCollectWizardData(){
    var tags = ($('#dsx-up-tags').value || '').split(',').map(function(s){return s.trim();}).filter(Boolean);
    return {
      contentType: chipsOnValues('#dsx-contenttype')[0] || '',
      title: $('#dsx-up-title').value.trim(),
      category: dsxUploadCategoryValue(),
      shortDesc: $('#dsx-up-short').value.trim(),
      longDesc: $('#dsx-up-long').value.trim(),
      tags: tags,
      technical: (function(){ var t={}; $all('.dsx-tech-field').forEach(function(inp){ t[inp.dataset.key]=inp.value.trim(); }); return t; })(),
      rightsOwner: $('#dsx-rights-owner').checked,
      aiDisclosure: ($all('input[name="ai"]').find(function(r){return r.checked;}) || {}).value || 'none',
      licensed: $all('.dsx-licensed:checked').map(function(c){ return c.value; })
    };
  }
  function dsxRenderReview(){
    var d = dsxCollectWizardData();
    var fieldDefs = dsxTechFieldsFor(d.contentType);
var techRows = fieldDefs.map(function(f){ return d.technical[f.key] ? '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">'+esc(f.label)+'</span><span>'+esc(d.technical[f.key])+'</span></div>' : ''; }).join('');
    $('#dsx-up-review').innerHTML =
      '<div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.6rem;">Content &amp; files</h3>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Content type</span><span>'+(esc(d.contentType)||'<span style="color:var(--red);">not set</span>')+'</span></div>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Design file</span><span>'+(dsxDesignFile?esc(dsxDesignFile.name):(dsxEditingId?'unchanged':'<span style="color:var(--red);">missing</span>'))+'</span></div>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;"><span class="dsx-sub" style="margin:0;">Thumbnail</span><span>'+(dsxThumbFile?esc(dsxThumbFile.name):(dsxEditingId?'unchanged':'<span style="color:var(--red);">missing</span>'))+'</span></div>' +
      '</div>' +
      '<div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.6rem;">Product info</h3>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Title</span><span>'+(esc(d.title)||'<span style="color:var(--red);">not set</span>')+'</span></div>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Category</span><span>'+(esc(d.category)||'<span style="color:var(--red);">not set</span>')+'</span></div>' +
        '<div style="padding:.35rem 0;border-bottom:1px solid var(--border);"><div class="dsx-sub" style="margin-bottom:.2rem;">Short description</div>'+(esc(d.shortDesc)||'<span class="dsx-sub">—</span>')+'</div>' +
        '<div style="padding:.35rem 0;border-bottom:1px solid var(--border);"><div class="dsx-sub" style="margin-bottom:.2rem;">Full description</div>'+(esc(d.longDesc)||'<span class="dsx-sub">—</span>')+'</div>' +
        '<div style="padding:.35rem 0;"><div class="dsx-sub" style="margin-bottom:.2rem;">Tags</div>'+(d.tags.length?esc(d.tags.join(', ')):'<span class="dsx-sub">—</span>')+'</div>' +
      '</div>' +
      (techRows ? '<div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.6rem;">Technical details</h3>'+techRows+'</div>' : '') +
      '<div class="dsx-card" style="margin-bottom:1rem;"><h3 style="margin-bottom:.6rem;">Rights &amp; license</h3>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">Rights confirmed</span><span class="dsx-badge2 '+(d.rightsOwner?'b-green':'b-red')+'">'+(d.rightsOwner?'✓ Confirmed':'Not confirmed')+'</span></div>' +
        '<div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">AI content</span><span>'+esc(d.aiDisclosure)+'</span></div>' +
        '<div style="padding:.35rem 0;"><div class="dsx-sub" style="margin-bottom:.2rem;">Licensed assets</div>'+(d.licensed.length?esc(d.licensed.join(', ')):'<span class="dsx-sub">None marked</span>')+'</div>' +
      '</div>';
  }

  var dsxUploadIdemKey = null;
  window.dsxSubmitUpload = async function(isDraft){
    var errEl = $('#dsx-up-error');
    errEl.style.display = 'none';
    if (!dsxEditingId && (!dsxDesignFile || !dsxThumbFile)) {
      errEl.textContent = 'Please add both a design file and a thumbnail (in steps 2 and 4) before submitting.';
      errEl.style.display = '';
      return;
    }
    var d = dsxCollectWizardData();
    if (!isDraft && !d.rightsOwner) {
      errEl.textContent = 'You must confirm the rights checkbox in step 6 before submitting for review (draft is OK without it).';
      errEl.style.display = '';
      return;
    }
    var btn = $('#dsx-up-submit');
    var orig = btn.textContent;
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
      var fd = new FormData();
      if (!dsxUploadIdemKey) dsxUploadIdemKey = 'idem_' + Date.now() + '_' + Math.random().toString(36).slice(2);
      fd.append('idempotency_key', dsxUploadIdemKey);
      fd.append('title', d.title);
      fd.append('category', d.category);
      fd.append('shortDesc', d.shortDesc);
      fd.append('longDesc', d.longDesc);
      fd.append('keywords', JSON.stringify(d.tags));
      fd.append('tags', JSON.stringify(d.tags));
      fd.append('status', isDraft ? 'Draft' : 'Pending');
      fd.append('contentType', d.contentType);
      fd.append('technical', JSON.stringify(d.technical));
      fd.append('rightsOwner', d.rightsOwner ? '1' : '');
      fd.append('aiDisclosure', d.aiDisclosure);
      fd.append('licensed', JSON.stringify(d.licensed));
      if (dsxDesignFile) fd.append('design_file', dsxDesignFile, dsxDesignFile.name);
      if (dsxThumbFile)  fd.append('thumbnail', dsxThumbFile, dsxThumbFile.name);

      var path = dsxEditingId ? ('/upload/' + dsxEditingId) : '/upload';
      var res = await fetch(DSX_API.replace(/\/$/,'') + path, {method:'POST', headers:{'X-WP-Nonce': DSX_NONCE}, body: fd});
      var text = await res.text();
      var saved; try { saved = JSON.parse(text); } catch(e){ throw new Error('Server error — check PHP error log.'); }
      if (!res.ok) throw new Error(saved.message || 'Upload failed');

      if (dsxEditingId) {
        var idx = dsxUploads.findIndex(function(u){ return u.id === dsxEditingId; });
        if (idx > -1) dsxUploads[idx] = saved;
      } else {
        dsxUploads.unshift(saved);
      }
      dsxEditingId = null; dsxDesignFile = null; dsxThumbFile = null; dsxUploadIdemKey = null;
      $('#dsx-up-title').value=''; $('#dsx-up-short').value=''; $('#dsx-up-long').value=''; $('#dsx-up-tags').value='';
      var __techEl = $('#dsx-tech-fields'); if (__techEl) __techEl.innerHTML = '';
      $('#dsx-rights-owner').checked = false;
      $all('input[name="ai"]').forEach(function(r){ r.checked = (r.value === 'none'); });
      $all('.dsx-licensed').forEach(function(c){ c.checked = false; });
      $all('.dsx-chip.on', $('#dsx-contenttype')).forEach(function(c){ c.classList.remove('on'); });
      $('#dsx-filelist').innerHTML=''; $('#dsx-thumb-info').innerHTML=''; $('#dsx-up-submit').textContent='Submit for Review';
      await loadCategories();
      renderProducts(); renderRecentProducts(); renderProfileProducts(); renderTopProducts(); renderDashboardStats(); renderAnalytics();
      dsxToast(isDraft ? 'Draft saved ✓' : 'Submitted for review ✓ — check My Products');
      dsxNav('products');
    } catch(err) {
      errEl.textContent = 'Error: ' + err.message;
      errEl.style.display = '';
    } finally {
      btn.disabled = false; btn.textContent = orig;
    }
  };

  var dsxPolicies = [];
async function loadPolicies(){
try { dsxPolicies = await dsxApi('/policies'); } catch(e){ dsxPolicies = []; }
renderPolicies();
}
function renderPolicies(){
var html = dsxPolicies.length ? dsxPolicies.map(function(p){
if (p.url) {
return '<a href="'+esc(p.url)+'" target="_blank" class="dsx-card" style="padding:.8rem 1rem;display:flex;align-items:center;gap:.6rem;justify-content:space-between;">'+esc(p.label)+' <span style="color:var(--blue);font-size:.8rem;">Download ↓</span></a>';
}
return '<span class="dsx-card" style="padding:.8rem 1rem;display:flex;align-items:center;gap:.6rem;justify-content:space-between;opacity:.55;cursor:default;">'+esc(p.label)+' <span style="font-size:.75rem;">Not yet available</span></span>';
}).join('') : '';
var a = $('#dsx-ob-policies'); if (a) a.innerHTML = html;
var b = $('#dsx-portal-policies'); if (b) b.innerHTML = html;
}
var dsxNotifs = [];
async function loadNotifications(){
try { dsxNotifs = await dsxApi('/notifications'); } catch(e){ dsxNotifs = []; }
renderNotifs();
updateUnread();
}
function dsxTimeAgo(dateStr){
if (!dateStr) return '';
var iso = dateStr.indexOf('T') > -1 ? dateStr : dateStr.replace(' ','T') + 'Z';
var then = new Date(iso).getTime();
if (isNaN(then)) then = new Date(dateStr).getTime();
if (isNaN(then)) return dateStr;
var diff = Math.max(0, Date.now() - then);
var mins = Math.floor(diff/60000);
if (mins < 1) return 'Just now';
if (mins < 60) return mins + ' min ago';
var hrs = Math.floor(mins/60);
if (hrs < 24) return hrs + ' hour' + (hrs>1?'s':'') + ' ago';
var days = Math.floor(hrs/24);
if (days < 7) return days + ' day' + (days>1?'s':'') + ' ago';
return new Date(then).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
}
function renderNotifs(){
var list = $('#dsx-notif-list');
if (!dsxNotifs.length) { list.innerHTML = '<div class="dsx-card"><p class="dsx-sub" style="margin:0;">No notifications yet.</p></div>'; return; }
var colorMap = {approved:'b-green', rejected:'b-red', broadcast:'b-amber', system:'b-gray'};
var iconMap = {approved:'✓', rejected:'⚠', broadcast:'📣', system:'ℹ'};
list.innerHTML = dsxNotifs.map(function(n){
var color = colorMap[n.type] || 'b-gray';
var icon = iconMap[n.type] || '';
var timeStr = dsxTimeAgo(n.date);
return '<div class="dsx-card" style="margin-bottom:.7rem;display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;'+(!n.read?'border-color:rgba(59,130,246,.35);':'')+'">' +
'<div style="display:flex;gap:.8rem;"><span class="dsx-badge2 '+color+'" style="border-radius:10px;width:36px;height:36px;justify-content:center;">'+icon+'</span>' +
'<div><strong style="font-size:.92rem;">'+esc(n.title)+'</strong><div class="dsx-sub" style="margin:.25rem 0;">'+esc(n.message)+'</div><div class="dsx-sub" style="margin:0;font-size:.75rem;">'+timeStr+'</div></div></div>' +
'<div style="text-align:right;flex-shrink:0;">' + (!n.read ? '<a href="#" data-id="'+n.id+'" class="dsx-mark" style="color:var(--blue);font-size:.8rem;display:block;margin-bottom:.4rem;">Mark read</a>' : '') + '<a href="#" data-id="'+n.id+'" class="dsx-del" style="color:var(--red);font-size:.8rem;">🗑</a></div>' +
'</div>';
}).join('');
$all('.dsx-mark').forEach(function(a){ a.addEventListener('click', async function(e){ e.preventDefault(); try { await dsxApi('/notifications/'+a.dataset.id+'/read', {method:'POST'}); await loadNotifications(); } catch(err){ dsxToast('Error: '+err.message); } }); });
$all('.dsx-del').forEach(function(a){ a.addEventListener('click', async function(e){ e.preventDefault(); try { await dsxApi('/notifications/'+a.dataset.id, {method:'DELETE'}); await loadNotifications(); } catch(err){ dsxToast('Error: '+err.message); } }); });
}
function updateUnread(){
var n = dsxNotifs.filter(function(x){return !x.read;}).length;
$('#dsx-unread-count').textContent = n;
$('#dsx-notif-badge').textContent = n;
$('#dsx-notif-badge').style.display = n ? '' : 'none';
}
window.dsxMarkAllRead = async function(){
try { await dsxApi('/notifications/read-all', {method:'POST'}); await loadNotifications(); } catch(e){ dsxToast('Error: '+e.message); }
};
var dsxProfile = null;
var DSX_CREATOR_TYPES = ['Graphic Designer','Presentation Designer','UI/UX Designer','Illustrator','Motion Designer','Video Editor','3D Artist','Photographer','AI Creator','Other'];
var DSX_SKILL_OPTIONS = ['PowerPoint','Figma','After Effects','Illustrator','Photoshop','Canva','Motion Graphics','AI Imaging','InDesign','Premiere Pro','Blender','Sketch'];
var DSX_LANG_OPTIONS = ['English','Hindi','Kannada','Tamil','Telugu','Spanish','French','German','Other'];

function dsxRenderProfileChips(container, options, selected){
var el = $(container); if (!el) return;
selected = selected || [];
el.innerHTML = options.map(function(o){ return '<span class="dsx-chip'+(selected.indexOf(o)>-1?' on':'')+'">'+esc(o)+'</span>'; }).join('');
$all('.dsx-chip', el).forEach(function(c){ c.addEventListener('click', function(){ c.classList.toggle('on'); }); });
}

async function loadProfile(){
try { dsxProfile = await dsxApi('/profile'); } catch(e){ dsxProfile = null; }
renderProfileView();
renderDashChart();
}

function dsxProfileCompletionPct(pr){
if (!pr) return 0;
var checks = [
!!(pr.bio && pr.bio.trim()),
!!(pr.creator_types && pr.creator_types.length),
!!(pr.skills && pr.skills.length),
!!(pr.languages && pr.languages.length),
!!(pr.portfolio_url || pr.website || pr.behance || pr.dribbble)
];
var done = checks.filter(Boolean).length;
return Math.round(done / checks.length * 100);
}

function renderProfileView(){
var pr = dsxProfile || {};
var nameEl = $('#dsx-profile-name'); if (nameEl) nameEl.textContent = pr.display_name || nameEl.textContent;
var bioEl = $('#dsx-profile-bio-view'); if (bioEl) bioEl.textContent = (pr.bio && pr.bio.trim()) ? pr.bio : 'No bio yet — click Edit profile to add one.';
var metaParts = [];
if (pr.creator_types && pr.creator_types.length) metaParts.push(pr.creator_types.join(' · '));
if (pr.years_experience) metaParts.push(pr.years_experience + ' years');
if (pr.member_since) { var d = new Date(String(pr.member_since).replace(' ','T')); if (!isNaN(d.getTime())) metaParts.push('Member since ' + d.toLocaleDateString('en-IN',{month:'short',year:'numeric'})); }
var metaEl = $('#dsx-profile-meta-view'); if (metaEl) metaEl.textContent = metaParts.join(' · ') || '—';

var skillsEl = $('#dsx-profile-skills-view');
if (skillsEl) skillsEl.innerHTML = (pr.skills && pr.skills.length) ? pr.skills.map(function(s){ return '<span class="dsx-chip on" style="cursor:default;">'+esc(s)+'</span>'; }).join('') : '<span class="dsx-sub" style="margin:0;">No skills added yet.</span>';

var linksEl = $('#dsx-profile-links-view');
if (linksEl) {
var linkDefs = [['Website','website'],['Portfolio','portfolio_url'],['LinkedIn','linkedin'],['Instagram','instagram'],['Behance','behance'],['Dribbble','dribbble']];
var linkRows = linkDefs.filter(function(d){ return pr[d[1]]; }).map(function(d){
return '<div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);"><span class="dsx-sub" style="margin:0;">'+d[0]+'</span><a href="'+esc(pr[d[1]])+'" target="_blank">'+esc(String(pr[d[1]]).replace(/^https?:\/\//,''))+' ↗</a></div>';
}).join('');
linksEl.innerHTML = linkRows || '<span class="dsx-sub" style="margin:0;">No portfolio links yet — click Edit profile to add some.</span>';
}

var pct = dsxProfileCompletionPct(pr);
var pctEl = $('#dsx-profile-pct'); if (pctEl) pctEl.textContent = pct + '%';
var pctBar = $('#dsx-profile-pct-bar'); if (pctBar) pctBar.style.width = pct + '%';
var checklist = [];
if (!(pr.bio && pr.bio.trim())) checklist.push('Add creator bio');
if (!(pr.skills && pr.skills.length)) checklist.push('Add skills');
if (!(pr.portfolio_url || pr.website)) checklist.push('Add portfolio');
var checklistEl = $('#dsx-profile-checklist');
if (checklistEl) checklistEl.innerHTML = checklist.length ? checklist.map(function(c){ return '<span class="dsx-chip on" style="cursor:pointer;" onclick="dsxProfileToggleEdit()">'+esc(c)+'</span>'; }).join('') : '<span class="dsx-sub" style="margin:0;">Profile complete ✓</span>';
}
function dsxProfileFillEditForm(){
var pr = dsxProfile || {};
$('#dsx-pf-display-name').value = pr.display_name || '';
$('#dsx-pf-bio').value = pr.bio || '';
$('#dsx-pf-years').value = pr.years_experience || '';
$('#dsx-pf-website').value = pr.website || '';
$('#dsx-pf-portfolio').value = pr.portfolio_url || '';
$('#dsx-pf-linkedin').value = pr.linkedin || '';
$('#dsx-pf-instagram').value = pr.instagram || '';
$('#dsx-pf-behance').value = pr.behance || '';
$('#dsx-pf-dribbble').value = pr.dribbble || '';
$('#dsx-pf-other').value = pr.other_social || '';
dsxRenderProfileChips('#dsx-pf-creatortype', DSX_CREATOR_TYPES, pr.creator_types || []);
dsxRenderProfileChips('#dsx-pf-skills', DSX_SKILL_OPTIONS, pr.skills || []);
dsxRenderProfileChips('#dsx-pf-langs', DSX_LANG_OPTIONS, pr.languages || []);
}

window.dsxProfileToggleEdit = function(focusPortfolio){
var isEditing = !$('#dsx-profile-edit').classList.contains('hide');
if (isEditing) {
$('#dsx-profile-edit').classList.add('hide');
$('#dsx-profile-view').classList.remove('hide');
$('#dsx-profile-edit-btn').textContent = '✎ Edit profile';
} else {
dsxProfileFillEditForm();
$('#dsx-profile-view').classList.add('hide');
$('#dsx-profile-edit').classList.remove('hide');
$('#dsx-profile-edit-btn').textContent = '✕ Cancel edit';
if (focusPortfolio === true) {
var card = $('#dsx-pf-portfolio-card');
if (card) card.scrollIntoView({behavior:'smooth', block:'center'});
}
}
};

window.dsxProfileSave = async function(){
var btn = $('#dsx-profile-save-btn');
var errEl = $('#dsx-profile-error');
errEl.style.display = 'none';
var bio = $('#dsx-pf-bio').value.trim();
var creatorTypes = chipsOnValues('#dsx-pf-creatortype');
var skills = chipsOnValues('#dsx-pf-skills');
var langs = chipsOnValues('#dsx-pf-langs');
if (!$('#dsx-pf-display-name').value.trim()) { errEl.textContent='Display Name is required.'; errEl.style.display=''; return; }
if (!bio) { errEl.textContent='Creator Bio is required.'; errEl.style.display=''; return; }
if (!creatorTypes.length) { errEl.textContent='Select at least one Creator Type.'; errEl.style.display=''; return; }
if (!skills.length) { errEl.textContent='Select at least one Skill.'; errEl.style.display=''; return; }
if (!langs.length) { errEl.textContent='Select at least one Language.'; errEl.style.display=''; return; }
var orig = btn.textContent; btn.disabled = true; btn.textContent = 'Saving…';
try {
var payload = {
display_name: $('#dsx-pf-display-name').value.trim(),
bio: bio,
years_experience: $('#dsx-pf-years').value.trim(),
website: $('#dsx-pf-website').value.trim(),
portfolio_url: $('#dsx-pf-portfolio').value.trim(),
linkedin: $('#dsx-pf-linkedin').value.trim(),
instagram: $('#dsx-pf-instagram').value.trim(),
behance: $('#dsx-pf-behance').value.trim(),
dribbble: $('#dsx-pf-dribbble').value.trim(),
other_social: $('#dsx-pf-other').value.trim(),
creator_types: creatorTypes,
skills: skills,
languages: langs
};
dsxProfile = await dsxApi('/profile', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
renderProfileView();
dsxProfileToggleEdit();
dsxToast('Profile saved ✓');
} catch(e) {
errEl.textContent = 'Error: ' + e.message; errEl.style.display = '';
} finally {
btn.disabled = false; btn.textContent = orig;
}
};

  var faqs = [
    {q:'How long does review take?',a:"Most submissions are reviewed within 2–3 business days. You'll be notified as soon as a decision is made.",open:true},
    {q:'Why was my product marked Changes Required?',a:'Usually a licensing or asset issue flagged by the reviewer — check the reviewer note on the product in My Products.'},
    {q:'When do earnings become available?',a:'Earnings clear 14 days after the download event, then move from Pending to Available.'},
    {q:'How are my files protected?',a:'Files are stored with access controls; only you and marketplace buyers (post-purchase) can access them.'},
    {q:'Can I disclose AI-assisted work?',a:'Yes — the upload wizard has a dedicated AI-content disclosure step (No / AI-assisted / AI-generated).'}
  ];
  $('#dsx-faq').innerHTML = faqs.map(function(f,i){
    return '<div style="border-bottom:1px solid var(--border);"><button class="dsx-faq-q" data-i="'+i+'" style="width:100%;text-align:left;background:none;border:0;color:var(--text);padding:.9rem 0;display:flex;justify-content:space-between;font-weight:600;font-size:.9rem;">'+f.q+'<span>'+(f.open?'▲':'▼')+'</span></button><div class="dsx-faq-a" style="display:'+(f.open?'block':'none')+';color:var(--muted);font-size:.85rem;padding-bottom:.9rem;">'+f.a+'</div></div>';
  }).join('');
  $all('.dsx-faq-q').forEach(function(btn){
    btn.addEventListener('click', function(){
      var body = btn.nextElementSibling;
      var open = body.style.display === 'block';
      body.style.display = open ? 'none' : 'block';
      btn.querySelector('span').textContent = open ? '▼' : '▲';
    });
  });

  function loadChartJs(cb){
    if (window.Chart) return cb();
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';
    s.onload = cb;
    document.head.appendChild(s);
  }
  var analyticsChartDone=false;
  var DSX_DASH_RANGES = [
{key:'today', label:'Today'},
{key:'week', label:'Week'},
{key:'month', label:'Month'},
{key:'3m', label:'3M'},
{key:'6m', label:'6M'},
{key:'year', label:'Year'}
];
var dsxDashRange = 'month';
var dsxDashChartInstance = null;

function renderDashRangeChips(){
var el = $('#dsx-dash-range'); if (!el) return;
el.innerHTML = DSX_DASH_RANGES.map(function(r){ return '<span class="dsx-chip'+(r.key===dsxDashRange?' on':'')+'" data-range="'+r.key+'" style="padding:.35rem .8rem;font-size:.78rem;">'+r.label+'</span>'; }).join('');
$all('.dsx-chip', el).forEach(function(c){
c.addEventListener('click', function(){
dsxDashRange = c.dataset.range;
$all('.dsx-chip', el).forEach(function(o){ o.classList.remove('on'); });
c.classList.add('on');
renderDashChart();
});
});
}

function dsxJoinDate(){
var m = (dsxProfile && dsxProfile.member_since) ? dsxProfile.member_since : null;
if (!m) return null;
var d = new Date(String(m).replace(' ','T'));
return isNaN(d.getTime()) ? null : d;
}

function dsxBucketEarnings(range){
var now = new Date();
var join = dsxJoinDate();
var rangeStart, bucketType;
if (range === 'today') { rangeStart = new Date(now.getFullYear(),now.getMonth(),now.getDate()); bucketType='hour'; }
else if (range === 'week') { rangeStart = new Date(now); rangeStart.setDate(rangeStart.getDate()-6); rangeStart.setHours(0,0,0,0); bucketType='day'; }
else if (range === 'month') { rangeStart = new Date(now); rangeStart.setDate(rangeStart.getDate()-29); rangeStart.setHours(0,0,0,0); bucketType='day'; }
else if (range === '3m') { rangeStart = new Date(now); rangeStart.setDate(rangeStart.getDate()-83); rangeStart.setHours(0,0,0,0); bucketType='week'; }
else if (range === '6m') { rangeStart = new Date(now.getFullYear(),now.getMonth()-5,1); bucketType='month'; }
else { rangeStart = new Date(now.getFullYear(),now.getMonth()-11,1); bucketType='month'; }

var clippedToJoin = false;
if (join && join > rangeStart) { rangeStart = join; clippedToJoin = true; }

var rows = (dsxEarnData && dsxEarnData.earnings) ? dsxEarnData.earnings : [];
rows = rows.filter(function(r){
var d = new Date(String(r.created_at).replace(' ','T'));
return !isNaN(d.getTime()) && d >= rangeStart && d <= now;
});

var buckets = [];
if (bucketType === 'hour') {
for (var h=0; h<24; h++){
var bStart = new Date(rangeStart); bStart.setHours(h,0,0,0);
var bEnd = new Date(bStart); bEnd.setHours(h+1,0,0,0);
if (bStart > now) break;
var hr12 = (h%12===0?12:h%12);
buckets.push({label: hr12+(h<12?'am':'pm'), start:bStart, end:bEnd});
}
} else if (bucketType === 'day') {
var cursor = new Date(rangeStart); cursor.setHours(0,0,0,0);
while (cursor <= now) {
var bStart = new Date(cursor);
var bEnd = new Date(bStart); bEnd.setDate(bEnd.getDate()+1);
buckets.push({label: bStart.toLocaleDateString('en-IN',{day:'numeric',month:'short'}), start:bStart, end:bEnd});
cursor.setDate(cursor.getDate()+1);
}
} else if (bucketType === 'week') {
var cursor = new Date(rangeStart); cursor.setHours(0,0,0,0);
while (cursor <= now) {
var bStart = new Date(cursor);
var bEnd = new Date(bStart); bEnd.setDate(bEnd.getDate()+7);
buckets.push({label: bStart.toLocaleDateString('en-IN',{day:'numeric',month:'short'}), start:bStart, end:bEnd});
cursor.setDate(cursor.getDate()+7);
}
} else {
var cursor = new Date(rangeStart.getFullYear(), rangeStart.getMonth(), 1);
while (cursor <= now) {
var bStart = new Date(cursor);
var bEnd = new Date(cursor.getFullYear(), cursor.getMonth()+1, 1);
var opts = {month:'short'}; if (bStart.getFullYear() !== now.getFullYear()) opts.year = '2-digit';
buckets.push({label: bStart.toLocaleDateString('en-IN',opts), start:bStart, end:bEnd});
cursor.setMonth(cursor.getMonth()+1);
}
}

var downloads = buckets.map(function(){ return 0; });
var earnings = buckets.map(function(){ return 0; });
rows.forEach(function(r){
var d = new Date(String(r.created_at).replace(' ','T'));
for (var i=0;i<buckets.length;i++){
if (d >= buckets[i].start && d < buckets[i].end) { downloads[i] += 1; earnings[i] += parseFloat(r.amount || 0); break; }
}
});

return { labels: buckets.map(function(b){return b.label;}), downloads: downloads, earnings: earnings, join: join, clippedToJoin: clippedToJoin, rangeStart: rangeStart };
}
function renderDashChart(){
var canvas = $('#dsx-chart-dash'); if (!canvas) return;
loadChartJs(function(){
var bucketed = dsxBucketEarnings(dsxDashRange);
var subEl = $('#dsx-dash-chart-sub');
if (subEl) {
if (!dsxEarnData) {
subEl.textContent = 'Loading…';
} else if (bucketed.join) {
subEl.textContent = 'Since you joined ' + bucketed.join.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
} else {
subEl.textContent = 'No activity yet';
}
}
var ctx = canvas.getContext('2d');
if (dsxDashChartInstance) { dsxDashChartInstance.destroy(); }
dsxDashChartInstance = new Chart(ctx, {
type:'line',
data:{labels:bucketed.labels,datasets:[
{label:'Downloads',data:bucketed.downloads,borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,.25)',fill:true,tension:.4},
{label:'Earnings (₹)',data:bucketed.earnings,borderColor:'#ef4444',backgroundColor:'rgba(239,68,68,.15)',fill:true,tension:.4}
]},
options:{plugins:{legend:{labels:{color:'#9ba6bc'}}},scales:{x:{ticks:{color:'#9ba6bc'},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'#9ba6bc'},grid:{color:'rgba(255,255,255,.05)'},beginAtZero:true}}}
});
});
}
function renderAnalyticsChart(){
    if (analyticsChartDone) return; analyticsChartDone = true;
    loadChartJs(function(){
      new Chart($('#dsx-chart-analytics').getContext('2d'), {
        type:'line',
        data:{labels:['Feb','Mar','Apr','May','Jun','Jul','Aug'],datasets:[
          {label:'Views',data:[8600,10800,13600,14800,13200,17800,9000],borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,.25)',fill:true,tension:.4},
          {label:'Downloads',data:[1200,1500,1900,2200,2000,2600,1700],borderColor:'#ef4444',backgroundColor:'rgba(239,68,68,.15)',fill:true,tension:.4}
        ]},
        options:{plugins:{legend:{labels:{color:'#9ba6bc'}}},scales:{x:{ticks:{color:'#9ba6bc'},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'#9ba6bc'},grid:{color:'rgba(255,255,255,.05)'}}}}
      });
    });
  }

  // boot: you're only ever shown this script when logged in (logged-out
  // visitors get the login-gate template instead — see PHP above), so skip
  // straight to the real portal. Honor a #hash on the URL so links from
  // other pages land on a specific tab, e.g. "#products".
  renderDashRangeChips();
var startHash = (window.location.hash || '').replace('#','');
  dsxGo('portal');
  if (startHash && $all('.dsx-nav a[data-tab]').some(function(a){return a.dataset.tab===startHash;})) {
    dsxNav(startHash);
  } else {
    renderDashChart();
  }
  // Pull real data from the backend now that the shell is rendered.
  loadUploads();
  loadCategories();
  loadEarnings();
loadProfile();
loadNotifications();
loadPolicies();
})();
</script>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('designstager_portal_ui', 'ds_portal_ui_shortcode');
