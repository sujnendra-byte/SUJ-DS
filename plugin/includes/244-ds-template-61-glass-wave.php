<?php
/**
 * Code Snippets export — DS Template 61 — Glass Wave
 *
 * Snippet ID : 1039
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:12:31
 * Load order : 235 of 242
 */
/**
 * DS Template 61 — Glass Wave
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator: dark corporate frosted glass. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Frosted Glass" and "DS Template Glass Kit" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl61_generate)
 *  - Layouts:    its own background scene, colours, fonts and glass shape (JS below); the layouts come from the Glass Kit
 *  - Download:   ds_tpl61_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glasswave', array(
        'name'     => "Glass Wave",
        'fonts'    => array('head' => "Epilogue", 'body' => "Epilogue"),
        'pal'      => array('bg' => '#0A0D26', 'text' => '#FFFFFF', 'accent' => '#A9B4FF', 'accent2' => '#4B58C8'),
        'voice'    => "Assured, smooth and customer-minded. Clear benefit-led sentences with a professional, steady rhythm.",
        'recipe'   => "Open with the promise to the customer, name the need, show how the service works, prove it with numbers, close with a clear invitation to proceed.",
        'generate' => 'ds_tpl61_generate',
        'build'    => 'ds_tpl61_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl61_generate') ) {
function ds_tpl61_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl61_build') ) {
function ds_tpl61_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glasswave">
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 61 - Glass Wave : midnight indigo, slow flowing bands behind pill-cornered frosted glass */
D.glassKit({
  id:'glasswave', name:'Glass Wave', tag:'Midnight indigo · frosted glass · waves',
  desc:'Corporate midnight indigo with slow flowing bands that blur behind pill-cornered frosted glass; a photo can fill the slide or half of it behind the glass. For software, telecom, insurance and investor decks.',
  fonts:{head:'Epilogue', body:'Epilogue', headW:700, bodyW:400, headLh:1.1, gf:'Epilogue:wght@400;500;600;700;800'},
  bg:'#0A0D26', tx:'#FFFFFF', mu:'#BEC3E6', ac:'#A9B4FF', ac2:'#4B58C8', card:'#141838', line:'#2A2F5E', onAc:'#0A0D26',
  r:40, mark:'dot', num:'plain', cover:'r', close:'c', quote:'c', b3:'cols', veil:'#060819', veilOp:0.36,
  ph:{ang:150, stops:[[0, '#4B58C8'], [100, '#0C1030']]},
  scene:function(C, R, k, U){
    var W = U.W;
    function band(y, amp, ph, th, color, op){
      var d = [], n = 40, i, t;
      for (i = 0; i <= n; i++) { t = i / n; d.push([i ? 'L' : 'M', W * t, y + Math.sin(t * 5.4 + ph) * amp]); }
      for (i = n; i >= 0; i--) { t = i / n; d.push(['L', W * t, y + th * (0.6 + 0.4 * Math.sin(t * 3.3 + ph * 1.6)) + Math.sin(t * 4.2 + ph + 1) * amp * 1.2]); }
      d.push(['Z']); C.path(d, {fill:color, op:op});
    }
    var y0 = R.y + R.h * 0.5, p = k * 1.3;
    band(y0 - 250, 70, 0.3 + p, 150, '#2E3AA0', 0.75);
    band(y0 - 110, 60, 1.7 + p, 110, '#A9B4FF', 0.5);
    band(y0 + 10, 80, 2.9 + p, 170, '#4B58C8', 0.6);
    band(y0 + 170, 50, 0.9 + p, 90, '#DDE1FF', 0.35);
    band(y0 + 250, 60, 2.1 + p, 200, '#1B2270', 0.8);
  }
});
})();
</script>
<?php
}, 1071);
