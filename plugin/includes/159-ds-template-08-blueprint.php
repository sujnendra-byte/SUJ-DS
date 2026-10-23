<?php
/**
 * Code Snippets export — DS Template 08 — Blueprint
 *
 * Snippet ID : 881
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-09-29 08:14:30
 * Load order : 159 of 157
 */
/**
 * DS Template 08 — Blueprint
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" snippet to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl08_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote
 *                and closing designs with several layout options each (JS below)
 *  - Download:   its own PPTX build (ds_tpl08_build) — the same elements the
 *                editor shows are written into PowerPoint.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('blueprint', array(
        'name'     => "Blueprint",
        'fonts'    => array('head' => "Exo 2", 'body' => "Titillium Web"),
        'pal'      => array('bg' => '#0E2E74', 'text' => '#FFFFFF', 'accent' => '#7CC6FF', 'accent2' => '#3A8DF0'),
        'voice'    => "Technical but accessible voice: precise terminology, systems thinking and data-backed statements.",
        'recipe'   => "Overview, core components, how it works, applications, data and results, responsible use, future outlook.",
        'generate' => 'ds_tpl08_generate',
        'build'    => 'ds_tpl08_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl08_generate') ) {
function ds_tpl08_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl08_build') ) {
function ds_tpl08_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-blueprint">
/* Template 08 — Blueprint : royal blue, HUD frames, airy tracked type (AI sample 2) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#0E2E74', text:'#FFFFFF', title:'#FFFFFF', muted:'#BFD3F5', accent:'#7CC6FF', accent2:'#3A8DF0', card:'#173C8F', line:'#3F67B8', onAccent:'#0E2E74'};
var S = {
  margin:70,
  header:{kind:'center', tMax:46, tMin:26, sMax:17, top:70},
  card:{kind:'outline', r:16, fill:'#1A4299', op:0.55, line:'#7CC6FF', lw:1.4, dark:true, tc:'#FFFFFF', mc:'#CFE0FA', hl:{kind:'solid', fill:'#DCE9FF', dark:false, tc:'#0E2E74', mc:'#284B8E'}},
  icon:{kind:'square', r:6, bg:'#FFFFFF', fg:'#0E2E74'},
  num:{kind:'square', r:6, bg:'#FFFFFF', fg:'#0E2E74'},
  conc:{kind:'line', label:'Takeaway', lc:'#7CC6FF', lineC:'#3F67B8', tc:'#FFFFFF'},
  footer:{brand:true, pagePrefix:'PAGE '},
  imgFramed:true, imgR:8, imgPh:{grad:{ang:135, stops:[[0,'#2A5CC8'],[100,'#0B1E52']]}}
};
function hud(C, x, y, w, h, o){
  o = o || {}; var c = o.c || 28, col = o.color || '#7CC6FF';
  C.path([['M',x+c,y],['L',x+w*0.3,y],['L',x+w*0.33,y+10],['L',x+w*0.67,y+10],['L',x+w*0.7,y],['L',x+w-c,y],['L',x+w,y+c],['L',x+w,y+h-c],['L',x+w-c,y+h],['L',x+c,y+h],['L',x,y+h-c],['L',x,y+c],['Z']], {stroke:col, lw:1.6, lop:0.9, fill:o.fill || null, op:o.op});
  for (var k = 0; k < 6; k++) C.shape('parallelogram', x + 40 + k * 16, y + h + 10, 12, 8, {fill:col, op:0.8});
  for (var j = 0; j < 6; j++) C.shape('parallelogram', x + w - 136 + j * 16, y + h + 10, 12, 8, {fill:col, op:0.8});
}
function grid(C){
  for (var x = 0; x <= W; x += 80) C.rect(x, 0, 1, H, {fill:'#FFFFFF', op:0.035});
  for (var y = 0; y <= H; y += 80) C.rect(0, y, W, 1, {fill:'#FFFFFF', op:0.035});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0,'#133A8E'],[100,'#0A2260']]}});
  grid(C);
  D.glow(C, 640, 820, 700, 260, '#3A8DF0', 0.35);
  C.rect(70, 36, W - 140, 1, {fill:'#FFFFFF', op:0.35});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  D.glow(C, 640, 600, 620, 200, '#6FC3FF', 0.75);
  D.glow(C, 640, 640, 260, 90, '#FFFFFF', 0.55);
  if (o.img) D.imgBox(C, 0, 470, W, 250, {});
  C.rect(70, 40, W - 140, 1, {fill:'#FFFFFF', op:0.5});
  if (f.company) C.text(70, 58, 500, 20, f.company, {font:'body', size:13, color:'#FFFFFF', lh:1, ls:0.2, caps:true, ed:{f:'tf-company'}});
  var words = String(C.s.title || '').split(/\s+/), cut = words.length > 2 ? Math.ceil(words.length / 2) : 1;
  C.text(140, 150, W - 280, 60, words.slice(0, cut).join(' '), {font:'body', fit:{max:38, min:18}, color:'#FFFFFF', align:'c', ls:0.35, lh:1, maxLines:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (words.length > cut) C.text(80, 222, W - 160, 150, words.slice(cut).join(' '), {font:'head', fit:{max:84, min:34}, color:'#FFFFFF', align:'c', caps:true, lh:1.02, maxLines:2, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(240, 385, W - 480, 56, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:'#CFE0FA', align:'c', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(70, H - 60, W - 140, 1, {fill:'#FFFFFF', op:0.5});
  var who = [f.presenter ? 'Presented by ' + f.presenter : '', f.designation].filter(Boolean).join(', ');
  if (who) C.text(70, H - 48, 600, 18, who, {font:'body', size:12, color:'#FFFFFF', lh:1});
  if (f.date || f.website) C.text(W - 570, H - 48, 500, 18, [f.date, f.website].filter(Boolean).join('  |  '), {font:'body', size:12, color:'#FFFFFF', lh:1, align:'r'});
}
function closing(C, v){
  var P = C.P;
  D.glow(C, 640, 560, 520, 220, '#6FC3FF', 0.7);
  C.text(140, 160, W - 280, 40, C.s.subtitle || 'Thank you for your', {font:'body', fit:{max:26, min:14}, color:'#FFFFFF', align:'c', ls:0.3, lh:1, maxLines:1, ed:{f:'subtitle'}});
  C.text(100, 210, W - 200, 110, C.s.title || 'Attention', {font:'head', fit:{max:80, min:36}, color:'#FFFFFF', align:'c', caps:true, ls:0.08, ed:{f:'title'}, maxLines:1});
  hud(C, 180, 380, W - 360, 180, {fill:'#1A4299', op:0.4});
  D.contactRow(C, 210, 440, W - 420, {});
}
function agenda(C, v){
  var P = C.P, it = C.it.slice(0, 8), n = it.length;
  C.path([['M',70,560],['C',140,380,110,200,260,150],['C',330,130,380,200,360,300]], {stroke:'#7CC6FF', lw:1.4, lop:0.6});
  for (var k = 0; k < 7; k++) C.text(70 + (k % 2) * 20, 180 + k * 44, 260, 30, '0110 1001 01', {font:'body', size:16, color:'#7CC6FF', op:0.35 - k * 0.03, lh:1, ls:0.2});
  C.text(460, 70, 700, 80, C.s.title || 'Agenda', {font:'head', fit:{max:58, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  var cols = n > 4 ? 2 : 1, per = Math.ceil(n / cols), cw = cols === 2 ? 350 : 700, rh = Math.min(84, 470 / per);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = 460 + c * (cw + 30), y = 180 + r * rh;
    C.rect(x, y + (rh - 36) / 2, 36, 36, {fill:'#FFFFFF', r:6});
    C.text(x, y + (rh - 36) / 2, 36, 36, String(k + 1), {font:'head', size:18, color:'#0E2E74', align:'c', va:'m', lh:1, caps:false});
    C.text(x + 52, y + 6, cw - 60, rh * 0.45, t.h || t.d, {font:'head', fit:{max:19, min:12}, color:'#FFFFFF', va:'b', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    if (t.h && t.d) C.text(x + 52, y + rh * 0.45 + 10, cw - 60, rh * 0.45 - 6, t.d, {font:'body', fit:{max:14, min:10}, color:'#BFD3F5', lh:1.25, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#7CC6FF'}); return; }
  hud(C, 140, 110, W - 280, H - 250, {fill:'#1A4299', op:0.35});
  D.B.quote(C, {x:200, y:140, w:W - 400, h:H - 300}, {qc:'#7CC6FF', qFont:'body'});
}
function frameStd(C, o){
  hud(C, 30, 30, W - 60, H - 74, {fill:'#1A4299', op:0.18});
  var oo = D.merge(o, {}); D.std(C, oo);
}
D.register({
  id:'blueprint', name:'Blueprint', tag:'Royal blue · futuristic', desc:'Royal-blue blueprint grid, HUD frames, airy tracked lettering and outlined info panels.',
  fonts:{head:'Exo 2', body:'Titillium Web', headW:600, bodyW:400, headLh:1.05, headLs:0.02, gf:'Exo 2:wght@400;500;600;700|Titillium Web:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Glow horizon',{}), V('v2','Image horizon',{img:'bottom'})],
    agenda:[V('v1','Binary agenda',{})],
    text:[V('v1','Framed statement',{fn:function(C,o){ hud(C, 140, 110, W - 280, H - 250, {fill:'#1A4299', op:0.35}); D.std(C, {body:'text'}); }}), V('v2','Statement + image right',{body:'text', img:'right'})],
    p2:[V('v1','Outlined panels',{body:'duo'}), V('n2','Framed columns',{foot:false, fn:function(C){ frameStd(C, {body:'numbered', hk:'center'}); }}), V('v2','Image left + list',{body:'list', img:'left', mark:'num'}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Outlined columns',{body:'cols', center:true}), V('n2','HUD framed list',{foot:false, fn:function(C){ frameStd(C, {body:'list', mark:'num', rules:true}); }}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', mark:'num'}), V('v3','List + image right',{body:'list', img:'right'})],
    p4:[V('v1','Mind map',{body:'hub'}), V('n2','Outlined grid',{body:'grid'}), V('n3','Four columns',{body:'cols', center:true}), V('v2','Image left + list',{body:'list', img:'left', mark:'num'}), V('v3','Grid + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Outlined grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Outlined grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Framed numbers',{foot:false, fn:function(C){ frameStd(C, {body:'statCols', vc:'#FFFFFF'}); }}), V('n2','Outlined stat panels',{body:'statCards', icons:true}), V('n3','Progress rings',{body:'statRings', track:'#1F4AA3'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Signal line',{body:'tlH'}), V('n2','Outlined milestones',{body:'tlCards'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','HUD quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

</script>
<?php
}, 1006);
