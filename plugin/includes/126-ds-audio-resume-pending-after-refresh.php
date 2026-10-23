<?php
/**
 * Code Snippets export — DS Audio - Resume Pending After Refresh
 *
 * Snippet ID : 806
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-120
 * Modified   : 2026-09-09 02:40:33
 * Load order : 126 of 144
 */
/**
 * Resumes audio generations that were left stuck in "pending" status
 * because the user refreshed/left the page before the client-side poll
 * (poll-music / poll-speech) ever got a chance to fetch the finished
 * result from WaveSpeed. Those items had prediction_id + status=pending
 * saved to ds_ai_audio_generations, but nothing checked on them again
 * afterward, so they stayed pending forever with no url - unplayable.
 *
 * Purely additive: a new REST route that reuses the existing
 * ds_audio_poll_common() function (already defined and in production
 * use by the DS AI Audio REST snippet's /poll-music and /poll-speech
 * routes) to catch up any pending items for the current user, plus a
 * small footer script (only on the audio pages) that calls it on load
 * and tells the user if anything finished while they were away.
 * No existing snippet is modified.
 */

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/resume-pending-audio', array(
        'methods'             => 'GET',
        'permission_callback' => function () { return is_user_logged_in(); },
        'callback'            => function () {
            $uid = get_current_user_id();
            $checked = 0;

            if (function_exists('ds_audio_poll_common')) {
                $gens = get_user_meta($uid, 'ds_ai_audio_generations', true);
                if (!is_array($gens)) $gens = array();
                $cutoff = time() - (48 * HOUR_IN_SECONDS);

                foreach ($gens as $g) {
                    if ($checked >= 10) break;
                    if (!is_array($g) || ($g['status'] ?? '') !== 'pending') continue;
                    $pid = $g['prediction_id'] ?? '';
                    if (empty($pid)) continue;
                    $ts = !empty($g['date']) ? strtotime($g['date']) : 0;
                    if ($ts && $ts < $cutoff) continue; // don't keep re-polling abandoned jobs forever

                    $fake = new WP_REST_Request('GET', '/ds/v1/resume-pending-audio');
                    $fake->set_param('prediction_id', $pid);
                    $fake->set_param('label', $g['label'] ?? '');
                    ds_audio_poll_common($fake, $g['kind'] ?? 'music');
                    $checked++;
                }
            }

            return rest_ensure_response(array('success' => true, 'checked' => $checked));
        },
    ));
});

add_action('wp_footer', function () {
    if (!is_user_logged_in()) return;
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (strpos($uri, 'ai-audio') === false && strpos($uri, 'my-generated-images') === false) return;
    ?>
    <script>
    (function(){
        var nonce = (window.wpApiSettings && wpApiSettings.nonce) ? wpApiSettings.nonce : (window.DMC_NONCE || '');
        fetch('/wp-json/ds/v1/resume-pending-audio', {
            headers: nonce ? {'X-WP-Nonce': nonce} : {},
            credentials: 'include'
        }).then(function(r){ return r.json(); }).then(function(d){
            if (!d || !d.checked) return;
            var bar = document.createElement('div');
            bar.textContent = (d.checked === 1 ? 'An audio file' : d.checked + ' audio files') + ' finished generating while you were away. ';
            bar.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:999999;background:#1f2937;color:#fff;padding:12px 16px;border-radius:8px;font:14px/1.4 -apple-system,sans-serif;box-shadow:0 4px 16px rgba(0,0,0,.3);max-width:320px;';
            var btn = document.createElement('a');
            btn.textContent = 'Refresh';
            btn.href = '#';
            btn.style.cssText = 'color:#93c5fd;font-weight:600;text-decoration:underline;cursor:pointer;margin-left:4px;';
            btn.addEventListener('click', function(e){ e.preventDefault(); location.reload(); });
            bar.appendChild(btn);
            document.body.appendChild(bar);
            setTimeout(function(){ if (bar.parentNode) bar.parentNode.removeChild(bar); }, 20000);
        }).catch(function(){});
    })();
    </script>
    <?php
}, 99);
