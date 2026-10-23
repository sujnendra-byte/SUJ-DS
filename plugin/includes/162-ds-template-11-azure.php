<?php
/**
 * Code Snippets export — DS Template 11 — Azure Corporate
 *
 * Snippet ID : 884
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-09-29 08:14:33
 * Load order : 162 of 157
 */
/**
 * DS Template 11 — Azure Corporate
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" snippet to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl11_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote
 *                and closing designs with several layout options each (JS below)
 *  - Download:   its own PPTX build (ds_tpl11_build) — the same elements the
 *                editor shows are written into PowerPoint.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('azure', array(
        'name'     => "Azure Corporate",
        'fonts'    => array('head' => "Nunito Sans", 'body' => "Nunito Sans"),
        'pal'      => array('bg' => '#0B63C5', 'text' => '#FFFFFF', 'accent' => '#FFFFFF', 'accent2' => '#9FD3FF'),
        'voice'    => "Friendly corporate voice: clear objectives, services and measurable project outcomes.",
        'recipe'   => "Overview, about us, services, project data, objectives, statistics, projects, strategy, team, business concept.",
        'generate' => 'ds_tpl11_generate',
        'build'    => 'ds_tpl11_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl11_generate') ) {
function ds_tpl11_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl11_build') ) {
function ds_tpl11_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-azure">
/* Template 11 — Azure Corporate : vivid blue gradient, white type, rounded photo frames (Business Project sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#0B63C5', text:'#FFFFFF', title:'#FFFFFF', muted:'#DCEBFA', accent:'#FFFFFF', accent2:'#9FD3FF', card:'#0A57AE', line:'#5CA0E6', onAccent:'#0B63C5'};
var S = {
  margin:64,
  header:{kind:'left', tMax:48, tMin:26, sMax:18, top:54},
  card:{kind:'grad', r:4, grad:{ang:180, stops:[[0,'#0A4FA3'],[100,'#1A74D6',0.6]]}, line:'#FFFFFF', lw:1, lop:0.25, shadow:0.18, dark:true, tc:'#FFFFFF', mc:'#DCEBFA', hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:'#0B4A96', mc:'#2C5C92'}},
  icon:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF', lw:2},
  num:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#FFFFFF', op:1, label:'Takeaway', lc:'#0B63C5', tc:'#0B4A96'},
  footer:{pagePrefix:'PAGE ', color:'#DCEBFA'},
  imgFramed:true, imgR:26, imgPh:{grad:{ang:135, stops:[[0,'#7DBBF5'],[100,'#0A4FA3']]}}
};
function bg(C, kind){
  var k = C.i % 3;
  C.bg({grad:{ang:[125, 60, 200][k], stops:[[0,'#0A56B4'],[55,'#1478DA'],[100,'#62B3F2']]}});
  D.glow(C, [1100, 150, 640][k], [90, 650, 700][k], 520, 300, '#CDE9FF', 0.35);
  D.glow(C, [200, 1100, 100][k], [700, 60, 80][k], 420, 260, '#073E8C', 0.45);
}
function brand(C, x, y){
  C.path([['M',x,y+14],['L',x+16,y+2],['L',x+32,y+14],['M',x+6,y+12],['L',x+6,y+30],['L',x+26,y+30],['L',x+26,y+12],['M',x+13,y+30],['L',x+13,y+19],['L',x+19,y+19],['L',x+19,y+30]], {stroke:'#FFFFFF', lw:1.6});
  var n = String(C.cover.company || 'Company').split(' ');
  C.text(x + 42, y, 260, 34, n.slice(0, 1).join(' ') + (n.length > 1 ? '\n' + n.slice(1).join(' ') : ''), {font:'body', size:12.5, color:'#FFFFFF', lh:1.2, va:'m', ed:C.i === 0 ? {f:'tf-company'} : null});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.bg({grad:{ang:135, stops:[[0,'#1E7BDB'],[60,'#0B5FC0'],[100,'#063E8A']]}});
  D.glow(C, 900, 180, 560, 360, '#D6EEFF', 0.45);
  brand(C, 90, 60);
  C.rect(W - 132, 64, 44, 2, {fill:'#FFFFFF', op:0.7}); C.rect(W - 132, 74, 44, 2, {fill:'#FFFFFF', op:0.7}); C.rect(W - 132, 84, 44, 2, {fill:'#FFFFFF', op:0.7});
  if (o.img) { D.imgBox(C, 700, 150, 500, 420, {r:28}); }
  var al = o.img ? 'l' : 'c', x = o.img ? 90 : 120, w = o.img ? 560 : W - 240;
  var words = String(C.s.title || '').split(/\s+/), cut = words.length > 2 ? Math.floor(words.length / 2) : 1;
  C.text(x, 200, w, 60, words.slice(0, cut).join(' '), {font:'body', fit:{max:44, min:20}, color:'#FFFFFF', align:al, caps:true, lh:1, maxLines:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (words.length > cut) C.text(x - 10, 266, w + 20, 170, words.slice(cut).join(' '), {font:'head', fit:{max:104, min:40}, color:'#FFFFFF', align:al, caps:true, lh:1, maxLines:2, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(x, 450, w, 56, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#E4F1FD', align:al, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  if (f.website) C.text(x, 520, w, 22, f.website, {font:'body', size:16, color:'#FFFFFF', align:al, lh:1, ed:{f:'tf-website'}});
  C.rect(90, H - 70, W - 180, 1, {fill:'#FFFFFF', op:0.5});
}
function closing(C, v){
  var P = C.P;
  C.bg({grad:{ang:135, stops:[[0,'#1E7BDB'],[60,'#0B5FC0'],[100,'#063E8A']]}});
  D.imgBox(C, 640, 40, 600, 360, {r:40});
  brand(C, 90, 80);
  C.text(90, 190, 520, 140, C.s.title || "Thank's For Watching", {font:'head', fit:{max:64, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, lh:1.05, caps:true, va:'b'});
  if (C.s.subtitle) C.text(90, 344, 520, 110, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#E4F1FD', ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var cs = D.contacts(C), x = 90;
  cs.forEach(function(c){ var w = Math.min(320, c.val.length * 8.6 + 40); C.rect(x, 480, w, 34, {fill:'#FFFFFF', r:17}); C.text(x, 480, w, 34, c.val, {font:'body', fit:{max:14, min:9}, wt:600, color:'#0B4A96', align:'c', va:'m', lh:1, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); x += w + 14; });
  if (C.s.conclusion) C.text(640, 440, 560, 80, C.s.conclusion, {font:'body', fit:{max:17, min:12}, color:'#FFFFFF', ed:{f:'x-conc'}, lh:1.4, maxLines:3});
}
function agenda(C, v){
  var P = C.P, it = C.it.slice(0, 10), n = it.length;
  D.imgBox(C, 640, 0, 640, H, {});
  C.text(90, 70, 520, 70, C.s.title || 'Overview', {font:'head', fit:{max:52, min:28}, color:'#FFFFFF', caps:true, ed:{f:'title'}, maxLines:1});
  if (C.s.subtitle) C.text(90, 145, 500, 80, C.s.subtitle, {font:'body', fit:{max:15, min:11}, color:'#E4F1FD', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var top = 250, rh = Math.min(44, 400 / Math.max(1, n));
  C.rect(90, top - 10, 30, rh * n + 20, {fill:'#FFFFFF', r:2});
  it.forEach(function(t, k){
    var y = top + k * rh;
    C.text(90, y, 30, rh, D.u.pad2(k + 1), {font:'body', size:13, wt:700, color:'#0B4A96', align:'c', va:'m', lh:1});
    C.text(140, y, 460, rh, t.h || t.d, {font:'body', fit:{max:17, min:11}, wt:600, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#FFFFFF'}); return; }
  C.rect(140, 120, W - 280, H - 240, {fill:'#FFFFFF', op:0.12, line:'#FFFFFF', lop:0.5, lw:1, r:6});
  D.B.quote(C, {x:200, y:150, w:W - 400, h:H - 300}, {qc:'#FFFFFF'});
}
D.register({
  id:'azure', name:'Azure Corporate', tag:'Vivid blue · business', desc:'Bright azure gradients, crisp white typography, deep-blue panels and rounded photo frames.',
  fonts:{head:'Nunito Sans', body:'Nunito Sans', headW:800, bodyW:400, headLh:1.05, gf:'Nunito Sans:wght@400;600;700;800;900'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Centered title',{}), V('v2','Title + photo',{img:'right'})],
    agenda:[V('v1','Numbered overview',{body:'agList', hk:'left'}), V('v2','Overview + photo',{fn:function(C){ agenda(C); }})],
    p2:[V('v1','Deep-blue panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered', lineC:'#9FD3FF', numC:'#FFFFFF'}), V('v2','Photo left + list',{body:'list', img:'left', mark:'num'}), V('v3','List + photo right',{body:'list', img:'right', mark:'num'})],
    p3:[V('v1','Service cards',{body:'cols', center:true, mark:'num', ms:70}), V('n2','Numbered list',{body:'list', mark:'num'}), V('n3','Numbered columns',{body:'numbered', lineC:'#9FD3FF', numC:'#FFFFFF'}), V('v2','Photo left + objectives',{body:'list', img:'left', mark:'num', cards:true}), V('v3','Objectives + photo right',{body:'list', img:'right', mark:'num'}), V('v4','Photo top + columns',{body:'cols', img:'top'})],
    p4:[V('v1','Blue card grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', center:true}), V('n3','Hub diagram',{body:'hub', hubGrad:{type:'radial', stops:[[0,'#FFFFFF'],[100,'#CDE6FF']]}}), V('v2','Photo left + list',{body:'list', img:'left', mark:'num'}), V('v3','List + photo right',{body:'list', img:'right'})],
    p56:[V('v1','Blue card grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'}), V('n3','Numbered grid',{body:'numbered', lineC:'#9FD3FF', numC:'#FFFFFF'}), V('v2','Photo left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Blue card grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Progress rings',{body:'statRings', track:'#4F95DE'}), V('n2','Blue stat cards',{body:'statCards', hl:'first'}), V('n3','Big numbers',{body:'statCols'}), V('n4','Progress bars',{body:'statBars'}), V('v2','Photo left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Milestone cards',{body:'tlCards', hlLast:true, hc:'#FFFFFF'}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + photo right',{body:'tlV', img:'right'})],
    quote:[V('v1','Glass quote',{}), V('v2','Quote + photo right',{img:'right'})]
  }
});
})();

</script>
<?php
}, 1006);
