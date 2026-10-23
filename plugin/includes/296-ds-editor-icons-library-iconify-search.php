<?php
/**
 * Code Snippets export — DS Editor Icons Library (Iconify search)
 *
 * Snippet ID : 1126
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-09 16:57:06
 * Load order : 296 of 297
 */
add_action('wp_footer', function () {
	if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
	?>
<style id="ds-iclib-css">
/* DS Editor Icons Library */
#dsel-dock .dsel-iclib-b{display:inline-flex;align-items:center;justify-content:center;gap:5px}
#dsel-icpop{display:none;position:fixed;z-index:100011;width:560px;height:480px;max-height:480px;flex-direction:column;background:#fff;border-radius:12px;box-shadow:0 10px 34px rgba(0,0,0,.42);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif;color:#0F172A;overflow:hidden;box-sizing:border-box}
#dsel-icpop *{box-sizing:border-box}
#dsel-icpop.dsel-ic-drag{box-shadow:0 16px 44px rgba(0,0,0,.5)}
#dsel-icpop .dsel-ic-h{display:flex;align-items:center;justify-content:space-between;padding:11px 12px 8px 14px;cursor:move;user-select:none;touch-action:none;flex:none}
#dsel-icpop .dsel-ic-h b{font-size:13px;font-weight:700;color:#0F172A}
#dsel-icpop .dsel-ic-h b::before{content:"\2807";display:inline-block;margin-right:6px;color:#94A3B8;font-weight:400;letter-spacing:-2px}
#dsel-icpop button{font-family:inherit;text-transform:none;letter-spacing:0;box-shadow:none;min-height:0;line-height:1.2;margin:0}
#dsel-icpop .dsel-ic-x{width:24px;height:24px;padding:0;border:0;background:transparent;color:#475569;font-size:19px;line-height:1;cursor:pointer;border-radius:6px}
#dsel-icpop .dsel-ic-x:hover{background:#F1F5F9;color:#0F172A}
#dsel-icpop .dsel-ic-top{padding:0 12px 8px;border-bottom:1px solid #E2E8F0;flex:none}
#dsel-icpop .dsel-ic-s{display:flex;align-items:center;gap:6px;height:34px;padding:0 6px 0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#F8FAFC;color:#64748B}
#dsel-icpop .dsel-ic-s:focus-within{border-color:#2563EB;background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
#dsel-icpop .dsel-ic-q{flex:1;min-width:0;height:30px;border:0!important;outline:0!important;background:transparent!important;box-shadow:none!important;padding:0!important;margin:0;font-size:13px;color:#0F172A;font-family:inherit}
#dsel-icpop .dsel-ic-q::-webkit-search-cancel-button{display:none}
#dsel-icpop .dsel-ic-clr{visibility:hidden;width:22px;height:22px;padding:0;border:0;background:transparent;color:#64748B;font-size:16px;cursor:pointer;border-radius:50%}
#dsel-icpop .dsel-ic-clr:hover{background:#E2E8F0;color:#0F172A}
#dsel-icpop .dsel-ic-st{display:flex;gap:4px;margin-top:8px;padding:3px;background:#F1F5F9;border-radius:8px}
#dsel-icpop .dsel-ic-st button{flex:1;height:24px;padding:0;border:0;border-radius:6px;background:transparent;color:#475569;font-size:12px;font-weight:600;cursor:pointer}
#dsel-icpop .dsel-ic-st button.on{background:#fff;color:#1D4ED8;box-shadow:0 1px 3px rgba(15,23,42,.15)}
#dsel-icpop .dsel-ic-main{flex:1 1 auto;min-height:0;display:flex}
#dsel-icpop .dsel-ic-cats{flex:none;width:156px;overflow-y:auto;overscroll-behavior:contain;padding:8px 6px 8px 8px;border-right:1px solid #E2E8F0;background:#F8FAFC;scrollbar-width:thin}
#dsel-icpop .dsel-ic-cats button{display:flex;align-items:center;gap:8px;width:100%;height:30px;padding:0 8px;border:0;border-radius:7px;background:transparent;color:#334155;font-size:12.5px;font-weight:500;text-align:left;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsel-icpop .dsel-ic-cats button:hover{background:#EEF2F7;color:#0F172A}
#dsel-icpop .dsel-ic-cats button.on{background:#E0E7FF;color:#1D4ED8;font-weight:700}
#dsel-icpop .dsel-ic-ci{flex:none;width:16px;height:16px;display:block;color:#64748B}
#dsel-icpop .dsel-ic-cats button.on .dsel-ic-ci{color:#1D4ED8}
#dsel-icpop .dsel-ic-ci svg{display:block;width:16px;height:16px}
#dsel-icpop .dsel-ic-body{flex:1 1 auto;min-width:0;overflow-y:auto;overscroll-behavior:contain;padding:8px 12px 10px}
#dsel-icpop .dsel-ic-info{font-size:11.5px;color:#64748B;padding:4px 0 6px}
#dsel-icpop .dsel-ic-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(46px,1fr));gap:4px}
#dsel-icpop .dsel-ic-g button{aspect-ratio:1;width:100%;height:auto;padding:0;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:8px;background:#fff;cursor:pointer;transition:background .12s}
#dsel-icpop .dsel-ic-g button:hover{background:#F1F5F9;border-color:#CBD5E1}
#dsel-icpop .dsel-ic-g button.dsel-ic-ok{background:#DCFCE7;border-color:#22C55E}
#dsel-icpop .dsel-ic-g img{width:26px;height:26px;display:block;pointer-events:none}
#dsel-icpop .dsel-ic-ph{display:block;width:20px;height:20px;border-radius:5px;background:linear-gradient(90deg,#F1F5F9,#E2E8F0,#F1F5F9);background-size:200% 100%;animation:dsel-ic-sh 1s linear infinite}
@keyframes dsel-ic-sh{to{background-position:-200% 0}}
#dsel-icpop .dsel-ic-more{display:none;width:100%;height:30px;margin-top:8px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#1D4ED8;font-size:12px;font-weight:600;cursor:pointer}
#dsel-icpop .dsel-ic-more:hover{background:#EFF6FF;border-color:#2563EB}
#dsel-icpop .dsel-ic-f{flex:none;padding:7px 12px;border-top:1px solid #E2E8F0;font-size:11px;line-height:1.4;color:#64748B;background:#F8FAFC}
@media (max-width:600px){
#dsel-icpop{height:auto}
#dsel-icpop .dsel-ic-main{flex-direction:column}
#dsel-icpop .dsel-ic-cats{width:auto;display:flex;gap:4px;overflow-x:auto;overflow-y:hidden;border-right:0;border-bottom:1px solid #E2E8F0;padding:6px 8px;flex:none}
#dsel-icpop .dsel-ic-cats button{width:auto;flex:none}
}
/* bottom tool bar: with a mouse, wrap onto a second row instead of hiding the last buttons off the edge
   (phones and tablets keep the swipe-to-scroll bar) */
@media (hover: hover) and (pointer: fine) {
html:not(.dsel-side) #dsel-dock{flex-wrap:wrap!important;overflow:visible!important;justify-content:center!important}
html:not(.dsel-side) #dsel-dock .dsel-right{flex-wrap:wrap!important;white-space:normal!important;justify-content:center!important;row-gap:6px}
}
</style>
<script id="ds-iclib-js">
/* DS Editor Icons Library — an "Icons" button in the editor's tool bar opens a panel where users search
   100,000+ free icons (Iconify, commercial-use licences only: MIT / Apache / ISC) and click one to add it to the slide.
   Icons are added through the editor's own "newicon" action, so they save, recolour and download to PPTX like the built-in icons. */
(function(){
if (window.__dsIconLib) return; window.__dsIconLib = 1;
var H = document.documentElement;
var SETS = {
  line:   'tabler,lucide,iconoir,hugeicons,carbon,heroicons,icon-park-outline,ph,mynaui,tdesign,uil,ion,famicons,clarity,healthicons,jam,mage,stash,teenyicons,ix',
  solid:  'mdi,ic,material-symbols,mingcute,ri,bi,fluent,boxicons,icon-park-solid,f7,garden',
  colour: 'fluent-color,icon-park,marketeq,glyphs-poly,fluent-emoji-flat,fluent-emoji,noto,noto-v1,fxemoji,flat-color-icons,flat-ui,meteocons,circle-flags'
};
SETS.all = [SETS.line, SETS.solid, SETS.colour].join(',');
var COLOUR = {}; SETS.colour.split(',').forEach(function(k){ COLOUR[k] = 1; });
var NAMES = {tabler:'Tabler', lucide:'Lucide', iconoir:'Iconoir', hugeicons:'Huge Icons', carbon:'Carbon', heroicons:'Heroicons', 'icon-park-outline':'IconPark', ph:'Phosphor',
  mynaui:'Myna UI', tdesign:'TDesign', uil:'Unicons', ion:'Ionicons', famicons:'Famicons', clarity:'Clarity', healthicons:'Health Icons', jam:'Jam', mage:'Mage', stash:'Stash', teenyicons:'Teenyicons', ix:'Siemens iX',
  mdi:'Material Design', ic:'Google Material', 'material-symbols':'Material Symbols', mingcute:'MingCute', ri:'Remix', bi:'Bootstrap', fluent:'Fluent', boxicons:'Boxicons', 'icon-park-solid':'IconPark Solid', f7:'Framework7', garden:'Garden',
  'fluent-color':'Fluent Colour', 'icon-park':'IconPark Colour', marketeq:'Marketeq', 'glyphs-poly':'Glyphs Poly', 'fluent-emoji-flat':'Fluent Emoji', 'fluent-emoji':'Fluent Emoji 3D', noto:'Noto', 'noto-v1':'Noto v1', fxemoji:'FxEmoji',
  'flat-color-icons':'Flat Colour', 'flat-ui':'Flat UI', meteocons:'Meteocons', 'circle-flags':'Circle Flags'};
var CATS = [
  ['all','All icons','layout-grid',[]],
  ['popular','Popular','star',[]],
  ['business','Business','briefcase',['business','briefcase','office','handshake','presentation','meeting','company','strategy','growth','idea','agreement','workspace']],
  ['finance','Finance','coin',['money','coin','wallet','bank','credit-card','cash','dollar','investment','savings','payment','currency','receipt','calculator']],
  ['charts','Charts & data','chart-bar',['chart','graph','analytics','report','statistics','dashboard','pie-chart','bar-chart','trend','data','diagram']],
  ['marketing','Marketing','speakerphone',['marketing','megaphone','target','campaign','advertising','promotion','seo','social','brand','funnel','announcement']],
  ['people','People','users',['user','users','team','person','people','group','account','avatar','customer','employee','family','manager']],
  ['comms','Communication','message-circle',['message','chat','mail','phone','email','call','comment','notification','send','inbox','speech','contact']],
  ['tech','Technology','device-laptop',['computer','laptop','cloud','code','server','database','network','wifi','robot','ai','smartphone','chip','monitor','internet']],
  ['education','Education','school',['education','school','book','graduation','student','teacher','learning','library','certificate','pencil','science','university']],
  ['health','Health','stethoscope',['medical','health','hospital','heart-pulse','doctor','medicine','pill','stethoscope','ambulance','fitness','dna','virus','tooth']],
  ['travel','Travel','plane',['travel','plane','luggage','hotel','passport','beach','map','ticket','suitcase','camping','compass','mountain']],
  ['food','Food & drink','tools-kitchen-2',['food','coffee','restaurant','drink','pizza','burger','cake','fruit','wine','cooking','bread','ice-cream']],
  ['nature','Nature','leaf',['nature','leaf','tree','plant','flower','forest','mountain','water','earth','recycle','eco','seed']],
  ['weather','Weather','sun',['weather','sun','cloud-rain','snow','storm','wind','rainbow','thermometer','moon','umbrella','lightning','fog']],
  ['security','Security','shield-check',['security','shield','lock','key','password','fingerprint','safe','privacy','guard','alarm','verified','firewall']],
  ['time','Time & date','clock',['clock','time','calendar','hourglass','alarm','schedule','timer','stopwatch','event','date','history','deadline']],
  ['location','Location','map-pin',['location','map','pin','globe','navigation','compass','route','gps','direction','world','address','flag']],
  ['shopping','Shopping','shopping-cart',['shopping','cart','store','gift','bag','tag','sale','discount','delivery','package','basket','price']],
  ['transport','Transport','car',['car','truck','bus','train','bicycle','ship','airplane','taxi','motorcycle','rocket','scooter','fuel']],
  ['buildings','Buildings','building',['building','home','factory','city','house','office-building','bank','school','hospital','warehouse','store','castle']],
  ['arrows','Arrows','arrow-right',['arrow','chevron','direction','refresh','undo','redo','expand','swap','up','down','sync','exchange']],
  ['files','Files','file-text',['file','folder','document','clipboard','pdf','archive','attachment','note','paper','page','spreadsheet','upload']],
  ['awards','Awards','trophy',['trophy','award','medal','star','crown','badge','ribbon','winner','prize','certificate','champion','first-place']],
  ['energy','Energy','bolt',['energy','bolt','battery','solar','power','electricity','plug','wind-turbine','fire','lightbulb','charging','nuclear']],
  ['sports','Sports','ball-football',['sport','ball','fitness','bike','football','basketball','tennis','golf','swimming','running','gym','soccer']],
  ['emotions','Emotions','mood-smile',['smile','emoji','heart','like','happy','sad','love','laugh','thumbs-up','wink','angry','party']],
  ['tools','Tools','tool',['tool','settings','wrench','gear','hammer','screwdriver','toolbox','repair','build','construction','paint','brush']],
  ['media','Media','camera',['camera','video','music','image','photo','microphone','headphones','film','play','speaker','tv','radio']],
  ['animals','Animals','paw',['animal','paw','dog','bird','cat','fish','horse','butterfly','lion','elephant','bee','rabbit']]
];
var POP = 'briefcase,chart-bar,chart-pie,chart-line,trending-up,target,bulb,rocket,users,user,user-check,building,building-skyscraper,coin,cash,wallet,credit-card,receipt,shopping-cart,truck,world,map-pin,phone,mail,message-circle,calendar,clock,settings,shield-check,lock,key,cloud,database,server,device-laptop,device-mobile,code,cpu,wifi,brain,school,book,certificate,award,trophy,star,heart,thumb-up,check,circle-check,x,alert-triangle,info-circle,search,filter,list-check,clipboard-list,file-text,folder,presentation,speakerphone,share,link,leaf,plant,sun,bolt,recycle,home,heart-handshake,stethoscope,plane,coffee,puzzle,flag,gift,diamond,arrow-right,arrows-exchange,refresh'.split(',').map(function(n){ return 'tabler:' + n; });
var PAGE = 96;
var st = {q: '', cat: 'all', lazy: null, total: 0, style: 'all', list: [], shown: 0, busy: 0, err: 0, seq: 0, moved: null, mode: ''};
var cache = {};
function esc(s){ return String(s || '').replace(/[&<>"]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }

/* ---------- the button in the editor tool bar ---------- */
function btn(){
  var dock = document.getElementById('dsel-dock'); if (!dock) return;
  var row = dock.querySelector('.dsel-right') || dock;
  if (row.querySelector('.dsel-iclib-b')) return;
  var add = row.querySelector('[data-a="add"]');
  var b = document.createElement('button');
  b.type = 'button'; b.className = 'dsel-b dsel-iclib-b'; b.title = 'Search thousands of icons and add them to the slide';
  b.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none"><path d="M12 3l2.6 5.4 5.9.8-4.3 4.1 1 5.8L12 16.4 6.8 19.1l1-5.8L3.5 9.2l5.9-.8z"/></svg>Icons';
  if (add && add.nextSibling) row.insertBefore(b, add.nextSibling); else row.appendChild(b);
}

/* ---------- the panel ---------- */
function pop(){
  var p = document.getElementById('dsel-icpop'); if (p) return p;
  p = document.createElement('div'); p.id = 'dsel-icpop';
  p.innerHTML =
    '<div class="dsel-ic-h"><b>Icons</b><button type="button" class="dsel-ic-x" title="Close">&times;</button></div>' +
    '<div class="dsel-ic-top">' +
      '<div class="dsel-ic-s"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>' +
      '<input type="search" class="dsel-ic-q" placeholder="Search 100,000+ icons" autocomplete="off" spellcheck="false"><button type="button" class="dsel-ic-clr" title="Clear">&times;</button></div>' +
      '<div class="dsel-ic-st"><button type="button" data-st="all" class="on">All</button><button type="button" data-st="line">Line</button><button type="button" data-st="solid">Solid</button><button type="button" data-st="colour">Colour</button></div>' +
    '</div>' +
    '<div class="dsel-ic-main"><div class="dsel-ic-cats"></div>' +
    '<div class="dsel-ic-body"><div class="dsel-ic-info"></div><div class="dsel-ic-g"></div><button type="button" class="dsel-ic-more">Show more</button></div></div>' +
    '<div class="dsel-ic-f">Click an icon to add it to the slide. Change its colour in the settings.</div>';
  document.body.appendChild(p);
  var q = p.querySelector('.dsel-ic-q');
  q.addEventListener('input', function(){ clearTimeout(q.t); q.t = setTimeout(function(){ search(q.value); }, 320); });
  q.addEventListener('keydown', function(ev){ if (ev.key === 'Enter') { clearTimeout(q.t); search(q.value); } ev.stopPropagation(); });
  q.addEventListener('keyup', function(ev){ ev.stopPropagation(); });
  p.addEventListener('click', function(ev){
    var t = ev.target;
    if (t.closest('.dsel-ic-x')) { hide(); return; }
    if (t.closest('.dsel-ic-clr')) { q.value = ''; search(''); q.focus(); return; }
    var s = t.closest('[data-st]'); if (s) { st.style = s.getAttribute('data-st'); [].forEach.call(p.querySelectorAll('[data-st]'), function(x){ x.classList.toggle('on', x === s); }); search(st.q, 1); return; }
    var ct = t.closest('[data-cat]'); if (ct) { q.value = ''; st.cat = ct.getAttribute('data-cat'); search(''); return; }
    if (t.closest('.dsel-ic-more')) { more(); return; }
    var ic = t.closest('[data-ic]'); if (ic) { addIcon(ic.getAttribute('data-ic')); ic.classList.add('dsel-ic-ok'); setTimeout(function(){ ic.classList.remove('dsel-ic-ok'); }, 700); }
  });
  var bd = p.querySelector('.dsel-ic-body');
  bd.addEventListener('scroll', function(){ if (bd.scrollHeight - bd.scrollTop - bd.clientHeight < 240 && hasMore()) more(); }, {passive: true});
  var cl = p.querySelector('.dsel-ic-cats');
  cl.innerHTML = CATS.map(function(c){ return '<button type="button" data-cat="' + c[0] + '"><span class="dsel-ic-ci" data-ci="tabler:' + c[2] + '"></span>' + c[1] + '</button>'; }).join('');
  loadData(CATS.map(function(c){ return 'tabler:' + c[2]; }), function(){
    [].forEach.call(cl.querySelectorAll('[data-ci]'), function(sp){ var d = DATA[sp.getAttribute('data-ci')]; if (d) sp.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' + d.l + ' ' + d.t + ' ' + d.w + ' ' + d.h + '" width="16" height="16" aria-hidden="true">' + d.b + '</svg>'; });
  });
  return p;
}
function catOf(id){ for (var k = 0; k < CATS.length; k++) if (CATS[k][0] === id) return CATS[k]; return CATS[0]; }
function fetchTerm(term, style){
  var key = style + '|' + term;
  if (cache[key]) return Promise.resolve(cache[key]);
  return fetch('https://api.iconify.design/search?query=' + encodeURIComponent(term) + '&limit=999&prefixes=' + SETS[style])
    .then(function(r){ if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function(j){ cache[key] = (j && j.icons) || []; return cache[key]; });
}
/* mix the results of several words, a few from each in turn, so a category shows a varied set */
function mix(lists){
  var out = [], seen = {}, i = 0, more = true;
  while (more) { more = false; lists.forEach(function(l){ for (var k = i * 4; k < i * 4 + 4 && k < l.length; k++) { more = true; if (!seen[l[k]]) { seen[l[k]] = 1; out.push(l[k]); } } }); i++; }
  return out;
}
function search(q, force){
  q = String(q || '').trim().toLowerCase();
  var p = pop();
  st.q = q;
  p.querySelector('.dsel-ic-clr').style.visibility = q ? 'visible' : 'hidden';
  [].forEach.call(p.querySelectorAll('[data-cat]'), function(x){ x.classList.toggle('on', !q && x.getAttribute('data-cat') === st.cat); });
  var c = catOf(st.cat), my = ++st.seq;
  st.lazy = null;
  if (!q && c[0] === 'all') {
    st.busy = 0; st.err = 0; st.list = []; st.total = 0;
    st.lazy = {sets: order(st.style), i: 0, loading: 0, seen: {}};
    totals(st.style, function(n){ if (my === st.seq) { st.total = n; info(); } });
    draw(1); return;
  }
  if (!q && c[0] === 'popular' && (st.style === 'all' || st.style === 'line')) { st.busy = 0; st.err = 0; st.list = POP; draw(1); return; }
  var terms = q ? [q] : (c[3].length ? c[3] : ['business', 'chart', 'user', 'idea', 'money', 'target', 'rocket', 'star', 'calendar', 'mail', 'globe', 'heart']);
  st.busy = 1; st.err = 0; draw(1);
  Promise.all(terms.map(function(t){ return fetchTerm(t, st.style).catch(function(){ return null; }); }))
    .then(function(res){
      if (my !== st.seq) return;
      var ok = res.filter(function(r){ return r; });
      st.busy = 0;
      if (!ok.length) { st.err = 1; st.list = []; draw(1); return; }
      st.list = ok.length === 1 ? ok[0] : mix(ok); draw(1);
    });
}
function draw(reset){
  var p = pop(), g = p.querySelector('.dsel-ic-g'), ie = p.querySelector('.dsel-ic-info'), mb = p.querySelector('.dsel-ic-more');
  if (reset) { g.innerHTML = ''; st.shown = 0; p.querySelector('.dsel-ic-body').scrollTop = 0; }
  if (st.busy) { ie.textContent = 'Searching…'; mb.style.display = 'none'; return; }
  if (st.err) { ie.textContent = 'Could not reach the icon library. Check your internet connection and try again.'; mb.style.display = 'none'; return; }
  if (!st.list.length && !st.lazy) { ie.textContent = 'No icons found for “' + st.q + '”. Try a simpler word, like “money” or “team”.'; mb.style.display = 'none'; return; }
  more();
}
var DATA = {};
/* each icon is drawn as one small picture: light for the page, and colour icons keep their own gradients */
function svgOf(n){
  var d = DATA[n]; if (!d) return '';
  var body = COLOUR[n.split(':')[0]] ? d.b : d.b.replace(/currentColor/g, '#334155');
  var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' + d.l + ' ' + d.t + ' ' + d.w + ' ' + d.h + '" width="48" height="48">' + body + '</svg>';
  return '<img alt="" decoding="async" src="data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg) + '">';
}
/* one request per icon set for a whole page of results (the per-icon image links get rate-limited) */
function loadData(names, done){
  var need = {}, left = 0;
  names.forEach(function(n){ if (DATA[n] === undefined) { var pr = n.split(':'); (need[pr[0]] = need[pr[0]] || []).push(pr[1]); } });
  var sets = Object.keys(need); if (!sets.length) { done(); return; }
  left = sets.length;
  sets.forEach(function(pre){
    fetch('https://api.iconify.design/' + pre + '.json?icons=' + need[pre].join(','))
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(j){
        need[pre].forEach(function(nm){
          var ic = j && j.icons && j.icons[nm], al = j && j.aliases && j.aliases[nm], hops = 0;
          while (!ic && al && hops++ < 4) { ic = j.icons[al.parent]; if (!ic) al = j.aliases[al.parent]; }
          if (!ic) { DATA[pre + ':' + nm] = null; return; }
          DATA[pre + ':' + nm] = { b: String(ic.body || '').replace(/<script[\s\S]*?<\/script>/gi, '').replace(/\son[a-z]+\s*=/gi, ' data-x='), l: ic.left || j.left || 0, t: ic.top || j.top || 0, w: ic.width || (al && al.width) || j.width || 16, h: ic.height || (al && al.height) || j.height || 16 };
        });
      })
      .catch(function(){})
      .then(function(){ if (--left === 0) done(); });
  });
}
/* the order sets appear in under "All": styles mixed so the first screens are varied */
function order(style){
  if (style !== 'all') return SETS[style].split(',');
  var a = SETS.line.split(','), b = SETS.solid.split(','), c = SETS.colour.split(','), out = [];
  for (var i = 0; i < Math.max(a.length, b.length, c.length); i++) { if (a[i]) out.push(a[i]); if (c[i]) out.push(c[i]); if (b[i]) out.push(b[i]); }
  return out;
}
var TOT = {};
function totals(style, cb){
  if (TOT[style]) { cb(TOT[style]); return; }
  fetch('https://api.iconify.design/collections?prefixes=' + SETS[style]).then(function(r){ return r.json(); })
    .then(function(j){ var n = 0; Object.keys(j || {}).forEach(function(k){ n += j[k].total || 0; }); TOT[style] = n; cb(n); }).catch(function(){});
}
var LISTS = {};
function setList(pre, cb){
  if (LISTS[pre]) { cb(LISTS[pre]); return; }
  fetch('https://api.iconify.design/collection?prefix=' + pre).then(function(r){ return r.json(); })
    .then(function(j){
      var hid = {}, out = []; (j.hidden || []).forEach(function(h){ hid[h] = 1; });
      var add = function(n){ if (!hid[n]) out.push(pre + ':' + n); };
      if (j.categories) Object.keys(j.categories).forEach(function(k){ j.categories[k].forEach(add); });
      (j.uncategorized || []).forEach(add);
      LISTS[pre] = out; cb(out);
    }).catch(function(){ LISTS[pre] = []; cb([]); });
}
function info(){
  var p = pop(), el = p.querySelector('.dsel-ic-info');
  if (st.lazy) { el.textContent = 'All icons' + (st.total ? ' \u00b7 ' + st.total.toLocaleString() + ' icons' : '') + ' \u00b7 scroll for more'; return; }
  el.textContent = st.q ? st.list.length.toLocaleString() + ' icon' + (st.list.length === 1 ? '' : 's') + ' for \u201c' + st.q + '\u201d' : catOf(st.cat)[1] + ' \u00b7 ' + st.list.length.toLocaleString() + ' icons';
}
function hasMore(){ return st.shown < st.list.length || !!(st.lazy && st.lazy.i < st.lazy.sets.length); }
function more(){
  var p = pop(), g = p.querySelector('.dsel-ic-g'), mb = p.querySelector('.dsel-ic-more');
  var L = st.lazy, my = st.seq;
  if (L && st.shown + PAGE > st.list.length && L.i < L.sets.length) {
    if (L.loading) return;
    L.loading = 1; mb.style.display = 'block'; mb.textContent = 'Loading\u2026';
    setList(L.sets[L.i++], function(names){
      if (my !== st.seq) return;
      L.loading = 0; mb.textContent = 'Show more';
      st.list = st.list.concat(names); more();
    });
    return;
  }
  var next = st.list.slice(st.shown, st.shown + PAGE);
  g.insertAdjacentHTML('beforeend', next.map(function(n){
    var pr = n.split(':');
    return '<button type="button" data-ic="' + esc(n) + '" title="' + esc(pr[1].replace(/-/g, ' ') + ' \u00b7 ' + (NAMES[pr[0]] || pr[0])) + '">' + (svgOf(n) || '<i class="dsel-ic-ph"></i>') + '</button>';
  }).join(''));
  st.shown += next.length;
  info();
  mb.style.display = hasMore() ? 'block' : 'none';
  loadData(next, function(){
    if (my !== st.seq) return;
    next.forEach(function(n){
      var b = g.querySelector('[data-ic="' + n + '"]'); if (!b || !b.querySelector('.dsel-ic-ph')) return;
      if (DATA[n]) b.innerHTML = svgOf(n); else b.remove();
    });
  });
}
/* add through the editor's own action, so the icon behaves exactly like the built-in ones */
function addIcon(n){
  var panel = document.getElementById('dsel-panel'); if (!panel || !/^[a-z0-9-]+:[a-z0-9-]+$/.test(n)) return;
  var b = document.createElement('button'); b.type = 'button'; b.setAttribute('data-p', 'newicon'); b.setAttribute('data-n', n); b.style.display = 'none';
  panel.appendChild(b); try { b.click(); } finally { b.remove(); }
}

/* ---------- open / close / move ---------- */
function place(p, b){
  p.style.left = p.style.top = p.style.bottom = p.style.maxHeight = '';
  var w = Math.min(560, innerWidth - 16);
  p.style.width = w + 'px';
  if (st.moved) { clampTo(p, st.moved.x, st.moved.y); return; }
  if (H.classList.contains('dsel-side')) { clampTo(p, 196, 64); return; }
  var r = b ? b.getBoundingClientRect() : {left: 16, top: innerHeight, bottom: innerHeight};
  var x = Math.max(8, Math.min(r.left, innerWidth - w - 8));
  if (r.top > innerHeight - r.bottom) { p.style.left = x + 'px'; p.style.top = 'auto'; p.style.bottom = (innerHeight - r.top + 8) + 'px'; p.style.maxHeight = Math.min(480, r.top - 16) + 'px'; }
  else clampTo(p, x, r.bottom + 8);
}
function clampTo(p, x, y){
  var w = p.offsetWidth || 560;
  x = Math.max(8, Math.min(x, innerWidth - w - 8)); y = Math.max(8, Math.min(y, innerHeight - 120));
  p.style.left = x + 'px'; p.style.top = y + 'px'; p.style.bottom = 'auto';
  p.style.maxHeight = Math.min(480, innerHeight - y - 12) + 'px';
}
function open(){ var p = document.getElementById('dsel-icpop'); return !!(p && p.style.display === 'flex'); }
function show(b){
  var p = pop(); p.style.display = 'flex'; place(p, b);
  st.mode = H.classList.contains('dsel-side') ? 'side' : 'bar';
  if (!st.list.length && !st.busy) search(st.q);
  if (matchMedia('(hover: hover)').matches) setTimeout(function(){ p.querySelector('.dsel-ic-q').focus(); }, 30);
}
function hide(){ var p = document.getElementById('dsel-icpop'); if (p) p.style.display = 'none'; }
function toggle(b){ if (open()) hide(); else show(b); }
window.__dsIconLibOpen = function(){ show(document.querySelector('.dsel-iclib-b')); };
document.addEventListener('pointerdown', function(ev){
  var h = ev.target.closest && ev.target.closest('#dsel-icpop .dsel-ic-h');
  if (h && !ev.target.closest('.dsel-ic-x') && (ev.button === 0 || ev.pointerType !== 'mouse')) {
    var p = document.getElementById('dsel-icpop'), r = p.getBoundingClientRect(), dx = ev.clientX - r.left, dy = ev.clientY - r.top;
    ev.preventDefault(); p.classList.add('dsel-ic-drag');
    var mv = function(e){ clampTo(p, e.clientX - dx, e.clientY - dy); };
    var up = function(){ document.removeEventListener('pointermove', mv, true); document.removeEventListener('pointerup', up, true); p.classList.remove('dsel-ic-drag'); var b = p.getBoundingClientRect(); st.moved = {x: b.left, y: b.top}; };
    document.addEventListener('pointermove', mv, true); document.addEventListener('pointerup', up, true);
    return;
  }
  if (!open()) return;
  if (ev.target.closest('#dsel-icpop') || ev.target.closest('.dsel-iclib-b') || ev.target.closest('#dsp-ppt-main-slide') || ev.target.closest('#dsel-bar') || ev.target.closest('#dsel-panel')) return;
  hide();
}, true);
document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape' && open()) hide(); });
/* one document-level handler, so the button keeps working when the editor redraws its tool bar */
document.addEventListener('click', function(ev){ var b = ev.target.closest && ev.target.closest('.dsel-iclib-b'); if (!b) return; ev.preventDefault(); ev.stopPropagation(); toggle(b); }, true);
new MutationObserver(function(){
  if (open() && (st.mode === 'side') !== H.classList.contains('dsel-side')) hide();
}).observe(H, {attributes: true, attributeFilter: ['class']});
/* if an Iconify picture on the slide fails to load (the image service can be busy), draw it from the icon data instead */
var FIX = {};
document.addEventListener('error', function(ev){
  var im = ev.target; if (!im || im.tagName !== 'IMG') return;
  var u = im.getAttribute('src') || '', m = u.match(/^https:\/\/api\.iconify\.design\/([a-z0-9-]+)\/([a-z0-9-]+)\.svg(?:\?(.*))?$/); if (!m || im.__dsIcFix) return;
  im.__dsIcFix = 1;
  var n = m[1] + ':' + m[2], col = ((m[3] || '').match(/color=(%23|#)([0-9a-fA-F]{3,8})/) || [])[2];
  var go = function(){
    var d = DATA[n]; if (!d) return;
    var body = d.b; if (col) body = body.replace(/currentColor/g, '#' + col);
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' + d.l + ' ' + d.t + ' ' + d.w + ' ' + d.h + '" width="' + d.w + '" height="' + d.h + '">' + body + '</svg>';
    im.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
  };
  if (DATA[n] !== undefined) go(); else (FIX[n] = FIX[n] || []).push(go), loadData([n], function(){ (FIX[n] || []).forEach(function(f){ f(); }); delete FIX[n]; });
}, true);
setInterval(function(){ btn(); if (open() && !document.getElementById('dsel-dock')) hide(); }, 1000);
btn();
})();
</script>
<?php
}, 1170);
