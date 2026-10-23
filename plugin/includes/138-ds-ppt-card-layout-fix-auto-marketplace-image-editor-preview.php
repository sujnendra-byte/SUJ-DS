<?php
/**
 * Code Snippets export — DS PPT Card Layout Fix + Auto Marketplace Image (editor preview)
 *
 * Snippet ID : 820
 * Active     : yes
 * Priority   : 999
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-09-29 12:52:39
 * Load order : 138 of 144
 */
/**
 * DS PPT — Card layout fix (full-height boxes, no truncation, bigger text)
 * + auto marketplace image by slide title.
 * Editor preview only. Safe to disable.
 */
add_action( 'wp_footer', function () {
	echo <<<'DSFIX'
<style id="ds-card-layout-fix">
/* card grids fill the full content zone */
#dsp-editor-fullscreen div[style*="grid-template-columns:repeat("]{
  align-content: stretch !important;
  grid-auto-rows: minmax(0, 1fr) !important;
  height: 100% !important;
}
#dsp-editor-fullscreen div[style*="border-radius:14px"]{
  height: 100% !important;
  overflow: hidden !important;
  padding: 2.8% 3.0% !important;
  justify-content: center !important;   /* no dead space at the bottom */
}
/* no clamped / truncated text */
#dsp-editor-fullscreen div[style*="-webkit-line-clamp"]{
  -webkit-line-clamp: unset !important;
  display: block !important;
  overflow: visible !important;
}
/* bigger text */
#dsp-editor-fullscreen div[style*="-webkit-line-clamp:2"]{ font-size: 1.05em !important; }
/* timeline descriptions (tl-*) sit in narrow columns: keep them small so they fit their layout */
#dsp-editor-fullscreen div[data-ed-field^="tl-"][style*="-webkit-line-clamp:2"]{ font-size: .5em !important; line-height: 1.3 !important; }
#dsp-editor-fullscreen div[style*="-webkit-line-clamp:8"]{
  font-size: .80em !important;
  line-height: 1.4 !important;
  margin-top: .55em !important;
}
</style>
<script>
(function(){
  var STOP = new Set(('a an and the of for with to in on our your why how what is are we you their '+
    'from that this it its by as at be more less than slide overview intro new key').split(' '));
  function kw(s){
    return (s||'').toLowerCase().replace(/[^a-z0-9 ]+/g,' ').split(/\s+/)
      .filter(function(w){ return w.length>2 && !STOP.has(w); });
  }
  window.dsMktKw = kw;
  window.DS_AUTO_MKT = true;   // set false to stop auto-filling on editor open

  /* slide index -> title, taken from the thumbnail rail so indexes always line up */
  window.dsSlideTitles = function(){
    return [].slice.call(document.querySelectorAll('#dsp-ppt-thumbs .dsp-thumb-item'))
      .map(function(th){
        var el = th.querySelector('div[style*="font-size:1.25em"]');
        return el ? el.textContent.trim() : '';
      });
  };

  function sleep(ms){ return new Promise(function(r){ setTimeout(r, ms); }); }

  /* load the marketplace catalogue once, cache it */
  window.dsMktHarvest = async function(){
    if(window.__dsMktCache) return window.__dsMktCache;
    if(typeof window.dsMktPickOpen !== 'function') return [];
    window.dsMktPickOpen(0);
    var ov = document.getElementById('ds-mkt-pick-overlay');
    if(ov) ov.style.visibility = 'hidden';
    for(var i=0;i<40 && !document.querySelector('.ds-mkt-pick-item');i++) await sleep(200);
    for(var p=0;p<4;p++){
      var btn = document.getElementById('ds-mkt-pg-more');
      if(!btn || btn.offsetParent === null) break;
      var n = document.querySelectorAll('.ds-mkt-pick-item').length;
      window.dsMktPickLoadMore();
      for(var j=0;j<30 && document.querySelectorAll('.ds-mkt-pick-item').length===n;j++) await sleep(200);
    }
    var out = [];
    document.querySelectorAll('.ds-mkt-pick-item').forEach(function(it){
      var l = it.querySelector('.ds-mkt-pick-item-lbl');
      out.push({ lbl: l ? l.textContent.trim() : '', use: it.getAttribute('onclick') || '' });
    });
    if(ov) ov.style.visibility = '';
    if(typeof window.dsMktPickClose === 'function') window.dsMktPickClose();
    window.__dsMktCache = out;
    return out;
  };

  /* Slide text for matching: the title carries most of the weight, the body
     text breaks ties. Title-only matching left 8 of 17 slides unmatched. */
  window.dsSlideText = function(i){
    var th = document.querySelectorAll('#dsp-ppt-thumbs .dsp-thumb-item')[i];
    if(!th) return {title:'', body:''};
    var t = th.querySelector('div[style*="font-size:1.25em"]');
    return { title: t ? t.textContent.trim() : '',
             body: (th.textContent||'').replace(/\s+/g,' ').trim().slice(0,400) };
  };

  /* Best UNUSED image for a slide, so no two slides share a picture. */
  window.dsMktBest = function(title, body, items, used){
    var T = kw(title), B = kw(body), best = null, bs = -1;
    if(!T.length && !B.length) return null;
    items.forEach(function(it){
      if(used && used[it.lbl]) return;
      var L = kw(it.lbl), s = 0;
      T.forEach(function(k){
        if(L.indexOf(k) !== -1) s += 6;
        else if(L.some(function(x){ return x.indexOf(k.slice(0,5)) === 0; })) s += 3;
      });
      B.forEach(function(k){ if(L.indexOf(k) !== -1) s += 1; });
      if(s > bs){ bs = s; best = it; }
    });
    return best;
  };

  window.dsAutoMktImage = async function(slideIdx, used){
    var items = await window.dsMktHarvest();
    var st = window.dsSlideText(slideIdx);
    var pick = window.dsMktBest(st.title, st.body, items, used || {});
    if(!pick) return false;
    if(used) used[pick.lbl] = 1;
    window.dsMktPickOpen(slideIdx);
    var ov = document.getElementById('ds-mkt-pick-overlay');
    if(ov) ov.style.visibility = 'hidden';
    await sleep(500);
    /* An automatic picture is free: it must not use up one of the person's
       marketplace downloads. The picker's own "use" action asks for the
       original file and counts a download, so for this one automatic call
       its request is sent to the not-counted route instead (1920px copy).
       A picture the person picks by hand still goes through the picker
       unchanged and is counted. */
    var dsPrevFetch = window.fetch, dsSwapped = false;
    window.fetch = function(input, init){
      try {
        var u = typeof input === 'string' ? input : ((input && input.url) || '');
        if (!dsSwapped && u.indexOf('/ds/v1/mkt-fetch-image') !== -1 && init && typeof init.body === 'string') {
          dsSwapped = true;
          var ni = {}; for (var k in init) ni[k] = init[k];
          if (window.__dsAutoImg) {
            return dsPrevFetch.call(this, u.replace('/mkt-fetch-image', '/mkt-auto-image'), ni);
          }
          /* sharp-picture snippet is off: ask for the preview, which is not counted either */
          var b = JSON.parse(init.body); delete b.post_id; ni.body = JSON.stringify(b);
          return dsPrevFetch.call(this, u, ni);
        }
      } catch (e) {}
      return dsPrevFetch.apply(this, arguments);
    };
    try { (new Function(pick.use))(); } catch(e){}
    window.fetch = dsPrevFetch;
    if(ov) ov.style.visibility = '';
    await sleep(900);
    return true;
  };

  window.dsAutoMktFillAll = async function(){
    var n = document.querySelectorAll('#dsp-ppt-thumbs .dsp-thumb-item').length;
    var used = {}, placed = 0;
    /* DS-FIX — never touch a slide that already has a picture.
         Reopening a saved deck used to re-search the marketplace and
         re-download an image for EVERY slide, then auto-save ~5 MB back,
         for images that were already saved. Only genuinely empty slides
         are filled now, so reopening a finished deck does nothing. */
      function dsSlideNeedsImage(ix){
        var a = window.S && window.S.data && window.S.data.slides && window.S.data.slides[ix];
        var b = window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[ix];
        var d = (a && a.image_data) || (b && b.image_data) || '';
        return !(typeof d === 'string' && d.length > 100);
      }
      var needy = [];
      for(var i=0;i<n;i++){ if(dsSlideNeedsImage(i)) needy.push(i); }
      if(!needy.length) return;
      for(var qi=0;qi<needy.length;qi++){ if(await window.dsAutoMktImage(needy[qi], used)) placed++; }
    /* Persist once at the end. Without this the images live in memory only,
       so a reload loses them and the next open re-spends a marketplace
       download on every slide. */
    if(placed){
        /* DS-FIX — save in the background. Clicking the Save button pops the
           blocking "Saving..." overlay; calling the function directly does
           the same save without making the user wait behind a modal. */
        await sleep(1200);
        if(typeof window.dspSaveDraft === 'function') window.dspSaveDraft();
      }
  };

  /* ---- real MDI icons (same Iconify source the PPTX export uses) ---- */
  window.DS_ICON_MAP = [
    ['brain','ai,a.i,intelligence,machine learning,algorithm,triage,engine,model'],
    ['database','data,dataset,lake,warehouse,record,repository'],
    ['shield-lock','security,secure,privacy,encryption,protection,hipaa'],
    ['clipboard-check','compliance,regulatory,regulation,legal,policy,clearance,audit,governance,standard'],
    ['rocket-launch','launch,release,beta,rollout,go-live,mvp,pilot'],
    ['city','urban,city,metro,downtown'],
    ['home-group','rural,village,remote,community,local'],
    ['trending-up','growth,scale,scaling,expansion,expand,increase,traction,uplift'],
    ['cash-multiple','funding,capital,series,revenue,pricing,price,cost,money,monetis,monetiz,budget,financial,finance'],
    ['chart-donut','dilution,allocation,breakdown,distribution'],
    ['handshake','partnership,partner,alliance,collaboration,collaborate,vendor,supplier'],
    ['account-group','adoption,provider,user,customer,team,people,staff,audience,member'],
    ['monitor-dashboard','dashboard,screen,interface,portal,console'],
    ['stethoscope','symptom,checker,diagnos,clinical,clinic,consult,doctor,physician'],
    ['file-document-outline','summary,report,document,paperwork'],
    ['sitemap','microservice,architecture,layer,infrastructure,platform,stack,module'],
    ['connection','integration,integrate,api,connect,sync,interoperab,ehr,emr'],
    ['headset','support,helpdesk,concierge'],
    ['clock-outline','clock,speed,hour,wait,latency,turnaround'],
    ['chart-bar','analytics,metric,measure,insight,outcome,benchmark,performance'],
    ['cog','automation,automate,workflow,process,operation,config,setting'],
    ['cloud-outline','cloud,hosting,saas,server,deployment'],
    ['cellphone','mobile,phone,android,handset'],
    ['earth','global,world,international,geography,region'],
    ['check-decagram','quality,trust,verified,certif,accredit,assurance'],
    ['alert-octagon-outline','risk,threat,challenge,issue,problem,barrier,gap,fragment,friction'],
    ['target','opportunity,goal,target,objective,strategy,strategic,positioning'],
    ['school','education,training,learn,onboarding'],
    ['message-text','communication,message,messaging,conversation,notification'],
    ['email-outline','email,inbox,campaign,newsletter'],
    ['calendar-check','calendar,schedule,timeline,milestone,quarter,appointment,booking'],
    ['magnify','search,discovery,research,explore,screening'],
    ['palette','design,creative,brand,visual,theme'],
    ['flash','energy,instant,realtime,real-time'],
    ['lan','network,connectivity,broadband,bandwidth'],
    ['star-outline','feedback,review,rating,satisfaction,testimonial'],
    ['trophy','competition,competitor,competitive,rival'],
    ['lightbulb-on-outline','innovation,idea,concept,vision,mission,differenti'],
    ['key-variant','access,entry,permission,login,credential,unlock'],
    ['heart-pulse','health,care,patient,wellness,medical,telehealth,vital'],
    ['map-marker-path','journey,stage,funnel,lifecycle'],
    ['currency-usd','subscription,licensing,billing,invoice'],
    ['hospital-building','hospital,facility,practice,institution']
  ];

  /* longest matching keyword wins, so "data security" beats "data" */
  window.dsPickIcon = function(text, seq){
    var t = (text||'').toLowerCase(), name = null, len = 0;
    window.DS_ICON_MAP.forEach(function(e){
      e[1].split(',').forEach(function(k){
        k = k.trim();
        if(k && k.length > len && t.indexOf(k) !== -1){ len = k.length; name = e[0]; }
      });
    });
    if(name) return name;
    var fb = ['check-circle-outline','arrow-right-circle-outline','information-outline',
              'square-rounded-outline','circle-slice-8','shape-outline'];
    return fb[(seq||0) % fb.length];
  };

  window.dsInjectIcons = function(root){
    root = root || document.getElementById('dsp-editor-fullscreen');
    if(!root) return 0;
    var n = 0, seq = 0;
    [].forEach.call(root.querySelectorAll('div[style*="width:"][style*="height:"]'), function(slot){
      if(slot.children.length || (slot.textContent||'').trim()) return;
      var st = (slot.getAttribute('style')||'').replace(/\s/g,'');
      /* an empty icon placeholder is exactly width:Xem;height:Xem — dividers and bars are not */
      var m = st.match(/^width:([\d.]+em);height:([\d.]+em);?$/); if(!m) return;
      var holder = slot.parentElement; if(!holder) return;
      var row = holder.parentElement; if(!row) return;
      var titleEl = row.querySelector('div[style*="-webkit-line-clamp"]') ||
        [].filter.call(row.children, function(c){ return c !== holder && (c.textContent||'').trim(); })[0];
      var label = titleEl ? titleEl.textContent.trim() : (row.textContent||'').trim();
      if(!label) return;
      /* white inside a filled circle, otherwise follow the heading colour */
      var color = 'ffffff';
      if(!slot.closest('div[style*="border-radius:50%"]') && titleEl){
        var c = getComputedStyle(titleEl).color.match(/\d+/g);
        if(c) color = c.slice(0,3).map(function(v){ return ('0'+(+v).toString(16)).slice(-2); }).join('');
      }
      var icon = window.dsPickIcon(label, seq++);
      var img = document.createElement('img');
      img.src = 'https://api.iconify.design/mdi/' + icon + '.svg?color=%23' + color;
      img.setAttribute('style','width:'+m[1]+';height:'+m[2]+';display:block');
      img.setAttribute('data-ds-icon', icon);
      slot.replaceWith(img);
      n++;
    });
    return n;
  };

  /* re-inject after every re-render */
  var iconTimer = null;
  function scheduleIcons(){
    clearTimeout(iconTimer);
    iconTimer = setTimeout(function(){ window.dsInjectIcons(); }, 250);
  }
  var host = document.getElementById('dsp-editor-fullscreen');
  if(host && window.MutationObserver){
    new MutationObserver(scheduleIcons).observe(host, { childList:true, subtree:true });
  }
  scheduleIcons();

  /* Fire when the editor's thumbnail rail is populated. Wrapping dspEditorOpen
     at load time was racy: snippet 820 runs in wp_footer and 12b sometimes
     defines dspEditorOpen later, so the wrapper silently never installed.  */
  var _lastDeck = '';
  setInterval(function(){
    if(!window.DS_AUTO_MKT) return;
    var th = document.querySelectorAll('#dsp-ppt-thumbs .dsp-thumb-item');
    if(!th.length){ _lastDeck = ''; return; }
    var key = th.length + '|' + (window.dsSlideText(1).title || '');
    if(key === _lastDeck) return;
    _lastDeck = key;
    setTimeout(window.dsAutoMktFillAll, 1500);
  }, 1500);
})();
</script>
DSFIX;
}, 999 );
