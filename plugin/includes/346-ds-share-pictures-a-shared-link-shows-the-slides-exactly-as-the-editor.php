<?php
/**
 * Code Snippets export — DS Share Pictures - a shared link shows the slides exactly as the editor shows them
 *
 * Snippet ID : 1181
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-share-pictures
 * Modified   : 2026-10-10 15:55:00
 * Load order : 346 of 346
 */
/**
 * DS Share Pictures - a shared link shows the slides exactly as the editor shows them
 * Run: Everywhere
 *
 * The public viewer (yoursite.com/?ds_view=TOKEN) draws slides with its own simple drawing code. It only knows the
 * eight old colour themes, so a presentation made with a design template looked completely different when shared
 * (a photo with plain text on top).
 *
 * With this snippet, when somebody presses Share:
 *  1. the share link is made as before,
 *  2. every slide is photographed the same way the JPG download does it (design template, layout, edits, charts,
 *     icons - hidden slides are left out) and the pictures are stored with the link,
 *  3. the viewer shows those pictures (arrows, keyboard, swipe, full screen), and the link preview in WhatsApp /
 *     Facebook / LinkedIn / X uses the first slide and the presentation title.
 * If the pictures cannot be made for any reason, the link still works and shows the old simple view.
 * Links shared before this snippet keep the old view until the presentation is shared again.
 * Pictures are deleted when the link expires (90 days) or the shared presentation is removed.
 *
 * NEW SNIPPET - nothing existing is changed. Undo: deactivate this snippet (links go back to the old simple view).
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_sp_base') ) {

function ds_sp_base() {
	$u = wp_upload_dir();
	return array( trailingslashit( $u['basedir'] ) . 'ds-share-pics', trailingslashit( $u['baseurl'] ) . 'ds-share-pics' );
}
function ds_sp_token( $t ) {
	$t = is_string( $t ) ? $t : '';
	return preg_match( '/^[A-Za-z0-9]{10,40}$/', $t ) ? $t : '';
}
/* does the share link still exist (asked straight from the table, so nothing heavy is loaded) */
function ds_sp_share_exists( $token ) {
	global $wpdb;
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", 'ds_share_' . $token ) );
}
function ds_sp_owns( $token ) {
	$uid = get_current_user_id();
	if ( ! $uid ) return false;
	$rec = get_option( 'ds_share_' . $token );
	return is_array( $rec ) && isset( $rec['owner'] ) && (int) $rec['owner'] === (int) $uid;
}
function ds_sp_file( $token, $key, $i ) {
	return $token . '/' . $key . '-' . sprintf( '%02d', (int) $i ) . '.jpg';
}
function ds_sp_wipe( $token ) {
	$token = ds_sp_token( $token );
	if ( $token === '' ) return;
	list( $dir ) = ds_sp_base();
	$d = $dir . '/' . $token;
	if ( is_dir( $d ) ) {
		foreach ( (array) glob( $d . '/*.jpg' ) as $f ) { if ( is_file( $f ) ) @unlink( $f ); }
		@rmdir( $d );
	}
}
function ds_sp_fail( $msg ) {
	return rest_ensure_response( array( 'success' => false, 'error' => $msg ) );
}

add_action( 'rest_api_init', function () {
	$logged = function () { return is_user_logged_in(); };

	/* 1. start: forget the old pictures of this link */
	register_rest_route( 'ds/v1', '/share-pics/begin', array(
		'methods' => 'POST', 'permission_callback' => $logged,
		'callback' => function ( WP_REST_Request $req ) {
			$token = ds_sp_token( $req->get_param( 'token' ) );
			$n     = (int) $req->get_param( 'count' );
			if ( $token === '' || ! ds_sp_owns( $token ) ) return ds_sp_fail( 'Link not found' );
			if ( $n < 1 || $n > 80 ) return ds_sp_fail( 'Bad slide count' );
			list( $dir ) = ds_sp_base();
			ds_sp_wipe( $token );
			if ( ! wp_mkdir_p( $dir . '/' . $token ) ) return ds_sp_fail( 'Could not make the folder' );
			if ( ! is_file( $dir . '/index.html' ) ) @file_put_contents( $dir . '/index.html', '' );
			@file_put_contents( $dir . '/' . $token . '/index.html', '' );
			update_option( 'ds_sharepics_' . $token, array(
				'state'   => 'uploading',
				'key'     => strtolower( wp_generate_password( 8, false, false ) ),
				'n'       => $n,
				'title'   => sanitize_text_field( (string) $req->get_param( 'title' ) ),
				'owner'   => get_current_user_id(),
				'created' => time(),
				'w'       => 0, 'h' => 0,
			), false );
			return rest_ensure_response( array( 'success' => true ) );
		},
	) );

	/* 2. one slide picture */
	register_rest_route( 'ds/v1', '/share-pics/put', array(
		'methods' => 'POST', 'permission_callback' => $logged,
		'callback' => function ( WP_REST_Request $req ) {
			$token = ds_sp_token( $req->get_param( 'token' ) );
			$i     = (int) $req->get_param( 'i' );
			if ( $token === '' || ! ds_sp_owns( $token ) ) return ds_sp_fail( 'Link not found' );
			$p = get_option( 'ds_sharepics_' . $token );
			if ( ! is_array( $p ) || empty( $p['key'] ) || $p['state'] !== 'uploading' ) return ds_sp_fail( 'Not started' );
			if ( $i < 0 || $i >= (int) $p['n'] ) return ds_sp_fail( 'Bad slide number' );
			$files = $req->get_file_params();
			$f = isset( $files['file'] ) ? $files['file'] : null;
			if ( ! is_array( $f ) || ! empty( $f['error'] ) || empty( $f['tmp_name'] ) || ! is_uploaded_file( $f['tmp_name'] ) ) return ds_sp_fail( 'No picture' );
			if ( (int) $f['size'] < 500 || (int) $f['size'] > 3 * 1024 * 1024 ) return ds_sp_fail( 'Picture too large' );
			$info = @getimagesize( $f['tmp_name'] );
			if ( ! $info || (int) $info[2] !== IMAGETYPE_JPEG || $info[0] < 200 || $info[0] > 4000 || $info[1] < 100 || $info[1] > 4000 ) return ds_sp_fail( 'Not a JPG picture' );
			list( $dir ) = ds_sp_base();
			$dest = $dir . '/' . ds_sp_file( $token, $p['key'], $i );
			if ( ! @move_uploaded_file( $f['tmp_name'], $dest ) ) return ds_sp_fail( 'Could not save the picture' );
			@chmod( $dest, 0644 );
			if ( $i === 0 ) { $p['w'] = (int) $info[0]; $p['h'] = (int) $info[1]; update_option( 'ds_sharepics_' . $token, $p, false ); }
			return rest_ensure_response( array( 'success' => true ) );
		},
	) );

	/* 3. finished: from now on the viewer shows the pictures */
	register_rest_route( 'ds/v1', '/share-pics/done', array(
		'methods' => 'POST', 'permission_callback' => $logged,
		'callback' => function ( WP_REST_Request $req ) {
			$token = ds_sp_token( $req->get_param( 'token' ) );
			if ( $token === '' || ! ds_sp_owns( $token ) ) return ds_sp_fail( 'Link not found' );
			$p = get_option( 'ds_sharepics_' . $token );
			if ( ! is_array( $p ) || empty( $p['key'] ) ) return ds_sp_fail( 'Not started' );
			list( $dir ) = ds_sp_base();
			for ( $i = 0; $i < (int) $p['n']; $i++ ) {
				if ( ! is_file( $dir . '/' . ds_sp_file( $token, $p['key'], $i ) ) ) return ds_sp_fail( 'A slide picture is missing' );
			}
			$p['state'] = 'ready'; $p['created'] = time();
			update_option( 'ds_sharepics_' . $token, $p, false );
			return rest_ensure_response( array( 'success' => true, 'slides' => (int) $p['n'] ) );
		},
	) );
} );

/* ===== the viewer: runs just before the old one and takes over only when the pictures are there ===== */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['ds_view'] ) ) return;
	$token = ds_sp_token( sanitize_text_field( wp_unslash( $_GET['ds_view'] ) ) );
	if ( $token === '' ) return;
	$p = get_option( 'ds_sharepics_' . $token );
	if ( ! is_array( $p ) || empty( $p['key'] ) || empty( $p['n'] ) || ( isset( $p['state'] ) ? $p['state'] : '' ) !== 'ready' ) return;
	if ( ! empty( $p['created'] ) && ( time() - (int) $p['created'] ) > 90 * DAY_IN_SECONDS ) return;
	if ( ! ds_sp_share_exists( $token ) ) return;
	list( $dir, $url ) = ds_sp_base();
	$n = (int) $p['n']; $pics = array();
	for ( $i = 0; $i < $n; $i++ ) {
		$rel = ds_sp_file( $token, $p['key'], $i );
		if ( ! is_file( $dir . '/' . $rel ) ) return;                 /* something is missing: the old viewer answers */
		$pics[] = set_url_scheme( $url . '/' . $rel );
	}
	$title = trim( (string) ( isset( $p['title'] ) ? $p['title'] : '' ) );
	if ( $title === '' ) $title = 'Presentation';
	$w = max( 1, (int) $p['w'] ); $h = max( 1, (int) $p['h'] );
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$desc = $n . ( $n === 1 ? ' slide' : ' slides' ) . ( $site !== '' ? ' - made with ' . $site : '' );
	$self = home_url( '/?ds_view=' . $token );
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo esc_html( $title ); ?></title>
<meta name="description" content="<?php echo esc_attr( $desc ); ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $self ); ?>">
<meta property="og:image" content="<?php echo esc_url( $pics[0] ); ?>">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="<?php echo (int) $w; ?>">
<meta property="og:image:height" content="<?php echo (int) $h; ?>">
<?php if ( $site !== '' ) : ?><meta property="og:site_name" content="<?php echo esc_attr( $site ); ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>">
<meta name="twitter:image" content="<?php echo esc_url( $pics[0] ); ?>">
<style>
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;background:#0a0a0a;color:#e5e7eb;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;-webkit-tap-highlight-color:transparent}
#dsv-top{position:fixed;left:0;right:0;top:0;display:flex;align-items:center;gap:12px;padding:12px 16px;font-size:14px;color:#cbd5e1}
#dsv-title{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600}
#dsv-stage{position:relative;width:min(94vw,calc((100vh - 150px) * <?php echo (int) $w; ?> / <?php echo (int) $h; ?>));max-width:1500px;aspect-ratio:<?php echo (int) $w; ?> / <?php echo (int) $h; ?>;border-radius:6px;overflow:hidden;background:#151515;box-shadow:0 10px 40px rgba(0,0,0,.6);touch-action:pan-y}
#dsv-stage img{position:absolute;inset:0;width:100%;height:100%;display:block;object-fit:contain;-webkit-user-drag:none;user-select:none}
#dsv-stage .dsv-half{position:absolute;top:0;bottom:0;width:50%;background:transparent;border:0;padding:0;cursor:pointer}
#dsv-stage .dsv-half:focus-visible{outline:2px solid #60a5fa;outline-offset:-2px}
#dsv-l{left:0}#dsv-r{right:0}
#dsv-nav{display:flex;align-items:center;gap:18px;margin-top:18px;color:#9ca3af;font-size:13px;font-variant-numeric:tabular-nums}
.dsv-b{background:#1f1f1f;border:1px solid #2a2a2a;color:#e5e7eb;border-radius:8px;min-width:40px;height:40px;padding:0 10px;cursor:pointer;font:inherit;font-size:18px;line-height:1;display:inline-flex;align-items:center;justify-content:center}
.dsv-b:hover{background:#2a2a2a}
.dsv-b:disabled{opacity:.3;cursor:default}
.dsv-b:focus-visible{outline:2px solid #60a5fa;outline-offset:2px}
#dsv-fs{font-size:13px;gap:6px}
body.dsv-full #dsv-top{display:none}
body.dsv-full #dsv-stage{width:min(100vw,calc(100vh * <?php echo (int) $w; ?> / <?php echo (int) $h; ?>));max-width:none;border-radius:0}
body.dsv-full #dsv-nav{position:fixed;bottom:10px;left:50%;transform:translateX(-50%);margin:0;opacity:.35;transition:opacity .2s}
body.dsv-full #dsv-nav:hover,body.dsv-full #dsv-nav:focus-within{opacity:1}
@media (max-width:640px){#dsv-stage{width:100vw;border-radius:0}#dsv-top{padding:10px 12px;font-size:13px}}
@media (prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
</head>
<body>
<div id="dsv-top"><div id="dsv-title"><?php echo esc_html( $title ); ?></div></div>
<div id="dsv-stage">
  <img id="dsv-img" src="<?php echo esc_url( $pics[0] ); ?>" width="<?php echo (int) $w; ?>" height="<?php echo (int) $h; ?>" alt="<?php echo esc_attr( $title . ' - slide 1' ); ?>" decoding="async">
  <button type="button" class="dsv-half" id="dsv-l" aria-label="Previous slide" tabindex="-1"></button>
  <button type="button" class="dsv-half" id="dsv-r" aria-label="Next slide" tabindex="-1"></button>
</div>
<div id="dsv-nav">
  <button type="button" class="dsv-b" id="dsv-prev" aria-label="Previous slide">&#8249;</button>
  <span id="dsv-count" aria-live="polite">1 / <?php echo (int) $n; ?></span>
  <button type="button" class="dsv-b" id="dsv-next" aria-label="Next slide">&#8250;</button>
  <button type="button" class="dsv-b" id="dsv-fs" aria-label="Full screen">&#x26F6;</button>
</div>
<script>
(function(){
  var PICS = <?php echo wp_json_encode( $pics ); ?>, TITLE = <?php echo wp_json_encode( $title ); ?>;
  var idx = 0, n = PICS.length, img = document.getElementById('dsv-img'), seen = {};
  function $(id){ return document.getElementById(id); }
  function warm(i){ if (i < 0 || i >= n || seen[i]) return; seen[i] = 1; var im = new Image(); im.decoding = 'async'; im.src = PICS[i]; }
  function show(i){
    idx = Math.max(0, Math.min(i, n - 1));
    img.src = PICS[idx]; img.alt = TITLE + ' - slide ' + (idx + 1);
    $('dsv-count').textContent = (idx + 1) + ' / ' + n;
    $('dsv-prev').disabled = idx === 0; $('dsv-next').disabled = idx === n - 1;
    try { history.replaceState(null, '', location.pathname + location.search + (idx ? '#' + (idx + 1) : '')); } catch (e) {}
    warm(idx + 1); warm(idx + 2); warm(idx - 1);
  }
  $('dsv-prev').onclick = $('dsv-l').onclick = function(){ show(idx - 1); };
  $('dsv-next').onclick = $('dsv-r').onclick = function(){ show(idx + 1); };
  document.addEventListener('keydown', function(e){
    if (e.key === 'ArrowLeft' || e.key === 'PageUp') { show(idx - 1); e.preventDefault(); }
    else if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') { show(idx + 1); e.preventDefault(); }
    else if (e.key === 'Home') show(0); else if (e.key === 'End') show(n - 1);
  });
  var sx = null, sy = null, st = $('dsv-stage');
  st.addEventListener('touchstart', function(e){ var t = e.changedTouches[0]; sx = t.clientX; sy = t.clientY; }, {passive: true});
  st.addEventListener('touchend', function(e){
    if (sx == null) return; var t = e.changedTouches[0], dx = t.clientX - sx, dy = t.clientY - sy; sx = null;
    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.3) { show(idx + (dx < 0 ? 1 : -1)); e.preventDefault(); }
  });
  var fs = $('dsv-fs'), el = document.documentElement;
  if (!(el.requestFullscreen || el.webkitRequestFullscreen)) fs.style.display = 'none';
  fs.onclick = function(){
    var on = document.fullscreenElement || document.webkitFullscreenElement;
    try { if (on) (document.exitFullscreen || document.webkitExitFullscreen).call(document); else (el.requestFullscreen || el.webkitRequestFullscreen).call(el); } catch (e) {}
  };
  function fsch(){ document.body.classList.toggle('dsv-full', !!(document.fullscreenElement || document.webkitFullscreenElement)); }
  document.addEventListener('fullscreenchange', fsch); document.addEventListener('webkitfullscreenchange', fsch);
  var h = parseInt((location.hash || '').replace('#', ''), 10);
  show(h > 0 ? h - 1 : 0);
})();
</script>
</body>
</html>
<?php
	exit;
}, 9 );

/* ===== housekeeping: once a day, delete pictures of links that expired or no longer exist ===== */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ds_sp_gc' ) ) wp_schedule_event( time() + 600, 'daily', 'ds_sp_gc' );
} );
add_action( 'ds_sp_gc', function () {
	list( $dir ) = ds_sp_base();
	if ( ! is_dir( $dir ) ) return;
	$done = 0;
	foreach ( (array) glob( $dir . '/*', GLOB_ONLYDIR ) as $d ) {
		$token = ds_sp_token( basename( $d ) );
		if ( $token === '' ) continue;
		$p = get_option( 'ds_sharepics_' . $token );
		$age = is_array( $p ) && ! empty( $p['created'] ) ? time() - (int) $p['created'] : PHP_INT_MAX;
		$stale = ! is_array( $p ) ? ( time() - (int) @filemtime( $d ) > DAY_IN_SECONDS ) : ( $age > 90 * DAY_IN_SECONDS || ( $age > DAY_IN_SECONDS && ! ds_sp_share_exists( $token ) ) || ( $p['state'] !== 'ready' && $age > DAY_IN_SECONDS ) );
		if ( ! $stale ) continue;
		ds_sp_wipe( $token );
		delete_option( 'ds_sharepics_' . $token );
		if ( ++$done >= 200 ) break;
	}
} );

/* ===== editor: after the link is made, photograph the slides and store them with the link ===== */
add_action( 'wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a( $post, 'WP_Post' ) ) return;
	if ( ! has_shortcode( $post->post_content, 'ds_ai_ppt' ) && ! has_shortcode( $post->post_content, 'ds_ai_studio' ) ) return;
	?>
<script id="ds-share-pics-cfg">window.DS_SHAREPICS={rest:<?php echo wp_json_encode( esc_url_raw( rest_url( 'ds/v1/share-pics/' ) ) ); ?>,nonce:<?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>};</script>
<script id="ds-share-pics-js">
(function(){
  'use strict';
  if (window.__dsSharePics) return; window.__dsSharePics = 1;
  var CFG = window.DS_SHAREPICS || {}, busy = false, hush = false;
  var OUT_W = 1600, QUALITY = 0.86, LIMIT_MS = 150000;
  var rawFetch = window.fetch;

  function nonce(){ return CFG.nonce || window.DSP_NONCE || ((window.wpApiSettings || {}).nonce) || ''; }
  function wait(ms){ return new Promise(function(r){ setTimeout(r, ms); }); }
  function deckTitle(){
    try { return String((window.G && G.pptTitle) || (window.S && S.data && S.data.title) || (window.G && G.pptData && G.pptData.title) || '').slice(0, 160); } catch (e) { return ''; }
  }
  function post(step, body, isForm){
    var o = {method: 'POST', credentials: 'same-origin', headers: {'X-WP-Nonce': nonce()}};
    if (isForm) o.body = body; else { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
    return rawFetch.call(window, CFG.rest + step, o).then(function(r){ return r.json(); }).then(function(d){
      if (!d || !d.success) throw new Error((d && (d.error || d.message)) || 'The slide pictures could not be saved.');
      return d;
    });
  }
  /* the download snippet tells other snippets "a file was downloaded" - not true here */
  window.addEventListener('ds:ppt-exported', function(e){ if (hush) e.stopImmediatePropagation(); }, true);

  function shrink(blob){
    if (typeof createImageBitmap !== 'function') return Promise.resolve(blob);
    return createImageBitmap(blob).then(function(bm){
      if (bm.width <= OUT_W && blob.size < 420000) { if (bm.close) bm.close(); return blob; }
      var w = Math.min(OUT_W, bm.width), h = Math.round(bm.height * w / bm.width);
      var c = document.createElement('canvas'); c.width = w; c.height = h;
      var x = c.getContext('2d'); x.imageSmoothingQuality = 'high'; x.drawImage(bm, 0, 0, w, h);
      if (bm.close) bm.close();
      return new Promise(function(res){ c.toBlob(function(b){ c.width = c.height = 0; res(b && b.size < blob.size ? b : blob); }, 'image/jpeg', QUALITY); });
    }).catch(function(){ return blob; });
  }
  /* the wording on the progress card while the slides are photographed */
  function relabel(){
    var m = document.querySelector('#dsx-prog .dsx-m'); if (!m) return;
    var t = m.textContent || '', u = t.replace('Preparing your download', 'Preparing your share link').replace('Finishing your file', 'Saving the slide pictures');
    if (u !== t) m.textContent = u;
  }
  async function photograph(){
    var X = window.DSX;
    if (!X || typeof X.exportAs !== 'function') throw new Error('The picture maker is not on this page.');
    if (!document.getElementById('dsp-ppt-main-slide') || typeof window.dspSelectThumb !== 'function') throw new Error('The editor is not open.');
    var got = null, save0 = X.save, toast0 = window.dspToast, fp = window.DSFP, fpx = fp ? fp.exported : undefined;
    var mySave = function(blob, name){ got = {blob: blob, name: String(name || '')}; };
    var myToast = function(msg){ if (/download/i.test(String(msg || ''))) return; if (typeof toast0 === 'function') return toast0.apply(this, arguments); };
    var mo = null;
    try { mo = new MutationObserver(relabel); mo.observe(document.body, {childList: true, subtree: true, characterData: true}); } catch (e) {}
    X.save = mySave; window.dspToast = myToast; hush = true;
    try { await X.exportAs('jpg'); }
    finally {
      if (X.save === mySave) X.save = save0;
      if (window.dspToast === myToast) window.dspToast = toast0;
      hush = false; if (mo) mo.disconnect();
      if (fp && fpx !== undefined) fp.exported = fpx;
    }
    if (!got || !got.blob) throw new Error('No slide pictures were made.');
    var blobs = [];
    if (/\.zip$/i.test(got.name)) {
      if (!window.JSZip) throw new Error('Could not open the slide pictures.');
      var z = await window.JSZip.loadAsync(got.blob), names = Object.keys(z.files).filter(function(k){ return !z.files[k].dir && /\.jpe?g$/i.test(k); }).sort();
      for (var i = 0; i < names.length; i++) blobs.push(new Blob([await z.files[names[i]].async('arraybuffer')], {type: 'image/jpeg'}));
    } else blobs.push(got.blob);
    if (!blobs.length) throw new Error('No slide pictures were made.');
    return blobs;
  }
  async function makePics(token){
    var blobs = await photograph(), n = blobs.length;
    await post('begin', {token: token, count: n, title: deckTitle()});
    for (var i = 0; i < n; i++) {
      var b = await shrink(blobs[i]); blobs[i] = null;
      var fd = new FormData(); fd.append('token', token); fd.append('i', String(i)); fd.append('file', b, 'slide-' + (i + 1) + '.jpg');
      try { await post('put', fd, true); }
      catch (e1) { await wait(900); await post('put', fd, true); }      /* one more try */
    }
    await post('done', {token: token});
  }

  window.fetch = function(input, init){
    var url = '';
    try { url = typeof input === 'string' ? input : ((input && input.url) || ''); } catch (e) {}
    var p = rawFetch.apply(this, arguments);
    if (busy || !CFG.rest || url.indexOf('/ds/v1/create-share-link') === -1) return p;
    return p.then(function(res){
      if (!res || !res.ok || typeof res.clone !== 'function') return res;
      return res.clone().json().then(function(d){
        if (!d || !d.success || !d.share_token || !/^[A-Za-z0-9]{10,40}$/.test(String(d.share_token))) return res;
        busy = true;
        var work = makePics(String(d.share_token));
        var cap = new Promise(function(r){ setTimeout(r, LIMIT_MS); });
        return Promise.race([work, cap]).catch(function(err){ if (window.console) console.warn('[DS share pictures]', err); })
          .then(function(){ busy = false; return res; });
      }, function(){ return res; });
    });
  };
})();
</script>
	<?php
}, 2020 );

} /* function_exists guard */
