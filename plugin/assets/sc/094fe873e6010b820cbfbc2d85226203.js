
/* Slide photos were squeezed twice: once when they arrive (1920px, JPEG 0.92) and again on every Save / Download
   (1600px, JPEG 0.82). The second pass made them soft on a big screen. Now a photo that is already slide-size is left
   exactly as it is; only an oversized one is resized, once, to 1920px at JPEG 0.92 (PNG with transparency stays PNG). */
(function(){
if (typeof window.dspShrinkImages !== 'function' || window.__dsImgClarity) return; window.__dsImgClarity = 1;
var MAXPX = 1920, QUALITY = 0.92, LIMIT = 400000;
function hasAlpha(cv, w, h){
  try { var px = cv.getContext('2d').getImageData(0, 0, w, h).data; for (var i = 3; i < px.length; i += 40) if (px[i] < 250) return true; } catch (e) { return true; }
  return false;
}
function one(s){
  return new Promise(function(res){
    var d = s && s.image_data;
    if (!d || typeof d !== 'string' || d.length < LIMIT || d.indexOf('data:image') !== 0) { res(); return; }
    var im = new Image();
    im.onload = function(){
      try {
        var w = im.naturalWidth, h = im.naturalHeight, isJpg = /^data:image\/jpe?g/.test(d);
        if (Math.max(w, h) <= MAXPX && isJpg) { res(); return; }           /* already slide-size: do not compress again */
        var sc = Math.min(1, MAXPX / Math.max(w, h));
        var cw = Math.max(1, Math.round(w * sc)), ch = Math.max(1, Math.round(h * sc));
        var cv = document.createElement('canvas'); cv.width = cw; cv.height = ch;
        var cx = cv.getContext('2d'); cx.imageSmoothingEnabled = true; cx.imageSmoothingQuality = 'high';
        cx.drawImage(im, 0, 0, cw, ch);
        var out = (d.indexOf('data:image/png') === 0 && hasAlpha(cv, cw, ch)) ? cv.toDataURL('image/png') : cv.toDataURL('image/jpeg', QUALITY);
        if (out && out.length < d.length) s.image_data = out;
      } catch (e) {}
      res();
    };
    im.onerror = function(){ res(); };
    im.src = d;
  });
}
window.dspShrinkImages = function(slides){ return Promise.all((slides || []).map(one)); };
})();
