<?php
/**
 * Code Snippets export — 22 ds ppt save load variant fix
 *
 * Snippet ID : 720
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-034
 * Modified   : 2026-09-09 02:44:57
 * Load order : 034 of 144
 */
/**
 * Snippet 22: DS PPT Save/Load Variant Fix
 * Run: Everywhere
 * NEW SNIPPET — does not edit Snippet 17 or any existing snippet.
 *
 * BUG THIS FIXES (pre-existing, unrelated to Snippet 21):
 * Snippet 17's own save-ppt interceptor already merges your image/text
 * edits from S.data into what gets saved (see its comment: "the images
 * you pick/generate via this panel live in S.data..."), but it never
 * includes S.choices — the layout/variant you picked per slide via
 * "Change Layout". And its load-ppt interceptor never restores variants
 * either. So a saved-then-reopened deck always falls back to each
 * slide's default layout, which can also make a real, correctly-saved
 * image look "gone" if the default layout doesn't have an image zone.
 *
 * This snippet adds a second fetch interceptor (stacks on top of
 * Snippet 17's own, in either load order — verified) that:
 *  - on save: adds your current variant picks into the saved data
 *  - on load: restores them back into S.choices
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<script>
(function () {
'use strict';
var _of2 = window.fetch;
if (!_of2) return;

window.fetch = function (url, opts) {
  var us = typeof url === 'string' ? url : '';

  if (us.indexOf('/ds/v1/save-ppt') !== -1) {
    var sb = {};
    try { sb = JSON.parse(opts && opts.body ? opts.body : '{}'); } catch (e) {}
    if (window.S && window.S.choices) {
      if (!sb.ppt_data) sb.ppt_data = {};
      sb.ppt_data.slide_variants = window.S.choices;
    }
    var sno = {}; for (var k in (opts || {})) sno[k] = opts[k];
    sno.body = JSON.stringify(sb);
    return _of2.call(this, url, sno);
  }

  if (us.indexOf('/ds/v1/load-ppt') !== -1) {
    return _of2.apply(this, arguments).then(function (r) {
      r.clone().json().then(function (d) {
        if (d && d.data && d.data.slide_variants && window.S) {
          window.S.choices = d.data.slide_variants;
          try { sessionStorage.setItem('dspVPC', JSON.stringify(window.S.choices)); } catch (e) {}
        }
      }).catch(function () {});
      return r;
    });
  }

  return _of2.apply(this, arguments);
};
})();
</script>
    <?php
});
