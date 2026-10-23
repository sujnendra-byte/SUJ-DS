
(function(){
  'use strict';
  if (window.__dsImgOffload) return; window.__dsImgOffload = true;
  if (!window.fetch || !window.crypto || !crypto.subtle || !window.TextEncoder) return;

  var inner = window.fetch;                  // limiter + image shrink (snippet 127)
  var known = Object.create(null);           // sha1 -> true (server has it)
  var hashCache = new Map();                 // data URI -> sha1 (per page)
  var MIN_LEN = 1000;

  function urlOf(u){ return typeof u === 'string' ? u : (u && u.url) || ''; }
  function isData(v){ return typeof v === 'string' && v.length > MIN_LEN && v.indexOf('data:image/') === 0; }

  function sha1(str){
    if (hashCache.has(str)) return Promise.resolve(hashCache.get(str));
    return crypto.subtle.digest('SHA-1', new TextEncoder().encode(str)).then(function(buf){
      var a = new Uint8Array(buf), h = '';
      for (var i = 0; i < a.length; i++) h += (a[i] < 16 ? '0' : '') + a[i].toString(16);
      hashCache.set(str, h);
      return h;
    });
  }

  function restBase(u){ var i = u.indexOf('/ds/v1/'); return i === -1 ? '/wp-json/ds/v1/' : u.slice(0, i + 7); }

  function jsonHeaders(opts){
    var h = {'Content-Type':'application/json'};
    var src = opts && opts.headers;
    try {
      if (src && typeof src.forEach === 'function' && !(src instanceof Array)) { src.forEach(function(v,k){ if (/nonce/i.test(k)) h[k] = v; }); }
      else if (src) { for (var k in src) if (/nonce/i.test(k)) h[k] = src[k]; }
    } catch(e) {}
    if (!h['X-WP-Nonce'] && window.wpApiSettings && wpApiSettings.nonce) h['X-WP-Nonce'] = wpApiSettings.nonce;
    return h;
  }

  /* Upload any images the server doesn't have; replace them with refs in `slides`. */
  function offload(slides, base, opts, force){
    if (!Array.isArray(slides)) return Promise.resolve();
    var idx = [];
    slides.forEach(function(s, i){ if (s && isData(s.image_data)) idx.push(i); });
    if (!idx.length) return Promise.resolve();
    var headers = jsonHeaders(opts);

    return Promise.all(idx.map(function(i){ return sha1(slides[i].image_data); })).then(function(hashes){
      var ask = hashes.filter(function(h){ return force || !known[h]; });
      var check = ask.length && !force
        ? inner(base + 'ppt-img-has', {method:'POST', headers:headers, credentials:'same-origin', body:JSON.stringify({hashes:ask})})
            .then(function(r){ return r.json(); })
            .then(function(d){ (d && d.have || []).forEach(function(h){ known[h] = true; }); })
            .catch(function(){})
        : Promise.resolve();

      return check.then(function(){
        var todo = [], seen = {};
        idx.forEach(function(i, n){ var h = hashes[n]; if ((force || !known[h]) && !seen[h]) { seen[h] = 1; todo.push({h:h, d:slides[i].image_data}); } });
        // one upload at a time: never crowd the image fetches out of the queue
        return todo.reduce(function(p, t){
          return p.then(function(){
            return inner(base + 'ppt-img-put', {method:'POST', headers:headers, credentials:'same-origin', body:JSON.stringify({data:t.d})})
              .then(function(r){ return r.json(); })
              .then(function(d){ if (d && d.success && d.ref === 'dsimg:' + t.h) known[t.h] = true; });
          });
        }, Promise.resolve());
      }).then(function(){
        idx.forEach(function(i, n){ if (known[hashes[n]]) slides[i].image_data = 'dsimg:' + hashes[n]; });
      });
    });
  }

  function slidesOf(body, kind){ return kind === 'save' ? (body.ppt_data && body.ppt_data.slides) : body.slides; }

  /* Send one request with refs; if the server reports missing files, re-upload once. */
  function sendOffloaded(url, opts, kind){
    var body;
    try { body = JSON.parse(opts.body); } catch(e) { return inner(url, opts); }
    var base = restBase(url);
    function attempt(force){
      var b = JSON.parse(JSON.stringify(body)); // never touch the editor's own objects
      return offload(slidesOf(b, kind), base, opts, force).then(function(){
        var o = {}; for (var k in opts) o[k] = opts[k];
        o.body = JSON.stringify(b);
        return inner(url, o);
      });
    }
    return attempt(false).then(function(r){
      return r.clone().json().then(function(d){
        if (d && d.missing_images && d.missing_images.length) {
          d.missing_images.forEach(function(h){ delete known[h]; });
          return attempt(true);
        }
        return r;
      }, function(){ return r; });
    }).catch(function(err){
      // Offload itself failed (e.g. very old browser) — fall back to the original request.
      if (err && err.name === 'AbortError') throw err;
      return inner(url, opts);
    });
  }

  /* ── single-flight save-ppt ── */
  var inflight = null;     // Promise of the running save
  var pending  = null;     // {url, opts, waiters:[{resolve,reject}]}
  var lastId   = '';

  function runSave(url, opts){
    var p = sendOffloaded(url, opts, 'save');
    inflight = p;
    function next(){
      inflight = null;
      if (!pending) return;
      var job = pending; pending = null;
      // A queued save for a brand-new deck must reuse the id the first save created.
      try {
        var b = JSON.parse(job.opts.body);
        if (!b.ppt_id && lastId) { b.ppt_id = lastId; job.opts.body = JSON.stringify(b); }
      } catch(e) {}
      runSave(job.url, job.opts).then(function(r){
        job.waiters.forEach(function(w){ w.resolve(r.clone()); });
      }, function(e){
        job.waiters.forEach(function(w){ w.reject(e); });
      });
    }
    p.then(function(r){
      return r.clone().json().then(function(d){ if (d && d.success && d.ppt_id) lastId = d.ppt_id; }, function(){});
    }, function(){}).then(next, next);
    return p;
  }

  window.fetch = function(input, init){
    var url = urlOf(input);
    var opts = init || {};
    if (typeof opts.body !== 'string' || url.indexOf('/ds/v1/') === -1) return inner.apply(this, arguments);

    if (url.indexOf('/ds/v1/save-ppt') !== -1) {
      if (!inflight) return runSave(url, opts);
      return new Promise(function(resolve, reject){
        if (!pending) pending = {url:url, opts:null, waiters:[]};
        var o = {}; for (var k in opts) o[k] = opts[k];
        pending.url = url; pending.opts = o;               // newest data wins
        pending.waiters.push({resolve:resolve, reject:reject});
      });
    }
    if (url.indexOf('/ds/v1/build-pptx-v2') !== -1) return sendOffloaded(url, opts, 'build');
    if (url.indexOf('/ds/v1/create-share-link') !== -1) return sendOffloaded(url, opts, 'share');
    return inner.apply(this, arguments);
  };
})();
