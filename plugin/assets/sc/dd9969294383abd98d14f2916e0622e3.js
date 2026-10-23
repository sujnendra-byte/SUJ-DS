
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
