<?php
/**
 * Code Snippets export — DS Template 71 — Profile Block
 *
 * Snippet ID : 1104
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 05:41:14
 * Load order : 274 of 296
 */
/**
 * DS Template 71 — Profile Block
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)", "DS Template Kit 2" and "DS Template Kit 3" snippets to be active.
 *  - Generation: its own AI prompt voice + storyline (ds_tpl71_generate)
 *  - Layouts:    its own cover, agenda, content, stats, timeline, quote and closing designs (JS below)
 *  - Download:   ds_tpl71_build - the same shapes the editor shows are written into PowerPoint.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('profileblock', array(
        'name'     => "Profile Block",
        'fonts'    => array('head' => "Raleway", 'body' => "Lato"),
        'pal'      => array('bg' => '#FFFFFF', 'text' => '#232323', 'accent' => '#1C3F7A', 'accent2' => '#2D5AA6'),
        'voice'    => "Corporate profile voice: factual, proud and concise, with key figures up front.",
        'recipe'   => "Company profile, introduction with key figures, mission and vision, key products and services, target market, competitive advantage, social responsibility, leadership, future outlook, contact.",
        'generate' => 'ds_tpl71_generate',
        'build'    => 'ds_tpl71_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl71_generate') ) {
    function ds_tpl71_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
        return ds_tpl_generate( $req, $T, $mode );
    }
}
if ( ! function_exists('ds_tpl71_build') ) {
    function ds_tpl71_build( WP_REST_Request $req, $T ) {
        return ds_tpl_build( $req, $T );
    }
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
?>
<script id="ds-tpl-profileblock">
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || !D.K3) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, K3 = D.K3, pad2 = D.u.pad2;
/* Template 71 - Profile Block : white page, offset navy blocks behind square photos, grey icon tiles, navy KPI circles */
var BG = '#FFFFFF', NV = '#1C3F7A', NV2 = '#2D5AA6', GR = '#D9DADC', GL = '#F1F2F4', INK = '#232323', MU = '#666A70';
var P0 = {bg:BG, text:INK, title:INK, muted:MU, accent:NV, accent2:NV2, card:GL, line:'#DADCE0', onAccent:'#FFFFFF'};
function bg(C, kind){
  C.bg({fill:BG});
  if (kind === 'cover' || kind === 'closing') return;
  var k = (C.i || 0) % 3;
  if (k === 0) { C.rect(0, 0, 150, 26, {fill:NV}); C.rect(W - 26, 120, 26, 90, {fill:NV}); C.rect(W - 26, 222, 26, 26, {fill:GR}); }
  else if (k === 1) { C.rect(W - 180, 0, 180, 26, {fill:NV}); C.rect(0, H - 120, 26, 90, {fill:NV}); }
  else { C.rect(0, 0, 26, 130, {fill:NV}); C.rect(W - 220, H - 26, 220, 26, {fill:NV}); C.rect(W - 246, H - 26, 20, 26, {fill:GR}); }
}
function head(C, A, o){
  var y = 58;
  C.text(A.x, y, A.w, 16, (C.cover.company || 'Company profile'), {font:'body', size:11.5, wt:700, color:MU, caps:true, ls:0.16, lh:1, va:'m', maxLines:1});
  y += 22;
  var te = C.text(A.x, y, A.w, 92, C.s.title || '', {font:'head', fit:{max:40, min:22}, wt:800, color:INK, caps:true, lh:1.06, ed:{f:'title'}, maxLines:2});
  y += te._h + 10;
  if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:MU, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
  return y;
}
/* photo with a navy block offset behind it */
function block(C, x, y, w, h, k){ var d = 22; C.rect(x + (k ? -d : d), y + d, w, h, {fill:NV}); D.imgBox(C, x, y, w, h, {r:0}); }
var X = {
  M:72, tc:INK, mc:MU, ac:NV, markS:46, g:18, head:head,
  card:function(C, x, y, w, h, k, o){
    if (o.big) { C.rect(x, y, w, h, {fill:GL}); C.rect(x, y + h - 8, w * 0.4, 8, {fill:NV}); return {tc:INK, mc:MU, ac:NV}; }
    if (o.hot || (o.stat && k % 2 === 0)) { C.rect(x, y, w, h, {fill:NV}); return {tc:'#FFFFFF', mc:'#D3DCEC', ac:'#FFFFFF'}; }
    C.rect(x, y, w, h, {fill:GL}); C.rect(x, y + h - 5, w, 5, {fill:NV});
    return {tc:INK, mc:MU, ac:NV};
  },
  row:function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:'#DADCE0'}); return {tc:INK, mc:MU, ac:NV}; },
  mark:function(C, x, y, s, t, k, cc){ var on = cc && cc.tc === '#FFFFFF'; C.rect(x, y, s, s, {fill:on ? '#FFFFFF' : NV}); K.icon(C, x + s * 0.22, y + s * 0.22, s * 0.56, t, on ? NV : '#FFFFFF'); },
  rowMark:function(C, x, y, s, t, k){ C.rect(x, y, s, s, {fill:k % 2 ? NV2 : NV}); C.text(x, y, s, s, pad2(k + 1), {font:'head', size:s * 0.38, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); },
  node:function(C, cx, cy, s, k){ C.rect(cx - s / 2, cy - s / 2, s, s, {fill:NV}); K.icon(C, cx - s * 0.28, cy - s * 0.28, s * 0.56, {i:k, h:'', d:''}, '#FFFFFF'); },
  track:function(C, x1, y, x2){ C.rect(x1, y - 1, x2 - x1, 2, {fill:GR}); },
  photo:function(C, Z, side){
    if (side === 'bottom') { D.imgBox(C, 72, Z.y, W - 144, Z.h - 20, {r:0}); C.rect(72, Z.y - 8, 180, 8, {fill:NV}); return; }
    block(C, Z.x + 30, 70, Z.w - 90, H - 170, side === 'left' ? 0 : 1);
  }
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 42; C.text(a.x, y, 400, 16, C.cover.website || '', {font:'body', size:11.5, wt:600, color:MU, lh:1, va:'m'}); C.rect(a.x + a.w - 50, y, 50, 18, {fill:NV}); C.text(a.x + a.w - 50, y, 50, 18, pad2(C.i + 1), {font:'head', size:11, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1}); }
var S = {margin:72, series:[NV, NV2, '#7C9CD0', GR, '#666A70', '#232323'], header:{kind:'left', top:60}, card:{kind:'solid', fill:GL, r:0}, icon:{kind:'square', bg:NV, fg:'#FFFFFF', r:0}, num:{kind:'big', fg:NV},
  conc:{kind:'band', fill:NV, r:0, label:'Summary', tc:'#FFFFFF', lc:'#AFC3E6'}, footer:{fn:foot}, imgFramed:false, imgR:0, bodyGap:24, imgPh:{grad:{ang:135, stops:[[0, '#9DB3D8'], [100, '#1C3F7A']]}}};
/* signature: KPI circles - navy circles with a grey ring offset behind */
function kpi(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw * 0.34, Z.h * 0.3, 118), vals = st.map(K3.sv), labs = st.map(K3.sl), tw = cw - 30, cy = Z.y + Math.max(r + 10, (Z.h - r * 2 - 130) / 2 + r);
  var vs = C.groupFit(vals, r * 1.5, r * 0.7, {font:'head', max:44, min:16, maxLines:1}), ls = C.groupFit(labs, tw, 52, {font:'head', max:19, min:12, maxLines:2});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    C.ell(cx - r - 8, cy - r - 8, r * 2 + 16, r * 2 + 16, {line:GR, lw:3});
    C.ell(cx - r, cy - r, r * 2, r * 2, {fill:k % 2 ? NV2 : NV});
    C.text(cx - r * 0.8, cy - r * 0.4, r * 1.6, r * 0.8, vals[k], {font:'head', size:vs, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var le = C.text(cx - tw / 2, cy + r + 26, tw, 52, labs[k], {font:'head', size:ls, wt:700, color:INK, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - tw / 2, cy + r + 32 + le._h, tw, Z.y + Z.h - (cy + r + 32 + le._h), t.x, {font:'body', size:13.5, color:MU, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}
/* signature: product tiles - grey icon squares on a navy band with a pin line down to the text */
function tiles(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (n < 2 || Z.w < 760) return B.cards(C, Z, o);
  var cw = Z.w / n, s = Math.min(cw * 0.6, 110), tw = cw - 24, by = Z.y + s * 0.55;
  C.rect(0, by, W, Z.y + Z.h - by + 6, {fill:NV});
  var F = K.fitHD(C, it, tw, 50, Z.y + Z.h - (Z.y + s + 50), {hMax:23, dMax:17, hMin:12, dMin:10});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    C.rect(cx - s / 2, Z.y, s, s, {fill:GL, line:'#FFFFFF', lw:3});
    K.icon(C, cx - s * 0.25, Z.y + s * 0.25, s * 0.5, t, NV);
    C.line(cx, Z.y + s, cx, Z.y + s + 18, {color:'#FFFFFF', lw:2}); C.ell(cx - 4, Z.y + s + 16, 8, 8, {fill:'#FFFFFF'});
    K.hd(C, cx - tw / 2, Z.y + s + 36, tw, Z.h - s - 40, t, F, {tc:'#FFFFFF', mc:'#D3DCEC', align:'c'});
  });
}
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function cover(C, v){
  var o = C.o || {}, f = C.cover, photo = o.img === 'photo';
  C.rect(0, 0, 330, 70, {fill:NV}); C.rect(W - 40, 70, 40, 360, {fill:NV});
  if (photo) { D.imgBox(C, 70, 70, W - 140, 330, {r:0}); C.rect(W - 110, 300, 24, 24, {fill:'#FFFFFF'}); C.rect(W - 110, 336, 24, 24, {fill:'#FFFFFF'}); }
  else { C.rect(70, 70, W - 140, 330, {fill:GL}); for (var i = 0; i < 5; i++) C.rect(700 + i * 90, 400 - (60 + i * 50), 60, 60 + i * 50, {fill:i === 4 ? NV : (i % 2 ? NV2 : GR)}); }
  C.rect(70, 70, 22, 330, {fill:NV});
  if (f.company) C.text(108, 0, 300, 70, f.company, {font:'head', size:15, wt:800, color:'#FFFFFF', va:'m', caps:true, ls:0.08, lh:1, ed:{f:'tf-company'}, maxLines:1});
  C.text(72, 430, 760, 140, C.s.title, {font:'head', fit:{max:56, min:26}, wt:800, color:INK, caps:true, lh:1.04, va:'t', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(72, 578, 680, 56, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) C.text(860, 440, 350, 40, who, {font:'body', fit:{max:15, min:11}, wt:700, color:NV, align:'r', va:'m', lh:1.2, ed:{f:'tf-presenter'}, maxLines:2});
  C.rect(860, 650, 350, 2, {fill:INK});
  if (f.website) C.text(860, 620, 350, 20, f.website, {font:'body', size:13, color:INK, align:'r', lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var o = C.o || {}, photo = o.img === 'photo';
  C.rect(70, 70, W - 140, H - 140, {fill:NV});
  C.rect(0, 0, 220, 70, {fill:GR}); C.rect(W - 220, H - 70, 220, 70, {fill:GR}); C.rect(W - 110, 0, 110, 230, {fill:GR});
  if (photo) block(C, 760, 130, 380, 300, 1);
  C.text(140, 150, photo ? 560 : W - 280, 170, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:32}, wt:800, color:'#FFFFFF', caps:true, lh:1, align:photo ? 'l' : 'c', va:'b', ed:{f:'title'}, maxLines:2});
  if (C.s.subtitle) C.text(140, 336, photo ? 560 : W - 280, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#D3DCEC', lh:1.4, align:photo ? 'l' : 'c', ed:{f:'subtitle'}, maxLines:2});
  var cs = D.contacts(C), cw = (W - 280) / Math.max(1, cs.length);
  cs.forEach(function(c, k){ var x = 140 + k * cw; C.icon(x + cw / 2 - 12, 480, 24, c.icon, '#FFFFFF'); C.text(x + 10, 516, cw - 20, 40, c.val, {font:'body', fit:{max:15, min:11}, wt:600, color:'#FFFFFF', align:'c', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); });
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0], photo = o.img === 'photo', w = photo ? 620 : W - 144;
  if (photo) block(C, 780, 110, 400, 460, 1);
  C.rect(72, 110, 80, 80, {fill:NV}); C.text(72, 110, 80, 80, '“', {font:'head', size:70, wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false});
  var qe = C.text(72, 230, w, 300, C.s.title, {font:'head', fit:{max:photo ? 36 : 44, min:20}, wt:700, color:INK, lh:1.2, ed:{f:'title'}, maxLines:6});
  if (by) C.text(72, 230 + qe._h + 26, w, 24, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:NV, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
}
var R = B.rows, CA = B.cards, ST = B.steps;
D.register({
  id:'profileblock', name:'Profile Block', tag:'White · navy blocks',
  desc:'White pages with offset navy blocks behind square photos, grey icon tiles on a navy band and navy KPI circles. For company profiles, annual reports, corporate and real-estate decks.',
  fonts:{head:'Raleway', body:'Lato', headW:800, bodyW:400, headLh:1.06, gf:'Raleway:wght@600;700;800|Lato:wght@400;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Navy blocks', {}), V('v2', 'Photo banner', {img:'photo'})],
    closing:[V('v1', 'Navy panel', {}), V('v2', 'Navy panel + photo', {img:'photo'})],
    agenda:[V('v1', 'Numbered rows', pg(R, {cols:2})), V('n2', 'Grey tiles', pg(CA)), V('v2', 'Rows + block photo', pg(R, {img:'right', cols:1}))],
    text:[V('v1', 'Grey panel', pg(B.statement)), V('v2', 'Panel + block photo', pg(B.statement, {img:'right'})), V('v3', 'Block photo + panel', pg(B.statement, {img:'left'}))],
    p2:[V('v1', 'Grey tiles', pg(CA, {hl:true})), V('n2', 'Numbered rows', pg(R)), V('v2', 'Block photo + rows', pg(R, {img:'left'})), V('v3', 'Tiles + block photo', pg(CA, {img:'right', cols:1})), V('v4', 'Tiles + photo strip', pg(CA, {img:'bottom'}))],
    p3:[V('v1', 'Product tiles', pg(tiles)), V('n2', 'Grey tiles', pg(CA, {hl:true})), V('n3', 'Numbered rows', pg(R)), V('v2', 'Block photo + rows', pg(R, {img:'left'})), V('v3', 'Rows + block photo', pg(R, {img:'right'}))],
    p4:[V('v1', 'Product tiles', pg(tiles)), V('n2', 'Grey tiles', pg(CA)), V('n3', 'Numbered rows', pg(R, {cols:2})), V('v2', 'Block photo + rows', pg(R, {img:'left'}))],
    p56:[V('v1', 'Product tiles', pg(tiles)), V('n2', 'Grey tiles', pg(CA)), V('n3', 'Numbered rows', pg(R)), V('v2', 'Rows + block photo', pg(R, {img:'right', cols:1}))],
    p78:[V('v1', 'Grey tiles', pg(CA)), V('n2', 'Numbered rows', pg(R)), V('v2', 'Rows + block photo', pg(R, {img:'right', cols:1, max:8}))],
    stats:[V('v1', 'KPI circles', pg(kpi)), V('n2', 'Number tiles', pg(B.stats)), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + block photo', pg(B.stats, {img:'right'}))],
    timeline:[V('v1', 'Square steps', pg(ST)), V('n2', 'Product tiles', pg(tiles)), V('v2', 'Rows + block photo', pg(R, {img:'right', cols:1})), V('v3', 'Steps + photo strip', pg(ST, {img:'bottom'}))],
    quote:[V('v1', 'Navy mark quote', {}), V('v2', 'Quote + block photo', {img:'photo'})]
  }
});
})();

</script>
<?php
}, 1086);
