
(function(){
'use strict';
var edit=null;

function slideOf(i){return (window.S&&window.S.data&&window.S.data.slides)?window.S.data.slides[i]:null;}
function clamp(v){return Math.max(0,Math.min(100,v));}
var savedPos={};
function commit(i,x,y){
  var p={x:Math.round(x*10)/10,y:Math.round(y*10)/10};
  savedPos[i]=p;
  var s=slideOf(i);if(s)s.image_position=p;
  if(window.G&&window.G.pptData&&window.G.pptData.slides&&window.G.pptData.slides[i])window.G.pptData.slides[i].image_position=p;
}

function applyAndClose(save){
  if(!edit)return;
  var i=edit.idx;
  if(edit.raf)cancelAnimationFrame(edit.raf);
  var sh=document.getElementById('dsp-crop-hide');if(sh)sh.remove();
  document.documentElement.style.overflow=edit.prevOvf||'';
  if(edit.ov&&edit.ov.parentNode)edit.ov.parentNode.removeChild(edit.ov);
  var wasEdit=edit; edit=null;
  if(save){
    commit(i,wasEdit.pos.x,wasEdit.pos.y);
    var v=(window.S&&window.S.choices&&window.S.choices['slide_'+i])||'v1';
    if(typeof window.dspRenderThumbs==='function')window.dspRenderThumbs();
    if(typeof window.applyCanvas==='function')window.applyCanvas(i,v);
    if(typeof window.dspToast==='function')window.dspToast('Crop applied!','success');
  }
}

/* the picture zone that is really on screen: slides with a key takeaway also carry a hidden copy of the zone, and measuring that one (0 x 0) made Crop do nothing */
function dsVisZone(i){var zs=document.querySelectorAll('#dsp-ppt-main-slide .dsp-vp-imgzone[data-slide-idx="'+i+'"]'),k;for(k=0;k<zs.length;k++){if(zs[k].getBoundingClientRect().width>2)return zs[k];}return null;}
function openCrop(i){
  applyAndClose(false);
  var s=slideOf(i);
  if(!s||!s.image_data){if(typeof window.dspToast==='function')window.dspToast('No image on this slide yet','error');return;}
  var main=document.getElementById('dsp-ppt-main-slide');if(!main)return;
  var zone=dsVisZone(i);if(!zone)return;
  var prevOvf=document.documentElement.style.overflow;
  document.documentElement.style.overflow='hidden';   // lock scroll BEFORE measuring
  var zr=zone.getBoundingClientRect();
  var zL=zr.left,zT=zr.top,zW=zr.width,zH=zr.height;
  if(zW<10||zH<10)return;
  var p=(s.image_position&&typeof s.image_position.x==='number')?s.image_position:{x:50,y:50};

  var probe=new Image();
  probe.onload=function(){
    var natW=probe.naturalWidth||1920,natH=probe.naturalHeight||1080;
    var scale=Math.max(zW/natW,zH/natH);          // cover the frame, PowerPoint-style
    var iW=natW*scale,iH=natH*scale;

    // Fixed overlay over the WHOLE viewport — image can extend past the slide
    var ov=document.createElement('div');
    ov.style.cssText='position:fixed;inset:0;z-index:2147483000;cursor:grab;user-select:none;-webkit-user-select:none;';

    var backdrop=document.createElement('div');
    backdrop.style.cssText='position:absolute;inset:0;background:rgba(0,0,0,.6);';
    ov.appendChild(backdrop);

    // ONE ghost = the full image, dimmed, wherever it extends
    var ghost=document.createElement('img');
    ghost.src=s.image_data;ghost.draggable=false;
    ghost.style.cssText='position:absolute;filter:brightness(.4);pointer-events:none;max-width:none;max-height:none;width:'+iW+'px;height:'+iH+'px;';
    ov.appendChild(ghost);

    // Bright part = the framed area only
    var clip=document.createElement('div');
    clip.style.cssText='position:absolute;left:'+zL+'px;top:'+zT+'px;width:'+zW+'px;height:'+zH+'px;overflow:hidden;pointer-events:none;';
    var bright=document.createElement('img');
    bright.src=s.image_data;bright.draggable=false;
    bright.style.cssText='position:absolute;pointer-events:none;max-width:none;max-height:none;width:'+iW+'px;height:'+iH+'px;';
    clip.appendChild(bright);
    ov.appendChild(clip);

    // White frame + corner handles
    var frame=document.createElement('div');
    frame.style.cssText='position:absolute;left:'+zL+'px;top:'+zT+'px;width:'+zW+'px;height:'+zH+'px;border:2px solid #fff;box-sizing:border-box;pointer-events:none;';
    [['top:-3px;left:-3px;border-width:3px 0 0 3px'],['top:-3px;right:-3px;border-width:3px 3px 0 0'],['bottom:-3px;left:-3px;border-width:0 0 3px 3px'],['bottom:-3px;right:-3px;border-width:0 3px 3px 0']].forEach(function(c){
      var d=document.createElement('div');
      d.style.cssText='position:absolute;width:16px;height:16px;border-color:#fff;border-style:solid;'+c[0]+';';
      frame.appendChild(d);
    });
    ov.appendChild(frame);

    var done=document.createElement('button');
    done.textContent='\u2713 Done';
    done.style.cssText='position:absolute;left:'+(zL+zW-150)+'px;top:'+Math.min(window.innerHeight-46,zT+zH+10)+'px;z-index:10;background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:13px;font-weight:700;cursor:pointer;';
    var cancel=document.createElement('button');
    cancel.textContent='\u2715';
    cancel.style.cssText='position:absolute;left:'+(zL+zW-42)+'px;top:'+Math.min(window.innerHeight-46,zT+zH+10)+'px;z-index:10;background:rgba(0,0,0,.65);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:6px;padding:8px 12px;font-size:13px;cursor:pointer;';
    ov.appendChild(done);ov.appendChild(cancel);

    // KEY FIX: hide the entire zone while cropping — no more double layers
    zone.style.visibility='hidden';

     var cropHide=document.createElement('style');
    cropHide.id='dsp-crop-hide';
    cropHide.textContent='#dsp-ppt-main-slide{visibility:hidden!important}#dsp-ppt-main-slide *{visibility:hidden!important}';
    document.head.appendChild(cropHide);
    document.body.appendChild(ov);
    edit={idx:i,ov:ov,pos:{x:p.x,y:p.y},zL:zL,zT:zT,zW:zW,zH:zH,iW:iW,iH:iH,
          natW:natW,natH:natH,clip:clip,frame:frame,raf:0,
          ghost:ghost,bright:bright,cropZone:zone,prevOvf:prevOvf,drag:null};
    place();

    // Sync loop: if the editor re-renders/moves the slide mid-crop,
    // re-hide the new zone image and re-align the overlay every frame
    edit.raf=requestAnimationFrame(function tick(){
      if(!edit)return;
      var z=dsVisZone(edit.idx);
      if(z){
        if(z.style.visibility!=='hidden'){
          edit.cropZone=z;
          z.style.visibility='hidden';
        }
        var r=z.getBoundingClientRect();
        if(r.width>10&&(Math.abs(r.left-edit.zL)>.5||Math.abs(r.top-edit.zT)>.5||Math.abs(r.width-edit.zW)>.5||Math.abs(r.height-edit.zH)>.5)){
          edit.zL=r.left;edit.zT=r.top;edit.zW=r.width;edit.zH=r.height;
          var sc=Math.max(edit.zW/edit.natW,edit.zH/edit.natH);
          edit.iW=edit.natW*sc;edit.iH=edit.natH*sc;
          edit.ghost.style.width=edit.iW+'px';edit.ghost.style.height=edit.iH+'px';
          edit.bright.style.width=edit.iW+'px';edit.bright.style.height=edit.iH+'px';
          edit.clip.style.left=edit.zL+'px';edit.clip.style.top=edit.zT+'px';edit.clip.style.width=edit.zW+'px';edit.clip.style.height=edit.zH+'px';
          edit.frame.style.left=edit.zL+'px';edit.frame.style.top=edit.zT+'px';edit.frame.style.width=edit.zW+'px';edit.frame.style.height=edit.zH+'px';
          place();
        }
      }
      edit.raf=requestAnimationFrame(tick);
    });

    done.addEventListener('mousedown',function(e){e.stopPropagation();});
    cancel.addEventListener('mousedown',function(e){e.stopPropagation();});
    done.addEventListener('click',function(e){e.stopPropagation();applyAndClose(true);});
    cancel.addEventListener('click',function(e){e.stopPropagation();applyAndClose(false);});

    ov.addEventListener('mousedown',startDrag);
    ov.addEventListener('touchstart',startDrag,{passive:false});
  };
  probe.onerror=function(){if(typeof window.dspToast==='function')window.dspToast('Image failed to load','error');};
  probe.src=s.image_data;
}

function place(){
  if(!edit)return;
  var offX=(edit.zW-edit.iW)*edit.pos.x/100;   // same math CSS uses for background-position %
  var offY=(edit.zH-edit.iH)*edit.pos.y/100;
  edit.ghost.style.left=(edit.zL+offX)+'px';
  edit.ghost.style.top=(edit.zT+offY)+'px';
  edit.bright.style.left=offX+'px';
  edit.bright.style.top=offY+'px';
}

function startDrag(e){
  if(!edit)return;
  var pt=e.touches?e.touches[0]:e;
  edit.drag={sx:pt.clientX,sy:pt.clientY,x0:edit.pos.x,y0:edit.pos.y,moved:false};
  edit.ov.style.cursor='grabbing';
  if(e.cancelable)e.preventDefault();
  e.stopPropagation();
}
function moveDrag(e){
  if(!edit||!edit.drag)return;
  var pt=e.touches?e.touches[0]:e;
  var dx=pt.clientX-edit.drag.sx,dy=pt.clientY-edit.drag.sy;
  if(Math.abs(dx)>3||Math.abs(dy)>3)edit.drag.moved=true;
  var dW=edit.zW-edit.iW,dH=edit.zH-edit.iH;
  edit.pos={
    x:dW?clamp(edit.drag.x0+dx*100/dW):edit.drag.x0,
    y:dH?clamp(edit.drag.y0+dy*100/dH):edit.drag.y0
  };
  place();
  if(e.cancelable)e.preventDefault();
}
function endDrag(e){
  if(!edit||!edit.drag)return;
  var moved=edit.drag.moved;
  edit.drag=null;
  edit.ov.style.cursor='grab';
  // Quick click (no drag) outside the frame = apply & close, like PowerPoint
  if(!moved&&e){
    var pt=(e.changedTouches&&e.changedTouches[0])||e;
    if(pt.clientX<edit.zL||pt.clientX>edit.zL+edit.zW||pt.clientY<edit.zT||pt.clientY>edit.zT+edit.zH){
      // swallow the click that fires right after this mouseup,
      // so it can't hit the editor underneath and trigger a re-render
      var stopGhost=function(ev){ev.stopImmediatePropagation();ev.preventDefault();document.removeEventListener('click',stopGhost,true);};
      document.addEventListener('click',stopGhost,true);
      setTimeout(function(){document.removeEventListener('click',stopGhost,true);},80);
      applyAndClose(true);
    }
  }
}
window.addEventListener('mousemove',moveDrag,true);
window.addEventListener('touchmove',moveDrag,{passive:false,capture:true});
window.addEventListener('mouseup',endDrag,true);
window.addEventListener('touchend',endDrag,true);

document.addEventListener('keydown',function(e){
  if(edit&&e.key==='Escape')applyAndClose(false);
  if(edit&&e.key==='Enter')applyAndClose(true);
});

/* Crop button — capture phase */
document.addEventListener('click',function(e){
  var b=e.target.closest&&e.target.closest('.dsp-vp-cropbtn');
  if(!b)return;
  e.preventDefault();e.stopImmediatePropagation();
  var z=b.closest('.dsp-vp-imgzone');
  if(!z||z.closest('#dsp-ppt-thumbs'))return;
  var i=parseInt(z.getAttribute('data-slide-idx'),10);
  if(!isNaN(i))openCrop(i);
},true);

/* Double-click image = crop */
document.addEventListener('dblclick',function(e){
  if(edit)return;
  var z=e.target.closest&&e.target.closest('.dsp-vp-imgzone');
  if(!z||z.closest('#dsp-ppt-thumbs'))return;
  var i=parseInt(z.getAttribute('data-slide-idx'),10);
  if(!isNaN(i)){e.preventDefault();e.stopImmediatePropagation();openCrop(i);}
},true);

/* Take over all old crop entry points */
window.vpStartCropEdit=function(i){openCrop(i);};
window.dspCropOpen=function(i){openCrop(i);};
window.vpImgOptionCrop=function(){if(window._vpOptIdx!==undefined&&window._vpOptIdx>=0)openCrop(window._vpOptIdx);};
	/* Guard: if anything re-renders the slide, re-apply the saved crop */
function reassert(){
  var main=document.getElementById('dsp-ppt-main-slide');
  if(!main)return;
  Object.keys(savedPos).forEach(function(k){
    var i=parseInt(k,10),p=savedPos[k];
    var s=slideOf(i);
    if(s)s.image_position=p;
    if(window.G&&window.G.pptData&&window.G.pptData.slides&&window.G.pptData.slides[i])window.G.pptData.slides[i].image_position=p;
    var z=dsVisZone(i);
    if(z&&z.firstElementChild){
      var want=p.x+'% '+p.y+'%';
      if(z.firstElementChild.style.backgroundPosition!==want)z.firstElementChild.style.backgroundPosition=want;
    }
  });
}
var _cropMo=new MutationObserver(function(){if(!edit)reassert();});
var _cropMoWait=setInterval(function(){
  var main=document.getElementById('dsp-ppt-main-slide');
  if(main){clearInterval(_cropMoWait);_cropMo.observe(main,{childList:true,subtree:true});}
},400);
})();
