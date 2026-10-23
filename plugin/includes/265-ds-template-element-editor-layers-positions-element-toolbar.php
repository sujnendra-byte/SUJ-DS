<?php
/**
 * Code Snippets export — DS Template Element Editor - layers, positions, element toolbar (Glassline)
 *
 * Snippet ID : 1078
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-16 (V41.1: Crop for pictures inside shapes)
 * Load order : 256 of 257
 */
/**
 * DS Template Element Editor - layers, positions, element toolbar (Glassline)
 * Run: Everywhere
 *
 * AI Presentation editor, design-template decks. Every element of a slide - each text, box, circle,
 * line, icon, picture, chart - can be clicked and edited on its own:
 *  - a bar above the slide changes with what is selected (text: font, weight, size, colour, bold,
 *    italic, underline, strike, capitals, alignment, spacing, transparency; shapes: fill, outline,
 *    corners, transparency; icons, pictures, charts and the logo get their own buttons);
 *  - the right half of the bar never changes: Positions, Layers, Add element, Edit slide, Logo,
 *    Master slide, Add slide, Undo, Redo, Reset;
 *  - Positions: forward / backward / front / back, align and distribute, width, height, X, Y, rotate;
 *    one element or one group lines up with the slide; several line up with each other or with the slide
 *    ("Selected elements" / "Slide"); with several selected the bar has an Align button with the same choices;
 *  - Layers: every element of the slide in a list - select, lock / unlock, hide / show, drag to reorder
 *    (a locked element cannot be selected, moved or edited on the slide until it is unlocked);
 *  - Add element: text box, rectangle, rounded box, circle, line, triangle, diamond, hexagon,
 *    pentagon, arrow, icon;
 *  - Fill / Outline / Text colour panel: None, Solid or Gradient; a gradient has presets, linear or radial type,
 *    direction and angle, and up to 6 colour stops (drag, add, remove; colour, position, transparency for each);
 *    solid colours and outlines have their own transparency; outline width and dash type;
 *  - fill and outline (boxes, circles, shapes; text colour too), outline width,
 *    an outline around letters and around pictures, and a shadow (colour, opacity, blur, distance, angle, size)
 *    for boxes, shapes, texts, pictures, icons and lines - drawn in the editor, the picture / PDF downloads and
 *    (with the matching change in the engine) the PowerPoint download;
 *  - "apply this look" (the brush beside Duplicate): to the similar elements of this slide, or of every slide;
 *  - right click on the slide: a menu with copy, paste, cut, duplicate, delete, order, group, align, lock and the
 *    panels; Ctrl+C / Ctrl+X / Ctrl+V copy and paste elements, also to another slide;
 *  - the small Colour / A- / A+ bar of "Deck style" is hidden while this editor is on (the bar above has it all);
 *  - an element moved off the slide stays visible and selectable beside it (it is still left out of the downloads);
 *  - "Copy effects" (in the brush menu and the right-click menu), then Ctrl + V on another element: its look is pasted;
 *    Ctrl + C copies the element itself;
 *  - Space + drag moves the whole canvas (Home puts it back); a Shortcuts button in the corner of the canvas (or the ? key)
 *    lists every keyboard shortcut;
 *  - Reset: a warning first, then the slide (or every slide) goes back to how it was when the deck was opened this
 *    time - a new deck to how it was generated, a saved and re-opened deck to that saved version;
 *  - Ctrl + S saves the deck;
 *  - the deck name in the top bar can be renamed (click it, or the pencil beside it);
 *  - a group is its own row in Layers with its elements under it (fold, rename, lock, hide, drag as one);
 *    the small picture of a layer is the real shape;
 *  - with the Layers list open, the up / down arrow keys move the selected layer forward / backward
 *    (Shift = to the front / back); Ctrl+] and Ctrl+[ do the same at any time;
 *  - a picture is not a button: a click selects it; the small "Change image" button that shows while the mouse
 *    is over it (or "Change image" in the bar) opens the picker;
 *  - Duplicate on a picture frame makes a second frame with a picture of its own ("Change image" on it);
 *    it resizes freely from every side like the first one - the picture is cut again to fill it, not stretched -
 *    and has the same tools: Change image, Crop (zoom and position), Round corners (a slider), Transparency;
 *  - PNG pictures ("+ Add PNG", or Add element > PNG picture) are elements too: select, move, resize, lock,
 *    group, duplicate, remove, and a row in Layers (they always stay in front);
 *  - several elements at once: Shift + click, Ctrl + A, or drag a box around them (everything that lies
 *    completely inside it); Group / Ungroup (Ctrl+G, Ctrl+Shift+G); a selection or group moves, resizes
 *    and turns as one;
 *  - the bar sits in the middle above the slide, and the EDIT SLIDE / LOGO buttons beside the slide are
 *    hidden while it is shown (the bar has both).
 * The changes are kept on the slide as "dsel" and are used by the editor, the thumbnails, the
 * picture / PDF downloads and the PowerPoint download (the page elements are changed before they
 * are sent to the builder). Needs the small matching change in "DS Template Engine (T00)":
 * data-dk on every drawn element, rotation / underline / own font for text, rotation and
 * transparency for pictures and icons.
 *
 * Templates it is switched on for: the list in ds_el_templates() (filter: ds_el_editor_templates).
 * NEW SNIPPET. Undo: deactivate this snippet - decks look as generated again, nothing is lost.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_el_templates') ) {
function ds_el_templates() {
    $all  = ! empty($GLOBALS['ds_tpl_registry']) ? array_keys($GLOBALS['ds_tpl_registry']) : array('glassline'); /* every design template (all except the classic look, which is not a design template) */
    $list = apply_filters('ds_el_editor_templates', array_diff($all, array('classic')));
    return array_values(array_filter(array_map('sanitize_key', (array) $list)));
}
function ds_el_should_print() {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return false;
    return (bool) apply_filters('ds_el_editor_on', true);
}
}

/* step 1: before the frosted-glass step (priority 1103) */
add_action('wp_footer', function () {
    if ( ! ds_el_should_print() ) return;
    ?>
<script id="ds-el-core">
/* DS Template Element Editor - step 1 of 2 (runs before the frosted-glass step, so a moved glass box is blurred where it now is):
   gives every element a key, adds the user's own elements, hides, moves, resizes, rotates, restyles and reorders them. */
(function(){
'use strict';
var D = window.DSTE; if (!D || !D.renderPage || window.DSEL) return;
var X = window.DSEL = {ALLOW: <?php echo wp_json_encode( (object) array_fill_keys( ds_el_templates(), 1 ) ); ?>, pages: {}, fonts: {}};
function clone(e){ var c = {}, k; for (k in e) c[k] = e[k]; return c; }
X.clone = clone;
/* shape outline with round corners and any number of sides: a path in the shape's own box (0..w, 0..h) */
X.POLY = {chevron: [[0, 0], [82, 0], [100, 50], [82, 100], [0, 100], [18, 50]], homePlate: [[0, 0], [82, 0], [100, 50], [82, 100], [0, 100]], triangle: [[50, 0], [100, 100], [0, 100]], rtTriangle: [[0, 0], [100, 100], [0, 100]], diamond: [[50, 0], [100, 50], [50, 100], [0, 50]], hexagon: [[25, 0], [75, 0], [100, 50], [75, 100], [25, 100], [0, 50]], parallelogram: [[25, 0], [100, 0], [75, 100], [0, 100]], pentagon: [[50, 0], [100, 38], [82, 100], [18, 100], [0, 38]]};
X.SIDES0 = {triangle: 3, diamond: 4, pentagon: 5, hexagon: 6};
X.sidesOf = function(e){ return e && e.t === 'shape' ? (e.sides || X.SIDES0[e.prst] || 0) : 0; };
X.polyPts = function(e){
  var w = Math.max(1, +e.w || 1), h = Math.max(1, +e.h || 1), n = e.sides ? Math.max(3, Math.min(12, Math.round(e.sides))) : 0;
  if (n) {
    var a0 = n % 2 ? -90 : -90 + 180 / n, raw = [], i, x0 = 1e9, y0 = 1e9, x1 = -1e9, y1 = -1e9;
    for (i = 0; i < n; i++) { var a = (a0 + i * 360 / n) * Math.PI / 180, px = Math.cos(a), py = Math.sin(a); raw.push([px, py]); x0 = Math.min(x0, px); x1 = Math.max(x1, px); y0 = Math.min(y0, py); y1 = Math.max(y1, py); }
    return raw.map(function(p){ return [(p[0] - x0) / (x1 - x0 || 1) * w, (p[1] - y0) / (y1 - y0 || 1) * h]; });
  }
  var P = X.POLY[e.prst]; if (!P) return null;
  return P.map(function(p){ return [p[0] / 100 * w, p[1] / 100 * h]; });
};
X.mkPd = function(e){
  if (!e || e.t !== 'shape') return;
  var r = +e.r || 0;
  if (!(r > 0) && !e.sides) { delete e.pd; return; }
  var P = X.polyPts(e); if (!P || P.length < 3) { delete e.pd; return; }
  var n = P.length, d = [], R = function(v){ return Math.round(v * 100) / 100; };
  if (!(r > 0)) { P.forEach(function(p, i){ d.push([i ? 'L' : 'M', R(p[0]), R(p[1])]); }); d.push(['Z']); e.pd = d; return; }
  var cuts = P.map(function(v, i){
    var A = P[(i - 1 + n) % n], B = P[(i + 1) % n];
    var ax = A[0] - v[0], ay = A[1] - v[1], bx = B[0] - v[0], by = B[1] - v[1], la = Math.sqrt(ax * ax + ay * ay) || 1, lb = Math.sqrt(bx * bx + by * by) || 1;
    var ux = ax / la, uy = ay / la, wx = bx / lb, wy = by / lb;
    var th = Math.acos(Math.max(-1, Math.min(1, ux * wx + uy * wy))), tn = Math.tan(th / 2) || 1e-6;
    var t = Math.min(r / tn, la / 2, lb / 2), rr = t * tn, k = 4 / 3 * Math.tan((Math.PI - th) / 4) * rr;
    var a = [v[0] + ux * t, v[1] + uy * t], b = [v[0] + wx * t, v[1] + wy * t];
    return {a: a, b: b, ca: [a[0] - ux * k, a[1] - uy * k], cb: [b[0] - wx * k, b[1] - wy * k]};
  });
  d.push(['M', R(cuts[0].b[0]), R(cuts[0].b[1])]);
  for (var i = 1; i <= n; i++) { var k = cuts[i % n]; d.push(['L', R(k.a[0]), R(k.a[1])]); d.push(['C', R(k.ca[0]), R(k.ca[1]), R(k.cb[0]), R(k.cb[1]), R(k.b[0]), R(k.b[1])]); }
  d.push(['Z']);
  e.pd = d;
};

/* the edits of one slide for one layout: {m:{key:{...}}, z:[keys bottom to top], add:[{k,e}], n:counter} */
X.layer = function(slide, vid, make){
  if (!slide) return null;
  var d = slide.dsel;
  if (!d || typeof d !== 'object') { if (!make) return null; d = slide.dsel = {}; }
  if (!d.L || typeof d.L !== 'object') { if (!make) return null; d.L = {}; }
  var L = d.L[vid];
  if (!L || typeof L !== 'object') { if (!make) return null; L = d.L[vid] = {}; }
  if (!L.m || typeof L.m !== 'object') L.m = {};
  if (!Array.isArray(L.z)) L.z = [];
  if (!Array.isArray(L.add)) L.add = [];
  return L;
};
X.bbox = function(e){
  if (e.t === 'line') return {x: Math.min(e.x1, e.x2), y: Math.min(e.y1, e.y2), w: Math.abs(e.x2 - e.x1), h: Math.abs(e.y2 - e.y1)};
  if (e.t === 'path') {
    var xs = [], ys = [];
    (e.d || []).forEach(function(c){ for (var i = 1; i + 1 < c.length; i += 2) { xs.push(+c[i]); ys.push(+c[i + 1]); } });
    if (!xs.length) return {x: 0, y: 0, w: 0, h: 0};
    var a = Math.min.apply(null, xs), b = Math.min.apply(null, ys);
    return {x: a, y: b, w: Math.max.apply(null, xs) - a, h: Math.max.apply(null, ys) - b};
  }
  return {x: +e.x || 0, y: +e.y || 0, w: +e.w || 0, h: +e.h || 0};
};
/* move / resize / rotate. o: {dx,dy (px from where the layout put it), sx,sy (times its own size), rot (degrees)} */
X.geom = function(e, o){
  var b = e._b || (e._b = X.bbox(e));
  o = o || {};
  var dx = +o.dx || 0, dy = +o.dy || 0, sx = o.sx > 0 ? +o.sx : 1, sy = o.sy > 0 ? +o.sy : 1, rot = +o.rot || 0;
  if (e.t === 'ring') { var s1 = sx !== 1 ? sx : sy; sx = sy = s1; }
  var f = {x: b.x + dx, y: b.y + dy, w: b.w * sx, h: b.h * sy, rot: rot};
  if (e.t === 'line' || e.t === 'path') {
    if (dx || dy || sx !== 1 || sy !== 1 || rot) {
      var cx = f.x + f.w / 2, cy = f.y + f.h / 2, r = rot * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r);
      var P = function(x, y){
        var px = b.x + (x - b.x) * sx + dx, py = b.y + (y - b.y) * sy + dy;
        if (rot) { var ux = px - cx, uy = py - cy; px = cx + ux * cs - uy * sn; py = cy + ux * sn + uy * cs; }
        return [Math.round(px * 100) / 100, Math.round(py * 100) / 100];
      };
      if (e.t === 'line') { var p1 = P(e.x1, e.y1), p2 = P(e.x2, e.y2); e.x1 = p1[0]; e.y1 = p1[1]; e.x2 = p2[0]; e.y2 = p2[1]; }
      else e.d = (e.d || []).map(function(c){ var n = [c[0]]; for (var i = 1; i + 1 < c.length; i += 2) { var q = P(+c[i], +c[i + 1]); n.push(q[0], q[1]); } return n; });
    }
  } else {
    e.x = f.x; e.y = f.y; e.w = f.w; e.h = f.h;
    if (o.rot != null) e.rot = rot; else f.rot = +e.rot || 0;
  }
  e._f = f;
};
function hex(c){ c = String(c || '').replace('#', '').trim(); if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2]; return /^[0-9a-fA-F]{6}$/.test(c) ? '#' + c.toUpperCase() : ''; }
X.hex = hex;
function num(v, a, b){ v = +v; if (!isFinite(v)) return null; return Math.max(a, Math.min(b, v)); }
/* colours, sizes, fonts, text */
/* a two-colour (or more) fill, checked: only numbers and hex colours get through */
function cleanGrad(g){
  if (!g || !Array.isArray(g.stops)) return null;
  var st = [];
  g.stops.slice(0, 6).forEach(function(s){ if (!Array.isArray(s)) return; var c = hex(s[1]), p = num(s[0], 0, 100); if (!c || p == null) return; var a = s[2] != null ? num(s[2], 0, 1) : null; st.push(a != null && a < 1 ? [p, c, a] : [p, c]); });
  if (st.length < 2) return null;
  var out = {stops: st, ang: num(g.ang != null ? g.ang : 135, -360, 720)}; if (out.ang == null) out.ang = 135;
  if (g.type === 'radial') out.type = 'radial';
  return out;
}
function cleanSh(s){
  function n(k, d, a, b){ var v = num(s[k], a, b); return v == null ? d : v; }
  return {c: hex(s.c) || '#000000', o: n('o', 0.35, 0, 1), b: n('b', 12, 0, 100), d: n('d', 6, 0, 100), a: n('a', 45, 0, 360), s: n('s', 0, 0, 60)};
}
X.cleanGrad = cleanGrad; X.cleanSh = cleanSh;
/* how the element was generated: used for "apply to similar" and for size changes that are carried to other texts */
X.orig = function(e){
  if (e._o) return;
  e._o = {t: e.t, fill: hex(e.fill) || '', grad: e.grad ? 1 : 0, line: hex(e.line) || '', color: hex(e.color) || '', stroke: hex(e.stroke) || '', r: e.r ? 1 : 0, prst: e.prst || '', size: +e.size || 0, wt: +e.wt || 0, font: e.font || '', frost: e.frost != null ? 1 : 0};
};
function cqs(v){ return (v / 12.8).toFixed(3) + 'cqw'; }
function rgbaS(c, o){ c = hex(c) || '#000000'; return 'rgba(' + parseInt(c.substr(1, 2), 16) + ',' + parseInt(c.substr(3, 2), 16) + ',' + parseInt(c.substr(5, 2), 16) + ',' + (o == null ? 1 : o) + ')'; }
function gcss(g, op){ return 'linear-gradient(' + (g.ang != null ? g.ang : 135) + 'deg,' + g.stops.map(function(s){ return rgbaS(s[1], (s[2] != null ? s[2] : 1) * (op != null ? op : 1)) + ' ' + s[0] + '%'; }).join(',') + ')'; }
/* the CSS for what the engine does not draw itself: shadow, gradient outline, text outline, gradient text, picture outline */
X.fx = function(e){
  if (!e || !e.t) return;
  delete e._css; delete e._cssIn; delete e._wrap;
  var css = '', inn = '', sh = e.sh && typeof e.sh === 'object' ? e.sh : null, shv = '';
  if (sh) { var r = (+sh.a || 0) * Math.PI / 180; shv = cqs((+sh.d || 0) * Math.cos(r)) + ' ' + cqs((+sh.d || 0) * Math.sin(r)) + ' ' + cqs(+sh.b || 0); }
  if (e.t === 'rect' || e.t === 'ell' || e.t === 'shape') {
    var lg = e.lgrad && e.lgrad.stops ? e.lgrad : null;
    if (lg && !e.pd) {
      var w = cqs(e.lw || 2), fb = e.grad && e.grad.stops ? (e.grad.type === 'radial' ? 'radial-gradient(closest-side,' + e.grad.stops.map(function(s){ return rgbaS(s[1], (s[2] != null ? s[2] : 1) * (e.op != null ? e.op : 1)) + ' ' + s[0] + '%'; }).join(',') + ')' : gcss(e.grad, e.op)) : (e.fill ? 'linear-gradient(' + rgbaS(e.fill, e.op != null ? e.op : 1) + ',' + rgbaS(e.fill, e.op != null ? e.op : 1) + ')' : '');
      if (!fb && e.pimg) fb = 'linear-gradient(transparent,transparent)';
      if (fb) css += 'border:' + w + ' solid transparent;box-sizing:border-box;background:' + fb + ' padding-box,' + gcss(lg) + ' border-box;';
      else if (e.t === 'rect' && !e.r) css += 'border:' + w + ' solid;border-image:' + gcss(lg) + ' 1;box-sizing:border-box;';
      /* no fill, round corners or a circle: the gradient is painted over the whole box and everything but the border is cut away */
      else if (e.t !== 'shape') css += 'border:' + w + ' solid transparent;box-sizing:border-box;background:' + gcss(lg) + ' border-box;-webkit-mask:linear-gradient(#000 0 0) padding-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;';
      else css += 'border:' + w + ' solid ' + rgbaS(lg.stops[0][1], 1) + ';box-sizing:border-box;';
    }
    if (sh) { if (e.t === 'shape') e._wrap = 'filter:drop-shadow(' + shv + ' ' + rgbaS(sh.c, sh.o) + ');'; else css += 'box-shadow:' + shv + ' ' + cqs(+sh.s || 0) + ' ' + rgbaS(sh.c, sh.o) + ';'; }
  } else if (e.t === 'text') {
    var tg = e.tgrad && e.tgrad.stops ? e.tgrad : null;
    if (tg) inn += 'background-image:' + gcss(tg, e.op) + ';-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;';
    /* a gradient outline: the gradient is painted behind the letters and shows where the (clear) outline is; the letters
       themselves keep their colour. With gradient letters as well, the outline is drawn in its first colour. */
    var tlg = e.tline && e.tlgrad && e.tlgrad.stops ? e.tlgrad : null;
    if (tlg && !tg) { inn += 'background-image:' + gcss(tlg) + ';-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:' + rgbaS(e.color, e.op != null ? e.op : 1) + ';-webkit-text-stroke:' + cqs(e.tlw || 1) + ' transparent;'; tg = tlg; }
    else if (e.tline) inn += '-webkit-text-stroke:' + cqs(e.tlw || 1) + ' ' + rgbaS(e.tline, 1) + ';paint-order:stroke fill;';
    if (sh) inn += tg ? 'filter:drop-shadow(' + shv + ' ' + rgbaS(sh.c, sh.o) + ');' : 'text-shadow:' + shv + ' ' + rgbaS(sh.c, sh.o) + ';';
  } else if (e.t === 'img') {
    if (e.line && e.lgrad && e.lgrad.stops) css += 'border:' + cqs(e.lw || 3) + ' solid transparent;box-sizing:border-box;background:' + gcss(e.lgrad) + ' border-box;';
    else if (e.line) css += 'border:' + cqs(e.lw || 3) + ' solid ' + rgbaS(e.line, 1) + ';box-sizing:border-box;';
    if (sh) css += 'box-shadow:' + shv + ' ' + cqs(+sh.s || 0) + ' ' + rgbaS(sh.c, sh.o) + ';';
  } else if (e.t === 'line' || e.t === 'path' || e.t === 'icon' || e.t === 'logo') {
    if (sh) css += 'filter:drop-shadow(' + shv + ' ' + rgbaS(sh.c, sh.o) + ');';
  }
  if (css) e._css = css; if (inn) e._cssIn = inn;
};
X.style = function(e, o, T){
  if (!o) return;
  var v;
  if (o.sh && typeof o.sh === 'object') { if (o.sh.off) { delete e.sh; delete e.shadow; } else { e.sh = cleanSh(o.sh); delete e.shadow; } }
  if (e.t === 'text') {
    if (o.text != null && e.ed && e.ed.f === 'x-dsel') e.text = String(o.text).slice(0, 2000);
    if ((v = num(o.size, 6, 400)) != null) e.size = v;
    if ((v = num(o.wt, 100, 900)) != null) e.wt = v;
    if ((v = num(o.lh, 0.6, 3)) != null) e.lh = v;
    if ((v = num(o.ls, -0.2, 1)) != null) e.ls = v;
    if (o.italic != null) e.italic = !!o.italic;
    if (o.caps != null) e.caps = !!o.caps;
    if (o.align === 'l' || o.align === 'c' || o.align === 'r') e.align = o.align;
    if (o.va === 't' || o.va === 'm' || o.va === 'b') e.va = o.va;
    if (o.dec != null) e.dec = String(o.dec).replace(/[^us]/g, '');
    if (o.ff && /^[A-Za-z0-9 ]{2,40}$/.test(o.ff)) { e.ff = o.ff; e.fam = o.ff; X.fonts[o.ff] = X.fonts[o.ff] || 1; }
    if (hex(o.color)) e.color = hex(o.color);
    if ((v = num(o.op, 0, 1)) != null) e.op = v;
    if (o.tgrad && (v = cleanGrad(o.tgrad))) e.tgrad = v;
    if (o.tline !== undefined) { if (hex(o.tline)) e.tline = hex(o.tline); else delete e.tline; }
    if (o.tlgrad && e.tline && (v = cleanGrad(o.tlgrad))) e.tlgrad = v;
    if ((v = num(o.tlw, 0.2, 30)) != null) e.tlw = v;
    return;
  }
  if (e.t === 'rect' || e.t === 'ell' || e.t === 'shape') {
    /* picture fill: a picture (PNG / JPEG) inside the box, circle or shape */
    if (o.pimg !== undefined) { if (!o.pimg || o.pimg.off) delete e.pimg; else if (typeof o.pimg.src === 'string' && /^data:image\/(png|jpeg);base64,/.test(o.pimg.src)) { var pi = o.pimg, nn = function(q, d, a, b){ var r0 = num(q, a, b); return r0 == null ? d : r0; }; e.pimg = {src: pi.src, iw: nn(pi.iw, 0, 0, 20000), ih: nn(pi.ih, 0, 0, 20000), op: nn(pi.op, 1, 0, 1), z: nn(pi.z, 1, 1, 4), px: nn(pi.px, 50, 0, 100), py: nn(pi.py, 50, 0, 100), st: pi.st ? 1 : 0}; if (pi.gb && num(pi.gb.w, 1, 50000) != null && num(pi.gb.h, 1, 50000) != null) e.pimg.gb = {x: nn(pi.gb.x, 0, -50000, 50000), y: nn(pi.gb.y, 0, -50000, 50000), w: num(pi.gb.w, 1, 50000), h: num(pi.gb.h, 1, 50000)}; } }
    if (o.fill !== undefined) { if (o.fill === '') { delete e.fill; delete e.grad; } else if (hex(o.fill)) { e.fill = hex(o.fill); delete e.grad; } }
    if (o.grad && (v = cleanGrad(o.grad))) { e.grad = v; }
    if (o.lgrad && (v = cleanGrad(o.lgrad))) { e.lgrad = v; if (!e.line) e.line = v.stops[0][1]; if (!e.lw) e.lw = 2; }
    if (o.line !== undefined) { if (o.line === '') { delete e.line; delete e.lgrad; } else if (hex(o.line)) { e.line = hex(o.line); if (!e.lw) e.lw = 2; if (o.op == null && e.lop == null) e.lop = 1; } }
    if ((v = num(o.lw, 0.5, 60)) != null) e.lw = v;
    if ((v = num(o.r, 0, 400)) != null && (e.t === 'rect' || e.t === 'shape')) e.r = v;
    if ((v = num(o.sides, 3, 12)) != null && e.t === 'shape') e.sides = Math.round(v);
    if (e.t === 'shape') X.mkPd(e);
    if ((v = num(o.op, 0, 1)) != null) { e.op = v; if (e.line) e.lop = v; }
    if ((v = num(o.lop, 0, 1)) != null && e.line) e.lop = v;
    if (o.dash != null) e.dash = !!o.dash;
    return;
  }
  if (e.t === 'line') {
    if (hex(o.color)) e.color = hex(o.color);
    if ((v = num(o.lw, 0.5, 60)) != null) e.lw = v;
    if ((v = num(o.op, 0, 1)) != null) e.op = v;
    if (o.dash != null) e.dash = !!o.dash;
    return;
  }
  if (e.t === 'path') {
    if (hex(o.color)) { if (e.stroke) e.stroke = hex(o.color); if (e.fill) e.fill = hex(o.color); if (!e.stroke && !e.fill) e.stroke = hex(o.color); }
    if ((v = num(o.lw, 0.5, 60)) != null && e.stroke) e.lw = v;
    if ((v = num(o.op, 0, 1)) != null) { if (e.fill) e.op = v; if (e.stroke) e.lop = v; }
    return;
  }
  if (e.t === 'icon') {
    if (hex(o.color)) e.color = hex(o.color);
    if (o.name && /^[a-z0-9-]+:[a-z0-9-]+$/.test(o.name)) e.name = o.name;
    if (o.tgrad && (v = cleanGrad(o.tgrad))) e.tgrad = v;
    if (o.tline !== undefined) { if (hex(o.tline)) e.tline = hex(o.tline); else { delete e.tline; delete e.tlgrad; } }
    if (o.tlgrad && e.tline && (v = cleanGrad(o.tlgrad))) e.tlgrad = v;
    if ((v = num(o.tlw, 0.2, 30)) != null) e.tlw = v;
    if ((v = num(o.op, 0, 1)) != null) e.op = v;
    return;
  }
  if (e.t === 'ring') {
    if (hex(o.color)) e.color = hex(o.color);
    if (hex(o.track)) e.track = hex(o.track);
    if ((v = num(o.pct, 0, 100)) != null) e.pct = v;
    return;
  }
  if (e.t === 'img') {
    if ((v = num(o.r, 0, 400)) != null && e.shape !== 'ell') e.r = v;
    if ((v = num(o.op, 0, 1)) != null) e.op = v;
    if (o.line !== undefined) { if (hex(o.line)) e.line = hex(o.line); else delete e.line; }
    if (o.lgrad && e.line && (v = cleanGrad(o.lgrad))) e.lgrad = v;
    if ((v = num(o.lw, 0.5, 60)) != null) e.lw = v;
    return;
  }
  if (e.t === 'logo') { if ((v = num(o.op, 0, 1)) != null) e.op = v; }
};
/* stacking order: groups [{k, els}] sorted by the saved order; anything not in it stays on top in its own order */
X.order = function(groups, z){
  if (!z || !z.length) return groups;
  var rank = {}; z.forEach(function(k, i){ rank[k] = i; });
  var any = false;
  var arr = groups.map(function(g, i){ var r = (g.k != null && rank.hasOwnProperty(g.k)) ? rank[g.k] : null; if (r != null) any = true; return {g: g, r: r, i: i}; });
  if (!any) return groups;
  /* a group without a rank that comes before the first ranked one (page furniture drawn first) stays at the bottom */
  var seen = false;
  arr.forEach(function(a){ if (a.r != null) seen = true; else a.r = seen ? 1e9 + a.i : -1e9 + a.i; });
  arr.sort(function(a, b){ return a.r - b.r || a.i - b.i; });
  return arr.map(function(a){ return a.g; });
};
X.prepText = function(e, T){
  if (e.t !== 'text') return;
  if (!e.ed) e.ed = {f: 'x-dsel', i: e._dk};
  if (!e.fam) e.fam = e.ff || (T && T.fonts && T.fonts[e.font === 'head' ? 'head' : 'body']) || 'Arial';
};
function keyFor(e, cnt, seen){
  var k;
  if (e.t === 'text' && e.ed && e.ed.f && e.ed.f !== 'x-dsel') k = 't.' + e.ed.f + (e.ed.i != null ? '.' + e.ed.i : '') + (e.ed.p != null ? '.' + e.ed.p : '');
  else { var tt = e._dsT || e.t; cnt[tt] = cnt[tt] || 0; k = tt + (cnt[tt]++); } /* a template photo cut into a shape keeps its photo key */
  k = String(k).replace(/[^A-Za-z0-9_.-]/g, '');
  while (seen[k]) k += '_';
  seen[k] = 1;
  return k;
}
X.keyFor = keyFor;
function stageA(T, slide, vid, p){
  var L = X.layer(slide, vid, false), cnt = {}, seen = {}, out = [], hid = [];
  p.els.forEach(function(e0){
    if (!e0 || !e0.t) return;
    var e = clone(e0); e._dk = keyFor(e, cnt, seen); if (!e0._dsGone) out.push(e); /* a cover the photo-shape step made unneeded: keyed (so later keys stay put), then left out */
  });
  if (L) L.add.forEach(function(a){
    if (!a || !a.k || !a.e || !a.e.t || seen[a.k]) return;
    var e = clone(a.e); if (e.d) e.d = e.d.map(function(c){ return c.slice(); });
    delete e.ed; delete e.fam;
    if (e.t === 'text' && T && T.fonts) { if (e.wt == null) e.wt = e.font === 'head' ? (T.fonts.headW || 700) : (T.fonts.bodyW || 400); }
    e._dk = String(a.k).replace(/[^A-Za-z0-9_.-]/g, ''); e._add = 1; seen[e._dk] = 1; out.push(e);
  });
  out = out.filter(function(e){
    var o = L && L.m[e._dk];
    e._b = X.bbox(e);
    X.prepText(e, T); X.orig(e);
    if (o && o.hide) { hid.push(e); return false; }
    X.geom(e, o);
    if (o) X.style(e, o, T);
    if (e.rot && e.t === 'rect' && e.frost == null) e.frost = 0;      /* a turned glass box keeps its tint, without the blurred backdrop */
    return true;
  });
  if (L && L.z.length) { var gs = X.order(out.map(function(e){ return {k: e._dk, els: [e]}; }), L.z); out = gs.map(function(g){ return g.els[0]; }); }
  out.forEach(X.fx);
  p.els = out; p._dsHid = hid; p._dsA = 1;
}
var rpA = D.renderPage;
D.renderPage = function(T, slide, vid){
  var p = rpA.apply(this, arguments);
  try { if (p && p.els && T && X.ALLOW[T.id] && slide && !p._dsA) stageA(T, slide, vid || 'v1', p); }
  catch (er) { if (window.console) console.warn('[DSEL] step 1', er); }
  return p;
};
})();
</script>
    <?php
}, 1102);

/* step 2 and the tools: after every other template step */
add_action('wp_footer', function () {
    if ( ! ds_el_should_print() ) return;
    ?>
<style id="ds-el-css">
#dsp-editor-fullscreen.dsel-on #dsp-ppt-slide-outer{position:relative}
#dsp-editor-fullscreen.dsel-on #dsp-ppt-main-slide .dsp-vp-ihover{display:none!important}
#dsel-ov{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:60}
#dsel-ov .dsel-picload{position:absolute;display:none;flex-direction:column;align-items:center;justify-content:center;gap:10px;background:rgba(10,14,22,.55);z-index:5;pointer-events:none;color:#E8EEF7;font:600 13px/1.3 Inter,Arial,sans-serif;text-shadow:0 1px 3px rgba(0,0,0,.6)}
#dsel-ov .dsel-picload i{width:34px;height:34px;box-sizing:border-box;border:3px solid rgba(255,255,255,.25);border-top-color:#4A9EFF;border-radius:50%;animation:dselPicSpin .8s linear infinite}
@keyframes dselPicSpin{to{transform:rotate(360deg)}}
html.dsx-ptmode #dsel-ov .dsel-fr,html.dsx-drawing #dsel-ov .dsel-fr{display:none!important}
#dsel-ov .dsx-draw{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:auto;cursor:crosshair;z-index:5;touch-action:none}
#dsel-ov .dsx-draw svg{position:absolute;left:0;top:0;width:100%;height:100%;overflow:visible;pointer-events:none}
#dsel-ov .dsx-bar,#dsel-ov .dsx-ptbar{position:absolute;left:50%;top:10px;transform:translateX(-50%);display:flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:center;max-width:94%;padding:7px 10px;border-radius:12px;background:#0F172A;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,.35);cursor:default;font:600 12px/1.2 Inter,Arial,sans-serif;pointer-events:auto;z-index:7}
#dsel-ov .dsx-bar button{border:1px solid rgba(255,255,255,.18);background:transparent;color:#fff;border-radius:8px;padding:6px 9px;font:600 12px/1 Inter,Arial,sans-serif;cursor:pointer}
#dsel-ov .dsx-bar button.on{background:#3B82F6;border-color:#3B82F6}
#dsel-ov .dsx-bar button.sw{width:20px;height:20px;padding:0;border-radius:50%;border:2px solid rgba(255,255,255,.35)}
#dsel-ov .dsx-bar button.sw.on{outline:2px solid #fff;outline-offset:2px}
#dsel-ov .dsx-bar button.lw{width:34px;height:26px;display:flex;align-items:center;justify-content:center;padding:0}
#dsel-ov .dsx-bar button.lw span{display:block;width:18px;background:#fff;border-radius:3px}
#dsel-ov .dsx-bar .sep{width:1px;height:20px;background:rgba(255,255,255,.2)}
#dsel-ov .dsx-bar button.done{background:#fff;color:#0F172A;border-color:#fff}
#dsel-ov .dsx-bar .tip{flex-basis:100%;font-weight:500;font-size:11px;opacity:.75;text-align:center}
#dsel-ov .dsx-pts{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:6}
#dsel-ov .dsx-pts b{position:absolute;width:10px;height:10px;margin:-5px 0 0 -5px;background:#fff;border:2px solid #3B82F6;box-sizing:border-box;border-radius:2px;pointer-events:auto;cursor:move;box-shadow:0 1px 3px rgba(0,0,0,.4)}
#dsel-ov .dsx-pts b.on{background:#3B82F6;border-color:#fff}
#dsel-ov .dsx-ptbar{padding:6px 8px 6px 12px;gap:10px}
#dsel-ov .dsx-ptbar button{border:0;background:#fff;color:#0F172A;border-radius:7px;padding:6px 10px;font:700 12px/1 Inter,Arial,sans-serif;cursor:pointer}
#dsel-ov .dsel-fr{position:absolute;box-sizing:border-box;outline:2px solid #6EA8FF;outline-offset:1px;pointer-events:none;min-width:2px;min-height:2px}
#dsel-ov .dsel-fr.dsel-edited{outline-color:#8B7CFF}
#dsel-ov .dsel-mv{position:absolute;pointer-events:auto;cursor:move}
#dsel-ov .dsel-h{position:absolute;width:var(--dsel-hs,11px);height:var(--dsel-hs,11px);margin:calc(var(--dsel-hs,11px) / -2) 0 0 calc(var(--dsel-hs,11px) / -2);background:#fff;border:1.5px solid #3B82F6;border-radius:2px;box-sizing:border-box;pointer-events:auto;box-shadow:0 1px 3px rgba(0,0,0,.35)}
#dsel-ov .dsel-h-nw{left:0;top:0;cursor:nwse-resize}#dsel-ov .dsel-h-n{left:50%;top:0;cursor:ns-resize}#dsel-ov .dsel-h-ne{left:100%;top:0;cursor:nesw-resize}
#dsel-ov .dsel-h-e{left:100%;top:50%;cursor:ew-resize}#dsel-ov .dsel-h-se{left:100%;top:100%;cursor:nwse-resize}#dsel-ov .dsel-h-s{left:50%;top:100%;cursor:ns-resize}
#dsel-ov .dsel-h-sw{left:0;top:100%;cursor:nesw-resize}#dsel-ov .dsel-h-w{left:0;top:50%;cursor:ew-resize}
#dsel-ov .dsel-wonly .dsel-h-nw,#dsel-ov .dsel-wonly .dsel-h-n,#dsel-ov .dsel-wonly .dsel-h-ne,#dsel-ov .dsel-wonly .dsel-h-se,#dsel-ov .dsel-wonly .dsel-h-s,#dsel-ov .dsel-wonly .dsel-h-sw{display:none}
#dsel-ov .dsel-wonly .dsel-h-e,#dsel-ov .dsel-wonly .dsel-h-w{height:18px;margin-top:-9px;border-radius:5px;width:8px;margin-left:-4px}
#dsel-ov .dsel-t-chart .dsel-h,#dsel-ov .dsel-t-logo .dsel-h{display:none}
#dsel-ov .dsel-rot{position:absolute;left:50%;top:100%;margin:16px 0 0 -12px;width:24px;height:24px;border-radius:50%;background:#fff;color:#1E293B;border:1.5px solid #3B82F6;box-sizing:border-box;font:700 14px/21px Arial,sans-serif;text-align:center;pointer-events:auto;cursor:grab;box-shadow:0 1px 4px rgba(0,0,0,.35);-webkit-user-select:none;user-select:none}
#dsel-ov .dsel-norot .dsel-rot{display:none}
#dsel-ov .dsel-tiny .dsel-h{border-width:1px;border-radius:1.5px}
#dsel-ov .dsel-tiny .dsel-h-n,#dsel-ov .dsel-tiny .dsel-h-e,#dsel-ov .dsel-tiny .dsel-h-s,#dsel-ov .dsel-tiny .dsel-h-w{display:none}
#dsel-ov .dsel-tiny .dsel-h-nw{margin:calc(var(--dsel-hs) * -1) 0 0 calc(var(--dsel-hs) * -1)}#dsel-ov .dsel-tiny .dsel-h-ne{margin:calc(var(--dsel-hs) * -1) 0 0 0}#dsel-ov .dsel-tiny .dsel-h-se{margin:0}#dsel-ov .dsel-tiny .dsel-h-sw{margin:0 0 0 calc(var(--dsel-hs) * -1)}
#dsel-ov .dsel-tiny .dsel-rot{width:20px;height:20px;margin:10px 0 0 -10px;font-size:12px;line-height:17px}
#dsel-ov .dsel-sd{display:none;position:absolute;left:100%;top:22%;width:var(--dsel-hs,12px);height:var(--dsel-hs,12px);margin:calc(var(--dsel-hs,12px) / -2) 0 0 calc(var(--dsel-hs,12px) / -2);background:#fff;border:1.5px solid #3B82F6;transform:rotate(45deg);box-sizing:border-box;pointer-events:auto;cursor:ns-resize;box-shadow:0 1px 3px rgba(0,0,0,.35)}
#dsel-ov .dsel-hassd .dsel-sd{display:block}
#dsel-ov .dsel-rc{display:none;position:absolute;width:var(--dsel-hs,12px);height:var(--dsel-hs,12px);margin:calc(var(--dsel-hs,12px) / -2) 0 0 calc(var(--dsel-hs,12px) / -2);border-radius:50%;background:#fff;border:1.5px solid #3B82F6;box-sizing:border-box;pointer-events:auto;cursor:move;box-shadow:0 1px 3px rgba(0,0,0,.35)}
#dsel-ov .dsel-rc::after{content:'';position:absolute;left:50%;top:50%;width:36%;height:36%;transform:translate(-50%,-50%);border-radius:50%;background:#3B82F6}
#dsel-ov .dsel-hasrc .dsel-rc{display:block}
html.dsel-dragging #dsel-ov .dsel-rc{display:none}
#dsel-ov .dsel-sdt{display:none;position:absolute;left:100%;top:22%;margin:-11px 0 0 14px;padding:2px 8px;border-radius:5px;background:#E5E7EB;color:#111827;font:600 11.5px/18px Arial,sans-serif;white-space:nowrap;pointer-events:none;box-shadow:0 1px 4px rgba(0,0,0,.3)}
#dsel-ov .dsel-sdt.dsel-on{display:block}
#dsel-ov .dsel-mini{position:absolute;left:0;bottom:100%;margin-bottom:9px;display:flex;gap:2px;padding:3px;border-radius:9px;background:rgba(15,23,42,.94);box-shadow:0 2px 10px rgba(0,0,0,.4);pointer-events:auto;white-space:nowrap}
#dsel-ov .dsel-mini.dsel-mini-in{bottom:auto;top:100%;margin:9px 0 0 44px}
#dsel-ov .dsel-mini button{all:unset;box-sizing:border-box;width:28px;height:26px;border-radius:6px;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer}
#dsel-ov .dsel-mini button:hover{background:rgba(255,255,255,.16)}
#dsel-ov .dsel-mini button[data-h="mv"]{cursor:move}
#dsel-ov .dsel-fr:not(.dsel-edited) .dsel-mini button[data-m="rst"]{display:none}
#dsel-ov .dsel-mem i{position:absolute;box-sizing:border-box;outline:1px solid #6EA8FF;pointer-events:none}
#dsel-ov.dsel-busy .dsel-mem{display:none}
#dsel-ov .dsel-mq{position:absolute;display:none;box-sizing:border-box;border:1px solid #3B82F6;background:rgba(59,130,246,.14);pointer-events:none}
#dsel-ov .dsel-multi{outline-style:dashed}
html.dsel-live #dsce-bar{visibility:hidden!important;pointer-events:none!important}
#dsel-ov .dsel-g{position:absolute;display:none;background:#F43F5E;pointer-events:none}
#dsel-ov .dsel-gv{left:50%;top:0;width:1px;height:100%}#dsel-ov .dsel-gh{top:50%;left:0;height:1px;width:100%}
html.dsel-dragging,html.dsel-dragging *{-webkit-user-select:none!important;user-select:none!important}
#dsel-bar,:is(#dsel-bar,#dsel-dock) *,#dsel-dock,#dsel-dock *,#dsel-panel,#dsel-panel *{box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;text-transform:none;letter-spacing:normal}
#dsel-bar,#dsel-dock{position:fixed;z-index:70;display:none;flex-wrap:wrap;align-items:center;justify-content:center;gap:6px 8px;padding:8px 14px;background:#fff;color:#0F172A;border-radius:22px;box-shadow:0 6px 24px rgba(0,0,0,.35)}
:is(#dsel-bar,#dsel-dock) .dsel-left,:is(#dsel-bar,#dsel-dock) .dsel-right{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
:is(#dsel-bar,#dsel-dock) .dsel-left{min-height:32px}
:is(#dsel-bar,#dsel-dock) .dsel-sep{width:1px;align-self:stretch;min-height:28px;background:#CBD5E1;margin:0 4px}
:is(#dsel-bar,#dsel-dock) .dsel-hint{font-size:12.5px;line-height:1.35;color:#475569;max-width:330px}
:is(#dsel-bar,#dsel-dock) .dsel-kind{font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#64748B;margin-right:2px}
:is(#dsel-bar,#dsel-dock) .dsel-lbl{font-size:11.5px;color:#64748B;margin-left:2px}
:is(#dsel-bar,#dsel-dock) .dsel-b,#dsel-panel .dsel-pbt{all:unset;box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;gap:6px;height:32px;padding:0 11px;border:1px solid #94A3B8;border-radius:8px;background:#fff;color:#0F172A;font-size:13px;font-weight:500;line-height:1;cursor:pointer;white-space:nowrap}
:is(#dsel-bar,#dsel-dock) .dsel-b:hover,#dsel-panel .dsel-pbt:hover{background:#F1F5F9;border-color:#64748B}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-on,#dsel-panel .dsel-pbt.dsel-on{background:#DBEAFE;border-color:#3B82F6;color:#1D4ED8}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-ic{width:32px;padding:0;border-color:transparent;font-size:15px}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-ic:hover{border-color:#CBD5E1}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-wide{max-width:150px;overflow:hidden;justify-content:flex-start}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-grp{background:#EDE9FE;border-color:#8B5CF6;color:#5B21B6;font-weight:600}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-reset{border-color:#F87171;color:#B91C1C}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-reset:hover{background:#FEF2F2}
:is(#dsel-bar,#dsel-dock) .dsel-A{display:inline-block;font-weight:700;font-size:15px;line-height:17px;border-bottom:4px solid #000;padding:0 2px}
:is(#dsel-bar,#dsel-dock) .dsel-sw,#dsel-panel .dsel-sw{display:inline-block;width:16px;height:16px;border-radius:4px;border:1px solid #94A3B8;flex:none}
:is(#dsel-bar,#dsel-dock) .dsel-sw-none{background:linear-gradient(135deg,#fff 44%,#EF4444 45%,#EF4444 55%,#fff 56%)!important}
:is(#dsel-bar,#dsel-dock) .dsel-num{display:inline-flex;align-items:center;height:32px;border:1px solid #94A3B8;border-radius:16px;overflow:hidden;background:#fff}
:is(#dsel-bar,#dsel-dock) .dsel-num button{all:unset;box-sizing:border-box;width:26px;height:30px;text-align:center;font-size:15px;line-height:30px;color:#0F172A;cursor:pointer}
:is(#dsel-bar,#dsel-dock) .dsel-num button:hover{background:#F1F5F9}
:is(#dsel-bar,#dsel-dock) .dsel-num input{all:unset;box-sizing:border-box;width:40px;height:30px;text-align:center;font-size:13px;color:#0F172A;-moz-appearance:textfield;appearance:textfield}
:is(#dsel-bar,#dsel-dock) .dsel-num input::-webkit-outer-spin-button,:is(#dsel-bar,#dsel-dock) .dsel-num input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
#dsel-panel{position:fixed;z-index:71;width:248px;overflow:auto;background:#fff;color:#0F172A;border-radius:12px;box-shadow:0 10px 34px rgba(0,0,0,.42);font-size:12.5px;line-height:1.4}
#dsel-panel .dsel-ph{display:flex;align-items:center;justify-content:space-between;padding:11px 12px 6px 14px;position:sticky;top:0;background:#fff;z-index:1}
#dsel-panel .dsel-ph b{font-size:13px;font-weight:700}
#dsel-panel .dsel-ph button{all:unset;width:24px;height:24px;text-align:center;font-size:19px;line-height:22px;color:#475569;cursor:pointer;border-radius:6px}
#dsel-panel .dsel-ph button:hover{background:#F1F5F9}
#dsel-panel .dsel-pb{padding:4px 14px 14px}
#dsel-panel .dsel-tabs{display:flex;border-bottom:1px solid #E2E8F0;margin:0 14px 10px}
#dsel-panel .dsel-tabs button{all:unset;flex:1;text-align:center;padding:7px 0;font-size:12.5px;color:#64748B;cursor:pointer;border-bottom:2px solid transparent}
#dsel-panel .dsel-tabs button.dsel-on{color:#0F172A;font-weight:600;border-bottom-color:#7C3AED}
#dsel-panel .dsel-sub{font-size:11.5px;font-weight:700;color:#334155;margin:13px 0 7px}
#dsel-panel .dsel-note{font-size:11.5px;color:#64748B;margin:9px 0 8px}
#dsel-panel .dsel-grid2{display:grid;grid-template-columns:1fr 1fr;gap:7px}
#dsel-panel .dsel-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px 7px}
#dsel-panel .dsel-grid3 label{display:flex;flex-direction:column;gap:3px;font-size:10.5px;color:#64748B}
#dsel-panel input[type=number],#dsel-panel input[type=text]{all:unset;box-sizing:border-box;width:100%;height:30px;padding:0 8px;border:1px solid #CBD5E1;border-radius:7px;background:#fff;color:#0F172A;font-size:12.5px}
#dsel-panel input:disabled{background:#F1F5F9;color:#94A3B8}
#dsel-panel input:focus{border-color:#3B82F6}
#dsel-panel .dsel-pbt{width:100%;height:30px;font-size:12px;border-color:#CBD5E1;justify-content:center}
#dsel-panel .dsel-pbt.dsel-danger{border-color:#F87171;color:#B91C1C}
#dsel-panel .dsel-pbt.dsel-danger:hover{background:#FEF2F2}
#dsel-panel .dsel-row{display:flex;align-items:center;gap:7px;margin:6px 0;font-size:11.5px;color:#475569}
#dsel-panel .dsel-row .dsel-pbt{width:auto;padding:0 10px;flex:none}
#dsel-panel input[type=color]{all:unset;width:34px;height:30px;flex:none;border:1px solid #CBD5E1;border-radius:7px;cursor:pointer;background:#fff}
#dsel-panel input[type=color]::-webkit-color-swatch-wrapper{padding:3px}
#dsel-panel input[type=color]::-webkit-color-swatch{border:none;border-radius:4px}
#dsel-panel .dsel-chk{display:flex;align-items:center;gap:6px;cursor:pointer}
#dsel-panel .dsel-chk input{width:15px;height:15px;margin:0}
#dsel-panel .dsel-sws{display:flex;flex-wrap:wrap;gap:6px}
#dsel-panel .dsel-swb{all:unset;box-sizing:border-box;width:24px;height:24px;border-radius:50%;border:1px solid #94A3B8;cursor:pointer}
#dsel-panel .dsel-swb.dsel-on{box-shadow:0 0 0 2px #fff,0 0 0 4px #7C3AED}
#dsel-panel .dsel-sl{display:grid;grid-template-columns:1fr auto;gap:2px 8px;align-items:center;margin:8px 0;font-size:11.5px;color:#475569}
#dsel-panel .dsel-sl input{grid-column:1/3;grid-row:2;width:100%;margin:0;accent-color:#7C3AED}
#dsel-panel .dsel-sl em{font-style:normal;font-weight:600;color:#0F172A}
#dsel-panel .dsel-list{display:flex;flex-direction:column;gap:2px}
#dsel-panel .dsel-list button{all:unset;box-sizing:border-box;padding:7px 10px;border-radius:7px;font-size:14px;cursor:pointer;color:#0F172A}
#dsel-panel .dsel-list button:hover{background:#F1F5F9}
#dsel-panel .dsel-list button.dsel-on{background:#EDE9FE;color:#5B21B6}
#dsel-panel .dsel-icons{display:grid;grid-template-columns:repeat(6,1fr);gap:4px}
#dsel-panel .dsel-icons button{all:unset;box-sizing:border-box;aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:7px;cursor:pointer;border:1px solid transparent}
#dsel-panel .dsel-icons button:hover{background:#F1F5F9;border-color:#CBD5E1}
#dsel-panel .dsel-icons img{width:20px;height:20px;display:block}
#dsel-panel .dsel-add{all:unset;box-sizing:border-box;display:flex;align-items:center;gap:8px;height:34px;padding:0 9px;border:1px solid #CBD5E1;border-radius:8px;font-size:12px;cursor:pointer;color:#0F172A}
#dsel-panel .dsel-add:hover{background:#F1F5F9;border-color:#64748B}
#dsel-panel .dsel-add i{font-style:normal;width:18px;text-align:center;font-size:15px;color:#475569}
#dsel-panel .dsel-layers{display:flex;flex-direction:column;gap:2px}
#dsel-panel .dsel-lr{display:flex;align-items:center;gap:8px;padding:5px 6px 5px 8px;border-radius:7px;cursor:pointer;border:1px solid transparent;font-size:12px;color:#0F172A}
#dsel-panel .dsel-lr:hover{background:#F1F5F9}
#dsel-panel .dsel-lr.dsel-on{background:#EDE9FE;border-color:#C4B5FD}
#dsel-panel .dsel-lr.dsel-off{color:#94A3B8;cursor:default}
#dsel-panel .dsel-lr.dsel-dt{box-shadow:0 -2px 0 #7C3AED}#dsel-panel .dsel-lr.dsel-db{box-shadow:0 2px 0 #7C3AED}
#dsel-panel .dsel-lr span{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#dsel-panel .dsel-lr button{all:unset;flex:none;padding:1px 5px;border-radius:5px;font-size:12px;color:#64748B;cursor:pointer}
#dsel-panel .dsel-lr button:hover{background:#E2E8F0;color:#0F172A}
#dsel-panel .dsel-gb{flex:none;font-style:normal;font-size:9.5px;font-weight:700;color:#5B21B6;background:#EDE9FE;border-radius:4px;padding:1px 4px}
#dsel-panel .dsel-ld{flex:none;width:14px;height:14px;border-radius:3px;border:1px solid #94A3B8}
#dsel-panel .dsel-lg{flex:none;width:14px;text-align:center;font-style:normal;font-weight:700;font-size:12px}
@media (max-width:900px){#dsel-bar,#dsel-dock{border-radius:14px;padding:6px 8px;gap:4px}:is(#dsel-bar,#dsel-dock) .dsel-hint{display:none}:is(#dsel-bar,#dsel-dock) .dsel-sep{display:none}#dsel-panel{width:220px}}
#dsel-panel .dsel-lr .dsel-lkb{color:#CBD5E1}
#dsel-panel .dsel-lr:hover .dsel-lkb{color:#64748B}
#dsel-panel .dsel-lr .dsel-lkb.dsel-on{color:#7C3AED}
#dsel-panel .dsel-lr.dsel-lk span{color:#64748B}
#dsp-ppt-main-slide [data-dsel-lock],#dsp-ppt-main-slide [data-dsel-lock] *{pointer-events:none!important}
#dsel-panel .dsel-lt{flex:none;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-style:normal}
#dsel-panel .dsel-lt img{width:15px;height:15px;display:block}
#dsel-panel .dsel-lg{width:18px}
#dsel-panel .dsel-lr.dsel-in{margin-left:20px}
#dsel-panel .dsel-lr.dsel-gr{font-weight:600;padding-left:2px;gap:4px}
#dsel-panel .dsel-lr .dsel-chev{padding:1px 2px;color:#475569}
#dsel-panel .dsel-lr.dsel-shut .dsel-chev svg{transform:rotate(-90deg)}
#dsel-panel .dsel-lr .dsel-pen{color:#CBD5E1}
#dsel-panel .dsel-lr:hover .dsel-pen{color:#64748B}
#dsel-panel .dsel-lr .dsel-pen svg{width:13px;height:13px}
#dsel-panel .dsel-gin{flex:1;min-width:0;height:22px;box-sizing:border-box;border:1px solid #7C3AED;border-radius:5px;padding:0 6px;font:600 12px/1 inherit;font-family:inherit;color:#0F172A;background:#fff;outline:none}
html.dsel-live [style*="--ds-obj:"]{pointer-events:none!important;outline:none!important}
html.dsel-live [style*="--ds-obj:"]>span[data-dsobj]{display:none!important}
#dsel-ov .dsel-t-png .dsel-h-n,#dsel-ov .dsel-t-png .dsel-h-e,#dsel-ov .dsel-t-png .dsel-h-s,#dsel-ov .dsel-t-png .dsel-h-w{display:none}
#dsel-ov .dsel-imgbtn{all:unset;box-sizing:border-box;position:absolute;z-index:6;transform:translate(calc(-100% - 10px),10px);display:flex;align-items:center;gap:6px;height:30px;padding:0 11px;border-radius:8px;background:rgba(15,23,42,.9);color:#fff;font:600 12px/1 Inter,Arial,sans-serif;cursor:pointer;pointer-events:auto;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.25)}
#dsel-ov .dsel-imgbtn:hover{background:#2563EB}
#dsel-ov .dsel-imgbtn svg{width:14px;height:14px}
#dsel-ov .dsel-cropbtn{transform:translate(calc(-100% - 10px),46px)}
#dsel-ov .dsel-pfg{position:absolute;display:none;background-size:100% 100%;background-repeat:no-repeat;opacity:.35;pointer-events:none;outline:1px dashed rgba(59,130,246,.9);box-shadow:0 0 0 1px rgba(255,255,255,.6)}
html.dsel-pfc-drag,html.dsel-pfc-drag *{cursor:grabbing!important;user-select:none!important}
html.dsel-dragging #dsel-ov .dsel-imgbtn{display:none!important}
#dsp-editor-fullscreen.dsel-on #dsp-ppt-main-slide .dsp-vp-imgzone{cursor:default!important}
html.dsel-live [style*="--ds-obj:"]>img{object-fit:cover}
#dsel-warn{position:fixed;inset:0;z-index:100050;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Inter,Arial,sans-serif}
#dsel-warn .dsel-wbox{background:#fff;color:#0F172A;border-radius:14px;max-width:440px;width:100%;padding:22px 22px 18px;box-shadow:0 20px 60px rgba(0,0,0,.35);text-align:left}
#dsel-warn .dsel-wic{width:44px;height:44px;border-radius:50%;background:#FEF3C7;color:#B45309;display:flex;align-items:center;justify-content:center;margin-bottom:12px}
#dsel-warn .dsel-wic svg{width:24px;height:24px}
#dsel-warn h3{margin:0 0 8px;font:700 17px/1.3 Inter,Arial,sans-serif;color:#0F172A}
#dsel-warn p{margin:0 0 8px;font:400 13px/1.5 Inter,Arial,sans-serif;color:#334155}
#dsel-warn p.dsel-wsm{color:#64748B;font-size:12px}
#dsel-warn .dsel-wbt{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;margin-top:14px}
#dsel-warn .dsel-wbt button{all:unset;box-sizing:border-box;height:36px;padding:0 14px;border-radius:8px;border:1px solid #CBD5E1;font:600 13px/34px Inter,Arial,sans-serif;color:#0F172A;cursor:pointer;background:#fff}
#dsel-warn .dsel-wbt button:hover{background:#F1F5F9}
#dsel-warn .dsel-wbt button:focus-visible{outline:2px solid #2563EB;outline-offset:2px}
#dsel-warn .dsel-wbt .dsel-wd{border-color:#DC2626;color:#B91C1C}
#dsel-warn .dsel-wbt .dsel-wd:hover{background:#FEF2F2}
#dsel-warn .dsel-wbt .dsel-wall{background:#DC2626;color:#fff}
#dsel-warn .dsel-wbt .dsel-wall:hover{background:#B91C1C}
#dsp-etitle.dsel-rent{cursor:text}
#dsp-etitle.dsel-rent:hover{text-decoration:underline dotted;text-underline-offset:3px}
#dsel-ren{all:unset;box-sizing:border-box;flex:none;width:26px;height:26px;margin-left:4px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;color:#94A3B8;cursor:pointer}
#dsel-ren:hover{background:rgba(148,163,184,.2);color:#fff}
#dsel-ren svg{width:14px;height:14px}
#dsel-reni{box-sizing:border-box;width:min(360px,60vw);height:28px;padding:0 8px;border:1px solid #60A5FA;border-radius:6px;background:#0F172A;color:#fff;font:inherit;outline:none}
#dsp-ppt-toolbar #dsp-etitle.dsel-rent{flex:0 1 auto!important}
#dsp-ppt-toolbar #dsel-ren{margin-right:auto}
#dsel-panel .dsel-seg{display:flex;border:1px solid #CBD5E1;border-radius:8px;overflow:hidden}
#dsel-panel .dsel-seg button{all:unset;box-sizing:border-box;flex:1;height:30px;text-align:center;font-size:12px;font-weight:600;color:#475569;cursor:pointer;background:#fff}
#dsel-panel .dsel-seg button+button{border-left:1px solid #CBD5E1}
#dsel-panel .dsel-seg button.dsel-on{background:#EDE9FE;color:#5B21B6}
#dsel-panel .dsel-seg button[disabled]{cursor:default;opacity:.55}
#dsel-panel .dsel-pbt.dsel-al{justify-content:flex-start;gap:7px;padding:0 8px;font-size:11.5px;white-space:nowrap}
#dsel-panel .dsel-pbt.dsel-al svg{flex:none;color:#475569}
#dsel-panel .dsel-pbt.dsel-al span{overflow:hidden;text-overflow:ellipsis}
#dsel-panel .dsel-pbt[disabled]{opacity:.45;cursor:default}
#dsel-panel .dsel-pbt.dsel-al{min-width:0;font-size:12px}
#dsel-panel .dsel-pbt.dsel-al.dsel-w2{grid-column:1/-1}
html.dsel-live #dsov-bar{display:none!important}
#dsel-ov .dsel-multi .dsel-mini button[data-m="app"]{display:none}
#dsel-menu{position:fixed;z-index:100040;min-width:230px;max-width:320px;padding:5px;border-radius:10px;background:#fff;border:1px solid #CBD5E1;box-shadow:0 12px 34px rgba(15,23,42,.28);font-family:Inter,Arial,sans-serif;display:flex;flex-direction:column}
#dsel-menu button{all:unset;box-sizing:border-box;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:6px 10px;border-radius:6px;font-size:12.5px;color:#0F172A;cursor:pointer;white-space:nowrap}
#dsel-menu button:hover{background:#EEF2FF}
#dsel-menu button[disabled]{opacity:.4;cursor:default;background:none}
#dsel-menu button em{font-style:normal;font-size:11px;color:#94A3B8}
#dsel-menu i{display:block;height:1px;background:#E2E8F0;margin:4px 6px}
html.dsel-pan #dsp-ppt-canvas-area,html.dsel-pan #dsp-ppt-canvas-area *{cursor:grab!important}
html.dsel-panning #dsp-ppt-canvas-area,html.dsel-panning #dsp-ppt-canvas-area *{cursor:grabbing!important;user-select:none!important}
#dsel-keys{position:fixed;z-index:9990;display:flex;gap:6px;font-family:Inter,Arial,sans-serif}
#dsel-keys button{all:unset;box-sizing:border-box;display:inline-flex;align-items:center;gap:6px;height:30px;padding:0 10px;border-radius:8px;background:rgba(15,23,42,.82);border:1px solid rgba(148,163,184,.35);color:#E2E8F0;font-size:12px;font-weight:600;cursor:pointer}
#dsel-keys button:hover{background:#2563EB;color:#fff;border-color:#2563EB}
#dsel-keys button svg{width:15px;height:15px}
#dsel-help{position:fixed;inset:0;z-index:100050;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Inter,Arial,sans-serif}
#dsel-help .dsel-hbox{background:#fff;color:#0F172A;border-radius:14px;width:100%;max-width:760px;max-height:calc(100vh - 32px);display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.35)}
#dsel-help .dsel-hh{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #E2E8F0}
#dsel-help .dsel-hh b{font-size:16px}
#dsel-help .dsel-hh button{all:unset;width:28px;height:28px;border-radius:7px;text-align:center;font-size:20px;line-height:28px;color:#475569;cursor:pointer}
#dsel-help .dsel-hh button:hover{background:#F1F5F9}
#dsel-help .dsel-hb{padding:6px 18px 10px;overflow:auto;columns:2 300px;column-gap:28px}
#dsel-help .dsel-hg{break-inside:avoid;margin:10px 0 6px}
#dsel-help h4{margin:0 0 6px;font:700 11px/1.2 Inter,Arial,sans-serif;letter-spacing:.06em;text-transform:uppercase;color:#64748B}
#dsel-help .dsel-hr{display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:4px 0;border-bottom:1px dashed #E2E8F0;font-size:12.5px}
#dsel-help .dsel-hr span{color:#334155;text-align:right}
#dsel-help kbd{flex:none;font:600 11.5px/1.4 ui-monospace,Menlo,Consolas,monospace;background:#F1F5F9;border:1px solid #CBD5E1;border-bottom-width:2px;border-radius:5px;padding:1px 6px;color:#0F172A;white-space:nowrap}
#dsel-help .dsel-hn{margin:0;padding:8px 18px 14px;font-size:11.5px;color:#64748B}
html.dsel-live #dsp-ppt-main-slide,html.dsel-live #dsp-ppt-main-slide>.dste-slide,html.dsel-live #dsp-ppt-slide-outer{overflow:visible!important}
#dsel-panel .dsel-rl{flex:none;color:#475569}
#dsel-panel .dsel-row select{flex:1;min-width:0;height:28px;border:1px solid #CBD5E1;border-radius:7px;background:#fff;color:#0F172A;font-size:12px;padding:0 6px}
#dsel-panel .dsel-row input[type=number]{width:54px;height:28px;border:1px solid #CBD5E1;border-radius:7px;padding:0 6px;font-size:12px;color:#0F172A;background:#fff;box-sizing:border-box}
#dsel-panel .dsel-gpre{display:grid;grid-template-columns:repeat(6,1fr);gap:5px}
#dsel-panel .dsel-gpre button{all:unset;box-sizing:border-box;height:24px;border-radius:6px;border:1px solid #94A3B8;cursor:pointer;background-color:#fff}
#dsel-panel .dsel-gpre button:hover{outline:2px solid #7C3AED;outline-offset:1px}
#dsel-panel .dsel-dirs{display:flex;gap:3px;flex-wrap:wrap}
#dsel-panel .dsel-dirs button{all:unset;box-sizing:border-box;width:24px;height:24px;border:1px solid #CBD5E1;border-radius:6px;text-align:center;font-size:13px;line-height:22px;color:#334155;cursor:pointer;background:#fff}
#dsel-panel .dsel-dirs button.dsel-on{background:#EDE9FE;border-color:#7C3AED;color:#5B21B6}
#dsel-panel .dsel-grow{align-items:flex-start;margin-bottom:14px}
#dsel-panel .dsel-gwrap{flex:1;min-width:0;padding:0 7px;background:repeating-conic-gradient(#E2E8F0 0 25%,#fff 0 50%) 0 0/10px 10px;border-radius:6px}
#dsel-panel .dsel-gbar{position:relative;height:22px;border-radius:5px;border:1px solid #94A3B8;cursor:copy;box-sizing:border-box}
#dsel-panel .dsel-gs{position:absolute;top:15px;width:13px;height:15px;margin-left:-7px;box-sizing:border-box;border:2px solid #fff;border-radius:3px;box-shadow:0 0 0 1px #64748B;cursor:ew-resize;touch-action:none}
#dsel-panel .dsel-gs.dsel-on{box-shadow:0 0 0 2px #7C3AED;z-index:2}
#dsel-panel .dsel-gbt{all:unset;box-sizing:border-box;flex:none;width:24px;height:22px;border:1px solid #CBD5E1;border-radius:6px;text-align:center;font-size:15px;line-height:20px;color:#0F172A;cursor:pointer;background:#fff}
#dsel-panel .dsel-gbt:hover{background:#F1F5F9}
#dsel-panel .dsel-gbt[disabled]{opacity:.4;cursor:default}

/* object settings on top, fixed buttons at the bottom; Edit screen hides the slides and layout panels */
#dsel-dock{flex-wrap:nowrap;overflow-x:auto;justify-content:flex-start;scrollbar-width:none;padding:7px 12px}
#dsel-dock::-webkit-scrollbar{display:none}
#dsel-dock .dsel-right{flex-wrap:nowrap;white-space:nowrap}
#dsel-dock .dsel-edscr{font-weight:700;border:1.5px solid #0F172A;min-width:96px;justify-content:center}
#dsel-dock .dsel-edscr.dsel-on{background:#0F172A;color:#fff;border-color:#0F172A}
#dsel-dock .dsel-dsep{flex:none;width:1px;align-self:stretch;min-height:28px;background:#CBD5E1;margin:0 6px}
html.dsel-focus #dsp-ppt-panel-left,html.dsel-focus #dsp-vp-panel{display:none!important}
html.dsel-focus body #dsp-editor-fullscreen.dsp-open{right:0!important;bottom:0!important}

/* the settings panel can be dragged by its title bar (double-click the title bar: back to its place) */
#dsel-panel .dsel-ph{cursor:move;touch-action:none;-webkit-user-select:none;user-select:none}
#dsel-panel .dsel-ph button{cursor:pointer}
/* font size: a box with a slider under it */
:is(#dsel-bar,#dsel-dock) .dsel-fsz{position:relative;display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 5px 0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#fff}
:is(#dsel-bar,#dsel-dock) .dsel-fszl{font-size:12.5px;color:#334155;cursor:pointer;white-space:nowrap}
:is(#dsel-bar,#dsel-dock) .dsel-fsz input[type=number]{width:46px;height:24px;border:0;outline:0;border-radius:5px;background:#F1F5F9;color:#0F172A;font-weight:700;font-size:13px;text-align:center;padding:0;-moz-appearance:textfield;appearance:textfield}
:is(#dsel-bar,#dsel-dock) .dsel-fsz input[type=number]::-webkit-inner-spin-button,:is(#dsel-bar,#dsel-dock) .dsel-fsz input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
:is(#dsel-bar,#dsel-dock) .dsel-fszp{display:none;position:absolute;left:-1px;top:100%;padding-top:5px;z-index:6}
:is(#dsel-bar,#dsel-dock) .dsel-fsz:hover .dsel-fszp,:is(#dsel-bar,#dsel-dock) .dsel-fsz:focus-within .dsel-fszp,:is(#dsel-bar,#dsel-dock) .dsel-fsz.dsel-open .dsel-fszp{display:block}
:is(#dsel-bar,#dsel-dock) .dsel-fszin{display:flex;align-items:center;gap:8px;width:240px;padding:10px 12px;background:#fff;border-radius:10px;box-shadow:0 8px 22px rgba(0,0,0,.28)}
:is(#dsel-bar,#dsel-dock) .dsel-fszin input[type=range]{flex:1;min-width:0;margin:0;accent-color:#0F172A;cursor:pointer;height:20px}
:is(#dsel-bar,#dsel-dock) .dsel-fa1{font-size:11px;color:#334155}
:is(#dsel-bar,#dsel-dock) .dsel-fa2{font-size:20px;line-height:1;color:#0F172A}
</style>
<script id="ds-el-ui">
/* DS Template Element Editor - step 2 of 2 (runs last): elements that later steps added (logo, glass backdrops), the final
   look of every element, and the editing tools: selection, bar, Positions, Layers, Add element, Reset. */
(function(){
'use strict';
var D = window.DSTE, X = window.DSEL; if (!D || !X || !D.renderPage || X.ui) return; X.ui = 1;
var W = 1280, H = 720;
function clone(e){ return X.clone(e); }
function $(id){ return document.getElementById(id); }
function esc(s){ return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
function hex(c){ return X.hex(c); }
function r1(v){ return Math.round(v * 10) / 10; }
function toast(m, t){ if (window.dspToast) window.dspToast(m, t || 'success'); }

/* ------------------------------------------------------------------ step 2 */
function stageB(T, slide, vid, idx, deck, p){
  var L = X.layer(slide, vid, false), cnt = {}, seen = {}, groups = [], pend = [], prev = null, hid = p._dsHid || [];
  p.els.forEach(function(e){ if (e && e._dk) seen[e._dk] = 1; });
  p.els.forEach(function(e){
    if (!e || !e.t) return;
    if (e._dk && !e._fol) {
      var o = L && L.m[e._dk];
      if (o) X.style(e, o, T);
      if (!e._f) { e._b = e._b || X.bbox(e); X.geom(e, null); }
      else if (e.t !== 'line' && e.t !== 'path') { e._f.x = e.x; e._f.y = e.y; e._f.w = e.w; e._f.h = e.h; }
      prev = {k: e._dk, els: pend.concat([e]), main: e}; pend = []; groups.push(prev); return;
    }
    if (e.t === 'pic') { e._fol = 1; pend.push(e); return; }
    if (e.frost === 0 && !e._dk && prev && prev.main.t !== 'text') {            /* the light the frosted-glass step paints on a glass box */
      e._fol = 1; e._dk = prev.k + '~s' + prev.els.length; prev.els.push(e); return;
    }
    /* an element a later step added: it gets its key and all of its edits here */
    var k = 'b.' + X.keyFor(e, cnt, {}); while (seen[k]) k += '_'; seen[k] = 1;
    e._dk = k; e._b = X.bbox(e); X.prepText(e, T); X.orig(e);
    var o2 = L && L.m[k];
    if (o2 && o2.hide) { hid.push(e); return; }
    X.geom(e, o2); if (o2) X.style(e, o2, T);
    prev = {k: k, els: pend.concat([e]), main: e}; pend = []; groups.push(prev);
  });
  if (pend.length) groups.push({k: null, els: pend});
  if (L && L.z.length) groups = X.order(groups, L.z);
  var out = []; groups.forEach(function(g){ g.els.forEach(function(e){ out.push(e); }); });
  out.forEach(X.fx);
  var base = null;
  out.forEach(function(e){
    var k = String(e._dk || '').split('~s')[0], o3 = L && L.m[k];
    if (e.t === 'pic' && !e._dk) { /* the blurred backdrop of a glass box: with its box */ } else base = {moved: !!(e._add || (o3 && (o3.dx || o3.dy || (o3.sx && o3.sx !== 1) || (o3.sy && o3.sy !== 1) || o3.rot)))};
    if (!base || base.moved) return;
    if (e.t === 'line' || e.t === 'path') { e._css = (e._css || '') + 'overflow:hidden;'; return; }
    var x = +e.x, y = +e.y, w = +e.w, hh = +e.h; if (!(w > 0 && hh > 0) || !isFinite(x) || !isFinite(y)) return;
    var t0 = Math.max(0, -y), l0 = Math.max(0, -x), r0 = Math.max(0, x + w - W), b0 = Math.max(0, y + hh - H);
    if (!(t0 || l0 || r0 || b0)) return;
    if (e.t === 'shape' || e.rot) { e._wrap = (e._wrap || '') + 'clip-path:inset(0);'; return; }
    e._css = (e._css || '') + 'clip-path:inset(' + (t0 / hh * 100).toFixed(3) + '% ' + (r0 / w * 100).toFixed(3) + '% ' + (b0 / hh * 100).toFixed(3) + '% ' + (l0 / w * 100).toFixed(3) + '%);';
  });
  p.els = out; p._dsHid = hid;
  /* only the deck that is open in the editor: other decks are drawn too (the covers in "My presentations"), and their
     first slide must never be taken for slide 1 of this deck */
  if (deck && deck.slides && idx >= 0 && deck.slides[idx] === slide && decks().indexOf(deck) >= 0) X.pages[idx] = {vid: vid, p: p, T: T};
  for (var f in X.fonts) if (X.fonts[f] === 1) needFont(f);
}
var rpB = D.renderPage;
D.renderPage = function(T, slide, vid, idx, deck){
  var p = rpB.apply(this, arguments);
  try { if (p && p.els && p._dsA && T && X.ALLOW[T.id] && slide) stageB(T, slide, vid || 'v1', idx, deck, p); }
  catch (er) { if (window.console) console.warn('[DSEL] step 2', er); }
  return p;
};

/* ------------------------------------------------------------------ fonts */
var FONTS = ['Inter', 'Roboto', 'Poppins', 'Montserrat', 'Open Sans', 'Lato', 'Outfit', 'DM Sans', 'Nunito', 'Raleway', 'Work Sans', 'Space Grotesk', 'Playfair Display', 'Merriweather', 'Lora', 'Oswald', 'Bebas Neue', 'Caveat'];
var fontT = null;
function needFont(f){
  if (X.fonts[f] === 2) return; X.fonts[f] = 2;
  var l = document.createElement('link'); l.rel = 'stylesheet';
  l.href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g, '+') + ':wght@300;400;500;600;700;800&display=swap';
  l.onerror = function(){ l.href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g, '+') + '&display=swap'; l.onerror = null; };
  document.head.appendChild(l);
  if (document.fonts && document.fonts.load) {
    Promise.all([document.fonts.load('400 20px "' + f + '"'), document.fonts.load('700 20px "' + f + '"')]).then(function(){
      clearTimeout(fontT); fontT = setTimeout(function(){ if (on()) { try { D.refreshEditor(); } catch (e) {} } }, 150);
    }).catch(function(){});
  }
}

/* ------------------------------------------------------------------ deck / slide */
function fsEl(){ return $('dsp-editor-fullscreen'); }
function mainEl(){ return $('dsp-ppt-main-slide'); }
function decks(){ var a = []; if (window.S && S.data && S.data.slides) a.push(S.data); if (window.G && G.pptData && G.pptData.slides && a.indexOf(G.pptData) < 0) a.push(G.pptData); return a; }
function deck(){ return decks()[0] || null; }
function on(){
  var fs = fsEl(); if (!fs || !fs.classList.contains('dsp-open')) return false;
  var d = deck(); if (!d || !d.tpl || !X.ALLOW[d.tpl] || !D.reg[d.tpl]) return false;
  var m = mainEl(); return !!(m && m.offsetWidth);
}
function curIdx(){ var d = deck(); var i = (window.S && S.idx >= 0) ? S.idx : 0; return d && d.slides[i] ? i : 0; }
function curVid(){ return (window.S && S.choices && S.choices['slide_' + curIdx()]) || 'v1'; }
function curSlide(){ var d = deck(); return d ? d.slides[curIdx()] : null; }
function mirror(idx){
  var ds = decks(); if (ds.length < 2) return;
  var a = ds[0].slides[idx], b = ds[1].slides[idx]; if (!a || !b || a === b) return;
  if (a.dsel) b.dsel = a.dsel; else delete b.dsel;
}
function page(){ var pg = X.pages[curIdx()]; return pg && pg.vid === curVid() ? pg : null; }
/* PNG pictures added with "+ Add PNG" are kept on the slide as png_objects, always in front. Here they are elements like the others. */
function pngList(s){ return s && Array.isArray(s.png_objects) ? s.png_objects : []; }
function isPng(k){ return /^png\d+$/.test(String(k || '')); }
function pngOf(k){ var m = /^png(\d+)$/.exec(String(k || '')); return m ? (pngList(curSlide())[+m[1]] || null) : null; }
function pngEl(b, k){ var f = {x: +b.x || 0, y: +b.y || 0, w: +b.w || 10, h: +b.h || 10, rot: 0}; return {t: 'png', _dk: 'png' + k, _png: b, op: b.op, r: Math.min(+b.rr || 0, Math.floor(Math.min(f.w, f.h) / 2)), x: f.x, y: f.y, w: f.w, h: f.h, _b: {x: f.x, y: f.y, w: f.w, h: f.h}, _f: f}; }
var needBake = [];
function applyPng(b, f){
  if (b.hold) {
    /* a picture frame: any width and height; the picture is cut again to fill it, never stretched */
    var ow = b.w, oh = b.h;
    if (f.w != null) b.w = Math.max(24, Math.min(1600, Math.round(f.w))); if (f.h != null) b.h = Math.max(24, Math.min(1600, Math.round(f.h)));
    if (f.x != null) b.x = Math.round(f.x); if (f.y != null) b.y = Math.round(f.y);
    if ((b.w !== ow || b.h !== oh) && needBake.indexOf(b) < 0) needBake.push(b);
    return;
  }
  var w0 = +b.w || 10, h0 = +b.h || 10, k = 1;
  if (f.w != null && f.h != null) k = Math.max(f.w / w0, f.h / h0); else if (f.w != null) k = f.w / w0; else if (f.h != null) k = f.h / h0;
  k = Math.max(24 / w0, Math.min(1600 / w0, k));
  b.w = Math.round(w0 * k); b.h = Math.round(h0 * k);
  if (f.x != null) b.x = Math.round(f.x); if (f.y != null) b.y = Math.round(f.y);
}
function remapPng(L, old, now){
  var map = {}, pm = {}, k;
  old.forEach(function(b, i){ var j = now.indexOf(b); map['png' + i] = j >= 0 ? 'png' + j : null; });
  for (k in map) if (L.m[k]) { if (map[k]) pm[map[k]] = L.m[k]; delete L.m[k]; }
  for (k in pm) L.m[k] = pm[k];
  if (L.g) for (var id in L.g) { L.g[id] = L.g[id].map(function(q){ return map.hasOwnProperty(q) ? map[q] : q; }).filter(Boolean); if (L.g[id].length < 2) delete L.g[id]; }
}
/* a copy of a picture frame is a picture of its own (kept as a PNG, corners included); "Change image" gives it another picture */
function rrPath(cx, w, h, r){ r = Math.max(0, Math.min(r, w / 2, h / 2)); cx.beginPath(); cx.moveTo(r, 0); cx.arcTo(w, 0, w, h, r); cx.arcTo(w, h, 0, h, r); cx.arcTo(0, h, 0, 0, r); cx.arcTo(0, 0, w, 0, r); cx.closePath(); }
function holderPng(src, w, h, r, pos, cb, ex){
  function draw(im){
    var m = 1000, data = '';
    try {
      for (var t = 0; t < 6; t++) {
        var k = m / Math.max(w, h), cv = document.createElement('canvas'); cv.width = Math.max(2, Math.round(w * k)); cv.height = Math.max(2, Math.round(h * k));
        var cx = cv.getContext('2d'); rrPath(cx, cv.width, cv.height, r * k); cx.clip();
        if (im) {
          var sc = Math.max(cv.width / im.naturalWidth, cv.height / im.naturalHeight) * Math.max(1, Math.min(4, (ex && +ex.zm) || 1)), dw = im.naturalWidth * sc, dh = im.naturalHeight * sc;
          if (ex && ex.op != null && +ex.op < 1) cx.globalAlpha = Math.max(0, +ex.op);
          cx.drawImage(im, (cv.width - dw) * ((pos && pos.x != null ? +pos.x : 50) / 100), (cv.height - dh) * ((pos && pos.y != null ? +pos.y : 50) / 100), dw, dh);
        } else {
          cx.fillStyle = 'rgba(100,116,139,.32)'; cx.fillRect(0, 0, cv.width, cv.height);
          var u2 = Math.min(cv.width, cv.height) * 0.1, ox = cv.width / 2, oy = cv.height / 2;
          cx.strokeStyle = 'rgba(255,255,255,.9)'; cx.lineWidth = Math.max(2, u2 * 0.14); cx.lineJoin = 'round';
          cx.strokeRect(ox - u2, oy - u2 * 0.75, u2 * 2, u2 * 1.5);
          cx.beginPath(); cx.moveTo(ox - u2, oy + u2 * 0.5); cx.lineTo(ox - u2 * 0.3, oy - u2 * 0.1); cx.lineTo(ox + u2 * 0.2, oy + u2 * 0.4); cx.lineTo(ox + u2 * 0.55, oy + u2 * 0.1); cx.lineTo(ox + u2, oy + u2 * 0.5); cx.stroke();
        }
        data = cv.toDataURL('image/png'); if (data.length < 2800000) break; m = Math.round(m * 0.72);
      }
    } catch (x) { data = ''; }
    cb(data && data.length < 2800000 ? data : '');
  }
  if (!src) { draw(null); return; }
  var im = new Image(); im.onload = function(){ draw(im); }; im.onerror = function(){ cb(''); }; im.src = src;
}
function srcFit(src, cb){
  if (!src) { cb(''); return; }
  var im = new Image();
  im.onload = function(){
    try {
      var mx = Math.max(im.naturalWidth, im.naturalHeight);
      if (mx <= 1400 && src.length < 900000) { cb(src); return; }
      var k = Math.min(1, 1400 / mx), cv = document.createElement('canvas'); cv.width = Math.round(im.naturalWidth * k); cv.height = Math.round(im.naturalHeight * k);
      var cx = cv.getContext('2d'); cx.fillStyle = '#fff'; cx.fillRect(0, 0, cv.width, cv.height); cx.drawImage(im, 0, 0, cv.width, cv.height);
      cb(cv.toDataURL('image/jpeg', 0.88));
    } catch (x) { cb(src.length < 900000 ? src : ''); }
  };
  im.onerror = function(){ cb(''); }; im.src = src;
}
var PICKF = ['image_data', 'image_position', 'image_market_id', 'image_market_tier', 'image_mkt_fetched'], pick = null;
function openPngPick(k){
  var b = pngOf(k), i = curIdx(); if (!b) return;
  if (typeof window.vpImgOptionOpen !== 'function') { toast('The picture picker is not available here.', 'error'); return; }
  /* the picker always puts its picture on the slide: remember the slide's own picture, and move the new one into this frame when it arrives */
  var snaps = decks().map(function(d){ var s = d.slides[i] || {}, o = {}; PICKF.forEach(function(f){ o[f] = (s[f] && typeof s[f] === 'object') ? JSON.parse(JSON.stringify(s[f])) : s[f]; }); return o; });
  pick = {b: b, i: i, snaps: snaps, t: Date.now(), base: (deck().slides[i] || {}).image_data || ''};
  window.vpImgOptionOpen(i);
}
/* Fill > Picture: the same picture window as the slide picture (generate with AI, browse the store, upload) */
function openFillPick(k){
  var i = curIdx(); if (!k || !elByKey(k)) return;
  if (typeof window.vpImgOptionOpen !== 'function') { toast('The picture window is not available here.', 'error'); return; }
  var snaps = decks().map(function(d){ var s = d.slides[i] || {}, o = {}; PICKF.forEach(function(f){ o[f] = (s[f] && typeof s[f] === 'object') ? JSON.parse(JSON.stringify(s[f])) : s[f]; }); return o; });
  pick = {fk: k, i: i, snaps: snaps, t: Date.now(), base: (deck().slides[i] || {}).image_data || '', keys: pfKeys().length > 1 ? pfKeys() : null, one: pfOne};
  window.vpImgOptionOpen(i);
}
/* Fill > Picture with several boxes selected: one picture across all of them. Each box keeps pimg.gb = the picture's box
   measured from its own top left corner, so the picture moves with the boxes */
var pfOne = true;
function pfKeys(){ return (sset.length > 1 ? sset : (sel ? [sel] : [])).filter(function(k){ var x = elByKey(k); return x && fam(x) === 'box' && !isLocked(k); }); }
function pfGroup(keys){ var fr = keys.map(function(k){ return boxOf(elByKey(k)); }); return fr.length > 1 ? bboxOf(fr) : null; }
function pfRel(G, x){ var f = boxOf(x); return {x: r1(G.x - f.x), y: r1(G.y - f.y), w: r1(G.w), h: r1(G.h)}; }
function pfEach(fn, quiet, tag){
  var ks = pfKeys();
  commit(function(L){ ks.forEach(function(k){ var x = elByKey(k); if (!x || !x.pimg) return; var np = JSON.parse(JSON.stringify(x.pimg)); fn(np, x); var o = L.m[k] || (L.m[k] = {}); o.pimg = np; }); }, quiet, tag);
  if (quiet) { rerender(); sync(); var l = bar && bar.querySelector('.dsel-left'), e2 = elByKey(sel); if (l && e2) l.innerHTML = leftHTML(e2); }
}
function pfSetOne(on){
  pfOne = !!on; var ks = pfKeys(), G = on ? pfGroup(ks) : null;
  pfEach(function(np, x){ if (G) np.gb = pfRel(G, x); else delete np.gb; });
}
function picFillHTML(e){
  var p = e.pimg || {}, has = !!p.src;
  var h = '<div class="dsel-sub">Picture source</div>';
  if (has) h += '<div style="height:96px;border-radius:8px;border:1px solid #CBD5E1;background:#F1F5F9 url(\'' + String(p.src).replace(/['"()\\<>\s]/g, '') + '\') center/cover no-repeat;margin-bottom:8px"></div>';
  h += '<div class="dsel-grid2"><button type="button" class="dsel-pbt" data-p="pfins">' + (has ? 'Change picture...' : 'Insert...') + '</button>' + (has ? '<button type="button" class="dsel-pbt dsel-danger" data-p="pfdel">Remove picture</button>' : '') + '</div>';
  var many = pfKeys().length > 1;
  if (many) h += '<div class="dsel-sub">Several shapes</div><div class="dsel-seg"><button type="button" data-p="pfone" data-n="1" class="' + ((has ? !!p.gb : pfOne) ? 'dsel-on' : '') + '">One picture across all</button><button type="button" data-p="pfone" data-n="0" class="' + ((has ? !p.gb : !pfOne) ? 'dsel-on' : '') + '">Each its own</button></div>';
  if (!has) return h + '<p class="dsel-note">Insert opens the picture window: generate with AI, browse the store or upload from your device.' + (many ? ' With several shapes selected, one picture shows through all of them.' : '') + '</p>';
  h += slider('Transparency', 'pftr', 0, 100, 1, Math.round((1 - (p.op != null ? p.op : 1)) * 100), '%');
  h += '<div class="dsel-sub">Fit</div><div class="dsel-seg"><button type="button" data-p="pffit" data-n="fill" class="' + (p.st ? '' : 'dsel-on') + '">Fill shape</button><button type="button" data-p="pffit" data-n="stretch" class="' + (p.st ? 'dsel-on' : '') + '">Stretch</button></div>';
  if (!p.st) h += slider('Zoom', 'pfz', 100, 400, 5, Math.round((p.z || 1) * 100), '%') + slider('Left \u2194 right', 'pfx', 0, 100, 1, Math.round(p.px != null ? p.px : 50), '%') + slider('Top \u2195 bottom', 'pfy', 0, 100, 1, Math.round(p.py != null ? p.py : 50), '%');
  return h;
}
function checkPick(){
  var p = pick; if (!p) return;
  if (Date.now() - p.t > 300000 || curIdx() !== p.i) { pick = null; return; }
  var s = deck().slides[p.i], now = (s && s.image_data) || '';
  if (!now || now === p.base) return;
  pick = null;
  decks().forEach(function(d, j){ var sl = d.slides[p.i], o = p.snaps[j]; if (!sl || !o) return; PICKF.forEach(function(f){ if (o[f] === undefined) delete sl[f]; else sl[f] = o[f]; }); });
  if (p.fk) { /* Fill > Picture: the new picture goes into the shape, the slide keeps its own picture */
    var fk = p.fk;
    srcFit(now, function(fit){
      var src = fit || now, im = new Image();
      im.onload = function(){
        if (!/^data:image\/(png|jpeg);base64,/.test(src)) { try { var cv = document.createElement('canvas'); cv.width = im.naturalWidth; cv.height = im.naturalHeight; cv.getContext('2d').drawImage(im, 0, 0); src = cv.toDataURL('image/jpeg', 0.9); } catch (x) { src = ''; } }
        if (!src || curIdx() !== p.i || !elByKey(fk)) { rerender(); if (!src) toast('This picture could not be used.', 'error'); return; }
        var mk = p.keys && p.keys.filter(function(k){ return elByKey(k); });
        if (mk && mk.length > 1) {
          var G = p.one !== false ? pfGroup(mk) : null;
          commit(function(L){ mk.forEach(function(k){ var o = L.m[k] || (L.m[k] = {}), np = {src: src, iw: im.naturalWidth, ih: im.naturalHeight, op: 1, z: 1, px: 50, py: 50, st: 0}; if (G) np.gb = pfRel(G, elByKey(k)); o.pimg = np; o.fill = ''; delete o.grad; }); });
          pmode = 'fill'; paint(); try { if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs(); } catch (x3) {}
          toast(G ? 'One picture across ' + mk.length + ' shapes.' : 'Picture fill added.');
          return;
        }
        if (sel !== fk) select(fk);
        setO(fk, {pimg: {src: src, iw: im.naturalWidth, ih: im.naturalHeight, op: 1, z: 1, px: 50, py: 50, st: 0}, fill: '', grad: null});
        pmode = 'fill'; paint(); try { if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs(); } catch (x2) {}
        toast('Picture fill added.');
      };
      im.onerror = function(){ rerender(); toast('This picture could not be used.', 'error'); };
      im.src = src;
    });
    return;
  }
  srcFit(now, function(fit){ holderPng(fit || now, +p.b.w || 10, +p.b.h || 10, +p.b.rr || 0, {x: 50, y: 50}, function(data){
    if (!data || pngList(deck().slides[p.i]).indexOf(p.b) < 0) { rerender(); if (!data) toast('This picture could not be used.', 'error'); return; }
    pushUndo(p.i); p.b.src = data; p.b.hold = 1; p.b.o = fit || ''; p.b.pos = {x: 50, y: 50}; delete p.b.zm; if (pngTh) pngTh.delete(p.b);
    rerender(); try { if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs(); } catch (x) {}
    paint(); toast('Picture changed.');
  }, {op: p.b.op}); });
}
function dupImg(e){
  var s = curSlide(), f = frameOf(e); if (!s) return;
  if (pngList(s).length >= 6) { toast('A slide can hold 6 extra pictures.', 'error'); return; }
  var had = !!s.image_data, rr0 = e.shape === 'ell' ? 9999 : (+e.r || 0), pos0 = s.image_position ? {x: +s.image_position.x || 0, y: +s.image_position.y || 0} : {x: 50, y: 50};
  if (!s.image_position) pos0 = {x: 50, y: 50};
  srcFit(s.image_data || '', function(fit){ holderPng(fit, f.w, f.h, rr0, pos0, function(data){
    if (!data) { toast('This picture could not be copied.', 'error'); return; }
    var key = null;
    commit(function(L, s2){ if (!Array.isArray(s2.png_objects)) s2.png_objects = []; if (s2.png_objects.length >= 6) return; s2.png_objects.push({src: data, x: Math.round(f.x + 24), y: Math.round(f.y + 24), w: Math.round(f.w), h: Math.round(f.h), rr: rr0, hold: 1, o: fit || '', pos: pos0}); key = 'png' + (s2.png_objects.length - 1); }, true);
    rerender(); if (key) { pngSeen = pngList(curSlide()).length; setSel([key]); }
    toast(had ? 'Picture frame copied. Use "Change image" to give the copy another picture.' : 'Picture frame copied. Use "Add image" on it to pick a picture.');
  }); });
}
var pngTh = (typeof WeakMap === 'function') ? new WeakMap() : null;
function pngThumb(b){
  if (!pngTh) return '';
  var v = pngTh.get(b); if (typeof v === 'string') return v; if (v) return '';
  pngTh.set(b, 1);
  var im = new Image();
  im.onload = function(){ try { var s = 40 / Math.max(im.naturalWidth, im.naturalHeight, 1), cv = document.createElement('canvas'); cv.width = Math.max(1, Math.round(im.naturalWidth * s)); cv.height = Math.max(1, Math.round(im.naturalHeight * s)); cv.getContext('2d').drawImage(im, 0, 0, cv.width, cv.height); pngTh.set(b, cv.toDataURL('image/png')); if (pmode === 'layers') paintPanel(); } catch (x) {} };
  im.src = b.src; return '';
}
function els(){ var pg = page(); if (!pg) return []; var a = pg.p.els.filter(function(e){ return e._dk && !e._fol; }); pngList(curSlide()).slice(0, 6).forEach(function(b, k){ if (b && b.src) a.push(pngEl(b, k)); }); return a; }
function elByKey(k){ if (!k) return null; var a = els(); for (var i = 0; i < a.length; i++) if (a[i]._dk === k) return a[i]; return null; }
function rerender(){ try { if (typeof window.applyCanvas === 'function') window.applyCanvas(curIdx(), curVid()); else D.refreshEditor(); } catch (e) {} }

/* the generated state of a slide, kept once, for Reset */
var SKIP = /^(image|dsel$|png_objects$|__)/;
function snap(s){ var o = {}, k; for (k in s) { if (SKIP.test(k) || typeof s[k] === 'function') continue; try { o[k] = JSON.parse(JSON.stringify(s[k])); } catch (e) {} } return o; }
function ensureOrig(){
  var d = deck(); if (!d) return;
  d.slides.forEach(function(s, i){
    if (!s || (s.dsel && s.dsel.o)) return;
    if (!s.dsel || typeof s.dsel !== 'object') s.dsel = {};
    s.dsel.o = snap(s); mirror(i);
  });
}

/* ------------------------------------------------------------------ undo */
var undo = {}, redo = {};
function stamp(idx){ var s = deck().slides[idx]; return {js: JSON.stringify((s && s.dsel) || null), png: pngList(s).map(function(b){ return {b: b, x: b.x, y: b.y, w: b.w, h: b.h, s: b.src, e: JSON.stringify([b.rr, b.op, b.zm, b.pos])}; })}; }
function pushUndo(idx){ (undo[idx] = undo[idx] || []).push(stamp(idx)); if (undo[idx].length > 60) undo[idx].shift(); redo[idx] = []; }
function restore(idx, st){ var s = deck().slides[idx]; var v = JSON.parse(st.js); if (v) s.dsel = v; else delete s.dsel; if (st.png.length) s.png_objects = st.png.map(function(p){ p.b.x = p.x; p.b.y = p.y; p.b.w = p.w; p.b.h = p.h; if (p.s && p.b.src !== p.s) { p.b.src = p.s; if (pngTh) pngTh.delete(p.b); } if (p.e && p.b.hold) { try { var q = JSON.parse(p.e); ['rr', 'op', 'zm', 'pos'].forEach(function(n2, i2){ if (q[i2] == null) delete p.b[n2]; else p.b[n2] = q[i2]; }); } catch (x) {} } return p.b; }); else if (s.png_objects) delete s.png_objects; mirror(idx); }
function doUndo(){ var i = curIdx(), u = undo[i]; if (!u || !u.length) { toast('Nothing to undo on this slide.'); return; } (redo[i] = redo[i] || []).push(stamp(i)); restore(i, u.pop()); rerender(); paint(); }
function doRedo(){ var i = curIdx(), r = redo[i]; if (!r || !r.length) { toast('Nothing to redo.'); return; } (undo[i] = undo[i] || []).push(stamp(i)); restore(i, r.pop()); rerender(); paint(); }
function validKey(k){ return !!k; }

/* ------------------------------------------------------------------ edits */
var sel = null, sset = [], lastIdx = -1, lastVid = '', lastTag = '', lastTagT = 0;
/* sel is the element the bar shows; sset is everything that is selected (one element, several, or a group) */
function norm(){
  if (!sel) { sset = []; return; }
  if (sset.indexOf(sel) < 0) sset = [sel];
  sset = sset.filter(function(k, i){ return sset.indexOf(k) === i && !!elByKey(k) && !isLocked(k); });
  if (sset.indexOf(sel) < 0) sel = sset.length ? sset[sset.length - 1] : null;
}
function setSel(keys, primary){
  var u = []; (keys || []).forEach(function(k){ if (k && u.indexOf(k) < 0 && elByKey(k) && !isLocked(k)) u.push(k); });
  sset = u; sel = u.length ? (primary && u.indexOf(primary) >= 0 ? primary : u[u.length - 1]) : null;
  sync(); paint();
}
function isLocked(k){ var L = X.layer(curSlide(), curVid(), false); return !!(L && L.m[k] && L.m[k].lock); }
function live1(){ return els().filter(function(e){ return !isLocked(e._dk); }); }
function markLocks(){
  var m = mainEl(); if (!m) return;
  var all = m.querySelectorAll('[data-dk]');
  for (var i = 0; i < all.length; i++) { var k = all[i].getAttribute('data-dk').split('~s')[0], l = isLocked(k); if (l !== all[i].hasAttribute('data-dsel-lock')) { if (l) all[i].setAttribute('data-dsel-lock', '1'); else all[i].removeAttribute('data-dsel-lock'); } }
}
function setLock(keys, v){
  commit(function(L){ keys.forEach(function(k){ var o = L.m[k] || (L.m[k] = {}); if (v) o.lock = 1; else delete o.lock; }); }, true);
  if (v) { sset = sset.filter(function(k){ return keys.indexOf(k) < 0; }); if (keys.indexOf(sel) >= 0) sel = sset.length ? sset[sset.length - 1] : null; }
  paint();
  toast(v ? (keys.length > 1 ? 'Locked. These elements' : 'Locked. This element') + ' cannot be selected or moved on the slide until you unlock ' + (keys.length > 1 ? 'them' : 'it') + ' in Layers.' : 'Unlocked.');
}
function groupName(id){ var L = X.layer(curSlide(), curVid(), false); return (L && L.gname && L.gname[id]) || ('Group ' + String(id).replace(/^g/, '')); }
function groupKeys(id){ var g = groupsOf()[id] || []; return g.filter(function(k){ return !!elByKey(k); }); }
var gcol = {};
function fam(e){ return !e ? '' : (/^(rect|ell|shape)$/.test(e.t) ? 'box' : e.t); }
function groupsOf(){ var L = X.layer(curSlide(), curVid(), false); return (L && L.g) || {}; }
function groupId(key){ var g = groupsOf(); for (var id in g) if (g[id] && g[id].indexOf(key) >= 0) return id; return null; }
function expand(key){ var id = groupId(key); if (!id) return [key]; var a = groupsOf()[id].filter(function(k){ return !!elByKey(k) && !isLocked(k); }); return a.length > 1 ? a : [key]; }
function hasGroup(){ return sset.some(function(k){ return !!groupId(k); }); }
function oneGroup(){
  if (sset.length < 2) return false; var id = groupId(sset[0]); if (!id) return false;
  var g = groupsOf()[id].filter(function(k){ return !!elByKey(k); });
  return g.length === sset.length && sset.every(function(k){ return g.indexOf(k) >= 0; });
}
function doGroup(){
  if (sset.length < 2) { toast('Select two or more elements first (Shift + click, or drag a box around them).', 'error'); return; }
  var keys = sset.slice();
  commit(function(L){
    L.g = L.g || {};
    for (var id in L.g) { L.g[id] = L.g[id].filter(function(k){ return keys.indexOf(k) < 0; }); if (L.g[id].length < 2) delete L.g[id]; }
    L.gn = (L.gn || 0) + 1; L.g['g' + L.gn] = keys;
    /* like PowerPoint: the members sit together in the stacking order, where the front-most one was */
    var z = keyOrder(), mem = z.filter(function(k){ return keys.indexOf(k) >= 0; });
    if (mem.length > 1) { var top = z.indexOf(mem[mem.length - 1]), rest = z.filter(function(k){ return keys.indexOf(k) < 0; }), at = 0; for (var i = 0; i < top; i++) if (keys.indexOf(z[i]) < 0) at++; L.z = rest.slice(0, at).concat(mem, rest.slice(at)); }
  });
  if (pmode !== 'layers') { pmode = 'layers'; paint(); }
  toast('Grouped. One click selects the group; click an element again to pick only that one.');
}
function doUngroup(){
  var keys = sset.slice();
  commit(function(L){ if (!L.g) return; for (var id in L.g) if (L.g[id].some(function(k){ return keys.indexOf(k) >= 0; })) delete L.g[id]; });
  toast('Ungrouped.');
}
function commit(fn, quiet, tag){
  var s = curSlide(); if (!s) return;
  var i = curIdx(), now = Date.now();
  if (!(tag && tag === lastTag && now - lastTagT < 1500)) pushUndo(i);      /* one slider drag is one undo step */
  lastTag = tag || ''; lastTagT = now;
  var L = X.layer(s, curVid(), true);
  try { fn(L, s); } catch (e) { if (window.console) console.warn('[DSEL]', e); }
  if (L.gname) { for (var gq in L.gname) if (!L.g || !L.g[gq]) delete L.gname[gq]; }
  for (var k in L.m) { var o = L.m[k], n = 0, q; for (q in o) { if (o[q] == null) delete o[q]; else n++; } if (!n) delete L.m[k]; }
  mirror(i);
  if (needBake.length) { var nb = needBake; needBake = []; nb.forEach(function(b){ holderPng(b.o || '', +b.w || 10, +b.h || 10, +b.rr || 0, b.pos, function(data){ if (!data) return; b.src = data; if (pngTh) pngTh.delete(b); rerender(); sync(); }, {op: b.op, zm: b.zm}); }); }
  if (!quiet) { rerender(); paint(); }
}
function setO(key, patch, quiet, tag){
  if (!key) return;
  /* a colour, font or size set on the bar goes to every selected element of the same kind; places and sizes do not */
  var keys = [key], geo = false, q;
  for (q in patch) if (/^(dx|dy|sx|sy|rot|hide|text|name)$/.test(q)) geo = true;
  if (!geo && key === sel && sset.length > 1) { var f0 = fam(elByKey(key)); keys = sset.filter(function(k){ return fam(elByKey(k)) === f0; }); if (keys.indexOf(key) < 0) keys.push(key); }
  commit(function(L){ keys.forEach(function(kk){ var o = L.m[kk] || (L.m[kk] = {}); for (var k in patch) { if (patch[k] === null) delete o[k]; else o[k] = patch[k]; } }); }, quiet, tag);
}
function moveBy(keys, dx, dy, tag){
  commit(function(L){ keys.forEach(function(k){ var pb = pngOf(k); if (pb) { pb.x = Math.round((+pb.x || 0) + dx); pb.y = Math.round((+pb.y || 0) + dy); return; } if (isPng(k)) return; var o = L.m[k] || (L.m[k] = {}); o.dx = r1((+o.dx || 0) + dx); o.dy = r1((+o.dy || 0) + dy); }); }, false, tag);
}
function ovOf(key){ var L = X.layer(curSlide(), curVid(), false); return (L && L.m[key]) || {}; }
/* set the frame of an element in slide px */
function patchFor(e, f){
  var b = e._b, p = {};
  if (f.x != null) p.dx = r1(f.x - b.x);
  if (f.y != null) p.dy = r1(f.y - b.y);
  if (f.w != null && b.w >= 1) p.sx = Math.max(0.01, f.w / b.w);
  if (f.h != null && b.h >= 1) p.sy = Math.max(0.01, f.h / b.h);
  if (f.rot != null) p.rot = ((Math.round(f.rot * 10) / 10) % 360 + 360) % 360;
  return p;
}
function setFrame(key, f, quiet){
  var e = elByKey(key); if (!e) return;
  if (e._png) { commit(function(){ applyPng(e._png, f); }, quiet); return; }
  setO(key, patchFor(e, f), quiet);
}
/* several elements at once: list of {k, f, size} */
function setFrames(list){
  commit(function(L){ list.forEach(function(it){ var e = elByKey(it.k); if (!e) return; if (e._png) { applyPng(e._png, it.f); return; } var p = patchFor(e, it.f), o = L.m[it.k] || (L.m[it.k] = {}), q; for (q in p) o[q] = p[q]; if (it.size) o.size = it.size; }); });
}
function boxOf(e){ var f = e._f || {x: e.x, y: e.y, w: e.w, h: e.h, rot: 0}; return {x: f.x, y: f.y, w: f.w, h: f.h, rot: f.rot || 0}; }
function cornersOf(f){
  var cx = f.x + f.w / 2, cy = f.y + f.h / 2, r = (f.rot || 0) * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r);
  return [[-1, -1], [1, -1], [1, 1], [-1, 1]].map(function(q){ var ux = q[0] * f.w / 2, uy = q[1] * f.h / 2; return [cx + ux * cs - uy * sn, cy + ux * sn + uy * cs]; });
}
function bboxOf(frames){
  var x0 = 1e9, y0 = 1e9, x1 = -1e9, y1 = -1e9;
  frames.forEach(function(f){ cornersOf(f).forEach(function(p){ x0 = Math.min(x0, p[0]); y0 = Math.min(y0, p[1]); x1 = Math.max(x1, p[0]); y1 = Math.max(y1, p[1]); }); });
  return {x: x0, y: y0, w: x1 - x0, h: y1 - y0, rot: 0};
}
function selEls(){ return sset.map(elByKey).filter(Boolean); }
function selFrame(){ var a = selEls(); if (!a.length) return null; if (a.length === 1) return frameOf(a[0]); return bboxOf(a.map(frameOf)); }
function itemsOf(keys){ return keys.map(function(k){ var e = elByKey(k); return e ? {k: k, e: e, g: boxOf(e)} : null; }).filter(Boolean); }
/* the selection as a whole made larger / smaller: every element keeps its place inside it */
function scaleItems(items, F, nf){
  if (F.w < 1 || F.h < 1) return;
  var kx = nf.w / F.w, ky = nf.h / F.h, even = Math.abs(kx - ky) < 0.02;
  setFrames(items.map(function(it){
    var g = it.g, o = {k: it.k, f: {x: nf.x + (g.x - F.x) * kx, y: nf.y + (g.y - F.y) * ky, w: g.w * kx, h: g.h * ky}};
    if (it.e.t === 'text' && even && it.e.size) o.size = Math.max(6, Math.round(it.e.size * kx * 10) / 10);
    return o;
  }));
}
/* the selection as a whole turned around its middle */
function rotateItems(items, F, deg){
  var cx = F.x + F.w / 2, cy = F.y + F.h / 2, r = deg * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r);
  setFrames(items.map(function(it){
    var g = it.g, ux = g.x + g.w / 2 - cx, uy = g.y + g.h / 2 - cy, nx = cx + ux * cs - uy * sn, ny = cy + ux * sn + uy * cs;
    var f = {x: nx - g.w / 2, y: ny - g.h / 2}; if (canRotate(it.e)) f.rot = (g.rot || 0) + deg;
    return {k: it.k, f: f};
  }));
}
function keyOrder(){ return els().filter(function(e){ return !e._png; }).map(function(e){ return e._dk; }); }
function arrange(how){
  norm(); if (!sset.length) return;
  var z = keyOrder(), S = sset.filter(function(k){ return z.indexOf(k) >= 0; }).sort(function(a, b){ return z.indexOf(a) - z.indexOf(b); }), i, t;
  if (!S.length) { toast('PNG pictures always stay in front of the other elements.'); return; }
  if (how === 'front' || how === 'back') { z = z.filter(function(k){ return S.indexOf(k) < 0; }); z = how === 'front' ? z.concat(S) : S.concat(z); }
  else if (how === 'fwd') { for (i = z.length - 2; i >= 0; i--) if (S.indexOf(z[i]) >= 0 && S.indexOf(z[i + 1]) < 0) { t = z[i]; z[i] = z[i + 1]; z[i + 1] = t; } }
  else { for (i = 1; i < z.length; i++) if (S.indexOf(z[i]) >= 0 && S.indexOf(z[i - 1]) < 0) { t = z[i]; z[i] = z[i - 1]; z[i - 1] = t; } }
  commit(function(L){ L.z = z; });
}
function removeMany(keys){
  var list = keys.map(elByKey).filter(Boolean); if (!list.length) return;
  var added = 0;
  commit(function(L, s){
    var pk = list.filter(function(e){ return !!e._png; });
    if (pk.length) { var arr = pngList(s), old = arr.slice(); pk.forEach(function(e){ var i = arr.indexOf(e._png); if (i >= 0) arr.splice(i, 1); }); if (!arr.length) delete s.png_objects; remapPng(L, old, arr); }
      /* the page keeps the deck twice (editor copy and saved copy): the picture leaves both, so it does not come back after Save */
      if (pk.length) decks().forEach(function(d){ var s2 = d.slides[curIdx()]; if (!s2 || s2 === s || !Array.isArray(s2.png_objects)) return; pk.forEach(function(e){ var pb = e._png, j = -1; s2.png_objects.forEach(function(o, q){ if (j < 0 && o && (o === pb || (o.src === pb.src && +o.x === +pb.x && +o.y === +pb.y))) j = q; }); if (j >= 0) s2.png_objects.splice(j, 1); }); if (!s2.png_objects.length) delete s2.png_objects; });
    list.forEach(function(e){
      var key = e._dk;
      if (e._png) { added++; return; }
      if (e._add) { added++; L.add = L.add.filter(function(a){ return a.k !== key; }); delete L.m[key]; L.z = L.z.filter(function(k){ return k !== key; }); if (L.g) for (var id in L.g) { L.g[id] = L.g[id].filter(function(k){ return k !== key; }); if (L.g[id].length < 2) delete L.g[id]; } }
      else (L.m[key] || (L.m[key] = {})).hide = true;
    });
  }, true);
  sel = null; sset = []; rerender(); paint();
  toast(added === list.length ? (list.length > 1 ? 'Elements removed.' : 'Element removed.') : 'Hidden. Bring it back from Layers, or with Undo.');
}
function removeEl(key){ removeMany([key]); }
function plain(e){ var c = {}, k; for (k in e) { if (k.charAt(0) !== '_' && k !== 'ed' && k !== 'fam' && k !== 'frost') c[k] = e[k]; } if (c.d) c.d = c.d.map(function(x){ return x.slice(); }); return c; }
function addEl(obj){
  var key = null;
  commit(function(L){ L.n = (L.n || 0) + 1; key = 'u' + L.n; L.add.push({k: key, e: obj}); }, true);
  sel = key; sset = [key]; rerender(); paint();
}

/* ------------------------------------------------------------------ Draw (pen and click-to-place shape) and Edit points (2026-10-15) */
var dxs = null, dxp = null, dxRaf = 0;
function dxR(v){ return Math.round(v * 10) / 10; }
function dxOv(){ return ensureOv(); }
function dxRdp(a, eps){ if (a.length < 3) return a; var x1 = a[0][0], y1 = a[0][1], x2 = a[a.length - 1][0], y2 = a[a.length - 1][1], ddx = x2 - x1, ddy = y2 - y1, L = Math.sqrt(ddx * ddx + ddy * ddy), mi = 0, md = 0, i, d; for (i = 1; i < a.length - 1; i++) { d = L ? Math.abs(ddy * a[i][0] - ddx * a[i][1] + x2 * y1 - y2 * x1) / L : Math.sqrt(Math.pow(a[i][0] - x1, 2) + Math.pow(a[i][1] - y1, 2)); if (d > md) { md = d; mi = i; } } if (md > eps) { var l = dxRdp(a.slice(0, mi + 1), eps), r = dxRdp(a.slice(mi), eps); return l.slice(0, -1).concat(r); } return [a[0], a[a.length - 1]]; }
function dxSmooth(raw){
var p = dxRdp(raw, 1.2), d = [['M', dxR(p[0][0]), dxR(p[0][1])]], i;
if (p.length < 2) { d.push(['L', dxR(p[0][0] + 0.6), dxR(p[0][1] + 0.6)]); return d; }
if (p.length === 2) { d.push(['L', dxR(p[1][0]), dxR(p[1][1])]); return d; }
for (i = 0; i < p.length - 1; i++) { var p0 = p[i - 1] || p[i], p1 = p[i], p2 = p[i + 1], p3 = p[i + 2] || p2; d.push(['C', dxR(p1[0] + (p2[0] - p0[0]) / 6), dxR(p1[1] + (p2[1] - p0[1]) / 6), dxR(p2[0] - (p3[0] - p1[0]) / 6), dxR(p2[1] - (p3[1] - p1[1]) / 6), dxR(p2[0]), dxR(p2[1])]); }
return d;
}
function dxCols(){ var P = palOf() || {}, a = [hex(P.accent), hex(P.accent2), hex(P.title || P.text), '#FFFFFF', '#111827', '#EF4444', '#F59E0B', '#10B981', '#3B82F6'], o = []; a.forEach(function(c){ if (c && o.indexOf(c) < 0) o.push(c); }); return o.slice(0, 8); }
function dxBar(){
var h = '<button type="button" data-x="pen" class="' + (dxs.mode === 'pen' ? 'on' : '') + '" title="Draw freehand: drag on the slide">&#9998; Pen</button>'
+ '<button type="button" data-x="poly" class="' + (dxs.mode === 'poly' ? 'on' : '') + '" title="Click to place points; click the first point to close a filled shape">&#11040; Shape</button><i class="sep"></i>';
dxCols().forEach(function(c){ h += '<button type="button" data-x="c" data-c="' + c + '" class="sw' + (c === dxs.col ? ' on' : '') + '" style="background:' + c + '" title="' + c + '"></button>'; });
h += '<i class="sep"></i>';
[[2, 'Thin'], [4, 'Medium'], [8, 'Thick']].forEach(function(w){ h += '<button type="button" data-x="w" data-w="' + w[0] + '" class="lw' + (dxs.lw === w[0] ? ' on' : '') + '" title="' + w[1] + ' line"><span style="height:' + Math.max(2, w[0] / 1.5) + 'px"></span></button>'; });
h += '<i class="sep"></i><button type="button" data-x="done" class="done">Done</button>';
h += '<div class="tip">' + (dxs.mode === 'pen' ? 'Drag on the slide to draw. Each stroke becomes its own element.' : 'Click to add points. Click the first point to close a filled shape. Double-click or Enter finishes an open line. Backspace removes the last point.') + '</div>';
return h;
}
function dxPaint(){ if (!dxs) return; dxs.bar.innerHTML = dxBar(); dxPrev(); }
function dxPrev(){
if (!dxs) return; var s = '';
if (dxs.pts && dxs.pts.length) s += '<polyline points="' + dxs.pts.map(function(p){ return p[0] + ',' + p[1]; }).join(' ') + '" fill="none" stroke="' + dxs.col + '" stroke-width="' + dxs.lw + '" stroke-linecap="round" stroke-linejoin="round"/>';
if (dxs.poly && dxs.poly.length) {
var all = dxs.poly.concat(dxs.hov ? [dxs.hov] : []);
s += '<polyline points="' + all.map(function(p){ return p[0] + ',' + p[1]; }).join(' ') + '" fill="none" stroke="' + dxs.col + '" stroke-width="' + Math.max(2, dxs.lw) + '" stroke-linejoin="round"/>';
dxs.poly.forEach(function(p, i){ s += '<circle cx="' + p[0] + '" cy="' + p[1] + '" r="' + (i === 0 && dxs.poly.length > 2 ? 9 : 5) + '" fill="#fff" stroke="#3B82F6" stroke-width="2"/>'; });
}
dxs.svg.innerHTML = s;
}
function startDraw(){
stopPts(); if (dxs) return;
var o = dxOv(); if (!o) return;
pmode = '';
var P = palOf() || {};
dxs = {mode: 'pen', col: hex(P.accent) || '#3B82F6', lw: 4, pts: null, poly: null, hov: null};
var lay = document.createElement('div'); lay.className = 'dsx-draw';
lay.innerHTML = '<svg viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none"></svg><div class="dsx-bar"></div>';
o.appendChild(lay); dxs.el = lay; dxs.svg = lay.querySelector('svg'); dxs.bar = lay.querySelector('.dsx-bar');
document.documentElement.classList.add('dsx-drawing');
lay.addEventListener('pointerdown', dxDown); lay.addEventListener('pointermove', dxMove); lay.addEventListener('pointerup', dxUp); lay.addEventListener('dblclick', dxDbl);
dxs.bar.addEventListener('pointerdown', function(ev){ ev.stopPropagation(); });
dxs.bar.addEventListener('click', dxBarClick);
dxPaint(); paint(); dxWatch();
}
function stopDraw(){ if (!dxs) return; var d0 = dxs; if (d0.poly && d0.poly.length > 1) dxFinishPoly(false); if (d0.el && d0.el.parentNode) d0.el.parentNode.removeChild(d0.el); dxs = null; document.documentElement.classList.remove('dsx-drawing'); paint(); sync(); }
function dxBarClick(ev){
ev.preventDefault(); ev.stopPropagation(); var b = ev.target.closest ? ev.target.closest('[data-x]') : null; if (!b || !dxs) return;
var a = b.getAttribute('data-x');
if (a === 'done') { stopDraw(); return; }
if (a === 'pen' || a === 'poly') { if (dxs.poly && dxs.poly.length > 1) dxFinishPoly(false); dxs.poly = null; dxs.mode = a; }
if (a === 'c') dxs.col = b.getAttribute('data-c');
if (a === 'w') dxs.lw = +b.getAttribute('data-w');
dxPaint();
}
function dxIn(ev){ var p = toSlide(ev); return [dxR(Math.max(-40, Math.min(W + 40, p.x))), dxR(Math.max(-40, Math.min(H + 40, p.y)))]; }
function dxDown(ev){
if (!dxs || ev.button !== 0 || (ev.target.closest && ev.target.closest('.dsx-bar'))) return;
ev.preventDefault(); ev.stopPropagation();
var p = dxIn(ev);
if (dxs.mode === 'pen') { dxs.pts = [p]; try { dxs.el.setPointerCapture(ev.pointerId); } catch (x) {} dxPrev(); return; }
if (!dxs.poly) dxs.poly = [];
var f = dxs.poly[0];
if (f && dxs.poly.length > 2 && Math.abs(f[0] - p[0]) < 12 && Math.abs(f[1] - p[1]) < 12) { dxFinishPoly(true); return; }
var l = dxs.poly[dxs.poly.length - 1]; if (!l || Math.abs(l[0] - p[0]) > 1 || Math.abs(l[1] - p[1]) > 1) dxs.poly.push(p);
dxPrev();
}
function dxMove(ev){
if (!dxs) return; var p = dxIn(ev);
if (dxs.mode === 'pen' && dxs.pts) { var l = dxs.pts[dxs.pts.length - 1]; if (Math.abs(l[0] - p[0]) + Math.abs(l[1] - p[1]) >= 2) { dxs.pts.push(p); dxPrev(); } return; }
if (dxs.mode === 'poly' && dxs.poly && dxs.poly.length) { dxs.hov = p; dxPrev(); }
}
function dxUp(){
if (!dxs || dxs.mode !== 'pen' || !dxs.pts) return;
var raw = dxs.pts; dxs.pts = null; dxPrev();
addEl({t: 'path', d: dxSmooth(raw), stroke: dxs.col, lw: dxs.lw});
dxEnsure();
}
function dxDbl(ev){ if (!dxs || dxs.mode !== 'poly') return; ev.preventDefault(); ev.stopPropagation(); dxFinishPoly(false); }
function dxFinishPoly(close){
var a = dxs.poly || []; dxs.poly = null; dxs.hov = null; dxPrev();
if (a.length < 2) return;
var d = a.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; });
if (close && a.length > 2) { d.push(['Z']); addEl({t: 'path', d: d, fill: dxs.col}); }
else addEl({t: 'path', d: d, stroke: dxs.col, lw: dxs.lw});
dxEnsure();
}
function dxEnsure(){ var o = dxOv(); if (!o) return; if (dxs && dxs.el.parentNode !== o) o.appendChild(dxs.el); if (dxp && dxp.el.parentNode !== o) o.appendChild(dxp.el); }
function dxWatch(){
if (dxRaf) return;
(function loop(){
if (!dxs && !dxp) { dxRaf = 0; return; }
if (!on()) { stopDraw(); stopPts(); dxRaf = 0; return; }
dxEnsure();
if (dxp && !dxp.drag && (sel !== dxp.k || curIdx() !== dxp.i || !elByKey(dxp.k))) stopPts(); else if (dxp && !dxp.drag) ptsPlace();
dxRaf = requestAnimationFrame(loop);
})();
}
/* Edit points: any shape, box, circle, line or drawn shape becomes a drawn shape whose points can be dragged */
function ptsOK(e){ return !!e && sset.length <= 1 && !e._png && !isLocked(e._dk) && /^(path|line|shape|rect|ell)$/.test(e.t); }
function ptsBtn(e){ return ptsOK(e) ? btn('editpts', '&#10022; Edit points', 'Drag the points of this shape. Double-click the outline to add a point; double-click a point (or click it and press Delete) to remove it. Esc when done.', '', !!dxp) : ''; }
function rotP(x, y, f){ if (!f.rot) return [x, y]; var cx = f.x + f.w / 2, cy = f.y + f.h / 2, r = f.rot * Math.PI / 180, c = Math.cos(r), s = Math.sin(r), ux = x - cx, uy = y - cy; return [cx + ux * c - uy * s, cy + ux * s + uy * c]; }
function asPath(e){
if (e.t === 'path') return (e.d || []).map(function(c){ return c.slice(); });
if (e.t === 'line') return [['M', +e.x1, +e.y1], ['L', +e.x2, +e.y2]];
var f = frameOf(e), w = Math.max(1, f.w), h = Math.max(1, f.h), box;
if (e.t === 'shape') { var tmp = {t: 'shape', prst: e.prst, sides: e.sides, r: e.r, w: w, h: h}; X.mkPd(tmp); if (tmp.pd) box = tmp.pd; else { box = []; (X.polyPts(tmp) || [[0, 0], [w, 0], [w, h], [0, h]]).forEach(function(p, i){ box.push([i ? 'L' : 'M', p[0], p[1]]); }); box.push(['Z']); } }
else if (e.t === 'ell') { var a = w / 2, b = h / 2, k = 0.5523; box = [['M', w, b], ['C', w, b + b * k, a + a * k, h, a, h], ['C', a - a * k, h, 0, b + b * k, 0, b], ['C', 0, b - b * k, a - a * k, 0, a, 0], ['C', a + a * k, 0, w, b - b * k, w, b], ['Z']]; }
else { var r = Math.min(+e.r || 0, w / 2, h / 2), q = r * 0.4477; box = r > 0 ? [['M', r, 0], ['L', w - r, 0], ['C', w - q, 0, w, q, w, r], ['L', w, h - r], ['C', w, h - q, w - q, h, w - r, h], ['L', r, h], ['C', q, h, 0, h - q, 0, h - r], ['L', 0, r], ['C', 0, q, q, 0, r, 0], ['Z']] : [['M', 0, 0], ['L', w, 0], ['L', w, h], ['L', 0, h], ['Z']]; }
return box.map(function(c){ var o = [c[0]]; for (var i = 1; i + 1 < c.length; i += 2) { var p = rotP(f.x + (+c[i]), f.y + (+c[i + 1]), f); o.push(dxR(p[0]), dxR(p[1])); } return o; });
}
function pathStyle(e){
var o = {};
if (e.t === 'path') { ['stroke', 'fill', 'lw', 'op', 'lop', 'keep'].forEach(function(k){ if (e[k] != null) o[k] = e[k]; }); return o; }
if (e.t === 'line') { o.stroke = hex(e.color) || '#111111'; o.lw = e.lw || 2; if (e.op != null) o.lop = e.op; return o; }
var fill = e.fill || (e.grad && e.grad.stops && e.grad.stops[0] && e.grad.stops[0][1]) || null;
if (fill && hex(fill)) o.fill = hex(fill);
if (e.line && hex(e.line)) { o.stroke = hex(e.line); o.lw = e.lw || 2; if (e.lop != null) o.lop = e.lop; }
if (e.op != null) o.op = e.op;
if (e.keep) o.keep = e.keep;
if (!o.fill && !o.stroke) { o.stroke = '#111111'; o.lw = 2; }
return o;
}
function ptsEntry(L, k){ for (var i = 0; i < L.add.length; i++) if (L.add[i].k === k) return L.add[i]; return null; }
function ptsClr(m){ if (m) { delete m.dx; delete m.dy; delete m.sx; delete m.sy; delete m.rot; } }
function startPts(key){
if (dxp) { stopPts(); return; }
stopDraw();
var e = elByKey(key); if (!ptsOK(e)) return;
var nk = key, d = asPath(e), st = pathStyle(e), lost = !!e.grad && e.t !== 'path', z = keyOrder();
commit(function(L){
var ent = ptsEntry(L, key), o = {t: 'path', d: d}, k;
for (k in st) o[k] = st[k];
if (ent && ent.e.t === 'path') { ent.e.d = d; ptsClr(L.m[key]); }
else if (ent) { ent.e = o; delete L.m[key]; }
else { (L.m[key] || (L.m[key] = {})).hide = true; L.n = (L.n || 0) + 1; nk = 'u' + L.n; L.add.push({k: nk, e: o}); var zi = z.indexOf(key); if (zi >= 0) { z.splice(zi + 1, 0, nk); L.z = z; } }
}, true);
sel = nk; sset = [nk]; rerender();
var o2 = dxOv(); if (!o2) return;
var box = document.createElement('div'); box.className = 'dsx-pts'; o2.appendChild(box);
dxp = {k: nk, i: curIdx(), el: box, act: -1, sig: '', drag: false};
document.documentElement.classList.add('dsx-ptmode');
box.addEventListener('pointerdown', ptsDown); box.addEventListener('dblclick', ptsDblPt);
paint(); ptsPlace(); dxWatch();
toast(lost ? 'The gradient became a solid colour so the shape can be reshaped (Undo brings it back). Drag the points; Esc when done.' : 'Drag the points. Double-click the outline to add a point. Esc when done.');
}
function stopPts(){ if (!dxp) return; var p0 = dxp; dxp = null; if (p0.el && p0.el.parentNode) p0.el.parentNode.removeChild(p0.el); document.documentElement.classList.remove('dsx-ptmode'); paint(); sync(); }
function ptsAnchors(d){
var out = [], i, start = -1;
for (i = 0; i < d.length; i++) {
var c = d[i]; if (c[0] === 'Z' || c.length < 3) continue; if (c[0] === 'M') start = i;
var x = +c[c.length - 2], y = +c[c.length - 1], nx = d[i + 1];
if (start >= 0 && i !== start && nx && nx[0] === 'Z') { var s0 = d[start]; if (Math.abs(+s0[1] - x) < 0.6 && Math.abs(+s0[2] - y) < 0.6) { out.push({i: i, x: x, y: y, dup: start}); continue; } }
out.push({i: i, x: x, y: y});
}
return out;
}
function ptsPlace(){
if (!dxp) return; var e = elByKey(dxp.k); if (!e || !e.d) return;
var sig = JSON.stringify(e.d) + '|' + dxp.act; if (sig === dxp.sig) return; dxp.sig = sig;
var h = '';
ptsAnchors(e.d).forEach(function(a){ if (a.dup != null) return; h += '<b data-pi="' + a.i + '" class="' + (a.i === dxp.act ? 'on' : '') + '" style="left:' + (a.x / W * 100) + '%;top:' + (a.y / H * 100) + '%"></b>'; });
dxp.el.innerHTML = h + '<div class="dsx-ptbar"><span>Edit points</span><button type="button" data-pb="done">Done</button></div>';
}
function ptsSave(k, d, quiet, tag){ commit(function(L){ var ent = ptsEntry(L, k); if (ent) { ent.e.d = d; ptsClr(L.m[k]); } }, quiet, tag); }
function ptsDown(ev){
if (!dxp || ev.button !== 0) return;
var t = ev.target;
if (t.closest && t.closest('[data-pb]')) { ev.preventDefault(); ev.stopPropagation(); stopPts(); return; }
var b = t.closest ? t.closest('[data-pi]') : null; if (!b) return;
ev.preventDefault(); ev.stopPropagation();
var i = +b.getAttribute('data-pi'), k = dxp.k, tag = 'pts' + Date.now(), e = elByKey(k); if (!e || !e.d) return;
var base = e.d.map(function(c){ return c.slice(); }), A = ptsAnchors(base), p0 = toSlide(ev), want = null, raf = 0;
dxp.act = i; dxp.drag = true;
function build(dx, dy){
var d = base.map(function(c){ return c.slice(); });
function mv(j){ var c = d[j]; if (!c) return; var n = c.length; c[n - 2] = dxR(+c[n - 2] + dx); c[n - 1] = dxR(+c[n - 1] + dy); if (c[0] === 'C') { c[3] = dxR(+c[3] + dx); c[4] = dxR(+c[4] + dy); } var nx = d[j + 1]; if (nx && nx[0] === 'C') { nx[1] = dxR(+nx[1] + dx); nx[2] = dxR(+nx[2] + dy); } }
mv(i); A.forEach(function(a){ if (a.dup === i) mv(a.i); });
return d;
}
function flush(){ raf = 0; if (!want || !dxp) return; ptsSave(k, build(want[0], want[1]), true, tag); rerender(); dxp.sig = ''; ptsPlace(); }
function mvh(e2){ var p = toSlide(e2); want = [p.x - p0.x, p.y - p0.y]; if (!raf) raf = requestAnimationFrame(flush); }
function up(){ window.removeEventListener('pointermove', mvh, true); window.removeEventListener('pointerup', up, true); if (raf) { cancelAnimationFrame(raf); flush(); } if (dxp) { dxp.drag = false; dxp.sig = ''; ptsPlace(); } paint(); }
window.addEventListener('pointermove', mvh, true); window.addEventListener('pointerup', up, true);
want = [0, 0]; flush();
}
function ptsDblPt(ev){ var b = ev.target.closest ? ev.target.closest('[data-pi]') : null; if (!b || !dxp) return; ev.preventDefault(); ev.stopPropagation(); ptsDel(+b.getAttribute('data-pi')); }
function ptsDel(i){
var k = dxp.k, e = elByKey(k); if (!e || !e.d) return;
var d = e.d.map(function(c){ return c.slice(); }), closed = d.some(function(c){ return c[0] === 'Z'; }), A = ptsAnchors(d).filter(function(a){ return a.dup == null; });
if (A.length <= (closed ? 3 : 2)) { toast('A shape needs at least ' + (closed ? 3 : 2) + ' points.', 'error'); return; }
var c = d[i]; if (!c || c[0] === 'Z') return;
if (c[0] === 'M') {
var nx = d[i + 1]; if (!nx || nx[0] === 'Z' || nx[0] === 'M') return;
var old = [+c[1], +c[2]], nm = [+nx[nx.length - 2], +nx[nx.length - 1]];
d[i + 1] = ['M', nm[0], nm[1]]; d.splice(i, 1);
for (var j = i + 1; j < d.length && d[j][0] !== 'M'; j++) { var q = d[j], n2 = q.length; if (q[0] !== 'Z' && n2 >= 3 && d[j + 1] && d[j + 1][0] === 'Z' && Math.abs(+q[n2 - 2] - old[0]) < 0.6 && Math.abs(+q[n2 - 1] - old[1]) < 0.6) { q[n2 - 2] = nm[0]; q[n2 - 1] = nm[1]; } }
} else d.splice(i, 1);
ptsSave(k, d); dxp.act = -1; dxp.sig = ''; ptsPlace();
}
function ptsAdd(j, t){
var k = dxp.k, e = elByKey(k); if (!e || !e.d) return;
var d = e.d.map(function(c){ return c.slice(); }), prev = null, start = null, i, c;
for (i = 0; i < j; i++) { c = d[i]; if (c[0] === 'M') { start = [+c[1], +c[2]]; prev = start; } else if (c[0] === 'Z') prev = start; else prev = [+c[c.length - 2], +c[c.length - 1]]; }
c = d[j]; if (!prev) return;
function Lp(a, b){ return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t]; }
if (c[0] === 'C') {
var p1 = [+c[1], +c[2]], p2 = [+c[3], +c[4]], p3 = [+c[5], +c[6]], a = Lp(prev, p1), bq = Lp(p1, p2), cc = Lp(p2, p3), ab = Lp(a, bq), bc = Lp(bq, cc), m = Lp(ab, bc);
d.splice(j, 1, ['C', dxR(a[0]), dxR(a[1]), dxR(ab[0]), dxR(ab[1]), dxR(m[0]), dxR(m[1])], ['C', dxR(bc[0]), dxR(bc[1]), dxR(cc[0]), dxR(cc[1]), dxR(p3[0]), dxR(p3[1])]);
} else { var end = c[0] === 'Z' ? start : [+c[c.length - 2], +c[c.length - 1]], mp = Lp(prev, end); d.splice(j, 0, ['L', dxR(mp[0]), dxR(mp[1])]); }
ptsSave(k, d); dxp.act = j; dxp.sig = ''; ptsPlace();
}
window.addEventListener('dblclick', function(ev){
if (!dxp || (ev.target.closest && ev.target.closest('.dsx-pts'))) return;
var e = elByKey(dxp.k); if (!e || !e.d) return;
var p = toSlide(ev), best = null, d = e.d, i, start = null, prev = null;
function near(q, t, j){ var dd = Math.sqrt(Math.pow(q[0] - p.x, 2) + Math.pow(q[1] - p.y, 2)); if (!best || dd < best.dd) best = {dd: dd, j: j, t: t}; }
for (i = 0; i < d.length; i++) {
var c = d[i], s;
if (c[0] === 'M') { start = [+c[1], +c[2]]; prev = start; continue; }
if (!prev) continue;
if (c[0] === 'Z') { for (s = 1; s < 20; s++) near([prev[0] + (start[0] - prev[0]) * s / 20, prev[1] + (start[1] - prev[1]) * s / 20], s / 20, i); prev = start; continue; }
var end = [+c[c.length - 2], +c[c.length - 1]];
for (s = 1; s < 40; s++) { var t = s / 40, m = 1 - t, q = c[0] === 'C' ? [m * m * m * prev[0] + 3 * m * m * t * c[1] + 3 * m * t * t * c[3] + t * t * t * end[0], m * m * m * prev[1] + 3 * m * m * t * c[2] + 3 * m * t * t * c[4] + t * t * t * end[1]] : [prev[0] + (end[0] - prev[0]) * t, prev[1] + (end[1] - prev[1]) * t]; near(q, t, i); }
prev = end;
}
if (!best || best.dd > 16) return;
ev.preventDefault(); ev.stopImmediatePropagation();
ptsAdd(best.j, best.t);
}, true);
window.addEventListener('keydown', function(ev){
if (!dxs && !dxp) return;
var t = ev.target; if (t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName || ''))) return;
var k = ev.key;
if (dxs) {
if (k === 'Escape') { if (dxs.poly && dxs.poly.length) { dxs.poly = null; dxs.hov = null; dxPrev(); } else stopDraw(); }
else if (k === 'Enter') { if (dxs.poly) dxFinishPoly(false); else return; }
else if ((k === 'Backspace' || k === 'Delete') && dxs.poly && dxs.poly.length) { dxs.poly.pop(); dxPrev(); }
else return;
} else {
if (k === 'Escape' || k === 'Enter') stopPts();
else if ((k === 'Backspace' || k === 'Delete') && dxp.act >= 0) ptsDel(dxp.act);
else return;
}
ev.preventDefault(); ev.stopImmediatePropagation();
}, true);

/* "Loading your image..." over a shape with a picture inside: from the moment a new picture is chosen until it is showing */
var picWait = null, picFetch = 0;
(function(){ var f0 = window.fetch; if (!f0 || f0.__dsPicW) return; var w = function(input){ var url = typeof input === 'string' ? input : ((input && input.url) || ''); var p = f0.apply(this, arguments); if (picWait && /(fetch-image|slide-image|generate-image|ai-image|image-proxy|mkt|market|download)/i.test(url)) { picFetch++; var d = function(){ picFetch = Math.max(0, picFetch - 1); }; p.then(d, d); } return p; }; w.__dsPicW = 1; window.fetch = w; })();
document.addEventListener('click', function(ev){ if (picWait && ev.target && ev.target.closest && ev.target.closest('.ds-mkt-pick-item,#vp-img-genbtn')) picWait.chose = Date.now(); }, true);
document.addEventListener('change', function(ev){ if (picWait && ev.target && ev.target.type === 'file' && ev.target.files && ev.target.files.length) picWait.chose = Date.now(); }, true);
function picShown(id){ var el = document.getElementById(id); if (!el) return false; var cs = getComputedStyle(el); return cs.display !== 'none' && cs.visibility !== 'hidden'; }
function picPickerOpen(){ return picShown('vp-img-option-modal') || picShown('vp-imgmod') || picShown('ds-mkt-pick-overlay'); }
function picStart(k, mode){ var e = elByKey(k); if (!e || !e.pimg) return; picWait = {k: k, i: curIdx(), mode: mode, old: mode === 'slide' ? ((curSlide() || {}).image_data || '') : e.pimg.src, t0: Date.now(), closedAt: 0, chose: 0, busyAt: 0, fresh: '', dec: 0}; setTimeout(picLoop, 300); }
function picStop(){ picWait = null; picShow(false); }
function picShow(onn){
var o = ov, d = o && o.querySelector('.dsel-picload');
if (!onn || !picWait) { if (d) d.style.display = 'none'; return; }
var e = elByKey(picWait.k); if (!e || !o) return;
var f = frameOf(e);
if (!d) { d = document.createElement('div'); d.className = 'dsel-picload'; d.innerHTML = '<i></i><span>Loading your image\u2026</span>'; o.appendChild(d); }
d.style.display = 'flex'; d.style.left = (f.x / W * 100) + '%'; d.style.top = (f.y / H * 100) + '%'; d.style.width = (f.w / W * 100) + '%'; d.style.height = (f.h / H * 100) + '%';
d.style.transform = f.rot ? 'rotate(' + f.rot + 'deg)' : '';
var poly = '', ok = e.pd && e.pd.length > 2 && e.w > 0 && e.h > 0;
if (ok) e.pd.forEach(function(q){ if (!ok) return; if (q[0] === 'Z') return; if (q[0] !== 'M' && q[0] !== 'L') { ok = false; return; } poly += (poly ? ',' : '') + (q[1] / e.w * 100).toFixed(2) + '% ' + (q[2] / e.h * 100).toFixed(2) + '%'; });
d.style.clipPath = ok && poly ? 'polygon(' + poly + ')' : '';
}
function picLoop(){
var w = picWait; if (!w) return;
var now = Date.now();
if (!on() || curIdx() !== w.i || now - w.t0 > 120000) { picStop(); return; }
if (picPickerOpen()) { w.closedAt = 0; picShow(false); setTimeout(picLoop, 250); return; }
if (!w.closedAt) w.closedAt = now;
var sl = curSlide() || {}, e = elByKey(w.k), cur = w.mode === 'slide' ? (sl.image_data || '') : (e && e.pimg ? e.pimg.src : '');
if (cur && cur !== w.old) {
if (!w.fresh) { w.fresh = cur; var im = new Image(); im.onload = im.onerror = function(){ w.dec = 1; }; im.src = cur; }
picShow(true);
var shown = e && e.pimg && e.pimg.src === w.fresh && (+e.pimg.iw > 0 || e.pimg.st);
if (w.dec && shown) { if (!w.doneAt) w.doneAt = now; if (now - w.doneAt > 400) { picStop(); return; } }
} else {
if (picFetch > 0 || sl.image_loading || (w.chose && now - w.chose < 60000)) w.busyAt = now;
if (w.busyAt) picShow(true);
if (now - Math.max(w.closedAt, w.busyAt || 0) > (w.busyAt ? 4000 : 1500)) { picStop(); return; }
}
setTimeout(picLoop, 250);
}
function hasPicFill(e){ return !!(e && e.t !== 'img' && e.t !== 'png' && e.pimg && e.pimg.src); }
/* Crop for a picture inside a shape: drag the picture to move it, scroll (or the Zoom slider) to zoom; the whole picture shows faintly around the shape while cropping */
function pfGeo(e){
  var p = e && e.pimg; if (!p || !p.src || p.st) return null;
  var f = frameOf(e), iw = +p.iw || 0, ih = +p.ih || 0; if (!(iw > 0 && ih > 0 && f.w > 0 && f.h > 0)) return null;
  var sc = Math.max(f.w / iw, f.h / ih) * Math.max(1, +p.z || 1), sw = iw * sc, sh = ih * sc;
  var px = p.px != null ? +p.px : 50, py = p.py != null ? +p.py : 50;
  return {f: f, sw: sw, sh: sh, ox: sw - f.w, oy: sh - f.h, ix: (f.w - sw) * px / 100, iy: (f.h - sh) * py / 100};
}
function pfCropOn(){ var e = sel && sset.length === 1 ? elByKey(sel) : null; return pmode === 'pfcrop' && hasPicFill(e) && !e.pimg.st && !e.pimg.gb ? e : null; }
function pfGhost(){
  if (!ov) return;
  var g = ov.querySelector('.dsel-pfg'), e = pfCropOn(), q = e && pfGeo(e);
  document.documentElement.classList.toggle('dsel-pfc-on', !!e);
  var cb1 = ov.querySelector('.dsel-cropbtn'), sp1 = cb1 && cb1.querySelector('span'); if (sp1) sp1.textContent = (e && cb1.getAttribute('data-k') === sel) ? 'Done' : 'Crop';
  if (!q) { if (g) g.style.display = 'none'; return; }
  if (!g) { g = document.createElement('div'); g.className = 'dsel-pfg'; ov.insertBefore(g, ov.firstChild); }
  g.style.display = 'block';
  g.style.left = ((q.f.x + q.ix) / W * 100) + '%'; g.style.top = ((q.f.y + q.iy) / H * 100) + '%';
  g.style.width = (q.sw / W * 100) + '%'; g.style.height = (q.sh / H * 100) + '%';
  var u = String(e.pimg.src).replace(/['"()\\<>\s]/g, '');
  if (g.getAttribute('data-u') !== String(u.length) + u.slice(-24)) { g.style.backgroundImage = "url('" + u + "')"; g.setAttribute('data-u', String(u.length) + u.slice(-24)); }
}
function pfCropBtn(e, f, k){
  var cb = ov && ov.querySelector('.dsel-cropbtn'); if (!cb) return;
  if (!e || !hasPicFill(e) || e.pimg.st) { cb.style.display = 'none'; return; }
  cb.setAttribute('data-k', k); cb.innerHTML = IC.crop + '<span>' + (pmode === 'pfcrop' && sel === k ? 'Done' : 'Crop') + '</span>';
  cb.style.display = 'flex'; cb.style.left = ((f.x + f.w) / W * 100) + '%'; cb.style.top = (f.y / H * 100) + '%';
}
function pfInside(ev, e){ var pt = toSlide(ev), f = frameOf(e); return pt.x >= f.x && pt.x <= f.x + f.w && pt.y >= f.y && pt.y <= f.y + f.h; }
var pfDrag = null;
window.addEventListener('pointerdown', function(ev){
  if (ev.button !== 0) return;
  var e = pfCropOn(); if (!e) return;
  var t = ev.target; if (t.closest && t.closest('button,.dsel-h,.dsel-rot,.dsel-sd,.dsel-rc,.dsel-mini,#dsce-bar,#dsel-panel,[data-p],[data-a]')) return;
  if (!mainEl() || !pfInside(ev, e)) return;
  var q = pfGeo(e); if (!q) return;
  ev.preventDefault(); ev.stopPropagation();
  var rc = mainEl().getBoundingClientRect();
  pfDrag = {x0: ev.clientX, y0: ev.clientY, sc: W / rc.width, q: q, px: e.pimg.px != null ? +e.pimg.px : 50, py: e.pimg.py != null ? +e.pimg.py : 50, raf: 0, last: null};
  document.documentElement.classList.add('dsel-pfc-drag');
}, true);
window.addEventListener('pointermove', function(ev){
  if (!pfDrag) return;
  var d = pfDrag, e = pfCropOn(); if (!e) { pfDrag = null; return; }
  var dx = (ev.clientX - d.x0) * d.sc, dy = (ev.clientY - d.y0) * d.sc;
  var nx = d.q.ox > 0.5 ? Math.max(0, Math.min(100, d.px - dx / d.q.ox * 100)) : d.px;
  var ny = d.q.oy > 0.5 ? Math.max(0, Math.min(100, d.py - dy / d.q.oy * 100)) : d.py;
  d.last = [nx, ny];
  if (d.raf) return;
  d.raf = requestAnimationFrame(function(){
    d.raf = 0; var e2 = pfCropOn(); if (!e2 || !d.last) return;
    var np = JSON.parse(JSON.stringify(e2.pimg)); np.px = Math.round(d.last[0] * 10) / 10; np.py = Math.round(d.last[1] * 10) / 10;
    quietSet({pimg: np}, sel + '|pfdrag'); pfGhost();
  });
}, true);
window.addEventListener('pointerup', function(){
  if (!pfDrag) return;
  pfDrag = null; document.documentElement.classList.remove('dsel-pfc-drag');
  setTimeout(function(){ paint(); pfGhost(); }, 30); /* the sliders show the new place */
}, true);
var pfWheelT = 0;
window.addEventListener('wheel', function(ev){
  var e = pfCropOn(); if (!e || !mainEl() || !pfInside(ev, e)) return;
  ev.preventDefault(); ev.stopPropagation();
  var np = JSON.parse(JSON.stringify(e.pimg)), z = Math.max(1, +np.z || 1);
  z = Math.max(1, Math.min(4, z * (ev.deltaY < 0 ? 1.1 : 1 / 1.1))); np.z = Math.round(z * 100) / 100;
  quietSet({pimg: np}, sel + '|pfz'); pfGhost();
  clearTimeout(pfWheelT); pfWheelT = setTimeout(function(){ paint(); pfGhost(); }, 250);
}, {capture: true, passive: false});
document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape' && pmode === 'pfcrop') { pmode = ''; paint(); pfGhost(); } });
var paintPf0 = paint; paint = function(){ var r = paintPf0.apply(this, arguments); try { pfGhost(); } catch (x) {} return r; };

function dupMany(keys){
  var imgs = keys.map(elByKey).filter(function(e){ return e && e.t === 'img'; });
  if (imgs.length) { dupImg(imgs[0]); if (keys.length === 1) return; }
  var grouped = oneGroup(), list = keys.map(elByKey).filter(function(e){ return e && !/^(chart|logo|pic|img)$/.test(e.t); });
  if (!list.length) { toast('This kind of element cannot be duplicated.', 'error'); return; }
  var z = keyOrder(); list.sort(function(a, b){ return z.indexOf(a._dk) - z.indexOf(b._dk); });
  var made = [];
  commit(function(L, s){
    list.forEach(function(e){
      if (e._png) { var pa = pngList(s); if (pa.length >= 6) { toast('A slide can hold 6 PNG pictures.', 'error'); return; } var pc2 = {src: e._png.src, x: e._png.x + 24, y: e._png.y + 24, w: e._png.w, h: e._png.h}; if (e._png.hold) { pc2.hold = 1; pc2.o = e._png.o || ''; pc2.rr = e._png.rr || 0; pc2.pos = e._png.pos || {x: 50, y: 50}; if (e._png.op != null) pc2.op = e._png.op; if (e._png.zm) pc2.zm = e._png.zm; } pa.push(pc2); made.push('png' + (pa.length - 1)); return; }
      var c = plain(e), d = 24;
      if (c.t === 'line') { c.x1 += d; c.x2 += d; c.y1 += d; c.y2 += d; }
      else if (c.t === 'path') c.d = c.d.map(function(x){ var n = [x[0]]; for (var i = 1; i < x.length; i++) n.push(x[i] + d); return n; });
      else { c.x += d; c.y += d; }
      L.n = (L.n || 0) + 1; var key = 'u' + L.n; L.add.push({k: key, e: c}); made.push(key);
    });
    if (grouped && made.length > 1) { L.g = L.g || {}; L.gn = (L.gn || 0) + 1; L.g['g' + L.gn] = made.slice(); }
  }, true);
  if (!made.length) { rerender(); paint(); return; }
  sset = made.slice(); sel = made[made.length - 1]; rerender(); paint();
}
function dupEl(key){ dupMany([key]); }
function resetEl(key){ commit(function(L){ delete L.m[key]; }); }
/* Reset goes back to how the deck was when it was opened this time (a deck that was saved, closed and opened again
   goes back to that saved version). The picture of it is taken at the first click or key after opening. */
var snap0 = null, needSnap = true, snapId = '';
function deepClone(v){ if (Array.isArray(v)) return v.map(deepClone); if (v && typeof v === 'object') { var o = {}; for (var k in v) { if (typeof v[k] !== 'function') o[k] = deepClone(v[k]); } return o; } return v; }
function takeSnap(){
  var d = deck(); if (!d || !d.slides) return;
  var ch = {}, sc = (window.S && S.choices) || {}; for (var k in sc) ch[k] = sc[k];
  snap0 = {slides: d.slides.map(deepClone), style: d.style ? deepClone(d.style) : null, choices: ch};
  needSnap = false; snapId = (window.G && G.pptId) || '';
}
function snapCheck(){ if (needSnap && on()) takeSnap(); }
window.addEventListener('pointerdown', snapCheck, true);
window.addEventListener('keydown', snapCheck, true);
function resetSlide(idx){
  var d = deck(), s = d && d.slides[idx], src = snap0 && snap0.slides[idx]; if (!s || !src) return false;
  decks().forEach(function(dk){
    var t = dk.slides[idx]; if (!t) return;
    for (var k in t) { if (!(k in src)) delete t[k]; }
    for (var k2 in src) t[k2] = deepClone(src[k2]);
  });
  mirror(idx);
  if (window.S) {
    if (Array.isArray(S.choices)) { var o = {}; for (var q in S.choices) o[q] = S.choices[q]; S.choices = o; }
    S.choices = S.choices || {};
    var was = snap0.choices['slide_' + idx]; if (was) S.choices['slide_' + idx] = was; else delete S.choices['slide_' + idx];
    try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch (x) {}
  }
  undo[idx] = []; redo[idx] = [];
  return true;
}
function doReset(all){
  var d = deck(); if (!d) return;
  if (!snap0) { toast('Nothing has been changed since this presentation was opened.', 'error'); return; }
  var i = curIdx(), n = 0, skipped = 0;
  if (all) {
    d.slides.forEach(function(s, j){ if (resetSlide(j)) n++; else skipped++; });
    decks().forEach(function(dk){ if (snap0.style) dk.style = deepClone(snap0.style); else delete dk.style; });
  } else if (!resetSlide(i)) { toast('This slide was added after you opened the presentation, so there is nothing to go back to.', 'error'); return; }
  sel = null; sset = []; pmode = ''; pick = null; pngSeen = null;
  try { if (D.clearFit) D.clearFit(); } catch (x) {}
  try { if (D.refreshEditor) D.refreshEditor(); else rerender(); } catch (x2) {}
  try { if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs(); } catch (x3) {}
  paint();
  toast(all ? 'The presentation is back to how it was when you opened it' + (skipped ? ' (slides added since then were left as they are)' : '') + '. Click Save to keep it this way.' : 'Slide ' + (i + 1) + ' is back to how it was when you opened the presentation. Click Save to keep it this way.');
}
var warnEl = null;
function closeWarn(){ if (warnEl) { warnEl.remove(); warnEl = null; } }
function showWarn(){
  var fs = fsEl(); if (!fs) return; closeWarn(); snapCheck();
  var n = (deck() && deck().slides.length) || 0, i = curIdx();
  warnEl = document.createElement('div'); warnEl.id = 'dsel-warn';
  warnEl.innerHTML = '<div class="dsel-wbox" role="alertdialog" aria-modal="true"><div class="dsel-wic">' + svg('<path d="M12 3L2 20h20L12 3z"/><path d="M12 10v4.5M12 17.4v.2"/>') + '</div>'
    + '<h3>Reset to how it was when you opened it?</h3>'
    + '<p>Everything you changed since you opened this presentation is undone: texts, positions, sizes, colours, effects, added elements, pictures, layouts, groups, locks and layer order.</p>'
    + '<p class="dsel-wsm">A new presentation goes back to how it was generated. One that you saved, closed and opened again goes back to that saved version. Slides you added or removed stay as they are. The reset itself is kept only after you click Save.</p>'
    + '<div class="dsel-wbt"><button type="button" data-w="cancel">Cancel</button><button type="button" class="dsel-wd" data-w="slide">Reset slide ' + (i + 1) + '</button><button type="button" class="dsel-wd dsel-wall" data-w="all">Reset all ' + n + ' slides</button></div></div>';
  warnEl.addEventListener('click', function(ev){
    var b = ev.target.closest ? ev.target.closest('[data-w]') : null;
    if (!b) { if (ev.target === warnEl) closeWarn(); return; }
    var a = b.getAttribute('data-w'); closeWarn();
    if (a === 'slide') doReset(false); else if (a === 'all') doReset(true);
  });
  fs.appendChild(warnEl);
  var cb = warnEl.querySelector('[data-w="cancel"]'); if (cb) cb.focus();
}
/* the deck name in the top bar can be changed: click it, or the pencil beside it */
function wireTitle(){
  var t = $('dsp-etitle'); if (!t || t.__dselRen) return; t.__dselRen = 1;
  var pb = document.createElement('button'); pb.type = 'button'; pb.id = 'dsel-ren'; pb.title = 'Rename this presentation'; pb.innerHTML = IC.pen;
  t.parentNode.insertBefore(pb, t.nextSibling);
  t.title = 'Click to rename'; t.classList.add('dsel-rent');
  function start(ev){
    if (ev) { ev.preventDefault(); ev.stopPropagation(); }
    if (t.querySelector('input')) return;
    var cur = String(t.textContent || '').trim(), inp = document.createElement('input'), done = false;
    inp.type = 'text'; inp.id = 'dsel-reni'; inp.maxLength = 120; inp.value = cur; inp.spellcheck = false;
    t.textContent = ''; t.appendChild(inp); inp.focus(); inp.select();
    function end(save){
      if (done) return; done = true;
      var v = String(inp.value || '').replace(/\s+/g, ' ').trim().slice(0, 120);
      if (!save || !v) v = cur;
      t.textContent = v;
      if (v !== cur) {
        try { if (window.G) G.pptTitle = v; } catch (x) {}
        decks().forEach(function(d){ d.title = v; });
        toast('Renamed to "' + v + '". Click Save to keep the new name.');
      }
    }
    inp.addEventListener('keydown', function(e){ e.stopPropagation(); if (e.key === 'Enter') { e.preventDefault(); end(true); } else if (e.key === 'Escape') { e.preventDefault(); end(false); } }, true);
    inp.addEventListener('blur', function(){ end(true); });
    ['click', 'mousedown', 'pointerdown', 'dblclick'].forEach(function(n){ inp.addEventListener(n, function(e){ e.stopPropagation(); }); });
  }
  t.addEventListener('click', start); pb.addEventListener('click', start);
}

/* ------------------------------------------------------------------ geometry on screen */
function toSlide(ev){ var r = mainEl().getBoundingClientRect(); return {x: (ev.clientX - r.left) / r.width * W, y: (ev.clientY - r.top) / r.height * H}; }
function nodesFor(key){ var m = mainEl(), a = []; if (!m) return a; if (isPng(key)) { var pn = m.querySelector('[style*="--ds-obj:' + curIdx() + '_' + key.slice(3) + '"]'); return pn ? [pn] : a; } var all = m.querySelectorAll('[data-dk]'); for (var i = 0; i < all.length; i++) { var k = all[i].getAttribute('data-dk'); if (k === key || k.indexOf(key + '~s') === 0) a.push(all[i]); } return a; }
function nodeFor(key){ var a = nodesFor(key); if (isPng(key)) return a[0] || null; for (var i = 0; i < a.length; i++) if (a[i].getAttribute('data-dk') === key) return a[i]; return null; }
/* what the user sees as the element: for text, the lines themselves rather than the whole text area */
function frameOf(e){
  var f = e._f || {x: e.x, y: e.y, w: e.w, h: e.h, rot: 0};
  f = {x: f.x, y: f.y, w: f.w, h: f.h, rot: f.rot || 0};
  if (e.t === 'text' && !f.rot) {
    var n = nodeFor(e._dk), inner = n && n.firstElementChild, m = mainEl();
    if (inner && m) { var a = inner.getBoundingClientRect(), b = m.getBoundingClientRect(); if (a.height > 1 && b.height > 1) {
			var top = a.top, bot = a.bottom;
			/* text that runs past its box (bigger line spacing, more lines): the frame covers all of it */
			try { var rg = document.createRange(); rg.selectNodeContents(inner); var tr = rg.getBoundingClientRect(); if (tr.height > a.height * 1.12 + 2) { top = Math.min(top, tr.top); bot = Math.max(bot, tr.bottom); } } catch (er) {}
			var y = (top - b.top) / b.height * H, h = (bot - top) / b.height * H; f.ty = f.y; f.y = y; f.h = Math.max(8, h); } }
  }
  return f;
}
var hitCx = null;
function pathHit(e, x, y){
  try {
    if (!hitCx) hitCx = document.createElement('canvas').getContext('2d');
    var p = new Path2D();
    if (e.t === 'line') { p.moveTo(e.x1, e.y1); p.lineTo(e.x2, e.y2); }
    else (e.d || []).forEach(function(c){ if (c[0] === 'M') p.moveTo(c[1], c[2]); else if (c[0] === 'L') p.lineTo(c[1], c[2]); else if (c[0] === 'C') p.bezierCurveTo(c[1], c[2], c[3], c[4], c[5], c[6]); else if (c[0] === 'Q') p.quadraticCurveTo(c[1], c[2], c[3], c[4]); else if (c[0] === 'Z') p.closePath(); });
    hitCx.lineWidth = Math.max(14, (e.lw || 1) + 10);
    if (hitCx.isPointInStroke(p, x, y)) return true;
    return !!(e.t === 'path' && e.fill && hitCx.isPointInPath(p, x, y));
  } catch (er) { var b = X.bbox(e); return x >= b.x - 6 && x <= b.x + b.w + 6 && y >= b.y - 6 && y <= b.y + b.h + 6; }
}
/* an element still where the design put it is cut off at the slide edge: the part outside is not shown, so it is not picked there */
function movedOut(e){
  if (e._png || e._add) return true;
  var L = X.layer(curSlide(), curVid(), false), o = L && L.m[String(e._dk).split('~s')[0]];
  return !!(o && (o.dx || o.dy || (o.sx && o.sx !== 1) || (o.sy && o.sy !== 1) || o.rot));
}
/* a soft glow: a round shape whose colour fades away to nothing at its edge */
function isGlow(e){
  var g = e && e.t === 'ell' && e.grad, st = g && g.type === 'radial' && g.stops;
  if (!st || !st.length) return false;
  var last = st[st.length - 1]; return last[2] != null && +last[2] <= 0.05;
}
function hitTest(pt){
  var a = els(), outside = pt.x < 0 || pt.y < 0 || pt.x > W || pt.y > H;
  for (var i = a.length - 1; i >= 0; i--) {
    var e = a[i];
    if (isLocked(e._dk)) continue;
    if (isGlow(e) && !e._add) continue; /* a soft background light (glow) is decoration: a click goes through it; pick it from Layers */                      /* a locked element is not picked on the slide */
    if (outside && !movedOut(e)) continue;
    if (e.t === 'line' || e.t === 'path') { if (pathHit(e, pt.x, pt.y)) return e; continue; }
    var f = frameOf(e), x = pt.x, y = pt.y;
    if (f.rot) { var cx = f.x + f.w / 2, cy = f.y + f.h / 2, r = -f.rot * Math.PI / 180, ux = x - cx, uy = y - cy; x = cx + ux * Math.cos(r) - uy * Math.sin(r); y = cy + ux * Math.sin(r) + uy * Math.cos(r); }
    var px = f.w < 12 ? (12 - f.w) / 2 : 0, py = f.h < 12 ? (12 - f.h) / 2 : 0;
    if (x < f.x - px || x > f.x + f.w + px || y < f.y - py || y > f.y + f.h + py) continue;
    var hollow = (e.t === 'rect' || e.t === 'ell' || e.t === 'shape') && !e.fill && !e.grad && !e.pimg; /* a picture fill is solid: picked anywhere inside */
    if (e.t === 'ell' && f.w > 16 && f.h > 16) {
      var nx = (x - (f.x + f.w / 2)) / (f.w / 2), ny = (y - (f.y + f.h / 2)) / (f.h / 2), dd = nx * nx + ny * ny;
      if (dd > (isGlow(e) ? 0.3 : 1.08)) continue;        /* a glow: only its visible middle */
      if (hollow) { var edge = 10 / Math.min(f.w, f.h) * 2; if (dd < (1 - edge) * (1 - edge)) continue; }
    } else if (hollow) {
      var t = 9; if (x > f.x + t && x < f.x + f.w - t && y > f.y + t && y < f.y + f.h - t) continue;
    }
    return e;
  }
  return null;
}

/* ------------------------------------------------------------------ selection frame */
var ov = null, fr = null, gv = null, gh = null;
function svg(d, fill){ return '<svg viewBox="0 0 24 24" width="16" height="16" fill="' + (fill ? 'currentColor' : 'none') + '" stroke="' + (fill ? 'none' : 'currentColor') + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none">' + d + '</svg>'; }
var IC = {
  move: svg('<circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/>', 1),
  del: svg('<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>'),
  dup: svg('<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>'),
  rst: svg('<path d="M4 4v6h6"/><path d="M5.5 15a7.5 7.5 0 1 0 1.2-8.3L4 10"/>'),
  lock: svg('<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>'),
  unlock: svg('<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/>'),
  chev: svg('<path d="M6 9l6 6 6-6"/>'),
  pen: svg('<path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/>'),
  pic: svg('<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M21 16l-5-5-8 8"/>'),
  crop: svg('<path d="M6 2v14a2 2 0 0 0 2 2h14"/><path d="M2 6h14a2 2 0 0 1 2 2v14"/>'),
  shd: svg('<rect x="4" y="4" width="12" height="12" rx="2"/><path d="M20 9v9a2 2 0 0 1-2 2H9"/>'),
  brush: svg('<path d="M4 5h13v5H4z"/><path d="M17 7.5h3v5h-9v3"/><rect x="9.5" y="15.5" width="3" height="5" rx="1"/>'),
  eye: svg('<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>'),
  op: svg('<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h6V3M9 9v6h6V9zM15 15v6M15 15h6M3 15h6v6" stroke-width="1.4"/>')
};
function ensureOv(){
  var m = mainEl(), outer = m && m.parentElement; if (!outer) return null;
  if (ov && ov.parentElement === outer) return ov;
  if (ov && ov.parentElement) ov.parentElement.removeChild(ov);
  ov = document.createElement('div'); ov.id = 'dsel-ov';
  ov.innerHTML = '<div class="dsel-g dsel-gv"></div><div class="dsel-g dsel-gh"></div><div class="dsel-mem"></div><div class="dsel-mq"></div><button type="button" class="dsel-imgbtn" style="display:none"></button><button type="button" class="dsel-imgbtn dsel-cropbtn" style="display:none" title="Move and zoom the picture inside this shape"></button>' +
    '<div class="dsel-fr"><i class="dsel-mv" data-h="mv" style="left:0;right:0;top:-5px;height:10px"></i><i class="dsel-mv" data-h="mv" style="left:0;right:0;bottom:-5px;height:10px"></i><i class="dsel-mv" data-h="mv" style="top:0;bottom:0;left:-5px;width:10px"></i><i class="dsel-mv" data-h="mv" style="top:0;bottom:0;right:-5px;width:10px"></i>' +
    ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'].map(function(h){ return '<b class="dsel-h dsel-h-' + h + '" data-h="' + h + '"></b>'; }).join('') +
    '<b class="dsel-rot" data-h="rot" title="Drag to rotate">&#8635;</b>' +
      '<b class="dsel-sd" data-h="sides" title="Drag up for more sides, down for fewer"></b><span class="dsel-sdt"></span>' +
      '<b class="dsel-rc" data-h="corner" title="Drag inward to round the corners"></b>' +
    '<div class="dsel-mini"><button type="button" data-h="mv" title="Drag to move">' + IC.move + '</button><button type="button" data-m="del" title="Delete (or hide) this element">' + IC.del + '</button><button type="button" data-m="dup" title="Duplicate">' + IC.dup + '</button><button type="button" data-m="app" title="Apply this look to similar elements: on this slide, or on every slide">' + IC.brush + '</button><button type="button" data-m="rst" title="Put this element back as it was generated">' + IC.rst + '</button></div></div>';
  outer.appendChild(ov);
  fr = ov.querySelector('.dsel-fr'); gv = ov.querySelector('.dsel-gv'); gh = ov.querySelector('.dsel-gh');
  ov.addEventListener('pointerdown', onHandleDown, true);
  ov.addEventListener('click', function(e){
    var cb0 = e.target.closest ? e.target.closest('.dsel-cropbtn') : null;
if (cb0) { e.preventDefault(); e.stopPropagation(); var kk0 = cb0.getAttribute('data-k'); if (kk0 && sel !== kk0) select(kk0); openPanel('pfcrop'); return; }
var ib = e.target.closest ? e.target.closest('.dsel-imgbtn') : null;
    if (ib) { e.preventDefault(); e.stopPropagation(); openImg(ib.getAttribute('data-k')); return; }
    var b = e.target.closest ? e.target.closest('[data-m]') : null; if (!b || !sel) return;
    e.preventDefault(); e.stopPropagation();
    var a = b.getAttribute('data-m');
    if (a === 'del') removeMany(sset.slice()); else if (a === 'dup') dupMany(sset.slice()); else if (a === 'rst') resetEl(sel); else if (a === 'app') { var br = b.getBoundingClientRect(); applyMenu(br.left, br.bottom + 6); }
  });
  return ov;
}
function placeFrame(f){
  if (!fr) return;
  fr.style.left = (f.x / W * 100) + '%'; fr.style.top = (f.y / H * 100) + '%';
  fr.style.width = (f.w / W * 100) + '%'; fr.style.height = (f.h / H * 100) + '%';
  fr.style.transform = f.rot ? 'rotate(' + f.rot + 'deg)' : '';
  var mini = fr.querySelector('.dsel-mini'); if (mini) mini.className = 'dsel-mini' + (f.y < 54 ? ' dsel-mini-in' : '');
/* handles scale with the object on screen, so a small object is never hidden under them */
try { var pr = fr.parentNode ? fr.parentNode.getBoundingClientRect() : null, pw = pr && pr.width ? pr.width : W, ph = pr && pr.height ? pr.height : H, sp = Math.min(f.w / W * pw, f.h / H * ph), hs = Math.max(5, Math.min(11, Math.round(sp / 5))); fr.style.setProperty('--dsel-hs', hs + 'px'); fr.classList.toggle('dsel-tiny', sp < 44); } catch (x) {}
}
function canRotate(e){ return !/^(chart|ring|pic|png)$/.test(e.t); }
function sync(){
  if (!on()) { if (ov) ov.style.display = 'none'; return; }
  if (!ensureOv()) return;
  ov.style.display = '';
  markLocks();
  norm();
  var e = sel && elByKey(sel), mem = ov.querySelector('.dsel-mem');
  if (!e) { fr.style.display = 'none'; if (mem) mem.innerHTML = ''; return; }
  fr.style.display = '';
  if (sset.length > 1) {
    var a = selEls();
    fr.className = 'dsel-fr dsel-multi';
    placeFrame(bboxOf(a.map(frameOf)));
    if (mem) mem.innerHTML = a.map(function(x){ var f = frameOf(x); return '<i style="left:' + (f.x / W * 100) + '%;top:' + (f.y / H * 100) + '%;width:' + (f.w / W * 100) + '%;height:' + (f.h / H * 100) + '%;' + (f.rot ? 'transform:rotate(' + f.rot + 'deg);' : '') + '"></i>'; }).join('');
    return;
  }
  if (mem) mem.innerHTML = '';
  fr.className = 'dsel-fr dsel-t-' + (e._png && e._png.hold ? 'hold' : e.t) + (canRotate(e) ? '' : ' dsel-norot') + (e.t === 'text' ? ' dsel-wonly' : '') + (ovHas(sel) ? ' dsel-edited' : '') + (X.sidesOf(e) ? ' dsel-hassd' : '') + (cornerOK(e) ? ' dsel-hasrc' : ''); placeRc(e);
  placeFrame(frameOf(e));
}
function ovHas(key){ var o = ovOf(key); for (var k in o) return true; return false; }
function select(key){
  if (sel === key && sset.length <= 1) { sync(); return; }
  sel = key || null; sset = sel ? [sel] : [];
  sync(); paint();
}

/* ------------------------------------------------------------------ pointer: select, move, resize, rotate */
var pend = null, drag = null, eatClick = false, bypassImg = false;
function isUi(t){ return !!(t && t.closest && t.closest('#dsel-bar,#dsel-dock,#dsel-panel,#dsel-ov')); }
function live(nodes, fn){ for (var i = 0; i < nodes.length; i++) fn(nodes[i].style); }
function clearLive(nodes){ live(nodes, function(s){ s.translate = ''; s.rotate = ''; }); }
function blurText(){ try { var s = window.getSelection(); if (s) s.removeAllRanges(); if (document.activeElement && document.activeElement.isContentEditable) document.activeElement.blur(); } catch (x) {} }
function toggleKeys(keys){
  var all = keys.every(function(k){ return sset.indexOf(k) >= 0; });
  setSel(all ? sset.filter(function(k){ return keys.indexOf(k) < 0; }) : sset.concat(keys), all ? null : keys[keys.length - 1]);
}
var mq = null, hovK = null;
/* a picture opens the picker only from its small button (shown while the mouse is over it) or from "Change image" in the bar */
function openImg(k){
  /* a shape with a picture inside: the template photo opens the normal picture window, a picture the user put in opens its own picker */
  var pe0 = k && elByKey(k); if (pe0 && hasPicFill(pe0)) { if (sel !== k || sset.length !== 1) select(k); var sl0 = curSlide() || {}; if (sl0.image_data && sl0.image_data === pe0.pimg.src && typeof window.vpImgOptionOpen === 'function') { picStart(k, 'slide'); window.vpImgOptionOpen(curIdx()); return; } picStart(k, 'fill'); openFillPick(k); return; }
  var zn = k && nodeFor(k); if (!zn) return;
  if (sel !== k || sset.length !== 1) select(k);
  if (isPng(k)) { openPngPick(k); return; }
  var zz = (zn.classList && zn.classList.contains('dsp-vp-imgzone')) ? zn : zn.querySelector('.dsp-vp-imgzone'), si = zz ? parseInt(zz.getAttribute('data-slide-idx'), 10) : curIdx();
  if (isNaN(si)) si = curIdx();
  if (typeof window.vpImgOptionOpen === 'function') { window.vpImgOptionOpen(si); return; }
  bypassImg = true; try { (zn.querySelector('.dsp-vp-ihover-inner') || zz || zn).click(); } finally { bypassImg = false; }
}
/* the editor opens the picker when the mouse is pressed and let go on a picture: here a press on a picture only selects it */
function noPick(ev){
  if (!on() || bypassImg) return;
  var m = mainEl(), t = ev.target; if (!m || !t || !t.closest || !m.contains(t)) return;
  if (document.getElementById('dsp-cre-fullslide')) return;            /* the picture is being cropped */
  if (t.closest('.dsp-vp-imgzone') && !t.closest('button,.dsp-vp-cropbtn,.dsp-vp-imgreset')) ev.stopPropagation();
}
window.addEventListener('mousedown', noPick, true);
window.addEventListener('touchstart', noPick, true);
function hoverImg(ev){
  var b = ov && ov.querySelector('.dsel-imgbtn'); if (!b) return;
  var t = ev && ev.target, m = mainEl(), k = null, e = null;
  if (ev && t === b) return;
  var ar0 = $('dsp-ppt-canvas-area');
  if (ev && m && t && t.closest && (m.contains(t) || (ar0 && ar0.contains(t) && !t.closest('.dsel-mini,.dsel-rot,button')))) {
    var pt = toSlide(ev); e = hitTest(pt);
    if (e && (e.t === 'img' || e.t === 'png' || hasPicFill(e))) k = e._dk; else e = null;
  }
  if (!k && ev && sel && sset.length === 1) { var se0 = elByKey(sel); if (se0 && !isLocked(sel) && (se0.t === 'img' || se0.t === 'png' || hasPicFill(se0))) { k = sel; e = se0; } } /* the selected picture keeps its Change image button */
if (!k) { pfCropBtn(null); if (hovK) { hovK = null; b.style.display = 'none'; } return; }
  var f = frameOf(e), n = nodeFor(k), has = !!(n && n.querySelector('img'));
  if (e.t === 'img') has = !!(curSlide() || {}).image_data;
if (hasPicFill(e)) has = true;
  hovK = k; b.setAttribute('data-k', k); b.innerHTML = IC.pic + '<span>' + (has ? 'Change image' : 'Add image') + '</span>';
  b.style.display = 'flex'; b.style.left = ((f.x + f.w) / W * 100) + '%'; b.style.top = (f.y / H * 100) + '%';
pfCropBtn(e, f, k);
}
document.addEventListener('pointerdown', function(ev){
  if (!on() || ev.button !== 0) return;
  var t = ev.target, m = mainEl(), area = $('dsp-ppt-canvas-area');
  if (pick && area && area.contains(t)) pick = null;             /* back on the slide without having picked a picture */
  if (isUi(t)) return;
  if (!m.contains(t)) {
    /* beside the slide: an element that was moved out there is picked like any other; on an empty place a click clears
       the selection and a drag draws the selection box */
    if (!(area && area.contains(t)) || (t.closest && t.closest('button,a,input,select,textarea,#dsce-bar'))) return;
  }
  if (t.closest('[data-dsobj]')) return;                       /* the "+ Add PNG" button */
  if (t.closest('button')) return;
  norm();
  var pt = toSlide(ev), e = hitTest(pt);
  if (!e) { if (!(t.closest && t.closest('[contenteditable="true"]'))) blurText(); pend = {x0: ev.clientX, y0: ev.clientY, mq: true, add: ev.shiftKey, clear: !ev.shiftKey}; return; }
  var k = e._dk, f = frameOf(e), big = f.w * f.h > W * H * 0.5 || isGlow(e), grp = expand(k);
  if (e.t !== 'text') blurText();                              /* leaving a text that was being typed in */
  if (ev.shiftKey) { ev.preventDefault(); blurText(); toggleKeys(grp); return; }                 /* Shift + click: add to / take out of the selection */
  if (sset.length > 1 && sset.indexOf(k) >= 0) { ev.preventDefault(); blurText(); pend = {k: k, x0: ev.clientX, y0: ev.clientY, can: true, drill: true}; return; }
  if (grp.length > 1) { ev.preventDefault(); blurText(); setSel(grp, k); pend = {k: k, x0: ev.clientX, y0: ev.clientY, can: true}; return; }
  var was = (sel === k && sset.length === 1);
  if (big && !was) { pend = {k: k, x0: ev.clientX, y0: ev.clientY, mq: true, add: false, pick: k}; return; }   /* a background: click selects it, drag draws the selection box */
  select(k);
  pend = {k: k, x0: ev.clientX, y0: ev.clientY, can: e.t === 'text' ? !was : (e.t !== 'img' && e.t !== 'chart')};
}, true);
/* the small round handle inside a box, shape or picture: drag it inward to round the corners, back out to sharpen them */
function cornerOK(e){ return !!e && !e._png && (e.t === 'rect' || (e.t === 'img' && e.shape !== 'ell') || (e.t === 'shape' && !!X.polyPts({t: 'shape', prst: e.prst, sides: e.sides, w: 10, h: 10}))); }
function rcGeo(e){
  var f = frameOf(e), w = Math.max(1, f.w), h = Math.max(1, f.h), V = [0, 0], C = [w / 2, h / 2];
  if (e.t === 'shape') { var P = X.polyPts({t: 'shape', prst: e.prst, sides: e.sides, w: w, h: h}); if (P && P.length) { V = P[0]; C = [0, 0]; P.forEach(function(p){ C[0] += p[0] / P.length; C[1] += p[1] / P.length; }); } }
  var dx = C[0] - V[0], dy = C[1] - V[1], L = Math.sqrt(dx * dx + dy * dy) || 1;
  return {f: f, w: w, h: h, V: V, u: [dx / L, dy / L], L: L};
}
function placeRc(e){
  var b = fr && fr.querySelector('.dsel-rc'); if (!b || !cornerOK(e)) return;
  var g = rcGeo(e), d = Math.min(g.L * 0.85, 16 + (+e.r || 0));
  b.style.left = ((g.V[0] + g.u[0] * d) / g.w * 100) + '%'; b.style.top = ((g.V[1] + g.u[1] * d) / g.h * 100) + '%';
}
function cornerDrag(e, ev){
  var g = rcGeo(e), key = e._dk, cur = +e.r || 0, tip = ov && ov.querySelector('.dsel-sdt');
  var mx = e.t === 'shape' ? 400 : Math.floor(Math.min(g.w, g.h) / 2);
  function show(r){ var t = ov && ov.querySelector('.dsel-sdt'); if (t) { t.textContent = 'Corners: ' + r; t.classList.add('dsel-on'); } }
  show(Math.round(cur));
  document.documentElement.classList.add('dsel-dragging');
  function loc(x){ var p = toSlide(x), cx = g.f.x + g.f.w / 2, cy = g.f.y + g.f.h / 2, a = -(g.f.rot || 0) * Math.PI / 180, ux = p.x - cx, uy = p.y - cy; return [cx + ux * Math.cos(a) - uy * Math.sin(a) - g.f.x, cy + ux * Math.sin(a) + uy * Math.cos(a) - g.f.y]; }
  function mv(x){
    var q = loc(x), d = (q[0] - g.V[0]) * g.u[0] + (q[1] - g.V[1]) * g.u[1], r = Math.round(Math.max(0, Math.min(mx, d - 16)));
    if (r !== cur) { cur = r; setO(key, {r: r}, true, key + '|r'); rerender(); sync(); }
    show(r);
  }
  function up(){ window.removeEventListener('pointermove', mv, true); window.removeEventListener('pointerup', up, true); document.documentElement.classList.remove('dsel-dragging'); var t3 = ov && ov.querySelector('.dsel-sdt'); if (t3) t3.classList.remove('dsel-on'); paint(); }
  window.addEventListener('pointermove', mv, true); window.addEventListener('pointerup', up, true);
}
/* the small diamond on a polygon: drag up for more sides, down for fewer (3 to 12) */
function sidesDrag(e, ev){
  var n0 = X.sidesOf(e) || 3, y0 = ev.clientY, cur = n0, key = e._dk, tip = ov && ov.querySelector('.dsel-sdt');
  if (tip) { tip.textContent = 'Sides: ' + n0; tip.classList.add('dsel-on'); }
  document.documentElement.classList.add('dsel-dragging');
  function mv(x){ var n = Math.max(3, Math.min(12, n0 + Math.round((y0 - x.clientY) / 14))); if (tip) tip.textContent = 'Sides: ' + n; if (n !== cur) { cur = n; setO(key, {sides: n}, true, key + '|sides'); rerender(); sync(); var t2 = ov && ov.querySelector('.dsel-sdt'); if (t2) { t2.textContent = 'Sides: ' + n; t2.classList.add('dsel-on'); } } }
  function up(){ window.removeEventListener('pointermove', mv, true); window.removeEventListener('pointerup', up, true); document.documentElement.classList.remove('dsel-dragging'); var t3 = ov && ov.querySelector('.dsel-sdt'); if (t3) t3.classList.remove('dsel-on'); paint(); }
  window.addEventListener('pointermove', mv, true); window.addEventListener('pointerup', up, true);
}
function onHandleDown(ev){
  if (ev.button !== 0 || !sel) return;
  var h = ev.target.closest ? ev.target.closest('[data-h]') : null; if (!h) return;
  var e = elByKey(sel); if (!e) return;
  ev.preventDefault(); ev.stopPropagation();
  var mode = h.getAttribute('data-h');
  if (mode === 'sides') { sidesDrag(e, ev); return; }
  if (mode === 'corner') { cornerDrag(e, ev); return; }
  startDrag(mode === 'mv' ? 'move' : (mode === 'rot' ? 'rot' : 'size'), mode, e, frameOf(e), ev);
}
/* what lies on a box moves with the box (its icon, its texts): everything in front of it whose middle is inside it */
function carried(e){
  if (!/^(rect|ell|shape|img)$/.test(e.t)) return [];
  var f = frameOf(e); if (f.rot || f.w * f.h > W * H * 0.5 || f.w < 60 || f.h < 40) return [];
  var a = els(), i = a.indexOf(e), out = [];
  for (var j = i + 1; j < a.length && i >= 0; j++) {
    var g = frameOf(a[j]), cx = g.x + g.w / 2, cy = g.y + g.h / 2;
    if (cx > f.x && cx < f.x + f.w && cy > f.y && cy < f.y + f.h && g.w * g.h <= f.w * f.h * 1.02 && !isLocked(a[j]._dk)) out.push(a[j]._dk);
  }
  return out;
}
function startDrag(kind, handle, e, f, ev){
  var m = mainEl(), rc = m.getBoundingClientRect();
  var keys = (sset.length > 1 && sset.indexOf(e._dk) >= 0) ? sset.slice() : [e._dk], many = keys.length > 1;
  if (many) f = selFrame();
  drag = {kind: kind, h: handle, k: e._dk, e: e, f: f, keys: keys, many: many, x0: ev.clientX, y0: ev.clientY, sc: W / rc.width, loc: m.offsetWidth / W, nodes: [], moved: false, cur: null,
          ratio: many || ratioLock || /^(icon|ring|logo)$/.test(e.t) || (e.t === 'png' && !e._png.hold), comp: [], items: many ? itemsOf(keys) : null};
  keys.forEach(function(k){ drag.nodes = drag.nodes.concat(nodesFor(k)); });
  if (kind === 'move' && !ev.altKey) {
    keys.forEach(function(k){ var el = elByKey(k); if (el) carried(el).forEach(function(q){ if (keys.indexOf(q) < 0 && drag.comp.indexOf(q) < 0) drag.comp.push(q); }); });
    drag.comp.forEach(function(k){ drag.nodes = drag.nodes.concat(nodesFor(k)); });
  }
  document.documentElement.classList.add('dsel-dragging');
  if (ov) ov.classList.add('dsel-busy');
  blurText();
}
function mqDraw(r){
  var el = ov && ov.querySelector('.dsel-mq'); if (!el) return;
  if (!r) { el.style.display = 'none'; return; }
  el.style.display = 'block'; el.style.left = (r.x / W * 100) + '%'; el.style.top = (r.y / H * 100) + '%'; el.style.width = (r.w / W * 100) + '%'; el.style.height = (r.h / H * 100) + '%';
}
/* the mouse button was let go where the page could not see it (outside the window, over another program): finish cleanly */
function lostUp(){ if (drag || mq || pend) endDrag(); }
window.addEventListener('blur', lostUp);
document.addEventListener('pointermove', function(ev){
  if ((drag || mq || pend) && ev.pointerType === 'mouse' && ev.buttons === 0) { lostUp(); return; }
  if (on() && ov) { if (drag || mq) hoverImg(null); else if (!pend) hoverImg(ev); }
  if (pend && !drag && !mq) {
    if (Math.abs(ev.clientX - pend.x0) + Math.abs(ev.clientY - pend.y0) < 5) return;
    var p = pend; pend = null;
    if (p.mq) {
      if (!ensureOv()) return;
      var mr = mainEl().getBoundingClientRect();
      mq = {x0: (p.x0 - mr.left) / mr.width * W, y0: (p.y0 - mr.top) / mr.height * H, base: p.add ? sset.slice() : [], r: null};
      document.documentElement.classList.add('dsel-dragging'); blurText();
    } else if (p.can) {
      var e = elByKey(p.k); if (!e) return;
      startDrag('move', 'mv', e, frameOf(e), {clientX: p.x0, clientY: p.y0, altKey: ev.altKey});
    } else return;
  }
  if (mq) {
    ev.preventDefault();
    var q = toSlide(ev);
    mq.r = {x: Math.min(mq.x0, q.x), y: Math.min(mq.y0, q.y), w: Math.abs(q.x - mq.x0), h: Math.abs(q.y - mq.y0)};
    mqDraw(mq.r); return;
  }
  if (!drag) return;
  ev.preventDefault();
  var d = drag, f = d.f, dx = (ev.clientX - d.x0) * d.sc, dy = (ev.clientY - d.y0) * d.sc;
  d.moved = true;
  if (d.kind === 'move') {
    var cx = f.x + f.w / 2 + dx, cy = f.y + f.h / 2 + dy, sv = false, sh = false;
    if (!ev.altKey) { if (Math.abs(cx - W / 2) < 7) { dx += W / 2 - cx; sv = true; } if (Math.abs(cy - H / 2) < 7) { dy += H / 2 - cy; sh = true; } }
    if (ev.shiftKey) { if (Math.abs(dx) > Math.abs(dy)) dy = 0; else dx = 0; }
    gv.style.display = sv ? 'block' : 'none'; gh.style.display = sh ? 'block' : 'none';
    d.cur = {dx: dx, dy: dy};
    live(d.nodes, function(s){ s.translate = (dx * d.loc) + 'px ' + (dy * d.loc) + 'px'; });
    placeFrame({x: f.x + dx, y: f.y + dy, w: f.w, h: f.h, rot: f.rot});
  } else if (d.kind === 'rot') {
    var m = mainEl().getBoundingClientRect(), ccx = m.left + (f.x + f.w / 2) / W * m.width, ccy = m.top + (f.y + f.h / 2) / H * m.height;
    var ang = Math.atan2(ev.clientY - ccy, ev.clientX - ccx) * 180 / Math.PI + 90;
    ang = ((ang % 360) + 360) % 360;
    if (ev.shiftKey) ang = Math.round(ang / 15) * 15; else [0, 45, 90, 135, 180, 225, 270, 315, 360].forEach(function(a){ if (Math.abs(ang - a) < 3) ang = a % 360; });
    d.cur = {rot: ang};
    if (!d.many) live(d.nodes, function(s){ s.rotate = (ang - (f.rot || 0)) + 'deg'; });
    placeFrame({x: f.x, y: f.y, w: f.w, h: f.h, rot: ang});
  } else {
    var r = -(f.rot || 0) * Math.PI / 180, lx = dx * Math.cos(r) - dy * Math.sin(r), ly = dx * Math.sin(r) + dy * Math.cos(r), h = d.h;
    var L0 = -f.w / 2, R0 = f.w / 2, T0 = -f.h / 2, B0 = f.h / 2, L1 = L0, R1 = R0, T1 = T0, B1 = B0, MIN = 6;
    if (h.indexOf('w') >= 0) L1 = Math.min(R0 - MIN, L0 + lx);
    if (h.indexOf('e') >= 0) R1 = Math.max(L0 + MIN, R0 + lx);
    if (h.indexOf('n') >= 0) T1 = Math.min(B0 - MIN, T0 + ly);
    if (h.indexOf('s') >= 0) B1 = Math.max(T0 + MIN, B0 + ly);
    if ((d.ratio || ev.shiftKey) && h.length === 2 && f.w > 1 && f.h > 1) {
      var k = Math.max((R1 - L1) / f.w, (B1 - T1) / f.h), nw = f.w * k, nh = f.h * k;
      if (h.indexOf('w') >= 0) L1 = R0 - nw; else R1 = L0 + nw;
      if (h.indexOf('n') >= 0) T1 = B0 - nh; else B1 = T0 + nh;
    }
    var w1 = R1 - L1, h1 = B1 - T1, lcx = (L1 + R1) / 2, lcy = (T1 + B1) / 2, rr = (f.rot || 0) * Math.PI / 180;
    var gcx = f.x + f.w / 2 + lcx * Math.cos(rr) - lcy * Math.sin(rr), gcy = f.y + f.h / 2 + lcx * Math.sin(rr) + lcy * Math.cos(rr);
    var nf = {x: gcx - w1 / 2, y: gcy - h1 / 2, w: w1, h: h1, rot: f.rot};
    d.cur = {f: nf};
    placeFrame(nf);
    if (!d.many && d.e.t !== 'line' && d.e.t !== 'path' && d.e.t !== 'text') live(d.nodes, function(s){ s.left = (nf.x / W * 100) + '%'; s.top = (nf.y / H * 100) + '%'; s.width = (nf.w / W * 100) + '%'; s.height = (nf.h / H * 100) + '%'; });
  }
}, true);
function endDrag(){
  var p = pend; pend = null;
  if (mq) {
    /* the selection box: everything that lies completely inside it */
    var q = mq, r = q.r; mq = null; mqDraw(null);
    document.documentElement.classList.remove('dsel-dragging');
    eatClick = true; setTimeout(function(){ eatClick = false; }, 80);
    var keys = [];
    if (r && (r.w > 3 || r.h > 3)) live1().forEach(function(e){
      var b = bboxOf([frameOf(e)]);
      if (b.x >= r.x - 0.5 && b.y >= r.y - 0.5 && b.x + b.w <= r.x + r.w + 0.5 && b.y + b.h <= r.y + r.h + 0.5) expand(e._dk).forEach(function(k){ if (keys.indexOf(k) < 0) keys.push(k); });
    });
    setSel(q.base.concat(keys));
    return;
  }
  if (!drag) {
    if (p) {
      if (p.pick) setSel(expand(p.pick), p.pick);
      else if (p.mq && p.clear) { if (sel) select(null); }
      else if (p.drill) setSel([p.k]);                    /* a second click inside a selection or group picks that one element */
    }
    return;
  }
  var d = drag; drag = null;
  document.documentElement.classList.remove('dsel-dragging');
  if (ov) ov.classList.remove('dsel-busy');
  if (gv) gv.style.display = 'none'; if (gh) gh.style.display = 'none';
  if (!d.moved || !d.cur) { clearLive(d.nodes); sync(); return; }
  eatClick = true; setTimeout(function(){ eatClick = false; }, 80);
  if (d.kind === 'move') moveBy(d.keys.concat(d.comp), d.cur.dx, d.cur.dy);
  else if (d.many) { if (d.kind === 'rot') rotateItems(d.items, d.f, d.cur.rot); else scaleItems(d.items, d.f, d.cur.f); }
  else if (d.kind === 'rot') setFrame(d.k, {rot: d.cur.rot});
  else {
    var nf = d.cur.f, f0 = d.f;
    if (d.e.t === 'text') {
      /* the frame shows the lines; the text area keeps its own top and height */
      var top = (f0.ty != null ? f0.ty : f0.y);
      setFrame(d.k, {x: nf.x, w: nf.w, y: top + (nf.y - f0.y)});
    } else setFrame(d.k, {x: nf.x, y: nf.y, w: nf.w, h: nf.h});
  }
}
document.addEventListener('pointerup', endDrag, true);
document.addEventListener('pointercancel', endDrag, true);
/* clicks on the slide: a plain click on a picture selects it (the "Change image" label and the bar button still open the picker) */
window.addEventListener('click', function(ev){
  if (!on()) return;
  var m = mainEl(), t = ev.target; if (!m || !t || !t.closest || !m.contains(t)) return;
  if (eatClick) { ev.preventDefault(); ev.stopPropagation(); return; }
  if (bypassImg) return;
  var z = t.closest('.dsp-vp-imgzone');
  if (z && !t.closest('button')) { ev.stopPropagation(); }      /* a click only selects the picture; its small "Change image" button, or the one in the bar, opens the picker */
}, true);
window.addEventListener('dblclick', function(ev){
  if (!on()) return;
  var m = mainEl(), t = ev.target; if (!m || !t || !t.closest || !m.contains(t)) return;
  if (t.closest('[contenteditable="true"]')) return;
  ev.stopPropagation();                      /* a double click on a shape must not open "Edit slide" */
}, true);

function covered(){
  var m = mainEl(); if (!m) return true;
  var r = m.getBoundingClientRect(), el = document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2);
  return !(el && (m.contains(el) || (ov && ov.contains(el)) || (panel && panel.contains(el)) || (bar && bar.contains(el)) || (dock && dock.contains(el))));
}
/* ------------------------------------------------------------------ text typed into texts that belong to no slide field */
document.addEventListener('input', function(ev){
  var el = ev.target; if (!el || !el.getAttribute || el.getAttribute('data-ed-field') !== 'x-dsel') return;
  var si = parseInt(el.getAttribute('data-ed-slide'), 10), key = el.getAttribute('data-ed-idx'), d = deck();
  if (!d || !d.slides[si] || !key) return;
  var vid = (window.S && S.choices && S.choices['slide_' + si]) || 'v1', L = X.layer(d.slides[si], vid, true);
  var txt = String(el.innerText || '').replace(/ /g, ' ').replace(/\n+$/, '');
  (L.m[key] || (L.m[key] = {})).text = txt;
  mirror(si);
}, true);
window.addEventListener('keydown', function(ev){
  if ((ev.ctrlKey || ev.metaKey) && !ev.shiftKey && !ev.altKey && (ev.key === 's' || ev.key === 'S')) {
    var fs0 = fsEl(); if (!fs0 || getComputedStyle(fs0).display === 'none' || !on()) return;
    ev.preventDefault(); ev.stopPropagation();
    try { var ae0 = document.activeElement; if (ae0 && ae0.isContentEditable) ae0.blur(); } catch (x) {}
    if (typeof window.dspSaveDraft === 'function') window.dspSaveDraft(); else toast('Saving is not available here.', 'error');
    return;
  }
}, true);
window.addEventListener('keydown', function(ev){
  if (!on()) return;
  var t = ev.target, typing = !!(t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName || '')));
  if (typing) {
    if (ev.key === 'Enter' && ev.shiftKey && t.getAttribute && t.getAttribute('data-ed-field') === 'x-dsel') { ev.stopImmediatePropagation(); return; }   /* new line */
    if (ev.key === 'Escape' && t.isContentEditable) { t.blur(); }
    return;
  }
  if (warnEl) { if (ev.key === 'Escape') { ev.preventDefault(); closeWarn(); } return; }
  if (helpEl) { if (ev.key === 'Escape' || ev.key === '?') { ev.preventDefault(); closeHelp(); } return; }
  if (ev.key === ' ' || ev.code === 'Space') { ev.preventDefault(); if (!spaceDn) setSpace(true); return; }                 /* Space + drag moves the canvas */
  if (ev.key === '?') { ev.preventDefault(); showHelp(); return; }
  if (ev.key === 'Home') { ev.preventDefault(); resetPan(); return; }
  if (covered()) return;                       /* a pop-up is open over the slide */
  var mod = ev.ctrlKey || ev.metaKey, k = ev.key;
  if (mod && (k === 'z' || k === 'Z')) { ev.preventDefault(); if (ev.shiftKey) doRedo(); else doUndo(); return; }
  if (mod && (k === 'y' || k === 'Y')) { ev.preventDefault(); doRedo(); return; }
  if (menuEl && k === 'Escape') { ev.preventDefault(); closeMenu(); return; }
  if (mod && !ev.shiftKey && (k === 'v' || k === 'V')) { ev.preventDefault(); doPaste(); return; }
  if (mod && !ev.shiftKey && (k === 'c' || k === 'C')) { norm(); if (sel) { ev.preventDefault(); doCopy(); } return; }
  if (mod && !ev.shiftKey && (k === 'x' || k === 'X')) { norm(); if (sel) { ev.preventDefault(); if (doCopy(true)) { removeMany(sset.slice()); toast('Cut. Paste here or on any other slide (Ctrl + V).'); } } return; }
  if (mod && (k === 'a' || k === 'A')) { ev.preventDefault(); blurText(); setSel(live1().map(function(e){ return e._dk; })); return; }                 /* select everything on the slide */
  norm();
  if (!sel) return;
  if (mod && (k === 'g' || k === 'G')) { ev.preventDefault(); if (ev.shiftKey) doUngroup(); else doGroup(); return; }
  if (k === 'Escape') { select(null); return; }
  if (k === 'Delete' || k === 'Backspace') { ev.preventDefault(); removeMany(sset.slice()); return; }
  if (mod && (k === 'd' || k === 'D')) { ev.preventDefault(); dupMany(sset.slice()); return; }
  if (mod && (k === ']' || k === '[' || k === '}' || k === '{')) { var up1 = (k === ']' || k === '}'); ev.preventDefault(); arrange(ev.shiftKey ? (up1 ? 'front' : 'back') : (up1 ? 'fwd' : 'bwd')); return; }
  /* with the Layers list open, the up and down arrows move the selected layer up (forward) or down (backward) in the list */
  if (pmode === 'layers' && (k === 'ArrowUp' || k === 'ArrowDown')) { ev.preventDefault(); arrange(ev.shiftKey ? (k === 'ArrowUp' ? 'front' : 'back') : (k === 'ArrowUp' ? 'fwd' : 'bwd')); return; }
  var step = ev.shiftKey ? 10 : 1, mv = {ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step]}[k];
  if (mv) {
    ev.preventDefault();
    var ks = sset.slice();
    if (!ev.altKey) sset.forEach(function(q){ var el = elByKey(q); if (el) carried(el).forEach(function(c2){ if (ks.indexOf(c2) < 0) ks.push(c2); }); });
    moveBy(ks, mv[0], mv[1], sset.join(',') + '|nudge');
  }
}, true);

/* ------------------------------------------------------------------ the bar */
var bar = null, panel = null, pmode = '', ratioLock = false;
var WT = [[300, 'Light'], [400, 'Regular'], [500, 'Medium'], [600, 'Semi-bold'], [700, 'Bold'], [800, 'Extra-bold']];
function wtName(w){ var n = 'Regular', b = 1e9; WT.forEach(function(x){ var d = Math.abs(x[0] - w); if (d < b) { b = d; n = x[1]; } }); return n; }
function sw(c, none){ return '<i class="dsel-sw' + (c ? '' : ' dsel-sw-none') + '" style="' + (c ? 'background:' + c : '') + '"></i>'; }
function btn(a, label, title, cls, on1){ return '<button type="button" class="dsel-b ' + (cls || '') + (on1 ? ' dsel-on' : '') + '" data-a="' + a + '" title="' + esc(title || '') + '">' + label + '</button>'; }
/* font size: a box (type a size) with a slider under it - drag it and the text grows or shrinks as you go */
function fsizeHTML(e){ var v = Math.round(e.size || 16); return '<span class="dsel-fsz" title="Font size: drag the slider, or type a size"><span class="dsel-fszl" data-a="fsz">Font size</span><input type="number" data-i="size" min="6" max="400" step="1" value="' + v + '"><span class="dsel-fszp"><span class="dsel-fszin"><span class="dsel-fa1">A</span><input type="range" data-r="size" min="6" max="200" step="1" value="' + Math.min(200, v) + '"><span class="dsel-fa2">A</span></span></span></span>'; }
function stepper(key, val, title, min, max, stepv){ return '<span class="dsel-num" title="' + esc(title) + '"><button type="button" data-a="st-" data-k="' + key + '" data-s="' + stepv + '">&minus;</button><input type="number" data-i="' + key + '" min="' + min + '" max="' + max + '" step="' + stepv + '" value="' + val + '"><button type="button" data-a="st+" data-k="' + key + '" data-s="' + stepv + '">+</button></span>'; }
function fillOf(e){ return e.grad ? (e.grad.stops && e.grad.stops[0] && e.grad.stops[0][1]) || '' : (e.fill || ''); }
function opOf(e){ if (e.t === 'path') return e.fill ? (e.op != null ? e.op : 1) : (e.lop != null ? e.lop : 1); return e.op != null ? e.op : 1; }
function leftHTML(e){ return leftHTML0(e) + (hasPicFill(e) && sset.length <= 1 ? btn('chgpic', IC.pic + ' Change image', 'Change the picture inside this shape') + (!e.pimg.st && !e.pimg.gb ? btn('pfcrop', IC.crop + ' Crop', 'Move and zoom the picture inside this shape', '', pmode === 'pfcrop') : '') : '') + ptsBtn(e); }
function leftHTML0(e){
  if (!e) return '<span class="dsel-hint">Click an element to edit it. Shift + click, or drag a box around elements, to select several. Ctrl + A selects all.</span>';
  var h = '<span class="dsel-kind">' + esc(kindName(e)) + '</span>';
  if (sset.length > 1) {
    var a = selEls(), same = a.every(function(x){ return fam(x) === fam(a[0]); }), og = oneGroup();
    h = '<span class="dsel-kind">' + (og ? 'Group of ' : '') + a.length + ' elements</span>'
      + (og ? '' : btn('group', 'Group', 'Keep these elements together (Ctrl+G)', 'dsel-grp'))
      + (hasGroup() ? btn('ungroup', 'Ungroup', 'Split the group (Ctrl+Shift+G)') : '')
      + btn('align', svg(AIC.l) + ' Align &#9662;', 'Align and distribute: to each other, or to the slide', '', pmode === 'align');
    if (!same) return h + '<span class="dsel-lbl">Drag to move, pull a corner to resize.</span>';
  } else if (groupId(e._dk)) h += '<span class="dsel-lbl">in a group</span>';
  if (e.t === 'text') {
    var al = e.align === 'c' ? 'Centre' : (e.align === 'r' ? 'Right' : 'Left'), dec = String(e.dec || '');
    h += btn('font', '<span style="font-family:\'' + esc(e.fam) + '\',Arial,sans-serif">' + esc(e.fam) + '</span> &#9662;', 'Font', 'dsel-wide')
      + btn('weight', esc(wtName(e.wt || 400)) + ' &#9662;', 'Weight')
      + fsizeHTML(e)
      + btn('color', '<span class="dsel-A" style="border-color:' + esc(e.color) + '">A</span>', 'Text colour', 'dsel-ic')
      + btn('bold', '<b>B</b>', 'Bold', 'dsel-ic', (e.wt || 400) >= 600)
      + btn('italic', '<i style="font-style:italic;font-family:Georgia,serif">I</i>', 'Italic', 'dsel-ic', !!e.italic)
      + btn('under', '<u>U</u>', 'Underline', 'dsel-ic', dec.indexOf('u') >= 0)
      + btn('strike', '<s>S</s>', 'Strikethrough', 'dsel-ic', dec.indexOf('s') >= 0)
      + btn('caps', 'aA', 'Capitals on / off', 'dsel-ic', !!e.caps)
      + btn('talign', alignIcon(e.align), 'Text alignment: ' + al, 'dsel-ic', pmode === 'talign')
      + btn('spacing', '&#8597;&#8201;T', 'Line and letter spacing', 'dsel-ic')
      + btn('opacity', IC.op, 'Transparency', 'dsel-ic');
  } else if (e.t === 'rect' || e.t === 'ell' || e.t === 'shape') {
    h += btn('fill', (e.pimg && e.pimg.src ? '<span class="dsel-sw" style="background:#CBD5E1 url(\'' + String(e.pimg.src).replace(/['"()\\<>\s]/g, '') + '\') center/cover no-repeat"></span>' : sw(fillOf(e))) + ' Fill', 'Fill: one colour, a gradient or a picture') + btn('line', sw(e.line || '') + ' Outline', 'Outline: colour or gradient, and width')
      + (e.line ? stepper('lw', r1(e.lw || 1), 'Outline width', 0.5, 60, 0.5) : '')
      + ((e.t === 'rect' || e.t === 'shape') ? '<span class="dsel-lbl">Corners</span>' + stepper('r', Math.round(e.r || 0), 'Corner roundness', 0, 400, 2) : '') + (X.sidesOf(e) ? '<span class="dsel-lbl">Sides</span>' + stepper('sides', X.sidesOf(e), 'Number of sides - or drag the small diamond on the right edge up or down', 3, 12, 1) : '')
      + btn('opacity', IC.op + ' Transparency', 'Transparency');
  } else if (e.t === 'line' || e.t === 'path') {
    h += btn('color', sw(e.t === 'line' ? e.color : (e.stroke || e.fill || '')) + ' Colour', 'Colour')
      + ((e.t === 'line' || e.stroke) ? '<span class="dsel-lbl">Thickness</span>' + stepper('lw', r1(e.lw || 1), 'Line thickness', 0.5, 60, 0.5) : '')
      + btn('opacity', IC.op + ' Transparency', 'Transparency');
  } else if (e.t === 'icon') {
    h += btn('color', (e.tgrad && e.tgrad.stops ? '<span class="dsel-sw" style="background:linear-gradient(135deg,' + e.tgrad.stops.map(function(q){ return q[1]; }).join(',') + ')"></span>' : sw(e.color)) + ' Colour', 'Icon colour: one colour or a gradient') + btn('icon', 'Change icon', 'Pick another icon') + btn('opacity', IC.op + ' Transparency', 'Transparency');
  } else if (e.t === 'ring') {
    h += btn('color', sw(e.color) + ' Colour', 'Ring colour') + btn('track', sw(e.track || '#DDDDDD') + ' Track', 'Colour of the unfilled part') + '<span class="dsel-lbl">Value %</span>' + stepper('pct', Math.round(e.pct || 0), 'Value in percent', 0, 100, 1);
  } else if (e.t === 'img') {
    h += btn('img', 'Change image', 'Pick or upload another picture') + btn('crop', 'Crop', 'Move the picture inside its frame')
      + (e.shape !== 'ell' ? btn('corners', 'Round corners', 'Drag the slider to make the corners rounder or sharper', '', pmode === 'corners') : '') + btn('opacity', IC.op + ' Transparency', 'Transparency');
  } else if (e.t === 'png') {
    h += btn('img', 'Change image', 'Pick or upload another picture for this frame');
    if (e._png.hold) h += btn('crop', 'Crop', 'Move and zoom the picture inside its frame', '', pmode === 'pcrop') + btn('corners', 'Round corners', 'Drag the slider to make the corners rounder or sharper', '', pmode === 'corners') + btn('opacity', IC.op + ' Transparency', 'Transparency');
    else h += '<span class="dsel-lbl">Always in front of the other elements.</span>';
  } else if (e.t === 'chart') {
    h += btn('chart', 'Edit chart', 'Change the chart data');
  } else if (e.t === 'logo') {
    h += btn('logo', 'Edit logo', 'Change or remove the logo') + btn('opacity', IC.op + ' Transparency', 'Transparency');
  }
  if (e.t === 'img') h += btn('line', sw(e.line || '') + ' Outline', 'Outline colour and width', '', pmode === 'line');
  if (e.t === 'text' || e.t === 'icon') h += btn('tline', sw(e.tline || '') + ' Outline', e.t === 'icon' ? 'Outline around the icon: colour or gradient, and width' : 'Outline around the letters: colour and width', '', pmode === 'tline');
  if (/^(rect|ell|shape|text|img|icon|line|path|logo)$/.test(e.t)) h += btn('shadow', IC.shd + ' Shadow', 'Shadow: colour, opacity, blur, distance, angle, size', '', pmode === 'shadow');
	if (sset.length <= 1) h += btn('align', svg(AIC.l) + ' Align &#9662;', 'Align and distribute on the slide', '', pmode === 'align');
  return h;
}
function alignIcon(a){
  var w = a === 'c' ? [[3, 12], [5, 8], [3, 12], [5, 8]] : (a === 'r' ? [[4, 12], [8, 8], [4, 12], [8, 8]] : [[2, 12], [2, 8], [2, 12], [2, 8]]), s = '';
  w.forEach(function(x, i){ s += '<rect x="' + x[0] + '" y="' + (2 + i * 4) + '" width="' + x[1] + '" height="1.8" rx=".9"/>'; });
  return '<svg viewBox="0 0 18 18" width="16" height="16" fill="currentColor">' + s + '</svg>';
}
function rightHTML(){
  var fo = focusOn();
  return btn('edscreen', fo ? 'Exit' : 'Edit screen', fo ? 'Show the slides and layout panels again' : 'Hide the slides and layout panels - more room to edit', 'dsel-edscr', fo) + '<span class="dsel-dsep"></span>'
    + btn('pos', 'Positions', 'Order, alignment, size, place, rotation', '', pmode === 'pos') + btn('layers', 'Layers', 'Every element of this slide', '', pmode === 'layers')
    + btn('add', '+ Add element', 'Add a text box, a shape, a line or an icon', '', pmode === 'add')
    + btn('edslide', 'Edit Slide', 'Edit the text and points of this slide') + btn('edlogo', 'Logo', 'Company logo on every slide')
    + btn('master', 'Master Slide', 'Fonts, sizes and colours for the whole deck') + btn('addslide', 'Add Slide', 'Add a new slide')
    + btn('undo', '&#8630;', 'Undo (Ctrl+Z)', 'dsel-ic') + btn('redo', '&#8631;', 'Redo (Ctrl+Y)', 'dsel-ic')
    + btn('reset', '&#8634; Reset', 'Undo everything changed since this presentation was opened', 'dsel-reset');
}
function kindName(e){
  if (e.t === 'text') return 'Text';
  if (e.t === 'rect') return e.r ? 'Rounded box' : 'Rectangle';
  if (e.t === 'ell') return Math.abs(e.w - e.h) < 2 ? 'Circle' : 'Ellipse';
  if (e.t === 'shape') return ({chevron: 'Chevron', homePlate: 'Arrow', triangle: 'Triangle', rtTriangle: 'Triangle', diamond: 'Diamond', hexagon: 'Hexagon', parallelogram: 'Parallelogram', pentagon: 'Pentagon'})[e.prst] || 'Shape';
  return ({png: 'Picture', line: 'Line', path: 'Drawn shape', icon: 'Icon', ring: 'Progress ring', img: 'Picture', chart: 'Chart', logo: 'Logo'})[e.t] || 'Element';
}
/* the fixed buttons sit in their own bar at the bottom; Edit screen hides the slides and layout panels */
var dock = null;
function focusOn(){ return document.documentElement.classList.contains('dsel-focus'); }
function setFocus(v){
  document.documentElement.classList.toggle('dsel-focus', !!v);
  try { window.dispatchEvent(new Event('resize')); } catch (er) {}
  paint();
  setTimeout(function(){ try { window.dispatchEvent(new Event('resize')); } catch (er) {} place(); }, 150);
}
/* the settings panel: drag it by its title bar to where you like it; the place is remembered */
var panPos = null; try { panPos = JSON.parse(localStorage.getItem('dsel_panel_pos') || 'null'); } catch (er) { panPos = null; }
function panelDragDown(ev){
  var h = ev.target.closest ? ev.target.closest('.dsel-ph') : null; if (!h || ev.target.closest('button,input,select') || ev.button > 0) return;
  ev.preventDefault();
  var r0 = panel.getBoundingClientRect(), ox = ev.clientX - r0.left, oy = ev.clientY - r0.top;
  document.documentElement.classList.add('dsel-dragging');
  function mv(x){
    var l = Math.max(4, Math.min(window.innerWidth - r0.width - 4, x.clientX - ox)), t = Math.max(4, Math.min(window.innerHeight - 60, x.clientY - oy));
    panPos = {l: Math.round(l), t: Math.round(t)};
    panel.style.left = panPos.l + 'px'; panel.style.right = 'auto'; panel.style.top = panPos.t + 'px'; panel.style.maxHeight = Math.max(160, window.innerHeight - panPos.t - 12) + 'px';
  }
  function up(){ window.removeEventListener('pointermove', mv, true); window.removeEventListener('pointerup', up, true); document.documentElement.classList.remove('dsel-dragging'); try { if (panPos) localStorage.setItem('dsel_panel_pos', JSON.stringify(panPos)); } catch (er) {} }
  window.addEventListener('pointermove', mv, true); window.addEventListener('pointerup', up, true);
}
function ensureBar(){
  var fs = fsEl(); if (!fs) return null;
  if (bar && bar.parentElement === fs) return bar;
  bar = document.createElement('div'); bar.id = 'dsel-bar';
  bar.innerHTML = '<div class="dsel-left"></div>';
  fs.appendChild(bar);
  dock = document.createElement('div'); dock.id = 'dsel-dock'; dock.style.display = 'none'; dock.innerHTML = '<div class="dsel-right"></div>'; fs.appendChild(dock);
  dock.addEventListener('click', onBarClick);
  dock.addEventListener('mousedown', function(e){ e.preventDefault(); });
  panel = document.createElement('div'); panel.id = 'dsel-panel'; panel.style.display = 'none'; fs.appendChild(panel);
  bar.addEventListener('click', onBarClick);
  bar.addEventListener('change', onInput);
  bar.addEventListener('keydown', function(e){ if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.target.blur(); });
  bar.addEventListener('mousedown', function(e){ if (e.target.tagName !== 'INPUT') e.preventDefault(); });
  panel.addEventListener('click', onPanelClick);
  panel.addEventListener('pointerdown', gsDown);
  panel.addEventListener('pointerdown', panelDragDown);
  panel.addEventListener('dblclick', function(ev){ if (ev.target.closest && ev.target.closest('.dsel-ph') && !ev.target.closest('button')) { panPos = null; try { localStorage.removeItem('dsel_panel_pos'); } catch (er) {} place(); } });
  bar.addEventListener('input', function(ev){ var t = ev.target; if (!t || !t.getAttribute || t.getAttribute('data-r') !== 'size' || !sel) return; var v = Math.round(+t.value); setO(sel, {size: v}, true, sel + '|size'); rerender(); sync(); var n0 = bar.querySelector('input[data-i="size"]'); if (n0) n0.value = v; });
  bar.addEventListener('change', function(ev){ var t = ev.target; if (t && t.getAttribute && t.getAttribute('data-r') === 'size') paint(); });
  panel.addEventListener('dblclick', function(ev){ var g = ev.target.closest ? ev.target.closest('.dsel-gn') : null; if (g) { ev.preventDefault(); renameGroup(g.getAttribute('data-g')); } });
  panel.addEventListener('change', onInput);
  panel.addEventListener('input', onPanelInput);
  return bar;
}
function paint(){
  if (!on() || !ensureBar()) return;
  norm();
  var e = sel && elByKey(sel);
  if (sel && !e) { sel = null; sset = []; }
  bar.querySelector('.dsel-left').innerHTML = leftHTML(e);
  dock.querySelector('.dsel-right').innerHTML = rightHTML();
  if (pmode && /^(color|fill|line|track|font|weight|spacing|opacity|icon)$/.test(pmode) && !e) pmode = '';
  paintPanel(); place(); sync();
}
function place(){
  if (!bar) return;
  var area = $('dsp-ppt-canvas-area'), show = on() && area;
  bar.style.display = show ? 'flex' : 'none';
  if (dock) dock.style.display = show ? 'flex' : 'none';
  if (!show) { var ck0 = $('dsel-keys'); if (ck0) ck0.style.display = 'none'; closeHelp(); closeMenu(); if (panXY.x || panXY.y) resetPan(); if (panel) panel.style.display = 'none'; if (area && area.__dselPad) { area.style.paddingTop = ''; area.style.paddingBottom = ''; area.__dselPad = 0; area.__dselPadB = 0; }
    if (focusOn()) { document.documentElement.classList.remove('dsel-focus'); try { window.dispatchEvent(new Event('resize')); } catch (er) {} } return; }
  var r = area.getBoundingClientRect();
  var ck = ensureCorner(); if (ck) { ck.style.display = 'flex'; ck.style.left = Math.round(r.left + 14) + 'px'; ck.style.top = Math.round(r.bottom - 44) + 'px'; }
  if (panXY.x || panXY.y) applyPan();
  bar.style.top = Math.round(r.top + 8) + 'px'; bar.style.maxWidth = Math.round(r.width - 28) + 'px';
  bar.style.left = Math.round(r.left + Math.max(14, (r.width - bar.offsetWidth) / 2)) + 'px';          /* in the middle, above the slide */
  var lw = bar.querySelector('.dsel-left'); if (lw && lw.offsetWidth < 6) bar.style.display = 'none';   /* nothing selected and no hint on a small screen: no empty pill */
  var bh = bar.style.display === 'none' ? 0 : (bar.offsetHeight || 52), pad = bh ? bh + 30 : 16;
  if (area.__dselPad !== pad) { area.style.setProperty('padding-top', pad + 'px', 'important'); area.__dselPad = pad; }
  if (dock) {
    dock.style.maxWidth = Math.round(r.width - 20) + 'px';
    var dw = dock.offsetWidth, dh = dock.offsetHeight || 50, dl = Math.round(r.left + Math.max(10, (r.width - dw) / 2)), lift = 0;
    var ckb = ck && ck.offsetWidth ? ck.getBoundingClientRect() : null; if (ckb && dl < ckb.right + 8) lift = ckb.height + 10;
    dock.style.left = dl + 'px'; dock.style.top = Math.round(r.bottom - 10 - dh - lift) + 'px';
    var padB = dh + lift + 22; if (area.__dselPadB !== padB) { area.style.setProperty('padding-bottom', padB + 'px', 'important'); area.__dselPadB = padB; }
    if (area.__dselRz !== pad + '|' + padB) { area.__dselRz = pad + '|' + padB; clearTimeout(area.__dselRzT); area.__dselRzT = setTimeout(function(){ try { window.dispatchEvent(new Event('resize')); } catch (er) {} }, 60); }   /* let the slide re-fit between the two bars */
  }
  if (panel) {
    panel.style.display = pmode ? 'block' : 'none';
    if (panPos) {   /* where the user dragged it */
      var pw0 = panel.offsetWidth || 280, pl = Math.max(4, Math.min(window.innerWidth - pw0 - 4, panPos.l)), pt0 = Math.max(4, Math.min(window.innerHeight - 80, panPos.t));
      panel.style.left = pl + 'px'; panel.style.right = 'auto'; panel.style.top = pt0 + 'px'; panel.style.maxHeight = Math.max(160, window.innerHeight - pt0 - 12) + 'px';
    } else {
      panel.style.left = '';
      panel.style.top = Math.round(r.top + bh + 18) + 'px'; panel.style.right = Math.round(window.innerWidth - r.right + 16) + 'px';
      panel.style.maxHeight = Math.max(160, Math.round(r.height - bh - 34)) + 'px';
    }
  }
}
function openPanel(m){ pmode = (pmode === m) ? '' : m; paint(); }
function onBarClick(ev){
  var b = ev.target.closest ? ev.target.closest('[data-a]') : null; if (!b) return;
  var a = b.getAttribute('data-a'), e = sel && elByKey(sel);
  if (a === 'reset') { showWarn(); return; }
  if (a === 'edscreen') { setFocus(!focusOn()); return; }
  if (a === 'fsz') { var fz = b.closest('.dsel-fsz'); if (fz) fz.classList.toggle('dsel-open'); return; }
  if (a === 'pos' || a === 'layers' || a === 'add' || a === 'align') { openPanel(a); return; }
  if (a === 'group') { doGroup(); return; }
  if (a === 'ungroup') { doUngroup(); return; }
  if (a === 'undo') { doUndo(); return; }
  if (a === 'redo') { doRedo(); return; }
  if (a === 'edslide') { var x = $('dsce-bedit'); if (x && x.style.display !== 'none') x.click(); else toast('This slide is edited on the slide itself.'); return; }
  if (a === 'edlogo') { var y = $('dsce-blogo'); if (y) y.click(); return; }
  if (a === 'master') { var z = $('dsds-tile'); if (z) z.click(); else toast('Deck style is not available for this deck.', 'error'); return; }
  if (a === 'addslide') { if (typeof window.dspAddSlide === 'function') window.dspAddSlide(); return; }
  if (!e) return;
  if (a === 'st-' || a === 'st+') {
    var k = b.getAttribute('data-k'), inp = b.parentElement.querySelector('input'), st = parseFloat(b.getAttribute('data-s')) || 1;
    var v = (parseFloat(inp.value) || 0) + (a === 'st+' ? st : -st);
    v = Math.max(parseFloat(inp.min), Math.min(parseFloat(inp.max), v)); applyNum(k, v); return;
  }
  if (a === 'bold') { setO(sel, {wt: (e.wt || 400) >= 600 ? 400 : 700}); return; }
  if (a === 'italic') { setO(sel, {italic: !e.italic}); return; }
  if (a === 'under' || a === 'strike') { var c = a === 'under' ? 'u' : 's', d = String(e.dec || ''); d = d.indexOf(c) >= 0 ? d.replace(c, '') : d + c; setO(sel, {dec: d}); return; }
  if (a === 'caps') { setO(sel, {caps: !e.caps}); return; }
  if (a === 'talign') { openPanel('talign'); return; }
  if (a === 'color' || a === 'fill' || a === 'line' || a === 'track' || a === 'font' || a === 'weight' || a === 'spacing' || a === 'opacity' || a === 'icon' || a === 'tline' || a === 'shadow') { openPanel(a); return; }
  if (a === 'pfcrop') { openPanel('pfcrop'); return; }
if (a === 'chgpic') { openImg(sel); return; }
if (a === 'editpts') { startPts(sel); return; }
if (a === 'img') { openImg(sel); return; }
  if (a === 'crop' && e._png) { openPanel('pcrop'); return; }
  if (a === 'corners') { openPanel('corners'); return; }
  if (a === 'crop') { if (typeof window.vpStartCropEdit === 'function') window.vpStartCropEdit(curIdx()); return; }
  if (a === 'chart') { if (window.DS_CHART && DS_CHART.openEditor) DS_CHART.openEditor(curIdx()); return; }
  if (a === 'logo') { var lg = $('dsce-blogo'); if (lg) lg.click(); return; }
}
function applyNum(k, v){
  if (!sel || !isFinite(v)) return;
  var p = {}; p[k] = v; setO(sel, p);
}
function onInput(ev){
  var t = ev.target; if (!t || !t.getAttribute) return;
  var k = t.getAttribute('data-i'); if (!k) return;
  var v = parseFloat(t.value); if (!isFinite(v)) return;
  if (t.min !== '') v = Math.max(parseFloat(t.min), v); if (t.max !== '') v = Math.min(parseFloat(t.max), v);
  var e = sel && elByKey(sel);
  if (/^(px|py|pw|ph|prot)$/.test(k)) {
    if (!e) return;
    if (sset.length > 1) {
      var F = selFrame(); if (!F) return;
      if (k === 'px') moveBy(sset.slice(), v - F.x, 0); else if (k === 'py') moveBy(sset.slice(), 0, v - F.y);
      else if (k === 'pw' && F.w > 0) scaleItems(itemsOf(sset), F, {x: F.x, y: F.y, w: v, h: ratioLock ? F.h * v / F.w : F.h});
      else if (k === 'ph' && F.h > 0) scaleItems(itemsOf(sset), F, {x: F.x, y: F.y, h: v, w: ratioLock ? F.w * v / F.h : F.w});
      else if (k === 'prot') rotateItems(itemsOf(sset), F, v);
      return;
    }
    var f = frameOf(e);
    if (k === 'px') setFrame(sel, {x: v});
    else if (k === 'py') setFrame(sel, {y: (f.ty != null ? f.ty : f.y) + (v - f.y)});
    else if (k === 'pw') setFrame(sel, ratioLock && f.w > 0 && e.t !== 'text' ? {w: v, h: e._f.h * v / f.w} : {w: v});
    else if (k === 'ph') { if (e.t === 'text') return; setFrame(sel, ratioLock && f.h > 0 ? {h: v, w: f.w * v / f.h} : {h: v}); }
    else setFrame(sel, {rot: v});
    return;
  }
  applyNum(k, v);
}

/* ------------------------------------------------------------------ panels */
function head(t){ return '<div class="dsel-ph"><b>' + esc(t) + '</b><button type="button" data-p="close" title="Close">&times;</button></div>'; }
function designColors(){
  var seen = {}, out = [], pg = page();
  function add(c){ c = hex(c); if (c && !seen[c]) { seen[c] = 1; out.push(c); } }
  if (pg) { var P = (pg.T && pg.T.pal) || {}; ['accent', 'accent2', 'title', 'text', 'muted', 'bg', 'card'].forEach(function(k){ add(P[k]); });
    pg.p.els.forEach(function(e){ add(e.color); add(e.fill); add(e.line); add(e.stroke); }); }
  return out.slice(0, 18);
}
var BASE = ['#FFFFFF', '#000000', '#EF4444', '#F97316', '#F59E0B', '#22C55E', '#14B8A6', '#3B82F6', '#6366F1', '#A855F7', '#EC4899', '#64748B'];
function gradOf(target, e){ var g = target === 'fill' ? e.grad : (target === 'line' ? e.lgrad : (target === 'tline' ? e.tlgrad : (target === 'color' && (e.t === 'text' || e.t === 'icon') ? e.tgrad : null))); return g && g.stops && g.stops.length >= 2 ? g : null; }
function gradCan(target, e){ return (target === 'fill') || (target === 'line' && /^(rect|ell|shape|img)$/.test(e.t)) || (target === 'tline' && (e.t === 'text' || e.t === 'icon')) || (target === 'color' && (e.t === 'text' || e.t === 'icon')); }
function solidOf(target, e){ return target === 'fill' ? fillOf(e) : (target === 'line' ? (e.line || '') : (target === 'tline' ? (e.tline || '') : (target === 'track' ? (e.track || '#DDDDDD') : (e.t === 'path' ? (e.stroke || e.fill || '') : e.color)))); }
/* the change for "this colour": with a gradient on, it is the first of the two colours */
function colPatch(target, c, e){
  var g = e && gradOf(target, e), G = g ? {ang: g.ang != null ? g.ang : 135, stops: [[0, c], [100, hex(g.stops[g.stops.length - 1][1]) || '#FFFFFF']]} : null;
  if (target === 'fill') return G ? {fill: c, grad: G} : {fill: c, grad: null};
  if (target === 'line') return G ? {line: c, lgrad: G} : {line: c, lgrad: null};
  if (target === 'tline') return G ? {tline: c, tlgrad: G} : {tline: c, tlgrad: null};
  if (target === 'track') return {track: c};
  return G ? {color: c, tgrad: G} : (e && (e.t === 'text' || e.t === 'icon') ? {color: c, tgrad: null} : {color: c});
}
/* ---- Fill / Outline / Colour panel: none, one colour, or a gradient with any number of stops ---- */
var gSel = 0, gKey = '';
function mix(c1, c2, t){ c1 = hex(c1) || '#000000'; c2 = hex(c2) || '#FFFFFF'; var o = '#'; for (var i = 1; i < 7; i += 2) { var a = parseInt(c1.substr(i, 2), 16), b2 = parseInt(c2.substr(i, 2), 16), v = Math.round(a + (b2 - a) * t); o += (v < 16 ? '0' : '') + v.toString(16); } return o.toUpperCase(); }
function rgbaOf(c, a){ c = hex(c) || '#000000'; return 'rgba(' + parseInt(c.substr(1, 2), 16) + ',' + parseInt(c.substr(3, 2), 16) + ',' + parseInt(c.substr(5, 2), 16) + ',' + (a == null ? 1 : a) + ')'; }
function gClone(g){ var o = {ang: g.ang != null ? +g.ang : 135, stops: g.stops.map(function(s){ return [+s[0] || 0, hex(s[1]) || '#000000', s[2] != null ? +s[2] : 1]; })}; if (g.type === 'radial') o.type = 'radial'; return o; }
function curGrad(target, e){
  var g = gradOf(target, e); if (g) return gClone(g);
  var c1 = hex(solidOf(target, e)) || '#3B82F6';
  return {ang: 135, stops: [[0, c1, 1], [100, mix(c1, '#FFFFFF', 0.7), 1]]};
}
function gSorted(G){ return G.stops.slice().sort(function(a, b2){ return a[0] - b2[0]; }); }
function gBarCss(G){ return 'linear-gradient(90deg,' + gSorted(G).map(function(s){ return rgbaOf(s[1], s[2]) + ' ' + s[0] + '%'; }).join(',') + ')'; }
function gradPatch(target, G){
  var st = gSorted(G).map(function(s){ var p = Math.round(s[0] * 10) / 10, a = Math.round((s[2] != null ? s[2] : 1) * 100) / 100; return a < 1 ? [p, s[1], a] : [p, s[1]]; });
  var o = {ang: ((Math.round(+G.ang || 0) % 360) + 360) % 360, stops: st}; if (G.type === 'radial' && target === 'fill') o.type = 'radial';
  var c1 = st[0][1];
  return target === 'fill' ? {fill: c1, grad: o} : (target === 'line' ? {line: c1, lgrad: o} : (target === 'tline' ? {tline: c1, tlgrad: o} : {color: c1, tgrad: o}));
}
function quietSet(patch, tag){
  setO(sel, patch, true, tag); rerender(); sync();
  var l = bar && bar.querySelector('.dsel-left'), e2 = elByKey(sel); if (l && e2) l.innerHTML = leftHTML(e2);
}
function gPresets(c1){
  var d = designColors().filter(function(x){ return x !== c1; }), d0 = d[0] || '#3B82F6', d1 = d[1] || '#EC4899';
  return [
    {ang: 135, stops: [[0, c1, 1], [100, mix(c1, '#FFFFFF', 0.75), 1]]},
    {ang: 135, stops: [[0, c1, 1], [100, mix(c1, '#000000', 0.6), 1]]},
    {ang: 180, stops: [[0, c1, 1], [100, c1, 0]]},
    {ang: 90, stops: [[0, c1, 1], [100, d0, 1]]},
    {ang: 90, stops: [[0, d0, 1], [100, d1, 1]]},
    {ang: 90, stops: [[0, c1, 1], [50, mix(c1, '#FFFFFF', 0.6), 1], [100, c1, 1]]}
  ];
}
function gBarHTML(G){
  var srt = gSorted(G);
  return '<div class="dsel-gwrap"><div class="dsel-gbar" data-p="gbar" style="background:' + gBarCss(G) + '" title="Click to add a colour stop">'
    + srt.map(function(s, i){ return '<i class="dsel-gs' + (i === gSel ? ' dsel-on' : '') + '" data-n="' + i + '" style="left:' + s[0] + '%;background:' + s[1] + '" title="Drag to move this stop"></i>'; }).join('') + '</div></div>';
}
function colorPanel(target, e){
  var canNone = (target === 'fill' || target === 'line' || target === 'tline'), canGrad = gradCan(target, e);
  var canPic = target === 'fill' && /^(rect|ell|shape)$/.test(e.t);
  var g = canGrad ? gradOf(target, e) : null, solid = hex(solidOf(target, e)), mode = (canPic && e.pimg) ? 'pic' : (g ? 'grad' : (solid || !canNone ? 'solid' : 'none')), cur = solid || '#000000';
  var title = ({fill: 'Fill', line: 'Outline', tline: e.t === 'icon' ? 'Outline' : 'Text outline', track: 'Track colour'})[target] || (e.t === 'text' ? 'Text colour' : 'Colour');
  var key = sel + '|' + target; if (gKey !== key) { gKey = key; gSel = 0; }
  function row(list, on1){ return '<div class="dsel-sws">' + list.map(function(c){ return '<button type="button" class="dsel-swb' + (c === on1 ? ' dsel-on' : '') + '" data-p="col" data-c="' + c + '" style="background:' + c + '" title="' + c + '"></button>'; }).join('') + '</div>'; }
  function seg(v, l){ return '<button type="button" data-p="fmode" data-n="' + v + '" class="' + (mode === v ? 'dsel-on' : '') + '">' + l + '</button>'; }
  var h = head(title) + '<div class="dsel-pb">';
  if (canNone || canGrad) h += '<div class="dsel-seg">' + (canNone ? seg('none', target === 'fill' ? 'No fill' : 'None') : '') + seg('solid', 'Solid') + (canGrad ? seg('grad', 'Gradient') : '') + (canPic ? seg('pic', 'Picture') : '') + '</div>';
  if (mode === 'pic') h += picFillHTML(e);
  else if (mode === 'grad') {
    var G = gClone(g), srt = gSorted(G); if (gSel >= srt.length) gSel = srt.length - 1; if (gSel < 0) gSel = 0;
    var s = srt[gSel], rad = G.type === 'radial', pre = gPresets(srt[0][1]);
    h += '<div class="dsel-sub">Preset gradients</div><div class="dsel-gpre">' + pre.map(function(p, i){ return '<button type="button" data-p="gpre" data-n="' + i + '" style="background:linear-gradient(' + p.ang + 'deg,' + p.stops.map(function(x){ return rgbaOf(x[1], x[2]) + ' ' + x[0] + '%'; }).join(',') + ')"></button>'; }).join('') + '</div>';
    if (target === 'fill') h += '<div class="dsel-row"><span class="dsel-rl">Type</span><select data-pc="gtype"><option value="linear"' + (rad ? '' : ' selected') + '>Linear</option><option value="radial"' + (rad ? ' selected' : '') + '>Radial</option></select></div>';
    if (!rad) h += '<div class="dsel-row"><span class="dsel-rl">Direction</span><span class="dsel-dirs">' + [[90, '&rarr;'], [180, '&darr;'], [135, '&#8600;'], [45, '&#8599;'], [270, '&larr;'], [0, '&uarr;']].map(function(d){ return '<button type="button" data-p="gdir" data-n="' + d[0] + '" class="' + (Math.round(G.ang) === d[0] ? 'dsel-on' : '') + '" title="' + d[0] + '&deg;">' + d[1] + '</button>'; }).join('') + '</span></div>'
      + slider('Angle', 'gang', 0, 360, 1, Math.round(((G.ang % 360) + 360) % 360), '°');
    h += '<div class="dsel-sub">Gradient stops</div><div class="dsel-row dsel-grow">' + gBarHTML(G) + '<button type="button" class="dsel-gbt" data-p="gadd" title="Add a colour stop">+</button><button type="button" class="dsel-gbt" data-p="gdel" title="Remove the selected stop"' + (srt.length <= 2 ? ' disabled' : '') + '>&minus;</button></div>'
      + '<div class="dsel-row"><span class="dsel-rl">Colour</span><input type="color" data-pc="gsc" value="' + s[1] + '"><span class="dsel-rl" style="margin-left:6px">Position</span><input type="number" data-pc="gsp" min="0" max="100" step="1" value="' + Math.round(s[0]) + '"><span>%</span></div>'
      + slider('Transparency', 'gst', 0, 100, 1, Math.round((1 - (s[2] != null ? s[2] : 1)) * 100), '%')
      + '<div class="dsel-sub">Colours in this design</div>' + row(designColors(), s[1]) + '<div class="dsel-sub">Basic colours</div>' + row(BASE, s[1]);
  } else {
    h += '<div class="dsel-row"><input type="color" data-pc="pick" value="' + cur + '"><input type="text" data-pc="hex" value="' + cur + '" maxlength="7" spellcheck="false"></div>';
    if (mode === 'solid') {
      if (target === 'fill' || (target === 'color' && /^(text|icon|line|path)$/.test(e.t))) h += slider('Transparency', 'tr', 0, 100, 1, Math.round((1 - opOf(e)) * 100), '%');
      if (target === 'line' && /^(rect|ell|shape)$/.test(e.t)) h += slider('Transparency', 'ltr', 0, 100, 1, Math.round((1 - (e.lop != null ? e.lop : 1)) * 100), '%');
    }
    h += '<div class="dsel-sub">Colours in this design</div>' + row(designColors(), mode === 'solid' ? cur : '') + '<div class="dsel-sub">Basic colours</div>' + row(BASE, mode === 'solid' ? cur : '');
  }
  if (mode !== 'none') {
    if (target === 'line') h += '<div class="dsel-sub">Line</div>' + slider('Width', 'lw', 0.5, 40, 0.5, r1(e.lw || (e.t === 'img' ? 3 : 1)));
    if (target === 'tline') h += '<div class="dsel-sub">Line</div>' + slider('Width', 'tlw', 0.2, 12, 0.2, r1(e.tlw || 1)) + (mode === 'grad' && e.tgrad ? '<p class="dsel-note">These letters have a gradient colour too, so the outline shows in its first colour. Give the letters one colour to see the gradient outline.</p>' : '');
    if ((target === 'line' && /^(rect|ell|shape)$/.test(e.t) && mode === 'solid') || (target === 'color' && e.t === 'line')) h += '<div class="dsel-row"><span class="dsel-rl">Dash type</span><select data-pc="dash"><option value="0"' + (e.dash ? '' : ' selected') + '>Solid</option><option value="1"' + (e.dash ? ' selected' : '') + '>Dashed</option></select></div>';
    if (target === 'color' && e.t === 'line') h += slider('Width', 'lw', 0.5, 40, 0.5, r1(e.lw || 1));
  }
  return h + '</div>';
}
/* drag a gradient stop along the bar */
function gsDown(ev){
  var mk = ev.target.closest ? ev.target.closest('.dsel-gs') : null; if (!mk || !sel) return;
  var e = elByKey(sel), tg = pmode; if (!e || !gradOf(tg, e)) return;
  ev.preventDefault(); ev.stopPropagation();
  var barEl = mk.parentNode, r = barEl.getBoundingClientRect(), G = curGrad(tg, e), srt = gSorted(G), st = srt[+mk.getAttribute('data-n')], moved = false; if (!st) return;
  gSel = srt.indexOf(st);
  Array.prototype.forEach.call(barEl.querySelectorAll('.dsel-gs'), function(x){ x.classList.toggle('dsel-on', x === mk); });
  function mv(x){
    var p = Math.round(Math.max(0, Math.min(100, (x.clientX - r.left) / r.width * 100))); if (p === st[0] && !moved) return;
    st[0] = p; moved = true; mk.style.left = p + '%'; barEl.style.background = gBarCss(G);
    clearTimeout(slT); slT = setTimeout(function(){ quietSet(gradPatch(tg, G), sel + '|gdrag'); }, 60);
  }
  function up(){
    document.removeEventListener('pointermove', mv, true); document.removeEventListener('pointerup', up, true); document.removeEventListener('pointercancel', up, true);
    clearTimeout(slT); gSel = gSorted(G).indexOf(st);
    if (moved) setO(sel, gradPatch(tg, G), false, sel + '|gdrag'); else paintPanel();
  }
  document.addEventListener('pointermove', mv, true); document.addEventListener('pointerup', up, true); document.addEventListener('pointercancel', up, true);
}
/* shadow: on / off, colour, opacity, blur, distance, angle, size */
var SHDEF = {c: '#000000', o: 0.35, b: 14, d: 8, a: 45, s: 0};
function shOf(e){ if (e.sh && typeof e.sh === 'object') return e.sh; if (e.shadow) return {c: '#000000', o: typeof e.shadow === 'number' ? e.shadow : 0.18, b: 24, d: 8, a: 90, s: 0}; return null; }
function shadowPanel(e){
  var s = shOf(e), v = s || SHDEF, size = /^(rect|ell|img)$/.test(e.t);
  var h = head('Shadow') + '<div class="dsel-pb"><div class="dsel-row"><label class="dsel-chk"><input type="checkbox" data-pc="shon"' + (s ? ' checked' : '') + '> Shadow</label><input type="color" data-pc="shc" value="' + (hex(v.c) || '#000000') + '"' + (s ? '' : ' disabled') + '></div>'
    + '<div' + (s ? '' : ' style="opacity:.45;pointer-events:none"') + '>'
    + slider('Opacity', 'sh.o', 0, 100, 1, Math.round((v.o != null ? v.o : 0.35) * 100), '%') + slider('Blur', 'sh.b', 0, 80, 1, Math.round(v.b || 0)) + slider('Distance', 'sh.d', 0, 80, 1, Math.round(v.d || 0))
    + slider('Angle', 'sh.a', 0, 360, 5, Math.round(v.a || 0), '\u00B0') + (size ? slider('Size', 'sh.s', 0, 40, 1, Math.round(v.s || 0)) : '')
    + '</div><p class="dsel-note">Angle 0 throws the shadow to the right, 90 downwards.' + (size ? '' : ' Size is for boxes, circles and pictures.') + '</p></div>';
  return h;
}

function slider(label, key, min, max, step, val, unit){ return '<div class="dsel-sl"><span>' + label + '</span><input type="range" data-ps="' + key + '" min="' + min + '" max="' + max + '" step="' + step + '" value="' + val + '"><em data-pv="' + key + '">' + val + (unit || '') + '</em></div>'; }
var ICONS = ['mdi:star', 'mdi:heart', 'mdi:check-circle', 'mdi:lightbulb-on', 'mdi:rocket-launch', 'mdi:chart-bar', 'mdi:trending-up', 'mdi:account-group', 'mdi:shield-check', 'mdi:earth', 'mdi:cog', 'mdi:flash', 'mdi:target', 'mdi:trophy', 'mdi:handshake', 'mdi:calendar-check', 'mdi:email-outline', 'mdi:phone', 'mdi:map-marker', 'mdi:arrow-right-circle', 'mdi:alert-circle', 'mdi:cash-multiple', 'mdi:leaf', 'mdi:coffee', 'mdi:clock-outline', 'mdi:magnify', 'mdi:thumb-up', 'mdi:cloud-outline', 'mdi:lock', 'mdi:school'];
function iconGrid(act){ return '<div class="dsel-icons">' + ICONS.map(function(n){ return '<button type="button" data-p="' + act + '" data-n="' + n + '" title="' + n.split(':')[1] + '"><img alt="" src="https://api.iconify.design/' + n.replace(':', '/') + '.svg?color=%23334155"></button>'; }).join('') + '</div>'; }
function posPanel(e, tab){
  var h = head('Position') + '<div class="dsel-tabs"><button type="button" data-p="tab-pos" class="' + (tab === 'pos' ? 'dsel-on' : '') + '">Arrange</button><button type="button" data-p="tab-layers" class="' + (tab === 'layers' ? 'dsel-on' : '') + '">Layers</button></div><div class="dsel-pb">';
  if (tab === 'layers') return h + layersHTML() + '</div>';
  if (!e) return h + '<p class="dsel-note">Select an element on the slide first.</p></div>';
  var many = sset.length > 1, f = many ? selFrame() : frameOf(e), dis = (!many && e.t === 'text') ? ' disabled' : '';
  if (many) f.rot = 0;
  function b(a, l){ return '<button type="button" class="dsel-pbt" data-p="' + a + '">' + l + '</button>'; }
  h += '<div class="dsel-grid2">' + b('fwd', '&#8679; Forward') + b('bwd', '&#8681; Backward') + b('front', '&#10514; To front') + b('back', '&#10515; To back') + '</div>'
    + '<div class="dsel-sub">Align and distribute</div>' + alignHTML()
    + '<div class="dsel-sub">Advanced</div><div class="dsel-grid3">'
    + '<label>Width<input type="number" data-i="pw" min="1" max="4000" step="1" value="' + r1(f.w) + '"></label>'
    + '<label>Height<input type="number" data-i="ph" min="1" max="4000" step="1" value="' + r1(f.h) + '"' + dis + '></label>'
    + '<label>Ratio<button type="button" class="dsel-pbt' + (ratioLock ? ' dsel-on' : '') + '" data-p="ratio" title="Keep the proportions when the size changes">' + (ratioLock ? '&#128274;' : '&#128275;') + '</button></label>'
    + '<label>X<input type="number" data-i="px" min="-4000" max="4000" step="1" value="' + r1(f.x) + '"></label>'
    + '<label>Y<input type="number" data-i="py" min="-4000" max="4000" step="1" value="' + r1(f.y) + '"></label>'
    + '<label>Rotate<input type="number" data-i="prot" min="-360" max="360" step="1" value="' + r1(f.rot || 0) + '"' + (many || canRotate(e) ? '' : ' disabled') + '></label></div>'
    + (many ? '<p class="dsel-note">' + sset.length + ' elements are selected: these values are for all of them together. Rotate turns them by the number of degrees you type.</p>' : '')
    + '<p class="dsel-note">Sizes are in slide points: the slide is 1280 wide and 720 high.</p>';
  return h + '</div>';
}
function layerLabel(e){
  if (e.t === 'text') { var t = String(e.text || '').replace(/\s+/g, ' ').trim(); return t ? t.slice(0, 34) : 'Empty text'; }
  if (e.t === 'icon') return 'Icon · ' + String(e.name || '').split(':').pop();
  return kindName(e);
}
var POLY = {chevron: '0,0 82,0 100,50 82,100 0,100 18,50', homePlate: '0,0 82,0 100,50 82,100 0,100', triangle: '50,0 100,100 0,100', rtTriangle: '0,0 100,100 0,100', diamond: '50,0 100,50 50,100 0,50', hexagon: '25,0 75,0 100,50 75,100 25,100 0,50', parallelogram: '25,0 100,0 75,100 0,100', pentagon: '50,0 100,38 82,100 18,100 0,38'};
/* the small picture of a layer: the real shape, in its own proportions and colour */
function layerDot(e){
  if (e.t === 'img' || (e.t === 'png' && e._png.hold)) return '<i class="dsel-lt" style="color:#334155">' + IC.pic + '</i>';      /* a picture frame */
  if (e.t === 'png') { var pt = pngThumb(e._png); return '<i class="dsel-lt">' + (pt ? '<img alt="" src="' + pt + '" style="width:18px;height:18px;object-fit:contain">' : IC.pic) + '</i>'; }
  var g = ({text: 'T', chart: '&#128202;', logo: '&#127991;'})[e.t];
  if (g) return '<i class="dsel-lg">' + g + '</i>';
  if (e.t === 'icon' && /^[a-z0-9-]+:[a-z0-9-]+$/.test(e.name || '')) return '<i class="dsel-lt"><img alt="" src="https://api.iconify.design/' + e.name.replace(':', '/') + '.svg?color=%23334155"></i>';
  var S = 18, P = 1.5, b = X.bbox(e), bw = Math.max(b.w, 0.01), bh = Math.max(b.h, 0.01), k = (S - 2 * P) / Math.max(bw, bh);
  var w = Math.max(bw * k, 2), hh = Math.max(bh * k, 2), ox = (S - w) / 2, oy = (S - hh) / 2;
  /* a white or very pale outline would not show on the white list: draw it grey */
  function vis(c){ c = hex(c); if (!c) return ''; var r = parseInt(c.substr(1, 2), 16), g2 = parseInt(c.substr(3, 2), 16), b2 = parseInt(c.substr(5, 2), 16); return (0.299 * r + 0.587 * g2 + 0.114 * b2) > 200 ? '#94A3B8' : c; }
  var fill = hex(fillOf(e)), line = vis(e.line || e.stroke || ''), col = vis(e.color) || '#475569';
  var st = ' fill="' + (fill || 'none') + '" stroke="' + (fill ? '#64748B' : (line || '#475569')) + '" stroke-width="' + (fill ? 0.7 : 1.3) + '" stroke-linejoin="round" stroke-linecap="round"';
  function n2(v){ return Math.round(v * 100) / 100; }
  var body = '';
  if (e.t === 'rect') body = '<rect x="' + n2(ox) + '" y="' + n2(oy) + '" width="' + n2(w) + '" height="' + n2(hh) + '" rx="' + n2(Math.min((+e.r || 0) * k, w / 2, hh / 2)) + '"' + st + '/>';
  else if (e.t === 'ell') body = '<ellipse cx="' + S / 2 + '" cy="' + S / 2 + '" rx="' + n2(w / 2) + '" ry="' + n2(hh / 2) + '"' + st + '/>';
  else if (e.t === 'ring') body = '<circle cx="9" cy="9" r="6" fill="none" stroke="' + (hex(e.track) || '#CBD5E1') + '" stroke-width="2.6"/><path d="M9 3a6 6 0 0 1 6 6" fill="none" stroke="' + col + '" stroke-width="2.6" stroke-linecap="round"/>';
  else if (e.t === 'shape') {
    var pts = (POLY[e.prst] || '0,0 100,0 100,100 0,100').split(' ').map(function(p){ var q = p.split(','); return n2(ox + q[0] / 100 * w) + ',' + n2(oy + q[1] / 100 * hh); }).join(' ');
    body = '<polygon points="' + pts + '"' + st + '/>';
  } else if (e.t === 'line') {
    body = '<line x1="' + n2(ox + (e.x1 - b.x) * k) + '" y1="' + n2(oy + (e.y1 - b.y) * k) + '" x2="' + n2(ox + (e.x2 - b.x) * k) + '" y2="' + n2(oy + (e.y2 - b.y) * k) + '" stroke="' + col + '" stroke-width="1.6" stroke-linecap="round"/>';
  } else if (e.t === 'path') {
    var d = (e.d || []).map(function(cm){ var s = cm[0]; for (var i = 1; i + 1 < cm.length; i += 2) s += (i > 1 ? ' ' : '') + n2(ox + (cm[i] - b.x) * k) + ' ' + n2(oy + (cm[i + 1] - b.y) * k); return s; }).join('');
    var pf = hex(e.fill), ps = vis(e.stroke);
    body = '<path d="' + d + '" fill="' + (pf || 'none') + '" stroke="' + (ps || (pf ? '#64748B' : '#475569')) + '" stroke-width="' + (pf && !ps ? 0.7 : 1.3) + '" stroke-linejoin="round" stroke-linecap="round"/>';
  } else body = '<rect x="3" y="3" width="12" height="12" rx="2" fill="none" stroke="#475569" stroke-width="1.3"/>';
  return '<i class="dsel-lt"><svg viewBox="0 0 18 18" width="18" height="18" style="display:block;pointer-events:none">' + body + '</svg></i>';
}
function layerRow(e, inGroup){
  var k = esc(e._dk), lk = isLocked(e._dk);
  return '<div class="dsel-lr' + (sset.indexOf(e._dk) >= 0 ? ' dsel-on' : '') + (lk ? ' dsel-lk' : '') + (inGroup ? ' dsel-in' : '') + '"' + (e._png ? '' : ' draggable="true"') + ' data-p="pick" data-k="' + k + '">' + layerDot(e) + '<span>' + esc(layerLabel(e)) + '</span>'
    + '<button type="button" class="dsel-lkb' + (lk ? ' dsel-on' : '') + '" data-p="lock" data-k="' + k + '" title="' + (lk ? 'Locked - click to unlock' : 'Lock: cannot be selected, moved or edited on the slide') + '">' + (lk ? IC.lock : IC.unlock) + '</button>'
    + (e._png ? '<button type="button" data-p="pdel" data-k="' + k + '" title="Remove this PNG picture">' + IC.del + '</button>' : '<button type="button" data-p="hide" data-k="' + k + '" title="Hide">' + IC.eye + '</button>') + '</div>';
}
function layersHTML(){
  var a = els().slice().reverse(), pg = page(), hid = (pg && pg.p._dsHid) || [];
  if (!a.length && !hid.length) return '<p class="dsel-note">This slide has no elements.</p>';
  var h = '<p class="dsel-note">Top of the list is in front. Drag a row to reorder. Shift + click selects several. With this list open, the &uarr; and &darr; keys move the selected layer forward or backward. Double-click a group name to rename it.</p><div class="dsel-layers">';
  var done = {};
  a.forEach(function(e){
    var gi = groupId(e._dk);
    if (!gi) { h += layerRow(e, false); return; }
    if (done[gi]) return; done[gi] = 1;
    var mem = a.filter(function(x){ return groupId(x._dk) === gi; });
    if (mem.length < 2) { h += layerRow(e, false); return; }
    var allSel = mem.every(function(x){ return sset.indexOf(x._dk) >= 0; }), allLk = mem.every(function(x){ return isLocked(x._dk); }), shut = !!gcol[gi], id = esc(gi);
    h += '<div class="dsel-lr dsel-gr' + (allSel ? ' dsel-on' : '') + (shut ? ' dsel-shut' : '') + '" draggable="true" data-p="gpick" data-g="' + id + '" data-k="grp:' + id + '">'
      + '<button type="button" class="dsel-chev" data-p="gfold" data-g="' + id + '" title="' + (shut ? 'Show the elements of this group' : 'Fold this group') + '">' + IC.chev + '</button>'
      + '<span class="dsel-gn" data-g="' + id + '" title="Double-click to rename">' + esc(groupName(gi)) + '</span>'
      + '<button type="button" class="dsel-pen" data-p="gname" data-g="' + id + '" title="Rename this group">' + IC.pen + '</button>'
      + '<button type="button" class="dsel-lkb' + (allLk ? ' dsel-on' : '') + '" data-p="glock" data-g="' + id + '" title="' + (allLk ? 'Locked - click to unlock the group' : 'Lock the whole group') + '">' + (allLk ? IC.lock : IC.unlock) + '</button>'
      + '<button type="button" data-p="ghide" data-g="' + id + '" title="Hide the whole group">' + IC.eye + '</button></div>';
    if (!shut) mem.forEach(function(x){ h += layerRow(x, true); });
  });
  h += '</div>';
  if (hid.length) {
    h += '<div class="dsel-sub">Hidden</div><div class="dsel-layers">';
    hid.forEach(function(e){ h += '<div class="dsel-lr dsel-off">' + layerDot(e) + '<span>' + esc(layerLabel(e)) + '</span><button type="button" data-p="show" data-k="' + esc(e._dk) + '" title="Show again">Show</button></div>'; });
    h += '</div>';
  }
  return h;
}
function renameGroup(id){
  if (!panel) return;
  var sp = panel.querySelector('.dsel-gn[data-g="' + id + '"]'); if (!sp) return;
  var row = sp.parentNode, inp = document.createElement('input');
  inp.type = 'text'; inp.className = 'dsel-gin'; inp.maxLength = 40; inp.value = groupName(id); inp.spellcheck = false;
  row.setAttribute('draggable', 'false'); row.replaceChild(inp, sp); inp.focus(); inp.select();
  var fin = false;
  function end(save){
    if (fin) return; fin = true;
    var v = String(inp.value || '').replace(/\s+/g, ' ').trim().slice(0, 40);
    if (save && v && v !== groupName(id)) { commit(function(L){ L.gname = L.gname || {}; L.gname[id] = v; }, true); }
    try { inp.blur(); } catch (x) {}
    inp.className = ''; paintPanel();
  }
  inp.addEventListener('keydown', function(ev){ ev.stopPropagation(); if (ev.key === 'Enter') { ev.preventDefault(); end(true); } else if (ev.key === 'Escape') { ev.preventDefault(); end(false); } });
  inp.addEventListener('blur', function(){ end(true); });
  ['click', 'dblclick', 'pointerdown'].forEach(function(t){ inp.addEventListener(t, function(ev){ ev.stopPropagation(); }); });
}
function addPanel(){
  function b(k, l, ic){ return '<button type="button" class="dsel-add" data-p="new" data-n="' + k + '"><i>' + ic + '</i>' + l + '</button>'; }
  return head('Add element') + '<div class="dsel-pb"><div class="dsel-grid2">'
    + b('text', 'Text box', 'T') + b('head', 'Heading', '<b>H</b>') + b('rect', 'Rectangle', '&#9645;') + b('round', 'Rounded box', '&#9649;') + b('ell', 'Circle', '&#9711;') + b('line', 'Line', '&#9585;')
    + b('triangle', 'Triangle', '&#9651;') + b('diamond', 'Diamond', '&#9671;') + b('hexagon', 'Hexagon', '&#11041;') + b('pentagon', 'Pentagon', '&#11040;') + b('homePlate', 'Arrow', '&#10145;') + b('chevron', 'Chevron', '&#10095;') + b('draw', 'Draw', '&#9998;')
    + (typeof window.dsPngObjAdd === 'function' ? '<button type="button" class="dsel-add" data-p="newpng" title="A picture with a transparent background: a person, a product, any object"><i>' + IC.pic + '</i>PNG picture</button>' : '')
    + '</div><div class="dsel-sub">Icons</div>' + iconGrid('newicon')
    + '<p class="dsel-note">The new element appears in the middle of the slide, selected. Drag it into place.</p></div>';
}
function resetPanel(){
  return head('Reset') + '<div class="dsel-pb"><p class="dsel-note" style="margin-top:0">Reset puts the text, positions, sizes, colours and order back to how the slide was generated, and removes elements you added. Pictures you placed stay.</p>'
    + '<button type="button" class="dsel-pbt dsel-danger" data-p="rs-slide">Reset this slide</button><button type="button" class="dsel-pbt dsel-danger" data-p="rs-deck" style="margin-top:8px">Reset every slide of the deck</button>'
    + (sel && ovHas(sel) ? '<button type="button" class="dsel-pbt" data-p="rs-el" style="margin-top:8px">Reset only the selected element</button>' : '') + '</div>';
}
function paintPanel(){
  if (!panel) return;
  var ae = document.activeElement; if (ae && ae.className === 'dsel-gin' && panel.contains(ae)) return;      /* a group name is being typed */
  var e = sel && elByKey(sel), h = '';
  if (!pmode) { panel.innerHTML = ''; return; }
  if (pmode === 'pos' || pmode === 'layers') h = posPanel(e, pmode);
  else if (pmode === 'align') { if (!e) { pmode = ''; panel.innerHTML = ''; return; } h = head('Align') + '<div class="dsel-pb">' + alignHTML() + '</div>'; }
  else if (pmode === 'add') h = addPanel();
  else if (pmode === 'reset') h = resetPanel();
  else if (!e) { pmode = ''; panel.innerHTML = ''; return; }
  else if (pmode === 'color' || pmode === 'fill' || pmode === 'line' || pmode === 'track' || pmode === 'tline') h = colorPanel(pmode, e);
  else if (pmode === 'shadow') h = shadowPanel(e);
	else if (pmode === 'talign') h = e.t === 'text' ? textAlignPanel(e) : '';
  else if (pmode === 'font') {
    var list = FONTS.slice(), pg = page(), tf = pg && pg.T && pg.T.fonts; if (tf) [tf.body, tf.head].forEach(function(f){ if (f && list.indexOf(f) < 0) list.unshift(f); });
    list.forEach(needFontQuiet);
    h = head('Font') + '<div class="dsel-pb dsel-list">' + list.map(function(f){ return '<button type="button" data-p="ff" data-n="' + esc(f) + '" class="' + (e.fam === f ? 'dsel-on' : '') + '" style="font-family:\'' + esc(f) + '\',Arial,sans-serif">' + esc(f) + '</button>'; }).join('') + '</div>';
  } else if (pmode === 'weight') {
    h = head('Weight') + '<div class="dsel-pb dsel-list">' + WT.map(function(x){ return '<button type="button" data-p="wt" data-n="' + x[0] + '" class="' + (wtName(e.wt || 400) === x[1] ? 'dsel-on' : '') + '" style="font-weight:' + x[0] + '">' + x[1] + '</button>'; }).join('') + '</div>';
  } else if (pmode === 'spacing') {
    h = head('Spacing') + '<div class="dsel-pb">' + slider('Line height', 'lh', 0.7, 2.6, 0.05, r2(e.lh || 1.2)) + slider('Letter spacing', 'ls', -0.05, 0.5, 0.01, r2(e.ls || 0)) + '</div>';
  } else if (pmode === 'corners') {
    var cf = frameOf(e), cmx = Math.max(1, Math.floor(Math.min(cf.w, cf.h) / 2));
    h = head('Round corners') + '<div class="dsel-pb">' + slider('Roundness', 'r', 0, cmx, 1, Math.min(cmx, Math.round(e.r || 0))) + '<p class="dsel-note">Drag to the right for rounder corners, to the left for sharp ones.</p></div>';
  } else if (pmode === 'pfcrop') {
if (!hasPicFill(e) || e.pimg.st) { pmode = ''; panel.innerHTML = ''; pfGhost(); return; }
var pq = e.pimg;
h = head('Crop picture') + '<div class="dsel-pb">' + slider('Zoom', 'pfz', 100, 400, 5, Math.round((pq.z || 1) * 100), '%') + slider('Left \u2194 right', 'pfx', 0, 100, 1, Math.round(pq.px != null ? pq.px : 50), '%') + slider('Top \u2195 bottom', 'pfy', 0, 100, 1, Math.round(pq.py != null ? pq.py : 50), '%')
+ '<div class="dsel-grid2"><button type="button" class="dsel-pbt" data-p="pfreset">Reset</button><button type="button" class="dsel-pbt" data-p="close">Done</button></div><p class="dsel-note">Drag the picture inside the shape to move it, scroll to zoom. The faint picture shows the part that is cut away.</p></div>';
} else if (pmode === 'pcrop') {
    if (!e._png || !e._png.hold) { pmode = ''; panel.innerHTML = ''; return; }
    var cp = e._png.pos || {x: 50, y: 50};
    h = head('Crop') + '<div class="dsel-pb">' + slider('Zoom', 'zm', 100, 300, 5, Math.round((+e._png.zm || 1) * 100), '%') + slider('Left - right', 'cx', 0, 100, 1, Math.round(cp.x != null ? cp.x : 50)) + slider('Up - down', 'cy', 0, 100, 1, Math.round(cp.y != null ? cp.y : 50))
      + '<p class="dsel-note">Zoom in, then slide to choose the part of the picture that shows in the frame.</p></div>';
  } else if (pmode === 'opacity') {
    h = head('Transparency') + '<div class="dsel-pb">' + slider('Opacity', 'op', 0, 100, 1, Math.round(opOf(e) * 100), '%') + '</div>';
  } else if (pmode === 'icon') {
    h = head('Change icon') + '<div class="dsel-pb">' + iconGrid('seticon') + '<div class="dsel-sub">Any Iconify name</div><div class="dsel-row"><input type="text" data-pc="iname" value="' + esc(e.name || '') + '" placeholder="mdi:rocket" spellcheck="false"><button type="button" class="dsel-pbt" data-p="iname">Use</button></div></div>';
  }
  var sc0 = panel.querySelector('.dsel-pb'), top0 = sc0 ? sc0.scrollTop : 0, top1 = panel.scrollTop;
  panel.innerHTML = h;
  var sc1 = panel.querySelector('.dsel-pb'); if (sc1 && top0) sc1.scrollTop = top0; if (top1) panel.scrollTop = top1;
  if (pmode === 'layers' && sel) { var cur = panel.querySelector('.dsel-lr.dsel-on'); if (cur && cur.scrollIntoView) { try { cur.scrollIntoView({block: 'nearest'}); } catch (x) {} } }
  wireLayerDrag();
}
function r2(v){ return Math.round(v * 100) / 100; }
function needFontQuiet(f){ if (X.fonts[f] !== 2) { X.fonts[f] = 1; needFont(f); } }
function palOf(){ var pg = page(); return (pg && pg.T && pg.T.pal) || {}; }
function newEl(kind){
  var P = palOf(), ac = hex(P.accent) || '#3B82F6', tx = hex(P.title || P.text) || '#111111';
	/* new text reads on the slide it is added to: white on a dark slide, the template's text colour on a light one */
	var pgB = page(), bgc = pgB && pgB.p && pgB.p.bg ? hex(pgB.p.bg.fill || (pgB.p.bg.grad && pgB.p.bg.grad.stops && pgB.p.bg.grad.stops[0] && pgB.p.bg.grad.stops[0][1])) : '', dkS = false;
	if (bgc) { var lr = parseInt(bgc.substr(1, 2), 16) / 255, lg = parseInt(bgc.substr(3, 2), 16) / 255, lb = parseInt(bgc.substr(5, 2), 16) / 255; dkS = (0.2126 * lr + 0.7152 * lg + 0.0722 * lb) < 0.5; }
	if (dkS) tx = '#FFFFFF';
  if (kind === 'text') return {t: 'text', x: 440, y: 330, w: 400, h: 60, text: 'Your text', size: 26, font: 'body', color: dkS ? '#F3F5F7' : (hex(P.text) || tx), align: 'l', va: 't', lh: 1.3};
  if (kind === 'head') return {t: 'text', x: 340, y: 310, w: 600, h: 100, text: 'Your heading', size: 54, font: 'head', color: tx, align: 'l', va: 't', lh: 1.1};
  if (kind === 'rect') return {t: 'rect', x: 510, y: 280, w: 260, h: 160, fill: ac};
  if (kind === 'round') return {t: 'rect', x: 510, y: 280, w: 260, h: 160, fill: ac, r: 22};
  if (kind === 'ell') return {t: 'ell', x: 550, y: 270, w: 180, h: 180, fill: ac};
  if (kind === 'line') return {t: 'line', x1: 480, y1: 360, x2: 800, y2: 360, color: ac, lw: 3};
  return {t: 'shape', prst: kind, x: 530, y: 270, w: kind === 'homePlate' || kind === 'chevron' ? 260 : 200, h: kind === 'homePlate' || kind === 'chevron' ? 120 : 180, fill: ac};
}
/* ---- the look of one element given to the others like it ---- */
var STYK = ['fill', 'grad', 'line', 'lgrad', 'lop', 'lw', 'r', 'op', 'color', 'tgrad', 'tline', 'tlgrad', 'tlw', 'wt', 'lh', 'ls', 'italic', 'caps', 'align', 'dec', 'ff', 'dash', 'sh', 'track', 'size'];
function sigOf(e){
  if (!e || e._png || !e._o) return '';
  var o = e._o;
  if (e.t === 'text') { var k = String(e._dk || ''); if (k.indexOf('t.') !== 0) return 'T|x|' + o.font + '|' + Math.round(o.size); var ed = e.ed || {}; return 'T|' + (ed.f || k) + '|' + (ed.p != null ? ed.p : ''); }   /* the same field (and the same part of it), whatever its number */
  return [e.t, o.prst, o.fill, o.line, o.color, o.stroke, o.grad, o.r, o.frost].join('|');
}
function similarOn(idx, src){
  var pg = X.pages[idx], sg = sigOf(src); if (!pg || !sg) return [];
  var vid = (window.S && S.choices && S.choices['slide_' + idx]) || 'v1'; if (pg.vid !== vid) return [];
  return pg.p.els.filter(function(x){ return x._dk && !x._fol && !(idx === curIdx() && x._dk === src._dk) && sigOf(x) === sg; });
}
function styleOf(key){ var o = ovOf(key), p = {}, n = 0; STYK.forEach(function(k){ if (o[k] != null) { p[k] = JSON.parse(JSON.stringify(o[k])); n++; } }); return n ? p : null; }
function applyStyle(all){
  var src = sel && elByKey(sel), d = deck(); if (!src || !d) return;
  var p = styleOf(sel); if (!p) { toast('Change this element first (colour, font, outline, shadow...), then give its look to the others.', 'error'); return; }
  var ratio = (p.size && src._o && src._o.size) ? p.size / src._o.size : null, n = 0, slidesN = 0, none = [];
  d.slides.forEach(function(s, i){
    if (!all && i !== curIdx()) return;
    var list = similarOn(i, src); if (!list.length) { if (i !== curIdx()) none.push(i + 1); return; }
    var vid = (window.S && S.choices && S.choices['slide_' + i]) || 'v1';
    pushUndo(i);
    var L = X.layer(s, vid, true);
    list.forEach(function(x){
      var o = L.m[x._dk] || (L.m[x._dk] = {});
      STYK.forEach(function(k){ delete o[k]; });
      for (var k in p) { if (k === 'size') { if (ratio && x._o && x._o.size) o.size = Math.max(6, Math.round(x._o.size * ratio * 10) / 10); } else o[k] = JSON.parse(JSON.stringify(p[k])); }
      n++;
    });
    slidesN++; mirror(i);
  });
  if (!n) { toast('Nothing similar was found.', 'error'); return; }
  try { if (all && D.refreshEditor) D.refreshEditor(); else rerender(); } catch (x) {}
  try { if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs(); } catch (x2) {}
  paint();
  var miss = !all || !none.length ? '' : (none.length <= 8 ? ' Slide' + (none.length > 1 ? 's ' : ' ') + none.join(', ') + (none.length > 1 ? ' have' : ' has') + ' no element of this kind, so nothing changed there' : ' ' + none.length + ' slides have no element of this kind, so nothing changed there') + ' - use "Copy effects" and Ctrl + V to put the look on any other element.';
  toast('Applied to ' + n + ' element' + (n > 1 ? 's' : '') + (all ? ' on ' + slidesN + ' slide' + (slidesN > 1 ? 's' : '') : ' on this slide') + '.' + miss);
}
/* "Copy effects": the whole look of one element; Ctrl + V then puts it on the selected element(s), on any slide */
var fxClip = null, clipMode = '';
var FXK = {text: ['color', 'tgrad', 'tline', 'tlgrad', 'tlw', 'wt', 'italic', 'caps', 'dec', 'ff', 'ls', 'lh', 'sh', 'op'], box: ['fill', 'grad', 'line', 'lgrad', 'lw', 'lop', 'dash', 'r', 'sh', 'op'], line: ['color', 'lw', 'dash', 'sh', 'op'], path: ['color', 'lw', 'sh', 'op'], icon: ['color', 'tgrad', 'tline', 'tlgrad', 'tlw', 'sh', 'op'], img: ['line', 'lgrad', 'lw', 'r', 'sh', 'op'], logo: ['sh', 'op'], ring: ['color', 'track']};
function lookOf(e){
  var f = fam(e), p = {}, sh = shOf(e);
  function cl(v){ return v == null ? null : JSON.parse(JSON.stringify(v)); }
  if (f === 'text') p = {color: hex(e.color) || null, tgrad: e.tgrad ? cl(e.tgrad) : null, tline: e.tline || '', tlgrad: e.tline && e.tlgrad ? cl(e.tlgrad) : null, tlw: e.tlw || null, wt: e.wt || 400, italic: !!e.italic, caps: !!e.caps, dec: e.dec || '', ff: e.fam || null, ls: e.ls || 0, lh: e.lh || null, op: e.op != null ? e.op : 1};
  else if (f === 'box') p = {fill: hex(fillOf(e)) || '', grad: e.grad && e.grad.stops ? cl(e.grad) : null, line: hex(e.line) || '', lgrad: e.lgrad ? cl(e.lgrad) : null, lw: e.line ? (e.lw || 1) : null, lop: e.line && e.lop != null ? e.lop : null, dash: !!e.dash, r: e.t === 'rect' ? (e.r || 0) : null, op: e.op != null ? e.op : 1};
  else if (f === 'line') p = {color: hex(e.color) || null, lw: e.lw || 1, dash: !!e.dash, op: e.op != null ? e.op : 1};
  else if (f === 'path') p = {color: hex(e.stroke || e.fill) || null, lw: e.lw || null, op: opOf(e)};
  else if (f === 'icon') p = {color: hex(e.color) || null, tgrad: e.tgrad ? cl(e.tgrad) : null, tline: e.tline || '', tlgrad: e.tline && e.tlgrad ? cl(e.tlgrad) : null, tlw: e.tlw || null, op: e.op != null ? e.op : 1};
  else if (f === 'img') p = {line: hex(e.line) || '', lgrad: e.line && e.lgrad ? cl(e.lgrad) : null, lw: e.line ? (e.lw || 3) : null, r: e.shape === 'ell' ? null : (e.r || 0), op: e.op != null ? e.op : 1};
  else if (f === 'ring') p = {color: hex(e.color) || null, track: hex(e.track) || null};
  else p = {op: e.op != null ? e.op : 1};
  p.sh = sh ? cl(sh) : {off: 1};
  return {fam: f, p: p};
}
function copyFx(){
  var e = sel && elByKey(sel); if (!e || e._png) { toast('Select one element (not a PNG picture or copied picture frame) to copy its effects.', 'error'); return; }
  fxClip = lookOf(e); clipMode = 'fx';
  toast('Effects copied. Select another element - on any slide - and press Ctrl + V to give it this look.');
}
function pasteFx(){
  if (!fxClip) { toast('No effects copied yet: use the brush button, then "Copy effects".', 'error'); return; }
  norm(); var list = selEls().filter(function(e){ return !e._png; });
  if (!list.length) { toast('Select the element that should get the copied effects, then press Ctrl + V.', 'error'); return; }
  var n = 0;
  commit(function(L){
    list.forEach(function(e){
      var keys = FXK[fam(e)] || [], o = L.m[e._dk] || (L.m[e._dk] = {}), did = false;
      keys.forEach(function(k){
        if (!fxClip.p.hasOwnProperty(k)) return;
        var v = fxClip.p[k];
        if (k === 'r' && e.t !== 'rect' && e.t !== 'img') return;
        if (v === null) delete o[k]; else o[k] = (v && typeof v === 'object') ? JSON.parse(JSON.stringify(v)) : v;
        did = true;
      });
      if (did) n++;
    });
  });
  toast(n ? 'Effects pasted on ' + n + ' element' + (n > 1 ? 's' : '') + '.' + (list.some(function(e){ return fam(e) !== fxClip.fam; }) ? ' Only what fits this kind of element was used.' : '') : 'Nothing of the copied effects fits this kind of element.', n ? 'success' : 'error');
}
function applyCounts(){
  var src = sel && sset.length === 1 && elByKey(sel), d = deck(), here = 0, all = 0; if (!src || !d) return {here: 0, all: 0};
  d.slides.forEach(function(s, i){ var c = similarOn(i, src).length; all += c; if (i === curIdx()) here = c; });
  return {here: here, all: all};
}
function applyItems(){
  var c = applyCounts(), it = [];
  if (c.here > 0) it.push({l: 'Apply this look to similar on this slide (' + c.here + ')', f: function(){ applyStyle(false); }});
  if (c.all > c.here) it.push({l: 'Apply this look to every slide (' + c.all + ')', f: function(){ applyStyle(true); }});
  return it;
}
function fxItems(){
  var e = sel && sset.length === 1 && elByKey(sel), it = [];
  if (e && !e._png) it.push({l: 'Copy effects', f: copyFx});
  if (fxClip) it.push({l: 'Paste effects', key: clipMode === 'fx' ? 'Ctrl+V' : '', f: pasteFx});
  return it;
}
function applyMenu(x, y){
  var it = applyItems(), fx = fxItems();
  if (it.length && fx.length) it.push('-');
  it = it.concat(fx);
  if (!it.length) return;
  openMenu(x, y, it);
}
/* ---- copy and paste: to this slide or to any other ---- */
var clip = null;
function doCopy(quiet){
  norm(); var z = keyOrder(), a = selEls(); if (!a.length) return false;
  a.sort(function(p, q){ return z.indexOf(p._dk) - z.indexOf(q._dk); });
  var items = [], skipped = 0, s = curSlide();
  a.forEach(function(e){
    if (e._png) { var b = e._png, c = {}; ['src', 'x', 'y', 'w', 'h', 'rr', 'hold', 'o', 'op', 'zm'].forEach(function(k){ if (b[k] != null) c[k] = b[k]; }); if (b.pos) c.pos = {x: b.pos.x, y: b.pos.y}; items.push({kind: 'png', b: c}); return; }
    if (e.t === 'img') { var f = frameOf(e); items.push({kind: 'img', src: (s && s.image_data) || '', f: {x: f.x, y: f.y, w: f.w, h: f.h}, r: e.shape === 'ell' ? 9999 : (+e.r || 0), pos: s && s.image_position ? {x: +s.image_position.x || 0, y: +s.image_position.y || 0} : {x: 50, y: 50}}); return; }
    if (/^(chart|logo|pic)$/.test(e.t)) { skipped++; return; }
    var pe = plain(e); if (e.t === 'text' && !pe.ff && e.fam) pe.ff = e.fam;
    items.push({kind: 'el', e: JSON.parse(JSON.stringify(pe))});
  });
  if (!items.length) { toast('This kind of element cannot be copied.', 'error'); return false; }
  clip = {from: curIdx(), items: items, grouped: oneGroup(), n: 0}; clipMode = 'obj';
  if (!quiet) toast(items.length + ' element' + (items.length > 1 ? 's' : '') + ' copied. Paste here or on any other slide (Ctrl + V).' + (skipped ? ' Charts and the logo are not copied.' : ''));
  return true;
}
function doPaste(){
  if (clipMode === 'fx') { pasteFx(); return; }
  if (!clip || !clip.items.length) { toast('Nothing to paste yet: select an element and copy it first (Ctrl + C).', 'error'); return; }
  var same = clip.from === curIdx(); if (same || clip.n > 0) clip.n++; else clip.n = 0.0001;
  var d = same || clip.n >= 1 ? 24 * Math.max(1, Math.floor(clip.n)) : 0, made = [], full = false, imgs = [];
  if (!same && clip.n < 1) clip.n = 0.5;
  commit(function(L, s){
    clip.items.forEach(function(it){
      if (it.kind === 'img') { imgs.push(it); return; }
      if (it.kind === 'png') {
        if (!Array.isArray(s.png_objects)) s.png_objects = [];
        if (s.png_objects.length >= 6) { full = true; return; }
        var b = JSON.parse(JSON.stringify(it.b)); b.x = Math.round((+b.x || 0) + d); b.y = Math.round((+b.y || 0) + d); s.png_objects.push(b); made.push('png' + (s.png_objects.length - 1)); return;
      }
      var c = JSON.parse(JSON.stringify(it.e));
      if (c.t === 'line') { c.x1 += d; c.x2 += d; c.y1 += d; c.y2 += d; }
      else if (c.t === 'path') c.d = (c.d || []).map(function(x){ var n = [x[0]]; for (var i = 1; i < x.length; i++) n.push(x[i] + d); return n; });
      else { c.x = (+c.x || 0) + d; c.y = (+c.y || 0) + d; }
      L.n = (L.n || 0) + 1; var key = 'u' + L.n; L.add.push({k: key, e: c}); made.push(key);
    });
    if (clip.grouped && made.length > 1) { L.g = L.g || {}; L.gn = (L.gn || 0) + 1; L.g['g' + L.gn] = made.slice(); }
  }, true);
  pngSeen = pngList(curSlide()).length;
  rerender();
  if (made.length) { sset = made.slice(); sel = made[made.length - 1]; }
  paint();
  imgs.forEach(function(it){
    srcFit(it.src, function(fit){ holderPng(fit, it.f.w, it.f.h, it.r, it.pos, function(data){
      if (!data) return;
      var key = null;
      commit(function(L, s2){ if (!Array.isArray(s2.png_objects)) s2.png_objects = []; if (s2.png_objects.length >= 6) return; s2.png_objects.push({src: data, x: Math.round(it.f.x + d), y: Math.round(it.f.y + d), w: Math.round(it.f.w), h: Math.round(it.f.h), rr: it.r, hold: 1, o: fit || '', pos: it.pos}); key = 'png' + (s2.png_objects.length - 1); }, true);
      rerender(); pngSeen = pngList(curSlide()).length; if (key) setSel(made.concat([key])); else toast('A slide can hold 6 extra pictures.', 'error');
    }); });
  });
  if (full) toast('A slide can hold 6 extra pictures: not every picture was pasted.', 'error');
  else if (made.length || imgs.length) toast('Pasted.');
}
/* ---- Space + drag moves the whole canvas; Home puts it back in the middle ---- */
var spaceDn = false, pan = null, panXY = {x: 0, y: 0};
function applyPan(){
  var o = $('dsp-ppt-slide-outer'); if (!o) return;
  var v = (panXY.x || panXY.y) ? (Math.round(panXY.x) + 'px ' + Math.round(panXY.y) + 'px') : '';
  if (o.style.translate !== v) o.style.translate = v;
  var hb = $('dsel-home'); if (hb) hb.style.display = v ? '' : 'none';
}
function resetPan(){ panXY = {x: 0, y: 0}; applyPan(); }
function setSpace(v){ spaceDn = v; document.documentElement.classList.toggle('dsel-pan', v || !!pan); }
window.addEventListener('keyup', function(ev){ if (ev.key === ' ' || ev.code === 'Space') setSpace(false); }, true);
window.addEventListener('blur', function(){ pan = null; setSpace(false); });
window.addEventListener('pointerdown', function(ev){
  if (!spaceDn || !on() || ev.button !== 0) return;
  var area = $('dsp-ppt-canvas-area'), t = ev.target; if (!area || !t || !area.contains(t)) return;
  ev.preventDefault(); ev.stopImmediatePropagation();
  pan = {x0: ev.clientX, y0: ev.clientY, px: panXY.x, py: panXY.y}; closeMenu();
  document.documentElement.classList.add('dsel-pan', 'dsel-panning');
}, true);
window.addEventListener('mousedown', function(ev){ if ((spaceDn || pan) && on()) { var a = $('dsp-ppt-canvas-area'); if (a && a.contains(ev.target)) { ev.preventDefault(); ev.stopImmediatePropagation(); } } }, true);
window.addEventListener('pointermove', function(ev){
  if (!pan) return;
  ev.preventDefault(); ev.stopImmediatePropagation();
  panXY = {x: pan.px + ev.clientX - pan.x0, y: pan.py + ev.clientY - pan.y0}; applyPan();
}, true);
function endPan(ev){ if (!pan) return; pan = null; document.documentElement.classList.remove('dsel-panning'); if (!spaceDn) document.documentElement.classList.remove('dsel-pan'); if (ev) { ev.stopImmediatePropagation(); } eatClick = true; setTimeout(function(){ eatClick = false; }, 80); sync(); }
window.addEventListener('pointerup', endPan, true); window.addEventListener('pointercancel', endPan, true);
/* ---- the list of keyboard shortcuts ---- */
var KEYS = [
  ['Select', [['Click', 'Select an element'], ['Shift + click', 'Add to, or take out of, the selection'], ['Drag on an empty place', 'Select everything inside the box'], ['Ctrl + A', 'Select everything on the slide'], ['Esc', 'Clear the selection, close a menu']]],
  ['Edit', [['Ctrl + C', 'Copy'], ['Ctrl + X', 'Cut'], ['Ctrl + V', 'Paste - on this slide or any other'], ['Ctrl + D', 'Duplicate'], ['Delete / Backspace', 'Delete'], ['Ctrl + S', 'Save the presentation'], ['Ctrl + Z', 'Undo'], ['Ctrl + Y  or  Ctrl + Shift + Z', 'Redo'], ['Brush button, then Copy effects', 'Copy the look of an element; then select another one and press Ctrl + V'], ['Right click', 'Menu with all the actions'], ['Shift + Enter', 'New line while typing in a text']]],
  ['Move and size', [['Arrow keys', 'Move by 1'], ['Shift + arrow keys', 'Move by 10'], ['Shift while dragging', 'Move straight: only sideways or only up / down'], ['Alt while dragging', 'No snapping to the middle; a box moves without what lies on it'], ['Shift while resizing', 'Keep the proportions'], ['Shift while turning', 'Turn in steps of 15\u00B0']]],
  ['Arrange', [['Ctrl + G', 'Group'], ['Ctrl + Shift + G', 'Ungroup'], ['Ctrl + ]', 'Bring forward'], ['Ctrl + [', 'Send backward'], ['Ctrl + Shift + ]', 'Bring to front'], ['Ctrl + Shift + [', 'Send to back'], ['\u2191 / \u2193 with Layers open', 'Move the layer forward / backward']]],
  ['View', [['Space + drag', 'Move the whole canvas'], ['Home', 'Put the canvas back in the middle'], ['?', 'Show this list']]]
];
var helpEl = null;
function closeHelp(){ if (helpEl) { helpEl.remove(); helpEl = null; } }
function showHelp(){
  var fs = fsEl(); if (!fs) return; if (helpEl) { closeHelp(); return; }
  helpEl = document.createElement('div'); helpEl.id = 'dsel-help';
  helpEl.innerHTML = '<div class="dsel-hbox" role="dialog" aria-modal="true"><div class="dsel-hh"><b>Keyboard shortcuts</b><button type="button" data-x="1" title="Close">&times;</button></div><div class="dsel-hb">'
    + KEYS.map(function(g){ return '<div class="dsel-hg"><h4>' + esc(g[0]) + '</h4>' + g[1].map(function(r){ return '<div class="dsel-hr"><kbd>' + esc(r[0]) + '</kbd><span>' + esc(r[1]) + '</span></div>'; }).join('') + '</div>'; }).join('')
    + '</div><p class="dsel-hn">On a Mac use Cmd in place of Ctrl.</p></div>';
  helpEl.addEventListener('click', function(ev){ if (ev.target === helpEl || (ev.target.closest && ev.target.closest('[data-x]'))) closeHelp(); });
  fs.appendChild(helpEl);
}
function ensureCorner(){
  var fs = fsEl(); if (!fs) return null;
  var k = $('dsel-keys'); if (k && k.parentElement === fs) return k;
  k = document.createElement('div'); k.id = 'dsel-keys';
  k.innerHTML = '<button type="button" id="dsel-home" title="Put the canvas back in the middle (Home)" style="display:none">' + svg('<circle cx="12" cy="12" r="3"/><path d="M12 3v4M12 17v4M3 12h4M17 12h4"/>') + '<span>Centre</span></button>'
    + '<button type="button" id="dsel-kbtn" title="Keyboard shortcuts (?)">' + svg('<rect x="2.5" y="6" width="19" height="12" rx="2.5"/><path d="M6.5 10h.01M10 10h.01M13.5 10h.01M17 10h.01M7.5 14h9"/>') + '<span>Shortcuts</span></button>';
  k.addEventListener('click', function(ev){ var b = ev.target.closest ? ev.target.closest('button') : null; if (!b) return; if (b.id === 'dsel-home') resetPan(); else showHelp(); });
  fs.appendChild(k); return k;
}
/* ---- a small menu: right click on the slide, and the "apply this look" button ---- */
var menuEl = null;
function closeMenu(){ if (menuEl) { menuEl.remove(); menuEl = null; } }
function openMenu(x, y, items){
  var fs = fsEl(); if (!fs) return; closeMenu();
  menuEl = document.createElement('div'); menuEl.id = 'dsel-menu';
  menuEl.innerHTML = items.map(function(it, i){ return it === '-' ? '<i></i>' : '<button type="button" data-i="' + i + '"' + (it.dis ? ' disabled' : '') + '><span>' + esc(it.l) + '</span>' + (it.key ? '<em>' + esc(it.key) + '</em>' : '') + '</button>'; }).join('');
  menuEl.addEventListener('click', function(ev){ var b = ev.target.closest ? ev.target.closest('button[data-i]') : null; if (!b || b.disabled) return; var it = items[+b.getAttribute('data-i')]; closeMenu(); try { it.f(); } catch (x) { if (window.console) console.warn('[DSEL]', x); } });
  menuEl.addEventListener('contextmenu', function(ev){ ev.preventDefault(); });
  fs.appendChild(menuEl);
  var w = menuEl.offsetWidth, hh = menuEl.offsetHeight;
  menuEl.style.left = Math.max(6, Math.min(x, window.innerWidth - w - 6)) + 'px'; menuEl.style.top = Math.max(6, Math.min(y, window.innerHeight - hh - 6)) + 'px';
}
document.addEventListener('pointerdown', function(ev){ if (menuEl && !menuEl.contains(ev.target)) closeMenu(); }, true);
window.addEventListener('blur', closeMenu); window.addEventListener('resize', closeMenu);
document.addEventListener('contextmenu', function(ev){
  if (!on()) return;
  var t = ev.target, m = mainEl(), area = $('dsp-ppt-canvas-area'); if (!t || !t.closest || !m) return;
  if (!(m.contains(t) || (ov && ov.contains(t)) || (area && area.contains(t) && !t.closest('button,a,input,select,textarea')))) return;
  var ce = t.closest('[contenteditable="true"]'); if (ce && document.activeElement === ce) return;          /* typing in a text: the browser's own menu */
  ev.preventDefault();
  norm();
  var hit = hitTest(toSlide(ev));
  if (hit) { if (sset.indexOf(hit._dk) < 0) setSel(expand(hit._dk), hit._dk); }
  else if (!(ov && ov.contains(t) && sset.length)) { if (sel) select(null); }
  var e = sel && elByKey(sel), it = [];
  if (e) {
    var many = sset.length > 1, og = oneGroup();
    it.push({l: 'Copy', key: 'Ctrl+C', f: function(){ doCopy(); }}, {l: 'Paste', key: 'Ctrl+V', dis: !clip, f: doPaste}, {l: 'Cut', key: 'Ctrl+X', f: function(){ if (doCopy(true)) removeMany(sset.slice()); }}, {l: 'Duplicate', key: 'Ctrl+D', f: function(){ dupMany(sset.slice()); }}, {l: 'Delete', key: 'Del', f: function(){ removeMany(sset.slice()); }}, '-');
    it.push({l: 'Bring forward', key: 'Ctrl+]', f: function(){ arrange('fwd'); }}, {l: 'Send backward', key: 'Ctrl+[', f: function(){ arrange('bwd'); }}, {l: 'Bring to front', f: function(){ arrange('front'); }}, {l: 'Send to back', f: function(){ arrange('back'); }}, '-');
    if (many && !og) it.push({l: 'Group', key: 'Ctrl+G', f: doGroup});
    if (hasGroup()) it.push({l: 'Ungroup', key: 'Ctrl+Shift+G', f: doUngroup});
    it.push({l: 'Align and distribute...', f: function(){ pmode = 'align'; paint(); }}, {l: many ? 'Lock these elements' : 'Lock', f: function(){ setLock(sset.slice(), true); }});
    if (!many) {
      var ai = applyItems().concat(fxItems()); if (ai.length) { it.push('-'); ai.forEach(function(x){ it.push(x); }); }
      it.push('-');
      if (e.t === 'img' || (e._png && e._png.hold)) it.push({l: 'Change image...', f: function(){ openImg(sel); }});
      if (/^(rect|ell|shape)$/.test(e.t)) it.push({l: 'Fill...', f: function(){ pmode = 'fill'; paint(); }}, {l: 'Outline...', f: function(){ pmode = 'line'; paint(); }});
      if (e.t === 'text') it.push({l: 'Text colour...', f: function(){ pmode = 'color'; paint(); }}, {l: 'Text outline...', f: function(){ pmode = 'tline'; paint(); }}, {l: 'Font...', f: function(){ pmode = 'font'; paint(); }});
      if (/^(rect|ell|shape|text|img|icon|line|path|logo)$/.test(e.t)) it.push({l: 'Shadow...', f: function(){ pmode = 'shadow'; paint(); }});
    } else { if (fxClip) it.push('-', {l: 'Paste effects', key: clipMode === 'fx' ? 'Ctrl+V' : '', f: pasteFx}); it.push('-'); }
    it.push({l: 'Position and size...', f: function(){ pmode = 'pos'; paint(); }}, {l: 'Layers...', f: function(){ pmode = 'layers'; paint(); }});
    if (!many && ovHas(sel) && !e._png) it.push('-', {l: 'Reset this element', f: function(){ resetEl(sel); }});
  } else {
    it.push({l: 'Paste', key: 'Ctrl+V', dis: !clip, f: doPaste}, {l: 'Select all', key: 'Ctrl+A', f: function(){ setSel(live1().map(function(x){ return x._dk; })); }}, '-', {l: 'Add element...', f: function(){ pmode = 'add'; paint(); }}, {l: 'Layers...', f: function(){ pmode = 'layers'; paint(); }});
  }
  openMenu(ev.clientX, ev.clientY, it);
}, true);
/* ---- align and distribute. One element, or one group: to the slide. Several: to each other, or to the slide (the user's choice). ---- */
var alignPref = 'sel';
function alignUnits(){
  var seen = {}, out = [];
  sset.forEach(function(k){
    if (seen[k]) return;
    var gid = groupId(k), ks = [k];
    if (gid) { var g = groupKeys(gid).filter(function(q){ return sset.indexOf(q) >= 0; }); if (g.length > 1) ks = g; }
    ks.forEach(function(q){ seen[q] = 1; });
    var a = ks.map(elByKey).filter(Boolean); if (!a.length) return;
    out.push({keys: ks, f: bboxOf(a.map(frameOf))});
  });
  return out;
}
function alignScope(u){ u = u || alignUnits(); return (u.length > 1 && alignPref === 'sel') ? 'sel' : 'page'; }
function moveMany(list){
  list = list.filter(function(m){ return Math.abs(m.dx) > 0.05 || Math.abs(m.dy) > 0.05; });
  if (!list.length) return;
  commit(function(L){
    list.forEach(function(m){ m.keys.forEach(function(k){
      var pb = pngOf(k); if (pb) { pb.x = Math.round((+pb.x || 0) + m.dx); pb.y = Math.round((+pb.y || 0) + m.dy); return; } if (isPng(k)) return;
      var o = L.m[k] || (L.m[k] = {}); o.dx = r1((+o.dx || 0) + m.dx); o.dy = r1((+o.dy || 0) + m.dy);
    }); });
  });
}
function doAlign(how){
  var u = alignUnits(); if (!u.length) return;
  var B = alignScope(u) === 'sel' ? bboxOf(u.map(function(x){ return x.f; })) : {x: 0, y: 0, w: W, h: H};
  moveMany(u.map(function(x){
    var f = x.f, dx = 0, dy = 0;
    if (how === 'l') dx = B.x - f.x; else if (how === 'c') dx = B.x + (B.w - f.w) / 2 - f.x; else if (how === 'r') dx = B.x + B.w - f.w - f.x;
    else if (how === 't') dy = B.y - f.y; else if (how === 'm') dy = B.y + (B.h - f.h) / 2 - f.y; else if (how === 'b') dy = B.y + B.h - f.h - f.y;
    return {keys: x.keys, dx: dx, dy: dy};
  }));
}
function doDistribute(ax){
  var u = alignUnits(); if (!u.length) return;
  var sc = alignScope(u), hz = ax === 'h';
  if (sc === 'sel' && u.length < 3) { toast('Select three or more elements to space them evenly between the first and the last - or choose "Slide" to spread them across the slide.', 'error'); return; }
  u.sort(function(a, b){ return hz ? (a.f.x - b.f.x) : (a.f.y - b.f.y); });
  var sum = 0; u.forEach(function(x){ sum += hz ? x.f.w : x.f.h; });
  var B = sc === 'sel' ? bboxOf(u.map(function(x){ return x.f; })) : {x: 0, y: 0, w: W, h: H};
  var span = hz ? B.w : B.h, start = hz ? B.x : B.y, gap = sc === 'sel' ? (span - sum) / (u.length - 1) : (span - sum) / (u.length + 1), pos = sc === 'sel' ? start : start + gap;
  moveMany(u.map(function(x){ var d = pos - (hz ? x.f.x : x.f.y); pos += (hz ? x.f.w : x.f.h) + gap; return {keys: x.keys, dx: hz ? d : 0, dy: hz ? 0 : d}; }));
}
var AIC = {
  l: '<path d="M4 3v18"/><rect x="7" y="6" width="12" height="4" rx="1"/><rect x="7" y="14" width="7" height="4" rx="1"/>',
  c: '<path d="M12 3v18"/><rect x="5" y="6" width="14" height="4" rx="1"/><rect x="8" y="14" width="8" height="4" rx="1"/>',
  r: '<path d="M20 3v18"/><rect x="5" y="6" width="12" height="4" rx="1"/><rect x="10" y="14" width="7" height="4" rx="1"/>',
  t: '<path d="M3 4h18"/><rect x="6" y="7" width="4" height="12" rx="1"/><rect x="14" y="7" width="4" height="7" rx="1"/>',
  m: '<path d="M3 12h18"/><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="8" width="4" height="8" rx="1"/>',
  b: '<path d="M3 20h18"/><rect x="6" y="5" width="4" height="12" rx="1"/><rect x="14" y="10" width="4" height="7" rx="1"/>',
  h: '<path d="M4 3v18M20 3v18"/><rect x="9.5" y="7" width="5" height="10" rx="1"/>',
  v: '<path d="M3 4h18M3 20h18"/><rect x="7" y="9.5" width="10" height="5" rx="1"/>'
};
/* Text alignment panel (Left / Centre / Right of the words inside a text box) - separate from Align (element position) */
function textAlignPanel(e){
	var cur = e.align === 'c' || e.align === 'r' ? e.align : 'l';
	function b(k, l){ return '<button type="button" class="dsel-pbt dsel-al dsel-w2' + (cur === k ? ' dsel-on' : '') + '" data-p="ta" data-n="' + k + '" title="Align text ' + l.toLowerCase() + '">' + alignIcon(k) + '<span>' + l + '</span></button>'; }
	return head('Text alignment') + '<div class="dsel-pb"><div class="dsel-grid2">' + b('l', 'Left') + b('c', 'Centre') + b('r', 'Right') + '</div></div>';
}
function alignHTML(){
  var u = alignUnits(), choose = u.length > 1, sc = alignScope(u);
  function b(a, k, l, wide){ return '<button type="button" class="dsel-pbt dsel-al' + (wide ? ' dsel-w2' : '') + '" data-p="' + a + '" title="' + (wide ? l : 'Align ' + l.toLowerCase()) + '">' + svg(AIC[k]) + '<span>' + l + '</span></button>'; }
  var note = !choose ? (oneGroup() ? 'A group is lined up with the slide as one piece.' : 'One element is lined up with the slide. Select several to line them up with each other.')
    : (sc === 'sel' ? 'The selected elements are lined up with each other.' : 'Each selected element is lined up with the slide.');
  return '<div class="dsel-seg"><button type="button" data-p="amode" data-n="sel" class="' + (sc === 'sel' ? 'dsel-on' : '') + '"' + (choose ? '' : ' disabled') + '>Selected elements</button><button type="button" data-p="amode" data-n="page" class="' + (sc === 'page' ? 'dsel-on' : '') + '"' + (choose ? '' : ' disabled') + '>Slide</button></div>'
    + '<p class="dsel-note" style="margin:6px 0 8px">' + note + '</p>'
    + '<div class="dsel-grid2">' + b('al-l', 'l', 'Left') + b('al-t', 't', 'Top') + b('al-c', 'c', 'Centre') + b('al-m', 'm', 'Middle') + b('al-r', 'r', 'Right') + b('al-b', 'b', 'Bottom')
    + b('ds-h', 'h', 'Distribute horizontally', 1) + b('ds-v', 'v', 'Distribute vertically', 1) + '</div>';
}
function alignTo(how){
  var e = sel && elByKey(sel); if (!e) return;
  if (sset.length > 1) {
    var F = selFrame(), mx = 0, my = 0;
    if (how === 'l') mx = -F.x; else if (how === 'c') mx = (W - F.w) / 2 - F.x; else if (how === 'r') mx = W - F.w - F.x;
    else if (how === 't') my = -F.y; else if (how === 'm') my = (H - F.h) / 2 - F.y; else if (how === 'b') my = H - F.h - F.y;
    moveBy(sset.slice(), mx, my); return;
  }
  var f = frameOf(e), top = (f.ty != null ? f.ty : f.y);
  if (how === 'l') setFrame(sel, {x: 0}); else if (how === 'c') setFrame(sel, {x: (W - f.w) / 2}); else if (how === 'r') setFrame(sel, {x: W - f.w});
  else if (how === 't') setFrame(sel, {y: top + (0 - f.y)}); else if (how === 'm') setFrame(sel, {y: top + ((H - f.h) / 2 - f.y)}); else if (how === 'b') setFrame(sel, {y: top + (H - f.h - f.y)});
}
function setColor(target, c){
  if (!sel) return;
  setO(sel, colPatch(target, c, elByKey(sel)));
}
function onPanelClick(ev){
  var b = ev.target.closest ? ev.target.closest('[data-p]') : null; if (!b) return;
  var a = b.getAttribute('data-p'), e = sel && elByKey(sel), n = b.getAttribute('data-n');
  if (a === 'close') { pmode = ''; paint(); return; }
	if (a === 'ta') { if (e && e.t === 'text' && /^[lcr]$/.test(n)) setO(sel, {align: n}); return; }
  if (a === 'tab-pos') { pmode = 'pos'; paint(); return; }
  if (a === 'tab-layers') { pmode = 'layers'; paint(); return; }
  if (a === 'hide') { ev.stopPropagation(); var hk = b.getAttribute('data-k'); sset = sset.filter(function(q){ return q !== hk; }); if (sel === hk) sel = sset.length ? sset[sset.length - 1] : null; setO(hk, {hide: true}); return; }
  var gid = b.getAttribute('data-g');
  if (a === 'gfold') { ev.stopPropagation(); gcol[gid] = !gcol[gid]; paintPanel(); return; }
  if (a === 'gname') { ev.stopPropagation(); renameGroup(gid); return; }
  if (a === 'glock') { ev.stopPropagation(); var gk = groupKeys(gid); if (gk.length) setLock(gk, !gk.every(isLocked)); return; }
  if (a === 'ghide') {
    ev.stopPropagation(); var hk2 = groupKeys(gid); if (!hk2.length) return;
    sset = sset.filter(function(q){ return hk2.indexOf(q) < 0; }); if (hk2.indexOf(sel) >= 0) sel = sset.length ? sset[sset.length - 1] : null;
    commit(function(L){ hk2.forEach(function(q){ if (!isPng(q)) (L.m[q] || (L.m[q] = {})).hide = true; }); }); return;
  }
  if (a === 'gpick') {
    var gm = groupKeys(gid).filter(function(q){ return !isLocked(q); });
    if (!gm.length) { toast('This group is locked. Click its lock in the list to unlock it.'); return; }
    if (ev.shiftKey || ev.ctrlKey || ev.metaKey) toggleKeys(gm); else setSel(gm); return;
  }
  if (a === 'lock') {
    ev.stopPropagation(); var lk0 = b.getAttribute('data-k'), on0 = isLocked(lk0);
    setLock((!on0 && sset.length > 1 && sset.indexOf(lk0) >= 0) ? sset.slice() : [lk0], !on0); return;
  }
  if (a === 'pdel') { ev.stopPropagation(); removeMany([b.getAttribute('data-k')]); return; }
  if (a === 'newpng') { if (typeof window.dsPngObjAdd === 'function') window.dsPngObjAdd(curIdx()); return; }
  if (a === 'show') { setO(b.getAttribute('data-k'), {hide: null}); return; }
  if (a === 'pick') { var pk = b.getAttribute('data-k'); if (isLocked(pk)) { toast('This element is locked. Click its lock in the list to unlock it.'); return; } if (ev.shiftKey || ev.ctrlKey || ev.metaKey) toggleKeys([pk]); else setSel([pk]); return; }
  if (a === 'new') { if (n === 'draw') { startDraw(); return; } addEl(newEl(n)); return; }
  if (a === 'newicon') { var P = palOf(); addEl({t: 'icon', x: 592, y: 312, w: 96, h: 96, name: n, color: hex(P.accent) || '#3B82F6'}); return; }
  if (a === 'rs-slide') { var i = curIdx(); resetSlide(i); sel = null; sset = []; pmode = ''; try { if (D.clearFit) D.clearFit(); } catch (x) {} rerender(); paint(); toast('Slide ' + (i + 1) + ' is back to how it was generated.'); return; }
  if (a === 'rs-deck') { var d = deck(); d.slides.forEach(function(s, j){ resetSlide(j); }); sel = null; sset = []; pmode = ''; try { if (D.clearFit) D.clearFit(); D.refreshEditor(); } catch (x2) {} paint(); toast('Every slide is back to how it was generated.'); return; }
  if (a === 'rs-el') { if (sel) resetEl(sel); return; }
  if (!e) return;
  if (a === 'fwd' || a === 'bwd' || a === 'front' || a === 'back') { arrange(a); return; }
  if (a.indexOf('al-') === 0) { doAlign(a.slice(3)); return; }
  if (a === 'ds-h' || a === 'ds-v') { doDistribute(a.slice(3)); return; }
  if (a === 'amode') { alignPref = n === 'page' ? 'page' : 'sel'; paintPanel(); return; }
  if (a === 'ratio') { ratioLock = !ratioLock; paintPanel(); return; }
  if (a === 'col') {
    var gcc = e && gradCan(pmode, e) && gradOf(pmode, e);
    if (gcc) { var Gc = curGrad(pmode, e), sc = gSorted(Gc); if (sc[gSel]) { sc[gSel][1] = b.getAttribute('data-c'); setO(sel, gradPatch(pmode, Gc)); } return; }
    setColor(pmode, b.getAttribute('data-c')); return;
  }
  if (a === 'pfins' && e) { openFillPick(sel); return; }
  if (a === 'pfdel' && e) { setO(sel, {pimg: null, fill: hex(palOf().accent) || '#3B82F6', grad: null}); return; }
  if (a === 'pfreset' && e && e.pimg) { pfEach(function(nr){ nr.z = 1; nr.px = 50; nr.py = 50; nr.st = 0; }); setTimeout(pfGhost, 30); return; }
if (a === 'pffit' && e && e.pimg) { pfEach(function(nf){ nf.st = n === 'stretch' ? 1 : 0; }); return; }
  if (a === 'pfone') { if (e && e.pimg) pfSetOne(n === '1'); else { pfOne = n === '1'; paint(); } return; }
  if (a === 'fmode' && e && n === 'pic') { if (!e.pimg) openFillPick(sel); return; }
  if (a === 'fmode' && e) {
    if (n === 'none') { if (pmode === 'fill') setO(sel, {fill: '', grad: null, pimg: null}); else if (pmode === 'line') setO(sel, {line: '', lgrad: null}); else if (pmode === 'tline') setO(sel, {tline: '', tlgrad: null}); return; }
    var c0 = hex(solidOf(pmode, e)) || (pmode === 'fill' ? (hex(palOf().accent) || '#3B82F6') : (pmode === 'tline' || pmode === 'line' ? '#FFFFFF' : '#000000'));
    if (n === 'solid') { var pS = pmode === 'fill' ? {fill: c0, grad: null, pimg: null} : (pmode === 'line' ? {line: c0, lgrad: null} : (pmode === 'tline' ? {tline: c0, tlgrad: null} : {color: c0, tgrad: null})); setO(sel, pS); return; }
    if (n === 'grad' && gradCan(pmode, e)) { gSel = 0; var G0 = gradOf(pmode, e) ? curGrad(pmode, e) : {ang: 135, stops: [[0, c0, 1], [100, mix(c0, '#FFFFFF', 0.7), 1]]}, gP = gradPatch(pmode, G0); if (pmode === 'fill') gP.pimg = null; setO(sel, gP); return; }
    return;
  }
  if ((a === 'gadd' || a === 'gdel' || a === 'gpre' || a === 'gdir' || a === 'gbar') && e && gradOf(pmode, e)) {
    if (a === 'gbar' && ev.target !== b) return;                       /* a click on a stop is not a click on the bar */
    var Gg = curGrad(pmode, e), sg = gSorted(Gg);
    if (a === 'gdel') { if (sg.length <= 2 || !sg[gSel]) return; Gg.stops.splice(Gg.stops.indexOf(sg[gSel]), 1); gSel = Math.max(0, gSel - 1); }
    else if (a === 'gadd' || a === 'gbar') {
      if (sg.length >= 6) { toast('A gradient can have up to 6 colour stops.', 'error'); return; }
      var pos;
      if (a === 'gbar') { var br = b.getBoundingClientRect(); pos = Math.round(Math.max(0, Math.min(100, (ev.clientX - br.left) / br.width * 100))); }
      else { var s1 = sg[gSel] || sg[0], s2 = sg[gSel + 1] || sg[gSel - 1] || sg[sg.length - 1]; pos = Math.round((s1[0] + s2[0]) / 2); }
      var lo = sg[0], hi = sg[sg.length - 1]; sg.forEach(function(x){ if (x[0] <= pos) lo = x; }); for (var gi = sg.length - 1; gi >= 0; gi--) if (sg[gi][0] >= pos) hi = sg[gi];
      var tt = hi[0] > lo[0] ? (pos - lo[0]) / (hi[0] - lo[0]) : 0, ns = [pos, mix(lo[1], hi[1], tt), Math.round((lo[2] + (hi[2] - lo[2]) * tt) * 100) / 100];
      Gg.stops.push(ns); gSel = gSorted(Gg).indexOf(ns);
    }
    else if (a === 'gpre') { var pr = gPresets(sg[0][1])[+n]; if (!pr) return; Gg.stops = pr.stops; Gg.ang = pr.ang; if (pmode !== 'fill') delete Gg.type; else delete Gg.type; gSel = 0; }
    else if (a === 'gdir') { Gg.ang = +n || 0; }
    setO(sel, gradPatch(pmode, Gg)); return;
  }
  if (a === 'nocol') { if (pmode === 'fill') setO(sel, {fill: '', grad: null}); else if (pmode === 'line') setO(sel, {line: '', lgrad: null});  else if (pmode === 'tline') setO(sel, {tline: '', tlgrad: null}); return; }
  if (a === 'ff') { needFontQuiet(n); setO(sel, {ff: n}); return; }
  if (a === 'wt') { setO(sel, {wt: parseInt(n, 10)}); return; }
  if (a === 'seticon') { setO(sel, {name: n}); return; }
  if (a === 'iname') { var inp = panel.querySelector('[data-pc="iname"]'), v = String(inp && inp.value || '').trim().toLowerCase(); if (/^[a-z0-9-]+:[a-z0-9-]+$/.test(v)) setO(sel, {name: v}); else toast('Type an icon name like mdi:rocket', 'error'); return; }
}
var slT = null;
function onPanelInput(ev){
  var t = ev.target; if (!t || !t.getAttribute || !sel) return;
  var ps = t.getAttribute('data-ps'), pc = t.getAttribute('data-pc'), e = elByKey(sel); if (!e) return;
  if (ps) {
    var v = parseFloat(t.value), lab = panel.querySelector('[data-pv="' + ps + '"]'); if (lab) lab.textContent = t.value + (ps === 'op' || ps === 'zm' || ps === 'sh.o' || ps === 'tr' || ps === 'ltr' || ps === 'gst' || /^pf/.test(ps) ? '%' : (ps === 'sh.a' || ps === 'gang' ? '\u00B0' : ''));
    clearTimeout(slT);
    if (ps === 'tr' || ps === 'ltr') { slT = setTimeout(function(){ var pt = {}; pt[ps === 'tr' ? 'op' : 'lop'] = Math.round(100 - v) / 100; quietSet(pt, sel + '|' + ps); }, 60); return; }
    if (ps === 'gang' || ps === 'gst') {
      var tgG = pmode; if (!gradOf(tgG, e)) return;
      var Gs = curGrad(tgG, e), ss = gSorted(Gs);
      if (ps === 'gang') Gs.ang = v; else if (ss[gSel]) ss[gSel][2] = Math.round(100 - v) / 100;
      var gb = panel.querySelector('.dsel-gbar'); if (gb) gb.style.background = gBarCss(Gs);
      if (ps === 'gang') Array.prototype.forEach.call(panel.querySelectorAll('[data-p="gdir"]'), function(x){ x.classList.toggle('dsel-on', +x.getAttribute('data-n') === Math.round(v)); });
      slT = setTimeout(function(){ quietSet(gradPatch(tgG, Gs), sel + '|' + ps); }, 60);
      return;
    }
    if (/^pf(tr|z|x|y)$/.test(ps)) {
      if (!e.pimg) return;
      slT = setTimeout(function(){ pfEach(function(np){ if (ps === 'pftr') np.op = Math.round(100 - v) / 100; else if (ps === 'pfz') np.z = v / 100; else if (ps === 'pfx') np.px = v; else np.py = v; }, true, sel + '|' + ps); }, 60);
      return;
    }
    if (ps.indexOf('sh.') === 0) {
      var sn = JSON.parse(JSON.stringify(shOf(e) || SHDEF)), fk = ps.slice(3); sn[fk] = fk === 'o' ? v / 100 : v;
      slT = setTimeout(function(){ setO(sel, {sh: sn}, true, sel + '|sh'); rerender(); sync(); }, 60);
      return;
    }
    if (e._png) {
      /* a copied picture frame: the picture is cut again with the new corners / transparency / crop */
      var pb = e._png; if (!pb.hold) return;
      slT = setTimeout(function(){
        commit(function(){
          var p0 = pb.pos || {x: 50, y: 50};
          if (ps === 'r') pb.rr = v; else if (ps === 'op') pb.op = v / 100; else if (ps === 'zm') pb.zm = v / 100;
          else if (ps === 'cx') pb.pos = {x: v, y: p0.y != null ? p0.y : 50}; else if (ps === 'cy') pb.pos = {x: p0.x != null ? p0.x : 50, y: v}; else return;
          if (needBake.indexOf(pb) < 0) needBake.push(pb);
        }, true, sel + '|' + ps);
      }, 140);
      return;
    }
    slT = setTimeout(function(){ var p = {}; p[ps] = ps === 'op' ? v / 100 : v; setO(sel, p, true, sel + '|' + ps); rerender(); sync(); }, 60);
    return;
  }
  if (pc === 'pick') { var hx = panel.querySelector('[data-pc="hex"]'); if (hx) hx.value = t.value.toUpperCase(); clearTimeout(slT); var tg = pmode, c = t.value; slT = setTimeout(function(){ quietColor(tg, c); }, 60); return; }
  if (pc === 'hex') { var c2 = hex(t.value); if (c2 && t.value.replace('#', '').length === 6) setColor(pmode, c2); return; }
  if (pc === 'gsc' || pc === 'gsp') {
    var tg2 = pmode; if (!gradOf(tg2, e)) return;
    var G2 = curGrad(tg2, e), s2s = gSorted(G2), stp = s2s[gSel]; if (!stp) return;
    if (pc === 'gsc') { var hc = hex(t.value); if (!hc) return; stp[1] = hc; }
    else { var pv = parseFloat(t.value); if (!isFinite(pv)) return; stp[0] = Math.max(0, Math.min(100, Math.round(pv))); gSel = gSorted(G2).indexOf(stp); }
    var gw = panel.querySelector('.dsel-gwrap'); if (gw) gw.outerHTML = gBarHTML(G2);
    clearTimeout(slT); slT = setTimeout(function(){ quietSet(gradPatch(tg2, G2), sel + '|' + pc); }, 60);
    return;
  }
  if (pc === 'gtype') { if (!gradOf(pmode, e)) return; var G3 = curGrad(pmode, e); if (t.value === 'radial') G3.type = 'radial'; else delete G3.type; setO(sel, gradPatch(pmode, G3)); return; }
  if (pc === 'dash') { setO(sel, {dash: t.value === '1'}); return; }
  if (pc === 'shon') { setO(sel, {sh: t.checked ? JSON.parse(JSON.stringify(shOf(e) || SHDEF)) : {off: 1}}); return; }
  if (pc === 'shc') { var s0 = JSON.parse(JSON.stringify(shOf(e) || SHDEF)); s0.c = hex(t.value) || '#000000'; clearTimeout(slT); slT = setTimeout(function(){ setO(sel, {sh: s0}, true, sel + '|shc'); rerender(); sync(); }, 60); return; }
}
/* while a colour is being dragged in the picker the panel is not rebuilt (that would close the picker) */
function quietColor(target, c){
  if (!sel) return;
  var p = colPatch(target, c, elByKey(sel));
  setO(sel, p, true, sel + '|c|' + target); rerender(); sync();
  var l = bar && bar.querySelector('.dsel-left'), e = elByKey(sel); if (l && e) l.innerHTML = leftHTML(e);
}
var dragKey = null;
function wireLayerDrag(){
  if (!panel) return;
  var rows = panel.querySelectorAll('.dsel-lr[draggable]');
  Array.prototype.forEach.call(rows, function(r){
    r.addEventListener('dragstart', function(e){ dragKey = r.getAttribute('data-k'); try { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', dragKey); } catch (x) {} });
    r.addEventListener('dragover', function(e){ if (!dragKey) return; e.preventDefault(); var b = r.getBoundingClientRect(); r.classList.toggle('dsel-dt', e.clientY < b.top + b.height / 2); r.classList.toggle('dsel-db', e.clientY >= b.top + b.height / 2); });
    r.addEventListener('dragleave', function(){ r.classList.remove('dsel-dt', 'dsel-db'); });
    r.addEventListener('drop', function(e){
      e.preventDefault(); var tk = r.getAttribute('data-k'), above = r.classList.contains('dsel-dt'); r.classList.remove('dsel-dt', 'dsel-db');
      var k = dragKey; dragKey = null; if (!k || k === tk) return;
      var z = keyOrder();
      var mv = k.indexOf('grp:') === 0 ? z.filter(function(q){ return groupId(q) === k.slice(4); }) : [k];
      if (tk.indexOf('grp:') === 0) { var tm = z.filter(function(q){ return groupId(q) === tk.slice(4); }); if (!tm.length) return; tk = tm[tm.length - 1]; above = true; }
      if (!mv.length || mv.indexOf(tk) >= 0) return;
      z = z.filter(function(q){ return mv.indexOf(q) < 0; });
      var j = z.indexOf(tk); if (j < 0) return;
      z = z.slice(0, above ? j + 1 : j).concat(mv, z.slice(above ? j + 1 : j));          /* the list is top first, the order is bottom first */
      commit(function(L){ L.z = z; });
    });
    r.addEventListener('dragend', function(){ dragKey = null; });
  });
}

/* ------------------------------------------------------------------ keep everything in step with the editor */
var mo = null, moT = null, wasOn = false, pngSeen = null;
function watch(){
  var m = mainEl(); if (!m || m.__dselMo) return; m.__dselMo = 1;
  mo = new MutationObserver(function(){ if (drag) return; clearTimeout(moT); moT = setTimeout(function(){ if (sel && !elByKey(sel)) { sel = null; paint(); } else sync(); if (pmode === 'layers') paintPanel(); }, 30); });
  mo.observe(m, {childList: true});
}
function tick(){
  try { wireTitle(); } catch (x) {}
  var o = on(), fs = fsEl();
  if (fs) fs.classList.toggle('dsel-on', o);
  document.documentElement.classList.toggle('dsel-live', o);
  if (!o) { if (wasOn) { wasOn = false; sel = null; pmode = ''; place(); sync(); } return; }
  watch(); ensureOrig(); if (pick) checkPick();
  var i = curIdx(), v = curVid();
  var gid = (window.G && G.pptId) || '';
  if (!wasOn) { wasOn = true; needSnap = true; snap0 = null; undo = {}; redo = {}; lastIdx = i; lastVid = v; paint(); return; }
  if (!needSnap && gid && snapId && gid !== snapId) { needSnap = true; snap0 = null; undo = {}; redo = {}; }      /* another deck was loaded without closing */
  if (!needSnap && gid && !snapId) snapId = gid;                                                                 /* first save of a new deck */
  if (i !== lastIdx || v !== lastVid) { lastIdx = i; lastVid = v; sel = null; pngSeen = pngList(curSlide()).length; paint(); return; }
  var pn2 = pngList(curSlide()).length;
  if (pngSeen != null && pn2 > pngSeen && !drag && elByKey('png' + (pn2 - 1))) { pngSeen = pn2; setSel(['png' + (pn2 - 1)]); return; }     /* a PNG was just added: select it */
  if (pngSeen !== pn2) { pngSeen = pn2; if (pmode === 'layers') paintPanel(); }
  place(); markLocks();
}
setInterval(function(){ try { tick(); } catch (e) { if (window.console) console.warn('[DSEL]', e); } }, 350);
window.addEventListener('resize', function(){ try { place(); sync(); } catch (e) {} });
X.select = select; X.setSel = setSel; X.state = function(){ return {sel: sel, set: sset.slice(), idx: curIdx(), vid: curVid(), on: on(), mode: pmode}; };
})();
</script>
    <?php
}, 2000);
