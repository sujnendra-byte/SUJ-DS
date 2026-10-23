<?php
/**
 * Code Snippets export — DS Edit + Add Points button - above the slide in the editor
 *
 * Snippet ID : 1180
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : ds-edit-points-button
 * Modified   : 2026-10-10 10:23:06
 * Load order : 345 of 345
 */
/* DS Edit + Add Points button - a button above the top-left corner of the slide in the editor.
 * It opens the same Edit Slide window as the toolbar button (edit the text, add, remove or reorder points).
 * On title, quote and closing slides it reads 'Edit Slide'. New snippet; nothing existing is edited. */
if ( ! defined( 'ABSPATH' ) ) return;

add_action( 'wp_footer', function () {
	if ( ! function_exists( 'ds_tpl_should_print' ) || ! ds_tpl_should_print() ) return;
	echo "<script>\n" . <<<'DSJS'
(function(){
'use strict';
if (window.__dsEapBtn) return; window.__dsEapBtn = 1;
var CSS = '#ds-eap-btn{position:absolute;left:0;bottom:100%;margin:0 0 10px 0;z-index:6;display:none;align-items:center;gap:7px;height:34px;padding:0 14px;border:1px solid #D5DAE3;border-radius:9px;background:#FFFFFF;color:#111827;font:600 13px/1 Inter,system-ui,-apple-system,Segoe UI,Arial,sans-serif;letter-spacing:0!important;text-transform:none!important;white-space:nowrap;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.28);transition:background .15s,border-color .15s,transform .15s}'
  + '#ds-eap-btn:hover{background:#EEF4FF;border-color:#2563EB;color:#1D4ED8}'
  + '#ds-eap-btn:active{transform:translateY(1px)}'
  + '#ds-eap-btn:focus-visible{outline:2px solid #2563EB;outline-offset:2px}'
  + '#ds-eap-btn svg{width:15px;height:15px;flex:none}'
  + '#dsp-editor-fullscreen.dsp-open #ds-eap-btn.ds-eap-on{display:inline-flex}'
  + '@media (prefers-reduced-motion:reduce){#ds-eap-btn{transition:none}}';
var ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
function opener(){ return document.querySelector('#dsp-editor-fullscreen [data-a="edslide"]'); }
function kind(){ var l = document.getElementById('vp-lbl'), m = l && /·\s*([a-z_]+)/i.exec(l.textContent || ''); return m ? m[1].toLowerCase() : ''; }
function ensure(){
  var outer = document.getElementById('dsp-ppt-slide-outer'); if (!outer) return;
  var b = document.getElementById('ds-eap-btn');
  if (!b) {
    if (!document.getElementById('ds-eap-css')) { var st = document.createElement('style'); st.id = 'ds-eap-css'; st.textContent = CSS; document.head.appendChild(st); }
    b = document.createElement('button'); b.type = 'button'; b.id = 'ds-eap-btn';
    b.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); var o = opener(); if (o) o.click(); });
    b.addEventListener('pointerdown', function(ev){ ev.stopPropagation(); });
    b.addEventListener('mousedown', function(ev){ ev.stopPropagation(); });
  }
  if (b.parentNode !== outer) outer.appendChild(b);
  var k = kind(), plain = (k === 'title' || k === 'closing' || k === 'quote'), txt = plain ? 'Edit Slide' : 'Edit + Add Points';
  if (b.getAttribute('data-t') !== txt) { b.setAttribute('data-t', txt); b.innerHTML = ICON + '<span>' + txt + '</span>'; b.title = plain ? 'Edit the text of this slide' : 'Edit the text of this slide and add, remove or reorder its points'; }
  b.classList.toggle('ds-eap-on', !!opener());
}
var t = null;
function kick(){ if (t) return; t = setTimeout(function(){ t = null; try { ensure(); } catch (e) {} }, 120); }
function start(){
  kick();
  try { new MutationObserver(kick).observe(document.body, {childList: true, subtree: true}); } catch (e) { setInterval(kick, 1200); }
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
DSJS
	. "\n</script>\n";
}, 2010 );
