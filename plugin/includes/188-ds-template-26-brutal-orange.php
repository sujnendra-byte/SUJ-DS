<?php
/**
 * Code Snippets export — DS Template 26 — Brutal Orange
 *
 * Snippet ID : 925
 * Active     : yes
 * Priority   : 11
 * Scope      : global
 * Tags       : ds-templates-pack2
 * Modified   : 2026-09-30 19:33:27
 * Load order : 183 of 191
 */
/**
 * DS Template 26 — Brutal Orange
 * Run: Everywhere
 *
 * One design template for the AI Presentation generator. Needs the
 * "DS Template Engine (T00)" and "DS Template Kit 2" snippets to be active.
 * Its own AI voice + storyline, its own signature layouts, its own PPTX build.
 * Undo: deactivate this snippet — the template disappears from the picker.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('init', function () {
    if ( ! function_exists('ds_tpl_register') ) return;
    ds_tpl_register('brutal', array(
        'name'     => "Brutal Orange",
        'fonts'    => array('head' => "Anton", 'body' => "Inter"),
        'pal'      => array('bg' => '#0E0E0E', 'text' => '#F5EDDC', 'accent' => '#FF4A12', 'accent2' => '#F5EDDC'),
        'voice'    => "Bold agency voice: loud, direct, short uppercase-worthy headlines; no fluff.",
        'recipe'   => "Manifesto, what we do, services, work, results, process, clients, contact.",
        'generate' => 'ds_tpl26_generate',
        'build'    => 'ds_tpl26_build',
    ));
}, 5);

if ( ! function_exists('ds_tpl26_generate') ) {
function ds_tpl26_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    return ds_tpl_generate( $req, $T, $mode );
}
}
if ( ! function_exists('ds_tpl26_build') ) {
function ds_tpl26_build( WP_REST_Request $req, $T ) {
    return ds_tpl_build( $req, $T );
}
}

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-brutal">
/* Template 26 — Brutal Orange : black and cream pages in turn, huge condensed caps, vivid orange side bands and blocks, checker marks, hard square edges */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2;
var INK = '#0E0E0E', ORG = '#FF4A12', CRM = '#F5EDDC', GREY = '#1F1F1F';
var PD = {bg:INK, text:CRM, title:CRM, muted:'#BDB4A4', accent:ORG, accent2:CRM, card:GREY, line:'#3A3A3A', onAccent:INK};
var PL = {bg:CRM, text:INK, title:INK, muted:'#4F4A42', accent:ORG, accent2:INK, card:INK, line:'#CFC5B0', onAccent:INK};
var S = {
  margin:84, series:[ORG, CRM, '#FF7A4D', '#B8B0A0', '#FF4A12', '#6B6B6B'],
  header:{kind:'left', tMax:62, tMin:30, sMax:17, sMin:13, top:58, gap:8, tH:120},
  card:{kind:'solid', r:0, fill:GREY, dark:true, tc:CRM, mc:'#CFC7B8'},
  icon:{kind:'square', r:0, bg:ORG, fg:INK}, num:{kind:'square', r:0, bg:ORG, fg:INK},
  conc:{kind:'band', fill:ORG, r:0, tc:INK, lc:INK, label:'Bottom line'},
  footer:{fn:function(C){ foot(C); }}, bodyGap:30, imgFramed:false, imgR:0
};

/* ---------- motif kit ---------- */
function isDk(C){ return C.P.bg === INK; }
/* checker mark: alternating squares */
function checker(C, x, y, cols, rows, s, c1, c2){
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) {
    if ((i + j) % 2 === 0) C.rect(x + i * s, y + j * s, s, s, {fill:c1});
    else if (c2) C.rect(x + i * s, y + j * s, s, s, {fill:c2});
  }
}
/* slanted tape strip */
function tape(C, x, y, w, h, fill, sl){ sl = sl == null ? Math.min(16, h * 0.3) : sl; K.poly(C, [[x + sl, y], [x + w, y], [x + w - sl, y + h], [x, y + h]], {fill:fill}); }
/* width a single line needs */
function lineW(C, s, size, font, wt, maxW, caps, ls){
  for (var w = 30; w < maxW; w += 6) { var m = C.measureText(s, w, {font:font, wt:wt, caps:caps, ls:ls, max:size, min:Math.max(6, size * 0.7), maxLines:1}); if (m.n === 1 && m.s >= size) return w; }
  return maxW;
}
function F2(C, it, w, hb, db, hMax, dMax, hl){ return K.fitHD(C, it, w, hb, db, {hMax:hMax, dMax:dMax, hMin:13, dMin:11, hLines:hl || 2}); }
function dH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + dH(C, t, w, F); }
/* heading + description (heading edit part can be overridden with t.hp) */
function hdx(C, x, y, w, h, t, F, tc, mc, al){
  al = al || 'l'; var hh = t.h ? F.hh : 0;
  if (t.h) C.text(x, y, w, hh + 2, t.h, {font:'head', size:F.hs, color:tc, align:al, lh:1.15, ed:{f:'x-part', i:t.i, p:t.hp != null ? t.hp : 0}, maxLines:2});
  if (t.d) { var dy = y + (hh ? hh + 6 : 0); C.text(x, dy, w, Math.max(14, h - (dy - y)), t.d, {font:'body', size:F.ds, color:mc, align:al, lh:1.36, ed:{f:'x-part', i:t.i, p:1}}); }
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }

/* ---------- page chrome ---------- */
function foot(C){
  var P = C.P, f = C.cover, im = (C.o || {}).img;
  if (f.company && im !== 'left') C.text(84, H - 40, 420, 20, f.company, {font:'body', size:12, wt:800, color:P.muted, caps:true, ls:0.16, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.rect(W - 84 - 34, H - 46, 34, 30, {fill:ORG});
  C.text(W - 84 - 34, H - 46, 34, 30, pad2(C.i + 1), {font:'body', size:12, wt:800, color:INK, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  var dk = kind === 'cover' || kind === 'closing' ? true : (C.i || 0) % 2 === 0;
  C.P = dk ? PD : PL;
  C.S = D.merge(C.T.s, {header:{tc:C.P.title, sc:C.P.muted}});
  C.bg({fill:C.P.bg});
  if (kind === 'cover' || kind === 'closing') return;
  C.rect(0, 0, 24, H, {fill:ORG});
  checker(C, 24, H - 132, 2, 8, 12, dk ? CRM : INK);
  checker(C, W - 84 - 60, 24, 6, 2, 10, ORG);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing' || kind === 'quote') return;
  var im = (C.o || {}).img, iw = Math.round(W * (C.S.imgPct || 0.4));
  if (im === 'right') { C.rect(W - iw, 0, 18, H, {fill:ORG}); checker(C, W - iw + 18, 0, 1, 6, 18, INK); }
  if (im === 'left') { C.rect(iw - 18, 0, 18, H, {fill:ORG}); checker(C, iw - 36, H - 108, 1, 6, 18, INK); }
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* 1. index table: thick rules, huge orange numbers (or years), heading | description */
function brIndex(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 48, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(o.rMax || 150, Z.h / per);
  var y0 = Z.y + Math.max(0, (Z.h - rh * per) / 2);
  var labs = it.map(function(t, k){ return o.year ? (t.h || pad2(k + 1)) : pad2(k + 1); });
  var ns = C.groupFit(labs, o.year ? Math.min(220, cw * 0.3) : 160, rh - 18, {font:'head', max:Math.min(72, rh * 0.72), min:18, maxLines:1});
  var nw = 0; labs.forEach(function(l){ nw = Math.max(nw, lineW(C, l, ns, 'head', 400, cw * 0.4)); }); nw += 26;
  var tt = it.map(function(t){ return o.year ? {i:t.i, h:'', d:t.d} : t; });
  var three = cols === 1 && cw - nw >= 680 && !o.year;
  var hw = three ? Math.round((cw - nw) * 0.36) : 0, tx = nw, tw = cw - nw - hw;
  var F = three ? {hs:C.groupFit(it.map(function(t){ return t.h; }), hw - 30, rh - 24, {font:'head', max:n <= 3 ? 36 : 30, min:14, maxLines:2}), hh:0,
                   ds:C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 24, {font:'body', max:n <= 3 ? 22 : 19, min:11, lh:1.36})}
                : F2(C, tt, tw, rh * 0.42, rh - 22, 26, o.year ? 20 : 18, 1);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    C.rect(x, y, cw, 3, {fill:P.text});
    if (r === per - 1 || k === n - 1) C.rect(x, y + rh - 3, cw, 3, {fill:P.text});
    C.text(x, y + 3, nw, rh - 6, labs[k], {font:'head', size:ns, color:ORG, va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.year && t.h ? {f:'x-part', i:t.i, p:0} : null});
    if (three) {
      if (t.h) C.text(x + tx, y + 3, hw - 30, rh - 6, t.h, {font:'head', size:F.hs, color:P.text, va:'m', lh:1.08, maxLines:2, ed:{f:'x-part', i:t.i, p:0}});
      C.text(x + tx + hw, y + 3, tw, rh - 6, t.d, {font:'body', size:F.ds, color:P.muted, va:'m', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      var bh = blockH(C, tt[k], tw, F);
      hdx(C, x + tx, y + Math.max(6, (rh - bh) / 2), tw, rh - 10, tt[k], F, P.text, P.muted);
    }
  });
}

/* 2. hard-edged block grid: first block orange, huge number, checker corner, text bottom */
function brBlocks(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P, dk = isDk(C);
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))), rows = Math.ceil(n / cols), g = 14;
  var cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = Math.max(16, Math.min(26, cw * 0.08));
  var labs = it.map(function(t, k){ return o.year ? (t.h || pad2(k + 1)) : pad2(k + 1); });
  var ns = C.groupFit(labs, cw - pad * 2 - 40, Math.min(ch * 0.34, 90), {font:'head', max:Math.min(ch * 0.34, 84), min:18, maxLines:1});
  var tt = it.map(function(t){
    if (o.year) return {i:t.i, h:'', d:t.d};
    if (o.agenda) return {i:t.i, h:t.h || t.d, d:t.h ? t.d : '', hp:t.h ? 0 : 1};
    return t;
  });
  var F = F2(C, tt, cw - pad * 2, 64, ch - pad * 2 - ns - 12, o.agenda ? 30 : 24, o.year ? 19 : 17, 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + (cols - inRow) * (cw + g) / 2 + c * (cw + g), y = Z.y + r * (ch + g);
    var hot = k === 0, fill = hot ? ORG : (dk ? GREY : INK), tc = hot ? INK : CRM, mc = hot ? '#2A1208' : '#CFC7B8', nc = hot ? INK : ORG;
    C.rect(x, y, cw, ch, {fill:fill});
    checker(C, x + cw - pad - 30, y + pad, 3, 3, 10, hot ? INK : ORG);
    C.text(x + pad, y + pad - 2, cw - pad * 2 - 40, ns * 1.02, labs[k], {font:'head', size:ns, color:nc, lh:1, caps:false, ls:0, maxLines:1, ed:o.year && t.h ? {f:'x-part', i:t.i, p:0} : null});
    var bh = blockH(C, tt[k], cw - pad * 2, F), ty = Math.max(y + pad + ns + 10, y + ch - pad - bh);
    hdx(C, x + pad, ty, cw - pad * 2, y + ch - pad - ty, tt[k], F, tc, mc);
  });
}

/* 3. two big panels: orange slab vs heavy outline, giant numbers */
function brPanels(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (n < 2) return brIndex(C, Z, o); var P = C.P;
  var gap = 26, cw = (Z.w - gap) / 2, ch = Z.h, pad = cw > 420 ? 36 : 24, big = Math.min(150, ch * 0.34, cw * 0.42);
  var F = F2(C, it, cw - pad * 2, 96, ch - pad * 2 - big - 24, cw > 420 ? 34 : 22, 20, 2);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), y = Z.y, tc = k ? P.text : INK, mc = k ? P.muted : '#2A1208';
    if (k) C.rect(x, y, cw, ch, {line:P.text, lw:5}); else C.rect(x, y, cw, ch, {fill:ORG});
    C.text(x + pad, y + pad - 6, cw - pad * 2, big * 1.02, pad2(k + 1), {font:'head', size:big, color:k ? ORG : INK, lh:1, caps:false, ls:0, maxLines:1});
    checker(C, x + cw - pad - 48, y + pad, 4, 2, 12, k ? ORG : INK);
    var bh = blockH(C, t, cw - pad * 2, F), ty = Math.max(y + pad + big + 16, y + ch - pad - bh);
    hdx(C, x + pad, ty, cw - pad * 2, y + ch - pad - ty, t, F, tc, mc);
  });
}

/* 4. tall columns split by thick rules, orange cap bar, giant number, checker foot */
function brColumns(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, pad = n > 3 ? 18 : 26, ns = Math.min(100, Z.h * 0.24, cw * 0.42);
  var iw = function(k){ return cw - (k ? pad : 0) - (k < n - 1 ? pad : 0); }, mw = iw(Math.min(1, n - 1));
  var ty0 = Z.y + 26 + ns + 20;
  var F = F2(C, it, Math.min(iw(0), mw), 80, Z.y + Z.h - 34 - ty0, 26, 18, 2);
  it.forEach(function(t, k){
    var ix = Z.x + k * cw + (k ? pad : 0), w = iw(k);
    if (k) C.rect(Z.x + k * cw - 2, Z.y, 4, Z.h, {fill:P.text});
    C.rect(ix, Z.y, w, 12, {fill:ORG});
    C.text(ix, Z.y + 26, w, ns * 1.02, pad2(k + 1), {font:'head', size:ns, color:k % 2 ? P.text : ORG, lh:1, caps:false, ls:0, maxLines:1});
    hdx(C, ix, ty0, Math.min(w, Math.min(iw(0), mw)), Z.y + Z.h - 34 - ty0, t, F, P.text, P.muted);
    checker(C, ix, Z.y + Z.h - 14, 6, 1, 14, ORG);
  });
}

/* 5. orange tape labels with the heading, description beside */
function brTape(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols, gy = per > 4 ? 10 : 16;
  var rh = Math.min(o.rMax || 118, (Z.h - gy * (per - 1)) / per), y0 = Z.y + Math.max(0, (Z.h - rh * per - gy * (per - 1)) / 2);
  var tw = Math.round(Math.min(cw * 0.42, 360)), slant = Math.min(18, rh * 0.3), nw = Math.min(46, rh * 0.8);
  var labs = it.map(function(t){ return t.h || t.d; });
  var hs = C.groupFit(labs, tw - slant * 2 - nw - 10, rh - 14, {font:'head', max:28, min:13, maxLines:2, lh:1.05});
  var dw = cw - tw - 22;
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), dw, rh - 6, {font:'body', max:n <= 3 ? 20 : 17, min:11, lh:1.34});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), hot = k % 2 === 0;
    tape(C, x, y, tw, rh, hot ? ORG : P.text, slant);
    C.text(x + slant, y, nw, rh, pad2(k + 1), {font:'body', size:Math.min(15, rh * 0.32), wt:800, color:hot ? INK : ORG, va:'m', lh:1, maxLines:1});
    C.text(x + slant + nw, y, tw - slant * 2 - nw - 4, rh, labs[k], {font:'head', size:hs, color:hot ? INK : P.bg, va:'m', lh:1.05, maxLines:2, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    if (t.h && t.d) C.text(x + tw + 22, y, dw, rh, t.d, {font:'body', size:ds, color:P.text, va:'m', lh:1.34, ed:{f:'x-part', i:t.i, p:1}});
    if (r < per - 1) C.rect(x + tw + 22, y + rh + gy / 2 - 1, dw, 2, {fill:P.text, op:0.18});
  });
}

/* 6. giant figures in columns with heavy under-bars */
function brBigStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return; var P = C.P;
  var vals = st.map(sv), labs = st.map(sl), cw = Z.w / n, pad = n > 3 ? 16 : 24, w = cw - pad * 2;
  var vs = C.groupFit(vals, w, Z.h * 0.42, {font:'head', max:n <= 2 ? 160 : 124, min:28, maxLines:1});
  var ls = C.groupFit(labs, w, 70, {font:'body', wt:700, max:n <= 2 ? 22 : 19, min:12, maxLines:3, lh:1.25});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), w, 60, {font:'body', max:15, min:11, lh:1.35});
  var lh = 0; labs.forEach(function(l){ lh = Math.max(lh, C.measureText(l, w, {font:'body', wt:700, max:ls, min:ls, lh:1.25}).h); });
  var xh = 0; st.forEach(function(t){ if (t.x) xh = Math.max(xh, C.measureText(t.x, w, {font:'body', max:xs, min:xs, lh:1.35}).h + 10); });
  var tot = 34 + vs * 1.02 + 18 + 10 + 18 + lh + xh, y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
  st.forEach(function(t, k){
    var x = Z.x + k * cw + pad, y = y0;
    if (k) C.rect(Z.x + k * cw - 1, Z.y + 10, 2, Z.h - 20, {fill:P.text, op:0.25});
    checker(C, x, y, 4, 2, 9, ORG);
    y += 34;
    C.text(x, y, w, vs * 1.02, vals[k], {font:'head', size:vs, color:k % 2 ? P.text : ORG, lh:1, caps:false, ls:0, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    y += vs * 1.02 + 18;
    C.rect(x, y, w, 10, {fill:k % 2 ? ORG : P.text});
    y += 28;
    C.text(x, y, w, lh + 2, labs[k], {font:'body', size:ls, wt:700, color:P.text, lh:1.25, caps:true, ls:0.04, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x, y + lh + 10, w, xh, t.x, {font:'body', size:xs, color:P.muted, lh:1.35});
  });
}

/* 7. stat blocks: orange lead block, dark blocks, figures top, labels bottom */
function brStatBlocks(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return; var P = C.P, dk = isDk(C);
  var cols = o.cols || (Z.w / n >= 230 ? n : 2), rows = Math.ceil(n / cols), g = 14;
  var cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = Math.min(26, cw * 0.1), w = cw - pad * 2;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, w - 10, ch * 0.42, {font:'head', max:rows > 1 ? 70 : 96, min:22, maxLines:1});
  var ls = C.groupFit(labs, w, ch * 0.3, {font:'body', wt:700, max:rows > 1 ? 17 : 19, min:11.5, maxLines:3, lh:1.25});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), w, ch * 0.2, {font:'body', max:14, min:11, lh:1.3, maxLines:2});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), hot = k === 0;
    var fill = hot ? ORG : (dk ? GREY : INK), tc = hot ? INK : CRM, vc = hot ? INK : ORG, mc = hot ? '#2A1208' : '#CFC7B8';
    C.rect(x, y, cw, ch, {fill:fill});
    checker(C, x + cw - pad - 24, y + ch - pad - 24, 3, 3, 8, hot ? INK : ORG);
    C.text(x + pad, y + pad - 4, w, vs * 1.05, vals[k], {font:'head', size:vs, color:vc, lh:1, caps:false, ls:0, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(x + pad, y + pad + vs * 1.05 + 4, 44, 6, {fill:tc});
    var lh = C.measureText(labs[k], w - 34, {font:'body', wt:700, max:ls, min:ls, lh:1.25}).h, xh = t.x ? C.measureText(t.x, w - 34, {font:'body', max:xs, min:xs, lh:1.3}).h + 6 : 0;
    var ly = Math.max(y + pad + vs * 1.05 + 22, y + ch - pad - lh - xh);
    C.text(x + pad, ly, w - 34, lh + 2, labs[k], {font:'body', size:ls, wt:700, color:tc, lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + pad, ly + lh + 6, w - 34, xh, t.x, {font:'body', size:xs, color:mc, lh:1.3, maxLines:2});
  });
}

/* 8. ruler timeline: heavy bar with tick marks, huge years above, notes below */
function brRuler(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, ys = it.map(function(t, k){ return t.h || pad2(k + 1); });
  var yz = C.groupFit(ys, cw - 20, Z.h * 0.3, {font:'head', max:96, min:18, maxLines:1});
  var by = Math.max(Z.y + yz * 1.02 + 34, Z.y + Z.h * 0.42), bh = 14, yy = by - 34 - yz * 1.02;
  C.rect(Z.x, by, Z.w, bh, {fill:P.text});
  for (var tx = Z.x + 8; tx < Z.x + Z.w - 4; tx += 16) C.rect(tx, by + bh, 2, (Math.round((tx - Z.x) / 16) % 4 === 0) ? 14 : 7, {fill:P.text});
  var tt = it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), ny = by + bh + 40;
  var F = F2(C, tt, cw - 30, 1, Z.y + Z.h - ny, 16, 21, 1);
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    C.text(cx - cw / 2 + 10, yy, cw - 20, yz * 1.02, ys[k], {font:'head', size:yz, color:k % 2 ? P.text : ORG, align:'c', lh:1, caps:false, ls:0, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx - 2, yy + yz * 1.02 + 8, 4, by - (yy + yz * 1.02 + 8), {fill:ORG});
    C.rect(cx - 15, by - 8, 30, 30, {fill:ORG});
    C.rect(cx - 5, by + 2, 10, 10, {fill:INK});
    hdx(C, cx - cw / 2 + 15, ny, cw - 30, Z.y + Z.h - ny, tt[k], F, P.text, P.muted, 'c');
  });
}

/* 9. statement: huge condensed caps, orange rule or full orange block */
function brStatement(C, Z, o){
  var t = C.it[0], txt = t ? t.d : (C.s.subtitle || ''), P = C.P, blk = !!o.block;
  var pad = blk ? (Z.w > 800 ? 50 : 36) : 0, x = Z.x + (blk ? pad : 44), y = Z.y + (blk ? pad : 0), w = Z.w - (blk ? pad * 2 : 44), h = Z.h - (blk ? pad * 2 : 0);
  var tc = blk ? INK : P.text;
  if (blk) { C.rect(Z.x, Z.y, Z.w, Z.h, {fill:ORG}); checker(C, Z.x + Z.w - pad - 60, Z.y + pad - 14, 6, 2, 10, INK); }
  else C.rect(Z.x, Z.y, 14, Z.h, {fill:ORG});
  if (t && t.h) {
    var lw = Math.min(w * 0.6, lineW(C, t.h, 15, 'body', 800, w, true, 0.12) + 36);
    C.rect(x, y, lw, 36, {fill:blk ? INK : P.text});
    C.text(x, y, lw, 36, t.h, {font:'body', size:15, wt:800, color:blk ? ORG : P.bg, caps:true, ls:0.12, align:'c', va:'m', lh:1, maxLines:1, ed:{f:'x-part', i:t.i, p:0}});
    y += 56; h -= 56;
  }
  C.text(x, y, w - (blk ? 0 : 20), h, txt, {font:'head', fit:{max:58, min:20}, color:tc, lh:1.08, va:'m', ed:t ? (t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}) : null});
}

/* 10. agenda: orange / ink number squares, huge caps titles, heavy rules */
function brAgenda(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 44, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(88, Z.h / per);
  var sq = Math.min(rh - 14, 56), y0 = Z.y + Math.max(0, (Z.h - rh * per) / 2);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - sq - 30, rh - 12, {font:'head', max:36, min:14, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, hot = k % 2 === 0;
    C.rect(x, y + (rh - sq) / 2, sq, sq, {fill:hot ? ORG : P.text});
    C.text(x, y + (rh - sq) / 2, sq, sq, pad2(k + 1), {font:'head', size:Math.round(sq * 0.5), color:hot ? INK : P.bg, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + sq + 22, y, cw - sq - 30, rh, t.h || t.d, {font:'head', size:hs, color:P.text, va:'m', lh:1, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    C.rect(x + sq + 22, y + rh - 2, cw - sq - 22, 2, {fill:P.text, op:r < per - 1 ? 0.22 : 0});
  });
}

/* ============================================================
   FULL SLIDES
   ============================================================ */
function cover(C, v){
  var f = C.cover, o = C.o || {}, img = !!o.img;
  var x = 84, w = img ? W * 0.5 - 140 : W - 260;
  if (img) {
    D.imgBox(C, W * 0.5, 0, W * 0.5, H, {r:0});
    C.rect(W * 0.5 - 10, 0, 30, H, {fill:ORG});
    checker(C, W - 144, H - 144, 6, 6, 20, CRM, INK);
  } else {
    C.rect(0, 0, 28, H, {fill:ORG});
    checker(C, W - 250, 60, 10, 3, 16, ORG);
    C.rect(W - 110, 170, 26, H - 290, {fill:ORG});
  }
  if (f.company) C.text(x, 56, 480, 22, f.company, {font:'body', size:14, wt:800, color:CRM, caps:true, ls:0.18, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(x, 110, w, 390, C.s.title, {font:'head', fit:{max:img ? 108 : 150, min:44}, color:CRM, ed:{f:'title'}, maxLines:img ? 4 : 3, va:'b', lh:0.94});
  C.rect(x, 522, 140, 12, {fill:ORG});
  if (C.s.subtitle) C.text(x, 552, img ? w : Math.min(w, 820), 64, C.s.subtitle, {font:'body', fit:{max:20, min:13}, wt:500, color:'#CFC7B8', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  /  ');
  if (meta) C.text(x, H - 58, w, 22, meta, {font:'body', size:13, wt:700, color:ORG, caps:true, ls:0.12, lh:1, va:'m', maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, img = !!o.img;
  var x = img ? W * 0.5 + 70 : 84, w = img ? W * 0.5 - 150 : W - 260;
  if (img) {
    D.imgBox(C, 0, 0, W * 0.5, H, {r:0});
    C.rect(W * 0.5 - 20, 0, 30, H, {fill:ORG});
    checker(C, 24, 24, 6, 6, 20, CRM, INK);
  } else {
    C.rect(0, 0, 28, H, {fill:ORG});
    checker(C, W - 250, 60, 10, 3, 16, ORG);
  }
  C.text(x, 90, w, 250, C.s.title || 'Thank You', {font:'head', fit:{max:img ? 120 : 170, min:48}, color:CRM, ed:{f:'title'}, maxLines:2, va:'b', lh:0.92});
  C.rect(x, 360, 140, 12, {fill:ORG});
  if (C.s.subtitle) C.text(x, 390, img ? w : Math.min(w, 860), 64, C.s.subtitle, {font:'body', fit:{max:20, min:13}, wt:500, color:'#CFC7B8', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  var n = cs.length, gap = 16, cols = img ? 1 : n, cw = img ? w : (Math.min(w, 1060) - gap * (n - 1)) / n;
  cs.forEach(function(c, k){
    var cx = img ? x : x + k * (cw + gap), cy = img ? 490 + k * 62 : 510;
    C.rect(cx, cy, 50, 50, {fill:k % 2 ? CRM : ORG});
    C.icon(cx + 13, cy + 13, 24, c.icon, INK);
    C.rect(cx + 50, cy, cw - 50, 50, {line:'#3A3A3A', lw:2});
    C.text(cx + 66, cy, cw - 80, 50, c.val, {font:'body', fit:{max:17, min:11}, wt:700, color:CRM, va:'m', maxLines:1, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null});
  });
}
function quote(C, v){
  var o = C.o || {}, P = C.P, a = C.it[0], by = a ? (a.h ? a.h + ': ' + a.d : a.d) : (C.s.subtitle || '');
  var x = 150, w = W - 300;
  if (o.img) {
    var iw = Math.round(W * 0.4), ix = o.img === 'right' ? W - iw : 0;
    D.imgBox(C, ix, 0, iw, H, {r:0});
    C.rect(o.img === 'right' ? ix : ix + iw - 18, 0, 18, H, {fill:ORG});
    x = o.img === 'right' ? 110 : iw + 80; w = W - iw - 190;
  }
  var qm = C.measureText(C.s.title || '', w, {font:'head', max:o.img ? 52 : 64, min:22, lh:1.04, h:290, maxLines:6});
  var y0 = Math.max(60, (H - 40 - (146 + Math.min(290, qm.h) + (by ? 86 : 0))) / 2);
  C.rect(x, y0, 110, 110, {fill:ORG});
  C.text(x, y0 + 6, 110, 130, '“', {font:'head', size:170, color:INK, align:'c', va:'t', lh:1, caps:false, ls:0});
  checker(C, x + 130, y0, 6, 2, 12, ORG);
  var q = C.text(x, y0 + 146, w, 290, C.s.title || '', {font:'head', fit:{max:o.img ? 52 : 64, min:22}, color:P.text, lh:1.04, va:'t', ed:{f:'title'}, maxLines:6});
  if (by) {
    var yy = y0 + 146 + q._h + 30;
    C.rect(x, yy, 60, 8, {fill:ORG});
    C.text(x, yy + 22, w, 26, by, {font:'body', fit:{max:16, min:11}, wt:800, color:P.text, caps:true, ls:0.12, lh:1, maxLines:1, ed:a ? {f:'content-plain', i:a.i} : null});
  }
}

D.register({
  id:'brutal', name:'Brutal Orange', tag:'Bold · agency', desc:'Black and cream pages in turn with huge condensed caps, vivid orange side bands and blocks, checker marks and hard square edges — for agencies, launches and bold pitches.',
  fonts:{head:'Anton', body:'Inter', headW:400, bodyW:400, headLh:1.0, headCaps:true, headLs:0.01, gf:'Anton|Inter:wght@400;500;600;700|Yellowtail'},
  pal:PD, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Giant caps',{}), V('v2','Photo + orange band',{img:true})],
    closing:[V('v1','Giant thanks + contact blocks',{}), V('v2','Photo left + contacts',{img:true})],
    agenda:[V('v1','Number squares',{body:brAgenda}), V('n2','Block grid',{body:brBlocks, agenda:true}), V('v2','Squares + image right',{body:brAgenda, img:'right', cols:1})],
    text:[V('v1','Huge statement',{body:brStatement}), V('n2','Orange block',{body:brStatement, block:true}), V('v2','Statement + image right',{body:brStatement, img:'right'}), V('v3','Image left + orange block',{body:brStatement, block:true, img:'left'})],
    p2:[V('v1','Orange vs outline',{body:brPanels}), V('n2','Index table',{body:brIndex}), V('n3','Tape labels',{body:brTape}), V('v2','Panels + image right',{body:brPanels, img:'right'}), V('v3','Image left + index',{body:brIndex, img:'left'})],
    p3:[V('v1','Ruled columns',{body:brColumns}), V('n2','Block grid',{body:brBlocks}), V('n3','Index table',{body:brIndex}), V('n4','Tape labels',{body:brTape}), V('v2','Image left + index',{body:brIndex, img:'left'}), V('v3','Tape + image right',{body:brTape, img:'right'})],
    p4:[V('v1','Block grid',{body:brBlocks}), V('n2','Ruled columns',{body:brColumns}), V('n3','Index table',{body:brIndex}), V('n4','Tape labels',{body:brTape}), V('v2','Tape + image right',{body:brTape, img:'right'})],
    p56:[V('v1','Tape labels',{body:brTape}), V('n2','Block grid',{body:brBlocks}), V('n3','Index table',{body:brIndex}), V('v2','Index + image right',{body:brIndex, img:'right', cols:1})],
    p78:[V('v1','Block grid',{body:brBlocks}), V('n2','Tape labels',{body:brTape}), V('n3','Index table',{body:brIndex}), V('v2','Tape + image right',{body:brTape, img:'right', cols:1})],
    stats:[V('v1','Giant figures',{body:brBigStats}), V('n2','Stat blocks',{body:brStatBlocks}), V('v2','Image left + blocks',{body:brStatBlocks, img:'left'}), V('v3','Figures + image right',{body:brBigStats, img:'right'})],
    timeline:[V('v1','Ruler',{body:brRuler}), V('n2','Year table',{body:brIndex, year:true}), V('n3','Year blocks',{body:brBlocks, year:true}), V('v2','Year table + image right',{body:brIndex, year:true, img:'right', cols:1})],
    quote:[V('v1','Orange mark quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();

</script>
<?php
}, 1007);
