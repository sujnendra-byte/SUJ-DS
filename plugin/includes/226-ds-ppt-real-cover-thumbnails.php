<?php
/**
 * Code Snippets export — DS PPT Real Cover Thumbnails
 *
 * Snippet ID : 1013
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-ppt
 * Modified   : 2026-10-03 15:16:37
 * Load order : 217 of 218
 */
/**
 * DS PPT Real Cover Thumbnails
 * Run: Everywhere
 *
 * AI Presentation - My Presentations: the card of a saved deck shows its real first (cover) slide,
 * drawn with the deck's own design template, colours, layout and picture - not a plain-text
 * preview and not just the cover picture on its own.
 *  - REST ds/v1/my-ppt-covers: for every saved deck, its template, theme, cover layout and first
 *    slide (the picture as the small cached thumbnail, so the reply stays light).
 *  - JS: draws that cover into the card. Classic (no template) decks keep their built-in cover
 *    preview, scaled to the card size so the text is readable.
 * Needs the "DS Template Engine (T00)" snippet.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/my-ppt-covers', array(
        'methods' => 'GET',
        'permission_callback' => function () { return is_user_logged_in(); },
        'callback' => function () {
            $uid = get_current_user_id();
            $drafts = get_user_meta( $uid, 'ds_ppt_drafts', true );
            $out = array();
            /* the list route defines the cached small-thumbnail helper (ds_ppt_thumb) when it runs */
            if ( ! function_exists('ds_ppt_thumb') && function_exists('ds_ppt_list_drafts') ) { try { ds_ppt_list_drafts( new WP_REST_Request( 'GET', '/ds/v1/my-ppts' ) ); } catch ( \Throwable $e ) {} }
            if ( is_array( $drafts ) ) {
                foreach ( $drafts as $k => $d ) {
                    if ( ! is_array( $d ) ) continue;
                    $id = isset( $d['id'] ) && is_string( $d['id'] ) ? $d['id'] : ( is_string( $k ) ? $k : '' );
                    if ( $id === '' ) continue;
                    $data = null;
                    foreach ( array('data','ppt_data') as $dk ) { if ( isset( $d[ $dk ] ) && is_array( $d[ $dk ] ) ) { $data = $d[ $dk ]; break; } }
                    if ( ! $data && isset( $d['slides'] ) ) $data = $d;
                    if ( ! is_array( $data ) || empty( $data['slides'] ) || ! is_array( $data['slides'] ) ) continue;
                    $slides = array_values( $data['slides'] );
                    $s = is_array( $slides[0] ) ? $slides[0] : array();
                    $img = '';
                    if ( ! empty( $s['image_data'] ) && is_string( $s['image_data'] ) ) {
                        if ( function_exists('ds_ppt_thumb') ) $img = (string) ds_ppt_thumb( $s['image_data'], $uid );
                        if ( $img === '' && strlen( $s['image_data'] ) < 60000 ) $img = $s['image_data'];
                        if ( $img === '' && function_exists('imagecreatefromstring') && preg_match( '#^data:image/[a-z+]+;base64,(.+)$#s', $s['image_data'], $im ) ) {
                            $gd = @imagecreatefromstring( (string) base64_decode( $im[1] ) );
                            if ( $gd ) {
                                $w = imagesx( $gd ); $h = imagesy( $gd ); $nw = min( 480, $w ); $nh = max( 1, (int) round( $h * $nw / max( 1, $w ) ) );
                                $sm = imagecreatetruecolor( $nw, $nh ); imagefill( $sm, 0, 0, imagecolorallocate( $sm, 255, 255, 255 ) );
                                imagecopyresampled( $sm, $gd, 0, 0, 0, 0, $nw, $nh, $w, $h );
                                ob_start(); imagejpeg( $sm, null, 78 ); $jb = ob_get_clean(); imagedestroy( $gd ); imagedestroy( $sm );
                                if ( $jb ) $img = 'data:image/jpeg;base64,' . base64_encode( $jb );
                            }
                        }
                    }
                    unset( $s['image_data'], $s['image2_data'] );
                    if ( $img !== '' ) $s['image_data'] = $img;
                    $tpl = isset( $data['tpl'] ) && is_string( $data['tpl'] ) ? sanitize_key( $data['tpl'] ) : 'classic';
                    $v = 'v1';
                    if ( isset( $data['slide_variants']['slide_0'] ) && is_string( $data['slide_variants']['slide_0'] ) ) $v = sanitize_text_field( $data['slide_variants']['slide_0'] );
                    $theme = isset( $data['tplTheme'] ) && is_string( $data['tplTheme'] ) ? sanitize_text_field( $data['tplTheme'] ) : '';
                    $out[ $id ] = array( 'tpl' => $tpl ? $tpl : 'classic', 'theme' => $theme, 'v' => $v, 'slide' => $s, 'sig' => substr( md5( wp_json_encode( array( $tpl, $theme, $v, $s ) ) ), 0, 12 ) );
                }
            }
            return array( 'success' => true, 'covers' => $out );
        },
    ));
});

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-ppt-real-covers">
(function(){
(function(){
  if (window.__dsDeckCovers) return; window.__dsDeckCovers = 1;
  var covers = null, loading = null, fonts = {}, timer = 0;
  function D(){ return window.DSTE; }
  function nonce(){ return window.DSP_NONCE || (window.wpApiSettings && wpApiSettings.nonce) || ''; }
  function load(){
    if (loading) return loading;
    loading = fetch('/wp-json/ds/v1/my-ppt-covers', {credentials:'same-origin', headers:{'X-WP-Nonce':nonce()}}).then(function(r){ return r.json(); }).then(function(d){ covers = (d && d.covers) || {}; loading = null; return covers; }).catch(function(){ covers = covers || {}; loading = null; return covers; });
    return loading;
  }
  function font(T){ var gf = T && T.fonts && T.fonts.gf; if (!gf || fonts[gf]) return; fonts[gf] = 1; var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = 'https://fonts.googleapis.com/css2?family=' + gf.split('|').join('&family=') + '&display=swap'; document.head.appendChild(l); }
  function paint(card){
    var id = card.id.slice(9), th = card.firstElementChild; if (!th) return true;
    var c = covers[id]; if (!c) return false;
    var d = D(), sig = id + '|' + (c.sig || '');
    if (th.getAttribute('data-ds-cov') === sig && (c.tpl === 'classic' || th.querySelector('.ds-cov'))) return true;
    if (c.tpl && c.tpl !== 'classic' && d && d.reg && d.reg[c.tpl] && d.renderHTML && c.slide) {
      try {
        var T = d.themed ? d.themed(d.reg[c.tpl], c.theme || '') : d.reg[c.tpl]; font(d.reg[c.tpl]);
        var h = d.renderHTML(T, c.slide, c.v || 'v1', 0, {slides:[c.slide], title:c.slide.title || ''}, {edit:false});
        var old = th.querySelector('.ds-cov'); if (old) old.remove();
        var layer = document.createElement('div'); layer.className = 'ds-cov'; layer.style.cssText = 'position:absolute;inset:0;z-index:1;pointer-events:none;font-size:initial;text-align:left;';
        layer.innerHTML = h; th.appendChild(layer);
      } catch (e) {}
    } else {
      /* Classic decks: the built-in cover preview was sized for small cards - scale it to the card */
      var w = th.offsetWidth; if (w > 0) th.style.fontSize = (w * 0.0146).toFixed(2) + 'px';
    }
    th.setAttribute('data-ds-cov', sig);
    return true;
  }
  function run(){
    var cards = document.querySelectorAll('[id^="dsp-card-ppt_"]'); if (!cards.length) return;
    if (!covers) { load().then(run); return; }
    var miss = false; for (var i = 0; i < cards.length; i++) if (!paint(cards[i])) miss = true;
    if (miss && !run._re) { run._re = 1; covers = null; load().then(function(){ run(); setTimeout(function(){ run._re = 0; }, 4000); }); }
  }
  function soon(){ clearTimeout(timer); timer = setTimeout(run, 120); }
  new MutationObserver(function(ms){ for (var i = 0; i < ms.length; i++) { var t = ms[i].target; if (t && t.closest && t.closest('.ds-cov')) continue; soon(); return; } }).observe(document.body, {childList:true, subtree:true});
  window.addEventListener('resize', function(){ var a = document.querySelectorAll('[id^="dsp-card-ppt_"] > [data-ds-cov]'); for (var i = 0; i < a.length; i++) if (!a[i].querySelector('.ds-cov')) a[i].removeAttribute('data-ds-cov'); soon(); });
  /* a save changes the cover: read the list again */
  var of = window.fetch; window.fetch = function(input){ var u = typeof input === 'string' ? input : ((input && input.url) || ''); var p = of.apply(this, arguments); if (u.indexOf('/ds/v1/save-ppt') >= 0) p.then(function(){ covers = null; soon(); }, function(){}); return p; };
  soon();
})();
})();
</script>
<?php
}, 1105);
