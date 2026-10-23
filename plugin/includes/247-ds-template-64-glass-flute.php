<?php
/**
 * Code Snippets export — DS Template 64 — Glass Flute
 *
 * Snippet ID : 1042
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:18:08
 * Load order : 238 of 242
 */
/**
 * DS Template 64 — Glass Flute
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator: dark pebbled frosted glass. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Frosted Glass" and "DS Template Glass Kit" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl64_generate)
 *  - Layouts:    its own background scene, colours, fonts and glass shape (JS below); the layouts come from the Glass Kit
 *  - Download:   ds_tpl64_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glassflute', array(
        'name'     => "Glass Flute",
        'fonts'    => array('head' => "Archivo", 'body' => "Albert Sans"),
        'pal'      => array('bg' => '#0A0A0C', 'text' => '#FFFFFF', 'accent' => '#E3B7DA', 'accent2' => '#8E3F7A'),
        'voice'    => "Polished, confident and understated. Short elegant sentences, strong nouns, nothing loud.",
        'recipe'   => "Open with the proposition, show what makes it distinct, present the offer or work piece by piece, give the proof, close with a clear invitation.",
        'generate' => 'ds_tpl64_generate',
        'build'    => 'ds_tpl64_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl64_generate') ) {
function ds_tpl64_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl64_build') ) {
function ds_tpl64_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glassflute">
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 64 - Glass Flute : black fluted wall, two metallic plum spheres behind a pebbled-glass panel */
D.glassKit({
  id:'glassflute', name:'Glass Flute', tag:'Black · pebbled glass · fluted wall',
  desc:'A black fluted wall with two metallic plum spheres that blur behind a pebbled-glass panel; white text carries the slide and a photo can fill the slide or half of it behind the glass. For premium brands, events, product and portfolio decks.',
  fonts:{head:'Archivo', body:'Albert Sans', headW:800, bodyW:400, headLh:1.04, gf:'Archivo:wght@600;700;800;900|Albert+Sans:wght@400;500;600'},
  bg:'#0A0A0C', tx:'#FFFFFF', mu:'#CFCBD2', ac:'#E3B7DA', ac2:'#8E3F7A', card:'#161618', line:'#2A2A2E', onAc:'#0A0A0C',
  r:18, mark:'bar', num:'ring', big:true, grain:16, calm:1, stag:false, cover:'c', close:'l', quote:'c', b3:'cols', b4:'grid', veil:'#070708', veilOp:0.5,
  ph:{ang:150, stops:[[0, '#6E3A62'], [100, '#111113']]},
  scene:function(C, R, k, U){
    var W = U.W, H = U.H, n = 46, w = W / n, f = k % 2;
    for (var i = 0; i < n; i++) C.rect(i * w, 0, w - 3, H, {grad:{ang:90, stops:[[0, '#0C0C0E'], [45, '#2A2A30'], [100, '#0C0C0E']]}});
    function ball(cx, cy, r, a, b, c){ C.ell(cx - r, cy - r, r * 2, r * 2, {grad:{ang:150, stops:[[0, a], [45, b], [100, c]]}}); U.D.glow(C, cx - r * 0.32, cy - r * 0.36, r * 0.5, r * 0.4, '#FFFFFF', 0.6); }
    var x1 = f ? R.x : R.x + R.w, m = Math.max(90, Math.min(R.w, R.h) * 0.24);
    ball(x1 + (f ? -m * 0.1 : m * 0.1), R.y + R.h * 0.68, m, '#F4DDEE', '#93407F', '#1F0B1B');
    ball(x1 + (f ? m * 0.5 : -m * 0.5), R.y + R.h * 0.22, m * 0.42, '#F1F1F4', '#8B8B97', '#1A1A1F');
    ball(R.x + R.w * (f ? 0.72 : 0.28), R.y + R.h * 0.96, m * 0.6, '#EFD3E8', '#7A3569', '#190916');
  }
});
})();
</script>
<?php
}, 1074);
