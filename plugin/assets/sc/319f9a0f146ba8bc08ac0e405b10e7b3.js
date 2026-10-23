
(function(){
  var box=document.getElementById('dshVidBox'); if(!box) return;
  var titleEl=document.getElementById('dshVidTitle'), thumbs=document.getElementById('dshVidThumbs');
  function play(id){
    var old=box.querySelector('iframe'); if(old) old.remove();
    var p=document.getElementById('dshVidPoster'); if(p) p.remove();
    var f=document.createElement('iframe');
    f.src='https://www.youtube-nocookie.com/embed/'+id+'?autoplay=1&rel=0&modestbranding=1&playsinline=1';
    f.allow='autoplay; encrypted-media; picture-in-picture; fullscreen';
    f.setAttribute('allowfullscreen',''); f.title='Designstager video';
    box.insertBefore(f, box.firstChild); box.dataset.id=id;
  }
  var poster=document.getElementById('dshVidPoster');
  if(poster) poster.addEventListener('click',function(){ play(box.dataset.id); });
  document.getElementById('dshVidFs').addEventListener('click',function(){
    if(!box.querySelector('iframe')) play(box.dataset.id);
    var fs=box.requestFullscreen||box.webkitRequestFullscreen; if(fs) fs.call(box);
  });
  if(thumbs){
    thumbs.addEventListener('click',function(e){
      var b=e.target.closest('.dsh-hv-th'); if(!b) return;
      thumbs.querySelectorAll('.dsh-hv-th').forEach(function(t){t.classList.remove('is-on');});
      b.classList.add('is-on'); titleEl.textContent=b.dataset.title; play(b.dataset.id);
    });
    document.querySelectorAll('#dsHero .dsh-hv-nav').forEach(function(n){
      n.addEventListener('click',function(){ thumbs.scrollBy({left:(+n.dataset.dir)*thumbs.clientWidth*0.8,behavior:'smooth'}); });
    });
  }
})();
