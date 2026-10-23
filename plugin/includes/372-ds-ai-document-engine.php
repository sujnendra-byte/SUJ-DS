<?php
/**
 * Code Snippets export — DS AI Document — Engine
 *
 * Snippet ID : 1257
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-23 20:30:00
 * Load order : 372 of 374
 */
/**
 * DS AI Document — Engine (document designs + Word export)
 * Serves the document design engine used by the AI Document page and the Word add-in:
 *   https://YOUR-SITE/?ds_doc_asset=engine
 * Version: 1.0.0-ac386964
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'ds_doc_engine_js' ) ) :
define( 'DS_DOC_ENGINE_VER', '1.6.3-picfill' );
function ds_doc_engine_js() {
	return <<<'DSJS'
/*! DesignStager Document Engine — (c) DesignStager */
(function(){'use strict';
/* DesignStager Document Engine (DSDOC)
 * One document model, rendered two ways:
 *   - HTML pages for the website preview / editor and the Word add-in preview
 *   - a real .docx (Word) file built with docx.js, used for downloads and for Word.
 * Designs are original DesignStager designs made for Word: real headings, text,
 * lists and tables, with designed covers and page decoration drawn as pictures
 * behind the text (the same technique Word's own templates use).
 */
var D = {};
D.version = '1.0.0';

/* ---------- page sizes (96 px per inch; Word works in twips = 1/1440 inch) ---------- */
D.SIZES = {
  A4: { w: 794, h: 1123, tw: 11906, th: 16838, label: 'A4' },
  Letter: { w: 816, h: 1056, tw: 12240, th: 15840, label: 'US Letter' },
  A3: { w: 1123, h: 1587, tw: 16838, th: 23811, label: 'A3' },
  A5: { w: 559, h: 794, tw: 8391, th: 11906, label: 'A5' },
  Tabloid: { w: 1056, h: 1632, tw: 15840, th: 24480, label: 'Tabloid (11 × 17 in)' },
  BizCard: { w: 336, h: 192, tw: 5040, th: 2880, label: 'Business card (3.5 × 2 in)' },
  BizCardEU: { w: 321, h: 208, tw: 4819, th: 3118, label: 'Business card (85 × 55 mm)' },
  Social: { w: 1080, h: 1080, tw: 16200, th: 16200, label: 'Square post (1080 × 1080)', screen: 1 },
  SocialP: { w: 1080, h: 1350, tw: 16200, th: 20250, label: 'Portrait post (1080 × 1350)', screen: 1 },
  Story: { w: 1080, h: 1920, tw: 16200, th: 28800, label: 'Story (1080 × 1920)', screen: 1 }
};
/* which page sizes each document type offers (first = default) */
D.TYPE_SIZES = { flyer: ['A4', 'Letter', 'A5'], poster: ['A3', 'Tabloid', 'A4', 'Letter'], brochure: ['A4', 'Letter'], social_post: ['Social', 'SocialP', 'Story'],
  business_card: ['BizCard', 'BizCardEU'], infographic: ['A4', 'Letter', 'A3'], invitation: ['A4', 'Letter', 'A5'], menu: ['A4', 'Letter', 'A3'], certificate: ['A4', 'Letter'],
  tickets: ['A4', 'Letter'], card: ['A4', 'Letter'] };
D.sizesFor = function (type) { return D.TYPE_SIZES[type] || ['A4', 'Letter']; };
D.pxToTw = function (px) { return Math.round(px * 15); };          // 1 px = 15 twips
D.pxToEmu = function (px) { return Math.round(px * 9525); };       // 1 px = 9525 EMU
D.ptToPx = function (pt) { return pt * 96 / 72; };

/* ---------- fonts: Office fonts (what Word uses) + a web look-alike for the site preview ---------- */
D.FONTS = {
  'Aptos': { web: 'Inter', kind: 'sans' },
  'Aptos Display': { web: 'Inter Tight', kind: 'sans' },
  'Calibri': { web: 'Carlito', kind: 'sans' },
  'Calibri Light': { web: 'Carlito', kind: 'sans' },
  'Cambria': { web: 'Caladea', kind: 'serif' },
  'Georgia': { web: 'Gelasio', kind: 'serif' },
  'Garamond': { web: 'EB Garamond', kind: 'serif' },
  'Century Gothic': { web: 'Questrial', kind: 'sans' },
  'Franklin Gothic Medium': { web: 'Libre Franklin', kind: 'sans' },
  'Gill Sans MT': { web: 'Lato', kind: 'sans' },
  'Rockwell': { web: 'Roboto Slab', kind: 'serif' },
  'Tw Cen MT': { web: 'Jost', kind: 'sans' },
  'Trebuchet MS': { web: 'Fira Sans', kind: 'sans' },
  'Segoe UI': { web: 'Open Sans', kind: 'sans' },
  'Bahnschrift': { web: 'Barlow Semi Condensed', kind: 'sans' },
  'Arial': { web: 'Arimo', kind: 'sans' },
  'Arial Black': { web: 'Archivo Black', kind: 'sans' },
  'Book Antiqua': { web: 'Libre Baskerville', kind: 'serif' },
  'Bodoni MT': { web: 'Libre Bodoni', kind: 'serif' },
  'Constantia': { web: 'Crimson Pro', kind: 'serif' },
  'Corbel': { web: 'Source Sans 3', kind: 'sans' },
  'Times New Roman': { web: 'Tinos', kind: 'serif' },
  'Verdana': { web: 'Noto Sans', kind: 'sans' },
  'Impact': { web: 'Anton', kind: 'sans' },
  'Lucida Handwriting': { web: 'Dancing Script', kind: 'script' },
  'Segoe Script': { web: 'Dancing Script', kind: 'script' }
};
D.css = function (font) {
  var f = D.FONTS[font] || { web: 'Inter', kind: 'sans' };
  var tail = f.kind === 'serif' ? 'Georgia, serif' : f.kind === 'script' ? 'cursive' : 'Segoe UI, Arial, sans-serif';
  return '"' + font + '", "' + f.web + '", "Nirmala UI", "Noto Sans", ' + tail;
};
/** Google Fonts link for the web look-alikes of the given Office fonts. */
D.fontLink = function (fonts) {
  var seen = {}, fam = [];
  (fonts || Object.keys(D.FONTS)).forEach(function (f) {
    var w = (D.FONTS[f] || {}).web;
    if (w && !seen[w]) { seen[w] = 1; fam.push('family=' + w.replace(/ /g, '+') + ':ital,wght@0,400;0,600;0,700;1,400'); }
  });
  return 'https://fonts.googleapis.com/css2?' + fam.join('&') + '&display=swap';
};
/** Fonts Word should use for scripts the main font doesn't cover (Indic, Arabic, CJK). */
D.scriptFonts = function (lang) {
  lang = String(lang || '').toLowerCase();
  if (/hindi|kannada|tamil|telugu|marathi|bengali|gujarati|malayalam|punjabi|odia|nepali|sanskrit/.test(lang)) return { cs: 'Nirmala UI', ea: '' };
  if (/arabic|urdu|persian|farsi/.test(lang)) return { cs: 'Arial', ea: '' };
  if (/chinese/.test(lang)) return { cs: '', ea: 'Microsoft YaHei' };
  if (/japanese/.test(lang)) return { cs: '', ea: 'Yu Gothic' };
  if (/korean/.test(lang)) return { cs: '', ea: 'Malgun Gothic' };
  return { cs: '', ea: '' };
};

/* ---------- colour & typography themes ---------- */
// p = primary, s = secondary, a = accent, ink = text, mute = secondary text, tint = light fill, line = rules
function th(id, name, p, s, a, ink, mute, tint, line, head, body) {
  return { id: id, name: name, p: p, s: s, a: a, ink: ink, mute: mute, tint: tint, line: line, head: head, body: body };
}
D.THEMES = [
  th('navy-gold', 'Navy & Gold', '#14213D', '#274C77', '#E0A526', '#1C1E26', '#5E6573', '#F3F5F9', '#D9DEE7', 'Georgia', 'Aptos'),
  th('ocean', 'Ocean', '#0B4F8A', '#2F80C2', '#14B8A6', '#14202E', '#566577', '#EEF5FB', '#D3E2F0', 'Aptos Display', 'Aptos'),
  th('emerald', 'Emerald', '#0B6E4F', '#16A06A', '#F2B134', '#14231D', '#52645B', '#EEF7F2', '#D2E8DC', 'Century Gothic', 'Calibri'),
  th('crimson', 'Crimson', '#8F1D2C', '#C8364B', '#F2A541', '#231A1C', '#6A5A5D', '#FBF1F2', '#EED5D9', 'Rockwell', 'Calibri'),
  th('royal', 'Royal Violet', '#3A1C9C', '#6E3FE0', '#F04E98', '#1D1830', '#5F5A77', '#F4F1FD', '#DFD7F8', 'Bahnschrift', 'Segoe UI'),
  th('charcoal-lime', 'Charcoal & Lime', '#22262E', '#3D4450', '#8BC34A', '#1A1D22', '#5D636E', '#F2F4F1', '#DADDE2', 'Franklin Gothic Medium', 'Aptos'),
  th('terracotta', 'Terracotta', '#9C4A35', '#D08A5F', '#2A8C82', '#2A201C', '#6B5D56', '#FBF4EE', '#EBDACB', 'Book Antiqua', 'Gill Sans MT'),
  th('slate-sky', 'Slate & Sky', '#2F3E52', '#5B6B80', '#2BA8E0', '#1C2430', '#5E6978', '#F1F5F9', '#D8E0E9', 'Segoe UI', 'Segoe UI'),
  th('teal-coral', 'Teal & Coral', '#0E6E73', '#4BA3A4', '#EE7B5C', '#162A2B', '#566A6B', '#EEF7F6', '#D0E6E5', 'Trebuchet MS', 'Calibri'),
  th('midnight', 'Midnight Amber', '#101828', '#1F2A44', '#F5A524', '#141A26', '#5A6273', '#F2F4F7', '#D7DCE4', 'Aptos Display', 'Aptos'),
  th('forest', 'Forest', '#1E4636', '#3F7D5C', '#D9A441', '#17231D', '#55645B', '#F1F6F2', '#D5E3D9', 'Garamond', 'Georgia'),
  th('rose', 'Rose', '#8C3A5C', '#C8698E', '#F2A65A', '#2A1C22', '#6E5A63', '#FCF2F5', '#F0D6E0', 'Constantia', 'Corbel'),
  th('mono', 'Monochrome Red', '#111111', '#3B3B3B', '#E02F44', '#151515', '#5C5C5C', '#F4F4F4', '#DDDDDD', 'Arial Black', 'Arial'),
  th('sunset', 'Sunset', '#7A1E48', '#D9480F', '#F9B529', '#2A1A20', '#6D5A61', '#FDF3EE', '#F2D9CC', 'Tw Cen MT', 'Calibri'),
  th('arctic', 'Arctic', '#0B6E8A', '#28B5D3', '#F2784B', '#14252B', '#556B72', '#EEF8FB', '#CFE8F0', 'Century Gothic', 'Century Gothic'),
  th('sand', 'Sand & Ink', '#5C4535', '#A9805E', '#2F5D7C', '#2A231E', '#6E6259', '#F9F5F0', '#E8DED3', 'Garamond', 'Gill Sans MT'),
  th('indigo-mint', 'Indigo & Mint', '#2E2A86', '#4F46E5', '#12B886', '#1B1A33', '#5B5A78', '#F2F2FD', '#DADAF5', 'Segoe UI', 'Aptos'),
  th('burgundy', 'Burgundy & Cream', '#5E1220', '#8E2F3D', '#C2964A', '#2B1A1C', '#6C5A5B', '#FAF5EE', '#EADFD2', 'Bodoni MT', 'Garamond'),
  th('steel-orange', 'Steel & Orange', '#263238', '#455A64', '#FF7043', '#1C2326', '#5E6A70', '#F2F4F5', '#D9DEE0', 'Bahnschrift', 'Calibri'),
  th('lavender', 'Lavender', '#4B3F99', '#8C7AE6', '#F7A1C4', '#221F33', '#625D78', '#F6F4FD', '#E3DEF8', 'Gill Sans MT', 'Gill Sans MT'),
  th('black-gold', 'Black & Gold', '#0D0D0D', '#2B2B2B', '#C9A227', '#151515', '#5C5C5C', '#F6F4EE', '#E2DDCF', 'Bodoni MT', 'Georgia'),
  th('mint-navy', 'Mint & Navy', '#0A2342', '#2CA58D', '#F2C14E', '#122033', '#566173', '#EEF8F5', '#D2E9E3', 'Aptos Display', 'Aptos'),
  th('cherry', 'Cherry Pop', '#B0127F', '#6A1B9A', '#2EC4F1', '#26142A', '#6B5770', '#FDF0F8', '#F2D4E7', 'Arial Black', 'Verdana'),
  th('classic', 'Classic Blue', '#1F3864', '#2E74B5', '#C55A11', '#1A1A1A', '#595959', '#F2F6FB', '#D6E0EE', 'Cambria', 'Calibri')
];
D.theme = function (id) {
  for (var i = 0; i < D.THEMES.length; i++) if (D.THEMES[i].id === id) return D.THEMES[i];
  return D.THEMES[0];
};

/* ---------- colour helpers ---------- */
D.hex = function (c) { c = String(c || '#000').replace('#', ''); if (c.length === 3) c = c.replace(/(.)/g, '$1$1'); return c.toUpperCase(); };
D.rgb = function (c) { var h = D.hex(c); return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)]; };
D.mix = function (a, b, t) {
  var x = D.rgb(a), y = D.rgb(b);
  return '#' + [0, 1, 2].map(function (i) { return ('0' + Math.round(x[i] + (y[i] - x[i]) * t).toString(16)).slice(-2); }).join('').toUpperCase();
};
D.lum = function (c) { var r = D.rgb(c).map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }); return 0.2126 * r[0] + 0.7152 * r[1] + 0.0722 * r[2]; };
D.cr = function (a, b) { var x = D.lum(a), y = D.lum(b); return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05); };
D.readable = function (c, bg, min) { min = min || 4.5; var k = 0, o = c; while (k < 20 && D.cr(o, bg) < min) { k++; o = D.mix(c, D.lum(bg) > 0.4 ? '#121418' : '#FFFFFF', k * 0.05); } return o; };
D.darkAt = function (t) { var bg = D.lum(t.p) < 0.3 ? t.p : '#1E2026'; return '--at:' + D.readable(t.a, bg, 4.5) + ';--st:' + D.readable(t.a, bg, 4.5); };
D.onColor = function (c) { return D.cr(c, '#141414') > D.cr(c, '#FFFFFF') * 1.25 ? '#141414' : '#FFFFFF'; };

/* ---------- small utilities ---------- */
D.esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
D.clone = function (o) { return JSON.parse(JSON.stringify(o)); };
D.uid = function () { return 'b' + Math.random().toString(36).slice(2, 9); };
/** Inline marks: **bold**, *italic*. Returns [{t, b, i}] runs. */
D.runs = function (text) {
  var out = [], s = String(text == null ? '' : text), re = /(\*\*([^*]+)\*\*|\*([^*]+)\*)/g, last = 0, m;
  while ((m = re.exec(s))) {
    if (m.index > last) out.push({ t: s.slice(last, m.index) });
    if (m[2] != null) out.push({ t: m[2], b: 1 }); else out.push({ t: m[3], i: 1 });
    last = re.lastIndex;
  }
  if (last < s.length) out.push({ t: s.slice(last) });
  return out.length ? out : [{ t: '' }];
};
D.runsHTML = function (text) {
  return D.runs(text).map(function (r) {
    var t = D.esc(r.t).replace(/\n/g, '<br>');
    return r.b ? '<b>' + t + '</b>' : r.i ? '<i>' + t + '</i>' : t;
  }).join('');
};
/** contenteditable HTML back to the **bold** / *italic* text format. */
D.htmlToText = function (html) {
  var div = document.createElement('div');
  div.innerHTML = String(html || '').replace(/<br\s*\/?>/gi, '\n').replace(/<\/(div|p)>/gi, '\n');
  function walk(n) {
    if (n.nodeType === 3) return n.nodeValue;
    var inner = Array.prototype.map.call(n.childNodes, walk).join('');
    var tag = (n.tagName || '').toLowerCase(), st = n.getAttribute ? (n.getAttribute('style') || '') : '';
    if (!inner.trim()) return inner;
    if (tag === 'b' || tag === 'strong' || /font-weight:\s*(bold|[6-9]00)/.test(st)) return '**' + inner + '**';
    if (tag === 'i' || tag === 'em' || /font-style:\s*italic/.test(st)) return '*' + inner + '*';
    return inner;
  }
  return walk(div).replace(/ /g, ' ').replace(/\n{3,}/g, '\n\n').replace(/\n+$/, '');
};
/* ---------- artwork: covers, page decoration, letterheads, single-page backgrounds ----------
 * Everything here is drawn as SVG in the theme colours (no text). In the browser it is shown
 * behind the page; in Word it becomes a PNG placed behind the text. Text always stays real text.
 */
D.ART = { cover: {}, page: {}, single: {}, head: {} };

function svg(W, H, inner, defs) {
  return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="' + W + '" height="' + H +
    '" viewBox="0 0 ' + W + ' ' + H + '">' + (defs ? '<defs>' + defs + '</defs>' : '') + inner + '</svg>';
}
function rect(x, y, w, h, fill, op, rx) { return '<rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '"' + (rx ? ' rx="' + rx + '"' : '') + ' fill="' + fill + '"' + (op != null ? ' fill-opacity="' + op + '"' : '') + '/>'; }
function circ(cx, cy, r, fill, op) { return '<circle cx="' + cx + '" cy="' + cy + '" r="' + r + '" fill="' + fill + '"' + (op != null ? ' fill-opacity="' + op + '"' : '') + '/>'; }
function ring(cx, cy, r, stroke, w, op) { return '<circle cx="' + cx + '" cy="' + cy + '" r="' + r + '" fill="none" stroke="' + stroke + '" stroke-width="' + w + '"' + (op != null ? ' stroke-opacity="' + op + '"' : '') + '/>'; }
function poly(pts, fill, op) { return '<polygon points="' + pts.map(function (p) { return p[0] + ',' + p[1]; }).join(' ') + '" fill="' + fill + '"' + (op != null ? ' fill-opacity="' + op + '"' : '') + '/>'; }
function path(d, fill, op, stroke, sw) { return '<path d="' + d + '" fill="' + (fill || 'none') + '"' + (op != null ? ' fill-opacity="' + op + '"' : '') + (stroke ? ' stroke="' + stroke + '" stroke-width="' + (sw || 1) + '"' : '') + '/>'; }
function line(x1, y1, x2, y2, c, w, op) { return '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + c + '" stroke-width="' + (w || 1) + '"' + (op != null ? ' stroke-opacity="' + op + '"' : '') + '/>'; }
function grad(id, a, b, x2, y2) { return '<linearGradient id="' + id + '" x1="0" y1="0" x2="' + (x2 == null ? 1 : x2) + '" y2="' + (y2 == null ? 1 : y2) + '"><stop offset="0" stop-color="' + a + '"/><stop offset="1" stop-color="' + b + '"/></linearGradient>'; }
function dots(x, y, cols, rows, gap, r, fill, op) {
  var s = '';
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) s += circ(x + i * gap, y + j * gap, r, fill, op);
  return s;
}
function photo(img, x, y, w, h, id, rx) {
  if (!img) return '';
  return '<clipPath id="' + id + '"><rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '"' + (rx ? ' rx="' + rx + '"' : '') + '/></clipPath>' +
    '<image href="' + img + '" xlink:href="' + img + '" x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" preserveAspectRatio="xMidYMid slice" clip-path="url(#' + id + ')"/>';
}
function wave(W, y, amp, fill, op, phase, H) {
  var d = 'M0 ' + y, n = 4, seg = W / n;
  for (var i = 0; i < n; i++) {
    var x0 = i * seg, s = (i + (phase || 0)) % 2 ? 1 : -1;
    d += ' C ' + (x0 + seg * 0.35) + ' ' + (y + s * amp) + ', ' + (x0 + seg * 0.65) + ' ' + (y + s * amp) + ', ' + (x0 + seg) + ' ' + y;
  }
  return path(d + ' L ' + W + ' ' + (H || 2000) + ' L 0 ' + (H || 2000) + ' Z', fill, op);
}
function blob(cx, cy, r, seed, fill, op) {
  var pts = [], n = 7;
  for (var i = 0; i < n; i++) {
    var a = (i / n) * Math.PI * 2, k = 0.78 + 0.32 * Math.abs(Math.sin(seed * 9.1 + i * 2.3));
    pts.push([cx + Math.cos(a) * r * k, cy + Math.sin(a) * r * k]);
  }
  var d = 'M' + ((pts[0][0] + pts[n - 1][0]) / 2) + ' ' + ((pts[0][1] + pts[n - 1][1]) / 2);
  for (var j = 0; j < n; j++) {
    var p = pts[j], q = pts[(j + 1) % n];
    d += ' Q ' + p[0] + ' ' + p[1] + ' ' + ((p[0] + q[0]) / 2) + ' ' + ((p[1] + q[1]) / 2);
  }
  return path(d + ' Z', fill, op);
}

/* Text zones: where the cover text sits (page px). dark = white text on colour. */
function Z(x, y, w, align, dark, extra) { var z = { x: x, y: y, w: w, align: align || 'left', dark: !!dark }; for (var k in extra || {}) z[k] = extra[k]; return z; }

/* ---------------- covers ---------------- */
D.ART.cover.diagonal = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') +
      poly([[0, 0], [W, 0], [W, H * 0.5], [0, H * 0.62]], t.p) +
      poly([[0, H * 0.62], [W, H * 0.5], [W, H * 0.535], [0, H * 0.655]], t.s) +
      poly([[W * 0.62, H * 0.555], [W, H * 0.5], [W, H * 0.522]], t.a) +
      dots(W - 150, 70, 6, 4, 18, 2.4, '#FFFFFF', 0.35) +
      rect(64, H - 120, 56, 6, t.a)),
    zone: Z(72, H * 0.17, W - 160, 'left', true, { meta: Z(72, H - 100, W - 144, 'left', false) })
  };
};
D.ART.cover.split = function (t, W, H) {
  var pw = W * 0.36;
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, pw, H, t.p) +
      ring(pw * 0.5, H * 0.24, 90, '#FFFFFF', 2, 0.25) + ring(pw * 0.5, H * 0.24, 130, '#FFFFFF', 1, 0.15) +
      circ(pw, H * 0.74, 46, t.a) + rect(pw - 3, 0, 6, H, t.s) + dots(30, H - 170, 5, 5, 20, 2.6, '#FFFFFF', 0.3) +
      rect(pw + 56, H * 0.3 - 30, 64, 6, t.a)),
    zone: Z(pw + 56, H * 0.3, W - pw - 110, 'left', false, { meta: Z(pw + 56, H - 120, W - pw - 110, 'left', false) })
  };
};
D.ART.cover.frame = function (t, W, H) {
  var c = t.p;
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + '<rect x="34" y="34" width="' + (W - 68) + '" height="' + (H - 68) + '" fill="none" stroke="' + c + '" stroke-width="3"/>' +
      '<rect x="46" y="46" width="' + (W - 92) + '" height="' + (H - 92) + '" fill="none" stroke="' + c + '" stroke-width="1"/>' +
      poly([[W / 2, H * 0.27 - 14], [W / 2 + 14, H * 0.27], [W / 2, H * 0.27 + 14], [W / 2 - 14, H * 0.27]], t.a) +
      line(W / 2 - 120, H * 0.27, W / 2 - 26, H * 0.27, c, 1) + line(W / 2 + 26, H * 0.27, W / 2 + 120, H * 0.27, c, 1) +
      line(W / 2 - 70, H * 0.62, W / 2 + 70, H * 0.62, t.a, 2)),
    zone: Z(110, H * 0.33, W - 220, 'center', false, { meta: Z(110, H * 0.68, W - 220, 'center', false) })
  };
};
D.ART.cover.waves = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + wave(W, H * 0.66, 34, t.tint, 1, 0, H) + wave(W, H * 0.72, 40, t.s, 0.85, 1, H) +
      wave(W, H * 0.78, 30, t.p, 1, 0, H) + wave(W, H * 0.9, 22, t.a, 0.9, 1, H) + rect(72, 90, 48, 6, t.a)),
    zone: Z(72, H * 0.14, W - 150, 'left', false, { meta: Z(72, H * 0.52, W - 150, 'left', false) })
  };
};
D.ART.cover.geo = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + poly([[W * 0.38, 0], [W, 0], [W, H * 0.4]], t.p) + poly([[W * 0.62, 0], [W, 0], [W, H * 0.22]], t.s) +
      poly([[W * 0.82, H * 0.3], [W, H * 0.2], [W, H * 0.46]], t.a) + poly([[0, H], [0, H * 0.8], [W * 0.26, H]], t.p) +
      poly([[0, H], [0, H * 0.9], [W * 0.13, H]], t.a) + dots(W * 0.45, H * 0.86, 7, 3, 18, 2.4, t.s, 0.5)),
    zone: Z(72, H * 0.44, W - 200, 'left', false, { meta: Z(72, H * 0.74, W - 200, 'left', false) })
  };
};
D.ART.cover.photo = function (t, W, H, img) {
  var ph = H * 0.56, defs = grad('pg', t.p, t.s, 1, 1) + '<linearGradient id="fade" x1="0" y1="0" x2="0" y2="1"><stop offset="0.45" stop-color="' + t.p + '" stop-opacity="0"/><stop offset="1" stop-color="' + t.p + '" stop-opacity="0.85"/></linearGradient>';
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W, ph, 'url(#pg)') + photo(img, 0, 0, W, ph, 'cp') +
      rect(0, 0, W, ph, 'url(#fade)') + rect(0, ph, W, 12, t.a) + rect(72, ph + 60, 56, 6, t.s), defs),
    zone: Z(72, ph + 82, W - 150, 'left', false, { meta: Z(72, H - 110, W - 150, 'left', false), photo: true })
  };
};
D.ART.cover.gradient = function (t, W, H) {
  var defs = grad('g1', t.p, t.s, 1, 1);
  return {
    svg: svg(W, H, rect(0, 0, W, H, 'url(#g1)') + circ(W * 0.92, H * 0.1, 260, '#FFFFFF', 0.06) + circ(W * 0.85, H * 0.18, 150, '#FFFFFF', 0.07) +
      circ(W * 0.08, H * 0.95, 220, t.a, 0.22) + ring(W * 0.8, H * 0.78, 120, '#FFFFFF', 2, 0.2) + rect(72, H * 0.33 - 34, 56, 6, t.a), defs),
    zone: Z(72, H * 0.33, W - 170, 'left', true, { meta: Z(72, H - 120, W - 150, 'left', true) })
  };
};
D.ART.cover.minimal = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(72, 72, 34, 34, t.a) + rect(112, 72, 34, 34, t.p) + line(72, H * 0.78, W - 72, H * 0.78, t.line, 1.5) +
      rect(W - 120, H - 60, 48, 6, t.p)),
    zone: Z(72, H * 0.3, W - 160, 'left', false, { meta: Z(72, H * 0.8, W - 144, 'left', false) })
  };
};
D.ART.cover.corner = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(W - 300, 0, 300, 230, t.p) + rect(W - 160, 230, 160, 110, t.s) + rect(W - 300, 230, 140, 56, t.a) +
      rect(0, H - 210, 210, 210, t.s) + rect(0, H - 120, 120, 120, t.p) + rect(210, H - 64, 64, 64, t.a)),
    zone: Z(72, H * 0.38, W - 200, 'left', false, { meta: Z(72, H * 0.68, W - 200, 'left', false) })
  };
};
D.ART.cover.stripes = function (t, W, H) {
  var s = '';
  for (var i = -10; i < 30; i++) s += poly([[i * 40, H * 0.62], [i * 40 + 20, H * 0.62], [i * 40 + 20 - 120, H * 0.82], [i * 40 - 120, H * 0.82]], i % 3 ? t.p : t.a, i % 3 ? 0.9 : 1);
  return {
    svg: svg(W, H, rect(0, 0, W, H, t.tint) + '<clipPath id="sb"><rect x="0" y="' + H * 0.62 + '" width="' + W + '" height="' + H * 0.2 + '"/></clipPath><g clip-path="url(#sb)">' + s + '</g>' +
      rect(0, H * 0.82, W, H * 0.18, t.p) + rect(72, H * 0.18 - 30, 56, 6, t.a)),
    zone: Z(72, H * 0.18, W - 150, 'left', false, { meta: Z(72, H * 0.86, W - 150, 'left', true) })
  };
};
D.ART.cover.circles = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + circ(W * 0.78, H * 0.2, 230, t.p, 0.95) + circ(W * 0.52, H * 0.12, 140, t.a, 0.85) +
      circ(W * 0.95, H * 0.42, 150, t.s, 0.85) + ring(W * 0.66, H * 0.36, 70, t.p, 3, 0.6) + circ(W * 0.1, H * 0.92, 60, t.tint) + circ(W * 0.18, H * 0.88, 18, t.a)),
    zone: Z(72, H * 0.58, W - 160, 'left', false, { meta: Z(72, H * 0.83, W - 160, 'left', false) })
  };
};
D.ART.cover.book = function (t, W, H) {
  var a = t.a;
  return {
    svg: svg(W, H, rect(0, 0, W, H, t.p) + '<rect x="40" y="40" width="' + (W - 80) + '" height="' + (H - 80) + '" fill="none" stroke="' + a + '" stroke-width="2"/>' +
      '<rect x="52" y="52" width="' + (W - 104) + '" height="' + (H - 104) + '" fill="none" stroke="' + a + '" stroke-width="0.8"/>' +
      poly([[W / 2, H * 0.24 - 12], [W / 2 + 12, H * 0.24], [W / 2, H * 0.24 + 12], [W / 2 - 12, H * 0.24]], a) +
      line(W / 2 - 150, H * 0.24, W / 2 - 22, H * 0.24, a, 1) + line(W / 2 + 22, H * 0.24, W / 2 + 150, H * 0.24, a, 1) +
      line(W / 2 - 60, H * 0.66, W / 2 + 60, H * 0.66, a, 1.5)),
    zone: Z(110, H * 0.32, W - 220, 'center', true, { meta: Z(110, H * 0.72, W - 220, 'center', true) })
  };
};
D.ART.cover.academic = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + line(96, H * 0.3, W - 96, H * 0.3, t.p, 2) + line(96, H * 0.3 + 6, W - 96, H * 0.3 + 6, t.p, 0.7) +
      line(96, H * 0.58, W - 96, H * 0.58, t.p, 0.7) + line(96, H * 0.58 + 6, W - 96, H * 0.58 + 6, t.p, 2)),
    zone: Z(110, H * 0.34, W - 220, 'center', false, { meta: Z(110, H * 0.64, W - 220, 'center', false) })
  };
};
D.ART.cover.notebook = function (t, W, H) {
  var s = '';
  for (var y = 140; y < H - 40; y += 30) s += line(0, y, W, y, t.s, 0.8, 0.18);
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + line(92, 0, 92, H, t.a, 1.6, 0.7) + rect(W / 2 - 80, 18, 160, 40, t.a, 0.55) +
      circ(46, H * 0.25, 9, t.line) + circ(46, H * 0.5, 9, t.line) + circ(46, H * 0.75, 9, t.line) + rect(120, H * 0.62, W - 220, 150, t.tint, 1, 10)),
    zone: Z(124, H * 0.24, W - 230, 'left', false, { meta: Z(140, H * 0.64, W - 260, 'left', false) })
  };
};
D.ART.cover.blob = function (t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, t.tint) + blob(W * 0.88, H * 0.14, 230, 1, t.s, 0.9) + blob(W * 0.68, H * 0.05, 120, 2, t.a, 0.9) +
      blob(W * 0.08, H * 0.88, 210, 3, t.p, 0.95) + blob(W * 0.3, H * 0.97, 110, 4, t.a, 0.8) + dots(W - 210, H * 0.5, 6, 6, 20, 3, t.p, 0.25)),
    zone: Z(80, H * 0.36, W - 200, 'left', false, { meta: Z(80, H * 0.62, W - 200, 'left', false) })
  };
};

/* ---------------- corporate cover collection ----------------
 * Each concept is its own composition (not a colour swap of another). A concept can be drawn mirrored
 * (v.f) or with its alternative arrangement (v.b), so the same idea never looks the same twice.
 * Only plain shapes are used (rect, circle, polygon, path, line, linear gradients), so every shape
 * becomes an editable element in the editor and a clean picture in Word.
 */
function KIT(W, H, f) {
  var X = function (x) { return f ? W - x : x; };
  function pts(a) { return a.map(function (p) { return [X(p[0]), p[1]]; }); }
  function d(cmds) {               /* ['M',x,y,'L',x,y,'C',x1,y1,x2,y2,x,y,'Q',x1,y1,x,y,'A',rx,ry,rot,large,sweep,x,y,'Z'] */
    var out = [], i = 0;
    while (i < cmds.length) {
      var c = cmds[i++];
      if (c === 'Z') { out.push('Z'); continue; }
      if (c === 'A') { var rx = cmds[i++], ry = cmds[i++], rot = cmds[i++], lg = cmds[i++], sw = cmds[i++], ax = cmds[i++], ay = cmds[i++]; out.push('A' + rx + ' ' + ry + ' ' + rot + ' ' + lg + ' ' + (f ? 1 - sw : sw) + ' ' + r2(X(ax)) + ' ' + r2(ay)); continue; }
      var n = c === 'C' ? 3 : c === 'Q' ? 2 : 1, s = c;
      for (var k = 0; k < n; k++) { s += (k ? ' ' : '') + r2(X(cmds[i++])) + ' ' + r2(cmds[i++]); }
      out.push(s);
    }
    return out.join(' ');
  }
  function r2(v) { return Math.round(v * 10) / 10; }
  return {
    X: X,
    r: function (x, y, w, h, fill, op, rx) { return rect(r2(f ? W - x - w : x), r2(y), r2(w), r2(h), fill, op, rx); },
    c: function (cx, cy, r, fill, op) { return circ(r2(X(cx)), r2(cy), r2(r), fill, op); },
    o: function (cx, cy, r, stroke, w, op) { return ring(r2(X(cx)), r2(cy), r2(r), stroke, w, op); },
    p: function (a, fill, op) { return poly(pts(a).map(function (q) { return [r2(q[0]), r2(q[1])]; }), fill, op); },
    l: function (x1, y1, x2, y2, c, w, op) { return line(r2(X(x1)), r2(y1), r2(X(x2)), r2(y2), c, w, op); },
    d: function (cmds, fill, op, stroke, sw) { return path(d(cmds), fill, op, stroke, sw); },
    s: function (cmds, stroke, w, op) { return '<path d="' + d(cmds) + '" fill="none" stroke="' + stroke + '" stroke-width="' + w + '"' + (op != null ? ' stroke-opacity="' + op + '"' : '') + ' stroke-linecap="round"/>'; },
    z: function (x, y, w, align, dark, extra) { return Z(r2(f ? W - x - w : x), y, w, f && !extra && align === 'left' ? 'right' : align, dark, extra); },   /* mirrored: names and dates line up on the right */
    dots: function (x, y, cols, rows, gap, r, fill, op) { var s = ''; for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) s += circ(r2(X(x + i * gap)), r2(y + j * gap), r, fill, op); return s; },
    g: function (id, a, b, x1, y1, x2, y2) { /* gradient that follows the mirror */ return '<linearGradient id="' + id + '" x1="' + (f ? 1 - x1 : x1) + '" y1="' + y1 + '" x2="' + (f ? 1 - x2 : x2) + '" y2="' + y2 + '"><stop offset="0" stop-color="' + a + '"/><stop offset="1" stop-color="' + b + '"/></linearGradient>'; },
    ph: function (img, x, y, w, h, id, rx) { return photo(img, r2(f ? W - x - w : x), r2(y), r2(w), r2(h), id, rx); }
  };
}
function arcPath(k, cx, cy, r, a0, a1, w) {        /* a thick arc (donut segment) from angle a0 to a1 (degrees, 0 = right, clockwise) */
  var rad = Math.PI / 180, ro = r + w / 2, ri = r - w / 2, lg = (a1 - a0) > 180 ? 1 : 0;
  var p = function (rr, a) { return [cx + rr * Math.cos(a * rad), cy + rr * Math.sin(a * rad)]; };
  var A = p(ro, a0), B = p(ro, a1), C = p(ri, a1), E = p(ri, a0);
  return ['M', A[0], A[1], 'A', ro, ro, 0, lg, 1, B[0], B[1], 'L', C[0], C[1], 'A', ri, ri, 0, lg, 0, E[0], E[1], 'Z'];
}
var CORP = {};
/* 1. Monolith: deep colour page, a thin vertical rule and a large quarter ring */
CORP.monolith = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + k.d(arcPath(k, W, H, W * 0.62, 180, 270, 70), t.s, 0.85) + k.d(arcPath(k, W, H, W * 0.62 - 92, 180, 270, 16), t.a) +
      k.r(72, 72, 3, H * 0.34, t.a) + k.dots(W - 210, 80, 7, 3, 20, 2.2, '#FFFFFF', 0.28)),
    zone: k.z(104, H * 0.18, W * 0.62, 'left', true, { meta: k.z(104, H - 150, W * 0.5, 'left', true) }) };
};
/* 2. Halftone: a corner of dots that grow towards the corner, clean white page */
CORP.halftone = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', g = 26;
  for (var i = 0; i < 16; i++) for (var j = 0; j < 18; j++) {
    var x = W - 20 - i * g, y = 20 + j * g, dd = Math.sqrt(i * i + j * j * 0.6), r = Math.max(0, 9 - dd * 0.72);
    if (r > 0.8) s += k.c(x, y, r, j % 5 === 0 && i % 4 === 1 ? t.a : t.p, 0.9);
  }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, H - 22, W, 22, t.p) + k.r(0, H - 22, W * 0.34, 22, t.a) + k.r(72, H * 0.5 - 36, 64, 5, t.a)),
    zone: k.z(72, H * 0.5, W - 170, 'left', false, { meta: k.z(72, H - 160, W * 0.6, 'left', false) }) };
};
/* 3. Arch: a tall arch window in colour on a warm light page */
CORP.arch = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), ax = 72, aw = W - 144, top = 96, bot = H * 0.66;
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + k.d(['M', ax, bot, 'L', ax, top + aw / 2, 'A', aw / 2, aw / 2, 0, 0, 1, ax + aw, top + aw / 2, 'L', ax + aw, bot, 'Z'], t.p) +
      k.c(W / 2, top + aw / 2, aw * 0.34, '#FFFFFF', 0.06) + k.o(W / 2, top + aw / 2, aw * 0.44, '#FFFFFF', 1.2, 0.18) + k.r(ax, bot, aw, 8, t.a) + k.r(ax + aw / 2 - 30, bot + 64, 60, 4, t.s)),
    zone: Z(ax + 50, top + aw * 0.42, aw - 100, 'center', true, { meta: Z(ax, bot + 96, aw, 'center', false) }) };
};
/* 4. Bauhaus tiles: quarter circles and squares in a 3x3 block */
CORP.bauhaus = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), cs = 132, x0 = W - 72 - cs * 3, y0 = 72, cols = [t.p, t.s, t.a, t.tint], s = '';
  var pat = [[0, 1, 2], [3, 0, 1], [1, 2, 0]], quad = [[0, 2, 1], [3, 1, 0], [2, 0, 3]];
  for (var i = 0; i < 3; i++) for (var j = 0; j < 3; j++) {
    var x = x0 + i * cs, y = y0 + j * cs, c = cols[pat[j][i]], q = quad[j][i];
    s += k.r(x, y, cs, cs, j === 1 && i === 1 ? '#FFFFFF' : t.tint);
    var cx = x + (q === 1 || q === 2 ? cs : 0), cy = y + (q >= 2 ? cs : 0);
    s += '<clipPath id="bq' + i + j + '">' + k.r(x, y, cs, cs, '#000').replace('fill="#000"', '') + '</clipPath><g clip-path="url(#bq' + i + j + ')">' + k.c(cx, cy, cs, c) + '</g>';
  }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(72, H - 96, 220, 8, t.p) + k.r(72, H - 96, 70, 8, t.a)),
    zone: k.z(72, y0 + cs * 3 + 70, W - 160, 'left', false, { meta: k.z(72, H - 190, W * 0.6, 'left', false) }) };
};
/* 5. Ribbon: a folded diagonal ribbon across a white page */
CORP.ribbon = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), y = H * 0.38;
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.p([[0, y + 40], [W * 0.62, y - 40], [W * 0.62, y + 70], [0, y + 150]], t.p) +
      k.p([[W * 0.62, y - 40], [W * 0.74, y + 6], [W * 0.62, y + 70]], t.s) + k.p([[W * 0.74, y + 6], [W, y - 50], [W, y + 60], [W * 0.74, y + 116]], t.p, 0.92) +
      k.p([[W * 0.62, y + 70], [W * 0.74, y + 6], [W * 0.74, y + 116]], '#000000', 0.18) + k.r(72, 88, 44, 44, t.a, 1, 4) + k.r(124, 88, 44, 44, t.tint, 1, 4)),
    zone: k.z(72, y + 230, W - 170, 'left', false, { meta: k.z(72, H - 160, W * 0.6, 'left', false) }) };
};
/* 6. Columns: a bar-chart skyline of slim columns along the bottom */
CORP.columns = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', n = 14, bw = (W - 144) / n, hs = [.3, .45, .38, .6, .52, .7, .64, .82, .74, .9, .86, 1, .95, 1.1];
  for (var i = 0; i < n; i++) { var hh = H * 0.28 * hs[i]; s += k.r(72 + i * bw + 3, H - 110 - hh, bw - 6, hh, i === n - 3 ? t.a : (i % 2 ? t.p : t.s), i === n - 3 ? 1 : 0.9 - (n - i) * 0.025); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(72, H - 106, W - 144, 2, t.ink) + k.r(72, 72, W - 144, 1.5, t.line) + k.r(72, 72, 120, 5, t.a)),
    zone: k.z(72, 150, W - 170, 'left', false, { meta: k.z(72, H - 90, W * 0.7, 'left', false) }) };
};
/* 7. Isometric cubes band */
CORP.cubes = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', a = 46, hgt = a * Math.sqrt(3) / 2, y0 = 40;
  for (var j = 0; j < 7; j++) for (var i = -1; i < 12; i++) {
    var cx = i * a * 1.5 * 1.0 + (j % 2 ? a * 0.75 : 0) + 20, cy = y0 + j * hgt * 1.5, sh = (i + j) % 5;
    if (cx > W + a) continue;
    var top = [[cx, cy - hgt], [cx + a * 0.75, cy - hgt / 2], [cx, cy], [cx - a * 0.75, cy - hgt / 2]];
    var left = [[cx - a * 0.75, cy - hgt / 2], [cx, cy], [cx, cy + hgt], [cx - a * 0.75, cy + hgt / 2]];
    var right = [[cx, cy], [cx + a * 0.75, cy - hgt / 2], [cx + a * 0.75, cy + hgt / 2], [cx, cy + hgt]];
    s += k.p(top, sh === 0 ? t.a : t.s, 0.95) + k.p(left, t.p, 0.95) + k.p(right, t.p, 0.75);
  }
  var bh = y0 + 7 * hgt * 1.5 - 20;
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + '<clipPath id="cbx"><rect x="0" y="0" width="' + W + '" height="' + bh + '"/></clipPath><g clip-path="url(#cbx)">' + rect(0, 0, W, bh, t.p) + s + '</g>' + rect(0, bh, W, 8, t.a)),
    zone: Z(72, bh + 90, W - 160, 'left', false, { meta: Z(72, H - 150, W * 0.6, 'left', false) }) };
};
/* 8. Concentric arcs rising from a corner */
CORP.orbits = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var i = 0; i < 9; i++) s += k.o(0, H, 140 + i * 58, i === 3 ? t.a : t.p, i === 3 ? 6 : 1.4, i === 3 ? 1 : 0.22 + i * 0.04);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.c(0, H, 150, t.p) + k.c(0, H, 92, t.s) + s + k.c(W - 120, 120, 9, t.a) + k.r(W - 230, 116, 90, 1.5, t.line)),
    zone: k.z(W * 0.36, H * 0.16, W * 0.56, 'left', false, { meta: k.z(W * 0.36, H * 0.52, W * 0.56, 'left', false) }) };
};
/* 9. Round photo window over a colour field */
CORP.lens = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), cx = W * 0.64, cy = H * 0.32, r = W * 0.36, defs = k.g('lg9', t.s, t.p, 0, 0, 1, 1);
  var clip = '<clipPath id="lc9"><circle cx="' + k.X(cx) + '" cy="' + cy + '" r="' + r + '"/></clipPath>';
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + k.r(0, 0, W, H * 0.56, t.p) + (img ? '' : '<circle cx="' + k.X(cx) + '" cy="' + cy + '" r="' + r + '" fill="url(#lg9)"/>') +
      (img ? clip + '<image href="' + img + '" xlink:href="' + img + '" x="' + (k.X(cx) - r) + '" y="' + (cy - r) + '" width="' + r * 2 + '" height="' + r * 2 + '" preserveAspectRatio="xMidYMid slice" clip-path="url(#lc9)"/>' : '') +
      k.o(cx, cy, r + 18, t.a, 3) + k.c(cx - r * 0.86, cy + r * 0.72, 22, t.a), defs),
    zone: k.z(72, H * 0.64, W - 170, 'left', false, { meta: k.z(72, H - 140, W * 0.6, 'left', false), photo: true }) };
};
/* 10. Photo half page with a colour title panel */
CORP.halfphoto = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), pw = W * 0.5, defs = k.g('hp10', t.s, t.p, 0, 0, 0, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + (img ? '' : k.r(pw, 0, W - pw, H, 'url(#hp10)')) + k.ph(img, pw, 0, W - pw, H, 'hc10') + k.r(pw - 6, 0, 12, H, t.a) +
      k.r(56, 72, 40, 4, t.a) + k.dots(56, H - 200, 4, 6, 18, 2.2, '#FFFFFF', 0.3), defs),
    zone: k.z(56, H * 0.26, pw - 100, 'left', true, { meta: k.z(56, H - 130, pw - 100, 'left', true), photo: true }) };
};
/* 11. Angled photo band through the middle */
CORP.slash = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), y1 = H * 0.34, y2 = H * 0.62, defs = k.g('sl11', t.s, t.p, 0, 0, 1, 1);
  var band = [[0, y1 + 60], [W, y1 - 30], [W, y2 - 40], [0, y2 + 50]];
  var clip = '<clipPath id="sc11">' + k.p(band, '#000').replace(' fill="#000"', '') + '</clipPath>';
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + (img ? '' : k.p(band, 'url(#sl11)')) + (img ? clip + '<image href="' + img + '" xlink:href="' + img + '" x="0" y="' + (y1 - 40) + '" width="' + W + '" height="' + (y2 - y1 + 100) + '" preserveAspectRatio="xMidYMid slice" clip-path="url(#sc11)"/>' : '') +
      k.p([[0, y2 + 50], [W, y2 - 40], [W, y2 - 26], [0, y2 + 64]], t.a) + k.r(72, 90, 56, 5, t.a), defs),
    zone: k.z(72, 120, W - 170, 'left', false, { meta: k.z(72, H - 150, W * 0.6, 'left', false), photo: true }) };
};
/* 12. Full-bleed photo with an overlapping white title card */
CORP.card = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), ph = H * 0.62, defs = k.g('cd12', t.p, t.s, 0, 0, 1, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + (img ? '' : rect(0, 0, W, ph, 'url(#cd12)')) + photo(img, 0, 0, W, ph, 'cc12') + k.r(56, ph - 170, W * 0.7, 300, '#000000', 0.08, 2) +
      k.r(48, ph - 180, W * 0.7, 300, '#FFFFFF', 1, 2) + k.r(48, ph - 180, 8, 300, t.a), defs),
    zone: k.z(96, ph - 140, W * 0.7 - 90, 'left', false, { meta: k.z(96, H - 150, W * 0.6, 'left', false), photo: true }) };
};
/* 13. Low-poly triangle mosaic header */
CORP.mosaic = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', cw = W / 6, rh = 120, rows = 4, cols = [t.p, t.s, D.mix(t.p, t.s, 0.5), D.mix(t.p, '#000000', 0.25)];
  for (var j = 0; j < rows; j++) for (var i = 0; i < 6; i++) {
    var x = i * cw, y = j * rh, a = (i * 7 + j * 3) % 4, b = (i * 5 + j * 11 + 1) % 4;
    s += k.p([[x, y], [x + cw, y], [x, y + rh]], cols[a]) + k.p([[x + cw, y], [x + cw, y + rh], [x, y + rh]], (i === 4 && j === 1) || (i === 1 && j === 3) ? t.a : cols[b]);
  }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, rows * rh, W, 6, t.a)),
    zone: Z(72, rows * rh + 90, W - 160, 'left', false, { meta: Z(72, H - 150, W * 0.6, 'left', false) }) };
};
/* 14. Contour lines sweeping across a corner */
CORP.contour = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var i = 0; i < 16; i++) {
    var o = i * 22;
    s += k.s(['M', W * 0.25 - o * 0.4, -10, 'C', W * 0.55 + o * 0.2, H * 0.18 + o * 0.5, W * 0.62 - o, H * 0.36 + o * 0.3, W + 10, H * 0.42 + o * 0.9], i === 6 ? t.a : t.p, i === 6 ? 3 : 1.3, i === 6 ? 1 : 0.18 + (i % 4) * 0.08);
  }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, 0, 18, H, t.p) + k.r(0, H * 0.58, 18, 120, t.a)),
    zone: k.z(84, H * 0.56, W - 190, 'left', false, { meta: k.z(84, H - 150, W * 0.6, 'left', false) }) };
};
/* 15. Fine grid paper with one strong colour block */
CORP.gridblock = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', g = 28;
  for (var x = 0; x <= W; x += g) s += line(x, 0, x, H, t.line, 0.6, 0.55);
  for (var y = 0; y <= H; y += g) s += line(0, y, W, y, t.line, 0.6, 0.55);
  var bx = 0, by = g * 5, bw = g * 19, bh = g * 13;
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(bx, by, bw, bh, t.p) + k.r(bx + bw, by + bh - g * 3, g * 3, g * 3, t.a) + k.r(bx + bw - g * 2, by - g * 2, g * 2, g * 2, t.s)),
    zone: k.z(72, by + 64, bw - 120, 'left', true, { meta: k.z(72, H - 150, W * 0.6, 'left', false) }) };
};
/* 16. Growth chevrons pointing forward */
CORP.chevron = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', y = H * 0.66, hh = 190;
  [[t.tint, 0], [t.s, 1], [t.p, 2], [t.a, 3]].forEach(function (c, i) { var x = W * 0.18 + i * 120; s += k.p([[x, y], [x + 120, y], [x + 220, y + hh / 2], [x + 120, y + hh], [x, y + hh], [x + 100, y + hh / 2]], c[0]); });
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(0, 0, W, 16, t.p) + s + k.r(72, 110, 56, 5, t.a)),
    zone: k.z(72, 140, W - 170, 'left', false, { meta: k.z(72, H * 0.52, W * 0.6, 'left', false) }) };
};
/* 17. Horizontal data bars, like a ranking chart */
CORP.databars = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', L = [0.86, 0.64, 0.92, 0.5, 0.74, 0.38];
  L.forEach(function (q, i) { var y = H * 0.58 + i * 48; s += k.r(0, y, (W - 72) * q, 30, i === 2 ? t.a : (i % 2 ? t.s : t.p), 1, 0) + k.c((W - 72) * q, y + 15, 15, i === 2 ? t.a : (i % 2 ? t.s : t.p)); });
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + s + k.r(72, 80, 120, 4, t.p)),
    zone: k.z(72, 120, W - 170, 'left', false, { meta: k.z(72, H * 0.45, W * 0.6, 'left', false) }) };
};
/* 18. Stacked rounded capsules, rotated */
CORP.capsules = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', c = [t.p, t.s, t.a, t.p, t.s];
  for (var i = 0; i < 5; i++) { var x = W * 0.48 + i * 66, y = -60 + i * 70; s += '<g>' + k.d(['M', x + 40, y, 'L', x + 40 + 0, y, 'A', 40, 40, 0, 0, 1, x + 80, y + 40, 'L', x + 80, y + 380, 'A', 40, 40, 0, 0, 1, x, y + 380, 'L', x, y + 40, 'A', 40, 40, 0, 0, 1, x + 40, y, 'Z'], c[i], i === 2 ? 1 : 0.92) + '</g>'; }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(72, H - 92, W - 144, 1.5, t.line) + k.c(80, H - 92, 6, t.a)),
    zone: k.z(72, H * 0.62, W - 170, 'left', false, { meta: k.z(72, H - 150, W * 0.6, 'left', false) }) };
};
/* 19. Hexagon cluster */
CORP.hexes = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', r = 62, hx = function (cx, cy, fill, op) { var a = []; for (var i = 0; i < 6; i++) { var g = Math.PI / 3 * i + Math.PI / 6; a.push([cx + r * Math.cos(g), cy + r * Math.sin(g)]); } return k.p(a, fill, op); };
  var cells = [[0, 0, t.p], [1, 0, t.s], [2, 0, t.tint], [0.5, 1, t.a], [1.5, 1, t.p], [2.5, 1, t.s], [1, 2, t.tint], [2, 2, t.p], [3, 2, t.s, 0.6], [1.5, 3, t.s, 0.5], [3.5, 1, t.tint]];
  cells.forEach(function (c) { s += hx(W - 120 - c[0] * r * 1.74 + (c[1] % 2 ? 0 : 0), 110 + c[1] * r * 1.5, c[2], c[3]); });
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, H - 64, W, 64, t.p) + k.r(0, H - 64, W * 0.2, 64, t.a)),
    zone: k.z(72, H * 0.5, W - 170, 'left', false, { meta: k.z(72, H - 210, W * 0.6, 'left', false) }) };
};
/* 20. Swiss grid: heavy rule, number block and fine rules */
CORP.swiss = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var i = 0; i < 6; i++) s += k.r(72, H * 0.7 + i * 22, W - 144, 1, t.line);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(72, 72, W - 144, 14, t.p) + k.r(72, 104, 140, 140, t.a) + k.r(232, 104, 4, 140, t.p) + s + k.r(W - 72 - 160, H - 92, 160, 20, t.p)),
    zone: k.z(72, 300, W - 160, 'left', false, { meta: k.z(72, H * 0.7 - 110, W * 0.6, 'left', false) }) };
};
/* 21. Corner brackets framing the title */
CORP.brackets = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), a = 120, w = 10, x1 = 60, y1 = H * 0.24, x2 = W - 60, y2 = H * 0.62;
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + k.r(x1, y1, a, w, t.a) + k.r(x1, y1, w, a, t.a) + k.r(x2 - a, y2 - w, a, w, t.a) + k.r(x2 - w, y2 - a, w, a, t.a) +
      k.r(x2 - a, y1, a, 2, '#FFFFFF', 0.35) + k.r(x1, y2 - 2, a, 2, '#FFFFFF', 0.35) + k.c(W - 90, H - 90, 140, t.s, 0.55) + k.c(W - 60, H - 60, 70, t.s, 0.9)),
    zone: Z(x1 + 60, y1 + 70, x2 - x1 - 120, 'left', true, { meta: Z(x1 + 60, H - 160, W * 0.5, 'left', true) }) };
};
/* 22. Sunburst rays from a corner */
CORP.rays = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', cx = W, cy = 0, R = H * 1.1;
  for (var i = 0; i < 18; i++) { var a0 = 90 + i * 5, a1 = a0 + 2.6, rad = Math.PI / 180; s += k.p([[cx, cy], [cx + R * Math.cos(a0 * rad), cy + R * Math.sin(a0 * rad)], [cx + R * Math.cos(a1 * rad), cy + R * Math.sin(a1 * rad)]], i === 15 ? t.a : t.p, i === 15 ? 0.9 : 0.05 + (i % 3) * 0.03); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.c(W, 0, 170, t.p) + k.c(W, 0, 104, t.a) + k.r(72, H - 120, 64, 5, t.a)),
    zone: k.z(72, H * 0.46, W - 190, 'left', false, { meta: k.z(72, H - 100, W * 0.6, 'left', false) }) };
};
/* 23. Diamond lattice texture with a solid lower band */
CORP.lattice = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', g = 54;
  for (var j = 0; j < 12; j++) for (var i = 0; i < 16; i++) { var cx = i * g + (j % 2 ? g / 2 : 0), cy = j * g / 2; if (cy > H * 0.5) continue; s += k.p([[cx, cy - g / 2 + 6], [cx + g / 2 - 6, cy], [cx, cy + g / 2 - 6], [cx - g / 2 + 6, cy]], (i + j * 3) % 11 === 0 && k.X(cx) > W * 0.58 ? t.a : '#FFFFFF', (i + j * 3) % 11 === 0 && k.X(cx) > W * 0.58 ? 0.9 : 0.07); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W, H * 0.5, t.p) + s + rect(0, H * 0.5, W, 10, t.a)),
    zone: Z(72, H * 0.16, W - 160, 'left', true, { meta: Z(72, H * 0.5 + 80, W * 0.6, 'left', false) }) };
};
/* 24. Overlapping translucent rectangles */
CORP.overlap = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(W * 0.34, 0, W * 0.66, H * 0.5, t.p, 0.95) + k.r(W * 0.5, H * 0.22, W * 0.5, H * 0.36, t.s, 0.75) + k.r(W * 0.22, H * 0.36, W * 0.42, H * 0.2, t.a, 0.85) +
      k.r(W * 0.22, H * 0.36, W * 0.12, H * 0.2, t.p, 0.6)),
    zone: k.z(72, H * 0.64, W - 170, 'left', false, { meta: k.z(72, H - 140, W * 0.6, 'left', false) }) };
};
/* 25. Flowing line waves (strokes only) */
CORP.linewaves = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var i = 0; i < 12; i++) { var y = H * 0.72 + i * 12; s += k.s(['M', -10, y, 'C', W * 0.3, y - 90 + i * 4, W * 0.6, y + 80 - i * 3, W + 10, y - 30], i === 4 ? t.a : t.p, i === 4 ? 3 : 1.4, i === 4 ? 1 : 0.25 + i * 0.05); }
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + s + k.r(72, 96, 10, 10, t.a) + k.r(90, 96, 10, 10, t.p) + k.r(108, 96, 10, 10, t.s)),
    zone: k.z(72, H * 0.22, W - 170, 'left', false, { meta: k.z(72, H * 0.56, W * 0.6, 'left', false) }) };
};
/* 26. Planet and orbit */
CORP.planet = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), defs = k.g('pl26', t.s, t.p, 0, 0, 1, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + '<circle cx="' + k.X(W * 0.7) + '" cy="' + H * 0.3 + '" r="' + W * 0.3 + '" fill="url(#pl26)"/>' +
      (function () { var c = [], a0 = -14 * Math.PI / 180, cx = W * 0.7, cy = H * 0.3; for (var i = 0; i <= 72; i++) { var a = i / 72 * Math.PI * 2, x = Math.cos(a) * W * 0.46, y = Math.sin(a) * W * 0.1; c.push(i ? 'L' : 'M', cx + x * Math.cos(a0) - y * Math.sin(a0), cy + x * Math.sin(a0) + y * Math.cos(a0)); } return k.s(c, t.a, 3); })() +
      k.c(W * 0.27, H * 0.21, 12, t.a) + k.dots(72, 80, 8, 2, 22, 1.8, '#FFFFFF', 0.3), defs),
    zone: k.z(72, H * 0.6, W - 170, 'left', true, { meta: k.z(72, H - 140, W * 0.6, 'left', true) }) };
};
/* 27. Two-tone diagonal split */
CORP.split2 = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + k.p([[0, 0], [W, 0], [W, H * 0.34], [0, H * 0.7]], t.p) + k.p([[0, H * 0.7], [W, H * 0.34], [W, H * 0.36], [0, H * 0.72]], t.a) +
      k.dots(W - 190, H * 0.6, 6, 6, 22, 2.4, t.p, 0.3)),
    zone: k.z(72, H * 0.14, W - 180, 'left', true, { meta: k.z(72, H - 150, W * 0.6, 'left', false) }) };
};
/* 28. Tall capsule photo at the side */
CORP.pillphoto = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), x = W * 0.56, y = 72, w = W * 0.36, h = H * 0.62, defs = k.g('pp28', t.s, t.p, 0, 0, 1, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + (img ? '' : k.r(x, y, w, h, 'url(#pp28)', 1, w / 2)) + k.ph(img, x, y, w, h, 'pc28', w / 2) + k.r(x - 40, y + h - 140, 80, 80, t.a, 1, 40) +
      k.r(x + w - 30, y - 20, 50, 50, t.p, 1, 25) + k.r(72, H - 100, W - 144, 2, t.line), defs),
    zone: k.z(72, y + 40, W * 0.44, 'left', false, { meta: k.z(72, H - 180, W * 0.6, 'left', false), photo: true, ts: 0.86 }) };
};
/* 29. Soft glow: large translucent circles on a gradient */
CORP.glow = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), defs = k.g('gl29', t.p, D.mix(t.p, '#000000', 0.35), 0, 0, 1, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, 'url(#gl29)') + k.c(W * 0.8, H * 0.2, W * 0.42, t.s, 0.35) + k.c(W * 0.95, H * 0.42, W * 0.26, t.a, 0.28) + k.c(W * 0.1, H * 0.98, W * 0.34, t.s, 0.25) + k.r(72, H * 0.5 - 40, 72, 5, t.a), defs),
    zone: k.z(72, H * 0.5, W - 180, 'left', true, { meta: k.z(72, H - 140, W * 0.6, 'left', true) }) };
};
/* 30. Horizontal colour band holding the title */
CORP.band = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), y = H * 0.36, h = H * 0.3;
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(0, y, W, h, t.p) + rect(0, y - 10, W, 4, t.a) + rect(0, y + h + 6, W, 4, t.a) + k.r(W - 200, y + h - 60, 128, 60, t.s) +
      k.r(72, 72, W - 144, 1, t.line) + k.r(72, H - 72, W - 144, 1, t.line)),
    zone: Z(72, y + 46, W - 300, 'left', true, { meta: Z(72, y + h + 70, W * 0.6, 'left', false) }) };
};
/* 31. Rising stair blocks */
CORP.stairs = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', n = 6, sw = (W - 72) / n;
  for (var i = 0; i < n; i++) s += k.r(72 + i * sw, H - 90 - (i + 1) * 54, sw, (i + 1) * 54 + 90, i === n - 1 ? t.a : D.mix(t.tint, t.p, 0.15 + i * 0.16));
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, 0, W, 10, t.p)),
    zone: k.z(72, 130, W - 170, 'left', false, { meta: k.z(72, H * 0.46, W * 0.6, 'left', false) }) };
};
/* 32. Plus-sign pattern panel */
CORP.plus = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', g = 40, pw = W * 0.42;
  for (var i = 0; i < 9; i++) for (var j = 0; j < 27; j++) { var x = 24 + i * g, y = 30 + j * g; if (x > pw - 20) continue; var c = (i * 3 + j) % 13 === 0 ? t.a : '#FFFFFF', o = c === t.a ? 1 : 0.22; s += k.r(x - 7, y - 1.5, 14, 3, c, o) + k.r(x - 1.5, y - 7, 3, 14, c, o); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(0, 0, pw, H, t.p) + s + k.r(pw, 0, 6, H, t.a)),
    zone: k.z(pw + 52, H * 0.3, W - pw - 100, 'left', false, { meta: k.z(pw + 52, H - 150, W - pw - 100, 'left', false), ts: 0.86 }) };
};
/* 33. Donut chart arcs */
CORP.donut = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), cx = W * 0.72, cy = H * 0.3, segs = [[-90, 40, t.p], [44, 150, t.s], [154, 220, t.a], [224, 266, t.tint]], s = '';
  segs.forEach(function (g) { s += k.d(arcPath(k, cx, cy, 170, g[0], g[1], 64), g[2]); });
  s += k.d(arcPath(k, cx, cy, 104, -90, 160, 14), t.p, 0.35) + k.c(cx, cy, 40, t.a, 0.9);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(72, H - 90, W - 144, 2, t.p)),
    zone: k.z(72, H * 0.58, W - 170, 'left', false, { meta: k.z(72, H - 170, W * 0.6, 'left', false) }) };
};
/* 34. Folded corner, like a turned page */
CORP.fold = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), f = 260;
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + k.p([[W - f, 0], [W, 0], [W, f]], t.tint) + k.p([[W - f, 0], [W, f], [W - f, f]], t.a) + k.p([[W - f, f], [W, f], [W - f + 40, f + 40]], '#000000', 0.18) +
      k.r(72, H - 140, W - 144, 1, '#FFFFFF', 0.3)),
    zone: k.z(72, H * 0.38, W - 190, 'left', true, { meta: k.z(72, H - 120, W * 0.6, 'left', true) }) };
};
/* 35. Colour border inset */
CORP.inset = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), m = 34;
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + rect(m, m, W - m * 2, H - m * 2, '#FFFFFF') + rect(m, H * 0.62, W - m * 2, 6, t.a) + k.r(m, m, 140, 140, t.tint) + k.r(m + 40, m + 40, 60, 60, t.a)),
    zone: k.z(84, H * 0.3, W - 168, 'left', false, { meta: k.z(84, H * 0.62 + 50, W * 0.6, 'left', false) }) };
};
/* 36. Elegant petals */
CORP.petals = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', cx = W * 0.82, cy = H * 0.84, c = [t.p, t.s, t.a, t.p, t.s, t.tint];
  for (var i = 0; i < 6; i++) { var a = (-180 + i * 30) * Math.PI / 180, ex = cx + Math.cos(a) * 320, ey = cy + Math.sin(a) * 320, nx = Math.cos(a + Math.PI / 2) * 70, ny = Math.sin(a + Math.PI / 2) * 70;
    s += k.d(['M', cx, cy, 'Q', (cx + ex) / 2 + nx, (cy + ey) / 2 + ny, ex, ey, 'Q', (cx + ex) / 2 - nx, (cy + ey) / 2 - ny, cx, cy, 'Z'], c[i], 0.85); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(72, 96, 64, 4, t.a)),
    zone: k.z(72, 136, W - 170, 'left', false, { meta: k.z(72, H * 0.42, W * 0.5, 'left', false) }) };
};
/* 37. Bauhaus composition: half circle, square and line */
CORP.compose = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + k.d(['M', W * 0.2, H * 0.5, 'A', W * 0.3, W * 0.3, 0, 0, 1, W * 0.8, H * 0.5, 'Z'], t.p) + k.r(W * 0.5, H * 0.5, W * 0.3, W * 0.3, t.a) +
      k.r(W * 0.2, H * 0.5, W * 0.3, W * 0.3, t.s) + k.c(W * 0.35, H * 0.5 + W * 0.15, W * 0.09, '#FFFFFF') + k.r(W * 0.12, H * 0.5 - 2, W * 0.76, 4, t.ink)),
    zone: Z(72, 96, W - 160, 'left', false, { meta: Z(72, H - 140, W * 0.6, 'left', false) }) };
};
/* 38. City skyline silhouette */
CORP.skyline = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', x = 0, hs = [140, 210, 120, 280, 180, 330, 150, 240, 300, 170, 220, 130], ws = [70, 54, 90, 60, 76, 64, 92, 58, 70, 84, 62, 90];
  for (var i = 0; i < hs.length && x < W; i++) { s += k.r(x, H - 120 - hs[i], ws[i] - 4, hs[i] + 120, i === 5 ? t.a : D.mix(t.p, t.s, (i % 3) * 0.35)); for (var w = 0; w < 4; w++) s += k.r(x + 10, H - 100 - hs[i] + 18 + w * 30, ws[i] - 24, 6, '#FFFFFF', 0.12); x += ws[i]; }
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + s + rect(0, H - 40, W, 40, t.p)),
    zone: k.z(72, 120, W - 170, 'left', false, { meta: k.z(72, H * 0.42, W * 0.6, 'left', false) }) };
};
/* 39. Giant half circle from the top */
CORP.dome = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), r = W * 0.62;
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + circ(W / 2, 0, r, t.p) + ring(W / 2, 0, r + 26, t.a, 6) + ring(W / 2, 0, r + 46, t.line, 1.5) + k.c(W * 0.16, H * 0.86, 10, t.a) + k.r(W * 0.16 + 24, H * 0.86 - 1, 110, 2, t.line)),
    zone: Z(110, 120, W - 220, 'center', true, { meta: Z(110, H * 0.72, W - 220, 'center', false) }) };
};
/* 40. World of dots: a dotted cluster */
CORP.dotmap = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', g = 16;
  var inside = function (x, y) { var a = Math.pow((x - W * 0.62) / (W * 0.36), 2) + Math.pow((y - H * 0.32) / (H * 0.2), 2), b = Math.pow((x - W * 0.86) / (W * 0.14), 2) + Math.pow((y - H * 0.48) / (H * 0.1), 2), c = Math.pow((x - W * 0.4) / (W * 0.12), 2) + Math.pow((y - H * 0.2) / (H * 0.08), 2); return a < 1 || b < 1 || c < 1; };
  for (var x = 8; x < W; x += g) for (var y = 8; y < H * 0.62; y += g) if (inside(x, y)) s += k.c(x, y, 3.6, ((x * 7 + y * 3) % 97) < 6 ? t.a : '#FFFFFF', ((x * 7 + y * 3) % 97) < 6 ? 1 : 0.55);
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + s + k.r(72, H * 0.7 - 30, 56, 5, t.a)),
    zone: k.z(72, H * 0.7, W - 170, 'left', true, { meta: k.z(72, H - 120, W * 0.6, 'left', true) }) };
};
/* 41. Side tab: a colour tab with the title beside it */
CORP.sidetab = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(0, 0, 120, H, t.p) + k.r(120, H * 0.26, 18, H * 0.24, t.a) + k.r(0, H - 120, 120, 120, t.s) + k.dots(30, 60, 4, 10, 18, 2, '#FFFFFF', 0.28) +
      k.r(W - 72 - 160, H - 72, 160, 1.5, t.line)),
    zone: k.z(180, H * 0.26, W - 250, 'left', false, { meta: k.z(180, H - 170, W - 250, 'left', false) }) };
};
/* 42. Prism shards */
CORP.shards = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + k.p([[W * 0.45, 0], [W, 0], [W, H * 0.32]], t.s, 0.8) + k.p([[W * 0.7, 0], [W, H * 0.58], [W * 0.82, H * 0.6]], t.a, 0.9) +
      k.p([[W * 0.55, H * 0.14], [W * 0.95, H * 0.62], [W * 0.62, H * 0.5]], '#FFFFFF', 0.08) + k.p([[0, H], [W * 0.5, H], [0, H * 0.82]], t.s, 0.55) + k.r(72, H * 0.44 - 36, 60, 5, t.a)),
    zone: k.z(72, H * 0.44, W * 0.66, 'left', true, { meta: k.z(72, H - 130, W * 0.6, 'left', true) }) };
};
/* 43. Grid of tiles with one photo tile */
CORP.tiles = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), g = 12, cw = (W - 72 * 2 - g) / 2, ch = 190, y0 = 72, defs = k.g('tl43', t.s, t.p, 0, 0, 1, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + (img ? '' : k.r(72, y0, cw, ch * 2 + g, 'url(#tl43)')) + k.ph(img, 72, y0, cw, ch * 2 + g, 'tc43') + k.r(72 + cw + g, y0, cw, ch, t.a) +
      k.r(72 + cw + g, y0 + ch + g, cw, ch, t.tint) + k.c(72 + cw + g + cw / 2, y0 + ch + g + ch / 2, 50, t.p, 0.9), defs),
    zone: k.z(72, y0 + ch * 2 + g + 70, W - 170, 'left', false, { meta: k.z(72, H - 150, W * 0.6, 'left', false), photo: true }) };
};
/* 44. Spine: book-style coloured spine with a thin frame */
CORP.spine = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f);
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + k.r(0, 0, 60, H, t.p) + k.r(60, 0, 6, H, t.a) + '<rect x="' + (v.f ? 40 : 110) + '" y="50" width="' + (W - 150) + '" height="' + (H - 100) + '" fill="none" stroke="' + t.p + '" stroke-width="1.2"/>' +
      k.r(150, H * 0.62, 80, 3, t.a)),
    zone: k.z(150, H * 0.3, W - 260, 'left', false, { meta: k.z(150, H * 0.62 + 40, W - 260, 'left', false) }) };
};

/* inner pages for the collection */
D.ART.page.hairline = function (t, W, H) { return svg(W, H, rect(72, 44, W - 144, 1, t.line) + rect(72, 44, 60, 3, t.a) + rect(72, H - 52, W - 144, 1, t.line)); };
D.ART.page.tabright = function (t, W, H) { return svg(W, H, rect(W - 12, 0, 12, H, t.p) + rect(W - 12, 110, 12, 70, t.a)); };
D.ART.page.footband = function (t, W, H) { return svg(W, H, rect(0, H - 26, W, 26, t.p) + rect(0, H - 26, 120, 26, t.a)); };
D.ART.page.cornerblock = function (t, W, H) { return svg(W, H, rect(0, 0, 48, 48, t.p) + rect(48, 0, 16, 16, t.a) + rect(W - 72 - 90, H - 54, 90, 3, t.a)); };
D.ART.page.arcs = function (t, W, H) { return svg(W, H, ring(W, 0, 90, t.p, 14, 0.9) + ring(W, 0, 122, t.a, 4) + ring(W, 0, 140, t.line, 1)); };
D.ART.page.gridtop = function (t, W, H) { var s = ''; for (var x = 0; x <= W; x += 22) s += line(x, 0, x, 34, t.line, 0.8); return svg(W, H, s + rect(0, 34, W, 3, t.p) + rect(0, 34, W * 0.18, 3, t.a)); };
D.ART.page.doubleline = function (t, W, H) { return svg(W, H, rect(30, 0, 2, H, t.p) + rect(36, 0, 1, H, t.a)); };
D.ART.page.chevfoot = function (t, W, H) { var s = ''; for (var i = 0; i < 4; i++) s += poly([[W - 72 - 70 + i * 16, H - 50], [W - 72 - 62 + i * 16, H - 44], [W - 72 - 70 + i * 16, H - 38]], i === 3 ? t.a : t.p, 0.85); return svg(W, H, s + rect(72, H - 45, W - 144 - 90, 1, t.line)); };
D.ART.page.topbar2 = function (t, W, H) { return svg(W, H, rect(0, 0, W, 8, t.p) + rect(W * 0.7, 0, W * 0.3, 8, t.a)); };
D.ART.page.dotsside = function (t, W, H) { var s = ''; for (var j = 0; j < 40; j++) s += circ(26, 40 + j * 26, 2.2, t.p, 0.35); return svg(W, H, s + rect(20, H * 0.2, 12, 60, t.a, 1, 6)); };
D.ART.page.halfring = function (t, W, H) { return svg(W, H, ring(0, H, 120, t.p, 10, 0.9) + ring(0, H, 150, t.a, 3)); };
D.ART.page.sidenote = function (t, W, H) { return svg(W, H, rect(0, 0, W, 4, t.a) + rect(0, H - 60, 8, 60, t.p)); };

/* ---------------- learning & study cover collection ---------------- */
function starPts(cx, cy, r, n) { var a = [], k; for (k = 0; k < n * 2; k++) { var rr = k % 2 ? r * 0.45 : r, g = Math.PI / n * k - Math.PI / 2; a.push([cx + Math.cos(g) * rr, cy + Math.sin(g) * rr]); } return a; }
/* 45. Ruled notebook with spiral holes */
CORP.ruled = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var y = 150; y < H - 40; y += 28) s += k.l(110, y, W, y, t.s, 0.8, 0.22);
  for (var j = 0; j < 14; j++) s += k.c(48, 90 + j * 70, 11, t.tint) + k.o(48, 90 + j * 70, 11, t.line, 1.4);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(0, 0, 96, H, t.p, 0.06) + s + k.l(140, 0, 140, H, t.a, 2, 0.8) + k.r(W - 200, 56, 128, 36, t.a, 0.9, 4)),
    zone: k.z(176, H * 0.22, W - 260, 'left', false, { meta: k.z(176, H - 170, W - 260, 'left', false) }) };
};
/* 46. Stack of book spines */
CORP.bookstack = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', y = H - 60, books = [[0.78, 54, t.p], [0.66, 40, t.a], [0.84, 62, t.s], [0.6, 46, D.mix(t.p, '#FFFFFF', 0.35)], [0.72, 52, t.p], [0.5, 38, t.a]];
  books.forEach(function (b, i) { var w = (W - 144) * b[0], x = 72 + (i % 2 ? 24 : 0); y -= b[1]; s += k.r(x, y, w, b[1] - 4, b[2], 1, 4) + k.r(x + 22, y + 8, 6, b[1] - 20, '#FFFFFF', 0.35) + k.r(x + w - 90, y + (b[1] - 4) / 2 - 2, 60, 4, '#FFFFFF', 0.4); });
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + s + k.r(72, H - 62, W - 144, 2, t.ink, 0.6) + k.r(72, 92, 60, 5, t.a)),
    zone: k.z(72, 124, W - 170, 'left', false, { meta: k.z(72, H * 0.4, W * 0.6, 'left', false) }) };
};
/* 47. Graph paper with a plotted curve */
CORP.plot = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', g = 24, x0 = 72, y0 = H * 0.5, w = W - 144, h = H * 0.38;
  for (var x = x0; x <= x0 + w + 1; x += g) s += k.l(x, y0, x, y0 + h, t.line, 0.8);
  for (var y = y0; y <= y0 + h + 1; y += g) s += k.l(x0, y, x0 + w, y, t.line, 0.8);
  var c = []; for (var i = 0; i <= 40; i++) { var px = x0 + w * i / 40, py = y0 + h * (0.85 - 0.6 * (i / 40) - 0.12 * Math.sin(i / 40 * Math.PI * 3)); c.push(i ? 'L' : 'M', px, py); }
  s += k.s(c, t.a, 4) + k.l(x0, y0 + h, x0 + w, y0 + h, t.p, 2.5) + k.l(x0, y0, x0, y0 + h, t.p, 2.5);
  [8, 18, 28, 36].forEach(function (i) { var px = x0 + w * i / 40, py = y0 + h * (0.85 - 0.6 * (i / 40) - 0.12 * Math.sin(i / 40 * Math.PI * 3)); s += k.c(px, py, 7, t.p) + k.c(px, py, 3, '#FFFFFF'); });
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, 0, W, 12, t.p)),
    zone: k.z(72, 110, W - 170, 'left', false, { meta: k.z(72, y0 + h + 30, W * 0.6, 'left', false) }) };
};
/* 48. Atom: orbits around a nucleus */
CORP.atom = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', cx = W * 0.62, cy = H * 0.34;
  [0, 60, 120].forEach(function (deg, n) {
    var a0 = deg * Math.PI / 180, c = [];
    for (var i = 0; i <= 72; i++) { var a = i / 72 * Math.PI * 2, x = Math.cos(a) * 250, y = Math.sin(a) * 82; c.push(i ? 'L' : 'M', cx + x * Math.cos(a0) - y * Math.sin(a0), cy + x * Math.sin(a0) + y * Math.cos(a0)); }
    s += k.s(c, n === 1 ? t.a : '#FFFFFF', n === 1 ? 4 : 2.5, n === 1 ? 1 : 0.7);
    var ea = 1.1 + n * 1.7; s += k.c(cx + Math.cos(ea) * 250 * Math.cos(a0) - Math.sin(ea) * 82 * Math.sin(a0), cy + Math.cos(ea) * 250 * Math.sin(a0) + Math.sin(ea) * 82 * Math.cos(a0), 12, t.a);
  });
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + k.c(cx, cy, 300, t.s, 0.35) + s + k.c(cx, cy, 34, t.a) + k.c(cx, cy, 16, '#FFFFFF', 0.85)),
    zone: k.z(72, H * 0.64, W - 170, 'left', true, { meta: k.z(72, H - 130, W * 0.6, 'left', true) }) };
};
/* 49. Pencils in a row */
CORP.pencils = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', c = [t.p, t.a, t.s, D.mix(t.a, '#FFFFFF', 0.3), t.p, t.s];
  for (var i = 0; i < 6; i++) { var x = W * 0.46 + i * 58, top = 60 + (i % 3) * 50, len = H * 0.5 - (i % 2) * 60; s += k.r(x, top, 40, len, c[i]) + k.r(x, top, 40, 26, '#F4C2C2') + k.r(x, top + 26, 40, 8, '#C9CED6') + k.p([[x, top + len], [x + 40, top + len], [x + 20, top + len + 50]], '#F3D9B1') + k.p([[x + 13, top + len + 33], [x + 27, top + len + 33], [x + 20, top + len + 50]], t.ink) + k.r(x + 13, top + 34, 3, len - 34, '#FFFFFF', 0.25); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(72, H - 92, W - 144, 2, t.line)),
    zone: k.z(72, H * 0.66, W - 170, 'left', false, { meta: k.z(72, H - 160, W * 0.6, 'left', false) }) };
};
/* 50. Speech bubbles */
CORP.bubbles = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), b = function (x, y, w, h, fill, tl) { return k.r(x, y, w, h, fill, 1, 26) + k.p(tl ? [[x + 40, y + h - 2], [x + 80, y + h - 2], [x + 30, y + h + 40]] : [[x + w - 80, y + h - 2], [x + w - 40, y + h - 2], [x + w - 30, y + h + 40]], fill); };
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + b(W * 0.42, 70, W * 0.5, 150, t.p, true) + b(W * 0.26, 270, W * 0.46, 120, t.a, false) + b(W * 0.5, 440, W * 0.42, 110, t.s, true) +
      k.r(W * 0.46, 120, W * 0.3, 10, '#FFFFFF', 0.6, 5) + k.r(W * 0.46, 150, W * 0.2, 10, '#FFFFFF', 0.4, 5) + k.c(W * 0.33, 330, 8, '#FFFFFF') + k.c(W * 0.37, 330, 8, '#FFFFFF') + k.c(W * 0.41, 330, 8, '#FFFFFF')),
    zone: k.z(72, H * 0.6, W - 170, 'left', false, { meta: k.z(72, H - 150, W * 0.6, 'left', false) }) };
};
/* 51. Jigsaw pieces */
CORP.puzzle = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', cs = 120, c = [t.p, t.s, t.a, t.tint, t.s, t.p];
  for (var i = 0; i < 3; i++) for (var j = 0; j < 2; j++) { var x = W - 72 - (3 - i) * cs, y = 80 + j * cs, col = c[i + j * 3]; s += k.r(x + 4, y + 4, cs - 8, cs - 8, col, 1, 8); if (i < 2) s += k.c(x + cs, y + cs / 2, 18, col); if (j < 1) s += k.c(x + cs / 2, y + cs, 18, col); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.r(0, H - 30, W, 30, t.p) + k.r(72, H * 0.46 - 32, 56, 5, t.a)),
    zone: k.z(72, H * 0.46, W - 170, 'left', false, { meta: k.z(72, H - 160, W * 0.6, 'left', false) }) };
};
/* 52. Light bulb idea */
CORP.bulb = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), cx = W * 0.7, cy = H * 0.28, s = '';
  for (var i = 0; i < 12; i++) { var a = i / 12 * Math.PI * 2; s += k.l(cx + Math.cos(a) * 150, cy + Math.sin(a) * 150, cx + Math.cos(a) * 190, cy + Math.sin(a) * 190, t.a, 6); }
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + s + k.c(cx, cy, 110, t.a) + k.c(cx - 36, cy - 36, 28, '#FFFFFF', 0.35) + k.r(cx - 46, cy + 100, 92, 30, '#FFFFFF', 0.85, 6) + k.r(cx - 40, cy + 136, 80, 18, '#FFFFFF', 0.6, 6) + k.r(cx - 24, cy + 160, 48, 14, '#FFFFFF', 0.4, 6)),
    zone: k.z(72, H * 0.6, W - 170, 'left', true, { meta: k.z(72, H - 130, W * 0.6, 'left', true) }) };
};
/* 53. Ruler band with ticks */
CORP.ruler = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', y = H * 0.52;
  for (var x = 10; x < W; x += 12) { var n = Math.round((x - 10) / 12), L = n % 10 === 0 ? 38 : n % 5 === 0 ? 26 : 14; s += k.r(x, y, 2, L, t.p, 0.85); }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(0, y - 8, W, 96, t.a, 0.9) + s + k.r(0, y + 88, W, 6, t.p) + k.r(0, 0, W, H * 0.06, t.tint)),
    zone: k.z(72, 120, W - 170, 'left', false, { meta: k.z(72, y + 150, W * 0.6, 'left', false) }) };
};
/* 54. Scattered stars */
CORP.stars = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', P = [[0.62, 0.12, 46, t.a], [0.86, 0.2, 26, '#FFFFFF'], [0.72, 0.34, 18, '#FFFFFF'], [0.5, 0.24, 14, t.a], [0.9, 0.42, 34, t.a], [0.58, 0.46, 12, '#FFFFFF'], [0.8, 0.08, 10, '#FFFFFF'], [0.42, 0.1, 22, '#FFFFFF']];
  P.forEach(function (p) { s += k.p(starPts(W * p[0], H * p[1], p[2], 5), p[3], p[3] === '#FFFFFF' ? 0.6 : 1); });
  return { svg: svg(W, H, rect(0, 0, W, H, t.p) + k.c(W * 0.3, H * 1.02, W * 0.7, t.s, 0.6) + s),
    zone: k.z(72, H * 0.58, W - 170, 'left', true, { meta: k.z(72, H - 130, W * 0.6, 'left', true) }) };
};
/* 55. Folder index tabs */
CORP.tabs = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', c = [t.p, t.s, t.a, D.mix(t.p, '#FFFFFF', 0.4), D.mix(t.a, '#FFFFFF', 0.3)];
  for (var i = 0; i < 5; i++) s += k.r(W - 110, 110 + i * 110, 110, 96, c[i], 1, 10) + k.r(W - 96, 150 + i * 110, 50, 6, '#FFFFFF', 0.6, 3);
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + s + k.r(0, 0, W - 96, H, '#FFFFFF') + k.r(W - 96, 0, 4, H, t.line) + k.r(72, 96, 60, 5, t.a)),
    zone: k.z(72, 136, W - 260, 'left', false, { meta: k.z(72, H - 170, W - 260, 'left', false) }) };
};
/* 56. Laurel wreath */
CORP.laurel = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', cx = W / 2, cy = H * 0.24, R = 120;
  for (var side = -1; side <= 1; side += 2) for (var i = 0; i < 9; i++) {
    var a = (110 + i * 17) * Math.PI / 180, x = cx + side * Math.cos(a) * -R, y = cy + Math.sin(a) * R, rot = a + side * 0.9, lx = Math.cos(rot) * 22, ly = Math.sin(rot) * 22;
    s += k.d(['M', x - lx, y - ly, 'Q', x + ly * 0.5, y - lx * 0.5, x + lx, y + ly, 'Q', x - ly * 0.5, y + lx * 0.5, x - lx, y - ly, 'Z'], t.a, 0.9);
  }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + k.c(cx, cy, 44, t.p) + k.l(96, H * 0.42, W - 96, H * 0.42, t.p, 1.5) + k.l(96, H * 0.78, W - 96, H * 0.78, t.p, 1.5) + k.l(96, H * 0.78 + 5, W - 96, H * 0.78 + 5, t.p, 0.6)),
    zone: Z(110, H * 0.46, W - 220, 'center', false, { meta: Z(110, H * 0.81, W - 220, 'center', false) }) };
};
/* 57. Classical columns */
CORP.pillars = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', y0 = H * 0.5, h = H * 0.34;
  s += k.p([[W * 0.12, y0], [W * 0.5, y0 - 110], [W * 0.88, y0]], t.p) + k.r(W * 0.12, y0, W * 0.76, 16, t.s);
  for (var i = 0; i < 5; i++) { var x = W * 0.16 + i * W * 0.16; s += k.r(x, y0 + 30, 48, h - 40, t.tint) + k.r(x - 8, y0 + 18, 64, 14, t.s) + k.r(x - 8, y0 + h - 12, 64, 14, t.s); for (var f = 1; f < 4; f++) s += k.r(x + f * 12, y0 + 36, 2, h - 52, t.line); }
  s += k.r(W * 0.08, y0 + h, W * 0.84, 18, t.p);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s),
    zone: Z(96, 110, W - 192, 'center', false, { meta: Z(96, y0 + h + 50, W - 192, 'center', false) }) };
};
/* 58. Chalkboard */
CORP.chalk = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), board = D.mix(t.p, '#0F1A17', 0.4), s = '';
  s += k.s(['M', W * 0.55, 140, 'C', W * 0.65, 100, W * 0.75, 180, W * 0.88, 120], '#FFFFFF', 3, 0.7) + k.s(['M', W * 0.58, 220, 'L', W * 0.86, 220], '#FFFFFF', 2.5, 0.5) + k.s(['M', W * 0.58, 260, 'L', W * 0.78, 260], '#FFFFFF', 2.5, 0.5);
  s += k.o(W * 0.68, 380, 60, '#FFFFFF', 2.5, 0.55) + k.p([[W * 0.8, 440], [W * 0.88, 320], [W * 0.94, 440]], '#FFFFFF', 0.12) + k.s(['M', W * 0.8, 440, 'L', W * 0.88, 320, 'L', W * 0.94, 440, 'Z'], '#FFFFFF', 2.5, 0.55);
  return { svg: svg(W, H, rect(0, 0, W, H, D.mix(t.s, '#5B3A1E', 0.5)) + rect(28, 28, W - 56, H - 56, board) + s + rect(28, H - 90, W - 56, 14, D.mix(t.s, '#5B3A1E', 0.6)) + k.r(W - 220, H - 104, 60, 12, '#FFFFFF', 0.85, 3) + k.r(W - 140, H - 104, 40, 12, t.a, 1, 3)),
    zone: k.z(80, H * 0.44, W - 200, 'left', true, { meta: k.z(80, H - 200, W * 0.6, 'left', true) }) };
};
/* 59. Toy blocks */
CORP.blocks = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '', B = [[0.56, 0.2, 130, t.p, -8], [0.76, 0.16, 110, t.a, 10], [0.66, 0.34, 120, t.s, 4], [0.86, 0.32, 90, D.mix(t.a, '#FFFFFF', 0.25), -12]];
  B.forEach(function (b) { var cx = W * b[0], cy = H * b[1], r = b[2] / 2, a = b[4] * Math.PI / 180, pts = [[-r, -r], [r, -r], [r, r], [-r, r]].map(function (p) { return [cx + p[0] * Math.cos(a) - p[1] * Math.sin(a), cy + p[0] * Math.sin(a) + p[1] * Math.cos(a)]; }); s += k.p(pts, b[3]); s += k.c(cx, cy, r * 0.42, '#FFFFFF', 0.85); });
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(0, H * 0.46, W, 10, t.tint) + s + k.r(0, H - 40, W, 40, t.tint)),
    zone: k.z(72, H * 0.54, W - 170, 'left', false, { meta: k.z(72, H - 170, W * 0.6, 'left', false) }) };
};
/* 60. Editorial: magazine rules and columns */
CORP.editorial = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var c = 0; c < 3; c++) for (var i = 0; i < 12; i++) s += k.r(72 + c * ((W - 144) / 3) + 6, H * 0.64 + i * 16, (W - 144) / 3 - 24 - (i % 4 === 3 ? 60 : 0), 4, t.line);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.r(72, 64, W - 144, 8, t.p) + k.r(72, 80, W - 144, 1.5, t.p) + k.r(72, H * 0.6, W - 144, 1.5, t.p) + s + k.r(72, H * 0.6 - 60, 40, 40, t.a)),
    zone: k.z(72, 150, W - 160, 'left', false, { meta: k.z(130, H * 0.6 - 56, W * 0.6, 'left', false), ts: 1.12 }) };
};
/* 61. Giant quote marks */
CORP.quotes = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), q = function (x, y, r, c) { return k.c(x, y, r, c) + k.d(['M', x + r * 0.95, y - r * 0.1, 'C', x + r * 1.1, y + r * 1.4, x - r * 0.2, y + r * 2.2, x - r * 0.9, y + r * 2.3, 'C', x - r * 0.1, y + r * 1.6, x + r * 0.2, y + r * 1.1, x, y + r, 'Z'], c); };
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + q(W * 0.62, H * 0.12, 70, t.p) + q(W * 0.82, H * 0.12, 70, t.a) + k.r(72, H * 0.86, W - 144, 1.5, t.line)),
    zone: k.z(72, H * 0.42, W - 170, 'left', false, { meta: k.z(72, H * 0.88, W * 0.6, 'left', false), ts: 0.92 }) };
};
/* 62. Manuscript margin */
CORP.margin = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), s = '';
  for (var y = H * 0.62; y < H - 80; y += 24) s += k.l(150, y, W - 72, y, t.line, 1);
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFDF8') + k.l(118, 0, 118, H, t.a, 1.6) + k.l(124, 0, 124, H, t.a, 0.8) + s + k.c(W - 120, 120, 34, t.p, 0.9) + k.c(W - 92, 150, 12, t.p, 0.6) + k.c(W - 150, 162, 7, t.p, 0.5)),
    zone: k.z(150, H * 0.26, W - 240, 'left', false, { meta: k.z(150, H * 0.5, W - 240, 'left', false) }) };
};
/* 63. Open book */
CORP.openbook = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), cx = W / 2, y = H * 0.18, w = W * 0.36, s = '';
  s += k.d(['M', cx, y + 30, 'C', cx - w * 0.5, y - 10, cx - w * 0.8, y, cx - w, y + 20, 'L', cx - w, y + 250, 'C', cx - w * 0.8, y + 230, cx - w * 0.5, y + 220, cx, y + 260, 'Z'], t.p);
  s += k.d(['M', cx, y + 30, 'C', cx + w * 0.5, y - 10, cx + w * 0.8, y, cx + w, y + 20, 'L', cx + w, y + 250, 'C', cx + w * 0.8, y + 230, cx + w * 0.5, y + 220, cx, y + 260, 'Z'], t.s);
  for (var i = 1; i < 6; i++) { s += k.s(['M', cx - w * 0.86, y + 40 + i * 32, 'C', cx - w * 0.5, y + 24 + i * 32, cx - w * 0.3, y + 30 + i * 32, cx - 16, y + 48 + i * 32], '#FFFFFF', 2, 0.35) + k.s(['M', cx + 16, y + 48 + i * 32, 'C', cx + w * 0.3, y + 30 + i * 32, cx + w * 0.5, y + 24 + i * 32, cx + w * 0.86, y + 40 + i * 32], '#FFFFFF', 2, 0.35); }
  return { svg: svg(W, H, rect(0, 0, W, H, t.tint) + s + k.r(cx - 3, y + 30, 6, 232, t.a)),
    zone: Z(96, y + 340, W - 192, 'center', false, { meta: Z(96, H - 150, W - 192, 'center', false) }) };
};
/* 64. Graduation cap */
CORP.gradcap = function (t, W, H, img, v) {
  var k = KIT(W, H, v.f), cx = W * 0.68, cy = H * 0.24;
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + k.c(cx, cy + 20, 210, t.tint) + k.p([[cx - 180, cy], [cx, cy - 80], [cx + 180, cy], [cx, cy + 80]], t.p) + k.d(['M', cx - 110, cy + 30, 'L', cx - 110, cy + 110, 'C', cx - 60, cy + 150, cx + 60, cy + 150, cx + 110, cy + 110, 'L', cx + 110, cy + 30, 'L', cx, cy + 80, 'Z'], t.s) +
      k.l(cx, cy, cx + 150, cy + 30, t.a, 4) + k.l(cx + 150, cy + 30, cx + 150, cy + 130, t.a, 4) + k.c(cx + 150, cy + 140, 14, t.a) + k.c(cx, cy, 10, t.a) + k.r(0, H - 18, W, 18, t.p)),
    zone: k.z(72, H * 0.52, W - 170, 'left', false, { meta: k.z(72, H - 170, W * 0.6, 'left', false) }) };
};

/* ---------------- letterheads (page 1 of letters, memos, bios, job descriptions) ----------------
 * LH[name] = { f: art(t, W, H), top: where the name / title block starts, dark: white text, al: alignment, m: page margins }
 */
var LH = {};
function lh(name, o, f) { o.f = f; LH[name] = o; D.ART.head[name] = f; }
lh('lhAngle', { top: 42, dark: 1, m: { t: 214 } }, function (t, W, H) { return svg(W, H, poly([[0, 0], [W, 0], [W, 150], [0, 178]], t.p) + poly([[0, 178], [W, 150], [W, 158], [0, 186]], t.a) + circ(W - 70, 40, 110, '#FFFFFF', 0.06)); });
lh('lhCornerTri', { top: 60, m: { t: 180 } }, function (t, W, H) { return svg(W, H, poly([[W - 230, 0], [W, 0], [W, 200]], t.p) + poly([[W - 120, 0], [W, 0], [W, 104]], t.a) + poly([[0, H], [0, H - 110], [150, H]], t.s, 0.85) + rect(72, 150, 80, 3, t.a)); });
lh('lhSideStripe', { top: 56, m: { t: 172, l: 104 } }, function (t, W, H) { return svg(W, H, rect(0, 0, 26, H, t.p) + rect(0, 60, 26, 110, t.a) + rect(104, 150, W - 176, 1.2, t.line)); });
lh('lhDoubleRule', { top: 56, al: 'center', m: { t: 176 } }, function (t, W, H) { return svg(W, H, rect(0, 0, W, 8, t.p) + line(96, 146, W - 96, 146, t.p, 2) + line(96, 151, W - 96, 151, t.p, 0.7) + line(96, H - 64, W - 96, H - 64, t.line, 1)); });
lh('lhCircle', { top: 64, m: { t: 184 } }, function (t, W, H) { return svg(W, H, circ(W - 40, -10, 190, t.p) + circ(W - 210, 120, 30, t.a) + ring(W - 40, -10, 214, t.p, 1.5, 0.35) + rect(72, 156, 64, 4, t.a)); });
lh('lhWave', { top: 36, dark: 1, m: { t: 200 } }, function (t, W, H) { return svg(W, H, path('M0 0 L' + W + ' 0 L' + W + ' 112 C ' + W * 0.7 + ' 176, ' + W * 0.35 + ' 82, 0 146 Z', t.p) + '<path d="M0 156 C ' + W * 0.35 + ' 92, ' + W * 0.7 + ' 186, ' + W + ' 122" fill="none" stroke="' + t.a + '" stroke-width="4"/>'); });
lh('lhSplit', { top: 40, dark: 1, m: { t: 196 } }, function (t, W, H) { return svg(W, H, rect(0, 0, W * 0.64, 150, t.p) + rect(W * 0.64, 0, W * 0.36, 150, t.a) + rect(W * 0.64, 150, W * 0.36, 8, t.s) + dots(W * 0.7, 30, 6, 4, 18, 2.2, '#FFFFFF', 0.45)); });
lh('lhFrame', { top: 70, al: 'center', m: { t: 196, l: 100, r: 100, b: 96 } }, function (t, W, H) { return svg(W, H, '<rect x="30" y="30" width="' + (W - 60) + '" height="' + (H - 60) + '" fill="none" stroke="' + t.p + '" stroke-width="1.6"/>' + rect(22, 22, 18, 18, t.a) + rect(W - 40, 22, 18, 18, t.a) + rect(22, H - 40, 18, 18, t.a) + rect(W - 40, H - 40, 18, 18, t.a) + line(W / 2 - 60, 168, W / 2 + 60, 168, t.a, 2)); });
lh('lhMonogram', { top: 132, al: 'center', m: { t: 250 } }, function (t, W, H) { return svg(W, H, circ(W / 2, 74, 40, t.p) + ring(W / 2, 74, 50, t.a, 2) + line(96, 74, W / 2 - 66, 74, t.line, 1) + line(W / 2 + 66, 74, W - 96, 74, t.line, 1) + rect(0, H - 10, W, 10, t.p)); });
lh('lhChevrons', { top: 60, m: { t: 180 } }, function (t, W, H) { var s = ''; for (var i = 0; i < 4; i++) { var x = W - 250 + i * 46; s += poly([[x, 40], [x + 30, 40], [x + 60, 80], [x + 30, 120], [x, 120], [x + 30, 80]], i === 3 ? t.a : t.p, 0.35 + i * 0.2); } return svg(W, H, s + rect(72, 152, W - 144, 1.2, t.line)); });
lh('lhHex', { top: 60, m: { t: 184 } }, function (t, W, H) { var s = '', r = 34, hx = function (cx, cy, c, o) { var a = []; for (var i = 0; i < 6; i++) { var g = Math.PI / 3 * i + Math.PI / 6; a.push([cx + r * Math.cos(g), cy + r * Math.sin(g)]); } return poly(a, c, o); }; s += hx(W - 80, 50, t.p) + hx(W - 139, 50, t.s) + hx(W - 110, 101, t.a) + hx(W - 51, 101, t.tint) + hx(W - 198, 50, t.tint); return svg(W, H, s + rect(72, 156, 72, 4, t.a)); });
lh('lhGradBar', { top: 54, m: { t: 172 } }, function (t, W, H) { return svg(W, H, rect(0, 0, W, 18, 'url(#lgb)') + rect(0, H - 18, W, 18, 'url(#lgb)') + rect(72, 146, W - 144, 1.2, t.line), grad('lgb', t.p, t.a, 1, 0)); });
lh('lhBracket', { top: 66, m: { t: 190, l: 96 } }, function (t, W, H) { return svg(W, H, rect(56, 46, 90, 6, t.a) + rect(56, 46, 6, 90, t.a) + rect(W - 146, 150, 90, 2, t.p) + rect(W - 58, 60, 2, 92, t.p)); });
lh('lhRibbon', { top: 60, m: { t: 180 } }, function (t, W, H) { return svg(W, H, rect(W - 140, 0, 48, 120, t.p) + poly([[W - 140, 120], [W - 116, 100], [W - 92, 120]], '#FFFFFF') + rect(W - 84, 0, 16, 84, t.a) + rect(72, 152, W - 260, 1.2, t.line)); });
lh('lhStripes', { top: 70, m: { t: 190 } }, function (t, W, H) { var s = ''; for (var i = -4; i < 40; i++) s += poly([[i * 30, 0], [i * 30 + 14, 0], [i * 30 + 14 - 48, 48], [i * 30 - 48, 48]], i % 4 ? t.p : t.a, i % 4 ? 0.85 : 1); return svg(W, H, '<clipPath id="lhs"><rect x="0" y="0" width="' + W + '" height="48"/></clipPath><g clip-path="url(#lhs)">' + s + '</g>' + rect(72, 160, 60, 4, t.a)); });
lh('lhArcs', { top: 60, m: { t: 180 } }, function (t, W, H) { return svg(W, H, ring(W, 0, 90, t.p, 22) + ring(W, 0, 130, t.a, 6) + ring(W, 0, 156, t.line, 1.5) + rect(72, 152, 80, 3, t.p)); });
lh('lhPanel', { top: 60, m: { t: 170, l: 250 } }, function (t, W, H) { return svg(W, H, rect(0, 0, 200, H, t.tint) + rect(196, 0, 4, H, t.a) + rect(40, 60, 120, 120, t.p, 1, 6) + circ(100, 120, 28, t.a) + dots(46, H - 180, 6, 6, 20, 2.4, t.p, 0.3)); });
lh('lhBottomWave', { top: 56, m: { t: 172, b: 130 } }, function (t, W, H) { return svg(W, H, rect(0, 0, W, 6, t.a) + wave(W, H - 70, 16, t.s, 0.5, 0, H) + wave(W, H - 50, 14, t.p, 1, 1, H) + rect(72, 146, W - 144, 1.2, t.line)); });
lh('lhTab', { top: 112, m: { t: 228 } }, function (t, W, H) { return svg(W, H, rect(72, 0, 150, 84, t.p, 1, 0) + rect(72, 84, 150, 6, t.a) + rect(236, 70, W - 308, 1.2, t.line) + rect(0, H - 6, W, 6, t.p)); });
lh('lhTiles', { top: 60, m: { t: 184 } }, function (t, W, H) { var s = '', c = 56; s += rect(W - 72 - c * 3, 40, c, c, t.p) + '<clipPath id="lt1"><rect x="' + (W - 72 - c * 2) + '" y="40" width="' + c + '" height="' + c + '"/></clipPath><g clip-path="url(#lt1)">' + circ(W - 72 - c * 2, 40 + c, c, t.a) + '</g>' + rect(W - 72 - c, 40, c, c, t.tint) + circ(W - 72 - c / 2, 40 + c / 2, c * 0.3, t.s); return svg(W, H, s + rect(72, 152, W - 144, 1.2, t.line)); });
lh('lhMinimal', { top: 64, m: { t: 180 } }, function (t, W, H) { return svg(W, H, rect(72, 58, 14, 14, t.a) + line(72, 152, W - 72, 152, t.ink, 0.8) + line(72, H - 60, W - 72, H - 60, t.line, 0.8)); });
lh('lhBlocks', { top: 60, m: { t: 172, b: 120 } }, function (t, W, H) { var c = [t.p, t.s, t.a, t.tint], s = '', x = 0; [0.3, 0.2, 0.12, 0.38].forEach(function (f, i) { s += rect(x, H - 34, W * f, 34, c[i]); x += W * f; }); return svg(W, H, s + rect(72, 146, 120, 4, t.p)); });
lh('lhDotsTop', { top: 64, m: { t: 184 } }, function (t, W, H) { return svg(W, H, dots(W - 250, 30, 10, 5, 18, 2.4, t.p, 0.4) + dots(W - 250 + 18 * 7, 30 + 18 * 3, 3, 2, 18, 2.4, t.a, 1) + rect(72, 156, 64, 4, t.a)); });
lh('lhBlockBand', { top: 44, dark: 1, m: { t: 200 } }, function (t, W, H) { return svg(W, H, rect(0, 0, W, 156, t.p) + rect(0, 156, W, 6, t.a) + rect(W - 180, 0, 180, 156, t.s, 0.55) + circ(W - 90, 78, 44, t.a, 0.9)); });

/* ---------------- resume page art (sidebars, bands, rails) ---------------- */
D.ART.page.sbBlock = function (t, W, H, o) { var w = (o && o.sw) || 260; return svg(W, H, rect(0, 0, w, H, t.p) + rect(0, 0, w, 10, t.a) + circ(w - 30, H - 40, 90, '#FFFFFF', 0.05) + rect(w, 0, 4, H, t.a)); };
D.ART.page.sbGrad = function (t, W, H, o) { var w = (o && o.sw) || 260; return svg(W, H, rect(0, 0, w, H, 'url(#sbg)') + dots(24, H - 140, 5, 5, 18, 2.2, '#FFFFFF', 0.28), grad('sbg', t.p, t.s, 0, 1)); };
D.ART.page.sbAngle = function (t, W, H, o) { var w = (o && o.sw) || 260; return svg(W, H, rect(0, 0, w, H, t.p) + poly([[0, H], [w, H], [w, H - 120], [0, H - 40]], t.s) + poly([[0, H - 40], [w, H - 120], [w, H - 130], [0, H - 50]], t.a)); };
D.ART.page.sbTintTop = function (t, W, H, o) { var w = (o && o.sw) || 260; return svg(W, H, rect(0, 0, w, H, t.tint) + rect(0, 0, W, 10, t.p) + rect(0, 10, w, 4, t.a)); };
D.ART.page.sbTintDots = function (t, W, H, o) { var w = (o && o.sw) || 260; return svg(W, H, rect(0, 0, w, H, t.tint) + dots(20, 20, 3, H / 22 | 0, 22, 1.6, t.p, 0.18) + rect(w - 3, 0, 3, H, t.p)); };
D.ART.page.bandAngle = function (t, W, H, o) { var h = (o && o.hh) || 180; return svg(W, H, poly([[0, 0], [W, 0], [W, h - 30], [0, h + 10]], t.p) + poly([[0, h + 10], [W, h - 30], [W, h - 22], [0, h + 18]], t.a)); };
D.ART.page.bandWave = function (t, W, H, o) { var h = (o && o.hh) || 180; return svg(W, H, path('M0 0 L' + W + ' 0 L' + W + ' ' + (h - 20) + ' C ' + W * 0.7 + ' ' + (h + 30) + ', ' + W * 0.3 + ' ' + (h - 50) + ', 0 ' + (h + 10) + ' Z', t.p) + circ(W - 80, 30, 70, '#FFFFFF', 0.07)); };
D.ART.page.bandSplit = function (t, W, H, o) { var h = (o && o.hh) || 180; return svg(W, H, rect(0, 0, W, h, t.p) + rect(W * 0.72, 0, W * 0.28, h, t.s) + rect(0, h, W, 6, t.a) + dots(W * 0.76, 30, 6, 5, 18, 2.2, '#FFFFFF', 0.35)); };
D.ART.page.bandCircles = function (t, W, H, o) { var h = (o && o.hh) || 180; return svg(W, H, '<clipPath id="bcc"><rect x="0" y="0" width="' + W + '" height="' + h + '"/></clipPath><g clip-path="url(#bcc)">' + rect(0, 0, W, h, t.p) + circ(W - 60, h, 140, t.s) + circ(W - 180, 20, 60, t.a, 0.85) + '</g>'); };
D.ART.page.railDots = function (t, W, H) { var s = ''; for (var y = 120; y < H - 60; y += 34) s += circ(64, y, 3, t.a, 0.7); return svg(W, H, rect(62, 0, 4, H, t.p) + s + rect(0, 0, W, 8, t.p)); };
D.ART.page.railBlock = function (t, W, H) { return svg(W, H, rect(0, 0, 44, H, t.tint) + rect(40, 0, 4, H, t.p) + rect(0, 80, 44, 120, t.a)); };
D.ART.page.centreTop = function (t, W, H) { return svg(W, H, rect(0, 0, W, 8, t.p) + line(96, 168, W - 96, 168, t.a, 1.5) + line(96, H - 50, W - 96, H - 50, t.line, 1)); };
D.ART.page.centreFrame = function (t, W, H) { return svg(W, H, '<rect x="26" y="26" width="' + (W - 52) + '" height="' + (H - 52) + '" fill="none" stroke="' + t.p + '" stroke-width="1.4"/>' + rect(W / 2 - 40, 22, 80, 8, t.a)); };
D.ART.page.topAccent = function (t, W, H) { return svg(W, H, rect(0, 0, W, 14, t.p) + rect(72, 14, 120, 6, t.a) + rect(0, H - 6, W, 6, t.p)); };
D.ART.page.cornerSq = function (t, W, H) { return svg(W, H, rect(W - 120, 0, 120, 120, t.p) + rect(W - 160, 0, 40, 40, t.a) + rect(0, H - 60, 60, 60, t.s, 0.85)); };

/* ---------------- inner-page decoration (repeats on every page) ---------------- */
D.ART.page.none = function () { return ''; };
D.ART.page.topband = function (t, W, H) { return svg(W, H, rect(0, 0, W, 14, t.p) + rect(0, 14, W * 0.3, 5, t.a)); };
D.ART.page.siderail = function (t, W, H) { return svg(W, H, rect(0, 0, 16, H, t.p) + rect(0, 120, 16, 90, t.a)); };
D.ART.page.cornertr = function (t, W, H) { return svg(W, H, poly([[W - 150, 0], [W, 0], [W, 150]], t.p) + poly([[W - 70, 0], [W, 0], [W, 70]], t.a) + poly([[0, H], [0, H - 60], [60, H]], t.s, 0.9)); };
D.ART.page.bottomwave = function (t, W, H) { return svg(W, H, wave(W, H - 44, 12, t.s, 0.5, 0, H) + wave(W, H - 30, 10, t.p, 1, 1, H)); };
D.ART.page.dotcorner = function (t, W, H) { return svg(W, H, dots(W - 140, 36, 6, 4, 18, 2.6, t.p, 0.35) + rect(0, H - 8, W, 8, t.p) + rect(0, H - 8, W * 0.22, 8, t.a)); };
D.ART.page.frame = function (t, W, H) { return svg(W, H, '<rect x="28" y="28" width="' + (W - 56) + '" height="' + (H - 56) + '" fill="none" stroke="' + t.p + '" stroke-width="1"/>'); };
D.ART.page.leftrail = function (t, W, H) { return svg(W, H, rect(0, 0, 54, H, t.p) + rect(54, 0, 4, H, t.a)); };
D.ART.page.sidebar = function (t, W, H, o) { var w = (o && o.sw) || W * 0.33; return svg(W, H, rect(0, 0, w, H, t.p)); };
D.ART.page.sidebarTint = function (t, W, H, o) { var w = (o && o.sw) || W * 0.33; return svg(W, H, rect(0, 0, w, H, t.tint) + rect(w - 4, 0, 4, H, t.a)); };
D.ART.page.headband = function (t, W, H, o) { var h = (o && o.hh) || 190; return svg(W, H, rect(0, 0, W, h, t.p) + rect(0, h, W, 6, t.a)); };
D.ART.page.notebook = function (t, W, H) { return svg(W, H, line(70, 0, 70, H, t.a, 1.2, 0.5)); };
D.ART.page.ribbon = function (t, W, H) { return svg(W, H, rect(W - 110, 0, 36, 90, t.a) + poly([[W - 110, 90], [W - 92, 76], [W - 74, 90]], '#FFFFFF')); };

/* ---------------- letterheads (top of a letter / memo / invoice) ---------------- */
D.ART.head.bar = function (t, W, H) { return svg(W, H, rect(0, 0, W, 10, t.p) + rect(72, 120, W - 144, 2, t.a)); };
D.ART.head.barTop = function (t, W, H) { return svg(W, H, rect(0, 0, W, 10, t.p)); };
D.ART.head.block = function (t, W, H) { return svg(W, H, rect(0, 0, W, 150, t.p) + rect(0, 150, W, 6, t.a) + circ(W - 80, 30, 90, '#FFFFFF', 0.06)); };
D.ART.head.side = function (t, W, H) { return svg(W, H, rect(0, 0, 22, H, t.p) + rect(72, 132, 120, 4, t.a)); };
D.ART.head.classic = function (t, W, H) { return svg(W, H, line(96, 130, W - 96, 130, t.p, 1.5) + line(96, 134, W - 96, 134, t.p, 0.6) + line(96, H - 70, W - 96, H - 70, t.line, 1)); };
D.ART.head.corner = function (t, W, H) { return svg(W, H, poly([[W - 220, 0], [W, 0], [W, 160]], t.p) + poly([[W - 110, 0], [W, 0], [W, 80]], t.a) + poly([[0, H], [0, H - 90], [140, H]], t.s, 0.85)); };
/* ---------- document types (what the AI writes / what a template is for) ---------- */
D.TYPES = [
  { id: 'report', name: 'Business report', group: 'Business & Corporate', ai: 1, pages: 6 },
  { id: 'proposal', name: 'Business proposal', group: 'Business & Corporate', ai: 1, pages: 6 },
  { id: 'business_plan', name: 'Business plan', group: 'Business & Corporate', ai: 1, pages: 8 },
  { id: 'case_study', name: 'Case study', group: 'Business & Corporate', ai: 1, pages: 4 },
  { id: 'company_profile', name: 'Company profile', group: 'Business & Corporate', ai: 1, pages: 5 },
  { id: 'annual_report', name: 'Annual report', group: 'Business & Corporate', ai: 1, pages: 8 },
  { id: 'whitepaper', name: 'White paper', group: 'Business & Corporate', ai: 1, pages: 6 },
  { id: 'sow', name: 'Statement of work', group: 'Business & Corporate', ai: 1, pages: 4 },
  { id: 'ebook', name: 'eBook / guide', group: 'Education & Learning', ai: 1, pages: 10 },
  { id: 'research', name: 'Research paper', group: 'Education & Learning', ai: 1, pages: 6 },
  { id: 'essay', name: 'Article / essay', group: 'Education & Learning', ai: 1, pages: 3 },
  { id: 'student_report', name: 'Student report', group: 'Education & Learning', ai: 1, pages: 4 },
  { id: 'lesson', name: 'Lesson plan / notes', group: 'Education & Learning', ai: 1, pages: 3 },
  { id: 'study_guide', name: 'Study guide', group: 'Education & Learning', ai: 1, pages: 4 },
  { id: 'resume', name: 'Resume', group: 'Career', ai: 1, pages: 1, fixed: 1 },
  { id: 'cover_letter', name: 'Cover letter', group: 'Career', ai: 1, pages: 1, fixed: 1 },
  { id: 'professional_bio', name: 'Professional biography', group: 'Career', ai: 1, pages: 1, fixed: 1 },
  { id: 'job_description', name: 'Job description', group: 'Career', ai: 1, pages: 2 },
  { id: 'letter', name: 'Business letter', group: 'Letters & Memos', ai: 1, pages: 1, fixed: 1 },
  { id: 'formal_letter', name: 'Formal letter', group: 'Letters & Memos', ai: 1, pages: 1, fixed: 1 },
  { id: 'email_draft', name: 'Email draft', group: 'Letters & Memos', ai: 1, pages: 1, fixed: 1 },
  { id: 'memo', name: 'Memo', group: 'Letters & Memos', ai: 1, pages: 1, fixed: 1 },
  { id: 'press', name: 'Press release', group: 'Letters & Memos', ai: 1, pages: 2, fixed: 1 },
  { id: 'newsletter', name: 'Newsletter', group: 'Letters & Memos', ai: 1, pages: 3 },
  { id: 'fax', name: 'Fax cover sheet', group: 'Letters & Memos', ai: 0, pages: 1, fixed: 1 },
  { id: 'agenda', name: 'Meeting agenda', group: 'Meetings & Planning', ai: 1, pages: 1, fixed: 1 },
  { id: 'minutes', name: 'Meeting minutes', group: 'Meetings & Planning', ai: 1, pages: 2, fixed: 1 },
  { id: 'project_plan', name: 'Project plan', group: 'Meetings & Planning', ai: 1, pages: 5 },
  { id: 'project_timeline', name: 'Project timeline', group: 'Meetings & Planning', ai: 1, pages: 1, fixed: 1 },
  { id: 'todo', name: 'To-do list', group: 'Meetings & Planning', ai: 1, pages: 1, fixed: 1 },
  { id: 'planner', name: 'Weekly planner', group: 'Meetings & Planning', ai: 0, pages: 1, fixed: 1 },
  { id: 'calendar', name: 'Monthly calendar', group: 'Meetings & Planning', ai: 0, pages: 1, fixed: 1 },
  { id: 'invoice', name: 'Invoice', group: 'Finance', ai: 0, pages: 1, fixed: 1 },
  { id: 'quote', name: 'Quote / estimate', group: 'Finance', ai: 1, pages: 1, fixed: 1 },
  { id: 'budget_plan', name: 'Budget plan', group: 'Finance', ai: 1, pages: 2 },
  { id: 'financial_report', name: 'Financial report', group: 'Finance', ai: 1, pages: 6 },
  { id: 'expense', name: 'Expense report', group: 'Finance', ai: 0, pages: 1, fixed: 1 },
  { id: 'flyer', name: 'Flyer / poster', group: 'Marketing & Advertising', ai: 1, pages: 1, fixed: 1 },
  { id: 'invitation', name: 'Invitation', group: 'Events & Print', ai: 1, pages: 1, fixed: 1 },
  { id: 'menu', name: 'Menu', group: 'Events & Print', ai: 1, pages: 1, fixed: 1 },
  { id: 'certificate', name: 'Certificate', group: 'Events & Print', ai: 0, pages: 1, fixed: 1 },
  { id: 'tickets', name: 'Tickets', group: 'Events & Print', ai: 0, pages: 1, fixed: 1 },
  { id: 'card', name: 'Thank-you card', group: 'Events & Print', ai: 1, pages: 1, fixed: 1 }
];
D.type = function (id) { for (var i = 0; i < D.TYPES.length; i++) if (D.TYPES[i].id === id) return D.TYPES[i]; return D.TYPES[0]; };

/* ---------- designs ----------
 * kind: flow (cover + flowing pages) | resume | single (one designed page)
 * cover: cover art (flow); page: decoration on every page; first: decoration on page 1 only
 * head: heading style; types: document types it is made for (first = its sample)
 */
function des(id, name, kind, types, theme, o) {
  var d = { id: id, name: name, kind: kind, types: types, theme: theme, cover: null, page: 'none', first: null, head: 'bar', body: 10.5, h1: 20, h2: 13.5, m: { t: 96, r: 88, b: 88, l: 88 } };
  for (var k in o || {}) d[k] = o[k];
  return d;
}
var FLOW = ['report', 'proposal', 'business_plan', 'case_study', 'whitepaper', 'sow', 'ebook'];
D.DESIGNS = [
  // ---- reports, proposals, plans (designed cover + flowing pages) ----
  des('meridian', 'Meridian', 'flow', FLOW, 'navy-gold', { cover: 'diagonal', page: 'topband', head: 'bar', desc: 'Diagonal colour band · corporate' }),
  des('atlas', 'Atlas', 'flow', ['report', 'case_study', 'proposal', 'whitepaper', 'newsletter'], 'ocean', { cover: 'photo', page: 'siderail', head: 'number', desc: 'Photo cover · numbered sections' }),
  des('summit', 'Summit', 'flow', ['business_plan', 'proposal', 'report', 'sow'], 'emerald', { cover: 'geo', page: 'cornertr', head: 'caps', desc: 'Geometric corners · bold labels' }),
  des('horizon', 'Horizon', 'flow', ['proposal', 'report', 'business_plan', 'case_study'], 'teal-coral', { cover: 'waves', page: 'bottomwave', head: 'underline', desc: 'Flowing waves · friendly' }),
  des('keystone', 'Keystone', 'flow', ['report', 'whitepaper', 'proposal', 'sow'], 'midnight', { cover: 'split', page: 'leftrail', head: 'bar', m: { t: 96, r: 88, b: 88, l: 104 }, desc: 'Split panel · executive' }),
  des('prism', 'Prism', 'flow', ['proposal', 'business_plan', 'report', 'ebook'], 'royal', { cover: 'gradient', page: 'dotcorner', head: 'number', desc: 'Gradient cover · modern' }),
  des('ledger', 'Ledger', 'flow', ['report', 'whitepaper', 'sow', 'case_study'], 'slate-sky', { cover: 'minimal', page: 'topband', head: 'underline', desc: 'Minimal · financial' }),
  des('pinnacle', 'Pinnacle', 'flow', ['case_study', 'proposal', 'report'], 'crimson', { cover: 'corner', page: 'cornertr', head: 'caps', desc: 'Bold corner blocks' }),
  des('canvas', 'Canvas', 'flow', ['proposal', 'case_study', 'ebook', 'report'], 'sunset', { cover: 'circles', page: 'dotcorner', head: 'bar', desc: 'Overlapping circles · creative' }),
  des('aurora', 'Aurora', 'flow', ['proposal', 'ebook', 'business_plan', 'report'], 'lavender', { cover: 'blob', page: 'none', head: 'caps', desc: 'Soft shapes · fresh' }),
  des('chapter', 'Chapter', 'flow', ['ebook', 'essay', 'report'], 'forest', { cover: 'book', page: 'none', head: 'chapter', body: 11, desc: 'Book cover · chapter openers' }),
  des('playbook', 'Playbook', 'flow', ['ebook', 'sow', 'lesson', 'whitepaper'], 'indigo-mint', { cover: 'stripes', page: 'siderail', head: 'number', desc: 'Striped band · handbook' }),
  des('scholar', 'Scholar', 'flow', ['research', 'essay', 'student_report', 'whitepaper'], 'classic', { cover: 'academic', page: 'none', head: 'serif', body: 11, m: { t: 96, r: 96, b: 96, l: 96 }, desc: 'Academic title page' }),
  des('lecture', 'Lecture', 'flow', ['lesson', 'student_report', 'essay'], 'arctic', { cover: 'notebook', page: 'notebook', head: 'underline', m: { t: 96, r: 88, b: 88, l: 104 }, desc: 'Notebook · school' }),
  des('gazette', 'Gazette', 'flow', ['newsletter', 'press'], 'mono', { first: 'block', page: 'none', head: 'ruletop', desc: 'Newspaper masthead' }),
  des('bulletin', 'Bulletin', 'flow', ['newsletter', 'press', 'memo'], 'ocean', { first: 'barTop', page: 'none', head: 'bar', desc: 'Clean bulletin' }),
  // ---- letters, memos, forms (letterhead on page 1) ----
  des('letter-classic', 'Classic Letter', 'flow', ['letter', 'cover_letter'], 'burgundy', { first: 'classic', head: 'plain', body: 11, m: { t: 160, r: 96, b: 96, l: 96 }, desc: 'Centred letterhead · serif' }),
  des('letter-modern', 'Modern Letter', 'flow', ['letter', 'cover_letter'], 'ocean', { first: 'side', head: 'plain', m: { t: 150, r: 88, b: 88, l: 88 }, desc: 'Side bar · modern' }),
  des('letter-bold', 'Bold Letter', 'flow', ['cover_letter', 'letter'], 'indigo-mint', { first: 'block', head: 'plain', m: { t: 190, r: 88, b: 88, l: 88 }, desc: 'Colour header block' }),
  des('memo-bold', 'Bold Memo', 'flow', ['memo'], 'sunset', { first: 'corner', head: 'bar', m: { t: 120, r: 88, b: 88, l: 88 }, desc: 'Big memo title' }),
  des('memo-clean', 'Clean Memo', 'flow', ['memo', 'press'], 'slate-sky', { first: 'bar', head: 'underline', m: { t: 110, r: 88, b: 88, l: 88 }, desc: 'Simple office memo' }),
  des('fax-classic', 'Fax Cover', 'flow', ['fax'], 'mono', { first: 'classic', head: 'plain', m: { t: 150, r: 96, b: 96, l: 96 }, desc: 'Classic fax sheet' }),
  des('agenda-modern', 'Agenda', 'flow', ['agenda', 'minutes'], 'teal-coral', { first: 'block', head: 'bar', m: { t: 190, r: 88, b: 88, l: 88 }, desc: 'Meeting agenda' }),
  des('minutes-clean', 'Minutes', 'flow', ['minutes', 'agenda'], 'navy-gold', { first: 'bar', head: 'underline', m: { t: 110, r: 88, b: 88, l: 88 }, desc: 'Minutes & actions' }),
  des('invoice-modern', 'Invoice Blue', 'flow', ['invoice', 'quote', 'expense'], 'ocean', { first: 'block', head: 'plain', m: { t: 190, r: 80, b: 80, l: 80 }, desc: 'Colour header invoice' }),
  des('invoice-minimal', 'Invoice Gold', 'flow', ['invoice', 'quote', 'expense'], 'black-gold', { first: 'classic', head: 'plain', m: { t: 160, r: 88, b: 88, l: 88 }, desc: 'Minimal black & gold' }),
  des('expense-sheet', 'Expense Sheet', 'flow', ['expense', 'invoice'], 'emerald', { first: 'bar', head: 'plain', m: { t: 110, r: 80, b: 80, l: 80 }, desc: 'Expense report' }),
  des('todo-pop', 'To-do Pop', 'flow', ['todo', 'planner'], 'cherry', { first: 'block', head: 'bar', m: { t: 190, r: 80, b: 80, l: 80 }, desc: 'Bright to-do list' }),
  des('planner-calm', 'Calm Planner', 'flow', ['planner', 'todo'], 'lavender', { first: 'bar', head: 'caps', m: { t: 110, r: 72, b: 72, l: 72 }, desc: 'Weekly planner' }),
  des('calendar-grid', 'Month Grid', 'flow', ['calendar'], 'forest', { first: 'block', head: 'plain', m: { t: 190, r: 64, b: 64, l: 64 }, desc: 'Monthly calendar' }),
  // ---- resumes ----
  des('resume-sidebar', 'Navy Sidebar', 'resume', ['resume'], 'navy-gold', { page: 'sidebar', side: 'dark', sw: 262, m: { t: 64, r: 56, b: 56, l: 40 }, desc: 'Dark sidebar' }),
  des('resume-tint', 'Soft Sidebar', 'resume', ['resume'], 'teal-coral', { page: 'sidebarTint', side: 'tint', sw: 262, m: { t: 64, r: 56, b: 56, l: 40 }, desc: 'Tinted sidebar' }),
  des('resume-minimal', 'Elegant', 'resume', ['resume'], 'black-gold', { page: 'none', side: 'none', m: { t: 80, r: 88, b: 72, l: 88 }, desc: 'Centred serif' }),
  des('resume-band', 'Header Band', 'resume', ['resume'], 'crimson', { page: 'headband', side: 'none', hh: 170, m: { t: 48, r: 72, b: 64, l: 72 }, desc: 'Name in colour band' }),
  des('resume-timeline', 'Timeline', 'resume', ['resume'], 'indigo-mint', { page: 'leftrail', side: 'none', m: { t: 72, r: 64, b: 64, l: 168 }, desc: 'Dates down the side' }),
  // ---- one designed page ----
  des('flyer-bold', 'Bold Flyer', 'single', ['poster'], 'sunset', { layout: 'flyerBold', desc: 'Big shapes · event or sale' }),
  des('flyer-photo', 'Photo Flyer', 'single', ['poster'], 'ocean', { layout: 'flyerPhoto', desc: 'Photo on top' }),
  des('invite-elegant', 'Elegant Invite', 'single', ['invitation'], 'black-gold', { layout: 'inviteElegant', desc: 'Formal invitation' }),
  des('invite-party', 'Party Invite', 'single', ['invitation'], 'cherry', { layout: 'inviteParty', desc: 'Confetti party' }),
  des('menu-bistro', 'Bistro Menu', 'single', ['menu'], 'forest', { layout: 'menu', desc: 'Restaurant menu' }),
  des('certificate-classic', 'Classic Certificate', 'single', ['certificate'], 'navy-gold', { layout: 'certificate', orient: 'landscape', desc: 'Award certificate' }),
  des('tickets-sheet', 'Ticket Sheet', 'single', ['tickets'], 'royal', { layout: 'tickets', desc: '8 tickets per page' }),
  des('card-thanks', 'Thank-you Card', 'single', ['card'], 'rose', { layout: 'card', desc: 'Note card' })
];
/* ---------- Business & Corporate collection: palettes and designs ----------
 * Every subcategory gets its own designs (made for that document type only), each with its own cover
 * idea, palette, type pairing, inner-page decoration, heading style and spacing.
 */
(function () {
  function pal(id, name, p, s, a, head, body) {
    var ink = D.mix(p, '#15171C', 0.78), mute = D.mix(ink, '#FFFFFF', 0.4);
    var t = th(id, name, p, s, a, ink, mute, D.mix(p, '#FFFFFF', 0.94), D.mix(p, '#FFFFFF', 0.84), head, body);
    t.hide = 1; return t;
  }
  var P = [
    pal('c01', 'Navy & Teal', '#0B2545', '#13315C', '#1FA7A0', 'Aptos Display', 'Aptos'),
    pal('c02', 'Graphite & Orange', '#1F2328', '#3A4048', '#F26B21', 'Bahnschrift', 'Aptos'),
    pal('c03', 'Royal & Sky', '#1D3B8F', '#3D6FD8', '#5BC0EB', 'Segoe UI', 'Segoe UI'),
    pal('c04', 'Forest & Gold', '#183A2E', '#2E6B4F', '#C9A54C', 'Garamond', 'Gill Sans MT'),
    pal('c05', 'Burgundy & Blush', '#5C1A2B', '#8E3A4E', '#E7A27E', 'Bodoni MT', 'Gill Sans MT'),
    pal('c06', 'Ink & Lime', '#121826', '#253047', '#A3D139', 'Franklin Gothic Medium', 'Aptos'),
    pal('c07', 'Ocean & Coral', '#0E4C6B', '#1C7CA8', '#FF6F59', 'Century Gothic', 'Calibri'),
    pal('c08', 'Plum & Gold', '#3B1F4E', '#6B3D7E', '#E4B34A', 'Constantia', 'Corbel'),
    pal('c09', 'Steel & Red', '#2B3A4A', '#4C6075', '#D93F3F', 'Arial Black', 'Arial'),
    pal('c10', 'Teal & Sand', '#0F5257', '#2A8A86', '#E3B778', 'Georgia', 'Aptos'),
    pal('c11', 'Indigo & Amber', '#23215B', '#3E3A99', '#F5A623', 'Aptos Display', 'Aptos'),
    pal('c12', 'Charcoal & Gold', '#222222', '#444444', '#C8A24A', 'Book Antiqua', 'Gill Sans MT'),
    pal('c13', 'Blue & Mint', '#0A3D91', '#1C5FD1', '#2BD9A7', 'Bahnschrift', 'Segoe UI'),
    pal('c14', 'Espresso & Caramel', '#3E2A1F', '#6E4B37', '#D9A066', 'Rockwell', 'Calibri'),
    pal('c15', 'Slate & Mint', '#334155', '#475569', '#34D399', 'Segoe UI', 'Segoe UI'),
    pal('c16', 'Crimson & Navy', '#9B1B30', '#1F2A44', '#F2C14E', 'Cambria', 'Calibri'),
    pal('c17', 'Emerald & Navy', '#065F46', '#0F3B57', '#F59E0B', 'Tw Cen MT', 'Calibri'),
    pal('c18', 'Violet & Cyan', '#4C1D95', '#6D28D9', '#22D3EE', 'Century Gothic', 'Century Gothic'),
    pal('c19', 'Petrol & Peach', '#0B4F5C', '#127C8A', '#F4A88A', 'Trebuchet MS', 'Calibri'),
    pal('c20', 'Midnight & Teal', '#0F172A', '#1E293B', '#14B8A6', 'Aptos Display', 'Aptos'),
    pal('c21', 'Oxblood & Sand', '#5B1B1B', '#8B3A3A', '#D8B48A', 'Garamond', 'Georgia'),
    pal('c22', 'Cobalt & Orange', '#1446A0', '#2F6FDB', '#FF8A3D', 'Franklin Gothic Medium', 'Calibri'),
    pal('c23', 'Olive & Gold', '#3D4A1F', '#66773A', '#D4A93C', 'Book Antiqua', 'Corbel'),
    pal('c24', 'Navy & Pink', '#172554', '#1E3A8A', '#F472B6', 'Segoe UI', 'Segoe UI'),
    pal('c25', 'Copper & Ink', '#1C1C28', '#3B3B52', '#C87533', 'Bodoni MT', 'Aptos'),
    pal('c26', 'Aqua & Navy', '#0C2D48', '#145DA0', '#2EC4B6', 'Calibri Light', 'Calibri'),
    pal('c27', 'Ruby & Grey', '#A4161A', '#4A4E58', '#F5B700', 'Bahnschrift', 'Calibri'),
    pal('c28', 'Sage & Charcoal', '#2F3E36', '#5E7A66', '#D6A35C', 'Constantia', 'Corbel'),
    pal('c29', 'Ultramarine & Gold', '#1B1F6B', '#3A3FBF', '#E9B949', 'Georgia', 'Aptos'),
    pal('c30', 'Teal & Magenta', '#035E63', '#0C8A8F', '#E0458C', 'Tw Cen MT', 'Calibri'),
    pal('c31', 'Sky & Navy', '#12355B', '#2C6EA6', '#7EC8E3', 'Aptos Display', 'Aptos'),
    pal('c32', 'Black & Teal', '#0B0B0F', '#23232D', '#12B5A5', 'Arial Black', 'Arial'),
    pal('c33', 'Wine & Gold', '#4A1030', '#7A2050', '#DDB66A', 'Cambria', 'Calibri'),
    pal('c34', 'Pine & Coral', '#0D3B2E', '#1F6E57', '#F07167', 'Century Gothic', 'Calibri'),
    pal('c35', 'Denim & Lemon', '#1E3A5F', '#3B6EA5', '#F2D649', 'Trebuchet MS', 'Calibri'),
    pal('c36', 'Rust & Slate', '#A23E1F', '#3C4A57', '#E9C46A', 'Rockwell', 'Calibri'),
    pal('c37', 'Navy & Silver', '#0D1B2A', '#1B263B', '#9AA9BD', 'Garamond', 'Gill Sans MT'),
    pal('c38', 'Orchid & Navy', '#2A1B3D', '#44318D', '#E98074', 'Segoe UI', 'Segoe UI'),
    pal('c39', 'Lagoon & Gold', '#014F5A', '#00838F', '#F4B942', 'Calibri Light', 'Calibri'),
    pal('c40', 'Brick & Cream', '#8A2E1E', '#B5523B', '#E8C07D', 'Book Antiqua', 'Gill Sans MT'),
    pal('c41', 'Electric Ink', '#111827', '#2563EB', '#F59E0B', 'Bahnschrift', 'Aptos'),
    pal('c42', 'Mauve & Teal', '#5E3B57', '#8E5C83', '#2AA39C', 'Constantia', 'Corbel'),
    pal('c43', 'Azure & Graphite', '#0369A1', '#334155', '#FB7185', 'Aptos Display', 'Aptos'),
    pal('c44', 'Gold & Black', '#14110F', '#34302B', '#D4AF37', 'Bodoni MT', 'Georgia')
  ];
  P.forEach(function (x) { D.THEMES.push(x); });

  var PHOTO = { lens: 1, halfphoto: 1, slash: 1, card: 1, pillphoto: 1, tiles: 1 };
  var CDESC = {
    monolith: 'Deep colour · quarter ring', halftone: 'Halftone dot corner', arch: 'Arch window', bauhaus: 'Bauhaus tiles', ribbon: 'Folded ribbon',
    columns: 'Bar-chart skyline', cubes: 'Isometric cube band', orbits: 'Concentric arcs', lens: 'Round photo window', halfphoto: 'Half-page photo',
    slash: 'Angled photo band', card: 'Photo with title card', mosaic: 'Low-poly mosaic', contour: 'Contour lines', gridblock: 'Grid paper · colour block',
    chevron: 'Growth chevrons', databars: 'Data bars', capsules: 'Rounded capsules', hexes: 'Hexagon cluster', swiss: 'Swiss grid',
    brackets: 'Corner brackets', rays: 'Sunburst rays', lattice: 'Diamond lattice', overlap: 'Overlapping panels', linewaves: 'Line waves',
    planet: 'Planet & orbit', split2: 'Diagonal two-tone', pillphoto: 'Capsule photo', glow: 'Soft glow', band: 'Title band',
    stairs: 'Rising steps', plus: 'Plus pattern panel', donut: 'Donut chart arcs', fold: 'Folded corner', inset: 'Colour border',
    petals: 'Petal fan', compose: 'Bauhaus composition', skyline: 'City skyline', dome: 'Dome', dotmap: 'Dotted world',
    sidetab: 'Side tab', shards: 'Prism shards', tiles: 'Photo tiles', spine: 'Book spine'
  };
  var SUBS = [
    ['annual_report', ['columns', 'donut', 'skyline', 'glow', 'monolith', 'swiss', 'databars', 'stairs', 'rays', 'dome', 'mosaic', 'band', 'halfphoto', 'overlap', 'orbits', 'shards', 'plus', 'card']],
    ['company_profile', ['halfphoto', 'lens', 'card', 'pillphoto', 'tiles', 'slash', 'arch', 'hexes', 'cubes', 'dotmap', 'brackets', 'sidetab', 'compose', 'petals', 'planet', 'fold', 'inset', 'ribbon']],
    ['business_plan', ['chevron', 'stairs', 'databars', 'gridblock', 'bauhaus', 'ribbon', 'split2', 'orbits', 'capsules', 'halftone', 'contour', 'columns', 'lattice', 'monolith', 'donut']],
    ['case_study', ['lens', 'slash', 'card', 'tiles', 'band', 'sidetab', 'brackets', 'dotmap', 'glow', 'split2', 'compose', 'halftone', 'fold', 'pillphoto']],
    ['whitepaper', ['swiss', 'contour', 'lattice', 'spine', 'gridblock', 'plus', 'linewaves', 'cubes', 'shards', 'inset', 'mosaic', 'dome', 'hexes', 'planet']],
    ['proposal', ['arch', 'ribbon', 'capsules', 'petals', 'overlap', 'linewaves', 'rays', 'bauhaus', 'halftone', 'sidetab', 'glow']],
    ['report', ['band', 'spine', 'inset', 'compose', 'contour', 'mosaic', 'plus', 'fold', 'dotmap']]
  ];
  var NAMES = ['Vertex', 'Lumen', 'Halo', 'Cascade', 'Northstar', 'Quartz', 'Solstice', 'Beacon', 'Strata', 'Axis', 'Cobalt', 'Ember', 'Harbor', 'Ion', 'Juniper',
    'Kestrel', 'Lattice', 'Monarch', 'Nimbus', 'Onyx', 'Pillar', 'Quill', 'Radiant', 'Sierra', 'Tandem', 'Umbra', 'Vantage', 'Wren', 'Xenon', 'Yardline',
    'Zenith', 'Alder', 'Bastion', 'Crest', 'Delta', 'Equinox', 'Fathom', 'Granite', 'Helix', 'Insight', 'Jade', 'Keystone Pro', 'Legacy', 'Mosaic', 'Nova',
    'Orbit', 'Pulse', 'Quantum', 'Ridge', 'Sable', 'Terra', 'Unity', 'Velvet', 'Willow', 'Arcadia', 'Boreal', 'Citadel', 'Dune', 'Eclipse', 'Fjord',
    'Gallery', 'Horizon Pro', 'Ivory', 'Jetstream', 'Kinetic', 'Lighthouse', 'Marble', 'Noble', 'Oasis', 'Paragon', 'Regal', 'Signal', 'Tidal', 'Upland',
    'Vista', 'Westwind', 'Atrium', 'Bridge', 'Clarity', 'Drift', 'Element', 'Frontier', 'Glacier', 'Hallmark', 'Icon', 'Journey', 'Kite', 'Landmark',
    'Mercury', 'Neon', 'Opal', 'Prospect', 'Riverstone', 'Sterling', 'Topaz', 'Uptown', 'Venture', 'Wavelength', 'Apex', 'Brio', 'Compass', 'Dawn'];
  var PAGES = ['hairline', 'tabright', 'footband', 'cornerblock', 'arcs', 'gridtop', 'doubleline', 'chevfoot', 'topbar2', 'dotsside', 'halfring', 'sidenote', 'topband', 'siderail', 'cornertr', 'dotcorner', 'leftrail'];
  var HEADS = ['overline', 'tag', 'boxed', 'double', 'bar', 'number', 'underline', 'caps', 'ruletop', 'plain'];
  var SERIF = { 'Garamond': 1, 'Bodoni MT': 1, 'Georgia': 1, 'Constantia': 1, 'Book Antiqua': 1, 'Cambria': 1, 'Rockwell': 1 };
  function coll(o) {
    var used = {}, n = 0, P = o.P, PAGES = o.pages, HEADS = o.heads, NAMES = o.names;
    o.subs.forEach(function (sub, si) {
      sub[1].forEach(function (c, i) {
        var u = used[c] = (used[c] || 0) + 1, key = o.key + c + '_' + u, flip = u === 2, alt = u === 3;
        var p = P[(si * 7 + i * 5 + (u - 1) * 13) % P.length], pg = PAGES[(si * 3 + i) % PAGES.length], hd = (sub[2] || HEADS)[(si * 2 + i * 3) % (sub[2] || HEADS).length];
        if (hd === 'plain' && SERIF[p.head]) hd = 'serif';
        if (pg === 'leftrail' || pg === 'siderail') hd = hd === 'tag' ? 'bar' : hd;
        D.ART.cover[key] = (function (cc, f) { return function (t, W, H, img) { return CORP[cc](t, W, H, img, { f: f }); }; })(c, alt ? false : flip);
        var m = { t: 92 + (i % 3) * 8, r: 84 + (i % 2) * 8, b: 84, l: pg === 'leftrail' ? 108 : pg === 'doubleline' || pg === 'dotsside' ? 96 : 84 + (i % 2) * 8 };
        if (pg === 'tabright') m.r = 96;
        if (sub[3]) { for (var q in sub[3]) m[q] = sub[3][q]; }
        var nm = NAMES[n % NAMES.length]; n++;
        D.DESIGNS.push(des(o.code + '-' + sub[0].replace(/_/g, '') + '-' + (i + 1), nm, 'flow', [sub[0]], p.id, {
          cover: key, page: pg, head: hd, body: (sub[4] || [10.5, 10.5, 11, 10])[i % 4], h1: [20, 22, 19, 21][i % 4], h2: [13.5, 14, 13, 13.5][i % 4], m: m,
          photo: !!PHOTO[c], desc: CDESC[c] + ' · ' + p.name, coll: o.coll
        }));
      });
    });
  }
  coll({ code: 'bc', key: 'k_', coll: 'business', P: P, subs: SUBS, pages: PAGES, heads: HEADS, names: NAMES });
  /* ---- Education & Learning collection ---- */
  var EP = [
    pal('e01', 'Sky & Sun', '#0B5CAD', '#3B8BEB', '#FFC233', 'Century Gothic', 'Calibri'),
    pal('e02', 'Coral & Teal', '#0E6E6E', '#22A39F', '#FF7A59', 'Trebuchet MS', 'Calibri'),
    pal('e03', 'Grape & Lime', '#4A2C82', '#7B52C9', '#B7E03B', 'Tw Cen MT', 'Calibri'),
    pal('e04', 'Ink & Tangerine', '#1D2433', '#3A4660', '#FF8C2B', 'Aptos Display', 'Aptos'),
    pal('e05', 'Oxford Blue', '#0F2C59', '#2B4C7E', '#C9A227', 'Georgia', 'Georgia'),
    pal('e06', 'Scholar Red', '#7A1E28', '#A23B45', '#2F5D7C', 'Garamond', 'Garamond'),
    pal('e07', 'Mint & Navy', '#123B5C', '#2C7DA0', '#4FD1A5', 'Segoe UI', 'Segoe UI'),
    pal('e08', 'Berry & Gold', '#6A1B4D', '#9C2C6E', '#F2B33D', 'Constantia', 'Corbel'),
    pal('e09', 'Forest Study', '#1F4D3A', '#3A7D5E', '#F0B44C', 'Cambria', 'Calibri'),
    pal('e10', 'Cobalt & Pink', '#1E40AF', '#3B63D6', '#F472B6', 'Bahnschrift', 'Segoe UI'),
    pal('e11', 'Slate & Lemon', '#2F3A4A', '#516079', '#F4D35E', 'Franklin Gothic Medium', 'Aptos'),
    pal('e12', 'Ocean Learning', '#05668D', '#028090', '#F0F3BD', 'Century Gothic', 'Century Gothic'),
    pal('e13', 'Crayon Red', '#C0392B', '#2C3E50', '#F1C40F', 'Rockwell', 'Calibri'),
    pal('e14', 'Lavender Ink', '#3E2F6B', '#6C5BB3', '#FFB38A', 'Gill Sans MT', 'Gill Sans MT'),
    pal('e15', 'Teal & Plum', '#00676B', '#2E8B8E', '#8E3B8C', 'Segoe UI', 'Segoe UI'),
    pal('e16', 'Navy & Coral', '#14213D', '#2C3E66', '#FA7268', 'Aptos Display', 'Aptos'),
    pal('e17', 'Library Green', '#244A3D', '#4B7564', '#C08A3E', 'Book Antiqua', 'Book Antiqua'),
    pal('e18', 'Royal Purple', '#3B1E6E', '#5B34A8', '#F5C451', 'Bodoni MT', 'Georgia'),
    pal('e19', 'Turquoise & Ink', '#0B3C49', '#0E8E9C', '#FFD166', 'Calibri Light', 'Calibri'),
    pal('e20', 'Chalk Green', '#2E5E4E', '#45826C', '#FFE08A', 'Trebuchet MS', 'Trebuchet MS'),
    pal('e21', 'Classic Navy', '#1B2A41', '#324A5F', '#B3001B', 'Times New Roman', 'Times New Roman'),
    pal('e22', 'Orange & Blue', '#1A4E8A', '#2F74C0', '#FF9F1C', 'Bahnschrift', 'Calibri'),
    pal('e23', 'Rose & Grey', '#9D3C5E', '#4A4E5A', '#F6B26B', 'Constantia', 'Corbel'),
    pal('e24', 'Midnight Gold', '#121A2E', '#23304F', '#E8B04A', 'Garamond', 'Gill Sans MT'),
    pal('e25', 'Leaf & Sky', '#2E7D32', '#43A047', '#29B6F6', 'Century Gothic', 'Calibri'),
    pal('e26', 'Indigo & Aqua', '#2A2D7C', '#4549B8', '#2EC4D6', 'Aptos Display', 'Aptos'),
    pal('e27', 'Brown Paper', '#5B3A29', '#8A5A44', '#2A9D8F', 'Rockwell', 'Calibri'),
    pal('e28', 'Charcoal Red', '#262626', '#454545', '#E63946', 'Arial Black', 'Arial'),
    pal('e29', 'Steel & Mint', '#37474F', '#546E7A', '#64FFDA', 'Segoe UI', 'Segoe UI'),
    pal('e30', 'Plum & Peach', '#4E2A4F', '#7D4E7E', '#FFB997', 'Book Antiqua', 'Gill Sans MT'),
    pal('e31', 'Science Blue', '#0D3B66', '#1D70A2', '#F95738', 'Franklin Gothic Medium', 'Calibri'),
    pal('e32', 'Sunflower', '#3D348B', '#7678ED', '#F7B801', 'Tw Cen MT', 'Calibri'),
    pal('e33', 'Ivy & Cream', '#264635', '#3E6B55', '#E6C99A', 'Cambria', 'Cambria'),
    pal('e34', 'Ruby & Navy', '#8B1E3F', '#1F2F4A', '#F2C14E', 'Georgia', 'Aptos'),
    pal('e35', 'Ink Blue', '#101E3C', '#2A3F6B', '#5FA8D3', 'Times New Roman', 'Calibri'),
    pal('e36', 'Candy', '#D1495B', '#00798C', '#EDAE49', 'Trebuchet MS', 'Calibri')
  ];
  EP.forEach(function (x) { D.THEMES.push(x); });
  var ED = { ruled: 'Ruled notebook', bookstack: 'Stack of books', plot: 'Graph & curve', atom: 'Atom orbits', pencils: 'Pencils', bubbles: 'Speech bubbles', puzzle: 'Jigsaw pieces',
    bulb: 'Light bulb idea', ruler: 'Ruler band', stars: 'Stars', tabs: 'Index tabs', laurel: 'Laurel wreath', pillars: 'Classical columns', chalk: 'Chalkboard', blocks: 'Toy blocks',
    editorial: 'Editorial rules', quotes: 'Big quote marks', margin: 'Manuscript margin', openbook: 'Open book', gradcap: 'Graduation cap' };
  for (var e in ED) CDESC[e] = ED[e];
  var ACAD = ['serif', 'double', 'plain', 'underline', 'ruletop', 'caps', 'overline'];
  var ESUBS = [
    ['ebook', ['openbook', 'bookstack', 'bulb', 'stars', 'tabs', 'puzzle', 'petals', 'arch', 'glow', 'linewaves', 'dome', 'capsules', 'compose', 'halfphoto']],
    ['research', ['laurel', 'pillars', 'margin', 'editorial', 'swiss', 'spine', 'contour', 'lattice', 'plot', 'atom', 'gridblock', 'inset', 'dome', 'mosaic', 'plus', 'quotes', 'halftone', 'band', 'hexes'], ACAD, { t: 96, r: 96, l: 96 }, [11, 11, 11.5, 10.5]],
    ['essay', ['editorial', 'quotes', 'margin', 'spine', 'ruled', 'inset', 'petals', 'linewaves', 'contour', 'openbook', 'band', 'compose', 'arch', 'fold', 'laurel', 'sidetab', 'dotmap'], ACAD, { t: 96, r: 96, l: 96 }, [11, 11.5, 11, 12]],
    ['student_report', ['atom', 'plot', 'bulb', 'puzzle', 'blocks', 'bubbles', 'pencils', 'ruler', 'ruled', 'gradcap', 'stars', 'chalk', 'hexes', 'cubes', 'orbits', 'bauhaus', 'chevron', 'tabs']],
    ['lesson', ['chalk', 'ruled', 'pencils', 'ruler', 'blocks', 'bubbles', 'bulb', 'gradcap', 'tabs', 'puzzle', 'openbook', 'stars', 'plot', 'atom', 'bookstack', 'bauhaus', 'capsules', 'halftone']],
    ['study_guide', ['tabs', 'bulb', 'plot', 'ruler', 'puzzle', 'bookstack', 'gradcap', 'stars', 'bubbles', 'chalk', 'ruled', 'laurel', 'openbook', 'atom', 'swiss', 'gridblock', 'donut', 'stairs']]
  ];
  var ENAMES = ['Scholar Pro', 'Campus', 'Chapter One', 'Inkwell', 'Academy', 'Lexicon', 'Syllabus', 'Notebook', 'Thesis', 'Almanac', 'Primer', 'Sage', 'Atlas Edu', 'Folio', 'Margin',
    'Lecture Hall', 'Quill Pro', 'Pathway', 'Bright', 'Curious', 'Discovery', 'Elements', 'Fieldnote', 'Genius', 'Honors', 'Insightful', 'Journal', 'Keynote', 'Learner', 'Mentor',
    'Northwind', 'Oxford', 'Pioneer', 'Quest', 'Reader', 'Studio', 'Tutor', 'Upbeat', 'Vellum', 'Wisdom', 'Xplore', 'Young Minds', 'Zest', 'Abacus', 'Beacon Edu', 'Compass Edu',
    'Dialog', 'Essayist', 'Footnote', 'Gazette Edu', 'Highlight', 'Index', 'Jot', 'Kindle', 'Lantern', 'Method', 'Notion', 'Origin', 'Paperback', 'Quiz', 'Recap', 'Summary',
    'Thinker', 'Unit', 'Venn', 'Workbook', 'Xenial', 'Yearbook', 'Zone', 'Aspire', 'Blueprint Edu', 'Cornerstone', 'Draft', 'Edition', 'Flashcard', 'Glossary', 'Handbook',
    'Inquiry', 'Junior', 'Knowledge', 'Logic', 'Module', 'Nucleus', 'Outline', 'Prism Edu', 'Query', 'Review', 'Spark', 'Topic', 'Uplift', 'Vision', 'Wonder', 'Axiom', 'Brainy',
    'Concept', 'Digest', 'Explorer', 'Fable', 'Grove', 'Hypothesis', 'Ideas', 'Journey Edu', 'Keystone Edu', 'Lumen Edu', 'Milestone', 'Novella'];
  var EPAGES = ['hairline', 'dotsside', 'topbar2', 'cornerblock', 'notebook', 'gridtop', 'footband', 'tabright', 'arcs', 'sidenote', 'chevfoot', 'doubleline', 'topband', 'halfring', 'ribbon', 'siderail'];
  /* ---- Career collection: resumes, cover letters, professional bios, job descriptions ---- */
  var KP = [
    pal('k01', 'Navy Pro', '#16324F', '#2A5C8A', '#F2A541', 'Aptos Display', 'Aptos'), pal('k02', 'Charcoal Mint', '#2B2D33', '#474B54', '#3DDC97', 'Segoe UI', 'Segoe UI'),
    pal('k03', 'Burgundy Classic', '#5E1A2F', '#8A3550', '#D4A373', 'Georgia', 'Calibri'), pal('k04', 'Teal Modern', '#0B5D63', '#1D8A8F', '#FFB347', 'Century Gothic', 'Calibri'),
    pal('k05', 'Black Gold', '#141414', '#333333', '#C9A227', 'Bodoni MT', 'Gill Sans MT'), pal('k06', 'Royal Blue', '#1F3C88', '#3A5FCD', '#5EE6EB', 'Bahnschrift', 'Segoe UI'),
    pal('k07', 'Forest Pro', '#1E4032', '#2F6B53', '#E9C46A', 'Garamond', 'Gill Sans MT'), pal('k08', 'Slate Coral', '#2E3A48', '#4E5D6E', '#FF6B6B', 'Franklin Gothic Medium', 'Aptos'),
    pal('k09', 'Plum Rose', '#4A1D46', '#7A3B72', '#F28AB2', 'Constantia', 'Corbel'), pal('k10', 'Steel Orange', '#263238', '#455A64', '#FF7043', 'Tw Cen MT', 'Calibri'),
    pal('k11', 'Sky Ink', '#0F2D52', '#1F6FB2', '#7CC6FE', 'Calibri Light', 'Calibri'), pal('k12', 'Olive Cream', '#3B4A2A', '#5E7344', '#D9B26F', 'Book Antiqua', 'Gill Sans MT'),
    pal('k13', 'Indigo Lime', '#2B2A70', '#4844B8', '#B8E04A', 'Aptos Display', 'Aptos'), pal('k14', 'Graphite Red', '#202124', '#3C4043', '#D93025', 'Arial Black', 'Arial'),
    pal('k15', 'Ocean Sand', '#0E4D64', '#137C8B', '#E0B972', 'Trebuchet MS', 'Calibri'), pal('k16', 'Wine Silver', '#4A1426', '#6E2A3E', '#A7B1BF', 'Cambria', 'Calibri'),
    pal('k17', 'Midnight Cyan', '#0E1726', '#1E2A3F', '#22D3EE', 'Segoe UI', 'Segoe UI'), pal('k18', 'Copper', '#3A2A20', '#5D4433', '#C77D3E', 'Rockwell', 'Calibri'),
    pal('k19', 'Lavender Navy', '#2D2A5A', '#4E4A94', '#C8B6FF', 'Gill Sans MT', 'Gill Sans MT'), pal('k20', 'Emerald Ink', '#0F3D2E', '#1C6B4F', '#F4D35E', 'Georgia', 'Aptos'),
    pal('k21', 'Blue Steel', '#23395B', '#406E8E', '#8EA8C3', 'Corbel', 'Corbel'), pal('k22', 'Crimson Grey', '#A4161A', '#4A4E58', '#E5E5E5', 'Bahnschrift', 'Calibri'),
    pal('k23', 'Teal Plum', '#00676B', '#5C3D6E', '#F7B267', 'Century Gothic', 'Century Gothic'), pal('k24', 'Classic Ink', '#1A1A2E', '#3A3A5A', '#E94560', 'Times New Roman', 'Calibri')
  ];
  KP.forEach(function (x) { D.THEMES.push(x); });
  var KN = ['Executive', 'Clarity', 'Momentum', 'Profile', 'Ascent', 'Credential', 'Signature', 'Precision', 'Elevate', 'Folio', 'Avenue', 'Brightside', 'Contour', 'Distinct',
    'Envoy', 'Fidelity', 'Grace', 'Heritage', 'Impact', 'Junction', 'Keystone CV', 'Linear', 'Merit', 'Notable', 'Outlook', 'Poise', 'Quorum', 'Resolve', 'Stature', 'Tailored',
    'Upright', 'Valor', 'Westgate', 'Axiom CV', 'Bold Move', 'Candor', 'Debut', 'Emblem', 'Forte', 'Gallant', 'Harmony', 'Initiative', 'Jubilee', 'Kudos', 'Leader', 'Mastery',
    'Noble CV', 'Optimum', 'Pinnacle CV', 'Quantum CV', 'Renown', 'Standard', 'Talent', 'Uplift CV', 'Vanguard', 'Worthy', 'Zenith CV', 'Atlas CV', 'Brisk', 'Crisp', 'Dynamo',
    'Earnest', 'Fresh Start', 'Genuine', 'Honour', 'Ideal', 'Journey CV', 'Kinetic CV', 'Luminary', 'Modern Pro', 'Nexus'];
  var ki = 0, kn = function () { return KN[ki++ % KN.length]; };
  /* resumes: structure + its own art */
  var RES = [
    ['dark', 'sbBlock', 250], ['dark', 'sbGrad', 240], ['dark', 'sbAngle', 260], ['dark', 'sidebar', 230], ['tint', 'sbTintTop', 250], ['tint', 'sbTintDots', 240],
    ['band', 'bandAngle', 190], ['band', 'bandWave', 200], ['band', 'bandSplit', 180], ['band', 'bandCircles', 190], ['rail', 'railDots', 0], ['rail', 'railBlock', 0],
    ['centre', 'centreTop', 0], ['centre', 'centreFrame', 0], ['plain', 'topAccent', 0], ['plain', 'cornerSq', 0]
  ];
  RES.forEach(function (r, i) {
    var p = KP[(i * 5) % KP.length], o = { desc: '', coll: 'career' };
    if (r[0] === 'dark' || r[0] === 'tint') { o.page = r[1]; o.side = r[0]; o.sw = r[2]; o.m = { t: 64, r: 56, b: 56, l: 40 }; o.desc = (r[0] === 'dark' ? 'Colour sidebar' : 'Soft sidebar'); }
    if (r[0] === 'band') { o.page = r[1]; o.side = 'none'; o.band = 1; o.hh = r[2]; o.m = { t: 48, r: 72, b: 64, l: 72 }; o.desc = 'Name in a colour band'; }
    if (r[0] === 'rail') { o.page = r[1]; o.side = 'none'; o.rmode = 'timeline'; o.m = { t: 72, r: 60, b: 64, l: 186 }; o.desc = 'Dates down the side'; }
    if (r[0] === 'centre') { o.page = r[1]; o.side = 'none'; o.rmode = 'centre'; o.m = { t: 84, r: 88, b: 72, l: 88 }; o.desc = 'Centred, elegant'; }
    if (r[0] === 'plain') { o.page = r[1]; o.side = 'none'; o.m = { t: 76, r: 76, b: 64, l: 76 }; o.desc = 'Clean one column'; }
    o.desc += ' · ' + p.name;
    D.DESIGNS.push(des('cr-resume-' + (i + 1), kn(), 'resume', ['resume'], p.id, o));
  });
  /* letter-style documents: a letterhead on page 1 */
  function lcoll(code, subs, P0, heads, collName) {
    subs.forEach(function (sub, si) {
      sub[1].forEach(function (name, i) {
        var L = LH[name], p = P0[(si * 7 + i * 5) % P0.length], m = { t: 180, r: 88, b: 88, l: 88 };
        for (var q in L.m || {}) m[q] = L.m[q];
        D.DESIGNS.push(des(code + '-' + sub[0].replace(/_/g, '') + '-' + (i + 1), kn(), 'flow', [sub[0]], p.id, {
          first: name, hbTop: L.top, hbDark: !!L.dark, hbAlign: L.al || 'left', head: (sub[2] || heads)[(si + i) % (sub[2] || heads).length], m: m, body: sub[0] === 'cover_letter' || sub[0] === 'letter' ? 11 : 10.5,
          desc: (LHD[name] || 'Letterhead') + ' · ' + p.name, coll: collName
        }));
      });
    });
  }
  var LHD = { lhAngle: 'Angled colour header', lhCornerTri: 'Corner triangles', lhSideStripe: 'Side stripe', lhDoubleRule: 'Double rule, centred', lhCircle: 'Corner circle', lhWave: 'Wave header',
    lhSplit: 'Two-tone header', lhFrame: 'Framed page', lhMonogram: 'Monogram seal', lhChevrons: 'Chevrons', lhHex: 'Hexagons', lhGradBar: 'Gradient bars', lhBracket: 'Corner brackets',
    lhRibbon: 'Bookmark ribbon', lhStripes: 'Diagonal stripes', lhArcs: 'Corner arcs', lhPanel: 'Side panel', lhBottomWave: 'Footer wave', lhTab: 'Header tab', lhTiles: 'Bauhaus tiles',
    lhMinimal: 'Minimal rule', lhBlocks: 'Colour blocks footer', lhDotsTop: 'Dot grid', lhBlockBand: 'Colour header band' };
  var LALL = Object.keys(LHD);
  function rot(a, k, n) { var o = []; for (var i = 0; i < n; i++) o.push(a[(k + i) % a.length]); return o; }
  lcoll('cr', [['cover_letter', rot(LALL, 0, 17), ['plain']], ['professional_bio', rot(LALL, 6, 18)], ['job_description', rot(LALL, 12, 18)]], KP, ['bar', 'underline', 'overline', 'caps', 'boxed', 'double', 'tag', 'plain'], 'career');

  /* ---- Letters & Memos collection ---- */
  var LP = [
    pal('l01', 'Executive Navy', '#1B2B48', '#34507A', '#C8A15A', 'Georgia', 'Calibri'), pal('l02', 'Modern Teal', '#0D5C63', '#2B8C96', '#F4A259', 'Aptos Display', 'Aptos'),
    pal('l03', 'Ink & Red', '#1C1C1C', '#3A3A3A', '#D7263D', 'Franklin Gothic Medium', 'Aptos'), pal('l04', 'Bordeaux', '#5A1830', '#86304B', '#E0B07A', 'Garamond', 'Garamond'),
    pal('l05', 'Cobalt', '#163F8C', '#2E63C7', '#53D8C8', 'Segoe UI', 'Segoe UI'), pal('l06', 'Evergreen', '#1E453E', '#2F6E62', '#D9B44A', 'Cambria', 'Calibri'),
    pal('l07', 'Graphite & Amber', '#2A2E35', '#464C56', '#F2A93B', 'Bahnschrift', 'Calibri'), pal('l08', 'Royal Plum', '#3E1F5C', '#62388F', '#F0A6CA', 'Constantia', 'Corbel'),
    pal('l09', 'Harbour Blue', '#0F3557', '#2A6F97', '#A9D6E5', 'Calibri Light', 'Calibri'), pal('l10', 'Terracotta Ink', '#7A3B26', '#A65A3C', '#2A5D67', 'Book Antiqua', 'Gill Sans MT'),
    pal('l11', 'Midnight Lime', '#111827', '#1F2937', '#A3E635', 'Aptos Display', 'Aptos'), pal('l12', 'Classic Blue', '#1F3864', '#2E74B5', '#C55A11', 'Times New Roman', 'Calibri'),
    pal('l13', 'Slate Rose', '#3B4252', '#5E6779', '#E5989B', 'Gill Sans MT', 'Gill Sans MT'), pal('l14', 'Emerald Gold', '#0B4F3C', '#17795C', '#E9C46A', 'Bodoni MT', 'Georgia'),
    pal('l15', 'Ocean Coral', '#0B4F6C', '#1B7FA6', '#FF7F51', 'Trebuchet MS', 'Calibri'), pal('l16', 'Charcoal Blue', '#262B33', '#3D4452', '#4C9AFF', 'Segoe UI', 'Segoe UI'),
    pal('l17', 'Burnt Orange', '#8C3B16', '#B85C2B', '#264653', 'Rockwell', 'Calibri'), pal('l18', 'Violet Mint', '#3C1F7B', '#5E3BB5', '#5CE1B9', 'Century Gothic', 'Calibri'),
    pal('l19', 'Steel Gold', '#2C3E50', '#4A6278', '#D4AC0D', 'Cambria', 'Cambria'), pal('l20', 'Rose Navy', '#1D2D50', '#133B5C', '#FC5185', 'Tw Cen MT', 'Calibri')
  ];
  LP.forEach(function (x) { D.THEMES.push(x); });
  lcoll('lm', [
    ['letter', rot(LALL, 2, 17), ['plain']], ['formal_letter', rot(LALL, 9, 20), ['plain']], ['email_draft', rot(LALL, 15, 20), ['plain']],
    ['memo', rot(LALL, 4, 17)], ['press', rot(LALL, 11, 17)], ['newsletter', rot(LALL, 19, 17)]
  ], LP, ['bar', 'underline', 'overline', 'caps', 'boxed', 'double', 'tag', 'ruletop'], 'letters');

  /* ---- Meetings & Planning and Finance collections ---- */
  var MP = P.concat(LP).concat(KP), FP = LP.concat(P.slice(10)).concat(KP.slice(4));
  lcoll('mp', [['agenda', rot(LALL, 3, 18)], ['minutes', rot(LALL, 10, 18)], ['todo', rot(LALL, 17, 18)], ['project_timeline', rot(LALL, 7, 18)]], MP.slice(5), ['bar', 'underline', 'overline', 'caps', 'boxed', 'double', 'tag', 'ruletop'], 'meetings');
  lcoll('fi', [['quote', rot(LALL, 1, 18), ['plain']], ['invoice', rot(LALL, 8, 18), ['plain']], ['budget_plan', rot(LALL, 14, 18)], ['expense', rot(LALL, 20, 17), ['plain']]], FP, ['bar', 'underline', 'overline', 'caps', 'boxed', 'double', 'tag', 'ruletop'], 'finance');
  coll({ code: 'mp', key: 'km_', coll: 'meetings', P: MP.slice(11), subs: [['project_plan', ['chevron', 'stairs', 'gridblock', 'swiss', 'databars', 'orbits', 'capsules', 'band', 'tiles', 'bauhaus', 'contour', 'split2', 'hexes', 'ribbon', 'plus', 'halftone', 'compose', 'monolith']]], pages: PAGES, heads: HEADS, names: KN.slice(20) });
  coll({ code: 'fi', key: 'kf_', coll: 'finance', P: FP.slice(7), subs: [['financial_report', ['donut', 'columns', 'databars', 'stairs', 'swiss', 'skyline', 'glow', 'band', 'lattice', 'mosaic', 'dome', 'monolith', 'spine', 'inset', 'plot', 'rays', 'shards', 'overlap']]], pages: PAGES, heads: HEADS, names: NAMES.slice(40) });

  coll({ code: 'el', key: 'ke_', coll: 'education', P: EP, subs: ESUBS, pages: EPAGES, heads: ['overline', 'tag', 'boxed', 'number', 'bar', 'caps', 'underline', 'double', 'plain', 'ruletop'], names: ENAMES });
})();

D.design = function (id) { for (var i = 0; i < D.DESIGNS.length; i++) if (D.DESIGNS[i].id === id) return D.DESIGNS[i]; return D.DESIGNS[0]; };
D.designsFor = function (type) { return D.DESIGNS.filter(function (d) { return d.types.indexOf(type) >= 0; }); };

/** The colours/fonts in use: the design's own theme, or a chosen theme. */
D.look = function (doc) {
  var dz = D.design(doc.tpl);
  var t = D.clone(D.theme(doc.theme || dz.theme));
  if (doc.fonts && doc.fonts.head) t.head = doc.fonts.head;
  if (doc.fonts && doc.fonts.body) t.body = doc.fonts.body;
  return t;
};
D.size = function (doc) {
  var dz = D.design(doc.tpl), ok = dz.sizes || D.sizesFor(doc.type || dz.types[0]), key = ok.indexOf(doc.size) >= 0 ? doc.size : ok[0], s = D.clone(D.SIZES[key] || D.SIZES.A4);
  s.key = key;
  if (dz.orient === 'landscape') { s = { w: s.h, h: s.w, tw: s.th, th: s.tw, label: s.label, landscape: true, key: key, screen: s.screen }; }
  return s;
};
/* ---------- sample content (what a template shows before the user writes anything) ---------- */
function B(t, o) { var b = { t: t, id: D.uid() }; for (var k in o || {}) b[k] = o[k]; return b; }
D.B = B;

var LOREM = {
  intro: 'This document sets out where we are today, what we learned over the last quarter and the three priorities we recommend for the next one. Each section ends with a short list of actions so it is easy to see who does what next.',
  p2: 'Customer demand stayed strong through the period, while delivery times improved after the new scheduling process went live. The main risk ahead is hiring: two key roles are still open and the team is working at full capacity.',
  p3: 'We tested three approaches with a small group of customers before choosing one. The option we recommend costs less to run, can be rolled out in stages and gives us clear numbers to judge it by after ninety days.'
};

D.sample = function (type) {
  var y = new Date().getFullYear(), today = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
  var doc = { v: 1, type: type, title: '', subtitle: '', kicker: '', author: 'Your Name', org: 'Your Company', date: today, blocks: [] };
  var b = doc.blocks;
  function body() {
    b.push(B('h1', { text: 'Executive summary' }), B('p', { text: LOREM.intro }),
      B('stats', { items: [{ v: '38%', l: 'Growth in repeat customers' }, { v: '4.6/5', l: 'Average customer rating' }, { v: '12 days', l: 'Faster delivery' }] }),
      B('p', { text: LOREM.p2 }),
      B('callout', { tone: 'key', title: 'Key recommendation', text: 'Invest in two senior hires this quarter and move routine reporting to the new dashboard so the team can focus on customers.' }),
      B('h1', { text: 'Where we are today' }), B('p', { text: LOREM.p3 }),
      B('h2', { text: 'What is working' }), B('ul', { items: ['Shorter delivery times after the scheduling change', '**Higher satisfaction** among repeat customers', 'Lower support costs per order'] }),
      B('h2', { text: 'What needs attention' }), B('ul', { items: ['Two open senior roles', 'Manual weekly reporting', 'Slow onboarding for new partners'] }),
      B('image', { q: 'team meeting office', cap: 'The operations team reviewing the new process', w: 1 }),
      B('h1', { text: 'Plan for the next quarter' }),
      B('timeline', { items: [{ d: 'Month 1', title: 'Hire and onboard', text: 'Fill both senior roles and run a two-week onboarding.' }, { d: 'Month 2', title: 'Automate reporting', text: 'Move weekly reports to the shared dashboard.' }, { d: 'Month 3', title: 'Review results', text: 'Compare against the targets below and decide next steps.' }] }),
      B('table', { head: ['Goal', 'Owner', 'Target', 'Due'], rows: [['Repeat customers', 'Sales', '+10%', 'Q3'], ['Delivery time', 'Operations', 'Under 5 days', 'Q3'], ['Support cost', 'Service', '-15%', 'Q4']] }),
      B('quote', { text: 'Small, steady improvements every week add up to a very different business by the end of the year.', by: 'Team lead' }),
      B('h1', { text: 'Next steps' }), B('ol', { items: ['Approve the hiring budget', 'Choose the dashboard owner', 'Book the 90-day review'] }));
  }
  switch (type) {
    case 'proposal':
      doc.title = 'Project Proposal'; doc.subtitle = 'A clear plan to launch our customer portal in 12 weeks'; doc.kicker = 'Proposal'; body(); break;
    case 'business_plan':
      doc.title = 'Business Plan ' + y; doc.subtitle = 'Growth strategy, market and financial outlook'; doc.kicker = 'Business plan'; body(); break;
    case 'case_study':
      doc.title = 'Case Study'; doc.subtitle = 'How a regional retailer cut delivery times by 40%'; doc.kicker = 'Case study'; body(); break;
    case 'whitepaper':
      doc.title = 'White Paper'; doc.subtitle = 'Practical steps to adopt AI in small teams'; doc.kicker = 'White paper'; body(); break;
    case 'sow':
      doc.title = 'Statement of Work'; doc.subtitle = 'Website redesign project'; doc.kicker = 'Statement of work'; body(); break;
    case 'ebook':
      doc.title = 'The Practical Guide'; doc.subtitle = 'Everything you need to get started, step by step'; doc.kicker = 'eBook'; body(); break;
    case 'research':
      doc.title = 'Effects of Remote Work on Team Productivity'; doc.subtitle = 'A study of 120 small teams'; doc.kicker = 'Research paper';
      b.push(B('h1', { text: 'Abstract' }), B('p', { text: 'This paper looks at how remote and hybrid work affect the output of small teams. Using survey data and weekly task logs, we find that clear goals and short daily check-ins matter more than where people work.' }),
        B('h1', { text: 'Introduction' }), B('p', { text: LOREM.p2 }), B('h1', { text: 'Method' }), B('p', { text: LOREM.p3 }),
        B('table', { head: ['Group', 'Teams', 'Avg. size', 'Weeks'], rows: [['Remote', '42', '6.1', '12'], ['Hybrid', '51', '7.4', '12'], ['Office', '27', '6.8', '12']] }),
        B('h1', { text: 'Results' }), B('p', { text: LOREM.intro }), B('h1', { text: 'Conclusion' }), B('p', { text: 'Teams with written weekly goals were more productive in every setting. Managers should focus on clear priorities rather than location.' }),
        B('h1', { text: 'References' }), B('ol', { items: ['Author, A. (2024). Title of the work. Publisher.', 'Author, B. & Author, C. (2023). Article title. Journal Name, 12(3), 45–67.'] })); break;
    case 'essay':
      doc.title = 'Why Small Habits Matter'; doc.subtitle = 'An essay'; doc.kicker = 'Article';
      b.push(B('p', { text: LOREM.intro }), B('h2', { text: 'Starting small' }), B('p', { text: LOREM.p2 }), B('quote', { text: 'We become what we repeatedly do.', by: 'Common saying' }), B('h2', { text: 'Keeping going' }), B('p', { text: LOREM.p3 })); break;
    case 'student_report':
      doc.title = 'The Water Cycle'; doc.subtitle = 'Science project report'; doc.kicker = 'Student report'; doc.author = 'Student Name'; doc.org = 'Class 8 · Science';
      b.push(B('h1', { text: 'Introduction' }), B('p', { text: 'Water moves between the sea, the sky and the land in a never-ending loop called the water cycle. This report explains each stage and why it matters.' }),
        B('h1', { text: 'The four stages' }), B('cols', { items: [{ title: 'Evaporation', text: 'The sun heats water and turns it into vapour.' }, { title: 'Condensation', text: 'Vapour cools and forms clouds.' }] }),
        B('cols', { items: [{ title: 'Precipitation', text: 'Water falls as rain, snow or hail.' }, { title: 'Collection', text: 'Water gathers in rivers, lakes and seas.' }] }),
        B('h1', { text: 'Conclusion' }), B('p', { text: 'Without the water cycle there would be no fresh water for plants, animals or people.' })); break;
    case 'lesson':
      doc.title = 'Lesson Plan: Fractions'; doc.subtitle = 'Grade 5 · Mathematics · 45 minutes'; doc.kicker = 'Lesson plan';
      b.push(B('kv', { rows: [['Subject', 'Mathematics'], ['Class', 'Grade 5'], ['Duration', '45 minutes'], ['Teacher', 'Your Name']] }),
        B('h1', { text: 'Learning goals' }), B('ul', { items: ['Understand what a fraction is', 'Compare simple fractions', 'Add fractions with the same denominator'] }),
        B('h1', { text: 'Lesson steps' }), B('timeline', { items: [{ d: '10 min', title: 'Warm-up', text: 'Share a pizza drawing and ask how to split it fairly.' }, { d: '20 min', title: 'Main activity', text: 'Pairs fold paper strips into halves, quarters and eighths.' }, { d: '15 min', title: 'Practice & review', text: 'Worksheet, then three students explain their answers.' }] }),
        B('callout', { tone: 'tip', title: 'Homework', text: 'Find three examples of fractions at home and draw them.' })); break;
    case 'newsletter':
      doc.title = 'The Monthly Update'; doc.subtitle = 'News, wins and what is coming up'; doc.kicker = 'Issue 12 · ' + today;
      b.push(B('h1', { text: 'A record month for the team' }), B('p', { text: LOREM.intro }), B('image', { q: 'happy team celebration', cap: 'Celebrating the launch', w: 1 }),
        B('h1', { text: 'Spotlight' }), B('cols', { items: [{ title: 'New starters', text: 'Please welcome Asha and Daniel to the design team.' }, { title: 'Coming up', text: 'Town hall on the 18th and the summer outing on the 30th.' }] }),
        B('h1', { text: 'Tips of the month' }), B('ul', { items: ['Book meeting rooms a day ahead', 'Use the shared calendar for leave', 'Share wins in the team channel'] })); break;
    case 'press':
      doc.title = 'Press Release'; doc.subtitle = 'Company launches new app to help small shops sell online'; doc.kicker = 'For immediate release';
      b.push(B('p', { text: '**City, ' + today + '** — ' + LOREM.intro }), B('p', { text: LOREM.p3 }), B('quote', { text: 'Our goal is to give every small shop the same tools as the big brands.', by: 'Founder & CEO' }),
        B('h2', { text: 'About the company' }), B('p', { text: 'Your Company builds simple software for small businesses. Founded in 2019, it serves customers in 12 countries.' }),
        B('h2', { text: 'Media contact' }), B('p', { text: 'Name · press@example.com · +1 555 0100' })); break;
    case 'letter': case 'cover_letter':
      doc.title = type === 'letter' ? 'Business letter' : 'Cover letter';
      b.push(B('lh', { name: 'Your Name', lines: ['Your Company', '123 Main Street, City', 'you@example.com · +1 555 0100'] }), B('p', { text: today }),
        B('addr', { lines: ['Recipient Name', 'Job Title', 'Company Name', 'Street Address, City'] }),
        B('p', { text: type === 'letter' ? '**Subject: Proposal for the new office move**' : '**Re: Application for the Marketing Manager role**' }), B('p', { text: 'Dear Ms. Recipient,' }),
        B('p', { text: type === 'letter' ? 'Thank you for meeting with us last week. As promised, this letter confirms the plan we discussed and the next steps for the move.' : 'I am writing to apply for the Marketing Manager role. I have six years of experience growing brands online and leading small creative teams.' }),
        B('p', { text: LOREM.p3 }), B('p', { text: 'I would be glad to discuss this further at a time that suits you.' }), B('sign', { close: 'Yours sincerely,', name: 'Your Name', role: 'Your Title' })); break;
    case 'project_plan':
      doc.title = 'Project Plan'; doc.subtitle = 'Customer portal launch · scope, timeline and team'; doc.kicker = 'Project plan';
      b.push(B('h1', { text: 'Project overview' }), B('p', { text: 'This plan sets out how we will build and launch the new customer portal: what is in scope, who does what, the timeline and how we will measure success.' }),
        B('kv', { rows: [['Project lead', 'Your Name'], ['Start date', today], ['Target launch', 'In 12 weeks'], ['Budget', '$85,000']] }),
        B('h1', { text: 'Goals' }), B('ol', { items: ['Let customers book and pay online', 'Cut support calls by a quarter', 'Launch on time and on budget'] }),
        B('h1', { text: 'Scope' }), B('cols', { items: [{ title: 'In scope', text: 'Booking, payments, account page, email alerts' }, { title: 'Out of scope', text: 'Mobile app, loyalty scheme, new branding' }] }),
        B('h1', { text: 'Timeline' }), B('timeline', { items: [{ d: 'Weeks 1–2', title: 'Discovery', text: 'Interviews, requirements and plan sign-off.' }, { d: 'Weeks 3–6', title: 'Design', text: 'Wireframes, visual design and testing.' }, { d: 'Weeks 7–10', title: 'Build', text: 'Development and integration.' }, { d: 'Weeks 11–12', title: 'Launch', text: 'Final tests, training and go-live.' }] }),
        B('h1', { text: 'Team and roles' }), B('table', { head: ['Role', 'Name', 'Responsible for'], rows: [['Project lead', 'Your Name', 'Plan, budget, reporting'], ['Designer', 'Team member', 'User experience and visuals'], ['Developer', 'Team member', 'Build and testing']] }),
        B('h1', { text: 'Risks' }), B('table', { head: ['Risk', 'Impact', 'Plan'], rows: [['Late content', 'Medium', 'Agree a content deadline in week 2'], ['Payment set-up delays', 'High', 'Start the provider set-up in week 1']] })); break;
    case 'project_timeline':
      doc.title = 'Project Timeline';
      b.push(B('title', { text: 'Project Timeline', sub: 'Customer portal launch · 12 weeks' }),
        B('timeline', { items: [{ d: 'Week 1', title: 'Kick-off', text: 'Meet the team, agree goals and plan.' }, { d: 'Week 2', title: 'Requirements', text: 'Interviews and requirement sign-off.' }, { d: 'Week 4', title: 'Design review', text: 'Wireframes and visual design approved.' }, { d: 'Week 8', title: 'Build complete', text: 'All features built and tested.' }, { d: 'Week 11', title: 'User testing', text: 'Pilot with 20 customers.' }, { d: 'Week 12', title: 'Launch', text: 'Go live and handover.' }] }),
        B('h2', { text: 'Milestones' }), B('table', { head: ['Milestone', 'Owner', 'Due', 'Status'], rows: [['Plan signed off', 'Project lead', 'Week 2', 'Done'], ['Design approved', 'Designer', 'Week 4', 'In progress'], ['Launch', 'Team', 'Week 12', 'Planned']] })); break;
    case 'budget_plan':
      doc.title = 'Budget Plan';
      b.push(B('title', { text: 'Budget Plan ' + y, sub: 'Marketing department · January to December' }),
        B('stats', { items: [{ v: '$120,000', l: 'Total budget' }, { v: '$10,000', l: 'Per month' }, { v: '8%', l: 'Kept in reserve' }] }),
        B('table', { head: ['Area', 'Q1', 'Q2', 'Q3', 'Q4', 'Total'], rows: [['Advertising', '$9,000', '$9,000', '$10,000', '$12,000', '$40,000'], ['Events', '$4,000', '$6,000', '$4,000', '$6,000', '$20,000'], ['Content', '$5,000', '$5,000', '$5,000', '$5,000', '$20,000'], ['Tools', '$2,500', '$2,500', '$2,500', '$2,500', '$10,000'], ['Team training', '$5,000', '$5,000', '$5,000', '$5,000', '$20,000']] }),
        B('chart', { kind: 'doughnut', title: 'Where the money goes', labels: ['Advertising', 'Events', 'Content', 'Tools', 'Training'], series: [{ name: 'Budget', values: [40, 20, 20, 10, 20] }], legend: true, vals: true, unit: '%' }),
        B('callout', { tone: 'key', title: 'Approval', text: 'Please approve this budget by the end of the month so spending can start in January.' })); break;
    case 'financial_report':
      doc.title = 'Financial Report'; doc.subtitle = 'Results for the year ended 31 March ' + y; doc.kicker = 'Financial report';
      b.push(B('h1', { text: 'Summary' }), B('p', { text: 'Revenue grew steadily through the year, costs stayed within budget and the business ended the year with a healthy cash position.' }),
        B('stats', { items: [{ v: '$48.2M', l: 'Revenue' }, { v: '$9.1M', l: 'Operating profit' }, { v: '18.9%', l: 'Operating margin' }, { v: '$12.4M', l: 'Cash at year end' }] }),
        B('h1', { text: 'Income statement' }), B('table', { head: ['', 'This year', 'Last year', 'Change'], rows: [['Revenue', '$48.2M', '$41.5M', '+16%'], ['Cost of sales', '$26.1M', '$23.0M', '+13%'], ['Gross profit', '$22.1M', '$18.5M', '+19%'], ['Operating costs', '$13.0M', '$10.9M', '+19%'], ['Operating profit', '$9.1M', '$7.6M', '+20%']] }),
        B('chart', { kind: 'line', title: 'Revenue by quarter ($M)', labels: ['Q1', 'Q2', 'Q3', 'Q4'], series: [{ name: 'This year', values: [10.8, 11.6, 12.3, 13.5] }, { name: 'Last year', values: [9.6, 10.1, 10.6, 11.2] }], legend: true, vals: false, unit: '' }),
        B('h1', { text: 'Balance sheet highlights' }), B('kv', { rows: [['Total assets', '$62.0M'], ['Total liabilities', '$21.4M'], ['Equity', '$40.6M'], ['Debt to equity', '0.18']] }),
        B('h1', { text: 'Outlook' }), B('p', { text: LOREM.p3 })); break;
    case 'formal_letter':
      doc.title = 'Formal letter';
      b.push(B('lh', { name: 'Your Name', lines: ['Your Address Line 1', 'City, Postcode', 'you@example.com · +00 123 456 789'] }), B('p', { text: today }),
        B('addr', { lines: ['The Manager', 'Department Name', 'Organisation Name', 'Street Address, City'] }),
        B('p', { text: '**Subject: Request for a copy of my records**' }), B('p', { text: 'Dear Sir or Madam,' }),
        B('p', { text: 'I am writing to formally request a copy of the records held about my account. I would be grateful if you could send them to the address above within the usual time.' }),
        B('p', { text: 'Please let me know if you need any further details or documents from me to process this request.' }),
        B('p', { text: 'Thank you for your attention to this matter.' }), B('sign', { close: 'Yours faithfully,', name: 'Your Name', role: '' })); break;
    case 'email_draft':
      doc.title = 'Email draft';
      b.push(B('title', { text: 'Email draft', sub: 'Project update for the client team' }),
        B('kv', { rows: [['To', 'client@company.com'], ['Cc', 'team@yourcompany.com'], ['Subject', 'Project update: phase one complete']] }),
        B('p', { text: 'Hi Priya,' }), B('p', { text: 'Good news: phase one of the project is complete and the new booking page is now live for testing.' }),
        B('ul', { items: ['The booking page is ready for your team to try', 'We fixed the two issues from last week', 'Phase two starts on Monday'] }),
        B('p', { text: 'Could you share your feedback by Thursday so we can include it before launch?' }), B('p', { text: 'Thanks,' }), B('p', { text: 'Your Name' })); break;
    case 'professional_bio':
      doc.title = 'Professional Biography';
      b.push(B('title', { text: 'Alex Morgan', sub: 'Product Designer · Bengaluru · alex@example.com' }),
        B('p', { text: '**Alex Morgan** is a product designer with seven years of experience creating simple, friendly digital products. Alex has led design for apps used by more than two million people and works closely with engineers, researchers and business teams.' }),
        B('h1', { text: 'Experience' }), B('p', { text: 'Alex is a Senior Product Designer at Brightline Apps, where Alex leads the redesign of the main app and the company design system. Before that, Alex designed web and mobile products for more than twenty clients at Northwind Studio.' }),
        B('h1', { text: 'Expertise' }), B('ul', { items: ['User research and testing', 'Design systems for large teams', 'Mobile and web product design', 'Workshops and design training'] }),
        B('h1', { text: 'Education and recognition' }), B('p', { text: 'Alex holds a B.Des. in Interaction Design and speaks regularly at local design meetups.' }),
        B('quote', { text: 'Good design is the shortest path between a person and what they want to do.', by: 'Alex Morgan' })); break;
    case 'job_description':
      doc.title = 'Job Description';
      b.push(B('title', { text: 'Marketing Manager', sub: 'Full time · Bengaluru (hybrid) · Marketing team' }),
        B('kv', { rows: [['Reports to', 'Head of Marketing'], ['Location', 'Bengaluru, hybrid'], ['Type', 'Full time, permanent'], ['Salary', 'Competitive, based on experience']] }),
        B('h1', { text: 'About the role' }), B('p', { text: 'We are looking for a Marketing Manager to plan and run campaigns that bring in new customers and keep current ones engaged. You will own the marketing calendar and work with sales, design and product.' }),
        B('h1', { text: 'What you will do' }), B('ul', { items: ['Plan and run campaigns across email, social and events', 'Manage the marketing budget and report results each month', 'Work with design on brochures, ads and web pages', 'Lead a small team of two marketing executives'] }),
        B('h1', { text: 'What you bring' }), B('ul', { items: ['4+ years of experience in marketing', 'Clear writing and good presentation skills', 'Comfort with data and marketing tools', 'Experience managing a small team'] }),
        B('h1', { text: 'What we offer' }), B('cols', { items: [{ title: 'Growth', text: 'Training budget and clear career steps.' }, { title: 'Balance', text: 'Hybrid work and flexible hours.' }, { title: 'Benefits', text: 'Health cover and paid time off.' }] }),
        B('callout', { tone: 'info', title: 'How to apply', text: 'Send your CV and a short cover letter to careers@yourcompany.com.' })); break;
    case 'memo':
      doc.title = 'Memo';
      b.push(B('title', { text: 'Memo', sub: '' }), B('kv', { rows: [['To', 'All staff'], ['From', 'Your Name, Operations'], ['Date', today], ['Subject', 'New office hours from next month']] }),
        B('p', { text: 'From the first of next month our office will open at 8:30 and close at 17:30. This gives everyone a quieter hour in the morning and keeps evenings free.' }),
        B('ul', { items: ['Core hours are 10:00–16:00', 'Reception moves to the ground floor', 'Please update your calendar by Friday'] }), B('p', { text: 'Thank you for your help with the change.' })); break;
    case 'fax':
      doc.title = 'Fax';
      b.push(B('title', { text: 'FAX', sub: 'Your Company · 123 Main Street, City · +1 555 0100' }), B('kv', { rows: [['To', ''], ['Fax', ''], ['From', ''], ['Pages', ''], ['Phone', ''], ['Date', today], ['Re', '']] }),
        B('check', { items: ['Urgent', 'For review', 'Please comment', 'Please reply'] }), B('h2', { text: 'Comments' }), B('p', { text: 'Type your message here.' })); break;
    case 'agenda':
      doc.title = 'Meeting Agenda';
      b.push(B('title', { text: 'Meeting Agenda', sub: 'Quarterly planning · ' + today + ' · 10:00–11:30 · Room 4' }),
        B('kv', { rows: [['Chair', 'Your Name'], ['Note taker', 'Team member'], ['Attendees', 'Sales, Operations, Finance']] }),
        B('table', { head: ['Time', 'Topic', 'Lead'], rows: [['10:00', 'Welcome and goals', 'Chair'], ['10:10', 'Last quarter results', 'Finance'], ['10:40', 'Plans for next quarter', 'All'], ['11:20', 'Actions and close', 'Chair']] }),
        B('callout', { tone: 'info', title: 'Please prepare', text: 'Read the results summary before the meeting and bring one idea for next quarter.' })); break;
    case 'minutes':
      doc.title = 'Meeting Minutes';
      b.push(B('title', { text: 'Meeting Minutes', sub: 'Quarterly planning · ' + today }), B('kv', { rows: [['Present', 'A. Rao, D. Lee, M. Khan, S. Patel'], ['Apologies', 'J. Smith'], ['Chair', 'A. Rao']] }),
        B('h2', { text: '1. Results' }), B('p', { text: 'Finance shared the results. Revenue grew 12% and costs stayed on budget.' }),
        B('h2', { text: '2. Next quarter' }), B('p', { text: 'The team agreed on three priorities: hiring, reporting and partner onboarding.' }),
        B('h2', { text: 'Actions' }), B('table', { head: ['Action', 'Owner', 'Due'], rows: [['Post two job adverts', 'M. Khan', 'Next Friday'], ['Set up dashboard', 'D. Lee', 'End of month'], ['Partner guide draft', 'S. Patel', 'Two weeks']] })); break;
    case 'todo':
      doc.title = 'To-do List';
      b.push(B('title', { text: 'To-do List', sub: 'Week of ' + today }), B('todo', { rows: [{ task: 'Send the proposal', due: 'Mon', pr: 'High' }, { task: 'Book flights', due: 'Tue', pr: 'Medium' }, { task: 'Review budget', due: 'Wed', pr: 'High' }, { task: 'Team lunch', due: 'Fri', pr: 'Low' }, { task: '', due: '', pr: '' }, { task: '', due: '', pr: '' }, { task: '', due: '', pr: '' }, { task: '', due: '', pr: '' }] }),
        B('h2', { text: 'Notes' }), B('p', { text: '' })); break;
    case 'planner':
      doc.title = 'Weekly Planner';
      b.push(B('title', { text: 'Weekly Planner', sub: 'Week of ' + today }), B('week', { days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }),
        B('h2', { text: 'Top priorities' }), B('check', { items: ['', '', ''] })); break;
    case 'calendar':
      var now = new Date(); doc.title = 'Calendar';
      b.push(B('title', { text: now.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' }), sub: 'Plans, deadlines and events' }), B('cal', { y: now.getFullYear(), m: now.getMonth() }), B('h2', { text: 'Notes' }), B('p', { text: '' })); break;
    case 'invoice': case 'quote':
      doc.title = type === 'invoice' ? 'Invoice' : 'Quote';
      b.push(B('title', { text: type === 'invoice' ? 'INVOICE' : 'QUOTE', sub: (type === 'invoice' ? 'Invoice' : 'Quote') + ' #1042 · ' + today }),
        B('cols', { items: [{ title: 'From', text: 'Your Company\n123 Main Street, City\nyou@example.com' }, { title: type === 'invoice' ? 'Bill to' : 'Prepared for', text: 'Client Name\nClient Company\nclient@example.com' }] }),
        B('inv', { cur: '$', tax: 10, rows: [{ d: 'Website design', q: 1, r: 1200 }, { d: 'Content writing (pages)', q: 6, r: 80 }, { d: 'Hosting setup', q: 1, r: 150 }] }),
        B('callout', { tone: 'info', title: type === 'invoice' ? 'Payment terms' : 'Valid for', text: type === 'invoice' ? 'Please pay within 15 days. Bank: Your Bank · Account 0000 0000 · IFSC/Sort code 000000' : 'This quote is valid for 30 days from the date above.' }),
        B('p', { text: 'Thank you for your business!' })); break;
    case 'expense':
      doc.title = 'Expense Report';
      b.push(B('title', { text: 'Expense Report', sub: 'Your Name · Sales · ' + today }),
        B('table', { head: ['Date', 'Description', 'Category', 'Amount'], rows: [['02 Mar', 'Train to client site', 'Travel', '$48.00'], ['02 Mar', 'Lunch with client', 'Meals', '$36.50'], ['05 Mar', 'Printing brochures', 'Office', '$72.00'], ['', '', '**Total**', '**$156.50**']] }),
        B('sigs', { items: [{ name: 'Employee', role: 'Signature & date' }, { name: 'Manager', role: 'Approved by' }] })); break;
    case 'resume':
      doc.title = 'Resume';
      b.push(B('r_head', { name: 'Alex Morgan', role: 'Product Designer', contacts: ['alex@example.com', '+1 555 0100', 'City, Country', 'portfolio.example.com'] }),
        B('r_sec', { text: 'Profile' }), B('p', { text: 'Product designer with 7 years of experience turning complex problems into simple, friendly products. Led design for apps used by more than 2 million people.' }),
        B('r_sec', { text: 'Experience' }),
        B('r_entry', { title: 'Senior Product Designer', org: 'Brightline Apps', date: '2021 – Present', items: ['Led the redesign of the main app; sign-ups rose 32%', 'Built the company design system used by 5 teams'] }),
        B('r_entry', { title: 'Product Designer', org: 'Northwind Studio', date: '2018 – 2021', items: ['Designed web and mobile apps for 20+ clients', 'Ran user tests and shared findings with clients'] }),
        B('r_sec', { text: 'Education' }), B('r_entry', { title: 'B.Des. in Interaction Design', org: 'National Institute of Design', date: '2014 – 2018', items: [] }),
        B('r_sec', { text: 'Skills', side: 1 }), B('r_skills', { side: 1, items: ['User research', 'Wireframing', 'Prototyping', 'Design systems', 'Figma', 'HTML & CSS'] }),
        B('r_sec', { text: 'Languages', side: 1 }), B('r_skills', { side: 1, items: ['English', 'Hindi', 'Kannada'] })); break;
    case 'flyer':
      doc.title = 'Flyer';
      b.push(B('s_kicker', { text: 'Saturday · 14 June' }), B('s_title', { text: 'Summer Market' }), B('s_sub', { text: 'Local food, music and makers' }),
        B('s_text', { text: 'Join us for a day of fresh food, live music and more than 40 local makers. Free entry for everyone.' }),
        B('s_details', { rows: [['When', '10:00 – 18:00'], ['Where', 'Central Park, Main Gate'], ['Tickets', 'Free']] }), B('s_cta', { text: 'yourwebsite.com' })); break;
    case 'invitation':
      doc.title = 'Invitation';
      b.push(B('s_kicker', { text: 'You are warmly invited to' }), B('s_title', { text: 'An Evening Celebration' }), B('s_sub', { text: 'in honour of our 10th anniversary' }),
        B('s_details', { rows: [['Date', 'Friday, 20 June'], ['Time', '7:00 pm'], ['Venue', 'The Grand Hall, City']] }), B('s_text', { text: 'Kindly reply by 10 June' })); break;
    case 'menu':
      doc.title = 'Menu';
      b.push(B('s_kicker', { text: 'Since 2012' }), B('s_title', { text: 'The Garden Bistro' }),
        B('s_menu', { sections: [{ name: 'Starters', items: [{ n: 'Tomato soup', d: 'Basil, cream, sourdough', p: '6' }, { n: 'Garden salad', d: 'Seasonal greens, lemon dressing', p: '7' }] },
          { name: 'Mains', items: [{ n: 'Wood-fired pizza', d: 'Mozzarella, tomato, basil', p: '12' }, { n: 'Mushroom risotto', d: 'Parmesan, thyme', p: '14' }, { n: 'Grilled paneer', d: 'Spiced vegetables, mint yoghurt', p: '13' }] },
          { name: 'Desserts', items: [{ n: 'Chocolate cake', d: 'Vanilla ice cream', p: '7' }, { n: 'Fruit sorbet', d: 'Three scoops', p: '6' }] }] }),
        B('s_text', { text: 'Please tell us about any allergies.' })); break;
    case 'certificate':
      doc.title = 'Certificate';
      b.push(B('s_cert', { title: 'Certificate', sub: 'of Achievement', pre: 'This certificate is proudly presented to', name: 'Recipient Name', reason: 'for outstanding work and dedication during the ' + y + ' programme.', date: today, sigs: [{ name: 'Name Surname', role: 'Director' }, { name: 'Name Surname', role: 'Programme Lead' }] })); break;
    case 'tickets':
      doc.title = 'Tickets';
      b.push(B('s_tickets', { event: 'Spring Concert', sub: 'Admit one', date: 'Sat 12 April · 7 pm', place: 'City Hall', price: '$15', n: 8, start: 1 })); break;
    case 'card':
      doc.title = 'Thank-you card';
      b.push(B('s_title', { text: 'Thank you' }), B('s_text', { text: 'Thank you so much for your kindness and support. It meant more than words can say.' }), B('s_sub', { text: 'With love, Your Name' })); break;
    case 'study_guide':
      doc.title = 'Study Guide: Cell Biology'; doc.subtitle = 'Key ideas, terms and practice questions'; doc.kicker = 'Study guide'; doc.author = 'Student Name'; doc.org = 'Biology · Unit 3';
      b.push(B('callout', { tone: 'info', title: 'What you will learn', text: 'The parts of a cell, what each part does and how cells divide.' }),
        B('h1', { text: 'Key ideas' }), B('ul', { items: ['All living things are made of cells', 'Cells have parts called **organelles**, each with a job', 'Cells make new cells by dividing'] }),
        B('h1', { text: 'Key terms' }), B('kv', { rows: [['Nucleus', 'Holds the cell’s DNA and controls the cell'], ['Mitochondria', 'Makes energy for the cell'], ['Cell membrane', 'Controls what goes in and out'], ['Cytoplasm', 'Jelly-like fluid where reactions happen']] }),
        B('h1', { text: 'Plant and animal cells' }), B('table', { head: ['Part', 'Plant cell', 'Animal cell'], rows: [['Cell wall', 'Yes', 'No'], ['Chloroplasts', 'Yes', 'No'], ['Nucleus', 'Yes', 'Yes']] }),
        B('h1', { text: 'Practice questions' }), B('ol', { items: ['What is the job of the mitochondria?', 'Name two parts found only in plant cells.', 'Why do cells need to divide?'] }),
        B('callout', { tone: 'tip', title: 'Revision tip', text: 'Draw and label a cell from memory, then check it against your notes.' }),
        B('h1', { text: 'Checklist' }), B('check', { items: ['I can name the main parts of a cell', 'I can explain what each part does', 'I can compare plant and animal cells'] })); break;
    case 'company_profile':
      doc.title = 'Company Profile'; doc.subtitle = 'Who we are, what we do and why clients choose us'; doc.kicker = 'Company profile';
      b.push(B('h1', { text: 'About us' }), B('p', { text: 'We are a team of specialists who help growing businesses plan, build and run better services. This profile explains what we do, how we work and the results our clients see.' }),
        B('cols', { items: [{ title: 'Our mission', text: 'Make good service simple for every client we work with.' }, { title: 'Our vision', text: 'Be the partner growing companies trust first.' }] }),
        B('stats', { items: [{ v: '2009', l: 'Founded' }, { v: '1,200+', l: 'Clients served' }, { v: '85', l: 'Team members' }] }),
        B('h1', { text: 'What we do' }), B('ul', { items: ['**Consulting** – plans that fit your goals and budget', '**Delivery** – projects run on time with clear updates', '**Support** – a team that answers when you need it'] }),
        B('image', { q: 'modern office team collaboration', cap: 'Our team at work', w: 1 }),
        B('h1', { text: 'How we work' }), B('timeline', { items: [{ d: 'Step 1', title: 'Listen', text: 'We learn your goals and constraints.' }, { d: 'Step 2', title: 'Plan', text: 'We agree a clear plan, budget and timeline.' }, { d: 'Step 3', title: 'Deliver', text: 'We build, test and hand over with training.' }] }),
        B('h1', { text: 'Why clients choose us' }), B('quote', { text: 'They understood what we needed and delivered exactly that.', by: 'Client, retail group' }),
        B('h1', { text: 'Contact' }), B('kv', { rows: [['Address', '123 Business Avenue, Your City'], ['Phone', '+00 123 456 789'], ['Email', 'hello@yourcompany.com'], ['Website', 'www.yourcompany.com']] })); break;
    case 'annual_report':
      doc.title = 'Annual Report ' + y; doc.subtitle = 'Performance, highlights and outlook'; doc.kicker = 'Annual report';
      b.push(B('h1', { text: 'Message from the leadership' }), B('p', { text: LOREM.intro }), B('p', { text: LOREM.p2 }),
        B('h1', { text: 'Year at a glance' }), B('stats', { items: [{ v: '$48.2M', l: 'Revenue' }, { v: '12,400', l: 'Customers' }, { v: '860', l: 'Employees' }, { v: '14', l: 'Locations' }] }),
        B('chart', { kind: 'column', title: 'Revenue by quarter', labels: ['Q1', 'Q2', 'Q3', 'Q4'], series: [{ name: 'Revenue', values: [12, 15, 14, 19] }], legend: false, vals: true, unit: '' }),
        B('h1', { text: 'Highlights of the year' }), B('timeline', { items: [{ d: 'Q1', title: 'New market', text: 'Opened our first office in a new region.' }, { d: 'Q2', title: 'Product launch', text: 'Released the new customer portal.' }, { d: 'Q3', title: 'Partnership', text: 'Signed a long-term partner agreement.' }, { d: 'Q4', title: 'Recognition', text: 'Named a top employer in our sector.' }] }),
        B('h1', { text: 'Financial summary' }), B('table', { head: ['', 'This year', 'Last year', 'Change'], rows: [['Revenue', '$48.2M', '$41.5M', '+16%'], ['Operating profit', '$9.1M', '$7.6M', '+20%'], ['Net profit', '$6.4M', '$5.2M', '+23%']] }),
        B('h1', { text: 'Looking ahead' }), B('p', { text: LOREM.p3 }), B('ol', { items: ['Grow in our two strongest markets', 'Invest in people and training', 'Keep improving customer service'] })); break;
    default:
      doc.title = 'Annual Report ' + y; doc.subtitle = 'Results, highlights and plans for the year ahead'; doc.kicker = 'Report'; body();
  }
  return doc;
};
/* ---------- HTML rendering (preview, editor, add-in) ---------- */
D.CSS = [
  '.dsd-doc{position:relative;display:flex;flex-direction:column;align-items:center;gap:28px;--gap:28px}',
  '.dsd-page{position:relative;background:#fff;box-shadow:0 2px 14px rgba(15,23,42,.18);overflow:hidden;flex:none}',
  '.dsd-art{position:absolute;inset:0;background-size:100% 100%;background-repeat:no-repeat;pointer-events:none}',
  '.dsd-flow{position:relative;flex:none}',
  '.dsd-sheets .dsd-page{position:absolute;left:0}',
  '.dsd-col{position:absolute;top:0}',
  '.dsd-col>*{position:relative}',
  '.dsd-spacer{height:0;pointer-events:none}',
  '.dsd-col{pointer-events:none}.dsd-col>*{pointer-events:auto}',
  '.dsd-lay{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none}.dsd-lay-f{z-index:3}',
  '.dsd-L{position:absolute;transform-origin:50% 50%}.dsd-L svg{display:block}',
  '.dsd-edit .dsd-L{pointer-events:auto;cursor:move}.dsd-edit .dsd-L.lock{pointer-events:none}',
  '.dsd-edit .dsd-L .dsd-Ltx[contenteditable=true]{cursor:text;outline:none}',
  '.dsd-chart{margin:10pt auto}.dsd-chart svg{display:block;width:100%;height:auto}.dsd-chart figcaption{font-size:8.5pt;color:var(--mute);font-style:italic;margin-top:4pt;text-align:center}',
  '.dsd-run{position:absolute;font-size:8.5pt;letter-spacing:.06em;text-transform:uppercase;color:var(--mute);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}',
  '.dsd-pn{position:absolute;font-size:9pt;color:var(--mute)}.dsd-run span,.dsd-pn span{background:rgba(255,255,255,.94);padding:1px 6px;border-radius:2px}',
  '.dsd-doc *{box-sizing:border-box}',
  '.dsd-doc.dsd-doc.dsd-doc.dsd-doc.dsd-doc,.dsd-doc.dsd-doc.dsd-doc.dsd-doc.dsd-doc *{font-family:var(--ff) !important}',
  '.dsd-doc{color:var(--ink);font-family:var(--bodyf);--ff:var(--bodyf);font-size:var(--bs)}',
  '.dsd-b{margin:0 0 8pt 0;line-height:1.38;outline:none}',
  '.dsd-h{font-family:var(--headf);--ff:var(--headf);color:var(--p);line-height:1.15;margin:16pt 0 8pt;font-weight:700;break-after:avoid}',
  '.dsd-h1{font-size:var(--h1s)}.dsd-h2{font-size:var(--h2s);color:var(--st)}.dsd-h3{font-size:11.5pt;color:var(--ink)}',
  '.st-bar .dsd-h1{border-left:5px solid var(--a);padding-left:12px}',
  '.st-underline .dsd-h1{border-bottom:2px solid var(--a);padding-bottom:5px}',
  '.st-ruletop .dsd-h1{border-top:4px solid var(--p);padding-top:6px;text-transform:uppercase;letter-spacing:.02em}',
  '.st-serif .dsd-h1{color:var(--ink)}.st-serif .dsd-blk.hd .dsd-h1{margin-top:16pt}.st-serif .dsd-sn{margin-top:16pt}.st-serif .dsd-h2{color:var(--ink);font-style:italic}',
  '.st-overline .dsd-h1::before{content:"";display:block;width:44px;height:4px;background:var(--a);margin-bottom:9pt}',
  '.st-tag .dsd-blk.hd::after{content:"";display:block;clear:both}.st-tag .dsd-tag{float:left;margin:15pt 10pt 0 0;background:var(--a);color:var(--ona);font-family:var(--headf);--ff:var(--headf);font-weight:700;font-size:calc(var(--h1s) * .6);line-height:1;padding:5pt 7pt;border-radius:3px}',
  '.st-boxed .dsd-h1{background:var(--tint);border-left:4px solid var(--p);padding:7pt 12pt}',
  '.st-double .dsd-h1{border-bottom:4px double var(--a);padding-bottom:6px}',
  '.st-chapter .dsd-h1{text-align:center;font-size:calc(var(--h1s) + 6pt);margin-top:40pt}',
  '.dsd-lab{display:block;font-family:var(--bodyf);--ff:var(--bodyf);font-size:8.5pt;letter-spacing:.18em;text-transform:uppercase;color:var(--at);font-weight:700;margin-bottom:4pt}',
  '.dsd-numl{display:block;font-family:var(--headf);--ff:var(--headf);font-size:26pt;color:var(--at);font-weight:700;line-height:1;margin-bottom:2pt}',
  '.dsd-orn{display:block;text-align:center;color:var(--at);font-size:12pt;margin-top:6pt;letter-spacing:.4em}',
  '.dsd-ul,.dsd-ol,.dsd-ck{padding-left:20pt}.dsd-ul li,.dsd-ol li,.dsd-ck li{margin:0 0 3pt;line-height:1.38}',
  '.dsd-ul li::marker{color:var(--a)}.dsd-ol li::marker{color:var(--a);font-weight:700}',
  '.dsd-ck{list-style:none;padding-left:4pt}.dsd-ck li::before{content:"\\2610";color:var(--p);margin-right:8pt;font-size:12pt}',
  '.dsd-quote{border-left:4px solid var(--a);padding:6pt 0 6pt 16pt;margin:12pt 0;font-family:var(--headf);--ff:var(--headf);font-size:13pt;font-style:italic;color:var(--p)}',
  '.dsd-quote .by{display:block;font-family:var(--bodyf);--ff:var(--bodyf);font-style:normal;font-size:9pt;color:var(--mute);margin-top:4pt;letter-spacing:.06em;text-transform:uppercase}',
  '.dsd-call{background:var(--tint);border-left:5px solid var(--a);padding:10pt 14pt;margin:10pt 0}',
  '.dsd-call .ct{font-weight:700;color:var(--p);margin-bottom:3pt;font-family:var(--headf);--ff:var(--headf)}',
  '.dsd-stats{display:flex;gap:8pt;margin:10pt 0}.dsd-stats>div{flex:1;background:var(--tint);border-top:4px solid var(--a);padding:10pt 10pt 8pt}',
  '.dsd-stats .v{font-family:var(--headf);--ff:var(--headf);font-size:22pt;font-weight:700;color:var(--p);line-height:1.05}.dsd-stats .l{font-size:8.5pt;color:var(--mute);margin-top:3pt}',
  '.dsd-tab{width:100%;border-collapse:collapse;margin:8pt 0 12pt;font-size:calc(var(--bs) - .5pt)}',
  '.dsd-tab th{background:var(--p);color:var(--onp);text-align:left;padding:6pt 8pt;font-weight:700}',
  '.dsd-tab td{padding:6pt 8pt;border-bottom:1px solid var(--line);vertical-align:top}.dsd-tab tr:nth-child(even) td{background:var(--tint)}',
  '.dsd-tl{margin:8pt 0}.dsd-tl .r{display:flex;gap:12pt;margin-bottom:8pt}.dsd-tl .d{width:70pt;flex:none;font-weight:700;color:var(--at);font-size:9pt;padding-top:2pt;text-transform:uppercase;letter-spacing:.04em}',
  '.dsd-tl .c{flex:1;border-left:2px solid var(--line);padding-left:12pt}.dsd-tl .t{font-weight:700;color:var(--p)}',
  '.dsd-cols{display:flex;gap:16pt;margin:8pt 0}.dsd-cols>div{flex:1}.dsd-cols .ct{font-family:var(--headf);--ff:var(--headf);font-weight:700;color:var(--p);font-size:11.5pt;margin-bottom:3pt;border-bottom:2px solid var(--a);padding-bottom:3pt}',
  '.dsd-cols .cx{white-space:pre-line}',
  '.dsd-kv{width:100%;border-collapse:collapse;margin:6pt 0 12pt}.dsd-kv td{padding:5pt 6pt;border-bottom:1px solid var(--line);vertical-align:top}.dsd-kv td.k{width:28%;font-weight:700;color:var(--p);text-transform:uppercase;font-size:8.5pt;letter-spacing:.06em}',
  '.dsd-fig{margin:10pt 0}.dsd-fig img,.dsd-fig .ph{display:block;width:100%;height:auto;border-radius:2px}.dsd-fig .ph{aspect-ratio:16/9;background:var(--tint);border:1.5px dashed var(--line);display:flex;align-items:center;justify-content:center;color:var(--mute);font-size:10pt}',
  '.dsd-fig figcaption{font-size:8.5pt;color:var(--mute);font-style:italic;margin-top:4pt;text-align:center}',
  '.dsd-lh .n{font-family:var(--headf);--ff:var(--headf);font-size:20pt;font-weight:700;color:var(--p);line-height:1.1}.dsd-lh .l{font-size:9pt;color:var(--mute)}',
  '.dsd-title .tt{font-family:var(--headf);--ff:var(--headf);font-size:28pt;font-weight:700;color:var(--p);line-height:1.05;letter-spacing:.01em}.dsd-title .ts{font-size:10pt;color:var(--mute);margin-top:4pt}',
  '.dsd-hb.dark .n,.dsd-hb.dark .tt{color:#fff}.dsd-hb.dark .l,.dsd-hb.dark .ts{color:rgba(255,255,255,.85)}',
  '.dsd-addr{white-space:pre-line}.dsd-sign .gap{height:30pt}.dsd-sign .nm{font-weight:700}.dsd-sign .rl{color:var(--mute)}',
  '.dsd-inv{width:100%;border-collapse:collapse;margin:10pt 0}.dsd-inv th{background:var(--p);color:var(--onp);padding:6pt 8pt;text-align:left}.dsd-inv th.n,.dsd-inv td.n{text-align:right}',
  '.dsd-inv td{padding:6pt 8pt;border-bottom:1px solid var(--line)}.dsd-inv tr.tot td{border:0;padding:3pt 8pt}.dsd-inv tr.grand td{background:var(--a);color:var(--ona);font-weight:700;font-size:12pt}',
  '.dsd-todo{width:100%;border-collapse:collapse;margin:8pt 0}.dsd-todo th{background:var(--p);color:var(--onp);padding:6pt 8pt;text-align:left}.dsd-todo td{padding:8pt;border-bottom:1px solid var(--line);height:28pt}.dsd-todo td.bx{width:26pt;color:var(--p);font-size:13pt;text-align:center}',
  '.dsd-week{width:100%;border-collapse:collapse;margin:8pt 0}.dsd-week td{border:1px solid var(--line);vertical-align:top;padding:6pt 8pt;height:66pt}.dsd-week td.dn{width:26%;background:var(--tint);font-family:var(--headf);--ff:var(--headf);font-weight:700;color:var(--p)}',
  '.dsd-cal{width:100%;border-collapse:collapse;table-layout:fixed;margin:6pt 0}.dsd-cal th{background:var(--p);color:var(--onp);padding:6pt 4pt;font-size:9pt}.dsd-cal td{border:1px solid var(--line);height:78pt;vertical-align:top;padding:4pt 6pt;font-weight:700;color:var(--p)}.dsd-cal td.o{color:#C9CDD3;background:#FAFAFA}',
  '.dsd-sigs{display:flex;gap:30pt;margin-top:30pt}.dsd-sigs>div{flex:1;border-top:1.5px solid var(--ink);padding-top:4pt}.dsd-sigs .nm{font-weight:700}.dsd-sigs .rl{font-size:9pt;color:var(--mute)}',
  '.dsd-toc .tt{font-family:var(--headf);--ff:var(--headf);font-size:var(--h1s);color:var(--p);font-weight:700;margin-bottom:8pt}.dsd-toc .it{display:flex;gap:10pt;padding:5pt 0;border-bottom:1px dotted var(--line)}.dsd-toc .it b{color:var(--at);width:22pt}',
  /* covers */
  '.dsd-cz{position:absolute}.dsd-cz .k{font-size:9.5pt;letter-spacing:.2em;text-transform:uppercase;font-weight:700;color:var(--at);margin-bottom:10pt}',
  '.dsd-cz .t{font-family:var(--headf);--ff:var(--headf);font-size:var(--cts);line-height:1.04;font-weight:700;color:var(--p)}',
  '.dsd-cz .s{font-size:13pt;color:var(--mute);margin-top:12pt;line-height:1.35}.dsd-cz .m{font-size:10pt;color:var(--ink)}.dsd-cz .m b{color:var(--p)}',
  '.dsd-side-dark .dsd-side{--at:var(--a);--st:var(--s)}.dsd-sz.dark,.dsd-cz.dark{color:#fff}.dsd-cz.dark .t,.dsd-cz.dark .m,.dsd-cz.dark .m b{color:#fff}.dsd-cz.dark .s{color:rgba(255,255,255,.88)}',
  /* resume */
  '.dsd-rh .nm{font-family:var(--headf);--ff:var(--headf);font-size:30pt;font-weight:700;color:var(--p);line-height:1.02}.dsd-rh .rl{font-size:12pt;color:var(--at);font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-top:4pt}',
  '.dsd-rh .ct{font-size:9pt;color:var(--mute);margin-top:6pt}.dsd-rs{font-family:var(--headf);--ff:var(--headf);font-size:11pt;font-weight:700;color:var(--p);text-transform:uppercase;letter-spacing:.12em;border-bottom:2px solid var(--a);padding-bottom:3pt;margin:14pt 0 8pt}',
  '.dsd-re{margin-bottom:9pt}.dsd-re .tl{display:flex;justify-content:space-between;gap:8pt}.dsd-re .t{font-weight:700;color:var(--ink)}.dsd-re .dt{color:var(--mute);font-size:9pt;white-space:nowrap}.dsd-re .o{color:var(--st);font-weight:600;font-size:9.5pt}',
  '.dsd-re ul{margin:3pt 0 0;padding-left:14pt}.dsd-re li{margin-bottom:2pt}',
  '.dsd-sk{display:flex;flex-wrap:wrap;gap:5pt}.dsd-sk span{background:var(--tint);color:var(--p);padding:3pt 8pt;border-radius:10pt;font-size:9pt;font-weight:600}',
  '.dsd-side-dark .dsd-side{color:#fff}.dsd-side-dark .dsd-side .dsd-rs{color:#fff;border-color:var(--a)}.dsd-side-dark .dsd-side .dsd-sk span{background:rgba(255,255,255,.14);color:#fff}',
  '.dsd-side-dark .dsd-side .dsd-rh .ct,.dsd-side-dark .dsd-side .dsd-b{color:rgba(255,255,255,.9)}',
  '.dsd-rt .dsd-re{position:relative}.dsd-rt .dsd-re .dt{position:absolute;left:-92pt;width:80pt;text-align:right;top:1pt;white-space:normal;color:var(--at);font-weight:700}',
  '.dsd-band .dsd-rh .nm,.dsd-band .dsd-rh .rl{color:#fff}.dsd-band .dsd-rh .ct{color:rgba(255,255,255,.85)}',
  '.dsd-centre .dsd-rh{text-align:center}.dsd-centre .dsd-rs{text-align:center;border-bottom:1px solid var(--line)}',
  /* single pages */
  '.dsd-sz{position:absolute;display:flex;flex-direction:column}.dsd-pz{font-size:var(--bs);color:var(--ink);z-index:2}.dsd-pz>.dsd-blk{max-width:100%;align-self:stretch}.dsd-pz .dsd-blk:first-child>*{margin-top:0}',
  '.dsd-s-k{font-size:calc(11pt*var(--k,1)*var(--fit,1));letter-spacing:.24em;text-transform:uppercase;font-weight:700;color:var(--at)}',
  '.dsd-s-t{font-family:var(--headf);--ff:var(--headf);font-weight:700;line-height:1;color:var(--p);margin:calc(8pt*var(--k,1)*var(--fit,1)) 0}',
  '.dsd-s-s{font-family:var(--headf);--ff:var(--headf);font-size:calc(16pt*var(--k,1)*var(--fit,1));color:var(--st);font-style:italic}.dsd-s-x{font-size:calc(11.5pt*var(--k,1)*var(--fit,1));line-height:1.45;margin-top:calc(10pt*var(--k,1)*var(--fit,1))}',
  '.dsd-s-d{margin-top:calc(14pt*var(--k,1)*var(--fit,1));width:100%}.dsd-s-d .r{display:flex;gap:calc(10pt*var(--k,1)*var(--fit,1));padding:calc(5pt*var(--k,1)*var(--fit,1)) 0;border-bottom:1px solid var(--line)}.dsd-s-d .r b{width:30%;color:var(--at);text-transform:uppercase;font-size:calc(9pt*var(--k,1)*var(--fit,1));letter-spacing:.1em;padding-top:calc(2pt*var(--k,1)*var(--fit,1))}',
  '.dsd-s-c{margin-top:calc(16pt*var(--k,1)*var(--fit,1));display:inline-block;background:var(--a);color:var(--ona);font-weight:700;padding:calc(8pt*var(--k,1)*var(--fit,1)) calc(18pt*var(--k,1)*var(--fit,1));font-size:calc(12pt*var(--k,1)*var(--fit,1))}',
  '.dsd-menu .ms{font-family:var(--headf);--ff:var(--headf);font-size:calc(15pt*var(--k,1)*var(--fit,1));color:var(--at);text-transform:uppercase;letter-spacing:.2em;margin:calc(14pt*var(--k,1)*var(--fit,1)) 0 calc(6pt*var(--k,1)*var(--fit,1));text-align:center}',
  '.dsd-menu .mi{display:flex;align-items:baseline;gap:calc(6pt*var(--k,1)*var(--fit,1));margin:calc(5pt*var(--k,1)*var(--fit,1)) 0}.dsd-menu .mn{font-weight:700;font-size:calc(11.5pt*var(--k,1)*var(--fit,1))}.dsd-menu .dl{flex:1;border-bottom:1.5px dotted var(--line)}.dsd-menu .mp{font-weight:700;color:var(--p)}',
  '.dsd-menu .md{font-size:calc(9pt*var(--k,1)*var(--fit,1));color:var(--mute);margin:-2pt 0 calc(6pt*var(--k,1)*var(--fit,1))}',
  '.dsd-tk{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:calc(10pt*var(--k,1)*var(--fit,1))}.dsd-tk .tkt{display:flex;border:1.5px solid var(--p);height:calc(118pt*var(--k,1)*var(--fit,1));background:#fff}',
  '.dsd-tk .tm{flex:1;padding:calc(10pt*var(--k,1)*var(--fit,1)) calc(12pt*var(--k,1)*var(--fit,1));border-right:2px dashed var(--line)}.dsd-tk .te{font-family:var(--headf);--ff:var(--headf);font-weight:700;color:var(--p);font-size:calc(14pt*var(--k,1)*var(--fit,1));line-height:1.1}',
  '.dsd-tk .tsb{font-size:calc(8pt*var(--k,1)*var(--fit,1));letter-spacing:.2em;text-transform:uppercase;color:var(--at);font-weight:700;margin-top:calc(3pt*var(--k,1)*var(--fit,1))}.dsd-tk .ti{font-size:calc(8.5pt*var(--k,1)*var(--fit,1));color:var(--ink);margin-top:calc(6pt*var(--k,1)*var(--fit,1))}',
  '.dsd-tk .stub{width:calc(64pt*var(--k,1)*var(--fit,1));background:var(--p);color:var(--onp);display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:700}.dsd-tk .stub .no{font-size:calc(15pt*var(--k,1)*var(--fit,1))}.dsd-tk .stub .pr{font-size:calc(9pt*var(--k,1)*var(--fit,1));margin-top:calc(4pt*var(--k,1)*var(--fit,1))}',
  '.dsd-cert{text-align:center}.dsd-cert .ct{font-family:var(--headf);--ff:var(--headf);font-size:calc(46pt*var(--k,1)*var(--fit,1));color:var(--p);letter-spacing:.06em;text-transform:uppercase;line-height:1}.dsd-cert .cs{font-size:calc(15pt*var(--k,1)*var(--fit,1));color:var(--at);letter-spacing:.3em;text-transform:uppercase;margin-top:calc(6pt*var(--k,1)*var(--fit,1))}',
  '.dsd-cert .cp{font-size:calc(11pt*var(--k,1)*var(--fit,1));color:var(--mute);margin-top:calc(22pt*var(--k,1)*var(--fit,1))}.dsd-cert .cn{font-family:var(--headf);--ff:var(--headf);font-size:calc(34pt*var(--k,1)*var(--fit,1));color:var(--ink);font-style:italic;margin-top:calc(8pt*var(--k,1)*var(--fit,1));border-bottom:1.5px solid var(--a);display:inline-block;padding:0 calc(24pt*var(--k,1)*var(--fit,1)) calc(4pt*var(--k,1)*var(--fit,1))}',
  '.dsd-cert .cr{font-size:calc(11.5pt*var(--k,1)*var(--fit,1));margin:calc(12pt*var(--k,1)*var(--fit,1)) auto 0;max-width:72%}.dsd-cert .dsd-sigs{margin:calc(34pt*var(--k,1)*var(--fit,1)) calc(60pt*var(--k,1)*var(--fit,1)) 0}',
  /* editing */
  '.dsd-edit [contenteditable]{cursor:text}.dsd-edit [contenteditable]:hover{outline:1px dashed rgba(59,130,246,.5);outline-offset:2px}',
  '.dsd-edit [contenteditable]:focus{outline:2px solid #3B82F6;outline-offset:2px}',
  '.dsd-edit .dsd-blk.sel{box-shadow:0 0 0 2px #3B82F6;border-radius:2px}',
  '.dsd-blk.s-fs .dsd-h,.dsd-blk.s-fs .dsd-b,.dsd-blk.s-fs li,.dsd-blk.s-fs td,.dsd-blk.s-fs th,.dsd-blk.s-fs .dsd-quote,.dsd-blk.s-fs [class^="dsd-s-"],.dsd-blk.s-fs .ct,.dsd-blk.s-fs .t,.dsd-blk.s-fs .nm,.dsd-blk.s-fs .rl,.dsd-blk.s-fs .dsd-rs,.dsd-blk.s-fs figcaption{font-size:var(--fs)!important}',
  '.dsd-blk.s-tc .dsd-h,.dsd-blk.s-tc .dsd-b,.dsd-blk.s-tc li,.dsd-blk.s-tc td,.dsd-blk.s-tc .dsd-quote,.dsd-blk.s-tc .ct,.dsd-blk.s-tc .t,.dsd-blk.s-tc .v,.dsd-blk.s-tc .l,.dsd-blk.s-tc [class^="dsd-s-"],.dsd-blk.s-tc .nm,.dsd-blk.s-tc .rl,.dsd-blk.s-tc .dsd-rs,.dsd-blk.s-tc .dsd-lab,.dsd-blk.s-tc .dsd-numl,.dsd-blk.s-tc figcaption{color:var(--tc)!important}',
  '.dsd-blk.s-fw *{font-weight:inherit!important}.dsd-blk.s-it *{font-style:inherit!important}.dsd-blk.s-td *{text-decoration:inherit!important}.dsd-blk.s-cp *{text-transform:inherit!important}',
  '.dsd-blk.s-al *{text-align:inherit!important}.dsd-blk.s-lh *{line-height:inherit!important}.dsd-blk.s-ls *{letter-spacing:inherit!important}',
  '.dsd-blk.s-box{box-sizing:border-box}.dsd-blk.s-box>*:first-child,.dsd-blk.s-box>*:first-child>*:first-child{margin-top:0}.dsd-blk.s-box>*:last-child{margin-bottom:0}',
  '.dsd-blk>figure.dsd-fig,.dsd-blk>figure.dsd-chart{width:100%;margin-left:0;margin-right:0}',
  '.dsd-logo{position:absolute;z-index:4;pointer-events:none;display:block;box-sizing:border-box}',
  '.dsd-edit [contenteditable]:empty::before{content:attr(data-ph);color:#A0A7B4}'
].join('\n');

D.injectCSS = function (doc) {
  doc = doc || document;
  if (doc.getElementById('dsd-css')) return;
  var s = doc.createElement('style'); s.id = 'dsd-css'; s.textContent = D.CSS; doc.head.appendChild(s);
  var l = doc.createElement('link'); l.rel = 'stylesheet'; l.href = D.fontLink(); doc.head.appendChild(l);
};

function vars(t, dz) {
  return '--p:' + t.p + ';--s:' + t.s + ';--a:' + t.a + ';--ink:' + t.ink + ';--mute:' + t.mute + ';--tint:' + t.tint + ';--line:' + t.line +
    ';--at:' + D.readable(t.a, t.tint) + ';--st:' + D.readable(t.s, t.tint) + ';--pt:' + D.readable(t.p, t.tint) + ';--onp:' + D.onColor(t.p) + ';--ona:' + D.onColor(t.a) + ';--headf:' + D.css(t.head).replace(/"/g, "'") + ';--bodyf:' + D.css(t.body).replace(/"/g, "'") +
    ';--bs:' + dz.body + 'pt;--h1s:' + dz.h1 + 'pt;--h2s:' + dz.h2 + 'pt';
}
function artURL(svgStr) { return svgStr ? "url('data:image/svg+xml;charset=utf-8," + encodeURIComponent(svgStr).replace(/'/g, '%27') + "')" : 'none'; }

/* editable field helper */
function ed(o, tag, cls, field, val, ph, rich) {
  var a = '<' + tag + ' class="' + cls + '"';
  if (o.edit) a += ' contenteditable="true" spellcheck="true" data-f="' + field + '"' + (rich === false ? '' : ' data-r="1"') + (ph ? ' data-ph="' + D.esc(ph) + '"' : '');
  return a + '>' + (rich === false ? D.esc(val || '') : D.runsHTML(val || '')) + '</' + tag + '>';
}

/* ---------------- a block's own box: width, place and look (set in the editor) ---------------- */
function rgbaC(c, a) { var r = D.rgb(c); return 'rgba(' + r[0] + ',' + r[1] + ',' + r[2] + ',' + (Math.round(a * 1000) / 1000) + ')'; }
D.paintCSS = function (p, t) {
  if (p && p.m === 'pic') return D.picSrcOK(p.src) ? 'url(' + String(p.src).replace(/["'()\\\s]/g, '') + ') ' + D.num(p.px, 50, 0, 100) + '% ' + D.num(p.py, 50, 0, 100) + '%/' + (p.st ? '100% 100%' : (D.num(p.z, 1, 1, 4) === 1 ? 'cover' : Math.round(D.num(p.z, 1, 1, 4) * 100) + '%')) + ' no-repeat' : rgbaC(D.col('@tint', t, '#EEF1F6'), 1);
  if (p && p.m === 'grad') {
    var g = p.g || {}, st = (g.s && g.s.length ? g.s : [[0, '@p', 1], [100, '@a', 1]]).map(function (x) { return rgbaC(D.col(x[1], t), D.num(x[2], 1, 0, 1)) + ' ' + D.num(x[0], 0, 0, 100) + '%'; }).join(',');
    return g.t === 'radial' ? 'radial-gradient(circle,' + st + ')' : 'linear-gradient(' + (D.num(g.a, 0) + 90) + 'deg,' + st + ')';
  }
  return rgbaC(D.col(p && p.c, t, '#000000'), p && p.o != null ? D.num(p.o, 1, 0, 1) : 1);
};
/** width (0.15-1 of the text column), left offset, space above / below */
D.blockGeo = function (b) {
  var w = b.w != null ? D.num(b.w, 1, 0.15, 1) : 1, def = (b.t === 'image' || b.t === 'chart') ? (1 - w) / 2 : 0;
  var ox = b.ox != null ? D.num(b.ox, 0, 0, 0.85) : def;
  if (ox + w > 1) ox = Math.max(0, 1 - w);
  return { w: w, ox: ox, mt: D.num(b.mt, 0, -40, 600), mb: D.num(b.mb, 0, -40, 600) };
};
D.blockBox = function (b, t) {
  var st = b.st || {}, s = '', c = '', g = D.blockGeo(b), box = false;
  if (g.w < 1) s += 'width:' + (g.w * 100).toFixed(2) + '%;';
  if (g.ox > 0) s += 'margin-left:' + (g.ox * 100).toFixed(2) + '%;';
  if (g.mt) s += 'margin-top:' + g.mt + 'px;';
  if (g.mb) s += 'margin-bottom:' + g.mb + 'px;';
  if (st.ff) { var f = D.css(st.ff).replace(/"/g, "'"); s += '--headf:' + f + ';--bodyf:' + f + ';--ff:' + f + ';'; }
  if (st.fs) { var z = D.num(st.fs, 11, 4, 300); s += '--fs:' + z + 'pt;--bs:' + z + 'pt;--h1s:' + z + 'pt;--h2s:' + z + 'pt;'; c += ' s-fs'; }
  if (st.tc) { s += '--tc:' + D.col(st.tc, t) + ';'; c += ' s-tc'; }
  if (st.fw) { s += 'font-weight:' + D.num(st.fw, 400, 100, 900) + ';'; c += ' s-fw'; }
  if (st.it != null) { s += 'font-style:' + (st.it ? 'italic' : 'normal') + ';'; c += ' s-it'; }
  if (st.ul || st.sk) { s += 'text-decoration:' + [st.ul ? 'underline' : '', st.sk ? 'line-through' : ''].join(' ').trim() + ';'; c += ' s-td'; }
  if (st.caps) { s += 'text-transform:uppercase;'; c += ' s-cp'; }
  if (st.al) { s += 'text-align:' + ({ left: 'left', center: 'center', right: 'right', justify: 'justify' }[st.al] || 'left') + ';'; c += ' s-al'; }
  if (st.lh) { s += 'line-height:' + D.num(st.lh, 1.3, 0.6, 4) + ';'; c += ' s-lh'; }
  if (st.ls) { s += 'letter-spacing:' + D.num(st.ls, 0, -5, 40) + 'px;'; c += ' s-ls'; }
  if (st.fill && st.fill.m && st.fill.m !== 'none') { s += 'background:' + D.paintCSS(st.fill, t) + ';'; box = true; }
  if (st.ln && st.ln.m && st.ln.m !== 'none') {
    var lw = D.num(st.ln.w, 2, 0, 30);
    s += 'border:' + lw + 'px ' + ({ dash: 'dashed', dot: 'dotted' }[st.ln.d] || 'solid') + ' ' + (st.ln.m === 'grad' ? 'transparent' : rgbaC(D.col(st.ln.c || '@a', t), st.ln.o != null ? D.num(st.ln.o, 1, 0, 1) : 1)) + ';';
    if (st.ln.m === 'grad') s += 'border-image:' + D.paintCSS(st.ln, t) + ' 1;';
    box = true;
  }
  if (st.r) s += 'border-radius:' + D.num(st.r, 0, 0, 300) + 'px;';
  if (st.sh) { var a = D.num(st.sh.a, 45) * Math.PI / 180, d = D.num(st.sh.d, 6, 0, 200); s += 'box-shadow:' + (Math.cos(a) * d).toFixed(1) + 'px ' + (Math.sin(a) * d).toFixed(1) + 'px ' + D.num(st.sh.b, 10, 0, 200) + 'px ' + D.num(st.sh.s, 0, 0, 60) + 'px ' + rgbaC(D.col(st.sh.c, t, '#000000'), D.num(st.sh.o, 0.35, 0, 1)) + ';'; }
  if (st.op != null && st.op < 1) s += 'opacity:' + D.num(st.op, 1, 0, 1) + ';';
  if (box) { var pd = D.num(st.pad, 10, 0, 80); s += 'padding:' + pd + 'px ' + (pd + 4) + 'px;'; c += ' s-box'; }
  return { cls: c, style: s.replace(/"/g, "'") };
};
/** what a block is called in the editor (Layers list, toolbar) */
D.blockName = function (b) {
  var N = { h1: 'Heading', h2: 'Subheading', h3: 'Small heading', p: 'Paragraph', ul: 'Bullet list', ol: 'Numbered list', check: 'Checklist', quote: 'Quote', callout: 'Callout box', stats: 'Key numbers', table: 'Table', timeline: 'Timeline', cols: 'Columns', kv: 'Details table', image: 'Picture', chart: 'Chart', pb: 'Page break', toc: 'Contents', lh: 'Letterhead', title: 'Title', addr: 'Address', sign: 'Sign-off', inv: 'Invoice lines', todo: 'To-do table', week: 'Week planner', cal: 'Calendar', sigs: 'Signatures', r_head: 'Name & contacts', r_sec: 'Resume section', r_entry: 'Job / education', r_skills: 'Skills', s_kicker: 'Small line', s_title: 'Big title', s_sub: 'Subtitle', s_text: 'Text', s_cta: 'Button', s_details: 'Details', s_menu: 'Menu', s_tickets: 'Tickets', s_cert: 'Certificate' };
  return N[b.t] || 'Block';
};
D.blockText = function (b) {
  var t = b.text || b.title || b.name || (b.items && typeof b.items[0] === 'string' ? b.items[0] : '') || (b.head && b.head.join(' · ')) || b.event || '';
  return D.runs(String(t)).map(function (r) { return r.t; }).join('').replace(/\s+/g, ' ').trim();
};

/* ---------------- blocks ---------------- */
D.blockHTML = function (b, ctx) {
  var o = ctx.o, dz = ctx.dz, h = '', i;
  if (b.lock && o.edit) o = { edit: false, images: o.images };
  function wrap(inner, cls) { var bx = D.blockBox(b, ctx.t || D.look(ctx.doc)); return '<div class="dsd-blk ' + (cls || '') + bx.cls + (b.lock ? ' dsd-lk' : '') + '" data-id="' + b.id + '" data-t="' + b.t + '"' + (bx.style ? ' style="' + bx.style + '"' : '') + '>' + inner + '</div>'; }
  switch (b.t) {
    case 'h1':
      ctx.h1n = (ctx.h1n || 0) + 1;
      var n = ('0' + ctx.h1n).slice(-2), pre = '';
      if (dz.head === 'number') pre = '<span class="dsd-numl">' + n + '</span>';
      if (dz.head === 'caps') pre = '<span class="dsd-lab">Section ' + n + '</span>';
      if (dz.head === 'chapter') pre = '<span class="dsd-lab" style="text-align:center">Chapter ' + ctx.h1n + '</span>';
      if (dz.head === 'tag') pre = '<span class="dsd-tag">' + n + '</span>';
      if (dz.head === 'serif') pre = '<span class="dsd-sn" style="float:left;margin-right:8pt;font-family:var(--headf);--ff:var(--headf);font-size:var(--h1s);font-weight:700;line-height:1.15">' + ctx.h1n + '.</span>';
      return wrap(pre + ed(o, 'h2', 'dsd-h dsd-h1', 'text', b.text, 'Heading') + (dz.head === 'chapter' ? '<span class="dsd-orn">◆ ◆ ◆</span>' : ''), 'hd' + (dz.head === 'chapter' ? ' chap' : ''));
    case 'h2': return wrap(ed(o, 'h3', 'dsd-h dsd-h2', 'text', b.text, 'Subheading'), 'hd');
    case 'h3': return wrap(ed(o, 'h4', 'dsd-h dsd-h3', 'text', b.text, 'Small heading'), 'hd');
    case 'p': return wrap(ed(o, 'p', 'dsd-b', 'text', b.text, 'Type here…'));
    case 'ul': case 'ol': case 'check':
      var tag = b.t === 'ol' ? 'ol' : 'ul', cls = b.t === 'ul' ? 'dsd-ul' : b.t === 'ol' ? 'dsd-ol' : 'dsd-ck';
      h = '<' + tag + ' class="' + cls + '">';
      for (i = 0; i < (b.items || []).length; i++) h += ed(o, 'li', '', 'items.' + i, b.items[i], 'List item');
      return wrap(h + '</' + tag + '>');
    case 'quote': return wrap('<div class="dsd-quote">' + ed(o, 'div', '', 'text', b.text, 'Quote') + (b.by != null ? ed(o, 'span', 'by', 'by', b.by, 'Who said it', false) : '') + '</div>');
    case 'callout': return wrap('<div class="dsd-call">' + ed(o, 'div', 'ct', 'title', b.title, 'Title', false) + ed(o, 'div', '', 'text', b.text, 'Text') + '</div>');
    case 'stats':
      h = '<div class="dsd-stats">';
      (b.items || []).forEach(function (s, k) { h += '<div>' + ed(o, 'div', 'v', 'items.' + k + '.v', s.v, '0', false) + ed(o, 'div', 'l', 'items.' + k + '.l', s.l, 'Label', false) + '</div>'; });
      return wrap(h + '</div>');
    case 'table':
      h = '<table class="dsd-tab"><thead><tr>';
      (b.head || []).forEach(function (c, k) { h += ed(o, 'th', '', 'head.' + k, c, 'Column', false); });
      h += '</tr></thead><tbody>';
      (b.rows || []).forEach(function (r, ri) { h += '<tr>'; r.forEach(function (c, k) { h += ed(o, 'td', '', 'rows.' + ri + '.' + k, c, ''); }); h += '</tr>'; });
      return wrap(h + '</tbody></table>');
    case 'timeline':
      h = '<div class="dsd-tl">';
      (b.items || []).forEach(function (s, k) { h += '<div class="r">' + ed(o, 'div', 'd', 'items.' + k + '.d', s.d, 'When', false) + '<div class="c">' + ed(o, 'div', 't', 'items.' + k + '.title', s.title, 'Step', false) + ed(o, 'div', '', 'items.' + k + '.text', s.text, 'Details') + '</div></div>'; });
      return wrap(h + '</div>');
    case 'cols':
      h = '<div class="dsd-cols">';
      (b.items || []).forEach(function (s, k) { h += '<div>' + ed(o, 'div', 'ct', 'items.' + k + '.title', s.title, 'Title', false) + ed(o, 'div', 'cx', 'items.' + k + '.text', s.text, 'Text') + '</div>'; });
      return wrap(h + '</div>');
    case 'kv':
      h = '<table class="dsd-kv">';
      (b.rows || []).forEach(function (r, k) { h += '<tr>' + ed(o, 'td', 'k', 'rows.' + k + '.0', r[0], 'Label', false) + ed(o, 'td', '', 'rows.' + k + '.1', r[1], '') + '</tr>'; });
      return wrap(h + '</table>');
    case 'image':
      var src = b.src || (ctx.images && ctx.images[b.id]) || '';
      h = '<figure class="dsd-fig">' +
        (src ? '<img src="' + D.esc(src) + '" alt="' + D.esc(b.cap || b.q || '') + '">' : '<div class="ph">' + (o.edit ? 'Click to add a picture' : 'Picture') + '</div>') +
        (b.cap != null ? ed(o, 'figcaption', '', 'cap', b.cap, 'Caption', false) : '') + '</figure>';
      return wrap(h, 'img');
    case 'chart':
      var cw0 = 640, ch0 = Math.round(cw0 * D.chartRatio(b));
      return wrap('<figure class="dsd-chart">' + D.chartSVG(b, ctx.t || D.look(ctx.doc), cw0, ch0, { ff: (ctx.t || D.look(ctx.doc)).body }) +
        (b.cap != null ? ed(o, 'figcaption', '', 'cap', b.cap, 'Caption', false) : '') + '</figure>', 'img');
    case 'pb': return wrap('<div class="dsd-pbm" style="height:1px"></div>', 'pb');
    case 'toc':
      h = '<div class="dsd-toc"><div class="tt">Contents</div>'; var c = 0;
      (ctx.doc.blocks || []).forEach(function (x) { if (x.t === 'h1') { c++; h += '<div class="it"><b>' + ('0' + c).slice(-2) + '</b><span>' + D.esc(D.runs(x.text).map(function (r) { return r.t; }).join('')) + '</span></div>'; } });
      return wrap(h + '</div>');
    case 'lh': return wrap('<div class="dsd-lh">' + ed(o, 'div', 'n', 'name', b.name, 'Your name', false) + (b.lines || []).map(function (l, k) { return ed(o, 'div', 'l', 'lines.' + k, l, '', false); }).join('') + '</div>');
    case 'title': return wrap('<div class="dsd-title">' + ed(o, 'div', 'tt', 'text', b.text, 'Title', false) + (b.sub != null ? ed(o, 'div', 'ts', 'sub', b.sub, 'Subtitle', false) : '') + '</div>');
    case 'addr': return wrap('<div class="dsd-addr dsd-b">' + (b.lines || []).map(function (l, k) { return ed(o, 'div', '', 'lines.' + k, l, '', false); }).join('') + '</div>');
    case 'sign': return wrap('<div class="dsd-sign dsd-b">' + ed(o, 'div', '', 'close', b.close, 'Yours sincerely,', false) + '<div class="gap"></div>' + ed(o, 'div', 'nm', 'name', b.name, 'Name', false) + ed(o, 'div', 'rl', 'role', b.role, 'Title', false) + '</div>');
    case 'inv':
      var cur = b.cur || '$', sub = 0;
      h = '<table class="dsd-inv"><thead><tr><th>Description</th><th class="n">Qty</th><th class="n">Rate</th><th class="n">Amount</th></tr></thead><tbody>';
      (b.rows || []).forEach(function (r, k) {
        var amt = (+r.q || 0) * (+r.r || 0); sub += amt;
        h += '<tr>' + ed(o, 'td', '', 'rows.' + k + '.d', r.d, 'Item', false) + ed(o, 'td', 'n', 'rows.' + k + '.q', String(r.q == null ? '' : r.q), '0', false) +
          ed(o, 'td', 'n', 'rows.' + k + '.r', String(r.r == null ? '' : r.r), '0', false) + '<td class="n">' + D.money(amt, cur) + '</td></tr>';
      });
      var tax = sub * (+b.tax || 0) / 100;
      h += '<tr class="tot"><td colspan="3" class="n">Subtotal</td><td class="n">' + D.money(sub, cur) + '</td></tr>' +
        '<tr class="tot"><td colspan="3" class="n">Tax (' + (+b.tax || 0) + '%)</td><td class="n">' + D.money(tax, cur) + '</td></tr>' +
        '<tr class="grand"><td colspan="3" class="n">Total</td><td class="n">' + D.money(sub + tax, cur) + '</td></tr></tbody></table>';
      return wrap(h);
    case 'todo':
      h = '<table class="dsd-todo"><thead><tr><th></th><th>Task</th><th style="width:22%">Due</th><th style="width:18%">Priority</th></tr></thead><tbody>';
      (b.rows || []).forEach(function (r, k) { h += '<tr><td class="bx">☐</td>' + ed(o, 'td', '', 'rows.' + k + '.task', r.task, '', false) + ed(o, 'td', '', 'rows.' + k + '.due', r.due, '', false) + ed(o, 'td', '', 'rows.' + k + '.pr', r.pr, '', false) + '</tr>'; });
      return wrap(h + '</tbody></table>');
    case 'week':
      h = '<table class="dsd-week">';
      (b.days || []).forEach(function (d, k) { h += '<tr>' + ed(o, 'td', 'dn', 'days.' + k, d, '', false) + ed(o, 'td', '', 'notes.' + k, (b.notes || [])[k] || '', '') + '</tr>'; });
      return wrap(h + '</table>');
    case 'cal': return wrap(D.calHTML(b));
    case 'sigs': return wrap('<div class="dsd-sigs">' + (b.items || []).map(function (s, k) { return '<div>' + ed(o, 'div', 'nm', 'items.' + k + '.name', s.name, 'Name', false) + ed(o, 'div', 'rl', 'items.' + k + '.role', s.role, 'Role', false) + '</div>'; }).join('') + '</div>');
    /* resume */
    case 'r_head': return wrap('<div class="dsd-rh">' + ed(o, 'div', 'nm', 'name', b.name, 'Your name', false) + ed(o, 'div', 'rl', 'role', b.role, 'Job title', false) +
      '<div class="ct">' + (b.contacts || []).map(function (c, k) { return ed(o, 'span', '', 'contacts.' + k, c, '', false); }).join(' · ') + '</div></div>');
    case 'r_sec': return wrap(ed(o, 'div', 'dsd-rs', 'text', b.text, 'Section', false), 'hd');
    case 'r_entry':
      h = '<div class="dsd-re"><div class="tl">' + ed(o, 'span', 't', 'title', b.title, 'Role', false) + ed(o, 'span', 'dt', 'date', b.date, 'Dates', false) + '</div>' + ed(o, 'div', 'o', 'org', b.org, 'Organisation', false);
      if (b.text) h += ed(o, 'div', 'dsd-b', 'text', b.text, '');
      if ((b.items || []).length) h += '<ul>' + b.items.map(function (x, k) { return ed(o, 'li', '', 'items.' + k, x, ''); }).join('') + '</ul>';
      return wrap(h + '</div>');
    case 'r_skills': return wrap('<div class="dsd-sk">' + (b.items || []).map(function (x, k) { return ed(o, 'span', '', 'items.' + k, x, 'Skill', false); }).join('') + '</div>');
    /* single pages */
    case 's_kicker': return wrap(ed(o, 'div', 'dsd-s-k', 'text', b.text, 'Small line', false));
    case 's_title': return wrap(ed(o, 'div', 'dsd-s-t', 'text', b.text, 'Big title', false).replace('class="dsd-s-t"', 'class="dsd-s-t" style="font-size:calc(' + (ctx.titlePt || 48) + 'pt * var(--fit,1))"'));
    case 's_sub': return wrap(ed(o, 'div', 'dsd-s-s', 'text', b.text, 'Subtitle', false));
    case 's_text': return wrap(ed(o, 'div', 'dsd-s-x', 'text', b.text, 'Text'));
    case 's_cta': return wrap('<div>' + ed(o, 'span', 'dsd-s-c', 'text', b.text, 'Call to action', false) + '</div>');
    case 's_details': return wrap('<div class="dsd-s-d">' + (b.rows || []).map(function (r, k) { return '<div class="r">' + ed(o, 'b', '', 'rows.' + k + '.0', r[0], '', false) + ed(o, 'span', '', 'rows.' + k + '.1', r[1], '', false) + '</div>'; }).join('') + '</div>');
    case 's_menu':
      h = '<div class="dsd-menu">';
      (b.sections || []).forEach(function (sc, si) {
        h += ed(o, 'div', 'ms', 'sections.' + si + '.name', sc.name, 'Section', false);
        (sc.items || []).forEach(function (it, ii) {
          var f = 'sections.' + si + '.items.' + ii + '.';
          h += '<div class="mi">' + ed(o, 'span', 'mn', f + 'n', it.n, 'Dish', false) + '<span class="dl"></span>' + ed(o, 'span', 'mp', f + 'p', it.p, '0', false) + '</div>' + ed(o, 'div', 'md', f + 'd', it.d, 'Description', false);
        });
      });
      return wrap(h + '</div>');
    case 's_tickets':
      h = '<div class="dsd-tk">';
      for (i = 0; i < (b.n || 8); i++) {
        h += '<div class="tkt"><div class="tm">' + (i === 0 ? ed(o, 'div', 'te', 'event', b.event, 'Event', false) + ed(o, 'div', 'tsb', 'sub', b.sub, '', false) + ed(o, 'div', 'ti', 'date', b.date, '', false) + ed(o, 'div', 'ti', 'place', b.place, '', false)
          : '<div class="te">' + D.esc(b.event) + '</div><div class="tsb">' + D.esc(b.sub) + '</div><div class="ti">' + D.esc(b.date) + '</div><div class="ti">' + D.esc(b.place) + '</div>') +
          '</div><div class="stub"><span class="no">No. ' + ('00' + ((+b.start || 1) + i)).slice(-3) + '</span><span class="pr">' + (i === 0 ? ed(o, 'span', '', 'price', b.price, '', false) : D.esc(b.price)) + '</span></div></div>';
      }
      return wrap(h + '</div>');
    case 's_cert':
      return wrap('<div class="dsd-cert">' + ed(o, 'div', 'ct', 'title', b.title, 'Certificate', false) + ed(o, 'div', 'cs', 'sub', b.sub, '', false) + ed(o, 'div', 'cp', 'pre', b.pre, '', false) +
        '<div>' + ed(o, 'div', 'cn', 'name', b.name, 'Name', false) + '</div>' + ed(o, 'div', 'cr', 'reason', b.reason, '') +
        '<div class="dsd-sigs">' + (b.sigs || []).map(function (s, k) { return '<div>' + ed(o, 'div', 'nm', 'sigs.' + k + '.name', s.name, 'Name', false) + ed(o, 'div', 'rl', 'sigs.' + k + '.role', s.role, 'Role', false) + '</div>'; }).join('') + '</div></div>');
  }
  return wrap(ed(o, 'p', 'dsd-b', 'text', b.text || '', ''));
};
D.money = function (n, cur) { return (cur || '') + (Math.round(n * 100) / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
D.calHTML = function (b) {
  var y = +b.y || new Date().getFullYear(), m = +b.m || 0, first = new Date(y, m, 1), start = (first.getDay() + 6) % 7, days = new Date(y, m + 1, 0).getDate();
  var h = '<table class="dsd-cal"><thead><tr>' + ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map(function (d) { return '<th>' + d + '</th>'; }).join('') + '</tr></thead><tbody>';
  var cells = Math.ceil((start + days) / 7) * 7;
  for (var i = 0; i < cells; i++) {
    if (i % 7 === 0) h += '<tr>';
    var d = i - start + 1;
    h += d >= 1 && d <= days ? '<td>' + d + '</td>' : '<td class="o"></td>';
    if (i % 7 === 6) h += '</tr>';
  }
  return h + '</tbody></table>';
};

/* ---------------- the design's own shapes as editable elements ----------------
 * D.detachArt(doc) turns the cover / letterhead / flyer artwork and the decoration that repeats on
 * every page into "vec" layers: each shape can then be selected, moved, resized, turned, recoloured
 * or deleted like any other element. Shapes that repeat on every page get pg:'m' (one element,
 * drawn on every page). Only the plain page colour and photos stay in the background.
 */
D.artKey = function (doc) { return (doc.tpl || '') + '|' + (doc.size || 'A4'); };
D.artOn = function (doc) { return !!(doc && doc.artv && doc.artTpl === D.artKey(doc)); };
D.isBand = function (dz) { return !!(dz && (dz.band || dz.page === 'headband')); };
D.masterOn = function (doc, dz, p) { return !(p === 0 && (dz.first || D.isBand(dz))); };
/** the background that stays when the shapes are elements: the page colour and photos */
D.artBg = function (svgStr) {
  if (!svgStr || typeof DOMParser === 'undefined') return svgStr;
  var d = new DOMParser().parseFromString(svgStr, 'image/svg+xml'), r = d.documentElement; if (!r || d.getElementsByTagName('parsererror').length) return svgStr;
  var W = +r.getAttribute('width'), H = +r.getAttribute('height');
  Array.prototype.slice.call(r.children).forEach(function (n) {
    var nm = n.localName;
    if (/^(defs|clipPath|linearGradient|radialGradient|image)$/.test(nm)) return;
    if (nm === 'rect' && +(n.getAttribute('x') || 0) === 0 && +(n.getAttribute('y') || 0) === 0 && +n.getAttribute('width') >= W - 1 && +n.getAttribute('height') >= H - 1) return;
    n.parentNode.removeChild(n);
  });
  return new XMLSerializer().serializeToString(r);
};
D.detachArt = function (doc) {
  if (typeof document === 'undefined') return doc;
  var dz = D.design(doc.tpl), t = D.look(doc), sz = D.size(doc), NS = 'http://www.w3.org/2000/svg', out = [], cnt = 0;
  var tokens = [['@p', t.p], ['@s', t.s], ['@a', t.a], ['@tint', t.tint], ['@line', t.line], ['@ink', t.ink], ['@mute', t.mute], ['@w', '#FFFFFF'], ['@k', '#000000']];
  function tok(c) { c = String(c || '').trim(); if (!/^#/.test(c)) return c; var hx = D.col(c, t); for (var i = 0; i < tokens.length; i++) if (D.col(tokens[i][1], t) === hx) return tokens[i][0]; return hx; }
  var mh = document.createElementNS(NS, 'svg'); mh.setAttribute('width', sz.w); mh.setAttribute('height', sz.h); mh.style.cssText = 'position:absolute;left:-99999px;top:0;visibility:hidden'; document.body.appendChild(mh);
  function conv(svgStr, pg, prefix) {
    if (!svgStr) return;
    var dd = new DOMParser().parseFromString(svgStr, 'image/svg+xml'), root = dd.documentElement; if (!root || dd.getElementsByTagName('parsererror').length) return;
    var W = +root.getAttribute('width'), H = +root.getAttribute('height'), grads = {}, clips = {};
    Array.prototype.forEach.call(root.getElementsByTagName('linearGradient'), function (g) { grads[g.getAttribute('id')] = g; });
    Array.prototype.forEach.call(root.getElementsByTagName('clipPath'), function (c) { clips[c.getAttribute('id')] = c; });
    var kids = Array.prototype.slice.call(root.children).filter(function (n) { return !/^(defs|clipPath|linearGradient|radialGradient|image)$/.test(n.localName); });
    /* panel designs wrap each panel in <g transform="translate(x,y)"><svg>...</svg></g>: take the shapes out of the
       wrapper so each one becomes its own element with its own colour (the wrapper used to become one plain shape) */
    var offs = new Map(), flat = [];
    kids.forEach(function (n) {
      var tm = n.localName === 'g' && /^translate\(\s*([-\d.]+)[ ,]+([-\d.]+)\s*\)$/.exec(n.getAttribute('transform') || ''), inner = tm && n.children.length === 1 && n.firstElementChild.localName === 'svg' ? n.firstElementChild : null;
      if (!inner) { flat.push(n); return; }
      Array.prototype.forEach.call(inner.children, function (c) { if (/^(defs|clipPath|linearGradient|radialGradient|image)$/.test(c.localName)) return; offs.set(c, [+tm[1], +tm[2]]); flat.push(c); });
    });
    kids = flat;
    function gradPaint(id, op) {
      var g = grads[id]; if (!g) return null;
      var x1 = +(g.getAttribute('x1') || 0), y1 = +(g.getAttribute('y1') || 0), x2 = g.getAttribute('x2') == null ? 1 : +g.getAttribute('x2'), y2 = g.getAttribute('y2') == null ? 0 : +g.getAttribute('y2');
      var st = Array.prototype.map.call(g.getElementsByTagName('stop'), function (x) { var o = String(x.getAttribute('offset') || '0'); return [Math.round((/%$/.test(o) ? parseFloat(o) : parseFloat(o) * 100) || 0), tok(x.getAttribute('stop-color') || '#000000'), x.getAttribute('stop-opacity') == null ? 1 : +x.getAttribute('stop-opacity')]; });
      return { m: 'grad', o: op, g: { t: 'linear', a: Math.round(Math.atan2(y2 - y1, x2 - x1) * 180 / Math.PI), s: st } };
    }
    function add(nodes, name) {
      var fill = null, ln = null, mk = '', shifted = false;
      nodes.forEach(function (n0) {
        var n = n0.cloneNode(true), list = [n].concat(Array.prototype.slice.call(n.querySelectorAll('*')));
        list.forEach(function (e) {
          var f = e.getAttribute('fill'), fo = e.getAttribute('fill-opacity'), sk = e.getAttribute('stroke');
          if (f != null && f !== 'none' && e.localName !== 'g' && e.localName !== 'svg') {
            var m = /^url\(#([^)]+)\)$/.exec(f), pnt = m ? gradPaint(m[1], fo == null ? 1 : +fo) : { m: 'solid', c: tok(f), o: fo == null ? 1 : +fo };
            if (!fill && pnt) fill = pnt;
            e.removeAttribute('fill'); e.removeAttribute('fill-opacity'); e.setAttribute('data-f', '1');
          } else if (e.localName !== 'g' && e.localName !== 'svg' && /^(rect|circle|ellipse|polygon|path|line|polyline)$/.test(e.localName) && f == null && !sk) { e.setAttribute('data-f', '1'); if (!fill) fill = { m: 'solid', c: '@k' }; }
          if (sk && sk !== 'none') {
            if (!ln) ln = { m: 'solid', c: tok(sk), w: +(e.getAttribute('stroke-width') || 1), o: e.getAttribute('stroke-opacity') == null ? 1 : +e.getAttribute('stroke-opacity') };
            e.removeAttribute('stroke'); e.removeAttribute('stroke-width'); e.removeAttribute('stroke-opacity'); e.setAttribute('data-s', '1');
          }
        });
        var of0 = offs.get(n0);
        var cp = n.getAttribute && n.getAttribute('clip-path'), cm = cp && /url\(#([^)]+)\)/.exec(cp), cr = cm && clips[cm[1]] && clips[cm[1]].querySelector('rect');
        if (cr) {
          n.removeAttribute('clip-path');
          var cx = +cr.getAttribute('x') || 0, cy = +cr.getAttribute('y') || 0, cw = +cr.getAttribute('width'), ch = +cr.getAttribute('height');
          mk += (of0 ? '<g transform="translate(' + of0[0] + ',' + of0[1] + ')">' : '') + '<svg x="' + cx + '" y="' + cy + '" width="' + cw + '" height="' + ch + '" overflow="hidden"><g transform="translate(' + (-cx) + ',' + (-cy) + ')">' + new XMLSerializer().serializeToString(n) + '</g></svg>' + (of0 ? '</g>' : '');
        } else mk += of0 && (of0[0] || of0[1]) ? '<g transform="translate(' + of0[0] + ',' + of0[1] + ')">' + new XMLSerializer().serializeToString(n) + '</g>' : new XMLSerializer().serializeToString(n);
        if (of0 && (of0[0] || of0[1])) shifted = true;
      });
      mk = mk.replace(/ xmlns(:\w+)?="[^"]*"/g, '');
      mh.innerHTML = mk;
      var bb = null;
      try { var b = mh.getBBox ? null : null, mr = mh.getBoundingClientRect(); Array.prototype.forEach.call(mh.children, function (c) { var r = shifted ? (function (q) { return { x: q.left - mr.left, y: q.top - mr.top, width: q.width, height: q.height }; })(c.getBoundingClientRect()) : c.getBBox(); bb = bb ? { x1: Math.min(bb.x1, r.x), y1: Math.min(bb.y1, r.y), x2: Math.max(bb.x2, r.x + r.width), y2: Math.max(bb.y2, r.y + r.height) } : { x1: r.x, y1: r.y, x2: r.x + r.width, y2: r.y + r.height }; }); } catch (er) { bb = null; }
      if (!bb) return;
      var pad = ln ? ln.w / 2 : 0;
      bb.x1 -= pad; bb.y1 -= pad; bb.x2 += pad; bb.y2 += pad;
      var lim = dz.kind === 'panels' ? 0 : 200, x1 = Math.max(-lim, bb.x1), y1 = Math.max(-lim, bb.y1), x2 = Math.min(W + lim, bb.x2), y2 = Math.min(H + lim, bb.y2);
      if (x2 - x1 < 1) { x1 -= 0.5; x2 += 0.5; } if (y2 - y1 < 1) { y1 -= 0.5; y2 += 0.5; }
      var w = Math.round((x2 - x1) * 100) / 100, h = Math.round((y2 - y1) * 100) / 100;
      if (w * h < 2) return;
      cnt++;
      out.push({ id: 'a' + Math.random().toString(36).slice(2, 9), k: 'vec', art: 1, behind: 1, clip: dz.kind === 'panels' && (bb.x1 < x1 - 0.5 || bb.y1 < y1 - 0.5 || bb.x2 > x2 + 0.5 || bb.y2 > y2 + 0.5) ? 1 : undefined, pg: pg, x: Math.round(x1 * 100) / 100, y: Math.round(y1 * 100) / 100, w: w, h: h, bx: [x1, y1, w, h].map(function (v) { return Math.round(v * 100) / 100; }), svg: mk, fill: fill || { m: 'none' }, ln: ln || undefined, name: prefix + ' · ' + name });
    }
    var i = 0;
    while (i < kids.length) {
      var n = kids[i], nm = n.localName;
      if (nm === 'rect' && +(n.getAttribute('x') || 0) === 0 && +(n.getAttribute('y') || 0) === 0 && +n.getAttribute('width') >= W - 1 && +n.getAttribute('height') >= H - 1) { i++; continue; }   /* the page colour stays the page */
      if (nm === 'circle' && +n.getAttribute('r') <= 4) {
        var run = [n], j = i + 1;
        while (j < kids.length && kids[j].localName === 'circle' && kids[j].getAttribute('fill') === n.getAttribute('fill') && kids[j].getAttribute('r') === n.getAttribute('r')) run.push(kids[j++]);
        if (run.length >= 4) { add(run, 'Dots'); i = j; continue; }
      }
      add([n], n.getAttribute('clip-path') ? 'Pattern' : ({ rect: 'Box', circle: 'Circle', ellipse: 'Circle', polygon: 'Shape', path: 'Shape', line: 'Line', polyline: 'Line', g: 'Group' })[nm] || 'Shape');
      i++;
    }
  }
  try {
    if (dz.kind === 'single') { var L0 = (D.LAYOUT[dz.layout] || D.LAYOUT.flyerBold)(doc, t, sz.w, sz.h); conv(L0.svg, 0, 'Design'); }
    else if (dz.kind === 'panels') { D.panelSpec(doc, t, dz, sz).pages.forEach(function (pg, i) { conv(pg.svg, i, 'Design'); }); }
    else {
      if (dz.kind === 'flow' && dz.cover) conv(D.coverSpec(doc, t, dz, sz).svg, 'c', 'Cover design');
      if (dz.first && D.ART.head[dz.first]) conv(D.ART.head[dz.first](t, sz.w, sz.h), 0, 'Letterhead');
      if (D.ART.page[dz.page] && dz.page !== 'none') conv(D.ART.page[dz.page](t, sz.w, sz.h, dz), D.isBand(dz) ? 0 : 'm', D.isBand(dz) ? 'Header band' : 'Page design');
    }
  } finally { mh.remove(); }
  doc.layers = out.concat((doc.layers || []).filter(function (L) { return L && !L.art; }));
  doc.artv = 1; doc.artTpl = D.artKey(doc);
  return doc;
};

/* ---------------- logo (same place on every page) ---------------- */
/* The logo works like the presentation's: the cover (or the one-page design) and the other pages each have their own
 * position (0-100 % top to bottom, left to right), size (% of the page width), an optional badge behind it
 * (darker ... see-through ... lighter) with a glow, and the other pages may use a second, shorter logo.
 * doc.logo = { src, ar, src2, ar2, cv:{on,x,y,s,badge,bc,glow}, pg:{on,x,y,s,badge,bc,glow}, op, hide } */
D.LOGO_DEF = { cv: { on: 1, x: 0, y: 0, s: 20, badge: 0, bc: 50, glow: 0 }, pg: { on: 1, x: 100, y: 0, s: 14, badge: 0, bc: 50, glow: 0 } };
D.logoCfg = function (doc, which) {
  var L = doc && doc.logo; if (!L || !L.src || L.hide) return null;
  var key = which === 'cover' ? 'cv' : 'pg', c = Object.assign({}, D.LOGO_DEF[key], L[key] || {});
  if (!L.cv && !L.pg && (L.pos || L.w)) {     /* logos saved by the first version: corner + width in px */
    var pos = L.pos || 'tr'; c.x = /l$/.test(pos) ? 0 : 100; c.y = /^t/.test(pos) ? 0 : 100; c.s = Math.round(D.num(L.w, 120) / 7.94);
  }
  if (c.on === 0 || c.on === false) return null;
  var two = which !== 'cover' && L.src2;
  return { src: two ? L.src2 : L.src, ar: D.num(two ? L.ar2 : L.ar, 0.4, 0.02, 20), x: D.num(c.x, 0, 0, 100), y: D.num(c.y, 0, 0, 100), s: D.num(c.s, 15, 2, 100), badge: !!c.badge, bc: D.num(c.bc, 50, 0, 100), glow: D.num(c.glow, 0, 0, 100), op: D.num(L.op, 1, 0, 1) };
};
D.logoGeo = function (cfg, sz) {
  var m = 28, w = Math.max(16, sz.w * cfg.s / 100), h = w * cfg.ar, pad = cfg.badge ? Math.round(Math.min(w, h) * 0.18 + 8) : 0;
  var W = Math.min(sz.w, w + 2 * pad), H = h + 2 * pad;
  if (W >= sz.w) { w = sz.w - 2 * pad; h = w * cfg.ar; H = h + 2 * pad; }
  var x = m + cfg.x / 100 * Math.max(0, sz.w - 2 * m - W), y = m + cfg.y / 100 * Math.max(0, sz.h - 2 * m - H);
  if (W > sz.w - 2 * m) x = (sz.w - W) / 2;
  return { x: x, y: y, W: W, H: H, w: w, h: h, pad: pad };
};
D.logoBadge = function (cfg) {
  if (!cfg.badge) return null;
  var v = cfg.bc;
  if (Math.abs(v - 50) < 1) return null;
  return v < 50 ? { c: '#000000', o: (50 - v) / 50 * 0.85 } : { c: '#FFFFFF', o: (v - 50) / 50 * 0.95 };
};
D.logoBox = function (doc, sz, which) { var c = D.logoCfg(doc, which); return c ? D.logoGeo(c, sz) : null; };
D.logoHTML = function (doc, sz, which) {
  var c = D.logoCfg(doc, which === true ? 'cover' : which || 'page'); if (!c) return '';
  var g = D.logoGeo(c, sz), b = D.logoBadge(c), gl = c.badge && c.glow ? c.glow : 0;
  var bg = b ? 'background:' + D.rgba(b.c, b.o) + ';' : '', sh = gl ? 'box-shadow:0 0 ' + Math.round(gl * 0.4) + 'px ' + Math.round(gl * 0.08) + 'px ' + D.rgba(b && b.c === '#000000' ? '#000000' : '#FFFFFF', gl / 100 * 0.7) + ';' : '';
  return '<div class="dsd-logo" data-logo="' + (which === 'cover' || which === true ? 'cover' : 'page') + '" style="left:' + r2(g.x) + 'px;top:' + r2(g.y) + 'px;width:' + r2(g.W) + 'px;height:' + r2(g.H) + 'px;padding:' + g.pad + 'px;border-radius:' + (c.badge ? Math.round(g.pad * 0.9 + 4) : 0) + 'px;' + bg + sh + (c.op < 1 ? 'opacity:' + c.op + ';' : '') + '">' +
    '<img alt="Logo" src="' + D.esc(c.src) + '" style="display:block;width:100%;height:100%;object-fit:contain"></div>';
  function r2(v) { return Math.round(v * 100) / 100; }
};
D.rgba = function (hex, a) { var h = String(hex || '#000').replace('#', ''); if (h.length === 3) h = h.replace(/./g, '$&$&'); return 'rgba(' + parseInt(h.slice(0, 2), 16) + ',' + parseInt(h.slice(2, 4), 16) + ',' + parseInt(h.slice(4, 6), 16) + ',' + Math.round(a * 1000) / 1000 + ')'; };
/** the logo with its badge as one PNG for Word: {data, w, h, x, y} in page pixels */
D.logoPng = function (doc, sz, which) {
  var c = D.logoCfg(doc, which); if (!c) return Promise.resolve(null);
  var g = D.logoGeo(c, sz), b = D.logoBadge(c), gl = c.badge && c.glow ? c.glow : 0, P = gl ? Math.round(gl * 0.5 + 4) : 0;
  return D.toDataURI(c.src).then(function (uri) {
    var W = g.W + 2 * P, H = g.H + 2 * P, rr = c.badge ? Math.round(g.pad * 0.9 + 4) : 0;
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '">' +
      (gl ? '<defs><filter id="g" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="' + (gl * 0.2) + '"/></filter></defs><rect x="' + P + '" y="' + P + '" width="' + g.W + '" height="' + g.H + '" rx="' + rr + '" fill="' + (b && b.c === '#000000' ? '#000000' : '#FFFFFF') + '" fill-opacity="' + (gl / 100 * 0.7) + '" filter="url(#g)"/>' : '') +
      (b ? '<rect x="' + P + '" y="' + P + '" width="' + g.W + '" height="' + g.H + '" rx="' + rr + '" fill="' + b.c + '" fill-opacity="' + b.o + '"/>' : '') +
      '<image x="' + (P + g.pad) + '" y="' + (P + g.pad) + '" width="' + g.w + '" height="' + g.h + '" preserveAspectRatio="xMidYMid meet" href="' + uri + '" xlink:href="' + uri + '"' + (c.op < 1 ? ' opacity="' + c.op + '"' : '') + '/></svg>';
    return D.svgToPng(svg, W, H, 3).then(function (by) { return { data: by, w: W, h: H, x: g.x - P, y: g.y - P }; });
  }).catch(function () { return null; });
};
/* ---------------- cover ---------------- */
D.coverSpec = function (doc, t, dz, sz) {
  var fn = D.ART.cover[dz.cover]; if (!fn) return null;
  var img = doc.cover && doc.cover.src;
  return fn(t, sz.w, sz.h, img);
};
D.coverHTML = function (doc, o, t, dz, sz) {
  var c = D.coverSpec(doc, t, dz, sz), z = c.zone, mz = z.meta || {};
  if (D.artOn(doc)) c = { svg: D.artBg(c.svg), zone: c.zone };
  var tl = String(doc.title || '').length, cts = Math.round((tl > 60 ? 30 : tl > 38 ? 36 : tl > 22 ? 42 : 48) * (z.ts || 1));
  var h = '<div class="dsd-page dsd-cover" data-page="cover" style="width:' + sz.w + 'px;height:' + sz.h + 'px;--cts:' + cts + 'pt">' +
    '<div class="dsd-art" style="background-image:' + artURL(c.svg) + '"></div>' +
    '<div class="dsd-cz' + (z.dark ? ' dark' : '') + '" style="' + (z.dark ? D.darkAt(t) + ';' : '') + 'left:' + z.x + 'px;top:' + z.y + 'px;width:' + z.w + 'px;text-align:' + z.align + '">' +
    (doc.kicker != null ? edDoc(o, 'div', 'k', 'kicker', doc.kicker, 'Document type') : '') + edDoc(o, 'div', 't', 'title', doc.title, 'Document title') +
    (doc.subtitle != null ? edDoc(o, 'div', 's', 'subtitle', doc.subtitle, 'Subtitle') : '') + '</div>' +
    '<div class="dsd-cz' + (mz.dark ? ' dark' : '') + '" style="left:' + mz.x + 'px;top:' + mz.y + 'px;width:' + mz.w + 'px;text-align:' + mz.align + '"><div class="m">' +
    edDoc(o, 'b', '', 'author', doc.author, 'Author') + '</div><div class="m">' + edDoc(o, 'span', '', 'org', doc.org, 'Organisation') + '</div><div class="m">' + edDoc(o, 'span', '', 'date', doc.date, 'Date') + '</div></div>';
  if (o.edit && z.photo) h += '<button type="button" class="dsd-cover-pic" data-act="cover-pic" style="position:absolute;right:16px;top:16px">Change cover photo</button>';
  return h + '</div>';
};
D.coverCss = function (doc, f, t) {
  var s = (doc.cst || {})[f]; if (!s) return '';
  var css = D.blockBox({ t: 'p', st: s }, t || D.look(doc)).style;
  if (s.tc) css += 'color:' + D.col(s.tc, t || D.look(doc)) + ';';
  if (s.fs) css += 'font-size:' + D.num(s.fs, 12, 4, 300) + 'pt;';
  return css.replace(/"/g, "'");
};
function edDoc(o, tag, cls, f, val, ph) {
  var cd = D._cdoc || {};
  if ((cd.chide || {})[f]) return '';
  if ((cd.cfree || {})[f]) return '<' + tag + ' class="' + cls + '" style="visibility:hidden;pointer-events:none' + (cd.cst && cd.cst[f] ? ';' + D.coverCss(cd, f) : '') + '" aria-hidden="true">' + D.esc(val || '') + '</' + tag + '>';      /* moved out as a free text box: its place stays */
  var cs = cd.cst && cd.cst[f] ? D.coverCss(cd, f) : '';
  return '<' + tag + ' class="' + cls + '"' + (cs ? ' style="' + cs + '"' : '') + (o.edit ? ' contenteditable="true" data-df="' + f + '" data-ph="' + D.esc(ph) + '"' : '') + '>' + D.esc(val || '') + '</' + tag + '>';
}

/* ---------------- whole document ---------------- */
/**
 * Renders the document into `host`. Returns {layout(), pages}. o: {edit, images, gap}
 */
D.render = function (host, doc, o) {
  o = o || {};
  var t = D.look(doc), dz = D.design(doc.tpl), sz = D.size(doc), gap = o.gap == null ? 28 : o.gap;
  var ctx = { o: o, dz: dz, doc: doc, t: t, images: o.images || {}, h1n: 0 };
  D._cdoc = doc;
  host.innerHTML = '';
  var root = document.createElement('div');
  root.className = 'dsd-doc st-' + dz.head + (o.edit ? ' dsd-edit' : '');
  root.setAttribute('style', vars(t, dz) + ';gap:' + gap + 'px');
  host.appendChild(root);

  if (dz.kind === 'single') return D.renderSingle(root, doc, ctx, t, dz, sz);
  if (dz.kind === 'panels') return D.renderPanels(root, doc, ctx, t, dz, sz, gap);

  if (dz.kind === 'flow' && dz.cover) root.insertAdjacentHTML('beforeend', D.coverHTML(doc, o, t, dz, sz));

  var flow = document.createElement('div');
  flow.className = 'dsd-flow' + (dz.kind === 'resume' ? ' dsd-resume ' + resumeClass(dz) : '');
  flow.style.width = sz.w + 'px';
  root.appendChild(flow);
  var sheets = document.createElement('div'); sheets.className = 'dsd-sheets'; flow.appendChild(sheets);
  var layBack = document.createElement('div'); layBack.className = 'dsd-lay dsd-lay-b'; flow.appendChild(layBack);

  // header blocks (letterhead / form title) sit in the page-1 header area, over the letterhead art
  var blocks = doc.blocks || [], hb = [], k = 0;
  if (dz.kind === 'flow' && !dz.cover) { while (k < blocks.length && (blocks[k].t === 'lh' || blocks[k].t === 'title')) hb.push(blocks[k++]); }
  hb = hb.filter(function (x) { return !x.hide; });
  var bodyBlocks = blocks.slice(k).filter(function (x) { return !x.hide; });
  var hbEl = null, autoHb = false;
  if (!hb.length && dz.kind === 'flow' && !dz.cover && dz.first) { autoHb = true; hb = [{ t: '_mast' }]; }
  if (hb.length) {
    hbEl = document.createElement('div');
    var dark = dz.hbDark != null ? dz.hbDark : dz.first === 'block';
    hbEl.className = 'dsd-hb' + (dark ? ' dark' : '');
    hbEl.style.cssText = 'position:absolute;left:' + dz.m.l + 'px;width:' + (sz.w - dz.m.l - dz.m.r) + 'px;top:' + (dz.hbTop || (dz.first === 'block' ? 40 : 44)) + 'px;z-index:2;' + (dz.hbAlign === 'center' || dz.first === 'classic' ? 'text-align:center' : '');
    hbEl.innerHTML = autoHb ? '<div class="dsd-title">' + (doc.kicker ? edDoc(o, 'div', 'ts', 'kicker', doc.kicker, 'Issue / date') : '') + edDoc(o, 'div', 'tt', 'title', doc.title, 'Title') +
      (doc.subtitle != null ? edDoc(o, 'div', 'ts', 'subtitle', doc.subtitle, 'Subtitle') : '') + '</div>' : hb.map(function (b) { return D.blockHTML(b, ctx); }).join('');
    flow.appendChild(hbEl);
  }

  var cols = [];
  if (dz.kind === 'resume' && (dz.side === 'dark' || dz.side === 'tint')) {
    var sw = dz.sw, gapc = 28;
    cols.push({ x: dz.m.l, w: sw - dz.m.l - 22, blocks: bodyBlocks.filter(function (b) { return b.side || b.t === 'r_head' && false; }), cls: 'dsd-side' });
    cols.push({ x: sw + gapc, w: sz.w - sw - gapc - dz.m.r, blocks: bodyBlocks.filter(function (b) { return !b.side; }), cls: 'dsd-main' });
    // contacts live in the sidebar: split r_head into the main column (name) + sidebar (contacts)
  } else {
    cols.push({ x: dz.m.l, w: sz.w - dz.m.l - dz.m.r, blocks: bodyBlocks, cls: 'dsd-main' });
  }
  cols.forEach(function (c) {
    var el = document.createElement('div');
    el.className = 'dsd-col ' + c.cls;
    el.style.left = c.x + 'px'; el.style.width = c.w + 'px';
    el.innerHTML = c.blocks.map(function (b) { return D.blockHTML(b, ctx); }).join('');
    flow.appendChild(el);
    c.el = el;
  });
  var layFront = document.createElement('div'); layFront.className = 'dsd-lay dsd-lay-f'; flow.appendChild(layFront);
  var coverEl = root.querySelector('.dsd-cover'), coverB = null, coverF = null;
  if (coverEl && doc.logo && doc.logo.src) coverEl.insertAdjacentHTML('beforeend', D.logoHTML(doc, sz, 'cover'));
  if (coverEl) { coverB = document.createElement('div'); coverB.className = 'dsd-lay dsd-lay-b'; coverEl.insertBefore(coverB, coverEl.children[1] || null); coverF = document.createElement('div'); coverF.className = 'dsd-lay dsd-lay-f'; coverEl.appendChild(coverF); }

  var api = {
    root: root, pages: 0,
    layout: function () {
      var pitch = sz.h + gap, top0 = dz.m.t, bot = dz.m.b, maxBottom = 0;
      var firstTop = top0;
      if (hbEl) firstTop = Math.max(top0, hbEl.offsetTop + hbEl.offsetHeight + 26);
      if (D.isBand(dz)) firstTop = Math.max(firstTop, (dz.hh || 170) + 34);
      cols.forEach(function (c) {
        Array.prototype.forEach.call(c.el.querySelectorAll(':scope > .dsd-spacer'), function (s) { s.remove(); });
        var kids = Array.prototype.slice.call(c.el.children);
        var bandTop = D.isBand(dz) && c.cls === 'dsd-main' && kids[0] && kids[0].getAttribute('data-t') === 'r_head';
        kids.forEach(function (el, i) {
          var y = el.offsetTop, hgt = el.offsetHeight, pg = Math.max(0, Math.floor(y / pitch));
          var areaTop = pg * pitch + (pg === 0 ? (bandTop && i === 0 ? 34 : firstTop) : top0), areaBot = pg * pitch + sz.h - bot;
          var push = 0;
          var forceNew = el.getAttribute('data-t') === 'pb' || (el.classList.contains('chap') && i > 0);
          if (forceNew) { push = (pg + 1) * pitch + top0 - y; if (el.getAttribute('data-t') === 'pb') { insertSpacer(el.nextSibling, c.el, push); return; } }
          else if (y < areaTop) push = areaTop - y;
          else if (y + hgt > areaBot && hgt < sz.h - top0 - bot) push = (pg + 1) * pitch + top0 - y;
          else if (el.classList.contains('hd') && y + hgt + 70 > areaBot) push = (pg + 1) * pitch + top0 - y;
          if (push > 0) insertSpacer(el, c.el, push);
        });
        var last = c.el.lastElementChild;
        if (last) maxBottom = Math.max(maxBottom, last.offsetTop + last.offsetHeight);
      });
      var n = Math.max(1, Math.ceil((maxBottom + bot) / pitch - 0.0001));
      flow.style.height = (n * pitch - gap) + 'px';
      sheets.innerHTML = '';
      var runTitle = (doc.title || ''), artOn = D.artOn(doc);
      for (var p = 0; p < n; p++) {
        var art = '';
        if (p === 0 && dz.first && D.ART.head[dz.first]) art = D.ART.head[dz.first](t, sz.w, sz.h);
        else if (D.ART.page[dz.page]) art = D.ART.page[dz.page](t, sz.w, sz.h, dz);
        if (p > 0 && D.isBand(dz)) art = '';
        if (artOn) art = D.artBg(art);
        var sh = '<div class="dsd-page" style="top:' + (p * pitch) + 'px;width:' + sz.w + 'px;height:' + sz.h + 'px"><div class="dsd-art" style="background-image:' + artURL(art) + '"></div>';
        if (dz.kind === 'flow' && !(p === 0 && hb.length) && runTitle && dz.head !== 'chapter') sh += '<div class="dsd-run" style="left:' + dz.m.l + 'px;right:' + dz.m.r + 'px;top:' + Math.max(30, dz.m.t - 52) + 'px;text-align:right"><span>' + D.esc(runTitle) + '</span></div>';
        if (dz.kind === 'flow' && n + (dz.cover ? 1 : 0) > 1) sh += '<div class="dsd-pn" style="left:' + dz.m.l + 'px;right:' + dz.m.r + 'px;bottom:' + Math.max(26, bot - 52) + 'px;text-align:' + (dz.page === 'leftrail' || dz.page === 'siderail' ? 'left' : 'right') + '"><span>' + (p + 1) + '</span></div>';
        sh += D.logoHTML(doc, sz, 'page');
        sheets.insertAdjacentHTML('beforeend', sh + '</div>');
      }
      api.pages = n + (dz.cover && dz.kind === 'flow' ? 1 : 0);
      api.flowPages = n; api.pitch = pitch;
      api.drawLayers();
      return api.pages;
    }
  };
  api.flow = flow; api.cover = coverEl;
  api.drawLayers = function () {
    layBack.innerHTML = ''; layFront.innerHTML = ''; if (coverB) { coverB.innerHTML = ''; coverF.innerHTML = ''; }
    var n = api.flowPages || 1, pitch = api.pitch || (sz.h + gap);
    if (D.artOn(doc)) (doc.layers || []).forEach(function (L) {      /* page-design elements: one element, on every page */
      if (!L || L.pg !== 'm' || L.hide) return;
      for (var p = 0; p < n; p++) if (D.masterOn(doc, dz, p)) (L.behind ? layBack : layFront).insertAdjacentHTML('beforeend', D.layerHTML(L, t, { dy: p * pitch }).replace('<div class="dsd-L', '<div data-mp="' + p + '" class="dsd-L'));
    });
    D.placeLayers(doc, t, o, function (pg) {
      if (pg === 'c') return coverB ? { back: coverB, front: coverF, dy: 0 } : { back: layBack, front: layFront, dy: 0 };
      var p = Math.max(0, Math.min(n - 1, +pg || 0));
      return { back: layBack, front: layFront, dy: p * pitch };
    });
  };
  /** page (from 0) on which each block starts */
  api.blockPages = function () {
    var m = {}, pitch = api.pitch || (sz.h + gap);
    cols.forEach(function (c) { Array.prototype.forEach.call(c.el.children, function (el) { var id = el.getAttribute('data-id'); if (id) m[id] = Math.max(0, Math.floor(el.offsetTop / pitch)); }); });
    return m;
  };
  function insertSpacer(before, parent, h) {
    var s = document.createElement('div'); s.className = 'dsd-spacer'; s.style.height = h + 'px';
    parent.insertBefore(s, before || null);
  }
  api.layout();
  // fonts load late: lay out again when they are ready
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { api.layout(); });
  return api;
};
function resumeClass(dz) {
  if (dz.side === 'dark') return 'dsd-side-dark';
  if (D.isBand(dz)) return 'dsd-band';
  if (dz.id === 'resume-minimal' || dz.rmode === 'centre') return 'dsd-centre';
  if (dz.id === 'resume-timeline' || dz.rmode === 'timeline') return 'dsd-rt';
  return '';
}

/* ---------------- single designed page ---------------- */
D.LAYOUT = {};
D.renderSingle = function (root, doc, ctx, t, dz, sz) {
  var L = (D.LAYOUT[dz.layout] || D.LAYOUT.flyerBold)(doc, t, sz.w, sz.h);
  if (D.artOn(doc)) L = { svg: D.artBg(L.svg), zone: L.zone, titlePt: L.titlePt };
  ctx.titlePt = L.titlePt || 48;
  var z = L.zone;
  var h = '<div class="dsd-page dsd-single" style="width:' + sz.w + 'px;height:' + sz.h + 'px"><div class="dsd-art" style="background-image:' + artURL(L.svg) + '"></div>' +
    '<div class="dsd-sz' + (z.dark ? ' dark' : '') + '" style="left:' + z.x + 'px;top:' + z.y + 'px;width:' + z.w + 'px;' + (z.h ? 'height:' + z.h + 'px;' : '') + 'text-align:' + z.align + ';align-items:' + (z.align === 'center' ? 'center' : 'flex-start') + (z.css ? ';' + z.css : '') + '">' +
    (doc.blocks || []).filter(function (b) { return !b.hide; }).map(function (b) { return D.blockHTML(b, ctx); }).join('') + '</div></div>';
  root.insertAdjacentHTML('beforeend', h);
  var pageEl = root.querySelector('.dsd-single'), sb = document.createElement('div'), sf = document.createElement('div');
  if (doc.logo && doc.logo.src) pageEl.insertAdjacentHTML('beforeend', D.logoHTML(doc, sz, 'cover'));
  sb.className = 'dsd-lay dsd-lay-b'; sf.className = 'dsd-lay dsd-lay-f'; pageEl.insertBefore(sb, pageEl.children[1] || null); pageEl.appendChild(sf);
  var drawL = function () { sb.innerHTML = ''; sf.innerHTML = ''; D.placeLayers(doc, t, ctx.o, function () { return { back: sb, front: sf, dy: 0 }; }); };
  drawL();
  if (z.dark) { var zs = root.querySelector('.dsd-sz').style; zs.setProperty('--ink', '#FFFFFF'); zs.setProperty('--p', '#FFFFFF'); zs.setProperty('--s', t.a); D.darkAt(t).split(';').forEach(function (kv) { kv = kv.split(':'); zs.setProperty(kv[0], kv[1]); }); zs.setProperty('--mute', 'rgba(255,255,255,.8)'); zs.setProperty('--line', 'rgba(255,255,255,.35)'); }
  return { root: root, pages: 1, flowPages: 1, pitch: sz.h, single: true, drawLayers: drawL, blockPages: function () { var m = {}; (doc.blocks || []).forEach(function (b) { m[b.id] = 0; }); return m; }, layout: function () { drawL(); return 1; } };
};
/* ---------- free elements ("layers"): shapes, text boxes, icons, pictures, charts ----------
 * A layer sits on one page at an exact position, in front of or behind the text, like the
 * elements of the presentation editor. In Word, shapes and text boxes become real Word shapes,
 * icons / pictures / charts become pictures, all placed on the same page at the same spot.
 *
 * Layer: { id, pg ('c' = cover | page number from 0), k: 'shape'|'text'|'icon'|'image'|'chart',
 *   x, y, w, h (px on the page), rot (deg), op (0-1), hide, lock, name, grp, behind,
 *   shape, r (corner px), fill {m:'none'|'solid'|'grad', c, o, g:{t:'linear'|'radial', a, s:[[pos,c,o]]}},
 *   ln {m, c, o, w, d:'solid'|'dash'|'dot', g}, sh {c, o, b, d, a},
 *   text: tx, ff, fs (pt), fw, it, ul, st, caps, al, va, lh, ls, tc, pad,
 *   icon: ic {n, b, vw, vh}, tc;  image: src, pid, url, rr;  chart: ch {kind, title, labels, series, legend, vals, unit} }
 * Colours may be theme tokens (@p @s @a @ink @mute @tint @line @w @k) so they follow theme changes.
 */
D.SHAPES = [
  ['rect', 'Rectangle'], ['roundRect', 'Rounded box'], ['ellipse', 'Circle'], ['line', 'Line'], ['triangle', 'Triangle'],
  ['rtTriangle', 'Right triangle'], ['diamond', 'Diamond'], ['pentagon', 'Pentagon'], ['hexagon', 'Hexagon'], ['octagon', 'Octagon'],
  ['star5', 'Star'], ['rightArrow', 'Arrow'], ['leftArrow', 'Arrow left'], ['chevron', 'Chevron'], ['parallelogram', 'Parallelogram']
];
D.CHART_KINDS = [['column', 'Columns'], ['bar', 'Bars'], ['line', 'Line'], ['area', 'Area'], ['pie', 'Pie'], ['doughnut', 'Doughnut']];

/** Theme token or colour → a safe CSS colour. */
D.col = function (c, t, fb) {
  c = String(c == null ? '' : c).trim();
  if (c.charAt(0) === '@' && t) {
    var k = c.slice(1);
    if (k === 'w') return '#FFFFFF';
    if (k === 'k') return '#000000';
    if (t[k] && /^#/.test(t[k])) return t[k];
  }
  if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(c)) return c.toUpperCase();
  return fb || '#000000';
};
D.num = function (v, d, lo, hi) { v = +v; if (!isFinite(v)) v = d; if (lo != null && v < lo) v = lo; if (hi != null && v > hi) v = hi; return v; };

var _gid = 0;
function gradDef(g, t, op) {
  var id = 'g' + (++_gid) + Math.random().toString(36).slice(2, 6);
  var stops = (g && g.s && g.s.length ? g.s : [[0, '@p', 1], [100, '@a', 1]]).slice(0, 8);
  var st = stops.map(function (s) { return '<stop offset="' + D.num(s[0], 0, 0, 100) + '%" stop-color="' + D.col(s[1], t) + '" stop-opacity="' + (D.num(s[2], 1, 0, 1) * op) + '"/>'; }).join('');
  if (g && g.t === 'radial') return { id: id, def: '<radialGradient id="' + id + '" cx="50%" cy="50%" r="50%">' + st + '</radialGradient>' };
  var a = D.num(g && g.a, 0) * Math.PI / 180, cx = Math.cos(a) / 2, cy = Math.sin(a) / 2;
  return { id: id, def: '<linearGradient id="' + id + '" x1="' + (0.5 - cx) + '" y1="' + (0.5 - cy) + '" x2="' + (0.5 + cx) + '" y2="' + (0.5 + cy) + '">' + st + '</linearGradient>' };
}
/** a picture fill: p = {m:'pic', src, iw, ih, z 1-4, px/py 0-100, st (stretch), o, gb {x,y,w,h}: one picture across several shapes - the picture's box from this shape's top left corner}
 *  c = {w, h, L, bx}: the shape's size, the layer (for gb) and, for a design piece, its viewBox */
D.picSrcOK = function (s) { return /^(data:image\/(png|jpe?g|webp);base64,|https:\/\/)/.test(String(s || '')); };
D.picBox = function (p, w, h, L) {
  var gb = p.gb, b = gb ? { x: D.num(gb.x, 0), y: D.num(gb.y, 0), w: Math.max(1, D.num(gb.w, w)), h: Math.max(1, D.num(gb.h, h)) } : { x: 0, y: 0, w: w, h: h };
  var iw = D.num(p.iw, 0), ih = D.num(p.ih, 0), r = { x: b.x, y: b.y, w: b.w, h: b.h };
  if (!p.st && iw > 0 && ih > 0) { var k = Math.max(b.w / iw, b.h / ih) * D.num(p.z, 1, 1, 4); r.w = iw * k; r.h = ih * k; r.x = b.x + (b.w - r.w) * D.num(p.px, 50, 0, 100) / 100; r.y = b.y + (b.h - r.h) * D.num(p.py, 50, 0, 100) / 100; }
  return r;
};
function picPaint(p, t, c) {
  if (!D.picSrcOK(p.src) || !c) return { defs: '', attr: 'fill="' + D.col('@tint', t, '#EEF1F6') + '"' };
  var r = D.picBox(p, c.w, c.h, c.L), op = D.num(p.o, 1, 0, 1), id = 'p' + (++_gid) + Math.random().toString(36).slice(2, 6);
  if (c.bx) { var sx = c.bx[2] / c.w, sy = c.bx[3] / c.h; r = { x: c.bx[0] + r.x * sx, y: c.bx[1] + r.y * sy, w: r.w * sx, h: r.h * sy }; }
  var f = function (v) { return Math.round(v * 100) / 100; }, src = D.esc(String(p.src));
  return { defs: '<pattern id="' + id + '" patternUnits="userSpaceOnUse" x="' + f(r.x) + '" y="' + f(r.y) + '" width="' + f(r.w) + '" height="' + f(r.h) + '"><image href="' + src + '" xlink:href="' + src + '" x="0" y="0" width="' + f(r.w) + '" height="' + f(r.h) + '" preserveAspectRatio="none"' + (op < 1 ? ' opacity="' + op + '"' : '') + '/></pattern>', attr: 'fill="url(#' + id + ')"' };
}
/** fill / outline paint → {defs, attr} */
function paint(p, t, what, c) {
  if (!p || p.m === 'none' || !p.m) return { defs: '', attr: what + '="none"' };
  if (p.m === 'pic') return what === 'fill' ? picPaint(p, t, c) : { defs: '', attr: what + '="none"' };
  if (p.m === 'grad') { var g = gradDef(p.g, t, 1); return { defs: g.def, attr: what + '="url(#' + g.id + ')"' }; }
  return { defs: '', attr: what + '="' + D.col(p.c, t) + '"' + (p.o != null && p.o < 1 ? ' ' + what + '-opacity="' + D.num(p.o, 1, 0, 1) + '"' : '') };
}
D.shapePoints = function (s, w, h, r) {
  var ss = Math.min(w, h), P = function (a) { return a.map(function (p) { return (+p[0]).toFixed(2) + ',' + (+p[1]).toFixed(2); }).join(' '); };
  switch (s) {
    case 'triangle': return P([[w / 2, 0], [w, h], [0, h]]);
    case 'rtTriangle': return P([[0, 0], [w, h], [0, h]]);
    case 'diamond': return P([[w / 2, 0], [w, h / 2], [w / 2, h], [0, h / 2]]);
    case 'pentagon': return P([[w / 2, 0], [w, h * 0.3804], [w * 0.809, h], [w * 0.191, h], [0, h * 0.3804]]);
    case 'hexagon': var hx = ss * 0.25; return P([[hx, 0], [w - hx, 0], [w, h / 2], [w - hx, h], [hx, h], [0, h / 2]]);
    case 'octagon': var o = ss * 0.29289; return P([[o, 0], [w - o, 0], [w, o], [w, h - o], [w - o, h], [o, h], [0, h - o], [0, o]]);
    case 'parallelogram': var q = ss * 0.25; return P([[q, 0], [w, 0], [w - q, h], [0, h]]);
    case 'chevron': var c = ss * 0.5; return P([[0, 0], [w - c, 0], [w, h / 2], [w - c, h], [0, h], [c, h / 2]]);
    case 'rightArrow': var hd = ss * 0.5; return P([[0, h * 0.25], [w - hd, h * 0.25], [w - hd, 0], [w, h / 2], [w - hd, h], [w - hd, h * 0.75], [0, h * 0.75]]);
    case 'leftArrow': var hl = ss * 0.5; return P([[w, h * 0.25], [hl, h * 0.25], [hl, 0], [0, h / 2], [hl, h], [hl, h * 0.75], [w, h * 0.75]]);
    case 'star5':
      var pts = [], cx = w / 2, cy = h / 2;
      for (var i = 0; i < 10; i++) { var a = -Math.PI / 2 + i * Math.PI / 5, rr = i % 2 ? 0.382 : 1; pts.push([cx + Math.cos(a) * cx * rr, cy + Math.sin(a) * cy * rr * 1.0515 + h * 0.025]); }
      return P(pts);
  }
  return '';
};
/** One shape as an SVG element with the given paint attributes. */
D.shapeEl = function (s, w, h, r, attrs) {
  if (s === 'ellipse') return '<ellipse cx="' + w / 2 + '" cy="' + h / 2 + '" rx="' + w / 2 + '" ry="' + h / 2 + '" ' + attrs + '/>';
  if (s === 'line') return '<line x1="0" y1="' + h / 2 + '" x2="' + w + '" y2="' + h / 2 + '" ' + attrs + '/>';
  if (s === 'rect' || s === 'roundRect' || !s) { var rr = s === 'roundRect' ? Math.min(D.num(r, 12, 0), Math.min(w, h) / 2) : 0; return '<rect x="0" y="0" width="' + w + '" height="' + h + '"' + (rr ? ' rx="' + rr + '"' : '') + ' ' + attrs + '/>'; }
  return '<polygon points="' + D.shapePoints(s, w, h, r) + '" ' + attrs + '/>';
};
function shadowDef(sh, t) {
  if (!sh) return null;
  var id = 'f' + (++_gid) + Math.random().toString(36).slice(2, 6), a = D.num(sh.a, 45) * Math.PI / 180, d = D.num(sh.d, 6, 0, 200);
  return { id: id, def: '<filter id="' + id + '" x="-50%" y="-50%" width="200%" height="200%"><feDropShadow dx="' + (Math.cos(a) * d).toFixed(2) + '" dy="' + (Math.sin(a) * d).toFixed(2) + '" stdDeviation="' + (D.num(sh.b, 8, 0, 200) / 2) + '" flood-color="' + D.col(sh.c, t, '#000000') + '" flood-opacity="' + D.num(sh.o, 0.35, 0, 1) + '"/></filter>' };
}
/** Padding around a layer's box so outlines and shadows are not cut off. */
D.layerPad = function (L) {
  var p = 2 + (L.ln && L.ln.m && L.ln.m !== 'none' ? D.num(L.ln.w, 2, 0, 60) : 0);
  if (L.sh) p += D.num(L.sh.b, 8, 0, 200) * 1.5 + D.num(L.sh.d, 6, 0, 200);
  return Math.ceil(p);
};
D.hasBox = function (L) { return !!((L.fill && L.fill.m && L.fill.m !== 'none') || (L.ln && L.ln.m && L.ln.m !== 'none') || L.sh); };
/** Standalone SVG of a layer's drawing (without text). */
D.layerSVG = function (L, t, opt) {
  opt = opt || {};
  var w = Math.max(1, D.num(L.w, 100)), h = Math.max(1, D.num(L.h, 100)), P = opt.noPad ? 0 : D.layerPad(L), defs = '', body = '';
  var sd = shadowDef(L.sh, t), filt = sd ? ' filter="url(#' + sd.id + ')"' : '';
  if (sd) defs += sd.def;
  if (L.k === 'shape' || L.k === 'text') {
    if (L.k === 'text' && !D.hasBox(L)) return '';
    var f = paint(L.fill, t, 'fill', { w: w, h: h, L: L }), s = paint(L.ln, t, 'stroke'), sw = L.ln && L.ln.m && L.ln.m !== 'none' ? D.num(L.ln.w, 2, 0, 60) : 0;
    var dash = L.ln && L.ln.d === 'dash' ? ' stroke-dasharray="' + sw * 3 + ',' + sw * 2 + '"' : L.ln && L.ln.d === 'dot' ? ' stroke-dasharray="' + sw + ',' + sw * 1.5 + '" stroke-linecap="round"' : '';
    if (L.shape === 'line') { s = paint(L.ln && L.ln.m !== 'none' ? L.ln : { m: 'solid', c: L.fill && L.fill.c || '@p' }, t, 'stroke'); sw = sw || 3; }
    defs += f.defs + s.defs;
    body = D.shapeEl(L.k === 'text' ? (L.shape || 'rect') : L.shape, w, h, L.r, f.attr + ' ' + s.attr + ' stroke-width="' + sw + '"' + dash + ' stroke-linejoin="round"' + filt);
  } else if (L.k === 'icon') {
    var ic = L.ic || {}, vw = D.num(ic.vw, 24, 1), vh = D.num(ic.vh, 24, 1), sc = Math.min(w / vw, h / vh);
    body = '<g' + filt + '><g transform="translate(' + ((w - vw * sc) / 2) + ',' + ((h - vh * sc) / 2) + ') scale(' + sc + ')" color="' + D.col(L.tc, t, '#1C2333') + '" fill="currentColor">' +
      D.cleanSvgBody(ic.b).replace(/currentColor/g, D.col(L.tc, t, '#1C2333')) + '</g></g>';
  } else if (L.k === 'image') {
    var cid = 'c' + (++_gid), rr = Math.min(D.num(L.rr, 0, 0), Math.min(w, h) / 2), ln = paint(L.ln, t, 'stroke'), lw = L.ln && L.ln.m && L.ln.m !== 'none' ? D.num(L.ln.w, 2, 0, 60) : 0;
    defs += '<clipPath id="' + cid + '"><rect x="0" y="0" width="' + w + '" height="' + h + '" rx="' + rr + '"/></clipPath>' + ln.defs;
    var src = String(L.src || '');
    body = '<g' + filt + '>' + (/^(data:image\/|https:\/\/)/.test(src) ? '<image href="' + D.esc(src) + '" xlink:href="' + D.esc(src) + '" x="0" y="0" width="' + w + '" height="' + h + '" preserveAspectRatio="xMidYMid slice" clip-path="url(#' + cid + ')"/>'
      : '<rect width="' + w + '" height="' + h + '" rx="' + rr + '" fill="' + D.col('@tint', t, '#EEF1F6') + '"/>') + '</g>' +
      (lw ? '<rect x="0" y="0" width="' + w + '" height="' + h + '" rx="' + rr + '" fill="none" ' + ln.attr + ' stroke-width="' + lw + '"/>' : '');
  } else if (L.k === 'chart') {
    body = '<g' + filt + '>' + D.chartSVG(L.ch || {}, t, w, h, { inner: true, ff: t && t.body }) + '</g>';
  } else if (L.k === 'vec') {
    var bx = (L.bx && L.bx.length === 4 ? L.bx : [0, 0, w, h]).map(function (v) { return D.num(v, 0); });
    var fA = L.fill && L.fill.m && L.fill.m !== 'none' ? paint(L.fill, t, 'fill', { w: w, h: h, L: L, bx: bx }) : { defs: '', attr: 'fill="none"' };
    var sOn = L.ln && L.ln.m && L.ln.m !== 'none', sA = sOn ? paint(L.ln, t, 'stroke') : null, sw0 = sOn ? D.num(L.ln.w, 1, 0, 60) : 0;
    var dsh = sOn && L.ln.d === 'dash' ? ' stroke-dasharray="' + sw0 * 3 + ',' + sw0 * 2 + '"' : sOn && L.ln.d === 'dot' ? ' stroke-dasharray="' + sw0 + ',' + sw0 * 1.5 + '" stroke-linecap="round"' : '';
    defs += fA.defs + (sA ? sA.defs : '');
    var mk = String(L.svg || '').replace(/ data-f="1"/g, ' ' + fA.attr).replace(/ data-s="1"/g, sOn ? ' ' + sA.attr + ' stroke-width="' + sw0 + '"' + dsh : ' stroke="none"');
    body = '<g' + filt + '><svg x="0" y="0" width="' + w + '" height="' + h + '" viewBox="' + bx.join(' ') + '" preserveAspectRatio="none" overflow="' + (L.clip ? 'hidden' : 'visible') + '">' + mk + '</svg></g>';
  }
  return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="' + (w + 2 * P) + '" height="' + (h + 2 * P) + '" viewBox="' + (-P) + ' ' + (-P) + ' ' + (w + 2 * P) + ' ' + (h + 2 * P) + '"' +
    (opt.style ? ' style="' + opt.style + '"' : '') + '>' + (defs ? '<defs>' + defs + '</defs>' : '') +
    (opt.op != null && opt.op < 1 ? '<g opacity="' + D.num(opt.op, 1, 0, 1) + '">' + body + '</g>' : body) + '</svg>';
};

/** Keeps only drawing elements of an icon's SVG body (icons come from Iconify or a saved document). */
D.cleanSvgBody = function (b) {
  b = String(b || '');
  if (!b) return '';
  if (typeof DOMParser === 'undefined') return '';
  var ok = /^(path|g|circle|rect|ellipse|line|polyline|polygon|defs|lineargradient|radialgradient|stop|mask|clippath|use|symbol|title)$/i;
  var doc = new DOMParser().parseFromString('<svg xmlns="http://www.w3.org/2000/svg">' + b + '</svg>', 'image/svg+xml');
  if (doc.getElementsByTagName('parsererror').length) return '';
  (function walk(n) {
    Array.prototype.slice.call(n.children).forEach(function (c) {
      if (!ok.test(c.localName)) { c.remove(); return; }
      Array.prototype.slice.call(c.attributes).forEach(function (a) {
        if (/^on/i.test(a.name) || (/href$/i.test(a.name) && !/^#/.test(a.value)) || /javascript:|url\((?!#)/i.test(a.value)) c.removeAttribute(a.name);
      });
      walk(c);
    });
  })(doc.documentElement);
  return doc.documentElement.innerHTML;
};

/* ---------------- charts ---------------- */
D.chartColors = function (t, ch) {
  var base = [t.p, t.a, t.s, D.mix(t.p, '#FFFFFF', 0.45), D.mix(t.a, '#FFFFFF', 0.4), D.mix(t.s, '#FFFFFF', 0.5), t.mute, D.mix(t.p, '#000000', 0.3)];
  return base;
};
function niceStep(range, n) {
  var raw = range / Math.max(1, n), mag = Math.pow(10, Math.floor(Math.log(raw) / Math.LN10)), f = raw / mag;
  return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * mag;
}
function fmtNum(v) {
  var a = Math.abs(v);
  if (a >= 1e9) return +(v / 1e9).toFixed(1) + 'B';
  if (a >= 1e6) return +(v / 1e6).toFixed(1) + 'M';
  if (a >= 1e4) return +(v / 1e3).toFixed(1) + 'K';
  return String(Math.round(v * 100) / 100);
}
/** A chart as SVG (standalone, or inner markup with opt.inner). */
D.chartSVG = function (ch, t, W, H, opt) {
  opt = opt || {};
  t = t || D.THEMES[0];
  W = Math.max(60, +W || 640); H = Math.max(60, +H || 360);
  var kind = ch.kind || 'column', labels = (ch.labels || []).map(String).slice(0, 24);
  var series = (ch.series || []).slice(0, 8).map(function (s, i) { return { name: String(s.name || 'Series ' + (i + 1)), c: s.c, v: (s.values || []).map(function (x) { return D.num(String(x).replace(/[^\d.\-]/g, ''), 0); }) }; });
  if (!series.length) series = [{ name: 'Series 1', v: [] }];
  var n = Math.max(labels.length, series.reduce(function (m, s) { return Math.max(m, s.v.length); }, 0));
  while (labels.length < n) labels.push('');
  var pal = D.chartColors(t), colOf = function (i, s) { return s && s.c ? D.col(s.c, t, pal[i % pal.length]) : pal[i % pal.length]; };
  var ff = D.esc(D.css(opt.ff || t.body)).replace(/&quot;/g, "'"), ink = t.ink, mute = t.mute, line = t.line;
  var fs = Math.max(9, Math.min(13, W / 48)), unit = D.esc(ch.unit || '');
  var o = '', top = 8, bot = 8, left = 8, right = 10;
  if (ch.title) { o += '<text x="' + W / 2 + '" y="' + (top + fs * 1.3) + '" text-anchor="middle" font-size="' + (fs * 1.25) + '" font-weight="700" fill="' + ink + '">' + D.esc(ch.title) + '</text>'; top += fs * 2; }
  var pie = kind === 'pie' || kind === 'doughnut';
  var legendItems = pie ? labels.map(function (l, i) { return { name: l, c: pal[i % pal.length] }; }) : series.map(function (s, i) { return { name: s.name, c: colOf(i, s) }; });
  var showLegend = ch.legend !== false && (pie || series.length > 1);
  if (showLegend) {
    var lx = [], tot = 0;
    legendItems.forEach(function (it) { var wv = 16 + it.name.length * fs * 0.56 + 14; lx.push(wv); tot += wv; });
    var rows = Math.max(1, Math.ceil(tot / (W - 20))), ly = H - bot - rows * (fs + 8) + fs, cx = (W - Math.min(tot, W - 20)) / 2, rowW = 0;
    legendItems.forEach(function (it, i) {
      if (rowW + lx[i] > W - 20 && rowW > 0) { ly += fs + 8; cx = 10; rowW = 0; }
      o += '<rect x="' + cx + '" y="' + (ly - fs * 0.75) + '" width="' + fs * 0.85 + '" height="' + fs * 0.85 + '" rx="2" fill="' + it.c + '"/><text x="' + (cx + fs * 1.2) + '" y="' + ly + '" font-size="' + fs + '" fill="' + mute + '">' + D.esc(it.name) + '</text>';
      cx += lx[i]; rowW += lx[i];
    });
    bot += rows * (fs + 8) + 6;
  }
  if (pie) {
    var vals = series[0].v.slice(0, n).map(function (v) { return Math.max(0, v); }), sum = vals.reduce(function (a, b) { return a + b; }, 0) || 1;
    var R = Math.max(10, Math.min(W - 20, H - top - bot) / 2 - 4), CX = W / 2, CY = top + (H - top - bot) / 2, a0 = -Math.PI / 2, inner = kind === 'doughnut' ? R * 0.58 : 0;
    vals.forEach(function (v, i) {
      var a1 = a0 + v / sum * Math.PI * 2, big = a1 - a0 > Math.PI ? 1 : 0, c = pal[i % pal.length];
      if (v / sum > 0.9999) { o += '<circle cx="' + CX + '" cy="' + CY + '" r="' + R + '" fill="' + c + '"/>'; }
      else if (v > 0) {
        var p1 = [CX + R * Math.cos(a0), CY + R * Math.sin(a0)], p2 = [CX + R * Math.cos(a1), CY + R * Math.sin(a1)];
        o += '<path d="M' + CX + ' ' + CY + ' L' + p1[0].toFixed(2) + ' ' + p1[1].toFixed(2) + ' A' + R + ' ' + R + ' 0 ' + big + ' 1 ' + p2[0].toFixed(2) + ' ' + p2[1].toFixed(2) + ' Z" fill="' + c + '" stroke="#FFFFFF" stroke-width="1.5"/>';
      }
      if (ch.vals !== false && v / sum > 0.04) {
        var am = (a0 + a1) / 2, rr2 = inner ? (R + inner) / 2 : R * 0.64;
        o += '<text x="' + (CX + rr2 * Math.cos(am)).toFixed(1) + '" y="' + (CY + rr2 * Math.sin(am) + fs * 0.35).toFixed(1) + '" text-anchor="middle" font-size="' + fs + '" font-weight="700" fill="' + D.onColor(c) + '">' + Math.round(v / sum * 100) + '%</text>';
      }
      a0 = a1;
    });
    if (inner) o += '<circle cx="' + CX + '" cy="' + CY + '" r="' + inner + '" fill="#FFFFFF"/>';
  } else {
    var all = []; series.forEach(function (s) { all = all.concat(s.v.slice(0, n)); });
    var mx = Math.max.apply(null, all.concat([0])), mn = Math.min.apply(null, all.concat([0]));
    if (mx === mn) mx = mn + 1;
    var step = niceStep(mx - mn, 4), hi = Math.ceil(mx / step) * step, lo = Math.floor(mn / step) * step;
    var horiz = kind === 'bar', labW = Math.min(W * 0.3, 8 + Math.max.apply(null, labels.map(function (l) { return l.length; }).concat([1])) * fs * 0.56);
    var tickW = 10 + Math.max(fmtNum(hi).length, fmtNum(lo).length) * fs * 0.6 + (unit ? unit.length * fs * 0.5 : 0);
    var px0 = left + (horiz ? labW : tickW), px1 = W - right, py0 = top + 6, py1 = H - bot - (horiz ? fs + 6 : fs * 1.6 + 6);
    var scale = function (v) { return horiz ? px0 + (v - lo) / (hi - lo) * (px1 - px0) : py1 - (v - lo) / (hi - lo) * (py1 - py0); };
    for (var v = lo; v <= hi + step / 2; v += step) {
      var p = scale(v);
      o += horiz ? '<line x1="' + p + '" y1="' + py0 + '" x2="' + p + '" y2="' + py1 + '" stroke="' + line + '" stroke-width="1"/><text x="' + p + '" y="' + (py1 + fs + 4) + '" text-anchor="middle" font-size="' + fs * 0.9 + '" fill="' + mute + '">' + fmtNum(v) + unit + '</text>'
        : '<line x1="' + px0 + '" y1="' + p + '" x2="' + px1 + '" y2="' + p + '" stroke="' + line + '" stroke-width="1"/><text x="' + (px0 - 6) + '" y="' + (p + fs * 0.35) + '" text-anchor="end" font-size="' + fs * 0.9 + '" fill="' + mute + '">' + fmtNum(v) + unit + '</text>';
    }
    var band = (horiz ? (py1 - py0) : (px1 - px0)) / Math.max(1, n), zero = scale(Math.max(lo, Math.min(0, hi)));
    labels.forEach(function (l, i) {
      var c = (horiz ? py0 : px0) + band * (i + 0.5), txt = D.esc(l.length > 18 ? l.slice(0, 17) + '…' : l);
      o += horiz ? '<text x="' + (px0 - 6) + '" y="' + (c + fs * 0.35) + '" text-anchor="end" font-size="' + fs + '" fill="' + ink + '">' + txt + '</text>'
        : '<text x="' + c + '" y="' + (py1 + fs * 1.3) + '" text-anchor="middle" font-size="' + fs + '" fill="' + ink + '">' + txt + '</text>';
    });
    if (kind === 'column' || kind === 'bar') {
      var gw = band * 0.72, bw = gw / series.length;
      series.forEach(function (s, si) {
        s.v.slice(0, n).forEach(function (val, i) {
          var st = (horiz ? py0 : px0) + band * i + (band - gw) / 2 + bw * si, p2 = scale(val), a = Math.min(p2, zero), len = Math.abs(p2 - zero), c = colOf(si, s);
          o += horiz ? '<rect x="' + a.toFixed(2) + '" y="' + st.toFixed(2) + '" width="' + len.toFixed(2) + '" height="' + (bw * 0.9).toFixed(2) + '" rx="2" fill="' + c + '"/>'
            : '<rect x="' + st.toFixed(2) + '" y="' + a.toFixed(2) + '" width="' + (bw * 0.9).toFixed(2) + '" height="' + len.toFixed(2) + '" rx="2" fill="' + c + '"/>';
          if (ch.vals !== false && bw > fs * 1.4) o += horiz ? '<text x="' + (Math.max(p2, zero) + 4).toFixed(1) + '" y="' + (st + bw * 0.45 + fs * 0.35).toFixed(1) + '" font-size="' + fs * 0.85 + '" fill="' + ink + '">' + fmtNum(val) + unit + '</text>'
            : '<text x="' + (st + bw * 0.45).toFixed(1) + '" y="' + (Math.min(p2, zero) - 4).toFixed(1) + '" text-anchor="middle" font-size="' + fs * 0.85 + '" fill="' + ink + '">' + fmtNum(val) + unit + '</text>';
        });
      });
    } else {
      series.forEach(function (s, si) {
        var c = colOf(si, s), pts = s.v.slice(0, n).map(function (val, i) { return [px0 + band * (i + 0.5), scale(val)]; });
        if (!pts.length) return;
        var d = pts.map(function (p, i) { return (i ? 'L' : 'M') + p[0].toFixed(2) + ' ' + p[1].toFixed(2); }).join(' ');
        if (kind === 'area') o += '<path d="' + d + ' L' + pts[pts.length - 1][0].toFixed(2) + ' ' + zero + ' L' + pts[0][0].toFixed(2) + ' ' + zero + ' Z" fill="' + c + '" fill-opacity="0.22"/>';
        o += '<path d="' + d + '" fill="none" stroke="' + c + '" stroke-width="' + Math.max(2, W / 260) + '" stroke-linejoin="round" stroke-linecap="round"/>';
        pts.forEach(function (p, i) {
          o += '<circle cx="' + p[0].toFixed(2) + '" cy="' + p[1].toFixed(2) + '" r="' + Math.max(2.5, W / 200) + '" fill="#FFFFFF" stroke="' + c + '" stroke-width="2"/>';
          if (ch.vals !== false && series.length < 3) o += '<text x="' + p[0].toFixed(1) + '" y="' + (p[1] - 8).toFixed(1) + '" text-anchor="middle" font-size="' + fs * 0.85 + '" fill="' + ink + '">' + fmtNum(s.v[i]) + unit + '</text>';
        });
      });
    }
    o += horiz ? '<line x1="' + zero + '" y1="' + py0 + '" x2="' + zero + '" y2="' + py1 + '" stroke="' + mute + '" stroke-width="1.2"/>' : '<line x1="' + px0 + '" y1="' + zero + '" x2="' + px1 + '" y2="' + zero + '" stroke="' + mute + '" stroke-width="1.2"/>';
  }
  var g = '<g font-family="' + ff + '">' + o + '</g>';
  if (opt.inner) return g;
  return '<svg xmlns="http://www.w3.org/2000/svg" width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '"' + (opt.style ? ' style="' + opt.style + '"' : '') + '>' + (opt.bg ? '<rect width="' + W + '" height="' + H + '" fill="' + opt.bg + '"/>' : '') + g + '</svg>';
};
/** A chart block's aspect (height / width). */
D.chartRatio = function (ch) { return ch && (ch.kind === 'pie' || ch.kind === 'doughnut') ? 0.62 : 0.56; };

/* ---------------- layers in the page view ---------------- */
D.textStyle = function (L, t) {
  var ff = D.css(L.ff || t.body).replace(/"/g, "'");
  var va = L.va === 'middle' ? 'center' : L.va === 'bottom' ? 'flex-end' : 'flex-start';
  return 'display:flex;flex-direction:column;justify-content:' + va + ';position:absolute;inset:0;padding:' + D.num(L.pad, 8, 0, 80) + 'px;' +
    '--ff:' + ff + ';font-family:' + ff + ';font-size:' + D.num(L.fs, 14, 4, 300) + 'pt;font-weight:' + (L.fw || 400) + ';' +
    (L.it ? 'font-style:italic;' : '') + 'text-decoration:' + ([L.ul ? 'underline' : '', L.st ? 'line-through' : ''].join(' ').trim() || 'none') + ';' +
    (L.caps ? 'text-transform:uppercase;' : '') + 'text-align:' + (L.al || 'left') + ';line-height:' + D.num(L.lh, 1.25, 0.6, 4) + ';letter-spacing:' + D.num(L.ls, 0, -10, 60) + 'px;' +
    'color:' + D.col(L.tc, t, t.ink) + ';white-space:pre-wrap;word-wrap:break-word;overflow:visible';
};
D.layerHTML = function (L, t, o) {
  var w = D.num(L.w, 100, 1), h = D.num(L.h, 100, 1), P = D.layerPad(L), st = 'left:' + D.num(L.x, 0) + 'px;top:' + (D.num(L.y, 0) + (o.dy || 0)) + 'px;width:' + w + 'px;height:' + h + 'px;' +
    (L.rot ? 'transform:rotate(' + D.num(L.rot, 0) + 'deg);' : '') + (L.op != null && L.op < 1 ? 'opacity:' + D.num(L.op, 1, 0, 1) + ';' : '') + (L.hide ? 'display:none;' : '');
  var svg = D.layerSVG(L, t, { style: 'position:absolute;left:' + (-P) + 'px;top:' + (-P) + 'px;pointer-events:none;overflow:visible' });
  var inner = svg;
  if (L.k === 'text') inner += '<div class="dsd-Ltx" style="' + D.textStyle(L, t) + '">' + D.esc(L.tx || '') + '</div>';
  return '<div class="dsd-L k-' + L.k + (L.lock ? ' lock' : '') + '" data-lid="' + D.esc(L.id) + '" style="' + st + '">' + inner + '</div>';
};
/** Draws the document's layers into the page view. where(pg) → {front, back, dy} or null. */
D.placeLayers = function (doc, t, o, where) {
  var on = D.artOn(doc);
  (doc.layers || []).forEach(function (L) {
    if (!L || !L.k || L.pg === 'm') return;
    if (L.art && !on) return;
    var w = where(L.pg);
    if (!w) return;
    (L.behind ? w.back : w.front).insertAdjacentHTML('beforeend', D.layerHTML(L, t, { dy: w.dy }));
  });
};
/* ---------- one designed page: art + where the text goes ---------- */
D.LAYOUT.flyerBold = function (doc, t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, t.tint) + circ(W * 0.86, H * 0.1, 250, t.p) + circ(W * 0.62, H * 0.03, 110, t.a) + circ(W * 1.02, H * 0.33, 120, t.s, 0.9) +
      poly([[0, H * 0.78], [W, H * 0.9], [W, H], [0, H]], t.p) + poly([[0, H * 0.83], [W * 0.55, H * 0.9], [0, H * 0.94]], t.a) + dots(60, 70, 6, 5, 20, 3, t.p, 0.3)),
    zone: Z(70, H * 0.3, W - 150, 'left', false), titlePt: 64
  };
};
D.LAYOUT.flyerPhoto = function (doc, t, W, H) {
  var img = doc.cover && doc.cover.src, ph = H * 0.46;
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W, ph, t.s) + photo(img, 0, 0, W, ph, 'fp') + poly([[0, ph - 60], [W, ph - 10], [W, ph + 20], [0, ph + 20]], '#FFFFFF') +
      rect(70, ph + 34, 70, 7, t.a) + rect(0, H - 26, W, 26, t.p)),
    zone: Z(70, ph + 56, W - 140, 'left', false, { photo: true }), titlePt: 54
  };
};
D.LAYOUT.inviteElegant = function (doc, t, W, H) {
  var a = t.a;
  return {
    svg: svg(W, H, rect(0, 0, W, H, t.p) + '<rect x="46" y="46" width="' + (W - 92) + '" height="' + (H - 92) + '" fill="none" stroke="' + a + '" stroke-width="2"/>' +
      '<rect x="58" y="58" width="' + (W - 116) + '" height="' + (H - 116) + '" fill="none" stroke="' + a + '" stroke-width="0.8"/>' +
      ring(W / 2, 170, 34, a, 1.5) + poly([[W / 2, 150], [W / 2 + 12, 170], [W / 2, 190], [W / 2 - 12, 170]], a) +
      line(W / 2 - 90, H - 170, W / 2 + 90, H - 170, a, 1) + circ(W / 2, H - 170, 4, a)),
    zone: Z(110, 260, W - 220, 'center', true), titlePt: 44
  };
};
D.LAYOUT.inviteParty = function (doc, t, W, H) {
  var s = '', cols = [t.p, t.s, t.a, D.mix(t.a, '#FFFFFF', 0.4)];
  for (var i = 0; i < 46; i++) {
    var x = (i * 97) % W, y = (i * 53) % (H * 0.26) + (i % 2 ? H * 0.74 : 0), r = 6 + (i * 7) % 14;
    s += i % 3 ? circ(x, y, r, cols[i % 4], 0.9) : rect(x, y, r * 1.6, r * 0.7, cols[i % 4], 0.9, 2);
  }
  return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + s + rect(90, H * 0.29, W - 180, H * 0.43, t.tint, 1, 18)), zone: Z(130, H * 0.33, W - 260, 'center', false), titlePt: 50 };
};
D.LAYOUT.menu = function (doc, t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFDF8') + rect(34, 34, W - 68, H - 68, 'none') + '<rect x="34" y="34" width="' + (W - 68) + '" height="' + (H - 68) + '" fill="none" stroke="' + t.p + '" stroke-width="2"/>' +
      line(W / 2 - 60, 150, W / 2 + 60, 150, t.a, 2) + circ(W / 2, 150, 5, t.a) + path('M ' + (W / 2 - 30) + ' ' + (H - 70) + ' q 30 -26 60 0', null, null, t.a, 1.5)),
    zone: Z(110, 64, W - 220, 'center', false, { css: 'gap:0' }), titlePt: 38
  };
};
D.LAYOUT.certificate = function (doc, t, W, H) {
  var a = t.a;
  return {
    svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W, H, t.tint) + '<rect x="28" y="28" width="' + (W - 56) + '" height="' + (H - 56) + '" fill="#FFFFFF" stroke="' + t.p + '" stroke-width="10"/>' +
      '<rect x="48" y="48" width="' + (W - 96) + '" height="' + (H - 96) + '" fill="none" stroke="' + a + '" stroke-width="1.5"/>' +
      poly([[28, 28], [150, 28], [28, 150]], t.p) + poly([[W - 28, H - 28], [W - 150, H - 28], [W - 28, H - 150]], t.p) +
      circ(W - 150, H - 175, 46, a) + ring(W - 150, H - 175, 38, '#FFFFFF', 2, 0.8) + poly([[W - 172, H - 135], [W - 150, H - 150], [W - 128, H - 135], [W - 128, H - 95], [W - 150, H - 110], [W - 172, H - 95]], a)),
    zone: Z(110, 92, W - 220, 'center', false), titlePt: 46
  };
};
D.LAYOUT.tickets = function (doc, t, W, H) { return { svg: svg(W, H, rect(0, 0, W, H, '#FFFFFF')), zone: Z(56, 60, W - 112, 'left', false), titlePt: 20 }; };
D.LAYOUT.card = function (doc, t, W, H) {
  return {
    svg: svg(W, H, rect(0, 0, W, H, t.tint) + blob(W * 0.15, H * 0.12, 170, 5, t.s, 0.55) + blob(W * 0.9, H * 0.9, 220, 6, t.p, 0.4) + blob(W * 0.88, H * 0.1, 80, 7, t.a, 0.7) +
      rect(110, H * 0.3, W - 220, H * 0.42, '#FFFFFF', 1, 16)),
    zone: Z(150, H * 0.36, W - 300, 'center', false), titlePt: 60
  };
};
/* ---------- Word (.docx) export with docx.js ----------
 * Everything is real Word content: headings use Word's Heading styles (so they show in the
 * navigation pane), lists are Word lists, tables are Word tables. Covers and page decoration
 * are pictures placed behind the text, like Word's own templates.
 */
D.DOCX_URL = 'https://cdn.jsdelivr.net/npm/docx@9.5.1/dist/index.iife.js';
D.loadDocx = function () {
  if (window.docx && window.docx.Document) return Promise.resolve(window.docx);
  if (D._docxP) return D._docxP;
  D._docxP = new Promise(function (res, rej) {
    var s = document.createElement('script'); s.src = D.DOCX_URL; s.async = true;
    s.onload = function () { window.docx ? res(window.docx) : rej(new Error('The Word exporter did not load.')); };
    s.onerror = function () { D._docxP = null; rej(new Error('The Word exporter could not be loaded. Check your connection.')); };
    document.head.appendChild(s);
  });
  return D._docxP;
};

/* SVG → PNG bytes */
D.svgToPng = function (svgStr, w, h, scale) {
  scale = scale || 2;
  return new Promise(function (res, rej) {
    var img = new Image();
    img.onload = function () {
      var c = document.createElement('canvas'); c.width = Math.round(w * scale); c.height = Math.round(h * scale);
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
      res(D.dataToBytes(c.toDataURL('image/png')));
    };
    img.onerror = function () { rej(new Error('A design picture could not be drawn.')); };
    img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgStr);
  });
};
D.dataToBytes = function (dataUri) {
  var b64 = String(dataUri).split(',')[1] || '', bin = atob(b64), out = new Uint8Array(bin.length);
  for (var i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
  return out;
};
/** Any picture (data: URI or https URL) → {data, type, w, h} that Word accepts (PNG/JPEG). */
D.loadPicture = function (src) {
  function viaCanvas(url) {
    return new Promise(function (res, rej) {
      var img = new Image(); img.crossOrigin = 'anonymous';
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight, max = 2200, k = Math.min(1, max / Math.max(w, h));
        var c = document.createElement('canvas'); c.width = Math.round(w * k); c.height = Math.round(h * k);
        var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
        res({ data: D.dataToBytes(c.toDataURL('image/jpeg', 0.9)), type: 'jpg', w: c.width, h: c.height });
      };
      img.onerror = function () { rej(new Error('A picture could not be loaded.')); };
      img.src = url;
    });
  }
  if (/^data:image\/(png|jpe?g);/i.test(src)) {
    return new Promise(function (res) {
      var img = new Image();
      img.onload = function () { res({ data: D.dataToBytes(src), type: /png/i.test(src.slice(0, 20)) ? 'png' : 'jpg', w: img.naturalWidth, h: img.naturalHeight }); };
      img.onerror = function () { res(null); };
      img.src = src;
    });
  }
  if (/^data:/i.test(src)) return viaCanvas(src);
  return fetch(src, { credentials: 'include' }).then(function (r) { if (!r.ok) throw new Error('picture'); return r.blob(); })
    .then(function (blob) { return new Promise(function (res) { var fr = new FileReader(); fr.onload = function () { res(fr.result); }; fr.readAsDataURL(blob); }); })
    .then(function (du) { return D.loadPicture(du); })
    .catch(function () { return viaCanvas(src).catch(function () { return null; }); });
};

/** Any picture source → data: URI (so it can be drawn into a picture). */
D.toDataURI = function (src) {
  src = String(src || '');
  if (/^data:/i.test(src)) return Promise.resolve(src);
  return fetch(src, { credentials: 'include' }).then(function (r) { if (!r.ok) throw new Error('picture'); return r.blob(); })
    .then(function (blob) { return new Promise(function (res) { var fr = new FileReader(); fr.onload = function () { res(fr.result); }; fr.readAsDataURL(blob); }); });
};
/** Icons, pictures and charts on the pages → PNG bytes {data, P}. */
D.layerPng = function (L, t) {
  var P = D.layerPad(L), go = function (L2) { return D.svgToPng(D.layerSVG(L2, t, { op: L.op }), D.num(L.w, 100, 1) + 2 * P, D.num(L.h, 100, 1) + 2 * P, 2).then(function (b) { return { data: b, P: P }; }); };
  if (L.k === 'image' && L.src && !/^data:/i.test(L.src)) return D.toDataURI(L.src).then(function (du) { var c = D.clone(L); c.src = du; return go(c); }, function () { return null; });
  return go(L).catch(function () { return null; });
};
/** Which page every block starts on (lays the document out off-screen). */
D.pageMap = function (doc) {
  if (typeof document === 'undefined') return {};
  D.injectCSS();
  var host = document.createElement('div');
  host.style.cssText = 'position:absolute;left:-99999px;top:0;visibility:hidden;width:' + D.size(doc).w + 'px';
  document.body.appendChild(host);
  try { var api = D.render(host, doc, { edit: false, gap: 28 }); return api.blockPages ? api.blockPages() : {}; }
  catch (e) { return {}; } finally { host.remove(); }
};
/** A shape or text box as a real Word shape (DrawingML). */
D.layerXML = function (L, t, id, z, sf) {
  var E = function (v) { return Math.round(v * 9525); }, X = function (v) { return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };
  var op = L.op != null ? D.num(L.op, 1, 0, 1) : 1;
  var clr = function (c, a) { a = Math.max(0, Math.min(1, a)); return '<a:srgbClr val="' + D.hex(D.col(c, t)) + '">' + (a < 1 ? '<a:alpha val="' + Math.round(a * 100000) + '"/>' : '') + '</a:srgbClr>'; };
  var fillX = function (p) {
    if (!p || !p.m || p.m === 'none') return '<a:noFill/>';
    if (p.m === 'grad') {
      var g = p.g || {}, st = (g.s && g.s.length ? g.s : [[0, '@p', 1], [100, '@a', 1]]).slice(0, 8);
      return '<a:gradFill rotWithShape="1"><a:gsLst>' + st.map(function (x) { return '<a:gs pos="' + Math.round(D.num(x[0], 0, 0, 100) * 1000) + '">' + clr(x[1], D.num(x[2], 1, 0, 1) * op) + '</a:gs>'; }).join('') + '</a:gsLst>' +
        (g.t === 'radial' ? '<a:path path="circle"><a:fillToRect l="50000" t="50000" r="50000" b="50000"/></a:path>' : '<a:lin ang="' + Math.round(((D.num(g.a, 0) % 360) + 360) % 360 * 60000) + '" scaled="0"/>') + '</a:gradFill>';
    }
    return '<a:solidFill>' + clr(p.c, (p.o != null ? D.num(p.o, 1, 0, 1) : 1) * op) + '</a:solidFill>';
  };
  var lnX = function (p) {
    if (!p || !p.m || p.m === 'none') return '<a:ln><a:noFill/></a:ln>';
    var dot = p.d === 'dot';
    return '<a:ln w="' + E(D.num(p.w, 2, 0, 60)) + '"' + (dot ? ' cap="rnd"' : '') + '>' + fillX(p) + (p.d === 'dash' ? '<a:prstDash val="dash"/>' : dot ? '<a:prstDash val="sysDot"/>' : '') + '<a:round/></a:ln>';
  };
  var w = D.num(L.w, 100, 1), h = D.num(L.h, 100, 1), x = D.num(L.x, 0), y = D.num(L.y, 0), isText = L.k === 'text';
  var prst = (isText ? (L.shape || 'rect') : L.shape) || 'rect', isLine = prst === 'line';
  if (!/^(rect|roundRect|ellipse|line|triangle|rtTriangle|diamond|pentagon|hexagon|octagon|star5|rightArrow|leftArrow|chevron|parallelogram)$/.test(prst)) prst = 'rect';
  if (isLine) { y += h / 2; h = 0; }
  var av = prst === 'roundRect' ? '<a:avLst><a:gd name="adj" fmla="val ' + Math.round(Math.min(50000, D.num(L.r, 12, 0) / Math.max(1, Math.min(w, h)) * 100000)) + '"/></a:avLst>' : '<a:avLst/>';
  var fill = isLine ? '<a:noFill/>' : fillX(L.fill);
  var ln = isLine ? lnX(L.ln && L.ln.m && L.ln.m !== 'none' ? L.ln : { m: 'solid', c: (L.fill && L.fill.c) || '@p', w: 3 }) : lnX(L.ln);
  var eff = '';
  if (L.sh) eff = '<a:effectLst><a:outerShdw blurRad="' + E(D.num(L.sh.b, 8, 0, 200)) + '" dist="' + E(D.num(L.sh.d, 6, 0, 200)) + '" dir="' + Math.round(((D.num(L.sh.a, 45) % 360) + 360) % 360 * 60000) + '" algn="ctr" rotWithShape="0">' + clr(L.sh.c || '#000000', D.num(L.sh.o, 0.35, 0, 1) * op) + '</a:outerShdw></a:effectLst>';
  var tx = '';
  if (isText) {
    var f = L.ff || t.body, jc = { left: 'left', center: 'center', right: 'right', justify: 'both' }[L.al || 'left'] || 'left';
    var rpr = '<w:rPr><w:rFonts w:ascii="' + X(f) + '" w:hAnsi="' + X(f) + '" w:cs="' + X((sf && sf.cs) || f) + '" w:eastAsia="' + X((sf && sf.ea) || f) + '"/>' +
      (L.fw >= 600 ? '<w:b/><w:bCs/>' : '') + (L.it ? '<w:i/><w:iCs/>' : '') + (L.caps ? '<w:caps/>' : '') + (L.st ? '<w:strike/>' : '') +
      '<w:color w:val="' + D.hex(D.col(L.tc, t, t.ink)) + '"/>' + (L.ls ? '<w:spacing w:val="' + Math.round(D.num(L.ls, 0, -10, 60) * 15) + '"/>' : '') +
      '<w:sz w:val="' + Math.round(D.num(L.fs, 14, 4, 300) * 2) + '"/><w:szCs w:val="' + Math.round(D.num(L.fs, 14, 4, 300) * 2) + '"/>' + (L.ul ? '<w:u w:val="single"/>' : '') + '</w:rPr>';
    tx = '<wps:txbx><w:txbxContent>' + String(L.tx || '').split('\n').map(function (line) {
      return '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="' + Math.round(D.num(L.lh, 1.25, 0.6, 4) * 240) + '" w:lineRule="auto"/><w:jc w:val="' + jc + '"/></w:pPr>' +
        (line ? '<w:r>' + rpr + '<w:t xml:space="preserve">' + X(line) + '</w:t></w:r>' : '') + '</w:p>';
    }).join('') + '</w:txbxContent></wps:txbx>';
  }
  var pad = E(isText ? D.num(L.pad, 8, 0, 80) : 4), anchor = { middle: 'ctr', bottom: 'b' }[L.va] || 't';
  return '<w:r><w:drawing><wp:anchor distT="0" distB="0" distL="0" distR="0" simplePos="0" relativeHeight="' + z + '" behindDoc="' + (L.behind ? 1 : 0) + '" locked="0" layoutInCell="1" allowOverlap="1">' +
    '<wp:simplePos x="0" y="0"/><wp:positionH relativeFrom="page"><wp:posOffset>' + E(x) + '</wp:posOffset></wp:positionH><wp:positionV relativeFrom="page"><wp:posOffset>' + E(y) + '</wp:posOffset></wp:positionV>' +
    '<wp:extent cx="' + E(w) + '" cy="' + E(h) + '"/><wp:effectExtent l="0" t="0" r="0" b="0"/><wp:wrapNone/><wp:docPr id="' + id + '" name="' + X(L.name || (isText ? 'Text box' : 'Shape')) + ' ' + id + '"/><wp:cNvGraphicFramePr/>' +
    '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"><wps:wsp><wps:cNvSpPr' + (isText ? ' txBox="1"' : '') + '/>' +
    '<wps:spPr><a:xfrm' + (L.rot ? ' rot="' + Math.round(((D.num(L.rot, 0) % 360) + 360) % 360 * 60000) + '"' : '') + '><a:off x="0" y="0"/><a:ext cx="' + E(w) + '" cy="' + E(h) + '"/></a:xfrm><a:prstGeom prst="' + prst + '">' + av + '</a:prstGeom>' + fill + ln + eff + '</wps:spPr>' +
    tx + '<wps:bodyPr rot="0" vert="horz" wrap="square" lIns="' + pad + '" tIns="' + pad + '" rIns="' + pad + '" bIns="' + pad + '" anchor="' + anchor + '" anchorCtr="0"><a:noAutofit/></wps:bodyPr></wps:wsp></a:graphicData></a:graphic></wp:anchor></w:drawing></w:r>';
};

D.toDocx = function (doc, opts) {
  opts = opts || {};
  var X, t = D.look(doc), dz = D.design(doc.tpl), sz = D.size(doc), sf = D.scriptFonts(doc.lang), TA = D.readable(t.a, t.tint), TS = D.readable(t.s, t.tint), TAD = D.readable(t.a, D.lum(t.p) < 0.3 ? t.p : '#1E2026', 4.5);
  var tw = D.pxToTw, contentW = sz.w - dz.m.l - dz.m.r, pics = {}, arts = {};
  var images = opts.images || {};
  var h = function (c) { return D.hex(c); };
  var artOn = D.artOn(doc);
  var layers = (doc.layers || []).filter(function (L) { return L && L.k && !L.hide && (artOn || !L.art); }), lpics = {}, pageOf = {};
  var masters = layers.filter(function (L) { return L.pg === 'm'; }); layers = layers.filter(function (L) { return L.pg !== 'm'; });

  return D.loadDocx().then(function (lib) {
    X = lib;
    var jobs = [];
    // design pictures
    function art(key, svgStr, w, hh) { if (svgStr) jobs.push(D.svgToPng(svgStr, w, hh, 1.6).then(function (b) { arts[key] = b; })); }
    if (dz.kind === 'flow' && dz.cover) { var cs = D.coverSpec(doc, t, dz, sz); arts.coverZone = cs.zone; art('cover', artOn ? D.artBg(cs.svg) : cs.svg, sz.w, sz.h); }
    if (dz.kind === 'single') { var L = (D.LAYOUT[dz.layout] || D.LAYOUT.flyerBold)(doc, t, sz.w, sz.h); arts.singleL = L; art('single', artOn ? D.artBg(L.svg) : L.svg, sz.w, sz.h); }
    if (dz.kind === 'panels') { var PS0 = D.panelSpec(doc, t, dz, sz); arts.panelSpec = PS0; PS0.pages.forEach(function (pg, i) { art('panel' + i, artOn ? D.artBg(pg.svg) : pg.svg, sz.w, sz.h); }); }
    if (dz.kind !== 'single') {
      if (!artOn && D.ART.page[dz.page] && dz.page !== 'none') art('page', D.ART.page[dz.page](t, sz.w, sz.h, dz), sz.w, sz.h);
      if (!artOn && dz.first && D.ART.head[dz.first]) art('first', D.ART.head[dz.first](t, sz.w, sz.h), sz.w, sz.h);
    }
    // card icons and logo
    (doc.blocks || []).forEach(function (b) {
      if (b.t === 's_contact') (b.rows || []).forEach(function (r) {
        var key = 'ic_' + (D.ICONS[r.i] ? r.i : 'link');
        if (pics[key] !== undefined) return; pics[key] = null;
        var ic = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><circle cx="24" cy="24" r="24" fill="' + t.a + '"/><g transform="translate(10.5,10.5) scale(1.125)"><path d="' + D.ICONS[key.slice(3)] + '" fill="' + D.onColor(t.a) + '"/></g></svg>';
        jobs.push(D.svgToPng(ic, 48, 48, 1).then(function (by) { pics[key] = { data: by, type: 'png', w: 48, h: 48 }; }).catch(function () {}));
      });
      if (b.t === 's_qr') jobs.push(D.svgToPng(D.qrSVG(b.text || ' ', '#111111', '#FFFFFF', 300), 300, 300, 1).then(function (by) { pics['qr_' + b.id] = { data: by, type: 'png', w: 300, h: 300 }; }).catch(function () {}));
      if (b.t === 's_photo' && b.src) jobs.push(D.loadPicture(b.src).then(function (p) { if (p) pics['ph_' + b.id] = p; }).catch(function () {}));
      if ((b.t === 's_mark' || dz.kind === 'panels') && doc.logo && doc.logo.src && !doc.logo.hide && pics.__logo === undefined) { pics.__logo = null; jobs.push(D.loadPicture(doc.logo.src).then(function (p) { if (p) pics.__logo = p; }).catch(function () {})); }
    });
    // block pictures
    (doc.blocks || []).forEach(function (b) {
      var src = b.t === 'image' ? (b.src || images[b.id]) : null;
      if (src) jobs.push(D.loadPicture(src).then(function (p) { if (p) pics[b.id] = p; }));
      if (b.t === 'chart') {
        var cw0 = 960, ch0 = Math.round(cw0 * D.chartRatio(b));
        jobs.push(D.svgToPng(D.chartSVG(b, t, cw0, ch0, { bg: '#FFFFFF' }), cw0, ch0, 2).then(function (by) { pics[b.id] = { data: by, type: 'png', w: cw0, h: ch0 }; }).catch(function () {}));
      }
    });
    if (doc.logo && doc.logo.src && !doc.logo.hide) {
      jobs.push(D.logoPng(doc, sz, 'cover').then(function (p) { if (p) arts.logoC = p; }));
      jobs.push(D.logoPng(doc, sz, 'page').then(function (p) { if (p) arts.logoP = p; }));
    }
    // pictures for icons, pictures and charts placed on the pages
    layers.concat(masters).forEach(function (L) { if (L.k === 'icon' || L.k === 'image' || L.k === 'chart' || L.k === 'vec' || (L.fill && L.fill.m === 'pic')) jobs.push(D.layerPng(L, t).then(function (r) { if (r) lpics[L.id] = r; })); });
    if (layers.some(function (L) { return L.pg !== 'c'; }) && dz.kind !== 'single') pageOf = D.pageMap(doc);
    return Promise.all(jobs);
  }).then(function () {
    var A = X.AlignmentType, BS = X.BorderStyle, WT = X.WidthType, SH = X.ShadingType;
    var NONE = { style: BS.NONE, size: 0, color: 'FFFFFF' };
    var noBorders = { top: NONE, bottom: NONE, left: NONE, right: NONE, insideHorizontal: NONE, insideVertical: NONE };
    function font(f) { return { ascii: f, hAnsi: f, cs: sf.cs || f, eastAsia: sf.ea || f }; }
    var HF = font(t.head), BF = font(t.body);
    function sizeHp(pt) { return Math.round(pt * 2); }
    var CS = null, CP = null;        // the style of the block being written (from the editor)
    function R(text, o) {
      o = o || {};
      var s = CS || {};
      return D.runs(text).map(function (r) {
        var parts = String(r.t).split('\n'), out = [];
        parts.forEach(function (pt, i) {
          out.push(new X.TextRun({ text: pt, break: i > 0 ? 1 : undefined,
            bold: s.fw ? (s.fw >= 600 || !!r.b) : (o.bold || !!r.b), italics: s.it != null ? (!!s.it || !!r.i) : (o.italics || !!r.i),
            color: s.tc ? h(D.col(s.tc, t)) : (o.color ? h(o.color) : undefined), size: s.fs ? sizeHp(s.fs) : (o.size ? sizeHp(o.size) : undefined),
            font: s.ff ? font(s.ff) : (o.font || undefined), allCaps: s.caps || o.caps || undefined, strike: s.sk ? true : undefined, underline: s.ul ? {} : undefined,
            characterSpacing: s.ls ? Math.round(s.ls * 15) : (o.cs || undefined), shading: o.shading || undefined }));
        });
        return out;
      }).reduce(function (a, b) { return a.concat(b); }, []);
    }
    function P(children, o) {
      o = o || {}; o.children = children;
      if (CP && !o.__cell) {
        if (CP.al) o.alignment = CP.al;
        if (CP.ind) o.indent = Object.assign({}, o.indent || {}, { left: ((o.indent && o.indent.left) || 0) + CP.ind.l, right: CP.ind.r });
        if (CP.shade) o.shading = CP.shade;
        if (CP.border) o.border = CP.border;
        if (CP.lh) { o.spacing = Object.assign({}, o.spacing || {}); o.spacing.line = CP.lh; }
      }
      delete o.__cell;
      if (o.spacing && o.spacing.line) o.spacing.lineRule = X.LineRuleType.AUTO;
      return new X.Paragraph(o);
    }
    function T(text, ro, po) { return P(R(text, ro), po); }
    function cell(children, o) {
      o = o || {};
      return new X.TableCell({ children: children.length ? children : [P([])], width: o.w ? { size: o.w, type: WT.DXA } : undefined, columnSpan: o.span,
        shading: o.fill ? { fill: h(o.fill), type: SH.CLEAR, color: 'auto' } : undefined, borders: o.borders || { top: NONE, bottom: NONE, left: NONE, right: NONE },
        margins: o.margins || { top: 100, bottom: 100, left: 140, right: 140 }, verticalAlign: o.va || X.VerticalAlign.TOP });
    }
    function table(rows, widths, o) {
      o = o || {};
      return new X.Table({ rows: rows, width: { size: widths.reduce(function (a, b) { return a + b; }, 0), type: WT.DXA }, columnWidths: widths,
        layout: X.TableLayoutType.FIXED, borders: o.borders || noBorders, alignment: o.align });
    }
    function line(c, sz2) { return { style: BS.SINGLE, size: sz2 || 4, color: h(c) }; }
    var CW = tw(contentW);
    function split(total, parts) { var w = Math.floor(total / parts); var a = []; for (var i = 0; i < parts; i++) a.push(i === parts - 1 ? total - w * (parts - 1) : w); return a; }

    /* numbering: bullets + a numbered list per list instance */
    var numbering = { config: [
      { reference: 'ds-bul', levels: [{ level: 0, format: X.LevelFormat.BULLET, text: '●', alignment: A.LEFT, style: { paragraph: { indent: { left: 360, hanging: 260 } }, run: { color: h(t.a), size: 14 } } }] },
      { reference: 'ds-num', levels: [{ level: 0, format: X.LevelFormat.DECIMAL, text: '%1.', alignment: A.LEFT, style: { paragraph: { indent: { left: 400, hanging: 300 } }, run: { color: h(t.a), bold: true } } }] }
    ] };
    var listN = 0, h1n = 0;
    var SP = { after: 160, line: 300 };

    function imageRunFloating(bytes, w, hh) {
      return new X.ImageRun({ type: 'png', data: bytes, transformation: { width: w, height: hh },
        floating: { horizontalPosition: { relative: X.HorizontalPositionRelativeFrom.PAGE, offset: 0 }, verticalPosition: { relative: X.VerticalPositionRelativeFrom.PAGE, offset: 0 },
          behindDocument: true, allowOverlap: true, wrap: { type: X.TextWrappingType.NONE } }, altText: { title: 'Design', description: 'Page design', name: 'Design' } });
    }

    /* ----- one block → Word elements ----- */
    var FLOWT = /^(h1|h2|h3|p|ul|ol|check|quote|addr|sign|lh|title|r_sec|s_kicker|s_title|s_sub|s_text|s_cta|image|chart)$/;
    function block(b, o) {
      if (b.hide) return [];
      var sCS = CS, sCP = CP, st = b.st || {}, gx = D.blockGeo(b), res;
      CS = b.st ? st : null; CP = null;
      if (FLOWT.test(b.t) && b.t !== 'image' && b.t !== 'chart') {
        var cwv = (o && o.cw) || CW;
        CP = {};
        if (st.al) CP.al = { left: A.LEFT, center: A.CENTER, right: A.RIGHT, justify: A.JUSTIFIED }[st.al];
        if (gx.w < 1 || gx.ox > 0) CP.ind = { l: Math.round(gx.ox * cwv), r: Math.round(Math.max(0, 1 - gx.ox - gx.w) * cwv) };
        var fc = st.fill && st.fill.m && st.fill.m !== 'none' && st.fill.m !== 'pic' ? (st.fill.m === 'grad' ? ((st.fill.g && st.fill.g.s && st.fill.g.s[0]) || [0, '@p'])[1] : st.fill.c) : '';
        if (fc) CP.shade = { type: SH.CLEAR, fill: h(D.col(fc, t)), color: 'auto' };
        if (st.ln && st.ln.m && st.ln.m !== 'none') { var bl = { style: st.ln.d === 'dash' ? BS.DASHED : st.ln.d === 'dot' ? BS.DOTTED : BS.SINGLE, size: Math.max(2, Math.round(D.num(st.ln.w, 2, 0, 30) * 6)), color: h(D.col(st.ln.m === 'grad' ? (((st.ln.g && st.ln.g.s && st.ln.g.s[0]) || [0, '@a'])[1]) : (st.ln.c || '@a'), t)), space: 6 }; CP.border = { top: bl, bottom: bl, left: bl, right: bl }; }
        if (st.lh) CP.lh = Math.round(D.num(st.lh, 1.3, 0.6, 4) * 240);
      }
      try { res = block0(b, o); } finally { CS = sCS; CP = sCP; }
      if (gx.mt > 0) res.unshift(new X.Paragraph({ children: [], spacing: { before: 0, after: tw(gx.mt), line: 20, lineRule: X.LineRuleType.EXACT } }));
      if (gx.mb > 0) res.push(new X.Paragraph({ children: [], spacing: { before: 0, after: tw(gx.mb), line: 20, lineRule: X.LineRuleType.EXACT } }));
      return res;
    }
    function block0(b, o) {
      o = o || {};
      var out = [], i, cw = o.cw || CW, onDark = o.dark, K = o.k || 1;
      var ink = onDark ? '#FFFFFF' : t.ink, pc = onDark ? '#FFFFFF' : t.p, mute = onDark ? '#E6E9EF' : t.mute;
      switch (b.t) {
        case 'h1':
          h1n++;
          var n2 = ('0' + h1n).slice(-2), hp = { heading: X.HeadingLevel.HEADING_1, keepNext: true, keepLines: true, spacing: { before: 320, after: 160 } };
          var hr = { font: HF, color: pc, bold: true, size: dz.h1 };
          if (dz.head === 'number') out.push(T(n2, { font: HF, color: (onDark ? TAD : TA), bold: true, size: 26 }, { keepNext: true, spacing: { before: 280, after: 0 } }));
          if (dz.head === 'caps') out.push(T('Section ' + n2, { font: BF, color: (onDark ? TAD : TA), bold: true, size: 8.5, caps: true, cs: 36 }, { keepNext: true, spacing: { before: 300, after: 40 } }));
          if (dz.head === 'chapter') { out.push(T('Chapter ' + h1n, { font: BF, color: (onDark ? TAD : TA), bold: true, size: 9, caps: true, cs: 40 }, { alignment: A.CENTER, pageBreakBefore: h1n > 1, keepNext: true, spacing: { before: 600, after: 60 } })); hp.alignment = A.CENTER; hr.size = dz.h1 + 6; }
          if (dz.head === 'bar') hp.border = { left: { style: BS.SINGLE, size: 36, color: h(t.a), space: 10 } };
          if (dz.head === 'underline') hp.border = { bottom: { style: BS.SINGLE, size: 16, color: h(t.a), space: 4 } };
          if (dz.head === 'ruletop') { hp.border = { top: { style: BS.SINGLE, size: 32, color: h(t.p), space: 4 } }; hr.caps = true; }
          if (dz.head === 'serif') { hr.color = ink; b = { text: h1n + '.  ' + b.text }; }
          if (dz.head === 'overline') { out.push(T('━━━', { color: (onDark ? TAD : TA), size: 11 }, { keepNext: true, spacing: { before: 320, after: 20 } })); hp.spacing = { before: 0, after: 160 }; }
          if (dz.head === 'boxed') { hp.shading = { type: SH.CLEAR, fill: h(t.tint), color: 'auto' }; hp.border = { left: { style: BS.SINGLE, size: 32, color: h(t.p), space: 8 } }; }
          if (dz.head === 'double') hp.border = { bottom: { style: BS.DOUBLE, size: 12, color: h(t.a), space: 4 } };
          if (dz.head === 'tag') out.push(P(R(' ' + n2 + ' ', { font: HF, bold: true, size: Math.round(dz.h1 * 0.6), color: D.onColor(t.a), shading: { type: SH.CLEAR, fill: h(t.a), color: 'auto' } }).concat(R('   ' + b.text, hr)), hp));
          else out.push(P(R(b.text, hr), hp));
          if (dz.head === 'chapter') out.push(T('◆  ◆  ◆', { color: (onDark ? TAD : TA), size: 10 }, { alignment: A.CENTER, spacing: { after: 360 } }));
          break;
        case 'h2': out.push(P(R(b.text, { font: HF, color: dz.head === 'serif' ? ink : (onDark ? '#FFFFFF' : t.s), bold: true, size: dz.h2, italics: dz.head === 'serif' }), { heading: X.HeadingLevel.HEADING_2, keepNext: true, spacing: { before: 240, after: 100 } })); break;
        case 'h3': out.push(P(R(b.text, { font: HF, color: ink, bold: true, size: 11.5 }), { heading: X.HeadingLevel.HEADING_3, keepNext: true, spacing: { before: 200, after: 80 } })); break;
        case 'p': out.push(P(R(b.text, { color: onDark ? '#FFFFFF' : undefined }), { spacing: SP, alignment: o.align })); break;
        case 'ul': (b.items || []).forEach(function (it) { out.push(P(R(it, { color: onDark ? '#FFFFFF' : undefined }), { numbering: { reference: 'ds-bul', level: 0 }, spacing: { after: 60, line: 290 } })); }); break;
        case 'ol': listN++; (b.items || []).forEach(function (it) { out.push(P(R(it), { numbering: { reference: 'ds-num', level: 0, instance: listN }, spacing: { after: 60, line: 290 } })); }); break;
        case 'check': (b.items || []).forEach(function (it) { out.push(P([new X.TextRun({ text: '☐  ', font: 'Segoe UI Symbol', color: h(t.p), size: 24 })].concat(R(it)), { spacing: { after: 80 }, indent: { left: 120 } })); }); break;
        case 'quote':
          out.push(P(R(b.text, { font: HF, italics: true, size: 13, color: t.p }), { border: { left: { style: BS.SINGLE, size: 32, color: h(t.a), space: 14 } }, indent: { left: 260 }, spacing: { before: 200, after: b.by ? 40 : 240 } }));
          if (b.by) out.push(T(b.by, { size: 9, color: t.mute, caps: true, cs: 12 }, { indent: { left: 260 }, border: { left: { style: BS.SINGLE, size: 32, color: h(t.a), space: 14 } }, spacing: { after: 240 } }));
          break;
        case 'callout':
          out.push(table([new X.TableRow({ children: [cell([T(b.title || '', { font: HF, bold: true, color: t.p }, { spacing: { after: 60 } }), P(R(b.text), { spacing: { after: 0, line: 290 } })],
            { w: cw, fill: t.tint, borders: { left: { style: BS.SINGLE, size: 40, color: h(t.a) }, top: NONE, bottom: NONE, right: NONE }, margins: { top: 160, bottom: 160, left: 240, right: 240 } })] })], [cw]));
          out.push(P([], { spacing: { after: 120 } }));
          break;
        case 'stats':
          var items = b.items || [], gapw = 160, k = Math.max(1, items.length), cwid = Math.floor((cw - gapw * (k - 1)) / k), cells = [], widths = [];
          items.forEach(function (s, si) {
            if (si) { cells.push(cell([P([])], { w: gapw, margins: { top: 0, bottom: 0, left: 0, right: 0 } })); widths.push(gapw); }
            cells.push(cell([T(s.v, { font: HF, bold: true, size: 22, color: t.p }, { spacing: { after: 40 } }), T(s.l, { size: 8.5, color: t.mute }, { spacing: { after: 0 } })],
              { w: cwid, fill: t.tint, borders: { top: { style: BS.SINGLE, size: 32, color: h(t.a) }, bottom: NONE, left: NONE, right: NONE }, margins: { top: 160, bottom: 140, left: 180, right: 140 } }));
            widths.push(cwid);
          });
          out.push(table([new X.TableRow({ children: cells, cantSplit: true })], widths), P([], { spacing: { after: 120 } }));
          break;
        case 'table':
          var cols = Math.max(1, (b.head || []).length || ((b.rows || [])[0] || []).length), wds = split(cw, cols), rows = [];
          if ((b.head || []).length) rows.push(new X.TableRow({ tableHeader: true, cantSplit: true, children: b.head.map(function (c, ci) { return cell([T(c, { bold: true, color: D.onColor(t.p), size: Math.max(8, dz.body - 0.5) }, { spacing: { after: 0 } })], { w: wds[ci], fill: t.p }); }) }));
          (b.rows || []).forEach(function (r, ri) {
            rows.push(new X.TableRow({ cantSplit: true, children: wds.map(function (w, ci) { return cell([P(R(r[ci] || '', { size: Math.max(8, dz.body - 0.5) }), { spacing: { after: 0 } })], { w: w, fill: ri % 2 ? t.tint : null, borders: { bottom: line(t.line, 6), top: NONE, left: NONE, right: NONE } }); }) }));
          });
          out.push(table(rows, wds), P([], { spacing: { after: 160 } }));
          break;
        case 'timeline':
          var dw = 1500, tws = [dw, cw - dw];
          out.push(table((b.items || []).map(function (s) {
            return new X.TableRow({ cantSplit: true, children: [cell([T(s.d, { bold: true, color: (onDark ? TAD : TA), size: 9, caps: true }, { spacing: { after: 0 } })], { w: dw, margins: { top: 60, bottom: 120, left: 0, right: 120 } }),
              cell([T(s.title, { bold: true, color: t.p }, { spacing: { after: 30 } }), P(R(s.text), { spacing: { after: 0, line: 290 } })], { w: cw - dw, borders: { left: line(t.line, 12), top: NONE, bottom: NONE, right: NONE }, margins: { top: 60, bottom: 160, left: 240, right: 0 } })] });
          }), tws), P([], { spacing: { after: 120 } }));
          break;
        case 'cols':
          var ci2 = b.items || [], g2 = 300, n3 = Math.max(1, ci2.length), w3 = Math.floor((cw - g2 * (n3 - 1)) / n3), cs2 = [], ws2 = [];
          ci2.forEach(function (s, si) {
            if (si) { cs2.push(cell([P([])], { w: g2, margins: { top: 0, bottom: 0, left: 0, right: 0 } })); ws2.push(g2); }
            cs2.push(cell([T(s.title, { font: HF, bold: true, color: pc, size: 11.5 }, { border: { bottom: { style: BS.SINGLE, size: 16, color: h(t.a), space: 3 } }, spacing: { after: 80 } }),
              P(R(s.text, { color: onDark ? '#FFFFFF' : undefined }), { spacing: { after: 0, line: 290 } })], { w: w3, margins: { top: 0, bottom: 0, left: 0, right: 0 } }));
            ws2.push(w3);
          });
          out.push(table([new X.TableRow({ children: cs2, cantSplit: true })], ws2), P([], { spacing: { after: 160 } }));
          break;
        case 'kv':
          var kw = Math.round(cw * 0.28);
          out.push(table((b.rows || []).map(function (r) {
            return new X.TableRow({ children: [cell([T(r[0], { bold: true, color: t.p, size: 8.5, caps: true, cs: 10 }, { spacing: { after: 0 } })], { w: kw, borders: { bottom: line(t.line, 6), top: NONE, left: NONE, right: NONE }, margins: { top: 90, bottom: 90, left: 60, right: 60 } }),
              cell([P(R(r[1] || ''), { spacing: { after: 0 } })], { w: cw - kw, borders: { bottom: line(t.line, 6), top: NONE, left: NONE, right: NONE }, margins: { top: 90, bottom: 90, left: 60, right: 60 } })] });
          }), [kw, cw - kw]), P([], { spacing: { after: 160 } }));
          break;
        case 'image':
          var pic = pics[b.id];
          if (pic) {
            var gI = D.blockGeo(b), maxW = (cw / 15) * gI.w, ww = Math.min(maxW, pic.w), hh2 = ww * pic.h / pic.w;
            if (hh2 > 700) { hh2 = 700; ww = hh2 * pic.w / pic.h; }
            var cen = Math.abs(gI.ox - (1 - gI.w) / 2) < 0.01;
            out.push(P([new X.ImageRun({ type: pic.type, data: pic.data, transformation: { width: Math.round(ww), height: Math.round(hh2) }, altText: { title: b.cap || 'Picture', description: b.cap || b.q || 'Picture', name: 'Picture' } })], { alignment: cen ? A.CENTER : A.LEFT, indent: cen ? undefined : { left: Math.round(gI.ox * cw) }, keepNext: !!b.cap, spacing: { before: 120, after: b.cap ? 60 : 200, line: 240 } }));
          }
          if (b.cap) out.push(T(b.cap, { italics: true, size: 8.5, color: t.mute }, { alignment: A.CENTER, spacing: { after: 220 } }));
          break;
        case 'chart':
          var cpic = pics[b.id];
          if (cpic) {
            var gC = D.blockGeo(b), cww = (cw / 15) * gC.w, chh = cww * cpic.h / cpic.w, cenC = Math.abs(gC.ox - (1 - gC.w) / 2) < 0.01;
            out.push(P([new X.ImageRun({ type: 'png', data: cpic.data, transformation: { width: Math.round(cww), height: Math.round(chh) }, altText: { title: b.title || 'Chart', description: (b.title || 'Chart') + ': ' + (b.labels || []).join(', '), name: 'Chart' } })], { alignment: cenC ? A.CENTER : A.LEFT, indent: cenC ? undefined : { left: Math.round(gC.ox * cw) }, keepNext: !!b.cap, spacing: { before: 120, after: b.cap ? 60 : 200, line: 240 } }));
          }
          if (b.cap) out.push(T(b.cap, { italics: true, size: 8.5, color: t.mute }, { alignment: A.CENTER, spacing: { after: 220 } }));
          break;
        case 'pb': out.push(P([new X.PageBreak()])); break;
        case 'toc':
          out.push(T('Contents', { font: HF, bold: true, color: t.p, size: dz.h1 }, { spacing: { after: 200 } }));
          var c3 = 0;
          (doc.blocks || []).forEach(function (x) { if (x.t === 'h1') { c3++; out.push(P([new X.TextRun({ text: ('0' + c3).slice(-2) + '   ', bold: true, color: h(t.a) })].concat(R(x.text)), { border: { bottom: { style: BS.DOTTED, size: 4, color: h(t.line), space: 4 } }, spacing: { after: 100 } })); } });
          out.push(P([new X.PageBreak()]));
          break;
        case 'lh':
          out.push(T(b.name, { font: HF, bold: true, size: 20, color: onDark ? '#FFFFFF' : t.p }, { alignment: o.align, spacing: { after: 40 } }));
          (b.lines || []).forEach(function (l) { out.push(T(l, { size: 9, color: onDark ? '#E8ECF3' : t.mute }, { alignment: o.align, spacing: { after: 0 } })); });
          break;
        case 'title':
          out.push(T(b.text, { font: HF, bold: true, size: 28, color: onDark ? '#FFFFFF' : t.p }, { alignment: o.align, spacing: { after: 40 } }));
          if (b.sub) out.push(T(b.sub, { size: 10, color: onDark ? '#E8ECF3' : t.mute }, { alignment: o.align, spacing: { after: 0 } }));
          break;
        case 'addr': out.push(P(R((b.lines || []).join('\n')), { spacing: SP })); break;
        case 'sign':
          out.push(T(b.close || '', {}, { spacing: { before: 120, after: 600 } }), T(b.name || '', { bold: true }, { spacing: { after: 0 } }), T(b.role || '', { color: t.mute }, { spacing: { after: 160 } }));
          break;
        case 'inv':
          var cur = b.cur || '$', sub = 0, iw = [Math.round(cw * 0.52), Math.round(cw * 0.12), Math.round(cw * 0.17)]; iw.push(cw - iw[0] - iw[1] - iw[2]);
          var ir = [new X.TableRow({ tableHeader: true, children: ['Description', 'Qty', 'Rate', 'Amount'].map(function (x, xi) { return cell([T(x, { bold: true, color: D.onColor(t.p) }, { alignment: xi ? A.RIGHT : A.LEFT, spacing: { after: 0 } })], { w: iw[xi], fill: t.p }); }) })];
          (b.rows || []).forEach(function (r) {
            var amt = (+r.q || 0) * (+r.r || 0); sub += amt;
            ir.push(new X.TableRow({ cantSplit: true, children: [r.d || '', String(r.q == null ? '' : r.q), D.money(+r.r || 0, cur), D.money(amt, cur)].map(function (x, xi) {
              return cell([T(x, {}, { alignment: xi ? A.RIGHT : A.LEFT, spacing: { after: 0 } })], { w: iw[xi], borders: { bottom: line(t.line, 6), top: NONE, left: NONE, right: NONE } });
            }) }));
          });
          var tax = sub * (+b.tax || 0) / 100;
          [['Subtotal', D.money(sub, cur)], ['Tax (' + (+b.tax || 0) + '%)', D.money(tax, cur)], ['Total', D.money(sub + tax, cur)]].forEach(function (r, ri) {
            var g = ri === 2;
            ir.push(new X.TableRow({ children: [cell([T(r[0], { bold: g, color: g ? D.onColor(t.a) : t.ink, size: g ? 12 : undefined }, { alignment: A.RIGHT, spacing: { after: 0 } })], { w: iw[0] + iw[1] + iw[2], span: 3, fill: g ? t.a : null }),
              cell([T(r[1], { bold: g, color: g ? D.onColor(t.a) : t.ink, size: g ? 12 : undefined }, { alignment: A.RIGHT, spacing: { after: 0 } })], { w: iw[3], fill: g ? t.a : null })] }));
          });
          out.push(table(ir, iw), P([], { spacing: { after: 200 } }));
          break;
        case 'todo':
          var tw2 = [Math.round(cw * 0.08), Math.round(cw * 0.54), Math.round(cw * 0.2)]; tw2.push(cw - tw2[0] - tw2[1] - tw2[2]);
          var trs = [new X.TableRow({ tableHeader: true, children: ['', 'Task', 'Due', 'Priority'].map(function (x, xi) { return cell([T(x, { bold: true, color: D.onColor(t.p) }, { spacing: { after: 0 } })], { w: tw2[xi], fill: t.p }); }) })];
          (b.rows || []).forEach(function (r) {
            trs.push(new X.TableRow({ height: { value: 560, rule: X.HeightRule.ATLEAST }, children: [cell([T('☐', { font: 'Segoe UI Symbol', size: 13, color: t.p }, { alignment: A.CENTER, spacing: { after: 0 } })], { w: tw2[0], va: X.VerticalAlign.CENTER, borders: { bottom: line(t.line, 6), top: NONE, left: NONE, right: NONE } })]
              .concat([r.task, r.due, r.pr].map(function (x, xi) { return cell([T(x || '', {}, { spacing: { after: 0 } })], { w: tw2[xi + 1], va: X.VerticalAlign.CENTER, borders: { bottom: line(t.line, 6), top: NONE, left: NONE, right: NONE } }); })) }));
          });
          out.push(table(trs, tw2), P([], { spacing: { after: 160 } }));
          break;
        case 'week':
          var ww2 = [Math.round(cw * 0.26), cw - Math.round(cw * 0.26)], all = { top: line(t.line, 6), bottom: line(t.line, 6), left: line(t.line, 6), right: line(t.line, 6) };
          out.push(table((b.days || []).map(function (d, di) {
            return new X.TableRow({ height: { value: 1300, rule: X.HeightRule.ATLEAST }, cantSplit: true, children: [cell([T(d, { font: HF, bold: true, color: t.p }, { spacing: { after: 0 } })], { w: ww2[0], fill: t.tint, borders: all }), cell([P(R((b.notes || [])[di] || ''), { spacing: { after: 0 } })], { w: ww2[1], borders: all })] });
          }), ww2), P([], { spacing: { after: 160 } }));
          break;
        case 'cal':
          var y = +b.y, m = +b.m, st = (new Date(y, m, 1).getDay() + 6) % 7, days = new Date(y, m + 1, 0).getDate(), cw7 = split(cw, 7), crs = [], allb = { top: line(t.line, 6), bottom: line(t.line, 6), left: line(t.line, 6), right: line(t.line, 6) };
          crs.push(new X.TableRow({ tableHeader: true, children: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map(function (d, di) { return cell([T(d, { bold: true, color: D.onColor(t.p), size: 9 }, { alignment: A.CENTER, spacing: { after: 0 } })], { w: cw7[di], fill: t.p }); }) }));
          var total = Math.ceil((st + days) / 7) * 7, row = [];
          for (i = 0; i < total; i++) {
            var dd = i - st + 1, ok = dd >= 1 && dd <= days;
            row.push(cell([T(ok ? String(dd) : '', { bold: true, color: t.p }, { spacing: { after: 0 } })], { w: cw7[i % 7], fill: ok ? null : '#FAFAFA', borders: allb }));
            if (i % 7 === 6) { crs.push(new X.TableRow({ height: { value: 1450, rule: X.HeightRule.ATLEAST }, cantSplit: true, children: row })); row = []; }
          }
          out.push(table(crs, cw7), P([], { spacing: { after: 160 } }));
          break;
        case 'sigs':
          var sg = b.items || [], gw = 500, sn = Math.max(1, sg.length), sw2 = Math.floor((cw - gw * (sn - 1)) / sn), scs = [], sws = [];
          sg.forEach(function (s, si) {
            if (si) { scs.push(cell([P([])], { w: gw })); sws.push(gw); }
            scs.push(cell([T(s.name, { bold: true, color: ink }, { alignment: o.align, spacing: { after: 0 } }), T(s.role, { size: 9, color: mute }, { alignment: o.align, spacing: { after: 0 } })], { w: sw2, borders: { top: line(ink, 12), bottom: NONE, left: NONE, right: NONE }, margins: { top: 60, bottom: 0, left: 0, right: 0 } }));
            sws.push(sw2);
          });
          out.push(P([], { spacing: { before: 600, after: 0 } }), table([new X.TableRow({ children: scs })], sws, { align: o.align === A.CENTER ? A.CENTER : undefined }));
          break;
        /* resume */
        case 'r_head':
          out.push(T(b.name, { font: HF, bold: true, size: 30, color: onDark || o.band ? '#FFFFFF' : t.p }, { alignment: o.align, spacing: { after: 40 } }),
            T(b.role, { bold: true, size: 12, color: o.band ? '#FFFFFF' : t.a, caps: true, cs: 20 }, { alignment: o.align, spacing: { after: 60 } }),
            T((b.contacts || []).join('  ·  '), { size: 9, color: o.band ? '#F1F1F1' : t.mute }, { alignment: o.align, spacing: { after: 200 } }));
          break;
        case 'r_sec': out.push(T(b.text, { font: HF, bold: true, size: 11, color: onDark ? '#FFFFFF' : t.p, caps: true, cs: 24 }, { alignment: o.align, keepNext: true, border: { bottom: { style: BS.SINGLE, size: o.align === A.CENTER ? 4 : 16, color: h(o.align === A.CENTER ? t.line : t.a), space: 3 } }, spacing: { before: 260, after: 140 } })); break;
        case 'r_entry':
          var tl = o.timeline;
          if (tl) {
            var lw = 1350;
            var inner = [P([new X.TextRun({ text: b.title || '', bold: true, color: h(ink) })], { spacing: { after: 20 } }), T(b.org || '', { bold: true, size: 9.5, color: (onDark ? TAD : TS) }, { spacing: { after: 40 } })];
            if (b.text) inner.push(P(R(b.text), { spacing: { after: 40 } }));
            (b.items || []).forEach(function (it) { inner.push(P(R(it), { numbering: { reference: 'ds-bul', level: 0 }, spacing: { after: 30 } })); });
            out.push(table([new X.TableRow({ cantSplit: true, children: [cell([T(b.date || '', { bold: true, color: (onDark ? TAD : TA), size: 9 }, { alignment: A.RIGHT, spacing: { after: 0 } })], { w: lw, margins: { top: 0, bottom: 120, left: 0, right: 200 } }), cell(inner, { w: cw - lw, margins: { top: 0, bottom: 120, left: 0, right: 0 } })] })], [lw, cw - lw]));
            break;
          }
          out.push(P([new X.TextRun({ text: b.title || '', bold: true, color: h(ink) }), new X.TextRun({ text: '\t' + (b.date || ''), color: h(mute), size: 18 })], { tabStops: [{ type: X.TabStopType.RIGHT, position: cw }], keepNext: true, spacing: { after: 20 } }),
            T(b.org || '', { bold: true, size: 9.5, color: onDark ? t.a : t.s }, { keepNext: true, spacing: { after: 40 } }));
          if (b.text) out.push(P(R(b.text, { color: onDark ? '#FFFFFF' : undefined }), { spacing: { after: 40 } }));
          (b.items || []).forEach(function (it) { out.push(P(R(it, { color: onDark ? '#FFFFFF' : undefined }), { numbering: { reference: 'ds-bul', level: 0 }, spacing: { after: 30 } })); });
          out.push(P([], { spacing: { after: 100 } }));
          break;
        case 'r_skills':
          var runs = [];
          (b.items || []).forEach(function (x, xi) {
            if (xi) runs.push(new X.TextRun({ text: '  ' }));
            runs.push(new X.TextRun({ text: ' ' + x + ' ', bold: true, size: 18, color: h(onDark ? '#FFFFFF' : t.p), shading: { type: SH.CLEAR, fill: h(onDark ? D.mix(t.p, '#FFFFFF', 0.18) : t.tint), color: 'auto' } }));
          });
          out.push(P(runs, { alignment: o.align, spacing: { after: 160, line: 380 } }));
          break;
        /* single pages */
        case 's_kicker': out.push(T(b.text, { bold: true, size: 11 * K, color: (onDark ? TAD : TA), caps: true, cs: Math.round(50 * K) }, { alignment: o.align, spacing: { after: Math.round(120 * K) } })); break;
        case 's_title': out.push(T(b.text, { font: HF, bold: true, size: o.titlePt || 48, color: pc }, { alignment: o.align, spacing: { after: Math.round(160 * K), line: 240 } })); break;
        case 's_sub': out.push(o.bc ? T(b.text, { size: 6.6, caps: true, cs: 30, color: onDark ? TAD : TS }, { alignment: o.align, spacing: { after: 60 } }) : T(b.text, { font: HF, italics: true, size: 16 * K, color: onDark ? TAD : TS }, { alignment: o.align, spacing: { after: Math.round(160 * K) } })); break;
        case 's_text': out.push(T(b.text, { size: 11.5 * K, color: ink }, { alignment: o.align, spacing: { before: Math.round(120 * K), after: Math.round(160 * K), line: 320 } })); break;
        case 's_cta': out.push(P([new X.TextRun({ text: '  ' + (b.text || '') + '  ', bold: true, size: Math.round(24 * K), color: h(D.onColor(t.a)), shading: { type: SH.CLEAR, fill: h(t.a), color: 'auto' } })], { alignment: o.align, spacing: { before: Math.round(280 * K) } })); break;
        case 's_details':
          var dw2 = Math.round(cw * 0.3);
          out.push(table((b.rows || []).map(function (r) {
            return new X.TableRow({ children: [cell([T(r[0], { bold: true, color: onDark ? TAD : TA, size: 9 * K, caps: true, cs: 16 }, { spacing: { after: 0 } })], { w: dw2, borders: { bottom: line(onDark ? '#7A7A7A' : t.line, 6), top: NONE, left: NONE, right: NONE } }),
              cell([T(r[1], { color: ink, size: K !== 1 ? 10.5 * K : undefined }, { spacing: { after: 0 } })], { w: cw - dw2, borders: { bottom: line(onDark ? '#7A7A7A' : t.line, 6), top: NONE, left: NONE, right: NONE } })] });
          }), [dw2, cw - dw2], { align: A.CENTER }), P([], { spacing: { after: 120 } }));
          break;
        case 's_contact':
          var cwC = cw, icW = Math.round(15 * 15 * K);
          out.push(table((b.rows || []).map(function (r) {
            var ip = pics['ic_' + (D.ICONS[r.i] ? r.i : 'link')], px = Math.round(12 * K);
            return new X.TableRow({ cantSplit: true, children: [
              cell([ip ? P([new X.ImageRun({ type: 'png', data: ip.data, transformation: { width: px, height: px }, altText: { title: r.i || 'icon', description: r.i || 'icon', name: 'icon' } })], { spacing: { after: 0 } }) : P([])], { w: icW, margins: { top: 20, bottom: 20, left: 0, right: 40 }, va: X.VerticalAlign.CENTER }),
              cell([T(r.t || '', { size: 7.4 * K, color: ink }, { spacing: { after: 0 } })], { w: cwC - icW, margins: { top: 20, bottom: 20, left: 60, right: 0 }, va: X.VerticalAlign.CENTER })] });
          }), [icW, cwC - icW]), P([], { spacing: { after: 0, line: 20, lineRule: X.LineRuleType.EXACT } }));
          break;
        case 's_qr':
          var qp = pics['qr_' + b.id], qs = Math.round(Math.min(o.zw || 60, o.zh || 60));
          if (qp) out.push(P([new X.ImageRun({ type: 'png', data: qp.data, transformation: { width: qs, height: qs }, altText: { title: 'QR code', description: b.text || 'QR code', name: 'QR code' } })], { alignment: A.CENTER, spacing: { after: 0 } }));
          break;
        case 's_photo':
          var pp = pics['ph_' + b.id];
          if (pp) {
            var pw = Math.round(o.zw || 120), ph = Math.round(o.zh || 120), rw = pp.w / pp.h > pw / ph ? ph * pp.w / pp.h : pw;
            out.push(P([new X.ImageRun({ type: pp.type, data: pp.data, transformation: { width: Math.round(Math.min(rw, pw)), height: Math.round(Math.min(rw, pw) * pp.h / pp.w) }, altText: { title: 'Photo', description: 'Photo', name: 'Photo' } })], { alignment: A.CENTER, spacing: { after: 0 } }));
          }
          break;
        case 's_mark':
          var lp = pics.__logo, mkPt = (o.mk || (o.small ? 24 : 34)) * (o.lgk || 1);
          if (lp) { var mh = mkPt * 96 / 72, mw = Math.min(cw / 15, mh * lp.w / lp.h); out.push(P([new X.ImageRun({ type: lp.type, data: lp.data, transformation: { width: Math.round(mw), height: Math.round(mw * lp.h / lp.w) }, altText: { title: 'Logo', description: 'Logo', name: 'Logo' } })], { alignment: o.align, spacing: { after: o.after || 80 } })); }
          else out.push(P([new X.TextRun({ text: '\u00a0' + (b.text || D.initials(D.panelCompany(doc))) + '\u00a0', bold: true, size: Math.round(mkPt * 0.8), color: h(D.onColor(t.a)), font: HF, shading: { type: SH.CLEAR, fill: h(t.a), color: 'auto' } })], { alignment: o.align, spacing: { after: 100 } }));
          break;
        case 's_menu':
          (b.sections || []).forEach(function (sc) {
            out.push(T(sc.name, { font: HF, size: 15, color: TA, caps: true, cs: 60 }, { alignment: A.CENTER, spacing: { before: 300, after: 140 } }));
            (sc.items || []).forEach(function (it) {
              out.push(P([new X.TextRun({ text: it.n || '', bold: true, size: 23 }), new X.TextRun({ text: '\t' + (it.p || ''), bold: true, color: h(t.p), size: 23 })], { tabStops: [{ type: X.TabStopType.RIGHT, position: cw, leader: 'dot' }], keepNext: true, spacing: { after: 0 } }),
                T(it.d || '', { size: 9, color: t.mute }, { spacing: { after: 120 } }));
            });
          });
          break;
        case 's_tickets':
          var half = Math.floor((cw - 200) / 2), stubw = 1100, tk = [], rowk = [];
          for (i = 0; i < (b.n || 8); i++) {
            var tkt = table([new X.TableRow({ height: { value: 2300, rule: X.HeightRule.EXACT }, children: [
              cell([T(b.event, { font: HF, bold: true, size: 14, color: t.p }, { spacing: { after: 30 } }), T(b.sub, { bold: true, size: 8, color: TA, caps: true, cs: 30 }, { spacing: { after: 100 } }), T(b.date, { size: 8.5 }, { spacing: { after: 0 } }), T(b.place, { size: 8.5 }, { spacing: { after: 0 } })],
                { w: half - stubw, borders: { top: line(t.p, 12), bottom: line(t.p, 12), left: line(t.p, 12), right: { style: BS.DASHED, size: 12, color: h(t.line) } }, margins: { top: 160, bottom: 100, left: 200, right: 120 } }),
              cell([T('No. ' + ('00' + ((+b.start || 1) + i)).slice(-3), { bold: true, size: 13, color: D.onColor(t.p) }, { alignment: A.CENTER, spacing: { after: 60 } }), T(b.price, { bold: true, size: 9, color: D.onColor(t.p) }, { alignment: A.CENTER, spacing: { after: 0 } })],
                { w: stubw, fill: t.p, va: X.VerticalAlign.CENTER, borders: { top: line(t.p, 12), bottom: line(t.p, 12), right: line(t.p, 12), left: NONE } })] })], [half - stubw, stubw]);
            rowk.push(cell([tkt], { w: half, margins: { top: 0, bottom: 200, left: 0, right: 0 } }));
            if (rowk.length === 2) { rowk.splice(1, 0, cell([P([])], { w: 200 })); tk.push(new X.TableRow({ cantSplit: true, children: rowk })); rowk = []; }
          }
          out.push(table(tk, [half, 200, half]));
          break;
        case 's_cert':
          out.push(T(b.title, { font: HF, size: 46, color: t.p, caps: true, cs: 60 }, { alignment: A.CENTER, spacing: { after: 60 } }), T(b.sub, { size: 15, color: TA, caps: true, cs: 90 }, { alignment: A.CENTER, spacing: { after: 360 } }),
            T(b.pre, { size: 11, color: t.mute }, { alignment: A.CENTER, spacing: { after: 160 } }),
            T(b.name, { font: HF, size: 34, italics: true, color: t.ink }, { alignment: A.CENTER, border: { bottom: { style: BS.SINGLE, size: 12, color: h(t.a), space: 4 } }, indent: { left: 2200, right: 2200 }, spacing: { after: 240 } }),
            T(b.reason, { size: 11.5 }, { alignment: A.CENTER, indent: { left: 1400, right: 1400 }, spacing: { after: 200 } }));
          out = out.concat(block({ t: 'sigs', items: b.sigs || [] }, { cw: cw - 2400, align: A.CENTER }));
          break;
        default: out.push(P(R(b.text || ''), { spacing: SP }));
      }
      return out;
    }

    /* ----- sections ----- */
    var sections = [], pageProps = function (m) {
      return { page: { size: sz.landscape ? { width: sz.th, height: sz.tw, orientation: X.PageOrientation.LANDSCAPE } : { width: sz.tw, height: sz.th },
        margin: { top: tw(m.t), right: tw(m.r), bottom: tw(m.b), left: tw(m.l), header: tw(30), footer: tw(28) } } };
    };
    var artRun = function (key) { return arts[key] ? imageRunFloating(arts[key], sz.w, sz.h) : null; };
    var logoRun = function (which) {
      var lp = which === 'cover' ? arts.logoC : arts.logoP; if (!lp) return null;
      return new X.ImageRun({ type: 'png', data: lp.data, transformation: { width: Math.round(lp.w), height: Math.round(lp.h) },
        floating: { horizontalPosition: { relative: X.HorizontalPositionRelativeFrom.PAGE, offset: D.pxToEmu(lp.x) }, verticalPosition: { relative: X.VerticalPositionRelativeFrom.PAGE, offset: D.pxToEmu(lp.y) },
          behindDocument: false, allowOverlap: true, zIndex: 251700000, wrap: { type: X.TextWrappingType.NONE } }, altText: { title: 'Logo', description: 'Logo', name: 'Logo' } });
    };
    var logoP = function (which) { var r = logoRun(which); return r ? P([r], { spacing: { before: 0, after: 0, line: 20, lineRule: X.LineRuleType.EXACT } }) : null; };

    /* ----- free elements: each goes into a tiny paragraph just before the first block on its page ----- */
    var anchors = {}, order = (doc.blocks || []).map(function (b) { return b.id; }), firstOn = {};
    order.forEach(function (id) { var p = pageOf[id]; if (p != null && firstOn[p] == null) firstOn[p] = id; });
    var sideTable = dz.kind === 'resume' && (dz.side === 'dark' || dz.side === 'tint');
    function anchorKey(L) {
      if (dz.kind === 'single' || sideTable) return 'start';
      if (dz.kind === 'panels') { var pp = L.pg === 'c' || L.pg === 'm' ? 0 : Math.max(0, +L.pg || 0); return 'pp' + pp; }
      if (L.pg === 'c') return dz.cover ? 'cover' : 'start';
      var p = Math.max(0, +L.pg || 0);
      if (firstOn[p] != null) return firstOn[p];
      for (var q = p + 1; q < p + 40; q++) if (firstOn[q] != null) return firstOn[q];
      for (var r = p - 1; r >= 0; r--) if (firstOn[r] != null) return firstOn[r];
      return 'start';
    }
    layers.forEach(function (L, i) {
      var key = anchorKey(L), run = null, z = 251660000 + i * 8;
      var picF = L.fill && L.fill.m === 'pic' && lpics[L.id];
      if ((L.k === 'shape' || L.k === 'text') && !picF) run = X.ImportedXmlComponent.fromXmlString(D.layerXML(L, t, 7001 + i, z, sf));
      else if (lpics[L.id]) {
        var lp = lpics[L.id], P0 = lp.P;
        run = new X.ImageRun({ type: 'png', data: lp.data, transformation: { width: D.num(L.w, 100, 1) + 2 * P0, height: D.num(L.h, 100, 1) + 2 * P0, rotation: L.rot ? D.num(L.rot, 0) : undefined },
          floating: { horizontalPosition: { relative: X.HorizontalPositionRelativeFrom.PAGE, offset: D.pxToEmu(D.num(L.x, 0) - P0) }, verticalPosition: { relative: X.VerticalPositionRelativeFrom.PAGE, offset: D.pxToEmu(D.num(L.y, 0) - P0) },
            behindDocument: !!L.behind, allowOverlap: true, zIndex: z, wrap: { type: X.TextWrappingType.NONE } },
          altText: { title: L.name || L.k, description: L.k === 'chart' ? ((L.ch && L.ch.title) || 'Chart') : (L.name || L.k), name: L.name || L.k } });
      }
      if (run) (anchors[key] = anchors[key] || []).push(run);
      if (picF && L.k === 'text' && L.tx) {   /* the words of a picture-filled text box go on top of its picture */
        var L2 = D.clone(L); L2.fill = { m: 'none' }; L2.ln = { m: 'none' }; L2.sh = null;
        anchors[key].push(X.ImportedXmlComponent.fromXmlString(D.layerXML(L2, t, 7601 + i, z + 1, sf)));
      }
    });
    function masterRun(L, i) {
      var lp = lpics[L.id]; if (!lp) return null;
      return new X.ImageRun({ type: 'png', data: lp.data, transformation: { width: D.num(L.w, 100, 1) + 2 * lp.P, height: D.num(L.h, 100, 1) + 2 * lp.P, rotation: L.rot ? D.num(L.rot, 0) : undefined },
        floating: { horizontalPosition: { relative: X.HorizontalPositionRelativeFrom.PAGE, offset: D.pxToEmu(D.num(L.x, 0) - lp.P) }, verticalPosition: { relative: X.VerticalPositionRelativeFrom.PAGE, offset: D.pxToEmu(D.num(L.y, 0) - lp.P) },
          behindDocument: !!L.behind, allowOverlap: true, zIndex: 251650000 + i, wrap: { type: X.TextWrappingType.NONE } }, altText: { title: L.name || 'Design', description: L.name || 'Design', name: 'Design' } });
    }
    var masterRuns = function () { var r = masters.map(masterRun).filter(Boolean); return r.length ? P(r, { spacing: { before: 0, after: 0, line: 20, lineRule: X.LineRuleType.EXACT } }) : null; };
    function anchorPara(key) {
      if (!anchors[key] || !anchors[key].length) return null;
      var r = anchors[key]; anchors[key] = null;
      return new X.Paragraph({ children: r, keepNext: true, spacing: { before: 0, after: 0, line: 20, lineRule: X.LineRuleType.EXACT } });
    }

    if (dz.kind === 'panels') {
      var PS = arts.panelSpec, grp = {}, lastK = [0, 0], tiny = { spacing: { before: 0, after: 0, line: 20, lineRule: X.LineRuleType.EXACT } };
      (doc.blocks || []).forEach(function (b) {
        if (b.hide) return;
        var rr = b.role && PS.roles && PS.roles[b.role];
        if (b.role && PS.roles && b.role in PS.roles && !rr) return;
        var pg = rr ? rr[0] : b.pg == null ? lastK[0] : Math.max(0, Math.min(PS.pages.length - 1, +b.pg || 0)), zn = rr ? rr[1] : b.pg == null ? lastK[1] : Math.max(0, Math.min(PS.pages[pg].zones.length - 1, +b.zn || 0));
        lastK = [pg, zn]; (grp[pg + '.' + zn] = grp[pg + '.' + zn] || []).push(b);
      });
      PS.pages.forEach(function (Pg, pi) {
        var zs = Pg.zones, kids = [], top = Math.min.apply(null, zs.map(function (z) { return z.y; }));
        var lgk = D.lgK(doc, !(PS.roles && PS.roles.logo ? PS.roles.logo[0] === pi : pi === 0));
        var zk = function (z, zi, only) {
          var f = D.panelFit(doc, pi, zi) * (z.lgTop && pics.__logo ? 0.78 : 0.92), al = z.align === 'center' ? A.CENTER : z.align === 'right' ? A.RIGHT : A.LEFT, out = [];
          (only || grp[pi + '.' + zi] || []).forEach(function (b) { var bs = D.blockSc(b); out = out.concat(block(b, { align: al, dark: z.dark, titlePt: Math.round((z.titlePt || 48) * f * bs), cw: tw(z.w), k: (z.k || 1) * f * bs, bc: /\bbc\b/.test(z.cls || ''), zw: z.w * bs, zh: z.h * bs, lgk: lgk * bs })); });
          if (only) return out;
          if (z.lgTop && pics.__logo) out = block({ t: 's_mark', id: 'lgtop' + pi + zi }, { align: al, dark: z.dark, cw: tw(z.w), k: 1, mk: 15, lgk: lgk, after: 40 }).concat(out);
          if (!out.length && z.auto === 'mark') out = out.concat(block({ t: 's_mark', id: 'auto' + pi + zi, text: D.initials(D.panelCompany(doc)) }, { align: al, dark: z.dark, cw: tw(z.w), k: 1, small: true, lgk: lgk }));
          return out;
        };
        var ar = artRun('panel' + pi); if (ar) kids.push(P([ar], tiny));
        if (pi === 0 && !PS.ownLogo) { var lg0 = logoP('cover'); if (lg0) kids.push(lg0); }
        var ap = anchorPara('pp' + pi); if (ap) kids.push(ap);
        var minX = Math.min.apply(null, zs.map(function (z) { return z.x; })), maxR = Math.max.apply(null, zs.map(function (z) { return z.x + z.w; }));
        /* a one-zone card page goes in a floating table, like the mixed card pages: Word lines are taller than the page's, so normal margins spill onto a blank third page */
        var bcOne = zs.some(function (z) { return /\bbc\b/.test(z.cls || ''); });
        if (zs.length === 1 && !bcOne) {
          var z0 = zs[0], k0 = zk(z0, 0), free = z0.h - 0;
          if (z0.v === 'bottom' || z0.v === 'center') {
            /* push the text down roughly like the page does: measured height is not known in Word, so use the zone's fit */
            var hgt = 0; Array.prototype.forEach.call(document.querySelectorAll('.dsd-pz[data-pg="' + pi + '"][data-zn="0"]'), function (el) { hgt = el.scrollHeight; });
            if (hgt && hgt < z0.h) kids.push(P([], { spacing: { before: tw(z0.v === 'bottom' ? z0.h - hgt : (z0.h - hgt) / 2), after: 0, line: 20, lineRule: X.LineRuleType.EXACT } }));
          }
          kids = kids.concat(k0.length ? k0 : [P([])]);
          sections.push({ properties: pageProps({ t: z0.y, r: Math.max(0, sz.w - z0.x - z0.w), b: 20, l: z0.x }), children: kids });
        } else if (bcOne || !zs.every(function (a, i) { return zs.every(function (b, j) { return i === j || a.x + a.w <= b.x + 1 || b.x + b.w <= a.x + 1; }); }) &&
          !zs.every(function (a, i) { return zs.every(function (b, j) { return i === j || a.y + a.h <= b.y + 1 || b.y + b.h <= a.y + 1; }); })) {
          /* zones beside AND above one another (cards): each zone is a small floating table at its own place on the page */
          zs.forEach(function (z, zi) {
            /* blocks moved freely on the page: each goes in its own small table where the page shows it */
            var all0 = grp[pi + '.' + zi] || [], moved = all0.filter(function (b) { return D.num(b.dx, 0) || D.num(b.dy, 0) || D.blockSc(b) !== 1; });
            if (moved.length) {
              grp[pi + '.' + zi] = all0.filter(function (b) { return moved.indexOf(b) < 0; });
              moved.forEach(function (b) {
                var r = D.blockRectOnPage(b.id, pi, sz), ob = zk(z, zi, [b]); if (!r || !ob.length) { grp[pi + '.' + zi].push(b); return; }
                kids.push(new X.Table({ rows: [new X.TableRow({ children: [cell(ob, { w: tw(r.w), margins: { top: 0, bottom: 0, left: 0, right: 0 } })] })],
                  width: { size: tw(r.w), type: WT.DXA }, columnWidths: [tw(r.w)], layout: X.TableLayoutType.FIXED, borders: noBorders,
                  float: { horizontalAnchor: X.TableAnchorType.PAGE, verticalAnchor: X.TableAnchorType.PAGE, absoluteHorizontalPosition: tw(Math.max(0, r.x)), absoluteVerticalPosition: tw(Math.max(0, r.y)), overlap: X.OverlapType.OVERLAP } }));
                kids.push(P([], tiny));
              });
            }
            var ck = zk(z, zi); grp[pi + '.' + zi] = all0; if (!ck.length) return;
            var VA2 = X.VerticalAlign;
            kids.push(new X.Table({ rows: [new X.TableRow({ cantSplit: true, height: { value: tw(z.h * 0.9), rule: X.HeightRule.ATLEAST }, children: [cell(ck, { w: tw(z.w), margins: { top: 0, bottom: 0, left: 0, right: 0 }, va: z.v === 'bottom' ? VA2.BOTTOM : z.v === 'center' ? VA2.CENTER : VA2.TOP })] })],
              width: { size: tw(z.w), type: WT.DXA }, columnWidths: [tw(z.w)], layout: X.TableLayoutType.FIXED, borders: noBorders,
              float: { horizontalAnchor: X.TableAnchorType.PAGE, verticalAnchor: X.TableAnchorType.PAGE, absoluteHorizontalPosition: tw(z.x), absoluteVerticalPosition: tw(z.y), overlap: X.OverlapType.OVERLAP } }));
            kids.push(P([], tiny));
          });
          sections.push({ properties: pageProps({ t: 4, r: 4, b: 4, l: 4 }), children: kids });
        } else if (!zs.every(function (a, i) { return zs.every(function (b, j) { return i === j || a.x + a.w <= b.x + 1 || b.x + b.w <= a.x + 1; }); })) {
          /* zones above one another: one table row per zone */
          var W0 = tw(maxR - minX), rws = [], py = top, VA = X.VerticalAlign;
          zs.map(function (z, i) { return i; }).sort(function (a, b) { return zs[a].y - zs[b].y; }).forEach(function (zi) {
            var z = zs[zi];
            if (z.y - py > 2) rws.push(new X.TableRow({ height: { value: tw(z.y - py), rule: X.HeightRule.EXACT }, children: [cell([P([], tiny)], { w: W0, margins: { top: 0, bottom: 0, left: 0, right: 0 } })] }));
            rws.push(new X.TableRow({ cantSplit: true, height: { value: tw(z.h * 0.85), rule: X.HeightRule.ATLEAST }, children: [cell(zk(z, zi), { w: W0, va: z.v === 'bottom' ? VA.BOTTOM : z.v === 'center' ? VA.CENTER : VA.TOP,
              margins: { top: 0, bottom: 0, left: tw(z.x - minX), right: tw(Math.max(0, maxR - z.x - z.w)) } })] }));
            py = z.y + z.h;
          });
          kids.push(table(rws, [W0])); kids.push(P([], tiny));
          sections.push({ properties: pageProps({ t: top, r: Math.max(0, sz.w - maxR), b: 4, l: minX }), children: kids });
        } else {
          var order2 = zs.map(function (z, i) { return i; }).sort(function (a, b) { return zs[a].x - zs[b].x; }), widths = [], cells = [], px = minX;
          var rowH = (Math.max.apply(null, zs.map(function (z) { return z.y + z.h; })) - top) * 0.82;
          order2.forEach(function (zi) {
            var z = zs[zi];
            if (z.x - px > 2) { widths.push(tw(z.x - px)); cells.push(cell([P([])], { w: tw(z.x - px), margins: { top: 0, bottom: 0, left: 0, right: 0 } })); }
            var ck = zk(z, zi), pre = z.y - top;
            if (pre > 2) ck.unshift(P([], { spacing: { before: tw(pre), after: 0, line: 20, lineRule: X.LineRuleType.EXACT } }));
            widths.push(tw(z.w)); cells.push(cell(ck, { w: tw(z.w), margins: { top: 0, bottom: 0, left: 0, right: 0 }, va: z.v === 'bottom' ? X.VerticalAlign.BOTTOM : z.v === 'center' ? X.VerticalAlign.CENTER : X.VerticalAlign.TOP }));
            px = z.x + z.w;
          });
          kids.push(table([new X.TableRow({ cantSplit: true, height: { value: tw(rowH), rule: X.HeightRule.ATLEAST }, children: cells })], widths));
          kids.push(P([], tiny));
          sections.push({ properties: pageProps({ t: top, r: Math.max(0, sz.w - maxR), b: 4, l: minX }), children: kids });
        }
      });
    } else if (dz.kind === 'single') {
      var Ls = arts.singleL, z = Ls.zone, al = z.align === 'center' ? A.CENTER : A.LEFT;
      var kids = [];
      (doc.blocks || []).forEach(function (b) { if (!b.hide) kids = kids.concat(block(b, { align: al, dark: z.dark, titlePt: Ls.titlePt, cw: tw(z.w) })); });
      if (!kids.length) kids.push(P([]));
      var first = artRun('single');
      var sa = anchorPara('start'); if (sa) kids.unshift(sa);
      if (first) kids.unshift(P([first], { spacing: { after: 0, line: 240 } }));
      var lgS = logoP('cover'); if (lgS) kids.unshift(lgS);
      sections.push({ properties: pageProps({ t: z.y, r: sz.w - z.x - z.w, b: 40, l: z.x }), children: kids });
    } else {
      if (dz.kind === 'flow' && dz.cover) {
        var cz = arts.coverZone, al2 = cz.align === 'center' ? A.CENTER : A.LEFT, tl2 = String(doc.title || '').length, cts = Math.round((tl2 > 60 ? 30 : tl2 > 38 ? 36 : tl2 > 22 ? 42 : 48) * (cz.ts || 1));
        var ckids = [];
        var cr = artRun('cover');
        if (cr) ckids.push(P([cr], { spacing: { after: 0, line: 240 } }));
        var lgC = logoP('cover'); if (lgC) ckids.push(lgC);
        var ca = anchorPara('cover'); if (ca) ckids.push(ca);
        var tc = cz.dark ? '#FFFFFF' : t.p;
        var cT = function (f, text, ro, po) {
          if ((doc.chide || {})[f] || (doc.cfree || {})[f]) return P([], { spacing: { after: 0, line: 20, lineRule: X.LineRuleType.EXACT } });
          var sv = CS; CS = (doc.cst || {})[f] || null;
          if (CS && CS.al) po = Object.assign({}, po, { alignment: { left: A.LEFT, center: A.CENTER, right: A.RIGHT, justify: A.JUSTIFIED }[CS.al] });
          var pp = T(text, ro, po); CS = sv; return pp;
        };
        if (doc.kicker) ckids.push(cT('kicker', doc.kicker, { bold: true, size: 9.5, color: TA, caps: true, cs: 40 }, { alignment: al2, spacing: { after: 200 } }));
        ckids.push(cT('title', doc.title || '', { font: HF, bold: true, size: cts, color: tc }, { alignment: al2, spacing: { after: 240, line: 250 } }));
        if (doc.subtitle) ckids.push(cT('subtitle', doc.subtitle, { size: 13, color: cz.dark ? '#EEF1F6' : t.mute }, { alignment: al2, spacing: { after: 0 } }));
        // author / organisation / date: in the cover's footer when the design puts them low on the page
        // (Word keeps footers at the bottom), otherwise a little below the title.
        var mz = cz.meta, mc = mz.dark ? '#FFFFFF' : t.ink, mal = mz.align === 'center' ? A.CENTER : mz.align === 'right' ? A.RIGHT : A.LEFT, mind = mz.align === 'right' ? { right: tw(Math.max(0, cz.x + cz.w - mz.x - mz.w)) } : { left: tw(Math.max(0, mz.x - cz.x)) };
        var metaP = [cT('author', doc.author || '', { bold: true, color: mz.dark ? '#FFFFFF' : t.p, size: 10 }, { alignment: mal, indent: mind, spacing: { after: 0 } }),
          cT('org', doc.org || '', { color: mc, size: 10 }, { alignment: mal, indent: mind, spacing: { after: 0 } }), cT('date', doc.date || '', { color: mc, size: 10 }, { alignment: mal, indent: mind, spacing: { after: 0 } })];
        var coverSec = { properties: pageProps({ t: cz.y, r: sz.w - cz.x - cz.w, b: 30, l: cz.x }), children: ckids };
        if (mz.y > sz.h * 0.62) {
          coverSec.properties.page.margin.bottom = tw(sz.h - mz.y - 4);
          coverSec.properties.page.margin.footer = tw(Math.max(24, sz.h - mz.y - 62));
          coverSec.footers = { default: new X.Footer({ children: metaP }) };
        } else {
          var cpl = Math.max(8, cz.w / (cts * 1.333 * 0.55)), tLines = Math.ceil(tl2 / cpl), sLines = Math.ceil(String(doc.subtitle || '').length / (cz.w / (13 * 1.333 * 0.5)));
          var used = (doc.kicker ? 34 : 0) + tLines * cts * 1.333 * 1.25 + 18 + sLines * 24;
          ckids.push(P([], { spacing: { before: tw(Math.max(16, Math.min(mz.y - cz.y - used, sz.h - cz.y - used - 160))), after: 0 } }));
          ckids.push.apply(ckids, metaP);
        }
        sections.push(coverSec);
      }
      // header blocks for page 1 (letterhead / form title)
      var blocks = doc.blocks || [], k2 = 0, hb2 = [];
      if (dz.kind === 'flow' && !dz.cover) while (k2 < blocks.length && (blocks[k2].t === 'lh' || blocks[k2].t === 'title')) hb2.push(blocks[k2++]);
      if (!hb2.length && dz.kind === 'flow' && !dz.cover && dz.first) hb2 = [{ t: 'title', text: doc.title || '', sub: [doc.kicker, doc.subtitle].filter(Boolean).join(' · ') }];
      hb2 = hb2.filter(function (x) { return !x.hide; });
      var bodyBlocks = blocks.slice(k2).filter(function (x) { return !x.hide; });

      var headerKids = [], firstKids = [];
      var pr = artRun('page'); if (pr) headerKids.push(P([pr], { spacing: { after: 0 } }));
      var lgH = logoP(); if (lgH) headerKids.push(lgH);
      var mrH = masterRuns(); if (mrH) headerKids.unshift(mrH);
      if (dz.kind === 'flow' && doc.title && dz.head !== 'chapter') headerKids.push(T(doc.title, { size: 8, color: t.mute, caps: true, cs: 12 }, { alignment: A.RIGHT, spacing: { after: 0 } }));
      if (!headerKids.length) headerKids.push(P([]));
      var useFirst = !!(arts.first || hb2.length || D.isBand(dz));
      if (useFirst) {
        var fr = artRun('first') || (D.isBand(dz) ? null : null);
        if (fr) firstKids.push(P([fr], { spacing: { after: 0 } }));
        var lgF = logoP(); if (lgF) firstKids.push(lgF);
        var dark = dz.hbDark != null ? dz.hbDark : dz.first === 'block', al3 = dz.hbAlign === 'center' || dz.first === 'classic' ? A.CENTER : A.LEFT;
        firstKids.push(P([], { spacing: { after: tw(dz.hbTop ? Math.max(4, dz.hbTop - 40) : (dz.first === 'block' ? 4 : 8)) } }));
        hb2.forEach(function (b) { firstKids = firstKids.concat(block(b, { dark: dark, align: al3 })); });
        firstKids.push(P([], { spacing: { after: tw(dz.first === 'block' ? 40 : 18) } }));
      }
      var footer = new X.Footer({ children: [P([new X.TextRun({ children: [X.PageNumber.CURRENT], size: 18, color: h(t.mute) })], { alignment: dz.page === 'siderail' || dz.page === 'leftrail' ? A.LEFT : A.RIGHT })] });
      var children = [];
      if (dz.kind === 'resume' && (dz.side === 'dark' || dz.side === 'tint')) {
        var sideW = tw(dz.sw - dz.m.l - 22), gapw2 = tw(50), mainW = CW - sideW - gapw2;
        var sk = [], mk = [], darkSide = dz.side === 'dark';
        bodyBlocks.forEach(function (b) {
          if (b.side) sk = sk.concat(block(b, { cw: sideW, dark: darkSide }));
          else mk = mk.concat(block(b, { cw: mainW }));
        });
        children.push(table([new X.TableRow({ children: [cell(sk, { w: sideW, margins: { top: 0, bottom: 0, left: 0, right: 0 } }), cell([P([])], { w: gapw2 }), cell(mk, { w: mainW, margins: { top: 0, bottom: 0, left: 0, right: 0 } })] })], [sideW, gapw2, mainW]));
      } else {
        var ro = {};
        if (dz.kind === 'resume') {
          if (dz.id === 'resume-minimal' || dz.rmode === 'centre') ro.align = A.CENTER;
          if (dz.id === 'resume-timeline' || dz.rmode === 'timeline') ro.timeline = true;
        }
        bodyBlocks.forEach(function (b, bi) {
          var o2 = {}; for (var kk in ro) o2[kk] = ro[kk];
          if (b.t === 'r_head' && D.isBand(dz)) o2.band = true;
          if (ro.align && b.t !== 'r_head' && b.t !== 'r_sec' && b.t !== 'r_skills') o2.align = undefined;
          var ap = anchorPara(b.id); if (ap) children.push(ap);
          children = children.concat(block(b, o2));
        });
      }
      var st0 = anchorPara('start'); if (st0) children.unshift(st0);
      Object.keys(anchors).forEach(function (k) { var rest = anchorPara(k); if (rest) children.push(rest); });
      if (!children.length) children.push(P([]));
      if (D.isBand(dz) && !artOn) {
        // name sits inside the colour band: band picture goes behind page 1 only
        var bandSvg = (D.ART.page[dz.page] || D.ART.page.headband)(t, sz.w, sz.h, dz);
        return D.svgToPng(bandSvg, sz.w, sz.h, 1.6).then(function (bytes) {
          children.unshift(P([imageRunFloating(bytes, sz.w, sz.h)], { spacing: { after: 0, line: 240 } }));
          var lgB = logoP(); if (lgB) children.unshift(lgB);
          sections.push({ properties: pageProps(dz.m), children: children, footers: { default: footer } });
          return finish();
        });
      }
      var sec = { properties: pageProps(dz.m), headers: { default: new X.Header({ children: headerKids }) }, footers: { default: footer }, children: children };
      if (dz.kind === 'flow' && dz.cover) sec.properties.page.pageNumbers = { start: 1 };
      if (useFirst) { sec.properties.titlePage = true; sec.headers.first = new X.Header({ children: firstKids.length ? firstKids : [P([])] }); sec.footers.first = new X.Footer({ children: [P([])] }); }
      if (dz.kind === 'resume') { sec.footers = { default: new X.Footer({ children: [P([])] }) }; }
      sections.push(sec);
    }
    return finish();

    function finish() {
      var docx = new X.Document({
        creator: doc.author || 'DesignStager', title: doc.title || 'Document', description: 'Made with DesignStager',
        styles: {
          default: {
            document: { run: { font: BF, size: sizeHp(dz.body), color: h(t.ink) }, paragraph: { spacing: { after: 160, line: 300, lineRule: X.LineRuleType.AUTO } } },
            heading1: { run: { font: HF, size: sizeHp(dz.h1), bold: true, color: h(t.p) }, paragraph: { spacing: { before: 320, after: 160 } } },
            heading2: { run: { font: HF, size: sizeHp(dz.h2), bold: true, color: h(t.s) }, paragraph: { spacing: { before: 240, after: 100 } } },
            heading3: { run: { font: HF, size: 23, bold: true, color: h(t.ink) }, paragraph: { spacing: { before: 200, after: 80 } } },
            title: { run: { font: HF, size: 56, bold: true, color: h(t.p) } }
          }
        },
        numbering: numbering,
        sections: sections
      });
      return opts.base64 ? X.Packer.toBase64String(docx) : X.Packer.toBlob(docx);
    }
  });
};
/* =====================================================================================
 * Marketing & Advertising and Events & Print collection
 * "panels" designs: one or more pages (front / back, outside / inside), each with one or
 * more text zones. Every zone sits on a solid plate drawn in the artwork, so text never sits
 * on top of busy shapes; zones that are too full shrink their text until it fits.
 * ===================================================================================== */
(function () {
  /* ---------- rendering ---------- */
  D._fit = D._fit || {};
  function zoneVars(z) {
    var k = z.k || 1;
    return '--k:' + k + ';--bs:calc(' + (10.5 * k).toFixed(2) + 'pt * var(--fit,1));--h1s:calc(' + (20 * k).toFixed(2) + 'pt * var(--fit,1));--h2s:calc(' + (13.5 * k).toFixed(2) + 'pt * var(--fit,1))';
  }
  function zoneOf(spec, b) {
    if (b.role && spec.roles && b.role in spec.roles) return spec.roles[b.role];   /* null: this design has no place for it */
    var pg = Math.max(0, Math.min(spec.pages.length - 1, +b.pg || 0)), zs = spec.pages[pg].zones;
    return [pg, Math.max(0, Math.min(zs.length - 1, +b.zn || 0))];
  }
  D.panelSpec = function (doc, t, dz, sz) {
    var f = D.PANEL[dz.layout] || D.PANEL.flyer2;
    return f(doc, t, sz.w, sz.h, dz, sz);
  };
  /* where a block shows on its page (page px), read from the editor if it is open, else from any render */
  D.blockRectOnPage = function (id, pi, sz) {
    var els = Array.prototype.slice.call(document.querySelectorAll('.dsd-blk[data-id="' + String(id).replace(/"/g, '') + '"]')).filter(function (e) { return e.getBoundingClientRect().width > 0; });
    var e = els.filter(function (x) { return x.closest('.dsd-edit'); })[0] || els[0]; if (!e) return null;
    var fl = e.closest('.dsd-flow'), pg = fl && fl.querySelector('.dsd-page[data-pg="' + pi + '"]'); if (!pg) return null;
    var pr = pg.getBoundingClientRect(), k = pr.width / sz.w || 1, r = e.getBoundingClientRect();
    return { x: (r.left - pr.left) / k, y: (r.top - pr.top) / k, w: Math.max(30, r.width / k + 4), h: r.height / k };
  };
  /* logo size on cards: doc.logo.bc = { f: front 0-100, b: back 0-100, back: 0 hides the small back logo }; 50 is the normal size */
  D.lgK = function (doc, back) { var c = doc && doc.logo && doc.logo.bc || {}, v = D.num(back ? c.b : c.f, 50); return Math.round((v <= 50 ? 0.5 + v / 100 : 1 + (v - 50) / 50) * 100) / 100; };
  /* a block on a card page keeps its place in its zone; b.dx, b.dy shift it (page px) and b.sc makes it larger or smaller (0.3-5) */
  D.blockSc = function (b) { return Math.round(D.num(b && b.sc, 1, 0.3, 5) * 100) / 100; };
  D.offBlock = function (html, b) {
    var dx = D.num(b.dx, 0), dy = D.num(b.dy, 0), sc = D.blockSc(b); if (!dx && !dy && sc === 1) return html;
    /* zoom also enlarges the shift, so the shift is divided by it */
    var tr = (dx || dy ? 'translate:' + Math.round(dx / sc * 100) / 100 + 'px ' + Math.round(dy / sc * 100) / 100 + 'px;' : '') + (sc !== 1 ? 'zoom:' + sc + ';' : ''), i = html.indexOf('>'), head = html.slice(0, i);
    head = /\sstyle="/.test(head) ? head.replace(/\sstyle="/, ' style="' + tr) : head + ' style="' + tr + '"';
    if (sc !== 1) head += ' data-sc="' + sc + '"';
    return head + html.slice(i);
  };
  D.renderPanels = function (root, doc, ctx, t, dz, sz, gap) {
    var spec = D.panelSpec(doc, t, dz, sz), n = spec.pages.length, pitch = sz.h + gap, artOn = D.artOn(doc), o = ctx.o;
    var flow = document.createElement('div');
    flow.className = 'dsd-flow dsd-panels'; flow.style.width = sz.w + 'px'; flow.style.height = (n * pitch - gap) + 'px';
    root.appendChild(flow);
    var sheets = '<div class="dsd-sheets">';
    spec.pages.forEach(function (P, p) {
      sheets += '<div class="dsd-page" data-pg="' + p + '" style="top:' + (p * pitch) + 'px;width:' + sz.w + 'px;height:' + sz.h + 'px"><div class="dsd-art" style="background-image:' + artURL(artOn ? D.artBg(P.svg) : P.svg) + '"></div>' +
        (p === 0 && !spec.ownLogo ? D.logoHTML(doc, sz, 'cover') : '') + '</div>';
    });
    flow.insertAdjacentHTML('beforeend', sheets + '</div>');
    var layBack = document.createElement('div'); layBack.className = 'dsd-lay dsd-lay-b'; flow.appendChild(layBack);
    /* blocks into their zones; a block without a page follows the one before it */
    var groups = {}, last = [0, 0];
    (doc.blocks || []).forEach(function (b) {
      if (b.hide) return;
      var mapped = b.role && spec.roles && b.role in spec.roles, key = b.pg == null && !mapped ? last : zoneOf(spec, b);
      if (!key) return; last = key;
      (groups[key[0] + '.' + key[1]] = groups[key[0] + '.' + key[1]] || []).push(b);
    });
    var zh = '';
    spec.pages.forEach(function (P, p) {
      P.zones.forEach(function (z, zi) {
        ctx.titlePt = z.titlePt || 48;
        var inner = (groups[p + '.' + zi] || []).map(function (b) { return D.offBlock(D.blockHTML(b, ctx), b); }).join('');
        if (z.lgTop && doc.logo && doc.logo.src && !doc.logo.hide) inner = '<div class="dsd-s-mk dsd-s-mk2"><img alt="Logo" src="' + D.esc(doc.logo.src) + '"></div>' + inner;
        if (!inner && z.auto === 'mark') { var lg0 = doc.logo && doc.logo.src && !doc.logo.hide ? doc.logo.src : ''; inner = '<div class="dsd-s-mk">' + (lg0 ? '<img alt="Logo" src="' + D.esc(lg0) + '">' : '<span class="mono">' + D.esc(D.initials(D.panelCompany(doc))) + '</span>') + '</div>'; }
        var dk = z.dark ? ';--ink:#FFFFFF;--p:#FFFFFF;--s:' + t.a + ';--mute:rgba(255,255,255,.82);--line:rgba(255,255,255,.35);' + D.darkAt(t) : '';
        zh += '<div class="dsd-sz dsd-pz' + (z.dark ? ' dark' : '') + (z.cls ? ' ' + z.cls : '') + '" data-pg="' + p + '" data-zn="' + zi + '" data-h="' + Math.round(z.h) + '" style="left:' + Math.round(z.x) + 'px;top:' + Math.round(p * pitch + z.y) + 'px;width:' + Math.round(z.w) + 'px;height:' + Math.round(z.h) + 'px;' +
          '--lgk:' + D.lgK(doc, !(spec.roles && spec.roles.logo ? spec.roles.logo[0] === p : p === 0)) + ';text-align:' + z.align + ';align-items:' + (z.align === 'center' ? 'center' : z.align === 'right' ? 'flex-end' : 'flex-start') + ';justify-content:' + (z.v === 'bottom' ? 'flex-end' : z.v === 'center' ? 'center' : 'flex-start') + ';' + zoneVars(z) + dk + (z.vars ? ';' + z.vars : '') + '">' + inner + '</div>';
      });
    });
    flow.insertAdjacentHTML('beforeend', zh);
    var layFront = document.createElement('div'); layFront.className = 'dsd-lay dsd-lay-f'; flow.appendChild(layFront);
    var fk = D.artKey(doc) + '|' + (doc.id || '');
    function fit() {
      var res = {};
      Array.prototype.forEach.call(flow.querySelectorAll('.dsd-pz'), function (el) {
        var h = +el.getAttribute('data-h'), f = 1;
        if (el.querySelector('.dsd-blk[data-sc]')) { res[el.getAttribute('data-pg') + '.' + el.getAttribute('data-zn')] = 1; el.style.setProperty('--fit', '1'); return; }   /* sizes set by hand are kept */
        var jc = el.style.justifyContent;
        el.style.setProperty('--fit', '1'); el.style.justifyContent = 'flex-start';
        while (el.scrollHeight > h + 1 && f > 0.42) { f = Math.round((f - 0.04) * 100) / 100; el.style.setProperty('--fit', String(f)); }
        el.style.justifyContent = jc;
        res[el.getAttribute('data-pg') + '.' + el.getAttribute('data-zn')] = f;
      });
      D._fit[fk] = res;
    }
    var api = {
      root: root, flow: flow, cover: null, pages: n, flowPages: n, pitch: pitch, panels: true,
      drawLayers: function () {
        layBack.innerHTML = ''; layFront.innerHTML = '';
        D.placeLayers(doc, t, o, function (pg) { var q = pg === 'c' ? 0 : Math.max(0, Math.min(n - 1, +pg || 0)); return { back: layBack, front: layFront, dy: q * pitch }; });
      },
      blockPages: function () { var m = {}, lastP = 0; (doc.blocks || []).forEach(function (b) { var mp = b.role && spec.roles && b.role in spec.roles, k = b.pg == null && !mp ? null : zoneOf(spec, b); if (k) lastP = k[0]; m[b.id] = lastP; }); return m; },
      layout: function () { fit(); api.drawLayers(); return n; }
    };
    api.layout();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { fit(); });
    return api;
  };
  /* the company name of a card (for the initials badge when no logo was added) */
  D.panelCompany = function (doc) { var c = (doc.blocks || []).filter(function (b) { return b.role === 'company'; })[0]; return (c && c.text) || doc.org || ''; };
  D.panelFit = function (doc, p, zi) { var r = D._fit[D.artKey(doc) + '|' + (doc.id || '')]; return (r && r[p + '.' + zi]) || 1; };

  /* ---------- drawing kit ---------- */
  function ib(b, p) { return { x: b.x + p, y: b.y + p, w: b.w - 2 * p, h: b.h - 2 * p }; }
  function g(x, y, inner, sc) { return '<g transform="translate(' + x + ',' + y + ')' + (sc ? ' scale(' + sc + ')' : '') + '">' + inner + '</g>'; }
  function dark(c) { return D.lum(c) < 0.33; }
  /* a panel that fills the whole page needs no wrapper: its shapes then sit straight on the page (so they can be
     taken apart into editable shapes one by one, and the page colour stays the page colour) */
  function full(r, W, H) {
    var a = '<g transform="translate(0,0)"><svg x="0" y="0" width="' + W + '" height="' + H + '" overflow="hidden">';
    if (r.svg.indexOf(a) === 0 && r.svg.slice(-10) === '</svg></g>') r.svg = r.svg.slice(a.length, -10);
    return r;
  }
  function plate(b, fill, o) {
    o = o || {}; var s = '';
    if (o.shadow) s += rect(b.x + o.shadow, b.y + o.shadow * 1.4, b.w, b.h, '#0B1220', 0.13, o.rx);
    s += rect(b.x, b.y, b.w, b.h, fill, null, o.rx);
    if (o.edge) s += rect(b.x, b.y, o.ew || 6, b.h, o.edge, null);
    if (o.top) s += rect(b.x, b.y, b.w, o.th || 6, o.top);
    if (o.line) s += '<rect x="' + (b.x + 0.5) + '" y="' + (b.y + 0.5) + '" width="' + (b.w - 1) + '" height="' + (b.h - 1) + '" fill="none" stroke="' + o.line + '" stroke-width="' + (o.lw || 1.5) + '"' + (o.rx ? ' rx="' + o.rx + '"' : '') + '/>';
    return s;
  }
  function chev(x, y, s, c, op) { return poly([[x, y], [x + s * 0.5, y], [x + s, y + s * 0.5], [x + s * 0.5, y + s], [x, y + s], [x + s * 0.5, y + s * 0.5]], c, op); }
  function tri(x, y, s, c, op, r) { return r ? poly([[x, y], [x + s, y], [x, y + s]], c, op) : poly([[x, y + s], [x + s, y + s], [x + s, y]], c, op); }
  function stripes(x, y, w, h, n, c, op, ang) {
    var s = '', gp = w / n;
    for (var i = 0; i < n; i++) s += poly([[x + i * gp, y + h], [x + i * gp + gp * 0.45, y + h], [x + i * gp + gp * 0.45 + h * (ang || 0.6), y], [x + i * gp + h * (ang || 0.6), y]], c, op);
    return '<svg x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" overflow="hidden"><g transform="translate(' + (-x) + ',' + (-y) + ')">' + s + '</g></svg>';
  }
  function arcs(cx, cy, r0, n, step, c, w, op) { var s = ''; for (var i = 0; i < n; i++) s += ring(cx, cy, r0 + i * step, c, w, op); return s; }
  function plus(cx, cy, s, w, c, op) { return rect(cx - s / 2, cy - w / 2, s, w, c, op) + rect(cx - w / 2, cy - s / 2, w, s, c, op); }
  function hex(cx, cy, r, c, op) { var p = []; for (var i = 0; i < 6; i++) { var a = Math.PI / 3 * i + Math.PI / 6; p.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]); } return poly(p, c, op); }
  function halftone(x, y, w, h, gp, rmax, c) {
    var s = ''; for (var i = 0; i * gp < w; i++) for (var j = 0; j * gp < h; j++) { var r = rmax * (1 - (i * gp) / w) * 0.95 + 0.4; if (r > 0.6) s += circ(x + i * gp, y + j * gp, r, c); } return s;
  }
  function zigzag(x, y, w, amp, n, c, sw) { var d = 'M' + x + ' ' + y, st = w / n; for (var i = 1; i <= n; i++) d += ' L' + (x + i * st) + ' ' + (y + (i % 2 ? -amp : 0)); return path(d, 'none', null, c, sw); }

  /* A motif draws the design of one panel (x, y, w, h) and returns where the text plate goes.
   * box: the plate; fill: plate colour (null = page colour shows, still drawn so nothing crosses it). */
  var MO = {};
  function M(name, f) { MO[name] = f; }
  /* each returns { bg, deco, box, fill, po (plate options), align, v } in panel coordinates */
  M('split', function (t, W, H) {
    var land = W / H > 0.85, sw = land ? W * 0.34 : W, sh = land ? H : H * 0.44, u = Math.min(W, H);
    var d = rect(0, 0, sw, sh, t.p) + circ(land ? sw : W * 0.78, land ? H * 0.3 : sh, u * 0.13, t.a) + ring(land ? sw : W * 0.78, land ? H * 0.3 : sh, u * 0.19, t.s, Math.max(1.5, u * 0.006)) +
      dots(u * 0.07, (land ? H * 0.7 : sh * 0.18), 5, 4, u * 0.035, Math.max(1.2, u * 0.005), '#FFFFFF', 0.45);
    var box = land ? { x: sw + u * 0.26, y: H * 0.12, w: W - sw - u * 0.33, h: H * 0.76 } : { x: u * 0.08, y: sh + u * 0.24, w: W - u * 0.16, h: H - sh - u * 0.31 };
    return { bg: '#FFFFFF', deco: d, box: box, fill: '#FFFFFF', align: 'left', v: land ? 'center' : 'top' };
  });
  M('topband', function (t, W, H) {
    var u = Math.min(W, H), bh = H * 0.5;
    var d = rect(0, 0, W, bh, t.p) + circ(W * 0.9, bh * 0.08, u * 0.24, t.s, 0.85) + circ(W * 0.86, bh, u * 0.07, t.a) + rect(0, bh - u * 0.012, W * 0.4, u * 0.012, t.a);
    var box = { x: u * 0.08, y: bh * 0.4, w: W - u * 0.3, h: bh * 0.52 };
    return { bg: '#FFFFFF', deco: d, box: box, fill: null, align: 'left', v: 'bottom', dk: 1,
      box2: { x: u * 0.08, y: bh + u * 0.08, w: W - u * 0.16, h: H - bh - u * 0.16 } };
  });
  M('frame', function (t, W, H) {
    var u = Math.min(W, H), m = u * 0.05, s = u * 0.07;
    var d = rect(0, 0, W, H, t.tint) + '<rect x="' + m + '" y="' + m + '" width="' + (W - 2 * m) + '" height="' + (H - 2 * m) + '" fill="#FFFFFF" stroke="' + t.p + '" stroke-width="' + Math.max(1.5, u * 0.006) + '"/>' +
      rect(m - s / 2, m - s / 2, s, s, t.a) + rect(W - m - s / 2, H - m - s / 2, s, s, t.a) + rect(W - m - s / 2, m - s / 2, s, s, t.p) + rect(m - s / 2, H - m - s / 2, s, s, t.p);
    return { bg: t.tint, deco: d, box: { x: m + u * 0.1, y: m + u * 0.12, w: W - 2 * m - u * 0.2, h: H - 2 * m - u * 0.24 }, fill: '#FFFFFF', align: 'center', v: 'center' };
  });
  M('diagonal', function (t, W, H) {
    var u = Math.min(W, H);
    var d = poly([[0, H * 0.62], [W, H * 0.4], [W, H], [0, H]], t.p) + poly([[0, H * 0.62], [W, H * 0.4], [W, H * 0.44], [0, H * 0.66]], t.a) + stripes(W * 0.55, H * 0.72, W * 0.4, H * 0.2, 6, '#FFFFFF', 0.12);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.07, w: W - u * 0.16, h: H * 0.4 - u * 0.04 - H * 0.07 + (H * 0.62 - H * 0.4) * 0.15 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('arcs', function (t, W, H) {
    var u = Math.min(W, H), cx = W, cy = H, box = { x: u * 0.08, y: u * 0.1, w: W * 0.62, h: H * 0.5 };
    var lim = Math.hypot(cx - (box.x + box.w), cy - (box.y + box.h)) - u * 0.04;
    var d = circ(cx, cy, lim * 0.42, t.p) + arcs(cx, cy, lim * 0.55, 4, lim * 0.12, t.s, Math.max(2, u * 0.012)) + circ(cx - lim * 0.9, cy - lim * 0.2, u * 0.03, t.a);
    return { bg: '#FFFFFF', deco: d, box: box, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('pillars', function (t, W, H) {
    var u = Math.min(W, H), n = 5, pw = W * 0.06, d = '';
    for (var i = 0; i < n; i++) d += rect(W - (i + 1) * pw * 1.35, H * (0.15 + i * 0.12), pw, H, [t.p, t.s, t.a, t.p, t.s][i], 1, pw / 2);
    var bx = W - n * pw * 1.35 - u * 0.06;
    return { bg: t.tint, deco: d, box: { x: u * 0.08, y: H * 0.12, w: bx - u * 0.08, h: H * 0.76 }, fill: t.tint, align: 'left', v: 'center' };
  });
  M('dotgrid', function (t, W, H) {
    var u = Math.min(W, H), gp = u * 0.05, d = dots(W * 0.55, u * 0.08, Math.floor(W * 0.4 / gp), Math.floor(H * 0.32 / gp), gp, Math.max(1.5, u * 0.007), t.p, 0.55) +
      rect(W * 0.62, H * 0.12, u * 0.28, u * 0.28, t.a, 0.9) + rect(u * 0.08, H - u * 0.1, W * 0.3, u * 0.018, t.p);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.42, w: W - u * 0.16, h: H * 0.46 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('sun', function (t, W, H) {
    var u = Math.min(W, H), r = Math.min(W * 0.55, H * 0.36);
    var d = rect(0, 0, W, H, t.p) + circ(W / 2, 0, r * 1.25, t.s, 0.6) + circ(W / 2, 0, r, t.a) + circ(W / 2, 0, r * 0.7, D.mix(t.a, '#FFFFFF', 0.35));
    return { bg: t.p, deco: d, box: { x: u * 0.09, y: r * 1.25 + u * 0.08, w: W - u * 0.18, h: H - r * 1.25 - u * 0.16 }, fill: t.p, align: 'center', v: 'center' };
  });
  M('waves', function (t, W, H) {
    var u = Math.min(W, H);
    var d = wave(W, H * 0.7, H * 0.035, t.s, 0.55, 0, H) + wave(W, H * 0.76, H * 0.04, t.p, 1, 1, H) + wave(W, H * 0.86, H * 0.03, t.a, 1, 0, H);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.08, w: W - u * 0.16, h: H * 0.56 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('mosaic', function (t, W, H) {
    var u = Math.min(W, H), cols = 4, rows = 3, cw = W / cols, ch = H * 0.42 / rows, d = '', C = [t.p, t.s, t.a, t.tint, '#FFFFFF'];
    for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) {
      var c = C[(i * 2 + j * 3) % 5], x = i * cw, y = j * ch;
      d += rect(x, y, cw, ch, c);
      if ((i + j) % 3 === 0) d += circ(x + cw / 2, y + ch / 2, Math.min(cw, ch) * 0.32, c === t.a ? t.p : t.a);
      else if ((i + j) % 3 === 1) d += tri(x, y, Math.min(cw, ch), c === t.p ? t.s : t.p, 1, 1);
    }
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.42 + u * 0.08, w: W - u * 0.16, h: H * 0.58 - u * 0.16 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('nightsky', function (t, W, H) {
    var u = Math.min(W, H), d = rect(0, 0, W, H, t.p) + '<rect width="' + W + '" height="' + H + '" fill="url(#mg1)"/>';
    for (var i = 0; i < 9; i++) d += line(W * (0.1 + i * 0.1), 0, W * (0.1 + i * 0.1) - H * 0.2, H, '#FFFFFF', 1, 0.06);
    d += rect(u * 0.08, H * 0.14, u * 0.12, u * 0.012, t.a);
    return { bg: t.p, deco: d, defs: grad('mg1', t.p, D.mix(t.s, t.p, 0.3), 1, 1), box: { x: u * 0.08, y: H * 0.18, w: W - u * 0.16, h: H * 0.68 }, fill: null, align: 'left', v: 'top', dk: 1 };
  });
  M('halfdisc', function (t, W, H) {
    var u = Math.min(W, H), r = Math.min(W * 0.62, H * 0.4);
    var d = circ(W / 2, H, r, t.p) + circ(W / 2, H, r * 0.68, t.s) + circ(W / 2, H, r * 0.36, t.a);
    return { bg: t.tint, deco: d, box: { x: u * 0.09, y: H * 0.08, w: W - u * 0.18, h: H - r - H * 0.08 - u * 0.06 }, fill: t.tint, align: 'center', v: 'center' };
  });
  M('corners', function (t, W, H) {
    var u = Math.min(W, H), s = u * 0.42;
    var d = tri(0, 0, s, t.p, 1, 1) + tri(0, 0, s * 0.62, t.a, 1, 1) + tri(W - s, H - s, s, t.p) + tri(W - s * 0.6, H - s * 0.6, s * 0.6, t.s);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.12, y: s * 0.62 + u * 0.06, w: W - u * 0.24, h: H - s * 0.62 - s - u * 0.06 }, fill: '#FFFFFF', align: 'left', v: 'center' };
  });
  M('card', function (t, W, H) {
    var u = Math.min(W, H), gp = u * 0.06, d = rect(0, 0, W, H, t.p);
    for (var i = -2; i * gp < W + H; i++) d += line(i * gp, 0, i * gp - H, H, '#FFFFFF', Math.max(1, u * 0.003), 0.1);
    d += circ(W * 0.9, H * 0.1, u * 0.18, t.a, 0.95);
    var box = { x: u * 0.09, y: H * 0.22, w: W - u * 0.18, h: H * 0.6 };
    return { bg: t.p, deco: d, box: box, fill: '#FFFFFF', po: { shadow: u * 0.012, top: t.a, th: Math.max(4, u * 0.012) }, align: 'left', v: 'center' };
  });
  M('capsules', function (t, W, H) {
    var u = Math.min(W, H), cw = u * 0.15, d = '';
    var C = [t.p, t.a, t.s, t.p, t.s, t.a];
    for (var i = 0; i < 6; i++) d += '<rect x="' + (W * 0.5 + i * cw * 0.62) + '" y="' + (-u * 0.1 + (i % 3) * u * 0.07) + '" width="' + cw * 0.5 + '" height="' + (H * 0.32 + (i % 2) * u * 0.1) + '" rx="' + cw * 0.25 + '" fill="' + C[i] + '"/>';
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.46, w: W - u * 0.16, h: H * 0.46 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('bauhaus', function (t, W, H) {
    var u = Math.min(W, H), bh = H * 0.46, c = W / 3;
    var d = rect(0, 0, W, bh, t.tint) + rect(0, 0, c, bh / 2, t.p) + circ(c * 1.5, bh / 2, Math.min(c, bh) * 0.42, t.a) + tri(c * 2, 0, Math.min(c, bh / 2), t.s, 1, 1) +
      rect(c * 2, bh / 2, c, bh / 2, t.p) + '<path d="M0 ' + bh + ' A ' + (c / 2) + ' ' + (c / 2) + ' 0 0 1 ' + c + ' ' + bh + ' Z" fill="' + t.s + '"/>';
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: bh + u * 0.07, w: W - u * 0.16, h: H - bh - u * 0.14 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('sidebar', function (t, W, H) {
    var u = Math.min(W, H), land = W / H > 0.85, sw = land ? W * 0.5 : W * 0.6, d = rect(0, 0, sw, H, t.p);
    d += halftone(sw + u * 0.04, u * 0.06, W - sw - u * 0.08, H - u * 0.12, u * 0.045, u * 0.016, t.s) + rect(sw, 0, u * 0.015, H, t.a);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.1, w: sw - u * 0.14, h: H * 0.8 }, fill: null, align: 'left', v: 'center', dk: 1 };
  });
  M('ribbon', function (t, W, H) {
    var u = Math.min(W, H), y0 = H * 0.36, bh = H * 0.34;
    var d = rect(0, 0, W, H, t.tint) + rect(0, y0, W, bh, t.p) + rect(0, y0 - u * 0.02, W, u * 0.012, t.a) + rect(0, y0 + bh + u * 0.008, W, u * 0.012, t.a) +
      dots(u * 0.08, u * 0.08, 6, 3, u * 0.04, Math.max(1.4, u * 0.006), t.p, 0.4) + chev(W - u * 0.22, H - u * 0.2, u * 0.1, t.a) + chev(W - u * 0.15, H - u * 0.2, u * 0.1, t.s);
    return { bg: t.tint, deco: d, box: { x: u * 0.08, y: y0 + u * 0.03, w: W - u * 0.16, h: bh - u * 0.06 }, fill: null, align: 'center', v: 'center', dk: 1,
      box2: { x: u * 0.08, y: y0 + bh + u * 0.05, w: W - u * 0.16, h: H - y0 - bh - u * 0.3 } };
  });
  M('orbit', function (t, W, H) {
    var u = Math.min(W, H), cx = W * 0.72, cy = H * 0.24, r = u * 0.2;
    var d = ring(cx, cy, r * 1.6, t.line || t.s, Math.max(1, u * 0.004)) + ring(cx, cy, r * 1.15, t.s, Math.max(1, u * 0.004), 0.6) + circ(cx, cy, r, t.p) + circ(cx + r * 1.6, cy, u * 0.025, t.a) + circ(cx - r * 1.13, cy + r * 0.3, u * 0.018, t.s);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: cy + r * 1.6 + u * 0.05, w: W - u * 0.16, h: H - (cy + r * 1.6 + u * 0.05) - u * 0.08 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('blocks', function (t, W, H) {
    var u = Math.min(W, H), d = rect(0, 0, W * 0.62, H * 0.3, t.p) + rect(W * 0.62, 0, W * 0.38, H * 0.18, t.a) + rect(W * 0.62, H * 0.18, W * 0.38, H * 0.12, t.s) +
      rect(0, H * 0.3, W * 0.2, H * 0.08, t.s) + rect(W * 0.8, H - u * 0.14, W * 0.2, u * 0.14, t.a);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.38 + u * 0.06, w: W - u * 0.16, h: H - H * 0.38 - u * 0.24 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('gradient', function (t, W, H) {
    var u = Math.min(W, H), d = '<rect width="' + W + '" height="' + H + '" fill="url(#mg2)"/>' + blob(W * 0.86, H * 0.12, u * 0.3, 3, '#FFFFFF', 0.1) + blob(W * 0.1, H * 0.92, u * 0.26, 7, '#FFFFFF', 0.08);
    return { bg: t.p, deco: d, defs: grad('mg2', t.p, t.s, 1, 1), box: { x: u * 0.09, y: H * 0.26, w: W - u * 0.18, h: H * 0.5 }, fill: null, align: 'center', v: 'center', dk: 1 };
  });
  M('stack', function (t, W, H) {
    var u = Math.min(W, H), d = '';
    for (var i = 0; i < 4; i++) d += rect(W * 0.5 + i * u * 0.05, u * 0.06 + i * u * 0.05, W * 0.45 - i * u * 0.05, H * 0.3, [t.tint, t.s, t.a, t.p][i], 1, u * 0.02);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: H * 0.3 + u * 0.24, w: W - u * 0.16, h: H - H * 0.3 - u * 0.32 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  M('lines', function (t, W, H) {
    var u = Math.min(W, H), d = '';
    for (var i = 0; i < 7; i++) d += zigzag(W * 0.5, u * 0.1 + i * u * 0.045, W * 0.42, u * 0.02, 12, i % 3 === 0 ? t.a : t.p, Math.max(1.5, u * 0.005));
    d += rect(0, H - u * 0.05, W, u * 0.05, t.p);
    return { bg: '#FFFFFF', deco: d, box: { x: u * 0.08, y: u * 0.1 + 7 * u * 0.045 + u * 0.08, w: W - u * 0.16, h: H - (u * 0.1 + 7 * u * 0.045 + u * 0.08) - u * 0.12 }, fill: '#FFFFFF', align: 'left', v: 'top' };
  });
  var MOTIFS = Object.keys(MO);

  /* draw one motif into a panel; returns { svg (inner), zones } */
  function drawMotif(name, t, x, y, W, H, o) {
    o = o || {};
    var m = MO[name](t, W, H), s = '', u = Math.min(W, H);
    s += rect(0, 0, W, H, m.bg || '#FFFFFF') + (m.deco || '');
    var zones = [];
    [[m.box, m.fill, m.dk], [m.box2, t.tint, 0]].forEach(function (q, i) {
      var b = q[0]; if (!b || (i === 1 && !o.two)) return;
      var fill = q[1] != null ? q[1] : null;
      if (fill) s += plate(b, fill, m.po);
      var isDark = fill ? dark(fill) : !!q[2], pad = Math.max(6, u * (o.pad || 0.045)) + (m.po && m.po.top ? (m.po.th || 6) : 0) * 0.5;
      zones.push({ x: x + b.x + pad, y: y + b.y + pad, w: b.w - 2 * pad, h: b.h - 2 * pad, align: m.align, v: i ? 'top' : m.v, dark: isDark });
    });
    return { svg: g(x, y, '<svg x="0" y="0" width="' + W + '" height="' + H + '" overflow="hidden">' + (m.defs ? '<defs>' + m.defs + '</defs>' : '') + s + '</svg>'), zones: zones };
  }
  /* calm second page / inner panel: a header strip that echoes the motif colours and a clean area */
  var BACKS = ['strip', 'side', 'corner', 'frame'];
  function drawBack(kind, t, x, y, W, H, o) {
    o = o || {}; var u = Math.min(W, H), s = rect(0, 0, W, H, o.tint ? t.tint : '#FFFFFF'), box;
    if (kind === 'strip') { s += rect(0, 0, W, u * 0.05, t.p) + rect(0, u * 0.05, W * 0.3, u * 0.012, t.a) + rect(0, H - u * 0.03, W, u * 0.03, t.p); box = { x: u * 0.08, y: u * 0.11, w: W - u * 0.16, h: H - u * 0.19 }; }
    else if (kind === 'side') { s += rect(0, 0, u * 0.04, H, t.p) + rect(u * 0.04, 0, u * 0.012, H * 0.3, t.a); box = { x: u * 0.11, y: u * 0.08, w: W - u * 0.18, h: H - u * 0.16 }; }
    else if (kind === 'corner') { s += tri(W - u * 0.22, 0, u * 0.22, t.p, 1, 0).replace(/points="[^"]*"/, 'points="' + (W - u * 0.22) + ',0 ' + W + ',0 ' + W + ',' + (u * 0.22) + '"') + circ(u * 0.04, H - u * 0.04, u * 0.12, t.a, 0.9); box = { x: u * 0.08, y: u * 0.08, w: W - u * 0.3, h: H - u * 0.24 }; }
    else { s += '<rect x="' + u * 0.035 + '" y="' + u * 0.035 + '" width="' + (W - u * 0.07) + '" height="' + (H - u * 0.07) + '" fill="none" stroke="' + t.p + '" stroke-width="' + Math.max(1, u * 0.004) + '"/>' + rect(W / 2 - u * 0.06, u * 0.035 - 2, u * 0.12, 4, t.a); box = { x: u * 0.09, y: u * 0.09, w: W - u * 0.18, h: H - u * 0.18 }; }
    var pad = Math.max(4, u * 0.02);
    return { svg: g(x, y, '<svg x="0" y="0" width="' + W + '" height="' + H + '" overflow="hidden">' + s + '</svg>'), zones: [{ x: x + box.x + pad, y: y + box.y + pad, w: box.w - 2 * pad, h: box.h - 2 * pad, align: o.align || 'left', v: o.v || 'top', dark: false }] };
  }
  /* a full-colour back side (business card back, flyer back variant) */
  function drawSolid(t, x, y, W, H, o) {
    o = o || {}; var u = Math.min(W, H), c = o.c || t.p, s = rect(0, 0, W, H, c);
    if (o.pat === 'dots') s += dots(W - u * 0.3, u * 0.08, 6, 4, u * 0.045, Math.max(1, u * 0.008), '#FFFFFF', 0.25);
    if (o.pat === 'bar') s += rect(0, H - u * 0.06, W, u * 0.06, t.a);
    if (o.pat === 'ring') s += ring(W, 0, u * 0.5, t.a, Math.max(2, u * 0.02), 0.9);
    if (o.pat === 'stripe') s += stripes(W * 0.7, 0, W * 0.3, H, 5, '#FFFFFF', 0.1);
    var bx = { x: u * 0.1, y: u * 0.1, w: W * (o.pat === 'ring' || o.pat === 'stripe' ? 0.62 : 1) - u * 0.2 * (o.pat === 'ring' || o.pat === 'stripe' ? 0.5 : 1), h: H - u * 0.2 - (o.pat === 'bar' ? u * 0.06 : 0) };
    return { svg: g(x, y, '<svg x="0" y="0" width="' + W + '" height="' + H + '" overflow="hidden">' + s + '</svg>'), zones: [{ x: x + bx.x, y: y + bx.y, w: bx.w, h: bx.h, align: o.align || 'left', v: o.v || 'center', dark: dark(c) }] };
  }

  /* text size for a zone: k scales the small text, titlePt the big title */
  function kOf(sz, zw) {
    var key = sz.key || 'A4';
    if (key === 'BizCard' || key === 'BizCardEU') return 0.6;
    if (/^Social|^Story/.test(key)) return 1.75;
    return Math.max(0.62, Math.min(1.5, Math.sqrt(sz.w * sz.h) / 944));
  }
  function finish(zs, sz, tp) {
    zs.forEach(function (z) { z.k = z.k || kOf(sz, z.w); z.titlePt = Math.round(Math.max(10, Math.min(tp || 999, z.w * (z.tf || 0.1)))); });
    return zs;
  }

  /* ---------- layouts per document type ---------- */
  D.PANEL = {};
  /* flyer: front + back (2-sided) */
  D.PANEL.flyer2 = function (doc, t, W, H, dz, sz) {
    var f = full(drawMotif(dz.motif, t, 0, 0, W, H), W, H), b = full(dz.back === 'solid' ? drawSolid(t, 0, 0, W, H, { pat: dz.pat || 'dots', v: 'top' }) : drawBack(dz.back || 'strip', t, 0, 0, W, H, { tint: dz.tintBack }), W, H);
    b.zones.forEach(function (z) { z.k = kOf(sz) * 1.3; z.tf = 0.07; }); finish(b.zones, sz, 40);
    f.zones.forEach(function (z) { z.tf = 0.1; z.k = kOf(sz) * 1.12; });
    return { pages: [{ svg: svg(W, H, f.svg), zones: finish(f.zones, sz) }, { svg: svg(W, H, b.svg), zones: b.zones }] };
  };
  /* one page: poster, social post, invitation, menu, certificate, tickets */
  D.PANEL.one = function (doc, t, W, H, dz, sz) {
    var f = full(dz.back && dz.plain ? drawBack(dz.back, t, 0, 0, W, H, { align: dz.align || 'center', v: dz.v || 'top', tint: dz.tintBack }) : drawMotif(dz.motif, t, 0, 0, W, H, { two: dz.two }), W, H);
    if (dz.align) f.zones.forEach(function (z) { z.align = dz.align; });
    if (dz.v) f.zones.forEach(function (z) { z.v = dz.v; });
    f.zones.forEach(function (z) { z.tf = dz.tf || 0.1; z.k = kOf(sz) * (dz.kx || 1); });
    return { pages: [{ svg: svg(W, H, f.svg), zones: finish(f.zones, sz, dz.maxT) }] };
  };
  /* business card: front (motif) + back (solid colour or calm) */
  D.PANEL.bizcard = function (doc, t, W, H, dz, sz) {
    var f = full(drawMotif(dz.motif, t, 0, 0, W, H, { pad: 0.06 }), W, H), b = full(drawSolid(t, 0, 0, W, H, { c: dz.backc === 'a' ? t.s : dz.backc === 'w' ? '#FFFFFF' : t.p, pat: dz.pat, align: dz.balign || 'left' }), W, H);
    f.zones.forEach(function (z) { z.tf = 0.13; });
    b.zones.forEach(function (z) { z.tf = 0.1; });
    return { pages: [{ svg: svg(W, H, f.svg), zones: finish(f.zones, sz) }, { svg: svg(W, H, b.svg), zones: finish(b.zones, sz) }] };
  };
  /* tri-fold brochure on a landscape page: outside (flap | back | front cover) + inside (3 panels) */
  D.PANEL.trifold = function (doc, t, W, H, dz, sz) {
    var pw = W / 3, out = '', inn = '', zo = [], zi = [];
    var flap = drawBack(BACKS[(dz.vi || 0) % 4], t, 0, 0, pw, H, { tint: true }), back = drawSolid(t, pw, 0, pw, H, { pat: dz.pat || 'bar', align: 'center', v: 'bottom' }), front = drawMotif(dz.motif, t, 2 * pw, 0, pw, H);
    out = flap.svg + back.svg + front.svg; zo = flap.zones.concat(back.zones, front.zones);
    for (var i = 0; i < 3; i++) { var p = drawBack(BACKS[(i + (dz.vi || 0)) % 4], t, i * pw, 0, pw, H, { tint: i === 1 }); inn += p.svg; zi = zi.concat(p.zones); }
    inn += line(pw, H * 0.06, pw, H * 0.94, t.line, 1) + line(2 * pw, H * 0.06, 2 * pw, H * 0.94, t.line, 1);
    zo.forEach(function (z) { z.tf = 0.11; }); zi.forEach(function (z) { z.tf = 0.09; });
    var k = 0.95;
    zo.concat(zi).forEach(function (z) { z.k = k; });
    return { pages: [{ svg: svg(W, H, out), zones: finish(zo, sz, 40) }, { svg: svg(W, H, inn), zones: finish(zi, sz, 22) }] };
  };
  /* folded card on a landscape page: outside (back | front) + inside (left | right) */
  D.PANEL.fold = function (doc, t, W, H, dz, sz) {
    var pw = W / 2, back = drawBack(BACKS[(dz.vi || 0) % 4], t, 0, 0, pw, H, { align: 'center', v: 'bottom' }), front = drawMotif(dz.motif, t, pw, 0, pw, H);
    var il = drawBack('corner', t, 0, 0, pw, H, { tint: true, align: 'center', v: 'center' }), ir = drawBack(BACKS[((dz.vi || 0) + 1) % 4], t, pw, 0, pw, H, { align: 'center', v: 'center' });
    var zo = back.zones.concat(front.zones), zi = il.zones.concat(ir.zones);
    zo.concat(zi).forEach(function (z) { z.k = 0.95; z.tf = 0.12; });
    if (dz.align) front.zones.forEach(function (z) { z.align = dz.align; });
    return { pages: [{ svg: svg(W, H, back.svg + front.svg + line(pw, 0, pw, H, '#000000', 0.5, 0.08)), zones: finish(zo, sz, 60) }, { svg: svg(W, H, il.svg + ir.svg + line(pw, 0, pw, H, '#000000', 0.5, 0.08)), zones: finish(zi, sz, 40) }] };
  };
  /* infographic: a headline band and a long content area */
  D.PANEL.info = function (doc, t, W, H, dz, sz) {
    var u = Math.min(W, H), hh = H * (dz.hh || 0.24), top = drawMotif(dz.motif, t, 0, 0, W, hh, { pad: 0.07 }), s = rect(0, 0, W, H, t.tint) + top.svg;
    var bx = { x: u * 0.06, y: hh + u * 0.04, w: W - u * 0.12, h: H - hh - u * 0.1 };
    s += plate(bx, '#FFFFFF', { top: t.a, th: 5 }) + rect(0, H - u * 0.03, W, u * 0.03, t.p);
    var zs = top.zones.map(function (z) { z.tf = 0.075; return z; });
    zs.push({ x: bx.x + u * 0.05, y: bx.y + u * 0.05, w: bx.w - u * 0.1, h: bx.h - u * 0.08, align: 'left', v: 'top', dark: false, tf: 0.06 });
    zs.forEach(function (z) { z.k = kOf(sz) * (dz.kx || 1); });
    return { pages: [{ svg: svg(W, H, s), zones: finish(zs, sz, 46) }] };
  };

  /* ---------- palettes and fonts ---------- */
  function mp(id, name, p, s, a, head, body) {
    var ink = D.mix(p, '#15171C', 0.78), mute = D.mix(ink, '#FFFFFF', 0.4);
    var x = th(id, name, p, s, a, ink, mute, D.mix(p, '#FFFFFF', 0.93), D.mix(p, '#FFFFFF', 0.82), head, body); x.hide = 1; return x;
  }
  var MP = [
    mp('m01', 'Electric Blue', '#0A2A66', '#1F5BD8', '#FFB400', 'Aptos Display', 'Aptos'), mp('m02', 'Coral Night', '#1B1F3B', '#3D4C7A', '#FF6B5B', 'Bahnschrift', 'Aptos'),
    mp('m03', 'Fresh Mint', '#0B4F4A', '#13867B', '#FFD166', 'Century Gothic', 'Calibri'), mp('m04', 'Berry', '#4A0E4E', '#81267F', '#FF9F1C', 'Franklin Gothic Medium', 'Calibri'),
    mp('m05', 'Citrus', '#173F35', '#2F7D5D', '#F4D03F', 'Segoe UI', 'Segoe UI'), mp('m06', 'Signal Red', '#1D1D1F', '#3A3A3C', '#E63946', 'Arial Black', 'Arial'),
    mp('m07', 'Ocean Pop', '#023E8A', '#0077B6', '#90E0EF', 'Aptos Display', 'Aptos'), mp('m08', 'Sunset Pink', '#6A1B4D', '#B33771', '#FDA65D', 'Trebuchet MS', 'Calibri'),
    mp('m09', 'Royal Gold', '#1C1446', '#3B2C85', '#E9B949', 'Georgia', 'Calibri'), mp('m10', 'Forest Lime', '#14301F', '#2D6A4F', '#B7E04A', 'Gill Sans MT', 'Gill Sans MT'),
    mp('m11', 'Terracotta', '#5B2A1E', '#A0522D', '#F2C14E', 'Rockwell', 'Calibri'), mp('m12', 'Ink Cyan', '#0F172A', '#1E3A5F', '#22D3EE', 'Segoe UI', 'Segoe UI'),
    mp('m13', 'Plum Rose', '#3F1D38', '#6B2D5C', '#F28482', 'Constantia', 'Corbel'), mp('m14', 'Charcoal Lime', '#202426', '#3F4549', '#C6F432', 'Bahnschrift', 'Calibri'),
    mp('m15', 'Navy Peach', '#14213D', '#2C4570', '#FCA17D', 'Tw Cen MT', 'Calibri'), mp('m16', 'Teal Sun', '#005F73', '#0A9396', '#EE9B00', 'Calibri Light', 'Calibri'),
    mp('m17', 'Wine & Blush', '#5C0F2A', '#8E2C48', '#F6BD9C', 'Garamond', 'Gill Sans MT'), mp('m18', 'Graphite Orange', '#212529', '#495057', '#FD7E14', 'Franklin Gothic Medium', 'Aptos'),
    mp('m19', 'Indigo Mint', '#2B2D7C', '#4F46E5', '#5EEAD4', 'Aptos Display', 'Aptos'), mp('m20', 'Olive Cream', '#3A4422', '#5F6F35', '#E9C46A', 'Book Antiqua', 'Gill Sans MT'),
    mp('m21', 'Black & Gold', '#111111', '#2B2B2B', '#D4A72C', 'Bodoni MT', 'Gill Sans MT'), mp('m22', 'Sky Coral', '#0E4C75', '#1C7DB8', '#FF7A59', 'Trebuchet MS', 'Calibri'),
    mp('m23', 'Emerald Gold', '#064E3B', '#047857', '#FBBF24', 'Cambria', 'Calibri'), mp('m24', 'Midnight Violet', '#170F33', '#3B1F7A', '#F472B6', 'Century Gothic', 'Century Gothic')
  ];
  MP.forEach(function (x) { D.THEMES.push(x); });

  var NAMES = ['Aurora', 'Beacon', 'Bloom', 'Bolt', 'Cadence', 'Canvas', 'Catalyst', 'Chroma', 'Civic', 'Comet', 'Crest', 'Dash', 'Delta', 'Echo', 'Edge', 'Ember', 'Flare', 'Flux', 'Forge', 'Fusion',
    'Glide', 'Halo', 'Harbor', 'Horizon', 'Icon', 'Ignite', 'Jet', 'Keynote', 'Kinetic', 'Lumen', 'Marquee', 'Meridian', 'Metro', 'Mosaic', 'Nova', 'Onyx', 'Orbit', 'Pulse', 'Prism', 'Quartz',
    'Radiant', 'Rally', 'Relay', 'Ripple', 'Sable', 'Signal', 'Solstice', 'Spark', 'Spectrum', 'Sprint', 'Summit', 'Swift', 'Tempo', 'Tidal', 'Trek', 'Unity', 'Vertex', 'Vista', 'Vivid', 'Volt',
    'Wave', 'Zenith', 'Zest', 'Apex', 'Arcade', 'Atlas', 'Banner', 'Blaze', 'Breeze', 'Cascade', 'Citadel', 'Coral', 'Drift', 'Ellipse', 'Fable', 'Gala', 'Grove', 'Haven', 'Iris', 'Jubilee'];
  var MDESC = { split: 'Colour panel and circle', topband: 'Bold colour header', frame: 'Framed, centred', diagonal: 'Diagonal cut', arcs: 'Corner arcs', pillars: 'Rounded pillars', dotgrid: 'Dot grid and square',
    sun: 'Rising sun', waves: 'Layered waves', mosaic: 'Geometric mosaic', nightsky: 'Deep colour, fine lines', halfdisc: 'Half-circle target', corners: 'Corner triangles', card: 'Card on pattern',
    capsules: 'Capsule shapes', bauhaus: 'Bauhaus shapes', sidebar: 'Colour side and halftone', ribbon: 'Colour ribbon', orbit: 'Orbit rings', blocks: 'Colour blocks', gradient: 'Soft gradient', stack: 'Stacked cards', lines: 'Zigzag lines' };
  var ni = 0;
  function nm() { return NAMES[ni++ % NAMES.length]; }
  function add(code, type, n, layout, extra, coll, pOff, skip) {
    var ML = MOTIFS.filter(function (m) { return !skip || skip.indexOf(m) < 0; });
    for (var i = 0; i < n; i++) {
      var gcd = function (a, b) { return b ? gcd(b, a % b) : a; }, st = [7, 5, 11, 13, 3].filter(function (x) { return gcd(x, ML.length) === 1; })[0] || 1;
      var mo = ML[(i * st + (pOff || 0)) % ML.length], p = MP[(i * 5 + (pOff || 0) * 3) % MP.length];
      var o = { layout: layout, motif: mo, back: BACKS[i % 4], pat: ['dots', 'bar', 'ring', 'stripe'][(i + (pOff || 0)) % 4], vi: i, coll: coll, desc: (MDESC[mo] || 'Design') + ' · ' + p.name };
      var x = typeof extra === 'function' ? extra(i, mo) : extra; for (var k in x || {}) o[k] = x[k];
      D.DESIGNS.push(des(code + '-' + type.replace(/_/g, '') + '-' + (i + 1), nm(), 'panels', [type], p.id, o));
    }
  }
  /* new document types */
  var TY = [
    { id: 'brochure', name: 'Tri-fold brochure', group: 'Marketing & Advertising', ai: 1, pages: 2, fixed: 1 },
    { id: 'poster', name: 'Poster', group: 'Marketing & Advertising', ai: 1, pages: 1, fixed: 1 },
    { id: 'social_post', name: 'Social media post', group: 'Marketing & Advertising', ai: 1, pages: 1, fixed: 1 },
    { id: 'infographic', name: 'Infographic', group: 'Marketing & Advertising', ai: 1, pages: 1, fixed: 1 },
    { id: 'business_card', name: 'Business card', group: 'Marketing & Advertising', ai: 1, pages: 2, fixed: 1 }
  ];
  var at = 0; D.TYPES.forEach(function (x, i) { if (x.id === 'flyer') at = i + 1; });
  D.TYPES.splice.apply(D.TYPES, [at, 0].concat(TY));
  D.type('flyer').name = 'Flyer (2-sided)';
  D.type('card').name = 'Greeting / thank-you card';

  add('ma', 'flyer', 18, 'flyer2', function (i) { return { back: i % 3 === 2 ? 'solid' : BACKS[i % 4], tintBack: i % 2 === 1 }; }, 'marketing', 0, ['topband', 'ribbon']);
  add('ma', 'poster', 18, 'one', function (i) { return { two: true, tf: 0.13, kx: 1.45 }; }, 'marketing', 3);
  add('ma', 'social_post', 18, 'one', function (i) { return { tf: 0.13, kx: 1.4 }; }, 'marketing', 5, ['topband', 'ribbon']);
  add('ma', 'brochure', 16, 'trifold', { orient: 'landscape' }, 'marketing', 2, ['topband', 'ribbon']);
  add('ma', 'infographic', 16, 'info', null, 'marketing', 9);
  add('ma', 'business_card', 18, 'bizcard', function (i) { return { backc: ['p', 'a', 'w'][i % 3], balign: i % 4 === 3 ? 'center' : 'left' }; }, 'marketing', 11, ['topband', 'ribbon']);
  add('ev', 'invitation', 16, 'one', function (i) { return { align: 'center', v: 'center', tf: 0.1, maxT: 60, kx: 1.3 }; }, 'events', 4, ['topband', 'ribbon']);
  add('ev', 'menu', 16, 'info', function (i) { return { kx: 1.4, hh: 0.2 }; }, 'events', 6, ['topband', 'ribbon']);
  add('ev', 'certificate', 15, 'one', function (i) { return { plain: 1, back: i % 2 ? 'frame' : 'strip', orient: 'landscape', align: 'center', v: 'center', maxT: 40, tintBack: i % 3 === 0 }; }, 'events', 8);
  add('ev', 'tickets', 15, 'one', function (i) { return { plain: 1, back: BACKS[i % 4], v: 'top', maxT: 20 }; }, 'events', 10);
  add('ev', 'card', 16, 'fold', { orient: 'landscape' }, 'events', 1, ['topband', 'ribbon']);

  /* ---------- sample content (pg = page, zn = zone on that page) ---------- */
  function SB(t, o, pg, zn) { var b = D.B(t, o); if (pg != null) b.pg = pg; if (zn != null) b.zn = zn; return b; }
  var sample0 = D.sample;
  D.sample = function (type) {
    var dz0 = null;
    var doc = sample0(type), b;
    switch (type) {
      case 'flyer':
        doc.title = 'Flyer'; b = doc.blocks = [];
        b.push(SB('s_kicker', { text: 'Saturday · 14 June' }, 0), SB('s_title', { text: 'Summer Market' }, 0), SB('s_sub', { text: 'Local food, music and makers' }, 0),
          SB('s_text', { text: 'Join us for a day of fresh food, live music and more than 40 local makers. Free entry for everyone.' }, 0), SB('s_cta', { text: 'yourwebsite.com' }, 0),
          SB('h2', { text: 'What’s on' }, 1), SB('ul', { items: ['Street food from 20 local kitchens', 'Live music on two stages', 'Workshops for children', 'Craft and design stalls'] }, 1),
          SB('h2', { text: 'Plan your visit' }, 1), SB('s_details', { rows: [['When', '10:00 – 18:00'], ['Where', 'Central Park, Main Gate'], ['Tickets', 'Free']] }, 1),
          SB('s_text', { text: 'Bring a reusable bag and come early for the best picks.' }, 1), SB('s_cta', { text: 'yourwebsite.com' }, 1));
        break;
      case 'poster':
        doc.title = 'Poster'; b = doc.blocks = [];
        b.push(SB('s_kicker', { text: 'Live on stage · 21 June' }, 0, 0), SB('s_title', { text: 'Jazz in the Park' }, 0, 0), SB('s_sub', { text: 'An evening of music under the stars' }, 0, 0),
          SB('s_details', { rows: [['Doors', '6:30 pm'], ['Where', 'City Park Bandstand'], ['Tickets', '[Price]']] }, 0, 1), SB('s_cta', { text: 'yourwebsite.com' }, 0, 1));
        break;
      case 'social_post':
        doc.title = 'Social media post'; b = doc.blocks = [];
        b.push(SB('s_kicker', { text: 'New this week' }, 0), SB('s_title', { text: 'Fresh ideas, made simple' }, 0), SB('s_sub', { text: 'Our new collection is here' }, 0), SB('s_cta', { text: 'Shop now' }, 0));
        break;
      case 'brochure':
        doc.title = 'Brochure'; b = doc.blocks = [];
        b.push(SB('h2', { text: 'Who we are' }, 0, 0), SB('s_text', { text: 'A small team with big experience, helping local businesses grow with clear plans and friendly support.' }, 0, 0),
          SB('ul', { items: ['Founded in [Year]', '[Number] happy clients', 'Based in [City]'] }, 0, 0),
          SB('s_title', { text: 'Get in touch' }, 0, 1), SB('s_details', { rows: [['Phone', '[Phone]'], ['Email', '[Email]'], ['Web', 'yourwebsite.com']] }, 0, 1),
          SB('s_kicker', { text: 'Your Company' }, 0, 2), SB('s_title', { text: 'Grow your business with confidence' }, 0, 2), SB('s_sub', { text: 'Services · Plans · Support' }, 0, 2),
          SB('h2', { text: 'What we do' }, 1, 0), SB('s_text', { text: 'We plan, design and run the things that help you win customers and keep them.' }, 1, 0), SB('ul', { items: ['Strategy and planning', 'Design and branding', 'Websites and online shops'] }, 1, 0),
          SB('h2', { text: 'How it works' }, 1, 1), SB('ol', { items: ['A free first call', 'A clear plan and price', 'We deliver, you grow'] }, 1, 1),
          SB('s_text', { text: 'Most projects start within two weeks.' }, 1, 1),
          SB('h2', { text: 'Why choose us' }, 1, 2), SB('ul', { items: ['Fixed prices, no surprises', 'One contact from start to finish', 'Results you can measure'] }, 1, 2), SB('s_cta', { text: 'Book a call' }, 1, 2));
        break;
      case 'infographic':
        doc.title = 'Infographic'; b = doc.blocks = [];
        b.push(SB('s_kicker', { text: 'At a glance' }, 0, 0), SB('s_title', { text: 'How we spend a working week' }, 0, 0), SB('s_sub', { text: 'Survey of [Number] office teams' }, 0, 0),
          SB('stats', { items: [{ v: '[X]%', l: 'Time in meetings' }, { v: '[X]%', l: 'Focused work' }, { v: '[X]%', l: 'Email and chat' }] }, 0, 1),
          SB('chart', { kind: 'bar', title: 'Focus hours per day', labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], series: [{ name: 'Focus', values: [4, 5, 3, 5, 4] }], vals: true }, 0, 1),
          SB('h2', { text: 'Three things that help' }, 0, 1), SB('ol', { items: ['Block two hours a day for focus', 'Keep meetings to 25 minutes', 'Check email at set times'] }, 0, 1),
          SB('s_text', { text: 'Source: [Your source]' }, 0, 1));
        break;
      case 'business_card':
        doc.title = 'Business card'; b = doc.blocks = [];
        b.push(SB('s_title', { text: 'Your Name' }, 0), SB('s_sub', { text: 'Job Title' }, 0), SB('s_kicker', { text: 'Your Company' }, 1),
          SB('s_details', { rows: [['Phone', '[Phone]'], ['Email', '[Email]'], ['Web', 'yourwebsite.com']] }, 1));
        break;
      case 'menu':
        doc.blocks.forEach(function (x) { x.pg = 0; x.zn = /^s_(kicker|title)$/.test(x.t) ? 0 : 1; });
        break;
      case 'card':
        if (true) {
          doc.title = 'Card'; b = doc.blocks = [];
          b.push(SB('s_text', { text: 'Made with care' }, 0, 0), SB('s_title', { text: 'Thank you' }, 0, 1), SB('s_sub', { text: 'for everything' }, 0, 1),
            SB('s_sub', { text: '“Gratitude turns what we have into enough.”' }, 1, 0),
            SB('s_text', { text: 'Dear [Name],\nThank you so much for your kindness and support. It meant more than words can say.' }, 1, 1), SB('s_sub', { text: 'With love, Your Name' }, 1, 1));
        }
        break;
    }
    return doc;
  };
})();

/* ---------- QR code (byte mode, error correction M, versions 1-10) ---------- */
(function () {
  var ECC = [-1, 10, 16, 26, 18, 24, 16, 18, 22, 22, 26], NB = [-1, 1, 1, 1, 2, 2, 4, 4, 4, 5, 5];
  function rawModules(v) { var r = (16 * v + 128) * v + 64; if (v >= 2) { var n = Math.floor(v / 7) + 2; r -= (25 * n - 10) * n - 55; if (v >= 7) r -= 36; } return r; }
  function dataCw(v) { return Math.floor(rawModules(v) / 8) - ECC[v] * NB[v]; }
  function mul(x, y) { var z = 0; for (var i = 7; i >= 0; i--) { z = (z << 1) ^ ((z >>> 7) * 0x11D); z ^= ((y >>> i) & 1) * x; } return z & 255; }
  function rsGen(n) { var r = []; for (var i = 0; i < n - 1; i++) r.push(0); r.push(1); var root = 1; for (i = 0; i < n; i++) { for (var j = 0; j < n; j++) { r[j] = mul(r[j], root); if (j + 1 < n) r[j] ^= r[j + 1]; } root = mul(root, 2); } return r; }
  function rsRem(data, gen) { var r = gen.map(function () { return 0; }); data.forEach(function (b) { var f = b ^ r.shift(); r.push(0); for (var i = 0; i < gen.length; i++) r[i] ^= mul(gen[i], f); }); return r; }
  function utf8(s) { var out = [], e = unescape(encodeURIComponent(s)); for (var i = 0; i < e.length; i++) out.push(e.charCodeAt(i)); return out; }
  function bit(x, i) { return ((x >>> i) & 1) !== 0; }
  D.qr = function (text) {
    var bytes = utf8(String(text || ' ')), v;
    for (v = 1; v <= 10; v++) { var cap = dataCw(v) * 8, need = 4 + (v < 10 ? 8 : 16) + bytes.length * 8; if (need <= cap) break; }
    if (v > 10) { v = 10; bytes = bytes.slice(0, dataCw(10) - 3); }
    var bb = [], push = function (val, len) { for (var i = len - 1; i >= 0; i--) bb.push((val >>> i) & 1); };
    push(4, 4); push(bytes.length, v < 10 ? 8 : 16); bytes.forEach(function (b) { push(b, 8); });
    var capB = dataCw(v) * 8; push(0, Math.min(4, capB - bb.length)); push(0, (8 - bb.length % 8) % 8);
    for (var pad = 0xEC; bb.length < capB; pad ^= 0xEC ^ 0x11) push(pad, 8);
    var dcw = []; for (var i = 0; i < bb.length; i += 8) { var x = 0; for (var k = 0; k < 8; k++) x = (x << 1) | bb[i + k]; dcw.push(x); }
    /* blocks + error correction, interleaved */
    var nb = NB[v], ec = ECC[v], raw = Math.floor(rawModules(v) / 8), nShort = nb - raw % nb, shortLen = Math.floor(raw / nb), gen = rsGen(ec), blocks = [], pos = 0;
    for (i = 0; i < nb; i++) { var len = shortLen - ec + (i < nShort ? 0 : 1), dat = dcw.slice(pos, pos + len); pos += len; var e2 = rsRem(dat, gen); if (i < nShort) dat.push(0); blocks.push(dat.concat(e2)); }
    var cw = []; for (i = 0; i < blocks[0].length; i++) blocks.forEach(function (b, j) { if (i !== shortLen - ec || j >= nShort) cw.push(b[i]); });
    var size = v * 4 + 17, M = [], F = [];
    for (i = 0; i < size; i++) { M.push(new Array(size).fill(false)); F.push(new Array(size).fill(false)); }
    function set(x, y, d) { M[y][x] = d; F[y][x] = true; }
    for (i = 0; i < size; i++) { set(6, i, i % 2 === 0); set(i, 6, i % 2 === 0); }
    [[3, 3], [size - 4, 3], [3, size - 4]].forEach(function (c) { for (var dy = -4; dy <= 4; dy++) for (var dx = -4; dx <= 4; dx++) { var xx = c[0] + dx, yy = c[1] + dy, dist = Math.max(Math.abs(dx), Math.abs(dy)); if (xx >= 0 && xx < size && yy >= 0 && yy < size) set(xx, yy, dist !== 2 && dist !== 4); } });
    if (v > 1) {
      var na = Math.floor(v / 7) + 2, step = Math.ceil((v * 4 + 4) / (na * 2 - 2)) * 2, al = [6];
      for (var p = size - 7; al.length < na; p -= step) al.splice(1, 0, p);
      for (i = 0; i < na; i++) for (var j = 0; j < na; j++) { if ((i === 0 && j === 0) || (i === 0 && j === na - 1) || (i === na - 1 && j === 0)) continue; for (var ay = -2; ay <= 2; ay++) for (var ax = -2; ax <= 2; ax++) set(al[i] + ax, al[j] + ay, Math.max(Math.abs(ax), Math.abs(ay)) !== 1); }
    }
    function format(mask) {
      var data = (0 << 3) | mask, rem = data; for (var q = 0; q < 10; q++) rem = (rem << 1) ^ ((rem >>> 9) * 0x537);
      var bits = ((data << 10) | rem) ^ 0x5412;
      for (q = 0; q <= 5; q++) set(8, q, bit(bits, q)); set(8, 7, bit(bits, 6)); set(8, 8, bit(bits, 7)); set(7, 8, bit(bits, 8));
      for (q = 9; q < 15; q++) set(14 - q, 8, bit(bits, q));
      for (q = 0; q < 8; q++) set(size - 1 - q, 8, bit(bits, q));
      for (q = 8; q < 15; q++) set(8, size - 15 + q, bit(bits, q));
      set(8, size - 8, true);
    }
    format(0);
    if (v >= 7) { var rem = v; for (i = 0; i < 12; i++) rem = (rem << 1) ^ ((rem >>> 11) * 0x1F25); var vb = (v << 12) | rem; for (i = 0; i < 18; i++) { var b2 = bit(vb, i), a2 = size - 11 + i % 3, c2 = Math.floor(i / 3); set(a2, c2, b2); set(c2, a2, b2); } }
    /* data */
    var bi = 0;
    for (var right = size - 1; right >= 1; right -= 2) {
      if (right === 6) right = 5;
      for (var vert = 0; vert < size; vert++) for (j = 0; j < 2; j++) {
        var xx = right - j, up = ((right + 1) & 2) === 0, yy = up ? size - 1 - vert : vert;
        if (!F[yy][xx] && bi < cw.length * 8) { M[yy][xx] = bit(cw[bi >>> 3], 7 - (bi & 7)); bi++; }
      }
    }
    var masks = [function (x, y) { return (x + y) % 2 === 0; }, function (x, y) { return y % 2 === 0; }, function (x) { return x % 3 === 0; }, function (x, y) { return (x + y) % 3 === 0; },
      function (x, y) { return (Math.floor(x / 3) + Math.floor(y / 2)) % 2 === 0; }, function (x, y) { return x * y % 2 + x * y % 3 === 0; }, function (x, y) { return (x * y % 2 + x * y % 3) % 2 === 0; }, function (x, y) { return ((x + y) % 2 + x * y % 3) % 2 === 0; }];
    function apply(m) { for (var y = 0; y < size; y++) for (var x = 0; x < size; x++) if (!F[y][x] && masks[m](x, y)) M[y][x] = !M[y][x]; }
    function penalty() {
      var pn = 0, dark = 0, x, y, run, c;
      for (y = 0; y < size; y++) { run = 1; for (x = 1; x < size; x++) { if (M[y][x] === M[y][x - 1]) { run++; if (run === 5) pn += 3; else if (run > 5) pn++; } else run = 1; } }
      for (x = 0; x < size; x++) { run = 1; for (y = 1; y < size; y++) { if (M[y][x] === M[y - 1][x]) { run++; if (run === 5) pn += 3; else if (run > 5) pn++; } else run = 1; } }
      for (y = 0; y < size - 1; y++) for (x = 0; x < size - 1; x++) { c = M[y][x]; if (c === M[y][x + 1] && c === M[y + 1][x] && c === M[y + 1][x + 1]) pn += 3; }
      for (y = 0; y < size; y++) for (x = 0; x < size; x++) if (M[y][x]) dark++;
      return pn + Math.floor(Math.abs(dark * 20 - size * size * 10) / (size * size)) * 10;
    }
    var best = 0, bp = 1e9;
    for (var m = 0; m < 8; m++) { apply(m); format(m); var pv = penalty(); if (pv < bp) { bp = pv; best = m; } apply(m); }
    apply(best); format(best);
    return { size: size, m: M };
  };
  /* the QR code as SVG (one path of squares), with a quiet zone of 2 modules */
  D.qrSVG = function (text, dark, light, px) {
    var q = D.qr(text), n = q.size, b = 2, s = n + 2 * b, d = '';
    for (var y = 0; y < n; y++) for (var x = 0; x < n; x++) if (q.m[y][x]) d += 'M' + (x + b) + ' ' + (y + b) + 'h1v1h-1z';
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + s + ' ' + s + '"' + (px ? ' width="' + px + '" height="' + px + '"' : '') + ' shape-rendering="crispEdges"><rect width="' + s + '" height="' + s + '" fill="' + (light || '#FFFFFF') + '"/><path d="' + d + '" fill="' + (dark || '#000000') + '"/></svg>';
  };
})();

/* =====================================================================================
 * Business cards (2026-10-23 rebuild): real corporate cards.
 * Three layouts, each a set of designs:
 *   "Logo + details"  front = logo, company and tagline; back = name, title and contact rows with icons
 *   "Details + logo"  front = name, title and contacts; back = logo, company and tagline on colour
 *   "One side"        everything on one side
 * Blocks carry a role (logo, company, tagline, name, title, contact); each design decides where a role goes.
 * ===================================================================================== */
(function () {
  var IC = {
    phone: 'M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z',
    mail: 'M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z',
    web: 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.9 6h-2.9a15.7 15.7 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8zM12 4c.8 1.2 1.5 2.5 1.9 4h-3.8c.4-1.5 1.1-2.8 1.9-4zM4.3 14a8.2 8.2 0 0 1 0-4h3.4a16.5 16.5 0 0 0 0 4zm.8 2h2.9c.3 1.3.8 2.5 1.4 3.6A8 8 0 0 1 5.1 16zM8 8H5.1a8 8 0 0 1 4.3-3.6C8.8 5.5 8.3 6.7 8 8zm4 12c-.8-1.2-1.5-2.5-1.9-4h3.8c-.4 1.5-1.1 2.8-1.9 4zm2.3-6H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4zm.3 5.6c.6-1.1 1.1-2.3 1.4-3.6h2.9a8 8 0 0 1-4.3 3.6zm1.8-5.6a16.5 16.5 0 0 0 0-4h3.4a8.2 8.2 0 0 1 0 4z',
    pin: 'M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z',
    user: 'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v3h16v-3c0-2.8-3.6-5-8-5z',
    link: 'M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zM8 18a4 4 0 0 1-2.8-6.8l2.1-2.1a1 1 0 1 1 1.4 1.4l-2.1 2.1a2 2 0 0 0 2.8 2.8l2.1-2.1a1 1 0 1 1 1.4 1.4l-2.1 2.1A4 4 0 0 1 8 18zm8.7-5.3a1 1 0 0 1-.7-1.7l2.1-2.1a2 2 0 0 0-2.8-2.8l-2.1 2.1a1 1 0 1 1-1.4-1.4l2.1-2.1a4 4 0 0 1 5.6 5.6l-2.1 2.1a1 1 0 0 1-.7.3z'
  };
  D.ICONS = IC; D.ICON_ORDER = ['phone', 'mail', 'web', 'pin', 'user', 'link'];
  D.iconSVG = function (name, color, size) {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' + (size || 24) + '" height="' + (size || 24) + '" viewBox="0 0 24 24"><path d="' + (IC[name] || IC.link) + '" fill="' + (color || '#fff') + '"/></svg>';
  };
  /* the initials of a company name, for the badge when no logo was added */
  D.initials = function (s) {
    var w = String(s || '').replace(/[\[\]]/g, '').trim().split(/\s+/).filter(Boolean);
    return (w.length > 1 ? w[0].charAt(0) + w[1].charAt(0) : (w[0] || 'DS').slice(0, 2)).toUpperCase();
  };

  /* ---------- blocks: s_contact (rows with icons) and s_mark (logo or initials badge) ---------- */
  var blockHTML0 = D.blockHTML;
  D.blockHTML = function (b, ctx) {
    if (b.t !== 's_contact' && b.t !== 's_mark' && b.t !== 's_qr' && b.t !== 's_photo') return blockHTML0(b, ctx);
    var o = ctx.o || {}, bx = D.blockBox(b, ctx.t || D.look(ctx.doc));
    var w = function (inner) { return '<div class="dsd-blk ' + bx.cls + (b.lock ? ' dsd-lk' : '') + '" data-id="' + b.id + '" data-t="' + b.t + '"' + (bx.style ? ' style="' + bx.style + '"' : '') + '>' + inner + '</div>'; };
    var edf = function (tag, cls, field, val, ph) {
      return '<' + tag + (cls ? ' class="' + cls + '"' : '') + (o.edit ? ' contenteditable="true" spellcheck="true" data-f="' + field + '" data-ph="' + D.esc(ph || '') + '"' : '') + '>' + D.esc(val || '') + '</' + tag + '>';
    };
    if (b.t === 's_contact') {
      return w('<div class="dsd-s-ct">' + (b.rows || []).map(function (r, k) {
        return '<div class="r"><i class="ic" data-ic="' + k + '" title="' + (o.edit ? 'Click to change the icon' : '') + '"><svg viewBox="0 0 24 24"><path d="' + (IC[r.i] || IC.link) + '"/></svg></i>' + edf('span', 'tx', 'rows.' + k + '.t', r.t, 'Contact') + '</div>';
      }).join('') + '</div>');
    }
    if (b.t === 's_qr') {
      return w('<div class="dsd-s-qr">' + D.qrSVG(b.text || ' ', '#111111', '#FFFFFF') + '</div>' + (o.edit ? edf('div', 'dsd-s-qu', 'text', b.text, 'QR link, e.g. https://yoursite.com') : ''));
    }
    if (b.t === 's_photo') {
      return w('<div class="dsd-s-ph' + (b.src ? '' : ' empty') + '"' + (b.src ? ' style="background-image:url(\'' + String(b.src).replace(/'/g, '%27') + '\')"' : '') + '>' + (b.src ? '' : '<span><svg viewBox="0 0 24 24"><path d="' + IC.user + '"/></svg>' + (o.edit ? '<em>Click to add a photo</em>' : '') + '</span>') + '</div>');
    }
    var doc = ctx.doc || D._cdoc || {}, lg = doc.logo && doc.logo.src && !doc.logo.hide ? doc.logo.src : '';
    return w('<div class="dsd-s-mk">' + (lg ? '<img alt="Logo" src="' + D.esc(lg) + '">' : '<span class="mono">' + edf('span', '', 'text', b.text || D.initials(doc.org), 'DS') + '</span>') + '</div>');
  };
  D.CSS += '.dsd-s-ct{display:flex;flex-direction:column;gap:calc(3.6pt*var(--fit,1));width:100%}' +
    '.dsd-s-ct .r{display:flex;align-items:center;gap:calc(5pt*var(--fit,1));font-size:calc(7.4pt*var(--fit,1));line-height:1.2;color:var(--ink)}' +
    '.dsd-s-ct .ic{flex:none;width:calc(12.5pt*var(--fit,1));height:calc(12.5pt*var(--fit,1));border-radius:50%;background:var(--a);display:flex;align-items:center;justify-content:center;font-style:normal}' +
    '.dsd-s-ct .ic svg{width:58%;height:58%;fill:var(--ona)}.dsd-edit .dsd-s-ct .ic{cursor:pointer}' +
    '.dsd-s-ct .tx{min-width:20px;overflow-wrap:anywhere}' +
    '.bcqr .dsd-s-qr{width:var(--qr,50px);height:var(--qr,50px);margin:0 auto}.dsd-s-qr svg{display:block;width:100%;height:100%}' +
    '.dsd-s-qu{display:none;font-size:6pt;margin-top:2px;color:#64748B;word-break:break-all;line-height:1.1}.dsd-edit .dsd-s-qu{display:block}' +
    '.bcph{padding:0!important}.bcph>.dsd-blk{margin:0!important}.dsd-s-ph{width:100%;height:var(--zh,120px);background:#CBD5E1 center/cover no-repeat;clip-path:var(--clip,none);display:flex;align-items:center;justify-content:center}' +
    '.dsd-s-ph.empty{background:linear-gradient(135deg,#E2E8F0,#CBD5E1)}.dsd-s-ph span{display:flex;flex-direction:column;align-items:center;gap:3px}.dsd-s-ph svg{width:38%;max-width:46px;fill:#94A3B8}.dsd-s-ph em{font-style:normal;font-size:6pt;color:#475569}.dsd-edit .dsd-s-ph{cursor:pointer}' +
    '.bc.ond .dsd-s-ct .ic{background:var(--icb,#1B1D22)}.bc.ond .dsd-s-ct .ic svg{fill:#fff}.bc.ond:not(.dark) .dsd-s-s{color:var(--p)}.bc.ond.dark .dsd-s-s{color:rgba(255,255,255,.9)}.bc.ond.lead .dsd-s-t,.bc.ond .dsd-s-t{border-color:rgba(255,255,255,.6)}' +
    '.dsd-s-mk{display:block;width:100%;line-height:0}.dsd-s-mk2{margin-bottom:calc(6pt*var(--fit,1))}.dsd-s-mk2 img{height:calc(20pt*var(--fit,1)*var(--lgk,1))}' +
    '.dsd-s-mk img{display:inline-block;max-width:100%;height:calc(var(--mk,34pt)*var(--fit,1)*var(--lgk,1));object-fit:contain}' +
    '.dsd-s-mk .mono{display:inline-flex;align-items:center;justify-content:center;width:calc(var(--mk,34pt)*var(--fit,1));height:calc(var(--mk,34pt)*var(--fit,1));border-radius:var(--mkr,50%);background:var(--a);color:var(--ona);font-family:var(--headf);font-weight:800;font-size:calc(var(--mk,34pt)*.4*var(--fit,1));letter-spacing:.02em}' +
    /* card text: tight and readable */
    '.bc .dsd-s-t{line-height:1.02;margin:0 0 calc(1.5pt*var(--fit,1))}.bc .dsd-s-s{font-style:normal;font-family:var(--bodyf);font-size:calc(6.6pt*var(--fit,1));letter-spacing:.16em;text-transform:uppercase;margin:0;color:var(--st)}' +
    '.bc .dsd-s-k{font-size:calc(6.2pt*var(--fit,1));letter-spacing:.2em;margin:0 0 calc(2pt*var(--fit,1))}.bc .dsd-s-x{font-size:calc(7pt*var(--fit,1));margin-top:calc(3pt*var(--fit,1))}' +
    '.bc .dsd-blk+.dsd-blk{margin-top:calc(2pt*var(--fit,1))}.bc .dsd-blk[data-t="s_contact"]{margin-top:calc(7pt*var(--fit,1))}.bc .dsd-blk[data-t="s_mark"]+.dsd-blk{margin-top:calc(5pt*var(--fit,1))}' +
    '.bc.dark .dsd-s-ct .r{color:#fff}.bc.dark .dsd-s-s{color:var(--at)}' +
    '.bc.icl .dsd-s-ct .ic{background:transparent;border:1px solid var(--a)}.bc.icl .dsd-s-ct .ic svg{fill:var(--a)}' +
    '.bc.lead .dsd-s-t{border-bottom:1.5px solid var(--a);padding-bottom:calc(3pt*var(--fit,1));margin-bottom:calc(3pt*var(--fit,1))}';

  /* Word export of the two blocks */
  var toDocx0 = D.toDocx;
  D.toDocx = function (doc, opts) { D._bcIcons = {}; return toDocx0(doc, opts); };
  D._bcBlockDocx = null; /* set inside toDocx via hook below */

  /* ---------- the card designs ---------- */
  function P(pts) { return poly(pts, arguments[1], arguments[2]); }
  function curve(d, c, op) { return path(d, c, op); }
  /* each front/back returns { s: shapes, z: [ zones ] } for a W x H card; zones: x,y,w,h,align,v,dark,cls,tp,mk */
  var F = {}, Bk = {};
  /* logo-side designs */
  F.swoosh = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + curve('M' + W * 0.42 + ' ' + H + ' C ' + W * 0.62 + ' ' + H * 0.55 + ', ' + W * 0.8 + ' ' + H * 0.45 + ', ' + W + ' ' + H * 0.12 + ' L ' + W + ' ' + H + ' Z', t.a) +
      curve('M' + W * 0.5 + ' ' + H + ' C ' + W * 0.68 + ' ' + H * 0.66 + ', ' + W * 0.84 + ' ' + H * 0.58 + ', ' + W + ' ' + H * 0.3 + ' L ' + W + ' ' + H + ' Z', t.s),
      z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.5, h: H * 0.72, align: 'left', v: 'center', dark: true }] };
  };
  F.angle = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[0, 0], [W * 0.58, 0], [W * 0.4, H], [0, H]], t.p) + P([[W * 0.58, 0], [W * 0.63, 0], [W * 0.45, H], [W * 0.4, H]], t.a) + P([[W * 0.82, 0], [W, 0], [W, H * 0.32]], t.s),
      z: [{ x: W * 0.06, y: H * 0.12, w: W * 0.36, h: H * 0.76, align: 'center', v: 'center', dark: true, mk: 30 }] };
  };
  F.gold = function (t, W, H) {
    var m = H * 0.07;
    return { s: rect(0, 0, W, H, '#14161B') + '<rect x="' + m + '" y="' + m + '" width="' + (W - 2 * m) + '" height="' + (H - 2 * m) + '" fill="none" stroke="' + t.a + '" stroke-width="1"/>' +
      P([[W - m, H - m - H * 0.3], [W - m, H - m], [W - m - H * 0.3, H - m]], t.a) + P([[m, m + H * 0.3], [m, m], [m + H * 0.3, m]], t.a),
      z: [{ x: W * 0.15, y: H * 0.16, w: W * 0.7, h: H * 0.68, align: 'center', v: 'center', dark: true, gold: 1 }] };
  };
  F.chevron = function (t, W, H) {
    var s = rect(0, 0, W, H, t.p);
    for (var i = 0; i < 3; i++) { var x = W * 0.66 + i * W * 0.09; s += P([[x, 0], [x + W * 0.07, 0], [x + W * 0.07 + H * 0.28, H / 2], [x + W * 0.07, H], [x, H], [x + H * 0.28, H / 2]], i === 1 ? t.a : t.s, i === 1 ? 1 : 0.85); }
    return { s: s, z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.56, h: H * 0.72, align: 'left', v: 'center', dark: true }] };
  };
  F.circles = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + circ(W, 0, H * 0.62, t.p) + circ(W, 0, H * 0.4, t.a) + circ(0, H, H * 0.36, t.s) + circ(0, H, H * 0.2, t.a),
      z: [{ x: W * 0.12, y: H * 0.2, w: W * 0.56, h: H * 0.6, align: 'left', v: 'center', dark: false }] };
  };
  F.split = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.5, 0, W * 0.5, H, t.p) + rect(W * 0.5 - 2, H * 0.2, 4, H * 0.6, t.a),
      z: [{ x: W * 0.05, y: H * 0.12, w: W * 0.4, h: H * 0.76, align: 'center', v: 'center', dark: false, mk: 32 }, { x: W * 0.55, y: H * 0.12, w: W * 0.4, h: H * 0.76, align: 'center', v: 'center', dark: true, role2: 1 }] };
  };
  F.wave = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + wave(W, H * 0.74, H * 0.06, t.s, 1, 0, H) + wave(W, H * 0.84, H * 0.05, t.a, 1, 1, H),
      z: [{ x: W * 0.1, y: H * 0.1, w: W * 0.8, h: H * 0.58, align: 'center', v: 'center', dark: true }] };
  };
  F.hexa = function (t, W, H) {
    var s = rect(0, 0, W, H, t.tint), r = H * 0.1;
    for (var i = 0; i < 4; i++) for (var j = 0; j < 3; j++) { if ((i + j) % 3 === 1) continue; var cx = W * 0.64 + i * r * 1.75 + (j % 2) * r * 0.87, cy = H * 0.2 + j * r * 1.5; s += hexP(cx, cy, r * 0.92, (i + j) % 2 ? t.p : t.a); }
    return { s: s, z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.5, h: H * 0.72, align: 'left', v: 'center', dark: false }] };
  };
  function hexP(cx, cy, r, c) { var p = []; for (var i = 0; i < 6; i++) { var a = Math.PI / 3 * i + Math.PI / 6; p.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]); } return poly(p, c); }
  F.ribbon = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[W * 0.55, 0], [W, 0], [W, H * 0.5], [W * 0.86, H * 0.5]], t.p) + P([[W * 0.66, 0], [W * 0.74, 0], [W * 0.96, H * 0.44], [W * 0.88, H * 0.44]], t.a) +
      P([[0, H * 0.64], [W * 0.22, H], [0, H]], t.p) + P([[0, H * 0.82], [W * 0.1, H], [0, H]], t.a),
      z: [{ x: W * 0.1, y: H * 0.18, w: W * 0.56, h: H * 0.64, align: 'left', v: 'center', dark: false }] };
  };
  /* details-side designs */
  Bk.sideCurve = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + curve('M' + W * 0.72 + ' 0 C ' + W * 0.6 + ' ' + H * 0.35 + ', ' + W * 0.6 + ' ' + H * 0.65 + ', ' + W * 0.72 + ' ' + H + ' L ' + W + ' ' + H + ' L ' + W + ' 0 Z', t.p) +
      curve('M' + W * 0.7 + ' 0 C ' + W * 0.58 + ' ' + H * 0.35 + ', ' + W * 0.58 + ' ' + H * 0.65 + ', ' + W * 0.7 + ' ' + H + ' L ' + W * 0.72 + ' ' + H + ' C ' + W * 0.6 + ' ' + H * 0.65 + ', ' + W * 0.6 + ' ' + H * 0.35 + ', ' + W * 0.72 + ' 0 Z', t.a),
      z: [{ x: W * 0.07, y: H * 0.1, w: W * 0.54, h: H * 0.8, align: 'left', v: 'center', dark: false, lead: 1 }, { x: W * 0.7, y: H * 0.2, w: W * 0.26, h: H * 0.6, align: 'center', v: 'center', dark: true, small: 1 }] };
  };
  Bk.topBar = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W, H * 0.07, t.p) + rect(0, H * 0.07, W * 0.32, H * 0.025, t.a) + P([[W * 0.84, H], [W, H * 0.68], [W, H]], t.p),
      z: [{ x: W * 0.07, y: H * 0.16, w: W * 0.86, h: H * 0.74, align: 'left', v: 'center', dark: false, lead: 1, two: 1 }] };
  };
  Bk.darkPanel = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + rect(W * 0.62, 0, W * 0.38, H, t.s) + rect(W * 0.62, 0, 3, H, t.a),
      z: [{ x: W * 0.07, y: H * 0.1, w: W * 0.5, h: H * 0.8, align: 'left', v: 'center', dark: true, lead: 1 }, { x: W * 0.65, y: H * 0.2, w: W * 0.32, h: H * 0.6, align: 'center', v: 'center', dark: true, small: 1 }] };
  };
  Bk.cornerTri = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[W * 0.7, 0], [W, 0], [W, H * 0.55]], t.p) + P([[W * 0.82, 0], [W, 0], [W, H * 0.3]], t.a) + rect(0, H - H * 0.05, W, H * 0.05, t.p),
      z: [{ x: W * 0.07, y: H * 0.12, w: W * 0.66, h: H * 0.76, align: 'left', v: 'center', dark: false, lead: 1 }] };
  };
  Bk.goldBack = function (t, W, H) {
    var m = H * 0.07;
    return { s: rect(0, 0, W, H, '#14161B') + '<rect x="' + m + '" y="' + m + '" width="' + (W - 2 * m) + '" height="' + (H - 2 * m) + '" fill="none" stroke="' + t.a + '" stroke-width="0.8" stroke-opacity="0.6"/>',
      z: [{ x: W * 0.1, y: H * 0.14, w: W * 0.8, h: H * 0.72, align: 'left', v: 'center', dark: true, lead: 1, gold: 1, icl: 1 }] };
  };
  Bk.waveSide = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + curve('M0 0 L ' + W * 0.24 + ' 0 C ' + W * 0.34 + ' ' + H * 0.3 + ', ' + W * 0.16 + ' ' + H * 0.62 + ', ' + W * 0.26 + ' ' + H + ' L 0 ' + H + ' Z', t.p) +
      curve('M' + W * 0.24 + ' 0 L ' + W * 0.27 + ' 0 C ' + W * 0.37 + ' ' + H * 0.3 + ', ' + W * 0.19 + ' ' + H * 0.62 + ', ' + W * 0.29 + ' ' + H + ' L ' + W * 0.26 + ' ' + H + ' C ' + W * 0.16 + ' ' + H * 0.62 + ', ' + W * 0.34 + ' ' + H * 0.3 + ', ' + W * 0.24 + ' 0 Z', t.a),
      z: [{ x: W * 0.02, y: H * 0.25, w: W * 0.2, h: H * 0.5, align: 'center', v: 'center', dark: true, small: 1 }, { x: W * 0.36, y: H * 0.1, w: W * 0.58, h: H * 0.8, align: 'left', v: 'center', dark: false, lead: 1 }], swap: 1 };
  };
  Bk.stripeBottom = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.tint) + rect(0, H * 0.8, W, H * 0.2, t.p) + P([[W * 0.6, H * 0.8], [W * 0.68, H * 0.8], [W * 0.62, H], [W * 0.54, H]], t.a),
      z: [{ x: W * 0.07, y: H * 0.08, w: W * 0.86, h: H * 0.68, align: 'left', v: 'center', dark: false, lead: 1, two: 1 }] };
  };
  Bk.cleanLine = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.06, H * 0.12, 3, H * 0.76, t.a) + circ(W * 0.92, H * 0.85, H * 0.07, t.p),
      z: [{ x: W * 0.11, y: H * 0.1, w: W * 0.82, h: H * 0.8, align: 'left', v: 'center', dark: false, two: 1, icl: 1 }] };
  };
  /* ---- more logo-side designs (2026-10-23 reference set) ---- */
  F.brackets = function (t, W, H) {
    var s = rect(0, 0, W, H, t.p), L = H * 0.22, th2 = H * 0.035, m = H * 0.1;
    [[m, m, 1, 1], [W - m, m, -1, 1], [m, H - m, 1, -1], [W - m, H - m, -1, -1]].forEach(function (c) { s += rect(c[2] > 0 ? c[0] : c[0] - L, c[3] > 0 ? c[1] : c[1] - th2, L, th2, t.a) + rect(c[2] > 0 ? c[0] : c[0] - th2, c[3] > 0 ? c[1] : c[1] - L, th2, L, t.a); });
    return { s: s, z: [{ x: W * 0.2, y: H * 0.2, w: W * 0.6, h: H * 0.6, align: 'center', v: 'center', dark: true }] };
  };
  F.notch = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + circ(0, H / 2, H * 0.3, t.p) + circ(W, H / 2, H * 0.3, t.p) + circ(0, H / 2, H * 0.19, t.a) + circ(W, H / 2, H * 0.19, t.a) + rect(0, 0, W, H * 0.04, t.p) + rect(0, H * 0.96, W, H * 0.04, t.p),
      z: [{ x: W * 0.22, y: H * 0.16, w: W * 0.56, h: H * 0.68, align: 'center', v: 'center', dark: false }] };
  };
  F.bandFoot = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + rect(0, H * 0.78, W, H * 0.22, '#FFFFFF') + rect(0, H * 0.75, W, H * 0.03, t.a) + P([[W * 0.38, H * 0.78], [W * 0.62, H * 0.78], [W * 0.58, H * 0.86], [W * 0.42, H * 0.86]], t.a),
      z: [{ x: W * 0.12, y: H * 0.1, w: W * 0.76, h: H * 0.6, align: 'center', v: 'center', dark: true }] };
  };
  F.goldSwirl = function (t, W, H) {
    var s = rect(0, 0, W, H, '#111317');
    for (var i = 0; i < 5; i++) s += path('M' + (W * 0.5 + i * 7) + ' ' + H + ' C ' + (W * 0.7 + i * 6) + ' ' + H * 0.62 + ', ' + (W * 0.62 + i * 5) + ' ' + H * 0.3 + ', ' + (W + 4) + ' ' + (H * 0.05 + i * 6), 'none', null, t.a, i === 2 ? 2.2 : 0.9);
    return { s: s, z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.5, h: H * 0.72, align: 'left', v: 'center', dark: true, gold: 1 }] };
  };
  F.scallop = function (t, W, H) {
    var s = rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.8, 0, W * 0.2, H, t.p), r = H / 10;
    for (var i = 0; i < 5; i++) s += circ(W * 0.8, r + i * 2 * r, r * 0.92, t.a);
    return { s: s, z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.6, h: H * 0.72, align: 'left', v: 'center', dark: false }] };
  };
  F.leaf = function (t, W, H) {
    var s = rect(0, 0, W, H, t.p), lf = function (cx, cy, l, a, c, op) { return '<path transform="rotate(' + a + ' ' + cx + ' ' + cy + ')" d="M' + cx + ' ' + (cy - l / 2) + ' C ' + (cx + l * 0.32) + ' ' + (cy - l * 0.2) + ', ' + (cx + l * 0.32) + ' ' + (cy + l * 0.2) + ', ' + cx + ' ' + (cy + l / 2) + ' C ' + (cx - l * 0.32) + ' ' + (cy + l * 0.2) + ', ' + (cx - l * 0.32) + ' ' + (cy - l * 0.2) + ', ' + cx + ' ' + (cy - l / 2) + ' Z" fill="' + c + '"' + (op != null ? ' fill-opacity="' + op + '"' : '') + '/>'; };
    s += lf(W * 0.86, H * 0.32, H * 0.9, 35, t.a) + lf(W * 0.74, H * 0.82, H * 0.7, -40, t.s) + lf(W * 0.96, H * 0.86, H * 0.5, 10, t.a, 0.6);
    return { s: s, z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.54, h: H * 0.72, align: 'left', v: 'center', dark: true }] };
  };
  F.geoTri = function (t, W, H) {
    var s = rect(0, 0, W, H, '#FFFFFF'), c = [t.p, t.s, t.a, t.p, t.a, t.s], k = 0, g = H / 3;
    for (var j = 0; j < 3; j++) for (var i = 0; i < 3 - j; i++) { var x = i * g * 0.9, y = j * g; s += P([[x, y], [x + g, y], [x, y + g]], c[k++ % 6]) + (i + j < 2 ? P([[x + g, y], [x + g, y + g], [x, y + g]], c[k++ % 6], 0.85) : ''); }
    return { s: s, z: [{ x: W * 0.4, y: H * 0.16, w: W * 0.54, h: H * 0.68, align: 'right', v: 'center', dark: false }] };
  };
  F.doubleWave = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + path('M0 ' + H * 0.64 + ' C ' + W * 0.3 + ' ' + H * 0.5 + ', ' + W * 0.6 + ' ' + H * 0.86 + ', ' + W + ' ' + H * 0.62 + ' L ' + W + ' ' + H + ' L 0 ' + H + ' Z', t.p) +
      path('M0 ' + H * 0.62 + ' C ' + W * 0.3 + ' ' + H * 0.48 + ', ' + W * 0.6 + ' ' + H * 0.84 + ', ' + W + ' ' + H * 0.6, 'none', null, t.a, 2.5),
      z: [{ x: W * 0.12, y: H * 0.08, w: W * 0.76, h: H * 0.48, align: 'center', v: 'center', dark: false }] };
  };
  F.photoHalf = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[W * 0.5, 0], [W * 0.56, 0], [W * 0.46, H], [W * 0.4, H]], t.a) + rect(0, H * 0.92, W * 0.44, H * 0.08, t.p),
      z: [{ x: W * 0.07, y: H * 0.12, w: W * 0.36, h: H * 0.72, align: 'left', v: 'center', dark: false, mk: 28 },
        { tag: 'photo', x: W * 0.47, y: 0, w: W * 0.53, h: H, clip: 'polygon(14% 0,100% 0,100% 100%,0 100%)' }] };
  };
  F.photoCircle = function (t, W, H) {
    var r = H * 0.36;
    return { s: rect(0, 0, W, H, t.p) + circ(W * 0.74, H * 0.5, r + 6, t.a) + circ(W * 0.74, H * 0.5, r + 2, t.p),
      z: [{ x: W * 0.07, y: H * 0.14, w: W * 0.46, h: H * 0.72, align: 'left', v: 'center', dark: true, mk: 28 },
        { tag: 'photo', x: W * 0.74 - r, y: H * 0.5 - r, w: r * 2, h: r * 2, clip: 'circle(50% at 50% 50%)' }] };
  };
  /* ---- more details-side designs ---- */
  Bk.nameTab = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[0, H * 0.1], [W * 0.6, H * 0.1], [W * 0.66, H * 0.22], [W * 0.6, H * 0.34], [0, H * 0.34]], t.p) + rect(0, H * 0.34, W * 0.04, H * 0.03, t.a) + rect(0, H * 0.94, W, H * 0.06, t.a),
      z: [{ tag: 'tab', x: W * 0.07, y: H * 0.11, w: W * 0.52, h: H * 0.22, align: 'left', v: 'center', dark: true, tp: 12 },
        { x: W * 0.07, y: H * 0.42, w: W * 0.6, h: H * 0.48, align: 'left', v: 'center', dark: false },
        { tag: 'qr', x: W * 0.72, y: H * 0.44, w: H * 0.4, h: H * 0.4 }] };
  };
  Bk.footWeb = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(0, H * 0.84, W, H * 0.16, t.p) + P([[W * 0.66, H * 0.84], [W, H * 0.84], [W, H * 0.7]], t.a) + rect(W * 0.07, H * 0.1, W * 0.05, 2, t.a),
      z: [{ x: W * 0.07, y: H * 0.13, w: W * 0.6, h: H * 0.66, align: 'left', v: 'center', dark: false, lead: 1 }, { tag: 'qr', x: W * 0.72, y: H * 0.22, w: H * 0.42, h: H * 0.42 }] };
  };
  Bk.leftBar = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W * 0.3, H, t.p) + P([[W * 0.3, 0], [W * 0.34, 0], [W * 0.3, H * 0.16]], t.a) + rect(W * 0.3, H - 3, W * 0.7, 3, t.a),
      z: [{ x: W * 0.03, y: H * 0.25, w: W * 0.24, h: H * 0.5, align: 'center', v: 'center', dark: true, small: 1 }, { x: W * 0.36, y: H * 0.1, w: W * 0.6, h: H * 0.8, align: 'left', v: 'center', dark: false, lead: 1 }], swap: 1 };
  };
  Bk.waveBottom2 = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + path('M0 ' + H * 0.86 + ' C ' + W * 0.35 + ' ' + H * 0.74 + ', ' + W * 0.65 + ' ' + H * 0.98 + ', ' + W + ' ' + H * 0.8 + ' L ' + W + ' ' + H + ' L 0 ' + H + ' Z', t.p) + path('M0 ' + H * 0.84 + ' C ' + W * 0.35 + ' ' + H * 0.72 + ', ' + W * 0.65 + ' ' + H * 0.96 + ', ' + W + ' ' + H * 0.78, 'none', null, t.a, 2),
      z: [{ x: W * 0.07, y: H * 0.08, w: W * 0.62, h: H * 0.66, align: 'left', v: 'center', dark: false, lead: 1 }, { tag: 'qr', x: W * 0.74, y: H * 0.16, w: H * 0.4, h: H * 0.4 }] };
  };
  Bk.orangePanel = function (t, W, H) {
    var dk = D.cr('#FFFFFF', t.a) > D.cr('#141414', t.a);
    return { s: rect(0, 0, W, H, t.p) + rect(W * 0.04, H * 0.08, W * 0.56, H * 0.84, t.a) + rect(W * 0.66, H * 0.08, W * 0.3, 2, t.a),
      z: [{ x: W * 0.08, y: H * 0.12, w: W * 0.49, h: H * 0.76, align: 'left', v: 'center', dark: dk, ond: 1, icb: t.p }, { x: W * 0.66, y: H * 0.14, w: W * 0.3, h: H * 0.3, align: 'center', v: 'center', dark: true, small: 1 },
        { tag: 'qr', x: W * 0.81 - H * 0.2, y: H * 0.5, w: H * 0.4, h: H * 0.4 }] };
  };
  Bk.goldLines = function (t, W, H) {
    var s = rect(0, 0, W, H, '#111317');
    for (var i = 0; i < 4; i++) s += line(W * 0.62 + i * 12, 0, W + i * 12, H * 0.62, t.a, i === 1 ? 1.6 : 0.7);
    return { s: s, z: [{ x: W * 0.08, y: H * 0.12, w: W * 0.56, h: H * 0.76, align: 'left', v: 'center', dark: true, lead: 1, gold: 1, icl: 1 }, { tag: 'qr', x: W * 0.74, y: H * 0.52, w: H * 0.38, h: H * 0.38 }] };
  };
  Bk.notchBack = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + circ(W, H / 2, H * 0.46, t.a) + circ(W, H / 2, H * 0.42, t.p) + rect(0, 0, W * 0.5, H * 0.04, t.p),
      z: [{ x: W * 0.07, y: H * 0.12, w: W * 0.58, h: H * 0.76, align: 'left', v: 'center', dark: false, lead: 1 }, { x: W * 0.78, y: H * 0.3, w: W * 0.2, h: H * 0.4, align: 'center', v: 'center', dark: true, small: 1 }] };
  };
  Bk.photoSide = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.34, 0, 3, H, t.a) + rect(W * 0.34, H * 0.9, W * 0.66, H * 0.1, t.p),
      z: [{ tag: 'photo', x: 0, y: 0, w: W * 0.34, h: H }, { x: W * 0.4, y: H * 0.08, w: W * 0.56, h: H * 0.78, align: 'left', v: 'center', dark: false, lead: 1 }], swap: 1 };
  };
  Bk.panelQR = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.64, 0, W * 0.36, H, t.p) + rect(W * 0.64, 0, 3, H, t.a) + rect(W * 0.07, H * 0.9, W * 0.2, 2, t.a),
      z: [{ x: W * 0.07, y: H * 0.1, w: W * 0.54, h: H * 0.78, align: 'left', v: 'center', dark: false, lead: 1 },
        { tag: 'mark', x: W * 0.67, y: H * 0.07, w: W * 0.3, h: H * 0.42, align: 'center', v: 'center', dark: true, small: 1 },
        { tag: 'qr', x: W * 0.82 - H * 0.17, y: H * 0.56, w: H * 0.34, h: H * 0.34 }] };
  };
  Bk.photoMark = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.3, 0, 3, H, t.a) + rect(W * 0.76, 0, W * 0.24, H, t.p),
      z: [{ tag: 'photo', x: 0, y: 0, w: W * 0.3, h: H }, { x: W * 0.34, y: H * 0.1, w: W * 0.4, h: H * 0.8, align: 'left', v: 'center', dark: false, lead: 1 },
        { tag: 'mark', x: W * 0.77, y: H * 0.15, w: W * 0.22, h: H * 0.7, align: 'center', v: 'center', dark: true, small: 1 }] };
  };
  Bk.cleanQR = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(W * 0.06, H * 0.12, 3, H * 0.64, t.a) + rect(0, H * 0.86, W, H * 0.14, t.p),
      z: [{ x: W * 0.1, y: H * 0.08, w: W * 0.58, h: H * 0.72, align: 'left', v: 'center', dark: false }, { tag: 'qr', x: W * 0.74, y: H * 0.14, w: H * 0.42, h: H * 0.42 }] };
  };
  /* ---- third set (picked from the reference sheet) ---- */
  function mandala(cx, cy, R, c, op) {
    var s = ring(cx, cy, R, c, 1.2, op) + ring(cx, cy, R * 0.78, c, 0.8, op) + ring(cx, cy, R * 0.34, c, 1, op) + circ(cx, cy, R * 0.12, c, op);
    for (var i = 0; i < 16; i++) { var a = i * 22.5; s += '<ellipse cx="' + cx + '" cy="' + (cy - R * 0.56) + '" rx="' + R * 0.09 + '" ry="' + R * 0.2 + '" transform="rotate(' + a + ' ' + cx + ' ' + cy + ')" fill="none" stroke="' + c + '" stroke-width="1"' + (op != null ? ' stroke-opacity="' + op + '"' : '') + '/>'; }
    for (i = 0; i < 32; i++) { var b = i * 11.25; s += '<circle cx="' + cx + '" cy="' + (cy - R * 0.89) + '" r="' + R * 0.025 + '" transform="rotate(' + b + ' ' + cx + ' ' + cy + ')" fill="' + c + '"' + (op != null ? ' fill-opacity="' + op + '"' : '') + '/>'; }
    return s;
  }
  F.mandala = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#14161B') + mandala(W * 0.98, H * 0.5, H * 0.62, t.a) + rect(0, H * 0.92, W * 0.5, 2, t.a),
      z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.5, h: H * 0.72, align: 'left', v: 'center', dark: true, gold: 1 }] };
  };
  F.mandalaColor = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + mandala(W * 0.04, H / 2, H * 0.6, t.a, 0.7) + rect(0, 0, W, H * 0.04, t.a) + rect(0, H * 0.96, W, H * 0.04, t.a),
      z: [{ x: W * 0.44, y: H * 0.16, w: W * 0.5, h: H * 0.68, align: 'left', v: 'center', dark: true }] };
  };
  F.blobs = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + blob(W * 0.95, H * 0.08, H * 0.48, 2, t.a, 0.85) + blob(W * 0.98, H * 0.18, H * 0.36, 5, t.p) + blob(W * 0.02, H * 0.98, H * 0.36, 8, t.s, 0.9) + blob(W * 0.09, H * 1.02, H * 0.2, 3, t.a),
      z: [{ x: W * 0.2, y: H * 0.16, w: W * 0.5, h: H * 0.68, align: 'center', v: 'center', dark: false }] };
  };
  F.shards = function (t, W, H) {
    var C = [t.p, t.a, t.s, D.mix(t.a, '#FFFFFF', 0.35), D.mix(t.p, '#FFFFFF', 0.3), t.a], s = rect(0, 0, W, H, '#FFFFFF'), x = W * 0.44;
    var pts = [[[0, 0], [x * 0.6, 0], [x * 0.2, H * 0.45]], [[x * 0.6, 0], [x, 0], [x * 0.7, H * 0.4], [x * 0.2, H * 0.45]], [[0, 0], [x * 0.2, H * 0.45], [0, H * 0.62]], [[x * 0.2, H * 0.45], [x * 0.7, H * 0.4], [x * 0.55, H], [0, H], [0, H * 0.62]], [[x * 0.7, H * 0.4], [x, 0], [x * 0.82, H], [x * 0.55, H]]];
    pts.forEach(function (p, i) { s += P(p, C[i % C.length]); });
    return { s: s, z: [{ x: W * 0.5, y: H * 0.16, w: W * 0.44, h: H * 0.68, align: 'left', v: 'center', dark: false }] };
  };
  F.centerBand = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + rect(0, H * 0.3, W, H * 0.4, '#FFFFFF') + rect(0, H * 0.27, W, 2, t.a) + rect(0, H * 0.71, W, 2, t.a) + dots(W * 0.06, H * 0.08, 8, 2, H * 0.06, 1.2, '#FFFFFF', 0.35),
      z: [{ x: W * 0.1, y: H * 0.31, w: W * 0.8, h: H * 0.38, align: 'center', v: 'center', dark: false, mk: 22, tp: 12 }] };
  };
  F.diamondEdge = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + P([[W * 0.78, H * 0.5 - H * 0.62], [W * 0.78 + H * 0.62, H * 0.5], [W * 0.78, H * 0.5 + H * 0.62], [W * 0.78 - H * 0.62, H * 0.5]], t.a) + P([[W * 0.78, H * 0.5 - H * 0.36], [W * 0.78 + H * 0.36, H * 0.5], [W * 0.78, H * 0.5 + H * 0.36], [W * 0.78 - H * 0.36, H * 0.5]], t.p),
      z: [{ x: W * 0.07, y: H * 0.14, w: W * 0.46, h: H * 0.72, align: 'left', v: 'center', dark: true }] };
  };
  F.quarter = function (t, W, H) {
    var r = H * 0.26;
    return { s: rect(0, 0, W, H, t.p) + circ(0, 0, r, t.a) + circ(W, 0, r * 0.6, t.a) + circ(0, H, r * 0.6, t.a) + circ(W, H, r, t.a) + ring(W, H, r * 1.35, t.a, 1.2, 0.6),
      z: [{ x: W * 0.2, y: H * 0.16, w: W * 0.6, h: H * 0.68, align: 'center', v: 'center', dark: true }] };
  };
  F.diagStripes = function (t, W, H) {
    var s = rect(0, 0, W, H, t.p) + P([[0, 0], [W * 0.46, 0], [W * 0.3, H], [0, H]], t.a);
    for (var i = 0; i < 4; i++) s += P([[W * (0.47 + i * 0.05), 0], [W * (0.49 + i * 0.05), 0], [W * (0.33 + i * 0.05), H], [W * (0.31 + i * 0.05), H]], t.a, 0.8 - i * 0.18);
    return { s: s, z: [{ x: W * 0.52, y: H * 0.16, w: W * 0.42, h: H * 0.68, align: 'right', v: 'center', dark: true }] };
  };
  F.texture = function (t, W, H) {
    var s = rect(0, 0, W, H, t.p), m = H * 0.08;
    for (var i = -10; i < 40; i++) s += line(i * 9, 0, i * 9 - H, H, '#FFFFFF', 0.5, 0.06);
    s += '<rect x="' + m + '" y="' + m + '" width="' + (W - 2 * m) + '" height="' + (H - 2 * m) + '" fill="none" stroke="' + t.a + '" stroke-width="1.2"/>';
    return { s: s, z: [{ x: W * 0.16, y: H * 0.16, w: W * 0.68, h: H * 0.68, align: 'center', v: 'center', dark: true }] };
  };
  F.silk = function (t, W, H) {
    var s = '<defs>' + grad('bcsk', t.p, t.s, 1, 1) + '</defs><rect width="' + W + '" height="' + H + '" fill="url(#bcsk)"/>';
    for (var i = 0; i < 4; i++) s += path('M' + W * 0.45 + ' ' + H + ' C ' + W * (0.6 + i * 0.04) + ' ' + H * (0.55 - i * 0.05) + ', ' + W * (0.78 - i * 0.02) + ' ' + H * (0.62 + i * 0.04) + ', ' + W + ' ' + H * (0.15 + i * 0.1) + ' L ' + W + ' ' + H + ' Z', i % 2 ? t.a : '#FFFFFF', i % 2 ? 0.55 : 0.08);
    return { s: s, z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.5, h: H * 0.62, align: 'left', v: 'center', dark: true }] };
  };
  F.fold = function (t, W, H) {
    var f = H * 0.34;
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[W - f, 0], [W, 0], [W, f]], t.p) + P([[W - f, 0], [W, f], [W - f * 0.88, f * 0.88]], D.mix(t.p, '#000000', 0.25)) + rect(0, 0, W * 0.025, H, t.a) + rect(0, H - H * 0.05, W * 0.6, H * 0.05, t.p),
      z: [{ x: W * 0.1, y: H * 0.16, w: W * 0.62, h: H * 0.66, align: 'left', v: 'center', dark: false }] };
  };
  F.zigzagBand = function (t, W, H) {
    var s = rect(0, 0, W, H, t.p), y = H * 0.78, n = 14, d = 'M0 ' + H + ' L0 ' + y;
    for (var i = 0; i < n; i++) d += ' L' + (W / n * (i + 0.5)) + ' ' + (y - H * 0.06) + ' L' + (W / n * (i + 1)) + ' ' + y;
    s += path(d + ' L' + W + ' ' + H + ' Z', t.a);
    return { s: s, z: [{ x: W * 0.1, y: H * 0.1, w: W * 0.8, h: H * 0.56, align: 'center', v: 'center', dark: true }] };
  };
  Bk.qrStrip = function (t, W, H) {
    return { s: rect(0, 0, W, H, t.p) + rect(W * 0.7, 0, W * 0.3, H, t.a) + rect(W * 0.07, H * 0.88, W * 0.16, 2, t.a),
      z: [{ x: W * 0.07, y: H * 0.1, w: W * 0.6, h: H * 0.76, align: 'left', v: 'center', dark: true, lead: 1 },
        { tag: 'mark', x: W * 0.72, y: H * 0.06, w: W * 0.26, h: H * 0.38, align: 'center', v: 'center', dark: D.cr('#FFFFFF', t.a) > D.cr('#141414', t.a), small: 1 },
        { tag: 'qr', x: W * 0.85 - H * 0.19, y: H * 0.5, w: H * 0.38, h: H * 0.38 }] };
  };
  Bk.blobBack = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + blob(W * 0.98, H * 0.96, H * 0.46, 4, t.a, 0.85) + blob(W * 1.02, H * 1.02, H * 0.3, 6, t.p) + blob(W * 0.02, H * 0.02, H * 0.18, 9, t.s, 0.9),
      z: [{ x: W * 0.1, y: H * 0.12, w: W * 0.62, h: H * 0.76, align: 'left', v: 'center', dark: false, lead: 1 }] };
  };
  Bk.diamondBack = function (t, W, H) {
    var c = H * 0.5, r = H * 0.62;
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[-r * 0.25, c - r], [r * 0.75, c], [-r * 0.25, c + r]], t.p) + P([[r * 0.75 + 6, c], [-r * 0.25 + 6, c - r], [-r * 0.25 + 10, c - r]], t.a),
      z: [{ tag: 'mark', x: W * 0.02, y: H * 0.3, w: W * 0.2, h: H * 0.4, align: 'center', v: 'center', dark: true, small: 1 }, { x: W * 0.36, y: H * 0.1, w: W * 0.6, h: H * 0.8, align: 'left', v: 'center', dark: false, lead: 1 }], swap: 1 };
  };
  Bk.centerBandBack = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#FFFFFF') + rect(0, 0, W, H * 0.3, t.p) + rect(0, H * 0.3, W, 2, t.a) + rect(0, H - 3, W, 3, t.a),
      z: [{ tag: 'tab', x: W * 0.07, y: H * 0.04, w: W * 0.86, h: H * 0.22, align: 'left', v: 'center', dark: true, tp: 13 },
        { x: W * 0.07, y: H * 0.38, w: W * 0.6, h: H * 0.54, align: 'left', v: 'center', dark: false }, { tag: 'qr', x: W * 0.74, y: H * 0.42, w: H * 0.4, h: H * 0.4 }] };
  };
  Bk.quarterBack = function (t, W, H) {
    var r = H * 0.22;
    return { s: rect(0, 0, W, H, t.p) + circ(W, 0, r, t.a) + circ(0, H, r * 0.7, t.a) + ring(W, 0, r * 1.4, t.a, 1, 0.6),
      z: [{ x: W * 0.1, y: H * 0.12, w: W * 0.66, h: H * 0.76, align: 'left', v: 'center', dark: true, lead: 1 }] };
  };
  Bk.foldBack = function (t, W, H) {
    var f = H * 0.22;
    return { s: rect(0, 0, W, H, '#FFFFFF') + P([[0, H - f], [0, H], [f, H]], t.p) + P([[0, H - f], [f, H], [f * 0.88, H - f * 0.88]], D.mix(t.p, '#000000', 0.25)) + rect(W * 0.07, H * 0.08, W * 0.86, 2, t.a),
      z: [{ x: W * 0.12, y: H * 0.13, w: W * 0.56, h: H * 0.66, align: 'left', v: 'center', dark: false, lead: 1 }, { tag: 'qr', x: W * 0.74, y: H * 0.46, w: H * 0.4, h: H * 0.4 }, { tag: 'mark', x: W * 0.72, y: H * 0.12, w: W * 0.24, h: H * 0.3, align: 'center', v: 'center', dark: false, small: 1 }] };
  };
  Bk.mandalaBack = function (t, W, H) {
    return { s: rect(0, 0, W, H, '#14161B') + mandala(W * 1.02, H * 1.02, H * 0.6, t.a, 0.85) + rect(W * 0.08, H * 0.1, W * 0.12, 1.5, t.a),
      z: [{ x: W * 0.08, y: H * 0.14, w: W * 0.6, h: H * 0.74, align: 'left', v: 'center', dark: true, lead: 1, gold: 1, icl: 1 }] };
  };
  Bk.zigzagBack = function (t, W, H) {
    var x = W * 0.1, n = 8, d = 'M0 0 L' + x + ' 0';
    for (var i = 0; i < n; i++) d += ' L' + (x + H * 0.06) + ' ' + (H / n * (i + 0.5)) + ' L' + x + ' ' + (H / n * (i + 1));
    return { s: rect(0, 0, W, H, '#FFFFFF') + path(d + ' L0 ' + H + ' Z', t.a) + rect(0, 0, x * 0.55, H, t.p),
      z: [{ x: W * 0.22, y: H * 0.1, w: W * 0.5, h: H * 0.8, align: 'left', v: 'center', dark: false, lead: 1 }, { tag: 'qr', x: W * 0.76, y: H * 0.3, w: H * 0.4, h: H * 0.4 }] };
  };
  /* logo on colour, for the "details + logo" layout */
  Bk.logoColour = function (t, W, H, k) {
    var s = rect(0, 0, W, H, k % 2 ? t.p : t.s);
    if (k % 3 === 0) s += circ(W * 0.88, H * 0.12, H * 0.42, '#FFFFFF', 0.08) + circ(W * 0.08, H * 0.95, H * 0.3, '#FFFFFF', 0.06);
    if (k % 3 === 1) s += rect(0, H - H * 0.06, W, H * 0.06, t.a) + stripesP(W * 0.72, 0, W * 0.28, H, t);
    if (k % 3 === 2) s += P([[0, 0], [W * 0.18, 0], [0, H * 0.34]], t.a) + P([[W, H], [W * 0.82, H], [W, H * 0.66]], t.a);
    return { s: s, z: [{ x: W * 0.15, y: H * 0.16, w: W * 0.7, h: H * 0.68, align: 'center', v: 'center', dark: true }] };
  };
  function stripesP(x, y, w, h, t) { var s = ''; for (var i = 0; i < 6; i++) s += P([[x + i * w / 6, y + h], [x + i * w / 6 + w / 14, y + h], [x + i * w / 6 + w / 14 + h * 0.5, y], [x + i * w / 6 + h * 0.5, y]], '#FFFFFF', 0.07); return '<svg x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" overflow="hidden"><g transform="translate(' + (-x) + ',' + (-y) + ')">' + s + '</g></svg>'; }

  function Zs(list, sz) {
    return list.map(function (z) {
      var o = { x: z.x, y: z.y, w: z.w, h: z.h, align: z.align || 'left', v: z.v || 'center', dark: z.dark, k: 1, tag: z.tag || (z.small ? 'mark' : 'main'), cls: 'bc' + (z.dark ? ' dark' : '') + (z.lead ? ' lead' : '') + (z.icl ? ' icl' : '') + (z.ond ? ' ond' : '') };
      o.titlePt = z.small ? 9 : z.tp || 15; if (z.small) o.auto = 'mark';
      o.vars = '--mk:' + (z.small ? 24 : z.mk || 34) + 'pt;' + (z.gold ? '--mkr:4px;' : '') + (z.icb ? '--icb:' + z.icb + ';' : '');
      if (o.tag === 'photo') { o.cls = 'bcph'; o.vars = '--zh:' + Math.round(z.h) + 'px;' + (z.clip ? '--clip:' + z.clip + ';' : ''); o.v = 'top'; }
      if (o.tag === 'qr') { o.cls = 'bcqr'; o.align = 'center'; o.vars = '--qr:' + Math.round(Math.min(z.w, z.h)) + 'px;'; }
      return o;
    });
  }
  /* role -> [page, zone] for each layout */
  function at(pages, p, tag) { var zs = pages[p] && pages[p].zones || []; for (var i = 0; i < zs.length; i++) if (zs[i].tag === tag) return [p, i]; return null; }
  D.PANEL.bizcard2 = function (doc, t, W, H, dz, sz) {
    var f = F[dz.front] ? F[dz.front](t, W, H) : null, b = Bk[dz.backd] ? Bk[dz.backd](t, W, H, dz.vi || 0) : null, pages = [], roles = {};
    var det = function (p) {          /* name, title, contacts, QR and photo on the details side (page p) */
      var main = at(pages, p, 'main'), tab = at(pages, p, 'tab');
      if (main && !tab && !at(pages, p, 'mark') && !(doc.logo && doc.logo.bc && doc.logo.bc.back === 0)) pages[p].zones[main[1]].lgTop = 1;   /* an uploaded logo also shows small, above the name, on the details side */
      roles.name = tab || main; roles.title = tab || main; roles.contact = main;
      roles.qr = at(pages, p, 'qr'); if (!roles.photo) roles.photo = at(pages, p, 'photo');
    };
    if (dz.bc === 'logo') {           /* front: logo; back: details */
      var fz = Zs(f.z, sz), bz = Zs(b.z, sz);
      fz[0].titlePt = 14; if (fz[1] && fz[1].tag === 'main') fz[1].titlePt = 13;
      pages.push({ svg: svg(W, H, f.s), zones: fz }, { svg: svg(W, H, b.s), zones: bz });
      var two = fz[1] && fz[1].tag === 'main' ? [0, 1] : [0, 0];
      roles = { logo: [0, 0], company: two, tagline: two, photo: at(pages, 0, 'photo') };
      det(1);
    } else if (dz.bc === 'details') { /* front: name and contacts; back: logo on colour */
      var dzs = Zs(b.z, sz), LC = Bk.logoColour(t, W, H, dz.vi || 0), lz = Zs(LC.z, sz);
      lz[0].titlePt = 14;
      pages.push({ svg: svg(W, H, b.s), zones: dzs }, { svg: svg(W, H, LC.s), zones: lz });
      roles = { logo: [1, 0], company: [1, 0], tagline: [1, 0] };
      det(0);
    } else {                          /* one side: logo and company in one zone, person and contacts in the other */
      var o1 = Zs(b.z, sz), mi = o1.map(function (z) { return z.tag; }).indexOf('main');
      if (o1.filter(function (z) { return z.tag === 'mark'; }).length === 0) {
        var z0 = o1[mi], wl = z0.w * (o1.length > 1 ? 1 : 0.6);
        var side = { x: z0.x + z0.w * 0.66, y: z0.y, w: z0.w * 0.34, h: z0.h, align: 'center', v: 'center', dark: z0.dark, k: 1, tag: 'mark', cls: z0.cls.replace(' lead', ''), titlePt: 10, vars: '--mk:28pt;' };
        if (o1.length === 1) { z0.w = wl; o1.push(side); }
        else { side.x = z0.x; side.y = z0.y; side.w = z0.w * 0.34; side.align = 'left'; z0.x += z0.w * 0.38; z0.w *= 0.62; o1.push(side); }
      }
      o1.forEach(function (z) { if (z.tag === 'mark') z.titlePt = 10; if (z.tag === 'main') z.titlePt = 13.5; });
      pages.push({ svg: svg(W, H, b.s), zones: o1 });
      var mk = at(pages, 0, 'mark');
      roles = { logo: mk, company: mk, tagline: mk };
      det(0);
    }
    return { pages: pages, roles: roles, ownLogo: true };
  };

  /* palettes for cards */
  function cp(id, name, p, s, a, head, body) { var ink = D.mix(p, '#15171C', 0.8), x = th(id, name, p, s, a, ink, D.mix(ink, '#FFFFFF', 0.38), D.mix(p, '#FFFFFF', 0.94), D.mix(p, '#FFFFFF', 0.82), head, body); x.hide = 1; return x; }
  var CP = [
    cp('bc01', 'Navy Gold', '#10213F', '#1C3561', '#E8A93B', 'Montserrat', 'Aptos'), cp('bc02', 'Black Orange', '#1B1D22', '#33363D', '#F28C28', 'Bahnschrift', 'Aptos'),
    cp('bc03', 'Teal Coral', '#0D4D57', '#166A75', '#F27059', 'Century Gothic', 'Calibri'), cp('bc04', 'Royal Blue', '#0F2F7A', '#1D4ED8', '#38BDF8', 'Segoe UI', 'Segoe UI'),
    cp('bc05', 'Forest Gold', '#123524', '#1E5A3C', '#D4A72C', 'Georgia', 'Calibri'), cp('bc06', 'Wine Rose', '#4C1130', '#7A1F4B', '#F4A3B5', 'Constantia', 'Corbel'),
    cp('bc07', 'Charcoal Lime', '#23272E', '#3A404A', '#A3D139', 'Franklin Gothic Medium', 'Aptos'), cp('bc08', 'Black Gold', '#14161B', '#2A2D34', '#C9A14A', 'Bodoni MT', 'Gill Sans MT'),
    cp('bc09', 'Purple Amber', '#2E1A5E', '#4C2C96', '#FFB020', 'Aptos Display', 'Aptos'), cp('bc10', 'Slate Red', '#1F2933', '#334454', '#E23D3D', 'Arial Black', 'Arial'),
    cp('bc11', 'Ocean Sun', '#073B4C', '#0B5A73', '#FFD166', 'Trebuchet MS', 'Calibri'), cp('bc12', 'Blue Orange', '#12355B', '#1F5288', '#FF8C42', 'Calibri Light', 'Calibri'),
    cp('bc13', 'Black Yellow', '#111111', '#2B2B2B', '#FFC300', 'Bahnschrift', 'Aptos'), cp('bc14', 'Violet Pink', '#3A0CA3', '#7209B7', '#F72585', 'Century Gothic', 'Century Gothic'),
    cp('bc15', 'Leaf Green', '#1B4332', '#2D6A4F', '#74C69D', 'Gill Sans MT', 'Gill Sans MT'), cp('bc16', 'Deep Teal Orange', '#0B3C49', '#145A6B', '#FF7F3F', 'Segoe UI', 'Segoe UI'),
    cp('bc17', 'Maroon Gold', '#5E0B15', '#90323D', '#D9B44A', 'Garamond', 'Calibri'), cp('bc18', 'Ink Sky', '#0B2545', '#13315C', '#8DA9C4', 'Aptos Display', 'Aptos')
  ];
  CP.forEach(function (x) { D.THEMES.push(x); });
  /* drop the first card collection; add the new one */
  D.DESIGNS = D.DESIGNS.filter(function (d) { return d.types.indexOf('business_card') < 0; });
  var LIST = [
    ['logo', 'swoosh', 'sideCurve', 'Summit', 0], ['logo', 'angle', 'topBar', 'Vector', 3], ['logo', 'gold', 'goldBack', 'Regal', 7], ['logo', 'chevron', 'darkPanel', 'Arrow', 1],
    ['logo', 'circles', 'cornerTri', 'Orbit', 5], ['logo', 'split', 'cleanLine', 'Duo', 11], ['logo', 'wave', 'waveSide', 'Tide', 2], ['logo', 'hexa', 'stripeBottom', 'Hive', 6],
    ['logo', 'ribbon', 'sideCurve', 'Ribbon', 9], ['logo', 'swoosh', 'darkPanel', 'Velocity', 10],
    ['details', null, 'sideCurve', 'Crescent', 4], ['details', null, 'topBar', 'Ledger', 8], ['details', null, 'cornerTri', 'Apex', 3], ['details', null, 'waveSide', 'Flow', 0],
    ['details', null, 'stripeBottom', 'Base', 11], ['details', null, 'goldBack', 'Noir', 7],
    ['one', null, 'darkPanel', 'Solo', 1], ['one', null, 'sideCurve', 'Single', 2], ['one', null, 'cleanLine', 'Minimal', 9], ['one', null, 'waveSide', 'Brook', 5],
    ['logo', 'brackets', 'orangePanel', 'Frame', 2], ['logo', 'notch', 'notchBack', 'Notch', 0], ['logo', 'bandFoot', 'footWeb', 'Harbor', 11], ['logo', 'goldSwirl', 'goldLines', 'Luxe', 7],
    ['logo', 'scallop', 'nameTab', 'Scallop', 6], ['logo', 'leaf', 'leftBar', 'Leaf', 4], ['logo', 'geoTri', 'cleanQR', 'Prism', 9], ['logo', 'doubleWave', 'waveBottom2', 'Ripple', 3],
    ['logo', 'photoHalf', 'nameTab', 'Portrait', 1], ['logo', 'photoCircle', 'footWeb', 'Profile', 10],
    ['details', null, 'nameTab', 'Tag', 5], ['details', null, 'footWeb', 'Footer', 8], ['details', null, 'leftBar', 'Pillar', 0], ['details', null, 'photoSide', 'Photo ID', 11],
    ['one', null, 'panelQR', 'Scan', 3], ['one', null, 'photoMark', 'Face', 2],
    ['logo', 'mandala', 'mandalaBack', 'Mandala', 7], ['logo', 'blobs', 'blobBack', 'Organic', 14], ['logo', 'shards', 'foldBack', 'Mosaic', 11], ['logo', 'centerBand', 'centerBandBack', 'Banner', 9],
    ['logo', 'diamondEdge', 'diamondBack', 'Diamond', 12], ['logo', 'quarter', 'qrStrip', 'Quarter', 15], ['logo', 'diagStripes', 'zigzagBack', 'Stripe', 12], ['logo', 'texture', 'quarterBack', 'Linen', 16],
    ['logo', 'silk', 'blobBack', 'Silk', 13], ['logo', 'fold', 'foldBack', 'Fold', 17], ['logo', 'zigzagBand', 'zigzagBack', 'Zigzag', 10], ['logo', 'mandalaColor', 'quarterBack', 'Royal', 16],
    ['details', null, 'mandalaBack', 'Ornate', 16], ['details', null, 'qrStrip', 'Strip', 15], ['details', null, 'centerBandBack', 'Header', 5], ['details', null, 'diamondBack', 'Facet', 3],
    ['details', null, 'quarterBack', 'Corner', 2], ['one', null, 'qrStrip', 'Compact', 1], ['one', null, 'foldBack', 'Folded', 17]
  ];
  var DESC = { logo: 'Logo side + details side', details: 'Details side + logo side', one: 'One side, all details' }, EXTRA = { orangePanel: ' · QR code', footWeb: ' · QR code', goldLines: ' · QR code', nameTab: ' · QR code', cleanQR: ' · QR code', waveBottom2: ' · QR code', photoSide: ' · photo', panelQR: ' · QR code', photoMark: ' · photo', qrStrip: ' · QR code', centerBandBack: ' · QR code', foldBack: ' · QR code', zigzagBack: ' · QR code', photoHalf: ' · photo', photoCircle: ' · photo' };
  LIST.forEach(function (x, i) {
    D.DESIGNS.push(des('ma-businesscard-' + (i + 1), x[3], 'panels', ['business_card'], CP[x[4]].id, {
      layout: 'bizcard2', bc: x[0], front: x[1], backd: x[2], vi: i, coll: 'marketing', desc: DESC[x[0]] + (EXTRA[x[1]] || EXTRA[x[2]] || '') + ' · ' + CP[x[4]].name
    }));
  });

  /* card sample text, with roles */
  var sample1 = D.sample;
  D.sample = function (type) {
    var doc = sample1(type);
    if (type !== 'business_card') return doc;
    var R = function (t, o, role) { var b = D.B(t, o); b.role = role; return b; };
    doc.title = 'Business card'; doc.org = 'Your Company';
    doc.blocks = [R('s_mark', { text: '' }, 'logo'), R('s_title', { text: 'Your Company' }, 'company'), R('s_sub', { text: 'Your tagline here' }, 'tagline'),
      R('s_title', { text: 'Your Name' }, 'name'), R('s_sub', { text: 'Job Title' }, 'title'),
      R('s_contact', { rows: [{ i: 'phone', t: '+00 000 000 0000' }, { i: 'mail', t: 'name@yourcompany.com' }, { i: 'web', t: 'www.yourcompany.com' }, { i: 'pin', t: 'Your address, City' }] }, 'contact'),
      R('s_qr', { text: 'https://www.yourcompany.com' }, 'qr'), R('s_photo', { src: '' }, 'photo')];
    return doc;
  };
})();

window.DSDOC=D;})();

DSJS;
}
add_action( 'init', function () {
	if ( ! isset( $_GET['ds_doc_asset'] ) || 'engine' !== $_GET['ds_doc_asset'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	do_action( 'litespeed_control_set_ttl', 86400 );
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Access-Control-Allow-Origin: *' );
	echo ds_doc_engine_js(); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}, 1 );
endif;
