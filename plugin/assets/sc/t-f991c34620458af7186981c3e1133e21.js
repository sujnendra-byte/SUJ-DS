(function(){
if (window.DS_DOCIMPORT) return;
var DI = window.DS_DOCIMPORT = {};
var CFG = window.DS_DI_CFG || {};
var MAX_SLIDES = 20, MAX_PDF_PAGES = 80, MAX_OCR_PAGES = 20, IMG_MAX = 1400;
var LIBS = {
  pdf: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
  pdfWorker: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js',
  zip: 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js'
};
function $(id){ return document.getElementById(id); }
function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function clean(s){ return String(s == null ? '' : s).replace(/ /g, ' ').replace(/[​﻿]/g, '').replace(/\s+/g, ' ').trim(); }
var _libs = {};
function lib(name){
  if (_libs[name]) return _libs[name];
  _libs[name] = new Promise(function(res, rej){
    if (name === 'pdf' && window.pdfjsLib) return res();
    if (name === 'zip' && window.JSZip) return res();
    var s = document.createElement('script'); s.src = LIBS[name]; s.async = true;
    s.onload = function(){ res(); }; s.onerror = function(){ _libs[name] = null; rej(new Error('Could not load the file reader. Check your connection and try again.')); };
    document.head.appendChild(s);
  });
  return _libs[name];
}
function post(url, body){
  return fetch(url, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce': window.NONCE || window.DSP_NONCE || CFG.nonce || ''}, body: JSON.stringify(body)})
    .then(function(r){ return r.json().catch(function(){ return {success:false, message:'Server error ' + r.status}; }); });
}

/* ---------- images ---------- */
function blobToImg(blob){
  return new Promise(function(res){
    var u = URL.createObjectURL(blob), im = new Image();
    im.onload = function(){ res(im); }; im.onerror = function(){ URL.revokeObjectURL(u); res(null); }; im.src = u;
  });
}
function toJpeg(src, w, h){
  var sc = Math.min(1, IMG_MAX / Math.max(w, h)), c = document.createElement('canvas');
  c.width = Math.max(1, Math.round(w*sc)); c.height = Math.max(1, Math.round(h*sc));
  var x = c.getContext('2d'); x.fillStyle = '#FFFFFF'; x.fillRect(0, 0, c.width, c.height);
  x.drawImage(src, 0, 0, c.width, c.height);
  return c.toDataURL('image/jpeg', 0.84);
}
async function blobImage(blob){
  var im = await blobToImg(blob); if (!im) return null;
  if (im.naturalWidth < 160 || im.naturalHeight < 120) return null;
  return {data: toJpeg(im, im.naturalWidth, im.naturalHeight), w: im.naturalWidth, h: im.naturalHeight};
}
var IMG_EXT = {png:'image/png', jpg:'image/jpeg', jpeg:'image/jpeg', gif:'image/gif', bmp:'image/bmp', webp:'image/webp'};

/* ---------- text → blocks (markdown-ish, used for txt/md and OCR) ---------- */
function mdBlocks(text, out){
  out = out || [];
  var para = [];
  function flush(){ if (para.length) { out.push({k:'p', t: clean(para.join(' '))}); para = []; } }
  String(text || '').replace(/\r/g, '').split('\n').forEach(function(line){
    var l = line.trim();
    if (!l) { flush(); return; }
    var m;
    if ((m = l.match(/^(#{1,4})\s+(.+)$/))) { flush(); out.push({k:'h', lvl: Math.min(3, m[1].length), t: clean(m[2].replace(/[*_]+/g, ''))}); return; }
    if ((m = l.match(/^(?:[-*•●▪◦►✓]|\d{1,2}[.)])\s+(.+)$/))) { flush(); out.push({k:'li', t: clean(m[1].replace(/\*\*/g, ''))}); return; }
    if (/^\|.*\|$/.test(l)) { flush(); if (!/^\|[\s:|-]+\|$/.test(l)) out.push({k:'li', t: clean(l.replace(/^\||\|$/g, '').split('|').map(function(c){ return c.trim(); }).filter(Boolean).join(' | '))}); return; }
    para.push(l.replace(/\*\*/g, ''));
  });
  flush();
  return out;
}

/* ---------- PDF ---------- */
function objGet(page, name){
  return new Promise(function(res){
    var done = false, t = setTimeout(function(){ if (!done) { done = true; res(null); } }, 2500);
    try {
      var store = String(name).indexOf('g_') === 0 ? page.commonObjs : page.objs;
      store.get(name, function(o){ if (!done) { done = true; clearTimeout(t); res(o); } });
    } catch(e) { if (!done) { done = true; clearTimeout(t); res(null); } }
  });
}
function imgObjToJpeg(o){
  if (!o) return null;
  var w = o.width, h = o.height; if (!w || !h || w < 200 || h < 150) return null;
  var c = document.createElement('canvas'); c.width = w; c.height = h; var x = c.getContext('2d');
  if (o.bitmap) x.drawImage(o.bitmap, 0, 0);
  else if (o.data) {
    var id = x.createImageData(w, h), d = id.data, s = o.data, n = w*h;
    if (o.kind === 3 || s.length === n*4) d.set(s.subarray ? s.subarray(0, n*4) : s);
    else if (o.kind === 2 || s.length === n*3) { for (var i = 0, j = 0; i < n; i++, j += 3) { d[i*4] = s[j]; d[i*4+1] = s[j+1]; d[i*4+2] = s[j+2]; d[i*4+3] = 255; } }
    else return null;
    x.putImageData(id, 0, 0);
  } else return null;
  return {data: toJpeg(c, w, h), w: w, h: h};
}
async function pdfPageImage(pdfjs, page){
  try {
    var ops = await page.getOperatorList(), best = null, OPS = pdfjs.OPS;
    for (var i = 0; i < ops.fnArray.length; i++) {
      var fn = ops.fnArray[i];
      if (fn !== OPS.paintImageXObject && fn !== OPS.paintJpegXObject && fn !== OPS.paintInlineImageXObject) continue;
      var a = ops.argsArray[i], o = fn === OPS.paintInlineImageXObject ? a[0] : await objGet(page, a[0]);
      if (o && o.width && o.height && (!best || o.width*o.height > best.width*best.height)) best = o;
    }
    return imgObjToJpeg(best);
  } catch(e) { return null; }
}
async function pdfPageRender(page){
  var v0 = page.getViewport({scale:1}), sc = Math.min(2.2, 1500 / Math.max(v0.width, v0.height)), v = page.getViewport({scale: sc});
  var c = document.createElement('canvas'); c.width = Math.round(v.width); c.height = Math.round(v.height);
  var x = c.getContext('2d'); x.fillStyle = '#FFFFFF'; x.fillRect(0, 0, c.width, c.height);
  await page.render({canvasContext: x, viewport: v}).promise;
  return c.toDataURL('image/jpeg', 0.8);
}
async function readPdf(file, prog, info){
  await lib('pdf');
  var pdfjs = window.pdfjsLib; pdfjs.GlobalWorkerOptions.workerSrc = LIBS.pdfWorker;
  var doc = await pdfjs.getDocument({data: new Uint8Array(await file.arrayBuffer())}).promise;
  var N = Math.min(doc.numPages, MAX_PDF_PAGES), pages = [], sizes = [], ocrUsed = 0;
  info.pages = doc.numPages; if (doc.numPages > N) info.notes.push('Only the first ' + N + ' of ' + doc.numPages + ' pages were read.');
  for (var p = 1; p <= N; p++) {
    prog('Reading page ' + p + ' of ' + N + '…');
    var page = await doc.getPage(p), tc = await page.getTextContent();
    var items = tc.items.filter(function(it){ return it.str && it.str.trim(); }).map(function(it){
      var tr = it.transform; return {s: it.str, x: tr[4], y: tr[5], h: Math.abs(tr[3]) || Math.hypot(tr[2], tr[3]) || it.height || 10, w: it.width || 0};
    });
    var chars = items.reduce(function(a, it){ return a + it.s.trim().length; }, 0);
    var pg = {lines: [], img: null, ocr: null};
    if (chars < 25) {
      if (ocrUsed < MAX_OCR_PAGES && CFG.ocr) {
        prog('Page ' + p + ' is a scan — reading it with AI…');
        var shot = await pdfPageRender(page), r = await post(CFG.ocr, {image: shot});
        ocrUsed++;
        if (r && r.success && r.text) pg.ocr = r.text;
        else info.notes.push('Page ' + p + ' is a scan and could not be read' + (r && r.message ? ' (' + r.message + ')' : '') + '.');
      } else if (chars === 0) info.notes.push('Page ' + p + ' has no text.');
    } else {
      items.sort(function(a, b){ return Math.abs(b.y - a.y) > Math.min(a.h, b.h)*0.5 ? b.y - a.y : a.x - b.x; });
      var cur = null;
      items.forEach(function(it){
        if (cur && Math.abs(cur.y - it.y) <= Math.max(2, Math.min(cur.h, it.h)*0.5)) {
          var gap = it.x - cur.xe; cur.t += (gap > cur.h*0.15 && !/\s$/.test(cur.t) && !/^\s/.test(it.s) ? ' ' : '') + it.s; cur.xe = it.x + it.w; cur.h = Math.max(cur.h, it.h);
        } else { cur = {t: it.s, y: it.y, h: it.h, xe: it.x + it.w, x: it.x}; pg.lines.push(cur); }
      });
      pg.lines.forEach(function(l){ l.t = clean(l.t); for (var k = 0; k < l.t.length; k++) sizes.push(Math.round(l.h*2)/2); });
      pg.img = await pdfPageImage(pdfjs, page);
    }
    pages.push(pg);
  }
  info.ocr = ocrUsed;
  /* body text size = most common size by characters */
  var cnt = {}, body = 10, bestN = 0; sizes.forEach(function(s){ cnt[s] = (cnt[s] || 0) + 1; }); for (var s in cnt) if (cnt[s] > bestN) { bestN = cnt[s]; body = +s; }
  var blocks = [];
  pages.forEach(function(pg, pi){
    blocks.push({k:'brk', page: pi + 1});
    if (pg.ocr) { mdBlocks(pg.ocr, blocks); return; }
    var prev = null, imgDone = false;
    pg.lines.forEach(function(l){
      if (!l.t || /^\d{1,3}$/.test(l.t)) return;                         /* page numbers */
      var ratio = l.h / body, m;
      if (ratio >= 1.18 && l.t.length <= 140 && !/[.;,]$/.test(l.t)) {
        var lvl = ratio >= 1.75 ? 1 : ratio >= 1.4 ? 2 : 3;
        if (prev && prev.k === 'h' && prev.lvl === lvl && prev.y - l.y < l.h*1.7) prev.t = clean(prev.t + ' ' + l.t);  /* heading on two lines */
        else { prev = {k:'h', lvl: lvl, t: l.t, y: l.y}; blocks.push(prev); }
        if (pg.img && !imgDone) { blocks.push({k:'img', img: pg.img}); imgDone = true; }
        return;
      }
      if ((m = l.t.match(/^(?:[•●▪◦►✓\-–*]|\d{1,2}[.)])\s*(.+)$/))) { prev = {k:'li', t: m[1], y: l.y}; blocks.push(prev); return; }
      if (prev && (prev.k === 'p' || prev.k === 'li') && prev.y - l.y < l.h*1.9 && !/[.!?:]$/.test(prev.t) || (prev && prev.k === 'p' && prev.y - l.y < l.h*1.5)) { prev.t = clean(prev.t + ' ' + l.t); prev.y = l.y; return; }
      prev = {k:'p', t: l.t, y: l.y}; blocks.push(prev);
    });
    if (pg.img && !imgDone) blocks.push({k:'img', img: pg.img});
  });
  return blocks;
}

/* ---------- Word (.docx) ---------- */
function kids(el, name){ var o = []; for (var i = 0; i < el.childNodes.length; i++) { var c = el.childNodes[i]; if (c.nodeName === name) o.push(c); } return o; }
function first(el, name){ var l = el.getElementsByTagName(name); return l.length ? l[0] : null; }
function attr(el, name){ return el ? (el.getAttribute(name) || '') : ''; }
function xml(str){ return new DOMParser().parseFromString(str, 'application/xml'); }
function relMap(str){ var m = {}; if (!str) return m; var d = xml(str), rs = d.getElementsByTagName('Relationship'); for (var i = 0; i < rs.length; i++) m[rs[i].getAttribute('Id')] = rs[i].getAttribute('Target'); return m; }
function joinPath(base, target){
  if (/^\//.test(target)) return target.slice(1);
  var parts = base.split('/'); parts.pop();
  target.split('/').forEach(function(p){ if (p === '..') parts.pop(); else if (p !== '.') parts.push(p); });
  return parts.join('/');
}
async function zipImage(zip, path){
  var ext = (path.split('.').pop() || '').toLowerCase(); if (!IMG_EXT[ext]) return null;
  var f = zip.file(path); if (!f) return null;
  var bytes = await f.async('uint8array'); return blobImage(new Blob([bytes], {type: IMG_EXT[ext]}));
}
function wText(p){
  var t = '', all = p.getElementsByTagName('*');
  for (var i = 0; i < all.length; i++) {
    var n = all[i].nodeName;
    if (n === 'w:t') t += all[i].textContent; else if (n === 'w:tab') t += ' '; else if (n === 'w:br' || n === 'w:cr') t += ' ';
  }
  return clean(t);
}
async function readDocx(file, prog){
  await lib('zip');
  prog('Opening the Word file…');
  var zip = await window.JSZip.loadAsync(await file.arrayBuffer());
  var main = zip.file('word/document.xml'); if (!main) throw new Error('This does not look like a Word .docx file.');
  var doc = xml(await main.async('string'));
  var rels = relMap(zip.file('word/_rels/document.xml.rels') ? await zip.file('word/_rels/document.xml.rels').async('string') : '');
  var styleLvl = {};
  if (zip.file('word/styles.xml')) {
    var sd = xml(await zip.file('word/styles.xml').async('string')), st = sd.getElementsByTagName('w:style');
    for (var i = 0; i < st.length; i++) {
      var id = st[i].getAttribute('w:styleId'), nm = attr(first(st[i], 'w:name'), 'w:val'), ol = first(st[i], 'w:outlineLvl'), m = nm.match(/heading\s*(\d)/i);
      if (/^title$/i.test(nm)) styleLvl[id] = 1; else if (/^subtitle$/i.test(nm)) styleLvl[id] = 'sub';
      else if (m) styleLvl[id] = Math.min(3, +m[1]); else if (ol) styleLvl[id] = Math.min(3, +attr(ol, 'w:val') + 1);
    }
  }
  var body = first(doc, 'w:body'), blocks = [], n = 0, all = body ? body.childNodes : [];
  for (var k = 0; k < all.length; k++) {
    var el = all[k];
    if (el.nodeName === 'w:tbl') {
      kids(el, 'w:tr').forEach(function(tr){
        var cells = kids(tr, 'w:tc').map(function(tc){ return wText(tc); }).filter(Boolean);
        if (cells.length) blocks.push({k:'li', t: cells.join(' | ')});
      });
      continue;
    }
    if (el.nodeName !== 'w:p') continue;
    if (++n % 40 === 0) prog('Reading paragraph ' + n + '…');
    var pPr = first(el, 'w:pPr'), sid = pPr ? attr(first(pPr, 'w:pStyle'), 'w:val') : '', lvl = styleLvl[sid];
    var ol2 = pPr ? first(pPr, 'w:outlineLvl') : null; if (!lvl && ol2) lvl = Math.min(3, +attr(ol2, 'w:val') + 1);
    var t = wText(el);
    var blips = el.getElementsByTagName('a:blip');
    for (var b = 0; b < blips.length; b++) {
      var rid = blips[b].getAttribute('r:embed'); if (!rid || !rels[rid]) continue;
      var im = await zipImage(zip, joinPath('word/document.xml', rels[rid])); if (im) blocks.push({k:'img', img: im});
    }
    if (!t) continue;
    if (lvl === 'sub') { blocks.push({k:'sub', t: t}); continue; }
    if (lvl) { blocks.push({k:'h', lvl: lvl, t: t}); continue; }
    if (pPr && first(pPr, 'w:numPr')) { blocks.push({k:'li', t: t}); continue; }
    var runs = el.getElementsByTagName('w:r'), bold = runs.length > 0;
    for (var r = 0; r < runs.length && bold; r++) { var rp = first(runs[r], 'w:rPr'), bb = rp ? first(rp, 'w:b') : null; if (runs[r].getElementsByTagName('w:t').length && (!bb || attr(bb, 'w:val') === '0' || attr(bb, 'w:val') === 'false')) bold = false; }
    if (bold && t.length <= 90 && !/[.,;]$/.test(t)) { blocks.push({k:'h', lvl: 3, t: t}); continue; }
    blocks.push({k:'p', t: t});
  }
  return blocks;
}

/* ---------- PowerPoint (.pptx) ---------- */
function aText(p){ var ts = p.getElementsByTagName('a:t'), o = ''; for (var i = 0; i < ts.length; i++) o += ts[i].textContent; return clean(o); }
function xfrmOf(el){
  var x = first(el, 'a:off'), e = first(el, 'a:ext');
  return {x: +attr(x, 'x') || 0, y: +attr(x, 'y') || 0, cx: +attr(e, 'cx') || 0, cy: +attr(e, 'cy') || 0};
}
function isBadge(t){ return /^[-–—•·|*]+$/.test(t) || /^(draft|confidential)$/i.test(t) || (t.length <= 4 && /^[A-Za-z]{0,3}\d{0,3}$/.test(t)); }
function isNoteFill(spPr){
  var f = kids(spPr, 'a:solidFill')[0], c = f ? first(f, 'a:srgbClr') : null, v = (attr(c, 'val') || '').toUpperCase();
  return /^(FFFF00|FFFF66|FFFF99|FFFFCC|FFFF33|FFF200|FFEB3B|FFEE58|FFF59D)$/.test(v);
}
async function readPptx(file, prog, info){
  await lib('zip');
  prog('Opening the PowerPoint file…');
  var zip = await window.JSZip.loadAsync(await file.arrayBuffer());
  var pres = zip.file('ppt/presentation.xml'); if (!pres) throw new Error('This does not look like a PowerPoint .pptx file.');
  var pd = xml(await pres.async('string')), prel = relMap(await zip.file('ppt/_rels/presentation.xml.rels').async('string'));
  var sz = first(pd, 'p:sldSz'), SW = +attr(sz, 'cx') || 12192000, SH = +attr(sz, 'cy') || 6858000;
  var ids = pd.getElementsByTagName('p:sldId'), out = [];
  info.pages = ids.length;
  for (var i = 0; i < ids.length; i++) {
    prog('Reading slide ' + (i + 1) + ' of ' + ids.length + '…');
    var path = joinPath('ppt/presentation.xml', prel[ids[i].getAttribute('r:id')] || ''), f = zip.file(path); if (!f) continue;
    var sx = xml(await f.async('string')), rp = path.replace(/slides\/(slide\d+\.xml)$/, 'slides/_rels/$1.rels'), srel = relMap(zip.file(rp) ? await zip.file(rp).async('string') : '');
    var sl = {title:'', sub:'', lines:[], img:null}, texts = [];
    var sps = sx.getElementsByTagName('p:sp');
    for (var s = 0; s < sps.length; s++) {
      var ph = first(sps[s], 'p:ph'), type = ph ? (ph.getAttribute('type') || 'body') : '';
      if (/^(sldNum|dt|ftr|hdr)$/.test(type)) continue;
      var tb = first(sps[s], 'p:txBody'); if (!tb) continue;
      var paras = kids(tb, 'a:p').map(function(p){ return {t: aText(p), lvl: +attr(first(p, 'a:pPr'), 'lvl') || 0}; }).filter(function(p){ return p.t; });
      paras = paras.filter(function(p){ return !isBadge(p.t); });
      if (!paras.length) continue;
      if (!type) {
        var spPr = first(sps[s], 'p:spPr'), xf0 = xfrmOf(spPr || sps[s]);
        if (spPr && isNoteFill(spPr)) { info.skipped = (info.skipped || 0) + 1; continue; }               /* yellow review sticky notes */
        if (xf0.cx && xf0.cy) {                                                                       /* boxes parked mostly off the slide */
          var vw = Math.max(0, Math.min(SW, xf0.x + xf0.cx) - Math.max(0, xf0.x)), vh = Math.max(0, Math.min(SH, xf0.y + xf0.cy) - Math.max(0, xf0.y));
          if (vw * vh < 0.75 * xf0.cx * xf0.cy) { info.skipped = (info.skipped || 0) + 1; continue; }
        }
        /* a small card (name / date / place) becomes one point */
        if (paras.length >= 2 && paras.length <= 5 && paras.every(function(p){ return p.t.split(' ').length <= 8; })) paras = [{t: paras.map(function(p){ return p.t.replace(/:$/, ''); }).join(' – '), lvl: 0}];
      }
      if (type === 'title' || type === 'ctrTitle') { if (!sl.title) { sl.title = paras.map(function(p){ return p.t; }).join(' '); continue; } }
      if (type === 'subTitle' && !sl.sub) { sl.sub = paras.map(function(p){ return p.t; }).join(' '); continue; }
      var xf = xfrmOf(first(sps[s], 'p:spPr') || sps[s]);
      texts.push({y: xf.y, x: xf.x, paras: paras});
    }
    var tbls = sx.getElementsByTagName('a:tbl');
    for (var tI = 0; tI < tbls.length; tI++) {
      var rows = tbls[tI].getElementsByTagName('a:tr'), ps = [];
      for (var rI = 0; rI < rows.length; rI++) { var cells = kids(rows[rI], 'a:tc').map(function(c){ return aText(c); }).filter(Boolean); if (cells.length) ps.push({t: cells.join(' | '), lvl: 0}); }
      if (ps.length) texts.push({y: 1e12, x: 0, paras: ps});
    }
    texts.sort(function(a, b){ return Math.abs(a.y - b.y) > SH * 0.04 ? a.y - b.y : a.x - b.x; });   /* rows first, then left → right */
    if (!sl.title && texts.length && texts[0].paras[0].t.length <= 100) sl.title = texts[0].paras.shift().t;
    texts.forEach(function(tx){ tx.paras.forEach(function(p){ sl.lines.push(p.t); }); });
    var pics = sx.getElementsByTagName('p:pic'), best = null;
    for (var pI = 0; pI < pics.length; pI++) {
      var bl = first(pics[pI], 'a:blip'), rid = bl && bl.getAttribute('r:embed'), xf2 = xfrmOf(first(pics[pI], 'p:spPr') || pics[pI]), ar = xf2.cx*xf2.cy/(SW*SH);
      if (!rid || !srel[rid] || ar < 0.04 || ar > 0.97) continue;
      if (!best || ar > best.ar) best = {ar: ar, path: joinPath(path, srel[rid])};
    }
    if (best) sl.img = await zipImage(zip, best.path);
    if (sl.title || sl.lines.length) out.push(sl);
  }
  return out;
}

/* ---------- read any file ---------- */
DI.read = async function(file, prog){
  var ext = (file.name.split('.').pop() || '').toLowerCase(), info = {name: file.name, kind: ext, notes: [], pages: 0, ocr: 0};
  if (ext === 'doc' || ext === 'ppt') throw new Error('Old .' + ext + ' files can\'t be read. Open it in Word/PowerPoint and save it as .' + ext + 'x, then upload again.');
  if (file.size > (CFG.maxMB || 30) * 1048576) throw new Error('The file is larger than ' + (CFG.maxMB || 30) + ' MB.');
  if (ext === 'pptx') return {info: info, slides: await readPptx(file, prog, info)};
  var blocks;
  if (ext === 'pdf') blocks = await readPdf(file, prog, info);
  else if (ext === 'docx') blocks = await readDocx(file, prog);
  else if (ext === 'txt' || ext === 'md' || ext === 'markdown' || ext === 'csv') blocks = mdBlocks(await file.text());
  else if (/^image\/(png|jpe?g|webp)$/.test(file.type)) {
    prog('Reading the picture with AI…');
    var im = await blobImage(file), r = im ? await post(CFG.ocr, {image: im.data}) : null;
    if (!r || !r.success) throw new Error((r && r.message) || 'Could not read the picture.');
    info.ocr = 1; blocks = mdBlocks(r.text);
  }
  else throw new Error('Please upload a PDF, Word (.docx), PowerPoint (.pptx) or text file.');
  return {info: info, blocks: blocks};
};

/* ---------- blocks → sections (split at headings) ---------- */
DI.sections = function(blocks, docName){
  var cnt = {1:0, 2:0, 3:0}; blocks.forEach(function(b){ if (b.k === 'h') cnt[b.lvl]++; });
  var L = cnt[1] >= 2 ? 1 : cnt[2] >= 2 ? 2 : cnt[3] >= 2 ? 3 : (cnt[1] ? 1 : cnt[2] ? 2 : cnt[3] ? 3 : 0);
  if (L && cnt[L] > 40 && L > 1 && cnt[L-1] >= 2) L = L - 1;
  var secs = [], cur = null;
  function open(h){ cur = {h: h || '', lines: [], imgs: []}; secs.push(cur); }
  var usePages = !L && blocks.some(function(b){ return b.k === 'brk'; });
  blocks.forEach(function(b){
    if (b.k === 'brk') { if (usePages && (!cur || cur.lines.length)) open(''); return; }
    if (b.k === 'h' && b.lvl <= L) { open(b.t); return; }
    if (!cur) open('');
    if (b.k === 'img') { cur.imgs.push(b.img); return; }
    if (b.k === 'h') cur.lines.push('### ' + b.t);
    else if (b.k === 'sub') { if (!cur.sub) cur.sub = b.t; else cur.lines.push(b.t); }
    else if (b.k === 'li') cur.lines.push('- ' + b.t);
    else cur.lines.push(b.t);
  });
  secs = secs.filter(function(s){ return s.h || s.lines.length || s.imgs.length; });
  if (!L && !usePages && secs.length === 1 && secs[0].lines.length > 6) {        /* no structure at all: groups of paragraphs */
    var all = secs[0].lines, imgs = secs[0].imgs; secs = [];
    for (var i = 0; i < all.length; i += 4) secs.push({h: '', lines: all.slice(i, i + 4), imgs: i === 0 ? imgs : []});
  }
  return secs;
};

/* ---------- heuristic slides (fallback when AI is not available) ---------- */
function sentences(t){ return String(t).match(/[^.!?]+[.!?]+(\s|$)|[^.!?]+$/g) || [t]; }
function pointsOf(lines){
  var pts = [];
  lines.forEach(function(l){
    if (/^### /.test(l)) { pts.push(l.slice(4) + ':'); return; }
    var t = l.replace(/^- /, '');
    if (t.split(' ').length <= 40) { pts.push(t); return; }
    var buf = ''; sentences(t).forEach(function(s){ s = s.trim(); if ((buf + ' ' + s).split(' ').length > 40 && buf) { pts.push(buf.trim()); buf = s; } else buf += ' ' + s; }); if (buf.trim()) pts.push(buf.trim());
  });
  /* "Label:" followed by its text → one "Label: text" point */
  var out = []; for (var i = 0; i < pts.length; i++) { if (/:$/.test(pts[i]) && pts[i+1] && !/:$/.test(pts[i+1])) { out.push(pts[i] + ' ' + pts[i+1]); i++; } else if (!/:$/.test(pts[i])) out.push(pts[i]); }
  return out;
}
DI.heuristic = function(secs, max, docTitle){
  var slides = secs.map(function(s, i){ return {title: s.h || (i ? '' : docTitle), subtitle: s.sub || '', points: pointsOf(s.lines), sections: [i]}; });
  slides.forEach(function(s, i){ if (!s.title) s.title = s.points.length && s.points[0].length <= 70 ? s.points.shift().replace(/:$/, '') : (slides[i-1] ? slides[i-1].title : docTitle); });
  /* long slides → continue on the next slide (max 6 points) */
  var out = [];
  slides.forEach(function(s){ if (s.points.length <= 6) { out.push(s); return; } for (var k = 0; k < s.points.length; k += 6) out.push({title: s.title, subtitle: k ? '' : s.subtitle, points: s.points.slice(k, k + 6), sections: s.sections}); });
  var cover = {title: docTitle, subtitle: '', points: [], sections: []};
  if (out.length && !out[0].points.length) cover = out.shift();
  while (out.length > max - 1 && out.length > 1) {                      /* merge the smallest neighbours */
    var bi = 0, bl = 1e9;
    for (var j = 0; j < out.length - 1; j++) { var l = out[j].points.length + out[j+1].points.length; if (l < bl) { bl = l; bi = j; } }
    var a = out[bi], b = out[bi+1];
    out.splice(bi, 2, {title: a.title, subtitle: a.subtitle, points: a.points.concat(b.title && b.title !== a.title ? [b.title + ':'] : []).concat(b.points).slice(0, 8), sections: a.sections.concat(b.sections)});
  }
  return [cover].concat(out).slice(0, max);
};

/* ---------- slides → deck (uses Preserve mode's own parser) ---------- */
DI.toDeck = function(slides, secs){
  var used = {};
  /* Preserve mode's parser picks the slide type and icons; the words themselves are put back exactly */
  var deck = slides.map(function(s, i){
    var title = clean(s.title || 'Slide ' + (i + 1)), sub = clean(s.subtitle || ''), pts = (s.points || []).map(clean).filter(Boolean);
    var d = null;
    if (window.dspPreserveParse) try {
      var txt = 'Slide ' + (i + 1) + ': ' + title + '\n' + (sub || '\u200b') + (pts.length ? '\n' + pts.map(function(p){ return '- ' + p; }).join('\n') : '');
      var got = window.dspPreserveParse(i ? 'Slide 1: Cover\n\n' + txt : txt);
      d = got[i ? 1 : 0] || null;
    } catch(e) { d = null; }
    if (!d) d = {type: i ? 'bullet' : 'title', image_query: title, icon: '', icons: []};
    d.title = title; d.subtitle = sub; d.content = pts;
    if (d.type === 'stats') {                                                        /* big-number layout only when the points really are figures */
      var numeric = pts.filter(function(p){ return /^\s*[~≈<>+\-]?[$₹€£]?\d/.test(p) || /^[^:]{1,40}:\s*[~≈<>+\-]?[$₹€£]?\d/.test(p); }).length;
      if (!pts.length || numeric < pts.length * 0.6 || pts.some(function(p){ return p.split(' ').length > 30; })) d.type = 'bullet';
    }
    if (i === 0 && !pts.length) d.type = 'title';
    ['icons', 'emojis'].forEach(function(k){
      var a = d[k]; if (!a || !a.length || !pts.length) return;
      var o = []; for (var j = 0; j < pts.length; j++) o.push(a[j % a.length]); d[k] = o;
    });
    return d;
  });
  deck.forEach(function(d, i){
    var s = slides[i]; if (!s) return;
    if (s.img) { d.image_data = s.img.data; return; }
    var ss = s.sections || [];
    for (var k = 0; k < ss.length; k++) { var sec = secs && secs[ss[k]]; if (!sec) continue; for (var m = 0; m < sec.imgs.length; m++) { var key = ss[k] + ':' + m; if (!used[key]) { used[key] = 1; d.image_data = sec.imgs[m].data; return; } } }
  });
  return deck;
};

/* ============================================================
   UI — the file is ATTACHED to the prompt (no popup). Nothing happens until the
   user presses Build: then the normal flow runs — review your slides → Generate.
   ============================================================ */
var CSS = ''
+ '#dsdi-tip{margin-left:10px;padding:3px 10px;border-radius:999px;border:1px dashed rgba(138,180,248,.5);background:transparent;color:#8ab4f8;font:600 12px/1.4 inherit;font-family:inherit;cursor:pointer;text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsdi-tip:hover{border-color:#8ab4f8;background:rgba(138,180,248,.08);}'
+ '#dsp-pbox.dsdi-drag{border-color:#8ab4f8!important;box-shadow:0 0 0 4px rgba(138,180,248,.25)!important;}'
+ '#dsdi-chip{display:inline-flex;align-items:center;gap:7px;max-width:calc(100% - 32px);box-sizing:border-box;margin:2px 16px 4px;padding:6px 8px 6px 10px;border-radius:9px;border:1px solid #2f3a52;background:#141a26;color:#dbe4f3;font:500 12.5px/1.3 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsdi-chip b{font-weight:700;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}'
+ '#dsdi-chip i{font-style:normal;color:#8b97ad;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;}'
+ '#dsdi-chip.ok i{color:#6ee7b7;}#dsdi-chip.err{border-color:#7f1d1d;background:#1f1215;}#dsdi-chip.err i{color:#fca5a5;}'
+ '#dsdi-chip .dsdi-x{background:none;border:0;color:#8b97ad;font-size:16px;line-height:1;cursor:pointer;padding:0 2px;}#dsdi-chip .dsdi-x:hover{color:#fff;}'
+ '#dsdi-chip .dsdi-sp{width:12px;height:12px;border:2px solid #2f3a52;border-top-color:#8ab4f8;border-radius:50%;animation:dsdispin .8s linear infinite;flex:0 0 auto;}'
+ '@keyframes dsdispin{to{transform:rotate(360deg);}}'
+ '#dsdi-ibox{width:100%;max-width:680px;box-sizing:border-box;margin:0 0 16px;padding:12px 16px 10px;border:1px solid #1e1e1e;border-radius:12px;background:#0d0d0d;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsdi-ibox label{display:flex;align-items:center;gap:8px;margin:0 0 6px;color:#c9d1dd;font:700 13px/1.3 inherit;font-family:inherit;}'
+ '#dsdi-ibox label span{color:#6b7280;font-weight:500;}'
+ '#dsdi-instr{display:block;width:100%;box-sizing:border-box;min-height:44px;resize:vertical;border:0;outline:0;background:transparent;color:#e5e7eb;font:400 14px/1.5 inherit;font-family:inherit;padding:0;}'
+ '#dsdi-instr::placeholder{color:#4b5563;}'
+ '#dsdi-ibox:focus-within{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15);}'
+ '.dsdi-rvimg{position:relative;flex:0 0 auto;}.dsdi-rvimg img{width:72px;height:41px;object-fit:cover;border-radius:5px;display:block;border:1px solid #30363d;}'
+ '.dsdi-rvimg button{position:absolute;top:-7px;right:-7px;width:18px;height:18px;border-radius:50%;border:1px solid #30363d;background:#161b22;color:#f85149;font-size:12px;line-height:1;cursor:pointer;padding:0;}';

function injectCss(){ if ($('dsdi-css')) return; var st = document.createElement('style'); st.id = 'dsdi-css'; st.textContent = CSS; document.head.appendChild(st); }
function val(id){ var e = $(id); return e ? String(e.value || '').trim() : ''; }
var picker = null;
function pick(){
  if (!picker) {
    picker = document.createElement('input'); picker.type = 'file'; picker.style.display = 'none';
    picker.accept = '.pdf,.docx,.pptx,.txt,.md,.doc,.ppt,image/png,image/jpeg,image/webp';
    picker.onchange = function(){ var f = picker.files && picker.files[0]; picker.value = ''; if (f) DI.attach(f); };
    document.body.appendChild(picker);
  }
  picker.click();
}
DI.pick = pick;
function docTitleOf(name, blocks){
  var h = (blocks || []).filter(function(b){ return b.k === 'h'; })[0];
  return h ? h.t : name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ').replace(/\s+/g, ' ').trim();
}

/* ---------- attached file: read in the background, keep it until Build ---------- */
var ATT = null, RUN = 0;
DI.attach = function(file){
  var run = ++RUN, a = ATT = {file: file, status: 'reading', msg: 'Reading…'};
  chip();
  a.promise = (async function(){
    try {
      var got = await DI.read(file, function(m){ if (ATT === a) { a.msg = m; chip(); } });
      var kind = got.info.kind, secs;
      if (got.slides) {
        if (!got.slides.length) throw new Error('No text was found in this presentation.');
        secs = got.slides.slice(0, 60).map(function(s){ return {h: s.title || '', sub: s.sub || '', lines: s.lines.map(function(l){ return '- ' + l; }), imgs: s.img ? [s.img] : []}; });
        a.title = (got.slides[0] && got.slides[0].title) || docTitleOf(file.name);
      } else {
        var blocks = got.blocks || [];
        if (!blocks.some(function(b){ return b.t; })) throw new Error(kind === 'pdf' ? 'No readable text was found in this PDF.' : 'No text was found in this file.');
        secs = DI.sections(blocks, file.name);
        a.title = docTitleOf(file.name, blocks);
      }
      a.info = got.info; a.secs = secs; a.status = 'ready';
      var p = got.info.pages;
      a.msg = (p ? p + (kind === 'pptx' ? ' slide' : ' page') + (p > 1 ? 's' : '') + ' · ' : '') + (got.info.ocr ? got.info.ocr + ' scanned · ' : '') + 'ready';
    } catch(err) { a.status = 'error'; a.msg = (err && err.message) || 'Could not read this file.'; }
    if (ATT === a) chip();
  })();
};
DI.detach = function(){ ATT = null; RUN++; chip(); };
DI.attached = function(){ return ATT; };
function chip(){
  injectCss();
  var tip = $('dsdi-tip'); if (tip) tip.innerHTML = ATT ? '&#128206; Change file' : '&#128206; Upload PDF, Word or PPT';
  var pb = $('dsp-pbox'), c = $('dsdi-chip');
  if (!ATT) { if (c) c.remove(); return; }
  if (!pb) return;
  if (!c) { c = document.createElement('div'); c.id = 'dsdi-chip'; var ft = $('dsp-pbox-footer'); if (ft) pb.insertBefore(c, ft); else pb.appendChild(c); }
  var ic = {pdf: '&#128213;', docx: '&#128216;', pptx: '&#128217;'}[(ATT.file.name.split('.').pop() || '').toLowerCase()] || '&#128196;';
  c.className = ATT.status === 'ready' ? 'ok' : ATT.status === 'error' ? 'err' : '';
  c.title = ATT.status === 'error' ? ATT.msg : 'This file is used when you press Build';
  c.innerHTML = (ATT.status === 'reading' ? '<span class="dsdi-sp"></span>' : '<span>' + ic + '</span>') + '<b>' + esc(ATT.file.name) + '</b><i>' + (ATT.status === 'ready' ? '&#10003; ' : '') + esc(ATT.msg) + '</i><button type="button" class="dsdi-x" title="Remove file">&times;</button>';
  c.querySelector('.dsdi-x').onclick = function(e){ e.preventDefault(); e.stopPropagation(); DI.detach(); };
}

/* sections → what the server gets (Preserve) / one text (AI) */
function payload(secs){
  var budget = 42000, total = 0;
  var p = secs.map(function(s, i){ return {i: i, h: s.h, s: s.sub || '', t: s.lines.join('\n')}; });
  p.forEach(function(x){ total += x.t.length; });
  if (total > budget) { var f = budget / total; p.forEach(function(x){ if (x.t.length > 300) x.t = x.t.slice(0, Math.max(300, Math.floor(x.t.length * f))); }); }
  return p;
}
function docText(secs){
  var t = secs.map(function(s){ return (s.h ? '## ' + s.h + '\n' : '') + (s.sub ? s.sub + '\n' : '') + s.lines.join('\n'); }).join('\n\n');
  return t.slice(0, 24000);
}
function slidesWanted(instr){
  var m = String(instr || '').match(/\b(\d{1,2})\s*(?:slides?|cards?)\b/i);
  var n = m ? +m[1] : parseInt(val('dsp-slides'), 10) || 10;
  return Math.max(1, Math.min(MAX_SLIDES, n));
}

/* ---------- Build: our flow only when a file / instructions / another language is involved ---------- */
function ours(){
  var mode = window.G && G.mode;
  if (mode !== 'ai' && mode !== 'preserve') return false;
  if (ATT) return true;
  if (!val('dsp-prompt')) return false;                      /* empty prompt: the normal "please type something" */
  if (val('dsdi-instr')) return true;
  return mode === 'preserve' && !/^english/i.test(val('dsp-lang') || 'English');
}
function wrapGen(){
  var f = window.dspGen; if (typeof f !== 'function' || f.__dsdi) return;
  var w = function(){ if (ours()) { run(); return; } return f.apply(this, arguments); };
  w.__dsdi = 1; window.dspGen = w;
}
function loader(msg){
  var el = document.createElement('div'); el.id = 'dsdi-loading';
  el.style.cssText = 'position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  el.innerHTML = '<div style="background:#111827;border:1px solid #2a2a2a;border-radius:12px;padding:32px 40px;text-align:center;color:#e5e7eb;max-width:420px;">'
    + '<div style="width:36px;height:36px;border:3px solid #2a2a2a;border-top-color:#4A9EFF;border-radius:50%;margin:0 auto 16px;animation:dsdispin .8s linear infinite;"></div>'
    + '<div id="dsdi-lmsg" style="font-weight:700;font-size:15px;">' + esc(msg) + '</div>'
    + '<div style="color:#9ca3af;font-size:12.5px;margin-top:4px;">This is just a preview — nothing is charged yet.</div></div>';
  injectCss(); document.body.appendChild(el);
  return {msg: function(m){ var x = $('dsdi-lmsg'); if (x) x.textContent = m; }, done: function(){ if (el.parentNode) el.parentNode.removeChild(el); }};
}
function srcImages(slides, secs){
  var used = {};
  slides.forEach(function(s){
    var ss = s.src || s.sections || [];
    for (var k = 0; k < ss.length && !s._img; k++) {
      var sec = secs && secs[ss[k]]; if (!sec) continue;
      for (var m = 0; m < sec.imgs.length; m++) { var key = ss[k] + ':' + m; if (!used[key]) { used[key] = 1; s._img = sec.imgs[m].data; break; } }
    }
    delete s.src; delete s.sections;
  });
}
async function run(){
  var mode = G.mode, prompt = val('dsp-prompt'), instr = val('dsdi-instr'), lang = val('dsp-lang') || 'English';
  var n = slidesWanted(instr), a = ATT;
  var L = loader(a && a.status === 'reading' ? 'Reading your file…' : mode === 'ai' ? 'Writing your slides…' : 'Organizing your content…');
  var ppt, note = '';
  try {
    if (a) {
      await a.promise;
      if (a.status === 'error') throw new Error(a.msg);
      L.msg(mode === 'ai' ? 'Writing your slides from ' + a.file.name + '…' : 'Organizing ' + a.file.name + ' into slides…');
    }
    if (mode === 'ai') {
      var r = await post(CFG.ai, {prompt: prompt, doc: a ? docText(a.secs) : '', file: a ? a.file.name : '', instructions: instr, num_slides: n, language: lang});
      if (!r || r.error || !r.ppt_data) throw new Error((r && r.message) || 'The AI could not write the slides. Please try again.');
      ppt = r.ppt_data;
      note = a ? 'AI rewrote and improved the content of <b>' + esc(a.file.name) + '</b>.' : '';
    } else {
      var r2 = await post(CFG.pp, {sections: a ? payload(a.secs) : [], text: prompt, instructions: instr, num_slides: n, language: lang, title: a ? a.title : '', kind: a ? a.info.kind : ''});
      if (r2 && r2.success && r2.ppt_data) {
        ppt = r2.ppt_data; if (a) srcImages(ppt.slides, a.secs); else ppt.slides.forEach(function(s){ delete s.src; });
        note = r2.translated ? 'Your text was translated to <b>' + esc(lang) + '</b> — same sentences and meaning.' : 'Your own words are kept — AI only arranged them into slides.';
      } else if (a) {                                                  /* AI not available: follow the document's headings */
        var hs = DI.toDeck(DI.heuristic(a.secs, n, a.title), a.secs);
        hs.forEach(function(s){ if (s.image_data) { s._img = s.image_data; delete s.image_data; } });
        ppt = {title: hs[0] && hs[0].title || a.title, slides: hs};
        note = 'AI arranging was not available' + (r2 && r2.message ? ' (' + esc(r2.message) + ')' : '') + ', so the slides follow the document\'s headings.';
      } else throw new Error((r2 && r2.message) || 'Could not organise your text. Please try again.');
    }
  } catch(err) { L.done(); alert((err && err.message) || 'Something went wrong. Please try again.'); return; }
  L.done();
  review(ppt, mode, note);
}

/* ---------- review panel (same look as the normal Review Your Slides) ---------- */
var TYPE_LABEL = {title: 'Title', bullet: 'Bullets', two_column: '2 Columns', three_column: '3 Columns', feature_grid: '2x2 Grid', five_point: '5 Steps', six_point: '6 Steps', seven_point: '7 Steps', eight_point: '8 Steps', stats: 'Stats', timeline: 'Timeline', quote: 'Quote', image_text: 'Image + Text'};
function retype(s, mode){
  var n = (s.content || []).length;
  if (['two_column', 'three_column', 'feature_grid', 'bullet', 'five_point', 'six_point', 'seven_point', 'eight_point'].indexOf(s.type) < 0 || !n) return;
  s.type = n === 2 ? 'two_column' : n === 3 ? 'three_column' : n === 4 ? 'feature_grid' : mode === 'ai' && n <= 8 ? ['five_point', 'six_point', 'seven_point', 'eight_point'][n - 5] : 'bullet';
}
function costOf(n){ return Math.max(window.DS_MIN_CREDITS || 5, n * (window.DS_CREDITS_PER_SLIDE || 1)); }
function review(ppt, mode, note){
  var ov = document.createElement('div'); ov.id = 'dsdi-rv';
  ov.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;padding:24px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  ov.innerHTML = '<div style="background:#161b22;border:1px solid #2a2a2a;border-radius:14px;max-width:720px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">'
    + '<div style="padding:22px 26px;border-bottom:1px solid #21262d;"><div style="color:#f9fafb;font-weight:800;font-size:19px;">Review Your Slides</div>'
    + '<div style="color:#9ca3af;font-size:13px;margin-top:3px;">' + (note ? note + ' ' : '') + 'Edit, delete a slide, or add/remove points, then confirm.</div></div>'
    + '<div id="dsdi-rv-list" style="padding:20px 26px;overflow-y:auto;flex:1;"></div>'
    + '<div style="padding:16px 26px;border-top:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;gap:12px;">'
    + '<button id="dsdi-rv-back" style="background:transparent;border:1px solid #30363d;color:#c9d1d9;border-radius:8px;padding:11px 18px;font-weight:700;font-size:13px;cursor:pointer;">&larr; Back to Edit</button>'
    + '<button id="dsdi-rv-go" style="background:#2563eb;border:none;color:#fff;border-radius:8px;padding:11px 22px;font-weight:800;font-size:13px;cursor:pointer;"></button></div></div>';
  document.body.appendChild(ov);
  var list = $('dsdi-rv-list');
  function draw(){
    list.innerHTML = ppt.slides.map(function(s, i){
      var pts = (s.content || []).map(function(c, ci){
        return '<li style="margin-bottom:8px;display:flex;align-items:flex-start;gap:8px;"><span contenteditable="true" data-i="' + i + '" data-c="' + ci + '" style="flex:1;color:#c9d1d9;font-size:13px;line-height:1.5;outline:none;">' + esc(c) + '</span>'
          + '<button class="dsdi-dp" data-i="' + i + '" data-c="' + ci + '" title="Remove point" style="flex-shrink:0;background:transparent;border:none;color:#f85149;font-size:15px;cursor:pointer;line-height:1;padding:2px 4px;">&times;</button></li>';
      }).join('');
      return '<div style="background:#0d1117;border:1px solid #21262d;border-radius:10px;padding:18px 20px;margin-bottom:14px;">'
        + '<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;"><span style="color:#58A6FF;font-weight:700;font-size:12px;letter-spacing:.03em;">SLIDE ' + (i + 1) + '</span>'
        + '<div style="display:flex;align-items:center;gap:10px;">' + (s._img ? '<span class="dsdi-rvimg" title="Picture from your file"><img src="' + s._img + '" alt=""/><button class="dsdi-di" data-i="' + i + '" title="Remove picture">&times;</button></span>' : '')
        + '<span style="color:#6b7280;font-size:11px;background:#161b22;border:1px solid #21262d;border-radius:20px;padding:2px 10px;">' + esc(TYPE_LABEL[s.type] || s.type) + '</span>'
        + '<button class="dsdi-ds" data-i="' + i + '" title="Delete slide" style="background:transparent;border:1px solid #30363d;color:#f85149;border-radius:6px;font-size:12px;cursor:pointer;padding:3px 8px;">&#128465; Delete</button></div></div>'
        + '<div contenteditable="true" data-i="' + i + '" data-f="title" style="color:#f9fafb;font-weight:800;font-size:17px;margin-bottom:4px;outline:none;">' + esc(s.title) + '</div>'
        + '<div contenteditable="true" data-i="' + i + '" data-f="subtitle" style="color:#8b95a5;font-size:13px;font-style:italic;margin-bottom:10px;outline:none;">' + esc(s.subtitle || '') + '</div>'
        + (pts ? '<ul style="margin:10px 0 0;padding-left:6px;list-style:none;">' + pts + '</ul>' : '')
        + '<button class="dsdi-ap" data-i="' + i + '" style="margin-top:8px;background:transparent;border:1px dashed #30363d;color:#8b95a5;border-radius:6px;font-size:12px;cursor:pointer;padding:5px 10px;">+ Add point</button></div>';
    }).join('');
    $('dsdi-rv-go').innerHTML = '&#10003; Generate Presentation &middot; ' + costOf(ppt.slides.length) + ' credits';
  }
  list.addEventListener('click', function(e){
    var b = e.target.closest('button'); if (!b) return;
    var i = +b.getAttribute('data-i'), s = ppt.slides[i];
    if (b.classList.contains('dsdi-ds')) { if (ppt.slides.length <= 1) { alert('You need at least one slide.'); return; } ppt.slides.splice(i, 1); }
    else if (b.classList.contains('dsdi-dp')) { s.content.splice(+b.getAttribute('data-c'), 1); retype(s, mode); }
    else if (b.classList.contains('dsdi-ap')) { s.content = s.content || []; s.content.push('New Point: Click to edit this text.'); retype(s, mode); }
    else if (b.classList.contains('dsdi-di')) { delete s._img; }
    else return;
    draw();
  });
  list.addEventListener('input', function(e){
    var t = e.target, i = t.getAttribute('data-i'); if (i === null) return;
    var s = ppt.slides[+i], f = t.getAttribute('data-f'), c = t.getAttribute('data-c');
    if (f) s[f] = t.textContent; else if (c !== null) s.content[+c] = t.textContent;
  });
  draw();
  $('dsdi-rv-back').onclick = function(){ ov.remove(); };
  $('dsdi-rv-go').onclick = function(){
    var btn = $('dsdi-rv-go'); btn.disabled = true; btn.textContent = 'Generating…';
    var imgs = ppt.slides.map(function(s){ return s._img || ''; });
    var send = {title: ppt.title, slides: ppt.slides.map(function(s){ var o = {}; for (var k in s) if (k !== '_img' && k !== 'image_data') o[k] = s[k]; return o; })};
    post(mode === 'ai' ? CFG.aic : CFG.pc, {ppt_data: send, source: ATT ? 1 : 0})
    .then(function(d){
      if (!d || d.error || !d.ppt_data) { btn.disabled = false; draw(); alert((d && d.message) || 'Could not generate the presentation. Please try again.'); return; }
      ov.remove();
      build(d, mode, imgs);
    })
    .catch(function(){ btn.disabled = false; draw(); alert('Network error while generating. Please try again.'); });
  };
}

function build(d, mode, imgs){
  var fin = d.ppt_data, design = val('dsp-design') || 'navy-pro';
  (fin.slides || []).forEach(function(s, i){ if (imgs[i]) s.image_data = imgs[i]; });
  fin.layout = val('dsp-layout') || fin.layout || '16:9';
  tagTpl(fin);
  if (d.credits_balance !== undefined) { G.credits = d.credits_balance; var ce = $('ds-cr-display'); if (ce) ce.textContent = Number(G.credits).toLocaleString(); }
  var label = ATT ? ATT.file.name : (fin.title || 'Presentation'), done = false;
  function finish(){
    if (done) return; done = true;
    if (window.dspLiveGenHide) window.dspLiveGenHide();
    G.pptId = d.ppt_id || (mode + '_' + Date.now()); G.pptData = fin; G.pptTitle = fin.title || 'Presentation';
    if (window.buildEditor) window.buildEditor(fin);
    if (window.dspSyncPreserveData) window.dspSyncPreserveData(fin);
    picLayouts(fin);
    DI.detach();
  }
  if (window.dspLiveGenShow && window.dspLiveGenStream) {
    window.dspLiveGenShow(label, fin.slides.length, design);
    var bt = $('dsp-lg-banner-txt'); if (bt) bt.innerHTML = '<b style="color:#111;">Building Your Slides:</b> Turning your reviewed content into a deck.';
    setTimeout(finish, 15000);
    window.dspLiveGenStream(fin, design, finish);
  } else finish();
}
/* design template picked on the home screen (DS Template Engine): the deck must carry it, in both modes */
function tagTpl(fin){
  var X = window.DSTE; if (!X || !X.reg || !fin) return;
  var pk = X.pick && X.pick !== 'classic' && X.reg[X.pick] ? X.pick : null;
  if (!pk) { delete fin.tpl; return; }                       /* Classic picked: old layouts */
  if (!fin.tpl || !X.reg[fin.tpl]) fin.tpl = pk;
  if (fin.tplTheme == null) fin.tplTheme = X.homeTheme || '';
  if (window.S && S.data && S.data !== fin && S.data.slides) { S.data.tpl = fin.tpl; S.data.tplTheme = fin.tplTheme; }
}
/* slides with a picture from the file open on a picture layout */
function picLayouts(d){
  setTimeout(function(){
    if (!window.S) return;
    var T = null; try { T = window.DS_TPL_ACTIVE && window.DS_TPL_ACTIVE(); } catch(e){}
    var sl = (S.data && S.data.slides) || d.slides, set = 0;
    S.choices = S.choices || {};
    sl.forEach(function(s, i){
      if (!s || !s.image_data || s.type === 'chart') return;
      var ok = true;
      if (T) { var v = T.variants && T.variants(DS_TPL_KIND(s), s); ok = !!(v && v.some(function(x){ return x.id === 'v2'; })); }
      if (ok && !S.choices['slide_' + i]) { S.choices['slide_' + i] = 'v2'; set++; }
    });
    if (set) {
      try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch(e){}
      if (window.dspRenderThumbs) window.dspRenderThumbs();
      if (window.dspSelectThumb) window.dspSelectThumb(0);
    }
  }, 1800);
}
function DS_TPL_KIND(s){ return window.DSTE && window.DSTE.kindOf ? window.DSTE.kindOf(s) : s.type; }

/* ---------- hooks on the home screen ---------- */
function hook(){
  wrapGen();
  var old = document.querySelectorAll('#dsp-m-keep .dsdi-up'); for (var i = 0; i < old.length; i++) old[i].remove();   /* no upload button on the Preserve card */
  var tip = $('dsp-pbox-tip');
  if (tip && !$('dsdi-tip')) {
    injectCss();
    var b = document.createElement('button'); b.type = 'button'; b.id = 'dsdi-tip';
    b.onclick = function(e){ e.preventDefault(); pick(); };
    tip.parentNode.insertBefore(b, tip.nextSibling);
    chip();
  }
  var pb = $('dsp-pbox');
  if (pb && !$('dsdi-ibox') && pb.parentNode) {
    injectCss();
    var box = document.createElement('div'); box.id = 'dsdi-ibox';
    box.innerHTML = '<label for="dsdi-instr">&#9998;&#65039; Instructions for AI <span>(optional)</span></label>'
      + '<textarea id="dsdi-instr" rows="2" placeholder="E.g. Put 5 points on slide 2 · Make slide 4 a timeline · Keep titles short · Add a thank-you slide at the end"></textarea>';
    pb.parentNode.insertBefore(box, pb.nextSibling);
  }
  if (pb && !pb.__dsdi) {
    pb.__dsdi = 1;
    pb.addEventListener('dragover', function(e){ if (e.dataTransfer && [].indexOf.call(e.dataTransfer.types || [], 'Files') >= 0) { e.preventDefault(); pb.classList.add('dsdi-drag'); } });
    pb.addEventListener('dragleave', function(){ pb.classList.remove('dsdi-drag'); });
    pb.addEventListener('drop', function(e){ var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]; pb.classList.remove('dsdi-drag'); if (!f) return; e.preventDefault(); DI.attach(f); });
  }
}
setInterval(hook, 800);
if (document.readyState !== 'loading') hook(); else document.addEventListener('DOMContentLoaded', hook);
})();


