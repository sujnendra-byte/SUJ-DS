<?php
/**
 * Code Snippets export — DS Saved Items Card Actions
 *
 * Snippet ID : 817
 * Active     : yes
 * Priority   : 1002
 * Scope      : front-end
 * Tags       : designstager, ds-order-131
 * Modified   : 2026-09-10 13:18:40
 * Load order : 141 of 144
 */
/**
 * DesignStager module: DS Saved Items (single implementation)
 *
 * Execution order 131 of 131 (priority 1002, scope front-end).
 *
 * WHY THIS EXISTS
 *
 * The Saved Items modal was built three separate times, in three modules, each
 * defining its own dsmsBuildModal, dsmsOpenSaved, dsmsLoadImages, dsmsLoadVideos,
 * dsmsLoadDrafts, dsmsDeleteVideo, dsmsDeleteDraft, dsmsClose and
 * dsmsRefreshCapLine. None of them guard their assignments, so whichever module
 * printed its script last on a given page won. Measured on the live site:
 *
 *   /ai-studio/ai-images/   no modal builder, no opener  -> the icon NAVIGATED away
 *   /ai-studio/ai-audio/    opener but no builder        -> broken
 *   /ai-video/              one copy
 *   /ai-presentation/       one copy
 *   /my-generated-images/   a different copy (the only one with audio)
 *
 * That is why the same feature behaved differently on every page.
 *
 * This module is now the single implementation. It builds its own modal
 * (#ds-si-overlay, a distinct id so it cannot collide with the legacy
 * #dsms-modal-overlay), owns window.dsmsOpenSaved, and binds every Saved Items
 * control on any page so the popup opens in place instead of navigating. The
 * old copies are left untouched on disk and simply stop being reached.
 *
 * All four tabs read existing REST routes, which are registered globally and so
 * answer on every page: ds/v1/my-images-page, my-videos, my-audio, my-ppts and
 * my-cap.
 *
 * @package DesignStager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Mint a fresh wp_rest nonce for the logged-in user.
 *
 * Nonces baked into markup die after roughly 12-24 hours, and a page left open
 * past that point hands the browser dead media URLs — the 401 on
 * ds-hub/v1/file/{id}. REST cannot serve the replacement, because REST cookie
 * auth needs a valid nonce to begin with, so this goes through admin-ajax,
 * which authenticates on the cookie alone. No privilege is granted: without a
 * logged-in cookie this returns nothing.
 */
add_action(
	'wp_ajax_ds_fresh_rest_nonce',
	function () {
		wp_send_json_success( array( 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
	}
);

/**
 * Card actions and media repair, printed in wp_head so they exist before the
 * user can click anything. The image cards served by ds/v1/my-images-page call
 * dsmyDL / dsmyDelete from inline onclick attributes, and those were only ever
 * defined on the My Creations page — everywhere else the button threw
 * "dsmyDL is not defined".
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_user_logged_in() || is_admin() ) {
			return;
		}
		$nonce    = wp_create_nonce( 'wp_rest' );
		$rest     = trailingslashit( rest_url( 'ds/v1' ) );
		$rest_hub = trailingslashit( rest_url( 'ds-hub/v1' ) );
		?>
<script data-no-optimize="1" data-no-defer="1">
(function(){
  var NONCE    = <?php echo wp_json_encode( $nonce ); ?>;
  var AJAX     = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
  var REST     = <?php echo wp_json_encode( $rest ); ?>;
  var REST_HUB = <?php echo wp_json_encode( $rest_hub ); ?>;

  window.dsSI = window.dsSI || {};
  window.dsSI.nonce   = function(){ return NONCE; };
  window.dsSI.rest    = REST;
  window.dsSI.restHub = REST_HUB;

  /* Our REST routes need the nonce, and an <a href>, <img> or <video> cannot
     send a header — so it has to travel in the query string. */
  function withNonce(url){
    if(!url) return url;
    if(url.indexOf('/wp-json/') === -1 && url.indexOf('rest_route=') === -1) return url;
    if(url.indexOf('_wpnonce=') !== -1) return url;
    return url + (url.indexOf('?') === -1 ? '?' : '&') + '_wpnonce=' + encodeURIComponent(NONCE);
  }
  window.dsSI.withNonce = withNonce;

  function clickAway(a){
    document.body.appendChild(a);
    a.click();
    setTimeout(function(){ if(a.parentNode) a.parentNode.removeChild(a); }, 400);
  }

  /* ---------------- Download ---------------- */
  window.dsmyDL = function(url, fileId){
    if(!url) return;
    /* The proxy 302s to a presigned R2 URL that already carries
       response-content-disposition=attachment, so the browser saves the file
       and stays on the page. No target="_blank": never needed, and it only
       left an empty tab behind. */
    if (fileId) {
      var ra = document.createElement('a');
      ra.href = withNonce(url);
      ra.setAttribute('download', '');
      clickAway(ra);
      return;
    }
    var fn = 'designstager-ai-' + Date.now() + '.png';
    if (url.indexOf('data:') === 0) {
      var da = document.createElement('a');
      da.href = url; da.download = fn;
      clickAway(da);
      return;
    }
    var a = document.createElement('a');
    a.href = REST + 'download?url=' + encodeURIComponent(url) + '&_wpnonce=' + encodeURIComponent(NONCE);
    a.download = fn; a.target = '_self';
    clickAway(a);
  };

  /* ---------------- Delete (image cards) ---------------- */
  if (typeof window.dsmyDelete !== 'function') {
    window.dsmyDelete = function(aid, url, fileId){
      if(!aid && !fileId) return;
      if(!confirm('Delete this image permanently? This cannot be undone.')) return;
      var req = fileId
        ? fetch(REST_HUB + 'delete/' + fileId, {method:'POST', headers:{'X-WP-Nonce':NONCE}, credentials:'same-origin'})
        : fetch(REST + 'delete-image', {method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE}, credentials:'same-origin', body:JSON.stringify({attachment_id:aid})});
      req.then(function(r){ return r.json(); })
      .then(function(d){
        if (d && d.success === true) {
          document.querySelectorAll('.dsmy-card').forEach(function(c){
            var oc = c.getAttribute('onclick') || '';
            if ((fileId && oc.indexOf(',' + fileId + ',') !== -1) || (url && oc.indexOf(url) !== -1)) { c.remove(); }
          });
          if (typeof window.dsmyLBClose === 'function') { window.dsmyLBClose(); }
          if (window.dsSI && window.dsSI.refreshCap) { window.dsSI.refreshCap(); }
        } else {
          alert('Could not delete: ' + ((d && (d.error || d.message)) || 'Unknown error'));
        }
      })
      .catch(function(){ alert('Delete failed. Please try again.'); });
    };
  }

  /* Lightbox / share / marketplace: the full versions drive modals that exist
     only on the My Creations page. Do the useful part rather than throw. */
  if (typeof window.dsmyOpenLB !== 'function') {
    window.dsmyOpenLB = function(url){ if (url) { window.open(withNonce(url), '_blank', 'noopener'); } };
  }
  if (typeof window.dsmyOpenShare !== 'function') {
    window.dsmyOpenShare = function(url, prompt, aid){
      var link = (aid && aid > 0) ? (location.origin + '/?ds_share_img=' + aid) : withNonce(url);
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(function(){ alert('Share link copied to clipboard.'); })
          .catch(function(){ window.prompt('Copy this share link:', link); });
      } else { window.prompt('Copy this share link:', link); }
    };
  }
  if (typeof window.dsmyOpenMkt !== 'function') {
    window.dsmyOpenMkt = function(){ alert('Open My Creations to submit this to the Marketplace.'); };
  }

  /* ---------------- Stale-nonce repair ---------------- */
  function healNonce(root){
    if (!root || !root.querySelectorAll) return;
    var sel = 'video[src*="ds-hub/v1/file/"], source[src*="ds-hub/v1/file/"], audio[src*="ds-hub/v1/file/"],' +
              ' img[src*="ds-hub/v1/file/"], a[href*="ds-hub/v1/file/"]';
    root.querySelectorAll(sel).forEach(function(el){
      var attr = el.hasAttribute('src') ? 'src' : 'href';
      var val  = el.getAttribute(attr) || '';
      if (val.indexOf('ds-hub/v1/file/') === -1) return;
      var fixed = val.indexOf('_wpnonce=') !== -1
        ? val.replace(/([?&])_wpnonce=[^&]*/, '$1_wpnonce=' + encodeURIComponent(NONCE))
        : withNonce(val);
      if (fixed !== val) {
        el.setAttribute(attr, fixed);
        if ((el.tagName === 'VIDEO' || el.tagName === 'AUDIO') && typeof el.load === 'function') { el.load(); }
      }
    });
  }
  window.dsSI.heal = function(root){ try { healNonce(root || document); } catch (e) {} };

  var refreshing = null;
  function refreshNonce(){
    if (refreshing) return refreshing;
    refreshing = fetch(AJAX + '?action=ds_fresh_rest_nonce', { credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (d && d.success && d.data && d.data.nonce) { NONCE = d.data.nonce; window.dsSI.heal(); }
        return NONCE;
      })
      .catch(function(){ return NONCE; })
      .then(function(n){ refreshing = null; return n; });
    return refreshing;
  }

  document.addEventListener('error', function(ev){
    var el = ev.target;
    if (!el || !el.tagName) return;
    if (el.tagName !== 'VIDEO' && el.tagName !== 'IMG' && el.tagName !== 'SOURCE' && el.tagName !== 'AUDIO') return;
    var val = el.getAttribute('src') || '';

    /* Records pointing at /wp-content/uploads/*.mp4 files that were never
       migrated to R2 and are no longer on disk — measured at 13 of 21 videos
       in this account. Nothing can bring those back; say so plainly. */
    if (val.indexOf('/wp-content/uploads/') !== -1 && (el.tagName === 'VIDEO' || el.tagName === 'AUDIO')) {
      if (el.__dsGone) return;
      el.__dsGone = true;
      var note = document.createElement('div');
      note.style.cssText = 'display:flex;align-items:center;justify-content:center;text-align:center;min-height:110px;' +
        'padding:14px;color:#94a3b8;font-size:12px;line-height:1.5;background:#111;border-radius:8px;';
      note.textContent = 'This file is no longer stored on the server.';
      if (el.parentNode) { el.parentNode.replaceChild(note, el); }
      return;
    }
    if (val.indexOf('ds-hub/v1/file/') === -1) return;
    if (el.__dsRetried) return;
    el.__dsRetried = true;
    refreshNonce().then(function(){
      if ((el.tagName === 'VIDEO' || el.tagName === 'AUDIO') && typeof el.load === 'function') { el.load(); }
    });
  }, true);

  /* Media loading is staggered: the host tolerates roughly 4 concurrent
     requests, and a gallery asking for 13 videos inside one second (measured)
     gets 503s back from the edge. */
  var MAX_INFLIGHT = 2, inflight = 0, waiting = [];
  function pump(){
    while (inflight < MAX_INFLIGHT && waiting.length) {
      var v = waiting.shift();
      if (!v || !v.isConnected) continue;
      inflight++;
      (function(el){
        var done = function(){
          el.removeEventListener('loadedmetadata', done);
          el.removeEventListener('error', done);
          inflight--; pump();
        };
        el.addEventListener('loadedmetadata', done);
        el.addEventListener('error', done);
        try { el.preload = 'metadata'; el.load(); } catch (e) { done(); }
        setTimeout(done, 12000);
      })(v);
    }
  }
  var io = null;
  function queue(v){
    if (!v || v.__dsQueued) return;
    v.__dsQueued = true; v.preload = 'none';
    waiting.push(v); pump();
  }
  try {
    io = new IntersectionObserver(function(es){
      es.forEach(function(e){ if (e.isIntersecting) { io.unobserve(e.target); queue(e.target); } });
    }, { rootMargin: '300px' });
  } catch (e) {}
  window.dsSI.watchMedia = function(root){
    if (!root || !root.querySelectorAll) return;
    root.querySelectorAll('video,audio').forEach(function(v){
      if (v.__dsQueued || v.__dsWatched) return;
      v.__dsWatched = true; v.preload = 'none';
      if (io) { io.observe(v); } else { queue(v); }
    });
  };

  function sweep(){ window.dsSI.heal(document); window.dsSI.watchMedia(document); }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', sweep); } else { sweep(); }
  try {
    new MutationObserver(function(muts){
      for (var i = 0; i < muts.length; i++) {
        var a = muts[i].addedNodes;
        for (var j = 0; j < a.length; j++) {
          if (a[j].nodeType === 1) { window.dsSI.heal(a[j]); window.dsSI.watchMedia(a[j]); }
        }
      }
    }).observe(document.documentElement, { childList: true, subtree: true });
  } catch (e) {}
})();
</script>
		<?php
	},
	1
);

/**
 * The single Saved Items modal.
 *
 * Printed in the footer so it can take ownership of window.dsmsOpenSaved after
 * the three legacy copies have assigned theirs, and re-asserted on load in case
 * a deferred bundle lands later.
 */
add_action(
	'wp_footer',
	function () {
		if ( ! is_user_logged_in() || is_admin() ) {
			return;
		}
		$rest = trailingslashit( rest_url( 'ds/v1' ) );
		?>
<script data-no-optimize="1" data-no-defer="1">
(function(){
  var PAGE = 12;
  var REST = <?php echo wp_json_encode( $rest ); ?>;
  var N  = function(){ return (window.dsSI && window.dsSI.nonce) ? window.dsSI.nonce() : ''; };
  var wn = function(u){ return (window.dsSI && window.dsSI.withNonce) ? window.dsSI.withNonce(u) : u; };

  var TABS = [
    { key:'images', label:'Images' },
    { key:'videos', label:'Videos' },
    { key:'audios', label:'Audios' },
    { key:'drafts', label:'Presentations' }
  ];
  var state = {};
  function resetState(){
    TABS.forEach(function(t){ state[t.key] = { offset:0, total:null, loading:false, loaded:false }; });
  }
  resetState();

  function esc(s){
    return String(s == null ? '' : s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function api(path){
    return fetch(REST + path, { headers:{ 'X-WP-Nonce': N() }, credentials:'same-origin' })
      .then(function(r){ return r.json(); });
  }

  function css(){
    if (document.getElementById('ds-si-css')) return;
    var s = document.createElement('style');
    s.id = 'ds-si-css';
    s.textContent = [
      '#ds-si-overlay{position:fixed;inset:0;background:rgba(2,6,16,.78);z-index:2147483000;display:none;overflow:auto;padding:40px 16px;}',
      '#ds-si-overlay.open{display:block;}',
      '#ds-si-panel{max-width:1240px;margin:0 auto;background:#0b1120;border-radius:14px;padding:28px 32px 34px;',
        'color:#fff;font-family:inherit;box-shadow:0 30px 80px rgba(0,0,0,.55);}',
      '.ds-si-head{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;}',
      '.ds-si-head h2{font-size:30px;font-weight:700;margin:0;color:#fff;letter-spacing:-.01em;line-height:1.2;}',
      '.ds-si-right{display:flex;flex-direction:column;align-items:flex-end;gap:6px;}',
      '.ds-si-x{background:none;border:0;color:#fff;font-size:28px;line-height:1;cursor:pointer;padding:0 2px;opacity:.9;}',
      '.ds-si-x:hover{opacity:1;}',
      '.ds-si-used{font-size:13px;color:#c7cedb;white-space:nowrap;}',
      '.ds-si-tabs{display:flex;gap:14px;flex-wrap:wrap;margin:26px 0 30px;}',
      '.ds-si-tab{appearance:none;background:transparent;border:1px solid #e8492b;color:#fff;font-size:15px;',
        'font-family:inherit;font-weight:500;text-transform:none;letter-spacing:normal;padding:13px 30px;',
        'border-radius:7px;cursor:pointer;transition:background .15s;min-width:150px;}',
      '.ds-si-tab:hover{background:rgba(232,73,43,.14);}',
      '.ds-si-tab.on{background:#e8492b;border-color:#e8492b;font-weight:600;}',
      '.ds-si-pane{display:none;}','.ds-si-pane.on{display:block;}',
      '.ds-si-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;}',
      '@media (max-width:1080px){.ds-si-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}',
      '@media (max-width:800px){.ds-si-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}',
      '@media (max-width:520px){.ds-si-grid{grid-template-columns:1fr;}.ds-si-tab{flex:1 1 45%;min-width:0;padding:12px 8px;}',
        '#ds-si-panel{padding:20px 16px 26px;}.ds-si-head h2{font-size:23px;}}',
      '.ds-si-card{background:#0f1629;border:1px solid #1b2540;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;}',
      '.ds-si-card video,.ds-si-card>img{width:100%;display:block;aspect-ratio:16/9;object-fit:cover;background:#070b14;}',
      '.ds-si-card audio{width:100%;padding:14px 12px 4px;}',
      '.ds-si-body{padding:11px 13px;flex:1;}',
      '.ds-si-title{color:#e5e7eb;font-weight:600;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}',
      '.ds-si-sub{color:#7c879c;font-size:11px;margin-top:3px;}',
      '.ds-si-acts{display:flex;gap:8px;padding:0 13px 13px;}',
      '.ds-si-btn{flex:1;text-align:center;background:#141d33;border:1px solid #22304f;color:#9db4ff;text-decoration:none;',
        'font-size:12px;font-weight:600;padding:9px 6px;border-radius:6px;cursor:pointer;font-family:inherit;}',
      '.ds-si-btn:hover{background:#1b2745;}',
      '.ds-si-btn.del{color:#f27272;border-color:#4a2230;background:#25131a;}',
      '.ds-si-btn.del:hover{background:#33191f;}',
      '.ds-si-more{display:flex;justify-content:center;padding:34px 0 4px;}',
      '.ds-si-more button{background:#3b6ef5;border:0;color:#fff;font-family:inherit;font-size:15px;font-weight:500;',
        'padding:15px 44px;border-radius:7px;cursor:pointer;}',
      '.ds-si-more button:hover{background:#2f5ede;}',
      '.ds-si-more button[disabled]{opacity:.6;cursor:default;}',
      '.ds-si-msg{color:#8b95a7;font-size:14px;padding:34px 0;}',
      /* The Images tab injects cards rendered by ds/v1/my-images-page. Their
         styles live in the [ds_my_generated_images] shortcode, which only runs
         on the My Creations page — so everywhere else the cards arrived
         unstyled: no thumbnail, giant blue default buttons, raw label text.
         These are the same rules, carried with the modal. */
      '.ds-si-grid .dsmy-card{position:relative;border-radius:10px;overflow:hidden;background:#111;cursor:pointer;aspect-ratio:16/9;border:1px solid #1b2540;}',
      '.ds-si-grid .dsmy-card img{width:100%;height:100%;object-fit:cover;background:#111;display:block;}',
      '.ds-si-grid .dsmy-card-tag{position:absolute;top:6px;left:6px;background:rgba(0,0,0,.7);color:#9aa7bd;font-size:9px;padding:2px 6px;border-radius:3px;}',
      '.ds-si-grid .dsmy-card-sub{position:absolute;top:6px;right:6px;background:rgba(37,99,235,.85);color:#fff;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;}',
      '.ds-si-grid .dsmy-card-acc{background:rgba(22,163,74,.9);}',
      '.ds-si-grid .dsmy-card-exp{position:absolute;bottom:6px;left:6px;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;color:#fff;display:flex;align-items:center;gap:3px;}',
      '.ds-si-grid .dsmy-card-actions{position:absolute;bottom:6px;right:6px;display:flex;gap:2px;opacity:.95;}',
      '.ds-si-grid .dsmy-btn-sm{width:26px!important;height:26px!important;min-width:0!important;border-radius:5px!important;',
        'background:transparent!important;border:none!important;outline:none!important;box-shadow:none!important;',
        'padding:0!important;margin:0!important;cursor:pointer;display:flex;align-items:center;justify-content:center;',
        'color:#fff;font-size:0!important;line-height:0!important;filter:drop-shadow(0 1px 3px rgba(0,0,0,.85));}',
      '.ds-si-grid .dsmy-btn-sm:hover{background:rgba(255,255,255,.12)!important;}',
      '.ds-si-grid .dsmy-card-expired-ph{display:flex;align-items:center;justify-content:center;height:100%;padding:14px;',
        'text-align:center;color:#94a3b8;font-size:11px;line-height:1.5;}',
      '.ds-si-grid .ds-img-spin{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1;pointer-events:none;}',
      '.ds-si-grid .ds-spin{width:18px;height:18px;border:2px solid #1a1a1a;border-top-color:#2563eb;border-radius:50%;animation:dsSpin .7s linear infinite;}',
      '@keyframes dsSpin{to{transform:rotate(360deg)}}'
    ].join('');
    document.head.appendChild(s);
  }

  function build(){
    if (document.getElementById('ds-si-overlay')) return;
    css();
    var o = document.createElement('div');
    o.id = 'ds-si-overlay';
    o.innerHTML =
      '<div id="ds-si-panel">' +
        '<div class="ds-si-head"><h2>Saved Items</h2>' +
          '<div class="ds-si-right">' +
            '<button type="button" class="ds-si-x" aria-label="Close">&times;</button>' +
            '<div class="ds-si-used" id="ds-si-used">&nbsp;</div>' +
          '</div>' +
        '</div>' +
        '<div class="ds-si-tabs" id="ds-si-tabs"></div>' +
        '<div id="ds-si-panes"></div>' +
      '</div>';
    document.body.appendChild(o);

    var tabs = o.querySelector('#ds-si-tabs');
    var panes = o.querySelector('#ds-si-panes');
    TABS.forEach(function(t){
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'ds-si-tab';
      b.setAttribute('data-k', t.key); b.textContent = t.label;
      b.onclick = function(){ show(t.key); };
      tabs.appendChild(b);

      var p = document.createElement('div');
      p.className = 'ds-si-pane'; p.setAttribute('data-k', t.key);
      p.innerHTML = '<div class="ds-si-grid"></div><div class="ds-si-more"></div>';
      panes.appendChild(p);
    });

    o.querySelector('.ds-si-x').onclick = close;
    o.addEventListener('click', function(e){ if (e.target === o) close(); });
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape') {
        var ov = document.getElementById('ds-si-overlay');
        if (ov && ov.classList.contains('open')) close();
      }
    });
  }

  function close(){
    var o = document.getElementById('ds-si-overlay');
    if (!o) return;
    o.classList.remove('open');
    o.querySelectorAll('video,audio').forEach(function(m){ try { m.pause(); } catch (e) {} });
  }

  function paneOf(k){ return document.querySelector('#ds-si-panes .ds-si-pane[data-k="' + k + '"]'); }
  function gridOf(k){ var p = paneOf(k); return p ? p.querySelector('.ds-si-grid') : null; }
  function moreOf(k){ var p = paneOf(k); return p ? p.querySelector('.ds-si-more') : null; }

  function show(key){
    var o = document.getElementById('ds-si-overlay');
    if (!o) return;
    o.querySelectorAll('.ds-si-tab').forEach(function(b){ b.classList.toggle('on', b.getAttribute('data-k') === key); });
    o.querySelectorAll('.ds-si-pane').forEach(function(p){ p.classList.toggle('on', p.getAttribute('data-k') === key); });
    if (!state[key].loaded) { load(key); }
  }

  function renderMore(key){
    var box = moreOf(key), st = state[key];
    if (!box) return;
    if (st.total !== null && st.offset >= st.total) { box.innerHTML = ''; return; }
    box.innerHTML = '<button type="button">Load More</button>';
    box.firstChild.onclick = function(){ load(key); };
  }

  function load(key){
    var st = state[key];
    if (st.loading) return;
    st.loading = true;
    var grid = gridOf(key), box = moreOf(key);
    if (!grid) { st.loading = false; return; }
    if (!st.offset) { grid.innerHTML = '<div class="ds-si-msg">Loading&hellip;</div>'; }
    var btn = box ? box.querySelector('button') : null;
    if (btn) { btn.disabled = true; btn.textContent = 'Loading…'; }

    var path = {
      images: 'my-saved-images',
      videos: 'my-videos?offset=0&limit=500',
      audios: 'my-audio?offset=0&limit=500',
      drafts: 'my-ppts?offset=' + st.offset + '&limit=' + PAGE
    }[key];

    api(path).then(function(d){
      if (!st.offset) { grid.innerHTML = ''; }
      if      (key === 'images') { addImages(grid, d, st); }
      else if (key === 'videos') { addList(grid, (d && d.videos) || [], st, videoCard); }
      else if (key === 'audios') { addList(grid, (d && d.audio)  || [], st, audioCard); }
      else                       { addDrafts(grid, d, st); }

      st.loaded = true; st.loading = false;
      if (!grid.children.length) { grid.innerHTML = '<div class="ds-si-msg">Nothing saved here yet.</div>'; }
      renderMore(key);
      if (window.dsSI) { window.dsSI.heal(grid); window.dsSI.watchMedia(grid); }
    }).catch(function(){
      st.loading = false;
      if (!st.offset) {
        grid.innerHTML = '<div class="ds-si-msg">Could not load these items. ' +
          '<span style="text-decoration:underline;cursor:pointer" class="ds-si-retry">Retry</span></div>';
        var r = grid.querySelector('.ds-si-retry');
        if (r) { r.onclick = function(){ load(key); }; }
      }
      renderMore(key);
    });
  }

  /* Images arrive as server-rendered cards; their buttons call dsmyDL and
     dsmyDelete, which the head script defines on every page. */
  /* Each card ships its <img> as a 1x1 transparent GIF with the real URL in
     data-fallback; the page that renders them normally swaps it in later. That
     resolver lives in the shortcode too, so without this the thumbnails never
     appear and the spinner turns forever. */
  function resolveThumbs(root){
    root.querySelectorAll('img[data-fallback]').forEach(function(img){
      if (img.__dsResolved) return;
      img.__dsResolved = true;
      var card = img.closest('.dsmy-card');
      var stop = function(){
        if (!card) return;
        var sp = card.querySelector('.ds-img-spin');
        if (sp && sp.parentNode) sp.parentNode.removeChild(sp);
      };
      img.addEventListener('load', stop);
      img.addEventListener('error', stop);
      var url = img.getAttribute('data-fallback') || '';
      if (url) { img.src = (window.dsSI && window.dsSI.withNonce) ? window.dsSI.withNonce(url) : url; }
      else { stop(); }
      /* Never leave a spinner running if the image simply never fires. */
      setTimeout(stop, 15000);
    });
  }

  /* The Images tab must show ONLY saved files, the same test the cap counter
     uses. It previously called ds/v1/my-images-page, which returns every
     non-expired generation regardless of status — so unsaved items (e.g. a
     marketplace submission) appeared in the list but were never counted,
     which is why the tab looked like 8 while the counter said 7.
     ds/v1/my-saved-images applies the status === 'saved' filter server-side
     and returns the whole set at once, so it is paged here exactly the way
     videos and audio already are. */
  function addImages(grid, d, st){
    var tmp = document.createElement('div');
    tmp.innerHTML = (d && d.html) || '';
    var all = [].slice.call(tmp.children);
    st.total = all.length;
    var slice = all.slice(st.offset, st.offset + PAGE);
    slice.forEach(function(c){ grid.appendChild(c); });
    st.offset = st.offset + slice.length;
    resolveThumbs(grid);
  }

  /* Videos and audio have no server-side filter for "saved", so the list comes
     back once and is paged here. */
  function addList(grid, all, st, render){
    var saved = all.filter(function(x){ return x && x.exp_badge === 'saved'; });
    st.total = saved.length;
    var slice = saved.slice(st.offset, st.offset + PAGE);
    slice.forEach(function(item){ grid.insertAdjacentHTML('beforeend', render(item)); });
    st.offset += slice.length;
    wire(grid);
  }

  function addDrafts(grid, d, st){
    var list = (d && d.drafts) || [];
    list.forEach(function(p){ grid.insertAdjacentHTML('beforeend', draftCard(p)); });
    st.total  = (d && typeof d.total === 'number') ? d.total : st.total;
    st.offset = (d && typeof d.next_offset === 'number') ? d.next_offset : (st.offset + list.length);
    wire(grid);
  }

  function videoCard(v){
    var url = wn(v.url || '');
    return '<div class="ds-si-card">' +
      '<video controls preload="none" src="' + esc(url) + '"></video>' +
      '<div class="ds-si-body"><div class="ds-si-title">' + esc(String(v.prompt || 'Video').substring(0, 60)) + '</div>' +
      '<div class="ds-si-sub">Saved</div></div>' +
      '<div class="ds-si-acts"><a class="ds-si-btn" href="' + esc(url) + '" download>Download</a>' +
      (v.file_id ? '<button type="button" class="ds-si-btn del" data-del="' + esc(v.file_id) + '">Delete</button>' : '') +
      '</div></div>';
  }

  function audioCard(a){
    var url = wn(a.url || '');
    return '<div class="ds-si-card">' +
      '<audio controls preload="none" src="' + esc(url) + '"></audio>' +
      '<div class="ds-si-body"><div class="ds-si-title">' + esc(String(a.prompt || 'Audio').substring(0, 60)) + '</div>' +
      '<div class="ds-si-sub">Saved</div></div>' +
      '<div class="ds-si-acts"><a class="ds-si-btn" href="' + esc(url) + '" download>Download</a>' +
      (a.file_id ? '<button type="button" class="ds-si-btn del" data-del="' + esc(a.file_id) + '">Delete</button>' : '') +
      '</div></div>';
  }

  function draftCard(p){
    var thumb = (p.first_slide && p.first_slide.image_data) ? p.first_slide.image_data : '';
    var when  = p.updated ? new Date(p.updated * 1000).toLocaleDateString() : '';
    var head  = thumb
      ? '<img src="' + esc(thumb) + '" alt="">'
      : '<div style="aspect-ratio:16/9;background:#141d33;display:flex;align-items:center;justify-content:center;' +
        'color:#5b6a86;font-size:12px;padding:10px;text-align:center;">' +
        esc(String(p.title || 'Presentation').substring(0, 40)) + '</div>';
    return '<div class="ds-si-card">' + head +
      '<div class="ds-si-body"><div class="ds-si-title">' + esc(p.title || 'Presentation') + '</div>' +
      '<div class="ds-si-sub">' + esc(p.slides_count || 0) + ' slides &middot; ' + esc(when) + '</div></div>' +
      '<div class="ds-si-acts">' +
      '<button type="button" class="ds-si-btn" data-open-ppt="' + esc(p.id) + '">Open</button>' +
      '<button type="button" class="ds-si-btn del" data-del-ppt="' + esc(p.id) + '">Delete</button>' +
      '</div></div>';
  }

  function wire(grid){
    grid.querySelectorAll('[data-del]').forEach(function(b){
      if (b.__w) return; b.__w = true;
      b.onclick = function(){
        if (!confirm('Delete this permanently? This cannot be undone.')) return;
        b.disabled = true;
        fetch(window.dsSI.restHub + 'delete/' + b.getAttribute('data-del'),
              { method:'POST', headers:{ 'X-WP-Nonce': N() }, credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if (d && d.success !== false) { var c = b.closest('.ds-si-card'); if (c) c.remove(); refreshCap(); }
          else { b.disabled = false; alert('Could not delete that item.'); }
        })
        .catch(function(){ b.disabled = false; alert('Could not delete that item.'); });
      };
    });
    grid.querySelectorAll('[data-del-ppt]').forEach(function(b){
      if (b.__w) return; b.__w = true;
      b.onclick = function(){
        if (!confirm('Delete this presentation permanently?')) return;
        b.disabled = true;
        fetch(REST + 'delete-ppt', { method:'POST',
          headers:{ 'Content-Type':'application/json', 'X-WP-Nonce': N() }, credentials:'same-origin',
          body: JSON.stringify({ ppt_id: b.getAttribute('data-del-ppt') }) })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if (d && d.success !== false) { var c = b.closest('.ds-si-card'); if (c) c.remove(); refreshCap(); }
          else { b.disabled = false; alert('Could not delete that presentation.'); }
        })
        .catch(function(){ b.disabled = false; alert('Could not delete that presentation.'); });
      };
    });
    grid.querySelectorAll('[data-open-ppt]').forEach(function(b){
      if (b.__w) return; b.__w = true;
      b.onclick = function(){
        location.href = location.origin + '/ai-presentation/?ds_load_ppt=' +
          encodeURIComponent(b.getAttribute('data-open-ppt'));
      };
    });
  }

  function refreshCap(){
    api('my-cap').then(function(d){
      var el = document.getElementById('ds-si-used');
      if (el && d && typeof d.used !== 'undefined') {
        el.textContent = d.used + '/' + d.total + ' Saved items Used';
      }
    }).catch(function(){});
  }
  window.dsSI = window.dsSI || {};
  window.dsSI.refreshCap = refreshCap;

  function open(){
    build();
    resetState();
    var o = document.getElementById('ds-si-overlay');
    o.classList.add('open');
    o.scrollTop = 0;
    /* If a legacy copy also opened its own modal, get it out of the way. */
    var legacy = document.getElementById('dsms-modal-overlay');
    if (legacy) { legacy.classList.remove('open'); legacy.style.display = 'none'; }
    /* Clear any previously rendered cards so a reopen shows fresh data. */
    TABS.forEach(function(t){ var g = gridOf(t.key); if (g) g.innerHTML = ''; var m = moreOf(t.key); if (m) m.innerHTML = ''; });
    refreshCap();
    show('images');
    return false;
  }

  /* This module owns the opener. The legacy copies assign theirs without any
     guard, so claim it here and again after load in case a deferred bundle
     lands later. */
  function claim(){ window.dsmsOpenSaved = open; }
  claim();
  window.addEventListener('load', function(){ setTimeout(claim, 0); setTimeout(claim, 1200); });
  window.dsSI.open  = open;
  window.dsSI.close = close;

  /* Every Saved Items control opens the popup in place — including the ones
     that were plain links to /my-generated-images/ on pages that had no modal
     at all. Visiting that page directly still works. */
  function savedControl(el){
    for (var n = el; n && n !== document.body; n = n.parentElement) {
      if (n.nodeType !== 1) continue;
      var oc = n.getAttribute ? (n.getAttribute('onclick') || '') : '';
      if (oc.indexOf('dsmsOpenSaved') !== -1) return { el:n, kind:'popup' };
      var t = n.getAttribute ? (n.getAttribute('title') || '') : '';
      if (/saved\s*(files|items)?/i.test(t)) return { el:n, kind:'popup' };
      if (n.id === 'dsmc-capbar') return { el:n, kind:'popup' };
      var href = n.getAttribute ? (n.getAttribute('href') || '') : '';
      if (href && /\/my-generated-images\/?($|[?#])/.test(href)) return { el:n, kind:'page' };
    }
    return null;
  }
  document.addEventListener('click', function(e){
    if (e.defaultPrevented) return;
    var hit = savedControl(e.target);
    if (!hit) return;
    e.preventDefault();
    e.stopPropagation();
    if (hit.kind === 'page') {
      /* Only the "My Creations" link (href -> /my-generated-images/) goes to
         the page. Already being on that page means there is nothing to do —
         navigating again would just reload it. */
      if (!/^\/my-creations\/?$/.test(window.location.pathname)) {
        window.location.href = window.location.origin + '/my-creations/';
      }
      return;
    }
    /* Saved Files / Saved Items / the cap bar keep opening the popup in place. */
    open();
  }, true);
})();
</script>
		<?php
	},
	99
);
