<?php
/**
 * Code Snippets export — DS Template 58 — Glass Beam
 *
 * Snippet ID : 1036
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:12:30
 * Load order : 232 of 242
 */
/**
 * DS Template 58 — Glass Beam
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator: dark corporate frosted glass. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Frosted Glass" and "DS Template Glass Kit" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl58_generate)
 *  - Layouts:    its own background scene, colours, fonts and glass shape (JS below); the layouts come from the Glass Kit
 *  - Download:   ds_tpl58_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glassbeam', array(
        'name'     => "Glass Beam",
        'fonts'    => array('head' => "Schibsted Grotesk", 'body' => "Schibsted Grotesk"),
        'pal'      => array('bg' => '#0F1012', 'text' => '#FFFFFF', 'accent' => '#D8B978', 'accent2' => '#8A734A'),
        'voice'    => "Composed, discreet and authoritative. Short formal sentences, precise terms, every claim backed by a fact.",
        'recipe'   => "Open with the matter at hand, set out the background, present the advice or offer point by point, state the terms and figures, close with the recommended course.",
        'generate' => 'ds_tpl58_generate',
        'build'    => 'ds_tpl58_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl58_generate') ) {
function ds_tpl58_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl58_build') ) {
function ds_tpl58_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glassbeam">
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 58 - Glass Beam : charcoal, slanted beams of soft gold light behind sharp-cornered frosted glass */
D.glassKit({
  id:'glassbeam', name:'Glass Beam', tag:'Charcoal · frosted glass · gold beams',
  desc:'Corporate charcoal with slanted beams of soft gold light that blur behind sharp-cornered frosted glass; a photo can fill the slide or half of it behind the glass. For law, advisory, luxury services and board decks.',
  fonts:{head:'Schibsted Grotesk', body:'Schibsted Grotesk', headW:700, bodyW:400, headLh:1.1, gf:'Schibsted+Grotesk:wght@400;500;600;700;800'},
  bg:'#0F1012', tx:'#FFFFFF', mu:'#C4BFB3', ac:'#D8B978', ac2:'#8A734A', card:'#1A1B1E', line:'#33312C', onAc:'#0F1012', rim:'#F3E6C8',
  r:0, mark:'line', num:'plain', caps:true, stag:false, cover:'r', close:'l', quote:'c', b3:'rows', veil:'#08080A', veilOp:0.4,
  ph:{ang:150, stops:[[0, '#8A734A'], [100, '#141416']]},
  scene:function(C, R, k, U){
    var H = U.H, K = U.K, ws = [110, 26, 12, 170, 48, 18], ops = [0.3, 0.55, 0.8, 0.12, 0.22, 0.6], x = R.x - 60 + (k % 3) * 70, sl = 300;
    U.D.glow(C, R.x + R.w * 0.5, R.y + R.h * 0.4, 520, 360, '#6E5A34', 0.35);
    for (var j = 0; j < 6; j++) {
      K.poly(C, [[x + sl, 0], [x + sl + ws[j], 0], [x + ws[j], H], [x, H]], {fill:j % 3 === 2 ? '#F3E6C8' : '#D8B978', op:ops[j]});
      x += ws[j] + [150, 60, 210, 120, 90, 0][j] * (R.w / 900);
    }
  }
});
})();
</script>
<?php
}, 1068);
