<?php
/**
 * Code Snippets export — DS Free Image Limit - Upgrade Popup [CLONE - light theme colors]
 *
 * Snippet ID : 809
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-123
 * Modified   : 2026-09-09 02:43:20
 * Load order : 129 of 144
 */
/**
 * DS Free Image Limit - Upgrade Popup [light theme colors]
 *
 * Additive, standalone. Does NOT modify any existing snippet.
 *
 * When the free-tier image generation gate ("DS Free Tier Guard - Daily
 * Budget + Rate Limit [CLONE - image only]", snippet 668) blocks a
 * /generate-image request with HTTP 429 (either the per-user cooldown/gap,
 * or the 2/day cap), this shows a popup with the exact wait/limit message
 * plus an upgrade-to-premium pitch and a button to /pricing.
 *
 * Implementation: wraps window.fetch to observe (never modify) responses
 * from the generate-image endpoint - the existing page JS (snippet 579)
 * still runs its own handling untouched; this just adds the popup on top.
 *
 * Color palette matches the site's "Submit to Marketplace" premium-feature
 * modal (cream card, navy heading, amber highlight box, solid orange CTA).
 */
add_action('wp_head', function () {
    ?>
    <script>
    (function(){
      if (window.__dsUpgradePopupInstalled) return;
      window.__dsUpgradePopupInstalled = true;

      var origFetch = window.fetch;
      window.fetch = function(input, init){
        var url = (typeof input === 'string') ? input : (input && input.url) || '';
        var isGenImage = url.indexOf('/generate-image') !== -1;
        var p = origFetch.apply(this, arguments);
        if (!isGenImage) return p;

        p.then(function(res){
          if (res.status !== 429) return;
          res.clone().json().then(function(j){
            if (j && j.error && typeof j.message === 'string') {
              dsShowUpgradePopup(j.message);
            }
          }).catch(function(){});
        }).catch(function(){});

        return p;
      };

      function dsShowUpgradePopup(message){
        if (document.getElementById('ds-upgrade-popup')) return;

        var overlay = document.createElement('div');
        overlay.id = 'ds-upgrade-popup';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;';

        var card = document.createElement('div');
        card.style.cssText = 'position:relative;max-width:380px;width:100%;background:#FBF6EC;border-radius:22px;padding:28px 24px 24px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.35);';

        var closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label','Close');
        closeBtn.style.cssText = 'position:absolute;top:10px;right:14px;background:none;border:none;color:#9CA3AF;font-size:22px;line-height:1;cursor:pointer;padding:4px 8px;';
        closeBtn.onclick = function(){ overlay.remove(); };

        var lock = document.createElement('div');
        lock.textContent = '\uD83D\uDD12';
        lock.style.cssText = 'font-size:26px;margin-bottom:10px;';

        var msg = document.createElement('p');
        msg.textContent = message;
        msg.style.cssText = 'margin:0 0 14px;font-size:15px;font-weight:800;color:#1E2749;line-height:1.4;';

        var pitchBox = document.createElement('div');
        pitchBox.style.cssText = 'background:#FDF1DE;border:1px solid #F2C88B;border-radius:12px;padding:14px 16px;margin:0 0 20px;text-align:left;';

        var pitch = document.createElement('p');
        pitch.style.cssText = 'margin:0;font-size:13px;color:#92400E;line-height:1.5;';
        pitch.innerHTML = 'For better quality images and to try our best models, upgrade to <strong style="color:#7C2D12;">Premium</strong>.';
        pitchBox.appendChild(pitch);

        var upgradeBtn = document.createElement('a');
        upgradeBtn.href = '/pricing';
        upgradeBtn.textContent = 'Upgrade to Premium';
        upgradeBtn.style.cssText = 'display:block;background:#E08E0B;color:#FFFFFF;padding:14px 28px;border-radius:12px;font-size:14px;font-weight:800;text-decoration:none;';

        card.appendChild(closeBtn);
        card.appendChild(lock);
        card.appendChild(msg);
        card.appendChild(pitchBox);
        card.appendChild(upgradeBtn);
        overlay.appendChild(card);

        overlay.addEventListener('click', function(e){
          if (e.target === overlay) overlay.remove();
        });

        document.body.appendChild(overlay);
      }
    })();
    </script>
    <?php
}, 1);
