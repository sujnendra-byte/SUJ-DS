<?php
/**
 * Code Snippets export — DS Template 01 — Glassline
 *
 * Snippet ID : 874
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-09-29 08:14:22
 * Load order : 152 of 157
 */
/**
 * DS Template 01 — Glassline
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" snippet to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl01_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote
 *                and closing designs with several layout options each (JS below)
 *  - Download:   its own PPTX build (ds_tpl01_build) — the same elements the
 *                editor shows are written into PowerPoint.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('glassline', array(
        'name'     => "Glassline",
        'fonts'    => array('head' => "Outfit", 'body' => "Inter"),
        'pal'      => array('bg' => '#DCE3EA', 'text' => '#2E3236', 'accent' => '#3F6FB5', 'accent2' => '#8FB6EC'),
        'voice'    => "Modern startup voice: confident, optimistic, crisp. Lead with the customer problem and quantified traction.",
        'recipe'   => "Problem, solution, market, business model, traction, roadmap, team ask.",
        'generate' => 'ds_tpl01_generate',
        'build'    => 'ds_tpl01_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl01_generate') ) {
function ds_tpl01_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl01_build') ) {
function ds_tpl01_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-glassline">
/* Template 01 — Glassline : frosted blue glass, charcoal display type (Startup sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, u = D.u, V = D.V;
var LIGHT = {bg:'#DCE3EA', text:'#2E3236', title:'#2E3236', muted:'#59626B', accent:'#3F6FB5', accent2:'#8FB6EC', card:'#FFFFFF', line:'#AEBCCB', onAccent:'#FFFFFF'};
var DARK  = {bg:'#3A3D41', text:'#F3F5F7', title:'#FFFFFF', muted:'#C4CAD1', accent:'#9CC2F4', accent2:'#6E95D6', card:'#FFFFFF', line:'#6A7078', onAccent:'#1E2A3A'};
var S = {
  margin:70,
  header:{kind:'left', tMax:52, tMin:28, sMax:18},
  card:{kind:'glass', r:18, op:0.42, lop:0.8, hl:{kind:'grad', grad:{ang:135, stops:[[0,'#A9CCF7'],[100,'#EEF5FE']]}, dark:false, tc:'#1F2A36', mc:'#3F4B57'}},
  icon:{kind:'plain', fg:'#2E3236'},
  num:{kind:'ring', bg:'#2E3236', fg:'#2E3236'},
  conc:{kind:'pill', fill:'#FFFFFF', op:0.5, line:'#FFFFFF', lw:1, label:'Takeaway', lc:'#3F6FB5'},
  footer:{pagePrefix:'PAGE '},
  imgFramed:true, imgR:22
};
var SD = D.merge(S, {card:{kind:'glass', op:0.08, lop:0.3, line:'#FFFFFF', hl:{kind:'grad', grad:{ang:135, stops:[[0,'#9CC2F4'],[100,'#DCEAFD']]}, dark:false, tc:'#1E2A3A', mc:'#34465A'}}, icon:{kind:'plain', fg:'#FFFFFF'}, num:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF'}, conc:{fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lc:'#9CC2F4', tc:'#F3F5F7'}});
function isDark(C, kind){ return kind==='stats' || kind==='timeline' || kind==='quote' || (kind==='p4' && C.i % 2 === 1); }

function bg(C, kind){
  var dark = isDark(C, kind) && C.vid !== 'n9';
  if (dark) { C.P = DARK; C.S = SD; } else { C.P = LIGHT; C.S = S; }
  var P = C.P;
  C.bg({fill:P.bg});
  if (dark) {
    D.glow(C, 1120, 120, 420, 300, '#6F93CF', 0.55);
    D.glow(C, 200, 700, 380, 260, '#C49A6C', 0.25);
    D.glow(C, 640, 360, 520, 260, '#4E5C70', 0.35);
  } else {
    D.glow(C, 1150, 60, 460, 330, '#9EC1F0', 0.95);
    D.glow(C, 80, 640, 420, 300, '#B7CFEF', 0.85);
    D.glow(C, 760, 520, 300, 200, '#E8D9F3', 0.55);
    C.shape('parallelogram', 760, -40, 620, 820, {fill:'#FFFFFF', op:0.16});
  }
  D.hexOutline(C, 1180, 640, 70, {color:'#FFFFFF', op:dark ? 0.12 : 0.55});
  D.hexOutline(C, 90, 110, 44, {color:'#FFFFFF', op:dark ? 0.1 : 0.45});
}
function brand(C, x, y, color){
  var name = C.cover.company || 'Company';
  D.hexOutline(C, x + 11, y + 11, 11, {color:color, op:1, lw:2.2});
  C.ell(x + 8, y + 8, 6, 6, {fill:color});
  C.text(x + 30, y, 360, 22, name, {font:'head', size:17, color:color, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null});
}

function cover(C, v){
  var P = C.P, f = C.cover, M = 70, o = C.o || {};
  brand(C, M, 40, P.text);
  C.rect(M, 84, W - 2*M, 1.2, {fill:P.text, op:0.55});
  if (f.presenter) {
    C.text(W - M - 360, 42, 360, 16, 'Presented by :', {font:'body', size:11.5, wt:700, color:P.text, align:'r', lh:1});
    C.text(W - M - 360, 60, 360, 18, [f.presenter, f.designation].filter(Boolean).join(', '), {font:'body', size:12.5, color:P.muted, align:'r', lh:1, ed:{f:'tf-presenter'}});
  }
  if (o.img === 'right') {
    D.imgBox(C, 700, 120, W - 700 - M, 520, {r:26});
    C.rect(M - 10, 330, 700, 300, {fill:'#FFFFFF', op:0.35, line:'#FFFFFF', lop:0.8, lw:1.2, r:22});
    C.text(M + 20, 350, 640, 170, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:3, va:'b', lh:1.0});
    if (C.s.subtitle) C.text(M + 20, 536, 620, 76, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.4, maxLines:3});
    return;
  }
  var te = C.text(M - 4, 170, 900, 420, C.s.title, {font:'head', fit:{max:124, min:48}, color:P.title, ed:{f:'title'}, maxLines:3, va:'b', lh:0.98, ls:-0.02});
  if (C.s.subtitle) C.text(W - M - 280, 470, 280, 110, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:P.text, ed:{f:'subtitle'}, va:'b', lh:1.35, maxLines:5});
  var meta = [f.date, f.website].filter(Boolean).join('   •   ');
  if (meta) C.text(M, 640, 700, 20, meta, {font:'body', size:13, wt:600, color:P.muted, lh:1});
  C.text(W - M - 120, 684, 120, 16, 'PAGE 01', {font:'body', size:11, wt:700, color:P.muted, align:'r', lh:1});
}

function closing(C, v){
  var P = C.P, M = 70;
  brand(C, M, 40, P.text);
  C.rect(M, 84, W - 2*M, 1.2, {fill:P.text, op:0.55});
  C.text(M - 4, 180, 720, 420, C.s.title || 'Thank You', {font:'head', fit:{max:150, min:56}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:0.95, ls:-0.02});
  if (C.s.subtitle) C.text(M, 610, 640, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(840, 300, 380, 300, {fill:'#FFFFFF', op:0.4, line:'#FFFFFF', lop:0.85, lw:1.2, r:22});
  D.contactList(C, 870, 330, 330, {gap:66, markO:{kind:'circle', bg:'#2E3236', fg:'#FFFFFF'}});
  if (C.s.conclusion) C.text(870, 530, 330, 56, C.s.conclusion, {font:'body', fit:{max:14, min:11}, color:P.text, ed:{f:'x-conc'}, lh:1.35, maxLines:3});
}

function agenda(C, v){
  var P = C.P;
  brand(C, 70, 40, P.text);
  D.agendaSplit(C, {split:0.45, titleY:250, tMax:80, numC:P.text, itemWt:500, listY:60, rowMax:58});
}

function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:C.P.accent}); return; }
  C.rect(150, 110, W - 300, H - 220, {fill:'#FFFFFF', op:0.08, line:'#FFFFFF', lop:0.3, lw:1.2, r:28});
  D.B.quote(C, {x:210, y:150, w:W - 420, h:H - 300}, {qc:C.P.accent});
}

D.register({
  id:'glassline', name:'Glassline', tag:'Frosted glass · startup', desc:'Soft blue glass panels, charcoal display type and alternating dark insight slides.',
  fonts:{head:'Outfit', body:'Inter', headW:700, bodyW:400, headLh:1.02, headLs:-0.01, gf:'Outfit:wght@400;600;700;800|Inter:wght@400;500;600;700'},
  pal:LIGHT, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Big type',{}), V('v2','Glass card + image',{img:'right'})],
    agenda:[V('v1','Split list',{})],
    text:D.defaultVariants.text,
    p2:[V('v1','Glass panels',{body:'duo', hl:true}), V('n2','Two panels + VS',{body:'duo', vs:true}), V('v2','Image right + cards',{body:'list', img:'right', cards:true}), V('v3','Image left + list',{body:'list', img:'left'})],
    p3:[V('v1','Glass cards',{body:'cols', hl:'first'}), V('n2','Icon list',{body:'list', rules:true}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + cards',{body:'list', img:'left', cards:true}), V('v3','Cards + image right',{body:'list', img:'right', cards:true}), V('v4','Cards + image bottom',{body:'cols', img:'bottom'})],
    p4:[V('v1','Offset glass grid',{body:'grid', hl:'first', mark:'num'}), V('n2','Four columns',{body:'cols', hl:'alt'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + list',{body:'list', img:'left', cards:true}), V('v3','List + image right',{body:'list', img:'right'})],
    p56:[V('v1','Glass grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1, cards:true})],
    p78:[V('v1','Glass grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('v2','List + image right',{body:'list', img:'right', cols:2})],
    stats:[V('v1','Big numbers',{body:'statCols', vc:'#9CC2F4'}), V('n2','Glass stat cards',{body:'statCards', icons:true, hl:'first'}), V('n3','Progress rings',{body:'statRings', track:'#555A60'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Year pills',{body:'tlCards'}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Glass quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();

</script>
<?php
}, 1006);
