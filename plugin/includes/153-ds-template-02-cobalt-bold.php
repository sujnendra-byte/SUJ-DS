<?php
/**
 * Code Snippets export — DS Template 02 — Cobalt Bold
 *
 * Snippet ID : 875
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-09-29 08:14:23
 * Load order : 153 of 157
 */
/**
 * DS Template 02 — Cobalt Bold
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" snippet to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl02_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote
 *                and closing designs with several layout options each (JS below)
 *  - Download:   its own PPTX build (ds_tpl02_build) — the same elements the
 *                editor shows are written into PowerPoint.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('cobalt', array(
        'name'     => "Cobalt Bold",
        'fonts'    => array('head' => "Archivo Black", 'body' => "Archivo"),
        'pal'      => array('bg' => '#F3F5FB', 'text' => '#1A12B8', 'accent' => '#1A12B8', 'accent2' => '#5A52F0'),
        'voice'    => "Punchy investor-pitch voice: bold, short, declarative headlines; every point backed by a hard number.",
        'recipe'   => "Problem, solution, market size (TAM/SAM/SOM), product tiers, team, performance, milestones, funding ask.",
        'generate' => 'ds_tpl02_generate',
        'build'    => 'ds_tpl02_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl02_generate') ) {
function ds_tpl02_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl02_build') ) {
function ds_tpl02_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-cobalt">
/* Template 02 — Cobalt Bold : electric cobalt, heavy condensed caps (Pitch Deck sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var LIGHT = {bg:'#F3F5FB', text:'#1A12B8', title:'#1A12B8', muted:'#3B3F8C', accent:'#1A12B8', accent2:'#5A52F0', card:'#FFFFFF', line:'#C9CCEB', onAccent:'#FFFFFF'};
var BLUE  = {bg:'#1A12B8', text:'#FFFFFF', title:'#FFFFFF', muted:'#D5D3FF', accent:'#FFFFFF', accent2:'#A9A4FF', card:'#2A22D0', line:'#5750E0', onAccent:'#1A12B8'};
var S = {
  margin:64,
  header:{kind:'split', tMax:46, tMin:26, sMax:17, top:52},
  card:{kind:'outline', r:0, line:'#1A12B8', lw:1.5, hl:{kind:'solid', fill:'#1A12B8', dark:true, tc:'#FFFFFF', mc:'#D5D3FF'}},
  icon:{kind:'square', r:0, bg:'#1A12B8', fg:'#FFFFFF'},
  num:{kind:'big', fg:'#1A12B8'},
  mark:'icon',
  conc:{kind:'line', label:'(Key takeaway)', lc:'#1A12B8', lineC:'#1A12B8', wt:600},
  footer:{fn:foot},
  imgFramed:false, imgPct:0.42
};
var SB = D.merge(S, {card:{kind:'outline', line:'#FFFFFF', lw:1.5, fill:null, hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:'#1A12B8', mc:'#3B3F8C'}}, icon:{bg:'#FFFFFF', fg:'#1A12B8'}, num:{fg:'#FFFFFF'}, conc:{lc:'#FFFFFF', lineC:'#FFFFFF', tc:'#FFFFFF'}});
function foot(C){
  var P = C.P, f = C.cover, y = H - 38;
  var who = f.presenter ? 'PRESENTED BY ' + f.presenter : (f.company || '');
  if (who) C.text(64, y, 520, 16, who, {font:'body', size:11, wt:800, color:P.text, caps:true, ls:0.06, lh:1});
  C.text(W - 64 - 140, y, 140, 16, 'PAGE ' + (C.i + 1), {font:'body', size:11, wt:800, color:P.text, align:'r', lh:1, caps:true});
}
function blue(C, kind){ return kind === 'stats' || kind === 'quote' || (kind === 'p2' && C.vid === 'v1'); }
function bg(C, kind){
  if (blue(C, kind)) { C.P = BLUE; C.S = SB; } else { C.P = LIGHT; C.S = S; }
  C.bg({fill:C.P.bg});
  if (!blue(C, kind)) C.rect(W - 64 - 90, 0, 90, 10, {fill:'#1A12B8'});
}
function cover(C, v){
  C.P = BLUE; C.bg({fill:'#1A12B8'});
  var P = C.P, f = C.cover, o = C.o || {};
  var tw = o.img ? 640 : W - 128;
  if (o.img) D.imgBox(C, 700, 0, W - 700, H, {});
  C.text(40, 40, tw + 24, 430, C.s.title, {font:'head', fit:{max:170, min:56}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:0.86, ls:-0.03, va:'t'});
  var sx = o.img ? 44 : W - 64 - 440, sw = o.img ? 600 : 440;
  if (f.company) C.text(sx, 488, sw, 30, '(' + f.company + ')', {font:'head', size:20, color:'#FFFFFF', ed:{f:'tf-company'}, lh:1});
  if (C.s.subtitle) C.text(sx, 528, sw, 90, C.s.subtitle, {font:'body', fit:{max:15, min:11}, wt:800, color:'#FFFFFF', caps:true, ed:{f:'subtitle'}, lh:1.25, maxLines:4});
  var who = [f.presenter ? 'PRESENTED BY\n' + f.presenter : '', f.website].filter(Boolean);
  if (who[0]) C.text(44, 640, 260, 40, who[0], {font:'body', size:11, wt:800, color:'#FFFFFF', caps:true, lh:1.2});
  if (f.website) C.text(330, 652, 300, 20, f.website, {font:'body', size:11, wt:800, color:'#FFFFFF', caps:true, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var P = LIGHT; C.P = P;
  C.bg({fill:P.bg});
  D.imgBox(C, 0, 250, 300, H - 250, {});
  if (C.s.subtitle) C.text(330, 410, W - 400, 50, C.s.subtitle, {font:'body', fit:{max:18, min:12}, wt:600, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.3});
  C.text(330, 90, W - 380, 300, C.s.title || "Let's collaborate", {font:'head', fit:{max:150, min:60}, color:P.title, ed:{f:'title'}, maxLines:2, lh:0.86, ls:-0.03, va:'b'});
  var cs = D.contacts(C), y = 470;
  cs.forEach(function(c){
    C.text(W - 64 - 420, y, 420, 18, (c.label || '').toUpperCase(), {font:'head', size:15, color:P.text, lh:1});
    C.text(W - 64 - 420, y + 22, 420, 24, c.val, {font:'head', fit:{max:18, min:11}, color:P.text, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1, lh:1});
    y += 64;
  });
  if (C.cover.presenter) C.text(330, 560, 360, 40, 'PRESENTED BY\n' + C.cover.presenter, {font:'body', size:13, color:P.text, lh:1.2, caps:true, wt:600});
}
function agenda(C, v){
  D.agendaSplit(C, {split:0.42, titleY:70, tMax:96, numC:'#1A12B8', itemFont:'head', itemWt:400, iMax:22, rowMax:60, ruleC:'#1A12B8', titleVa:'t'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#FFFFFF', qFont:'head', qMax:40}); return; }
  C.text(64, 70, W - 128, 460, C.s.title, {font:'head', fit:{max:66, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:5, lh:1.0, va:'t'});
  var by = C.it[0] ? (C.it[0].h ? C.it[0].h + ': ' + C.it[0].d : C.it[0].d) : '';
  C.rect(64, 580, 120, 6, {fill:'#FFFFFF'});
  if (by) C.text(64, 604, 900, 30, by, {font:'body', fit:{max:18, min:12}, wt:800, color:'#FFFFFF', caps:true, ed:C.it[0] ? {f:'content-plain', i:C.it[0].i} : null, maxLines:1});
}
D.register({
  id:'cobalt', name:'Cobalt Bold', tag:'Electric blue · pitch', desc:'Heavy uppercase headlines, sharp outlined boxes and full-bleed cobalt statement slides.',
  fonts:{head:'Archivo Black', body:'Archivo', headW:400, bodyW:500, headLh:0.95, headCaps:true, headLs:-0.01, gf:'Archivo Black|Archivo:wght@400;500;600;700;800'},
  pal:LIGHT, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Giant headline',{}), V('v2','Headline + image right',{img:'right'})],
    agenda:[V('v1','Split list',{}), V('n2','Numbered tiles',{body:'agTiles'})],
    p2:[V('v1','Blue panels',{body:'duo', bigNum:true, hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image left + columns',{body:'list', img:'left'}), V('v3','Columns + image right',{body:'duo', img:'right'})],
    p3:[V('v1','Outlined tiers',{body:'cols', hl:'alt', mark:'num'}), V('n2','Numbered columns',{body:'numbered'}), V('n3','Big-number list',{body:'list', mark:'num', rules:true}), V('v2','Image left + columns',{body:'cols', img:'left', mark:'num'}), V('v3','Image right + list',{body:'list', img:'right'}), V('v4','Image bottom + columns',{body:'numbered', img:'bottom'})],
    p4:[V('v1','Box grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', hl:'first', mark:'num'}), V('n3','Numbered row',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left'}), V('v3','List + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Box grid',{body:'grid', hl:'first', mark:'num'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Box grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, rules:true, mark:'num'})],
    stats:[V('v1','Giant numbers',{body:'statCols', vc:'#FFFFFF', vMax:110}), V('n2','Outlined stat boxes',{body:'statCards', hl:'first'}), V('n3','Progress bars',{body:'statBars'}), V('v2','Image left + numbers',{body:'statBars', img:'left'})],
    timeline:[V('v1','Milestone line',{body:'tlH'}), V('n2','Milestone boxes',{body:'tlCards', hlLast:true}), V('n3','Arrow steps',{body:'tlChev'}), V('n4','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Statement',{}), V('v2','Statement + image right',{img:'right'})]
  }
});
})();

</script>
<?php
}, 1006);
