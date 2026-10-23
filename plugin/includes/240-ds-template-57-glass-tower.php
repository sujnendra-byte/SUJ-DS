<?php
/**
 * Code Snippets export — DS Template 57 — Glass Tower
 *
 * Snippet ID : 1035
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:12:29
 * Load order : 231 of 242
 */
/**
 * DS Template 57 — Glass Tower
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator: dark corporate frosted glass. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Frosted Glass" and "DS Template Glass Kit" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl57_generate)
 *  - Layouts:    its own background scene, colours, fonts and glass shape (JS below); the layouts come from the Glass Kit
 *  - Download:   ds_tpl57_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glasstower', array(
        'name'     => "Glass Tower",
        'fonts'    => array('head' => "Hanken Grotesk", 'body' => "Hanken Grotesk"),
        'pal'      => array('bg' => '#08111F', 'text' => '#FFFFFF', 'accent' => '#8FC1F2', 'accent2' => '#3E6FA3'),
        'voice'    => "Measured, factual and confident, like a well-run board report. Plain business language, exact figures, no hype.",
        'recipe'   => "Open with the position in one line, show the market and the numbers, set out the plan in clear steps, give the financial case, close with the decision needed.",
        'generate' => 'ds_tpl57_generate',
        'build'    => 'ds_tpl57_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl57_generate') ) {
function ds_tpl57_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl57_build') ) {
function ds_tpl57_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glasstower">
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 57 - Glass Tower : dark navy, a skyline of steel-blue towers behind square-cut frosted glass */
D.glassKit({
  id:'glasstower', name:'Glass Tower', tag:'Dark navy · frosted glass · towers',
  desc:'Corporate dark navy with a skyline of steel-blue towers that blurs behind square-cut frosted glass panels; a photo can fill the slide or half of it behind the glass. For finance, real estate, consulting and company reports.',
  fonts:{head:'Hanken Grotesk', body:'Hanken Grotesk', headW:700, bodyW:400, headLh:1.1, gf:'Hanken+Grotesk:wght@400;500;600;700;800'},
  bg:'#08111F', tx:'#FFFFFF', mu:'#B4C2D6', ac:'#8FC1F2', ac2:'#3E6FA3', card:'#101D30', line:'#24364F', onAc:'#08111F',
  r:6, mark:'tick', num:'box', stag:false, cover:'l', close:'c', quote:'c', b3:'cols', veil:'#050B15', veilOp:0.36,
  ph:{ang:160, stops:[[0, '#3E6FA3'], [100, '#0B1A2E']]},
  scene:function(C, R, k, U){
    var W = U.W, H = U.H, n = 17, bw = W / n;
    U.D.glow(C, R.x + R.w * 0.7, 120, 560, 380, '#2B5E99', 0.4);
    for (var i = 0; i < n; i++) {
      var h = 150 + ((i * 37 + k * 53 + 5) % 11) / 10 * 380, x = i * bw + 5, y = H - h, hot = (i + k) % 5 === 2;
      C.rect(x, y, bw - 10, h, {grad:{ang:180, stops:[[0, hot ? '#8FC1F2' : '#3E6FA3', hot ? 0.85 : 0.75], [100, '#0B1A2E', 0.15]]}});
      C.rect(x, y, bw - 10, 3, {fill:'#CFE6FF', op:hot ? 0.95 : 0.5});
      if (i % 2 === 0) C.rect(x + (bw - 10) * 0.3, y + 26, (bw - 10) * 0.4, h * 0.5, {fill:'#CFE6FF', op:0.1});
    }
  }
});
})();
</script>
<?php
}, 1067);
