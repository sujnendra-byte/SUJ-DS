
(function () {
'use strict';

var _prevDspGen = window.dspGen; // whatever Preserve Mode / AI mode already does
if (!_prevDspGen) return;

var AI_URL = (window.PURL || '').replace('generate-ppt', 'preserve-ai-rearrange');

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

  window.dspLiveGenShow(prompt, hint || 10, design);
  var bannerTxt = document.getElementById('dsp-lg-banner-txt');
  if (bannerTxt) bannerTxt.innerHTML = '<b style="color:#111;">Formatting Your Content:</b> AI is organizing your exact words into slides.';

  fetch(AI_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
    body: JSON.stringify({ text: prompt, num_slides_hint: hint })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    if (!d || d.error || !d.ppt_data) {
      window.dspLiveGenHide();
      alert((d && d.message) ? d.message : 'AI formatting failed. Falling back to manual formatting.');
      return _prevDspGen.apply(_outerThis, _outerArgs);
    }
    if (d.credits_balance !== undefined) {
      window.G.credits = d.credits_balance;
      var ce = document.getElementById('ds-cr-display');
      if (ce) ce.textContent = window.G.credits.toLocaleString();
    }
    var ppt = d.ppt_data;
    function finish() {
      window.dspLiveGenHide();
      window.G.pptId = d.ppt_id || ('preserve_' + Date.now());
      window.G.pptData = ppt;
      window.G.pptTitle = ppt.title;
      if (typeof window.buildEditor === 'function') window.buildEditor(ppt);
      if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(ppt);
    }
    // Route through build-pptx-v2 so layouts render correctly in PPTX
    var bldUrl = window.DSP_BLD_URL || window.BLDURL || '/wp-json/ds/v1/build-pptx-v2';
    fetch(bldUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
      body: JSON.stringify({ slides: ppt.slides, design: design, title: ppt.title, slide_variants: {} })
    })
    .then(function(r){ return r.json(); })
    .then(function(bd){
      if (bd && bd.url) {
        // Store the pre-built URL so download button uses it directly
        window.G.preserveDownloadUrl = bd.url;
        window.G.preserveDownloadFilename = bd.filename || 'presentation.pptx';
      }
      window.dspLiveGenStream(ppt, design, finish);
    })
    .catch(function(){
      window.dspLiveGenStream(ppt, design, finish);
    });
  })
  .catch(function () {
    window.dspLiveGenHide();
    alert('Network error while formatting with AI. Falling back to manual formatting.');
    return _prevDspGen.apply(_outerThis, _outerArgs);
  });
};

})();
