<?php
/**
 * Code Snippets export — DS Template Second Picture
 *
 * Snippet ID : 1008
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-03 14:20:21
 * Load order : 214 of 218
 */
/**
 * DS Template Second Picture
 * Run: Everywhere
 *
 * AI Presentation - design templates: a layout with two picture boxes (the Midnight Neon closing
 * slide) used the slide's one picture in both boxes. With this snippet the second box is its own
 * picture slot: click it in the editor to upload a different picture (stored on the slide as
 * image2_data, a JPEG of at most 1600 px). It shows in the editor, the thumbnails and - together
 * with the matching change in "DS Template Engine (T00)" - in the PowerPoint download.
 * Picture 2 is always centred; crop and drag are only for picture 1.
 * NEW SNIPPET. Undo: deactivate this snippet (both boxes show picture 1 again).
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( ! function_exists('ds_tpl_should_print') || ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-tpl-second-picture">
(function(){
(function(){
  var D = window.DSTE; if (!D || !D.renderPage || !D.toHTML || D.__img2) return; D.__img2 = 1;
  var rp = D.renderPage, th = D.toHTML, MK = '#01FE02', MKCSS = 'background:rgba(1,254,2,1);';
  /* 1. the second picture box of a layout becomes picture slot 2 */
  D.renderPage = function(){
    var p = rp.apply(this, arguments);
    try { var n = 0; (p && p.els || []).forEach(function(e){ if (e && e.t === 'img') { n++; if (n === 2) e.slot = 2; } }); } catch (e) {}
    return p;
  };
  function okData(s){ return typeof s === 'string' && /^data:image\/(png|jpe?g);base64,[A-Za-z0-9+\/=]+$/.test(s); }
  /* 2. slot 2 is drawn from the slide's own second picture (image2_data), not from the first one */
  D.toHTML = function(page, idx, opts){
    var has = false; try { has = !!(page && page.els && page.els.some(function(e){ return e && e.t === 'img' && e.slot === 2; })); } catch (e) {}
    if (!has) return th.apply(this, arguments);
    var ph = null, els = page.els.map(function(e){
      if (!(e && e.t === 'img' && e.slot === 2)) return e;
      ph = e.ph; return {t:e.shape === 'ell' ? 'ell' : 'rect', x:e.x, y:e.y, w:e.w, h:e.h, r:e.r || 0, fill:MK};
    });
    var cp = {}; for (var k in page) cp[k] = page[k]; cp.els = els;
    var a = Array.prototype.slice.call(arguments); a[0] = cp;
    var h = th.apply(this, a), o = opts || {}, sl = o.slide || {}, edit = o.edit !== false && idx >= 0, img = okData(sl.image2_data) ? sl.image2_data : '';
    var bg = img ? 'background-image:url(' + img + ');background-size:cover;background-repeat:no-repeat;background-position:50% 50%;'
                 : 'background:' + (ph && ph.grad && D.cssGrad ? D.cssGrad(ph.grad) : '#8899AA') + ';';
    var i = h.indexOf(MKCSS); if (i < 0) return h;
    var j = h.indexOf('"', i), rest = h.slice(i + MKCSS.length, j);
    if (edit) rest = rest.replace('pointer-events:none;', '') + 'pointer-events:auto;cursor:pointer;--ds-img2' + (img ? '' : '-empty') + ':' + idx + ';';
    return h.slice(0, i) + bg + rest + h.slice(j);
  };
  /* 3. editor: click the second box to upload its own picture */
  var st = document.createElement('style');
  st.textContent = '[style*="--ds-img2"]{overflow:hidden}' +
    '[style*="--ds-img2"]::after{content:"Change picture 2";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font:600 1.15cqw Arial,sans-serif;background:rgba(0,0,0,.5);opacity:0;transition:opacity .15s;border-radius:inherit}' +
    '[style*="--ds-img2"]:hover::after{opacity:1}' +
    '[style*="--ds-img2-empty"]::after{content:"Click to add picture 2";background:transparent;opacity:.85}';
  document.head.appendChild(st);
  function slides(){ return (window.S && S.data && S.data.slides) || null; }
  function setImg(idx, data){
    var ss = slides(); if (!ss || !ss[idx]) return false;
    if (data) ss[idx].image2_data = data; else delete ss[idx].image2_data;
    try { D.refreshEditor(); } catch (e) {}
    return true;
  }
  window.dsImg2Set = setImg;
  function pick(idx){
    var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/*'; inp.style.display = 'none'; document.body.appendChild(inp);
    inp.addEventListener('change', function(){
      var f = inp.files && inp.files[0]; inp.remove(); if (!f) return;
      var url = URL.createObjectURL(f), im = new Image();
      im.onload = function(){
        try {
          var m = 1600, s = Math.min(1, m / Math.max(im.naturalWidth, im.naturalHeight)), c = document.createElement('canvas');
          c.width = Math.max(1, Math.round(im.naturalWidth * s)); c.height = Math.max(1, Math.round(im.naturalHeight * s));
          var x = c.getContext('2d'); x.fillStyle = '#FFFFFF'; x.fillRect(0, 0, c.width, c.height); x.drawImage(im, 0, 0, c.width, c.height);
          setImg(idx, c.toDataURL('image/jpeg', 0.86));
          if (window.dspToast) window.dspToast('Picture 2 added. Click Save to keep it.');
        } catch (e) { if (window.dspToast) window.dspToast('This picture could not be read.'); }
        URL.revokeObjectURL(url);
      };
      im.onerror = function(){ URL.revokeObjectURL(url); if (window.dspToast) window.dspToast('This picture could not be read.'); };
      im.src = url;
    });
    inp.click();
  }
  document.addEventListener('click', function(e){
    var t = e.target && e.target.closest ? e.target.closest('[style*="--ds-img2"]') : null; if (!t) return;
    var m = /--ds-img2(?:-empty)?:(\d+)/.exec(t.getAttribute('style') || ''); if (!m) return;
    e.preventDefault(); e.stopPropagation(); pick(parseInt(m[1], 10));
  }, true);
})();
})();
</script>
<?php
}, 1102);
