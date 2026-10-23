
(function(){
(function(){
  if (window.__dsSaveLimit) return; window.__dsSaveLimit = 1;
  var of = window.fetch, API = '/wp-json/', quietUntil = 0, open = false;
  function nonce(){ return window.DSP_NONCE || (window.wpApiSettings && wpApiSettings.nonce) || ''; }
  function api(path, method, body){ return of.call(window, API + path, {method:method || 'GET', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce':nonce()}, body:body ? JSON.stringify(body) : undefined}).then(function(r){ return r.json().catch(function(){ return {}; }); }); }
  function esc(s){ return String(s == null ? '' : s).replace(/[&<>"]/g, function(c){ return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;'}[c]; }); }
  function css(){
    if (document.getElementById('dssl2-css')) return; var s = document.createElement('style'); s.id = 'dssl2-css';
    s.textContent = '#dssl2{position:fixed;inset:0;z-index:1000000;background:rgba(4,7,14,.72);display:flex;align-items:center;justify-content:center;padding:18px;font-family:inherit}' +
      '#dssl2 .c{width:min(620px,100%);max-height:86vh;display:flex;flex-direction:column;background:#101627;border:1px solid rgba(255,255,255,.14);border-radius:16px;box-shadow:0 24px 70px rgba(0,0,0,.6);color:#e9ecf5;overflow:hidden}' +
      '#dssl2 .h{padding:20px 22px 6px;font-size:19px;font-weight:700;display:flex;gap:10px;align-items:center}' +
      '#dssl2 .p{padding:4px 22px 14px;font-size:14px;line-height:1.5;color:#b9c2d8}' +
      '#dssl2 .u{margin:0 22px 12px;padding:9px 12px;border-radius:10px;background:rgba(255,255,255,.06);font-size:13px;font-weight:600;display:flex;justify-content:space-between;gap:10px}' +
      '#dssl2 .u b{color:#ff8b8b}#dssl2 .u.ok b{color:#6fe3a5}' +
      '#dssl2 .l{flex:1;overflow:auto;padding:0 22px 6px;min-height:120px}' +
      '#dssl2 .g{margin:10px 0 6px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8e9ab8}' +
      '#dssl2 .r{display:flex;align-items:center;gap:12px;padding:9px 10px;border:1px solid rgba(255,255,255,.09);border-radius:10px;margin-bottom:7px;background:rgba(255,255,255,.03)}' +
      '#dssl2 .r img{width:54px;height:38px;object-fit:cover;border-radius:6px;background:#1c2438;flex:none}' +
      '#dssl2 .r .t{flex:1;min-width:0}#dssl2 .r .t div{font-size:13.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#dssl2 .r .t span{font-size:11.5px;color:#8e9ab8}' +
      '#dssl2 button{font:inherit;cursor:pointer;border-radius:9px;border:1px solid rgba(255,255,255,.18);background:transparent;color:#e9ecf5;padding:9px 18px;font-size:13.5px;font-weight:700}' +
      '#dssl2 .r button{padding:6px 12px;font-size:12px;color:#ff9c9c;border-color:rgba(255,120,120,.4);flex:none}#dssl2 .r button.sure{background:#d83a3a;color:#fff;border-color:#d83a3a}' +
      '#dssl2 .f{display:flex;justify-content:flex-end;gap:10px;padding:14px 22px 18px;border-top:1px solid rgba(255,255,255,.08)}' +
      '#dssl2 .f .ok{background:#2f6bff;border-color:#2f6bff;color:#fff}#dssl2 button:disabled{opacity:.45;cursor:not-allowed}' +
      '#dssl2 .e{padding:18px 0;text-align:center;color:#8e9ab8;font-size:13px}';
    document.head.appendChild(s);
  }
  function all(path, key){
    var out = [];
    function step(off, n){ return api(path + '?offset=' + off).then(function(d){ var a = (d && d[key]) || []; out = out.concat(a); if (a.length && d.next_offset && d.next_offset > off && n < 40) return step(d.next_offset, n + 1); return out; }); }
    return step(0, 0).catch(function(){ return out; });
  }
  /* returns a promise: true = room was made, save again; false = do not save */
  function ask(){
    if (open) return Promise.resolve(false); open = true; css();
    return new Promise(function(done){
      var o = document.createElement('div'); o.id = 'dssl2'; document.body.appendChild(o);
      var used = 0, total = 0;
      function close(v){ open = false; if (o.parentNode) o.parentNode.removeChild(o); done(v); }
      function step1(){
        o.innerHTML = '<div class="c"><div class="h">&#9888;&#65039; Saved items limit reached</div>' +
          '<div class="p">This presentation cannot be saved because your saved items are full. Delete some saved files to make room, then it will be saved.<br><br>You can still <b>download</b> this presentation without saving it.</div>' +
          '<div class="f"><button type="button" data-a="cancel">Cancel</button><button type="button" class="ok" data-a="ok">OK</button></div></div>';
      }
      function bar(){ var u = o.querySelector('.u'); if (!u) return; var room = total - used; u.className = 'u' + (room > 0 ? ' ok' : ''); u.innerHTML = '<span>Saved items used</span><b>' + used + ' of ' + total + (room > 0 ? ' &middot; room for ' + room : ' &middot; full') + '</b>'; var b = o.querySelector('[data-a="save"]'); if (b) b.disabled = !(room > 0); }
      function cap(){ return api('ds/v1/saved-limit-info').then(function(d){ if (d && d.success) { used = d.used; total = d.cap; } bar(); return d; }).catch(function(){}); }
      function row(kind, id, title, meta, thumb){ return '<div class="r" data-k="' + kind + '" data-id="' + esc(id) + '">' + (thumb ? '<img src="' + esc(thumb) + '" alt="" loading="lazy">' : '') + '<div class="t"><div>' + esc(title) + '</div><span>' + esc(meta) + '</span></div><button type="button" data-a="del">Delete</button></div>'; }
      function step2(){
        o.innerHTML = '<div class="c"><div class="h">Delete saved items</div><div class="p">Delete the files you no longer need, then press <b>OK</b> to save this presentation. Deleted files cannot be brought back.</div><div class="u"><span>Loading&hellip;</span></div><div class="l"><div class="e">Loading your saved items&hellip;</div></div>' +
          '<div class="f"><button type="button" data-a="cancel">Cancel</button><button type="button" class="ok" data-a="save" disabled>OK &ndash; save now</button></div></div>';
        Promise.all([cap(), all('ds/v1/my-ppts', 'drafts'), Promise.resolve([])]).then(function(r){
          var info = r[0] || {}, drafts = r[1] || [], built = r[2] || [], files = info.files || [], h = '';
          if (false) { h += '<div class="g">Downloaded PowerPoint files (' + built.length + ')</div>'; built.forEach(function(b){ h += row('built', b.id, b.title || b.filename, (b.date || '') + (b.size ? ' · ' + Math.round(b.size / 1024) + ' KB' : '')); }); }
          if (drafts.length) { h += '<div class="g">Saved presentations (' + drafts.length + ')</div>'; drafts.forEach(function(p){ h += row('draft', p.id, p.title, (p.slides_count ? p.slides_count + ' slides' : '') + (p.updated ? ' · ' + new Date(p.updated * 1000).toLocaleDateString() : '')); }); }
          if (files.length) { h += '<div class="g">Saved images, videos and audio (' + files.length + ')</div>'; files.forEach(function(f){ h += row('file', f.id, f.title || f.type || 'Saved file', (f.type || '') + (f.date ? ' · ' + f.date : ''), f.thumb); }); }
          var l = o.querySelector('.l'); if (l) l.innerHTML = h || '<div class="e">No saved items were found.</div>';
        });
      }
      o.addEventListener('click', function(e){
        var b = e.target.closest ? e.target.closest('button[data-a]') : null; if (!b) return;
        var a = b.getAttribute('data-a');
        if (a === 'cancel') return close(false);
        if (a === 'ok') return step2();
        if (a === 'save') return close(true);
        if (a === 'del') {
          var r = b.closest('.r'), kind = r.getAttribute('data-k'), id = r.getAttribute('data-id');
          if (!b.classList.contains('sure')) { b.classList.add('sure'); b.textContent = 'Delete?'; setTimeout(function(){ if (b.parentNode && !b.disabled) { b.classList.remove('sure'); b.textContent = 'Delete'; } }, 3500); return; }
          b.disabled = true; b.textContent = 'Deleting…';
          var q = kind === 'draft' ? api('ds/v1/delete-ppt', 'POST', {ppt_id:id}) : kind === 'built' ? api('ds/v1/delete-built-ppt', 'POST', {id:id}) : api('ds-hub/v1/delete/' + encodeURIComponent(id), 'POST', {});
          q.then(function(d){ if (d && (d.success || d.deleted)) { r.parentNode.removeChild(r); cap(); } else { b.disabled = false; b.classList.remove('sure'); b.textContent = 'Failed – retry'; } }).catch(function(){ b.disabled = false; b.classList.remove('sure'); b.textContent = 'Failed – retry'; });
        }
      });
      step1();
    });
  }
  window.dsSaveLimitAsk = ask;
  function fake(obj){ return new Response(JSON.stringify(obj), {status:200, headers:{'Content-Type':'application/json'}}); }
  var NOT = {success:false, error:'Not saved: saved items limit reached. You can still download this presentation.'};
  window.fetch = function(input, init){
    var url = typeof input === 'string' ? input : ((input && input.url) || '');
    if (url.indexOf('/ds/v1/save-ppt') < 0 || !init || typeof init.body !== 'string') return of.apply(this, arguments);
    var self = this;
    function attempt(n){
      return of.call(self, input, init).then(function(r){
        return r.clone().json().then(function(d){
          if (!(d && d.success === false && /saved-item limit/i.test(String(d.error || '')))) return r;
          if (Date.now() < quietUntil || n > 4) return fake(NOT);
          return ask().then(function(ok){ if (!ok) { quietUntil = Date.now() + 20000; return fake(NOT); } return attempt(n + 1); });
        }, function(){ return r; });
      });
    }
    return attempt(0);
  };
})();
})();
