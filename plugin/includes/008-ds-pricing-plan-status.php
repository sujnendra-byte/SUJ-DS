<?php
/**
 * Code Snippets export — DS Pricing Plan Status
 *
 * Snippet ID : 694
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-008
 * Modified   : 2026-09-09 02:45:32
 * Load order : 008 of 144
 */
/**
 * Designstager — Dynamic Pricing Page Plan Status
 * - Shows "Current Plan" badge on active plan
 * - Disables "Get Premium" button if already premium
 * - Works on /pricing page only
 */

add_action('wp_footer', function() {
    if (!is_page('pricing')) return;

    $is_logged_in = is_user_logged_in();
    $is_premium   = false;
    $user_name    = '';

    if ($is_logged_in) {
        $user       = wp_get_current_user();
        $user_name  = esc_js($user->display_name);
        $is_premium = in_array('premium_member', (array) $user->roles);
    }

    $is_premium_js  = $is_premium ? 'true' : 'false';
    $is_logged_js   = $is_logged_in ? 'true' : 'false';
    $login_url      = esc_url(home_url('/login?redirect_to=' . urlencode(home_url('/pricing'))));
    ?>

    <style>
    /* ===== CURRENT PLAN BANNER ===== */
    .ds-current-plan-banner {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, #1C1F2E 0%, #2A2D3E 100%);
        border: 1px solid rgba(232,68,42,0.3);
        border-radius: 12px;
        padding: 14px 24px;
        margin: 0 auto 32px;
        max-width: 700px;
        font-size: 14px;
        font-weight: 500;
        color: #ffffff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    .ds-current-plan-banner .ds-cp-icon {
        width: 32px; height: 32px; min-width: 32px;
        background: #E8442A;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
    }
    .ds-current-plan-banner strong { color: #E8442A; }
    .ds-current-plan-banner a {
        color: #94A3B8;
        font-size: 12px;
        text-decoration: underline;
        margin-left: 8px;
    }
    .ds-current-plan-banner a:hover { color: #fff; }

    /* ===== CURRENT PLAN BADGE on card ===== */
    .ds-plan-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 20px;
        margin-bottom: 12px;
        letter-spacing: 0.03em;
    }
    .ds-plan-badge--active {
        background: #ECFDF5;
        color: #059669;
        border: 1px solid rgba(5,150,105,0.25);
    }
    .ds-plan-badge--inactive {
        background: #F8FAFC;
        color: #94A3B8;
        border: 1px solid #E2E8F0;
    }

    /* ===== DISABLED BUTTON ===== */
    .ds-btn-disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        filter: grayscale(30%) !important;
    }

    /* ===== GET STARTED FREE — if not logged in ===== */
    .ds-btn-login-required {
        position: relative;
    }
    </style>

    <script>
    (function() {
        var isPremium  = <?php echo $is_premium_js; ?>;
        var isLoggedIn = <?php echo $is_logged_js; ?>;
        var userName   = '<?php echo $user_name; ?>';
        var loginUrl   = '<?php echo $login_url; ?>';
        var logoutUrl  = '<?php echo esc_url(wp_logout_url(home_url('/pricing'))); ?>';

        // Wait for DOM
        document.addEventListener('DOMContentLoaded', function() {
            initPricingPage();
        });

        // Fallback if DOM already loaded
        if (document.readyState !== 'loading') {
            initPricingPage();
        }

        function initPricingPage() {

            // ── Find all buttons on pricing page ──
            var allBtns = document.querySelectorAll('a, button');
            var freeBtn    = null;
            var premiumBtn = null;

            allBtns.forEach(function(btn) {
                var txt = (btn.textContent || '').trim().toLowerCase();
                if (txt.indexOf('get started free') !== -1 || txt.indexOf('get started') !== -1) {
                    freeBtn = btn;
                }
                if (txt.indexOf('get premium') !== -1) {
                    premiumBtn = btn;
                }
            });

            // ── Banner removed by user ──
// (Banner code removed - user requested removal)
            // ── Handle FREE button ──
            if (freeBtn) {
                if (!isLoggedIn) {
                    // Not logged in — redirect to login
                    freeBtn.href = loginUrl;
                } else {
                    // Already logged in — show current plan badge
                    addBadgeNear(freeBtn, !isPremium ? 'current' : 'notcurrent', !isPremium ? '✓ Your Current Plan' : 'Free Plan');
                    if (!isPremium) {
                        // On free plan — disable free button
                        freeBtn.classList.add('ds-btn-disabled');
                        freeBtn.setAttribute('title', 'This is your current plan');
                    }
                }
            }

            // ── Handle PREMIUM button ──
            if (premiumBtn) {
                if (!isLoggedIn) {
                    // Not logged in — redirect to login first
                    var originalHref = premiumBtn.href;
                    premiumBtn.href = loginUrl;
                } else if (isPremium) {
                    // Already premium — disable button and show badge
                    premiumBtn.classList.add('ds-btn-disabled');
                    premiumBtn.textContent = '✓ Current Plan';
                    premiumBtn.setAttribute('title', 'You are already on Premium');
                    addBadgeNear(premiumBtn, 'current', '⭐ Your Current Plan');
                } else {
                    // Free user — keep button active, add "upgrade" badge
                    addBadgeNear(premiumBtn, 'notcurrent', 'Available to upgrade');
                }
            }
        }

        function addBadgeNear(btn, type, label) {
            // Don't add duplicate
            if (btn.parentNode.querySelector('.ds-plan-badge')) return;

            var badge = document.createElement('div');
            badge.className = 'ds-plan-badge ' + (type === 'current' ? 'ds-plan-badge--active' : 'ds-plan-badge--inactive');
            badge.textContent = label;
            badge.style.cssText = 'display:block;text-align:center;margin-top:12px;';
            btn.parentNode.insertBefore(badge, btn.nextSibling);
        }

        function findPricingSection() {
            // Try to find the section containing the pricing cards
            var headings = document.querySelectorAll('h1,h2,h3,h4');
            for (var i = 0; i < headings.length; i++) {
                var txt = (headings[i].textContent || '').toLowerCase();
                if (txt.indexOf('standard') !== -1 || txt.indexOf('premium') !== -1 || txt.indexOf('pricing') !== -1) {
                    return headings[i].closest('section, .elementor-section, .e-con, .wp-block-group') || headings[i].parentNode;
                }
            }
            return null;
        }
    })();
    </script>
    <?php
}, 20);
