<?php
/**
 * Code Snippets export — 6 DS PPT Download Engine
 *
 * Snippet ID : 696
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-010
 * Modified   : 2026-09-09 02:41:36
 * Load order : 010 of 144
 */
/**
 * Snippet Name: DS AI Presentation UI
 * Shortcode: [ds_ai_ppt]
 * Run: Everywhere
 *
 * Dark theme UI for AI Presentation generator.
 * Calls the existing /ds/v1/generate-ppt REST endpoint (your Groq/AI engine).
 * Colour scheme matches AI Images dark theme.
 */
add_shortcode( 'ds_ai_ppt', 'ds_ai_ppt_shortcode' );
function ds_ai_ppt_shortcode() {
    if ( ! is_user_logged_in() ) return '';
    $uid     = get_current_user_id();
    $credits = (int) ds_get_credits( $uid );
    ob_start();
    ?>
<style>
/* ===== PPT UI — Dark Theme ===== */
#dsPPT { background:#0a0a0a; color:#fff; border-radius:12px; padding:0; }
#dsPPT * { box-sizing:border-box; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }

/* Textarea card */
.pp-ta-card { background:#111; border:1px solid #1e1e1e; border-radius:12px; margin-bottom:14px; overflow:hidden; transition:border-color .2s; }
.pp-ta-card:focus-within { border-color:#2563eb; }
.pp-ta { width:100%; background:transparent; border:none; outline:none; resize:none; padding:16px 18px 8px; font-size:13.5px; color:#c9d1d9; line-height:1.75; font-family:inherit; display:block; min-height:170px; }
.pp-ta::placeholder { color:#2a3040; }
.pp-ta-foot { display:flex; align-items:center; justify-content:space-between; padding:8px 16px 10px; border-top:1px solid #161616; }

/* Mode cards — 2 column */
.pp-modes { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
.pp-mode { padding:13px 15px; border-radius:10px; border:1.5px solid #1e1e1e; background:#0d0d0d; cursor:pointer; transition:all .15s; }
.pp-mode.on { border-color:#2563eb; background:#0a1628; }
.pp-mode-h { font-size:13px; font-weight:700; color:#9ca3af; margin-bottom:3px; display:flex; align-items:center; gap:6px; }
.pp-mode.on .pp-mode-h { color:#60a5fa; }
.pp-mode-d { font-size:11.5px; color:#374151; line-height:1.4; }

/* Controls 2-col */
.pp-ctrl { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px; }
.pp-lbl { color:#374151; font-size:9.5px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; margin-bottom:7px; display:flex; align-items:center; gap:5px; }

/* DROPDOWN — proper styling that actually works */
.pp-sel-wrap { position:relative; }
.pp-sel-wrap::after { content:'▾'; position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#4b5563; font-size:11px; pointer-events:none; }
.pp-sel {
    width:100%; background:#0d0d0d; border:1px solid #1e1e1e;
    border-radius:8px; color:#c9d1d9; padding:10px 30px 10px 11px;
    font-size:13px; font-weight:500; cursor:pointer; outline:none;
    -webkit-appearance:none; -moz-appearance:none; appearance:none;
    transition:border-color .15s;
}
.pp-sel:focus { border-color:#2563eb; }
.pp-sel option { background:#1a1a1a; color:#c9d1d9; }

/* Include images row */
.pp-img-row { display:flex; align-items:center; gap:9px; padding:10px 13px; background:#0d0d0d; border:1px solid #1e1e1e; border-radius:8px; cursor:pointer; transition:border-color .15s; }
.pp-img-row:hover { border-color:#2563eb; }
.pp-chk { width:18px; height:18px; border-radius:4px; background:#2563eb; border:none; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background .15s; }
.pp-chk.off { background:#1a1a1a; border:1.5px solid #252525; }
.pp-chk-lbl { color:#9ca3af; font-size:13px; }

/* Summary bar */
.pp-summary { display:flex; align-items:center; justify-content:space-between; padding:11px 15px; background:#0d0d0d; border:1px solid #1a1a1a; border-radius:9px; margin-bottom:14px; flex-wrap:wrap; gap:8px; }
.pp-sum-txt { color:#4b5563; font-size:12.5px; }
.pp-sum-cr { color:#2563eb; font-weight:700; font-size:13px; }
.pp-sum-have { color:#2d3748; font-size:12px; }

/* Generate button */
.pp-gen { width:100%; background:#2563eb; color:#fff; border:none; padding:15px; border-radius:10px; font-size:14px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; letter-spacing:.4px; text-transform:uppercase; transition:background .15s; }
.pp-gen:hover { background:#1d4ed8; }
.pp-gen:disabled { opacity:.4; cursor:default; }
.pp-spin { width:17px; height:17px; border:2.5px solid rgba(255,255,255,.2); border-top-color:#fff; border-radius:50%; animation:ppSpin .7s linear infinite; display:none; flex-shrink:0; }
@keyframes ppSpin { to { transform:rotate(360deg); } }

/* Error */
.pp-err { padding:13px 15px; background:#1a0808; border:1px solid #3d1010; border-radius:9px; color:#f87171; font-size:13px; line-height:1.5; margin-top:14px; display:none; }

/* Result */
.pp-result { margin-top:18px; background:#0a1628; border:1px solid #1e3a5f; border-radius:12px; padding:28px 24px; text-align:center; display:none; }
.pp-result-icon { width:54px; height:54px; background:#111d35; border-radius:13px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.pp-dl { display:inline-flex; align-items:center; gap:8px; padding:12px 26px; background:#2563eb; color:#fff; border:none; border-radius:9px; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; margin-top:6px; }
.pp-dl:hover { background:#1d4ed8; color:#fff; }
.pp-again { display:block; margin:12px auto 0; background:none; border:1px solid #1e1e1e; color:#4b5563; padding:8px 20px; border-radius:7px; font-size:12px; cursor:pointer; font-family:inherit; }
.pp-again:hover { border-color:#2563eb; color:#60a5fa; }

/* Loading steps */
.pp-steps { margin-top:14px; display:none; }
.pp-step { display:flex; align-items:center; gap:10px; padding:10px 13px; border-radius:8px; background:#0d0d0d; border:1px solid #1a1a1a; margin-bottom:7px; transition:all .3s; }
.pp-step.act { border-color:#2563eb; background:#0a1628; }
.pp-step.done { border-color:#10b981; background:#0a1f16; }
.pp-step-ic { width:24px; height:24px; border-radius:50%; background:#1a1a1a; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; }
.pp-step.act .pp-step-ic { background:#2563eb; }
.pp-step.done .pp-step-ic { background:#10b981; }
.pp-step-txt { font-size:12.5px; color:#4b5563; }
.pp-step.act .pp-step-txt { color:#60a5fa; font-weight:600; }
.pp-step.done .pp-step-txt { color:#34d399; }
</style>

<div id="dsPPT">

  <!-- Content Textarea -->
  <div class="pp-ta-card">
    <textarea id="ppContent" class="pp-ta" maxlength="3000"
      placeholder="Option 1: Just type your topic — AI generates everything&#10;Example: The Future of Renewable Energy&#10;&#10;— OR —&#10;&#10;Option 2: Paste your own content&#10;Slide 1: Introduction&#10;  - Your first point&#10;  - Second point&#10;Slide 2: Next Topic&#10;  - Content here"></textarea>
    <div class="pp-ta-foot">
      <span style="color:#2a3040;font-size:11px;">Tip: Type "Slide 1:" format to use Preserve mode</span>
      <span style="color:#1f2937;font-size:11px;"><span id="ppCC">0</span>/3000</span>
    </div>
  </div>

  <!-- Mode selection -->
  <div class="pp-modes">
    <div class="pp-mode on" id="ppModeAI" onclick="ppSetMode('ai')">
      <div class="pp-mode-h">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#2563eb"/></svg>
        Update with AI
      </div>
      <div class="pp-mode-d">AI rewrites &amp; enhances your content professionally</div>
    </div>
    <div class="pp-mode" id="ppModeP" onclick="ppSetMode('preserve')">
      <div class="pp-mode-h">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#4b5563" stroke-width="2"/><polyline points="14 2 14 8 20 8" stroke="#4b5563" stroke-width="2"/></svg>
        Preserve My Text
      </div>
      <div class="pp-mode-d">Cleans &amp; splits your text by Slide 1, 2, 3… headers</div>
    </div>
  </div>

  <!-- Controls -->
  <div class="pp-ctrl">

    <!-- Number of Slides -->
    <div>
      <span class="pp-lbl">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#374151" stroke-width="2"/></svg>
        Number of Slides
      </span>
      <div class="pp-sel-wrap">
        <select id="ppSlides" class="pp-sel">
          <option value="5">5 Slides</option>
          <option value="8">8 Slides</option>
          <option value="10" selected>10 Slides</option>
          <option value="12">12 Slides</option>
          <option value="15">15 Slides</option>
          <option value="20">20 Slides</option>
        </select>
      </div>
    </div>

    <!-- Design Theme -->
    <div>
      <span class="pp-lbl">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="#374151" stroke-width="2"/><path d="M12 2a10 10 0 010 20" stroke="#374151" stroke-width="2"/></svg>
        Design Theme
      </span>
      <div class="pp-sel-wrap">
        <select id="ppTheme" class="pp-sel">
          <option value="navy-pro">Navy Pro</option>
          <option value="dark-modern">Dark Modern</option>
          <option value="clean-corporate">Clean Corporate</option>
          <option value="bold-red">Bold Red</option>
          <option value="corporate-dark">Corporate Dark</option>
          <option value="clean-white">Clean White</option>
          <option value="gradient-pro">Gradient Pro</option>
          <option value="bold-impact">Bold Impact</option>
        </select>
      </div>
    </div>

    <!-- Language -->
    <div>
      <span class="pp-lbl">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="#374151" stroke-width="2"/><line x1="2" y1="12" x2="22" y2="12" stroke="#374151" stroke-width="2"/><path d="M12 2a15 15 0 010 20M12 2a15 15 0 000 20" stroke="#374151" stroke-width="2"/></svg>
        Language
      </span>
      <div class="pp-sel-wrap">
        <select id="ppLang" class="pp-sel">
          <option value="English" selected>English</option>
          <option value="Hindi">Hindi</option>
          <option value="Kannada">Kannada</option>
          <option value="Tamil">Tamil</option>
          <option value="Telugu">Telugu</option>
          <option value="Malayalam">Malayalam</option>
          <option value="Spanish">Spanish</option>
          <option value="French">French</option>
          <option value="German">German</option>
          <option value="Arabic">Arabic</option>
          <option value="Portuguese">Portuguese</option>
          <option value="Japanese">Japanese</option>
          <option value="Chinese">Chinese</option>
        </select>
      </div>
    </div>

    <!-- AI Images per slide -->
    <div>
      <span class="pp-lbl">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#374151" stroke-width="2"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="#374151" stroke-width="2"/></svg>
        AI Images per Slide
      </span>
      <div class="pp-img-row" onclick="ppToggleImg()" id="ppImgRow">
        <div class="pp-chk" id="ppChk">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <span class="pp-chk-lbl">Include images</span>
      </div>
    </div>

  </div>

  <!-- Summary bar -->
  <div class="pp-summary">
    <span class="pp-sum-txt" id="ppSumTxt">10 slides · Navy Pro · AI Mode</span>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="pp-sum-cr" id="ppCrCost">20 credits</span>
      <span class="pp-sum-have">(You have <?php echo number_format($credits); ?>cr)</span>
    </div>
  </div>

  <!-- Generate button -->
  <button id="ppGenBtn" class="pp-gen" onclick="ppGenerate()">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polygon points="5 3 19 12 5 21 5 3" fill="#fff"/></svg>
    <span id="ppGenTxt">GENERATE PRESENTATION</span>
    <div class="pp-spin" id="ppSpinner"></div>
  </button>

  <!-- Loading steps (shown during generation) -->
  <div class="pp-steps" id="ppSteps">
    <div class="pp-step" id="ppS1">
      <div class="pp-step-ic">1</div>
      <span class="pp-step-txt">Analysing your content with AI...</span>
    </div>
    <div class="pp-step" id="ppS2">
      <div class="pp-step-ic">2</div>
      <span class="pp-step-txt">Building slide structure...</span>
    </div>
    <div class="pp-step" id="ppS3">
      <div class="pp-step-ic">3</div>
      <span class="pp-step-txt">Generating presentation file...</span>
    </div>
  </div>

  <!-- Error -->
  <div class="pp-err" id="ppErr"></div>

  <!-- Result -->
  <div class="pp-result" id="ppResult">
    <div class="pp-result-icon">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#2563eb" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke="#2563eb" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12" y2="21" stroke="#2563eb" stroke-width="2" stroke-linecap="round"/></svg>
    </div>
    <p style="color:#d1d5db;font-size:16px;font-weight:800;margin:0 0 5px;" id="ppResTitle">Presentation Ready!</p>
    <p style="color:#4b5563;font-size:12.5px;margin:0 0 18px;" id="ppResSub">Your presentation has been generated.</p>
    <a href="#" id="ppDlLink" class="pp-dl" download>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/><polyline points="7 10 12 15 17 10" stroke="#fff" stroke-width="2.2"/><line x1="12" y1="15" x2="12" y2="3" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
      Download Presentation (.pptx)
    </a>
    <button class="pp-again" onclick="ppReset()">Generate Another</button>
  </div>

</div>

<script>
/* ===== DS AI PPT UI ===== */
(function(){

  /* State */
  var PP = { mode:'ai', img:true };

  /* ---- DOM ready: wire up change listeners ---- */
  document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('ppSlides').addEventListener('change', ppCalc);
    document.getElementById('ppTheme').addEventListener('change',  ppCalc);
    document.getElementById('ppLang').addEventListener('change',   ppCalc);
    document.getElementById('ppContent').addEventListener('input', function(){
      document.getElementById('ppCC').textContent = this.value.length;
    });
    ppCalc(); /* initial summary */
  });

  /* Mode switch */
  window.ppSetMode = function(m) {
    PP.mode = m;
    document.getElementById('ppModeAI').classList.toggle('on', m==='ai');
    document.getElementById('ppModeP').classList.toggle('on',  m==='preserve');
    ppCalc();
  };

  /* Image toggle */
  window.ppToggleImg = function() {
    PP.img = !PP.img;
    var chk = document.getElementById('ppChk');
    chk.classList.toggle('off', !PP.img);
    chk.innerHTML = PP.img
      ? '<svg width="10" height="10" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>'
      : '';
    ppCalc();
  };

  /* Calc summary + credit cost */
  function ppCalc() {
    var slides   = parseInt(document.getElementById('ppSlides').value) || 10;
    var themeEl  = document.getElementById('ppTheme');
    var themeTxt = themeEl.options[themeEl.selectedIndex].text.trim();
    var modeTxt  = PP.mode === 'ai' ? 'AI Mode' : 'Preserve Mode';
    var cost     = slides * 2 + (PP.img ? slides * 5 : 0);
    document.getElementById('ppSumTxt').textContent  = slides + ' slides \u00b7 ' + themeTxt + ' \u00b7 ' + modeTxt;
    document.getElementById('ppCrCost').textContent  = cost + ' credits';
  }

  /* Steps animation */
  function ppStep(n) {
    for (var i=1; i<=3; i++) {
      var el = document.getElementById('ppS'+i);
      el.className = 'pp-step' + (i < n ? ' done' : (i===n ? ' act' : ''));
      var ic = el.querySelector('.pp-step-ic');
      if (i < n)  ic.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="#fff" stroke-width="3"/></svg>';
      else        ic.textContent = i;
    }
  }

  /* ---- GENERATE ---- */
  window.ppGenerate = function() {
    var content = document.getElementById('ppContent').value.trim();
    if (!content) { ppShowErr('Please enter a topic or paste your content.'); return; }

    var slides  = parseInt(document.getElementById('ppSlides').value) || 10;
    var theme   = document.getElementById('ppTheme').value;
    var lang    = document.getElementById('ppLang').value;
    var cost    = slides * 2 + (PP.img ? slides * 5 : 0);
    var credits = <?php echo (int)$credits; ?>;

    if (credits < cost) {
      ppShowErr('Not enough credits. Need ' + cost + ', you have ' + credits + '. <a href="<?php echo esc_js(home_url('/pricing')); ?>" style="color:#60a5fa;">Buy credits</a>');
      return;
    }

    ppSetLoading(true);
    ppHideErr();
    document.getElementById('ppResult').style.display = 'none';
    document.getElementById('ppSteps').style.display  = 'block';
    ppStep(1);

    /* Call existing /ds/v1/generate-ppt endpoint from your Groq AI snippet */
    var nonce   = (window.dsAI && window.dsAI.nonce)   ? window.dsAI.nonce   : '';
    var restUrl = (window.dsAI && window.dsAI.restUrl)  ? window.dsAI.restUrl : '/wp-json/ds/v1/';

    setTimeout(function(){ ppStep(2); }, 3000);
    setTimeout(function(){ ppStep(3); }, 8000);

    fetch(restUrl + 'generate-ppt', {
      method:  'POST',
      headers: { 'Content-Type':'application/json', 'X-WP-Nonce': nonce },
      body:    JSON.stringify({
        topic:          content,
        slides:         slides,
        template:       theme,
        language:       lang,
        mode:           PP.mode,
        include_images: PP.img,
        num_slides:     slides,   /* some engine versions use this key */
      })
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status + ' — check your generate-ppt snippet is active.');
      return r.json();
    })
    .then(function(d) {
      ppSetLoading(false);
      document.getElementById('ppSteps').style.display = 'none';
      if (d.error) { ppShowErr(d.error); return; }

      /* The engine returns either a direct URL or a ppt_id to poll */
      if (d.url || d.download_url) {
        ppShowResult(d.url || d.download_url, d.filename || 'presentation.pptx', slides, theme);
        return;
      }

      /* If it returns ppt_id, hit the download-ppt endpoint */
      if (d.ppt_id) {
        var dlUrl = restUrl + 'download-ppt/' + d.ppt_id + '?_wpnonce=' + nonce;
        ppShowResult(dlUrl, (d.filename || 'presentation') + '.pptx', slides, theme);
        return;
      }

      /* Fallback: try build-pptx if ppt_data returned */
      if (d.ppt_data) {
        fetch(restUrl + 'build-pptx', {
          method:  'POST',
          headers: { 'Content-Type':'application/json', 'X-WP-Nonce': nonce },
          body:    JSON.stringify({ title: d.ppt_data.title || 'Presentation', template: theme, slides: d.ppt_data.slides })
        })
        .then(function(r2){ return r2.json(); })
        .then(function(d2){
          if (d2.url) { ppShowResult(d2.url, d2.filename || 'presentation.pptx', slides, theme); }
          else        { ppShowErr(d2.error || 'Could not build PPTX file.'); }
        })
        .catch(function(e){ ppShowErr('Build error: ' + e.message); });
        return;
      }

      ppShowErr('Unexpected response from server. Check generate-ppt snippet is working.');
    })
    .catch(function(e) {
      ppSetLoading(false);
      document.getElementById('ppSteps').style.display = 'none';
      ppShowErr('Error: ' + e.message);
    });
  };

  function ppSetLoading(on) {
    var btn  = document.getElementById('ppGenBtn');
    var txt  = document.getElementById('ppGenTxt');
    var spin = document.getElementById('ppSpinner');
    btn.disabled    = on;
    txt.textContent = on ? 'Generating...' : 'GENERATE PRESENTATION';
    spin.style.display = on ? 'block' : 'none';
  }

  function ppShowResult(url, fname, slides, theme) {
    var res = document.getElementById('ppResult');
    res.style.display = 'block';
    document.getElementById('ppResTitle').textContent = 'Presentation Ready!';
    document.getElementById('ppResSub').textContent   = slides + ' slides \u00b7 ' + theme + ' \u00b7 Generated successfully';
    var dl = document.getElementById('ppDlLink');
    dl.href = url;
    dl.setAttribute('download', fname);
    res.scrollIntoView({ behavior:'smooth', block:'center' });
  }

  function ppShowErr(msg) {
    var el = document.getElementById('ppErr');
    el.innerHTML     = msg;
    el.style.display = 'block';
  }
  function ppHideErr() {
    document.getElementById('ppErr').style.display = 'none';
  }

  window.ppReset = function() {
    document.getElementById('ppResult').style.display  = 'none';
    document.getElementById('ppSteps').style.display   = 'none';
    document.getElementById('ppContent').value = '';
    document.getElementById('ppCC').textContent = '0';
    ppHideErr();
    ppCalc();
  };

})();
</script>
    <?php
    return ob_get_clean();
}
