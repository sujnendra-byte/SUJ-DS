
(function(){
'use strict';

function splitColon(line){
  var i=line.indexOf(':');
  if(i===-1)return{h:line.trim(),d:''};
  return{h:line.slice(0,i).trim(),d:line.slice(i+1).trim()};
}

function detectType(items, slideTitle, slideIndex){
  if(!items.length)return slideIndex===0?'title':'bullet';
  var n=items.length;
  var t=String(slideTitle||'').toLowerCase();

  if(/timeline|history|roadmap|milestone|phase|quarter|q[1-4]\b|schedule/i.test(t)) return 'timeline';
  if(/metric|stat|kpi|number|result|figure|rate|percentage|score|projection|forecast/i.test(t)) return 'stats';
  if(/\bvs\.?\b|versus|compar|pro.?con|advantage|disadvantage/i.test(t)) return 'two_column';
  if(/step|process|workflow|method|pillar|approach|framework|how.to/i.test(t)) return 'three_column';
  if(/feature|benefit|service|offering|capability|why.us|what.we|choose/i.test(t)) return 'feature_grid';
  if(/quote|testimonial|said|according|feedback/i.test(t)) return 'quote';

  var allColon=items.every(function(it){return it.indexOf(':')!==-1;});
  if(allColon){
    var heads=items.map(function(it){return splitColon(it).h;});
    var allNumeric=heads.every(function(h){return /^[\$€£]?[\d.,]+\s*[%MKBx+]*$/i.test(h);});
    if(allNumeric && n<=5) return 'stats';
    var allYearish=heads.every(function(h){return /^(19|20)\d{2}|^(Q[1-4]|Phase|Step|Stage|Year|Week|Month)\b/i.test(h);});
    if(allYearish) return 'timeline';
    if(n===2) return 'two_column';
    if(n===3) return 'three_column';
    if(n>=4) return 'feature_grid';
  }

  if(n===2) return 'two_column';
  if(n===3) return 'three_column';
  if(n===4) return 'feature_grid';
  if(n>=5 && slideIndex%3===2) return 'image_text';
  return 'bullet';
}

var _ICONS={
  'revenue':'mdi:cash','profit':'mdi:cash','cost':'mdi:cash','pricing':'mdi:cash',
  'affordable':'mdi:cash','money':'mdi:cash','budget':'mdi:cash',
  'team':'mdi:account-group','support':'mdi:account-group','customer':'mdi:account',
  'user':'mdi:account','community':'mdi:account-group','client':'mdi:account',
  'ai':'mdi:brain','artificial':'mdi:brain','digital':'mdi:cog','platform':'mdi:cog',
  'software':'mdi:cog','tech':'mdi:cog','tool':'mdi:cog','technology':'mdi:cog',
  'automation':'mdi:lightning-bolt','lightning':'mdi:lightning-bolt',
  'growth':'mdi:trending-up','increase':'mdi:trending-up','improve':'mdi:trending-up',
  'scale':'mdi:trending-up','expand':'mdi:trending-up','sales':'mdi:trending-up',
  'marketing':'mdi:trending-up','conversion':'mdi:trending-up',
  'security':'mdi:shield-check','trust':'mdi:shield-check','safe':'mdi:shield-check',
  'protect':'mdi:shield-check','brand':'mdi:shield-check','branding':'mdi:shield-check',
  'goal':'mdi:target','strategy':'mdi:target','focus':'mdi:target',
  'solution':'mdi:target','objective':'mdi:target',
  'data':'mdi:chart-bar','analytics':'mdi:chart-bar','metric':'mdi:chart-bar',
  'chart':'mdi:chart-bar','statistics':'mdi:chart-bar','report':'mdi:chart-bar',
  'presentation':'mdi:chart-bar','slide':'mdi:chart-bar','deck':'mdi:chart-bar',
  'global':'mdi:earth','worldwide':'mdi:earth','web':'mdi:earth',
  'website':'mdi:earth','online':'mdi:earth','market':'mdi:earth',
  'quality':'mdi:star','premium':'mdi:star','professional':'mdi:star',
  'best':'mdi:star','excellent':'mdi:star',
  'success':'mdi:check-circle','complete':'mdi:check-circle','achieve':'mdi:check-circle',
  'launch':'mdi:rocket-launch','startup':'mdi:rocket-launch','start':'mdi:rocket-launch',
  'care':'mdi:heart','health':'mdi:heart','wellness':'mdi:heart',
  'learn':'mdi:book-open','education':'mdi:book-open','training':'mdi:book-open',
  'knowledge':'mdi:book-open','library':'mdi:book-open','template':'mdi:book-open',
  'time':'mdi:clock-outline','schedule':'mdi:clock-outline','quick':'mdi:clock-outline',
  'design':'mdi:sparkles','creative':'mdi:sparkles','visual':'mdi:sparkles',
  'graphic':'mdi:sparkles','art':'mdi:sparkles','logo':'mdi:sparkles',
  'video':'mdi:play-circle','media':'mdi:play-circle',
  'award':'mdi:trophy','winner':'mdi:trophy','achievement':'mdi:trophy',
  'partner':'mdi:handshake','collaborate':'mdi:handshake',
  'email':'mdi:email','contact':'mdi:email','communication':'mdi:email',
  'plan':'mdi:calendar','roadmap':'mdi:calendar',
  'privacy':'mdi:lock','password':'mdi:lock','secure':'mdi:lock',
  'popular':'mdi:fire','trending':'mdi:fire',
  'business':'mdi:briefcase','work':'mdi:briefcase','enterprise':'mdi:briefcase',
  'corporate':'mdi:briefcase','company':'mdi:briefcase',
  'sustainable':'mdi:leaf','green':'mdi:leaf','eco':'mdi:leaf',
  'puzzle':'mdi:puzzle','integrate':'mdi:puzzle',
  'low':'mdi:trending-up','high':'mdi:star','poor':'mdi:target',
  'challenge':'mdi:target','problem':'mdi:target','issue':'mdi:target',
  'consistent':'mdi:shield-check','inconsistent':'mdi:shield-check',
  'competitive':'mdi:trophy','advantage':'mdi:trophy',
  'large':'mdi:earth','access':'mdi:earth','explore':'mdi:earth',
  'easy':'mdi:check-circle','simple':'mdi:check-circle','flexible':'mdi:check-circle',
  'dedicated':'mdi:heart','friendly':'mdi:heart','assist':'mdi:account-group',
  'custom':'mdi:sparkles','personali':'mdi:sparkles',
  'grow':'mdi:trending-up','transform':'mdi:rocket-launch','build':'mdi:rocket-launch',
  'image':'mdi:sparkles','generate':'mdi:brain','powered':'mdi:brain',
  'request':'mdi:email','submit':'mdi:email',
  'subscription':'mdi:cash','right':'mdi:check-circle','choose':'mdi:target'
};
var _FALLBACK_ICONS=['mdi:lightbulb','mdi:chart-bar','mdi:target','mdi:check-circle','mdi:rocket-launch','mdi:star'];

function _pickIcon(text){
  var lower=String(text||'').toLowerCase();
  var words=lower.split(/[\s\-_:,]+/);
  for(var i=0;i<words.length;i++){
    var w=words[i].replace(/[^a-z]/g,'');
    if(_ICONS[w]) return _ICONS[w];
  }
  for(var key in _ICONS){
    if(lower.indexOf(key)!==-1) return _ICONS[key];
  }
  return null;
}

var _EMOJIS={
  'cost':'💰','pricing':'💰','affordable':'💰','money':'💰','budget':'💰','expensive':'💰','revenue':'💰','profit':'💰',
  'team':'👥','partner':'🤝','collaborate':'🤝','support':'🤝','client':'👤','customer':'👤','user':'👤','community':'👥',
  'ai':'🤖','artificial':'🤖','generate':'🤖','powered':'🤖','digital':'💻','platform':'💻','software':'💻','tech':'⚙️','tool':'⚙️','technology':'⚙️',
  'growth':'📈','increase':'📈','improve':'📈','scale':'📈','sales':'📈','expand':'📈','conversion':'📈','high':'📈',
  'marketing':'📣','brand':'🎨','branding':'🎨','design':'🎨','creative':'🎨','visual':'🎨','graphic':'🎨','art':'🎨','logo':'🎨','custom':'✨','personali':'✨','tailored':'✨',
  'security':'🛡','trust':'🛡','safe':'🛡','protect':'🛡','privacy':'🔒','secure':'🔒','consistent':'✅','inconsistent':'⚠️',
  'goal':'🎯','strategy':'🎯','focus':'🎯','solution':'🎯','objective':'🎯','target':'🎯',
  'data':'📊','analytics':'📊','metric':'📊','report':'📊','statistics':'📊','quality':'⭐','premium':'⭐','professional':'⭐','best':'⭐','excellent':'⭐',
  'presentation':'📋','slide':'📋','deck':'📋','template':'📚','templates':'📚','library':'📚','collection':'📚','learn':'📚','education':'📚','training':'📚','knowledge':'📚',
  'global':'🌐','website':'🌐','online':'🌐','market':'🌐','web':'🌐','marketplace':'🏪','access':'🔍','explore':'🔍','browse':'🔍',
  'success':'✅','complete':'✅','achieve':'✅','easy':'✅','simple':'✅','flexible':'✅',
  'launch':'🚀','startup':'🚀','start':'🚀','transform':'🚀','build':'🚀',
  'care':'❤','health':'❤','wellness':'❤','dedicated':'❤','friendly':'❤',
  'time':'⏰','consuming':'⏰','schedule':'📅','quick':'⚡','fast':'⚡','speed':'⚡',
  'video':'🎬','media':'🎬','social':'📱','image':'🖼',
  'email':'📧','contact':'📧','communication':'📧','request':'📧',
  'subscription':'💳','plan':'📅','roadmap':'📅',
  'popular':'🔥','trending':'🔥','competitive':'🏆','advantage':'🏆','award':'🏆','winner':'🏆',
  'business':'💼','work':'💼','enterprise':'💼','corporate':'💼','company':'💼',
  'sustainable':'🌱','green':'🌱','eco':'🌱',
  'poor':'⚠️','challenge':'⚠️','problem':'⚠️','issue':'⚠️','low':'📉','decrease':'📉','reduce':'📉','weak':'⚠️',
  'idea':'💡','innovation':'💡','create':'💡','insight':'💡',
  'large':'📚','thousands':'📚',
  'grow':'🌱','assistance':'💬','service':'💎','services':'💎'
};

function _pickEmoji(text){
  var lower=String(text||'').toLowerCase();
  var words=lower.split(/[\s\-_:,]+/);
  for(var i=0;i<words.length;i++){var w=words[i].replace(/[^a-z]/g,'');if(_EMOJIS[w])return _EMOJIS[w];}
  for(var key in _EMOJIS){if(lower.indexOf(key)!==-1)return _EMOJIS[key];}
  return null;
}

var _FB_EMOJI=['💡','📊','🎯','✅','🚀','⭐'];

function _assignIcons(slide){
  if(!slide.content||!slide.content.length) return slide;
  var icons=[],emojis=[];
  slide.content.forEach(function(item,i){
    var colon=item.indexOf(':');
    var heading=colon>0?item.slice(0,colon).trim():item;
    icons.push(_pickIcon(heading)||_FALLBACK_ICONS[i%_FALLBACK_ICONS.length]);
    emojis.push(_pickEmoji(heading)||_FB_EMOJI[i%_FB_EMOJI.length]);
  });
  slide.icons=icons;
  slide.emojis=emojis;
  if(slide.title){slide.icon=_pickIcon(slide.title)||'mdi:lightbulb';slide.icon_emoji=_pickEmoji(slide.title)||'💡';}
  return slide;
}

var _SKIP=/^(key\s+point|key\s+feature|key\s+takeaway|main\s+point|highlight|overview|summary|agenda|table\s+of\s+content|contact\s+us|get\s+in\s+touch|questions?\??|thank\s+you|next\s+step|action\s+item|note|tip)/i;

function dspPreserveParse(text){
  var blocks=[];
  var raw=text.split(/(?=(?:^|\n)\s*Slide\s+\d+\s*:)/i).map(function(s){return s.trim();}).filter(Boolean);
  if(raw.length>1||(raw.length===1&&/^Slide\s+\d+\s*:/i.test(raw[0]))){
    blocks=raw;
  } else {
    var mdSplit=text.split(/(?=^#{1,3}\s+.+$)/m).map(function(s){return s.trim();}).filter(Boolean);
    if(mdSplit.length>1){
      blocks=mdSplit;
    } else {
      blocks=text.split(/\n\s*\n/).map(function(s){return s.trim();}).filter(function(s){return s.length>5;});
    }
  }

  var slides=[];
  blocks.forEach(function(block,bi){
    var lines=block.split('\n').map(function(l){return l.trim();}).filter(Boolean);
    if(!lines.length)return;

    var heading=lines[0]
      .replace(/^Slide\s+\d+\s*:?\s*/i,'')
      .replace(/^#{1,3}\s*/,'')
      .replace(/^[-*]\s*/,'')
      .trim();
    var rest=lines.slice(1).map(function(l){
      return l.replace(/^[-*•►✓]\s*/,'').replace(/^\d+[.)]\s*/,'').trim();
    }).filter(function(l){return l.length>1;});

    if(/^(title|untitled|slide)$/i.test(heading)&&rest.length){
      heading=rest.shift();
    }
    if(!heading)return;

    var subtitle='';
    if(rest.length&&rest[0].indexOf(':')===-1&&rest[0].split(' ').length<=14&&!_SKIP.test(rest[0])){
      subtitle=rest.shift();
    }

        var _titleFields=[];
    if(bi===0){
      var _TF_MAP={'subtitle':'subtitle','tagline':'tagline','company':'company','organization':'company','org':'company','presenter':'presenter','presented by':'presenter','by':'presenter','author':'presenter','speaker':'presenter','presented':'presenter','designation':'designation','role':'designation','position':'designation','date':'date','website':'website','site':'website','url':'website','web':'website','confidential':'confidential'};
      var _kept=[];
      rest.forEach(function(l){
        var ci=l.indexOf(':');
        if(ci>0){
          var lbl=l.slice(0,ci).trim().toLowerCase();
          var val=l.slice(ci+1).trim();
          if(_TF_MAP.hasOwnProperty(lbl)){
            var canon=_TF_MAP[lbl];
            if(canon==='subtitle'){
              if(!subtitle)subtitle=val;
            } else if(canon==='presenter'&&val.indexOf(',')!==-1){
              var _pp=val.split(',');
              _titleFields.push('presenter: '+_pp[0].trim());
              _titleFields.push('designation: '+_pp.slice(1).join(',').trim());
            } else {
              _titleFields.push(canon+': '+val);
            }
            return;
          }
        }
        _kept.push(l);
      });
      rest=_kept;
    }

    var _filtered=rest.filter(function(l){ var _lc=l.replace(/^\d+[.)]\s*/,''); return !(_SKIP.test(_lc)&&_lc.split(' ').length<=5); });
    var content=[]; var fi=0;
    while(fi<_filtered.length){
      var fl=_filtered[fi]; var fn=_filtered[fi+1]||'';
      var endsColon=/:\s*$/.test(fl);
      var shortLine=fl.split(' ').length<=8;
      var nextLong=fn.split(' ').length>=4;
      var nextIsHeading=/:\s*$/.test(fn)||(fn.split(' ').length<=6&&!/[.!?]\s*$/.test(fn));
      var flAlreadyPaired=fl.indexOf(':')!==-1&&!endsColon;
      if(!flAlreadyPaired&&(endsColon||shortLine)&&fn&&nextLong&&!nextIsHeading){
        content.push(fl.replace(/:\s*$/,'')+': '+fn);
        fi+=2;
      } else {
        content.push(fl); fi++;
      }
    }

    content=_titleFields.concat(content);
    var type=(bi===0)?'title':detectType(content, heading, bi);
    var slide={type:type,title:heading,subtitle:subtitle,content:content,image_query:heading+' professional',icon:'',icons:[]};
    _assignIcons(slide);
    slides.push(slide);
  });

  if(!slides.length){
    slides.push({type:'title',title:'Presentation',subtitle:'',content:[],image_query:'',icon:'',icons:[]});
  }
  if(slides[0].type!=='title'&&slides[0].content.length===0) slides[0].type='title';

  return slides;
}

window.dspPreserveParse = dspPreserveParse;

var _origDspGen=window.dspGen;
window.dspGen=function(){
  if(typeof window.G==='undefined'||window.G.mode!=='preserve'){
    return _origDspGen.apply(this,arguments);
  }

  var promptEl=document.getElementById('dsp-prompt');
  var prompt=promptEl?promptEl.value.trim():'';
  if(!prompt){
    var pbox=document.getElementById('dsp-pbox');
    if(pbox){
      pbox.style.setProperty('border-color','#ef4444','important');
      pbox.style.setProperty('box-shadow','0 0 0 4px rgba(239,68,68,.25)','important');
      setTimeout(function(){
        pbox.style.setProperty('border-color','#2563eb','important');
        pbox.style.setProperty('box-shadow','0 0 0 4px rgba(37,99,235,.18)','important');
      },2000);
    }
    if(promptEl)promptEl.focus();
    return;
  }

  var cost=window.dspUpdCost?window.dspUpdCost():20;
  if(window.G.credits<cost){alert('Not enough credits! Need '+cost+', you have '+window.G.credits+'.');return;}

  var design=(document.getElementById('dsp-design')||{}).value||'navy-pro';
  var slides=dspPreserveParse(prompt);
  var ppt={title:slides[0].title||'Presentation',slides:slides};

  window.dspLiveGenShow(prompt,slides.length,design);
  var bannerTxt=document.getElementById('dsp-lg-banner-txt');
  if(bannerTxt)bannerTxt.innerHTML='<b style="color:#111;">Formatting Your Content:</b> Building your slides from what you wrote.';

  // Deduct credits
  fetch(window.PURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':window.NONCE},
    body:JSON.stringify({topic:prompt.slice(0,200),slides:slides.length,deduct_only:true,amount:cost})})
  .then(function(r){return r.json();})
  .then(function(d){
    if(d&&d.credits_balance!==undefined){
      window.G.credits=d.credits_balance;
      var ce=document.getElementById('ds-cr-display');
      if(ce)ce.textContent=window.G.credits.toLocaleString();
    }
  })
  .catch(function(){});

  // Send typed slides through build-pptx-v2 same as AI mode
  var bldUrl=window.DSP_BLD_URL||window.BLDURL||'/wp-json/ds/v1/build-pptx-v2';
  fetch(bldUrl,{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':window.NONCE},
    body:JSON.stringify({slides:slides,design:design,title:ppt.title,slide_variants:{}})
  })
  .then(function(r){return r.json();})
  .then(function(d){
    window.dspLiveGenHide();
    // build-pptx-v2 returns a URL directly, not ppt_data
    // Use our correctly typed parsed slides for the editor
    // The downloaded PPTX already has correct layouts
    ppt.slides = slides;
    window.G.pptId='preserve_'+Date.now();
    window.G.pptData=ppt;
    window.G.pptTitle=ppt.title;
    // If a direct download URL came back, store it for the download button
    if(d && d.url){
      window.G.lastPreserveUrl = d.url;
      window.G.lastPreserveFilename = d.filename || 'presentation.pptx';
      // Auto-trigger download
      var a = document.createElement('a');
      a.href = d.url;
      a.download = d.filename || 'presentation.pptx';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }
    if(typeof window.buildEditor==='function')window.buildEditor(ppt);
    if(typeof window.dspSyncPreserveData==='function')window.dspSyncPreserveData(ppt);
  })
  .catch(function(){
    window.dspLiveGenHide();
    window.G.pptId='preserve_'+Date.now();
    window.G.pptData=ppt;
    window.G.pptTitle=ppt.title;
    if(typeof window.buildEditor==='function')window.buildEditor(ppt);
  });
};

document.addEventListener('input', function(e){
  if(!e.target || e.target.id!=='dsp-prompt')return;
  var v=e.target.value;
  var matches=v.match(/(?:^|\n)\s*Slide\s+\d+\s*:/gi);
  if(!matches || !matches.length)return;
  var count=matches.length;
  if(typeof window.dspPillSet==='function'){
    window.dspPillSet('slides', String(count), count+' CARD'+(count>1?'S':''));
  }
}, true);

})();
