<?php
/**
 * Code Snippets export — DS Image Holder — transparent PNG stays transparent (no background)
 *
 * Snippet ID : 1187
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-ppt, images
 * Modified   : 2026-10-13
 * Load order : 350 of 352
 */
/**
 * DS Image Holder — transparent PNG stays transparent
 * Run: Everywhere (front end only)
 *
 * AI Presentation editor: a picture uploaded into a slide's image holder (Upload from Device, the
 * "or upload your own image" link, drag & drop onto the holder) and into a template's second picture
 * box keeps its transparency. A PNG / WebP / GIF / SVG with see-through parts is stored as PNG and
 * shown with nothing behind it: the slide's own background and shapes show through the empty parts,
 * in the editor, the thumbnails, the picture / PDF downloads and the PowerPoint download.
 *
 * Before: a transparent PNG larger than about 300 KB, and every second-box picture, was flattened
 * onto white and saved as JPEG, so a white (or coloured) block appeared behind the cut-out.
 * Now: a transparent picture is scaled down (longest side 1600 px and smaller when needed) until it
 * fits, but always stays PNG. Photos without transparency are handled exactly as before (JPEG).
 *
 * NEW SNIPPET. It replaces the upload handlers of "DS PPT Image Upload (from device)" and
 * "DS Template Second Picture" at run time; those snippets are not edited.
 * Undo: deactivate this snippet (the old behaviour comes back).
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
	if ( is_admin() ) return;
	?>
<script id="ds-image-holder-png">
(function(){
	if (window.__dsImgHolderPng) return; window.__dsImgHolderPng = 1;
	var MAX_MB = 15, MAXPX = 1600, MINPX = 480, TARGET = 880000, HARD = 2600000;

	function toast(m, t){ if (typeof window.dspToast === 'function') window.dspToast(m, t || 'success', 3500); }
	function curIdx(){
		var S = window.S;
		if (window._vpMktTargetSlide >= 0) return window._vpMktTargetSlide;
		if (window._vpOptIdx >= 0) return window._vpOptIdx;
		return S && S.idx >= 0 ? S.idx : -1;
	}
	function mayAlpha(type, name){ return /png|webp|gif|svg/i.test(type || '') || /\.(png|webp|gif|svg)$/i.test(name || ''); }
	function hasAlpha(ctx, w, h){
		try { var d = ctx.getImageData(0, 0, w, h).data; for (var i = 3; i < d.length; i += 8) if (d[i] < 250) return true; } catch (e) {}
		return false;
	}
	function canvasAt(im, px){
		var w = im.naturalWidth || 1600, h = im.naturalHeight || 900, s = Math.min(1, px / Math.max(w, h));
		var c = document.createElement('canvas'); c.width = Math.max(1, Math.round(w * s)); c.height = Math.max(1, Math.round(h * s));
		var x = c.getContext('2d'); x.imageSmoothingEnabled = true; x.imageSmoothingQuality = 'high';
		x.clearRect(0, 0, c.width, c.height); x.drawImage(im, 0, 0, c.width, c.height);
		return {c:c, x:x};
	}
	function jpeg(cv){
		var c2 = document.createElement('canvas'); c2.width = cv.width; c2.height = cv.height;
		var x2 = c2.getContext('2d'); x2.fillStyle = '#ffffff'; x2.fillRect(0, 0, c2.width, c2.height); x2.drawImage(cv, 0, 0);
		return c2.toDataURL('image/jpeg', 0.86);
	}
	/* file -> data URI. Transparent picture: PNG, made smaller step by step until it fits (never flattened). */
	function prepare(file, cb){
		var url = URL.createObjectURL(file), im = new Image();
		im.onload = function(){
			var out = '';
			try {
				var first = canvasAt(im, MAXPX);
				if (mayAlpha(file.type, file.name) && hasAlpha(first.x, first.c.width, first.c.height)) {
					var px = MAXPX, cur = first, best = '';
					for (var t = 0; t < 8; t++) {
						var d = cur.c.toDataURL('image/png');
						if (d.indexOf('data:image/png') === 0) best = d;
						if (best && best.length <= TARGET) break;
						px = Math.round(px * 0.8); if (px < MINPX) break;
						cur = canvasAt(im, px);
					}
					if (best && best.length <= HARD) out = best;
				}
				if (!out) out = jpeg(canvasAt(im, MAXPX).c);
			} catch (e) { out = ''; }
			URL.revokeObjectURL(url);
			if (out) cb(out); else toast('This image could not be processed.', 'error');
		};
		im.onerror = function(){ URL.revokeObjectURL(url); toast('This image format is not supported. Try JPG or PNG.', 'error'); };
		im.src = url;
	}
	function check(f){
		if (!f) return false;
		if (!f.type || f.type.indexOf('image/') !== 0) { toast('Please choose an image file (JPG, PNG, WEBP or GIF).', 'error'); return false; }
		if (f.size > MAX_MB * 1024 * 1024) { toast('That image is too large. Maximum size is ' + MAX_MB + ' MB.', 'error'); return false; }
		return true;
	}
	function clearMarket(sl){ if (!sl) return; sl.image_position = {x:50, y:50}; delete sl.image_market_id; delete sl.image_market_tier; delete sl.image_mkt_fetched; }
	function apply(idx, uri){
		var S = window.S, G = window.G;
		if (!S || !S.data || !S.data.slides || !S.data.slides[idx]) { toast('Could not find the slide for this image.', 'error'); return; }
		clearMarket(S.data.slides[idx]);
		if (G && G.pptData && G.pptData.slides && G.pptData.slides[idx]) clearMarket(G.pptData.slides[idx]);
		var ok = false;
		if (typeof window.dspUpdateSlideImage === 'function') ok = window.dspUpdateSlideImage(idx, uri, null);
		if (!ok) { S.data.slides[idx].image_data = uri; if (G && G.pptData && G.pptData.slides && G.pptData.slides[idx]) G.pptData.slides[idx].image_data = uri; }
		try { document.dispatchEvent(new CustomEvent('ds:slide-image-uploaded', {detail:{idx:idx}})); } catch (e) {}
		toast(/^data:image\/png/.test(uri) ? 'Image added to slide ' + (idx + 1) + ' (transparency kept)' : 'Image added to slide ' + (idx + 1), 'success');
	}
	function handle(f, idx){
		if (!check(f)) return;
		if (!(idx >= 0)) { toast('Could not find the slide for this image. Click the image area and try again.', 'error'); return; }
		toast('Adding your image...', 'success');
		prepare(f, function(uri){ apply(idx, uri); });
	}
	function pickFile(accept, done){
		var inp = document.createElement('input'); inp.type = 'file'; inp.accept = accept; inp.style.display = 'none'; document.body.appendChild(inp);
		inp.addEventListener('change', function(){ var f = inp.files && inp.files[0]; inp.remove(); if (f) done(f); });
		inp.click();
	}
	var ACC = 'image/png,image/jpeg,image/webp,image/gif,image/bmp,image/svg+xml,image/*';

	/* 1. "Upload from Device" in the Add Image popup and "or upload your own image" in the Generate Image popup */
	function up(){ var idx = curIdx(); if (typeof window.vpImgOptionClose === 'function') window.vpImgOptionClose(); pickFile(ACC, function(f){ handle(f, idx); }); }
	function upGen(){ var S = window.S, idx = (S && S.genIdx >= 0) ? S.genIdx : curIdx(); if (typeof window.vpImgClose === 'function') window.vpImgClose(); pickFile(ACC, function(f){ handle(f, idx); }); }
	up.__dsPng = upGen.__dsPng = 1;
	function install(){
		if (!window.vpImgOptionUpload || !window.vpImgOptionUpload.__dsPng) window.vpImgOptionUpload = up;
		if (!window.vpImgGenUpload || !window.vpImgGenUpload.__dsPng) window.vpImgGenUpload = upGen;
	}
	install(); if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', install); setTimeout(install, 1500); setTimeout(install, 5000);

	/* 2. a file dropped onto an image holder (runs before the older drop handler) */
	window.addEventListener('drop', function(e){
		var t = e.dataTransfer && e.dataTransfer.types, isF = false; if (t) for (var i = 0; i < t.length; i++) if (t[i] === 'Files') isF = true;
		if (!isF) return;
		var z = e.target && e.target.closest ? e.target.closest('.dsp-vp-imgzone') : null; if (!z) return;
		e.preventDefault(); e.stopPropagation(); z.classList.remove('ds-up-drop');
		var a = z.getAttribute('data-slide-idx'), idx = (a !== null && a !== '' && a !== 'undefined') ? parseInt(a, 10) : (window.S ? window.S.idx : -1);
		var f = e.dataTransfer.files && e.dataTransfer.files[0]; if (f) handle(f, idx);
	}, true);

	/* 3. the second picture box of a template layout */
	window.addEventListener('click', function(e){
		var t = e.target && e.target.closest ? e.target.closest('[style*="--ds-img2"]') : null; if (!t || typeof window.dsImg2Set !== 'function') return;
		var m = /--ds-img2(?:-empty)?:(\d+)/.exec(t.getAttribute('style') || ''); if (!m) return;
		e.preventDefault(); e.stopPropagation();
		var idx = parseInt(m[1], 10);
		pickFile('image/*', function(f){
			if (!check(f)) return;
			prepare(f, function(uri){ window.dsImg2Set(idx, uri); toast(/^data:image\/png/.test(uri) ? 'Picture 2 added (transparency kept). Click Save to keep it.' : 'Picture 2 added. Click Save to keep it.'); });
		});
	}, true);
})();
</script>
	<?php
}, 1210);
