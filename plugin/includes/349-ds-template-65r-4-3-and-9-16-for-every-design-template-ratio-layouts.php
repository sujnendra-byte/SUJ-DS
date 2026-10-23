<?php
/**
 * Code Snippets export — DS Template 65R — 4:3 and 9:16 for every design template (ratio layouts)
 *
 * Snippet ID : 1186
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-templates, ratio
 * Modified   : 2026-10-13
 * Load order : 349 of 352
 */
/**
 * DS Template 65R — 4:3 and 9:16 for every design template (ratio layouts)
 * Run: Everywhere
 *
 * Adds 4:3 (960 x 720) and 9:16 (720 x 1280) decks to every design template registered on the
 * "DS Template Engine (T00)" (Classic and the other 129), with layouts made for each shape.
 * Classic uses its own Classic look; every other template keeps its own colours, fonts, card style,
 * icons, header, footer and background pieces (moved to the same edges / corners of the new shape).
 *  - Home: the Aspect ratio picker opens for any design template (16:9 / 4:3 Standard / 9:16 Portrait).
 *  - Editor: main slide, thumbnails and layout panel take the deck's shape, and every slide kind has its own
 *    4:3 / 9:16 layouts (cover, closing, agenda, statement, 2-16 points, stats, timeline, quote, chart).
 *  - Download: the PowerPoint file gets the matching slide size (4:3 = 10 x 7.5 in, 9:16 = 7.5 x 13.33 in).
 * How: when the page is sent, the template engine / element editor / deck style scripts get a resizable
 * canvas (a few exact text swaps in the page output; their snippets are not edited). The canvas stays
 * 1280 x 720 for every 16:9 deck, so 16:9 decks look exactly as before.
 * NEW SNIPPET — no existing snippet is changed. Undo: deactivate this snippet (4:3 / 9:16 decks then open as 16:9).
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! defined('DS_TPL_RATIO') ) {
define('DS_TPL_RATIO', '1.0.0');

function ds_tplr_sizes() {
	return array(
		'4:3'  => array('w' => 960, 'h' => 720,  'type' => 'screen4x3'),
		'9:16' => array('w' => 720, 'h' => 1280, 'type' => ''),
	);
}
function ds_tplr_templates() {
	$ids = ( isset($GLOBALS['ds_tpl_registry']) && is_array($GLOBALS['ds_tpl_registry']) ) ? array_keys($GLOBALS['ds_tpl_registry']) : array();
	return (array) apply_filters('ds_tplr_templates', $ids);
}
function ds_tplr_on_page() {
	return function_exists('ds_tpl_should_print') && ds_tpl_should_print();
}

/* ---------------------------------------------------------------
 * 1. Download: Classic's own build, then the slide size of the deck's shape
 * ------------------------------------------------------------- */
add_action('init', function () {
	if ( ! function_exists('ds_tpl_get') || ! function_exists('ds_tpl_register') ) return;
	foreach ( ds_tplr_templates() as $id ) {
		$T = ds_tpl_get($id);
		if ( ! is_array($T) || ! empty($T['ratio_build']) ) continue;
		$T['ratio_orig_build'] = isset($T['build']) ? $T['build'] : 'ds_tpl_build';
		$T['ratio_build'] = 1;
		$T['build'] = 'ds_tplr_build';
		ds_tpl_register($id, $T);
	}
}, 99);

function ds_tplr_build( WP_REST_Request $req, $T ) {
	$orig = ( isset($T['ratio_orig_build']) && is_callable($T['ratio_orig_build']) ) ? $T['ratio_orig_build'] : 'ds_tpl_build';
	$S = ds_tplr_sizes();
	$ratio = (string) $req->get_param('tpl_ratio');
	if ( ! isset($S[$ratio]) ) $ratio = (string) $req->get_param('layout');
	if ( ! isset($S[$ratio]) ) return call_user_func($orig, $req, $T); // 16:9: exactly as before
	$GLOBALS['ds_tplr_building'] = $ratio;
	try {
		$out = call_user_func($orig, $req, $T);
	} finally {
		unset($GLOBALS['ds_tplr_building']);
	}
	if ( is_array($out) && ! empty($out['success']) && ! empty($out['url']) ) {
		if ( ds_tplr_resize_pptx((string) $out['url'], $S[$ratio]) ) $out['layout'] = $ratio;
		else error_log('[DS TPL ratio] could not set the ' . $ratio . ' slide size on ' . $out['url']);
	}
	return $out;
}

/* the DesignStager promo slide is drawn for 16:9, so 4:3 / 9:16 files leave it out */
add_filter('ds_tpl_include_promo', function ( $on ) {
	return empty($GLOBALS['ds_tplr_building']) ? $on : false;
}, 99);

function ds_tplr_resize_pptx( $url, $sz ) {
	if ( ! class_exists('ZipArchive') ) return false;
	$up = wp_upload_dir();
	$name = basename( (string) wp_parse_url($url, PHP_URL_PATH) );
	if ( $name === '' || substr($name, -5) !== '.pptx' ) return false;
	$path = trailingslashit($up['basedir']) . 'ds-ppt-downloads/' . $name;
	if ( ! file_exists($path) ) return false;
	$cx = (int) round($sz['w'] * 9525); $cy = (int) round($sz['h'] * 9525);
	$zip = new ZipArchive();
	if ( $zip->open($path) !== true ) return false;
	$put = array();
	$p = $zip->getFromName('ppt/presentation.xml');
	if ( $p === false ) { $zip->close(); return false; }
	$put['ppt/presentation.xml'] = preg_replace('#<p:sldSz\b[^>]*/>#', '<p:sldSz cx="' . $cx . '" cy="' . $cy . '"' . ( $sz['type'] ? ' type="' . $sz['type'] . '"' : '' ) . '/>', $p, 1);
	$old = '<a:ext cx="12192000" cy="6858000"/><a:chOff x="0" y="0"/><a:chExt cx="12192000" cy="6858000"/>';
	$new = '<a:ext cx="' . $cx . '" cy="' . $cy . '"/><a:chOff x="0" y="0"/><a:chExt cx="' . $cx . '" cy="' . $cy . '"/>';
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$n = $zip->getNameIndex($i);
		if ( ! preg_match('#^ppt/slides/slide\d+\.xml$#', (string) $n) ) continue;
		$x = $zip->getFromIndex($i);
		if ( $x !== false && strpos($x, $old) !== false ) $put[$n] = str_replace($old, $new, $x);
	}
	foreach ( $put as $n => $x ) $zip->addFromString($n, $x);
	return $zip->close();
}

/* ---------------------------------------------------------------
 * 2. Page output: a resizable canvas for the engine, the element editor
 *    and deck style (exact text swaps; if a text is not found nothing changes)
 * ------------------------------------------------------------- */
add_action('wp_footer', function () {
	if ( ! ds_tplr_on_page() ) return;
	if ( ob_start() ) $GLOBALS['ds_tplr_ob'] = ob_get_level();
}, 1004);
add_action('wp_footer', function () {
	$lv = isset($GLOBALS['ds_tplr_ob']) ? (int) $GLOBALS['ds_tplr_ob'] : 0;
	unset($GLOBALS['ds_tplr_ob']);
	if ( ! $lv || ob_get_level() !== $lv ) return;
	echo ds_tplr_patch( (string) ob_get_clean() );
}, 2001);

function ds_tplr_patch( $h ) {
	$p = array();
	/* engine core: the canvas size can change (it stays 1280 x 720 unless a 4:3 / 9:16 Classic deck is drawn) */
	$p['DSTE.core = 1; DSTE.W = W; DSTE.H = H;'] = 'DSTE.core = 1; DSTE.W = W; DSTE.H = H; DSTE.setSize = function(w, h){ w = +w || 1280; h = +h || 720; if (w === W && h === H) return; W = w; H = h; DSTE.W = W; DSTE.H = H; var hk = window.__dsSizeHooks || []; for (var i = 0; i < hk.length; i++) { try { hk[i](W, H); } catch (e) {} } };';
	$p["function cq(v){ return (v/12.8).toFixed(3)+'cqw'; }"] = "function cq(v){ return (v/(W/100)).toFixed(3)+'cqw'; }";
	$p['\'<svg viewBox="0 0 1280 720" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;overflow:visible;">\''] = '\'<svg viewBox="0 0 \'+W+\' \'+H+\'" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;overflow:visible;">\'';
	/* router: the deck's shape goes with the download; Classic keeps the aspect-ratio picker open */
	$p["b2.tpl = id; b2.layout = '16:9';"] = "b2.tpl = id; b2.layout = DSTE.deckRatio ? DSTE.deckRatio(d, id) : '16:9'; b2.tpl_ratio = b2.layout;";
	$p["if (id && lay) { lay.value = '16:9';"] = "if (id && lay && !(DSTE.ratioOK && DSTE.ratioOK(id))) { lay.value = '16:9';";
	$p["pl.style.opacity = id ? '.45' : ''; pl.style.pointerEvents = id ? 'none' : ''; pl.title = id ? 'Design templates are 16:9' : '';"] = "var _rl = id && !(DSTE.ratioOK && DSTE.ratioOK(id)); pl.style.opacity = _rl ? '.45' : ''; pl.style.pointerEvents = _rl ? 'none' : ''; pl.title = _rl ? 'Design templates are 16:9' : ''; if (DSTE.ratioMenu) DSTE.ratioMenu(id);";
	$p["Slides are 16:9.' : '';"] = "' + ((DSTE.ratioOK && DSTE.ratioOK(id)) ? 'Slides: 16:9, 4:3 or 9:16 (Aspect ratio).' : 'Slides are 16:9.') : '';";
	$p["box.style.cssText = 'position:relative;width:100%;aspect-ratio:16/9;border-radius:3px;"] = "box.style.cssText = 'position:relative;width:100%;aspect-ratio:' + (DSTE.arCss ? DSTE.arCss(ppt) : '16/9') + ';border-radius:3px;";
	$p['if (lgMain) lgMain.style.background = T.pal.bg;'] = 'if (lgMain) lgMain.style.background = T.pal.bg; if (DSTE.liveRatio) DSTE.liveRatio(ppt);';
	/* template hover preview: one cached preview per ratio, and its panel height follows the ratio */
	$p["var key = id + '|' + (d.homeTheme || ''); if (cache[key]) return cache[key];"] = "var key = id + '|' + (d.homeTheme || '') + '|' + (d.homeRatio ? d.homeRatio() : ''); if (cache[key]) return cache[key];";
	$p['ph = w / 3 * 9 / 16 + 62;'] = "ph = w / 3 * ((d.homeRatio && d.homeRatio() === '9:16') ? 5 / 4 : ((d.homeRatio && d.homeRatio() === '4:3') ? 3 / 4 : 9 / 16)) + 62;";
	/* deck style: picture-zone maths in the deck's size */
	$p['var TW = 1280, TQ = 720, TPG = {}, pptMode = false, tplQuiet = false;'] = 'var TW = 1280, TQ = 720, TPG = {}, pptMode = false, tplQuiet = false; (window.__dsSizeHooks = window.__dsSizeHooks || []).push(function(w, h){ TW = w; TQ = h; });';
	foreach ( $p as $a => $b ) $h = str_replace($a, $b, $h);
	/* element editor: drag / resize maths in the deck's size */
	$h = preg_replace('/(X\.ui = 1;\s*)var W = 1280, H = 720;/', '$1var W = 1280, H = 720; (window.__dsSizeHooks = window.__dsSizeHooks || []).push(function(w, h){ W = w; H = h; });', $h, 1);
	return $h;
}

/* ---------------------------------------------------------------
 * 3. Front end: deck shape, Classic 4:3 / 9:16 layouts, editor sizing
 * ------------------------------------------------------------- */
add_action('wp_footer', function () {
	if ( ! ds_tplr_on_page() ) return;
	$tpls = array();
	foreach ( ds_tplr_templates() as $id ) { $T = function_exists('ds_tpl_get') ? ds_tpl_get($id) : null; if ( is_array($T) && ! empty($T['ratio_build']) ) $tpls[ sanitize_key($id) ] = 1; }
	?>
<style id="ds-tpl-ratio-css">
body.dsr-on .dsp-thumb-box{aspect-ratio:var(--dsr-ar)!important;}
body.dsr-on .vp-thumb{aspect-ratio:var(--dsr-ar)!important;}
body.dsr-on .dsce-prev{aspect-ratio:var(--dsr-ar)!important;}
body.dsr-916 .dsp-thumb-box{width:62%!important;margin:0 auto!important;}
body.dsr-916 .vp-thumb{width:56%!important;margin:0 auto!important;}
#dsp-pill-layout-menu .dsp-pill-opt[data-dsr]{white-space:nowrap;}
/* home: template cards, sample strip and 3-slide preview follow the picked ratio */
body.dsr-h43 .dste-th, body.dsr-h43 #dste-strip .dste-sl>div, body.dsr-h43 .dssl-at, body.dsr-h43 #ds-tplhov .t{aspect-ratio:4/3!important;}
body.dsr-h916 .dste-th, body.dsr-h916 #dste-strip .dste-sl>div, body.dsr-h916 .dssl-at, body.dsr-h916 #ds-tplhov .t{aspect-ratio:4/5!important;}
body.dsr-h916 .dste-th .dste-slide, body.dsr-h916 #dste-strip .dste-sl>div .dste-slide, body.dsr-h916 .dssl-at .dste-slide, body.dsr-h916 #ds-tplhov .t .dste-slide{left:14.84%!important;right:auto!important;width:70.31%!important;}
</style>
<script id="ds-tpl-ratio">
(function(){
'use strict';
var D = window.DSTE;
if (!D || !D.reg || typeof D.setSize !== 'function' || !D.renderPage || !D.toHTML || D.__ratio) return; /* engine not patched: nothing changes */
D.__ratio = 1;
var SIZES = {'16:9':[1280,720], '4:3':[960,720], '9:16':[720,1280]};
var MAIN = {'16:9':[900,506], '4:3':[675,506], '9:16':[360,640]};
var TPLS = <?php echo wp_json_encode((object) $tpls); ?>; /* every template whose download takes the deck's size */

function ok(id){ return !!(id && TPLS[id] && D.reg[id] && D.reg[id].__ratioOn); }
function ratioOf(d, id){ id = id || (d && d.tpl); var r = d && d.layout; return (ok(id) && r && SIZES[r]) ? r : '16:9'; }
function use(r){ var s = SIZES[r] || SIZES['16:9']; D.setSize(s[0], s[1]); }
function mode(){ var W = D.W, H = D.H; return (W === 960 && H === 720) ? '4:3' : ((W === 720 && H === 1280) ? '9:16' : null); }
D.ratioOK = ok;
D.deckRatio = function(d, id){ return ratioOf(d, id); };
D.arCss = function(d){ var s = SIZES[ratioOf(d)]; return s[0] + '/' + s[1]; };

/* ---------- the open deck sets the size the editor works in; every render uses its own deck's size ---------- */
function openDeck(){ if (window.S && window.S.data && window.S.data.slides) return window.S.data; if (window.G && window.G.pptData && window.G.pptData.slides) return window.G.pptData; return null; }
function openRatio(){ var d = openDeck(); if (!d) return '16:9'; var id = D.activeId ? D.activeId() : d.tpl; return ratioOf(d, id); }
var amb = '16:9';
var rp = D.renderPage;
/* home previews (template cards, sample strip, 3-slide preview) are drawn in the ratio picked on the home screen */
function homeR(){ var pk = D.pick, id = (pk && pk !== 'classic' && D.reg[pk]) ? pk : null; if (!ok(id)) return '16:9'; var l = document.getElementById('dsp-layout'); return (l && SIZES[l.value]) ? l.value : '16:9'; }
D.homeRatio = homeR;
function isSample(d){
	var S = D.SAMPLE; if (!d || !S || !d.slides || d.layout) return false;
	for (var i = 0; i < d.slides.length; i++) { var s = d.slides[i]; if (s && (s === S.cover || s === S.p3 || s === S.stats || s === S.timeline)) return true; }
	return false;
}
D.renderPage = function(T, slide, vid, idx, deck){
	var r = '16:9';
	if (T && ok(T.id)) { r = ratioOf(deck, T.id); if (r === '16:9' && isSample(deck)) r = homeR(); }
	use(r);
	try {
		var p = rp.apply(this, arguments);
		if (p && typeof p === 'object') { try { Object.defineProperty(p, '__dsr', {value:r, configurable:true, writable:true, enumerable:false}); } catch (e) {} }
		return p;
	} finally { use(amb); }
};
var th = D.toHTML;
D.toHTML = function(page){
	use((page && page.__dsr) || amb);
	try { return th.apply(this, arguments); } finally { use(amb); }
};

/* company logo boxes come in 1280 x 720 units: keep them on the same side / corner of a 4:3 or 9:16 slide */
function wrapLogo(){
	var f = window.dsceLogoEls; if (typeof f !== 'function' || f.__dsr) return;
	var w = function(){
		var r = f.apply(this, arguments), W = D.W, H = D.H;
		if (!r || !r.logo || (W === 1280 && H === 720)) return r;
		var b = r.badge || r.logo, cx = b.x + b.w / 2, cy = b.y + b.h / 2;
		var dx = cx < 1280 / 3 ? 0 : (cx > 1280 * 2 / 3 ? W - 1280 : (W - 1280) / 2);
		var dy = cy < 720 / 3 ? 0 : (cy > 720 * 2 / 3 ? H - 720 : (H - 720) / 2);
		var o = {}, k; for (k in r) o[k] = r[k];
		function mv(q){ var n = {}, kk; for (kk in q) n[kk] = q[kk]; n.x = q.x + dx; n.y = q.y + dy; return n; }
		o.logo = mv(r.logo); if (r.badge) o.badge = mv(r.badge);
		var bb = o.badge || o.logo, fx = 0, fy = 0;
		if (bb.x < 0) fx = -bb.x; else if (bb.x + bb.w > W) fx = W - bb.x - bb.w;
		if (bb.y < 0) fy = -bb.y; else if (bb.y + bb.h > H) fy = H - bb.y - bb.h;
		if (fx || fy) { o.logo.x += fx; o.logo.y += fy; if (o.badge) { o.badge.x += fx; o.badge.y += fy; } }
		return o;
	};
	w.__dsr = 1; window.dsceLogoEls = w;
}

/* ---------- home: the Aspect ratio picker ---------- */
D.ratioMenu = function(id){
	var menu = document.getElementById('dsp-pill-layout-menu'); if (!menu) return;
	var on = ok(id);
	[['4:3', '4:3 Standard'], ['9:16', '9:16 Portrait']].forEach(function(p){
		var el = menu.querySelector('[data-dsr="' + p[0] + '"]');
		if (!el) {
			el = document.createElement('div'); el.className = 'dsp-pill-opt'; el.setAttribute('data-dsr', p[0]); el.textContent = p[1];
			el.addEventListener('click', function(){
				if (typeof window.dspPillSet === 'function') window.dspPillSet('layout', p[0], p[0]);
				else { var i = document.getElementById('dsp-layout'); if (i) i.value = p[0]; }
			});
			menu.appendChild(el);
		}
		var want = on ? '' : 'none'; if (el.style.display !== want) el.style.display = want;
	});
	if (!on) {
		var lay = document.getElementById('dsp-layout');
		if (lay && (lay.value === '4:3') && typeof window.dspPillSet === 'function') window.dspPillSet('layout', '16:9', '16:9');
	}
};
/* live "generating" screen */
D.liveRatio = function(ppt){
	var m = document.getElementById('dsp-lg-main'); if (!m) return;
	var r = ratioOf(ppt), s = SIZES[r];
	m.style.aspectRatio = s[0] + '/' + s[1];
	m.style.width = r === '9:16' ? 'min(340px,90%)' : (r === '4:3' ? 'min(640px,90%)' : 'min(860px,90%)');
};

/* ---------- new decks carry the picked ratio (AI review, preserve, doc import, free plan and classic paths) ---------- */
var pend = null; /* {r: ratio picked when the last generation request left, t: time} */
var GEN = /\/ds\/v1\/(ai-preview|ai-confirm|generate-ppt|preserve-preview|preserve-confirm|doc-[a-z\-]+)(\?|$)/;
function curPick(){ var l = document.getElementById('dsp-layout'); return (l && SIZES[l.value]) ? l.value : '16:9'; }
function fresh(){ return !!(pend && Date.now() - pend.t < 600000); }
function wrapNew(){
	var f = window.fetch;
	if (typeof f === 'function' && !f.__dsr) {
		var wf = function(url, opts){
			try {
				var us = typeof url === 'string' ? url : (url && url.url) || '';
				if (GEN.test(us)) {
					var add = false;
					if (opts && typeof opts.body === 'string' && opts.body.charAt(0) === '{') { try { var b = JSON.parse(opts.body); add = !!(b.append_slide || b.deduct_only); } catch (e1) {} }
					if (!add) pend = {r:curPick(), t:Date.now()};
				}
			} catch (e) {}
			return f.apply(this, arguments);
		};
		wf.__dsr = 1; window.fetch = wf;
	}
	var g = window.dspLiveGenStream; /* every generation path shows the live screen: tag the new deck here (kept outermost) */
	if (typeof g === 'function' && !g.__dsr) {
		var wg = function(ppt){
			try {
				if (ppt && typeof ppt === 'object' && ppt.slides && ok(ppt.tpl)) {
					if (fresh()) ppt.layout = pend.r; else if (!SIZES[ppt.layout]) ppt.layout = curPick();
				}
			} catch (e) {}
			return g.apply(this, arguments);
		};
		wg.__dsr = 1; window.dspLiveGenStream = wg;
	}
	var be = window.buildEditor; /* a generation that opens the editor without the live screen */
	if (typeof be === 'function' && !be.__dsr) {
		var wb = function(ppt){
			try { if (ppt && typeof ppt === 'object' && ppt.slides && ok(ppt.tpl) && !SIZES[ppt.layout] && fresh()) ppt.layout = pend.r; } catch (e) {}
			pend = null;
			return be.apply(this, arguments);
		};
		wb.__dsr = 1; window.buildEditor = wb;
	}
}

/* ---------- editor: the slide box takes the deck's shape ---------- */
var _mo = null;
function fitMain(){
	var m = document.getElementById('dsp-ppt-main-slide'); if (!m) return;
	if (!_mo || _mo.__el !== m) {
		if (_mo) _mo.disconnect();
		_mo = new MutationObserver(function(){ fitMain(); }); _mo.__el = m;
		_mo.observe(m, {attributes:true, attributeFilter:['style']});
	}
	var id = D.activeId ? D.activeId() : null;
	if (!ok(id)) return;
	var r = amb, want = SIZES[r][0] / SIZES[r][1];
	var cw = parseFloat(m.style.width) || m.offsetWidth, ch = parseFloat(m.style.height) || m.offsetHeight;
	if (!cw || !ch || Math.abs(cw / ch - want) > 0.02) { m.style.width = MAIN[r][0] + 'px'; m.style.height = MAIN[r][1] + 'px'; }
}
function editorOpen(){
	var e = document.getElementById('dsp-editor-fullscreen');
	if (e) return e.offsetParent !== null || getComputedStyle(e).display !== 'none';
	var m = document.getElementById('dsp-ppt-main-slide'); return !!(m && m.offsetParent !== null);
}
/* both copies of the open deck carry its shape */
function syncLayout(){
	var a = window.S && window.S.data, b = window.G && window.G.pptData;
	if (!a || !b || a === b || !a.slides || !b.slides) return;
	if ((a.tpl || b.tpl) !== (b.tpl || a.tpl)) return;
	if (SIZES[b.layout] && !SIZES[a.layout]) a.layout = b.layout;
	else if (SIZES[a.layout] && !SIZES[b.layout]) b.layout = a.layout;
}

/* a template's own background / decoration / footer is drawn for 1280 x 720: draw it there, then move each
   piece to the same edge or corner of the 4:3 / 9:16 slide (full-width / full-height pieces are stretched) */
function axis(p, s, ns, S0, S1, full){
	if (full) return p * S1 / S0;
	var c = p + s / 2;
	if (c < S0 / 3) return p;
	if (c > S0 * 2 / 3) return S1 - (S0 - (p + s)) - ns;
	return c * S1 / S0 - ns / 2;
}
function mapPath(d, sx, sy){
	return d.map(function(c){
		if (!Array.isArray(c) || c.length < 2) return c;
		var n = c.slice(), cmd = String(c[0]).toUpperCase();
		if (cmd === 'A') { n[1] *= sx; n[2] *= sy; n[6] *= sx; n[7] *= sy; return n; }
		if (cmd === 'H') { n[1] *= sx; return n; }
		if (cmd === 'V') { n[1] *= sy; return n; }
		for (var i = 1; i + 1 < n.length; i += 2) { n[i] *= sx; n[i + 1] *= sy; }
		return n;
	});
}
function remapEls(els, old, W, H){
	var sx = W / 1280, sy = H / 720, k = (Math.min(sx, sy) + 1) / 2;
	for (var i = 0; i < els.length; i++) {
		var e = els[i]; if (!e || old.has(e)) continue; /* only the pieces this step added */
		if (e.t === 'line') { e.x1 *= sx; e.x2 *= sx; e.y1 *= sy; e.y2 *= sy; continue; }
		if (e.t === 'path') { if (Array.isArray(e.d)) e.d = mapPath(e.d, sx, sy); continue; }
		if (typeof e.x !== 'number' || typeof e.y !== 'number' || !(e.w > 0) || !(e.h > 0)) continue;
		var fw = e.w >= 1024, fh = e.h >= 576, tx = e.t === 'text';
		var nw = fw ? e.w * sx : ((fh || tx) ? e.w : e.w * k), nh = fh ? e.h * sy : ((fw || tx) ? e.h : e.h * k);
		if (tx && nw > W - 40) nw = W - 40;
		var nx = axis(e.x, e.w, nw, 1280, W, fw), ny = axis(e.y, e.h, nh, 720, H, fh);
		if (tx) { if (nx < 20) nx = 20; if (nx + nw > W - 20) nx = Math.max(0, W - 20 - nw); }
		e.x = nx; e.y = ny; e.w = nw; e.h = nh;
	}
}
function remapRun(C, fn){
	var W = D.W, H = D.H; if (W === 1280 && H === 720) return fn();
	var old = new Set(C.els);
	D.setSize(1280, 720);
	try { return fn(); } finally { D.setSize(W, H); remapEls(C.els, old, W, H); }
}

/* =====================================================================
   4:3 and 9:16 layouts (drawn as elements, so every editor tool works on them).
   Classic: the Classic look. Every other template: its own card style, colours, fonts, header and footer.
   ===================================================================== */
function classicLayouts(T, ov, o0){
	var cls = T.id === 'classicx';
	var u = D.u || {}, mix = u.mix, V = D.V;
	var pad2 = u.pad2 || function(n){ return (n < 10 ? '0' : '') + n; };
	if (!mix || !V || !D.std || !D.mark || !D.iconFor || !D.imgBox || !D.merge || !D.contacts) return null;
	function body(kind, vid){ var l = ov[kind] || []; for (var i = 0; i < l.length; i++) if (l[i].id === vid) return l[i].o && l[i].o.body; return null; }
	var cStats = body('stats', 'v1'), cStatCards = body('stats', 'n2');
	if (typeof cStats !== 'function') cStats = null;
	if (typeof cStatCards !== 'function') cStatCards = null;

	function SZ(){ var W = D.W, H = D.H; return {W:W, H:H, port:H > W}; }
	function tint(P, t){ return mix(P.accent, P.bg, t == null ? 0.9 : t); }
	function tcol(P){ return P.title || P.text; }
	function icon(C, t){ return D.iconFor(C.s, t.i, (t.h || '') + ' ' + t.d); }
	function sv(t){ return t.h || String(t.d).split(' ')[0]; }
	function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
	function numDisc(C, x, y, s, n){
		var P = C.P;
		C.ell(x, y, s, s, {fill:P.accent});
		C.text(x, y, s, s, pad2(n), {font:'head', size:Math.round(s * 0.4), wt:800, color:P.onAccent || '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
	}
	/* a card in the template's own card style; returns the text colours that read on it */
	function cb(C, x, y, w, h, r){
		var P = C.P;
		if (cls || typeof D.card !== 'function') { C.rect(x, y, w, h, {fill:tint(P), r:r}); return {tc:P.text, mc:P.muted}; }
		var o = D.card(C, x, y, w, h, {}) || {};
		return {tc:o.tc || P.text, mc:o.mc || P.muted};
	}
	function hc(cc, P){ return cc.tc === P.text ? tcol(P) : cc.tc; }
	/* big titles: never larger than the size at which the longest word still fits on one line (wide display fonts) */
	function wcap(C, text, w, max){
		var ws = String(text || '').split(/\s+/).sort(function(a, b){ return b.length - a.length; });
		if (!ws[0]) return max;
		var m = C.measureText(ws[0], w, {font:'head', max:max, min:12, maxLines:1});
		return Math.max(18, Math.min(max, Math.floor(m.s * 0.9)));
	}

	/* ---------- cover patterns: editorial / corporate compositions for 4:3 and 9:16 ----------
	   Thirty designs (photo frames, collage blocks, stripes, dot grids, chevrons, rings, barcode bars, mastheads, flyers,
	   tickets, colour blocks, polaroids, posters, Bauhaus shapes, Swiss grids, badges ...), each also mirrored or with rounded / arch photos.
	   They use the template's own colours and fonts; each template opens with its own pattern and can switch to the others. */
	var lum = u.lum || function(){ return 0.5; };
	function inkOf(P){ var c = [P.title, P.text, P.bg].filter(Boolean); c.sort(function(a, b){ return lum(a) - lum(b); }); return c[0] || '#111827'; }
	function paperOf(P){ var c = [P.bg, P.card, '#FFFFFF'].filter(Boolean); c.sort(function(a, b){ return lum(b) - lum(a); }); return lum(c[0]) > 0.6 ? c[0] : '#FFFFFF'; }
	function on(bg, P){ return lum(bg) < 0.5 ? '#FFFFFF' : inkOf(P); }
	function acc2(P){ return P.accent2 || mix(P.accent, '#FFFFFF', 0.35); }
	function yearOf(C){ var m = String(C.cover.date || '').match(/(19|20)\d{2}/); return m ? m[0] : String(new Date().getFullYear()); }
	function cLabel(C, x, y, w, col, al, sz){ var f = C.cover; if (!f.company) return null; return C.text(x, y, w, 20, f.company, {font:'body', size:sz || 12, wt:800, color:col, caps:true, ls:0.2, lh:1, va:'m', align:al || 'l', maxLines:1, ed:{f:'tf-company'}}); }
	function pTitle(C, x, y, w, h, col, o){ o = o || {}; var tm = wcap(C, C.s.title, w, o.max || 72); return C.text(x, y, w, h, C.s.title, {font:'head', fit:{max:tm, min:Math.min(20, tm)}, color:col, align:o.al || 'l', va:o.va || 'b', lh:o.lh || 1.02, caps:o.caps, ls:o.ls, ed:{f:'title'}, maxLines:o.lines || 4}); }
	function pSub(C, x, y, w, h, col, o){ o = o || {}; if (!C.s.subtitle || h < 24) return null; return C.text(x, y, w, h, C.s.subtitle, {font:'body', fit:{max:o.max || 19, min:11}, color:col, align:o.al || 'l', lh:1.42, ed:{f:'subtitle'}, maxLines:o.lines || 4}); }
	function pMeta(C, x, y, w, col, o){
		o = o || {}; var f = C.cover, it = [];
		if (f.presenter) it.push({f:'tf-presenter', v:f.presenter + (f.designation ? ' · ' + f.designation : '')});
		if (f.date) it.push({f:'tf-date', v:f.date});
		if (f.website) it.push({f:'tf-website', v:f.website});
		if (!it.length) return;
		if (o.stack) { it.forEach(function(t, i){ C.text(x, y + i * (o.gap || 24), w, 20, t.v, {font:'body', size:o.size || 13, wt:600, color:col, align:o.al || 'l', va:'m', lh:1, maxLines:1, caps:o.caps, ls:o.caps ? 0.12 : 0, ed:{f:t.f}}); }); return; }
		var n = it.length, cw = w / n;
		if (cw < 230 && f.presenter) it[0].v = f.presenter;
		it.forEach(function(t, i){ C.text(x + i * cw, y, cw - 10, 20, t.v, {font:'body', fit:{max:o.size || 12, min:8}, wt:600, color:col, align:n === 1 ? (o.al || 'l') : (i === 0 ? 'l' : (i === n - 1 ? 'r' : 'c')), va:'m', lh:1, maxLines:1, caps:o.caps, ls:o.caps ? 0.1 : 0, ed:{f:t.f}}); });
	}
	function vLabel(C, cx, cy, len, txt, col){ if (!txt) return; var e = C.text(cx - len / 2, cy - 9, len, 18, txt, {font:'body', size:11, wt:700, color:col, caps:true, ls:0.24, lh:1, va:'m', align:'c', maxLines:1}); e.rot = -90; }
	function dots(C, x, y, cols, rows, gap, r, col, op){ for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) C.ell(x + i * gap, y + j * gap, r * 2, r * 2, {fill:col, op:op}); }
	function stripes(C, x, y, w, h, gap, th, col, op, vert){ if (vert) { for (var i = 0; i * gap < w; i++) C.rect(x + i * gap, y, th, h, {fill:col, op:op}); } else { for (var j = 0; j * gap < h; j++) C.rect(x, y + j * gap, w, th, {fill:col, op:op}); } }
	function brackets(C, x, y, w, h, l, th, col){ [[x, y, 1, 1], [x + w, y, -1, 1], [x, y + h, 1, -1], [x + w, y + h, -1, -1]].forEach(function(c){ C.rect(c[2] > 0 ? c[0] : c[0] - l, c[3] > 0 ? c[1] : c[1] - th, l, th, {fill:col}); C.rect(c[2] > 0 ? c[0] : c[0] - th, c[3] > 0 ? c[1] : c[1] - l, th, l, {fill:col}); }); }
	var PHS = 0; /* photo shape of the running design: 0 as drawn, 1 rounded corners, 2 pill / arch */
	function photo(C, x, y, w, h, o){ o = o || {}; if (PHS && o.shape !== 'ell') { var z = SZ(); if (x > 2 && y > 2 && x + w < z.W - 2 && y + h < z.H - 2) { var q = {}; for (var k in o) q[k] = o[k]; q.r = PHS === 1 ? Math.max(o.r || 0, 28) : Math.round(Math.min(w, h) / 2); o = q; } } return D.imgBox(C, x, y, w, h, o); }
	var PATS = [
	{n:'Editorial photo', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P);
		if (z.port) { photo(C, 0, 0, W, Math.round(H * 0.58)); C.rect(0, Math.round(H * 0.58), W, 8, {fill:P.accent});
			cLabel(C, 48, Math.round(H * 0.62), W - 140, P.accent); pTitle(C, 48, Math.round(H * 0.645), W - 130, Math.round(H * 0.17), tcol(P), {caps:true, va:'t', max:64});
			vLabel(C, W - 34, Math.round(H * 0.78), 300, C.cover.website || C.cover.company, P.muted);
			pSub(C, 48, Math.round(H * 0.82), W - 130, Math.round(H * 0.075), P.text);
			C.rect(48, H - 104, W - 96, 1.5, {fill:P.muted, op:0.5}); pMeta(C, 48, H - 86, W - 96, P.muted, {caps:true, size:11}); }
		else { var pw = Math.round(W * 0.52); photo(C, 0, 0, pw, H); C.rect(pw, 0, 8, H, {fill:P.accent}); var x = pw + 50, w = W - x - 50;
			cLabel(C, x, 64, w, P.accent); pTitle(C, x, 104, w, Math.round(H * 0.34), tcol(P), {caps:true, max:54}); C.rect(x, Math.round(H * 0.5) + 6, 70, 5, {fill:P.accent});
			pSub(C, x, Math.round(H * 0.55), w, Math.round(H * 0.2), P.text, {max:17}); C.rect(x, H - 120, w, 1.5, {fill:P.muted, op:0.5}); pMeta(C, x, H - 104, w, P.muted, {stack:true, size:12, gap:22}); } }},
	{n:'Framed card', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, pa = paperOf(P), ink = inkOf(P), oc = on(P.accent, P);
		C.rect(0, 0, W, H, {fill:P.accent}); dots(C, W - 150, 26, 6, 4, 20, 2.5, oc, 0.35);
		if (z.port) { var x = 48, y = 92, w = W - 96, h = H - 184; C.rect(x, y, w, h, {fill:pa, r:18, shadow:{o:0.18, b:30, d:10}}); brackets(C, x - 18, y - 18, w + 36, h + 36, 40, 4, oc);
			photo(C, x + 20, y + 20, w - 40, Math.round(h * 0.5), {r:12}); var ty = y + 20 + Math.round(h * 0.5) + 26;
			cLabel(C, x + 34, ty, w - 68, P.accent); pTitle(C, x + 34, ty + 30, w - 68, Math.round(h * 0.2), ink, {va:'t', max:54});
			pSub(C, x + 34, ty + 34 + Math.round(h * 0.2), w - 68, Math.round(h * 0.11), mix(ink, pa, 0.25), {max:17});
			pMeta(C, x + 34, y + h - 48, w - 68, mix(ink, pa, 0.35), {size:11, caps:true}); }
		else { var x2 = 56, y2 = 56, w2 = W - 112, h2 = H - 112; C.rect(x2, y2, w2, h2, {fill:pa, r:16, shadow:{o:0.18, b:30, d:10}}); brackets(C, x2 - 16, y2 - 16, w2 + 32, h2 + 32, 36, 4, oc);
			var pw2 = Math.round(w2 * 0.46); photo(C, x2 + 18, y2 + 18, pw2, h2 - 36, {r:10}); var tx = x2 + pw2 + 52, tw = w2 - pw2 - 86;
			cLabel(C, tx, y2 + 40, tw, P.accent); pTitle(C, tx, y2 + 72, tw, Math.round(h2 * 0.42), ink, {va:'t', max:46});
			pSub(C, tx, y2 + 84 + Math.round(h2 * 0.42), tw, Math.round(h2 * 0.24), mix(ink, pa, 0.25), {max:16}); pMeta(C, tx, y2 + h2 - 90, tw, mix(ink, pa, 0.35), {stack:true, size:12, gap:22}); } }},
	{n:'Diagonal split', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), oc = '#FFFFFF';
		photo(C, 0, 0, W, H);
		if (z.port) { var a = Math.round(H * 0.5), b = Math.round(H * 0.6); C.path([['M', 0, a], ['L', W, b], ['L', W, H], ['L', 0, H], ['Z']], {fill:ink}); C.line(0, a, W, b, {color:P.accent, lw:8});
			cLabel(C, 48, b + 34, W - 96, P.accent); pTitle(C, 48, b + 62, W - 96, Math.round(H * 0.17), oc, {va:'t', max:60, caps:true});
			pSub(C, 48, b + 74 + Math.round(H * 0.17), W - 96, Math.round(H * 0.07), mix(oc, ink, 0.25)); pMeta(C, 48, H - 64, W - 96, mix(oc, ink, 0.35), {size:11, caps:true}); }
		else { var p1 = Math.round(W * 0.6), p2 = Math.round(W * 0.47); C.path([['M', 0, 0], ['L', p1, 0], ['L', p2, H], ['L', 0, H], ['Z']], {fill:ink}); C.line(p1, 0, p2, H, {color:P.accent, lw:8});
			var w = p2 - 90; cLabel(C, 56, 70, w, P.accent); pTitle(C, 56, 104, w, Math.round(H * 0.38), oc, {caps:true, max:50}); C.rect(56, Math.round(H * 0.56) + 4, 70, 5, {fill:P.accent});
			pSub(C, 56, Math.round(H * 0.6), w, Math.round(H * 0.2), mix(oc, ink, 0.25), {max:16}); pMeta(C, 56, H - 96, w, mix(oc, ink, 0.35), {stack:true, size:12, gap:22}); } }},
	{n:'Grid collage', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), g = 10, tint = mix(P.accent, P.bg, 0.82);
		if (z.port) { var pw = Math.round(W * 0.62), ph = Math.round(H * 0.5), rx = pw + g, rw = W - rx, h1 = Math.round(ph * 0.48);
			photo(C, 0, 0, pw, ph); C.rect(rx, 0, rw, h1, {fill:P.accent}); dots(C, rx + 22, 26, Math.floor((rw - 30) / 22), Math.floor((h1 - 40) / 22), 22, 3, on(P.accent, P), 0.4);
			C.rect(rx, h1 + g, rw, ph - h1 - g, {fill:ink}); vLabel(C, rx + rw / 2, h1 + g + (ph - h1 - g) / 2, ph - h1 - 40, C.cover.company || yearOf(C), on(ink, P));
			C.rect(0, ph + g, W, 4, {fill:P.accent}); pTitle(C, 48, ph + 50, W - 96, Math.round(H * 0.2), tcol(P), {va:'t', max:62});
			pSub(C, 48, ph + 64 + Math.round(H * 0.2), W - 96, Math.round(H * 0.1), P.text); C.rect(48, H - 96, 60, 5, {fill:P.accent}); pMeta(C, 48, H - 70, W - 96, P.muted, {size:11, caps:true}); }
		else { var pw2 = Math.round(W * 0.5), ph2 = Math.round(H * 0.62), rx2 = pw2 + g, rw2 = W - rx2, h12 = Math.round(ph2 * 0.46);
			photo(C, 0, 0, pw2, ph2); C.rect(rx2, 0, rw2, h12, {fill:P.accent}); dots(C, rx2 + 24, 24, Math.floor((rw2 - 34) / 22), Math.floor((h12 - 36) / 22), 22, 3, on(P.accent, P), 0.4);
			C.rect(rx2, h12 + g, rw2, ph2 - h12 - g, {fill:tint}); cLabel(C, rx2 + 28, h12 + g + 26, rw2 - 56, P.accent); pSub(C, rx2 + 28, h12 + g + 56, rw2 - 56, ph2 - h12 - g - 76, P.text, {max:16});
			pTitle(C, 56, ph2 + 26, W - 112, H - ph2 - 84, tcol(P), {va:'m', max:44, lines:2}); pMeta(C, 56, H - 44, W - 112, P.muted, {size:11, caps:true}); } }},
	{n:'Corporate stripes', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, oc = on(P.accent, P);
		if (z.port) { var bw = Math.round(W * 0.15); C.rect(0, 0, bw, H, {fill:P.accent}); stripes(C, 0, 40, bw, Math.round(H * 0.42), 18, 2, oc, 0.3); vLabel(C, bw / 2, Math.round(H * 0.74), 360, C.cover.company, oc);
			var d = Math.round(W * 0.6), cx = bw + Math.round((W - bw - d) / 2); C.ell(cx - 16, 76 - 16, d + 32, d + 32, {line:P.accent, lw:3}); photo(C, cx, 76, d, d, {shape:'ell'}); dots(C, W - 120, 60, 4, 4, 18, 2.5, P.accent, 0.5);
			var x = bw + 40, w = W - x - 44, ty = 76 + d + 60; pTitle(C, x, ty, w, Math.round(H * 0.19), tcol(P), {va:'t', max:58});
			C.rect(x, ty + Math.round(H * 0.19) + 12, 80, 6, {fill:P.accent}); pSub(C, x, ty + Math.round(H * 0.19) + 36, w, Math.round(H * 0.1), P.text);
			pMeta(C, x, H - 120, w, P.muted, {stack:true, size:13, gap:24}); }
		else { var bw2 = Math.round(W * 0.09); C.rect(0, 0, bw2, H, {fill:P.accent}); stripes(C, 0, 30, bw2, Math.round(H * 0.5), 16, 2, oc, 0.3);
			var d2 = Math.round(H * 0.62), cx2 = W - d2 - 70, cy2 = Math.round((H - d2) / 2); C.ell(cx2 - 14, cy2 - 14, d2 + 28, d2 + 28, {line:P.accent, lw:3}); photo(C, cx2, cy2, d2, d2, {shape:'ell'});
			var x2 = bw2 + 44, w2 = cx2 - x2 - 50; cLabel(C, x2, 70, w2, P.accent); pTitle(C, x2, 104, w2, Math.round(H * 0.34), tcol(P), {max:50}); C.rect(x2, 104 + Math.round(H * 0.34) + 14, 80, 6, {fill:P.accent});
			pSub(C, x2, 104 + Math.round(H * 0.34) + 40, w2, Math.round(H * 0.2), P.text, {max:16}); pMeta(C, x2, H - 70, w2, P.muted, {size:11, caps:true}); } }},
	{n:'Big numeral', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, yr = yearOf(C);
		if (z.port) { C.text(24, 30, W - 48, Math.round(H * 0.2), yr, {font:'head', fit:{max:260, min:60}, wt:800, color:P.accent, op:0.16, align:'l', va:'t', lh:0.9, caps:false, maxLines:1});
			cLabel(C, 48, Math.round(H * 0.07), W - 96, P.accent); pTitle(C, 48, Math.round(H * 0.1), W - 96, Math.round(H * 0.23), tcol(P), {max:64});
			C.rect(48, Math.round(H * 0.34) + 6, 90, 6, {fill:P.accent}); pSub(C, 48, Math.round(H * 0.37), W - 96, Math.round(H * 0.08), P.text); pMeta(C, 48, Math.round(H * 0.46), W - 96, P.muted, {size:11, caps:true});
			photo(C, 40, Math.round(H * 0.5), W - 80, Math.round(H * 0.46), {r:28}); C.rect(W - 160, Math.round(H * 0.5) - 22, 120, 44, {fill:P.accent, r:22}); C.text(W - 160, Math.round(H * 0.5) - 22, 120, 44, yr, {font:'head', size:18, wt:800, color:on(P.accent, P), align:'c', va:'m', lh:1, caps:false}); }
		else { C.text(30, 20, Math.round(W * 0.6), Math.round(H * 0.34), yr, {font:'head', fit:{max:240, min:60}, wt:800, color:P.accent, op:0.16, align:'l', va:'t', lh:0.9, caps:false, maxLines:1});
			var w = Math.round(W * 0.5); cLabel(C, 56, 66, w, P.accent); pTitle(C, 56, 100, w, Math.round(H * 0.36), tcol(P), {max:50}); C.rect(56, 100 + Math.round(H * 0.36) + 14, 80, 6, {fill:P.accent});
			pSub(C, 56, 100 + Math.round(H * 0.36) + 40, w, Math.round(H * 0.18), P.text, {max:16}); pMeta(C, 56, H - 70, w, P.muted, {size:11, caps:true});
			photo(C, Math.round(W * 0.6), 48, Math.round(W * 0.4) - 48, H - 96, {r:24}); } }},
	{n:'Arch window', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P;
		C.rect(22, 22, W - 44, H - 44, {line:P.accent, lw:1.5, lop:0.6});
		if (z.port) { var aw = Math.round(W * 0.64), ax = Math.round((W - aw) / 2), ah = Math.round(H * 0.48); photo(C, ax, 70, aw, ah, {r:aw / 2}); C.rect(ax - 14, 70 - 14, aw + 28, ah + 28, {line:P.accent, lw:2, r:(aw + 28) / 2});
			C.shape('diamond', 60, 120, 22, 22, {fill:P.accent}); C.shape('diamond', W - 82, 70 + ah - 40, 16, 16, {fill:acc2(P)});
			var ty = 70 + ah + 44; cLabel(C, 60, ty, W - 120, P.accent, 'c'); pTitle(C, 60, ty + 28, W - 120, Math.round(H * 0.16), tcol(P), {al:'c', va:'t', max:58});
			C.rect(W / 2 - 40, ty + 40 + Math.round(H * 0.16), 80, 4, {fill:P.accent}); pSub(C, 70, ty + 60 + Math.round(H * 0.16), W - 140, Math.round(H * 0.08), P.text, {al:'c'}); pMeta(C, 60, H - 80, W - 120, P.muted, {size:11, caps:true}); }
		else { var aw2 = Math.round(W * 0.34), ah2 = H - 120; photo(C, 70, 60, aw2, ah2, {r:aw2 / 2}); C.rect(56, 46, aw2 + 28, ah2 + 28, {line:P.accent, lw:2, r:(aw2 + 28) / 2});
			var x = 70 + aw2 + 70, w = W - x - 70; cLabel(C, x, 96, w, P.accent); pTitle(C, x, 130, w, Math.round(H * 0.34), tcol(P), {max:48}); C.rect(x, 130 + Math.round(H * 0.34) + 14, 70, 4, {fill:P.accent});
			pSub(C, x, 130 + Math.round(H * 0.34) + 36, w, Math.round(H * 0.18), P.text, {max:16}); pMeta(C, x, H - 110, w, P.muted, {stack:true, size:12, gap:22}); C.shape('diamond', W - 90, 60, 22, 22, {fill:P.accent}); } }},
	{n:'Dot matrix', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), oc = '#FFFFFF';
		C.rect(0, 0, W, H, {fill:ink});
		if (z.port) { dots(C, W - 230, 40, 9, 9, 22, 3, P.accent, 0.6); var px = 48, py = Math.round(H * 0.16), pw = Math.round(W * 0.7), ph = Math.round(H * 0.4);
			C.rect(px + 22, py + 22, pw, ph, {fill:P.accent}); photo(C, px, py, pw, ph); cLabel(C, 48, 56, W - 300, P.accent);
			var ty = py + ph + 70; pTitle(C, 48, ty, W - 96, Math.round(H * 0.17), oc, {va:'t', max:62}); pSub(C, 48, ty + 14 + Math.round(H * 0.17), W - 96, Math.round(H * 0.08), mix(oc, ink, 0.3));
			C.rect(48, H - 96, W - 96, 1, {fill:oc, op:0.25}); pMeta(C, 48, H - 76, W - 96, mix(oc, ink, 0.35), {size:11, caps:true}); }
		else { dots(C, 40, 40, 6, 5, 22, 3, P.accent, 0.6); var pw2 = Math.round(W * 0.42), ph2 = Math.round(H * 0.62), px2 = W - pw2 - 70, py2 = Math.round((H - ph2) / 2) - 10;
			C.rect(px2 + 20, py2 + 20, pw2, ph2, {fill:P.accent}); photo(C, px2, py2, pw2, ph2); var x = 56, w = px2 - 110;
			cLabel(C, x, 190, w, P.accent); pTitle(C, x, 222, w, Math.round(H * 0.3), oc, {va:'t', max:46}); pSub(C, x, 238 + Math.round(H * 0.3), w, Math.round(H * 0.16), mix(oc, ink, 0.3), {max:16}); pMeta(C, x, H - 70, w, mix(oc, ink, 0.35), {size:11, caps:true}); } }},
	{n:'Magazine masthead', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P);
		if (z.port) { C.rect(32, 34, W - 64, 3, {fill:tcol(P)}); pTitle(C, 32, 50, W - 64, Math.round(H * 0.22), tcol(P), {al:'c', va:'m', caps:true, max:110, lh:0.92});
			C.rect(32, Math.round(H * 0.27), W - 64, 3, {fill:tcol(P)}); cLabel(C, 32, Math.round(H * 0.275), W / 2, P.accent); C.text(W / 2, Math.round(H * 0.275), W / 2 - 32, 20, C.cover.date || yearOf(C), {font:'body', size:12, wt:800, color:P.accent, caps:true, ls:0.2, align:'r', va:'m', lh:1, maxLines:1, ed:C.cover.date ? {f:'tf-date'} : null});
			photo(C, 0, Math.round(H * 0.3), W, Math.round(H * 0.5)); C.rect(32, Math.round(H * 0.8) - 46, 150, 46, {fill:P.accent}); C.text(32, Math.round(H * 0.8) - 46, 150, 46, 'FEATURED', {font:'body', size:12, wt:800, color:on(P.accent, P), caps:true, ls:0.2, align:'c', va:'m', lh:1});
			pSub(C, 32, Math.round(H * 0.82), W - 64, Math.round(H * 0.1), P.text, {al:'c'}); pMeta(C, 32, H - 52, W - 64, P.muted, {size:11, caps:true}); }
		else { C.rect(40, 30, W - 80, 3, {fill:tcol(P)}); pTitle(C, 40, 42, W - 80, Math.round(H * 0.26), tcol(P), {al:'c', va:'m', caps:true, max:90, lh:0.92}); C.rect(40, Math.round(H * 0.33), W - 80, 3, {fill:tcol(P)});
			var pw = Math.round(W * 0.58), py = Math.round(H * 0.37); photo(C, 40, py, pw, H - py - 40); var x = 40 + pw + 36, w = W - x - 40;
			cLabel(C, x, py + 6, w, P.accent); pSub(C, x, py + 40, w, Math.round(H * 0.3), P.text, {max:16}); C.rect(x, H - 150, 60, 5, {fill:P.accent}); pMeta(C, x, H - 130, w, P.muted, {stack:true, size:12, gap:22}); } }},
	{n:'Chevron flow', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, n = 5;
		var ph = Math.round(H * (z.port ? 0.46 : 0.5)); photo(C, 0, 0, W, ph);
		var cw = (W + 40) / n; for (var i = 0; i < n; i++) C.shape(i === 0 ? 'homePlate' : 'chevron', -20 + i * cw - (i ? 10 : 0), ph - 28, cw + (i ? 20 : 10), 56, {fill:mix(P.accent, acc2(P), i / (n - 1))});
		if (z.port) { cLabel(C, 48, ph + 66, W - 96, P.accent); pTitle(C, 48, ph + 96, W - 96, Math.round(H * 0.2), tcol(P), {va:'t', max:62}); pSub(C, 48, ph + 112 + Math.round(H * 0.2), W - 96, Math.round(H * 0.1), P.text);
			pMeta(C, 48, H - 70, W - 96, P.muted, {size:11, caps:true}); }
		else { var lw = Math.round(W * 0.58); cLabel(C, 56, ph + 54, lw, P.accent); pTitle(C, 56, ph + 80, lw, H - ph - 120, tcol(P), {va:'t', max:42, lines:3});
			var rx = 56 + lw + 30; C.rect(rx - 16, ph + 58, 2, H - ph - 100, {fill:P.accent, op:0.5}); pSub(C, rx, ph + 54, W - rx - 56, Math.round(H * 0.2), P.text, {max:15}); pMeta(C, rx, H - 90, W - rx - 56, P.muted, {stack:true, size:11, gap:20}); } }},
	{n:'Orbit circles', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P;
		if (z.port) { var cx = W / 2, cy = Math.round(H * 0.3), r0 = Math.round(W * 0.27);
			[150, 96, 48].forEach(function(dr, i){ var r = r0 + dr; C.ell(cx - r, cy - r, r * 2, r * 2, {line:P.accent, lw:2, lop:0.25 + i * 0.2}); });
			photo(C, cx - r0, cy - r0, r0 * 2, r0 * 2, {shape:'ell'}); C.ell(cx + r0 + 30, cy - 40, 18, 18, {fill:P.accent}); C.ell(cx - r0 - 110, cy + 70, 12, 12, {fill:acc2(P)});
			var ty = Math.round(H * 0.6); cLabel(C, 56, ty, W - 112, P.accent, 'c'); pTitle(C, 56, ty + 28, W - 112, Math.round(H * 0.17), tcol(P), {al:'c', va:'t', max:62});
			pSub(C, 70, ty + 40 + Math.round(H * 0.17), W - 140, Math.round(H * 0.08), P.text, {al:'c'}); pMeta(C, 56, H - 64, W - 112, P.muted, {size:11, caps:true}); }
		else { var cx2 = Math.round(W * 0.72), cy2 = H / 2, r2 = Math.round(H * 0.26);
			[130, 84, 42].forEach(function(dr, i){ var r = r2 + dr; C.ell(cx2 - r, cy2 - r, r * 2, r * 2, {line:P.accent, lw:2, lop:0.25 + i * 0.2}); });
			photo(C, cx2 - r2, cy2 - r2, r2 * 2, r2 * 2, {shape:'ell'}); C.ell(cx2 + r2 + 24, cy2 - 60, 16, 16, {fill:P.accent});
			var w = Math.round(W * 0.42); cLabel(C, 56, 110, w, P.accent); pTitle(C, 56, 144, w, Math.round(H * 0.34), tcol(P), {max:48}); C.rect(56, 144 + Math.round(H * 0.34) + 14, 70, 5, {fill:P.accent});
			pSub(C, 56, 144 + Math.round(H * 0.34) + 38, w, Math.round(H * 0.18), P.text, {max:16}); pMeta(C, 56, H - 70, w, P.muted, {size:11, caps:true}); } }},
	{n:'Photo strip', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P);
		function bars(x, y, h){ var ws = [3, 7, 2, 5, 9, 2, 4, 6, 2, 8, 3, 5], xx = x; ws.forEach(function(w, i){ C.rect(xx, y, w, h, {fill:i % 3 === 1 ? P.accent : tcol(P), op:i % 3 === 1 ? 1 : 0.85}); xx += w + 4; }); }
		if (z.port) { var sx = Math.round(W * 0.5), sh = Math.round(H * 0.6); photo(C, sx, 0, W - sx, sh); C.rect(sx - 10, 0, 10, sh, {fill:P.accent});
			bars(48, 64, 70); cLabel(C, 48, 160, sx - 90, P.accent); C.text(48, 194, sx - 90, 40, yearOf(C), {font:'head', size:34, wt:800, color:tcol(P), lh:1, va:'m', caps:false});
			vLabel(C, sx - 30, Math.round(sh * 0.62), 320, C.cover.website || 'Presentation', P.muted);
			var ty = sh + 50; pTitle(C, 48, ty, W - 96, Math.round(H * 0.19), tcol(P), {va:'t', max:66, caps:true}); pSub(C, 48, ty + 14 + Math.round(H * 0.19), W - 96, Math.round(H * 0.08), P.text);
			C.rect(48, H - 92, W - 96, 1.5, {fill:P.muted, op:0.5}); pMeta(C, 48, H - 72, W - 96, P.muted, {size:11, caps:true}); }
		else { var sx2 = Math.round(W * 0.62); photo(C, sx2, 0, W - sx2, H); C.rect(sx2 - 10, 0, 10, H, {fill:P.accent}); bars(56, 56, 56);
			var w = sx2 - 110; cLabel(C, 56, 140, w, P.accent); pTitle(C, 56, 172, w, Math.round(H * 0.32), tcol(P), {caps:true, max:48}); pSub(C, 56, 186 + Math.round(H * 0.32), w, Math.round(H * 0.18), P.text, {max:16});
			C.rect(56, H - 90, w, 1.5, {fill:P.muted, op:0.5}); pMeta(C, 56, H - 70, w, P.muted, {size:11, caps:true}); } }}
	];
	/* flyer-style info chips: icon + text in a pill (date, presenter, website) */
	function chips(C, x, y, w, col, line, o){
		o = o || {}; var f = C.cover, it = [];
		if (f.date) it.push({ic:'mdi:calendar-blank', v:f.date, f:'tf-date'});
		if (f.presenter) it.push({ic:'mdi:account', v:f.presenter, f:'tf-presenter'});
		if (f.website) it.push({ic:'mdi:web', v:f.website, f:'tf-website'});
		if (!it.length) return 0;
		if (o.stack) { it.forEach(function(t, i){ var yy = y + i * 44; C.rect(x, yy, w, 36, {line:line, lw:1.5, r:18, fill:o.fill, op:o.fop}); C.icon(x + 14, yy + 9, 18, t.ic, line); C.text(x + 42, yy, w - 56, 36, t.v, {font:'body', size:13, wt:700, color:col, va:'m', lh:1, maxLines:1, ed:{f:t.f}}); }); return it.length * 44; }
		var g = 10, cw = (w - g * (it.length - 1)) / it.length;
		it.forEach(function(t, i){ var xx = x + i * (cw + g); C.rect(xx, y, cw, 38, {line:line, lw:1.5, r:19, fill:o.fill, op:o.fop}); C.icon(xx + 12, y + 10, 18, t.ic, line); C.text(xx + 36, y, cw - 46, 38, t.v, {font:'body', fit:{max:13, min:9}, wt:700, color:col, va:'m', lh:1, maxLines:1, ed:{f:t.f}}); });
		return 38;
	}
	function waves(C, x0, y0, x1, y1, col, o){ if (typeof D.waveBundle !== 'function') return; o = o || {}; D.waveBundle(C, {n:o.n || 16, x0:x0, y0:y0, x1:x1, y1:y1, amp:o.amp || 90, spread:o.spread || 7, color:col, lw:1.1, op:o.op != null ? o.op : 0.55, fade:true, phase:o.phase || 0}); }
	function halftone(C, x, y, cols, rows, gap, col, o){ for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) { var t = (o && o.dir === 'x') ? i / cols : j / rows, r = Math.max(0.6, gap * 0.36 * (1 - t)); C.ell(x + i * gap + gap / 2 - r, y + j * gap + gap / 2 - r, r * 2, r * 2, {fill:col, op:0.75}); } }
	function hexes(C, x, y, n, s, col, op){ var w = s, h = s * 0.87; for (var i = 0; i < n; i++) { var r = Math.floor(i / 3), c = i % 3; C.shape('hexagon', x + c * w * 0.76 + (r % 2 ? w * 0.38 : 0) * 0, y + r * h + (c % 2 ? h / 2 : 0), w, h, {line:col, lw:1.4, lop:op}); } }
	function trace(C, pts, col, op){ C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; }), {stroke:col, lw:2, lop:op}); var e = pts[pts.length - 1]; C.ell(e[0] - 5, e[1] - 5, 10, 10, {fill:col, op:op}); }
	PATS = PATS.concat([
	{n:'Wave flyer', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), dark = lum(P.bg) < 0.45, tc = tcol(P), lc = dark ? acc2(P) : P.accent;
		waves(C, W * 0.45, -40, W + 60, H * 0.28, lc, {phase:0.3}); waves(C, -60, H * 0.38, W * 0.5, H + 40, lc, {phase:1.2, amp:110});
		if (z.port) { cLabel(C, 60, 110, W - 120, tc, 'c', 14); pTitle(C, 50, 150, W - 100, Math.round(H * 0.16), tc, {al:'c', va:'m', max:84, caps:true});
			pSub(C, 70, 160 + Math.round(H * 0.16), W - 140, Math.round(H * 0.07), P.text, {al:'c', max:22});
			chips(C, 70, Math.round(H * 0.38), W - 140, tc, lc, {stack:true});
			photo(C, 70, Math.round(H * 0.56), W - 140, Math.round(H * 0.36), {r:26}); C.rect(70 - 10, Math.round(H * 0.56) - 10, W - 120, Math.round(H * 0.36) + 20, {line:lc, lw:2, r:32, lop:0.7}); }
		else { var w = Math.round(W * 0.5); cLabel(C, 56, 70, w, tc, 'l', 13); pTitle(C, 56, 100, w, Math.round(H * 0.3), tc, {max:66, caps:true}); pSub(C, 56, 112 + Math.round(H * 0.3), w, Math.round(H * 0.14), P.text, {max:17});
			chips(C, 56, Math.round(H * 0.62), w - 20, tc, lc, {stack:true}); var px = Math.round(W * 0.58); photo(C, px, 70, W - px - 56, H - 140, {r:24}); C.rect(px - 10, 60, W - px - 36, H - 120, {line:lc, lw:2, r:30, lop:0.7}); } }},
	{n:'Speaker arch', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), tc = tcol(P), f = C.cover;
		halftone(C, W - 150, 0, 6, 10, 24, P.accent);
		if (z.port) { cLabel(C, 48, 56, W - 220, P.accent); pTitle(C, 48, 86, W - 200, Math.round(H * 0.2), tc, {caps:true, va:'t', max:72});
			var aw = Math.round(W * 0.58), ax = 48, ay = Math.round(H * 0.33), ah = Math.round(H * 0.42); C.rect(ax - 8, ay - 8, aw + 16, ah + 16, {line:P.accent, lw:4, r:(aw + 16) / 2}); photo(C, ax, ay, aw, ah, {r:aw / 2});
			if (f.presenter) { C.rect(ax + 20, ay + ah - 30, aw - 40, 52, {fill:P.accent, r:12}); C.text(ax + 20, ay + ah - 30, aw - 40, 52, f.presenter, {font:'head', fit:{max:22, min:12}, wt:800, color:on(P.accent, P), align:'c', va:'m', lh:1, maxLines:1, ed:{f:'tf-presenter'}}); }
			var rx = ax + aw + 30, rw = W - rx - 40; pSub(C, rx, ay + 40, rw, Math.round(ah * 0.6), P.text, {max:15, lines:8});
			C.rect(48, H - 150, W - 96, 1.5, {fill:P.muted, op:0.5}); pMeta(C, 48, H - 128, W - 96, P.muted, {size:12, caps:true}); C.text(48, H - 96, W - 96, 30, f.date || yearOf(C), {font:'head', size:24, wt:800, color:P.accent, va:'m', lh:1, maxLines:1, ed:f.date ? {f:'tf-date'} : null}); }
		else { var aw2 = Math.round(W * 0.32), ah2 = H - 150, ax2 = W - aw2 - 90; C.rect(ax2 - 8, 62, aw2 + 16, ah2 + 16, {line:P.accent, lw:4, r:(aw2 + 16) / 2}); photo(C, ax2, 70, aw2, ah2, {r:aw2 / 2});
			if (f.presenter) { C.rect(ax2 + 14, 70 + ah2 - 36, aw2 - 28, 48, {fill:P.accent, r:12}); C.text(ax2 + 14, 70 + ah2 - 36, aw2 - 28, 48, f.presenter, {font:'head', fit:{max:20, min:11}, wt:800, color:on(P.accent, P), align:'c', va:'m', lh:1, maxLines:1, ed:{f:'tf-presenter'}}); }
			var w = ax2 - 120; cLabel(C, 56, 66, w, P.accent); pTitle(C, 56, 98, w, Math.round(H * 0.34), tc, {caps:true, max:56}); pSub(C, 56, 112 + Math.round(H * 0.34), w, Math.round(H * 0.18), P.text, {max:16});
			C.text(56, H - 120, w, 40, f.date || yearOf(C), {font:'head', size:30, wt:800, color:P.accent, va:'m', lh:1, maxLines:1, ed:f.date ? {f:'tf-date'} : null}); C.text(56, H - 74, w, 20, f.website || '', {font:'body', size:12, wt:600, color:P.muted, va:'m', lh:1, maxLines:1, ed:f.website ? {f:'tf-website'} : null}); } }},
	{n:'Vertical title', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), f = C.cover, pa = paperOf(P);
		if (z.port) { var tl = H - 120, th = Math.round(W * 0.3), e = C.text(W - th / 2 - 30 - tl / 2, H / 2 - th / 2, tl, th, C.s.title, {font:'head', fit:{max:wcap(C, C.s.title, tl, 150), min:40}, color:tc, align:'c', va:'m', lh:0.95, caps:true, ed:{f:'title'}, maxLines:2}); e.rot = -90;
			halftone(C, W - th - 70, 40, 2, 26, 22, P.accent);
			var pw = Math.round(W * 0.56), px = 44, py = 70; C.rect(px - 10, py - 10, pw + 20, Math.round(H * 0.42) + 70, {fill:pa, r:10, shadow:{o:0.2, b:24, d:8}}); photo(C, px, py, pw, Math.round(H * 0.42), {r:6});
			C.text(px, py + Math.round(H * 0.42) + 10, pw, 22, 'FEATURING', {font:'body', size:11, wt:700, color:P.accent, caps:true, ls:0.24, align:'c', va:'m', lh:1});
			C.text(px, py + Math.round(H * 0.42) + 32, pw, 24, f.presenter || f.company || '', {font:'head', size:20, wt:800, color:inkOf(P), align:'c', va:'m', lh:1, maxLines:1, ed:f.presenter ? {f:'tf-presenter'} : (f.company ? {f:'tf-company'} : null)});
			var ty = Math.round(H * 0.6); C.text(44, ty, pw, 80, (String(f.date || '').match(/\d{1,2}/) || [yearOf(C).slice(2)])[0], {font:'head', size:72, wt:800, color:tc, va:'m', lh:1, caps:false});
			pSub(C, 44, ty + 96, pw, Math.round(H * 0.16), P.text, {max:16, lines:6}); pMeta(C, 44, H - 130, pw, P.muted, {stack:true, size:12, gap:22, caps:true}); }
		else { var tl2 = H - 80, th2 = 150, e2 = C.text(W - th2 / 2 - 40 - tl2 / 2, H / 2 - th2 / 2, tl2, th2, C.s.title, {font:'head', fit:{max:wcap(C, C.s.title, tl2, 120), min:34}, color:tc, align:'c', va:'m', lh:0.95, caps:true, ed:{f:'title'}, maxLines:2}); e2.rot = -90;
			halftone(C, W - th2 - 90, 30, 2, 28, 22, P.accent); var pw2 = Math.round(W * 0.34); C.rect(46, 46, pw2 + 20, H - 92, {fill:pa, r:10, shadow:{o:0.2, b:24, d:8}}); photo(C, 56, 56, pw2, H - 160, {r:6});
			C.text(56, H - 98, pw2, 40, f.presenter || f.company || '', {font:'head', size:18, wt:800, color:inkOf(P), align:'c', va:'m', lh:1, maxLines:1, ed:f.presenter ? {f:'tf-presenter'} : (f.company ? {f:'tf-company'} : null)});
			var x = 56 + pw2 + 50, w = W - x - th2 - 120; cLabel(C, x, 80, w, P.accent); pSub(C, x, 120, w, Math.round(H * 0.36), P.text, {max:16, lines:8}); pMeta(C, x, H - 130, w, P.muted, {stack:true, size:12, gap:22, caps:true}); } }},
	{n:'Hex tech', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P);
		hexes(C, W - 260, -20, 9, 92, P.accent, 0.45); hexes(C, -40, H - 220, 6, 80, P.accent, 0.3);
		if (z.port) { cLabel(C, 48, 70, W - 300, P.accent); pTitle(C, 48, 104, W - 120, Math.round(H * 0.2), tc, {caps:true, va:'t', max:74});
			var d = Math.round(W * 0.5), cx = W - d - 60, cy = Math.round(H * 0.38); C.shape('hexagon', cx - 30, cy - 26, d + 60, (d + 60) * 0.87 + 10, {fill:P.accent, op:0.18}); photo(C, cx, cy, d, d, {shape:'ell'}); D.ring && D.ring(C, cx + d / 2, cy + d / 2, d / 2 + 10, {color:P.accent, lw:3});
			pSub(C, 48, cy + 10, cx - 80, Math.round(d * 0.9), P.text, {max:17, lines:8});
			C.rect(48, Math.round(H * 0.72), 70, 6, {fill:P.accent}); chips(C, 48, Math.round(H * 0.75), W - 96, tc, P.accent, {stack:true}); }
		else { var d2 = Math.round(H * 0.5), cx2 = W - d2 - 90, cy2 = Math.round(H * 0.3); C.shape('hexagon', cx2 - 30, cy2 - 26, d2 + 60, (d2 + 60) * 0.87 + 10, {fill:P.accent, op:0.18}); photo(C, cx2, cy2, d2, d2, {shape:'ell'}); D.ring && D.ring(C, cx2 + d2 / 2, cy2 + d2 / 2, d2 / 2 + 10, {color:P.accent, lw:3});
			var w = cx2 - 120; cLabel(C, 56, 70, w, P.accent); pTitle(C, 56, 100, w, Math.round(H * 0.32), tc, {caps:true, max:56}); pSub(C, 56, 114 + Math.round(H * 0.32), w, Math.round(H * 0.16), P.text, {max:16}); chips(C, 56, H - 150, w, tc, P.accent, {stack:false}); } }},
	{n:'Photo hero', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), oc = '#FFFFFF';
		C.rect(0, 0, W, H, {fill:ink});
		if (z.port) { var ph = Math.round(H * 0.62); photo(C, 0, 0, W, ph); C.rect(0, Math.round(ph * 0.45), W, ph - Math.round(ph * 0.45) + 2, {grad:{ang:180, stops:[[0, ink, 0], [100, ink, 1]]}});
			cLabel(C, 48, 50, W - 96, oc); pTitle(C, 48, Math.round(ph * 0.66), W - 96, Math.round(H * 0.18), oc, {va:'b', max:76, caps:true});
			C.rect(48, ph + 18, 90, 6, {fill:P.accent}); pSub(C, 48, ph + 44, W - 96, Math.round(H * 0.1), mix(oc, ink, 0.25), {max:19});
			chips(C, 48, H - 100, W - 96, oc, P.accent, {fill:P.accent, fop:0.14}); }
		else { var pw = Math.round(W * 0.62); photo(C, W - pw, 0, pw, H); C.rect(W - pw, 0, Math.round(pw * 0.5), H, {grad:{ang:90, stops:[[0, ink, 1], [100, ink, 0]]}});
			var w = Math.round(W * 0.52); cLabel(C, 56, 66, w, oc); pTitle(C, 56, 96, w, Math.round(H * 0.38), oc, {max:60, caps:true}); C.rect(56, 110 + Math.round(H * 0.38), 80, 6, {fill:P.accent});
			pSub(C, 56, 134 + Math.round(H * 0.38), w - 40, Math.round(H * 0.16), mix(oc, ink, 0.25), {max:16}); chips(C, 56, H - 84, W - 112, oc, P.accent, {fill:P.accent, fop:0.14}); } }},
	{n:'Circuit lines', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), oc = '#FFFFFF', lc = acc2(P);
		C.rect(0, 0, W, H, {fill:ink});
		trace(C, [[W, 60], [W - 120, 60], [W - 160, 100], [W - 260, 100]], lc, 0.7); trace(C, [[W, 84], [W - 90, 84], [W - 130, 124], [W - 200, 124]], lc, 0.5);
		trace(C, [[0, H * 0.3], [70, H * 0.3], [110, H * 0.3 + 40], [110, H * 0.3 + 120]], lc, 0.6);
		if (z.port) { cLabel(C, 56, 140, W - 112, lc, 'c'); pTitle(C, 40, 176, W - 80, Math.round(H * 0.2), oc, {al:'c', va:'t', max:96, caps:true, lh:0.95});
			pSub(C, 70, 196 + Math.round(H * 0.2), W - 140, Math.round(H * 0.08), mix(oc, ink, 0.25), {al:'c'});
			var y0 = Math.round(H * 0.5); C.text(60, y0, W / 2 - 60, 60, C.cover.date || yearOf(C), {font:'head', fit:{max:44, min:18}, wt:800, color:oc, va:'m', lh:1, maxLines:1, ed:C.cover.date ? {f:'tf-date'} : null}); C.text(W / 2, y0, W / 2 - 60, 60, C.cover.website || '', {font:'body', size:14, wt:600, color:lc, align:'r', va:'m', lh:1.2, maxLines:2, ed:C.cover.website ? {f:'tf-website'} : null});
			var d = Math.round(W * 0.8); D.ring && D.ring(C, W - d * 0.32, H - d * 0.18, d / 2 + 16, {color:lc, lw:3, op:0.8}); photo(C, W - d * 0.82, H - d * 0.68, d, d, {shape:'ell'});
			pMeta(C, 56, Math.round(H * 0.6), W * 0.5, mix(oc, ink, 0.3), {stack:true, size:12, gap:22}); }
		else { var w = Math.round(W * 0.56); cLabel(C, 56, 90, w, lc); pTitle(C, 56, 120, w, Math.round(H * 0.34), oc, {max:70, caps:true, lh:0.95}); pSub(C, 56, 134 + Math.round(H * 0.34), w, Math.round(H * 0.14), mix(oc, ink, 0.25), {max:16});
			C.text(56, H - 130, w, 50, C.cover.date || yearOf(C), {font:'head', fit:{max:36, min:16}, wt:800, color:oc, va:'m', lh:1, maxLines:1, ed:C.cover.date ? {f:'tf-date'} : null}); pMeta(C, 56, H - 72, w, mix(oc, ink, 0.3), {size:11, caps:true});
			var d2 = Math.round(H * 0.8); D.ring && D.ring(C, W - d2 * 0.3, H * 0.62, d2 / 2 + 14, {color:lc, lw:3, op:0.8}); photo(C, W - d2 * 0.8, H * 0.62 - d2 / 2, d2, d2, {shape:'ell'}); } }}
	]);
	/* ---------- twelve more cover designs: tickets, colour blocks, polaroids, posters, Bauhaus, Swiss grid, badges ... ---------- */
	function perfRow(C, x0, x1, y, r, gap, col){ for (var x = x0; x <= x1; x += gap) C.ell(x - r, y - r, r * 2, r * 2, {fill:col}); }
	function perfCol(C, x, y0, y1, r, gap, col){ for (var y = y0; y <= y1; y += gap) C.ell(x - r, y - r, r * 2, r * 2, {fill:col}); }
	function bars(C, x, y, h, col){ var ws = [3, 6, 2, 4, 8, 2, 3, 5, 2, 7, 3, 4, 2, 6], xx = x; ws.forEach(function(w){ C.rect(xx, y, w, h, {fill:col}); xx += w + 3; }); return xx - x; }
	function ghost(C, x, y, w, h, txt, col, op, al){ return C.text(x, y, w, h, txt, {font:'head', fit:{max:Math.round(h * 0.95), min:20}, wt:900, color:col, op:op, align:al || 'l', va:'m', lh:0.9, caps:false, maxLines:1}); }
	PATS = PATS.concat([
	{n:'Ticket stub', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, pa = paperOf(P), ink = inkOf(P), f = C.cover, bgc = mix(P.accent, '#000000', 0.15);
		C.rect(0, 0, W, H, {fill:bgc}); dots(C, 24, 24, 4, 3, 18, 2, '#FFFFFF', 0.25);
		if (z.port) { var x = 44, y = 84, w = W - 88, h = H - 168, cut = y + Math.round(h * 0.8), sy = cut + Math.round((y + h - cut - 110) / 2);
			C.rect(x, y, w, h, {fill:pa, r:22, shadow:{o:0.25, b:30, d:12}}); photo(C, x + 18, y + 18, w - 36, Math.round(h * 0.36), {r:14});
			var ty = y + 18 + Math.round(h * 0.36) + 26; cLabel(C, x + 30, ty, w - 60, P.accent); pTitle(C, x + 30, ty + 28, w - 60, cut - ty - 120, ink, {va:'t', max:56, caps:true});
			pSub(C, x + 30, cut - 86, w - 60, 70, mix(ink, pa, 0.3), {max:16, lines:3});
			C.ell(x - 22, cut - 22, 44, 44, {fill:bgc}); C.ell(x + w - 22, cut - 22, 44, 44, {fill:bgc}); perfRow(C, x + 40, x + w - 40, cut, 3, 16, mix(ink, pa, 0.7));
			C.text(x + 30, sy, w * 0.55, 54, f.date || yearOf(C), {font:'head', fit:{max:30, min:14}, wt:800, color:P.accent, va:'m', lh:1, maxLines:2, ed:f.date ? {f:'tf-date'} : null});
			C.text(x + 30, sy + 60, w * 0.55, 20, f.presenter || f.website || '', {font:'body', size:12, wt:700, color:mix(ink, pa, 0.35), caps:true, ls:0.12, va:'m', lh:1, maxLines:1, ed:f.presenter ? {f:'tf-presenter'} : (f.website ? {f:'tf-website'} : null)});
			bars(C, x + w - 30 - 110, sy + 4, 76, ink); }
		else { var x2 = 60, y2 = 70, w2 = W - 120, h2 = H - 140, cut2 = x2 + Math.round(w2 * 0.76);
			C.rect(x2, y2, w2, h2, {fill:pa, r:22, shadow:{o:0.25, b:30, d:12}}); var pw = Math.round(w2 * 0.34); photo(C, x2 + 18, y2 + 18, pw, h2 - 36, {r:14});
			var tx = x2 + pw + 52, tw = cut2 - tx - 40; cLabel(C, tx, y2 + 44, tw, P.accent); pTitle(C, tx, y2 + 76, tw, Math.round(h2 * 0.42), ink, {va:'t', max:48, caps:true});
			pSub(C, tx, y2 + 92 + Math.round(h2 * 0.42), tw, Math.round(h2 * 0.26), mix(ink, pa, 0.3), {max:15});
			C.ell(cut2 - 22, y2 - 22, 44, 44, {fill:bgc}); C.ell(cut2 - 22, y2 + h2 - 22, 44, 44, {fill:bgc}); perfCol(C, cut2, y2 + 40, y2 + h2 - 40, 3, 16, mix(ink, pa, 0.7));
			var sw = x2 + w2 - cut2, sx = cut2 + sw / 2, e = C.text(sx - (h2 - 80) / 2, y2 + h2 / 2 - 30, h2 - 80, 60, f.date || yearOf(C), {font:'head', fit:{max:34, min:14}, wt:800, color:P.accent, align:'c', va:'m', lh:1, maxLines:1, ed:f.date ? {f:'tf-date'} : null}); e.rot = -90;
			C.text(cut2 + 18, y2 + h2 - 54, sw - 36, 20, f.website || '', {font:'body', size:10, wt:700, color:mix(ink, pa, 0.35), caps:true, align:'c', va:'m', lh:1, maxLines:1, ed:f.website ? {f:'tf-website'} : null}); } }},
	{n:'Colour blocks', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, ink = inkOf(P), g = 12, oc = on(ink, P), a2 = acc2(P), tint = mix(P.accent, '#FFFFFF', 0.8);
		C.rect(0, 0, W, H, {fill:P.bg});
		if (z.port) { var r1 = Math.round(H * 0.36), pw = Math.round(W * 0.64);
			photo(C, g, g, pw - g, r1 - g); C.rect(pw + g, g, W - pw - 2 * g, Math.round((r1 - g) * 0.55), {fill:P.accent}); C.rect(pw + g, g + Math.round((r1 - g) * 0.55) + g, W - pw - 2 * g, r1 - g - Math.round((r1 - g) * 0.55) - g, {fill:a2});
			var my = r1 + g, mh = Math.round(H * 0.42); C.rect(g, my, W - 2 * g, mh, {fill:ink});
			cLabel(C, 44, my + 36, W - 88, P.accent); pTitle(C, 44, my + 66, W - 88, Math.round(mh * 0.55), oc, {va:'t', max:64, caps:true}); pSub(C, 44, my + mh - Math.round(mh * 0.3), W - 88, Math.round(mh * 0.24), mix(oc, ink, 0.3), {max:17});
			var by = my + mh + g, bh = H - by - g; C.rect(g, by, Math.round(W * 0.3), bh, {fill:P.accent}); C.text(g, by, Math.round(W * 0.3), bh, yearOf(C), {font:'head', fit:{max:44, min:16}, wt:800, color:on(P.accent, P), align:'c', va:'m', lh:1, caps:false, maxLines:1});
			var mx = g + Math.round(W * 0.3) + g; C.rect(mx, by, W - mx - g, bh, {fill:tint}); pMeta(C, mx + 24, by + 22, W - mx - g - 48, inkOf(P), {stack:true, size:12, gap:22}); }
		else { var lw = Math.round(W * 0.56), rh = Math.round(H * 0.62);
			C.rect(g, g, lw - g, H - 2 * g, {fill:ink}); cLabel(C, 52, 64, lw - 90, P.accent); pTitle(C, 52, 96, lw - 90, Math.round(H * 0.42), oc, {va:'t', max:56, caps:true});
			pSub(C, 52, 110 + Math.round(H * 0.42), lw - 90, Math.round(H * 0.2), mix(oc, ink, 0.3), {max:16}); pMeta(C, 52, H - 64, lw - 90, mix(oc, ink, 0.35), {size:11, caps:true});
			var rx = lw + g; photo(C, rx, g, W - rx - g, rh - g); var bw = Math.round((W - rx - g - g) * 0.45);
			C.rect(rx, rh + g, bw, H - rh - 2 * g, {fill:P.accent}); C.text(rx, rh + g, bw, H - rh - 2 * g, yearOf(C), {font:'head', fit:{max:48, min:16}, wt:800, color:on(P.accent, P), align:'c', va:'m', lh:1, caps:false, maxLines:1});
			C.rect(rx + bw + g, rh + g, W - rx - bw - 2 * g, H - rh - 2 * g, {fill:a2}); dots(C, rx + bw + g + 22, rh + g + 22, 5, 4, 18, 2.5, on(a2, P), 0.45); } }},
	{n:'Corner circle', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P);
		if (z.port) { var r = Math.round(W * 0.62), cx = Math.round(W * 0.8), cy = Math.round(H * 0.2);
			C.ell(cx - r - 26, cy - r - 26, (r + 26) * 2, (r + 26) * 2, {line:P.accent, lw:3, lop:0.8}); C.ell(cx - r - 60, cy - r - 60, (r + 60) * 2, (r + 60) * 2, {line:P.accent, lw:1.5, lop:0.35});
			photo(C, cx - r, cy - r, r * 2, r * 2, {shape:'ell'}); C.ell(cx - r - 4, cy + r - 90, 36, 36, {fill:P.accent});
			var ty = Math.round(H * 0.56); cLabel(C, 48, ty, W - 96, P.accent); pTitle(C, 48, ty + 30, W - 96, Math.round(H * 0.2), tc, {va:'t', max:70});
			C.rect(48, ty + 44 + Math.round(H * 0.2), 64, 5, {fill:P.accent}); pSub(C, 48, ty + 66 + Math.round(H * 0.2), W - 120, Math.round(H * 0.09), P.text, {max:18}); pMeta(C, 48, H - 60, W - 96, P.muted, {size:11, caps:true}); }
		else { var r2 = Math.round(H * 0.64), cx2 = Math.round(W - r2 * 0.42), cy2 = Math.round(H * 0.5);
			C.ell(cx2 - r2 - 26, cy2 - r2 - 26, (r2 + 26) * 2, (r2 + 26) * 2, {line:P.accent, lw:3, lop:0.8}); photo(C, cx2 - r2, cy2 - r2, r2 * 2, r2 * 2, {shape:'ell'}); C.ell(cx2 - r2 + 20, cy2 + r2 * 0.55, 34, 34, {fill:P.accent});
			var w = cx2 - r2 - 110; cLabel(C, 60, 90, w, P.accent); pTitle(C, 60, 122, w, Math.round(H * 0.38), tc, {max:58}); C.rect(60, 136 + Math.round(H * 0.38), 64, 5, {fill:P.accent});
			pSub(C, 60, 160 + Math.round(H * 0.38), w, Math.round(H * 0.16), P.text, {max:16}); pMeta(C, 60, H - 70, w, P.muted, {size:11, caps:true}); } }},
	{n:'Polaroid stack', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, pa = '#FFFFFF', tc = tcol(P), ink = '#1F2937';
		function pol(x, y, w, h, rot, fill){ var e = C.rect(x, y, w, h, {fill:fill, r:4, shadow:{o:0.22, b:22, d:8}}); e.rot = rot; return e; }
		if (z.port) { var w = Math.round(W * 0.66), h = Math.round(w * 1.18), x = Math.round((W - w) / 2), y = 70;
			pol(x - 30, y + 20, w, h, -7, mix(P.accent, '#FFFFFF', 0.55)); pol(x + 34, y + 10, w, h, 6, mix(acc2(P), '#FFFFFF', 0.5)); pol(x, y, w, h, 0, pa);
			photo(C, x + 18, y + 18, w - 36, w - 36); C.text(x + 18, y + w - 6, w - 36, h - w - 12, C.cover.company || yearOf(C), {font:'head', fit:{max:26, min:12}, wt:700, italic:true, color:ink, align:'c', va:'m', lh:1, caps:false, maxLines:1, ed:C.cover.company ? {f:'tf-company'} : null});
			var ty = y + h + 56; pTitle(C, 48, ty, W - 96, Math.round(H * 0.15), tc, {al:'c', va:'t', max:60}); pSub(C, 60, ty + 14 + Math.round(H * 0.15), W - 120, Math.round(H * 0.08), P.text, {al:'c', max:17});
			pMeta(C, 48, H - 56, W - 96, P.muted, {size:11, caps:true, al:'c'}); }
		else { var h2 = H - 170, w2 = Math.round(h2 * 0.85), x2 = W - w2 - 110, y2 = 80;
			pol(x2 - 40, y2 + 12, w2, h2, -8, mix(P.accent, '#FFFFFF', 0.55)); pol(x2 + 40, y2 + 6, w2, h2, 7, mix(acc2(P), '#FFFFFF', 0.5)); pol(x2, y2, w2, h2, 0, pa);
			photo(C, x2 + 16, y2 + 16, w2 - 32, w2 - 32); C.text(x2 + 16, y2 + w2 - 6, w2 - 32, h2 - w2 - 10, C.cover.company || yearOf(C), {font:'head', fit:{max:24, min:11}, wt:700, italic:true, color:ink, align:'c', va:'m', lh:1, caps:false, maxLines:1, ed:C.cover.company ? {f:'tf-company'} : null});
			var w = x2 - 150; C.text(60, 96, w, 20, yearOf(C), {font:'body', size:13, wt:800, color:P.accent, caps:true, ls:0.2, va:'m', lh:1}); pTitle(C, 60, 128, w, Math.round(H * 0.4), tc, {max:56});
			pSub(C, 60, 144 + Math.round(H * 0.4), w, Math.round(H * 0.18), P.text, {max:16}); pMeta(C, 60, H - 74, w, P.muted, {size:11, caps:true}); } }},
	{n:'Poster type', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), yr = yearOf(C);
		if (z.port) { ghost(C, -10, 30, W + 20, Math.round(H * 0.24), yr, P.accent, 0.14, 'c');
			C.rect(40, 40, W - 80, 3, {fill:tc}); cLabel(C, 40, 54, W / 2, tc); C.text(W / 2, 54, W / 2 - 40, 20, C.cover.date || yr, {font:'body', size:12, wt:800, color:tc, caps:true, ls:0.2, align:'r', va:'m', lh:1, maxLines:1, ed:C.cover.date ? {f:'tf-date'} : null});
			pTitle(C, 34, Math.round(H * 0.2), W - 68, Math.round(H * 0.38), tc, {va:'m', max:150, caps:true, lh:0.9, lines:5});
			var py = Math.round(H * 0.61); photo(C, 40, py, W - 80, Math.round(H * 0.2)); C.rect(40, py + Math.round(H * 0.2) - 8, Math.round((W - 80) * 0.35), 8, {fill:P.accent});
			pSub(C, 40, py + Math.round(H * 0.2) + 24, W - 80, Math.round(H * 0.08), P.text, {max:17}); C.rect(40, H - 74, W - 80, 1.5, {fill:tc, op:0.6}); pMeta(C, 40, H - 58, W - 80, P.muted, {size:11, caps:true}); }
		else { var sw = Math.round(W * 0.26); ghost(C, 30, H - 230, W - sw - 80, 220, yr, P.accent, 0.12);
			C.rect(48, 44, W - sw - 120, 3, {fill:tc}); cLabel(C, 48, 58, W / 2, tc);
			pTitle(C, 42, 96, W - sw - 130, Math.round(H * 0.52), tc, {va:'t', max:120, caps:true, lh:0.9, lines:4});
			pSub(C, 48, 120 + Math.round(H * 0.52), W - sw - 160, Math.round(H * 0.14), P.text, {max:16, lines:3}); pMeta(C, 48, H - 56, W - sw - 140, P.muted, {size:11, caps:true});
			photo(C, W - sw - 40, 40, sw, H - 80); C.rect(W - sw - 40, 40, 8, H - 80, {fill:P.accent}); } }},
	{n:'Sidebar index', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), sb = z.port ? 86 : 110, oc = on(P.accent, P);
		C.rect(0, 0, sb, H, {fill:P.accent}); C.text(0, 30, sb, 30, (C.cover.company || 'D').charAt(0).toUpperCase(), {font:'head', size:28, wt:900, color:oc, align:'c', va:'m', lh:1, caps:false});
		var labels = ['01', '02', '03', '04'], y0 = Math.round(H * (z.port ? 0.32 : 0.3)), gap = z.port ? 64 : 56;
		labels.forEach(function(l, i){ var y = y0 + i * gap; if (!i) C.rect(sb - 6, y - 4, 6, 30, {fill:oc}); C.text(0, y, sb, 22, l, {font:'head', size:i ? 14 : 18, wt:800, color:oc, op:i ? 0.55 : 1, align:'c', va:'m', lh:1, caps:false}); });
		vLabel(C, sb / 2, H - 150, 220, C.cover.website || '', oc);
		if (z.port) { var x = sb + 40, w = W - x - 40; cLabel(C, x, 70, w, P.accent); pTitle(C, x, 100, w, Math.round(H * 0.2), tc, {va:'t', max:60});
			pSub(C, x, 116 + Math.round(H * 0.2), w, Math.round(H * 0.1), P.text, {max:17}); pMeta(C, x, 130 + Math.round(H * 0.34), w, P.muted, {stack:true, size:12, gap:22});
			var py = Math.round(H * 0.56); photo(C, x, py, W - x, H - py - 40, {r:20}); C.rect(x - 14, py + 30, 14, 120, {fill:acc2(P)}); }
		else { var x2 = sb + 54, pw = Math.round(W * 0.36), w2 = W - x2 - pw - 100; cLabel(C, x2, 80, w2, P.accent); pTitle(C, x2, 112, w2, Math.round(H * 0.4), tc, {max:52});
			pSub(C, x2, 128 + Math.round(H * 0.4), w2, Math.round(H * 0.18), P.text, {max:16}); pMeta(C, x2, H - 80, w2, P.muted, {size:11, caps:true});
			photo(C, W - pw - 50, 50, pw, H - 100, {r:20}); C.rect(W - pw - 64, 90, 14, 140, {fill:acc2(P)}); } }},
	{n:'Stair blocks', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), n = 4;
		if (z.port) { var sw = W / n, base = H; for (var i = 0; i < n; i++) { var h = Math.round(H * (0.06 + i * 0.05)); C.rect(i * sw, base - h, sw + 1, h, {fill:mix(P.accent, acc2(P), i / (n - 1)), op:0.35 + i * 0.2}); }
			var ps = Math.round(W * 0.56); photo(C, W - ps - 40, 60, ps, ps, {r:12}); C.rect(W - ps - 58, 78, 18, ps - 36, {fill:P.accent});
			cLabel(C, 48, 70, W - ps - 120, P.accent); C.text(48, 100, W - ps - 120, 60, yearOf(C), {font:'head', size:40, wt:800, color:P.accent, va:'m', lh:1, caps:false});
			var ty = 60 + ps + 40; pTitle(C, 48, ty, W - 96, Math.round(H * 0.17), tc, {va:'t', max:64}); pSub(C, 48, ty + 14 + Math.round(H * 0.17), W - 96, Math.round(H * 0.07), P.text, {max:17});
			pMeta(C, 48, Math.round(H * 0.73), W - 96, P.muted, {size:11, caps:true}); }
		else { var sw2 = Math.round(W * 0.55) / n; for (var j = 0; j < n; j++) { var h2 = Math.round(H * (0.08 + j * 0.065)); C.rect(j * sw2, H - h2, sw2 + 1, h2, {fill:mix(P.accent, acc2(P), j / (n - 1)), op:0.35 + j * 0.2}); }
			var pw = Math.round(W * 0.38), ph = H - 120; photo(C, W - pw - 50, 60, pw, ph, {r:12}); C.rect(W - pw - 50, 60 + ph - 10, Math.round(pw * 0.5), 10, {fill:P.accent});
			var w = W - pw - 160; cLabel(C, 56, 64, w, P.accent); pTitle(C, 56, 96, w, Math.round(H * 0.3), tc, {max:54}); pSub(C, 56, 110 + Math.round(H * 0.3), w * 0.9, Math.round(H * 0.12), P.text, {max:16});
			pMeta(C, 56, 124 + Math.round(H * 0.42), w, P.muted, {size:11, caps:true}); } }},
	{n:'Bauhaus shapes', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), ink = inkOf(P), a2 = acc2(P);
		if (z.port) { var s = Math.round(W / 2);
			C.rect(0, 0, s, s, {fill:P.accent}); C.ell(-s / 2, -s / 2, s, s, {fill:a2}); C.rect(s, 0, s, s, {fill:ink}); photo(C, s + 24, 24, s - 48, s - 48, {shape:'ell'});
			C.shape('triangle', 0, s, s, Math.round(s * 0.8), {fill:mix(P.accent, '#FFFFFF', 0.25)}); C.rect(s, s, s, Math.round(s * 0.8), {fill:mix(a2, '#FFFFFF', 0.6)}); C.ell(s + s / 4, s + s * 0.15, s / 2, s / 2, {fill:P.accent});
			var ty = s + Math.round(s * 0.8) + 40; cLabel(C, 48, ty, W - 96, P.accent); pTitle(C, 48, ty + 28, W - 96, Math.round(H * 0.16), tc, {va:'t', max:60, caps:true});
			pSub(C, 48, ty + 40 + Math.round(H * 0.16), W - 96, Math.round(H * 0.07), P.text, {max:16}); pMeta(C, 48, H - 56, W - 96, P.muted, {size:11, caps:true}); }
		else { var s2 = Math.round(H / 2), x0 = W - s2 * 2;
			C.rect(x0, 0, s2, s2, {fill:P.accent}); C.ell(x0 - s2 / 2, -s2 / 2, s2, s2, {fill:a2}); C.rect(x0 + s2, 0, s2, s2, {fill:ink}); photo(C, x0 + s2 + 20, 20, s2 - 40, s2 - 40, {shape:'ell'});
			C.shape('triangle', x0, s2, s2, s2, {fill:mix(P.accent, '#FFFFFF', 0.25)}); C.rect(x0 + s2, s2, s2, s2, {fill:mix(a2, '#FFFFFF', 0.6)}); C.ell(x0 + s2 + s2 / 4, s2 + s2 / 4, s2 / 2, s2 / 2, {fill:P.accent});
			var w = x0 - 110; cLabel(C, 56, 90, w, P.accent); pTitle(C, 56, 122, w, Math.round(H * 0.36), tc, {max:54, caps:true}); C.rect(56, 136 + Math.round(H * 0.36), 70, 6, {fill:a2});
			pSub(C, 56, 160 + Math.round(H * 0.36), w, Math.round(H * 0.16), P.text, {max:16}); pMeta(C, 56, H - 70, w, P.muted, {size:11, caps:true}); } }},
	{n:'Swiss grid', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), cols = z.port ? 4 : 6, m = z.port ? 36 : 48, cw = (W - 2 * m) / cols;
		for (var i = 0; i <= cols; i++) C.line(m + i * cw, 0, m + i * cw, H, {color:P.muted, lw:1, op:0.3}); C.line(0, Math.round(H * 0.12), W, Math.round(H * 0.12), {color:P.muted, lw:1, op:0.3});
		C.text(m, 18, cw * 2, Math.round(H * 0.12) - 24, C.cover.company || '', {font:'body', size:12, wt:800, color:tc, caps:true, ls:0.14, va:'m', lh:1, maxLines:1, ed:C.cover.company ? {f:'tf-company'} : null});
		C.text(m + cw * (cols - 2), 18, cw * 2 - 10, Math.round(H * 0.12) - 24, C.cover.date || yearOf(C), {font:'body', size:12, wt:700, color:P.muted, caps:true, align:'r', va:'m', lh:1, maxLines:1, ed:C.cover.date ? {f:'tf-date'} : null});
		if (z.port) { photo(C, m + cw, Math.round(H * 0.12), cw * 3, Math.round(H * 0.36)); C.text(m, Math.round(H * 0.12) + 10, cw, 80, '01', {font:'head', size:54, wt:900, color:P.accent, va:'t', lh:1, caps:false});
			var ty = Math.round(H * 0.52); pTitle(C, m, ty, cw * 4, Math.round(H * 0.2), tc, {va:'t', max:66}); C.rect(m, ty + Math.round(H * 0.21), cw, 6, {fill:P.accent});
			pSub(C, m + cw, ty + Math.round(H * 0.23), cw * 3, Math.round(H * 0.1), P.text, {max:17}); pMeta(C, m + cw, H - 120, cw * 3, P.muted, {stack:true, size:12, gap:22}); }
		else { photo(C, m + cw * 3, Math.round(H * 0.12), cw * 3, H - Math.round(H * 0.12) - 50); C.text(m, Math.round(H * 0.12) + 14, cw, 70, '01', {font:'head', size:52, wt:900, color:P.accent, va:'t', lh:1, caps:false});
			pTitle(C, m, Math.round(H * 0.3), cw * 3 - 30, Math.round(H * 0.36), tc, {va:'t', max:54}); C.rect(m, Math.round(H * 0.68), cw, 6, {fill:P.accent});
			pSub(C, m + cw, Math.round(H * 0.7), cw * 2 - 30, Math.round(H * 0.16), P.text, {max:15}); pMeta(C, m, H - 40, cw * 3 - 30, P.muted, {size:11, caps:true}); } }},
	{n:'Badge seal', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), oc = on(P.accent, P);
		function badge(cx, cy, r){ C.ell(cx - r - 8, cy - r - 8, (r + 8) * 2, (r + 8) * 2, {fill:P.bg}); C.ell(cx - r, cy - r, r * 2, r * 2, {fill:P.accent}); C.ell(cx - r + 10, cy - r + 10, r * 2 - 20, r * 2 - 20, {line:oc, lw:2, lop:0.7});
			C.text(cx - r, cy - r * 0.42, r * 2, r * 0.3, 'EST.', {font:'body', size:Math.round(r * 0.16), wt:800, color:oc, caps:true, ls:0.3, align:'c', va:'m', lh:1});
			C.text(cx - r, cy - r * 0.16, r * 2, r * 0.5, yearOf(C), {font:'head', size:Math.round(r * 0.42), wt:900, color:oc, align:'c', va:'m', lh:1, caps:false});
			C.text(cx - r * 0.8, cy + r * 0.36, r * 1.6, r * 0.24, C.cover.company || '', {font:'body', fit:{max:Math.round(r * 0.14), min:8}, wt:800, color:oc, caps:true, ls:0.16, align:'c', va:'m', lh:1, maxLines:1}); }
		if (z.port) { var ph = Math.round(H * 0.46); photo(C, 0, 0, W, ph); var r = Math.round(W * 0.15); badge(W / 2, ph, r);
			var ty = ph + r + 40; pTitle(C, 48, ty, W - 96, Math.round(H * 0.17), tc, {al:'c', va:'t', max:62}); pSub(C, 64, ty + 14 + Math.round(H * 0.17), W - 128, Math.round(H * 0.08), P.text, {al:'c', max:17});
			pMeta(C, 48, H - 60, W - 96, P.muted, {size:11, caps:true, al:'c'}); }
		else { var px = Math.round(W * 0.52); photo(C, px, 0, W - px, H); var r2 = Math.round(H * 0.17); badge(px, Math.round(H * 0.5), r2);
			var w = px - r2 - 100; cLabel(C, 56, 80, w, P.accent); pTitle(C, 56, 112, w, Math.round(H * 0.38), tc, {max:52}); C.rect(56, 126 + Math.round(H * 0.38), 60, 5, {fill:P.accent});
			pSub(C, 56, 150 + Math.round(H * 0.38), w, Math.round(H * 0.16), P.text, {max:16}); pMeta(C, 56, H - 66, w, P.muted, {size:11, caps:true}); } }},
	{n:'Overlap card', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, pa = paperOf(P), ink = inkOf(P);
		if (z.port) { var ph = Math.round(H * 0.56); photo(C, 0, 0, W, ph); var cx = 40, cy = ph - 150, cw = W - 80, ch = Math.round(H * 0.34);
			C.rect(cx, cy, cw, ch, {fill:pa, r:14, shadow:{o:0.22, b:34, d:12}}); C.rect(cx, cy, 10, ch, {fill:P.accent, r:0});
			cLabel(C, cx + 36, cy + 34, cw - 70, P.accent); pTitle(C, cx + 36, cy + 64, cw - 70, Math.round(ch * 0.5), ink, {va:'t', max:56});
			pSub(C, cx + 36, cy + ch - Math.round(ch * 0.3), cw - 70, Math.round(ch * 0.24), mix(ink, pa, 0.3), {max:16}); pMeta(C, 48, H - 76, W - 96, P.muted, {stack:false, size:11, caps:true}); }
		else { var pw = Math.round(W * 0.6); photo(C, 0, 0, pw, H); var cx2 = Math.round(W * 0.46), cw2 = W - cx2 - 56, ch2 = Math.round(H * 0.66), cy2 = Math.round((H - ch2) / 2);
			C.rect(cx2, cy2, cw2, ch2, {fill:pa, r:14, shadow:{o:0.22, b:34, d:12}}); C.rect(cx2, cy2, cw2, 10, {fill:P.accent, r:0});
			cLabel(C, cx2 + 40, cy2 + 46, cw2 - 80, P.accent); pTitle(C, cx2 + 40, cy2 + 78, cw2 - 80, Math.round(ch2 * 0.44), ink, {va:'t', max:48});
			pSub(C, cx2 + 40, cy2 + 92 + Math.round(ch2 * 0.44), cw2 - 80, Math.round(ch2 * 0.22), mix(ink, pa, 0.3), {max:15}); pMeta(C, cx2 + 40, cy2 + ch2 - 50, cw2 - 80, mix(ink, pa, 0.4), {size:11, caps:true}); } }},
	{n:'Gradient blob', fn:function(C){ var z = SZ(), W = z.W, H = z.H, P = C.P, tc = tcol(P), a2 = acc2(P);
		function blob(x, y, w, h, rot, ca, cb, op){ var e = C.rect(x, y, w, h, {r:Math.min(w, h) / 2, op:op, grad:{ang:135, stops:[[0, ca], [100, cb]]}}); e.rot = rot; return e; }
		if (z.port) { blob(W * 0.18, -90, W * 1.0, H * 0.46, -18, P.accent, a2, 0.95); blob(W * 0.45, H * 0.9, W * 0.8, H * 0.18, -10, a2, P.accent, 0.3);
			var d = Math.round(W * 0.5), px = Math.round(W * 0.42), py = 90; C.ell(px - 14, py - 14, d + 28, d + 28, {fill:'#FFFFFF', op:0.9}); photo(C, px, py, d, d, {shape:'ell'});
			var ty = Math.round(H * 0.5); cLabel(C, 48, ty, W - 96, P.accent); pTitle(C, 48, ty + 30, W - 96, Math.round(H * 0.18), tc, {va:'t', max:64});
			pSub(C, 48, ty + 44 + Math.round(H * 0.18), W - 120, Math.round(H * 0.08), P.text, {max:17}); chips(C, 48, H - 160, W - 96, tc, P.accent, {stack:true}); }
		else { blob(W * 0.5, -H * 0.18, W * 0.62, H * 0.86, -16, P.accent, a2, 0.95); blob(W * 0.36, H * 0.72, W * 0.4, H * 0.28, 10, a2, P.accent, 0.35);
			var d2 = Math.round(H * 0.56), px2 = Math.round(W * 0.64), py2 = Math.round(H * 0.18); C.ell(px2 - 14, py2 - 14, d2 + 28, d2 + 28, {fill:'#FFFFFF', op:0.9}); photo(C, px2, py2, d2, d2, {shape:'ell'});
			var w = Math.round(W * 0.5); cLabel(C, 56, 80, w, P.accent); pTitle(C, 56, 112, w, Math.round(H * 0.36), tc, {max:58}); pSub(C, 56, 126 + Math.round(H * 0.36), w - 40, Math.round(H * 0.15), P.text, {max:16});
			chips(C, 56, H - 90, Math.round(W * 0.56), tc, P.accent, {stack:false}); } }}
	]);
	/* ---------- variations: a design can be mirrored and its photos rounded or pill-shaped, so every template opens with its own cover ---------- */
	var FLIP = {chevron:1, homePlate:1};
	function mirrorEls(els, W){
		els.forEach(function(e){
			if (!e) return;
			if (e.t === 'line') { e.x1 = W - e.x1; e.x2 = W - e.x2; return; }
			if (e.t === 'path') { if (Array.isArray(e.d)) e.d = e.d.map(function(c){ if (!Array.isArray(c)) return c; var n = c.slice(); for (var i = 1; i + 1 < n.length; i += 2) n[i] = W - n[i]; return n; }); return; }
			if (e.x == null) return;
			var w = e.w != null ? e.w : (e.s != null ? e.s : (e.size != null ? e.size : 0));
			e.x = W - e.x - w;
			if (e.t === 'text') { if (e.align === 'l' || !e.align) e.align = 'r'; else if (e.align === 'r') e.align = 'l'; return; }
			if (e.rot && e.t !== 'img') e.rot = -e.rot;
			if (e.t === 'shape' && FLIP[e.prst]) e.rot = ((e.rot || 0) + 180) % 360;
			if (e.grad && e.grad.ang != null) e.grad.ang = (360 - e.grad.ang) % 360;
		});
	}
	function runPat(C, o){
		var p = PATS[o.pat]; if (!p) return false;
		var n0 = C.els.length; PHS = o.ph || 0;
		try { p.fn(C); } finally { PHS = 0; }
		if (o.mir) mirrorEls(C.els.slice(n0), SZ().W);
		return true;
	}
	/* every template opens with its own cover: the designs are dealt out in the order the templates are listed (so neighbours never match),
	   then dealt again mirrored, then with rounded photos, then with arch photos - 180 combinations for the 129 templates */
	var NP = PATS.length, hsh = 0; String(T.id).split('').forEach(function(ch){ hsh = (hsh * 31 + ch.charCodeAt(0)) % 9973; });
	var IDX = (D.order && D.order.indexOf(T.id) >= 0) ? D.order.indexOf(T.id) : hsh;
	var DEF = {pat:IDX % NP, mir:Math.floor(IDX / NP) % 2, ph:Math.floor(IDX / (NP * 2)) % 3};
	function patVariants(first){
		var out = [];
		if (first) out.push(V('v1', PATS[DEF.pat].n + (DEF.mir ? ' (mirrored)' : '') + (DEF.ph === 1 ? ' \u00B7 rounded photo' : (DEF.ph === 2 ? ' \u00B7 arch photo' : '')), {pat:DEF.pat, mir:DEF.mir, ph:DEF.ph}));
		PATS.forEach(function(p, i){ out.push(V('pt' + (i + 1), p.n, {pat:i})); });
		return out;
	}

	/* background. Classic: accent bar along the top. Other templates: their own background pieces, moved to the new shape */
	function bg(C, kind){
		var P = C.P, z = SZ(), self = this, args = arguments;
		if (!cls) {
			if (o0.bg) remapRun(C, function(){ o0.bg.apply(self, args); });
			var S0 = C.S || T.s || {}, hd = S0.header || {}, ft = S0.footer;
			var ov2 = {margin: Math.min(S0.margin || 64, z.port ? 52 : 60), bodyGap: z.port ? 28 : 22, concH: z.port ? 76 : 58,
				header: {tMax: Math.min(hd.tMax || 44, z.port ? 40 : 38), tH: z.port ? 110 : 100}};
			if (z.port && hd.kind === 'split') ov2.header.kind = 'left';
			if (ft && typeof ft.fn === 'function') { var ff = ft.fn; ov2.footer = {fn:function(C2){ return remapRun(C2, function(){ return ff(C2); }); }}; }
			else if (ft && ft.y) ov2.footer = {y:null};
			C.S = D.merge(S0, ov2);
			return;
		}
		C.S = D.merge(T.s || {}, {
			margin: z.port ? 48 : 56, bodyGap: z.port ? 28 : 22, concH: z.port ? 76 : 58,
			header: {sc:P.accent, top: z.port ? 64 : 44, tMax: z.port ? 40 : 38, tMin: 22, sMax: z.port ? 18 : 16, tH: z.port ? 110 : 100},
			card: {fill:tint(P)},
			conc: {fill:tint(P, 0.93), line:tint(P, 0.7), lw:1, lc:P.accent, tc:P.text, max: z.port ? 17 : 16}
		});
		C.bg({fill:P.bg});
		if (kind !== 'cover' && kind !== 'closing') C.rect(0, 0, z.W, 6, {fill:P.accent});
	}

	/* Classic card: round icon (or number) + title, accent rule, text. Fonts grow with the card. */
	function card1(C, x, y, w, h, t, F, hd, de){
		var P = C.P, pad = F.pad, ms = F.ms, hx = x + pad + ms + 12, hw = w - pad * 2 - ms - 12, my = y + pad + Math.max(0, (F.hb - ms) / 2);
		var cc = cb(C, x, y, w, h, 18);
		if (F.num) numDisc(C, x + pad, my, ms, F.num); else D.mark(C, x + pad, my, ms, icon(C, t), {});
		if (hd) C.text(hx, y + pad, hw, F.hb, hd, {font:'head', size:F.hs, color:hc(cc, P), va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
		if (!de) return;
		var ry = y + pad + F.hb + 10;
		C.rect(x + pad, ry, w - pad * 2, 2.5, {fill:P.accent});
		C.text(x + pad, ry + 14, w - pad * 2, Math.max(16, y + h - pad - (ry + 14)), de, {font:'body', size:F.ds, color:cc.tc, lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
	}
	function grid(C, Z, o){
		var it = C.it.slice(0, o.max || 16), n = it.length; if (!n) return;
		var cols = Math.max(1, Math.min(n, o.cols || 1)), rows = Math.ceil(n / cols);
		var g = o.gap || (rows > 4 ? 12 : (rows > 2 ? 16 : 20)), ch = (Z.h - g * (rows - 1)) / rows;
		var cw = (Z.w - g * (cols - 1)) / cols, anyD = it.some(function(t){ return t.h ? !!t.d : !o.num; });
		ch = Math.min(ch, !anyD ? Math.max(90, Math.min(150, cw * 0.5)) : (SZ().port ? Math.max(200, cw * 1.1) : ch)); /* short cards when there is little text */
		var y0 = Z.y + Math.max(0, (Z.h - (ch * rows + g * (rows - 1))) * 0.35), pad = Math.min(cw, ch) < 150 ? 14 : 22;
		var ms = Math.round(Math.max(26, Math.min(46, cw * 0.16, ch * 0.24)));
		var tw = cw - pad * 2, hw = tw - ms - 12;
		var hMax = Math.round(Math.max(14, Math.min(30, ch * 0.12, cw * 0.075))), dMax = Math.round(Math.max(12, Math.min(26, ch * 0.1, cw * 0.06)));
		var heads = it.map(function(t){ return t.h || (o.num ? t.d : ''); });
		var descs = it.map(function(t){ return t.h ? t.d : (o.num ? '' : t.d); });
		var hs = C.groupFit(heads, hw, 80, {font:'head', max:hMax, min:12, maxLines:2});
		var hh = 0; heads.forEach(function(s){ if (s) hh = Math.max(hh, C.measureText(s, hw, {font:'head', max:hs, min:hs, maxLines:2}).h); });
		var hb = Math.max(ms, hh);
		var ds = C.groupFit(descs, tw, Math.max(18, ch - pad * 2 - hb - 26), {font:'body', max:dMax, min:10, lh:1.45});
		for (var r = 0; r < rows; r++) {
			var m = Math.min(cols, n - r * cols), cw2 = (Z.w - g * (m - 1)) / m;
			for (var k = 0; k < m; k++) {
				var q = r * cols + k;
				card1(C, Z.x + k * (cw2 + g), y0 + r * (ch + g), cw2, ch, it[q], {pad:pad, ms:ms, hb:hb, hs:hs, ds:ds, num:o.num ? q + 1 : 0}, heads[q], descs[q]);
			}
		}
	}
	/* full-width rows: icon (or number) on the left, title + text on the right; 1 or 2 columns */
	function list(C, Z, o){
		var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
		var P = C.P, z = SZ(), cols = Math.max(1, Math.min(o.cols || 1, n)), per = Math.ceil(n / cols), gx = 24;
		var cw = (Z.w - gx * (cols - 1)) / cols, g = per > 6 ? 10 : 14;
		var rh = Math.min((Z.h - g * (per - 1)) / per, o.maxH || (z.port ? 210 : 150));
		var ms = Math.round(Math.max(26, Math.min(50, rh * 0.42))), tx = 22 + ms + 18, tw = cw - tx - 20;
		var heads = it.map(function(t){ return t.h || (o.num ? t.d : ''); });
		var descs = it.map(function(t){ return t.h ? t.d : (o.num ? '' : t.d); });
		var anyH = heads.some(Boolean), anyD = descs.some(Boolean);
		var hs = anyH ? C.groupFit(heads, tw, anyD ? rh * 0.42 : rh - 20, {font:'head', max:Math.round(Math.max(14, Math.min(28, rh * (anyD ? 0.2 : 0.28)))), min:12, maxLines:anyD ? 1 : 2}) : 0;
		var ds = anyD ? C.groupFit(descs, tw, Math.max(16, rh - 20 - (anyH ? hs * 1.2 + 6 : 0)), {font:'body', max:Math.round(Math.max(12, Math.min(22, rh * 0.15))), min:10, lh:1.4}) : 0;
		var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
		it.forEach(function(t, k){
			var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g), hd = heads[k], de = descs[k];
			var cc = cb(C, x, y, cw, rh, 16);
			C.rect(x, y, 6, rh, {fill:P.accent, r:0});
			var my = y + (rh - ms) / 2;
			if (o.num) numDisc(C, x + 22, my, ms, k + 1); else D.mark(C, x + 22, my, ms, icon(C, t), {});
			var hH = hd ? C.measureText(hd, tw, {font:'head', max:hs, min:hs, maxLines:de ? 1 : 2}).h : 0;
			var dH = de ? Math.min(rh - 20 - (hd ? hH + 6 : 0), C.measureText(de, tw, {font:'body', max:ds, min:ds, lh:1.4}).h) : 0;
			var ty = y + Math.max(10, (rh - (hH + (hd && de ? 6 : 0) + dH)) / 2), dy = ty + (hd ? hH + 6 : 0);
			if (hd) C.text(x + tx, ty, tw, hH + 2, hd, {font:'head', size:hs, color:hc(cc, P), lh:1.15, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:de ? 1 : 2});
			if (de) C.text(x + tx, dy, tw, Math.max(16, y + rh - 10 - dy), de, {font:'body', size:ds, color:cc.tc, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
		});
	}
	/* plain-text slide: one soft panel with an accent edge, sized to its text */
	function statement(C, Z, o){
		var it = C.it[0]; if (!it) return;
		var P = C.P, z = SZ(), pad = z.port ? 40 : 38, w = Z.w - pad * 2 - 8, x = Z.x + pad + 8;
		var hm = it.h ? C.measureText(it.h, w, {font:'head', max:z.port ? 36 : 30, min:16, maxLines:3, h:z.port ? 150 : 100}) : null;
		var hH = hm ? hm.h : 0, room = Math.max(40, Z.h - pad * 2 - (hm ? hH + 16 : 0));
		var dm = C.measureText(it.d, w, {font:'body', max:z.port ? (it.h ? 28 : 32) : (it.h ? 24 : 27), min:13, lh:1.5, h:room});
		var inner = (hm ? hH + 16 : 0) + Math.min(room, dm.h), ph = Math.min(Z.h, Math.max(inner + pad * 2, Z.h * (z.port ? 0.5 : 0.62)));
		var y0 = Z.y + (Z.h - ph) * 0.4, y = y0 + Math.max(pad, (ph - inner) / 2);
		var cc = cb(C, Z.x, y0, Z.w, ph, 18);
		C.rect(Z.x, y0, 8, ph, {fill:P.accent, r:0});
		if (hm) { C.text(x, y, w, hH + 4, it.h, {font:'head', size:hm.s, color:hc(cc, P), lh:1.15, ed:{f:'x-part', i:it.i, p:0}, maxLines:3}); y += hH + 16; }
		C.text(x, y, w, Math.max(20, y0 + ph - pad - y), it.d, {font:'body', size:dm.s, color:cc.tc, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
	}
	/* stats stacked: big number on the left, label (+ note) on the right */
	function statRows(C, Z, o){
		var st = C.it.slice(0, 4), n = st.length; if (!n) return;
		var P = C.P, z = SZ(), g = 16, rh = Math.min((Z.h - g * (n - 1)) / n, z.port ? 200 : 140), vw = Math.round(Z.w * (z.port ? 0.42 : 0.34)), pad = 24;
		var vals = st.map(sv), labs = st.map(sl), lw = Z.w - vw - pad * 2;
		var vs = C.groupFit(vals, vw - pad * 2, rh * 0.62, {font:'head', max:Math.round(Math.min(z.port ? 68 : 54, rh * 0.42)), min:22, maxLines:1});
		var ls = C.groupFit(labs, lw, rh * 0.5, {font:'body', max:Math.round(Math.min(22, rh * 0.15)), min:11, lh:1.35});
		var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), lw, rh * 0.32, {font:'body', max:Math.round(Math.min(17, rh * 0.11)), min:10, lh:1.35});
		var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
		st.forEach(function(t, k){
			var y = y0 + k * (rh + g);
			var cc = cb(C, Z.x, y, Z.w, rh, 18);
			C.rect(Z.x, y, 6, rh, {fill:P.accent, r:0});
			C.text(Z.x + pad, y, vw - pad * 2, rh, vals[k], {font:'head', size:vs, color:cc.tc === P.text ? (P.title || P.accent) : cc.tc, va:'m', lh:1.05, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
			C.rect(Z.x + vw, y + 18, 2, rh - 36, {fill:P.accent, op:0.5});
			var lH = C.measureText(labs[k], lw, {font:'body', max:ls, min:ls, lh:1.35}).h, xH = t.x ? C.measureText(t.x, lw, {font:'body', max:xs, min:xs, lh:1.35}).h : 0;
			var ty = y + Math.max(10, (rh - (lH + (xH ? xH + 6 : 0))) / 2);
			C.text(Z.x + vw + pad, ty, lw, Math.min(lH + 4, rh - 20), labs[k], {font:'body', size:ls, wt:600, color:cc.tc, lh:1.35, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
			if (t.x) C.text(Z.x + vw + pad, ty + lH + 6, lw, Math.max(14, xH + 4), t.x, {font:'body', size:xs, color:cc.mc, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
		});
	}
	/* stat cards, 2 across */
	function statGrid(C, Z, o){
		var st = C.it.slice(0, 4), n = st.length; if (!n) return;
		var P = C.P, cols = n === 1 ? 1 : 2, rows = Math.ceil(n / cols), g = 18, pad = 24;
		var ch = Math.min((Z.h - g * (rows - 1)) / rows, SZ().port ? 380 : 300), cw = (Z.w - g * (cols - 1)) / cols, iw = cw - pad * 2;
		var vals = st.map(sv), labs = st.map(sl);
		var vs = C.groupFit(vals, iw, ch * 0.3, {font:'head', max:Math.round(Math.min(60, ch * 0.24)), min:22, maxLines:1});
		var ls = C.groupFit(labs, iw, Math.max(20, ch - pad * 2 - 70 - vs * 1.15), {font:'body', max:Math.round(Math.min(20, ch * 0.08)), min:10.5, lh:1.4});
		var tot = ch * rows + g * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.35);
		st.forEach(function(t, k){
			var r = Math.floor(k / cols), c = k % cols, m = Math.min(cols, n - r * cols), cw2 = (Z.w - g * (m - 1)) / m, x = Z.x + c * (cw2 + g), y = y0 + r * (ch + g);
			var cc = cb(C, x, y, cw2, ch, 18);
			C.rect(x, y, cw2, 6, {fill:P.accent, r:0});
			D.mark(C, x + pad, y + pad + 6, 40, D.iconFor(C.s, t.i, labs[k]), {});
			C.text(x + pad, y + pad + 58, cw2 - pad * 2, vs * 1.15, vals[k], {font:'head', size:vs, color:cc.tc === P.text ? (P.title || P.accent) : cc.tc, lh:1.05, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
			C.text(x + pad, y + pad + 70 + vs * 1.15, cw2 - pad * 2, Math.max(16, ch - pad * 2 - 70 - vs * 1.15), labs[k], {font:'body', size:ls, color:cc.tc, lh:1.4, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
		});
	}
	/* agenda: numbered rows or numbered cards */
	function ag(opt){
		return function(C){
			var z = SZ(), n = C.it.length;
			var fn = opt.cards
				? function(C2, Z){ grid(C2, Z, {num:1, max:10, cols:z.port ? (n > 4 ? 2 : 1) : (n > 6 ? 4 : (n > 4 ? 3 : 2))}); }
				: function(C2, Z){ list(C2, Z, {num:1, max:10, cols:opt.cols || (!z.port && n > 5 ? 2 : 1)}); };
			D.std(C, {body:fn, img:opt.img, imgPct:opt.imgPct});
		};
	}

	function chip(C, x, y, al, w){
		var P = C.P, f = C.cover; if (!f.company) return;
		var tw = Math.min(w - 40, f.company.length * 9.5 + 10), x0 = al === 'c' ? x + (w - 40 - tw) / 2 : x;
		C.rect(x0, y, 30, 30, {fill:P.accent, r:7});
		C.text(x0, y, 30, 30, String(f.company).charAt(0).toUpperCase(), {font:'head', size:15, wt:800, color:P.onAccent || '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
		C.text(x0 + 40, y, tw, 30, f.company, {font:'head', size:15, wt:700, color:tcol(P), va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
	}
	function meta(C, x, y, w, al){
		var P = C.P, f = C.cover, cells = [];
		if (f.presenter) cells.push({ic:'mdi:account', lb:'Presented by', v:f.presenter, f:'tf-presenter', sub:f.designation});
		if (f.date) cells.push({ic:'mdi:calendar-blank', lb:'Date', v:f.date, f:'tf-date'});
		if (!cells.length) return;
		var cw = Math.min(260, w / cells.length), x0 = al === 'c' ? x + (w - cw * cells.length) / 2 : x;
		cells.forEach(function(c, k){
			var cx = x0 + k * cw;
			C.ell(cx, y, 34, 34, {fill:P.accent}); C.icon(cx + 8, y + 8, 18, c.ic, P.onAccent || '#FFFFFF');
			C.text(cx + 44, y - 1, cw - 50, 14, c.lb, {font:'body', size:10.5, wt:700, color:P.muted, caps:true, ls:0.12, lh:1});
			C.text(cx + 44, y + 15, cw - 50, 22, c.v, {font:'head', size:15, wt:700, color:tcol(P), lh:1.1, ed:{f:c.f}, maxLines:1});
			if (c.sub) C.text(cx + 44, y + 38, cw - 50, 16, c.sub, {font:'body', size:11.5, color:P.muted, lh:1.1, maxLines:1});
		});
	}
	/* ---------- the template's OWN cover / closing, re-composed for 4:3 or 9:16 ----------
	   The template draws its cover in 1280 x 720 as usual. Its picture zone, title panel, accent rule, colours,
	   fonts and decorations are read from that drawing and placed again for the new shape: a side picture
	   becomes a top / bottom band in 9:16, a framed or round picture stays framed / round, a full-bleed photo
	   stays full bleed, decorations keep their edge / corner, and the text keeps the template's own styling. */
	function area(b){ return Math.max(0, b.w) * Math.max(0, b.h); }
	function inter(a, b){ var x = Math.max(a.x, b.x), y = Math.max(a.y, b.y), r = Math.min(a.x + a.w, b.x + b.w), q = Math.min(a.y + a.h, b.y + b.h); return Math.max(0, r - x) * Math.max(0, q - y); }
	function box(e){ return {x:e.x, y:e.y, w:e.w, h:e.h}; }
	function into(e, A, B){ /* move an element that sat inside box A to the same spot inside box B */
		var sx = B.w / Math.max(1, A.w), sy = B.h / Math.max(1, A.h);
		if (e.t === 'line') { e.x1 = B.x + (e.x1 - A.x) * sx; e.x2 = B.x + (e.x2 - A.x) * sx; e.y1 = B.y + (e.y1 - A.y) * sy; e.y2 = B.y + (e.y2 - A.y) * sy; return; }
		if (e.t === 'path') { if (Array.isArray(e.d)) e.d = e.d.map(function(c){ if (!Array.isArray(c) || c.length < 3) return c; var n = c.slice(); for (var i = 1; i + 1 < n.length; i += 2) { n[i] = B.x + (n[i] - A.x) * sx; n[i + 1] = B.y + (n[i + 1] - A.y) * sy; } return n; }); return; }
		e.x = B.x + (e.x - A.x) * sx; e.y = B.y + (e.y - A.y) * sy; e.w *= sx; e.h *= sy;
	}
	function tstyle(e, dflt){ /* the text options of one of the template's own text elements */
		var o = {font:dflt.font || 'body', color:dflt.color};
		if (!e) return o;
		o.font = e.font || o.font; o.color = e.color || o.color; o.wt = e.wt; o.caps = e.caps; o.ls = e.ls; o.italic = e.italic; o.op = e.op; o._size = e.size;
		return o;
	}
	function pathBox(e){
		var xs = [], ys = [];
		(Array.isArray(e.d) ? e.d : []).forEach(function(c){ if (!Array.isArray(c)) return; for (var i = 1; i + 1 < c.length; i += 2) { if (typeof c[i] === 'number') { xs.push(c[i]); ys.push(c[i + 1]); } } });
		if (!xs.length) return null;
		var x0 = Math.min.apply(null, xs), y0 = Math.min.apply(null, ys);
		return {x:x0, y:y0, w:Math.max(1, Math.max.apply(null, xs) - x0), h:Math.max(1, Math.max.apply(null, ys) - y0)};
	}
	function ebox(e){
		if (e.t === 'line') return {x:Math.min(e.x1, e.x2), y:Math.min(e.y1, e.y2), w:Math.abs(e.x2 - e.x1) + 1, h:Math.abs(e.y2 - e.y1) + 1};
		if (e.t === 'path') return pathBox(e);
		return (e.w > 0 && e.h > 0) ? box(e) : null;
	}
	function union(a, b){ if (!a) return {x:b.x, y:b.y, w:b.w, h:b.h}; var r = Math.max(a.x + a.w, b.x + b.w), q = Math.max(a.y + a.h, b.y + b.h); var x = Math.min(a.x, b.x), y = Math.min(a.y, b.y); return {x:x, y:y, w:r - x, h:q - y}; }
	function fitIn(A, Z, k){ var s = Math.min(Z.w / A.w, Z.h / A.h) * (k || 0.9), w = A.w * s, h = A.h * s; return {x:Z.x + (Z.w - w) / 2, y:Z.y + (Z.h - h) / 2, w:w, h:h}; }
	function adapt(C, ovar, kind, self){
		var z = SZ(), W = z.W, H = z.H, P = C.P, fn = kind === 'cover' ? (o0.cover || D.genericCover) : (o0.closing || D.genericClosing);
		var before = new Set(C.els), keepO = C.o, keepV = C.v;
		C.o = ovar.o || {}; C.v = ovar;
		D.setSize(1280, 720);
		try { fn.call(self || T, C, ovar); } finally { D.setSize(W, H); C.o = keepO; C.v = keepV; }
		var added = C.els.filter(function(e){ return !before.has(e); });
		C.els = C.els.filter(function(e){ return before.has(e); });
		var title = null, fields = {}, imgs = [], others = [];
		added.forEach(function(e){
			if (!e) return;
			if (e.t === 'text') { var f = e.ed && e.ed.f; if (f === 'title') { if (!title || e.size > title.size) title = e; } else if (f && f !== 'x-part' && !fields[f]) fields[f] = e; return; }
			if (e.t === 'img') { imgs.push(e); return; }
			if (e.t !== 'logo') others.push(e);
		});
		if (!title) return false;
		var SL = {x:0, y:0, w:1280, h:720}, SA = area(SL), TB = box(title), UB = box(title);
		Object.keys(fields).forEach(function(k){ var e = fields[k]; if (e.w > 0 && e.h > 0) UB = union(UB, box(e)); });
		imgs.sort(function(a, b){ return area(b) - area(a); });
		var im = imgs[0] || null, mode = null;
		if (im) {
			var ar = area(im) / SA;
			if (ar > 0.85) mode = 'bg';
			else if (im.h >= 560 && im.x >= 380) mode = 'right';
			else if (im.h >= 560 && im.x + im.w <= 900) mode = 'left';
			else if (im.w >= 1000 && im.y >= 240) mode = 'bottom';
			else if (im.w >= 1000 && im.y + im.h <= 480) mode = 'top';
			else mode = 'framed';
		}
		/* the title panel (a card behind the text) and the short accent rule under the title */
		var panel = null, rule = null;
		others.forEach(function(e){
			if (e.t !== 'rect' && e.t !== 'shape') return;
			var b = ebox(e); if (!b) return;
			if (b.h <= 10 && b.w <= 320 && b.y >= TB.y + TB.h - 14 && b.y <= TB.y + TB.h + 70 && !rule) { rule = e; return; }
			if (area(b) < SA * 0.7 && inter(b, TB) >= area(TB) * 0.7 && (!panel || area(b) < area(panel))) panel = e;
		});
		var GROW = {x:UB.x - 26, y:UB.y - 26, w:UB.w + 52, h:UB.h + 52};
		/* the template's illustration (pattern, shapes, chart art) beside the text: kept together as one artwork */
		var ART = null, artSet = null;
		if (!im) {
			others.forEach(function(e){ if (e === panel || e === rule || e.t === 'line' || e.t === 'path') return; var b = ebox(e); if (b && area(b) >= SA * 0.002 && area(b) < SA * 0.6 && inter(b, GROW) <= area(b) * 0.1) ART = union(ART, b); });
			if (ART && area(ART) >= SA * 0.06 && !(ART.w > 1100 && ART.h > 600)) {
				artSet = new Set();
				others.forEach(function(e){ if (e === panel || e === rule) return; var b = ebox(e); if (b && area(b) < SA * 0.6 && inter(b, ART) >= area(b) * 0.85) artSet.add(e); });
			} else ART = null;
		}
		var zone = mode;
		if (!im && ART) { var acx = ART.x + ART.w / 2, ucx = UB.x + UB.w / 2, acy = ART.y + ART.h / 2, ucy = UB.y + UB.h / 2; zone = Math.abs(acx - ucx) > 140 ? (acx > ucx ? 'right' : 'left') : (acy > ucy ? 'bottom' : 'top'); }
		var M = z.port ? 56 : 64, tx = M, tw = W - 2 * M, ty0 = 0, ty1 = H, NB = null, al = title.align === 'c' ? 'c' : (title.align === 'r' ? 'r' : 'l');
		if (zone === 'bg') NB = {x:0, y:0, w:W, h:H};
		else if (zone === 'right' || zone === 'left') {
			if (z.port) { var ih = Math.round(H * 0.42); if (zone === 'right') { NB = {x:0, y:0, w:W, h:ih}; ty0 = ih; } else { NB = {x:0, y:H - ih, w:W, h:ih}; ty1 = H - ih; } }
			else { var iw = Math.round(W * 0.45); if (zone === 'right') { NB = {x:W - iw, y:0, w:iw, h:H}; tw = W - iw - M - 40; } else { NB = {x:0, y:0, w:iw, h:H}; tx = iw + 44; tw = W - iw - M - 44; } }
		}
		else if (zone === 'top') { NB = {x:0, y:0, w:W, h:Math.round(H * (z.port ? 0.4 : 0.42))}; ty0 = NB.h; }
		else if (zone === 'bottom') { var bh = Math.round(H * (z.port ? 0.38 : 0.36)); NB = {x:0, y:H - bh, w:W, h:bh}; ty1 = H - bh; }
		else if (zone === 'framed') {
			var iar = im.w / Math.max(1, im.h), right = im.x + im.w / 2 > 640;
			if (z.port) { var fw = Math.min(W - 2 * M, H * 0.3 * iar), fh = fw / iar; NB = {x:Math.round((W - fw) / 2), y:Math.round(H * 0.07), w:fw, h:fh}; ty0 = NB.y + fh + 10; }
			else { var fh2 = Math.min(H - 140, (W * 0.4) / iar), fw2 = fh2 * iar; NB = {x:right ? W - M - fw2 : M, y:Math.round((H - fh2) / 2), w:fw2, h:fh2}; if (right) tw = W - fw2 - 2 * M - 40; else { tx = M + fw2 + 40; tw = W - tx - M; } }
			al = z.port ? al : 'l';
		}
		if ((zone === 'right' || zone === 'left') && !z.port && al === 'c') al = 'l';
		var AB = (ART && NB) ? fitIn(ART, {x:NB.x + NB.w * 0.05, y:NB.y + NB.h * 0.06, w:NB.w * 0.9, h:NB.h * 0.88}, 1) : null;
		/* text block area (inside the panel when the template has one) */
		var pad = panel ? (z.port ? 34 : 30) : 0, X = tx + pad, Wt = tw - pad * 2, T0 = ty0 + pad, R = (ty1 - ty0) - pad * 2, pb = null;
		if (panel) {
			pb = {x:tx, y:ty0 + (ty1 - ty0) * 0.06, w:tw, h:(ty1 - ty0) * 0.88};
			if (zone === 'bg' || !zone) { pb.y = ty0 + (ty1 - ty0) * 0.12; pb.h = (ty1 - ty0) * 0.76; }
			T0 = pb.y + pad; R = pb.h - pad * 2;
		}
		var NT = {x:X - 14, y:T0 - 14, w:Wt + 28, h:R + 28};
		/* place the template's pieces again, in their own order: picture, its overlays, artwork, decorations */
		var IMB = im ? box(im) : null;
		added.forEach(function(e){
			if (!e || e.t === 'text' || e.t === 'logo' || e === panel || e === rule) return;
			if (e.t === 'img') { if (e !== im) return; e.x = NB.x; e.y = NB.y; e.w = NB.w; e.h = NB.h; C.els.push(e); return; }
			if (artSet && artSet.has(e)) { into(e, ART, AB); C.els.push(e); return; }
			var b = ebox(e);
			if (b && e.t !== 'line' && e.t !== 'path' && area(b) < SA * 0.15 && inter(b, GROW) >= area(b) * 0.9) return; /* bullets / icons / rules of the old text block */
			if (b && IMB && NB && e.t !== 'line' && e.t !== 'path' && area(b) < SA * 0.95 && inter(b, IMB) >= area(b) * 0.8) { into(e, IMB, NB); C.els.push(e); return; } /* overlays on the picture */
			remapEls([e], new Set(), W, H);
			var b2 = ebox(e);
			if (b2 && e.t !== 'line' && e.t !== 'path' && area(b2) < W * H * 0.45 && inter(b2, NT) > area(b2) * 0.25 && !(e.op != null && e.op < 0.3)) return; /* would sit on the text */
			C.els.push(e);
		});
		if (panel) { var pe = {}, k; for (k in panel) pe[k] = panel[k]; pe.x = pb.x; pe.y = pb.y; pe.w = pb.w; pe.h = pb.h; if (pe.pd) delete pe.pd; C.els.push(pe); }
		/* text block in the template's own styles */
		var Y = function(t){ return Math.round(T0 + R * t); };
		var sTitle = tstyle(title, {font:'head', color:tcol(P)}), f = C.cover, cs = kind === 'closing' ? D.contacts(C) : [];
		var cmp = fields['tf-company'];
		if (kind === 'cover' && f.company) { var sc = tstyle(cmp, {font:'head', color:P.accent}); C.text(X, Y(0.12), Wt, Math.max(22, (sc._size || 15) * 1.5), f.company, {font:sc.font, size:Math.min(sc._size || 15, 22), wt:sc.wt || 700, color:sc.color, caps:sc.caps, ls:sc.ls, italic:sc.italic, align:al, va:'m', lh:1.1, ed:{f:'tf-company'}, maxLines:1}); }
		var tTop = kind === 'cover' ? 0.2 : (cs.length ? 0.1 : 0.22), tBot = kind === 'cover' ? 0.52 : (cs.length ? 0.4 : 0.55);
		var tmax = wcap(C, C.s.title || (kind === 'closing' ? 'Thank You' : ''), Wt, Math.min(sTitle._size || 64, z.port ? 74 : 72));
		C.text(X, Y(tTop), Wt, Y(tBot) - Y(tTop), C.s.title || (kind === 'closing' ? 'Thank You' : ''), {font:sTitle.font, fit:{max:tmax, min:Math.min(24, tmax)}, color:sTitle.color, wt:sTitle.wt, caps:sTitle.caps, ls:sTitle.ls, italic:sTitle.italic, align:al, va:'b', lh:title.lh || 1.05, ed:{f:'title'}, maxLines:z.port ? 5 : 3});
		var ry = Y(tBot) + 14;
		if (rule) { var re = {}, k2; for (k2 in rule) re[k2] = rule[k2]; re.x = al === 'c' ? X + (Wt - rule.w) / 2 : (al === 'r' ? X + Wt - rule.w : X); re.y = ry; C.els.push(re); ry += rule.h + 16; } else ry += 6;
		var sSub = tstyle(fields.subtitle, {font:'body', color:P.text});
		if (C.s.subtitle) { var sh = Math.max(36, Y(kind === 'cover' ? 0.74 : (cs.length ? 0.56 : 0.72)) - ry); C.text(X, ry, Wt, sh, C.s.subtitle, {font:sSub.font, fit:{max:Math.min(sSub._size || 20, z.port ? 24 : 21), min:12}, color:sSub.color, wt:sSub.wt, caps:sSub.caps, ls:sSub.ls, italic:sSub.italic, align:al, lh:1.4, ed:{f:'subtitle'}, maxLines:z.port ? 5 : 3}); }
		if (kind === 'cover') {
			var y = Y(0.79), lines = [];
			if (f.presenter) lines.push({f:'tf-presenter', v:f.presenter + (f.designation ? ' · ' + f.designation : ''), e:fields['tf-presenter']});
			if (f.date) lines.push({f:'tf-date', v:f.date, e:fields['tf-date']});
			if (f.website) lines.push({f:'tf-website', v:f.website, e:fields['tf-website']});
			lines.forEach(function(l){
				if (y > T0 + R + (pad ? pad - 8 : 0) - 14) return;
				var s = tstyle(l.e, {font:'body', color:P.muted}), sz = Math.min(s._size || 14, z.port ? 17 : 15);
				C.text(X, y, Wt, sz * 1.5, l.v, {font:s.font, size:sz, wt:s.wt || 600, color:s.color, caps:s.caps, ls:s.ls, italic:s.italic, align:al, va:'m', lh:1.1, ed:{f:l.f}, maxLines:1});
				y += sz * 1.5 + 8;
			});
		} else if (cs.length) {
			var yc = Y(0.62), cw = Math.min(Wt, 520), cx = al === 'c' ? X + (Wt - cw) / 2 : (al === 'r' ? X + Wt - cw : X), gap = Math.min(76, Math.max(58, (Y(1) - yc) / cs.length));
			cs.forEach(function(c, i){
				var yy = yc + i * gap; if (yy + 56 > T0 + R + pad) return;
				var cc = cb(C, cx, yy, cw, 56, 12);
				D.mark(C, cx + 10, yy + 10, 36, c.icon, {});
				C.text(cx + 58, yy + 7, cw - 70, 14, c.label || '', {font:'body', size:10.5, wt:700, color:cc.tc === P.text ? P.accent : cc.mc, caps:true, ls:0.12, lh:1});
				C.text(cx + 58, yy + 23, cw - 70, 26, c.val, {font:'body', fit:{max:17, min:11}, wt:600, color:cc.tc, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
			});
		}
		return true;
	}
	function swapSide(l){ return String(l).replace(/\b(right|left)\b/gi, function(w){ var r = /^r/i.test(w) ? 'top' : 'bottom'; return w.charAt(0) === w.charAt(0).toUpperCase() ? r.charAt(0).toUpperCase() + r.slice(1) : r; }); }
	function derive(list, kind, port, gens, pre){
		var src = (list && list.length) ? list : [V('v1', kind === 'cover' ? 'Cover' : 'Closing', {})];
		return src.map(function(v){ var lb = port ? swapSide(v.lbl || v.id) : (v.lbl || v.id); return V((pre || '') + v.id, pre ? 'Template design: ' + lb : lb, {__ov:v}); }).concat(gens);
	}

	/* simple cover (Classic, and the "Simple" choices of every template): centred title with a short accent rule */
	function cover(C, v){
		if (C.o && C.o.pat != null && PATS[C.o.pat]) { runPat(C, C.o); return; }
		if (!cls && C.o && C.o.__ov) { try { if (adapt(C, C.o.__ov, 'cover', this)) return; } catch (e) { if (window.console) console.warn('[DS ratio] cover', T.id, e); } }
		var z = SZ(), W = z.W, H = z.H, P = C.P, f = C.cover, o = C.o || {}, m = o.img;
		var M = z.port ? 56 : 64, x = M, w = W - 2 * M, al = 'c', y0 = 0, y1 = H;
		if (m === 'bg') { D.imgBox(C, 0, 0, W, H, {}); C.rect(0, 0, W, H, {fill:P.bg, op:0.84}); }
		else if (m === 'right') { var iw = Math.round(W * 0.45); D.imgBox(C, W - iw, 0, iw, H, {}); x = 56; w = W - iw - 100; al = 'l'; }
		else if (m === 'top') { y0 = Math.round(H * 0.42); D.imgBox(C, 0, 0, W, y0, {}); }
		else if (m === 'bottom') { var bh = Math.round(H * (z.port ? 0.38 : 0.36)); D.imgBox(C, 0, H - bh, W, bh, {}); y1 = H - bh; }
		if (cls && (!m || m === 'bottom')) C.rect(0, 0, W, 6, {fill:P.accent});
		if (m === 'top') C.rect(0, y0, W, 6, {fill:P.accent});
		var R = y1 - y0, Y = function(t){ return Math.round(y0 + R * t); };
		chip(C, x, Y(0.15), al, w);
		var tm = wcap(C, C.s.title, w, m === 'right' ? 50 : (z.port ? 66 : 68));
		C.text(x, Y(0.23), w, Y(0.555) - Y(0.23), C.s.title, {font:'head', fit:{max:tm, min:Math.min(28, tm)}, color:tcol(P), align:al, ed:{f:'title'}, maxLines:z.port ? 5 : 3, va:'b', lh:1.05});
		C.rect(al === 'c' ? x + (w - 90) / 2 : x, Y(0.58), 90, 5, {fill:P.accent, r:3});
		if (C.s.subtitle) C.text(x, Y(0.615), w, Math.max(40, Y(0.77) - Y(0.615)), C.s.subtitle, {font:'body', fit:{max:z.port ? 24 : 20, min:12}, color:P.text, align:al, ed:{f:'subtitle'}, lh:1.42, maxLines:z.port ? 5 : 3});
		meta(C, x, Y(0.8), w, al);
		if (f.website && m !== 'bottom') {
			var ww = Math.min(w, f.website.length * 8 + 30), wx = al === 'c' ? x + (w - ww) / 2 : x, wy = Y(0.92);
			C.icon(wx, wy + 2, 16, 'mdi:web', P.accent);
			C.text(wx + 22, wy, ww - 22, 20, f.website, {font:'body', size:12.5, color:P.muted, lh:1, va:'m', ed:{f:'tf-website'}, maxLines:1});
		}
	}
	function closing(C, v){
		if (!cls && C.o && C.o.__ov) { try { if (adapt(C, C.o.__ov, 'closing', this)) return; } catch (e) { if (window.console) console.warn('[DS ratio] closing', T.id, e); } }
		var z = SZ(), W = z.W, H = z.H, P = C.P, o = C.o || {}, m = o.img;
		var M = z.port ? 56 : 64, x = M, w = W - 2 * M, al = 'c', y0 = 0, y1 = H;
		if (m === 'right') { var iw = Math.round(W * 0.45); D.imgBox(C, W - iw, 0, iw, H, {}); x = 56; w = W - iw - 100; al = 'l'; }
		else if (m === 'top') { y0 = Math.round(H * 0.4); D.imgBox(C, 0, 0, W, y0, {}); }
		else if (m === 'bg') { D.imgBox(C, 0, 0, W, H, {}); C.rect(0, 0, W, H, {fill:P.bg, op:0.86}); }
		if (cls && !m) C.rect(0, 0, W, 6, {fill:P.accent});
		if (m === 'top') C.rect(0, y0, W, 6, {fill:P.accent});
		var cs = D.contacts(C), R = y1 - y0, Y = function(t){ return Math.round(y0 + R * t); };
		var tTop = cs.length ? 0.16 : 0.26, tBot = cs.length ? 0.42 : 0.56;
		var tm = wcap(C, C.s.title || 'Thank You', w, al === 'c' ? (z.port ? 84 : 80) : 60);
		C.text(x, Y(tTop), w, Y(tBot) - Y(tTop), C.s.title || 'Thank You', {font:'head', fit:{max:tm, min:Math.min(32, tm)}, color:tcol(P), align:al, ed:{f:'title'}, maxLines:z.port ? 3 : 2, va:'b', lh:1.05});
		var ry = Y(tBot) + 18;
		C.rect(al === 'c' ? x + (w - 90) / 2 : x, ry, 90, 5, {fill:P.accent, r:3});
		var sh = Math.round(R * 0.12);
		if (C.s.subtitle) C.text(x, ry + 26, w, sh, C.s.subtitle, {font:'body', fit:{max:z.port ? 22 : 21, min:13}, color:P.text, align:al, ed:{f:'subtitle'}, lh:1.42, maxLines:3});
		if (!cs.length) return;
		var yc = ry + 26 + sh + 30;
		if (!z.port && al === 'c') { D.contactRow(C, x, yc, w, {cards:true}); return; }
		var cw = Math.min(w, 520), cx = al === 'c' ? x + (w - cw) / 2 : x;
		cs.forEach(function(c, k){
			var y = yc + k * 76;
			var cc = cb(C, cx, y, cw, 64, 12);
			D.mark(C, cx + 12, y + 12, 40, c.icon, {});
			C.text(cx + 64, y + 9, cw - 78, 16, c.label || '', {font:'body', size:11, wt:700, color:cc.tc === P.text ? P.accent : cc.mc, caps:true, ls:0.12, lh:1});
			C.text(cx + 64, y + 27, cw - 78, 28, c.val, {font:'body', fit:{max:18, min:11}, wt:600, color:cc.tc, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
		});
	}
	function quote(C, v){
		var z = SZ(), W = z.W, H = z.H, P = C.P, o = C.o || {}, by = C.it[0];
		if (o.img === 'right' && !z.port) { D.quoteSlide(C, {img:'right', align:'l', qc:P.accent, tc:P.title}); return; }
		var top = 0;
		if (o.img === 'top') { top = Math.round(H * 0.36); D.imgBox(C, 0, 0, W, top, {}); C.rect(0, top, W, 6, {fill:P.accent}); }
		else if (cls) C.rect(0, 0, W, 6, {fill:P.accent});
		var mx = z.port ? 48 : 80, cy0 = top + (z.port ? 56 : 70), ch = H - cy0 - (z.port ? 72 : 70), qo = z.port ? 150 : 120;
		var cc = cb(C, mx, cy0, W - mx * 2, ch, 24);
		C.text(mx, cy0 + 24, W - mx * 2, 100, '\u201C', {font:'head', size:z.port ? 120 : 100, wt:800, color:P.accent, align:'c', va:'t', lh:1, caps:false});
		C.text(mx + 40, cy0 + qo, W - mx * 2 - 80, Math.max(60, ch - qo - 110), C.s.title, {font:'head', fit:{max:wcap(C, C.s.title, W - mx * 2 - 80, z.port ? 44 : 36), min:16}, color:hc(cc, P), align:'c', lh:1.25, ed:{f:'title'}, maxLines:z.port ? 8 : 5, va:'m'});
		if (by) C.text(mx + 40, cy0 + ch - 90, W - mx * 2 - 80, 56, by.h ? by.h + ' \u2014 ' + by.d : by.d, {font:'body', size:z.port ? 18 : 16, wt:700, color:P.accent, align:'c', va:'m', lh:1.2, ed:{f:'content-plain', i:by.i}, maxLines:2});
	}

	var sCols = cls ? (cStats || statRows) : 'statCols', sCards = cls ? (cStatCards || statGrid) : 'statCards';
	var v43 = {
		cover:[V('v1', 'Text only', {}), V('v2', 'Full background image', {img:'bg'}), V('v3', 'Image right', {img:'right'}), V('v4', 'Image bottom', {img:'bottom'})],
		closing:[V('v1', 'Text only', {}), V('v2', 'Image right', {img:'right'}), V('v3', 'Full background image', {img:'bg'})],
		agenda:[V('v1', 'Numbered list', {fn:ag({})}), V('n2', 'Numbered cards', {fn:ag({cards:1})}), V('v2', 'List + image right', {fn:ag({img:'right', cols:1, imgPct:0.38})})],
		text:[V('v1', 'Statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right', imgPct:0.42}), V('v3', 'Statement + image bottom', {body:statement, img:'bottom'})],
		p2:[V('v1', '2 columns', {body:grid, cols:2}), V('n2', 'Stacked list', {body:list}), V('v2', 'Cards + image right', {body:grid, cols:1, img:'right'}), V('v3', 'Columns + image bottom', {body:grid, cols:2, img:'bottom'})],
		p3:[V('v1', '3 columns', {body:grid, cols:3}), V('n2', 'Stacked list', {body:list}), V('v2', 'List + image right', {body:list, img:'right'}), V('v3', 'Columns + image bottom', {body:grid, cols:3, img:'bottom'})],
		p4:[V('v1', '2 \u00D7 2 grid', {body:grid, cols:2}), V('n2', '4 columns', {body:grid, cols:4}), V('n3', 'Stacked list', {body:list}), V('v2', 'List + image right', {body:list, img:'right'})],
		p56:[V('v1', 'Card grid', {body:grid, cols:3}), V('n2', '2-column list', {body:list, cols:2}), V('v2', 'List + image right', {body:list, img:'right'})],
		p78:[V('v1', 'Card grid', {body:grid, cols:4}), V('n2', '2-column list', {body:list, cols:2}), V('n3', 'Card grid (2 across)', {body:grid, cols:2})],
		p9:[V('v1', 'Card grid', {body:grid, cols:4}), V('n2', '2-column list', {body:list, cols:2, max:16})],
		stats:[V('v1', 'Stats centred', {body:sCols}), V('n2', 'Stat cards', {body:sCards}), V('n3', 'Stacked stats', {body:statRows}), V('n4', 'Progress bars', {body:'statBars'}), V('v2', 'Stats + image bottom', {body:sCols, img:'bottom'})],
		timeline:[V('v1', 'Arrow steps', {body:'tlChev'}), V('n2', 'Milestone line', {body:'tlH'}), V('n3', 'Vertical timeline', {body:'tlV'}), V('v2', 'Vertical + image right', {body:'tlV', img:'right'})],
		quote:[V('v1', 'Centred quote', {}), V('v2', 'Quote + image right', {img:'right'})],
		chart:[V('v1', 'Chart + insights', {body:'chart'}), V('n2', 'Full-width chart', {body:'chart', full:true}), V('n3', 'Centred chart', {body:'chart', center:true, hk:'center'})]
	};
	var v916 = {
		cover:[V('v1', 'Text only', {}), V('v2', 'Full background image', {img:'bg'}), V('v3', 'Image top', {img:'top'}), V('v4', 'Image bottom', {img:'bottom'})],
		closing:[V('v1', 'Text only', {}), V('v2', 'Image top', {img:'top'}), V('v3', 'Full background image', {img:'bg'})],
		agenda:[V('v1', 'Numbered list', {fn:ag({cols:1})}), V('n2', 'Numbered cards', {fn:ag({cards:1})}), V('v2', 'Image top + list', {fn:ag({img:'top', cols:1})})],
		text:[V('v1', 'Statement', {body:statement}), V('v2', 'Image top + statement', {body:statement, img:'top'}), V('v3', 'Statement + image bottom', {body:statement, img:'bottom'})],
		p2:[V('v1', 'Stacked cards', {body:grid, cols:1}), V('n2', 'Stacked list', {body:list}), V('v2', 'Image top + cards', {body:grid, cols:1, img:'top'}), V('v3', 'Cards + image bottom', {body:grid, cols:1, img:'bottom'})],
		p3:[V('v1', 'Stacked cards', {body:grid, cols:1}), V('n2', 'Stacked list', {body:list}), V('v2', 'Image top + cards', {body:grid, cols:1, img:'top'}), V('v3', 'Cards + image bottom', {body:grid, cols:1, img:'bottom'})],
		p4:[V('v1', 'Stacked cards', {body:grid, cols:1}), V('n2', '2 \u00D7 2 grid', {body:grid, cols:2}), V('n3', 'Stacked list', {body:list}), V('v2', 'Image top + list', {body:list, img:'top'})],
		p56:[V('v1', '2-column grid', {body:grid, cols:2}), V('n2', 'Stacked list', {body:list}), V('v2', 'Image top + list', {body:list, img:'top'})],
		p78:[V('v1', '2-column grid', {body:grid, cols:2}), V('n2', 'Stacked list', {body:list})],
		p9:[V('v1', '2-column grid', {body:grid, cols:2}), V('n2', 'Stacked list', {body:list, max:16})],
		stats:[V('v1', 'Stacked stats', {body:statRows}), V('n2', 'Stat cards 2 \u00D7 2', {body:statGrid}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Image top + stats', {body:statRows, img:'top'})],
		timeline:[V('v1', 'Vertical timeline', {body:'tlV'}), V('n2', 'Numbered steps', {body:list, num:1}), V('v2', 'Image top + timeline', {body:'tlV', img:'top'})],
		quote:[V('v1', 'Centred quote', {}), V('v2', 'Image top + quote', {img:'top'})],
		chart:[V('v1', 'Full-width chart', {body:'chart', full:true}), V('n2', 'Centred chart', {body:'chart', center:true, hk:'center'})]
	};
	if (cls) { v43.cover = v43.cover.concat(patVariants(false)); v916.cover = v916.cover.concat(patVariants(false)); }
	if (!cls) { /* every template opens with its own cover pattern; the other patterns, its own cover designs (re-composed) and the simple ones are extra choices */
		v43.cover = patVariants(true).concat(derive(ov.cover, 'cover', false, [V('g1', 'Simple centred', {}), V('g2', 'Simple + image right', {img:'right'}), V('g3', 'Simple + background image', {img:'bg'})], 'o'));
		v916.cover = patVariants(true).concat(derive(ov.cover, 'cover', true, [V('g1', 'Simple centred', {}), V('g2', 'Simple + image top', {img:'top'}), V('g3', 'Simple + background image', {img:'bg'})], 'o'));
		v43.closing = derive(ov.closing, 'closing', false, [V('g1', 'Simple centred', {}), V('g2', 'Simple + image right', {img:'right'})]);
		v916.closing = derive(ov.closing, 'closing', true, [V('g1', 'Simple centred', {}), V('g2', 'Simple + image top', {img:'top'})]);
	}
	/* 16:9: the template keeps its own cover designs (its default stays first); the flyer patterns are added after them */
	var v169 = {}, c169 = null, s169 = null, pat169 = patVariants(false);
	Object.defineProperty(v169, 'cover', {enumerable:true, get:function(){
		var base = Array.isArray(ov.cover) && ov.cover.length ? ov.cover : null;
		if (!c169 || s169 !== base) { s169 = base; c169 = (base || [V('v1', 'Template cover', {})]).concat(pat169); }
		return c169;
	}});
	return {bg:bg, cover:cover, closing:closing, quote:quote, v:{'4:3':v43, '9:16':v916, '16:9':v169}};
}

/* every template keeps its 16:9 look; in a 4:3 or 9:16 deck its pieces switch to the layouts above */
function patchTpl(id){
	var T = D.reg[id]; if (!T || T.__ratioDone) return;
	T.__ratioDone = 1;
	var ov = T.variants || {}, L = null;
	var o0 = {bg:T.bg, cover:T.cover, closing:T.closing, quote:T.quote, decor:T.decor};
	try { L = classicLayouts(T, ov, o0); } catch (e) { if (window.console) console.warn('[DS ratio]', e); }
	if (!L) return; /* the engine changed shape: this template stays 16:9 only */
	T.bg = function(C, kind){ if (mode()) return L.bg.apply(this, arguments); if (o0.bg) return o0.bg.apply(this, arguments); };
	/* decoration: pieces drawn at fixed spots are moved to the new shape; effects that follow the slide's own elements (shadows, pop titles) run as they are */
	var relDecor = o0.decor && /offsetShadow|popTitle|C\.els\b/.test(String(o0.decor));
	if (o0.decor) T.decor = function(C, kind){ var self = this, a = arguments; if (mode() && !relDecor) return remapRun(C, function(){ return o0.decor.apply(self, a); }); return o0.decor.apply(this, arguments); };
	T.cover = function(C, v){ return (mode() || (C && C.o && C.o.pat != null)) ? L.cover.call(this, C, v) : (o0.cover || D.genericCover).apply(this, arguments); };
	T.closing = function(C, v){ return mode() ? L.closing.call(this, C, v) : (o0.closing || D.genericClosing).apply(this, arguments); };
	T.quote = function(C, v){ if (mode()) return L.quote(C, v); if (o0.quote) return o0.quote.apply(this, arguments); D.quoteSlide(C, {}); };
	var nv = {}, keys = {};
	[ov, L.v['4:3'], L.v['9:16'], L.v['16:9']].forEach(function(o){ for (var k in o) keys[k] = 1; });
	Object.keys(keys).forEach(function(k){
		Object.defineProperty(nv, k, {enumerable:true, configurable:true,
			get:function(){ var m = mode() || '16:9', src = L.v[m]; return (src && src[k]) || ov[k]; },
			set:function(x){ ov[k] = x; }});
	});
	T.variants = nv;
	T.__ratioOn = 1;
}

/* ---------- keep everything in step ---------- */
var lastHome = null;
function tick(){
	Object.keys(TPLS).forEach(function(id){ if (D.reg[id] && !D.reg[id].__ratioDone) patchTpl(id); });
	syncLayout();
	var r = openRatio();
	if (r !== amb) { amb = r; use(amb); }
	var b = document.body;
	if (b) {
		var on = amb !== '16:9';
		if (b.classList.contains('dsr-on') !== on) b.classList.toggle('dsr-on', on);
		b.classList.toggle('dsr-43', amb === '4:3'); b.classList.toggle('dsr-916', amb === '9:16');
		if (on) b.style.setProperty('--dsr-ar', SIZES[amb][0] + '/' + SIZES[amb][1]); else b.style.removeProperty('--dsr-ar');
		var hr = homeR();
		if (b.classList.contains('dsr-h43') !== (hr === '4:3')) b.classList.toggle('dsr-h43', hr === '4:3');
		if (b.classList.contains('dsr-h916') !== (hr === '9:16')) b.classList.toggle('dsr-h916', hr === '9:16');
		if (lastHome !== null && hr !== lastHome) { /* ratio changed on the home screen: redraw the previews */
			var ag = document.getElementById('dssl-arch-g'); if (ag) ag.innerHTML = '';
			if (typeof D.onRegister === 'function') { try { D.onRegister({id:'__dsr_refresh'}); } catch (e) {} }
		}
		lastHome = hr;
	}
	wrapLogo();
	wrapNew();
	fitMain();
	/* editor open on a Classic deck: the hidden ratio field follows the deck (saves / downloads use it) */
	var d = openDeck(), id = D.activeId ? D.activeId() : null, lay = document.getElementById('dsp-layout');
	if (d && ok(id) && lay && editorOpen() && lay.value !== amb) { lay.value = amb; var ll = document.getElementById('dsp-pill-layout-lbl'); if (ll) ll.textContent = amb; }
	/* home: Classic picked -> the picker is open with 4:3 / 9:16 */
	var pk = D.pick, pid = (pk && pk !== 'classic' && D.reg[pk]) ? pk : null, pl = document.getElementById('dsp-pill-layout');
	if (pl) {
		if (ok(pid) && pl.style.pointerEvents === 'none') { pl.style.opacity = ''; pl.style.pointerEvents = ''; pl.title = ''; }
		D.ratioMenu(pid);
	}
}
tick();
setInterval(tick, 300);
if (typeof D.refreshEditor === 'function') setTimeout(function(){ if (openRatio() !== '16:9') { try { D.refreshEditor(); } catch (e) {} } }, 50);
})();
</script>
	<?php
}, 2003);

} // DS_TPL_RATIO
