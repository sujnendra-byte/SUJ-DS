
(function () {
  'use strict';

  /* ──────────────────────────────────────────────────────────────────────
     DS PPT — Background autosave + no-op save guard

     Two problems this solves:

     1. Saving an unchanged deck. Opening a saved presentation and pressing
        Save re-encoded every image and pushed several MB back to the server
        for a deck that had not changed by a single character.

     2. Saving blocked the user. The Save button raises a full-screen
        "Saving..." overlay and the user waits behind it.

     After this snippet: the deck is fingerprinted whenever it is loaded or
     saved. A Save with no changes returns instantly and sends nothing. A
     real edit is written back quietly in the background a couple of seconds
     later, with a small status chip in the corner instead of a modal.
     ────────────────────────────────────────────────────────────────────── */

  var SAVED = null;      // fingerprint of what the server currently holds
  var timer = null;
  var saving = false;
  var queued = false;
  var watchdog = null;

  /* ── status chip (non-blocking) ─────────────────────────────────────── */

  var chipEl = null;
  var chipHide = null;

  function chip(text, tone) {
    if (!document.body) return;
    if (!chipEl) {
      chipEl = document.createElement('div');
      chipEl.id = 'ds-autosave-chip';
      chipEl.style.cssText =
        'position:fixed;right:18px;bottom:18px;z-index:2147483000;' +
        'padding:8px 14px;border-radius:999px;font:600 12px/1 -apple-system,' +
        'BlinkMacSystemFont,"Segoe UI",sans-serif;color:#fff;background:#111827;' +
        'border:1px solid #374151;box-shadow:0 6px 20px rgba(0,0,0,.35);' +
        'opacity:0;transition:opacity .2s;pointer-events:none;';
      document.body.appendChild(chipEl);
    }
    chipEl.textContent = text;
    chipEl.style.background = tone === 'error' ? '#7f1d1d' : '#111827';
    chipEl.style.opacity = '1';
    clearTimeout(chipHide);
    if (tone !== 'busy') {
      chipHide = setTimeout(function () {
        if (chipEl) chipEl.style.opacity = '0';
      }, 2200);
    }
  }

  /* ── fingerprint ────────────────────────────────────────────────────── */

  function slides() {
    if (window.S && window.S.data && window.S.data.slides && window.S.data.slides.length) {
      return window.S.data.slides;
    }
    if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides.length) {
      return window.G.pptData.slides;
    }
    return null;
  }

  function safe(v) {
    try { return JSON.stringify(v == null ? '' : v); } catch (e) { return ''; }
  }

  /* Images are megabytes each, so they are represented by their length plus
     their last 64 characters — enough to notice a swap, cheap to compute. */
  function imgKey(s) {
    var d = (s && s.image_data) || '';
    if (typeof d !== 'string' || !d) return '0';
    return d.length + ':' + d.slice(-64);
  }

  /* PNG pictures / copied picture frames: place, size, settings and the tail of the picture data */
  function pngKey(s) {
    var a = (s && s.png_objects) || [];
    if (!a.length) return '';
    var out = [];
    for (var i = 0; i < a.length; i++) {
      var b = a[i] || {}, src = typeof b.src === 'string' ? b.src : '';
      out.push([b.x, b.y, b.w, b.h, b.rr, b.op, b.zm, safe(b.pos), src.length, src.slice(-32)].join(','));
    }
    return out.join(';');
  }

  function fingerprint() {
    var sl = slides();
    if (!sl) return null;
    var design = document.getElementById('dsp-design');
    var parts = [
      (window.G && window.G.pptTitle) || '',
      design ? design.value : '',
      sl.length
    ];
    try { parts.push(sessionStorage.getItem('dspVPC') || ''); } catch (e) { parts.push(''); }
    for (var i = 0; i < sl.length; i++) {
      var s = sl[i] || {};
      parts.push([
        s.type || '',
        s.title || '',
        s.subtitle || '',
        safe(s.content),
        safe(s.icons || s.icon),
        safe(s.image_position),
        safe(s.dsel && s.dsel.L), pngKey(s),   /* DS Template Element Editor: element edits, added elements, PNG pictures and copied picture frames count as changes */
        safe(s.ov), (i === 0 ? safe(window.dsDeckStyleGet && window.dsDeckStyleGet()) : ''),   /* DS Deck Style: deck style + per-object colours / sizes count as changes */
        s.image_market_id || '',
        imgKey(s)
      ].join(''));
    }
    return parts.join('');
  }

  function markClean() {
    var f = fingerprint();
    if (f !== null) SAVED = f;
  }

  function isDirty() {
    var f = fingerprint();
    return f !== null && SAVED !== null && f !== SAVED;
  }

  /* ── watch save-ppt / load-ppt without disturbing the other wrappers ─── */

  var _of = window.fetch;
  if (_of) {
    window.fetch = function (url, opts) {
      var us = typeof url === 'string' ? url : (url && url.url) || '';
      var p = _of.apply(this, arguments);

      if (us.indexOf('/ds/v1/load-ppt') !== -1) {
        /* Give the editor a moment to populate S.data / G.pptData, then
           treat what is on screen as exactly what the server holds. */
        p.then(function () {
          setTimeout(markClean, 2500);
          setTimeout(markClean, 6000);
        }).catch(function () {});
      }

      if (us.indexOf('/ds/v1/save-ppt') !== -1) {
        var snap = fingerprint();
        p.then(function (r) { return r.clone().json(); })
         .then(function (d) {
           if (d && d.success) {
             if (snap !== null) SAVED = snap;
             chip('Saved');
           } else {
             chip('Save failed', 'error');
           }
           finishSave();
         })
         .catch(function () { chip('Save failed', 'error'); finishSave(); });
      }

      return p;
    };
  }

  function finishSave() {
    saving = false;
    clearTimeout(watchdog);
    if (queued) { queued = false; schedule(); }
  }

  /* ── background autosave ────────────────────────────────────────────── */

  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(run, 2000);
  }

  function run() {
    if (!window.G || !window.G.pptId) return;     // never auto-create a draft
    if (saving) { queued = true; return; }
    if (!isDirty()) return;
    if (typeof window.dspSaveDraft !== 'function') return;
    saving = true;
    chip('Saving…', 'busy');
    /* Called directly, not via the button — the blocking overlay in the
       layout panel only listens for clicks on #dsp-savebtn. */
    try { window.dspSaveDraft(); } catch (e) { finishSave(); }
    clearTimeout(watchdog);
    watchdog = setTimeout(function () {
      if (saving) { saving = false; chip('Save failed', 'error'); }
    }, 90000);
  }

  setInterval(function () {
    if (!window.G || !window.G.pptId) return;
    if (SAVED === null) { markClean(); return; }
    if (saving) return;
    if (isDirty()) schedule();
  }, 3000);

  /* ── manual Save: do nothing when nothing changed ───────────────────── */

  function installSaveGuard() {
    if (typeof window.dspSaveDraft !== 'function') return;
    if (window.dspSaveDraft.__dsNoopGuard) return;
    var orig = window.dspSaveDraft;
    var guarded = function () {
      if (window.G && window.G.pptId && SAVED !== null && !isDirty()) {
        chip('No changes to save');
        if (typeof window.dspToast === 'function') {
          window.dspToast('Already saved — no changes', 'success');
        }
        return;
      }
      return orig.apply(this, arguments);
    };
    guarded.__dsNoopGuard = true;
    window.dspSaveDraft = guarded;
  }

  setInterval(installSaveGuard, 800);
  installSaveGuard();

  /* Don't lose an edit that is still pending when the tab closes. */
  window.addEventListener('beforeunload', function (e) {
    if (window.G && window.G.pptId && !saving && isDirty()) {
      e.preventDefault();
      e.returnValue = '';
      return '';
    }
  });
})();
