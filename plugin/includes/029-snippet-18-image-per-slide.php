<?php
/**
 * Code Snippets export — Snippet 18 image per slide
 *
 * Snippet ID : 715
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-029
 * Modified   : 2026-09-09 02:42:29
 * Load order : 029 of 144
 */
/**
 * Snippet 18: DS PPT Image Per Slide
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * By default only 'image_text' slides get an image.
 * This snippet generates images for ALL slide types so
 * any layout variant that needs an image has one available.
 *
 * HOW: Intercepts the generate-ppt endpoint response and
 * auto-marks ALL slides as needing an image query.
 * The existing fetchAllSlideImages() JS then fetches them.
 *
 * IMAGE QUERY: If slide has image_query → use it.
 *              Otherwise auto-generate query from title.
 */

if ( ! defined('ABSPATH') ) exit;

// Hook into the generate response to add image queries to all slides
add_filter('ds_ppt_generated_slides', 'ds_ppt_add_image_queries_all', 10, 1);

if ( ! function_exists('ds_ppt_add_image_queries_all') ) {
function ds_ppt_add_image_queries_all($slides) {
    if ( ! is_array($slides) ) return $slides;

    foreach ($slides as $i => $slide) {
        // Skip if already has a query
        if ( ! empty($slide['image_query']) ) continue;

        $title = sanitize_text_field($slide['title'] ?? '');
        $type  = $slide['type'] ?? 'bullet';

        // Build a contextual image query from the slide title
        if ( $title ) {
            $slides[$i]['image_query'] = $title . ' professional business';
        }
    }
    return $slides;
}
}

// Also: add REST endpoint to get image for any slide (not just image_text)
// This piggybacks on the existing fetch-slide-image endpoint logic
// by filtering which slides qualify.

// Filter to allow image fetch for all slide types (snippet 12b checks type === image_text)
// We can't change 12b, so instead we run a wp_footer script that
// also calls fetchAllSlideImages after adjusting which slides need images.

add_action('wp_footer', function() {
    global $post;
    if ( ! is_a($post, 'WP_Post') || ! has_shortcode($post->post_content, 'ds_ai_ppt') ) return;
    ?>
<script>
/* Snippet 18: ensure all slides get image queries so variants can use them */
(function(){
  var _origFetch = window.fetch;
  if (!_origFetch) return;

  // After ppt_data is captured, add image_query to slides missing one
  // and temporarily set type to 'image_text' so fetchAllSlideImages picks them up
  // The original type is saved in _orig_type and restored after images load
  window._dsp18_patchSlides = function(pptData) {
    if (!pptData || !pptData.slides) return;
    pptData.slides.forEach(function(s) {
      if (!s.image_query && s.title) {
        s.image_query = s.title + ' professional';
      }
      // Mark as needing an image (fetchAllSlideImages checks type === image_text)
      if (!s.image_data && s.type !== 'image_text') {
        s._orig_type = s.type;   // save original
        s.type = 'image_text';   // trick fetchAllSlideImages into fetching it
      }
    });
  };

  // After images load, restore types
  window._dsp18_restoreSlides = function(pptData) {
    if (!pptData || !pptData.slides) return;
    pptData.slides.forEach(function(s) {
      if (s._orig_type) {
        s.type = s._orig_type;
        delete s._orig_type;
      }
    });
  };

  // Intercept generate-ppt response to patch slides
  var __f = window.fetch;
  window.fetch = function(url, opts) {
    var us = typeof url === 'string' ? url : '';
    if (us.indexOf('/generate-ppt') !== -1) {
      return __f.apply(this, arguments).then(function(resp) {
        resp.clone().json().then(function(d) {
          if (d && d.ppt_data) {
            window._dsp18_patchSlides(d.ppt_data);
            // After a delay, restore types (images should be loaded by then)
            setTimeout(function() {
              window._dsp18_restoreSlides(d.ppt_data);
            }, 60000); // restore after 60s
          }
        }).catch(function(){});
        return resp;
      });
    }
    return __f.apply(this, arguments);
  };
})();
</script>
    <?php
});
