
(function(){
'use strict';

var THEMES24 = [
  {key:'navy-pro',       label:'Navy Pro',       bg:'#1B2A4A', tc:'#FFFFFF', bc:'#C8D8F0', ac:'#4A9EFF', font:'Inter'},
  {key:'dark-modern',    label:'Dark Modern',    bg:'#0D1117', tc:'#FFFFFF', bc:'#C9D1D9', ac:'#58A6FF', font:'Inter'},
  {key:'clean-corporate',label:'Clean Corporate',bg:'#FFFFFF', tc:'#1A365D', bc:'#2D3748', ac:'#2B6CB0', font:'Roboto'},
  {key:'bold-red',       label:'Bold Red',       bg:'#1A0000', tc:'#FFFFFF', bc:'#FFD0D0', ac:'#FF4444', font:'Oswald'},
  {key:'corporate-dark', label:'Corporate Dark', bg:'#111827', tc:'#F9FAFB', bc:'#D1D5DB', ac:'#6366F1', font:'IBM Plex Sans'},
  {key:'clean-white',    label:'Clean White',    bg:'#F8FAFC', tc:'#0F172A', bc:'#334155', ac:'#0EA5E9', font:'Work Sans'},
  {key:'gradient-pro',   label:'Gradient Pro',   bg:'#0F0C29', tc:'#FFFFFF', bc:'#E0D7FF', ac:'#A78BFA', font:'Space Grotesk'},
  {key:'bold-impact',    label:'Bold Impact',    bg:'#111111', tc:'#FFFFFF', bc:'#CCCCCC', ac:'#FFD700', font:'Bebas Neue'},
  {key:'sunset-glow',    label:'Sunset Glow',    bg:'#2D1B2E', tc:'#FFFFFF', bc:'#F4C9D9', ac:'#FF6B9D', font:'Poppins'},
  {key:'forest-green',   label:'Forest Green',   bg:'#0F2419', tc:'#FFFFFF', bc:'#C8E6D0', ac:'#4ADE80', font:'Nunito'},
  {key:'ocean-breeze',   label:'Ocean Breeze',   bg:'#F0F9FF', tc:'#0C4A6E', bc:'#334155', ac:'#0284C7', font:'DM Sans'},
  {key:'golden-hour',    label:'Golden Hour',    bg:'#1C1408', tc:'#FFFFFF', bc:'#F5DEB3', ac:'#F59E0B', font:'Playfair Display'},
  {key:'lavender-mist',  label:'Lavender Mist',  bg:'#F5F3FF', tc:'#3B0764', bc:'#4C1D95', ac:'#8B5CF6', font:'Quicksand'},
  {key:'slate-minimal',  label:'Slate Minimal',  bg:'#F8F9FA', tc:'#1E293B', bc:'#475569', ac:'#64748B', font:'Inter'},
  {key:'cherry-bold',    label:'Cherry Bold',    bg:'#FFF5F5', tc:'#7F1D1D', bc:'#450A0A', ac:'#DC2626', font:'Montserrat'},
  {key:'midnight-teal',  label:'Midnight Teal',  bg:'#042F2E', tc:'#FFFFFF', bc:'#99F6E4', ac:'#2DD4BF', font:'Josefin Sans'},
  {key:'coral-sunset',   label:'Coral Sunset',   bg:'#FFF7ED', tc:'#7C2D12', bc:'#9A3412', ac:'#FB923C', font:'Raleway'},
  {key:'royal-purple',   label:'Royal Purple',   bg:'#1E0A3C', tc:'#FFFFFF', bc:'#DDD6FE', ac:'#A855F7', font:'Playfair Display'},
  {key:'mint-fresh',     label:'Mint Fresh',     bg:'#F0FDF4', tc:'#14532D', bc:'#166534', ac:'#22C55E', font:'Nunito'},
  {key:'rose-gold',      label:'Rose Gold',      bg:'#FFF1F2', tc:'#881337', bc:'#9F1239', ac:'#FB7185', font:'Merriweather'},
  {key:'steel-blue',     label:'Steel Blue',     bg:'#0F172A', tc:'#F1F5F9', bc:'#94A3B8', ac:'#38BDF8', font:'Source Sans Pro'},
  {key:'amber-warm',     label:'Amber Warm',     bg:'#1C1917', tc:'#FEF3C7', bc:'#D6D3D1', ac:'#FBBF24', font:'Crimson Text'},
  {key:'arctic-white',   label:'Arctic White',   bg:'#FFFFFF', tc:'#0F172A', bc:'#334155', ac:'#06B6D4', font:'Work Sans'},
  {key:'crimson-night',  label:'Crimson Night',  bg:'#1A0A0A', tc:'#FFFFFF', bc:'#FCA5A5', ac:'#EF4444', font:'Libre Baskerville'},
	{key:'aurora',        label:'Aurora',         bg:'linear-gradient(135deg,#0F2027,#1A3A4A)', tc:'#FFFFFF', bc:'#B8D4DE', ac:'#4FD1C5', font:'Poppins'},
  {key:'sunset-blaze',  label:'Sunset Blaze',   bg:'linear-gradient(135deg,#7B0000,#DD2476)', tc:'#FFFFFF', bc:'#FFE0E9', ac:'#FFD700', font:'Montserrat'},
  {key:'cosmic-purple', label:'Cosmic Purple',  bg:'linear-gradient(135deg,#1A0050,#4A00E0)', tc:'#FFFFFF', bc:'#E0D4FF', ac:'#00E5FF', font:'Space Grotesk'},
  {key:'ocean-depth',   label:'Ocean Depth',    bg:'linear-gradient(135deg,#000028,#003366)', tc:'#FFFFFF', bc:'#CDEFFF', ac:'#FFD93D', font:'DM Sans'},
  {key:'emerald-dusk',  label:'Emerald Dusk',   bg:'linear-gradient(135deg,#0A2E1A,#134E5E)', tc:'#FFFFFF', bc:'#DFF5E8', ac:'#FFC857', font:'Nunito'},
  {key:'rose-gold-glow',label:'Rose Gold Glow', bg:'linear-gradient(135deg,#0D0720,#1D2671)', tc:'#FFFFFF', bc:'#FFD9E6', ac:'#FFD700', font:'Playfair Display'},
  {key:'amber-blaze',   label:'Amber Blaze',    bg:'linear-gradient(135deg,#1A0A00,#7A3800)', tc:'#FFFFFF', bc:'#FFD9A0', ac:'#FFA500', font:'Poppins'},
  {key:'midnight-bloom',label:'Midnight Bloom', bg:'linear-gradient(135deg,#0B0020,#0B3D47)', tc:'#FFFFFF', bc:'#D9F7FA', ac:'#F72585', font:'Josefin Sans'},
	{key:'deep-navy',     label:'Deep Navy',      bg:'linear-gradient(135deg,#0A1628,#0D3B6E)', tc:'#FFFFFF', bc:'#C8D8F0', ac:'#4A9EFF', font:'Inter'},
  {key:'dark-crimson',  label:'Dark Crimson',   bg:'linear-gradient(135deg,#1A0000,#5C0000)', tc:'#FFFFFF', bc:'#FFD0D0', ac:'#FF6666', font:'Montserrat'},
  {key:'dark-slate',    label:'Dark Slate',     bg:'linear-gradient(135deg,#0F172A,#1E293B)', tc:'#F1F5F9', bc:'#94A3B8', ac:'#38BDF8', font:'Inter'},
  {key:'dark-forest',   label:'Dark Forest',    bg:'linear-gradient(135deg,#052010,#0A3020)', tc:'#FFFFFF', bc:'#C8E6D0', ac:'#4ADE80', font:'Nunito'},
];
var POPULAR_KEYS = ['navy-pro','clean-corporate','sunset-glow','forest-green','rose-gold','royal-purple','ocean-breeze','cherry-bold'];

function themeByKey(key){ for(var i=0;i<THEMES24.length;i++) if(THEMES24[i].key===key) return THEMES24[i]; return null; }

function extendThemes(){
  if (window.THEMES) THEMES24.forEach(function(c){ if(!window.THEMES[c.key]) window.THEMES[c.key]={bg:c.bg,title:c.tc,body:c.bc,accent:c.ac}; });
  if (window.TH) THEMES24.forEach(function(c){ if(!window.TH[c.key]) window.TH[c.key]={bg:c.bg,tc:c.tc,bc:c.bc,ac:c.ac}; });
}

function preloadAllCardFonts(){
  var seen={}, families=[];
  THEMES24.forEach(function(c){
    if(seen[c.font])return;
    seen[c.font]=true;
    families.push('family='+c.font.replace(/ /g,'+')+':wght@400;700;800');
  });
  var link=document.getElementById('dsp-th-fontpreload');
  if(!link){ link=document.createElement('link'); link.id='dsp-th-fontpreload'; link.rel='stylesheet'; document.head.appendChild(link); }
  link.href='https://fonts.googleapis.com/css2?'+families.join('&')+'&display=swap';
}

function loadFont(name){
  var fam = name.replace(/ /g,'+');
  var link = document.getElementById('dsp-tf-fontlink');
  if (link) link.href = 'https://fonts.googleapis.com/css2?family=' + fam + ':wght@400;700;800&display=swap';
}
function applyFontLive(name){
  var el = document.getElementById('dsp-tf-live-font');
  if (!el) { el = document.createElement('style'); el.id='dsp-tf-live-font'; document.head.appendChild(el); }
  el.textContent = '#dsp-ppt-main-slide, #dsp-ppt-main-slide *, #dsp-ppt-thumbs, #dsp-ppt-thumbs *{font-family:"' + name + '",sans-serif!important;}';
}
function setFontField(name){
  var fEl = document.getElementById('dsp-font');
  if (!fEl) { fEl = document.createElement('input'); fEl.type='hidden'; fEl.id='dsp-font'; document.body.appendChild(fEl); }
  fEl.value = name;
  loadFont(name);
  applyFontLive(name);
}

function currentThemeKey(){
  var el = document.getElementById('dsp-design');
  return (el && el.value) ? el.value : 'navy-pro';
}

function cardHTML(c, sel){
  return '<div class="dsp-th-card' + (sel?' dsp-th-sel':'') + '" data-th-key="' + c.key + '" data-th-label="' + c.label + '" data-th-font="' + c.font + '">'
    + '<div class="dsp-th-swatch" style="background:' + c.bg + ';">'
      + '<div class="t" style="color:' + c.tc + ';font-family:\'' + c.font + '\',sans-serif;">Title</div>'
      + '<div class="b" style="color:' + c.ac + ';font-family:\'' + c.font + '\',sans-serif;">' + c.font + '</div>'
    + '</div>'
    + '<div class="dsp-th-name">' + c.label + '<span class="ck">✓</span></div>'
  + '</div>';
}

function renderGrid(gridEl, expanded){
  if (!gridEl) return;
  var sel = currentThemeKey();
  var list = expanded ? THEMES24 : THEMES24.filter(function(c){ return POPULAR_KEYS.indexOf(c.key) !== -1; });
  gridEl.innerHTML = list.map(function(c){ return cardHTML(c, c.key === sel); }).join('');
}

var genExpanded = false, edExpanded = false;

function selectTheme(key, label, font){
  if (typeof window.dspPillSet === 'function') window.dspPillSet('design', key, label);
  else { var el = document.getElementById('dsp-design'); if (el) el.value = key; }
  setFontField(font);
  if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs();
  if (window.S && typeof window.S.idx === 'number' && window.S.idx >= 0 && typeof window.applyCanvas === 'function') {
    var curVariant = (window.S.choices && window.S.choices['slide_' + window.S.idx]) || 'v1';
    window.applyCanvas(window.S.idx, curVariant);
  }
  renderGrid(document.getElementById('dsp-th-grid'), genExpanded);
  if (document.getElementById('dsp-ed-theme-drawer')) { renderGrid(document.getElementById('dsp-ed-drawer-row'), true); toggleDrawer(false); }
}

document.addEventListener('click', function(e){
  if (e.target.id === 'dsp-th-more-btn') { genExpanded = !genExpanded; e.target.textContent = genExpanded ? '🎨 Show less' : '🎨 View more'; renderGrid(document.getElementById('dsp-th-grid'), genExpanded); return; }
  var card = e.target.closest('[data-th-key]');
  if (card) { selectTheme(card.getAttribute('data-th-key'), card.getAttribute('data-th-label'), card.getAttribute('data-th-font')); return; }
});

function mountGenSection(){
  var costbar = document.getElementById('dsp-costbar');
  var section = document.getElementById('dsp-theme-section');
  if (!costbar || !section || section.classList.contains('dsp-th-mounted')) return;
  section.classList.remove('dsp-th-hidden-row');
  section.classList.add('dsp-th-mounted');
  costbar.parentNode.insertBefore(section, costbar);
  renderGrid(document.getElementById('dsp-th-grid'), genExpanded);
}

function findPaginationStrip(){
  var all = document.querySelectorAll('body *');
  for (var i = 0; i < all.length; i++) {
    var el = all[i];
    if (el.children.length > 0) continue;
    var txt = (el.textContent || '').trim();
    if (!/^\d+\s*\/\s*\d+$/.test(txt)) continue;
    var node = el;
    for (var depth = 0; depth < 5 && node.parentElement; depth++) {
      node = node.parentElement;
      if (/\d+%/.test(node.textContent || '')) return node;
    }
    return el.parentElement;
  }
  return null;
}

function isVisible(el){
  var node = el;
  while (node && node.nodeType === 1) {
    var style = window.getComputedStyle(node);
    if (style.display === 'none' || style.visibility === 'hidden') return false;
    node = node.parentElement;
  }
  return true;
}

function isInEditor(){
  var btns = document.querySelectorAll('button');
  var candidates = [];
  for (var i = 0; i < btns.length; i++) {
    if (/download\s*pptx/i.test(btns[i].textContent) && !btns[i].closest('#ds30-screen, #ds30-preview-modal, #ds31-success, [id^="ds30-"], [id^="ds31-"]')) { /* FIX 2026-09-16: template gallery Download button is not the editor - no theme popup there */
      candidates.push({ text: btns[i].textContent.trim(), visible: isVisible(btns[i]) });
    }
  }
  var result = candidates.some(function(c){ return c.visible; });
  return result;
}

function mountEditorDrawer(){
  if (!isInEditor() || document.getElementById('dsp-ed-theme-trigger')) return;

  var trigger = document.createElement('button');
  trigger.id = 'dsp-ed-theme-trigger';
  trigger.type = 'button';
  trigger.innerHTML = '🎨 Theme';
  trigger.onclick = function(){ toggleDrawer(true); };

  var bar = document.getElementById('dsp-ppt-bottom-bar');
  if (bar) {
    trigger.classList.add('dsp-ed-theme-trigger-barfixed');
    bar.insertBefore(trigger, bar.firstChild);
  } else {
    trigger.classList.add('dsp-ed-theme-trigger-fixed');
    document.body.appendChild(trigger);
  }

  var drawer = document.createElement('div');
  drawer.id = 'dsp-ed-theme-drawer';
  drawer.innerHTML =
    '<div class="dsp-th-hdr">' +
      '<div><h4>Select theme</h4></div>' +
      '<button class="dsp-th-more" id="dsp-ed-drawer-close" type="button">Close ✕</button>' +
    '</div>' +
    '<div id="dsp-ed-drawer-row" class="dsp-th-row"></div>';
  document.body.appendChild(drawer);

  var scrim = document.createElement('div');
  scrim.id = 'dsp-ed-theme-scrim';
  document.body.appendChild(scrim);

  document.getElementById('dsp-ed-drawer-close').onclick = function(){ toggleDrawer(false); };
  scrim.onclick = function(){ toggleDrawer(false); };

  /* FIX 2026-09-16: no auto-open - theme panel opens only from the Theme button */
}

function toggleDrawer(open){
  var drawer = document.getElementById('dsp-ed-theme-drawer');
  var scrim = document.getElementById('dsp-ed-theme-scrim');
  if (!drawer || !scrim) return;
  if (open) {
    renderGrid(document.getElementById('dsp-ed-drawer-row'), true);
    drawer.classList.add('dsp-ed-drawer-open');
    scrim.classList.add('dsp-ed-scrim-open');
  } else {
    drawer.classList.remove('dsp-ed-drawer-open');
    scrim.classList.remove('dsp-ed-scrim-open');
  }
}

	window.dspApplyThemeFont = function(themeKey){
  var theme = themeByKey(themeKey);
  if (theme) setFontField(theme.font);
};
	
extendThemes();
preloadAllCardFonts();
// Both sections can appear at any time — the editor panel in particular
// often doesn't exist until well after page load, since generation
// (topic -> AI -> editor rendering) can take much longer than any fixed
// polling window would reasonably wait. A MutationObserver reacts the
// instant either target appears, with no timeout to silently miss it.
function dspThTryMountAll(){
  mountGenSection();
  mountEditorDrawer();
}
var dspThObserver = new MutationObserver(dspThTryMountAll);
dspThObserver.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
dspThTryMountAll();

})();
