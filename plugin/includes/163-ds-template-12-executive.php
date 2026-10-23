<?php
/**
 * Code Snippets export — DS Template 12 — Executive Serif
 *
 * Snippet ID : 885
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-09-29 08:14:35
 * Load order : 163 of 157
 */
/**
 * DS Template 12 — Executive Serif
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" snippet to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl12_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote
 *                and closing designs with several layout options each (JS below)
 *  - Download:   its own PPTX build (ds_tpl12_build) — the same elements the
 *                editor shows are written into PowerPoint.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('executive', array(
        'name'     => "Executive Serif",
        'fonts'    => array('head' => "Playfair Display", 'body' => "Lato"),
        'pal'      => array('bg' => '#FBF9F4', 'text' => '#14213D', 'accent' => '#B8893B', 'accent2' => '#14213D'),
        'voice'    => "Formal, board-level and precise. Measured sentences, evidence first, no hype.",
        'recipe'   => "Executive summary, context, strategic options, recommendation, financial impact, risks and mitigations, roadmap, decision required.",
        'generate' => 'ds_tpl12_generate',
        'build'    => 'ds_tpl12_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl12_generate') ) {
function ds_tpl12_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl12_build') ) {
function ds_tpl12_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-executive">
/* Template 12 — Executive Serif : ivory, navy and gold, classic board-room serif */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#FBF9F4', text:'#14213D', title:'#14213D', muted:'#4B5563', accent:'#B8893B', accent2:'#14213D', card:'#FFFFFF', line:'#E3DCCD', onAccent:'#FFFFFF'};
var NAVY = {bg:'#14213D', text:'#FFFFFF', title:'#FFFFFF', muted:'#C9CFDC', accent:'#D4AA5E', accent2:'#D4AA5E', card:'#1D2C4F', line:'#34456B', onAccent:'#14213D'};
var S = {
  margin:96,
  header:{kind:'left', tMax:44, tMin:26, sMax:17, top:64, kicker:function(C){ return 'Section ' + D.u.pad2(C.i); }, kc:'#B8893B', kSize:12, kGap:30},
  card:{kind:'top', r:0, fill:'#FFFFFF', line:'#E3DCCD', lw:1, barW:3, shadow:0.06, hl:{kind:'solid', fill:'#14213D', dark:true, tc:'#FFFFFF', mc:'#C9CFDC'}},
  icon:{kind:'ring', bg:'#B8893B', fg:'#B8893B', lw:1.4},
  num:{kind:'big', fg:'#B8893B'},
  conc:{kind:'line', label:'In summary', lc:'#B8893B', lineC:'#D9CFB9', italic:true, wt:400},
  footer:{fn:foot},
  imgFramed:false, imgPct:0.38, imgPh:{grad:{ang:135, stops:[[0,'#2A3B63'],[100,'#14213D']]}}
};
var SN = D.merge(S, {card:{kind:'top', fill:'#1D2C4F', line:'#34456B', shadow:0, hl:{kind:'solid', fill:'#D4AA5E', dark:false, tc:'#14213D', mc:'#3A3322'}}, icon:{bg:'#D4AA5E', fg:'#D4AA5E'}, num:{fg:'#D4AA5E'}, header:{kc:'#D4AA5E'}, conc:{lc:'#D4AA5E', lineC:'#34456B'}});
function foot(C){
  var P = C.P;
  C.rect(96, H - 50, W - 192, 1, {fill:P.accent, op:0.6});
  C.text(96, H - 40, 600, 16, (C.cover.company || '') + (C.cover.company ? '  —  ' : '') + 'Confidential', {font:'body', size:11, color:P.muted, lh:1, ls:0.08, caps:true});
  C.text(W - 96 - 100, H - 40, 100, 16, String(C.i + 1), {font:'head', size:14, italic:true, color:P.accent, align:'r', lh:1});
}
function navy(C, kind){ return kind === 'stats' || kind === 'quote'; }
function bg(C, kind){
  if (navy(C, kind)) { C.P = NAVY; C.S = SN; } else { C.P = P0; C.S = S; }
  C.bg({fill:C.P.bg});
  C.rect(0, 0, 44, H, {fill:navy(C, kind) ? '#0E1830' : '#14213D'});
  C.rect(44, 0, 4, H, {fill:C.P.accent});
}
function cover(C, v){
  var f = C.cover, o = C.o || {};
  C.P = NAVY; C.bg({fill:'#14213D'});
  if (o.img) D.imgBox(C, 780, 0, 500, H, {});
  var w = o.img ? 600 : 900;
  C.rect(110, 110, 80, 3, {fill:'#D4AA5E'});
  if (f.company) C.text(110, 130, 600, 24, f.company, {font:'body', size:14, wt:700, color:'#D4AA5E', caps:true, ls:0.24, lh:1, ed:{f:'tf-company'}});
  C.text(106, 200, w, 250, C.s.title, {font:'head', fit:{max:78, min:36}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.08, va:'b'});
  if (C.s.subtitle) C.text(110, 470, w - 60, 70, C.s.subtitle, {font:'head', fit:{max:21, min:13}, italic:true, wt:400, color:'#C9CFDC', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(110, 580, w - 20, 1, {fill:'#D4AA5E', op:0.6});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) C.text(110, 600, 520, 20, who, {font:'body', size:14, wt:700, color:'#FFFFFF', lh:1});
  if (f.date) C.text(110, 626, 520, 18, f.date, {font:'body', size:13, color:'#C9CFDC', lh:1, ed:{f:'tf-date'}});
  if (!o.img) { C.ell(W - 330, 150, 420, 420, {line:'#D4AA5E', lw:1, lop:0.35}); C.ell(W - 290, 190, 340, 340, {line:'#D4AA5E', lw:1, lop:0.2}); }
}
function closing(C, v){
  var f = C.cover;
  C.P = NAVY; C.bg({fill:'#14213D'});
  C.ell(W / 2 - 300, H / 2 - 300, 600, 600, {line:'#D4AA5E', lw:1, lop:0.25});
  C.rect(W / 2 - 40, 170, 80, 3, {fill:'#D4AA5E'});
  C.text(140, 200, W - 280, 130, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:36}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, align:'c', va:'m'});
  if (C.s.subtitle) C.text(240, 340, W - 480, 60, C.s.subtitle, {font:'head', fit:{max:21, min:13}, italic:true, wt:400, color:'#C9CFDC', ed:{f:'subtitle'}, align:'c', lh:1.4, maxLines:2});
  D.contactRow(C, 200, 470, W - 400, {lc:'#D4AA5E', vc:'#FFFFFF'});
}
function agenda(C, v){
  D.agendaSplit(C, {split:0.4, titleY:200, tMax:64, numC:'#B8893B', itemFont:'head', itemWt:400, iMax:22, rowMax:62, ruleC:'#D9CFB9'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#D4AA5E', qFont:'head', italic:true}); return; }
  D.B.quote(C, {x:180, y:140, w:W - 360, h:H - 260}, {qc:'#D4AA5E', qFont:'head', italic:true, qMax:42});
}
D.register({
  id:'executive', name:'Executive Serif', tag:'Navy & gold · board room', desc:'Ivory pages, navy spine, gold rules and a classic serif — made for boards and investors.',
  fonts:{head:'Playfair Display', body:'Lato', headW:700, bodyW:400, headLh:1.12, gf:'Playfair Display:ital,wght@0,400;0,600;0,700;1,400|Lato:wght@400;700'},
  voice:'Formal, board-level and precise. Measured sentences, evidence first, no hype.',
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Navy classic',{}), V('v2','Navy + photo',{img:'right'})],
    agenda:[V('v1','Serif contents',{})],
    p2:[V('v1','Ruled columns',{body:'numbered'}), V('n2','Two cards',{body:'duo', hl:true}), V('v2','Photo right + list',{body:'list', img:'right', mark:'num', rules:true}), V('v3','Photo left + list',{body:'list', img:'left', mark:'num', rules:true})],
    p3:[V('v1','Ruled cards',{body:'cols', mark:'num'}), V('n2','Numbered columns',{body:'numbered'}), V('n3','Ruled list',{body:'list', mark:'num', rules:true}), V('v2','Photo right + list',{body:'list', img:'right', rules:true}), V('v3','Photo left + list',{body:'list', img:'left', rules:true}), V('v4','Cards + photo band',{body:'cols', img:'bottom', mark:'num'})],
    p4:[V('v1','Ruled grid',{body:'grid', mark:'num'}), V('n2','Four columns',{body:'numbered'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Photo right + list',{body:'list', img:'right', rules:true}), V('v3','Photo left + list',{body:'list', img:'left', rules:true, mark:'num'})],
    p56:[V('v1','Ruled grid',{body:'grid', mark:'num'}), V('n2','Two-column list',{body:'list', cols:2, rules:true, mark:'num'}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Photo left + list',{body:'list', img:'left', cols:1, rules:true})],
    p78:[V('v1','Ruled grid',{body:'grid', mark:'num'}), V('n2','Two-column list',{body:'list', cols:2, rules:true, mark:'num'})],
    stats:[V('v1','Gold numbers',{body:'statCols', vc:'#D4AA5E'}), V('n2','Stat cards',{body:'statCards', hl:'first'}), V('n3','Progress rings',{body:'statRings', track:'#2A3B63'}), V('v2','Photo left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Classic timeline',{body:'tlH'}), V('n2','Ruled milestones',{body:'tlCards'}), V('n3','Vertical timeline',{body:'tlV'}), V('v2','Vertical + photo right',{body:'tlV', img:'right'})],
    quote:[V('v1','Serif quote',{}), V('v2','Quote + photo right',{img:'right'})]
  }
});
})();

</script>
<?php
}, 1006);
