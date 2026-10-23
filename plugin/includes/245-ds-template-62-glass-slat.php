<?php
/**
 * Code Snippets export — DS Template 62 — Glass Slat
 *
 * Snippet ID : 1040
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-04 04:12:31
 * Load order : 236 of 242
 */
/**
 * DS Template 62 — Glass Slat
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator: dark corporate frosted glass. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2", "DS Template Frosted Glass" and "DS Template Glass Kit" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl62_generate)
 *  - Layouts:    its own background scene, colours, fonts and glass shape (JS below); the layouts come from the Glass Kit
 *  - Download:   ds_tpl62_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glassslat', array(
        'name'     => "Glass Slat",
        'fonts'    => array('head' => "Familjen Grotesk", 'body' => "Familjen Grotesk"),
        'pal'      => array('bg' => '#0B1512', 'text' => '#FFFFFF', 'accent' => '#D2B272', 'accent2' => '#3F6B5A'),
        'voice'    => "Steady, prudent and trustworthy. Careful wording, realistic figures, commitments stated plainly.",
        'recipe'   => "Open with the year or plan in one sentence, review performance, set out priorities and how they will be delivered, give the figures and risks, close with the outlook.",
        'generate' => 'ds_tpl62_generate',
        'build'    => 'ds_tpl62_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl62_generate') ) {
function ds_tpl62_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl62_build') ) {
function ds_tpl62_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glassslat">
(function(){
var D = window.DSTE; if (!D || !D.glassKit) return;
/* Template 62 - Glass Slat : dark slate green, rows of lit slats with brass ones among them behind neat frosted glass */
D.glassKit({
  id:'glassslat', name:'Glass Slat', tag:'Slate green · frosted glass · slats',
  desc:'Corporate dark slate green with rows of softly lit slats, a few of them brass, that blur behind neat frosted glass; a photo can fill the slide or half of it behind the glass. For banking, architecture, hospitality and annual reviews.',
  fonts:{head:'Familjen Grotesk', body:'Familjen Grotesk', headW:700, bodyW:400, headLh:1.1, gf:'Familjen+Grotesk:wght@400;500;600;700'},
  bg:'#0B1512', tx:'#FFFFFF', mu:'#BFCDC6', ac:'#D2B272', ac2:'#3F6B5A', card:'#14211C', line:'#263A32', onAc:'#0B1512', rim:'#EDE2C6',
  r:10, mark:'tick', num:'ring', caps:true, stag:false, cover:'tl', close:'l', quote:'br', b3:'rows', b4:'grid', veil:'#050B09', veilOp:0.4,
  ph:{ang:150, stops:[[0, '#3F6B5A'], [100, '#0D1814']]},
  scene:function(C, R, k, U){
    var W = U.W, n = 13, sh = 720 / n;
    U.D.glow(C, R.x + R.w * (k % 2 ? 0.2 : 0.8), R.y + R.h * 0.5, 600, 420, '#2C5546', 0.45);
    for (var i = 0; i < n; i++) {
      var y = i * sh + 6, v = (i * 5 + k * 3) % 7, x0 = (i % 3) * 90 - 40, w = W - x0 + 40 - ((i + k) % 4) * 120;
      if (v === 1 || v === 4) C.rect(x0 + 200, y, w - 380, sh - 14, {grad:{ang:90, stops:[[0, '#D2B272', 0.15], [50, '#EBD39A', 0.9], [100, '#D2B272', 0.2]]}, r:(sh - 14) / 2});
      else C.rect(x0, y, w, sh - 14, {grad:{ang:90, stops:[[0, '#3F6B5A', 0.15], [45, '#7FA997', v === 2 ? 0.7 : 0.4], [100, '#3F6B5A', 0.1]]}, r:(sh - 14) / 2});
    }
  }
});
})();
</script>
<?php
}, 1072);
