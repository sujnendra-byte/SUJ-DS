<?php
/**
 * Code Snippets export — DS Template 70 — Night Diamond
 *
 * Snippet ID : 1103
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 05:41:13
 * Load order : 273 of 296
 */
/**
 * DS Template 70 — Night Diamond
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2" and "DS Template Kit 3" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl70_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl70_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('nightdiamond', array(
        'name'     => "Night Diamond",
        'fonts'    => array('head' => "Sora", 'body' => "Mulish"),
        'pal'      => array('bg' => '#0A1630', 'text' => '#FFFFFF', 'accent' => '#C9D6EA', 'accent2' => '#5B86C4'),
        'voice'    => "Academic research voice: structured, objective and evidence-based, with clear findings.",
        'recipe'   => "Introduction, background, problem statement, objectives, framework, methodology, findings, discussion, recommendations, conclusion, thank you.",
        'generate' => 'ds_tpl70_generate',
        'build'    => 'ds_tpl70_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl70_generate') ) {
    function ds_tpl70_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl70_build') ) {
    function ds_tpl70_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-nightdiamond">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 70 - Night Diamond : deep navy night, white title band, stacked silver diamond outlines, glowing steel diamonds */
var BG = '#0A1630', NV = '#13284F', ST = '#5B86C4', SV = '#C9D6EA', TX = '#FFFFFF', MU = '#AFC0DA', INK = '#0A1630';
var P0 = {bg:BG, text:TX, title:TX, muted:MU, accent:SV, accent2:ST, card:NV, line:'#2A426C', onAccent:INK};
function dmd(C, cx, cy, r, o){ return K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], o); }
/* three nested silver outlines, offset like a chevron stack */
function stack(C, cx, cy, r, dir){ for (var k = 0; k < 3; k++) dmd(C, cx + dir * k * r * 0.34, cy, r, {stroke:SV, lw:3, lop:0.95 - k * 0.25}); }
/* glossy steel diamond */
function gem(C, cx, cy, r){ C.shape('diamond', cx - r, cy - r, r * 2, r * 2, {grad:{ang:135, stops:[[0, '#9CB8E2'], [55, '#2E5A96'], [100, '#0E2246']]}}); dmd(C, cx, cy, r, {stroke:SV, lw:2, lop:0.9}); }
function bg(C, kind){
  C.bg({fill:BG});
  D.glow(C, 160, 640, 420, 260, '#1E3D72', 0.55);
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 2;
  if (k) { gem(C, -6, H / 2 + 60, 46); stack(C, W - 40, H / 2 + 150, 50, 1); } else { stack(C, -20, H / 2 + 90, 56, 1); gem(C, W + 4, H / 2 + 110, 48); }
}
function head(C, A, o){
  var y = 40;
  C.rect(o.img === 'left' ? A.x - 48 : 0, y, o.img === 'left' ? W - A.x + 48 : A.x + A.w + 40, 60, {fill:'#FFFFFF'});
  dmd(C, A.x + A.w + 40, y + 30, 30, {fill:'#FFFFFF'});
  C.text(A.x, y, A.w, 60, C.s.title || '', {font:'head', fit:{max:30, min:18}, wt:800, color:INK, caps:true, ls:0.06, align:'c', va:'m', lh:1.05, ed:{f:'title'}, maxLines:1});
  y += 74;
  if (C.s.subtitle) { var se = C.text(A.x + A.w * 0.1, y, A.w * 0.8, 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:MU, align:'c', lh:1.4, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
function diaPhoto(C, cx, cy, r){
  K.diamondImg(C, cx, cy, r, BG, {border:'#FFFFFF', bw:8});
  for (var k = 1; k < 3; k++) C.path([['M', cx + k * r * 0.3, cy - r], ['L', cx + r + k * r * 0.3, cy], ['L', cx + k * r * 0.3, cy + r]], {stroke:SV, lw:3, lop:0.85 - k * 0.25});
}
var X = {
  M:72, tc:TX, mc:MU, ac:SV, markS:44, g:18, head:head, ptc:TX, pmc:MU, footH:70,
  card:function(C, x, y, w, h, k, o){
    if (o.hot) { C.rect(x, y, w, h, {fill:'#FFFFFF', r:4}); return {tc:INK, mc:'#3B4D6B', ac:'#2E5A96'}; }
    C.rect(x, y, w, h, {grad:{ang:160, stops:[[0, '#1A3360'], [100, '#0E1E3E']]}, r:4, line:'#3A5786', lw:1.2});
    C.rect(x, y, w, 3, {fill:SV, op:0.8});
    return {tc:TX, mc:MU, ac:SV};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y, w, h, {fill:k % 2 ? '#0E1E3E' : '#162C55', r:4}); C.rect(x, y, 4, h, {fill:SV}); return {tc:TX, mc:MU, ac:SV}; },
  mark:function(C, x, y, s, t, k, cc){ var r = s / 2; gem(C, x + r, y + r, r); K.icon(C, x + r - s * 0.2, y + r - s * 0.2, s * 0.4, t, '#FFFFFF'); },
  node:function(C, cx, cy, s, k){ gem(C, cx, cy, s / 2); C.text(cx - s / 2, cy - s / 2, s, s, pad2(k + 1), {font:'head', size:s * 0.3, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  track:function(C, x1, y, x2){ C.line(x1, y, x2, y, {color:SV, lw:2, op:0.6}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, Z.x, Z.y, Z.w, Z.h, {r:0}); C.rect(0, Z.y - 4, W, 4, {fill:'#FFFFFF'}); return; }
    diaPhoto(C, Z.x + Z.w / 2 - (side === 'right' ? 20 : 0), H / 2 + 20, Math.min(Z.w * 0.4, 200));
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 400, 16, C.cover.company || '', {font:'head', size:11.5, wt:700, color:MU, caps:true, ls:0.14, lh:1, va:'m'}); C.text(a.x + a.w - 80, y, 80, 16, pad2(C.i + 1), {font:'head', size:12, wt:800, color:SV, align:'r', va:'m', lh:1}); }
var S = {margin:72, series:[SV, ST, '#8FB0E0', '#2E5A96', '#FFFFFF', '#3A5786'], header:{kind:'center', top:56}, card:{kind:'solid', fill:NV, r:4}, icon:{kind:'plain', fg:SV}, num:{kind:'big', fg:SV},
  conc:{kind:'box', fill:'#FFFFFF', op:0.08, r:4, line:'#3A5786', lw:1, label:'Insight', lc:SV, tc:TX}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#5B86C4'], [100, '#0E1E3E']]}}};
/* signature: diamond flow - input, process, output style; one gem per point joined by arrows */
function flow(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return B.rows(C, Z, o);
  var cw = Z.w / n, r = Math.min(cw * 0.2, 56), cy = Z.y + r + 8, tw = cw - 26;
  var F = K.fitHD(C, it, tw, 50, Z.h - r * 2 - 40, {hMax:21, dMax:16, hMin:12, dMin:10});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    if (k < n - 1) { C.line(cx + r + 14, cy, cx + cw - r - 14, cy, {color:SV, lw:2}); K.poly(C, [[cx + cw - r - 14, cy - 6], [cx + cw - r - 4, cy], [cx + cw - r - 14, cy + 6]], {fill:SV}); }
    gem(C, cx, cy, r); K.icon(C, cx - r * 0.4, cy - r * 0.4, r * 0.8, t, '#FFFFFF');
    K.hd(C, cx - tw / 2, cy + r + 22, tw, Z.h - r * 2 - 30, t, F, {tc:TX, mc:MU, align:'c'});
  });
}
/* signature: two-column findings, a silver rule between */
function split(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cl = n > 1 && Z.w > 700 ? 2 : 1, per = Math.ceil(n / cl), cw = (Z.w - 60) / cl, rh = Z.h / per, tw = cw;
  var F = K.fitHD(C, it, tw, 36, rh - 16, {hMax:22, dMax:16, hMin:12, dMin:10});
  if (cl === 2) C.rect(Z.x + cw + 29, Z.y, 2, Z.h, {fill:SV, op:0.5});
  it.forEach(function(t, k){ var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + 60), y = Z.y + r * rh; var bh = K3.bH(C, t, tw, F); K.hd(C, x, y + Math.max(4, (rh - bh) / 2), tw, rh - 8, t, F, {tc:TX, mc:MU, align:cl === 2 ? 'c' : 'l'}); });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  stack(C, W - 60, 120, 90, -1); stack(C, 60, H - 40, 70, 1);
  if (photo) { D.imgBox(C, 0, 470, W, 250, {r:0}); C.rect(0, 466, W, 4, {fill:'#FFFFFF'}); gem(C, W - 170, 470, 90); }
  else { gem(C, 1000, 380, 150); gem(C, 820, 560, 70); stack(C, 1080, 380, 150, 1); }
  if (f.company) { var cw = Math.min(360, f.company.length * 10 + 56); C.rect(72, 54, cw, 38, {fill:'#FFFFFF', r:19}); C.text(72, 54, cw, 38, f.company, {font:'head', size:13, wt:800, color:INK, align:'c', va:'m', caps:true, ls:0.08, lh:1, ed:{f:'tf-company'}, maxLines:1}); }
  var top = photo ? 120 : 170, th = photo ? 200 : 250;
  C.text(72, top, photo ? 900 : 640, th, C.s.title, {font:'head', fit:{max:photo ? 60 : 68, min:28}, wt:800, color:TX, caps:true, lh:1.02, va:'b', ed:{f:'title'}, maxLines:3});
  if (C.s.subtitle) C.text(72, top + th + 14, 600, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who && !photo) C.text(72, 590, 560, 24, who, {font:'body', size:15, wt:700, color:SV, va:'m', lh:1, ed:{f:'tf-presenter'}, maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  stack(C, W - 60, H - 80, 90, -1);
  if (photo) diaPhoto(C, 950, 330, 190); else { gem(C, 960, 330, 170); C.icon(900, 270, 120, 'mdi:star-four-points-outline', '#FFFFFF'); }
  C.text(72, 120, 620, 220, C.s.title || 'Thank You', {font:'head', fit:{max:88, min:36}, wt:800, color:TX, caps:true, lh:1, va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 356, 560, 60, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  D.contacts(C).forEach(function(c, k){ var y = 444 + k * 54; gem(C, 92, y + 20, 20); C.icon(82, y + 10, 20, c.icon, '#FFFFFF'); C.text(128, y, 520, 40, c.val, {font:'body', fit:{max:17, min:12}, wt:600, color:TX, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 640 : W - 260, x = photo ? 72 : 130;
  if (photo) diaPhoto(C, 1010, 360, 180); else { stack(C, 40, 120, 70, 1); stack(C, W - 40, H - 120, 70, -1); }
  C.rect(x, 130, w, 2, {fill:SV, op:0.6});
  var qe = C.text(x, 160, w, 320, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:600, color:TX, align:photo ? 'l' : 'c', va:'m', lh:1.22, ed:{f:'title'}, maxLines:6});
  C.rect(x, 500, w, 2, {fill:SV, op:0.6});
  if (by) C.text(x, 520, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:SV, align:photo ? 'l' : 'c', lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, STP = B.steps;
D.register({
  id:'nightdiamond', name:'Night Diamond', tag:'Deep navy · silver diamonds',
  desc:'A deep navy night page with a bright white title band, stacked silver diamond outlines, glossy steel gems and diamond-cut photos. For research, group projects, reports and academic decks.',
  fonts:{head:'Sora', body:'Mulish', headW:800, bodyW:400, headLh:1.05, gf:'Sora:wght@500;600;700;800|Mulish:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Steel gems', {}), V('v2', 'Photo band', {img:'photo'})],
    closing:[V('v1', 'Steel gem', {}), V('v2', 'Diamond photo', {img:'photo'})],
    agenda:[V('v1', 'Silver rows', pg(R, {cols:2})), V('n2', 'Night cards', pg(CA)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Night panel', pg(B.statement)), V('v2', 'Panel + diamond photo', pg(B.statement, {img:'right'})), V('v3', 'Diamond photo + panel', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Split findings', pg(split)), V('n2', 'Night cards', pg(CA, {hl:true})), V('n3', 'Diamond flow', pg(flow)), V('v2', 'Cards + diamond photo', pg(CA, {img:'right', cols:1})), V('v3', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p3:[V('v1', 'Diamond flow', pg(flow)), V('n2', 'Night cards', pg(CA, {hl:true})), V('n3', 'Silver rows', pg(R)), V('v2', 'Rows + diamond photo', pg(R, {img:'right'})), V('v3', 'Diamond photo + rows', pg(R, {img:'left'})), V('v4', 'Cards + photo strip', pg(CA, {img:'bottom'}))],
    p4:[V('v1', 'Diamond flow', pg(flow)), V('n2', 'Night cards', pg(CA)), V('n3', 'Split findings', pg(split)), V('v2', 'Rows + diamond photo', pg(R, {img:'right'}))],
    p56:[V('v1', 'Night cards', pg(CA)), V('n2', 'Split findings', pg(split)), V('n3', 'Silver rows', pg(R)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Night cards', pg(CA)), V('n2', 'Silver rows', pg(R)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'Night number cards', pg(B.stats, {hot:0, center:true})), V('n2', 'Progress rings', {body:'statRings', track:'#1A3360'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + diamond photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Gem steps', pg(STP)), V('n2', 'Diamond flow', pg(flow)), V('v2', 'Rows + diamond photo', pg(R, {img:'right', cols:1})), V('v3', 'Steps + photo strip', pg(STP, {img:'bottom'}))],
    quote:[V('v1', 'Ruled quote', {}), V('v2', 'Quote + diamond photo', {img:'photo'})]
  }
});
})();

</script>
<?php
}, 1085);
