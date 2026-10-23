<?php
/**
 * Code Snippets export — DS Google Ads Conversion - Subscription Purchase
 *
 * Snippet ID : 860
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : designstager, ds-order-149
 * Modified   : 2026-09-20 03:51:56
 * Load order : 149 of 149
 */
/**
 * DS Google Ads Conversion - Subscription Purchase (added 2026-09-20)
 * Fires the Google Ads 'Subscription Purchase' conversion on /thank-you
 * ONLY after DS Thank You Page verifies the Razorpay payment (success box shown).
 * Value = plan INR price, transaction_id = Razorpay payment id (no double count).
 * Uses the Google tag AW-18302135400 already on the site. Deactivate to undo.
 */
add_action('wp_footer', function () {
    if (!function_exists('is_page') || !is_page('thank-you')) return;
    ?>
<script>
(function(){
  var SEND_TO = 'AW-18302135400/1BrVCJjQmP4cEOjYkZdE';
  var PRICES  = { premium: 249, pro: 450, business: 650 };
  var qs = new URLSearchParams(window.location.search);
  if ((qs.get('type') || 'subscription') !== 'subscription') return;
  var pid = qs.get('payment_id'); if (!pid) return;
  var KEY = 'ds_gads_conv_' + pid, done = false;
  function fire(){
    if (done) return; done = true;
    try { if (localStorage.getItem(KEY)) return; } catch(e){}
    var plan = (qs.get('plan') || '').toLowerCase();
    var val = PRICES[plan] || 1;
    window.dataLayer = window.dataLayer || [];
    if (typeof window.gtag !== 'function') { window.gtag = function(){ window.dataLayer.push(arguments); }; }
    // Load the Google tag here if Site Kit did not print it for this visitor
    var TAG = 'AW-18302135400';
    if (!window.dsGadsTagLoaded && !document.getElementById('google_gtagjs-js')) {
      window.dsGadsTagLoaded = true;
      var gt = document.createElement('scr' + 'ipt'); gt.async = true;
      gt.src = 'https://www.' + 'googletagmanager.com/gtag/js' + String.fromCharCode(63) + 'id' + String.fromCharCode(61) + TAG;
      document.getElementsByTagName('head')[0].appendChild(gt);
      window.gtag('js', new Date());
      window.gtag('config', TAG);
    }
    window.gtag('event', 'conversion', { send_to: SEND_TO, value: val, currency: 'INR', transaction_id: pid });
    try { localStorage.setItem(KEY, '1'); } catch(e){}
    window.dsGadsConvFired = { plan: plan, value: val, transaction_id: pid };
  }
  function ok(){
    var el = document.getElementById('ds-ty-success');
    return !!(el && el.style.display !== 'none' && el.offsetParent !== null);
  }
  if (ok()) { fire(); return; }
  var obs = new MutationObserver(function(){ if (ok()) { obs.disconnect(); fire(); } });
  obs.observe(document.body, { attributes: true, subtree: true, attributeFilter: ['style'] });
})();
</script>
    <?php
}, 99);
