<?php
/**
 * Code Snippets export — DS Vector Export — EPS + SVG download (editable vector, live text)
 *
 * Snippet ID : 1191
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, vector-export
 * Modified   : 2026-10-23
 * Load order : 353 of 353
 *
 * V42: also offered on the AI Document page ([ds_ai_document]).
 * V40: not offered inside the PowerPoint add-in (side panel or PowerPoint window); website only.
 */
/**
 * DS Vector Export — EPS + SVG download (editable vector, live text)
 * Run: front end
 *
 * Adds "EPS + SVG (vector)" to the editor's Download menu (the menu made by "DS PPT Export").
 * Every slide is turned into real vector files, exactly as the editor shows it:
 *   SVG – every element is its own named group (Title, Subtitle, Image 1, Ellipses (98) …); live text with the
 *         template's fonts; gradients, transparency, rounded corners, clip shapes, icons and inline drawings stay vector.
 *   EPS – the same drawing for Illustrator / print, with live text. EPS cannot do transparency, so soft shadows, blurs
 *         and see-through areas over pictures are kept as small pictures in place; everything else stays vector.
 * Download = one .zip: SVG/… one file per slide, EPS/… one file per slide, and "Fonts used.txt".
 * Works in the browser from the slides as shown (any template, any ratio 16:9 / 4:3 / 9:16).
 * Free presentations: locked unless the free plan allows it (DSFP.canExport('vector')).
 *
 * NEW SNIPPET — edits no other snippet. Deactivate it to remove the menu entry.
 */
if ( ! defined('ABSPATH') ) exit;

/* pictures kept on another storage host cannot be read by the browser (no CORS): the exporter asks this site for them.
   Signed-in users only, pictures only, and only from this site, its own domain, Cloudflare R2 or the Iconify icon service. */
add_action('rest_api_init', function () {
	register_rest_route('ds/v1', '/vx-img', array(
		'methods'             => 'GET',
		'permission_callback' => function () { return is_user_logged_in(); },
		'callback'            => 'ds_vx_img_proxy',
	));
});
if ( ! function_exists('ds_vx_img_proxy') ) {
	function ds_vx_img_proxy( WP_REST_Request $req ) {
		$u = esc_url_raw( (string) $req->get_param('u') );
		$p = $u ? wp_parse_url( $u ) : false;
		if ( ! $p || empty($p['host']) || ! in_array( strtolower( isset($p['scheme']) ? $p['scheme'] : '' ), array('http', 'https'), true ) ) return new WP_Error('ds_vx_bad', 'Bad picture address.', array('status' => 400));
		$host = strtolower($p['host']);
		$site = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		$root = implode('.', array_slice( explode('.', $site), -2 ));
		$ok = $host === $site || $host === $root || ( $root && substr($host, -strlen('.' . $root)) === '.' . $root )
			|| preg_match('/(\.r2\.dev|\.r2\.cloudflarestorage\.com)$/', $host) || $host === 'api.iconify.design';
		if ( ! apply_filters('ds_vx_img_host_ok', $ok, $host, $u) ) return new WP_Error('ds_vx_host', 'This picture address is not allowed.', array('status' => 403));
		$r = wp_safe_remote_get( $u, array('timeout' => 20, 'redirection' => 5, 'limit_response_size' => 15 * 1024 * 1024) );
		if ( is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200 ) return new WP_Error('ds_vx_fetch', 'The picture could not be loaded.', array('status' => 502));
		$type = strtolower( (string) wp_remote_retrieve_header($r, 'content-type') );
		if ( strpos($type, 'image/') !== 0 ) return new WP_Error('ds_vx_type', 'Not a picture.', array('status' => 415));
		$body = (string) wp_remote_retrieve_body($r);
		nocache_headers();
		header('Content-Type: ' . preg_replace('/[^\w\/+.\-]/', '', strtok($type, ';')));
		header('Content-Length: ' . strlen($body));
		header('X-Content-Type-Options: nosniff');
		header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox");
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- binary picture data
		exit;
	}
}

add_action('wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a($post, 'WP_Post') ) return;
	if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') && ! has_shortcode($post->post_content, 'ds_ai_document') ) return;
	?>
<style id="ds-vector-export-css">
#dsx-menu .dsx-vec{background:#6d28d9;flex-direction:column;gap:1px;font-size:9.5px;line-height:1.05;}
#dsvx-prog{position:fixed;inset:0;z-index:2147483000;background:rgba(6,9,16,.86);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
#dsvx-prog .dsvx-card{width:min(92vw,400px);background:#141a26;border:1px solid #2a3550;border-radius:16px;padding:28px 28px 24px;text-align:center;color:#e6ecf7;}
#dsvx-prog .dsvx-sp{width:38px;height:38px;border:3px solid #2a3550;border-top-color:#a78bfa;border-radius:50%;margin:0 auto 16px;animation:dsvxspin .8s linear infinite;}
#dsvx-prog .dsvx-m{font-size:15px;font-weight:700;}
#dsvx-prog .dsvx-s{font-size:12.5px;color:#8b97ad;margin-top:5px;}
#dsvx-prog .dsvx-bar{height:6px;border-radius:99px;background:#1e2740;margin-top:18px;overflow:hidden;}
#dsvx-prog .dsvx-bar i{display:block;height:100%;width:0;background:#8b5cf6;border-radius:99px;transition:width .25s;}
@keyframes dsvxspin{to{transform:rotate(360deg);}}
</style>
<script id="ds-vector-export-cfg">window.DSVX_CFG = {img: <?php echo wp_json_encode( rest_url('ds/v1/vx-img') ); ?>, nonce: <?php echo wp_json_encode( wp_create_nonce('wp_rest') ); ?>};</script>
<script id="ds-vector-export-js">
(function(){
if (window.DSVX && window.DSVX.version) return;
/* ===== DSVX part A: numbers, colours, matrices, paths ===== */
var K = 0.5522847498;
function num(v){ v = +v; if (!isFinite(v)) v = 0; v = Math.round(v * 100) / 100; return v === 0 ? 0 : v; }
function clamp(v, a, b){ return Math.max(a, Math.min(b, v)); }
var _cc = null;
function col(c){ /* any css colour -> {r,g,b,a} (0..255, 0..1) or null */
	if (c == null || c === '' || c === 'none') return null;
	if (typeof c === 'object' && c.r != null) return c;
	c = String(c).trim(); if (c === 'transparent') return {r:0, g:0, b:0, a:0};
	var m = /^#([0-9a-f]{3,8})$/i.exec(c);
	if (m) { var h = m[1]; if (h.length < 6) h = h.split('').map(function(x){ return x + x; }).join('');
		return {r:parseInt(h.slice(0, 2), 16), g:parseInt(h.slice(2, 4), 16), b:parseInt(h.slice(4, 6), 16), a:h.length === 8 ? parseInt(h.slice(6, 8), 16) / 255 : 1}; }
	m = /^rgba?\(([^)]+)\)$/i.exec(c);
	if (m) { var pa = m[1].split(/[\s,\/]+/).filter(Boolean), cv = function(v){ return /%$/.test(v) ? parseFloat(v) * 2.55 : parseFloat(v); }, al = pa[3];
		return {r:cv(pa[0]) || 0, g:cv(pa[1]) || 0, b:cv(pa[2]) || 0, a:al == null ? 1 : (/%$/.test(al) ? parseFloat(al) / 100 : parseFloat(al))}; }
	try { _cc = _cc || document.createElement('canvas').getContext('2d'); _cc.fillStyle = '#010203'; _cc.fillStyle = c; var s = _cc.fillStyle; if (s && s !== '#010203' && s !== c) return col(s); } catch (e) {}
	return null;
}
function withA(c, a){ c = col(c); return c ? {r:c.r, g:c.g, b:c.b, a:(c.a == null ? 1 : c.a) * a} : null; }
function hex(c){ c = col(c); if (!c) return 'none'; function h(v){ v = clamp(Math.round(v), 0, 255).toString(16); return v.length < 2 ? '0' + v : v; } return '#' + h(c.r) + h(c.g) + h(c.b); }
function rgbaS(c, op){ c = col(c); if (!c) return 'rgba(0,0,0,0)'; return 'rgba(' + Math.round(c.r) + ',' + Math.round(c.g) + ',' + Math.round(c.b) + ',' + num((c.a == null ? 1 : c.a) * (op == null ? 1 : op) * 1000) / 1000 + ')'; }
function over(top, under){ /* top (with alpha) composited over an opaque colour */
	top = col(top); under = col(under) || {r:255, g:255, b:255, a:1}; var a = top ? top.a : 0;
	return {r:under.r + (top ? top.r - under.r : 0) * a, g:under.g + (top ? top.g - under.g : 0) * a, b:under.b + (top ? top.b - under.b : 0) * a, a:1};
}
/* 2-D affine matrices {a,b,c,d,e,f} */
var IM = {a:1, b:0, c:0, d:1, e:0, f:0};
function mm(A, B){ return {a:A.a * B.a + A.c * B.b, b:A.b * B.a + A.d * B.b, c:A.a * B.c + A.c * B.d, d:A.b * B.c + A.d * B.d, e:A.a * B.e + A.c * B.f + A.e, f:A.b * B.e + A.d * B.f + A.f}; }
function mT(x, y){ return {a:1, b:0, c:0, d:1, e:x, f:y}; }
function isI(m){ return !m || (Math.abs(m.a - 1) < 1e-6 && Math.abs(m.b) < 1e-6 && Math.abs(m.c) < 1e-6 && Math.abs(m.d - 1) < 1e-6 && Math.abs(m.e) < 1e-4 && Math.abs(m.f) < 1e-4); }
function tp(m, x, y){ return [m.a * x + m.c * y + m.e, m.b * x + m.d * y + m.f]; }
function mdet(m){ return Math.sqrt(Math.abs(m.a * m.d - m.b * m.c)) || 1; }
function mInv(m){ var d = m.a * m.d - m.b * m.c || 1e-9; return {a:m.d / d, b:-m.b / d, c:-m.c / d, d:m.a / d, e:(m.c * m.f - m.d * m.e) / d, f:(m.b * m.e - m.a * m.f) / d}; }
function mStr(m){ return [m.a, m.b, m.c, m.d, m.e, m.f].map(function(v){ return Math.round(v * 10000) / 10000; }).join(' '); }

/* paths: [['M',x,y],['L',x,y],['C',x1,y1,x2,y2,x,y],['Z']] */
function rrP(x, y, w, h, R){ /* R: [[rx,ry] tl, tr, br, bl] */
	if (w <= 0 || h <= 0) return null;
	R = (R || [[0, 0], [0, 0], [0, 0], [0, 0]]).map(function(r){ return [Math.max(0, r[0] || 0), Math.max(0, r[1] || 0)]; });
	var f = Math.min(1, w / ((R[0][0] + R[1][0]) || 1e-9), w / ((R[3][0] + R[2][0]) || 1e-9), h / ((R[0][1] + R[3][1]) || 1e-9), h / ((R[1][1] + R[2][1]) || 1e-9));
	if (f < 1) R = R.map(function(r){ return [r[0] * f, r[1] * f]; });
	var tl = R[0], tr = R[1], br = R[2], bl = R[3], q = 1 - K, p = [['M', x + tl[0], y], ['L', x + w - tr[0], y]];
	if (tr[0] || tr[1]) p.push(['C', x + w - tr[0] * q, y, x + w, y + tr[1] * q, x + w, y + tr[1]]);
	p.push(['L', x + w, y + h - br[1]]);
	if (br[0] || br[1]) p.push(['C', x + w, y + h - br[1] * q, x + w - br[0] * q, y + h, x + w - br[0], y + h]);
	p.push(['L', x + bl[0], y + h]);
	if (bl[0] || bl[1]) p.push(['C', x + bl[0] * q, y + h, x, y + h - bl[1] * q, x, y + h - bl[1]]);
	p.push(['L', x, y + tl[1]]);
	if (tl[0] || tl[1]) p.push(['C', x, y + tl[1] * q, x + tl[0] * q, y, x + tl[0], y]);
	p.push(['Z']);
	return p;
}
function rectP(x, y, w, h){ return [['M', x, y], ['L', x + w, y], ['L', x + w, y + h], ['L', x, y + h], ['Z']]; }
function ellP(cx, cy, rx, ry){ var ox = rx * K, oy = ry * K; return [['M', cx + rx, cy], ['C', cx + rx, cy + oy, cx + ox, cy + ry, cx, cy + ry], ['C', cx - ox, cy + ry, cx - rx, cy + oy, cx - rx, cy], ['C', cx - rx, cy - oy, cx - ox, cy - ry, cx, cy - ry], ['C', cx + ox, cy - ry, cx + rx, cy - oy, cx + rx, cy], ['Z']]; }
function polyP(pts, close){ var p = pts.map(function(q, i){ return [i ? 'L' : 'M', q[0], q[1]]; }); if (close !== false) p.push(['Z']); return p; }
function mapP(path, m){ if (!path || isI(m)) return path; return path.map(function(s){ if (s[0] === 'Z') return s; var o = [s[0]]; for (var i = 1; i + 1 < s.length; i += 2) { var q = tp(m, s[i], s[i + 1]); o.push(q[0], q[1]); } return o; }); }
function pathBox(path){ var x0 = 1e9, y0 = 1e9, x1 = -1e9, y1 = -1e9; (path || []).forEach(function(s){ for (var i = 1; i + 1 < s.length; i += 2) { x0 = Math.min(x0, s[i]); x1 = Math.max(x1, s[i]); y0 = Math.min(y0, s[i + 1]); y1 = Math.max(y1, s[i + 1]); } }); return x1 < x0 ? null : {x:x0, y:y0, w:x1 - x0, h:y1 - y0}; }
function boxHit(a, b){ return a && b && a.x < b.x + b.w && b.x < a.x + a.w && a.y < b.y + b.h && b.y < a.y + a.h; }
function dStr(path){ return (path || []).map(function(s){ return s[0] === 'Z' ? 'Z' : s[0] + s.slice(1).map(num).join(' '); }).join(''); }

/* SVG path data (any commands, relative / absolute, arcs) -> absolute M/L/C/Z */
function parseD(d){
	var toks = String(d || '').match(/[MmLlHhVvCcSsQqTtAaZz]|[-+]?(?:\d*\.\d+|\d+\.?)(?:[eE][-+]?\d+)?/g) || [], i = 0, out = [], cmd = '', x = 0, y = 0, sx = 0, sy = 0, lc = null, lq = null;
	function n(){ return parseFloat(toks[i++]); }
	function isN(){ return i < toks.length && !/^[A-Za-z]$/.test(toks[i]); }
	var guard = 0;
	while (i < toks.length && guard++ < 200000) {
		if (/^[A-Za-z]$/.test(toks[i])) cmd = toks[i++];
		var rel = cmd === cmd.toLowerCase(), C = cmd.toUpperCase(), ox = rel ? x : 0, oy = rel ? y : 0;
		if (C === 'Z') { out.push(['Z']); x = sx; y = sy; lc = lq = null; if (isN()) cmd = rel ? 'l' : 'L'; continue; }
		if (!isN()) { if (i < toks.length && !/^[A-Za-z]$/.test(toks[i])) i++; else if (i < toks.length && toks[i] === cmd) i++; continue; }
		if (C === 'M') { x = ox + n(); y = oy + n(); sx = x; sy = y; out.push(['M', x, y]); cmd = rel ? 'l' : 'L'; lc = lq = null; continue; }
		if (C === 'L') { x = ox + n(); y = oy + n(); out.push(['L', x, y]); lc = lq = null; continue; }
		if (C === 'H') { x = ox + n(); out.push(['L', x, y]); lc = lq = null; continue; }
		if (C === 'V') { y = oy + n(); out.push(['L', x, y]); lc = lq = null; continue; }
		if (C === 'C') { var a1 = ox + n(), b1 = oy + n(), a2 = ox + n(), b2 = oy + n(); x = ox + n(); y = oy + n(); out.push(['C', a1, b1, a2, b2, x, y]); lc = [a2, b2]; lq = null; continue; }
		if (C === 'S') { var r1 = lc ? [2 * x - lc[0], 2 * y - lc[1]] : [x, y], c2x = ox + n(), c2y = oy + n(), nx = ox + n(), ny = oy + n(); out.push(['C', r1[0], r1[1], c2x, c2y, nx, ny]); lc = [c2x, c2y]; x = nx; y = ny; lq = null; continue; }
		if (C === 'Q' || C === 'T') { var qx, qy; if (C === 'Q') { qx = ox + n(); qy = oy + n(); } else { qx = lq ? 2 * x - lq[0] : x; qy = lq ? 2 * y - lq[1] : y; }
			var ex = ox + n(), ey = oy + n(); out.push(['C', x + 2 / 3 * (qx - x), y + 2 / 3 * (qy - y), ex + 2 / 3 * (qx - ex), ey + 2 / 3 * (qy - ey), ex, ey]); lq = [qx, qy]; lc = null; x = ex; y = ey; continue; }
		if (C === 'A') { var rx = Math.abs(n()), ry = Math.abs(n()), rot = n(), la = n(), sw = n(), ax = ox + n(), ay = oy + n(); arcToC(out, x, y, rx, ry, rot, la, sw, ax, ay); x = ax; y = ay; lc = lq = null; continue; }
		i++;
	}
	return out;
}
function arcToC(out, x1, y1, rx, ry, phi, fa, fs, x2, y2){
	if (!rx || !ry || (x1 === x2 && y1 === y2)) { out.push(['L', x2, y2]); return; }
	var p = phi * Math.PI / 180, cp = Math.cos(p), sp = Math.sin(p);
	var dx = (x1 - x2) / 2, dy = (y1 - y2) / 2, x1p = cp * dx + sp * dy, y1p = -sp * dx + cp * dy;
	var l = x1p * x1p / (rx * rx) + y1p * y1p / (ry * ry); if (l > 1) { rx *= Math.sqrt(l); ry *= Math.sqrt(l); }
	var s = (fa == fs ? -1 : 1) * Math.sqrt(Math.max(0, (rx * rx * ry * ry - rx * rx * y1p * y1p - ry * ry * x1p * x1p) / (rx * rx * y1p * y1p + ry * ry * x1p * x1p)));
	var cxp = s * rx * y1p / ry, cyp = -s * ry * x1p / rx, cx = cp * cxp - sp * cyp + (x1 + x2) / 2, cy = sp * cxp + cp * cyp + (y1 + y2) / 2;
	function ang(ux, uy, vx, vy){ return Math.atan2(ux * vy - uy * vx, ux * vx + uy * vy); }
	var t1 = ang(1, 0, (x1p - cxp) / rx, (y1p - cyp) / ry), dt = ang((x1p - cxp) / rx, (y1p - cyp) / ry, (-x1p - cxp) / rx, (-y1p - cyp) / ry);
	if (!+fs && dt > 0) dt -= 2 * Math.PI; else if (+fs && dt < 0) dt += 2 * Math.PI;
	var segs = Math.max(1, Math.ceil(Math.abs(dt) / (Math.PI / 2))), d = dt / segs, k = 4 / 3 * Math.tan(d / 4);
	function pt(t){ var ct = Math.cos(t), st = Math.sin(t); return [cx + rx * ct * cp - ry * st * sp, cy + rx * ct * sp + ry * st * cp]; }
	function dv(t){ var ct = Math.cos(t), st = Math.sin(t); return [-rx * st * cp - ry * ct * sp, -rx * st * sp + ry * ct * cp]; }
	for (var i = 0; i < segs; i++) { var a = t1 + i * d, b = a + d, P0 = pt(a), P3 = pt(b), D0 = dv(a), D3 = dv(b);
		out.push(['C', P0[0] + k * D0[0], P0[1] + k * D0[1], P3[0] - k * D3[0], P3[1] - k * D3[1], P3[0], P3[1]]); }
}

/* ---------- CSS value parsing ---------- */
function splitTop(s, sep){ var out = [], depth = 0, cur = ''; s = String(s || ''); sep = sep || ',';
	for (var i = 0; i < s.length; i++) { var ch = s[i]; if (ch === '(') depth++; else if (ch === ')') depth--;
		if (depth === 0 && (sep === ' ' ? /\s/.test(ch) : ch === sep)) { if (cur.trim()) out.push(cur.trim()); cur = ''; } else cur += ch; }
	if (cur.trim()) out.push(cur.trim()); return out; }
function len(v, ref){ v = String(v == null ? '' : v).trim(); if (!v || v === 'auto') return null; if (/%$/.test(v)) return parseFloat(v) / 100 * (ref || 0); var n = parseFloat(v); return isFinite(n) ? n : null; }
function pos1(v, ref){ v = String(v || '').trim(); var kw = {left:0, top:0, center:0.5, right:1, bottom:1}; if (v in kw) return kw[v] * ref; var n = len(v, ref); return n == null ? ref / 2 : n; }
var COLRE = /^(rgba?\(|hsla?\(|#|color\(|transparent\b|oklch\(|oklab\(|lab\(|lch\(|hwb\(|currentcolor\b|[a-z]+$)/i;
function stopList(args, L){ /* css stops -> [{o,c}] with o in 0..1 along a ray of length L (css px) */
	var raw = [];
	args.forEach(function(a){
		var parts = splitTop(a, ' '), c = null, ps = [];
		parts.forEach(function(p){ if (c == null && COLRE.test(p) && !/^(px|%)$/.test(p) && col(p)) c = col(p); else ps.push(p); });
		if (!c) return; /* a colour hint: ignored */
		if (!ps.length) raw.push({o:null, c:c});
		ps.forEach(function(p){ var v = /%$/.test(p) ? parseFloat(p) / 100 : (parseFloat(p) / (L || 1)); raw.push({o:isFinite(v) ? v : null, c:c}); });
	});
	if (!raw.length) return [];
	if (raw[0].o == null) raw[0].o = 0; if (raw[raw.length - 1].o == null) raw[raw.length - 1].o = Math.max(1, raw.reduce(function(m, s){ return s.o != null ? Math.max(m, s.o) : m; }, 0));
	for (var i = 1; i < raw.length; i++) if (raw[i].o != null && raw[i].o < raw[i - 1].o) raw[i].o = raw[i - 1].o;
	for (var j = 1; j < raw.length; j++) if (raw[j].o == null) { var k = j; while (raw[k].o == null) k++; var a0 = raw[j - 1].o, a1 = raw[k].o; for (var q = j; q < k; q++) raw[q].o = a0 + (a1 - a0) * (q - j + 1) / (k - j + 1); }
	return raw;
}
function fixTransparent(st){ /* transparent stops take their neighbours' colour (css mixes premultiplied) */
	var out = [];
	st.forEach(function(s, i){
		if (s.c.a > 0.001) { out.push(s); return; }
		var L = null, R = null, j;
		for (j = i - 1; j >= 0; j--) if (st[j].c.a > 0.001) { L = st[j].c; break; }
		for (j = i + 1; j < st.length; j++) if (st[j].c.a > 0.001) { R = st[j].c; break; }
		if (!L && !R) { out.push(s); return; }
		if (L) out.push({o:s.o, c:{r:L.r, g:L.g, b:L.b, a:0}});
		if (R && (!L || R.r !== L.r || R.g !== L.g || R.b !== L.b)) out.push({o:s.o, c:{r:R.r, g:R.g, b:R.b, a:0}});
	});
	return out;
}
function lerpC(a, b, t){ return {r:a.r + (b.r - a.r) * t, g:a.g + (b.g - a.g) * t, b:a.b + (b.b - a.b) * t, a:a.a + (b.a - a.a) * t}; }
function normStops(st){ /* clamp to 0..1; the colour just after 0 and just before 1 (hard stops stay hard) */
	st = st.slice(); if (!st.length) return st;
	if (st.length === 1) return [{o:0, c:st[0].c}, {o:1, c:st[0].c}];
	function seg(o, fromRight){
		var i;
		if (fromRight) { for (i = st.length - 1; i >= 0; i--) if (st[i].o <= o) break; if (i < 0) return st[0].c; if (i === st.length - 1) return st[i].c; }
		else { for (i = 0; i < st.length; i++) if (st[i].o >= o) break; if (i >= st.length) return st[st.length - 1].c; if (i === 0) return st[0].c; i--; }
		var a = st[i], b = st[i + 1], t = b.o > a.o ? (o - a.o) / (b.o - a.o) : 1; return lerpC(a.c, b.c, clamp(t, 0, 1));
	}
	var out = [{o:0, c:seg(0, true)}];
	st.forEach(function(s){ if (s.o > 0 && s.o < 1) out.push({o:s.o, c:s.c}); });
	out.push({o:1, c:seg(1, false)});
	return out;
}
function repeatStops(st){ /* repeating-* gradient: copy the period across 0..1 (max 160 periods) */
	var a = st[0].o, b = st[st.length - 1].o, p = b - a; if (!(p > 0.002)) return st;
	var out = [], k0 = Math.floor(-a / p) - 1;
	for (var k = k0; k < k0 + 160; k++) { var off = k * p; if (a + off > 1) break; st.forEach(function(s){ out.push({o:s.o + off, c:s.c}); }); }
	return out;
}
/* one css gradient -> paint {grad} over the box (x,y,w,h in slide units, s = slide units per css px) */
function cssGrad(str, x, y, w, h, s){
	var m = /^(repeating-)?(linear|radial|conic)-gradient\(([\s\S]*)\)$/i.exec(String(str).trim()); if (!m) return null;
	var rep = !!m[1], type = m[2].toLowerCase(), args = splitTop(m[3]), pre = null;
	if (args.length && !COLRE.test(args[0].split(/\s+/)[0]) || (args.length && /^(to|at|from|circle|ellipse|closest|farthest)\b/i.test(args[0]))) pre = args.shift();
	var g;
	if (type === 'linear') {
		var ang = Math.PI; /* 180deg */
		if (pre) {
			var am = /(-?[\d.]+)(deg|turn|rad|grad)/.exec(pre);
			if (am) { var v = parseFloat(am[1]); ang = am[2] === 'deg' ? v * Math.PI / 180 : am[2] === 'turn' ? v * 2 * Math.PI : am[2] === 'grad' ? v * Math.PI / 200 : v; }
			else if (/^to\s/.test(pre)) { var t = pre.slice(3); var dx = /right/.test(t) ? 1 : /left/.test(t) ? -1 : 0, dy = /bottom/.test(t) ? 1 : /top/.test(t) ? -1 : 0;
				if (dx && dy) ang = Math.atan2(dx * h, -dy * w); else ang = Math.atan2(dx, -dy); }
		}
		var sn = Math.sin(ang), cs = -Math.cos(ang), Ls = Math.abs(w * sn) + Math.abs(h * Math.cos(ang)), cx = x + w / 2, cy = y + h / 2;
		var st = stopList(args, Ls / s); if (!st.length) return null; if (rep) st = repeatStops(st);
		g = {type:'linear', x1:cx - sn * Ls / 2, y1:cy - cs * Ls / 2, x2:cx + sn * Ls / 2, y2:cy + cs * Ls / 2, stops:normStops(fixTransparent(st))};
	} else if (type === 'radial') {
		var shape = 'ellipse', size = 'farthest-corner', px = w / 2, py = h / 2, sz = null;
		if (pre) {
			var at = pre.split(/\bat\b/), lead = (at[0] || '').trim().split(/\s+/).filter(Boolean);
			if (at[1]) { var pp = at[1].trim().split(/\s+/); if (pp.length === 1) pp.push(/^(top|bottom)$/.test(pp[0]) ? 'center' : 'center');
				if (/^(top|bottom)$/.test(pp[0]) || /^(left|right)$/.test(pp[1])) pp = [pp[1], pp[0]];
				px = pos1(pp[0], w / s) * s; py = pos1(pp[1], h / s) * s; }
			var lens = [];
			lead.forEach(function(t){ if (t === 'circle' || t === 'ellipse') shape = t; else if (/^(closest|farthest)-(side|corner)$/.test(t)) size = t; else lens.push(t); });
			if (lens.length) { size = null; if (lens.length === 1 && shape !== 'ellipse') { shape = 'circle'; } sz = lens.length === 1 ? [len(lens[0], w / s) * s, len(lens[0], w / s) * s] : [len(lens[0], w / s) * s, len(lens[1], h / s) * s]; if (lens.length === 1 && !/circle/.test(pre)) shape = 'circle'; }
		}
		var rx, ry;
		if (sz) { rx = sz[0]; ry = shape === 'circle' ? sz[0] : sz[1]; }
		else {
			var dl = px, dr = w - px, dt = py, db = h - py, csx = Math.min(Math.abs(dl), Math.abs(dr)), csy = Math.min(Math.abs(dt), Math.abs(db)), fsx = Math.max(Math.abs(dl), Math.abs(dr)), fsy = Math.max(Math.abs(dt), Math.abs(db));
			if (shape === 'circle') {
				if (size === 'closest-side') rx = Math.min(csx, csy); else if (size === 'farthest-side') rx = Math.max(fsx, fsy);
				else if (size === 'closest-corner') rx = Math.sqrt(csx * csx + csy * csy); else rx = Math.sqrt(fsx * fsx + fsy * fsy);
				ry = rx;
			} else {
				if (size === 'closest-side') { rx = csx; ry = csy; } else if (size === 'farthest-side') { rx = fsx; ry = fsy; }
				else if (size === 'closest-corner') { rx = csx * Math.SQRT2; ry = csy * Math.SQRT2; } else { rx = fsx * Math.SQRT2; ry = fsy * Math.SQRT2; }
			}
		}
		rx = Math.max(rx || 0, 0.01); ry = Math.max(ry || 0, 0.01);
		var st2 = stopList(args, rx / s); if (!st2.length) return null; if (rep) st2 = repeatStops(st2);
		g = {type:'radial', m:{a:rx, b:0, c:0, d:ry, e:x + px, f:y + py}, stops:normStops(fixTransparent(st2))};
	} else {
		var from = 0, ccx = w / 2, ccy = h / 2;
		if (pre) { var fm = /from\s+(-?[\d.]+)(deg|turn|rad)/.exec(pre); if (fm) from = fm[2] === 'deg' ? +fm[1] : fm[2] === 'turn' ? fm[1] * 360 : fm[1] * 180 / Math.PI;
			var at2 = pre.split(/\bat\b/)[1]; if (at2) { var q2 = at2.trim().split(/\s+/); ccx = pos1(q2[0], w / s) * s; ccy = pos1(q2[1] || 'center', h / s) * s; } }
		var st3 = [];
		args.forEach(function(a){ var parts = splitTop(a, ' '), c = null, ps = []; parts.forEach(function(p){ if (c == null && COLRE.test(p) && col(p)) c = col(p); else ps.push(p); }); if (!c) return;
			if (!ps.length) st3.push({o:null, c:c}); ps.forEach(function(p){ var v = /deg$/.test(p) ? parseFloat(p) / 360 : /turn$/.test(p) ? parseFloat(p) : /%$/.test(p) ? parseFloat(p) / 100 : null; st3.push({o:v, c:c}); }); });
		if (!st3.length) return null;
		if (st3[0].o == null) st3[0].o = 0; if (st3[st3.length - 1].o == null) st3[st3.length - 1].o = 1;
		for (var j = 1; j < st3.length; j++) if (st3[j].o == null) { var k = j; while (st3[k].o == null) k++; for (var q = j; q < k; q++) st3[q].o = st3[j - 1].o + (st3[k].o - st3[j - 1].o) * (q - j + 1) / (k - j + 1); }
		st3 = normStops(fixTransparent(st3));
		var Rr = Math.sqrt(w * w + h * h) * 1.2, N = 90, wedges = [];
		for (var i = 0; i < N; i++) {
			var t0 = i / N, t1 = (i + 1) / N, tm = (t0 + t1) / 2, cc = st3[0].c;
			for (var z = 1; z < st3.length; z++) if (tm <= st3[z].o) { var A0 = st3[z - 1], B0 = st3[z]; cc = lerpC(A0.c, B0.c, B0.o > A0.o ? (tm - A0.o) / (B0.o - A0.o) : 1); break; }
			var a0 = (from + t0 * 360 - 0.4) * Math.PI / 180, a1 = (from + t1 * 360 + 0.4) * Math.PI / 180, X = x + ccx, Y = y + ccy;
			wedges.push({path:polyP([[X, Y], [X + Rr * Math.sin(a0), Y - Rr * Math.cos(a0)], [X + Rr * Math.sin(a1), Y - Rr * Math.cos(a1)]]), c:cc});
		}
		return {wedges:wedges};
	}
	return {grad:g};
}
function gradXform(g, M){ /* move a gradient by a matrix */
	if (!g || isI(M)) return g;
	if (g.type === 'radial') return {type:'radial', m:mm(M, g.m), stops:g.stops};
	var p1 = tp(M, g.x1, g.y1), p2 = tp(M, g.x2, g.y2); return {type:'linear', x1:p1[0], y1:p1[1], x2:p2[0], y2:p2[1], stops:g.stops};
}
function gradOpaque(g){ return g.stops.every(function(s){ return s.c.a >= 0.995; }); }
/* ===== DSVX part B: reading a rendered slide into vector items =====
   read(win, root, W, H) -> {W, H, bg, layers:[{name, kind, items}], fonts:{family:{weight:1}}}
   item kinds: shape {path, fill:{c}|{grad}, stroke, op, blur, fillRule}
               text  {runs:[{s,x,y,w,line,size,family,stack,weight,italic,color,ls}], align, m, op}
               image {src, x,y,w,h, clip, m, op}           (local coords, m = local -> slide)
               group {items, clip, op, blur}
   all coordinates are slide units (0..W, 0..H). */
var SKIP = {script:1, style:1, noscript:1, template:1, button:1, input:1, textarea:1, select:1, option:1, link:1, meta:1, title:1, br:1, wbr:1, iframe:1, video:1, audio:1, object:1, embed:1};
var NOTINLINE = {img:1, svg:1, canvas:1, video:1, iframe:1, input:1, button:1, select:1, textarea:1};
var SVGNS = 'http://www.w3.org/2000/svg';
var fmCache = {};
function fontMetrics(fam, wt, it){
	var k = fam + '|' + wt + '|' + it; if (fmCache[k]) return fmCache[k];
	var cx = fontMetrics.cx || (fontMetrics.cx = document.createElement('canvas').getContext('2d'));
	cx.font = (it ? 'italic ' : '') + wt + ' 100px ' + fam;
	var m = cx.measureText('Hgjpqy'), a = m.fontBoundingBoxAscent, d = m.fontBoundingBoxDescent;
	if (!(a > 0)) { a = 92; d = 24; }
	return (fmCache[k] = {r:a / (a + d)});
}
function firstFamily(ff){ return String(ff || '').split(',')[0].replace(/["']/g, '').trim() || 'Arial'; }
function prettyName(k, el){
	k = String(k || '').replace(/^t\./, '').replace(/^tf-/, '').replace(/^x-/, '');
	var pm = /^part\.(\d+)/.exec(k), tx = (el && el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 30);
	if (pm) return 'Point ' + (+pm[1] + 1) + (tx ? ' - ' + tx : '');
	var m = /^([a-z]+)(\d+)$/i.exec(k), base = (m ? m[1] : k).toLowerCase();
	var TY = {ell:'Ellipse', rect:'Rectangle', path:'Path', line:'Line', shape:'Shape', img:'Image', pic:'Picture', icon:'Icon', text:'Text', glow:'Glow', ring:'Ring', bar:'Bar', dot:'Dot', card:'Card', poly:'Polygon', tri:'Triangle', arc:'Arc'};
	var label = TY[base] || (k.charAt(0).toUpperCase() + k.slice(1).replace(/[-_.]+/g, ' '));
	if (base === 'text') { var t = (el && el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 30); if (t) return 'Text - ' + t; }
	return m ? label + ' ' + (+m[2] + 1) : label;
}
function kindOf(k){ var m = /^([a-z]+)\d+$/i.exec(String(k || '')); return m ? m[1].toLowerCase() : ''; }

function read(win, root, W, H, opt){
	opt = opt || {};
	var doc = win.document, gcs = function(e){ return win.getComputedStyle(e); };
	var RR = root.getBoundingClientRect(), s = W / (RR.width || W), fonts = {}, pend = [];
	function rel(r){ return {x:(r.left - RR.left) * s, y:(r.top - RR.top) * s, w:r.width * s, h:r.height * s}; }
	function px(v){ var n = parseFloat(v); return isFinite(n) ? n * s : 0; }
	function radii(cs, w, h){
		return ['borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomRightRadius', 'borderBottomLeftRadius'].map(function(p){
			var v = String(cs[p] || '0').trim().split(/\s+/); var a = len(v[0], w / s) || 0, b = v.length > 1 ? (len(v[1], h / s) || 0) : (/%$/.test(v[0]) ? len(v[0], h / s) || 0 : a);
			return [a * s, b * s];
		});
	}
	function shrinkR(R, d){ return R.map(function(r){ return [Math.max(0, r[0] - d), Math.max(0, r[1] - d)]; }); }

	/* own box painting: shadow, background colour + layers, border */
	function paintBox(el, cs, r, M, out, noSh){
		if (r.w <= 0.01 || r.h <= 0.01) return;
		var R = radii(cs, r.w, r.h), box = rrP(r.x, r.y, r.w, r.h, R), boxM = mapP(box, M);
		if (!noSh && cs.boxShadow && cs.boxShadow !== 'none') splitTop(cs.boxShadow).reverse().forEach(function(sh){
			if (/\binset\b/.test(sh)) return;
			var cm = /(rgba?\([^)]*\)|#[0-9a-f]{3,8})/i.exec(sh), c = cm ? col(cm[1]) : null; if (!c || c.a <= 0.003) return;
			var L = sh.replace(cm[1], '').match(/-?[\d.]+(px)?/g) || [], dx = px(L[0]), dy = px(L[1]), bl = px(L[2]), sp = px(L[3]);
			var p = rrP(r.x + dx - sp, r.y + dy - sp, r.w + 2 * sp, r.h + 2 * sp, R.map(function(q){ return [q[0] + sp, q[1] + sp]; }));
			if (p) out.push({k:'shape', role:'shadow', path:mapP(p, M), fill:{c:c}, blur:bl / 2});
		});
		var bc = col(cs.backgroundColor);
		var bgClip = String(cs.backgroundClip || cs.webkitBackgroundClip || '');
		if (/text/.test(bgClip)) return border();
		if (bc && bc.a > 0.003) out.push({k:'shape', path:boxM, fill:{c:bc}});
		var bi = cs.backgroundImage;
		if (bi && bi !== 'none') {
			var layers = splitTop(bi), sizes = splitTop(cs.backgroundSize), poss = splitTop(cs.backgroundPosition), reps = splitTop(cs.backgroundRepeat);
			for (var i = layers.length - 1; i >= 0; i--) {
				var L = layers[i], sz = sizes[i % sizes.length] || 'auto', ps = poss[i % poss.length] || '0% 0%', rp = reps[i % reps.length] || 'repeat';
				var um = /^url\(\s*["']?([\s\S]*?)["']?\s*\)$/.exec(L);
				if (um) { if (um[1] && um[1] !== 'none') { var it = {k:'pending', type:'bg', src:um[1], box:r, size:sz, pos:ps, clip:box, m:isI(M) ? null : M}; out.push(it); pend.push(it); } continue; }
				bgGrad(L, sz, ps, rp, r, box, M, out);
			}
		}
		border();
		function border(){
			var sides = ['Top', 'Right', 'Bottom', 'Left'].map(function(S){ var st = cs['border' + S + 'Style']; var w = (st === 'none' || st === 'hidden') ? 0 : px(cs['border' + S + 'Width']); return {w:w, c:col(cs['border' + S + 'Color']), st:st}; });
			if (!sides.some(function(q){ return q.w > 0.01 && q.c && q.c.a > 0.003; })) return;
			var same = sides.every(function(q){ return Math.abs(q.w - sides[0].w) < 0.01 && q.st === sides[0].st && q.c && sides[0].c && hex(q.c) === hex(sides[0].c) && Math.abs(q.c.a - sides[0].c.a) < 0.01; });
			if (same) {
				var bw = sides[0].w, p2 = rrP(r.x + bw / 2, r.y + bw / 2, r.w - bw, r.h - bw, shrinkR(R, bw / 2)); if (!p2) return;
				var dash = sides[0].st === 'dashed' ? [bw * 3, bw * 2] : sides[0].st === 'dotted' ? [0.01, bw * 2] : null;
				out.push({k:'shape', role:'border', path:mapP(p2, M), stroke:{c:sides[0].c, w:bw * mdet(M), dash:dash ? dash.map(function(v){ return v * mdet(M); }) : null, cap:sides[0].st === 'dotted' ? 'round' : 'butt'}});
				return;
			}
			var x = r.x, y = r.y, w = r.w, h = r.h, t = sides[0].w, rt = sides[1].w, b = sides[2].w, l = sides[3].w, parts = [];
			var Q = [[[x, y], [x + w, y], [x + w - rt, y + t], [x + l, y + t]], [[x + w, y], [x + w, y + h], [x + w - rt, y + h - b], [x + w - rt, y + t]],
				[[x + w, y + h], [x, y + h], [x + l, y + h - b], [x + w - rt, y + h - b]], [[x, y + h], [x, y], [x + l, y + t], [x + l, y + h - b]]];
			sides.forEach(function(q, i){ if (q.w > 0.01 && q.c && q.c.a > 0.003) parts.push({k:'shape', role:'border', path:mapP(polyP(Q[i]), M), fill:{c:q.c}}); });
			var round = R.some(function(q){ return q[0] > 0.5 || q[1] > 0.5; });
			if (round) out.push({k:'group', clip:boxM, items:parts}); else parts.forEach(function(p){ out.push(p); });
		}
	}
	function bgGrad(L, sz, ps, rp, r, box, M, out){
		var tw = r.w, th = r.h, full = true, szs = String(sz).trim().split(/\s+/);
		if (!/^(auto|auto auto|cover|contain|100% 100%)$/.test(String(sz).trim())) {
			var a = len(szs[0], r.w / s), b = szs.length > 1 ? len(szs[1], r.h / s) : null;
			tw = a == null ? r.w : a * s; th = b == null ? (a == null ? r.h : r.h) : b * s;
			full = tw >= r.w * 0.98 && th >= r.h * 0.98;
		}
		if (full) { var g = cssGrad(L, r.x, r.y, r.w, r.h, s); if (g) out.push(shapeOf(mapP(box, M), g, M)); return; }
		if (tw < 0.5 || th < 0.5) return;
		var pp = String(ps).trim().split(/\s+/), ox = len(pp[0], (r.w - tw) / s), oy = len(pp[1] || '50%', (r.h - th) / s); ox = (ox || 0) * s; oy = (oy || 0) * s;
		var rx = !/no-repeat|repeat-y/.test(rp) && !/^no-repeat/.test(rp), ry = !/no-repeat|repeat-x/.test(rp);
		if (rp === 'repeat-x') { rx = true; ry = false; } if (rp === 'repeat-y') { rx = false; ry = true; } if (rp === 'no-repeat') { rx = ry = false; }
		var x0 = rx ? r.x + (ox % tw) - (ox % tw > 0 ? tw : 0) : r.x + ox, y0 = ry ? r.y + (oy % th) - (oy % th > 0 ? th : 0) : r.y + oy;
		var nx = rx ? Math.ceil((r.x + r.w - x0) / tw) : 1, ny = ry ? Math.ceil((r.y + r.h - y0) / th) : 1;
		if (nx * ny > 1200) return;
		var tiles = [];
		for (var j = 0; j < ny; j++) for (var i = 0; i < nx; i++) { var tx = x0 + i * tw, ty = y0 + j * th, g2 = cssGrad(L, tx, ty, tw, th, s); if (g2) tiles.push(shapeOf(mapP(rectP(tx, ty, tw, th), M), g2, M)); }
		if (tiles.length) out.push({k:'group', role:'pattern', clip:mapP(box, M), items:tiles});
	}
	function shapeOf(path, paint, M){
		if (paint.wedges) return {k:'group', role:'conic', clip:path, items:paint.wedges.map(function(w){ return {k:'shape', path:mapP(w.path, M), fill:{c:w.c}}; })};
		return paint.grad ? {k:'shape', path:path, fill:{grad:gradXform(paint.grad, M)}} : {k:'shape', path:path, fill:{c:paint.c}}; }

	function clipPathOf(cs, r, M){
		var v = String(cs.clipPath || cs.webkitClipPath || 'none').trim(); if (!v || v === 'none') return null;
		var m = /^(inset|polygon|circle|ellipse|path)\(([\s\S]*)\)/.exec(v); if (!m) return null;
		var a = m[2], p = null;
		if (m[1] === 'inset') {
			var rd = a.split(/\bround\b/), q = rd[0].trim().split(/\s+/);
			if (q.length === 1) q = [q[0], q[0], q[0], q[0]]; else if (q.length === 2) q = [q[0], q[1], q[0], q[1]]; else if (q.length === 3) q = [q[0], q[1], q[2], q[1]];
			var t = (len(q[0], r.h / s) || 0) * s, rt = (len(q[1], r.w / s) || 0) * s, b = (len(q[2], r.h / s) || 0) * s, l = (len(q[3], r.w / s) || 0) * s;
			var rr = 0; if (rd[1]) rr = (len(rd[1].trim().split(/\s+/)[0], r.w / s) || 0) * s;
			p = rrP(r.x + l, r.y + t, Math.max(0, r.w - l - rt), Math.max(0, r.h - t - b), [[rr, rr], [rr, rr], [rr, rr], [rr, rr]]);
			if (!p) p = rectP(r.x, r.y, 0, 0);
		} else if (m[1] === 'polygon') {
			var pts = splitTop(a).filter(function(x){ return !/^(nonzero|evenodd)$/.test(x); }).map(function(x){ var k = x.trim().split(/\s+/); return [r.x + (len(k[0], r.w / s) || 0) * s, r.y + (len(k[1], r.h / s) || 0) * s]; });
			if (pts.length > 2) p = polyP(pts);
		} else if (m[1] === 'circle' || m[1] === 'ellipse') {
			var at = a.split(/\bat\b/), lead = (at[0] || '').trim().split(/\s+/).filter(Boolean), cx = r.w / 2, cy = r.h / 2;
			if (at[1]) { var pp = at[1].trim().split(/\s+/); cx = pos1(pp[0], r.w / s) * s; cy = pos1(pp[1] || 'center', r.h / s) * s; }
			var rx, ry, diag = Math.sqrt(r.w * r.w + r.h * r.h) / Math.SQRT2;
			if (m[1] === 'circle') { rx = lead[0] && !/side|corner/.test(lead[0]) ? (/%$/.test(lead[0]) ? parseFloat(lead[0]) / 100 * diag : parseFloat(lead[0]) * s) : (lead[0] === 'farthest-side' ? Math.max(cx, cy, r.w - cx, r.h - cy) : Math.min(cx, cy, r.w - cx, r.h - cy)); ry = rx; }
			else { rx = lead[0] && !/side/.test(lead[0]) ? (len(lead[0], r.w / s) || 0) * s : Math.min(cx, r.w - cx); ry = lead[1] && !/side/.test(lead[1]) ? (len(lead[1], r.h / s) || 0) * s : Math.min(cy, r.h - cy); }
			p = ellP(r.x + cx, r.y + cy, rx, ry);
		} else if (m[1] === 'path') {
			var dm = /["']([\s\S]*)["']/.exec(a); if (dm) p = mapP(parseD(dm[1]), {a:s, b:0, c:0, d:s, e:r.x, f:r.y});
		}
		return p ? mapP(p, M) : null;
	}
	function ownMatrix(el, cs, r0){
		var parts = [];
		if (cs.translate && cs.translate !== 'none') { var tt = cs.translate.split(/\s+/); parts.push(mT((parseFloat(tt[0]) || 0) * s, (parseFloat(tt[1]) || 0) * s)); }
		if (cs.rotate && cs.rotate !== 'none') { var rm = /(-?[\d.]+)deg/.exec(cs.rotate); if (rm) { var a = rm[1] * Math.PI / 180; parts.push({a:Math.cos(a), b:Math.sin(a), c:-Math.sin(a), d:Math.cos(a), e:0, f:0}); } }
		if (cs.scale && cs.scale !== 'none') { var sc = cs.scale.split(/\s+/).map(parseFloat); parts.push({a:sc[0], b:0, c:0, d:sc.length > 1 ? sc[1] : sc[0], e:0, f:0}); }
		if (cs.transform && cs.transform !== 'none') {
			var mm3 = /matrix3d\(([^)]+)\)/.exec(cs.transform), m2 = /matrix\(([^)]+)\)/.exec(cs.transform), v;
			if (mm3) { v = mm3[1].split(',').map(parseFloat); parts.push({a:v[0], b:v[1], c:v[4], d:v[5], e:v[12] * s, f:v[13] * s}); }
			else if (m2) { v = m2[1].split(',').map(parseFloat); parts.push({a:v[0], b:v[1], c:v[2], d:v[3], e:v[4] * s, f:v[5] * s}); }
		}
		if (!parts.length) return null;
		var o = String(cs.transformOrigin || '').split(/\s+/);
		el.style.setProperty('transform', 'none', 'important'); el.style.setProperty('rotate', 'none', 'important'); el.style.setProperty('scale', 'none', 'important'); el.style.setProperty('translate', 'none', 'important');
		var r = rel(el.getBoundingClientRect()), ox = r.x + px(o[0]), oy = r.y + px(o[1]);
		var T = parts.reduce(function(acc, p){ return mm(acc, p); }, IM);
		return mm(mT(ox, oy), mm(T, mT(-ox, -oy)));
	}
	function zOf(cs){ var z = parseInt(cs.zIndex, 10); return cs.position !== 'static' && isFinite(z) ? z : 0; }
	function collect(el, texts, blocks){
		for (var n = el.firstChild; n; n = n.nextSibling) {
			if (n.nodeType === 3) { if (/\S/.test(n.data) || /pre/.test(gcs(el).whiteSpace) && n.data.length && texts.length) texts.push(n); continue; }
			if (n.nodeType !== 1) continue;
			var tg = n.localName; if (SKIP[tg] && tg !== 'br') continue; if (tg === 'br') continue;
			var cs = gcs(n);
			if (cs.display === 'inline' && !NOTINLINE[tg]) collect(n, texts, blocks); else blocks.push(n);
		}
	}
	function textFill(ps, p, stop){
		var c = col(ps.webkitTextFillColor) || col(ps.color);
		if (c && c.a > 0.003) return c;
		for (var e = p; e && e !== stop.parentElement; e = e.parentElement) {
			var es = gcs(e); if (/text/.test(String(es.backgroundClip || es.webkitBackgroundClip)) && es.backgroundImage !== 'none') {
				var g = cssGrad(splitTop(es.backgroundImage)[0], 0, 0, 100, 100, 1);
				if (g && g.grad) { var st = g.grad.stops, k = st[Math.floor(st.length / 2)]; return {r:k.c.r, g:k.c.g, b:k.c.b, a:1}; }
				if (g && g.c) return g.c;
			}
		}
		return c || {r:0, g:0, b:0, a:1};
	}
	function textItem(el, cs, nodes, M, op){
		var chars = [], rng = doc.createRange();
		nodes.forEach(function(tn){
			var p = tn.parentElement, ps = gcs(p); if (ps.visibility === 'hidden') return;
			var tt = ps.textTransform, txt = tn.data, key = [ps.fontFamily, ps.fontSize, ps.fontWeight, ps.fontStyle, ps.color, ps.letterSpacing, ps.webkitTextFillColor].join('|');
			for (var i = 0; i < txt.length; i++) {
				var cc = txt.charCodeAt(i), n1 = cc >= 0xD800 && cc <= 0xDBFF ? 2 : 1, ch = txt.substr(i, n1), prev = i ? txt[i - 1] : ' ';
				rng.setStart(tn, i); rng.setEnd(tn, i + n1); i += n1 - 1;
				var rs = rng.getClientRects(); if (!rs.length) continue;
				var b = rs[0]; for (var q = 0; q < rs.length; q++) if (rs[q].width > 0) { b = rs[q]; break; }
				if (/[\r\n]/.test(ch)) continue;
				if (b.width <= 0.01 && /\s/.test(ch)) continue;
				if (tt === 'uppercase') ch = ch.toUpperCase(); else if (tt === 'lowercase') ch = ch.toLowerCase(); else if (tt === 'capitalize' && /\s/.test(prev)) ch = ch.toUpperCase();
				if (/\s/.test(ch)) ch = ' ';
				if (/[\u00AD\u200B-\u200D\uFEFF\u2060]/.test(ch)) continue;
				if (/[\u2010\u2011]/.test(ch)) ch = '-';
				chars.push({c:ch, l:(b.left - RR.left) * s, r:(b.right - RR.left) * s, t:(b.top - RR.top) * s, b:(b.bottom - RR.top) * s, ps:ps, p:p, key:key});
			}
		});
		if (!chars.length) return null;
		var lines = [], cur = null;
		chars.forEach(function(c){
			var same = cur && c.l >= cur.lastR - Math.max(1, (c.b - c.t) * 0.3) && (Math.min(c.b, cur.b) - Math.max(c.t, cur.t)) > 0.35 * Math.min(c.b - c.t, cur.b - cur.t);
			if (!same) { cur = {chars:[], t:c.t, b:c.b, lastR:c.r}; lines.push(cur); }
			cur.chars.push(c); cur.t = Math.min(cur.t, c.t); cur.b = Math.max(cur.b, c.b); cur.lastR = c.r;
		});
		var runs = [];
		lines.forEach(function(L, li){
			var cs2 = L.chars, a = 0, z = cs2.length - 1;
			while (a <= z && cs2[a].c === ' ') a++; while (z >= a && cs2[z].c === ' ') z--;
			if (a > z) return;
			var run = null;
			for (var i = a; i <= z; i++) {
				var c = cs2[i];
				if (!run || run.key !== c.key) { run = {key:c.key, chars:[], ps:c.ps, p:c.p}; runs.push(run); run.line = li; }
				run.chars.push(c);
			}
		});
		var out = runs.map(function(R){
			var ps = R.ps, c0 = R.chars[0], cN = R.chars[R.chars.length - 1], t = 1e9, b = -1e9;
			R.chars.forEach(function(c){ t = Math.min(t, c.t); b = Math.max(b, c.b); });
			var fam = firstFamily(ps.fontFamily), wt = parseInt(ps.fontWeight, 10) || 400, it = ps.fontStyle !== 'normal', fm = fontMetrics(ps.fontFamily, wt, it);
			(fonts[fam] = fonts[fam] || {})[wt + (it ? 'i' : '')] = 1;
			var ls = parseFloat(ps.letterSpacing); ls = isFinite(ls) ? ls * s : 0;
			return {s:R.chars.map(function(c){ return c.c; }).join('').replace(/ {2,}/g, function(m){ return m; }), x:c0.l, w:cN.r - c0.l, y:t + (b - t) * fm.r, line:R.line,
				size:parseFloat(ps.fontSize) * s, family:fam, stack:ps.fontFamily, weight:wt, italic:it, color:textFill(ps, R.p, el), ls:ls};
		});
		var al = String(cs.textAlign || 'left'); al = /center/.test(al) ? 'center' : /right|end/.test(al) ? 'right' : 'left';
		return {k:'text', runs:out, align:al, m:isI(M) ? null : M, op:op};
	}
	function imgEl(el, cs, r, M, out){
		var src = el.currentSrc || el.src; if (!src) return;
		var R = radii(cs, r.w, r.h), clip = rrP(r.x, r.y, r.w, r.h, R);
		var svgLike = /^data:image\/svg/i.test(src) || /\.svg(\?|#|$)/i.test(src) || /api\.iconify\.design/i.test(src);
		var it = {k:'pending', type:svgLike ? 'svgimg' : 'img', src:src, box:r, fit:cs.objectFit || 'fill', pos:cs.objectPosition || '50% 50%', clip:clip, m:isI(M) ? null : M, nw:el.naturalWidth, nh:el.naturalHeight};
		out.push(it); pend.push(it);
	}

	/* ---------- inline svg ---------- */
	function svgItems(svg, M, op0){
		var S0 = {a:s, b:0, c:0, d:s, e:-RR.left * s, f:-RR.top * s}, out = [];
		function ctm(e){ var m = e.getScreenCTM && e.getScreenCTM(); if (!m) return null; return mm(M, mm(S0, {a:m.a, b:m.b, c:m.c, d:m.d, e:m.e, f:m.f})); }
		function byId(id){ try { return svg.querySelector('#' + (win.CSS && win.CSS.escape ? win.CSS.escape(id) : id)) || doc.getElementById(id); } catch (e) { return doc.getElementById(id); } }
		function lenOf(L, ref){ if (!L) return 0; var b = L.baseVal; return b.unitType === 2 ? b.valueInSpecifiedUnits / 100 * (ref == null ? 1 : ref) : b.value; }
		function chain(g, attr){ var e = g, n = 0; while (e && n++ < 6) { if (attr === 'stops' ? e.querySelector('stop') : e.hasAttribute(attr)) return e; var h = e.getAttribute('href') || e.getAttributeNS('http://www.w3.org/1999/xlink', 'href'); e = h && h.charAt(0) === '#' ? byId(h.slice(1)) : null; } return g; }
		function gradPaint(el, g, m, alpha){
			var sg = chain(g, 'stops'), stops = [].map.call(sg.querySelectorAll('stop'), function(st){ var c = col(gcs(st).stopColor) || {r:0, g:0, b:0, a:1}; var o = st.offset ? st.offset.baseVal : parseFloat(st.getAttribute('offset')) || 0; return {o:clamp(o, 0, 1), c:withA(c, (parseFloat(gcs(st).stopOpacity) || 0) * alpha)}; });
			if (!stops.length) return null; for (var i = 1; i < stops.length; i++) if (stops[i].o < stops[i - 1].o) stops[i].o = stops[i - 1].o;
			stops = normStops(stops);
			var ub = g.gradientUnits ? g.gradientUnits.baseVal !== 1 : true, G = IM;
			if (ub) { var bb; try { bb = el.getBBox(); } catch (e) { bb = {x:0, y:0, width:1, height:1}; } G = {a:bb.width || 1, b:0, c:0, d:bb.height || 1, e:bb.x, f:bb.y}; }
			try { var tl = g.gradientTransform && g.gradientTransform.baseVal.consolidate(); if (tl) { var t = tl.matrix; G = mm(G, {a:t.a, b:t.b, c:t.c, d:t.d, e:t.e, f:t.f}); } } catch (e) {}
			var MG = mm(m, G);
			if (g.localName === 'radialGradient') {
				var cx = lenOf(chain(g, 'cx').cx, 1), cy = lenOf(chain(g, 'cy').cy, 1), rr = lenOf(chain(g, 'r').r, 1);
				if (!g.hasAttribute('cx') && chain(g, 'cx') === g) cx = ub ? 0.5 : cx; if (!g.hasAttribute('cy') && chain(g, 'cy') === g) cy = ub ? 0.5 : cy; if (!g.hasAttribute('r') && chain(g, 'r') === g) rr = ub ? 0.5 : rr;
				return {grad:{type:'radial', m:mm(MG, {a:rr, b:0, c:0, d:rr, e:cx, f:cy}), stops:stops}};
			}
			var x1 = g.hasAttribute('x1') ? lenOf(g.x1, 1) : 0, y1 = g.hasAttribute('y1') ? lenOf(g.y1, 1) : 0, x2 = g.hasAttribute('x2') ? lenOf(g.x2, 1) : (ub ? 1 : lenOf(g.x2, 1)), y2 = g.hasAttribute('y2') ? lenOf(g.y2, 1) : 0;
			var p1 = tp(MG, x1, y1), p2 = tp(MG, x2, y2);
			return {grad:{type:'linear', x1:p1[0], y1:p1[1], x2:p2[0], y2:p2[1], stops:stops}};
		}
		function paintOf(el, cs, key, m){
			var v = String(cs[key] || 'none'), alpha = parseFloat(cs[key + 'Opacity']); if (!isFinite(alpha)) alpha = 1;
			if (v === 'none') return null;
			var u = /url\(["']?#([^"')]+)["']?\)\s*(.*)$/.exec(v);
			if (u) { var g = byId(u[1]); if (g && /Gradient$/.test(g.localName)) return gradPaint(el, g, m, alpha); var fb = col(u[2]); return fb ? {c:withA(fb, alpha)} : null; }
			var c = col(v); if (!c || c.a * alpha <= 0.003) return null; return {c:withA(c, alpha)};
		}
		function geom(e){
			var t = e.localName, d = null;
			try {
				if (t === 'path') d = parseD(e.getAttribute('d'));
				else if (t === 'rect') { var x = e.x.baseVal.value, y = e.y.baseVal.value, w = e.width.baseVal.value, h = e.height.baseVal.value, rx = e.rx.baseVal.value, ry = e.ry.baseVal.value; if (!e.hasAttribute('ry') && rx && !ry) ry = rx; if (!e.hasAttribute('rx') && ry && !rx) rx = ry; d = w > 0 && h > 0 ? rrP(x, y, w, h, [[rx, ry], [rx, ry], [rx, ry], [rx, ry]]) : null; }
				else if (t === 'circle') { var r = e.r.baseVal.value; d = r > 0 ? ellP(e.cx.baseVal.value, e.cy.baseVal.value, r, r) : null; }
				else if (t === 'ellipse') { var a = e.rx.baseVal.value, b = e.ry.baseVal.value; d = a > 0 && b > 0 ? ellP(e.cx.baseVal.value, e.cy.baseVal.value, a, b) : null; }
				else if (t === 'line') d = [['M', e.x1.baseVal.value, e.y1.baseVal.value], ['L', e.x2.baseVal.value, e.y2.baseVal.value]];
				else if (t === 'polyline' || t === 'polygon') { var pts = []; for (var i = 0; i < e.points.numberOfItems; i++) { var q = e.points.getItem(i); pts.push([q.x, q.y]); } d = pts.length ? polyP(pts, t === 'polygon') : null; }
			} catch (er) { d = null; }
			return d && d.length ? d : null;
		}
		function clipOf(e, m){
			var v = String(gcs(e).clipPath || 'none'), u = /url\(["']?#([^"')]+)/.exec(v); if (!u) return null;
			var cp = byId(u[1]); if (!cp) return null;
			var obb = cp.clipPathUnits && cp.clipPathUnits.baseVal === 2, B = IM;
			if (obb) { try { var bb = e.getBBox(); B = {a:bb.width, b:0, c:0, d:bb.height, e:bb.x, f:bb.y}; } catch (er) {} }
			var all = [];
			[].forEach.call(cp.querySelectorAll('path,rect,circle,ellipse,polygon,polyline'), function(c){
				var g = geom(c); if (!g) return; var T = IM; try { var tl = c.transform.baseVal.consolidate(); if (tl) T = {a:tl.matrix.a, b:tl.matrix.b, c:tl.matrix.c, d:tl.matrix.d, e:tl.matrix.e, f:tl.matrix.f}; } catch (er) {}
				all = all.concat(mapP(g, mm(m, mm(B, T))));
			});
			return all.length ? all : null;
		}
		function walk(node, op, list){
			for (var e = node.firstElementChild; e; e = e.nextElementSibling) {
				var t = e.localName;
				if (/^(defs|clipPath|mask|pattern|symbol|marker|linearGradient|radialGradient|style|title|desc|metadata|filter|script|foreignObject)$/.test(t)) continue;
				var cs = gcs(e); if (cs.display === 'none') continue;
				var o = op * (parseFloat(cs.opacity) || (cs.opacity === '0' ? 0 : 1)); if (o <= 0.003) continue;
				var m = ctm(e); if (!m) continue;
				var sub = list, cl = clipOf(e, m);
				if (cl) sub = [];
				if (t === 'g' || t === 'a' || t === 'svg' || t === 'switch') walk(e, o, sub);
				else if (t === 'use') {
					var h = e.getAttribute('href') || e.getAttributeNS('http://www.w3.org/1999/xlink', 'href'), ref = h && h.charAt(0) === '#' ? byId(h.slice(1)) : null;
					if (ref) { var g = doc.createElementNS(SVGNS, 'g'), ux = e.x.baseVal.value, uy = e.y.baseVal.value; g.setAttribute('transform', (e.getAttribute('transform') || '') + ' translate(' + ux + ' ' + uy + ')');
						var cl2 = ref.cloneNode(true); cl2.removeAttribute('id'); if (cl2.localName === 'symbol') { var gg = doc.createElementNS(SVGNS, 'g'); while (cl2.firstChild) gg.appendChild(cl2.firstChild); cl2 = gg; }
						g.appendChild(cl2); ['fill', 'stroke', 'color'].forEach(function(a){ if (e.hasAttribute(a)) g.setAttribute(a, e.getAttribute(a)); });
						e.parentNode.insertBefore(g, e.nextSibling); try { walk({firstElementChild:g}, op, sub); } finally { g.remove(); } }
				}
				else if (t === 'text') svgText(e, cs, m, o, sub);
				else if (t === 'image') {
					var hr = e.getAttribute('href') || e.getAttributeNS('http://www.w3.org/1999/xlink', 'href'); if (hr) {
						var bx = {x:e.x.baseVal.value, y:e.y.baseVal.value, w:e.width.baseVal.value, h:e.height.baseVal.value}, par = e.getAttribute('preserveAspectRatio') || '';
						var it = {k:'pending', type:'img', src:hr, box:bx, fit:/none/.test(par) ? 'fill' : /slice/.test(par) ? 'cover' : 'contain', pos:'50% 50%', clip:rectP(bx.x, bx.y, bx.w, bx.h), m:m, op:o, unit:1};
						sub.push(it); pend.push(it); }
				}
				else {
					var d = geom(e); if (d) {
						var path = mapP(d, m), it2 = {k:'shape', path:path, op:o, fillRule:cs.fillRule === 'evenodd' ? 'evenodd' : null};
						var f = (t === 'line') ? null : paintOf(e, cs, 'fill', m); if (f) it2.fill = f;
						var sw = parseFloat(cs.strokeWidth);
						if (sw > 0) { var sp = paintOf(e, cs, 'stroke', m); if (sp) {
							var k = cs.vectorEffect === 'non-scaling-stroke' ? s * mdet(M) : mdet(m);
							var da = cs.strokeDasharray && cs.strokeDasharray !== 'none' ? cs.strokeDasharray.split(/[\s,]+/).map(parseFloat).filter(isFinite).map(function(v){ return v * k; }) : null;
							it2.stroke = {c:sp.c || (sp.grad && sp.grad.stops[0].c), w:sw * k, dash:da && da.length ? da : null, cap:cs.strokeLinecap, join:cs.strokeLinejoin}; } }
						if (it2.fill || it2.stroke) sub.push(it2);
					}
				}
				if (cl && sub.length) list.push({k:'group', clip:cl, items:sub});
			}
		}
		function svgText(e, cs, m, o, list){
			var n = 0; try { n = e.getNumberOfChars(); } catch (er) {} if (!n) return;
			var txt = e.textContent, fill = paintOf(e, cs, 'fill', m), c = fill ? (fill.c || fill.grad.stops[0].c) : null; if (!c) return;
			var size = parseFloat(cs.fontSize) || 16, fam = firstFamily(cs.fontFamily), wt = parseInt(cs.fontWeight, 10) || 400, it = cs.fontStyle !== 'normal', runs = [], cur = null, ls = parseFloat(cs.letterSpacing) || 0;
			(fonts[fam] = fonts[fam] || {})[wt + (it ? 'i' : '')] = 1;
			for (var i = 0; i < n && i < txt.length; i++) {
				var a, b; try { a = e.getStartPositionOfChar(i); b = e.getEndPositionOfChar(i); } catch (er) { continue; }
				var ch = txt[i]; if (/\s/.test(ch)) ch = ' ';
				if (!cur || Math.abs(a.y - cur.y) > 0.5 || Math.abs(a.x - cur.ex) > size * 0.35) { cur = {s:'', x:a.x, y:a.y, ex:a.x}; runs.push(cur); }
				cur.s += ch; cur.ex = b.x;
			}
			runs = runs.filter(function(r){ return r.s.trim(); }).map(function(r, i){ return {s:r.s, x:r.x, y:r.y, w:r.ex - r.x, line:i, size:size, family:fam, stack:cs.fontFamily, weight:wt, italic:it, color:c, ls:ls}; });
			if (runs.length) list.push({k:'text', runs:runs, align:'left', m:m, op:o});
		}
		walk(svg, op0 == null ? 1 : op0, out);
		return out;
	}

	/* ---------- the element walk ---------- */
	function visit(el, A, op, out){
		if (!el || el.nodeType !== 1) return;
		var tag = el.localName; if (SKIP[tag]) return;
		var cs = gcs(el); if (cs.display === 'none') return;
		var o = op * parseFloat(cs.opacity); if (!(o > 0.004)) return;
		var r0 = null, own = ownMatrix(el, cs, r0), M = own ? mm(A, own) : A;
		var r = rel(el.getBoundingClientRect()), hidden = cs.visibility === 'hidden' || cs.visibility === 'collapse', mine = [];
		if (!hidden) paintBox(el, cs, r, M, mine);
		if (tag === 'svg') {
			if (!hidden) { var si = svgItems(el, M, 1); if (si.length) { if (cs.overflow !== 'visible') mine.push({k:'group', clip:mapP(rectP(r.x, r.y, r.w, r.h), M), items:si}); else si.forEach(function(x){ mine.push(x); }); } }
		} else if (tag === 'img') { if (!hidden) imgEl(el, cs, r, M, mine); }
		else if (tag === 'canvas') {
			if (!hidden) { var src = null; try { src = el.toDataURL('image/png'); } catch (e) {} if (src) { var ci = {k:'pending', type:'img', src:src, box:r, fit:cs.objectFit || 'fill', pos:'50% 50%', clip:rectP(r.x, r.y, r.w, r.h), m:isI(M) ? null : M}; mine.push(ci); pend.push(ci); } }
		} else {
			var texts = [], blocks = [], inner = [];
			collect(el, texts, blocks);
			var flow = [], posd = [];
			blocks.forEach(function(b, i){ var bs = gcs(b); (bs.position === 'static' ? flow : posd).push({el:b, z:zOf(bs), i:i}); });
			flow.forEach(function(b){ visit(b.el, M, o, inner); });
			if (texts.length) { var ti = textItem(el, cs, texts, M, o); if (ti) inner.push(ti); }
			posd.sort(function(a, b){ return a.z - b.z || a.i - b.i; }).forEach(function(b){ visit(b.el, M, o, inner); });
			if (inner.length) {
				if (cs.overflow !== 'visible' && cs.overflow !== 'visible visible' && el !== root) {
					var bl = px(cs.borderLeftWidth), bt = px(cs.borderTopWidth), br = px(cs.borderRightWidth), bb = px(cs.borderBottomWidth);
					var cp = rrP(r.x + bl, r.y + bt, r.w - bl - br, r.h - bt - bb, shrinkR(radii(cs, r.w, r.h), Math.max(bl, bt)));
					inner = cp ? [{k:'group', clip:mapP(cp, M), items:inner}] : [];
				}
				inner.forEach(function(x){ mine.push(x); });
			}
		}
		/* opacity is carried down to every item */
		if (o < 0.999) mine.forEach(function f(x){ if (x.k === 'group') x.items.forEach(f); else if (x.op == null) x.op = o; });
		var cp2 = clipPathOf(cs, r, M), fb = /blur\(([\d.]+)px\)/.exec(cs.filter || ''), blur = fb ? parseFloat(fb[1]) * s / 2 : 0;
		if ((cp2 || blur) && mine.length) mine = [{k:'group', clip:cp2, blur:blur || 0, items:mine}];
		mine.forEach(function(x){ out.push(x); });
	}

	/* ---------- layers: one per element of the slide ---------- */
	var slideEl = root.querySelector('.dste-slide') || root, layers = [], bgItems = [];
	if (slideEl !== root) { var rcs = gcs(root); paintBox(root, rcs, rel(root.getBoundingClientRect()), IM, bgItems, 1); }
	var scs = gcs(slideEl); paintBox(slideEl, scs, rel(slideEl.getBoundingClientRect()), IM, bgItems, 1);
	if (bgItems.length) layers.push({name:'Background', kind:'bg', items:bgItems});
	var kids = [];
	var texts0 = [], blocks0 = []; collect(slideEl, texts0, blocks0);
	blocks0.forEach(function(b, i){ var bs = gcs(b); kids.push({el:b, z:bs.position === 'static' ? -1e6 : zOf(bs), i:i}); });
	if (slideEl !== root) for (var n = root.firstElementChild; n; n = n.nextElementSibling) if (n !== slideEl && !n.contains(slideEl)) kids.push({el:n, z:1e6, i:1e6 + kids.length});
	kids.sort(function(a, b){ return a.z - b.z || a.i - b.i; });
	var ti0 = texts0.length ? textItem(slideEl, scs, texts0, IM, 1) : null; if (ti0) layers.push({name:'Text', kind:'', items:[ti0]});
	kids.forEach(function(k, i){
		var items = []; visit(k.el, IM, 1, items); if (!items.length) return;
		var key = k.el.getAttribute('data-dk') || k.el.getAttribute('data-ed-field'), d = null;
		if (!key) { d = k.el.querySelector('[data-dk],[data-ed-field]'); if (d) key = d.getAttribute('data-dk') || d.getAttribute('data-ed-field'); }
		var name = key ? prettyName(key, d || k.el) : 'Element ' + (i + 1), kind = kindOf(key);
		var deco = /^(ell|rect|path|line|shape|ring|glow|dot|bar|poly|tri|arc)$/.test(kind) && !items.some(function f(x){ return x.k === 'text' || x.k === 'pending' || (x.k === 'group' && x.items.some(f)); });
		var last = layers[layers.length - 1];
		if (deco && last && last.deco && last.kind === kind) { last.items = last.items.concat(items); last.count++; last.name = prettyName(kind) + 's (' + last.count + ')'; return; }
		layers.push({name:name, kind:kind, deco:deco, count:1, items:items});
	});
	/* background colour of the slide (EPS flattening needs it) */
	var bg = col(scs.backgroundColor); if (!bg || bg.a < 0.5) bg = col(gcs(root).backgroundColor);
	if (!bg || bg.a < 0.5) { var g0 = scs.backgroundImage !== 'none' ? cssGrad(splitTop(scs.backgroundImage).pop(), 0, 0, 10, 10, 1) : null; bg = g0 && g0.grad ? g0.grad.stops[0].c : (g0 && g0.c) || {r:255, g:255, b:255, a:1}; }
	return {W:W, H:H, s:s, bg:{r:bg.r, g:bg.g, b:bg.b, a:1}, layers:layers, fonts:fonts, pending:pend, svgItems:svgItems, RR:RR, root:root};
}

/* ---------- pictures: load, place (cover / contain / position) and inline svg icons ---------- */
var imgCache = {};
function loadImg(src){
	if (imgCache[src]) return imgCache[src];
	return (imgCache[src] = new Promise(function(res){
		/* 1. the picture itself (readable when the server allows it), 2. the same picture through this site (pictures kept on
		   another storage host), 3. as a last resort a picture that can be shown but not read (linked in the SVG only) */
		var cfg = window.DSVX_CFG || {}, tries = [];
		if (/^https?:/i.test(src)) {
			tries.push({u:src, co:1});
			if (cfg.img) tries.push({u:cfg.img + (cfg.img.indexOf('?') < 0 ? '?' : '&') + 'u=' + encodeURIComponent(src) + (cfg.nonce ? '&_wpnonce=' + cfg.nonce : ''), co:1});
			tries.push({u:src, co:0, taint:1});
		} else tries.push({u:src, co:0});
		(function next(i){
			if (i >= tries.length) { res(null); return; }
			var t = tries[i], im = new Image(), done = false, tm = 0;
			function fin(ok){ if (done) return; done = true; clearTimeout(tm); if (ok && im.naturalWidth) { if (t.taint) im.__tainted = true; res(im); } else next(i + 1); }
			if (t.co) im.crossOrigin = 'anonymous';
			im.onload = function(){ fin(true); }; im.onerror = function(){ fin(false); };
			tm = setTimeout(function(){ fin(!!im.naturalWidth); }, 15000);
			im.src = t.u;
		})(0);
	}));
}
var svgCache = {};
function fetchSvg(src){
	if (svgCache[src]) return svgCache[src];
	return (svgCache[src] = (/^data:image\/svg/i.test(src) ? Promise.resolve(decodeData(src)) : fetch(src, {mode:'cors', credentials:'omit'}).then(function(r){ return r.ok ? r.text() : null; })).catch(function(){ return null; }));
}
function decodeData(u){ var i = u.indexOf(','), meta = u.slice(0, i), d = u.slice(i + 1); try { return /;base64/i.test(meta) ? decodeURIComponent(escape(atob(d))) : decodeURIComponent(d); } catch (e) { return null; } }
function place(nw, nh, box, size, pos, s, fit){ /* -> x,y,w,h of the whole picture */
	var bw = box.w, bh = box.h, w, h;
	if (fit) { /* object-fit */
		if (fit === 'fill') return {x:box.x, y:box.y, w:bw, h:bh};
		var k = fit === 'cover' ? Math.max(bw / nw, bh / nh) : fit === 'none' ? s : Math.min(bw / nw, bh / nh);
		if (fit === 'scale-down') k = Math.min(k, s);
		w = nw * k; h = nh * k;
	} else {
		var sz = String(size || 'auto').trim();
		if (sz === 'cover' || sz === 'contain') { var k2 = sz === 'cover' ? Math.max(bw / nw, bh / nh) : Math.min(bw / nw, bh / nh); w = nw * k2; h = nh * k2; }
		else { var q = sz.split(/\s+/), a = len(q[0], bw / s), b = q.length > 1 ? len(q[1], bh / s) : null;
			if (a == null && b == null) { w = nw * s; h = nh * s; } else if (b == null) { w = a * s; h = w * nh / nw; } else if (a == null) { h = b * s; w = h * nw / nh; } else { w = a * s; h = b * s; } }
	}
	var p = String(pos || '50% 50%').trim().split(/\s+/);
	function one(v, free){ v = v || '50%'; if (v === 'left' || v === 'top') return 0; if (v === 'right' || v === 'bottom') return free; if (v === 'center') return free / 2; if (/%$/.test(v)) return parseFloat(v) / 100 * free; return (parseFloat(v) || 0) * s; }
	return {x:box.x + one(p[0], bw - w), y:box.y + one(p[1], bh - h), w:w, h:h};
}
async function resolvePending(R, win){
	var doc = win.document;
	for (var i = 0; i < R.pending.length; i++) {
		var it = R.pending[i];
		try {
			if (it.type === 'svgimg') {
				var txt = await fetchSvg(it.src), done = false;
				if (txt && /<svg[\s>]/i.test(txt)) {
					var host = doc.createElement('div'), b = it.box, s = R.s;
					host.style.cssText = 'position:absolute;left:' + (R.RR.left + b.x / s) + 'px;top:' + (R.RR.top + b.y / s) + 'px;width:' + (b.w / s) + 'px;height:' + (b.h / s) + 'px;margin:0;padding:0;border:0;transform:none;';
					host.innerHTML = txt.replace(/<\?xml[\s\S]*?\?>/, '');
					var sv = host.querySelector('svg');
					if (sv) {
						sv.setAttribute('width', '100%'); sv.setAttribute('height', '100%'); sv.style.cssText = 'display:block;width:100%;height:100%;';
						if (it.fit === 'fill') sv.setAttribute('preserveAspectRatio', 'none');
						doc.body.appendChild(host);
						var items = R.svgItems(sv, it.m || IM, 1);
						host.remove();
						if (items.length) { it.k = 'group'; it.items = items; it.clip = null; done = true; }
					}
				}
				if (done) continue;
				it.type = 'img';
			}
			var im = await loadImg(it.src);
			if (!im) { it.k = 'skip'; continue; }
			var pl = it.fit && !it.size ? place(im.naturalWidth, im.naturalHeight, it.box, null, it.pos, it.unit ? 1 : R.s, it.type === 'bg' ? null : it.fit) : place(im.naturalWidth, im.naturalHeight, it.box, it.size, it.pos, R.s, null);
			it.k = 'image'; it.img = im; it.x = pl.x; it.y = pl.y; it.w = pl.w; it.h = pl.h;
		} catch (e) { it.k = 'skip'; }
	}
}
/* ===== DSVX part C: picture data, SVG writer, EPS writer ===== */
function isOpaqueSrc(src){ return /^data:image\/jpe?g/i.test(src) || /\.(jpe?g)(\?|#|$)/i.test(src); }
var encCache = {};
async function encodeImages(R){
	var list = [];
	(function f(items){ items.forEach(function(it){ if (it.k === 'group') f(it.items); else if (it.k === 'image') list.push(it); }); })([].concat.apply([], R.layers.map(function(L){ return L.items; })));
	for (var i = 0; i < list.length; i++) {
		var it = list[i], im = it.img, k = it.m ? mdet(it.m) : 1;
		var tw = Math.max(8, Math.min(im.naturalWidth, Math.round(it.w * k * 2.2), 2600)), th = Math.max(8, Math.round(tw * im.naturalHeight / im.naturalWidth));
		if (th > 2600) { th = 2600; tw = Math.max(8, Math.round(th * im.naturalWidth / im.naturalHeight)); }
		var key = it.src + '|' + tw + 'x' + th;
		if (!encCache[key]) {
			var e = {w:tw, h:th, canvas:null, data:null, opaque:true};
			if (!im.__tainted) {
				try {
					var cv = document.createElement('canvas'); cv.width = tw; cv.height = th;
					var x = cv.getContext('2d', {willReadFrequently:true}); x.imageSmoothingQuality = 'high'; x.drawImage(im, 0, 0, tw, th);
					var px = x.getImageData(0, 0, tw, th).data, op = true;
					if (!isOpaqueSrc(it.src)) for (var p = 3; p < px.length; p += 4) if (px[p] < 250) { op = false; break; }
					e.canvas = cv; e.opaque = op; e.data = cv.toDataURL(op ? 'image/jpeg' : 'image/png', 0.9);
				} catch (er) { e.canvas = null; }
			}
			if (!e.data) e.data = it.src;
			encCache[key] = e;
		}
		it.enc = encCache[key];
	}
}

/* ---------- SVG ---------- */
function xe(s){ return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
function psName(family, wt, italic){
	var f = String(family || 'Helvetica').replace(/["']/g, '').split(',')[0].trim();
	var base = {'arial':'Helvetica', 'helvetica':'Helvetica', 'sans-serif':'Helvetica', 'system-ui':'Helvetica', 'serif':'Times', 'times new roman':'Times', 'times':'Times', 'courier new':'Courier', 'courier':'Courier', 'monospace':'Courier'}[f.toLowerCase()];
	var w = +wt || 400;
	if (base === 'Helvetica' || base === 'Courier') return base + (w >= 600 ? '-Bold' : '') + (italic ? (w >= 600 ? 'Oblique' : '-Oblique') : '');
	if (base === 'Times') return 'Times-' + (w >= 600 ? (italic ? 'BoldItalic' : 'Bold') : (italic ? 'Italic' : 'Roman'));
	var st = w >= 850 ? 'Black' : w >= 750 ? 'ExtraBold' : w >= 650 ? 'Bold' : w >= 550 ? 'SemiBold' : w >= 450 ? 'Medium' : w >= 350 ? 'Regular' : w >= 250 ? 'Light' : w >= 150 ? 'ExtraLight' : 'Thin';
	if (italic) st = st === 'Regular' ? 'Italic' : st + 'Italic';
	return f.replace(/\s+/g, '') + '-' + st;
}
function psFallback(n){ /* the standard font used to draw the text when the real font is not installed (the font name is kept) */
	if (/^(Helvetica|Times|Courier)/.test(n)) return n;
	var b = /(Bold|Black|Heavy|SemiBold|DemiBold|ExtraBold)/.test(n), i = /(Italic|Oblique)/.test(n);
	return 'Helvetica' + (b || i ? '-' : '') + (b ? 'Bold' : '') + (i ? 'Oblique' : '');
}
function svgId(name, used){
	var n = String(name || 'Layer').replace(/[^A-Za-z0-9\-]/g, function(c){ return c === ' ' ? '_x20_' : '_x' + c.charCodeAt(0).toString(16).toUpperCase() + '_'; });
	if (!/^[A-Za-z]/.test(n)) n = 'L' + n; n = n.slice(0, 90);
	var k = n, i = 2; while (used[k]) k = n + '-' + (i++); used[k] = 1; return k;
}
function lineAnchors(runs, align){ /* a line made of one run keeps its alignment (so edits stay centred / right aligned) */
	var per = {}; runs.forEach(function(r){ per[r.line] = (per[r.line] || 0) + 1; });
	return runs.map(function(r){ if (per[r.line] !== 1 || align === 'left') return {a:'start', x:r.x}; return align === 'center' ? {a:'middle', x:r.x + r.w / 2} : {a:'end', x:r.x + r.w}; });
}
function toSVG(R, title){
	var W = R.W, H = R.H, defs = [], gid = 0, used = {}, families = Object.keys(R.fonts || {});
	function nid(p){ return 'ds' + p + (++gid); }
	function stops(st){ return st.map(function(s){ var a = s.c.a == null ? 1 : s.c.a; return '<stop offset="' + num(s.o) + '" stop-color="' + hex(s.c) + '"' + (a < 0.999 ? ' stop-opacity="' + (Math.round(a * 1000) / 1000) + '"' : '') + '/>'; }).join(''); }
	function gradRef(g){
		var id = nid('g');
		if (g.type === 'radial') defs.push('<radialGradient id="' + id + '" gradientUnits="userSpaceOnUse" cx="0" cy="0" r="1" gradientTransform="matrix(' + mStr(g.m) + ')">' + stops(g.stops) + '</radialGradient>');
		else defs.push('<linearGradient id="' + id + '" gradientUnits="userSpaceOnUse" x1="' + num(g.x1) + '" y1="' + num(g.y1) + '" x2="' + num(g.x2) + '" y2="' + num(g.y2) + '">' + stops(g.stops) + '</linearGradient>');
		return 'url(#' + id + ')';
	}
	function blurRef(b, box){ var id = nid('f'), e = b * 3 + 2; defs.push('<filter id="' + id + '" filterUnits="userSpaceOnUse" x="' + num(box.x - e) + '" y="' + num(box.y - e) + '" width="' + num(box.w + 2 * e) + '" height="' + num(box.h + 2 * e) + '" color-interpolation-filters="sRGB"><feGaussianBlur stdDeviation="' + num(b) + '"/></filter>'); return ' filter="url(#' + id + ')"'; }
	function clipRef(p){ var id = nid('c'); defs.push('<clipPath id="' + id + '" clipPathUnits="userSpaceOnUse"><path d="' + dStr(p) + '"/></clipPath>'); return ' clip-path="url(#' + id + ')"'; }
	function opA(o){ return o != null && o < 0.999 ? ' opacity="' + (Math.round(o * 1000) / 1000) + '"' : ''; }
	function itemBox(it){ if (it.k === 'shape') return pathBox(it.path); if (it.k === 'group') { var b = null; it.items.forEach(function(x){ var q = itemBox(x); if (!q) return; if (!b) b = {x:q.x, y:q.y, w:q.w, h:q.h}; else { var x1 = Math.max(b.x + b.w, q.x + q.w), y1 = Math.max(b.y + b.h, q.y + q.h); b.x = Math.min(b.x, q.x); b.y = Math.min(b.y, q.y); b.w = x1 - b.x; b.h = y1 - b.y; } }); return b; }
		if (it.k === 'image') return pathBox(mapP(it.clip || rectP(it.x, it.y, it.w, it.h), it.m || IM)); if (it.k === 'text') { var bb = null; it.runs.forEach(function(r){ var q = pathBox(mapP(rectP(r.x, r.y - r.size, r.w, r.size * 1.3), it.m || IM)); if (!bb) bb = q; else { var x1 = Math.max(bb.x + bb.w, q.x + q.w), y1 = Math.max(bb.y + bb.h, q.y + q.h); bb.x = Math.min(bb.x, q.x); bb.y = Math.min(bb.y, q.y); bb.w = x1 - bb.x; bb.h = y1 - bb.y; } }); return bb; } return null; }
	function item(it){
		if (!it || it.k === 'skip' || it.k === 'pending') return '';
		var op = opA(it.op);
		if (it.k === 'shape') {
			var s = '<path d="' + dStr(it.path) + '"';
			if (it.fill && it.fill.grad) s += ' fill="' + gradRef(it.fill.grad) + '"';
			else if (it.fill && it.fill.c) { s += ' fill="' + hex(it.fill.c) + '"'; if (it.fill.c.a < 0.999) s += ' fill-opacity="' + (Math.round(it.fill.c.a * 1000) / 1000) + '"'; }
			else s += ' fill="none"';
			if (it.fillRule) s += ' fill-rule="evenodd"';
			if (it.stroke && it.stroke.c) {
				s += ' stroke="' + hex(it.stroke.c) + '" stroke-width="' + num(it.stroke.w) + '"';
				if (it.stroke.c.a < 0.999) s += ' stroke-opacity="' + (Math.round(it.stroke.c.a * 1000) / 1000) + '"';
				if (it.stroke.dash) s += ' stroke-dasharray="' + it.stroke.dash.map(num).join(' ') + '"';
				if (it.stroke.cap && it.stroke.cap !== 'butt') s += ' stroke-linecap="' + it.stroke.cap + '"';
				s += ' stroke-linejoin="' + (it.stroke.join && it.stroke.join !== 'miter' ? it.stroke.join : 'miter') + '"';
			}
			if (it.blur > 0.3) s += blurRef(it.blur, pathBox(it.path));
			return s + op + '/>';
		}
		if (it.k === 'text') {
			var tr = it.m ? ' transform="matrix(' + mStr(it.m) + ')"' : '', an = lineAnchors(it.runs, it.align);
			var t = '<text xml:space="preserve" style="white-space:pre"' + tr + op + '>';
			it.runs.forEach(function(r, i){
				var stack = String(r.stack || r.family).replace(/"/g, "'"), c = col(r.color) || {r:0, g:0, b:0, a:1};
				t += '<tspan x="' + num(an[i].x) + '" y="' + num(r.y) + '"' + (an[i].a !== 'start' ? ' text-anchor="' + an[i].a + '"' : '') +
					' font-family="' + xe("'" + psName(r.family, r.weight, r.italic) + "', " + stack) + '" font-size="' + num(r.size) + '" font-weight="' + r.weight + '"' + (r.italic ? ' font-style="italic"' : '') +
					(Math.abs(r.ls) > 0.01 ? ' letter-spacing="' + num(r.ls) + '"' : '') + ' fill="' + hex(c) + '"' + (c.a < 0.999 ? ' fill-opacity="' + (Math.round(c.a * 1000) / 1000) + '"' : '') + '>' + xe(r.s) + '</tspan>';
			});
			return t + '</text>';
		}
		if (it.k === 'image') {
			var src = it.enc ? it.enc.data : it.src;
			return '<g' + (it.m ? ' transform="matrix(' + mStr(it.m) + ')"' : '') + op + '><g' + (it.clip ? clipRef(it.clip) : '') + '><image x="' + num(it.x) + '" y="' + num(it.y) + '" width="' + num(it.w) + '" height="' + num(it.h) + '" preserveAspectRatio="none" xlink:href="' + xe(src) + '"/></g></g>';
		}
		if (it.k === 'group') {
			var inner = it.items.map(item).join(''); if (!inner) return '';
			var a = (it.clip ? clipRef(it.clip) : '') + op;
			if (it.blur > 0.3) { var bb = itemBox(it); if (bb) a += blurRef(it.blur, bb); }
			return a ? '<g' + a + '>' + inner + '</g>' : inner;
		}
		return '';
	}
	var body = R.layers.map(function(L){
		var inner = L.items.map(item).join('\n'); if (!inner) return '';
		var b = itemBox({k:'group', items:L.items}), out = b && (b.x < -0.5 || b.y < -0.5 || b.x + b.w > W + 0.5 || b.y + b.h > H + 0.5);
		var id = svgId(L.name, used);
		return '<g id="' + id + '" data-name="' + xe(L.name) + '"' + (out ? clipRef(rectP(0, 0, W, H)) : '') + '>\n' + inner + '\n</g>';
	}).filter(Boolean).join('\n');
	var gf = families.filter(function(f){ return !/^(arial|helvetica|times new roman|georgia|verdana|courier new|sans-serif|serif|system-ui|monospace)$/i.test(f); });
	var fontCss = gf.length ? '<style>@import url(\'https://fonts.googleapis.com/css2?' + gf.map(function(f){ var ws = Object.keys(R.fonts[f]).map(function(w){ return parseInt(w, 10); }).filter(function(v, i, a){ return a.indexOf(v) === i; }).sort(function(a, b){ return a - b; }); return 'family=' + encodeURIComponent(f).replace(/%20/g, '+') + ':wght@' + ws.join(';'); }).join('&amp;') + '&amp;display=swap\');</style>' : '';
	return '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '">\n<title>' + xe(title || 'Slide') + '</title>\n' +
		'<defs>' + fontCss + defs.join('\n') + '</defs>\n' + body + '\n</svg>\n';
}

/* ---------- EPS ---------- */
var ENC = (function(){ var m = {}, i; for (i = 32; i < 127; i++) m[i] = i;
	var hi = {0x20AC:128, 0x201A:130, 0x0192:131, 0x201E:132, 0x2026:133, 0x2020:134, 0x2021:135, 0x02C6:136, 0x2030:137, 0x0160:138, 0x2039:139, 0x0152:140, 0x017D:142, 0x2018:145, 0x2019:146, 0x201C:147, 0x201D:148, 0x2022:149, 0x2013:150, 0x2014:151, 0x02DC:152, 0x2122:153, 0x0161:154, 0x203A:155, 0x0153:156, 0x017E:158, 0x0178:159};
	for (var k in hi) m[k] = hi[k]; for (i = 160; i < 256; i++) m[i] = i; return m; })();
var GLYPHS = '/Euro/.notdef/quotesinglbase/florin/quotedblbase/ellipsis/dagger/daggerdbl/circumflex/perthousand/Scaron/guilsinglleft/OE/.notdef/Zcaron/.notdef/.notdef/quoteleft/quoteright/quotedblleft/quotedblright/bullet/endash/emdash/tilde/trademark/scaron/guilsinglright/oe/.notdef/zcaron/Ydieresis';
function psStr(s){
	var o = '(';
	Array.from(String(s)).forEach(function(ch){
		var u = ch.codePointAt(0), c = ENC[u];
		if (c == null) { if (u === 0x2212 || u === 0x2010 || u === 0x2011) c = 45; else if (u === 0x00AD || (u >= 0x200B && u <= 0x200D) || u === 0xFEFF || u === 0x2060) return; else if (u === 0x2012) c = 150; else if (u === 0x2015) c = 151; else if (u === 0x2032) c = 39; else if (u === 0x2033) c = 34; else if (u === 0x00A0 || u === 0x2009 || u === 0x202F || u === 0x2007) c = 32; else if (u === 0x2192) { o += '->'; return; } else if (u === 0x2190) { o += '<-'; return; } else if (u === 0x2713 || u === 0x2714) { o += 'v'; return; } else if (u === 0x25CF || u === 0x25AA || u === 0x25A0) c = 149; else c = 63; }
		if (c === 40 || c === 41 || c === 92) o += '\\' + String.fromCharCode(c);
		else if (c < 32 || c > 126) o += '\\' + ('00' + c.toString(8)).slice(-3);
		else o += String.fromCharCode(c);
	});
	return o + ')';
}
function a85(bytes){
	var out = [], line = '', n = bytes.length, i = 0;
	function put(s){ line += s; if (line.length >= 76) { out.push(line.slice(0, 76)); line = line.slice(76); } }
	for (; i + 4 <= n; i += 4) {
		var v = ((bytes[i] << 24) >>> 0) + (bytes[i + 1] << 16) + (bytes[i + 2] << 8) + bytes[i + 3];
		if (v === 0) { put('z'); continue; }
		var c5 = v % 85; v = (v - c5) / 85; var c4 = v % 85; v = (v - c4) / 85; var c3 = v % 85; v = (v - c3) / 85; var c2 = v % 85; v = (v - c2) / 85;
		put(String.fromCharCode(33 + v, 33 + c2, 33 + c3, 33 + c4, 33 + c5));
	}
	var r = n - i;
	if (r) { var v2 = 0; for (var j = 0; j < 4; j++) v2 = v2 * 256 + (j < r ? bytes[i + j] : 0); var d = []; for (var k = 0; k < 5; k++) { d.unshift(33 + v2 % 85); v2 = Math.floor(v2 / 85); } put(String.fromCharCode.apply(null, d.slice(0, r + 1))); }
	out.push(line + '~>');
	return out.join('\n');
}
async function deflate(bytes){
	if (typeof CompressionStream === 'undefined') return null;
	try { var cs = new CompressionStream('deflate'), w = cs.writable.getWriter(); w.write(bytes); w.close(); return new Uint8Array(await new Response(cs.readable).arrayBuffer()); } catch (e) { return null; }
}
function jpegBytes(cv, q){ /* synchronous encode: toBlob is throttled while the tab is in the background */
	try { var u = cv.toDataURL('image/jpeg', q || 0.9), b = atob(u.slice(u.indexOf(',') + 1)), n = b.length, a = new Uint8Array(n); for (var i = 0; i < n; i++) a[i] = b.charCodeAt(i); return Promise.resolve(n > 100 ? a : null); } catch (e) { return Promise.resolve(null); }
}
function P2D(path){ return new Path2D(dStr(path)); }

async function toEPS(R, title){
	var W = R.W, H = R.H, S = 0.75, SC = 1.5, fonts = {}, body = [];
	var cv = document.createElement('canvas'); cv.width = Math.round(W * SC); cv.height = Math.round(H * SC);
	var cx = cv.getContext('2d', {willReadFrequently:true}); cx.fillStyle = hex(R.bg); cx.fillRect(0, 0, cv.width, cv.height);
	var leaves = [];
	function flat(items, clips, op, blur, layer){
		items.forEach(function(it){
			if (!it || it.k === 'skip' || it.k === 'pending') return;
			var o = op * (it.op == null ? 1 : it.op);
			if (it.k === 'group') flat(it.items, it.clip ? clips.concat([it.clip]) : clips, o, Math.max(blur, it.blur || 0), layer);
			else leaves.push({it:it, clips:clips, op:o, blur:Math.max(blur, it.blur || 0), layer:layer});
		});
	}
	R.layers.forEach(function(Ly){ flat(Ly.items, [], 1, 0, Ly.name); });

	function c3(c){ return num(c.r / 255) + ' ' + num(c.g / 255) + ' ' + num(c.b / 255); }
	function pth(path){ return path.map(function(s){ if (s[0] === 'M') return num(s[1]) + ' ' + num(s[2]) + ' m'; if (s[0] === 'L') return num(s[1]) + ' ' + num(s[2]) + ' l'; if (s[0] === 'C') return s.slice(1).map(num).join(' ') + ' c'; return 'h'; }).join(' '); }
	function clipS(clips){ return clips.map(function(p){ return 'n ' + pth(p) + ' W n'; }).join('\n'); }
	function shadeS(g, under, op){
		var cs = g.stops.map(function(s, i){ return over(withA(s.c, op), under[i] || under.avg); }), fns = [], bounds = [], enc = [];
		for (var i = 0; i < g.stops.length - 1; i++) { fns.push('<< /FunctionType 2 /Domain [0 1] /C0 [' + c3(cs[i]) + '] /C1 [' + c3(cs[i + 1]) + '] /N 1 >>'); if (i) bounds.push(num(Math.max(g.stops[i].o, (bounds.length ? +bounds[bounds.length - 1] : 0) + 0.0001))); enc.push('0 1'); }
		var fn = fns.length === 1 ? fns[0] : '<< /FunctionType 3 /Domain [0 1] /Functions [' + fns.join(' ') + '] /Bounds [' + bounds.join(' ') + '] /Encode [' + enc.join(' ') + '] >>';
		if (g.type === 'radial') return 'gsave [' + mStr(g.m) + '] concat << /ShadingType 3 /ColorSpace /DeviceRGB /Coords [0 0 0 0 0 1] /Function ' + fn + ' /Extend [true true] >> shfill grestore';
		return '<< /ShadingType 2 /ColorSpace /DeviceRGB /Coords [' + [g.x1, g.y1, g.x2, g.y2].map(num).join(' ') + '] /Function ' + fn + ' /Extend [true true] >> shfill';
	}
	/* ---- the raster copy: answers "what colour is under this?" and makes patches ---- */
	function ptIn(L, x, y){ var X = x * SC, Y = y * SC; if (X < 0 || Y < 0 || X >= cv.width || Y >= cv.height) return false;
		cx.setTransform(SC, 0, 0, SC, 0, 0); for (var i = 0; i < L.clips.length; i++) if (!cx.isPointInPath(L.clipsP[i], X, Y)) return false; return true; }
	function px(x, y){ var d = cx.getImageData(Math.floor(x * SC), Math.floor(y * SC), 1, 1).data; return {r:d[0], g:d[1], b:d[2], a:1}; }
	function avgC(list){ if (!list.length) return null; var a = {r:0, g:0, b:0, a:1}; list.forEach(function(c){ a.r += c.r; a.g += c.g; a.b += c.b; }); a.r /= list.length; a.g /= list.length; a.b /= list.length; return a; }
	var imgs = [];
	function inImg(x, y){ cx.setTransform(SC, 0, 0, SC, 0, 0); for (var i = 0; i < imgs.length; i++) if (cx.isPointInPath(imgs[i].p2d, x * SC, y * SC)) return true; return false; }
	function under(L){
		var it = L.it, pts = [];
		if (it.k === 'shape') {
			var b = pathBox(it.path); if (!b) return {avg:R.bg};
			var p2 = L.p2d || (L.p2d = P2D(it.path)); if (it.stroke) cx.lineWidth = it.stroke.w;
			for (var j = 0; j < 7; j++) for (var i = 0; i < 7; i++) { var x = b.x + b.w * (i + 0.5) / 7, y = b.y + b.h * (j + 0.5) / 7; cx.setTransform(SC, 0, 0, SC, 0, 0); if ((it.fill ? cx.isPointInPath(p2, x * SC, y * SC, it.fillRule || 'nonzero') : cx.isPointInStroke(p2, x * SC, y * SC)) && ptIn(L, x, y) && !inImg(x, y)) pts.push([x, y]); }
			if (!pts.length) { var c0 = [b.x + b.w / 2, b.y + b.h / 2]; if (ptIn(L, c0[0], c0[1])) pts.push(c0); }
		} else if (it.k === 'text') {
			it.runs.forEach(function(r){ var q = tp(it.m || IM, r.x + r.w / 2, r.y - r.size * 0.3); if (ptIn(L, q[0], q[1])) pts.push(q); });
		}
		var cols = pts.map(function(p){ return px(p[0], p[1]); }), avg = avgC(cols) || R.bg, res = {avg:avg, varied:false};
		if (it.k === 'shape' && cols.length > 2) { var dev = 0; cols.forEach(function(c){ dev = Math.max(dev, Math.abs(c.r - avg.r), Math.abs(c.g - avg.g), Math.abs(c.b - avg.b)); }); res.varied = dev > 14; }
		if (it.k === 'shape' && it.fill && it.fill.grad) {
			var g = it.fill.grad, p2d = L.p2d;
			g.stops.forEach(function(s, i){
				var cand = [];
				if (g.type === 'radial') for (var k = 0; k < 8; k++) { var a = k * Math.PI / 4; cand.push(tp(g.m, s.o * Math.cos(a), s.o * Math.sin(a))); }
				else cand.push([g.x1 + (g.x2 - g.x1) * s.o, g.y1 + (g.y2 - g.y1) * s.o]);
				var ok = cand.filter(function(q){ cx.setTransform(SC, 0, 0, SC, 0, 0); return cx.isPointInPath(p2d, q[0] * SC, q[1] * SC) && ptIn(L, q[0], q[1]) && !inImg(q[0], q[1]); });
				res[i] = avgC(ok.map(function(q){ return px(q[0], q[1]); })) || avg;
			});
		}
		return res;
	}
	function draw(L){
		var it = L.it;
		cx.save(); cx.setTransform(SC, 0, 0, SC, 0, 0);
		L.clipsP.forEach(function(p){ cx.clip(p); });
		if (L.blur > 0.3) cx.filter = 'blur(' + (L.blur * SC) + 'px)';
		cx.globalAlpha = clamp(L.op, 0, 1);
		if (it.k === 'shape') {
			var p = L.p2d || (L.p2d = P2D(it.path));
			if (it.fill && it.fill.c) { cx.fillStyle = rgbaS(it.fill.c); cx.fill(p, it.fillRule || 'nonzero'); }
			else if (it.fill && it.fill.grad) {
				var g = it.fill.grad;
				if (g.type === 'radial') { cx.save(); cx.clip(p, it.fillRule || 'nonzero'); cx.transform(g.m.a, g.m.b, g.m.c, g.m.d, g.m.e, g.m.f);
					var rg = cx.createRadialGradient(0, 0, 0, 0, 0, 1); g.stops.forEach(function(s){ rg.addColorStop(clamp(s.o, 0, 1), rgbaS(s.c)); }); cx.fillStyle = rg;
					var bb = pathBox(mapP(pathBox(it.path) ? rectP(pathBox(it.path).x, pathBox(it.path).y, pathBox(it.path).w, pathBox(it.path).h) : rectP(0, 0, W, H), mInv(g.m))); if (bb) cx.fillRect(bb.x - 1, bb.y - 1, bb.w + 2, bb.h + 2); cx.restore(); }
				else if (g.stops.length > 12) { /* many hard stops (stripes): band by band, so edges stay sharp */
					cx.save(); cx.clip(p, it.fillRule || 'nonzero');
					var dx = g.x2 - g.x1, dy = g.y2 - g.y1, ln = Math.sqrt(dx * dx + dy * dy) || 1, nx = -dy / ln * 1e4, ny = dx / ln * 1e4;
					var band = function(o0, o1, c0, c1){ var ax = g.x1 + dx * o0, ay = g.y1 + dy * o0, bx = g.x1 + dx * o1, by = g.y1 + dy * o1;
						cx.beginPath(); cx.moveTo(ax + nx, ay + ny); cx.lineTo(bx + nx, by + ny); cx.lineTo(bx - nx, by - ny); cx.lineTo(ax - nx, ay - ny); cx.closePath();
						if (o1 - o0 > 0.0005 && rgbaS(c0) !== rgbaS(c1)) { var bg2 = cx.createLinearGradient(ax, ay, bx, by); bg2.addColorStop(0, rgbaS(c0)); bg2.addColorStop(1, rgbaS(c1)); cx.fillStyle = bg2; } else cx.fillStyle = rgbaS(c0); cx.fill(); };
					band(-50, 0, g.stops[0].c, g.stops[0].c);
					for (var bi = 0; bi < g.stops.length - 1; bi++) if (g.stops[bi + 1].o > g.stops[bi].o + 1e-7) band(g.stops[bi].o, g.stops[bi + 1].o, g.stops[bi].c, g.stops[bi + 1].c);
					band(1, 50, g.stops[g.stops.length - 1].c, g.stops[g.stops.length - 1].c);
					cx.restore(); }
				else { var lg = cx.createLinearGradient(g.x1, g.y1, g.x2, g.y2); g.stops.forEach(function(s){ lg.addColorStop(clamp(s.o, 0, 1), rgbaS(s.c)); }); cx.fillStyle = lg; cx.fill(p, it.fillRule || 'nonzero'); }
			}
			if (it.stroke && it.stroke.c) { cx.strokeStyle = rgbaS(it.stroke.c); cx.lineWidth = it.stroke.w; cx.lineCap = it.stroke.cap || 'butt'; cx.lineJoin = it.stroke.join || 'miter'; cx.setLineDash(it.stroke.dash || []); cx.stroke(p); }
		} else if (it.k === 'text') {
			if (it.m) cx.transform(it.m.a, it.m.b, it.m.c, it.m.d, it.m.e, it.m.f);
			var an = lineAnchors(it.runs, it.align);
			it.runs.forEach(function(r, i){ cx.font = (r.italic ? 'italic ' : '') + r.weight + ' ' + r.size + 'px ' + (r.stack || r.family); try { cx.letterSpacing = (r.ls || 0) + 'px'; } catch (e) {}
				cx.textAlign = an[i].a === 'middle' ? 'center' : an[i].a === 'end' ? 'right' : 'left'; cx.textBaseline = 'alphabetic'; cx.fillStyle = rgbaS(r.color); cx.fillText(r.s, an[i].x, r.y); });
		} else if (it.k === 'image') {
			if (it.m) cx.transform(it.m.a, it.m.b, it.m.c, it.m.d, it.m.e, it.m.f);
			if (it.clip) cx.clip(P2D(it.clip));
			try { if (it.enc && it.enc.canvas) cx.drawImage(it.enc.canvas, it.x, it.y, it.w, it.h); else if (it.img && !it.img.__tainted) cx.drawImage(it.img, it.x, it.y, it.w, it.h); } catch (e) {}
		}
		cx.restore();
	}
	async function patch(box, clipsS, label){ /* the raster copy of a region, placed and clipped */
		var x0 = Math.max(0, Math.floor(box.x * SC)), y0 = Math.max(0, Math.floor(box.y * SC)), x1 = Math.min(cv.width, Math.ceil((box.x + box.w) * SC)), y1 = Math.min(cv.height, Math.ceil((box.y + box.h) * SC));
		if (x1 - x0 < 1 || y1 - y0 < 1) return '';
		var pc = document.createElement('canvas'); pc.width = x1 - x0; pc.height = y1 - y0; pc.getContext('2d').drawImage(cv, x0, y0, pc.width, pc.height, 0, 0, pc.width, pc.height);
		var jb = await jpegBytes(pc, 0.92); if (!jb) return '';
		return '%' + (label || 'patch') + '\ngsave ' + clipsS + '\n' + num(x0 / SC) + ' ' + num(y0 / SC) + ' translate ' + num(pc.width / SC) + ' ' + num(pc.height / SC) + ' scale\n' +
			'<< /ImageType 1 /Width ' + pc.width + ' /Height ' + pc.height + ' /BitsPerComponent 8 /Decode [0 1 0 1 0 1] /ImageMatrix [' + pc.width + ' 0 0 ' + pc.height + ' 0 0] /DataSource currentfile /ASCII85Decode filter /DCTDecode filter >> /DeviceRGB setcolorspace image\n' + a85(jb) + '\ngrestore';
	}
	async function imageS(L){
		var it = L.it, e = it.enc; if (!e || !e.canvas) return '';
		var w = e.w, h = e.h, s = 'gsave ' + clipS(L.clips) + '\n' + (it.m ? '[' + mStr(it.m) + '] concat ' : '') + (it.clip ? 'n ' + pth(it.clip) + ' W n ' : '') + num(it.x) + ' ' + num(it.y) + ' translate ' + num(it.w) + ' ' + num(it.h) + ' scale /DeviceRGB setcolorspace\n';
		if (e.opaque) { var jb = await jpegBytes(e.canvas, 0.9); if (!jb) return ''; return s + '<< /ImageType 1 /Width ' + w + ' /Height ' + h + ' /BitsPerComponent 8 /Decode [0 1 0 1 0 1] /ImageMatrix [' + w + ' 0 0 ' + h + ' 0 0] /DataSource currentfile /ASCII85Decode filter /DCTDecode filter >> image\n' + a85(jb) + '\ngrestore'; }
		var px4 = e.canvas.getContext('2d', {willReadFrequently:true}).getImageData(0, 0, w, h).data, n = w * h, buf = new Uint8Array(n * 4);
		for (var p = 0, q = 0; p < n; p++) { buf[q++] = px4[p * 4 + 3] >= 128 ? 255 : 0; buf[q++] = px4[p * 4]; buf[q++] = px4[p * 4 + 1]; buf[q++] = px4[p * 4 + 2]; }
		var z = await deflate(buf), src = 'currentfile /ASCII85Decode filter' + (z ? ' /FlateDecode filter' : '');
		return s + '<< /ImageType 3 /InterleaveType 1 /DataDict << /ImageType 1 /Width ' + w + ' /Height ' + h + ' /BitsPerComponent 8 /Decode [0 1 0 1 0 1] /ImageMatrix [' + w + ' 0 0 ' + h + ' 0 0] /DataSource ' + src + ' >> /MaskDict << /ImageType 1 /Width ' + w + ' /Height ' + h + ' /BitsPerComponent 8 /Decode [1 0] /ImageMatrix [' + w + ' 0 0 ' + h + ' 0 0] >> >> image\n' + a85(z || buf) + '\ngrestore';
	}
	function trans(L){ var it = L.it; if (L.op < 0.995) return true;
		if (it.k === 'shape') return !!((it.fill && (it.fill.c ? it.fill.c.a < 0.995 : !gradOpaque(it.fill.grad))) || (it.stroke && it.stroke.c && it.stroke.c.a < 0.995));
		if (it.k === 'text') return it.runs.some(function(r){ return (col(r.color) || {a:1}).a < 0.995; });
		return false; }
	function leafBox(L){ var it = L.it; if (it.k === 'shape') { var b = pathBox(it.path); if (it.stroke && b) { var e = it.stroke.w / 2; b = {x:b.x - e, y:b.y - e, w:b.w + 2 * e, h:b.h + 2 * e}; } return b; }
		if (it.k === 'image') return pathBox(mapP(it.clip || rectP(it.x, it.y, it.w, it.h), it.m || IM));
		if (it.k === 'text') { var bb = null; it.runs.forEach(function(r){ var q = pathBox(mapP(rectP(r.x - r.size * 0.2, r.y - r.size * 1.05, r.w + r.size * 0.6, r.size * 1.4), it.m || IM)); if (!bb) bb = q; else { var x1 = Math.max(bb.x + bb.w, q.x + q.w), y1 = Math.max(bb.y + bb.h, q.y + q.h); bb.x = Math.min(bb.x, q.x); bb.y = Math.min(bb.y, q.y); bb.w = x1 - bb.x; bb.h = y1 - bb.y; } }); return bb; }
		return null; }
	function inter(a, b){ if (!a || !b) return null; var x = Math.max(a.x, b.x), y = Math.max(a.y, b.y), x1 = Math.min(a.x + a.w, b.x + b.w), y1 = Math.min(a.y + a.h, b.y + b.h); return x1 > x && y1 > y ? {x:x, y:y, w:x1 - x, h:y1 - y} : null; }
	function clipBox(L, b){ L.clips.forEach(function(c){ b = inter(b, pathBox(c)); }); return inter(b, {x:0, y:0, w:W, h:H}); }

	var lastLayer = null;
	for (var li = 0; li < leaves.length; li++) {
		var L = leaves[li], it = L.it, part = [];
		L.clipsP = L.clips.map(P2D);
		var box = clipBox(L, leafBox(L)); if (!box) continue;
		if (L.layer !== lastLayer) { lastLayer = L.layer; body.push('%%-- ' + String(lastLayer).replace(/[\r\n]/g, ' ')); }
		var tr = trans(L), U = null;
		if (tr && !(L.blur > 0.3) && it.k !== 'image') U = under(L);
		draw(L);
		if (L.blur > 0.3 || (it.k === 'image' && (L.op < 0.995 || !it.enc || !it.enc.canvas))) {
			var e = (L.blur || 0) * 3; body.push(await patch(clipBox(L, {x:box.x - e, y:box.y - e, w:box.w + 2 * e, h:box.h + 2 * e}) || box, clipS(L.clips), L.blur ? 'soft shadow / blur' : 'faded picture'));
			continue;
		}
		if (it.k === 'image') {
			body.push(await imageS(L));
			var ic = mapP(it.clip || rectP(it.x, it.y, it.w, it.h), it.m || IM); imgs.push({p2d:P2D(ic), path:ic, box:box, clips:L.clips});
			continue;
		}
		if (U && U.varied) { /* see-through shape over a busy area: a picture of that area keeps the exact look */
			body.push(await patch(box, clipS(L.clips) + (it.fill ? '\nn ' + pth(it.path) + (it.fillRule === 'evenodd' ? ' eoclip' : ' W') + ' n' : ''), 'see-through shape'));
			continue;
		}
		var hits = tr && it.k === 'shape' && it.fill ? imgs.filter(function(im){ return boxHit(im.box, box); }) : [];
		var excl = hits.length ? 'n 0 0 m ' + W + ' 0 l ' + W + ' ' + H + ' l 0 ' + H + ' l h ' + hits.map(function(im){ return pth(im.path); }).join(' ') + ' eoclip n\n' : '';
		var op = L.op, pre = 'gsave ' + clipS(L.clips) + '\n' + excl;
		if (it.k === 'shape') {
			var fr = it.fillRule === 'evenodd' ? ' eofill' : ' f';
			if (it.fill && it.fill.c) { var fc = tr ? over(withA(it.fill.c, op), U.avg) : it.fill.c; part.push(c3(fc) + ' rg n ' + pth(it.path) + fr); }
			else if (it.fill && it.fill.grad) part.push('gsave n ' + pth(it.path) + (it.fillRule === 'evenodd' ? ' eoclip' : ' W') + ' n ' + shadeS(it.fill.grad, U || {avg:R.bg}, op) + ' grestore');
			if (it.stroke && it.stroke.c) { var sc = tr ? over(withA(it.stroke.c, op), U.avg) : it.stroke.c;
				part.push('gsave ' + c3(sc) + ' RG ' + num(it.stroke.w) + ' w ' + ({round:1, square:2}[it.stroke.cap] || 0) + ' J ' + ({round:1, bevel:2}[it.stroke.join] || 0) + ' j [' + (it.stroke.dash || []).map(num).join(' ') + '] 0 d n ' + pth(it.path) + ' S grestore'); }
			if (part.length) body.push(pre + part.join('\n') + '\ngrestore');
			for (var h = 0; h < hits.length; h++) { var hb = inter(box, hits[h].box); if (hb) body.push(await patch(hb, clipS(L.clips) + '\n' + clipS(hits[h].clips) + '\nn ' + pth(hits[h].path) + ' W n n ' + pth(it.path) + (it.fillRule === 'evenodd' ? ' eoclip' : ' W') + ' n', 'overlay on picture')); }
			continue;
		}
		if (it.k === 'text') {
			var an = lineAnchors(it.runs, it.align), t = pre + (it.m ? '[' + mStr(it.m) + '] concat\n' : '');
			it.runs.forEach(function(r, i){
				var fn = psName(r.family, r.weight, r.italic); fonts[fn] = 1;
				var c = tr ? over(withA(r.color, op), U.avg) : col(r.color), str = psStr(r.s), ls = num(r.ls || 0);
				var xe2 = an[i].a === 'middle' ? num(an[i].x) + ' ' + str + ' ' + ls + ' DSw 2 div sub' : an[i].a === 'end' ? num(an[i].x) + ' ' + str + ' ' + ls + ' DSw sub' : num(an[i].x);
				t += '/' + fn + '-DS ' + num(r.size) + ' selectfont ' + c3(c) + ' rg ' + xe2 + ' ' + num(r.y) + ' ' + ls + ' ' + str + ' DSt\n';
			});
			body.push(t + 'grestore');
		}
	}
	var fl = Object.keys(fonts), Wp = Math.round(W * S), Hp = Math.round(H * S);
	var head = ['%!PS-Adobe-3.0 EPSF-3.0', '%%BoundingBox: 0 0 ' + Wp + ' ' + Hp, '%%HiResBoundingBox: 0 0 ' + num(W * S) + ' ' + num(H * S), '%%Creator: DesignStager', '%%Title: ' + String(title || 'Slide').replace(/[\r\n]/g, ' ').replace(/[^\x20-\x7e]/g, '?'),
		'%%CreationDate: ' + new Date().toISOString().slice(0, 10), '%%LanguageLevel: 3', '%%DocumentData: Clean7Bit'];
	if (fl.length) head.push('%%DocumentNeededResources: font ' + fl[0]); fl.slice(1).forEach(function(f){ head.push('%%+ font ' + f); });
	head.push('%%EndComments', '%%BeginProlog',
		'/DSdict 40 dict def DSdict begin',
		'/m /moveto load def /l /lineto load def /c /curveto load def /h /closepath load def /n /newpath load def /f /fill load def /W /clip load def /S /stroke load def',
		'/w /setlinewidth load def /J /setlinecap load def /j /setlinejoin load def /d /setdash load def /rg /setrgbcolor load def /RG /setrgbcolor load def',
		'/DSenc ISOLatin1Encoding dup length array copy def DSenc 128 [' + GLYPHS + '] putinterval DSenc 39 /quotesingle put DSenc 96 /grave put DSenc 45 /hyphen put',
		'/DSfont { /DSf exch def /DSb exch def /DSn exch def DSb /Font resourcestatus { pop pop DSb findfont } { DSf findfont } ifelse dup length dict begin { 1 index /FID ne { def } { pop pop } ifelse } forall /Encoding DSenc def /FontName DSb def currentdict end DSn exch definefont pop } bind def',
		'/DSw { /DSl2 exch def dup length DSl2 mul exch stringwidth pop add } bind def',
		'/DSt { /DSs exch def /DSl exch def moveto gsave 1 -1 scale DSl 0 DSs ashow grestore } bind def',
		'end', '%%EndProlog', '%%BeginSetup', 'DSdict begin');
	fl.forEach(function(f){ head.push('%%IncludeResource: font ' + f, '/' + f + '-DS /' + f + ' /' + psFallback(f) + ' DSfont'); });
	head.push('end', '%%EndSetup', 'DSdict begin', 'gsave 0 ' + num(H * S) + ' translate ' + S + ' ' + (-S) + ' scale', 'n 0 0 m ' + W + ' 0 l ' + W + ' ' + H + ' l 0 ' + H + ' l h W n');
	return head.join('\n') + '\n' + body.filter(Boolean).join('\n') + '\ngrestore\nend\nshowpage\n%%Trailer\n%%EOF\n';
}
/* ===== DSVX part D: the download (one SVG + one EPS per slide, in a .zip) and the menu entry ===== */
var DSVX = window.DSVX = {read:read, resolvePending:resolvePending, encodeImages:encodeImages, toSVG:toSVG, toEPS:toEPS, version:'1.0'};
var ZIP = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', LONG = 1280, busy = false, zipP = null;
var STRIP = '.dsp-vp-ihover,.dsp-vp-imgreset,.dsp-vp-cropbtn,button,input,textarea,select,[data-dsobj="add"],.dsp-vp-ihover-inner,[data-dsvx-skip]';
function $(id){ return document.getElementById(id); }
function wait(ms){ return new Promise(function(r){ setTimeout(r, ms); }); }
function toast(msg, type){ if (typeof window.dspToast === 'function') window.dspToast(msg, type || 'success', 4500); }
function zipLib(){
	if (window.JSZip) return Promise.resolve();
	if (zipP) return zipP;
	zipP = new Promise(function(res, rej){ var s = document.createElement('script'); s.src = ZIP; s.async = true;
		s.onload = function(){ window.JSZip ? res() : rej(new Error('The download tool did not load.')); };
		s.onerror = function(){ zipP = null; rej(new Error('The download tool could not be loaded. Check your connection and try again.')); };
		document.head.appendChild(s); });
	return zipP;
}
function deck(){
	if (window.S && S.data && S.data.slides && S.data.slides.length) return S.data;
	if (window.G && G.pptData && G.pptData.slides && G.pptData.slides.length) return G.pptData;
	return null;
}
function freeDeck(){ try { return !!(window.DSFP && DSFP.deckIsFree && DSFP.deckIsFree()); } catch (e) { return false; } }
function freeCan(){ try { return (window.DSFP && typeof DSFP.canExport === 'function') ? !!(DSFP.canExport('vector') || DSFP.canExport('svg') || DSFP.canExport('eps')) : false; } catch (e) { return false; } }
function freeWords(){ try { return (window.DSFP && typeof DSFP.fmtWords === 'function') ? DSFP.fmtWords() : 'PDF'; } catch (e) { return 'PDF'; } }
function upgrade(msg){ if (window.DSFP && typeof DSFP.upgrade === 'function') { DSFP.upgrade(msg); return; } toast(msg, 'error'); }
function fileBase(){
	var t = (window.G && G.pptTitle) || (deck() && deck().title) || 'presentation';
	t = String(t).replace(/[\\\/:*?"<>|]+/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 80);
	return t || 'presentation';
}
function progress(){
	var el = document.createElement('div'); el.id = 'dsvx-prog';
	el.innerHTML = '<div class="dsvx-card"><div class="dsvx-sp"></div><div class="dsvx-m">Preparing your vector files…</div><div class="dsvx-s">Please keep this tab open.</div><div class="dsvx-bar"><i></i></div></div>';
	document.body.appendChild(el);
	return {
		set: function(m, pct){ el.querySelector('.dsvx-m').textContent = m; if (pct != null) el.querySelector('.dsvx-bar i').style.width = Math.max(0, Math.min(100, pct)) + '%'; },
		done: function(){ if (el.parentNode) el.parentNode.removeChild(el); }
	};
}
function imagesReady(root, max){
	var imgs = [].slice.call(root.querySelectorAll('img')).filter(function(i){ return !i.complete; });
	if (!imgs.length) return Promise.resolve();
	return Promise.race([Promise.all(imgs.map(function(i){ return new Promise(function(r){ i.addEventListener('load', r); i.addEventListener('error', r); }); })), wait(max)]);
}
async function settle(main, i, d){
	await wait(320);
	var t0 = Date.now();
	while (Date.now() - t0 < 6000) {
		var s = d.slides[i] || {};
		if (!s.image_loading && !main.querySelector('img:not([src])') && !main.querySelector('.ds-img-busy')) break;
		await wait(250);
	}
	await imagesReady(main, 4000);
	try { await document.fonts.ready; } catch (e) {}
}
/* a hidden page (same styles as the editor) where each slide is laid out at full size */
async function mkFrame(W, H){
	var f = document.createElement('iframe');
	f.setAttribute('aria-hidden', 'true'); f.tabIndex = -1;
	f.style.cssText = 'position:fixed;left:-14000px;top:0;width:' + (W + 40) + 'px;height:' + (H + 40) + 'px;border:0;pointer-events:none;visibility:visible;';
	document.body.appendChild(f);
	var d = f.contentDocument;
	d.open(); d.write('<!doctype html><meta charset="utf-8"><title>slide</title>'); d.close();
	d.documentElement.className = document.documentElement.className;
	d.body.className = document.body.className;
	d.body.style.cssText = 'margin:0!important;padding:0!important;background:transparent!important;overflow:hidden!important;';
	var waits = [];
	document.querySelectorAll('style,link[rel="stylesheet"]').forEach(function(n){
		if (n.id === 'ds-vector-export-css' || n.id === 'ds-ppt-export-css') return;
		var c = d.importNode(n, true);
		if (c.tagName === 'LINK') waits.push(new Promise(function(r){ c.onload = c.onerror = r; setTimeout(r, 5000); }));
		d.head.appendChild(c);
	});
	await Promise.all(waits);
	return f;
}
var RESET = 'position:static!important;display:block!important;width:auto!important;height:auto!important;min-width:0!important;min-height:0!important;max-width:none!important;max-height:none!important;transform:none!important;overflow:visible!important;margin:0!important;padding:0!important;border:0!important;background:transparent!important;box-shadow:none!important;inset:auto!important;zoom:1!important;opacity:1!important;visibility:visible!important;float:none!important;';
async function stage(f, main, W, H){
	var d = f.contentDocument;
	d.body.innerHTML = '';
	var chain = [], a = main.parentElement, top = $('ds-stu');
	while (a && a !== document.body) { chain.unshift(a); if (a === top) break; a = a.parentElement; }
	var host = d.body;
	chain.forEach(function(n){ var c = d.importNode(n, false); c.style.cssText = RESET; host.appendChild(c); host = c; });
	var m = d.importNode(main, true);
	m.style.margin = '0'; m.style.boxShadow = 'none'; m.style.borderRadius = '0'; m.style.transform = 'none'; m.style.position = 'relative';
	m.style.width = W + 'px'; m.style.height = H + 'px'; m.style.minWidth = W + 'px'; m.style.maxWidth = 'none'; m.style.maxHeight = 'none'; m.style.zoom = '1';
	host.appendChild(m);
	m.querySelectorAll(STRIP).forEach(function(x){ x.remove(); });
	m.querySelectorAll('.ds-img-busy').forEach(function(x){ x.classList.remove('ds-img-busy'); });
	m.querySelectorAll('[contenteditable]').forEach(function(x){ x.removeAttribute('contenteditable'); });
	m.querySelectorAll('*').forEach(function(x){
		if (x.children.length) return;
		if (/^\W{0,3}(click to add|add image|change image|click to upload|add an image)/i.test((x.textContent || '').trim())) x.style.visibility = 'hidden';
	});
	var oc = main.querySelectorAll('canvas'), nc = m.querySelectorAll('canvas');
	for (var i = 0; i < oc.length && i < nc.length; i++) { try { nc[i].width = oc[i].width; nc[i].height = oc[i].height; nc[i].getContext('2d').drawImage(oc[i], 0, 0); } catch (e) {} }
	try { await d.fonts.ready; } catch (e) {}
	await imagesReady(m, 4000);
	await wait(60);
	return m;
}
function sizeOf(main){
	var w = main.offsetWidth || 16, h = main.offsetHeight || 9, W, H;
	if (w >= h) { W = LONG; H = Math.round(LONG * h / w); } else { H = LONG; W = Math.round(LONG * w / h); }
	[720, 960, 1280, 1024, 1080].forEach(function(v){ if (Math.abs(W - v) <= 4) W = v; if (Math.abs(H - v) <= 4) H = v; });
	return {W:W, H:H};
}
function fontNote(all){
	var fam = Object.keys(all).sort(), lines = ['Fonts used in these files', '=========================', ''];
	if (!fam.length) lines.push('(no text)');
	fam.forEach(function(f){
		var ws = Object.keys(all[f]).sort();
		var names = ws.map(function(w){ var it = /i$/.test(w), n = parseInt(w, 10); return psName(f, n, it); });
		lines.push(f + '  -  weights: ' + ws.map(function(w){ return w.replace('i', ' italic'); }).join(', '));
		lines.push('    PostScript names: ' + names.join(', '));
		if (!/^(arial|helvetica|times new roman|georgia|verdana|courier new|sans-serif|serif)$/i.test(f)) lines.push('    Free download: https://fonts.google.com/specimen/' + encodeURIComponent(f).replace(/%20/g, '+'));
		lines.push('');
	});
	lines.push('Install these fonts before opening the files so the text keeps its look.',
		'All text is live and editable. Each slide comes as:',
		'  SVG - every element as its own named group, gradients and transparency kept (Figma, Illustrator, Inkscape, Affinity).',
		'  EPS - print / Illustrator format. EPS has no transparency, so soft shadows, blurs and see-through areas over pictures are',
		'        kept as small pictures in place; everything else stays vector.');
	return lines.join('\r\n');
}

DSVX.exportDeck = async function(){
	var d = deck();
	if (!d) { toast('Nothing to download yet.', 'error'); return; }
	if (busy) return;
	if (typeof window.dspSelectThumb !== 'function' || !$('dsp-ppt-main-slide')) { toast('Open the presentation in the editor first.', 'error'); return; }
	busy = true;
	var ui = progress(), f = null, n = d.slides.length;
	var keep = (window.G && G.activeSlideIdx >= 0) ? G.activeSlideIdx : 0;
	try {
		ui.set('Getting ready…', 3);
		await zipLib();
		var main = $('dsp-ppt-main-slide'), base = fileBase(), zip = new window.JSZip(), sz = sizeOf(main), fonts = {}, bad = 0;
		f = await mkFrame(sz.W, sz.H);
		for (var i = 0; i < n; i++) {
			var nn = String(i + 1).padStart(2, '0');
			ui.set('Slide ' + (i + 1) + ' of ' + n + ': reading the design…', 5 + (i / n) * 88);
			window.dspSelectThumb(i);
			await settle(main, i, d);
			sz = sizeOf(main);
			if (parseInt(f.style.width, 10) < sz.W + 40 || parseInt(f.style.height, 10) < sz.H + 40) { f.style.width = (sz.W + 40) + 'px'; f.style.height = (sz.H + 40) + 'px'; }
			try {
				var root = await stage(f, main, sz.W, sz.H);
				var R = read(f.contentWindow, root, sz.W, sz.H);
				ui.set('Slide ' + (i + 1) + ' of ' + n + ': icons and pictures…', 5 + ((i + 0.35) / n) * 88);
				await resolvePending(R, f.contentWindow);
				await encodeImages(R);
				Object.keys(R.fonts).forEach(function(k){ fonts[k] = fonts[k] || {}; Object.keys(R.fonts[k]).forEach(function(w){ fonts[k][w] = 1; }); });
				var title = base + ' - slide ' + (i + 1);
				zip.file('SVG/' + base + ' - slide ' + nn + '.svg', toSVG(R, title));
				ui.set('Slide ' + (i + 1) + ' of ' + n + ': writing the EPS…', 5 + ((i + 0.7) / n) * 88);
				zip.file('EPS/' + base + ' - slide ' + nn + '.eps', await toEPS(R, title));
			} catch (er) { bad++; if (window.console) console.warn('[DS vector] slide ' + (i + 1), er); }
		}
		if (bad === n) throw new Error('The vector files could not be made. Please try again.');
		zip.file('Fonts used.txt', fontNote(fonts));
		ui.set('Finishing your file…', 95);
		var blob = await zip.generateAsync({type:'blob', compression:'DEFLATE', compressionOptions:{level:6}}), name = base + ' - EPS + SVG.zip';
		ui.set('Done', 100);
		DSVX.last = {kind:'vector', name:name, size:blob.size, slides:n, failed:bad};
		if (window.DSX && typeof DSX.save === 'function') DSX.save(blob, name);
		else { var u = URL.createObjectURL(blob), a = document.createElement('a'); a.href = u; a.download = name; document.body.appendChild(a); a.click(); setTimeout(function(){ a.remove(); URL.revokeObjectURL(u); }, 4000); }
		try { document.dispatchEvent(new CustomEvent('ds:ppt-exported', {detail:DSVX.last})); } catch (e) {}
		toast(bad ? ('EPS + SVG downloaded (' + bad + ' slide' + (bad > 1 ? 's' : '') + ' could not be converted)') : 'EPS + SVG files downloaded', bad ? 'error' : 'success');
	} catch (err) {
		if (window.console) console.warn('[DS vector]', err);
		toast((err && err.message) || 'The download could not be made. Please try again.', 'error');
	} finally {
		if (f && f.parentNode) f.parentNode.removeChild(f);
		try { window.dspSelectThumb(Math.min(keep, n - 1)); } catch (e) {}
		ui.done(); busy = false;
	}
};

/* ---------- "EPS + SVG" in the Download menu ---------- */
function addItem(m){
	if (!m || m.querySelector('[data-vx]')) return;
	/* Not offered inside the PowerPoint add-in (side panel or PowerPoint window) - website only. */
	if (document.documentElement.classList.contains('ds-office-mode')) return;
	var locked = freeDeck() && !freeCan();
	var b = document.createElement('button'); b.type = 'button'; b.setAttribute('data-vx', '1'); if (locked) b.className = 'dsx-locked';
	b.innerHTML = '<span class="dsx-ic dsx-vec"><span>EPS</span><span>SVG</span></span><span><span class="dsx-t">EPS + SVG (vector)</span><span class="dsx-d">Editable vector files for Illustrator, Figma or Inkscape – live text, named layers</span></span>' + (locked ? '<span class="dsx-lock">&#128274; Upgrade</span>' : '');
	b.addEventListener('click', function(e){
		e.preventDefault(); e.stopPropagation();
		if (m.parentNode) m.parentNode.removeChild(m);
		if (locked) { upgrade('EPS + SVG download is part of the paid plans. Free presentations can be downloaded as ' + freeWords() + '.'); return; }
		if (freeDeck()) { var why = ''; try { why = (window.DSFP && typeof DSFP.exportBlock === 'function') ? (DSFP.exportBlock() || '') : ''; } catch (er) {} if (why) { upgrade(why); return; } }
		DSVX.exportDeck();
	});
	var after = m.querySelector('button[data-k="jpg"]');
	if (after && after.nextSibling) m.insertBefore(b, after.nextSibling); else m.appendChild(b);
	var r = m.getBoundingClientRect(); if (r.bottom > window.innerHeight - 8) m.style.top = Math.max(8, window.innerHeight - r.height - 8) + 'px';
}
new MutationObserver(function(list){
	list.forEach(function(x){ [].forEach.call(x.addedNodes, function(nd){ if (nd.id === 'dsx-menu') addItem(nd); }); });
}).observe(document.body, {childList:true});
})();
</script>
	<?php
}, 1310);
