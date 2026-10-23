<?php
/**
 * Code Snippets export — DS PPT Export — Download as PPTX, PDF, PNG or JPG
 *
 * Snippet ID : 967
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : ds-ppt, export
 * Modified   : 2026-10-02 14:29:33
 * Load order : 186 of 192
 */
/**
 * DS PPT Export — Download as PPTX, PDF, PNG or JPG
 * Run: front end
 *
 * Turns the editor's "Download PPTX" button into a Download menu with four formats:
 *   PowerPoint (.pptx)  – the existing builder, unchanged (calls dspBuild())
 *   PDF (.pdf)          – one page per slide
 *   PNG images          – one picture per slide (a .zip when there is more than one slide)
 *   JPG images          – one picture per slide (a .zip when there is more than one slide)
 * PDF / PNG / JPG are made in the browser from the slides exactly as the editor shows them.
 *
 * NEW SNIPPET — edits no other snippet. Deactivate to get the old single button back.
 * Works with "DS PPT Free Plan": for a free presentation only the formats switched on in
 * Settings > Free Presentations are unlocked (the free plan snippet answers DSFP.canExport()).
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a($post, 'WP_Post') ) return;
	if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
	?>
<style id="ds-ppt-export-css">
#dsx-menu{position:fixed;z-index:2147482000;min-width:270px;padding:8px;border-radius:14px;background:#141a26;border:1px solid #2a3550;box-shadow:0 18px 50px rgba(0,0,0,.55);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
#dsx-menu .dsx-h{padding:6px 10px 8px;color:#8b97ad;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;}
#dsx-menu button{display:flex;align-items:center;gap:12px;width:100%;padding:10px;border:0;border-radius:10px;background:transparent;color:#e6ecf7;text-align:left;cursor:pointer;font-family:inherit;text-transform:none;letter-spacing:normal;}
#dsx-menu button:hover{background:#1e2740;}
#dsx-menu .dsx-ic{flex:0 0 40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;letter-spacing:.02em;}
#dsx-menu .dsx-pptx{background:#c2410c;}#dsx-menu .dsx-pdf{background:#b91c1c;}#dsx-menu .dsx-png{background:#1d4ed8;}#dsx-menu .dsx-jpg{background:#047857;}
#dsx-menu .dsx-t{display:block;font-size:14px;font-weight:700;line-height:1.25;color:#f3f6fc;}
#dsx-menu .dsx-d{display:block;font-size:12px;font-weight:400;line-height:1.3;color:#8b97ad;margin-top:2px;}
#dsx-menu .dsx-lock{margin-left:auto;font-size:11px;font-weight:800;color:#fbbf24;border:1px solid rgba(251,191,36,.45);border-radius:999px;padding:3px 8px;white-space:nowrap;}
#dsx-menu button.dsx-locked .dsx-ic{filter:grayscale(1);opacity:.55;}
#dsx-menu button.dsx-locked .dsx-t{color:#9aa5bb;}
#dsx-prog{position:fixed;inset:0;z-index:2147483000;background:rgba(6,9,16,.86);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}
#dsx-prog .dsx-card{width:min(92vw,380px);background:#141a26;border:1px solid #2a3550;border-radius:16px;padding:28px 28px 24px;text-align:center;color:#e6ecf7;}
#dsx-prog .dsx-sp{width:38px;height:38px;border:3px solid #2a3550;border-top-color:#60a5fa;border-radius:50%;margin:0 auto 16px;animation:dsxspin .8s linear infinite;}
#dsx-prog .dsx-m{font-size:15px;font-weight:700;}
#dsx-prog .dsx-s{font-size:12.5px;color:#8b97ad;margin-top:5px;}
#dsx-prog .dsx-bar{height:6px;border-radius:99px;background:#1e2740;margin-top:18px;overflow:hidden;}
#dsx-prog .dsx-bar i{display:block;height:100%;width:0;background:#3b82f6;border-radius:99px;transition:width .25s;}
@keyframes dsxspin{to{transform:rotate(360deg);}}
</style>
<script id="ds-ppt-export-js">
(function(){
	if (window.DSX) return;
	var DSX = window.DSX = {};
	var LIBS = {
		h2c: ['https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', function(){ return !!window.html2canvas; }],
		pdf: ['https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', function(){ return !!(window.jspdf && window.jspdf.jsPDF); }],
		zip: ['https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', function(){ return !!window.JSZip; }]
	};
	var OUT_W = 1920;          /* picture width in pixels */
	var STRIP = '.dsp-vp-ihover,.dsp-vp-imgreset,.dsp-vp-cropbtn,button,input,textarea,select';
	var busy = false, _libs = {};

	function $(id){ return document.getElementById(id); }
	function wait(ms){ return new Promise(function(r){ setTimeout(r, ms); }); }
	function toast(msg, type){ if (typeof window.dspToast === 'function') window.dspToast(msg, type || 'success', 4000); }
	function lib(name){
		var L = LIBS[name];
		if (L[1]()) return Promise.resolve();
		if (_libs[name]) return _libs[name];
		_libs[name] = new Promise(function(res, rej){
			var s = document.createElement('script'); s.src = L[0]; s.async = true;
			s.onload = function(){ L[1]() ? res() : rej(new Error('The export tool did not load.')); };
			s.onerror = function(){ _libs[name] = null; rej(new Error('The export tool could not be loaded. Check your connection and try again.')); };
			document.head.appendChild(s);
		});
		return _libs[name];
	}
	function deck(){
		if (window.S && S.data && S.data.slides && S.data.slides.length) return S.data;
		if (window.G && G.pptData && G.pptData.slides && G.pptData.slides.length) return G.pptData;
		return null;
	}
	function freeDeck(){ try { return !!(window.DSFP && DSFP.deckIsFree && DSFP.deckIsFree()); } catch(e){ return false; } }
	/* which formats a free presentation may use: asked from the free plan snippet (an older one only allowed PDF) */
	function freeCan(k){ try { return (window.DSFP && typeof DSFP.canExport === 'function') ? !!DSFP.canExport(k) : k === 'pdf'; } catch(e){ return k === 'pdf'; } }
	function freeWords(){ try { return (window.DSFP && typeof DSFP.fmtWords === 'function') ? DSFP.fmtWords() : 'PDF'; } catch(e){ return 'PDF'; } }
	function freeWhy(){ try { return (window.DSFP && typeof DSFP.exportBlock === 'function') ? (DSFP.exportBlock() || '') : ''; } catch(e){ return ''; } }
	function upgrade(msg){
		if (window.DSFP && typeof DSFP.upgrade === 'function') { DSFP.upgrade(msg); return; }
		toast(msg, 'error');
	}
	function fileBase(){
		var t = (window.G && G.pptTitle) || (deck() && deck().title) || 'presentation';
		t = String(t).replace(/[\\\/:*?"<>|]+/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 80);
		return t || 'presentation';
	}

	/* ---------- progress ---------- */
	function progress(){
		var el = document.createElement('div'); el.id = 'dsx-prog';
		el.innerHTML = '<div class="dsx-card"><div class="dsx-sp"></div><div class="dsx-m">Preparing your download…</div><div class="dsx-s">Please keep this tab open.</div><div class="dsx-bar"><i></i></div></div>';
		document.body.appendChild(el);
		return {
			set: function(m, pct){ el.querySelector('.dsx-m').textContent = m; if (pct != null) el.querySelector('.dsx-bar i').style.width = Math.max(0, Math.min(100, pct)) + '%'; },
			done: function(){ if (el.parentNode) el.parentNode.removeChild(el); }
		};
	}

	/* ---------- an empty hidden page that holds one slide at a time (fast to photograph) ---------- */
	async function mkFrame(){
		var f = document.createElement('iframe');
		f.setAttribute('aria-hidden', 'true'); f.tabIndex = -1;
		f.style.cssText = 'position:fixed;left:-12000px;top:0;width:1400px;height:1100px;border:0;pointer-events:none;';
		document.body.appendChild(f);
		var d = f.contentDocument;
		d.open(); d.write('<!doctype html><meta charset="utf-8"><title>slide</title>');   /* no closing body tag in this text: the page cache inserts its own script before the first one it finds */ d.close();
		d.documentElement.className = document.documentElement.className;
		d.body.className = document.body.className;
		d.body.style.cssText = 'margin:0!important;padding:0!important;background:transparent!important;overflow:hidden!important;';
		var waits = [];
		document.querySelectorAll('style,link[rel="stylesheet"]').forEach(function(n){
			if (n.id === 'ds-ppt-export-css') return;
			var c = d.importNode(n, true);
			if (c.tagName === 'LINK') waits.push(new Promise(function(r){ c.onload = c.onerror = r; setTimeout(r, 5000); }));
			d.head.appendChild(c);
		});
		await Promise.all(waits);
		return f;
	}
	var RESET = 'position:static!important;display:block!important;width:auto!important;height:auto!important;min-width:0!important;min-height:0!important;max-width:none!important;max-height:none!important;transform:none!important;overflow:visible!important;margin:0!important;padding:0!important;border:0!important;background:transparent!important;box-shadow:none!important;inset:auto!important;zoom:1!important;opacity:1!important;visibility:visible!important;float:none!important;';
	async function capSlide(f, main){
		var d = f.contentDocument;
		d.body.innerHTML = '';
		/* keep the slide's parents (ids / classes only) so the page's own styles still apply */
		var chain = [], a = main.parentElement, top = $('ds-stu');
		while (a && a !== document.body) { chain.unshift(a); if (a === top) break; a = a.parentElement; }
		var host = d.body;
		chain.forEach(function(n){ var c = d.importNode(n, false); c.style.cssText = RESET; host.appendChild(c); host = c; });
		var w = main.offsetWidth, h = main.offsetHeight;
		var m = d.importNode(main, true);
		m.style.margin = '0'; m.style.boxShadow = 'none'; m.style.borderRadius = '0'; m.style.transform = 'none';
		m.style.width = w + 'px'; m.style.height = h + 'px';
		host.appendChild(m);
		/* editor-only controls never go into the file */
		m.querySelectorAll(STRIP).forEach(function(x){ x.remove(); });
		m.querySelectorAll('.ds-img-busy').forEach(function(x){ x.classList.remove('ds-img-busy'); });   /* the "Optimizing image" spinner */
		m.querySelectorAll('[contenteditable]').forEach(function(x){ x.removeAttribute('contenteditable'); });
		m.querySelectorAll('*').forEach(function(x){
			if (x.children.length) return;
			if (/^\W{0,3}(click to add|add image|change image|click to upload|add an image)/i.test((x.textContent || '').trim())) x.style.visibility = 'hidden';
		});
		/* drawn charts are copied pixel for pixel */
		var oc = main.querySelectorAll('canvas'), nc = m.querySelectorAll('canvas');
		for (var i = 0; i < oc.length; i++) {
			try { nc[i].width = oc[i].width; nc[i].height = oc[i].height; nc[i].getContext('2d').drawImage(oc[i], 0, 0); } catch(e){}
		}
		try { await d.fonts.ready; } catch(e){}
		await imagesReady(m, 4000);
		await wait(80);
		var cv = await window.html2canvas(m, {scale: OUT_W / w, useCORS: true, logging: false, backgroundColor: null, width: w, height: h, windowWidth: 1400, windowHeight: 1100});
		/* exact size: 1920 wide, height from the slide's own shape */
		var H = Math.round(OUT_W * h / w); if (Math.abs(H - 1080) <= 2) H = 1080; if (Math.abs(H - 1440) <= 2) H = 1440;
		var out = document.createElement('canvas'); out.width = OUT_W; out.height = H;
		var x = out.getContext('2d'); x.fillStyle = '#FFFFFF'; x.fillRect(0, 0, OUT_W, H); x.drawImage(cv, 0, 0, OUT_W, H);
		return out;
	}
	function imagesReady(root, max){
		var imgs = [].slice.call(root.querySelectorAll('img')).filter(function(i){ return !i.complete; });
		if (!imgs.length) return Promise.resolve();
		return Promise.race([
			Promise.all(imgs.map(function(i){ return new Promise(function(r){ i.addEventListener('load', r); i.addEventListener('error', r); }); })),
			wait(max)
		]);
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
		try { await document.fonts.ready; } catch(e){}
	}
	function blobOf(canvas, type, q){
		return new Promise(function(res, rej){ canvas.toBlob(function(b){ b ? res(b) : rej(new Error('Could not make the picture.')); }, type, q); });
	}

	/* The save step is separate so it can be replaced (tests) without touching the export itself. */
	DSX.save = function(blob, name){
		var u = URL.createObjectURL(blob), a = document.createElement('a');
		a.href = u; a.download = name; a.style.display = 'none';
		document.body.appendChild(a); a.click();
		setTimeout(function(){ if (a.parentNode) a.parentNode.removeChild(a); URL.revokeObjectURL(u); }, 4000);
	};

	DSX.exportAs = async function(kind){
		var d = deck();
		if (!d) { toast('Nothing to download yet.', 'error'); return; }
		if (busy) return;
		if (typeof window.dspSelectThumb !== 'function' || !$('dsp-ppt-main-slide')) { toast('Open the presentation in the editor first.', 'error'); return; }
		busy = true;
		var ui = progress(), f = null, n = d.slides.length;
		var keep = (window.G && G.activeSlideIdx >= 0) ? G.activeSlideIdx : 0;
		try {
			ui.set('Getting ready…', 3);
			await lib('h2c');
			if (kind === 'pdf') await lib('pdf'); else if (n > 1) await lib('zip');
			f = await mkFrame();
			var main = $('dsp-ppt-main-slide'), pdf = null, zip = null, single = null, base = fileBase();
			var type = kind === 'png' ? 'image/png' : 'image/jpeg', ext = kind === 'png' ? 'png' : 'jpg';
			if (kind !== 'pdf' && n > 1) zip = new window.JSZip();
			for (var i = 0; i < n; i++) {
				ui.set('Preparing slide ' + (i + 1) + ' of ' + n + '…', 5 + (i / n) * 85);
				window.dspSelectThumb(i);
				await settle(main, i, d);
				var cv = await capSlide(f, main);
				if (kind === 'pdf') {
					var pw = 960, ph = Math.round(960 * cv.height / cv.width * 100) / 100;
					if (!pdf) pdf = new window.jspdf.jsPDF({orientation: pw >= ph ? 'landscape' : 'portrait', unit: 'pt', format: [pw, ph], compress: true});
					else pdf.addPage([pw, ph], pw >= ph ? 'landscape' : 'portrait');
					pdf.addImage(cv.toDataURL('image/jpeg', 0.92), 'JPEG', 0, 0, pw, ph, undefined, 'FAST');
				} else {
					var b = await blobOf(cv, type, 0.93);
					if (zip) zip.file('slide-' + String(i + 1).padStart(2, '0') + '.' + ext, b); else single = b;
				}
				cv.width = cv.height = 0;
			}
			ui.set('Finishing your file…', 94);
			var blob, name;
			if (kind === 'pdf') { pdf.setProperties({title: base}); blob = pdf.output('blob'); name = base + '.pdf'; }
			else if (zip) { blob = await zip.generateAsync({type: 'blob', compression: 'STORE'}); name = base + ' - ' + ext.toUpperCase() + ' slides.zip'; }
			else { blob = single; name = base + '.' + ext; }
			ui.set('Done', 100);
			DSX.last = {kind: kind, name: name, size: blob.size, slides: n};
			DSX.save(blob, name);
			try { document.dispatchEvent(new CustomEvent('ds:ppt-exported', {detail: DSX.last})); } catch(e){}
			toast(kind === 'pdf' ? 'PDF downloaded' : (ext.toUpperCase() + ' ' + (n > 1 ? 'slides' : 'slide') + ' downloaded'), 'success');
		} catch (err) {
			if (window.console) console.warn('[DS export]', err);
			toast((err && err.message) || 'The download could not be made. Please try again.', 'error');
		} finally {
			if (f && f.parentNode) f.parentNode.removeChild(f);
			try { window.dspSelectThumb(Math.min(keep, n - 1)); } catch(e){}
			ui.done(); busy = false;
		}
	};

	/* ---------- the Download menu ---------- */
	var ITEMS = [
		{k: 'pptx', t: 'PowerPoint (.pptx)', d: 'Editable slides for PowerPoint, Keynote or Google Slides'},
		{k: 'pdf',  t: 'PDF (.pdf)',         d: 'One page per slide – best for sharing and printing'},
		{k: 'png',  t: 'PNG images',         d: 'One sharp picture per slide'},
		{k: 'jpg',  t: 'JPG images',         d: 'One small picture per slide'}
	];
	function closeMenu(){ var m = $('dsx-menu'); if (m) m.remove(); }
	function openMenu(btn){
		closeMenu();
		var free = freeDeck();
		var m = document.createElement('div'); m.id = 'dsx-menu';
		m.innerHTML = '<div class="dsx-h">Download as</div>' + ITEMS.map(function(it){
			var locked = free && !freeCan(it.k);
			return '<button type="button" data-k="' + it.k + '"' + (locked ? ' class="dsx-locked"' : '') + '><span class="dsx-ic dsx-' + it.k + '">' + it.k.toUpperCase() + '</span><span><span class="dsx-t">' + it.t + '</span><span class="dsx-d">' + it.d + '</span></span>' + (locked ? '<span class="dsx-lock">&#128274; Upgrade</span>' : '') + '</button>';
		}).join('');
		document.body.appendChild(m);
		var r = btn.getBoundingClientRect(), mw = m.offsetWidth, mh = m.offsetHeight;
		var left = Math.min(Math.max(8, r.right - mw), window.innerWidth - mw - 8);
		var top = r.bottom + 8; if (top + mh > window.innerHeight - 8) top = Math.max(8, r.top - mh - 8);
		m.style.left = left + 'px'; m.style.top = top + 'px';
		m.addEventListener('click', function(e){
			var b = e.target.closest('button[data-k]'); if (!b) return;
			var k = b.getAttribute('data-k'); closeMenu();
			if (b.classList.contains('dsx-locked')) { upgrade((k === 'pptx' ? 'PowerPoint (.pptx)' : k.toUpperCase()) + ' download is part of the paid plans. Free presentations can be downloaded as ' + freeWords() + '.'); return; }
			if (freeDeck()) { var why = freeWhy(); if (why) { upgrade(why); return; } }
			if (k === 'pptx') { if (typeof window.dspBuild === 'function') window.dspBuild(); return; }
			DSX.exportAs(k);
		});
	}
	document.addEventListener('click', function(e){
		var t = e.target;
		var btn = t && t.closest ? t.closest('#dsp-buildbtn') : null;
		if (btn) {
			e.preventDefault(); e.stopImmediatePropagation();
			if ($('dsx-menu')) closeMenu(); else openMenu(btn);
			return;
		}
		if ($('dsx-menu') && !(t && t.closest && t.closest('#dsx-menu'))) closeMenu();
	}, true);
	document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });
	
	/* button label: "Download" + a small arrow */
	function label(){
		var b = $('dsp-buildbtn'); if (!b) return;
		if (b.getAttribute('data-dsx') === '1') { keepName(b); return; }
		if (!/download\s*pptx/i.test(b.textContent || '')) return;
		var done = false;
		(function walk(n){
			for (var i = 0; i < n.childNodes.length && !done; i++) {
				var c = n.childNodes[i];
				if (c.nodeType === 3 && /download\s*pptx/i.test(c.nodeValue)) { c.nodeValue = c.nodeValue.replace(/download\s*pptx/i, 'Download ▾'); done = true; }
				else if (c.nodeType === 1) walk(c);
			}
		})(b);
		if (done) { b.setAttribute('data-dsx', '1'); b.title = 'Download as PPTX, PDF, PNG or JPG'; keepName(b); }
	}
	/* other snippets (the editor's Theme button, ...) recognise the editor by a visible button that says "Download PPTX":
	   keep those words inside the button, hidden from view, so they still work after the rename */
	function keepName(b){
		if (b.querySelector('.dsx-legacy')) return;
		var sp = document.createElement('span'); sp.className = 'dsx-legacy'; sp.setAttribute('aria-hidden', 'true'); sp.textContent = ' Download PPTX';
		sp.style.cssText = 'position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0;';
		b.appendChild(sp);
	}
	setInterval(label, 900); label();
})();
</script>
	<?php
}, 1300);
