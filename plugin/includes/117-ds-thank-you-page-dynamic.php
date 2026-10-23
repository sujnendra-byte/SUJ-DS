<?php
/**
 * Code Snippets export — DS Thank You Page (Dynamic)
 *
 * Snippet ID : 798
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-112
 * Modified   : 2026-09-09 02:44:27
 * Load order : 117 of 144
 */
/**
 * Snippet Name: DS Thank You Page (Dynamic)
 * Run: Everywhere
 * Overrides the /thank-you page with a dynamic payment-confirmation
 * screen: shows a loading state, verifies the payment via
 * /ds/v1/verify-payment, then shows success (with credits/plan) or
 * failure. Matches the site's dark theme.
 */
add_action('template_redirect', function () {
    if (!function_exists('is_page') || !is_page('thank-you')) return;

    $nonce = wp_create_nonce('wp_rest');
    $rest  = trailingslashit(rest_url('ds/v1'));

    get_header();
    ?>
    <style>
        #ds-ty-wrap{width:100%;flex:1 1 auto;min-height:70vh;display:flex;align-items:center;justify-content:center;padding:60px 16px;background:#0c0f22;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;box-sizing:border-box;}
        #ds-ty-card{max-width:480px;width:100%;background:#161b30;border:1.5px solid rgba(255,255,255,.1);border-radius:24px;padding:44px 36px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35);}
        .ds-ty-icon{width:76px;height:76px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 22px;font-size:34px;}
        #ds-ty-loading .ds-ty-icon{background:rgba(99,102,241,.15);}
        #ds-ty-success .ds-ty-icon{background:linear-gradient(135deg,#6366f1,#8b5cf6);}
        #ds-ty-fail .ds-ty-icon{background:rgba(239,68,68,.15);}
        .ds-spinner{width:34px;height:34px;border-radius:50%;border:3px solid rgba(99,102,241,.25);border-top-color:#818cf8;animation:ds-spin .8s linear infinite;}
        @keyframes ds-spin{to{transform:rotate(360deg);}}
        #ds-ty-wrap h1{font-size:24px;font-weight:900;color:#fff;margin:0 0 10px;}
        #ds-ty-wrap p{font-size:14px;color:rgba(255,255,255,.6);line-height:1.6;margin:0 0 6px;}
        .ds-ty-note{font-size:12.5px;color:rgba(255,255,255,.4);margin-top:14px;}
        .ds-ty-pill{display:inline-flex;align-items:center;gap:8px;background:rgba(99,102,241,.12);border:1.5px solid rgba(99,102,241,.25);border-radius:100px;padding:10px 22px;margin:18px 0 6px;font-weight:800;color:#a5b4fc;font-size:15px;}
        .ds-ty-stats{display:flex;gap:12px;margin:24px 0 8px;}
        .ds-ty-stat{flex:1;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:14px 8px;}
        .ds-ty-stat b{display:block;font-size:19px;color:#fff;font-weight:900;}
        .ds-ty-stat span{font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.05em;}
        .ds-ty-btn{display:inline-block;margin-top:20px;padding:13px 30px;border-radius:100px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:800;font-size:14px;text-decoration:none;border:none;cursor:pointer;font-family:inherit;}
        .ds-ty-btn.secondary{background:rgba(255,255,255,.08);color:rgba(255,255,255,.75);margin-left:8px;}
        .ds-ty-payid{font-size:11.5px;color:rgba(255,255,255,.35);margin-top:16px;word-break:break-all;}
    </style>

    <div id="ds-ty-wrap">
      <div id="ds-ty-card">

        <div id="ds-ty-loading">
          <div class="ds-ty-icon"><div class="ds-spinner"></div></div>
          <h1>Confirming your payment...</h1>
          <p>Hang tight, this usually takes just a few seconds.</p>
          <p class="ds-ty-note">UPI payments can take a minute or two to confirm on the bank’s side — please don’t close this page.</p>
        </div>

        <div id="ds-ty-success" style="display:none;">
          <div class="ds-ty-icon">🎉</div>
          <h1>Payment Successful!</h1>
          <p>Thank you for subscribing to <strong>Designstager AI</strong>.</p>
          <div class="ds-ty-pill" id="ds-ty-plan-pill">⭐ Plan Active</div>
          <div class="ds-ty-stats">
            <div class="ds-ty-stat"><b id="ds-ty-credits">-</b><span>Credits</span></div>
            <div class="ds-ty-stat"><b id="ds-ty-plan">-</b><span>Plan</span></div>
          </div>
          <div>
            <a href="<?php echo esc_url(home_url('/ai-studio/')); ?>" class="ds-ty-btn">Go to AI Studio</a>
          </div>
        </div>

        <div id="ds-ty-fail" style="display:none;">
          <div class="ds-ty-icon">⚠</div>
          <h1>We Couldn’t Confirm This Payment</h1>
          <p id="ds-ty-fail-msg">Your payment may still be processing, or verification failed.</p>
          <p class="ds-ty-note">If money was deducted, keep this payment ID handy and contact support if it doesn’t resolve shortly.</p>
          <div class="ds-ty-payid" id="ds-ty-payid"></div>
          <div>
            <button class="ds-ty-btn" onclick="dsTyRetry()">Try Again</button>
            <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="ds-ty-btn secondary">Back to Pricing</a>
          </div>
        </div>

        <div id="ds-ty-empty" style="display:none;">
          <div class="ds-ty-icon">💳</div>
          <h1>Nothing to Confirm</h1>
          <p>No payment details were found for this page.</p>
          <div>
            <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="ds-ty-btn">View Plans</a>
          </div>
        </div>

      </div>
    </div>

    <script>
    (function(){
      var REST = '<?php echo esc_js($rest); ?>';
      var NONCE = '<?php echo esc_js($nonce); ?>';
      var qs = new URLSearchParams(window.location.search);
      var attempts = 0;

      function show(id) {
        ['ds-ty-loading','ds-ty-success','ds-ty-fail','ds-ty-empty'].forEach(function(x){
          document.getElementById(x).style.display = (x === id) ? '' : 'none';
        });
      }

      window.dsTyRetry = function() {
        attempts = 0;
        show('ds-ty-loading');
        verify();
      };

      function verify() {
        var payId = qs.get('payment_id');
        if (!payId) { show('ds-ty-empty'); return; }

        var body = {
          type: qs.get('type') || 'subscription',
          plan: qs.get('plan') || '',
          credits: qs.get('credits') || 0,
          razorpay_payment_id: payId,
          razorpay_subscription_id: qs.get('subscription_id') || '',
          razorpay_order_id: qs.get('order_id') || '',
          razorpay_signature: qs.get('signature') || ''
        };

        fetch(REST + 'verify-payment', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-WP-Nonce':NONCE},
          body: JSON.stringify(body)
        })
        .then(function(r){ return r.json(); })
        .then(function(v) {
          if (v.success) {
            document.getElementById('ds-ty-credits').textContent = (v.credits_balance !== undefined ? v.credits_balance : '-');
            document.getElementById('ds-ty-plan').textContent = v.plan_label || (v.plan || '-');
            document.getElementById('ds-ty-plan-pill').textContent = '⭐ ' + (v.plan_label || 'Plan') + ' Active';
            show('ds-ty-success');
          } else {
            attempts++;
            if (attempts < 3) {
              setTimeout(verify, 4000);
            } else {
              document.getElementById('ds-ty-fail-msg').textContent = (v.message || 'Payment verification failed. Please contact support if you were charged.');
              document.getElementById('ds-ty-payid').textContent = 'Payment ID: ' + payId;
              show('ds-ty-fail');
            }
          }
        })
        .catch(function() {
          attempts++;
          if (attempts < 3) { setTimeout(verify, 4000); }
          else {
            document.getElementById('ds-ty-fail-msg').textContent = 'Network error while confirming your payment.';
            document.getElementById('ds-ty-payid').textContent = 'Payment ID: ' + payId;
            show('ds-ty-fail');
          }
        });
      }

      verify();
    })();
    </script>
    <?php
    get_footer();
    exit;
}, 100);
