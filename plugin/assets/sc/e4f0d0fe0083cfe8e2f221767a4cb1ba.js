
(function(){
  'use strict';
  if (window.__dsSharePics) return; window.__dsSharePics = 1;
  var CFG = window.DS_SHAREPICS || {}, busy = false, hush = false;
  var OUT_W = 1600, QUALITY = 0.86, LIMIT_MS = 150000;
  var rawFetch = window.fetch;

  function nonce(){ return CFG.nonce || window.DSP_NONCE || ((window.wpApiSettings || {}).nonce) || ''; }
  function wait(ms){ return new Promise(function(r){ setTimeout(r, ms); }); }
  function deckTitle(){
    try { return String((window.G && G.pptTitle) || (window.S && S.data && S.data.title) || (window.G && G.pptData && G.pptData.title) || '').slice(0, 160); } catch (e) { return ''; }
  }
  function post(step, body, isForm){
    var o = {method: 'POST', credentials: 'same-origin', headers: {'X-WP-Nonce': nonce()}};
    if (isForm) o.body = body; else { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
    return rawFetch.call(window, CFG.rest + step, o).then(function(r){ return r.json(); }).then(function(d){
      if (!d || !d.success) throw new Error((d && (d.error || d.message)) || 'The slide pictures could not be saved.');
      return d;
    });
  }
  /* the download snippet tells other snippets "a file was downloaded" - not true here */
  window.addEventListener('ds:ppt-exported', function(e){ if (hush) e.stopImmediatePropagation(); }, true);

  function shrink(blob){
    if (typeof createImageBitmap !== 'function') return Promise.resolve(blob);
    return createImageBitmap(blob).then(function(bm){
      if (bm.width <= OUT_W && blob.size < 420000) { if (bm.close) bm.close(); return blob; }
      var w = Math.min(OUT_W, bm.width), h = Math.round(bm.height * w / bm.width);
      var c = document.createElement('canvas'); c.width = w; c.height = h;
      var x = c.getContext('2d'); x.imageSmoothingQuality = 'high'; x.drawImage(bm, 0, 0, w, h);
      if (bm.close) bm.close();
      return new Promise(function(res){ c.toBlob(function(b){ c.width = c.height = 0; res(b && b.size < blob.size ? b : blob); }, 'image/jpeg', QUALITY); });
    }).catch(function(){ return blob; });
  }
  /* the wording on the progress card while the slides are photographed */
  function relabel(){
    var m = document.querySelector('#dsx-prog .dsx-m'); if (!m) return;
    var t = m.textContent || '', u = t.replace('Preparing your download', 'Preparing your share link').replace('Finishing your file', 'Saving the slide pictures');
    if (u !== t) m.textContent = u;
  }
  async function photograph(){
    var X = window.DSX;
    if (!X || typeof X.exportAs !== 'function') throw new Error('The picture maker is not on this page.');
    if (!document.getElementById('dsp-ppt-main-slide') || typeof window.dspSelectThumb !== 'function') throw new Error('The editor is not open.');
    var got = null, save0 = X.save, toast0 = window.dspToast, fp = window.DSFP, fpx = fp ? fp.exported : undefined;
    var mySave = function(blob, name){ got = {blob: blob, name: String(name || '')}; };
    var myToast = function(msg){ if (/download/i.test(String(msg || ''))) return; if (typeof toast0 === 'function') return toast0.apply(this, arguments); };
    var mo = null;
    try { mo = new MutationObserver(relabel); mo.observe(document.body, {childList: true, subtree: true, characterData: true}); } catch (e) {}
    X.save = mySave; window.dspToast = myToast; hush = true;
    try { await X.exportAs('jpg'); }
    finally {
      if (X.save === mySave) X.save = save0;
      if (window.dspToast === myToast) window.dspToast = toast0;
      hush = false; if (mo) mo.disconnect();
      if (fp && fpx !== undefined) fp.exported = fpx;
    }
    if (!got || !got.blob) throw new Error('No slide pictures were made.');
    var blobs = [];
    if (/\.zip$/i.test(got.name)) {
      if (!window.JSZip) throw new Error('Could not open the slide pictures.');
      var z = await window.JSZip.loadAsync(got.blob), names = Object.keys(z.files).filter(function(k){ return !z.files[k].dir && /\.jpe?g$/i.test(k); }).sort();
      for (var i = 0; i < names.length; i++) blobs.push(new Blob([await z.files[names[i]].async('arraybuffer')], {type: 'image/jpeg'}));
    } else blobs.push(got.blob);
    if (!blobs.length) throw new Error('No slide pictures were made.');
    return blobs;
  }
  async function makePics(token){
    var blobs = await photograph(), n = blobs.length;
    await post('begin', {token: token, count: n, title: deckTitle()});
    for (var i = 0; i < n; i++) {
      var b = await shrink(blobs[i]); blobs[i] = null;
      var fd = new FormData(); fd.append('token', token); fd.append('i', String(i)); fd.append('file', b, 'slide-' + (i + 1) + '.jpg');
      try { await post('put', fd, true); }
      catch (e1) { await wait(900); await post('put', fd, true); }      /* one more try */
    }
    await post('done', {token: token});
  }

  window.fetch = function(input, init){
    var url = '';
    try { url = typeof input === 'string' ? input : ((input && input.url) || ''); } catch (e) {}
    var p = rawFetch.apply(this, arguments);
    if (busy || !CFG.rest || url.indexOf('/ds/v1/create-share-link') === -1) return p;
    return p.then(function(res){
      if (!res || !res.ok || typeof res.clone !== 'function') return res;
      return res.clone().json().then(function(d){
        if (!d || !d.success || !d.share_token || !/^[A-Za-z0-9]{10,40}$/.test(String(d.share_token))) return res;
        busy = true;
        var work = makePics(String(d.share_token));
        var cap = new Promise(function(r){ setTimeout(r, LIMIT_MS); });
        return Promise.race([work, cap]).catch(function(err){ if (window.console) console.warn('[DS share pictures]', err); })
          .then(function(){ busy = false; return res; });
      }, function(){ return res; });
    });
  };
})();
