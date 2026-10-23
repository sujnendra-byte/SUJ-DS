<?php
/**
 * Code Snippets export — 11 ds ai thumbnails
 *
 * Snippet ID : 700
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-014
 * Modified   : 2026-09-09 02:44:49
 * Load order : 014 of 144
 */
/**
 * Snippet Name: DS AI Thumbnails
 * Run: Everywhere
 * Shortcode: [ds_ai_thumbnails]
 * Features: Platform presets, text overlay on generated image, multilingual
 */
add_shortcode('ds_ai_thumbnails', function() {
    if (!is_user_logged_in()) return '';
    $uid   = get_current_user_id();
    $cr    = (int) get_user_meta($uid, 'ds_ai_credits', true);
    $nonce = wp_create_nonce('wp_rest');
    $gurl  = esc_js(rest_url('ds/v1/generate-image'));
    $surl  = esc_js(rest_url('ds/v1/generation-status/'));
    ob_start(); ?>

<div id="dsthumb" class="ds-mode-wrap">

<!-- HOME -->
<div id="dsthumb-home">

  <!-- Platform presets -->
  <div style="margin-bottom:18px;">
    <span class="ds-slabel">? Platform Preset</span>
    <div style="display:flex;gap:8px;flex-wrap:wrap;" id="dsthumb-platforms">
      <?php
      $platforms = [
        ['YT','YouTube',1280,720,'16:9','#FF0000'],
        ['IG','Instagram',1080,1080,'1:1','#E1306C'],
        ['IG-S','Instagram Story',1080,1920,'9:16','#833AB4'],
        ['FB','Facebook',1200,628,'16:9','#1877F2'],
        ['TW','Twitter/X',1200,675,'16:9','#000000'],
        ['LI','LinkedIn',1200,627,'16:9','#0A66C2'],
        ['PIN','Pinterest',1000,1500,'2:3','#E60023'],
        ['CUSTOM','Custom','','','1:1','#6366f1'],
      ];
      foreach ($platforms as $i => $p): ?>
      <div class="dsthumb-plat <?php echo $i===0?'dsthumb-plat-on':''; ?>"
        data-name="<?php echo esc_attr($p[1]); ?>"
        data-w="<?php echo $p[2]; ?>"
        data-h="<?php echo $p[3]; ?>"
        data-r="<?php echo esc_attr($p[4]); ?>"
        onclick="dsthumbPlat(this)"
        style="padding:8px 14px;border-radius:10px;border:2px solid <?php echo $i===0?'#6366f1':'#e5e7eb'; ?>;background:<?php echo $i===0?'#eef2ff':'#fff'; ?>;cursor:pointer;font-size:12px;font-weight:700;color:<?php echo $i===0?'#4f46e5':'#6b7280'; ?>;transition:all .2s;display:flex;align-items:center;gap:6px;">
        <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $p[5]; ?>;flex-shrink:0;"></span>
        <?php echo $p[0]; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:8px;font-size:12px;color:#9ca3af;" id="dsthumb-pinfo">YouTube Thumbnail ? 1280?720 ? 16:9</div>
  </div>

  <!-- Image prompt -->
  <div class="ds-pbox">
    <textarea id="dsthumb-prompt" class="ds-pta" rows="3"
      placeholder="Describe the background image... e.g. 'Dramatic tech workspace with glowing screens, dark moody lighting'"
      oninput="dsthumbChar()"></textarea>
    <div style="display:flex;justify-content:space-between;padding:6px 18px 12px;font-size:12px;color:#9ca3af;">
      <span>? Describe the background image - text will be added on top</span>
      <span id="dsthumb-char">0/500</span>
    </div>
  </div>

  <!-- Text overlay settings -->
  <div style="background:#fff;border:1.5px solid #e8eaf0;border-radius:16px;padding:18px;margin-bottom:14px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
      <span style="font-size:13px;font-weight:700;color:#0f0f23;">Text Overlay</span>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#6b7280;">
        <div style="position:relative;width:38px;height:22px;background:#e5e7eb;border-radius:11px;transition:background .2s;" id="dsthumb-tog-track" onclick="dsthumbTogText()">
          <div id="dsthumb-tog-knob" style="position:absolute;top:2px;left:2px;width:18px;height:18px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 4px rgba(0,0,0,.2);"></div>
        </div>
        <span id="dsthumb-tog-lbl">Add text to image</span>
      </label>
    </div>

    <div id="dsthumb-text-opts">

      <!-- Main title text -->
      <div style="margin-bottom:12px;">
        <span class="ds-slabel">Main Title (large text)</span>
        <input id="dsthumb-title" type="text" placeholder="e.g. 10 AI Tools That Changed Everything"
          style="width:100%;padding:11px 14px;border-radius:11px;border:1.5px solid #e5e7eb;font-size:14px;font-family:inherit;outline:none;transition:border .2s;"
          onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e5e7eb'"/>
      </div>

      <!-- Subtitle text -->
      <div style="margin-bottom:12px;">
        <span class="ds-slabel">Subtitle (small text, optional)</span>
        <input id="dsthumb-sub" type="text" placeholder="e.g. Must Watch in 2026!"
          style="width:100%;padding:11px 14px;border-radius:11px;border:1.5px solid #e5e7eb;font-size:14px;font-family:inherit;outline:none;transition:border .2s;"
          onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e5e7eb'"/>
      </div>

      <!-- Language for text -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <span class="ds-slabel">? Text Language</span>
          <div style="position:relative;" class="ds-wrap">
            <button class="ds-sel" id="dsthumb-lsel" onclick="dsthumbTog('dsthumb-ldd',this)">
              <span id="dsthumb-llbl">English</span><span style="font-size:10px;opacity:.4;margin-left:auto;">?</span>
            </button>
            <div class="ds-dd" id="dsthumb-ldd">
              <?php
              $langs = [
                ['','English','English'],['','Hindi',''],
                ['','Kannada',''],['','Tamil',''],
                ['','Telugu',''],['','Bengali',''],
                ['','Marathi',''],['','Gujarati',''],
                ['','Malayalam',''],['','Bengali',''],
                ['','Arabic',''],['','Japanese',''],
                ['','Korean',''],['','German','Deutsch'],
                ['','French','Fran?ais'],['','Spanish','Espa?ol'],
              ];
              foreach ($langs as $l): ?>
              <div class="ds-opt <?php echo $l[1]==='English'?'ds-on':''; ?>" onclick="dsthumbLang('<?php echo esc_js($l[0]); ?> <?php echo esc_js($l[1]); ?>','<?php echo esc_js($l[1]); ?>',this)">
                <?php echo $l[0]; ?> <?php echo $l[1]; ?> <span style="color:#9ca3af;font-size:11px;"><?php echo $l[2]; ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Text position -->
        <div>
          <span class="ds-slabel">? Text Position</span>
          <div style="position:relative;" class="ds-wrap">
            <button class="ds-sel" id="dsthumb-psel" onclick="dsthumbTog('dsthumb-pdd',this)">
              <span id="dsthumb-plbl">? Bottom Left</span><span style="font-size:10px;opacity:.4;margin-left:auto;">?</span>
            </button>
            <div class="ds-dd" id="dsthumb-pdd">
              <div class="ds-opt" onclick="dsthumbPos('top-left','? Top Left',this)">? Top Left</div>
              <div class="ds-opt" onclick="dsthumbPos('top-center','? Top Center',this)">? Top Center</div>
              <div class="ds-opt" onclick="dsthumbPos('top-right','? Top Right',this)">? Top Right</div>
              <div class="ds-opt" onclick="dsthumbPos('center','? Center',this)">? Center</div>
              <div class="ds-opt ds-on" onclick="dsthumbPos('bottom-left','? Bottom Left',this)">? Bottom Left</div>
              <div class="ds-opt" onclick="dsthumbPos('bottom-center','? Bottom Center',this)">? Bottom Center</div>
              <div class="ds-opt" onclick="dsthumbPos('bottom-right','? Bottom Right',this)">? Bottom Right</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Text style -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
          <span class="ds-slabel">? Text Style</span>
          <div style="position:relative;" class="ds-wrap">
            <button class="ds-sel" onclick="dsthumbTog('dsthumb-tsdd',this)">
              <span id="dsthumb-tslbl">? Bold Yellow</span><span style="font-size:10px;opacity:.4;margin-left:auto;">?</span>
            </button>
            <div class="ds-dd" id="dsthumb-tsdd">
              <div class="ds-opt ds-on" onclick="dsthumbTS('? Bold Yellow','#FFD700','#000000','900',this)">? Bold Yellow</div>
              <div class="ds-opt" onclick="dsthumbTS('White Shadow','#FFFFFF','#000000','800',this)">White Shadow</div>
              <div class="ds-opt" onclick="dsthumbTS('? Red Impact','#FF3333','#000000','900',this)">? Red Impact</div>
              <div class="ds-opt" onclick="dsthumbTS('Blue Bold','#4FC3F7','#003366','800',this)">Blue Bold</div>
              <div class="ds-opt" onclick="dsthumbTS('Gradient','gradient','#000000','900',this)">Gradient</div>
              <div class="ds-opt" onclick="dsthumbTS('? Black Clean','#111111','#ffffff','700',this)">? Black Clean</div>
            </div>
          </div>
        </div>
        <div>
          <span class="ds-slabel">Model</span>
          <div style="position:relative;" class="ds-wrap">
            <button class="ds-sel" onclick="dsthumbTog('dsthumb-mdd2',this)">
              <span id="dsthumb-mlbl2">Cloudflare AI</span><span style="font-size:10px;opacity:.4;margin-left:auto;">?</span>
            </button>
            <div class="ds-dd" id="dsthumb-mdd2">
              <div class="ds-opt ds-on" onclick="dsthumbMod2('Cloudflare AI','cloudflare',1,this)">Cloudflare AI<span class="ds-badge ds-free">1cr</span></div>
              <div class="ds-opt" onclick="dsthumbMod2('Flux Schnell','flux-schnell',5,this)">Flux Schnell<span class="ds-badge ds-mid">5cr</span></div>
              <div class="ds-opt" onclick="dsthumbMod2('? Flux Dev','flux-dev',15,this)">? Flux Dev<span class="ds-badge ds-high">15cr</span></div>
              <div class="ds-opt" onclick="dsthumbMod2('DALL?E 3','dall-e-3',40,this)">DALL?E 3<span class="ds-badge ds-high">40cr</span></div>
              <div class="ds-opt" onclick="dsthumbMod2('Google Gemini','gemini',5,this)">Google Gemini<span class="ds-badge ds-mid">5cr</span></div>
              <div class="ds-opt" onclick="dsthumbMod2('Seedream 4.5','seedream',10,this)">Seedream 4.5<span class="ds-badge ds-high">10cr</span></div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- text opts -->
  </div><!-- text overlay card -->

  <!-- Cost bar -->
  <div style="background:#fff;border:1.5px solid #e0e7ff;border-radius:14px;padding:11px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:13px;color:#6b7280;" id="dsthumb-costlbl">Cloudflare AI ? YouTube 16:9</span>
    <span style="font-size:14px;font-weight:800;color:#6366f1;">Total: <span id="dsthumb-total">10</span>cr</span>
  </div>

  <div style="text-align:center;margin-bottom:36px;">
    <button class="ds-genbtn" onclick="dsthumbGen()">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L4.5 13.5H11L10 22L19.5 10.5H13L14 2z"/></svg>
      Generate Thumbnail
    </button>
  </div>

  <!-- Examples -->
  <div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
      <div style="flex:1;height:1px;background:linear-gradient(90deg,transparent,#e5e7eb);"></div>
      <span style="font-size:12px;color:#9ca3af;font-weight:600;">Background image examples</span>
      <div style="flex:1;height:1px;background:linear-gradient(90deg,#e5e7eb,transparent);"></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
      <?php foreach([
        ['','Dramatic tech workspace, glowing screens, dark moody atmosphere'],
        ['','Person holding cash, confetti background, celebration feeling'],
        ['','Trophy on podium with spotlight, dramatic lighting, winner theme'],
        ['','Futuristic AI robot face, neon lights, cyberpunk city background'],
        ['','Delicious food close-up, steam rising, warm appetizing lighting'],
        ['','Gaming setup with RGB lights, controller in hands, dark room'],
      ] as $e): ?>
      <div onclick="document.getElementById('dsthumb-prompt').value='<?php echo esc_js($e[1]); ?>';dsthumbChar();"
        style="padding:12px 14px;background:#fff;border-radius:12px;border:1.5px solid #e8eaf0;cursor:pointer;display:flex;align-items:flex-start;gap:8px;transition:all .2s;"
        onmouseover="this.style.borderColor='#c7d2fe';this.style.background='#eef2ff'"
        onmouseout="this.style.borderColor='#e8eaf0';this.style.background='#fff'">
        <span style="font-size:17px;flex-shrink:0;"><?php echo $e[0]; ?></span>
        <span style="font-size:12px;color:#4b5563;line-height:1.4;"><?php echo esc_html($e[1]); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div><!-- home -->

<!-- LOADING -->
<div id="dsthumb-loading" style="display:none;text-align:center;padding:60px 24px;">
  <div class="ds-spinner"><div class="ds-ring1"></div><div class="ds-ring2"></div><div class="ds-ring-ic">?</div></div>
  <h3 style="font-size:22px;font-weight:800;color:#0f0f23;margin:0 0 8px;">Creating Thumbnail...</h3>
  <p style="font-size:13px;color:#6b7280;margin:0 0 24px;">Generating image then adding your text overlay</p>
  <div style="background:#e8eaf0;border-radius:100px;height:5px;overflow:hidden;max-width:320px;margin:0 auto 12px;">
    <div id="dsthumb-lprog" style="height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:100px;width:5%;transition:width .8s ease;"></div>
  </div>
  <p style="font-size:12px;color:#9ca3af;" id="dsthumb-lhint">Generating background image...</p>
  <button onclick="dsthumbHome()" style="margin-top:16px;padding:8px 20px;border-radius:100px;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;font-size:13px;cursor:pointer;font-family:inherit;">Cancel</button>
</div>

<!-- RESULTS -->
<div id="dsthumb-results" style="display:none;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <button class="ds-back" onclick="dsthumbHome()">? New Thumbnail</button>
    <span style="font-size:14px;font-weight:700;color:#0f0f23;">Thumbnail Ready!</span>
    <a href="<?php echo home_url('/my-generated-images'); ?>" style="font-size:13px;color:#6366f1;text-decoration:none;font-weight:600;">View All ?</a>
  </div>
  <div id="dsthumb-rbody"></div>
</div>

</div><!-- #dsthumb -->

<style>
#dsthumb{padding:4px 0;}
#dsthumb .ds-wrap{position:relative;}
.dsthumb-plat{transition:all .2s;}
.dsthumb-plat-on{border-color:#6366f1!important;background:#eef2ff!important;color:#4f46e5!important;}
.dsthumb-plat:hover{border-color:#a5b4fc!important;color:#4f46e5!important;}
/* Canvas for text overlay */
#dsthumb-canvas-wrap{position:relative;display:inline-block;max-width:100%;}
#dsthumb-canvas{max-width:100%;display:block;border-radius:12px;}
</style>

<script>
(function(){
var G={
  credits:<?php echo (int)$cr; ?>,
  model:'cloudflare',cost:1,mname:'Cloudflare AI',
  platform:'YouTube',W:1280,H:720,ratio:'16:9',
  textOn:true,
  title:'',sub:'',
  lang:'English',
  pos:'bottom-left',
  txtColor:'#FFD700',shadowColor:'#000000',fontWeight:'900',
  progT:null,pollT:null,predId:null,
  lastImgUrl:null,
};
var NONCE='<?php echo esc_js($nonce); ?>',GURL='<?php echo $gurl; ?>',SURL='<?php echo $surl; ?>';

function q(id){return document.getElementById(id);}
function dsthumbChar(){var v=q('dsthumb-prompt').value;q('dsthumb-char').textContent=v.length+'/500';}
window.dsthumbChar=dsthumbChar;

// Toggle text
var textOn=true;
window.dsthumbTogText=function(){
  textOn=!textOn;
  var track=q('dsthumb-tog-track'),knob=q('dsthumb-tog-knob'),lbl=q('dsthumb-tog-lbl'),opts=q('dsthumb-text-opts');
  track.style.background=textOn?'#6366f1':'#e5e7eb';
  knob.style.transform=textOn?'translateX(16px)':'translateX(0)';
  lbl.textContent=textOn?'Add text to image':'No text overlay';
  opts.style.opacity=textOn?'1':'0.4';
  opts.style.pointerEvents=textOn?'':'none';
  G.textOn=textOn;
};
// Init toggle ON
(function(){
  q('dsthumb-tog-track').style.background='#6366f1';
  q('dsthumb-tog-knob').style.transform='translateX(16px)';
})();

// Platform
window.dsthumbPlat=function(el){
  document.querySelectorAll('.dsthumb-plat').forEach(function(b){b.classList.remove('dsthumb-plat-on');b.style.borderColor='#e5e7eb';b.style.background='#fff';b.style.color='#6b7280';});
  el.classList.add('dsthumb-plat-on');
  G.platform=el.dataset.name;
  G.W=parseInt(el.dataset.w)||1280;
  G.H=parseInt(el.dataset.h)||720;
  G.ratio=el.dataset.r;
  q('dsthumb-pinfo').textContent=G.platform+' ? '+G.W+'?'+G.H+' ? '+G.ratio;
  q('dsthumb-costlbl').textContent=G.mname+' ? '+G.platform+' '+G.ratio;
};

// Dropdowns
window.dsthumbTog=function(ddId,btn){
  var dd=q(ddId),open=dd.classList.contains('ds-open');
  document.querySelectorAll('#dsthumb .ds-dd').forEach(function(d){d.classList.remove('ds-open');});
  document.querySelectorAll('#dsthumb .ds-sel').forEach(function(b){b.classList.remove('ds-open');});
  if(!open){dd.classList.add('ds-open');btn.classList.add('ds-open');}
};
document.addEventListener('click',function(e){
  if(!e.target.closest('#dsthumb .ds-wrap')){
    document.querySelectorAll('#dsthumb .ds-dd').forEach(function(d){d.classList.remove('ds-open');});
    document.querySelectorAll('#dsthumb .ds-sel').forEach(function(b){b.classList.remove('ds-open');});
  }
});

window.dsthumbLang=function(lbl,lang,el){
  G.lang=lang;q('dsthumb-llbl').textContent=lbl;
  document.querySelectorAll('#dsthumb-ldd .ds-opt').forEach(function(o){o.classList.remove('ds-on');});
  el.classList.add('ds-on');q('dsthumb-ldd').classList.remove('ds-open');q('dsthumb-lsel').classList.remove('ds-open');
};
window.dsthumbPos=function(pos,lbl,el){
  G.pos=pos;q('dsthumb-plbl').textContent=lbl;
  document.querySelectorAll('#dsthumb-pdd .ds-opt').forEach(function(o){o.classList.remove('ds-on');});
  el.classList.add('ds-on');q('dsthumb-pdd').classList.remove('ds-open');q('dsthumb-psel').classList.remove('ds-open');
};
window.dsthumbTS=function(lbl,col,shadow,fw,el){
  G.txtColor=col;G.shadowColor=shadow;G.fontWeight=fw;
  q('dsthumb-tslbl').textContent=lbl;
  document.querySelectorAll('#dsthumb-tsdd .ds-opt').forEach(function(o){o.classList.remove('ds-on');});
  el.classList.add('ds-on');q('dsthumb-tsdd').classList.remove('ds-open');
};
window.dsthumbMod2=function(n,id,c,el){
  G.model=id;G.cost=c;G.mname=n;
  q('dsthumb-mlbl2').textContent=n;
  document.querySelectorAll('#dsthumb-mdd2 .ds-opt').forEach(function(o){o.classList.remove('ds-on');});
  el.classList.add('ds-on');q('dsthumb-mdd2').classList.remove('ds-open');
  q('dsthumb-total').textContent=G.cost;
  q('dsthumb-costlbl').textContent=G.mname+' ? '+G.platform+' '+G.ratio;
};

// Views
function show(v){['dsthumb-home','dsthumb-loading','dsthumb-results'].forEach(function(id){q(id).style.display='none';});q(v).style.display='block';}
window.dsthumbHome=function(){if(G.pollT){clearInterval(G.pollT);}if(G.progT){clearInterval(G.progT);}show('dsthumb-home');};

// Progress
function startProg(hints,start){
  var p=start||5,hi=0;
  q('dsthumb-lprog').style.width=p+'%';
  G.progT=setInterval(function(){
    p=Math.min(90,p+(p<50?6:(p<80?2:.5)));
    q('dsthumb-lprog').style.width=p+'%';
    hi=(hi+1)%hints.length;
    q('dsthumb-lhint').textContent=hints[hi];
  },3000);
}

// Generate
window.dsthumbGen=function(){
  var imgPrompt=q('dsthumb-prompt').value.trim();
  if(!imgPrompt){
    var b=q('dsthumb-home').querySelector('.ds-pbox');
    b.style.borderColor='#ef4444';setTimeout(function(){b.style.borderColor='#e8eaf0';},2000);return;
  }
  if(G.credits<G.cost){alert('Not enough credits! Need '+G.cost+' credits.');return;}

  G.title=q('dsthumb-title').value.trim();
  G.sub=q('dsthumb-sub').value.trim();

  // Build image prompt with platform context
  var prompt=imgPrompt+', '+G.platform+' thumbnail style, professional quality, high contrast, no text in image';
  show('dsthumb-loading');
  startProg(['Generating background image...','Rendering pixels...','Almost done...']);

  fetch(GURL,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({model:G.model,prompt:prompt,count:1,multiplier:1,width:Math.min(G.W,1344),height:Math.min(G.H,1344),size:Math.min(G.W,1344)+'x'+Math.min(G.H,1344)})})
  .then(function(r){return r.json();})
  .then(function(d){
    if(d.error){clearInterval(G.progT);show('dsthumb-home');alert(d.message||'Generation failed.');return;}
    if(d.credits_balance!==undefined){G.credits=d.credits_balance;var ce=document.getElementById('ds-cr-display');if(ce)ce.textContent=G.credits.toLocaleString();}
    if(d.status==='processing'&&d.prediction_id){G.predId=d.prediction_id;pollThumb();}
    else if(d.images){clearInterval(G.progT);applyTextOverlay(d.images[0].url);}
  }).catch(function(){clearInterval(G.progT);show('dsthumb-home');alert('Connection error.');});
};

function pollThumb(){
  var tries=0;
  G.pollT=setInterval(function(){
    tries++;if(tries>60){clearInterval(G.pollT);clearInterval(G.progT);show('dsthumb-home');alert('Timed out.');return;}
    fetch(SURL+G.predId,{headers:{'X-WP-Nonce':NONCE}}).then(function(r){return r.json();}).then(function(d){
      if(d.status==='complete'&&d.images){clearInterval(G.pollT);clearInterval(G.progT);applyTextOverlay(d.images[0].url);}
      else if(d.status==='failed'){clearInterval(G.pollT);clearInterval(G.progT);show('dsthumb-home');alert('Failed.');}
    }).catch(function(){});
  },3000);
}

// Apply text overlay using Canvas API
function applyTextOverlay(imgUrl){
  q('dsthumb-lhint').textContent='Adding text overlay...';
  q('dsthumb-lprog').style.width='95%';
  var img=new Image();
  img.crossOrigin='anonymous';
  img.onload=function(){
    var canvas=document.createElement('canvas');
    var displayW=Math.min(img.naturalWidth,1344);
    var displayH=Math.round(displayW*(img.naturalHeight/img.naturalWidth));
    canvas.width=displayW;canvas.height=displayH;
    var ctx=canvas.getContext('2d');
    ctx.drawImage(img,0,0,displayW,displayH);

    if(G.textOn&&(G.title||G.sub)){
      // Dark gradient overlay for text readability
      var pos=G.pos;
      var gradH=displayH*0.45;
      var gradY,gradDir;
      if(pos.includes('top')){gradY=0;gradDir='down';}
      else if(pos==='center'){gradY=displayH*0.25;gradDir='none';}
      else{gradY=displayH-gradH;gradDir='up';}

      var grd;
      if(gradDir==='down'){grd=ctx.createLinearGradient(0,0,0,gradH);grd.addColorStop(0,'rgba(0,0,0,0.82)');grd.addColorStop(1,'rgba(0,0,0,0)');}
      else if(gradDir==='up'){grd=ctx.createLinearGradient(0,gradY,0,displayH);grd.addColorStop(0,'rgba(0,0,0,0)');grd.addColorStop(1,'rgba(0,0,0,0.85)');}
      else{grd=ctx.createLinearGradient(0,gradY,0,gradY+gradH);grd.addColorStop(0,'rgba(0,0,0,0)');grd.addColorStop(.5,'rgba(0,0,0,0.7)');grd.addColorStop(1,'rgba(0,0,0,0)');}

      if(gradDir!=='none'){ctx.fillStyle=grd;ctx.fillRect(0,gradY,displayW,gradH);}
      else{ctx.fillStyle='rgba(0,0,0,0.5)';ctx.fillRect(0,gradY,displayW,gradH);}

      // Calculate text size based on canvas width
      var baseFontSize=Math.round(displayW*0.065);
      var subFontSize=Math.round(displayW*0.03);
      var padding=Math.round(displayW*0.05);

      var txX,tyTitle,tySub,tAlign;
      if(pos.includes('left')){txX=padding;tAlign='left';}
      else if(pos.includes('right')){txX=displayW-padding;tAlign='right';}
      else{txX=displayW/2;tAlign='center';}

      var titleH=G.sub?baseFontSize+subFontSize+padding*0.5:baseFontSize;
      if(pos.includes('top')){tyTitle=padding+baseFontSize;}
      else if(pos==='center'){tyTitle=displayH/2-(G.sub?baseFontSize/2+subFontSize*.5:baseFontSize/2);}
      else{tyTitle=displayH-padding-(G.sub?subFontSize+padding*0.4:0);}
      tySub=tyTitle+(G.title?baseFontSize*0.25+subFontSize:0);

      // Detect if we need special font for non-latin scripts
      var nonLatin=['Hindi','Kannada','Tamil','Telugu','Bengali','Marathi','Gujarati','Malayalam','Arabic','Japanese','Korean'];
      var isNonLatin=nonLatin.indexOf(G.lang)>=0;
      var fontStack=isNonLatin?
        "'Noto Sans Devanagari','Noto Sans','Noto Sans Tamil','Noto Sans Telugu','Noto Sans Bengali','Noto Sans Kannada','Noto Sans Malayalam','Noto Sans Gujarati','Amiri',serif":
        "'Impact','Arial Black','sans-serif'";

      function drawText(txt,x,y,fs,align,col,shadow){
        ctx.textAlign=align;ctx.textBaseline='top';
        ctx.font=G.fontWeight+' '+fs+'px '+fontStack;
        // Shadow
        ctx.shadowColor=shadow;ctx.shadowBlur=Math.round(fs*.4);ctx.shadowOffsetX=Math.round(fs*.03);ctx.shadowOffsetY=Math.round(fs*.03);
        if(col==='gradient'){
          var grd2=ctx.createLinearGradient(x-fs*3,y,x+fs*3,y+fs);
          grd2.addColorStop(0,'#FF6B6B');grd2.addColorStop(.5,'#FFD700');grd2.addColorStop(1,'#4FC3F7');
          ctx.fillStyle=grd2;
        } else {ctx.fillStyle=col;}
        ctx.fillText(txt,x,y);
        ctx.shadowColor='transparent';ctx.shadowBlur=0;ctx.shadowOffsetX=0;ctx.shadowOffsetY=0;
      }

      if(G.title) drawText(G.title,txX,tyTitle-baseFontSize,baseFontSize,tAlign,G.txtColor,G.shadowColor);
      if(G.sub)   drawText(G.sub,txX,tySub,subFontSize,tAlign,G.txtColor==='#FFD700'?'#ffffff':G.txtColor,G.shadowColor);
    }

    var finalUrl=canvas.toDataURL('image/jpeg',0.92);
    G.lastImgUrl=finalUrl;
    showThumbResult(finalUrl,imgUrl);
  };
  img.onerror=function(){
    // CORS error - show original image with text instructions
    showThumbResult(imgUrl,imgUrl);
  };
  img.src=imgUrl;
}

function showThumbResult(finalUrl,origUrl){
  q('dsthumb-lprog').style.width='100%';
  setTimeout(function(){
    show('dsthumb-results');
    var rp=G.ratio.split(':'),cssr=rp[0]+'/'+rp[1];
    var html='<div style="margin-bottom:20px;">';
    html+='<div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(99,102,241,0.12);border:1.5px solid #e8eaf0;">';
    html+='<img src="'+finalUrl+'" style="width:100%;display:block;aspect-ratio:'+cssr+';object-fit:cover;cursor:zoom-in;" alt="Thumbnail" onclick="dsthLightbox(\''+finalUrl+'\')"/>';
    html+='</div>';
    html+='<div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">';
    html+='<a href="'+finalUrl+'" download="thumbnail-'+G.platform.toLowerCase().replace(/\s/g,'-')+'.jpg" style="flex:1;min-width:140px;padding:12px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:700;text-decoration:none;text-align:center;">? Download '+G.platform+'</a>';
    if(origUrl!==finalUrl){
      html+='<a href="'+origUrl+'" download="bg-image.jpg" style="flex:1;min-width:140px;padding:12px;border-radius:12px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:14px;font-weight:600;text-decoration:none;text-align:center;">? Background Only</a>';
    }
    html+='</div>';

    // Show platform variants info
    html+='<div style="margin-top:16px;background:#f8faff;border-radius:12px;padding:14px 16px;">';
    html+='<div style="font-size:13px;font-weight:700;color:#0f0f23;margin-bottom:8px;">? Resize for other platforms:</div>';
    html+='<div style="display:flex;gap:8px;flex-wrap:wrap;">';
    var others=[['YouTube','1280?720'],['Instagram','1080?1080'],['Story','1080?1920'],['Pinterest','1000?1500']];
    others.forEach(function(p){
      if(p[0]!==G.platform){
        html+='<span style="padding:5px 12px;border-radius:100px;background:#eef2ff;color:#4f46e5;font-size:12px;font-weight:600;">'+p[0]+' '+p[1]+'</span>';
      }
    });
    html+='</div><div style="font-size:12px;color:#9ca3af;margin-top:8px;">Download then resize in Canva/Photoshop for other platforms</div></div>';
    html+='</div>';

    html+='<div style="text-align:center;margin-top:16px;">';
    html+='<button onclick="dsthumbHome()" class="ds-genbtn" style="padding:13px 32px;font-size:14px;">? New Thumbnail</button>';
    html+='</div>';
    q('dsthumb-rbody').innerHTML=html;
  },400);
}

// Update cost on load
q('dsthumb-total').textContent=G.cost;
})();
</script>

<?php /* lightbox */ ?>
<div id="dsth-lb" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.93);z-index:99999;align-items:center;justify-content:center;flex-direction:column;" onclick="if(event.target===this)dsthCloseLB()">
  <button onclick="dsthCloseLB()" style="position:absolute;top:16px;right:20px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:24px;width:44px;height:44px;border-radius:50%;cursor:pointer;">&#10005;</button>
  <img id="dsth-lb-img" src="" style="max-width:92vw;max-height:82vh;object-fit:contain;border-radius:12px;box-shadow:0 20px 80px rgba(0,0,0,.5);" alt="Thumbnail full"/>
  <a id="dsth-lb-dl" href="" download style="margin-top:14px;padding:10px 28px;border-radius:100px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:700;text-decoration:none;">&#11015; Download Full Resolution</a>
</div>
<script>
window.dsthLightbox = function(url) {
  var lb = document.getElementById('dsth-lb');
  document.getElementById('dsth-lb-img').src = url;
  document.getElementById('dsth-lb-dl').href = url;
  lb.style.display = 'flex';
  document.body.style.overflow = 'hidden';
};
window.dsthCloseLB = function() {
  document.getElementById('dsth-lb').style.display = 'none';
  document.body.style.overflow = '';
};
document.addEventListener('keydown',function(e){if(e.key==='Escape')dsthCloseLB();});
</script>

<?php return ob_get_clean();
});
