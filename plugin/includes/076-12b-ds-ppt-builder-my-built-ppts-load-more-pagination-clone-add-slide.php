<?php
/**
 * Code Snippets export — 12b ds ppt builder — my-built-ppts Load More pagination [CLONE] [Add-Slide priced at extra-slide rate, not flat 5]
 *
 * Snippet ID : 760
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-074
 * Modified   : 2026-10-06 12:52:56
 * Load order : 74 of 218
 */
/**
 * Snippet Name: DS AI PPT v6.1 - Builder
 * Run: Everywhere
 *
 * Endpoints: /ds/v1/generate-ppt, /ds/v1/fetch-slide-image, /ds/v1/build-pptx
 * v6.1: layout param (16:9, 4:5, 9:16) changes <p:sldSz>.
 * Required: 12a-renderers, 12c-ui, 12d-ui-js
 */

if ( ! defined('ABSPATH') ) exit;


/**
 * Snippet Name: DS AI PPT v6 - UI JavaScript
 * Run: Everywhere
 */

if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_ppt_ui_emit_js') ) {
function ds_ppt_ui_emit_js($nonce, $purl, $bldurl, $imgurl, $cr) {
    $cr_int = (int) $cr;
    $nonce_js = esc_js($nonce);
    $uid_for_plan = get_current_user_id();
    $plan_js = esc_js( function_exists('ds_access_plan') ? ds_access_plan($uid_for_plan) : ( function_exists('ds_get_user_plan') ? ds_get_user_plan($uid_for_plan) : 'free' ) );
    $__ppt_opt = get_option('ds_ppt_credit_settings');
    $__ppt_base_slides = ( is_array($__ppt_opt) && isset($__ppt_opt['base_slides']) && $__ppt_opt['base_slides'] !== '' ) ? (int)$__ppt_opt['base_slides'] : 5;
    $__ppt_base_credits = ( is_array($__ppt_opt) && isset($__ppt_opt['base_credits']) && $__ppt_opt['base_credits'] !== '' ) ? (int)$__ppt_opt['base_credits'] : 5;
    $__ppt_extra_rate = ( is_array($__ppt_opt) && isset($__ppt_opt['extra_slide_rate']) && $__ppt_opt['extra_slide_rate'] !== '' ) ? (int)$__ppt_opt['extra_slide_rate'] : 1;
    $js = <<<'JSBLOCK'

<script>
(function(){
'use strict';

var THEMES={
  'navy-pro':       {bg:'#1B2A4A',title:'#FFFFFF',body:'#C8D8F0',accent:'#4A9EFF'},
  'dark-modern':    {bg:'#0D1117',title:'#FFFFFF',body:'#C9D1D9',accent:'#58A6FF'},
  'clean-corporate':{bg:'#FFFFFF',title:'#1A365D',body:'#2D3748',accent:'#2B6CB0'},
  'bold-red':       {bg:'#1A0000',title:'#FFFFFF',body:'#FFD0D0',accent:'#FF4444'},
  'corporate-dark': {bg:'#111827',title:'#F9FAFB',body:'#D1D5DB',accent:'#6366F1'},
  'clean-white':    {bg:'#F8FAFC',title:'#0F172A',body:'#334155',accent:'#0EA5E9'},
  'gradient-pro':   {bg:'#0F0C29',title:'#FFFFFF',body:'#E0D7FF',accent:'#A78BFA'},
  'bold-impact':    {bg:'#111111',title:'#FFFFFF',body:'#CCCCCC',accent:'#FFD700'}
};
var SLIDE_TYPES=['title','bullet','two_column','three_column','feature_grid','five_point','six_point','seven_point','eight_point','stats','timeline','quote'];
var TYPE_LABELS={title:'Title',bullet:'Bullets',two_column:'Two Cols',three_column:'3 Cols',feature_grid:'4 Grid',five_point:'5 Cards',six_point:'6 Cards',seven_point:'7 Cards',eight_point:'8 Cards',stats:'Stats',timeline:'Timeline',quote:'Quote'};

var G={mode:'ai',credits:__CR__,plan:'__PLAN__',pptData:null,pptId:null,pptTitle:'Presentation',building:false,activeTab:'edit',activeSlideIdx:0};
var NONCE='__NONCE__';
var PURL='__PURL__';
var BLDURL='__BLDURL__';
var IMGURL='__IMGURL__';

// Publish these to window so other snippets (e.g. Snippet 17) can read
// them without redefining their own copies. Everything above this line
// otherwise only exists inside this script's own private scope.
window.G=G;window.NONCE=NONCE;window.PURL=PURL;window.BLDURL=BLDURL;window.IMGURL=IMGURL;
window.dspRenderThumbs=dspRenderThumbs;
window.dspSelectThumb=dspSelectThumb;
window.renderEditList=renderEditList;
window.buildEditor=buildEditor;

function q(id){return document.getElementById(id);}
function escH(s){var d=document.createElement('div');d.textContent=s==null?'':String(s);return d.innerHTML;}

window.dspShow=function(id){
  document.querySelectorAll('#dsp-wrap .dsp-scr').forEach(function(el){el.classList.remove('dsp-active');});
  var t=q(id);if(t){t.classList.add('dsp-active');}
};

var _toastTimer=null;
window.dspToast=function(msg,type,duration){
  var t=q('dsp-toast');if(!t)return;
  var icon=q('dsp-toast-icon');var msgEl=q('dsp-toast-msg');
  if(msgEl)msgEl.textContent=msg||'Done!';
  if(icon){icon.textContent=type==='error'?'!':'\u2713';}
  t.style.borderColor=type==='error'?'#ef4444':'#10b981';
  t.style.background=type==='error'?'#2b1212':'#1d2b1d';
  t.style.color=type==='error'?'#fca5a5':'#6ee7b7';
  t.classList.add('dsp-toast-show');
  if(_toastTimer)clearTimeout(_toastTimer);
  _toastTimer=setTimeout(function(){t.classList.remove('dsp-toast-show');},duration||3500);
};

window.dspFill=function(el){
  var ta=q('dsp-prompt');
  ta.value=el.textContent||el.innerText||'';
  ta.focus();dspChar();
};

window.dspShowUpgradeModal=function(){
  var old=q('dsp-upgrade-modal');if(old&&old.parentNode)old.parentNode.removeChild(old);
  var overlay=document.createElement('div');
  overlay.id='dsp-upgrade-modal';
  overlay.style.cssText='position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.6);'
    +'display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,sans-serif;';
  var card=document.createElement('div');
  card.style.cssText='background:#fdf9f3;border-radius:18px;padding:32px 28px;max-width:420px;width:92%;'
    +'box-shadow:0 20px 60px rgba(0,0,0,.45);';
  card.innerHTML=
    '<h3 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#1a1a1a;">🔒 Pro Feature</h3>'
    +'<p style="margin:0 0 16px;font-size:14px;color:#5a6478;line-height:1.55;">AI Presentation generation is available on Pro and Business plans.</p>'
    +'<div style="background:#fff7e6;border:1px solid #fde7c9;border-radius:10px;padding:12px 14px;margin-bottom:20px;font-size:13px;color:#92660a;line-height:1.5;">Upgrade your plan to unlock AI-generated presentations, premium designs, and more.</div>'
    +'<div style="display:flex;gap:8px;">'
    +'<button id="dsp-upg-cancel" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;">Maybe Later</button>'
    +'<button id="dsp-upg-go" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:800;border:none;cursor:pointer;font-family:inherit;">⭐ Upgrade to Pro</button>'
    +'</div>';
  overlay.appendChild(card);
  document.body.appendChild(overlay);
  function closeIt(){if(overlay.parentNode)overlay.parentNode.removeChild(overlay);}
  document.getElementById('dsp-upg-cancel').onclick=closeIt;
  document.getElementById('dsp-upg-go').onclick=function(){window.location.href='/pricing';};
  overlay.onclick=function(e){if(e.target===overlay)closeIt();};
};

window.dspChar=function(){
  var v=q('dsp-prompt').value;
  q('dsp-char').textContent=v.length+'/3000';
  if(/(?:^|\n)Slide\s+\d+\s*:/i.test(v)&&G.mode!=='preserve') dspMode('preserve');
};

window.dspPillTog=function(btn){
  var menu=btn.querySelector('.dsp-pill-menu');
  var isOpen=menu&&menu.classList.contains('dsp-menu-open');
  document.querySelectorAll('.dsp-pill-menu').forEach(function(m){
    m.classList.remove('dsp-menu-open');
    m.style.top='';
    m.style.left='';
  });
  document.querySelectorAll('.dsp-pill').forEach(function(p){
    p.classList.remove('dsp-pill-open');
  });
  if(!isOpen&&menu){
    var rect=btn.getBoundingClientRect();
    var menuW=220;
    var left=rect.left;
    if(left+menuW>window.innerWidth-8)left=window.innerWidth-menuW-8;
    if(left<8)left=8;
    menu.style.top=(rect.bottom+6)+'px';
    menu.style.left=left+'px';
    menu.classList.add('dsp-menu-open');
    btn.classList.add('dsp-pill-open');
  }
};

document.addEventListener('click',function(e){
  if(!e.target.closest('.dsp-pill')){
    document.querySelectorAll('.dsp-pill-menu').forEach(function(m){m.classList.remove('dsp-menu-open');m.style.top='';m.style.left='';});
    document.querySelectorAll('.dsp-pill').forEach(function(p){p.classList.remove('dsp-pill-open');});
  }
},true);

document.addEventListener('scroll',function(e){
  if(e.target && e.target.closest && e.target.closest('.dsp-pill-menu')) return;
  var _pb = document.querySelector('.dsp-pill.dsp-pill-open');
  var _pm = _pb && _pb.querySelector('.dsp-pill-menu');
  if(!_pb || !_pm) return;
  var _r = _pb.getBoundingClientRect();
  var _l = _r.left;
  if(_l+220 > window.innerWidth-8) _l = window.innerWidth-220-8;
  if(_l < 8) _l = 8;
  _pm.style.top = (_r.bottom+6)+'px';
  _pm.style.left = _l+'px';
},true);

window.dspPillSet=function(kind,val,label){
  var hidden=q('dsp-'+(kind==='lang'?'lang':kind));
  if(hidden) hidden.value=val;
  var lbl=q('dsp-pill-'+kind+'-lbl');
  if(lbl) lbl.textContent=label;
  var btn=q('dsp-pill-'+kind);
  if(btn){
    btn.classList.remove('dsp-pill-open');
    btn.querySelectorAll('.dsp-pill-opt').forEach(function(o){o.classList.remove('dsp-pill-opt-on');});
    var opts=btn.querySelectorAll('.dsp-pill-opt');
    for(var i=0;i<opts.length;i++){
      if(opts[i].textContent.indexOf(label)!==-1){opts[i].classList.add('dsp-pill-opt-on');break;}
    }
  }
  if(kind==='design' && typeof window.dspRenderPreview==='function') window.dspRenderPreview();
  dspUpdCost();
};

window.dspImgsTog=function(){
  var h=q('dsp-imgs'),on=h.value==='1';
  h.value=on?'0':'1';
  var pill=q('dsp-pill-imgs'),lbl=q('dsp-pill-imgs-lbl');
  if(on){pill.classList.add('dsp-pill-off');lbl.textContent='Images off';}
  else  {pill.classList.remove('dsp-pill-off');lbl.textContent='Images on';}
  dspUpdCost();
};

window.dspMode=function(mode){
  G.mode=mode;
  var ai=q('dsp-m-ai'),pr=q('dsp-m-keep');
  if(mode==='ai'){ai.classList.add('dsp-m-on');pr.classList.remove('dsp-m-on');q('dsp-genbtn-lbl').textContent='Generate Presentation';if(q('dsp-s0t'))q('dsp-s0t').textContent='Understanding your topic...';}
  else{pr.classList.add('dsp-m-on');ai.classList.remove('dsp-m-on');q('dsp-genbtn-lbl').textContent='Build from My Content';if(q('dsp-s0t'))q('dsp-s0t').textContent='Parsing your slides...';}
  dspUpdCost();
};

window.DS_PPT_BASE_SLIDES=__PPTBASESLIDES__;
window.DS_PPT_BASE_CREDITS=__PPTBASECR__;
window.DS_PPT_EXTRA_RATE=__PPTRATE__;
window.dspUpdCost=function(){
  var n=parseInt(q('dsp-slides').value)||10;
  var lbl=q('dsp-pill-design-lbl');
  var design=lbl?lbl.textContent:'Navy Pro';
  var baseSlides=window.DS_PPT_BASE_SLIDES||5;
  var baseCredits=window.DS_PPT_BASE_CREDITS||5;
  var extraRate=window.DS_PPT_EXTRA_RATE||1;
  var cost=(n<=baseSlides)?baseCredits:(baseCredits+(n-baseSlides)*extraRate);
  q('dsp-costsumm').textContent=n+' slide'+(n>1?'s':'')+' · '+design+' · '+(G.mode==='ai'?'AI Mode':'Preserve');
  q('dsp-costamt').innerHTML=cost+' credits <span id="dsp-costhave" style="font-size:11.5px;font-weight:400;color:#6b7280;">(You have '+G.credits.toLocaleString()+'cr)</span>';
  return cost;
};

function setStep(i,state){
  var s=q('dsp-s'+i);if(!s)return;
  s.classList.remove('act','done');
  var ic=s.querySelector('.dsp-stic');
  if(state==='active')s.classList.add('act');
  if(state==='done'){s.classList.add('done');if(ic)ic.textContent='✓';}
}
function resetSteps(){
  var icons=['🧠','📋','🎨','✅'];
  [0,1,2,3].forEach(function(i){
    var s=q('dsp-s'+i);if(!s)return;
    s.classList.remove('act','done');
    var ic=s.querySelector('.dsp-stic');if(ic)ic.textContent=icons[i];
  });
}

function parsePreserve(text){
  // Support multiple slide header formats:
  // "Slide 1 – Cover" or "Slide 1: Cover" or "Slide 1 - Cover"
  var raw=text.split(/(?=(?:^|\n)Slide\s+\d+\s*[:\-–])/i);
  var slides=[];
  raw.forEach(function(part){
    part=part.trim();if(!part)return;

    // Match slide header line
    var headerMatch=part.match(/^Slide\s+\d+\s*[:\-–]\s*([^\n]+)/i);
    if(!headerMatch)return;

    var slideTitle=headerMatch[1].trim();
    if(!slideTitle||slideTitle.length<2)return;

    // Get remaining lines after header
    var rest=part.replace(/^Slide\s+\d+\s*[:\-–][^\n]*/i,'').trim();
    var lines=rest.split('\n').map(function(l){return l.trim();}).filter(Boolean);

    var subtitle='';
    var content=[];

    var parsedTitle = '';
    lines.forEach(function(line){
      // Skip label-only lines with no value
      if(/^(Title|Sub Header|Point\s*\d*)\s*:?\s*$/i.test(line)) return;

      // Extract Title: value → use as slide title
      var titleMatch = line.match(/^Title\s*:\s*(.+)/i);
      if(titleMatch){
        parsedTitle = titleMatch[1].trim();
        return;
      }

      // Extract Subtitle: value → use as subtitle
      var subMatch = line.match(/^Sub(?:title|Header|title)?\s*:\s*(.+)/i);
      if(subMatch){
        if(!subtitle) subtitle = subMatch[1].trim();
        return;
      }

      // Extract bullet points — "* text" or "- text"
      var bulletMatch = line.match(/^[\*\-\•]\s*(.+)/);
      if(bulletMatch){
        if(content.length<8) content.push(bulletMatch[1].trim());
        return;
      }

      // Extract point content — "Point 1: Some text" → "Some text"
      var pointMatch = line.match(/^Point\s+\d+\s*:\s*(.+)/i);
      if(pointMatch){
        if(content.length<8) content.push(pointMatch[1].trim());
        return;
      }

      // First non-label line with no bullet → subtitle
      if(!subtitle && line.length>5 && content.length===0){
        subtitle = line;
        return;
      }

      // Everything else → content
      if(content.length<8) content.push(line);
    });

    // Use parsed Title: value if found, else use slide header name
    if(parsedTitle) slideTitle = parsedTitle;

    // Determine slide type
    var type='bullet';
    if(slides.length===0) type='title';
    else if(slideTitle.toLowerCase().includes('cover') || 
            headerMatch[1].trim().toLowerCase().includes('cover')) type='title';

    slides.push({
      type:type,
      title:slideTitle,
      subtitle:subtitle,
      content:content,
      image_query:slideTitle+' business professional',
      icon:''
    });
  });

  var n=parseInt(q('dsp-slides').value)||10;

  // Fallback if nothing parsed
  if(!slides.length){
    text.split(/\n\s*\n/).filter(function(p){return p.trim().length>10;}).forEach(function(p,i){
      var ls=p.trim().split('\n').map(function(l){return l.trim();}).filter(Boolean);
      slides.push({type:i===0?'title':'bullet',title:ls[0]||'Section '+(i+1),subtitle:'',content:ls.slice(1,6),image_query:'',icon:''});
    });
  }

  while(slides.length<n) slides.push({type:'bullet',title:'Slide '+(slides.length+1),subtitle:'',content:['Add content here'],image_query:'',icon:''});
  return slides.slice(0,n);
}

window.dspGen=function(){
    var prompt=q('dsp-prompt').value.trim();
  if(!prompt){
    q('dsp-pbox').style.setProperty('border-color','#ef4444','important');
    q('dsp-pbox').style.setProperty('box-shadow','0 0 0 4px rgba(239,68,68,.25)','important');
    setTimeout(function(){
      q('dsp-pbox').style.setProperty('border-color','#2563eb','important');
      q('dsp-pbox').style.setProperty('box-shadow','0 0 0 4px rgba(37,99,235,.18)','important');
    },2000);
    q('dsp-prompt').focus();return;
  }
  var cost=dspUpdCost();
  if(G.credits<cost){alert('Not enough credits! Need '+cost+', you have '+G.credits+'.');return;}

  if(G.mode==='preserve'){
    dspShow('dsp-loading');resetSteps();q('dsp-prog').style.width='0%';
    q('dsp-ltitle').textContent='Building from Your Content...';
    q('dsp-lsub').textContent='Parsing and organising slides...';
    setStep(0,'active');
    setTimeout(function(){q('dsp-prog').style.width='35%';setStep(0,'done');setStep(1,'active');},400);
    setTimeout(function(){
      var parsed=parsePreserve(prompt);
      setStep(1,'done');setStep(2,'active');q('dsp-prog').style.width='65%';
      setTimeout(function(){
        setStep(2,'done');setStep(3,'active');q('dsp-prog').style.width='90%';
        G.pptData={title:parsed[0].title||'Presentation',slides:parsed};
        G.pptId='preserve_'+Date.now();G.pptTitle=G.pptData.title;
        fetch(PURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify({topic:'credit_deduct_only',slides:0,deduct_only:true,amount:20})})
          .then(function(r){return r.json();}).then(function(d){if(d.credits_balance!==undefined){G.credits=d.credits_balance;var ce=document.getElementById('ds-cr-display');if(ce)ce.textContent=G.credits.toLocaleString();}}).catch(function(){});
        setTimeout(function(){setStep(3,'done');q('dsp-prog').style.width='100%';setTimeout(function(){buildEditor(G.pptData);},400);},600);
      },700);
    },1100);
    return;
  }

  var n=parseInt(q('dsp-slides').value)||10;
  var lang=q('dsp-lang').value;
  var design=q('dsp-design').value;
  var imgs=q('dsp-imgs').value==='1';
  dspLiveGenShow(prompt,n,design);
  fetch(PURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({topic:prompt,slides:n,num_slides:n,language:lang,design:design,template:design,use_images:imgs})})
  .then(function(r){if(r.status===404){throw new Error('generate-ppt endpoint not found (404).');}if(!r.ok)throw new Error('Server error '+r.status);return r.json();})
  .then(function(d){
    if(d.error||d.message){dspLiveGenHide();dspShow('dsp-home');alert('Error: '+(d.message||d.error));return;}
    if(!d.ppt_data||!d.ppt_data.slides||!d.ppt_data.slides.length){dspLiveGenHide();dspShow('dsp-home');alert('No slides returned. Try again.');return;}
    d.ppt_data.layout=q('dsp-layout')?q('dsp-layout').value:'16:9';
    if(d.credits_balance!==undefined){G.credits=d.credits_balance;var ce=document.getElementById('ds-cr-display');if(ce)ce.textContent=G.credits.toLocaleString();}
    G.pptId=d.ppt_id;G.pptData=d.ppt_data;G.pptTitle=(d.ppt_data.title)||'Presentation';
    var _fp=d.ppt_data,_done=false;
function _fin(){if(_done)return;_done=true;dspLiveGenHide();buildEditor(_fp);}
setTimeout(_fin,12000);
dspLiveGenStream(_fp,design,_fin);
  })
  .catch(function(e){dspLiveGenHide();dspShow('dsp-home');alert('Error: '+e.message);});
};

window.dspLiveGenShow=function(prompt,total,design){
  var old=q('dsp-livegen-overlay');if(old&&old.parentNode)old.parentNode.removeChild(old);
  var designVal=design||'navy-pro';
  var theme=THEMES[designVal]||THEMES['navy-pro'];
  var ov=document.createElement('div');
  ov.id='dsp-livegen-overlay';
  ov.style.cssText='position:fixed;inset:0;z-index:99998;background:#1a1a1a;display:flex;flex-direction:column;font-family:-apple-system,BlinkMacSystemFont,sans-serif;';

  // Top banner - exactly like Gamma
  var banner='<div id="dsp-lg-banner" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:10px 20px;background:#fff;border-bottom:1px solid #e5e7eb;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.1);">'
    +'<div style="width:16px;height:16px;border:2px solid #d1d5db;border-top-color:#6366f1;border-radius:50%;animation:dspSpin .8s linear infinite;flex-shrink:0;"></div>'
    +'<span id="dsp-lg-banner-txt" style="font-size:13px;font-weight:600;color:#374151;"><b style=\'color:#111;\'>AI Generating:</b> Don\'t close this tab while generation is in progress.</span>'
    +'</div>';

  // Left panel + main canvas - like Gamma editor
  var body='<div style="display:flex;flex:1;overflow:hidden;background:#3c4043;">'
    // Left thumbnails panel
    +'<div style="width:160px;flex-shrink:0;background:#2d2e30;border-right:1px solid #1a1a1a;display:flex;flex-direction:column;overflow:hidden;">'
    +'<div style="padding:8px 10px 6px;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#6b7280;border-bottom:1px solid #1a1a1a;flex-shrink:0;">SLIDES</div>'
    +'<div id="dsp-lg-thumbs" style="flex:1;overflow-y:auto;padding:10px 8px;display:flex;flex-direction:column;gap:8px;"></div>'
    +'</div>'
    // Main canvas
    +'<div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px;gap:16px;overflow:hidden;">'
    +'<div id="dsp-lg-main" style="width:min(860px,90%);aspect-ratio:16/9;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.5);background:'+theme.bg+';position:relative;font-size:8px;">'
    // Shimmer overlay (left half grey, right half content) - Gamma style
    +'<div id="dsp-lg-shimmer" style="position:absolute;inset:0;background:rgba(40,40,40,0.85);z-index:10;display:flex;align-items:center;justify-content:center;">'
    +'<div style="display:flex;flex-direction:column;align-items:center;gap:12px;">'
    +'<div style="width:36px;height:36px;border:3px solid rgba(255,255,255,.15);border-top-color:#6366f1;border-radius:50%;animation:dspSpin .8s linear infinite;"></div>'
    +'<div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.5px;">AI generating...</div>'
    +'</div>'
    +'</div>'
    +'<div id="dsp-lg-slide-content" style="position:absolute;inset:0;"></div>'
    +'</div>'
    +'</div>'
    +'</div>';

  ov.innerHTML=banner+body;
  document.body.appendChild(ov);
  // Add CSS for shimmer pulse
  var styleEl=document.createElement('style');
  styleEl.id='dsp-lg-style';
  styleEl.textContent='@keyframes dspLgPulse{0%,100%{opacity:.85}50%{opacity:.6}}#dsp-lg-shimmer{animation:dspLgPulse 1.5s ease-in-out infinite;}';
  document.head.appendChild(styleEl);
};

window.dspLiveGenHide=function(){
  var ov=q('dsp-livegen-overlay');
  if(ov&&ov.parentNode)ov.parentNode.removeChild(ov);
  var st=q('dsp-lg-style');
  if(st&&st.parentNode)st.parentNode.removeChild(st);
};

window.dspLiveGenStream=function(ppt,design,onDone){
  var designVal=design||'navy-pro';
  var theme=THEMES[designVal]||THEMES['navy-pro'];
  var slides=ppt.slides;
  var total=slides.length;
  var thumbsCol=q('dsp-lg-thumbs');
  var mainContent=q('dsp-lg-slide-content');
  var shimmer=q('dsp-lg-shimmer');
  var bannerTxt=q('dsp-lg-banner-txt');
  if(thumbsCol)thumbsCol.innerHTML='';
  if(bannerTxt)bannerTxt.innerHTML='<b style="color:#111;">AI Generating:</b> Don\'t close this tab while generation is in progress.';

  slides.forEach(function(s,i){
    // Add placeholder thumb immediately
    var thumbWrap=document.createElement('div');
    thumbWrap.style.cssText='display:flex;flex-direction:column;align-items:flex-start;cursor:pointer;';
    var thumbNum=document.createElement('span');
    thumbNum.style.cssText='font-size:9px;color:#9aa0a6;margin-bottom:3px;padding-left:2px;';
    thumbNum.textContent=i+1;
    var thumbBox=document.createElement('div');
    thumbBox.style.cssText='width:100%;aspect-ratio:16/9;border-radius:3px;background:'+theme.bg+';border:2px solid transparent;overflow:hidden;font-size:3.5px;position:relative;opacity:0.35;transition:all .4s ease;box-shadow:0 1px 3px rgba(0,0,0,.3);';
    // Grey shimmer placeholder
    thumbBox.innerHTML='<div style="position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;">'
      +'<div style="width:10px;height:10px;border:1.5px solid rgba(255,255,255,.2);border-top-color:#6366f1;border-radius:50%;animation:dspSpin .8s linear infinite;"></div>'
      +'</div>';
    thumbWrap.appendChild(thumbNum);
    thumbWrap.appendChild(thumbBox);
    if(thumbsCol)thumbsCol.appendChild(thumbWrap);

    setTimeout(function(){
      // Show slide in left thumb
      thumbBox.innerHTML=renderThumbSlide(s,theme,i+1);
      thumbBox.style.opacity='1';
      thumbBox.style.borderColor='#8ab4f8';

      // Update main canvas - show slide with shimmer fading out
      if(mainContent){
        mainContent.style.background=theme.bg;
        mainContent.innerHTML=renderThumbSlide(s,theme,i+1);
      }
      // Fade out shimmer to reveal slide
      if(shimmer){
        shimmer.style.transition='opacity .5s ease';
        shimmer.style.opacity='0';
        setTimeout(function(){
          // Pulse shimmer back for next slide
          if(i<total-1&&shimmer){
            shimmer.style.transition='opacity .3s ease';
            shimmer.style.opacity='0.85';
          }
        },300);
      }

      // Scroll thumb into view
      thumbWrap.scrollIntoView({block:'nearest',behavior:'smooth'});

      // Click thumb to show in main
      thumbWrap.onclick=function(){
        if(mainContent){mainContent.style.background=theme.bg;mainContent.innerHTML=renderThumbSlide(s,theme,i+1);}
        // Highlight active thumb
        thumbsCol.querySelectorAll('div>div[style*="border-radius:3px"]').forEach(function(b){b.style.borderColor='transparent';});
        thumbBox.style.borderColor='#8ab4f8';
      };

      // Update banner on last slide
      if(i===total-1){
        setTimeout(function(){
          if(shimmer)shimmer.style.display='none';
          if(bannerTxt)bannerTxt.innerHTML='<b style="color:#111;">Done!</b> Opening your presentation editor...';
          var _bnr=q('dsp-lg-banner');var spinnerEl=_bnr?_bnr.querySelector('div'):null;
          if(spinnerEl){spinnerEl.style.borderTopColor='#10b981';spinnerEl.style.animation='none';spinnerEl.style.background='#10b981';spinnerEl.style.border='none';spinnerEl.innerHTML='<span style="color:#fff;font-size:10px;font-weight:900;">\u2713</span>';spinnerEl.style.display='flex';spinnerEl.style.alignItems='center';spinnerEl.style.justifyContent='center';}
          setTimeout(function(){if(onDone)onDone();},700);
        },400);
      }
    },i*250);
  });
};

window.dspTab=function(t){
  G.activeTab=t;
  var list=q('dsp-list'),prev=q('dsp-preview');
  if(list)list.style.display=t==='edit'?'':'none';
  if(prev)prev.style.display=t==='preview'?'':'none';
  if(t==='preview') dspRenderPreview();
};

var LAYOUTS={'16:9':{w:900,h:506},'4:5':{w:506,h:632},'9:16':{w:320,h:568}};
function getLayoutDims(){
  var el=q('dsp-layout');
  var lk=(el&&el.value)?el.value:'16:9';
  return LAYOUTS[lk]||LAYOUTS['16:9'];
}

function buildEditor(ppt){
  if(!ppt||!ppt.slides||!ppt.slides.length){dspShow('dsp-home');alert('No slide data returned.');return;}
  G.pptData=ppt;G.pptTitle=ppt.title||'Presentation';
  G.activeSlideIdx=0;
  dspEditorOpen();
  q('dsp-etitle').textContent=ppt.title||'Presentation';
  var _dims=getLayoutDims();
  var _main=q('dsp-ppt-main-slide');
  if(_main){_main.style.width=_dims.w+'px';_main.style.height=_dims.h+'px';}
  renderEditList();
  dspRenderThumbs();
  dspSelectThumb(0);
  dspDrawRuler();
  // Auto-fetch removed — images are now added manually per-slide via the editor's image picker.
}

function dspRenderThumbs(){
  var thumbs=q('dsp-ppt-thumbs');
  if(!thumbs||!G.pptData)return;
  var designEl=q('dsp-design');
  var designVal=(designEl&&designEl.value)?designEl.value:'navy-pro';
  var theme=THEMES[designVal]||THEMES['navy-pro'];
  thumbs.innerHTML='';
  G.pptData.slides.forEach(function(s,i){
    var wrap=document.createElement('div');
    wrap.className='dsp-thumb-item'+(i===G.activeSlideIdx?' dsp-thumb-active':'');
    var num=document.createElement('span');
    num.className='dsp-thumb-num';
    num.textContent=i+1;
    var box=document.createElement('div');
    box.className='dsp-thumb-box';
    var ld=getLayoutDims();
    box.style.cssText='background:'+theme.bg+';font-size:3.5px;aspect-ratio:'+ld.w+'/'+ld.h+';';
    box.innerHTML=renderPreviewSlide(s,theme,i+1);
    wrap.appendChild(num);
    wrap.appendChild(box);
    wrap.addEventListener('click',function(){dspSelectThumb(i);});
    thumbs.appendChild(wrap);
  });
}

function dspSelectThumb(i){
  if(!G.pptData||!G.pptData.slides)return;
  i=Math.max(0,Math.min(i,G.pptData.slides.length-1));
  G.activeSlideIdx=i;
  var thumbs=q('dsp-ppt-thumbs');
  if(thumbs){
    Array.from(thumbs.children).forEach(function(el,j){el.classList.toggle('dsp-thumb-active',j===i);});
    var activeWrap=thumbs.children[i];
    if(activeWrap)activeWrap.scrollIntoView({block:'nearest',behavior:'smooth'});
  }
  var main=q('dsp-ppt-main-slide');
  if(main){
    var designEl=q('dsp-design');
    var designVal=(designEl&&designEl.value)?designEl.value:'navy-pro';
    var theme=THEMES[designVal]||THEMES['navy-pro'];
    var s=G.pptData.slides[i];
    var dims=getLayoutDims();
    main.style.cssText='width:'+dims.w+'px;height:'+dims.h+'px;overflow:hidden;position:relative;box-shadow:0 2px 8px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.5);background:'+theme.bg+';flex-shrink:0;border-radius:2px;';
    main.innerHTML=renderEditSlide(s,theme,i);
  }
  var lbl=q('dsp-ppt-nav-lbl');
  if(lbl)lbl.textContent=(i+1)+' / '+G.pptData.slides.length;
}

var G_zoom=1;
window.dspZoom=function(delta){
  G_zoom=Math.min(2,Math.max(0.3,G_zoom+delta));
  var outer=q('dsp-ppt-slide-outer');
  if(outer)outer.style.transform='scale('+G_zoom+')';
  var lbl=q('dsp-ppt-zoom-lbl');
  if(lbl)lbl.textContent=Math.round(G_zoom*100)+'%';
};

window.dspEditorOpen=function(){
  var fs=q('dsp-editor-fullscreen');
  if(!fs)return;
  fs.classList.add('dsp-open');
  document.body.classList.add('dsp-editor-open');
  setTimeout(function(){dspSelectThumb(G.activeSlideIdx||0);},60);
};

window.dspEditorClose=function(){
  var fs=q('dsp-editor-fullscreen');
  if(fs)fs.classList.remove('dsp-open');
  document.body.classList.remove('dsp-editor-open');
  dspShow('dsp-home');
};

window.dspToggleFullscreen=function(){dspEditorOpen();};

document.addEventListener('wheel',function(e){
  var area=q('dsp-ppt-canvas-area');
  if(!area||!area.contains(e.target))return;
  e.preventDefault();
  dspZoom(e.deltaY<0?0.1:-0.1);
},{passive:false});

window.dspPptNav=function(dir){
  if(!G.pptData)return;
  dspSelectThumb(G.activeSlideIdx+dir);
};

// Add Slide logic moved to Snippet 17 (variant picker ui), so it can keep
// the canvas/layout data (S.data) in sync instead of only updating G.pptData.

window.dspReturnToEditor=function(){
  dspEditorOpen();
  dspRenderThumbs();
  dspSelectThumb(G.activeSlideIdx);
};

window.dspEditorUpdate=function(slideIdx,field,val,bulletIdx){
  if(!G.pptData||!G.pptData.slides[slideIdx])return;
  if(field==='content'&&bulletIdx!==undefined){G.pptData.slides[slideIdx].content[bulletIdx]=val;}
  else{G.pptData.slides[slideIdx][field]=val;}
  dspSelectThumb(slideIdx);
  var thumbEl=q('dsp-ppt-thumbs');
  if(thumbEl){
    var theme=THEMES[q('dsp-design').value]||THEMES['navy-pro'];
    var th=thumbEl.children[slideIdx];
    if(th){var thumbDiv=th.querySelector('.dsp-thumb-box');if(thumbDiv){thumbDiv.style.cssText='background:'+theme.bg+';font-size:3.5px;';thumbDiv.innerHTML=renderPreviewSlide(G.pptData.slides[slideIdx],theme,slideIdx+1);}}
  }
};

window.dspEditorDeleteSlide=function(i){
  if(!G.pptData||G.pptData.slides.length<=1)return;
  G.pptData.slides.splice(i,1);
  renderEditList();dspRenderThumbs();dspSelectThumb(Math.min(i,G.pptData.slides.length-1));
};

window.dspEditorDeleteBullet=function(si,bi){
  if(!G.pptData)return;
  G.pptData.slides[si].content.splice(bi,1);
  dspSelectThumb(si);
};

window.dspEditorAddBullet=function(si){
  if(!G.pptData)return;
  G.pptData.slides[si].content.push('');
  dspSelectThumb(si);
};

function fetchAllSlideImages(){
  if(!G.pptData||!G.pptData.slides)return;
  var indices=[];
  G.pptData.slides.forEach(function(s,i){if(s.type==='image_text'&&!s.image_data){indices.push(i);}});
  if(!indices.length){updateImgStatus('');return;}
  var done=0,total=indices.length;
  updateImgStatus('Generating '+total+' image'+(total>1?'s':'')+' in parallel...');
  function runOne(idx){
    G.pptData.slides[idx].image_loading=true;
    dspRenderPreview();
    var query=G.pptData.slides[idx].image_query||G.pptData.slides[idx].title;
    return fetch(IMGURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify({query:query})})
    .then(function(r){return r.json();})
    .then(function(d){
      G.pptData.slides[idx].image_loading=false;
      if(d&&d.success&&d.image){G.pptData.slides[idx].image_data=d.image;}
      else{G.pptData.slides[idx].image_failed=true;}
    })
    .catch(function(){G.pptData.slides[idx].image_loading=false;G.pptData.slides[idx].image_failed=true;})
    .then(function(){
      done++;
      dspRenderPreview();
      if(done>=total){updateImgStatus('');}
      else{updateImgStatus('Generated '+done+' of '+total+' images...');}
      if(indices.length){return runOne(indices.shift());}
    });
  }
  var workers=Math.min(3,indices.length);
  for(var w=0;w<workers;w++){runOne(indices.shift());}
}

function updateImgStatus(msg){
  var tb=q('dsp-imgstatus-tb');
  if(tb){if(!msg){tb.style.display='none';}else{tb.style.display='';tb.textContent=msg;}}
  var bar=q('dsp-imgstatus');
  if(bar){if(!msg){bar.style.display='none';}else{bar.style.display='';bar.textContent=msg;}}
}

function renderEditList(){
  var list=q('dsp-list');if(!list)return;
  list.innerHTML='';
  if(!G.pptData||!G.pptData.slides)return;
  G.pptData.slides.forEach(function(s,i){
    var card=document.createElement('div');card.className='dsp-card';
    var hd=document.createElement('div');hd.className='dsp-chd';
    var num=document.createElement('div');num.className='dsp-cnum';num.textContent=i+1;
    var typeSel=document.createElement('select');typeSel.className='dsp-typesel';
    SLIDE_TYPES.forEach(function(tp){
      var op=document.createElement('option');op.value=tp;op.textContent=TYPE_LABELS[tp];
      if(s.type===tp)op.selected=true;typeSel.appendChild(op);
    });
    typeSel.addEventListener('click',function(e){e.stopPropagation();});
    typeSel.addEventListener('change',function(){G.pptData.slides[i].type=typeSel.value;renderEditList();});
    var tit=document.createElement('div');tit.className='dsp-ctit';tit.contentEditable='true';tit.textContent=s.title||'';
    tit.addEventListener('click',function(e){e.stopPropagation();});
    tit.addEventListener('input',function(){G.pptData.slides[i].title=tit.innerText.trim();});
    hd.appendChild(num);hd.appendChild(typeSel);hd.appendChild(tit);
    var body=document.createElement('div');body.className='dsp-cbdy';
    if(s.type==='title'){
      var subRow=document.createElement('div');subRow.className='dsp-crow';
      var subDot=document.createElement('div');subDot.className='dsp-cdot';subDot.style.background='#10b981';
      var subInput=document.createElement('div');subInput.className='dsp-cpt';subInput.contentEditable='true';
      subInput.textContent=s.subtitle||'';
      subInput.addEventListener('input',function(){G.pptData.slides[i].subtitle=subInput.innerText.trim();});
      subRow.appendChild(subDot);subRow.appendChild(subInput);body.appendChild(subRow);
    }
    (s.content||[]).forEach(function(p,pi){
      var row=document.createElement('div');row.className='dsp-crow';
      var dot=document.createElement('div');dot.className='dsp-cdot';
      var pt=document.createElement('div');pt.className='dsp-cpt';pt.contentEditable='true';pt.textContent=p;
      pt.addEventListener('input',function(){G.pptData.slides[i].content[pi]=pt.innerText.trim();});
      var del=document.createElement('button');del.type='button';del.className='dsp-bullet-del';del.innerHTML='×';
      del.addEventListener('click',function(e){e.stopPropagation();G.pptData.slides[i].content.splice(pi,1);renderEditList();});
      row.appendChild(dot);row.appendChild(pt);row.appendChild(del);body.appendChild(row);
    });
    card.appendChild(hd);card.appendChild(body);
    var acts=document.createElement('div');acts.className='dsp-actions';
    if(s.type!=='title'&&s.type!=='quote'){
      var addB=document.createElement('button');addB.type='button';addB.className='dsp-act';addB.textContent='+ Bullet';
      addB.addEventListener('click',function(){G.pptData.slides[i].content=G.pptData.slides[i].content||[];G.pptData.slides[i].content.push('New point');renderEditList();});
      acts.appendChild(addB);
    }
    var delS=document.createElement('button');delS.type='button';delS.className='dsp-act dsp-act-del';delS.textContent='🗑 Delete Slide';
    delS.addEventListener('click',function(){
      if(G.pptData.slides.length<=1){alert('Cannot delete the only slide.');return;}
      if(!confirm('Delete slide '+(i+1)+'?'))return;
      G.pptData.slides.splice(i,1);renderEditList();
    });
    acts.appendChild(delS);card.appendChild(acts);
    list.appendChild(card);
    var addRow=document.createElement('div');addRow.className='dsp-addslide-row';
    var addBtn=document.createElement('button');addBtn.type='button';addBtn.className='dsp-addslide';addBtn.textContent='+ Add Slide';
    addBtn.addEventListener('click',function(){G.pptData.slides.splice(i+1,0,{type:'bullet',title:'New Slide',subtitle:'',content:['New point'],image_query:'',icon:''});renderEditList();});
    addRow.appendChild(addBtn);list.appendChild(addRow);
  });
}

window.dspRenderPreview=function(){
  if(!G.pptData||!G.pptData.slides)return;
  if(q('dsp-ppt-thumbs'))dspRenderThumbs();
  if(q('dsp-ppt-main-slide'))dspSelectThumb(G.activeSlideIdx||0);
  dspDrawRuler();
};

function dspDrawRuler(){
  var ruler=q('dsp-ppt-ruler');if(!ruler)return;
  ruler.innerHTML='';
  for(var i=0;i<=20;i++){
    var m=document.createElement('div');m.className='dsp-ruler-mark';m.style.left=(i*5)+'%';
    m.style.height=i%5===0?'10px':'6px';
    if(i%5===0&&i>0){m.style.top='4px';m.textContent=i;}
    ruler.appendChild(m);
  }
}

var G_editSlideIdx=0;
function ce(field,sidx,cidx,style,val){
  var ci=cidx!==undefined?' data-cidx="'+cidx+'"':'';
  return '<div class="dsp-editable" contenteditable="true" data-field="'+field+'" data-sidx="'+sidx+'"'+ci+
    ' style="outline:none;'+style+'" onblur="dspCanvasSave(this)" onkeydown="if(event.key===\'Enter\'){event.preventDefault();this.blur();}">'+escH(val||'')+'</div>';
}
function slideIcons(){return ['💡','📊','🎯','✅','🚀','⭐','🏆','🌐','🛡','⚡'];}
function ic(iName,sz){
if(!iName||iName.indexOf(':')<0) return '<div style="width:'+sz+'em;height:'+sz+'em;"></div>';
var u='https://api.iconify.design/'+iName.replace(':','/')+'.svg?color=%23ffffff';
return '<img src="'+u+'" style="width:'+sz+'em;height:'+sz+'em;display:block;" onerror="this.style.display=\'none\';"/>';
}


function renderEditSlide(s,theme,slideIdx){
  G_editSlideIdx=slideIdx;
  var bg=theme.bg,tc=theme.title,bc=theme.body,ac=theme.accent;
  var type=s.type||'bullet';
  var content=s.content||[];
  var icns=slideIcons();
  var html='<div style="position:absolute;inset:0;background:'+bg+';overflow:hidden;font-family:Arial,sans-serif;">';

  if(type==='title'){
    html+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:6% 8%;">';
    html+=ce('title',slideIdx,undefined,'font-size:2.2em;font-weight:900;color:'+tc+';line-height:1.15;max-width:90%;',s.title);
    html+='<div style="width:8%;height:3px;background:'+ac+';margin:0.8em 0;"></div>';
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.85em;color:'+bc+';max-width:80%;line-height:1.5;',s.subtitle);
    content.forEach(function(c,j){
      if(!String(c).trim())return;
      html+=ce('content',slideIdx,j,'font-size:0.68em;color:'+bc+';max-width:80%;line-height:1.5;margin-top:0.3em;',c);
    });
    html+='</div>';
  } else if(type==='stats'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:4%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.5em;font-weight:800;color:'+tc+';margin-bottom:0.15em;',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.7em;color:'+ac+';margin-bottom:0.3em;font-weight:600;',s.subtitle);
    html+='</div>';
    html+='<div style="position:absolute;top:30%;left:0;right:0;bottom:4%;display:flex;align-items:center;justify-content:space-around;padding:0 4%;">';
    content.slice(0,3).forEach(function(c,j){
      var parts=String(c).split(':');var num=parts[0].trim(),lbl=parts.slice(1).join(':').trim();
      html+='<div style="text-align:center;flex:1;padding:0 2%;"><div style="font-size:2.6em;font-weight:900;color:'+ac+';line-height:1;">'+ce('content',slideIdx,j,'font-size:1em;font-weight:900;color:'+ac+';display:inline-block;',num)+'</div>';
      html+='<div style="width:30%;height:2px;background:'+ac+';margin:0.3em auto;"></div>';
      html+='<div style="font-size:0.65em;color:'+bc+';line-height:1.3;margin-top:0.3em;">'+escH(lbl)+'</div></div>';
    });
    html+='</div>';
  } else if(type==='quote'){
    html+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8%;">';
    html+='<div style="font-size:5em;color:'+ac+';line-height:0.7;margin-bottom:0.2em;opacity:0.6;">"</div>';
    html+=ce('title',slideIdx,undefined,'font-size:1.05em;color:'+tc+';font-style:italic;text-align:center;line-height:1.6;max-width:85%;',s.title);
    if(content[0]) html+='<div style="margin-top:1.2em;color:'+ac+';font-size:0.7em;font-weight:700;">— '+ce('content',slideIdx,0,'display:inline;color:'+ac+';',content[0])+'</div>';
    html+='</div>';
  } else if(type==='feature_grid'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.5em;font-weight:800;color:'+tc+';',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.68em;color:'+ac+';font-weight:600;margin-top:0.1em;',s.subtitle);
    html+='</div>';
    html+='<div style="position:absolute;top:26%;left:4%;right:4%;bottom:4%;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:2.5%;">';
    content.slice(0,4).forEach(function(c,j){
      var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():String(c);var d=colon>0?String(c).slice(colon+1).trim():'';
      html+='<div style="background:'+ac+'18;border-radius:5px;padding:5% 6%;display:flex;flex-direction:column;">';
      html+='<div style="display:flex;align-items:center;gap:0.5em;margin-bottom:0.5em;"><div style="font-size:1.3em;flex-shrink:0;">'+ic((s.icons&&s.icons[j])?s.icons[j]:'',1.3)+'</div>';
      html+='<div style="font-size:0.78em;font-weight:800;color:'+tc+';">'+escH(h)+'</div></div>';
      html+='<div style="font-size:0.62em;color:'+bc+';line-height:1.4;">'+ce('content',slideIdx,j,'font-size:1em;color:'+bc+';',d||c)+'</div></div>';
    });
    html+='</div>';
	} else if(type==='five_point'||type==='six_point'||type==='seven_point'||type==='eight_point'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.35em;font-weight:800;color:'+tc+';',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.6em;color:'+ac+';font-weight:600;margin-top:0.1em;',s.subtitle);
    html+='</div>';
    var nPts=type==='five_point'?5:type==='six_point'?6:type==='seven_point'?7:8;
    var mpItems=content.slice(0,nPts);
    var mpCols=3;
    var mpRows=Math.ceil(nPts/mpCols);
    html+='<div style="position:absolute;top:24%;left:4%;right:4%;bottom:3%;display:flex;flex-direction:column;gap:2.5%;">';
    for(var mpR=0;mpR<mpRows;mpR++){
      html+='<div style="display:flex;gap:2.5%;flex:1;">';
      for(var mpC=0;mpC<mpCols;mpC++){
        var mpI=mpR*mpCols+mpC,mpIt=mpItems[mpI];
        if(!mpIt&&type==='five_point'&&mpR===1&&mpI>=nPts){html+='<div style="flex:1;"></div>';continue;}
        if(!mpIt){html+='<div style="flex:1;"></div>';continue;}
        var mpColon=String(mpIt).indexOf(':'),mpH=mpColon>0?String(mpIt).slice(0,mpColon).trim():String(mpIt),mpD=mpColon>0?String(mpIt).slice(mpColon+1).trim():'';
        html+='<div style="flex:1;background:'+ac+'18;border-radius:5px;padding:4% 5%;display:flex;flex-direction:column;overflow:hidden;">';
        html+='<div style="display:flex;align-items:center;gap:0.4em;margin-bottom:0.3em;"><div style="font-size:1em;flex-shrink:0;">'+ic((s.icons&&s.icons[mpI])?s.icons[mpI]:'',1)+'</div>';
        html+='<div style="font-size:0.68em;font-weight:800;color:'+tc+';overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">'+escH(mpH)+'</div></div>';
        html+='<div style="width:24px;height:2px;background:'+ac+';margin-bottom:0.3em;"></div>';
        html+='<div style="font-size:0.52em;color:'+bc+';line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">'+escH(mpD)+'</div></div>';
      }
      html+='</div>';
    }
    html+='</div>';
  } else if(type==='image_text'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:52%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.4em;font-weight:800;color:'+tc+';line-height:1.2;',s.title);
    if(s.
	subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.65em;color:'+ac+';font-weight:600;margin-top:0.2em;',s.subtitle);
    html+='</div>';
    var itContent=content.slice(0,7);
    if(itContent.length>0&&itContent.length<=6){
      // Box-grid mode: N columns = bullet count, each box has icon + heading + desc
      var gridCols=itContent.length;
      html+='<div style="position:absolute;top:26%;left:4%;right:'+(s.image_data?'52%':'4%')+';bottom:3%;display:grid;grid-template-columns:repeat('+gridCols+',1fr);gap:1.5%;overflow:hidden;">';
      itContent.forEach(function(c,j){
        var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
        html+='<div style="background:'+ac+'18;border-radius:6px;padding:6% 5%;display:flex;flex-direction:column;">';
        html+='<div style="width:1.5em;height:1.5em;border-radius:50%;background:'+ac+'35;display:flex;align-items:center;justify-content:center;font-size:0.7em;flex-shrink:0;margin-bottom:0.4em;">'+ic((s.icons&&s.icons[j])?s.icons[j]:'',.8)+'</div>';
        if(h) html+='<div style="font-size:0.6em;font-weight:800;color:'+tc+';line-height:1.2;margin-bottom:0.25em;">'+escH(h)+'</div>';
        html+=ce('content',slideIdx,j,'font-size:0.5em;color:'+bc+';line-height:1.3;',d);
        html+='</div>';
      });
      html+='</div>';
    } else {
      // Vertical list mode (unchanged) for 7+ bullets
      html+='<div style="position:absolute;top:26%;left:4%;right:'+(s.image_data?'52%':'4%')+';bottom:3%;overflow:hidden;">';
      itContent.forEach(function(c,j){
        var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
        html+='<div style="display:flex;align-items:flex-start;gap:0.5em;margin-bottom:0.7em;">';
        html+='<div style="width:1.4em;height:1.4em;border-radius:50%;background:'+ac+'30;display:flex;align-items:center;justify-content:center;font-size:0.75em;flex-shrink:0;margin-top:0.1em;">'+ic((s.icons&&s.icons[j])?s.icons[j]:'',.85)+'</div>';
        html+='<div style="flex:1;">';
        if(h) html+='<div style="font-size:0.72em;font-weight:800;color:'+tc+';line-height:1.2;">'+escH(h)+'</div>';
        html+=ce('content',slideIdx,j,'font-size:0.62em;color:'+bc+';line-height:1.35;',d);
        html+='</div></div>';
      });
      html+='</div>';
    }
    var imgSrc=s.image_data||'';
    if(imgSrc){
      html+='<div class="dsp-img-editable" onclick="dspImgModalOpen('+slideIdx+')" style="position:absolute;top:4%;right:3%;left:51%;bottom:3%;border-radius:8px;overflow:hidden;background:'+ac+'22;cursor:pointer;">';
      html+='<img src="'+imgSrc+'" style="width:100%;height:100%;object-fit:cover;"/>';
      html+='</div>';
    }
  } else if(type==='two_column'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.5em;font-weight:800;color:'+tc+';',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.68em;color:'+ac+';font-weight:600;margin-top:0.1em;',s.subtitle);
    html+='</div>';
    html+='<div style="position:absolute;top:24%;left:4%;right:4%;bottom:4%;display:grid;grid-template-columns:1fr 1fr;gap:3%;">';
    content.slice(0,2).forEach(function(c,j){
      var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
      html+='<div style="background:'+ac+'18;border-radius:5px;padding:6%;display:flex;flex-direction:column;align-items:center;text-align:center;">';
      html+='<div style="font-size:2.2em;margin-bottom:0.5em;">'+ic((s.icons&&s.icons[j])?s.icons[j]:'',2.2)+'</div>';
      html+='<div style="font-size:0.82em;font-weight:800;color:'+tc+';margin-bottom:0.5em;line-height:1.2;">'+escH(h)+'</div>';
      html+='<div style="width:20%;height:2px;background:'+ac+';margin-bottom:0.6em;opacity:0.6;"></div>';
      html+=ce('content',slideIdx,j,'font-size:0.65em;color:'+bc+';line-height:1.5;',d);
      html+='</div>';
    });
    html+='</div>';
  } else if(type==='three_column'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.5em;font-weight:800;color:'+tc+';',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.68em;color:'+ac+';font-weight:600;margin-top:0.1em;',s.subtitle);
    html+='</div>';
    html+='<div style="position:absolute;top:24%;left:4%;right:4%;bottom:4%;display:grid;grid-template-columns:1fr 1fr 1fr;gap:2.5%;">';
    content.slice(0,3).forEach(function(c,j){
      var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
      html+='<div style="background:'+ac+'18;border-radius:5px;padding:6%;display:flex;flex-direction:column;align-items:center;text-align:center;">';
      html+='<div style="font-size:1.9em;margin-bottom:0.5em;">'+ic((s.icons&&s.icons[j])?s.icons[j]:'',1.9)+'</div>';
      html+='<div style="font-size:0.76em;font-weight:800;color:'+tc+';margin-bottom:0.4em;line-height:1.2;">'+escH(h)+'</div>';
      html+='<div style="width:20%;height:2px;background:'+ac+';margin-bottom:0.5em;opacity:0.6;"></div>';
      html+=ce('content',slideIdx,j,'font-size:0.6em;color:'+bc+';line-height:1.4;',d);
      html+='</div>';
    });
    html+='</div>';
  } else if(type==='timeline'){
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.5em;font-weight:800;color:'+tc+';',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.68em;color:'+ac+';font-weight:600;margin-top:0.1em;',s.subtitle);
    html+='</div>';
    html+='<div style="position:absolute;top:30%;left:4%;right:4%;bottom:3%;display:flex;flex-direction:column;gap:0.6em;overflow:hidden;">';
    content.slice(0,5).forEach(function(c,j){
      var colon=String(c).indexOf(':');var yr=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
      html+='<div style="display:flex;align-items:flex-start;gap:0.8em;">';
      html+='<div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;">';
      html+='<div style="width:0.9em;height:0.9em;border-radius:50%;background:'+ac+';flex-shrink:0;"></div>';
      if(j<content.slice(0,5).length-1) html+='<div style="width:2px;flex:1;min-height:0.8em;background:'+ac+'40;margin-top:2px;"></div>';
      html+='</div><div style="flex:1;padding-bottom:0.3em;">';
      if(yr) html+='<div style="font-size:0.72em;font-weight:800;color:'+ac+';line-height:1;">'+escH(yr)+'</div>';
      html+=ce('content',slideIdx,j,'font-size:0.63em;color:'+bc+';line-height:1.35;margin-top:0.15em;',d);
      html+='</div></div>';
    });
    html+='</div>';
  } else {
    html+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';"></div>';
    html+='<div style="position:absolute;top:3%;left:4%;right:4%;">';
    html+=ce('title',slideIdx,undefined,'font-size:1.5em;font-weight:800;color:'+tc+';',s.title);
    if(s.subtitle) html+=ce('subtitle',slideIdx,undefined,'font-size:0.68em;color:'+ac+';font-weight:600;margin-top:0.1em;',s.subtitle);
    html+='</div>';
    html+='<div style="position:absolute;top:28%;left:4%;right:4%;bottom:3%;display:flex;flex-direction:column;gap:0.55em;overflow:hidden;">';
    content.slice(0,6).forEach(function(c,j){
      var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
      var ico=ic((s.icons&&s.icons[j])?s.icons[j]:'',.85);
      html+='<div style="position:relative;display:flex;align-items:flex-start;gap:0.6em;padding-right:1.6em;">';
      html+='<div style="width:1.5em;height:1.5em;border-radius:50%;background:'+ac+'25;display:flex;align-items:center;justify-content:center;font-size:0.75em;flex-shrink:0;margin-top:0.1em;">'+ico+'</div>';
      html+='<div style="flex:1;">';
      if(h) html+='<div style="font-size:0.75em;font-weight:800;color:'+tc+';line-height:1.2;">'+escH(h)+'</div>';
      html+=ce('content',slideIdx,j,'font-size:0.63em;color:'+bc+';line-height:1.35;',d);
      html+='</div>';
      html+='<button onclick="event.stopPropagation();dspEditorDeleteBullet('+slideIdx+','+j+')" title="Remove point" '
        +'style="position:absolute;right:0;top:0;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.35);'
        +'color:#ef4444;border-radius:50%;width:1.1em;height:1.1em;font-size:0.7em;cursor:pointer;'
        +'display:flex;align-items:center;justify-content:center;line-height:1;padding:0;">×</button>';
      html+='</div>';
    });
    html+='</div>';
  }
  html+='</div>';
  return html;
}

window.dspCanvasSave=function(el){
  var sidx=parseInt(el.getAttribute('data-sidx'));
  var field=el.getAttribute('data-field');
  var cidx=el.getAttribute('data-cidx');
  var val=el.innerText||el.textContent||'';
  if(!G.pptData||!G.pptData.slides[sidx])return;
  var s=G.pptData.slides[sidx];
  if(field==='content'&&cidx!==null){if(!s.content)s.content=[];s.content[parseInt(cidx)]=val;}
  else{s[field]=val;}
  var thumbs=q('dsp-ppt-thumbs');
  if(thumbs&&thumbs.children[sidx]){
    var designEl=q('dsp-design');
    var designVal=(designEl&&designEl.value)?designEl.value:'navy-pro';
    var theme=THEMES[designVal]||THEMES['navy-pro'];
    var thumbDiv=thumbs.children[sidx].querySelector('.dsp-thumb-box');
    if(thumbDiv){thumbDiv.style.cssText='background:'+theme.bg+';font-size:3.5px;';thumbDiv.innerHTML=renderPreviewSlide(s,theme,sidx+1);}
  }
  if(field==='title'&&sidx===0){var et=q('dsp-etitle');if(et)et.textContent=val;}
};

var G_imgModalSlideIdx=-1;
window.dspImgModalOpen=function(sidx){
  G_imgModalSlideIdx=sidx;
  var modal=q('dsp-img-modal');if(modal)modal.classList.add('dsp-open');
  var prompt=q('dsp-img-prompt');
  if(prompt){var s=G.pptData&&G.pptData.slides[sidx];prompt.value=(s&&s.image_query)||'';prompt.focus();}
};
window.dspImgModalClose=function(){var modal=q('dsp-img-modal');if(modal)modal.classList.remove('dsp-open');};
window.dspImgModalGenerate=function(){
  var prompt=q('dsp-img-prompt');if(!prompt||!prompt.value.trim())return;
  var query=prompt.value.trim();
  var btn=q('dsp-img-modal-btn');if(btn){btn.textContent='Generating...';btn.disabled=true;}
  fetch(IMGURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},body:JSON.stringify({query:query})})
  .then(function(r){return r.json();})
  .then(function(d){
    if(btn){btn.textContent='✨ Generate';btn.disabled=false;}
    if(d&&d.success&&d.image){
      if(G.pptData&&G.pptData.slides[G_imgModalSlideIdx]){G.pptData.slides[G_imgModalSlideIdx].image_data=d.image;G.pptData.slides[G_imgModalSlideIdx].image_query=query;}
      dspImgModalClose();dspSelectThumb(G_imgModalSlideIdx);dspRenderThumbs();dspSelectThumb(G_imgModalSlideIdx);
      dspToast('Image updated successfully!','success');
    } else {dspToast((d&&d.error?d.error:'Image generation failed')+' — try a shorter prompt.','error');}
  })
  .catch(function(){if(btn){btn.textContent='✨ Generate';btn.disabled=false;}dspToast('Network error.','error');});
};

function renderPreviewSlide(s,theme,num){
  var numHtml='<div class="dsp-pv-num" style="color:'+theme.body+';">'+num+'</div>';
  switch(s.type){
    case 'title':        return numHtml+pv_title(s,theme);
    case 'two_column':   return numHtml+pv_two_column(s,theme);
    case 'image_text':   return numHtml+pv_image_text(s,theme);
    case 'feature_grid': return numHtml+pv_feature_grid(s,theme);
    case 'five_point':   return numHtml+pv_multipoint(s,theme,5);
    case 'six_point':    return numHtml+pv_multipoint(s,theme,6);
    case 'seven_point':  return numHtml+pv_multipoint(s,theme,7);
    case 'eight_point':  return numHtml+pv_multipoint(s,theme,8);
    case 'stats':        return numHtml+pv_stats(s,theme);
    case 'timeline':     return numHtml+pv_timeline(s,theme);
    case 'quote':        return numHtml+pv_quote(s,theme);
    case 'bullet': default: return numHtml+pv_bullet(s,theme);
  }
}

function pv_title(s,t){
  var hasImg=!!s.image_data;
  var sub=s.subtitle?'<div style="font-size:0.85em;color:'+(hasImg?'#e5e7eb':t.body)+';margin-top:0.6em;">'+escH(s.subtitle)+'</div>':'';
  var extras=(s.content||[]).filter(function(c){return String(c).trim();}).map(function(c){
    return '<div style="font-size:0.65em;color:'+(hasImg?'#d1d5db':t.body)+';margin-top:0.35em;">'+escH(c)+'</div>';
  }).join('');
  var titleColor=hasImg?'#fff':t.title;
  var inner='<div style="font-size:2.2em;font-weight:800;color:'+titleColor+';line-height:1.15;">'+escH(s.title||'')+'</div>'+
    '<div style="width:9%;height:3px;background:'+t.accent+';margin:0.8em 0;"></div>'+sub+extras;
    if(hasImg){
    return '<div style="position:absolute;inset:0;background:url('+s.image_data+') center/cover no-repeat;"></div>'+
      '<div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.3),rgba(0,0,0,.6));"></div>'+
      '<div class="dsp-pv-cnt" style="flex-direction:column;justify-content:center;align-items:center;text-align:center;">'+inner+'</div>';
  }
  return '<div class="dsp-pv-cnt" style="flex-direction:column;justify-content:center;align-items:center;text-align:center;">'+inner+'</div>';
}

function pv_bullet(s,t){
  var rows=(s.content||[]).slice(0,6).map(function(c,i){
    var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
    var symbols=['💡','📊','🎯','✅','🚀','⭐'];var sym=symbols[i%symbols.length];
    return '<div style="display:flex;align-items:flex-start;gap:0.5em;margin-bottom:0.45em;">'+
      '<div style="width:1.2em;height:1.2em;border-radius:50%;background:'+t.accent+'30;color:'+t.accent+';display:flex;align-items:center;justify-content:center;font-size:0.65em;font-weight:700;flex-shrink:0;margin-top:0.1em;">'+sym+'</div>'+
      '<div style="flex:1;">'+(h?'<div style="font-size:0.75em;font-weight:800;color:'+t.title+';line-height:1.2;">'+escH(h)+'</div>':'')+(d?'<div style="font-size:0.65em;color:'+t.body+';line-height:1.3;margin-top:0.1em;">'+escH(d)+'</div>':'')+'</div></div>';
  }).join('');
  var sub=s.subtitle?'<div style="font-size:0.7em;color:'+t.accent+';margin-bottom:0.5em;font-weight:600;">'+escH(s.subtitle)+'</div>':'';
  return '<div style="position:absolute;top:0;left:0;right:0;height:1.5%;background:'+t.accent+';"></div>'+
    '<div class="dsp-pv-cnt" style="flex-direction:column;">'+
    '<div style="font-size:1.4em;font-weight:800;color:'+t.title+';margin-bottom:0.2em;">'+escH(s.title||'')+'</div>'+
    sub+'<div style="flex:1;overflow:hidden;">'+rows+'</div></div>';
}

function pv_two_column(s,t){
  var c=(s.content||[]).slice(0,2);while(c.length<2)c.push('');
  var symbols=['💡','📊'];
  var cards=c.map(function(x,i){
    var colon=String(x).indexOf(':');var h=colon>0?String(x).slice(0,colon).trim():'';var d=colon>0?String(x).slice(colon+1).trim():String(x);
    return '<div style="flex:1;background:'+t.accent+'15;border-radius:8px;padding:8%;display:flex;flex-direction:column;align-items:center;text-align:center;gap:0.4em;">'+
      '<div style="width:1.8em;height:1.8em;border-radius:50%;background:'+t.accent+'40;color:'+t.accent+';display:flex;align-items:center;justify-content:center;font-size:1em;font-weight:700;">'+symbols[i]+'</div>'+
      (h?'<div style="font-size:0.85em;font-weight:800;color:'+t.title+';">'+escH(h)+'</div>':'')+
      (d?'<div style="font-size:0.65em;color:'+t.body+';line-height:1.3;">'+escH(d)+'</div>':'')+
    '</div>';
  }).join('');
  var sub=s.subtitle?'<div style="font-size:0.7em;color:'+t.accent+';margin-bottom:0.4em;font-weight:600;">'+escH(s.subtitle)+'</div>':'';
  return '<div class="dsp-pv-cnt" style="flex-direction:column;">'+
    '<div style="font-size:1.4em;font-weight:800;color:'+t.title+';margin-bottom:0.2em;">'+escH(s.title||'')+'</div>'+
    sub+'<div style="flex:1;display:flex;gap:4%;align-items:stretch;">'+cards+'</div></div>';
}

function pv_image_text(s,t){
  var ac=t.accent.replace('#','');
  var r=parseInt(ac.substring(0,2),16),g=parseInt(ac.substring(2,4),16),b=parseInt(ac.substring(4,6),16);
  var tint='rgba('+r+','+g+','+b+',0.18)';
  var imgPanel;
  if(s.image_data){imgPanel='<div style="flex:1;background:'+tint+';border-radius:8px;overflow:hidden;background-image:url('+s.image_data+');background-size:cover;background-position:center;"></div>';}
  else if(s.image_loading){imgPanel='<div style="flex:1;background:'+tint+';border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:'+t.body+';font-size:0.7em;font-weight:700;"><div style="width:24px;height:24px;border:2px solid '+t.accent+';border-top-color:transparent;border-radius:50%;animation:dspSpin 1s linear infinite;margin-bottom:0.6em;"></div>GENERATING...</div>';}
  else if(s.image_failed){imgPanel='<div style="flex:1;background:'+tint+';border-radius:8px;display:flex;align-items:center;justify-content:center;color:'+t.body+';font-size:0.7em;font-weight:700;opacity:0.5;">⚠ IMAGE FAILED</div>';}
  else{imgPanel='<div style="flex:1;background:'+tint+';border-radius:8px;display:flex;align-items:center;justify-content:center;color:'+t.body+';font-size:0.7em;font-weight:700;">🖼 IMAGE</div>';}
  var rows=(s.content||[]).slice(0,4).map(function(c,i){
    var colon=String(c).indexOf(':');var h=colon>0?String(c).slice(0,colon).trim():'';var d=colon>0?String(c).slice(colon+1).trim():String(c);
    var syms=['💡','📊','🎯','✅'];
    return '<div style="display:flex;align-items:flex-start;gap:0.4em;margin-bottom:0.35em;">'+
      '<div style="width:1em;height:1em;border-radius:50%;background:'+t.accent+'30;color:'+t.accent+';display:flex;align-items:center;justify-content:center;font-size:0.6em;flex-shrink:0;margin-top:0.1em;">'+syms[i%4]+'</div>'+
      '<div style="flex:1;">'+(h?'<div style="font-size:0.7em;font-weight:800;color:'+t.title+';line-height:1.1;">'+escH(h)+'</div>':'')+(d?'<div style="font-size:0.6em;color:'+t.body+';line-height:1.2;">'+escH(d)+'</div>':'')+'</div></div>';
  }).join('');
  var sub2=s.subtitle?'<div style="font-size:0.65em;color:'+t.accent+';margin-bottom:0.35em;font-weight:600;">'+escH(s.subtitle)+'</div>':'';
  return '<div class="dsp-pv-cnt" style="flex-direction:column;">'+
    '<div style="font-size:1.4em;font-weight:800;color:'+t.title+';margin-bottom:0.2em;">'+escH(s.title||'')+'</div>'+
    sub2+'<div style="display:flex;gap:4%;flex:1;"><div style="flex:1;overflow:hidden;">'+rows+'</div>'+imgPanel+'</div></div>';
}

function pv_feature_grid(s,t){
  var items=(s.content||[]).slice(0,4);while(items.length<4)items.push('');
  var ac=t.accent.replace('#','');
  var r=parseInt(ac.substring(0,2),16),g=parseInt(ac.substring(2,4),16),b=parseInt(ac.substring(4,6),16);
  var tint='rgba('+r+','+g+','+b+',0.12)';
  var SYMBOLS={rocket:'🚀',chart:'📊',users:'👥',target:'🎯',lightbulb:'💡',shield:'🛡',globe:'🌐',star:'⭐',check:'✅',flag:'🏁',trending:'📈',gear:'⚙',clock:'⏰',heart:'❤',home:'🏠',mail:'📧',phone:'📱',search:'🔍',settings:'🔧',user:'👤',zap:'⚡',award:'🏆',book:'📖',briefcase:'💼',calendar:'📅',sparkles:'✨',sun:'☀',warning:'⚠',play:'▶',plus:'➕'};
  var defaults=['lightbulb','chart','users','target'];
  var slideIcon=(s.icon||'').trim();var cardIcons=(s.icons&&s.icons.length)?s.icons:[];
  var cards=items.map(function(it,idx){
    var parts=String(it).split(':');var h=(parts[0]||'').trim();var d=parts.slice(1).join(':').trim();
    var ico=cardIcons[idx]&&SYMBOLS[cardIcons[idx]]?cardIcons[idx]:(slideIcon&&SYMBOLS[slideIcon]?slideIcon:defaults[idx]);
    var sym=SYMBOLS[ico]||SYMBOLS.star;
    return '<div style="background:'+tint+';border-radius:8px;padding:8% 6%;display:flex;flex-direction:column;gap:0.4em;">'+
      '<div style="display:flex;align-items:center;gap:0.5em;">'+
      '<div style="width:1.5em;height:1.5em;border-radius:50%;background:'+t.accent+';color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.95em;font-weight:700;flex-shrink:0;">'+sym+'</div>'+
      '<div style="font-size:0.85em;font-weight:800;color:'+t.title+';">'+escH(h)+'</div></div>'+
      '<div style="font-size:0.65em;color:'+t.body+';line-height:1.3;">'+escH(d)+'</div></div>';
  }).join('');
  return '<div class="dsp-pv-cnt" style="flex-direction:column;">'+
    '<div style="font-size:1.5em;font-weight:800;color:'+t.title+';margin-bottom:0.6em;">'+escH(s.title||'')+'</div>'+
    '<div style="flex:1;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:3%;">'+cards+'</div></div>';
}

function pv_multipoint(s,t,count){
  var items=(s.content||[]).slice(0,count);while(items.length<count)items.push('');
  var ac=t.accent.replace('#','');
  var r=parseInt(ac.substring(0,2),16),g=parseInt(ac.substring(2,4),16),b=parseInt(ac.substring(4,6),16);
  var tint='rgba('+r+','+g+','+b+',0.12)';
  var cols=count<=6?3:4;
  var cards=items.map(function(it,idx){
    var parts=String(it).split(':');var h=(parts[0]||'').trim();var d=parts.slice(1).join(':').trim();
    return '<div style="background:'+tint+';border-radius:8px;padding:6% 5%;display:flex;flex-direction:column;gap:0.3em;">'+
      '<div style="display:flex;align-items:center;gap:0.4em;">'+
      '<div style="width:1.3em;height:1.3em;border-radius:50%;background:'+t.accent+';color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75em;font-weight:700;flex-shrink:0;">'+(idx+1)+'</div>'+
      '<div style="font-size:0.7em;font-weight:800;color:'+t.title+';">'+escH(h)+'</div></div>'+
      '<div style="font-size:0.55em;color:'+t.body+';line-height:1.3;">'+escH(d)+'</div></div>';
  }).join('');
  return '<div class="dsp-pv-cnt" style="flex-direction:column;">'+
    '<div style="font-size:1.4em;font-weight:800;color:'+t.title+';margin-bottom:0.5em;">'+escH(s.title||'')+'</div>'+
    '<div style="flex:1;display:grid;grid-template-columns:repeat('+cols+',1fr);gap:2.5%;align-content:start;">'+cards+'</div></div>';
}

function pv_stats(s,t){
  var items=(s.content||[]).slice(0,3);while(items.length<3)items.push('');
  var stats=items.map(function(it){
    var parts=String(it).split(':');var num=(parts[0]||'').trim();var lbl=parts.slice(1).join(':').trim();
    var nLen=num.length;var sz=nLen<=3?'2.6em':nLen<=5?'2.0em':nLen<=7?'1.6em':'1.3em';
    return '<div style="flex:1;display:flex;flex-direction:column;align-items:center;text-align:center;">'+
      '<div style="font-size:'+sz+';font-weight:800;color:'+t.accent+';line-height:1;margin-bottom:0.3em;">'+escH(num)+'</div>'+
      '<div style="font-size:0.65em;color:'+t.body+';line-height:1.3;">'+escH(lbl)+'</div></div>';
  }).join('');
  return '<div class="dsp-pv-cnt" style="flex-direction:column;align-items:center;">'+
    '<div style="font-size:1.4em;font-weight:800;color:'+t.title+';margin-bottom:0.8em;text-align:center;">'+escH(s.title||'')+'</div>'+
    '<div style="display:flex;gap:3%;flex:1;align-items:center;width:100%;">'+stats+'</div></div>';
}

function pv_timeline(s,t){
  var items=(s.content||[]).slice(0,5);if(!items.length)items=[''];
  var html=items.map(function(it){
    var parts=String(it).split(':');var step=(parts[0]||'').trim();var desc=parts.slice(1).join(':').trim();
    return '<div style="display:flex;align-items:flex-start;gap:0.5em;margin-bottom:0.55em;">'+
      '<div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;">'+
      '<div style="width:0.75em;height:0.75em;border-radius:50%;background:'+t.accent+';margin-top:0.15em;"></div>'+
      '<div style="width:2px;flex:1;background:'+t.accent+'40;min-height:0.8em;margin-top:2px;"></div></div>'+
      '<div style="flex:1;"><div style="font-size:0.72em;font-weight:800;color:'+t.accent+';line-height:1.2;">'+escH(step)+'</div>'+
      (desc?'<div style="font-size:0.6em;color:'+t.body+';line-height:1.3;margin-top:0.1em;">'+escH(desc)+'</div>':'')+'</div></div>';
  }).join('');
  return '<div class="dsp-pv-cnt" style="flex-direction:column;">'+
    '<div style="font-size:1.4em;font-weight:800;color:'+t.title+';margin-bottom:0.15em;">'+escH(s.title||'')+'</div>'+
    (s.subtitle?'<div style="font-size:0.65em;color:'+t.accent+';margin-bottom:0.5em;font-weight:600;">'+escH(s.subtitle)+'</div>':'')+
    '<div style="flex:1;overflow:hidden;padding-top:0.3em;">'+html+'</div></div>';
}

function pv_quote(s,t){
  var attr=(s.content&&s.content[0])?s.content[0]:'';
  return '<div class="dsp-pv-cnt" style="flex-direction:column;justify-content:center;align-items:center;text-align:center;">'+
    '<div style="font-size:3em;font-weight:800;color:'+t.accent+';line-height:0.7;margin-bottom:0.3em;">"</div>'+
    '<div style="font-size:1.15em;font-style:italic;color:'+t.title+';line-height:1.4;margin-bottom:0.8em;">'+escH(s.title||'')+'</div>'+
    (attr?'<div style="font-size:0.75em;font-weight:700;color:'+t.accent+';">— '+escH(attr)+'</div>':'')+
    '</div>';
}
function renderThumbSlide(s,theme,num){
  try{
    var keep=G_editSlideIdx;
    var html=renderEditSlide(s,theme,num-1);
    G_editSlideIdx=keep;
    html=html.replace(/contenteditable="true"/g,'contenteditable="false"');
    html=html.replace(/ onclick="[^"]*"/g,'');
    html=html.replace(/ onblur="[^"]*"/g,'');
    html=html.replace(/ onkeydown="[^"]*"/g,'');
    return '<div style="position:absolute;inset:0;pointer-events:none;">'+html+'</div>';
  }catch(e){
    return renderPreviewSlide(s,theme,num);
  }
}

function bprog(pct){var el=q('dsp-bprog');if(el)el.style.width=pct+'%';}
function bmsgSet(txt){var el=q('dsp-build-msg');if(el)el.textContent=txt;}

/* Shrink oversized inline slide images before they are sent to the server.
   The generator can return 5000px PNGs (40MB+ once base64 encoded), which
   makes Save crawl and makes the PPTX build time out. Cap the long edge at
   1600px and re-encode as JPEG. */
window.dspShrinkImages=function(slides){
  var MAXPX=1600, LIMIT=400000;
  var jobs=(slides||[]).map(function(s){
    return new Promise(function(res){
      var d=s&&s.image_data;
      if(!d||typeof d!=='string'||d.length<LIMIT||d.indexOf('data:image')!==0){res();return;}
      var im=new Image();
      im.onload=function(){
        try{
          var w=im.naturalWidth,h=im.naturalHeight;
          var sc=Math.min(1,MAXPX/Math.max(w,h));
          var cw=Math.max(1,Math.round(w*sc)),ch=Math.max(1,Math.round(h*sc));
          var cv=document.createElement('canvas');cv.width=cw;cv.height=ch;
          cv.getContext('2d').drawImage(im,0,0,cw,ch);
          var out=cv.toDataURL('image/jpeg',0.82);
          if(out&&out.length<d.length)s.image_data=out;
        }catch(e){}
        res();
      };
      im.onerror=function(){res();};
      im.src=d;
    });
  });
  return Promise.all(jobs);
};
window.dspBuild=function(){
  if(G.building)return;
  if(!G.pptData||!G.pptData.slides||!G.pptData.slides.length){alert('No slides to build.');return;}
  var stillLoading=G.pptData.slides.filter(function(s){return s.type==='image_text'&&s.image_loading;}).length;
  if(stillLoading>0){if(!confirm('Still generating '+stillLoading+' image(s). Build now without them?'))return;}
  G.building=true;
  // Show instant feedback the moment the button is clicked
  dspDlPopup('downloading');
  var overlay=q('dsp-build-overlay');
  if(overlay){overlay.style.display='flex';}
  bprog(5);bmsgSet('Building PPTX...');
  var template=q('dsp-design').value||'navy-pro';
  var layout=q('dsp-layout')?q('dsp-layout').value:'16:9';
  var slides=G.pptData.slides;
  var total=slides.length;
  var designVal=(q('dsp-design')&&q('dsp-design').value)?q('dsp-design').value:'navy-pro';
  var theme=THEMES[designVal]||THEMES['navy-pro'];
  var thumbsCol=q('dsp-build-thumbs-list')||q('dsp-build-thumbs-col');
  var mainSlide=q('dsp-build-main-slide');
  if(thumbsCol)thumbsCol.innerHTML='';
  if(mainSlide){mainSlide.style.background=theme.bg;mainSlide.innerHTML=renderPreviewSlide(slides[0],theme,1);}
  slides.forEach(function(s,i){
    var thumb=document.createElement('div');
    thumb.className='dsp-build-thumb';
    thumb.style.background=theme.bg;
    var numSpan=document.createElement('span');
    numSpan.className='dsp-build-thumb-num';
    numSpan.textContent=i+1;
    thumb.appendChild(numSpan);
    if(thumbsCol)thumbsCol.appendChild(thumb);
    setTimeout(function(){
      thumb.innerHTML=renderPreviewSlide(s,theme,i+1)+'<span class="dsp-build-thumb-num">'+(i+1)+'</span>';
      thumb.classList.add('dsp-bt-ready');
      if(mainSlide)mainSlide.innerHTML=renderPreviewSlide(s,theme,i+1);
      bprog(Math.round(((i+1)/total)*85)+5);
      bmsgSet('Assembling slide '+(i+1)+' of '+total+'...');
      thumb.onclick=function(){if(mainSlide)mainSlide.innerHTML=renderPreviewSlide(s,theme,i+1);};
    },i*200);
  });
  setTimeout(function(){
    bprog(95);bmsgSet('Finalizing PPTX file... this can take up to 30 seconds for slides with images.');
    // Keep cycling reassuring messages while the server processes images/icons,
    // since this step has no per-slide progress signal from the backend.
    var _waitMsgs=['Still working — embedding images...','Almost there — packaging your file...','Optimizing images for PowerPoint...','Just a little longer...'];
    var _waitIdx=0;
    var _waitTimer=setInterval(function(){
      bmsgSet(_waitMsgs[_waitIdx % _waitMsgs.length]);
      _waitIdx++;
    },4000);
    console.log('DS12B sending template:', template, 'dsp-design value:', q('dsp-design')?q('dsp-design').value:'NOT FOUND');
    dspShrinkImages(G.pptData.slides).then(function(){
    return fetch(BLDURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
      body:JSON.stringify({title:G.pptTitle,template:template,layout:layout,slides:G.pptData.slides,
      /* Per-slide layout choices. The PPTX endpoint expects slide_variants
         ({slide_0:'v2',...}); without it every slide exported as v1, so the
         layout picker never affected the download. Snippet 17 mirrors its
         choices into sessionStorage['dspVPC']. */
      slide_variants:(function(){try{return JSON.parse(sessionStorage.getItem('dspVPC')||'{}');}catch(e){return {};}})()})
    });
    })
    .then(function(r){if(r.status===404)throw new Error('build-pptx 404');if(!r.ok)throw new Error('Server '+r.status);return r.json();})
    .then(function(d){
      clearInterval(_waitTimer);
      G.building=false;
      if(!d||!d.success){
        if(overlay)overlay.style.display='none';
        throw new Error(d&&d.error?d.error:'Build failed');
      }
      bprog(100);bmsgSet('Done!');
      if(overlay)overlay.style.display='none';
      // Trigger download
      var dlUrl=d.url+(d.url.indexOf('?')>-1?'&':'?')+'_wpnonce='+NONCE;
      var a=document.createElement('a');
      a.href=dlUrl;
      a.setAttribute('download',d.filename||'presentation.pptx');
      a.style.display='none';
      document.body.appendChild(a);
      a.click();
      setTimeout(function(){if(a.parentNode)a.parentNode.removeChild(a);},2000);
      var dl=q('dsp-dllink');
      if(dl){dl.href=dlUrl;dl.setAttribute('download',d.filename||'presentation.pptx');}
      // After 3s show downloaded popup
      setTimeout(function(){dspDlPopup('done');},3000);
    })
    .catch(function(e){
      clearInterval(_waitTimer);
      G.building=false;
      if(overlay)overlay.style.display='none';
      var _dlp=q('dsp-dl-popup');if(_dlp&&_dlp.parentNode)_dlp.parentNode.removeChild(_dlp);
      dspEditorOpen();
      alert('Build error: '+e.message);
    });
  },(total*200)+500);
};

window.dspDlPopup=function(state){
  var old=q('dsp-dl-popup');if(old&&old.parentNode)old.parentNode.removeChild(old);
  var overlay=document.createElement('div');
  overlay.id='dsp-dl-popup';
  var isDl=state==='downloading';
  overlay.style.cssText='position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.55);'
    +'display:flex;align-items:center;justify-content:center;';
  var card=document.createElement('div');
  card.style.cssText='background:#fdf9f3;border-radius:18px;padding:36px 32px;max-width:340px;width:90%;'
    +'text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.4);font-family:-apple-system,BlinkMacSystemFont,sans-serif;';
  var iconHtml=isDl
    ? '<div style="width:54px;height:54px;margin:0 auto 16px;border:4px solid #fde7c9;border-top-color:#d97706;border-radius:50%;animation:dspSpin .8s linear infinite;"></div>'
    : '<div style="font-size:52px;margin:0 auto 12px;line-height:1;">🎉</div>';
  var textHtml=isDl
    ? '<h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:#1a1a1a;">Downloading PPTX...</h3>'
      +'<p style="margin:0 0 0;font-size:13px;color:#6b6b6b;line-height:1.5;">Preparing your file, this won\'t take long.</p>'
    : '<h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:#1a1a1a;">Download Complete!</h3>'
      +'<p style="margin:0 0 20px;font-size:13px;color:#6b6b6b;line-height:1.5;">Check your downloads folder.</p>'
      +'<button id="dsp-dl-okbtn" style="width:100%;padding:13px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);'
      +'color:#fff;font-size:14px;font-weight:800;border:none;cursor:pointer;font-family:inherit;">AWESOME!</button>';
  card.innerHTML=iconHtml+textHtml;
  overlay.appendChild(card);
  document.body.appendChild(overlay);
  if(!isDl){
    var okBtn=document.getElementById('dsp-dl-okbtn');
    function closeIt(){if(overlay.parentNode)overlay.parentNode.removeChild(overlay);}
    if(okBtn)okBtn.onclick=closeIt;
    overlay.onclick=function(e){if(e.target===overlay)closeIt();};
    setTimeout(closeIt,6000);
  }
};
/* ===== SAVE DRAFTS ===== */
var SAVEURL=PURL.replace('/generate-ppt','/save-ppt');
var LISTURL=PURL.replace('/generate-ppt','/my-ppts');
var LOADURL=PURL.replace('/generate-ppt','/load-ppt');
var DELURL=PURL.replace('/generate-ppt','/delete-ppt');

window.dspSaveDraft=function(){
  if(!G.pptData){dspToast('Nothing to save yet','error');return;}
  var btn=q('dsp-savebtn');
  if(btn){btn.innerHTML='&#128190; Save';btn.disabled=false;}
  dspShrinkImages(G.pptData.slides).then(function(){
  return fetch(SAVEURL,{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({ppt_id:G.pptId||'',title:G.pptTitle||'Presentation',ppt_data:G.pptData,design:(q('dsp-design')&&q('dsp-design').value)||'dark-modern'})
  });
  })
  .then(function(r){return r.json();})
  .then(function(d){
    if(btn){btn.innerHTML='&#128190; Save';btn.disabled=false;}
    if(d&&d.success){
      if(d.ppt_id)G.pptId=d.ppt_id;
      dspToast('Draft saved!','success');
      dspRenderMyPPTs();
    } else {
      dspToast((d&&d.error)||'Save failed','error');
    }
  })
  .catch(function(){
    if(btn){btn.textContent='&#128190; Save';btn.disabled=false;}
    dspToast('Network error','error');
  });
};

window.dspLoadDraft=function(id){
  dspToast('Loading...','success');
  fetch(LOADURL,{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({ppt_id:id})
  })
  .then(function(r){return r.json();})
  .then(function(d){
    if(d&&d.success&&d.data&&d.data.slides){
      G.pptId=id;
      var designEl=q('dsp-design');
      if(designEl)designEl.value=d.design||'navy-pro';
      if(typeof window.dspApplyThemeFont==='function')window.dspApplyThemeFont(d.design||'navy-pro');
      buildEditor(d.data);
    } else {
      dspToast('Could not load draft','error');
    }
  })
  .catch(function(){dspToast('Network error','error');});
};

/* ===== My Presentations — paged 6 at a time (16.2) ===== */
var DSP_PAGE=6;
var dspPptState={offset:0,total:0,loading:false};

window.dspDeleteDraft=function(id,ev){
  if(ev){ev.stopPropagation();ev.preventDefault();}
  if(!confirm('Delete this presentation permanently?'))return;

  /* Remove the card straight away. The round trip can take several seconds on
     a busy server, and leaving the card sitting there made delete look broken. */
  var card=document.getElementById('dsp-card-'+id);
  var placeholder=null;
  if(card){
    placeholder=card.nextSibling;
    card.style.transition='opacity .15s';
    card.style.opacity='.35';
    card.style.pointerEvents='none';
  }

  /* 16.3: never let this hang silently — if nothing comes back in 30s, say so
     rather than leaving the card sitting there looking ignored. */
  var ctrl=(typeof AbortController!=='undefined')?new AbortController():null;
  var timedOut=false;
  var killer=setTimeout(function(){timedOut=true;if(ctrl)ctrl.abort();},30000);

  fetch(DELURL,{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    credentials:'same-origin',
    signal:ctrl?ctrl.signal:undefined,
    body:JSON.stringify({ppt_id:id})
  })
  .then(function(r){
    clearTimeout(killer);
    if(!r.ok)throw new Error('HTTP '+r.status);
    return r.json();
  })
  .then(function(d){
    if(!d||d.success===false)throw new Error((d&&d.error)||'Delete failed');
    if(card&&card.parentNode)card.parentNode.removeChild(card);
    dspPptState.total=Math.max(0,dspPptState.total-1);
    dspPptState.offset=Math.max(0,dspPptState.offset-1);
    dspToast('Presentation deleted','success');
    dspUpdatePptFooter();
    var grid=document.getElementById('dsp-ppt-grid');
    if(grid&&!grid.children.length)dspRenderMyPPTs();
  })
  .catch(function(err){
    clearTimeout(killer);
    /* Put the card back and say what went wrong, instead of failing silently. */
    if(card){card.style.opacity='1';card.style.pointerEvents='';}
    var msg=timedOut
      ? 'the server did not respond in 30s. It may still have deleted — reload to check.'
      : (err&&err.message?err.message:'please try again');
    dspToast('Could not delete: '+msg,'error');
  });
};

function dspPptCardHtml(p){
  var dt=new Date(p.updated*1000).toLocaleDateString();
  var theme=THEMES[p.design]||THEMES['dark-modern'];
  var thumbHtml='';
  if(p.first_slide){
    try{thumbHtml=renderPreviewSlide(p.first_slide,theme,1);}catch(e){thumbHtml='';}
  }
  var safeTitle=String(p.title==null?'':p.title).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  return '<div id="dsp-card-'+p.id+'" onclick="dspLoadDraft(\''+p.id+'\')" style="background:#111;border:1px solid #222;border-radius:10px;overflow:hidden;cursor:pointer;transition:border-color .2s;" onmouseover="this.style.borderColor=\'#3b82f6\'" onmouseout="this.style.borderColor=\'#222\'">'
    +'<div style="width:100%;aspect-ratio:16/9;background:'+theme.bg+';overflow:hidden;position:relative;font-size:3.5px;pointer-events:none;">'
    +thumbHtml
    +'</div>'
    +'<div style="padding:10px 12px;display:flex;align-items:center;justify-content:space-between;">'
      +'<div style="overflow:hidden;">'
        +'<div style="color:#e5e7eb;font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">'+safeTitle+'</div>'
        +'<div style="color:#6b7280;font-size:11px;margin-top:2px;">'+p.slides_count+' slides &middot; '+dt+'</div>'
      +'</div>'
      +'<span onclick="dspDeleteDraft(\''+p.id+'\',event)" title="Delete" style="color:#4b5563;font-size:18px;font-weight:700;cursor:pointer;padding:4px 6px;border-radius:4px;flex-shrink:0;" onmouseover="this.style.color=\'#ef4444\'" onmouseout="this.style.color=\'#4b5563\'">&times;</span>'
    +'</div>'
  +'</div>';
}

function dspUpdatePptFooter(){
  var foot=document.getElementById('dsp-ppt-foot');
  if(!foot)return;
  var shown=document.querySelectorAll('#dsp-ppt-grid > div').length;
  if(shown>=dspPptState.total){
    foot.innerHTML=dspPptState.total
      ? '<div style="color:#4b5563;font-size:11px;padding:14px 0;">All '+dspPptState.total+' presentations shown</div>'
      : '';
    return;
  }
  foot.innerHTML='<button id="dsp-ppt-more" onclick="dspLoadMorePPTs()" style="background:#191c25;color:#60a5fa;border:1px solid #262b38;padding:11px 30px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s;" onmouseover="this.style.background=\'#27395c\'" onmouseout="this.style.background=\'#191c25\'">Load more ('+(dspPptState.total-shown)+' left)</button>';
}

window.dspLoadMorePPTs=function(){
  if(dspPptState.loading)return;
  dspPptState.loading=true;
  var btn=document.getElementById('dsp-ppt-more');
  if(btn){btn.disabled=true;btn.textContent='Loading…';}
  fetch(LISTURL+'?limit='+DSP_PAGE+'&offset='+dspPptState.offset,{headers:{'X-WP-Nonce':NONCE},credentials:'same-origin'})
  .then(function(r){return r.json();})
  .then(function(d){
    dspPptState.loading=false;
    if(!d||!d.drafts)throw new Error('bad response');
    dspPptState.total=d.total||0;
    dspPptState.offset+=d.drafts.length;
    var grid=document.getElementById('dsp-ppt-grid');
    if(grid)d.drafts.forEach(function(p){grid.insertAdjacentHTML('beforeend',dspPptCardHtml(p));});
    dspUpdatePptFooter();
  })
  .catch(function(){
    dspPptState.loading=false;
    var f=document.getElementById('dsp-ppt-foot');
    if(f)f.innerHTML='<div style="color:#ef4444;font-size:12px;padding:14px 0;">Could not load more — <span style="text-decoration:underline;cursor:pointer;" onclick="dspLoadMorePPTs()">retry</span></div>';
  });
};

window.dspRenderMyPPTs=function(retries){
  retries=retries||0;
  var homeEl=q('dsp-home');
  if(!homeEl){
    if(retries<20)setTimeout(function(){dspRenderMyPPTs(retries+1);},250);
    return;
  }
  dspPptState={offset:0,total:0,loading:false};
  var old=q('dsp-my-ppts-section');
  if(old&&old.parentNode)old.parentNode.removeChild(old);
  var sec=document.createElement('div');
  sec.id='dsp-my-ppts-section';
  sec.style.cssText='width:100%;max-width:900px;margin-top:32px;';
  sec.innerHTML='<div style="font-size:10px;font-weight:700;letter-spacing:.8px;color:#6b7280;text-transform:uppercase;margin-bottom:12px;">\ud83d\udcc2 My Presentations</div>'
    +'<div style="color:#6b7280;font-size:13px;padding:20px 0;">Loading your presentations…</div>';
  homeEl.appendChild(sec);

  fetch(LISTURL+'?limit='+DSP_PAGE+'&offset=0',{headers:{'X-WP-Nonce':NONCE},credentials:'same-origin'})
  .then(function(r){return r.json();})
  .then(function(d){
    var cur=q('dsp-my-ppts-section');
    if(!cur)return;
    if(!d||!d.drafts||!d.drafts.length){
      cur.innerHTML='<div style="font-size:10px;font-weight:700;letter-spacing:.8px;color:#6b7280;text-transform:uppercase;margin-bottom:12px;">\ud83d\udcc2 My Presentations</div>'
        +'<div style="color:#6b7280;font-size:13px;padding:20px 0;">No saved presentations yet — generate one above to see it here.</div>';
      return;
    }
    dspPptState.total=d.total||d.drafts.length;
    dspPptState.offset=d.drafts.length;
    cur.innerHTML='<div style="font-size:10px;font-weight:700;letter-spacing:.8px;color:#6b7280;text-transform:uppercase;margin-bottom:12px;">\ud83d\udcc2 My Presentations</div>'
      +'<div id="dsp-ppt-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;">'
      +d.drafts.map(dspPptCardHtml).join('')
      +'</div>'
      +'<div id="dsp-ppt-foot" style="display:flex;justify-content:center;padding:18px 0 4px;"></div>';
    dspUpdatePptFooter();
  })
  .catch(function(){
    var cur=q('dsp-my-ppts-section');
    if(!cur)return;
    cur.innerHTML='<div style="font-size:10px;font-weight:700;letter-spacing:.8px;color:#6b7280;text-transform:uppercase;margin-bottom:12px;">\ud83d\udcc2 My Presentations</div>'
      +'<div style="color:#ef4444;font-size:13px;padding:20px 0;">Could not load your presentations — <span style="text-decoration:underline;cursor:pointer;" onclick="dspRenderMyPPTs()">retry</span></div>';
  });
};


dspRenderMyPPTs();
/* ===== END SAVE DRAFTS ===== */
/* === Auto-load draft from My Account === */
(function(){
    var _id=localStorage.getItem('dsp_load_draft');
    if(!_id)return;
    // Only auto-load if we're on the AI Presentation section, not AI Images
    var pptSection=document.getElementById('dsp-wrap')||document.getElementById('dsp-home');
    if(!pptSection)return; // Not on PPT page at all — clear and bail
    localStorage.removeItem('dsp_load_draft');
    setTimeout(function(){
        if(typeof dspLoadDraft==='function'){
            dspLoadDraft(_id);
        } else {
            fetch(PURL.replace('/generate-ppt','/load-ppt'),{
                method:'POST',
                headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
                body:JSON.stringify({ppt_id:_id})
            })
            .then(function(r){return r.json();})
            .then(function(d){
                if(d&&d.success&&d.data&&d.data.slides){buildEditor(d.data);}
            })
            .catch(function(){});
        }
    },600);
})();
/* === END Auto-load === */

// Refresh credit display every 60 seconds so the header stays in sync
// with actual db value without requiring a page reload.
setInterval(function(){
  fetch(PURL.replace('/generate-ppt','/credits'),{
    headers:{'X-WP-Nonce':NONCE}
  })
  .then(function(r){return r.json();})
  .then(function(d){
    if(d&&d.credits!==undefined){
      G.credits=d.credits;
      var el=document.getElementById('ds-cr-display');
      if(el)el.textContent=d.credits.toLocaleString();
    }
  }).catch(function(){});
}, 60000);

// Sync the displayed cost/summary immediately on load, instead of leaving
// the hardcoded placeholder text in the initial HTML on screen until the
// user happens to touch a toggle or dropdown.
dspUpdCost();

})();
</script>
JSBLOCK;

    $js = str_replace(
        array('__CR__', '__NONCE__', '__PURL__', '__BLDURL__', '__IMGURL__', '__PLAN__', '__PPTBASESLIDES__', '__PPTBASECR__', '__PPTRATE__'),
        array($cr_int, $nonce_js, $purl, $bldurl, $imgurl, $plan_js, $__ppt_base_slides, $__ppt_base_credits, $__ppt_extra_rate),
        $js
    );
    echo $js;
}
}

// Force 12a to be available
if ( ! function_exists('ds_render_slide_body') ) {
    $active_snippets = get_option('active_snippets', array());
    error_log('[12b] ds_render_slide_body NOT found at load time. Active snippets: ' . print_r($active_snippets, true));
}

// ─── REST endpoints ───────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/generate-ppt', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_v3_generate',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/build-pptx', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_v3_build_pptx',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/fetch-slide-image', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_v3_fetch_one_image',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
	register_rest_route('ds/v1','/ppt-debug',array('methods'=>'GET','callback'=>function(){
$dsv_test_slide = array('type'=>'eight_point','variant'=>'v4','title'=>'t','content'=>array('H1: D1','H2: D2','H3: D3','H4: D4','H5: D5','H6: D6','H7: D7','H8: D8'));
$dsv_test_out = function_exists('ds_render_slide_body') ? ds_render_slide_body('eight_point', $dsv_test_slide, 'FFFFFF', 'FFFFFF', 'FFFFFF', 'FFFFFF', null, array()) : 'NO_FN';
$dsv_test_has_div = (strpos($dsv_test_out, 'name="Div0"') !== false) ? 'YES' : 'NO';
$dsv_test_len = strlen($dsv_test_out);
$dsv_variant_check = (isset($dsv_test_slide['variant']) && preg_match('/^v[1-5]$/', (string)$dsv_test_slide['variant'])) ? $dsv_test_slide['variant'] : 'v1';
    return rest_ensure_response(array(
		'permission_callback' => function(){ return current_user_can('manage_options'); },
        'ds_render_slide_body' => function_exists('ds_render_slide_body') ? 'YES' : 'NO',
        'ds_ppt_v3_build_pptx' => function_exists('ds_ppt_v3_build_pptx') ? 'YES' : 'NO',
        'ds_ppt_v3_generate' => function_exists('ds_ppt_v3_generate') ? 'YES' : 'NO',
        'ZipArchive' => class_exists('ZipArchive') ? 'YES' : 'NO',
        'php_version' => PHP_VERSION,
'ds_pptv2_eight_point_v4' => function_exists('ds_pptv2_eight_point_v4') ? 'YES' : 'NO',
'ds_pptv2_eight_point_v5' => function_exists('ds_pptv2_eight_point_v5') ? 'YES' : 'NO',
'_ds_pptv2_longcard_grid' => function_exists('_ds_pptv2_longcard_grid') ? 'YES' : 'NO',
'ds_pptv2_eight_point_v1' => function_exists('ds_pptv2_eight_point_v1') ? 'YES' : 'NO',
'dsv_test_has_div' => $dsv_test_has_div,
'dsv_test_len' => $dsv_test_len,
'dsv_variant_check' => $dsv_variant_check,
    ));
},'permission_callback'=>'__return_true'));
});

// ─── Generate slides via Groq ─────────────────────────────────────────────────
if ( ! function_exists( 'ds_ppt_v3_generate' ) ) {
function ds_ppt_v3_generate( WP_REST_Request $req ) {
    $uid     = get_current_user_id();

   // No plan gate. Credits are the limiter — a user who cannot afford the
    // generation is stopped by the balance check below, which is the correct
    // and only place to enforce it.

    $credits = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid,'ds_ai_credits',true);

    $topic       = sanitize_text_field( $req->get_param('topic') ?: '' );
    $num_slides  = (int) ( $req->get_param('num_slides') ?: $req->get_param('slides') ?: 10 );
    $num_slides  = max(1, min(20, $num_slides));
    $language    = sanitize_text_field( $req->get_param('language') ?: 'English' );
    $deduct_only = ! empty( $req->get_param('deduct_only') );
    $forced_type = sanitize_text_field( $req->get_param('slide_type') ?: '' );
    $forced_pts  = (int) ( $req->get_param('num_points') ?: 0 );
    $is_add_slide = ! empty( $req->get_param('append_slide') );
    // Adding a slide is priced as one slide, so it also makes exactly one.
    if ( $is_add_slide ) $num_slides = 1;
// Cost is ALWAYS computed server-side — never trust client 'amount'.
// Pricing lives in ds_ppt_credit_cost() (Snippet 1a): 1 credit per slide,
// minimum 5 per generation. Change it there, not here.
// EXCEPTION: appending a single AI slide to an already-generated deck
// (Add Slide with AI) is priced at the plain per-slide rate, not the
// flat per-deck minimum — that minimum only makes sense for a brand-new
// deck's first few slides, not for topping up an existing one.
if ( $is_add_slide ) {
    $opt_addslide = get_option('ds_ppt_credit_settings');
    $extra_rate   = ( is_array($opt_addslide) && isset($opt_addslide['extra_slide_rate']) && $opt_addslide['extra_slide_rate'] !== '' ) ? (int) $opt_addslide['extra_slide_rate'] : 1;
    $cost         = (int) ( ( ! $deduct_only && function_exists('ds_ppt_add_slide_cost') ) ? ds_ppt_add_slide_cost() : apply_filters( 'ds_credits_per_slide', max(1, $extra_rate), $deduct_only ? 'preserve' : 'ai' ) );
} else {
    $cost = ds_ppt_credit_cost( $num_slides, $deduct_only ? 'preserve' : 'ai' );
}

    if ($credits < $cost) {
        return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
    }

    if ($deduct_only) {
        if (!ds_deduct_credits($uid, $cost)) {
            return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
        }
        try {
        $ppt_log = get_user_meta($uid,'ds_ppt_generations',true) ?: array();
        array_unshift($ppt_log, array('date'=>current_time('mysql'),'prompt'=>($topic && $topic!=='credit_deduct_only')?$topic:'Preserve My Text','credits'=>$cost,'num_slides'=>$num_slides));
        update_user_meta($uid,'ds_ppt_generations',array_slice($ppt_log,0,100));
        return rest_ensure_response(array('credits_balance'=>(function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid,'ds_ai_credits',true))));
        } catch (Throwable $e) { if (function_exists('ds_add_credits')) { ds_add_credits($uid, $cost); } return rest_ensure_response(array('error' => 'Generation failed', 'message' => 'Something went wrong after payment - your credits have been refunded.')); }
    }

    if ( empty($topic) ) {
        return rest_ensure_response(array('error'=>'No topic','message'=>'Please enter a topic.'));
    }

    // Build Groq prompt
    // Iconify icon names format: collection:icon-name
    $icons = 'mdi:rocket-launch,mdi:chart-bar,mdi:account-group,mdi:target,mdi:lightbulb,mdi:shield-check,mdi:earth,mdi:star,mdi:check-circle,mdi:flag,mdi:trending-up,mdi:cog,mdi:clock-outline,mdi:heart,mdi:home,mdi:email,mdi:cellphone,mdi:magnify,mdi:tune,mdi:account,mdi:lightning-bolt,mdi:trophy,mdi:book-open,mdi:briefcase,mdi:calendar,mdi:sparkles,mdi:weather-sunny,mdi:alert,mdi:play-circle,mdi:plus-circle,mdi:leaf,mdi:cash,mdi:handshake,mdi:brain,mdi:cloud,mdi:lock,mdi:fire,mdi:diamond,mdi:puzzle,mdi:thumb-up';

    // Build the rich prompt
    $prompt  = "Create a {$num_slides}-slide professional presentation in {$language}.\n\n";
    $prompt .= "TOPIC: {$topic}\n\n";

    $prompt .= "=== CONTENT RULES ===\n";
    $prompt .= "- BANNED PHRASES - never write these or anything this generic: Key point, Important aspect, Description here, Clearly defining the issue, Introducing the product or service, Analyzing the target audience, Highlighting unique selling points, Setting the stage, Showcasing the solution, Demonstrating traction and demand, Building rapport with the audience, Crafting a compelling narrative.\n";
    $prompt .= "- Every explanation MUST contain at least ONE concrete element: a number or percentage, a year, a named tool/company/person, or a measurable real-world outcome.\n";
    $prompt .= "- BAD example: Market Opportunity: Analyzing the target audience. GOOD example: Market Opportunity: India SMB payroll market hit \$2.1B in 2025, growing 14% YoY, yet 68% of firms under 50 staff still process salaries manually in Excel.\n";
    $prompt .= "- Every point must have a BOLD HEADING (2-4 words) AND a rich EXPLANATION (30-45 words minimum) with specific facts, numbers, named tools, real examples.\n";
    $prompt .= "- If user gives vague topic, INVENT plausible specifics (real-sounding names, percentages, years, product names).\n";
    $prompt .= "- Subtitles must also be specific - never write subtitles like Key Components of a Successful Pitch. Write subtitles with substance like Why 9 of 10 pitches fail in the first 3 minutes.\n\n";

    $prompt .= "=== SLIDE TYPE SELECTION RULES ===\n";
    $prompt .= "- Slide 1: ALWAYS type \'title\' with 12-16 word subtitle.\n";
	$prompt .= "  If the topic implies a real company, product, or person, also populate slide 1\'s content array with any that apply, each its own item, EXACT format:\n";
    $prompt .= "  \'Company: <name>\', \'Presenter: <full name>\', \'Designation: <job title>\', \'Date: <e.g. 11 July 2026>\', \'Website: <e.g. www.companyname.com>\', \'Tagline: <3-6 words>\'.\n";
    $prompt .= "  Only include items you can fill with something real or plausible - omit any you can\'t. Never include a \'Confidential:\' item unless the topic is explicitly sensitive/internal.\n";
    $prompt .= "- Use your expert knowledge of the TOPIC to decide the natural number of points for each slide.\n";
    $prompt .= "  Do NOT force a fixed count. The topic itself tells you: SWOT=4, pros/cons=2, RGB=3, 4 seasons=4, 5 steps=5.\n";
    $prompt .= "- Choose slide type based on point count AND content nature:\n";
    $prompt .= "  two_column: exactly 2 parallel/contrasting points\n";
    $prompt .= "  three_column: exactly 3 parallel points\n";
    $prompt .= "  feature_grid: exactly 4 key items/quadrants/pillars\n";
	$prompt .= "  five_point: exactly 5 parallel items/pillars/components/steps\n";
  $prompt .= "  six_point: exactly 6 parallel items when content has six natural elements\n";
  $prompt .= "  seven_point: exactly 7 parallel items when content has seven natural elements\n";
    $prompt .= "  bullet: 4-6 points needing detail\n";
    $prompt .= "  stats: 3 key numbers/metrics\n";
	$prompt .= "  five_point / six_point / seven_point: same heading:explanation format as bullet — 5/6/7 items respectively.\n";
    $prompt .= "  timeline: sequential steps/phases/milestones\n";
    $prompt .= "  quote: single powerful quote\n";
    $prompt .= "- Use stats AND timeline AND quote at least once each.\n\n";

    $prompt .= "=== PER-TYPE FORMAT (content array items) ===\n";
    $prompt .= "bullet / two_column / three_column:\n";
    $prompt .= "  Each item = \'BOLD HEADING: Full explanation sentence 30-45 words with specific fact or number.\'\n";
    $prompt .= "  Example: \'Customer Retention: Our loyalty program reduced annual churn from 18% to 6.2% by introducing tiered rewards across 3 membership levels, saving approximately $2.4M in re-acquisition costs each quarter.\'\n";
    $prompt .= "  Number of items: two_column=2, three_column=3, bullet=4-6\n";
  $prompt .= "  five_point=5 items, six_point=6 items, seven_point=7 items (same heading:explanation format as bullet)\n\n";
    $prompt .= "feature_grid:\n";
    $prompt .= "  EXACTLY 4 items = \'Short Heading: 30-40 word explanation with concrete data.\'\n";
    $prompt .= "  Also add 'icons' array of EXACTLY 4 Iconify icon names. Format: collection:name (e.g. mdi:rocket-launch).\n";
    $prompt .= "  Pick from: {$icons}\n";
    $prompt .= "  CRITICAL: Choose icons that DIRECTLY match each card's specific topic. Examples:\n";
    $prompt .= "  team/people/collaboration=mdi:account-group, money/revenue/finance=mdi:cash, security/trust/safety=mdi:shield-check, growth/increase/trending=mdi:trending-up, idea/innovation/creative=mdi:lightbulb, global/worldwide/reach=mdi:earth, technology/software/digital=mdi:cog, speed/fast/efficient=mdi:lightning-bolt, success/complete/done=mdi:check-circle, goal/strategy/focus=mdi:target, data/analytics/metrics=mdi:chart-bar, customer/user/person=mdi:account, heart/care/health=mdi:heart, time/schedule/plan=mdi:clock-outline, award/trophy/best=mdi:trophy, learning/education/book=mdi:book-open, communication/message=mdi:email, rocket/launch/startup=mdi:rocket-launch, leaf/green/eco/sustainable=mdi:leaf, handshake/partner/deal=mdi:handshake, brain/ai/smart=mdi:brain, lock/private/secure=mdi:lock, fire/hot/popular=mdi:fire, puzzle/solution/fit=mdi:puzzle, like/approve/positive=mdi:thumb-up\n";
    $prompt .= "stats:\n";
    $prompt .= "  EXACTLY 3 items = \'METRIC_VALUE: 4-7 word descriptive label\'\n";
    $prompt .= "  Example: \'94.7%: Annual customer retention rate 2024\'\n\n";
    $prompt .= "timeline:\n";
    $prompt .= "  EXACTLY 5 items = \'YEAR or PHASE: 15-22 word description of what was achieved or planned.\'\n\n";
    $prompt .= "quote:\n";
    $prompt .= "  title = full quote 20-30 words (no quotes in JSON), content[0] = \'Person Name, Title, Company\'\n\n";

    $prompt .= "=== MANDATORY SLIDE HEADER ===\n";
    $prompt .= "Every slide (except title/quote) MUST have:\n";
    $prompt .= "  - \'title\': Strong 3-6 word slide heading\n";
    $prompt .= "  - \'subtitle\': 8-12 word supporting sub-headline that adds context\n\n";

    $prompt .= "Return ONLY valid JSON in this shape (no markdown):\n";
    $prompt .= '{"title":"Presentation Title","slides":[{"type":"title","title":"...","subtitle":"...","content":[],"image_query":"","icon":"","icons":[]},{"type":"two_column","title":"...","subtitle":"...","content":["Heading One: Full 30 word explanation with specific data.","Heading Two: Full 30 word explanation with specific data."],"image_query":"","icon":"","icons":[]},{"type":"three_column","title":"...","subtitle":"...","content":["Heading: explanation...","Heading: explanation...","Heading: explanation..."],"image_query":"","icon":"","icons":[]},{"type":"feature_grid","title":"...","subtitle":"...","content":["Heading: explanation...","Heading: explanation...","Heading: explanation...","Heading: explanation..."],"image_query":"","icon":"","icons":["rocket","chart","users","target"]},{"type":"stats","title":"...","subtitle":"...","content":["94.7%: Annual customer retention","1.2M: Active monthly users","$4.8M: Quarterly revenue 2024"],"image_query":"","icon":"","icons":[]},{"type":"timeline","title":"...","subtitle":"...","content":["2022 Q1: Launched MVP serving 200 pilot customers in 3 cities.","2023 Q2: Scaled to 50,000 users with Series A funding of $12M.","2023 Q4: Expanded into 10 new international markets.","2024 Q2: Reached 500,000 active users with 99.9% platform uptime.","2024 Q4: Launched enterprise tier, signing 40 Fortune 500 clients."],"image_query":"","icon":"","icons":[]},{"type":"quote","title":"The best salespeople are the ones who listen more than they talk and ask questions that reveal the true pain of the customer.","subtitle":"","content":["Brian Tracy, Sales Trainer, FocalPoint"],"image_query":"","icon":"","icons":[]},{"type":"bullet","title":"...","subtitle":"...","content":["Heading: Full 35 word explanation with specific numbers or named tools.","Heading: Full 35 word explanation with specific numbers or named tools.","Heading: Full 35 word explanation with specific numbers or named tools.","Heading: Full 35 word explanation with specific numbers or named tools."],"image_query":"","icon":"","icons":[]}]}';

    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $api_key = isset($keys['groq']) ? $keys['groq'] : '';
    if ( empty($api_key) ) {
        return rest_ensure_response(array('error'=>'No API key','message'=>'Groq API key missing in snippet 2a (ds_get_api_keys).'));
    }
	$groq_model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');

	// ── Add Slide: force the requested slide type ──────────────────────────
    if ( $num_slides === 1 && $forced_type !== '' ) {
        $type_rules = array(
            'title'        => 'type "title" — a cover slide with a strong title and 12-16 word subtitle.',
            'bullet'       => 'type "bullet" — 4-6 detailed bullet points each with a bold heading and 30-45 word explanation.',
            'two_column'   => 'type "two_column" — exactly 2 parallel or contrasting points.',
            'three_column' => 'type "three_column" — exactly 3 parallel points.',
            'feature_grid' => 'type "feature_grid" — exactly 4 key items with icons.',
            'stats'        => 'type "stats" — exactly 3 key metrics in VALUE: label format.',
            'timeline'     => 'type "timeline" — exactly ' . ( $forced_pts > 0 ? $forced_pts : 5 ) . ' sequential steps or milestones.',
            'quote'        => 'type "quote" — a single powerful quote (20-30 words) with attribution in content[0].',
            'swot'         => 'type "feature_grid" — exactly 4 items: Strengths, Weaknesses, Opportunities, Threats. Each 30-40 words with concrete data. Icons: mdi:shield-check, mdi:alert, mdi:trending-up, mdi:target.',
        );
        $type_instruction = $type_rules[ $forced_type ] ?? 'type "' . $forced_type . '"';
        $prompt .= "\n\n=== ADD SLIDE OVERRIDE ===\n";
        $prompt .= "Generate EXACTLY 1 slide. It MUST use " . $type_instruction . "\n";
        $prompt .= "Do NOT use any other type. Do NOT generate a title/cover slide.\n";
        if ( $forced_pts > 0 && $forced_type === 'timeline' ) {
            $prompt .= "The timeline MUST have exactly {$forced_pts} items.\n";
        }
    }
    // ──────────────────────────────────────────────────────────────────────
    $sys_msg = "You are a world-class presentation designer and content strategist. Write RICH, SPECIFIC, DATA-DRIVEN content. NEVER write generic placeholders. Every bullet point has a bold SHORT HEADING (2-4 words) followed by a colon and a FULL EXPLANATION (30-45 words minimum) with real statistics, named tools, specific examples, or concrete outcomes. Use your deep knowledge of each topic to decide how many points naturally belong — do NOT force a fixed count. Choose slide type based on what the topic demands: SWOT=4 items feature_grid, pros/cons=2 items two_column, 3 pillars=three_column, detailed list=bullet, numbers=stats, steps=timeline. Every slide except title/quote must have both title AND subtitle. Output ONLY valid JSON, no markdown, no code fences.";

    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'timeout' => 60,
        'headers' => array(
            'Authorization' => 'Bearer '.$api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode(array(
            'model'           => $groq_model,
            'messages'        => array(
                array('role'=>'system','content'=>$sys_msg),
                array('role'=>'user','content'=>$prompt),
            ),
            'temperature'     => 0.85,
            'top_p'           => 0.95,
            'max_tokens'      => 6000,
            'response_format' => array('type'=>'json_object'),
        )),
    ));

    if (is_wp_error($resp)) {
        return rest_ensure_response(array('error'=>'API error','message'=>$resp->get_error_message()));
    }
    $body = wp_remote_retrieve_body($resp);
    $data = json_decode($body, true);
$content = $data['choices'][0]['message']['content'] ?? '';

// Strip markdown fences if model wraps JSON in ```
$content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
$content = preg_replace('/\s*```$/', '', $content);

$ppt = json_decode($content, true);

    if ( ! $ppt || empty($ppt['slides']) ) {
        return rest_ensure_response(array('error'=>'Parse failed','message'=>'AI response could not be parsed.'));
    }

    // Sanitize slides
    $clean_slides = array();
    foreach ($ppt['slides'] as $s) {
        if ( ! is_array($s) ) continue;
        $type = sanitize_text_field($s['type'] ?? 'bullet');
        $allowed = array('title','bullet','two_column','three_column','feature_grid','stats','timeline','quote');
        if ( ! in_array($type, $allowed, true) ) $type = 'bullet';
        $clean_content = array();
        if ( ! empty($s['content']) && is_array($s['content']) ) {
            foreach ($s['content'] as $c) $clean_content[] = sanitize_text_field((string)$c);
        }
        // Auto-fix column type based on actual point count.
        // 'image_text' is included so the AI can never lock a slide into an
        // image-only layout. Images are added after generation via the layout
        // picker, which offers "+ image" variants for every one of these types.
        if ( in_array($type, array('two_column','three_column','bullet','image_text'), true) && count($clean_content) > 0 ) {
            $cnt = count($clean_content);
            if     ($cnt === 2) $type = 'two_column';
            elseif ($cnt === 3) $type = 'three_column';
            elseif ($cnt === 4) $type = 'feature_grid';
            else                $type = 'bullet';   // 5/6/7 map to card layouts in the picker
        }
        $clean_icons = array();
        if ( ! empty($s['icons']) && is_array($s['icons']) ) {
            foreach ($s['icons'] as $ic) $clean_icons[] = sanitize_text_field((string)$ic);
        }
        $clean_slides[] = array(
            'type'         => $type,
            'title'        => sanitize_text_field($s['title'] ?? ''),
            'subtitle'     => sanitize_text_field($s['subtitle'] ?? ''),
            'content'      => $clean_content,
            // Blanked deliberately: no image is fetched at generation time.
            // Remove the '' and restore the sanitize_text_field(...) line if you
            // ever want auto-images back.
            'image_query'  => '',
            'icon'         => sanitize_text_field($s['icon'] ?? ''),
            'icons'        => $clean_icons,
        );
    }
    if ( count($clean_slides) > $num_slides ) $clean_slides = array_slice($clean_slides, 0, $num_slides);
    while ( count($clean_slides) < $num_slides ) {
        $clean_slides[] = array(
            'type'         => 'bullet',
            'title'        => 'Section '.(count($clean_slides) + 1),
            'subtitle'     => '',
            'content'      => array('Add content here'),
            'image_query'  => '',
            'icon'         => '',
        );
    }

    $ppt = array(
        'title'  => sanitize_text_field($ppt['title'] ?? $topic),
        'slides' => $clean_slides,
    );

    // Save & deduct
    $id = 'ppt_'.time().'_'.wp_generate_password(8,false,false);
    $jobs = get_user_meta($uid,'ds_ppt_jobs',true) ?: array();
    array_unshift($jobs, array('id'=>$id,'title'=>$ppt['title'],'slides'=>count($ppt['slides']),'time'=>time()));
    update_user_meta($uid,'ds_ppt_jobs',array_slice($jobs,0,30));
    if (!ds_deduct_credits($uid, $cost)) {
            return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
        }
        try {

    // Log this to the Credit History tab (Account dropdown)
    $ppt_log = get_user_meta($uid,'ds_ppt_generations',true) ?: array();
    array_unshift($ppt_log, array('date'=>current_time('mysql'),'prompt'=>$topic,'credits'=>$cost,'num_slides'=>$num_slides));
    update_user_meta($uid,'ds_ppt_generations',array_slice($ppt_log,0,100));

    return rest_ensure_response(array('ppt_id'=>$id,'ppt_data'=>$ppt,'credits_balance'=>(int)get_user_meta($uid,'ds_ai_credits',true)));
        } catch (Throwable $e) { if (function_exists('ds_add_credits')) { ds_add_credits($uid, $cost); } return rest_ensure_response(array('error' => 'Generation failed', 'message' => 'Something went wrong after payment - your credits have been refunded.')); }
}
}

// ─── Image fetch helper (single image — frontend calls per-slide) ────────────
if ( ! function_exists( 'ds_ppt_fetch_image' ) ) {
function ds_ppt_fetch_image($query) {
    $query = trim((string)$query);
    if ( empty($query) ) return null;
    if ( ! function_exists('ds_generate_image_dispatch') ) {
        error_log('[ds_ppt_fetch_image] ds_generate_image_dispatch not loaded');
        return null;
    }

    // ⭐ FIX: 4:3 LANDSCAPE for image_text slides (was 3:4 portrait)
    $enhanced = $query . ', professional photography, clean composition, soft lighting, modern aesthetic, landscape orientation, wide shot';
    $r = ds_generate_image_dispatch('cloudflare', $enhanced, '', '16:9', 1, '2K');

    if ( ! is_array($r) ) return null;
    if ( ! empty($r['error']) ) {
        error_log('[ds_ppt_fetch_image] '.$r['error']);
        return null;
    }

    $url = null;
    if ( ! empty($r['images'][0]) ) {
        $url = $r['images'][0];
    } elseif ( ! empty($r['prediction_id']) ) {
        $pid = $r['prediction_id'];
        for ($i = 0; $i < 15; $i++) {
            sleep(2);
            if ( ! function_exists('ds_poll_replicate') ) break;
            $p = ds_poll_replicate($pid);
            if ( ! empty($p['images'][0]) ) { $url = $p['images'][0]; break; }
            if ( isset($p['status']) && $p['status'] === 'failed' ) break;
        }
    }
    if ( empty($url) ) return null;

    $bytes = null;
    if ( strpos($url, 'data:') === 0 ) {
        $parts = explode(',', $url, 2);
        $bytes = isset($parts[1]) ? base64_decode($parts[1]) : null;
    } else {
        $resp = wp_remote_get($url, array('timeout' => 60, 'sslverify' => false));
        if ( is_wp_error($resp) ) return null;
        $bytes = wp_remote_retrieve_body($resp);
    }
    if ( empty($bytes) || strlen($bytes) < 1000 ) return null;

    $ext = null;
    $head = substr($bytes, 0, 12);
    $b0 = ord($head[0] ?? "\0"); $b1 = ord($head[1] ?? "\0");
    $b2 = ord($head[2] ?? "\0"); $b3 = ord($head[3] ?? "\0");
    if ($b0 === 0x89 && $b1 === 0x50 && $b2 === 0x4E && $b3 === 0x47) $ext = 'png';
    elseif ($b0 === 0xFF && $b1 === 0xD8 && $b2 === 0xFF) $ext = 'jpg';
    elseif (substr($head, 0, 4) === 'RIFF' && substr($head, 8, 4) === 'WEBP') {
        if ( function_exists('imagecreatefromstring') ) {
            $im = @imagecreatefromstring($bytes);
            if ($im) {
                ob_start(); imagejpeg($im, null, 90);
                $bytes = ob_get_clean(); imagedestroy($im);
                $ext = 'jpg';
            }
        }
    }
    if ($ext === null) return null;

    return array('bytes' => $bytes, 'ext' => $ext);
}
}

if ( ! function_exists( 'ds_ppt_v3_fetch_one_image' ) ) {
function ds_ppt_v3_fetch_one_image( WP_REST_Request $req ) {
    @set_time_limit(120);
    $uid     = get_current_user_id();
    $cost    = 2; // credits per AI-generated image
    $credits = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid,'ds_ai_credits',true);
    if ( $credits < $cost ) {
        return rest_ensure_response(array('success'=>false,'error'=>'Not enough credits','message'=>'You need '.$cost.' credits to generate an image.'));
    }
    $query = sanitize_text_field( $req->get_param('query') );
    if ( empty($query) ) return rest_ensure_response(array('success'=>false,'error'=>'No query'));
    $img = ds_ppt_fetch_image($query);
    if ( ! $img ) return rest_ensure_response(array('success'=>false,'error'=>'Image gen failed'));
    update_user_meta($uid,'ds_ai_credits',max(0,$credits-$cost));

    // Log this to the Credit History tab — same bucket the standalone
    // AI Images tool uses (model is genuinely 'huggingface' here too,
    // see ds_ppt_fetch_image — so it correctly costs 3cr via the
    // existing $model_costs lookup, no other change needed).
    $gens = get_user_meta($uid,'ds_ai_generations',true) ?: array();
    array_unshift($gens, array('date'=>current_time('mysql'),'model'=>'cloudflare','prompt'=>$query,'url'=>''));
    update_user_meta($uid,'ds_ai_generations',array_slice($gens,0,200));

    $mime = $img['ext'] === 'png' ? 'image/png' : 'image/jpeg';
    $data_uri = 'data:'.$mime.';base64,'.base64_encode($img['bytes']);
    return rest_ensure_response(array('success'=>true,'image'=>$data_uri,'credits_balance'=>(int)get_user_meta($uid,'ds_ai_credits',true)));
}
}


// ─── Fetch icon from Iconify API and return PNG bytes ────────────────────────
if ( ! function_exists( 'ds_ppt_fetch_iconify' ) ) {
function ds_ppt_fetch_iconify($icon_name, $color = 'FFFFFF', $size = 128) {
    // icon_name format: "mdi:rocket-launch" -> collection=mdi, name=rocket-launch
    if ( empty($icon_name) || strpos($icon_name, ':') === false ) return null;

    list($collection, $name) = explode(':', $icon_name, 2);
    $collection = preg_replace('/[^a-z0-9-]/', '', strtolower($collection));
    $name       = preg_replace('/[^a-z0-9-]/', '', strtolower($name));
    if ( empty($collection) || empty($name) ) return null;

    // Fetch SVG from Iconify
    $url = "https://api.iconify.design/{$collection}/{$name}.svg?color=%23{$color}&width={$size}&height={$size}";
    $resp = wp_remote_get($url, array(
        'timeout'   => 8,
        'sslverify' => false,
        'headers'   => array('User-Agent' => 'Mozilla/5.0'),
    ));
    if ( is_wp_error($resp) ) return null;
    $svg = wp_remote_retrieve_body($resp);
    if ( empty($svg) || strpos($svg, '<svg') === false ) return null;

    // Convert SVG to PNG using GD + Imagick if available
    // Method 1: Imagick (best quality)
    if ( class_exists('Imagick') ) {
        try {
            $im = new Imagick();
            $im->setBackgroundColor(new ImagickPixel('transparent'));
            $im->readImageBlob($svg);
            $im->setImageFormat('png32');
            $im->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
            $png = $im->getImageBlob();
            $im->destroy();
            if ( strlen($png) > 200 ) {
                /* DS-FIX 2026-09-12 - return the vector alongside the raster.
                   Previously Imagick's PNG was returned on its own, so the deck
                   builder never saw an SVG and icons shipped as flat bitmaps that
                   could not be recoloured in PowerPoint. */
                return array( 'svg' => $svg, 'png' => $png, 'collection' => $collection, 'name' => $name );
            }
        } catch(Exception $e) {
            error_log('[ds_ppt_iconify] Imagick error: '.$e->getMessage());
        }
    }

    // Method 2: Request PNG directly via Iconify (some icons support it)
    $png_url = "https://api.iconify.design/{$collection}/{$name}.svg?color=%23{$color}&width={$size}&height={$size}";
    // Iconify only serves SVG, so we use GD to rasterize
    // Method 3: Use GD to render SVG by parsing basic shapes (fallback - just return SVG as data)
    // For now return the raw SVG bytes and handle in the slide builder
    return array('svg' => $svg, 'collection' => $collection, 'name' => $name);
}
}

// Convert Iconify SVG to embeddable PNG bytes
if ( ! function_exists( 'ds_ppt_svg_to_png' ) ) {
function ds_ppt_svg_to_png($svg_string, $size = 128) {
    // Try Imagick first
    if ( class_exists('Imagick') ) {
        try {
            $im = new Imagick();
            $im->setBackgroundColor(new ImagickPixel('transparent'));
            $im->setResolution(300, 300);
            $im->readImageBlob($svg_string);
            $im->setImageFormat('png32');
            $im->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
            $png = $im->getImageBlob();
            $im->destroy();
            if ( strlen($png) > 200 ) return $png;
        } catch(Exception $e) {}
    }
    // Fallback: use GD with a white square (icon unavailable)
    if ( function_exists('imagecreatetruecolor') ) {
        $im = imagecreatetruecolor($size, $size);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);
        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);
        return $png;
    }
    return null;
}
}
if ( ! defined('DS_PROMO_ICON_MAIL_B64') )  define('DS_PROMO_ICON_MAIL_B64',  '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');
if ( ! defined('DS_PROMO_ICON_GLOBE_B64') ) define('DS_PROMO_ICON_GLOBE_B64', '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');
if ( ! defined('DS_PROMO_ICON_CHECK_B64') ) define('DS_PROMO_ICON_CHECK_B64', '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');
if ( ! defined('DS_PROMO_SLIDE_BODY_XML') ) define('DS_PROMO_SLIDE_BODY_XML', '<p:sp><p:nvSpPr><p:cNvPr id="2" name="Shape 0"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="4206240" cy="6858000"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="0E1A33"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:sp><p:nvSpPr><p:cNvPr id="3" name="Shape 1"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4206240" y="0"/><a:ext cx="7985455" cy="6858000"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="16284A"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:sp><p:nvSpPr><p:cNvPr id="4" name="Text 2"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="640080"/><a:ext cx="3246120" cy="274320"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1200" b="1" spc="200" kern="0" dirty="0"><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>NEED SOMETHING BETTER?</a:t></a:r><a:endParaRPr lang="en-US" sz="1200" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="5" name="Text 3"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="960120"/><a:ext cx="3246120" cy="1188720"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="t"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:lnSpc><a:spcPct val="108000"/></a:lnSpc><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="3000" b="1" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>Don&apos;t Love This Presentation?</a:t></a:r><a:endParaRPr lang="en-US" sz="3000" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="6" name="Text 4"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="2331720"/><a:ext cx="3246120" cy="960120"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="t"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:lnSpc><a:spcPct val="125000"/></a:lnSpc><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1300" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Our AI creates presentations in seconds, but if you need a polished, business-ready presentation, our professional designers can help.</a:t></a:r><a:endParaRPr lang="en-US" sz="1300" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="7" name="Shape 5"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="3429000"/><a:ext cx="3246120" cy="868680"/></a:xfrm><a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 8421"/></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln><a:effectLst> <a:outerShdw sx="100000" sy="100000" kx="0" ky="0" algn="bl" rotWithShape="0" blurRad="101600" dist="38100" dir="5400000"> <a:srgbClr val="000000"> <a:alpha val="25000"/></a:srgbClr> </a:outerShdw></a:effectLst></p:spPr></p:sp><p:sp><p:nvSpPr><p:cNvPr id="8" name="Text 6"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="3502152"/><a:ext cx="3246120" cy="274320"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="ctr" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1100" b="1" spc="150" kern="0" dirty="0"><a:solidFill><a:srgbClr val="0E1A33"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>STARTING AT JUST</a:t></a:r><a:endParaRPr lang="en-US" sz="1100" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="9" name="Text 7"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="3749040"/><a:ext cx="3246120" cy="457200"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="ctr" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="2200" b="1" dirty="0"><a:solidFill><a:srgbClr val="0E1A33"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>$7 USD per Slide</a:t></a:r><a:endParaRPr lang="en-US" sz="2200" dirty="0"/></a:p></p:txBody></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="10" name="Image 0" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId1"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="502920" y="4645152"/>  <a:ext cx="237744" cy="237744"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="11" name="Text 8"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="868680" y="4590288"/><a:ext cx="2880360" cy="292608"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>support@designstager.com</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="12" name="Image 1" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId2"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="502920" y="5029200"/>  <a:ext cx="237744" cy="237744"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="13" name="Text 9"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="868680" y="4974336"/><a:ext cx="2880360" cy="292608"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>designstager.com</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="14" name="Text 10"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="502920" y="5897880"/><a:ext cx="3246120" cy="502920"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="t"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:lnSpc><a:spcPct val="130000"/></a:lnSpc><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1200" b="1" spc="150" kern="0" dirty="0"><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>DESIGNSTAGER</a:t></a:r><a:endParaRPr lang="en-US" sz="1200" dirty="0"/></a:p><a:p><a:pPr algn="l" indent="0" marL="0"><a:lnSpc><a:spcPct val="130000"/></a:lnSpc><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1000" dirty="0"><a:solidFill><a:srgbClr val="8AA0CC"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>Professional Presentation Design Services</a:t></a:r><a:endParaRPr lang="en-US" sz="1200" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="15" name="Text 11"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="548640"/><a:ext cx="6995160" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1900" b="1" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>We Can Customize:</a:t></a:r><a:endParaRPr lang="en-US" sz="1900" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="16" name="Shape 12"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="1170432"/><a:ext cx="292608" cy="292608"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="17" name="Image 2" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId3"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="4777740" y="1239012"/>  <a:ext cx="155448" cy="155448"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="18" name="Text 13"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="5129784" y="1115568"/><a:ext cx="2939796" cy="420624"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Professional PowerPoint Design</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="19" name="Shape 14"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8343900" y="1170432"/><a:ext cx="292608" cy="292608"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="20" name="Image 3" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId4"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="8412480" y="1239012"/>  <a:ext cx="155448" cy="155448"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="21" name="Text 15"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8764524" y="1115568"/><a:ext cx="2939796" cy="420624"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Premium Infographics &amp; Visuals</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="22" name="Shape 16"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="1737360"/><a:ext cx="292608" cy="292608"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="23" name="Image 4" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId5"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="4777740" y="1805940"/>  <a:ext cx="155448" cy="155448"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="24" name="Text 17"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="5129784" y="1682496"/><a:ext cx="2939796" cy="420624"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Brand Colors &amp; Typography</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="25" name="Shape 18"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8343900" y="1737360"/><a:ext cx="292608" cy="292608"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="26" name="Image 5" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId6"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="8412480" y="1805940"/>  <a:ext cx="155448" cy="155448"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="27" name="Text 19"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8764524" y="1682496"/><a:ext cx="2939796" cy="420624"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Charts, Icons &amp; Illustrations</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="28" name="Shape 20"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="2304288"/><a:ext cx="292608" cy="292608"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="29" name="Image 6" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId7"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="4777740" y="2372868"/>  <a:ext cx="155448" cy="155448"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="30" name="Text 21"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="5129784" y="2249424"/><a:ext cx="2939796" cy="420624"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Executive-Level Layouts</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="31" name="Shape 22"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8343900" y="2304288"/><a:ext cx="292608" cy="292608"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst></a:avLst></a:prstGeom><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:ln></a:ln></p:spPr></p:sp><p:pic>  <p:nvPicPr><p:cNvPr id="32" name="Image 7" descr="preencoded.png">    </p:cNvPr>    <p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>    <p:nvPr></p:nvPr>  </p:nvPicPr><p:blipFill><a:blip r:embed="rId8"></a:blip>  <a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr> <a:xfrm>  <a:off x="8412480" y="2372868"/>  <a:ext cx="155448" cy="155448"/> </a:xfrm> <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="33" name="Text 23"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8764524" y="2249424"/><a:ext cx="2939796" cy="420624"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Fast Turnaround</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="34" name="Text 24"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="3200400"/><a:ext cx="6995160" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1900" b="1" dirty="0"><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface="Arial" pitchFamily="34" charset="-122"/><a:cs typeface="Arial" pitchFamily="34" charset="-120"/></a:rPr><a:t>Perfect For:</a:t></a:r><a:endParaRPr lang="en-US" sz="1900" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="35" name="Text 25"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="3794760"/><a:ext cx="3360420" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" marL="177800" indent="-177800"><a:buSzPct val="100000"/><a:buChar char="&#x25CF;"/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Business Presentations</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="36" name="Text 26"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8343900" y="3794760"/><a:ext cx="3360420" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" marL="177800" indent="-177800"><a:buSzPct val="100000"/><a:buChar char="&#x25CF;"/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Investor Pitch Decks</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="37" name="Text 27"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="4251960"/><a:ext cx="3360420" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" marL="177800" indent="-177800"><a:buSzPct val="100000"/><a:buChar char="&#x25CF;"/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Sales Presentations</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="38" name="Text 28"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8343900" y="4251960"/><a:ext cx="3360420" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" marL="177800" indent="-177800"><a:buSzPct val="100000"/><a:buChar char="&#x25CF;"/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Company Profiles</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="39" name="Text 29"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="4709160"/><a:ext cx="3360420" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" marL="177800" indent="-177800"><a:buSzPct val="100000"/><a:buChar char="&#x25CF;"/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Marketing Decks</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="40" name="Text 30"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="8343900" y="4709160"/><a:ext cx="3360420" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" marL="177800" indent="-177800"><a:buSzPct val="100000"/><a:buChar char="&#x25CF;"/></a:pPr><a:r><a:rPr lang="en-US" sz="1250" dirty="0"><a:solidFill><a:srgbClr val="C9D4E8"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Training Presentations</a:t></a:r><a:endParaRPr lang="en-US" sz="1250" dirty="0"/></a:p></p:txBody></p:sp><p:sp><p:nvSpPr><p:cNvPr id="41" name="Text 31"></p:cNvPr><p:cNvSpPr/><p:nvPr></p:nvPr></p:nvSpPr><p:spPr><a:xfrm><a:off x="4709160" y="5486400"/><a:ext cx="6995160" cy="365760"/></a:xfrm><a:prstGeom prst="rect"><a:avLst></a:avLst></a:prstGeom><a:noFill/><a:ln></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" rtlCol="0" anchor="ctr"></a:bodyPr><a:lstStyle/><a:p><a:pPr algn="l" indent="0" marL="0"><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="1400" i="1" dirty="0"><a:solidFill><a:srgbClr val="F59E0B"/></a:solidFill><a:latin typeface="Calibri" pitchFamily="34" charset="0"/><a:ea typeface="Calibri" pitchFamily="34" charset="-122"/><a:cs typeface="Calibri" pitchFamily="34" charset="-120"/></a:rPr><a:t>Let&apos;s elevate your next presentation.</a:t></a:r><a:endParaRPr lang="en-US" sz="1400" dirty="0"/></a:p></p:txBody></p:sp>');

// ─── Build PPTX file ──────────────────────────────────────────────────────────
/**
 * Convert an image_position {x, y} percentage (0-100) into OOXML blipFill
 * crop XML using <a:srcRect>. This replicates PowerPoint's "crop to fill"
 * behaviour: the image is treated as if background-size:cover, then
 * background-position:X% Y% decides which part shows.
 *
 * @param array|null $pos  ['x'=>0..100, 'y'=>0..100], or null for center
 * @param float $imgW  image natural width in pixels
 * @param float $imgH  image natural height in pixels  
 * @param float $zoneW zone width in EMU
 * @param float $zoneH zone height in EMU
 * @return string  OOXML blipFill inner XML (stretch or srcRect+stretch)
 */
if ( ! function_exists( 'ds_ppt_blip_fill_crop' ) ) {
function ds_ppt_blip_fill_crop( $pos, $imgW, $imgH, $zoneW, $zoneH ) {
    $px = isset($pos['x']) ? (float)$pos['x'] : 50;
    $py = isset($pos['y']) ? (float)$pos['y'] : 50;
    if ( ! $imgW || ! $imgH || ! $zoneW || ! $zoneH ) {
        return '<a:stretch><a:fillRect/></a:stretch>';
    }
    $zoneRatio = $zoneW / $zoneH;
    $imgRatio  = $imgW / $imgH;
    if ( $imgRatio > $zoneRatio ) {
        $visibleFrac = $zoneRatio / $imgRatio;
        $cropTotal   = 1.0 - $visibleFrac;
        $l = $cropTotal * ( $px / 100.0 );
        $r = $cropTotal * ( 1.0 - $px / 100.0 );
        $t = 0.0; $b = 0.0;
    } else {
        $visibleFrac = $imgRatio / $zoneRatio;
        $cropTotal   = 1.0 - $visibleFrac;
        $t = $cropTotal * ( $py / 100.0 );
        $b = $cropTotal * ( 1.0 - $py / 100.0 );
        $l = 0.0; $r = 0.0;
    }
    $toOOXML = function($v) { return (int) round($v * 100000); };
    $lv = $toOOXML($l); $rv = $toOOXML($r);
    $tv = $toOOXML($t); $bv = $toOOXML($b);
    if ( $lv === 0 && $rv === 0 && $tv === 0 && $bv === 0 ) {
        return '<a:stretch><a:fillRect/></a:stretch>';
    }
    return '<a:srcRect l="'.$lv.'" t="'.$tv.'" r="'.$rv.'" b="'.$bv.'"/>'
         . '<a:stretch><a:fillRect/></a:stretch>';
}
}

if ( ! function_exists('ds_ppt_bg_xml') ) {
function ds_ppt_bg_xml($bg, $template) {
    $gradients = array(
        'aurora'         => array('0F2027','203A43','2C5364'),
        'sunset-blaze'   => array('7B0000','B22222','FF4500'),
        'cosmic-purple'  => array('1A0050','4B0082','7B00D4'),
        'ocean-depth'    => array('000028','00003F','0000A0'),
        'emerald-dusk'   => array('0A2E1A','1A5C34','2E8B57'),
        'rose-gold-glow' => array('0D0720','3D1A4A','7B2D8B'),
        'amber-blaze'    => array('1A0A00','7B3F00','CC6600'),
        'midnight-bloom' => array('0B0020','1A0040','2D006B'),
        'deep-navy'      => array('0A1628','0D2244','1B3A6B'),
        'dark-crimson'   => array('1A0000','3D0000','660000'),
        'dark-slate'     => array('0F172A','1E293B','334155'),
        'dark-forest'    => array('052010','0A3320','0F4D30'),
    );
    if ( isset($gradients[$template]) ) {
        $stops = $gradients[$template];
        return '<p:bg><p:bgPr>'
            . '<a:gradFill rotWithShape="1">'
            . '<a:gsLst>'
            . '<a:gs pos="0"><a:srgbClr val="'.$stops[0].'"/></a:gs>'
            . '<a:gs pos="50000"><a:srgbClr val="'.$stops[1].'"/></a:gs>'
            . '<a:gs pos="100000"><a:srgbClr val="'.$stops[2].'"/></a:gs>'
            . '</a:gsLst>'
            . '<a:lin ang="5400000" scaled="0"/>'
            . '</a:gradFill>'
            . '</p:bgPr></p:bg>';
    }
    return '<p:bg><p:bgPr><a:solidFill><a:srgbClr val="'.$bg.'"/></a:solidFill></p:bgPr></p:bg>';
}
}

if ( ! function_exists( 'ds_ppt_v3_build_pptx' ) ) {
function ds_ppt_v3_build_pptx( WP_REST_Request $req ) {
update_option('_dsv_debug_entry', array('time'=>time(),'route'=>$req->get_route()), false);

    @set_time_limit(0);
    @ignore_user_abort(true);
    @ini_set('memory_limit', '512M');

    $title    = sanitize_text_field( $req->get_param('title') ?: 'Presentation' );
    $template = sanitize_text_field( $req->get_param('template') ?: 'navy-pro' );
    $layout   = sanitize_text_field( $req->get_param('layout') ?: '16:9' );
    $slides   = $req->get_param('slides');
    if ( function_exists('ds_hub_count_saved_items') ) {
        $ds_ppt_uid = get_current_user_id();
        if ( $ds_ppt_uid && ds_hub_count_saved_items( $ds_ppt_uid ) >= ( function_exists('ds_get_user_saved_cap') ? ds_get_user_saved_cap( $ds_ppt_uid ) : 100 ) ) {
            $ds_ppt_at_cap = true;   /* at the limit the file is still built and downloaded - it is just not kept in the saved list */
        }
    }

    // Slide dims (EMU). 16:9=standard widescreen. 4:5 and 9:16 use correct portrait EMUs.
    $layouts = array(
        '16:9' => array('cx' => 12192000, 'cy' => 6858000,  'type' => 'screen16x9'),
        '4:5'  => array('cx' => 6858000,  'cy' => 8572500,  'type' => 'custom'),
        '9:16' => array('cx' => 6858000,  'cy' => 12192000, 'type' => 'custom'),
    );
    $L = isset($layouts[$layout]) ? $layouts[$layout] : $layouts['16:9'];
    $sld_cx = $L['cx']; $sld_cy = $L['cy']; $sld_type = $L['type'];

    if ( ! is_array($slides) || empty($slides) ) {
        return rest_ensure_response(array('success'=>false,'error'=>'No slides data.'));
    }
    if ( ! class_exists('ZipArchive') ) {
        return rest_ensure_response(array('success'=>false,'error'=>'ZipArchive not available.'));
    }
  
	if ( $layout === '16:9' ) {
        array_splice($slides, max(count($slides) - 1, 0), 0, array(array('type' => '_promo_cta')));
    }

    // theme: bg/title/body/accent
    $themes = array(
        'navy-pro'       => array('1B2A4A','FFFFFF','C8D8F0','4A9EFF'),
        'dark-modern'    => array('0D1117','FFFFFF','C9D1D9','58A6FF'),
        'clean-corporate'=> array('FFFFFF','1A365D','2D3748','2B6CB0'),
        'bold-red'       => array('1A0000','FFFFFF','FFD0D0','FF4444'),
        'corporate-dark' => array('111827','F9FAFB','D1D5DB','6366F1'),
        'clean-white'    => array('F8FAFC','0F172A','334155','0EA5E9'),
        'gradient-pro'   => array('0F0C29','FFFFFF','E0D7FF','A78BFA'),
        'bold-impact'    => array('111111','FFFFFF','CCCCCC','FFD700'),
        'sunset-glow'    => array('2D1B2E','FFFFFF','F4C9D9','FF6B9D'),
        'forest-green'   => array('0F2419','FFFFFF','C8E6D0','4ADE80'),
        'ocean-breeze'   => array('F0F9FF','0C4A6E','334155','0284C7'),
        'golden-hour'    => array('1C1408','FFFFFF','F5DEB3','F59E0B'),
        'lavender-mist'  => array('F5F3FF','3B0764','4C1D95','8B5CF6'),
        'slate-minimal'  => array('F8F9FA','1E293B','475569','64748B'),
        'cherry-bold'    => array('FFF5F5','7F1D1D','450A0A','DC2626'),
        'midnight-teal'  => array('042F2E','FFFFFF','99F6E4','2DD4BF'),
        'royal-purple'   => array('1E0A3C','FFFFFF','DDD6FE','A855F7'),
        'steel-blue'     => array('0F172A','F1F5F9','94A3B8','38BDF8'),
        'amber-warm'     => array('1C1917','FEF3C7','D6D3D1','FBBF24'),
        'crimson-night'  => array('1A0A0A','FFFFFF','FCA5A5','EF4444'),
        // Gradient themes — use darker end color for PPTX solid bg
        'aurora'         => array('0F2027','FFFFFF','B8D4DE','4FD1C5'),
        'sunset-blaze'   => array('7B0000','FFFFFF','FFE0E9','FFD700'),
        'cosmic-purple'  => array('1A0050','FFFFFF','E0D4FF','00E5FF'),
        'ocean-depth'    => array('000028','FFFFFF','CDEFFF','FFD93D'),
        'emerald-dusk'   => array('0A2E1A','FFFFFF','DFF5E8','FFC857'),
        'rose-gold-glow' => array('0D0720','FFFFFF','FFD9E6','FFD700'),
        'amber-blaze'    => array('1A0A00','FFFFFF','FFD9A0','FFA500'),
        'midnight-bloom' => array('0B0020','FFFFFF','D9F7FA','F72585'),
        'deep-navy'      => array('0A1628','FFFFFF','C8D8F0','4A9EFF'),
        'dark-crimson'   => array('1A0000','FFFFFF','FFD0D0','FF6666'),
        'dark-slate'     => array('0F172A','F1F5F9','94A3B8','38BDF8'),
        'dark-forest'    => array('052010','FFFFFF','C8E6D0','4ADE80'),
    );
    $t = isset($themes[$template]) ? $themes[$template] : $themes['navy-pro'];
    list($bg, $tc, $bc, $ac) = $t;
    error_log('DS12B BUILD: template=' . $template . ' bg=' . $bg . ' tc=' . $tc);

    $upload  = wp_upload_dir();
    $dl_dir  = $upload['basedir'] . '/ds-ppt-downloads';
    wp_mkdir_p($dl_dir);
    $safe    = sanitize_file_name($title);
    $fname   = 'ds-ppt-' . $safe . '-' . time() . '.pptx';
    $fpath   = $dl_dir . '/' . $fname;
    $furl    = $upload['baseurl'] . '/ds-ppt-downloads/' . $fname;

    $zip = new ZipArchive();
    if ( $zip->open($fpath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true ) {
        return rest_ensure_response(array('success'=>false,'error'=>'Cannot create file on server.'));
    }

    $n = count($slides);

    // [Content_Types].xml
    $ct_slides = '';
    for ($i = 1; $i <= $n; $i++) {
        $ct_slides .= '<Override PartName="/ppt/slides/slide'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
    }
    $ovr = function($p,$c){return '<Override PartName="/ppt/'.$p.'" ContentType="application/vnd.openxmlformats-officedocument.'.$c.'+xml"/>';};
    $zip->addFromString('[Content_Types].xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
        '<Default Extension="xml" ContentType="application/xml"/>'.
        '<Default Extension="png" ContentType="image/png"/>'.
        '<Default Extension="jpeg" ContentType="image/jpeg"/>'.
        '<Default Extension="jpg" ContentType="image/jpeg"/>'.
        $ovr('presentation.xml','presentationml.presentation.main').
        $ovr('presProps.xml','presentationml.presProps').
        $ovr('viewProps.xml','presentationml.viewProps').
        $ovr('tableStyles.xml','presentationml.tableStyles').
        '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>'.
        $ovr('slideMasters/slideMaster1.xml','presentationml.slideMaster').
        $ovr('slideLayouts/slideLayout1.xml','presentationml.slideLayout').
        $ct_slides.
        '</Types>');

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'.
        '</Relationships>');

    $sld_rels = '';
    for ($i = 1; $i <= $n; $i++) {
        $sld_rels .= '<Relationship Id="rId'.($i+5).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide'.$i.'.xml"/>';
    }
    $zip->addFromString('ppt/_rels/presentation.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>'.
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/presProps" Target="presProps.xml"/>'.
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/viewProps" Target="viewProps.xml"/>'.
        '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>'.
        '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/tableStyles" Target="tableStyles.xml"/>'.
        $sld_rels.
        '</Relationships>');

    $sld_id_lst = '';
    for ($i = 1; $i <= $n; $i++) {
        $sld_id_lst .= '<p:sldId id="'.($i+255).'" r:id="rId'.($i+5).'"/>';
    }
    $zip->addFromString('ppt/presentation.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'.
        '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'.
        '<p:sldIdLst>'.$sld_id_lst.'</p:sldIdLst>'.
        '<p:sldSz cx="'.$sld_cx.'" cy="'.$sld_cy.'" type="'.$sld_type.'"/>'.
        '<p:notesSz cx="6858000" cy="9144000"/>'.
        '<p:defaultTextStyle>'.
        '<a:defPPr><a:defRPr lang="en-US"/></a:defPPr>'.
        '<a:lvl1pPr marL="0" algn="l" defTabSz="457200" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1">'.
        '<a:defRPr sz="1800" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill>'.
        '<a:latin typeface="+mn-lt"/></a:defRPr></a:lvl1pPr>'.
        '</p:defaultTextStyle>'.
        '</p:presentation>');

    $zip->addFromString('ppt/presProps.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:presentationPr xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"/>');

    $zip->addFromString('ppt/viewProps.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:viewPr xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'.
        '<p:normalViewPr><p:restoredLeft sz="15620"/><p:restoredTop sz="94660"/></p:normalViewPr>'.
        '<p:slideViewPr><p:cSldViewPr><p:cViewPr varScale="1">'.
        '<p:scale><a:sx n="100" d="100"/><a:sy n="100" d="100"/></p:scale>'.
        '<p:origin x="0" y="0"/></p:cViewPr></p:cSldViewPr></p:slideViewPr>'.
        '</p:viewPr>');

    $zip->addFromString('ppt/tableStyles.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<a:tblStyleLst xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' def="{5C22544A-7EE6-4342-B048-85BDC9FD1C3A}"/>');

    // Theme — Calibri = major + minor (system-installed everywhere, like Gamma uses Mona Sans)
    $sf = '<a:solidFill><a:schemeClr val="phClr"/></a:solidFill>';
    $clr = function($k,$v){return '<a:'.$k.'><a:srgbClr val="'.$v.'"/></a:'.$k.'>';};
    $zip->addFromString('ppt/theme/theme1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="DS">'.
        '<a:themeElements><a:clrScheme name="DS">'.
        $clr('dk1',$bg).$clr('lt1',$tc).$clr('dk2',$bc).$clr('lt2',$ac).
        $clr('accent1',$ac).$clr('accent2',$ac).$clr('accent3',$ac).
        $clr('accent4',$ac).$clr('accent5',$ac).$clr('accent6',$ac).
        $clr('hlink',$ac).$clr('folHlink',$ac).
        '</a:clrScheme>'.
        '<a:fontScheme name="DS">'.
        '<a:majorFont><a:latin typeface="Arial Black" pitchFamily="34" charset="0"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont>'.
        '<a:minorFont><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont>'.
        '</a:fontScheme>'.
        '<a:fmtScheme name="DS">'.
        '<a:fillStyleLst>'.$sf.$sf.$sf.'</a:fillStyleLst>'.
        '<a:lnStyleLst>'.
        '<a:ln w="6350">'.$sf.'</a:ln><a:ln w="12700">'.$sf.'</a:ln><a:ln w="19050">'.$sf.'</a:ln>'.
        '</a:lnStyleLst>'.
        '<a:effectStyleLst><a:effectStyle><a:effectLst/></a:effectStyle><a:effectStyle><a:effectLst/></a:effectStyle><a:effectStyle><a:effectLst/></a:effectStyle></a:effectStyleLst>'.
        '<a:bgFillStyleLst>'.$sf.$sf.$sf.'</a:bgFillStyleLst>'.
        '</a:fmtScheme></a:themeElements></a:theme>');

    $zip->addFromString('ppt/slideMasters/slideMaster1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:sldMaster xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'.
        '<p:cSld>'.ds_ppt_bg_xml($bg, $template).'<a:effectLst/>'.
        '<p:spTree>'.
        '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
        '<p:grpSpPr/>'.
        '</p:spTree></p:cSld>'.
        '<p:clrMap bg1="dk1" tx1="lt1" bg2="dk2" tx2="lt2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'.
        '<p:sldLayoutIdLst><p:sldLayoutId id="2147493748" r:id="rId1"/></p:sldLayoutIdLst>'.
        '<p:txStyles>'.
        '<p:titleStyle><a:lvl1pPr><a:defRPr sz="4800" b="1"><a:solidFill><a:srgbClr val="'.$tc.'"/></a:solidFill><a:latin typeface="Arial Black" pitchFamily="34" charset="0"/></a:defRPr></a:lvl1pPr></p:titleStyle>'.
        '<p:bodyStyle><a:lvl1pPr><a:defRPr sz="1900"><a:solidFill><a:srgbClr val="'.$bc.'"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/></a:defRPr></a:lvl1pPr></p:bodyStyle>'.
        '<p:otherStyle/>'.
        '</p:txStyles>'.
        '</p:sldMaster>');

    $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'.
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="../theme/theme1.xml"/>'.
        '</Relationships>');

    $zip->addFromString('ppt/slideLayouts/slideLayout1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:sldLayout xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" type="blank" preserve="1">'.
        '<p:cSld name="Blank">'.
        '<p:spTree>'.
        '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
        '<p:grpSpPr/>'.
        '</p:spTree></p:cSld>'.
        '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'.
        '</p:sldLayout>');

    $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'.
        '</Relationships>');

	// Marketplace-picked images: deduct from the user's free/pro quota and
    // swap in the real, unwatermarked original ONLY now, at actual file
    // download time -- picking/editing a slide never deducts anything.
    $mkt_unlocked = 0;
    $mkt_locked   = 0;
    foreach ( $slides as $idx => $slide ) {
        $mkt_id   = isset($slide['image_market_id'])   ? (int) $slide['image_market_id']   : 0;
        $mkt_tier = isset($slide['image_market_tier']) ? sanitize_text_field($slide['image_market_tier']) : '';
        if ( ! $mkt_id || ! in_array($mkt_tier, array('free', 'pro'), true) ) continue;
		// New flow: real file already in image_data and quota already
        // deducted at pick time — nothing to do at build time.
        if ( ! empty($slide['image_mkt_fetched']) ) { $mkt_unlocked++; continue; }
        if ( ! function_exists('ds_dl_check_core') || ! function_exists('ds_mkt_get_unwatermarked_file_bytes') ) continue;

        $check = ds_dl_check_core( get_current_user_id(), $mkt_tier );
        if ( empty($check['ok']) ) { $mkt_locked++; continue; }

        $real = ds_mkt_get_unwatermarked_file_bytes( $mkt_id );
        if ( $real && ! empty($real['bytes']) ) {
            $slides[$idx]['image_data'] = 'data:' . $real['mime'] . ';base64,' . base64_encode($real['bytes']);
            $mkt_unlocked++;
        } else {
            $mkt_locked++;
        }
    }


    // Pre-fetched AI images (data URIs from frontend)
    $slide_images = array();
    foreach ($slides as $idx => $slide) {
        $stype = $slide['type'] ?? 'bullet';
        if ($stype !== 'image_text') continue;
        $data_uri = trim((string)($slide['image_data'] ?? ''));
        if ( strpos($data_uri, 'data:image/') !== 0 ) continue;
        $parts = explode(',', $data_uri, 2);
        if ( count($parts) !== 2 ) continue;
        $bytes = base64_decode($parts[1]);
        if ( empty($bytes) || strlen($bytes) < 1000 ) continue;
        $ext = null;
        $b0 = ord($bytes[0]); $b1 = ord($bytes[1]);
        $b2 = ord($bytes[2]); $b3 = ord($bytes[3]);
        if ($b0 === 0x89 && $b1 === 0x50 && $b2 === 0x4E && $b3 === 0x47) $ext = 'png';
        elseif ($b0 === 0xFF && $b1 === 0xD8 && $b2 === 0xFF) $ext = 'jpg';
        if ( $ext === null ) continue;
        $img_w = 0; $img_h = 0;
if ( function_exists('getimagesizefromstring') ) {
    $sz = @getimagesizefromstring($bytes);
    if ( $sz ) { $img_w = $sz[0]; $img_h = $sz[1]; }
}
$slide_images[$idx] = array('bytes' => $bytes, 'ext' => $ext, 'w' => $img_w, 'h' => $img_h);
    }

    // Pre-fetch Iconify icons for slides that use them
    $slide_icon_rids = array(); // idx => array of rIds for icons
    $slide_icon_rels = array(); // idx => relationship XML strings
    $icon_types_needing = array('feature_grid','five_point','six_point','seven_point','eight_point','two_column','three_column','bullet','image_text');
    foreach ($slides as $idx => $slide) {
        $stype = $slide['type'] ?? 'bullet';
        if ( ! in_array($stype, $icon_types_needing, true) ) continue;
        $icons_list = isset($slide['icons']) && is_array($slide['icons']) ? $slide['icons'] : array();
        // For bullet/image_text use default icons if none provided
        if ( empty($icons_list) ) {
            $defaults = array('mdi:lightbulb','mdi:chart-bar','mdi:target','mdi:check-circle','mdi:rocket-launch','mdi:star');
            $cnt = count($slide['content'] ?? array());
            $icons_list = array_slice($defaults, 0, max($cnt, 4));
        }
        $rids = array();
        $rels = array();
        $icon_rid_start = 20; // start from rId20 to avoid conflicts
        foreach ($icons_list as $ii => $icon_name) {
            $icon_name = sanitize_text_field((string)$icon_name);
            if ( empty($icon_name) || strpos($icon_name, ':') === false ) continue;
            $result = ds_ppt_fetch_iconify($icon_name, 'FFFFFF', 128);
            if ( ! $result ) continue;
            $png_bytes = null;
            if ( is_array($result) && isset($result['svg']) ) {
                $png_bytes = ds_ppt_svg_to_png($result['svg'], 128);
            } elseif ( is_string($result) ) {
                $png_bytes = $result;
            }
            if ( empty($png_bytes) || strlen($png_bytes) < 100 ) continue;
            $rid_num = $icon_rid_start + $ii;
            $rid = 'rId'.$rid_num;
            $img_file = 'icon_'.$idx.'_'.$ii.'.png';
            $zip->addFromString('ppt/media/'.$img_file, $png_bytes);
            $rids[$ii] = $rid;
            $rels[] = '<Relationship Id="'.$rid.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$img_file.'"/>';
        }
        if ( ! empty($rids) ) {
            $slide_icon_rids[$idx] = $rids;
            $slide_icon_rels[$idx] = $rels;
        }
    }

    // Build slides
    foreach ($slides as $idx => $slide) {
        $sn = $idx + 1;
        $type = $slide['type'] ?? 'bullet';
        if ( $type === '_promo_cta' ) {
            $zip->addFromString('ppt/media/promo-mail.png', base64_decode(DS_PROMO_ICON_MAIL_B64));
            $zip->addFromString('ppt/media/promo-globe.png', base64_decode(DS_PROMO_ICON_GLOBE_B64));
            $zip->addFromString('ppt/media/promo-check.png', base64_decode(DS_PROMO_ICON_CHECK_B64));

            $promo_slide_xml =
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
                '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
                ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'.
                ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
                '<p:cSld>'.
                '<p:bg><p:bgPr><a:solidFill><a:srgbClr val="0E1A33"/></a:solidFill></p:bgPr></p:bg>'.
                '<p:spTree>'.
                '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
                '<p:grpSpPr><a:xfrm>'.
                '<a:off x="0" y="0"/>'.
                '<a:ext cx="'.$sld_cx.'" cy="'.$sld_cy.'"/>'.
                '<a:chOff x="0" y="0"/>'.
                '<a:chExt cx="12192000" cy="6858000"/>'.
                '</a:xfrm></p:grpSpPr>'.
                DS_PROMO_SLIDE_BODY_XML.
                '</p:spTree>'.
                '</p:cSld>'.
                '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'.
                '</p:sld>';

            $zip->addFromString('ppt/slides/slide'.$sn.'.xml', $promo_slide_xml);
            $zip->addFromString('ppt/slides/_rels/slide'.$sn.'.xml.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
                '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-mail.png"/>'.
                '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-globe.png"/>'.
                '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>'.
                '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>'.
                '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>'.
                '<Relationship Id="rId6" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>'.
                '<Relationship Id="rId7" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>'.
                '<Relationship Id="rId8" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>'.
                '<Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'.
                '</Relationships>');
            continue;
        }
		
        $image_rid = null;
        $slide_rels_extra = '';
        if ( isset($slide_images[$idx]) ) {
            $img = $slide_images[$idx];
            $img_filename = 'image'.$sn.'.'.$img['ext'];
            $zip->addFromString('ppt/media/'.$img_filename, $img['bytes']);
            $image_rid = 'rId10';
            $slide_rels_extra =
                '<Relationship Id="rId10" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$img_filename.'"/>';
        }

        $icon_rids_for_slide = isset($slide_icon_rids[$idx]) ? $slide_icon_rids[$idx] : array();
        if ( ! function_exists('ds_render_slide_body') ) {
            $zip->close();
            return rest_ensure_response(array('success'=>false,'error'=>'Renderer snippet (12a) not active.'));
        }
        if ( ! function_exists('ds_render_slide_body') ) {
    $zip->close();
    return rest_ensure_response(array('success'=>false,'error'=>'12a not loaded. Please deactivate and reactivate snippet 12a.'));
}
$img_w = isset($slide_images[$idx]['w']) ? $slide_images[$idx]['w'] : 0;
$img_h = isset($slide_images[$idx]['h']) ? $slide_images[$idx]['h'] : 0;
$img_pos = isset($slide['image_position']) && is_array($slide['image_position']) ? $slide['image_position'] : null;
if(!isset($GLOBALS['_dsv_inline_debug']))$GLOBALS['_dsv_inline_debug']=array();
$GLOBALS['_dsv_inline_debug'][]=array('idx'=>$idx,'type'=>$type,'slide_variant'=>(isset($slide['variant'])?$slide['variant']:'MISSING'),'slide_keys'=>array_keys($slide));
$body_xml = ds_render_slide_body($type, $slide, $tc, $bc, $ac, $bg, $image_rid, $icon_rids_for_slide, $img_pos, $img_w, $img_h);

        $slide_xml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
            ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'.
            ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
            '<p:cSld>'.
            ds_ppt_bg_xml($bg, $template).
            '<p:spTree>'.
            '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
            '<p:grpSpPr><a:xfrm>'.
            '<a:off x="0" y="0"/>'.
            '<a:ext cx="'.$sld_cx.'" cy="'.$sld_cy.'"/>'.
            '<a:chOff x="0" y="0"/>'.
            '<a:chExt cx="12192000" cy="6858000"/>'.
            '</a:xfrm></p:grpSpPr>'. 
            $body_xml.
            '</p:spTree>'.
            '</p:cSld>'.
            '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'.
            '</p:sld>';

        $zip->addFromString('ppt/slides/slide'.$sn.'.xml', $slide_xml);
        $icon_rels_str = isset($slide_icon_rels[$idx]) ? implode('', $slide_icon_rels[$idx]) : '';
        $zip->addFromString('ppt/slides/_rels/slide'.$sn.'.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'.
            $slide_rels_extra.
            $icon_rels_str.
            '</Relationships>');
    }

    $zip->close();

    if ( ! file_exists($fpath) || filesize($fpath) < 500 ) {
        return rest_ensure_response(array('success'=>false,'error'=>'PPTX file creation failed.'));
    }
    // Record this built PPTX in the user's own 'My Creations' list (previously
    // untracked — anyone with the URL could access it, and it never showed up
    // anywhere in the account). Best-effort: never blocks the download on failure.
    $ds_ppt_build_uid = get_current_user_id();
    /* a download is not a saved item: the file is handed to the user and removed from the server after an hour */
    wp_schedule_single_event( time() + 3600, 'ds_ppt_unlink_tmp', array( $fpath ) );
    if ( false ) {
        $ds_ppt_built = get_user_meta( $ds_ppt_build_uid, 'ds_ppt_built_files', true );
        if ( ! is_array( $ds_ppt_built ) ) $ds_ppt_built = array();
        $ds_ppt_built[] = array(
            'id'       => wp_generate_uuid4(),
            'title'    => $title,
            'filename' => $safe . '.pptx',
            'url'      => $furl,
            'path'     => $fpath,
            'size'     => file_exists($fpath) ? filesize($fpath) : 0,
            'date'     => current_time( 'mysql' ),
        );
        update_user_meta( $ds_ppt_build_uid, 'ds_ppt_built_files', $ds_ppt_built );
    }

    return rest_ensure_response(array(
        'success'      => true,
        'url'          => $furl,
        'filename'     => $safe . '.pptx',
        'images'       => count($slide_images),
        'mkt_unlocked' => $mkt_unlocked,
        'mkt_locked'   => $mkt_locked,
    'dsv_debug' => (isset($GLOBALS['_dsv_inline_debug']) ? $GLOBALS['_dsv_inline_debug'] : null),
    ));
}
}

add_action('rest_api_init', function () {
/*
 * REMOVED in 16.1 — 19 leftover debug/introspection routes, every one of them
 * registered with permission_callback => '__return_true' (callable by anyone,
 * logged in or not). They read plugin source, reset opcache and purged the
 * whole site cache, and they all targeted the Code Snippets plugin, which is
 * no longer used. Removed:
 *
 *   ds/v1/ppt-debug-post
 *   ds/v1/ppt-debug-last
 *   ds/v1/ppt-debug-call
 *   ds/v1/ppt-debug-entry
 *   ds/v1/ppt-opcache-reset
 *   ds/v1/ppt-wpcache-flush
 *   ds/v1/ppt-reflect
 *   ds/v1/ppt-plugin-src
 *   ds/v1/ppt-plugin-src2
 *   ds/v1/ppt-cs-cache-clear
 *   ds/v1/ppt-cs-cache-clear2
 *   ds/v1/ppt-route-introspect
 *   ds/v1/ppt-plugin-src3
 *   ds/v1/ppt-cache-probe
 *   ds/v1/ppt-grep-scope
 *   ds/v1/ppt-grep-scope2
 *   ds/v1/ppt-eval-src
 *   ds/v1/ppt-cache-clear3
 *   ds/v1/ppt-route-introspect-v2
 *
 * The DS Security Guard module was blocking these from non-admins; they are
 * now gone entirely. The two real routes below are unchanged.
 */

register_rest_route('ds/v1', '/my-built-ppts', array(
    'methods'  => 'GET',
    'permission_callback'  => function() { return is_user_logged_in(); },
    'callback'  => function(WP_REST_Request $req) {
        $uid = get_current_user_id();
        $built = get_user_meta( $uid, 'ds_ppt_built_files', true );
        if ( ! is_array( $built ) ) $built = array();
        $out = array();
        foreach ( $built as $b ) {
            if ( empty($b['path']) || ! file_exists($b['path']) ) continue;
            $out[] = array(
                'id' => $b['id'],
                'title' => $b['title'],
                'filename' => $b['filename'],
                'url' => $b['url'],
                'size' => $b['size'],
                'date' => $b['date'],
            );
        }
        $__rev = array_reverse($out);
        $__total = count($__rev);
        $__offset = max(0, (int) $req->get_param('offset'));
        $__limit = (int) $req->get_param('limit');
        if ($__limit <= 0) $__limit = 20;
        $__slice = array_slice($__rev, $__offset, $__limit);
        return rest_ensure_response(array('success'=>true,'files'=>$__slice,'total'=>$__total,'next_offset'=>$__offset+count($__slice)));
    },
));

register_rest_route('ds/v1', '/delete-built-ppt', array(
    'methods'  => 'POST',
    'permission_callback'  => function() { return is_user_logged_in(); },
    'callback'  => function(WP_REST_Request $req) {
        $uid = get_current_user_id();
        $del_id = sanitize_text_field( $req->get_param('id') ?: '' );
        if ( empty($del_id) ) return new WP_Error('ds_ppt_bad_request','Missing id.',array('status'=>400));
        $built = get_user_meta( $uid, 'ds_ppt_built_files', true );
        if ( ! is_array( $built ) ) $built = array();
        $found = false; $keep = array();
        foreach ( $built as $b ) {
            if ( isset($b['id']) && $b['id'] === $del_id ) {
                $found = true;
                if ( ! empty($b['path']) && file_exists($b['path']) ) @unlink($b['path']);
            } else {
                $keep[] = $b;
            }
        }
        if ( ! $found ) return new WP_Error('ds_ppt_not_found','Not found.',array('status'=>404));
        update_user_meta( $uid, 'ds_ppt_built_files', $keep );
        return rest_ensure_response(array('success'=>true));
    },
));
});
