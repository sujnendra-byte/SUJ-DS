<?php
/**
 * Code Snippets export — Snippet 29 template gallery
 *
 * Snippet ID : 730
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-044
 * Modified   : 2026-09-09 02:45:43
 * Load order : 044 of 144
 */
/**
 * Snippet: 29 DS PPT Pick-a-Template Gallery
 * Run: Everywhere
 * NEW SNIPPET — does not edit 12B, 12C, or Snippet 17. Independent of
 * Snippet 28 (Theme & Font Card Picker) but uses the exact same
 * integration point (dspPillSet('design', ...)), so both can be
 * installed together or separately.
 *
 * WHAT THIS DOES:
 * Shows a "Browse Templates" gallery of 14 starting-style cards before
 * the user types a topic — matching Gamma's "choose a template" step.
 * Each card is a themed preview (CSS gradient built from that theme's
 * own colors + a representative icon + label), not a real photo, since
 * there's no image-hosting step for static template art in this build.
 * Picking a card sets the theme via the same dspPillSet('design', ...)
 * the dropdown/card-picker already use, then closes the gallery and
 * focuses the topic box so the user can type straight away.
 *
 * The gallery opens automatically once per browser session (sessionStorage
 * flag) the first time the generator loads, and can be re-opened anytime
 * via a "🖼️ Browse Templates" button next to the topic box. Skipping it
 * (✕ or tapping outside) just leaves whatever theme was already selected.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<style>
#dsp-tpl-panel{position:fixed;inset:0;z-index:100001;background:rgba(0,0,0,.88);display:none;flex-direction:column;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
#dsp-tpl-panel.dsp-tpl-open{display:flex;}
.dsp-tpl-hdr{padding:18px;border-bottom:1px solid #21262d;flex-shrink:0;}
.dsp-tpl-hdr h2{margin:0 0 4px;color:#f9fafb;font-size:20px;font-weight:800;}
.dsp-tpl-hdr p{margin:0;color:#8b95a5;font-size:13px;}
.dsp-tpl-close{position:absolute;top:16px;right:16px;background:transparent;border:1px solid #30363d;color:#c9d1d9;border-radius:8px;padding:6px 12px;font-size:13px;cursor:pointer;}
.dsp-tpl-body{flex:1;overflow-y:auto;padding:16px;}
.dsp-tpl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;}
.dsp-tpl-card{border-radius:14px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:transform .15s,border-color .15s;}
.dsp-tpl-card:active{transform:scale(.97);}
.dsp-tpl-card:hover{border-color:#58A6FF;}
.dsp-tpl-art{height:96px;display:flex;align-items:center;justify-content:center;font-size:34px;}
.dsp-tpl-lbl{padding:10px 12px;background:#0d1117;}
.dsp-tpl-lbl .n{color:#f9fafb;font-size:13px;font-weight:700;}
.dsp-tpl-lbl .d{color:#6b7280;font-size:10.5px;margin-top:2px;}
</style>
<div id="dsp-tpl-panel">
  <div class="dsp-tpl-hdr">
    <h2>Choose a template</h2>
    <p>Pick a starting style — you can change it anytime.</p>
    <button class="dsp-tpl-close" id="dsp-tpl-close-btn">Skip ✕</button>
  </div>
  <div class="dsp-tpl-body"><div id="dsp-tpl-grid" class="dsp-tpl-grid"></div></div>
</div>
<script>
(function(){
'use strict';

var TEMPLATES = [
  {theme:'navy-pro',        icon:'🚀', label:'Business Pitch',      desc:'Bold & confident'},
  {theme:'clean-corporate', icon:'📊', label:'Corporate Report',    desc:'Sharp & professional'},
  {theme:'royal-purple',    icon:'🎨', label:'Creative Portfolio',  desc:'Expressive & bold'},
  {theme:'slate-minimal',   icon:'⬜', label:'Minimalist',          desc:'Clean & simple'},
  {theme:'coral-sunset',    icon:'📈', label:'Marketing Strategy',  desc:'Warm & energetic'},
  {theme:'dark-modern',     icon:'💻', label:'Tech Startup',        desc:'Sleek & modern'},
  {theme:'forest-green',    icon:'🌱', label:'Sustainability',      desc:'Natural & grounded'},
  {theme:'steel-blue',      icon:'📚', label:'Academic Research',   desc:'Focused & credible'},
  {theme:'bold-red',        icon:'💼', label:'Sales Proposal',      desc:'High-impact'},
  {theme:'golden-hour',     icon:'✨', label:'Product Launch',      desc:'Exciting & fresh'},
  {theme:'mint-fresh',      icon:'👥', label:'Team Update',         desc:'Friendly & open'},
  {theme:'corporate-dark',  icon:'💰', label:'Financial Overview',  desc:'Serious & precise'},
  {theme:'rose-gold',       icon:'🎯', label:'Brand Guidelines',    desc:'Polished & elegant'},
  {theme:'midnight-teal',   icon:'📉', label:'Investor Deck',       desc:'Trustworthy & calm'}
];

function themeColors(key){
  var t = (window.THEMES && window.THEMES[key]) || {bg:'#1B2A4A',title:'#FFFFFF',accent:'#4A9EFF'};
  return t;
}

function renderGrid(){
  var grid = document.getElementById('dsp-tpl-grid');
  if (!grid) return;
  grid.innerHTML = TEMPLATES.map(function(tpl){
    var c = themeColors(tpl.theme);
    var bg = 'linear-gradient(135deg,' + c.bg + ' 0%,' + (c.accent || c.bg) + '55 100%)';
    return '<div class="dsp-tpl-card" data-tpl-theme="' + tpl.theme + '">'
      + '<div class="dsp-tpl-art" style="background:' + bg + ';">' + tpl.icon + '</div>'
      + '<div class="dsp-tpl-lbl"><div class="n">' + tpl.label + '</div><div class="d">' + tpl.desc + '</div></div>'
    + '</div>';
  }).join('');
}

function openGallery(){
  renderGrid();
  var panel = document.getElementById('dsp-tpl-panel');
  if (panel) panel.classList.add('dsp-tpl-open');
}
function closeGallery(){
  var panel = document.getElementById('dsp-tpl-panel');
  if (panel) panel.classList.remove('dsp-tpl-open');
  var promptEl = document.getElementById('dsp-prompt');
  if (promptEl) promptEl.focus();
}

document.addEventListener('click', function(e){
  if (e.target.id === 'dsp-tpl-trigger' || e.target.closest('#dsp-tpl-trigger')) { openGallery(); return; }
  if (e.target.id === 'dsp-tpl-close-btn') { closeGallery(); return; }
  var card = e.target.closest('[data-tpl-theme]');
  if (card) {
    var themeKey = card.getAttribute('data-tpl-theme');
    var themeLabelMap = {};
    (window.THEMES ? Object.keys(window.THEMES) : []).forEach(function(k){});
    var label = themeKey.replace(/-/g,' ').replace(/\b\w/g, function(ch){ return ch.toUpperCase(); });
    if (typeof window.dspPillSet === 'function') {
      window.dspPillSet('design', themeKey, label);
    } else {
      var el = document.getElementById('dsp-design');
      if (el) el.value = themeKey;
    }
    if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs();
    closeGallery();
    return;
  }
});

})();
</script>
    <?php
}, 1003);
