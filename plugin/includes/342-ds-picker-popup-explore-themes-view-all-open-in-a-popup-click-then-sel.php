<?php
/**
 * Code Snippets export — DS Picker Popup - Explore Themes / View All open in a popup (click, then Select)
 *
 * Snippet ID : 1176
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : ds-template-pack-90-129
 * Modified   : 2026-10-10 06:01:35
 * Load order : 342 of 345
 */
/**
 * DS Picker Popup - "Explore 36 Themes" and "View All" open in a popup window
 * Run: Front end only
 *
 * On the AI Presentation home screen the two links used to stretch the panel down the page.
 * With this snippet they open a popup instead:
 *  - click a theme or a template in the popup: it is outlined and a Select button appears on it (and in the popup footer)
 *  - click Select: the choice is applied exactly as a normal click would apply it, and the popup closes
 *  - Cancel, the X, the dark area or the Esc key close the popup without changing anything
 * The popup shows the real picker cards (same thumbnails, same order), so every template and theme works as before.
 * Only real clicks by the visitor are caught; clicks made by other snippets pass through untouched.
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet - the links stretch the panel again.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<style id="ds-picker-popup-css">
.dspk-back{position:fixed;inset:0;background:rgba(4,8,18,.74);z-index:99990;}
.dspk-frame{position:fixed;inset:0;margin:auto;width:min(1180px,calc(100vw - 32px));height:min(820px,calc(100vh - 32px));background:#0f1629;border:1px solid #27324d;border-radius:18px;box-shadow:0 30px 80px rgba(0,0,0,.6);z-index:99991;display:flex;flex-direction:column;font-family:inherit;color:#e8ecf6;}
.dspk-head{display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid #222c45;flex:0 0 auto;}
.dspk-head h3{margin:0;font-size:19px;font-weight:700;color:#fff;line-height:1.2;}
.dspk-head p{margin:0;font-size:13px;color:#9aa6c2;flex:1;}
.dspk-x{appearance:none;border:1px solid #2c3858;background:#18213a;color:#dfe5f3;border-radius:10px;width:38px;height:38px;font-size:24px;line-height:34px;padding:0;text-align:center;cursor:pointer;flex:0 0 auto;}
.dspk-x:hover{background:#22305a;}
.dspk-body{flex:1 1 auto;min-height:0;overflow:hidden;}
.dspk-foot{display:flex;align-items:center;gap:12px;padding:12px 20px;border-top:1px solid #222c45;flex:0 0 auto;flex-wrap:wrap;}
.dspk-foot span{flex:1;min-width:140px;font-size:14px;color:#9aa6c2;}
.dspk-foot span b{color:#fff;font-weight:600;}
.dspk-btn{appearance:none;border:1px solid #2c3858;background:#18213a;color:#dfe5f3;border-radius:10px;padding:10px 18px;font-size:14px;font-weight:600;cursor:pointer;}
.dspk-btn.dspk-go{background:#2f6bff;border-color:#2f6bff;color:#fff;}
.dspk-btn.dspk-go[disabled]{opacity:.45;cursor:not-allowed;}
.dspk-float{position:fixed;z-index:99994;appearance:none;border:0;background:#2f6bff;color:#fff;border-radius:999px;padding:9px 22px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.5),0 0 0 3px rgba(255,255,255,.9);transform:translate(-50%,-50%);}
.dspk-float:hover{background:#1f57e6;}
#dsp-home.dssl .dspk-body > .dspk-grid{position:static!important;width:100%!important;height:100%!important;max-height:none!important;min-height:0!important;overflow-y:auto!important;overflow-x:hidden!important;display:grid!important;gap:14px!important;padding:18px 20px!important;margin:0!important;box-sizing:border-box!important;align-content:start!important;grid-auto-rows:max-content!important;background:transparent!important;border:0!important;border-radius:0!important;}
#dsp-home.dssl #dste-grid.dspk-grid{grid-template-columns:repeat(auto-fill,minmax(210px,1fr))!important;}
#dsp-home.dssl #dsp-th-grid.dspk-grid{grid-template-columns:repeat(auto-fill,minmax(170px,1fr))!important;}
#dsp-home.dssl .dspk-body .dspk-pend{outline:3px solid #2f6bff!important;outline-offset:2px!important;}
html.dspk-lock,html.dspk-lock body{overflow:hidden!important;}
@media (max-width:640px){.dspk-head p{display:none}.dspk-frame{width:calc(100vw - 12px);height:calc(100vh - 12px);border-radius:12px}#dsp-home.dssl #dste-grid.dspk-grid{grid-template-columns:repeat(2,1fr)!important}#dsp-home.dssl #dsp-th-grid.dspk-grid{grid-template-columns:repeat(2,1fr)!important}}
</style>
<script id="ds-picker-popup">
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
</script>
    <?php
}, 1200);
