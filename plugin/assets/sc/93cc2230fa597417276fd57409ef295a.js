
(function(){
  var root=document.getElementById('dsHero');if(!root)return;
  if(!root.querySelector('.dsh-laptop'))return;   /* the video sits in this place: nothing to animate */

  /* ---- images shown inside the laptop screen. Change these URLs to your own. ---- */
  var BASE='https://designstager.com/wp-content/uploads/2026/09/';
  var PIC={
    exec  : BASE+'ds-blog-diverse-executive-team-discussing-strate-1789541394.png',
    city  : BASE+'ds-blog-vibrant-sunrise-over-bustling-city-skyli-1789627849.png',
    artist: BASE+'ds-blog-artist-sketching-vibrant-illustrations-o-1789627853.png',
    music : BASE+'ds-blog-musician-recording-acoustic-sounds-besid-1789627857.png',
    film  : BASE+'ds-blog-filmmaker-directing-a-lively-street-perf-1789627855.png',
    swatch: BASE+'ds-blog-art-director-arranging-color-swatches-an-1789455034.png',
    folio : BASE+'ds-blog-designer-arranging-printed-portfolio-boa-1789455030.png'
  };
  function fill(scene){
    scene.querySelectorAll('img[data-dsh]').forEach(function(img){
      if(img.getAttribute('src'))return;
      var u=PIC[img.getAttribute('data-dsh')];if(u)img.setAttribute('src',u);
    });
  }

  /* ---- the clip that plays inside the laptop on the AI Video screen ---- */
  var VIDEO='https://designstager.com/wp-content/uploads/2026/09/ds-hub-video-530-c0bORn.mp4';
  var vScene=root.querySelector('.dsh-s3'),
      vEl=root.querySelector('.dsh-vid'),
      vFrame=root.querySelector('.dsh-frame'),
      vBar=root.querySelector('.dsh-track i');
  var conn=navigator.connection||{};
  var canPlay=!!vEl&&window.matchMedia('(min-width:901px)').matches
              &&!window.matchMedia('(prefers-reduced-motion:reduce)').matches&&!conn.saveData;
  if(canPlay){
    vEl.addEventListener('playing',function(){vFrame.classList.add('is-playing');vScene.classList.add('is-live');});
    vEl.addEventListener('timeupdate',function(){
      if(vBar&&vEl.duration)vBar.style.width=Math.min(100,(vEl.currentTime/vEl.duration)*100)+'%';
    });
    vEl.addEventListener('error',function(){vFrame.classList.remove('is-playing');vScene.classList.remove('is-live');});
  }
  function videoPlay(){
    if(!canPlay)return;
    if(!vEl.getAttribute('src'))vEl.setAttribute('src',VIDEO);
    var p=vEl.play();if(p&&p.catch)p.catch(function(){});
  }
  function videoStop(){
    if(!vEl)return;
    try{vEl.pause();}catch(e){}
    vFrame.classList.remove('is-playing');vScene.classList.remove('is-live');
  }

  var scenes=root.querySelectorAll('.dsh-scene'),cards=root.querySelectorAll('.dsh-fc'),i=0,timer=null;
  var order=[0,1,2,3,5,6,4];
  function show(n){
    scenes.forEach(function(s){var on=+s.dataset.s===n;s.classList.toggle('is-on',on);if(on)fill(s);});
    cards.forEach(function(c){c.classList.toggle('is-on',+c.dataset.i===n);});
    if(n===2)videoPlay();else videoStop();
  }
  function next(){i=(i+1)%order.length;show(order[i]);}
  function start(){stop();if(!window.matchMedia('(prefers-reduced-motion:reduce)').matches)timer=setInterval(next,3000);}
  function stop(){if(timer){clearInterval(timer);timer=null;}}
  document.addEventListener('visibilitychange',function(){if(document.hidden)videoStop();});
  show(0);start();
  cards.forEach(function(c){
    c.addEventListener('mouseenter',function(){stop();i=order.indexOf(+c.dataset.i);show(+c.dataset.i);});
    c.addEventListener('mouseleave',start);
  });
  document.addEventListener('visibilitychange',function(){document.hidden?stop():start();});
})();
