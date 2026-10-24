<?php
/**
 * Code Snippets export — DS AI Document — Page & Editor
 *
 * Snippet ID : 1258
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-24 14:00:00
 * Load order : 373 of 374
 */
/**
 * DS AI Document — Page & Editor
 * - Shortcode [ds_ai_document]: the AI Document page (create with AI / your text / templates,
 *   designs and themes, editor, Word and PDF download, My documents).
 * - Creates the page /ai-document/ once (same layout settings as the AI Presentation page).
 * - Adds an "AI Document" tab to the AI Studio tab bar on the other AI pages.
 * Needs: "DS AI Document — Engine" and "DS AI Document — Backend".
 * Version: 1.0.0-ac386964
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'ds_doc_page_shortcode' ) ) :
define( 'DS_DOC_APP_VER', '1.8.6-review' );

function ds_doc_app_js() {
	return <<<'DSJS'
/* DesignStager AI Document — element editor, built to look and work exactly like the AI Presentation editor
 * (same top bar, bottom bar, settings panel, layers list, handles, menus and shortcuts).
 * Every element is selectable: free elements (text boxes, shapes, lines, icons, pictures, charts) and the
 * document's own text (headings, paragraphs, lists, tables, pictures, charts) and the cover texts.
 * window.DSDOC_LE(env) → { onRender(api), sync(), clear(), busy(), selected(), editChart(obj, done), addChart(), place(), destroy() }
 */
/* Icons library (same as the AI Presentation editor): 100,000+ free icons with commercial-use licences, by category and style. */
(function(){
if (window.__dsDocIcons) return;
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

/* ---------- the panel ---------- */
function pop(){
  var p = document.getElementById('dsdoc-icpop'); if (p) return p;
  p = document.createElement('div'); p.id = 'dsdoc-icpop';
  p.innerHTML =
    '<div class="dsel-ic-h"><b>Icons</b><span class="dsel-ic-hb"><button type="button" class="dsel-ic-lk" title="Lock: keep this window open"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path class="lk-a" d="M8 11V7a4 4 0 0 1 7.5-2"/></svg></button><button type="button" class="dsel-ic-x" title="Close">&times;</button></span></div>' +
    '<div class="dsel-ic-top">' +
      '<div class="dsel-ic-s"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>' +
      '<input type="search" class="dsel-ic-q" placeholder="Search 100,000+ icons" autocomplete="off" spellcheck="false"><button type="button" class="dsel-ic-clr" title="Clear">&times;</button></div>' +
      '<div class="dsel-ic-st"><button type="button" data-st="all" class="on">All</button><button type="button" data-st="line">Line</button><button type="button" data-st="solid">Solid</button><button type="button" data-st="colour">Colour</button></div>' +
    '</div>' +
    '<div class="dsel-ic-main"><div class="dsel-ic-cats"></div>' +
    '<div class="dsel-ic-body"><div class="dsel-ic-info"></div><div class="dsel-ic-g"></div><button type="button" class="dsel-ic-more">Show more</button></div></div>' +
    '<div class="dsel-ic-f">Click an icon to add it to the page. Change its colour from the top bar.</div>';
  document.body.appendChild(p);
  var q = p.querySelector('.dsel-ic-q');
  q.addEventListener('input', function(){ clearTimeout(q.t); q.t = setTimeout(function(){ search(q.value); }, 320); });
  q.addEventListener('keydown', function(ev){ if (ev.key === 'Enter') { clearTimeout(q.t); search(q.value); } ev.stopPropagation(); });
  q.addEventListener('keyup', function(ev){ ev.stopPropagation(); });
  p.addEventListener('click', function(ev){
    var t = ev.target;
    if (t.closest('.dsel-ic-x')) { st.locked = false; p.classList.remove('dsel-ic-locked'); hide(); return; }
    if (t.closest('.dsel-ic-lk')) { st.locked = !st.locked; p.classList.toggle('dsel-ic-locked', st.locked); var pa = p.querySelector('.lk-a'); if (pa) pa.setAttribute('d', st.locked ? 'M8 11V7a4 4 0 0 1 8 0v4' : 'M8 11V7a4 4 0 0 1 7.5-2'); return; }
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
function addIcon(n){ if (!/^[a-z0-9-]+:[a-z0-9-]+$/.test(n)) return; if (st.pick) st.pick(n); if (st.once) hide(); }

/* ---------- open / close / move ---------- */
function place(p, b){
  p.style.left = p.style.top = p.style.bottom = p.style.maxHeight = '';
  var w = Math.min(560, innerWidth - 16);
  p.style.width = w + 'px';
  if (st.moved) { clampTo(p, st.moved.x, st.moved.y); return; }
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
function open(){ var p = document.getElementById('dsdoc-icpop'); return !!(p && p.style.display === 'flex'); }
function show(b){
  var p = pop(); p.style.display = 'flex'; place(p, b);
  if (!st.list.length && !st.busy) search(st.q);
  if (matchMedia('(hover: hover)').matches) setTimeout(function(){ p.querySelector('.dsel-ic-q').focus(); }, 30);
}
function hide(){ var p = document.getElementById('dsdoc-icpop'); if (p) p.style.display = 'none'; }
function toggle(b){ if (open()) hide(); else show(b); }
window.__dsDocIconsX = 1;
window.__dsDocIcons = { locked: function(){ return !!st.locked; }, flash: function(){ var p = document.getElementById('dsdoc-icpop'); if (!p) return; p.classList.remove('dsel-pflash'); void p.offsetWidth; p.classList.add('dsel-pflash'); }, show: function(b, pick, once){ st.pick = pick; st.once = !!once; var p = pop(); p.querySelector('.dsel-ic-h b').textContent = once ? 'Change icon' : 'Icons'; show(b); }, hide: function(){ hide(); }, open: function(){ return open(); } };
document.addEventListener('pointerdown', function(ev){
  var h = ev.target.closest && ev.target.closest('#dsdoc-icpop .dsel-ic-h');
  if (h && !ev.target.closest('.dsel-ic-x') && !ev.target.closest('.dsel-ic-lk') && (ev.button === 0 || ev.pointerType !== 'mouse')) {
    var p = document.getElementById('dsdoc-icpop'), r = p.getBoundingClientRect(), dx = ev.clientX - r.left, dy = ev.clientY - r.top;
    ev.preventDefault(); p.classList.add('dsel-ic-drag');
    var mv = function(e){ clampTo(p, e.clientX - dx, e.clientY - dy); };
    var up = function(){ document.removeEventListener('pointermove', mv, true); document.removeEventListener('pointerup', up, true); p.classList.remove('dsel-ic-drag'); var b = p.getBoundingClientRect(); st.moved = {x: b.left, y: b.top}; };
    document.addEventListener('pointermove', mv, true); document.addEventListener('pointerup', up, true);
    return;
  }
  if (!open() || st.locked) return;
  if (ev.target.closest('#dsdoc-icpop') || ev.target.closest('.dsel-pwin') || ev.target.closest('#dsel-dock') || ev.target.closest('#dsel-bar') || ev.target.closest('#dsel-panel')) return;
  hide();
}, true);
document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape' && open() && !st.locked) hide(); });
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
setInterval(function(){ if (open() && !document.getElementById('dsel-dock')) hide(); }, 1000);
})();
(function () {
  'use strict';
  var CLIP = [], STYLE_CLIP = null, ICON_CACHE = {};
  var WT = [[300, 'Light'], [400, 'Regular'], [500, 'Medium'], [600, 'Semi-bold'], [700, 'Bold'], [800, 'Extra-bold']];
  var BASE = ['#FFFFFF', '#000000', '#EF4444', '#F97316', '#F59E0B', '#22C55E', '#14B8A6', '#3B82F6', '#6366F1', '#A855F7', '#EC4899', '#64748B'];
  var TOKENS = [['@p', 'Primary'], ['@s', 'Secondary'], ['@a', 'Accent'], ['@ink', 'Text'], ['@mute', 'Muted text'], ['@tint', 'Light'], ['@line', 'Lines'], ['@w', 'White'], ['@k', 'Black']];
  var SHDEF = { c: '#000000', o: 0.35, b: 14, d: 8, a: 45, s: 0 };
  var QUICK_ICONS = ['mdi:star', 'mdi:heart', 'mdi:check-circle', 'mdi:lightbulb-on', 'mdi:rocket-launch', 'mdi:chart-bar', 'mdi:trending-up', 'mdi:account-group', 'mdi:shield-check', 'mdi:earth', 'mdi:cog', 'mdi:flash', 'mdi:target', 'mdi:trophy', 'mdi:handshake', 'mdi:calendar-check', 'mdi:email-outline', 'mdi:phone', 'mdi:map-marker', 'mdi:arrow-right-circle', 'mdi:alert-circle', 'mdi:cash-multiple', 'mdi:leaf', 'mdi:clock-outline'];
  function svg(d, fill) { return '<svg viewBox="0 0 24 24" width="16" height="16" fill="' + (fill ? 'currentColor' : 'none') + '" stroke="' + (fill ? 'none' : 'currentColor') + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none">' + d + '</svg>'; }
  var IC = {
    move: svg('<circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/>', 1),
    del: svg('<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>'),
    crop: svg('<path d="M6 2v14a2 2 0 0 0 2 2h14"/><path d="M18 22V8a2 2 0 0 0-2-2H2"/>'),
    copy: svg('<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a1 1 0 0 1 1-1h10"/><path d="M12.5 14.5h4M14.5 12.5v4"/>'),
    dup: svg('<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>'),
    lock: svg('<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>'),
    unlock: svg('<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/>'),
    chev: svg('<path d="M6 9l6 6 6-6"/>'),
    pen: svg('<path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/>'),
    pic: svg('<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M21 16l-5-5-8 8"/>'),
    shd: svg('<rect x="4" y="4" width="12" height="12" rx="2"/><path d="M20 9v9a2 2 0 0 1-2 2H9"/>'),
    brush: svg('<path d="M4 5h13v5H4z"/><path d="M17 7.5h3v5h-9v3"/><rect x="9.5" y="15.5" width="3" height="5" rx="1"/>'),
    eye: svg('<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>'),
    op: svg('<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h6V3M9 9v6h6V9zM15 15v6M15 15h6M3 15h6v6" stroke-width="1.4"/>'),
    star: svg('<path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6 6.6 19.5l1.2-6L3.3 9.3l6.1-.7z"/>'),
    keys: svg('<rect x="2.5" y="6" width="19" height="12" rx="2.5"/><path d="M6.5 10h.01M10 10h.01M13.5 10h.01M17 10h.01M7.5 14h9"/>'),
    spark: svg('<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/>')
  };
  var AIC = {
    l: '<path d="M4 3v18"/><rect x="7" y="6" width="12" height="4" rx="1"/><rect x="7" y="14" width="7" height="4" rx="1"/>',
    c: '<path d="M12 3v18"/><rect x="5" y="6" width="14" height="4" rx="1"/><rect x="8" y="14" width="8" height="4" rx="1"/>',
    r: '<path d="M20 3v18"/><rect x="5" y="6" width="12" height="4" rx="1"/><rect x="10" y="14" width="7" height="4" rx="1"/>',
    t: '<path d="M3 4h18"/><rect x="6" y="7" width="4" height="12" rx="1"/><rect x="14" y="7" width="4" height="7" rx="1"/>',
    m: '<path d="M3 12h18"/><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="8" width="4" height="8" rx="1"/>',
    b: '<path d="M3 20h18"/><rect x="6" y="5" width="4" height="12" rx="1"/><rect x="14" y="10" width="4" height="7" rx="1"/>',
    h: '<path d="M4 3v18M20 3v18"/><rect x="9.5" y="7" width="5" height="10" rx="1"/>',
    v: '<path d="M3 4h18M3 20h18"/><rect x="7" y="9.5" width="10" height="5" rx="1"/>'
  };
  function alignIcon(a) {
    var w = a === 'center' ? [[3, 12], [5, 8], [3, 12], [5, 8]] : (a === 'right' ? [[4, 12], [8, 8], [4, 12], [8, 8]] : a === 'justify' ? [[2, 14], [2, 14], [2, 14], [2, 9]] : [[2, 12], [2, 8], [2, 12], [2, 8]]), s = '';
    w.forEach(function (x, i) { s += '<rect x="' + x[0] + '" y="' + (2 + i * 4) + '" width="' + x[1] + '" height="1.8" rx=".9"/>'; });
    return '<svg viewBox="0 0 18 18" width="16" height="16" fill="currentColor" style="display:block;pointer-events:none">' + s + '</svg>';
  }
  var KEYS = [
    ['Select', [['Click', 'Select an element - a text, a heading, a table, a picture, a shape'], ['Shift + click', 'Add to, or take out of, the selection'], ['Drag on an empty place', 'Select everything inside the box'], ['Ctrl + A', 'Select everything on the page'], ['Esc', 'Clear the selection, close a menu']]],
    ['Edit', [['Ctrl + C', 'Copy'], ['Ctrl + X', 'Cut'], ['Ctrl + V', 'Paste - on this page or any other'], ['Ctrl + D', 'Duplicate'], ['Delete / Backspace', 'Delete'], ['Ctrl + S', 'Save the document'], ['Ctrl + Z', 'Undo'], ['Ctrl + Y  or  Ctrl + Shift + Z', 'Redo'], ['Brush button', 'Copy the look of an element and give it to others'], ['Right click', 'Menu with all the actions'], ['Double-click a text box', 'Type in it'], ['Shift + Enter', 'New line while typing in a text']]],
    ['Move and size', [['Arrow keys', 'Move by 1'], ['Shift + arrow keys', 'Move by 10'], ['Drag a document text', 'Move it to another place in the document'], ['Side handles of a text', 'Make the text narrower or wider'], ['Alt while dragging', 'No snapping to the middle'], ['Shift while resizing', 'Keep the proportions'], ['Shift while turning', 'Turn in steps of 15°']]],
    ['Arrange', [['Ctrl + G', 'Group'], ['Ctrl + Shift + G', 'Ungroup'], ['Ctrl + ]', 'Bring forward'], ['Ctrl + [', 'Send backward'], ['Ctrl + Shift + ]', 'Bring to front'], ['Ctrl + Shift + [', 'Send to back'], ['↑ / ↓ with Layers open', 'Move the layer forward / backward']]],
    ['View', [['?', 'Show this list']]]
  ];

  window.DSDOC_LE = function (env) {
    var D = window.DSDOC, doc = env.doc, host = env.host, canvas = env.canvas, docBox = env.docBox, esc = D.esc;
    var api = null, sel = [], pmode = '', editing = null, drag = null, bar = null, dock = null, panel = null, ov = null, keysEl = null, menuEl = null, helpEl = null;
    var ratioLock = true, layersAll = false, gfold = {}, amode = 'sel', gSel = 0, gKey = '', iconQ = '', brushOn = false, panPos = null;
    try { panPos = JSON.parse(localStorage.getItem('dsdoc_panel_pos') || 'null'); } catch (er) { panPos = null; }

    /* ================= model helpers ================= */
    function el(tag, cls, html) { var e = document.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; }
    function layers() { if (!doc.layers) doc.layers = []; return doc.layers; }
    function blocks() { if (!doc.blocks) doc.blocks = []; return doc.blocks; }
    function isL(k) { return k && k.charAt(0) === 'L'; }
    function isB(k) { return k && k.charAt(0) === 'B'; }
    function isC(k) { return k && k.charAt(0) === 'C'; }
    function kid(k) { return String(k).slice(2); }
    function lById(id) { var a = layers(); for (var i = 0; i < a.length; i++) if (a[i].id === id) return a[i]; return null; }
    function bIdx(id) { var a = blocks(); for (var i = 0; i < a.length; i++) if (a[i].id === id) return i; return -1; }
    function bById(id) { var i = bIdx(id); return i >= 0 ? blocks()[i] : null; }
    var CFIELDS = [['kicker', 'Type label'], ['title', 'Title'], ['subtitle', 'Subtitle'], ['author', 'Author'], ['org', 'Organisation'], ['date', 'Date']];
    function obj(k) { if (isL(k)) return lById(kid(k)); if (isB(k)) return bById(kid(k)); if (isC(k)) return doc[kid(k)] != null || kid(k) === 'title' ? { _c: kid(k) } : null; return null; }
    var mInst = 0;      /* page-design elements show on every page: the copy the user works on */
    function domOf(k) {
      if (isL(k)) { var L0 = lById(kid(k)); if (L0 && L0.pg === 'm') return docBox.querySelector('.dsd-L[data-lid="' + kid(k) + '"][data-mp="' + mInst + '"]') || docBox.querySelector('.dsd-L[data-lid="' + kid(k) + '"]'); return docBox.querySelector('.dsd-L[data-lid="' + kid(k) + '"]'); }
      if (isB(k)) return docBox.querySelector('.dsd-blk[data-id="' + kid(k) + '"]');
      if (isC(k)) return docBox.querySelector('[data-df="' + kid(k) + '"]');
      return null;
    }
    function zoom() { return env.zoom() || 1; }
    function theme() { return D.look(doc); }
    function dzn() { return D.design(doc.tpl); }
    function size() { return D.size(doc); }
    function uid() { return 'l' + Math.random().toString(36).slice(2, 10); }
    function hex(c) { return D.col(c, theme(), ''); }
    function r1(v) { return Math.round(v * 10) / 10; }
    function r2(v) { return Math.round(v * 100) / 100; }
    var snapNext = true;
    function begin() { if (snapNext) { env.snapshot(); snapNext = false; } }
    function done() { snapNext = true; env.changed(); }
    function isLocked(k) { var o = obj(k); return !!(o && !isC(k) && o.lock); }
    function hidden(k) { var o = obj(k); if (isC(k)) return !!((doc.chide || {})[kid(k)] || (doc.cfree || {})[kid(k)]); return !!(o && o.hide); }

    /* what kind of element: decides the tools in the top bar */
    function famOf(k) {
      var o = obj(k); if (!o) return '';
      if (isC(k)) return 'text';
      if (isL(k)) return o.k === 'shape' || o.k === 'vec' ? (o.shape === 'line' ? 'line' : 'shape') : o.k;
      if (o.t === 'image') return 'image';
      if (o.t === 'chart') return 'chart';
      if (o.t === 'pb') return 'pb';
      return 'text';
    }
    function kindName(k) {
      var o = obj(k); if (!o) return '';
      if (isC(k)) return (CFIELDS.filter(function (x) { return x[0] === kid(k); })[0] || ['', 'Text'])[1];
      if (isB(k)) return D.blockName(o);
      if (o.k === 'text') return 'Text';
      if (o.k === 'vec') return o.pg === 'm' ? 'Page design shape' : 'Design shape';
      if (o.k === 'shape') return (D.SHAPES.filter(function (s) { return s[0] === o.shape; })[0] || ['', 'Shape'])[1];
      return ({ icon: 'Icon', image: 'Picture', chart: 'Chart' })[o.k] || 'Element';
    }
    function label(k) {
      var o = obj(k); if (!o) return '';
      if (isC(k)) { var v = String(doc[kid(k)] || '').replace(/\s+/g, ' ').trim(); return kindName(k) + (v ? ' · ' + v.slice(0, 30) : ''); }
      if (isB(k)) { var t = D.blockText(o); return D.blockName(o) + (t ? ' · ' + t.slice(0, 30) : ''); }
      if (o.name) return o.name;
      if (o.k === 'text') { var s = String(o.tx || '').replace(/\s+/g, ' ').trim(); return s ? s.slice(0, 34) : 'Empty text'; }
      if (o.k === 'icon') return 'Icon · ' + String((o.ic && o.ic.n) || '').split(':').pop();
      if (o.k === 'chart') return 'Chart' + (o.ch && o.ch.title ? ' · ' + o.ch.title : '');
      return kindName(k);
    }

    /* ---- style values: one way to read / write them for free elements, document texts and cover texts ---- */
    var HEADT = /^(h1|h2|h3|r_sec|s_title|title|r_head|lh)$/;
    function stOf(k, mk) {
      var o = obj(k); if (!o) return {};
      if (isB(k)) { if (!o.st && mk) o.st = {}; return o.st || {}; }
      if (isC(k)) { if (!doc.cst) doc.cst = {}; var f = kid(k); if (!doc.cst[f] && mk) doc.cst[f] = {}; return doc.cst[f] || {}; }
      return o;
    }
    function bType(k) { var o = obj(k); return isB(k) ? o.t : isC(k) ? 'c_' + kid(k) : ''; }
    function defP(k, p) {
      var t = theme(), d = dzn(), bt = bType(k);
      if (p === 'ff') return (HEADT.test(bt) || bt === 'c_title') ? t.head : t.body;
      if (p === 'fs') return ({ h1: d.h1, h2: d.h2, h3: 11.5, r_sec: 11, s_title: 48, title: 28, c_title: 42, c_subtitle: 13, c_kicker: 9.5, c_author: 10, c_org: 10, c_date: 10, s_kicker: 11, s_sub: 16, s_text: 11.5, quote: 13, lh: 20, r_head: 30 })[bt] || d.body;
      if (p === 'fw') return (HEADT.test(bt) || bt === 'c_title' || bt === 'c_author' || bt === 'c_kicker' || bt === 's_kicker') ? 700 : 400;
      if (p === 'tc') return /^(h1|r_sec|s_title|title|c_title|lh)$/.test(bt) ? '@p' : bt === 'h2' ? '@s' : (bt === 'c_kicker' || bt === 's_kicker') ? '@a' : (bt === 'c_subtitle') ? '@mute' : '@ink';
      if (p === 'al') return 'left';
      if (p === 'lh') return 1.38;
      if (p === 'ls') return 0;
      if (p === 'op') return 1;
      return null;
    }
    var LMAP = { sk: 'st' };
    function getP(k, p) {
      var o = obj(k); if (!o) return null;
      if (isL(k)) {
        var m = p === 'r' ? (o.k === 'image' ? 'rr' : 'r') : (LMAP[p] || p), v = o[m];
        if (v == null) return ({ op: 1, fw: 400, fs: 14, al: 'left', lh: 1.25, ls: 0, tc: o.k === 'icon' ? '@p' : '@ink', ff: theme().body, r: 0 })[p];
        return v;
      }
      var s = stOf(k); if (s[p] != null) return s[p];
      if (p === 'tc') { var dc = domColor(k); if (dc) return dc; }
      return defP(k, p);
    }
    /* the colour a text shows now (the design may draw it white on a dark cover) */
    function domColor(k) {
      var n = domOf(k); if (!n) return '';
      if (isB(k)) n = n.querySelector('.dsd-h,.dsd-b,li,td,.dsd-quote,.ct,.dsd-s-t,.dsd-s-x,.dsd-s-s,.dsd-s-k,.nm,.dsd-rs') || n;
      var m = /rgba?\((\d+),\s*(\d+),\s*(\d+)/.exec(getComputedStyle(n).color || ''); if (!m) return '';
      return '#' + [m[1], m[2], m[3]].map(function (v) { return ('0' + (+v).toString(16)).slice(-2); }).join('').toUpperCase();
    }
    function setP(k, p, v) {
      var o = obj(k); if (!o) return;
      if (isL(k)) { var m = p === 'r' ? (o.k === 'image' ? 'rr' : 'r') : (LMAP[p] || p); if (v == null) delete o[m]; else o[m] = v; return; }
      var s = stOf(k, true); if (v == null) delete s[p]; else s[p] = v;
    }
    function supports(k, p) {
      var f = famOf(k);
      if (/^(ff|fs|fw|it|ul|sk|caps|al|lh|ls)$/.test(p)) return f === 'text';
      if (p === 'tc') return f === 'text' || f === 'icon';
      if (p === 'fill') return f === 'text' && !isC(k) || f === 'shape' || (f === 'chart' && isL(k));
      if (p === 'ln') return (f === 'text' && !isC(k)) || f === 'shape' || f === 'line' || (f === 'image' && isL(k));
      if (p === 'sh') return f !== 'pb' && !isC(k);
      if (p === 'op') return f !== 'pb';
      if (p === 'r') return f === 'shape' || (f === 'image' && isL(k)) || (f === 'text' && isL(k)) || (isB(k) && f !== 'pb');
      return true;
    }
    /** set a value on every selected element that has it, then redraw */
    function setAll(p, v, quiet) {
      var a = sel.filter(function (k) { return supports(k, p) && !isLocked(k); }); if (!a.length) return;
      begin();
      a.forEach(function (k) { setP(k, p, typeof v === 'function' ? v(k) : v); });
      redraw(a); if (!quiet) done();
    }
    /* layers only need their own drawing; document texts and cover texts need the pages laid out again */
    function redraw(keys) {
      var full = (keys || sel).some(function (k) { return !isL(k); });
      if (full) env.rerender(); else { if (api && api.drawLayers) api.drawLayers(); sync(); }
      if (pmode === 'layers' || pmode === 'pos') paintPanel();
    }

    /* ================= pages ================= */
    function isSingle() { return dzn().kind === 'single'; }
    function pitch() { return (api && api.pitch) || (size().h + 28); }
    function flowPages() { return (api && api.flowPages) || 1; }
    function localPos(e) { var x = 0, y = 0; while (e && e !== docBox) { x += e.offsetLeft; y += e.offsetTop; e = e.offsetParent; } return { x: x, y: y }; }
    function toLocal(cx, cy) { var r = docBox.getBoundingClientRect(), z = zoom(); return { x: (cx - r.left) / z, y: (cy - r.top) / z }; }
    function viewPage() {
      if (!api || isSingle()) return 0;
      var cr = canvas.getBoundingClientRect(), cy = cr.top + cr.height / 2;
      if (api.cover) { var c = api.cover.getBoundingClientRect(); if (cy < c.bottom + 14 * zoom()) return 'c'; }
      if (!api.flow) return 0;
      var f = api.flow.getBoundingClientRect(), p = Math.floor((cy - f.top) / (pitch() * zoom()));
      return Math.max(0, Math.min(flowPages() - 1, p));
    }
    var bpCache = null;
    function blockPages() { if (!bpCache) bpCache = (api && api.blockPages) ? api.blockPages() : {}; return bpCache; }
    function pageOfKey(k) { var o = obj(k); if (!o) return viewPage(); if (isL(k)) return o.pg === 'm' ? mInst : o.pg; if (isC(k)) return 'c'; var m = blockPages(); return m[o.id] != null ? m[o.id] : 0; }
    function curPage() { return sel.length ? pageOfKey(sel[0]) : viewPage(); }
    function pageOff(pg) { if (pg === 'm') pg = mInst; return pg === 'c' ? 0 : Math.max(0, Math.min(flowPages() - 1, +pg || 0)) * pitch(); }
    function pageName(p) { return p === 'c' ? 'Cover' : 'Page ' + ((+p || 0) + 1 + (api && api.cover ? 1 : 0)); }
    function keysOnPage(pg) {
      var out = [];
      if (pg === 'c') CFIELDS.forEach(function (f) { if ((f[0] === 'title' || doc[f[0]] != null) && !(doc.cfree || {})[f[0]]) out.push('C:' + f[0]); });
      else { var m = blockPages(); blocks().forEach(function (b) { if (!b.hide && (isSingle() || m[b.id] === pg)) out.push('B:' + b.id); }); }
      layers().forEach(function (L) { if (!L.hide && (L.pg === pg || isSingle() || onMaster(L, pg))) out.push('L:' + L.id); });
      return out;
    }
    function onMaster(L, pg) { return L.pg === 'm' && pg !== 'c' && pg != null && D.masterOn(doc, dzn(), +pg || 0); }
    function artOK(L) { return !L.art || D.artOn(doc); }

    /* ================= selection ================= */
    function select(keys, add) {
      if (editing) stopEdit();
      var exp = [];
      (keys || []).forEach(function (k) {
        var o = obj(k); if (!o) return;
        if (isL(k) && o.grp && !add) layers().forEach(function (x) { if (x.grp === o.grp && exp.indexOf('L:' + x.id) < 0) exp.push('L:' + x.id); });
        else if (exp.indexOf(k) < 0) exp.push(k);
      });
      if (add) exp.forEach(function (k) { var i = sel.indexOf(k); if (i >= 0) sel.splice(i, 1); else sel.push(k); });
      else sel = exp;
      selChanged();
    }
    function selChanged() {
      if (brushOn && sel.length) { applyBrush(); return; }
      if (pmode && /^(color|fill|line|font|weight|spacing|opacity|icon|shadow|talign|corners|chart)$/.test(pmode) && !sel.length) pmode = '';
      closeMenu();
      env.onBlockSelect(sel.length === 1 && isB(sel[0]) ? kid(sel[0]) : null);
      paint();
    }
    function clearSel() { if (sel.length) { sel = []; selChanged(); } }

    /* ================= overlay: frames, handles and the small bar ================= */
    function ensureOv() {
      if (ov && ov.parentNode === canvas) return ov;
      ov = el('div'); ov.id = 'dsel-ov';
      canvas.appendChild(ov);
      ov.addEventListener('pointerdown', onOvDown, true);
      ov.addEventListener('click', onOvClick);
      return ov;
    }
    function frameRect(k) {
      var e = domOf(k); if (!e) return null;
      var cr = canvas.getBoundingClientRect(), z = zoom(), ox = canvas.scrollLeft - cr.left, oy = canvas.scrollTop - cr.top;
      if (isL(k)) { var L = obj(k), dr = docBox.getBoundingClientRect(), lp = localPos(e); return { x: dr.left + ox + lp.x * z, y: dr.top + oy + lp.y * z, w: D.num(L.w, 100) * z, h: D.num(L.h, 100) * z, rot: D.num(L.rot, 0) }; }
      var r = e.getBoundingClientRect(); return { x: r.left + ox, y: r.top + oy, w: Math.max(4, r.width), h: Math.max(4, r.height), rot: 0 };
    }
    function frameCls(k) {
      var f = famOf(k), c = ' dsel-t-' + f;
      if (isC(k)) return c + ' dsel-nohd dsel-norot dsel-cmv';
      if (isB(k)) return c + (f === 'image' || f === 'chart' || dzn().kind === 'panels' ? ' dsel-corner dsel-norot' : ' dsel-wonly dsel-norot');
      if (f === 'line') c += ' dsel-wonly';
      return c;
    }
    /* parts of an element outside the page are cut off by the page: a faded copy under the pages shows them, and can be clicked */
    function drawGhost() {
      var g = docBox.querySelector(':scope > #dsel-ghost');
      if (!g) { g = el('div'); g.id = 'dsel-ghost'; docBox.appendChild(g); }
      var h = [];
      Array.prototype.forEach.call(docBox.querySelectorAll('.dsd-L[data-lid]'), function (n) {
        if (n.closest('#dsel-ghost') || !n.offsetParent) return;
        var lp = localPos(n), c = n.cloneNode(true);
        c.removeAttribute('data-lid'); c.setAttribute('data-glid', n.getAttribute('data-lid'));
        c.style.left = lp.x + 'px'; c.style.top = lp.y + 'px'; c.removeAttribute('contenteditable');
        Array.prototype.forEach.call(c.querySelectorAll('[contenteditable]'), function (x) { x.removeAttribute('contenteditable'); });
        h.push(c);
      });
      g.textContent = ''; h.forEach(function (c) { g.appendChild(c); });
    }
    var ghostT = 0;
    function ghostSoon() { if (ghostT) return; ghostT = requestAnimationFrame(function () { ghostT = 0; try { drawGhost(); } catch (er) { /* ignore */ } }); }
    function sync() {
      bpCache = null;
      ensureOv(); ghostSoon();
      sel = sel.filter(function (k) { return obj(k) && domOf(k) && !hidden(k); });
      var keep = ov.querySelectorAll('.dsel-g,.dsel-mq,.dsel-ins'), h = '';
      sel.forEach(function (k, i) {
        var r = frameRect(k); if (!r) return;
        var lk = isLocked(k), single = sel.length === 1;
        h += '<div class="dsel-fr' + frameCls(k) + (single && !lk && cornerOK(k) ? ' dsel-hasrc' : '') + (single && !lk && sidesOK(k) ? ' dsel-hassd' : '') + (lk ? ' dsel-lockd' : '') + (sel.length > 1 ? ' dsel-multi' : '') + (r.w < 40 || r.h < 40 ? ' dsel-tiny' : '') + (r.w < 16 || r.h < 16 ? ' dsel-micro' : '') + '" data-k="' + esc(k) + '" style="left:' + r.x + 'px;top:' + r.y + 'px;width:' + r.w + 'px;height:' + r.h + 'px;' + (r.rot ? 'transform:rotate(' + r.rot + 'deg)' : '') + '">';
        var tiny = r.w < 40 || r.h < 40;
        if (!lk && single && editing !== k && !drag) {
          if (!tiny) h += '<i class="dsel-mv" data-h="mv" style="left:0;right:0;top:-5px;height:10px"></i><i class="dsel-mv" data-h="mv" style="left:0;right:0;bottom:-5px;height:10px"></i><i class="dsel-mv" data-h="mv" style="top:0;bottom:0;left:-5px;width:10px"></i><i class="dsel-mv" data-h="mv" style="top:0;bottom:0;right:-5px;width:10px"></i>';
          h += ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'].map(function (x) { return '<b class="dsel-h dsel-h-' + x + '" data-h="' + x + '"></b>'; }).join('');
          h += '<b class="dsel-rot" data-h="rot" title="Drag to rotate">&#8635;</b>';
          if (cornerOK(k)) { var rp = rcPos(obj(k)); h += '<b class="dsel-rc" data-h="corner" title="Drag inward to round the corners, outward to sharpen them" style="left:' + rp[0] + '%;top:' + rp[1] + '%"></b>'; }
          if (sidesOK(k)) h += '<b class="dsel-sd" data-h="sides" title="Drag down for more points, up for fewer (down to a triangle)"></b>';
        }
        if (i === 0 && !drag && editing !== k) {
          var cvTop = canvas.scrollTop;
          h += '<div class="dsel-mini' + (r.y - cvTop < 54 ? ' dsel-mini-in' : '') + '">' + (lk ? '' : '<button type="button" data-h="mv" title="Drag to move">' + IC.move + '</button>') +
            '<button type="button" data-m="del" title="' + (isC(k) ? 'Hide this text' : 'Delete this element') + '">' + IC.del + '</button>' +
            '<button type="button" data-m="copy" title="Copy: then paste it on any page (Ctrl + V, or right-click → Paste)">' + IC.copy + '</button>' +
            '<button type="button" data-m="dup" title="Clone: a copy right here on this page (Ctrl + D)">' + IC.dup + '</button>' +
            '<button type="button" data-m="app" title="Apply this look to similar elements: on this page, or in the whole document">' + IC.brush + '</button></div>';
        }
        if (lk) h += '<span class="dsel-lkm" title="Locked">' + IC.lock + '</span>';
        if (single && isB(k) && (obj(k) || {}).t === 's_mark' && editing !== k && !drag) h += '<span class="dsel-imgbtns"><button type="button" class="dsel-pbtn" data-m="chglogo" title="Upload your logo, or change it">' + IC.pic + '<span>' + (doc.logo && doc.logo.src && !doc.logo.hide ? 'Change logo' : 'Upload logo') + '</span></button></span>';
        if (!lk && single && famOf(k) === 'image' && editing !== k && !drag) h += '<span class="dsel-imgbtns"><button type="button" class="dsel-pbtn" data-m="crop" title="Crop the picture">' + IC.crop + '<span>Crop</span></button><button type="button" class="dsel-pbtn" data-m="chgimg" title="Pick, create or upload another picture">' + IC.pic + '<span>Change image</span></button></span>';
        h += '</div>';
      });
      ov.innerHTML = h;
      Array.prototype.forEach.call(keep, function (x) { ov.appendChild(x); });
      if (pmode === 'pos') { var ae = document.activeElement; if (!(ae && panel && panel.contains(ae))) paintPanel(); }
    }

    /* ================= pointer: select, move, resize, rotate, box-select ================= */
    function onDown(e) {
      if (e.button !== 0 && e.button !== undefined) return;
      if (brushOn) { /* brush: the next click picks the element to give the look to */ }
      var le = e.target.closest('.dsd-L');
      if (le && le.dataset.glid) { var g0 = le.dataset.mp != null ? docBox.querySelector('.dsd-L[data-lid="' + le.dataset.glid + '"][data-mp="' + le.dataset.mp + '"]') : null; le = g0 || docBox.querySelector('.dsd-L[data-lid="' + le.dataset.glid + '"]') || le; }
      if (le && docBox.contains(le)) {
        var k = 'L:' + (le.dataset.lid || le.dataset.glid); if (!obj(k)) return;
        if (le.dataset.mp != null) mInst = +le.dataset.mp;
        if (editing === k) return;
        if (editing) stopEdit();
        e.preventDefault();
        if (e.shiftKey || e.ctrlKey || e.metaKey) { select([k], true); return; }
        if (sel.indexOf(k) < 0) select([k]);
        if (!isLocked(k)) startMove(e, null);
        return;
      }
      var cf = e.target.closest('[data-df]');
      if (cf && docBox.contains(cf)) {
        var ck = 'C:' + cf.dataset.df; if (e.shiftKey) { e.preventDefault(); select([ck], true); return; }
        if (!(sel.length === 1 && sel[0] === ck)) { e.preventDefault(); select([ck]); startMove(e, [ck]); }
        return;
      }
      var mkz = e.target.closest('.dsd-s-mk');
      if (mkz && docBox.contains(mkz) && !mkz.closest('.dsd-blk')) { e.preventDefault(); if (editing) stopEdit(); clearSel(); logoDialog(); return; }
      var be = e.target.closest('.dsd-blk');
      if (be && docBox.contains(be) && !be.closest('.dsd-hb.dark,.dsd-toc') || (be && docBox.contains(be))) {
        var bk = 'B:' + be.dataset.id, b = obj(bk); if (!b) return;
        if (editing) stopEdit();
        if (e.shiftKey || e.ctrlKey || e.metaKey) { e.preventDefault(); select([bk], true); return; }
        var wasSel = sel.length === 1 && sel[0] === bk, ae0 = document.activeElement, typing = wasSel && ae0 && be.contains(ae0) && ae0.isContentEditable;
        var inMany = dzn().kind === 'panels' && sel.length > 1 && sel.indexOf(bk) >= 0;
        if (!wasSel && !inMany) select([bk]);
        if (b.lock) e.preventDefault();
        else if (dzn().kind === 'panels' && !typing) startFreeBlockMove(e, inMany ? sel.filter(function (x) { return !isLocked(x) && !isC(x); }) : [bk]);   /* cards: drag a text or the logo anywhere */
        return;                                            /* the caret goes into the text: type straight away */
      }
      if (editing) stopEdit();
      if (e.target.closest('.dsd-logo')) { e.preventDefault(); clearSel(); logoDialog(); return; }
      if (e.target.closest('.dsd-cover-pic,[data-act],.ftb,.menu')) return;
      startMarquee(e);   /* box-select also starts outside the pages */
    }
    function onCanvasDown(e) { if (e.button !== 0 && e.button !== undefined) return; if (e.target === canvas || (e.target.id === 'dde-zoom')) { if (editing) stopEdit(); startMarquee(e); } }
    function onOvDown(e) {
      var h = e.target.closest('[data-h]'); if (!h || e.button > 0) return;
      e.preventDefault(); e.stopPropagation();
      var a = h.getAttribute('data-h');
      if (a === 'mv') { startMove(e, null); return; }
      startHandle(e, a);
    }
    function onOvClick(e) {
      var b = e.target.closest('[data-m]'); if (!b) return;
      var m = b.getAttribute('data-m');
      if (m === 'del') del();
      if (m === 'dup') duplicate();
      if (m === 'copy') copy();
      if (m === 'chgimg' && sel[0]) changePic(sel[0]);
      if (m === 'chglogo') { logoDialog(); return; }
      if (m === 'crop' && sel[0]) cropPic(sel[0]);
      if (m === 'app') { var r = b.getBoundingClientRect(); openMenu(r.left, r.bottom + 6, applyItems()); }
    }
    function snapLine(gx, gy, o) {
      ov.querySelectorAll('.dsel-g').forEach(function (x) { x.remove(); });
      if (gx == null && gy == null) return;
      var z = zoom(), cr = canvas.getBoundingClientRect(), dr = docBox.getBoundingClientRect(), sz = size();
      var originEl = o.pg === 'c' ? api.cover : (isSingle() ? docBox.querySelector('.dsd-single') : api.flow), lp = originEl ? localPos(originEl) : { x: 0, y: 0 };
      var bx = dr.left - cr.left + canvas.scrollLeft + lp.x * z, by = dr.top - cr.top + canvas.scrollTop + (lp.y + o.base) * z;
      if (gx != null) { var v = el('div', 'dsel-g dsel-gv'); v.style.cssText = 'display:block;left:' + (bx + gx * z) + 'px;top:' + by + 'px;height:' + (sz.h * z) + 'px'; ov.appendChild(v); }
      if (gy != null) { var hh = el('div', 'dsel-g dsel-gh'); hh.style.cssText = 'display:block;left:' + bx + 'px;top:' + (by + gy * z) + 'px;width:' + (sz.w * z) + 'px'; ov.appendChild(hh); }
    }
    /* a cover text as a free text box: same words, font, size and colour, in the same place */
    function freeTextOf(k) {
      var n = domOf(k), f = kid(k); if (!n || !api || !api.cover) return null;
      var cs = getComputedStyle(n), z = zoom(), r = n.getBoundingClientRect(), cr = api.cover.getBoundingClientRect(), fsPx = parseFloat(cs.fontSize) || 16;
      var lh = parseFloat(cs.lineHeight); lh = isFinite(lh) ? Math.round(lh / fsPx * 100) / 100 : 1.2;
      var tx = String(doc[f] == null ? '' : doc[f]);
      return { k: 'text', pg: 'c', tx: tx, x: Math.round((r.left - cr.left) / z - 8), y: Math.round((r.top - cr.top) / z - 8), w: Math.max(40, Math.round(r.width / z + 16)), h: Math.max(20, Math.round(r.height / z + 16)),
        ff: getP(k, 'ff'), fs: Math.round(fsPx * 0.75 * 10) / 10, fw: parseInt(cs.fontWeight, 10) || 400, tc: getP(k, 'tc') || domColor(k), al: /center|right|justify/.test(cs.textAlign) ? cs.textAlign : 'left', lh: Math.max(0.6, Math.min(4, lh)),
        ls: Math.round((parseFloat(cs.letterSpacing) || 0) * 10) / 10, caps: cs.textTransform === 'uppercase' ? 1 : undefined, it: cs.fontStyle === 'italic' ? 1 : undefined, name: kindName(k) };
    }
    function makeFree(keys) {
      var out = [];
      keys.forEach(function (k) {
        if (!isC(k)) { out.push(k); return; }
        var L = freeTextOf(k); if (!L) return;
        L.id = uid(); Object.keys(L).forEach(function (x) { if (L[x] == null) delete L[x]; });
        layers().push(L); doc.cfree = doc.cfree || {}; doc.cfree[kid(k)] = 1; out.push('L:' + L.id);
      });
      return out;
    }
    function startMove(e, only) {
      var ks0 = (only || sel).filter(function (k) { return !isLocked(k); });
      if (ks0.some(isC) && !ks0.some(isB)) {          /* cover texts: they become free text boxes, then move */
        var sx = e.clientX, sy = e.clientY;
        drag = {
          move: function (ev) {
            if (Math.abs(ev.clientX - sx) + Math.abs(ev.clientY - sy) < 4) return;
            begin(); var nk = makeFree(ks0); env.rerender(); sel = nk; sync();
            startMove({ clientX: sx, clientY: sy }, nk); if (drag) drag.move(ev);
          },
          up: function () { sync(); }
        };
        return;
      }
      var ks = ks0.filter(function (k) { return !isC(k); }); if (!ks.length) return;
      if (ks.some(isB)) { if (dzn().kind === 'panels') startFreeBlockMove(e, ks); else if (ks.every(isB)) startBlockMove(e, ks); return; }
      var st = toLocal(e.clientX, e.clientY), orig = ks.map(function (k) { var L = obj(k); return { L: L, x: D.num(L.x, 0), y: D.num(L.y, 0), off: pageOff(L.pg) }; }), moved = false, sx0 = e.clientX, sy0 = e.clientY;
      drag = {
        move: function (ev) {
          var p = toLocal(ev.clientX, ev.clientY), dx = p.x - st.x, dy = p.y - st.y;
          if (!moved && Math.abs(ev.clientX - sx0) + Math.abs(ev.clientY - sy0) < 3) return;
          if (!moved) { begin(); moved = true; document.documentElement.classList.add('dsel-dragging'); }
          if (ev.shiftKey) { if (Math.abs(dx) > Math.abs(dy)) dy = 0; else dx = 0; }
          var sx = 0, sy = 0, gx = null, gy = null, o = orig[0];
          if (orig.length === 1 && !ev.altKey) {
            var sz = size(), cx = o.x + dx + o.L.w / 2, cy = o.y + dy + o.L.h / 2, pg0 = o.L.pg === 'c' || isSingle() ? 0 : Math.floor((o.off + o.y + dy + o.L.h / 2) / pitch()) * pitch() - o.off;
            if (Math.abs(cx - sz.w / 2) < 6 / zoom()) { sx = sz.w / 2 - cx; gx = sz.w / 2; }
            if (Math.abs(cy - pg0 - sz.h / 2) < 6 / zoom()) { sy = pg0 + sz.h / 2 - cy; gy = sz.h / 2; }
            o.base = o.off + pg0;
          }
          orig.forEach(function (q) {
            q.L.x = Math.round(q.x + dx + sx); q.L.y = Math.round(q.y + dy + sy);
            if (q.L.pg === 'm') docBox.querySelectorAll('.dsd-L[data-lid="' + q.L.id + '"]').forEach(function (e3) { e3.style.left = q.L.x + 'px'; e3.style.top = (q.L.y + (+e3.dataset.mp || 0) * pitch()) + 'px'; });
            else { var e2 = domOf('L:' + q.L.id); if (e2) { e2.style.left = q.L.x + 'px'; e2.style.top = (q.L.y + q.off) + 'px'; } }
          });
          snapLine(gx, gy, { pg: o.L.pg, base: o.base || 0 });
          sync();
        },
        up: function () {
          snapLine(null, null); document.documentElement.classList.remove('dsel-dragging');
          if (!moved) return;
          orig.forEach(function (q) {     /* dragged onto another page: it moves to that page */
            if (q.L.pg === 'c' || q.L.pg === 'm' || isSingle()) return;
            var abs = q.off + q.L.y, np = Math.max(0, Math.min(flowPages() - 1, Math.floor((abs + q.L.h / 2) / pitch())));
            q.L.pg = np; q.L.y = Math.round(abs - np * pitch());
          });
          if (api && api.drawLayers) api.drawLayers(); sync(); done();
        }
      };
    }
    /* a document text is moved by dragging it to another place in the text: a line shows where it lands */
    function startBlockMove(e, ks) {
      var sx0 = e.clientX, sy0 = e.clientY, moved = false, target = null, ins = null;
      var ids = ks.map(kid);
      drag = {
        move: function (ev) {
          if (!moved && Math.abs(ev.clientX - sx0) + Math.abs(ev.clientY - sy0) < 4) return;
          if (!moved) { moved = true; document.documentElement.classList.add('dsel-dragging'); ins = el('div', 'dsel-ins'); ensureOv().appendChild(ins); sync(); }
          var all = Array.prototype.slice.call(docBox.querySelectorAll('.dsd-col .dsd-blk')), best = null, bd = 1e9;
          all.forEach(function (n) {
            if (ids.indexOf(n.dataset.id) >= 0) return;
            var r = n.getBoundingClientRect();
            [[r.top, 'before'], [r.bottom, 'after']].forEach(function (c) { var d = Math.abs(ev.clientY - c[0]) + (ev.clientX < r.left - 60 || ev.clientX > r.right + 60 ? 400 : 0); if (d < bd) { bd = d; best = { id: n.dataset.id, where: c[1], r: r }; } });
          });
          target = best;
          if (best) {
            var cr = canvas.getBoundingClientRect(), y = (best.where === 'before' ? best.r.top : best.r.bottom) - cr.top + canvas.scrollTop;
            ins.style.cssText = 'left:' + (best.r.left - cr.left + canvas.scrollLeft) + 'px;top:' + (y - 2) + 'px;width:' + best.r.width + 'px';
          }
        },
        up: function () {
          document.documentElement.classList.remove('dsel-dragging');
          if (ins) ins.remove();
          if (!moved || !target) { sync(); return; }
          begin();
          var a = blocks(), moving = a.filter(function (b) { return ids.indexOf(b.id) >= 0; }), rest = a.filter(function (b) { return ids.indexOf(b.id) < 0; });
          var ti = rest.findIndex(function (b) { return b.id === target.id; });
          if (ti < 0) { sync(); return; }
          rest.splice.apply(rest, [target.where === 'before' ? ti : ti + 1, 0].concat(moving));
          doc.blocks = rest; env.rerender(); done();
          var n = domOf(ks[0]); if (n) n.scrollIntoView({ block: 'nearest' });
        }
      };
    }
    /* on card pages a text, the logo, the QR code or the photo is dragged anywhere: it keeps its zone and is shifted by b.dx, b.dy */
    function startFreeBlockMove(e, ks) {
      var sx0 = e.clientX, sy0 = e.clientY, moved = false, z = zoom() || 1;
      /* a picture (logo, QR, photo, icon) must not start the browser's own picture drag */
      if (e.preventDefault && e.target && e.target.closest && e.target.closest('img,svg,.dsd-s-ph')) e.preventDefault();
      var items = ks.map(function (k) { var b = obj(k); if (!b || b.lock || isC(k)) return null; return isL(k) ? { L: b, k: k, x: D.num(b.x, 0), y: D.num(b.y, 0), off: pageOff(b.pg) } : { b: b, k: k, x: D.num(b.dx, 0), y: D.num(b.dy, 0), sc: D.blockSc(b) }; }).filter(Boolean);
      if (!items.length) return;
      drag = {
        move: function (ev) {
          if (!ev.clientX && !ev.clientY) return;
          if (!moved && Math.abs(ev.clientX - sx0) + Math.abs(ev.clientY - sy0) < 5) return;
          if (!moved) {
            moved = true; begin(); document.documentElement.classList.add('dsel-dragging');
            try { window.getSelection().removeAllRanges(); } catch (er) {}
            var ae = document.activeElement; if (ae && ae.blur && docBox.contains(ae)) ae.blur();
          }
          var dx = (ev.clientX - sx0) / z, dy = (ev.clientY - sy0) / z;
          if (ev.shiftKey) { if (Math.abs(dx) > Math.abs(dy)) dy = 0; else dx = 0; }
          items.forEach(function (q) {
            if (q.L) { q.L.x = Math.round(q.x + dx); q.L.y = Math.round(q.y + dy); var e2 = domOf(q.k); if (e2) { e2.style.left = q.L.x + 'px'; e2.style.top = (q.L.y + q.off) + 'px'; } return; }
            q.b.dx = Math.round(q.x + dx); q.b.dy = Math.round(q.y + dy); var n = domOf(q.k); if (n) n.style.translate = Math.round(q.b.dx / q.sc * 100) / 100 + 'px ' + Math.round(q.b.dy / q.sc * 100) / 100 + 'px';
          });
          sync();
        },
        up: function () {
          document.documentElement.classList.remove('dsel-dragging');
          if (!moved) return;
          items.forEach(function (q) { if (q.b) { if (!q.b.dx) delete q.b.dx; if (!q.b.dy) delete q.b.dy; } });
          env.rerender(); done(); sync();
        }
      };
    }
    /* on card pages a text, the logo, the QR code or the photo is made larger or smaller from its corners (any size, not only the design's) */
    function startBlockScale(e, k, h) {
      var b = obj(k), n = domOf(k); if (!b || !n) return;
      var r = n.getBoundingClientRect(), sc0 = D.blockSc(b), x0 = e.clientX, y0 = e.clientY, sx = /w/.test(h) ? -1 : /e/.test(h) ? 1 : 0, sy = /n/.test(h) ? -1 : /s/.test(h) ? 1 : 0;
      var dx0 = D.num(b.dx, 0), dy0 = D.num(b.dy, 0), z = zoom() || 1, w0 = r.width / z, h0 = r.height / z;
      begin(); document.documentElement.classList.add('dsel-dragging');
      drag = {
        move: function (ev) {
          if (!ev.clientX && !ev.clientY) return;
          var gx = sx ? sx * (ev.clientX - x0) / r.width : 0, gy = sy ? sy * (ev.clientY - y0) / r.height : 0;
          var g = sx && sy ? Math.max(gx, gy) : (sx ? gx : gy), sc = Math.max(0.3, Math.min(5, sc0 * (1 + g)));
          if (!ev.altKey && Math.abs(sc - 1) < 0.03) sc = 1;
          sc = Math.round(sc * 100) / 100; b.sc = sc;
          /* the opposite corner stays where it is */
          var kk = sc / sc0; b.dx = Math.round(dx0 - (sx < 0 ? (kk - 1) * w0 : 0)); b.dy = Math.round(dy0 - (sy < 0 ? (kk - 1) * h0 : 0));
          n.style.zoom = sc; n.style.translate = Math.round(b.dx / sc * 100) / 100 + 'px ' + Math.round(b.dy / sc * 100) / 100 + 'px'; n.setAttribute('data-sc', sc);
          sync();
          var tip = ov.querySelector('.dsel-sdt'); if (!tip) { var fr = ov.querySelector('.dsel-fr'); if (fr) { tip = el('span', 'dsel-sdt dsel-on'); fr.appendChild(tip); } }
          if (tip) tip.textContent = 'Size ' + Math.round(sc * 100) + '%';
        },
        up: function () {
          document.documentElement.classList.remove('dsel-dragging');
          if (b.sc === 1) delete b.sc; if (!b.dx) delete b.dx; if (!b.dy) delete b.dy;
          env.rerender(); done(); sync();
        }
      };
    }
    /* ---- round corners and number of points, dragged on the shape (like the AI Presentation editor) ---- */
    function shapeOfL(L) { return L.k === 'text' ? (L.shape || 'rect') : (L.shape || 'rect'); }
    function cornerOK(k) {
      if (!isL(k)) return false; var L = obj(k); if (!L || L.lock) return false;
      if (L.k === 'image') return true;
      if (L.k !== 'shape' && !(L.k === 'text' && D.hasBox(L))) return false;
      var s = shapeOfL(L); return s === 'rect' || s === 'roundRect' || !!D.polyPts(s, 10, 10, L);
    }
    function sidesOK(k) { if (!isL(k)) return false; var L = obj(k); return !!L && (L.k === 'shape' || L.k === 'text') && D.ptsOf(shapeOfL(L), L) > 0; }
    function rcGeo(L) {
      var w = Math.max(1, D.num(L.w, 100)), h = Math.max(1, D.num(L.h, 100)), V = [0, 0], C = [w / 2, h / 2];
      if (L.k !== 'image') { var P = D.polyPts(shapeOfL(L), w, h, L); if (P && P.length) { V = P[0]; C = [0, 0]; P.forEach(function (p) { C[0] += p[0] / P.length; C[1] += p[1] / P.length; }); } }
      var dx = C[0] - V[0], dy = C[1] - V[1], Ln = Math.sqrt(dx * dx + dy * dy) || 1;
      return { w: w, h: h, V: V, u: [dx / Ln, dy / Ln], L: Ln };
    }
    function rcR(L) { return D.num(L.k === 'image' ? L.rr : L.r, 0); }
    function rcPos(L) { var g = rcGeo(L), d = Math.min(g.L * 0.85, 14 + rcR(L)); return [Math.round((g.V[0] + g.u[0] * d) / g.w * 1000) / 10, Math.round((g.V[1] + g.u[1] * d) / g.h * 1000) / 10]; }
    function dragTip(t) { var tip = ov.querySelector('.dsel-sdt'); if (!tip) { var fr = ov.querySelector('.dsel-fr'); if (fr) { tip = el('span', 'dsel-sdt dsel-on'); fr.appendChild(tip); } } if (tip) { tip.textContent = t; tip.classList.add('dsel-on'); } }
    function cornerDrag(e, k) {
      var L = obj(k), g = rcGeo(L), off = pageOff(L.pg), poly = L.k !== 'image' && !!D.polyPts(shapeOfL(L), 10, 10, L), mx = poly ? 400 : Math.floor(Math.min(g.w, g.h) / 2), cur = rcR(L);
      begin(); document.documentElement.classList.add('dsel-dragging');
      function loc(ev) {   /* the pointer in the shape's own box, turned back if the shape is turned */
        var p = toLocal(ev.clientX, ev.clientY), cx = D.num(L.x, 0) + g.w / 2, cy = D.num(L.y, 0) + off + g.h / 2, a = -D.num(L.rot, 0) * Math.PI / 180, ux = p.x - cx, uy = p.y - cy;
        return [cx + ux * Math.cos(a) - uy * Math.sin(a) - D.num(L.x, 0), cy + ux * Math.sin(a) + uy * Math.cos(a) - D.num(L.y, 0) - off];
      }
      drag = {
        move: function (ev) {
          var q = loc(ev), d = (q[0] - g.V[0]) * g.u[0] + (q[1] - g.V[1]) * g.u[1], r = Math.round(Math.max(0, Math.min(mx, d - 14)));
          if (r !== cur) { cur = r; setP(k, 'r', r || null); if (L.k !== 'image' && shapeOfL(L) === 'roundRect' && !r) L.shape = 'rect'; if (api && api.drawLayers) api.drawLayers(); sync(); }
          dragTip('Corners: ' + r);
        },
        up: function () { document.documentElement.classList.remove('dsel-dragging'); done(); sync(); if (pmode === 'corners') paintPanel(); }
      };
    }
    function sidesDrag(e, k) {
      var L = obj(k), s = shapeOfL(L), n0 = D.ptsOf(s, L), y0 = e.clientY, cur = n0, star = s === 'star5';
      begin(); document.documentElement.classList.add('dsel-dragging'); dragTip((star ? 'Points: ' : 'Sides: ') + n0);
      drag = {
        move: function (ev) {
          var n = Math.max(3, Math.min(12, n0 + Math.round((ev.clientY - y0) / 14)));   /* down: more points; up: fewer, down to a triangle */
          if (n !== cur) { cur = n; if (star) L.pts = n; else L.sides = n; if (api && api.drawLayers) api.drawLayers(); sync(); }
          dragTip((star ? 'Points: ' : 'Sides: ') + n);
        },
        up: function () { document.documentElement.classList.remove('dsel-dragging'); done(); sync(); paint(); }
      };
    }
    function startHandle(e, h) {
      var k = sel[0]; if (!k || sel.length !== 1 || isLocked(k)) return;
      if (isB(k)) { if (dzn().kind === 'panels') startBlockScale(e, k, h); else startBlockResize(e, k, h); return; }
      if (!isL(k)) return;
      if (h === 'corner') { cornerDrag(e, k); return; }
      if (h === 'sides') { sidesDrag(e, k); return; }
      var L = obj(k); begin();
      var off = pageOff(L.pg), x0 = D.num(L.x, 0), y0 = D.num(L.y, 0), w0 = D.num(L.w, 100), h0 = D.num(L.h, 100), r0 = D.num(L.rot, 0) * Math.PI / 180;
      var c0 = { x: x0 + w0 / 2, y: y0 + h0 / 2 + off }, st = toLocal(e.clientX, e.clientY), keep = L.k === 'icon' || L.k === 'image';
      var le0 = domOf(k), lp = le0 ? localPos(le0) : { x: x0, y: y0 + off }, cR = { x: lp.x + w0 / 2, y: lp.y + h0 / 2 };
      document.documentElement.classList.add('dsel-dragging');
      drag = {
        move: function (ev) {
          var p = toLocal(ev.clientX, ev.clientY);
          if (h === 'rot') {
            var a = Math.atan2(p.y - cR.y, p.x - cR.x) * 180 / Math.PI + 90;
            if (ev.shiftKey) a = Math.round(a / 15) * 15;
            a = ((a % 360) + 540) % 360 - 180;
            [0, 90, -90, 180, -180].forEach(function (s) { if (Math.abs(a - s) < 4) a = s; });
            L.rot = Math.round(a * 10) / 10;
          } else {
            var dx = p.x - st.x, dy = p.y - st.y, lx = dx * Math.cos(-r0) - dy * Math.sin(-r0), ly = dx * Math.sin(-r0) + dy * Math.cos(-r0);
            var sx = /e/.test(h) ? 1 : /w/.test(h) ? -1 : 0, sy = /s/.test(h) ? 1 : /^n/.test(h) ? -1 : 0;
            var w = Math.max(8, w0 + sx * lx), hh = Math.max(L.shape === 'line' ? 1 : 8, h0 + sy * ly);
            if (sx && sy && (keep !== ev.shiftKey)) { var kk = Math.max(w / w0, hh / h0); w = w0 * kk; hh = h0 * kk; }
            var dw = w - w0, dh = hh - h0, cxl = sx * dw / 2, cyl = sy * dh / 2;
            var cx = c0.x + cxl * Math.cos(r0) - cyl * Math.sin(r0), cy = c0.y + cxl * Math.sin(r0) + cyl * Math.cos(r0);
            L.w = Math.round(w); L.h = Math.round(hh); L.x = Math.round(cx - w / 2); L.y = Math.round(cy - off - hh / 2);
          }
          if (api && api.drawLayers) api.drawLayers(); sync();
        },
        up: function () { document.documentElement.classList.remove('dsel-dragging'); if (api && api.drawLayers) api.drawLayers(); sync(); done(); }
      };
    }
    /* side handles make a document text narrower or wider; the corners of a picture or chart keep its shape */
    function startBlockResize(e, k, h) {
      var b = obj(k), n = domOf(k), col = n && n.parentElement; if (!b || !col) return;
      var colW = col.getBoundingClientRect().width, g = D.blockGeo(b), w0 = g.w, ox0 = g.ox, x0 = e.clientX, t = theme();
      begin(); document.documentElement.classList.add('dsel-dragging');
      drag = {
        move: function (ev) {
          var d = (ev.clientX - x0) / colW, w = w0, ox = ox0;
          var right = /e/.test(h), left = /w/.test(h);
          if (famOf(k) === 'image' || famOf(k) === 'chart') { var cen = Math.abs(ox0 - (1 - w0) / 2) < 0.01 && !ev.altKey; if (right) w = w0 + (cen ? 2 * d : d); if (left) { w = w0 - (cen ? 2 * d : d); if (!cen) ox = ox0 + d; } if (cen) ox = (1 - Math.max(0.15, Math.min(1, w))) / 2; }
          else { if (right) w = w0 + d; if (left) { w = w0 - d; ox = ox0 + d; } }
          w = Math.max(0.15, Math.min(1, w)); ox = Math.max(0, Math.min(1 - w, ox));
          if (!ev.altKey) [1, 0.75, 0.6667, 0.5, 0.3333, 0.25].forEach(function (s) { if (Math.abs(w - s) < 0.012) w = s; });
          b.w = r2(w * 1000) / 1000 === 1 ? undefined : Math.round(w * 10000) / 10000; b.ox = ox > 0.001 ? Math.round(ox * 10000) / 10000 : undefined;
          if (b.w == null) delete b.w; if (b.ox == null) delete b.ox;
          var bx = D.blockBox(b, t); n.setAttribute('style', bx.style); sync();
          var tip = ov.querySelector('.dsel-sdt'); if (!tip) { var fr = ov.querySelector('.dsel-fr'); if (fr) { tip = el('span', 'dsel-sdt dsel-on'); fr.appendChild(tip); } }
          if (tip) tip.textContent = 'Width ' + Math.round(w * 100) + '%';
        },
        up: function () { document.documentElement.classList.remove('dsel-dragging'); env.rerender(); done(); }
      };
    }
    function startMarquee(e) {
      var st = { x: e.clientX, y: e.clientY }, box = null, add = e.shiftKey || e.ctrlKey || e.metaKey;
      if (!add) clearSel();
      drag = {
        move: function (ev) {
          if (!box) { if (Math.abs(ev.clientX - st.x) + Math.abs(ev.clientY - st.y) < 6) return; box = el('div', 'dsel-mq'); ensureOv().appendChild(box); }
          var cr = canvas.getBoundingClientRect(), x1 = Math.min(ev.clientX, st.x), y1 = Math.min(ev.clientY, st.y), x2 = Math.max(ev.clientX, st.x), y2 = Math.max(ev.clientY, st.y);
          box.style.cssText = 'display:block;left:' + (x1 - cr.left + canvas.scrollLeft) + 'px;top:' + (y1 - cr.top + canvas.scrollTop) + 'px;width:' + (x2 - x1) + 'px;height:' + (y2 - y1) + 'px';
          box.__r = { x1: x1, y1: y1, x2: x2, y2: y2 };
        },
        up: function () {
          if (!box) return;
          var r = box.__r, hits = [];
          box.remove();
          var cand = layers().map(function (L) { return 'L:' + L.id; }).concat(blocks().map(function (b) { return 'B:' + b.id; }));
          cand.forEach(function (k) {
            if (isLocked(k) || hidden(k)) return;
            var ns = isL(k) && obj(k).pg === 'm' ? Array.prototype.slice.call(docBox.querySelectorAll('.dsd-L[data-lid="' + kid(k) + '"]')) : [domOf(k)];
            ns.forEach(function (n) {
            if (!n || hits.indexOf(k) >= 0) return;
            var b = n.getBoundingClientRect();
            if (b.width && b.left >= r.x1 && b.top >= r.y1 && b.right <= r.x2 && b.bottom <= r.y2) { hits.push(k); if (n.dataset.mp != null) mInst = +n.dataset.mp; }
            });
          });
          if (hits.length) select(add ? sel.concat(hits) : hits); else sync();
        }
      };
      e.preventDefault();
    }
    function onMove(e) { if (drag) drag.move(e); }
    function onUp(e) { if (drag) { var d = drag; drag = null; d.up(e); paint(); } }

    /* ================= typing in a free text box ================= */
    function startEdit(k) {
      var L = obj(k), e = domOf(k); if (!L || L.k !== 'text' || L.lock || !e) return;
      begin(); editing = k; sel = [k];
      var tx = e.querySelector('.dsd-Ltx');
      tx.setAttribute('contenteditable', 'true'); tx.setAttribute('spellcheck', 'true'); tx.focus();
      try { var r = document.createRange(); r.selectNodeContents(tx); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); } catch (x) { /* ignore */ }
      tx.addEventListener('input', function () {
        L.tx = tx.innerText.replace(/\n$/, '');
        var need = Math.ceil(tx.scrollHeight); if (need > L.h) { L.h = need; e.style.height = need + 'px'; }
      });
      sync(); paint();
    }
    function stopEdit() {
      if (!editing) return;
      var k = editing; editing = null;
      var e = domOf(k), L = obj(k);
      if (e && L) { var tx = e.querySelector('.dsd-Ltx'); if (tx) { L.tx = tx.innerText.replace(/\n$/, ''); tx.removeAttribute('contenteditable'); } }
      if (api && api.drawLayers) api.drawLayers(); sync(); done();
    }
    function onDbl(e) {
      var mkb = e.target.closest('.dsd-blk[data-t="s_mark"]');
      if (mkb && docBox.contains(mkb)) { e.preventDefault(); logoDialog(); return; }
      var le = e.target.closest('.dsd-L'); if (!le || !docBox.contains(le)) return;
      var k = 'L:' + (le.dataset.lid || le.dataset.glid), L = obj(k); if (!L) return;
      if (L.k === 'text') { e.preventDefault(); startEdit(k); }
      else if (L.k === 'chart') chartOf(k);
      else if (L.k === 'image') changePic(k);
      else if (L.k === 'icon') { pmode = 'icon'; paint(); }
    }

    /* ================= changes ================= */
    function del(keys) {
      keys = (keys || sel).filter(function (k) { return !isLocked(k); }); if (!keys.length) return;
      begin();
      var lids = keys.filter(isL).map(kid), bids = keys.filter(isB).map(kid);
      doc.layers = layers().filter(function (L) { return lids.indexOf(L.id) < 0; });
      doc.blocks = blocks().filter(function (b) { return bids.indexOf(b.id) < 0; });
      keys.filter(isC).forEach(function (k) { doc.chide = doc.chide || {}; doc.chide[kid(k)] = 1; });
      sel = [];
      env.rerender(); done(); selChanged();
    }
    function cloneL(L, dx, dy, keepGrp) { var c = D.clone(L); c.id = uid(); c.x = D.num(c.x, 0) + (dx || 0); c.y = D.num(c.y, 0) + (dy || 0); if (!keepGrp) delete c.grp; return c; }
    function remapGroups(list) { var m = {}; list.forEach(function (c) { if (c.grp) { m[c.grp] = m[c.grp] || 'g' + uid(); c.grp = m[c.grp]; } }); return list; }
    function duplicate() {
      var cks = sel.filter(isC), ks = sel.filter(function (k) { return !isC(k); });
      if (!ks.length && !cks.length) return;
      begin();
      cks.forEach(function (k) { var L = freeTextOf(k); if (!L) return; L.id = uid(); L.x += 16; L.y += 16; Object.keys(L).forEach(function (x) { if (L[x] == null) delete L[x]; }); layers().push(L); ks.push('L:' + L.id + '#new'); });
      var fresh = ks.filter(function (k) { return /#new$/.test(k); }).map(function (k) { return k.replace('#new', ''); }); ks = ks.filter(function (k) { return !/#new$/.test(k); });
      if (!ks.length) { sel = fresh; env.rerender(); done(); selChanged(); return; }
      var nl = remapGroups(ks.filter(isL).map(function (k) { return cloneL(obj(k), 16, 16, true); }));
      doc.layers = layers().concat(nl);
      var nb = [];
      ks.filter(isB).forEach(function (k) { var b = obj(k), c = D.clone(b); c.id = D.uid(); blocks().splice(bIdx(b.id) + 1, 0, c); nb.push(c); });
      sel = nl.map(function (c) { return 'L:' + c.id; }).concat(nb.map(function (c) { return 'B:' + c.id; })).concat(fresh);
      env.rerender(); done(); selChanged();
    }
    function copy(quiet) {
      if (!sel.length) return false;
      CLIP = sel.map(function (k) { if (isC(k)) { var L = freeTextOf(k); return L ? { k: 'L', o: Object.assign(L, { id: 'x' }) } : null; } return { k: isL(k) ? 'L' : 'B', o: D.clone(obj(k)) }; }).filter(Boolean);
      if (!quiet) env.toast(CLIP.length + ' element' + (CLIP.length > 1 ? 's' : '') + ' copied. Go to any page and press Ctrl + V (or right-click → Paste).', '', 3200);
      return CLIP.length > 0;
    }
    function cut() { if (copy(true)) del(); }
    function paste() {
      if (!CLIP.length) return;
      begin();
      var pg = viewPage(), outL = [], outB = [], at = sel.length && isB(sel[sel.length - 1]) ? bIdx(kid(sel[sel.length - 1])) + 1 : -1;
      if (at < 0) { var bpm = blockPages(), last = -1; blocks().forEach(function (b, i) { if (bpm[b.id] === pg) last = i; }); at = last >= 0 ? last + 1 : blocks().length; }
      var Ls = CLIP.filter(function (c) { return c.k === 'L'; }).map(function (c) { var same = (c.o.pg === pg || c.o.pg === 'm') && lById(c.o.id); var n = cloneL(c.o, same ? 16 : 0, same ? 16 : 0, true); n.pg = pg; delete n.art; return n; });
      remapGroups(Ls).forEach(function (n) { layers().push(n); outL.push(n); });
      CLIP.filter(function (c) { return c.k === 'B'; }).forEach(function (c) { var n = D.clone(c.o); n.id = D.uid(); blocks().splice(at++, 0, n); outB.push(n); });
      sel = outL.map(function (c) { return 'L:' + c.id; }).concat(outB.map(function (c) { return 'B:' + c.id; }));
      env.rerender(); done(); selChanged();
      env.toast('Pasted on ' + pageName(pg) + '.', '', 1400);
    }
    function groupIds() { return sel.filter(isL); }
    function oneGroup() { var a = sel.map(obj); return sel.length > 1 && sel.every(isL) && a[0].grp && a.every(function (x) { return x.grp === a[0].grp; }); }
    function hasGroup() { return sel.some(function (k) { return isL(k) && obj(k).grp; }); }
    function doGroup() {
      var a = groupIds(); if (a.length < 2) return; begin(); var g = 'g' + uid(); a.forEach(function (k) { obj(k).grp = g; });
      /* shapes that all have a picture fill share the first one's picture once grouped */
      var pk = a.filter(function (k) { var q = getP(k, 'fill'); return supports(k, 'fill') && q && q.m === 'pic' && D.picSrcOK(q.src); });
      if (pk.length > 1) { var f0 = getP(pk[0], 'fill'), keep = sel; pk.forEach(function (k) { var q = D.clone(f0); delete q.gb; setP(k, 'fill', q); }); sel = pk; setPicGroup(true); sel = keep; }
      redraw(a); done(); paint(); env.toast('Grouped.', '', 1200);
    }
    function doUngroup() {
      begin(); var pf = 0;
      sel.filter(isL).forEach(function (k) {
        var q = getP(k, 'fill'); if (q && q.m === 'pic' && q.gb) { setP(k, 'fill', { m: 'solid', c: '@a' }); pf++; }   /* the group's picture goes with the group */
        delete obj(k).grp;
      });
      redraw(); done(); paint(); env.toast(pf ? 'Ungrouped. The group picture was removed.' : 'Ungrouped.', '', 1600);
    }
    function arrange(how) {
      var ks = sel.filter(isL), ids = ks.map(kid);
      if (!ks.length) {                       /* document texts: move up / down in the document */
        var bk = sel.filter(isB); if (!bk.length) return;
        begin();
        var a = blocks(), mv = a.filter(function (b) { return bk.indexOf('B:' + b.id) >= 0; }), rest = a.filter(function (b) { return bk.indexOf('B:' + b.id) < 0; }), i0 = a.indexOf(mv[0]);
        var to = how === 'front' ? 0 : how === 'back' ? rest.length : how === 'fwd' ? Math.max(0, i0 - 1) : Math.min(rest.length, i0 + 1);
        rest.splice.apply(rest, [to, 0].concat(mv)); doc.blocks = rest; env.rerender(); done();
        var n = domOf(bk[0]); if (n) n.scrollIntoView({ block: 'nearest' });
        return;
      }
      begin();
      var arr = layers().slice(), moving = arr.filter(function (L) { return ids.indexOf(L.id) >= 0; }), rest2 = arr.filter(function (L) { return ids.indexOf(L.id) < 0; });
      if (how === 'front') doc.layers = rest2.concat(moving);
      else if (how === 'back') doc.layers = moving.concat(rest2);
      else {
        var step = how === 'fwd' ? 1 : -1, list = step > 0 ? moving.slice().reverse() : moving;
        list.forEach(function (L) {
          var i = arr.indexOf(L), j = i + step;
          while (j >= 0 && j < arr.length && (arr[j].pg !== L.pg || ids.indexOf(arr[j].id) >= 0)) j += step;
          if (j >= 0 && j < arr.length) { arr.splice(i, 1); arr.splice(j, 0, L); }
        });
        doc.layers = arr;
      }
      if (api && api.drawLayers) api.drawLayers(); sync(); done(); paintPanel();
    }
    function setLock(keys, v) { begin(); keys.forEach(function (k) { var o = obj(k); if (o && !isC(k)) { if (v) o.lock = 1; else delete o.lock; } }); env.rerender(); done(); paint(); }
    function setHide(keys, v) {
      begin();
      keys.forEach(function (k) { var o = obj(k); if (!o) return; if (isC(k)) { doc.chide = doc.chide || {}; if (v) doc.chide[kid(k)] = 1; else delete doc.chide[kid(k)]; } else if (v) o.hide = 1; else delete o.hide; });
      sel = sel.filter(function (k) { return keys.indexOf(k) < 0 || !v; });
      env.rerender(); done(); paint();
    }
    function alignTo(how) {
      var ks = sel.filter(function (k) { return !isLocked(k) && !isC(k); }); if (!ks.length) return;
      begin();
      var sz = size(), lk = ks.filter(isL), many = lk.length > 1 && amode === 'sel';
      ks.filter(isB).forEach(function (k) {   /* a document text lines up inside the text column */
        var b = obj(k), g = D.blockGeo(b);
        if (how === 'l') b.ox = 0; if (how === 'c') b.ox = r2((1 - g.w) / 2 * 1000) / 1000; if (how === 'r') b.ox = r2((1 - g.w) * 1000) / 1000;
        if (!b.ox) delete b.ox;
      });
      if (lk.length) {
        var box;
        if (!many) box = { x1: 0, y1: 0, x2: sz.w, y2: sz.h };
        else box = lk.reduce(function (bb, k) { var L = obj(k); return { x1: Math.min(bb.x1, L.x), y1: Math.min(bb.y1, L.y), x2: Math.max(bb.x2, L.x + L.w), y2: Math.max(bb.y2, L.y + L.h) }; }, { x1: 1e9, y1: 1e9, x2: -1e9, y2: -1e9 });
        if (how === 'h' || how === 'v') {
          var kx = how === 'h' ? 'x' : 'y', ks2 = how === 'h' ? 'w' : 'h', srt = lk.map(obj).sort(function (p, q) { return p[kx] - q[kx]; });
          if (srt.length > 2) { var tot = srt.reduce(function (s, L) { return s + L[ks2]; }, 0), span = srt[srt.length - 1][kx] + srt[srt.length - 1][ks2] - srt[0][kx], gap = (span - tot) / (srt.length - 1), pos = srt[0][kx]; srt.forEach(function (L) { L[kx] = Math.round(pos); pos += L[ks2] + gap; }); }
        } else lk.forEach(function (k) {
          var L = obj(k);
          if (how === 'l') L.x = box.x1; if (how === 'r') L.x = box.x2 - L.w; if (how === 'c') L.x = Math.round((box.x1 + box.x2) / 2 - L.w / 2);
          if (how === 't') L.y = box.y1; if (how === 'b') L.y = box.y2 - L.h; if (how === 'm') L.y = Math.round((box.y1 + box.y2) / 2 - L.h / 2);
        });
      }
      redraw(ks); done();
    }
    function addLayer(L, quiet) {
      var sz = size(), pg = L.pg != null ? L.pg : viewPage();
      L.id = uid(); L.pg = pg;
      if (L.x == null) L.x = Math.round((sz.w - L.w) / 2);
      if (L.y == null) {
        var cr = canvas.getBoundingClientRect(), mid = toLocal(0, cr.top + cr.height / 2).y, org = pg === 'c' ? (api && api.cover) : (isSingle() ? null : (api && api.flow));
        var oy = org ? localPos(org).y : 0;
        L.y = Math.round(Math.max(0, Math.min(sz.h - L.h, mid - oy - pageOff(pg) - L.h / 2)));
      }
      env.snapshot(); layers().push(L);
      if (api && api.drawLayers) api.drawLayers(); env.changed();
      sel = ['L:' + L.id]; selChanged();
      if (!quiet && pmode === 'add') { pmode = ''; paint(); }
      return L;
    }
    function onDark() { return viewPage() === 'c' && /^(diagonal|gradient|book)$/.test(dzn().cover || ''); }
    function addText(kind) {
      var t = theme(), dk = onDark();
      var L = kind === 'head' ? { tx: 'Your heading', fs: 28, fw: 700, ff: t.head, tc: dk ? '@w' : '@p', w: 420, h: 60 } : kind === 'sub' ? { tx: 'Your subheading', fs: 18, fw: 600, ff: t.head, tc: dk ? '@a' : '@s', w: 380, h: 44 } : { tx: 'Your text', fs: 14, fw: 400, tc: dk ? '@w' : '@ink', w: 320, h: 56 };
      L.k = 'text'; L.al = 'left'; L.va = 'top'; L.lh = 1.25; L.pad = 8; L.fill = { m: 'none' }; L.ln = { m: 'none' };
      var n = addLayer(L);
      setTimeout(function () { startEdit('L:' + n.id); }, 40);
    }
    function addShape(s) {
      var c0 = onDark() ? '@a' : '@p';
      addLayer({ k: 'shape', shape: s, w: s === 'line' ? 260 : /Arrow|chevron|parallelogram/.test(s) ? 220 : 160, h: s === 'line' ? 12 : /Arrow/.test(s) ? 100 : 160,
        fill: s === 'line' ? { m: 'none' } : { m: 'solid', c: c0 }, ln: s === 'line' ? { m: 'solid', c: c0, w: 3, d: 'solid' } : { m: 'none', c: '@a', w: 2 }, r: s === 'roundRect' ? 18 : 0 });
    }
    function addPicture() {
      env.pickPicture(function (p) {
        var src = p.data || p.src, img = new Image();
        img.onload = function () { var w = 320, h = Math.round(w * img.naturalHeight / Math.max(1, img.naturalWidth)); addLayer({ k: 'image', src: src, pid: p.pid || 0, url: p.url || '', w: w, h: Math.min(h, 600), rr: 0, ln: { m: 'none', c: '@w', w: 4 } }); };
        img.onerror = function () { addLayer({ k: 'image', src: src, pid: p.pid || 0, url: p.url || '', w: 320, h: 200 }); };
        img.src = src;
      });
    }
    function changePic(k) {
      var o0 = obj(k);
      env.pickPicture(function (p) {
        begin(); var o = obj(k);
        delete o.osrc; delete o.crop; delete o.opid;
        if (isL(k)) { o.src = p.data || p.src; o.pid = p.pid || 0; o.url = p.url || ''; } else { o.src = p.data || p.src; o.pid = p.pid || 0; o.url = p.url || ''; }
        redraw([k]); done();
      }, { change: !!(o0 && (o0.src || o0.url)), prompt: (o0 && (o0.cap || o0.alt || o0.name)) || '' });
    }
    /* ---- crop: a window with the whole picture and a frame to keep; the cut picture replaces it (Word gets the same) ---- */
    function picData(src) {
      if (/^data:image\//.test(src)) return Promise.resolve(src);
      return fetch(src, { mode: 'cors', credentials: 'omit' }).then(function (r) { if (!r.ok) throw new Error('x'); return r.blob(); }).then(function (b) {
        return new Promise(function (res, rej) { var fr = new FileReader(); fr.onload = function () { res(fr.result); }; fr.onerror = rej; fr.readAsDataURL(b); });
      });
    }
    function cropPic(k) {
      var o = obj(k); if (!o || isLocked(k)) return;
      var src0 = o.osrc || o.src || o.url; if (!src0) { env.toast('Add a picture first.', 'err'); return; }
      picData(src0).catch(function () { return o.src && o.src !== src0 ? picData(o.src) : Promise.reject(); }).then(function (data) {
        var img = new Image();
        img.onload = function () { cropWin(k, o, data, img); };
        img.onerror = function () { env.toast('This picture could not be opened for cropping.', 'err'); };
        img.src = data;
      }).catch(function () { env.toast('This picture could not be opened for cropping. Change it to a store or uploaded picture first.', 'err'); });
    }
    function cropWin(k, o, data, img) {
      var IW = img.naturalWidth, IH = img.naturalHeight, c0 = o.osrc && o.crop ? o.crop : { x: 0, y: 0, w: 1, h: 1 };
      var r = { x: c0.x * IW, y: c0.y * IH, w: c0.w * IW, h: c0.h * IH }, ratio = 0;
      var m = el('div', 'dd-crop');
      m.innerHTML = '<div class="cr-box"><div class="cr-hd"><b>' + IC.crop + ' Crop picture</b><span>Drag the frame or its corners. The part outside the frame is cut away.</span></div>' +
        '<div class="cr-stage"><div class="cr-wrap"><img alt=""><div class="cr-dim"></div><div class="cr-fr"><i data-c="nw"></i><i data-c="n"></i><i data-c="ne"></i><i data-c="e"></i><i data-c="se"></i><i data-c="s"></i><i data-c="sw"></i><i data-c="w"></i><u></u></div></div></div>' +
        '<div class="cr-ft"><div class="cr-ar">' + [['0', 'Free'], ['o', 'Original'], ['1', '1:1'], ['1.3333', '4:3'], ['1.7778', '16:9'], ['0.75', '3:4']].map(function (a, i) { return '<button type="button" data-ar="' + a[0] + '"' + (i === 0 ? ' class="on"' : '') + '>' + a[1] + '</button>'; }).join('') + '</div>' +
        '<span class="cr-sp"></span><button type="button" class="cr-btn" data-cr="reset">Reset</button><button type="button" class="cr-btn" data-cr="x">Cancel</button><button type="button" class="cr-btn pri" data-cr="ok">Apply crop</button></div></div>';
      document.body.appendChild(m);
      var wrap = m.querySelector('.cr-wrap'), im = m.querySelector('img'), fr = m.querySelector('.cr-fr'), dim = m.querySelector('.cr-dim'), stage = m.querySelector('.cr-stage');
      im.src = data;
      var sc = 1;
      function layout() { var sw = stage.clientWidth - 24, sh = stage.clientHeight - 24; sc = Math.min(sw / IW, sh / IH, 4); wrap.style.width = Math.round(IW * sc) + 'px'; wrap.style.height = Math.round(IH * sc) + 'px'; draw(); }
      function clamp() {
        var mn = Math.max(8 / sc, 4);
        r.w = Math.max(mn, Math.min(IW, r.w)); r.h = Math.max(mn, Math.min(IH, r.h));
        r.x = Math.max(0, Math.min(IW - r.w, r.x)); r.y = Math.max(0, Math.min(IH - r.h, r.y));
      }
      function draw() {
        clamp();
        fr.style.cssText = 'left:' + r.x * sc + 'px;top:' + r.y * sc + 'px;width:' + r.w * sc + 'px;height:' + r.h * sc + 'px';
        dim.style.clipPath = 'polygon(0 0,100% 0,100% 100%,0 100%,0 0,' + r.x * sc + 'px ' + r.y * sc + 'px,' + r.x * sc + 'px ' + (r.y + r.h) * sc + 'px,' + (r.x + r.w) * sc + 'px ' + (r.y + r.h) * sc + 'px,' + (r.x + r.w) * sc + 'px ' + r.y * sc + 'px,' + r.x * sc + 'px ' + r.y * sc + 'px)';
      }
      function setRatio(a) {
        ratio = a; if (!a) return;
        var cx = r.x + r.w / 2, cy = r.y + r.h / 2, w = r.w, h = w / a;
        if (h > IH) { h = IH; w = h * a; } if (w > IW) { w = IW; h = w / a; }
        if (h > r.h && r.h > 0 && w / a > r.h) { h = Math.min(IH, Math.max(r.h, h)); w = h * a; }
        r = { x: cx - w / 2, y: cy - h / 2, w: w, h: h }; draw();
      }
      var dragC = null;
      wrap.addEventListener('pointerdown', function (e) {
        var h = e.target.getAttribute('data-c') || (e.target.closest('.cr-fr') ? 'mv' : null); if (!h) return;
        e.preventDefault(); wrap.setPointerCapture(e.pointerId);
        dragC = { h: h, x: e.clientX, y: e.clientY, r: { x: r.x, y: r.y, w: r.w, h: r.h } };
      });
      wrap.addEventListener('pointermove', function (e) {
        if (!dragC) return;
        var dx = (e.clientX - dragC.x) / sc, dy = (e.clientY - dragC.y) / sc, o0 = dragC.r, h = dragC.h, n = { x: o0.x, y: o0.y, w: o0.w, h: o0.h };
        if (h === 'mv') { n.x = o0.x + dx; n.y = o0.y + dy; r = n; draw(); return; }
        if (/e/.test(h)) n.w = Math.min(IW - o0.x, Math.max(4, o0.w + dx));
        if (/w/.test(h)) { var nx = Math.max(0, Math.min(o0.x + o0.w - 4, o0.x + dx)); n.w = o0.w + (o0.x - nx); n.x = nx; }
        if (/s/.test(h)) n.h = Math.min(IH - o0.y, Math.max(4, o0.h + dy));
        if (/^n/.test(h)) { var ny = Math.max(0, Math.min(o0.y + o0.h - 4, o0.y + dy)); n.h = o0.h + (o0.y - ny); n.y = ny; }
        if (ratio) {
          if (h === 'n' || h === 's') { n.w = n.h * ratio; if (n.x + n.w > IW) { n.w = IW - n.x; n.h = n.w / ratio; } }
          else { n.h = n.w / ratio; if (/^n/.test(h)) n.y = o0.y + o0.h - n.h; if (n.y < 0) { n.y = 0; n.h = /^n/.test(h) ? o0.y + o0.h : n.h; n.w = n.h * ratio; } if (n.y + n.h > IH) { n.h = IH - n.y; n.w = n.h * ratio; } if (/w/.test(h)) n.x = o0.x + o0.w - n.w; }
        }
        r = n; draw();
      });
      wrap.addEventListener('pointerup', function () { dragC = null; });
      function close() { m.remove(); window.removeEventListener('resize', layout); document.removeEventListener('keydown', onK, true); }
      function onK(e) { if (e.key === 'Escape') { e.stopPropagation(); close(); } if (e.key === 'Enter') { e.stopPropagation(); apply(); } }
      document.addEventListener('keydown', onK, true);
      function apply() {
        var full = r.x < 0.5 && r.y < 0.5 && r.w > IW - 1 && r.h > IH - 1;
        var mx = 1600, kk = Math.min(1, mx / Math.max(r.w, r.h)), cv = document.createElement('canvas');
        cv.width = Math.max(1, Math.round(r.w * kk)); cv.height = Math.max(1, Math.round(r.h * kk));
        var cx = cv.getContext('2d'), png = /^data:image\/(png|gif|webp)/.test(data);
        if (!png) { cx.fillStyle = '#fff'; cx.fillRect(0, 0, cv.width, cv.height); }
        cx.drawImage(img, r.x, r.y, r.w, r.h, 0, 0, cv.width, cv.height);
        var out = png ? cv.toDataURL('image/png') : cv.toDataURL('image/jpeg', 0.88);
        if (png && out.length > 700000) out = cv.toDataURL('image/jpeg', 0.88);
        begin();
        if (!o.osrc && data.length < 450000) o.osrc = data;          /* the whole picture, to crop again later */
        if (o.osrc && o.osrc.length >= 450000) delete o.osrc;
        if (full) { o.src = o.osrc || data; delete o.crop; delete o.osrc; }
        else { o.src = out; o.crop = { x: r.x / IW, y: r.y / IH, w: r.w / IW, h: r.h / IH }; }
        if (o.pid) { o.opid = o.pid; delete o.pid; }
        if (isL(k)) { var w0 = D.num(o.w, 200), h0 = D.num(o.h, 150), nh = Math.round(w0 * r.h / r.w); o.y = Math.round(D.num(o.y, 0) + (h0 - nh) / 2); o.h = Math.max(8, nh); }
        close(); redraw([k]); done();
      }
      m.addEventListener('click', function (e) {
        var a = e.target.closest('[data-ar]');
        if (a) { m.querySelectorAll('[data-ar]').forEach(function (x) { x.classList.toggle('on', x === a); }); var v = a.dataset.ar; setRatio(v === 'o' ? IW / IH : +v); return; }
        var b = e.target.closest('[data-cr]'); if (!b) { if (e.target === m) close(); return; }
        if (b.dataset.cr === 'x') close();
        if (b.dataset.cr === 'reset') { r = { x: 0, y: 0, w: IW, h: IH }; ratio = 0; m.querySelectorAll('[data-ar]').forEach(function (x, i) { x.classList.toggle('on', i === 0); }); draw(); }
        if (b.dataset.cr === 'ok') apply();
      });
      window.addEventListener('resize', layout);
      requestAnimationFrame(layout);
    }
    function addChart() {
      var ch = { kind: 'column', title: 'Chart title', labels: ['Q1', 'Q2', 'Q3', 'Q4'], series: [{ name: 'Series 1', values: [12, 19, 15, 24] }], legend: true, vals: true, unit: '' };
      editChart(ch, function (c) { addLayer({ k: 'chart', ch: c, w: 440, h: Math.round(440 * D.chartRatio(c)), fill: { m: 'none' } }); }, true);
    }
    function chartOf(k) {
      var o = obj(k); if (!o) return;
      if (isL(k)) editChart(o.ch || {}, function (c) { begin(); o.ch = c; redraw([k]); done(); });
      else editChart(o, function (c) { begin(); ['kind', 'title', 'labels', 'series', 'legend', 'vals', 'unit'].forEach(function (x) { o[x] = c[x]; }); redraw([k]); done(); });
    }
    function addIconByName(n, replaceK) {
      fetchIcon(n).then(function (ic) {
        if (!ic) { env.toast('That icon could not be loaded.', 'err'); return; }
        if (replaceK) { begin(); obj(replaceK).ic = ic; redraw([replaceK]); done(); return; }
        addLayer({ k: 'icon', ic: ic, w: 64, h: Math.round(64 * ic.vh / ic.vw), tc: onDark() ? '@w' : '@p' });
      });
    }
    function fetchIcon(n) {
      var x = String(n).split(':'); if (x.length !== 2) return Promise.resolve(null);
      if (ICON_CACHE['#' + n]) return Promise.resolve(ICON_CACHE['#' + n]);
      return fetch('https://api.iconify.design/' + x[0] + '.json?icons=' + x[1]).then(function (r) { return r.json(); }).then(function (d) {
        var ic = d.icons && d.icons[x[1]], al = d.aliases && d.aliases[x[1]], hops = 0;
        while (!ic && al && hops++ < 4) { ic = d.icons[al.parent]; if (!ic) al = d.aliases[al.parent]; }
        if (!ic) return null;
        var l = ic.left || d.left || 0, t = ic.top || d.top || 0, body = String(ic.body || '').replace(/<script[\s\S]*?<\/script>/gi, '');
        if (l || t) body = '<g transform="translate(' + (-l) + ',' + (-t) + ')">' + body + '</g>';
        return (ICON_CACHE['#' + n] = { n: n, b: body, vw: ic.width || (al && al.width) || d.width || 16, vh: ic.height || (al && al.height) || d.height || 16 });
      }).catch(function () { return null; });
    }
    function newBlock(t) {
      var bm = env.blocks, nb = bm.newBlock(t);
      begin();
      var at = sel.length && isB(sel[sel.length - 1]) ? bIdx(kid(sel[sel.length - 1])) + 1 : -1;
      if (at < 0) {     /* after the last text on the page in view */
        var m = blockPages(), pg = viewPage(), last = -1;
        blocks().forEach(function (b, i) { if (m[b.id] === pg) last = i; });
        at = last >= 0 ? last + 1 : blocks().length;
      }
      blocks().splice(at, 0, nb);
      env.rerender(); done();
      sel = ['B:' + nb.id]; selChanged();
      var n = domOf(sel[0]); if (n) { n.scrollIntoView({ block: 'center' }); var ce = n.querySelector('[contenteditable]'); if (ce && t !== 'image' && t !== 'chart') setTimeout(function () { ce.focus(); }, 30); }
      if (t === 'image') changePic('B:' + nb.id);
      if (t === 'chart') chartOf('B:' + nb.id);
      if (pmode === 'add') { pmode = ''; paint(); }
    }

    /* ---- the brush: give the look of one element to similar ones ---- */
    var LOOKK = ['ff', 'fs', 'fw', 'it', 'ul', 'sk', 'caps', 'al', 'lh', 'ls', 'tc', 'fill', 'ln', 'sh', 'op', 'r'];
    function lookOf(k) { var o = {}; LOOKK.forEach(function (p) { if (!supports(k, p)) return; var v = isL(k) ? obj(k)[p === 'sk' ? 'st' : p === 'r' && obj(k).k === 'image' ? 'rr' : p] : stOf(k)[p]; if (v != null) o[p] = D.clone(v); }); return o; }
    function giveLook(k, look) { LOOKK.forEach(function (p) { if (!supports(k, p)) return; setP(k, p, look[p] != null ? D.clone(look[p]) : null); }); }
    function similar(k, where) {
      var f = famOf(k), o = obj(k), pg = pageOfKey(k), out = [];
      var cand = layers().map(function (L) { return 'L:' + L.id; }).concat(blocks().map(function (b) { return 'B:' + b.id; }));
      cand.forEach(function (x) {
        if (x === k || isLocked(x)) return;
        var ox = obj(x); if (famOf(x) !== f) return;
        if (isB(k) && (!isB(x) || ox.t !== o.t)) return;
        if (isL(k) && (!isL(x) || ox.k !== o.k || (o.k === 'shape' && ox.shape !== o.shape))) return;
        if (where === 'page' && pageOfKey(x) !== pg) return;
        out.push(x);
      });
      return out;
    }
    function applyItems() {
      var k = sel[0]; if (!k || sel.length !== 1) return [];
      var nm = kindName(k).toLowerCase();
      return [
        { l: 'Copy effects (then click another element)', f: function () { STYLE_CLIP = lookOf(k); brushOn = true; host.classList.add('dsel-brush'); env.toast('Click the element that should get this look. Esc cancels.', '', 3500); } },
        { l: 'Apply this look to every ' + nm + ' on this page', f: function () { var a = similar(k, 'page'); if (!a.length) { env.toast('No other ' + nm + ' on this page.'); return; } begin(); var lk = lookOf(k); a.forEach(function (x) { giveLook(x, lk); }); redraw(a.concat([k])); done(); env.toast('Applied to ' + a.length + '.', 'ok', 1500); } },
        { l: 'Apply this look to every ' + nm + ' in the document', f: function () { var a = similar(k, 'all'); if (!a.length) { env.toast('No other ' + nm + ' in the document.'); return; } begin(); var lk = lookOf(k); a.forEach(function (x) { giveLook(x, lk); }); redraw(a.concat([k])); done(); env.toast('Applied to ' + a.length + '.', 'ok', 1500); } }
      ];
    }
    function applyBrush() {
      var targets = sel.slice(); brushOn = false; host.classList.remove('dsel-brush');
      if (!STYLE_CLIP) return;
      begin(); targets.forEach(function (k) { giveLook(k, STYLE_CLIP); }); redraw(targets); done();
      env.toast('Look applied.', 'ok', 1400); paint();
    }
    function pasteFx() { if (!STYLE_CLIP) return; begin(); sel.forEach(function (k) { giveLook(k, STYLE_CLIP); }); redraw(); done(); }

    /* ================= the bars: object settings on top, fixed buttons at the bottom ================= */
    function btn(a, lab, title, cls, on1) { return '<button type="button" class="dsel-b ' + (cls || '') + (on1 ? ' dsel-on' : '') + '" data-a="' + a + '" title="' + esc(title || '') + '">' + lab + '</button>'; }
    function wtName(w) { var n = 'Regular', b = 1e9; WT.forEach(function (x) { var d = Math.abs(x[0] - w); if (d < b) { b = d; n = x[1]; } }); return n; }
    function paintSw(p) { if (!p || !p.m || p.m === 'none') return ''; return p.m === 'grad' ? D.paintCSS(p, theme()) : hex(p.c || '@p'); }
    function sw(c) { return '<i class="dsel-sw' + (c ? '' : ' dsel-sw-none') + '" style="' + (c ? 'background:' + c : '') + '"></i>'; }
    function fsizeHTML(v) { v = Math.round(v * 2) / 2; return '<span class="dsel-fsz" title="Font size: drag the slider, or type a size"><span class="dsel-fszl" data-a="fsz">Font size</span><input type="number" data-i="size" min="4" max="300" step="0.5" value="' + v + '"><span class="dsel-fszp"><span class="dsel-fszin"><span class="dsel-fa1">A</span><input type="range" data-r="size" min="6" max="120" step="0.5" value="' + Math.min(120, v) + '"><span class="dsel-fa2">A</span></span></span></span>'; }
    function stepper(key, val, title, min, max, st) { return '<span class="dsel-num" title="' + esc(title) + '"><button type="button" data-a="st-" data-k="' + key + '" data-s="' + st + '">&minus;</button><input type="number" data-i="' + key + '" min="' + min + '" max="' + max + '" step="' + st + '" value="' + val + '"><button type="button" data-a="st+" data-k="' + key + '" data-s="' + st + '">+</button></span>'; }
    var TABLEISH = /^(table|stats|timeline|cols|kv|inv|todo|sigs|s_details|cal|week)$/;
    function leftHTML() {
      var k = sel[0];
      if (!k) return brushOn ? '<span class="dsel-hint">Click the element that should get the copied look. Esc cancels.</span>' : '<span class="dsel-hint">Click an element to edit it. Shift + click, or drag a box around elements, to select several. Ctrl + A selects all.</span>';
      var f = famOf(k), o = obj(k), h;
      if (sel.length > 1) {
        var same = sel.every(function (x) { return famOf(x) === f; }), og = oneGroup(), onlyL = sel.every(isL);
        h = '<span class="dsel-kind">' + (og ? 'Group of ' : '') + sel.length + ' elements</span>' +
          (onlyL && !og ? btn('group', 'Group', 'Keep these elements together (Ctrl+G)', 'dsel-grp') : '') + (hasGroup() ? btn('ungroup', 'Ungroup', 'Split the group (Ctrl+Shift+G)') : '') +
          btn('align', svg(AIC.l) + ' Align &#9662;', 'Align and distribute: to each other, or to the page', '', pmode === 'align');
        if (!same) return h + '<span class="dsel-lbl">Drag to move, pull a corner to resize.</span>';
      } else {
        h = '<span class="dsel-kind">' + esc(kindName(k)) + '</span>';
        if (isL(k) && o.grp) h += '<span class="dsel-lbl">in a group</span>';
        if (isLocked(k)) return h + '<span class="dsel-lbl">Locked</span>' + btn('unlock', IC.unlock + ' Unlock', 'Unlock this element');
      }
      if (f === 'text') {
        var ff = getP(k, 'ff'), fw = getP(k, 'fw'), al = getP(k, 'al') || 'left';
        h += btn('font', '<span style="font-family:' + esc(D.css(ff)).replace(/&quot;/g, "'") + '">' + esc(ff) + '</span> &#9662;', 'Font', 'dsel-wide') +
          btn('weight', esc(wtName(fw)) + ' &#9662;', 'Weight') + fsizeHTML(getP(k, 'fs')) +
          btn('color', '<span class="dsel-A" style="border-color:' + hex(getP(k, 'tc')) + '">A</span>', 'Text colour', 'dsel-ic') +
          btn('bold', '<b>B</b>', 'Bold', 'dsel-ic', fw >= 600) + btn('italic', '<i style="font-style:italic;font-family:Georgia,serif">I</i>', 'Italic', 'dsel-ic', !!getP(k, 'it')) +
          btn('under', '<u>U</u>', 'Underline', 'dsel-ic', !!getP(k, 'ul')) + btn('strike', '<s>S</s>', 'Strikethrough', 'dsel-ic', !!getP(k, 'sk')) +
          btn('caps', 'aA', 'Capitals on / off', 'dsel-ic', !!getP(k, 'caps')) + btn('talign', alignIcon(al), 'Text alignment', 'dsel-ic', pmode === 'talign') +
          btn('spacing', '&#8597;&#8201;T', 'Line and letter spacing', 'dsel-ic', pmode === 'spacing') + btn('opacity', IC.op, 'Transparency', 'dsel-ic', pmode === 'opacity');
        if (supports(k, 'fill')) h += btn('fill', sw(paintSw(getP(k, 'fill'))) + ' Fill', 'Fill behind the text: one colour or a gradient', '', pmode === 'fill');
        if (supports(k, 'ln')) h += btn('line', sw(paintSw(getP(k, 'ln'))) + ' Outline', 'Outline around the text: colour or gradient, and width', '', pmode === 'line');
        if (isL(k) && cornerOK(k)) h += '<span class="dsel-lbl">Corners</span>' + stepper('r', Math.round(D.num(o.r, 0)), 'Corner roundness of the text box', 0, 400, 2);
      } else if (f === 'shape') {
        var ln = getP(k, 'ln');
        h += btn('fill', sw(paintSw(getP(k, 'fill'))) + ' Fill', 'Fill: one colour or a gradient', '', pmode === 'fill') + btn('line', sw(paintSw(ln)) + ' Outline', 'Outline: colour or gradient, and width', '', pmode === 'line') +
          (ln && ln.m && ln.m !== 'none' ? stepper('lw', r1(D.num(ln.w, 2)), 'Outline width', 0.5, 60, 0.5) : '') +
          (cornerOK(k) ? '<span class="dsel-lbl">Corners</span>' + stepper('r', Math.round(D.num(o.r, 0)), 'Corner roundness (or drag the round dot on the shape)', 0, 400, 2) : '') +
          (sidesOK(k) ? '<span class="dsel-lbl">' + (o.shape === 'star5' ? 'Points' : 'Sides') + '</span>' + stepper('pts', D.ptsOf(o.shape, o), 'Number of points (or drag the diamond on the shape: down for more, up for fewer)', 3, 12, 1) : '') +
          btn('opacity', IC.op + ' Transparency', 'Transparency', '', pmode === 'opacity');
      } else if (f === 'line') {
        var l2 = getP(k, 'ln') || {};
        h += btn('color', sw(paintSw(l2.m ? l2 : { m: 'solid', c: '@p' })) + ' Colour', 'Line colour', '', pmode === 'color') + '<span class="dsel-lbl">Thickness</span>' + stepper('lw', r1(D.num(l2.w, 3)), 'Line thickness', 0.5, 60, 0.5) +
          btn('opacity', IC.op + ' Transparency', 'Transparency', '', pmode === 'opacity');
      } else if (f === 'icon') {
        h += btn('color', sw(hex(getP(k, 'tc'))) + ' Colour', 'Icon colour', '', pmode === 'color') + btn('icon', 'Change icon', 'Pick another icon', '', pmode === 'icon') + btn('opacity', IC.op + ' Transparency', 'Transparency', '', pmode === 'opacity');
      } else if (f === 'image') {
        h += btn('img', 'Change image', 'Pick or upload another picture') + btn('crop', IC.crop + ' Crop', 'Cut away the edges of the picture') + btn('corners', 'Round corners', 'Drag the slider to make the corners rounder or sharper', '', pmode === 'corners') + btn('opacity', IC.op + ' Transparency', 'Transparency', '', pmode === 'opacity');
        if (supports(k, 'ln')) h += btn('line', sw(paintSw(getP(k, 'ln'))) + ' Outline', 'Outline colour and width', '', pmode === 'line');
      } else if (f === 'chart') {
        h += btn('chart', 'Edit chart', 'Change the chart type and numbers');
        if (supports(k, 'fill')) h += btn('fill', sw(paintSw(getP(k, 'fill'))) + ' Background', 'Background behind the chart', '', pmode === 'fill');
      } else if (f === 'pb') h += '<span class="dsel-lbl">The text after this starts on a new page.</span>';
      if (supports(k, 'sh')) h += btn('shadow', IC.shd + ' Shadow', 'Shadow: colour, opacity, blur, distance, angle, size', '', pmode === 'shadow');
      if (sel.length <= 1 && !isC(k)) h += btn('align', svg(AIC.l) + ' Align &#9662;', isB(k) ? 'Place this text on the left, in the middle or on the right' : 'Align and distribute on the page', '', pmode === 'align');
      if (sel.length === 1 && isB(k)) {
        var bm = env.blocks, conv = bm.convertList.indexOf(o.t) >= 0, txt = f === 'text' && !TABLEISH.test(o.t);
        h += '<span class="dsel-sep"></span>' + (conv ? btn('btype', 'Change to &#9662;', 'Make this a heading, a paragraph, a list, a quote or a callout') : '') +
          (txt || TABLEISH.test(o.t) ? btn('ai', IC.spark + ' AI &#9662;', 'AI writing: improve, shorten, expand, translate...') : '') +
          (TABLEISH.test(o.t) ? btn('bopts', 'Rows &amp; items', 'Add or remove rows, columns and items') : '') +
          btn('addbelow', '+ Add below &#9662;', 'Add a new part to the document under this one');
      }
      if (sel.length === 1 && isL(k)) h += '<span class="dsel-sep"></span>' + btn('behind', o.behind ? 'In front of text' : 'Behind text', o.behind ? 'Put it in front of the document text' : 'Put it behind the document text, like a background');
      return h;
    }
    function rightHTML() {
      var fo = host.classList.contains('dd-focus');
      if (fo) return btn('edscreen', 'Exit', 'Show the pages and design panels again', 'dsel-edscr', true) +
        btn('theme', '<i class="dsel-thdot"></i> Theme', 'Colours and fonts for the whole document', 'dsel-theme') + '<span class="dsel-dsep"></span>' +
        btn('pos', 'Positions', 'Order, alignment, size, place, rotation', '', pmode === 'pos') + btn('layers', 'Layers', 'Every element of this page, the text included', '', pmode === 'layers') +
        btn('add', '+ Add element', 'Add a text box, a shape, a line, a picture, a chart, an icon or a new part of the text', '', pmode === 'add') +
        btn('icons', '&#9734; Icons', 'Search free icons and add one', '', pmode === 'icons') +
        btn('edtext', 'Edit Text', 'Edit all the text of the document in one list') + btn('logo', 'Logo', 'Company logo on every page') +
        btn('master', 'Document Style', 'Design, colours and fonts for the whole document') + btn('addsec', 'Add Section', 'Add a new section with a heading') +
        '<span class="dsel-urow">' + btn('undo', '&#8630;', 'Undo (Ctrl+Z)', 'dsel-ic') + btn('redo', '&#8631;', 'Redo (Ctrl+Y)', 'dsel-ic') + '</span>' +
        btn('reset', '&#8634; Reset', 'Undo everything changed since this document was opened', 'dsel-reset', pmode === 'reset');
      return btn('edscreen', fo ? 'Exit' : 'Edit screen', fo ? 'Show the pages and design panels again' : 'Hide the pages and design panels - more room to edit', 'dsel-edscr', fo) + '<span class="dsel-dsep"></span>' +
        btn('pos', 'Positions', 'Order, alignment, size, place, rotation', '', pmode === 'pos') + btn('layers', 'Layers', 'Every element of this page, the text included', '', pmode === 'layers') +
        btn('add', '+ Add element', 'Add a text box, a shape, a line, a picture, a chart, an icon or a new part of the text', '', pmode === 'add') +
        btn('icons', '&#9734; Icons', 'Search free icons and add one', '', pmode === 'icons') +
        btn('edtext', 'Edit Text', 'Edit all the text of the document in one list') + btn('logo', 'Logo', 'Company logo on every page') +
        btn('master', 'Document Style', 'Design, colours and fonts for the whole document') + btn('addsec', 'Add Section', 'Add a new section with a heading') +
        btn('undo', '&#8630;', 'Undo (Ctrl+Z)', 'dsel-ic') + btn('redo', '&#8631;', 'Redo (Ctrl+Y)', 'dsel-ic') +
        btn('reset', '&#8634; Reset', 'Undo everything changed since this document was opened', 'dsel-reset', pmode === 'reset');
    }
    var barKey = '';
    /* ---- locked windows: a panel that stays open in its own window while other tools are used (like AI Presentation) ---- */
    var pins = [], inPin = null, mainPanel = null;
    function withPin(pin, fn) {
      var sp = panel, sm = pmode; panel = pin.el; pmode = pin.m; inPin = pin;
      try { fn(); } finally { pin.m = pmode; panel = sp; pmode = sm; inPin = null; }
    }
    function pinBtnOn(pin) { var pb = pin.el.querySelector('[data-p="pin"]'); if (pb) { pb.classList.add('dsel-on'); pb.innerHTML = IC.lock; pb.title = 'Unlock and close this window'; } }
    function paintPins() {
      if (inPin) return;
      pins.slice().forEach(function (pin) { if (!pin.m) { closePin(pin); return; } withPin(pin, paintPanel); pinBtnOn(pin); });
    }
    function closePin(pin) { var i = pins.indexOf(pin); if (i >= 0) pins.splice(i, 1); pin.el.remove(); place(); }
    function pinCurrent() {
      if (!pmode || !panel) return;
      var ex = pinFor(pmode); if (ex) { var m0 = pmode; pmode = ''; showPin(ex, m0); paint(); return; }
      var r = panel.getBoundingClientRect(), w = el('div', 'dsel-pwin');
      w.style.cssText = 'display:block;left:' + Math.round(r.left) + 'px;top:' + Math.round(r.top) + 'px;width:' + Math.round(r.width) + 'px;height:' + Math.round(Math.min(r.height, window.innerHeight - r.top - 10)) + 'px';
      host.appendChild(w);
      var pin = { el: w, m: pmode }; pins.push(pin); wirePin(pin);
      pmode = ''; paint();
      env.toast('Window locked. It stays open until you close it.', 'ok', 2200);
    }
    function afterPin(pin) { if (!pin.m) closePin(pin); paint(); }
    function wirePin(pin) {
      var w = pin.el;
      w.addEventListener('click', function (ev) {
        var b = ev.target.closest('[data-p]');
        if (b && b.getAttribute('data-p') === 'pin') { closePin(pin); return; }
        withPin(pin, function () { onPanelClick(ev); }); afterPin(pin);
      });
      w.addEventListener('input', function (ev) { withPin(pin, function () { onPanelInput(ev); }); });
      w.addEventListener('change', function (ev) { withPin(pin, function () { onPanelChange(ev); }); afterPin(pin); });
      w.addEventListener('keydown', function (ev) { ev.stopPropagation(); if (ev.key === 'Enter' && ev.target.getAttribute('data-pc') === 'isearch') { ev.preventDefault(); withPin(pin, iconSearch); } });
      w.addEventListener('pointerdown', function (ev) {
        var hd = ev.target.closest('.dsel-ph');
        if (hd && !ev.target.closest('button,input,select') && ev.button === 0) {
          ev.preventDefault(); var r0 = w.getBoundingClientRect(), ox = ev.clientX - r0.left, oy = ev.clientY - r0.top;
          document.documentElement.classList.add('dsel-dragging');
          var mv = function (x) { w.style.left = Math.max(4, Math.min(window.innerWidth - 60, x.clientX - ox)) + 'px'; w.style.top = Math.max(4, Math.min(window.innerHeight - 40, x.clientY - oy)) + 'px'; };
          var up = function () { window.removeEventListener('pointermove', mv, true); window.removeEventListener('pointerup', up, true); document.documentElement.classList.remove('dsel-dragging'); };
          window.addEventListener('pointermove', mv, true); window.addEventListener('pointerup', up, true);
          return;
        }
        if (ev.target.closest('.dsel-gs')) { var sp = panel, sm = pmode; panel = w; pmode = pin.m; try { gsDown(ev); } finally { panel = sp; pmode = sm; } }
      });
    }
    function paint() {
      ensureBar();
      var key = sel.join(',') + '|' + pmode + '|' + (editing || ''), ae = document.activeElement;
      if (!(key === barKey && ae && bar.contains(ae) && ae.tagName === 'INPUT')) bar.querySelector('.dsel-left').innerHTML = leftHTML();
      barKey = key;
      dock.querySelector('.dsel-right').innerHTML = rightHTML();
      paintPanel(); paintPins(); sync(); place();
    }
    function ensureBar() {
      if (bar && bar.parentNode === host) return;
      bar = el('div'); bar.id = 'dsel-bar'; bar.innerHTML = '<div class="dsel-left"></div>'; host.appendChild(bar);
      dock = el('div'); dock.id = 'dsel-dock'; dock.innerHTML = '<div class="dsel-right"></div>'; host.appendChild(dock);
      panel = el('div'); panel.id = 'dsel-panel'; panel.style.display = 'none'; host.appendChild(panel); mainPanel = panel;
      keysEl = el('div'); keysEl.id = 'dsel-keys'; keysEl.innerHTML = '<button type="button" id="dsel-kbtn" title="Keyboard shortcuts (?)">' + IC.keys + '<span>Shortcuts</span></button>'; host.appendChild(keysEl);
      keysEl.addEventListener('click', showHelp);
      [bar, dock].forEach(function (b) {
        b.addEventListener('click', onBarClick);
        b.addEventListener('mousedown', function (e) { if (e.target.tagName !== 'INPUT') e.preventDefault(); });
      });
      bar.addEventListener('change', onInput);
      bar.addEventListener('keydown', function (e) { if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.target.blur(); });
      bar.addEventListener('input', function (ev) { var t = ev.target; if (t.getAttribute('data-r') !== 'size') return; begin(); sel.forEach(function (k) { if (supports(k, 'fs')) setP(k, 'fs', +t.value); }); liveRedraw(); var n0 = bar.querySelector('input[data-i="size"]'); if (n0) n0.value = t.value; });
      bar.addEventListener('change', function (ev) { if (ev.target.getAttribute('data-r') === 'size') done(); });
      panel.addEventListener('click', onPanelClick);
      panel.addEventListener('input', onPanelInput);
      panel.addEventListener('change', onPanelChange);
      panel.addEventListener('pointerdown', gsDown);
      panel.addEventListener('pointerdown', panelDragDown);
      panel.addEventListener('keydown', function (e) { if (e.key === 'Enter' && e.target.getAttribute('data-pc') === 'isearch') { e.preventDefault(); iconSearch(); } e.stopPropagation(); });
      panel.addEventListener('dblclick', function (ev) {
        if (ev.target.closest('.dsel-ph') && !ev.target.closest('button')) { panPos = null; try { localStorage.removeItem('dsdoc_panel_pos'); } catch (er) { /* ignore */ } place(); return; }
        var g = ev.target.closest('[data-rn]'); if (g) { ev.preventDefault(); renameRow(g); }
      });
    }
    var liveT = 0;
    function liveRedraw() { if (liveT) return; liveT = requestAnimationFrame(function () { liveT = 0; redraw(); }); }
    function place() {
      if (!bar) return;
      var r = canvas.getBoundingClientRect(); if (!r.width) return;
      bar.style.display = 'flex'; dock.style.display = 'flex';
      bar.style.maxWidth = Math.round(r.width - 28) + 'px'; bar.style.top = Math.round(r.top + 8) + 'px';
      var foc = host.classList.contains('dd-focus');
      dock.classList.toggle('dsel-dockv', foc);
      bar.style.left = Math.round(foc && !sel.length ? r.right - bar.offsetWidth - 16 : r.left + Math.max(14, (r.width - bar.offsetWidth) / 2)) + 'px';
      var bh = bar.offsetHeight || 52, pad = bh + 30;
      if (canvas.__padT !== pad) { canvas.style.paddingTop = pad + 'px'; canvas.__padT = pad; }
      keysEl.style.left = Math.round(r.left + 14) + 'px'; keysEl.style.top = Math.round(r.bottom - 44) + 'px';
      var dw, dh, padB;
      if (foc) {          /* full-screen editing: the tools stand in a column on the left, like AI Presentation */
        dock.style.maxWidth = ''; dock.style.left = Math.round(r.left + 6) + 'px'; dock.style.top = Math.round(r.top + 20) + 'px';
        dock.style.maxHeight = Math.max(200, Math.round(r.height - 80)) + 'px';
        padB = 40; dh = 0;
      } else {
        dock.style.maxHeight = '';
        dock.style.maxWidth = Math.round(r.width - 20) + 'px';
        dw = dock.offsetWidth; dh = dock.offsetHeight || 50;
        var dl = Math.round(r.left + Math.max(10, (r.width - dw) / 2)), kb = keysEl.getBoundingClientRect(), lift = 0;
        if (dl < kb.right + 8) { if (kb.right + 8 + dw <= r.right - 8) dl = Math.round(kb.right + 8); else lift = kb.height + 10; }   /* beside the Shortcuts button, near the bottom strip */
        dock.style.left = dl + 'px'; dock.style.top = Math.round(r.bottom - 8 - dh - lift) + 'px';
        padB = dh + lift + 22;
      } if (canvas.__padB !== padB) { canvas.style.paddingBottom = padB + 'px'; canvas.__padB = padB; }
      if (panel !== mainPanel) return;
      if (pmode && pinFor(pmode)) { var pf = pinFor(pmode); pmode = ''; showPin(pf); paintPanel(); }
      panel.style.display = pmode ? 'block' : 'none';
      var poff = pins.length * 28;
      if (pmode) {
        if (panPos) {
          var pw0 = panel.offsetWidth || 260, pl = Math.max(4, Math.min(window.innerWidth - pw0 - 4, panPos.l)), pt0 = Math.max(4, Math.min(window.innerHeight - 80, panPos.t));
          panel.style.left = Math.max(4, pl - poff) + 'px'; panel.style.right = 'auto'; panel.style.top = Math.min(window.innerHeight - 80, pt0 + poff) + 'px'; panel.style.maxHeight = Math.max(160, window.innerHeight - pt0 - 12) + 'px';
        } else {
          panel.style.left = ''; panel.style.top = Math.round(r.top + bh + 18 + poff) + 'px'; panel.style.right = Math.round(window.innerWidth - r.right + 16 + poff) + 'px';
          panel.style.maxHeight = Math.max(160, Math.round(r.height - bh - 34 - dh - 20)) + 'px';
        }
      }
    }
    function panelDragDown(ev) {
      var hd = ev.target.closest ? ev.target.closest('.dsel-ph') : null; if (!hd || ev.target.closest('button,input,select') || ev.button > 0) return;
      ev.preventDefault();
      var r0 = panel.getBoundingClientRect(), ox = ev.clientX - r0.left, oy = ev.clientY - r0.top;
      document.documentElement.classList.add('dsel-dragging');
      function mv(x) { var l = Math.max(4, Math.min(window.innerWidth - r0.width - 4, x.clientX - ox)), t = Math.max(4, Math.min(window.innerHeight - 60, x.clientY - oy)); panPos = { l: Math.round(l), t: Math.round(t) }; panel.style.left = panPos.l + 'px'; panel.style.right = 'auto'; panel.style.top = panPos.t + 'px'; }
      function up() { window.removeEventListener('pointermove', mv, true); window.removeEventListener('pointerup', up, true); document.documentElement.classList.remove('dsel-dragging'); try { if (panPos) localStorage.setItem('dsdoc_panel_pos', JSON.stringify(panPos)); } catch (er) { /* ignore */ } }
      window.addEventListener('pointermove', mv, true); window.addEventListener('pointerup', up, true);
    }
    function famP(m) { return m === 'layers' ? 'pos' : m; }
    function pinFor(m) { for (var i = 0; i < pins.length; i++) if (famP(pins[i].m) === famP(m)) return pins[i]; return null; }
    function showPin(pin, m) {           /* the locked window is the one: bring it to the front and make it blink */
      if (m && famP(m) === 'pos') pin.m = m;
      host.appendChild(pin.el);
      var r = pin.el.getBoundingClientRect();
      if (r.right < 40 || r.left > window.innerWidth - 40 || r.top > window.innerHeight - 40 || r.bottom < 20) { pin.el.style.left = '80px'; pin.el.style.top = '80px'; }
      pin.el.classList.remove('dsel-pflash'); void pin.el.offsetWidth; pin.el.classList.add('dsel-pflash');
    }
    function openPanel(m) {
      var pin = pinFor(m);
      if (pin) { if (famP(pmode) === famP(m)) pmode = ''; showPin(pin, m); paint(); return; }
      pmode = pmode === m ? '' : m; paint();
    }
    function toggleP(p) { var k = sel[0]; if (!k) return; var on = !getP(k, p); setAll(p, on ? 1 : null); paint(); }
    function onBarClick(ev) {
      var b = ev.target.closest('[data-a]'); if (!b) return;
      var a = b.getAttribute('data-a'), k = sel[0], o = k && obj(k);
      switch (a) {
        case 'edscreen': host.classList.toggle('dd-focus'); setTimeout(function () { env.refit(); paint(); }, 30); return;
        case 'logo': logoDialog(); return;
        case 'icons': if (window.__dsDocIcons) { if (window.__dsDocIcons.open() && window.__dsDocIcons.locked()) window.__dsDocIcons.flash(); else if (window.__dsDocIcons.open()) window.__dsDocIcons.hide(); else window.__dsDocIcons.show(b, function (n) { addIconByName(n); }); } else openPanel(a); return;
        case 'pos': case 'layers': case 'add': case 'reset': openPanel(a); return;
        case 'edtext': env.editText(); return;
        case 'theme': openPanel('theme'); return;
        case 'master': if (host.classList.contains('dd-focus')) { host.classList.remove('dd-focus'); env.refit(); } env.styleTab(); return;
        case 'addsec': env.addSection(); return;
        case 'undo': env.undo(); return;
        case 'redo': env.redo(); return;
        case 'fsz': var fz = b.closest('.dsel-fsz'); if (fz) fz.classList.toggle('dsel-open'); return;
        case 'group': doGroup(); return;
        case 'ungroup': doUngroup(); return;
        case 'unlock': setLock(sel.slice(), false); return;
      }
      if (!k) return;
      switch (a) {
        case 'font': case 'weight': case 'color': case 'fill': case 'line': case 'shadow': case 'talign': case 'spacing': case 'opacity': case 'corners': case 'align': openPanel(a); return;
        case 'icon': if (window.__dsDocIcons) { var rk = k; window.__dsDocIcons.show(b, function (n) { addIconByName(n, rk); }, true); } else openPanel(a); return;
        case 'bold': setAll('fw', getP(k, 'fw') >= 600 ? 400 : 700); paint(); return;
        case 'italic': toggleP('it'); return;
        case 'under': toggleP('ul'); return;
        case 'strike': toggleP('sk'); return;
        case 'caps': toggleP('caps'); return;
        case 'img': changePic(k); return;
        case 'crop': cropPic(k); return;
        case 'chart': chartOf(k); return;
        case 'behind': setAll('behind', o.behind ? null : 1); paint(); return;
        case 'bopts': env.blockTab(); return;
        case 'st-': case 'st+':
          var key = b.getAttribute('data-k'), inp = b.parentElement.querySelector('input'), st = parseFloat(b.getAttribute('data-s')) || 1;
          var v = Math.max(parseFloat(inp.min), Math.min(parseFloat(inp.max), (parseFloat(inp.value) || 0) + (a === 'st+' ? st : -st)));
          applyNum(key, v); return;
        case 'btype': {
          var r = b.getBoundingClientRect(), bm = env.blocks;
          openMenu(r.left, r.bottom + 6, bm.convertList.map(function (t) { return { l: bm.typeLabel[t] || t, on: o.t === t, f: function () { begin(); var nb = bm.convert(o, t); blocks()[bIdx(o.id)] = nb; env.rerender(); done(); sel = ['B:' + nb.id]; selChanged(); } }; }));
          return;
        }
        case 'ai': {
          var r2b = b.getBoundingClientRect(), bm2 = env.blocks;
          openMenu(r2b.left, r2b.bottom + 6, bm2.aiActs.map(function (x) {
            return { l: x[1], f: function () {
              if (x[0] === 'tone') { openMenu(r2b.left, r2b.bottom + 6, bm2.tones.map(function (tn) { return { l: tn, f: function () { bm2.aiBlock(o, 'tone', { tone: tn }); } }; })); return; }
              if (x[0] === 'translate') { openMenu(r2b.left, r2b.bottom + 6, bm2.langs.map(function (lg) { return { l: lg, f: function () { bm2.aiBlock(o, 'translate', { language: lg }); } }; })); return; }
              bm2.aiBlock(o, x[0]);
            } };
          }));
          return;
        }
        case 'addbelow': {
          var r3 = b.getBoundingClientRect();
          openMenu(r3.left, r3.bottom + 6, env.blocks.addable().map(function (x) { return { l: x[1], f: function () { newBlock(x[0]); } }; }));
          return;
        }
      }
    }
    function applyNum(key, v) {
      if (!isFinite(v)) return;
      if (key === 'size') { setAll('fs', v); paint(); return; }
      if (key === 'lw') { begin(); sel.forEach(function (k) { if (!supports(k, 'ln')) return; var p = D.clone(getP(k, 'ln') || { m: 'solid', c: '@p' }); if (!p.m || p.m === 'none') p.m = 'solid'; p.w = v; setP(k, 'ln', p); }); redraw(); done(); paint(); return; }
      if (key === 'r') { setAll('r', v); paint(); return; }
      if (key === 'pts') { begin(); sel.forEach(function (k) { if (!sidesOK(k)) return; var L = obj(k); if (shapeOfL(L) === 'star5') L.pts = Math.round(v); else L.sides = Math.round(v); }); redraw(); done(); paint(); return; }
    }
    function onInput(ev) {
      var t = ev.target, key = t.getAttribute('data-i'); if (!key) return;
      var v = parseFloat(t.value); if (!isFinite(v)) return;
      if (t.min !== '') v = Math.max(parseFloat(t.min), v); if (t.max !== '') v = Math.min(parseFloat(t.max), v);
      applyNum(key, v);
    }

    /* ================= the settings panel ================= */
    function themePanel() {
      var own = D.look(Object.assign({}, doc, { theme: '' })), cur = doc.theme || '';
      var list = [{ id: '', name: 'Design colours', t: own }].concat(D.THEMES.filter(function (x) { return !x.hide; }).map(function (x) { return { id: x.id, name: x.name, t: x }; }));
      return head('Theme') + '<div class="dsel-pb"><p class="dsel-note" style="margin-top:0">Colours and fonts for the whole document.</p><div class="dsel-thg">' + list.map(function (x) {
        return '<button type="button" data-p="theme" data-n="' + esc(x.id) + '" class="' + (cur === x.id ? 'dsel-on' : '') + '" title="' + esc(x.name) + '"><span class="dsel-ths"><i style="background:' + x.t.p + '"></i><i style="background:' + x.t.s + '"></i><i style="background:' + x.t.a + '"></i><i style="background:' + (x.t.tint || '#fff') + '"></i></span><span class="dsel-thn">' + esc(x.name) + '</span></button>';
      }).join('') + '</div></div>';
    }
    function head(t) { return '<div class="dsel-ph"><b>' + esc(t) + '</b><span class="dsel-phb"><button type="button" data-p="pin" class="dsel-pinb" title="Lock: keep this window open while you use other tools">' + IC.unlock + '</button><button type="button" data-p="close" title="Close">&times;</button></span></div>'; }
    function slider(lab, key, min, max, step, val, unit) { return '<div class="dsel-sl"><span>' + lab + '</span><input type="range" data-ps="' + key + '" min="' + min + '" max="' + max + '" step="' + step + '" value="' + val + '"><em data-pv="' + key + '">' + val + (unit || '') + '</em></div>'; }
    function designRow(curC) { var t = theme(); return '<div class="dsel-sws">' + TOKENS.map(function (x) { return '<button type="button" class="dsel-swb' + (curC === x[0] ? ' dsel-on' : '') + '" data-p="col" data-c="' + x[0] + '" style="background:' + D.col(x[0], t) + '" title="' + x[1] + '"></button>'; }).join('') + '</div>'; }
    function baseRow(curC) { return '<div class="dsel-sws">' + BASE.map(function (c) { return '<button type="button" class="dsel-swb' + (String(curC).toUpperCase() === c ? ' dsel-on' : '') + '" data-p="col" data-c="' + c + '" style="background:' + c + '" title="' + c + '"></button>'; }).join('') + '</div>'; }
    function pKey(target, k) { return target === 'fill' ? 'fill' : (target === 'line' || (target === 'color' && famOf(k) === 'line')) ? 'ln' : null; }
    function gPresets(t, c1) {
      return [[[0, c1 || '@p', 1], [100, '@a', 1]], [[0, '@p', 1], [100, '@s', 1]], [[0, '@a', 1], [100, '@w', 1]], [[0, '@p', 1], [100, '@p', 0]], [[0, '#0F172A', 1], [100, '#334155', 1]], [[0, '#F97316', 1], [100, '#EC4899', 1]],
        [[0, '#06B6D4', 1], [100, '#3B82F6', 1]], [[0, '#22C55E', 1], [100, '#14B8A6', 1]], [[0, '#8B5CF6', 1], [100, '#EC4899', 1]], [[0, '#F59E0B', 1], [100, '#EF4444', 1]], [[0, '#FFFFFF', 1], [100, '@tint', 1]], [[0, '#000000', 0.6], [100, '#000000', 0]]];
    }
    function gCss(s, t) { return 'linear-gradient(90deg,' + s.slice().sort(function (a, b) { return a[0] - b[0]; }).map(function (x) { var c = D.rgb(D.col(x[1], t)); return 'rgba(' + c.join(',') + ',' + D.num(x[2], 1, 0, 1) + ') ' + x[0] + '%'; }).join(',') + ')'; }
    function colorPanel(target, k) {
      var t = theme(), f = famOf(k), pk = pKey(target, k), P = pk ? (getP(k, pk) || { m: 'none' }) : null;
      if (target === 'color' && f === 'line' && (!P.m || P.m === 'none')) P = { m: 'solid', c: '@p', w: 3 };
      var mode = target === 'color' && f !== 'line' ? 'solid' : (P.m || 'none'), canNone = target !== 'color', canGrad = !!pk, canPic = pk === 'fill';
      var cur = pk ? (P.c || '@p') : getP(k, 'tc');
      var title = ({ fill: f === 'chart' ? 'Background' : 'Fill', line: 'Outline' })[target] || (f === 'text' ? 'Text colour' : f === 'icon' ? 'Icon colour' : 'Colour');
      var gk = k + '|' + target; if (gKey !== gk) { gKey = gk; gSel = 0; }
      function seg(v, l) { return '<button type="button" data-p="fmode" data-n="' + v + '" class="' + (mode === v ? 'dsel-on' : '') + '">' + l + '</button>'; }
      var h = head(title) + '<div class="dsel-pb">';
      if (canNone || canGrad) h += '<div class="dsel-seg">' + (canNone ? seg('none', target === 'fill' ? 'No fill' : 'None') : '') + seg('solid', 'Solid') + (canGrad ? seg('grad', 'Gradient') : '') + (canPic ? seg('pic', 'Picture') : '') + '</div>';
      if (mode === 'pic') h += picFillHTML(P);
      else if (mode === 'grad') {
        var G = P.g || { t: 'linear', a: 0, s: [[0, P.c || '@p', 1], [100, '@a', 1]] }, S = G.s; if (gSel >= S.length) gSel = S.length - 1;
        var s = S[gSel], rad = G.t === 'radial';
        h += '<div class="dsel-sub">Preset gradients</div><div class="dsel-gpre">' + gPresets(t, S[0][1]).map(function (p, i) { return '<button type="button" data-p="gpre" data-n="' + i + '" style="background:' + gCss(p, t) + '"></button>'; }).join('') + '</div>';
        h += '<div class="dsel-row"><span class="dsel-rl">Type</span><select data-pc="gtype"><option value="linear"' + (rad ? '' : ' selected') + '>Linear</option><option value="radial"' + (rad ? ' selected' : '') + '>Radial</option></select></div>';
        if (!rad) h += '<div class="dsel-row"><span class="dsel-rl">Direction</span><span class="dsel-dirs">' + [[0, '&rarr;'], [90, '&darr;'], [45, '&#8600;'], [315, '&#8599;'], [180, '&larr;'], [270, '&uarr;']].map(function (d) { return '<button type="button" data-p="gdir" data-n="' + d[0] + '" class="' + (((+G.a || 0) % 360 + 360) % 360 === d[0] ? 'dsel-on' : '') + '">' + d[1] + '</button>'; }).join('') + '</span></div>' + slider('Angle', 'gang', 0, 360, 1, Math.round(((+G.a || 0) % 360 + 360) % 360), '°');
        h += '<div class="dsel-sub">Gradient stops</div><div class="dsel-row dsel-grow"><div class="dsel-gwrap"><div class="dsel-gbar" data-p="gbar" style="background:' + gCss(S, t) + '">' +
          S.map(function (x, i) { return '<span class="dsel-gs' + (i === gSel ? ' dsel-on' : '') + '" data-p="gstop" data-n="' + i + '" style="left:' + x[0] + '%;background:' + D.col(x[1], t) + '"></span>'; }).join('') +
          '</div></div><button type="button" class="dsel-gbt" data-p="gadd" title="Add a colour stop">+</button><button type="button" class="dsel-gbt" data-p="gdel" title="Remove the selected stop"' + (S.length <= 2 ? ' disabled' : '') + '>&minus;</button></div>' +
          '<div class="dsel-row"><span class="dsel-rl">Colour</span><input type="color" data-pc="gsc" value="' + D.col(s[1], t) + '"><span class="dsel-rl" style="margin-left:6px">Position</span><input type="number" data-pc="gsp" min="0" max="100" step="1" value="' + Math.round(s[0]) + '"><span>%</span></div>' +
          slider('Transparency', 'gst', 0, 100, 1, Math.round((1 - D.num(s[2], 1, 0, 1)) * 100), '%') +
          '<div class="dsel-sub">Colours in this design</div>' + designRow(s[1]) + '<div class="dsel-sub">Basic colours</div>' + baseRow(s[1]);
      } else if (mode === 'solid') {
        var hx = D.col(cur, t);
        h += '<div class="dsel-row"><input type="color" data-pc="pick" value="' + hx + '"><input type="text" data-pc="hex" value="' + hx + '" maxlength="7" spellcheck="false"></div>';
        if (pk) h += slider('Transparency', 'ptr', 0, 100, 1, Math.round((1 - D.num(P.o, 1, 0, 1)) * 100), '%');
        else h += slider('Transparency', 'op', 0, 100, 1, Math.round((1 - D.num(getP(k, 'op'), 1, 0, 1)) * 100), '%');
        h += '<div class="dsel-sub">Colours in this design</div>' + designRow(cur) + '<div class="dsel-sub">Basic colours</div>' + baseRow(cur);
      }
      if (pk === 'ln' && mode !== 'none') h += '<div class="dsel-sub">Line</div>' + slider('Width', 'lw', 0.5, 30, 0.5, r1(D.num(P.w, 2))) +
        '<div class="dsel-row"><span class="dsel-rl">Dash type</span><select data-pc="dash"><option value="solid"' + (P.d === 'dash' || P.d === 'dot' ? '' : ' selected') + '>Solid</option><option value="dash"' + (P.d === 'dash' ? ' selected' : '') + '>Dashed</option><option value="dot"' + (P.d === 'dot' ? ' selected' : '') + '>Dotted</option></select></div>';
      return h + '</div>';
    }
    /* Fill > Picture: the same picture window as pictures (generate with AI, browse the store, upload) */
    function picFillHTML(P) {
      var has = D.picSrcOK(P.src), many = sel.filter(function (x) { return isL(x) && supports(x, 'fill'); }).length > 1;
      var h = '<div class="dsel-sub">Picture</div>';
      if (has) h += '<div style="height:96px;border-radius:8px;border:1px solid #CBD5E1;background:#F1F5F9 url(' + String(P.src).replace(/["\'()\\\s<>]/g, '') + ') center/cover no-repeat;margin-bottom:8px"></div>';
      h += '<div class="dsel-grid2"><button type="button" class="dsel-pbt" data-p="pfins">' + (has ? 'Change picture...' : 'Insert picture...') + '</button>' + (has ? '<button type="button" class="dsel-pbt dsel-danger" data-p="pfdel">Remove picture</button>' : '') + '</div>';
      if (many) h += '<p class="dsel-note" style="margin:8px 0 2px">' + (oneGroup() ? 'This group shows one picture across all its shapes. Ungroup removes the picture.' : 'Each selected shape gets its own copy of the picture. Group them first to show one picture across all of them.') + '</p>';
      if (!has) return h + '<p class="dsel-note">Insert opens the picture window: generate with AI, browse the store or upload from your device.</p>';
      h += slider('Transparency', 'pftr', 0, 100, 1, Math.round((1 - D.num(P.o, 1, 0, 1)) * 100), '%');
      h += '<div class="dsel-sub">Fit</div><div class="dsel-seg"><button type="button" data-p="pffit" data-n="fill" class="' + (P.st ? '' : 'dsel-on') + '">Fill shape</button><button type="button" data-p="pffit" data-n="stretch" class="' + (P.st ? 'dsel-on' : '') + '">Stretch</button></div>';
      if (!P.st) h += slider('Zoom', 'pfz', 100, 400, 5, Math.round(D.num(P.z, 1, 1, 4) * 100), '%') + slider('Left \u2194 right', 'pfx', 0, 100, 1, Math.round(D.num(P.px, 50, 0, 100)), '%') + slider('Top \u2195 bottom', 'pfy', 0, 100, 1, Math.round(D.num(P.py, 50, 0, 100)), '%');
      return h;
    }
    /* the box around the selected shapes, page by page: one picture is spread over it */
    function picGroupBoxes() {
      var by = {};
      sel.forEach(function (x) { if (!isL(x) || !supports(x, 'fill')) return; var L = obj(x), pg = String(L.pg), b = by[pg]; var x0 = D.num(L.x, 0), y0 = D.num(L.y, 0), x1 = x0 + D.num(L.w, 1), y1 = y0 + D.num(L.h, 1);
        by[pg] = b ? { x: Math.min(b.x, x0), y: Math.min(b.y, y0), r: Math.max(b.r, x1), b: Math.max(b.b, y1), n: b.n + 1 } : { x: x0, y: y0, r: x1, b: y1, n: 1 }; });
      var out = {}; Object.keys(by).forEach(function (pg) { var b = by[pg]; if (b.n > 1) out[pg] = { x: Math.round(b.x), y: Math.round(b.y), w: Math.max(1, Math.round(b.r - b.x)), h: Math.max(1, Math.round(b.b - b.y)) }; });
      return out;
    }
    function setPicGroup(on) {
      var gbs = on ? picGroupBoxes() : {};
      sel.forEach(function (x) { if (!isL(x) || !supports(x, 'fill')) return; var q = getP(x, 'fill'); if (!q || q.m !== 'pic') return; q = D.clone(q); var L = obj(x), g = gbs[String(L.pg)]; if (g) q.gb = { x: Math.round(g.x - D.num(L.x, 0)), y: Math.round(g.y - D.num(L.y, 0)), w: g.w, h: g.h }; else delete q.gb; setP(x, 'fill', q); });   /* kept from each shape's corner: the picture moves with the shapes */
    }
    /* a picked picture as a data link (at most 1600 px) with its size, so it also goes into the Word file */
    function picData(pic, cb) {
      var src = pic && (pic.data || pic.src || pic.url); if (!src) { cb(null); return; }
      var im = new Image(); if (!/^data:/.test(src)) im.crossOrigin = 'anonymous';
      im.onload = function () {
        var w = im.naturalWidth || 1, hh = im.naturalHeight || 1;
        if (/^data:image\/(png|jpe?g|webp);base64,/.test(src) && src.length < 700000) { cb({ src: src, iw: w, ih: hh }); return; }
        try {
          var k = Math.min(1, 1600 / Math.max(w, hh)), cv = document.createElement('canvas'); cv.width = Math.max(1, Math.round(w * k)); cv.height = Math.max(1, Math.round(hh * k));
          cv.getContext('2d').drawImage(im, 0, 0, cv.width, cv.height);
          var d = cv.toDataURL('image/jpeg', 0.86); if (d.length > 740000) d = cv.toDataURL('image/jpeg', 0.7);
          cb({ src: d, iw: cv.width, ih: cv.height });
        } catch (er) { cb(/^https:\/\//.test(src) ? { src: src, iw: w, ih: hh } : null); }
      };
      im.onerror = function () { cb(null); };
      im.src = src;
    }
    function insertPicFill(k) {
      var keys = sel.slice(), one = oneGroup();   /* one picture across the shapes only when they are a group */
      env.pickPicture(function (pic) {
        picData(pic, function (r) {
          if (!r) { env.toast('This picture could not be used.', 'error'); return; }
          sel = keys.filter(function (x) { return obj(x); }); if (!sel.length) return;
          begin();
          sel.forEach(function (x) { if (!supports(x, 'fill')) return; var q = D.clone(getP(x, 'fill') || {}); setP(x, 'fill', { m: 'pic', src: r.src, iw: r.iw, ih: r.ih, z: 1, px: 50, py: 50, o: q.m === 'pic' && q.o != null ? q.o : 1, st: 0 }); });
          setPicGroup(one);
          redraw(); done(); paint(); env.toast('Picture fill added.', '', 1500);
        });
      });
    }
    function shadowPanel(k) {
      var s = getP(k, 'sh'), v = s || SHDEF, bx = !(famOf(k) === 'text' && isL(k) && !D.hasBox(obj(k)));
      return head('Shadow') + '<div class="dsel-pb"><div class="dsel-row"><label class="dsel-chk"><input type="checkbox" data-pc="shon"' + (s ? ' checked' : '') + '> Shadow</label><input type="color" data-pc="shc" value="' + D.col(v.c || '#000000', theme()) + '"' + (s ? '' : ' disabled') + '></div>' +
        '<div' + (s ? '' : ' style="opacity:.45;pointer-events:none"') + '>' + slider('Opacity', 'sh.o', 0, 100, 1, Math.round(D.num(v.o, 0.35) * 100), '%') + slider('Blur', 'sh.b', 0, 80, 1, Math.round(D.num(v.b, 14))) +
        slider('Distance', 'sh.d', 0, 80, 1, Math.round(D.num(v.d, 8))) + slider('Angle', 'sh.a', 0, 360, 5, Math.round(D.num(v.a, 45)), '°') + (isB(k) ? slider('Size', 'sh.s', 0, 40, 1, Math.round(D.num(v.s, 0))) : '') + '</div>' +
        '<p class="dsel-note">Angle 0 throws the shadow to the right, 90 downwards.' + (bx ? '' : ' A text box shows its shadow once it has a fill or an outline.') + (isB(k) ? ' Word shows the text without the shadow.' : '') + '</p></div>';
    }
    function alignHTML() {
      var k = sel[0], lk = sel.filter(isL), choose = lk.length > 1, onlyB = sel.every(isB), sc = choose ? amode : 'page';
      function b(a, key, l, wide, dis) { return '<button type="button" class="dsel-pbt dsel-al' + (wide ? ' dsel-w2' : '') + '" data-p="' + a + '" title="' + (wide ? l : 'Align ' + l.toLowerCase()) + '"' + (dis ? ' disabled' : '') + '>' + svg(AIC[key]) + '<span>' + l + '</span></button>'; }
      var note = onlyB ? 'A document text is placed on the left, in the middle or on the right of the text column. Make it narrower first with its side handles.' : !choose ? 'One element is lined up with the page. Select several to line them up with each other.' : (sc === 'sel' ? 'The selected elements are lined up with each other.' : 'Each selected element is lined up with the page.');
      return '<div class="dsel-seg"><button type="button" data-p="amode" data-n="sel" class="' + (sc === 'sel' ? 'dsel-on' : '') + '"' + (choose ? '' : ' disabled') + '>Selected elements</button><button type="button" data-p="amode" data-n="page" class="' + (sc === 'page' ? 'dsel-on' : '') + '"' + (choose ? '' : ' disabled') + '>Page</button></div>' +
        '<p class="dsel-note" style="margin:6px 0 8px">' + note + '</p><div class="dsel-grid2">' + b('al-l', 'l', 'Left') + b('al-t', 't', 'Top', 0, onlyB) + b('al-c', 'c', 'Centre') + b('al-m', 'm', 'Middle', 0, onlyB) + b('al-r', 'r', 'Right') + b('al-b', 'b', 'Bottom', 0, onlyB) +
        b('ds-h', 'h', 'Distribute horizontally', 1, lk.length < 3) + b('ds-v', 'v', 'Distribute vertically', 1, lk.length < 3) + '</div>';
    }
    function posPanel(k, tab) {
      var h = head('Position') + '<div class="dsel-tabs"><button type="button" data-p="tab-pos" class="' + (tab === 'pos' ? 'dsel-on' : '') + '">Arrange</button><button type="button" data-p="tab-layers" class="' + (tab === 'layers' ? 'dsel-on' : '') + '">Layers</button></div><div class="dsel-pb">';
      if (tab === 'layers') return h + layersHTML() + '</div>';
      if (!k) return h + '<p class="dsel-note">Select an element on the page first.</p></div>';
      if (isC(k)) return h + '<p class="dsel-note">Drag this text to move it anywhere: it becomes a free text box you can also resize and turn. Click it again to type.</p><button type="button" class="dsel-pbt" data-p="cfree">Make it a free text box</button></div>';
      function b(a, l, dis) { return '<button type="button" class="dsel-pbt" data-p="' + a + '"' + (dis ? ' disabled' : '') + '>' + l + '</button>'; }
      var o = obj(k), lay = sel.some(isL);
      h += '<div class="dsel-grid2">' + (lay ? b('fwd', '&#8679; Forward') + b('bwd', '&#8681; Backward') + b('front', '&#10514; To front') + b('back', '&#10515; To back')
        : b('fwd', '&#8679; Move up') + b('bwd', '&#8681; Move down') + b('front', '&#10514; To the start') + b('back', '&#10515; To the end')) + '</div>';
      if (lay) h += '<label class="dsel-chk" style="margin:9px 0 2px"><input type="checkbox" data-pc="behind"' + (sel.filter(isL).every(function (x) { return obj(x).behind; }) ? ' checked' : '') + '> Behind the document text</label>';
      h += '<div class="dsel-sub">Align and distribute</div>' + alignHTML();
      if (sel.length === 1 && isL(k)) {
        var dis = o.k === 'text' ? '' : '';
        h += '<div class="dsel-sub">Advanced</div><div class="dsel-grid3">' +
          '<label>Width<input type="number" data-pi="pw" min="1" max="4000" step="1" value="' + r1(o.w) + '"></label>' +
          '<label>Height<input type="number" data-pi="ph" min="1" max="4000" step="1" value="' + r1(o.h) + '"' + dis + '></label>' +
          '<label>Ratio<button type="button" class="dsel-pbt' + (ratioLock ? ' dsel-on' : '') + '" data-p="ratio" title="Keep the proportions when the size changes">' + (ratioLock ? '&#128274;' : '&#128275;') + '</button></label>' +
          '<label>X<input type="number" data-pi="px" min="-4000" max="4000" step="1" value="' + r1(o.x) + '"></label>' +
          '<label>Y<input type="number" data-pi="py" min="-4000" max="4000" step="1" value="' + r1(o.y) + '"></label>' +
          '<label>Rotate<input type="number" data-pi="prot" min="-360" max="360" step="1" value="' + r1(o.rot || 0) + '"></label></div>' +
          '<p class="dsel-note">Sizes are in page points: the page is ' + size().w + ' wide and ' + size().h + ' high.</p>';
      } else if (sel.length === 1 && isB(k)) {
        var g = D.blockGeo(o);
        h += '<div class="dsel-sub">Advanced</div><div class="dsel-grid3">' +
          '<label>Width %<input type="number" data-pi="bw" min="15" max="100" step="1" value="' + Math.round(g.w * 100) + '"></label>' +
          '<label>From left %<input type="number" data-pi="box" min="0" max="85" step="1" value="' + Math.round(g.ox * 100) + '"></label>' +
          '<label>&nbsp;<button type="button" class="dsel-pbt" data-p="bfull" title="Full width again">Full</button></label>' +
          '<label>Space above<input type="number" data-pi="bmt" min="0" max="600" step="2" value="' + Math.round(g.mt) + '"></label>' +
          '<label>Space below<input type="number" data-pi="bmb" min="0" max="600" step="2" value="' + Math.round(g.mb) + '"></label></div>' +
          '<p class="dsel-note">The width is a part of the text column. The text flows on to the next page by itself; a Page break starts a new page.</p>';
      } else if (sel.length > 1) h += '<p class="dsel-note">' + sel.length + ' elements are selected.</p>';
      return h + '</div>';
    }
    /* ---- layers: every element of the page, the document's own text included ---- */
    function layerDot(k) {
      var o = obj(k);
      if (isC(k)) return '<i class="dsel-lg">T</i>';
      if (isB(k)) {
        if (o.t === 'image') return '<i class="dsel-lt" style="color:#334155">' + IC.pic + '</i>';
        var g = o.t === 'chart' ? '&#128202;' : /^(table|kv|inv|todo|cal|week)$/.test(o.t) ? '&#9638;' : /^(ul|ol|check|r_skills)$/.test(o.t) ? '&#8801;' : o.t === 'quote' ? '&#8220;' : o.t === 'pb' ? '&#8213;' : o.t === 'stats' ? '#' : (/^(h1|h2|h3|r_sec|s_title|title)$/.test(o.t) ? 'H' : 'T');
        return '<i class="dsel-lg">' + g + '</i>';
      }
      if (o.k === 'text') return '<i class="dsel-lg">T</i>';
      if (o.k === 'chart') return '<i class="dsel-lg">&#128202;</i>';
      if (o.k === 'image' && o.src && o.src.length < 300000) return '<i class="dsel-lt"><img alt="" src="' + esc(o.src) + '" style="width:18px;height:18px;object-fit:cover;border-radius:3px"></i>';
      var c = D.clone(o), kk = Math.min(16 / Math.max(1, c.w), 16 / Math.max(1, c.h));
      c.w = Math.max(2, c.w * kk); c.h = Math.max(2, c.h * kk); c.sh = null; c.rot = 0; if (c.ln) c.ln.w = Math.min(1.5, D.num(c.ln.w, 1));
      if (c.fill && c.fill.m === 'solid' && D.lum(D.col(c.fill.c, theme())) > 0.85) c.ln = { m: 'solid', c: '#94A3B8', w: 1 };
      return '<i class="dsel-lt">' + D.layerSVG(c, theme(), { noPad: true }) + '</i>';
    }
    function layerRow(k, inGroup) {
      var lk = isLocked(k), cv = isC(k);
      return '<div class="dsel-lr' + (sel.indexOf(k) >= 0 ? ' dsel-on' : '') + (lk ? ' dsel-lk' : '') + (inGroup ? ' dsel-in' : '') + '"' + (cv ? '' : ' draggable="true"') + ' data-p="pick" data-k="' + esc(k) + '">' + layerDot(k) +
        '<span' + (isL(k) ? ' data-rn="' + esc(k) + '" title="Double-click to rename"' : '') + '>' + esc(label(k)) + '</span>' +
        (cv ? '' : '<button type="button" class="dsel-lkb' + (lk ? ' dsel-on' : '') + '" data-p="lock" data-k="' + esc(k) + '" title="' + (lk ? 'Locked - click to unlock' : 'Lock: cannot be selected, moved or edited') + '">' + (lk ? IC.lock : IC.unlock) + '</button>') +
        '<button type="button" data-p="hide" data-k="' + esc(k) + '" title="Hide">' + IC.eye + '</button></div>';
    }
    function groupRows(list) {
      var h = '', seen = {};
      list.forEach(function (k) {
        var o = obj(k);
        if (!isL(k) || !o.grp) { h += layerRow(k, false); return; }
        if (seen[o.grp]) return; seen[o.grp] = 1;
        var mem = list.filter(function (x) { return isL(x) && obj(x).grp === o.grp; });
        if (mem.length < 2) { h += layerRow(k, false); return; }
        var id = esc(o.grp), allSel = mem.every(function (x) { return sel.indexOf(x) >= 0; }), allLk = mem.every(isLocked), shut = !!gfold[o.grp];
        h += '<div class="dsel-lr dsel-gr' + (allSel ? ' dsel-on' : '') + (shut ? ' dsel-shut' : '') + '" draggable="true" data-p="gpick" data-g="' + id + '" data-k="grp:' + id + '">' +
          '<button type="button" class="dsel-chev" data-p="gfold" data-g="' + id + '">' + IC.chev + '</button><span class="dsel-gn" data-rn="grp:' + id + '" title="Double-click to rename">' + esc((doc.gname || {})[o.grp] || ('Group of ' + mem.length)) + '</span>' +
          '<button type="button" class="dsel-lkb' + (allLk ? ' dsel-on' : '') + '" data-p="glock" data-g="' + id + '" title="Lock the whole group">' + (allLk ? IC.lock : IC.unlock) + '</button><button type="button" data-p="ghide" data-g="' + id + '" title="Hide the whole group">' + IC.eye + '</button></div>';
        if (!shut) mem.forEach(function (x) { h += layerRow(x, true); });
      });
      return h;
    }
    function layersHTML() {
      var pg = curPage(), all = layersAll || isSingle(), m = blockPages();
      var onPg = function (L) { return artOK(L) && (all || L.pg === pg || onMaster(L, pg)); };
      var front = layers().filter(function (L) { return onPg(L) && !L.hide && !L.behind; }).reverse().map(function (L) { return 'L:' + L.id; });
      var back = layers().filter(function (L) { return onPg(L) && !L.hide && L.behind; }).reverse().map(function (L) { return 'L:' + L.id; });
      var text = [];
      if (pg === 'c' || all) CFIELDS.forEach(function (f) { if ((f[0] === 'title' || doc[f[0]] != null) && !(doc.chide || {})[f[0]] && !(doc.cfree || {})[f[0]] && dzn().cover) text.push('C:' + f[0]); });
      blocks().forEach(function (b) { if (!b.hide && (all || m[b.id] === pg)) text.push('B:' + b.id); });
      var hid = layers().filter(function (L) { return onPg(L) && L.hide; }).map(function (L) { return 'L:' + L.id; })
        .concat(blocks().filter(function (b) { return b.hide; }).map(function (b) { return 'B:' + b.id; }))
        .concat(CFIELDS.filter(function (f) { return (doc.chide || {})[f[0]]; }).map(function (f) { return 'C:' + f[0]; }));
      var h = '<div class="dsel-seg" style="margin-bottom:6px"><button type="button" data-p="lall" data-n="0" class="' + (all ? '' : 'dsel-on') + '"' + (isSingle() ? ' disabled' : '') + '>' + esc(pageName(pg)) + '</button><button type="button" data-p="lall" data-n="1" class="' + (all ? 'dsel-on' : '') + '">All pages</button></div>' +
        '<p class="dsel-note">Top of the list is in front. Drag a row to reorder. Shift + click selects several. With this list open, the &uarr; and &darr; keys move the selected layer forward or backward. Double-click a name to rename it.</p>';
      if (!front.length && !text.length && !back.length && !hid.length) return h + '<p class="dsel-note">This page has no elements.</p>';
      if (front.length) h += '<div class="dsel-sub">In front of the text</div><div class="dsel-layers" data-sec="front">' + groupRows(front) + '</div>';
      if (text.length) h += '<div class="dsel-sub">Document text</div><div class="dsel-layers" data-sec="text">' + text.map(function (k) { return layerRow(k, false); }).join('') + '</div>';
      if (back.length) h += '<div class="dsel-sub">Behind the text</div><div class="dsel-layers" data-sec="back">' + groupRows(back) + '</div>';
      if (hid.length) h += '<div class="dsel-sub">Hidden</div><div class="dsel-layers">' + hid.map(function (k) { return '<div class="dsel-lr dsel-off">' + layerDot(k) + '<span>' + esc(label(k)) + '</span><button type="button" data-p="show" data-k="' + esc(k) + '" title="Show again">Show</button></div>'; }).join('') + '</div>';
      return h;
    }
    function renameRow(sp) {
      var key = sp.getAttribute('data-rn'), inp = el('input', 'dsel-gin'); inp.type = 'text'; inp.maxLength = 50;
      var isG = key.indexOf('grp:') === 0, g = isG ? key.slice(4) : null;
      inp.value = isG ? ((doc.gname || {})[g] || sp.textContent) : label(key);
      sp.parentNode.setAttribute('draggable', 'false'); sp.replaceWith(inp); inp.focus(); inp.select();
      var fin = false;
      function end(save) {
        if (fin) return; fin = true;
        var v = inp.value.replace(/\s+/g, ' ').trim().slice(0, 50);
        if (save && v) { begin(); if (isG) { doc.gname = doc.gname || {}; doc.gname[g] = v; } else obj(key).name = v; done(); }
        paintPanel();
      }
      inp.addEventListener('keydown', function (ev) { ev.stopPropagation(); if (ev.key === 'Enter') end(true); if (ev.key === 'Escape') end(false); });
      inp.addEventListener('blur', function () { end(true); });
    }
    function wireLayerDrag() {
      var src = null;
      panel.querySelectorAll('.dsel-lr[draggable="true"]').forEach(function (r) {
        r.addEventListener('dragstart', function (e) { src = r.getAttribute('data-k'); e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', 'x'); } catch (x) { /* ignore */ } });
        r.addEventListener('dragover', function (e) {
          if (!src) return; e.preventDefault();
          panel.querySelectorAll('.dsel-dt,.dsel-db').forEach(function (x) { x.classList.remove('dsel-dt', 'dsel-db'); });
          var b = r.getBoundingClientRect(); r.classList.add(e.clientY < b.top + b.height / 2 ? 'dsel-dt' : 'dsel-db');
        });
        r.addEventListener('dragleave', function () { r.classList.remove('dsel-dt', 'dsel-db'); });
        r.addEventListener('drop', function (e) {
          e.preventDefault(); var tk = r.getAttribute('data-k'), above = r.classList.contains('dsel-dt'); r.classList.remove('dsel-dt', 'dsel-db');
          if (!src || src === tk) return;
          var sec = r.parentNode.getAttribute('data-sec');
          var srcKeys = src.indexOf('grp:') === 0 ? layers().filter(function (L) { return L.grp === src.slice(4); }).map(function (L) { return 'L:' + L.id; }) : [src];
          var tgtKey = tk.indexOf('grp:') === 0 ? 'L:' + (layers().filter(function (L) { return L.grp === tk.slice(4); }).slice(-1)[0] || {}).id : tk;
          if (srcKeys.every(isL) && isL(tgtKey)) {
            begin();
            var ids = srcKeys.map(kid), a = layers(), mv = a.filter(function (L) { return ids.indexOf(L.id) >= 0; }), rest = a.filter(function (L) { return ids.indexOf(L.id) < 0; });
            var ti = rest.indexOf(lById(kid(tgtKey))); if (ti < 0) return;
            mv.forEach(function (L) { if (sec === 'back') L.behind = 1; else if (sec === 'front') delete L.behind; var tp = lById(kid(tgtKey)).pg; if (L.pg !== 'm' && tp !== 'm') L.pg = tp; });
            rest.splice.apply(rest, [above ? ti + 1 : ti, 0].concat(mv));     /* the list shows the front first */
            doc.layers = rest; if (api && api.drawLayers) api.drawLayers(); sync(); done(); paintPanel();
          } else if (srcKeys.every(isB) && isB(tgtKey)) {
            begin();
            var bid = srcKeys.map(kid), ab = blocks(), mvb = ab.filter(function (b) { return bid.indexOf(b.id) >= 0; }), restb = ab.filter(function (b) { return bid.indexOf(b.id) < 0; });
            var tb = restb.indexOf(bById(kid(tgtKey))); if (tb < 0) return;
            restb.splice.apply(restb, [above ? tb : tb + 1, 0].concat(mvb)); doc.blocks = restb; env.rerender(); done(); paintPanel();
          } else if (srcKeys.every(isL) && sec === 'text') { begin(); srcKeys.forEach(function (k2) { var L = obj(k2); if (above) delete L.behind; else L.behind = 1; }); if (api && api.drawLayers) api.drawLayers(); sync(); done(); paintPanel(); }
          src = null;
        });
        r.addEventListener('dragend', function () { src = null; });
      });
    }
    function addPanel() {
      function b(n, l, ic, p) { return '<button type="button" class="dsel-add" data-p="' + (p || 'new') + '" data-n="' + n + '"><i>' + ic + '</i>' + l + '</button>'; }
      var shapes = [['rect', 'Rectangle', '&#9645;'], ['roundRect', 'Rounded box', '&#9649;'], ['ellipse', 'Circle', '&#9711;'], ['line', 'Line', '&#9585;'], ['triangle', 'Triangle', '&#9651;'], ['diamond', 'Diamond', '&#9671;'], ['hexagon', 'Hexagon', '&#11041;'], ['pentagon', 'Pentagon', '&#11040;'],
        ['rightArrow', 'Arrow', '&#10145;'], ['chevron', 'Chevron', '&#10095;'], ['star5', 'Star', '&#9734;'], ['octagon', 'Octagon', '&#11042;'], ['rtTriangle', 'Right triangle', '&#9698;'], ['leftArrow', 'Arrow left', '&#11013;'], ['parallelogram', 'Parallelogram', '&#9649;']];
      var bl = [['p', 'Paragraph', '&para;'], ['h1', 'Section heading', '<b>H</b>'], ['h2', 'Subheading', 'h'], ['ul', 'Bullet list', '&#8226;'], ['ol', 'Numbered list', '1.'], ['check', 'Checklist', '&#9745;'], ['table', 'Table', '&#9638;'], ['chart', 'Chart', '&#128202;'], ['image', 'Picture', IC.pic],
        ['quote', 'Quote', '&#8220;'], ['callout', 'Callout box', '!'], ['stats', 'Key numbers', '#'], ['timeline', 'Timeline', '&#8942;'], ['cols', 'Two columns', '&#9707;'], ['kv', 'Details table', '&#9636;'], ['pb', 'Page break', '&#8213;']];
      return head('Add element') + '<div class="dsel-pb"><div class="dsel-grid2">' + b('text', 'Text box', 'T') + b('head', 'Heading', '<b>H</b>') +
        shapes.map(function (s) { return b(s[0], s[1], s[2]); }).join('') + b('pic', 'Picture', IC.pic) + b('chart', 'Chart', '&#128202;') +
        '</div><div class="dsel-sub">Add to the document text</div><div class="dsel-grid2">' + bl.map(function (x) { return b(x[0], x[1], x[2], 'newblk'); }).join('') +
        '</div><div class="dsel-sub">Icons</div>' + iconGrid('newicon') +
        '<p class="dsel-note">Shapes, text boxes, pictures and icons appear in the middle of the page you are looking at, selected. Drag them into place. Document parts are added under the selected text.</p></div>';
    }
    function iconGrid(act) { return '<div class="dsel-icons">' + QUICK_ICONS.map(function (n) { return '<button type="button" data-p="' + act + '" data-n="' + n + '" title="' + n.split(':')[1].replace(/-/g, ' ') + '"><img alt="" src="https://api.iconify.design/' + n.replace(':', '/') + '.svg?color=%23334155"></button>'; }).join('') + '</div>'; }
    var lastIcons = [];
    function iconsPanel(replace) {
      return head(replace ? 'Change icon' : 'Icons') + '<div class="dsel-pb"><div class="dsel-row"><input type="text" data-pc="isearch" placeholder="Search icons, e.g. growth, phone" value="' + esc(iconQ) + '" spellcheck="false"><button type="button" class="dsel-pbt" data-p="isearch">Search</button></div>' +
        '<div id="dsel-ires">' + (lastIcons.length && iconQ ? iconRes(replace) : iconGrid(replace ? 'seticon' : 'newicon')) + '</div><p class="dsel-note">Free icons from Iconify. Click one to ' + (replace ? 'use it' : 'add it to the page') + '. Change the colour from the top bar.</p></div>';
    }
    function iconRes(replace) {
      if (!lastIcons.length) return '<p class="dsel-note">No icons found.</p>';
      return '<div class="dsel-icons">' + lastIcons.map(function (ic, i) { return '<button type="button" data-p="' + (replace ? 'setires' : 'iadd') + '" data-n="' + i + '" title="' + esc(ic.n.split(':')[1].replace(/-/g, ' ')) + '">' + D.layerSVG({ k: 'icon', ic: ic, w: 22, h: 22, tc: '#334155' }, theme(), { noPad: true }) + '</button>'; }).join('') + '</div>';
    }
    function iconSearch() {
      var inp = panel.querySelector('[data-pc="isearch"]'), box = panel.querySelector('#dsel-ires'); if (!inp || !box) return;
      var q = inp.value.trim(); iconQ = q; if (!q) return;
      box.innerHTML = '<span class="dd-spin" style="border-color:#CBD5E1;border-top-color:#3B82F6"></span>';
      var key = q.toLowerCase(), replace = pmode === 'icon';
      var got = ICON_CACHE[key] ? Promise.resolve(ICON_CACHE[key]) : fetch('https://api.iconify.design/search?limit=72&query=' + encodeURIComponent(q)).then(function (r) { if (r.status === 429) throw new Error('busy'); return r.json(); }).then(function (j) {
        var groups = {}; (j.icons || []).forEach(function (n) { var x = n.split(':'); (groups[x[0]] = groups[x[0]] || []).push(x[1]); });
        return Promise.all(Object.keys(groups).map(function (p) {
          return fetch('https://api.iconify.design/' + p + '.json?icons=' + groups[p].join(',')).then(function (r) { return r.json(); }).then(function (d) {
            return groups[p].map(function (nm) { var ic = d.icons && d.icons[nm]; return ic ? { n: p + ':' + nm, b: ic.body, vw: ic.width || d.width || 24, vh: ic.height || d.height || 24 } : null; }).filter(Boolean);
          }).catch(function () { return []; });
        })).then(function (a) { var out = [].concat.apply([], a); ICON_CACHE[key] = out; return out; });
      });
      got.then(function (list) { lastIcons = list; var b2 = panel.querySelector('#dsel-ires'); if (b2) b2.innerHTML = iconRes(replace); })
        .catch(function (e) { var b3 = panel.querySelector('#dsel-ires'); if (b3) b3.innerHTML = '<p class="dsel-note">' + (e && e.message === 'busy' ? 'The icon library is busy. Please try again in a moment.' : 'Icons could not be loaded.') + '</p>'; });
    }
    function logoPanel() {
      var L = doc.logo, has = L && L.src;
      var h = head('Logo') + '<div class="dsel-pb">';
      h += has ? '<div class="dsel-lgpv"><img alt="Logo" src="' + esc(L.src) + '"></div>' : '<p class="dsel-note" style="margin-top:0">Add your company logo. It shows in the same place on every page, and in the Word file.</p>';
      h += '<button type="button" class="dsel-pbt" data-p="lgpick">' + (has ? 'Change logo' : '+ Add logo') + '</button>';
      if (has) {
        var pos = L.pos || 'tr';
        h += '<div class="dsel-sub">Place</div><div class="dsel-grid2">' + [['tl', 'Top left'], ['tr', 'Top right'], ['bl', 'Bottom left'], ['br', 'Bottom right']].map(function (x) { return '<button type="button" class="dsel-pbt' + (pos === x[0] ? ' dsel-on' : '') + '" data-p="lgpos" data-n="' + x[0] + '">' + x[1] + '</button>'; }).join('') + '</div>' +
          slider('Size', 'lgw', 30, 320, 1, Math.round(D.num(L.w, 120))) + slider('Distance from the edge', 'lgm', 6, 120, 1, Math.round(D.num(L.m, 28))) + slider('Transparency', 'lgop', 0, 100, 1, Math.round((1 - D.num(L.op, 1, 0, 1)) * 100), '%') +
          '<label class="dsel-chk" style="margin:8px 0"><input type="checkbox" data-pc="lghide"' + (L.hide ? '' : ' checked') + '> Show the logo</label>' +
          '<button type="button" class="dsel-pbt dsel-danger" data-p="lgdel" style="margin-top:6px">Remove logo</button>';
      }
      return h + '</div>';
    }
    /* ================= company logo: the same dialog as the AI Presentation editor ================= */
    var LGPOS = [['tl', 'Top left', 0, 0], ['tc', 'Top centre', 50, 0], ['tr', 'Top right', 100, 0], ['bl', 'Bottom left', 0, 100], ['bc', 'Bottom centre', 50, 100], ['br', 'Bottom right', 100, 100], ['c', 'Centre', 50, 50]];
    function readLogoFile(file, cb) {
      if (!file || !/^image\/(png|jpe?g|svg\+xml|webp|gif)$/i.test(file.type)) { env.toast('Please choose a PNG, JPG, SVG or WebP picture.', 'err'); return; }
      var fr = new FileReader();
      fr.onload = function () {
        var img = new Image();
        img.onload = function () {
          var w = img.naturalWidth || 600, h = img.naturalHeight || 240, k = Math.min(1, 1000 / Math.max(w, h));
          if (/svg/i.test(file.type) && Math.max(w, h) < 1000) k = 1000 / Math.max(w, h);
          var c = document.createElement('canvas'); c.width = Math.max(1, Math.round(w * k)); c.height = Math.max(1, Math.round(h * k));
          c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
          var du = c.toDataURL('image/png');
          if (du.length > 700000) du = c.toDataURL('image/webp', 0.9);
          cb(du, Math.round(h / w * 1000) / 1000);
        };
        img.onerror = function () { env.toast('That picture could not be read.', 'err'); };
        img.src = fr.result;
      };
      fr.readAsDataURL(file);
    }
    function logoDialog() {
      closeMenu(); if (pmode === 'logo') { pmode = ''; paint(); }
      var dz = dzn(), single = dz.kind === 'single', hasCover = single || !!(dz.kind === 'flow' && dz.cover), hasPages = !single;
      var W = D.clone(doc.logo || {});
      ['cv', 'pg'].forEach(function (key) {      /* start from what the page shows now */
        var c = D.logoCfg(Object.assign({}, { logo: Object.assign({}, W, { hide: 0, src: W.src || 'x' }) }), key === 'cv' ? 'cover' : 'page');
        W[key] = Object.assign({}, D.LOGO_DEF[key], W[key] || {}, c ? { x: c.x, y: c.y, s: c.s } : {});
      });
      delete W.pos; delete W.w; delete W.m; delete W.hide;
      var view = hasCover ? 'cover' : 'page', card = dz.kind === 'panels';
      if (card) W.bc = Object.assign({ f: 50, b: 50 }, W.bc || {});
      var m = el('div', 'dsdoc-lgm'); m.innerHTML = '<div class="dsdoc-lgb" role="dialog" aria-label="Company logo"><div class="dsdoc-lgh"><h3>&#127991; Company logo</h3><button type="button" class="dsdoc-lgx" data-l="x" title="Close">&times;</button></div><div class="dsdoc-lgc"></div>' +
        '<div class="dsdoc-lgf"><button type="button" class="dsdoc-lgbt" data-l="del">Remove logo</button><span></span><button type="button" class="dsdoc-lgbt" data-l="x">Cancel</button><button type="button" class="dsdoc-lgbt pri" data-l="ok">&#10003; Apply to all pages</button></div></div>';
      document.body.appendChild(m);
      var body = m.querySelector('.dsdoc-lgc');
      function sl(key, f, lab, a, b) { var v = Math.round(D.num(W[key][f], 0)); return '<div class="dsdoc-lgs"><div class="dsdoc-lgsh"><span>' + lab + '</span><em data-v="' + key + '.' + f + '">' + v + '%</em></div><input type="range" min="0" max="100" step="1" value="' + v + '" data-r="' + key + '.' + f + '"><div class="dsdoc-lgse"><span>' + a + '</span><span>' + b + '</span></div></div>'; }
      function drop(which) {
        var src = which === 2 ? W.src2 : W.src, t = which === 2 ? 'Upload a short logo or icon for the pages after the cover. Without one, the main logo is used.' : 'Click to upload PNG, JPG, SVG or WebP';
        return '<label class="dsdoc-lgd' + (src ? ' has' : '') + '">' + (src ? '<img alt="" src="' + esc(src) + '"><span>Click to change</span>' : '<span><b class="dsdoc-lgup">&#8679; Upload logo</b>' + t + '</span>') + '<input type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" data-f="' + which + '"></label>' +
          (which === 2 && src ? '<button type="button" class="dsdoc-lglink" data-l="rm2">Use the main logo on these pages</button>' : '');
      }
      function part(key, title, showLab, badgeLab) {
        var c = W[key], pos = LGPOS.filter(function (x) { return Math.abs(x[2] - c.x) < 1 && Math.abs(x[3] - c.y) < 1; })[0];
        return '<div class="dsdoc-lgt">' + title + '</div><label class="dsdoc-lgck"><input type="checkbox" data-c="' + key + '.on"' + (c.on === 0 ? '' : ' checked') + '> ' + showLab + '</label>' +
          '<div class="dsdoc-lgl">Position</div>' + sl(key, 'y', 'Top to bottom', 'Top', 'Bottom') + sl(key, 'x', 'Left to right', 'Left', 'Right') +
          '<div class="dsdoc-lgl">Size</div>' + sl(key, 's', 'Logo size', 'Small', 'Whole page') +
          '<div class="dsdoc-lgp">' + LGPOS.map(function (x) { return '<button type="button" data-p="' + key + '.' + x[0] + '" class="' + (pos && pos[0] === x[0] ? 'on' : '') + '">' + x[1] + '</button>'; }).join('') + '</div>' +
          '<div class="dsdoc-lgl">' + badgeLab + ' (box behind the logo)</div><label class="dsdoc-lgck"><input type="checkbox" data-c="' + key + '.badge"' + (c.badge ? ' checked' : '') + '> Show the badge</label>' +
          (c.badge ? sl(key, 'bc', 'Badge colour', 'Darker', 'Lighter') + sl(key, 'glow', 'Glow around the badge', 'None', 'Strong') + '<p class="dsdoc-lgn">Use darker for light logos and lighter for dark logos. The middle is see-through.</p>' : '');
      }
      function draw() {
        var st = body.querySelector('.dsdoc-lgr'), sc = st ? st.scrollTop : 0;
        var h = '<div class="dsdoc-lgv"><div class="dsdoc-lgpv"></div><div class="dsdoc-lgvt">' + (card ? '<button type="button" data-l="vp" class="' + (view === 'page' ? 'on' : '') + '">Preview: front</button><button type="button" data-l="vb" class="' + (view === 'back' ? 'on' : '') + '">Preview: back</button>' : hasPages ? '<button type="button" data-l="vp" class="' + (view === 'page' ? 'on' : '') + '">Preview: page</button>' : '') + (hasCover ? '<button type="button" data-l="vc" class="' + (view === 'cover' ? 'on' : '') + '">Preview: ' + (single ? 'page' : 'cover') + '</button>' : '') + '</div>' +
          '<p class="dsdoc-lgn">' + (hasCover && hasPages ? 'The cover and the other pages each have their own position and size. Both are used in the downloaded Word document.' : 'The logo is used in the downloaded Word document too.') + '</p></div>';
        h += '<div class="dsdoc-lgr"><div class="dsdoc-lgl">Logo image (' + (hasCover && hasPages ? 'cover and, unless you add another below, every page' : 'every page') + ')</div>' + drop(1);
        if (W.src && card) {
          h += '<div class="dsdoc-lgt">Front of the card</div>' + sl('bc', 'f', 'Logo size', 'Small', 'Big') +
            '<div class="dsdoc-lgt">Back of the card</div><label class="dsdoc-lgck"><input type="checkbox" data-c="bc.back"' + (W.bc.back === 0 ? '' : ' checked') + '> Also show the logo on the details side</label>' +
            sl('bc', 'b', 'Logo size', 'Small', 'Big') + '<p class="dsdoc-lgn">To move the logo or any text, drag it on the card. Double-click the logo to come back here.</p>';
        } else if (W.src) {
          if (hasCover) h += part('cv', single ? 'The page' : 'Cover page', single ? 'Show the logo' : 'Show the logo on the cover', single ? 'Badge' : 'Cover badge');
          if (hasPages) h += (hasCover ? '<div class="dsdoc-lgsep"></div>' : '') + '<div class="dsdoc-lgt">' + (hasCover ? 'Other pages' : 'Pages') + '</div>' + (hasCover ? '<div class="dsdoc-lgl">Logo for these pages (optional)</div>' + drop(2) : '') + part('pg', '', 'Show the logo on ' + (hasCover ? 'these pages' : 'every page'), 'Page badge').replace('<div class="dsdoc-lgt"></div>', '');
        }
        h += '</div>';
        body.innerHTML = h;
        var r2 = body.querySelector('.dsdoc-lgr'); if (r2) r2.scrollTop = sc;
        preview();
      }
      var pvT = 0;
      function preview() {
        clearTimeout(pvT);
        pvT = setTimeout(function () {
          var box = body.querySelector('.dsdoc-lgpv'); if (!box) return;
          var pd = D.clone(doc), sz = size(); pd.logo = W.src ? W : null; pd.blocks = (pd.blocks || []).slice(0, 16);
          var bw = box.clientWidth || 360, k = bw / sz.w;
          box.style.height = Math.round(sz.h * k) + 'px'; box.innerHTML = '';
          var inner = el('div'); inner.style.cssText = 'position:absolute;left:0;top:0;width:' + sz.w + 'px;transform-origin:0 0;pointer-events:none';
          box.appendChild(inner);
          var keepC = D._cdoc;
          try { D.render(inner, pd, { edit: false, gap: 0 }); } catch (er) { D._cdoc = keepC; return; }
          D._cdoc = keepC;
          var target = view === 'cover' ? inner.querySelector('.dsd-cover,.dsd-single') : Array.prototype.filter.call(inner.querySelectorAll('.dsd-page'), function (n) { return !n.classList.contains('dsd-cover'); })[view === 'back' ? 1 : 0];
          var oy = 0; if (target) { var a = inner.getBoundingClientRect(), b = target.getBoundingClientRect(); oy = b.top - a.top; }
          inner.style.transform = 'scale(' + k + ') translateY(' + (-oy) + 'px)';
        }, 120);
      }
      function close() { m.remove(); document.removeEventListener('keydown', onEsc, true); }
      function onEsc(e) { if (e.key === 'Escape') { e.stopPropagation(); close(); } }
      document.addEventListener('keydown', onEsc, true);
      m.addEventListener('click', function (e) {
        if (e.target === m) { close(); return; }
        var b = e.target.closest('[data-l],[data-p]'); if (!b) return;
        var l = b.getAttribute('data-l'), p = b.getAttribute('data-p');
        if (l === 'x') { close(); return; }
        if (l === 'vp' || l === 'vc' || l === 'vb') { view = l === 'vp' ? 'page' : l === 'vb' ? 'back' : 'cover'; draw(); return; }
        if (l === 'rm2') { delete W.src2; delete W.ar2; draw(); return; }
        if (l === 'del') { close(); if (doc.logo) { begin(); delete doc.logo; env.rerender(); done(); } return; }
        if (l === 'ok') {
          close(); if (!W.src) return;
          begin(); doc.logo = W; env.rerender(); done(); env.toast('Logo applied to all pages.', '', 1500); return;
        }
        if (p) { var q = p.split('.'), ps = LGPOS.filter(function (x) { return x[0] === q[1]; })[0]; if (ps) { W[q[0]].x = ps[2]; W[q[0]].y = ps[3]; if (q[0] === 'cv') view = 'cover'; else view = 'page'; draw(); } }
      });
      m.addEventListener('input', function (e) {
        var t = e.target, r = t.getAttribute('data-r'); if (!r) return;
        var q = r.split('.'); W[q[0]][q[1]] = +t.value;
        var em = body.querySelector('[data-v="' + r + '"]'); if (em) em.textContent = t.value + '%';
        if (q[0] === 'bc') { view = q[1] === 'b' ? 'back' : 'page'; body.querySelectorAll('.dsdoc-lgvt button').forEach(function (x) { x.classList.toggle('on', x.getAttribute('data-l') === (view === 'back' ? 'vb' : 'vp')); }); preview(); return; }
        var want = q[0] === 'cv' ? 'cover' : 'page'; if (view !== want && ((want === 'cover' && hasCover) || (want === 'page' && hasPages))) { view = want; body.querySelectorAll('.dsdoc-lgvt button').forEach(function (x) { x.classList.toggle('on', (x.getAttribute('data-l') === 'vc') === (view === 'cover')); }); }
        body.querySelectorAll('[data-p^="' + q[0] + '."]').forEach(function (x) { var ps = LGPOS.filter(function (y) { return q[0] + '.' + y[0] === x.getAttribute('data-p'); })[0]; x.classList.toggle('on', !!ps && Math.abs(ps[2] - W[q[0]].x) < 1 && Math.abs(ps[3] - W[q[0]].y) < 1); });
        preview();
      });
      m.addEventListener('change', function (e) {
        var t = e.target;
        if (t.getAttribute('data-c')) { var q = t.getAttribute('data-c').split('.'); if (q[1] === 'on') W[q[0]].on = t.checked ? 1 : 0; else W[q[0]][q[1]] = t.checked ? 1 : 0; if (q[1] === 'badge' && t.checked && W[q[0]].bc == null) W[q[0]].bc = 50; draw(); return; }
        if (t.getAttribute('data-f')) {
          var two = t.getAttribute('data-f') === '2';
          readLogoFile(t.files && t.files[0], function (du, ar) { if (two) { W.src2 = du; W.ar2 = ar; } else { W.src = du; W.ar = ar; delete W.pid; delete W.url; } draw(); });
        }
      });
      if (W.src && !W.ar) { var im = new Image(); im.onload = function () { if (im.naturalWidth) { W.ar = Math.round(im.naturalHeight / im.naturalWidth * 1000) / 1000; preview(); } }; im.src = W.src; }
      draw();
    }
    function resetPanel() {
      var k = sel[0];
      return head('Reset') + '<div class="dsel-pb"><p class="dsel-note" style="margin-top:0">Reset puts the whole document back to how it was when you opened the editor: text, elements, positions, sizes, colours and order.</p>' +
        '<button type="button" class="dsel-pbt dsel-danger" data-p="rs-all">Reset the whole document</button>' +
        (k && !isL(k) && Object.keys(stOf(k)).length ? '<button type="button" class="dsel-pbt" data-p="rs-el" style="margin-top:8px">Reset only the look of the selected text</button>' : '') + '</div>';
    }
    function paintPanel() {
      if (!panel) return;
      var ae = document.activeElement; if (ae && panel.contains(ae) && (ae.className === 'dsel-gin' || ae.getAttribute('data-pc') === 'isearch' || ae.getAttribute('data-pi'))) return;
      var k = sel[0], h = '';
      if (!pmode) { panel.innerHTML = ''; return; }
      if (pmode === 'pos' || pmode === 'layers') h = posPanel(k, pmode);
      else if (pmode === 'add') h = addPanel();
      else if (pmode === 'icons') h = iconsPanel(false);
      else if (pmode === 'logo') h = logoPanel();
      else if (pmode === 'reset') h = resetPanel();
      else if (pmode === 'theme') h = themePanel();
      else if (!k && inPin) { panel.innerHTML = head(inPin.title || 'Settings') + '<div class="dsel-pb"><p class="dsel-note">Select an element to use this window.</p></div>'; return; }
      else if (!k) { pmode = ''; panel.innerHTML = ''; return; }
      else if (pmode === 'align') h = head(isB(k) ? 'Place' : 'Align') + '<div class="dsel-pb">' + alignHTML() + '</div>';
      else if (pmode === 'color' || pmode === 'fill' || pmode === 'line') h = colorPanel(pmode, k);
      else if (pmode === 'shadow') h = shadowPanel(k);
      else if (pmode === 'icon') h = iconsPanel(true);
      else if (pmode === 'talign') { var cur = getP(k, 'al') || 'left'; h = head('Text alignment') + '<div class="dsel-pb"><div class="dsel-grid2">' + [['left', 'Left'], ['center', 'Centre'], ['right', 'Right'], ['justify', 'Justify']].map(function (x) { return '<button type="button" class="dsel-pbt dsel-al dsel-w2' + (cur === x[0] ? ' dsel-on' : '') + '" data-p="ta" data-n="' + x[0] + '">' + alignIcon(x[0]) + '<span>' + x[1] + '</span></button>'; }).join('') + '</div></div>'; }
      else if (pmode === 'font') { var ff = getP(k, 'ff'); h = head('Font') + '<div class="dsel-pb dsel-list">' + Object.keys(D.FONTS).map(function (f) { return '<button type="button" data-p="ff" data-n="' + esc(f) + '" class="' + (ff === f ? 'dsel-on' : '') + '" style="font-family:' + esc(D.css(f)).replace(/&quot;/g, "'") + '">' + esc(f) + '</button>'; }).join('') + '<p class="dsel-note">These are Word fonts, so the document looks the same when it opens in Word.</p></div>'; }
      else if (pmode === 'weight') { var fw = getP(k, 'fw'); h = head('Weight') + '<div class="dsel-pb dsel-list">' + WT.map(function (x) { return '<button type="button" data-p="wt" data-n="' + x[0] + '" class="' + (wtName(fw) === x[1] ? 'dsel-on' : '') + '" style="font-weight:' + x[0] + '">' + x[1] + '</button>'; }).join('') + '<p class="dsel-note">Word shows Light and Medium as regular, and Semi-bold and heavier as bold.</p></div>'; }
      else if (pmode === 'spacing') h = head('Spacing') + '<div class="dsel-pb">' + slider('Line height', 'lh', 0.8, 3, 0.05, r2(D.num(getP(k, 'lh'), 1.3))) + slider('Letter spacing', 'ls', -2, 20, 0.5, r1(D.num(getP(k, 'ls'), 0))) + (isL(k) ? slider('Inner margin', 'pad', 0, 60, 1, Math.round(D.num(obj(k).pad, 8))) + '<div class="dsel-sub">Vertical position</div><div class="dsel-seg">' + ['top', 'middle', 'bottom'].map(function (v) { return '<button type="button" data-p="va" data-n="' + v + '" class="' + ((obj(k).va || 'top') === v ? 'dsel-on' : '') + '">' + v.charAt(0).toUpperCase() + v.slice(1) + '</button>'; }).join('') + '</div>' : '') + '</div>';
      else if (pmode === 'corners') { var r = domOf(k), mx = isL(k) ? Math.floor(Math.min(obj(k).w, obj(k).h) / 2) : 120; h = head('Round corners') + '<div class="dsel-pb">' + slider('Roundness', 'r', 0, Math.max(1, mx), 1, Math.min(mx, Math.round(D.num(getP(k, 'r'), 0)))) + '<p class="dsel-note">Drag to the right for rounder corners, to the left for sharp ones.</p></div>'; }
      else if (pmode === 'opacity') h = head('Transparency') + '<div class="dsel-pb">' + slider('Opacity', 'op', 0, 100, 1, Math.round(D.num(getP(k, 'op'), 1, 0, 1) * 100), '%') + '</div>';
      var sc = panel.scrollTop;
      panel.innerHTML = h; panel.scrollTop = sc;
      if (inPin) { var tb = panel.querySelector('.dsel-ph b'); if (tb) inPin.title = tb.textContent; }
      if (pmode === 'layers' && sel.length) { var cur2 = panel.querySelector('.dsel-lr.dsel-on'); if (cur2) try { cur2.scrollIntoView({ block: 'nearest' }); } catch (x) { /* ignore */ } }
      wireLayerDrag();
    }
    /* ---- panel events ---- */
    function editPaint(k, target, fn) {   /* change the fill / outline of every selected element */
      var pk = pKey(target, k); if (!pk) return;
      begin();
      sel.forEach(function (x) { if (!supports(x, pk)) return; var p = D.clone(getP(x, pk) || { m: 'none' }); fn(p, x); setP(x, pk, p); });
      redraw();
    }
    function gsDown(ev) {
      var s = ev.target.closest('.dsel-gs'); if (!s) return;
      ev.preventDefault();
      var i = +s.getAttribute('data-n'), barEl = s.parentNode, r = barEl.getBoundingClientRect(), k = sel[0], target = pmode;
      gSel = i;
      function mv(x) { var v = Math.round(Math.max(0, Math.min(100, (x.clientX - r.left) / r.width * 100))); editPaint(k, target, function (p) { if (p.g && p.g.s[i]) p.g.s[i][0] = v; }); s.style.left = v + '%'; }
      function up() { window.removeEventListener('pointermove', mv, true); window.removeEventListener('pointerup', up, true); done(); paintPanel(); }
      window.addEventListener('pointermove', mv, true); window.addEventListener('pointerup', up, true);
    }
    function onPanelClick(ev) {
      var b = ev.target.closest('[data-p]'); if (!b || b.disabled) return;
      var p = b.getAttribute('data-p'), n = b.getAttribute('data-n'), k = sel[0], t = theme();
      if (p === 'close') { pmode = ''; paint(); return; }
      if (p === 'pin') { if (!inPin) pinCurrent(); return; }
      if (p === 'theme') { begin(); doc.theme = n || ''; doc.fonts = null; env.rerender(); done(); paintPanel(); return; }
      if (p === 'tab-pos') { pmode = 'pos'; paint(); return; }
      if (p === 'tab-layers') { pmode = 'layers'; paint(); return; }
      if (p === 'lall') { layersAll = n === '1'; paintPanel(); return; }
      if (p === 'pick') { var lk = b.getAttribute('data-k'); if (ev.target.closest('button') && ev.target.closest('button') !== b) return; select([lk], ev.shiftKey); var nn = domOf(lk); if (nn && !ev.shiftKey) nn.scrollIntoView({ block: 'nearest' }); return; }
      if (p === 'gpick') { select(layers().filter(function (L) { return L.grp === b.getAttribute('data-g'); }).map(function (L) { return 'L:' + L.id; })); return; }
      if (p === 'gfold') { var g0 = b.getAttribute('data-g'); gfold[g0] = !gfold[g0]; paintPanel(); ev.stopPropagation(); return; }
      if (p === 'lock') { ev.stopPropagation(); var k1 = b.getAttribute('data-k'); setLock([k1], !isLocked(k1)); return; }
      if (p === 'hide') { ev.stopPropagation(); setHide([b.getAttribute('data-k')], true); return; }
      if (p === 'show') { setHide([b.getAttribute('data-k')], false); return; }
      if (p === 'glock' || p === 'ghide') { ev.stopPropagation(); var mem = layers().filter(function (L) { return L.grp === b.getAttribute('data-g'); }).map(function (L) { return 'L:' + L.id; }); if (p === 'glock') setLock(mem, !mem.every(isLocked)); else setHide(mem, true); return; }
      if (p === 'new') {
        if (n === 'text' || n === 'head') addText(n); else if (n === 'pic') addPicture(); else if (n === 'chart') addChart(); else addShape(n);
        return;
      }
      if (p === 'newblk') { newBlock(n); return; }
      if (p === 'newicon') { addIconByName(n); return; }
      if (p === 'seticon') { addIconByName(n, k); return; }
      if (p === 'iadd') { var ic = lastIcons[+n]; if (ic) addLayer({ k: 'icon', ic: ic, w: 64, h: Math.round(64 * ic.vh / ic.vw), tc: onDark() ? '@w' : '@p' }, true); return; }
      if (p === 'setires') { var ic2 = lastIcons[+n]; if (ic2 && k) { begin(); obj(k).ic = ic2; redraw([k]); done(); } return; }
      if (p === 'isearch') { iconSearch(); return; }
      if (p === 'cfree') { var ck2 = sel.filter(isC); if (!ck2.length) return; begin(); var nk2 = makeFree(ck2); env.rerender(); done(); sel = nk2; selChanged(); return; }
      if (p === 'lgpick') { env.pickPicture(function (pic) { begin(); doc.logo = Object.assign({ pos: 'tr', w: 120, m: 28 }, doc.logo || {}, { src: pic.data || pic.src, pid: pic.pid || 0, url: pic.url || '' }); delete doc.logo.hide; env.rerender(); done(); paintPanel(); }); return; }
      if (p === 'lgpos') { begin(); doc.logo.pos = n; env.rerender(); done(); paintPanel(); return; }
      if (p === 'lgdel') { begin(); delete doc.logo; env.rerender(); done(); paintPanel(); return; }
      if (p === 'rs-all') { if (window.confirm('Put the whole document back to how it was when you opened the editor?')) { env.resetAll(); pmode = ''; sel = []; selChanged(); } return; }
      if (p === 'rs-el') { begin(); sel.forEach(function (x) { if (isB(x)) delete obj(x).st; if (isC(x) && doc.cst) delete doc.cst[kid(x)]; }); redraw(); done(); paint(); return; }
      if (p === 'ratio') { ratioLock = !ratioLock; paintPanel(); return; }
      if (p === 'bfull') { begin(); sel.filter(isB).forEach(function (x) { var o = obj(x); delete o.w; delete o.ox; }); redraw(); done(); paintPanel(); return; }
      if (/^(fwd|bwd|front|back)$/.test(p)) { arrange(p); return; }
      if (p === 'amode') { amode = n; paintPanel(); return; }
      if (/^al-|^ds-/.test(p)) { alignTo(p.slice(3)); return; }
      if (p === 'ff') { setAll('ff', n); paint(); return; }
      if (p === 'wt') { setAll('fw', +n); paint(); return; }
      if (p === 'ta') { setAll('al', n); paint(); return; }
      if (p === 'va') { begin(); sel.filter(isL).forEach(function (x) { obj(x).va = n; }); redraw(); done(); paintPanel(); return; }
      if (!k) return;
      if (p === 'fmode' && n === 'pic') {
        var cp = getP(k, 'fill');
        if (cp && cp.m === 'pic') { paint(); return; }
        insertPicFill(k); return;
      }
      if (p === 'pfins') { insertPicFill(k); return; }
      if (p === 'pfdel') { editPaint(k, 'fill', function (q) { Object.keys(q).forEach(function (x) { delete q[x]; }); q.m = 'solid'; q.c = '@p'; }); done(); paint(); return; }
      if (p === 'pffit') { editPaint(k, 'fill', function (q) { if (q.m === 'pic') q.st = n === 'stretch' ? 1 : 0; }); done(); paint(); return; }
      if (p === 'fmode') {
        editPaint(k, pmode, function (q, x) {
          q.m = n;
          if (n === 'grad' && !q.g) q.g = { t: 'linear', a: 0, s: [[0, q.c || '@p', 1], [100, '@a', 1]] };
          if (n !== 'none' && !q.c) q.c = pmode === 'fill' ? '@p' : '@a';
          if (n !== 'none' && pKey(pmode, x) === 'ln' && !q.w) q.w = 2;
        });
        done(); paint(); return;
      }
      if (p === 'col') {
        var c = b.getAttribute('data-c'), pk = pKey(pmode, k);
        if (!pk) { setAll('tc', c); paint(); return; }
        editPaint(k, pmode, function (q) { if (q.m === 'grad' && q.g) { q.g.s[Math.min(gSel, q.g.s.length - 1)][1] = c; } else { q.c = c; if (!q.m || q.m === 'none') q.m = 'solid'; } });
        done(); paint(); return;
      }
      if (p === 'gpre') { var pr = gPresets(t, (getP(k, pKey(pmode, k)) || {}).c)[+n]; editPaint(k, pmode, function (q) { q.m = 'grad'; q.g = q.g || { t: 'linear', a: 0 }; q.g.s = D.clone(pr); }); gSel = 0; done(); paint(); return; }
      if (p === 'gdir') { editPaint(k, pmode, function (q) { if (q.g) q.g.a = +n; }); done(); paintPanel(); return; }
      if (p === 'gstop') { gSel = +n; paintPanel(); return; }
      if (p === 'gadd') { editPaint(k, pmode, function (q) { var s = q.g.s, a = s[gSel] || s[0], z = s[Math.min(s.length - 1, gSel + 1)] || a; s.push([Math.round((a[0] + (z === a ? 100 : z[0])) / 2), a[1], 1]); s.sort(function (x, y) { return x[0] - y[0]; }); }); done(); paintPanel(); return; }
      if (p === 'gdel') { editPaint(k, pmode, function (q) { if (q.g.s.length > 2) q.g.s.splice(gSel, 1); }); gSel = 0; done(); paintPanel(); return; }
    }
    function onPanelInput(ev) {
      var t = ev.target, ps = t.getAttribute('data-ps'); if (!ps) return;
      var v = +t.value, k = sel[0], em = panel.querySelector('[data-pv="' + ps + '"]');
      if (em) em.textContent = t.value + (em.textContent.replace(/^[-\d.]+/, ''));
      if (/^lg/.test(ps)) { begin(); if (ps === 'lgw') doc.logo.w = v; if (ps === 'lgm') doc.logo.m = v; if (ps === 'lgop') doc.logo.op = 1 - v / 100; liveFull(); return; }
      if (!k) return;
      begin();
      if (ps === 'op') sel.forEach(function (x) { if (supports(x, 'op')) setP(x, 'op', v >= 100 ? null : v / 100); });
      else if (/^pf(tr|z|x|y)$/.test(ps)) {
        sel.forEach(function (x) {
          var q = getP(x, 'fill'); if (!supports(x, 'fill') || !q || q.m !== 'pic') return; q = D.clone(q);
          if (ps === 'pftr') q.o = Math.round(100 - v) / 100; else if (ps === 'pfz') q.z = v / 100; else if (ps === 'pfx') q.px = v; else q.py = v;
          setP(x, 'fill', q);
        });
      }
      else if (ps === 'ptr' || ps === 'lw' || ps === 'gang' || ps === 'gst') {
        var pk = pKey(pmode, k);
        sel.forEach(function (x) {
          if (!supports(x, pk)) return; var q = D.clone(getP(x, pk) || { m: 'solid' });
          if (ps === 'ptr') q.o = 1 - v / 100; if (ps === 'lw') q.w = v; if (ps === 'gang' && q.g) q.g.a = v;
          if (ps === 'gst' && q.g) q.g.s[Math.min(gSel, q.g.s.length - 1)][2] = 1 - v / 100;
          setP(x, pk, q);
        });
      } else if (/^sh\./.test(ps)) {
        var f = ps.slice(3);
        sel.forEach(function (x) { if (!supports(x, 'sh')) return; var s = D.clone(getP(x, 'sh') || SHDEF); s[f] = f === 'o' ? v / 100 : v; setP(x, 'sh', s); });
      } else if (ps === 'lh' || ps === 'ls') sel.forEach(function (x) { if (supports(x, ps)) setP(x, ps, v); });
      else if (ps === 'pad') sel.filter(isL).forEach(function (x) { obj(x).pad = v; });
      else if (ps === 'r') sel.forEach(function (x) { if (supports(x, 'r')) setP(x, 'r', v || null); });
      liveRedraw();
    }
    var liveF = 0;
    function liveFull() { if (liveF) return; liveF = requestAnimationFrame(function () { liveF = 0; env.rerender(); }); }
    function onPanelChange(ev) {
      var t = ev.target, pc = t.getAttribute('data-pc'), pi = t.getAttribute('data-pi'), k = sel[0];
      if (t.getAttribute('data-ps')) { done(); if (/^lg/.test(t.getAttribute('data-ps'))) return; paint(); return; }
      if (pc === 'lghide') { begin(); if (t.checked) delete doc.logo.hide; else doc.logo.hide = 1; env.rerender(); done(); return; }
      if (pc === 'isearch') return;
      if (!k) return;
      if (pi) {
        var v = parseFloat(t.value); if (!isFinite(v)) return;
        begin();
        var o = obj(k);
        if (isL(k)) {
          if (pi === 'pw') { if (ratioLock && o.w) o.h = Math.round(o.h * v / o.w); o.w = Math.max(1, v); }
          if (pi === 'ph') { if (ratioLock && o.h) o.w = Math.round(o.w * v / o.h); o.h = Math.max(1, v); }
          if (pi === 'px') o.x = v; if (pi === 'py') o.y = v; if (pi === 'prot') o.rot = v;
        } else if (isB(k)) {
          if (pi === 'bw') { o.w = Math.max(0.15, Math.min(1, v / 100)); if (o.w >= 1) delete o.w; }
          if (pi === 'box') { o.ox = Math.max(0, Math.min(0.85, v / 100)); if (!o.ox) delete o.ox; }
          if (pi === 'bmt') { if (v > 0) o.mt = v; else delete o.mt; }
          if (pi === 'bmb') { if (v > 0) o.mb = v; else delete o.mb; }
        }
        redraw([k]); done(); t.blur(); paintPanel(); return;
      }
      if (pc === 'behind') { begin(); sel.filter(isL).forEach(function (x) { if (t.checked) obj(x).behind = 1; else delete obj(x).behind; }); redraw(); done(); paint(); return; }
      if (pc === 'pick' || pc === 'hex') {
        var c = String(t.value).toUpperCase(); if (!/^#[0-9A-F]{6}$/.test(c)) return;
        var pk = pKey(pmode, k);
        if (!pk) setAll('tc', c); else { editPaint(k, pmode, function (q) { q.c = c; if (!q.m || q.m === 'none') q.m = 'solid'; }); done(); }
        paint(); return;
      }
      if (pc === 'gtype') { editPaint(k, pmode, function (q) { if (q.g) q.g.t = t.value; }); done(); paintPanel(); return; }
      if (pc === 'gsc') { var c2 = t.value.toUpperCase(); editPaint(k, pmode, function (q) { if (q.g) q.g.s[Math.min(gSel, q.g.s.length - 1)][1] = c2; }); done(); paintPanel(); return; }
      if (pc === 'gsp') { var v2 = Math.max(0, Math.min(100, +t.value || 0)); editPaint(k, pmode, function (q) { if (q.g) q.g.s[Math.min(gSel, q.g.s.length - 1)][0] = v2; }); done(); paintPanel(); return; }
      if (pc === 'dash') { editPaint(k, pmode, function (q) { q.d = t.value; }); done(); return; }
      if (pc === 'shon') { begin(); sel.forEach(function (x) { if (supports(x, 'sh')) setP(x, 'sh', t.checked ? D.clone(getP(x, 'sh') || SHDEF) : null); }); redraw(); done(); paint(); return; }
      if (pc === 'shc') { begin(); var c3 = t.value.toUpperCase(); sel.forEach(function (x) { var s = getP(x, 'sh'); if (s) { s = D.clone(s); s.c = c3; setP(x, 'sh', s); } }); redraw(); done(); return; }
    }

    /* ================= menus, shortcuts ================= */
    function closeMenu() { if (menuEl) { menuEl.remove(); menuEl = null; } }
    function openMenu(x, y, items) {
      closeMenu();
      menuEl = el('div'); menuEl.id = 'dsel-menu';
      menuEl.innerHTML = items.map(function (it, i) { return it === '-' ? '<i></i>' : '<button type="button" data-i="' + i + '"' + (it.dis ? ' disabled' : '') + (it.on ? ' class="dsel-on"' : '') + '><span>' + esc(it.l) + '</span>' + (it.key ? '<em>' + esc(it.key) + '</em>' : '') + '</button>'; }).join('');
      menuEl.addEventListener('click', function (ev) { var b = ev.target.closest('button[data-i]'); if (!b || b.disabled) return; var it = items[+b.getAttribute('data-i')]; closeMenu(); it.f(); });
      menuEl.addEventListener('contextmenu', function (ev) { ev.preventDefault(); });
      host.appendChild(menuEl);
      var w = menuEl.offsetWidth, hh = menuEl.offsetHeight;
      menuEl.style.left = Math.max(6, Math.min(x, window.innerWidth - w - 6)) + 'px'; menuEl.style.top = Math.max(6, Math.min(y, window.innerHeight - hh - 6)) + 'px';
    }
    function onContext(ev) {
      var t = ev.target;
      if (!(docBox.contains(t) || (ov && ov.contains(t)))) return;
      var ce = t.closest('[contenteditable="true"]'); if (ce && document.activeElement === ce && window.getSelection && String(window.getSelection())) return;   /* text is selected: the browser's own menu (spelling, copy) */
      ev.preventDefault();
      var le = t.closest('.dsd-L'), be = t.closest('.dsd-blk'), cf = t.closest('[data-df]'), hk = le ? 'L:' + (le.dataset.lid || le.dataset.glid) : cf ? 'C:' + cf.dataset.df : be ? 'B:' + be.dataset.id : (t.closest('.dsel-fr') ? t.closest('.dsel-fr').getAttribute('data-k') : null);
      if (hk && obj(hk)) { if (sel.indexOf(hk) < 0) select([hk]); } else clearSel();
      var it = [], k = sel[0];
      if (k) {
        var many = sel.length > 1, og = oneGroup(), f = famOf(k), o = obj(k);
        it.push({ l: 'Copy (paste on any page)', key: 'Ctrl+C', f: function () { copy(); } }, { l: 'Paste', key: 'Ctrl+V', dis: !CLIP.length, f: paste }, { l: 'Cut', key: 'Ctrl+X', dis: isC(k), f: cut }, { l: 'Clone (on this page)', key: 'Ctrl+D', f: duplicate }, { l: isC(k) ? 'Hide' : 'Delete', key: 'Del', f: function () { del(); } }, '-');
        if (sel.some(isL)) it.push({ l: 'Bring forward', key: 'Ctrl+]', f: function () { arrange('fwd'); } }, { l: 'Send backward', key: 'Ctrl+[', f: function () { arrange('bwd'); } }, { l: 'Bring to front', f: function () { arrange('front'); } }, { l: 'Send to back', f: function () { arrange('back'); } }, { l: o.behind ? 'In front of the text' : 'Behind the text', f: function () { setAll('behind', o.behind ? null : 1); paint(); } }, '-');
        else if (sel.some(isB)) it.push({ l: 'Move up', key: 'Ctrl+[', f: function () { arrange('fwd'); } }, { l: 'Move down', key: 'Ctrl+]', f: function () { arrange('bwd'); } }, '-');
        if (many && !og && sel.every(isL)) it.push({ l: 'Group', key: 'Ctrl+G', f: doGroup });
        if (hasGroup()) it.push({ l: 'Ungroup', key: 'Ctrl+Shift+G', f: doUngroup });
        it.push({ l: isB(k) ? 'Place...' : 'Align and distribute...', f: function () { pmode = 'align'; paint(); } });
        if (!isC(k)) it.push({ l: many ? 'Lock these elements' : (isLocked(k) ? 'Unlock' : 'Lock'), f: function () { setLock(sel.slice(), !isLocked(k)); } });
        if (!many) {
          it.push('-'); applyItems().forEach(function (x) { it.push(x); });
          if (STYLE_CLIP) it.push({ l: 'Paste effects', f: pasteFx });
          it.push('-');
          if (f === 'image') { it.push({ l: 'Change image...', f: function () { changePic(k); } }); it.push({ l: 'Crop...', f: function () { cropPic(k); } }); }
          if (f === 'chart') it.push({ l: 'Edit chart...', f: function () { chartOf(k); } });
          if (f === 'text') it.push({ l: 'Text colour...', f: function () { pmode = 'color'; paint(); } }, { l: 'Font...', f: function () { pmode = 'font'; paint(); } });
          if (supports(k, 'fill')) it.push({ l: 'Fill...', f: function () { pmode = 'fill'; paint(); } });
          if (supports(k, 'ln')) it.push({ l: 'Outline...', f: function () { pmode = 'line'; paint(); } });
          if (supports(k, 'sh')) it.push({ l: 'Shadow...', f: function () { pmode = 'shadow'; paint(); } });
        } else it.push('-');
        it.push({ l: 'Position and size...', f: function () { pmode = 'pos'; paint(); } }, { l: 'Layers...', f: function () { pmode = 'layers'; paint(); } });
      } else {
        it.push({ l: 'Paste', key: 'Ctrl+V', dis: !CLIP.length, f: paste }, { l: 'Select all on this page', key: 'Ctrl+A', f: selectAll }, '-',
          { l: 'Add text box', f: function () { addText('text'); } }, { l: 'Add element...', f: function () { pmode = 'add'; paint(); } }, { l: 'Add icon...', f: function () { pmode = 'icons'; paint(); } }, { l: 'Add picture...', f: addPicture }, { l: 'Add chart...', f: addChart }, '-',
          { l: 'Layers...', f: function () { pmode = 'layers'; paint(); } });
      }
      openMenu(ev.clientX, ev.clientY, it);
    }
    function selectAll() { var pg = viewPage(); select(keysOnPage(pg).filter(function (k) { return !isLocked(k) && !isC(k); })); }
    function closeHelp() { if (helpEl) { helpEl.remove(); helpEl = null; } }
    function showHelp() {
      if (helpEl) { closeHelp(); return; }
      helpEl = el('div'); helpEl.id = 'dsel-help';
      helpEl.innerHTML = '<div class="dsel-hbox" role="dialog" aria-modal="true"><div class="dsel-hh"><b>Keyboard shortcuts</b><button type="button" data-x="1" title="Close">&times;</button></div><div class="dsel-hb">' +
        KEYS.map(function (g) { return '<div class="dsel-hg"><h4>' + esc(g[0]) + '</h4>' + g[1].map(function (r) { return '<div class="dsel-hr"><kbd>' + esc(r[0]) + '</kbd><span>' + esc(r[1]) + '</span></div>'; }).join('') + '</div>'; }).join('') +
        '</div><p class="dsel-hn">On a Mac use Cmd in place of Ctrl.</p></div>';
      helpEl.addEventListener('click', function (ev) { if (ev.target === helpEl || ev.target.closest('[data-x]')) closeHelp(); });
      host.appendChild(helpEl);
    }
    var nudgeT = null;
    function onKey(e) {
      if (!host.isConnected) return;
      if (helpEl && e.key === 'Escape') { closeHelp(); return; }
      if (editing) { if (e.key === 'Escape') { e.preventDefault(); stopEdit(); paint(); } return; }
      var a = document.activeElement, typing = a && (a.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName));
      if (typing) {
        if (e.key === 'Escape' && a.isContentEditable && docBox.contains(a)) { a.blur(); try { window.getSelection().removeAllRanges(); } catch (x) { /* ignore */ } sync(); }
        return;
      }
      var mod = e.ctrlKey || e.metaKey, k = e.key.toLowerCase();
      if (e.key === '?') { e.preventDefault(); showHelp(); return; }
      if (k === 'escape') { if (brushOn) { brushOn = false; host.classList.remove('dsel-brush'); paint(); return; } if (menuEl) { closeMenu(); return; } if (pmode) { pmode = ''; paint(); return; } clearSel(); return; }
      if (mod && k === 'v' && CLIP.length) { e.preventDefault(); paste(); return; }
      if (mod && k === 'a') { e.preventDefault(); selectAll(); return; }
      if (!sel.length) return;
      if (pmode === 'layers' && !mod && (k === 'arrowup' || k === 'arrowdown')) { e.preventDefault(); arrange(k === 'arrowup' ? 'fwd' : 'bwd'); return; }
      if (k === 'delete' || k === 'backspace') { e.preventDefault(); del(); return; }
      if (k === 'enter' && sel.length === 1 && isL(sel[0]) && obj(sel[0]).k === 'text') { e.preventDefault(); startEdit(sel[0]); return; }
      if (k === 'enter' && sel.length === 1 && (isB(sel[0]) || isC(sel[0]))) { var ce = domOf(sel[0]); ce = ce && (ce.isContentEditable ? ce : ce.querySelector('[contenteditable]')); if (ce) { e.preventDefault(); ce.focus(); } return; }
      if (/^arrow/.test(k) && sel.some(isL)) {
        e.preventDefault(); if (!nudgeT) begin();
        var st = e.shiftKey ? 10 : 1;
        sel.forEach(function (x) { if (!isL(x) || isLocked(x)) return; var L = obj(x); if (k === 'arrowleft') L.x -= st; if (k === 'arrowright') L.x += st; if (k === 'arrowup') L.y -= st; if (k === 'arrowdown') L.y += st; });
        if (api && api.drawLayers) api.drawLayers(); sync();
        clearTimeout(nudgeT); nudgeT = setTimeout(function () { nudgeT = null; done(); }, 500);
        return;
      }
      if (!mod) return;
      if (k === 'c') { e.preventDefault(); copy(); }
      if (k === 'x') { e.preventDefault(); cut(); }
      if (k === 'd') { e.preventDefault(); duplicate(); }
      if (k === 'g') { e.preventDefault(); if (e.shiftKey) doUngroup(); else doGroup(); }
      if (k === ']') { e.preventDefault(); arrange(e.shiftKey ? 'front' : (sel.some(isL) ? 'fwd' : 'bwd')); }
      if (k === '[') { e.preventDefault(); arrange(e.shiftKey ? 'back' : (sel.some(isL) ? 'bwd' : 'fwd')); }
    }
    function onDocDown(e) { if (menuEl && !menuEl.contains(e.target)) closeMenu(); }
    /* ================= chart editor ================= */
    function editChart(ch0, save, isNew) {
      var ch = D.clone(ch0 || {});
      ch.kind = ch.kind || 'column'; ch.labels = (ch.labels || []).slice(); ch.series = (ch.series || []).map(function (s) { return { name: s.name || '', values: (s.values || []).slice(), c: s.c }; });
      if (!ch.labels.length) ch.labels = ['A', 'B', 'C'];
      if (!ch.series.length) ch.series = [{ name: 'Series 1', values: [1, 2, 3] }];
      var m = el('div', 'dd-rv dl-chm');
      function form() {
        var pie = ch.kind === 'pie' || ch.kind === 'doughnut', ser = pie ? ch.series.slice(0, 1) : ch.series;
        var h = '<div class="rv-box"><div class="rv-hd"><div><h3>' + (isNew ? 'Add a chart' : 'Edit chart') + '</h3><p>Type your numbers in the table. The chart updates as you type and uses your document colours.</p></div><button type="button" class="rv-x" data-cm="x">×</button></div>' +
          '<div class="dl-chbody"><div class="dl-chform"><label class="rv-lab">Chart type</label><div class="dl-kinds">' + D.CHART_KINDS.map(function (k) { return '<button type="button" data-kind="' + k[0] + '"' + (k[0] === ch.kind ? ' class="on"' : '') + '>' + k[1] + '</button>'; }).join('') + '</div>' +
          '<div class="rv-two"><div><label class="rv-lab">Title</label><input class="rv-in" data-cf="title" value="' + esc(ch.title || '') + '"></div><div><label class="rv-lab">Unit after numbers</label><input class="rv-in" data-cf="unit" value="' + esc(ch.unit || '') + '" placeholder="e.g. % or k"></div></div>' +
          '<label class="dl-chk"><input type="checkbox" data-cb="legend"' + (ch.legend !== false ? ' checked' : '') + '> Show legend</label><label class="dl-chk"><input type="checkbox" data-cb="vals"' + (ch.vals !== false ? ' checked' : '') + '> Show values</label>' +
          '<label class="rv-lab">Data</label><div class="rv-grid" style="grid-template-columns:minmax(90px,1.3fr) repeat(' + ser.length + ',minmax(80px,1fr))"><span class="rv-gh">Label</span>' +
          ser.map(function (s, si) { return '<span class="dl-sh"><input class="rv-in th" data-sn="' + si + '" value="' + esc(s.name) + '">' + (pie ? '' : '<input type="color" data-sc="' + si + '" value="' + D.col(s.c || D.chartColors(theme())[si % 8], theme()) + '" title="Colour">') + '</span>'; }).join('');
        ch.labels.forEach(function (l, li) { h += '<input class="rv-in" data-lb="' + li + '" value="' + esc(l) + '">' + ser.map(function (s, si) { return '<input class="rv-in num" data-v="' + si + '.' + li + '" value="' + esc(s.values[li] == null ? '' : s.values[li]) + '">'; }).join(''); });
        h += '</div><div class="rv-mini"><button type="button" data-cm="row">+ Row</button><button type="button" data-cm="delrow">− Row</button>' + (pie ? '' : '<button type="button" data-cm="ser">+ Series</button><button type="button" data-cm="delser">− Series</button>') + '</div></div>' +
          '<div class="dl-chpv" id="dl-chpv"></div></div><div class="rv-ft"><span class="rv-info"></span><button type="button" class="rv-btn" data-cm="x">Cancel</button><button type="button" class="rv-btn pri" data-cm="ok">' + (isNew ? 'Add chart' : 'Save chart') + '</button></div></div>';
        return h;
      }
      function pv() { var b = m.querySelector('#dl-chpv'); if (b) b.innerHTML = D.chartSVG(ch, theme(), 560, Math.round(560 * D.chartRatio(ch)), { bg: '#FFFFFF' }); }
      function draw() { m.innerHTML = form(); pv(); }
      document.body.appendChild(m); draw();
      m.addEventListener('input', function (e) {
        var t = e.target;
        if (t.dataset.cf) ch[t.dataset.cf] = t.value;
        if (t.dataset.sn != null) ch.series[+t.dataset.sn].name = t.value;
        if (t.dataset.sc != null) ch.series[+t.dataset.sc].c = t.value.toUpperCase();
        if (t.dataset.lb != null) ch.labels[+t.dataset.lb] = t.value;
        if (t.dataset.v) { var q = t.dataset.v.split('.'); ch.series[+q[0]].values[+q[1]] = t.value === '' ? 0 : (+String(t.value).replace(/[^\d.\-]/g, '') || 0); }
        if (t.dataset.cb) ch[t.dataset.cb] = t.checked;
        pv();
      });
      m.addEventListener('click', function (e) {
        var b = e.target.closest('[data-kind],[data-cm]'); if (!b) return;
        if (b.dataset.kind) { ch.kind = b.dataset.kind; draw(); return; }
        var c = b.dataset.cm;
        if (c === 'x') { m.remove(); return; }
        if (c === 'ok') { ch.labels = ch.labels.map(String); m.remove(); save(ch); return; }
        if (c === 'row') { ch.labels.push(''); ch.series.forEach(function (s) { s.values.push(0); }); }
        if (c === 'delrow' && ch.labels.length > 1) { ch.labels.pop(); ch.series.forEach(function (s) { s.values.pop(); }); }
        if (c === 'ser' && ch.series.length < 6) ch.series.push({ name: 'Series ' + (ch.series.length + 1), values: ch.labels.map(function () { return 0; }) });
        if (c === 'delser' && ch.series.length > 1) ch.series.pop();
        draw();
      });
    }


    /* ================= wiring ================= */
    docBox.addEventListener('pointerdown', onDown, true);
    docBox.addEventListener('dblclick', onDbl, true);
    canvas.addEventListener('pointerdown', onCanvasDown);
    canvas.addEventListener('contextmenu', onContext);
    document.addEventListener('pointermove', onMove);
    document.addEventListener('pointerup', onUp);
    document.addEventListener('keydown', onKey);
    document.addEventListener('pointerdown', onDocDown, true);
    window.addEventListener('resize', place);
    ensureBar(); paint();

    return {
      onRender: function (a) {
        api = a; bpCache = null;
        if (api && api.drawLayers && !api.__le) {
          var orig = api.drawLayers; api.__le = 1;
          api.drawLayers = function () { if (editing) return; orig(); };
        }
        sync(); paint();
      },
      sync: function () { sync(); },
      place: place,
      paint: paint,
      clear: function () { if (editing) stopEdit(); if (sel.length) { sel = []; selChanged(); } closeMenu(); },
      busy: function () { return !!(drag || editing); },
      selected: function () { return sel.slice(); },
      selectBlock: function (id) { if (bById(id)) { sel = ['B:' + id]; selChanged(); } },
      editChart: editChart,
      addChart: addChart,
      destroy: function () {
        document.removeEventListener('pointermove', onMove); document.removeEventListener('pointerup', onUp);
        document.removeEventListener('keydown', onKey); document.removeEventListener('pointerdown', onDocDown, true);
        window.removeEventListener('resize', place);
        closeMenu(); closeHelp();
      }
    };
  };
})();

/* DesignStager AI Document — page & editor. Needs window.DSDOC (engine) and window.DSDOC_CFG. */
(function () {
  'use strict';
  var D = window.DSDOC, CFG = window.DSDOC_CFG || {};
  if (!D) return;
  var root = document.getElementById('dsdoc-app');
  if (!root) return;
  D.injectCSS();

  /* ---------------- helpers ---------------- */
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function el(tag, cls, html) { var e = document.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; }
  var esc = D.esc;
  var toastT;
  function toast(msg, kind, ms) {
    var t = $('.dd-toast') || document.body.appendChild(el('div', 'dd-toast'));
    t.className = 'dd-toast ' + (kind || ''); t.textContent = msg; t.style.display = 'block';
    clearTimeout(toastT); toastT = setTimeout(function () { t.style.display = 'none'; }, ms || 3800);
  }
  function debounce(fn, ms) { var t; return function () { var a = arguments, s = this; clearTimeout(t); t = setTimeout(function () { fn.apply(s, a); }, ms); }; }
  function api(method, path, body) {
    var url = CFG.rest + path.replace(/^\//, '');
    var h = { 'Content-Type': 'application/json' };
    if (CFG.nonce) h['X-WP-Nonce'] = CFG.nonce;
    if (CFG.token) h['X-DSW-Token'] = CFG.token;
    return fetch(url, { method: method, credentials: 'same-origin', headers: h, body: body ? JSON.stringify(body) : undefined })
      .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, status: r.status, j: j }; }); })
      .then(function (x) {
        var j = x.j || {};
        if (!x.ok || j.success === false || (j.error && !j.success && !j.items && !j.images && !j.prediction_id && !j.status)) {
          var e = new Error(j.message || (typeof j.error === 'string' ? j.error : '') || 'Something went wrong (' + x.status + ').');
          e.status = x.status; e.data = j; throw e;
        }
        return j;
      });
  }
  function setCredits(v) { if (v == null || isNaN(+v)) return; S.credits = +v; $$('[data-credits]').forEach(function (n) { n.textContent = (Math.round(S.credits * 100) / 100).toLocaleString(); }); updateCost(); }
  function today() { return new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }); }
  function stripIds(blocks) { return (blocks || []).map(function (b) { var c = D.clone(b); delete c.id; delete c.src; return c; }); }
  function ensureIds(blocks) { (blocks || []).forEach(function (b) { if (!b.id) b.id = D.uid(); }); return blocks; }

  /* ---------------- state ---------------- */
  var LANGS = CFG.langs || ['English', 'Hindi', 'Kannada', 'Tamil', 'Telugu', 'Marathi', 'Bengali', 'Gujarati', 'Malayalam', 'Spanish', 'French', 'German', 'Portuguese', 'Italian', 'Arabic', 'Japanese', 'Chinese'];
  var TONES = ['Professional', 'Friendly', 'Persuasive', 'Academic', 'Simple and clear', 'Formal', 'Inspiring'];
  var S = { mode: 'ai', type: 'report', tpl: 'meridian', theme: '', pages: 6, lang: 'English', tone: 'Professional', size: CFG.size || 'A4', credits: +CFG.credits || 0, group: 'auto', docs: [], docsNext: 0, docsTotal: 0 };
  try { var saved = JSON.parse(localStorage.getItem('dsdoc_prefs') || '{}'); ['type', 'tpl', 'theme', 'lang', 'tone', 'size', 'pages'].forEach(function (k) { if (saved[k] != null) S[k] = saved[k]; }); } catch (e) { /* storage blocked */ }
  function savePrefs() { try { localStorage.setItem('dsdoc_prefs', JSON.stringify({ type: S.type, tpl: S.tpl, theme: S.theme, lang: S.lang, tone: S.tone, size: S.size, pages: S.pages })); } catch (e) { /* ignore */ } }
  if (D.design(S.tpl).types.indexOf(S.type) < 0) S.tpl = D.designsFor(S.type)[0].id;

  /* ---------------- My brand: your logo, colours and fonts, saved to your account and used on every new design ---------------- */
  var BRAND = CFG.brand || null;
  function brandTheme(b) {
    var p = b.p, s2 = b.s || D.mix(p, '#FFFFFF', 0.3), a = b.a || s2;
    return { id: 'my-brand', name: b.name || 'My brand', p: p, s: s2, a: a, ink: '#1C1E26', mute: '#5E6573', tint: D.mix(p, '#FFFFFF', 0.94), line: D.mix(p, '#FFFFFF', 0.82), head: b.head || 'Aptos Display', body: b.body || b.head || 'Aptos', mine: 1 };
  }
  function registerBrand(b) {
    for (var i = D.THEMES.length - 1; i >= 0; i--) if (D.THEMES[i].id === 'my-brand') D.THEMES.splice(i, 1);
    BRAND = b && b.p ? b : null;
    if (BRAND) D.THEMES.unshift(brandTheme(BRAND));
    D.myBrand = BRAND;
  }
  registerBrand(BRAND);
  if (BRAND && BRAND.auto) S.theme = 'my-brand';
  else if (S.theme === 'my-brand' && !BRAND) S.theme = '';
  /* a new design gets the brand logo when the brand is used */
  function brandLogo(doc) {
    if (BRAND && BRAND.logo && (BRAND.auto || S.theme === 'my-brand') && !(doc.logo && doc.logo.src)) doc.logo = { src: BRAND.logo };
    return doc;
  }
  var FONTS0 = ['Aptos', 'Aptos Display', 'Arial', 'Calibri', 'Cambria', 'Georgia', 'Helvetica', 'Segoe UI', 'Verdana', 'Trebuchet MS', 'Century Gothic', 'Gill Sans MT', 'Bahnschrift', 'Rockwell', 'Bodoni MT', 'Garamond', 'Times New Roman', 'Poppins', 'Montserrat', 'Roboto', 'Open Sans', 'Lato', 'Inter', 'Raleway', 'Nunito', 'Playfair Display', 'Merriweather'];
  function fontList(extra) {
    var seen = {}, out = [];
    (extra || []).concat(FONTS0, D.THEMES.map(function (t) { return t.head; }), D.THEMES.map(function (t) { return t.body; })).forEach(function (f) { if (f && !seen[f.toLowerCase()]) { seen[f.toLowerCase()] = 1; out.push(f); } });
    return out;
  }
  /* pictures are made smaller in the browser (an SVG logo becomes a PNG), so the saved logo stays light */
  function shrinkLogo(src, cb) {
    var im = new Image();
    im.onload = function () {
      var w = im.naturalWidth || 600, h = im.naturalHeight || 300, k = Math.min(1, 700 / Math.max(w, h));
      var tries = [k, k * 0.75, k * 0.55, k * 0.4];
      for (var i = 0; i < tries.length; i++) {
        var c = document.createElement('canvas'); c.width = Math.max(8, Math.round(w * tries[i])); c.height = Math.max(8, Math.round(h * tries[i]));
        c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
        var d = '';
        try { d = c.toDataURL('image/png'); if (d.length > 520000) d = c.toDataURL('image/webp', 0.9); } catch (er) { cb(''); return; }
        if (d.length <= 520000 && /^data:image\/(png|webp)/.test(d)) { cb(d); return; }
      }
      cb('');
    };
    im.onerror = function () { cb(''); };
    im.src = src;
  }
  function openBrand() {
    var old = $('.dd-gal', root); if (old) old.remove();
    var W = Object.assign({ name: 'My brand', p: '#1F3864', s: '#2E74B5', a: '#F2B134', head: 'Aptos Display', body: 'Aptos', logo: '', site: '', auto: 1 }, BRAND || {});
    var found = { colors: [], fonts: [] }, slot = 'p';
    var m = el('div', 'dd-gal');
    m.innerHTML = '<div class="dd-gbox dd-bk" role="dialog" aria-modal="true"><div class="dd-ghd"><div><h3>My brand</h3><p>Your logo, colours and fonts. Saved to your account as a personal theme and used on every new design.</p></div><button type="button" class="dd-gx" data-b="x" title="Close">×</button></div>' +
      '<div class="dd-bkbody"><div class="dd-bkform">' +
      '<label class="dd-l">Your website (optional)</label><div class="dd-bkrow"><input type="url" id="bk-site" placeholder="www.yourcompany.com"><button type="button" class="dd-bkbtn" data-b="site">Get logo &amp; colours</button></div>' +
      '<div class="dd-bkfound" id="bk-found"></div>' +
      '<label class="dd-l">Logo</label><div class="dd-bklogo"><div class="lg" id="bk-lg"></div><div><button type="button" class="dd-bkbtn" data-b="up">Upload logo</button> <button type="button" class="dd-bkbtn ghost" data-b="nolg">Remove</button><input type="file" id="bk-file" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="dd-hide"><small>PNG with a see-through background works best. SVG is turned into a PNG.</small></div></div>' +
      '<label class="dd-l">Brand colours <small>(click a slot, then a colour found on your website — or pick one)</small></label><div class="dd-bkcols">' +
      [['p', 'Main'], ['s', 'Second'], ['a', 'Accent']].map(function (x) { return '<label class="sl" data-slot="' + x[0] + '"><input type="color" data-c="' + x[0] + '"><span>' + x[1] + '</span></label>'; }).join('') + '</div>' +
      '<div class="dd-row2"><div><label class="dd-l">Heading font</label><select id="bk-head"></select></div><div><label class="dd-l">Text font</label><select id="bk-body"></select></div></div>' +
      '<label class="dd-l">Name</label><input type="text" id="bk-name" maxlength="40">' +
      '<label class="dd-bkauto"><input type="checkbox" id="bk-auto"> Use my brand on every new design (logo, colours and fonts)</label>' +
      '</div><div class="dd-bkpv"><div class="hd">Preview</div><div id="bk-pv"></div></div></div>' +
      '<div class="dd-bkft">' + (BRAND ? '<button type="button" class="dd-bkbtn ghost" data-b="del">Delete my brand</button>' : '') + '<span class="dd-sp"></span><button type="button" class="dd-bkbtn ghost" data-b="x">Cancel</button><button type="button" class="dd-bkbtn pri" data-b="save">Save as personal</button></div></div>';
    root.appendChild(m); document.body.classList.add('dd-noscroll');
    function close() { m.remove(); document.removeEventListener('keydown', key); document.body.classList.remove('dd-noscroll'); }
    function key(e) { if (e.key === 'Escape') close(); }
    document.addEventListener('keydown', key);
    function fonts() {
      var fl = fontList(found.fonts.concat([W.head, W.body]));
      ['head', 'body'].forEach(function (k) { $('#bk-' + k, m).innerHTML = fl.map(function (f) { return '<option' + (f === W[k] ? ' selected' : '') + '>' + esc(f) + '</option>'; }).join(''); });
    }
    function paint() {
      $$('[data-c]', m).forEach(function (i) { i.value = /^#[0-9a-f]{6}$/i.test(W[i.dataset.c]) ? W[i.dataset.c] : '#888888'; });
      $$('.sl', m).forEach(function (l) { l.classList.toggle('on', l.dataset.slot === slot); });
      $('#bk-lg', m).innerHTML = W.logo ? '<img alt="Logo" src="' + esc(W.logo) + '">' : '<span>No logo yet</span>';
      $('#bk-found', m).innerHTML = found.colors.length ? '<span>Found on your website:</span>' + found.colors.map(function (c) { return '<button type="button" class="fc" data-fc="' + c + '" style="background:' + c + '" title="' + c + '"></button>'; }).join('') : '';
      var t = brandTheme(W), pv = $('#bk-pv', m);
      pv.innerHTML = '<div class="bkcard" style="background:' + t.tint + ';border-top:8px solid ' + t.p + '">' + (W.logo ? '<img alt="" src="' + esc(W.logo) + '">' : '') +
        '<b style="color:' + t.p + ';font-family:' + esc(t.head) + ',sans-serif">' + esc(W.name || 'My brand') + '</b><i style="font-family:' + esc(t.body) + ',sans-serif;color:' + t.mute + '">Your headings use ' + esc(t.head) + ' and your text uses ' + esc(t.body) + '.</i>' +
        '<div class="bar"><em style="background:' + t.p + '"></em><em style="background:' + t.s + '"></em><em style="background:' + t.a + '"></em></div><span class="cta" style="background:' + t.a + ';color:' + D.onColor(t.a) + '">Call to action</span></div>';
    }
    $('#bk-site', m).value = W.site || ''; $('#bk-name', m).value = W.name || 'My brand'; $('#bk-auto', m).checked = !!W.auto;
    fonts(); paint();
    m.addEventListener('input', function (e) {
      var c = e.target.dataset && e.target.dataset.c; if (c) { W[c] = e.target.value.toUpperCase(); slot = c; paint(); }
      if (e.target.id === 'bk-name') { W.name = e.target.value; paint(); }
    });
    m.addEventListener('change', function (e) {
      if (e.target.id === 'bk-head' || e.target.id === 'bk-body') { W[e.target.id.slice(3)] = e.target.value; paint(); }
      if (e.target.id === 'bk-auto') W.auto = e.target.checked ? 1 : 0;
      if (e.target.id === 'bk-file' && e.target.files[0]) {
        var f = e.target.files[0], rd = new FileReader();
        if (f.size > 8 * 1024 * 1024) { toast('That picture is too big. Please use a logo under 8 MB.', 'err'); return; }
        rd.onload = function () { shrinkLogo(rd.result, function (d) { if (!d) { toast('That picture could not be used as a logo. Try a PNG or JPG.', 'err'); return; } W.logo = d; paint(); }); };
        rd.readAsDataURL(f); e.target.value = '';
      }
    });
    m.addEventListener('click', function (e) {
      if (e.target === m) { close(); return; }
      var sl = e.target.closest('.sl'); if (sl) { slot = sl.dataset.slot; paint(); }
      var fc = e.target.closest('[data-fc]'); if (fc) { W[slot] = fc.dataset.fc; slot = slot === 'p' ? 's' : slot === 's' ? 'a' : 'a'; paint(); return; }
      var b = e.target.closest('[data-b]'); if (!b) return;
      var a = b.dataset.b;
      if (a === 'x') close();
      if (a === 'up') $('#bk-file', m).click();
      if (a === 'nolg') { W.logo = ''; paint(); }
      if (a === 'site') {
        var u = $('#bk-site', m).value.trim(); if (!u) { toast('Type your website address first.', 'err'); return; }
        b.disabled = true; b.textContent = 'Reading your website…';
        api('POST', 'ds/v1/doc-brand-site', { url: u }).then(function (r) {
          found.colors = r.colors || []; found.fonts = r.fonts || []; W.site = r.site || u;
          if (found.colors[0]) W.p = found.colors[0];
          if (found.colors[1]) W.s = found.colors[1];
          if (found.colors[2]) W.a = found.colors[2];
          if (found.fonts[0]) { W.head = found.fonts[0]; W.body = found.fonts[1] || found.fonts[0]; }
          if (r.logo) W.logo = r.logo;
          if (r.name && (!W.name || W.name === 'My brand')) { W.name = r.name; $('#bk-name', m).value = r.name; }
          fonts(); paint();
          toast('Found ' + found.colors.length + ' colour' + (found.colors.length === 1 ? '' : 's') + (r.logo ? ', your logo' : '') + (found.fonts.length ? ' and ' + found.fonts.length + ' font' + (found.fonts.length === 1 ? '' : 's') : '') + '. Check them, then press Save.', 'ok', 6000);
        }).catch(function (er) { toast(er.message || 'We could not read that website.', 'err', 6000); })
          .then(function () { b.disabled = false; b.innerHTML = 'Get logo &amp; colours'; });
      }
      if (a === 'del') {
        api('POST', 'ds/v1/doc-brand', { clear: 1 }).then(function () {
          registerBrand(null); if (S.theme === 'my-brand') S.theme = ''; close(); refreshAll(); toast('Your brand was deleted.', 'ok');
        }).catch(function (er) { toast(er.message || 'Could not delete. Please try again.', 'err'); });
      }
      if (a === 'save') {
        W.name = $('#bk-name', m).value.trim() || 'My brand';
        b.disabled = true; b.textContent = 'Saving…';
        api('POST', 'ds/v1/doc-brand', { brand: W }).then(function (r) {
          registerBrand(r.brand); S.theme = 'my-brand'; close(); refreshAll();
          toast('Saved. "' + (r.brand.name || 'My brand') + '" is now at the top of your themes' + (r.brand.auto ? ' and is used on every new design.' : '.'), 'ok', 6000);
        }).catch(function (er) { toast(er.message || 'Could not save. Please try again.', 'err'); b.disabled = false; b.textContent = 'Save as personal'; });
      }
    });
  }

  /* ---------------- mini previews ---------------- */
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting && e.target.__draw) { var f = e.target.__draw; e.target.__draw = null; io.unobserve(e.target); f(); } });
  }, { rootMargin: '200px' }) : null;
  /** A scaled preview of a document's first page(s) inside box (lazy). */
  function preview(box, doc, w, opts) {
    opts = opts || {};
    var draw = function () {
      box.innerHTML = '';
      var inner = el('div'); box.appendChild(inner);
      var sz = D.size(doc), sc = (w || box.clientWidth || 130) / sz.w;
      var api2 = D.render(inner, doc, { edit: false, gap: opts.gap || 0, images: opts.images });
      inner.style.width = sz.w + 'px'; inner.style.transform = 'scale(' + sc + ')';
      if (opts.pages) { var r = api2.root; r.style.flexDirection = 'row'; r.style.alignItems = 'flex-start'; }
    };
    if (io && !opts.now) { box.__draw = draw; io.observe(box); } else draw();
  }
  /* the soft background behind a thumbnail: a different pattern for neighbouring designs */
  function mkBack(c1, c2, c3, id) {
    var h = 0; String(id || '').split('').forEach(function (ch) { h = (h * 31 + ch.charCodeAt(0)) >>> 0; });
    var P = [
      'linear-gradient(135deg,' + c1 + ',' + c2 + ')',
      'radial-gradient(circle at 28% 22%,' + c2 + ',' + c1 + ' 72%)',
      'radial-gradient(rgba(15,23,42,.10) 1.4px,transparent 1.6px) 0 0/12px 12px,linear-gradient(160deg,' + c1 + ',' + c2 + ')',
      'repeating-linear-gradient(45deg,rgba(255,255,255,.38) 0 6px,transparent 6px 15px),linear-gradient(135deg,' + c1 + ',' + c2 + ')',
      'linear-gradient(rgba(15,23,42,.06) 1px,transparent 1px) 0 0/14px 14px,linear-gradient(90deg,rgba(15,23,42,.06) 1px,transparent 1px) 0 0/14px 14px,' + c1,
      'linear-gradient(112deg,' + c1 + ' 0 56%,' + c2 + ' 56%)',
      'radial-gradient(circle at 86% 14%,' + c3 + ' 0 24%,transparent 24.5%),radial-gradient(circle at 8% 92%,rgba(255,255,255,.55) 0 20%,transparent 20.5%),' + c1,
      'repeating-radial-gradient(circle at 0 100%,transparent 0 13px,rgba(255,255,255,.4) 13px 15px),linear-gradient(135deg,' + c1 + ',' + c2 + ')',
      'conic-gradient(from 200deg at 70% 40%,' + c1 + ',' + c2 + ',' + c1 + ')',
      'linear-gradient(180deg,' + c2 + ' 0 38%,' + c1 + ' 38%)'
    ];
    return P[h % P.length];
  }
  /** Design thumbnails as mockups: the page (and the page behind it) on a soft colour background with a shadow; they lift on hover. */
  function mockup(box, doc, dz) {
    var draw = function () {
      box.innerHTML = ''; box.classList.add('dd-mk');
      var t = D.look(doc), sz = D.size(doc), bw = box.clientWidth || 200, bh = box.clientHeight || 150, n = 1;
      box.style.background = mkBack(D.mix(t.p, '#FFFFFF', 0.86), D.mix(t.a || t.p, '#FFFFFF', 0.7), D.mix(t.p, '#FFFFFF', 0.72), dz.id);
      try { if (dz.kind === 'panels') n = Math.min(2, D.panelSpec(doc, t, dz, sz).pages.length); else if (dz.cover || D.type(doc.type).pages > 1) n = 2; } catch (er) { n = 1; }
      var land = sz.w > sz.h * 1.05, pw, ph;
      if (n > 1 && land) { ph = bh * 0.42; pw = ph * sz.w / sz.h; if (pw > bw * 0.74) { pw = bw * 0.74; ph = pw * sz.h / sz.w; } }
      else { ph = bh * (n > 1 ? 0.8 : 0.86); pw = ph * sz.w / sz.h; var mx = bw * (n > 1 ? 0.46 : 0.8); if (pw > mx) { pw = mx; ph = pw * sz.h / sz.w; } }
      var k = pw / sz.w, front = null;
      for (var i = 0; i < n; i++) {
        var pg = el('div', 'mkp' + (i ? ' mkb' : '')); pg.style.width = pw + 'px'; pg.style.height = ph + 'px';
        if (n === 1) { pg.style.left = (bw - pw) / 2 + 'px'; pg.style.top = (bh - ph) / 2 + 'px'; }
        else if (land) { pg.style.left = ((bw - pw) / 2 + (i ? pw * 0.07 : -pw * 0.07)) + 'px'; pg.style.top = (i ? bh * 0.52 : bh * 0.07) + 'px'; }
        else { pg.style.left = (i ? bw / 2 - pw * 0.08 : bw / 2 - pw * 0.92) + 'px'; pg.style.top = ((bh - ph) / 2 + (i ? -5 : 5)) + 'px'; }
        var inner;
        if (!front) { inner = el('div'); pg.appendChild(inner); box.appendChild(pg); D.render(inner, doc, { edit: false, gap: 0 }); front = inner; }
        else { inner = front.cloneNode(true); pg.appendChild(inner); box.insertBefore(pg, box.firstChild); }
        inner.style.cssText = 'position:absolute;left:0;top:0;width:' + sz.w + 'px;transform-origin:0 0;transform:scale(' + k + ') translateY(' + (-i * sz.h) + 'px)';
      }
    };
    if (io) { box.__draw = draw; io.observe(box); } else draw();
  }
  /* sample photos for the design thumbnails: free pictures from the marketplace (like the AI Presentation templates) */
  var photoPool = null;
  function loadPhotoPool() {
    if (photoPool) return photoPool;
    var qs = ['business', 'office team', 'businessman', 'travel'];
    photoPool = Promise.all(qs.map(function (q) { return api('GET', 'ds/v1/mkt-slide-images?s=' + encodeURIComponent(q) + '&cat=images&per_page=12&page=1').then(function (r) { return r.items || []; }).catch(function () { return []; }); })).then(function (all) {
      var seen = {}, out = [];
      all.forEach(function (list) { list.forEach(function (it) { if (it && (it.thumb || it.full) && (!it.tier || it.tier === 'free') && !seen[it.id]) { seen[it.id] = 1; out.push(it.thumb || it.full); } }); });
      /* keep only pictures that load, and small ones first: the thumbnails stay quick (like the presentation templates) */
      function probe(u) { return new Promise(function (res) { var im = new Image(), done = false, tm = setTimeout(function () { if (!done) { done = true; res(null); } }, 7000); im.onload = function () { if (done) return; done = true; clearTimeout(tm); res({ u: u, w: im.naturalWidth || 0 }); }; im.onerror = function () { if (done) return; done = true; clearTimeout(tm); res(null); }; im.src = u; }); }
      return Promise.all(out.slice(0, 40).map(probe)).then(function (r) {
        var ok = r.filter(Boolean), small = ok.filter(function (x) { return x.w <= 1600; }), use = (small.length >= 6 ? small : ok).map(function (x) { return x.u; });
        if (use.length) { D.samplePhotos = use; try { drawDesigns(); drawPreview(); } catch (er) { /* the start view is not shown */ } }
        return use;
      });
    });
    return photoPool;
  }
  function sampleFor(type, tpl, theme) {
    var d = D.sample(type); d.tpl = tpl; d.theme = theme || ''; d.size = S.size; d.lang = S.lang;
    if (BRAND && BRAND.logo && (BRAND.auto || theme === 'my-brand')) d.logo = { src: BRAND.logo }; /* the thumbnails show your own logo too */
    return d;
  }

  /* ---------------- create view ---------------- */
  var groups = [];
  D.TYPES.forEach(function (t) { if (groups.indexOf(t.group) < 0) groups.push(t.group); });

  function typeOptions() {
    return groups.map(function (g) {
      return '<optgroup label="' + esc(g) + '">' + D.TYPES.filter(function (t) { return t.group === g && (S.mode !== 'ai' || t.ai); }).map(function (t) {
        return '<option value="' + t.id + '"' + (t.id === S.type ? ' selected' : '') + '>' + esc(t.name) + '</option>';
      }).join('') + '</optgroup>';
    }).join('');
  }

  var IC = {
    bulb: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.3 1 2.1V17h6v-.2c0-.8.4-1.6 1-2.1A7 7 0 0 0 12 2z"/></svg>',
    sliders: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/></svg>',
    folder: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>',
    clip: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.5l-8.5 8.5a6 6 0 0 1-8.5-8.5l9-9a4 4 0 0 1 5.7 5.7l-9 9a2 2 0 0 1-2.8-2.8l8.5-8.5"/></svg>',
    spark: '<svg width="18" height="18" viewBox="0 0 24 24" fill="#fbbf24"><path d="M12 2l1.8 5.4L19 9l-5.2 1.6L12 16l-1.8-5.4L5 9l5.2-1.6zM19 14l.9 2.6L22 17.5l-2.1.9L19 21l-.9-2.6L16 17.5l2.1-.9z"/></svg>',
    trash: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>',
    clock: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>'
  };
  root.innerHTML =
    '<div class="dd-grid"><div>' +
    '<div class="dd-card"><h3><span class="ic">' + IC.bulb + '</span>Document Prompt &amp; Structure</h3>' +
    '<div class="dd-modes">' +
    '<button type="button" class="dd-mode" data-mode="ai"><b>✨ Write with AI</b><span>AI writes the whole document, then you review the text before it is designed</span></button>' +
    '<button type="button" class="dd-mode" data-mode="preserve"><b>✎ Use My Text</b><span>Keep your exact words — AI only lays them out and formats them</span></button></div>' +
    '<label class="dd-l" style="margin-top:0">Document type</label><select id="dd-type"></select>' +
    '<label class="dd-l" id="dd-topic-l">What is your document about?</label>' +
    '<div class="dd-box"><textarea id="dd-topic" maxlength="60000" placeholder="E.g. A business plan for an online bakery in Bengaluru with delivery and catering"></textarea>' +
    '<div class="dd-tbar"><button type="button" class="dd-up" data-act="upload">' + IC.clip + ' Upload PDF, Word or text</button><input type="file" id="dd-file" accept=".txt,.md,.docx,.pdf" class="dd-hide"><button type="button" class="dd-up dd-lib dd-hide" data-act="linkedin" id="dd-lib"><span class="inb">in</span> Fill from LinkedIn</button><span class="dd-sp"></span><button type="button" class="dd-uai dd-hide" id="dd-uai" role="switch" aria-checked="true"><span class="tr" aria-hidden="true"></span><span>Use AI</span></button><span id="dd-count">0 / 3000 chars</span></div></div>' +
    '<div class="dd-box dd-dir" id="dd-dir-wrap"><div class="hd"><i>' + IC.sliders + '</i>Specific AI Directives (Optional)</div><textarea id="dd-dir" maxlength="800" placeholder="E.g. Add a pricing table · Include a 12-month timeline · Show the budget as a chart"></textarea></div>' +
    '<div class="dd-row"><div><label class="dd-l">Pages</label><select id="dd-pages"></select></div><div><label class="dd-l">Language</label><select id="dd-lang">' +
    LANGS.map(function (l) { return '<option' + (l === S.lang ? ' selected' : '') + '>' + esc(l) + '</option>'; }).join('') + '</select></div>' +
    '<div><label class="dd-l">Tone</label><select id="dd-tone">' + TONES.map(function (l) { return '<option' + (l === S.tone ? ' selected' : '') + '>' + esc(l) + '</option>'; }).join('') + '</select></div>' +
    '<div><label class="dd-l">Page size</label><select id="dd-size"></select></div></div>' +
    '<div class="dd-gobar"><div class="info"><div class="dd-cost" id="dd-cost"></div></div><button type="button" class="dd-go" id="dd-go">' + IC.spark + ' Generate Document</button></div>' +
    '<div class="dd-hint" id="dd-hint"></div></div></div>' +
    '<div><div class="dd-card"><h3>Colour &amp; Typography <span class="req">Required</span><button type="button" class="lnk" data-act="allthemes">Explore ' + D.THEMES.filter(function (x) { return !x.hide; }).length + ' Themes ›</button></h3><div class="dd-sub">Cohesive colour palettes and font styling</div><div class="dd-themes" id="dd-themes"></div></div>' +
    '<div class="dd-card"><h3>Document Designs <span class="req">Required</span><button type="button" class="lnk" data-act="alldesigns">View All (' + D.DESIGNS.length + ') ›</button></h3><div class="dd-sub">Select the layout and look of your document</div>' +
    '<div class="dd-chips" id="dd-chips"></div><div class="dd-designs" id="dd-designs"></div>' +
    '<div class="dd-pvbox"><div class="hd">Design preview <span id="dd-pvname"></span></div><div class="dd-preview" id="dd-preview"></div></div>' +
    '<div class="dd-note">Pick a theme above to recolour this design (colours + fonts), or keep "Design colours". Every design downloads as a real Word document.</div></div></div></div>' +
    '<div class="dd-sep"></div><div id="dd-mydocs-card"><div class="dd-mh"><span class="ib">' + IC.folder + '</span><h2>My Documents</h2><span class="n" id="dd-ndocs">0 Documents</span>' +
    '<span class="srch"><input type="search" id="dd-dsearch" placeholder="Search documents…"></span><select id="dd-dsort"><option value="mod">Last modified</option><option value="title">Title A–Z</option></select></div>' +
    '<div class="dd-mydocs" id="dd-mydocs"><div class="dd-empty">Loading…</div></div>' +
    '<button type="button" class="dd-more dd-hide" id="dd-more">Load more</button></div>';

  var elType = $('#dd-type'), elTopic = $('#dd-topic'), elPages = $('#dd-pages');
  function sizeList(type, tpl) { var dz = D.design(tpl); return dz.sizes || (D.sizesFor ? D.sizesFor(type) : ['A4', 'Letter']); }
  function sizeOptions(type, tpl) { return sizeList(type, tpl).map(function (k) { return '<option value="' + k + '">' + esc((D.SIZES[k] && D.SIZES[k].label) || k) + '</option>'; }).join(''); }
  function fillSizes() { var e = $('#dd-size'); e.innerHTML = sizeOptions(S.type, S.tpl); e.value = D.size({ type: S.type, tpl: S.tpl, size: S.size }).key || 'A4'; }
  window.__ddFillSizes = fillSizes;
  fillSizes();

  function fillPages() {
    var t = D.type(S.type), html = '';
    if (t.fixed) html = '<option value="1">' + (t.pages > 1 ? 'About ' + t.pages + ' pages' : '1 page') + '</option>';
    else for (var i = 2; i <= (CFG.maxPages || 20); i++) html += '<option value="' + i + '"' + (i === +S.pages ? ' selected' : '') + '>' + i + ' pages</option>';
    elPages.innerHTML = html; elPages.disabled = !!t.fixed;
    if (!t.fixed && (S.pages < 2 || S.pages > (CFG.maxPages || 20))) { S.pages = t.pages; elPages.value = S.pages; }
  }
  function setMode(m) {
    S.mode = m;
    $$('.dd-mode', root).forEach(function (b) { b.classList.toggle('on', b.dataset.mode === m); });
    elType.innerHTML = typeOptions();
    if (!elType.value) { S.type = D.TYPES.filter(function (t) { return m !== 'ai' || t.ai; })[0].id; elType.innerHTML = typeOptions(); }
    $('#dd-topic-l').textContent = m === 'preserve' ? 'Paste your text (we keep your words)' : 'What is your document about?';
    elTopic.placeholder = m === 'preserve' ? 'Paste the text of your document here, or upload a file…' : 'E.g. A business plan for an online bakery in Bengaluru with delivery and catering';
    $('#dd-dir-wrap').classList.toggle('dd-hide', m !== 'ai');
    $('#dd-uai').classList.toggle('dd-hide', m !== 'preserve');
    showLi();
    paintUseAi();
    updateCount(); updateCost();
  }
  /* "Use AI" switch on Use My Text (same as AI Presentation): off = no AI at all, your words are placed into the design as you typed them */
  if (S.useAi == null) S.useAi = !CFG.useAiFree;
  function useAiOn() { return S.mode === 'preserve' && S.useAi && !CFG.useAiFree; }
  function paintUseAi() {
    var b = $('#dd-uai'); if (!b) return;
    var on = S.useAi && !CFG.useAiFree;
    b.setAttribute('aria-checked', on ? 'true' : 'false');
    b.title = CFG.useAiFree ? 'Use AI is part of the paid plans. Your text is laid out by its headings, lists and line breaks.'
      : on ? 'AI arranges your exact words. Turn off to place your text by its headings, lists and line breaks, with no AI.'
        : 'AI is off: your text is placed by its headings, lists and line breaks, word for word.';
    $('#dd-topic-l').textContent = S.mode !== 'preserve' ? 'What is your document about?' : on ? 'Paste your text (we keep your words)' : 'Paste your text (no AI — placed exactly as you type it)';
  }
  function showLi() { var b = $('#dd-lib'); if (b) b.classList.toggle('dd-hide', S.type !== 'business_card'); }
  function updateCount() {
    var max = S.mode === 'preserve' ? 60000 : 3000;
    elTopic.maxLength = max;
    $('#dd-count').textContent = elTopic.value.length.toLocaleString() + ' / ' + max.toLocaleString() + ' chars';
  }
  /* cost: same formula as the server (asks the server when it can) */
  var costSeq = 0;
  function localCost(pages) {
    var p = CFG.pricing || {}, base = +p.base || 5, baseN = +p.baseN || 5, extra = p.extra != null ? +p.extra : 1;
    return pages <= baseN ? base : Math.round(base + (pages - baseN) * extra);
  }
  function pagesForCost() {
    var t = D.type(S.type);
    if (S.mode === 'preserve' && !t.fixed) { var w = (elTopic.value.match(/\S+/g) || []).length; return Math.max(1, Math.ceil(w / 380)); }
    return t.fixed ? 1 : +S.pages;
  }
  function updateCost() {
    var box = $('#dd-cost'); if (!box) return;
    var dz = D.design(S.tpl), th = S.theme ? D.theme(S.theme).name : 'design colours';
    var n = pagesForCost(), c = localCost(n), seq = ++costSeq;
    box.innerHTML = '<span class="dot"></span>' + n + ' page' + (n > 1 ? 's' : '') + ' · ' + esc(dz.name) + ' · ' + esc(S.lang) + '<br><b>' + c + ' credits</b> <small>(You have <span data-credits>' + S.credits + '</span>cr)</small>';
    api('GET', 'ds/v1/doc-cost?pages=' + n + '&mode=' + (S.mode === 'preserve' ? 'preserve' : 'ai')).then(function (r) {
      if (seq !== costSeq) return;
      var b = box.querySelector('b'); if (b) b.textContent = r.cost + ' credits';
      if (r.credits != null) setCredits(r.credits);
    }).catch(function () { /* keep the estimate */ });
  }
  var costLater = debounce(updateCost, 500);

  function drawThemes() {
    var box = $('#dd-themes'), dz = D.design(S.tpl), own = D.theme(dz.theme);
    var items = [{ id: '', name: 'Design colours', t: own }].concat(D.THEMES.filter(function (x) { return !x.hide && !x.mine; }).map(function (t) { return { id: t.id, name: t.name, t: t }; }));
    box.innerHTML = brandTile() + items.map(function (x) {
      return '<button type="button" class="dd-th' + (x.id === S.theme ? ' on' : '') + '" data-theme="' + x.id + '"><div class="sw"><i style="background:' + x.t.p + '"></i><i style="background:' + x.t.s + '"></i><i style="background:' + x.t.a + '"></i><i style="background:' + x.t.tint + '"></i></div><b>' + esc(x.name) + '</b><span>' + esc(x.t.head) + '</span></button>';
    }).join('');
  }
  /* "My brand" sits first in the theme list: your saved personal theme, or a button to add one */
  function brandTile() {
    if (!BRAND) return '<button type="button" class="dd-th dd-mybr add" data-act="brand"><div class="plus">+</div><b>Add my brand</b><span>Logo, colours &amp; fonts</span></button>';
    var t = brandTheme(BRAND);
    return '<div class="dd-th dd-mybr' + (S.theme === 'my-brand' ? ' on' : '') + '" data-theme="my-brand" role="button" tabindex="0"><span class="tag">Personal</span><button type="button" class="ed" data-act="brand" title="Edit my brand">Edit</button>' +
      '<div class="sw">' + (BRAND.logo ? '<img alt="" src="' + esc(BRAND.logo) + '">' : '') + '<i style="background:' + t.p + '"></i><i style="background:' + t.s + '"></i><i style="background:' + t.a + '"></i></div><b>' + esc(t.name) + '</b><span>' + esc(t.head) + '</span></div>';
  }
  function drawChips() {
    var gs = ['auto', 'all'].concat(groups);
    $('#dd-chips').innerHTML = gs.map(function (g) { return '<button type="button" class="dd-chip' + (g === S.group ? ' on' : '') + '" data-group="' + esc(g) + '">' + (g === 'auto' ? 'For this document' : g === 'all' ? 'All designs' : esc(g)) + '</button>'; }).join('');
  }
  function drawDesigns() {
    var box = $('#dd-designs');
    var list = S.group === 'auto' ? D.designsFor(S.type) : S.group === 'all' ? D.DESIGNS.slice() : D.DESIGNS.filter(function (d) { return D.type(d.types[0]).group === S.group; });
    if (!list.length) list = D.designsFor(S.type);
    box.innerHTML = '';
    list.forEach(function (dz) {
      var b = el('button', 'dd-ds' + (dz.id === S.tpl ? ' on' : ''));
      b.type = 'button'; b.dataset.tpl = dz.id;
      var pv = el('div', 'pv'); b.appendChild(pv);
      b.appendChild(el('b', '', esc(dz.name))); b.appendChild(el('span', '', esc(dz.desc || '')));
      box.appendChild(b);
      var t = dz.types.indexOf(S.type) >= 0 ? S.type : dz.types[0];
      mockup(pv, sampleFor(t, dz.id, S.theme), dz);
    });
  }
  var drawPreview = debounce(function () {
    var box = $('#dd-preview'), dz = D.design(S.tpl), t = dz.types.indexOf(S.type) >= 0 ? S.type : dz.types[0];
    $('#dd-pvname').textContent = dz.name;
    box.innerHTML = '';
    var doc = sampleFor(t, dz.id, S.theme), sz = D.size(doc), h = 300, w = Math.round(h * sz.w / sz.h);
    var tmp = el('div'); tmp.style.cssText = 'position:absolute;left:-99999px;top:0;width:' + sz.w + 'px';
    document.body.appendChild(tmp);
    var a = D.render(tmp, doc, { edit: false, gap: 0 });
    var pages = Math.min(3, a.pages || 1);
    document.body.removeChild(tmp);
    for (var i = 0; i < pages; i++) {
      var pp = el('div', 'pp'); pp.style.width = w + 'px'; pp.style.height = h + 'px';
      box.appendChild(pp);
      (function (pp, i) {
        var inner = el('div'); pp.appendChild(inner);
        var api2 = D.render(inner, doc, { edit: false, gap: 0 });
        inner.style.width = sz.w + 'px';
        var hasCover = dz.kind === 'flow' && !!dz.cover;
        var y = i * sz.h; // cover and first flow page are stacked; offset by page index
        inner.style.transform = 'scale(' + (w / sz.w) + ') translateY(' + (-y) + 'px)';
        api2.root.style.gap = '0px';
        if (hasCover && i > 0) inner.style.transform = 'scale(' + (w / sz.w) + ') translateY(' + (-(i) * sz.h) + 'px)';
      })(pp, i);
    }
  }, 120);

  /* "View all" pop-ups: every design (or every theme) in one big window, with search and categories */
  function openGallery(kind) {
    var old = $('.dd-gal', root); if (old) old.remove();
    var isD = kind === 'designs', grp = isD ? (S.group || 'auto') : '', q = '';
    var m = el('div', 'dd-gal');
    m.innerHTML = '<div class="dd-gbox" role="dialog" aria-modal="true"><div class="dd-ghd"><div><h3>' + (isD ? 'All document designs' : 'All colour themes') + '</h3><p id="dd-gn"></p></div>' +
      '<input type="search" id="dd-gq" placeholder="' + (isD ? 'Search designs…' : 'Search themes or fonts…') + '"><button type="button" class="dd-gx" data-g="x" title="Close">×</button></div>' +
      (isD ? '<div class="dd-chips dd-gchips" id="dd-gchips"></div>' : '') +
      '<div class="dd-ggrid ' + (isD ? 'des' : 'thm') + '" id="dd-ggrid"></div></div>';
    root.appendChild(m); document.body.classList.add('dd-noscroll');
    var grid = $('#dd-ggrid', m), io = null;
    function close() { if (io) io.disconnect(); m.remove(); document.removeEventListener('keydown', key); document.body.classList.remove('dd-noscroll'); }
    function key(e) { if (e.key === 'Escape') close(); }
    document.addEventListener('keydown', key);
    function match(txt) { return !q || txt.toLowerCase().indexOf(q) >= 0; }
    function chips() {
      var gs = ['auto', 'all'].concat(groups);
      $('#dd-gchips', m).innerHTML = gs.map(function (g) { return '<button type="button" class="dd-chip' + (g === grp ? ' on' : '') + '" data-gg="' + esc(g) + '">' + (g === 'auto' ? 'For this document' : g === 'all' ? 'All designs' : esc(g)) + '</button>'; }).join('');
    }
    function draw() {
      if (io) io.disconnect();
      grid.innerHTML = ''; grid.scrollTop = 0;
      if (isD) {
        var list = grp === 'auto' ? D.designsFor(S.type) : grp === 'all' ? D.DESIGNS.slice() : D.DESIGNS.filter(function (d) { return D.type(d.types[0]).group === grp; });
        list = list.filter(function (d) { return match(d.name + ' ' + (d.desc || '') + ' ' + d.types.map(function (t) { return D.type(t).name; }).join(' ')); });
        $('#dd-gn', m).textContent = list.length + ' design' + (list.length === 1 ? '' : 's');
        /* the thumbnails are drawn only when they scroll into view, so a window of 1000+ designs opens at once */
        io = 'IntersectionObserver' in window ? new IntersectionObserver(function (en) {
          en.forEach(function (x) {
            if (!x.isIntersecting) return;
            io.unobserve(x.target);
            var dz = D.design(x.target.dataset.d), t = dz.types.indexOf(S.type) >= 0 ? S.type : dz.types[0];
            try { mockup(x.target, sampleFor(t, dz.id, S.theme), dz); } catch (er) { /* one broken thumbnail must not stop the rest */ }
          });
        }, { root: grid, rootMargin: '300px' }) : null;
        list.forEach(function (dz) {
          var b = el('button', 'dd-ds' + (dz.id === S.tpl ? ' on' : ''));
          b.type = 'button'; b.dataset.tpl = dz.id;
          var pv = el('div', 'pv'); pv.dataset.d = dz.id; b.appendChild(pv);
          b.appendChild(el('b', '', esc(dz.name))); b.appendChild(el('span', '', esc(dz.desc || D.type(dz.types[0]).name)));
          grid.appendChild(b);
          if (io) io.observe(pv); else { var t = dz.types.indexOf(S.type) >= 0 ? S.type : dz.types[0]; mockup(pv, sampleFor(t, dz.id, S.theme), dz); }
        });
        if (!list.length) grid.innerHTML = '<div class="dd-empty">No designs match your search.</div>';
      } else {
        var own = D.theme(D.design(S.tpl).theme);
        var items = (BRAND ? [{ id: 'my-brand', name: brandTheme(BRAND).name + ' (personal)', t: brandTheme(BRAND) }] : []).concat([{ id: '', name: 'Design colours', t: own }]).concat(D.THEMES.filter(function (x) { return !x.hide && !x.mine; }).map(function (t) { return { id: t.id, name: t.name, t: t }; }));
        items = items.filter(function (x) { return match(x.name + ' ' + (x.t.head || '') + ' ' + (x.t.body || '')); });
        $('#dd-gn', m).textContent = items.length + ' theme' + (items.length === 1 ? '' : 's');
        grid.innerHTML = items.map(function (x) {
          var t = x.t;
          return '<button type="button" class="dd-th dd-gth' + (x.id === S.theme ? ' on' : '') + '" data-theme="' + x.id + '">' +
            '<div class="smp" style="background:' + t.tint + ';border-top:6px solid ' + t.p + '"><i style="color:' + t.p + ';font-family:' + esc(t.head) + ',serif">Aa</i><em style="background:' + t.s + '"></em><em style="background:' + t.a + ';width:40%"></em></div>' +
            '<div class="sw"><i style="background:' + t.p + '"></i><i style="background:' + t.s + '"></i><i style="background:' + t.a + '"></i><i style="background:' + t.tint + '"></i></div><b>' + esc(x.name) + '</b><span>' + esc(t.head) + (t.body && t.body !== t.head ? ' · ' + esc(t.body) : '') + '</span></button>';
        }).join('') || '<div class="dd-empty">No themes match your search.</div>';
      }
    }
    m.addEventListener('click', function (e) {
      if (e.target === m || e.target.closest('[data-g="x"]')) { close(); return; }
      var c = e.target.closest('[data-gg]'); if (c) { grp = c.dataset.gg; chips(); draw(); return; }
      if (e.target.closest('[data-tpl],[data-theme]')) setTimeout(function () {
        close();
        if (isD) { S.group = grp === 'auto' || grp === 'all' ? grp : grp; drawChips(); drawDesigns(); var on = $('#dd-designs .dd-ds.on'); if (on) on.scrollIntoView({ block: 'nearest' }); }
      }, 0);
    });
    $('#dd-gq', m).addEventListener('input', debounce(function () { q = this.value.trim().toLowerCase(); draw(); }.bind($('#dd-gq', m)), 200));
    if (isD) chips();
    draw();
    setTimeout(function () { $('#dd-gq', m).focus(); }, 50);
  }

  function refreshAll() { drawThemes(); drawChips(); drawDesigns(); drawPreview(); updateCost(); savePrefs(); }

  root.addEventListener('click', function (e) {
    var b = e.target.closest('[data-mode]'); if (b && root.contains(b)) { setMode(b.dataset.mode); return; }
    if (e.target.closest('#dd-uai')) {
      if (CFG.useAiFree) { toast('Use AI is part of the paid plans. On your plan, your text is laid out by its headings, lists and line breaks.', '', 6000); paintUseAi(); return; }
      S.useAi = !S.useAi; paintUseAi();
      toast(S.useAi ? 'Use AI is on: AI arranges your words into the design.' : 'Use AI is off: your text is placed as you typed it — one line per place (for cards: name, job title, company, then phone, email, website, address).', 'ok', 6000);
      return;
    }
    if (e.target.closest('[data-act="brand"]')) { openBrand(); return; }
    b = e.target.closest('[data-theme]'); if (b) { S.theme = b.dataset.theme; drawThemes(); drawDesigns(); drawPreview(); updateCost(); savePrefs(); return; }
    b = e.target.closest('[data-group]'); if (b) { S.group = b.dataset.group; drawChips(); drawDesigns(); return; }
    b = e.target.closest('[data-tpl]'); if (b) {
      S.tpl = b.dataset.tpl; var dz = D.design(S.tpl);
      if (dz.types.indexOf(S.type) < 0) {
        var nt = dz.types.filter(function (t) { return S.mode !== 'ai' || D.type(t).ai; })[0];
        if (nt) { S.type = nt; elType.innerHTML = typeOptions(); fillPages(); }
      }
      $$('.dd-ds', root).forEach(function (x) { x.classList.toggle('on', x.dataset.tpl === S.tpl); });
      drawThemes(); drawPreview(); updateCost(); savePrefs(); return;
    }
    b = e.target.closest('[data-act]'); if (!b) return;
    var act = b.dataset.act;
    if (act === 'upload') $('#dd-file').click();
    if (act === 'linkedin') openLinkedIn();
    if (act === 'alldesigns') openGallery('designs');
    if (act === 'allthemes') openGallery('themes');
  });
  elType.addEventListener('change', function () {
    S.type = elType.value; fillPages(); showLi();
    if (D.design(S.tpl).types.indexOf(S.type) < 0) S.tpl = D.designsFor(S.type)[0].id;
    fillSizes(); S.group = 'auto'; refreshAll();
  });
  elPages.addEventListener('change', function () { S.pages = +elPages.value; updateCost(); savePrefs(); });
  $('#dd-lang').addEventListener('change', function (e) { S.lang = e.target.value; updateCost(); savePrefs(); });
  $('#dd-tone').addEventListener('change', function (e) { S.tone = e.target.value; savePrefs(); });
  $('#dd-size').addEventListener('change', function (e) { S.size = e.target.value; drawDesigns(); drawPreview(); savePrefs(); });
  elTopic.addEventListener('input', function () { updateCount(); if (S.mode === 'preserve') costLater(); });
  $('#dd-file').addEventListener('change', function (e) {
    var f = e.target.files && e.target.files[0]; e.target.value = '';
    if (!f) return;
    toast('Reading ' + f.name + '…');
    readFile(f).then(function (txt) {
      txt = String(txt || '').replace(/\r/g, '').replace(/\n{3,}/g, '\n\n').trim();
      if (!txt) { toast('No text was found in that file.', 'err'); return; }
      if (S.mode !== 'preserve') setMode('preserve');
      elTopic.value = txt.slice(0, 60000); updateCount(); updateCost();
      toast('Text added from ' + f.name + '.', 'ok');
    }).catch(function (err) { toast(err.message || 'That file could not be read.', 'err'); });
  });
  $('#dd-go').addEventListener('click', function () { start(); });

  /* ---------------- business cards: fill the details from a LinkedIn profile ---------------- */
  /* a screenshot is made smaller in the browser before it is sent (JPEG, at most 1600 px wide) */
  function shrinkShot(file) {
    return new Promise(function (res, rej) {
      var rd = new FileReader();
      rd.onload = function () {
        var im = new Image();
        im.onload = function () {
          var k = Math.min(1, 1600 / (im.naturalWidth || 1600), 3600 / (im.naturalHeight || 3600));
          var c = document.createElement('canvas'); c.width = Math.round(im.naturalWidth * k); c.height = Math.round(im.naturalHeight * k);
          var g = c.getContext('2d'); g.fillStyle = '#fff'; g.fillRect(0, 0, c.width, c.height); g.drawImage(im, 0, 0, c.width, c.height);
          var d = c.toDataURL('image/jpeg', 0.85);
          if (d.length > 2700000) d = c.toDataURL('image/jpeg', 0.6);
          res(d);
        };
        im.onerror = function () { rej(new Error(file.name + ' could not be read as a picture.')); };
        im.src = rd.result;
      };
      rd.onerror = function () { rej(new Error(file.name + ' could not be read.')); };
      rd.readAsDataURL(file);
    });
  }
  function openLinkedIn() {
    var old = $('.dd-gal', root); if (old) old.remove();
    var shots = [], cost = +CFG.assistCost || 1;
    var m = el('div', 'dd-gal');
    m.innerHTML = '<div class="dd-gbox dd-bk dd-li" role="dialog" aria-modal="true"><div class="dd-ghd"><div><h3><span class="inb">in</span> Fill from LinkedIn</h3><p>The AI copies your name, job title, company, location and contact details from your profile onto the card. It never makes anything up.</p></div><button type="button" class="dd-gx" data-l="x" title="Close">×</button></div>' +
      '<div class="dd-bkform" style="padding:4px 22px 10px;overflow:auto">' +
      '<label class="dd-l">1. Upload your profile PDF or screenshots</label>' +
      '<div class="dd-lidrop" id="li-drop"><button type="button" class="dd-bkbtn" data-l="up">Upload PDF or screenshots</button><span>PDF, PNG, JPG · up to 3 screenshots · or drag them here, or paste with Ctrl/Cmd+V</span><input type="file" id="li-file" accept=".pdf,.txt,image/png,image/jpeg,image/webp" multiple class="dd-hide"></div>' +
      '<div class="dd-linote">For the PDF: open your LinkedIn profile, press <b>More → Save to PDF</b> (or print the page and choose Save as PDF). LinkedIn does not let other websites read profiles from a link, so this is the reliable way.</div>' +
      '<div class="dd-lifiles" id="li-files"></div>' +
      '<label class="dd-l">…or paste your profile text</label><textarea id="li-text" rows="4" maxlength="20000" placeholder="Name, headline, current job, location, contact info…"></textarea>' +
      '<label class="dd-l">2. Your LinkedIn link <small>(optional — shown on your card)</small></label><input type="text" id="li-url" placeholder="linkedin.com/in/your-name">' +
      '<label class="dd-bkauto"><input type="checkbox" id="li-qr"> Use this link for the QR code (people scan it to open your LinkedIn profile)</label>' +
      '<div class="dd-lierr dd-hide" id="li-err" role="alert"></div>' +
      '</div><div class="dd-bkft"><span class="dd-lic">Uses ' + cost + ' credit' + (cost === 1 ? '' : 's') + ' (not charged when nothing is found)</span><span class="dd-sp"></span><button type="button" class="dd-bkbtn ghost" data-l="x">Cancel</button><button type="button" class="dd-bkbtn pri" data-l="go">Get my details</button></div></div>';
    root.appendChild(m); document.body.classList.add('dd-noscroll');
    function close() { m.remove(); document.removeEventListener('keydown', key); document.body.classList.remove('dd-noscroll'); }
    function key(e) { if (e.key === 'Escape') close(); }
    document.addEventListener('keydown', key);
    function files() {
      $('#li-files', m).innerHTML = shots.map(function (s, i) { return '<span class="f"><img alt="" src="' + s.d + '">' + esc(s.n) + '<button type="button" data-lr="' + i + '" title="Remove">×</button></span>'; }).join('') +
        ($('#li-text', m).dataset.pdf ? '<span class="f">📄 ' + esc($('#li-text', m).dataset.pdf) + '</span>' : '');
    }
    function addFiles(list) {
      Array.prototype.forEach.call(list, function (f) {
        if (/^image\//.test(f.type)) {
          if (shots.length >= 3) { toast('Up to 3 screenshots.', 'err'); return; }
          shrinkShot(f).then(function (d) { shots.push({ n: f.name, d: d }); files(); }).catch(function (er) { toast(er.message, 'err'); });
        } else {
          readFile(f).then(function (txt) {
            txt = String(txt || '').replace(/\r/g, '').trim();
            if (!txt) throw new Error('No text was found in that PDF. Try a screenshot instead.');
            var ta = $('#li-text', m); ta.value = txt.slice(0, 20000); ta.dataset.pdf = f.name; files();
            if (!$('#li-url', m).value) { var u = txt.match(/(?:https?:\/\/)?(?:[a-z]{2,3}\.)?linkedin\.com\/in\/[^\s/?#]+/i); if (u) $('#li-url', m).value = u[0].replace(/^https?:\/\/(www\.)?/i, ''); }
          }).catch(function (er) { toast(er.message || 'That file could not be read.', 'err'); });
        }
      });
    }
    $('#li-file', m).addEventListener('change', function (e) { addFiles(e.target.files || []); e.target.value = ''; });
    var drop = $('#li-drop', m);
    drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('over'); });
    drop.addEventListener('dragleave', function () { drop.classList.remove('over'); });
    drop.addEventListener('drop', function (e) { e.preventDefault(); drop.classList.remove('over'); addFiles(e.dataTransfer.files || []); });
    m.addEventListener('paste', function (e) { /* a screenshot pasted with Ctrl+V / Cmd+V */
      var it = Array.prototype.filter.call((e.clipboardData && e.clipboardData.files) || [], function (f) { return /^image\//.test(f.type); });
      if (it.length) { e.preventDefault(); addFiles(it); }
    });
    m.addEventListener('click', function (e) {
      if (e.target === m) { close(); return; }
      var rm = e.target.closest('[data-lr]'); if (rm) { shots.splice(+rm.dataset.lr, 1); files(); return; }
      var b = e.target.closest('[data-l]'); if (!b) return;
      if (b.dataset.l === 'x') close();
      if (b.dataset.l === 'up') $('#li-file', m).click();
      if (b.dataset.l === 'go') {
        var txt = $('#li-text', m).value.trim(), url = $('#li-url', m).value.trim();
        /* problems are shown inside this window, where they are easy to see (not only as a small message at the bottom) */
        var errBox = $('#li-err', m);
        function fail(msg, buy) {
          errBox.innerHTML = esc(msg) + (buy && CFG.buyUrl ? ' <a href="' + esc(CFG.buyUrl) + '">Buy credits ›</a>' : '');
          errBox.classList.remove('dd-hide'); errBox.scrollIntoView({ block: 'nearest' });
        }
        errBox.classList.add('dd-hide');
        if (!url && txt.length < 30 && !shots.length) { fail('Upload your profile PDF or a screenshot, or paste your profile text, first.'); return; }
        if (S.credits < cost && (txt.length >= 30 || shots.length)) { fail('You need ' + cost + ' credit' + (cost === 1 ? '' : 's') + ' to read your profile. You have ' + S.credits + '.', true); return; }
        var qr = $('#li-qr', m).checked;
        if (qr && !url) { toast('Add your LinkedIn link to use it for the QR code.', 'err', 6000); return; }
        b.disabled = true; b.textContent = shots.length ? 'Reading your screenshots…' : txt.length >= 30 ? 'Reading your profile…' : 'Trying your LinkedIn link…';
        api('POST', 'ds/v1/doc-linkedin', { text: txt, url: url, images: shots.map(function (s) { return s.d; }) }).then(function (r) {
          if (r.credits_balance != null) setCredits(r.credits_balance);
          var c = r.card || {}, lines = [c.name, c.title, c.company, c.tagline, c.phone, c.email, c.website, c.address ? 'Address: ' + c.address : '', c.linkedin].filter(function (x) { return x && String(x).trim(); });
          if (!lines.length) throw new Error('No card details were found in that profile.');
          close();
          S.qrLink = qr && c.linkedin ? 'https://www.' + String(c.linkedin).replace(/^https?:\/\/(www\.)?/i, '') : '';
          if (S.mode !== 'preserve') setMode('preserve');
          elTopic.value = lines.join('\n'); updateCount(); updateCost();
          toast('Your details were added from LinkedIn' + (r.from === 'link' ? ' (from your link)' : '') + '. Check them, then press Generate Document.', 'ok', 7000);
          elTopic.focus();
        }).catch(function (er) {
          fail(er.message || 'Your profile could not be read.', er.status === 402 || (er.data && er.data.error === 'credits'));
          if (er.data && er.data.credits_balance != null) setCredits(er.data.credits_balance);
          if (er.data && er.data.need === 'file') { drop.classList.add('need'); drop.scrollIntoView({ block: 'center' }); }
          b.disabled = false; b.textContent = 'Get my details';
        });
      }
    });
  }

  /* ---------------- reading uploaded files ---------------- */
  function loadScript(src) {
    return new Promise(function (res, rej) { var s = document.createElement('script'); s.src = src; s.onload = res; s.onerror = function () { rej(new Error('A helper could not be loaded.')); }; document.head.appendChild(s); });
  }
  function readFile(f) {
    var name = f.name.toLowerCase();
    if (/\.(txt|md)$/.test(name)) return f.text();
    if (/\.docx$/.test(name)) {
      return (window.JSZip ? Promise.resolve() : loadScript('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js')).then(function () { return window.JSZip.loadAsync(f); })
        .then(function (z) { var x = z.file('word/document.xml'); if (!x) throw new Error('This Word file has no text.'); return x.async('string'); })
        .then(function (xml) {
          var d = new DOMParser().parseFromString(xml, 'application/xml'), out = [];
          Array.prototype.forEach.call(d.getElementsByTagName('w:p'), function (p) { var t = ''; Array.prototype.forEach.call(p.getElementsByTagName('w:t'), function (n) { t += n.textContent; }); out.push(t); });
          return out.join('\n\n');
        });
    }
    if (/\.pdf$/.test(name)) {
      return (window.pdfjsLib ? Promise.resolve() : loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js')).then(function () {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        return f.arrayBuffer();
      }).then(function (buf) { return window.pdfjsLib.getDocument({ data: buf }).promise; }).then(function (pdf) {
        var jobs = [];
        for (var i = 1; i <= Math.min(pdf.numPages, 60); i++) jobs.push(pdf.getPage(i).then(function (pg) { return pg.getTextContent(); }).then(function (tc) { return tc.items.map(function (it) { return it.str + (it.hasEOL ? '\n' : ' '); }).join(''); }));
        return Promise.all(jobs).then(function (pp) { return pp.join('\n\n'); });
      });
    }
    return Promise.reject(new Error('Please upload a .pdf, .docx or .txt file.'));
  }

  /* ---------------- pictures (marketplace, like AI Presentation) ---------------- */
  var usedPics = {};
  function picSize(src) {
    return new Promise(function (res) { var i = new Image(); i.onload = function () { res(Math.max(i.naturalWidth, i.naturalHeight)); }; i.onerror = function () { res(0); }; i.src = src; });
  }
  function sharpPhoto(pid, url) {
    return api('POST', 'ds/v1/mkt-auto-image', { post_id: pid, url: url }).then(function (r) {
      var d = r.data || r.image || r.data_uri || '';
      if (!/^data:image\//.test(d)) throw new Error('no picture');
      return picSize(d).then(function (px) { return { data: d, px: px }; });
    });
  }
  function findPhoto(q) {
    q = String(q || '').trim() || 'business';
    return api('GET', 'ds/v1/mkt-slide-images?s=' + encodeURIComponent(q) + '&cat=images&per_page=8&page=1').then(function (r) {
      var items = (r.items || []).filter(function (it) { return it && it.id && it.full; });
      if (S.plan === 'free' || CFG.plan === 'free') items = items.filter(function (it) { return it.tier === 'free'; }).concat(items.filter(function (it) { return it.tier !== 'free'; }));
      var fresh = items.filter(function (it) { return !usedPics[it.id]; });
      var list = (fresh.length ? fresh : items).slice(0, 4), small = null, i = 0;
      function next() {
        if (i >= list.length) return small;
        var it = list[i++];
        return sharpPhoto(it.id, it.full).then(function (p) {
          if (p.px && p.px < 1000) { small = small || { pid: it.id, url: it.full, data: p.data }; return next(); }
          usedPics[it.id] = 1;
          return { pid: it.id, url: it.full, data: p.data };
        }, next);
      }
      return next();
    });
  }
  /** Fills empty picture blocks and the cover photo. */
  function fillPictures(doc, onEach) {
    var jobs = [];
    var dz = D.design(doc.tpl);
    var wantsCover = (dz.photo || dz.cover === 'photo' || dz.layout === 'flyerPhoto');
    if (doc.logo && !doc.logo.src && doc.logo.pid) jobs.push(function () { return sharpPhoto(doc.logo.pid, doc.logo.url).then(function (x) { doc.logo.src = x.data; }).catch(function () {}); });
    if (wantsCover && !doc.cover) doc.cover = { q: doc.title || '' };
    if (wantsCover && doc.cover && !doc.cover.src) {
      jobs.push(function () {
        var p = doc.cover.pid ? sharpPhoto(doc.cover.pid, doc.cover.url).then(function (x) { return { pid: doc.cover.pid, url: doc.cover.url, data: x.data }; }) : findPhoto(doc.cover.q || doc.title);
        return p.then(function (x) { if (x) { doc.cover.pid = x.pid; doc.cover.url = x.url; doc.cover.src = x.data; } }).catch(function () {});
      });
    }
    (doc.blocks || []).forEach(function (b) {
      if ((b.t !== 'image' && !(b.t === 's_photo' && doc.type !== 'business_card')) || b.src) return;
      jobs.push(function () {
        var p = b.pid ? sharpPhoto(b.pid, b.url).then(function (x) { return { pid: b.pid, url: b.url, data: x.data }; }) : findPhoto(b.q || doc.title);
        return p.then(function (x) { if (x) { b.pid = x.pid; b.url = x.url; b.src = x.data; } }).catch(function () {}).then(function () { if (onEach) onEach(); });
      });
    });
    (doc.layers || []).forEach(function (L) {
      if (!L || L.k !== 'image' || L.src || !L.pid) return;
      jobs.push(function () {
        return sharpPhoto(L.pid, L.url).then(function (x) { L.src = x.data; }).catch(function () {}).then(function () { if (onEach) onEach(); });
      });
    });
    var k = 0;
    function worker() { if (k >= jobs.length) return Promise.resolve(); var j = jobs[k++]; return j().then(worker); }
    return Promise.all([worker(), worker(), worker()]);
  }

  /* ---------------- generation ---------------- */
  var gen = null;
  function genOverlay(title) {
    var o = el('div', 'dd-gen');
    o.innerHTML = '<div class="box"><div class="st"><h4>' + esc(title) + '</h4><p>Writing and designing your document. This takes about a minute — please keep this tab open.</p><ul class="steps" id="dd-steps"></ul><div class="bar"><span id="dd-gbar"></span></div></div><div class="lv" id="dd-live"><div></div></div></div>';
    document.body.appendChild(o); document.body.classList.add('dd-noscroll');
    return o;
  }
  function steps(list, cur, frac) {
    var ul = $('#dd-steps'); if (!ul) return;
    ul.innerHTML = list.map(function (s, i) { return '<li class="' + (i < cur ? 'ok' : i === cur ? 'on' : '') + '"><i>' + (i < cur ? '✓' : '') + '</i>' + esc(s) + '</li>'; }).join('');
    var bar = $('#dd-gbar'); if (bar) bar.style.width = Math.round((frac || 0) * 100) + '%';
  }
  var livePaint = debounce(function (doc) {
    var lv = $('#dd-live'); if (!lv) return;
    var inner = lv.firstElementChild, sz = D.size(doc), w = lv.clientWidth - 32;
    D.render(inner, doc, { edit: false, gap: 16 });
    inner.style.width = sz.w + 'px'; inner.style.transform = 'scale(' + (w / sz.w) + ')';
    lv.scrollTop = lv.scrollHeight;
  }, 250);
  function closeGen() { if (gen) { gen.remove(); gen = null; } document.body.classList.remove('dd-noscroll'); }

  function newDoc() {
    return brandLogo({ v: 1, type: S.type, tpl: S.tpl, theme: S.theme, size: S.size, lang: S.lang, title: '', subtitle: '', kicker: D.type(S.type).name, author: (CFG.user && CFG.user.name) || 'Your Name', org: '', date: today(), cover: { q: '' }, blocks: [] });
  }

  function start() {
    var t = D.type(S.type);
    if (!CFG.loggedIn) { location.href = CFG.loginUrl || '/login/'; return; }
    var topic = elTopic.value.trim();
    if (S.mode === 'ai' && topic.length < 3) { toast('Please describe what your document is about.', 'err'); elTopic.focus(); return; }
    if (S.mode === 'preserve' && topic.length < 40) { toast('Paste or upload the text you want to lay out.', 'err'); elTopic.focus(); return; }
    var cost = localCost(pagesForCost());
    if (S.credits < cost) { toast('You need ' + cost + ' credits for this document. You have ' + S.credits + '.', 'err', 6000); if (CFG.buyUrl) setTimeout(function () { location.href = CFG.buyUrl; }, 1600); return; }
    var go = $('#dd-go'); go.disabled = true; go.innerHTML = '<span class="dd-spin"></span> Starting…';
    var doc = newDoc(), plan = S.mode === 'preserve' && !useAiOn() ? ['Reading your text', 'Placing your words', 'Preparing your review'] : ['Planning the document', 'Writing the content', 'Preparing your review'];
    gen = genOverlay(t.fixed ? 'Creating your ' + t.name.toLowerCase() : 'Creating your document');
    steps(plan, 0, 0.04);
    var example = t.fixed ? stripIds(D.sample(S.type).blocks) : null;
    var job = null, refund = 0;
    api('POST', 'ds/v1/doc-start', { type: S.type, topic: S.mode === 'ai' ? topic : '', source_text: S.mode === 'preserve' ? topic : '', mode: S.mode === 'preserve' ? 'preserve' : 'ai', use_ai: S.mode === 'preserve' ? useAiOn() : true, pages: +S.pages, language: S.lang, tone: S.tone, directives: ($('#dd-dir').value || '').trim(), example: example })
      .then(function (r) {
        job = r.job; setCredits(r.credits_balance);
        if (r.fixed) {
          doc.title = r.doc.title || D.sample(S.type).title; doc.subtitle = r.doc.subtitle || ''; doc.blocks = ensureIds(r.doc.blocks || []);
          /* "Use this link for the QR code" from Fill from LinkedIn: the card's QR code opens the LinkedIn profile */
          if (S.qrLink && S.type === 'business_card' && elTopic.value.indexOf(S.qrLink.replace(/^https:\/\/www\./, '')) >= 0) doc.blocks.forEach(function (b) { if (b.t === 's_qr') b.text = S.qrLink; });
          steps(plan, 2, 0.6); livePaint(doc);
          return;
        }
        var o = r.outline || {};
        doc.title = o.title || topic.slice(0, 80); doc.subtitle = o.subtitle || ''; doc.kicker = o.kicker || doc.kicker; doc.cover = { q: o.cover_q || '' };
        var secs = o.sections || [], results = new Array(secs.length), done = 0, failed = 0, idx = 0;
        if (+S.pages >= 8 && ['report', 'ebook', 'business_plan', 'whitepaper', 'research'].indexOf(S.type) >= 0) doc.blocks.push({ t: 'toc', id: D.uid() });
        steps(plan, 1, 0.1); livePaint(doc);
        function assemble() {
          var bl = doc.blocks.filter(function (b) { return b.t === 'toc'; });
          results.forEach(function (r2) { if (r2) bl = bl.concat(r2); });
          doc.blocks = bl;
        }
        function one(i, attempt) {
          return api('POST', 'ds/v1/doc-section', { job: job, index: i }).then(function (x) {
            results[i] = ensureIds(x.blocks || []); done++;
          }, function (err) {
            if (attempt < 1 && err.status !== 404) return one(i, attempt + 1);
            failed++;
          }).then(function () {
            assemble(); livePaint(doc);
            steps(plan, 1, 0.1 + 0.7 * (done + failed) / secs.length);
            var li = $('#dd-steps li.on'); if (li) li.lastChild.textContent = 'Writing section ' + Math.min(secs.length, done + failed) + ' of ' + secs.length;
          });
        }
        function lane() { if (idx >= secs.length) return Promise.resolve(); var i = idx++; return one(i, 0).then(lane); }
        return Promise.all([lane(), lane()]).then(function () {
          if (!done) { var e = new Error('The AI could not write this document. Your credits were refunded.'); throw e; }
          if (failed) toast(failed + ' section' + (failed > 1 ? 's' : '') + ' could not be written; you were not charged for them.', '', 7000);
        });
      })
      .then(function () {
        steps(plan, 2, 0.9);
        return job ? api('POST', 'ds/v1/doc-finish', { job: job }).then(function (r) { refund = +r.refund || 0; setCredits(r.credits_balance); }).catch(function () {}) : null;
      })
      .then(function () { return saveDoc(doc, 0, true).catch(function () { return 0; }); })
      .then(function (id) {
        steps(plan, 3, 1);
        closeGen();
        if (refund) toast(refund + ' credits were refunded for the part that could not be written.', 'ok', 7000);
        loadDocs(true);
        reviewDoc(doc, id || 0);
      })
      .catch(function (err) {
        if (job) api('POST', 'ds/v1/doc-finish', { job: job }).then(function (r) { setCredits(r.credits_balance); }).catch(function () {});
        closeGen();
        toast(err.message || 'Something went wrong. Please try again.', 'err', 8000);
        if (err.data && err.data.credits_balance != null) setCredits(err.data.credits_balance);
      })
      .then(function () { go.disabled = false; go.innerHTML = IC.spark + ' Generate Document'; setMode(S.mode); });
  }

  /* ---------------- review the written text before designing (like "Review Your Slides") ---------------- */
  var RV_LABEL = { h2: 'Subheading', h3: 'Small heading', p: 'Paragraph', ul: 'Bullet list', ol: 'Numbered list', check: 'Checklist', quote: 'Quote', callout: 'Callout box', stats: 'Key numbers',
    table: 'Table', chart: 'Chart', timeline: 'Timeline', cols: 'Columns', kv: 'Details', image: 'Picture', pb: 'Page break', toc: 'Contents', lh: 'Letterhead', title: 'Title', addr: 'Address',
    sign: 'Sign-off', inv: 'Invoice lines', todo: 'Tasks', week: 'Week', cal: 'Calendar', sigs: 'Signatures', r_head: 'Name & contacts', r_sec: 'Resume section', r_entry: 'Job / education',
    r_skills: 'Skills', s_kicker: 'Small line above the heading', s_title: 'Heading', s_sub: 'Short line', s_text: 'Text', s_cta: 'Button text', s_details: 'Details', s_contact: 'Contact details', s_mark: 'Logo', s_qr: 'QR code', s_photo: 'Photo', s_menu: 'Menu', s_tickets: 'Tickets', s_cert: 'Certificate', h1: 'Section heading' };
  var RV_KEYS = { text: 'Text', title: 'Title', sub: 'Subtitle', by: 'Said by', cap: 'Caption', q: 'Picture search words', name: 'Name', role: 'Role', lines: 'Lines', items: 'Items', v: 'Number', l: 'Label', d: 'When',
    org: 'Organisation', date: 'Dates', contacts: 'Contacts', close: 'Closing', rows: 'Rows', head: 'Columns', task: 'Task', due: 'Due', pr: 'Priority', q2: 'Qty', r: 'Rate', days: 'Days', n: 'Dish', p: 'Price',
    event: 'Event', place: 'Place', price: 'Price', pre: 'Line above the name', reason: 'Reason', sections: 'Menu sections', unit: 'Unit' };
  /* designs with places (cards, flyers, brochures): name each place by what goes there, not by its text style */
  var RV_ROLE = {
    business_card: { logo: 'Logo', company: 'Company name', tagline: 'Company tagline', name: 'Your name', title: 'Job title', contact: 'Contact details', qr: 'QR code', photo: 'Photo' },
    flyer: { kicker: 'Small line above the headline', title: 'Headline', sub: 'Line under the headline', text: 'Main text', list: 'List of points', cta: 'Button text', badgeK: 'Badge – small line', badgeT: 'Badge – offer (e.g. 50% OFF)', contact: 'Contact details', photo1: 'Photo 1', photo2: 'Photo 2', photo3: 'Photo 3',
      bh1: 'Back – first heading', blist: 'Back – list of points', bh2: 'Back – second heading', btext: 'Back – text', btext2: 'Back – more text', bdet: 'Back – details', bcta: 'Back – button text', bcontact: 'Back – contact details', bphoto: 'Back – photo', stats: 'Key numbers', bstats: 'Back – key numbers' },
    brochure: { cvk: 'Cover – company name', cvt: 'Cover – title', cvs: 'Cover – short line', cvp: 'Cover – photo', fh: 'Inside flap – heading', ft: 'Inside flap – text', fl: 'Inside flap – points', flp: 'Inside flap – photo', bkh: 'Back – heading', bkc: 'Back – contact details', bkq: 'Back – QR code', bkp: 'Back – photo',
      i1h: 'Inside 1 – heading', i1t: 'Inside 1 – text', i1s: 'Inside 1 – key numbers', i1p: 'Inside 1 – photo', i2h: 'Inside 2 – heading', i2l: 'Inside 2 – points', i2p: 'Inside 2 – photo', i3h: 'Inside 3 – heading', i3l: 'Inside 3 – points', i3c: 'Inside 3 – button text', i3p: 'Inside 3 – photo' }
  };
  RV_ROLE.brochure_a4 = Object.assign({}, RV_ROLE.flyer, { title: 'Cover title', kicker: 'Cover – small line', sub: 'Cover – short line', text: 'Cover – text', blist: 'Inside – points' });
  var RV_PH = { name: 'Your full name', title: 'e.g. Marketing Manager', company: 'Your company name', tagline: 'A short line about your company (optional)' };
  var CT_TYPES = [['phone', 'Phone'], ['mail', 'Email'], ['web', 'Website'], ['pin', 'Address'], ['link', 'LinkedIn / link'], ['user', 'Other']];
  function rvLabel(b, type) { var r = b.role && RV_ROLE[type] && RV_ROLE[type][b.role]; return r || RV_LABEL[b.t] || b.t; }
  var RV_ADD = [['p', 'Paragraph'], ['h2', 'Subheading'], ['ul', 'Bullet list'], ['ol', 'Numbered list'], ['quote', 'Quote'], ['callout', 'Callout box'], ['stats', 'Key numbers'], ['table', 'Table'], ['chart', 'Chart'], ['timeline', 'Timeline'], ['cols', 'Two columns'], ['image', 'Picture']];
  function rvNew(t) {
    var b = { t: t, id: D.uid() };
    if (t === 'p' || t === 'h2' || t === 'quote') b.text = '';
    if (t === 'quote') b.by = '';
    if (t === 'ul' || t === 'ol') b.items = [''];
    if (t === 'callout') { b.title = 'Good to know'; b.text = ''; b.tone = 'info'; }
    if (t === 'stats') b.items = [{ v: '', l: '' }, { v: '', l: '' }, { v: '', l: '' }];
    if (t === 'table') { b.head = ['Column 1', 'Column 2', 'Column 3']; b.rows = [['', '', ''], ['', '', '']]; }
    if (t === 'chart') { b.kind = 'column'; b.title = 'Chart title'; b.labels = ['A', 'B', 'C', 'D']; b.series = [{ name: 'Series 1', values: [10, 20, 15, 25] }]; b.cap = ''; }
    if (t === 'timeline') b.items = [{ d: 'Step 1', title: '', text: '' }, { d: 'Step 2', title: '', text: '' }];
    if (t === 'cols') b.items = [{ title: '', text: '' }, { title: '', text: '' }];
    if (t === 'image') { b.q = ''; b.cap = ''; b.w = 1; }
    return b;
  }
  function rvGroups(blocks) {
    var g = [], cur = { head: null, blocks: [] };
    blocks.forEach(function (b) {
      if (b.t === 'h1') { if (cur.head || cur.blocks.length) g.push(cur); cur = { head: b, blocks: [] }; }
      else cur.blocks.push(b);
    });
    if (cur.head || cur.blocks.length || !g.length) g.push(cur);
    return g;
  }
  function reviewDoc(doc, docId, opts) {
    var groups = rvGroups(doc.blocks || []), dz = D.design(doc.tpl), sectioned = groups.some(function (g) { return g.head; });
    var m = el('div', 'dd-rv');
    m.innerHTML = '<div class="rv-box"><div class="rv-hd"><div><h3>Review your document</h3><p>' + (S.mode === 'preserve' && !useAiOn() ? 'This is your text, placed without AI.' : 'This is the text the AI wrote.') + ' Change anything, delete what you don’t need or add more — nothing is designed yet. When it looks right, press <b>Generate Word document</b>.</p></div><button type="button" class="rv-x" data-rv="close" title="Close">×</button></div>' +
      '<div class="rv-main"><nav class="rv-nav" id="rv-nav"></nav><div class="rv-body" id="rv-body"></div></div>' +
      '<div class="rv-ft"><span class="rv-info" id="rv-info"></span><button type="button" class="rv-btn" data-rv="back">' + (opts ? 'Cancel' : '← Back to edit') + '</button><button type="button" class="rv-btn pri" data-rv="go">' + (opts ? 'Apply changes →' : 'Generate Word document →') + '</button></div></div>';
    document.body.appendChild(m); document.body.classList.add('dd-noscroll');
    var body = $('#rv-body', m), nav = $('#rv-nav', m);
    function setp(obj, path, val) {
      var ps = path.split('.'), o = obj;
      for (var i = 0; i < ps.length - 1; i++) { var k = /^\d+$/.test(ps[i]) ? +ps[i] : ps[i]; if (o[k] == null) o[k] = /^\d+$/.test(ps[i + 1]) ? [] : {}; o = o[k]; }
      var last = ps[ps.length - 1]; o[/^\d+$/.test(last) ? +last : last] = val;
    }
    function fld(path, val, opt) {
      opt = opt || {};
      var long = opt.area || String(val || '').length > 70;
      return (opt.label ? '<label class="rv-lab">' + esc(opt.label) + '</label>' : '') + (long ? '<textarea class="rv-in" data-p="' + path + '" rows="' + (opt.rows || Math.min(10, Math.max(2, Math.ceil(String(val || '').length / 80) + 1))) + '"' + (opt.ph ? ' placeholder="' + esc(opt.ph) + '"' : '') + '>' + esc(val || '') + '</textarea>'
        : '<input class="rv-in" data-p="' + path + '" value="' + esc(val == null ? '' : val) + '"' + (opt.ph ? ' placeholder="' + esc(opt.ph) + '"' : '') + '>');
    }
    function linesFld(path, arr, label) { return '<label class="rv-lab">' + esc(label) + ' <i>(one per line)</i></label><textarea class="rv-in" data-lines="' + path + '" rows="' + Math.max(3, (arr || []).length + 1) + '">' + esc((arr || []).join('\n')) + '</textarea>'; }
    function gridFld(b, gi, bi) {
      var head = b.head || [], rows = b.rows || [], cols = Math.max(head.length, rows.reduce(function (a, r) { return Math.max(a, r.length); }, 0), 1), base = gi + '.' + bi + '.';
      var h = '<div class="rv-grid" style="grid-template-columns:repeat(' + cols + ',minmax(90px,1fr))">';
      for (var c = 0; c < cols; c++) h += '<input class="rv-in th" data-b="' + base + 'head.' + c + '" value="' + esc(head[c] || '') + '">';
      rows.forEach(function (r, ri) { for (var c2 = 0; c2 < cols; c2++) h += '<input class="rv-in" data-b="' + base + 'rows.' + ri + '.' + c2 + '" value="' + esc(r[c2] == null ? '' : r[c2]) + '">'; });
      return h + '</div><div class="rv-mini"><button type="button" data-op="addrow" data-at="' + gi + '.' + bi + '">+ Row</button><button type="button" data-op="delrow" data-at="' + gi + '.' + bi + '">− Row</button><button type="button" data-op="addcol" data-at="' + gi + '.' + bi + '">+ Column</button><button type="button" data-op="delcol" data-at="' + gi + '.' + bi + '">− Column</button></div>';
    }
    function chartFld(b, gi, bi) {
      var base = gi + '.' + bi + '.', labels = b.labels || [], series = b.series || [];
      var h = '<div class="rv-two"><div><label class="rv-lab">Chart type</label><select class="rv-in" data-b="' + base + 'kind">' + D.CHART_KINDS.map(function (k) { return '<option value="' + k[0] + '"' + (k[0] === b.kind ? ' selected' : '') + '>' + k[1] + '</option>'; }).join('') + '</select></div>' +
        '<div><label class="rv-lab">Chart title</label><input class="rv-in" data-b="' + base + 'title" value="' + esc(b.title || '') + '"></div></div>';
      h += '<label class="rv-lab">Data</label><div class="rv-grid" style="grid-template-columns:minmax(90px,1.2fr) repeat(' + Math.max(1, series.length) + ',minmax(80px,1fr))"><span class="rv-gh">Label</span>' +
        series.map(function (sr, si) { return '<input class="rv-in th" data-b="' + base + 'series.' + si + '.name" value="' + esc(sr.name || '') + '">'; }).join('');
      labels.forEach(function (l, li) {
        h += '<input class="rv-in" data-b="' + base + 'labels.' + li + '" value="' + esc(l) + '">' + series.map(function (sr, si) { return '<input class="rv-in num" data-num="1" data-b="' + base + 'series.' + si + '.values.' + li + '" value="' + esc((sr.values || [])[li] == null ? '' : sr.values[li]) + '">'; }).join('');
      });
      h += '</div><div class="rv-mini"><button type="button" data-op="crow" data-at="' + gi + '.' + bi + '">+ Row</button><button type="button" data-op="cdelrow" data-at="' + gi + '.' + bi + '">− Row</button><button type="button" data-op="cser" data-at="' + gi + '.' + bi + '">+ Series</button><button type="button" data-op="cdelser" data-at="' + gi + '.' + bi + '">− Series</button></div>';
      h += '<div class="rv-chartpv">' + D.chartSVG(b, D.look(doc), 520, Math.round(520 * D.chartRatio(b)), { bg: '#FFFFFF' }) + '</div>';
      return h + '<label class="rv-lab">Caption</label><input class="rv-in" data-b="' + base + 'cap" value="' + esc(b.cap || '') + '">';
    }
    function genericFld(b, gi, bi) {
      var base = gi + '.' + bi + '.', h = '';
      Object.keys(b).forEach(function (k) {
        if (/^(t|id|src|pid|url|w|side|tone|y|m|cur|tax|n|start|kind|role|pg|zn|dx|dy|sc|st|r|sides|pts)$/.test(k)) return;
        var v = b[k], lab = RV_KEYS[k] || k;
        if (typeof v === 'string' || typeof v === 'number') h += fld(base + k, v, { label: lab, area: k === 'text' || k === 'reason' });
        else if (Array.isArray(v) && v.every(function (x) { return typeof x === 'string'; })) h += '<label class="rv-lab">' + esc(lab) + ' <i>(one per line)</i></label><textarea class="rv-in" data-blines="' + base + k + '" rows="' + Math.max(3, v.length + 1) + '">' + esc(v.join('\n')) + '</textarea>';
        else if (Array.isArray(v)) {
          h += '<label class="rv-lab">' + esc(lab) + '</label><div class="rv-rows">';
          v.forEach(function (row, ri) {
            h += '<div class="rv-rowi">';
            if (Array.isArray(row)) row.forEach(function (cv, ci) { h += '<input class="rv-in" data-b="' + base + k + '.' + ri + '.' + ci + '" value="' + esc(cv == null ? '' : cv) + '">'; });
            else if (row && typeof row === 'object') Object.keys(row).forEach(function (rk) { if (typeof row[rk] === 'string' || typeof row[rk] === 'number') h += '<input class="rv-in" data-b="' + base + k + '.' + ri + '.' + rk + '" value="' + esc(row[rk]) + '" placeholder="' + esc(RV_KEYS[rk] || rk) + '" title="' + esc(RV_KEYS[rk] || rk) + '">'; });
            h += '<button type="button" class="rv-del" data-op="delitem" data-at="' + gi + '.' + bi + '" data-k="' + k + '" data-i="' + ri + '" title="Remove">×</button></div>';
          });
          h += '</div><div class="rv-mini"><button type="button" data-op="additem" data-at="' + gi + '.' + bi + '" data-k="' + k + '">+ Add</button></div>';
        }
      });
      return h || '<p class="rv-note">This block has no text to edit.</p>';
    }
    function blockFld(b, gi, bi) {
      var base = gi + '.' + bi + '.';
      switch (b.t) {
        case 'p': case 's_text': return '<textarea class="rv-in" data-b="' + base + 'text" rows="' + Math.min(14, Math.max(3, Math.ceil(String(b.text || '').length / 90) + 1)) + '" placeholder="Write a paragraph…">' + esc(b.text || '') + '</textarea>';
        case 'h2': case 'h3': case 's_title': case 's_sub': case 's_kicker': case 'r_sec': case 's_cta': return '<input class="rv-in big" data-b="' + base + 'text" value="' + esc(b.text || '') + '"' + (b.role && RV_PH[b.role] && doc.type === 'business_card' ? ' placeholder="' + esc(RV_PH[b.role]) + '"' : '') + '>';
        case 's_mark': return '<p class="rv-note">Your logo goes here. Upload it in the editor: click the logo, then <b>Upload logo</b>' + (D.myBrand && D.myBrand.logo ? ' — your saved brand logo is used automatically' : '') + '.</p>';
        case 's_photo': return '<p class="rv-note">A photo goes here. You can pick or upload it in the editor.</p>';
        case 's_qr': return '<label class="rv-lab">Link the QR code opens</label><input class="rv-in" data-b="' + base + 'text" value="' + esc(b.text || '') + '" placeholder="www.yourcompany.com or linkedin.com/in/your-name">';
        case 's_contact':
          return '<div class="rv-rows">' + (b.rows || []).map(function (r, ri) {
            if (!r.i) r.i = 'phone';
            return '<div class="rv-rowi rv-ct"><select class="rv-in" data-b="' + base + 'rows.' + ri + '.i">' + CT_TYPES.map(function (x) { return '<option value="' + x[0] + '"' + (x[0] === r.i ? ' selected' : '') + '>' + x[1] + '</option>'; }).join('') + '</select>' +
              '<input class="rv-in" data-b="' + base + 'rows.' + ri + '.t" value="' + esc(r.t || '') + '" placeholder="' + esc({ phone: '+91 98450 00000', mail: 'name@company.com', web: 'www.company.com', pin: 'Street, City', link: 'linkedin.com/in/your-name' }[r.i] || '') + '">' +
              '<button type="button" class="rv-del" data-op="delitem" data-at="' + gi + '.' + bi + '" data-k="rows" data-i="' + ri + '" title="Remove">×</button></div>';
          }).join('') + '</div><div class="rv-mini"><button type="button" data-op="additem" data-at="' + gi + '.' + bi + '" data-k="rows">+ Add a contact line</button></div>';
        case 'ul': case 'ol': case 'check': return '<textarea class="rv-in" data-blines="' + base + 'items" rows="' + Math.max(3, (b.items || []).length + 1) + '" placeholder="One point per line">' + esc((b.items || []).join('\n')) + '</textarea>';
        case 'quote': return '<textarea class="rv-in" data-b="' + base + 'text" rows="2">' + esc(b.text || '') + '</textarea><label class="rv-lab">Said by</label><input class="rv-in" data-b="' + base + 'by" value="' + esc(b.by || '') + '">';
        case 'callout': return '<input class="rv-in" data-b="' + base + 'title" value="' + esc(b.title || '') + '" placeholder="Title"><textarea class="rv-in" data-b="' + base + 'text" rows="2" style="margin-top:6px">' + esc(b.text || '') + '</textarea>';
        case 'table': return gridFld(b, gi, bi);
        case 'chart': return chartFld(b, gi, bi);
        case 'image': return '<div class="rv-two"><div><label class="rv-lab">Picture search words</label><input class="rv-in" data-b="' + base + 'q" value="' + esc(b.q || '') + '" placeholder="e.g. team meeting office"></div><div><label class="rv-lab">Caption</label><input class="rv-in" data-b="' + base + 'cap" value="' + esc(b.cap || '') + '"></div></div><p class="rv-note">A matching picture is added when the document is designed. You can change it in the editor.</p>';
        case 'pb': case 'toc': case 'cal': case 'week': return '<p class="rv-note">' + (b.t === 'toc' ? 'The contents list is made from your section headings.' : b.t === 'pb' ? 'Starts a new page.' : 'Filled in by the design.') + '</p>';
      }
      return genericFld(b, gi, bi);
    }
    function draw() {
      var h = '<div class="rv-doc"><label class="rv-lab">' + (D.type(doc.type).fixed ? 'Name in My Documents <i>(not printed on the design)</i>' : 'Document title') + '</label><input class="rv-in big" data-d="title" value="' + esc(doc.title || '') + '">' +
        (doc.subtitle != null && dz.kind !== 'single' ? '<label class="rv-lab">Subtitle</label><input class="rv-in" data-d="subtitle" value="' + esc(doc.subtitle || '') + '">' : '') + '</div>';
      var nh = '';
      groups.forEach(function (g, gi) {
        var name = g.head ? (g.head.text || 'Untitled section') : (gi === 0 ? (sectioned ? 'Opening' : 'Content') : 'More');
        nh += '<a href="#" data-go="' + gi + '"><b>' + (g.head ? ('0' + (groups.slice(0, gi + 1).filter(function (x) { return x.head; }).length)).slice(-2) : '•') + '</b>' + esc(String(name).replace(/\*/g, '')) + '</a>';
        h += '<section class="rv-sec" id="rv-s' + gi + '"><div class="rv-sh">' + (g.head ? '<input class="rv-in big hd" data-b="' + gi + '.h.text" value="' + esc(g.head.text || '') + '" placeholder="Section heading">' : '<span class="rv-st">' + esc(name) + '</span>') +
          '<span class="rv-acts">' + (g.head ? '<button type="button" data-op="secup" data-at="' + gi + '" title="Move section up">↑</button><button type="button" data-op="secdown" data-at="' + gi + '" title="Move section down">↓</button><button type="button" data-op="secdel" data-at="' + gi + '" title="Delete section">' + IC.trash + '</button>' : '') + '</span></div>';
        g.blocks.forEach(function (b, bi) {
          h += '<div class="rv-blk"><div class="rv-bh"><span class="rv-tag">' + esc(rvLabel(b, doc.type)) + '</span><span class="rv-acts"><button type="button" data-op="up" data-at="' + gi + '.' + bi + '" title="Move up">↑</button><button type="button" data-op="down" data-at="' + gi + '.' + bi + '" title="Move down">↓</button><button type="button" data-op="del" data-at="' + gi + '.' + bi + '" title="Delete">' + IC.trash + '</button></span></div>' + blockFld(b, gi, bi) + '</div>';
        });
        h += '<div class="rv-add"><select data-addto="' + gi + '"><option value="">+ Add to this section…</option>' + RV_ADD.map(function (x) { return '<option value="' + x[0] + '">' + x[1] + '</option>'; }).join('') + '</select>' +
          (sectioned ? '<button type="button" data-op="secadd" data-at="' + gi + '">+ New section below</button>' : '') + '</div></section>';
      });
      body.innerHTML = h; nav.innerHTML = nh;
      count();
    }
    function count() {
      var w = 0; groups.forEach(function (g) { [g.head].concat(g.blocks).forEach(function (b) { if (b) w += (JSON.stringify(b).match(/[A-Za-zऀ-෿]+/g) || []).length; }); });
      var nsec = groups.filter(function (g) { return g.head; }).length;
      $('#rv-info', m).textContent = (nsec ? nsec + ' section' + (nsec === 1 ? '' : 's') + ' · ' : '') + 'about ' + Math.max(1, Math.round(w * 0.8)).toLocaleString() + ' words';
    }
    function blockAt(at) { var p = at.split('.'); return { g: groups[+p[0]], gi: +p[0], bi: +p[1] }; }
    body.addEventListener('input', function (e) {
      var t = e.target;
      if (t.dataset.d) { doc[t.dataset.d] = t.value; return; }
      var path = t.dataset.b || t.dataset.blines;
      if (!path) return;
      var ps = path.split('.'), g = groups[+ps[0]], tgt = ps[1] === 'h' ? g.head : g.blocks[+ps[1]], rest = ps.slice(2).join('.');
      var val = t.dataset.blines ? t.value.split('\n').map(function (x) { return x.replace(/\s+$/, ''); }).filter(function (x, i, a) { return x || i < a.length - 1; }) : t.value;
      if (t.dataset.num) val = val === '' ? 0 : +String(val).replace(/[^\d.\-]/g, '') || 0;
      setp(tgt, rest, val);
      if (tgt.t === 'chart') { var pv = t.closest('.rv-blk').querySelector('.rv-chartpv'); if (pv) pv.innerHTML = D.chartSVG(tgt, D.look(doc), 520, Math.round(520 * D.chartRatio(tgt)), { bg: '#FFFFFF' }); }
      if (ps[1] === 'h') { var a = nav.querySelector('[data-go="' + ps[0] + '"]'); if (a) a.lastChild.textContent = t.value; }
    });
    body.addEventListener('change', function (e) {
      var sel = e.target.closest('[data-addto]'); if (sel && sel.value) { groups[+sel.dataset.addto].blocks.push(rvNew(sel.value)); draw(); var s2 = $('#rv-s' + sel.dataset.addto, m); if (s2) s2.lastElementChild.previousElementSibling.scrollIntoView({ block: 'center' }); return; }
      if (e.target.dataset.b && /\.kind$|\.rows\.\d+\.i$/.test(e.target.dataset.b)) draw();
    });
    m.addEventListener('click', function (e) {
      var go = e.target.closest('[data-go]'); if (go) { e.preventDefault(); var s3 = $('#rv-s' + go.dataset.go, m); if (s3) s3.scrollIntoView({ behavior: 'smooth', block: 'start' }); return; }
      var b = e.target.closest('[data-op]');
      if (b) {
        var op = b.dataset.op, at = b.dataset.at, x = at && at.indexOf('.') > 0 ? blockAt(at) : null, gi = +String(at).split('.')[0];
        if (op === 'del') x.g.blocks.splice(x.bi, 1);
        if (op === 'up' && x.bi > 0) x.g.blocks.splice(x.bi - 1, 0, x.g.blocks.splice(x.bi, 1)[0]);
        if (op === 'down' && x.bi < x.g.blocks.length - 1) x.g.blocks.splice(x.bi + 1, 0, x.g.blocks.splice(x.bi, 1)[0]);
        if (op === 'secdel') { if (!window.confirm('Delete this whole section?')) return; groups.splice(gi, 1); if (!groups.length) groups.push({ head: null, blocks: [] }); }
        if (op === 'secup' && gi > 0 && groups[gi - 1].head) groups.splice(gi - 1, 0, groups.splice(gi, 1)[0]);
        if (op === 'secdown' && gi < groups.length - 1) groups.splice(gi + 1, 0, groups.splice(gi, 1)[0]);
        if (op === 'secadd') groups.splice(gi + 1, 0, { head: { t: 'h1', id: D.uid(), text: 'New section' }, blocks: [rvNew('p')] });
        var bl = x && x.g.blocks[x.bi];
        if (op === 'addrow') bl.rows.push((bl.head || bl.rows[0] || ['']).map(function () { return ''; }));
        if (op === 'delrow' && bl.rows.length > 1) bl.rows.pop();
        if (op === 'addcol') { bl.head = bl.head || []; bl.head.push('Column'); bl.rows.forEach(function (r) { r.push(''); }); }
        if (op === 'delcol' && (bl.head || bl.rows[0] || []).length > 1) { if (bl.head) bl.head.pop(); bl.rows.forEach(function (r) { r.pop(); }); }
        if (op === 'crow') { bl.labels.push(''); bl.series.forEach(function (sr) { (sr.values = sr.values || []).push(0); }); }
        if (op === 'cdelrow' && bl.labels.length > 1) { bl.labels.pop(); bl.series.forEach(function (sr) { (sr.values || []).pop(); }); }
        if (op === 'cser' && bl.series.length < 6) bl.series.push({ name: 'Series ' + (bl.series.length + 1), values: bl.labels.map(function () { return 0; }) });
        if (op === 'cdelser' && bl.series.length > 1) bl.series.pop();
        if (op === 'additem') { var arr = bl[b.dataset.k]; var last = arr[arr.length - 1]; arr.push(Array.isArray(last) ? last.map(function () { return ''; }) : last && typeof last === 'object' ? Object.keys(last).reduce(function (o, k) { o[k] = typeof last[k] === 'number' ? 0 : ''; return o; }, {}) : ''); }
        if (op === 'delitem') { var arr2 = bl[b.dataset.k]; if (arr2.length > 1) arr2.splice(+b.dataset.i, 1); }
        var st = body.scrollTop; draw(); body.scrollTop = st;
        return;
      }
      var r = e.target.closest('[data-rv]'); if (!r) return;
      if (opts && (r.dataset.rv === 'close' || r.dataset.rv === 'back')) { close(); opts.onCancel(); return; }
      if (opts && r.dataset.rv === 'go') { flatten(); close(); opts.onApply(); return; }
      if (r.dataset.rv === 'close' || r.dataset.rv === 'back') { flatten(); saveDoc(doc, docId, true).catch(function () {}); close(); toast('Your text is saved in My Documents. You can open it again any time.', '', 5000); return; }
      if (r.dataset.rv === 'go') { flatten(); close(); designDoc(doc, docId); }
    });
    function flatten() {
      var out = [];
      groups.forEach(function (g) { if (g.head) out.push(g.head); out = out.concat(g.blocks); });
      doc.blocks = ensureIds(out);
    }
    function close() { m.remove(); document.body.classList.remove('dd-noscroll'); }
    draw();
  }
  /** After the review: apply the design, add pictures, save and open the editor. */
  function designDoc(doc, docId) {
    var plan = ['Applying the design', 'Adding pictures', 'Saving your document'];
    gen = genOverlay('Designing your document');
    var gp = gen.querySelector('p'); if (gp) gp.textContent = 'Laying out your text in the ' + D.design(doc.tpl).name + ' design. Designing is free.';
    steps(plan, 0, 0.15); livePaint(doc);
    setTimeout(function () {
      steps(plan, 1, 0.4);
      fillPictures(doc, function () { livePaint(doc); }).then(function () {
        steps(plan, 2, 0.85);
        return saveDoc(doc, docId, true).catch(function () { return docId; });
      }).then(function (id) {
        steps(plan, 3, 1);
        closeGen();
        openEditor(doc, id || docId || 0);
        loadDocs(true);
      });
    }, 500);
  }

  /* ---------------- saving ---------------- */
  function forSave(doc) {
    var d = D.clone(doc);
    (d.blocks || []).forEach(function (b) { if (b.src && /^data:/.test(b.src) && b.pid) delete b.src; if (b.src && /^data:/.test(b.src) && b.src.length > 700000) delete b.src; });
    if (d.cover && d.cover.src && /^data:/.test(d.cover.src)) delete d.cover.src;
    (d.layers || []).forEach(function (L) { if (L && L.k === 'image' && L.src && /^data:/.test(L.src) && (L.pid || L.src.length > 740000)) delete L.src; });
    return d;
  }
  function saveDoc(doc, id, quiet) {
    if (!CFG.loggedIn) return Promise.reject(new Error('Please sign in to save.'));
    return api('POST', 'ds/v1/save-doc', { doc_id: id || 0, doc: forSave(doc) }).then(function (r) { if (!quiet) toast('Saved.', 'ok'); return r.doc_id; });
  }

  /* ---------------- my documents ---------------- */
  function loadDocs(reset) {
    var box = $('#dd-mydocs');
    if (!CFG.loggedIn) { box.innerHTML = '<div class="dd-empty">Sign in to see your saved documents.</div>'; return; }
    if (reset) { S.docs = []; S.docsNext = 0; }
    api('GET', 'ds/v1/my-docs?offset=' + S.docsNext + '&limit=12').then(function (r) {
      S.docs = S.docs.concat(r.docs || []); S.docsNext = r.next_offset || S.docs.length; S.docsTotal = r.total || 0;
      drawDocs();
    }).catch(function () { box.innerHTML = '<div class="dd-empty">Your documents could not be loaded.</div>'; });
  }
  function drawDocs() {
    var box = $('#dd-mydocs'), q = ($('#dd-dsearch').value || '').toLowerCase(), sort = $('#dd-dsort').value;
    $('#dd-ndocs').textContent = (S.docsTotal || S.docs.length) + ' Document' + ((S.docsTotal || S.docs.length) === 1 ? '' : 's');
    var list = S.docs.filter(function (m) { return !q || String(m.title || '').toLowerCase().indexOf(q) >= 0 || String(m.subtitle || '').toLowerCase().indexOf(q) >= 0; });
    if (sort === 'title') list = list.slice().sort(function (x, y) { return String(x.title || '').localeCompare(String(y.title || '')); });
    if (!list.length) { box.innerHTML = '<div class="dd-empty">' + (S.docs.length ? 'No documents match your search.' : 'No documents yet. Describe one above and press Generate Document.') + '</div>'; $('#dd-more').classList.add('dd-hide'); return; }
    box.innerHTML = '';
    list.forEach(function (m) {
      var c = el('div', 'dd-md'), pv = el('div', 'pv');
      c.appendChild(pv);
      c.appendChild(el('span', 'badge', esc(D.type(m.type).name)));
      var x = el('button', 'x', IC.trash); x.type = 'button'; x.title = 'Delete'; c.appendChild(x);
      var bd = el('div', 'bd');
      bd.appendChild(el('b', '', esc(m.title || 'Untitled')));
      bd.appendChild(el('div', 'ds', esc(m.subtitle || D.design(m.tpl).name + ' design')));
      bd.appendChild(el('div', 'dt', IC.clock + ' ' + new Date((m.updated || 0) * 1000).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })));
      c.appendChild(bd);
      box.appendChild(c);
      var mini = { type: m.type || 'report', tpl: D.design(m.tpl).id, theme: m.theme || '', size: S.size, title: m.title, subtitle: m.subtitle || '', kicker: m.kicker || '', author: '', org: '', date: '', cover: {}, blocks: D.design(m.tpl).kind === 'flow' && D.design(m.tpl).cover ? [] : D.sample(m.type || 'report').blocks };
      setTimeout(function () { preview(pv, mini, pv.clientWidth || 300, { now: true }); }, 0);
      c.addEventListener('click', function (e) { if (!e.target.closest('.x')) openSaved(m.id); });
      x.addEventListener('click', function () {
        if (!window.confirm('Delete "' + (m.title || 'this document') + '"? This cannot be undone.')) return;
        api('POST', 'ds/v1/delete-doc', { doc_id: m.id }).then(function () { S.docs = S.docs.filter(function (d) { return d.id !== m.id; }); S.docsTotal = Math.max(0, S.docsTotal - 1); drawDocs(); toast('Deleted.'); }).catch(function (e) { toast(e.message, 'err'); });
      });
    });
    var left = Math.max(0, S.docsTotal - S.docs.length);
    $('#dd-more').textContent = 'Load more (' + left + ' left)';
    $('#dd-more').classList.toggle('dd-hide', left <= 0);
  }
  $('#dd-dsearch').addEventListener('input', debounce(drawDocs, 200));
  $('#dd-dsort').addEventListener('change', drawDocs);
  $('#dd-more').addEventListener('click', function () { loadDocs(false); });
  function openSaved(id) {
    toast('Opening…');
    api('POST', 'ds/v1/load-doc', { doc_id: id }).then(function (r) {
      var doc = r.doc || {}; ensureIds(doc.blocks || []);
      openEditor(doc, r.doc_id);
      fillPictures(doc, function () { if (ED) ED.repaint(); }).then(function () { if (ED) ED.repaint(); });
    }).catch(function (e) { toast(e.message, 'err'); });
  }

  /* ---------------- downloads ---------------- */
  function fileName(doc) { return (String(doc.title || 'document').replace(/[\\/:*?"<>|]+/g, ' ').trim().slice(0, 80) || 'document'); }
  function downloadDocx(doc) {
    toast('Preparing your Word file…', '', 20000);
    return D.toDocx(doc, {}).then(function (blob) {
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = fileName(doc) + '.docx';
      document.body.appendChild(a); a.click(); setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 4000);
      toast('Word file downloaded.', 'ok');
    }).catch(function (e) { toast(e.message || 'The Word file could not be made.', 'err'); });
  }
  /* ---------------- PNG / JPG / TIFF / EPS + SVG: every page as it looks in the editor ---------------- */
  var expBusy = false;
  function saveBlob(blob, name) {
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name;
    document.body.appendChild(a); a.click(); setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 6000);
  }
  function expProgress() {
    var m = el('div', 'dd-io'); m.innerHTML = '<div class="io-box io-sm" style="text-align:center"><span class="dd-spin"></span><h3 style="margin-top:12px">Preparing your download</h3><p class="io-sub io-msg">Getting ready…</p><div class="io-bar"><i></i></div></div>';
    document.body.appendChild(m);
    return { set: function (t, pc) { m.querySelector('.io-msg').textContent = t; m.querySelector('.io-bar i').style.width = Math.max(3, Math.min(100, pc)) + '%'; }, close: function () { m.remove(); } };
  }
  function waitImages(root) {
    var imgs = $$('img', root).filter(function (i) { return !i.complete; });
    var bg = [];
    $$('[style*="background-image"]', root).forEach(function (n) {
      var m = /url\(["']?([^"')]+)["']?\)/.exec(n.style.backgroundImage || ''); if (m) bg.push(m[1]);
    });
    return Promise.all(imgs.map(function (i) { return new Promise(function (r) { i.onload = i.onerror = r; setTimeout(r, 8000); }); }).concat(bg.map(function (u) { return new Promise(function (r) { var im = new Image(); im.onload = im.onerror = r; im.src = u; setTimeout(r, 8000); }); })));
  }
  function exportStage(doc) {
    var sz = D.size(doc), wrap = el('div', 'dd-expstage');
    wrap.style.cssText = 'position:absolute;left:0;top:0;width:' + sz.w + 'px;background:#fff;z-index:0;pointer-events:none';
    document.body.appendChild(wrap);
    var d2 = JSON.parse(JSON.stringify(doc));
    var a = D.render(wrap, d2, { edit: false, gap: 0 });
    $$('.dsd-page', wrap).forEach(function (p) { p.style.boxShadow = 'none'; });
    return (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(function () { if (a && a.layout) a.layout(); return waitImages(wrap); }).then(function () {
      return new Promise(function (r) { setTimeout(r, 350); });
    }).then(function () {
      var r0 = wrap.getBoundingClientRect();
      var pages = $$('.dsd-page', wrap).map(function (p) { var r = p.getBoundingClientRect(); return { x: r.left - r0.left, y: r.top - r0.top }; }).sort(function (a2, b2) { return a2.y - b2.y; });
      return { wrap: wrap, pages: pages, w: sz.w, h: sz.h };
    });
  }
  /* one page cut out of the laid-out document (the elements that sit over the page stay with it) */
  function pageClip(st, pg) {
    var box = el('div'); box.style.cssText = 'position:absolute;left:0;top:0;width:' + st.w + 'px;height:' + st.h + 'px;overflow:hidden;background:#fff;z-index:0;pointer-events:none';
    var c = st.wrap.cloneNode(true); c.className = ''; c.style.cssText = 'position:absolute;left:' + (-pg.x) + 'px;top:' + (-pg.y) + 'px;width:' + st.w + 'px;background:#fff';
    box.appendChild(c); document.body.appendChild(box);
    /* leave out what does not touch this page: smaller files */
    var br = box.getBoundingClientRect();
    $$('.dsd-page,.dsd-L,.dsd-blk', c).forEach(function (n) { var r = n.getBoundingClientRect(); if (r.bottom < br.top - 2 || r.top > br.bottom + 2) n.style.visibility = 'hidden'; });   /* hidden, not removed: the layout must not move */
    return box;
  }
  /* ---- print resolution: each page is drawn at the chosen dpi and the file carries that dpi ---- */
  var CRC = null;
  function crc32(u8) {
    if (!CRC) { CRC = new Uint32Array(256); for (var n = 0; n < 256; n++) { var c = n; for (var k = 0; k < 8; k++) c = c & 1 ? 0xEDB88320 ^ (c >>> 1) : c >>> 1; CRC[n] = c >>> 0; } }
    var x = 0xFFFFFFFF; for (var i = 0; i < u8.length; i++) x = CRC[(x ^ u8[i]) & 0xFF] ^ (x >>> 8); return (x ^ 0xFFFFFFFF) >>> 0;
  }
  function withDpi(blob, kind, dpi) {           /* write the dpi into the PNG (pHYs) or JPG (JFIF) header, so printers size it right */
    return blob.arrayBuffer().then(function (ab) {
      var u = new Uint8Array(ab);
      if (kind === 'jpg') {
        if (u[2] === 0xFF && u[3] === 0xE0 && u[6] === 0x4A && u[7] === 0x46 && u[8] === 0x49 && u[9] === 0x46) {
          u[13] = 1; u[14] = dpi >> 8; u[15] = dpi & 255; u[16] = dpi >> 8; u[17] = dpi & 255;
        }
        return new Blob([u], { type: 'image/jpeg' });
      }
      var ppm = Math.round(dpi / 0.0254), ch = new Uint8Array(21), v = new DataView(ch.buffer);
      v.setUint32(0, 9); ch.set([0x70, 0x48, 0x59, 0x73], 4); v.setUint32(8, ppm); v.setUint32(12, ppm); ch[16] = 1;
      v.setUint32(17, crc32(ch.subarray(4, 17)));
      var at = 8 + 25;                                   /* right after IHDR */
      var outb = new Uint8Array(u.length + 21); outb.set(u.subarray(0, at), 0); outb.set(ch, at); outb.set(u.subarray(at), at + 21);
      return new Blob([outb], { type: 'image/png' });
    });
  }
  var useZ = typeof CompressionStream !== 'undefined';
  function tiffPage(cv) {                         /* one page → RGB strip, compressed straight away so big pages don't fill the memory */
    var w = cv.width, h = cv.height, ctx = cv.getContext('2d'), parts = [], band = 512, y = 0;
    function zip(u8) {
      if (!useZ) return Promise.resolve(u8);
      var cs = new CompressionStream('deflate'), wr = cs.writable.getWriter(); wr.write(u8); wr.close();
      return new Response(cs.readable).arrayBuffer().then(function (b) { return new Uint8Array(b); });
    }
    var rgb = new Uint8Array(w * h * 3);
    for (y = 0; y < h; y += band) {
      var bh = Math.min(band, h - y), d = ctx.getImageData(0, y, w, bh).data, o = y * w * 3;
      for (var i = 0, j = 0, n = w * bh; i < n; i++, j += 4) { rgb[o + i * 3] = d[j]; rgb[o + i * 3 + 1] = d[j + 1]; rgb[o + i * 3 + 2] = d[j + 2]; }
    }
    cv.width = 1; cv.height = 1;
    return zip(rgb).then(function (data) { return { w: w, h: h, data: data }; });
  }
  function tiffBuild(imgs, dpi) {                 /* a multi-page TIFF, RGB 8-bit, Deflate when the browser has it */
    var NT = 12, size = 8;
    imgs.forEach(function (im) { im.dataOff = size; size += im.data.length + (im.data.length & 1); im.ifdOff = size; size += 2 + NT * 12 + 4; im.extOff = size; size += 6 + 16; });
    var buf = new ArrayBuffer(size), v = new DataView(buf), u = new Uint8Array(buf);
    v.setUint16(0, 0x4949); v.setUint16(2, 42, true); v.setUint32(4, imgs[0].ifdOff, true);
    imgs.forEach(function (im, k) {
      u.set(im.data, im.dataOff);
      var o = im.ifdOff, e = im.extOff;
      v.setUint16(e, 8, true); v.setUint16(e + 2, 8, true); v.setUint16(e + 4, 8, true);
      v.setUint32(e + 6, dpi, true); v.setUint32(e + 10, 1, true); v.setUint32(e + 14, dpi, true); v.setUint32(e + 18, 1, true);
      v.setUint16(o, NT, true); var p0 = o + 2;
      function tag(t, ty, cnt, val) { v.setUint16(p0, t, true); v.setUint16(p0 + 2, ty, true); v.setUint32(p0 + 4, cnt, true); if (ty === 3 && cnt === 1) v.setUint16(p0 + 8, val, true); else v.setUint32(p0 + 8, val, true); p0 += 12; }
      tag(256, 4, 1, im.w); tag(257, 4, 1, im.h); tag(258, 3, 3, e); tag(259, 3, 1, useZ ? 8 : 1); tag(262, 3, 1, 2);
      tag(273, 4, 1, im.dataOff); tag(277, 3, 1, 3); tag(278, 4, 1, im.h); tag(279, 4, 1, im.data.length);
      tag(282, 5, 1, e + 6); tag(283, 5, 1, e + 14); tag(296, 3, 1, 2);
      v.setUint32(p0, k + 1 < imgs.length ? imgs[k + 1].ifdOff : 0, true);
    });
    return new Blob([buf], { type: 'image/tiff' });
  }
  var DPI_LAST = {};
  function askDpi(doc, kind) {                    /* asked every time a PNG / JPG / TIFF is downloaded */
    return new Promise(function (resolve) {
      var sz = D.size(doc), opts = [[96, 'Screen', 'For screens, email and web'], [150, 'Web & office', 'Office printers, small files'], [300, 'Print', 'Standard print quality (recommended)'],
        [600, 'High-quality print', 'Sharp text and fine lines'], [900, 'Premium print', 'Large format and professional print · slow, very large files'], [1200, 'Maximum', 'Only when your printer asks for it · very slow']];
      var cur = DPI_LAST[kind] || (kind === 'tiff' ? 300 : kind === 'png' ? 300 : 150);
      var inch = function (px) { return px / 96; };
      var m = el('div', 'dd-io');
      m.innerHTML = '<div class="io-box io-md io-dpi"><h3 style="text-align:left">Download ' + kind.toUpperCase() + '</h3><p class="io-sub" style="text-align:left">Choose the resolution. Higher dpi gives sharper print but bigger files.</p><div class="io-dl">' +
        opts.map(function (o) {
          var w = Math.round(inch(sz.w) * o[0]), h = Math.round(inch(sz.h) * o[0]), big = w * h > 120e6;
          return '<label class="io-dr' + (big ? ' io-off' : '') + '"><input type="radio" name="dpi" value="' + o[0] + '"' + (o[0] === cur && !big ? ' checked' : '') + (big ? ' disabled' : '') + '><span class="io-dn"><b>' + o[0] + ' dpi</b> · ' + o[1] + '</span><span class="io-dd">' + (big ? 'Too large for this page size in the browser' : o[2]) + '</span><em>' + w + ' × ' + h + ' px</em></label>';
        }).join('') + '</div><div class="io-row" style="margin:14px 0 0"><button type="button" class="io-b io-x" data-d="x">Cancel</button><button type="button" class="io-b io-ai" data-d="ok">Download</button></div></div>';
      document.body.appendChild(m);
      function done(v) { m.remove(); resolve(v); }
      m.addEventListener('click', function (e) {
        if (e.target === m) { done(0); return; }
        var b = e.target.closest('[data-d]'); if (!b) return;
        if (b.dataset.d === 'x') { done(0); return; }
        var r = m.querySelector('input[name="dpi"]:checked'); var v = r ? +r.value : 300; DPI_LAST[kind] = v; done(v);
      });
      m.addEventListener('keydown', function (e) { if (e.key === 'Escape') done(0); if (e.key === 'Enter') { var r = m.querySelector('input[name="dpi"]:checked'); done(r ? +r.value : 300); } });
    });
  }
  function vxReady() {
    if (window.DSVX && window.DSVX.read) return Promise.resolve();
    return Promise.reject(new Error('The vector export is not available on this page yet. Please reload the page and try again.'));
  }
  function exportPages(doc, kind) {
    if (expBusy) return;
    if (kind === 'vec') { runExport(doc, kind, 0); return; }
    askDpi(doc, kind).then(function (dpi) { if (dpi) runExport(doc, kind, dpi); });
  }
  function runExport(doc, kind, dpi) {
    if (expBusy) return; expBusy = true;
    var ui = expProgress(), base = fileName(doc), st = null, out = [];
    var libs = [window.JSZip ? Promise.resolve() : loadScript('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js')];
    if (kind !== 'vec') libs.push(window.html2canvas ? Promise.resolve() : loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js'));
    else libs.push(vxReady());
    var scale = dpi / 96, mime = kind === 'jpg' ? 'image/jpeg' : 'image/png';
    Promise.all(libs).then(function () { ui.set('Laying out the pages…', 6); return exportStage(doc); }).then(function (s0) {
      st = s0; var n = st.pages.length, i = 0;
      if (!n) throw new Error('Nothing to download yet.');
      function next() {
        if (i >= n) return Promise.resolve();
        var pg = st.pages[i], k = i; i++;
        ui.set('Page ' + (k + 1) + ' of ' + n + (dpi ? ' at ' + dpi + ' dpi' : '') + '…', 8 + (k / n) * 84);
        var box = pageClip(st, pg);
        var job;
        if (kind === 'vec') {
          job = Promise.resolve().then(function () {
            var R = window.DSVX.read(window, box, st.w, st.h);
            return Promise.resolve(window.DSVX.resolvePending(R, window)).then(function () { return window.DSVX.encodeImages(R); }).then(function () {
              var t = base + ' - page ' + (k + 1);
              return Promise.resolve(window.DSVX.toEPS(R, t)).then(function (eps) { out.push({ svg: window.DSVX.toSVG(R, t), eps: eps }); });
            });
          });
        } else {
          job = window.html2canvas(box, { scale: scale, backgroundColor: '#ffffff', useCORS: true, allowTaint: false, logging: false, width: st.w, height: st.h, windowWidth: Math.max(st.w + 20, document.documentElement.clientWidth) }).then(function (cv) {
            if (!cv || cv.width < 2) throw new Error('big');
            if (kind === 'tiff') return tiffPage(cv).then(function (pz) { out.push(pz); });
            return new Promise(function (r, rej) { cv.toBlob(function (bl) { cv.width = 1; cv.height = 1; if (!bl) rej(new Error('big')); else r(bl); }, mime, 0.95); })
              .then(function (bl) { return withDpi(bl, kind, dpi); }).then(function (bl) { out.push(bl); });
          });
        }
        return job.then(function () { box.remove(); return next(); }, function (er) { box.remove(); throw er; });
      }
      return next();
    }).then(function () {
      ui.set('Finishing your file…', 94);
      var nn = function (k) { return String(k + 1).padStart(2, '0'); }, tag = dpi ? ' (' + dpi + ' dpi)' : '';
      if (kind === 'tiff') { saveBlob(tiffBuild(out, dpi), base + tag + '.tiff'); return; }
      if (kind === 'vec') {
        var zip = new window.JSZip();
        out.forEach(function (o, k) { zip.file('SVG/' + base + ' - page ' + nn(k) + '.svg', o.svg); zip.file('EPS/' + base + ' - page ' + nn(k) + '.eps', o.eps); });
        return zip.generateAsync({ type: 'blob', compression: 'DEFLATE' }).then(function (b) { saveBlob(b, base + ' - EPS + SVG.zip'); });
      }
      var ext = kind === 'jpg' ? '.jpg' : '.png';
      if (out.length === 1) { saveBlob(out[0], base + tag + ext); return; }
      var zip2 = new window.JSZip();
      out.forEach(function (b, k) { zip2.file(base + ' - page ' + nn(k) + ext, b); });
      return zip2.generateAsync({ type: 'blob' }).then(function (b) { saveBlob(b, base + ' - ' + kind.toUpperCase() + tag + '.zip'); });
    }).then(function () {
      ui.close(); toast('Downloaded.', 'ok');
    }).catch(function (e) {
      ui.close();
      var big = e && (e.message === 'big' || /memory|allocation|canvas/i.test(e.message || ''));
      toast(big ? 'That resolution is too large for the browser. Please try a lower dpi.' : (e.message || 'The file could not be made.'), 'err', 8000);
      if (window.console) console.warn('[DS doc export]', e);
    }).then(function () { expBusy = false; if (st && st.wrap) st.wrap.remove(); });
  }
  function printPdf(doc) {
    var sz = D.size(doc), wrap = el('div', 'dd-print');
    wrap.style.cssText = 'position:absolute;left:0;top:0;width:' + sz.w + 'px;background:#fff';
    document.body.appendChild(wrap);
    var a = D.render(wrap, doc, { edit: false, gap: 0 });
    $$('.dsd-page', wrap).forEach(function (p) { p.style.boxShadow = 'none'; });
    var st = el('style'); st.textContent = '@page{size:' + (sz.w / 96).toFixed(3) + 'in ' + (sz.h / 96).toFixed(3) + 'in;margin:0}@media print{html,body{background:#fff!important}.dsd-doc{gap:0!important}.dsd-page{break-after:page}}';
    document.head.appendChild(st);
    document.body.classList.add('dd-printing');
    var done = function () { document.body.classList.remove('dd-printing'); wrap.remove(); st.remove(); window.removeEventListener('afterprint', done); };
    window.addEventListener('afterprint', done);
    (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(function () { a.layout(); setTimeout(function () { window.print(); setTimeout(done, 1500); }, 300); });
  }

  /* ---------------- editor ---------------- */
  var ED = null;
  function openEditor(doc, docId) {
    if (ED) ED.close(true);
    ED = new Editor(doc, docId);
  }

  function Editor(doc, docId) {
    try { if (D.detachArt && !D.artOn(doc)) D.detachArt(doc); } catch (er) { /* keep the drawn design */ }
    var self = this, sel = null, dirty = false, hist = [], fut = [], zoom = 1, lastSaved = JSON.stringify(forSave(doc)), api2 = null;
    var host = el('div', 'dd-ed');
    host.innerHTML = '<div class="dd-top"><button class="bk" data-e="back" type="button">← BACK</button><span class="dd-tsep"></span><input type="text" id="dde-title" aria-label="Document title">' +
      '<button class="dd-ren" data-e="rename" type="button" title="Rename the document"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/></svg></button>' +
      '<span class="dd-saved" id="dde-saved"></span><span class="sp"></span>' +
      '<button class="eb pri" data-e="save" type="button">💾 SAVE</button><button class="eb pri" data-e="download" type="button">⬇ DOWNLOAD ▾</button></div>' +
      '<div class="dd-left"><h5>Pages</h5><button class="dstyle" data-e="style" type="button">🎨<span><b>Document style</b><span>Design · colours · fonts</span></span></button><ul class="ol" id="dde-outline"></ul>' +
      '<button class="eb" data-e="addsec" type="button" style="width:100%;margin-top:10px;justify-content:center">+ ADD SECTION</button></div>' +
      '<div class="dd-canvas" id="dde-canvas"><div class="dd-zoom" id="dde-zoom"><div id="dde-doc"></div></div></div>' +
      '<div class="dd-right"><div class="rtabs"><button data-tab="design" type="button" class="on">DESIGN</button><button data-tab="colours" type="button">COLOURS</button><button data-tab="block" type="button">BLOCK</button><button data-tab="ai" type="button">✨ AI</button></div><div class="rpane" id="dde-pane"></div></div>' +
      '<div class="dd-bot"><button class="eb pri" data-e="tab-colours" type="button">🎨 THEME</button>' +
      '<div class="dd-pill"><button data-e="pgprev" type="button" title="Previous page">&#8249;</button><span class="pg" id="dde-pg">1 / 1</span><button data-e="pgnext" type="button" title="Next page">&#8250;</button><span class="dd-pdiv"></span>' +
      '<button data-e="zout" type="button" title="Zoom out">−</button><span id="dde-z">100%</span><button data-e="zin" type="button" title="Zoom in">+</button><button data-e="zfit" type="button" title="Fit width">⤢</button></div></div>';
    document.body.appendChild(host); document.body.classList.add('dd-noscroll');
    var canvas = $('#dde-canvas', host), zoomBox = $('#dde-zoom', host), docBox = $('#dde-doc', host), pane = $('#dde-pane', host), ttl = $('#dde-title', host);
    ttl.value = doc.title || '';
    var tab = 'design';

    function snapshot() { hist.push(JSON.stringify(doc)); if (hist.length > 80) hist.shift(); fut = []; }
    function changed() { dirty = true; $('#dde-saved', host).textContent = 'Unsaved changes'; autosave(); }
    var autosave = debounce(function () { if (dirty && CFG.loggedIn) doSave(true); }, 20000);
    var typingSnap = debounce(function () { snapshot(); }, 900);

    function render(keepScroll) {
      var st = canvas.scrollTop;
      try { if (D.detachArt && !D.artOn(doc)) D.detachArt(doc); } catch (er) { /* keep the drawn design */ }
      api2 = D.render(docBox, doc, { edit: true, gap: 28 });
      if (LE) LE.onRender(api2);
      var sz = D.size(doc);
      docBox.style.width = sz.w + 'px';
      applyZoom();
      drawOutline();
      if (keepScroll) canvas.scrollTop = st;
      if (sel && !docBox.querySelector('.dsd-blk[data-id="' + sel + '"]')) sel = null;
      placeToolbar();
      pageCount();
    }
    self.repaint = function () { render(true); };
    var relayout = debounce(function () { if (api2) { if (LE && LE.busy()) return; api2.layout(); applyZoom(); pageCount(); placeToolbar(); drawOutline(); } }, 350);
    function goPage(dir) {
      var pages = $$('.dsd-page', docBox).filter(function (p) { return p.offsetParent; }).map(function (p) { return p.getBoundingClientRect().top; }).sort(function (a, b) { return a - b; });
      var cr = canvas.getBoundingClientRect(), cur = 0;
      pages.forEach(function (t, i) { if (t <= cr.top + cr.height / 3) cur = i; });
      var to = Math.max(0, Math.min(pages.length - 1, cur + dir)); if (pages[to] != null) canvas.scrollTop += pages[to] - cr.top - 24;
    }
    function applyZoom() {
      var sz = D.size(doc);
      docBox.style.transform = 'scale(' + zoom + ')';
      /* free room on every side, so Space + drag (or the middle mouse button) moves the page left, right, up and down */
      var cw = canvas.clientWidth - 40, dw = sz.w * zoom, pad = Math.round(canvas.clientWidth * 0.6), padT = Math.round(canvas.clientHeight * 0.35);
      var x0 = docBox.getBoundingClientRect().left, y0 = docBox.getBoundingClientRect().top, first = zoomBox.__pad == null;
      zoomBox.style.width = Math.ceil(dw) + 'px';
      zoomBox.style.height = Math.ceil(docBox.scrollHeight * zoom) + 'px';
      zoomBox.style.boxSizing = 'content-box';
      zoomBox.style.marginLeft = (pad + Math.max(0, (cw - dw) / 2)) + 'px';
      zoomBox.style.marginRight = '0px'; zoomBox.style.paddingRight = pad + 'px';
      zoomBox.style.marginTop = padT + 'px';
      zoomBox.style.marginBottom = Math.round(canvas.clientHeight * 0.4) + 'px';
      if (first) { canvas.scrollLeft = pad; canvas.scrollTop += padT; }
      else { var r1 = docBox.getBoundingClientRect(); canvas.scrollLeft += r1.left - x0; canvas.scrollTop += r1.top - y0; }   /* the page stays where it was */
      zoomBox.__pad = pad; zoomBox.__padT = padT;
      $('#dde-z', host).textContent = Math.round(zoom * 100) + '%';
      if (LE) { LE.sync(); LE.place(); }
    }
    function fit() { var sz = D.size(doc); zoom = Math.max(0.3, Math.min(1.5, (canvas.clientWidth - 70) / sz.w)); applyZoom(); canvas.scrollLeft = zoomBox.__pad || 0; canvas.scrollTop = Math.max(0, (zoomBox.__padT || 0) - 20); placeToolbar(); }
    /* zoom keeping the point under the pointer still (Ctrl/Cmd + wheel, trackpad pinch, Ctrl +/-) */
    var ZMIN = 0.1, ZMAX = 4;
    function zoomAt(nz, cx, cy) {
      nz = Math.max(ZMIN, Math.min(ZMAX, nz)); if (Math.abs(nz - zoom) < 0.0005) return;
      var cr = canvas.getBoundingClientRect(); if (cx == null) { cx = cr.left + cr.width / 2; cy = cr.top + cr.height / 2; }
      var r = docBox.getBoundingClientRect(), px = (cx - r.left) / zoom, py = (cy - r.top) / zoom;
      zoom = Math.round(nz * 1000) / 1000; applyZoom();
      var r2 = docBox.getBoundingClientRect();
      canvas.scrollLeft += r2.left + px * zoom - cx; canvas.scrollTop += r2.top + py * zoom - cy;
      if (LE) { LE.sync(); LE.place(); } placeToolbar(); pageCount();
    }
    canvas.addEventListener('wheel', function (e) {
      if (!(e.ctrlKey || e.metaKey)) return;
      e.preventDefault();
      var d = e.deltaMode === 1 ? e.deltaY * 16 : e.deltaY;
      zoomAt(zoom * Math.exp(-Math.max(-60, Math.min(60, d)) * 0.0085), e.clientX, e.clientY);
    }, { passive: false });
    /* Safari pinch */
    var gz = 1; canvas.addEventListener('gesturestart', function (e) { e.preventDefault(); gz = zoom; });
    canvas.addEventListener('gesturechange', function (e) { e.preventDefault(); zoomAt(gz * e.scale, e.clientX, e.clientY); });
    /* hand tool: hold Space and drag, or drag with the middle mouse button */
    var spaceDown = false, panning = null;
    function typingIn(t) { return t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)); }
    function onSpace(e) {
      if (e.code !== 'Space' && e.key !== ' ') return;
      if (typingIn(e.target) || typingIn(document.activeElement)) return;
      if (e.type === 'keydown') { e.preventDefault(); if (!spaceDown) { spaceDown = true; canvas.classList.add('dd-hand'); } }
      else { spaceDown = false; if (!panning) canvas.classList.remove('dd-hand'); }
    }
    document.addEventListener('keydown', onSpace, true); document.addEventListener('keyup', onSpace, true);
    window.addEventListener('blur', function () { spaceDown = false; canvas.classList.remove('dd-hand'); });
    canvas.addEventListener('pointerdown', function (e) {
      if (!(spaceDown && e.button === 0) && e.button !== 1) return;
      e.preventDefault(); e.stopPropagation();
      panning = { x: e.clientX, y: e.clientY, sl: canvas.scrollLeft, st: canvas.scrollTop };
      canvas.classList.add('dd-hand', 'dd-panning');
      try { canvas.setPointerCapture(e.pointerId); } catch (er) { /* ignore */ }
    }, true);
    canvas.addEventListener('pointermove', function (e) {
      if (!panning) return; e.preventDefault(); e.stopPropagation();
      canvas.scrollLeft = panning.sl - (e.clientX - panning.x); canvas.scrollTop = panning.st - (e.clientY - panning.y);
    }, true);
    function endPan(e) { if (!panning) return; panning = null; canvas.classList.remove('dd-panning'); if (!spaceDown) canvas.classList.remove('dd-hand'); if (e) e.stopPropagation(); if (LE) LE.place(); }
    canvas.addEventListener('pointerup', endPan, true); canvas.addEventListener('pointercancel', endPan, true);
    canvas.addEventListener('auxclick', function (e) { if (e.button === 1) e.preventDefault(); });
    function pageCount() {
      var n = api2 ? api2.pages : 1, pageH = (D.size(doc).h + 28) * zoom, cur = Math.min(n, Math.floor((canvas.scrollTop - (zoomBox.__padT || 0) + 120) / pageH) + 1);
      $('#dde-pg', host).textContent = cur + ' / ' + n;
    }
    canvas.addEventListener('scroll', function () { pageCount(); });

    function drawOutline() {
      var ol = $('#dde-outline', host), items = [], n = 0;
      (doc.blocks || []).forEach(function (b) { if (b.t === 'h1' || b.t === 'r_sec' || b.t === 's_title' || b.t === 'title') { n++; items.push({ id: b.id, n: n, text: D.runs(b.text || '').map(function (r) { return r.t; }).join('') || '(untitled)' }); } });
      var dz = D.design(doc.tpl);
      var head = dz.kind === 'flow' && dz.cover ? '<li data-go="cover"><b>◼</b><span>Cover</span></li>' : '';
      ol.innerHTML = head + items.map(function (it) { return '<li data-go="' + it.id + '"' + (it.id === sel ? ' class="on"' : '') + '><b>' + ('0' + it.n).slice(-2) + '</b><span>' + esc(it.text) + '</span></li>'; }).join('') || '<li><span>No sections yet</span></li>';
    }

    /* ---------- selection & toolbar ---------- */
    var tb = el('div', 'ftb dd-hide');
    var LE = null;          /* the element editor (top bar, bottom bar, panels, layers) - made at the end */
    function blockOf(id) { for (var i = 0; i < doc.blocks.length; i++) if (doc.blocks[i].id === id) return i; return -1; }
    function select(id) {
      if (LE) { if (id) LE.selectBlock(id); else LE.clear(); }
      sel = id; drawOutline();
      if (tab === 'block' || tab === 'ai') drawPane();
    }
    function placeToolbar() { if (LE) LE.sync(); }
    function placeToolbarOld() {
      var node = sel && docBox.querySelector('.dsd-blk[data-id="' + sel + '"]');
      if (!node) { tb.classList.add('dd-hide'); return; }
      var b = doc.blocks[blockOf(sel)];
      if (!b) { tb.classList.add('dd-hide'); return; }
      var cr = canvas.getBoundingClientRect(), r = node.getBoundingClientRect();
      tb.innerHTML = '<button data-b="type" title="Change block type">' + esc(TYPE_LABEL[b.t] || 'Block') + ' ▾</button><span class="sep"></span>' +
        (RICH[b.t] ? '<button data-b="bold" title="Bold"><b>B</b></button><button data-b="italic" title="Italic"><i>I</i></button><span class="sep"></span>' : '') +
        '<button data-b="ai" title="AI writing">✨ AI ▾</button>' + (b.t === 'image' ? '<button data-b="pic">🖼 Change picture</button>' : '') + (b.t === 'chart' ? '<button data-b="chart">📊 Edit chart</button>' : '') +
        '<span class="sep"></span><button data-b="up" title="Move up">↑</button><button data-b="down" title="Move down">↓</button><button data-b="dup" title="Duplicate">⧉</button><button data-b="del" title="Delete">🗑</button><span class="sep"></span><button data-b="add" title="Add below">＋ ▾</button>';
      tb.classList.remove('dd-hide');
      var top = r.top - cr.top + canvas.scrollTop - 42, left = r.left - cr.left + canvas.scrollLeft;
      tb.style.top = Math.max(canvas.scrollTop + 4, top) + 'px'; tb.style.left = Math.max(4, left) + 'px';
    }
    var TYPE_LABEL = { chart: 'Chart', h1: 'Heading', h2: 'Subheading', h3: 'Small heading', p: 'Paragraph', ul: 'Bullets', ol: 'Numbered', check: 'Checklist', quote: 'Quote', callout: 'Callout', stats: 'Stats', table: 'Table', timeline: 'Timeline', cols: 'Columns', kv: 'Details', image: 'Picture', pb: 'Page break', toc: 'Contents', lh: 'Letterhead', title: 'Title', addr: 'Address', sign: 'Sign-off', inv: 'Invoice items', todo: 'To-do table', week: 'Week', cal: 'Calendar', sigs: 'Signatures', r_head: 'Name', r_sec: 'Section', r_entry: 'Entry', r_skills: 'Skills', s_kicker: 'Small line above the heading', s_title: 'Heading', s_sub: 'Short line', s_text: 'Text', s_cta: 'Button', s_details: 'Details', s_contact: 'Contact details', s_mark: 'Logo', s_qr: 'QR code', s_photo: 'Photo', s_menu: 'Menu', s_tickets: 'Tickets', s_cert: 'Certificate' };
    var RICH = { p: 1, ul: 1, ol: 1, check: 1, quote: 1, callout: 1, cols: 1, timeline: 1, table: 1, r_entry: 1, s_text: 1, h1: 1, h2: 1, h3: 1, kv: 1, addr: 0 };
    var CONVERT = ['h1', 'h2', 'h3', 'p', 'ul', 'ol', 'check', 'quote', 'callout'];
    function blockText(b) {
      if (b.items && typeof b.items[0] === 'string') return b.items.join('\n');
      if (b.text != null) return b.text;
      if (b.t === 'callout') return b.text;
      return '';
    }
    function convert(b, to) {
      var text = blockText(b), n = { t: to, id: b.id };
      if (to === 'ul' || to === 'ol' || to === 'check') n.items = text.split(/\n+/).filter(Boolean); else n.text = text.replace(/\n+/g, ' ');
      if (to === 'callout') { n.title = b.title || 'Note'; n.tone = 'info'; }
      if (to === 'quote') n.by = b.by || '';
      return n;
    }
    function NEW(t) {
      var b = { t: t, id: D.uid() };
      switch (t) {
        case 'h1': b.text = 'New section'; break; case 'h2': b.text = 'Subheading'; break; case 'p': b.text = ''; break;
        case 'ul': case 'ol': case 'check': b.items = ['']; break;
        case 'quote': b.text = 'A short, memorable quote.'; b.by = 'Name'; break;
        case 'callout': b.title = 'Good to know'; b.text = 'Add a short note here.'; b.tone = 'info'; break;
        case 'stats': b.items = [{ v: '75%', l: 'Label' }, { v: '3x', l: 'Label' }, { v: '24/7', l: 'Label' }]; break;
        case 'table': b.head = ['Column', 'Column', 'Column']; b.rows = [['', '', ''], ['', '', '']]; break;
        case 'timeline': b.items = [{ d: 'Step 1', title: 'Title', text: 'Details' }, { d: 'Step 2', title: 'Title', text: 'Details' }]; break;
        case 'cols': b.items = [{ title: 'Title', text: 'Text' }, { title: 'Title', text: 'Text' }]; break;
        case 'kv': b.rows = [['Label', 'Value'], ['Label', 'Value']]; break;
        case 'image': b.q = ''; b.cap = 'Caption'; b.w = 1; break;
        case 'chart': b.kind = 'column'; b.title = 'Chart title'; b.labels = ['Q1', 'Q2', 'Q3', 'Q4']; b.series = [{ name: 'Series 1', values: [12, 19, 15, 24] }]; b.legend = true; b.vals = true; b.unit = ''; b.cap = ''; break;
        case 'sigs': b.items = [{ name: 'Name', role: 'Role' }, { name: 'Name', role: 'Role' }]; break;
        case 'r_entry': b.title = 'Role'; b.org = 'Organisation'; b.date = '2024 – Present'; b.items = ['Achievement']; break;
        case 'r_sec': b.text = 'Section'; break;
        case 'r_skills': b.items = ['Skill', 'Skill']; break;
      }
      return b;
    }
    var ADDABLE = [['p', 'Paragraph'], ['h1', 'Section heading'], ['h2', 'Subheading'], ['ul', 'Bullet list'], ['ol', 'Numbered list'], ['check', 'Checklist'], ['table', 'Table'], ['stats', 'Key numbers'], ['timeline', 'Timeline'], ['cols', 'Two columns'], ['callout', 'Callout box'], ['quote', 'Quote'], ['kv', 'Details table'], ['image', 'Picture'], ['chart', 'Chart'], ['sigs', 'Signature lines'], ['pb', 'Page break']];
    var ADD_RESUME = [['r_sec', 'Resume section'], ['r_entry', 'Job / education entry'], ['r_skills', 'Skills'], ['p', 'Paragraph'], ['ul', 'Bullet list']];
    var AI_ACTS = [['improve', 'Improve writing'], ['rewrite', 'Rewrite'], ['shorten', 'Make shorter'], ['expand', 'Make longer'], ['grammar', 'Fix grammar'], ['summarize', 'Summarise'], ['bullets', 'Turn into bullets'], ['continue', 'Continue writing'], ['tone', 'Change tone…'], ['translate', 'Translate…']];

    var menuEl = null;
    function closeMenu() { if (menuEl) { menuEl.remove(); menuEl = null; } }
    function menu(anchor, items, onPick) {
      closeMenu();
      menuEl = el('div', 'menu');
      menuEl.innerHTML = items.map(function (it) { return it[0] === '-' ? '<div class="mh">' + esc(it[1]) + '</div>' : '<button type="button" data-v="' + esc(it[0]) + '">' + esc(it[1]) + '</button>'; }).join('');
      canvas.appendChild(menuEl);
      var cr = canvas.getBoundingClientRect(), r = anchor.getBoundingClientRect();
      menuEl.style.top = (r.bottom - cr.top + canvas.scrollTop + 4) + 'px'; menuEl.style.left = (r.left - cr.left + canvas.scrollLeft) + 'px';
      menuEl.addEventListener('click', function (e) { var b = e.target.closest('[data-v]'); if (!b) return; var v = b.dataset.v; closeMenu(); onPick(v); });
    }
    tb.addEventListener('mousedown', function (e) { e.preventDefault(); }); // keep text selection for bold/italic
    tb.addEventListener('click', function (e) {
      var b = e.target.closest('[data-b]'); if (!b) return;
      var i = blockOf(sel); if (i < 0) return;
      var blk = doc.blocks[i], act = b.dataset.b;
      if (act === 'bold' || act === 'italic') { document.execCommand(act); var f = document.activeElement; if (f && f.isContentEditable) f.dispatchEvent(new Event('input', { bubbles: true })); return; }
      if (act === 'type') {
        if (CONVERT.indexOf(blk.t) < 0) { toast('This block keeps its type. Use ＋ to add a different block.'); return; }
        menu(b, CONVERT.map(function (t) { return [t, TYPE_LABEL[t]]; }), function (v) { snapshot(); doc.blocks[i] = convert(blk, v); changed(); render(true); select(blk.id); });
        return;
      }
      if (act === 'ai') { menu(b, AI_ACTS, function (v) { aiBlock(blk, v); }); return; }
      if (act === 'chart' && LE) { LE.editChart(blk, function (c) { snapshot(); ['kind', 'title', 'labels', 'series', 'legend', 'vals', 'unit'].forEach(function (k) { blk[k] = c[k]; }); changed(); render(true); select(blk.id); }); return; }
      if (act === 'pic') { pickPicture(function (p) { snapshot(); blk.src = p.data || p.src; blk.pid = p.pid || 0; blk.url = p.url || ''; changed(); render(true); }); return; }
      if (act === 'up' && i > 0) { snapshot(); doc.blocks.splice(i - 1, 0, doc.blocks.splice(i, 1)[0]); changed(); render(true); return; }
      if (act === 'down' && i < doc.blocks.length - 1) { snapshot(); doc.blocks.splice(i + 1, 0, doc.blocks.splice(i, 1)[0]); changed(); render(true); return; }
      if (act === 'dup') { snapshot(); var c = D.clone(blk); c.id = D.uid(); doc.blocks.splice(i + 1, 0, c); changed(); render(true); select(c.id); return; }
      if (act === 'del') { snapshot(); doc.blocks.splice(i, 1); sel = null; changed(); render(true); return; }
      if (act === 'add') {
        var list = D.design(doc.tpl).kind === 'resume' ? ADD_RESUME.concat(ADDABLE.slice(1)) : ADDABLE;
        menu(b, list, function (v) { snapshot(); var nb = NEW(v); doc.blocks.splice(i + 1, 0, nb); changed(); render(true); select(nb.id); focusBlock(nb.id); if (v === 'image') pickPicture(function (p) { nb.src = p.data || p.src; nb.pid = p.pid || 0; nb.url = p.url || ''; render(true); }); if (v === 'chart' && LE) LE.editChart(nb, function (c) { ['kind', 'title', 'labels', 'series', 'legend', 'vals', 'unit'].forEach(function (k) { nb[k] = c[k]; }); changed(); render(true); }); });
      }
    });
    function focusBlock(id) {
      setTimeout(function () { var n = docBox.querySelector('.dsd-blk[data-id="' + id + '"] [contenteditable]'); if (n) { n.focus(); placeCaretEnd(n); } }, 30);
    }
    function placeCaretEnd(n) { try { var r = document.createRange(); r.selectNodeContents(n); r.collapse(false); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); } catch (e) { /* ignore */ } }

    /* ---------- editing text in place ---------- */
    function setPath(obj, path, val) {
      var ps = path.split('.'), o = obj;
      for (var i = 0; i < ps.length - 1; i++) { var k = /^\d+$/.test(ps[i]) ? +ps[i] : ps[i]; if (o[k] == null) o[k] = /^\d+$/.test(ps[i + 1]) ? [] : {}; o = o[k]; }
      var last = /^\d+$/.test(ps[ps.length - 1]) ? +ps[ps.length - 1] : ps[ps.length - 1];
      o[last] = val;
    }
    function getPath(obj, path) { return path.split('.').reduce(function (o, k) { return o == null ? o : o[/^\d+$/.test(k) ? +k : k]; }, obj); }
    docBox.addEventListener('input', function (e) {
      var n = e.target.closest('[contenteditable]'); if (!n) return;
      if (n.dataset.df) { doc[n.dataset.df] = n.textContent; if (n.dataset.df === 'title') ttl.value = doc.title; }
      else {
        var blkEl = n.closest('.dsd-blk'), i = blkEl ? blockOf(blkEl.dataset.id) : -1; if (i < 0) return;
        var val = n.dataset.r ? D.htmlToText(n.innerHTML) : n.textContent;
        var f = n.dataset.f, b = doc.blocks[i];
        if (b.t === 'inv' && /\.(q|r)$/.test(f)) val = val.replace(/[^\d.\-]/g, '');
        setPath(b, f, val);
        if (b.t === 'inv') { clearTimeout(n.__t); n.__t = setTimeout(function () { render(true); }, 1200); }
      }
      typingSnap(); changed(); relayout();
    });
    docBox.addEventListener('focusin', function (e) { var b = e.target.closest('.dsd-blk'); if (b && b.dataset.id !== sel && LE && !LE.busy() && LE.selected().indexOf('B:' + b.dataset.id) < 0) select(b.dataset.id); });
    docBox.addEventListener('click', function (e) {
      var act = e.target.closest('[data-act="cover-pic"]');
      if (act) { pickPicture(function (p) { snapshot(); doc.cover = doc.cover || {}; doc.cover.src = p.data || p.src; doc.cover.pid = p.pid || 0; doc.cover.url = p.url || ''; changed(); render(true); }); return; }
      var icn = e.target.closest('.dsd-s-ct .ic[data-ic]');
      if (icn) { var cb = doc.blocks[blockOf(icn.closest('.dsd-blk').dataset.id)], ri = +icn.dataset.ic; if (cb && cb.rows && cb.rows[ri]) { var ord = D.ICON_ORDER || ['phone', 'mail', 'web', 'pin']; snapshot(); cb.rows[ri].i = ord[(ord.indexOf(cb.rows[ri].i) + 1) % ord.length]; changed(); render(true); } return; }
      var b = e.target.closest('.dsd-blk');
      if (b && b.dataset.t === 's_photo' && e.target.closest('.dsd-s-ph')) { pickPicture(function (p) { var bb = doc.blocks[blockOf(b.dataset.id)]; snapshot(); bb.src = p.data || p.src; bb.pid = p.pid || 0; bb.url = p.url || ''; changed(); render(true); }); return; }
      if (b) { if (b.dataset.t === 'image' && e.target.closest('.ph')) pickPicture(function (p) { var bb = doc.blocks[blockOf(b.dataset.id)]; snapshot(); bb.src = p.data || p.src; bb.pid = p.pid || 0; bb.url = p.url || ''; changed(); render(true); }); }
    });
    docBox.addEventListener('keydown', function (e) {
      var n = e.target.closest('[contenteditable]'); if (!n || !n.dataset.f) return;
      var blkEl = n.closest('.dsd-blk'), i = blockOf(blkEl.dataset.id), b = doc.blocks[i];
      var m = /^items\.(\d+)$/.exec(n.dataset.f);
      if (e.key === 'Enter' && !e.shiftKey && m && (b.t === 'ul' || b.t === 'ol' || b.t === 'check' || b.t === 'r_entry' || b.t === 'r_skills')) {
        e.preventDefault(); snapshot(); b.items.splice(+m[1] + 1, 0, ''); changed(); render(true);
        setTimeout(function () { var x = docBox.querySelector('.dsd-blk[data-id="' + b.id + '"] [data-f="items.' + (+m[1] + 1) + '"]'); if (x) x.focus(); }, 20);
        return;
      }
      if (e.key === 'Backspace' && m && !n.textContent && b.items.length > 1) {
        e.preventDefault(); snapshot(); b.items.splice(+m[1], 1); changed(); render(true);
        setTimeout(function () { var x = docBox.querySelector('.dsd-blk[data-id="' + b.id + '"] [data-f="items.' + Math.max(0, +m[1] - 1) + '"]'); if (x) { x.focus(); placeCaretEnd(x); } }, 20);
        return;
      }
      if (e.key === 'Enter' && !e.shiftKey && (b.t === 'p' || b.t === 'h1' || b.t === 'h2' || b.t === 'h3') && n.dataset.f === 'text') {
        e.preventDefault(); snapshot(); var nb = NEW('p'); doc.blocks.splice(i + 1, 0, nb); changed(); render(true); select(nb.id); focusBlock(nb.id);
        return;
      }
      if (e.key === 'Backspace' && b.t === 'p' && !n.textContent && doc.blocks.length > 1) {
        e.preventDefault(); snapshot(); doc.blocks.splice(i, 1); var prev = doc.blocks[Math.max(0, i - 1)]; changed(); render(true); if (prev) { select(prev.id); focusBlock(prev.id); }
      }
    });
    // paste as plain text
    docBox.addEventListener('paste', function (e) {
      var n = e.target.closest('[contenteditable]'); if (!n) return;
      e.preventDefault();
      var txt = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, txt);
    });

    /* ---------- AI on a block ---------- */
    function aiBlock(blk, action, extra) {
      var text = blockText(blk);
      if (action === 'tone' && !extra) { menu(tb.querySelector('[data-b="ai"]') || tb, TONES.map(function (t) { return [t, t]; }), function (v) { aiBlock(blk, 'tone', { tone: v }); }); return; }
      if (action === 'translate' && !extra) { menu(tb.querySelector('[data-b="ai"]') || tb, LANGS.map(function (t) { return [t, t]; }), function (v) { aiBlock(blk, 'translate', { language: v }); }); return; }
      if (!String(text).trim() && action !== 'write') { toast('This block has no text yet.'); return; }
      if (!CFG.loggedIn) { toast('Please sign in to use AI writing.', 'err'); return; }
      toast('✨ Writing…', '', 30000);
      api('POST', 'ds/v1/doc-assist', Object.assign({ action: action, text: text, language: doc.lang || 'English' }, extra || {})).then(function (r) {
        setCredits(r.credits_balance);
        snapshot();
        var out = String(r.text || '');
        if (action === 'continue') { var nb = { t: 'p', id: D.uid(), text: out }; doc.blocks.splice(blockOf(blk.id) + 1, 0, nb); }
        else if (action === 'bullets') { var lb = { t: 'ul', id: blk.id, items: out.split(/\n+/).map(function (x) { return x.replace(/^\s*[-•*]\s*/, '').trim(); }).filter(Boolean) }; doc.blocks[blockOf(blk.id)] = lb; }
        else if (blk.items && typeof blk.items[0] === 'string') blk.items = out.split(/\n+/).map(function (x) { return x.replace(/^\s*[-•*\d.]+\s*/, '').trim(); }).filter(Boolean);
        else blk.text = out;
        changed(); render(true); toast('Done.', 'ok', 1500);
      }).catch(function (e) { toast(e.message, 'err', 6000); if (e.data && e.data.credits_balance != null) setCredits(e.data.credits_balance); });
    }

    /* ---------- right panel ---------- */
    function drawPane() {
      $$('.rtabs button', host).forEach(function (b) { b.classList.toggle('on', b.dataset.tab === tab); });
      var dz = D.design(doc.tpl);
      if (tab === 'design') {
        pane.innerHTML = '<h5>Designs for this document</h5><div class="rds" id="dde-ds"></div><label class="rlab">Show</label><select id="dde-dsall"><option value="type">Made for this document type</option><option value="kind">All designs of this kind</option></select>';
        var showAll = self._dsAll;
        $('#dde-dsall', pane).value = showAll ? 'kind' : 'type';
        var list = showAll ? D.DESIGNS.filter(function (d) { return d.kind === dz.kind; }) : D.designsFor(doc.type);
        if (!list.length) list = D.DESIGNS.filter(function (d) { return d.kind === dz.kind; });
        var box = $('#dde-ds', pane);
        list.forEach(function (d) {
          var b = el('button', d.id === doc.tpl ? 'on' : ''); b.type = 'button'; b.dataset.d = d.id;
          var pv = el('div', 'pv'); b.appendChild(pv); b.appendChild(el('b', '', esc(d.name)));
          box.appendChild(b);
          var mini = D.clone(doc); mini.tpl = d.id; mini.blocks = mini.blocks.slice(0, 30);
          if (mini.cover) mini.cover = { src: doc.cover && doc.cover.src };
          mockup(pv, mini, d);
        });
        $('#dde-dsall', pane).onchange = function (e) { self._dsAll = e.target.value === 'kind'; drawPane(); };
        box.onclick = function (e) { var b = e.target.closest('[data-d]'); if (!b) return; snapshot(); doc.tpl = b.dataset.d; changed(); render(true); drawPane(); fillPictures(doc).then(function () { render(true); }); };
      } else if (tab === 'colours') {
        var own = D.theme(dz.theme), t = D.look(doc);
        var fonts = Object.keys(D.FONTS);
        pane.innerHTML = '<h5>Colour themes</h5><div class="rth" id="dde-th">' + [{ id: '', name: 'Design colours', t: own }].concat(D.THEMES.filter(function (x) { return !x.hide; }).map(function (x) { return { id: x.id, name: x.name, t: x }; })).map(function (x) {
          return '<button type="button" data-th="' + x.id + '" class="' + ((doc.theme || '') === x.id ? 'on' : '') + '"><div class="sw"><i style="background:' + x.t.p + '"></i><i style="background:' + x.t.s + '"></i><i style="background:' + x.t.a + '"></i><i style="background:' + x.t.tint + '"></i></div>' + esc(x.name) + '</button>';
        }).join('') + '</div><label class="rlab">Heading font</label><select id="dde-hf">' + fonts.map(function (f) { return '<option' + (f === t.head ? ' selected' : '') + '>' + esc(f) + '</option>'; }).join('') + '</select>' +
          '<label class="rlab">Body font</label><select id="dde-bf">' + fonts.map(function (f) { return '<option' + (f === t.body ? ' selected' : '') + '>' + esc(f) + '</option>'; }).join('') + '</select>' +
          '<label class="rlab">Page size</label><select id="dde-sz">' + (D.design(doc.tpl).sizes || (D.sizesFor ? D.sizesFor(doc.type) : ['A4', 'Letter'])).map(function (k) { return '<option value="' + k + '">' + esc((D.SIZES[k] && D.SIZES[k].label) || k) + '</option>'; }).join('') + '</select>' +
          '<label class="rlab">Language of the text</label><select id="dde-lang">' + LANGS.map(function (l) { return '<option' + (l === (doc.lang || 'English') ? ' selected' : '') + '>' + esc(l) + '</option>'; }).join('') + '</select>' +
          '<p class="rnote" style="margin-top:10px">Fonts are standard Word fonts, so the document looks the same when it is opened in Word.</p>';
        $('#dde-sz', pane).value = D.size(doc).key || doc.size || 'A4';
        $('#dde-th', pane).onclick = function (e) { var b = e.target.closest('[data-th]'); if (!b) return; snapshot(); doc.theme = b.dataset.th; doc.fonts = null; changed(); render(true); drawPane(); };
        $('#dde-hf', pane).onchange = function (e) { snapshot(); doc.fonts = doc.fonts || {}; doc.fonts.head = e.target.value; changed(); render(true); };
        $('#dde-bf', pane).onchange = function (e) { snapshot(); doc.fonts = doc.fonts || {}; doc.fonts.body = e.target.value; changed(); render(true); };
        $('#dde-sz', pane).onchange = function (e) { snapshot(); doc.size = e.target.value; changed(); render(true); fit(); };
        $('#dde-lang', pane).onchange = function (e) { doc.lang = e.target.value; changed(); };
      } else if (tab === 'block') {
        var i = blockOf(sel), b = doc.blocks[i];
        if (!b) { pane.innerHTML = '<p class="rnote">Click a heading, paragraph, table or picture in the document to change it here.</p><h5 style="margin-top:16px">Add to the end</h5><div class="rbtns" id="dde-add"></div>'; drawAdd(); return; }
        var h = '<h5>' + esc(TYPE_LABEL[b.t] || 'Block') + '</h5><div class="rbtns" id="dde-bo">';
        if (b.t === 'table') h += '<button data-o="addrow">+ Row</button><button data-o="delrow">− Row</button><button data-o="addcol">+ Column</button><button data-o="delcol">− Column</button>';
        if (b.t === 'stats' || b.t === 'timeline' || b.t === 'cols' || b.t === 'sigs') h += '<button data-o="additem">+ Item</button><button data-o="delitem">− Item</button>';
        if (b.t === 'kv' || b.t === 's_details') h += '<button data-o="addkv">+ Row</button><button data-o="delkv">− Row</button>';
        if (b.t === 's_contact') h += '<button data-o="addct">+ Row</button><button data-o="delct">− Row</button>';
        if (b.t === 's_mark') h += '<button data-o="logo">🖼 Upload or change logo</button>';
        if (b.dx || b.dy) h += '<button data-o="unmove">↺ Put back in place</button>';
        if (D.design(doc.tpl).kind === 'panels') h += '<button data-o="scdn">− Smaller</button><button data-o="scup">+ Larger</button>' + (b.sc && b.sc !== 1 ? '<button data-o="sc1">Normal size</button>' : '');
        if (b.t === 'inv') h += '<button data-o="addinv">+ Line</button><button data-o="delinv">− Line</button>';
        if (b.t === 'todo') h += '<button data-o="addtodo">+ Task</button><button data-o="deltodo">− Task</button>';
        if (b.t === 'image') h += '<button data-o="pic">🖼 Change picture</button><button data-o="w100">Full width</button><button data-o="w60">Medium</button><button data-o="w40">Small</button>';
        if (b.t === 'callout') h += ['info', 'tip', 'key', 'warn'].map(function (x) { return '<button data-o="tone-' + x + '">' + x + '</button>'; }).join('');
        h += '</div>';
        if (b.t === 'inv') h += '<label class="rlab">Currency symbol</label><input id="dde-cur" value="' + esc(b.cur || '$') + '"><label class="rlab">Tax %</label><input id="dde-tax" type="number" min="0" step="0.5" value="' + esc(b.tax || 0) + '">';
        if (b.t === 'cal') h += '<label class="rlab">Month</label><input id="dde-cal" type="month" value="' + b.y + '-' + ('0' + (+b.m + 1)).slice(-2) + '">';
        if (b.t === 's_tickets') h += '<label class="rlab">First ticket number</label><input id="dde-tk" type="number" min="1" value="' + esc(b.start || 1) + '">';
        h += '<p class="rnote" style="margin-top:12px">' + (D.design(doc.tpl).kind === 'panels' ? 'Drag a text, the logo, the QR code or the photo to move it anywhere on the card. Click text to type. Double-click the logo to upload yours.' : 'Click text in the page to type. Use the toolbar above the block to move, copy, delete or add blocks.') + '</p>';
        pane.innerHTML = h;
        var bo = $('#dde-bo', pane);
        bo.onclick = function (e) {
          var x = e.target.closest('[data-o]'); if (!x) return; var o = x.dataset.o;
          if (o === 'logo') { var lb = document.querySelector('.dsel-b[data-a="logo"]'); if (lb) lb.click(); return; }
          snapshot();
          if (o === 'unmove') { delete b.dx; delete b.dy; }
          if (o === 'scup' || o === 'scdn') { b.sc = Math.round(Math.max(0.3, Math.min(5, D.blockSc(b) * (o === 'scup' ? 1.1 : 1 / 1.1))) * 100) / 100; if (b.sc === 1) delete b.sc; }
          if (o === 'sc1') delete b.sc;
          if (o === 'addrow') b.rows.push((b.head || b.rows[0] || ['']).map(function () { return ''; }));
          if (o === 'delrow' && b.rows.length > 1) b.rows.pop();
          if (o === 'addcol') { (b.head || []).push('Column'); b.rows.forEach(function (r) { r.push(''); }); }
          if (o === 'delcol' && (b.head || b.rows[0]).length > 1) { if (b.head) b.head.pop(); b.rows.forEach(function (r) { r.pop(); }); }
          if (o === 'additem') b.items.push(D.clone(b.items[b.items.length - 1] || {}));
          if (o === 'delitem' && b.items.length > 1) b.items.pop();
          if (o === 'addkv') b.rows.push(['Label', '']);
          if (o === 'addct') b.rows.push({ i: 'link', t: '' });
          if (o === 'delct' && b.rows.length > 1) b.rows.pop();
          if (o === 'delkv' && b.rows.length > 1) b.rows.pop();
          if (o === 'addinv') b.rows.push({ d: 'Item', q: 1, r: 0 });
          if (o === 'delinv' && b.rows.length > 1) b.rows.pop();
          if (o === 'addtodo') b.rows.push({ task: '', due: '', pr: '' });
          if (o === 'deltodo' && b.rows.length > 1) b.rows.pop();
          if (o === 'w100') b.w = 1; if (o === 'w60') b.w = 0.6; if (o === 'w40') b.w = 0.4;
          if (/^tone-/.test(o)) b.tone = o.slice(5);
          if (o === 'pic') { pickPicture(function (p) { b.src = p.data || p.src; b.pid = p.pid || 0; b.url = p.url || ''; changed(); render(true); }); return; }
          changed(); render(true);
        };
        var cur = $('#dde-cur', pane); if (cur) cur.oninput = function () { b.cur = cur.value; changed(); render(true); };
        var tax = $('#dde-tax', pane); if (tax) tax.oninput = function () { b.tax = +tax.value || 0; changed(); render(true); };
        var cal = $('#dde-cal', pane); if (cal) cal.onchange = function () { var p = cal.value.split('-'); b.y = +p[0]; b.m = +p[1] - 1; changed(); render(true); };
        var tk = $('#dde-tk', pane); if (tk) tk.oninput = function () { b.start = +tk.value || 1; changed(); render(true); };
      } else if (tab === 'ai') {
        var bi = doc.blocks[blockOf(sel)];
        pane.innerHTML = '<h5>AI writing</h5>' + (bi ? '<p class="rnote">Selected: <b>' + esc(TYPE_LABEL[bi.t] || 'Block') + '</b></p><div class="rbtns" id="dde-ai">' + AI_ACTS.map(function (a) { return '<button data-a="' + a[0] + '">' + esc(a[1]) + '</button>'; }).join('') + '</div>' +
          '<label class="rlab">Or tell the AI what to change</label><textarea id="dde-ask" placeholder="E.g. Make it more persuasive and add a call to action"></textarea><button class="eb pri" data-e="askai" type="button" style="margin-top:8px;width:100%;justify-content:center">✨ Apply</button>' : '<p class="rnote">Select a block in the page to rewrite it with AI.</p>') +
          '<h5 style="margin-top:20px">Write something new</h5><textarea id="dde-write" placeholder="E.g. A paragraph about our refund policy"></textarea><button class="eb" data-e="writeai" type="button" style="margin-top:8px;width:100%;justify-content:center">✨ Write and insert</button>' +
          '<p class="rnote" style="margin-top:10px">Each AI action uses ' + (CFG.assistCost != null ? CFG.assistCost : 1) + ' credit.</p>';
        var ai = $('#dde-ai', pane); if (ai) ai.onclick = function (e) { var x = e.target.closest('[data-a]'); if (x) { tb.classList.remove('dd-hide'); aiBlock(bi, x.dataset.a); } };
      }
    }
    function drawAdd() {
      var box = $('#dde-add', pane); if (!box) return;
      var list = D.design(doc.tpl).kind === 'resume' ? ADD_RESUME.concat(ADDABLE.slice(1)) : ADDABLE;
      box.innerHTML = list.map(function (x) { return '<button data-n="' + x[0] + '">' + esc(x[1]) + '</button>'; }).join('');
      box.onclick = function (e) { var x = e.target.closest('[data-n]'); if (!x) return; snapshot(); var nb = NEW(x.dataset.n); doc.blocks.push(nb); changed(); render(true); select(nb.id); setTimeout(function () { var n = docBox.querySelector('.dsd-blk[data-id="' + nb.id + '"]'); if (n) n.scrollIntoView({ block: 'center' }); }, 50); focusBlock(nb.id); };
    }
    $('.rtabs', host).addEventListener('click', function (e) { var b = e.target.closest('[data-tab]'); if (!b) return; tab = b.dataset.tab; drawPane(); });

    /* ---------- top / bottom bar ---------- */
    function doSave(quiet) {
      if (!CFG.loggedIn) { toast('Please sign in to save your document.', 'err'); return Promise.resolve(); }
      $('#dde-saved', host).textContent = 'Saving…';
      return saveDoc(doc, docId, quiet).then(function (id) { docId = id; dirty = false; lastSaved = JSON.stringify(forSave(doc)); $('#dde-saved', host).textContent = 'All changes saved'; loadDocs(true); })
        .catch(function (e) { $('#dde-saved', host).textContent = 'Not saved'; toast(e.message, 'err'); });
    }
    host.addEventListener('click', function (e) {
      var b = e.target.closest('[data-e]'); if (!b) { if (menuEl && !e.target.closest('.menu') && !e.target.closest('.ftb')) closeMenu(); return; }
      var a = b.dataset.e;
      if (a === 'back') { self.close(); return; }
      if (a === 'save') doSave(false);
      if (a === 'download') {
        menu(b, [['docx', '⬇ Word document (.docx)'], ['pdf', '⬇ PDF (print)'], ['png', '⬇ PNG image'], ['jpg', '⬇ JPG image'], ['vec', '⬇ EPS + SVG (vector)'], ['tiff', '⬇ TIFF (print)']], function (v) {
          if (v === 'docx') downloadDocx(doc); else if (v === 'pdf') printPdf(doc); else exportPages(doc, v);
        });
        menuEl.style.left = 'auto'; menuEl.style.right = '12px'; menuEl.style.top = (canvas.scrollTop + 6) + 'px';
      }
      if (a === 'undo') undo();
      if (a === 'redo') redo();
      if (a === 'style' || a === 'tab-design') { tab = 'design'; drawPane(); }
      if (a === 'tab-colours') { tab = 'colours'; drawPane(); }
      if (a === 'zin') zoomAt(zoom * 1.2);
      if (a === 'zout') zoomAt(zoom / 1.2);
      if (a === 'zfit') fit();
      if (a === 'pgprev') goPage(-1);
      if (a === 'pgnext') goPage(1);
      if (a === 'rename') { ttl.focus(); ttl.select(); }
      if (a === 'addsec') { addSection(); return; }
      if (a === 'addsec_old') { snapshot(); var h1 = NEW(D.design(doc.tpl).kind === 'resume' ? 'r_sec' : 'h1'), p = NEW('p'); doc.blocks.push(h1, p); changed(); render(true); select(h1.id); focusBlock(h1.id); setTimeout(function () { var n = docBox.querySelector('.dsd-blk[data-id="' + h1.id + '"]'); if (n) n.scrollIntoView({ block: 'center' }); }, 60); }
      if (a === 'askai') { var bi = doc.blocks[blockOf(sel)], q = ($('#dde-ask', pane).value || '').trim(); if (bi && q) aiBlock(bi, 'custom', { instruction: q }); }
      if (a === 'writeai') {
        var q2 = ($('#dde-write', pane).value || '').trim(); if (!q2) { toast('Describe what to write.'); return; }
        toast('✨ Writing…', '', 30000);
        api('POST', 'ds/v1/doc-assist', { action: 'write', instruction: q2, text: '', language: doc.lang || 'English' }).then(function (r) {
          setCredits(r.credits_balance); snapshot();
          var at = sel ? blockOf(sel) + 1 : doc.blocks.length;
          String(r.text || '').split(/\n{2,}/).filter(Boolean).forEach(function (para, k) { doc.blocks.splice(at + k, 0, { t: 'p', id: D.uid(), text: para.trim() }); });
          changed(); render(true); toast('Added.', 'ok', 1500);
        }).catch(function (er) { toast(er.message, 'err'); });
      }
    });
    $('#dde-outline', host).addEventListener('click', function (e) {
      var li = e.target.closest('[data-go]'); if (!li) return;
      var n = li.dataset.go === 'cover' ? docBox.querySelector('.dsd-cover') : docBox.querySelector('.dsd-blk[data-id="' + li.dataset.go + '"]');
      if (n) { var cr = canvas.getBoundingClientRect(), r = n.getBoundingClientRect(); canvas.scrollTop += r.top - cr.top - (canvas.__padT || 60) - 14; if (li.dataset.go !== 'cover') select(li.dataset.go); }
    });
    ttl.addEventListener('input', function () { doc.title = ttl.value; changed(); var t2 = docBox.querySelector('[data-df="title"]'); if (t2 && document.activeElement !== t2) t2.textContent = doc.title; relayout(); });
    function undo() { if (!hist.length) return; fut.push(JSON.stringify(doc)); var d = JSON.parse(hist.pop()); Object.keys(doc).forEach(function (k) { delete doc[k]; }); Object.assign(doc, d); ttl.value = doc.title || ''; changed(); render(true); drawPane(); }
    function redo() { if (!fut.length) return; hist.push(JSON.stringify(doc)); var d = JSON.parse(fut.pop()); Object.keys(doc).forEach(function (k) { delete doc[k]; }); Object.assign(doc, d); ttl.value = doc.title || ''; changed(); render(true); drawPane(); }
    function onKey(e) {
      if (!typingIn(e.target)) {
        if ((e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '+')) { e.preventDefault(); zoomAt(zoom * 1.2); return; }
        if ((e.ctrlKey || e.metaKey) && (e.key === '-' || e.key === '_')) { e.preventDefault(); zoomAt(zoom / 1.2); return; }
        if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); fit(); return; }
        if (e.shiftKey && (e.key === '!' || e.code === 'Digit1') && !(e.ctrlKey || e.metaKey)) { e.preventDefault(); fit(); return; }
      }
      if (!(e.ctrlKey || e.metaKey)) return;
      var k = e.key.toLowerCase();
      if (k === 's') { e.preventDefault(); doSave(false); }
      if (k === 'z' && !e.shiftKey) { e.preventDefault(); if (LE) LE.clear(); undo(); }
      if (k === 'y' || (k === 'z' && e.shiftKey)) { e.preventDefault(); redo(); }
    }
    document.addEventListener('keydown', onKey);
    window.addEventListener('resize', applyZoom);
    function beforeUnload(e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } }
    window.addEventListener('beforeunload', beforeUnload);
    self.close = function (force) {
      if (!force && dirty && JSON.stringify(forSave(doc)) !== lastSaved) {
        if (CFG.loggedIn) { doSave(true).then(function () { self.close(true); }); return; }
        if (!window.confirm('Leave without saving? Your changes will be lost.')) return;
      }
      if (LE) LE.destroy();
      document.removeEventListener('keydown', onKey); document.removeEventListener('keydown', onSpace, true); document.removeEventListener('keyup', onSpace, true); window.removeEventListener('resize', applyZoom); window.removeEventListener('beforeunload', beforeUnload);
      host.remove(); document.body.classList.remove('dd-noscroll'); ED = null;
    };

    var initialJSON = JSON.stringify(doc);
    function addSection() { snapshot(); var h1 = NEW(D.design(doc.tpl).kind === 'resume' ? 'r_sec' : 'h1'), p = NEW('p'); var at = sel ? blockOf(sel) + 1 : doc.blocks.length; while (at < doc.blocks.length && !/^(h1|r_sec)$/.test(doc.blocks[at].t)) at++; doc.blocks.splice(at, 0, h1, p); changed(); render(true); select(h1.id); focusBlock(h1.id); setTimeout(function () { var n = docBox.querySelector('.dsd-blk[data-id="' + h1.id + '"]'); if (n) n.scrollIntoView({ block: 'center' }); }, 60); }
    function editText() {
      var before = JSON.stringify(doc); snapshot();
      reviewDoc(doc, docId, {
        onApply: function () { ensureIds(doc.blocks); changed(); render(true); fillPictures(doc, function () { render(true); }).then(function () { render(true); }); toast('Text updated.', 'ok', 1500); },
        onCancel: function () { var d = JSON.parse(before); Object.keys(doc).forEach(function (k) { delete doc[k]; }); Object.assign(doc, d); hist.pop(); render(true); }
      });
    }
    LE = window.DSDOC_LE ? window.DSDOC_LE({
      doc: doc, host: host, canvas: canvas, docBox: docBox,
      zoom: function () { return zoom; }, snapshot: function () { snapshot(); }, changed: function () { changed(); }, toast: toast,
      pickPicture: function (cb, o) { pickPicture(cb, Object.assign({ png: true }, o || {})); },
      rerender: function () { render(true); }, refit: function () { fit(); },
      onBlockSelect: function (id) { if (sel === id) return; sel = id; drawOutline(); if (tab === 'block' || tab === 'ai') drawPane(); },
      undo: function () { undo(); }, redo: function () { redo(); },
      resetAll: function () { snapshot(); var d = JSON.parse(initialJSON); Object.keys(doc).forEach(function (k) { delete doc[k]; }); Object.assign(doc, d); ttl.value = doc.title || ''; changed(); render(true); drawPane(); fillPictures(doc).then(function () { render(true); }); },
      editText: editText, addSection: addSection,
      styleTab: function () { tab = 'colours'; drawPane(); }, blockTab: function () { host.classList.remove('dd-focus'); tab = 'block'; drawPane(); fit(); },
      blocks: { convertList: CONVERT, typeLabel: TYPE_LABEL, aiActs: AI_ACTS, tones: TONES, langs: LANGS, newBlock: NEW, convert: convert, aiBlock: aiBlock,
        addable: function () { return D.design(doc.tpl).kind === 'resume' ? ADD_RESUME.concat(ADDABLE.slice(1)) : ADDABLE; } }
    }) : null;
    render(); drawPane(); setTimeout(fit, 30);
    $('#dde-saved', host).textContent = docId ? 'All changes saved' : (CFG.loggedIn ? '' : 'Sign in to save');
    snapshot(); hist = [];
  }

  /* ---------------- picture picker (marketplace · AI image · upload) ---------------- */
  /* the same picture window as AI Presentation: Generate with AI, Browse from store, Upload from device */
  function pickPicture(done, opt) {
    opt = opt || {};
    var m = el('div', 'dd-io');
    m.innerHTML = '<div class="io-box io-sm"><h3>' + (opt.change ? 'Change Image' : 'Add Image') + '</h3><p class="io-sub">Choose how to add an image to this document</p>' +
      '<button type="button" class="io-b io-ai" data-io="ai">&#10024; Generate with AI <small>(uses credits)</small></button>' +
      '<button type="button" class="io-b io-st" data-io="store">&#127978; Browse from store</button>' +
      '<button type="button" class="io-b io-up" data-io="up">&#128228; Upload from device<em>JPG, PNG, WEBP or GIF &middot; Free</em></button>' +
      '<button type="button" class="io-b io-x" data-io="x">Cancel</button></div>';
    document.body.appendChild(m);
    m.addEventListener('click', function (e) {
      if (e.target === m) { m.remove(); return; }
      var b = e.target.closest('[data-io]'); if (!b) return;
      var a = b.dataset.io; m.remove();
      if (a === 'ai') pickAI(done, opt);
      if (a === 'store') pickStore(done, opt);
      if (a === 'up') pickUpload(done, opt);
    });
  }
  function pickUpload(done, opt) {
    var f0 = document.createElement('input'); f0.type = 'file'; f0.accept = 'image/png,image/jpeg,image/webp,image/gif'; f0.style.display = 'none'; document.body.appendChild(f0);
    f0.onchange = function () {
      var f = f0.files && f0.files[0]; f0.remove(); if (!f) return;
      var fr = new FileReader();
      fr.onload = function () {
        var img = new Image();
        img.onload = function () {
          if (opt && opt.png && /png|gif|webp/.test(f.type)) {
            var data = '', side = 1400;
            do {
              var kk = Math.min(1, side / Math.max(img.naturalWidth, img.naturalHeight)), cv = document.createElement('canvas');
              cv.width = Math.max(1, Math.round(img.naturalWidth * kk)); cv.height = Math.max(1, Math.round(img.naturalHeight * kk));
              cv.getContext('2d').drawImage(img, 0, 0, cv.width, cv.height);
              data = cv.toDataURL('image/png'); side = Math.round(side * 0.75);
            } while (data.length > 700000 && side > 200);
            done({ data: data }); return;
          }
          var k = Math.min(1, 1600 / Math.max(img.naturalWidth, img.naturalHeight)), c = document.createElement('canvas');
          c.width = Math.round(img.naturalWidth * k); c.height = Math.round(img.naturalHeight * k);
          var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
          done({ data: c.toDataURL('image/jpeg', 0.85) });
        };
        img.onerror = function () { toast('That picture could not be read.', 'err'); };
        img.src = fr.result;
      };
      fr.readAsDataURL(f);
    };
    f0.click();
  }
  function aiPictures(prompt, ratio) {
    var req = { prompt: prompt, ratio: ratio || '16:9', count: 1 };
    return api('POST', 'ds/v1/generate-image', req).then(function (r) {
      if (r.credits_left != null) setCredits(r.credits_left);
      var urls = [].concat(r.images || []).map(function (x) { return typeof x === 'string' ? x : x && x.url; }).filter(Boolean);
      if (urls.length) return urls;
      var pid = r.prediction_id || (r.prediction_ids && r.prediction_ids[0]);
      if (!pid) throw new Error('No picture came back. Please try again.');
      var tries = 0;
      return new Promise(function (res, rej) {
        (function poll() {
          api('POST', 'ds/v1/poll-image', Object.assign({ prediction_id: pid }, req)).then(function (x) {
            if (x.status === 'succeeded') return res([].concat(x.images || []).map(function (y) { return typeof y === 'string' ? y : y && y.url; }).filter(Boolean));
            if (x.status === 'failed' || x.status === 'error' || ++tries > 60) return rej(new Error(x.error || 'The picture could not be created.'));
            setTimeout(poll, 2500);
          }).catch(rej);
        })();
      });
    });
  }
  function pickAI(done, opt) {
    var m = el('div', 'dd-io');
    m.innerHTML = '<div class="io-box io-md"><h3 style="text-align:left">&#127912; Generate Image</h3><p class="io-sub" style="text-align:left">Describe the image you want for this document</p>' +
      '<textarea class="io-ta" placeholder="E.g. A modern office team planning on a whiteboard, soft daylight"></textarea>' +
      '<div class="io-row"><select class="io-sel"><option value="16:9">Wide 16:9</option><option value="4:3">Standard 4:3</option><option value="1:1">Square</option><option value="3:4">Tall 3:4</option></select></div>' +
      '<div class="io-row"><button type="button" class="io-b io-ai" data-g="gen">&#10024; Generate</button><button type="button" class="io-b io-st" data-g="store">&#127978; Browse store</button><button type="button" class="io-b io-x" data-g="x">Cancel</button></div>' +
      '<div class="io-res"></div><button type="button" class="io-link" data-g="up">Or upload your own image</button></div>';
    document.body.appendChild(m);
    var ta = m.querySelector('.io-ta'); ta.value = opt.prompt || ''; setTimeout(function () { ta.focus(); }, 30);
    m.addEventListener('click', function (e) {
      if (e.target === m) { m.remove(); return; }
      var b = e.target.closest('[data-g]'); if (!b) return;
      var a = b.dataset.g;
      if (a === 'x') { m.remove(); return; }
      if (a === 'store') { m.remove(); pickStore(done, opt); return; }
      if (a === 'up') { m.remove(); pickUpload(done, opt); return; }
      if (a === 'gen') {
        var q = ta.value.trim(); if (!q) { toast('Describe the picture first.'); ta.focus(); return; }
        var out = m.querySelector('.io-res'); b.disabled = true; out.innerHTML = '<span class="dd-spin"></span> Creating your picture…';
        aiPictures(q, m.querySelector('.io-sel').value).then(function (urls) {
          out.innerHTML = '<p class="io-sub" style="text-align:left;margin:0 0 6px">Click the picture to use it.</p>';
          urls.forEach(function (u) { var pb = el('button'); pb.type = 'button'; pb.className = 'io-pick'; pb.innerHTML = '<img src="' + esc(u) + '" alt="">'; pb.onclick = function () { m.remove(); done({ src: u }); }; out.appendChild(pb); });
        }).catch(function (er) { out.innerHTML = '<p class="io-err">' + esc(er.message) + '</p>'; }).then(function () { b.disabled = false; });
      }
    });
  }
  function pickStore(done, opt) {
    var m = el('div', 'dd-io'), cat = 'images', q = '', page = 1, total = 0, loaded = 0, busy = false, cats = null;
    m.innerHTML = '<div class="io-box io-lg"><div class="io-hd"><b>&#127978; Browse From Marketplace</b><input class="io-q" type="search" placeholder="Search images..."><button type="button" class="io-close" data-s="x">&#10005; Close</button></div>' +
      '<div class="io-tabs"></div><div class="io-grid"></div><div class="io-ft"><span class="io-cnt"></span><button type="button" class="io-more" data-s="more">Load more</button></div></div>';
    document.body.appendChild(m);
    var grid = m.querySelector('.io-grid'), tabs = m.querySelector('.io-tabs'), cnt = m.querySelector('.io-cnt'), more = m.querySelector('.io-more');
    function dec(t) { var d = document.createElement('textarea'); d.innerHTML = String(t || ''); return d.value; }
    function drawTabs() {
      if (!cats) return;
      tabs.innerHTML = cats.map(function (c) { return '<button type="button" data-cat="' + esc(c.slug) + '" class="' + (c.slug === cat ? 'on' : '') + '">' + esc(dec(c.name)) + ' (' + (+c.count || 0) + ')</button>'; }).join('');
    }
    function load(reset) {
      if (busy) return; busy = true;
      if (reset) { page = 1; loaded = 0; grid.innerHTML = '<span class="dd-spin"></span>'; }
      more.disabled = true;
      api('GET', 'ds/v1/mkt-slide-images?per_page=100&page=' + page + '&cat=' + encodeURIComponent(cat) + (q ? '&s=' + encodeURIComponent(q) : '')).then(function (r) {
        if (reset) grid.innerHTML = '';
        if (!cats && r.categories) { cats = r.categories.filter(function (c) { return c && c.slug && +c.count > 0; }); drawTabs(); }
        total = +r.total || 0;
        var items = (r.items || []).filter(function (x) { return x.thumb || x.full; });
        if (!items.length && !loaded) grid.innerHTML = '<p class="io-sub">No pictures found.</p>';
        items.forEach(function (it) {
          var b = el('button'); b.type = 'button'; b.className = 'io-card'; var tt = dec(it.title || '');
          b.innerHTML = '<span class="io-th"><img loading="lazy" src="' + esc(it.thumb || it.full) + '" alt="" onerror="this.parentNode.classList.add(\'io-na\')"><i class="io-tier' + (it.tier && it.tier !== 'free' ? ' pro' : '') + '">' + (it.tier && it.tier !== 'free' ? 'Pro' : 'Free') + '</i></span><span class="io-tt" title="' + esc(tt) + '">' + esc(tt || 'Picture') + '</span>';
          b.onclick = function () {
            if (b.classList.contains('io-busy')) return;
            b.classList.add('io-busy');
            sharpPhoto(it.id, it.full).then(function (p) {
              if (p.px && p.px < 1000 && it.tier !== 'free' && CFG.plan === 'free') { b.classList.remove('io-busy'); toast('This is a Pro picture. It needs a paid plan for full quality.', 'err', 6000); return; }
              m.remove(); done({ data: p.data, pid: it.id, url: it.full });
            }).catch(function (e) { b.classList.remove('io-busy'); toast(e.message || 'That picture could not be added.', 'err'); });
          };
          grid.appendChild(b);
        });
        loaded += items.length;
        cnt.textContent = loaded + ' of ' + total + ' images loaded';
        more.style.display = loaded < total ? '' : 'none'; more.disabled = false;
      }).catch(function (e) { grid.innerHTML = '<p class="io-err">' + esc(e.message) + '</p>'; }).then(function () { busy = false; });
    }
    var qt = 0;
    m.querySelector('.io-q').addEventListener('input', function (e) { clearTimeout(qt); var v = e.target.value.trim(); qt = setTimeout(function () { q = v; load(true); }, 450); });
    m.addEventListener('click', function (e) {
      if (e.target === m) { m.remove(); return; }
      var t = e.target.closest('[data-cat]'); if (t) { cat = t.dataset.cat; drawTabs(); load(true); return; }
      var b = e.target.closest('[data-s]'); if (!b) return;
      if (b.dataset.s === 'x') m.remove();
      if (b.dataset.s === 'more') { page++; load(false); }
    });
    m.addEventListener('keydown', function (e) { if (e.key === 'Escape') m.remove(); e.stopPropagation(); });
    load(true);
  }
  function pickPictureOld(done, opt) {
    var m = el('div', 'dd-modal');
    m.innerHTML = '<div class="mb"><div class="mh2"><b>Add a picture</b><button class="c" type="button" data-x>×</button></div>' +
      '<div class="tabs"><button type="button" data-pt="mkt" class="on">Marketplace</button><button type="button" data-pt="ai">AI image</button><button type="button" data-pt="up">Upload</button></div><div class="bd" id="ddp-bd"></div></div>';
    document.body.appendChild(m);
    var bd = $('#ddp-bd', m), mode = 'mkt';
    function close() { m.remove(); }
    m.addEventListener('click', function (e) {
      if (e.target === m || e.target.closest('[data-x]')) { close(); return; }
      var t = e.target.closest('[data-pt]'); if (t) { mode = t.dataset.pt; $$('[data-pt]', m).forEach(function (b) { b.classList.toggle('on', b === t); }); draw(); }
    });
    function draw() {
      if (mode === 'mkt') {
        bd.innerHTML = '<div class="srch"><input id="ddp-q" placeholder="Search photos, e.g. office team"><button class="btn pri" id="ddp-go" type="button">Search</button></div><div class="imgs" id="ddp-res"></div>';
        var go = function () {
          var q = $('#ddp-q', m).value.trim() || 'business', res = $('#ddp-res', m);
          res.innerHTML = '<span class="dd-spin"></span>';
          api('GET', 'ds/v1/mkt-slide-images?s=' + encodeURIComponent(q) + '&cat=images&per_page=24&page=1').then(function (r) {
            var items = (r.items || []).filter(function (x) { return x.thumb || x.full; });
            res.innerHTML = items.length ? '' : '<span class="rnote">No pictures found.</span>';
            items.forEach(function (it) {
              var b = el('button'); b.type = 'button'; b.title = String(it.title || '').replace(/&amp;/g, '&');
              b.innerHTML = '<img loading="lazy" src="' + esc(it.thumb || it.full) + '" alt="">' + (it.tier && it.tier !== 'free' ? '<span class="pro">PRO</span>' : '');
              b.onclick = function () {
                b.innerHTML += '<span class="pro" style="left:5px;right:auto;background:#2F6FED;color:#fff">Adding…</span>';
                sharpPhoto(it.id, it.full).then(function (p) {
                  if (p.px && p.px < 1000 && it.tier !== 'free' && CFG.plan === 'free') { toast('This is a Pro picture. It needs a paid plan for full quality.', 'err', 6000); return; }
                  close(); done({ data: p.data, pid: it.id, url: it.full });
                }).catch(function (e) { toast(e.message || 'That picture could not be added.', 'err'); });
              };
              res.appendChild(b);
            });
          }).catch(function (e) { res.innerHTML = '<span class="rnote">' + esc(e.message) + '</span>'; });
        };
        $('#ddp-go', m).onclick = go; $('#ddp-q', m).onkeydown = function (e) { if (e.key === 'Enter') go(); };
        go();
      } else if (mode === 'ai') {
        bd.innerHTML = '<textarea id="ddp-p" placeholder="Describe the picture, e.g. A modern office team planning on a whiteboard, soft daylight"></textarea><div class="srch" style="margin-top:10px"><select id="ddp-r"><option value="16:9">Wide 16:9</option><option value="4:3">Standard 4:3</option><option value="1:1">Square</option><option value="3:4">Tall 3:4</option></select><button class="btn pri" id="ddp-gen" type="button">✨ Create picture</button></div><div class="imgs" id="ddp-ai"></div><p class="rnote">Uses your credits, like AI Images.</p>';
        $('#ddp-gen', m).onclick = function () {
          var p = $('#ddp-p', m).value.trim(); if (!p) { toast('Describe the picture first.'); return; }
          var out = $('#ddp-ai', m), btn = $('#ddp-gen', m); btn.disabled = true; out.innerHTML = '<span class="dd-spin"></span> Creating…';
          var req = { prompt: p, ratio: $('#ddp-r', m).value, count: 1 };
          api('POST', 'ds/v1/generate-image', req).then(function (r) {
            if (r.credits_left != null) setCredits(r.credits_left);
            var urls = [].concat(r.images || []).map(function (x) { return typeof x === 'string' ? x : x && x.url; }).filter(Boolean);
            if (urls.length) return urls;
            var pid = r.prediction_id || (r.prediction_ids && r.prediction_ids[0]);
            if (!pid) throw new Error('No picture came back. Please try again.');
            var tries = 0;
            return new Promise(function (res, rej) {
              (function poll() {
                api('POST', 'ds/v1/poll-image', Object.assign({ prediction_id: pid }, req)).then(function (x) {
                  if (x.status === 'succeeded') return res([].concat(x.images || []).map(function (y) { return typeof y === 'string' ? y : y && y.url; }).filter(Boolean));
                  if (x.status === 'failed' || x.status === 'error' || ++tries > 60) return rej(new Error(x.error || 'The picture could not be created.'));
                  setTimeout(poll, 2500);
                }).catch(rej);
              })();
            });
          }).then(function (urls) {
            out.innerHTML = '';
            urls.forEach(function (u) { var b = el('button'); b.type = 'button'; b.innerHTML = '<img src="' + esc(u) + '" alt="">'; b.onclick = function () { close(); done({ src: u }); }; out.appendChild(b); });
          }).catch(function (e) { out.innerHTML = '<span class="rnote">' + esc(e.message) + '</span>'; }).then(function () { btn.disabled = false; });
        };
      } else {
        bd.innerHTML = '<p class="rnote">Choose a JPG or PNG from your device. It is resized to keep your document light.</p><input type="file" id="ddp-f" accept="image/png,image/jpeg,image/webp">';
        $('#ddp-f', m).onchange = function (e) {
          var f = e.target.files && e.target.files[0]; if (!f) return;
          var fr = new FileReader();
          fr.onload = function () {
            var img = new Image();
            img.onload = function () {
              if (opt && opt.png && f.type === 'image/png') {     // keep transparency for picture elements
                var data = '', side = 1400;
                do {
                  var kk = Math.min(1, side / Math.max(img.naturalWidth, img.naturalHeight)), cv = document.createElement('canvas');
                  cv.width = Math.max(1, Math.round(img.naturalWidth * kk)); cv.height = Math.max(1, Math.round(img.naturalHeight * kk));
                  cv.getContext('2d').drawImage(img, 0, 0, cv.width, cv.height);
                  data = cv.toDataURL('image/png'); side = Math.round(side * 0.75);
                } while (data.length > 700000 && side > 200);
                close(); done({ data: data }); return;
              }
              var k = Math.min(1, 1600 / Math.max(img.naturalWidth, img.naturalHeight)), c = document.createElement('canvas');
              c.width = Math.round(img.naturalWidth * k); c.height = Math.round(img.naturalHeight * k);
              var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
              close(); done({ data: c.toDataURL('image/jpeg', 0.85) });
            };
            img.src = fr.result;
          };
          fr.readAsDataURL(f);
        };
      }
    }
    draw();
  }

  /* ---------------- start ---------------- */
  fillPages();
  setMode('ai');
  refreshAll();
  loadPhotoPool();
  loadDocs(true);
  // ?doc=123 opens a saved document straight in the editor (used by the Word add-in's "Edit on DesignStager")
  var qd = /[?&]doc=(\d+)/.exec(location.search);
  if (qd && CFG.loggedIn) openSaved(+qd[1]);
  window.DSDOC_APP = { open: openEditor, start: start, state: S };
})();

DSJS;
}
function ds_doc_app_css() {
	return <<<'DSCSS'
/* DesignStager AI Document — page & editor (styled like the AI Presentation page) */
#dsdoc-app{--card:#141927;--card2:#0d111b;--field:#1a2030;--bd:rgba(255,255,255,.07);--tx:#e9ecf5;--mut:#6b7280;--mut2:#9aa3b5;--lab:#c3c9d8;--blue:#2563eb;--blue2:#3b6cf0;--link:#6ea8ff;--org:#F0532B;
  color:var(--tx);padding:28px 24px 56px;box-sizing:border-box;margin:0 auto;
  background:radial-gradient(ellipse 60% 420px at 0% 0%,rgba(120,24,24,.32),transparent 70%),radial-gradient(ellipse 60% 420px at 100% 0%,rgba(29,57,128,.32),transparent 70%),#0a0a0a}
#dsdoc-app,#dsdoc-app *:not(.dsd-doc):not(.dsd-doc *){font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif!important}
#dsdoc-app *{box-sizing:border-box}
#dsdoc-app button{font-family:inherit;cursor:pointer;text-transform:none;letter-spacing:normal;line-height:1.3;min-width:0;min-height:0;padding:0;box-shadow:none;text-shadow:none}
#dsdoc-app .dd-sp{flex:1}
#dsdoc-app .dd-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:28px;align-items:start}
@media(max-width:900px){#dsdoc-app .dd-grid{grid-template-columns:1fr}}
#dsdoc-app .dd-card{background:var(--card);border:1px solid var(--bd);border-radius:20px;padding:28px}
#dsdoc-app .dd-card+.dd-card{margin-top:28px}
#dsdoc-app .dd-card h3{margin:0 0 6px;font-size:19px;font-weight:600;display:flex;align-items:center;flex-wrap:wrap;gap:10px;color:var(--tx);line-height:1.3}
#dsdoc-app .dd-card h3 .ic{color:#60a5fa;display:inline-flex}
#dsdoc-app .dd-card h3 .req{font-size:11px;background:rgba(220,38,38,.18);color:#f87171;padding:3px 10px;border-radius:20px;font-weight:700}
#dsdoc-app .dd-card h3 .lnk{margin-left:auto;font-size:14px;color:var(--link);font-weight:600;background:none;border:0}
#dsdoc-app .dd-sub{font-size:14px;color:var(--mut2);margin:4px 0 18px}
#dsdoc-app .dd-modes{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:18px 0 18px}
#dsdoc-app .dd-mode{border:1px solid var(--bd);background:var(--card2);border-radius:12px;padding:14px;text-align:left;color:var(--tx);min-height:96px}
#dsdoc-app .dd-mode b{display:block;font-size:13px;font-weight:700;margin-bottom:6px;color:#e5e7eb}
#dsdoc-app .dd-mode span{font-size:11.5px;color:var(--mut);line-height:1.45;display:block}
#dsdoc-app .dd-mode.on{border-color:var(--blue2);background:#131b33}
#dsdoc-app .dd-mode.on b{color:#60a5fa}
#dsdoc-app label.dd-l{display:block;font-size:11.5px;letter-spacing:.07em;text-transform:uppercase;color:var(--lab);font-weight:700;margin:18px 0 8px}
#dsdoc-app select,#dsdoc-app input[type=text],#dsdoc-app input[type=search],#dsdoc-app input:not([type]){width:100%;background:var(--field);border:1px solid var(--bd);color:var(--tx);border-radius:10px;padding:11px 14px;font-size:15px;font-weight:600;outline:none;height:auto}
#dsdoc-app textarea{width:100%;background:transparent;border:0;color:var(--tx);border-radius:2px;padding:16px;font-size:16px;outline:none;min-height:190px;resize:vertical;line-height:1.55}
#dsdoc-app .dd-box{background:var(--card2);border:1px solid var(--bd);border-radius:16px;overflow:hidden}
#dsdoc-app .dd-box:focus-within{border-color:var(--blue2)}
#dsdoc-app .dd-box textarea{min-height:190px}
#dsdoc-app .dd-dir{margin-top:18px}
#dsdoc-app .dd-dir .hd{display:flex;align-items:center;gap:8px;padding:14px 16px 0;font-size:15px;font-weight:600;color:var(--tx)}
#dsdoc-app .dd-dir .hd i{color:#f87171;font-style:normal}
#dsdoc-app .dd-dir textarea{min-height:64px;font-size:14.5px;padding:10px 16px 14px}
#dsdoc-app select:focus,#dsdoc-app input:focus{border-color:var(--blue2)}
#dsdoc-app .dd-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
#dsdoc-app .dd-row label.dd-l{margin-top:22px}
@media(max-width:600px){#dsdoc-app .dd-row{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}#dsdoc-app .dd-modes{grid-template-columns:1fr}}
#dsdoc-app .dd-tbar{display:flex;align-items:center;gap:12px;padding:10px 16px 14px;font-size:13px;color:var(--mut)}
#dsdoc-app .dd-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--bd);background:var(--field);color:var(--tx);border-radius:10px;padding:9px 14px;font-size:13.5px;font-weight:600}
#dsdoc-app .dd-btn:hover{border-color:var(--blue2)}
#dsdoc-app .dd-btn.pri{background:var(--blue);border-color:var(--blue);color:#fff}
#dsdoc-app .dd-btn[disabled]{opacity:.55;cursor:not-allowed}
#dsdoc-app .dd-uai{display:inline-flex;align-items:center;gap:8px;background:transparent;border:0;padding:4px 6px;color:var(--tx);font-size:13.5px;font-weight:600;cursor:pointer;white-space:nowrap}
#dsdoc-app .dd-uai .tr{position:relative;width:34px;height:20px;border-radius:999px;background:#2a3346;border:1px solid #3a465f;transition:background .15s}
#dsdoc-app .dd-uai .tr::after{content:"";position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#8b97ad;transition:transform .15s,background .15s}
#dsdoc-app .dd-uai[aria-checked="true"] .tr{background:#2563eb;border-color:#3b82f6}
#dsdoc-app .dd-uai[aria-checked="true"] .tr::after{transform:translateX(14px);background:#fff}
#dsdoc-app .dd-uai.dd-hide{display:none}
#dsdoc-app .dd-up{background:transparent;border:1px solid var(--bd);border-radius:20px;padding:7px 14px;font-size:13.5px;font-weight:600;color:var(--tx)}
#dsdoc-app .dd-gobar{display:flex;align-items:center;gap:16px;margin-top:26px;padding-top:22px;border-top:1px solid var(--bd)}
#dsdoc-app .dd-gobar .info{flex:1;min-width:0}
#dsdoc-app .dd-cost{font-size:13px;color:var(--mut2);line-height:1.6}
#dsdoc-app .dd-cost .dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:#3b82f6;margin-right:7px;vertical-align:1px}
#dsdoc-app .dd-cost b{color:#f87171;font-size:15px;font-weight:700}
#dsdoc-app .dd-cost small{color:var(--mut);font-size:11.5px}
#dsdoc-app .dd-go{flex:none;min-width:200px;background:var(--blue);color:#fff;border:0;border-radius:12px;padding:16px 20px;font-size:16.5px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;display:inline-flex;align-items:center;justify-content:center;gap:10px}
#dsdoc-app .dd-go:hover{background:#1d4ed8}
#dsdoc-app .dd-go[disabled]{opacity:.6;cursor:default}
#dsdoc-app .dd-hint{font-size:13px;color:#f87171;margin-top:12px}
#dsdoc-app .dd-themes{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;max-height:300px;overflow:auto;padding-right:6px}
#dsdoc-app .dd-th{border:1px solid var(--bd);background:var(--field);border-radius:14px;padding:10px 10px 8px;text-align:left;color:var(--tx)}
#dsdoc-app .dd-th .sw{display:flex;height:22px;border-radius:6px;overflow:hidden;margin-bottom:10px}
#dsdoc-app .dd-th .sw i{flex:1}
#dsdoc-app .dd-th b{display:block;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--tx)}
#dsdoc-app .dd-th span{display:block;font-size:12px;color:var(--mut);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsdoc-app .dd-th.on{border-color:var(--blue2);box-shadow:0 0 0 1px var(--blue2)}
#dsdoc-app .dd-chips{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
#dsdoc-app .dd-chip{border:1px solid var(--bd);background:var(--field);color:var(--mut2);border-radius:20px;padding:5px 12px;font-size:12.5px;font-weight:600}
#dsdoc-app .dd-chip.on{background:#131b33;border-color:var(--blue2);color:#fff}
#dsdoc-app .dd-mybr{position:relative;border-color:#3b82f6 !important;cursor:pointer}
#dsdoc-app .dd-mybr.add{border-style:dashed !important;display:flex;flex-direction:column;align-items:flex-start;justify-content:center}
#dsdoc-app .dd-mybr .plus{width:26px;height:26px;border-radius:8px;background:#2563eb;color:#fff;font-size:20px;line-height:24px;text-align:center;margin-bottom:6px}
#dsdoc-app .dd-mybr .tag{position:absolute;top:-8px;left:8px;background:#2563eb;color:#fff;font-size:10px;font-weight:800;padding:1px 7px;border-radius:999px;letter-spacing:.3px}
#dsdoc-app .dd-mybr .ed{position:absolute;top:4px;right:4px;background:var(--field);border:1px solid var(--bd);color:var(--tx);font-size:11px;font-weight:700;border-radius:7px;padding:2px 7px;cursor:pointer}
#dsdoc-app .dd-mybr .sw img{height:22px;max-width:48px;object-fit:contain;margin-right:4px;background:#fff;border-radius:4px;padding:1px}
#dsdoc-app .dd-bk{width:min(940px,96vw);height:auto;max-height:94vh}
#dsdoc-app .dd-bkbody{display:grid;grid-template-columns:1.25fr 1fr;gap:22px;padding:4px 22px 10px;overflow:auto}
#dsdoc-app .dd-bkform input[type=url],#dsdoc-app .dd-bkform input[type=text],#dsdoc-app .dd-bkform select{width:100%;background:var(--field);border:1px solid var(--bd);border-radius:10px;padding:9px 12px;color:var(--tx);font-size:13.5px;box-sizing:border-box}
#dsdoc-app .dd-bkrow{display:flex;gap:8px}
#dsdoc-app .dd-bkbtn{background:var(--field);border:1px solid var(--bd);color:var(--tx);border-radius:10px;padding:9px 14px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap}
#dsdoc-app .dd-bkbtn.pri{background:#2563eb;border-color:#2563eb;color:#fff}
#dsdoc-app .dd-bkbtn.ghost{background:transparent}
#dsdoc-app .dd-bkbtn:disabled{opacity:.6;cursor:wait}
#dsdoc-app .dd-bkfound{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px;font-size:12px;color:var(--mut)}
#dsdoc-app .dd-bkfound .fc{width:26px;height:26px;border-radius:7px;border:2px solid rgba(255,255,255,.25);cursor:pointer;padding:0}
#dsdoc-app .dd-bklogo{display:flex;gap:14px;align-items:center}
#dsdoc-app .dd-bklogo .lg{width:120px;height:70px;border-radius:10px;background:repeating-conic-gradient(#e8ebf0 0 25%,#fff 0 50%) 0 0/14px 14px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;overflow:hidden;color:#6b7280;font-size:12px}
#dsdoc-app .dd-bklogo .lg img{max-width:92%;max-height:86%;object-fit:contain}
#dsdoc-app .dd-bklogo small{display:block;margin-top:6px;color:var(--mut);font-size:11.5px}
#dsdoc-app .dd-bkcols{display:flex;gap:10px}
#dsdoc-app .dd-bkcols .sl{display:flex;flex-direction:column;align-items:center;gap:4px;padding:6px;border:2px solid transparent;border-radius:12px;cursor:pointer;font-size:12px;color:var(--mut2)}
#dsdoc-app .dd-bkcols .sl.on{border-color:#3b82f6}
#dsdoc-app .dd-bkcols input[type=color]{width:54px;height:40px;border:0;background:transparent;padding:0;cursor:pointer}
#dsdoc-app .dd-row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#dsdoc-app .dd-bkauto{display:flex;gap:8px;align-items:center;margin-top:14px;font-size:13px;color:var(--tx)}
#dsdoc-app .dd-bkpv .hd{font-size:12px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.5px;margin:12px 0 8px}
#dsdoc-app .bkcard{border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:10px;min-height:230px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
#dsdoc-app .bkcard img{max-height:54px;max-width:60%;object-fit:contain;align-self:flex-start}
#dsdoc-app .bkcard b{font-size:26px;line-height:1.15}
#dsdoc-app .bkcard i{font-style:normal;font-size:13px;line-height:1.5}
#dsdoc-app .bkcard .bar{display:flex;gap:6px}
#dsdoc-app .bkcard .bar em{flex:1;height:10px;border-radius:5px}
#dsdoc-app .bkcard .cta{align-self:flex-start;padding:8px 16px;border-radius:999px;font-size:13px;font-weight:700}
#dsdoc-app .dd-bkft{display:flex;gap:10px;align-items:center;padding:12px 22px 18px;border-top:1px solid var(--bd)}
@media (max-width:760px){#dsdoc-app .dd-bkbody{grid-template-columns:1fr}}
#dsdoc-app .inb{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:4px;background:#0A66C2;color:#fff;font:800 11px/1 Arial,sans-serif;vertical-align:-3px;margin-right:2px}
#dsdoc-app .dd-li{width:min(640px,96vw)}
#dsdoc-app .dd-li textarea{width:100%;box-sizing:border-box;background:var(--field);border:1px solid var(--bd);border-radius:10px;padding:10px 12px;color:var(--tx);font-size:13px;resize:vertical}
#dsdoc-app .dd-linote{margin-top:10px;font-size:12.5px;line-height:1.55;color:var(--mut2);background:var(--field);border:1px solid var(--bd);border-radius:10px;padding:10px 12px}
#dsdoc-app .dd-lierr{margin-top:12px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.55);color:#fecaca;border-radius:10px;padding:10px 12px;font-size:13px;line-height:1.5}
#dsdoc-app .dd-lierr a{color:#fbbf24;font-weight:700;text-decoration:none}
#dsdoc-app .dd-lierr.dd-hide{display:none}
#dsdoc-app .dd-lidrop{display:flex;align-items:center;gap:12px;border:1.5px dashed var(--bd);border-radius:12px;padding:12px 14px;font-size:12.5px;color:var(--mut)}
#dsdoc-app .dd-lidrop.over,#dsdoc-app .dd-lidrop.need{border-color:#3b82f6;background:rgba(59,130,246,.08)}
#dsdoc-app .dd-lifiles{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
#dsdoc-app .dd-lifiles .f{display:inline-flex;align-items:center;gap:6px;background:var(--field);border:1px solid var(--bd);border-radius:9px;padding:4px 6px 4px 4px;font-size:12px;color:var(--tx);max-width:240px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
#dsdoc-app .dd-lifiles img{width:34px;height:26px;object-fit:cover;border-radius:5px}
#dsdoc-app .dd-lifiles button{background:transparent;border:0;color:var(--mut);font-size:15px;cursor:pointer;padding:0 2px}
#dsdoc-app .dd-lifn,#dsdoc-app .dd-lic{font-size:12.5px;color:var(--mut);align-self:center}
#dsdoc-app .dd-gal{position:fixed;inset:0;z-index:100000;background:rgba(4,7,13,.78);display:flex;align-items:center;justify-content:center;padding:24px}
#dsdoc-app .dd-gbox{width:min(1180px,96vw);height:min(860px,92vh);background:var(--card,#141a26);border:1px solid var(--bd);border-radius:18px;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.55);overflow:hidden}
#dsdoc-app .dd-ghd{display:flex;align-items:center;gap:14px;padding:18px 22px 12px}
#dsdoc-app .dd-ghd>div{flex:1;min-width:0}
#dsdoc-app .dd-ghd h3{margin:0;font-size:19px;font-weight:800;color:var(--tx)}
#dsdoc-app .dd-ghd p{margin:2px 0 0;font-size:12.5px;color:var(--mut)}
#dsdoc-app .dd-ghd input{width:min(300px,40vw);background:var(--field);border:1px solid var(--bd);border-radius:10px;padding:9px 12px;color:var(--tx);font-size:13.5px}
#dsdoc-app .dd-gx{background:transparent;border:1px solid var(--bd);color:var(--tx);width:36px;height:36px;border-radius:10px;font-size:22px;line-height:1;cursor:pointer}
#dsdoc-app .dd-gchips{padding:0 22px 10px;margin:0}
#dsdoc-app .dd-ggrid{flex:1;overflow:auto;display:grid;gap:16px;padding:8px 22px 24px;align-content:start;grid-auto-rows:max-content}
#dsdoc-app .dd-ggrid>*{min-height:max-content}
#dsdoc-app .dd-ggrid.des{grid-template-columns:repeat(auto-fill,minmax(200px,1fr))}
#dsdoc-app .dd-ggrid.thm{grid-template-columns:repeat(auto-fill,minmax(170px,1fr))}
#dsdoc-app .dd-gth .smp{height:74px;border-radius:10px;margin-bottom:8px;padding:8px 10px;display:flex;flex-direction:column;gap:5px;overflow:hidden}
#dsdoc-app .dd-gth .smp i{font-style:normal;font-weight:700;font-size:24px;line-height:1}
#dsdoc-app .dd-gth .smp em{display:block;height:6px;border-radius:3px;width:70%}
#dsdoc-app .dd-gal .dd-empty{grid-column:1/-1}
@media (max-width:640px){#dsdoc-app .dd-gal{padding:8px}#dsdoc-app .dd-ghd{flex-wrap:wrap}#dsdoc-app .dd-ghd input{width:100%;order:3}#dsdoc-app .dd-ggrid.des{grid-template-columns:repeat(2,minmax(0,1fr))}}
#dsdoc-app .dd-designs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;max-height:520px;overflow:auto;padding-right:6px}
#dsdoc-app .dd-ds{border:1px solid var(--bd);background:var(--field);border-radius:14px;padding:10px 10px 4px;color:var(--tx);text-align:left}
#dsdoc-app .dd-ds .pv{height:150px;border-radius:9px;overflow:hidden;background:#fff;position:relative}
#dsdoc-app .dd-ds .pv>div{position:absolute;left:0;top:0;transform-origin:0 0}
#dsdoc-app .dd-ds b{display:block;font-size:14px;font-weight:600;margin:10px 2px 0;color:var(--tx)}
#dsdoc-app .dd-ds span{display:block;font-size:11.5px;color:var(--mut);margin:2px 2px 8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsdoc-app .dd-ds.on{border-color:var(--blue2);box-shadow:0 0 0 1px var(--blue2)}
.dd-mk{position:relative!important;overflow:hidden!important;border-radius:9px}
.dd-mk>.mkp{position:absolute!important;overflow:hidden;background:#fff;border-radius:2px;box-shadow:0 6px 16px rgba(15,23,42,.28),0 1px 3px rgba(15,23,42,.18);transition:transform .25s ease,box-shadow .25s ease;transform-origin:50% 60%!important}
.dd-mk>.mkb{transform:rotate(3deg)}
button:hover>.dd-mk>.mkp,.on>.dd-mk>.mkp{transform:translateY(-4px) scale(1.04);box-shadow:0 16px 32px rgba(15,23,42,.36),0 2px 6px rgba(15,23,42,.2)}
button:hover>.dd-mk>.mkb,.on>.dd-mk>.mkb{transform:rotate(5deg) translate(5px,-7px) scale(1.02)}
#dsdoc-app .dd-pvbox{margin-top:18px;border:1px solid var(--bd);background:var(--card2);border-radius:14px;padding:14px}
#dsdoc-app .dd-pvbox .hd{display:flex;align-items:center;font-size:11.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--lab);margin-bottom:12px}
#dsdoc-app .dd-pvbox .hd span{margin-left:auto;color:var(--link);text-transform:none;letter-spacing:0;font-size:13px}
#dsdoc-app .dd-preview{display:flex;gap:10px;overflow:auto;min-height:200px}
#dsdoc-app .dd-preview .pp{flex:none;position:relative;overflow:hidden;border-radius:4px}
#dsdoc-app .dd-preview .pp>div{position:absolute;left:0;top:0;transform-origin:0 0}
#dsdoc-app .dd-note{font-size:12.5px;color:var(--mut);margin-top:14px;line-height:1.55}
#dsdoc-app .dd-sep{height:1px;background:rgba(255,255,255,.08);margin:44px 0 36px}
#dsdoc-app .dd-mh{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:22px}
#dsdoc-app .dd-mh .ib{width:38px;height:38px;border-radius:10px;background:var(--field);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;color:var(--tx)}
#dsdoc-app .dd-mh h2{margin:0;font-size:24px;font-weight:600;color:var(--tx)}
#dsdoc-app .dd-mh .n{background:var(--field);border:1px solid var(--bd);border-radius:8px;padding:4px 10px;font-size:13px;font-weight:600;color:var(--tx)}
#dsdoc-app .dd-mh .srch{margin-left:auto;width:220px}
#dsdoc-app .dd-mh .srch input{padding:9px 12px;font-size:14px;font-weight:500}
#dsdoc-app .dd-mh select{width:150px;padding:9px 12px;font-size:14px;font-weight:500}
#dsdoc-app .dd-mydocs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}
@media(max-width:900px){#dsdoc-app .dd-mydocs{grid-template-columns:repeat(2,minmax(0,1fr))}#dsdoc-app .dd-mh .srch{margin-left:0}}
#dsdoc-app .dd-md{background:var(--card);border:1px solid var(--bd);border-radius:18px;overflow:hidden;position:relative;cursor:pointer}
#dsdoc-app .dd-md:hover{border-color:var(--blue2)}
#dsdoc-app .dd-md .pv{height:210px;background:#e9ecf3;overflow:hidden;position:relative}
#dsdoc-app .dd-md .pv>div{position:absolute;left:0;top:0;transform-origin:0 0}
#dsdoc-app .dd-md .bd{padding:14px 16px 16px}
#dsdoc-app .dd-md b{display:block;font-size:15.5px;font-weight:600;color:var(--tx);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsdoc-app .dd-md .ds{font-size:13px;color:var(--mut2);margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsdoc-app .dd-md .dt{font-size:12.5px;color:var(--mut2);margin-top:12px;font-weight:600}
#dsdoc-app .dd-md .badge{position:absolute;left:12px;top:12px;background:rgba(15,23,42,.85);color:#fff;font-size:11.5px;font-weight:700;padding:4px 9px;border-radius:7px}
#dsdoc-app .dd-md .x{position:absolute;right:12px;top:12px;background:rgba(15,23,42,.85);color:#fff;border:0;border-radius:50%;width:30px;height:30px;font-size:14px;display:flex;align-items:center;justify-content:center}
#dsdoc-app .dd-more{display:block;margin:28px auto 0;background:var(--field);border:1px solid var(--bd);color:var(--tx);border-radius:10px;padding:12px 34px;font-size:13.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
#dsdoc-app .dd-empty{grid-column:1/-1;color:var(--mut2);font-size:14px;padding:18px 2px}
#dsdoc-app .dd-hide{display:none!important}
.dd-toast{position:fixed;left:50%;bottom:26px;transform:translateX(-50%);background:#111A2E;color:#E6EBF5;border:1px solid #2A3A5E;padding:11px 18px;border-radius:12px;font:600 13px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;z-index:2147483600;box-shadow:0 8px 30px rgba(0,0,0,.35);max-width:90vw}
.dd-toast.err{border-color:#7F1D1D;background:#2A1114;color:#FECACA}
.dd-toast.ok{border-color:#14532D;color:#BBF7D0}
.dd-spin{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:ddspin .8s linear infinite;vertical-align:-3px}
@keyframes ddspin{to{transform:rotate(360deg)}}

/* generation overlay */
.dd-gen{position:fixed;inset:0;background:rgba(5,9,18,.82);z-index:2147483000;display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#E6EBF5}
.dd-gen .box{width:min(920px,94vw);background:#0E1628;border:1px solid #1E2A44;border-radius:20px;display:grid;grid-template-columns:300px 1fr;overflow:hidden;max-height:90vh}
@media(max-width:760px){.dd-gen .box{grid-template-columns:1fr}.dd-gen .lv{display:none}}
.dd-gen .st{padding:24px}
.dd-gen h4{margin:0 0 4px;font-size:18px}.dd-gen p{margin:0 0 16px;color:#93A0B8;font-size:12.5px}
.dd-gen .steps{list-style:none;margin:0;padding:0;font-size:13px}
.dd-gen .steps li{display:flex;gap:10px;align-items:center;padding:7px 0;color:#93A0B8}
.dd-gen .steps li.on{color:#fff}.dd-gen .steps li.ok{color:#86EFAC}
.dd-gen .steps li i{width:18px;height:18px;border-radius:50%;border:2px solid #2A3A5E;flex:none;display:inline-flex;align-items:center;justify-content:center;font-style:normal;font-size:10px}
.dd-gen .steps li.ok i{background:#16A34A;border-color:#16A34A;color:#fff}.dd-gen .steps li.on i{border-color:#4C8DFF;border-top-color:transparent;animation:ddspin .8s linear infinite}
.dd-gen .bar{height:6px;background:#1E2A44;border-radius:6px;margin-top:16px;overflow:hidden}.dd-gen .bar span{display:block;height:100%;background:linear-gradient(90deg,#2F6FED,#8B5CF6);width:0;transition:width .4s}
.dd-gen .lv{background:#2A3550;overflow:auto;padding:16px;position:relative}
.dd-gen .lv>div{transform-origin:0 0}

/* editor */
.dd-ed{position:fixed;inset:0;z-index:2147482000;background:#2B3140;display:grid;grid-template-rows:52px 50px minmax(0,1fr) 54px;grid-template-columns:230px minmax(0,1fr) 300px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#E6EBF5}
@media(max-width:1100px){.dd-ed{grid-template-columns:0 1fr 280px}.dd-ed .dd-left{display:none}}
@media(max-width:760px){.dd-ed{grid-template-columns:0 1fr 0}.dd-ed .dd-right{display:none}}
.dd-ed button{font-family:inherit;cursor:pointer;text-transform:none;letter-spacing:normal;line-height:1.3;min-width:0;min-height:0;padding:0;box-shadow:none;text-shadow:none}
.dd-ed .dd-top{grid-column:1/4;grid-row:1;display:flex;align-items:center;gap:10px;padding:0 14px;background:#0E1628;border-bottom:1px solid #1E2A44}
.dd-ed .dd-top .bk{background:none;border:0;color:#93A0B8;font-size:12.5px;font-weight:600;letter-spacing:.06em}
.dd-ed .dd-top input{background:transparent;border:1px solid transparent;color:#fff;font:600 15px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;padding:6px 8px;border-radius:8px;min-width:120px;width:30vw}
.dd-ed .dd-top input:hover,.dd-ed .dd-top input:focus{border-color:#2A3A5E;outline:none}
.dd-ed .dd-top .sp{flex:1}
.dd-ed .eb{display:inline-flex;align-items:center;gap:7px;border:1px solid #2A3A5E;background:#162038;color:#E6EBF5;border-radius:9px;padding:8px 13px;font-size:12.5px;font-weight:600;letter-spacing:.03em}
.dd-ed .eb:hover{border-color:#4C8DFF}
.dd-ed .eb.pri{background:#2F6FED;border-color:#2F6FED;color:#fff}
.dd-ed .eb.org{background:#F0532B;border-color:#F0532B;color:#fff}
.dd-ed .eb[disabled]{opacity:.5;cursor:default}
.dd-ed .dd-saved{font-size:11.5px;color:#93A0B8}
.dd-ed .dd-left{grid-column:1;grid-row:2/4;background:#141C2E;border-right:1px solid #1E2A44;overflow:auto;padding:12px}
.dd-ed .dd-left h5,.dd-ed .dd-right h5{margin:6px 2px 10px;font-size:10.5px;letter-spacing:.14em;color:#7D8AA5;text-transform:uppercase}
.dd-ed .dstyle{display:flex;gap:10px;align-items:center;width:100%;border:1px dashed #2F3D5E;background:none;color:#E6EBF5;border-radius:10px;padding:10px;text-align:left;margin-bottom:12px}
.dd-ed .dstyle b{display:block;font-size:12.5px}.dd-ed .dstyle span{font-size:10.5px;color:#93A0B8}
.dd-ed .ol{list-style:none;margin:0;padding:0}
.dd-ed .ol li{display:flex;gap:8px;align-items:flex-start;padding:7px 8px;border-radius:8px;font-size:12px;color:#C9D2E3;cursor:pointer}
.dd-ed .ol li:hover{background:#1B2540}.dd-ed .ol li.on{background:#1F3058;color:#fff}
.dd-ed .ol li b{color:#7D8AA5;font-size:10.5px;min-width:18px}
.dd-ed .dd-canvas{grid-column:2;grid-row:3;overflow:auto;position:relative;padding:30px 20px 60px}
.dd-ed .dd-zoom{margin:0;position:relative;overflow:visible}
#dde-doc>#dsel-ghost{position:absolute;left:0;top:0;width:0;height:0;z-index:-1;opacity:.38;pointer-events:none}
#dde-doc>#dsel-ghost .dsd-L{pointer-events:auto;cursor:move}
#dsel-ov .dsel-tiny .dsel-h{--dsel-hs:8px;width:8px;height:8px}
#dsel-ov .dsel-tiny .dsel-h-nw{margin:-11px 0 0 -11px}#dsel-ov .dsel-tiny .dsel-h-ne{margin:-11px 0 0 3px}#dsel-ov .dsel-tiny .dsel-h-se{margin:3px 0 0 3px}#dsel-ov .dsel-tiny .dsel-h-sw{margin:3px 0 0 -11px}
#dsel-ov .dsel-micro .dsel-h-nw,#dsel-ov .dsel-micro .dsel-h-ne,#dsel-ov .dsel-micro .dsel-h-sw{display:none}
#dsel-ov .dsel-tiny .dsel-rot{margin-top:16px}.dd-ed .dd-canvas.dd-hand,.dd-ed .dd-canvas.dd-hand *{cursor:grab!important}.dd-ed .dd-canvas.dd-panning,.dd-ed .dd-canvas.dd-panning *{cursor:grabbing!important;user-select:none}.dd-ed #dde-doc{transform-origin:0 0;position:relative}
.dd-ed .dd-right{grid-column:3;grid-row:2/4;background:#141C2E;border-left:1px solid #1E2A44;overflow:auto;display:flex;flex-direction:column}
.dd-ed .rtabs{display:flex;border-bottom:1px solid #1E2A44;position:sticky;top:0;background:#141C2E;z-index:2}
.dd-ed .rtabs button{flex:1;background:none;border:0;color:#93A0B8;padding:12px 4px;font-size:11.5px;font-weight:600;letter-spacing:.05em;border-bottom:2px solid transparent}
.dd-ed .rtabs button.on{color:#fff;border-bottom-color:#4C8DFF}
.dd-ed .rpane{padding:12px}
.dd-ed .rds{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px}
.dd-ed .rds button{border:1px solid #24324F;background:#0F1729;border-radius:10px;padding:5px;color:#E6EBF5;text-align:left}
.dd-ed .rds button.on{border-color:#4C8DFF;box-shadow:0 0 0 1px #4C8DFF}
.dd-ed .rds .pv{height:150px;background:#fff;border-radius:6px;overflow:hidden;position:relative}
.dd-ed .rds .pv>div{position:absolute;left:0;top:0;transform-origin:0 0}
.dd-ed .rds b{display:block;font-size:11px;margin:5px 2px 1px}
.dd-ed .rth{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:7px}
.dd-ed .rth button{border:1px solid #24324F;background:#0F1729;border-radius:9px;padding:6px;color:#E6EBF5;text-align:left;font-size:11px}
.dd-ed .rth button.on{border-color:#4C8DFF}
.dd-ed .rth .sw{display:flex;height:14px;border-radius:4px;overflow:hidden;margin-bottom:5px}.dd-ed .rth .sw i{flex:1}
.dd-ed .rlab{display:block;font-size:10.5px;letter-spacing:.1em;color:#7D8AA5;text-transform:uppercase;margin:14px 0 6px}
.dd-ed .rpane select,.dd-ed .rpane input,.dd-ed .rpane textarea{width:100%;background:#0F1729;border:1px solid #24324F;color:#E6EBF5;border-radius:8px;padding:8px 10px;font:13px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif}
.dd-ed .rpane textarea{min-height:80px}
.dd-ed .rbtns{display:flex;flex-wrap:wrap;gap:6px}
.dd-ed .rbtns button{border:1px solid #24324F;background:#0F1729;color:#E6EBF5;border-radius:8px;padding:7px 10px;font-size:12px}
.dd-ed .rbtns button:hover{border-color:#4C8DFF}
.dd-ed .rnote{font-size:11.5px;color:#93A0B8;line-height:1.45}
.dd-ed .dd-bot{grid-column:1/4;grid-row:4;display:flex;align-items:center;gap:10px;padding:0 14px;background:#0E1628;border-top:1px solid #1E2A44}
.dd-ed .dd-bot .pg{font-size:12.5px;color:#C9D2E3;min-width:70px;text-align:center}
.dd-ed .dd-bot .z{display:flex;align-items:center;gap:6px;font-size:12px;color:#C9D2E3}
.dd-ed .dd-bot .z button{width:28px;height:28px;border-radius:8px;border:1px solid #2A3A5E;background:#162038;color:#fff}
.dd-ed .ftb{position:absolute;z-index:30;display:flex;gap:2px;background:#0E1628;border:1px solid #2A3A5E;border-radius:10px;padding:3px;box-shadow:0 6px 22px rgba(0,0,0,.35)}
.dd-ed .ftb button{background:none;border:0;color:#E6EBF5;min-width:30px;height:30px;border-radius:7px;font-size:13px;padding:0 7px}
.dd-ed .ftb button:hover{background:#1F2B47}
.dd-ed .ftb .sep{width:1px;background:#2A3A5E;margin:4px 2px}
.dd-ed .menu{position:absolute;z-index:120;background:#0E1628;border:1px solid #2A3A5E;border-radius:10px;padding:5px;box-shadow:0 10px 30px rgba(0,0,0,.4);min-width:190px;max-height:60vh;overflow:auto}
.dd-ed .menu button{display:flex;width:100%;gap:8px;background:none;border:0;color:#E6EBF5;text-align:left;padding:8px 10px;border-radius:7px;font-size:12.5px}
.dd-ed .menu button:hover{background:#1F2B47}
.dd-ed .menu .mh{font-size:10px;letter-spacing:.12em;color:#7D8AA5;text-transform:uppercase;padding:6px 10px 3px}
.dd-modal button,.dd-gen button{font-family:inherit;cursor:pointer;text-transform:none;letter-spacing:normal;line-height:1.3;min-width:0;min-height:0;padding:0;box-shadow:none;text-shadow:none}
.dd-io{position:fixed;inset:0;z-index:2147483150;background:rgba(2,6,14,.72);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Poppins,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.dd-io *{box-sizing:border-box}
.dd-io .io-box{background:#151922;border:1px solid #2A3140;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.6);color:#E8ECF4;padding:22px;max-height:92vh;display:flex;flex-direction:column}.io-box>*{flex-shrink:0}.io-box .io-dl{flex:1 1 auto;min-height:0;overflow:auto;flex-shrink:1}
.dd-io .io-sm{width:min(312px,94vw)}.dd-io .io-md{width:min(386px,94vw)}.dd-io .io-lg{width:min(830px,96vw);height:min(600px,92vh);padding:0}
.dd-io h3{margin:0 0 6px;font-size:17px;font-weight:700;text-align:center;color:#fff}
.dd-io .io-sub{margin:0 0 14px;font-size:12px;color:#A9B1C3;text-align:center}
.dd-io .io-b{all:unset;box-sizing:border-box;display:block;width:100%;min-height:44px;margin:0 0 9px;padding:8px 12px;border-radius:7px;font-size:12.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#fff;cursor:pointer;text-align:center}
.dd-io .io-b small{font-size:10.5px;font-weight:600;opacity:.85;text-transform:none;letter-spacing:0}
.dd-io .io-b em{display:block;margin-top:2px;font-style:normal;font-size:9.5px;font-weight:600;opacity:.85;letter-spacing:.05em}
.dd-io .io-ai{background:#3D4FE0}.dd-io .io-ai:hover{background:#4A5CF0}
.dd-io .io-st{background:#7B3FE4}.dd-io .io-st:hover{background:#8A4FF2}
.dd-io .io-up{background:#1F9D5A}.dd-io .io-up:hover{background:#24AE65}
.dd-io .io-x{background:transparent;border:1px solid #333B4B;color:#A9B1C3;min-height:32px;font-size:11.5px;margin:0}.dd-io .io-x:hover{background:#1E2430;color:#fff}
.dd-io .io-b[disabled]{opacity:.6;cursor:wait}
.dd-io .io-ta{width:100%;min-height:62px;resize:vertical;background:#0E1118;border:1px solid #2F3747;border-radius:8px;color:#fff;padding:10px;font:13px/1.4 inherit;font-family:inherit;margin-bottom:10px}
.dd-io .io-sel{width:100%;background:#0E1118;border:1px solid #2F3747;border-radius:8px;color:#fff;padding:7px 9px;font:12.5px inherit;font-family:inherit}
.dd-io .io-row{display:flex;gap:8px;margin-bottom:12px}.dd-io .io-row .io-b{margin:0;width:auto;flex:1;font-size:11.5px;min-height:40px;padding:12px 8px}.dd-io .io-row .io-x{flex:0 0 auto;padding:0 14px;min-height:40px}
.dd-io .io-res{display:flex;flex-wrap:wrap;gap:8px;font-size:12.5px;color:#A9B1C3;align-items:center}.dd-io .io-res:empty{display:none}
.dd-io .io-pick{all:unset;cursor:pointer;border-radius:8px;overflow:hidden;border:2px solid transparent;width:100%}.dd-io .io-pick:hover{border-color:#5B6CFF}.dd-io .io-pick img{display:block;width:100%;max-height:220px;object-fit:cover}
.dd-io .io-link{all:unset;cursor:pointer;display:block;margin:12px auto 0;font-size:11.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#34D399;text-decoration:underline}
.dd-io .io-err{color:#FCA5A5;font-size:12.5px;margin:0}
.dd-io .io-hd{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #262D3B}.dd-io .io-hd b{font-size:15px;color:#fff;flex:1}
.dd-io .io-q{width:200px;background:#1C2130;border:1px solid #3A4257;border-radius:7px;color:#fff;padding:7px 10px;font:12.5px inherit;font-family:inherit}
.dd-io .io-close{all:unset;cursor:pointer;padding:5px 10px;border:1px solid #3A4257;border-radius:6px;font-size:11.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#CBD3E3}.dd-io .io-close:hover{background:#232A3A}
.dd-io .io-tabs{display:flex;gap:7px;padding:10px 12px;overflow-x:auto;scrollbar-color:#3A4257 transparent;border-bottom:1px solid #262D3B;flex:none;scrollbar-width:thin}
.dd-io .io-tabs button{all:unset;cursor:pointer;white-space:nowrap;padding:4px 11px;border-radius:999px;border:1px solid #3A4257;font-size:10.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#CBD3E3}.dd-io .io-tabs button.on{background:#3D5AFE;border-color:#3D5AFE;color:#fff}
.dd-io .io-grid{flex:1;overflow:auto;padding:12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));grid-auto-rows:max-content;gap:10px;align-content:start;min-height:0}
.dd-io .io-card{all:unset;cursor:pointer;background:#1C2130;border:1px solid #2B3243;border-radius:8px;overflow:hidden;display:flex;flex-direction:column}.dd-io .io-card:hover{border-color:#5B6CFF}
.dd-io .io-card.io-busy{opacity:.6;cursor:wait}
.dd-io .io-th{position:relative;display:block;aspect-ratio:16/10;background:#0E1118}.dd-io .io-th img{width:100%;height:100%;object-fit:cover;display:block}
.dd-io .io-th.io-na img{visibility:hidden}.dd-io .io-th.io-na::after{content:"Image not available";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;color:#7C8599}
.dd-io .io-tier{position:absolute;left:6px;top:6px;padding:2px 7px;border-radius:5px;background:rgba(0,0,0,.65);color:#fff;font-style:normal;font-size:10px;font-weight:700}.dd-io .io-tier.pro{background:#F59E0B;color:#111}
.dd-io .io-tt{display:block;padding:7px 8px;font-size:11px;color:#CBD3E3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dd-io .io-ft{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid #262D3B;font-size:11.5px;color:#A9B1C3}
.dd-io .io-bar{height:6px;border-radius:99px;background:#232A3A;overflow:hidden;margin-top:6px}.dd-io .io-bar i{display:block;height:100%;width:3%;background:linear-gradient(90deg,#3D5AFE,#A78BFA);transition:width .3s}
.dd-io .io-dl{display:flex;flex-direction:column;gap:6px}
.dd-io .io-dr{display:grid;grid-template-columns:20px 1fr auto;grid-template-rows:auto auto;column-gap:8px;align-items:center;padding:8px 10px;border:1px solid #2F3747;border-radius:8px;cursor:pointer;font-size:12.5px;color:#E8ECF4}
.dd-io .io-dr:hover{border-color:#5B6CFF}.dd-io .io-dr input{grid-row:1/3;margin:0;accent-color:#5B6CFF}
.dd-io .io-dr:has(input:checked){border-color:#5B6CFF;background:rgba(91,108,255,.12)}
.dd-io .io-dn b{color:#fff}.dd-io .io-dd{grid-column:2;font-size:11px;color:#A9B1C3}.dd-io .io-dr em{grid-row:1/3;grid-column:3;font-style:normal;font-size:11px;color:#A9B1C3;white-space:nowrap}
.dd-io .io-dr.io-off{opacity:.45;cursor:not-allowed}
.dd-io .io-more{all:unset;cursor:pointer;padding:5px 10px;border-radius:6px;background:#1E2A50;border:1px solid #3D5AFE;color:#C7D2FE;font-size:10.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
#dsel-ov .dsel-imgbtns{position:absolute;right:8px;top:8px;display:flex;gap:6px;pointer-events:none}
#dsel-ov .dsel-tiny .dsel-imgbtns{display:none}
.dd-crop{position:fixed;inset:0;z-index:2147483150;background:rgba(2,6,14,.78);display:flex;align-items:center;justify-content:center;padding:16px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.dd-crop *{box-sizing:border-box}
.dd-crop .cr-box{width:min(900px,96vw);height:min(640px,92vh);background:#151922;border:1px solid #2A3140;border-radius:14px;display:flex;flex-direction:column;overflow:hidden;color:#E8ECF4;box-shadow:0 20px 60px rgba(0,0,0,.6)}
.dd-crop .cr-hd{display:flex;align-items:center;gap:12px;padding:13px 16px;border-bottom:1px solid #262D3B}.dd-crop .cr-hd b{display:flex;align-items:center;gap:6px;font-size:15px}.dd-crop .cr-hd b svg{width:16px;height:16px}.dd-crop .cr-hd span{font-size:12px;color:#A9B1C3}
.dd-crop .cr-stage{flex:1;display:flex;align-items:center;justify-content:center;background:repeating-conic-gradient(#1b2030 0 25%,#202638 0 50%) 0 0/18px 18px;min-height:0}
.dd-crop .cr-wrap{position:relative;touch-action:none;user-select:none}.dd-crop .cr-wrap img{display:block;width:100%;height:100%;pointer-events:none}
.dd-crop .cr-dim{position:absolute;inset:0;background:rgba(0,0,0,.6);pointer-events:none}
.dd-crop .cr-fr{position:absolute;border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.5);cursor:move}
.dd-crop .cr-fr u{position:absolute;inset:0;pointer-events:none;background:linear-gradient(rgba(255,255,255,.35),rgba(255,255,255,.35)) 33.33% 0/1px 100% no-repeat,linear-gradient(rgba(255,255,255,.35),rgba(255,255,255,.35)) 66.66% 0/1px 100% no-repeat,linear-gradient(rgba(255,255,255,.35),rgba(255,255,255,.35)) 0 33.33%/100% 1px no-repeat,linear-gradient(rgba(255,255,255,.35),rgba(255,255,255,.35)) 0 66.66%/100% 1px no-repeat}
.dd-crop .cr-fr i{position:absolute;width:14px;height:14px;background:#fff;border:2px solid #3B82F6;border-radius:3px;margin:-7px 0 0 -7px;z-index:1}
.dd-crop .cr-fr i[data-c=nw]{left:0;top:0;cursor:nwse-resize}.dd-crop .cr-fr i[data-c=n]{left:50%;top:0;cursor:ns-resize}.dd-crop .cr-fr i[data-c=ne]{left:100%;top:0;cursor:nesw-resize}.dd-crop .cr-fr i[data-c=e]{left:100%;top:50%;cursor:ew-resize}
.dd-crop .cr-fr i[data-c=se]{left:100%;top:100%;cursor:nwse-resize}.dd-crop .cr-fr i[data-c=s]{left:50%;top:100%;cursor:ns-resize}.dd-crop .cr-fr i[data-c=sw]{left:0;top:100%;cursor:nesw-resize}.dd-crop .cr-fr i[data-c=w]{left:0;top:50%;cursor:ew-resize}
.dd-crop .cr-ft{display:flex;align-items:center;gap:8px;padding:12px 16px;border-top:1px solid #262D3B;flex-wrap:wrap}
.dd-crop .cr-ar{display:flex;gap:5px;flex-wrap:wrap}.dd-crop .cr-ar button{all:unset;cursor:pointer;padding:5px 10px;border-radius:999px;border:1px solid #3A4257;font-size:12px;color:#CBD3E3}.dd-crop .cr-ar button.on{background:#3D5AFE;border-color:#3D5AFE;color:#fff}
.dd-crop .cr-sp{flex:1}
.dd-crop .cr-btn{all:unset;cursor:pointer;padding:8px 14px;border-radius:8px;border:1px solid #3A4257;font-size:13px;font-weight:600;color:#E8ECF4}.dd-crop .cr-btn:hover{background:#232A3A}.dd-crop .cr-btn.pri{background:#2563EB;border-color:#2563EB;color:#fff}.dd-crop .cr-btn.pri:hover{background:#1D4ED8}
#dsel-ov .dsel-pbtn{all:unset;position:relative;display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:7px;background:rgba(15,23,42,.9);color:#fff;font:600 12px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;cursor:pointer;pointer-events:auto;box-shadow:0 2px 8px rgba(0,0,0,.35);white-space:nowrap}
#dsel-ov .dsel-pbtn:hover{background:#2563EB}#dsel-ov .dsel-pbtn svg{width:14px;height:14px}
#dsel-ov .dsel-imgbtns .dsel-pbtn{pointer-events:auto}
.dd-modal{position:fixed;inset:0;background:rgba(5,9,18,.7);z-index:2147483100;display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#E6EBF5}
.dd-modal .mb{width:min(860px,94vw);max-height:88vh;background:#0E1628;border:1px solid #1E2A44;border-radius:18px;display:flex;flex-direction:column;overflow:hidden}
.dd-modal .mh2{display:flex;align-items:center;gap:8px;padding:14px 16px;border-bottom:1px solid #1E2A44}
.dd-modal .mh2 b{font-size:15px;flex:1}
.dd-modal .mh2 button.c{background:none;border:0;color:#93A0B8;font-size:22px;line-height:1}
.dd-modal .tabs{display:flex;gap:6px;padding:10px 16px 0}
.dd-modal .tabs button{border:1px solid #24324F;background:#0F1729;color:#93A0B8;border-radius:16px;padding:6px 12px;font-size:12px}
.dd-modal .tabs button.on{background:#2F6FED;border-color:#2F6FED;color:#fff}
.dd-modal .bd{padding:14px 16px;overflow:auto}
.dd-modal .srch{display:flex;gap:8px;margin-bottom:12px}
.dd-modal input,.dd-modal textarea,.dd-modal select{background:#0F1729;border:1px solid #24324F;color:#E6EBF5;border-radius:9px;padding:9px 11px;font:13px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;width:100%}
.dd-modal .imgs{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px}
.dd-modal .imgs button{border:1px solid #24324F;background:#0F1729;border-radius:9px;padding:0;overflow:hidden;height:100px;position:relative}
.dd-modal .imgs img{width:100%;height:100%;object-fit:cover;display:block}
.dd-modal .imgs .pro{position:absolute;right:5px;top:5px;background:#F59E0B;color:#111;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:6px}
.dd-modal .mf{display:flex;gap:8px;justify-content:flex-end;padding:12px 16px;border-top:1px solid #1E2A44}
.dd-modal .btn{display:inline-flex;align-items:center;gap:6px;border:1px solid #2A3A5E;background:#162038;color:#E6EBF5;border-radius:9px;padding:9px 14px;font-size:13px;font-weight:600}
.dd-modal .btn.pri{background:#2F6FED;border-color:#2F6FED;color:#fff}
body.dd-noscroll{overflow:hidden!important}
@media print{body.dd-printing>*:not(.dd-print){display:none!important}.dd-print{display:block!important}}

/* review the written text (before designing) */
.dd-rv{position:fixed;inset:0;background:rgba(3,6,12,.82);z-index:2147482500;display:flex;align-items:center;justify-content:center;color:#e9ecf5}
.dd-rv.dd-rv.dd-rv.dd-rv.dd-rv *{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif!important;box-sizing:border-box}
.dd-rv button{cursor:pointer;text-transform:none;letter-spacing:normal;line-height:1.3;min-width:0;min-height:0;box-shadow:none}
.dd-rv .rv-box{width:min(1100px,96vw);height:min(92vh,980px);background:#0f1422;border:1px solid rgba(255,255,255,.08);border-radius:20px;display:flex;flex-direction:column;overflow:hidden}
.dd-rv .rv-hd{display:flex;gap:16px;align-items:flex-start;padding:22px 26px 16px;border-bottom:1px solid rgba(255,255,255,.07)}
.dd-rv .rv-hd h3{margin:0 0 6px;font-size:21px;font-weight:700;color:#fff}
.dd-rv .rv-hd p{margin:0;font-size:13.5px;color:#9aa3b5;line-height:1.55;max-width:760px}
.dd-rv .rv-hd p b{color:#e9ecf5}
.dd-rv .rv-x{margin-left:auto;background:#1a2030;border:1px solid rgba(255,255,255,.08);color:#cbd5e1;width:36px;height:36px;border-radius:50%;font-size:20px;padding:0;flex:none}
.dd-rv .rv-main{flex:1;display:grid;grid-template-columns:240px 1fr;min-height:0}
@media(max-width:800px){.dd-rv .rv-main{grid-template-columns:1fr}.dd-rv .rv-nav{display:none}}
.dd-rv .rv-nav{border-right:1px solid rgba(255,255,255,.07);overflow:auto;padding:14px 10px}
.dd-rv .rv-nav a{display:flex;gap:10px;padding:8px 10px;border-radius:9px;color:#c9d2e3;text-decoration:none;font-size:13px;line-height:1.35}
.dd-rv .rv-nav a:hover{background:#1a2030}
.dd-rv .rv-nav a b{color:#6ea8ff;font-size:11.5px;min-width:18px;padding-top:1px}
.dd-rv .rv-body{overflow:auto;padding:20px 26px 40px}
.dd-rv .rv-doc{background:#141927;border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:14px 16px;margin-bottom:18px}
.dd-rv .rv-sec{background:#141927;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px;margin-bottom:18px;scroll-margin-top:10px}
.dd-rv .rv-sh{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.dd-rv .rv-st{font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#9aa3b5}
.dd-rv .rv-acts{margin-left:auto;display:flex;gap:4px;flex:none}
.dd-rv .rv-acts button{background:#1a2030;border:1px solid rgba(255,255,255,.08);color:#cbd5e1;width:30px;height:30px;border-radius:8px;font-size:13px;padding:0;display:inline-flex;align-items:center;justify-content:center}
.dd-rv .rv-acts button:hover{border-color:#3b6cf0;color:#fff}
.dd-rv .rv-blk{background:#0d111b;border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:10px 12px 12px;margin-bottom:10px}
.dd-rv .rv-bh{display:flex;align-items:center;margin-bottom:8px}
.dd-rv .rv-tag{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#60a5fa;background:rgba(37,99,235,.14);padding:3px 8px;border-radius:6px}
.dd-rv .rv-in{width:100%;background:#1a2030;border:1px solid rgba(255,255,255,.08);color:#e9ecf5;border-radius:9px;padding:9px 11px;font-size:14px;outline:none;line-height:1.5;resize:vertical;font-weight:400;height:auto;margin:0}
.dd-rv .rv-in:focus{border-color:#3b6cf0}
.dd-rv .rv-in.big{font-size:16px;font-weight:600}
.dd-rv .rv-in.hd{font-size:17px;font-weight:700;background:#0d111b}
.dd-rv .rv-in.th{font-weight:700;background:#222a3d}
.dd-rv .rv-lab{display:block;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#9aa3b5;margin:10px 0 5px}
.dd-rv .rv-lab i{font-style:normal;text-transform:none;letter-spacing:0;font-weight:500;color:#6b7280}
.dd-rv .rv-two{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.dd-rv .rv-grid{display:grid;gap:5px;overflow:auto}
.dd-rv .rv-gh{font-size:11px;font-weight:700;color:#9aa3b5;align-self:center;padding-left:4px}
.dd-rv .rv-rows{display:flex;flex-direction:column;gap:6px}
.dd-rv .rv-rowi{display:flex;gap:6px;align-items:center}
.dd-rv .rv-ct select.rv-in{flex:0 0 150px;width:150px}
.dd-rv .rv-ct input.rv-in{flex:1}
.dd-rv .rv-del{background:none;border:0;color:#94a3b8;font-size:18px;width:26px;flex:none;padding:0}
.dd-rv .rv-mini{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}
.dd-rv .rv-mini button,.dd-rv .rv-add button{background:#1a2030;border:1px solid rgba(255,255,255,.08);color:#cbd5e1;border-radius:8px;padding:6px 10px;font-size:12.5px}
.dd-rv .rv-add{display:flex;gap:8px;align-items:center;margin-top:6px}
.dd-rv .rv-add select{background:#1a2030;border:1px dashed rgba(255,255,255,.18);color:#cbd5e1;border-radius:9px;padding:8px 10px;font-size:13px;width:auto;flex:1;max-width:280px}
.dd-rv .rv-chartpv{margin-top:10px;background:#fff;border-radius:10px;padding:8px;max-width:560px}
.dd-rv .rv-chartpv svg{display:block;width:100%;height:auto}
.dd-rv .rv-note{font-size:12.5px;color:#9aa3b5;margin:6px 0 0}
.dd-rv .rv-ft{display:flex;align-items:center;gap:10px;padding:14px 26px;border-top:1px solid rgba(255,255,255,.07);background:#0d111b}
.dd-rv .rv-info{flex:1;font-size:13px;color:#9aa3b5}
.dd-rv .rv-btn{background:#1a2030;border:1px solid rgba(255,255,255,.1);color:#e9ecf5;border-radius:12px;padding:13px 20px;font-size:14.5px;font-weight:700}
.dd-rv .rv-btn.pri{background:#2563eb;border-color:#2563eb;color:#fff;text-transform:uppercase;letter-spacing:.03em}
.dd-rv .rv-btn.pri:hover{background:#1d4ed8}

/* the theme forces Poppins on everything; the editor, overlays and popups use the same system font as the AI Presentation editor */
.dd-ed.dd-ed.dd-ed,.dd-ed.dd-ed.dd-ed *:not(.dsd-doc):not(.dsd-doc *),.dd-gen.dd-gen.dd-gen *:not(.dsd-doc):not(.dsd-doc *),.dd-modal.dd-modal.dd-modal *,.dd-toast.dd-toast.dd-toast{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif!important}

/* element bar above the page (like the presentation editor) */
.dd-ed .dd-elbar{grid-column:2;grid-row:2;display:flex;align-items:center;gap:8px;padding:0 10px;background:#111827;border-bottom:1px solid #1E2A44;min-width:0}
.dd-ed .dl-left{flex:1;min-width:0;display:flex;align-items:center;gap:3px;overflow-x:auto;scrollbar-width:thin;padding:4px 0}
.dd-ed .dl-right{flex:none;display:flex;align-items:center;gap:3px;border-left:1px solid #26324d;padding-left:8px}
.dd-ed .dl-hint{font-size:12px;color:#7D8AA5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dd-ed .dl-cnt{font-size:12px;color:#C9D2E3;font-weight:600;padding:0 6px;white-space:nowrap}
.dd-ed .dl-b{display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 9px;border-radius:8px;border:1px solid transparent;background:none;color:#D5DCEA;font-size:12.5px;font-weight:600;white-space:nowrap;flex:none}
.dd-ed .dl-b:hover{background:#1C2741;border-color:#2A3A5E}
.dd-ed .dl-b.on{background:#1F3058;border-color:#3B6CF0;color:#fff}
.dd-ed .dl-b svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex:none}
.dd-ed .dl-left .dl-b span{display:none}.dd-ed .dl-left .dl-b.lbl span{display:inline}
.dd-ed .dl-b.sq{width:32px;padding:0;justify-content:center;font-size:14px}
.dd-ed .dl-b.dl-red:hover{color:#F87171;border-color:#7F1D1D;background:#2A1114}
.dd-ed .dl-sw i{display:block;width:18px;height:18px;border-radius:5px;border:2px solid #fff;box-shadow:0 0 0 1px #2A3A5E}
.dd-ed .dl-sel,.dd-ed .dl-num{height:32px;background:#0F1729;border:1px solid #24324F;color:#E6EBF5;border-radius:8px;padding:0 8px;font-size:12.5px;flex:none}
.dd-ed .dl-sel{width:150px}.dd-ed .dl-num{width:62px}
.dd-ed .dl-div{width:1px;height:24px;background:#26324d;margin:0 3px}
@media(max-width:1400px){.dd-ed .dl-right .dl-b span{display:none}.dd-ed .dl-right .dl-b{padding:0 8px}}

/* selection frames and handles */
.dd-ed .dl-ov{position:absolute;left:0;top:0;width:0;height:0;pointer-events:none;z-index:50}
.dd-ed .dl-fr{position:absolute;outline:calc(1.5px / var(--z,1)) solid #3B82F6;transform-origin:50% 50%;pointer-events:none}
.dd-ed .dl-fr.multi{outline-style:dashed}.dd-ed .dl-fr.lock{outline-color:#F59E0B}
.dd-ed .dl-h{position:absolute;width:calc(11px / var(--z,1));height:calc(11px / var(--z,1));margin:calc(-5.5px / var(--z,1)) 0 0 calc(-5.5px / var(--z,1));background:#fff;border:calc(1.5px / var(--z,1)) solid #3B82F6;border-radius:3px;pointer-events:auto;box-sizing:border-box}
.dd-ed .h-nw{left:0;top:0;cursor:nwse-resize}.dd-ed .h-n{left:50%;top:0;cursor:ns-resize}.dd-ed .h-ne{left:100%;top:0;cursor:nesw-resize}.dd-ed .h-e{left:100%;top:50%;cursor:ew-resize}
.dd-ed .h-se{left:100%;top:100%;cursor:nwse-resize}.dd-ed .h-s{left:50%;top:100%;cursor:ns-resize}.dd-ed .h-sw{left:0;top:100%;cursor:nesw-resize}.dd-ed .h-w{left:0;top:50%;cursor:ew-resize}
.dd-ed .dl-rot{left:50%;top:calc(-26px / var(--z,1));border-radius:50%;cursor:grab;background:#3B82F6;border-color:#fff}
.dd-ed .dl-lk{position:absolute;right:calc(-8px / var(--z,1));top:calc(-8px / var(--z,1));font-size:calc(12px / var(--z,1))}
.dd-ed .dl-mq{position:absolute;border:calc(1px / var(--z,1)) solid #3B82F6;background:rgba(59,130,246,.12)}
.dd-ed .dl-gv,.dd-ed .dl-gh{position:absolute;background:#EC4899}
.dd-ed .dl-gv{width:calc(1px / var(--z,1))}.dd-ed .dl-gh{height:calc(1px / var(--z,1))}

/* popovers */
.dd-ed .dl-pop{position:fixed;z-index:60;width:250px;max-height:min(70vh,620px);overflow:auto;background:#0E1628;border:1px solid #2A3A5E;border-radius:12px;padding:10px 12px 12px;box-shadow:0 14px 40px rgba(0,0,0,.45);color:#E6EBF5;font-size:12.5px}
.dd-ed .dl-pop.wide{width:300px}.dd-ed .dl-pop.layers{width:330px}
.dd-ed .dl-pop.menu{width:auto;min-width:170px;padding:5px}
.dd-ed .dl-pop h6{display:flex;align-items:center;gap:8px;margin:8px 0 8px;font-size:10.5px;letter-spacing:.12em;text-transform:uppercase;color:#7D8AA5;font-weight:700}
.dd-ed .dl-pop h6:first-child{margin-top:2px}
.dd-ed .dl-pop h6 .dl-pg{color:#C9D2E3;letter-spacing:.04em}.dd-ed .dl-pop h6 .dl-small{margin-left:auto}
.dd-ed .dl-mi{display:block;width:100%;text-align:left;background:none;border:0;color:#E6EBF5;padding:8px 10px;border-radius:7px;font-size:13px}
.dd-ed .dl-mi:hover{background:#1F2B47}
.dd-ed .dl-seg{display:flex;background:#0A101E;border:1px solid #24324F;border-radius:9px;padding:3px;gap:3px;margin-bottom:8px}
.dd-ed .dl-seg button{flex:1;background:none;border:0;color:#93A0B8;border-radius:6px;padding:6px 4px;font-size:12px;font-weight:600}
.dd-ed .dl-seg button.on{background:#2563EB;color:#fff}
.dd-ed .dl-cg{display:grid;grid-template-columns:repeat(9,1fr);gap:5px;margin-bottom:7px}
.dd-ed .dl-c{aspect-ratio:1;border-radius:6px;border:1px solid rgba(255,255,255,.18);padding:0}
.dd-ed .dl-c.on{box-shadow:0 0 0 2px #0E1628,0 0 0 4px #3B82F6}
.dd-ed .dl-crow{display:flex;gap:6px;align-items:center;margin-bottom:6px}
.dd-ed .dl-crow input[type=color]{width:38px;height:30px;border:1px solid #24324F;border-radius:7px;background:#0F1729;padding:2px}
.dd-ed .dl-hex{flex:1;height:30px;background:#0F1729;border:1px solid #24324F;color:#E6EBF5;border-radius:7px;padding:0 8px;font-size:12.5px;text-transform:uppercase}
.dd-ed .dl-sl{display:grid;grid-template-columns:92px 1fr 42px;align-items:center;gap:6px;margin:6px 0;font-size:12px;color:#C9D2E3}
.dd-ed .dl-sl input{width:100%;accent-color:#3B82F6}.dd-ed .dl-sl em{font-style:normal;text-align:right;color:#93A0B8;font-size:11.5px}
.dd-ed .dl-gpre{display:grid;grid-template-columns:repeat(8,1fr);gap:5px;margin-bottom:8px}
.dd-ed .dl-gpre button{height:22px;border-radius:5px;border:1px solid rgba(255,255,255,.15);padding:0}
.dd-ed .dl-dirs{display:grid;grid-template-columns:repeat(8,1fr);gap:4px;margin-bottom:4px}
.dd-ed .dl-dirs button{height:26px;border-radius:6px;border:1px solid #24324F;background:#0F1729;color:#C9D2E3;font-size:13px;padding:0}
.dd-ed .dl-dirs button.on{border-color:#3B82F6;color:#fff;background:#1F3058}
.dd-ed .dl-bar-g{height:16px;border-radius:5px;margin:8px 0;border:1px solid rgba(255,255,255,.15)}
.dd-ed .dl-stop{display:grid;grid-template-columns:34px 1fr 1fr 20px;gap:6px;align-items:center;margin-bottom:6px}
.dd-ed .dl-stop input[type=color]{width:34px;height:28px;border:1px solid #24324F;border-radius:6px;background:#0F1729;padding:2px}
.dd-ed .dl-stop label{font-size:10.5px;color:#93A0B8;display:flex;flex-direction:column;gap:2px}.dd-ed .dl-stop input[type=range]{width:100%;accent-color:#3B82F6}
.dd-ed .dl-stop button{background:none;border:0;color:#93A0B8;font-size:16px;padding:0}
.dd-ed .dl-small{background:#162038;border:1px solid #2A3A5E;color:#C9D2E3;border-radius:7px;padding:5px 9px;font-size:11.5px;font-weight:600;letter-spacing:0;text-transform:none}
.dd-ed .dl-small:hover{border-color:#3B82F6;color:#fff}
.dd-ed .dl-shapes{display:grid;grid-template-columns:repeat(5,1fr);gap:6px}
.dd-ed .dl-shapes button{height:44px;border-radius:8px;border:1px solid #24324F;background:#0F1729;display:flex;align-items:center;justify-content:center;padding:0}
.dd-ed .dl-shapes button:hover,.dd-ed .dl-icons button:hover{border-color:#3B82F6;background:#16223D}
.dd-ed .dl-isr{display:flex;gap:6px;margin-bottom:8px}
.dd-ed .dl-isr input{flex:1;min-width:0;height:32px;background:#0F1729;border:1px solid #24324F;color:#E6EBF5;border-radius:8px;padding:0 9px;font-size:12.5px}
.dd-ed .dl-icons{display:grid;grid-template-columns:repeat(6,1fr);gap:5px;max-height:300px;overflow:auto;min-height:40px}
.dd-ed .dl-icons button{aspect-ratio:1;border-radius:8px;border:1px solid #24324F;background:#0F1729;display:flex;align-items:center;justify-content:center;padding:0}
.dd-ed .dl-icons .dl-note,.dd-ed .dl-icons .dd-spin{grid-column:1/-1}.dd-ed .dl-note{font-size:11.5px;color:#7D8AA5;margin:8px 0 0;line-height:1.45}
.dd-ed .dl-grid2,.dd-ed .dl-grid3{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-bottom:6px}.dd-ed .dl-grid3{grid-template-columns:1fr 1fr 1fr}
.dd-ed .dl-grid2 button,.dd-ed .dl-grid3 button{background:#0F1729;border:1px solid #24324F;color:#D5DCEA;border-radius:7px;padding:7px 4px;font-size:12px}
.dd-ed .dl-grid2 button:hover,.dd-ed .dl-grid3 button:hover{border-color:#3B82F6;color:#fff}
.dd-ed .dl-chk,.dd-rv .dl-chk{display:flex;align-items:center;gap:7px;font-size:12.5px;color:#C9D2E3;margin:6px 0}
.dd-ed .dl-nums{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
.dd-ed .dl-nums label{display:flex;flex-direction:column;gap:3px;font-size:10.5px;color:#93A0B8}
.dd-ed .dl-nums input{height:30px;background:#0F1729;border:1px solid #24324F;color:#E6EBF5;border-radius:7px;padding:0 7px;font-size:12.5px;width:100%}
.dd-ed .dl-ls{margin-bottom:8px}.dd-ed .dl-lsh{font-size:10.5px;color:#7D8AA5;padding:4px 2px;border-bottom:1px solid #1E2A44;margin-bottom:4px}
.dd-ed .dl-row{display:flex;align-items:center;gap:7px;padding:4px 6px;border-radius:8px;cursor:pointer;border:1px solid transparent}
.dd-ed .dl-row:hover{background:#16223D}.dd-ed .dl-row.on{background:#1F3058;border-color:#3B6CF0}
.dd-ed .dl-row.in{margin-left:18px}.dd-ed .dl-row.off{opacity:.5}.dd-ed .dl-row.drop{box-shadow:inset 0 2px 0 #3B82F6}
.dd-ed .dl-row .th{width:34px;height:26px;flex:none;background:#fff;border-radius:5px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.dd-ed .dl-row .th svg{max-width:30px;max-height:22px}
.dd-ed .dl-tt{font-weight:800;color:#334155;font-size:13px}.dd-ed .grp .dl-tt{color:#93A0B8}
.dd-ed .dl-row .nm{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12.5px}
.dd-ed .dl-pgt{font-size:10.5px;color:#7D8AA5;flex:none}
.dd-ed .dl-ic{background:none;border:0;color:#C9D2E3;width:24px;height:24px;border-radius:6px;font-size:12px;padding:0;flex:none}
.dd-ed .dl-ic:hover{background:#25314f}
.dd-ed .dl-fold{background:none;border:0;color:#93A0B8;width:16px;padding:0;font-size:11px}
.dd-ed .dl-rn{flex:1;min-width:0;height:24px;background:#0F1729;border:1px solid #3B82F6;color:#fff;border-radius:6px;padding:0 6px;font-size:12.5px}
.dd-ed .dl-ctx{position:fixed;z-index:70;min-width:220px;background:#0E1628;border:1px solid #2A3A5E;border-radius:10px;padding:5px;box-shadow:0 14px 40px rgba(0,0,0,.45)}
.dd-ed .dl-ctx button{display:flex;width:100%;align-items:center;gap:10px;background:none;border:0;color:#E6EBF5;padding:7px 10px;border-radius:7px;font-size:12.5px;text-align:left}
.dd-ed .dl-ctx button span{flex:1}.dd-ed .dl-ctx button em{font-style:normal;color:#7D8AA5;font-size:11px}
.dd-ed .dl-ctx button:hover{background:#1F2B47}.dd-ed .dl-ctx button[disabled]{opacity:.4;cursor:default}
.dd-ed .dl-ctx hr{border:0;border-top:1px solid #1E2A44;margin:4px 0}


/* ===== element editor: the AI Presentation editor's own styles (bars, panels, layers, menus, handles) ===== */
#dsel-ov{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:60}
#dsel-ov .dsel-picload{position:absolute;display:none;flex-direction:column;align-items:center;justify-content:center;gap:10px;background:rgba(10,14,22,.55);z-index:5;pointer-events:none;color:#E8EEF7;font:600 13px/1.3 Inter,Arial,sans-serif;text-shadow:0 1px 3px rgba(0,0,0,.6)}
#dsel-ov .dsel-picload i{width:34px;height:34px;box-sizing:border-box;border:3px solid rgba(255,255,255,.25);border-top-color:#4A9EFF;border-radius:50%;animation:dselPicSpin .8s linear infinite}
@keyframes dselPicSpin{to{transform:rotate(360deg)}}
#dsel-ov .dsel-fr{position:absolute;box-sizing:border-box;outline:2px solid #6EA8FF;outline-offset:1px;pointer-events:none;min-width:2px;min-height:2px}
#dsel-ov .dsel-fr.dsel-edited{outline-color:#8B7CFF}
#dsel-ov .dsel-mv{position:absolute;pointer-events:auto;cursor:move}
#dsel-ov .dsel-h{position:absolute;width:var(--dsel-hs,11px);height:var(--dsel-hs,11px);margin:calc(var(--dsel-hs,11px) / -2) 0 0 calc(var(--dsel-hs,11px) / -2);background:#fff;border:1.5px solid #3B82F6;border-radius:2px;box-sizing:border-box;pointer-events:auto;box-shadow:0 1px 3px rgba(0,0,0,.35)}
#dsel-ov .dsel-h-nw{left:0;top:0;cursor:nwse-resize}#dsel-ov .dsel-h-n{left:50%;top:0;cursor:ns-resize}#dsel-ov .dsel-h-ne{left:100%;top:0;cursor:nesw-resize}
#dsel-ov .dsel-h-e{left:100%;top:50%;cursor:ew-resize}#dsel-ov .dsel-h-se{left:100%;top:100%;cursor:nwse-resize}#dsel-ov .dsel-h-s{left:50%;top:100%;cursor:ns-resize}
#dsel-ov .dsel-h-sw{left:0;top:100%;cursor:nesw-resize}#dsel-ov .dsel-h-w{left:0;top:50%;cursor:ew-resize}
#dsel-ov .dsel-wonly .dsel-h-nw,#dsel-ov .dsel-wonly .dsel-h-n,#dsel-ov .dsel-wonly .dsel-h-ne,#dsel-ov .dsel-wonly .dsel-h-se,#dsel-ov .dsel-wonly .dsel-h-s,#dsel-ov .dsel-wonly .dsel-h-sw{display:none}
#dsel-ov .dsel-wonly .dsel-h-e,#dsel-ov .dsel-wonly .dsel-h-w{height:18px;margin-top:-9px;border-radius:5px;width:8px;margin-left:-4px}
#dsel-ov .dsel-t-chart .dsel-h,#dsel-ov .dsel-t-logo .dsel-h{display:none}
#dsel-ov .dsel-rot{position:absolute;left:50%;top:100%;margin:16px 0 0 -12px;width:24px;height:24px;border-radius:50%;background:#fff;color:#1E293B;border:1.5px solid #3B82F6;box-sizing:border-box;font:700 14px/21px Arial,sans-serif;text-align:center;pointer-events:auto;cursor:grab;box-shadow:0 1px 4px rgba(0,0,0,.35);-webkit-user-select:none;user-select:none}
#dsel-ov .dsel-norot .dsel-rot{display:none}
#dsel-ov .dsel-tiny .dsel-h{border-width:1px;border-radius:1.5px}
#dsel-ov .dsel-tiny .dsel-h-n,#dsel-ov .dsel-tiny .dsel-h-e,#dsel-ov .dsel-tiny .dsel-h-s,#dsel-ov .dsel-tiny .dsel-h-w{display:none}
#dsel-ov .dsel-tiny .dsel-h-nw{margin:calc(var(--dsel-hs) * -1) 0 0 calc(var(--dsel-hs) * -1)}#dsel-ov .dsel-tiny .dsel-h-ne{margin:calc(var(--dsel-hs) * -1) 0 0 0}#dsel-ov .dsel-tiny .dsel-h-se{margin:0}#dsel-ov .dsel-tiny .dsel-h-sw{margin:0 0 0 calc(var(--dsel-hs) * -1)}
#dsel-ov .dsel-tiny .dsel-rot{width:20px;height:20px;margin:10px 0 0 -10px;font-size:12px;line-height:17px}
#dsel-ov .dsel-sd{display:none;position:absolute;left:100%;top:22%;width:var(--dsel-hs,12px);height:var(--dsel-hs,12px);margin:calc(var(--dsel-hs,12px) / -2) 0 0 calc(var(--dsel-hs,12px) / -2);background:#fff;border:1.5px solid #3B82F6;transform:rotate(45deg);box-sizing:border-box;pointer-events:auto;cursor:ns-resize;box-shadow:0 1px 3px rgba(0,0,0,.35)}
#dsel-ov .dsel-hassd .dsel-sd{display:block}
#dsel-ov .dsel-rc{display:none;position:absolute;width:var(--dsel-hs,12px);height:var(--dsel-hs,12px);margin:calc(var(--dsel-hs,12px) / -2) 0 0 calc(var(--dsel-hs,12px) / -2);border-radius:50%;background:#fff;border:1.5px solid #3B82F6;box-sizing:border-box;pointer-events:auto;cursor:move;box-shadow:0 1px 3px rgba(0,0,0,.35)}
#dsel-ov .dsel-rc::after{content:'';position:absolute;left:50%;top:50%;width:36%;height:36%;transform:translate(-50%,-50%);border-radius:50%;background:#3B82F6}
#dsel-ov .dsel-hasrc .dsel-rc{display:block}
html.dsel-dragging #dsel-ov .dsel-rc{display:none}
#dsel-ov .dsel-sdt{display:none;position:absolute;left:100%;top:22%;margin:-11px 0 0 14px;padding:2px 8px;border-radius:5px;background:#E5E7EB;color:#111827;font:600 11.5px/18px Arial,sans-serif;white-space:nowrap;pointer-events:none;box-shadow:0 1px 4px rgba(0,0,0,.3)}
#dsel-ov .dsel-sdt.dsel-on{display:block}
#dsel-ov .dsel-mini{position:absolute;left:0;bottom:100%;margin-bottom:9px;display:flex;gap:2px;padding:3px;border-radius:9px;background:rgba(15,23,42,.94);box-shadow:0 2px 10px rgba(0,0,0,.4);pointer-events:auto;white-space:nowrap}
#dsel-ov .dsel-mini.dsel-mini-in{bottom:auto;top:100%;margin:9px 0 0 44px}
#dsel-ov .dsel-mini button{all:unset;box-sizing:border-box;width:28px;height:26px;border-radius:6px;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer}
#dsel-ov .dsel-mini button:hover{background:rgba(255,255,255,.16)}
#dsel-ov .dsel-mini button[data-h="mv"]{cursor:move}
#dsel-ov .dsel-fr:not(.dsel-edited) .dsel-mini button[data-m="rst"]{display:none}
#dsel-ov .dsel-mem i{position:absolute;box-sizing:border-box;outline:1px solid #6EA8FF;pointer-events:none}
#dsel-ov.dsel-busy .dsel-mem{display:none}
#dsel-ov .dsel-mq{position:absolute;display:none;box-sizing:border-box;border:1px solid #3B82F6;background:rgba(59,130,246,.14);pointer-events:none}
#dsel-ov .dsel-multi{outline-style:dashed}
#dsel-ov .dsel-g{position:absolute;display:none;background:#F43F5E;pointer-events:none}
#dsel-ov .dsel-gv{left:50%;top:0;width:1px;height:100%}#dsel-ov .dsel-gh{top:50%;left:0;height:1px;width:100%}
html.dsel-dragging,html.dsel-dragging *{-webkit-user-select:none!important;user-select:none!important}
#dsel-bar,:is(#dsel-bar,#dsel-dock) *,#dsel-dock,#dsel-dock *,:is(#dsel-panel,.dsel-pwin),:is(#dsel-panel,.dsel-pwin) *{box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;text-transform:none;letter-spacing:normal}
#dsel-bar,#dsel-dock{position:fixed;z-index:70;display:none;flex-wrap:wrap;align-items:center;justify-content:center;gap:6px 8px;padding:8px 14px;background:#fff;color:#0F172A;border-radius:22px;box-shadow:0 6px 24px rgba(0,0,0,.35)}
:is(#dsel-bar,#dsel-dock) .dsel-left,:is(#dsel-bar,#dsel-dock) .dsel-right{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
:is(#dsel-bar,#dsel-dock) .dsel-left{min-height:32px}
:is(#dsel-bar,#dsel-dock) .dsel-sep{width:1px;align-self:stretch;min-height:28px;background:#CBD5E1;margin:0 4px}
:is(#dsel-bar,#dsel-dock) .dsel-hint{font-size:12.5px;line-height:1.35;color:#475569;max-width:330px}
:is(#dsel-bar,#dsel-dock) .dsel-kind{font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#64748B;margin-right:2px}
:is(#dsel-bar,#dsel-dock) .dsel-lbl{font-size:11.5px;color:#64748B;margin-left:2px}
:is(#dsel-bar,#dsel-dock) .dsel-b,:is(#dsel-panel,.dsel-pwin) .dsel-pbt{all:unset;box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;gap:6px;height:32px;padding:0 11px;border:1px solid #94A3B8;border-radius:8px;background:#fff;color:#0F172A;font-size:13px;font-weight:500;line-height:1;cursor:pointer;white-space:nowrap}
:is(#dsel-bar,#dsel-dock) .dsel-b:hover,:is(#dsel-panel,.dsel-pwin) .dsel-pbt:hover{background:#F1F5F9;border-color:#64748B}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-on,:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-on{background:#DBEAFE;border-color:#3B82F6;color:#1D4ED8}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-ic{width:32px;padding:0;border-color:transparent;font-size:15px}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-ic:hover{border-color:#CBD5E1}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-wide{max-width:150px;overflow:hidden;justify-content:flex-start}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-grp{background:#EDE9FE;border-color:#8B5CF6;color:#5B21B6;font-weight:600}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-reset{border-color:#F87171;color:#B91C1C}
:is(#dsel-bar,#dsel-dock) .dsel-b.dsel-reset:hover{background:#FEF2F2}
:is(#dsel-bar,#dsel-dock) .dsel-A{display:inline-block;font-weight:700;font-size:15px;line-height:17px;border-bottom:4px solid #000;padding:0 2px}
:is(#dsel-bar,#dsel-dock) .dsel-sw,:is(#dsel-panel,.dsel-pwin) .dsel-sw{display:inline-block;width:16px;height:16px;border-radius:4px;border:1px solid #94A3B8;flex:none}
:is(#dsel-bar,#dsel-dock) .dsel-sw-none{background:linear-gradient(135deg,#fff 44%,#EF4444 45%,#EF4444 55%,#fff 56%)!important}
:is(#dsel-bar,#dsel-dock) .dsel-num{display:inline-flex;align-items:center;height:32px;border:1px solid #94A3B8;border-radius:16px;overflow:hidden;background:#fff}
:is(#dsel-bar,#dsel-dock) .dsel-num button{all:unset;box-sizing:border-box;width:26px;height:30px;text-align:center;font-size:15px;line-height:30px;color:#0F172A;cursor:pointer}
:is(#dsel-bar,#dsel-dock) .dsel-num button:hover{background:#F1F5F9}
:is(#dsel-bar,#dsel-dock) .dsel-num input{all:unset;box-sizing:border-box;width:40px;height:30px;text-align:center;font-size:13px;color:#0F172A;-moz-appearance:textfield;appearance:textfield}
:is(#dsel-bar,#dsel-dock) .dsel-num input::-webkit-outer-spin-button,:is(#dsel-bar,#dsel-dock) .dsel-num input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
:is(#dsel-panel,.dsel-pwin){position:fixed;z-index:71;width:248px;overflow:auto;background:#fff;color:#0F172A;border-radius:12px;box-shadow:0 10px 34px rgba(0,0,0,.42);font-size:12.5px;line-height:1.4}
:is(#dsel-panel,.dsel-pwin) .dsel-ph{display:flex;align-items:center;justify-content:space-between;padding:11px 12px 6px 14px;position:sticky;top:0;background:#fff;z-index:1}
:is(#dsel-panel,.dsel-pwin) .dsel-ph b{font-size:13px;font-weight:700}
:is(#dsel-panel,.dsel-pwin) .dsel-ph button{all:unset;width:24px;height:24px;text-align:center;font-size:19px;line-height:22px;color:#475569;cursor:pointer;border-radius:6px}
:is(#dsel-panel,.dsel-pwin) .dsel-ph button:hover{background:#F1F5F9}
:is(#dsel-panel,.dsel-pwin) .dsel-pb{padding:4px 14px 14px}
:is(#dsel-panel,.dsel-pwin) .dsel-tabs{display:flex;border-bottom:1px solid #E2E8F0;margin:0 14px 10px}
:is(#dsel-panel,.dsel-pwin) .dsel-tabs button{all:unset;flex:1;text-align:center;padding:7px 0;font-size:12.5px;color:#64748B;cursor:pointer;border-bottom:2px solid transparent}
:is(#dsel-panel,.dsel-pwin) .dsel-tabs button.dsel-on{color:#0F172A;font-weight:600;border-bottom-color:#7C3AED}
:is(#dsel-panel,.dsel-pwin) .dsel-sub{font-size:11.5px;font-weight:700;color:#334155;margin:13px 0 7px}
:is(#dsel-panel,.dsel-pwin) .dsel-note{font-size:11.5px;color:#64748B;margin:9px 0 8px}
:is(#dsel-panel,.dsel-pwin) .dsel-grid2{display:grid;grid-template-columns:1fr 1fr;gap:7px}
:is(#dsel-panel,.dsel-pwin) .dsel-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px 7px}
:is(#dsel-panel,.dsel-pwin) .dsel-grid3 label{display:flex;flex-direction:column;gap:3px;font-size:10.5px;color:#64748B}
:is(#dsel-panel,.dsel-pwin) input[type=number],:is(#dsel-panel,.dsel-pwin) input[type=text]{all:unset;box-sizing:border-box;width:100%;height:30px;padding:0 8px;border:1px solid #CBD5E1;border-radius:7px;background:#fff;color:#0F172A;font-size:12.5px}
:is(#dsel-panel,.dsel-pwin) input:disabled{background:#F1F5F9;color:#94A3B8}
:is(#dsel-panel,.dsel-pwin) input:focus{border-color:#3B82F6}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt{width:100%;height:30px;font-size:12px;border-color:#CBD5E1;justify-content:center}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-danger{border-color:#F87171;color:#B91C1C}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-danger:hover{background:#FEF2F2}
:is(#dsel-panel,.dsel-pwin) .dsel-row{display:flex;align-items:center;gap:7px;margin:6px 0;font-size:11.5px;color:#475569}
:is(#dsel-panel,.dsel-pwin) .dsel-row .dsel-pbt{width:auto;padding:0 10px;flex:none}
:is(#dsel-panel,.dsel-pwin) input[type=color]{all:unset;width:34px;height:30px;flex:none;border:1px solid #CBD5E1;border-radius:7px;cursor:pointer;background:#fff}
:is(#dsel-panel,.dsel-pwin) input[type=color]::-webkit-color-swatch-wrapper{padding:3px}
:is(#dsel-panel,.dsel-pwin) input[type=color]::-webkit-color-swatch{border:none;border-radius:4px}
:is(#dsel-panel,.dsel-pwin) .dsel-chk{display:flex;align-items:center;gap:6px;cursor:pointer}
:is(#dsel-panel,.dsel-pwin) .dsel-chk input{width:15px;height:15px;margin:0}
:is(#dsel-panel,.dsel-pwin) .dsel-sws{display:flex;flex-wrap:wrap;gap:6px}
:is(#dsel-panel,.dsel-pwin) .dsel-swb{all:unset;box-sizing:border-box;width:24px;height:24px;border-radius:50%;border:1px solid #94A3B8;cursor:pointer}
:is(#dsel-panel,.dsel-pwin) .dsel-swb.dsel-on{box-shadow:0 0 0 2px #fff,0 0 0 4px #7C3AED}
:is(#dsel-panel,.dsel-pwin) .dsel-sl{display:grid;grid-template-columns:1fr auto;gap:2px 8px;align-items:center;margin:8px 0;font-size:11.5px;color:#475569}
:is(#dsel-panel,.dsel-pwin) .dsel-sl input{grid-column:1/3;grid-row:2;width:100%;margin:0;accent-color:#7C3AED}
:is(#dsel-panel,.dsel-pwin) .dsel-sl em{font-style:normal;font-weight:600;color:#0F172A}
:is(#dsel-panel,.dsel-pwin) .dsel-list{display:flex;flex-direction:column;gap:2px}
:is(#dsel-panel,.dsel-pwin) .dsel-list button{all:unset;box-sizing:border-box;padding:7px 10px;border-radius:7px;font-size:14px;cursor:pointer;color:#0F172A}
:is(#dsel-panel,.dsel-pwin) .dsel-list button:hover{background:#F1F5F9}
:is(#dsel-panel,.dsel-pwin) .dsel-list button.dsel-on{background:#EDE9FE;color:#5B21B6}
:is(#dsel-panel,.dsel-pwin) .dsel-icons{display:grid;grid-template-columns:repeat(6,1fr);gap:4px}
:is(#dsel-panel,.dsel-pwin) .dsel-icons button{all:unset;box-sizing:border-box;aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:7px;cursor:pointer;border:1px solid transparent}
:is(#dsel-panel,.dsel-pwin) .dsel-icons button:hover{background:#F1F5F9;border-color:#CBD5E1}
:is(#dsel-panel,.dsel-pwin) .dsel-icons img{width:20px;height:20px;display:block}
:is(#dsel-panel,.dsel-pwin) .dsel-add{all:unset;box-sizing:border-box;display:flex;align-items:center;gap:8px;height:34px;padding:0 9px;border:1px solid #CBD5E1;border-radius:8px;font-size:12px;cursor:pointer;color:#0F172A}
:is(#dsel-panel,.dsel-pwin) .dsel-add:hover{background:#F1F5F9;border-color:#64748B}
:is(#dsel-panel,.dsel-pwin) .dsel-add i{font-style:normal;width:18px;text-align:center;font-size:15px;color:#475569}
:is(#dsel-panel,.dsel-pwin) .dsel-layers{display:flex;flex-direction:column;gap:2px}
:is(#dsel-panel,.dsel-pwin) .dsel-lr{display:flex;align-items:center;gap:8px;padding:5px 6px 5px 8px;border-radius:7px;cursor:pointer;border:1px solid transparent;font-size:12px;color:#0F172A}
:is(#dsel-panel,.dsel-pwin) .dsel-lr:hover{background:#F1F5F9}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-on{background:#EDE9FE;border-color:#C4B5FD}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-off{color:#94A3B8;cursor:default}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-dt{box-shadow:0 -2px 0 #7C3AED}:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-db{box-shadow:0 2px 0 #7C3AED}
:is(#dsel-panel,.dsel-pwin) .dsel-lr span{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
:is(#dsel-panel,.dsel-pwin) .dsel-lr button{all:unset;flex:none;padding:1px 5px;border-radius:5px;font-size:12px;color:#64748B;cursor:pointer}
:is(#dsel-panel,.dsel-pwin) .dsel-lr button:hover{background:#E2E8F0;color:#0F172A}
:is(#dsel-panel,.dsel-pwin) .dsel-gb{flex:none;font-style:normal;font-size:9.5px;font-weight:700;color:#5B21B6;background:#EDE9FE;border-radius:4px;padding:1px 4px}
:is(#dsel-panel,.dsel-pwin) .dsel-ld{flex:none;width:14px;height:14px;border-radius:3px;border:1px solid #94A3B8}
:is(#dsel-panel,.dsel-pwin) .dsel-lg{flex:none;width:14px;text-align:center;font-style:normal;font-weight:700;font-size:12px}
@media (max-width:900px){#dsel-bar,#dsel-dock{border-radius:14px;padding:6px 8px;gap:4px}:is(#dsel-bar,#dsel-dock) .dsel-hint{display:none}:is(#dsel-bar,#dsel-dock) .dsel-sep{display:none}:is(#dsel-panel,.dsel-pwin){width:220px}}
:is(#dsel-panel,.dsel-pwin) .dsel-lr .dsel-lkb{color:#CBD5E1}
:is(#dsel-panel,.dsel-pwin) .dsel-lr:hover .dsel-lkb{color:#64748B}
:is(#dsel-panel,.dsel-pwin) .dsel-lr .dsel-lkb.dsel-on{color:#7C3AED}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-lk span{color:#64748B}
:is(#dsel-panel,.dsel-pwin) .dsel-lt{flex:none;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-style:normal}
:is(#dsel-panel,.dsel-pwin) .dsel-lt img{width:15px;height:15px;display:block}
:is(#dsel-panel,.dsel-pwin) .dsel-lg{width:18px}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-in{margin-left:20px}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-gr{font-weight:600;padding-left:2px;gap:4px}
:is(#dsel-panel,.dsel-pwin) .dsel-lr .dsel-chev{padding:1px 2px;color:#475569}
:is(#dsel-panel,.dsel-pwin) .dsel-lr.dsel-shut .dsel-chev svg{transform:rotate(-90deg)}
:is(#dsel-panel,.dsel-pwin) .dsel-lr .dsel-pen{color:#CBD5E1}
:is(#dsel-panel,.dsel-pwin) .dsel-lr:hover .dsel-pen{color:#64748B}
:is(#dsel-panel,.dsel-pwin) .dsel-lr .dsel-pen svg{width:13px;height:13px}
:is(#dsel-panel,.dsel-pwin) .dsel-gin{flex:1;min-width:0;height:22px;box-sizing:border-box;border:1px solid #7C3AED;border-radius:5px;padding:0 6px;font:600 12px/1 inherit;font-family:inherit;color:#0F172A;background:#fff;outline:none}
#dsel-ov .dsel-t-png .dsel-h-n,#dsel-ov .dsel-t-png .dsel-h-e,#dsel-ov .dsel-t-png .dsel-h-s,#dsel-ov .dsel-t-png .dsel-h-w{display:none}
#dsel-ov .dsel-imgbtn{all:unset;box-sizing:border-box;position:absolute;z-index:6;transform:translate(calc(-100% - 10px),10px);display:flex;align-items:center;gap:6px;height:30px;padding:0 11px;border-radius:8px;background:rgba(15,23,42,.9);color:#fff;font:600 12px/1 Inter,Arial,sans-serif;cursor:pointer;pointer-events:auto;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.25)}
#dsel-ov .dsel-imgbtn:hover{background:#2563EB}
#dsel-ov .dsel-imgbtn svg{width:14px;height:14px}
#dsel-ov .dsel-cropbtn{transform:translate(calc(-100% - 10px),46px)}
#dsel-ov .dsel-pfg{position:absolute;display:none;background-size:100% 100%;background-repeat:no-repeat;opacity:.35;pointer-events:none;outline:1px dashed rgba(59,130,246,.9);box-shadow:0 0 0 1px rgba(255,255,255,.6)}
html.dsel-pfc-drag,html.dsel-pfc-drag *{cursor:grabbing!important;user-select:none!important}
html.dsel-dragging #dsel-ov .dsel-imgbtn{display:none!important}
#dsel-warn{position:fixed;inset:0;z-index:2147483450;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Inter,Arial,sans-serif}
#dsel-warn .dsel-wbox{background:#fff;color:#0F172A;border-radius:14px;max-width:440px;width:100%;padding:22px 22px 18px;box-shadow:0 20px 60px rgba(0,0,0,.35);text-align:left}
#dsel-warn .dsel-wic{width:44px;height:44px;border-radius:50%;background:#FEF3C7;color:#B45309;display:flex;align-items:center;justify-content:center;margin-bottom:12px}
#dsel-warn .dsel-wic svg{width:24px;height:24px}
#dsel-warn h3{margin:0 0 8px;font:700 17px/1.3 Inter,Arial,sans-serif;color:#0F172A}
#dsel-warn p{margin:0 0 8px;font:400 13px/1.5 Inter,Arial,sans-serif;color:#334155}
#dsel-warn p.dsel-wsm{color:#64748B;font-size:12px}
#dsel-warn .dsel-wbt{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;margin-top:14px}
#dsel-warn .dsel-wbt button{all:unset;box-sizing:border-box;height:36px;padding:0 14px;border-radius:8px;border:1px solid #CBD5E1;font:600 13px/34px Inter,Arial,sans-serif;color:#0F172A;cursor:pointer;background:#fff}
#dsel-warn .dsel-wbt button:hover{background:#F1F5F9}
#dsel-warn .dsel-wbt button:focus-visible{outline:2px solid #2563EB;outline-offset:2px}
#dsel-warn .dsel-wbt .dsel-wd{border-color:#DC2626;color:#B91C1C}
#dsel-warn .dsel-wbt .dsel-wd:hover{background:#FEF2F2}
#dsel-warn .dsel-wbt .dsel-wall{background:#DC2626;color:#fff}
#dsel-warn .dsel-wbt .dsel-wall:hover{background:#B91C1C}
#dsel-ren{all:unset;box-sizing:border-box;flex:none;width:26px;height:26px;margin-left:4px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;color:#94A3B8;cursor:pointer}
#dsel-ren:hover{background:rgba(148,163,184,.2);color:#fff}
#dsel-ren svg{width:14px;height:14px}
#dsel-reni{box-sizing:border-box;width:min(360px,60vw);height:28px;padding:0 8px;border:1px solid #60A5FA;border-radius:6px;background:#0F172A;color:#fff;font:inherit;outline:none}
:is(#dsel-panel,.dsel-pwin) .dsel-seg{display:flex;border:1px solid #CBD5E1;border-radius:8px;overflow:hidden}
:is(#dsel-panel,.dsel-pwin) .dsel-seg button{all:unset;box-sizing:border-box;flex:1;height:30px;text-align:center;font-size:12px;font-weight:600;color:#475569;cursor:pointer;background:#fff}
:is(#dsel-panel,.dsel-pwin) .dsel-seg button+button{border-left:1px solid #CBD5E1}
:is(#dsel-panel,.dsel-pwin) .dsel-seg button.dsel-on{background:#EDE9FE;color:#5B21B6}
:is(#dsel-panel,.dsel-pwin) .dsel-seg button[disabled]{cursor:default;opacity:.55}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-al{justify-content:flex-start;gap:7px;padding:0 8px;font-size:11.5px;white-space:nowrap}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-al svg{flex:none;color:#475569}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-al span{overflow:hidden;text-overflow:ellipsis}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt[disabled]{opacity:.45;cursor:default}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-al{min-width:0;font-size:12px}
:is(#dsel-panel,.dsel-pwin) .dsel-pbt.dsel-al.dsel-w2{grid-column:1/-1}
#dsel-ov .dsel-multi .dsel-mini button[data-m="app"]{display:none}
#dsel-menu{position:fixed;z-index:2147483400;min-width:230px;max-width:320px;padding:5px;border-radius:10px;background:#fff;border:1px solid #CBD5E1;box-shadow:0 12px 34px rgba(15,23,42,.28);font-family:Inter,Arial,sans-serif;display:flex;flex-direction:column}
#dsel-menu button{all:unset;box-sizing:border-box;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:6px 10px;border-radius:6px;font-size:12.5px;color:#0F172A;cursor:pointer;white-space:nowrap}
#dsel-menu button:hover{background:#EEF2FF}
#dsel-menu button[disabled]{opacity:.4;cursor:default;background:none}
#dsel-menu button em{font-style:normal;font-size:11px;color:#94A3B8}
#dsel-menu i{display:block;height:1px;background:#E2E8F0;margin:4px 6px}
#dsel-keys{position:fixed;z-index:9990;display:flex;gap:6px;font-family:Inter,Arial,sans-serif}
#dsel-keys button{all:unset;box-sizing:border-box;display:inline-flex;align-items:center;gap:6px;height:30px;padding:0 10px;border-radius:8px;background:rgba(15,23,42,.82);border:1px solid rgba(148,163,184,.35);color:#E2E8F0;font-size:12px;font-weight:600;cursor:pointer}
#dsel-keys button:hover{background:#2563EB;color:#fff;border-color:#2563EB}
#dsel-keys button svg{width:15px;height:15px}
#dsel-help{position:fixed;inset:0;z-index:2147483450;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:16px;font-family:Inter,Arial,sans-serif}
#dsel-help .dsel-hbox{background:#fff;color:#0F172A;border-radius:14px;width:100%;max-width:760px;max-height:calc(100vh - 32px);display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.35)}
#dsel-help .dsel-hh{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #E2E8F0}
#dsel-help .dsel-hh b{font-size:16px}
#dsel-help .dsel-hh button{all:unset;width:28px;height:28px;border-radius:7px;text-align:center;font-size:20px;line-height:28px;color:#475569;cursor:pointer}
#dsel-help .dsel-hh button:hover{background:#F1F5F9}
#dsel-help .dsel-hb{padding:6px 18px 10px;overflow:auto;columns:2 300px;column-gap:28px}
#dsel-help .dsel-hg{break-inside:avoid;margin:10px 0 6px}
#dsel-help h4{margin:0 0 6px;font:700 11px/1.2 Inter,Arial,sans-serif;letter-spacing:.06em;text-transform:uppercase;color:#64748B}
#dsel-help .dsel-hr{display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:4px 0;border-bottom:1px dashed #E2E8F0;font-size:12.5px}
#dsel-help .dsel-hr span{color:#334155;text-align:right}
#dsel-help kbd{flex:none;font:600 11.5px/1.4 ui-monospace,Menlo,Consolas,monospace;background:#F1F5F9;border:1px solid #CBD5E1;border-bottom-width:2px;border-radius:5px;padding:1px 6px;color:#0F172A;white-space:nowrap}
#dsel-help .dsel-hn{margin:0;padding:8px 18px 14px;font-size:11.5px;color:#64748B}
:is(#dsel-panel,.dsel-pwin) .dsel-rl{flex:none;color:#475569}
:is(#dsel-panel,.dsel-pwin) .dsel-row select{flex:1;min-width:0;height:28px;border:1px solid #CBD5E1;border-radius:7px;background:#fff;color:#0F172A;font-size:12px;padding:0 6px}
:is(#dsel-panel,.dsel-pwin) .dsel-row input[type=number]{width:54px;height:28px;border:1px solid #CBD5E1;border-radius:7px;padding:0 6px;font-size:12px;color:#0F172A;background:#fff;box-sizing:border-box}
:is(#dsel-panel,.dsel-pwin) .dsel-gpre{display:grid;grid-template-columns:repeat(6,1fr);gap:5px}
:is(#dsel-panel,.dsel-pwin) .dsel-gpre button{all:unset;box-sizing:border-box;height:24px;border-radius:6px;border:1px solid #94A3B8;cursor:pointer;background-color:#fff}
:is(#dsel-panel,.dsel-pwin) .dsel-gpre button:hover{outline:2px solid #7C3AED;outline-offset:1px}
:is(#dsel-panel,.dsel-pwin) .dsel-dirs{display:flex;gap:3px;flex-wrap:wrap}
:is(#dsel-panel,.dsel-pwin) .dsel-dirs button{all:unset;box-sizing:border-box;width:24px;height:24px;border:1px solid #CBD5E1;border-radius:6px;text-align:center;font-size:13px;line-height:22px;color:#334155;cursor:pointer;background:#fff}
:is(#dsel-panel,.dsel-pwin) .dsel-dirs button.dsel-on{background:#EDE9FE;border-color:#7C3AED;color:#5B21B6}
:is(#dsel-panel,.dsel-pwin) .dsel-grow{align-items:flex-start;margin-bottom:14px}
:is(#dsel-panel,.dsel-pwin) .dsel-gwrap{flex:1;min-width:0;padding:0 7px;background:repeating-conic-gradient(#E2E8F0 0 25%,#fff 0 50%) 0 0/10px 10px;border-radius:6px}
:is(#dsel-panel,.dsel-pwin) .dsel-gbar{position:relative;height:22px;border-radius:5px;border:1px solid #94A3B8;cursor:copy;box-sizing:border-box}
:is(#dsel-panel,.dsel-pwin) .dsel-gs{position:absolute;top:15px;width:13px;height:15px;margin-left:-7px;box-sizing:border-box;border:2px solid #fff;border-radius:3px;box-shadow:0 0 0 1px #64748B;cursor:ew-resize;touch-action:none}
:is(#dsel-panel,.dsel-pwin) .dsel-gs.dsel-on{box-shadow:0 0 0 2px #7C3AED;z-index:2}
:is(#dsel-panel,.dsel-pwin) .dsel-gbt{all:unset;box-sizing:border-box;flex:none;width:24px;height:22px;border:1px solid #CBD5E1;border-radius:6px;text-align:center;font-size:15px;line-height:20px;color:#0F172A;cursor:pointer;background:#fff}
:is(#dsel-panel,.dsel-pwin) .dsel-gbt:hover{background:#F1F5F9}
:is(#dsel-panel,.dsel-pwin) .dsel-gbt[disabled]{opacity:.4;cursor:default}

/* object settings on top, fixed buttons at the bottom; Edit screen hides the slides and layout panels */
#dsel-dock{flex-wrap:nowrap;overflow-x:auto;justify-content:flex-start;scrollbar-width:none;padding:7px 12px}
#dsel-dock::-webkit-scrollbar{display:none}
#dsel-dock .dsel-right{flex-wrap:nowrap;white-space:nowrap}
#dsel-dock .dsel-edscr{font-weight:700;border:1.5px solid #0F172A;min-width:96px;justify-content:center}
#dsel-dock .dsel-edscr.dsel-on{background:#0F172A;color:#fff;border-color:#0F172A}
#dsel-dock .dsel-dsep{flex:none;width:1px;align-self:stretch;min-height:28px;background:#CBD5E1;margin:0 6px}

/* the settings panel can be dragged by its title bar (double-click the title bar: back to its place) */
:is(#dsel-panel,.dsel-pwin) .dsel-ph{cursor:move;touch-action:none;-webkit-user-select:none;user-select:none}
:is(#dsel-panel,.dsel-pwin) .dsel-ph button{cursor:pointer}
/* font size: a box with a slider under it */
:is(#dsel-bar,#dsel-dock) .dsel-fsz{position:relative;display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 5px 0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#fff}
:is(#dsel-bar,#dsel-dock) .dsel-fszl{font-size:12.5px;color:#334155;cursor:pointer;white-space:nowrap}
:is(#dsel-bar,#dsel-dock) .dsel-fsz input[type=number]{width:46px;height:24px;border:0;outline:0;border-radius:5px;background:#F1F5F9;color:#0F172A;font-weight:700;font-size:13px;text-align:center;padding:0;-moz-appearance:textfield;appearance:textfield}
:is(#dsel-bar,#dsel-dock) .dsel-fsz input[type=number]::-webkit-inner-spin-button,:is(#dsel-bar,#dsel-dock) .dsel-fsz input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
:is(#dsel-bar,#dsel-dock) .dsel-fszp{display:none;position:absolute;left:-1px;top:100%;padding-top:5px;z-index:6}
:is(#dsel-bar,#dsel-dock) .dsel-fsz:hover .dsel-fszp,:is(#dsel-bar,#dsel-dock) .dsel-fsz:focus-within .dsel-fszp,:is(#dsel-bar,#dsel-dock) .dsel-fsz.dsel-open .dsel-fszp{display:block}
:is(#dsel-bar,#dsel-dock) .dsel-fszin{display:flex;align-items:center;gap:8px;width:240px;padding:10px 12px;background:#fff;border-radius:10px;box-shadow:0 8px 22px rgba(0,0,0,.28)}
:is(#dsel-bar,#dsel-dock) .dsel-fszin input[type=range]{flex:1;min-width:0;margin:0;accent-color:#0F172A;cursor:pointer;height:20px}
:is(#dsel-bar,#dsel-dock) .dsel-fa1{font-size:11px;color:#334155}
:is(#dsel-bar,#dsel-dock) .dsel-fa2{font-size:20px;line-height:1;color:#0F172A}

/* ===== the document editor frame, in the AI Presentation editor's colours ===== */
.dd-ed{background:#3C4043;grid-template-rows:48px minmax(0,1fr) 73px;grid-template-columns:200px minmax(0,1fr) 270px}
.dd-ed .dd-top{grid-column:1/3;grid-row:1;background:#202124;border-bottom:0;gap:8px}
.dd-ed .dd-top .bk{color:#C9D2E3}
.dd-ed .dd-tsep{width:1px;height:22px;background:#3C4043;margin:0 4px}
.dd-ed .dd-top input{width:auto;min-width:80px;max-width:40vw;field-sizing:content;font-size:14.5px}
.dd-ed .dd-ren{background:none;border:0;color:#94A3B8;width:26px;height:26px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center}
.dd-ed .dd-ren:hover{background:rgba(148,163,184,.2);color:#fff}
.dd-ed .dd-top .eb.pri{background:#2563EB;border-color:#2563EB;letter-spacing:.06em;font-weight:700}
.dd-ed .dd-left{grid-column:1;grid-row:2/4;background:#2D2E30;border-right:1px solid #1A1A1A}
.dd-ed .dd-right{grid-column:3;grid-row:1/4;background:#141414;border-left:0}
.dd-ed .dd-right .rtabs{background:#141414}
.dd-ed .dd-canvas{grid-column:2;grid-row:2;background:#3C4043;padding:70px 20px 80px}
.dd-ed .dd-elbar{display:none!important}
.dd-ed .dd-bot{grid-column:2;grid-row:3;background:#202124;border-top:0;position:relative;justify-content:flex-start}
.dd-ed .dd-pill{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);display:flex;align-items:center;gap:4px;height:48px;padding:0 18px;border-radius:100px;background:#202124;border:1px solid #2A2A2A;color:#C9D2E3;font-size:12.5px}
.dd-ed .dd-pill button{background:none;border:0;color:#C9D2E3;width:30px;height:30px;border-radius:50%;font-size:18px;line-height:1}
.dd-ed .dd-pill button:hover{background:#3C4043;color:#fff}
.dd-ed .dd-pill .pg{min-width:64px;text-align:center}
.dd-ed .dd-pill #dde-z{min-width:44px;text-align:center}
.dd-ed .dd-pdiv{width:1px;height:20px;background:#3C4043;margin:0 10px}
.dd-ed.dd-focus{grid-template-columns:0 minmax(0,1fr) 0}
.dd-ed.dd-focus .dd-left,.dd-ed.dd-focus .dd-right{display:none}
.dd-ed.dd-focus .dd-top{grid-column:1/4}
.dd-ed.dd-focus .dd-bot{display:none}.dd-ed.dd-focus .dd-canvas{grid-row:2/4;padding-left:210px}
#dsel-dock.dsel-dockv{flex-direction:column;flex-wrap:nowrap;align-items:stretch;justify-content:flex-start;width:192px;padding:10px;gap:8px;border-radius:12px;overflow-y:auto;overflow-x:hidden}
#dsel-dock.dsel-dockv .dsel-right{display:flex;flex-direction:column;flex-wrap:nowrap;align-items:stretch;gap:8px;width:100%}
#dsel-dock.dsel-dockv .dsel-right>button,#dsel-dock.dsel-dockv .dsel-urow button{justify-content:center;text-align:center;width:100%;margin:0;flex:none}
#dsel-dock.dsel-dockv .dsel-dsep{display:block;width:auto!important;height:1px!important;min-height:0!important;flex:none!important;margin:2px 0!important;padding:0!important;align-self:stretch;background:#E2E8F0!important;border:0!important}
#dsel-dock.dsel-dockv .dsel-urow .dsel-ic{border:1px solid #CBD5E1!important;border-radius:8px;min-height:32px}
#dsel-dock.dsel-dockv .dsel-edscr{min-width:0}
#dsel-dock.dsel-dockv .dsel-urow{display:flex;gap:8px}#dsel-dock.dsel-dockv .dsel-urow button{flex:1}
#dsel-dock .dsel-theme{background:linear-gradient(90deg,#2563EB,#4F46E5)!important;color:#fff!important;border-color:transparent!important;font-weight:600}
#dsel-dock .dsel-thdot{display:inline-block;width:12px;height:12px;border-radius:50%;background:conic-gradient(#EF4444,#F59E0B,#22C55E,#3B82F6,#A855F7,#EF4444);border:2px solid #fff;box-sizing:border-box}
.dd-ed .dsd-edit [contenteditable]:focus{outline:none}
.dd-ed .dsd-edit .dsd-blk.sel{box-shadow:none}
/* the bars and panels live inside the editor window */
.dd-ed #dsel-bar,.dd-ed #dsel-dock{z-index:40}
.dd-ed :is(#dsel-panel,.dsel-pwin){z-index:45}
.dd-ed #dsel-keys{z-index:38}
.dd-ed #dsel-menu{z-index:60}
.dd-ed #dsel-help{z-index:70}
.dd-ed #dsel-ov{position:absolute;left:0;top:0;width:0;height:0;overflow:visible;pointer-events:none;z-index:20}
#dsel-ov .dsel-fr.dsel-lockd{outline-color:#F59E0B}
#dsel-ov .dsel-lkm{position:absolute;right:-9px;top:-9px;width:18px;height:18px;border-radius:50%;background:#F59E0B;color:#fff;display:flex;align-items:center;justify-content:center;pointer-events:none}
#dsel-ov .dsel-lkm svg{width:11px;height:11px}
#dsel-ov .dsel-nohd .dsel-h,#dsel-ov .dsel-nohd .dsel-rot,#dsel-ov .dsel-norot .dsel-rot{display:none}
#dsel-ov .dsel-corner .dsel-h-n,#dsel-ov .dsel-corner .dsel-h-e,#dsel-ov .dsel-corner .dsel-h-s,#dsel-ov .dsel-corner .dsel-h-w{display:none}
#dsel-ov .dsel-ins{position:absolute;height:4px;background:#3B82F6;border-radius:2px;box-shadow:0 0 0 3px rgba(59,130,246,.25);pointer-events:none}
#dsel-ov .dsel-sdt{top:auto;bottom:100%;left:50%;margin:0 0 8px -40px}
html.dsel-dragging #dsel-ov .dsel-mini{display:none}
.dd-ed.dsel-brush .dsd-doc,.dd-ed.dsel-brush .dsd-doc *{cursor:copy!important}
:is(#dsel-panel,.dsel-pwin) .dsel-lgpv{padding:10px;border-radius:8px;background:repeating-conic-gradient(#E2E8F0 0 25%,#fff 0 50%) 0 0/12px 12px;text-align:center;margin-bottom:8px}
:is(#dsel-panel,.dsel-pwin) .dsel-lgpv img{max-width:100%;max-height:80px;display:inline-block}
:is(#dsel-panel,.dsel-pwin) .dsel-row input[type=text]{flex:1}
:is(#dsel-panel,.dsel-pwin) .dsel-icons button svg{width:22px;height:22px;display:block}
#dsel-menu button.dsel-on{background:#EDE9FE;color:#5B21B6}
:is(#dsel-panel,.dsel-pwin) .dsel-lt svg{display:block;max-width:18px;max-height:18px}

/* chart editor */
.dd-rv.dl-chm{z-index:2147483200}
.dd-rv.dl-chm .rv-box{height:auto;max-height:92vh;width:min(1060px,96vw)}
.dd-rv .dl-chbody{flex:1;min-height:0;overflow:auto;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:22px;padding:18px 26px}
@media(max-width:860px){.dd-rv .dl-chbody{grid-template-columns:1fr}}
.dd-rv .dl-kinds{display:flex;flex-wrap:wrap;gap:6px}
.dd-rv .dl-kinds button{background:#1a2030;border:1px solid rgba(255,255,255,.08);color:#cbd5e1;border-radius:8px;padding:7px 12px;font-size:12.5px}
.dd-rv .dl-kinds button.on{background:#2563eb;border-color:#2563eb;color:#fff}
.dd-rv .dl-sh{display:flex;gap:4px;align-items:center}.dd-rv .dl-sh input[type=color]{width:30px;height:34px;flex:none;border:1px solid rgba(255,255,255,.1);border-radius:7px;background:#1a2030;padding:2px}
.dd-rv .rv-in.num{text-align:right}
.dd-rv .dl-chpv{background:#fff;border-radius:12px;padding:10px;align-self:start;position:sticky;top:0}
.dd-rv .dl-chpv svg{display:block;width:100%;height:auto}

/* icons library (same as the presentation editor) */
/* DS Editor Icons Library */
#dsdoc-icpop{display:none;position:fixed;z-index:2147483300;width:560px;height:480px;max-height:480px;flex-direction:column;background:#fff;border-radius:12px;box-shadow:0 10px 34px rgba(0,0,0,.42);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif;color:#0F172A;overflow:hidden;box-sizing:border-box}
#dsdoc-icpop *{box-sizing:border-box}
#dsdoc-icpop.dsel-ic-drag{box-shadow:0 16px 44px rgba(0,0,0,.5)}
#dsdoc-icpop .dsel-ic-h{display:flex;align-items:center;justify-content:space-between;padding:11px 12px 8px 14px;cursor:move;user-select:none;touch-action:none;flex:none}
#dsdoc-icpop .dsel-ic-h b{font-size:13px;font-weight:700;color:#0F172A}
#dsdoc-icpop .dsel-ic-h b::before{content:"\2807";display:inline-block;margin-right:6px;color:#94A3B8;font-weight:400;letter-spacing:-2px}
#dsdoc-icpop button{font-family:inherit;text-transform:none;letter-spacing:0;box-shadow:none;min-height:0;line-height:1.2;margin:0}
#dsdoc-icpop .dsel-ic-x{width:24px;height:24px;padding:0;border:0;background:transparent;color:#475569;font-size:19px;line-height:1;cursor:pointer;border-radius:6px}
#dsdoc-icpop .dsel-ic-x:hover{background:#F1F5F9;color:#0F172A}
#dsdoc-icpop .dsel-ic-top{padding:0 12px 8px;border-bottom:1px solid #E2E8F0;flex:none}
#dsdoc-icpop .dsel-ic-s{display:flex;align-items:center;gap:6px;height:34px;padding:0 6px 0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#F8FAFC;color:#64748B}
#dsdoc-icpop .dsel-ic-s:focus-within{border-color:#2563EB;background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
#dsdoc-icpop .dsel-ic-q{flex:1;min-width:0;height:30px;border:0!important;outline:0!important;background:transparent!important;box-shadow:none!important;padding:0!important;margin:0;font-size:13px;color:#0F172A;font-family:inherit}
#dsdoc-icpop .dsel-ic-q::-webkit-search-cancel-button{display:none}
#dsdoc-icpop .dsel-ic-clr{visibility:hidden;width:22px;height:22px;padding:0;border:0;background:transparent;color:#64748B;font-size:16px;cursor:pointer;border-radius:50%}
#dsdoc-icpop .dsel-ic-clr:hover{background:#E2E8F0;color:#0F172A}
#dsdoc-icpop .dsel-ic-st{display:flex;gap:4px;margin-top:8px;padding:3px;background:#F1F5F9;border-radius:8px}
#dsdoc-icpop .dsel-ic-st button{flex:1;height:24px;padding:0;border:0;border-radius:6px;background:transparent;color:#475569;font-size:12px;font-weight:600;cursor:pointer}
#dsdoc-icpop .dsel-ic-st button.on{background:#fff;color:#1D4ED8;box-shadow:0 1px 3px rgba(15,23,42,.15)}
#dsdoc-icpop .dsel-ic-main{flex:1 1 auto;min-height:0;display:flex}
#dsdoc-icpop .dsel-ic-cats{flex:none;width:156px;overflow-y:auto;overscroll-behavior:contain;padding:8px 6px 8px 8px;border-right:1px solid #E2E8F0;background:#F8FAFC;scrollbar-width:thin}
#dsdoc-icpop .dsel-ic-cats button{display:flex;align-items:center;gap:8px;width:100%;height:30px;padding:0 8px;border:0;border-radius:7px;background:transparent;color:#334155;font-size:12.5px;font-weight:500;text-align:left;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsdoc-icpop .dsel-ic-cats button:hover{background:#EEF2F7;color:#0F172A}
#dsdoc-icpop .dsel-ic-cats button.on{background:#E0E7FF;color:#1D4ED8;font-weight:700}
#dsdoc-icpop .dsel-ic-ci{flex:none;width:16px;height:16px;display:block;color:#64748B}
#dsdoc-icpop .dsel-ic-cats button.on .dsel-ic-ci{color:#1D4ED8}
#dsdoc-icpop .dsel-ic-ci svg{display:block;width:16px;height:16px}
#dsdoc-icpop .dsel-ic-body{flex:1 1 auto;min-width:0;overflow-y:auto;overscroll-behavior:contain;padding:8px 12px 10px}
#dsdoc-icpop .dsel-ic-info{font-size:11.5px;color:#64748B;padding:4px 0 6px}
#dsdoc-icpop .dsel-ic-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(46px,1fr));gap:4px}
#dsdoc-icpop .dsel-ic-g button{aspect-ratio:1;width:100%;height:auto;padding:0;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:8px;background:#fff;cursor:pointer;transition:background .12s}
#dsdoc-icpop .dsel-ic-g button:hover{background:#F1F5F9;border-color:#CBD5E1}
#dsdoc-icpop .dsel-ic-g button.dsel-ic-ok{background:#DCFCE7;border-color:#22C55E}
#dsdoc-icpop .dsel-ic-g img{width:26px;height:26px;display:block;pointer-events:none}
#dsdoc-icpop .dsel-ic-ph{display:block;width:20px;height:20px;border-radius:5px;background:linear-gradient(90deg,#F1F5F9,#E2E8F0,#F1F5F9);background-size:200% 100%;animation:dsdoc-ic-sh 1s linear infinite}
@keyframes dsdoc-ic-sh{to{background-position:-200% 0}}
#dsdoc-icpop .dsel-ic-more{display:none;width:100%;height:30px;margin-top:8px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#1D4ED8;font-size:12px;font-weight:600;cursor:pointer}
#dsdoc-icpop .dsel-ic-more:hover{background:#EFF6FF;border-color:#2563EB}
#dsdoc-icpop .dsel-ic-f{flex:none;padding:7px 12px;border-top:1px solid #E2E8F0;font-size:11px;line-height:1.4;color:#64748B;background:#F8FAFC}
@media (max-width:600px){
#dsdoc-icpop{height:auto}
#dsdoc-icpop .dsel-ic-main{flex-direction:column}
#dsdoc-icpop .dsel-ic-cats{width:auto;display:flex;gap:4px;overflow-x:auto;overflow-y:hidden;border-right:0;border-bottom:1px solid #E2E8F0;padding:6px 8px;flex:none}
#dsdoc-icpop .dsel-ic-cats button{width:auto;flex:none}
}
/* company logo dialog (same as the presentation editor) */
.dsdoc-lgm{position:fixed;inset:0;z-index:2147483350;background:rgba(2,6,23,.62);display:flex;align-items:center;justify-content:center;padding:16px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif}
.dsdoc-lgm *{box-sizing:border-box}
.dsdoc-lgb{width:min(830px,100%);max-height:min(720px,94vh);display:flex;flex-direction:column;background:#131B2C;color:#E2E8F0;border:1px solid #23304A;border-radius:14px;box-shadow:0 24px 60px rgba(0,0,0,.55);overflow:hidden}
.dsdoc-lgh{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #23304A}
.dsdoc-lgh h3{margin:0;font-size:17px;font-weight:700;color:#fff;letter-spacing:0;text-transform:none}
.dsdoc-lgx{width:30px;height:30px;border:0;border-radius:8px;background:transparent;color:#CBD5E1;font-size:22px;line-height:1;cursor:pointer;padding:0}
.dsdoc-lgx:hover{background:#1E293B;color:#fff}
.dsdoc-lgc{flex:1;min-height:0;display:grid;grid-template-columns:370px 1fr;gap:20px;padding:18px 20px 0;overflow:hidden}
.dsdoc-lgv{min-width:0}
.dsdoc-lgpv{position:relative;width:100%;overflow:hidden;border-radius:8px;background:#fff;border:1px solid #2B3954}
.dsdoc-lgvt{display:flex;gap:8px;margin:12px 0 10px}
.dsdoc-lgvt button,.dsdoc-lgp button{height:38px;padding:0 13px;border:1px solid #334155;border-radius:8px;background:#0F172A;color:#E2E8F0;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;text-transform:none;letter-spacing:0}
.dsdoc-lgvt button.on,.dsdoc-lgp button.on{background:#172554;border-color:#3B82F6;color:#fff}
.dsdoc-lgvt button:hover,.dsdoc-lgp button:hover{border-color:#64748B}
.dsdoc-lgn{margin:6px 0 0;font-size:12.5px;line-height:1.5;color:#94A3B8}
.dsdoc-lgr{min-width:0;overflow-y:auto;padding:0 8px 18px 0;scrollbar-width:thin}
.dsdoc-lgl{margin:16px 0 8px;font-size:11.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94A3B8}
.dsdoc-lgr>.dsdoc-lgl:first-child{margin-top:2px}
.dsdoc-lgt{margin:14px 0 4px;font-size:15px;font-weight:700;color:#fff}
.dsdoc-lgsep{height:1px;background:#23304A;margin:20px 0 6px}
.dsdoc-lgd{position:relative;display:flex;align-items:center;justify-content:center;gap:12px;min-height:104px;padding:14px;border:2px dashed #60A5FA;border-radius:12px;background:rgba(37,99,235,.12);color:#F1F5F9;font-size:13.5px;text-align:center;line-height:1.6;cursor:pointer}
.dsdoc-lgup{display:block;width:max-content;margin:0 auto 8px;padding:8px 18px;border-radius:999px;background:#2563EB;color:#fff;font-size:14px;font-weight:700}
.dsdoc-lgd.has{min-height:76px}
.dsdoc-lgd:hover{border-color:#3B82F6;background:rgba(59,130,246,.06)}
.dsdoc-lgd input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dsdoc-lgd img{max-height:46px;max-width:60%;object-fit:contain;background:repeating-conic-gradient(#334155 0 25%,#1E293B 0 50%) 0 0/12px 12px;border-radius:4px;padding:3px}
.dsdoc-lgd.has span{font-size:12px;color:#94A3B8}
.dsdoc-lglink{margin-top:6px;border:0;background:none;color:#60A5FA;font-size:12px;cursor:pointer;padding:0;font-family:inherit}
.dsdoc-lgck{display:flex;align-items:center;gap:8px;margin:6px 0;font-size:13.5px;color:#E2E8F0;cursor:pointer}
.dsdoc-lgck input{width:16px;height:16px;accent-color:#3B82F6;margin:0}
.dsdoc-lgs{margin:4px 0 12px}
.dsdoc-lgsh{display:flex;justify-content:space-between;font-size:13.5px;color:#E2E8F0;margin-bottom:6px}
.dsdoc-lgsh em{font-style:normal;color:#CBD5E1;font-size:12.5px}
.dsdoc-lgs input[type=range]{width:100%;margin:0;accent-color:#3B82F6;height:18px}
.dsdoc-lgse{display:flex;justify-content:space-between;font-size:11.5px;color:#94A3B8;margin-top:3px}
.dsdoc-lgp{display:flex;flex-wrap:wrap;gap:8px;margin:6px 0 4px}
.dsdoc-lgf{display:flex;align-items:center;gap:10px;padding:14px 20px;border-top:1px solid #23304A}
.dsdoc-lgf span{flex:1}
.dsdoc-lgbt{height:42px;padding:0 16px;border:1px solid #334155;border-radius:9px;background:#1E293B;color:#E2E8F0;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;text-transform:none;letter-spacing:0}
.dsdoc-lgbt:hover{border-color:#64748B}
.dsdoc-lgbt.pri{background:#2563EB;border-color:#2563EB;color:#fff}
.dsdoc-lgbt.pri:hover{background:#1D4ED8}
@media (max-width:760px){.dsdoc-lgc{grid-template-columns:1fr;overflow-y:auto}.dsdoc-lgr{overflow:visible}}
#dde-doc .dsd-logo{pointer-events:auto;cursor:pointer}
#dde-doc .dsd-panels .dsd-blk img,#dde-doc .dsd-panels .dsd-blk svg{-webkit-user-drag:none;user-select:none}#dde-doc .dsd-panels .dsd-blk{cursor:move}#dde-doc .dsd-panels .dsd-blk [contenteditable]{cursor:text}
#dde-doc .dsd-logo:hover{outline:1.5px dashed #3B82F6;outline-offset:2px}

.dsel-pwin{position:fixed;z-index:44!important;resize:both;overflow:auto;min-width:220px;min-height:120px;box-shadow:0 10px 32px rgba(0,0,0,.45)!important;outline:2px solid #3B82F6;outline-offset:-1px}
.dsel-pwin .dsel-ph{cursor:move}
.dsel-phb{display:inline-flex;align-items:center;gap:2px;margin-left:auto}
.dsel-phb .dsel-pinb{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:6px;color:#64748B}
.dsel-phb .dsel-pinb:hover{background:#F1F5F9;color:#0F172A}.dsel-phb .dsel-pinb svg{width:15px;height:15px}
.dsel-phb .dsel-pinb.dsel-on{background:#DBEAFE;color:#1D4ED8}
.dsel-thg{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:7px}.dsel-thg button{min-width:0}
.dsel-thg button{all:unset;cursor:pointer;display:flex;flex-direction:column;gap:5px;padding:7px;border:1px solid #E2E8F0;border-radius:8px;font-size:11.5px;color:#0F172A;box-sizing:border-box}
.dsel-thg button:hover{border-color:#93C5FD}.dsel-thg button.dsel-on{border-color:#2563EB;box-shadow:0 0 0 1px #2563EB}
.dsel-ths{display:flex;height:18px;border-radius:4px;overflow:hidden}.dsel-ths i{flex:1}
.dsel-thn{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsdoc-icpop .dsel-ic-hb{display:inline-flex;align-items:center;gap:2px}
@keyframes dselPflash{0%,100%{transform:scale(1);outline-color:#3B82F6}40%{transform:scale(1.025);outline-color:#F59E0B}}
.dsel-pflash{animation:dselPflash .45s ease 2!important}
#dsdoc-icpop .dsel-ic-lk{width:24px;height:24px;padding:0;border:0;background:transparent;color:#64748B;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
#dsdoc-icpop .dsel-ic-lk:hover{background:#F1F5F9;color:#0F172A}
#dsdoc-icpop.dsel-ic-locked{outline:2px solid #3B82F6}#dsdoc-icpop.dsel-ic-locked .dsel-ic-lk{background:#DBEAFE;color:#1D4ED8}
DSCSS;
}
add_action( 'init', function () {
	$a = isset( $_GET['ds_doc_asset'] ) ? sanitize_key( wp_unslash( $_GET['ds_doc_asset'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'app' !== $a && 'css' !== $a ) {
		return;
	}
	do_action( 'litespeed_control_set_ttl', 86400 );
	header( 'Content-Type: ' . ( 'css' === $a ? 'text/css' : 'application/javascript' ) . '; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'X-Content-Type-Options: nosniff' );
	echo 'css' === $a ? ds_doc_app_css() : ds_doc_app_js(); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}, 1 );

/** The AI Document page's address. */
function ds_doc_page_url() {
	$id = (int) get_option( 'ds_doc_page_id' );
	return $id && get_post_status( $id ) === 'publish' ? get_permalink( $id ) : home_url( '/ai-document/' );
}

/** Credit price settings (the same ones AI Presentation uses). */
function ds_doc_pricing() {
	$o = get_option( 'ds_ppt_credit_settings' );
	$o = is_array( $o ) ? $o : array();
	return array(
		'base'  => isset( $o['base_credits'] ) ? (float) $o['base_credits'] : 5,
		'baseN' => isset( $o['base_slides'] ) ? (int) $o['base_slides'] : 5,
		'extra' => isset( $o['extra_slide_rate'] ) ? (float) $o['extra_slide_rate'] : 1,
	);
}

function ds_doc_page_shortcode() {
	$uid  = get_current_user_id();
	$user = $uid ? wp_get_current_user() : null;
	$plan = 'free';
	$cred = 0;
	if ( $uid ) {
		do_action( 'litespeed_control_set_nocache', 'ds ai document (logged in)' );
		$cred = function_exists( 'ds_get_credits' ) ? (float) ds_get_credits( $uid ) : (float) get_user_meta( $uid, 'ds_ai_credits', true );
		$r    = rest_do_request( new WP_REST_Request( 'GET', '/ds/v1/credits' ) );
		if ( ! $r->is_error() ) {
			$d    = $r->get_data();
			$plan = isset( $d['plan'] ) ? sanitize_key( $d['plan'] ) : 'free';
			if ( isset( $d['credits'] ) ) {
				$cred = (float) $d['credits'];
			}
		}
	}
	$s   = class_exists( 'DS_AI_Doc' ) ? DS_AI_Doc::settings() : array( 'max_pages' => 20, 'assist_cost' => 1 );
	$cfg = array(
		'rest'       => esc_url_raw( rest_url() ),
		'nonce'      => $uid ? wp_create_nonce( 'wp_rest' ) : '',
		'loggedIn'   => (bool) $uid,
		'user'       => array( 'name' => $user ? $user->display_name : '' ),
		'credits'    => $cred,
		'plan'       => $plan,
		'pricing'    => ds_doc_pricing(),
		'maxPages'   => (int) $s['max_pages'],
		'assistCost' => (int) $s['assist_cost'],
		'brand'      => $uid && class_exists( 'DS_AI_Doc' ) && method_exists( 'DS_AI_Doc', 'brand_get' ) ? DS_AI_Doc::brand_get( $uid ) : null,
		'useAiFree'  => $uid && function_exists( 'ds_uai_is_free' ) ? (bool) ds_uai_is_free( $uid ) : false,
		'loginUrl'   => wp_login_url( ds_doc_page_url() ),
		'buyUrl'     => home_url( '/pricing/' ),
		'links'      => array(
			array( 'label' => 'AI Images', 'url' => home_url( '/ai-studio/' ) ),
			array( 'label' => 'AI Presentation', 'url' => home_url( '/ai-studio/ai-presentation/' ) ),
			array( 'label' => 'AI Document', 'url' => ds_doc_page_url(), 'on' => true ),
			array( 'label' => 'AI Video', 'url' => home_url( '/ai-studio/ai-video/' ) ),
			array( 'label' => 'AI Audio', 'url' => home_url( '/ai-studio/ai-audio/' ) ),
		),
	);
	$v   = rawurlencode( DS_DOC_APP_VER . '-' . ( defined( 'DS_DOC_ENGINE_VER' ) ? DS_DOC_ENGINE_VER : '0' ) );
	$out = '<link rel="stylesheet" href="' . esc_url( add_query_arg( array( 'ds_doc_asset' => 'css', 'v' => $v ), home_url( '/' ) ) ) . '">';
	$out .= ds_doc_studio_bar( $cred );
	$out .= '<div id="dsdoc-app"><div style="padding:40px;text-align:center;color:#93A0B8;font-family:sans-serif">Loading AI Document…</div></div></div></div>';
	$out .= '<script>window.DSDOC_CFG=' . wp_json_encode( $cfg ) . ';</script>';
	$out .= '<script src="' . esc_url( add_query_arg( array( 'ds_doc_asset' => 'engine', 'v' => $v ), home_url( '/' ) ) ) . '"></script>';
	$out .= '<script src="' . esc_url( add_query_arg( array( 'ds_doc_asset' => 'app', 'v' => $v ), home_url( '/' ) ) ) . '"></script>';
	return $out;
}
add_shortcode( 'ds_ai_document', 'ds_doc_page_shortcode' );

/** The document icon used for the "AI Document" tab. */
function ds_doc_tab_icon() {
	return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><line x1="8" y1="13" x2="16" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="8" y1="17" x2="13" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
}

/**
 * The AI Studio top bar, exactly as the AI Presentation page draws it ("DS AI Studio" snippet):
 * the same tab buttons (the AI Video / AI Audio tab snippets add their tabs to it) and the credits row.
 */
function ds_doc_studio_bar( $credits ) {
	ob_start();
	?>
<style>
body.dsdoc-page,body.dsdoc-page #page,body.dsdoc-page .site-content,body.dsdoc-page #content,body.dsdoc-page .ast-container,body.dsdoc-page #primary,body.dsdoc-page .entry-content,body.dsdoc-page article{background:#0a0a0a!important}
body.dsdoc-page #primary,body.dsdoc-page .site-content>.ast-container{margin-top:0!important;margin-bottom:0!important;padding-top:0!important}
body.dsdoc-page .entry-content{margin:0!important}
.dsdoc-wrap{background:#0a0a0a;width:100%;padding:0 48px;box-sizing:border-box}@media(max-width:900px){.dsdoc-wrap{padding:0 16px}}
body.dsdoc-page .is-layout-constrained>.dsdoc-wrap,body.dsdoc-page .is-layout-constrained>*:has(>.dsdoc-wrap){max-width:none!important}
#ds-stu.dsdoc-stu{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0a0a;padding:0 4px;max-width:1600px;margin:0 auto}
#ds-stu.dsdoc-stu *{box-sizing:border-box}
.dsdoc-stu .dst-bar{display:flex;gap:14px;margin:24px auto 28px;justify-content:flex-start;flex-wrap:wrap}
.dsdoc-stu .dst-btn{padding:10px 22px;border-radius:3px;border:none;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .15s;letter-spacing:.3px;text-transform:uppercase;text-decoration:none}
.dsdoc-stu .dst-btn.on{background:#2563eb;color:#fff}
.dsdoc-stu .dst-btn:not(.on){background:#161616;color:#555;border:1px solid #1e1e1e}
.dsdoc-stu .dst-btn:not(.on):hover{color:#9ca3af;background:#1a1a1a}
</style>
<div class="dsdoc-wrap alignfull"><div id="ds-stu" class="dsdoc-stu">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0 18px;flex-wrap:wrap;gap:12px;">
    <div class="dst-bar" style="margin:0;">
      <a href="<?php echo esc_url( home_url( '/ai-studio/' ) ); ?>" class="dst-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="2"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/></svg>
        AI Images</a>
      <a href="<?php echo esc_url( home_url( '/ai-studio/ai-presentation/' ) ); ?>" class="dst-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        AI Presentation</a>
      <a href="<?php echo esc_url( ds_doc_page_url() ); ?>" class="dst-btn on" data-dsdoc="1"><?php echo ds_doc_tab_icon(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        AI Document</a>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
      <div style="background:#111;border:1px solid #1e1e1e;border-radius:100px;padding:7px 14px;display:flex;align-items:center;gap:6px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="#e8442a"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <span style="font-weight:800;color:#e8442a;font-size:14px;" id="ds-cr-display" data-credits><?php echo esc_html( number_format( (float) $credits ) ); ?></span>
        <span style="font-size:11px;color:#374151;">cr</span>
      </div>
      <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" style="background:#2563eb;color:#fff;padding:8px 16px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">+ Buy</a>
      <a href="<?php echo esc_url( home_url( '/my-generated-images' ) ); ?>" style="padding:8px 14px;border-radius:100px;border:1px solid #1e1e1e;background:#111;color:#6b7280;font-size:13px;font-weight:600;text-decoration:none;">My Creations</a>
    </div>
  </div>
	<?php
	return ob_get_clean();
}

/* Dark page background, like the AI Presentation page. */
add_filter( 'body_class', function ( $c ) {
	if ( is_singular() ) {
		$p = get_post();
		if ( $p && ( (int) get_option( 'ds_doc_page_id' ) === (int) $p->ID || has_shortcode( (string) $p->post_content, 'ds_ai_document' ) ) ) {
			$c[] = 'dsdoc-page';
		}
	}
	return $c;
} );

/* Create the page once, with the AI Presentation page's layout settings (full width, no sidebar). */
add_action( 'admin_init', function () {
	$id = (int) get_option( 'ds_doc_page_id' );
	if ( $id && get_post( $id ) ) {
		return;
	}
	$existing = get_page_by_path( 'ai-document' );
	if ( $existing ) {
		update_option( 'ds_doc_page_id', $existing->ID );
		return;
	}
	if ( ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'AI Document', 'post_name' => 'ai-document', 'post_content' => '[ds_ai_document]' ) );
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	update_option( 'ds_doc_page_id', $id );
	$ref = get_page_by_path( 'ai-presentation' );
	if ( $ref ) {
		foreach ( get_post_meta( $ref->ID ) as $k => $vals ) {
			if ( preg_match( '/^(site-|ast-|_astra|theme-transparent|stick-header|footer-sml|header-above|header-main|header-below|_surerank)/', $k ) ) {
				update_post_meta( $id, $k, maybe_unserialize( $vals[0] ) );
			}
		}
	}
	update_post_meta( $id, 'site-content-layout', 'page-builder' );
	update_post_meta( $id, 'site-sidebar-layout', 'no-sidebar' );
	update_post_meta( $id, 'site-post-title', 'disabled' );
} );

/* "AI Document" tab in the AI Studio tab bar on the other AI pages. */
add_action( 'wp_footer', function () {
	$url = ds_doc_page_url();
	?>
<script>(function(){var u=<?php echo wp_json_encode( $url ); ?>,ic=<?php echo wp_json_encode( ds_doc_tab_icon() ); ?>;function add(){document.querySelectorAll('.dst-bar,.dstv-bar').forEach(function(bar){if(bar.querySelector('[data-dsdoc]'))return;var tabs=bar.querySelectorAll('a,button,span');var ref=null;tabs.forEach(function(a){if(!ref&&/presentation/i.test(a.textContent)&&/dstv?-btn/.test(a.className))ref=a;});if(!ref)return;var a=document.createElement('a');a.className=ref.className.replace(/\bon\b/g,'').replace(/\s+/g,' ').trim();a.setAttribute('data-dsdoc','1');a.href=u;a.innerHTML=ic+' AI Document';ref.parentNode.insertBefore(a,ref.nextSibling);});}if(document.readyState!=='loading')add();else document.addEventListener('DOMContentLoaded',add);setTimeout(add,800);setTimeout(add,2000);})();</script>
	<?php
}, 99 );
endif;
