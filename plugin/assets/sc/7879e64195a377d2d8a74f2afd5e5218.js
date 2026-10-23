
(function(){
	if (window.__dsImgUpload) return;
	window.__dsImgUpload = true;

	var MAX_MB = 15;
	var input = null;

	function toast(msg, type){
		if (typeof window.dspToast === 'function') { window.dspToast(msg, type || 'success', 3500); }
		else if (type === 'error') { console.warn('[DS upload] ' + msg); }
	}

	function currentIdx(){
		var S = window.S;
		if (window._vpMktTargetSlide >= 0) return window._vpMktTargetSlide;
		if (window._vpOptIdx >= 0) return window._vpOptIdx;
		return S && S.idx >= 0 ? S.idx : -1;
	}

	function getInput(){
		if (input) return input;
		input = document.createElement('input');
		input.type = 'file';
		input.accept = 'image/png,image/jpeg,image/webp,image/gif,image/bmp,image/svg+xml,image/*';
		input.style.display = 'none';
		input.addEventListener('change', function(){
			var f = input.files && input.files[0];
			var idx = input._dsIdx;
			input.value = '';
			if (f) handleFile(f, idx);
		});
		document.body.appendChild(input);
		return input;
	}

	function pick(idx){
		var i = getInput();
		i._dsIdx = idx;
		i.click();
	}

	window.vpImgOptionUpload = function(){
		var idx = currentIdx();
		if (typeof window.vpImgOptionClose === 'function') window.vpImgOptionClose();
		pick(idx);
	};

	window.vpImgGenUpload = function(){
		var S = window.S;
		var idx = (S && S.genIdx >= 0) ? S.genIdx : currentIdx();
		if (typeof window.vpImgClose === 'function') window.vpImgClose();
		pick(idx);
	};

	function handleFile(f, idx){
		if (!f.type || f.type.indexOf('image/') !== 0) { toast('Please choose an image file (JPG, PNG, WEBP or GIF).', 'error'); return; }
		if (f.size > MAX_MB * 1024 * 1024) { toast('That image is too large. Maximum size is ' + MAX_MB + ' MB.', 'error'); return; }
		if (!(idx >= 0)) { toast('Could not find the slide for this image. Click the image area and try again.', 'error'); return; }
		toast('Adding your image...', 'success');
		var r = new FileReader();
		r.onload = function(){ prepare(r.result, f.type, function(uri){ apply(idx, uri, f.name); }); };
		r.onerror = function(){ toast('This image could not be read.', 'error'); };
		r.readAsDataURL(f);
	}

	function hasAlpha(ctx, w, h){
		try {
			var d = ctx.getImageData(0, 0, w, h).data;
			for (var i = 3; i < d.length; i += 16) { if (d[i] < 250) return true; }
		} catch (e) {}
		return false;
	}

	function prepare(src, type, cb){
		var im = new Image();
		im.onload = function(){
			try {
				var w = im.naturalWidth || 1600, h = im.naturalHeight || 900;
				var maybeAlpha = /png|webp|gif|svg/i.test(type);
				var MAX = maybeAlpha ? 1400 : 1600;
				var sc = Math.min(1, MAX / Math.max(w, h));
				var cw = Math.max(1, Math.round(w * sc)), ch = Math.max(1, Math.round(h * sc));
				var cv = document.createElement('canvas');
				cv.width = cw; cv.height = ch;
				var ctx = cv.getContext('2d');
				ctx.drawImage(im, 0, 0, cw, ch);
				var out = null;
				if (maybeAlpha && hasAlpha(ctx, cw, ch)) {
					out = cv.toDataURL('image/png');
					if (out.length > 400000) out = null;
				}
				if (!out) {
					var c2 = document.createElement('canvas');
					c2.width = cw; c2.height = ch;
					var x2 = c2.getContext('2d');
					x2.fillStyle = '#ffffff';
					x2.fillRect(0, 0, cw, ch);
					x2.drawImage(cv, 0, 0);
					out = c2.toDataURL('image/jpeg', 0.86);
				}
				cb(out);
			} catch (e) {
				if (/jpe?g|png/i.test(type)) cb(src); else toast('This image could not be processed.', 'error');
			}
		};
		im.onerror = function(){ toast('This image format is not supported. Try JPG or PNG.', 'error'); };
		im.src = src;
	}

	function clearMarket(sl){
		if (!sl) return;
		sl.image_position = { x: 50, y: 50 };
		delete sl.image_market_id;
		delete sl.image_market_tier;
		delete sl.image_mkt_fetched;
	}

	function apply(idx, uri, name){
		var S = window.S, G = window.G;
		if (!S || !S.data || !S.data.slides || !S.data.slides[idx]) { toast('Could not find the slide for this image.', 'error'); return; }
		clearMarket(S.data.slides[idx]);
		if (G && G.pptData && G.pptData.slides && G.pptData.slides[idx]) clearMarket(G.pptData.slides[idx]);
		/* keep the slide's existing image prompt so "Generate with AI" still starts from it */
		var ok = false;
		if (typeof window.dspUpdateSlideImage === 'function') ok = window.dspUpdateSlideImage(idx, uri, null);
		if (!ok) {
			S.data.slides[idx].image_data = uri;
			if (G && G.pptData && G.pptData.slides && G.pptData.slides[idx]) {
				G.pptData.slides[idx].image_data = uri;
			}
		}
		try { document.dispatchEvent(new CustomEvent('ds:slide-image-uploaded', { detail: { idx: idx } })); } catch (e) {}
		toast('Image added to slide ' + (idx + 1), 'success');
	}

	function inject(){
		var m = document.getElementById('vp-img-option-modal');
		if (m && !document.getElementById('vp-img-option-upload-btn')) {
			var store = m.querySelector('button[onclick*="vpImgOptionStore"]');
			var ai = m.querySelector('button[onclick*="vpImgOptionAI"]');
			var anchor = store || ai;
			if (anchor && anchor.parentNode) {
				var b = document.createElement('button');
				b.type = 'button';
				b.id = 'vp-img-option-upload-btn';
				b.setAttribute('onclick', 'vpImgOptionUpload()');
				b.innerHTML = '\uD83D\uDCE4 Upload from Device<small>JPG, PNG, WEBP or GIF \u00B7 free</small>';
				anchor.parentNode.insertBefore(b, anchor.nextSibling);
			}
		}
		var box = document.getElementById('vp-imgmod-box');
		if (box && !document.getElementById('ds-imgmod-upload-link')) {
			var l = document.createElement('button');
			l.type = 'button';
			l.id = 'ds-imgmod-upload-link';
			l.setAttribute('onclick', 'vpImgGenUpload()');
			l.textContent = 'or upload your own image';
			box.appendChild(l);
		}
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', inject); else inject();
	setInterval(inject, 1500);

	function zoneOf(e){ return e.target && e.target.closest ? e.target.closest('.dsp-vp-imgzone') : null; }
	function isFileDrag(e){
		var t = e.dataTransfer && e.dataTransfer.types;
		if (!t) return false;
		for (var i = 0; i < t.length; i++) { if (t[i] === 'Files') return true; }
		return false;
	}
	var lastZone = null;
	function clearDrop(){ if (lastZone) { lastZone.classList.remove('ds-up-drop'); lastZone = null; } }
	document.addEventListener('dragover', function(e){
		if (!isFileDrag(e)) return;
		var z = zoneOf(e);
		if (!z) { clearDrop(); return; }
		e.preventDefault();
		if (lastZone !== z) { clearDrop(); lastZone = z; z.classList.add('ds-up-drop'); }
	}, true);
	document.addEventListener('dragleave', function(e){ if (lastZone && !lastZone.contains(e.relatedTarget)) clearDrop(); }, true);
	document.addEventListener('drop', function(e){
		if (!isFileDrag(e)) return;
		var z = zoneOf(e);
		clearDrop();
		if (!z) return;
		e.preventDefault();
		e.stopPropagation();
		var f = e.dataTransfer.files && e.dataTransfer.files[0];
		var a = z.getAttribute('data-slide-idx');
		var idx = (a !== null && a !== '') ? parseInt(a, 10) : (window.S ? window.S.idx : -1);
		if (f) handleFile(f, idx);
	}, true);
})();
