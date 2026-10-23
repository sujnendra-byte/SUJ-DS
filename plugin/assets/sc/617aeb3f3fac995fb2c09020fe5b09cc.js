
(function(){
if (window.__dspk) return; window.__dspk = 2;
var KINDS = {tpl:{btn:'dste-more', grid:'dste-grid', card:'.dste-card', title:'Design Templates', word:'template', nm:'.dste-nm'}, th:{btn:'dsp-th-more-btn', grid:'dsp-th-grid', card:'.dsp-th-card', title:'Colour & Typography Themes', word:'theme', nm:'.dsp-th-name'}};
var S = null, pass = false;
function $(id){ return document.getElementById(id); }
function nameOf(card, K){ if (card.getAttribute('data-th-label')) return card.getAttribute('data-th-label'); var e = card.querySelector(K.nm), t = ''; if (e) { var c = e.cloneNode(true), x = c.querySelectorAll('small, span'); for (var i = 0; i < x.length; i++) if (x[i].parentNode) x[i].parentNode.removeChild(x[i]); t = c.textContent; } if (!t || !t.trim()) t = card.textContent || ''; return t.replace(/\s+/g, ' ').replace(/[✓✔]/g, '').trim().slice(0, 60); }
function floatPos(){
if (!S || !S.pend || !S.fl) return; var r = S.pend.getBoundingClientRect(), b = S.body.getBoundingClientRect(), cy = r.top + r.height * 0.42;
if (r.bottom < b.top + 10 || r.top > b.bottom - 10) { S.fl.style.display = 'none'; return; }
S.fl.style.display = ''; S.fl.style.left = (S.K.word === 'theme' ? r.right - 58 : r.left + r.width / 2) + 'px'; S.fl.style.top = Math.max(b.top + 26, Math.min(b.bottom - 26, cy)) + 'px';
}
function open(kind){
var K = KINDS[kind], grid = $(K.grid), host = $('dsp-home') || document.body; if (!grid || S) return;
var n = grid.querySelectorAll(K.card).length, h = grid.getBoundingClientRect().height, top = grid.scrollTop;
var back = document.createElement('div'); back.className = 'dspk-back';
var fr = document.createElement('div'); fr.className = 'dspk-frame'; fr.setAttribute('role', 'dialog'); fr.setAttribute('aria-modal', 'true'); fr.setAttribute('aria-label', K.title);
fr.innerHTML = '<div class="dspk-head"><h3></h3><p></p><button type="button" class="dspk-x" aria-label="Close">×</button></div><div class="dspk-body"></div><div class="dspk-foot"><span></span><button type="button" class="dspk-btn dspk-cancel">Cancel</button><button type="button" class="dspk-btn dspk-go" disabled>Select</button></div>';
fr.querySelector('h3').textContent = K.title + ' (' + n + ')'; fr.querySelector('p').textContent = 'Click a ' + K.word + ', then press Select.'; fr.querySelector('.dspk-foot span').textContent = 'Nothing chosen yet';
var sp = document.createElement('div'); sp.style.height = h + 'px'; sp.className = 'dspk-spacer';
grid.parentNode.insertBefore(sp, grid); host.appendChild(back); host.appendChild(fr);
S = {K:K, grid:grid, back:back, fr:fr, sp:sp, body:fr.querySelector('.dspk-body'), pend:null, fl:null, top:top};
S.body.appendChild(grid); grid.classList.add('dspk-grid'); document.documentElement.classList.add('dspk-lock');
var cur = grid.querySelector(K.card + '.dste-on, ' + K.card + '.dsp-th-sel'); if (cur) { try { grid.scrollTop = Math.max(0, cur.offsetTop - 90); } catch (e) {} }
back.addEventListener('click', function(){ close(false); }); fr.querySelector('.dspk-x').addEventListener('click', function(){ close(false); }); fr.querySelector('.dspk-cancel').addEventListener('click', function(){ close(false); }); fr.querySelector('.dspk-go').addEventListener('click', function(){ close(true); });
grid.addEventListener('scroll', floatPos, {passive:true}); window.addEventListener('resize', floatPos);
try { S.mo = new MutationObserver(function(){ if (!S) return; S.fr.querySelector('h3').textContent = K.title + ' (' + grid.querySelectorAll(K.card).length + ')'; if (S.pend && !grid.contains(S.pend)) { S.pend = null; S.fr.querySelector('.dspk-go').disabled = true; S.fr.querySelector('.dspk-foot span').textContent = 'Nothing chosen yet'; if (S.fl) S.fl.style.display = 'none'; } floatPos(); }); S.mo.observe(grid, {childList:true}); } catch (e) {}
try { fr.querySelector('.dspk-x').focus(); } catch (e) {}
}
function pick(card){
if (!S) return; if (S.pend) S.pend.classList.remove('dspk-pend'); S.pend = card; card.classList.add('dspk-pend');
if (!S.fl) { S.fl = document.createElement('button'); S.fl.type = 'button'; S.fl.className = 'dspk-float'; S.fl.textContent = 'Select'; S.fl.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); close(true); }); S.fr.appendChild(S.fl); }
S.fr.querySelector('.dspk-foot span').innerHTML = 'Chosen: <b></b>'; S.fr.querySelector('.dspk-foot b').textContent = nameOf(card, S.K) || 'this ' + S.K.word; S.fr.querySelector('.dspk-go').disabled = false; floatPos();
}
function close(apply){
if (!S) return; var s = S, card = apply ? s.pend : null; S = null;
s.grid.removeEventListener('scroll', floatPos); window.removeEventListener('resize', floatPos); if (s.mo) { try { s.mo.disconnect(); } catch (e) {} }
if (s.pend) s.pend.classList.remove('dspk-pend'); s.grid.classList.remove('dspk-grid');
if (s.sp.parentNode) s.sp.parentNode.insertBefore(s.grid, s.sp);
[s.back, s.fr, s.sp].forEach(function(e){ if (e && e.parentNode) e.parentNode.removeChild(e); });
document.documentElement.classList.remove('dspk-lock');
if (card && document.contains(card)) {
pass = true; try { card.click(); } finally { pass = false; }
setTimeout(function(){ try { s.grid.scrollTop = Math.max(0, card.offsetTop - s.grid.offsetTop - 12); } catch (e) {} }, 80);
} else { try { s.grid.scrollTop = s.top; } catch (e) {} }
var b = $(s.K.btn); if (b) { try { b.focus({preventScroll:true}); } catch (e) {} }
}
window.addEventListener('click', function(e){
if (pass || !e.isTrusted) return; var t = e.target; if (!t || !t.closest) return;
var b = t.closest('#dste-more, #dsp-th-more-btn');
if (b && !S) { e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation(); open(b.id === 'dste-more' ? 'tpl' : 'th'); return; }
if (S && S.grid.contains(t)) { var c = t.closest(S.K.card); e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation(); if (c) pick(c); }
}, true);
window.addEventListener('dblclick', function(e){ if (!S || !e.isTrusted || !e.target.closest) return; var c = S.grid.contains(e.target) && e.target.closest(S.K.card); if (c) { e.preventDefault(); e.stopPropagation(); pick(c); close(true); } }, true);
window.addEventListener('keydown', function(e){
if (!S) return; if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); close(false); return; }
if (e.key === 'Enter' && S.pend && !(e.target && e.target.closest && e.target.closest('.dspk-frame button'))) { e.preventDefault(); e.stopPropagation(); close(true); }
}, true);
})();
