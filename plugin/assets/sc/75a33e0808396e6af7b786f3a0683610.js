
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
