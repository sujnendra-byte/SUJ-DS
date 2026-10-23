
(function(){
if (window.__dsMobEditor) return; window.__dsMobEditor = 1;
var MQ = window.matchMedia('(max-width:1024px)');
function $(id){ return document.getElementById(id); }
function on(){ var fs = $('dsp-editor-fullscreen'); return MQ.matches && fs && fs.classList.contains('dsp-open'); }

/* ---------- zoom: fit to the screen + two-finger pinch ---------- */
var Z = 1, userZoom = false, lastKey = '';
function mainSize(){ var m = $('dsp-ppt-main-slide'); return m ? {w:m.offsetWidth || 900, h:m.offsetHeight || 506} : null; }
function applyZoom(z, quiet){
	var o = $('dsp-ppt-slide-outer'), d = mainSize(); if (!o || !d) return;
	Z = Math.max(0.2, Math.min(3, z));
	o.style.transform = 'scale(' + Z + ')';
	/* the scaled slide takes exactly the room it shows, so it centres and scrolls correctly */
	var mx = -(d.w - d.w * Z) / 2, my = -(d.h - d.h * Z) / 2;
	o.style.margin = my.toFixed(1) + 'px ' + mx.toFixed(1) + 'px';
	var l = $('dsp-ppt-zoom-lbl'); if (l) l.textContent = Math.round(Z * 100) + '%';
	if (!quiet) pokeBars();
}
function clearZoom(){ var o = $('dsp-ppt-slide-outer'); if (o && o.style.margin) { o.style.margin = ''; } }
function fitZoom(){
	var a = $('dsp-ppt-canvas-area'), d = mainSize(); if (!a || !d) return 1;
	var w = a.clientWidth - 24, h = a.clientHeight - 24;
	if (w < 40 || h < 40) return Z;
	return Math.max(0.2, Math.min(1.6, w / d.w, h / d.h));
}
var pokeT = 0, poking = false;
function pokeBars(){               /* let the Edit slide / Logo buttons follow the slide */
	clearTimeout(pokeT);
	pokeT = setTimeout(function(){ poking = true; try { window.dispatchEvent(new Event('resize')); } catch (e) {} poking = false; }, 180);
}
function refit(force){
	if (!on()) return;
	var a = $('dsp-ppt-canvas-area'), d = mainSize(); if (!a || !d) return;
	var key = a.clientWidth + 'x' + a.clientHeight + '|' + d.w + 'x' + d.h;
	if (!force && key === lastKey) { var o = $('dsp-ppt-slide-outer'); if (o && !o.style.margin) applyZoom(Z, true); return; }
	lastKey = key;
	if (!userZoom || force) { userZoom = false; applyZoom(fitZoom()); } else applyZoom(Z);
}
/* the editor's own − / + buttons and mouse wheel keep working, in the same scale */
function hookZoom(){
	var f = window.dspZoom; if (typeof f !== 'function' || f.__dsmb) return;
	var w = function(delta){
		if (!on()) return f.apply(this, arguments);
		userZoom = true; applyZoom(Math.round((Z + (delta || 0)) * 100) / 100);
	};
	w.__dsmb = 1; window.dspZoom = w;
}
function dist(t){ var dx = t[0].clientX - t[1].clientX, dy = t[0].clientY - t[1].clientY; return Math.sqrt(dx * dx + dy * dy) || 1; }
var pin = null;
document.addEventListener('touchstart', function(e){
	if (!on() || e.touches.length !== 2) return;
	var a = $('dsp-ppt-canvas-area'); if (!a || !a.contains(e.target)) return;
	pin = {d:dist(e.touches), z:Z}; a.classList.add('dsmb-pinch');
	if (document.activeElement && document.activeElement.blur && a.contains(document.activeElement)) document.activeElement.blur();
	e.preventDefault();
}, {passive:false, capture:true});
document.addEventListener('touchmove', function(e){
	if (!pin || e.touches.length !== 2) return;
	e.preventDefault();
	userZoom = true; applyZoom(pin.z * dist(e.touches) / pin.d, true);
}, {passive:false, capture:true});
function endPinch(e){
	if (!pin || (e.touches && e.touches.length >= 2)) return;
	pin = null; var a = $('dsp-ppt-canvas-area'); if (a) a.classList.remove('dsmb-pinch');
	pokeBars();
}
document.addEventListener('touchend', endPinch, true);
document.addEventListener('touchcancel', endPinch, true);
/* Safari: stop the page itself from zooming while pinching the slide */
document.addEventListener('gesturestart', function(e){ var a = $('dsp-ppt-canvas-area'); if (on() && a && a.contains(e.target)) e.preventDefault(); }, {passive:false});

/* ---------- strips: arrows + keep the current slide in view ---------- */
function arrows(host, scroller, key){
	if (!host || !scroller) return;
	var l = host.querySelector('.dsmb-arr.dsmb-l'), r = host.querySelector('.dsmb-arr.dsmb-r');
	if (!MQ.matches) { if (l) l.remove(); if (r) r.remove(); return; }
	if (!l) {
		l = document.createElement('button'); l.type = 'button'; l.className = 'dsmb-arr dsmb-l'; l.setAttribute('aria-label', 'Previous ' + key); l.innerHTML = '&#10216;';
		r = document.createElement('button'); r.type = 'button'; r.className = 'dsmb-arr dsmb-r'; r.setAttribute('aria-label', 'More ' + key); r.innerHTML = '&#10217;';
		l.onclick = function(e){ e.stopPropagation(); scroller.scrollBy({left:-scroller.clientWidth * 0.8, behavior:'smooth'}); };
		r.onclick = function(e){ e.stopPropagation(); scroller.scrollBy({left:scroller.clientWidth * 0.8, behavior:'smooth'}); };
		host.appendChild(l); host.appendChild(r);
	}
	var max = scroller.scrollWidth - scroller.clientWidth;
	l.disabled = scroller.scrollLeft <= 2; r.disabled = scroller.scrollLeft >= max - 2;
}
var lastActive = null;
function keepActive(){
	var th = $('dsp-ppt-thumbs'); if (!th) return;
	var a = th.querySelector('.dsp-thumb-item.dsp-thumb-active');
	if (!a || a === lastActive) return; lastActive = a;
	var ar = a.getBoundingClientRect(), tr = th.getBoundingClientRect();
	if (ar.left < tr.left || ar.right > tr.right) th.scrollBy({left:ar.left - tr.left - (tr.width - ar.width) / 2, behavior:'smooth'});
}
function stripHeight(){
	var p = $('dsp-vp-panel'); if (!p) return;
	var c = p.querySelector('.vp-card'), h = 132;
	if (c && c.offsetHeight) h = Math.round(c.offsetHeight + 46);
	var v = h + 'px', b = $('dsp-ppt-bottom-bar'), bv = ((b && b.offsetHeight) || 60) + 'px';
	if (document.documentElement.style.getPropertyValue('--dsmb-bar') !== bv) document.documentElement.style.setProperty('--dsmb-bar', bv);
	if (document.documentElement.style.getPropertyValue('--dsmb-strip') !== v) document.documentElement.style.setProperty('--dsmb-strip', v);
}

var wasOn = false;
function tick(){
	hookZoom();
	var act = on();
	if (act) {
		arrows($('dsp-ppt-panel-left'), $('dsp-ppt-thumbs'), 'slides');
		var p = $('dsp-vp-panel'); arrows(p, p && p.querySelector('.vp-scroll'), 'layouts');
		stripHeight(); keepActive();
		if (!wasOn) { userZoom = false; lastKey = ''; }
		refit(false);
	} else if (wasOn) {
		clearZoom(); lastKey = '';
		if (!MQ.matches) { arrows($('dsp-ppt-panel-left'), $('dsp-ppt-thumbs'), ''); var p2 = $('dsp-vp-panel'); arrows(p2, p2 && p2.querySelector('.vp-scroll'), ''); var o = $('dsp-ppt-slide-outer'); if (o) o.style.transform = 'scale(1)'; var lb = $('dsp-ppt-zoom-lbl'); if (lb) lb.textContent = '100%'; Z = 1; }
	}
	wasOn = act;
}
setInterval(tick, 400);
window.addEventListener('resize', function(){ if (!poking) setTimeout(function(){ refit(false); }, 60); });
window.addEventListener('orientationchange', function(){ setTimeout(function(){ userZoom = false; refit(true); }, 250); });
})();
