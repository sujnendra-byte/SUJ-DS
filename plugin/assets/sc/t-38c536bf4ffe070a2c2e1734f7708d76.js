window.DSP_TPL_CREDIT_COST  = 5;
(function () {
'use strict';

function tplNonce() { return window.DSP_NONCE || window.DSP_TPL_NONCE || ''; }
function tplEsc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

var GAL = { idx: -1, open: false };
var _pickedCache = {};

// Clear picked cache when new presentation is generated
document.addEventListener('dsp-slides-ready', function() {
    _pickedCache = {};
    // Also clear _real_template from all slides
    if (window.S && window.S.data && window.S.data.slides) {
        window.S.data.slides.forEach(function(s) {
            if (s) delete s._real_template;
        });
    }
    tplRefreshButtonBadge();
});

// Also clear when S.data changes (new generation)
var _ds21LastSlideCount = 0;
setInterval(function() {
    if (!window.S || !window.S.data || !window.S.data.slides) return;
    var count = window.S.data.slides.length;
    if (count !== _ds21LastSlideCount) {
        _ds21LastSlideCount = count;
        // New slides loaded — clear all template selections
        _pickedCache = {};
        if (window.S.data.slides) {
            window.S.data.slides.forEach(function(s) {
                if (s) delete s._real_template;
            });
        }
        tplRefreshButtonBadge();
    }
}, 1000);

function tplInjectButton() {
  var bar = document.getElementById('dsp-ppt-bottom-bar');
  if (bar && !document.getElementById('ds-tpl-pick-btn')) {
    var btn = document.createElement('button');
    btn.id = 'ds-tpl-pick-btn';
    btn.innerHTML = 'Pick a Design';
    btn.onclick = window.dsTplGalleryOpen;
    bar.appendChild(btn);
  }
}

function tplRefreshButtonBadge() {
  if (!window.S || !window.S.data || !window.S.data.slides) return;
  var n = window.S.data.slides.filter(function (s) { return s && s._real_template; }).length;

  var btn = document.getElementById('ds-tpl-pick-btn');
  if (btn) {
    var existing = btn.querySelector('.ds-tpl-badge');
    if (existing) existing.remove();
    if (_pickedCache[window.S.idx] || (window.S.data.slides[window.S.idx] && window.S.data.slides[window.S.idx]._real_template)) {
      var b = document.createElement('span');
      b.className = 'ds-tpl-badge';
      b.textContent = '✓';
      btn.appendChild(b);
    }
  }
}

/* ── Apply the picked design straight onto the canvas + thumbnail ── */
function tplImgWrap(idx, url, rendered) {
  var esc = tplEsc(url);
  var banner = rendered ? '' :
    '<div style="position:absolute;top:0;left:0;right:0;background:rgba(0,0,0,.8);color:#fbbf24;font-size:.6em;font-weight:700;padding:8px 10px;text-align:center;line-height:1.4;">⚠ Static preview shown (live render unavailable on this server) — your real content IS saved in the downloaded file</div>';
  return '<div class="ds-tpl-applied" data-ds-tpl-url="' + esc + '" style="position:relative;width:100%;height:100%;overflow:hidden;">'
    + banner
    + '<img src="' + esc + '" style="width:100%;height:100%;object-fit:contain;display:block;background:#000;">'
    + '<button onclick="window.dsTplRemoveDesign(' + idx + ')" style="position:absolute;' + (rendered ? 'top:8px;' : 'top:38px;') + 'right:8px;background:rgba(0,0,0,.75);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;font-family:inherit;">✕ Remove design</button>'
    + '</div>';
}

function tplReassertCanvas() {
      /* DS-FIX — picking a design from the marketplace used to paint the
         template's preview image over the main editor canvas and keep it
         there. The pick is still recorded on the slide (_real_template) and
         still drives the download; it is now shown only inside the
         "Pick a Design" popup, and the editor keeps its own live slide. */
      return;
  var main = document.getElementById('dsp-ppt-main-slide');
  if (!main || !window.S || !window.S.data || !window.S.data.slides) return;
  var idx = window.S.idx;
  var slide = window.S.data.slides[idx];
  var rt = slide && slide._real_template;
  if (!rt || !rt.preview_url) return;
  var applied = main.querySelector('.ds-tpl-applied');
  if (applied && applied.getAttribute('data-ds-tpl-url') === rt.preview_url) return;
  main.innerHTML = tplImgWrap(idx, rt.preview_url, !!rt.rendered);
}

function tplReassertThumb(idx) {
      /* DS-FIX — same for the editor's thumbnail rail: the template preview
         no longer replaces the real slide thumbnail. */
      return;
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (!thumbs || !window.S || !window.S.data || !window.S.data.slides) return;
  var slide = window.S.data.slides[idx];
  var rt = slide && slide._real_template;
  var el = thumbs.children[idx];
  if (!el || !rt || !rt.preview_url) return;
  if (el.getAttribute('data-ds-tpl-url') === rt.preview_url) return;
  el.setAttribute('data-ds-tpl-url', rt.preview_url);
  el.innerHTML = '<img src="' + tplEsc(rt.preview_url) + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
}

function tplReassertAllThumbs() {
  if (!window.S || !window.S.data || !window.S.data.slides) return;
  window.S.data.slides.forEach(function (s, i) { if (s && s._real_template) tplReassertThumb(i); });
}

function tplEnsureCanvasObserver() {
      /* DS-FIX — the observer existed only to re-paint the template preview
         over the canvas after every re-render. Nothing to re-assert now. */
      return;
  var main = document.getElementById('dsp-ppt-main-slide');
  if (!main || main._dsTplObserved) return;
  main._dsTplObserved = true;
  var obs = new MutationObserver(function () { tplReassertCanvas(); });
  obs.observe(main, { childList: true });
}

window.dsTplRemoveDesign = function (idx) {
  if (!window.S || !window.S.data || !window.S.data.slides[idx]) return;
  delete window.S.data.slides[idx]._real_template;
  delete _pickedCache[idx];
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (thumbs && thumbs.children[idx]) thumbs.children[idx].removeAttribute('data-ds-tpl-url');
  var vid = (window.S.choices && window.S.choices['slide_' + idx]) || 'v1';
  if (window.dspVPPick) window.dspVPPick(idx, vid);
  tplRefreshButtonBadge();
  renderSlideSidebar();
  if (window.dspToast) window.dspToast('Reverted to the editable layout for this slide.', 'success');
};

/* ── Unified modal: slide sidebar + gallery + combined download ── */

function tplCloneRealThumbInto(targetEl, slideIdx) {
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (!thumbs || !thumbs.children[slideIdx]) return false;
  var src = thumbs.children[slideIdx];

  var clone = src.cloneNode(true);
  clone.removeAttribute('id');
  Array.prototype.forEach.call(clone.querySelectorAll('[id]'), function (el) { el.removeAttribute('id'); });
  clone.removeAttribute('onclick');
  clone.classList.remove('active', 'selected', 'dsp-thumb-active');

  targetEl.innerHTML = '';

      /* DS-FIX — the clone used to be pinned to the top-left and scaled by
         width alone. The editor's thumbnail box is 128x100 while this slot
         is 16:9, so the thumbnail's own letterbox bar showed as a black
         strip and the slide was cut off at the bottom. Scale to COVER and
         centre instead: the bars are cropped and the card shows the whole
         slide, edge to edge. */
      var wrap = document.createElement('div');
      wrap.style.cssText = 'position:absolute;inset:0;overflow:hidden;';
      wrap.appendChild(clone);
      targetEl.appendChild(wrap);

      function place() {
        /* DS-FIX — the editor's thumbnail ITEM is 128x100, but the real slide
           is the .dsp-thumb-box inside it: 128x72, sitting 29px down, with the
           slide-number label occupying the strip above. Centring the whole
           item therefore left that label strip visible as a black band and cut
           the bottom off the slide. Measure and align on the slide box. */
        var box = src.querySelector('.dsp-thumb-box') || src;
        var itemRect = src.getBoundingClientRect();
        var boxRect = box.getBoundingClientRect();
        var dstW = targetEl.clientWidth;
        var dstH = targetEl.clientHeight;
        if (!(boxRect.width > 0 && boxRect.height > 0 && dstW > 0 && dstH > 0)) return false;
        var offX = boxRect.left - itemRect.left;
        var offY = boxRect.top - itemRect.top;
        var scale = Math.max(dstW / boxRect.width, dstH / boxRect.height);
        clone.style.position = 'absolute';
        clone.style.width = itemRect.width + 'px';
        clone.style.height = itemRect.height + 'px';
        clone.style.transformOrigin = '0 0';
        clone.style.transform = 'scale(' + scale + ')';
        /* Put the slide box's centre on the slot's centre. */
        clone.style.left = (dstW / 2 - (offX + boxRect.width / 2) * scale) + 'px';
        clone.style.top = (dstH / 2 - (offY + boxRect.height / 2) * scale) + 'px';
        clone.style.pointerEvents = 'none';
        return true;
      }
      /* DS-FIX — the slot is measured before the sidebar's scrollbar appears,
         which made the first measurement ~20px too wide and over-scaled the
         thumbnail. Re-place on every size change of the slot instead of
         trusting one early reading. */
      place();
      if (window.ResizeObserver) {
        var _ro = new ResizeObserver(function () { place(); });
        _ro.observe(targetEl);
        targetEl.__dsThumbRO = _ro;
      } else {
        setTimeout(place, 80);
        setTimeout(place, 300);
        setTimeout(place, 900);
      }

      return true;
    }

function renderSlideSidebar() {
  var sb = document.getElementById('ds-tpl-slide-sidebar');
  if (!sb || !window.S || !window.S.data || !window.S.data.slides) return;
  /* DS-FIX — rebuilding the list reset it to the top, so picking a design
         for a slide further down snapped the sidebar back to slide 1.
         Remember where the user was and put them back at the end. */
      var _dsKeepScroll = sb.scrollTop;
      sb.innerHTML = '<div class="ds-tpl-sb-label">Slides</div>';
  window.S.data.slides.forEach(function (s, i) {
    var rt = s && s._real_template;
    var card = document.createElement('div');
    card.className = 'ds-tpl-slide-thumb' + (i === GAL.idx ? ' active' : '');
    card.innerHTML =
      '<div class="ds-tpl-sb-num">' + (i + 1) + '</div>' +
      '<div class="ds-tpl-sb-img"></div>' +
      (rt ? '<div class="ds-tpl-sb-badge">✓ Selected</div>' : '');
    card.onclick = function () { selectSlideInModal(i); };
    sb.appendChild(card);

    // Add remove button if this slide has a selected design
    if (rt) {
      var removeBtn = document.createElement('button');
      removeBtn.textContent = '✕ Remove design';
      removeBtn.style.cssText = 'width:100%;margin-top:4px;padding:4px 8px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600;';
      removeBtn.onclick = (function(slideIdx) {
        return function(e) {
          e.stopPropagation();
          window.dsTplGalleryRemove(slideIdx);
        };
      })(i);
      card.appendChild(removeBtn);
    }

    var imgSlot = card.querySelector('.ds-tpl-sb-img');
    if (rt && rt.preview_url) {
      imgSlot.innerHTML = '<img src="' + tplEsc(rt.preview_url) + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
    } else {
      tplCloneRealThumbInto(imgSlot, i);
        }
      });

      /* DS-FIX — put the sidebar back where it was before the rebuild. */
      sb.scrollTop = _dsKeepScroll;
    }

function selectSlideInModal(idx) {
  GAL.idx = idx;
  renderSlideSidebar();
  dsTplGalleryLoad(idx);
}

window.dsTplGalleryOpen = function () {
  if (!window.S || !window.S.data || !window.S.data.slides || window.S.idx < 0) return;
  GAL.open = true;
  document.getElementById('ds-tpl-gallery-overlay').classList.add('open');
  document.getElementById('ds-tpl-combined-status').textContent = '';
  selectSlideInModal(window.S.idx);
};

window.dsTplGalleryClose = function () {
  GAL.open = false;
  document.getElementById('ds-tpl-gallery-overlay').classList.remove('open');
};

function dsTplGalleryLoad(idx) {
  var grid = document.getElementById('ds-tpl-gallery-grid');
  var sub  = document.getElementById('ds-tpl-gallery-sub');
  if (!grid) return;
  grid.innerHTML = '<div id="ds-tpl-gallery-empty">Looking for matching templates…</div>';

  var slide = window.S.data.slides[idx];
  if (sub) sub.textContent = 'Pick a design for slide ' + (idx + 1) + (slide.title ? ' — ' + slide.title : '');

  var costNumEl = document.getElementById('ds-tpl-gallery-cost-num');
  var costPluralEl = document.getElementById('ds-tpl-gallery-cost-plural');
  var tplCost = (typeof window.DSP_TPL_CREDIT_COST === 'number') ? window.DSP_TPL_CREDIT_COST : 1;
  if (costNumEl) costNumEl.textContent = tplCost;
  if (costPluralEl) costPluralEl.textContent = (tplCost === 1) ? '' : 's';

  fetch(window.DSP_TPL_MATCH_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': tplNonce() },
    body: JSON.stringify({ slide: { type: slide.type, title: slide.title, subtitle: slide.subtitle, content: slide.content } })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    var templates = (d && d.success && d.templates) ? d.templates : [];
    if (!GAL.open || GAL.idx !== idx) return;
    if (!templates.length) {
      grid.innerHTML = '<div id="ds-tpl-gallery-empty">No real templates tagged for this slide\'s shape yet.<br>Tag a few in wp-admin (per the AI Studio matching box) to see them here.</div>';
      return;
    }
    grid.innerHTML = '';
    var _tplQueue = templates.slice();
var _tplConcurrent = 0;
var _tplMaxConcurrent = 2;
function _tplRunNext() {
  while (_tplConcurrent < _tplMaxConcurrent && _tplQueue.length > 0) {
    var t = _tplQueue.shift();
    _tplConcurrent++;
    var _origFetchRef = window.fetch;
    dsTplRenderSkeleton(grid, idx, t);
    (function() {
      var checkDone = setInterval(function() {
        var loading = grid.querySelectorAll('.ds-tpl-loading').length;
        var total = grid.querySelectorAll('.ds-tpl-card').length;
        if (loading < _tplConcurrent || total === 0) {
          clearInterval(checkDone);
          _tplConcurrent = Math.max(0, _tplConcurrent - 1);
          _tplRunNext();
        }
      }, 500);
    })();
  }
}
_tplRunNext();
  })
  .catch(function () {
    grid.innerHTML = '<div id="ds-tpl-gallery-empty">Could not load matches. Please try again.</div>';
  });
}

function dsTplRenderSkeleton(grid, idx, t) {
  var card = document.createElement('div');
  card.className = 'ds-tpl-card ds-tpl-loading';
  card.innerHTML =
      '<div class="ds-tpl-card-img"><div class="ds-tpl-shimmer"></div></div>' +
      '<div class="ds-tpl-card-lbl">' + tplEsc(t.title || 'Template') + '</div>' +
      '<div class="ds-tpl-card-sub">✨ Rendering your content…</div>';
  grid.appendChild(card);

  var slide = window.S.data.slides[idx];

  function dropCard() {
    if (card.parentNode) card.parentNode.removeChild(card);
    if (!grid.querySelector('.ds-tpl-card')) {
      grid.innerHTML = '<div id="ds-tpl-gallery-empty">None of the matched templates could be rendered for this slide.</div>';
    }
  }

  fetch(window.DSP_TPL_RENDER_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': tplNonce() },
    body: JSON.stringify({ template_id: t.id, slide: { title: slide.title, subtitle: slide.subtitle, content: slide.content } })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    if (!GAL.open || GAL.idx !== idx) return;

    var img = card.querySelector('.ds-tpl-card-img');
    var subEl = card.querySelector('.ds-tpl-card-sub');
    card.classList.remove('ds-tpl-loading');

    if (d && d.success) {
      img.innerHTML = d.preview_url ? '<img src="' + tplEsc(d.preview_url) + '">' : '🏆';
      subEl.textContent = d.rendered
        ? '✓ Click to apply — your content, live preview'
        : '✓ Click to apply (no live preview here — content is still correctly filled)';
      card.onclick = function () { window.dsTplGalleryPick(idx, t.id, d.download_url, d.preview_url, d.rendered); };
    } else {
  var thumb = t.thumb || '';
  img.innerHTML = thumb ? '<img src="' + tplEsc(thumb) + '">' : '🏆';
  subEl.textContent = '✓ Click to apply (preview unavailable — content will still be filled)';
  card.onclick = (function(tid, tthumb) { return function () { window.dsTplGalleryPick(idx, tid, 'pending:' + tid, tthumb, false); }; })(t.id, thumb);
}
  })
  .catch(function () {
    if (!GAL.open || GAL.idx !== idx) return;
    var img = card.querySelector('.ds-tpl-card-img');
    var subEl = card.querySelector('.ds-tpl-card-sub');
    card.classList.remove('ds-tpl-loading');
    var thumb = t.thumb || '';
    img.innerHTML = thumb ? '<img src="' + tplEsc(thumb) + '">' : '🏆';
    subEl.textContent = '✓ Click to apply (preview unavailable)';
    card.onclick = function () { window.dsTplGalleryPick(idx, t.id, 'pending:' + t.id, thumb, false); };
  });
}

window.dsTplGalleryPick = function (idx, templateId, downloadUrl, previewUrl, rendered) {
  if (!window.S || !window.S.data || !window.S.data.slides[idx]) return;
  window.S.data.slides[idx]._real_template = { id: templateId, download_url: downloadUrl, preview_url: previewUrl, rendered: !!rendered };
  _pickedCache[idx] = window.S.data.slides[idx]._real_template;
  tplReassertCanvas();
  tplReassertThumb(idx);
  tplRefreshButtonBadge();
  renderSlideSidebar();
  if (window.dspToast) {
    window.dspToast(
      rendered ? 'Design applied to slide ' + (idx + 1) + '!' : 'Design applied to slide ' + (idx + 1) + ' (no live preview here).',
      'success', 4000
    );
  }
};

// ── Remove / restore original design ─────────────────────────────────────────
window.dsTplGalleryRemove = function (idx) {
  if (!window.S || !window.S.data || !window.S.data.slides[idx]) return;

  // Store original thumb URL before deleting
  var origThumbUrl = null;
  var thumbsEl = document.getElementById('dsp-ppt-thumbs');
  if (thumbsEl && thumbsEl.children[idx]) {
    var origImg = thumbsEl.children[idx].querySelector('img');
    if (origImg) origThumbUrl = origImg.src;
  }

  delete window.S.data.slides[idx]._real_template;
  delete _pickedCache[idx];
  tplReassertCanvas();
  tplReassertThumb(idx);
  tplRefreshButtonBadge();
  renderSlideSidebar();

  // Force sidebar thumbnail back to original
  setTimeout(function() {
    var sb = document.getElementById('ds-tpl-slide-sidebar');
    if (!sb) return;
    var cards = sb.querySelectorAll('.ds-tpl-slide-thumb');
    if (!cards[idx]) return;
    var imgSlot = cards[idx].querySelector('.ds-tpl-sb-img');
    if (!imgSlot) return;
    imgSlot.innerHTML = '';

    if (origThumbUrl) {
      // Use original thumbnail URL directly
      imgSlot.innerHTML = '<img src="' + origThumbUrl + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
    } else {
      tplCloneRealThumbInto(imgSlot, idx);
    }
  }, 150);

  if (window.dspToast) {
    window.dspToast('Design removed — original slide restored.', 'info', 3000);
  }
};

window.dsTplDownloadCombined = function () {
  if (!window.S || !window.S.data || !window.S.data.slides) return;
  var slides = window.S.data.slides.map(function (s, si) {
    var clean = {};
    for (var k in s) { if (k !== '_real_template') clean[k] = s[k]; }
    var vid = (window.S.choices || {})['slide_'+si] || 'v1';
    var cnt = (clean.content || []).length;
    if(clean.type==='bullet'&&cnt===5) clean.type='five_point';
    else if(clean.type==='bullet'&&cnt===6) clean.type='six_point';
    else if(clean.type==='bullet'&&cnt===7) clean.type='seven_point';
    return clean;
  });
  var realMap = {};
  window.S.data.slides.forEach(function (s, i) {
    if (s && s._real_template && s._real_template.id) realMap[i] = s._real_template.download_url || ('pending:' + s._real_template.id);
  });

  var status = document.getElementById('ds-tpl-combined-status');
  if (status) status.textContent = 'Building your full deck (' + slides.length + ' slide(s))…';

  // Same full-screen "Downloading PPTX..." overlay the regular download
  // button uses (Snippet 12B's window.dspDlPopup) -- shown the instant the
  // button is clicked, same as that flow does.
  if (window.dspDlPopup) window.dspDlPopup('downloading');

  function closeDlPopup() {
    var old = document.getElementById('dsp-dl-popup');
    if (old && old.parentNode) old.parentNode.removeChild(old);
  }

  var designEl = document.getElementById('dsp-design');
  var layoutEl = document.getElementById('dsp-layout');

  fetch(window.DSP_TPL_COMBINE_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': tplNonce() },
    body: JSON.stringify({
      slides: slides,
      slide_variants: window.S.choices || {},
      template: designEl ? designEl.value : 'navy-pro',
      layout: layoutEl ? layoutEl.value : '16:9',
		font_family: (document.getElementById('dsp-font') ? document.getElementById('dsp-font').value : ''),
      title: (window.G && window.G.pptTitle) || 'Presentation',
      real_template_map: realMap
    })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    if (d && d.success) {
      if (status) status.textContent = '✓ Built ' + d.slide_count + ' of ' + d.requested_count + ' slide(s) (' + d.real_count + ' real, ' + d.coded_count + ' coded). Downloading…';
      var a = document.createElement('a');
      a.href = d.download_url; a.download = ''; a.style.display = 'none';
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
      if (window.dspDlPopup) window.dspDlPopup('done'); else closeDlPopup();
    } else {
      if (status) status.textContent = (d && d.error) || 'Could not combine the files.';
      closeDlPopup();
    }
  })
  .catch(function () {
    if (status) status.textContent = 'Network error — please try again.';
    closeDlPopup();
  });
};

setInterval(function () {
  if (!document.getElementById('dsp-vp-panel') && !document.getElementById('dsp-editor-fullscreen')) return;
  tplInjectButton();
  tplRefreshButtonBadge();
  tplEnsureCanvasObserver();
  tplReassertCanvas();
  tplReassertAllThumbs();
}, 400);

})();
