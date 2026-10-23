<?php
/**
 * Code Snippets export — DS PPT auto pictures - sharp 1920px marketplace image (not counted)
 *
 * Snippet ID : 1047
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 11:11:52
 * Load order : 241 of 242
 */
/**
 * DS PPT auto pictures - sharp marketplace image for automatically chosen slide pictures.
 * The automatic picture on a slide used the 400px marketplace preview. This gives the slide the
 * original file resized to 1920px (JPEG 90) instead. It is not counted in the marketplace download limit.
 * New snippet: nothing existing is changed. Turn it off and slides get the 400px preview again.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_ppt_auto_img_resized') ) {
function ds_ppt_auto_img_resized( $post_id, $max = 1920 ) {
    $up  = wp_upload_dir();
    $dir = trailingslashit($up['basedir']) . 'ds-ppt-auto-img';
    $file = $dir . '/' . md5($post_id . '|' . $max . '|' . wp_salt('auth')) . '.jpg';
    if ( is_file($file) && filesize($file) > 1000 && ( time() - filemtime($file) ) < 30 * DAY_IN_SECONDS ) {
        $b = file_get_contents($file);
        if ( $b ) return $b;
    }
    if ( ! function_exists('ds_mkt_get_unwatermarked_file_bytes') ) return false;
    $real = ds_mkt_get_unwatermarked_file_bytes($post_id);
    if ( ! $real || empty($real['bytes']) ) return false;
    $bytes = $real['bytes']; unset($real);
    $out = false;
    try {
        if ( function_exists('wp_raise_memory_limit') ) wp_raise_memory_limit('image');
        $sz = function_exists('getimagesizefromstring') ? @getimagesizefromstring($bytes) : false;
        if ( ! $sz || $sz[0] < 1 || $sz[1] < 1 ) return false;
        if ( $sz[0] * $sz[1] > 80000000 ) return false;                      // too large to decode safely
        $sc = min(1, $max / max($sz[0], $sz[1]));
        $nw = max(1, (int) round($sz[0] * $sc)); $nh = max(1, (int) round($sz[1] * $sc));
        if ( function_exists('imagecreatefromstring') ) {
            $im = @imagecreatefromstring($bytes);
            if ( $im ) {
                $dst = imagecreatetruecolor($nw, $nh);
                imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // transparent areas become white, not black
                imagecopyresampled($dst, $im, 0, 0, 0, 0, $nw, $nh, imagesx($im), imagesy($im));
                imagedestroy($im);
                ob_start(); imagejpeg($dst, null, 90); $out = ob_get_clean();
                imagedestroy($dst);
            }
        }
        if ( ! $out && class_exists('Imagick') ) {
            $ik = new Imagick(); $ik->readImageBlob($bytes);
            $ik->setImageBackgroundColor('white'); $ik = $ik->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $ik->resizeImage($nw, $nh, Imagick::FILTER_LANCZOS, 1);
            $ik->setImageFormat('jpeg'); $ik->setImageCompressionQuality(90);
            $out = $ik->getImageBlob(); $ik->destroy();
        }
    } catch ( Throwable $e ) {
        error_log('[ds_ppt_auto_img] ' . $e->getMessage());
        $out = false;
    }
    if ( ! $out || strlen($out) < 1000 ) return false;
    if ( wp_mkdir_p($dir) ) {
        if ( ! is_file($dir . '/index.html') ) @file_put_contents($dir . '/index.html', '');
        @file_put_contents($file, $out);
    }
    return $out;
}
}

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/mkt-auto-image', array(
        'methods'             => 'POST',
        'permission_callback' => function () { return is_user_logged_in(); },
        'callback'            => function ( WP_REST_Request $req ) {
            @set_time_limit(90);
            $uid     = get_current_user_id();
            $post_id = (int) $req->get_param('post_id');
            $fallback = function () use ( $req ) {                          // old behaviour: the preview file
                if ( function_exists('ds_ppt_mkt_fetch_image') && $req->get_param('url') ) {
                    $r2 = new WP_REST_Request('POST', '/ds/v1/mkt-fetch-image');
                    $r2->set_param('url', $req->get_param('url'));
                    return ds_ppt_mkt_fetch_image($r2);
                }
                return new WP_REST_Response(array('success' => false, 'error' => 'No image source provided'), 400);
            };
            if ( $post_id < 1 || get_post_type($post_id) !== 'dlm_download' || get_post_status($post_id) !== 'publish' ) return $fallback();
            /* a Pro picture stays a preview for a plan that cannot use Pro pictures at all */
            $tier = function_exists('ds_ppt_mkt_item_tier') ? ds_ppt_mkt_item_tier($post_id) : 'free';
            if ( $tier === 'pro' && function_exists('ds_dl_check_core') ) {
                $chk = ds_dl_check_core($uid, 'pro', false);
                if ( empty($chk['ok']) && ( $chk['reason'] ?? '' ) === 'upgrade_required' ) return $fallback();
            }
            /* not counted in the download limit, so keep a plain hourly ceiling against misuse */
            $rk = 'ds_ppt_autoimg_' . $uid . '_' . gmdate('YmdH');
            $n  = (int) get_transient($rk);
            if ( $n >= 120 ) return $fallback();
            set_transient($rk, $n + 1, HOUR_IN_SECONDS);
            $bytes = ds_ppt_auto_img_resized($post_id, 1920);
            if ( ! $bytes ) return $fallback();
            return new WP_REST_Response(array(
                'success' => true,
                'data'    => 'data:image/jpeg;base64,' . base64_encode($bytes),
                'mime'    => 'image/jpeg',
                'size'    => strlen($bytes),
                'auto_hd' => true,
            ), 200);
        },
    ));
});

add_action('wp_footer', function () {
    if ( is_admin() || ! is_user_logged_in() ) return;
    ?>
<script id="ds-ppt-auto-img">
/* Automatic slide pictures: remember which marketplace item each preview belongs to, and ask for the sharp 1920px
   version of that item instead of the 400px preview. Pictures picked by hand are not touched. */
(function(){
if (window.__dsAutoImg || !window.fetch) return; window.__dsAutoImg = 1;
var MAP = {}, of = window.fetch;
function urlOf(input){ return typeof input === 'string' ? input : (input && input.url) || ''; }
window.fetch = function(input, init){
  var u = urlOf(input);
  try {
    if (u.indexOf('/ds/v1/mkt-fetch-image') !== -1 && init && typeof init.body === 'string') {
      var b = JSON.parse(init.body);
      if (b && b.url && !b.post_id && MAP[b.url]) {
        var ni = {}; for (var k in init) ni[k] = init[k];
        ni.body = JSON.stringify({post_id: MAP[b.url], url: b.url});
        return of.call(this, u.replace('/mkt-fetch-image', '/mkt-auto-image'), ni);
      }
    }
  } catch (e) {}
  var p = of.apply(this, arguments);
  if (u.indexOf('/ds/v1/mkt-slide-images') !== -1) {
    p.then(function(r){ try { r.clone().json().then(function(d){ (d && d.items || []).forEach(function(it){ if (it && it.id) { if (it.full) MAP[it.full] = it.id; if (it.thumb && !MAP[it.thumb]) MAP[it.thumb] = it.id; } }); }).catch(function(){}); } catch (e) {} }).catch(function(){});
  }
  return p;
};
})();
</script>
    <?php
}, 5);