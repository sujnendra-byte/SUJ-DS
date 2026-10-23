
(function () {
  var DRAG_THRESHOLD = 5;
  var active = null;
  var cropEdit = null;

  function clamp(v) { return Math.max(0, Math.min(100, v)); }

  function getStoredPos(idx) {
    if (!window.S || !window.S.data || !window.S.data.slides || !window.S.data.slides[idx]) return {x:50,y:50};
    var p = window.S.data.slides[idx].image_position;
    return (p && typeof p.x === 'number') ? p : {x:50,y:50};
  }

  function commitPos(idx, x, y) {
    var pos = {x:x, y:y};
    if (window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx])
      window.S.data.slides[idx].image_position = pos;
    if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[idx])
      window.G.pptData.slides[idx].image_position = pos;
  }

  function enterCropEdit(zone, idx) {
    if (cropEdit && cropEdit.idx === idx) return;
    exitCropEdit(true);
    var slide = window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx];
    if (!slide || !slide.image_data) {
      if (typeof window.vpImgOptionOpen === 'function') window.vpImgOptionOpen(idx);
      return;
    }
    var pos = getStoredPos(idx);
    cropEdit = { idx: idx, zone: zone, pos: {x:pos.x, y:pos.y} };
    var mainSlide = document.getElementById('dsp-ppt-main-slide');
    if (!mainSlide) return;
    if (getComputedStyle(mainSlide).position === 'static') mainSlide.style.position = 'relative';
    var mainRect = mainSlide.getBoundingClientRect();
    var zoneRect = zone.getBoundingClientRect();
    var zL = Math.max(0, (zoneRect.left - mainRect.left) / mainRect.width * 100);
    var zT = Math.max(0, (zoneRect.top  - mainRect.top)  / mainRect.height * 100);
    var zW = Math.min(100 - zL, zoneRect.width  / mainRect.width  * 100);
    var zH = Math.min(100 - zT, zoneRect.height / mainRect.height * 100);
    if (zW < 5 || zH < 5) { zL=0; zT=0; zW=100; zH=100; }
    var ov = document.createElement('div');
    ov.id = 'dsp-cre-fullslide';
    ov.style.cssText = 'position:absolute;inset:0;z-index:1000;overflow:hidden;cursor:grab;user-select:none;';
    var imgBg = document.createElement('div');
    imgBg.style.cssText = 'position:absolute;inset:0;background-image:url('+slide.image_data+');background-size:cover;background-repeat:no-repeat;background-position:'+pos.x+'% '+pos.y+'%;pointer-events:none;';
    ov.appendChild(imgBg);
    cropEdit.imgBg = imgBg;
    function makeShade(l,t,w,h){var d=document.createElement('div');d.style.cssText='position:absolute;background:rgba(0,0,0,0.58);pointer-events:none;left:'+l+'%;top:'+t+'%;width:'+w+'%;height:'+h+'%;';return d;}
    ov.appendChild(makeShade(0,0,100,zT));
    ov.appendChild(makeShade(0,zT+zH,100,100-zT-zH));
    ov.appendChild(makeShade(0,zT,zL,zH));
    ov.appendChild(makeShade(zL+zW,zT,100-zL-zW,zH));
    var frame = document.createElement('div');
    frame.style.cssText = 'position:absolute;pointer-events:none;box-sizing:border-box;border:2px solid rgba(255,255,255,0.9);left:'+zL+'%;top:'+zT+'%;width:'+zW+'%;height:'+zH+'%;';
    var grid = document.createElement('div');
    grid.style.cssText = 'position:absolute;inset:0;display:grid;grid-template-columns:1fr 1fr 1fr;grid-template-rows:1fr 1fr 1fr;';
    for(var gi=0;gi<9;gi++){var cell=document.createElement('div');cell.style.cssText='border:0.5px solid rgba(255,255,255,0.2);';grid.appendChild(cell);}
    [['top:-2px;left:-2px;border-width:3px 0 0 3px;'],['top:-2px;right:-2px;border-width:3px 3px 0 0;'],['bottom:-2px;left:-2px;border-width:0 0 3px 3px;'],['bottom:-2px;right:-2px;border-width:0 3px 3px 0;']].forEach(function(s){var c=document.createElement('div');c.style.cssText='position:absolute;width:14px;height:14px;border-color:#fff;border-style:solid;pointer-events:none;'+s[0];frame.appendChild(c);});
    frame.appendChild(grid);
    ov.appendChild(frame);
    var hint=document.createElement('div');
    hint.style.cssText='position:absolute;bottom:36px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.8);font-size:11px;pointer-events:none;white-space:nowrap;text-shadow:0 1px 3px rgba(0,0,0,0.9);';
    hint.textContent='Drag to reposition \u2022 Framed area appears in your slide';
    ov.appendChild(hint);
    var doneBtn=document.createElement('button');
    doneBtn.innerHTML='\u2713 Done';
    doneBtn.style.cssText='position:absolute;bottom:8px;right:8px;z-index:10;background:#2563eb;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;';
    doneBtn.onmousedown=function(e){e.stopPropagation();};
    doneBtn.onclick=function(e){e.stopPropagation();exitCropEdit(false);};
    ov.appendChild(doneBtn);
    var cancelBtn=document.createElement('button');
    cancelBtn.innerHTML='\u2715';
    cancelBtn.style.cssText='position:absolute;bottom:8px;right:80px;z-index:10;background:rgba(0,0,0,0.5);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:6px 10px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;';
    cancelBtn.onmousedown=function(e){e.stopPropagation();};
    cancelBtn.onclick=function(e){e.stopPropagation();exitCropEdit(true);};
    ov.appendChild(cancelBtn);
    mainSlide.appendChild(ov);
    cropEdit.ov = ov;
  }

  function exitCropEdit(cancel) {
    if (!cropEdit) return;
    var idx = cropEdit.idx;
    if (!cancel) {
      commitPos(idx, cropEdit.pos.x, cropEdit.pos.y);
      var v = (window.S && window.S.choices && window.S.choices['slide_'+idx]) || 'v1';
      if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs();
      if (typeof window.applyCanvas === 'function') window.applyCanvas(idx, v);
    }
    if (cropEdit.ov && cropEdit.ov.parentNode) cropEdit.ov.parentNode.removeChild(cropEdit.ov);
    cropEdit = null;
  }

  // Called by the ✂️ Crop button inside imgZone HTML
  window.vpStartCropEdit = function(idx) {
    var zone = document.querySelector('#dsp-ppt-main-slide .dsp-vp-imgzone[data-slide-idx="'+idx+'"]');
    if (!zone) zone = document.querySelector('.dsp-vp-imgzone[data-slide-idx="'+idx+'"]');
    if (zone) enterCropEdit(zone, idx);
  };

  // Override vpImgOptionOpen to track current slide index
  window.vpImgOptionOpen = function(idx) {
    window._vpOptIdx = idx;
    window._vpMktTargetSlide = idx;
    window._vpMktTargetVariant = window.S ? (window.S.choices['slide_'+idx] || 'v2') : 'v2';
    if (typeof MKT !== 'undefined') MKT.slideIdx = idx;
    window.MKT = window.MKT || {};
    window.MKT.slideIdx = idx;
    var hasCrop = window.S && window.S.data && window.S.data.slides && window.S.data.slides[idx] && window.S.data.slides[idx].image_data;
    var cropBtn = document.getElementById('vp-img-option-crop-btn');
    if (cropBtn) cropBtn.style.display = hasCrop ? 'block' : 'none';
    var m = document.getElementById('vp-img-option-modal');
    if (m) m.style.display = 'flex';
  };
 
  document.addEventListener('mousedown', onDown);
  document.addEventListener('touchstart', onDown, { passive: false });
  window.addEventListener('mousemove', onMove);
  window.addEventListener('touchmove', onMove, { passive: false });
  window.addEventListener('mouseup', onUp);
  window.addEventListener('touchend', onUp);

  function onDown(e) {
    // Let buttons handle their own clicks
    if (e.target.closest && e.target.closest('.dsp-vp-imgreset')) return;
    if (e.target.closest && e.target.closest('.dsp-vp-cropbtn')) return;
    if (e.target.closest && e.target.closest('button')) return;

    if (cropEdit) {
      var ov = document.getElementById('dsp-cre-fullslide');
      if (ov && ov.contains(e.target)) {
        var pt = e.touches ? e.touches[0] : e;
        active = { isCrop:true, startPx:pt.clientX, startPy:pt.clientY, startPos:{x:cropEdit.pos.x, y:cropEdit.pos.y} };
        ov.style.cursor = 'grabbing';
        if (e.cancelable) e.preventDefault();
        return;
      }
      exitCropEdit(false);
      return;
    }

    var zone = e.target.closest && e.target.closest('.dsp-vp-imgzone');
    if (!zone) return;
    // Skip zones inside the thumbnail panel - only act on the main slide
    if (zone.closest('#dsp-ppt-thumbs')) return;
    var idx = parseInt(zone.getAttribute('data-slide-idx'), 10);
    if (isNaN(idx)) return;
    var pt = e.touches ? e.touches[0] : e;
    active = { isCrop:false, el:zone, idx:idx, startPx:pt.clientX, startPy:pt.clientY, moved:false };
  }

  function onMove(e) {
    if (!active) return;
    var pt = e.touches ? e.touches[0] : e;
    var dx = pt.clientX - active.startPx;
    var dy = pt.clientY - active.startPy;
    if (active.isCrop && cropEdit) {
      var mainSlide = document.getElementById('dsp-ppt-main-slide');
      var mw = mainSlide ? mainSlide.getBoundingClientRect().width  : 800;
      var mh = mainSlide ? mainSlide.getBoundingClientRect().height : 450;
      var nx = clamp(active.startPos.x - (dx / mw) * 100 * 1.2);
      var ny = clamp(active.startPos.y - (dy / mh) * 100 * 1.2);
      cropEdit.pos = {x:nx, y:ny};
      if (cropEdit.imgBg) cropEdit.imgBg.style.backgroundPosition = nx+'% '+ny+'%';
      if (e.cancelable) e.preventDefault();
      return;
    }
    if (!active.isCrop) {
      if (!active.moved && (Math.abs(dx) > DRAG_THRESHOLD || Math.abs(dy) > DRAG_THRESHOLD))
        active.moved = true;
    }
  }

  function onUp(e) {
    if (!active) return;
    var isCrop = active.isCrop;
    var zone   = active.el;
    var idx    = active.idx;
    var moved  = active.moved;
    active = null;
    if (cropEdit && cropEdit.ov) cropEdit.ov.style.cursor = 'grab';
    // Single click on zone (no drag) -> open the Change Image popup
    if (!isCrop && !moved && zone) {
      if (typeof window.vpImgOptionOpen === 'function') window.vpImgOptionOpen(idx);
    }
  }

  window.dspResetImagePosition = function(idx) {
    commitPos(idx, 50, 50);
    if (cropEdit && cropEdit.idx === idx) {
      cropEdit.pos = {x:50, y:50};
      if (cropEdit.imgBg) cropEdit.imgBg.style.backgroundPosition = '50% 50%';
    }
    var v = (window.S && window.S.choices && window.S.choices['slide_'+idx]) || 'v1';
    if (typeof window.dspRenderThumbs === 'function') window.dspRenderThumbs();
    if (typeof window.applyCanvas === 'function') window.applyCanvas(idx, v);
  };
})();
