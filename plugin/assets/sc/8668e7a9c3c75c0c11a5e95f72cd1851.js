
(function(){
if (window.__dsDeckStyle) return; window.__dsDeckStyle = 1;
function $(id){ return document.getElementById(id); }
var ROLES = [['title','Title'],['sub','Subtitle'],['ph','Point title'],['pd','Point explanation'],['tk','Key takeaway']];
var FIELD = {title:'title', subtitle:'sub', 'content-h':'ph', 'content-d':'pd', 'content-plain':'pd', 'tl-step':'ph', 'tl-desc':'pd', 'stat-num':'ph', 'stat-label':'pd', takeaway:'tk',
             'tf-tagline':'sub', 'tf-presenter':'sub', 'tf-designation':'sub', 'tf-date':'sub', 'tf-company':'sub'};
var WNAME = {100:'Thin', 200:'Extra Light', 300:'Light', 400:'Regular', 500:'Medium', 600:'Semi Bold', 700:'Bold', 800:'Extra Bold', 900:'Black'};
var FONTS = {'Poppins':[100,200,300,400,500,600,700,800,900], 'Inter':[100,200,300,400,500,600,700,800,900], 'Montserrat':[100,200,300,400,500,600,700,800,900],
  'Roboto':[100,300,400,500,700,900], 'Open Sans':[300,400,500,600,700,800], 'Lato':[100,300,400,700,900], 'Nunito':[200,300,400,500,600,700,800,900],
  'Raleway':[100,200,300,400,500,600,700,800,900], 'Work Sans':[100,200,300,400,500,600,700,800,900], 'DM Sans':[100,200,300,400,500,600,700,800,900],
  'Plus Jakarta Sans':[200,300,400,500,600,700,800], 'Manrope':[200,300,400,500,600,700,800], 'Outfit':[100,200,300,400,500,600,700,800,900],
  'Sora':[100,200,300,400,500,600,700,800], 'Rubik':[300,400,500,600,700,800,900], 'Space Grotesk':[300,400,500,600,700], 'IBM Plex Sans':[100,200,300,400,500,600,700],
  'Quicksand':[300,400,500,600,700], 'Oswald':[200,300,400,500,600,700], 'Playfair Display':[400,500,600,700,800,900], 'Merriweather':[300,400,700,900], 'Lora':[400,500,600,700]};
var KEY = {title:'title', subtitle:'sub', takeaway:'tk', 'content-h':'h', 'tl-step':'h', 'stat-num':'h', 'content-d':'d', 'content-plain':'d', 'tl-desc':'d', 'stat-label':'d'};
var draft = null;                                  /* style being edited in the panel (previewed only) */
var resetAll = false;                              /* "Reset to default" was pressed in the panel */
/* the name of one object on a slide: title, sub, tk, h:<n>, d:<n>, tf:<field>, icon:<n> */
function keyOf(f, idx){
	if (/^tf-[a-z]+$/.test(f)) return 'tf:' + f.slice(3);
	var k = KEY[f]; if (!k) return '';
	if (k !== 'h' && k !== 'd') return k;
	return (idx === null || idx === undefined || idx === '') ? '' : k + ':' + parseInt(idx, 10);
}
function ovOf(s){ if (tplOn() || !s || !s.ov || typeof s.ov !== 'object') return null; for (var k in s.ov) return s.ov; return null; }
function sized(v){ return !!v && Math.abs(v - 1) > 0.001; }
var ICON = 'img[src*="api.iconify.design"]';
function badgeOf(img){ var p = img.parentElement; return (p && p.children.length === 1 && /background(-color)?\s*:/.test(p.getAttribute('style') || '')) ? p : null; }
function iconSrc(img, col){ var u = img.getAttribute('src') || '', q = 'color=' + encodeURIComponent(col); img.setAttribute('src', /[?&]color=/.test(u) ? u.replace(/([?&])color=[^&]*/, '$1' + q) : u + (u.indexOf('?') < 0 ? '?' : '&') + q); }
/* c = the round background (or the drawing itself when the icon has no background), ic = the drawing */
function styleIcon(img, o){
	var b = badgeOf(img);
	if (hex(o.c)) { if (b) b.style.setProperty('background', o.c, 'important'); else if (!hex(o.ic)) iconSrc(img, o.c); }
	if (hex(o.ic)) iconSrc(img, o.ic);
	(b || img).style.transform = sized(o.s) ? 'scale(' + o.s + ')' : '';
}
/* ---- breathing space: text never sits in the corner of its box ---- */
var BRF = '[data-ed-field="content-h"],[data-ed-field="content-d"],[data-ed-field="content-plain"],[data-ed-field="tl-step"],[data-ed-field="tl-desc"],[data-ed-field="stat-num"],[data-ed-field="stat-label"]';
/* the box a text sits in: the nearest parent with its own background and rounded corners */
function boxOf(el, root){
	var a = el.parentElement, n = 0;
	while (a && a !== root && n < 4) {
		var st = a.getAttribute('style') || '', cl = a.className || '';
		if (/border-radius:\s*[\d.]+(px|em|%)/.test(st) && !/border-radius:\s*50%/.test(st) && /background(-color)?:\s*(#|rgb)/.test(st) && !/dsce-tk|dsp-vp-imgzone/.test(cl)) return a;
		a = a.parentElement; n++;
	}
	return null;
}
/* the margin is a fixed share of the slide, whatever text size the box itself uses */
function brPad(b, fs){ fs = fs > 0.05 ? fs : 1; b.style.padding = (0.55 / fs).toFixed(3) + 'em ' + (0.85 / fs).toFixed(3) + 'em'; }
function breathePad(root){
	var t = root.querySelectorAll(BRF);
	for (var i = 0; i < t.length; i++) {
		var b = boxOf(t[i], root); if (!b || b.hasAttribute('data-br')) continue;
		b.setAttribute('data-br', '1');
		var fm = /font-size:\s*([\d.]+)em/.exec(b.getAttribute('style') || '');
		brPad(b, fm ? parseFloat(fm[1]) : 1); b.style.boxSizing = 'border-box';
	}
}
/* on the page: where the text does not fit its box, the boxes of that group get smaller text; the last text of a
   box shows as many whole lines as there is room for */
function breatheFit(root){
	if (!root || !root.querySelectorAll) return;
	var boxes = root.querySelectorAll('[data-br]'), i, groups = [];
	if (!boxes.length) return;
	var clamp = function(b){
		var t = b.querySelectorAll('[data-ed-field]'), last = t[t.length - 1]; if (!last) return;
		var cs = getComputedStyle(last), bc = getComputedStyle(b);
		/* whole lines only: in the editor the texts are plain blocks, so the height is limited as well as the line count */
		var fitLines = function(){
			var fp = parseFloat(cs.fontSize) || 12, lh = parseFloat(cs.lineHeight) || fp * 1.3;
			var room = b.getBoundingClientRect().bottom - (parseFloat(bc.paddingBottom) || 0) - last.getBoundingClientRect().top, n = Math.max(1, Math.floor((room + 1) / lh));
			last.style.webkitLineClamp = String(n);
			last.style.setProperty('max-height', (n * lh / fp).toFixed(3) + 'em', 'important'); last.style.setProperty('overflow', 'hidden', 'important');
		};
		fitLines();
		if (b.scrollHeight > b.clientHeight + 1 || last.getBoundingClientRect().bottom > b.getBoundingClientRect().bottom - (parseFloat(bc.paddingBottom) || 0) + 1) { b.style.justifyContent = 'flex-start'; fitLines(); }   /* too full to centre: start at the top margin, end on a whole line above the bottom margin */
	};
	var over = function(b){
		if (b.scrollHeight > b.clientHeight + 1 || b.scrollWidth > b.clientWidth + 1) return true;
		var t = b.querySelectorAll('[data-ed-field]');
		for (var q = 0; q < t.length; q++) { if (t[q].scrollHeight > t[q].clientHeight + 2) return true; }
		return false;
	};
	var setF = function(b, f){
		if (!b.hasAttribute('data-brf0')) { var m = /font-size:\s*([\d.]+)em/.exec(b.getAttribute('style') || ''); b.setAttribute('data-brf0', m ? m[1] : '1'); }
		var fs = (parseFloat(b.getAttribute('data-brf0')) || 1) * f;
		b.style.fontSize = fs.toFixed(3) + 'em'; brPad(b, fs); clamp(b);
	};
	for (i = 0; i < boxes.length; i++) {
		var b = boxes[i], f = 1;
		clamp(b);
		if (!b.hasAttribute('data-fs0')) { while (over(b) && f > 0.62) { f *= 0.94; setF(b, f); } }
		var g = null; for (var j = 0; j < groups.length; j++) { if (groups[j].p === b.parentElement) g = groups[j]; }
		if (!g) { g = {p:b.parentElement, f:1, list:[]}; groups.push(g); }
		g.list.push(b); g.f = Math.min(g.f, f);
	}
	groups.forEach(function(g){ if (g.f < 0.999) g.list.forEach(function(b){ if (!b.hasAttribute('data-fs0')) setF(b, g.f); }); });   /* one text size per group of boxes */
}
/* the open slide is re-fitted every time the editor draws it; thumbnails as they appear */
var brObs = null, brEl = null;
function breatheWatch(){
	var m = $('dsp-ppt-main-slide');
	if (m && m !== brEl && window.MutationObserver) {
		if (brObs) brObs.disconnect();
		brEl = m; brObs = new MutationObserver(function(){ if (!tplOn()) { try { breatheFit(m); } catch (e) {} } });
		brObs.observe(m, {childList:true});
		try { breatheFit(m); } catch (e) {}
	}
	var th = $('dsp-ppt-thumbs'); if (!th || tplOn()) return;
	var bx = th.querySelectorAll('.dsp-thumb-box');
	for (var i = 0; i < bx.length; i++) { var r = bx[i].firstElementChild; if (r && !r.hasAttribute('data-brd')) { r.setAttribute('data-brd', '1'); try { breatheFit(bx[i]); } catch (e) {} } }
}
setInterval(breatheWatch, 600);

/* ---- a picture down the left / right side: wider or narrower, the rest of the slide takes what is left ---- */
function zoneInfo(z){
	var st = ';' + (z.getAttribute('style') || '').replace(/\s+/g, ''), w = /;width:([\d.]+)%/.exec(st);
	if (!w || st.indexOf(';top:0') < 0 || st.indexOf(';bottom:0') < 0) return null;
	var side = st.indexOf(';right:0') >= 0 ? 'right' : (st.indexOf(';left:0') >= 0 ? 'left' : ''), now = parseFloat(w[1]);
	var w0 = parseFloat(z.getAttribute('data-w0')) || now;
	return (side && w0 > 8 && w0 < 70) ? {el:z, side:side, w0:w0, now:now} : null;
}
function sideZone(root){
	var zs = root.querySelectorAll('.dsp-vp-imgzone');
	for (var i = 0; i < zs.length; i++) { if (zs[i].closest('.dsce-hideimg')) continue; var inf = zoneInfo(zs[i]); if (inf) return inf; }
	return null;
}
function imgW(w0, s){ return Math.max(12, Math.min(60, Math.round(w0 * s * 10) / 10)); }
function imgApply(root, s){
	var z = sideZone(root); if (!z) return;
	var w1 = imgW(z.w0, s), k = (100 - w1) / (100 - z.w0), f = k < 1 ? Math.max(0.8, Math.sqrt(k)) : 1, box = z.el.parentElement;
	var css = 'position:absolute;top:0;bottom:0;' + (z.side === 'right' ? 'left:0;' : 'right:0;') + 'width:' + (k * 100).toFixed(3) + '%;' + (f < 1 ? 'font-size:' + f.toFixed(3) + 'em;' : '');
	var wrap = null, kids = Array.prototype.slice.call(box.children), i;
	for (i = 0; i < kids.length; i++) { if (kids[i].classList.contains('dsds-imw')) wrap = kids[i]; }
	if (!wrap) {
		wrap = document.createElement('div'); wrap.className = 'dsds-imw';
		for (i = 0; i < kids.length; i++) { if (kids[i] !== z.el && !kids[i].classList.contains('dsce-tk') && !/(^|\s)dsce-logo/.test(kids[i].className || '')) wrap.appendChild(kids[i]); }   /* the logo keeps its own place on the slide */
		box.insertBefore(wrap, box.firstChild);
	}
	wrap.setAttribute('style', css);
	for (i = 0; i < kids.length; i++) { if (kids[i].classList.contains('dsce-tk')) kids[i].style[z.side] = (w1 + 3) + '%'; }
	z.el.setAttribute('data-w0', z.w0);
	z.el.style.width = w1 + '%';
}
/* ---- cards after a resize: icon above the title when the card is narrow, text sized to fill the card ---- */
var HEAD = '[data-ed-field="content-h"]', fitT = 0;
/* the icon + title row of a card: a flex row (remembered once seen, because stacking turns it into a column) */
function rowOf(r, card){
	if (r === card) return null;
	if (r.hasAttribute('data-dsrow')) return r;
	var st = r.getAttribute('style') || '';
	if (r.children.length < 2 || !/display:\s*flex/.test(st) || /flex-direction:\s*column/.test(st)) return null;
	r.setAttribute('data-dsrow', '1'); return r;
}
function cardsOf(root){
	var hs = root.querySelectorAll(HEAD), out = [];
	for (var i = 0; i < hs.length; i++) {
		if (hs[i].closest('.dsp-vp-imgzone')) continue;
		var a = hs[i];
		while (a.parentElement && a.parentElement.querySelectorAll(HEAD).length < 2) a = a.parentElement;
		if (!a.parentElement || a === hs[i]) continue;
		var r = hs[i].parentElement;
		out.push({card:a, h:hs[i], row:rowOf(r, a)});
	}
	return out;
}
/* cards that sit side by side in one grid: with too little room they go onto more rows (2 + 1, 2 + 2 ...),
   and the cards of a short last row share the full width */
function gridFlow(cs, gc){
	if (!cs.length) return 0;
	var g = cs[0].card.parentElement, i;
	for (i = 0; i < cs.length; i++) { if (cs[i].card.parentElement !== g) return 0; }
	if (!g.hasAttribute('data-gc0')) { var m = /grid-template-columns:\s*repeat\((\d+)/.exec(g.getAttribute('style') || ''); if (!m) return 0; g.setAttribute('data-gc0', m[1]); }
	var N = parseInt(g.getAttribute('data-gc0'), 10) || 0, n = cs.length;
	if (!N || n > N) return 0;                           /* only a single row of cards is re-flowed */
	if (!gc || gc >= n) { g.style.gridTemplateColumns = 'repeat(' + N + ',minmax(0,1fr))'; g.style.gridTemplateRows = ''; for (i = 0; i < n; i++) cs[i].card.style.gridColumn = ''; return N; }
	var rows = Math.ceil(n / gc), rest = n % gc, unit = rest || 1;
	g.style.gridTemplateColumns = 'repeat(' + (gc * unit) + ',minmax(0,1fr))';
	g.style.gridTemplateRows = 'repeat(' + rows + ',minmax(0,1fr))';
	for (i = 0; i < n; i++) cs[i].card.style.gridColumn = 'span ' + ((rest && i >= n - rest) ? gc : unit);
	return N;
}
function fitApply(root, f, st, hl, gc){
	var on = sized(f) || st || hl || gc, all = cardsOf(root);
	gridFlow(all, gc || 0);
	all.forEach(function(c){
		if (!c.card.hasAttribute('data-fs0')) { var m = /font-size:\s*([\d.]+)em/.exec(c.card.getAttribute('style') || ''); c.card.setAttribute('data-fs0', m ? m[1] : '1'); }
		var b = parseFloat(c.card.getAttribute('data-fs0')) || 1;
		c.card.style.fontSize = (sized(f) || b !== 1) ? (b * (f || 1)).toFixed(3) + 'em' : '';
		if (c.card.hasAttribute('data-br')) brPad(c.card, b * (f || 1));
		c.card.style.justifyContent = on ? 'flex-start' : '';
		if (c.row) { c.row.style.flexDirection = st ? 'column' : ''; c.row.style.alignItems = st ? 'flex-start' : ''; }
		/* every point title gets the height of the tallest one, so the rule lines and the texts below sit level */
		var lh = /line-height:\s*([\d.]+)\s*(;|$)/.exec(c.h.getAttribute('style') || ''), unit = lh ? parseFloat(lh[1]) : 1.2;
		c.h.style.minHeight = hl ? (hl * unit).toFixed(2) + 'em' : '';
		c.h.style.webkitLineClamp = on ? String(Math.max(3, hl || 0)) : '';
		c.h.style.overflowWrap = on ? 'normal' : ''; c.h.style.wordBreak = on ? 'normal' : '';
		if (c.row && c.h.parentElement === c.row) c.h.style.width = st ? '100%' : '';
	});
}
/* measured on the open slide: icon above the title or beside it, how large the card text can get, how tall the titles are */
var fitCv = null;
function wordFits(h){
	var cs = getComputedStyle(h); fitCv = fitCv || document.createElement('canvas').getContext('2d');
	fitCv.font = cs.fontWeight + ' ' + cs.fontSize + ' ' + cs.fontFamily;
	var w = (h.textContent || '').split(/\s+/), mx = 0;
	for (var i = 0; i < w.length; i++) mx = Math.max(mx, fitCv.measureText(w[i]).width);
	return mx <= h.clientWidth + 1;
}
function fitMeasure(){
	var m = mainEl(); if (!m) return null;
	var cs = cardsOf(m); if (!cs.length) return null;
	/* how many cards fit side by side: a card narrower than 17% of the slide is too narrow to read */
	var gc = 0, N = gridFlow(cs, 0);
	if (N && cs.length > 1) { var g = cs.length; while (g > 1 && cs[0].card.clientWidth / m.clientWidth < 0.17) { g--; gridFlow(cs, g); } gc = g < cs.length ? g : 0; }
	var rows = false, i; for (i = 0; i < cs.length; i++) { if (cs[i].row) rows = true; }
	var lines = function(){
		var mx = 1;
		for (var j = 0; j < cs.length; j++) { var h = cs[j].h, st0 = getComputedStyle(h), lp = parseFloat(st0.lineHeight) || (parseFloat(st0.fontSize) * 1.2); mx = Math.max(mx, Math.round(h.scrollHeight / lp)); }
		return mx;
	};
	var hl = 0;
	var ok = function(f, st){
		fitApply(m, f, st, 0, gc); hl = lines(); fitApply(m, f, st, hl, gc);
		for (var j = 0; j < cs.length; j++) {
			var c = cs[j].card, cst = getComputedStyle(c), kids = c.children, top = 1e9, bot = -1e9;
			if (!wordFits(cs[j].h)) return false;
			for (var q = 0; q < kids.length; q++) { var r = kids[q].getBoundingClientRect(); if (r.height < 1) continue; top = Math.min(top, r.top); bot = Math.max(bot, r.bottom); }
			var avail = c.clientHeight - (parseFloat(cst.paddingTop) || 0) - (parseFloat(cst.paddingBottom) || 0);
			if (bot - top > avail * 0.96 || c.scrollWidth > c.clientWidth + 1) return false;
			var t = c.querySelectorAll('[data-ed-field]');
			for (q = 0; q < t.length; q++) { if (t[q].scrollHeight > t[q].clientHeight + 2) return false; }
		}
		return true;
	};
	var best = function(st){
		var lo = 0.6, hi = 1.6, any = false;
		if (ok(hi, st)) return {f:hi, ok:true};
		for (var n = 0; n < 7; n++) { var mid = (lo + hi) / 2; if (ok(mid, st)) { lo = mid; any = true; } else hi = mid; }
		return {f:Math.round(lo * 100) / 100, ok:any};
	};
	var a = best(false), st = false, f = a.f;
	if (rows) { var s2 = best(true); if (!a.ok || s2.f > a.f * 1.15) { st = true; f = s2.f; } }   /* no room beside the icon: icon on top, title below, text under it */
	ok(f, st);
	return {f:f, st:st, hl:hl, gc:gc};
}
function fitStore(){
	if (!getOv('img')) return;
	var r = fitMeasure(); if (!r) return;
	slidesAt().forEach(function(sl){ if (sl.ov && sl.ov.img) { sl.ov.img.f = r.f; sl.ov.img.st = r.st ? 1 : 0; sl.ov.img.hl = r.hl; sl.ov.img.gc = r.gc; } });
	paintThumbs(S.idx);
}

function tplOn(){ try { return !!(window.DS_TPL_ACTIVE && window.DS_TPL_ACTIVE()); } catch (e) { return false; } }
function decks(){ var a = []; if (window.S && S.data) a.push(S.data); if (window.G && G.pptData && a.indexOf(G.pptData) < 0) a.push(G.pptData); return a; }
function saved(){ var d = decks(); for (var i = 0; i < d.length; i++) { if (d[i] && d[i].style && typeof d[i].style === 'object') return d[i].style; } return null; }
function active(){ return tplOn() ? null : (draft || saved()); }
function activeAny(){ return draft || saved(); }
function hex(v){ return (typeof v === 'string' && /^#[0-9a-fA-F]{6}$/.test(v)) ? v : ''; }
function isEmpty(st){
	if (!st) return true;
	if (st.font || hex(st.bg) || hex(st.ac)) return false;
	var e = st.els || {};
	for (var k in e) { if (e[k] && (e[k].w || hex(e[k].c) || e[k].t || (e[k].s && Math.abs(e[k].s - 1) > 0.001))) return false; }
	return true;
}
function themeNow(){ var el = $('dsp-design'), T = window.TH || {}; return T[(el && el.value) || 'navy-pro'] || T['navy-pro'] || {bg:'#1B2A4A', tc:'#FFFFFF', bc:'#C8D8F0', ac:'#4A9EFF'}; }
function toHex(c){ var m = /#[0-9a-fA-F]{6}/.exec(String(c || '')); return m ? m[0] : '#000000'; }

/* ---- hooks used by Snippet 17 ---- */
window.dsDeckStyleOn = function(s){ var st = active(); return (!!st && !isEmpty(st)) || !!ovOf(s); };
window.dsDeckStyleT = function(t){
	var st = active(); if (!st || !t) return t;
	if (!hex(st.bg) && !hex(st.ac)) return t;
	var o = {}; for (var k in t) o[k] = t[k];
	if (hex(st.bg)) o.bg = st.bg;
	if (hex(st.ac)) o.ac = st.ac;
	return o;
};
window.dsDeckStyleH = function(H, s){
	if (typeof H !== 'string') return H;
	var st = active(), els = (st && st.els) || {}, ov = ovOf(s), any = false;
	for (var k0 in els) { any = true; break; }
	var roomy = !tplOn() && H.indexOf('border-radius') >= 0;
	if (!any && !ov && !roomy) return H;
	if (any || ov) H = H.replace(/(<[a-zA-Z]+\b[^>]*?\bdata-ed-field="([a-z\-]+)"[^>]*?\bstyle=")([^"]*)"/g, function(m, pre, f, css){
		var e = els[FIELD[f]], o = null;
		if (ov) { var im = /\bdata-ed-idx="(\d+)"/.exec(m); o = ov[keyOf(f, im ? im[1] : '')] || null; }
		if (!e && !o) return m;
		var w = e ? e.w : 0, col = (o && hex(o.c)) || (e && hex(e.c)) || '', sc = (o && sized(o.s)) ? o.s : ((e && e.s) || 1);
		var fm = /font-size:\s*([\d.]+)em/.exec(css), base = fm ? parseFloat(fm[1]) : 1, add = '';
		if (w) add += 'font-weight:' + w + '!important;';
		if (e && e.t) add += 'text-transform:' + (e.t === 'u' ? 'uppercase' : 'capitalize') + '!important;';
		if (col) add += 'color:' + col + '!important;';
		if (sized(sc)) add += 'font-size:' + (base * sc).toFixed(3) + 'em;';
		if (!add) return m;
		return pre + css + (css && !/;\s*$/.test(css) ? ';' : '') + add + '" data-dsb="' + base + '"';
	});
	{                                                /* breathing space in every box; single icons (in the order they appear) and the picture width */
		var hasIcon = false, hasImg = !!(ov && ov.img && sized(ov.img.s) && H.indexOf('dsp-vp-imgzone') >= 0);
		if (ov) for (var k1 in ov) { if (k1.indexOf('icon:') === 0) { hasIcon = true; break; } }
		hasIcon = hasIcon && H.indexOf('api.iconify.design') >= 0;
		if (hasIcon || hasImg || roomy) {
			try {
				var box = document.createElement('template'); box.innerHTML = H;
				if (roomy) breathePad(box.content);
				if (hasIcon) { var imgs = box.content.querySelectorAll(ICON); for (var i = 0; i < imgs.length; i++) { if (ov['icon:' + i]) styleIcon(imgs[i], ov['icon:' + i]); } }
				if (hasImg) { imgApply(box.content, ov.img.s); if (sized(ov.img.f) || ov.img.st || ov.img.hl || ov.img.gc) fitApply(box.content, ov.img.f || 1, !!ov.img.st, ov.img.hl || 0, ov.img.gc || 0); }
				H = box.innerHTML;
			} catch (e2) {}
		}
	}
	return H;
};
window.dsDeckStyleGet = function(){ var st = tplOn() ? null : saved(); return (st && !isEmpty(st)) ? st : null; };

/* ---- the deck font (all weights of the chosen family) ---- */
function applyFont(st){
	if (tplOn()) { tplFont(st); return; }
	var fam = st && st.font && FONTS[st.font] ? st.font : '';
	var tag = $('dsds-font'), link = $('dsds-fontlink');
	if (!fam) { if (tag) tag.remove(); return; }
	var href = 'https://fonts.googleapis.com/css2?family=' + fam.replace(/ /g, '+') + ':wght@' + FONTS[fam].join(';') + '&display=swap';
	if (!link) { link = document.createElement('link'); link.id = 'dsds-fontlink'; link.rel = 'stylesheet'; document.head.appendChild(link); }
	if (link.getAttribute('href') !== href) link.setAttribute('href', href);
	if (!tag) { tag = document.createElement('style'); tag.id = 'dsds-font'; document.head.appendChild(tag); }
	var sel = ['#dsp-ppt-main-slide', '#dsp-ppt-thumbs .dsp-thumb-box', '#vp-list .vp-thumb', '#dsce-prev', '#dsds-prev'].map(function(s){ s = s.replace(/^(#[a-z\-]+)/, '$1$1$1'); return 'html body ' + s + ',html body ' + s + ' *'; }).join(',');   /* the id three times: stronger than the editor's own font rules */
	var css = sel + '{font-family:"' + fam + '",Arial,sans-serif!important;}';
	if (tag.textContent !== css) tag.textContent = css;
}
/* the left-hand thumbnails: each one is the slide drawn again in its own layout (one slide, or all of them) */
function thumbBoxes(){ var th = $('dsp-ppt-thumbs'), a = []; if (!th) return a; for (var i = 0; i < th.children.length; i++) a.push(th.children[i].querySelector('.dsp-thumb-box,[style*="font-size:3"]')); return a; }
function paintThumbs(only){
	if (tplOn()) { tplThumbs(only); return; }
	if (typeof window.dsceRenderSlide !== 'function' || !(window.S && S.data && S.data.slides)) return;
	var b = thumbBoxes();
	for (var i = 0; i < S.data.slides.length; i++) {
		if (!b[i] || (typeof only === 'number' && only !== i)) continue;
		try { b[i].innerHTML = window.dsceRenderSlide(i); b[i].setAttribute('data-dsds', '1'); } catch (e) {}
	}
}
function styled(){ if (tplOn()) return false; if (saved() && !isEmpty(saved())) return true; var sl = (window.S && S.data && S.data.slides) || []; for (var i = 0; i < sl.length; i++) { if (ovOf(sl[i])) return true; } return false; }
function redraw(){
	if (tplOn()) { try { window.DSTE.refreshEditor(); } catch (e0) {} return; }
	paintThumbs();
	try { if (window.S && typeof S.idx === 'number' && S.idx >= 0 && typeof window.applyCanvas === 'function') window.applyCanvas(S.idx, (S.choices && S.choices['slide_' + S.idx]) || 'v1'); } catch (e) {}
}

/* ---- the panel ---- */
function blank(){ return {font:'', bg:'', ac:'', els:{}}; }
function cloneSt(st){ var o = blank(); if (!st) return o; o.font = st.font || ''; o.bg = hex(st.bg); o.ac = hex(st.ac); var e = st.els || {}; ROLES.forEach(function(r){ var x = e[r[0]]; if (x) o.els[r[0]] = {w:x.w || 0, s:x.s || 1, c:hex(x.c), t:(x.t === 'u' || x.t === 't') ? x.t : ''}; }); return o; }
function el(role){ if (!draft.els[role]) draft.els[role] = {w:0, s:1, c:'', t:''}; return draft.els[role]; }
function defCol(role){ var t = styleBase(); return toHex(role === 'sub' ? (hex(draft.ac) || t.ac) : role === 'pd' ? t.bc : t.tc); }
function esc(x){ return String(x == null ? '' : x).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
function weights(){ return draft.font && FONTS[draft.font] ? FONTS[draft.font] : [400, 700, 900]; }
function colCtl(key, val, def){ return '<div class="dsds-col' + (val ? '' : ' dsds-def') + '"><input type="color" data-c="' + key + '" value="' + esc(val || def) + '" title="Pick a colour"/><button type="button" data-cx="' + key + '" title="Back to the default colour">Default</button></div>'; }
function form(){
	var f = $('dsds-form'); if (!f) return;
	var t = styleBase(), W = weights();
	var h = '<label class="dsds-lbl">Font</label><select data-font><option value="">Theme font (default)</option>' + Object.keys(FONTS).map(function(n){ return '<option value="' + esc(n) + '"' + (draft.font === n ? ' selected' : '') + '>' + esc(n) + '</option>'; }).join('') + '</select>';
	h += '<label class="dsds-lbl">Text elements</label><div class="dsds-cols"><span>Element</span><span>Weight</span><span>Case</span><span>Size</span><span>Colour</span></div>';
	ROLES.forEach(function(r){
		var e = draft.els[r[0]] || {w:0, s:1, c:''};
		h += '<div class="dsds-row"><span>' + r[1] + '</span>'
			+ '<select data-w="' + r[0] + '"><option value="0">Default</option>' + W.map(function(w){ return '<option value="' + w + '"' + (e.w === w ? ' selected' : '') + '>' + WNAME[w] + '</option>'; }).join('') + '</select>'
			+ '<select data-t="' + r[0] + '" title="Capital letters"><option value="">As typed</option><option value="u"' + (e.t === 'u' ? ' selected' : '') + '>UPPERCASE</option><option value="t"' + (e.t === 't' ? ' selected' : '') + '>Title Case</option></select>'
			+ '<div class="dsds-step"><button type="button" data-s="' + r[0] + '" data-d="-1" title="Smaller">&minus;</button><b>' + Math.round((e.s || 1) * 100) + '%</b><button type="button" data-s="' + r[0] + '" data-d="1" title="Larger">+</button></div>'
			+ colCtl('el:' + r[0], e.c, defCol(r[0])) + '</div>';
	});
	h += '<label class="dsds-lbl">Slide colours</label><div class="dsds-two"><div><span>Background</span>' + colCtl('bg', draft.bg, toHex(t.bg)) + '</div><div><span>Accent &amp; icons</span>' + colCtl('ac', draft.ac, toHex(t.ac)) + '</div></div>';
	f.innerHTML = h;
}
var pvT = 0;
function preview(){
	clearTimeout(pvT);
	pvT = setTimeout(function(){
		var p = $('dsds-prev'); if (!p || !draft || typeof window.dsceRenderSlide !== 'function') return;
		applyFont(draft);
		var sl = (window.S && S.data && S.data.slides) || [], i = (window.S && S.idx >= 0) ? S.idx : 0;
		if (sl[i] && sl[i].type === 'title') { for (var k = 0; k < sl.length; k++) { if (sl[k].type !== 'title' && sl[k].type !== 'chart' && sl[k].type !== 'quote') { i = k; break; } } }
		try {
			if (tplOn()) { var D = window.DSTE; p.style.fontSize = ''; tplQuiet = true; try { p.innerHTML = D.renderHTML(D.curT(), sl[i], (S.choices && S.choices['slide_' + i]) || 'v1', i, decks()[0], {edit:false}); } finally { tplQuiet = false; } return; }
			var ratio = (typeof window.dsceFontRatio === 'function') ? window.dsceFontRatio() : 0.0272;
			p.style.fontSize = (p.clientWidth * ratio).toFixed(2) + 'px';
			p.innerHTML = String(window.dsceRenderSlide(i)).replace(/contenteditable="true"/g, 'contenteditable="false"');
		} catch (e) {}
	}, 80);
}
function close(){ var o = $('dsds-ov'); if (o) o.remove(); draft = null; applyFont(saved()); document.removeEventListener('keydown', onKey); }
function onKey(e){ if (e.key === 'Escape') close(); }
function open(){
	if ($('dsds-ov') || !(window.S && S.data && S.data.slides && S.data.slides.length)) return;
	draft = cloneSt(saved()); resetAll = false; deselect();
	var o = document.createElement('div'); o.id = 'dsds-ov'; o.className = 'dsds-ov';
	o.innerHTML = '<div class="dsds-m" role="dialog" aria-modal="true"><div class="dsds-hd"><h3>&#127912; Deck style</h3><button type="button" class="dsds-x" data-a="x" aria-label="Close">&#10005;</button></div>'
		+ '<div class="dsds-body"><div><div class="dsds-prev" id="dsds-prev"></div><p class="dsds-hint">Preview of one of your slides. Nothing changes in the deck until you press Save.</p></div><div id="dsds-form"></div></div>'
		+ '<div class="dsds-ft"><button type="button" class="dsds-b" data-a="reset" title="Remove the deck style and every single-object colour / size change: slides look as they were generated">&#8634; Reset to default</button><button type="button" class="dsds-b" data-a="x">Cancel</button><button type="button" class="dsds-b dsds-p" data-a="save">&#10003; Save &amp; apply to all slides</button></div></div>';
	document.body.appendChild(o);
	form(); preview();
	document.addEventListener('keydown', onKey);
	o.addEventListener('mousedown', function(e){ if (e.target === o) close(); });
	o.addEventListener('click', function(e){
		var t = e.target.closest ? e.target.closest('[data-a],[data-s],[data-cx]') : null; if (!t) return;
		if (t.hasAttribute('data-s')) { var x = el(t.getAttribute('data-s')); x.s = Math.max(0.6, Math.min(1.6, Math.round(((x.s || 1) + 0.05 * parseInt(t.getAttribute('data-d'), 10)) * 100) / 100)); form(); preview(); return; }
		if (t.hasAttribute('data-cx')) { var k = t.getAttribute('data-cx'); if (k === 'bg') draft.bg = ''; else if (k === 'ac') draft.ac = ''; else el(k.slice(3)).c = ''; form(); preview(); return; }
		var a = t.getAttribute('data-a');
		if (a === 'x') { close(); return; }
		if (a === 'reset') { draft = blank(); resetAll = true; form(); preview(); return; }
		if (a === 'save') {
			var st = isEmpty(draft) ? null : cloneSt(draft);
			decks().forEach(function(d){ if (st) d.style = JSON.parse(JSON.stringify(st)); else delete d.style; if (resetAll && d.slides) d.slides.forEach(function(s){ if (s) delete s.ov; }); });
			resetAll = false;
			draft = null; var ov = $('dsds-ov'); if (ov) ov.remove(); document.removeEventListener('keydown', onKey);
			applyFont(saved()); redraw();
			if (typeof window.dspToast === 'function') window.dspToast(st ? 'Deck style applied to all slides' : 'Deck style reset to default', 'success');
		}
	});
	o.addEventListener('input', function(e){
		var t = e.target; if (!t || !t.getAttribute) return;
		if (t.hasAttribute('data-c')) { var k = t.getAttribute('data-c'), v = hex(t.value); if (k === 'bg') draft.bg = v; else if (k === 'ac') draft.ac = v; else el(k.slice(3)).c = v; var w = t.parentNode; if (w) w.classList.remove('dsds-def'); preview(); }
	});
	o.addEventListener('change', function(e){
		var t = e.target; if (!t || !t.getAttribute) return;
		if (t.hasAttribute('data-font')) { draft.font = FONTS[t.value] ? t.value : ''; var W = weights(); ROLES.forEach(function(r){ var x = draft.els[r[0]]; if (x && x.w && W.indexOf(x.w) < 0) x.w = W.reduce(function(a, b){ return Math.abs(b - x.w) < Math.abs(a - x.w) ? b : a; }, W[0]); }); form(); preview(); }
		else if (t.hasAttribute('data-w')) { el(t.getAttribute('data-w')).w = parseInt(t.value, 10) || 0; preview(); }
		else if (t.hasAttribute('data-t')) { el(t.getAttribute('data-t')).t = (t.value === 'u' || t.value === 't') ? t.value : ''; preview(); }
	});
}
window.dsDeckStyleOpen = open;

/* ---- one object: click a text or an icon on the slide -> Colour / size / Reset bar ---- */
var sel = null, thT = 0;
function mainEl(){ return $('dsp-ppt-main-slide'); }
function slidesAt(){ var a = [], i = (window.S && typeof S.idx === 'number') ? S.idx : -1; if (i < 0) return a; decks().forEach(function(d){ var s = d.slides && d.slides[i]; if (s && a.indexOf(s) < 0) a.push(s); }); return a; }
function getOv(key){ var s = slidesAt()[0]; return (s && s.ov && s.ov[key]) || null; }
function setOv(key, patch){
	var cur = getOv(key), o = {c: cur ? hex(cur.c) : '', s: (cur && cur.s) || 1, ic: cur ? hex(cur.ic) : ''};
	if (patch) { if ('c' in patch) o.c = hex(patch.c); if ('s' in patch) o.s = patch.s; if ('ic' in patch) o.ic = hex(patch.ic); }
	var keep = !!patch && (o.c || o.ic || sized(o.s));
	slidesAt().forEach(function(s){
		if (keep) { if (!s.ov || typeof s.ov !== 'object') s.ov = {}; s.ov[key] = {c:o.c, s:o.s}; if (o.ic) s.ov[key].ic = o.ic; }
		else if (s.ov) { delete s.ov[key]; var left = false; for (var k in s.ov) { left = true; break; } if (!left) delete s.ov; }
	});
	return o;
}
function thumbsSoon(){ clearTimeout(thT); thT = setTimeout(function(){ paintThumbs(S.idx); }, 450); }
function pick(t){
	var m = mainEl(); if (!m || !t || !t.closest || !m.contains(t)) return null;
	if (tplOn()) {
		var tx = t.closest('[data-ed-field]');
		if (tx) { var rk = tKey(tx.getAttribute('data-ed-field'), tx.getAttribute('data-ed-idx'), tx.getAttribute('data-ed-part')); return rk ? {el:tx, kind:'t', key:rk[1], tpl:1} : null; }
		if (t.tagName !== 'IMG' || !/api\.iconify\.design/.test(t.getAttribute('src') || '')) return null;
		var tn = Array.prototype.indexOf.call(m.querySelectorAll(ICON), t);
		return tn < 0 ? null : {el:t, img:t, kind:'i', key:'icon:' + tn, tpl:1};
	}
	var x = t.closest('[data-ed-field]');
	if (x) { var k = keyOf(x.getAttribute('data-ed-field'), x.getAttribute('data-ed-idx')); return k ? {el:x, kind:'t', key:k} : null; }
	var img = t.tagName === 'IMG' ? t : ((t.children.length === 1 && t.firstElementChild.tagName === 'IMG') ? t.firstElementChild : null);
	if (!img || !/api\.iconify\.design/.test(img.getAttribute('src') || '')) return null;
	var n = Array.prototype.indexOf.call(m.querySelectorAll(ICON), img);
	return n < 0 ? null : {el: badgeOf(img) || img, img: img, kind:'i', key:'icon:' + n};
}
function find(key){
	var m = mainEl(); if (!m) return null;
	if (tplOn()) {
		if (key.indexOf('icon:') === 0) { var ti = m.querySelectorAll(ICON)[parseInt(key.slice(5), 10)]; return ti ? {el:ti, img:ti, kind:'i', key:key, tpl:1} : null; }
		var ta = m.querySelectorAll('[data-ed-field]');
		for (var j = 0; j < ta.length; j++) { var rk = tKey(ta[j].getAttribute('data-ed-field'), ta[j].getAttribute('data-ed-idx'), ta[j].getAttribute('data-ed-part')); if (rk && rk[1] === key) return {el:ta[j], kind:'t', key:key, tpl:1}; }
		return null;
	}
	if (key.indexOf('icon:') === 0) { var img = m.querySelectorAll(ICON)[parseInt(key.slice(5), 10)]; return img ? {el: badgeOf(img) || img, img: img, kind:'i', key:key} : null; }
	var all = m.querySelectorAll('[data-ed-field]');
	for (var i = 0; i < all.length; i++) { if (keyOf(all[i].getAttribute('data-ed-field'), all[i].getAttribute('data-ed-idx')) === key) return {el: all[i], kind:'t', key:key}; }
	return null;
}
function bar(){
	var b = $('dsov-bar'); if (b) return b;
	b = document.createElement('div'); b.id = 'dsov-bar';
	b.innerHTML = '<label data-k="ic" title="Colour of the icon itself" hidden><span class="dsov-sw"></span>Icon<input type="color" value="#ffffff"/></label>'
		+ '<label data-k="c" title="Change colour"><span class="dsov-sw"></span><span class="dsov-t">Colour</span><input type="color" value="#ffffff"/></label><i></i>'
		+ '<button type="button" data-a="-" title="Smaller">A&minus;</button><b>100%</b><button type="button" data-a="+" title="Larger">A+</button><i></i>'
		+ '<button type="button" data-a="r" title="Back to the default colour and size">Reset</button>';
	document.body.appendChild(b);
	b.addEventListener('mousedown', function(e){ if (e.target.tagName !== 'INPUT') e.preventDefault(); });
	b.addEventListener('click', function(e){
		var t = e.target.closest ? e.target.closest('[data-a]') : null; if (!t || !sel) return;
		var a = t.getAttribute('data-a'), key = sel.key;
		if (a === 'r') {
			setOv(key, null);
			try { if (typeof window.applyCanvas === 'function') window.applyCanvas(S.idx, (S.choices && S.choices['slide_' + S.idx]) || 'v1'); } catch (e2) {}
			thumbsSoon(); var p = find(key); if (p) select(p); else deselect();
			return;
		}
		var cur = getOv(key), s = Math.max(0.5, Math.min(2, Math.round((((cur && cur.s) || 1) + (a === '+' ? 0.1 : -0.1)) * 100) / 100));
		live(setOv(key, {s:s})); thumbsSoon(); show();
	});
	Array.prototype.forEach.call(b.querySelectorAll('label'), function(lb){
		var inp = lb.querySelector('input'), k = lb.getAttribute('data-k');
		inp.addEventListener('input', function(){ if (!sel) return; var v = hex(inp.value); if (!v) return; var p = {}; p[k] = v; live(setOv(sel.key, p)); lb.querySelector('.dsov-sw').style.background = v; });
		inp.addEventListener('change', function(){ thumbsSoon(); });
	});
	return b;
}
/* draw the change straight on the open slide (no redraw: the text cursor stays where it is) */
function live(o){
	if (!sel || !sel.el) return;
	if (sel.tpl) {                                   /* a template slide is laid out again, so the layout stays in step with the new size */
		var tk = sel.key; sel.el.classList.remove('dsov-sel'); tplCanvas();
		var tp = find(tk); if (tp) { tp.idx = S.idx; sel = tp; tp.el.classList.add('dsov-sel'); } else deselect();
		return;
	}
	if (sel.kind === 'i') { styleIcon(sel.img, o); return; }
	var x = sel.el, st = active(), e = ((st && st.els) || {})[FIELD[x.getAttribute('data-ed-field')]];
	if (!x.hasAttribute('data-dsb')) { var fm = /([\d.]+)em/.exec(x.style.fontSize || ''); x.setAttribute('data-dsb', fm ? fm[1] : '1'); }
	var base = parseFloat(x.getAttribute('data-dsb')) || 1, sc = sized(o.s) ? o.s : ((e && e.s) || 1);
	x.style.fontSize = (base * sc).toFixed(3) + 'em';
	if (hex(o.c)) x.style.setProperty('color', o.c, 'important');
}
function rgbHex(v){ var m = /rgba?\((\d+),\s*(\d+),\s*(\d+)/.exec(v || ''); return m ? '#' + [m[1], m[2], m[3]].map(function(n){ return ('0' + parseInt(n, 10).toString(16)).slice(-2); }).join('') : '#ffffff'; }
function srcCol(img){ var q = /[?&]color=(?:%23|#)([0-9a-fA-F]{6})/.exec(img.getAttribute('src') || ''); return q ? '#' + q[1].toLowerCase() : '#ffffff'; }
function show(){
	if (!sel) return;
	var b = bar(), o = getOv(sel.key), ti = (sel.tpl && sel.kind === 'i') ? tplIcon(sel.key) : null, badge = ti ? ti.badge : (sel.kind === 'i' && sel.el !== sel.img);
	var lc = b.querySelector('label[data-k="c"]'), li = b.querySelector('label[data-k="ic"]');
	var col = ti ? (ti.badge ? ti.bg : ti.ic) : (sel.kind === 'i' ? (badge ? rgbHex(getComputedStyle(sel.el).backgroundColor) : srcCol(sel.img)) : rgbHex(getComputedStyle(sel.el).color));
	li.hidden = !badge;                              /* an icon on a round background: one picker for the icon, one for the background */
	lc.querySelector('.dsov-t').textContent = badge ? 'Background' : 'Colour';
	lc.querySelector('.dsov-sw').style.background = col;
	if (document.activeElement !== lc.querySelector('input')) lc.querySelector('input').value = col;
	if (badge) { var ic = ti ? ti.ic : srcCol(sel.img); li.querySelector('.dsov-sw').style.background = ic; if (document.activeElement !== li.querySelector('input')) li.querySelector('input').value = ic; }
	b.querySelector('b').textContent = Math.round(((o && o.s) || 1) * 100) + '%';
	b.style.display = 'flex';
	place();
}
function place(){
	var b = $('dsov-bar'); if (!b || !sel || b.style.display === 'none') return;
	var r = sel.el.getBoundingClientRect(), w = b.offsetWidth, h = b.offsetHeight;
	var top = r.top - h - 10; if (top < 8) top = Math.min(window.innerHeight - h - 8, r.bottom + 10);
	b.style.left = Math.max(8, Math.min(window.innerWidth - w - 8, r.left)) + 'px';
	b.style.top = top + 'px';
}
function select(p){
	if (sel && sel.el && sel.el !== p.el) sel.el.classList.remove('dsov-sel');
	sel = p; sel.idx = S.idx; p.el.classList.add('dsov-sel'); show();
}
function deselect(){
	if (sel && sel.el) sel.el.classList.remove('dsov-sel');
	sel = null; var b = $('dsov-bar'); if (b) b.style.display = 'none';
}
document.addEventListener('click', function(e){
	var b = $('dsov-bar'); if (b && b.contains(e.target)) return;
	var p = pick(e.target); if (p) select(p); else if (sel) deselect();
}, true);
/* pressing anywhere outside the slide (Download, Save, a menu...) lets go of the object, so its outline never reaches an export */
function away(e){ if (!sel) return; var b = $('dsov-bar'); if (b && b.contains(e.target)) return; var m = mainEl(); if (m && m.contains(e.target)) return; deselect(); }
window.addEventListener('mousedown', away, true);
window.addEventListener('touchstart', away, true);
document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && sel) deselect(); });
window.addEventListener('scroll', place, true);
window.addEventListener('resize', place);
setInterval(function(){
	if (!sel) return;
	if (!window.S || S.idx !== sel.idx || !!sel.tpl !== tplOn()) { deselect(); return; }
	if (!document.body.contains(sel.el)) { var p = find(sel.key); if (p) select(p); else deselect(); return; }
	place();
}, 400);

/* ---- picture section: "- Image size +" and a drag handle on the picture of the open slide ---- */
function rerender(){
	try { if (typeof window.applyCanvas === 'function') window.applyCanvas(S.idx, (S.choices && S.choices['slide_' + S.idx]) || 'v1'); } catch (e) {}
	paintThumbs(S.idx);
	imgTools();                                      /* the buttons are back at once, so a quick second click still lands on them */
	if (!tplOn()) { clearTimeout(fitT); fitT = setTimeout(fitStore, 60); }   /* the cards re-fit themselves to the new width, once the slide has settled */
}
function imgSet(w1, z){
	w1 = Math.max(12, Math.min(60, w1));
	var s = Math.round(w1 / z.w0 * 1000) / 1000;
	var old = getOv('img'), of = old ? old.f : 0, ost = old ? old.st : 0, ohl = old ? old.hl : 0, ogc = old ? old.gc : 0;
	setOv('img', sized(s) ? {s:s} : null);
	if (sized(s)) slidesAt().forEach(function(sl){ if (sl.ov && sl.ov.img) { sl.ov.img.w = Math.round(w1 * 10) / 10; if (of) sl.ov.img.f = of; if (ost) sl.ov.img.st = ost; if (ohl) sl.ov.img.hl = ohl; if (ogc) sl.ov.img.gc = ogc; } });   /* the same width, in %, for the PowerPoint download */
	return s;
}
function zoneNow(){ return tplOn() ? tplZone() : sideZone(mainEl()); }
var imRaf = 0;
function imgTools(){
	var m = mainEl(); if (!m || !(window.S && S.data && S.data.slides && S.data.slides[S.idx])) return;
	var z = zoneNow(); if (!z || z.el.querySelector('.dsds-imctl')) return;
	var ctl = document.createElement('div'); ctl.className = 'dsds-imctl'; ctl.style[z.side === 'right' ? 'right' : 'left'] = '8px';
	ctl.innerHTML = '<button type="button" data-im="-" title="Make the picture narrower">&minus;</button><span>Image size</span><button type="button" data-im="+" title="Make the picture wider">+</button>' + (getOv('img') ? '<button type="button" data-im="r" title="Back to the normal picture width">Reset</button>' : '');
	var grip = document.createElement('div'); grip.className = 'dsds-imgrip'; grip.title = 'Drag to make the picture wider or narrower'; grip.style[z.side === 'right' ? 'left' : 'right'] = '0';
	var stop = function(e){ e.stopPropagation(); };
	['mousedown', 'mouseup', 'touchstart', 'pointerup', 'dblclick'].forEach(function(n){ ctl.addEventListener(n, stop); grip.addEventListener(n, stop); });
	grip.addEventListener('click', stop);
	ctl.addEventListener('pointerdown', stop);
	ctl.addEventListener('click', function(e){
		e.stopPropagation(); e.preventDefault();
		var t = e.target.closest ? e.target.closest('[data-im]') : null; if (!t) return;
		var a = t.getAttribute('data-im'), zi = zoneNow(); if (!zi) return;
		if (a === 'r') setOv('img', null); else imgSet(zi.now + (a === '+' ? 3 : -3), zi);
		rerender();
	});
	grip.addEventListener('pointerdown', function(e){
		e.stopPropagation(); e.preventDefault();
		var zi = zoneNow(); if (!zi) return;
		var r = mainEl().getBoundingClientRect(), s = 1; grip.classList.add('dsds-drag');
		var shield = document.createElement('div'); shield.setAttribute('style', 'position:fixed;inset:0;z-index:2147482500;cursor:ew-resize;'); document.body.appendChild(shield);
		var move = function(ev){
			var w1 = (zi.side === 'right' ? (r.right - ev.clientX) : (ev.clientX - r.left)) / r.width * 100;
			s = imgSet(w1, zi);
			if (tplOn()) { if (!imRaf) imRaf = requestAnimationFrame(function(){ imRaf = 0; tplCanvas(); }); } else imgApply(mainEl(), s);
		};
		var up = function(){
			window.removeEventListener('pointermove', move, true); window.removeEventListener('pointerup', up, true); window.removeEventListener('pointercancel', up, true);
			shield.remove(); grip.classList.remove('dsds-drag'); rerender();
		};
		window.addEventListener('pointermove', move, true); window.addEventListener('pointerup', up, true); window.addEventListener('pointercancel', up, true);
	});
	z.el.appendChild(ctl); z.el.appendChild(grip);
}
setInterval(imgTools, 500);

/* =====================================================================
   DESIGN TEMPLATES (Pick a design): the same settings on template decks.
   Templates are drawn by the template engine (DSTE) as a list of shapes that
   the editor, the thumbnails, PDF / PNG / JPG and the PowerPoint download all
   share, so every change below is made on that list.
   ===================================================================== */
var TW = 1280, TQ = 720, TPG = {}, pptMode = false, tplQuiet = false;
function tKey(f, idx, part){
	var i = parseInt(idx, 10);
	if (f === 'title' || f === 'x-tpart') return ['title', 'title'];
	if (f === 'subtitle') return ['sub', 'sub'];
	if (f === 'x-conc') return ['tk', 'tk'];
	if (/^tf-[a-z]+$/.test(f)) return ['', 'tf:' + f.slice(3)];
	if (isNaN(i)) return null;
	if (f === 'content-plain') return ['pd', 'd:' + i];
	if (f === 'x-part') { var p = parseInt(part, 10) || 0; return p === 0 ? ['ph', 'h:' + i] : ['pd', (p === 1 ? 'd:' : 'x:') + i]; }
	return null;
}
function tLum(c){ c = String(c || '').replace('#', ''); if (c.length !== 6) return 0.5; return (0.299 * parseInt(c.slice(0, 2), 16) + 0.587 * parseInt(c.slice(2, 4), 16) + 0.114 * parseInt(c.slice(4, 6), 16)) / 255; }
function tHex(c){ var m = /#[0-9a-fA-F]{6}/.exec(String(c || '')); return m ? m[0].toUpperCase() : ''; }
function tCase(s){ return String(s).replace(/(^|[\s\-(\/"“])([a-zà-þ])/g, function(m, a, b){ return a + b.toUpperCase(); }); }
/* the template of the open deck, with its colour theme but without the deck style */
var tplPlain = null;
function tplBase(){
	var D = window.DSTE, d = decks()[0]; if (!D || !d || !d.tpl || !D.reg || !D.reg[d.tpl]) return null;
	return tplPlain ? tplPlain(D.reg[d.tpl], d.tplTheme || '') : D.reg[d.tpl];
}
function tplLines(e, w, size){
	var F = window.DSTE.fit;
	return F({text:e.text, w:w, h:99999, max:size, min:size, font:e.fam, wt:e.wt, lh:e.lh, caps:e.caps, ls:e.ls, maxLines:0}).n;
}
/* text that no longer fits its box is made smaller (never cut) */
function tplRefit(e, strict){
	var F = window.DSTE.fit, lh = e.lh || 1.2, h = e.h;
	if (!strict) h = e._room || Math.max(h, e._need || 0);              /* the room the text really has: down to the next thing under it */
	var r = F({text:e.text, w:e.w, h:h, max:e.size, min:Math.max(6, e.size * 0.55), font:e.fam, wt:e.wt, lh:lh, caps:e.caps, ls:e.ls, maxLines:e.nw ? 1 : 0});
	if (r.s < e.size) e.size = r.s;
}
function tplSide(els){
	for (var i = 0; i < els.length; i++) {
		var e = els[i]; if (e.t !== 'img' || e.shape === 'ell') continue;
		if (e.h < TQ * 0.6 || e.w > TW * 0.62 || e.w < TW * 0.1) continue;
		var side = (e.x + e.w >= TW * 0.88) ? 'right' : (e.x <= TW * 0.12 ? 'left' : '');
		if (side) return {e:e, side:side};
	}
	return null;
}
/* picture down the left / right side made wider or narrower: the rest of the slide takes what is left */
function tplImg(page, slide){
	var z = tplSide(page.els); if (!z) return;
	var im = z.e, w0 = im.w, o = slide && slide.ov && slide.ov.img;
	page._img = {side:z.side, w0:w0 / TW * 100, now:w0 / TW * 100};
	if (!o || !sized(o.s)) return;
	var w1 = Math.max(0.12 * TW, Math.min(0.6 * TW, w0 * o.s));
	if (Math.abs(w1 - w0) < 1) return;
	var x0 = im.x, r = w1 / w0, right = z.side === 'right', nx = right ? (x0 + w0 - w1) : x0;
	var a0 = right ? x0 : (TW - (x0 + w0)), a1 = right ? nx : (TW - (nx + w1)), k = a1 / a0;
	if (!(a0 > 40) || !(k > 0.3)) return;
	var f = k < 1 ? Math.max(0.62, k) : 1;
	var mapC = function(x){ return right ? x * k : TW - (TW - x) * k; };
	var mapI = function(x){ return nx + (x - x0) * r; };
	var onImg = function(cx, cy){ return cx >= x0 && cx <= x0 + w0 && cy >= im.y && cy <= im.y + im.h; };
	var isRound = function(e){ return e.t === 'icon' || e.t === 'ell' || e.t === 'ring' || ((e.t === 'rect' || e.t === 'shape') && Math.abs(e.w - e.h) < 2 && e.w <= 150); };
	var rounds = page.els.filter(function(e){ return e !== im && isRound(e) && !e._lg; }).map(function(e){ return {cx:e.x + e.w / 2, cy:e.y + e.h / 2, w:e.w}; });
	page.els.forEach(function(e){
		if (e === im || e._lg) return;
		if (e.t === 'line') {
			if (Math.abs(e.x2 - e.x1) >= TW * 0.9) return;
			var ml = onImg((e.x1 + e.x2) / 2, (e.y1 + e.y2) / 2) ? mapI : mapC; e.x1 = ml(e.x1); e.x2 = ml(e.x2); return;
		}
		if (e.t === 'path') {
			if (!e.d || !e.d.length) return;
			var lo = 1e9, hi = -1e9, ys = 0, n = 0;
			e.d.forEach(function(c){ for (var j = 1; j + 1 < c.length; j += 2) { lo = Math.min(lo, c[j]); hi = Math.max(hi, c[j]); ys += c[j + 1]; n++; } });
			if (!n || hi - lo >= TW * 0.9) return;
			var mp = onImg((lo + hi) / 2, ys / n) ? mapI : mapC;
			e.d = e.d.map(function(c){ c = c.slice(); for (var j = 1; j + 1 < c.length; j += 2) c[j] = mp(c[j]); return c; });
			return;
		}
		if (typeof e.w !== 'number' || e.w >= TW * 0.9) return;        /* bands across the whole slide stay */
		if (e.y >= TQ - 48 && e.h <= 30) return;                        /* so does the footer line (company name, page number) */
		var cx = e.x + e.w / 2, cy = e.y + e.h / 2;
		if (onImg(cx, cy)) {                                           /* frames, labels and marks that sit on the picture go with it */
			if (isRound(e)) { e.x = mapI(cx) - e.w / 2; return; }
			e.x = mapI(e.x); e.w *= r; if (e.t === 'text') tplRefit(e);
			return;
		}
		if (isRound(e)) { var nc = mapC(cx); e.w *= f; e.h *= f; e.x = nc - e.w / 2; e.y = cy - e.h / 2; if (e.r) e.r *= f; if (e.th) e.th *= f; return; }
		var a = mapC(e.x), b = mapC(e.x + e.w); e.x = Math.min(a, b); e.w = Math.abs(b - a);
		if (e.t === 'text') {
			for (var q = 0; q < rounds.length; q++) { if (Math.abs(rounds[q].cx - cx) < 2 && Math.abs(rounds[q].cy - cy) < 2 && e.w / k <= rounds[q].w * 1.6) { e.size *= f; break; } }   /* the number inside a round badge */
			tplRefit(e);
		}
	});
	im.x = nx; im.w = w1;
	page._img.now = w1 / TW * 100;
}
/* breathing space: text and icons keep a margin from the edges of the box they sit in; text that then does not fit is made smaller */
function tplBreathe(page){
	var els = page.els, boxes = [];
	els.forEach(function(e, i){
		if (e.t === 'rect' && !e._lg && !e.rot && e.w >= 90 && e.h >= 44 && !(e.w >= TW * 0.9 && e.h >= TQ * 0.9) && (e.fill || e.grad || e.line)) boxes.push({e:e, i:i});
	});
	if (!boxes.length) return;
	var boxFor = function(x, y, w, h, i){
		var best = null;
		for (var b = 0; b < boxes.length; b++) {
			var B = boxes[b].e; if (boxes[b].i >= i) continue;
			var ox = Math.min(x + w, B.x + B.w) - Math.max(x, B.x), oy = Math.min(y + h, B.y + B.h) - Math.max(y, B.y);
			if (ox <= 0 || oy <= 0 || ox * oy < w * h * 0.85) continue;
			if (!best || B.w * B.h < best.w * best.h) best = B;
		}
		return best;
	};
	var padOf = function(B){ return Math.max(6, Math.min(14, Math.min(B.w, B.h) * 0.07)); };
	/* icons (with the round or square mark behind them) */
	els.forEach(function(e, i){
		if (e.t !== 'icon' || e._lg) return;
		var g = e, gi = i;
		if (e._k) { for (var j = i - 1; j >= 0 && j >= i - 3; j--) { if (els[j]._bk === e._k) { g = els[j]; gi = j; break; } } }
		var B = boxFor(g.x, g.y, g.w, g.h, gi); if (!B) return;
		if (g.x < B.x + 1.5 || g.y < B.y + 1.5 || g.x + g.w > B.x + B.w - 1.5 || g.y + g.h > B.y + B.h - 1.5) return;   /* on the edge on purpose */
		var p = padOf(B), dx = 0, dy = 0;
		if (g.x < B.x + p) dx = B.x + p - g.x; else if (g.x + g.w > B.x + B.w - p) dx = B.x + B.w - p - (g.x + g.w);
		if (g.y < B.y + p) dy = B.y + p - g.y; else if (g.y + g.h > B.y + B.h - p) dy = B.y + B.h - p - (g.y + g.h);
		if (Math.abs(dx) < 0.5 && Math.abs(dy) < 0.5) return;
		var ox = g.x, oy = g.y;
		if (g !== e) { g.x += dx; g.y += dy; }
		e.x += dx; e.y += dy;
		els.forEach(function(t){                                       /* the text next to (or under) it moves on, so nothing overlaps */
			if (t.t !== 'text') return;
			if (dx > 0 && t.x >= ox + g.w - 2 && t.x < g.x + g.w + 4 && t.y < g.y + g.h && t.y + t.h > g.y) { var mx = g.x + g.w + 6 - t.x; if (mx > 0 && t.w - mx > 30) { t.x += mx; t.w -= mx; tplRefit(t); } }
			if (dy > 0 && t.y >= oy + g.h - 2 && t.y < g.y + g.h + 4 && t.x < g.x + g.w && t.x + t.w > g.x) { var my = g.y + g.h + 4 - t.y; if (my > 0 && t.h - my > 10) { t.y += my; t.h -= my; tplRefit(t, true); } }
		});
	});
	/* text */
	els.forEach(function(e, i){
		if (e.t !== 'text' || e._lg || !e.text) return;
		var B = boxFor(e.x, e.y, e.w, e.h, i); if (!B) return;
		var p = padOf(B), L = B.x + p, R = B.x + B.w - p, T = B.y + p, U = B.y + B.h - p, ch = false, strict = false, lh = e.lh || 1.2;
		if (e.align === 'c') { var cut = Math.max(0, L - e.x, e.x + e.w - R); if (cut > 0.5 && e.w - 2 * cut > 24) { e.x += cut; e.w -= 2 * cut; ch = true; } }
		else {
			if (e.x < L - 0.5) { e.w -= L - e.x; e.x = L; ch = true; }
			if (e.x + e.w > R + 0.5) { e.w = R - e.x; ch = true; }
		}
		if (e.w < 24) return;
		var need = tplLines(e, e.w, e.size) * e.size * lh, sl = e.size * 0.15;   /* sl: the empty part of a line above and below the letters */
		T -= sl; U += sl;
		if (e.va === 'm') { var cy = e.y + e.h / 2, al = 2 * Math.min(cy - T, U - cy); if (al > 8 && need > al + 0.5) { e.y = cy - al / 2; e.h = al; ch = true; strict = true; } }
		else if (e.va === 'b') {
			if (e.y + e.h > U + 0.5) { e.h = U - e.y; ch = true; }
			if (e.y + e.h - need < T - 0.5) { var nb = e.y + e.h; e.y = Math.max(e.y, T); e.h = nb - e.y; ch = true; strict = true; }
		}
		else {
			if (e.y < T - 0.5) { var dy = T - e.y; e.h -= dy; e.y = T; if (e._room) e._room = Math.max(8, e._room - dy); ch = true; }
			if (e.y + need > U + 0.5) { e.h = U - e.y; ch = true; strict = true; }
		}
		if (ch && e.h > 8) { var s0 = e.size; tplRefit(e, strict); page._br = (page._br || 0) + 1; if (e.size < s0) page._bs = Math.min(page._bs || 1, e.size / s0); }
	});
}
/* the soft glow around the logo badge (Logo settings: Glow), drawn as a few faint layers so PowerPoint shows it too */
function tplGlow(page, deck, cover){
	var L = deck && deck.logo, els = page.els; if (!L) return;
	for (var i = els.length - 1; i > 0; i--) {
		if (els[i].t !== 'logo') continue;
		var b = els[i - 1]; if (!b || !b._lg || b.t !== 'rect') return;
		var g = cover ? L.glow : L.sglow; g = (typeof g === 'number') ? Math.max(0, Math.min(100, g)) : 50;
		if (g < 1) return;
		var col = tHex(b.line) || '#3B82F6', k = g / 50, add = [];
		[[18, 0.016], [15.5, 0.02], [13, 0.024], [10.5, 0.03], [8, 0.036], [6, 0.042], [4, 0.05], [2, 0.06]].forEach(function(l){ var d = l[0] * (0.6 + 0.4 * k); add.push({t:'rect', x:b.x - d, y:b.y - d, w:b.w + 2 * d, h:b.h + 2 * d, r:(b.r || 0) + d, fill:col, op:Math.min(0.5, l[1] * k), _lg:1}); });
		els.splice.apply(els, [i - 1, 0].concat(add));
		return;
	}
}
function tplPost(page, slide, idx, deck){
	var els = page.els, i;
	for (i = els.length - 1; i > 0; i--) { if (els[i].t === 'logo') { els[i]._lg = 1; var b = els[i - 1]; if (b && b.t === 'rect' && b.x <= els[i].x + 0.5 && b.y <= els[i].y + 0.5 && b.x + b.w >= els[i].x + els[i].w - 0.5 && b.y + b.h >= els[i].y + els[i].h - 0.5 && b.w < TW * 0.6) b._lg = 1; break; } }
	/* a text that was made bigger stops growing where the next text under it starts */
	els.forEach(function(e){
		if (e.t !== 'text' || !e._b0 || (e.va && e.va !== 't')) return;
		var lh = e.lh || 1.2, need0 = tplLines(e, e.w * 0.94 / 0.94, e._b0) * e._b0 * lh, lim = 1e9;
		for (var j = 0; j < els.length; j++) { var o = els[j]; if (o === e || o.t !== 'text' || !o.text) continue; if (o.y >= e.y + need0 - 2 && o.x < e.x + e.w && o.x + o.w > e.x) lim = Math.min(lim, o.y); }
		while (e.size > e._b0 && e.y + tplLines(e, e.w, e.size) * e.size * lh > lim + 1) e.size = Math.max(e._b0, e.size - 0.5);
		e._n = tplLines(e, e.w, e.size);
	});
	els.forEach(function(e){ if (e.t === 'text') e._s0 = e.size; });
	els.forEach(function(e){
		if (e.t !== 'text' || e._need) return;
		var need = e._need = (e._n || 1) * e.size * (e.lh || 1.2), room = e.h;
		for (var j = 0; j < els.length; j++) {                           /* how far down the text may grow before it meets the next text, icon or rule */
			var o = els[j]; if (o === e || typeof o.w !== 'number') continue;
			if (!((o.t === 'text' && o.text) || o.t === 'icon' || o.t === 'ring' || o.t === 'chart' || (o.t === 'rect' && o.h <= 8))) continue;
			if (o.y >= e.y + need - 2 && o.x < e.x + e.w && o.x + o.w > e.x) room = Math.min(room, o.y - e.y - 2);
		}
		e._room = Math.max(need, room);
	});
	/* single icons, in the order they appear: colour of the drawing, colour of the mark behind it, size */
	var ic = 0, ovs = (slide && slide.ov) || null;
	els.forEach(function(e, n){
		if (e.t !== 'icon' || e._lg) return;
		var key = 'icon:' + (ic++), pv = els[n - 1], badge = null, ov = ovs && ovs[key];
		if (pv && (pv.t === 'ell' || pv.t === 'rect' || pv.t === 'shape') && pv.w <= e.w * 3.2 && pv.h <= e.h * 3.2 && pv.x <= e.x + 0.5 && pv.y <= e.y + 0.5 && pv.x + pv.w >= e.x + e.w - 0.5 && pv.y + pv.h >= e.y + e.h - 0.5) badge = pv;
		e._k = key; if (badge) badge._bk = key;
		if (!ov) return;
		if (sized(ov.s)) { [e, badge].forEach(function(g){ if (!g) return; g.x -= g.w * (ov.s - 1) / 2; g.y -= g.h * (ov.s - 1) / 2; g.w *= ov.s; g.h *= ov.s; if (g.r) g.r *= ov.s; }); }
		if (badge) {
			if (hex(ov.c)) { if (badge.fill || badge.grad || !badge.line) { badge.fill = ov.c; delete badge.grad; } else badge.line = ov.c; }
			if (hex(ov.ic)) e.color = ov.ic;
		} else { var c1 = hex(ov.ic) || hex(ov.c); if (c1) e.color = c1; }
	});
	tplImg(page, slide);
	tplBreathe(page);
	/* points that started at one size stay at one size: the smallest one any of them needed */
	var grp = {};
	els.forEach(function(e){ if (e.t !== 'text' || !e._k || !/^[hdx]:/.test(e._k) || (ovs && ovs[e._k] && sized(ovs[e._k].s))) return; var g = e._k.charAt(0) + '|' + Math.round(e._s0 * 10); if (!grp[g] || e.size < grp[g]) grp[g] = e.size; });
	els.forEach(function(e){ if (e.t !== 'text' || !e._k || !/^[hdx]:/.test(e._k) || (ovs && ovs[e._k] && sized(ovs[e._k].s))) return; var g = e._k.charAt(0) + '|' + Math.round(e._s0 * 10); if (grp[g] < e.size) e.size = grp[g]; });
	els.forEach(function(e){
		if (e._dsc) { e.color = '#' + String(e._dsc).slice(1); }
		if (e._tc && e.t === 'text') { if (pptMode) e.text = tCase(e.text); else e.fam = e.fam + "',Arial,sans-serif!important;text-transform:capitalize;font-family:'" + e.fam; }
	});
	tplGlow(page, deck, page.kind === 'cover');
	if (typeof idx === 'number' && idx >= 0 && !tplQuiet) TPG[idx] = page;
}
function tplHook(){
	var D = window.DSTE; if (!D || D.__dsStyle || !D.Ctx || !D.renderPage || !D.themed || !D.fit) return;
	D.__dsStyle = 1;
	var CP = D.Ctx.prototype, oText = CP.text, oPage = D.renderPage, oThemed = D.themed, oLogo = D.addLogo;
	tplPlain = function(T, key){ return oThemed.call(D, T, key); };

	/* deck style and single-object changes go in while the template lays the slide out, so the layout stays in step */
	CP.text = function(x, y, w, h, text, o){
		var rk = (o && o.ed) ? tKey(o.ed.f, o.ed.i, o.ed.p) : null;
		if (!rk) return oText.call(this, x, y, w, h, text, o);
		var st = activeAny(), e0 = (rk[0] && st && st.els && st.els[rk[0]]) || null, ov = (this.s && this.s.ov && this.s.ov[rk[1]]) || null, e;
		var sc = (ov && sized(ov.s)) ? ov.s : ((e0 && e0.s) || 1);
		if (sized(sc) || (e0 && (e0.w || e0.t === 'u'))) {
			var q = {}, k; for (k in o) q[k] = o[k];
			var self = this, mo = function(p, c){ return self.measureText(text, w, {font:p.font, wt:p.wt, lh:p.lh, caps:p.caps, ls:p.ls, max:c, min:c - 0.01, h:h, maxLines:p.maxLines}).s === c; };
			var base, fitted = true;
			if (o.fit || !o.size) { var f0 = o.fit || {max:o.size, min:Math.max(9, Math.round(o.size * 0.6))}; base = this.measureText(text, w, {font:o.font, wt:o.wt, lh:o.lh, caps:o.caps, ls:o.ls, max:f0.max, min:f0.min, h:h, maxLines:o.maxLines}).s; }
			else base = o.size;
			fitted = mo(o, base);
			if (e0 && e0.w) q.wt = e0.w;
			if (e0 && e0.t === 'u') q.caps = true;
			var target = base * sc, size = target;
			if (fitted) { var floor = Math.min(base, target) * 0.6; while (size > floor && !mo(q, size)) size -= 0.5; if (size <= floor) size = Math.max(floor, 6); }
			q.size = Math.round(size * 100) / 100; delete q.fit;
			e = oText.call(this, x, y, w, h, text, q);
			if (q.size > base + 0.01) e._b0 = base;
		} else e = oText.call(this, x, y, w, h, text, o);
		e._k = rk[1];
		var col = (ov && hex(ov.c)) || (e0 && hex(e0.c)) || '';
		if (col) e._dsc = 'x' + col.slice(1);
		if (e0 && e0.t === 't') e._tc = 1;
		return e;
	};
	/* deck font, background and accent: the template is recoloured the same way a colour theme recolours it */
	D.themed = function(T, key){
		var B = oThemed.call(this, T, key), st = activeAny();
		if (!T || !st) return B;
		var font = (st.font && FONTS[st.font]) ? st.font : '', bg = hex(st.bg), ac = hex(st.ac);
		if (!font && !bg && !ac) return B;
		var sig = (key || '') + '|' + font + '|' + bg + '|' + ac;
		T.__dsc = T.__dsc || {};
		if (T.__dsc[sig]) return T.__dsc[sig];
		var R = B;
		if (bg || ac) {
			var th = key && D.THEMES && D.THEMES[key], P = T.pal || {};
			var b1 = bg || (th ? th[2] : (tHex(P.bg) || '#FFFFFF')), a1 = ac || (th ? th[0] : (tHex(P.accent) || '#3B82F6'));
			var tt = th ? th[3] : (tHex(P.title || P.text) || '#111827'), tx = th ? th[4] : (tHex(P.text) || '#111827');
			if (bg) { var lb = tLum(bg); if (Math.abs(tLum(tt) - lb) < 0.45) tt = lb < 0.5 ? '#FFFFFF' : '#0F172A'; if (Math.abs(tLum(tx) - lb) < 0.4) tx = lb < 0.5 ? '#D5DCE6' : '#334155'; }
			var name = '__ds' + sig, had = D.THEMES[name];
			D.THEMES[name] = [a1, th ? th[1] : T.fonts.head, b1, tt, tx];
			try { R = oThemed.call(this, T, name); } finally { if (!had) delete D.THEMES[name]; }
			if (R && R !== T) { R.__themeKey = key || ''; if (!th) { R.fonts = {}; for (var k1 in T.fonts) R.fonts[k1] = T.fonts[k1]; } }
		}
		if (font) {
			var F = Object.create(R); F.fonts = {}; for (var k2 in R.fonts) F.fonts[k2] = R.fonts[k2];
			F.fonts.head = font; F.fonts.body = font;
			if ((F.fonts.headW || 400) < 600) F.fonts.headW = 700;      /* a heavy display face was drawn at "regular": the new family needs a real bold */
			R = F;
		}
		T.__dsc[sig] = R;
		return R;
	};
	/* a second (short) logo for the slides after the cover */
	if (typeof oLogo === 'function') D.addLogo = function(C, kind){
		var d = C.deck, L = d && d.logo;
		if (!L || !L.src2 || kind === 'cover') return oLogo.call(this, C, kind);
		var L2 = {}, k; for (k in L) L2[k] = L[k];
		L2.src = L.src2; L2.w = L.w2 || L.w; L2.h = L.h2 || L.h;
		var d2 = Object.create(d); d2.logo = L2; C.deck = d2;
		var r; try { r = oLogo.call(this, C, kind); } finally { C.deck = d; }
		for (var i = C.els.length - 1; i >= 0; i--) { if (C.els[i].t === 'logo') { C.els[i].alt = 1; break; } }
		return r;
	};
	D.renderPage = function(T, slide, vid, idx, deck){
		var page = oPage.apply(this, arguments);
		try { tplPost(page, slide, idx, deck); } catch (e) { if (window.console) console.warn('[DS deck style] template', e); }
		return page;
	};
}
/* the download: runs outside the template engine's own request step, so the pages it builds know they are for PowerPoint */
function tplFetch(){
	if (window.__dsStyleFetch || !window.__dsteFetch) return; window.__dsStyleFetch = 1;
	var prev = window.fetch;
	window.fetch = function(url, opts){
		var us = typeof url === 'string' ? url : ((url && url.url) || '');
		if (/\/ds\/v1\/build-pptx(-v2)?(\?|$)/.test(us) && tplOn() && opts && typeof opts.body === 'string' && opts.body.charAt(0) === '{') {
			try {
				var b = JSON.parse(opts.body), d = decks()[0], ch = false, L = d && d.logo, st = saved();
				if (L && typeof L.src2 === 'string' && L.src2) { b.tpl_logo2 = {src:L.src2}; ch = true; }
				if (st && st.font && FONTS[st.font]) { b.tpl_fonts = {head:st.font, body:st.font}; ch = true; }
				if (ch) { var no = {}; for (var k in opts) no[k] = opts[k]; no.body = JSON.stringify(b); opts = no; }
			} catch (e) {}
			pptMode = true;
			try { return prev.apply(this, [url, opts]); } finally { pptMode = false; }
		}
		return prev.apply(this, arguments);
	};
}
function tplThumbs(only){
	var D = window.DSTE, d = decks()[0], th = $('dsp-ppt-thumbs'); if (!D || !d || !d.slides || !th) return;
	var T = D.curT(), ch = (window.S && S.choices) || {};
	for (var i = 0; i < th.children.length; i++) {
		if (typeof only === 'number' && only !== i) continue;
		var box = th.children[i].querySelector('.dsp-thumb-box'); if (!box || !d.slides[i]) continue;
		tplQuiet = true;
		try { box.innerHTML = D.renderHTML(T, d.slides[i], ch['slide_' + i] || 'v1', i, d, {edit:false}); } catch (e) {} finally { tplQuiet = false; }
	}
}
function tplCanvas(){ try { if (typeof window.applyCanvas === 'function') window.applyCanvas(S.idx, (S.choices && S.choices['slide_' + S.idx]) || 'v1'); } catch (e) {} }
function tplIcon(key){
	var pg = TPG[window.S ? S.idx : -1], out = {badge:false, bg:'#ffffff', ic:'#ffffff'}; if (!pg) return out;
	pg.els.forEach(function(e){
		if (e._k === key && e.t === 'icon') out.ic = (tHex(e.color) || '#FFFFFF').toLowerCase();
		if (e._bk === key) { out.badge = true; out.bg = (tHex(e.fill) || tHex(e.grad && e.grad.stops && e.grad.stops[0] && e.grad.stops[0][1]) || tHex(e.line) || '#FFFFFF').toLowerCase(); }
	});
	return out;
}
function tplZone(){
	var m = mainEl(), pg = TPG[window.S ? S.idx : -1]; if (!m || !pg || !pg._img) return null;
	var z = m.querySelector('.dsp-vp-imgzone'); if (!z) return null;
	return {el:z, side:pg._img.side, w0:pg._img.w0, now:pg._img.now};
}
function styleBase(){
	if (!tplOn()) return themeNow();
	var T = tplBase(), P = (T && T.pal) || {}, R = T && T.__rec;
	if (R) return {bg:R.bg, tc:R.title, bc:R.text, ac:R.acx};
	return {bg:tHex(P.bg) || '#FFFFFF', tc:tHex(P.title || P.text) || '#111827', bc:tHex(P.text) || '#111827', ac:tHex(P.accent) || '#3B82F6'};
}
var tplFontT = '';
function tplFont(st){
	var fam = st && st.font && FONTS[st.font] ? st.font : '', tag = $('dsds-font'), link = $('dsds-fontlink');
	if (tag) tag.remove();
	if (!fam) return;
	var href = 'https://fonts.googleapis.com/css2?family=' + fam.replace(/ /g, '+') + ':wght@' + FONTS[fam].join(';') + '&display=swap';
	if (!link) { link = document.createElement('link'); link.id = 'dsds-fontlink'; link.rel = 'stylesheet'; document.head.appendChild(link); }
	if (link.getAttribute('href') !== href) link.setAttribute('href', href);
	if (tplFontT !== fam && document.fonts && document.fonts.load) {
		tplFontT = fam;
		Promise.all([400, 700].map(function(w){ return document.fonts.load(w + ' 24px "' + fam + '"'); })).then(function(){
			try { window.DSTE.clearFit(); } catch (e) {}
			if (draft) preview(); else redraw();
		}).catch(function(){});
	}
}
try { tplHook(); } catch (eT) {}

/* ---- the "Deck style" card above slide 1, and keeping the font in step with the open deck ---- */
var lastSig = null;
function tick(){
	try { tplHook(); tplFetch(); } catch (eH) {}
	var fs = $('dsp-editor-fullscreen'), on = fs && fs.classList.contains('dsp-open') && window.S && S.data && S.data.slides && S.data.slides.length;
	var tile = $('dsds-tile');
	if (!on) { if (tile) tile.style.display = 'none'; if (lastSig !== '') { lastSig = ''; applyFont(null); } return; }
	var left = $('dsp-ppt-panel-left'), thumbs = $('dsp-ppt-thumbs');
	if (!tile && left && thumbs && thumbs.parentNode === left) {
		tile = document.createElement('button'); tile.id = 'dsds-tile'; tile.type = 'button'; tile.title = 'Fonts, sizes and colours for the whole deck';
		tile.innerHTML = '<i>&#127912;</i><span>Deck style<small>Fonts &middot; sizes &middot; colours</small></span>';
		tile.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); open(); });
		left.insertBefore(tile, thumbs);
	}
	if (tile) { tile.style.display = ''; var st0 = saved(), has = !!st0 && !isEmpty(st0); if (tile.classList.contains('dsds-on') !== has) tile.classList.toggle('dsds-on', has); }
	if (!draft) { var st = saved(), sig = (tplOn() ? 'T' : 'C') + (st ? JSON.stringify(st) : '-'); if (sig !== lastSig) { var first = lastSig === null || lastSig === ''; lastSig = sig; applyFont(st); if (!first || st) redraw(); }
		else if (styled()) { var tb = thumbBoxes(); for (var i = 0; i < tb.length; i++) { if (tb[i] && !tb[i].hasAttribute('data-dsds')) { paintThumbs(); break; } } }   /* the editor rebuilt the thumbnails: draw them styled again */
	}
}
setInterval(tick, 700);
})();
