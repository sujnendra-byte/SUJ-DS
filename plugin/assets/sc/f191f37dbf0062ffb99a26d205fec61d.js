
(function(){
  if (window.__dsxDialogs) return; window.__dsxDialogs = 1;
  var nConfirm = window.confirm, nAlert = window.alert, nPrompt = window.prompt;

  /* the last click / key, so it can be done again after OK */
  var last = null;
  document.addEventListener('click', function(ev){
    if (ev.target && ev.target.closest && ev.target.closest('#dsx-dlg-bg')) return;
    last = {kind: 'click', t: ev.target, at: Date.now(), o: {bubbles: true, cancelable: true, view: window, clientX: ev.clientX, clientY: ev.clientY, button: ev.button, shiftKey: ev.shiftKey, ctrlKey: ev.ctrlKey, metaKey: ev.metaKey, altKey: ev.altKey}};
  }, true);
  document.addEventListener('keydown', function(ev){
    if (ev.target && ev.target.closest && ev.target.closest('#dsx-dlg-bg')) return;
    last = {kind: 'keydown', t: ev.target, at: Date.now(), o: {bubbles: true, cancelable: true, key: ev.key, code: ev.code, keyCode: ev.keyCode, which: ev.which, shiftKey: ev.shiftKey, ctrlKey: ev.ctrlKey, metaKey: ev.metaKey, altKey: ev.altKey}};
  }, true);
  function trigger(){
    if (!last || Date.now() - last.at > 4000 || !last.t || !last.t.isConnected) return null;
    return last;
  }
  function replay(tr){
    try {
      var e = tr.kind === 'click' ? new MouseEvent('click', tr.o) : new KeyboardEvent('keydown', tr.o);
      if (tr.kind === 'keydown') { try { Object.defineProperty(e, 'keyCode', {get: function(){ return tr.o.keyCode; }}); Object.defineProperty(e, 'which', {get: function(){ return tr.o.which; }}); } catch (x) {} }
      tr.t.dispatchEvent(e);
    } catch (x) {}
  }

  /* answers given in the popup, used once when the action runs again */
  var given = {confirm: null, prompt: null};
  function take(k){ var g = given[k]; if (g && Date.now() < g.until) { given[k] = null; return g; } given[k] = null; return null; }

  var open = false;
  function esc(s){ return String(s == null ? '' : s).replace(/[&<>"]/g, function(c){ return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]; }); }
  function show(type, msg, def, done){
    open = true;
    var danger = type === 'confirm' && /delete|remove|reset|clear|discard|erase/i.test(String(msg));
    var title = type === 'alert' ? 'Notice' : (type === 'prompt' ? 'Enter a value' : (danger ? 'Please confirm' : 'Confirm'));
    var bg = document.createElement('div'); bg.id = 'dsx-dlg-bg';
    bg.innerHTML = '<div id="dsx-dlg" role="dialog" aria-modal="true"><div class="dsx-dlg-h"><b>' + title + '</b><button type="button" class="dsx-dlg-x" title="Close">&times;</button></div>' +
      '<div class="dsx-dlg-m">' + esc(msg) + '</div>' +
      (type === 'prompt' ? '<input class="dsx-dlg-in" type="text">' : '') +
      '<div class="dsx-dlg-f">' + (type === 'alert' ? '' : '<button type="button" class="dsx-dlg-no">Cancel</button>') + '<button type="button" class="dsx-dlg-ok' + (danger ? ' dsx-danger' : '') + '">OK</button></div></div>';
    document.body.appendChild(bg);
    var box = bg.querySelector('#dsx-dlg'), inp = bg.querySelector('.dsx-dlg-in'), okB = bg.querySelector('.dsx-dlg-ok');
    if (inp) inp.value = def == null ? '' : String(def);
    function close(ok){
      if (!bg.parentNode) return;
      bg.remove(); document.removeEventListener('keydown', key, true); open = false;
      setTimeout(function(){ done(ok, inp ? inp.value : null); }, 0);
    }
    function key(ev){
      if (ev.key === 'Escape') { ev.preventDefault(); ev.stopPropagation(); close(false); }
      else if (ev.key === 'Enter') { ev.preventDefault(); ev.stopPropagation(); close(true); }
      else if (!(inp && ev.target === inp)) { ev.stopPropagation(); }
    }
    document.addEventListener('keydown', key, true);
    okB.addEventListener('click', function(ev){ ev.stopPropagation(); close(true); });
    var no = bg.querySelector('.dsx-dlg-no'); if (no) no.addEventListener('click', function(ev){ ev.stopPropagation(); close(false); });
    bg.querySelector('.dsx-dlg-x').addEventListener('click', function(ev){ ev.stopPropagation(); close(type === 'alert'); });
    ['mousedown', 'pointerdown', 'click', 'keyup'].forEach(function(t){ bg.addEventListener(t, function(ev){ ev.stopPropagation(); }); });
    /* move by the title bar */
    bg.querySelector('.dsx-dlg-h').addEventListener('pointerdown', function(ev){
      if (ev.target.closest('button') || ev.button !== 0) return;
      ev.preventDefault();
      var r = box.getBoundingClientRect(), dx = ev.clientX - r.left, dy = ev.clientY - r.top;
      bg.style.alignItems = 'flex-start'; bg.style.justifyContent = 'flex-start'; bg.style.paddingTop = '0';
      box.style.position = 'fixed'; box.style.left = r.left + 'px'; box.style.top = r.top + 'px';
      function mv(e){ box.style.left = Math.max(0, Math.min(e.clientX - dx, innerWidth - 80)) + 'px'; box.style.top = Math.max(0, Math.min(e.clientY - dy, innerHeight - 50)) + 'px'; }
      function up(){ document.removeEventListener('pointermove', mv, true); document.removeEventListener('pointerup', up, true); }
      document.addEventListener('pointermove', mv, true); document.addEventListener('pointerup', up, true);
    });
    setTimeout(function(){ try { (inp || okB).focus(); if (inp) inp.select(); } catch (e) {} }, 0);
  }

  window.confirm = function(msg){
    var g = take('confirm'); if (g) return true;
    var tr = trigger(); if (!tr || open) return nConfirm.apply(window, arguments);
    show('confirm', msg, null, function(ok){
      if (!ok) return;
      given.confirm = {until: Date.now() + 5000};
      replay(tr);
    });
    return false;
  };
  window.prompt = function(msg, def){
    var g = take('prompt'); if (g) return g.v;
    var tr = trigger(); if (!tr || open) return nPrompt.apply(window, arguments);
    show('prompt', msg, def, function(ok, v){
      if (!ok) return;
      given.prompt = {until: Date.now() + 5000, v: v};
      replay(tr);
    });
    return null;
  };
  window.alert = function(msg){
    if (open) return nAlert.apply(window, arguments);
    show('alert', msg, null, function(){});
  };
})();
