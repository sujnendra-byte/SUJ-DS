
(function(){
  var D = window.DSTE; if (!D || !D.toHTML || D.__pngObj) return; D.__pngObj = 1;
  var th = D.toHTML, MAXN = 6, MAXPX = 1000, MAXLEN = 2800000;
  function ok(s){ return typeof s === 'string' && s.length < MAXLEN && /^data:image\/png;base64,[A-Za-z0-9+\/=]+$/.test(s); }
  function slides(){ return (window.S && S.data && S.data.slides) || null; }
  function list(sl){ return sl && Array.isArray(sl.png_objects) ? sl.png_objects : []; }
  function pc(v, t){ return (Math.round((+v || 0) / t * 100000) / 1000) + '%'; }
  function toast(m){ if (window.dspToast) window.dspToast(m); }
  /* 1. every PNG object of the slide is drawn on top of the layout - in the editor, the thumbnails and the picture / PDF downloads */
  D.toHTML = function(page, idx, opts){
    var h = th.apply(this, arguments);
    try {
      var o = opts || {}, sl = o.slide || {}, edit = o.edit !== false && idx >= 0, add = '';
      list(sl).slice(0, MAXN).forEach(function(b, k){
        if (!b || !ok(b.src)) return;
        add += '<div style="position:absolute;left:' + pc(b.x, 1280) + ';top:' + pc(b.y, 720) + ';width:' + pc(b.w, 1280) + ';height:' + pc(b.h, 720) + ';z-index:40;' +
          (edit ? 'pointer-events:auto;cursor:move;touch-action:none;--ds-obj:' + idx + '_' + k + ';' : 'pointer-events:none;') + '">' +
          '<img src="' + b.src + '" alt="" draggable="false" style="position:absolute;left:0;top:0;width:100%;height:100%;display:block;pointer-events:none;-webkit-user-select:none;user-select:none">' +
          (edit ? '<span data-dsobj="del" title="Remove this picture">×</span><span data-dsobj="size" title="Drag to resize"></span>' : '') + '</div>';
      });
      if (edit && o.slide && list(sl).length < MAXN) add += '<div data-dsobj="add" style="--ds-objadd:' + idx + ';" title="Add a PNG picture with a transparent background (a person, a product, any object)">+ Add PNG</div>';
      if (add) { var i = h.lastIndexOf('</div>'); if (i > 0) h = h.slice(0, i) + add + h.slice(i); }
    } catch (e) {}
    return h;
  };
  var st = document.createElement('style');
  st.textContent =
    '[style*="--ds-obj:"]>span[data-dsobj]{position:absolute;opacity:0;transition:opacity .12s;pointer-events:auto}' +
    '[style*="--ds-obj:"]:hover,[style*="--ds-obj:"].ds-obj-on{outline:1px dashed #0A6CCF;outline-offset:1px}' +
    '[style*="--ds-obj:"]:hover>span[data-dsobj],[style*="--ds-obj:"].ds-obj-on>span[data-dsobj]{opacity:1}' +
    'span[data-dsobj="del"]{right:-1.1cqw;top:-1.1cqw;width:2.2cqw;height:2.2cqw;min-width:16px;min-height:16px;border-radius:50%;background:#E5484D;color:#fff;font:700 1.5cqw/2.2cqw Arial,sans-serif;text-align:center;cursor:pointer}' +
    'span[data-dsobj="size"]{right:-.8cqw;bottom:-.8cqw;width:1.6cqw;height:1.6cqw;min-width:12px;min-height:12px;border-radius:3px;background:#0A6CCF;border:2px solid #fff;box-sizing:border-box;cursor:nwse-resize}' +
    '[data-dsobj="add"]{position:absolute;left:1.2cqw;bottom:1.2cqw;z-index:41;padding:.45cqw 1.1cqw;border-radius:99px;background:rgba(17,24,39,.88);color:#fff;font:600 1.1cqw/1.6 Arial,sans-serif;cursor:pointer;opacity:0;transition:opacity .15s;pointer-events:auto;white-space:nowrap}' +
    '*:hover>[data-dsobj="add"]{opacity:1}[data-dsobj="add"]:hover{background:#0A6CCF}';
  document.head.appendChild(st);
  function ref(el){ var m = /--ds-obj:(\d+)_(\d+)/.exec(el.getAttribute('style') || ''); if (!m) return null; var ss = slides(), i = +m[1], k = +m[2]; if (!ss || !ss[i]) return null; var b = list(ss[i])[k]; return b ? {i:i, k:k, b:b, sl:ss[i]} : null; }
  function refresh(){ try { D.refreshEditor(); } catch (e) {} }
  /* 2. add: pick a PNG, keep its transparency, place it in the middle of the slide */
  function pick(idx){
    var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/png,image/webp,image/gif'; inp.style.display = 'none'; document.body.appendChild(inp);
    inp.addEventListener('change', function(){
      var f = inp.files && inp.files[0]; inp.remove(); if (!f) return;
      var url = URL.createObjectURL(f), im = new Image();
      im.onload = function(){
        try {
          var nw = im.naturalWidth, nh = im.naturalHeight, data = '', m = MAXPX;
          for (var t = 0; t < 4; t++) {
            var s = Math.min(1, m / Math.max(nw, nh)), c = document.createElement('canvas');
            c.width = Math.max(1, Math.round(nw * s)); c.height = Math.max(1, Math.round(nh * s));
            c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
            data = c.toDataURL('image/png'); if (data.length < MAXLEN) break; m = Math.round(m * 0.7);
          }
          var ss = slides();
          if (!ok(data) || !ss || !ss[idx]) { toast('This picture could not be added.'); }
          else {
            var k = Math.min(420 / nw, 420 / nh), w = Math.round(nw * k), h = Math.round(nh * k);
            if (!Array.isArray(ss[idx].png_objects)) ss[idx].png_objects = [];
            if (ss[idx].png_objects.length >= MAXN) toast('A slide can hold ' + MAXN + ' PNG pictures.');
            else { ss[idx].png_objects.push({src:data, x:Math.round((1280 - w) / 2), y:Math.round((720 - h) / 2), w:w, h:h}); refresh(); toast('PNG added. Drag it into place, pull the blue corner to resize. Click Save to keep it.'); }
          }
        } catch (e) { toast('This picture could not be read.'); }
        URL.revokeObjectURL(url);
      };
      im.onerror = function(){ URL.revokeObjectURL(url); toast('This picture could not be read.'); };
      im.src = url;
    });
    inp.click();
  }
  window.dsPngObjAdd = pick;
  /* 3. move, resize, remove */
  var drag = null;
  document.addEventListener('pointerdown', function(e){
    var t = e.target; if (!t || !t.closest) return;
    var el = t.closest('[style*="--ds-obj:"]'); if (!el) return;
    var r = ref(el); if (!r) return;
    e.preventDefault(); e.stopPropagation();
    var mode = t.getAttribute && t.getAttribute('data-dsobj');
    if (mode === 'del') { drag = {del:r}; return; }
    var root = el.parentElement, rc = root.getBoundingClientRect(); if (!rc.width) return;
    drag = {el:el, r:r, mode:mode === 'size' ? 'size' : 'move', k:1280 / rc.width, x0:e.clientX, y0:e.clientY, bx:+r.b.x || 0, by:+r.b.y || 0, bw:+r.b.w || 10, bh:+r.b.h || 10, moved:false};
    el.classList.add('ds-obj-on');
    try { el.setPointerCapture(e.pointerId); } catch (x) {}
  }, true);
  document.addEventListener('pointermove', function(e){
    if (!drag || !drag.el) return;
    e.preventDefault();
    var dx = (e.clientX - drag.x0) * drag.k, dy = (e.clientY - drag.y0) * drag.k, b = drag.r.b;
    if (Math.abs(dx) + Math.abs(dy) > 2) drag.moved = true;
    if (drag.mode === 'move') {
      b.x = Math.round(Math.max(-drag.bw * 0.8, Math.min(1280 - drag.bw * 0.2, drag.bx + dx)));
      b.y = Math.round(Math.max(-drag.bh * 0.8, Math.min(720 - drag.bh * 0.2, drag.by + dy)));
    } else {
      var f = Math.max(24 / drag.bw, Math.min(1600 / drag.bw, (drag.bw + Math.max(dx, dy * drag.bw / drag.bh)) / drag.bw));
      b.w = Math.round(drag.bw * f); b.h = Math.round(drag.bh * f);
    }
    var s = drag.el.style; s.left = pc(b.x, 1280); s.top = pc(b.y, 720); s.width = pc(b.w, 1280); s.height = pc(b.h, 720);
  }, true);
  function end(e){
    if (!drag) return;
    var d = drag; drag = null;
    if (d.del) { var a = list(d.del.sl), k = a.indexOf(d.del.b); if (k >= 0) a.splice(k, 1); if (!a.length) delete d.del.sl.png_objects; refresh(); toast('Picture removed.'); return; }
    d.el.classList.remove('ds-obj-on');
    if (d.moved) refresh();
  }
  document.addEventListener('pointerup', end, true);
  document.addEventListener('pointercancel', end, true);
  document.addEventListener('click', function(e){
    var t = e.target; if (!t || !t.closest) return;
    var a = t.closest('[data-dsobj="add"]');
    if (a) { var m = /--ds-objadd:(\d+)/.exec(a.getAttribute('style') || ''); e.preventDefault(); e.stopPropagation(); if (m) pick(parseInt(m[1], 10)); return; }
    if (t.closest('[style*="--ds-obj:"]')) { e.preventDefault(); e.stopPropagation(); }
  }, true);
})();
