try{

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
    try { (new Function(pick.use))(); } catch(e){}
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

}catch(e){if(window.console)console.error(e);}
try{

(function () {
'use strict';

var _prevDspGen = window.dspGen; // Snippet 24, then the original Preserve/AI logic beneath it
if (!_prevDspGen) return;

var BASE = window.PURL || '';
var PREVIEW_URL = BASE.replace('generate-ppt', 'preserve-preview');
var CONFIRM_URL = BASE.replace('generate-ppt', 'preserve-confirm');

function esc(s) {
  return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function splitItem(item) {
  var i = item.indexOf(':');
  if (i === -1) return { h: item, d: '' };
  return { h: item.slice(0, i).trim(), d: item.slice(i + 1).trim() };
}

var TYPE_LABEL = {
  title: 'Title', bullet: 'Bullets', two_column: '2 Columns', three_column: '3 Columns',
  feature_grid: '2x2 Grid', stats: 'Stats', timeline: 'Timeline', quote: 'Quote', image_text: 'Image + Text'
};

function showPreviewLoader() {
  var el = document.createElement('div');
  el.id = 'dsp-rv-loading';
  el.style.cssText = 'position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  el.innerHTML =
    '<div style="background:#111827;border:1px solid #2a2a2a;border-radius:12px;padding:32px 40px;text-align:center;color:#e5e7eb;">' +
      '<div style="width:36px;height:36px;border:3px solid #2a2a2a;border-top-color:#4A9EFF;border-radius:50%;margin:0 auto 16px;animation:dsprvspin 0.8s linear infinite;"></div>' +
      '<div style="font-weight:700;font-size:15px;">Organizing your content&hellip;</div>' +
      '<div style="color:#9ca3af;font-size:12.5px;margin-top:4px;">This is just a preview — nothing is charged yet.</div>' +
    '</div>' +
    '<style>@keyframes dsprvspin{to{transform:rotate(360deg);}}</style>';
  document.body.appendChild(el);
  return el;
}

function removeEl(el) { if (el && el.parentNode) el.parentNode.removeChild(el); }

function recalcType(s) {
  if (s._typeFixed) return;
  var n = (s.content || []).length;
  if (['two_column','three_column','feature_grid','bullet'].indexOf(s.type) !== -1 && n > 0) {
    if (n === 2) s.type = 'two_column';
    else if (n === 3) s.type = 'three_column';
    else if (n === 4) s.type = 'feature_grid';
    else if (n >= 5) s.type = 'bullet';
  }
}

function showReviewPanel(ppt, cost, onConfirm, onBack, costFn) {
  var overlay = document.createElement('div');
  overlay.id = 'dsp-rv-panel';
  overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;padding:24px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';

  overlay.innerHTML =
    '<div style="background:#161b22;border:1px solid #2a2a2a;border-radius:14px;max-width:720px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">' +
      '<div style="padding:22px 26px;border-bottom:1px solid #21262d;">' +
        '<div style="color:#f9fafb;font-weight:800;font-size:19px;">Review Your Slides</div>' +
        '<div style="color:#9ca3af;font-size:13px;margin-top:3px;">AI organized your text below, using only your own words. Edit, delete a slide, or add/remove points, then confirm.</div>' +
      '</div>' +
      '<div id="dsp-rv-list" style="padding:20px 26px;overflow-y:auto;flex:1;"></div>' +
      '<div style="padding:16px 26px;border-top:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;gap:12px;">' +
        '<button id="dsp-rv-back" style="background:transparent;border:1px solid #30363d;color:#c9d1d9;border-radius:8px;padding:11px 18px;font-weight:700;font-size:13px;cursor:pointer;">&larr; Back to Edit</button>' +
        '<button id="dsp-rv-confirm" style="background:#2563eb;border:none;color:#fff;border-radius:8px;padding:11px 22px;font-weight:800;font-size:13px;cursor:pointer;">&#10003; Generate Presentation &middot; ' + cost + ' credits</button>' +
      '</div>' +
    '</div>';
  document.body.appendChild(overlay);

  var listEl = document.getElementById('dsp-rv-list');

  function renderBody() {
    listEl.innerHTML = ppt.slides.map(function (s, i) {
      var contentHtml = (s.content || []).map(function (c, ci) {
        return '<li style="margin-bottom:8px;display:flex;align-items:flex-start;gap:8px;">' +
          '<span contenteditable="true" data-idx="' + i + '" data-cidx="' + ci + '" style="flex:1;color:#c9d1d9;font-size:13px;line-height:1.5;outline:none;border-bottom:1px dashed transparent;" ' +
          'onfocus="this.style.borderBottomColor=\'#30363d\'" onblur="this.style.borderBottomColor=\'transparent\'">' + esc(c) + '</span>' +
          '<button class="dsp-rv-del-point" data-idx="' + i + '" data-cidx="' + ci + '" title="Remove point" style="flex-shrink:0;background:transparent;border:none;color:#f85149;font-size:15px;cursor:pointer;line-height:1;padding:2px 4px;">&times;</button>' +
        '</li>';
      }).join('');

      return (
        '<div style="background:#0d1117;border:1px solid #21262d;border-radius:10px;padding:18px 20px;margin-bottom:14px;">' +
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">' +
            '<span style="color:#58A6FF;font-weight:700;font-size:12px;letter-spacing:.03em;">SLIDE ' + (i + 1) + '</span>' +
            '<div style="display:flex;align-items:center;gap:8px;">' +
              '<span style="color:#6b7280;font-size:11px;background:#161b22;border:1px solid #21262d;border-radius:20px;padding:2px 10px;">' + esc(TYPE_LABEL[s.type] || s.type) + '</span>' +
              '<button class="dsp-rv-del-slide" data-idx="' + i + '" title="Delete slide" style="background:transparent;border:1px solid #30363d;color:#f85149;border-radius:6px;font-size:12px;cursor:pointer;padding:3px 8px;">&#128465; Delete</button>' +
            '</div>' +
          '</div>' +
          '<div contenteditable="true" data-idx="' + i + '" data-field="title" style="color:#f9fafb;font-weight:800;font-size:17px;margin-bottom:4px;outline:none;">' + esc(s.title) + '</div>' +
          '<div contenteditable="true" data-idx="' + i + '" data-field="subtitle" style="color:#8b95a5;font-size:13px;font-style:italic;margin-bottom:10px;outline:none;" data-placeholder="Add a subtitle…">' + esc(s.subtitle || '') + '</div>' +
          (contentHtml ? '<ul style="margin:10px 0 0;padding-left:6px;list-style:none;">' + contentHtml + '</ul>' : '') +
          '<button class="dsp-rv-add-point" data-idx="' + i + '" style="margin-top:8px;background:transparent;border:1px dashed #30363d;color:#8b95a5;border-radius:6px;font-size:12px;cursor:pointer;padding:5px 10px;">+ Add point</button>' +
        '</div>'
      );
    }).join('');

    var confirmBtn = document.getElementById('dsp-rv-confirm');
    if (confirmBtn) {
      cost = costFn ? costFn(ppt.slides.length) : cost;
      confirmBtn.innerHTML = '&#10003; Generate Presentation &middot; ' + cost + ' credits';
    }
  }

  listEl.addEventListener('click', function (e) {
    var delSlideBtn = e.target.closest('.dsp-rv-del-slide');
    if (delSlideBtn) {
      if (ppt.slides.length <= 1) { alert('You need at least one slide.'); return; }
      ppt.slides.splice(parseInt(delSlideBtn.getAttribute('data-idx'), 10), 1);
      renderBody();
      return;
    }
    var delPointBtn = e.target.closest('.dsp-rv-del-point');
    if (delPointBtn) {
      var s1 = ppt.slides[parseInt(delPointBtn.getAttribute('data-idx'), 10)];
      s1.content.splice(parseInt(delPointBtn.getAttribute('data-cidx'), 10), 1);
      recalcType(s1);
      renderBody();
      return;
    }
    var addPointBtn = e.target.closest('.dsp-rv-add-point');
    if (addPointBtn) {
      var s2 = ppt.slides[parseInt(addPointBtn.getAttribute('data-idx'), 10)];
      s2.content.push('New Point: Click to edit this text.');
      recalcType(s2);
      renderBody();
      return;
    }
  });

  // Text edits update the data model only — no re-render, so the cursor
  // and focus are never disturbed while typing.
  listEl.addEventListener('input', function (e) {
    var t = e.target;
    var idx = t.getAttribute('data-idx');
    if (idx === null) return;
    idx = parseInt(idx, 10);
    var field = t.getAttribute('data-field');
    if (field === 'title') { ppt.slides[idx].title = t.textContent; return; }
    if (field === 'subtitle') { ppt.slides[idx].subtitle = t.textContent; return; }
    var cidx = t.getAttribute('data-cidx');
    if (cidx !== null) { ppt.slides[idx].content[parseInt(cidx, 10)] = t.textContent; }
  });

  renderBody();

  document.getElementById('dsp-rv-back').onclick = function () { removeEl(overlay); onBack(); };
  document.getElementById('dsp-rv-confirm').onclick = function () {
    document.getElementById('dsp-rv-confirm').textContent = 'Generating…';
    document.getElementById('dsp-rv-confirm').disabled = true;
    // Re-apply smart layout types before confirming
    if (typeof window.dspPreserveParse === 'function' && ppt && ppt.slides) {
      var _prompt = (document.getElementById('dsp-prompt')||{}).value||'';
      var _retyped = window.dspPreserveParse(_prompt);
      _retyped.forEach(function(_s,_i){ if(ppt.slides[_i]) ppt.slides[_i].type = _s.type; });
    }
    onConfirm();
  };
}

window.dspGen = function () {
  var _outerThis = this, _outerArgs = arguments;

  if (typeof window.G === 'undefined' || window.G.mode !== 'preserve') {
    return _prevDspGen.apply(_outerThis, _outerArgs);
  }

  var promptEl = document.getElementById('dsp-prompt');
  var prompt = promptEl ? promptEl.value.trim() : '';
  if (!prompt) {
    if (promptEl) promptEl.focus();
    return;
  }

  var slidesInput = document.getElementById('dsp-slides');
  var hint = slidesInput ? (parseInt(slidesInput.value, 10) || 0) : 0;
  var design = (document.getElementById('dsp-design') || {}).value || 'navy-pro';

  var loader = showPreviewLoader();

  fetch(PREVIEW_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
    body: JSON.stringify({ text: prompt, num_slides_hint: hint })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    removeEl(loader);
    if (!d || d.error || !d.ppt_data) {
      alert((d && d.message) ? d.message : 'Preview failed. Falling back to manual formatting.');
      return _prevDspGen.apply(_outerThis, _outerArgs);
    }

    var ppt = d.ppt_data;
    var cost = d.cost || 8;

    showReviewPanel(ppt, cost, function onConfirm() {
      fetch(CONFIRM_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
        body: JSON.stringify({ ppt_data: ppt, slides: ppt.slides, design: (document.getElementById('dsp-design')||{}).value||'navy-pro', title: ppt.title||'Presentation' })
      })
      .then(function (r) { return r.json(); })
      .then(function (d2) {
        var panel = document.getElementById('dsp-rv-panel');
        removeEl(panel);
        if (!d2 || d2.error || (!d2.ppt_data && !d2.url)) {
          alert((d2 && d2.message) ? d2.message : 'Could not generate the presentation. Please try again.');
          return;
        }
        if (d2.credits_balance !== undefined) {
          window.G.credits = d2.credits_balance;
          var ce = document.getElementById('ds-cr-display');
          if (ce) ce.textContent = window.G.credits.toLocaleString();
        }
        var finalPpt = d2.ppt_data;
        window.dspLiveGenShow(prompt, finalPpt.slides.length, design);
        var bannerTxt = document.getElementById('dsp-lg-banner-txt');
        if (bannerTxt) bannerTxt.innerHTML = '<b style="color:#111;">Building Your Slides:</b> Turning your reviewed content into a deck.';

        // Call build-pptx-v2 — returns a URL directly like AI mode does
        var bldUrl = window.DSP_BLD_URL || window.BLDURL || '/wp-json/ds/v1/build-pptx-v2';
        fetch(bldUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
          body: JSON.stringify({
            slides: finalPpt.slides,
            design: design,
            title: finalPpt.title,
            slide_variants: {}
          })
        })
        .then(function(r){ return r.json(); })
        .then(function(bd){
          // Store the built PPTX URL so download button uses it
          if (bd && bd.url) {
            window.G.preserveDownloadUrl = bd.url;
            window.G.preserveDownloadFilename = bd.filename || 'presentation.pptx';
          }
          // Open editor with the correctly typed slides
          function finish() {
            window.dspLiveGenHide();
            window.G.pptId = d2.ppt_id || ('preserve_' + Date.now());
            window.G.pptData = finalPpt;
            window.G.pptTitle = finalPpt.title;
            if (typeof window.buildEditor === 'function') window.buildEditor(finalPpt);
            if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(finalPpt);
          }
          window.dspLiveGenStream(finalPpt, design, finish);
        })
        .catch(function(){
          function finish() {
            window.dspLiveGenHide();
            window.G.pptId = d2.ppt_id || ('preserve_' + Date.now());
            window.G.pptData = finalPpt;
            window.G.pptTitle = finalPpt.title;
            if (typeof window.buildEditor === 'function') window.buildEditor(finalPpt);
            if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(finalPpt);
          }
          window.dspLiveGenStream(finalPpt, design, finish);
        });
      })
      .catch(function () {
        removeEl(document.getElementById('dsp-rv-panel'));
        alert('Network error while generating. Please try again.');
      });
    }, function onBack() {
      // Nothing charged, nothing to undo — user just edits the topic/settings and retries.
    }, function costFn(n) { return Math.max(window.DS_MIN_CREDITS||5, n*(window.DS_CREDITS_PER_SLIDE||1)); });
  })
  .catch(function () {
    removeEl(loader);
    alert('Network error while previewing. Falling back to manual formatting.');
    return _prevDspGen.apply(_outerThis, _outerArgs);
  });
};

})();

}catch(e){if(window.console)console.error(e);}
try{

(function () {
'use strict';

var _prevDspGen = window.dspGen; // Snippet 25, then everything beneath it
if (!_prevDspGen) return;

var BASE = window.PURL || '';
var AI_PREVIEW_URL = BASE.replace('generate-ppt', 'ai-preview');
var AI_CONFIRM_URL = BASE.replace('generate-ppt', 'ai-confirm');

function esc(s) {
  return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function splitItem(item) {
  var i = item.indexOf(':');
  if (i === -1) return { h: item, d: '' };
  return { h: item.slice(0, i).trim(), d: item.slice(i + 1).trim() };
}
var TYPE_LABEL = {
  title: 'Title', bullet: 'Bullets', two_column: '2 Columns', three_column: '3 Columns',
  feature_grid: '2x2 Grid', five_point: '5 Steps', six_point: '6 Steps', seven_point: '7 Steps', eight_point: '8 Steps',
  stats: 'Stats', timeline: 'Timeline', quote: 'Quote', image_text: 'Image + Text'
};

function showPreviewLoader(label) {
  var el = document.createElement('div');
  el.id = 'dsp-ai-rv-loading';
  el.style.cssText = 'position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  el.innerHTML =
    '<div style="background:#111827;border:1px solid #2a2a2a;border-radius:12px;padding:32px 40px;text-align:center;color:#e5e7eb;">' +
      '<div style="width:36px;height:36px;border:3px solid #2a2a2a;border-top-color:#4A9EFF;border-radius:50%;margin:0 auto 16px;animation:dspairvspin 0.8s linear infinite;"></div>' +
      '<div style="font-weight:700;font-size:15px;">' + esc(label) + '</div>' +
      '<div style="color:#9ca3af;font-size:12.5px;margin-top:4px;">This is just a preview — nothing is charged yet.</div>' +
    '</div>' +
    '<style>@keyframes dspairvspin{to{transform:rotate(360deg);}}</style>';
  document.body.appendChild(el);
  return el;
}
function removeEl(el) { if (el && el.parentNode) el.parentNode.removeChild(el); }

function recalcType(s) {
  var n = (s.content || []).length;
  if (['two_column','three_column','feature_grid','bullet'].indexOf(s.type) !== -1 && n > 0) {
    if (n === 2) s.type = 'two_column';
    else if (n === 3) s.type = 'three_column';
    else if (n === 4) s.type = 'feature_grid';
    else if (n >= 5) s.type = 'bullet';
  }
}

function showReviewPanel(ppt, cost, onConfirm, onBack, costFn) {
  var overlay = document.createElement('div');
  overlay.id = 'dsp-ai-rv-panel';
  overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;padding:24px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';

  overlay.innerHTML =
    '<div style="background:#161b22;border:1px solid #2a2a2a;border-radius:14px;max-width:720px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">' +
      '<div style="padding:22px 26px;border-bottom:1px solid #21262d;">' +
        '<div style="color:#f9fafb;font-weight:800;font-size:19px;">Review Your Slides</div>' +
        '<div style="color:#9ca3af;font-size:13px;margin-top:3px;">Here\'s what the AI generated from your topic. Edit, delete a slide, or add/remove points, then confirm.</div>' +
      '</div>' +
      '<div id="dsp-ai-rv-list" style="padding:20px 26px;overflow-y:auto;flex:1;"></div>' +
      '<div style="padding:16px 26px;border-top:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;gap:12px;">' +
        '<button id="dsp-ai-rv-back" style="background:transparent;border:1px solid #30363d;color:#c9d1d9;border-radius:8px;padding:11px 18px;font-weight:700;font-size:13px;cursor:pointer;">&larr; Back to Edit</button>' +
        '<button id="dsp-ai-rv-confirm" style="background:#2563eb;border:none;color:#fff;border-radius:8px;padding:11px 22px;font-weight:800;font-size:13px;cursor:pointer;">&#10003; Generate Presentation &middot; ' + cost + ' credits</button>' +
      '</div>' +
    '</div>';
  document.body.appendChild(overlay);

  var listEl = document.getElementById('dsp-ai-rv-list');

  function renderBody() {
    listEl.innerHTML = ppt.slides.map(function (s, i) {
      var contentHtml = (s.content || []).map(function (c, ci) {
        return '<li style="margin-bottom:8px;display:flex;align-items:flex-start;gap:8px;">' +
          '<span contenteditable="true" data-idx="' + i + '" data-cidx="' + ci + '" style="flex:1;color:#c9d1d9;font-size:13px;line-height:1.5;outline:none;border-bottom:1px dashed transparent;" ' +
          'onfocus="this.style.borderBottomColor=\'#30363d\'" onblur="this.style.borderBottomColor=\'transparent\'">' + esc(c) + '</span>' +
          '<button class="dsp-rv-del-point" data-idx="' + i + '" data-cidx="' + ci + '" title="Remove point" style="flex-shrink:0;background:transparent;border:none;color:#f85149;font-size:15px;cursor:pointer;line-height:1;padding:2px 4px;">&times;</button>' +
        '</li>';
      }).join('');

      return (
        '<div style="background:#0d1117;border:1px solid #21262d;border-radius:10px;padding:18px 20px;margin-bottom:14px;">' +
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">' +
            '<span style="color:#58A6FF;font-weight:700;font-size:12px;letter-spacing:.03em;">SLIDE ' + (i + 1) + '</span>' +
            '<div style="display:flex;align-items:center;gap:8px;">' +
              '<span style="color:#6b7280;font-size:11px;background:#161b22;border:1px solid #21262d;border-radius:20px;padding:2px 10px;">' + esc(TYPE_LABEL[s.type] || s.type) + '</span>' +
              '<button class="dsp-rv-del-slide" data-idx="' + i + '" title="Delete slide" style="background:transparent;border:1px solid #30363d;color:#f85149;border-radius:6px;font-size:12px;cursor:pointer;padding:3px 8px;">&#128465; Delete</button>' +
            '</div>' +
          '</div>' +
          '<div contenteditable="true" data-idx="' + i + '" data-field="title" style="color:#f9fafb;font-weight:800;font-size:17px;margin-bottom:4px;outline:none;">' + esc(s.title) + '</div>' +
          '<div contenteditable="true" data-idx="' + i + '" data-field="subtitle" style="color:#8b95a5;font-size:13px;font-style:italic;margin-bottom:10px;outline:none;" data-placeholder="Add a subtitle…">' + esc(s.subtitle || '') + '</div>' +
          (contentHtml ? '<ul style="margin:10px 0 0;padding-left:6px;list-style:none;">' + contentHtml + '</ul>' : '') +
          '<button class="dsp-rv-add-point" data-idx="' + i + '" style="margin-top:8px;background:transparent;border:1px dashed #30363d;color:#8b95a5;border-radius:6px;font-size:12px;cursor:pointer;padding:5px 10px;">+ Add point</button>' +
        '</div>'
      );
    }).join('');

    var confirmBtn = document.getElementById('dsp-ai-rv-confirm');
    if (confirmBtn) {
      cost = costFn ? costFn(ppt.slides.length) : cost;
      confirmBtn.innerHTML = '&#10003; Generate Presentation &middot; ' + cost + ' credits';
    }
  }

  listEl.addEventListener('click', function (e) {
    var delSlideBtn = e.target.closest('.dsp-rv-del-slide');
    if (delSlideBtn) {
      if (ppt.slides.length <= 1) { alert('You need at least one slide.'); return; }
      ppt.slides.splice(parseInt(delSlideBtn.getAttribute('data-idx'), 10), 1);
      renderBody();
      return;
    }
    var delPointBtn = e.target.closest('.dsp-rv-del-point');
    if (delPointBtn) {
      var s1 = ppt.slides[parseInt(delPointBtn.getAttribute('data-idx'), 10)];
      s1.content.splice(parseInt(delPointBtn.getAttribute('data-cidx'), 10), 1);
      recalcType(s1);
      renderBody();
      return;
    }
    var addPointBtn = e.target.closest('.dsp-rv-add-point');
    if (addPointBtn) {
      var s2 = ppt.slides[parseInt(addPointBtn.getAttribute('data-idx'), 10)];
      s2.content.push('New Point: Click to edit this text.');
      recalcType(s2);
      renderBody();
      return;
    }
  });

  listEl.addEventListener('input', function (e) {
    var t = e.target;
    var idx = t.getAttribute('data-idx');
    if (idx === null) return;
    idx = parseInt(idx, 10);
    var field = t.getAttribute('data-field');
    if (field === 'title') { ppt.slides[idx].title = t.textContent; return; }
    if (field === 'subtitle') { ppt.slides[idx].subtitle = t.textContent; return; }
    var cidx = t.getAttribute('data-cidx');
    if (cidx !== null) { ppt.slides[idx].content[parseInt(cidx, 10)] = t.textContent; }
  });

  renderBody();

  document.getElementById('dsp-ai-rv-back').onclick = function () { removeEl(overlay); onBack(); };
  document.getElementById('dsp-ai-rv-confirm').onclick = function () {
    document.getElementById('dsp-ai-rv-confirm').textContent = 'Generating…';
    document.getElementById('dsp-ai-rv-confirm').disabled = true;
    onConfirm();
  };
}

window.dspGen = function () {
  var _outerThis = this, _outerArgs = arguments;

  if (typeof window.G === 'undefined' || window.G.mode !== 'ai') {
    return _prevDspGen.apply(_outerThis, _outerArgs);
  }

  var promptEl = document.getElementById('dsp-prompt');
  var topic = promptEl ? promptEl.value.trim() : '';
  if (!topic) {
    if (promptEl) promptEl.focus();
    return;
  }

  var slidesInput = document.getElementById('dsp-slides');
  var numSlides = slidesInput ? (parseInt(slidesInput.value, 10) || 10) : 10;
  var design = (document.getElementById('dsp-design') || {}).value || 'navy-pro';
  var langEl = document.getElementById('dsp-lang');
  var language = langEl ? langEl.value : 'English';

  var loader = showPreviewLoader('Generating your script…');

  fetch(AI_PREVIEW_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
    body: JSON.stringify({ topic: topic, num_slides: numSlides, language: language })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    removeEl(loader);
    if (!d || d.error || !d.ppt_data) {
      alert((d && d.message) ? d.message : 'Preview failed. Falling back to standard generation.');
      return _prevDspGen.apply(_outerThis, _outerArgs);
    }

    var ppt = d.ppt_data;
    var cost = d.cost || Math.max(window.DS_MIN_CREDITS || 5, numSlides * (window.DS_CREDITS_PER_SLIDE || 1));

    showReviewPanel(ppt, cost, function onConfirm() {
      fetch(AI_CONFIRM_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
        body: JSON.stringify({ ppt_data: ppt })
      })
      .then(function (r) { return r.json(); })
      .then(function (d2) {
        var panel = document.getElementById('dsp-ai-rv-panel');
        removeEl(panel);
        if (!d2 || d2.error || !d2.ppt_data) {
          alert((d2 && d2.message) ? d2.message : 'Could not generate the presentation. Please try again.');
          return;
        }
        if (d2.credits_balance !== undefined) {
          window.G.credits = d2.credits_balance;
          var ce = document.getElementById('ds-cr-display');
          if (ce) ce.textContent = window.G.credits.toLocaleString();
        }
        var finalPpt = d2.ppt_data;
        window.dspLiveGenShow(topic, finalPpt.slides.length, design);
        var bannerTxt = document.getElementById('dsp-lg-banner-txt');
        if (bannerTxt) bannerTxt.innerHTML = '<b style="color:#111;">Building Your Slides:</b> Turning your reviewed script into a deck.';
        function finish() {
          window.dspLiveGenHide();
          window.G.pptId = d2.ppt_id || ('ai_' + Date.now());
          window.G.pptData = finalPpt;
          window.G.pptTitle = finalPpt.title;
          if (typeof window.buildEditor === 'function') window.buildEditor(finalPpt);
          if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(finalPpt);
        }
        window.dspLiveGenStream(finalPpt, design, finish);
      })
      .catch(function () {
        removeEl(document.getElementById('dsp-ai-rv-panel'));
        alert('Network error while generating. Please try again.');
      });
    }, function onBack() {
      // Nothing charged, nothing to undo — user just edits the topic/settings and retries.
    }, function costFn(n) { return Math.max(window.DS_MIN_CREDITS || 5, n * (window.DS_CREDITS_PER_SLIDE || 1)); });
  })
  .catch(function () {
    removeEl(loader);
    alert('Network error while previewing. Falling back to standard generation.');
    return _prevDspGen.apply(_outerThis, _outerArgs);
  });
};

})();

}catch(e){if(window.console)console.error(e);}
