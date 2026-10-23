
(function(){
if (window.__dsPickFix) return; window.__dsPickFix = 1;
function $(id){ return document.getElementById(id); }

/* ---- picker images: show each one only when it is fully loaded ---- */
function isPickImg(t){ return t && t.tagName === 'IMG' && t.parentNode && t.parentNode.classList && t.parentNode.classList.contains('ds-mkt-pick-item-thumb'); }
function done(img, ok){
	var th = img.parentNode; if (!th) return;
	if (ok) img.classList.add('dspf-ok'); else th.classList.add('dspf-err');
	th.classList.add('dspf-done');
}
document.addEventListener('load', function(e){ if (isPickImg(e.target)) done(e.target, true); }, true);
document.addEventListener('error', function(e){ if (isPickImg(e.target)) done(e.target, false); }, true);
function sweep(){
	var ov = $('ds-mkt-pick-overlay'); if (!ov || !ov.classList.contains('open')) return;
	var h = ov.querySelector('#ds-mkt-pick-header h3');
	if (h && h.getAttribute('data-dspf') !== '1') { h.textContent = '🏪 Browse From Marketplace'; h.setAttribute('data-dspf', '1'); }
	var cats = ov.querySelectorAll('.ds-mkt-pick-cat');
	for (var i = 0; i < cats.length; i++) { var t = cats[i].textContent; if (t.indexOf('&amp;') >= 0) cats[i].textContent = t.replace(/&amp;/gi, '&'); }
	var imgs = ov.querySelectorAll('.ds-mkt-pick-item-thumb img:not(.dspf-ok)');
	for (var k = 0; k < imgs.length; k++) { var im = imgs[k]; if (im.complete && !im.parentNode.classList.contains('dspf-done')) done(im, im.naturalWidth > 0); }
}

/* ---- EDIT SLIDE / LOGO: hide while something other than the editor canvas covers them ---- */
function barCheck(){
	var bar = $('dsce-bar'); if (!bar) return;
	var r = bar.getBoundingClientRect(), hide = false;
	if (r.width && r.height && document.elementsFromPoint) {
		var els = document.elementsFromPoint(r.left + r.width / 2, r.top + r.height / 2), top = null;
		for (var i = 0; i < els.length; i++) { if (els[i] !== bar && !bar.contains(els[i])) { top = els[i]; break; } }
		hide = !!top && !(top.closest && top.closest('#dsp-ppt-canvas-area'));
	}
	if (bar.classList.contains('dspf-hide') !== hide) bar.classList.toggle('dspf-hide', hide);
}
setInterval(function(){ try { sweep(); } catch (e) {} try { barCheck(); } catch (e) {} }, 250);
document.addEventListener('click', function(){ setTimeout(function(){ try { sweep(); barCheck(); } catch (e) {} }, 30); }, true);
})();
