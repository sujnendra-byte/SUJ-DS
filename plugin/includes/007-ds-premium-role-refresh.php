<?php
/**
 * Code Snippets export — DS Premium Role Refresh
 *
 * Snippet ID : 693
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-007
 * Modified   : 2026-09-09 02:44:46
 * Load order : 007 of 144
 */
/**
 * Designstager — Premium Role Refresh on Marketplace
 * Polls every 3 seconds after payment and auto-reloads when premium role is detected
 * Only runs on /marketplace page
 */

// ✅ AJAX handler — returns current user role
add_action( 'wp_ajax_ds_check_premium_role', function() {
    $user = wp_get_current_user();
    $is_premium = in_array( 'premium_member', (array) $user->roles );
    wp_send_json( array( 'premium' => $is_premium ) );
} );

// ✅ Inject polling script only on /marketplace page
add_action( 'wp_footer', function() {
    if ( ! is_page( 'marketplace' ) ) return;
    if ( ! is_user_logged_in() ) return;

    // Only run if URL has ?upgraded=1 (Lemon Squeezy redirect parameter)
    // OR if user is not yet premium (just paid and landed here)
    $user = wp_get_current_user();
    $is_premium = in_array( 'premium_member', (array) $user->roles );
    if ( $is_premium ) return; // Already premium, no need to poll
    ?>
    <script>
    (function() {
        // Only start polling if URL has ?order or ?subscription or ?upgraded param
        // OR simply if user is logged in but not premium (just arrived after payment)
        var urlParams = new URLSearchParams(window.location.search);
        var justPaid  = urlParams.has('order_id') 
                     || urlParams.has('subscription_id') 
                     || urlParams.has('upgraded')
                     || document.referrer.indexOf('lemonsqueezy.com') !== -1
                     || document.referrer.indexOf('lemon.squeezy.com') !== -1;

        if ( ! justPaid ) return;

        var ajaxUrl  = '<?php echo admin_url("admin-ajax.php"); ?>';
        var attempts = 0;
        var maxTries = 20; // Try for 60 seconds max (20 x 3s)

        // Show a small notice to the user
        var notice = document.createElement('div');
        notice.id  = 'ds-premium-notice';
        notice.innerHTML = '🎉 Payment confirmed! Activating your Premium access...';
        notice.style.cssText = [
            'position:fixed',
            'bottom:24px',
            'left:50%',
            'transform:translateX(-50%)',
            'background:#1C1F2E',
            'color:#fff',
            'padding:14px 24px',
            'border-radius:12px',
            'font-size:14px',
            'font-weight:600',
            'z-index:9999999',
            'box-shadow:0 8px 32px rgba(0,0,0,0.3)',
            'border:1px solid rgba(232,68,42,0.4)',
            'display:flex',
            'align-items:center',
            'gap:10px',
            'white-space:nowrap'
        ].join(';');
        document.body.appendChild(notice);

        // Spinner
        var spinner = document.createElement('span');
        spinner.style.cssText = 'width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-top-color:#E8442A;border-radius:50%;display:inline-block;animation:dsSpin 0.7s linear infinite;';
        notice.appendChild(spinner);

        var style = document.createElement('style');
        style.textContent = '@keyframes dsSpin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);

        function checkRole() {
            attempts++;

            fetch(ajaxUrl + '?action=ds_check_premium_role', {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.premium) {
                    // ✅ Role updated — reload the page
                    notice.innerHTML = '✅ Premium activated! Loading your designs...';
                    notice.style.borderColor = 'rgba(16,185,129,0.5)';
                    setTimeout(function() {
                        window.location.href = window.location.pathname; // Reload without params
                    }, 1200);
                } else if (attempts < maxTries) {
                    setTimeout(checkRole, 3000); // Try again in 3 seconds
                } else {
                    // Timed out — show manual refresh message
                    notice.innerHTML = '⚠️ Taking longer than usual. <a href="" style="color:#E8442A;margin-left:6px;">Click to refresh</a>';
                }
            })
            .catch(function() {
                if (attempts < maxTries) setTimeout(checkRole, 3000);
            });
        }

        // Start checking after 4 seconds (give webhook time to fire)
        setTimeout(checkRole, 4000);
    })();
    </script>
    <?php
}, 99 );
