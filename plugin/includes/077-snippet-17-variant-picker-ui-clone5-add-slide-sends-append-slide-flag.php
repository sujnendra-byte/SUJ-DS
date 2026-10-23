<?php
/**
 * Code Snippets export — Snippet 17 variant picker ui [CLONE6] [Classic Editor: Edit Slide panel + 9-14 points + Key takeaway + Logo] [CLONE7] [Edit Slide + Key takeaway + Logo on template decks]
 *
 * Snippet ID : 953
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-08 04:01:12
 * Load order : 75 of 257
 */
/**
 * Snippet 17: DS PPT Variant Picker UI v4 - lean & robust
 * Run: Everywhere | NEW SNIPPET — no changes to existing snippets
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    // Expose REST nonce and URLs to JavaScript for image regeneration on draft load
    $nonce   = wp_create_nonce('wp_rest');
    $img_url = rest_url('ds/v1/fetch-slide-image');
    $bld_url = rest_url('ds/v1/build-pptx-v2');
    echo '<script>window.DSP_NONCE='.json_encode($nonce).';window.DSP_IMG_URL='.json_encode($img_url).';window.DSP_BLD_URL='.json_encode($bld_url).';</script>';
    ?>
<style>
#dsp-vp-panel{position:fixed;top:0;right:0;width:256px;height:100vh;overflow:hidden;background:#141414;border-left:1px solid #252525;z-index:100000;display:none;flex-direction:column;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
#dsp-vp-panel.vp-open{display:flex!important;}
.vp-hdr{padding:10px 12px 8px;background:#0d0d0d;border-bottom:1px solid #252525;flex-shrink:0;}
.vp-hdr h3{margin:0 0 1px;font-size:11px;font-weight:700;color:#e5e7eb;letter-spacing:.4px;text-transform:uppercase;}
.vp-hdr p{margin:0;font-size:10px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
		  .vp-scroll{flex:1;min-height:0;overflow-y:auto;overflow-x:hidden;padding:8px;display:flex;flex-direction:column;gap:7px;}
.vp-card{background:#1a1a1a;border:2px solid #2a2a2a;border-radius:8px;overflow:hidden;cursor:pointer;transition:border-color .15s;flex-shrink:0;}
.vp-card:hover{border-color:#4b82f8;}
.vp-card.vp-sel{border-color:#3b82f6;}
.vp-thumb{width:100%;aspect-ratio:16/9;display:block;overflow:hidden;position:relative;}
.vp-lbl{padding:5px 8px;font-size:10px;font-weight:600;color:#6b7280;background:#111;border-top:1px solid #222;}
.vp-card.vp-sel .vp-lbl{color:#60a5fa;}
.vp-empty{padding:28px 12px;text-align:center;color:#374151;font-size:11px;line-height:1.5;}
#dsp-vp-btn{background:#1d4ed8!important;color:#fff!important;border:none!important;border-radius:6px!important;padding:5px 12px!important;font-size:12px!important;font-weight:600!important;cursor:pointer!important;font-family:inherit!important;margin-left:6px!important;flex-shrink:0!important;}
#dsp-vp-btn:hover{background:#1e40af!important;}
body.vp-showing #dsp-editor-fullscreen{right:256px!important;}
body.vp-collapsed #dsp-editor-fullscreen{right:44px!important;}
#dsp-vp-panel.vp-collapsed{width:44px!important;}
#dsp-vp-panel.vp-collapsed .vp-hdr,#dsp-vp-panel.vp-collapsed .vp-scroll,#dsp-vp-panel.vp-collapsed #vp-collapse-toggle{display:none!important;}
#vp-collapse-strip{display:none;flex:1;flex-direction:column;align-items:center;justify-content:flex-start;gap:14px;padding:14px 0;cursor:pointer;}
#dsp-vp-panel.vp-collapsed #vp-collapse-strip{display:flex!important;}
#vp-collapse-strip span{writing-mode:vertical-rl;transform:rotate(180deg);font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:1px;text-transform:uppercase;}
#vp-collapse-strip .vp-cs-arrow{font-size:16px;color:#60a5fa;writing-mode:horizontal-tb;transform:none;}
#vp-collapse-toggle{position:absolute;top:10px;right:10px;width:22px;height:22px;border-radius:6px;background:#1a1a1a;border:1px solid #2a2a2a;color:#9ca3af;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:2;}
#vp-collapse-toggle:hover{background:#232323;color:#e5e7eb;}
/* image gen modal */
#vp-imgmod{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200000;display:none;align-items:center;justify-content:center;}
#vp-imgmod.open{display:flex!important;}
#vp-imgmod-box{background:#1a1a1a;border-radius:12px;padding:22px;width:420px;max-width:94vw;border:1px solid #2a2a2a;}
#vp-imgmod-box h3{margin:0 0 4px;font-size:15px;font-weight:700;color:#f3f4f6;}
#vp-imgmod-box p{margin:0 0 12px;font-size:11px;color:#6b7280;}
#vp-img-prompt{width:100%;box-sizing:border-box;background:#111;border:1px solid #374151;border-radius:8px;padding:10px 12px;font-size:13px;color:#f3f4f6;resize:vertical;min-height:60px;font-family:inherit;}
#vp-img-prompt:focus{outline:none;border-color:#3b82f6;}
#vp-img-status{font-size:11px;color:#60a5fa;min-height:16px;margin:8px 0 0;}
.vp-mod-btns{display:flex;gap:8px;margin-top:10px;}
#vp-img-genbtn{flex:1;background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;}
#vp-img-genbtn:disabled{background:#374151;cursor:not-allowed;}
#vp-img-cancelbtn{background:#1a1a1a;color:#9ca3af;border:1px solid #374151;border-radius:8px;padding:9px 14px;font-size:13px;cursor:pointer;font-family:inherit;}
/* hover on image areas inside the rendered slide */
.dsp-vp-imgzone{position:absolute;overflow:hidden;}
.dsp-vp-imgzone .dsp-vp-ihover{position:absolute;inset:0;opacity:0;background:rgba(0,0,0,0);transition:.2s;display:flex;align-items:center;justify-content:center;cursor:pointer;pointer-events:none;}
.dsp-vp-imgzone:hover .dsp-vp-ihover{opacity:1;background:rgba(0,0,0,.55);}
.dsp-vp-ihover-inner{text-align:center;color:#fff;pointer-events:auto;}
.dsp-vp-ihover-inner div:first-child{font-size:1.6em;}
.dsp-vp-ihover-inner div:last-child{font-size:0.55em;font-weight:700;background:#1d4ed8;border-radius:10px;padding:3px 10px;margin-top:4px;}
	#vp-load-overlay{position:fixed;inset:0;background:rgba(10,10,10,.92);z-index:300000;display:none;align-items:center;justify-content:center;flex-direction:column;}
#vp-load-overlay.open{display:flex!important;}
.vp-spinner{width:40px;height:40px;border:3px solid #2a2a2a;border-top-color:#3b82f6;border-radius:50%;animation:vpspin .8s linear infinite;}
@keyframes vpspin{to{transform:rotate(360deg);}}
.vp-load-txt{margin-top:16px;color:#e5e7eb;font-size:13px;font-weight:600;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
			 #vp-share-modal{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:300001;display:none;align-items:center;justify-content:center;padding:20px;}
#vp-share-modal.open{display:flex!important;}
.vp-share-box{background:#1a1a2e;border:1px solid #2a2a45;border-radius:16px;padding:28px;width:100%;max-width:420px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;max-height:90vh;overflow-y:auto;}
.vp-share-box h3{margin:0 0 6px;color:#fff;font-size:22px;font-weight:800;}
.vp-share-box>p{margin:0 0 18px;color:#9ca3af;font-size:14px;}
.vp-share-preview{position:relative;width:100%;aspect-ratio:16/9;border-radius:10px;overflow:hidden;margin-bottom:16px;background:linear-gradient(135deg,#1a3a5c,#0d1f35);}
.vp-share-preview img{width:100%;height:100%;object-fit:cover;display:block;}
.vp-share-cap{position:absolute;left:0;right:0;bottom:0;padding:16px;background:linear-gradient(to top,rgba(0,0,0,.75),transparent);}
.vp-share-cap span{color:#fff;font-weight:700;font-size:15px;line-height:1.3;}
.vp-share-info{background:linear-gradient(135deg,#1e2a4a,#2a1e4a);border-radius:10px;padding:16px;margin-bottom:18px;}
.vp-share-info b{display:block;color:#fff;font-size:15px;margin-bottom:4px;}
.vp-share-info span{color:#9ca3af;font-size:12px;}
.vp-share-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;}
.vp-share-grid button{padding:13px 10px;border-radius:9px;border:none;font-weight:700;font-size:13px;cursor:pointer;color:#fff;}
.vp-share-grid button:disabled{opacity:.5;cursor:default;}
.vp-wa{background:#22c55e;}
.vp-copy{background:#6d6df8;}
.vp-tw{background:#38bdf8;}
.vp-fb{background:#2563eb;}
#vp-share-close{width:100%;padding:13px;border-radius:9px;border:1px solid #2a2a45;background:transparent;color:#9ca3af;font-weight:700;font-size:13px;cursor:pointer;}
	   #dsp-ppt-thumbs .dsp-vp-imgzone{pointer-events:none;}
#dsp-ppt-thumbs .dsp-vp-ihover,
#dsp-ppt-thumbs .dsp-vp-cropbtn,
#dsp-ppt-thumbs .dsp-vp-imgreset{display:none!important;}
#dsp-ppt-thumbs [contenteditable]{pointer-events:none!important;user-select:none!important;cursor:default!important;}
#vp-list .dsp-vp-ihover,
#vp-list .dsp-vp-cropbtn,
#vp-list .dsp-vp-imgreset{display:none!important;}
#vp-list .dsp-vp-imgzone{pointer-events:none!important;cursor:default!important;}
#vp-list [contenteditable]{pointer-events:none!important;user-select:none!important;cursor:default!important;}
.vp-thumb-del{position:absolute;top:4px;right:4px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,.7);color:#fff;font-size:13px;line-height:18px;text-align:center;cursor:pointer;z-index:50;display:none;}
.dsp-thumb-item:hover .vp-thumb-del{display:block;}
.vp-thumb-del:hover{background:#ef4444;}
.vp-dragging{opacity:.4;}
.vp-drag-over{outline:2px solid #3b82f6;outline-offset:-2px;}
</style>

	<!-- Image option picker: AI or Marketplace -->
<div id="vp-img-option-modal" style="position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:200001;display:none;align-items:center;justify-content:center;">
  <div style="background:#1a1a1a;border-radius:14px;padding:28px 24px;width:340px;max-width:94vw;border:1px solid #2a2a2a;text-align:center;">
    <div style="font-size:18px;font-weight:700;color:#f3f4f6;margin-bottom:6px;">Add Image</div>
    <div style="font-size:12px;color:#6b7280;margin-bottom:22px;">Choose how to add an image to this slide</div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <button onclick="vpImgOptionAI()" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:14px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
        ✨ Generate with AI <span style="opacity:.75;font-weight:400;font-size:.85em;">(3 credits)</span>
      </button>
      <button onclick="vpImgOptionStore()" style="background:#7c3aed;color:#fff;border:none;border-radius:10px;padding:14px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
        🏪 Browse from Store
      </button>
      <button onclick="vpImgOptionClose()" style="background:transparent;color:#6b7280;border:1px solid #2a2a2a;border-radius:10px;padding:10px;font-size:13px;cursor:pointer;font-family:inherit;">
        Cancel
      </button>
    </div>
  </div>
</div>
<!-- Image gen popup -->
<div id="vp-imgmod">
  <div id="vp-imgmod-box">
    <h3>🎨 Generate Image</h3>
    <p>Describe the image you want for this slide</p>
    <textarea id="vp-img-prompt" placeholder="e.g. professional sales team in modern office, blue tones..."></textarea>
    <div id="vp-img-status"></div>
    <div class="vp-mod-btns">
      <button id="vp-img-genbtn" onclick="vpDoGen()">✨ Generate</button>
      <button id="vp-img-cancelbtn" onclick="vpImgClose()">Cancel</button>
    </div>
  </div>
</div>

<!-- Picker panel -->
<div id="dsp-vp-panel">
  <button id="vp-collapse-toggle" onclick="vpToggleCollapse()" title="Hide panel">&#8250;</button>
  <div class="vp-hdr">
    <h3>🎨 Change Layout</h3>
    <p id="vp-lbl">Click a slide thumbnail</p>
  </div>
  <div class="vp-scroll" id="vp-list">
    <div class="vp-empty">Generate a presentation, then click any slide to pick a layout.</div>
  </div>
  <div id="vp-collapse-strip" onclick="vpToggleCollapse()">
    <span class="vp-cs-arrow">&#8249;</span>
    <span>Change Layout</span>
  </div>
</div>
		<div id="vp-load-overlay">
  <div class="vp-spinner"></div>
  <div class="vp-load-txt">Loading your presentation…</div>
</div>
		<div id="vp-share-modal">
  <div class="vp-share-box">
    <h3>Share this Presentation</h3>
    <p>Made with Designstager AI Studio</p>
    <div class="vp-share-preview" id="vp-share-preview"></div>
    <div class="vp-share-info">
      <b id="vp-share-info-title">Presentation</b>
      <span id="vp-share-info-sub">Generating your shareable link…</span>
    </div>
    <div class="vp-share-grid">
      <button class="vp-wa" id="vp-share-wa" onclick="vpShareTo('whatsapp')" disabled>WhatsApp</button>
      <button class="vp-copy" id="vp-share-copy" onclick="vpShareCopy()" disabled>Copy Link</button>
      <button class="vp-tw" id="vp-share-tw" onclick="vpShareTo('twitter')" disabled>Twitter / X</button>
      <button class="vp-fb" id="vp-share-fb" onclick="vpShareTo('facebook')" disabled>Facebook</button>
    </div>
    <button id="vp-share-close" onclick="vpShareClose()">Close</button>
  </div>
</div>

<script>
(function(){
/* ── Layout config (adjustable) ─────────────────────────────────
   DS_OVERLAY_PCT : opacity % of the theme-coloured wash laid over a
                    full-background image (0-100).
   DS_IMG_PCT     : image share % of an image+text layout (0-100);
                    the text zone gets the remainder.
   Mirror of DS_PPT_OVERLAY_PCT / DS_PPT_IMG_PCT in Snippet 16.   */
window.DS_OVERLAY_PCT = (typeof window.DS_OVERLAY_PCT==='number') ? window.DS_OVERLAY_PCT : 80;
window.DS_IMG_PCT     = (typeof window.DS_IMG_PCT==='number')     ? window.DS_IMG_PCT     : 60;
function dsRgba(hex,a){
  hex=String(hex||'').replace('#','');
  if(hex.length===3) hex=hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
  if(hex.length!==6) return 'rgba(0,0,0,'+a+')';
  return 'rgba('+parseInt(hex.slice(0,2),16)+','+parseInt(hex.slice(2,4),16)+','+parseInt(hex.slice(4,6),16)+','+a+')';
}
/* Icon helper. Defined once at this level so EVERY layout branch can reach it.
   It used to exist only inside the multipoint branch, so timeline layouts threw
   "ic is not a function" and the whole variant list failed to re-render.       */

/* image_text is retired: it only ever offered two forced-image layouts.
   Any slide still carrying that type is rendered as a 3-column slide,
   which has the full set of layout variants.                          */
function dsNormType(t){ return t==='image_text' ? 'three_column' : t; }
function dsIc(iName,sz){
  if(!iName||iName.indexOf(':')<0) return '<div style="width:'+sz+'em;height:'+sz+'em;"></div>';
  var u='https://api.iconify.design/'+iName.replace(':','/')+'.svg?color=%23ffffff';
  return '<img src="'+u+'" style="width:'+sz+'em;height:'+sz+'em;display:block;" onerror="this.style.display=\'none\';"/>';
}
'use strict';
var S={open:false,idx:-1,choices:{},data:null,imgUrl:null,nonce:null,genIdx:-1};
window.S=S; // expose so fetch interceptor and console can access it
window.applyCanvas=applyCanvas; // expose so the theme picker can refresh the current slide after a theme change
try{S.choices=JSON.parse(sessionStorage.getItem('dspVPC')||'{}');}catch(e){}

var TH={
  'navy-pro':{bg:'#1B2A4A',tc:'#FFFFFF',bc:'#C8D8F0',ac:'#4A9EFF'},
  'dark-modern':{bg:'#0D1117',tc:'#FFFFFF',bc:'#C9D1D9',ac:'#58A6FF'},
  'clean-corporate':{bg:'#FFFFFF',tc:'#1A365D',bc:'#2D3748',ac:'#2B6CB0'},
  'bold-red':{bg:'#1A0000',tc:'#FFFFFF',bc:'#FFD0D0',ac:'#FF4444'},
  'corporate-dark':{bg:'#111827',tc:'#F9FAFB',bc:'#D1D5DB',ac:'#6366F1'},
  'clean-white':{bg:'#F8FAFC',tc:'#0F172A',bc:'#334155',ac:'#0EA5E9'},
  'gradient-pro':{bg:'#0F0C29',tc:'#FFFFFF',bc:'#E0D7FF',ac:'#A78BFA'},
  'bold-impact':{bg:'#111111',tc:'#FFFFFF',bc:'#CCCCCC',ac:'#FFD700'}
};
window.TH=TH;function gt(){var el=document.getElementById('dsp-design');return TH[(el?el.value:'navy-pro')]||TH['navy-pro'];}
function stripMd(s){
  return String(s)
    .replace(/\*\*([^*]+)\*\*/g,'$1')  // **bold** -> text
    .replace(/\*([^*]+)\*/g,'$1')        // *italic* -> text
    .replace(/^#+\s*/gm,'')              // ## Header -> text
    .replace(/`([^`]+)`/g,'$1')           // `code` -> text
    .replace(/__([^_]+)__/g,'$1');        // __bold__ -> text
}
function es(s){var t=stripMd(String(s));return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

/* ── image zone: full-bleed, click to generate ── */
function imgZone(img,idx,css){
  var pos=(S.data&&S.data.slides&&S.data.slides[idx]&&S.data.slides[idx].image_position)||{x:50,y:50};
  var bg=img
    ? 'background-image:url('+img+');background-size:cover;background-repeat:no-repeat;background-position:'+pos.x+'% '+pos.y+'%;'
    : 'background:linear-gradient(135deg,#1a3a5c,#0d1f35);';
  var ph=img?'':'<div style="font-size:1.6em;opacity:.3;">🖼</div>';
  var resetBtn=img?'<button type="button" class="dsp-vp-imgreset" onclick="event.stopPropagation();dspResetImagePosition('+idx+');" title="Reset image position">⟲</button>':'';
  var cropBtn=img?'<button type="button" class="dsp-vp-cropbtn" onclick="event.stopPropagation();window.vpStartCropEdit('+idx+');" title="Crop image">✂️ Crop</button>':'';
  return '<div class="dsp-vp-imgzone" data-slide-idx="'+idx+'" style="'+css+'">'
    +'<div style="position:absolute;inset:0;'+bg+'display:flex;align-items:center;justify-content:center;pointer-events:none;">'+ph+'</div>'
    +resetBtn
    +cropBtn
    +'<div class="dsp-vp-ihover" style="pointer-events:none;"><div class="dsp-vp-ihover-inner" style="pointer-events:auto;"><div>🖼</div><div>Change Image</div></div></div>'
    +'</div>';
}
			 function imgTextSplit(content){
  var items=(content||[]).slice(0,4);
  var totalChars=items.join(' ').length;
  var pct;
  if(totalChars<60)pct=56;
  else if(totalChars<130)pct=50;
  else if(totalChars<220)pct=44;
  else if(totalChars<340)pct=38;
  else pct=33;
  return Math.max(30,Math.min(58,pct));
}
			 function isDarkColor(hex){
  var c=hex.replace('#','');
  if(c.length===3)c=c[0]+c[0]+c[1]+c[1]+c[2]+c[2];
  var r=parseInt(c.substr(0,2),16),g=parseInt(c.substr(2,2),16),b=parseInt(c.substr(4,2),16);
  return (0.299*r+0.587*g+0.114*b)<128;
}

			 function dspApplyPreviewFont(){
  var f=document.getElementById('dsp-font');
  var fam=(f&&f.value)?f.value.trim():'';
  var st=document.getElementById('dsp-preview-font-css');
  if(!st){st=document.createElement('style');st.id='dsp-preview-font-css';document.head.appendChild(st);}
  st.textContent=fam
    ? '#dsp-wrap #dsp-ppt-main-slide *,#dsp-wrap #dsp-th-grid *,#dsp-wrap #dsp-ppt-thumbs *,#dsp-wrap #vp-list *{font-family:\''+fam+'\',Arial,sans-serif!important;}'
    : '';
  if(fam){
    var id='dsp-gf-'+fam.replace(/\W+/g,'-');
    if(!document.getElementById(id)){
      var l=document.createElement('link');
      l.id=id;l.rel='stylesheet';
      l.href='https://fonts.googleapis.com/css2?family='+fam.replace(/\s+/g,'+')+':wght@400;600;700;800;900&display=swap';
      document.head.appendChild(l);
    }
  }
}
/* ── slide renderers: full-bleed absolute layout ── */
function renderSlideBase(s,t,v,slideIdx){
  /* DS design templates (engine T00): a template deck renders with its own layouts */
  if(window.DS_TPL_ACTIVE){var _dsTpl=window.DS_TPL_ACTIVE();if(_dsTpl){return _dsTpl.render(s,v,(typeof slideIdx==='number'?slideIdx:S.idx));}}
  /* DS Charts: chart slides on Classic decks */
  if(s&&s.type==='chart'&&window.DS_CHART&&window.DS_CHART.renderClassic){return window.DS_CHART.renderClassic(s,t,v,(typeof slideIdx==='number'?slideIdx:S.idx));}
  dspApplyPreviewFont();
  var img=s.image_data||null, idx=(typeof slideIdx==='number'?slideIdx:S.idx);
  var _f=document.getElementById('dsp-font');
var _ff=(_f&&_f.value)?"'"+_f.value+"',":'';
var W='position:absolute;inset:0;background:'+t.bg+';overflow:hidden;font-family:'+_ff+'Arial,sans-serif;';
  var H='<div style="'+W+'">';
  var ACB='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+t.ac+';pointer-events:none;"></div>';
  var type=dsNormType(s.type||'bullet');
  var _cnt=(s.content||[]).length;
  if(type==='bullet'&&_cnt===5&&(v==='v1'||v==='v2'||v==='v3'))type='five_point';
  else if(type==='bullet'&&_cnt===6&&(v==='v1'||v==='v2'||v==='v3'))type='six_point';
  else if(type==='bullet'&&_cnt===7&&(v==='v1'||v==='v2'||v==='v3'))type='seven_point';
else if(type==='bullet'&&_cnt===8&&(v==='v1'||v==='v2'||v==='v3'))type='eight_point';

  function parseTitleFields(content){
    var out={company:'',presenter:'',designation:'',date:'',website:'',tagline:'',confidential:''};
    if(!content||!content.length)return out;
    content.forEach(function(item){
      var s2=String(item),ci=s2.indexOf(':');
      if(ci<=0)return;
      var label=s2.slice(0,ci).trim().toLowerCase();
      var val=s2.slice(ci+1).trim();
      if(out.hasOwnProperty(label))out[label]=val;
    });
    return out;
  }
  function TT(al, spread, scale){
    scale=scale||1;
    var a=al?'left':'center',mx=al?'0':'auto',jc=al?'flex-start':'center';
    var f=parseTitleFields(s.content);
    var A='', B='', C='';

    var _ceLg=window.dsceCoverLogoHtml?window.dsceCoverLogoHtml(jc,t):'';
    if(_ceLg){A+=_ceLg;}
    else if(f.company||f.tagline){
      var initial=es((f.company||s.title||'?').trim().charAt(0).toUpperCase()||'?');
      A+='<div style="display:flex;align-items:center;justify-content:'+jc+';gap:.6em;pointer-events:auto;flex-shrink:0;">'
        +'<div style="width:2em;height:2em;border-radius:22%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.8em;font-weight:900;color:#fff;flex-shrink:0;">'+initial+'</div>'
        +'<div style="text-align:left;">'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tf-company" style="outline:none;cursor:text;min-height:1em;font-size:.75em;font-weight:800;color:'+t.tc+';line-height:1.2;">'+es(f.company)+'</div>'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tf-tagline" style="outline:none;cursor:text;min-height:1em;font-size:.52em;color:'+t.bc+';line-height:1.2;">'+es(f.tagline)+'</div>'
        +'</div></div>';
    }

    B+='<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="title" style="pointer-events:auto;outline:none;cursor:text;font-size:'+(3.8*scale)+'em;font-weight:900;color:'+t.tc+';line-height:1.05;text-align:'+a+';flex-shrink:0;">'+es(s.title||'')+'</div>'
      +'<div style="width:90px;height:5px;background:'+t.ac+';margin:'+(.9*scale)+'em '+mx+';'+(al?'':'margin-left:auto;margin-right:auto;')+'flex-shrink:0;"></div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="subtitle" style="pointer-events:auto;outline:none;cursor:text;min-height:1em;font-size:'+(1.3*scale)+'em;color:'+t.bc+';text-align:'+a+';flex-shrink:0;">'+es(s.subtitle||'')+'</div>';

    if(f.presenter||f.designation||f.date){
  C+='<div style="height:1.5px;background:'+t.ac+'35;flex-shrink:0;margin-bottom:'+(1.5*scale)+'em;"></div>';
  C+='<div style="display:flex;justify-content:'+jc+';gap:'+(4*scale)+'em;text-align:left;pointer-events:auto;flex-shrink:0;">';
  
  if(f.presenter||f.designation){
    C+='<div style="display:flex;align-items:flex-start;gap:.8em;">'
      +'<svg style="width:1.4em;height:1.4em;flex-shrink:0;margin-top:.1em;" viewBox="0 0 24 24" fill="'+t.ac+'"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>'
      +'<div><div style="font-size:'+(.38*(.75+.25*scale))+'em;letter-spacing:.1em;color:'+t.bc+';opacity:.75;font-weight:700;text-transform:uppercase;white-space:nowrap;">Presented By</div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tf-presenter" style="outline:none;cursor:text;min-height:1.2em;font-size:'+(.75*scale)+'em;font-weight:800;color:'+t.tc+';margin-top:.15em;white-space:nowrap;">'+es(f.presenter)+'</div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tf-designation" style="outline:none;cursor:text;min-height:1em;font-size:'+(.42*(.75+.25*scale))+'em;color:'+t.bc+';opacity:.8;white-space:nowrap;">'+es(f.designation)+'</div>'
      +'</div></div>';
  }
  
  if(f.date){
    C+='<div style="display:flex;align-items:flex-start;gap:.8em;">'
      +'<svg style="width:1.4em;height:1.4em;flex-shrink:0;margin-top:.1em;" viewBox="0 0 24 24" fill="'+t.ac+'"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zm-5-5H7v5h7v-5z"/></svg>'
      +'<div><div style="font-size:'+(.38*(.75+.25*scale))+'em;letter-spacing:.1em;color:'+t.bc+';opacity:.75;font-weight:700;text-transform:uppercase;white-space:nowrap;">Date</div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tf-date" style="outline:none;cursor:text;min-height:1.2em;font-size:'+(.75*scale)+'em;font-weight:800;color:'+t.tc+';margin-top:.15em;white-space:nowrap;">'+es(f.date)+'</div></div>'
      +'</div>';
  }
  
  C+='</div>';
  
  if(f.website||s.website){
    var web=es(f.website||s.website||'');
    C+='<div style="margin-top:'+(2*scale)+'em;text-align:center;font-size:'+(.6*scale)+'em;color:'+t.ac+';pointer-events:auto;flex-shrink:0;">🌐 '+web+'</div>';
  }
}

return '<div style="display:flex;flex-direction:column;height:100%;justify-content:space-between;padding:4% 0;"><div style="margin-bottom:1.2em;">'+A+'</div><div style="text-align:'+a+';">'+B+'</div><div>'+C+'</div></div>';
}
  function TH2(){
    return '<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="title" style="pointer-events:auto;outline:none;cursor:text;font-size:1.25em;font-weight:800;color:'+t.tc+';">'+es(s.title||'')+'</div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="subtitle" style="pointer-events:auto;outline:none;cursor:text;min-height:1em;font-size:0.6em;color:'+t.ac+';font-weight:600;margin:.1em 0 .3em;">'+es(s.subtitle||'')+'</div>';
  }
  function BRs(max){
function ic(iName,sz){
if(!iName||iName.indexOf(':')<0) return '<div style="width:'+sz+'em;height:'+sz+'em;"></div>';
var u='https://api.iconify.design/'+iName.replace(':','/')+ '.svg?color=%23ffffff';
return '<img src="'+u+'" style="width:'+sz+'em;height:'+sz+'em;display:block;" onerror="this.style.display=\'none\';"/>';
}

    var sy=['💡','📊','🎯','✅','🚀','⭐'];
    var html=(s.content||[]).slice(0,max||5).map(function(x,i){
      var c=String(x),ci=c.indexOf(':'),h=ci>0?c.slice(0,ci).trim():'',d=ci>0?c.slice(ci+1).trim():c;
      return '<div style="display:flex;align-items:flex-start;gap:.35em;margin-bottom:.35em;">'
        +'<div style="width:.9em;height:.9em;border-radius:50%;background:'+t.ac+'28;display:flex;align-items:center;justify-content:center;font-size:.55em;flex-shrink:0;margin-top:.1em;">'+ic((s.icons&&s.icons[i])?s.icons[i]:'',.5)+'</div>'
        +'<div style="flex:1;pointer-events:auto;">'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:1em;font-size:.68em;font-weight:800;color:'+t.tc+';">'+es(h)+'</div>'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:1em;font-size:.57em;color:'+t.bc+';line-height:1.3;">'+es(d)+'</div>'
        +'</div></div>';
    }).join('');
    return html+'<div onclick="vpAddBullet('+idx+')" style="pointer-events:auto;cursor:pointer;font-size:.55em;color:'+t.ac+';opacity:.65;margin-top:.4em;">+ Add point</div>';
}
  function GCards(cols,fill){
function ic(iName,sz){
if(!iName||iName.indexOf(':')<0) return '<div style="width:'+sz+'em;height:'+sz+'em;"></div>';
var u='https://api.iconify.design/'+iName.replace(':','/')+ '.svg?color=%23ffffff';
return '<img src="'+u+'" style="width:'+sz+'em;height:'+sz+'em;display:block;" onerror="this.style.display=\'none\';"/>';
}

    var items=(s.content||[]).slice(0,cols===3?3:4);
    var sy=['💡','📊','🎯','✅','🚀','⭐'];
    if(fill===true){
    /* Cards laid out as a real grid (2 across for 4 points, 3 across for 3),
       with the icon on the LEFT and the heading beside it, a hairline rule,
       then left-aligned body copy - matching the PPTX renderer. */
    var gcards = items.map(function(it,i){
      var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',d=colon>0?String(it).slice(colon+1).trim():String(it);
      return '<div style="min-width:0;min-height:0;overflow:hidden;background:'+t.ac+'14;border-radius:14px;padding:3.4% 3.2%;pointer-events:auto;display:flex;flex-direction:column;align-items:flex-start;text-align:left;">'
        + '<div style="display:flex;align-items:center;gap:.55em;width:100%;flex-shrink:0;">'
        +   '<div style="width:1.6em;height:1.6em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;flex-shrink:0;">'+ic((s.icons||[])[i],0.95)+'</div>'
        +   '<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-width:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.62em;font-weight:800;line-height:1.2;color:'+t.tc+';">'+es(h)+'</div>'
        + '</div>'
        + '<div style="width:100%;height:2px;background:'+t.ac+';margin:.45em 0 .5em;flex-shrink:0;"></div>'
        + '<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;width:100%;min-height:0;overflow:hidden;overflow-wrap:break-word;display:-webkit-box;-webkit-line-clamp:8;-webkit-box-orient:vertical;font-size:.46em;color:'+t.bc+';line-height:1.42;text-align:left;">'+es(d)+'</div>'
        + '</div>';
    }).join('');
    return '<div style="display:grid;grid-template-columns:repeat('+cols+',minmax(0,1fr));gap:1.6%;height:100%;min-height:0;align-content:start;">'+gcards+'</div>';
  }
  if(fill==='stack'){
      var scards=items.map(function(it,i){
        var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',d=colon>0?String(it).slice(colon+1).trim():String(it);
        return '<div style="flex:1;min-height:0;overflow:hidden;background:'+t.ac+'14;border-radius:12px;padding:3% 5%;pointer-events:auto;display:flex;flex-direction:column;justify-content:center;">'
  +'<div style="width:1.3em;height:1.3em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.65em;margin-bottom:.18em;flex-shrink:0;">'+ic((s.icons&&s.icons[i])?s.icons[i]:'',.95)+'</div>'
  +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;flex-shrink:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.82em;font-weight:800;line-height:1.15;color:'+t.tc+';margin-bottom:.18em;">'+es(h)+'</div>'
  +'<div style="width:30px;height:2px;background:'+t.ac+';margin-bottom:.18em;flex-shrink:0;"></div>'
  +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;flex-shrink:0;min-height:0;max-height:2.7em;overflow:hidden;overflow-wrap:break-word;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;font-size:.5em;color:'+t.bc+';line-height:1.25;">'+es(d)+'</div></div>';
      }).join('');
      return '<div style="display:flex;flex-direction:column;gap:1%;height:100%;">'+scards+'</div>';
    }
	  if(fill==='split'){
      var pcards=items.map(function(it,i){
        var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',d=colon>0?String(it).slice(colon+1).trim():String(it);
        return '<div style="flex:1;min-height:0;overflow:hidden;background:'+t.ac+'14;border-radius:12px;padding:4% 5%;pointer-events:auto;display:flex;align-items:center;gap:4%;">'
          +'<div style="width:34%;flex-shrink:0;display:flex;flex-direction:column;align-items:center;text-align:center;">'
          +'<div style="width:2em;height:2em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.9em;margin-bottom:.4em;flex-shrink:0;">'+ic((s.icons&&s.icons[i])?s.icons[i]:'',1.05)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:2.5em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.86em;font-weight:800;color:'+t.tc+';line-height:1.22;">'+es(h)+'</div>'
          +'</div>'
          +'<div style="width:1px;align-self:stretch;background:'+t.ac+'50;flex-shrink:0;"></div>'
          +'<div style="flex:1;padding-left:4%;">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:7.3em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:5;-webkit-box-orient:vertical;font-size:.58em;color:'+t.bc+';line-height:1.45;">'+es(d)+'</div>'
          +'</div></div>';
      }).join('');
      return '<div style="display:flex;flex-direction:column;gap:6%;height:100%;">'+pcards+'</div>';
    }
    if(fill==='duo'){
      function fCard(it,i){
        var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',d=colon>0?String(it).slice(colon+1).trim():String(it);
        return '<div style="flex:1;min-height:0;overflow:hidden;background:'+t.ac+'14;border-radius:14px;padding:6%;pointer-events:auto;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">'
          +'<div style="width:2em;height:2em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.9em;margin-bottom:.5em;flex-shrink:0;">'+ic((s.icons&&s.icons[i])?s.icons[i]:'',1.05)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:2.5em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.92em;font-weight:800;color:'+t.tc+';margin-bottom:.3em;">'+es(h)+'</div>'
          +'<div style="width:36px;height:2px;background:'+t.ac+';margin-bottom:.5em;"></div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:4.3em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;font-size:.64em;color:'+t.bc+';line-height:1.4;">'+es(d)+'</div></div>';
      }
      function sCard(it,i){
        var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',d=colon>0?String(it).slice(colon+1).trim():String(it);
        return '<div style="flex:1;min-height:0;overflow:hidden;background:'+t.ac+'14;border-radius:12px;padding:3% 4%;pointer-events:auto;display:flex;align-items:center;gap:3%;">'
          +'<div style="width:28%;flex-shrink:0;display:flex;flex-direction:column;align-items:center;text-align:center;">'
          +'<div style="width:1.8em;height:1.8em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.85em;margin-bottom:.3em;flex-shrink:0;">'+ic((s.icons&&s.icons[i])?s.icons[i]:'',.95)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:2.5em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.74em;font-weight:800;color:'+t.tc+';line-height:1.18;">'+es(h)+'</div>'
          +'</div>'
          +'<div style="width:1px;align-self:stretch;background:'+t.ac+'50;flex-shrink:0;"></div>'
          +'<div style="flex:1;padding-left:3%;">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:4.3em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;font-size:.56em;color:'+t.bc+';line-height:1.42;">'+es(d)+'</div>'
          +'</div></div>';
      }
      var topRow='<div style="display:flex;gap:4%;flex:1.7;margin-bottom:4%;">'+fCard(items[0]||'',0)+fCard(items[1]||'',1)+'</div>';
      var botRow='<div style="flex:1;display:flex;">'+sCard(items[2]||'',2)+'</div>';
      return '<div style="display:flex;flex-direction:column;height:100%;">'+topRow+botRow+'</div>';
    }
    var rows=[]; var pr=cols;
    for(var r=0;r<Math.ceil(items.length/pr);r++){
      var rh='<div style="display:flex;gap:1.5%;flex:1;min-height:0;">';
      for(var ci=0;ci<pr;ci++){
        var it=items[r*pr+ci]||'',colon=String(it).indexOf(':'),gi=r*pr+ci;
        var h=colon>0?String(it).slice(0,colon).trim():'',d=colon>0?String(it).slice(colon+1).trim():String(it);
        rh+='<div style="flex:1;min-height:0;overflow:hidden;background:'+t.ac+'12;border-radius:6px;padding:4% 5%;pointer-events:auto;display:flex;flex-direction:column;justify-content:flex-start;">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+gi+'" style="outline:none;cursor:text;max-height:2.5em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.6em;font-weight:800;color:'+t.tc+';margin-bottom:.3em;line-height:1.18;flex-shrink:0;">'+es(h)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+gi+'" style="outline:none;cursor:text;min-height:0;flex-shrink:0;max-height:5.2em;overflow:hidden;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;font-size:.5em;color:'+t.bc+';line-height:1.28;">'+es(d)+'</div></div>';
      }
      rows.push(rh+'</div>');
    }
    return '<div style="display:flex;flex-direction:column;gap:1.5%;height:100%;">'+rows.join('')+'</div>';
  }

  if(type==='title'){
    if(v==='v2'){
      H+=imgZone(img,idx,'inset:0;');
      H+='<div style="position:absolute;inset:0;background:'+dsRgba(t.bg,window.DS_OVERLAY_PCT/100)+';pointer-events:none;"></div>';
      H+='<div style="position:absolute;inset:0;z-index:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:8%;pointer-events:none;">'+TT(false)+'</div>';
    }else if(v==='v3'){
      H+='<div style="position:absolute;top:0;bottom:0;left:0;right:'+window.DS_IMG_PCT+'%;display:flex;flex-direction:column;justify-content:center;padding:6% 4% 6% 7%;">'+TT(true, undefined, 0.5)+'</div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:'+window.DS_IMG_PCT+'%;');
    }else if(v==='v4'){
      H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:'+window.DS_IMG_PCT+'%;');
      H+='<div style="position:absolute;top:0;bottom:0;left:'+window.DS_IMG_PCT+'%;right:0;display:flex;flex-direction:column;justify-content:center;padding:6% 7% 6% 4%;">'+TT(true, undefined, 0.5)+'</div>';
    }else if(v==='v5'){
      H+='<div style="position:absolute;top:0;left:0;right:0;height:60%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:3% 8%;">'+TT(false, undefined, 0.5)+'</div>';
      H+='<div style="position:absolute;top:60%;left:0;right:0;height:2px;background:'+t.ac+';"></div>';
      H+=imgZone(img,idx,'top:61%;bottom:0;left:0;right:0;');
    }else{
  // v1 — Text only: no image, matches the PPTX exporter (ds_render_title).
  H+='<div style="position:absolute;inset:0;z-index:2;background:'+t.bg+';display:flex;flex-direction:column;align-items:center;padding:0 7%;">';
  H+=TT(false,true)+'</div>';
}
  }
  else if(type==='bullet'||type==='feature_grid'||type==='two_column'||type==='three_column'){
    var cols=type==='three_column'?3:2;
    var content=type==='bullet'?BRs(v==='v1'?6:5):GCards(cols,type==='feature_grid'?true:(type==='two_column'||type==='three_column'));
    H+=ACB;
    if(v==='v2'){
      H+='<div style="position:absolute;top:3px;bottom:0;left:0;right:29%;display:flex;flex-direction:column;padding:4% 2% 3% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+content+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:28%;');
    }else if(v==='v3'){
      H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:28%;');
      H+='<div style="position:absolute;top:3px;bottom:0;left:29%;right:0;display:flex;flex-direction:column;padding:4% 5% 3% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+content+'</div></div>';
    }else if(v==='v4'&&type==='two_column'){
      H+='<div style="position:absolute;top:0;left:0;right:0;height:56%;display:flex;flex-direction:column;padding:4% 5% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+content+'</div></div>';
      H+=imgZone(img,idx,'top:56%;bottom:0;left:0;right:0;');
    }else if(v==='v5'&&type==='two_column'){
      var _ov5=isDarkColor(t.tc)?'255,255,255':'0,0,0';
      H+=imgZone(img,idx,'inset:0;');
      H+='<div style="position:absolute;inset:0;background:rgba('+_ov5+',.9);pointer-events:none;"></div>';
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;z-index:1;pointer-events:none;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+content+'</div></div>';
    }else if(v==='v6'&&type==='two_column'){
      H+='<div style="position:absolute;top:0;bottom:0;left:0;right:48%;display:flex;flex-direction:column;padding:5% 4% 5% 6%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:3%;">'+GCards(cols,'stack')+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;left:52%;width:48%;');
    }else if(v==='v7'&&type==='two_column'){
      H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:48%;');
      H+='<div style="position:absolute;top:0;bottom:0;left:52%;right:0;display:flex;flex-direction:column;padding:5% 6% 5% 4%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:3%;">'+GCards(cols,'split')+'</div></div>';
    }else if(v==='v8'&&type==='two_column'){
      H+='<div style="position:absolute;top:0;bottom:0;left:0;right:48%;display:flex;flex-direction:column;padding:5% 4% 5% 6%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:3%;">'+GCards(cols,'split')+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;left:52%;width:48%;');
    }else if(v==='v4'&&type==='three_column'){
      H+='<div style="position:absolute;top:0;left:0;right:0;height:56%;display:flex;flex-direction:column;padding:4% 5% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+content+'</div></div>';
      H+=imgZone(img,idx,'top:56%;bottom:0;left:0;right:0;');
    }else if(v==='v5'&&type==='three_column'){
      H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:34%;');
      H+='<div style="position:absolute;top:0;bottom:0;left:37%;right:0;display:flex;flex-direction:column;padding:4% 5% 4% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+GCards(cols,'split')+'</div></div>';
    }else if(v==='v6'&&type==='three_column'){
      H+='<div style="position:absolute;top:0;bottom:0;left:0;right:37%;display:flex;flex-direction:column;padding:4% 2% 4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+GCards(cols,'split')+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:34%;');
    }else if(v==='v7'&&type==='three_column'){
      H+='<div style="position:absolute;top:0;bottom:0;left:0;right:29%;display:flex;flex-direction:column;padding:4% 3% 4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+GCards(cols,'duo')+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:28%;');
    }else if(v==='v4'&&type==='feature_grid'){
      H+='<div style="position:absolute;top:0;left:0;right:0;height:70%;display:flex;flex-direction:column;padding:4% 5% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+GCards(cols,true)+'</div></div>';
      H+=imgZone(img,idx,'top:70%;bottom:0;left:0;right:0;');
    }else if(v==='v5'&&type==='feature_grid'){
      H+='<div style="position:absolute;top:0;bottom:0;left:0;right:34%;display:flex;flex-direction:column;padding:4% 2% 4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+GCards(cols,'stack')+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:34%;');
    }else{
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+content+'</div></div>';
    }
  }
  else if(type==='stats'){
    var si=(s.content||[]).slice(0,16);
    /* Numbers slide: columns split by hairlines, a rule under the numbers, labels below, and the Key takeaway right under the numbers inside the text area (not across the bottom of the slide). 4-16 numbers wrap into rows. */
    var _sn=si.length,_sc=_sn<=4?Math.max(1,_sn):(_sn<=6?3:(_sn<=8?4:(_sn<=9?3:4))),_sr=Math.ceil(_sn/_sc),_s5=(v==='v5')?0.74:1,_ml=Math.max.apply(null,si.map(function(x){return String(x).split(':')[0].trim().length;}).concat([1])),_ns=Math.min((_sr<=1?(_sn<=3?3.6:2.6):_sr===2?2:_sr===3?1.45:1.1)*_s5,((v==='v2'||v==='v4')?25:34)/_sc*0.84/(0.8*_ml)),_ls=(_sr<=1?(_sn<=3?0.8:0.66):_sr===2?0.58:_sr===3?0.5:0.44)*(_s5<1?0.86:1),_hl=t.tc+'2E';
    var _tkS=(typeof ceTkOk==='function'&&typeof ceTplOn==='function'&&!ceTplOn()&&ceTkOk(s)&&s.takeaway)?String(s.takeaway).trim():'';
    var sH=si.map(function(x,si2){var p=String(x).split(':');return '<div style="flex:0 0 '+(100/_sc-(_sr>1?0.08:0)).toFixed(3)+'%;min-width:0;box-sizing:border-box;text-align:center;pointer-events:auto;'+((si2%_sc)===0?'':'border-left:1px solid '+_hl+';')+'">'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="stat-num" data-ed-idx="'+si2+'" style="outline:none;cursor:text;font-size:'+_ns+'em;font-weight:900;font-family:\'Arial Black\',Arial,sans-serif;white-space:nowrap;line-height:1.1;color:'+t.tc+';padding:0 .1em .16em;border-bottom:1px solid '+_hl+';">'+es((p[0]||'').trim())+'</div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="stat-label" data-ed-idx="'+si2+'" style="outline:none;cursor:text;min-height:1em;font-size:'+_ls+'em;line-height:1.3;color:'+(v==='v3'?t.tc:t.bc)+';padding:.55em .5em '+(_sr>1?'.5em':'1.4em')+';">'+es((p[1]||'').trim())+'</div></div>';}).join('');
    sH='<div style="width:100%;display:flex;flex-direction:column;justify-content:center;"><div style="display:flex;flex-wrap:'+(_sr>1?'wrap':'nowrap')+';justify-content:center;align-items:stretch;">'+sH+'</div>'+(_tkS&&typeof ceStripIn==='function'?ceStripIn(_tkS,t,idx,_s5<1):'')+'</div>';
    H+=ACB;
    if(v==='v2'){
      H+='<div style="position:absolute;top:3px;bottom:0;left:0;right:29%;display:flex;flex-direction:column;padding:4% 3% 4% 5%;">'+TH2()+'<div style="flex:1;display:flex;align-items:center;gap:2%;">'+sH+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:28%;');
    }else if(v==='v3'){
      H+=imgZone(img,idx,'inset:0;');
      H+='<div style="position:absolute;inset:0;background:'+dsRgba(t.bg,Math.max(window.DS_OVERLAY_PCT,88)/100)+';pointer-events:none;"></div>';
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;z-index:1;pointer-events:none;">'+TH2()+'<div style="flex:1;display:flex;align-items:center;gap:2%;">'+sH+'</div></div>';
    }else if(v==='v4'){
      H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:28%;');
      H+='<div style="position:absolute;top:3px;bottom:0;left:29%;right:0;display:flex;flex-direction:column;padding:4% 5% 4% 3%;">'+TH2()+'<div style="flex:1;display:flex;align-items:center;gap:2%;">'+sH+'</div></div>';
    }else if(v==='v5'){
      H+='<div style="position:absolute;top:3px;left:0;right:0;height:60%;display:flex;flex-direction:column;padding:3% 5%;">'+TH2()+'<div style="flex:1;display:flex;align-items:center;gap:2%;">'+sH+'</div></div>';
      H+='<div style="position:absolute;top:60%;left:0;right:0;height:2px;background:'+t.ac+';"></div>';
      H+=imgZone(img,idx,'top:61%;bottom:0;left:0;right:0;');
    }else{
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;">'+TH2()+'<div style="flex:1;display:flex;align-items:center;gap:2%;">'+sH+'</div></div>';
    }
  }
  else if(type==='timeline'){
    var tl=(s.content||[]).slice(0,5);
    function pad2(i){var n=i+1;return (n<10?'0':'')+n;}
    var tsy=['🚀','📊','🎯','✅','💡','⭐'];
    H+=ACB;
    if(v==='v2'||v==='v10'){
      var hH=tl.slice(0,4).map(function(x,ti){var p=String(x).split(':');return '<div style="flex:1;text-align:center;pointer-events:auto;">'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;font-size:.58em;font-weight:800;color:'+t.ac+';margin-bottom:.3em;">'+es((p[0]||'').trim())+'</div>'
        +'<div style="width:10px;height:10px;border-radius:50%;background:'+t.ac+';margin:0 auto .3em;"></div>'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;min-height:1em;font-size:.52em;color:'+t.bc+';line-height:1.3;">'+es(p.slice(1).join(':').trim())+'</div></div>';}).join('');
      var hWrap='<div style="flex:1;display:flex;align-items:center;position:relative;"><div style="position:absolute;top:50%;left:0;right:0;height:2px;background:'+t.ac+'30;margin-top:5px;"></div><div style="position:relative;z-index:1;display:flex;width:100%;">'+hH+'</div></div>';
      if(v==='v10'){
        H+='<div style="position:absolute;top:3px;left:0;right:0;height:60%;display:flex;flex-direction:column;padding:3% 5%;">'+TH2()+hWrap+'</div>'
          +'<div style="position:absolute;top:60%;left:0;right:0;height:2px;background:'+t.ac+';"></div>'
          +imgZone(img,idx,'top:61%;bottom:0;left:0;right:0;');
      }else{
        H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;">'+TH2()+hWrap+'</div>';
      }
    }else if(v==='v3'){
      var N3=tl.length||1;
      var zRows=tl.map(function(x,ti){
        var p=String(x).split(':'),step=p[0].trim(),desc=p.slice(1).join(':').trim();
        var isL=ti%2===0,rowTop=ti*(100/N3),rowH3=100/N3;
        var boxStyle='position:absolute;top:'+(rowTop+4)+'%;height:'+(rowH3-8)+'%;'+(isL?'left:4%;right:54%;':'left:54%;right:4%;')+'background:'+t.ac+'14;border-radius:10px;padding:3% 5%;pointer-events:auto;display:flex;flex-direction:column;justify-content:center;';
        var tickStyle='position:absolute;top:'+(rowTop+rowH3/2)+'%;height:2px;background:'+t.ac+'50;'+(isL?'left:46%;width:4%;':'left:50%;width:4%;');
        var nodeStyle='position:absolute;top:'+(rowTop+rowH3/2)+'%;left:50%;width:1.6em;height:1.6em;margin:-.8em 0 0 -.8em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.6em;font-weight:800;color:#fff;z-index:2;';
        return '<div style="'+boxStyle+'">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;font-size:.66em;font-weight:800;color:'+t.tc+';margin-bottom:.2em;">'+es(step)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;min-height:1em;font-size:.5em;color:'+t.bc+';line-height:1.3;">'+es(desc)+'</div></div>'
          +'<div style="'+tickStyle+'"></div>'
          +'<div style="'+nodeStyle+'">'+pad2(ti)+'</div>';
      }).join('');
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;position:relative;margin-top:2%;"><div style="position:absolute;top:0;bottom:0;left:50%;width:2px;background:'+t.ac+'25;"></div>'+zRows+'</div></div>';
    }else if(v==='v4'){
      var N4=tl.length||1;
      var jItems=tl.map(function(x,ti){
        var p=String(x).split(':'),step=p[0].trim(),desc=p.slice(1).join(':').trim();
        var cx=N4===1?50:(8+ti*(84/(N4-1||1))),up=ti%2===0;
        return '<div style="position:absolute;left:'+cx+'%;top:'+(up?24:76)+'%;width:1.8em;height:1.8em;margin:-.9em 0 0 -.9em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.8em;z-index:2;">'+dsIc((s.icons&&s.icons[ti])?s.icons[ti]:'',.95)+'</div>'
          +'<div style="position:absolute;left:'+cx+'%;top:'+(up?'32%':'50%')+';width:2px;height:18%;background:'+t.ac+'40;margin-left:-1px;"></div>'
          +'<div style="position:absolute;left:'+cx+'%;top:'+(up?'2%':'80%')+';width:9em;margin-left:-4.5em;text-align:center;pointer-events:auto;">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;font-size:.56em;font-weight:800;color:'+t.tc+';margin-bottom:.15em;">'+es(step)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;min-height:1em;font-size:.44em;color:'+t.bc+';line-height:1.25;">'+es(desc)+'</div></div>';
      }).join('');
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;position:relative;margin-top:2%;"><div style="position:absolute;top:50%;left:2%;right:2%;height:2px;background:'+t.ac+'30;"></div>'+jItems+'</div></div>';
    }else if(v==='v5'){
      var N5=tl.length||1;
      var mItems=tl.map(function(x,ti){
        var p=String(x).split(':'),step=p[0].trim(),desc=p.slice(1).join(':').trim();
        var cardW=Math.min(26,88/N5+6),up=ti%2===0;
        var cx=N5===1?50:((cardW/2+1)+ti*((98-cardW)/(N5-1||1)));
        return '<div style="position:absolute;left:'+cx+'%;top:'+(up?'0%':'55%')+';width:'+cardW+'%;margin-left:-'+(cardW/2)+'%;height:45%;background:'+t.ac+'14;border-radius:10px;padding:1.1em 1.3em;box-sizing:border-box;overflow:hidden;pointer-events:auto;display:flex;flex-direction:column;'+'justify-content:center;'+'">'
          +'<div style="width:1.5em;height:1.5em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.7em;margin-bottom:.25em;flex-shrink:0;">'+dsIc((s.icons&&s.icons[ti])?s.icons[ti]:'',.8)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;flex-shrink:0;font-size:.56em;font-weight:800;color:'+t.tc+';margin-bottom:.15em;">'+es(step)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.46em;color:'+t.bc+';line-height:1.3;">'+es(desc)+'</div></div>'
          +'<div style="position:absolute;left:'+cx+'%;top:'+(up?'45%':'50%')+';width:2px;height:5%;background:'+t.ac+'40;margin-left:-1px;"></div>'
          +'<div style="position:absolute;left:'+cx+'%;top:50%;width:.6em;height:.6em;margin:-.3em 0 0 -.3em;border-radius:50%;background:'+t.ac+';z-index:2;"></div>';
      }).join('');
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;position:relative;margin-top:2%;"><div style="position:absolute;top:50%;left:2%;right:2%;height:2px;background:'+t.ac+'30;"></div>'+mItems+'</div></div>';
    }else if(v==='v6'){
      var N6=tl.length||1;
      var cItems=tl.map(function(x,ti){
        var p=String(x).split(':'),step=p[0].trim(),desc=p.slice(1).join(':').trim();
        var ang=(-90+ti*(360/N6))*Math.PI/180;
        var cx6=50+32*Math.cos(ang),cy6=41+35*Math.sin(ang);
        return '<div style="position:absolute;left:'+cx6+'%;top:'+cy6+'%;width:2em;height:2em;margin:-1em 0 0 -1em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.72em;font-weight:800;color:#fff;z-index:2;">'+pad2(ti)+'</div>'
          +'<div style="position:absolute;left:'+cx6+'%;top:calc('+cy6+'% + 1.3em);width:12.5em;margin-left:-6.25em;text-align:center;pointer-events:auto;">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;font-size:.5em;font-weight:800;color:'+t.tc+';margin-bottom:.1em;">'+es(step)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.42em;color:'+t.bc+';line-height:1.25;">'+es(desc)+'</div></div>';
      }).join('');
      var subC=es(s.subtitle||s.title||'');
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;position:relative;margin-top:1%;"><div style="position:absolute;left:50%;top:53%;width:7em;height:7em;margin:-3.5em 0 0 -3.5em;border-radius:50%;background:'+t.ac+'18;border:1px solid '+t.ac+'50;display:flex;align-items:center;justify-content:center;text-align:center;padding:.4em;"><div style="font-size:.5em;font-weight:800;color:'+t.tc+';line-height:1.3;">'+subC+'</div></div>'+cItems+'</div></div>';
    }else if(v==='v7'){
      var N7=tl.length||1;
      var barW7=Math.min(16,72/N7);
      var gap7=N7>1?(84-barW7*N7)/(N7-1):0;
      var sItems=tl.map(function(x,ti){
        var p=String(x).split(':'),step=p[0].trim(),desc=p.slice(1).join(':').trim();
        var bx=8+ti*(barW7+gap7),bcx=bx+barW7/2;
        var bh=16+ti*(58/Math.max(1,N7-1));
        return '<div style="position:absolute;left:'+bx+'%;bottom:0;width:'+barW7+'%;height:'+bh+'%;background:'+t.ac+'22;border:1px solid '+t.ac+'55;border-radius:6px 6px 0 0;"></div>'
          +'<div style="position:absolute;left:'+bcx+'%;bottom:'+bh+'%;width:1.7em;height:1.7em;margin:0 0 -.85em -.85em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.6em;font-weight:800;color:#fff;z-index:2;">'+pad2(ti)+'</div>'
          +'<div style="position:absolute;left:'+bcx+'%;bottom:calc('+bh+'% + 1.6em);width:9em;margin-left:-4.5em;text-align:center;pointer-events:auto;">'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;font-size:.52em;font-weight:800;color:'+t.tc+';margin-bottom:.1em;">'+es(step)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:.42em;color:'+t.bc+';line-height:1.25;">'+es(desc)+'</div></div>';
      }).join('');
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;position:relative;margin-top:1%;"><div style="position:absolute;left:2%;right:2%;bottom:0;height:2px;background:'+t.ac+'30;"></div>'+sItems+'</div></div>';
    }else{
      var vH=tl.map(function(x,ti){var p=String(x).split(':'),step=p[0].trim(),desc=p.slice(1).join(':').trim();return '<div style="display:flex;align-items:flex-start;gap:.45em;margin-bottom:.45em;"><div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;"><div style="width:.6em;height:.6em;border-radius:50%;background:'+t.ac+';margin-top:.15em;"></div><div style="width:2px;flex:1;background:'+t.ac+'25;min-height:.8em;margin-top:2px;"></div></div><div style="flex:1;pointer-events:auto;">'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-step" data-ed-idx="'+ti+'" style="outline:none;cursor:text;font-size:.64em;font-weight:800;color:'+t.ac+';">'+es(step)+'</div>'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="tl-desc" data-ed-idx="'+ti+'" style="outline:none;cursor:text;min-height:1em;font-size:.54em;color:'+t.bc+';margin-top:.05em;line-height:1.3;">'+es(desc)+'</div></div></div>';}).join('');
      if(v==='v8'){
        H+=imgZone(img,idx,'inset:0;')
          +'<div style="position:absolute;inset:0;background:'+dsRgba(t.bg,window.DS_OVERLAY_PCT/100)+';pointer-events:none;"></div>'
          +'<div style="position:absolute;top:3px;bottom:0;inset-inline:0;z-index:1;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+vH+'</div></div>';
      }else if(v==='v9'){
        H+='<div style="position:absolute;top:3px;bottom:0;left:0;right:'+window.DS_IMG_PCT+'%;display:flex;flex-direction:column;padding:4% 3% 4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;min-height:0;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;">'+vH+'</div>'+'</div>'
          +imgZone(img,idx,'top:0;bottom:0;right:0;width:'+window.DS_IMG_PCT+'%;');
      }else{
        H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:4% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;">'+vH+'</div></div>';
      }
    }
  }
  else if(type==='quote'){
    var qA=(s.content&&s.content[0])?s.content[0]:'';
    var qB='<div style="font-size:2em;font-weight:800;color:'+t.ac+';line-height:.7;margin-bottom:.25em;">"</div>'
      +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="title" style="outline:none;cursor:text;pointer-events:auto;font-size:.88em;font-style:italic;color:'+t.tc+';line-height:1.4;margin-bottom:.6em;">'+es(s.title||'')+'</div>'
      +'<div style="font-size:.68em;font-weight:700;color:'+t.ac+';">— <span contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-plain" data-ed-idx="0" style="outline:none;cursor:text;pointer-events:auto;">'+es(qA)+'</span></div>';
    var qIP=window.DS_IMG_PCT;
    if(v==='v2'){H+='<div style="position:absolute;inset:0;right:'+qIP+'%;display:flex;flex-direction:column;justify-content:center;padding:6% 4% 6% 7%;">'+qB+'</div>'+imgZone(img,idx,'top:0;bottom:0;right:0;width:'+qIP+'%;');}
    else if(v==='v3'){H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:'+qIP+'%;')+'<div style="position:absolute;inset:0;left:'+qIP+'%;display:flex;flex-direction:column;justify-content:center;padding:6% 7% 6% 4%;">'+qB+'</div>';}
    else if(v==='v4'){H+=imgZone(img,idx,'inset:0;')+'<div style="position:absolute;inset:0;background:'+dsRgba(t.bg,window.DS_OVERLAY_PCT/100)+';pointer-events:none;"></div><div style="position:absolute;inset:0;z-index:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:6% 10%;">'+qB+'</div>';}
    else if(v==='v5'){H+='<div style="position:absolute;top:0;left:0;right:0;height:60%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:4% 10%;">'+qB+'</div><div style="position:absolute;top:60%;left:0;right:0;height:2px;background:'+t.ac+';"></div>'+imgZone(img,idx,'top:61%;bottom:0;left:0;right:0;');}
    else{H+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:6% 10%;">'+qB+'</div>';}
  }
  else if(type==='image_text'){
    var itH=BRs(4);
    var imgPct=imgTextSplit(s.content);
    if(v==='v2'){H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:'+imgPct+'%;')+'<div style="position:absolute;top:0;bottom:0;left:'+(imgPct+1)+'%;right:0;display:flex;flex-direction:column;padding:5% 5% 5% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;display:flex;flex-direction:column;justify-content:center;">'+itH+'</div></div>';}
    else{H+='<div style="position:absolute;top:0;bottom:0;left:0;right:'+(imgPct+1)+'%;display:flex;flex-direction:column;padding:5% 2% 5% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;display:flex;flex-direction:column;justify-content:center;">'+itH+'</div></div>'+imgZone(img,idx,'top:0;bottom:0;right:0;width:'+imgPct+'%;');}
  }
	else if(type==='five_point'||type==='six_point'||type==='seven_point'||type==='eight_point'){
    var nPts=type==='five_point'?5:type==='six_point'?6:type==='seven_point'?7:8;
    var items=(s.content||[]).slice(0,nPts);
    var sy=['💡','📊','🎯','✅','🚀','⭐','🔮'];
    function mpCard(it,i,compact){
function ic(iName,sz){
if(!iName||iName.indexOf(':')<0) return '<div style="width:'+sz+'em;height:'+sz+'em;"></div>';
var u='https://api.iconify.design/'+iName.replace(':','/')+ '.svg?color=%23ffffff';
return '<img src="'+u+'" style="width:'+sz+'em;height:'+sz+'em;display:block;" onerror="this.style.display=\'none\';"/>';
}

      var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',desc=colon>0?String(it).slice(colon+1).trim():String(it);
      var pad=compact?'3% 4%':'4% 5%';
      return '<div style="flex:1;min-width:0;overflow:hidden;background:'+t.ac+'14;border-radius:10px;padding:'+pad+';display:flex;flex-direction:column;align-items:flex-start;">'
        +'<div style="display:flex;align-items:center;gap:.4em;margin-bottom:.3em;flex-shrink:0;">'
        +'<div style="width:1.6em;height:1.6em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;font-size:.75em;flex-shrink:0;">'+ic((s.icons&&s.icons[i])?s.icons[i]:'',.85)+'</div>'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;font-size:.75em;font-weight:800;color:'+t.tc+';line-height:1.2;">'+es(h)+'</div></div>'
        +'<div style="width:28px;height:2px;background:'+t.ac+';margin-bottom:.3em;flex-shrink:0;"></div>'
        +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;font-size:.55em;color:'+t.bc+';line-height:1.35;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">'+es(desc)+'</div>'
        +'</div>';
    }
    function mpGrid(xOff,compact){
      var rows=[];
      if(nPts<=5){
        var top=items.slice(0,3).map(function(it,i){return mpCard(it,i,compact);}).join('');
        var bot=items.slice(3).map(function(it,i){return mpCard(it,i+3,compact);}).join('');
        rows.push('<div style="display:flex;gap:3%;flex:1.1;">'+top+'</div>');
        rows.push('<div style="display:flex;gap:3%;flex:1;">'+bot+'</div>');
      } else if(nPts===6){
        var r1=items.slice(0,3).map(function(it,i){return mpCard(it,i,compact);}).join('');
        var r2=items.slice(3,6).map(function(it,i){return mpCard(it,i+3,compact);}).join('');
        rows.push('<div style="display:flex;gap:3%;flex:1;">'+r1+'</div>');
        rows.push('<div style="display:flex;gap:3%;flex:1;">'+r2+'</div>');
      } else {
        var r1=items.slice(0,4).map(function(it,i){return mpCard(it,i,compact);}).join('');
        var r2=items.slice(4).map(function(it,i){return mpCard(it,i+4,compact);}).join('');
        rows.push('<div style="display:flex;gap:2%;flex:1;">'+r1+'</div>');
        rows.push('<div style="display:flex;gap:2%;flex:1;">'+r2+'</div>');
      }
      return '<div style="display:flex;flex-direction:column;gap:3%;height:100%;">'+rows.join('')+'</div>';
    }
    H+=ACB;
function ic(iName,sz){
if(!iName||iName.indexOf(':')<0) return '<div style="width:'+sz+'em;height:'+sz+'em;"></div>';
var u='https://api.iconify.design/'+iName.replace(':','/')+ '.svg?color=%23ffffff';
return '<img src="'+u+'" style="width:'+sz+'em;height:'+sz+'em;display:block;" onerror="this.style.display=\'none\';"/>';
}
window.ic = ic;
function mpLongCard(it,i){
var colon=String(it).indexOf(':'),h=colon>0?String(it).slice(0,colon).trim():'',desc=colon>0?String(it).slice(colon+1).trim():String(it);
var iName=(s.icons&&s.icons[i])?s.icons[i]:'';
return '<div style="flex:1;min-width:0;background:'+t.ac+'14;border-radius:12px;padding:3% 4%;pointer-events:auto;display:flex;align-items:center;gap:3%;">'
+'<div style="width:30%;flex-shrink:0;display:flex;flex-direction:column;align-items:center;text-align:center;">'
+'<div style="width:1.9em;height:1.9em;border-radius:50%;background:'+t.ac+';display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-bottom:.3em;">'+ic(iName,1)+'</div>'
+'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:1em;font-size:.6em;font-weight:800;color:'+t.tc+';line-height:1.2;">'+es(h)+'</div>'
+'</div>'
+'<div style="width:1px;align-self:stretch;background:'+t.ac+'50;flex-shrink:0;"></div>'
+'<div style="flex:1;min-width:0;padding-left:3%;">'
+'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:1em;font-size:.5em;color:'+t.bc+';line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">'+es(desc)+'</div>'
+'</div></div>';
}
function mpLongGrid(){
var rowsArr=[];
for(var r=0;r<Math.ceil(items.length/2);r++){
var a=items[r*2],b=items[r*2+1];
rowsArr.push('<div style="display:flex;gap:2.5%;flex:1;min-height:0;overflow:hidden;">'+mpLongCard(a,r*2)+(b!==undefined?mpLongCard(b,r*2+1):'<div style="flex:1;"></div>')+'</div>');
}
return '<div style="display:flex;flex-direction:column;gap:2.5%;height:100%;">'+rowsArr.join('')+'</div>';
}
if(v==='v4'||v==='v5'){
if(v==='v4'){
H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:3% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+mpLongGrid()+'</div></div>';
} else {
H+='<div style="position:absolute;top:3px;bottom:0;left:0;right:30%;display:flex;flex-direction:column;padding:3% 2% 3% 4%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+mpLongGrid()+'</div></div>';
H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:29%;');
}
return H+'</div>';
}

    if(v==='v2'){
      H+='<div style="position:absolute;top:3px;bottom:0;left:0;right:30%;display:flex;flex-direction:column;padding:3% 2% 3% 4%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+mpGrid(0,true)+'</div></div>';
      H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:29%;');
    } else if(v==='v3'){
      H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:29%;');
      H+='<div style="position:absolute;top:3px;bottom:0;left:30%;right:0;display:flex;flex-direction:column;padding:3% 4% 3% 2%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+mpGrid(0,true)+'</div></div>';
    } else {
      H+='<div style="position:absolute;top:3px;bottom:0;inset-inline:0;display:flex;flex-direction:column;padding:3% 5%;overflow:hidden;">'+TH2()+'<div style="flex:1;overflow:hidden;margin-top:2%;">'+mpGrid(0,false)+'</div></div>';
    }
  }
  else{H+='<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;"><div style="font-size:1.4em;font-weight:800;color:'+t.tc+';">'+es(s.title||'')+'</div></div>';}

  return H+'</div>';
}

function applyCanvas(idx,v){
  var main=document.getElementById('dsp-ppt-main-slide');
  if(!main||!S.data||!S.data.slides)return;
  var s=S.data.slides[idx];if(!s)return;
  var html=renderSlide(s,gt(),v,idx);
  main.innerHTML=html;
  // Also update the thumbnail in the left panel
  updateThumb(idx,html);
}
function updateThumb(idx,html){
  var thumbs=document.getElementById('dsp-ppt-thumbs');
  if(!thumbs||!thumbs.children[idx])return;
  var box=thumbs.children[idx].querySelector('.dsp-thumb-box,[style*="font-size:3"]');
  if(box){box.innerHTML=html;}
}
			 function vpPreloadAllSlides(){
  if(!S.data||!S.data.slides)return;
  S.data.slides.forEach(function(s,i){
    var vid=S.choices['slide_'+i]||'v1';
    var html=renderSlide(s,gt(),vid,i);
    updateThumb(i,html);
  });
  var activeIdx=(S.idx>=0)?S.idx:0;
  applyCanvas(activeIdx,S.choices['slide_'+activeIdx]||'v1');
  vpAugmentThumbs();
}
function vpAugmentThumbs(){
  var thumbs=document.getElementById('dsp-ppt-thumbs');
  if(!thumbs)return;
  Array.from(thumbs.children).forEach(function(item,i){
    item.setAttribute('draggable','true');
    item.style.position='relative';
    if(!item.querySelector('.vp-thumb-del')){
      var del=document.createElement('div');
      del.className='vp-thumb-del';
      del.innerHTML='&times;';
      del.title='Delete slide';
      item.appendChild(del);
    }
    item.querySelector('.vp-thumb-del').onclick=function(e){
      e.stopPropagation();
      vpDeleteSlide(i);
    };
    item.ondragstart=function(e){
      e.dataTransfer.effectAllowed='move';
      e.dataTransfer.setData('text/plain',String(i));
      item.classList.add('vp-dragging');
    };
    item.ondragend=function(){item.classList.remove('vp-dragging');};
    item.ondragover=function(e){e.preventDefault();item.classList.add('vp-drag-over');};
    item.ondragleave=function(){item.classList.remove('vp-drag-over');};
    item.ondrop=function(e){
      e.preventDefault();
      item.classList.remove('vp-drag-over');
      var fromIdx=parseInt(e.dataTransfer.getData('text/plain'),10);
      if(!isNaN(fromIdx)&&fromIdx!==i)vpMoveSlide(fromIdx,i);
    };
  });
}

window.dspSyncPreserveData=function(ppt){
  if(!ppt||!ppt.slides)return;
  var tries=0;
  var _wait=setInterval(function(){
    tries++;
    var thumbs=document.getElementById('dsp-ppt-thumbs');
    var ready=thumbs&&thumbs.children.length===ppt.slides.length;
    if(ready||tries>40){
      clearInterval(_wait);
      S.data=ppt;
      S.choices={};
      S.idx=0;
      try{sessionStorage.setItem('dspVPC','{}');}catch(e){}
      vpPreloadAllSlides();
      if(S.open)vpRender(0);
    }
  },100);
};
			 
window.vpDeleteSlide=function(idx){
  if(!S.data||!S.data.slides||S.data.slides.length<=1){
    if(window.dspToast)dspToast('Cannot delete the only slide','error');
    return;
  }
  if(!confirm('Delete slide '+(idx+1)+'?'))return;
  S.data.slides.splice(idx,1);
  if(window.G&&window.G.pptData&&window.G.pptData.slides)window.G.pptData.slides.splice(idx,1);
  var newChoices={};
  Object.keys(S.choices).forEach(function(k){
    var m=k.match(/^slide_(\d+)$/);
    if(!m)return;
    var n=parseInt(m[1],10);
    if(n<idx)newChoices[k]=S.choices[k];
    else if(n>idx)newChoices['slide_'+(n-1)]=S.choices[k];
  });
  S.choices=newChoices;
  try{sessionStorage.setItem('dspVPC',JSON.stringify(S.choices));}catch(e){}
  var newIdx=Math.min(idx,S.data.slides.length-1);
  S.idx=newIdx;
  if(window.dspRenderThumbs)window.dspRenderThumbs();
  if(window.dspSelectThumb)window.dspSelectThumb(newIdx);
  vpPreloadAllSlides();
};
window.vpMoveSlide=function(fromIdx,toIdx){
  if(!S.data||!S.data.slides)return;
  if(fromIdx<0||fromIdx>=S.data.slides.length||toIdx<0||toIdx>=S.data.slides.length)return;
  var moveArr=function(arr,from,to){var item=arr.splice(from,1)[0];arr.splice(to,0,item);};
  moveArr(S.data.slides,fromIdx,toIdx);
  if(window.G&&window.G.pptData&&window.G.pptData.slides)moveArr(window.G.pptData.slides,fromIdx,toIdx);
  var oldChoices=S.choices,newChoices={};
  var idxMap=[];
  for(var j=0;j<S.data.slides.length;j++)idxMap.push(j);
  moveArr(idxMap,fromIdx,toIdx);
  for(var p=0;p<idxMap.length;p++){
    var origPos=idxMap[p];
    if(oldChoices['slide_'+origPos]!==undefined)newChoices['slide_'+p]=oldChoices['slide_'+origPos];
  }
  S.choices=newChoices;
  try{sessionStorage.setItem('dspVPC',JSON.stringify(S.choices));}catch(e){}
  S.idx=toIdx;
  if(window.dspRenderThumbs)window.dspRenderThumbs();
  if(window.dspSelectThumb)window.dspSelectThumb(toIdx);
  vpPreloadAllSlides();
};
window.vpShowLoadOverlay=function(){
  var o=document.getElementById('vp-load-overlay');
  if(o)o.classList.add('open');
};
window.vpHideLoadOverlay=function(){
  var o=document.getElementById('vp-load-overlay');
  if(o)o.classList.remove('open');
};
var _shareUrl='';
window.dspSharePPT=function(){
  if(!S.data||!S.data.slides||!S.data.slides.length){
    if(window.dspToast)dspToast('Nothing to share yet','error');
    return;
  }
  var titleEl=document.getElementById('dsp-etitle');
  var title=titleEl?titleEl.textContent:'Presentation';
  var heroSlide=S.data.slides[S.idx>=0?S.idx:0]||S.data.slides[0];

  // Show the popup INSTANTLY using data we already have — no waiting.
  var prev=document.getElementById('vp-share-preview');
  if(prev){
    prev.innerHTML=(heroSlide.image_data?'<img src="'+heroSlide.image_data+'">':'')
      +'<div class="vp-share-cap"><span>'+es(heroSlide.title||title)+'</span></div>';
  }
  var infoTitle=document.getElementById('vp-share-info-title');
  if(infoTitle)infoTitle.textContent=title;
  var infoSub=document.getElementById('vp-share-info-sub');
  if(infoSub)infoSub.textContent='Generating your shareable link…';
  _shareUrl='';
  ['vp-share-wa','vp-share-copy','vp-share-tw','vp-share-fb'].forEach(function(id){
    var b=document.getElementById(id);if(b)b.disabled=true;
  });
  var m=document.getElementById('vp-share-modal');
  if(m)m.classList.add('open');

  // Now fetch the real link in the background.
  var slides=S.data.slides.map(function(s,i){
    var copy={};
    for(var k in s)copy[k]=s[k];
    copy.variant=S.choices['slide_'+i]||'v1';
    return copy;
  });
  var designEl=document.getElementById('dsp-design');
  var design=designEl&&designEl.value?designEl.value:'navy-pro';
  var url=window.location.origin+'/wp-json/ds/v1/create-share-link';
  var nc=S.nonce||window.DSP_NONCE||((window.wpApiSettings||{}).nonce||'');
  fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nc},
    body:JSON.stringify({title:title,design:design,slides:slides,share_token:S.shareToken||''})
  })
  .then(function(r){return r.json();})
  .then(function(d){
    if(d&&d.success&&d.share_url){
      S.shareToken=d.share_token;
      _shareUrl=d.share_url;
      if(infoSub)infoSub.textContent=S.data.slides.length+' slides · ready to share';
      ['vp-share-wa','vp-share-copy','vp-share-tw','vp-share-fb'].forEach(function(id){
        var b=document.getElementById(id);if(b)b.disabled=false;
      });
      try{navigator.clipboard.writeText(d.share_url);}catch(e){}
    }else{
      if(infoSub)infoSub.textContent='Could not create link — try again';
    }
  })
  .catch(function(){
    if(infoSub)infoSub.textContent='Network error — try again';
  });
};
window.vpShareTo=function(network){
  if(!_shareUrl)return;
  var title=(document.getElementById('vp-share-info-title')||{}).textContent||'My presentation';
  var u=encodeURIComponent(_shareUrl), t=encodeURIComponent(title);
  var dest='';
  if(network==='whatsapp')dest='https://wa.me/?text='+t+'%20'+u;
  if(network==='twitter')dest='https://twitter.com/intent/tweet?url='+u+'&text='+t;
  if(network==='facebook')dest='https://www.facebook.com/sharer/sharer.php?u='+u;
  if(dest)window.open(dest,'_blank');
};
window.vpShareCopy=function(){
  if(!_shareUrl)return;
  try{
    navigator.clipboard.writeText(_shareUrl);
    if(window.dspToast)dspToast('Link copied!','success');
  }catch(e){
    var ta=document.createElement('textarea');
    ta.value=_shareUrl;document.body.appendChild(ta);ta.select();
    try{document.execCommand('copy');}catch(e2){}
    document.body.removeChild(ta);
    if(window.dspToast)dspToast('Link copied!','success');
  }
};
window.vpShareClose=function(){
  var m=document.getElementById('vp-share-modal');
  if(m)m.classList.remove('open');
};
function vpSavePopup(state,msg){
  var old=document.getElementById('vp-save-popup');
  if(old&&old.parentNode)old.parentNode.removeChild(old);
  if(state==='hide')return;
  var overlay=document.createElement('div');
  overlay.id='vp-save-popup';
  overlay.style.cssText='position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;';
  var card=document.createElement('div');
  card.style.cssText='background:#fdf9f3;border-radius:18px;padding:36px 32px;max-width:340px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.4);font-family:-apple-system,BlinkMacSystemFont,sans-serif;';
  var iconHtml,textHtml;
  if(state==='saving'){
    iconHtml='<div style="width:54px;height:54px;margin:0 auto 16px;border:4px solid #cfe3fd;border-top-color:#2563eb;border-radius:50%;animation:dspSpin .8s linear infinite;"></div>';
    textHtml='<h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:#1a1a1a;">Saving...</h3>'
      +'<p style="margin:0;font-size:13px;color:#6b6b6b;line-height:1.5;">Hang tight, this won\'t take long.</p>';
  } else if(state==='success'){
    iconHtml='<div style="font-size:52px;margin:0 auto 12px;line-height:1;">✅</div>';
    textHtml='<h3 style="margin:0;font-size:19px;font-weight:800;color:#1a1a1a;">Saved!</h3>';
  } else {
    iconHtml='<div style="font-size:52px;margin:0 auto 12px;line-height:1;">⚠️</div>';
    textHtml='<h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:#1a1a1a;">Save Failed</h3>'
      +'<p style="margin:0;font-size:13px;color:#6b6b6b;line-height:1.5;">'+(msg||'Please try again.')+'</p>';
  }
  card.innerHTML=iconHtml+textHtml;
  overlay.appendChild(card);
  document.body.appendChild(overlay);
  if(state!=='saving'){
    setTimeout(function(){vpSavePopup('hide');},1400);
  }
}		
			 /* ── Save button popup: watches from the outside, doesn't touch dspSaveDraft or the save-ppt logic at all ── */
var _waitingForSaveToast=false;
document.addEventListener('click',function(e){
  var btn=e.target.closest('#dsp-savebtn');
  if(!btn)return;
  vpSavePopup('saving');
  _waitingForSaveToast=true;
},true);

var _toastObs=null;
function setupToastObs(){
  var toastEl=document.getElementById('dsp-toast');
  if(!toastEl||_toastObs)return;
  _toastObs=new MutationObserver(function(){
    if(!_waitingForSaveToast)return;
    if(toastEl.classList.contains('dsp-toast-show')){
      _waitingForSaveToast=false;
      var msgEl=document.getElementById('dsp-toast-msg');
      var isErr=toastEl.style.color==='#fca5a5';
      vpSavePopup(isErr?'error':'success',msgEl?msgEl.textContent:'');
    }
  });
  _toastObs.observe(toastEl,{attributes:true,attributeFilter:['class']});
}
var _toastWait=setInterval(function(){
  if(document.getElementById('dsp-toast')){
    clearInterval(_toastWait);
    setupToastObs();
  }
},300);

/* ── image gen popup ── */
			 var _vpOptIdx=-1;

window.vpImgOptionOpen=function(idx){
  _vpOptIdx=idx;
  // Show crop button only if slide already has an image
  var hasCrop=window.S&&window.S.data&&window.S.data.slides&&window.S.data.slides[idx]&&window.S.data.slides[idx].image_data;
  var cropBtn=document.getElementById('vp-img-option-crop-btn');
  if(cropBtn)cropBtn.style.display=hasCrop?'block':'none';
  var m=document.getElementById('vp-img-option-modal');
  if(m){m.style.display='flex';}
};

window.vpImgOptionCrop=function(){
  vpImgOptionClose();
  vpStartCropEdit(_vpOptIdx);
};

window.vpStartCropEdit=function(idx){
  var zone=document.querySelector('.dsp-vp-imgzone[data-slide-idx="'+idx+'"]');
  if(zone && typeof enterCropEdit==='function'){
    enterCropEdit(zone,idx);
  }
};

window.vpImgOptionClose=function(){
  var m=document.getElementById('vp-img-option-modal');
  if(m){m.style.display='none';}
};

window.vpImgOptionAI=function(){
  vpImgOptionClose();
  var _aiIdx = (window._vpMktTargetSlide>=0) ? window._vpMktTargetSlide : (_vpOptIdx>=0 ? _vpOptIdx : S.idx);
  vpImgClick(_aiIdx);
};

window.vpImgOptionStore=function(){
  vpImgOptionClose();
  var _storeIdx = (window._vpMktTargetSlide>=0) ? window._vpMktTargetSlide : (_vpOptIdx>=0 ? _vpOptIdx : S.idx);
  window._vpMktTargetSlide=_storeIdx;
  window._vpMktTargetVariant=S.choices['slide_'+_storeIdx]||'v2';
  if(window.dsMktPickOpen) window.dsMktPickOpen(_storeIdx);
};
window.vpImgClick=function(idx){
  S.genIdx=idx;
  var s=S.data&&S.data.slides?S.data.slides[idx]:null;
  var el=document.getElementById('vp-img-prompt');
  if(el)el.value=s&&s.image_query?s.image_query:(s&&s.title?s.title+' professional business':'');
  var st=document.getElementById('vp-img-status');if(st)st.textContent='';
  var btn=document.getElementById('vp-img-genbtn');if(btn){btn.disabled=false;btn.textContent='✨ Generate (3 credits)';}
  var m=document.getElementById('vp-imgmod');if(m)m.classList.add('open');
  var st=document.getElementById('vp-img-status');if(st)st.textContent='';
  var btn=document.getElementById('vp-img-genbtn');if(btn){btn.disabled=false;btn.textContent='✨ Generate (3 credits)';}
  var m=document.getElementById('vp-imgmod');if(m)m.classList.add('open');
};
window.vpImgClose=function(){var m=document.getElementById('vp-imgmod');if(m)m.classList.remove('open');};
window.vpDoGen=function(){
  var prompt=(document.getElementById('vp-img-prompt')||{value:''}).value.trim();
  if(!prompt)return;
  var btn=document.getElementById('vp-img-genbtn'),st=document.getElementById('vp-img-status');
  if(btn){btn.disabled=true;btn.textContent='⏳ Generating...';}
  if(st)st.textContent='Generating image (~15s)... (3 credits)';
  var url=S.imgUrl||window.DSP_IMG_URL||(window.location.origin+'/wp-json/ds/v1/fetch-slide-image');
  var nc=S.nonce||window.DSP_NONCE||((window.wpApiSettings||{}).nonce||'');
  fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nc},body:JSON.stringify({query:prompt})})
  .then(function(r){return r.json();})
  .then(function(d){
    if(d&&d.success&&d.image){
      var i=S.genIdx;
      if(S.data&&S.data.slides&&S.data.slides[i]){S.data.slides[i].image_data=d.image;S.data.slides[i].image_query=prompt;}
      var v=S.choices['slide_'+i]||'v1';
      if(v==='v1'){v='v2';S.choices['slide_'+i]='v2';}
      _cvApplying=true;
      applyCanvas(i,v);
      if(S.open && i>=0){vpRender(i);}
      setTimeout(function(){_cvApplying=false;},150);
      if(d.credits_balance!==undefined){
        if(window.G)window.G.credits=d.credits_balance;
        var ce=document.getElementById('ds-cr-display');
        if(ce)ce.textContent=d.credits_balance.toLocaleString();
      }
      if(st)st.textContent='✅ Done!';
      setTimeout(window.vpImgClose,700);
    }else{
      if(st)st.textContent='❌ '+(d&&d.message?d.message:'Failed — try a different prompt.');
      if(btn){btn.disabled=false;btn.textContent='✨ Try Again (3 credits)';}
    }
  })
  .catch(function(e){
    if(st)st.textContent='❌ '+e.message;
    if(btn){btn.disabled=false;btn.textContent='✨ Try Again (3 credits)';}
  });
};;

/* ── SVG thumbnail builder ── */
function mk(shapes,bg){
  bg=bg||'#1B2A4A';
  var s='<svg width="256" height="144" viewBox="0 0 256 144" xmlns="http://www.w3.org/2000/svg">'
    +'<rect width="256" height="144" fill="'+bg+'"/>'+shapes+'</svg>';
  try{return 'data:image/svg+xml;base64,'+btoa(s);}catch(e){return '';}
}
var IC='#2a4a6a';
// pre-build reusable shapes
var TXT_TITLE='<rect x="10" y="10" width="100" height="7" rx="2" fill="#fff" opacity=".85"/><rect x="10" y="21" width="60" height="3" rx="1" fill="#4A9EFF" opacity=".7"/>';
var TXT_TITLE_SM='<rect x="10" y="10" width="80" height="7" rx="2" fill="#fff" opacity=".85"/><rect x="10" y="21" width="50" height="3" rx="1" fill="#4A9EFF" opacity=".7"/>';
var IMG_R='<rect x="182" y="0" width="74" height="144" fill="'+IC+'" opacity=".92"/>';
var IMG_L='<rect x="0" y="0" width="74" height="144" fill="'+IC+'" opacity=".92"/>';
function rows(n,xOff){xOff=xOff||0;var r='';for(var i=0;i<n;i++){var y=31+i*20;r+='<circle cx="'+(17+xOff)+'" cy="'+(y+5)+'" r="3" fill="#4A9EFF" opacity=".8"/><rect x="'+(25+xOff)+'" y="'+y+'" width="70" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="'+(25+xOff)+'" y="'+(y+8)+'" width="90" height="3" rx="1" fill="#C8D8F0" opacity=".4"/>';}return r;}
function grid2x2(xOff){xOff=xOff||0;return '<rect x="'+(10+xOff)+'" y="30" width="'+(88)+'" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="'+(106+xOff)+'" y="30" width="88" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="'+(10+xOff)+'" y="82" width="88" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="'+(106+xOff)+'" y="82" width="88" height="50" rx="3" fill="#243a5a" opacity=".85"/>';}
function grid3(xOff){xOff=xOff||0;return '<rect x="'+(10+xOff)+'" y="30" width="64" height="104" rx="3" fill="#243a5a" opacity=".85"/><rect x="'+(82+xOff)+'" y="30" width="64" height="104" rx="3" fill="#243a5a" opacity=".85"/><rect x="'+(154+xOff)+'" y="30" width="64" height="104" rx="3" fill="#243a5a" opacity=".85"/>';}
function grid2(xOff){xOff=xOff||0;return '<rect x="'+(10+xOff)+'" y="30" width="108" height="104" rx="3" fill="#243a5a" opacity=".85"/><rect x="'+(126+xOff)+'" y="30" width="108" height="104" rx="3" fill="#243a5a" opacity=".85"/>';}

var V={
  title:[
    {id:'v1',lbl:'Text only',img:mk(TXT_TITLE+'<rect x="68" y="48" width="120" height="12" rx="2" fill="#fff" opacity=".9"/><rect x="88" y="66" width="80" height="4" rx="1" fill="#4A9EFF"/>')},
    {id:'v2',lbl:'Full background image',img:mk(TXT_TITLE_SM+'<rect x="38" y="42" width="180" height="14" rx="2" fill="#fff" opacity=".9"/><rect x="88" y="62" width="80" height="4" rx="1" fill="#4A9EFF"/><rect x="48" y="72" width="160" height="6" rx="1" fill="#C8D8F0" opacity=".6"/>','#1a3a5c')},
    {id:'v3',lbl:'Image right (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="102" y="0" width="154" height="144" fill="'+IC+'" opacity=".92"/><rect x="10" y="30" width="84" height="9" rx="2" fill="#fff" opacity=".9"/><rect x="10" y="44" width="50" height="3" rx="1" fill="#4A9EFF"/><rect x="10" y="52" width="80" height="5" rx="1" fill="#C8D8F0" opacity=".5"/>')},
    {id:'v4',lbl:'Image left (full bleed)',img:mk('<rect x="0" y="0" width="154" height="144" fill="'+IC+'" opacity=".92"/><rect x="164" y="30" width="82" height="9" rx="2" fill="#fff" opacity=".9"/><rect x="164" y="44" width="50" height="3" rx="1" fill="#4A9EFF"/><rect x="164" y="52" width="78" height="5" rx="1" fill="#C8D8F0" opacity=".5"/>')},
    {id:'v5',lbl:'Image bottom (full bleed)',img:mk(TXT_TITLE+'<rect x="50" y="10" width="156" height="11" rx="2" fill="#fff" opacity=".9"/><rect x="88" y="26" width="80" height="4" rx="1" fill="#4A9EFF"/><rect x="60" y="35" width="136" height="5" rx="1" fill="#C8D8F0" opacity=".5"/><line x1="0" y1="70" x2="256" y2="70" stroke="#4A9EFF" stroke-width="2" opacity=".6"/><rect x="0" y="72" width="256" height="72" fill="'+IC+'" opacity=".92"/>')}
  ],
  bullet:[
    {id:'v1',lbl:'List only',img:mk(TXT_TITLE_SM+rows(5))},
    {id:'v2',lbl:'Image right (full bleed)',img:mk(TXT_TITLE_SM+IMG_R+rows(4))},
    {id:'v3',lbl:'Image left (full bleed)',img:mk(IMG_L+'<rect x="84" y="10" width="80" height="7" rx="2" fill="#fff" opacity=".85"/><rect x="84" y="21" width="50" height="3" rx="1" fill="#4A9EFF" opacity=".7"/>'+rows(4,74))}
  ],
  feature_grid:[
    {id:'v1',lbl:'2×2 grid',img:mk(TXT_TITLE_SM+grid2x2())},
    {id:'v2',lbl:'Grid + image right (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="196" y="0" width="60" height="144" fill="'+IC+'" opacity=".92"/>'+grid2x2(-10))},
    {id:'v3',lbl:'Image left + grid (full bleed)',img:mk(IMG_L+'<rect x="82" y="10" width="80" height="7" rx="2" fill="#fff" opacity=".85"/><rect x="82" y="21" width="50" height="3" rx="1" fill="#4A9EFF" opacity=".7"/>'+grid2x2(72))},
    {id:'v4',lbl:'Row of 4 + image bottom (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="8" y="30" width="54" height="58" rx="3" fill="#243a5a" opacity=".85"/><rect x="68" y="30" width="54" height="58" rx="3" fill="#243a5a" opacity=".85"/><rect x="128" y="30" width="54" height="58" rx="3" fill="#243a5a" opacity=".85"/><rect x="188" y="30" width="60" height="58" rx="3" fill="#243a5a" opacity=".85"/><rect x="0" y="98" width="256" height="46" fill="'+IC+'" opacity=".92"/>')},
    {id:'v5',lbl:'Stacked 4 + image right (full bleed)',img:mk('<rect x="10" y="4" width="168" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="10" y="40" width="168" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="10" y="76" width="168" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="10" y="112" width="168" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="188" y="0" width="68" height="144" fill="'+IC+'" opacity=".92"/>')}
  ],
  two_column:[
    {id:'v1',lbl:'2 columns',img:mk(TXT_TITLE_SM+grid2())},
    {id:'v2',lbl:'Columns + image right (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="208" y="0" width="48" height="144" fill="'+IC+'" opacity=".92"/>'+grid2(-10))},
    {id:'v3',lbl:'Image left + columns (full bleed)',img:mk(IMG_L+'<rect x="82" y="10" width="80" height="7" rx="2" fill="#fff" opacity=".85"/>'+grid2(72))},
    {id:'v4',lbl:'Image bottom (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="100" height="7" rx="2" fill="#4A9EFF" opacity=".8"/><rect x="118" y="30" width="100" height="7" rx="2" fill="#4A9EFF" opacity=".8"/><rect x="10" y="42" width="100" height="26" rx="2" fill="#243a5a" opacity=".7"/><rect x="118" y="42" width="100" height="26" rx="2" fill="#243a5a" opacity=".7"/><rect x="0" y="80" width="256" height="64" fill="'+IC+'" opacity=".92"/>')},
    {id:'v5',lbl:'Cards over image (full bleed)',img:mk(TXT_TITLE_SM+grid2(),'#1a3a5c')},
    {id:'v6',lbl:'Stacked cards + image right (full bleed)',img:mk('<rect x="10" y="18" width="114" height="50" rx="4" fill="#243a5a" opacity=".85"/><rect x="10" y="76" width="114" height="50" rx="4" fill="#243a5a" opacity=".85"/><rect x="134" y="0" width="122" height="144" fill="'+IC+'" opacity=".92"/>')},
    {id:'v7',lbl:'Image left + split cards (full bleed)',img:mk('<rect x="0" y="0" width="122" height="144" fill="'+IC+'" opacity=".92"/><rect x="132" y="18" width="114" height="50" rx="4" fill="#243a5a" opacity=".85"/><circle cx="150" cy="43" r="7" fill="#4A9EFF"/><line x1="168" y1="24" x2="168" y2="62" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="176" y="30" width="60" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="176" y="40" width="50" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="132" y="76" width="114" height="50" rx="4" fill="#243a5a" opacity=".85"/><circle cx="150" cy="101" r="7" fill="#4A9EFF"/><line x1="168" y1="82" x2="168" y2="120" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="176" y="88" width="60" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="176" y="98" width="50" height="3" rx="1" fill="#C8D8F0" opacity=".4"/>')},
    {id:'v8',lbl:'Split cards + image right (full bleed)',img:mk('<rect x="10" y="18" width="114" height="50" rx="4" fill="#243a5a" opacity=".85"/><circle cx="28" cy="43" r="7" fill="#4A9EFF"/><line x1="46" y1="24" x2="46" y2="62" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="54" y="30" width="60" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="54" y="40" width="50" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="10" y="76" width="114" height="50" rx="4" fill="#243a5a" opacity=".85"/><circle cx="28" cy="101" r="7" fill="#4A9EFF"/><line x1="46" y1="82" x2="46" y2="120" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="54" y="88" width="60" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="54" y="98" width="50" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="134" y="0" width="122" height="144" fill="'+IC+'" opacity=".92"/>')}
  ],
  three_column:[
    {id:'v1',lbl:'3 columns',img:mk(TXT_TITLE_SM+grid3())},
    {id:'v2',lbl:'Columns + image right (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="210" y="0" width="46" height="144" fill="'+IC+'" opacity=".92"/>'+grid3(-8))},
    {id:'v3',lbl:'Image left + columns (full bleed)',img:mk(IMG_L+'<rect x="82" y="10" width="80" height="7" rx="2" fill="#fff" opacity=".85"/>'+grid3(72))},
    {id:'v4',lbl:'Image bottom (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="72" height="6" rx="2" fill="#4A9EFF" opacity=".8"/><rect x="92" y="30" width="72" height="6" rx="2" fill="#4A9EFF" opacity=".8"/><rect x="174" y="30" width="72" height="6" rx="2" fill="#4A9EFF" opacity=".8"/><rect x="10" y="40" width="72" height="24" rx="2" fill="#243a5a" opacity=".7"/><rect x="92" y="40" width="72" height="24" rx="2" fill="#243a5a" opacity=".7"/><rect x="174" y="40" width="72" height="24" rx="2" fill="#243a5a" opacity=".7"/><rect x="0" y="76" width="256" height="68" fill="'+IC+'" opacity=".92"/>')},
    {id:'v5',lbl:'Image left + stacked split cards (full bleed)',img:mk(IMG_L+'<rect x="82" y="4" width="168" height="40" rx="4" fill="#243a5a" opacity=".85"/><circle cx="98" cy="24" r="7" fill="#4A9EFF"/><line x1="116" y1="10" x2="116" y2="38" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="124" y="14" width="100" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="124" y="24" width="80" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="82" y="52" width="168" height="40" rx="4" fill="#243a5a" opacity=".85"/><circle cx="98" cy="72" r="7" fill="#4A9EFF"/><line x1="116" y1="58" x2="116" y2="86" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="124" y="62" width="100" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="124" y="72" width="80" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="82" y="100" width="168" height="40" rx="4" fill="#243a5a" opacity=".85"/><circle cx="98" cy="120" r="7" fill="#4A9EFF"/><line x1="116" y1="106" x2="116" y2="134" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="124" y="110" width="100" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="124" y="120" width="80" height="3" rx="1" fill="#C8D8F0" opacity=".4"/>')},
    {id:'v6',lbl:'Stacked split cards + image right (full bleed)',img:mk('<rect x="2" y="4" width="168" height="40" rx="4" fill="#243a5a" opacity=".85"/><circle cx="18" cy="24" r="7" fill="#4A9EFF"/><line x1="36" y1="10" x2="36" y2="38" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="44" y="14" width="100" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="44" y="24" width="80" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="2" y="52" width="168" height="40" rx="4" fill="#243a5a" opacity=".85"/><circle cx="18" cy="72" r="7" fill="#4A9EFF"/><line x1="36" y1="58" x2="36" y2="86" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="44" y="62" width="100" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="44" y="72" width="80" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><rect x="2" y="100" width="168" height="40" rx="4" fill="#243a5a" opacity=".85"/><circle cx="18" cy="120" r="7" fill="#4A9EFF"/><line x1="36" y1="106" x2="36" y2="134" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="44" y="110" width="100" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="44" y="120" width="80" height="3" rx="1" fill="#C8D8F0" opacity=".4"/>'+IMG_R)},
    {id:'v7',lbl:'2 cards + wide card + image right (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="82" height="50" rx="4" fill="#243a5a" opacity=".85"/><rect x="98" y="30" width="82" height="50" rx="4" fill="#243a5a" opacity=".85"/><rect x="10" y="86" width="170" height="46" rx="4" fill="#243a5a" opacity=".85"/><circle cx="26" cy="109" r="6" fill="#4A9EFF"/><line x1="42" y1="94" x2="42" y2="124" stroke="#4A9EFF" stroke-width="1" opacity=".5"/><rect x="50" y="100" width="80" height="4" rx="1" fill="#fff" opacity=".7"/><rect x="50" y="110" width="60" height="3" rx="1" fill="#C8D8F0" opacity=".4"/>'+IMG_R)}
  ],
  stats:[
    {id:'v1',lbl:'Stats centered',img:mk(TXT_TITLE_SM+'<rect x="18" y="56" width="64" height="18" rx="2" fill="#4A9EFF" opacity=".75"/><rect x="96" y="56" width="64" height="18" rx="2" fill="#4A9EFF" opacity=".75"/><rect x="174" y="56" width="64" height="18" rx="2" fill="#4A9EFF" opacity=".75"/>')},
    {id:'v2',lbl:'Stats + image right (full bleed)',img:mk(TXT_TITLE_SM+IMG_R+'<rect x="10" y="54" width="44" height="16" rx="2" fill="#4A9EFF" opacity=".75"/><rect x="64" y="54" width="44" height="16" rx="2" fill="#4A9EFF" opacity=".75"/><rect x="118" y="54" width="44" height="16" rx="2" fill="#4A9EFF" opacity=".75"/>')},
    {id:'v3',lbl:'Stats over image bg',img:mk('<rect x="18" y="56" width="64" height="18" rx="2" fill="#4A9EFF" opacity=".9"/><rect x="96" y="56" width="64" height="18" rx="2" fill="#4A9EFF" opacity=".9"/><rect x="174" y="56" width="64" height="18" rx="2" fill="#4A9EFF" opacity=".9"/>','#1a3a5c')},
    {id:'v5',lbl:'Stats + image bottom (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="18" y="40" width="52" height="14" rx="2" fill="#4A9EFF" opacity=".85"/><rect x="102" y="40" width="52" height="14" rx="2" fill="#4A9EFF" opacity=".85"/><rect x="186" y="40" width="52" height="14" rx="2" fill="#4A9EFF" opacity=".85"/><line x1="0" y1="86" x2="256" y2="86" stroke="#4A9EFF" stroke-width="2" opacity=".6"/><rect x="0" y="88" width="256" height="56" fill="'+IC+'" opacity=".92"/>')},
    {id:'v4',lbl:'Stats + image left (full bleed)',img:mk(IMG_L+'<rect x="84" y="10" width="80" height="7" rx="2" fill="#fff" opacity=".85"/><rect x="84" y="21" width="50" height="3" rx="1" fill="#4A9EFF" opacity=".7"/><rect x="84" y="54" width="44" height="16" rx="2" fill="#4A9EFF" opacity=".75"/><rect x="138" y="54" width="44" height="16" rx="2" fill="#4A9EFF" opacity=".75"/><rect x="192" y="54" width="44" height="16" rx="2" fill="#4A9EFF" opacity=".75"/>')}
  ],
  timeline:[
    {id:'v1',lbl:'Vertical timeline',img:mk(TXT_TITLE_SM+'<circle cx="22" cy="40" r="5" fill="#4A9EFF"/><rect x="20" y="45" width="4" height="14" rx="1" fill="#4A9EFF" opacity=".25"/><rect x="34" y="36" width="55" height="4" rx="1" fill="#4A9EFF" opacity=".8"/><rect x="34" y="44" width="120" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><circle cx="22" cy="70" r="5" fill="#4A9EFF"/><rect x="20" y="75" width="4" height="14" rx="1" fill="#4A9EFF" opacity=".25"/><rect x="34" y="66" width="55" height="4" rx="1" fill="#4A9EFF" opacity=".8"/><rect x="34" y="74" width="120" height="3" rx="1" fill="#C8D8F0" opacity=".4"/><circle cx="22" cy="100" r="5" fill="#4A9EFF"/><rect x="34" y="96" width="55" height="4" rx="1" fill="#4A9EFF" opacity=".8"/><rect x="34" y="104" width="120" height="3" rx="1" fill="#C8D8F0" opacity=".4"/>')},
    {id:'v2',lbl:'Horizontal timeline',img:mk(TXT_TITLE_SM+'<line x1="20" y1="93" x2="236" y2="93" stroke="#4A9EFF" stroke-width="2.5" opacity=".4"/><circle cx="52" cy="93" r="7" fill="#4A9EFF"/><circle cx="112" cy="93" r="7" fill="#4A9EFF"/><circle cx="172" cy="93" r="7" fill="#4A9EFF"/><circle cx="226" cy="93" r="7" fill="#4A9EFF"/><rect x="32" y="74" width="40" height="4" rx="1" fill="#4A9EFF" opacity=".8"/><rect x="92" y="74" width="40" height="4" rx="1" fill="#4A9EFF" opacity=".8"/><rect x="152" y="74" width="40" height="4" rx="1" fill="#4A9EFF" opacity=".8"/><rect x="206" y="74" width="40" height="4" rx="1" fill="#4A9EFF" opacity=".8"/>')},
    {id:'v3',lbl:'Zig-zag timeline',img:mk(TXT_TITLE_SM+'<line x1="128" y1="30" x2="128" y2="118" stroke="#4A9EFF" stroke-width="2" stroke-dasharray="4,3" opacity=".35"/><rect x="26" y="28" width="66" height="20" rx="4" fill="#243a5a" opacity=".85"/><circle cx="128" cy="38" r="6" fill="#4A9EFF"/><rect x="164" y="56" width="66" height="20" rx="4" fill="#243a5a" opacity=".85"/><circle cx="128" cy="66" r="6" fill="#4A9EFF"/><rect x="26" y="84" width="66" height="20" rx="4" fill="#243a5a" opacity=".85"/><circle cx="128" cy="94" r="6" fill="#4A9EFF"/><rect x="164" y="112" width="66" height="20" rx="4" fill="#243a5a" opacity=".85"/><circle cx="128" cy="122" r="6" fill="#4A9EFF"/>')},
    {id:'v4',lbl:'Curved journey timeline',img:mk(TXT_TITLE_SM+'<path d="M20,50 Q60,20 100,50 T180,50 T236,50" stroke="#4A9EFF" stroke-width="2" fill="none" opacity=".4"/><circle cx="20" cy="50" r="7" fill="#4A9EFF"/><circle cx="100" cy="50" r="7" fill="#4A9EFF"/><circle cx="180" cy="50" r="7" fill="#4A9EFF"/><rect x="8" y="64" width="24" height="4" rx="1" fill="#C8D8F0" opacity=".5"/><rect x="88" y="24" width="24" height="4" rx="1" fill="#C8D8F0" opacity=".5"/><rect x="168" y="64" width="24" height="4" rx="1" fill="#C8D8F0" opacity=".5"/>')},
    {id:'v5',lbl:'Milestone cards timeline',img:mk(TXT_TITLE_SM+'<line x1="14" y1="72" x2="242" y2="72" stroke="#4A9EFF" stroke-width="2" opacity=".3"/><circle cx="50" cy="72" r="5" fill="#4A9EFF"/><circle cx="128" cy="72" r="5" fill="#4A9EFF"/><circle cx="206" cy="72" r="5" fill="#4A9EFF"/><rect x="26" y="28" width="48" height="30" rx="5" fill="#243a5a" opacity=".85"/><rect x="104" y="86" width="48" height="30" rx="5" fill="#243a5a" opacity=".85"/><rect x="182" y="28" width="48" height="30" rx="5" fill="#243a5a" opacity=".85"/>')},
    {id:'v6',lbl:'Circular timeline',img:mk(TXT_TITLE_SM+'<circle cx="128" cy="76" r="20" fill="#4A9EFF" opacity=".25"/><circle cx="128" cy="40" r="7" fill="#4A9EFF"/><circle cx="160" cy="60" r="7" fill="#4A9EFF"/><circle cx="160" cy="96" r="7" fill="#4A9EFF"/><circle cx="128" cy="116" r="7" fill="#4A9EFF"/><circle cx="96" cy="96" r="7" fill="#4A9EFF"/><circle cx="96" cy="60" r="7" fill="#4A9EFF"/>')},
    {id:'v8',lbl:'Timeline over image (overlay)',img:mk('<circle cx="22" cy="46" r="5" fill="#4A9EFF"/><rect x="20" y="52" width="2" height="26" fill="#4A9EFF" opacity=".4"/><circle cx="22" cy="84" r="5" fill="#4A9EFF"/><rect x="36" y="40" width="120" height="6" rx="2" fill="#fff" opacity=".8"/><rect x="36" y="52" width="170" height="4" rx="2" fill="#fff" opacity=".55"/><rect x="36" y="78" width="120" height="6" rx="2" fill="#fff" opacity=".8"/><rect x="36" y="90" width="170" height="4" rx="2" fill="#fff" opacity=".55"/>','#1a3a5c')},
    {id:'v9',lbl:'Vertical timeline + image right (full bleed)',img:mk(TXT_TITLE_SM+'<rect x="102" y="0" width="154" height="144" fill="'+IC+'" opacity=".92"/><circle cx="16" cy="42" r="4" fill="#4A9EFF"/><rect x="26" y="38" width="64" height="5" rx="2" fill="#fff" opacity=".85"/><circle cx="16" cy="62" r="4" fill="#4A9EFF"/><rect x="26" y="58" width="70" height="5" rx="2" fill="#fff" opacity=".85"/><circle cx="16" cy="82" r="4" fill="#4A9EFF"/><rect x="26" y="78" width="58" height="5" rx="2" fill="#fff" opacity=".85"/><circle cx="16" cy="102" r="4" fill="#4A9EFF"/><rect x="26" y="98" width="66" height="5" rx="2" fill="#fff" opacity=".85"/>')},
    {id:'v10',lbl:'Horizontal timeline + image bottom (full bleed)',img:mk(TXT_TITLE_SM+'<line x1="20" y1="60" x2="236" y2="60" stroke="#4A9EFF" stroke-width="2" opacity=".5"/><circle cx="46" cy="60" r="5" fill="#4A9EFF"/><circle cx="110" cy="60" r="5" fill="#4A9EFF"/><circle cx="174" cy="60" r="5" fill="#4A9EFF"/><rect x="26" y="44" width="40" height="4" rx="2" fill="#fff" opacity=".8"/><rect x="90" y="44" width="40" height="4" rx="2" fill="#fff" opacity=".8"/><rect x="154" y="44" width="40" height="4" rx="2" fill="#fff" opacity=".8"/><line x1="0" y1="86" x2="256" y2="86" stroke="#4A9EFF" stroke-width="2" opacity=".6"/><rect x="0" y="88" width="256" height="56" fill="'+IC+'" opacity=".92"/>')},
    {id:'v7',lbl:'Stepped growth timeline',img:mk(TXT_TITLE_SM+'<rect x="24" y="98" width="32" height="26" rx="3" fill="#243a5a" opacity=".85"/><rect x="66" y="82" width="32" height="42" rx="3" fill="#243a5a" opacity=".85"/><rect x="108" y="64" width="32" height="60" rx="3" fill="#243a5a" opacity=".85"/><rect x="150" y="44" width="32" height="80" rx="3" fill="#243a5a" opacity=".85"/><rect x="192" y="26" width="32" height="98" rx="3" fill="#243a5a" opacity=".85"/><circle cx="40" cy="98" r="5" fill="#4A9EFF"/><circle cx="82" cy="82" r="5" fill="#4A9EFF"/><circle cx="124" cy="64" r="5" fill="#4A9EFF"/><circle cx="166" cy="44" r="5" fill="#4A9EFF"/><circle cx="208" cy="26" r="5" fill="#4A9EFF"/>')}
  ],
  quote:[
    {id:'v1',lbl:'Quote centered',img:mk('<text x="128" y="52" font-size="36" fill="#4A9EFF" opacity=".5" text-anchor="middle" font-family="serif">&#34;&#34;</text><rect x="28" y="60" width="200" height="6" rx="2" fill="#fff" opacity=".7"/><rect x="48" y="70" width="160" height="5" rx="2" fill="#fff" opacity=".6"/><rect x="88" y="88" width="80" height="4" rx="1" fill="#4A9EFF" opacity=".8"/>')},
    {id:'v2',lbl:'Quote + image right (full bleed)',img:mk(IMG_R+'<text x="72" y="46" font-size="28" fill="#4A9EFF" opacity=".5" text-anchor="middle" font-family="serif">&#34;&#34;</text><rect x="10" y="52" width="150" height="5" rx="2" fill="#fff" opacity=".7"/><rect x="10" y="62" width="130" height="4" rx="2" fill="#fff" opacity=".6"/><rect x="10" y="78" width="80" height="3" rx="1" fill="#4A9EFF" opacity=".7"/>')},
    {id:'v3',lbl:'Image left + quote (full bleed)',img:mk(IMG_L+'<text x="175" y="46" font-size="28" fill="#4A9EFF" opacity=".5" text-anchor="middle" font-family="serif">&#34;&#34;</text><rect x="84" y="52" width="160" height="5" rx="2" fill="#fff" opacity=".7"/><rect x="84" y="62" width="140" height="4" rx="2" fill="#fff" opacity=".6"/><rect x="84" y="78" width="80" height="3" rx="1" fill="#4A9EFF" opacity=".7"/>')},
    {id:'v4',lbl:'Quote over image (overlay)',img:mk('<text x="128" y="52" font-size="36" fill="#4A9EFF" opacity=".5" text-anchor="middle" font-family="serif">&#34;&#34;</text><rect x="28" y="60" width="200" height="6" rx="2" fill="#fff" opacity=".7"/><rect x="48" y="70" width="160" height="5" rx="2" fill="#fff" opacity=".6"/><rect x="88" y="88" width="80" height="4" rx="1" fill="#4A9EFF" opacity=".8"/>','#1a3a5c')},
    {id:'v5',lbl:'Quote + image bottom (full bleed)',img:mk('<text x="128" y="34" font-size="26" fill="#4A9EFF" opacity=".5" text-anchor="middle" font-family="serif">&#34;&#34;</text><rect x="38" y="42" width="180" height="5" rx="2" fill="#fff" opacity=".7"/><rect x="58" y="52" width="140" height="4" rx="2" fill="#fff" opacity=".6"/><rect x="88" y="64" width="80" height="3" rx="1" fill="#4A9EFF" opacity=".8"/><line x1="0" y1="86" x2="256" y2="86" stroke="#4A9EFF" stroke-width="2" opacity=".6"/><rect x="0" y="88" width="256" height="56" fill="'+IC+'" opacity=".92"/>')}
  ],
  five_point:[
    {id:'v1',lbl:'5 cards – no image',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="70" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="88" y="30" width="70" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="166" y="30" width="70" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="10" y="88" width="108" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="126" y="88" width="110" height="48" rx="3" fill="#243a5a" opacity=".85"/>')},
    {id:'v2',lbl:'5 cards + image right',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="58" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="76" y="30" width="58" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="142" y="30" width="58" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="10" y="84" width="88" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="106" y="84" width="94" height="46" rx="3" fill="#243a5a" opacity=".85"/>'+IMG_R)},
    {id:'v3',lbl:'Image left + 5 cards',img:mk(IMG_L+'<rect x="84" y="10" width="70" height="6" rx="2" fill="#fff" opacity=".85"/><rect x="84" y="20" width="46" height="3" rx="1" fill="#4A9EFF" opacity=".7"/><rect x="84" y="30" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="144" y="30" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="204" y="30" width="44" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="84" y="84" width="78" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="170" y="84" width="78" height="46" rx="3" fill="#243a5a" opacity=".85"/>')},
{id:'v4',lbl:'Long cards – no image (5)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="28" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="64" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="64" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="100" width="240" height="30" rx="4" fill="#243a5a" opacity=".85"/>')},
{id:'v5',lbl:'Long cards + image right (5)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="28" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="64" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="64" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="100" width="182" height="30" rx="4" fill="#243a5a" opacity=".85"/>'+IMG_R)}
  ],
  six_point:[
    {id:'v1',lbl:'6 cards – no image',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="70" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="88" y="30" width="70" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="166" y="30" width="70" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="10" y="84" width="70" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="88" y="84" width="70" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="166" y="84" width="70" height="50" rx="3" fill="#243a5a" opacity=".85"/>')},
    {id:'v2',lbl:'6 cards + image right',img:mk(TXT_TITLE_SM+'<rect x="10" y="30" width="56" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="74" y="30" width="56" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="138" y="30" width="54" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="10" y="84" width="56" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="74" y="84" width="56" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="138" y="84" width="54" height="46" rx="3" fill="#243a5a" opacity=".85"/>'+IMG_R)},
    {id:'v3',lbl:'Image left + 6 cards',img:mk(IMG_L+'<rect x="84" y="10" width="70" height="6" rx="2" fill="#fff" opacity=".85"/><rect x="84" y="20" width="46" height="3" rx="1" fill="#4A9EFF" opacity=".7"/><rect x="84" y="30" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="144" y="30" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="204" y="30" width="44" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="84" y="84" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="144" y="84" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="204" y="84" width="44" height="46" rx="3" fill="#243a5a" opacity=".85"/>')},
{id:'v4',lbl:'Long cards – no image (6)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="28" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="64" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="64" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="100" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="100" width="117" height="30" rx="4" fill="#243a5a" opacity=".85"/>')},
{id:'v5',lbl:'Long cards + image right (6)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="28" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="64" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="64" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="100" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="100" width="88" height="30" rx="4" fill="#243a5a" opacity=".85"/>'+IMG_R)}
  ],
  seven_point:[
    {id:'v1',lbl:'7 cards – no image',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="68" y="28" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="128" y="28" width="52" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="188" y="28" width="60" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="8" y="82" width="74" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="90" y="82" width="74" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="172" y="82" width="74" height="50" rx="3" fill="#243a5a" opacity=".85"/>')},
    {id:'v2',lbl:'7 cards + image right',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="56" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="104" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="152" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="8" y="80" width="58" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="74" y="80" width="60" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="142" y="80" width="50" height="48" rx="3" fill="#243a5a" opacity=".85"/>'+IMG_R)},
    {id:'v3',lbl:'Image left + 7 cards',img:mk(IMG_L+'<rect x="84" y="10" width="70" height="6" rx="2" fill="#fff" opacity=".85"/><rect x="84" y="20" width="46" height="3" rx="1" fill="#4A9EFF" opacity=".7"/><rect x="84" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="132" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="180" y="28" width="40" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="220" y="28" width="28" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="84" y="80" width="54" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="146" y="80" width="54" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="208" y="80" width="40" height="48" rx="3" fill="#243a5a" opacity=".85"/>')},
{id:'v4',lbl:'Long cards – no image (7)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="117" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="28" width="117" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="55" width="117" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="55" width="117" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="82" width="117" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="82" width="117" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="109" width="240" height="21" rx="4" fill="#243a5a" opacity=".85"/>')},
{id:'v5',lbl:'Long cards + image right (7)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="88" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="28" width="88" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="55" width="88" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="55" width="88" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="82" width="88" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="82" width="88" height="21" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="109" width="182" height="21" rx="4" fill="#243a5a" opacity=".85"/>'+IMG_R)}
  ],
eight_point:[
{id:'v1',lbl:'8 cards – no image',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="57" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="71" y="28" width="57" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="134" y="28" width="57" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="197" y="28" width="51" height="46" rx="3" fill="#243a5a" opacity=".85"/><rect x="8" y="80" width="57" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="71" y="80" width="57" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="134" y="80" width="57" height="50" rx="3" fill="#243a5a" opacity=".85"/><rect x="197" y="80" width="51" height="50" rx="3" fill="#243a5a" opacity=".85"/>')},
{id:'v2',lbl:'8 cards + image right',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="41" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="53" y="28" width="41" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="98" y="28" width="41" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="143" y="28" width="41" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="8" y="78" width="41" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="53" y="78" width="41" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="98" y="78" width="41" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="143" y="78" width="41" height="48" rx="3" fill="#243a5a" opacity=".85"/>'+IMG_R)},
{id:'v3',lbl:'Image left + 8 cards',img:mk(IMG_L+'<rect x="84" y="10" width="70" height="6" rx="2" fill="#fff" opacity=".85"/><rect x="84" y="20" width="46" height="3" rx="1" fill="#4A9EFF" opacity=".7"/><rect x="84" y="30" width="38" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="126" y="30" width="38" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="168" y="30" width="38" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="210" y="30" width="38" height="44" rx="3" fill="#243a5a" opacity=".85"/><rect x="84" y="78" width="38" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="126" y="78" width="38" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="168" y="78" width="38" height="48" rx="3" fill="#243a5a" opacity=".85"/><rect x="210" y="78" width="38" height="48" rx="3" fill="#243a5a" opacity=".85"/>')},
{id:'v4',lbl:'Long cards – no image (8)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="28" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="56" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="56" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="84" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="84" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="112" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="131" y="112" width="117" height="22" rx="4" fill="#243a5a" opacity=".85"/>')},
{id:'v5',lbl:'Long cards + image right (8)',img:mk(TXT_TITLE_SM+'<rect x="8" y="28" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="28" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="56" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="56" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="84" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="84" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="8" y="112" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/><rect x="102" y="112" width="88" height="22" rx="4" fill="#243a5a" opacity=".85"/>'+IMG_R)}
]
};
/* ================================================================
   DS CLASSIC EDITOR (added in this clone)
   - "Edit slide" panel for every Classic slide (like Edit Diagram):
     title, subtitle, points (add / delete / reorder, up to 14),
     Key takeaway
   - 9-14 point layouts (many_point) for the editor and the PPTX
   - Key takeaway strip under every point slide
   - Company logo in a corner of every slide (editor + PPTX)
   Template decks (DS Template Engine) and chart/diagram slides are
   left exactly as they were.
   ================================================================ */
var CE_MAX=16;function ceMax(){return ceTplOn()?16:CE_MAX;} /* template decks: up to 16 points */
var CE_PT=['bullet','two_column','three_column','feature_grid','five_point','six_point','seven_point','eight_point','many_point'];
var CE_CAP={stats:4,timeline:5,quote:1,chartk:4}; /* template Numbers slides show up to 4 numbers */function ceCap(k){return k==='points'?ceMax():(k==='stats'&&!ceTplOn()?16:CE_CAP[k]);} /* classic Numbers slides: up to 16 numbers */
var CE_TK=CE_PT.concat(['stats','chart']);
function ceTkOk(s){return !!s&&CE_TK.indexOf(ceType(s))>=0&&!s.dg;}
function ceType(s){return dsNormType((s&&s.type)||'bullet');}
function ceIsPoint(s){return !!s&&CE_PT.indexOf(ceType(s))>=0;}
function dsceMany(s){return ceIsPoint(s)&&(s.content||[]).length>=9;}
window.dsceMany=dsceMany;
function ceTplOn(){try{return !!(window.DS_TPL_ACTIVE&&window.DS_TPL_ACTIVE());}catch(e){return false;}}
function ceDecks(){var a=[];if(S.data)a.push(S.data);if(window.G&&G.pptData&&a.indexOf(G.pptData)<0)a.push(G.pptData);return a;}
function ceLogo(){var d=ceDecks();for(var i=0;i<d.length;i++){if(d[i].logo&&d[i].logo.src)return d[i].logo;}return null;}
window.dsceDeckLogo=function(){var L=ceLogo();if(!L)return null;var N=ceLogoN(L),C=ceCoverN(L);return {src2:L.src2||'',w2:L.w2||0,h2:L.h2||0,glow:(typeof L.glow==='number'?L.glow:50),sglow:(typeof L.sglow==='number'?L.sglow:50),sbadge:L.sbadge===true,sbgv:isFinite(parseFloat(L.sbgv))?+L.sbgv:'',cmode:C.mode,cx:C.x,cy:C.y,csz:C.sz,src:L.src,x:N.x,y:N.y,sz:N.sz,w:L.w||0,h:L.h||0,cover:L.cover!==false,badge:L.badge!==false,bgv:isFinite(parseFloat(L.bgv))?+L.bgv:''};};
function cePad(i){return (i<9?'0':'')+(i+1);}
function ceSplit(x){var c=String(x==null?'':x),i=c.indexOf(':');return i>0&&i<80?{h:c.slice(0,i).trim(),d:c.slice(i+1).trim()}:{h:'',d:c.trim()};}
/* logo box in a 1280 x 720 slide (the PPTX builder uses the same numbers) */
/* logo placement: x (0 = left, 100 = right), y (0 = top, 100 = bottom), sz (0 = small … 100 = whole slide) */
function ceLogoN(L){
  var p=L.pos||'tr',dz={s:12,m:22,l:34}[L.size||'m']||22;
  function n(v,d){v=parseFloat(v);return isFinite(v)?Math.max(0,Math.min(100,v)):d;}
  return {x:n(L.x,p.charAt(1)==='l'?0:100),y:n(L.y,p.charAt(0)==='b'?100:0),sz:n(L.sz,dz)};
}
function ceLogoBox(L){
  var N=ceLogoN(L),a=(L.w&&L.h)?L.w/L.h:2.5,f=N.sz/100;
  var w=50+1230*Math.pow(f,1.6),h=w/a;
  if(h>720){h=720;w=h*a;}if(w>1280){w=1280;h=w/a;}
  var m=26*(1-f),x=m+(1280-2*m-w)*N.x/100,y=m+(720-2*m-h)*N.y/100;
  return {x:x,y:y,w:w,h:h};
}
/* cover slide (main title slide) has its own placement:
   cmode 'auto' = in the title block above the title, 'free' = anywhere (cx 0 = left … 100 = right, cy 0 = top … 100 = bottom), csz = its own size */
function ceCoverN(L){
function n(v,d){v=parseFloat(v);return isFinite(v)?Math.max(0,Math.min(100,v)):d;}
return {mode:L&&L.cmode==='free'?'free':'auto',x:n(L&&L.cx,50),y:n(L&&L.cy,0),sz:n(L&&L.csz,ceLogoN(L||{}).sz)};
}
/* free cover logo box in 1280 x 720 units, with the badge around it (PPTX uses the same numbers) */
function ceCoverFreeBox(L,pad){
var C=ceCoverN(L),b=ceLogoBox({w:L.w,h:L.h,x:C.x,y:C.y,sz:C.sz});
if(!pad)return {x:b.x,y:b.y,w:b.w,h:b.h,bx:b.x,by:b.y,bw:b.w,bh:b.h};
var px=Math.max(14,b.h*.22),py=Math.max(8,b.h*.14),bx=b.x-px,by=b.y-py,bw=b.w+2*px,bh=b.h+2*py;
if(bw>1280||bh>720){bx=b.x;by=b.y;bw=b.w;bh=b.h;}
var dx=bx<0?-bx:(bx+bw>1280?1280-(bx+bw):0),dy=by<0?-by:(by+bh>720?720-(by+bh):0);
return {x:b.x+dx,y:b.y+dy,w:b.w,h:b.h,bx:bx+dx,by:by+dy,bw:bw,bh:bh};
}
/* logo + badge boxes (1280x720) for design-template decks; the template engine draws them */
window.dsceLogoEls=function(L,cover,bg,ac){
if(!L||!L.src)return null;
var Lx;
if(cover){var C0=ceCoverN(L);Lx={src:L.src,w:L.w,h:L.h,cmode:'free',cx:C0.x,cy:C0.y,csz:C0.sz,badge:L.badge!==false,bgv:L.bgv};}
else{var N0=ceLogoN(L);Lx={src:L.src,w:L.w,h:L.h,cmode:'free',cx:N0.x,cy:N0.y,csz:N0.sz,badge:L.sbadge===true,bgv:L.sbgv};}
var bd=ceBadge(Lx,isDarkColor(/^#[0-9a-fA-F]{6}$/.test(bg||'')?bg:'#1B2A4A')),b=ceCoverFreeBox(Lx,!!bd);
var out={logo:{x:b.x,y:b.y,w:b.w,h:b.h}};
if(bd)out.badge={x:b.bx,y:b.by,w:b.bw,h:b.bh,fill:bd.white?'#FFFFFF':'#000000',op:+bd.a.toFixed(3),r:.18*Math.min(b.bw,b.bh)};
return out;
};
function ceCoverFreeHtml(L,t){
var m=String(t.bg).match(/#[0-9a-fA-F]{6}/),bd=ceBadge(L,isDarkColor(m?m[0]:'#1B2A4A')),b=ceCoverFreeBox(L,!!bd),o='';
function pc(v,d){return (v/d*100).toFixed(3)+'%';}
if(bd){var r=.18*Math.min(b.bw,b.bh);
o+='<div class="dsce-logo-badge" style="position:absolute;left:'+pc(b.bx,1280)+';top:'+pc(b.by,720)+';width:'+pc(b.bw,1280)+';height:'+pc(b.bh,720)+';box-sizing:border-box;border-radius:'+pc(r,b.bw)+' / '+pc(r,b.bh)+';background:'+bd.css+';border:1px solid '+t.ac+'66;'+ceGlow(L,t)+'z-index:59;pointer-events:none;"></div>';}
return o+'<img class="dsce-logo" alt="" src="'+L.src+'" style="position:absolute;left:'+pc(b.x,1280)+';top:'+pc(b.y,720)+';width:'+pc(b.w,1280)+';height:'+pc(b.h,720)+';object-fit:contain;z-index:60;pointer-events:none;"/>';
}
/* cover slide logo: centred at the top, inside a soft badge (PPTX uses the same numbers) */
function ceCoverBox(L){
  var N={sz:ceCoverN(L).sz},sz=[200+2.6*N.sz,50+0.5*N.sz];
  var a=(L.w&&L.h)?L.w/L.h:2.5,w=sz[0],h=w/a;if(h>sz[1]){h=sz[1];w=h*a;}
  var px=Math.max(18,h*.28),py=12,bw=w+2*px,bh=h+2*py,by=(115-bh)/2+4;
  return {x:640-w/2,y:by+py,w:w,h:h,bx:640-bw/2,by:by,bw:bw,bh:bh};
}
/* cover slide: the uploaded logo replaces the AI's placeholder company mark, softly highlighted */
/* cover badge behind the logo: L.badge false = none; L.bgv 0 = solid dark … 50 = clear … 100 = solid light */
function ceBadge(L,dark){
  if(L.badge===false)return null;
  var v=parseFloat(L.bgv);if(!isFinite(v))v=dark?56:47;v=Math.max(0,Math.min(100,v));
  var a=Math.abs(v-50)/50,c=v>=50?'255,255,255':'0,0,0';
  return {css:'rgba('+c+','+a.toFixed(2)+')',white:v>=50,a:a,v:v};
}
window.dsceCoverLogoHtml=function(jc,t){
  var L=ceLogo();if(!L||L.cover===false||ceTplOn())return '';
if(ceCoverN(L).mode==='free')return '<span style="display:none"></span>'; /* placed freely on the cover by ceWrap */
  var b=ceCoverBox(L),dark=isDarkColor(String(t.bg).match(/#[0-9a-fA-F]{6}/)?String(t.bg).match(/#[0-9a-fA-F]{6}/)[0]:'#1B2A4A');
  var bd=ceBadge(L,dark),box=bd?'background:'+bd.css+';border:1px solid '+t.ac+'66;'+ceGlow(L,t)+'':'background:transparent;';
  return '<div style="display:flex;justify-content:'+jc+';margin:1.4em 0 .6em;pointer-events:none;flex-shrink:0;">'
    +'<div style="display:inline-flex;align-items:center;justify-content:center;padding:.55em 1.1em;border-radius:.7em;'+box+'">'
    +'<img class="dsce-logo" alt="" src="'+L.src+'" style="display:block;width:'+(b.w/32).toFixed(2)+'em;height:'+(b.h/32).toFixed(2)+'em;object-fit:contain;"/></div></div>';
};
/* DS logo options: glow round the badge (0 none .. 100 strong, 50 = default) */
function ceGlow(L,t){var g=(L&&typeof L.glow==='number')?L.glow:50;g=Math.max(0,Math.min(100,g));if(g<1)return '';var a=Math.min(255,Math.round(64*g/50)).toString(16);if(a.length<2)a='0'+a;return 'box-shadow:0 0 '+(1.2*g/50).toFixed(2)+'em '+t.ac+a+';';}
function ceLogoHtml(L,t){
if(L&&L.src2){var L2={};for(var k2 in L)L2[k2]=L[k2];L2.src=L.src2;L2.w=L.w2||L.w;L2.h=L.h2||L.h;L=L2;}   /* the slides after the cover have their own (short) logo */
/* other slides: optional badge behind the logo (same look as the cover badge) */
if(t&&L.sbadge===true){var SN=ceLogoN(L);return ceCoverFreeHtml({src:L.src,w:L.w,h:L.h,cmode:'free',cx:SN.x,cy:SN.y,csz:SN.sz,badge:true,bgv:L.sbgv,glow:(typeof L.sglow==='number'?L.sglow:50)},t);}
  var b=ceLogoBox(L);
  return '<img class="dsce-logo" alt="" src="'+L.src+'" style="position:absolute;left:'+(b.x/12.8)+'%;top:'+(b.y/7.2)+'%;width:'+(b.w/12.8)+'%;height:'+(b.h/7.2)+'%;object-fit:contain;z-index:60;pointer-events:none;"/>';
}
/* the strip lines up exactly with the content boxes: its left and right edges are taken from the slide's own title block, which spans the same column as the boxes in every layout.
   Called by a hidden marker inside the strip as soon as the slide is on the page; an interval retries strips that were not visible yet. */
window.dsceAlign=function(im){
  var tk=im&&im.parentNode;if(!tk||!tk.classList||!tk.classList.contains('dsce-tk'))return false;
  var root=tk.parentNode;if(!root)return false;
  var ti=root.querySelector('[data-ed-field="title"]');if(!ti)return false;
  var rr=root.getBoundingClientRect(),tt=ti.getBoundingClientRect();if(!(rr.width>4)||!(tt.width>4))return false;
  var l=(tt.left-rr.left)/rr.width*100,r=(rr.right-tt.right)/rr.width*100;
  if(l<0||r<0||l+r>75)return false;
  tk.style.left=Math.max(l,parseFloat(tk.getAttribute('data-lgl'))||0).toFixed(2)+'%';
  tk.style.right=Math.max(r,parseFloat(tk.getAttribute('data-lgr'))||0).toFixed(2)+'%';
  if(im.parentNode)im.parentNode.removeChild(im);
  return true;
};
setInterval(function(){var a=document.querySelectorAll('.dsce-tk img.dsce-al');for(var i=0;i<a.length&&i<80;i++){try{window.dsceAlign(a[i]);}catch(e){}}},900);
/* a full-height picture beside the text (image left / right layouts): where it sits and how wide it is */
function ceSideImg(H){
  var m=/class="dsp-vp-imgzone" data-slide-idx="\d+" style="top:0;bottom:0;(?:(right|left):0;width:([\d.]+)%;|left:([\d.]+)%;width:([\d.]+)%;)"/.exec(H);if(!m)return null;
  if(m[1])return {side:m[1],w:parseFloat(m[2]),css:'top:0;bottom:0;'+m[1]+':0;width:'+m[2]+'%;'};
  var l=parseFloat(m[3]),w=parseFloat(m[4]);if(Math.abs(l+w-100)>1)return null;
  return {side:'right',w:w,css:'top:0;bottom:0;left:'+m[3]+'%;width:'+m[4]+'%;'};
}
function ceStrip(tk,t,idx,L,sd){
  var lb=L?ceLogoBox(L):null,lft=4.5,rgt=4.5;
  if(sd){if(sd.side==='right'){rgt=Math.max(rgt,sd.w+2.6);lft=5.3;}else{lft=Math.max(lft,sd.w+2.6);rgt=5.3;}}   /* the strip stays inside the text area */
  /* a logo sitting on the strip's row (and not slide-sized) shortens the strip on its side */
  var lgl=0,lgr=0;
  if(lb&&lb.y+lb.h>629&&lb.y<694&&lb.w<640){if(lb.x+lb.w/2<640){lgl=(lb.x+lb.w)/12.8+1.2;lft=Math.max(lft,lgl);}else{lgr=(1280-lb.x)/12.8+1.2;rgt=Math.max(rgt,lgr);}}
  var bulb='<svg viewBox="0 0 24 24" style="width:1.35em;height:1.35em;flex-shrink:0;" fill="'+t.ac+'"><path d="M12 2a7 7 0 0 0-4 12.7V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.3A7 7 0 0 0 12 2zm-3 18h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-1z"/></svg>';
  return '<div class="dsce-tk" data-lgl="'+lgl.toFixed(2)+'" data-lgr="'+lgr.toFixed(2)+'" style="position:absolute;left:'+lft.toFixed(2)+'%;right:'+rgt.toFixed(2)+'%;top:87.4%;height:9.6%;background:'+t.ac+'1F;border-radius:.45em;display:flex;align-items:center;overflow:hidden;box-sizing:border-box;">'
    +'<div style="align-self:stretch;width:.32em;background:'+t.ac+';flex-shrink:0;"></div>'
    +'<div style="flex-shrink:0;display:flex;align-items:center;gap:.4em;font-size:.42em;font-weight:900;letter-spacing:.12em;color:'+t.ac+';text-transform:uppercase;white-space:nowrap;padding:0 1.6em 0 1.4em;">'+bulb+'Key takeaway</div>'
    +'<div style="width:1px;align-self:stretch;margin:.9% 0;background:'+t.ac+'66;flex-shrink:0;"></div>'
    +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="takeaway" style="pointer-events:auto;outline:none;cursor:text;flex:1;min-width:0;padding:0 2.2% 0 1.6%;font-size:.5em;font-weight:600;color:'+t.tc+';line-height:1.32;overflow:hidden;display:-webkit-box;-webkit-line-clamp: 2;-webkit-box-orient:vertical;">'+es(tk)+'</div><img alt="" src="data:," class="dsce-al" style="display:none;" onerror="window.dsceAlign&&window.dsceAlign(this)"/></div>';
}
/* Key takeaway inside a layout (classic Numbers slides): same look as the bottom strip, placed in the flow under the numbers */
function ceStripIn(tk,t,idx,small){
  var bulb='<svg viewBox="0 0 24 24" style="width:1.35em;height:1.35em;flex-shrink:0;" fill="'+t.ac+'"><path d="M12 2a7 7 0 0 0-4 12.7V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.3A7 7 0 0 0 12 2zm-3 18h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-1z"/></svg>';
  return '<div class="dsce-tk" style="position:relative;margin-top:'+(small?'.85em':'2em')+';min-height:'+(small?'1.9em':'2.5em')+';background:'+t.ac+'1F;border-radius:.45em;display:flex;align-items:center;overflow:hidden;box-sizing:border-box;pointer-events:auto;text-align:left;">'
    +'<div style="align-self:stretch;width:.32em;background:'+t.ac+';flex-shrink:0;"></div>'
    +'<div style="flex-shrink:0;display:flex;align-items:center;gap:.4em;font-size:.42em;font-weight:900;letter-spacing:.12em;color:'+t.ac+';text-transform:uppercase;white-space:nowrap;padding:0 1.6em 0 1.4em;">'+bulb+'Key takeaway</div>'
    +'<div style="width:1px;align-self:stretch;margin:.5em 0;background:'+t.ac+'66;flex-shrink:0;"></div>'
    +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="takeaway" style="pointer-events:auto;outline:none;cursor:text;flex:1;min-width:0;padding:.5em 2.2% .5em 1.6%;font-size:.5em;font-weight:600;color:'+t.tc+';line-height:1.32;overflow:hidden;display:-webkit-box;-webkit-line-clamp: 2;-webkit-box-orient:vertical;">'+es(tk)+'</div></div>';
}
function ceWrap(H,s,t,idx,v){
  var tk=ceTkOk(s)&&s.takeaway?String(s.takeaway).trim():'';
  if(tk&&ceType(s)==='stats'&&!ceTplOn())tk='';   /* classic Numbers slides draw their takeaway under the numbers (ceStripIn) */
  var L=ceLogo(); if(L&&L.cover===false&&s&&ceType(s)==='title')L=null;
  if(!tk&&!L)return H;
if(L&&s&&ceType(s)==='title'&&ceCoverN(L).mode==='free')return '<div style="position:absolute;inset:0;background:'+t.bg+';overflow:hidden;">'+H+ceCoverFreeHtml(L,t)+'</div>'; /* cover logo at its own position */
  var out='<div style="position:absolute;inset:0;background:'+t.bg+';overflow:hidden;">';
  if(L&&ceType(s)==='title'){if(!tk)return H;L=null;}   /* cover: the logo sits in the title block (company slot), see dsceCoverLogoHtml */
  /* a picture beside the text keeps its full height down to the bottom of the slide: the squeezed copy is hidden and the same picture is drawn again full height */
  var _sd=tk?ceSideImg(H):null;
  if(_sd&&!document.getElementById('dsce-hideimg-css')){var _st=document.createElement('style');_st.id='dsce-hideimg-css';_st.textContent='.dsce-hideimg .dsp-vp-imgzone{display:none!important;}';document.head.appendChild(_st);}
  out+=tk?'<div'+(_sd?' class="dsce-hideimg"':'')+' style="position:absolute;left:0;right:0;top:0;height:86%;font-size:.94em;">'+H+'</div>'+(_sd?imgZone(s.image_data||null,idx,_sd.css):'')+ceStrip(tk,t,idx,L,_sd):H;
  if(L)out+=ceLogoHtml(L,t);
  return out+'</div>';
}
/* ---- 9-14 points ---- */
function ceManyCols(n,img){if(img)return n<=9?3:4;return {9:3,10:5,11:4,12:4,13:5,14:5,15:5,16:4}[n]||4;}
function ceRenderMany(s,t,v,idx){
  var items=(s.content||[]).slice(0,CE_MAX),n=items.length,img=s.image_data||null;
  var _f=document.getElementById('dsp-font'),ff=(_f&&_f.value)?"'"+_f.value+"',":'';
  var H='<div style="position:absolute;inset:0;background:'+t.bg+';overflow:hidden;font-family:'+ff+'Arial,sans-serif;">';
  H+='<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+t.ac+';pointer-events:none;"></div>';
  var head='<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="title" style="pointer-events:auto;outline:none;cursor:text;font-size:1.25em;font-weight:800;color:'+t.tc+';">'+es(s.title||'')+'</div>'
    +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="subtitle" style="pointer-events:auto;outline:none;cursor:text;min-height:1em;font-size:.6em;color:'+t.ac+';font-weight:600;margin:.1em 0 .3em;">'+es(s.subtitle||'')+'</div>';
  var side=(v==='v2')?'r':(v==='v4')?'l':'';
  var list=(v==='v3'||v==='v4');
  var body;
  if(list){
    var per=Math.ceil(n/2),col=function(a,off){return '<div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:2.2%;">'+a.map(function(it,k){var p=ceSplit(it),i=off+k;
      return '<div style="flex:1;min-height:0;display:flex;align-items:center;gap:.5em;border-bottom:1px solid '+t.ac+'33;padding-bottom:.1em;pointer-events:auto;">'
        +'<div style="width:1.45em;height:1.45em;border-radius:50%;background:'+t.ac+';color:#fff;display:flex;align-items:center;justify-content:center;font-size:.62em;font-weight:800;flex-shrink:0;">'+cePad(i)+'</div>'
        +'<div style="flex:1;min-width:0;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp: 2;-webkit-box-orient:vertical;">'
        +'<span contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;font-size:.5em;font-weight:800;color:'+t.tc+';">'+es(p.h)+'</span>'
        +(p.h?'<span style="font-size:.5em;color:'+t.ac+';font-weight:800;"> · </span>':'')
        +'<span contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;font-size:.44em;color:'+t.bc+';">'+es(p.d)+'</span></div></div>';}).join('')+(a.length<per?'<div style="flex:'+(per-a.length)+';"></div>':'')+'</div>';};
    body='<div style="display:flex;gap:4%;height:100%;">'+col(items.slice(0,per),0)+col(items.slice(per),per)+'</div>';
  }else{
    var cols=ceManyCols(n,!!side),rows=Math.ceil(n/cols),clamp=rows>=3?2:3,rowsH=[];
    for(var r=0;r<rows;r++){
      var a=items.slice(r*cols,r*cols+cols),cells=a.map(function(it,k){var p=ceSplit(it),i=r*cols+k;
        return '<div style="flex:0 0 calc((100% - '+(cols-1)+' * 1.6%) / '+cols+');min-width:0;min-height:0;overflow:hidden;background:'+t.ac+'14;border-radius:8px;padding:2.2% 2.4%;box-sizing:border-box;display:flex;flex-direction:column;pointer-events:auto;">'
          +'<div style="display:flex;align-items:center;gap:.4em;margin-bottom:.25em;flex-shrink:0;">'
          +'<div style="width:1.35em;height:1.35em;border-radius:50%;background:'+t.ac+';color:#fff;display:flex;align-items:center;justify-content:center;font-size:.55em;font-weight:800;flex-shrink:0;">'+cePad(i)+'</div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-h" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-width:0;font-size:.5em;font-weight:800;color:'+t.tc+';line-height:1.18;overflow:hidden;display:-webkit-box;-webkit-line-clamp: 2;-webkit-box-orient:vertical;">'+es(p.h)+'</div></div>'
          +'<div contenteditable="true" data-ed-slide="'+idx+'" data-ed-field="content-d" data-ed-idx="'+i+'" style="outline:none;cursor:text;min-height:0;font-size:.4em;color:'+t.bc+';line-height:1.32;overflow:hidden;display:-webkit-box;-webkit-line-clamp: '+clamp+';-webkit-box-orient:vertical;">'+es(p.d)+'</div></div>';}).join('');
      rowsH.push('<div style="display:flex;justify-content:center;gap:1.6%;flex:1;min-height:0;">'+cells+'</div>');
    }
    body='<div style="display:flex;flex-direction:column;gap:2.4%;height:100%;">'+rowsH.join('')+'</div>';
  }
  var box=side==='r'?'left:0;right:29%;padding:3% 2% 3% 4%;':side==='l'?'left:29%;right:0;padding:3% 4% 3% 2%;':'inset-inline:0;padding:3% 4.5%;';
  H+='<div style="position:absolute;top:3px;bottom:0;'+box+'display:flex;flex-direction:column;overflow:hidden;">'+head+'<div style="flex:1;min-height:0;overflow:hidden;margin-top:1.5%;">'+body+'</div></div>';
  if(side==='r')H+=imgZone(img,idx,'top:0;bottom:0;right:0;width:28%;');
  if(side==='l')H+=imgZone(img,idx,'top:0;bottom:0;left:0;width:28%;');
  return H+'</div>';
}
V.many_point=[
  {id:'v1',lbl:'Card grid (9-16 points)',img:''},
  {id:'v2',lbl:'Cards + image right',img:''},
  {id:'v3',lbl:'Two-column numbered list',img:''},
  {id:'v4',lbl:'Image left + numbered list',img:''}
];

/* ---- numbers <-> chart (Change layout panel offers both) ---- */
function ceIsChartSlide(s){return !!s&&s.type==='chart'&&!!s.chart&&!s.dg;}
function ceStatParse(x){
  var p=String(x==null?'':x).split(':'),raw=(p[0]||'').trim(),lab=p.slice(1).join(':').split('|')[0].trim();
  var m=raw.match(/^([$€£₹¥]?)\s*(-?\d[\d,]*(?:\.\d+)?)\s*(%|[KMBkmb](?![a-z])|x\b|X\b)?/);if(!m)return null;
  var v=parseFloat(m[2].replace(/,/g,''));if(!isFinite(v))return null;
  var suf=(m[3]||'').toUpperCase(),mul=suf==='K'?1e3:suf==='M'?1e6:suf==='B'?1e9:1;
  var unit=m[1]||(suf==='%'?'%':suf==='X'?'x':'');
  return {v:v*mul,u:unit,l:lab||raw};
}
function ceStatsToChart(s){
  if(!window.DS_CHART)return null;
  var r=(s.content||[]).slice(0,6).map(ceStatParse);
  if(r.length<2||r.some(function(x){return !x;}))return null;
  var u=r[0].u;if(r.some(function(x){return x.u!==u;}))return null;
  var all100=r.every(function(x){return x.v>=0&&x.v<=100;});
  return {style:(u==='%'&&all100)?(r.length<=4?'gauge':'progress'):'column',unit:u,labels:r.map(function(x){return x.l.split(/\s+/).slice(0,5).join(' ');}),values:r.map(function(x){return x.v;}),angle:0};
}
function ceChartToStats(s){
  var D=window.DS_CHART;if(!D||!s.chart)return [];
  var d=D.norm(s.chart),c=s.content||[];return d.rows.slice(0,3).map(function(r,i){var lab=r.l,x=String(c[i]||''),k=x.indexOf(':');if(k>0&&k<90&&x.slice(0,k).toLowerCase().indexOf(r.l.toLowerCase().slice(0,8))===0)lab=x.slice(0,k).trim();return D.fmt(r.v,d.unit)+': '+lab;});
}
function ceStatsInsights(s){return (s.content||[]).slice(0,4).map(function(x){var p=ceStatParse(x);return p?p.l+': '+String(x).split(':')[0].trim():String(x);});}
function ceAltSlide(s,v){
  var o={};for(var k in s)o[k]=s[k];
  if(v.indexOf('nm_')===0&&ceIsChartSlide(s)){o.type='stats';o.content=s.__statsContent||ceChartToStats(s);return o;}
  if(v.indexOf('ch_')===0&&ceType(s)==='stats'){var ch=s.__chart||ceStatsToChart(s);if(!ch)return null;o.type='chart';o.chart=ch;o.content=s.__chartContent||ceStatsInsights(s);return o;}
  return null;
}
function ceExtraVariants(s){
  if(!s||ceTplOn()||!window.DS_CHART)return null;
  if(ceIsChartSlide(s))return DS_CHART.variants().concat(V.stats.map(function(v){return {id:'nm_'+v.id,lbl:'Numbers · '+v.lbl,img:v.img};}));
  if(ceType(s)==='stats'&&(s.__chart||ceStatsToChart(s)))return V.stats.concat(DS_CHART.variants().map(function(v){return {id:'ch_'+v.id,lbl:'Chart · '+v.lbl};}));
  return null;
}
/* picking a layout of the other kind turns the slide into it (and back again) */
function ceSwitchKind(idx,vid){
  if(typeof vid!=='string'||!S.data||!S.data.slides||!S.data.slides[idx])return vid;
  var s=S.data.slides[idx],pre=vid.slice(0,3),base=vid.slice(3);
  if(pre!=='nm_'&&pre!=='ch_')return vid;
  var alt=ceAltSlide(s,vid);if(!alt)return 'v1';
  ceDecks().forEach(function(d){var x=d.slides&&d.slides[idx];if(!x)return;
    if(pre==='nm_'){x.__chart=x.chart;x.__chartContent=(x.content||[]).slice();x.type='stats';x.content=alt.content.slice();}
    else{x.__statsContent=(x.content||[]).slice();x.type='chart';x.chart=JSON.parse(JSON.stringify(alt.chart));x.content=alt.content.slice();}
  });
  return base;
}
window.dsceSwitchKind=ceSwitchKind;
window.dsceRenderSlide=function(i,v){return renderSlide(S.data.slides[i],gt(),v||S.choices['slide_'+i]||'v1',i);};window.dsceFontRatio=ceFontRatio;   /* DS Deck Style */
/* the renderer every Classic view uses */
function renderSlide(s,t,v,slideIdx){
  if(ceTplOn())return renderSlideBase(s,t,v,slideIdx);
  /* DS Deck Style: deck-wide colours go into the theme, per-element font weight / size / colour into the markup */
  if(window.dsDeckStyleT)t=window.dsDeckStyleT(t,s,slideIdx);
  var H0=renderSlide0(s,t,v,slideIdx);
  return window.dsDeckStyleH?window.dsDeckStyleH(H0,s,slideIdx):H0;
}
function renderSlide0(s,t,v,slideIdx){
  var idx=(typeof slideIdx==='number'?slideIdx:S.idx);
  if(s&&typeof v==='string'&&(v.indexOf('nm_')===0||v.indexOf('ch_')===0)){var _alt=ceAltSlide(s,v);if(_alt){s=_alt;v=v.slice(3);}}
  if(s&&s.type==='chart')return ceWrap(renderSlideBase(s,t,v,slideIdx),s,t,idx,v);
  var H=dsceMany(s)?ceRenderMany(s,t,v,idx):renderSlideBase(s,t,v,slideIdx);
  return ceWrap(H,s,t,idx,v);
}
function ceStatsMany(s){return !!s&&String(s.type)==='stats';} /* every classic Numbers slide uses this renderer */
function ceNeedsOwn(s){return !!s&&!ceTplOn()&&((window.dsDeckStyleOn&&window.dsDeckStyleOn(s))||dsceMany(s)||ceStatsMany(s)||(ceTkOk(s)&&s.takeaway)||!!ceLogo());}
/* inline editing of the takeaway strip */
document.addEventListener('input',function(e){
  var el=e.target;if(!el||!el.getAttribute||el.getAttribute('data-ed-field')!=='takeaway')return;
  var i=parseInt(el.getAttribute('data-ed-slide'),10),txt=(el.innerText||'').replace(/\s+/g,' ').trim();
  ceDecks().forEach(function(d){if(d.slides&&d.slides[i])d.slides[i].takeaway=txt;});
},true);

/* ---------------- shared styles ---------------- */
function ceCss(){
  if(document.getElementById('dsce-css'))return;
  var c=document.createElement('style');c.id='dsce-css';
  c.textContent=''
  +'#dsce-bar{position:fixed;display:flex;gap:6px;}'
  +'#dsce-bar button{all:unset;box-sizing:border-box;background:#1d4ed8;color:#fff;border-radius:7px;padding:6px 12px;font:700 11.5px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;letter-spacing:.04em;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.35);}'
  +'#dsce-bar button.dsce-alt{background:#2a2f3a;}#dsce-bar button:hover{filter:brightness(1.15);}'
  +'.dsce-ov{position:fixed;inset:0;z-index:2147483000;background:rgba(0,0,0,.72);display:flex;align-items:center;justify-content:center;padding:22px;}'
  +'.dsce-ov,.dsce-ov *{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif!important;text-transform:none!important;letter-spacing:normal!important;box-sizing:border-box;}'
  +'.dsce-m{background:#161a22;border:1px solid #2a2f3a;border-radius:14px;width:min(1180px,100%);max-height:92vh;display:flex;flex-direction:column;overflow:hidden;color:#e5e7eb;}'
  +'.dsce-hd{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #232834;}'
  +'.dsce-hd h3{margin:0!important;font-size:16px!important;font-weight:800;color:#f9fafb!important;}'
  +'.dsce-ov button{all:unset;box-sizing:border-box;cursor:pointer;}'
  +'.dsce-ov .dsce-x{color:#9ca3af;font-size:18px!important;padding:4px 8px;}'
  +'.dsce-body{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);gap:20px;padding:18px 20px;overflow:auto;min-height:0;}'
  +'.dsce-body>div:first-child{position:sticky;top:0;align-self:start;}'
  +'@media(max-width:880px){.dsce-body{grid-template-columns:1fr;}.dsce-body>div:first-child{position:static;}}'
  +'.dsce-prev{position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;border-radius:8px;border:1px solid #2a2f3a;}'
  +'.dsce-hint{color:#8b93a7!important;font-size:12px!important;line-height:1.5!important;margin:10px 0 0!important;}'
  +'.dsce-lbl{display:flex!important;align-items:center;font-size:11px!important;font-weight:700!important;letter-spacing:.06em!important;text-transform:uppercase!important;color:#9ca3af!important;margin:14px 0 6px!important;}'
  +'.dsce-in,.dsce-ta{width:100%!important;background:#0d1117!important;border:1px solid #2a2f3a!important;border-radius:7px!important;color:#f3f4f6!important;font-size:13px!important;line-height:1.4!important;padding:7px 9px!important;outline:none!important;margin:0!important;height:auto!important;min-height:0!important;box-shadow:none!important;}'
  +'.dsce-in:focus,.dsce-ta:focus{border-color:#3b82f6!important;}.dsce-ta{resize:vertical;}'
  +'.dsce-pt{display:grid;grid-template-columns:24px minmax(0,1fr) minmax(0,1.7fr) 84px;gap:6px;align-items:start;margin-bottom:6px;}'
  +'.dsce-pt .dsce-n{width:22px;height:22px;border-radius:50%;background:#1d4ed8;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-top:6px;}'
  +'.dsce-pt>div:last-child{display:flex;gap:2px;}'
  +'.dsce-ov .dsce-ic{width:26px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:#232834;border:1px solid #2f3542;color:#cbd5e1;border-radius:6px;font-size:13px!important;}'
  +'.dsce-ov .dsce-ic:hover{background:#2d3442;}.dsce-ov .dsce-ic[disabled]{opacity:.3;cursor:default;}'
  +'.dsce-ov .dsce-b{display:inline-flex;align-items:center;gap:6px;background:#232834;border:1px solid #2f3542;color:#e5e7eb;border-radius:8px;padding:9px 16px;font-weight:700;font-size:13px!important;}'
  +'.dsce-ov .dsce-p{background:#2563eb;border-color:#2563eb;color:#fff;}.dsce-ov .dsce-b[disabled]{opacity:.45;cursor:default;}'
  +'.dsce-ft{display:flex;align-items:center;gap:10px;padding:14px 20px;border-top:1px solid #232834;}'
  +'.dsce-cnt{font-size:12px!important;color:#8b93a7!important;margin-left:8px;text-transform:none!important;letter-spacing:normal!important;font-weight:600!important;}'
  +'.dsce-seg{display:flex;gap:6px;flex-wrap:wrap;}.dsce-ov .dsce-seg button{background:#0d1117;border:1px solid #2a2f3a;color:#cbd5e1;border-radius:7px;padding:7px 12px;font-size:12px!important;font-weight:700;}'
  +'.dsce-ov .dsce-seg button.on{border-color:#3b82f6;background:#13254a;color:#fff;}'
  +'.dsce-drop{display:block!important;border:2px dashed #34405a;border-radius:10px;padding:18px;text-align:center;color:#9ca3af;font-size:13px!important;cursor:pointer;}'
  +'.dsce-drop:hover{border-color:#3b82f6;color:#e5e7eb;}'
  +'.dsce-rg{margin:4px 0 10px;}.dsce-rgl{display:flex;justify-content:space-between;font-size:13px!important;color:#e5e7eb;margin-bottom:4px;}.dsce-rgl span{color:#8b93a7;font-size:12px!important;}'
  +'.dsce-ov input[type=range]{-webkit-appearance:none;appearance:none;width:100%;height:4px;border-radius:4px;background:#2f3542;outline:none;margin:6px 0;padding:0;border:none;}'
  +'.dsce-ov input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:#fff;border:3px solid #2563eb;cursor:pointer;}'
  +'.dsce-ov input[type=range]::-moz-range-thumb{width:14px;height:14px;border-radius:50%;background:#fff;border:3px solid #2563eb;cursor:pointer;}'
  +'.dsce-rgs{display:flex;justify-content:space-between;font-size:11px!important;color:#6b7280;}'
  +'.dsce-chk{display:flex;align-items:center;gap:8px;font-size:13px!important;color:#cbd5e1;margin-top:12px;}';
  document.head.appendChild(c);
}
function ceToast(m,k){if(window.dspToast)dspToast(m,k||'success');}
function ceFontRatio(){var m=document.getElementById('dsp-ppt-main-slide');if(m&&m.clientWidth){var f=parseFloat(getComputedStyle(m).fontSize);if(f)return f/m.clientWidth;}return 0.0272;}
var _ceSv=0;
/* save right away; deck-level changes (the logo) are saved directly because the
   draft "dirty" check only looks at slide text */
function ceSave(direct){clearTimeout(_ceSv);_ceSv=setTimeout(function(){
  if(!window.G||!G.pptData)return;
  if(direct&&G.pptId){
    var u=(window.PURL?String(window.PURL).replace('/generate-ppt','/save-ppt'):'/wp-json/ds/v1/save-ppt'),dz=document.getElementById('dsp-design');
    fetch(u,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':window.NONCE||window.DSP_NONCE||''},
      body:JSON.stringify({ppt_id:G.pptId,title:G.pptTitle||'Presentation',ppt_data:G.pptData,design:(dz&&dz.value)||'navy-pro'})})
    .then(function(r){return r.json();}).then(function(d){if(d&&d.success)ceToast('Saved');}).catch(function(){});
    return;
  }
  if(typeof window.dspSaveDraft==='function')window.dspSaveDraft();
},900);}
function ceRefresh(i){if(ceTplOn()&&window.DSTE&&DSTE.refreshEditor){try{DSTE.refreshEditor();}catch(e){}}
  try{sessionStorage.setItem('dspVPC',JSON.stringify(S.choices));}catch(e){}
  if(typeof vpPreloadAllSlides==='function')vpPreloadAllSlides();
  if(S.open&&typeof vpRender==='function')vpRender(typeof i==='number'?i:(S.idx>=0?S.idx:0));
}

/* ---------------- EDIT SLIDE ---------------- */
var CED=null;
function ceTkKind(k){return k==='points'||k==='stats'||k==='chartk'||(k==='timeline'&&ceTplOn());}
function ceKind(s){if(ceTplOn()&&window.DSTE&&DSTE.kindOf){var tk=DSTE.kindOf(s);if(/^p\d/.test(tk)||tk==='text'||tk==='agenda')return 'points';if(tk==='stats'||tk==='timeline')return tk;if(tk==='quote')return 'quote';if(tk==='cover'||tk==='closing')return 'title';if(tk==='chart')return s.dg?'none':'chartk';return 'other';}var t=ceType(s);if(t==='chart')return s.dg?'none':'chartk';if(CE_PT.indexOf(t)>=0)return 'points';if(t==='stats'||t==='timeline')return t;if(t==='quote')return 'quote';if(t==='title')return 'title';return 'other';}
function ceRetype(old,n){
  if(CE_PT.indexOf(old)<0)return old;
  if(n<=1)return 'bullet';if(n===2)return 'two_column';if(n===3)return 'three_column';if(n===4)return 'feature_grid';
  if(n<=8)return ['five_point','six_point','seven_point','eight_point'][n-5];
  return 'bullet';
}
function ceVariantsFor(s){if(ceTplOn()){try{var _vs=window.DS_TPL_ACTIVE().variants(s.type,s);if(_vs&&_vs.length)return _vs;}catch(e){}return [{id:'v1'}];}var t=ceType(s),n=(s.content||[]).length;if(dsceMany(s))return V.many_point;var vt=(t==='bullet'&&n>=5&&n<=8)?['five_point','six_point','seven_point','eight_point'][n-5]:t;return V[vt]||V[t]||V.bullet;}
window.dsceEditSlide=function(idx){
  if(!S.data||!S.data.slides)return;
  if(typeof idx!=='number'||idx<0)idx=S.idx>=0?S.idx:0;
  var s=S.data.slides[idx];if(!s)return;
  
  if(s.dg){ceToast('Use “Edit diagram” for this slide.','info');return;}
  var k=ceKind(s);
  CED={idx:idx,kind:k,title:s.title||'',subtitle:s.subtitle||'',takeaway:(ceTplOn()?(s.conclusion||s.takeaway):s.takeaway)||'',
    rows:(s.content||[]).map(function(c,i){var p=ceSplit(c);return {h:p.h,d:p.d,raw:String(c),icon:(s.icons&&s.icons[i])||''};})};
  if(k==='quote')CED.author=(s.content&&s.content[0])||'';
  ceCss();
  var ov=document.createElement('div');ov.className='dsce-ov';ov.id='dsce-ed';
  ov.innerHTML='<div class="dsce-m"><div class="dsce-hd"><h3>&#9998; Edit slide '+(idx+1)+'</h3><button type="button" class="dsce-x" id="dsce-x">&#10005;</button></div>'
    +'<div class="dsce-body"><div><div class="dsce-prev" id="dsce-prev"></div><p class="dsce-hint">'+(k==='points'?(ceTplOn()?'Add or remove points (up to 16) and the layout re-flows automatically. 9 or more points switch to the 9–16 point layouts.':'Add or remove points (up to '+CE_MAX+') and the layout re-flows automatically. 9 or more points switch to the 9–16 point layouts.'):'Changes show here as you type.')+'</p></div><div id="dsce-form"></div></div>'
    +'<div class="dsce-ft"><button type="button" class="dsce-b" id="dsce-cancel">Cancel</button><span style="flex:1"></span><button type="button" class="dsce-b dsce-p" id="dsce-apply">&#10003; Apply changes</button></div></div>';
  document.body.appendChild(ov);
  ov.addEventListener('mousedown',function(e){if(e.target===ov)ov.remove();});
  document.getElementById('dsce-x').onclick=document.getElementById('dsce-cancel').onclick=function(){ov.remove();};
  document.getElementById('dsce-apply').onclick=ceApply;
  ceForm();cePreview();
};
function ceDraft(){
  var s=S.data.slides[CED.idx],o={};for(var k in s)o[k]=s[k];
  o.title=CED.title;o.subtitle=CED.subtitle;
  if(CED.kind==='quote'){o.content=[CED.author];}
  else{o.content=CED.rows.map(function(r){return CED.kind==='title'||CED.kind==='other'?r.raw:(r.h?r.h+': '+r.d:r.d);});o.icons=CED.rows.map(function(r){return r.icon||'';});}
  if(CED.kind==='points'){o.type=ceRetype(ceType(s),o.content.length);}
  if(ceTkKind(CED.kind)){o.takeaway=CED.takeaway.trim();if(ceTplOn())o.conclusion=o.takeaway;}
  return o;
}
var _cePv=0;
function cePreview(){clearTimeout(_cePv);_cePv=setTimeout(function(){
  var el=document.getElementById('dsce-prev');if(!el||!CED)return;
  var d=ceDraft(),vs=ceVariantsFor(d),cur=S.choices['slide_'+CED.idx]||'v1';
  if(!vs.some(function(x){return x.id===cur;}))cur='v1';
  el.style.fontSize=(el.clientWidth*ceFontRatio()).toFixed(2)+'px';
  el.innerHTML=renderSlide(d,gt(),cur,CED.idx).replace(/contenteditable="true"/g,'contenteditable="false"');
},90);}
function ceEsc(x){return String(x==null?'':x).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function ceForm(){
  var f=document.getElementById('dsce-form');if(!f||!CED)return;
  var k=CED.kind,h='';
  h+='<label class="dsce-lbl">'+(k==='quote'?'Quote':'Title')+'</label>'+(k==='quote'?'<textarea class="dsce-ta" data-f="title" rows="3">'+ceEsc(CED.title)+'</textarea>':'<input class="dsce-in" data-f="title" value="'+ceEsc(CED.title)+'"/>');
  if(k==='quote'){h+='<label class="dsce-lbl">Author</label><input class="dsce-in" data-f="author" value="'+ceEsc(CED.author)+'"/>';}
  else h+='<label class="dsce-lbl">Subtitle</label><input class="dsce-in" data-f="subtitle" value="'+ceEsc(CED.subtitle)+'"/>';
  if(k==='points'||k==='stats'||k==='timeline'||k==='chartk'){
    var cap=ceCap(k),lh=k==='stats'?'Number':k==='timeline'?'Date / phase':'Heading',ld=k==='stats'?'Label':'Explanation';
    h+='<label class="dsce-lbl">'+(k==='points'?'Points':k==='stats'?'Numbers':k==='chartk'?'Insights beside the chart':'Milestones')+'<span class="dsce-cnt">'+CED.rows.length+' of '+cap+'</span></label>';
    CED.rows.forEach(function(r,i){
      h+='<div class="dsce-pt"><div class="dsce-n">'+(i+1)+'</div><input class="dsce-in" data-r="'+i+'" data-k="h" placeholder="'+lh+'" value="'+ceEsc(r.h)+'"/>'
        +'<textarea class="dsce-ta" data-r="'+i+'" data-k="d" rows="2" placeholder="'+ld+'">'+ceEsc(r.d)+'</textarea>'
        +'<div style="white-space:nowrap"><button type="button" class="dsce-ic" data-up="'+i+'" title="Move up"'+(i?'':' disabled')+'>&#8593;</button><button type="button" class="dsce-ic" data-dn="'+i+'" title="Move down"'+(i<CED.rows.length-1?'':' disabled')+'>&#8595;</button><button type="button" class="dsce-ic" data-del="'+i+'" title="Delete point"'+(CED.rows.length>1?'':' disabled')+'>&#128465;</button></div></div>';
    });
    h+='<button type="button" class="dsce-b" id="dsce-add"'+(CED.rows.length>=cap?' disabled':'')+'>+ Add '+(k==='points'?'point':k==='stats'?'number':k==='chartk'?'insight':'milestone')+'</button>';
  }else if(k==='title'&&CED.rows.length){
    h+='<label class="dsce-lbl">Details (Company: …, Presenter: …, Date: …)</label>';
    CED.rows.forEach(function(r,i){h+='<input class="dsce-in" style="margin-bottom:6px" data-raw="'+i+'" value="'+ceEsc(r.raw)+'"/>';});
  }
  if(ceTkKind(k)){
    h+='<label class="dsce-lbl">Key takeaway</label><textarea class="dsce-ta" data-f="takeaway" rows="2" placeholder="One sentence the audience should remember (leave empty to hide the strip)">'+ceEsc(CED.takeaway)+'</textarea>';
  }
  f.innerHTML=h;
  Array.prototype.forEach.call(f.querySelectorAll('[data-f]'),function(el){el.oninput=function(){CED[el.getAttribute('data-f')]=el.value;cePreview();};});
  Array.prototype.forEach.call(f.querySelectorAll('[data-r]'),function(el){el.oninput=function(){var r=CED.rows[+el.getAttribute('data-r')];r[el.getAttribute('data-k')]=el.value.replace(/\n+/g,' ');cePreview();};});
  Array.prototype.forEach.call(f.querySelectorAll('[data-raw]'),function(el){el.oninput=function(){CED.rows[+el.getAttribute('data-raw')].raw=el.value;cePreview();};});
  Array.prototype.forEach.call(f.querySelectorAll('[data-del]'),function(el){el.onclick=function(){CED.rows.splice(+el.getAttribute('data-del'),1);ceForm();cePreview();};});
  Array.prototype.forEach.call(f.querySelectorAll('[data-up]'),function(el){el.onclick=function(){var i=+el.getAttribute('data-up');if(i<1)return;var a=CED.rows,x=a[i];a[i]=a[i-1];a[i-1]=x;ceForm();cePreview();};});
  Array.prototype.forEach.call(f.querySelectorAll('[data-dn]'),function(el){el.onclick=function(){var i=+el.getAttribute('data-dn'),a=CED.rows;if(i>=a.length-1)return;var x=a[i];a[i]=a[i+1];a[i+1]=x;ceForm();cePreview();};});
  var ad=document.getElementById('dsce-add');if(ad)ad.onclick=function(){
    var cap=ceCap(CED.kind);if(CED.rows.length>=cap)return;
    CED.rows.push({h:CED.kind==='points'?'New point':'',d:'',raw:'',icon:''});ceForm();cePreview();
    setTimeout(function(){var ins=f.querySelectorAll('input[data-r="'+(CED.rows.length-1)+'"]');if(ins[0]){ins[0].focus();ins[0].select();}},30);
  };
}
function ceApply(){
  if(!CED)return;var i=CED.idx,d=ceDraft();
  ceDecks().forEach(function(deck){var s=deck.slides&&deck.slides[i];if(!s)return;
    s.title=d.title;s.subtitle=d.subtitle;s.content=d.content.slice();
    if(d.icons)s.icons=d.icons.slice();
    if(CED.kind==='points')s.type=d.type;
    if(ceTkKind(CED.kind)){if(d.takeaway)s.takeaway=d.takeaway;else delete s.takeaway;if(ceTplOn()){if(d.takeaway)s.conclusion=d.takeaway;else delete s.conclusion;}}
  });
  var vs=ceVariantsFor(S.data.slides[i]),cur=S.choices['slide_'+i]||'v1';
  if(!vs.some(function(x){return x.id===cur;}))S.choices['slide_'+i]='v1';
  var ov=document.getElementById('dsce-ed');if(ov)ov.remove();
  CED=null;ceRefresh(i);ceToast('Slide updated');ceSave(true);
}

/* ---------------- LOGO ---------------- */
var CEL=null;
window.dsceLogoPanel=function(){
  if(!S.data||!S.data.slides)return;
  
  var L=ceLogo(),N=L?ceLogoN(L):{x:100,y:0,sz:22},C=L?ceCoverN(L):{mode:'auto',x:50,y:0,sz:N.sz};CEL={src2:(L&&L.src2)?L.src2:'',w2:L?(L.w2||0):0,h2:L?(L.h2||0):0,glow:(L&&typeof L.glow==='number')?L.glow:50,sglow:(L&&typeof L.sglow==='number')?L.sglow:50,sbadge:L?L.sbadge===true:false,sbgv:L&&isFinite(parseFloat(L.sbgv))?+L.sbgv:56,cmode:C.mode,cx:C.x,cy:C.y,csz:C.sz,src:L?L.src:'',x:N.x,y:N.y,sz:N.sz,w:L?L.w:0,h:L?L.h:0,cover:L?L.cover!==false:true,badge:L?L.badge!==false:true,bgv:L&&isFinite(parseFloat(L.bgv))?+L.bgv:56};
  if(ceTplOn())CEL.cmode='free'; /* template covers: logo placed by position (no title block) */
  ceCss();
  var ov=document.createElement('div');ov.className='dsce-ov';ov.id='dsce-lg';
  ov.innerHTML='<div class="dsce-m" style="width:min(900px,100%)"><div class="dsce-hd"><h3>&#127991; Company logo</h3><button type="button" class="dsce-x" id="dsce-lx">&#10005;</button></div>'
    +'<div class="dsce-body"><div><div class="dsce-prev" id="dsce-lprev"></div><div class="dsce-seg" id="dsce-lpv" style="margin-top:10px"><button type="button" data-pv="content" class="on">Preview: slide</button><button type="button" data-pv="cover">Preview: cover</button></div><p class="dsce-hint">The cover slide and the other slides each have their own position and size. Both are used in the downloaded PowerPoint.</p></div><div id="dsce-lform"></div></div>'
    +'<div class="dsce-ft"><button type="button" class="dsce-b" id="dsce-lrm">Remove logo</button><span style="flex:1"></span><button type="button" class="dsce-b" id="dsce-lcancel">Cancel</button><button type="button" class="dsce-b dsce-p" id="dsce-lok">&#10003; Apply to all slides</button></div></div>';
  document.body.appendChild(ov);
  ov.addEventListener('mousedown',function(e){if(e.target===ov)ov.remove();});
  document.getElementById('dsce-lx').onclick=document.getElementById('dsce-lcancel').onclick=function(){ov.remove();};
  document.getElementById('dsce-lrm').onclick=function(){ceDecks().forEach(function(d){delete d.logo;});if(window.G&&G.pptData)delete G.pptData.logo;if(S.data)delete S.data.logo;ov.remove();ceRefresh();ceToast('Logo removed');ceSave(true);};
  document.getElementById('dsce-lok').onclick=function(){
    if(!CEL.src){ceToast('Upload a logo first.','error');return;}
    ceDecks().forEach(function(d){d.logo={src2:CEL.src2||'',w2:CEL.w2||0,h2:CEL.h2||0,glow:CEL.glow,sglow:CEL.sglow,sbadge:CEL.sbadge,sbgv:CEL.sbgv,cmode:CEL.cmode,cx:CEL.cx,cy:CEL.cy,csz:CEL.csz,src:CEL.src,x:CEL.x,y:CEL.y,sz:CEL.sz,w:CEL.w,h:CEL.h,cover:CEL.cover,badge:CEL.badge,bgv:CEL.bgv};});
    ov.remove();ceRefresh();ceToast('Logo added to every slide');ceSave(true);
  };
  Array.prototype.forEach.call(ov.querySelectorAll('[data-pv]'),function(b){b.onclick=function(){cePv(b.getAttribute('data-pv'));};});
  ceLForm();ceLPrev();
};
function cePv(k){CEL._pv=k;Array.prototype.forEach.call(document.querySelectorAll('#dsce-lpv [data-pv]'),function(b){b.classList.toggle('on',b.getAttribute('data-pv')===k);});ceLPrev();}
function ceLPrev(){
  var el=document.getElementById('dsce-lprev');if(!el||!S.data)return;
  var i=S.idx>=0?S.idx:0,sl=S.data.slides;
  if(CEL._pv==='cover'){for(var q=0;q<sl.length;q++)if(ceType(sl[q])==='title'){i=q;break;}}
  else if(ceType(sl[i])==='title'&&sl.length>1)i=i+1<sl.length?i+1:0;
  var s=S.data.slides[i],keep=ceDecks().map(function(d){return d.logo;});
  ceDecks().forEach(function(d){if(CEL.src)d.logo=CEL;else delete d.logo;});
  el.style.fontSize=(el.clientWidth*ceFontRatio()).toFixed(2)+'px';
  try{el.innerHTML=renderSlide(s,gt(),S.choices['slide_'+i]||'v1',i).replace(/contenteditable="true"/g,'contenteditable="false"');}
  finally{ceDecks().forEach(function(d,j){if(keep[j])d.logo=keep[j];else delete d.logo;});}
}
function ceLForm(){
  var f=document.getElementById('dsce-lform');if(!f)return;
  var QP=[['Top left',0,0],['Top centre',50,0],['Top right',100,0],['Bottom left',0,100],['Bottom centre',50,100],['Bottom right',100,100],['Centre',50,50]];
function sec(t,first){return '<div style="'+(first?'':'margin-top:22px;padding-top:16px;border-top:1px solid rgba(255,255,255,.12);')+'font-size:15px;font-weight:800;color:#fff;letter-spacing:.01em;">'+t+'</div>';}
function rng(k,lbl,l0,l1){return '<div class="dsce-rg"><div class="dsce-rgl">'+lbl+'<span>'+Math.round(CEL[k])+'%</span></div><input type="range" min="0" max="100" step="1" data-rg="'+k+'" value="'+CEL[k]+'"/><div class="dsce-rgs"><span>'+l0+'</span><span>'+l1+'</span></div></div>';}
  if(typeof CEL.glow!=='number')CEL.glow=50;if(typeof CEL.sglow!=='number')CEL.sglow=50;   /* DS logo options: glow strength, 50 = the look it always had */
  f.innerHTML='<label class="dsce-lbl">Logo image (title slide and, unless you add another below, every slide)</label><label class="dsce-drop" id="dsce-ldrop">'+(CEL.src?'<img src="'+CEL.src+'" style="max-width:70%;max-height:70px;display:block;margin:0 auto 8px;"/>Click to replace':'Click to upload PNG, JPG, SVG or WebP')+'<input type="file" id="dsce-lfile" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" style="display:none"/></label>'
    +sec('Title slide (cover)',true)
+'<label class="dsce-chk" style="margin-top:0"><input type="checkbox" id="dsce-lcover"'+(CEL.cover?' checked':'')+'/> Show the logo on the cover slide</label>'
+(CEL.cover?(
'<label class="dsce-lbl">Position</label>'+rng('cy','Top to bottom','Top','Bottom')+rng('cx','Left to right','Left','Right')
+'<label class="dsce-lbl">Size</label>'+rng('csz','Logo size','Small',CEL.cmode==='free'?'Whole slide':'Large')
+'<div class="dsce-seg" style="margin-top:8px">'+QP.map(function(p){return '<button type="button" data-cqx="'+p[1]+'" data-cqy="'+p[2]+'"'+(CEL.cmode==='free'&&CEL.cx===p[1]&&CEL.cy===p[2]?' class="on"':'')+'>'+p[0]+'</button>';}).join('')+(ceTplOn()?'':'<button type="button" data-cauto="1"'+(CEL.cmode!=='free'?' class="on"':'')+'>Above the title</button>')+'</div>'
+'<p class="dsce-hint" id="dsce-chint" style="margin-top:6px!important;'+(CEL.cmode==='free'?'display:none;':'')+'">Now: in the title block, above the title. Move a position slider or pick a position to place it anywhere on the cover.</p>'
+'<label class="dsce-lbl">Cover badge (box behind the logo)</label>'
+'<label class="dsce-chk" style="margin-top:0"><input type="checkbox" id="dsce-lbadge"'+(CEL.badge?' checked':'')+'/> Show the badge</label>'
+(CEL.badge?rng('bgv','Badge colour','Darker','Lighter')+rng('glow','Glow around the badge','None','Strong'):'')
+'<p class="dsce-hint" style="margin-top:4px!important">Use darker for light logos and lighter for dark logos. The middle is see-through.</p>'
):'')
+sec('Other slides',false)
+'<label class="dsce-lbl">Logo for these slides (optional)</label><label class="dsce-drop" id="dsce-ldrop2">'+(CEL.src2?'<img src="'+CEL.src2+'" style="max-width:70%;max-height:56px;display:block;margin:0 auto 8px;"/>Click to replace':'Upload a short logo or icon for the slides after the cover. Without one, the main logo is used.')+'<input type="file" id="dsce-lfile2" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" style="display:none"/></label>'+(CEL.src2?'<button type="button" class="dsce-b" id="dsce-lrm2" style="margin:6px 0 0">Use the main logo instead</button>':'')
+'<label class="dsce-lbl">Position</label>'+rng('y','Top to bottom','Top','Bottom')+rng('x','Left to right','Left','Right')
+'<label class="dsce-lbl">Size</label>'+rng('sz','Logo size','Small','Whole slide')
+'<div class="dsce-seg" style="margin-top:8px">'+QP.map(function(p){return '<button type="button" data-qx="'+p[1]+'" data-qy="'+p[2]+'"'+(CEL.x===p[1]&&CEL.y===p[2]?' class="on"':'')+'>'+p[0]+'</button>';}).join('')+'</div>'
+'<label class="dsce-lbl">Slide badge (box behind the logo)</label>'
+'<label class="dsce-chk" style="margin-top:0"><input type="checkbox" id="dsce-lsbadge"'+(CEL.sbadge?' checked':'')+'/> Show the badge</label>'
+(CEL.sbadge?rng('sbgv','Badge colour','Darker','Lighter')+rng('sglow','Glow around the badge','None','Strong')+'<p class="dsce-hint" style="margin-top:4px!important">Use darker for light logos and lighter for dark logos. The middle is see-through.</p>':'');
var CK={cx:1,cy:1,csz:1,bgv:1,glow:1};
function marks(){
Array.prototype.forEach.call(f.querySelectorAll('[data-qx]'),function(b){b.classList.toggle('on',+b.getAttribute('data-qx')===CEL.x&&+b.getAttribute('data-qy')===CEL.y);});
Array.prototype.forEach.call(f.querySelectorAll('[data-cauto]'),function(b){b.classList.toggle('on',CEL.cmode!=='free');});
Array.prototype.forEach.call(f.querySelectorAll('[data-cqx]'),function(b){b.classList.toggle('on',CEL.cmode==='free'&&+b.getAttribute('data-cqx')===CEL.cx&&+b.getAttribute('data-cqy')===CEL.cy);});
}
Array.prototype.forEach.call(f.querySelectorAll('[data-rg]'),function(r){r.oninput=function(){var k=r.getAttribute('data-rg');CEL[k]=+r.value;if((k==='cx'||k==='cy')&&CEL.cmode!=='free'){CEL.cmode='free';var chn=document.getElementById('dsce-chint');if(chn)chn.style.display='none';var csr=f.querySelector('[data-rg="csz"]');var csl=csr&&csr.parentNode.querySelector('.dsce-rgs span:last-child');if(csl)csl.textContent='Whole slide';}var pv=CK[k]?'cover':'content';if(CEL._pv!==pv)cePv(pv);var sp=r.parentNode.querySelector('.dsce-rgl span');if(sp)sp.textContent=r.value+'%';marks();ceLPrev();};});
Array.prototype.forEach.call(f.querySelectorAll('[data-qx]'),function(b){b.onclick=function(){CEL.x=+b.getAttribute('data-qx');CEL.y=+b.getAttribute('data-qy');ceLForm();cePv('content');};});
Array.prototype.forEach.call(f.querySelectorAll('[data-cqx]'),function(b){b.onclick=function(){CEL.cmode='free';CEL.cx=+b.getAttribute('data-cqx');CEL.cy=+b.getAttribute('data-cqy');ceLForm();cePv('cover');};});
Array.prototype.forEach.call(f.querySelectorAll('[data-cauto]'),function(b){b.onclick=function(){CEL.cmode='auto';ceLForm();cePv('cover');};});
document.getElementById('dsce-lcover').onchange=function(){CEL.cover=this.checked;ceLForm();cePv('cover');};
var lf2=document.getElementById('dsce-lfile2');if(lf2)lf2.onchange=function(){var fl=this.files&&this.files[0];if(!fl)return;if(fl.size>8*1024*1024){ceToast('That file is too large (max 8 MB).','error');return;}var rd=new FileReader();rd.onload=function(){var im=new Image();im.onload=function(){var w=im.naturalWidth||im.width||600,h=im.naturalHeight||im.height||240,sc=Math.min(1,1400/w,800/h);var cv=document.createElement('canvas');cv.width=Math.max(1,Math.round(w*sc));cv.height=Math.max(1,Math.round(h*sc));cv.getContext('2d').drawImage(im,0,0,cv.width,cv.height);try{CEL.src2=cv.toDataURL('image/png');CEL.w2=cv.width;CEL.h2=cv.height;}catch(e){ceToast('Could not read that image.','error');return;}ceLForm();cePv('content');};im.onerror=function(){ceToast('Could not read that image.','error');};im.src=rd.result;};rd.readAsDataURL(fl);};
var lr2=document.getElementById('dsce-lrm2');if(lr2)lr2.onclick=function(){delete CEL.src2;delete CEL.w2;delete CEL.h2;ceLForm();cePv('content');};
var lsb=document.getElementById('dsce-lsbadge');if(lsb)lsb.onchange=function(){CEL.sbadge=this.checked;ceLForm();cePv('content');};
var lbd=document.getElementById('dsce-lbadge');if(lbd)lbd.onchange=function(){CEL.badge=this.checked;ceLForm();cePv('cover');};
  document.getElementById('dsce-lfile').onchange=function(){var fl=this.files&&this.files[0];if(!fl)return;
    if(fl.size>8*1024*1024){ceToast('That file is too large (max 8 MB).','error');return;}
    var rd=new FileReader();rd.onload=function(){var im=new Image();im.onload=function(){
      var w=im.naturalWidth||im.width||600,h=im.naturalHeight||im.height||240,sc=Math.min(1,1400/w,800/h);
      var cv=document.createElement('canvas');cv.width=Math.max(1,Math.round(w*sc));cv.height=Math.max(1,Math.round(h*sc));
      cv.getContext('2d').drawImage(im,0,0,cv.width,cv.height);
      try{CEL.src=cv.toDataURL('image/png');}catch(e){ceToast('Could not read that image.','error');return;}
      CEL.w=cv.width;CEL.h=cv.height;ceLForm();ceLPrev();
    };im.onerror=function(){ceToast('Could not read that image.','error');};im.src=rd.result;};rd.readAsDataURL(fl);};
}

/* ---------------- buttons over the slide ---------------- */
function ceBar(){
  var fs=document.getElementById('dsp-editor-fullscreen'),main=document.getElementById('dsp-ppt-main-slide'),bar=document.getElementById('dsce-bar');
  var on=fs&&fs.classList.contains('dsp-open')&&main&&main.offsetWidth&&S.data&&S.data.slides&&S.data.slides.length&&!document.querySelector('.dsce-ov');
  if(!on){if(bar){bar.style.display='none';bar.setAttribute('data-st','');}return;}
  ceCss();
  if(!bar){
    bar=document.createElement('div');bar.id='dsce-bar';
    bar.innerHTML='<button type="button" id="dsce-bedit" title="Edit this slide: text, points, key takeaway">&#9998; EDIT SLIDE</button><button type="button" class="dsce-alt" id="dsce-blogo" title="Add your company logo to every slide">&#127991; LOGO</button>';
    document.body.appendChild(bar);
    document.getElementById('dsce-bedit').onclick=function(e){e.preventDefault();e.stopPropagation();dsceEditSlide(S.idx);};
    document.getElementById('dsce-blogo').onclick=function(e){e.preventDefault();e.stopPropagation();dsceLogoPanel();};
  }
  var s=S.data.slides[S.idx>=0?S.idx:0];
  document.getElementById('dsce-bedit').style.display=(s&&s.dg)?'none':'';
  /* sits just above the slide's top-right corner (or just below it when there is no room) */
  var mr=main.getBoundingClientRect(),top=mr.top-40;if(top<50)top=mr.bottom+8;
  var st='display:flex;position:fixed;z-index:2147482000;top:'+Math.round(top)+'px;right:'+Math.round(window.innerWidth-mr.right)+'px;';
  if(bar.getAttribute('data-st')!==st){bar.setAttribute('data-st',st);bar.style.cssText=st;}
}
/* keep the logo on every copy of the deck (the editor keeps two) */
function ceSyncLogo(){if(!window.G||!G.pptData||!S.data||S.data===G.pptData)return;
  if(G.pptData.logo&&!S.data.logo)S.data.logo=G.pptData.logo;else if(S.data.logo&&!G.pptData.logo)G.pptData.logo=S.data.logo;}
setInterval(function(){try{ceSyncLogo();}catch(e){}ceBar();},600);
window.addEventListener('resize',ceBar);
document.addEventListener('dblclick',function(e){
  var main=e.target&&e.target.closest?e.target.closest('#dsp-ppt-main-slide'):null;if(!main)return;
  if(e.target.closest('[contenteditable="true"]')||e.target.closest('.dsp-vp-imgzone'))return;
  var s=S.data&&S.data.slides&&S.data.slides[S.idx];if(!s||s.dg)return;
  dsceEditSlide(S.idx);
},true);
/* ================= end DS CLASSIC EDITOR ================= */

/* ── panel render ── */
function vpType(){if(!S.data||!S.data.slides||S.idx<0)return null;var s=S.data.slides[S.idx];return s?dsNormType(s.type||'bullet'):null;}
function vpRender(idx){
  S.idx=idx;
  var lbl=document.getElementById('vp-lbl'),list=document.getElementById('vp-list');if(!list)return;
  var type=vpType();
  if(!type){if(lbl)lbl.textContent='Slide '+(idx+1);list.innerHTML='<div class="vp-empty">Generate a presentation first, then click a slide to see layouts.</div>';return;}
  if(lbl)lbl.textContent='Slide '+(idx+1)+' · '+type;
  var cnt=(S.data.slides[idx].content||[]).length;
  var vtype=(type==='bullet'&&cnt===5)?'five_point':(type==='bullet'&&cnt===6)?'six_point':(type==='bullet'&&cnt===7)?'seven_point':(type==='bullet'&&cnt===8)?'eight_point':type;
  if(dsceMany(S.data.slides[idx]))vtype='many_point';
  var _dsTplP=window.DS_TPL_ACTIVE&&window.DS_TPL_ACTIVE();var _ceXV=!_dsTplP&&ceExtraVariants(S.data.slides[idx]);var variants=(_dsTplP&&_dsTplP.variants(type,S.data.slides[idx]))||(type==='chart'&&window.DS_CHART&&window.DS_CHART.variants&&window.DS_CHART.variants())||V[vtype]||V[type]||V.bullet,chosen=S.choices['slide_'+idx]||'v1';if(_ceXV)variants=_ceXV;
var slide=S.data.slides[idx];
var theme=gt();
list.innerHTML=variants.map(function(v){
  var sel=v.id===chosen;
  // Render actual slide content as HTML preview
  var slideThumb={};for(var _k in slide)slideThumb[_k]=slide[_k];
slideThumb.image_data=slide.image_data;
var previewHtml=renderSlide(slideThumb,theme,v.id,idx);
previewHtml=previewHtml.replace(/class="dsp-vp-ihover[^"]*"/g,'class="dsp-vp-ihover" style="display:none!important;"');
previewHtml=previewHtml.replace(/class="dsp-vp-imgreset[^"]*"/g,'style="display:none!important;"');
previewHtml=previewHtml.replace(/class="dsp-vp-cropbtn[^"]*"/g,'style="display:none!important;"');
previewHtml=previewHtml.replace(/contenteditable="true"/g,'contenteditable="false"');
  return '<div class="vp-card'+(sel?' vp-sel':'')+'" onclick="dspVPPick('+idx+',\''+v.id+'\')">'
    +'<div class="vp-thumb" style="position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;font-size:3.5px;pointer-events:none;user-select:none;" onclick="dspVPPick('+idx+',\''+v.id+'\')">'
    +'<div style="position:absolute;inset:0;z-index:9999;cursor:pointer;" onclick="dspVPPick('+idx+',\''+v.id+'\')"></div>'
    +previewHtml+'</div>'
    +'<div class="vp-lbl">'+(sel?'&#10003; ':'')+v.lbl+'</div>'
    +'</div>';
}).join('');
vpLoadMktImagesForCards(idx, type, variants, chosen);
}

// Fetch the marketplace image for a slide, if one is cached and the slide
// doesn't already have image data. Safe to call repeatedly — it self-guards.
window.vpEnsureSlideImage=function(idx){
  if(!S.data||!S.data.slides||!S.data.slides[idx])return;
  if(S.data.slides[idx].image_data)return;
  var cachedUrl=_vpMktImageCache[idx];
  if(!cachedUrl)return;
  if(!window._vpMktImgPending) window._vpMktImgPending={};
  if(window._vpMktImgPending[idx])return;
  window._vpMktImgPending[idx]=true;
  var nc=window.DSP_NONCE||'';
  fetch('/wp-json/ds/v1/mkt-fetch-image',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':nc},
    body:JSON.stringify({url:cachedUrl})
  })
  .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
  .then(function(d){
    var imgData = d && d.data ? d.data : '';
    if(imgData && imgData.indexOf('data:image/')===0 && imgData.indexOf(';base64,')>0){
      if(S.data && S.data.slides && S.data.slides[idx]){
        S.data.slides[idx].image_data = imgData;
        applyCanvas(idx, S.choices['slide_'+idx] || 'v1');
      }
    }
    delete window._vpMktImgPending[idx];
  })
  .catch(function(){ delete window._vpMktImgPending[idx]; });
};

window.dspVPPick=function(idx,vid){
  // The user's choice is now ALWAYS honoured. Previously, picking an image
  // variant before the marketplace thumbnail had loaded hit a fallback that
  // silently reset the choice to v1 — which read as "layouts won't select
  // until the images appear". The image now arrives asynchronously instead.
  vid=ceSwitchKind(idx,vid);
  S.choices['slide_'+idx]=vid;
  try{sessionStorage.setItem('dspVPC',JSON.stringify(S.choices));}catch(e){}
  var imageVariants=['v2','v3','v4','v5','v6','v7','v8','v9','v10'];
  if(imageVariants.indexOf(vid)>=0) vpEnsureSlideImage(idx);
  vpRender(idx);
  applyCanvas(idx,vid);
  if(window.dspToast)dspToast('Layout applied!','success');
};
/* ── panel open/close ── */
function injectBtn(){
  // No toggle button — panel stays permanently open
  vpOpen();
}
window.dspVPToggle=function(){}; // disabled, panel always open
			 // Block PPTX download if marketplace images are still being fetched
document.addEventListener('click', function(e){
  var btn = e.target.closest
    ? e.target.closest('[id*="download"],[id*="dsp-dl"],[class*="dsp-download"]')
    : null;
  if(!btn) return;
  var pending = window._vpMktImgPending && Object.keys(window._vpMktImgPending).length > 0;
  if(pending){
    e.preventDefault();
    e.stopImmediatePropagation();
    if(window.dspToast) dspToast('⏳ Waiting for marketplace image to load — please try again in a moment.','info');
    return false;
  }
}, true); // capture phase so it fires before the download handler
function vpOpen(){
  var fs=document.getElementById('dsp-editor-fullscreen');
  if(!fs||!fs.classList.contains('dsp-open'))return; // only open inside the PPT editor
  S.open=true;
  var p=document.getElementById('dsp-vp-panel');if(p)p.classList.add('vp-open');
  document.body.classList.add('vp-showing');
  vpRender(S.idx>=0?S.idx:0);
}
			 function vpClose(){
  S.open=false;
  var p=document.getElementById('dsp-vp-panel');if(p)p.classList.remove('vp-open');
  document.body.classList.remove('vp-showing');
}
window.vpToggleCollapse=function(){
  var p=document.getElementById('dsp-vp-panel');
  if(!p)return;
  var collapsed=p.classList.toggle('vp-collapsed');
  document.body.classList.toggle('vp-collapsed',collapsed);
};

/* ── observe editor ── */
var _obs=null;
function setupObs(){var fs=document.getElementById('dsp-editor-fullscreen');if(!fs||_obs)return;if(fs.classList.contains('dsp-open'))setTimeout(vpOpen,150);_obs=new MutationObserver(function(){if(fs.classList.contains('dsp-open')){setTimeout(vpOpen,150);}else{vpClose();}});_obs.observe(fs,{attributes:true,attributeFilter:['class']});}

/* ── canvas observer: re-apply variant when 12b re-renders the slide ── */
// When user clicks a thumb, 12b's dspSelectThumb() overwrites main canvas with
// the default layout. We detect this change and re-apply the chosen variant.
var _cvObs=null, _cvApplying=false;
function setupCvObs(){
  var main=document.getElementById('dsp-ppt-main-slide');
  if(!main||_cvObs)return;
  _cvObs=new MutationObserver(function(){
    if(_cvApplying)return; // ignore our own writes
    // Read the REAL active slide straight from the thumbnail strip instead
    // of trusting S.idx — S.idx can update a tick late, which is what made
    // the main slide briefly redraw the previous slide on the first click.
    var curIdx=S.idx;
    var thumbs=document.getElementById('dsp-ppt-thumbs');
    if(thumbs){
      var activeIdx=Array.from(thumbs.children).findIndex(function(el){return el.classList.contains('dsp-thumb-active');});
      if(activeIdx>=0)curIdx=activeIdx;
    }
    S.idx=curIdx;
    var vid=S.choices['slide_'+curIdx]||'v1';
    if((vid!=='v1'||ceNeedsOwn(S.data&&S.data.slides&&S.data.slides[curIdx])||(window.DS_TPL_ACTIVE&&window.DS_TPL_ACTIVE())||(S.data&&S.data.slides&&S.data.slides[curIdx]&&S.data.slides[curIdx].type==='chart'))&&S.data&&S.data.slides&&S.data.slides[curIdx]){
      _cvApplying=true;
      applyCanvas(curIdx,vid);
      setTimeout(function(){_cvApplying=false;},150);
    }
  });
  _cvObs.observe(main,{childList:true,subtree:false});
}

/* ── slide thumb tracking: stays correct on click, add-slide, delete-slide, anything ── */
var _activeObs=null,_thumbsApplying=false;
function setupActiveObs(){
  var thumbs=document.getElementById('dsp-ppt-thumbs');
  if(!thumbs||_activeObs)return;
  _activeObs=new MutationObserver(function(muts){
    if(_thumbsApplying)return;
    var children=Array.from(thumbs.children);
    var activeIdx=children.findIndex(function(el){return el.classList.contains('dsp-thumb-active');});
    if(activeIdx<0)return;
    S.idx=activeIdx;
    if(S.open)vpRender(activeIdx);
    var rebuilt=muts.some(function(m){return m.target===thumbs&&m.type==='childList';});
    if(rebuilt){
      _thumbsApplying=true;
      vpPreloadAllSlides();
      setTimeout(function(){_thumbsApplying=false;},200);
    }
  });
  _activeObs.observe(thumbs,{attributes:true,attributeFilter:['class'],childList:true,subtree:true});
}

/* ── auto-regenerate images on draft load ── */
function regenImages(slides){
  if(!slides||!Array.isArray(slides))return;
  var url=S.imgUrl||window.DSP_IMG_URL||(window.location.origin+'/wp-json/ds/v1/fetch-slide-image');
  var nc=S.nonce||window.DSP_NONCE||((window.wpApiSettings||{}).nonce||'');
  var need=slides.map(function(s,i){return{s:s,i:i};})
    .filter(function(x){return (x.s.image_query||x.s.title)&&!x.s.image_data;});
  if(!need.length)return;
  // Show status
  var stEl=document.getElementById('dsp-imgstatus-tb');
  if(stEl)stEl.textContent='🎨 Regenerating '+need.length+' image(s)...';
  var done=0;
  need.forEach(function(x,qi){
    setTimeout(function(){
      var q=x.s.image_query||(x.s.title+' professional presentation');
      fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nc},body:JSON.stringify({query:q})})
      .then(function(r){return r.json();})
      .then(function(d){
        if(d&&d.success&&d.image){
          if(S.data&&S.data.slides&&S.data.slides[x.i]){
            S.data.slides[x.i].image_data=d.image;
          }
          done++;
          if(stEl)stEl.textContent=done<need.length?'🎨 '+done+'/'+need.length+' images...' :'';
          // Re-render current slide if this is the active one
          if(x.i===S.idx){
            var vid=S.choices['slide_'+x.i]||'v1';
            applyCanvas(x.i,vid);
          }
          // Update thumb
          var thumbs=document.getElementById('dsp-ppt-thumbs');
          if(thumbs&&thumbs.children[x.i]){
            var box=thumbs.children[x.i].querySelector('.dsp-thumb-box,[style*="font-size:3"]');
            if(box&&S.data&&S.data.slides){
              var vid=S.choices['slide_'+x.i]||'v1';
              box.innerHTML=renderSlide(S.data.slides[x.i],gt(),vid);
            }
          }
        }
      }).catch(function(){});
    }, qi * 2500); // stagger 2.5s apart
  });
}

/* ── fetch interceptor ── */
var _of=window.fetch;
window.fetch=function(url,opts){
  var us=typeof url==='string'?url:'';
  if(us.indexOf('/ds/v1/fetch-slide-image')!==-1){if(!S.imgUrl)S.imgUrl=us;if(!S.nonce&&opts&&opts.headers)S.nonce=opts.headers['X-WP-Nonce']||'';}
  if(us.indexOf('/ds/v1/generate-ppt')!==-1){
    var _reqBody={};
    try{_reqBody=JSON.parse(opts&&opts.body?opts.body:'{}');}catch(e){}
    var _isAddSlide=(_reqBody.slides===1||_reqBody.num_slides===1);
    return _of.apply(this,arguments).then(function(r){r.clone().json().then(function(d){if(d&&d.ppt_data&&d.ppt_data.slides){
          // Strip markdown artifacts (**bold**, *italic*, ## headers) from AI text
          d.ppt_data.slides.forEach(function(s){
            if(s.title)s.title=stripMd(s.title);
            if(s.subtitle)s.subtitle=stripMd(s.subtitle);
            if(s.content&&Array.isArray(s.content))s.content=s.content.map(stripMd);
          });
          if(_isAddSlide&&S.data&&S.data.slides){
            // "Add Slide" reuses this same endpoint for one slide only —
            // append it, don't wipe out the other slides' saved layouts/images.
            d.ppt_data.slides.forEach(function(s){S.data.slides.push(s);});
          } else {
            S.data=d.ppt_data;S.choices={};S.idx=0;try{sessionStorage.setItem('dspVPC','{}');}catch(e){}
          }
        }}).catch(function(){});return r;});}
  if(us.indexOf('/ds/v1/load-ppt')!==-1){
    vpShowLoadOverlay();
    return _of.apply(this,arguments).then(function(r){r.clone().json().then(function(d){
    if(d&&d.data&&d.data.slides){
      S.data=d.data;S.idx=0;
      // Auto-regeneration on load is disabled — reopening a saved draft
      // must reuse the exact images that were saved, never call the AI
      // image generator again automatically.
      var slideCount=d.data.slides.length,tries=0;
      var _wait=setInterval(function(){
        tries++;
        var thumbs=document.getElementById('dsp-ppt-thumbs');
        if(thumbs&&thumbs.children.length===slideCount){
          clearInterval(_wait);
          vpPreloadAllSlides();
          vpHideLoadOverlay();
        }else if(tries>40){
          clearInterval(_wait);
          vpHideLoadOverlay();
        }
      },150);
    }else{
      vpHideLoadOverlay();
    }
  }).catch(function(){vpHideLoadOverlay();});return r;});}
  if(us.indexOf('/ds/v1/save-ppt')!==-1){
    // The images you pick/generate via this panel live in S.data — the
    // editor's own copy (G.pptData) that save-ppt normally sends never
    // gets those images. Merge them in slide-by-slide here, but never
    // let a slide with no image in S.data wipe out one that's already there.
    var sb={};
    try{sb=JSON.parse(opts&&opts.body?opts.body:'{}');}catch(e){}
    if(S.data&&S.data.slides&&S.data.slides.length){
      if(!sb.ppt_data)sb.ppt_data={};
      var orig=Array.isArray(sb.ppt_data.slides)?sb.ppt_data.slides:[];
      var merged=S.data.slides.map(function(sd,i){
        var base=orig[i]||{};
        var out={};
        for(var k in base)out[k]=base[k];
        for(var k2 in sd)out[k2]=sd[k2];
        if(!out.image_data&&base.image_data)out.image_data=base.image_data;
        return out;
      });
      sb.ppt_data.slides=merged;
    }
    var sno={};for(var sk in(opts||{}))sno[sk]=opts[sk];sno.body=JSON.stringify(sb);
    return _of.call(this,url,sno);
  }
  if(us.indexOf('/ds/v1/build-pptx')!==-1&&us.indexOf('v2')===-1){
    // ALWAYS redirect to v2 — merge updated image_data + variant choices into request
    var nu=us.replace('/build-pptx','/build-pptx-v2'),b={};
    try{b=JSON.parse(opts&&opts.body?opts.body:'{}');}catch(e){}
    // Merge image_data from S.data (user-generated images) into build request
    if(S.data&&S.data.slides&&b.slides&&Array.isArray(b.slides)){
      for(var si=0;si<b.slides.length;si++){
        var ms=S.data.slides[si];
        if(!ms)continue;
        if(ms.image_data)b.slides[si].image_data=ms.image_data;
        if(ms.image_query)b.slides[si].image_query=ms.image_query;
		  if(ms.image_data)b.slides[si].image_data=ms.image_data;
if(ms.image_query)b.slides[si].image_query=ms.image_query;
if(ms.image_position)b.slides[si].image_position=ms.image_position;
      }
    }
    // If S.data has slides, use them directly instead of G.pptData copy
    if(S.data&&S.data.slides&&S.data.slides.length){
  b.slides=S.data.slides.map(function(sl,si){
    var copy={};
    for(var k in sl) copy[k]=sl[k];
    // Convert bullet->five/six/seven_point based on content count + chosen variant
    var vid=S.choices['slide_'+si]||'v1';
    var cnt=(copy.content||[]).length;
    if(copy.type==='bullet'&&cnt===5)copy.type='five_point';
    else if(copy.type==='bullet'&&cnt===6)copy.type='six_point';
    else if(copy.type==='bullet'&&cnt===7)copy.type='seven_point';
else if(copy.type==='bullet'&&cnt===8)copy.type='eight_point';
    if(dsceMany(copy))copy.type='many_point';
    return copy;
  });
}
b.slide_variants=S.choices;
if(window.dsceDeckLogo&&!(window.DS_TPL_ACTIVE&&window.DS_TPL_ACTIVE())){var _ceLg=window.dsceDeckLogo();if(_ceLg)b.logo=_ceLg;}
      if(!ceTplOn()&&window.dsDeckStyleGet){var _dsSt=window.dsDeckStyleGet();if(_dsSt)b.style=_dsSt;}   /* DS Deck Style */
    var fontEl=document.getElementById('dsp-font');
    if(fontEl&&fontEl.value)b.font_family=fontEl.value;if(b.style&&b.style.font)b.font_family=b.style.font;
    var no={};for(var k in(opts||{}))no[k]=opts[k];no.body=JSON.stringify(b);
    return _of.call(this,nu,no);
  }
  return _of.apply(this,arguments);
};

/* ── init ── */
			 // Expose slide image update so other snippets (marketplace) can update slide images

window.dspUpdateSlideImage = function(idx, imgData, query) {
  if (!S.data || !S.data.slides || !S.data.slides[idx]) return false;
  S.data.slides[idx].image_data = imgData;
  if (query) S.data.slides[idx].image_query = query;
  if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[idx]) {
    window.G.pptData.slides[idx].image_data = imgData;
    if (query) window.G.pptData.slides[idx].image_query = query;
  }
  // Re-render canvas with the new image
  var vid = S.choices['slide_' + idx] || 'v1';
  if (vid === 'v1') { vid = 'v2'; S.choices['slide_' + idx] = 'v2'; }
  S.idx = idx;
  applyCanvas(idx, vid);
  return true;
};

window.vpAddBullet = function(idx){
  if (!S.data || !S.data.slides || !S.data.slides[idx]) return;
  var slide = S.data.slides[idx];
  if (!slide.content) slide.content = [];
  slide.content.push('New point');
  var vid = S.choices['slide_' + idx] || 'v1';
  applyCanvas(idx, vid);
};

/* ── inline text editing on the canvas: typing writes straight back
   into S.data, so Save (which reads from S.data) picks it up automatically ── */
document.addEventListener('input', function(e){
  var el = e.target;
  if (!el || !el.getAttribute || !el.getAttribute('data-ed-field')) return;
  var slideIdx = parseInt(el.getAttribute('data-ed-slide'), 10);
  if (!S.data || !S.data.slides || !S.data.slides[slideIdx]) return;
  var slide = S.data.slides[slideIdx];
  var field = el.getAttribute('data-ed-field');
  var text = el.innerText || '';
  var main = document.getElementById('dsp-ppt-main-slide');
  function pairUp(fieldA, fieldB, idxAttr, joiner){
    var ci = parseInt(idxAttr, 10);
    if (!slide.content) slide.content = [];
    var aEl = main ? main.querySelector('[data-ed-field="'+fieldA+'"][data-ed-idx="' + ci + '"]') : null;
    var bEl = main ? main.querySelector('[data-ed-field="'+fieldB+'"][data-ed-idx="' + ci + '"]') : null;
    var av = aEl ? aEl.innerText.trim() : '';
    var bv = bEl ? bEl.innerText.trim() : '';
    slide.content[ci] = bv ? (av + joiner + bv) : av;
  }
  if (field === 'title') { slide.title = text; }
  else if (field === 'subtitle') { slide.subtitle = text; }
  else if (field === 'content-plain') {
    var pi = parseInt(el.getAttribute('data-ed-idx'), 10);
    if (!slide.content) slide.content = [];
    slide.content[pi] = text;
  }
  else if (field === 'content-h' || field === 'content-d') {
    pairUp('content-h', 'content-d', el.getAttribute('data-ed-idx'), ': ');
  }
  else if (field === 'stat-num' || field === 'stat-label') {
    pairUp('stat-num', 'stat-label', el.getAttribute('data-ed-idx'), ':');
  }
  else if (field === 'tl-step' || field === 'tl-desc') {
    pairUp('tl-step', 'tl-desc', el.getAttribute('data-ed-idx'), ': ');
  }
  else if (field.indexOf('tf-') === 0) {
    var tfLabels={'tf-company':'Company','tf-presenter':'Presenter','tf-designation':'Designation','tf-date':'Date','tf-website':'Website','tf-tagline':'Tagline','tf-confidential':'Confidential'};
    var wantLabel=tfLabels[field];
    if (wantLabel) {
      if (!slide.content) slide.content = [];
      var found=false;
      for (var ci2=0; ci2<slide.content.length; ci2++) {
        var itemStr=String(slide.content[ci2]),colonIdx=itemStr.indexOf(':');
        if (colonIdx>0 && itemStr.slice(0,colonIdx).trim().toLowerCase()===wantLabel.toLowerCase()) {
          slide.content[ci2]=wantLabel+': '+text;
          found=true;
          break;
        }
      }
      if (!found) slide.content.push(wantLabel+': '+text);
    }
  }
}, true);
document.addEventListener('keydown', function(e){
  if (e.key === 'Enter' && e.target && e.target.getAttribute && e.target.getAttribute('data-ed-field')) {
    e.preventDefault();
    e.target.blur();
  }
}, true);

			 window.dspAddSlide=function(){
  if(!S.data||!S.data.slides)return;
  var modal=document.getElementById('dsp-addslide-modal');
  if(modal){modal.classList.add('dsp-open');var inp=document.getElementById('dsp-addslide-input');if(inp){inp.value='';inp.focus();}}
};

window.dspAddSlideClose=function(){
  var modal=document.getElementById('dsp-addslide-modal');
  if(modal)modal.classList.remove('dsp-open');
};

window.dspAddSlideGenerate=function(){
  var inp=document.getElementById('dsp-addslide-input');
  if(!inp||!inp.value.trim()){inp&&inp.focus();return;}
  if(!S.data||!S.data.slides)return;
  var raw=inp.value.trim();
  var cost=(window.DS_CHART_CFG&&parseInt(window.DS_CHART_CFG.cost,10))||5;
  if(G.credits<cost){dspToast('Not enough credits. Need '+cost+'cr.','error');return;}
  var btn=document.getElementById('dsp-addslide-btn');
  if(btn){btn.textContent='Generating...';btn.disabled=true;}
  var designEl=document.getElementById('dsp-design');
  var design=(designEl&&designEl.value)?designEl.value:'navy-pro';
  var langEl=document.getElementById('dsp-lang');
  var lang=langEl?langEl.value:'English';

  // ── Detect slide type from user input ──────────────────────────────────
  // If the user typed a layout keyword (e.g. "SWOT", "Timeline 6 point",
  // "6 points", "header") extract the slide_type and use the remaining text
  // (or the current deck topic) as the content subject.
  var low=raw.toLowerCase();
  var slide_type=null, topic=raw;

  var typeMap=[
    {patterns:[/\bswot\b/],             type:'swot'},
    {patterns:[/\bheader\b/,/\btitle\b/,/\bcover\b/],type:'title'},
    {patterns:[/\bquote\b/],            type:'quote'},
    {patterns:[/\bstats?\b/,/\bmetrics?\b/,/\bnumbers?\b/],type:'stats'},
    {patterns:[/\btimeline\b.*?(\d+)\s*p/,/(\d+)\s*p.*?\btimeline\b/], type:'timeline', extract:true},
    {patterns:[/\btimeline\b/],         type:'timeline'},
    {patterns:[/\b(\d+)\s*points?\b/,/\b(\d+)\s*bullets?\b/], type:'bullet', extract:true},
    {patterns:[/\bbullets?\b/,/\blist\b/],type:'bullet'},
    {patterns:[/\btwo.?col/,/\b2.?col/,/\bsplit\b/], type:'two_column'},
    {patterns:[/\bthree.?col/,/\b3.?col/], type:'three_column'},
    {patterns:[/\bfeature/,/\bgrid\b/,/\b4.?point/,/\bfour.?point/], type:'feature_grid'},
  ];

  var extra_hints={};
  for(var ti=0;ti<typeMap.length;ti++){
    var tm=typeMap[ti];
    for(var pi=0;pi<tm.patterns.length;pi++){
      var pm=low.match(tm.patterns[pi]);
      if(pm){
        slide_type=tm.type;
        // Extract point count if present (e.g. "6 points" → num_points:6)
        if(tm.extract && pm[1]){extra_hints.num_points=parseInt(pm[1],10);}
        // Strip the layout keyword from topic so the AI gets clean subject text
        topic=raw.replace(new RegExp(tm.patterns[pi].source,'i'),'').replace(/^\s*[-:,]\s*/,'').trim();
        if(!topic){
          // No subject text left — fall back to current deck title as context
          var titleEl=document.getElementById('dsp-etitle');
          topic=(titleEl&&titleEl.textContent.trim())||raw;
        }
        break;
      }
    }
    if(slide_type)break;
  }
  // ──────────────────────────────────────────────────────────────────────

  var payload={topic:topic,slides:1,num_slides:1,language:lang,design:design,use_images:false,amount:cost,append_slide:true};
  if(slide_type)payload.slide_type=slide_type;
  if(extra_hints.num_points)payload.num_points=extra_hints.num_points;

  _of.call(window, PURL, {method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify(payload)
  })
  .then(function(r){return r.json();})
  .then(function(d){
    if(btn){btn.textContent='✨ Generate Slide';btn.disabled=false;}
    if(d.credits_balance!==undefined){G.credits=d.credits_balance;var ce=document.getElementById('ds-cr-display');if(ce)ce.textContent=G.credits.toLocaleString();}
    if(d&&d.ppt_data&&d.ppt_data.slides&&d.ppt_data.slides.length){
      var newSlide=d.ppt_data.slides[0];
      newSlide.title=stripMd(newSlide.title||'');
      if(newSlide.subtitle)newSlide.subtitle=stripMd(newSlide.subtitle);
      if(newSlide.content&&Array.isArray(newSlide.content))newSlide.content=newSlide.content.map(stripMd);
      S.data.slides.push(newSlide);
      if(window.G&&window.G.pptData&&window.G.pptData.slides&&window.G.pptData.slides!==S.data.slides){
        window.G.pptData.slides.push(newSlide);
      }
      var newIdx=S.data.slides.length-1;
      dspAddSlideClose();
      if(window.renderEditList)window.renderEditList();
      if(window.dspRenderThumbs)window.dspRenderThumbs();
      if(window.dspSelectThumb)window.dspSelectThumb(newIdx);
      dspToast('Slide added! ('+cost+'cr used)','success');
    } else {
      var em=(d&&(d.message||d.error))?(d.message||d.error):'Could not generate slide. Try again.';
      dspToast(em,'error');
    }
  })
  .catch(function(){
    if(btn){btn.textContent='✨ Generate Slide';btn.disabled=false;}
    dspToast('Network error. Try again.','error');
  });
};
var _t=setInterval(function(){if(document.getElementById('dsp-editor-fullscreen')&&document.getElementById('dsp-ppt-main-slide')){clearInterval(_t);setupObs();injectBtn();setupCvObs();setupActiveObs();}},300);
			 var _vpMktImageCache = {}; // cache: slideIdx -> image URL
			 var _vpMktLoadGen = 0;

function vpLoadMktImagesForCards(slideIdx, type, variants, chosen){
  var imageVariants=['v2','v3','v4','v5','v6','v7','v8','v9','v10'];
  var hasImageLayouts=variants.some(function(v){return imageVariants.indexOf(v.id)>=0;});
  if(!hasImageLayouts)return;

  if(_vpMktImageCache[slideIdx]){
    vpApplyMktImageToCards(slideIdx, variants, _vpMktImageCache[slideIdx]);
    return;
  }

  // Previously a 503 cached nothing and was swallowed by .catch(), so every
  // vpRender() — i.e. every layout click — fired another request. Requests
  // piled up, starved PHP workers, and caused more 503s.
  if(!window._vpMktInflight) window._vpMktInflight={};
  if(!window._vpMktFailedAt) window._vpMktFailedAt={};

  // One request per slide at a time.
  if(window._vpMktInflight[slideIdx]) return;

  // Negative cache: after a failure, wait 60s before trying that slide again.
  var failedAt=window._vpMktFailedAt[slideIdx];
  if(failedAt && (Date.now()-failedAt) < 60000) return;

  var slideForSearch=S.data&&S.data.slides?S.data.slides[slideIdx]:null;
  var searchTerm=slideForSearch?(slideForSearch.image_query||slideForSearch.title||''):'';
  var searchWords=searchTerm.split(/\s+/).filter(function(w){return w.length>2;}).slice(0,2).join(' ');

  var url='/wp-json/ds/v1/mkt-slide-images?per_page=1';
  if(searchWords) url+='&s='+encodeURIComponent(searchWords);
  var nc=window.DSP_NONCE||'';

  var _myGen=++_vpMktLoadGen;
  window._vpMktInflight[slideIdx]=true;

  function release(failed){
    delete window._vpMktInflight[slideIdx];
    if(failed) window._vpMktFailedAt[slideIdx]=Date.now();
  }

  fetch(url,{headers:{'X-WP-Nonce':nc}})
  .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
  .then(function(d){
    if(_myGen!==_vpMktLoadGen){ release(false); return; }
    if(d&&d.items&&d.items.length){
      var thumbUrl=d.items[0].thumb;
      var fullUrl=d.items[0].full||thumbUrl;
      _vpMktImageCache[slideIdx]=fullUrl;
      vpApplyMktImageToCards(slideIdx, variants, thumbUrl);
      // A URL is now available — if an image layout is already selected,
      // load the actual image into the main canvas.
      if(imageVariants.indexOf(S.choices['slide_'+slideIdx]||'v1')>=0) vpEnsureSlideImage(slideIdx);
      release(false);
      return;
    }
    var fallbackUrl='/wp-json/ds/v1/mkt-slide-images?per_page=1&page='+((slideIdx%5)+1);
    fetch(fallbackUrl,{headers:{'X-WP-Nonce':nc}})
    .then(function(r2){ if(!r2.ok) throw new Error('HTTP '+r2.status); return r2.json(); })
    .then(function(d2){
      if(!d2||!d2.items||!d2.items.length){ release(true); return; }
      var thumbUrl2=d2.items[0].thumb;
      var fullUrl2=d2.items[0].full||thumbUrl2;
      _vpMktImageCache[slideIdx]=fullUrl2;
      vpApplyMktImageToCards(slideIdx, variants, thumbUrl2);
      release(false);
    }).catch(function(){ release(true); });
  })
  .catch(function(){ release(true); });
}

function vpApplyMktImageToCards(slideIdx, variants, mktImgUrl){
  var imageVariants=['v2','v3','v4','v5','v6','v7','v8','v9','v10'];
  var cards=document.querySelectorAll('#vp-list .vp-card');
  variants.forEach(function(v,i){
    if(imageVariants.indexOf(v.id)<0)return;
    var card=cards[i];if(!card)return;
    var imgZones=card.querySelectorAll('.dsp-vp-imgzone');
    imgZones.forEach(function(zone){
      var inner=zone.querySelector('div');
      if(inner){
        inner.style.background='url('+mktImgUrl+') center/cover no-repeat';
        inner.innerHTML='';
      }
      zone.onclick=function(e){
        e.stopPropagation();
        window._vpMktTargetSlide=slideIdx;
        window._vpMktTargetVariant=v.id;
        window.dsMktPickOpen(slideIdx);
      };
    });
  });
}
})();
</script>
	
	<style>
.dsp-vp-imgzone{cursor:grab;}
.dsp-vp-imgzone.dsp-img-dragging{cursor:grabbing;}
.dsp-vp-imgreset{position:absolute;top:8px;right:8px;z-index:5;width:26px;height:26px;border-radius:50%;border:none;background:rgba(0,0,0,.55);color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s;}
.dsp-vp-imgzone:hover .dsp-vp-imgreset{opacity:1;}
.dsp-vp-cropbtn{position:absolute;bottom:8px;left:50%;transform:translateX(-50%);z-index:5;background:rgba(0,0,0,.65);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:6px;padding:4px 12px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;opacity:0;transition:opacity .15s;font-family:inherit;pointer-events:auto;}
.dsp-vp-imgzone:hover .dsp-vp-cropbtn{opacity:1;}
</style>
<script>
(function () {
  var DRAG_THRESHOLD = 5;
  var active = null;
  var cropEdit = null;

  function clamp(v) { return Math.max(0, Math.min(100, v)); }

  function getStoredPos(idx) {
    if (!window.S || !window.S.data || !window.S.data.slides || !window.S.data.slides[idx]) return {x:50,y:50};
    var p = window.S.data.slides[idx].image_position;
    return (p && typeof p.x === 'number') ? p : {x:50,y:50};
  }

  function commitPos(idx, x, y) {
    var pos = {x:x, y:y};
    if (window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx])
      window.S.data.slides[idx].image_position = pos;
    if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[idx])
      window.G.pptData.slides[idx].image_position = pos;
  }

  function enterCropEdit(zone, idx) {
    if (cropEdit && cropEdit.idx === idx) return;
    exitCropEdit(true);
    var slide = window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx];
    if (!slide || !slide.image_data) {
      if (typeof window.vpImgOptionOpen === 'function') window.vpImgOptionOpen(idx);
      return;
    }
    var pos = getStoredPos(idx);
    cropEdit = { idx: idx, zone: zone, pos: {x:pos.x, y:pos.y} };
    var mainSlide = document.getElementById('dsp-ppt-main-slide');
    if (!mainSlide) return;
    if (getComputedStyle(mainSlide).position === 'static') mainSlide.style.position = 'relative';
    var mainRect = mainSlide.getBoundingClientRect();
    var zoneRect = zone.getBoundingClientRect();
    var zL = Math.max(0, (zoneRect.left - mainRect.left) / mainRect.width * 100);
    var zT = Math.max(0, (zoneRect.top  - mainRect.top)  / mainRect.height * 100);
    var zW = Math.min(100 - zL, zoneRect.width  / mainRect.width  * 100);
    var zH = Math.min(100 - zT, zoneRect.height / mainRect.height * 100);
    if (zW < 5 || zH < 5) { zL=0; zT=0; zW=100; zH=100; }
    var ov = document.createElement('div');
    ov.id = 'dsp-cre-fullslide';
    ov.style.cssText = 'position:absolute;inset:0;z-index:1000;overflow:hidden;cursor:grab;user-select:none;';
    var imgBg = document.createElement('div');
    imgBg.style.cssText = 'position:absolute;inset:0;background-image:url('+slide.image_data+');background-size:cover;background-repeat:no-repeat;background-position:'+pos.x+'% '+pos.y+'%;pointer-events:none;';
    ov.appendChild(imgBg);
    cropEdit.imgBg = imgBg;
    function makeShade(l,t,w,h){var d=document.createElement('div');d.style.cssText='position:absolute;background:rgba(0,0,0,0.58);pointer-events:none;left:'+l+'%;top:'+t+'%;width:'+w+'%;height:'+h+'%;';return d;}
    ov.appendChild(makeShade(0,0,100,zT));
    ov.appendChild(makeShade(0,zT+zH,100,100-zT-zH));
    ov.appendChild(makeShade(0,zT,zL,zH));
    ov.appendChild(makeShade(zL+zW,zT,100-zL-zW,zH));
    var frame = document.createElement('div');
    frame.style.cssText = 'position:absolute;pointer-events:none;box-sizing:border-box;border:2px solid rgba(255,255,255,0.9);left:'+zL+'%;top:'+zT+'%;width:'+zW+'%;height:'+zH+'%;';
    var grid = document.createElement('div');
    grid.style.cssText = 'position:absolute;inset:0;display:grid;grid-template-columns:1fr 1fr 1fr;grid-template-rows:1fr 1fr 1fr;';
    for(var gi=0;gi<9;gi++){var cell=document.createElement('div');cell.style.cssText='border:0.5px solid rgba(255,255,255,0.2);';grid.appendChild(cell);}
    [['top:-2px;left:-2px;border-width:3px 0 0 3px;'],['top:-2px;right:-2px;border-width:3px 3px 0 0;'],['bottom:-2px;left:-2px;border-width:0 0 3px 3px;'],['bottom:-2px;right:-2px;border-width:0 3px 3px 0;']].forEach(function(s){var c=document.createElement('div');c.style.cssText='position:absolute;width:14px;height:14px;border-color:#fff;border-style:solid;pointer-events:none;'+s[0];frame.appendChild(c);});
    frame.appendChild(grid);
    ov.appendChild(frame);
    var hint=document.createElement('div');
    hint.style.cssText='position:absolute;bottom:36px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.8);font-size:11px;pointer-events:none;white-space:nowrap;text-shadow:0 1px 3px rgba(0,0,0,0.9);';
    hint.textContent='Drag to reposition \u2022 Framed area appears in your slide';
    ov.appendChild(hint);
    var doneBtn=document.createElement('button');
    doneBtn.innerHTML='\u2713 Done';
    doneBtn.style.cssText='position:absolute;bottom:8px;right:8px;z-index:10;background:#2563eb;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;';
    doneBtn.onmousedown=function(e){e.stopPropagation();};
    doneBtn.onclick=function(e){e.stopPropagation();exitCropEdit(false);};
    ov.appendChild(doneBtn);
    var cancelBtn=document.createElement('button');
    cancelBtn.innerHTML='\u2715';
    cancelBtn.style.cssText='position:absolute;bottom:8px;right:80px;z-index:10;background:rgba(0,0,0,0.5);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:6px 10px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;';
    cancelBtn.onmousedown=function(e){e.stopPropagation();};
    cancelBtn.onclick=function(e){e.stopPropagation();exitCropEdit(true);};
    ov.appendChild(cancelBtn);
    mainSlide.appendChild(ov);
    cropEdit.ov = ov;
  }

  function exitCropEdit(cancel) {
    if (!cropEdit) return;
    var idx = cropEdit.idx;
    if (!cancel) {
      commitPos(idx, cropEdit.pos.x, cropEdit.pos.y);
      var v = (window.S && window.S.choices && window.S.choices['slide_'+idx]) || 'v1';
      if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs();
      if (typeof window.applyCanvas === 'function') window.applyCanvas(idx, v);
    }
    if (cropEdit.ov && cropEdit.ov.parentNode) cropEdit.ov.parentNode.removeChild(cropEdit.ov);
    cropEdit = null;
  }

  // Called by the ✂️ Crop button inside imgZone HTML
  window.vpStartCropEdit = function(idx) {
    var zone = document.querySelector('#dsp-ppt-main-slide .dsp-vp-imgzone[data-slide-idx="'+idx+'"]');
    if (!zone) zone = document.querySelector('.dsp-vp-imgzone[data-slide-idx="'+idx+'"]');
    if (zone) enterCropEdit(zone, idx);
  };

  // Override vpImgOptionOpen to track current slide index
  window.vpImgOptionOpen = function(idx) {
    window._vpOptIdx = idx;
    window._vpMktTargetSlide = idx;
    window._vpMktTargetVariant = window.S ? (window.S.choices['slide_'+idx] || 'v2') : 'v2';
    if (typeof MKT !== 'undefined') MKT.slideIdx = idx;
    window.MKT = window.MKT || {};
    window.MKT.slideIdx = idx;
    var hasCrop = window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx] && window.S.data.slides[idx].image_data;
    var cropBtn = document.getElementById('vp-img-option-crop-btn');
    if (cropBtn) cropBtn.style.display = hasCrop ? 'block' : 'none';
    var m = document.getElementById('vp-img-option-modal');
    if (m) m.style.display = 'flex';
  };
 
  document.addEventListener('mousedown', onDown);
  document.addEventListener('touchstart', onDown, { passive: false });
  window.addEventListener('mousemove', onMove);
  window.addEventListener('touchmove', onMove, { passive: false });
  window.addEventListener('mouseup', onUp);
  window.addEventListener('touchend', onUp);

  function onDown(e) {
    // Let buttons handle their own clicks
    if (e.target.closest && e.target.closest('.dsp-vp-imgreset')) return;
    if (e.target.closest && e.target.closest('.dsp-vp-cropbtn')) return;
    if (e.target.closest && e.target.closest('button')) return;

    if (cropEdit) {
      var ov = document.getElementById('dsp-cre-fullslide');
      if (ov && ov.contains(e.target)) {
        var pt = e.touches ? e.touches[0] : e;
        active = { isCrop:true, startPx:pt.clientX, startPy:pt.clientY, startPos:{x:cropEdit.pos.x, y:cropEdit.pos.y} };
        ov.style.cursor = 'grabbing';
        if (e.cancelable) e.preventDefault();
        return;
      }
      exitCropEdit(false);
      return;
    }

    var zone = e.target.closest && e.target.closest('.dsp-vp-imgzone');
    if (!zone) return;
    // Skip zones inside the thumbnail panel - only act on the main slide
    if (zone.closest('#dsp-ppt-thumbs')) return;
    var idx = parseInt(zone.getAttribute('data-slide-idx'), 10);
    if (isNaN(idx)) return;
    var pt = e.touches ? e.touches[0] : e;
    active = { isCrop:false, el:zone, idx:idx, startPx:pt.clientX, startPy:pt.clientY, moved:false };
  }

  function onMove(e) {
    if (!active) return;
    var pt = e.touches ? e.touches[0] : e;
    var dx = pt.clientX - active.startPx;
    var dy = pt.clientY - active.startPy;
    if (active.isCrop && cropEdit) {
      var mainSlide = document.getElementById('dsp-ppt-main-slide');
      var mw = mainSlide ? mainSlide.getBoundingClientRect().width  : 800;
      var mh = mainSlide ? mainSlide.getBoundingClientRect().height : 450;
      var nx = clamp(active.startPos.x - (dx / mw) * 100 * 1.2);
      var ny = clamp(active.startPos.y - (dy / mh) * 100 * 1.2);
      cropEdit.pos = {x:nx, y:ny};
      if (cropEdit.imgBg) cropEdit.imgBg.style.backgroundPosition = nx+'% '+ny+'%';
      if (e.cancelable) e.preventDefault();
      return;
    }
    if (!active.isCrop) {
      if (!active.moved && (Math.abs(dx) > DRAG_THRESHOLD || Math.abs(dy) > DRAG_THRESHOLD))
        active.moved = true;
    }
  }

  function onUp(e) {
    if (!active) return;
    var isCrop = active.isCrop;
    var zone   = active.el;
    var idx    = active.idx;
    var moved  = active.moved;
    active = null;
    if (cropEdit && cropEdit.ov) cropEdit.ov.style.cursor = 'grab';
    // Single click on zone (no drag) -> open the Change Image popup
    if (!isCrop && !moved && zone) {
      if (typeof window.vpImgOptionOpen === 'function') window.vpImgOptionOpen(idx);
    }
  }

  window.dspResetImagePosition = function(idx) {
    commitPos(idx, 50, 50);
    if (cropEdit && cropEdit.idx === idx) {
      cropEdit.pos = {x:50, y:50};
      if (cropEdit.imgBg) cropEdit.imgBg.style.backgroundPosition = '50% 50%';
    }
    var v = (window.S && window.S.choices && window.S.choices['slide_'+idx]) || 'v1';
    if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs();
    if (typeof window.applyCanvas === 'function') window.applyCanvas(idx, v);
  };
})();
</script>
<?php
});
