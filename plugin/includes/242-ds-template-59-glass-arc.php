<?php
/**
 * Code Snippets export — DS Template 59 — Glass Arc
 *
 * Snippet ID : 1037
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:12:30
 * Load order : 233 of 242
 */
/**
 * DS Template 59 — Glass Arc
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator: dark corporate frosted glass. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Frosted Glass" and "DS Template Glass Kit" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl59_generate)
 *  - Layouts:    its own background scene, colours, fonts and glass shape (JS below); the layouts come from the Glass Kit
 *  - Download:   ds_tpl59_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glassarc', array(
        'name'     => "Glass Arc",
        'fonts'    => array('head' => "Geologica", 'body' => "Geologica"),
        'pal'      => array('bg' => '#061315', 'text' => '#FFFFFF', 'accent' => '#7FD6C6', 'accent2' => '#2F7F78'),
        'voice'    => "Calm, clear and forward-looking. Explain simply, connect each point to an outcome, keep a steady professional tone.",
        'recipe'   => "Open with the goal, describe where things stand, explain the approach in a few connected steps, show measured results, close with the next milestone.",
        'generate' => 'ds_tpl59_generate',
        'build'    => 'ds_tpl59_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl59_generate') ) {
function ds_tpl59_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl59_build') ) {
function ds_tpl59_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glassarc">
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 59 - Glass Arc : deep teal-black, large concentric rings behind rounded frosted glass */
D.glassKit({
  id:'glassarc', name:'Glass Arc', tag:'Deep teal · frosted glass · rings',
  desc:'Corporate deep teal-black with large concentric rings that blur behind rounded frosted glass; a photo can fill the slide or half of it behind the glass. For technology, healthcare, sustainability and strategy decks.',
  fonts:{head:'Geologica', body:'Geologica', headW:700, bodyW:300, headLh:1.1, gf:'Geologica:wght@300;400;500;600;700;800'},
  bg:'#061315', tx:'#FFFFFF', mu:'#B5CBC8', ac:'#7FD6C6', ac2:'#2F7F78', card:'#0E2023', line:'#1F3A3C', onAc:'#061315',
  r:30, mark:'dot', num:'ring', cover:'c', close:'l', quote:'r', b3:'cols', veil:'#031012', veilOp:0.36,
  ph:{ang:140, stops:[[0, '#2F7F78'], [100, '#08191B']]},
  scene:function(C, R, k, U){
    var cx = R.x + (k % 2 ? R.w * 0.04 : R.w * 0.96), cy = R.y + (k % 3 === 0 ? R.h * 0.92 : R.h * 0.1), rs = [90, 170, 250, 350, 470, 610], lw = [36, 10, 26, 6, 18, 4], op = [0.75, 0.5, 0.4, 0.6, 0.22, 0.4];
    U.D.glow(C, cx, cy, 620, 520, '#1E5F5A', 0.5);
    for (var j = 5; j >= 0; j--) C.ell(cx - rs[j], cy - rs[j], rs[j] * 2, rs[j] * 2, {line:j % 2 ? '#D7F5EF' : '#7FD6C6', lw:lw[j], lop:op[j]});
    C.ell(cx - 44, cy - 44, 88, 88, {grad:{ang:150, stops:[[0, '#D7F5EF'], [100, '#2F7F78']]}});
  }
});
})();
</script>
<?php
}, 1069);
