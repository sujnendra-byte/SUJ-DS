<?php
/**
 * Code Snippets export — DS Editor Popup Dialogs - in-page popups instead of browser boxes (confirm / alert / prompt)
 *
 * Snippet ID : 1129
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : 
 * Modified   : 2026-10-10 04:12:23
 * Load order : 299 of 299
 */
/**
 * DS Editor Popup Dialogs - in-page popups instead of the browser's own boxes
 *
 * The editor sometimes asks a question with the browser's own grey box at the top of the screen
 * ("stagingtest.designstager.com says ... Delete slide 5?"). Those boxes now open as a popup window inside
 * the editor, styled like the other editor popups, that can be moved by its title bar.
 *  - Question with OK / Cancel (confirm): OK carries on with the action, Cancel stops it.
 *  - Message with OK only (alert).
 *  - Question with a box to type in (prompt).
 * Keys: Enter = OK, Esc = Cancel.
 * How it works: the editor's question is answered "no" at once, and when OK is pressed in the popup the same
 * click (or key) is done again with the answer "yes". If a question comes without a click or key just before it,
 * the browser's own box is still used, so nothing can get stuck.
 * NEW SNIPPET - nothing existing is changed. Undo: deactivate this snippet.
 */
add_action('wp_footer', function () {
	if ( is_admin() ) return;
	if ( function_exists('ds_tpl_should_print') && ! ds_tpl_should_print() ) return;
	?>
<style id="ds-dialogs-css">
#dsx-dlg-bg{position:fixed;inset:0;z-index:100030;background:rgba(15,23,42,.35);display:flex;align-items:flex-start;justify-content:center;padding-top:14vh}
#dsx-dlg{position:relative;width:380px;max-width:calc(100vw - 32px);background:#fff;border-radius:12px;box-shadow:0 14px 40px rgba(0,0,0,.45);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif;color:#0F172A;overflow:hidden;box-sizing:border-box}
#dsx-dlg *{box-sizing:border-box}
#dsx-dlg .dsx-dlg-h{display:flex;align-items:center;justify-content:space-between;padding:12px 12px 8px 16px;cursor:move;user-select:none;touch-action:none}
#dsx-dlg .dsx-dlg-h b{font-size:14px;font-weight:700;color:#0F172A}
#dsx-dlg .dsx-dlg-h b::before{content:"\2807";display:inline-block;margin-right:6px;color:#94A3B8;font-weight:400;letter-spacing:-2px}
#dsx-dlg button{font-family:inherit;text-transform:none;letter-spacing:0;box-shadow:none;min-height:0;line-height:1.2;margin:0;cursor:pointer}
#dsx-dlg .dsx-dlg-x{width:26px;height:26px;padding:0;border:0;background:transparent;color:#475569;font-size:20px;line-height:1;border-radius:6px}
#dsx-dlg .dsx-dlg-x:hover{background:#F1F5F9;color:#0F172A}
#dsx-dlg .dsx-dlg-m{padding:4px 16px 14px;font-size:13.5px;line-height:1.5;color:#334155;white-space:pre-wrap;word-break:break-word;max-height:50vh;overflow:auto}
#dsx-dlg .dsx-dlg-in{display:block;width:calc(100% - 32px);margin:0 16px 14px;height:36px;padding:0 10px;border:1px solid #CBD5E1;border-radius:8px;font-size:13.5px;color:#0F172A;background:#F8FAFC;outline:0}
#dsx-dlg .dsx-dlg-in:focus{border-color:#2563EB;background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
#dsx-dlg .dsx-dlg-f{display:flex;justify-content:flex-end;gap:8px;padding:10px 16px 14px;border-top:1px solid #E2E8F0;background:#F8FAFC}
#dsx-dlg .dsx-dlg-f button{height:34px;padding:0 18px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid #CBD5E1;background:#fff;color:#334155}
#dsx-dlg .dsx-dlg-f button:hover{background:#F1F5F9}
#dsx-dlg .dsx-dlg-f .dsx-dlg-ok{background:#2563EB;border-color:#2563EB;color:#fff}
#dsx-dlg .dsx-dlg-f .dsx-dlg-ok:hover{background:#1D4ED8}
#dsx-dlg .dsx-dlg-f .dsx-dlg-ok.dsx-danger{background:#DC2626;border-color:#DC2626}
#dsx-dlg .dsx-dlg-f .dsx-dlg-ok.dsx-danger:hover{background:#B91C1C}
</style>
<script id="ds-dialogs-js">
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
</script>
<?php
}, 4);
