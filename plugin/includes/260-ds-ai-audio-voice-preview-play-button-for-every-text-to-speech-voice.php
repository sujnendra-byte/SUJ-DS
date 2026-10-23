<?php
/**
 * Code Snippets export — DS AI Audio - voice preview (play button for every Text to Speech voice)
 *
 * Snippet ID : 1058 (staging)
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ai-audio, voice-preview
 * Modified   : 2026-10-06 11:32:00
 * Load order : after 103 / 104 / 106 (DS AI Audio); before 258
 */
/**
 * DS AI Audio - voice preview (play button for every Text to Speech voice)
 *
 * New snippet. Does not edit "DS AI Audio" or its REST / API snippets.
 *  - The "Voice" box on the AI Audio page becomes a list with a play button in front of every voice (and one on
 *    the closed box for the selected voice). Play = hear the voice, click the name = select it. The original
 *    <select id="dsaaVoice"> stays in the page (hidden) and keeps the chosen value, so Generate Speech works
 *    exactly as before.
 *  - The preview is a fixed sample sentence, never the user's own text, and costs the user no credits.
 *  - Each voice's sample is made ONCE with the same speech model (the first time anyone presses its play button),
 *    saved in uploads/ds-voice-previews/ and reused for everybody afterwards. 17 voices = at most 17 short
 *    generations on the site's WaveSpeed account, ever.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_vp_voices') ) {
function ds_vp_voices() {
    return apply_filters('ds_voice_preview_ids', array(
        'Wise_Woman', 'Friendly_Person', 'Inspirational_girl', 'Deep_Voice_Man', 'Calm_Woman', 'Casual_Guy', 'Lively_Girl', 'Patient_Man',
        'Young_Knight', 'Determined_Man', 'Lovely_Girl', 'Decent_Boy', 'Imposing_Manner', 'Elegant_Man', 'Abbess', 'Sweet_Girl_2', 'Exuberant_Girl',
    ));
}
}
if ( ! function_exists('ds_vp_text') ) {
function ds_vp_text() {
    return apply_filters('ds_voice_preview_text', 'Hello! This is a short preview of my voice. I can read your script clearly and naturally, for videos, presentations and more.');
}
}
if ( ! function_exists('ds_vp_file') ) {
function ds_vp_file( $voice ) {
    $up = wp_upload_dir();
    $name = sanitize_file_name(strtolower($voice)) . '.mp3';
    return array(trailingslashit($up['basedir']) . 'ds-voice-previews/' . $name, trailingslashit($up['baseurl']) . 'ds-voice-previews/' . $name);
}
}

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/voice-preview', array(
        'methods'             => 'POST',
        'permission_callback' => function () { return is_user_logged_in(); },
        'callback'            => function ( WP_REST_Request $req ) {
            $voice = sanitize_text_field((string) $req->get_param('voice_id'));
            if ( ! in_array($voice, ds_vp_voices(), true) ) return rest_ensure_response(array('status' => 'error', 'message' => 'Unknown voice.'));
            list($path, $url) = ds_vp_file($voice);
            if ( file_exists($path) && filesize($path) > 1000 ) return rest_ensure_response(array('status' => 'ready', 'url' => set_url_scheme($url)));
            if ( ! function_exists('ds_call_wavespeed_speech') || ! function_exists('ds_poll_wavespeed_audio') ) return rest_ensure_response(array('status' => 'error', 'message' => 'Voice preview is not available right now.'));

            $kp = 'ds_vp_p_' . md5($voice); $kf = 'ds_vp_f_' . md5($voice);
            if ( get_transient($kf) ) return rest_ensure_response(array('status' => 'error', 'message' => 'This preview could not be made. Please try again in a minute.'));
            $pid = get_transient($kp);
            if ( ! $pid ) {                                   /* first request for this voice: start the one-time sample */
                set_transient($kp, 'starting', 60);           /* a second visitor in the same moment waits instead of starting another */
                $r = ds_call_wavespeed_speech(ds_vp_text(), $voice, 1.0, 'neutral', 'mp3');
                if ( empty($r['prediction_id']) ) {
                    delete_transient($kp); set_transient($kf, 1, 60);
                    error_log('[DS voice preview] ' . $voice . ': ' . ( isset($r['error']) ? $r['error'] : 'no task id' ));
                    return rest_ensure_response(array('status' => 'error', 'message' => 'This preview could not be made. Please try again in a minute.'));
                }
                set_transient($kp, (string) $r['prediction_id'], 5 * MINUTE_IN_SECONDS);
                return rest_ensure_response(array('status' => 'pending'));
            }
            if ( $pid === 'starting' ) return rest_ensure_response(array('status' => 'pending'));

            $res = ds_poll_wavespeed_audio($pid);
            $st = isset($res['status']) ? $res['status'] : '';
            if ( $st === 'failed' ) {
                delete_transient($kp); set_transient($kf, 1, 60);
                return rest_ensure_response(array('status' => 'error', 'message' => 'This preview could not be made. Please try again in a minute.'));
            }
            if ( $st !== 'succeeded' || empty($res['audio'][0]) ) return rest_ensure_response(array('status' => 'pending'));

            $dir = dirname($path);
            if ( ! file_exists($dir) ) wp_mkdir_p($dir);
            $tmp = $path . '.' . wp_generate_password(6, false, false) . '.part';
            $dl = wp_remote_get(esc_url_raw($res['audio'][0]), array('timeout' => 60, 'stream' => true, 'filename' => $tmp));
            if ( is_wp_error($dl) || (int) wp_remote_retrieve_response_code($dl) !== 200 || ! file_exists($tmp) || filesize($tmp) < 1000 ) {
                if ( file_exists($tmp) ) @unlink($tmp);
                return rest_ensure_response(array('status' => 'ready', 'url' => esc_url_raw($res['audio'][0])));   /* play it from the provider this time */
            }
            @rename($tmp, $path);
            delete_transient($kp);
            return rest_ensure_response(array('status' => 'ready', 'url' => set_url_scheme($url)));
        },
    ));
});

add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') || ! has_shortcode($post->post_content, 'ds_ai_audio') ) return;
    $ready = array();
    foreach ( ds_vp_voices() as $v ) { list($p, $u) = ds_vp_file($v); if ( file_exists($p) && filesize($p) > 1000 ) $ready[$v] = set_url_scheme($u); }
    ?>
<style id="ds-voice-preview-css">
.dsvp{position:relative;font-family:inherit}
.dsvp-head{display:flex;align-items:center;gap:8px;width:100%;background:#0a0a0a;border:1px solid #1f222b;border-radius:8px;padding:5px 6px;box-sizing:border-box}
.dsvp.open .dsvp-head{border-color:#2563eb}
.dsvp-cur{all:unset;box-sizing:border-box;flex:1;min-width:0;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:5px 6px;color:#e5e7eb;font-size:13.5px;cursor:pointer;border-radius:6px}
.dsvp-cur span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dsvp-cur svg{flex:none;transition:transform .15s}
.dsvp.open .dsvp-cur svg{transform:rotate(180deg)}
.dsvp-cur:focus-visible,.dsvp-play:focus-visible,.dsvp-row:focus-visible{outline:2px solid #60a5fa;outline-offset:1px}
.dsvp-play{all:unset;box-sizing:border-box;flex:none;width:28px;height:28px;border-radius:50%;background:#1d2434;color:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s}
.dsvp-play:hover{background:#2563eb}
.dsvp-play.playing{background:#2563eb}
.dsvp-play.loading svg{animation:dsvpSpin .8s linear infinite}
@keyframes dsvpSpin{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){.dsvp-play.loading svg{animation-duration:2.4s}.dsvp-cur svg{transition:none}}
.dsvp-list{display:none;margin-top:6px;background:#0a0a0a;border:1px solid #1f222b;border-radius:8px;max-height:272px;overflow-y:auto;padding:4px;scrollbar-width:thin;scrollbar-color:#2a2f3a transparent}
.dsvp.open .dsvp-list{display:block}
.dsvp-row{display:flex;align-items:center;gap:10px;padding:5px 8px;border-radius:6px;color:#cbd5e1;font-size:13.5px;cursor:pointer}
.dsvp-row:hover{background:#151922;color:#fff}
.dsvp-row.sel{background:#16213a;color:#fff}
.dsvp-row .dsvp-name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dsvp-row .dsvp-tick{flex:none;color:#60a5fa;visibility:hidden}
.dsvp-row.sel .dsvp-tick{visibility:visible}
.dsvp-hint{font-size:11.5px;color:#6b7280;margin-top:4px;line-height:1.4}
</style>
<script id="ds-voice-preview">
(function(){
var CFG = <?php echo wp_json_encode(array('url' => esc_url_raw(rest_url('ds/v1/voice-preview')), 'nonce' => wp_create_nonce('wp_rest'), 'ready' => (object) $ready)); ?>;
var IC = {
  play:'<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>',
  pause:'<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg>',
  load:'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9"/></svg>',
  caret:'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>',
  tick:'<svg class="dsvp-tick" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg>'
};
var audio = null, cur = '', token = 0, urls = CFG.ready || {};
function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
function say(msg){ var t = document.getElementById('dsaaToast'); if (window.dspToast) { try { window.dspToast(msg, 'error'); return; } catch (e) {} } if (window.console) console.warn('[voice preview]', msg); var h = document.querySelector('.dsvp-hint'); if (h) { h.textContent = msg; h.style.color = '#f87171'; setTimeout(function(){ h.textContent = HINT; h.style.color = ''; }, 5000); } }
var HINT = 'Press play to hear a voice. The preview is a sample sentence, not your text, and uses no credits.';
function btns(v){ return document.querySelectorAll('.dsvp-play[data-v="' + v + '"]'); }
function setState(v, st){
  [].forEach.call(document.querySelectorAll('.dsvp-play'), function(b){
    var mine = b.getAttribute('data-v') === v, s = mine ? st : '';
    b.classList.toggle('playing', s === 'playing'); b.classList.toggle('loading', s === 'loading');
    b.innerHTML = s === 'playing' ? IC.pause : s === 'loading' ? IC.load : IC.play;
    var nm = b.getAttribute('data-n') || '';
    b.setAttribute('aria-label', (s === 'playing' ? 'Stop preview of ' : s === 'loading' ? 'Loading preview of ' : 'Play preview of ') + nm);
  });
}
function stop(){ token++; if (audio) { try { audio.pause(); } catch (e) {} } cur = ''; setState('', ''); }
function fetchUrl(v, my, tries){
  if (urls[v]) return Promise.resolve(urls[v]);
  return fetch(CFG.url, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce':CFG.nonce}, body:JSON.stringify({voice_id:v})})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (my !== token) return null;
      if (d && d.status === 'ready' && d.url) { urls[v] = d.url; return d.url; }
      if (d && d.status === 'pending' && tries < 40) return new Promise(function(res){ setTimeout(res, 2000); }).then(function(){ return my === token ? fetchUrl(v, my, tries + 1) : null; });
      throw new Error((d && d.message) || 'This preview is not ready yet. Please try again.');
    });
}
function toggle(v){
  if (cur === v) { stop(); return; }
  stop(); cur = v; var my = token; setState(v, 'loading');
  fetchUrl(v, my, 0).then(function(u){
    if (my !== token || !u) return;
    if (!audio) { audio = new Audio(); audio.preload = 'auto'; audio.addEventListener('ended', function(){ cur = ''; setState('', ''); }); audio.addEventListener('error', function(){ if (cur) { var bad = cur; delete urls[bad]; cur = ''; setState('', ''); say('This preview could not be played. Please try again.'); } }); }
    /* the page's own players stop while a preview plays */
    [].forEach.call(document.querySelectorAll('.dsaaCard audio'), function(a){ try { a.pause(); } catch (e) {} });
    audio.src = u; var p = audio.play();
    setState(v, 'playing');
    if (p && p['catch']) p['catch'](function(){ if (my === token) { cur = ''; setState('', ''); } });
  })['catch'](function(e){ if (my === token) { cur = ''; setState('', ''); say(e && e.message ? e.message : 'This preview is not available.'); } });
}
function build(sel){
  var wrap = document.createElement('div'); wrap.className = 'dsvp';
  var opts = [].map.call(sel.options, function(o){ return {v:o.value, n:o.textContent}; });
  var h = '<div class="dsvp-head"><button type="button" class="dsvp-play dsvp-headplay"></button>'
        + '<button type="button" class="dsvp-cur" aria-haspopup="listbox" aria-expanded="false"><span></span>' + IC.caret + '</button></div>'
        + '<div class="dsvp-list" role="listbox" aria-label="Voice">';
  opts.forEach(function(o){
    h += '<div class="dsvp-row" role="option" tabindex="-1" data-v="' + esc(o.v) + '"><button type="button" class="dsvp-play" data-v="' + esc(o.v) + '" data-n="' + esc(o.n) + '" tabindex="-1"></button><span class="dsvp-name">' + esc(o.n) + '</span>' + IC.tick + '</div>';
  });
  h += '</div><div class="dsvp-hint">' + HINT + '</div>';
  wrap.innerHTML = h;
  sel.parentNode.insertBefore(wrap, sel.nextSibling);
  sel.style.display = 'none'; sel.setAttribute('aria-hidden', 'true'); sel.tabIndex = -1;
  var head = wrap.querySelector('.dsvp-cur'), hp = wrap.querySelector('.dsvp-headplay'), list = wrap.querySelector('.dsvp-list');
  function rows(){ return [].slice.call(list.querySelectorAll('.dsvp-row')); }
  function sync(){
    var o = sel.options[sel.selectedIndex] || sel.options[0]; if (!o) return;
    head.querySelector('span').textContent = o.textContent;
    hp.setAttribute('data-v', o.value); hp.setAttribute('data-n', o.textContent);
    rows().forEach(function(r){ var on = r.getAttribute('data-v') === o.value; r.classList.toggle('sel', on); r.setAttribute('aria-selected', on ? 'true' : 'false'); });
    setState(cur, cur ? (audio && !audio.paused ? 'playing' : 'loading') : '');
  }
  function open(on, focus){
    wrap.classList.toggle('open', on); head.setAttribute('aria-expanded', on ? 'true' : 'false');
    if (on && focus) { var s = list.querySelector('.dsvp-row.sel') || rows()[0]; if (s) { s.focus(); } }
  }
  function pick(v){ if (sel.value !== v) { sel.value = v; try { sel.dispatchEvent(new Event('change', {bubbles:true})); } catch (e) {} } sync(); open(false); head.focus(); }
  head.addEventListener('click', function(){ open(!wrap.classList.contains('open'), true); });
  head.addEventListener('keydown', function(e){ if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); open(true, true); } });
  wrap.addEventListener('click', function(e){
    var b = e.target.closest ? e.target.closest('.dsvp-play') : null;
    if (b) { e.preventDefault(); e.stopPropagation(); var v = b.getAttribute('data-v'); if (v) toggle(v); return; }
    var r = e.target.closest ? e.target.closest('.dsvp-row') : null;
    if (r) pick(r.getAttribute('data-v'));
  });
  list.addEventListener('keydown', function(e){
    var r = e.target.closest ? e.target.closest('.dsvp-row') : null; if (!r) return;
    var all = rows(), i = all.indexOf(r);
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); var n = all[Math.max(0, Math.min(all.length - 1, i + (e.key === 'ArrowDown' ? 1 : -1)))]; if (n) n.focus(); }
    else if (e.key === 'Enter') { e.preventDefault(); pick(r.getAttribute('data-v')); }
    else if (e.key === ' ') { e.preventDefault(); toggle(r.getAttribute('data-v')); }          /* space = hear it */
    else if (e.key === 'Escape') { e.preventDefault(); open(false); head.focus(); }
    else if (e.key === 'Home' || e.key === 'End') { e.preventDefault(); var t = all[e.key === 'Home' ? 0 : all.length - 1]; if (t) t.focus(); }
  });
  document.addEventListener('click', function(e){ if (wrap.classList.contains('open') && !wrap.contains(e.target)) open(false); });
  sel.addEventListener('change', sync);
  sync(); setState('', '');
}
function init(){ var sel = document.getElementById('dsaaVoice'); if (!sel) return false; if (!sel.__dsvp) { sel.__dsvp = 1; try { build(sel); } catch (e) { sel.style.display = ''; if (window.console) console.warn('[voice preview]', e); } } return true; }
if (!init()) { var n = 0, t = setInterval(function(){ if (init() || ++n > 40) clearInterval(t); }, 250); }
})();
</script>
    <?php
}, 99);
