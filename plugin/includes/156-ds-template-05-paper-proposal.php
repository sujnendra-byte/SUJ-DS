<?php
/**
 * Code Snippets export — DS Template 05 — Paper Proposal
 *
 * Snippet ID : 878
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-09-29 08:14:26
 * Load order : 156 of 157
 */
/**
 * DS Template 05 — Paper Proposal
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" snippet to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl05_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote
 *                and closing designs with several layout options each (JS below)
 *  - Download:   its own PPTX build (ds_tpl05_build) — the same elements the
 *                editor shows are written into PowerPoint.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('paper', array(
        'name'     => "Paper Proposal",
        'fonts'    => array('head' => "Montserrat", 'body' => "Open Sans"),
        'pal'      => array('bg' => '#F3F0EB', 'text' => '#1D4F7C', 'accent' => '#2F74AE', 'accent2' => '#8FB9DC'),
        'voice'    => "Professional business-proposal voice: structured, persuasive and client-focused; emphasise value and deliverables.",
        'recipe'   => "Market opportunity, problem statement, our solution, value proposition, target audience, competitive analysis, marketing and sales strategy, implementation plan, conclusion and next steps.",
        'generate' => 'ds_tpl05_generate',
        'build'    => 'ds_tpl05_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl05_generate') ) {
function ds_tpl05_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl05_build') ) {
function ds_tpl05_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-paper">
/* Template 05 — Paper Proposal : warm paper, steel-blue gradients, bold caps (Business Proposal sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#F3F0EB', text:'#1D4F7C', title:'#1D4F7C', muted:'#3E5C78', accent:'#2F74AE', accent2:'#8FB9DC', card:'#C3D8E8', line:'#C9D3DC', onAccent:'#FFFFFF'};
var S = {
  margin:70,
  header:{kind:'left', tMax:46, tMin:26, sMax:16, top:56},
  card:{kind:'solid', r:8, fill:'#C6DAEA', dark:false, tc:'#1D4F7C', mc:'#2F4E6B', hl:{kind:'solid', fill:'#2F74AE', dark:true, tc:'#FFFFFF', mc:'#E2EEF8'}},
  icon:{kind:'softsq', r:8, bg:'#2F74AE', fg:'#2F74AE'},
  num:{kind:'big', fg:'#1D4F7C'},
  mark:'num',
  conc:{kind:'band', fill:'#1D4F7C', r:6, label:'Next step', tc:'#FFFFFF', lc:'#9CC6EA'},
  footer:{pagePrefix:'PAGE ', brand:true},
  imgFramed:true, imgR:10, imgPh:{grad:{ang:135, stops:[[0,'#9CC3E4'],[100,'#2F74AE']]}}
};
function corners(C, a, b){
  D.glow(C, a[0], a[1], 460, 360, '#4E90C8', 0.75);
  D.glow(C, b[0], b[1], 380, 300, '#7FB3DD', 0.6);
  D.glow(C, a[0] - 80, a[1] + 40, 260, 200, '#9FE0E4', 0.35);
}
function bg(C, kind){
  C.bg({fill:'#F3F0EB'});
  var k = C.i % 3;
  if (k === 0) corners(C, [1280, 0], [0, 720]);
  else if (k === 1) corners(C, [0, 0], [1280, 720]);
  else corners(C, [1280, 720], [120, -60]);
}
function logo(C, x, y, col){
  C.path([['M',x,y+8],['L',x+8,y+16],['L',x+22,y+2]], {stroke:col, lw:3});
  C.path([['M',x+10,y+8],['L',x+18,y+16],['L',x+32,y+2]], {stroke:col, lw:3});
  if (C.cover.company) C.text(x + 44, y - 2, 360, 22, C.cover.company, {font:'head', size:15, color:col, va:'m', lh:1, caps:true, ls:0.04, ed:C.i === 0 ? {f:'tf-company'} : null});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  corners(C, [1280, 360], [1100, 720]);
  logo(C, 70, 60, '#1D4F7C');
  var words = String(C.s.title || '').split(/\s+/), first = words.length > 2 ? words.slice(0, Math.ceil(words.length / 2)).join(' ') : words[0] || '', rest = words.length > 2 ? words.slice(Math.ceil(words.length / 2)).join(' ') : words.slice(1).join(' ');
  var tw = o.img ? 640 : 900;
  if (o.img) D.imgBox(C, 760, 80, 450, 560, {r:12});
  var a = C.text(64, 200, tw, 110, first, {font:'head', fit:{max:84, min:36}, wt:400, color:'#1D4F7C', maxLines:1, lh:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (rest) C.text(64, 300, tw, 200, rest, {font:'head', fit:{max:104, min:40}, color:'#1D4F7C', maxLines:2, lh:1, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(70, 520, o.img ? 620 : 760, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  if (f.website) C.text(70, 632, 600, 22, f.website, {font:'head', size:17, color:'#1D4F7C', caps:true, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var P = C.P;
  corners(C, [1280, 360], [1100, 720]);
  logo(C, 70, 60, '#1D4F7C');
  var words = String(C.s.title || 'Thank You Very Much').split(/\s+/), cut = Math.max(1, Math.ceil(words.length / 2));
  C.text(64, 190, 900, 110, words.slice(0, cut).join(' '), {font:'head', fit:{max:92, min:40}, wt:400, color:'#1D4F7C', maxLines:1, lh:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (words.length > cut) C.text(64, 300, 900, 120, words.slice(cut).join(' '), {font:'head', fit:{max:104, min:40}, color:'#1D4F7C', maxLines:1, lh:1, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(70, 440, 700, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contactRow(C, 70, 560, 900, {lc:'#2F74AE'});
}
function agenda(C, v){
  var P = C.P, M = 70;
  C.text(M, 70, 900, 70, C.s.title || 'Agenda', {font:'head', fit:{max:52, min:28}, color:'#1D4F7C', ed:{f:'title'}, maxLines:1});
  if (C.s.subtitle) C.text(M, 140, 900, 30, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:1});
  D.B.agTiles(C, {x:M, y:200, w:W - 2*M, h:430}, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#2F74AE'}); return; }
  C.rect(90, 90, W - 180, H - 180, {fill:'#C6DAEA', op:0.85, r:6});
  D.B.quote(C, {x:170, y:130, w:W - 340, h:H - 260}, {qc:'#1D4F7C', qFont:'head'});
}
D.register({
  id:'paper', name:'Paper Proposal', tag:'Warm paper · proposal', desc:'Soft paper texture tones, steel-blue gradient corners and confident uppercase headlines.',
  fonts:{head:'Montserrat', body:'Open Sans', headW:800, bodyW:400, headLh:1.02, headCaps:true, gf:'Montserrat:wght@400;600;700;800|Open Sans:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Two-weight title',{}), V('v2','Title + image',{img:'right'})],
    agenda:[V('v1','Numbered tiles',{})],
    p2:[V('v1','Blue panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image right + list',{body:'list', img:'right', mark:'num'}), V('v3','Image left + list',{body:'list', img:'left', mark:'num'})],
    p3:[V('v1','Stacked tiles',{body:'list', cards:true, mark:'num'}), V('n2','Blue columns',{body:'cols', hl:'alt'}), V('n3','Numbered columns',{body:'numbered', dot:true}), V('v2','Image right + list',{body:'list', img:'right'}), V('v3','Image left + tiles',{body:'list', img:'left', cards:true}), V('v4','Columns + image bottom',{body:'numbered', img:'bottom'})],
    p4:[V('v1','Tile grid',{body:'grid', hl:'alt'}), V('n2','Value timeline',{body:'numbered', dot:true}), V('n3','Four columns',{body:'cols'}), V('v2','Image right + list',{body:'list', img:'right'}), V('v3','Image left + tiles',{body:'list', img:'left', cards:true})],
    p56:[V('v1','Tile grid',{body:'grid', hl:'first'}), V('n2','Numbered grid',{body:'numbered', dot:true}), V('n3','Two-column list',{body:'list', cols:2, rules:true}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Tile grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, rules:true})],
    stats:[V('v1','Stacked bars',{body:'statBars'}), V('n2','Big numbers',{body:'statCols'}), V('n3','Blue stat tiles',{body:'statCards', hl:'alt'}), V('n4','Progress rings',{body:'statRings', track:'#D5E2EC'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Dot timeline',{body:'tlH'}), V('n2','Milestone tiles',{body:'tlCards'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Framed quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

</script>
<?php
}, 1006);
