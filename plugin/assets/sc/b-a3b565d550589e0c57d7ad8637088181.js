try{

/* ============================================================
   DS TEMPLATE ENGINE — core (renderer, text fitting, layouts)
   Coordinate space: 1280 x 720 px (1 px = 9525 EMU in PPTX)
   Every layout produces a list of primitive elements. The same
   list is turned into the editor preview (HTML) and into the
   PPTX (PHP), so preview and download always match.
   ============================================================ */
(function(){
'use strict';
if (window.DSTE && window.DSTE.core) return;
var W = 1280, H = 720;
var DSTE = window.DSTE = window.DSTE || {};
DSTE.core = 1; DSTE.W = W; DSTE.H = H;
DSTE.reg = DSTE.reg || {}; DSTE.order = DSTE.order || [];

/* ---------------- small utils ---------------- */
function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function strip(s){return String(s==null?'':s).replace(/\*\*([^*]+)\*\*/g,'$1').replace(/\*([^*]+)\*/g,'$1').replace(/^#+\s*/gm,'').replace(/`([^`]+)`/g,'$1').replace(/__([^_]+)__/g,'$1').trim();}
function hx(c){c=String(c||'').replace('#','').trim();if(c.length===3)c=c[0]+c[0]+c[1]+c[1]+c[2]+c[2];return /^[0-9a-fA-F]{6}$/.test(c)?('#'+c.toUpperCase()):'#000000';}
function rgb(c){c=hx(c).slice(1);return [parseInt(c.slice(0,2),16),parseInt(c.slice(2,4),16),parseInt(c.slice(4,6),16)];}
function toHex(a){return '#'+a.map(function(v){v=Math.max(0,Math.min(255,Math.round(v)));return (v<16?'0':'')+v.toString(16);}).join('').toUpperCase();}
function mix(a,b,t){var x=rgb(a),y=rgb(b);return toHex([x[0]+(y[0]-x[0])*t,x[1]+(y[1]-x[1])*t,x[2]+(y[2]-x[2])*t]);}
function lum(c){var x=rgb(c);return (0.299*x[0]+0.587*x[1]+0.114*x[2])/255;}
function rgba(c,op){var x=rgb(c);return 'rgba('+x[0]+','+x[1]+','+x[2]+','+(op==null?1:op)+')';}
function pad2(n){return (n<10?'0':'')+n;}
function clamp(v,a,b){return Math.max(a,Math.min(b,v));}
DSTE.u = {esc:esc,strip:strip,hx:hx,mix:mix,lum:lum,rgba:rgba,pad2:pad2,clamp:clamp};

/* ---------------- content parsing ---------------- */
function splitItem(str){
  str = strip(str).replace(/^\s*(\d{1,2}[\.\)]|[-•*])\s+/,'');
  var h='', d=str, x='';
  var ci = str.indexOf(':');
  if (ci > 0 && ci < 70) { h = str.slice(0,ci).trim(); d = str.slice(ci+1).trim(); }
  var pi = d.indexOf(' | ');
  if (pi >= 0) { x = d.slice(pi+3).trim(); d = d.slice(0,pi).trim(); }
  return {h:h, d:d, x:x};
}
function joinItem(p){
  var s = p.h ? (p.h + (p.d||p.x ? ': ' : '')) : '';
  s += (p.d||'');
  if (p.x) s += ' | ' + p.x;
  return s;
}
DSTE.splitItem = splitItem; DSTE.joinItem = joinItem;

function items(slide){
  var c = Array.isArray(slide.content) ? slide.content : [];
  var out = [];
  c.forEach(function(it,i){ var s=String(it==null?'':it).trim(); if(!s) return; var p=splitItem(s); p.i=i; out.push(p); });
  return out;
}
function titleFields(slide){
  var o = {company:'',presenter:'',designation:'',date:'',website:'',tagline:'',email:'',phone:'',address:''};
  (slide && Array.isArray(slide.content) ? slide.content : []).forEach(function(it){
    var s=String(it||''); var ci=s.indexOf(':'); if(ci<=0) return;
    var k=s.slice(0,ci).trim().toLowerCase(), v=s.slice(ci+1).trim();
    if (k==='e-mail') k='email';
    if (o.hasOwnProperty(k)) o[k]=v;
  });
  return o;
}
DSTE.items = items; DSTE.titleFields = titleFields;

function kindOf(slide){
  var t = String(slide && slide.type || 'bullet');
  if (t==='chart') return 'chart';
  if (t==='title') return 'cover';
  if (t==='agenda') return 'agenda';
  if (t==='closing') return 'closing';
  if (t==='quote') return 'quote';
  if (t==='stats') return 'stats';
  if (t==='timeline') return 'timeline';
  if (t==='section') return 'section';
  var n = items(slide).length;
  if (n <= 1) return 'text';
  if (n === 2) return 'p2';
  if (n === 3) return 'p3';
  if (n === 4) return 'p4';
  if (n <= 6) return 'p56';
  if (n <= 8) return 'p78';
  return 'p9'; /* 9-16 points */
}
DSTE.kindOf = kindOf;

/* ---------------- icons ---------------- */
var ICON_KW = [
 [/team|people|staff|talent|hire|hiring|member|employee|human/,'mdi:account-group'],
 [/customer|client|user|audience|buyer|persona/,'mdi:account-heart'],
 [/revenue|profit|money|cash|price|pricing|cost|fund|budget|financ|invest|capital|\$/,'mdi:cash-multiple'],
 [/growth|increase|scale|expan|grow|rise/,'mdi:trending-up'],
 [/market|share|segment|competit/,'mdi:chart-pie'],
 [/data|analytic|metric|insight|report|measure|kpi/,'mdi:chart-bar'],
 [/ai|machine|model|intelligen|neural|algorithm|smart/,'mdi:brain'],
 [/secur|privacy|protect|safe|trust|complian|risk/,'mdi:shield-check'],
 [/cloud|platform|infra|server|hosting/,'mdi:cloud-outline'],
 [/tech|software|digital|app|code|develop|engineer|system|tool|automat/,'mdi:cog-outline'],
 [/speed|fast|efficien|quick|agil|perform/,'mdi:lightning-bolt'],
 [/goal|target|focus|objective|aim|mission/,'mdi:target'],
 [/idea|innovat|creativ|concept|vision/,'mdi:lightbulb-on-outline'],
 [/global|world|international|region|country|expansion/,'mdi:earth'],
 [/time|schedule|deadline|timeline|plan|roadmap|phase/,'mdi:calendar-clock'],
 [/partner|collab|deal|allian|relationship|network/,'mdi:handshake-outline'],
 [/sustain|green|eco|environment|energy|climate|carbon/,'mdi:leaf'],
 [/health|care|wellness|medical|patient/,'mdi:heart-pulse'],
 [/learn|educat|train|skill|knowledge|course/,'mdi:school-outline'],
 [/market(ing)?|brand|campaign|social|content|advert|media|promot/,'mdi:bullhorn-outline'],
 [/communicat|message|email|chat|support|service/,'mdi:message-text-outline'],
 [/quality|award|best|excellen|win|success|achiev/,'mdi:trophy-outline'],
 [/launch|start|mvp|rocket|begin/,'mdi:rocket-launch-outline'],
 [/product|feature|design|build/,'mdi:cube-outline'],
 [/operat|process|workflow|logistic|supply|manufactur/,'mdi:factory'],
 [/legal|regulat|policy|govern|law/,'mdi:scale-balance'],
 [/mobile|phone/,'mdi:cellphone'],
 [/search|research|discover|explor/,'mdi:magnify'],
 [/problem|challenge|issue|pain|barrier/,'mdi:alert-circle-outline'],
 [/solution|solve|fix|answer|resolve/,'mdi:puzzle-outline'],
 [/check|complete|done|result|outcome|deliver/,'mdi:check-decagram-outline']
];
var ICON_DEF = ['mdi:lightbulb-on-outline','mdi:chart-line','mdi:target','mdi:rocket-launch-outline','mdi:shield-check-outline','mdi:account-group-outline','mdi:cog-outline','mdi:star-four-points-outline'];
function iconFor(slide, i, text){
  var ic = slide && Array.isArray(slide.icons) ? slide.icons[i] : null;
  if (ic && /^[a-z0-9-]+:[a-z0-9-]+$/.test(String(ic).trim())) return String(ic).trim();
  var t = String(text||'').toLowerCase();
  for (var k=0;k<ICON_KW.length;k++){ if (ICON_KW[k][0].test(t)) return ICON_KW[k][1]; }
  return ICON_DEF[i % ICON_DEF.length];
}
DSTE.iconFor = iconFor;

/* ---------------- text measurement + fitting ---------------- */
var _cx = null, _fitCache = {};
function cx(){ if(_cx===null){ try{ _cx=document.createElement('canvas').getContext('2d'); }catch(e){ _cx=false; } } return _cx; }
function measure(txt, size, fam, wt){
  var c = cx();
  if (!c) return txt.length * size * 0.52;
  c.font = (wt||400) + ' ' + size + 'px "' + fam + '", Arial, sans-serif';
  return c.measureText(txt).width;
}
function countLines(text, w, size, fam, wt, caps, ls){
  var paras = String(text).split('\n'), n = 0, over = false;
  var lsPx = (ls||0) * size;
  var spaceW = measure(' ', size, fam, wt) + lsPx;
  for (var p=0;p<paras.length;p++){
    var words = (caps ? paras[p].toUpperCase() : paras[p]).split(/\s+/).filter(Boolean);
    if (!words.length){ n++; continue; }
    var line = 0; n++;
    for (var i=0;i<words.length;i++){
      var ww = measure(words[i], size, fam, wt) + lsPx*words[i].length;
      if (ww > w) over = true;
      if (line === 0) line = ww;
      else if (line + spaceW + ww <= w) line += spaceW + ww;
      else { n++; line = ww; }
    }
  }
  return {n:n, over:over};
}
/* o: {text,w,h,max,min,font(family),wt,lh,caps,ls,maxLines} -> {s,n} */
function fit(o){
  var text = String(o.text||'');
  var key = [text,o.w,o.h,o.max,o.min,o.font,o.wt,o.lh,o.caps?1:0,o.ls||0,o.maxLines||0].join('|');
  if (_fitCache[key]) return _fitCache[key];
  var w = o.w * 0.94, lh = o.lh || 1.2, res = null;
  for (var s = o.max; s >= o.min; s -= (s > 30 ? 2 : 1)) {
    var r = countLines(text, w, s, o.font, o.wt, o.caps, o.ls);
    if (!r.over && r.n * s * lh <= o.h + 0.5 && (!o.maxLines || r.n <= o.maxLines)) { res = {s:s, n:r.n}; break; }
  }
  if (!res) { var r2 = countLines(text, w, o.min, o.font, o.wt, o.caps, o.ls); res = {s:o.min, n:r2.n}; }
  _fitCache[key] = res;
  return res;
}
DSTE.fit = fit;
DSTE.clearFit = function(){ _fitCache = {}; };

/* ---------------- render context ---------------- */
function Ctx(T, slide, idx, vid, deck){
  this.T = T; this.P = T.pal; this.S = T.s || {};
  this.s = slide || {}; this.i = idx; this.vid = vid; this.deck = deck || null;
  this.els = []; this.bgFill = {fill:T.pal.bg};
  this.kind = kindOf(this.s);
  this.it = items(this.s);
  this.total = deck && deck.slides ? deck.slides.length : 0;
  this.cover = deck && deck.slides && deck.slides[0] ? titleFields(deck.slides[0]) : titleFields(this.s);
}
var CP = Ctx.prototype;
CP.font = function(k){ return k==='head' ? this.T.fonts.head : this.T.fonts.body; };
CP.add = function(e){ this.els.push(e); return e; };
CP.bg = function(o){ this.bgFill = o; };
CP.rect = function(x,y,w,h,o){ o=o||{}; return this.add({t:'rect',x:x,y:y,w:w,h:h,fill:o.fill,op:o.op,r:o.r,line:o.line,lw:o.lw,lop:o.lop,grad:o.grad,shadow:o.shadow,dash:o.dash}); };
CP.ell = function(x,y,w,h,o){ o=o||{}; return this.add({t:'ell',x:x,y:y,w:w,h:h,fill:o.fill,op:o.op,line:o.line,lw:o.lw,lop:o.lop,grad:o.grad,shadow:o.shadow,dash:o.dash}); };
CP.shape = function(prst,x,y,w,h,o){ o=o||{}; return this.add({t:'shape',prst:prst,x:x,y:y,w:w,h:h,fill:o.fill,op:o.op,line:o.line,lw:o.lw,lop:o.lop,grad:o.grad,rot:o.rot}); };
CP.line = function(x1,y1,x2,y2,o){ o=o||{}; return this.add({t:'line',x1:x1,y1:y1,x2:x2,y2:y2,color:o.color,lw:o.lw||1,op:o.op,dash:o.dash}); };
CP.path = function(d,o){ o=o||{}; return this.add({t:'path',d:d,stroke:o.stroke,lw:o.lw,fill:o.fill,op:o.op,lop:o.lop}); };
CP.icon = function(x,y,sz,name,color){ return this.add({t:'icon',x:x,y:y,w:sz,h:sz,name:name,color:hx(color)}); };
CP.ring = function(x,y,d,pct,o){ o=o||{}; return this.add({t:'ring',x:x,y:y,w:d,h:d,pct:clamp(pct,0,100),color:o.color,track:o.track,th:o.th||12,top:o.top}); };
CP.img = function(x,y,w,h,o){ o=o||{}; return this.add({t:'img',x:x,y:y,w:w,h:h,r:o.r||0,ph:o.ph||null,shape:o.shape||null}); };
/* text. o: {size|fit:{max,min}, font:'head'|'body', wt, color, align, va, lh, caps, ls, italic, op, ed:{f,i,p}, maxLines} */
CP.text = function(x,y,w,h,text,o){
  o=o||{}; text = strip(text);
  var fam = this.font(o.font||'body');
  var wt = o.wt || (o.font==='head' ? this.T.fonts.headW : this.T.fonts.bodyW) || 400;
  var lh = o.lh || (o.font==='head' ? (this.T.fonts.headLh||1.1) : 1.35);
  var caps = o.caps!=null ? o.caps : (o.font==='head' ? !!this.T.fonts.headCaps : false);
  var ls = o.ls!=null ? o.ls : (o.font==='head' ? (this.T.fonts.headLs||0) : 0);
  var size = o.size, n = 1;
  if (!size || o.fit) {
    var f = o.fit || {max:size, min:Math.max(9,Math.round(size*0.6))};
    var r = fit({text:text,w:w,h:h,max:f.max,min:f.min,font:fam,wt:wt,lh:lh,caps:caps,ls:ls,maxLines:o.maxLines});
    size = r.s; n = r.n;
  } else {
    n = countLines(text, w*0.94, size, fam, wt, caps, ls).n;
  }
  var e = this.add({t:'text',x:x,y:y,w:w,h:h,text:text,size:size,font:(o.font||'body'),fam:fam,wt:wt,color:hx(o.color||this.P.text),align:o.align||'l',va:o.va||'t',lh:lh,caps:caps,ls:ls,italic:!!o.italic,op:o.op,ed:o.ed||null,nw:o.maxLines===1?1:0});
  e._n = n; e._h = Math.min(h, n*size*lh);
  return e;
};
/* measure only (no element) */
CP.measureText = function(text,w,o){
  o=o||{}; var fam=this.font(o.font||'body');
  var wt=o.wt||(o.font==='head'?this.T.fonts.headW:this.T.fonts.bodyW)||400;
  var lh=o.lh||(o.font==='head'?(this.T.fonts.headLh||1.1):1.35);
  var caps=o.caps!=null?o.caps:(o.font==='head'?!!this.T.fonts.headCaps:false);
  var ls=o.ls!=null?o.ls:(o.font==='head'?(this.T.fonts.headLs||0):0);
  var r=fit({text:strip(text),w:w,h:o.h||9999,max:o.max,min:o.min,font:fam,wt:wt,lh:lh,caps:caps,ls:ls,maxLines:o.maxLines});
  return {s:r.s,n:r.n,h:r.n*r.s*lh,lh:lh};
};
/* common fit size for a group of texts in equal boxes */
CP.groupFit = function(texts,w,h,o){
  var self=this, s=o.max;
  texts.forEach(function(t){ if(!t) return; var r=self.measureText(t,w,{font:o.font,wt:o.wt,lh:o.lh,caps:o.caps,ls:o.ls,max:o.max,min:o.min,h:h,maxLines:o.maxLines}); if(r.s<s) s=r.s; });
  return s;
};
DSTE.Ctx = Ctx;

/* ============================================================
   STYLE PIECES (driven by template tokens T.s)
   ============================================================ */
/* card background */
function card(C,x,y,w,h,o){
  o=o||{}; var S=C.S.card||{}, P=C.P, r=S.r!=null?S.r:16, k=o.kind||S.kind||'solid';
  var hl = !!o.hl;
  if (hl && S.hl) { k = S.hl.kind || 'solid'; }
  switch(k){
    case 'none': break;
    case 'rule':  C.rect(x,y,w,3,{fill:hl?P.accent2||P.accent:P.accent}); break;
    case 'outline': C.rect(x,y,w,h,{fill:S.fill||null,op:S.op,line:S.line||P.line,lw:S.lw||1.5,r:r}); break;
    case 'glass': C.rect(x,y,w,h,{fill:S.fill||'#FFFFFF',op:S.op!=null?S.op:0.42,line:S.line||'#FFFFFF',lop:S.lop!=null?S.lop:0.7,lw:S.lw||1.2,r:r,shadow:S.shadow}); break;
    case 'grad': C.rect(x,y,w,h,{grad:hl&&S.hl&&S.hl.grad?S.hl.grad:(S.grad||{ang:135,stops:[[0,P.card],[100,mix(P.card,P.accent,0.25)]]}),r:r,line:S.line,lw:S.lw,lop:S.lop,shadow:S.shadow}); break;
    case 'bar': C.rect(x,y,w,h,{fill:S.fill||P.card,op:S.op,r:r,shadow:S.shadow}); C.rect(x,y,S.barW||6,h,{fill:P.accent,r:0}); break;
    case 'top': C.rect(x,y,w,h,{fill:S.fill||P.card,op:S.op,r:r,shadow:S.shadow,line:S.line,lw:S.lw}); C.rect(x,y,w,S.barW||5,{fill:P.accent}); break;
    default: C.rect(x,y,w,h,{fill:hl&&S.hl&&S.hl.fill?S.hl.fill:(S.fill||P.card),op:S.op,r:r,line:S.line,lw:S.lw,lop:S.lop,shadow:S.shadow});
  }
  var dark = hl && S.hl ? (S.hl.dark!=null?S.hl.dark:true) : (S.dark!=null ? S.dark : lum(S.fill||P.card) < 0.45 && k!=='none' && k!=='rule');
  return {hl: hl&&!!S.hl, tc: hl&&S.hl&&S.hl.tc ? S.hl.tc : (S.tc || (dark ? '#FFFFFF' : P.text)), mc: hl&&S.hl&&S.hl.mc ? S.hl.mc : (S.mc || (dark ? mix('#FFFFFF',S.fill||P.card,0.28) : P.muted))};
}
DSTE.card = card;

/* icon mark (icon inside a shape) */
function mark(C,x,y,sz,name,o){
  o=o||{}; var S=C.S.icon||{}, P=C.P, k=o.kind||S.kind||'circle';
  var bg=o.bg||S.bg||P.accent, fg=o.fg||S.fg||P.onAccent||'#FFFFFF';
  var isz=Math.round(sz*(S.scale||0.52));
  var ix=x+(sz-isz)/2, iy=y+(sz-isz)/2;
  var gr=o.bg?null:S.grad;
  if (k==='circle') C.ell(x,y,sz,sz,{fill:bg,grad:gr});
  else if (k==='square') C.rect(x,y,sz,sz,{fill:bg,r:S.r!=null?S.r:Math.round(sz*0.26),grad:gr});
  else if (k==='ring') { C.ell(x,y,sz,sz,{line:bg,lw:S.lw||2}); fg=o.fg||S.fg||bg; }
  else if (k==='soft') { C.ell(x,y,sz,sz,{fill:bg,op:S.op||0.14}); fg=o.fg||S.fg||bg; }
  else if (k==='softsq') { C.rect(x,y,sz,sz,{fill:bg,op:S.op||0.14,r:S.r!=null?S.r:Math.round(sz*0.26)}); fg=o.fg||S.fg||bg; }
  else if (k==='hex') C.shape('hexagon',x,y,sz,sz,{fill:bg});
  else { isz=sz; ix=x; iy=y; fg=o.fg||S.fg||bg; }
  C.icon(ix,iy,isz,name,fg);
}
DSTE.mark = mark;

/* number badge */
function num(C,x,y,sz,n,o){
  o=o||{}; var S=C.S.num||{}, P=C.P, k=o.kind||S.kind||'circle';
  var bg=o.bg||S.bg||P.accent, fg=o.fg||S.fg||P.onAccent||'#FFFFFF', t=pad2(n);
  if (k==='big') { C.text(x,y,sz*2,sz,t,{font:'head',size:Math.round(sz*0.9),color:o.fg||S.fg||P.accent,wt:C.T.fonts.headW,lh:1,va:'m',caps:false,ls:0}); return; }
  if (k==='circle') C.ell(x,y,sz,sz,{fill:bg});
  else if (k==='square') C.rect(x,y,sz,sz,{fill:bg,r:S.r!=null?S.r:6});
  else if (k==='ring') { C.ell(x,y,sz,sz,{line:bg,lw:2}); fg=o.fg||S.fg||bg; }
  else if (k==='pill') C.rect(x,y,sz*1.5,sz,{fill:bg,r:sz/2});
  var w = k==='pill'?sz*1.5:sz;
  C.text(x,y,w,sz,t,{font:S.font||'head',size:Math.round(sz*0.42),color:fg,align:'c',va:'m',lh:1,wt:700,caps:false,ls:0});
}
DSTE.num = num;

/* ---------------- header ---------------- */
function header(C,A,o){
  o=o||{}; var S=C.S.header||{}, P=C.P, T=C.T;
  var k=o.hk||S.kind||'left', y=A.top!=null?A.top:(S.top||56), x=A.x, w=A.w;
  var tMax=o.tMax||S.tMax||44, tMin=S.tMin||26, sMax=S.sMax||19, sMin=S.sMin||13;
  var title=C.s.title||'', sub=C.s.subtitle||'';
  var tc=o.tc||S.tc||P.title||P.text, sc=o.sc||S.sc||P.muted, al=k==='center'?'c':'l';
  if (S.kicker && !o.noKicker) {
    var kt = typeof S.kicker==='function' ? S.kicker(C) : pad2(C.i+1);
    if (kt) {
      if (S.kPill) {
        var ks=S.kSize||12, kw=Math.min(w, C.measureText(kt, 2000, {font:'body', wt:700, max:ks, min:ks, caps:true, ls:0.14}).s*0 + String(kt).length*ks*0.72+34);
        var kx=al==='c'?x+(w-kw)/2:x;
        C.rect(kx,y,kw,28,{fill:S.kPillFill||null,op:S.kPillOp,line:S.kPill===true?(S.kc||P.accent):S.kPill,lw:1.2,r:14});
        C.text(kx,y,kw,28,kt,{font:'body',size:ks,wt:700,color:S.kc||P.accent,align:'c',va:'m',caps:true,ls:0.14,lh:1});
        y+=S.kGap||44;
      } else { C.text(x,y,w,18,kt,{font:'body',size:S.kSize||13,wt:700,color:S.kc||P.accent,align:al,caps:true,ls:0.18,lh:1}); y+=S.kGap||28; }
    }
  }
  if (S.bar && !o.noBar) {
    var bw=S.barW||56; C.rect(al==='c'?x+(w-bw)/2:x,y,bw,S.barH||5,{fill:S.barC||P.accent,r:S.barR!=null?S.barR:3}); y+=S.barGap||22;
  }
  if (k==='split') {
    var tw=Math.round(w*0.5);
    var te=C.text(x,y,tw,S.tH||118,title,{font:'head',fit:{max:tMax,min:tMin},color:tc,ed:{f:'title'},maxLines:3});
    var sw=w-tw-40;
    if (sub) C.text(x+tw+40,y,sw,te._h,sub,{font:'body',fit:{max:sMax,min:sMin},color:sc,va:'b',ed:{f:'subtitle'},lh:1.4});
    return y+te._h;
  }
  var te2=C.text(x,y,w,S.tH||(o.tH||104),title,{font:'head',fit:{max:tMax,min:tMin},color:tc,align:al,ed:{f:'title'},maxLines:2});
  y+=te2._h+(S.gap||12);
  if (sub && !o.noSub) { var se=C.text(x,y,w,S.sH||52,sub,{font:'body',fit:{max:sMax,min:sMin},color:sc,align:al,ed:{f:'subtitle'},lh:1.4,maxLines:2}); y+=se._h; }
  if (S.rule && !o.noRule) { y+=16; C.rect(al==='c'?x+w/2-40:x,y,al==='c'?80:w,S.ruleH||1.5,{fill:S.ruleC||P.line,op:S.ruleOp}); }
  return y;
}
DSTE.header = header;

/* ---------------- conclusion strip ---------------- */
function conclusion(C,x,y,w,h,o){
  var txt = C.s.conclusion; if (!txt) return;
  o=o||{}; var S=C.S.conc||{}, P=C.P, k=o.kind||S.kind||'bar';
  var label = S.label!=null ? S.label : 'Key takeaway';
  var tc=S.tc||P.text, lc=S.lc||P.accent, pad=S.pad||20, tx=x, tw=w;
  if (k==='band') { C.rect(x,y,w,h,{fill:S.fill||P.accent,op:S.op,r:S.r!=null?S.r:0,grad:S.grad}); tc=S.tc||P.onAccent||'#FFFFFF'; lc=S.lc||tc; tx=x+pad; tw=w-pad*2; }
  else if (k==='pill') { C.rect(x,y,w,h,{fill:S.fill||P.accent,op:S.op!=null?S.op:0.12,r:h/2,line:S.line,lw:S.lw}); tx=x+h/2; tw=w-h; }
  else if (k==='box') { C.rect(x,y,w,h,{fill:S.fill||P.card,op:S.op,r:S.r!=null?S.r:10,line:S.line,lw:S.lw}); tx=x+pad; tw=w-pad*2; }
  else if (k==='line') { C.rect(x,y,w,1.5,{fill:S.lineC||P.line}); y+=8; h-=8; }
  else { C.rect(x,y+6,5,h-12,{fill:S.barC||P.accent,r:2}); tx=x+22; tw=w-22; }
  var lw=0;
  if (label) {
    var lbw = Math.min(190, Math.max(110, label.length*9.2+20));
    if (S.chip) { C.rect(tx,y+(h-26)/2,lbw,26,{fill:lc,r:13}); C.text(tx,y+(h-26)/2,lbw,26,label,{font:'body',size:11.5,wt:700,color:S.chipTc||P.onAccent||'#FFFFFF',caps:true,ls:0.12,align:'c',va:'m',lh:1}); }
    else C.text(tx,y,lbw,h,label,{font:'body',size:12,wt:800,color:lc,caps:true,ls:0.14,va:'m',lh:1.1});
    lw=lbw+14;
  }
  C.text(tx+lw,y+4,tw-lw,h-8,txt,{font:'body',fit:{max:S.max||17,min:12},color:tc,va:'m',italic:!!S.italic,wt:S.wt||500,lh:1.3,ed:{f:'x-conc'},maxLines:2});
}
DSTE.conclusion = conclusion;

/* ---------------- footer ---------------- */
function footer(C){
  var S=C.S.footer; if (S===false) return;
  S=S||{}; var P=C.P, fc=S.color||P.muted, M=C.S.margin||64, y=S.y||(H-34);
  if (typeof S.fn==='function') return S.fn(C);
  var brand = C.cover.company || '';
  if (brand && S.brand!==false) C.text(M,y,500,18,brand,{font:'body',size:12,wt:700,color:fc,caps:true,ls:0.12,lh:1,va:'m'});
  if (S.page!==false) C.text(W-M-120,y,120,18,(S.pagePrefix!=null?S.pagePrefix:'')+pad2(C.i+1),{font:'body',size:12,wt:700,color:fc,align:'r',ls:0.1,lh:1,va:'m'});
}
DSTE.footer = footer;

/* image zone helper: bleed or framed */
function imgBox(C,x,y,w,h,o){
  o=o||{}; var S=C.S, P=C.P;
  var ph = S.imgPh || {grad:{ang:135,stops:[[0,mix(P.accent,P.bg,0.35)],[100,mix(P.accent2||P.accent,P.bg,0.65)]]}};
  return C.img(x,y,w,h,{r:o.r!=null?o.r:0,ph:ph,shape:o.shape});
}
DSTE.imgBox = imgBox;

/* ============================================================
   BODY LAYOUTS  (C, Z={x,y,w,h}, o)
   ============================================================ */
var B = DSTE.B = {};

function markOrNum(C,x,y,sz,it,k,o,cc){
  var inv = cc && cc.hl ? {bg:cc.tc, fg:(C.S.card&&C.S.card.hl&&(C.S.card.hl.fill||(C.S.card.hl.grad&&C.S.card.hl.grad.stops[0][1])))||C.P.accent} : null;
  if ((o.mark||C.S.mark||'icon')==='num') num(C,x,y,sz,k+1,inv?{bg:inv.bg,fg:(C.S.num&&C.S.num.kind==='big'||C.S.num&&C.S.num.kind==='ring')?inv.bg:inv.fg}:o.numO);
  else mark(C,x,y,sz,iconFor(C.s,it.i,it.h+' '+it.d),inv?((C.S.icon&&/plain|ring|soft/.test(C.S.icon.kind||''))?{fg:inv.bg}:inv):o.markO);
}

/* cards in one row (2-5 items), icon top — content fills / centres in the card */
B.cols = function(C,Z,o){
  var it=C.it.slice(0,o.max||6), n=it.length; if(!n) return;
  var gap=o.gap||24, cw=(Z.w-gap*(n-1))/n, P=C.P, S=C.S;
  var flat=S.card&&(S.card.kind==='none'||S.card.kind==='rule');
  var pad=o.pad!=null?o.pad:(flat?0:28);
  var ms=o.ms||(n>=4?50:58), innerW=cw-pad*2;
  var topY = pad + (S.card&&S.card.kind==='rule'?20:0);
  var hMax=o.hMax||(n>=5?19:(n===4?21:(n===3?25:28))), dMax=o.dMax||(n>=5?15:(n===4?17:(n===3?20:22)));
  var hs=C.groupFit(it.map(function(t){return t.h;}),innerW,70,{font:'head',max:hMax,min:14,maxLines:2});
  var hh=0; it.forEach(function(t){var m=C.measureText(t.h,innerW,{font:'head',max:hs,min:hs});hh=Math.max(hh,m.h);});
  var dAvail=Z.h-topY-ms-18-hh-12-pad;
  var ds=C.groupFit(it.map(function(t){return t.d;}),innerW,dAvail,{font:'body',max:dMax,min:11,lh:1.45});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,innerW,{font:'body',max:ds,min:ds,lh:1.45}).h);});
  var slack=Math.max(0,dAvail-dh), off=o.top?0:Math.round(slack*(o.center?0.5:0.42));
  it.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), hl=o.hl==='first'?k===0:(o.hl==='alt'?k%2===1:false);
    var cc=card(C,x,Z.y,cw,Z.h,{hl:hl});
    var al=o.center?'c':'l', y=Z.y+topY+off;
    var mx=o.center?x+(cw-ms)/2:x+pad;
    markOrNum(C,mx,y,ms,t,k,o,cc);
    if (t.h) C.text(x+pad,y+ms+18,innerW,hh,t.h,{font:'head',size:hs,color:cc.tc,align:al,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x+pad,y+ms+18+hh+12,innerW,dh+4,t.d,{font:'body',size:ds,color:cc.mc,align:al,ed:{f:'x-part',i:t.i,p:1},lh:1.45});
  });
};

/* grid of cards: icon left + text (or stacked when narrow), centred vertically */
B.grid = function(C,Z,o){
  var it=C.it.slice(0,8), n=it.length; if(!n) return;
  var cols=o.cols||(n<=4?2:(n<=6?3:4)), rows=Math.ceil(n/cols);
  var gx=o.gap||22, gy=o.gapY||20, cw=(Z.w-gx*(cols-1))/cols, ch=(Z.h-gy*(rows-1))/rows;
  var pad=o.pad||(ch>160?26:20), ms=o.ms||(ch>170?54:(ch>120?46:40));
  var stacked = cols>=4 || cw<330;
  var tx=stacked?pad:pad+ms+18, tw=cw-tx-pad;
  var hMax=o.hMax||(n<=4?23:(n<=6?19:17)), dMax=o.dMax||(n<=4?19:(n<=6?16:14));
  var hs=C.groupFit(it.map(function(t){return t.h;}),tw,60,{font:'head',max:hMax,min:13,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,tw,{font:'head',max:hs,min:hs}).h);});
  var head=(stacked?ms+14:0)+hh+8, dAvail=ch-pad*2-head;
  var ds=C.groupFit(it.map(function(t){return t.d;}),tw,dAvail,{font:'body',max:dMax,min:10.5,lh:1.42});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,tw,{font:'body',max:ds,min:ds,lh:1.42}).h);});
  var off=Math.round(Math.max(0,dAvail-dh)*0.45);
  it.forEach(function(t,k){
    var r=Math.floor(k/cols), c=k%cols, inRow=Math.min(cols,n-r*cols);
    var shift = inRow<cols ? (cols-inRow)*(cw+gx)/2 : 0;
    var x=Z.x+shift+c*(cw+gx), y=Z.y+r*(ch+gy);
    var cc=card(C,x,y,cw,ch,{hl:o.hl==='first'?k===0:(o.hl==='alt'?k%2===1:false)});
    var cy=y+pad+off;
    markOrNum(C,x+pad,stacked?cy:cy-2,ms,t,k,o,cc);
    var hy=stacked?cy+ms+14:cy;
    if(t.h) C.text(x+tx,hy,tw,hh,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x+tx,hy+hh+8,tw,dh+4,t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.42});
  });
};

/* vertical list rows (one or two columns) */
B.list = function(C,Z,o){
  var it=C.it.slice(0,o.max||16), n=it.length; if(!n) return;
  var cols=o.cols||(n>5?2:1), per=Math.ceil(n/cols), gx=36, cw=(Z.w-gx*(cols-1))/cols;
  var gy=o.gap!=null?o.gap:(o.cards?14:10), rh=(Z.h-gy*(per-1))/per, ms=o.ms||Math.min(46,rh-12);
  var pad=o.cards?18:0, tx=pad+ms+18, tw=cw-tx-pad;
  var inline = rh < 76;
  var hs=C.groupFit(it.map(function(t){return t.h;}),inline?tw*0.36:tw,32,{font:'head',max:o.hMax||clamp(Math.round(rh*0.2),16,24),min:13,maxLines:inline?2:1});
  var hH = inline?0:hs*1.2+4;
  var ds=C.groupFit(it.map(function(t){return t.d;}),inline?tw*0.62:tw,rh-hH-(o.cards?20:4),{font:'body',max:o.dMax||clamp(Math.round(rh*0.19),14,21),min:10.5});
  it.forEach(function(t,k){
    var c=Math.floor(k/per), r=k%per, x=Z.x+c*(cw+gx), y=Z.y+r*(rh+gy);
    var cc={tc:C.P.text,mc:C.P.muted};
    if (o.cards) cc=card(C,x,y,cw,rh,{});
    else if (o.rules && r>0) C.rect(x,y-gy/2,cw,1,{fill:C.P.line,op:0.7});
    markOrNum(C,x+pad,y+(rh-ms)/2,ms,t,k,o);
    if (inline) {
      if(t.h) C.text(x+tx,y,tw*0.34,rh,t.h,{font:'head',size:hs,color:cc.tc,va:'m',ed:{f:'x-part',i:t.i,p:0},lh:1.15});
      C.text(x+tx+tw*0.37,y,tw*0.63,rh,t.d,{font:'body',size:ds,color:cc.mc,va:'m',ed:{f:'x-part',i:t.i,p:1},lh:1.35});
    } else {
      var blockH = (t.h?hH:0)+C.measureText(t.d,tw,{font:'body',max:ds,min:ds}).h;
      var by = y+Math.max(o.cards?10:0,(rh-blockH)/2);
      if(t.h) C.text(x+tx,by,tw,hH,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15,maxLines:1});
      C.text(x+tx,by+(t.h?hH:0),tw,rh-(by-y)-(t.h?hH:0),t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.38});
    }
  });
};

/* big numbered columns with top rule & dot (value proposition style) */
B.numbered = function(C,Z,o){
  var it=C.it.slice(0,8), n=it.length; if(!n) return;
  var cols=n<=4?n:Math.ceil(n/2), rows=Math.ceil(n/cols), gx=o.gap||30, gy=26;
  var cw=(Z.w-gx*(cols-1))/cols, ch=(Z.h-gy*(rows-1))/rows, P=C.P;
  var nh=o.numSize||(rows>1?38:52);
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw,54,{font:'head',max:o.hMax||(n<=3?24:21),min:13,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,cw,{font:'head',max:hs,min:hs}).h);});
  var dTop=22+nh+12+hh+8;
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw,ch-dTop,{font:'body',max:o.dMax||(n<=3?20:(n<=4?17:15)),min:10.5});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,cw,{font:'body',max:ds,min:ds}).h);});
  var blk=(dTop+dh)*rows+gy*(rows-1), oy=Math.max(0,(Z.h-blk)/2); if(rows>1) oy=0;
  if(rows===1){ ch=dTop+dh+10; }
  for (var r=0;r<rows;r++){ var ry=Z.y+oy+r*(ch+gy); C.rect(Z.x,ry+6,Z.w,o.lineW||2,{fill:o.lineC||P.line}); }
  it.forEach(function(t,k){
    var r=Math.floor(k/cols), c=k%cols, x=Z.x+c*(cw+gx), y=Z.y+oy+r*(ch+gy);
    C.ell(x,y,14,14,{fill:P.accent});
    C.text(x,y+22,cw,nh,pad2(k+1)+(o.dot?'.':''),{font:'head',size:nh,color:o.numC||P.accent,lh:1,caps:false,ls:0});
    if(t.h) C.text(x,y+22+nh+12,cw,hh,t.h,{font:'head',size:hs,color:P.text,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x,y+dTop,cw,ch-dTop,t.d,{font:'body',size:ds,color:P.muted,ed:{f:'x-part',i:t.i,p:1},lh:1.4});
  });
};

/* hub: centre medallion with items on both sides */
B.hub = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, d=Math.min(Z.h*0.78, 250), cx0=Z.x+Z.w/2, cy0=Z.y+Z.h/2;
  var left=Math.ceil(n/2), right=n-left, sideW=(Z.w-d)/2-44;
  C.ell(cx0-d/2-14,cy0-d/2-14,d+28,d+28,{line:P.accent,lw:1.5,lop:0.5,dash:true});
  C.ell(cx0-d/2,cy0-d/2,d,d,{grad:o.hubGrad||C.S.hubGrad||{type:'radial',stops:[[0,mix(P.accent,'#FFFFFF',0.2)],[100,P.accent]]}});
  C.icon(cx0-d*0.18,cy0-d*0.18-10,d*0.36,iconFor(C.s,99,C.s.title),P.onAccent||'#FFFFFF');
  var tt = String(C.s.title||'').split(/\s+/).slice(0,3).join(' ');
  C.text(cx0-d/2+22,cy0+d*0.2,d-44,d*0.24,tt,{font:'head',fit:{max:18,min:11},color:P.onAccent||'#FFFFFF',align:'c',maxLines:2,lh:1.1});
  var rows=Math.max(left,right), rh=(Z.h-(rows-1)*16)/rows;
  var hs=C.groupFit(it.map(function(t){return t.h;}),sideW-64,40,{font:'head',max:19,min:13,maxLines:1});
  var ds=C.groupFit(it.map(function(t){return t.d;}),sideW-64,rh-hs*1.25-8,{font:'body',max:15,min:10.5});
  it.forEach(function(t,k){
    var isL=k<left, r=isL?k:k-left, y=Z.y+r*(rh+16), x=isL?Z.x:cx0+d/2+44;
    var ms=44, mx=isL?x+sideW-ms:x, tx=isL?x:x+ms+16, al=isL?'r':'l';
    mark(C,mx,y+4,ms,iconFor(C.s,t.i,t.h+' '+t.d),{});
    if(t.h) C.text(isL?x:tx,y,sideW-64,hs*1.25,t.h,{font:'head',size:hs,color:P.text,align:al,ed:{f:'x-part',i:t.i,p:0},lh:1.15,maxLines:1});
    C.text(isL?x:tx,y+hs*1.25+6,sideW-64,rh-hs*1.25-6,t.d,{font:'body',size:ds,color:P.muted,align:al,ed:{f:'x-part',i:t.i,p:1},lh:1.38});
    var ly=y+4+ms/2; if(isL) C.line(x+sideW+4,ly,cx0-d/2-16,cy0+(ly-cy0)*0.5,{color:P.accent,lw:1.2,op:0.5}); else C.line(cx0+d/2+16,cy0+(ly-cy0)*0.5,x-4,ly,{color:P.accent,lw:1.2,op:0.5});
  });
};

/* horizontal gradient bars, one per item */
B.bars = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, gy=14, bh=(Z.h-gy*(n-1))/n, lw=Math.round(Z.w*0.3);
  var hs=C.groupFit(it.map(function(t){return t.h;}),lw-bh-24,bh-8,{font:'head',max:20,min:13,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),Z.w-lw-40,bh-10,{font:'body',max:15.5,min:10.5});
  it.forEach(function(t,k){
    var y=Z.y+k*(bh+gy), f=1-k*0.12;
    var g=o.grad?o.grad(k):{ang:90,stops:[[0,P.accent],[100,mix(P.accent2||P.accent,P.bg,0.35)]]};
    C.rect(Z.x,y,lw,bh,{grad:g,r:C.S.card&&C.S.card.r!=null?Math.min(C.S.card.r,bh/2):10,op:f});
    C.text(Z.x+18,y,bh*0.9,bh,pad2(k+1),{font:'head',size:Math.min(34,bh*0.5),color:P.onAccent||'#FFFFFF',va:'m',lh:1,caps:false,ls:0});
    if(t.h) C.text(Z.x+18+bh*0.9,y,lw-bh*0.9-30,bh,t.h,{font:'head',size:hs,color:P.onAccent||'#FFFFFF',va:'m',ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(Z.x+lw+28,y,Z.w-lw-28,bh,t.d,{font:'body',size:ds,color:P.muted,va:'m',ed:{f:'x-part',i:t.i,p:1},lh:1.38});
  });
};

/* two big panels side by side (p2) */
B.duo = function(C,Z,o){
  var it=C.it.slice(0,2); if(!it.length) return;
  var P=C.P, gap=o.gap||28, cw=(Z.w-gap)/2, pad=34;
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-pad*2,70,{font:'head',max:28,min:16,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,cw-pad*2,{font:'head',max:hs,min:hs}).h);});
  var ms=58, dTop=pad+ms+20+hh+12;
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-pad*2,Z.h-dTop-pad,{font:'body',max:23,min:12});
  it.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), cc=card(C,x,Z.y,cw,Z.h,{hl:o.hl?k===1:false});
    if (o.bigNum) C.text(x+cw-pad-120,Z.y+pad-8,120,70,pad2(k+1),{font:'head',size:64,color:cc.hl?cc.tc:P.accent,op:0.25,align:'r',lh:1,caps:false,ls:0});
    markOrNum(C,x+pad,Z.y+pad,ms,t,k,o,cc);
    if(t.h) C.text(x+pad,Z.y+pad+ms+20,cw-pad*2,hh,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x+pad,Z.y+dTop,cw-pad*2,Z.h-dTop-pad,t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.45});
  });
  if (o.vs) { var d=58; C.ell(Z.x+cw+gap/2-d/2,Z.y+Z.h/2-d/2,d,d,{fill:P.accent,line:P.bg,lw:4}); C.text(Z.x+cw+gap/2-d/2,Z.y+Z.h/2-d/2,d,d,'VS',{font:'head',size:18,color:P.onAccent||'#FFF',align:'c',va:'m',lh:1,caps:false,ls:0.05}); }
};

/* many points (9-16): compact card grid, icon or number on the left */
B.many = function(C,Z,o){
var it=C.it.slice(0,16), n=it.length; if(!n) return;
var cols=o.cols||(n<=9?3:4), rows=Math.ceil(n/cols);
var gx=o.gap||16, gy=o.gapY||14, cw=(Z.w-gx*(cols-1))/cols, ch=(Z.h-gy*(rows-1))/rows;
var flat=C.S.card&&(C.S.card.kind==='none'||C.S.card.kind==='rule');
var pad=flat?4:Math.max(10,Math.min(18,Math.round(ch*0.14)));
var ms=Math.round(Math.max(22,Math.min(40,ch-pad*2-4,cw*0.18)));
var tx=pad+ms+12, tw=cw-tx-pad;
var hs=C.groupFit(it.map(function(t){return t.h;}),tw,ch*0.42,{font:'head',max:o.hMax||16,min:10,maxLines:2});
var hh=0; it.forEach(function(t){ if(t.h) hh=Math.max(hh,C.measureText(t.h,tw,{font:'head',max:hs,min:hs,maxLines:2}).h); });
var dAvail=Math.max(14,ch-pad*2-(hh?hh+4:0));
var ds=C.groupFit(it.map(function(t){return t.d;}),tw,dAvail,{font:'body',max:o.dMax||13.5,min:8.5,lh:1.3});
it.forEach(function(t,k){
var r=Math.floor(k/cols), c=k%cols, inRow=Math.min(cols,n-r*cols);
var shift = inRow<cols ? (cols-inRow)*(cw+gx)/2 : 0;
var x=Z.x+shift+c*(cw+gx), y=Z.y+r*(ch+gy);
var cc=card(C,x,y,cw,ch,{hl:o.hl==='first'?k===0:false});
var dh=Math.min(dAvail,C.measureText(t.d,tw,{font:'body',max:ds,min:ds,lh:1.3}).h);
var blk=(t.h?hh+4:0)+dh, by=y+Math.max(pad,(ch-blk)/2);
markOrNum(C,x+pad,y+(ch-ms)/2,ms,t,k,o,cc);
if(t.h) C.text(x+tx,by,tw,hh,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
C.text(x+tx,by+(t.h?hh+4:0),tw,Math.max(dh,y+ch-pad-(by+(t.h?hh+4:0))),t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.3});
});
};

/* single rich paragraph */
B.text = function(C,Z,o){
  var P=C.P, it=C.it[0];
  var txt = it ? (it.h ? it.h+': '+it.d : it.d) : (C.s.subtitle||'');
  if (o.box) card(C,Z.x,Z.y,Z.w,Z.h,{});
  var pad=o.box?40:0;
  C.text(Z.x+pad,Z.y+pad,Z.w-pad*2,Z.h-pad*2,txt,{font:'body',fit:{max:30,min:16},color:P.text,ed:it?{f:'content-plain',i:it.i}:null,lh:1.45,va:'m'});
};

/* ---------------- stats ---------------- */
function statItems(C){ return C.it.slice(0,4).map(function(t){ return {i:t.i, v:t.h||t.d.split(' ')[0], l:t.h?t.d:t.d.split(' ').slice(1).join(' '), d:t.x}; }); }
function pctOf(v,k){ var m=String(v).match(/(\d+(?:\.\d+)?)\s*%/); if(m) return clamp(parseFloat(m[1]),3,100); return [78,64,86,55][k%4]; }
B.statCols = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gap=o.gap||30, cw=(Z.w-gap*(n-1))/n, iw=cw-16;
  var vs=C.groupFit(st.map(function(t){return t.v;}),iw,130,{font:'head',max:o.vMax||(n>=4?80:96),min:30,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),iw,60,{font:'head',max:n>=4?20:23,min:12,maxLines:2});
  var lh=0; st.forEach(function(t){lh=Math.max(lh,C.measureText(t.l,iw,{font:'head',max:ls,min:ls}).h);});
  var top=vs*1.08+14+lh+12, dAvail=Z.h-top-20;
  var ds=C.groupFit(st.map(function(t){return t.d;}),iw,dAvail,{font:'body',max:n>=4?16:18,min:10.5,lh:1.42});
  var dh=0; st.forEach(function(t){dh=Math.max(dh,t.d?C.measureText(t.d,iw,{font:'body',max:ds,min:ds,lh:1.42}).h:0);});
  var blockH=top+dh, y0=Z.y+Math.max(0,(Z.h-blockH)/2);
  st.forEach(function(t,k){
    var x=Z.x+k*(cw+gap);
    if (k>0 && o.dividers!==false) C.rect(x-gap/2,y0,1.5,blockH,{fill:P.line});
    C.text(x+8,y0,iw,vs*1.08,t.v,{font:'head',size:vs,color:o.vc||P.accent,lh:1.05,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:-0.01});
    C.text(x+8,y0+vs*1.08+14,iw,lh,t.l,{font:'head',size:ls,color:P.text,lh:1.2,ed:{f:'x-part',i:t.i,p:1}});
    if(t.d) C.text(x+8,y0+top,iw,dh+4,t.d,{font:'body',size:ds,color:P.muted,lh:1.42,ed:{f:'x-part',i:t.i,p:2}});
  });
};
B.statCards = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gap=o.gap||22, cw=(Z.w-gap*(n-1))/n, pad=28, iw=cw-pad*2;
  var vs=C.groupFit(st.map(function(t){return t.v;}),iw,110,{font:'head',max:o.vMax||(n>=4?60:74),min:28,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),iw,56,{font:'head',max:n>=4?19:22,min:12,maxLines:2});
  var lh=0; st.forEach(function(t){lh=Math.max(lh,C.measureText(t.l,iw,{font:'head',max:ls,min:ls}).h);});
  var ms=o.icons?50:0, top=(ms?ms+16:0)+vs*1.08+10+lh+10, dAvail=Z.h-pad*2-top;
  var ds=C.groupFit(st.map(function(t){return t.d;}),iw,dAvail,{font:'body',max:n>=4?15.5:17,min:10.5,lh:1.42});
  var dh=0; st.forEach(function(t){dh=Math.max(dh,t.d?C.measureText(t.d,iw,{font:'body',max:ds,min:ds,lh:1.42}).h:0);});
  var off=Math.round(Math.max(0,dAvail-dh)*0.45);
  st.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), cc=card(C,x,Z.y,cw,Z.h,{hl:o.hl==='first'?k===0:(o.hl==='alt'?k%2===0:false)});
    var y=Z.y+pad+off;
    if (ms) { mark(C,x+pad,y,ms,iconFor(C.s,t.i,t.l),{}); y+=ms+16; }
    var cbase=(C.S.card&&(C.S.card.fill||(C.S.card.grad&&C.S.card.grad.stops[0][1])))||P.card;
    var vcol=cc.hl?cc.tc:(o.vc||(Math.abs(lum(P.accent)-lum(cbase))<0.3?cc.tc:P.accent));
    C.text(x+pad,y,iw,vs*1.08,t.v,{font:'head',size:vs,color:vcol,lh:1.05,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:-0.01});
    C.text(x+pad,y+vs*1.08+10,iw,lh,t.l,{font:'head',size:ls,color:cc.tc,lh:1.2,ed:{f:'x-part',i:t.i,p:1}});
    if(t.d) C.text(x+pad,y+vs*1.08+10+lh+10,iw,dh+4,t.d,{font:'body',size:ds,color:cc.mc,lh:1.42,ed:{f:'x-part',i:t.i,p:2}});
  });
};
B.statRings = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gap=o.gap||26, cw=(Z.w-gap*(n-1))/n, d=Math.min(cw-40, Z.h*0.52, 190);
  var vs=C.groupFit(st.map(function(t){return t.v;}),d*0.72,d*0.4,{font:'head',max:40,min:16,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),cw-10,48,{font:'head',max:18,min:12,maxLines:2});
  var dTop=d+18+ls*2.5+6;
  var ds=C.groupFit(st.map(function(t){return t.d;}),cw-10,Z.h-dTop,{font:'body',max:14.5,min:10.5});
  st.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), rx=x+(cw-d)/2;
    if (o.cardBg) card(C,x,Z.y-10,cw,Z.h+10,{});
    C.ring(rx,Z.y,d,pctOf(t.v,k),{color:P.accent,track:o.track||mix(P.accent,P.bg,0.78),th:o.th||Math.round(d*0.09)});
    C.text(rx,Z.y,d,d,t.v,{font:'head',size:vs,color:P.text,align:'c',va:'m',lh:1,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:0});
    C.text(x+5,Z.y+d+18,cw-10,ls*2.5,t.l,{font:'head',size:ls,color:P.text,align:'c',lh:1.2,ed:{f:'x-part',i:t.i,p:1}});
    if(t.d) C.text(x+5,Z.y+dTop,cw-10,Z.h-dTop,t.d,{font:'body',size:ds,color:P.muted,align:'c',lh:1.4,ed:{f:'x-part',i:t.i,p:2}});
  });
};
B.statBars = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gy=18, rh=(Z.h-gy*(n-1))/n, vw=Math.round(Z.w*0.2);
  var vs=C.groupFit(st.map(function(t){return t.v;}),vw,rh*0.8,{font:'head',max:56,min:22,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),Z.w-vw-30,32,{font:'head',max:19,min:12,maxLines:1});
  st.forEach(function(t,k){
    var y=Z.y+k*(rh+gy), bx=Z.x+vw+30, bw=Z.w-vw-30;
    C.text(Z.x,y,vw,rh,t.v,{font:'head',size:vs,color:P.accent,va:'m',lh:1,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:-0.01});
    C.text(bx,y+rh*0.06,bw,ls*1.3,t.l,{font:'head',size:ls,color:P.text,lh:1.2,ed:{f:'x-part',i:t.i,p:1},maxLines:1});
    var by=y+rh*0.06+ls*1.3+10, bh=Math.max(10,Math.min(16,rh*0.14));
    C.rect(bx,by,bw,bh,{fill:mix(P.accent,P.bg,0.8),r:bh/2});
    C.rect(bx,by,bw*pctOf(t.v,k)/100,bh,{grad:{ang:90,stops:[[0,P.accent],[100,P.accent2||P.accent]]},r:bh/2});
    if(t.d) C.text(bx,by+bh+10,bw,y+rh-(by+bh+10),t.d,{font:'body',fit:{max:14.5,min:10.5},color:P.muted,lh:1.35,ed:{f:'x-part',i:t.i,p:2},maxLines:2});
  });
};

/* ---------------- timeline ---------------- */
B.tlH = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, cw=Z.w/n, ly=Z.y+Z.h*(o.lineAt||0.5), d=o.dot||22;
  C.rect(Z.x,ly-1.5,Z.w,3,{fill:o.lineC||P.line});
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-20,40,{font:'head',max:24,min:13,maxLines:1});
  var room=Z.h*0.5-d-26-hs*1.3;
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-20,room,{font:'body',max:n<=4?18:16.5,min:10.5});
  it.forEach(function(t,k){
    var x=Z.x+k*cw, cx0=x+cw/2, up=o.alt!==false?k%2===0:false;
    C.ell(cx0-d/2,ly-d/2,d,d,{fill:P.accent,line:P.bg,lw:4});
    if (up) {
      C.line(cx0,ly-d/2,cx0,ly-26,{color:P.accent,lw:1.5,op:0.6});
      var dh=C.measureText(t.d,cw-20,{font:'body',max:ds,min:ds}).h;
      C.text(x+10,ly-26-dh,cw-20,dh,t.d,{font:'body',size:ds,color:P.muted,align:'c',lh:1.38,ed:{f:'x-part',i:t.i,p:1},va:'b'});
      C.text(x+10,ly-26-dh-hs*1.3-4,cw-20,hs*1.3,t.h,{font:'head',size:hs,color:P.accent,align:'c',lh:1.1,ed:{f:'x-part',i:t.i,p:0},maxLines:1});
    } else {
      C.line(cx0,ly+d/2,cx0,ly+26,{color:P.accent,lw:1.5,op:0.6});
      C.text(x+10,ly+30,cw-20,hs*1.3,t.h,{font:'head',size:hs,color:P.accent,align:'c',lh:1.1,ed:{f:'x-part',i:t.i,p:0},maxLines:1});
      C.text(x+10,ly+30+hs*1.3+4,cw-20,room+10,t.d,{font:'body',size:ds,color:P.muted,align:'c',lh:1.38,ed:{f:'x-part',i:t.i,p:1}});
    }
  });
};
B.tlCards = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, gap=16, cw=(Z.w-gap*(n-1))/n, ly=Z.y+14, d=20, pad=20, cy=ly+32, ch=Z.h-(cy-Z.y), iw=cw-pad*2;
  C.rect(Z.x,ly-1.5,Z.w,3,{fill:P.line})._tlLine=1;
  var hs=C.groupFit(it.map(function(t){return t.h;}),iw,60,{font:'head',max:n>=5?21:24,min:13,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,iw,{font:'head',max:hs,min:hs}).h);});
  var dAvail=ch-pad*2-hh-12;
  var ds=C.groupFit(it.map(function(t){return t.d;}),iw,dAvail,{font:'body',max:n>=5?16:18,min:10.5,lh:1.42});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,iw,{font:'body',max:ds,min:ds,lh:1.42}).h);});
  var need=pad*2+hh+12+dh+36;
  if (ch>need){ var sh=(ch-need)/2; ly+=sh; cy+=sh; ch=need; dAvail=ch-pad*2-hh-12; }
  var off=Math.round(Math.max(0,dAvail-dh)*0.35);
  C.els.forEach(function(e){ if(e._tlLine){ e.y=ly-1.5; } });
  it.forEach(function(t,k){
    var x=Z.x+k*(cw+gap);
    C.ell(x+pad,ly-d/2,d,d,{fill:P.accent,line:P.bg,lw:3});
    var cc=card(C,x,cy,cw,ch,{hl:k===n-1&&o.hlLast});
    C.text(x+pad,cy+pad+off,iw,hh,t.h,{font:'head',size:hs,color:o.hc||(k===n-1&&o.hlLast?cc.tc:P.accent),lh:1.15,ed:{f:'x-part',i:t.i,p:0}});
    C.text(x+pad,cy+pad+off+hh+12,iw,dh+4,t.d,{font:'body',size:ds,color:cc.mc,lh:1.42,ed:{f:'x-part',i:t.i,p:1}});
  });
};
B.tlV = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, cols=n>4?2:1, per=Math.ceil(n/cols), gx=40, cw=(Z.w-gx*(cols-1))/cols, gy=10, rh=(Z.h-gy*(per-1))/per, d=18, lx=o.lx||120;
  var hs=C.groupFit(it.map(function(t){return t.h;}),lx-20,rh,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-lx-40,rh-4,{font:'body',max:15.5,min:10.5});
  for (var c=0;c<cols;c++){ var x0=Z.x+c*(cw+gx)+lx+8; C.rect(x0-1,Z.y+rh/2,2,(Math.min(per,n-c*per)-1)*(rh+gy),{fill:P.line}); }
  it.forEach(function(t,k){
    var c=Math.floor(k/per), r=k%per, x=Z.x+c*(cw+gx), y=Z.y+r*(rh+gy), dx=x+lx+8;
    C.text(x,y,lx-10,rh,t.h,{font:'head',size:hs,color:P.accent,align:'r',va:'m',lh:1.15,ed:{f:'x-part',i:t.i,p:0}});
    C.ell(dx-d/2,y+rh/2-d/2,d,d,{fill:P.accent,line:P.bg,lw:3});
    C.text(dx+22,y,cw-lx-40,rh,t.d,{font:'body',size:ds,color:P.text,va:'m',lh:1.38,ed:{f:'x-part',i:t.i,p:1}});
  });
};
B.tlChev = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, cw=Z.w/n, ch=o.chevH||86, pad=14;
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-60,ch-20,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-pad*2,Z.h-ch-22,{font:'body',max:15,min:10.5});
  it.forEach(function(t,k){
    var x=Z.x+k*cw, col=mix(P.accent,P.accent2||P.accent,n>1?k/(n-1):0);
    C.shape(k===0?'homePlate':'chevron',x,Z.y,cw+(k<n-1?18:0),ch,{fill:col});
    C.text(x+(k===0?20:40),Z.y,cw-60,ch,t.h,{font:'head',size:hs,color:P.onAccent||'#FFFFFF',va:'m',align:'c',lh:1.1,ed:{f:'x-part',i:t.i,p:0}});
    C.text(x+pad,Z.y+ch+22,cw-pad*2,Z.h-ch-22,t.d,{font:'body',size:ds,color:P.muted,lh:1.4,ed:{f:'x-part',i:t.i,p:1},align:'c'});
  });
};
B.tlStairs = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, gap=14, cw=(Z.w-gap*(n-1))/n, minH=Z.h*0.46, step=(Z.h-minH)/Math.max(1,n-1), pad=16;
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-pad*2,48,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-pad*2,minH-pad*2-hs*2.4-6,{font:'body',max:14.5,min:10});
  it.forEach(function(t,k){
    var h=minH+step*k, x=Z.x+k*(cw+gap), y=Z.y+Z.h-h;
    C.rect(x,y,cw,h,{grad:{ang:180,stops:[[0,mix(P.accent,P.accent2||P.accent,k/(Math.max(1,n-1)))],[100,mix(P.accent,P.bg,0.55)]]},r:C.S.card&&C.S.card.r!=null?Math.min(C.S.card.r,14):10});
    C.text(x+pad,y+pad,cw-pad*2,hs*2.4,t.h,{font:'head',size:hs,color:P.onAccent||'#FFFFFF',lh:1.15,ed:{f:'x-part',i:t.i,p:0}});
    C.text(x+pad,y+pad+hs*2.4+6,cw-pad*2,h-pad*2-hs*2.4-6,t.d,{font:'body',size:ds,color:mix(P.onAccent||'#FFFFFF',P.accent,0.12),lh:1.38,ed:{f:'x-part',i:t.i,p:1}});
  });
};

/* ---------------- agenda ---------------- */
B.agTiles = function(C,Z,o){
  var it=C.it.slice(0,8), n=it.length; if(!n) return;
  var P=C.P, cols=n<=4?n:Math.ceil(n/2), rows=Math.ceil(n/cols), gap=18, cw=(Z.w-gap*(cols-1))/cols, ch=(Z.h-gap*(rows-1))/rows, pad=20;
  var hs=C.groupFit(it.map(function(t){return t.h||t.d;}),cw-pad*2,48,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.h?t.d:'';}),cw-pad*2,ch-pad*2-44-hs*2.4,{font:'body',max:14,min:10});
  it.forEach(function(t,k){
    var r=Math.floor(k/cols), c=k%cols, x=Z.x+c*(cw+gap), y=Z.y+r*(ch+gap);
    var cc=card(C,x,y,cw,ch,{});
    C.text(x+pad,y+pad-4,cw-pad*2,44,pad2(k+1),{font:'head',size:36,color:P.accent,lh:1,caps:false,ls:0});
    C.text(x+pad,y+pad+44,cw-pad*2,hs*2.4,t.h||t.d,{font:'head',size:hs,color:cc.tc,lh:1.15,ed:{f:'x-part',i:t.i,p:t.h?0:1}});
    if (t.h && t.d && ds>=10) C.text(x+pad,y+pad+44+hs*2.4,cw-pad*2,ch-pad*2-44-hs*2.4,t.d,{font:'body',size:ds,color:cc.mc,lh:1.35,ed:{f:'x-part',i:t.i,p:1}});
  });
};
B.agList = function(C,Z,o){
  var it=C.it.slice(0,10), n=it.length; if(!n) return;
  var P=C.P, cols=o.cols||(n>5?2:1), per=Math.ceil(n/cols), gx=48, cw=(Z.w-gx*(cols-1))/cols, rh=Math.min(92,Z.h/per);
  var hs=C.groupFit(it.map(function(t){return t.h||t.d;}),cw-110,rh*0.5,{font:'head',max:23,min:13,maxLines:1});
  var ds=C.groupFit(it.map(function(t){return t.h?t.d:'';}),cw-110,rh*0.42,{font:'body',max:14.5,min:10,maxLines:1});
  it.forEach(function(t,k){
    var c=Math.floor(k/per), r=k%per, x=Z.x+c*(cw+gx), y=Z.y+r*rh;
    num(C,x,y+(rh-50)/2,50,k+1,o.numO);
    var two=t.h&&t.d;
    C.text(x+72,y+(two?rh*0.12:0),cw-80,two?rh*0.46:rh,t.h||t.d,{font:'head',size:hs,color:P.text,va:two?'b':'m',lh:1.15,ed:{f:'x-part',i:t.i,p:t.h?0:1},maxLines:1});
    if (two) C.text(x+72,y+rh*0.6,cw-80,rh*0.38,t.d,{font:'body',size:ds,color:P.muted,lh:1.3,ed:{f:'x-part',i:t.i,p:1},maxLines:1});
    if (o.rules!==false && r<per-1) C.rect(x+72,y+rh-1,cw-72,1,{fill:P.line,op:0.8});
  });
};

/* ---------------- quote ---------------- */
B.quote = function(C,Z,o){
  var P=C.P, q=C.s.title||'', by=(C.it[0]?(C.it[0].h?C.it[0].h+': '+C.it[0].d:C.it[0].d):(C.s.subtitle||''));
  var al=o.align||'c', qc=o.qc||P.accent;
  C.text(al==='c'?Z.x+Z.w/2-60:Z.x,Z.y-10,120,110,'“',{font:'head',size:150,color:qc,lh:1,align:al==='c'?'c':'l',op:0.9,caps:false,ls:0,wt:700});
  var qe=C.text(Z.x,Z.y+96,Z.w,Z.h-190,q,{font:o.qFont||'head',fit:{max:o.qMax||40,min:20},color:o.tc||P.text,align:al,lh:1.3,italic:!!o.italic,ed:{f:'title'},maxLines:5,va:'t'});
  var yy=Z.y+96+qe._h+30;
  C.rect(al==='c'?Z.x+Z.w/2-30:Z.x,yy,60,3,{fill:qc});
  if (by) C.text(Z.x,yy+18,Z.w,34,by,{font:'body',fit:{max:18,min:12},wt:700,color:o.bc||P.muted,align:al,caps:true,ls:0.1,ed:C.it[0]?{f:'content-plain',i:C.it[0].i}:null,maxLines:1});
};

/* chart slide (DS Charts snippet draws the chart: t:'chart' element) */
B.chart = function(C,Z,o){
  var DC = window.DS_CHART, P = C.P; if (!DC) return;
  var d = DC.norm(C.s.chart), n = d.rows.length, pie = d.kind === 'pie', it = C.it.slice(0,4), has = it.length > 0;
  var dark = lum(P.bg||'#FFFFFF') < 0.5, cols = DC.palette(P.accent||'#2563EB', dark, Math.max(n,3));
  var tc = P.title||P.text, mc = P.muted||mix(P.text,P.bg,0.35);
  var spec = {style:d.style, angle:d.angle, unit:d.unit, max:(C.s.chart&&C.s.chart.max)||'', labels:d.rows.map(function(r){return r.l;}), values:d.rows.map(function(r){return r.v;})};
  var noLeg = d.style==='rings'||d.style==='progress'||d.style==='gauge';
  function chart(x,y,w,h,leg){ C.add({t:'chart',x:x,y:y,w:w,h:h,fam:C.font('body'),spec:spec,colors:cols,tc:tc,mc:mc,bgc:hx(P.bg||'#FFFFFF'),legend:noLeg?'none':leg}); }
  function pts(x,y,w,h){
    var m=it.length, gap=14, ch=(h-gap*(m-1))/m;
    var hs=C.groupFit(it.map(function(t){return t.h;}),w-44,40,{font:'head',max:m>3?19:22,min:13,maxLines:2});
    it.forEach(function(t,k){
      var yy=y+k*(ch+gap), cc=card(C,x,yy,w,ch,{});
      C.rect(x,yy+ch*0.18,6,ch*0.64,{fill:cols[k%cols.length],r:3});
      var th=t.h?C.measureText(t.h,w-44,{font:'head',max:hs,min:hs}).h:0;
      var ds=C.measureText(t.d,w-44,{font:'body',max:m>3?15:17,min:11,h:ch-th-22}).s;
      var dh=t.d?C.measureText(t.d,w-44,{font:'body',max:ds,min:ds}).h:0, ty=yy+Math.max(8,(ch-th-dh-6)/2);
      if (t.h) C.text(x+26,ty,w-44,th,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
      if (t.d) C.text(x+26,ty+th+6,w-44,dh+4,t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.38});
    });
  }
  function legend(x,y,w,h,kpi){
    var rows=d.rows.map(function(r,i){return {r:r,i:i};}), one=d.kind==='bar'||d.kind==='line';
    if (kpi) rows=rows.slice().sort(function(a,b){return b.r.v-a.r.v;}).slice(0,3); else rows=rows.slice(0,7);
    var m=Math.max(1,rows.length), rh=kpi?Math.min(150,h/m):Math.min(64,h/m), y0=y+(h-rh*m)/2;
    rows.forEach(function(o,k){
      var yy=y0+k*rh, val=DC.fmt(o.r.v,d.unit), col=cols[one?0:o.i%cols.length];
      if (k) C.rect(x,yy,w,1,{fill:P.line||mix(P.text,P.bg,0.8)});
      if (kpi) {
        C.text(x,yy+10,w,rh*0.55,val,{font:'head',size:m>2?46:54,wt:800,color:col,lh:1,va:'b',caps:false,ls:0,maxLines:1});
        C.text(x,yy+10+rh*0.55+4,w,rh*0.45-20,o.r.l,{font:'body',fit:{max:17,min:12},wt:600,color:mc,lh:1.3,maxLines:2});
      } else {
        C.rect(x,yy+rh/2-8,16,16,{fill:col,r:5});
        C.text(x+30,yy,w-150,rh,o.r.l,{font:'body',fit:{max:18,min:12},wt:600,color:P.text,va:'m',maxLines:1});
        C.text(x+w-120,yy,120,rh,val,{font:'head',size:m>5?22:26,wt:800,color:tc,va:'m',align:'r',caps:false,ls:0,maxLines:1});
      }
    });
  }
  var gap=36;
  if (o.full) { chart(Z.x,Z.y,Z.w,Z.h,'right'); return; }
  if (o.center) {
    var bh=has?110:0, cw=Math.min(Z.w,960);
    chart(Z.x+(Z.w-cw)/2,Z.y,cw,Z.h-bh-(has?16:0),'right');
    if (has) { var i3=it.slice(0,3), w3=(Z.w-28*(i3.length-1))/i3.length;
      i3.forEach(function(t,k){ var x=Z.x+k*(w3+28), y=Z.y+Z.h-bh; C.rect(x,y,w3,4,{fill:cols[k%cols.length]});
        if (t.h) C.text(x,y+14,w3,26,t.h,{font:'head',fit:{max:19,min:13},color:tc,ed:{f:'x-part',i:t.i,p:0},maxLines:1});
        if (t.d) C.text(x,y+44,w3,bh-44,t.d,{font:'body',fit:{max:15.5,min:11},color:mc,ed:{f:'x-part',i:t.i,p:1},lh:1.35,maxLines:3}); }); }
    return;
  }
  if (o.kpi) { var kw=Math.round(Z.w*0.3); legend(Z.x,Z.y,kw,Z.h,true); chart(Z.x+kw+gap,Z.y,Z.w-kw-gap,Z.h,'bottom'); return; }
  var pw=Math.round(Z.w*0.4), cw2=Z.w-pw-gap, right=o.side==='r';
  var cx=right?Z.x+pw+gap:Z.x, px2=right?Z.x:Z.x+cw2+gap;
  chart(cx,Z.y,cw2,Z.h,pie?(has?'bottom':'none'):'none');
  if (has) pts(px2,Z.y,pw,Z.h); else legend(px2,Z.y,pw,Z.h,!(pie||d.kind==='gauge'));
};

/* ============================================================
   COMPOSED STANDARD SLIDE
   opt: {body, img:'right'|'left'|'bottom'|'top', imgPct, framed, hk (header kind), ...body opts}
   ============================================================ */
DSTE.std = function(C,o){
  o=o||{}; var S=C.S, P=C.P, M=S.margin||64;
  var A={x:M,w:W-2*M,top:null};
  var framed = o.framed!=null?o.framed:!!S.imgFramed, ir=S.imgR!=null?S.imgR:(framed?20:0);
  var bottom = H - (S.footer===false?40:(S.footerH||62));
  var imgPct = o.imgPct || S.imgPct || 0.4;
  if (o.img==='right'||o.img==='left'){
    var iw=Math.round(W*imgPct), ix=o.img==='right'?W-iw:0;
    if (framed) imgBox(C,ix+(o.img==='right'?0:28),28,iw-28,H-56,{r:ir});
    else imgBox(C,ix,0,iw,H,{r:0});
    A.x = o.img==='right'?M:iw+48; A.w = W-iw-M-48;
  }
  if (o.img==='top'){ var th=Math.round(H*0.3); if(framed) imgBox(C,28,28,W-56,th-28,{r:ir}); else imgBox(C,0,0,W,th,{r:0}); A.top=th+30; }
  var hb = header(C,A,o);
  var concH = C.s.conclusion && o.conc!==false ? (S.concH||54) : 0;
  if (o.img==='bottom'){ var bh=Math.round(H*0.26); if(framed) imgBox(C,28,H-bh-28+0,W-56,bh,{r:ir}); else imgBox(C,0,H-bh,W,bh,{r:0}); bottom=H-bh-24; }
  var zTop = hb + (S.bodyGap||30);
  var zBot = bottom - (concH?concH+18:0);
  var Z = {x:A.x, y:zTop, w:A.w, h:Math.max(80,zBot-zTop)};
  var fn = typeof o.body==='function' ? o.body : B[o.body||'cols'];
  if (fn) fn(C,Z,o);
  if (concH) conclusion(C,A.x,zBot+18,A.w,concH,o.concO);
  return Z;
};

/* ============================================================
   VARIANT SETS — sensible defaults a template may override
   ids: v1 + n2..n6 are text-only; v2..v10 have an image zone
   ============================================================ */
function V(id,lbl,o){ var v={id:id,lbl:lbl,o:o||{}}; if(o&&typeof o.fn==='function') v.fn=o.fn; return v; }
DSTE.V = V;
DSTE.defaultVariants = {
  text:[V('v1','Statement',{body:'text'}),V('n2','Statement in card',{body:'text',box:true}),V('v2','Statement + image right',{body:'text',img:'right'}),V('v3','Image left + statement',{body:'text',img:'left'})],
  p2:[V('v1','Two panels',{body:'duo'}),V('n2','Two panels + VS',{body:'duo',vs:true}),V('n3','Numbered columns',{body:'numbered'}),V('v2','Panels + image right',{body:'list',img:'right',cards:true}),V('v3','Image left + list',{body:'list',img:'left'}),V('v4','Columns + image bottom',{body:'cols',img:'bottom'})],
  p3:[V('v1','Three cards',{body:'cols'}),V('n2','Numbered columns',{body:'numbered'}),V('n3','Icon list',{body:'list',rules:true}),V('n4','Gradient bars',{body:'bars'}),V('v2','List + image right',{body:'list',img:'right'}),V('v3','Image left + cards',{body:'list',img:'left',cards:true}),V('v4','Cards + image bottom',{body:'cols',img:'bottom'})],
  p4:[V('v1','2 × 2 grid',{body:'grid'}),V('n2','Four columns',{body:'cols'}),V('n3','Hub diagram',{body:'hub'}),V('n4','Numbered row',{body:'numbered'}),V('v2','Grid + image right',{body:'list',img:'right',cards:true}),V('v3','Image left + list',{body:'list',img:'left'})],
  p56:[V('v1','Card grid',{body:'grid'}),V('n2','Two-column list',{body:'list',cols:2,rules:true}),V('n3','Numbered grid',{body:'numbered'}),V('n4','Hub diagram',{body:'hub'}),V('v2','List + image right',{body:'list',img:'right',cols:1}),V('v3','Image left + list',{body:'list',img:'left',cols:1})],
  p78:[V('v1','Card grid',{body:'grid'}),V('n2','Two-column list',{body:'list',cols:2,rules:true}),V('n3','Numbered grid',{body:'numbered'}),V('v2','List + image right',{body:'list',img:'right',cols:2})],
  p9:[V('v1','Card grid',{body:'many'}),V('n2','Numbered grid',{body:'many',mark:'num'}),V('n3','Two-column list',{body:'list',cols:2,rules:true,max:16}),V('n4','Three-column list',{body:'list',cols:3,rules:true,max:16}),V('v2','List + image right',{body:'list',img:'right',cols:2,max:16})],
  stats:[V('v1','Big numbers',{body:'statCols'}),V('n2','Stat cards',{body:'statCards',icons:true}),V('n3','Progress rings',{body:'statRings'}),V('n4','Progress bars',{body:'statBars'}),V('v2','Numbers + image right',{body:'statBars',img:'right'}),V('v3','Image left + cards',{body:'statBars',img:'left'})],
  timeline:[V('v1','Horizontal timeline',{body:'tlH'}),V('n2','Milestone cards',{body:'tlCards'}),V('n3','Arrow steps',{body:'tlChev'}),V('n4','Growth stairs',{body:'tlStairs'}),V('n5','Vertical timeline',{body:'tlV'}),V('v2','Vertical + image right',{body:'tlV',img:'right'})],
  agenda:[V('v1','Numbered tiles',{body:'agTiles'}),V('n2','Numbered list',{body:'agList'}),V('v2','List + image right',{body:'agList',img:'right',cols:1})],
  cover:[V('v1','Cover',{})],
  closing:[V('v1','Closing',{})],
  chart:[V('v1','Chart + insights',{body:'chart'}),V('n2','Insights + chart right',{body:'chart',side:'r'}),V('n3','Centered chart',{body:'chart',center:true,hk:'center'}),V('n4','Big numbers + chart',{body:'chart',kpi:true}),V('n5','Full-width chart',{body:'chart',full:true})],
  quote:[V('v1','Centered quote',{body:'quote'}),V('v2','Quote + image right',{body:'quote',img:'right',align:'l'}),V('v3','Image left + quote',{body:'quote',img:'left',align:'l'})]
};

DSTE.variantsFor = function(T, kind){
  var tv = T.variants || {};
  var list = tv[kind] || DSTE.defaultVariants[kind] || DSTE.defaultVariants.p3;
  if (kind === 'agenda' && list.length < 3) {           /* every template offers at least 3 agenda layouts */
    if (!T.__agx) {
      var have = {}; list.forEach(function(v){ have[v.id] = 1; });
      var extra = [V('n5','Numbered tiles',{body:'agTiles'}), V('n6','Numbered list',{body:'agList'}), V('v9','List + image right',{body:'agList', img:'right', cols:1})];
      T.__agx = list.concat(extra.filter(function(v){ return !have[v.id]; }));
    }
    return T.__agx;
  }
  return list;
};

/* ============================================================
   THEMES on template decks
   A theme picked from the Theme section re-maps every colour of a template
   by its role, the way a Classic deck takes a theme:
     - slide backgrounds  -> the theme background (solid or gradient)
     - greys / blacks / whites -> placed between the theme background and
       text colour by how light they are (cards, lines, muted text)
     - titles / body text -> the theme title / body colours
     - accent colours     -> the theme accent (lighter / darker shades kept)
   Inverted parts (a dark band on a light template) stay inverted.
   Headings switch to the theme font.
   ============================================================ */
/* key: [accent, font, background, title colour, body colour] */
DSTE.THEMES = {
 'navy-pro':['#4A9EFF','Inter','#1B2A4A','#FFFFFF','#C8D8F0'],
 'dark-modern':['#58A6FF','Inter','#0D1117','#FFFFFF','#C9D1D9'],
 'clean-corporate':['#2B6CB0','Roboto','#FFFFFF','#1A365D','#2D3748'],
 'bold-red':['#FF4444','Oswald','#1A0000','#FFFFFF','#FFD0D0'],
 'corporate-dark':['#6366F1','IBM Plex Sans','#111827','#F9FAFB','#D1D5DB'],
 'clean-white':['#0EA5E9','Work Sans','#F8FAFC','#0F172A','#334155'],
 'gradient-pro':['#A78BFA','Space Grotesk','#0F0C29','#FFFFFF','#E0D7FF'],
 'bold-impact':['#FFD700','Bebas Neue','#111111','#FFFFFF','#CCCCCC'],
 'sunset-glow':['#FF6B9D','Poppins','#2D1B2E','#FFFFFF','#F4C9D9'],
 'forest-green':['#4ADE80','Nunito','#0F2419','#FFFFFF','#C8E6D0'],
 'ocean-breeze':['#0284C7','DM Sans','#F0F9FF','#0C4A6E','#334155'],
 'golden-hour':['#F59E0B','Playfair Display','#1C1408','#FFFFFF','#F5DEB3'],
 'lavender-mist':['#8B5CF6','Quicksand','#F5F3FF','#3B0764','#4C1D95'],
 'slate-minimal':['#64748B','Inter','#F8F9FA','#1E293B','#475569'],
 'cherry-bold':['#DC2626','Montserrat','#FFF5F5','#7F1D1D','#450A0A'],
 'midnight-teal':['#2DD4BF','Josefin Sans','#042F2E','#FFFFFF','#99F6E4'],
 'coral-sunset':['#FB923C','Raleway','#FFF7ED','#7C2D12','#9A3412'],
 'royal-purple':['#A855F7','Playfair Display','#1E0A3C','#FFFFFF','#DDD6FE'],
 'mint-fresh':['#22C55E','Nunito','#F0FDF4','#14532D','#166534'],
 'rose-gold':['#FB7185','Merriweather','#FFF1F2','#881337','#9F1239'],
 'steel-blue':['#38BDF8','Source Sans Pro','#0F172A','#F1F5F9','#94A3B8'],
 'amber-warm':['#FBBF24','Crimson Text','#1C1917','#FEF3C7','#D6D3D1'],
 'arctic-white':['#06B6D4','Work Sans','#FFFFFF','#0F172A','#334155'],
 'crimson-night':['#EF4444','Libre Baskerville','#1A0A0A','#FFFFFF','#FCA5A5'],
 'aurora':['#4FD1C5','Poppins','linear-gradient(135deg,#0F2027,#1A3A4A)','#FFFFFF','#B8D4DE'],
 'sunset-blaze':['#FFD700','Montserrat','linear-gradient(135deg,#7B0000,#DD2476)','#FFFFFF','#FFE0E9'],
 'cosmic-purple':['#00E5FF','Space Grotesk','linear-gradient(135deg,#1A0050,#4A00E0)','#FFFFFF','#E0D4FF'],
 'ocean-depth':['#FFD93D','DM Sans','linear-gradient(135deg,#000028,#003366)','#FFFFFF','#CDEFFF'],
 'emerald-dusk':['#FFC857','Nunito','linear-gradient(135deg,#0A2E1A,#134E5E)','#FFFFFF','#DFF5E8'],
 'rose-gold-glow':['#FFD700','Playfair Display','linear-gradient(135deg,#0D0720,#1D2671)','#FFFFFF','#FFD9E6'],
 'amber-blaze':['#FFA500','Poppins','linear-gradient(135deg,#1A0A00,#7A3800)','#FFFFFF','#FFD9A0'],
 'midnight-bloom':['#F72585','Josefin Sans','linear-gradient(135deg,#0B0020,#0B3D47)','#FFFFFF','#D9F7FA'],
 'deep-navy':['#4A9EFF','Inter','linear-gradient(135deg,#0A1628,#0D3B6E)','#FFFFFF','#C8D8F0'],
 'dark-crimson':['#FF6666','Montserrat','linear-gradient(135deg,#1A0000,#5C0000)','#FFFFFF','#FFD0D0'],
 'dark-slate':['#38BDF8','Inter','linear-gradient(135deg,#0F172A,#1E293B)','#F1F5F9','#94A3B8'],
 'dark-forest':['#4ADE80','Nunito','linear-gradient(135deg,#052010,#0A3020)','#FFFFFF','#C8E6D0']
};
function hsl(c){ var x=rgb(c), r=x[0]/255, g=x[1]/255, b=x[2]/255, mx=Math.max(r,g,b), mn=Math.min(r,g,b), l=(mx+mn)/2, d=mx-mn, h=0, s=0;
  if (d>0){ s=d/(1-Math.abs(2*l-1)); h = mx===r ? ((g-b)/d)%6 : (mx===g ? (b-r)/d+2 : (r-g)/d+4); h*=60; if(h<0)h+=360; }
  return {h:h, s:s, l:l, c:d}; }
function fromHsl(h,s,l){ var c=(1-Math.abs(2*l-1))*s, x=c*(1-Math.abs((h/60)%2-1)), m=l-c/2, r=0,g=0,b=0; h=((h%360)+360)%360;
  if(h<60){r=c;g=x;}else if(h<120){r=x;g=c;}else if(h<180){g=c;b=x;}else if(h<240){g=x;b=c;}else if(h<300){r=x;b=c;}else{r=c;b=x;}
  return toHex([(r+m)*255,(g+m)*255,(b+m)*255]); }
function clamp01(v, a, b){ return Math.max(a, Math.min(b, v)); }
function tplBaseHue(T){
  var P=T.pal||{}, best=null, a0=hsl(P.accent||'#888888');
  if (a0.c > 0.12) return a0;
  ['accent2','bg','title','card','line','text'].forEach(function(k){ if(!P[k]) return; var q=hsl(P[k]); if(q.c>0.12 && (!best || q.c>best.c)) best=q; });
  return best || {h:210, s:0.5, l:0.5, c:0.3};
}
function parseThemeBg(bg){
  var m = String(bg).match(/linear-gradient\(\s*(\d+)deg\s*,([^)]*)\)/i);
  if (!m) return {solid:hx(bg), grad:null};
  var cols = m[2].match(/#[0-9a-fA-F]{6}/g) || [];
  if (cols.length < 2) return {solid:hx(cols[0] || '#FFFFFF'), grad:null};
  var stops = cols.map(function(c, i){ return [Math.round(i * 100 / (cols.length - 1)), hx(c)]; });
  return {solid:mix(cols[0], cols[cols.length - 1], 0.5), grad:{ang:parseInt(m[1], 10), stops:stops}};
}
DSTE.themed = function(T, key){
  var th = key && DSTE.THEMES[key]; if (!T || !th) return T;
  T.__th = T.__th || {};
  if (T.__th[key]) return T.__th[key];
  var D = Object.create(T), P = T.pal || {}, bgT = parseThemeBg(th[2]);
  D.fonts = {}; for (var k in T.fonts) D.fonts[k] = T.fonts[k];
  D.fonts.head = th[1];
  D.fonts.gf = (T.fonts.gf ? T.fonts.gf + '|' : '') + th[1] + (th[1] === 'Bebas Neue' ? '' : ':wght@400;700');
  var Lb = lum(P.bg || '#FFFFFF'), Lt = lum(P.text || P.title || '#000000');
  if (Math.abs(Lt - Lb) < 0.2) Lt = Lb > 0.5 ? 0 : 1;
  D.__rec = {
    Lb:Lb, Lt:Lt, base:tplBaseHue(T), bgq:hsl(P.bg || '#FFFFFF'),
    tpl:{title:hx(P.title || P.text || '#000000'), text:hx(P.text || '#000000'), muted:P.muted ? hx(P.muted) : null},
    bg:bgT.solid, grad:bgT.grad, title:hx(th[3]), text:hx(th[4]), muted:mix(th[4], bgT.solid, 0.3), ac:hsl(th[0]), acx:hx(th[0])
  };
  D.__themeKey = key;
  T.__th[key] = D; return D;
};
function toneT(c, R){ return (lum(c) - R.Lb) / (R.Lt - R.Lb); }
function mapColor(c, R, role){
  c = hx(c);
  if (role === 'fg') {
    if (c === R.tpl.title) return R.title;
    if (c === R.tpl.text) return R.text;
    if (R.tpl.muted && c === R.tpl.muted) return R.muted;
  }
  var q = hsl(c), G = R.bgq;
  var surface = G.c >= 0.12 && Math.abs(((q.h - G.h) % 360 + 540) % 360 - 180) <= 22 && Math.abs(q.l - G.l) <= 0.25;   /* tinted panels of a coloured template */
  if (!surface && q.c >= 0.16 && q.l > 0.2 && q.l < 0.86) {           /* accent family -> theme accent */
    var B = R.base, A = R.ac;
    var dh = ((q.h - B.h) % 360 + 540) % 360 - 180; if (Math.abs(dh) > 45) dh = 0;
    return fromHsl(A.h + dh * 0.5, clamp01((A.s || 0.05) * (q.s / (B.s || 0.5)), 0.05, 1), clamp01(A.l + (q.l - (B.l != null ? B.l : 0.5)), 0.12, 0.92));
  }
  var t = toneT(c, R);                                                   /* neutrals / tints -> between theme bg and text */
  if (role === 'fg') return t >= 0.85 ? mix(R.bg, R.title, clamp01(t, 0, 1)) : mix(R.bg, R.text, clamp01(t, 0, 1));
  if (t < 0) return mix(R.bg, R.title, Math.min(0.12, -t * 0.8));       /* cards lifted off the background */
  return mix(R.bg, R.title, clamp01(t, 0, 1));
}
function mapPageBg(bg, R){
  if (!bg) return bg;
  var cols = [];
  if (bg.grad && bg.grad.stops) bg.grad.stops.forEach(function(s){ if (typeof s[1] === 'string') cols.push(s[1]); });
  else if (bg.fill) cols.push(bg.fill);
  if (!cols.length) return bg;
  var t = 0; cols.forEach(function(c){ t += toneT(hx(c), R); }); t /= cols.length;
  var q = hsl(cols[0]);
  if (t < 0.5 || (q.c >= 0.16 && Math.abs(lum(cols[0]) - R.Lb) < 0.2)) return R.grad ? {grad:R.grad} : {fill:R.bg};   /* the template's normal background */
  return recolorAll(bg, R, 'fill');                                     /* an inverted (dark on light) slide stays inverted */
}
/* returns a recoloured copy (template constants such as shared gradients are never mutated) */
function recolorAll(o, R, role){
  if (typeof o === 'string') return /^#[0-9a-fA-F]{6}$/.test(o) ? mapColor(o, R, role || 'fill') : o;
  if (!o || typeof o !== 'object') return o;
  var out = Array.isArray(o) ? [] : {};
  for (var k in o) {
    if (!Object.prototype.hasOwnProperty.call(o, k)) continue;
    if (k === 'ed' || k === 'text' || k === 'd' || k === 'name' || k === 'src' || k === 'fam' || k === 'spec') { out[k] = o[k]; continue; }
    var r = role || 'fill';
    if (!role && k === 'color' && (o.t === 'text' || o.t === 'icon')) r = 'fg';
    if (!role && (k === 'tc' || k === 'mc') && o.t === 'chart') r = 'fg';
    if (!role && k === 'colors' && o.t === 'chart' && window.DS_CHART && Array.isArray(o[k])) { out[k] = window.DS_CHART.palette(R.acx, lum(R.bg) < 0.5, o[k].length); continue; }
    out[k] = recolorAll(o[k], R, r);
  }
  return out;
}
/* colour a point of the slide shows (page background + filled shapes stacked under it) */
function avgFill(e){
  if (e.grad && e.grad.stops && e.grad.stops.length) { var c = hx(e.grad.stops[0][1]), n = e.grad.stops.length; return mix(c, hx(e.grad.stops[n-1][1]), 0.5); }
  return e.fill ? hx(e.fill) : null;
}
function gradAt(e, px, py){
  var g = e.grad; if (!g || !g.stops || !g.stops.length) return e.fill ? hx(e.fill) : null;
  var st = g.stops, t;
  if (g.type === 'radial') { var dx = (px - (e.x + e.w/2)) / (e.w/2), dy = (py - (e.y + e.h/2)) / (e.h/2); t = Math.min(1, Math.sqrt(dx*dx + dy*dy)); }
  else {
    var a = (g.ang != null ? g.ang : 135) * Math.PI / 180, vx = Math.sin(a), vy = -Math.cos(a);
    var half = Math.abs(vx) * e.w / 2 + Math.abs(vy) * e.h / 2 || 1;
    t = clamp01(0.5 + ((px - (e.x + e.w/2)) * vx + (py - (e.y + e.h/2)) * vy) / (2 * half), 0, 1);
  }
  var pct = t * 100, lo = st[0], hi = st[st.length - 1];
  for (var i = 0; i < st.length - 1; i++) if (pct >= st[i][0] && pct <= st[i+1][0]) { lo = st[i]; hi = st[i+1]; break; }
  var span = (hi[0] - lo[0]) || 1;
  return mix(hx(lo[1]), hx(hi[1]), clamp01((pct - lo[0]) / span, 0, 1));
}
function colorUnder(C, idx, px, py){
  var bg = C.bgFill || {}, col = bg.grad ? avgFill(bg) : hx(bg.fill || '#FFFFFF');
  for (var i = 0; i < idx; i++) {
    var e = C.els[i];
    if (e.t !== 'rect' && e.t !== 'ell' && e.t !== 'shape' && e.t !== 'img') continue;
    if (px < e.x || px > e.x + e.w || py < e.y || py > e.y + e.h) continue;
    if (e.t === 'img') { col = '#6B7280'; continue; }
    var f = gradAt(e, px, py); if (!f) continue;
    var op = e.op != null ? e.op : 1;
    if (e.grad && e.grad.stops) { var so = e.grad.stops[0][2]; if (so != null) op *= (so + (e.grad.stops[e.grad.stops.length-1][2] != null ? e.grad.stops[e.grad.stops.length-1][2] : 1)) / 2; }
    if (e.grad && e.grad.type === 'radial') op *= 0.5;
    col = mix(col, f, clamp01(op, 0, 1));
  }
  return col;
}
/* after re-colouring, make sure every text / icon still reads on what is behind it */
function fixContrast(C, R){
  C.els.forEach(function(e, i){
    if ((e.t !== 'text' && e.t !== 'icon') || !e.color) return;
    var under = colorUnder(C, i, e.x + e.w / 2, e.y + Math.min(e.h / 2, 30)), lu = lum(under), lc = lum(e.color);
    var need = e.t === 'icon' ? 0.3 : 0.38;
    if (Math.abs(lc - lu) >= need) return;
    var cands = [R.title, R.text, R.bg, '#FFFFFF', '#111827'], best = e.color, bd = Math.abs(lc - lu);
    cands.forEach(function(c, k){ var d = Math.abs(lum(c) - lu) + (k < 3 ? 0.08 : 0); if (d > bd) { bd = d; best = c; } });
    e.color = best;
  });
}
function recolorPage(C, R){
  C.bgFill = mapPageBg(C.bgFill, R);
  C.els = C.els.map(function(e){
    if ((e.t === 'rect' || e.t === 'shape') && e.x <= 4 && e.y <= 4 && e.w >= W - 8 && e.h >= H - 8 && !e.line) {   /* full-slide backdrop */
      var b = mapPageBg(e.grad ? {grad:e.grad} : {fill:e.fill}, R), c = recolorAll(e, R);
      if (b.grad) { c.grad = b.grad; delete c.fill; } else if (b.fill) { c.fill = b.fill; delete c.grad; }
      return c;
    }
    return recolorAll(e, R);
  });
  fixContrast(C, R);
}

/* ============================================================
   RENDER a slide -> {bg, els}
   ============================================================ */
/* company logo on template decks: Snippet 17 (window.dsceLogoEls) gives the boxes in 1280x720 units */
DSTE.addLogo = function(C, kind){
var d = C.deck, L = d && d.logo; if (!L || !L.src || typeof window.dsceLogoEls !== 'function') return null;
var cov = kind==='cover'; if (cov && L.cover===false) return null;
var bg = (C.bgFill && (C.bgFill.fill || (C.bgFill.grad && C.bgFill.grad.stops && C.bgFill.grad.stops[0] && C.bgFill.grad.stops[0][1]))) || C.P.bg || '#FFFFFF';
var ac = hx(C.P.accent || '#3B82F6');
var r = window.dsceLogoEls(L, cov, hx(bg), ac); if (!r || !r.logo) return null;
var badge = r.badge ? C.rect(r.badge.x, r.badge.y, r.badge.w, r.badge.h, {fill:r.badge.fill, op:r.badge.op, r:r.badge.r, line:ac, lw:1, lop:0.4}) : null;
C.add({t:'logo', x:r.logo.x, y:r.logo.y, w:r.logo.w, h:r.logo.h, _src:L.src});
return {fix:function(){ if (badge) { badge.fill = r.badge.fill; badge.op = r.badge.op; } }};
};
DSTE.renderPage = function(T, slide, vid, idx, deck){
  var C = new Ctx(T, slide, idx, vid, deck);
  var kind = C.kind, list = DSTE.variantsFor(T, kind);
  if (kind==='section') kind='text';
  var v = null; for (var i=0;i<list.length;i++){ if(list[i].id===vid){ v=list[i]; break; } }
  if (!v) v = list[0];
  C.v = v; C.o = v.o || {};
  try {
    if (T.bg) T.bg(C, kind);
    if (kind==='cover') { (T.cover||DSTE.genericCover)(C, v); }
    else if (kind==='closing') { (T.closing||DSTE.genericClosing)(C, v); }
    else if (v.fn) { v.fn(C, C.o); }
    else if (kind==='quote' && T.quote) { T.quote(C, v); }
    else if (kind==='agenda' && T.agenda && !C.o.body) { T.agenda(C, v); }
    else {
      var o = {}; for (var k in C.o) o[k]=C.o[k];
      if (kind==='quote') { o.hk='none'; DSTE.quoteSlide(C,o); }
      else DSTE.std(C, o);
    }
    if (T.decor) T.decor(C, kind);
    if (kind!=='cover' && kind!=='closing' && C.o.foot!==false) footer(C);
  } catch(err) {
    if (window.console) console.error('[DSTE] render error', T.id, kind, err);
  }
  var _lg = null; try { _lg = DSTE.addLogo(C, kind); } catch(e){}
    if (T.__rec) recolorPage(C, T.__rec);
    if (_lg) _lg.fix();
  return {bg:C.bgFill, els:C.els, kind:kind, vid:v.id};
};

DSTE.quoteSlide = function(C,o){
  var S=C.S, M=S.margin||64, A={x:M,w:W-2*M};
  if (o.img==='right'||o.img==='left'){
    var iw=Math.round(W*0.42), framed=!!S.imgFramed;
    if (framed) imgBox(C,o.img==='right'?W-iw:28,28,iw-28,H-56,{r:S.imgR!=null?S.imgR:20}); else imgBox(C,o.img==='right'?W-iw:0,0,iw,H,{r:0});
    A.x=o.img==='right'?M:iw+56; A.w=W-iw-M-56;
  }
  B.quote(C,{x:A.x,y:120,w:A.w,h:H-220},o);
};

/* generic cover / closing (templates normally override) */
DSTE.genericCover = function(C,v){
  var P=C.P, f=C.cover, M=C.S.margin||64, o=C.o||{};
  var x=M, w=W-2*M;
  if (o.img==='right'){ imgBox(C,W*0.52,0,W*0.48,H,{}); w=W*0.52-M-40; }
  if (f.company) C.text(x,56,w,24,f.company,{font:'body',size:15,wt:700,color:P.accent,caps:true,ls:0.16,ed:{f:'tf-company'}});
  var te=C.text(x,190,w,230,C.s.title,{font:'head',fit:{max:76,min:36},color:P.title||P.text,ed:{f:'title'},maxLines:3,va:'b'});
  C.rect(x,436,90,5,{fill:P.accent});
  if (C.s.subtitle) C.text(x,462,w,80,C.s.subtitle,{font:'body',fit:{max:22,min:14},color:P.muted,ed:{f:'subtitle'},lh:1.4,maxLines:3});
  var meta=[f.presenter,f.designation,f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x,640,w,24,meta,{font:'body',size:14,wt:600,color:P.muted,ed:null});
};
DSTE.genericClosing = function(C,v){
  var P=C.P, M=C.S.margin||64;
  C.text(M,200,W-2*M,160,C.s.title||'Thank You',{font:'head',fit:{max:96,min:40},color:P.title||P.text,align:'c',ed:{f:'title'},maxLines:2,va:'b'});
  if (C.s.subtitle) C.text(M+120,380,W-2*M-240,70,C.s.subtitle,{font:'body',fit:{max:22,min:14},color:P.muted,align:'c',ed:{f:'subtitle'},maxLines:2});
  DSTE.contactRow(C,M,520,W-2*M,{});
};
/* contact lines from closing content (Email:, Website:, Phone:) with icons */
DSTE.contacts = function(C){
  var out=[], map={email:'mdi:email-outline',website:'mdi:web',phone:'mdi:phone-outline',address:'mdi:map-marker-outline',linkedin:'mdi:linkedin'};
  C.it.forEach(function(t){ var k=(t.h||'').toLowerCase(); var ic=map[k]||(/@/.test(t.d)?map.email:(/www|http|\.com/.test(t.d)?map.website:(/\+?\d[\d\s\-()]{6,}/.test(t.d)?map.phone:null))); if(ic) out.push({i:t.i,icon:ic,label:t.h,val:t.d}); });
  if (!out.length && C.cover.website) out.push({i:-1,icon:map.website,label:'Website',val:C.cover.website});
  return out.slice(0,3);
};
DSTE.contactRow = function(C,x,y,w,o){
  var P=C.P, cs=DSTE.contacts(C); if(!cs.length) return;
  var n=cs.length, gap=24, cw=(w-gap*(n-1))/n;
  cs.forEach(function(c,k){
    var cx0=x+k*(cw+gap);
    if (o.cards) card(C,cx0,y,cw,84,{});
    mark(C,cx0+(o.cards?18:0),y+(o.cards?20:0),44,c.icon,{});
    C.text(cx0+(o.cards?18:0)+58,y+(o.cards?18:0),cw-80,20,c.label||'',{font:'body',size:12,wt:700,color:o.lc||P.accent,caps:true,ls:0.12,lh:1});
    C.text(cx0+(o.cards?18:0)+58,y+(o.cards?40:22),cw-80,26,c.val,{font:'body',fit:{max:17,min:11},wt:600,color:o.vc||P.text,ed:c.i>=0?{f:'x-part',i:c.i,p:1}:null,maxLines:1});
  });
};
DSTE.contactList = function(C,x,y,w,o){
  var P=C.P, cs=DSTE.contacts(C); o=o||{};
  cs.forEach(function(c,k){
    var yy=y+k*(o.gap||56);
    mark(C,x,yy,40,c.icon,o.markO||{});
    C.text(x+56,yy,w-56,40,c.val,{font:'body',fit:{max:18,min:11},wt:600,color:o.vc||P.text,va:'m',ed:c.i>=0?{f:'x-part',i:c.i,p:1}:null,maxLines:1});
  });
};

/* ============================================================
   HTML OUTPUT (editor preview + thumbnails + layout panel)
   Sizes use container-query units so one render scales anywhere.
   ============================================================ */
function cq(v){ return (v/12.8).toFixed(3)+'cqw'; }
function px(v,d){ return (v/d*100).toFixed(3)+'%'; }
function cssGrad(g,op){
  if (!g || !g.stops) return '';
  var st=g.stops.map(function(s){ return rgba(s[1], (s[2]!=null?s[2]:1)*(op!=null?op:1))+' '+s[0]+'%'; }).join(',');
  if (g.type==='radial') return 'radial-gradient(closest-side,'+st+')';
  return 'linear-gradient('+(g.ang!=null?g.ang:135)+'deg,'+st+')';
}
DSTE.cssGrad = cssGrad;
var CLIP = {
  chevron:'polygon(0 0,calc(100% - 18%) 0,100% 50%,calc(100% - 18%) 100%,0 100%,18% 50%)',
  homePlate:'polygon(0 0,calc(100% - 18%) 0,100% 50%,calc(100% - 18%) 100%,0 100%)',
  triangle:'polygon(50% 0,100% 100%,0 100%)',
  rtTriangle:'polygon(0 0,100% 100%,0 100%)',
  diamond:'polygon(50% 0,100% 50%,50% 100%,0 50%)',
  hexagon:'polygon(25% 0,75% 0,100% 50%,75% 100%,25% 100%,0 50%)',
  parallelogram:'polygon(25% 0,100% 0,75% 100%,0 100%)',
  pentagon:'polygon(50% 0,100% 38%,82% 100%,18% 100%,0 38%)'
};
DSTE.toHTML = function(page, idx, opts){
  opts=opts||{};
  var edit = opts.edit!==false;
  var bg = page.bg||{}, bgCss = bg.grad ? ('background:'+cssGrad(bg.grad)+';') : ('background:'+hx(bg.fill||'#FFFFFF')+';');
  var h = '<div class="dste-slide" style="position:absolute;inset:0;overflow:hidden;container-type:size;'+bgCss+'line-height:1.2!important;">';
  page.els.forEach(function(e){
    var box = 'position:absolute;left:'+px(e.x,W)+';top:'+px(e.y,H)+';width:'+px(e.w,W)+';height:'+px(e.h,H)+';';
    if (e.t==='rect' || e.t==='ell' || e.t==='shape') {
      var s = box;
      if (e.grad) s += 'background:'+cssGrad(e.grad,e.op)+';';
      else if (e.fill) s += 'background:'+rgba(e.fill,e.op!=null?e.op:1)+';';
      if (e.line) s += 'border:'+(e.dash?'dashed ':'solid ')+'max(0.5px,'+cq(e.lw||1)+') '+rgba(e.line,e.lop!=null?e.lop:1)+';box-sizing:border-box;';
      if (e.t==='ell') s += 'border-radius:50%;';
      else if (e.r) s += 'border-radius:'+cq(e.r)+';';
      if (e.t==='shape' && CLIP[e.prst]) s += 'clip-path:'+CLIP[e.prst]+';';
      if (e.rot) s += 'transform:rotate('+e.rot+'deg);';
      if (e.shadow) s += 'box-shadow:0 '+cq(8)+' '+cq(24)+' rgba(0,0,0,'+(typeof e.shadow==='number'?e.shadow:0.18)+');';
      h += '<div style="'+s+'pointer-events:none;"></div>';
    } else if (e.t==='line') {
      h += svgWrap('<line x1="'+e.x1+'" y1="'+e.y1+'" x2="'+e.x2+'" y2="'+e.y2+'" stroke="'+hx(e.color)+'" stroke-opacity="'+(e.op!=null?e.op:1)+'" stroke-width="'+(e.lw||1)+'"'+(e.dash?' stroke-dasharray="6 5"':'')+'/>');
    } else if (e.t==='path') {
      h += svgWrap('<path d="'+pathD(e.d)+'" fill="'+(e.fill?hx(e.fill):'none')+'" fill-opacity="'+(e.op!=null?e.op:1)+'" stroke="'+(e.stroke?hx(e.stroke):'none')+'" stroke-opacity="'+(e.lop!=null?e.lop:1)+'" stroke-width="'+(e.lw||1)+'" stroke-linecap="round" stroke-linejoin="round"/>');
    } else if (e.t==='icon') {
      var u='https://api.iconify.design/'+String(e.name).replace(':','/')+'.svg?color='+encodeURIComponent(hx(e.color));
      h += '<img src="'+u+'" alt="" draggable="false" style="'+box+'display:block;pointer-events:none;" onerror="this.style.visibility=\'hidden\'"/>';
    } else if (e.t==='ring') {
      var th=(e.th/e.w*50).toFixed(2), pc=e.pct;
      h += '<div style="'+box+'border-radius:50%;background:conic-gradient('+hx(e.color)+' 0 '+pc+'%,'+hx(e.track||'#DDDDDD')+' '+pc+'% 100%);-webkit-mask:radial-gradient(farthest-side,transparent calc(100% - '+th+'%),#000 calc(100% - '+th+'% + 0.5px));mask:radial-gradient(farthest-side,transparent calc(100% - '+th+'%),#000 calc(100% - '+th+'% + 0.5px));pointer-events:none;"></div>';
    } else if (e.t==='img') {
      var sl = opts.slide || {};
      var img = sl.image_data || null, pos = sl.image_position || {x:50,y:50};
      var rad = e.shape==='ell' ? 'border-radius:50%;' : (e.r ? 'border-radius:'+cq(e.r)+';' : '');
      var inner = img ? ('background-image:url('+img+');background-size:cover;background-repeat:no-repeat;background-position:'+pos.x+'% '+pos.y+'%;')
                      : ('background:'+(e.ph&&e.ph.grad?cssGrad(e.ph.grad):(e.ph&&e.ph.fill?hx(e.ph.fill):'#8899AA'))+';');
      var phHtml = img ? '' : '<div style="display:flex;flex-direction:column;align-items:center;gap:'+cq(8)+';opacity:.75;color:#fff;font:600 '+cq(13)+' Arial,sans-serif;"><svg viewBox="0 0 24 24" style="width:'+cq(40)+';height:'+cq(40)+'" fill="#fff"><path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2M8.5 13.5l2.5 3 3.5-4.5 4.5 6H5z"/></svg>Click to add image</div>';
      if (edit) {
        h += '<div class="dsp-vp-imgzone" data-slide-idx="'+idx+'" style="'+box+rad+'overflow:hidden;">'
          + '<div style="position:absolute;inset:0;'+inner+'display:flex;align-items:center;justify-content:center;pointer-events:none;">'+phHtml+'</div>'
          + (img?'<button type="button" class="dsp-vp-imgreset" onclick="event.stopPropagation();dspResetImagePosition('+idx+');" title="Reset image position">⟲</button><button type="button" class="dsp-vp-cropbtn" onclick="event.stopPropagation();window.vpStartCropEdit('+idx+');" title="Crop image">✂️ Crop</button>':'')
          + '<div class="dsp-vp-ihover" style="pointer-events:none;"><div class="dsp-vp-ihover-inner" style="pointer-events:auto;"><div>🖼</div><div>Change Image</div></div></div>'
          + '</div>';
      } else {
        h += '<div style="'+box+rad+'overflow:hidden;"><div style="position:absolute;inset:0;'+inner+'display:flex;align-items:center;justify-content:center;">'+phHtml+'</div></div>';
      }
    } else if (e.t==='chart') {
      var DCc = window.DS_CHART, ci = edit && idx>=0 ? idx : -1;
      h += '<div class="dsch-box" data-dsch-idx="'+ci+'" style="'+box+'">'+(DCc ? DCc.svg(e.spec,{w:e.w,h:e.h,colors:e.colors,tc:e.tc,mc:e.mc,bgc:e.bgc,legend:e.legend,uid:'dst'+idx,font:C_font(e)}) : '')
        + (ci>=0 ? '<button type="button" class="dsch-edit" onclick="event.stopPropagation();window.DS_CHART&&DS_CHART.openEditor('+ci+')">&#9998; Edit chart</button>' : '') + '</div>';
    } else if (e.t==='logo') {
      h += '<img class="dste-logo" src="'+(e._src||'')+'" alt="" draggable="false" style="'+box+'display:block;object-fit:contain;pointer-events:none;"/>';
    } else if (e.t==='text') {
      var jc = e.va==='m'?'center':(e.va==='b'?'flex-end':'flex-start');
      var fam = "'"+e.fam+"',Arial,sans-serif";
      var ts = 'font-family:'+fam+'!important;font-size:'+cq(e.size)+';font-weight:'+e.wt+';color:'+rgba(e.color,e.op!=null?e.op:1)+';line-height:'+(e.lh||1.2)+'!important;text-align:'+(e.align==='c'?'center':(e.align==='r'?'right':'left'))+';'
             + (e.caps?'text-transform:uppercase;':'') + (e.ls?'letter-spacing:'+e.ls+'em;':'') + (e.italic?'font-style:italic;':'')
             + (e.nw?'white-space:nowrap;':'white-space:pre-wrap;overflow-wrap:break-word;')+'margin:0;padding:0;outline:none;';
      var ed = '';
      if (edit && e.ed && idx>=0) {
        ed = ' contenteditable="true" spellcheck="false" data-ed-slide="'+idx+'" data-ed-field="'+e.ed.f+'"'+(e.ed.i!=null?' data-ed-idx="'+e.ed.i+'"':'')+(e.ed.p!=null?' data-ed-part="'+e.ed.p+'"':'');
        ts += 'cursor:text;pointer-events:auto;min-height:1em;';
      }
      h += '<div style="'+box+'display:flex;flex-direction:column;justify-content:'+jc+';pointer-events:none;"><div'+ed+' style="'+ts+'">'+esc(e.text)+'</div></div>';
    }
  });
  return h + '</div>';
};
function C_font(e){ return e.fam || ''; }
function svgWrap(inner){ return '<svg viewBox="0 0 1280 720" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;overflow:visible;">'+inner+'</svg>'; }
function pathD(d){ return d.map(function(c){ return c[0]+(c.length>1?' '+c.slice(1).join(' '):''); }).join(' '); }

/* ============================================================
   REGISTRY
   ============================================================ */
DSTE.register = function(T){
  if (!T || !T.id) return;
  if (!DSTE.reg[T.id]) DSTE.order.push(T.id);
  T.fonts = T.fonts || {head:'Arial',body:'Arial',headW:700,bodyW:400};
  T.s = T.s || {}; T.pal = T.pal || {};
  var P = T.pal; P.title = P.title || P.text; P.onAccent = P.onAccent || '#FFFFFF'; P.line = P.line || mix(P.text||'#000000',P.bg||'#FFFFFF',0.82);
  DSTE.reg[T.id] = T;
  if (DSTE.onRegister) try { DSTE.onRegister(T); } catch(e){}
};
DSTE.get = function(id){ return DSTE.reg[id] || null; };
DSTE.renderHTML = function(T, slide, vid, idx, deck, opts){
  var page = DSTE.renderPage(T, slide, vid, idx, deck);
  opts = opts || {}; opts.slide = slide;
  return DSTE.toHTML(page, idx, opts);
};
})();

/* ---------- reusable full-slide compositions ---------- */
(function(){
var DSTE = window.DSTE, W = DSTE.W, H = DSTE.H, u = DSTE.u;
/* agenda: big title on the left, numbered list on the right */
DSTE.agendaSplit = function(C, o){
  o = o || {}; var P = C.P, M = C.S.margin || 64, split = o.split || 0.42;
  var lw = Math.round((W - 2*M) * split);
  var te = C.text(M, o.titleY || 200, lw - 30, 240, C.s.title || 'Agenda', {font:'head', fit:{max:o.tMax||76, min:36}, color:P.title, ed:{f:'title'}, maxLines:3, va:o.titleVa||'t'});
  if (C.s.subtitle) C.text(M, (o.titleY||200) + te._h + 20, lw - 40, 120, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var x = M + lw + 30, w = W - M - x;
  if (o.rule !== false) C.rect(x - 30, 70, 1.5, H - 140, {fill:o.ruleC || P.line});
  var it = C.it.slice(0, 10), n = it.length, top = o.listY || 90, bot = H - 90, rh = Math.min(o.rowMax || 64, (bot - top) / Math.max(1, n));
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), w - 90, rh * 0.62, {font:o.itemFont||'body', wt:o.itemWt||600, max:o.iMax||21, min:12, maxLines:1});
  var y0 = top + ((bot - top) - rh * n) / 2;
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    C.text(x + 10, y, 60, rh, u.pad2(k+1), {font:'head', size:Math.min(hs+2, 22), color:o.numC || P.accent, va:'m', lh:1, caps:false, ls:0});
    C.text(x + 80, y, w - 90, rh, t.h || t.d, {font:o.itemFont||'body', wt:o.itemWt||600, size:hs, color:P.text, va:'m', lh:1.2, ed:{f:'x-part', i:t.i, p:t.h?0:1}, maxLines:1});
    if (o.rows !== false && k < n - 1) C.rect(x + 10, y + rh - 0.5, w - 10, 1, {fill:P.line, op:0.8});
  });
};
/* hexagon outline decoration */
DSTE.hexOutline = function(C, cx, cy, r, o){
  o = o || {}; var pts = [];
  for (var k = 0; k < 6; k++){ var a = Math.PI/180*(60*k - 30); pts.push([cx + r*Math.cos(a), cy + r*Math.sin(a)]); }
  var d = [['M', pts[0][0], pts[0][1]]]; for (var j = 1; j < 6; j++) d.push(['L', pts[j][0], pts[j][1]]); d.push(['Z']);
  C.path(d, {stroke:o.color || '#FFFFFF', lw:o.lw || 1.5, lop:o.op != null ? o.op : 0.5});
};
/* soft glow blob (radial gradient ellipse fading to transparent) */
DSTE.glow = function(C, cx, cy, rx, ry, color, op){
  C.ell(cx - rx, cy - ry, rx*2, ry*2, {grad:{type:'radial', stops:[[0, color, op == null ? 0.9 : op], [100, color, 0]]}});
};
/* four-point sparkle star */
DSTE.sparkle = function(C, cx, cy, r, color, op){
  var k = r * 0.18;
  C.path([['M',cx,cy-r],['Q',cx+k,cy-k,cx+r,cy],['Q',cx+k,cy+k,cx,cy+r],['Q',cx-k,cy+k,cx-r,cy],['Q',cx-k,cy-k,cx,cy-r],['Z']], {fill:color, op:op == null ? 1 : op});
};
/* merge helper for per-slide style overrides */
DSTE.merge = function(a, b){ var o = {}, k; for (k in a) o[k] = a[k]; for (k in b) { if (b[k] && typeof b[k] === 'object' && !Array.isArray(b[k]) && a && a[k] && typeof a[k] === 'object') o[k] = DSTE.merge(a[k], b[k]); else o[k] = b[k]; } return o; };
})();

(function(){
var D = window.DSTE;
/* bundle of flowing lines (wave ribbons) */
D.waveBundle = function(C, o){
  var n = o.n || 14, x0 = o.x0, y0 = o.y0, x1 = o.x1, y1 = o.y1, amp = o.amp || 90, sp = o.spread || 6;
  for (var k = 0; k < n; k++){
    var t = k / Math.max(1, n - 1), dy = (k - n/2) * sp, ph = (o.phase || 0) + t * 1.4;
    var cx1 = x0 + (x1 - x0) * 0.33, cx2 = x0 + (x1 - x0) * 0.66;
    var a1 = amp * Math.sin(ph * 2.1), a2 = amp * Math.cos(ph * 1.7);
    C.path([['M', x0, y0 + dy], ['C', cx1, y0 + dy - a1 + (y1 - y0) * 0.2, cx2, y1 + dy + a2 - (y1 - y0) * 0.2, x1, y1 + dy * (o.fan || 1.6)]], {stroke:o.color, lw:o.lw || 1.2, lop:(o.op != null ? o.op : 0.6) * (o.fade ? (0.35 + 0.65 * (1 - Math.abs(t - 0.5) * 2)) : 1)});
  }
};
/* circle arc (for orbit / chrome decorations) */
D.arc = function(C, cx, cy, r, a0, a1, o){
  /* true circular arc: cubic Bezier segments of at most 45 degrees (no visible facets) */
  var n = Math.max(1, Math.ceil(Math.abs(a1 - a0) / 45)), step = (a1 - a0) / n, rad = Math.PI / 180;
  var k = 4 / 3 * Math.tan(step * rad / 4);
  var d = [['M', cx + r * Math.cos(a0 * rad), cy + r * Math.sin(a0 * rad)]];
  for (var i = 0; i < n; i++) {
    var s0 = (a0 + step * i) * rad, s1 = (a0 + step * (i + 1)) * rad;
    var x0 = cx + r * Math.cos(s0), y0 = cy + r * Math.sin(s0), x3 = cx + r * Math.cos(s1), y3 = cy + r * Math.sin(s1);
    d.push(['C', x0 - k * r * Math.sin(s0), y0 + k * r * Math.cos(s0), x3 + k * r * Math.sin(s1), y3 - k * r * Math.cos(s1), x3, y3]);
  }
  C.path(d, {stroke:o.color, lw:o.lw || 1.5, lop:o.op != null ? o.op : 1});
};
/* clean full ring (outline circle) */
D.ring = function(C, cx, cy, r, o){ o = o || {}; C.ell(cx - r, cy - r, r * 2, r * 2, {line:o.color || '#FFFFFF', lw:o.lw || 2, lop:o.op != null ? o.op : 1, fill:o.fill, op:o.fop}); };
})();

/* ============================================================
   DS TEMPLATE ENGINE — router + editor integration
   - template picker on the AI Presentation home screen
   - sends generation / download to the picked template's snippet
   - renders the editor, thumbnails and layout panel for that template
   ============================================================ */
(function(){
'use strict';
var DSTE = window.DSTE; if (!DSTE || DSTE.router) return;
DSTE.router = 1;
var u = DSTE.u;
var PICK_KEY = 'dsteTplPick';

/* ---------- which template is picked / active ---------- */
DSTE.pick = (function(){ try { return sessionStorage.getItem(PICK_KEY) || 'classic'; } catch(e){ return 'classic'; } })();
function setPick(id){
  DSTE.pick = id;
  try { sessionStorage.setItem(PICK_KEY, id); } catch(e){}
  syncHomeUI();
}
function deck(){ if (window.S && window.S.data && window.S.data.slides) return window.S.data; if (window.G && window.G.pptData) return window.G.pptData; return null; }
function activeId(){
  var d1 = window.S && window.S.data, d2 = window.G && window.G.pptData;
  var id = (d1 && d1.tpl) || (d2 && d2.tpl) || null;
  return id && DSTE.reg[id] ? id : null;
}
DSTE.activeId = activeId;
function pickedTpl(){ return DSTE.pick && DSTE.pick!=='classic' && DSTE.reg[DSTE.pick] ? DSTE.pick : null; }

/* ---------- theme on a template deck ('' = the template's own colours) ---------- */
var THEME_KEY = 'dsteTheme';
DSTE.homeTheme = (function(){ try { return sessionStorage.getItem(THEME_KEY) || ''; } catch(e){ return ''; } })();
function setHomeTheme(k){ DSTE.homeTheme = k || ''; try { sessionStorage.setItem(THEME_KEY, DSTE.homeTheme); } catch(e){} }
function deckTheme(){ var d = deck(); return (d && d.tplTheme) || ''; }
function setDeckTheme(k){
  [window.S && window.S.data, window.G && window.G.pptData].forEach(function(d){ if (d && d.slides) d.tplTheme = k || ''; });
}
function tplFor(id, key){ var T = DSTE.reg[id]; return T ? DSTE.themed(T, key) : null; }
function curT(){ var id = activeId(); return id ? tplFor(id, deckTheme()) : null; }
DSTE.curT = curT;

/* ---------- hook used by Snippet 17 (renderSlide / vpRender / canvas observer) ---------- */
window.DS_TPL_ACTIVE = function(){
  var id = activeId(); if (!id) return null;
  var T = curT();
  return {
    id: id,
    render: function(s, v, idx){ return DSTE.renderHTML(curT(), s, v || 'v1', typeof idx==='number' ? idx : -1, deck(), {edit:true}); },
    variants: function(type, slide){ var k = DSTE.kindOf(slide || {type:type}); if (k==='section') k='text'; return DSTE.variantsFor(T, k).map(function(v){ return {id:v.id, lbl:v.lbl}; }); }
  };
};

/* ---------- fonts ---------- */
var _fontsReq = {};
DSTE.loadFonts = function(T){
  if (!T || !T.fonts || !T.fonts.gf || _fontsReq[T.id + '|' + T.fonts.head]) return;
  _fontsReq[T.id + '|' + T.fonts.head] = 1;
  T.fonts.gf.split('|').forEach(function(f){               /* one request per family, so one bad family never blocks the rest */
    if (_fontsReq['gf:' + f]) return; _fontsReq['gf:' + f] = 1;
    var l = document.createElement('link'); l.rel = 'stylesheet';
    l.href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g,'+') + '&display=swap';
    document.head.appendChild(l);
  });
  if (document.fonts && document.fonts.load) {
    var hw = T.fonts.headW || 700, bw = T.fonts.bodyW || 400;
    var reqs = [];
    [400,500,600,700,800].forEach(function(w){ reqs.push(document.fonts.load(w + ' 40px "' + T.fonts.head + '"')); reqs.push(document.fonts.load(w + ' 16px "' + T.fonts.body + '"')); });
    reqs.push(document.fonts.load('italic 400 16px "' + T.fonts.head + '"'));
    Promise.all(reqs).then(function(){ DSTE.clearFit(); refreshAll(); }).catch(function(){});
  }
};
DSTE.onRegister = function(T){ if (pickedTpl()===T.id || activeId()===T.id) DSTE.loadFonts(T); scheduleHomeUI(); };

/* ---------- re-render editor (after fonts load / edits) ---------- */
function refreshAll(){
  var id = activeId(); if (!id) { syncHomeUI(true); return; }
  var d = deck(); if (!d || !d.slides) return;
  var T = curT(), ch = (window.S && window.S.choices) || {};
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (thumbs) Array.prototype.forEach.call(thumbs.children, function(item, i){
    var box = item.querySelector('.dsp-thumb-box'); if (!box || !d.slides[i]) return;
    box.innerHTML = DSTE.renderHTML(T, d.slides[i], ch['slide_'+i] || 'v1', i, d, {edit:false});
  });
  var idx = (window.S && window.S.idx >= 0) ? window.S.idx : 0;
  if (typeof window.applyCanvas === 'function') window.applyCanvas(idx, ch['slide_'+idx] || 'v1');
}
DSTE.refreshEditor = refreshAll;

/* ---------- inline editing for template-only fields ---------- */
document.addEventListener('input', function(e){
  var el = e.target; if (!el || !el.getAttribute) return;
  var f = el.getAttribute('data-ed-field'); if (!f || f.indexOf('x-') !== 0) return;
  var si = parseInt(el.getAttribute('data-ed-slide'), 10);
  var targets = [];
  if (window.S && window.S.data && window.S.data.slides && window.S.data.slides[si]) targets.push(window.S.data.slides[si]);
  if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[si] && targets.indexOf(window.G.pptData.slides[si]) < 0) targets.push(window.G.pptData.slides[si]);
  var txt = (el.innerText || '').replace(/\n+/g,' ').trim();
  targets.forEach(function(slide){
    if (f === 'x-conc') { slide.conclusion = txt; return; }
    if (f === 'x-tpart') {
      var root = el.closest ? el.closest('.dste-slide') : null, pts = [];
      if (root) Array.prototype.forEach.call(root.querySelectorAll('[data-ed-field="x-tpart"]'), function(n){ pts[parseInt(n.getAttribute('data-ed-part'),10)||0] = (n.innerText||'').replace(/\n+/g,' ').trim(); });
      slide.title = pts.filter(Boolean).join(' ');
      return;
    }
    if (f === 'x-part') {
      var i = parseInt(el.getAttribute('data-ed-idx'), 10), p = parseInt(el.getAttribute('data-ed-part'), 10);
      if (!slide.content) slide.content = [];
      var parts = DSTE.splitItem(slide.content[i] || '');
      if (p === 0) parts.h = txt; else if (p === 1) parts.d = txt; else parts.x = txt;
      slide.content[i] = DSTE.joinItem(parts);
    }
  });
}, true);

/* ---------- request routing ---------- */
function isJsonBody(opts){ return opts && typeof opts.body === 'string' && opts.body.charAt(0) === '{'; }
function withBody(opts, b){ var no = {}; for (var k in opts) no[k] = opts[k]; no.body = JSON.stringify(b); return no; }
function buildPages(d, choices){
  var T = tplFor(d.tpl, d.tplTheme || '');
  return d.slides.map(function(s, i){
    var p = DSTE.renderPage(T, s, choices['slide_'+i] || 'v1', i, d);
    return {bg: p.bg, els: p.els.map(function(e){ var c = {}; for (var k in e) { if (k.charAt(0) !== '_' && k !== 'ed' && k !== 'fam') c[k] = e[k]; } return c; })};
  });
}
/* Add Slide: snippet 17 posts with the fetch it saved at load time, so tag the request through the hidden design field */
function wrapAddSlide(){
  var f = window.dspAddSlideGenerate;
  if (typeof f !== 'function' || f.__dste) return;
  var w = function(){
    var id = activeId(), el = document.getElementById('dsp-design'), old = el ? el.value : null;
    if (id && el) el.value = 'tpl:' + id;
    try { return f.apply(this, arguments); } finally { if (el && old !== null) el.value = old; }
  };
  w.__dste = 1; window.dspAddSlideGenerate = w;
}
function installFetch(){
  if (window.__dsteFetch) return; window.__dsteFetch = 1;
  var prev = window.fetch;
  window.fetch = function(url, opts){
    var us = typeof url === 'string' ? url : (url && url.url) || '';
    try {
      if (/\/ds\/v1\/(ai-preview|ai-confirm|generate-ppt)(\?|$)/.test(us) && isJsonBody(opts)) {
        var b = JSON.parse(opts.body);
        var isAdd = !!b.append_slide || b.slides === 1 || b.num_slides === 1;
        if (b.deduct_only) {                       // "Preserve My Text" deck: tag it with the picked template
          var pk = pickedTpl();
          if (pk && window.G && window.G.pptData && !window.G.pptData.tpl) { window.G.pptData.tpl = pk; window.G.pptData.tplTheme = DSTE.homeTheme; if (window.S && window.S.data && window.S.data.slides) { window.S.data.tpl = pk; window.S.data.tplTheme = DSTE.homeTheme; } }
        } else {
          var tid = isAdd ? activeId() : (b.ppt_data && b.ppt_data.tpl && DSTE.reg[b.ppt_data.tpl] ? b.ppt_data.tpl : pickedTpl());
          if (tid) {
            b.tpl = tid;
            if (!isAdd) { b.tpl_theme = DSTE.homeTheme; if (b.ppt_data && typeof b.ppt_data === 'object' && b.ppt_data.tplTheme == null) b.ppt_data.tplTheme = DSTE.homeTheme; }
            opts = withBody(opts, b);
          }
        }
      }
      if (/\/ds\/v1\/build-pptx(\?|$)/.test(us) && isJsonBody(opts)) {
        var id = activeId(), d = deck();
        if (id && d && d.slides) {
          var b2 = JSON.parse(opts.body);
          b2.tpl = id; b2.layout = '16:9';
          b2.tpl_pages = buildPages(d, (window.S && window.S.choices) || {});
          if (d.logo && typeof d.logo.src === 'string' && d.logo.src) b2.tpl_logo = {src: d.logo.src}; /* company logo */
          var TT = tplFor(id, d.tplTheme || ''); if (TT && TT.__rec) b2.tpl_fonts = {head: TT.fonts.head, body: TT.fonts.body};
          opts = withBody(opts, b2);
        }
      }
    } catch(err) { if (window.console) console.warn('[DSTE] route', err); }
    return prev.apply(this, [url, opts]);
  };
}

/* ---------- live "generating" overlay for template decks ---------- */
function wrapLiveGen(){
  if (window.__dsteLive || typeof window.dspLiveGenStream !== 'function') return;
  window.__dsteLive = 1;
  var orig = window.dspLiveGenStream;
  window.dspLiveGenStream = function(ppt, design, onDone){
    var T = ppt && ppt.tpl && tplFor(ppt.tpl, ppt.tplTheme || '');
    if (!T) return orig.apply(this, arguments);
    DSTE.loadFonts(T);
    var slides = ppt.slides || [], total = slides.length;
    var thumbs = document.getElementById('dsp-lg-thumbs'), main = document.getElementById('dsp-lg-slide-content'), sh = document.getElementById('dsp-lg-shimmer');
    var lgMain = document.getElementById('dsp-lg-main'); if (lgMain) lgMain.style.background = T.pal.bg;
    if (thumbs) thumbs.innerHTML = '';
    slides.forEach(function(s, i){
      var box = document.createElement('div');
      box.style.cssText = 'position:relative;width:100%;aspect-ratio:16/9;border-radius:3px;overflow:hidden;opacity:.3;transition:opacity .4s;border:2px solid transparent;background:' + T.pal.bg + ';flex-shrink:0;';
      if (thumbs) thumbs.appendChild(box);
      setTimeout(function(){
        var html = DSTE.renderHTML(T, s, 'v1', -1, ppt, {edit:false});
        box.innerHTML = html; box.style.opacity = '1'; box.style.borderColor = '#8ab4f8';
        if (main) main.innerHTML = html;
        if (sh) { sh.style.transition = 'opacity .4s'; sh.style.opacity = '0'; }
        try { box.scrollIntoView({block:'nearest'}); } catch(e){}
        if (i === total - 1) setTimeout(function(){ if (sh) sh.style.display = 'none'; var bt = document.getElementById('dsp-lg-banner-txt'); if (bt) bt.innerHTML = '<b style="color:#111;">Done!</b> Opening your presentation editor...'; setTimeout(function(){ if (onDone) onDone(); }, 700); }, 400);
      }, i * 250);
    });
    if (!total && onDone) onDone();
  };
}

/* ============================================================
   HOME: template picker
   ============================================================ */
var SAMPLE = {
  cover:{type:'title',title:'Quarterly Growth Strategy',subtitle:'How we double revenue in 2026 through focused execution and smarter partnerships',content:['Company: Northwind Group','Presenter: Alex Morgan','Designation: Chief Strategy Officer','Date: March 2026','Website: www.northwind.com']},
  p3:{type:'three_column',title:'Our Three Growth Pillars',subtitle:'Where we will invest time, talent and capital over the next four quarters',content:['Product Expansion: Launch two enterprise modules that lift average contract value by 28% within the first year.','Channel Partners: Sign 40 regional resellers across India and the Gulf to cut acquisition cost by 35%.','Customer Success: A dedicated onboarding squad reduces churn from 11% to under 6% by Q4.'],icons:['mdi:cube-outline','mdi:handshake-outline','mdi:account-heart'],conclusion:'Focused investment in three pillars compounds growth faster than scattered bets.'},
  stats:{type:'stats',title:'Results That Matter',subtitle:'Twelve months of measurable progress across the business',content:['94%: Customer retention rate | Up from 81% after the new success program','3.2x: Pipeline growth | Driven by partner-sourced enterprise deals','$4.8M: Annual recurring revenue | Crossed our target two months early'],conclusion:'Momentum is real and repeatable when the whole team runs one plan.'},
  timeline:{type:'timeline',title:'Execution Roadmap',subtitle:'Five milestones that take us from pilot to market leader',content:['Q1 2026: Pilot with 12 enterprise design partners and refine pricing.','Q2 2026: Launch partner portal and onboard the first 15 resellers.','Q3 2026: Release analytics module and expand into the Gulf region.','Q4 2026: Reach $8M ARR with churn below 6%.'],conclusion:'Each milestone de-risks the next, so capital is released stage by stage.'}
};
DSTE.SAMPLE = SAMPLE;

var CSS = ''
+ '#dste-sec{background:#fff;border-radius:16px;padding:20px 22px 22px;margin:14px auto;width:100%;max-width:680px;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif;}'
+ '#dste-sec .dste-hd{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px;}'
+ '#dste-sec h4{margin:0 0 3px;font-size:16px;font-weight:700;color:#111827;}'
+ '#dste-sec .dste-sub{margin:0;font-size:12.5px;color:#6b7280;}'
+ '#dste-sec .dste-more{flex-shrink:0;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;}'
+ '#dste-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}'
+ '@media(max-width:560px){#dste-grid{grid-template-columns:repeat(2,1fr);}}'
+ '.dste-card{border:2px solid #e5e7eb;border-radius:12px;overflow:hidden;cursor:pointer;background:#fff;transition:border-color .15s,box-shadow .15s;text-align:left;padding:0;text-transform:none!important;letter-spacing:normal!important;font-weight:400;line-height:normal;}'
+ '#dsp-th-grid .dste-th-hide{display:none!important;}'
+ '.dste-card:hover{border-color:#93c5fd;}'
+ '.dste-card.dste-on{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15);}'
+ '.dste-th{position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;background:#1B2A4A;}'
+ '.dste-nm{padding:7px 10px 8px;font-size:12px;font-weight:700;color:#111827;display:flex;justify-content:space-between;align-items:center;gap:6px;}'
+ '.dste-nm small{display:block;font-size:10.5px;font-weight:500;color:#6b7280;margin-top:1px;}'
+ '.dste-ck{width:18px;height:18px;border-radius:50%;background:#2563eb;color:#fff;font-size:11px;display:none;align-items:center;justify-content:center;flex-shrink:0;}'
+ '.dste-on .dste-ck{display:flex;}'
+ '#dste-strip{display:none;margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0;}'
+ '#dste-strip.on{display:block;}'
+ '#dste-strip .dste-sl{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}'
+ '#dste-strip .dste-sl>div{position:relative;aspect-ratio:16/9;border-radius:6px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.15);}'
+ '#dste-strip p{margin:0 0 8px;font-size:12px;color:#374151;}'
+ '#dste-sec .dste-hide{display:none!important;}'
+ '#dste-sec .dste-note{margin-top:10px;font-size:11.5px;color:#6b7280;}'
+ '.dste-foot{display:flex;justify-content:center;margin-top:16px;}'
+ '.dste-viewall{background:#f3f4f6!important;border:1px solid #e5e7eb!important;border-radius:999px!important;padding:8px 22px!important;font-size:12px!important;font-weight:700!important;color:#374151!important;letter-spacing:.06em!important;text-transform:uppercase!important;line-height:1.2!important;cursor:pointer;box-shadow:none!important;}'
+ '.dste-viewall:hover{background:#e5e7eb!important;}';

function classicThumb(){
  return '<div style="position:absolute;inset:0;background:#1B2A4A;container-type:size;">'
    + '<div style="position:absolute;left:8%;top:30%;width:84%;font:800 7.5cqw/1.05 Arial,sans-serif;color:#fff;">Quarterly Growth Strategy</div>'
    + '<div style="position:absolute;left:8%;top:62%;width:14%;height:1.2%;background:#4A9EFF;"></div>'
    + '<div style="position:absolute;left:8%;top:68%;width:70%;font:400 3cqw/1.3 Arial,sans-serif;color:#C8D8F0;">Classic layouts with theme colours</div></div>';
}
var _built = false, _showAll = false;
function buildHome(){
  var home = document.getElementById('dsp-home'), theme = document.getElementById('dsp-theme-section');
  if (!home || !DSTE.order.length) return false;
  var sec = document.getElementById('dste-sec');
  if (!sec) {
    if (!document.getElementById('dste-css')) { var st = document.createElement('style'); st.id = 'dste-css'; st.textContent = CSS; document.head.appendChild(st); }
    sec = document.createElement('div'); sec.id = 'dste-sec';
    sec.innerHTML = '<div class="dste-hd"><div><h4>Design Template</h4><p class="dste-sub">Every template has its own layouts, colours and typography.</p></div></div><div id="dste-grid"></div><div id="dste-strip"></div><div class="dste-note" id="dste-note"></div><div class="dste-foot"><button type="button" class="dste-viewall" id="dste-more">View All</button></div>';
    if (theme && theme.parentNode) theme.parentNode.insertBefore(sec, theme);
    else { var gb = document.getElementById('dsp-costbar'); if (gb && gb.parentNode) gb.parentNode.insertBefore(sec, gb); else home.appendChild(sec); }
    sec.querySelector('#dste-more').onclick = function(){ _showAll = !_showAll; renderGrid(); };
  }
  renderGrid();
  _built = true;
  return true;
}
function renderGrid(){
  var g = document.getElementById('dste-grid'); if (!g) return;
  var ids = ['classic'].concat(DSTE.order);
  var cur = DSTE.pick || 'classic';
  var shown = _showAll ? ids : ids.slice(0, 6);
  if (!_showAll && shown.indexOf(cur) < 0) shown[5] = cur;
  var html = '';
  shown.forEach(function(id){
    var T = id === 'classic' ? null : tplFor(id, DSTE.homeTheme), on = id === cur;
    if (T) DSTE.loadFonts(T);
    var th = id === 'classic' ? classicThumb() : DSTE.renderHTML(T, SAMPLE.cover, 'v1', -1, {slides:[SAMPLE.cover]}, {edit:false});
    var nm = id === 'classic' ? 'Classic' : T.name, tg = id === 'classic' ? 'Original layouts + themes' : (T.tag || '');
    html += '<button type="button" class="dste-card' + (on ? ' dste-on' : '') + '" data-id="' + id + '"><div class="dste-th">' + th + '</div><div class="dste-nm"><div>' + u.esc(nm) + '<small>' + u.esc(tg) + '</small></div><span class="dste-ck">✓</span></div></button>';
  });
  g.innerHTML = html;
  Array.prototype.forEach.call(g.querySelectorAll('.dste-card'), function(b){ b.onclick = function(){ setPick(b.getAttribute('data-id')); }; });
  var more = document.getElementById('dste-more'); if (more) more.textContent = _showAll ? 'Show Less' : 'View All';
  renderStrip();
}
function renderStrip(){
  var s = document.getElementById('dste-strip'); if (!s) return;
  var id = pickedTpl();
  if (!id) { s.className = ''; s.innerHTML = ''; return; }
  var T = tplFor(id, DSTE.homeTheme), d = {slides:[SAMPLE.cover, SAMPLE.p3, SAMPLE.stats, SAMPLE.timeline]};
  var h = '<p><b>' + u.esc(T.name) + '</b> — ' + u.esc(T.desc || T.tag || '') + '</p><div class="dste-sl">';
  d.slides.forEach(function(sl, i){ h += '<div>' + DSTE.renderHTML(T, sl, 'v1', -1, d, {edit:false}).replace('class="dste-slide"', 'class="dste-slide" data-n="' + i + '"') + '</div>'; });
  s.innerHTML = h + '</div>'; s.className = 'on';
}
function syncHomeUI(){
  var id = pickedTpl();
  if (id) DSTE.loadFonts(tplFor(id, DSTE.homeTheme));
  var theme = document.getElementById('dsp-theme-section');
  if (theme) theme.style.display = '';
  hookThemeGrids();
  var lay = document.getElementById('dsp-layout');
  if (id && lay) { lay.value = '16:9'; var ll = document.getElementById('dsp-pill-layout-lbl'); if (ll) ll.textContent = '16:9'; }
  var pl = document.getElementById('dsp-pill-layout'); if (pl) { pl.style.opacity = id ? '.45' : ''; pl.style.pointerEvents = id ? 'none' : ''; pl.title = id ? 'Design templates are 16:9' : ''; }
  var note = document.getElementById('dste-note');
  if (note) note.textContent = id ? 'Pick a theme below to recolour this template (colours + heading font), or keep "Template colours". Slides are 16:9.' : '';
  renderGrid();
}
var _homeT = null;
function scheduleHomeUI(){ clearTimeout(_homeT); _homeT = setTimeout(function(){ if (buildHome()) syncHomeUI(); }, 60); }

/* ---------- Theme section: "Template colours" card + theme picks for template decks ---------- */
function tplThemeCard(T, sel){
  var P = T.pal || {};
  return '<div class="dsp-th-card dste-thc' + (sel ? ' dsp-th-sel' : '') + '" data-dste-th="1" title="Keep this template\'s own colours and fonts">'
    + '<div class="dsp-th-swatch" style="background:' + P.bg + ';"><div class="t" style="color:' + (P.title || P.text) + ';font-family:\'' + T.fonts.head + '\',sans-serif;">Title</div>'
    + '<div class="b" style="color:' + P.accent + ';font-family:\'' + T.fonts.body + '\',sans-serif;">' + u.esc(T.name) + '</div></div>'
    + '<div class="dsp-th-name">Template colours<span class="ck">✓</span></div></div>';
}
function gridCtx(g){
  if (g.id === 'dsp-th-grid') { var p = pickedTpl(); return p ? {id:p, key:DSTE.homeTheme} : null; }
  var a = activeId(); return a ? {id:a, key:deckTheme()} : null;
}
function decorateGrid(g){
  if (!g) return;
  var cx = gridCtx(g), mine = g.querySelector('[data-dste-th]');
  if (!cx) {
    if (mine) {
      mine.parentNode.removeChild(mine);
      Array.prototype.forEach.call(g.querySelectorAll('.dste-th-hide'), function(c){ c.classList.remove('dste-th-hide'); });
      var dz = document.getElementById('dsp-design'), cur = dz ? dz.value : '';
      Array.prototype.forEach.call(g.querySelectorAll('[data-th-key]'), function(c){ c.classList.toggle('dsp-th-sel', c.getAttribute('data-th-key') === cur); });
    }
    return;
  }
  var T = DSTE.reg[cx.id];
  if (!mine || mine.getAttribute('data-tpl') !== cx.id) {
    if (mine) mine.parentNode.removeChild(mine);
    var tmp = document.createElement('div'); tmp.innerHTML = tplThemeCard(T, false);
    mine = tmp.firstChild; mine.setAttribute('data-tpl', cx.id);
    g.insertBefore(mine, g.firstChild);
  }
  mine.classList.toggle('dsp-th-sel', !cx.key);
  if (cx.key && g.id === 'dsp-th-grid') {                         /* after a reload keep the cost bar / hidden field on the remembered theme */
    var dz2 = document.getElementById('dsp-design'), kc = g.querySelector('[data-th-key="' + cx.key + '"]');
    if (dz2 && dz2.value !== cx.key && kc && typeof window.dspPillSet === 'function') window.dspPillSet('design', cx.key, kc.getAttribute('data-th-label') || cx.key);
  }
  var keyed = g.querySelectorAll('[data-th-key]');                /* keep the collapsed home grid at 2 full rows */
  Array.prototype.forEach.call(keyed, function(c, i){ c.classList.toggle('dste-th-hide', g.id === 'dsp-th-grid' && keyed.length === 8 && i === 7 && c.getAttribute('data-th-key') !== cx.key); });
  Array.prototype.forEach.call(g.querySelectorAll('[data-th-key]'), function(c){ c.classList.toggle('dsp-th-sel', !!cx.key && c.getAttribute('data-th-key') === cx.key); });
}
/* Theme section: its "View more" button moves to the bottom and reads "View All", like the template section */
function placeThemeMore(){
  var btn = document.getElementById('dsp-th-more-btn'), sec = document.getElementById('dsp-theme-section');
  if (!btn || !sec) return;
  var foot = document.getElementById('dste-th-foot');
  if (!foot) { foot = document.createElement('div'); foot.id = 'dste-th-foot'; foot.className = 'dste-foot'; }
  if (foot.parentNode !== sec || sec.lastElementChild !== foot) sec.appendChild(foot);
  if (btn.parentNode !== foot) foot.appendChild(btn);
  btn.classList.add('dste-viewall');
  var g = document.getElementById('dsp-th-grid'), open = g && g.querySelectorAll('[data-th-key]').length > 8;
  var txt = open ? 'Show Less' : 'View All';
  if (btn.textContent !== txt) btn.textContent = txt;
}
document.addEventListener('click', function(e){ if (e.target && e.target.id === 'dsp-th-more-btn') setTimeout(placeThemeMore, 0); });
function hookThemeGrids(){
  if (!document.getElementById('dste-css')) { var st0 = document.createElement('style'); st0.id = 'dste-css'; st0.textContent = CSS; document.head.appendChild(st0); }
  placeThemeMore();
  ['dsp-th-grid', 'dsp-ed-drawer-row'].forEach(function(id){
    var g = document.getElementById(id); if (!g) return;
    if (!g.__dsteObs) { g.__dsteObs = 1; new MutationObserver(function(){ decorateGrid(g); }).observe(g, {childList:true}); }
    decorateGrid(g);
  });
}
function afterDeckTheme(){
  DSTE.clearFit();
  DSTE.loadFonts(curT());
  refreshAll();
  hookThemeGrids();
}
document.addEventListener('click', function(e){
  var t = e.target; if (!t || !t.closest) return;
  var card = t.closest('[data-dste-th],[data-th-key]'); if (!card) return;
  var key = card.hasAttribute('data-dste-th') ? '' : (card.getAttribute('data-th-key') || '');
  if (card.closest('#dsp-ed-drawer-row') && activeId()) {
    setDeckTheme(key);
    if (!key) { var cl = document.getElementById('dsp-ed-drawer-close'); if (cl) cl.click(); }
    setTimeout(afterDeckTheme, 30);
  } else if (card.closest('#dsp-th-grid')) {
    /* also before any template is picked: the template previews follow the theme straight away, and the template picked afterwards keeps it */
    setHomeTheme(key);
    setTimeout(function(){ var pt = pickedTpl(); if (pt) DSTE.loadFonts(tplFor(pt, DSTE.homeTheme)); renderGrid(); hookThemeGrids(); }, 30);
  }
}, true);

/* ---------- boot ---------- */
function boot(){
  installFetch();
  wrapLiveGen();
  scheduleHomeUI();
  var tries = 0, t = setInterval(function(){
    tries++;
    wrapLiveGen(); wrapAddSlide();
    if (!document.getElementById('dste-sec')) scheduleHomeUI();
    if (tries > 40) clearInterval(t);
  }, 500);
  var ai = activeId(); if (ai) DSTE.loadFonts(curT());
}
if (document.readyState === 'complete') setTimeout(boot, 0); else window.addEventListener('load', function(){ setTimeout(boot, 0); });

/* a template can mark a layout as the default for a slide kind (e.g. Chrome Noir's cover with a round image):
   when the open slide has no layout picked yet, pick that one through Snippet 17 so its image loads from the marketplace */
function applyDefaultLayout(){
  var id = activeId(); if (!id || !window.S || !window.S.data || !window.S.data.slides || !window.S.choices || typeof window.dspVPPick !== 'function') return;
  var i = window.S.idx; if (typeof i !== 'number' || i < 0) return;
  var sl = window.S.data.slides[i]; if (!sl || window.S.choices['slide_' + i]) return;
  var k = DSTE.kindOf(sl); if (k === 'section') k = 'text';
  var def = DSTE.variantsFor(DSTE.reg[id], k).filter(function(v){ return v.o && v.o.def; })[0];
  if (!def || def.id === 'v1') return;
  var t = window.dspToast; window.dspToast = function(){};
  try { window.dspVPPick(i, def.id); } catch(err){} finally { window.dspToast = t; }
}
/* load fonts whenever a template deck opens (generate / load draft) */
var _lastActive = null;
setInterval(function(){ wrapAddSlide(); hookThemeGrids(); applyDefaultLayout(); var a = activeId(); if (a && a + '|' + deckTheme() !== _lastActive) { _lastActive = a + '|' + deckTheme(); DSTE.loadFonts(curT()); } }, 800);
})();



}catch(e){if(window.console)console.error(e);}
try{

/* Template 01 — Glassline : frosted blue glass, charcoal display type (Startup sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, u = D.u, V = D.V;
var LIGHT = {bg:'#DCE3EA', text:'#2E3236', title:'#2E3236', muted:'#59626B', accent:'#3F6FB5', accent2:'#8FB6EC', card:'#FFFFFF', line:'#AEBCCB', onAccent:'#FFFFFF'};
var DARK  = {bg:'#3A3D41', text:'#F3F5F7', title:'#FFFFFF', muted:'#C4CAD1', accent:'#9CC2F4', accent2:'#6E95D6', card:'#FFFFFF', line:'#6A7078', onAccent:'#1E2A3A'};
var S = {
  margin:70,
  header:{kind:'left', tMax:52, tMin:28, sMax:18},
  card:{kind:'glass', r:18, op:0.42, lop:0.8, hl:{kind:'grad', grad:{ang:135, stops:[[0,'#A9CCF7'],[100,'#EEF5FE']]}, dark:false, tc:'#1F2A36', mc:'#3F4B57'}},
  icon:{kind:'plain', fg:'#2E3236'},
  num:{kind:'ring', bg:'#2E3236', fg:'#2E3236'},
  conc:{kind:'pill', fill:'#FFFFFF', op:0.5, line:'#FFFFFF', lw:1, label:'Takeaway', lc:'#3F6FB5'},
  footer:{pagePrefix:'PAGE '},
  imgFramed:true, imgR:22
};
var SD = D.merge(S, {card:{kind:'glass', op:0.08, lop:0.3, line:'#FFFFFF', hl:{kind:'grad', grad:{ang:135, stops:[[0,'#9CC2F4'],[100,'#DCEAFD']]}, dark:false, tc:'#1E2A3A', mc:'#34465A'}}, icon:{kind:'plain', fg:'#FFFFFF'}, num:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF'}, conc:{fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lc:'#9CC2F4', tc:'#F3F5F7'}});
function isDark(C, kind){ return kind==='stats' || kind==='timeline' || kind==='quote' || (kind==='p4' && C.i % 2 === 1); }

function bg(C, kind){
  var dark = isDark(C, kind) && C.vid !== 'n9';
  if (dark) { C.P = DARK; C.S = SD; } else { C.P = LIGHT; C.S = S; }
  var P = C.P;
  C.bg({fill:P.bg});
  if (dark) {
    D.glow(C, 1120, 120, 420, 300, '#6F93CF', 0.55);
    D.glow(C, 200, 700, 380, 260, '#C49A6C', 0.25);
    D.glow(C, 640, 360, 520, 260, '#4E5C70', 0.35);
  } else {
    D.glow(C, 1150, 60, 460, 330, '#9EC1F0', 0.95);
    D.glow(C, 80, 640, 420, 300, '#B7CFEF', 0.85);
    D.glow(C, 760, 520, 300, 200, '#E8D9F3', 0.55);
    C.shape('parallelogram', 760, -40, 620, 820, {fill:'#FFFFFF', op:0.16});
  }
  D.hexOutline(C, 1180, 640, 70, {color:'#FFFFFF', op:dark ? 0.12 : 0.55});
  D.hexOutline(C, 90, 110, 44, {color:'#FFFFFF', op:dark ? 0.1 : 0.45});
}
function brand(C, x, y, color){
  var name = C.cover.company || 'Company';
  D.hexOutline(C, x + 11, y + 11, 11, {color:color, op:1, lw:2.2});
  C.ell(x + 8, y + 8, 6, 6, {fill:color});
  C.text(x + 30, y, 360, 22, name, {font:'head', size:17, color:color, va:'m', lh:1, ed:C.i === 0 ? {f:'tf-company'} : null});
}

function cover(C, v){
  var P = C.P, f = C.cover, M = 70, o = C.o || {};
  brand(C, M, 40, P.text);
  C.rect(M, 84, W - 2*M, 1.2, {fill:P.text, op:0.55});
  if (f.presenter) {
    C.text(W - M - 360, 42, 360, 16, 'Presented by :', {font:'body', size:11.5, wt:700, color:P.text, align:'r', lh:1});
    C.text(W - M - 360, 60, 360, 18, [f.presenter, f.designation].filter(Boolean).join(', '), {font:'body', size:12.5, color:P.muted, align:'r', lh:1, ed:{f:'tf-presenter'}});
  }
  if (o.img === 'right') {
    D.imgBox(C, 700, 120, W - 700 - M, 520, {r:26});
    C.rect(M - 10, 330, 700, 300, {fill:'#FFFFFF', op:0.35, line:'#FFFFFF', lop:0.8, lw:1.2, r:22});
    C.text(M + 20, 350, 640, 170, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:3, va:'b', lh:1.0});
    if (C.s.subtitle) C.text(M + 20, 536, 620, 76, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.4, maxLines:3});
    return;
  }
  var te = C.text(M - 4, 170, 900, 420, C.s.title, {font:'head', fit:{max:124, min:48}, color:P.title, ed:{f:'title'}, maxLines:3, va:'b', lh:0.98, ls:-0.02});
  if (C.s.subtitle) C.text(W - M - 280, 470, 280, 110, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:P.text, ed:{f:'subtitle'}, va:'b', lh:1.35, maxLines:5});
  var meta = [f.date, f.website].filter(Boolean).join('   •   ');
  if (meta) C.text(M, 640, 700, 20, meta, {font:'body', size:13, wt:600, color:P.muted, lh:1});
  C.text(W - M - 120, 684, 120, 16, 'PAGE 01', {font:'body', size:11, wt:700, color:P.muted, align:'r', lh:1});
}

function closing(C, v){
  var P = C.P, M = 70;
  brand(C, M, 40, P.text);
  C.rect(M, 84, W - 2*M, 1.2, {fill:P.text, op:0.55});
  C.text(M - 4, 180, 720, 420, C.s.title || 'Thank You', {font:'head', fit:{max:150, min:56}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:0.95, ls:-0.02});
  if (C.s.subtitle) C.text(M, 610, 640, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(840, 300, 380, 300, {fill:'#FFFFFF', op:0.4, line:'#FFFFFF', lop:0.85, lw:1.2, r:22});
  D.contactList(C, 870, 330, 330, {gap:66, markO:{kind:'circle', bg:'#2E3236', fg:'#FFFFFF'}});
  if (C.s.conclusion) C.text(870, 530, 330, 56, C.s.conclusion, {font:'body', fit:{max:14, min:11}, color:P.text, ed:{f:'x-conc'}, lh:1.35, maxLines:3});
}

function agenda(C, v){
  var P = C.P;
  brand(C, 70, 40, P.text);
  D.agendaSplit(C, {split:0.45, titleY:250, tMax:80, numC:P.text, itemWt:500, listY:60, rowMax:58});
}

function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:C.P.accent}); return; }
  C.rect(150, 110, W - 300, H - 220, {fill:'#FFFFFF', op:0.08, line:'#FFFFFF', lop:0.3, lw:1.2, r:28});
  D.B.quote(C, {x:210, y:150, w:W - 420, h:H - 300}, {qc:C.P.accent});
}

D.register({
  id:'glassline', name:'Glassline', tag:'Frosted glass · startup', desc:'Soft blue glass panels, charcoal display type and alternating dark insight slides.',
  fonts:{head:'Outfit', body:'Inter', headW:700, bodyW:400, headLh:1.02, headLs:-0.01, gf:'Outfit:wght@400;600;700;800|Inter:wght@400;500;600;700'},
  pal:LIGHT, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Big type',{}), V('v2','Glass card + image',{img:'right'})],
    agenda:[V('v1','Split list',{})],
    text:D.defaultVariants.text,
    p2:[V('v1','Glass panels',{body:'duo', hl:true}), V('n2','Two panels + VS',{body:'duo', vs:true}), V('v2','Image right + cards',{body:'list', img:'right', cards:true}), V('v3','Image left + list',{body:'list', img:'left'})],
    p3:[V('v1','Glass cards',{body:'cols', hl:'first'}), V('n2','Icon list',{body:'list', rules:true}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + cards',{body:'list', img:'left', cards:true}), V('v3','Cards + image right',{body:'list', img:'right', cards:true}), V('v4','Cards + image bottom',{body:'cols', img:'bottom'})],
    p4:[V('v1','Offset glass grid',{body:'grid', hl:'first', mark:'num'}), V('n2','Four columns',{body:'cols', hl:'alt'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + list',{body:'list', img:'left', cards:true}), V('v3','List + image right',{body:'list', img:'right'})],
    p56:[V('v1','Glass grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1, cards:true})],
    p78:[V('v1','Glass grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('v2','List + image right',{body:'list', img:'right', cols:2})],
    stats:[V('v1','Big numbers',{body:'statCols', vc:'#9CC2F4'}), V('n2','Glass stat cards',{body:'statCards', icons:true, hl:'first'}), V('n3','Progress rings',{body:'statRings', track:'#555A60'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Year pills',{body:'tlCards'}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Glass quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 02 — Cobalt Bold : electric cobalt, heavy condensed caps (Pitch Deck sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var LIGHT = {bg:'#F3F5FB', text:'#1A12B8', title:'#1A12B8', muted:'#3B3F8C', accent:'#1A12B8', accent2:'#5A52F0', card:'#FFFFFF', line:'#C9CCEB', onAccent:'#FFFFFF'};
var BLUE  = {bg:'#1A12B8', text:'#FFFFFF', title:'#FFFFFF', muted:'#D5D3FF', accent:'#FFFFFF', accent2:'#A9A4FF', card:'#2A22D0', line:'#5750E0', onAccent:'#1A12B8'};
var S = {
  margin:64,
  header:{kind:'split', tMax:46, tMin:26, sMax:17, top:52},
  card:{kind:'outline', r:0, line:'#1A12B8', lw:1.5, hl:{kind:'solid', fill:'#1A12B8', dark:true, tc:'#FFFFFF', mc:'#D5D3FF'}},
  icon:{kind:'square', r:0, bg:'#1A12B8', fg:'#FFFFFF'},
  num:{kind:'big', fg:'#1A12B8'},
  mark:'icon',
  conc:{kind:'line', label:'(Key takeaway)', lc:'#1A12B8', lineC:'#1A12B8', wt:600},
  footer:{fn:foot},
  imgFramed:false, imgPct:0.42
};
var SB = D.merge(S, {card:{kind:'outline', line:'#FFFFFF', lw:1.5, fill:null, hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:'#1A12B8', mc:'#3B3F8C'}}, icon:{bg:'#FFFFFF', fg:'#1A12B8'}, num:{fg:'#FFFFFF'}, conc:{lc:'#FFFFFF', lineC:'#FFFFFF', tc:'#FFFFFF'}});
function foot(C){
  var P = C.P, f = C.cover, y = H - 38;
  var who = f.presenter ? 'PRESENTED BY ' + f.presenter : (f.company || '');
  if (who) C.text(64, y, 520, 16, who, {font:'body', size:11, wt:800, color:P.text, caps:true, ls:0.06, lh:1});
  C.text(W - 64 - 140, y, 140, 16, 'PAGE ' + (C.i + 1), {font:'body', size:11, wt:800, color:P.text, align:'r', lh:1, caps:true});
}
function blue(C, kind){ return kind === 'stats' || kind === 'quote' || (kind === 'p2' && C.vid === 'v1'); }
function bg(C, kind){
  if (blue(C, kind)) { C.P = BLUE; C.S = SB; } else { C.P = LIGHT; C.S = S; }
  C.bg({fill:C.P.bg});
  if (!blue(C, kind)) C.rect(W - 64 - 90, 0, 90, 10, {fill:'#1A12B8'});
}
function cover(C, v){
  C.P = BLUE; C.bg({fill:'#1A12B8'});
  var P = C.P, f = C.cover, o = C.o || {};
  var tw = o.img ? 640 : W - 128;
  if (o.img) D.imgBox(C, 700, 0, W - 700, H, {});
  C.text(40, 40, tw + 24, 430, C.s.title, {font:'head', fit:{max:170, min:56}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:0.86, ls:-0.03, va:'t'});
  var sx = o.img ? 44 : W - 64 - 440, sw = o.img ? 600 : 440;
  if (f.company) C.text(sx, 488, sw, 30, '(' + f.company + ')', {font:'head', size:20, color:'#FFFFFF', ed:{f:'tf-company'}, lh:1});
  if (C.s.subtitle) C.text(sx, 528, sw, 90, C.s.subtitle, {font:'body', fit:{max:15, min:11}, wt:800, color:'#FFFFFF', caps:true, ed:{f:'subtitle'}, lh:1.25, maxLines:4});
  var who = [f.presenter ? 'PRESENTED BY\n' + f.presenter : '', f.website].filter(Boolean);
  if (who[0]) C.text(44, 640, 260, 40, who[0], {font:'body', size:11, wt:800, color:'#FFFFFF', caps:true, lh:1.2});
  if (f.website) C.text(330, 652, 300, 20, f.website, {font:'body', size:11, wt:800, color:'#FFFFFF', caps:true, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var P = LIGHT; C.P = P;
  C.bg({fill:P.bg});
  D.imgBox(C, 0, 250, 300, H - 250, {});
  if (C.s.subtitle) C.text(330, 410, W - 400, 50, C.s.subtitle, {font:'body', fit:{max:18, min:12}, wt:600, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.3});
  C.text(330, 90, W - 380, 300, C.s.title || "Let's collaborate", {font:'head', fit:{max:150, min:60}, color:P.title, ed:{f:'title'}, maxLines:2, lh:0.86, ls:-0.03, va:'b'});
  var cs = D.contacts(C), y = 470;
  cs.forEach(function(c){
    C.text(W - 64 - 420, y, 420, 18, (c.label || '').toUpperCase(), {font:'head', size:15, color:P.text, lh:1});
    C.text(W - 64 - 420, y + 22, 420, 24, c.val, {font:'head', fit:{max:18, min:11}, color:P.text, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1, lh:1});
    y += 64;
  });
  if (C.cover.presenter) C.text(330, 560, 360, 40, 'PRESENTED BY\n' + C.cover.presenter, {font:'body', size:13, color:P.text, lh:1.2, caps:true, wt:600});
}
function agenda(C, v){
  D.agendaSplit(C, {split:0.42, titleY:70, tMax:96, numC:'#1A12B8', itemFont:'head', itemWt:400, iMax:22, rowMax:60, ruleC:'#1A12B8', titleVa:'t'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#FFFFFF', qFont:'head', qMax:40}); return; }
  C.text(64, 70, W - 128, 460, C.s.title, {font:'head', fit:{max:66, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:5, lh:1.0, va:'t'});
  var by = C.it[0] ? (C.it[0].h ? C.it[0].h + ': ' + C.it[0].d : C.it[0].d) : '';
  C.rect(64, 580, 120, 6, {fill:'#FFFFFF'});
  if (by) C.text(64, 604, 900, 30, by, {font:'body', fit:{max:18, min:12}, wt:800, color:'#FFFFFF', caps:true, ed:C.it[0] ? {f:'content-plain', i:C.it[0].i} : null, maxLines:1});
}
D.register({
  id:'cobalt', name:'Cobalt Bold', tag:'Electric blue · pitch', desc:'Heavy uppercase headlines, sharp outlined boxes and full-bleed cobalt statement slides.',
  fonts:{head:'Archivo Black', body:'Archivo', headW:400, bodyW:500, headLh:0.95, headCaps:true, headLs:-0.01, gf:'Archivo Black|Archivo:wght@400;500;600;700;800'},
  pal:LIGHT, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Giant headline',{}), V('v2','Headline + image right',{img:'right'})],
    agenda:[V('v1','Split list',{}), V('n2','Numbered tiles',{body:'agTiles'})],
    p2:[V('v1','Blue panels',{body:'duo', bigNum:true, hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image left + columns',{body:'list', img:'left'}), V('v3','Columns + image right',{body:'duo', img:'right'})],
    p3:[V('v1','Outlined tiers',{body:'cols', hl:'alt', mark:'num'}), V('n2','Numbered columns',{body:'numbered'}), V('n3','Big-number list',{body:'list', mark:'num', rules:true}), V('v2','Image left + columns',{body:'cols', img:'left', mark:'num'}), V('v3','Image right + list',{body:'list', img:'right'}), V('v4','Image bottom + columns',{body:'numbered', img:'bottom'})],
    p4:[V('v1','Box grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', hl:'first', mark:'num'}), V('n3','Numbered row',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left'}), V('v3','List + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Box grid',{body:'grid', hl:'first', mark:'num'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Box grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, rules:true, mark:'num'})],
    stats:[V('v1','Giant numbers',{body:'statCols', vc:'#FFFFFF', vMax:110}), V('n2','Outlined stat boxes',{body:'statCards', hl:'first'}), V('n3','Progress bars',{body:'statBars'}), V('v2','Image left + numbers',{body:'statBars', img:'left'})],
    timeline:[V('v1','Milestone line',{body:'tlH'}), V('n2','Milestone boxes',{body:'tlCards', hlLast:true}), V('n3','Arrow steps',{body:'tlChev'}), V('n4','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Statement',{}), V('v2','Statement + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 03 — Chrome Noir : black, silver tiles, chrome ribbons (Digital Marketing sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#0B0B0D', text:'#F2F2F4', title:'#FFFFFF', muted:'#A6A8AE', accent:'#5FD3E0', accent2:'#8FE9F2', card:'#26272B', line:'#3A3B40', onAccent:'#0B0B0D'};
var S = {
  margin:64,
  header:{kind:'left', tMax:48, tMin:26, sMax:17, top:48, kicker:function(C){ return C.cover.company || ''; }, kPill:'#C9CBD1', kc:'#E4E5E8', kSize:11, kGap:46},
  card:{kind:'solid', r:10, fill:'#232428', line:'#34353A', lw:1, hl:{kind:'solid', fill:'#F2F2F4', dark:false, tc:'#0B0B0D', mc:'#3A3B40'}},
  icon:{kind:'square', r:12, bg:'#3A3B41', fg:'#F2F2F4'},
  num:{kind:'pill', bg:'#3A3B41', fg:'#F2F2F4'},
  conc:{kind:'box', fill:'#1B1C20', line:'#3A3B40', lw:1, r:10, lc:'#5FD3E0', label:'Key takeaway'},
  footer:{brand:false, pagePrefix:''},
  imgFramed:true, imgR:12
};
/* clean concentric rings (true circles) with an optional round image in the middle */
function ringSet(C, cx, cy, o){
  o = o || {};
  C.ell(cx - 330, cy - 330, 660, 660, {fill:'#0B0B0D'});
  D.ring(C, cx, cy, 258, {color:'#EDEDF0', lw:3});
  D.ring(C, cx, cy, 316, {color:'#EDEDF0', lw:3});
  D.ring(C, cx, cy, 410, {color:'#F2F2F4', lw:8});
  if (o.img) D.imgBox(C, cx - 208, cy - 208, 416, 416, {shape:'ell'});
  else {
    C.ell(cx - 208, cy - 208, 416, 416, {grad:{type:'radial', stops:[[0,'#3A3B41'],[100,'#141417']]}});
    D.ring(C, cx, cy, 208, {color:'#5A5C62', lw:1.5, op:0.8});
  }
  var sa = (o.sparkA != null ? o.sparkA : -78) * Math.PI / 180;
  D.sparkle(C, cx + 316 * Math.cos(sa), cy + 316 * Math.sin(sa), 28, '#FFFFFF', 1);
}
/* small corner rings (content slides) */
function cornerRings(C, cx, cy, a0, a1, spark){
  C.ell(cx - 62, cy - 62, 124, 124, {fill:'#3A3B40'});
  [92, 116, 140].forEach(function(r, k){ D.arc(C, cx, cy, r, a0, a1, {color:'#CFD1D6', lw:k === 2 ? 2.2 : 1.6, op:0.85}); });
  if (spark) D.sparkle(C, spark[0], spark[1], 14, '#FFFFFF', 0.9);
}
function bg(C, kind){
  C.bg({grad:{type:'radial', stops:[[0,'#2A2B31'],[70,'#121215'],[100,'#08080A']]}});
  D.glow(C, 1180, 40, 300, 220, '#5FD3E0', 0.08);
  if (kind !== 'cover' && kind !== 'closing' && kind !== 'quote') cornerRings(C, W, 0, 90, 180, [W - 150, 152]);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  var right = o.side === 'r';
  if (right) ringSet(C, W - 130, 370, {img:!!o.img, sparkA:-102});
  else ringSet(C, 130, 370, {img:!!o.img});
  var x = right ? 64 : 610, w = right ? 600 : W - x - 60;
  if (f.company) { C.rect(x, 64, Math.min(260, f.company.length * 9 + 40), 28, {line:'#C9CBD1', lw:1.2, r:14}); C.text(x, 64, Math.min(260, f.company.length * 9 + 40), 28, f.company, {font:'body', size:11, wt:700, color:'#E4E5E8', align:'c', va:'m', lh:1, ed:{f:'tf-company'}}); }
  C.text(x, 130, w, 330, C.s.title, {font:'head', fit:{max:72, min:34}, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.02, ls:0.02});
  if (C.s.subtitle) C.text(x, 480, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var lines = [f.website, f.presenter ? f.presenter + (f.designation ? ', ' + f.designation : '') : '', f.date].filter(Boolean);
  lines.forEach(function(l, k){ C.text(x, 590 + k * 24, w, 20, '•  ' + l, {font:'body', size:13, color:'#D0D2D6', lh:1}); });
}
/* closing: rings (with a round image) anchored in one corner, text on the open side */
var CORNERS = {
  tr:{cx:W - 130, cy:130, sa:200, tx:64},
  tl:{cx:130, cy:130, sa:-20, tx:600},
  br:{cx:W - 130, cy:H - 130, sa:160, tx:64},
  bl:{cx:130, cy:H - 130, sa:20, tx:600},
  r:{cx:W - 130, cy:360, sa:-102, tx:64}
};
function closing(C, v){
  var P = C.P, o = C.o || {}, K = CORNERS[o.corner];
  var tx = 64, tw = 640, dy = o.corner === 'br' || o.corner === 'bl' ? -130 : 0;   /* rings at the bottom → lift the text so the slide balances */
  if (K) { ringSet(C, K.cx, K.cy, {img:!!o.img, sparkA:K.sa}); tx = K.tx; tw = K.tx > 64 ? W - K.tx - 64 : 640; }
  else ringSet(C, W - 130, 360, {sparkA:-102});
  C.text(tx, 170 + dy, tw, 300, C.s.title || 'Thank You', {font:'head', fit:{max:110, min:48}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1.0, ls:0.02});
  if (C.s.subtitle) C.text(tx, 490 + dy, Math.min(600, tw), 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactRow(C, tx, 580 + dy, K && K.tx > 64 ? tw : 690, {cards:true});
}
function agenda(C, v){
  var P = C.P, M = 64;
  C.text(M, 56, 800, 70, C.s.title || 'Table of Content', {font:'head', fit:{max:50, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, ls:0.04});
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols), cw = cols === 2 ? 560 : 900, rh = Math.min(84, (H - 260) / per), top = 150 + ((H - 260) - rh * per) / 2;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = M + c * (cw + 30), y = top + r * rh;
    C.rect(x, y, cw, rh - 12, {fill:'#2A2B30', r:6});
    C.text(x + 18, y, 60, rh - 12, D.u.pad2(k + 1), {font:'head', size:28, color:'#FFFFFF', va:'m', lh:1, ls:0});
    C.text(x + 80, y, cw - 96, rh - 12, t.h || t.d, {font:'body', fit:{max:21, min:11}, wt:700, color:'#F2F2F4', va:'m', lh:1.1, ls:0.03, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
  if (C.s.subtitle) C.text(M, H - 76, 900, 30, C.s.subtitle, {font:'body', fit:{max:15, min:11}, color:P.muted, ed:{f:'subtitle'}, maxLines:1});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#5FD3E0'}); return; }
  cornerRings(C, 0, H, 270, 360, [150, H - 152]);
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:'#C9CBD1'});
}
D.register({
  id:'chrome', name:'Chrome Noir', tag:'Black & silver · agency', desc:'Deep black canvas, graphite tiles, clean chrome rings and sparkle highlights.',
  fonts:{head:'Space Grotesk', body:'DM Sans', headW:700, bodyW:400, headLh:1.05, headLs:0.02, gf:'Space Grotesk:wght@500;600;700|DM Sans:wght@400;500;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v2','Rings + round image',{img:'ring', def:true}), V('v3','Image right + rings',{img:'ring', side:'r'}), V('v1','Chrome rings',{})],
    closing:[V('v2','Rings top right + image',{img:'ring', corner:'tr', def:true}), V('v3','Rings top left + image',{img:'ring', corner:'tl'}), V('v4','Rings bottom right + image',{img:'ring', corner:'br'}), V('v5','Rings bottom left + image',{img:'ring', corner:'bl'}), V('v6','Rings right + image',{img:'ring', corner:'r'})],
    agenda:[V('v1','Content tiles',{})],
    p2:[V('v1','Graphite panels',{body:'duo'}), V('n2','Panels + VS',{body:'duo', vs:true}), V('v2','Image left + tiles',{body:'list', img:'left', cards:true}), V('v3','Tiles + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Service tiles',{body:'list', cards:true}), V('n2','Three columns',{body:'cols'}), V('n3','Numbered columns',{body:'numbered', lineC:'#3A3B40'}), V('v2','Image left + tiles',{body:'list', img:'left', cards:true}), V('v3','Tiles + image right',{body:'list', img:'right', cards:true})],
    p4:[V('v1','Graphite grid',{body:'grid'}), V('n2','Four columns',{body:'cols', hl:'first'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + tiles',{body:'list', img:'left', cards:true}), V('v3','Tiles + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Graphite grid',{body:'grid'}), V('n2','Two-column tiles',{body:'list', cols:2, cards:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Graphite grid',{body:'grid'}), V('n2','Two-column tiles',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Ring tiles',{body:'statRings', cardBg:true, track:'#3A3B40'}), V('n2','Stat tiles',{body:'statCards', icons:true}), V('n3','Big numbers',{body:'statCols'}), V('n4','Progress bars',{body:'statBars'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Milestone tiles',{body:'tlCards'}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Chrome quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 04 — Aqua Wave : white, cyan line waves, navy type (AI Business Solutions sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#FFFFFF', text:'#16325C', title:'#16325C', muted:'#4A5D78', accent:'#1FB5EC', accent2:'#16325C', card:'#EFF9FE', line:'#CFE8F5', onAccent:'#16325C'};
var S = {
  margin:64,
  header:{kind:'left', tMax:44, tMin:26, sMax:17, top:52},
  card:{kind:'solid', r:18, fill:'#F1FAFE', line:'#D6EEF9', lw:1, hl:{kind:'solid', fill:'#16325C', dark:true, tc:'#FFFFFF', mc:'#CFE3F5'}},
  icon:{kind:'circle', bg:'#5BD0F6', fg:'#16325C'},
  num:{kind:'circle', bg:'#5BD0F6', fg:'#16325C', font:'body'},
  conc:{kind:'box', fill:'#EAF7FD', r:14, lc:'#1592C4', label:'Key message'},
  footer:{pagePrefix:'PAGE '},
  imgFramed:true, imgR:24, imgPh:{grad:{ang:135, stops:[[0,'#BDEBFB'],[100,'#5BA7D9']]}}
};
function waves(C, x0, y0, x1, y1, n, amp, op){ D.waveBundle(C, {x0:x0, y0:y0, x1:x1, y1:y1, n:n || 16, amp:amp || 80, spread:5, color:'#34C1F2', lw:1.1, op:op || 0.55, fade:true}); }
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  var alt = C.i % 3;
  C.ell(W - 260, -220, 520, 520, {fill:'#D8F3FD', op:0.85});
  if (alt === 0) waves(C, 700, 720, 1300, 380, 18, 70, 0.5);
  else if (alt === 1) waves(C, -20, 760, 520, 560, 14, 60, 0.45);
  else waves(C, 900, -20, 1300, 260, 14, 50, 0.45);
  C.ell(-120, 600, 300, 300, {fill:'#E4F7FE', op:0.9});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.bg({grad:{ang:160, stops:[[0,'#F3FBFF'],[100,'#DDF3FC']]}});
  D.waveBundle(C, {x0:-40, y0:560, x1:1320, y1:300, n:26, amp:110, spread:6, color:'#34C1F2', lw:1.1, op:0.6, fade:true});
  C.ell(760, -240, 700, 700, {fill:'#BEEBFB', op:0.6});
  if (o.img) D.imgBox(C, 700, 60, 520, 440, {r:28});
  if (f.company) { C.rect(64, 330, 14, 14, {fill:'#16325C', r:3}); C.text(88, 326, 400, 22, f.company, {font:'head', size:16, color:'#16325C', va:'m', lh:1, ed:{f:'tf-company'}}); }
  C.text(64, 360, o.img ? 600 : 900, 180, C.s.title, {font:'head', fit:{max:62, min:30}, color:'#16325C', ed:{f:'title'}, maxLines:3, lh:1.12});
  if (C.s.subtitle) C.text(64, 560, 720, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var info = [f.presenter, f.designation, f.date].filter(Boolean).join('  |  ');
  if (info) C.text(64, 640, 800, 20, info, {font:'body', size:13, wt:600, color:'#1592C4', lh:1});
  if (f.website && !o.img) { C.text(W - 64 - 360, 48, 360, 18, 'Contact Information', {font:'head', size:13, color:'#16325C', align:'r', lh:1}); C.text(W - 64 - 360, 70, 360, 18, f.website, {font:'body', size:13, color:P.muted, align:'r', lh:1, ed:{f:'tf-website'}}); }
}
function closing(C, v){
  var P = C.P;
  C.bg({grad:{ang:180, stops:[[0,'#EEF6FA'],[100,'#DDEEF6']]}});
  D.waveBundle(C, {x0:-40, y0:700, x1:1320, y1:520, n:22, amp:90, spread:6, color:'#34C1F2', lw:1.1, op:0.55, fade:true});
  if (C.cover.company) C.text(64, 70, 400, 20, C.cover.company, {font:'head', size:15, color:'#16325C', lh:1});
  C.text(64, 150, 640, 120, C.s.title || 'Thank You!', {font:'head', fit:{max:76, min:36}, color:'#16325C', ed:{f:'title'}, maxLines:2, lh:1.05});
  if (C.s.subtitle) C.text(64, 290, 620, 60, C.s.subtitle, {font:'head', fit:{max:20, min:13}, color:'#1592C4', ed:{f:'subtitle'}, maxLines:2, lh:1.35});
  C.text(820, 150, 360, 20, 'Contact Information', {font:'head', size:15, color:'#16325C', lh:1});
  D.contactList(C, 820, 190, 400, {gap:58});
  if (C.s.conclusion) C.text(64, 380, 620, 60, C.s.conclusion, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'x-conc'}, lh:1.4, maxLines:2});
}
function agenda(C, v){
  var P = C.P;
  D.agendaSplit(C, {split:0.4, titleY:180, tMax:60, itemWt:600, rowMax:62, numC:'#1592C4', ruleC:'#CFE8F5'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#1FB5EC'}); return; }
  C.rect(140, 120, W - 280, H - 240, {fill:'#F1FAFE', r:28, line:'#D6EEF9', lw:1});
  D.B.quote(C, {x:200, y:150, w:W - 400, h:H - 300}, {qc:'#1FB5EC'});
}
D.register({
  id:'aqua', name:'Aqua Wave', tag:'Clean white · tech', desc:'Crisp white slides with flowing cyan line-waves, navy headlines and numbered bubbles.',
  fonts:{head:'Poppins', body:'Poppins', headW:700, bodyW:400, headLh:1.12, gf:'Poppins:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Wave cover',{}), V('v2','Wave cover + image',{img:'right'})],
    agenda:[V('v1','Split list',{}), V('n2','Numbered tiles',{body:'agTiles'})],
    p2:[V('v1','Two panels',{body:'duo'}), V('n2','Numbered bubbles',{body:'list', mark:'num'}), V('v2','Image left + bubbles',{body:'list', img:'left', mark:'num'}), V('v3','Bubbles + image right',{body:'list', img:'right', mark:'num'})],
    p3:[V('v1','Numbered bubbles',{body:'list', mark:'num'}), V('n2','Soft cards',{body:'cols', hl:'first'}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + bubbles',{body:'list', img:'left', mark:'num'}), V('v3','Bubbles + image right',{body:'list', img:'right', mark:'num'}), V('v4','Image cards row',{body:'cols', img:'top'})],
    p4:[V('v1','Bubble grid',{body:'grid', mark:'num', cards:false}), V('n2','Soft card grid',{body:'grid'}), V('n3','Four columns',{body:'cols', hl:'first'}), V('n4','Hub diagram',{body:'hub'}), V('v2','Image left + grid',{body:'list', img:'left', mark:'num'}), V('v3','Grid + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Soft card grid',{body:'grid'}), V('n2','Two-column bubbles',{body:'list', cols:2, mark:'num'}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1, mark:'num'})],
    p78:[V('v1','Soft card grid',{body:'grid'}), V('n2','Two-column bubbles',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Stat cards',{body:'statCards', icons:true, hl:'first'}), V('n2','Big numbers',{body:'statCols'}), V('n3','Progress rings',{body:'statRings', track:'#E1F4FC'}), V('n4','Progress bars',{body:'statBars'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Before/after steps',{body:'tlH'}), V('n2','Milestone cards',{body:'tlCards'}), V('n3','Arrow steps',{body:'tlChev'}), V('n4','Vertical timeline',{body:'tlV'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Soft quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 05 — Paper Proposal : warm paper, steel-blue gradients, bold caps (Business Proposal sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#F3F0EB', text:'#1D4F7C', title:'#1D4F7C', muted:'#3E5C78', accent:'#2F74AE', accent2:'#8FB9DC', card:'#C3D8E8', line:'#C9D3DC', onAccent:'#FFFFFF'};
var S = {
  margin:70,
  header:{kind:'left', tMax:46, tMin:26, sMax:16, top:56},
  card:{kind:'solid', r:8, fill:'#C6DAEA', dark:false, tc:'#1D4F7C', mc:'#2F4E6B', hl:{kind:'solid', fill:'#2F74AE', dark:true, tc:'#FFFFFF', mc:'#E2EEF8'}},
  icon:{kind:'softsq', r:8, bg:'#2F74AE', fg:'#2F74AE'},
  num:{kind:'big', fg:'#1D4F7C'},
  mark:'num',
  conc:{kind:'band', fill:'#1D4F7C', r:6, label:'Next step', tc:'#FFFFFF', lc:'#9CC6EA'},
  footer:{pagePrefix:'PAGE ', brand:true},
  imgFramed:true, imgR:10, imgPh:{grad:{ang:135, stops:[[0,'#9CC3E4'],[100,'#2F74AE']]}}
};
function corners(C, a, b){
  D.glow(C, a[0], a[1], 460, 360, '#4E90C8', 0.75);
  D.glow(C, b[0], b[1], 380, 300, '#7FB3DD', 0.6);
  D.glow(C, a[0] - 80, a[1] + 40, 260, 200, '#9FE0E4', 0.35);
}
function bg(C, kind){
  C.bg({fill:'#F3F0EB'});
  var k = C.i % 3;
  if (k === 0) corners(C, [1280, 0], [0, 720]);
  else if (k === 1) corners(C, [0, 0], [1280, 720]);
  else corners(C, [1280, 720], [120, -60]);
}
function logo(C, x, y, col){
  C.path([['M',x,y+8],['L',x+8,y+16],['L',x+22,y+2]], {stroke:col, lw:3});
  C.path([['M',x+10,y+8],['L',x+18,y+16],['L',x+32,y+2]], {stroke:col, lw:3});
  if (C.cover.company) C.text(x + 44, y - 2, 360, 22, C.cover.company, {font:'head', size:15, color:col, va:'m', lh:1, caps:true, ls:0.04, ed:C.i === 0 ? {f:'tf-company'} : null});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  corners(C, [1280, 360], [1100, 720]);
  logo(C, 70, 60, '#1D4F7C');
  var words = String(C.s.title || '').split(/\s+/), first = words.length > 2 ? words.slice(0, Math.ceil(words.length / 2)).join(' ') : words[0] || '', rest = words.length > 2 ? words.slice(Math.ceil(words.length / 2)).join(' ') : words.slice(1).join(' ');
  var tw = o.img ? 640 : 900;
  if (o.img) D.imgBox(C, 760, 80, 450, 560, {r:12});
  var a = C.text(64, 200, tw, 110, first, {font:'head', fit:{max:84, min:36}, wt:400, color:'#1D4F7C', maxLines:1, lh:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (rest) C.text(64, 300, tw, 200, rest, {font:'head', fit:{max:104, min:40}, color:'#1D4F7C', maxLines:2, lh:1, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(70, 520, o.img ? 620 : 760, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  if (f.website) C.text(70, 632, 600, 22, f.website, {font:'head', size:17, color:'#1D4F7C', caps:true, lh:1, ed:{f:'tf-website'}});
}
function closing(C, v){
  var P = C.P;
  corners(C, [1280, 360], [1100, 720]);
  logo(C, 70, 60, '#1D4F7C');
  var words = String(C.s.title || 'Thank You Very Much').split(/\s+/), cut = Math.max(1, Math.ceil(words.length / 2));
  C.text(64, 190, 900, 110, words.slice(0, cut).join(' '), {font:'head', fit:{max:92, min:40}, wt:400, color:'#1D4F7C', maxLines:1, lh:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (words.length > cut) C.text(64, 300, 900, 120, words.slice(cut).join(' '), {font:'head', fit:{max:104, min:40}, color:'#1D4F7C', maxLines:1, lh:1, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(70, 440, 700, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  D.contactRow(C, 70, 560, 900, {lc:'#2F74AE'});
}
function agenda(C, v){
  var P = C.P, M = 70;
  C.text(M, 70, 900, 70, C.s.title || 'Agenda', {font:'head', fit:{max:52, min:28}, color:'#1D4F7C', ed:{f:'title'}, maxLines:1});
  if (C.s.subtitle) C.text(M, 140, 900, 30, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:1});
  D.B.agTiles(C, {x:M, y:200, w:W - 2*M, h:430}, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#2F74AE'}); return; }
  C.rect(90, 90, W - 180, H - 180, {fill:'#C6DAEA', op:0.85, r:6});
  D.B.quote(C, {x:170, y:130, w:W - 340, h:H - 260}, {qc:'#1D4F7C', qFont:'head'});
}
D.register({
  id:'paper', name:'Paper Proposal', tag:'Warm paper · proposal', desc:'Soft paper texture tones, steel-blue gradient corners and confident uppercase headlines.',
  fonts:{head:'Montserrat', body:'Open Sans', headW:800, bodyW:400, headLh:1.02, headCaps:true, gf:'Montserrat:wght@400;600;700;800|Open Sans:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Two-weight title',{}), V('v2','Title + image',{img:'right'})],
    agenda:[V('v1','Numbered tiles',{})],
    p2:[V('v1','Blue panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image right + list',{body:'list', img:'right', mark:'num'}), V('v3','Image left + list',{body:'list', img:'left', mark:'num'})],
    p3:[V('v1','Stacked tiles',{body:'list', cards:true, mark:'num'}), V('n2','Blue columns',{body:'cols', hl:'alt'}), V('n3','Numbered columns',{body:'numbered', dot:true}), V('v2','Image right + list',{body:'list', img:'right'}), V('v3','Image left + tiles',{body:'list', img:'left', cards:true}), V('v4','Columns + image bottom',{body:'numbered', img:'bottom'})],
    p4:[V('v1','Tile grid',{body:'grid', hl:'alt'}), V('n2','Value timeline',{body:'numbered', dot:true}), V('n3','Four columns',{body:'cols'}), V('v2','Image right + list',{body:'list', img:'right'}), V('v3','Image left + tiles',{body:'list', img:'left', cards:true})],
    p56:[V('v1','Tile grid',{body:'grid', hl:'first'}), V('n2','Numbered grid',{body:'numbered', dot:true}), V('n3','Two-column list',{body:'list', cols:2, rules:true}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Tile grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, rules:true})],
    stats:[V('v1','Stacked bars',{body:'statBars'}), V('n2','Big numbers',{body:'statCols'}), V('n3','Blue stat tiles',{body:'statCards', hl:'alt'}), V('n4','Progress rings',{body:'statRings', track:'#D5E2EC'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Dot timeline',{body:'tlH'}), V('n2','Milestone tiles',{body:'tlCards'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Framed quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 06 — Noir Gold : jet black, gold + violet accents, orbit lines & sparkles (Artificial Intelligence sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#050505', text:'#FFFFFF', title:'#FFFFFF', muted:'#B9B9BD', accent:'#F5C542', accent2:'#A63CF2', card:'#121214', line:'#2E2E33', onAccent:'#111111'};
var S = {
  margin:64,
  header:{kind:'left', tMax:50, tMin:26, sMax:16, top:96},
  card:{kind:'solid', r:14, fill:'#141416', line:'#2C2C31', lw:1, hl:{kind:'solid', fill:'#F5C542', dark:false, tc:'#111111', mc:'#3A3320'}},
  icon:{kind:'circle', bg:'#A63CF2', fg:'#FFFFFF'},
  num:{kind:'circle', bg:'#F5C542', fg:'#111111'},
  conc:{kind:'pill', fill:'#F5C542', op:1, label:'Key insight', lc:'#111111', tc:'#111111', chip:false},
  footer:{fn:foot},
  imgFramed:true, imgR:16, imgPh:{grad:{ang:135, stops:[[0,'#2A1A45'],[100,'#6B4A12']]}}
};
function foot(C){
  var P = C.P;
  if (C.cover.website) C.text(64, H - 40, 400, 16, C.cover.website, {font:'body', size:11.5, color:'#9A9AA0', lh:1});
  C.ell(W - 64 - 22, H - 46, 22, 22, {fill:'#F5C542'});
  C.text(W - 64 - 22, H - 46, 22, 22, '→', {font:'body', size:13, wt:700, color:'#111111', align:'c', va:'m', lh:1});
}
function nav(C){
  var name = C.cover.company || 'Company';
  C.text(64, 34, 220, 22, name, {font:'head', size:15, wt:600, color:'#FFFFFF', caps:true, lh:1, va:'m', ls:0.04});
  C.text(W - 480, 34, 80, 22, 'About', {font:'body', size:12, color:'#8E8E94', va:'m', lh:1});
  C.text(W - 400, 34, 70, 22, 'Home', {font:'body', size:12, wt:700, color:'#FFFFFF', va:'m', lh:1});
  C.text(W - 330, 34, 80, 22, 'Contact', {font:'body', size:12, color:'#8E8E94', va:'m', lh:1});
  C.rect(W - 230, 30, 90, 28, {fill:'#A63CF2', r:14});
  C.text(W - 230, 30, 90, 28, 'Join Us', {font:'body', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
  C.icon(W - 124, 34, 20, 'mdi:magnify', '#FFFFFF');
  C.icon(W - 92, 34, 20, 'mdi:menu', '#FFFFFF');
}
function orbit(C, k){
  if (k % 2 === 0) { D.arc(C, 1180, -40, 300, 95, 200, {color:'#FFFFFF', lw:1.4, op:0.7}); D.arc(C, 200, 800, 260, 250, 340, {color:'#FFFFFF', lw:1.4, op:0.55}); }
  else { D.arc(C, 90, -60, 280, 20, 120, {color:'#FFFFFF', lw:1.4, op:0.7}); D.arc(C, 1150, 820, 240, 190, 290, {color:'#FFFFFF', lw:1.4, op:0.55}); }
  D.sparkle(C, 1060 - (k % 3) * 40, 150 + (k % 2) * 380, 16, '#F5C542', 1);
  D.sparkle(C, 640 + (k % 4) * 30, 690, 14, '#F5C542', 1);
  D.sparkle(C, 40 + (k % 2) * 1180, 380, 12, '#A63CF2', 1);
}
function bg(C, kind){
  C.bg({fill:'#050505'});
  orbit(C, C.i);
  nav(C);
}
function twoTone(C, x, y, w, h, title, max, al){
  var words = String(title || '').split(/\s+/), cut = Math.max(1, Math.floor(words.length / 2));
  var a = words.slice(0, cut).join(' '), b = words.slice(cut).join(' ');
  var r = C.measureText(title, w, {font:'head', max:max, min:30, h:h, maxLines:3});
  C.text(x, y, w, h / 2, a, {font:'head', size:r.s, color:'#FFFFFF', lh:1.05, maxLines:2, align:al || 'l', va:'b', ed:{f:'x-tpart', p:0}});
  C.text(x, y + h / 2 + 4, w, h / 2, b, {font:'head', size:r.s, color:'#F5C542', lh:1.05, maxLines:2, ed:{f:'x-tpart', p:1}, align:al || 'l'});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, 780, 110, 420, 520, {r:200});
  C.text(40, 90, o.img ? 720 : 1200, 390, C.s.title, {font:'head', fit:{max:132, min:44}, wt:400, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:0.95, va:'b'});
  C.rect(40, 520, 30, 3, {fill:'#F5C542'});
  if (C.s.subtitle) C.text(84, 506, 640, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#D8D8DC', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(64, 600, 260, 36, {fill:'#F5C542', r:18});
  C.text(64, 600, 220, 36, 'START PRESENTATION', {font:'body', size:13, wt:700, color:'#111111', align:'c', va:'m', lh:1});
  C.ell(292, 602, 32, 32, {fill:'#A63CF2'});
  C.text(292, 602, 32, 32, '→', {font:'body', size:16, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
}
function closing(C, v){
  var P = C.P;
  C.text(64, 150, W - 128, 190, C.s.title || 'Thank You', {font:'head', fit:{max:140, min:50}, wt:400, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, align:'c', lh:1, va:'m'});
  if (C.s.subtitle) C.text(200, 350, W - 400, 60, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:'#D8D8DC', ed:{f:'subtitle'}, align:'c', lh:1.45, maxLines:2});
  D.contactRow(C, 140, 470, W - 280, {cards:true, lc:'#F5C542'});
}
function agenda(C, v){
  var P = C.P, M = 64, it = C.it.slice(0, 8), n = it.length;
  twoTone(C, M, 100, 520, 200, C.s.title || 'Agenda Overview', 60);
  if (C.s.subtitle) C.text(M, 320, 480, 100, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var x = 640, w = W - 64 - x, rh = Math.min(64, 520 / Math.max(1, n)), y0 = 110 + (520 - rh * n) / 2;
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    D.num(C, x, y + (rh - 40) / 2, 40, k + 1, {});
    C.text(x + 58, y, w - 60, rh, t.h || t.d, {font:'head', fit:{max:20, min:12}, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#F5C542'}); return; }
  D.B.quote(C, {x:180, y:140, w:W - 360, h:H - 280}, {qc:'#F5C542', qFont:'head'});
}
D.register({
  id:'noirgold', name:'Noir Gold', tag:'Black & gold · tech', desc:'Jet-black canvas with gold and violet highlights, orbit lines, sparkles and a web-style header.',
  fonts:{head:'Jost', body:'Jost', headW:500, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Jost:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Giant headline',{}), V('v2','Headline + arch image',{img:'right'})],
    agenda:[V('v1','Gold numbered list',{})],
    p2:[V('v1','Two panels',{body:'duo', hl:true}), V('n2','Numbered list',{body:'list', mark:'num'}), V('v2','Images right + list',{body:'list', img:'right', mark:'num'}), V('v3','Image left + list',{body:'list', img:'left', mark:'num'})],
    p3:[V('v1','Icon columns',{body:'cols', center:true, pad:22}), V('n2','Gold card + list',{body:'cols', hl:'first'}), V('n3','Numbered list',{body:'list', mark:'num', rules:true}), V('v2','Image right + list',{body:'list', img:'right'}), V('v3','Image left + cards',{body:'list', img:'left', cards:true}), V('v4','Columns + image bottom',{body:'cols', img:'bottom', center:true})],
    p4:[V('v1','Dark grid',{body:'grid', hl:'first'}), V('n2','Four icon columns',{body:'cols', center:true}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + list',{body:'list', img:'left'}), V('v3','List + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Dark grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Dark grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Gold numbers',{body:'statCols'}), V('n2','Stat cards',{body:'statCards', icons:true, hl:'first'}), V('n3','Progress rings',{body:'statRings', track:'#2A2A2F'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Orbit line',{body:'tlH'}), V('n2','Milestone cards',{body:'tlCards', hlLast:true}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Gold quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 07 — Violet Horizon : deep indigo gradients, glowing violet panels, flowing lines (AI sample 1) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#1B1060', text:'#FFFFFF', title:'#FFFFFF', muted:'#C9C3F2', accent:'#8E7CFF', accent2:'#D07BFF', card:'#3A2BB0', line:'#4B3CB8', onAccent:'#FFFFFF'};
var S = {
  margin:66,
  header:{kind:'left', tMax:48, tMin:26, sMax:17, top:60},
  card:{kind:'grad', r:6, grad:{ang:120, stops:[[0,'#5C45E6',0.85],[100,'#3A2BB0',0.55]]}, line:'#8E7CFF', lw:1, lop:0.6, dark:true, tc:'#FFFFFF', mc:'#DCD7FF', hl:{kind:'grad', grad:{ang:120, stops:[[0,'#8E7CFF'],[100,'#C07BFF']]}, dark:true, tc:'#FFFFFF', mc:'#F4EEFF'}},
  icon:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF', lw:1.6},
  num:{kind:'square', bg:'#8E7CFF', fg:'#FFFFFF', r:4},
  conc:{kind:'box', fill:'#2C1F8F', line:'#8E7CFF', lw:1, r:6, lc:'#C9B8FF', label:'Key takeaway'},
  footer:{pagePrefix:'PAGE '},
  imgFramed:true, imgR:14, imgPh:{grad:{ang:135, stops:[[0,'#3B2DC2'],[100,'#C04FE8']]}}
};
function logo(C, x, y, s){
  C.rect(x, y, s * 0.46, s * 0.46, {fill:'#A79BFF', r:s * 0.12});
  C.rect(x + s * 0.54, y + s * 0.2, s * 0.46, s * 0.46, {fill:'#A79BFF', op:0.75, r:s * 0.12});
  C.rect(x + s * 0.2, y + s * 0.54, s * 0.46, s * 0.46, {fill:'#A79BFF', op:0.55, r:s * 0.12});
}
function flow(C, y0, y1, op){ D.waveBundle(C, {x0:-20, y0:y0, x1:1300, y1:y1, n:22, amp:70, spread:4, color:'#9C7CFF', lw:1, op:op || 0.45, fade:true, phase:C.i}); }
function bg(C, kind){
  C.bg({grad:{ang:135, stops:[[0,'#120A4A'],[55,'#23158A'],[100,'#3A1FA8']]}});
  D.glow(C, 1150, 120, 380, 300, '#6A4CFF', 0.45);
  D.glow(C, 160, 690, 420, 240, '#C04FE8', 0.25);
  flow(C, 640, 520, 0.35);
  logo(C, W - 110, 40, 46);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  D.glow(C, 300, 360, 380, 380, '#3F7BFF', 0.55);
  if (o.img) D.imgBox(C, 64, 70, 480, 580, {r:22});
  else {
    for (var r = 90; r <= 300; r += 30) C.ell(300 - r, 360 - r, r * 2, r * 2, {line:'#7FB6FF', lw:1.2, lop:0.18 + (300 - r) / 900});
    C.icon(190, 250, 220, 'mdi:brain', '#9CC8FF');
  }
  C.rect(560, 150, 660, 330, {grad:{ang:90, stops:[[0,'#5C45E6',0.55],[100,'#5C45E6',0]]}});
  if (f.company) { logo(C, 1040, 60, 26); C.text(1074, 60, 180, 26, f.company, {font:'body', size:13, color:'#FFFFFF', va:'m', lh:1, ed:{f:'tf-company'}}); }
  C.text(590, 170, 610, 230, C.s.title, {font:'head', fit:{max:68, min:32}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.05, va:'m'});
  if (C.s.subtitle) C.text(592, 400, 600, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, italic:true, color:'#DCD7FF', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var meta = [f.presenter, f.date, f.website].filter(Boolean).join('   •   ');
  if (meta) C.text(590, 610, 620, 20, meta, {font:'body', size:13, color:'#DCD7FF', lh:1, align:'r'});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  D.imgBox(C, 830, 0, 450, H, {});
  C.text(90, 120, 640, 250, C.s.title || 'Thank You for Your Attention', {font:'head', fit:{max:66, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.08, va:'b'});
  if (C.s.subtitle) C.text(92, 390, 600, 50, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#DCD7FF', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(90, 470, 640, 180, {grad:{ang:120, stops:[[0,'#5C45E6',0.85],[100,'#3A2BB0',0.55]]}, line:'#8E7CFF', lw:1, lop:0.6, r:4});
  D.contactList(C, 120, 494, 580, {gap:50, markO:{kind:'plain', fg:'#FFFFFF'}});
}
function agenda(C, v){
  D.agendaSplit(C, {split:0.4, titleY:220, tMax:66, numC:'#C9B8FF', itemWt:600, rowMax:60, ruleC:'#4B3CB8'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#C9B8FF'}); return; }
  C.rect(160, 130, W - 320, H - 260, {grad:{ang:120, stops:[[0,'#5C45E6',0.6],[100,'#3A2BB0',0.35]]}, line:'#8E7CFF', lw:1, lop:0.5, r:6});
  D.B.quote(C, {x:220, y:160, w:W - 440, h:H - 320}, {qc:'#C9B8FF'});
}
D.register({
  id:'violet', name:'Violet Horizon', tag:'Indigo glow · AI', desc:'Deep indigo gradients, translucent violet panels, glowing orbits and flowing light lines.',
  fonts:{head:'Plus Jakarta Sans', body:'Plus Jakarta Sans', headW:800, bodyW:400, headLh:1.06, gf:'Plus Jakarta Sans:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Glowing orbit',{}), V('v2','Image panel',{img:'left'})],
    agenda:[V('v1','Split list',{}), V('n2','Numbered tiles',{body:'agTiles'})],
    p2:[V('v1','Glow panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Glow cards',{body:'cols', hl:'first'}), V('n2','Icon list',{body:'list', rules:true}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true}), V('v4','Image bottom + cards',{body:'cols', img:'bottom'})],
    p4:[V('v1','Glow grid',{body:'grid'}), V('n2','Four columns',{body:'cols', center:true}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + grid',{body:'grid', img:'left', cols:2}), V('v3','Grid + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Glow grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, rules:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Glow grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, rules:true})],
    stats:[V('v1','Glow stat panels',{body:'statCards', icons:true, hl:'first'}), V('n2','Big numbers',{body:'statCols', vc:'#C9B8FF'}), V('n3','Progress rings',{body:'statRings', track:'#3A2BB0'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Glow milestones',{body:'tlCards', hlLast:true}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Glow panel quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 08 — Blueprint : royal blue, HUD frames, airy tracked type (AI sample 2) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#0E2E74', text:'#FFFFFF', title:'#FFFFFF', muted:'#BFD3F5', accent:'#7CC6FF', accent2:'#3A8DF0', card:'#173C8F', line:'#3F67B8', onAccent:'#0E2E74'};
var S = {
  margin:70,
  header:{kind:'center', tMax:46, tMin:26, sMax:17, top:70},
  card:{kind:'outline', r:16, fill:'#1A4299', op:0.55, line:'#7CC6FF', lw:1.4, dark:true, tc:'#FFFFFF', mc:'#CFE0FA', hl:{kind:'solid', fill:'#DCE9FF', dark:false, tc:'#0E2E74', mc:'#284B8E'}},
  icon:{kind:'square', r:6, bg:'#FFFFFF', fg:'#0E2E74'},
  num:{kind:'square', r:6, bg:'#FFFFFF', fg:'#0E2E74'},
  conc:{kind:'line', label:'Takeaway', lc:'#7CC6FF', lineC:'#3F67B8', tc:'#FFFFFF'},
  footer:{brand:true, pagePrefix:'PAGE '},
  imgFramed:true, imgR:8, imgPh:{grad:{ang:135, stops:[[0,'#2A5CC8'],[100,'#0B1E52']]}}
};
function hud(C, x, y, w, h, o){
  o = o || {}; var c = o.c || 28, col = o.color || '#7CC6FF';
  C.path([['M',x+c,y],['L',x+w*0.3,y],['L',x+w*0.33,y+10],['L',x+w*0.67,y+10],['L',x+w*0.7,y],['L',x+w-c,y],['L',x+w,y+c],['L',x+w,y+h-c],['L',x+w-c,y+h],['L',x+c,y+h],['L',x,y+h-c],['L',x,y+c],['Z']], {stroke:col, lw:1.6, lop:0.9, fill:o.fill || null, op:o.op});
  for (var k = 0; k < 6; k++) C.shape('parallelogram', x + 40 + k * 16, y + h + 10, 12, 8, {fill:col, op:0.8});
  for (var j = 0; j < 6; j++) C.shape('parallelogram', x + w - 136 + j * 16, y + h + 10, 12, 8, {fill:col, op:0.8});
}
function grid(C){
  for (var x = 0; x <= W; x += 80) C.rect(x, 0, 1, H, {fill:'#FFFFFF', op:0.035});
  for (var y = 0; y <= H; y += 80) C.rect(0, y, W, 1, {fill:'#FFFFFF', op:0.035});
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0,'#133A8E'],[100,'#0A2260']]}});
  grid(C);
  D.glow(C, 640, 820, 700, 260, '#3A8DF0', 0.35);
  C.rect(70, 36, W - 140, 1, {fill:'#FFFFFF', op:0.35});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  D.glow(C, 640, 600, 620, 200, '#6FC3FF', 0.75);
  D.glow(C, 640, 640, 260, 90, '#FFFFFF', 0.55);
  if (o.img) D.imgBox(C, 0, 470, W, 250, {});
  C.rect(70, 40, W - 140, 1, {fill:'#FFFFFF', op:0.5});
  if (f.company) C.text(70, 58, 500, 20, f.company, {font:'body', size:13, color:'#FFFFFF', lh:1, ls:0.2, caps:true, ed:{f:'tf-company'}});
  var words = String(C.s.title || '').split(/\s+/), cut = words.length > 2 ? Math.ceil(words.length / 2) : 1;
  C.text(140, 150, W - 280, 60, words.slice(0, cut).join(' '), {font:'body', fit:{max:38, min:18}, color:'#FFFFFF', align:'c', ls:0.35, lh:1, maxLines:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (words.length > cut) C.text(80, 222, W - 160, 150, words.slice(cut).join(' '), {font:'head', fit:{max:84, min:34}, color:'#FFFFFF', align:'c', caps:true, lh:1.02, maxLines:2, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(240, 385, W - 480, 56, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:'#CFE0FA', align:'c', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(70, H - 60, W - 140, 1, {fill:'#FFFFFF', op:0.5});
  var who = [f.presenter ? 'Presented by ' + f.presenter : '', f.designation].filter(Boolean).join(', ');
  if (who) C.text(70, H - 48, 600, 18, who, {font:'body', size:12, color:'#FFFFFF', lh:1});
  if (f.date || f.website) C.text(W - 570, H - 48, 500, 18, [f.date, f.website].filter(Boolean).join('  |  '), {font:'body', size:12, color:'#FFFFFF', lh:1, align:'r'});
}
function closing(C, v){
  var P = C.P;
  D.glow(C, 640, 560, 520, 220, '#6FC3FF', 0.7);
  C.text(140, 160, W - 280, 40, C.s.subtitle || 'Thank you for your', {font:'body', fit:{max:26, min:14}, color:'#FFFFFF', align:'c', ls:0.3, lh:1, maxLines:1, ed:{f:'subtitle'}});
  C.text(100, 210, W - 200, 110, C.s.title || 'Attention', {font:'head', fit:{max:80, min:36}, color:'#FFFFFF', align:'c', caps:true, ls:0.08, ed:{f:'title'}, maxLines:1});
  hud(C, 180, 380, W - 360, 180, {fill:'#1A4299', op:0.4});
  D.contactRow(C, 210, 440, W - 420, {});
}
function agenda(C, v){
  var P = C.P, it = C.it.slice(0, 8), n = it.length;
  C.path([['M',70,560],['C',140,380,110,200,260,150],['C',330,130,380,200,360,300]], {stroke:'#7CC6FF', lw:1.4, lop:0.6});
  for (var k = 0; k < 7; k++) C.text(70 + (k % 2) * 20, 180 + k * 44, 260, 30, '0110 1001 01', {font:'body', size:16, color:'#7CC6FF', op:0.35 - k * 0.03, lh:1, ls:0.2});
  C.text(460, 70, 700, 80, C.s.title || 'Agenda', {font:'head', fit:{max:58, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  var cols = n > 4 ? 2 : 1, per = Math.ceil(n / cols), cw = cols === 2 ? 350 : 700, rh = Math.min(84, 470 / per);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = 460 + c * (cw + 30), y = 180 + r * rh;
    C.rect(x, y + (rh - 36) / 2, 36, 36, {fill:'#FFFFFF', r:6});
    C.text(x, y + (rh - 36) / 2, 36, 36, String(k + 1), {font:'head', size:18, color:'#0E2E74', align:'c', va:'m', lh:1, caps:false});
    C.text(x + 52, y + 6, cw - 60, rh * 0.45, t.h || t.d, {font:'head', fit:{max:19, min:12}, color:'#FFFFFF', va:'b', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    if (t.h && t.d) C.text(x + 52, y + rh * 0.45 + 10, cw - 60, rh * 0.45 - 6, t.d, {font:'body', fit:{max:14, min:10}, color:'#BFD3F5', lh:1.25, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#7CC6FF'}); return; }
  hud(C, 140, 110, W - 280, H - 250, {fill:'#1A4299', op:0.35});
  D.B.quote(C, {x:200, y:140, w:W - 400, h:H - 300}, {qc:'#7CC6FF', qFont:'body'});
}
function frameStd(C, o){
  hud(C, 30, 30, W - 60, H - 74, {fill:'#1A4299', op:0.18});
  var oo = D.merge(o, {}); D.std(C, oo);
}
D.register({
  id:'blueprint', name:'Blueprint', tag:'Royal blue · futuristic', desc:'Royal-blue blueprint grid, HUD frames, airy tracked lettering and outlined info panels.',
  fonts:{head:'Exo 2', body:'Titillium Web', headW:600, bodyW:400, headLh:1.05, headLs:0.02, gf:'Exo 2:wght@400;500;600;700|Titillium Web:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Glow horizon',{}), V('v2','Image horizon',{img:'bottom'})],
    agenda:[V('v1','Binary agenda',{})],
    text:[V('v1','Framed statement',{fn:function(C,o){ hud(C, 140, 110, W - 280, H - 250, {fill:'#1A4299', op:0.35}); D.std(C, {body:'text'}); }}), V('v2','Statement + image right',{body:'text', img:'right'})],
    p2:[V('v1','Outlined panels',{body:'duo'}), V('n2','Framed columns',{foot:false, fn:function(C){ frameStd(C, {body:'numbered', hk:'center'}); }}), V('v2','Image left + list',{body:'list', img:'left', mark:'num'}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Outlined columns',{body:'cols', center:true}), V('n2','HUD framed list',{foot:false, fn:function(C){ frameStd(C, {body:'list', mark:'num', rules:true}); }}), V('n3','Numbered columns',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', mark:'num'}), V('v3','List + image right',{body:'list', img:'right'})],
    p4:[V('v1','Mind map',{body:'hub'}), V('n2','Outlined grid',{body:'grid'}), V('n3','Four columns',{body:'cols', center:true}), V('v2','Image left + list',{body:'list', img:'left', mark:'num'}), V('v3','Grid + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Outlined grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Outlined grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Framed numbers',{foot:false, fn:function(C){ frameStd(C, {body:'statCols', vc:'#FFFFFF'}); }}), V('n2','Outlined stat panels',{body:'statCards', icons:true}), V('n3','Progress rings',{body:'statRings', track:'#1F4AA3'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Signal line',{body:'tlH'}), V('n2','Outlined milestones',{body:'tlCards'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','HUD quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 09 — Midnight Neon : black, blue→violet gradient cards, arrow chips (AI & Tech pitch sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#060609', text:'#FFFFFF', title:'#FFFFFF', muted:'#A9ACBD', accent:'#6C5CFF', accent2:'#C45BFF', card:'#15151E', line:'#2A2A38', onAccent:'#FFFFFF'};
var GR = {ang:135, stops:[[0,'#2F63FF'],[100,'#B24DFF']]};
var S = {
  margin:68,
  header:{kind:'left', tMax:46, tMin:26, sMax:16, top:78},
  card:{kind:'solid', r:18, fill:'#14141C', line:'#2A2A38', lw:1, hl:{kind:'grad', grad:GR, dark:true, tc:'#FFFFFF', mc:'#EDE6FF'}},
  icon:{kind:'circle', grad:GR, bg:'#6C5CFF', fg:'#FFFFFF'},
  num:{kind:'circle', bg:'#6C5CFF', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#6C5CFF', op:0.16, line:'#6C5CFF', lw:1, label:'Takeaway', lc:'#B9A6FF'},
  footer:{pagePrefix:''},
  imgFramed:true, imgR:20, imgPh:{grad:{ang:135, stops:[[0,'#1F3FB8'],[100,'#7C2FD1']]}}
};
function brand(C){
  var name = C.cover.company || 'Company';
  C.path([['M',68,36],['L',82,50],['M',82,36],['L',68,50]], {stroke:'#B24DFF', lw:3});
  C.text(92, 32, 300, 22, name, {font:'body', size:14, wt:600, color:'#FFFFFF', va:'m', lh:1});
}
function bg(C, kind){
  C.bg({fill:'#060609'});
  var k = C.i % 3;
  if (k === 0) { D.glow(C, 1280, 0, 420, 320, '#2F63FF', 0.55); D.glow(C, 0, 720, 360, 260, '#7C2FD1', 0.35); }
  else if (k === 1) { D.glow(C, 0, 0, 420, 320, '#2F63FF', 0.5); D.glow(C, 1280, 720, 380, 260, '#B24DFF', 0.35); }
  else { D.glow(C, 1280, 720, 460, 320, '#2F63FF', 0.5); D.glow(C, 640, -40, 360, 160, '#7C2FD1', 0.35); }
  brand(C);
}
function chip(C, x, y, w, label){
  C.rect(x, y, w, 30, {line:'#B24DFF', lw:1.4, r:15});
  C.text(x + 14, y, w - 44, 30, label, {font:'body', size:12, wt:600, color:'#D9CCFF', va:'m', lh:1, maxLines:1});
  C.icon(x + w - 26, y + 7, 16, 'mdi:arrow-top-right', '#D9CCFF');
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  var cols = [[70,80],[70,250],[70,420],[230,80],[230,250],[230,420]];
  if (o.img) D.imgBox(C, 70, 80, 340, 520, {r:170});
  else cols.forEach(function(p, k){ C.ell(p[0], p[1], 150, 150, {grad:{ang:135 + k * 20, stops:[[0,'#2F63FF'],[100,'#B24DFF']]}, op:0.35 + (k % 3) * 0.2}); });
  C.text(470, 150, 740, 280, C.s.title, {font:'head', fit:{max:72, min:34}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.05, va:'b'});
  C.rect(472, 456, 210, 42, {grad:GR, r:21});
  C.text(472, 456, 210, 42, f.tagline || 'PITCH DECK', {font:'body', size:15, wt:600, color:'#FFFFFF', align:'c', va:'m', caps:true, ls:0.08, lh:1, ed:f.tagline ? {f:'tf-tagline'} : null, maxLines:1});
  if (C.s.subtitle) C.text(472, 530, 620, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#D2D4E0', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  C.icon(W - 110, H - 110, 34, 'mdi:arrow-top-right', '#FFFFFF');
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  D.imgBox(C, 68, 110, 560, 270, {r:18});
  D.imgBox(C, 650, 110, 560, 270, {r:18});
  C.text(68, 430, 420, 170, C.s.title || 'Thank You for Watching', {font:'head', fit:{max:56, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, lh:1.08, va:'t'});
  if (C.s.subtitle) C.text(520, 434, 280, 150, C.s.subtitle, {font:'body', fit:{max:15, min:11}, color:'#A9ACBD', ed:{f:'subtitle'}, lh:1.45, maxLines:6});
  D.contactList(C, 840, 430, 380, {gap:52, markO:{kind:'ring', bg:'#B24DFF', fg:'#D9CCFF'}});
}
function agenda(C, v){
  var P = C.P, it = C.it.slice(0, 8), n = it.length, M = 68;
  C.text(M, 90, 420, 200, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.05});
  if (C.s.subtitle) C.text(M, 300, 380, 110, C.s.subtitle, {font:'body', fit:{max:16, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var cols = 2, per = Math.ceil(n / cols), x0 = 520, cw = 330, rh = Math.min(130, 520 / per);
  it.forEach(function(t, k){
    var c = k % 2, r = Math.floor(k / 2), x = x0 + c * (cw + 20), y = 100 + r * rh;
    C.rect(x, y, cw, rh - 14, {grad:k === 0 ? GR : null, fill:k === 0 ? null : '#14141C', line:'#2A2A38', lw:1, r:16});
    C.text(x + 20, y + 12, 80, 34, D.u.pad2(k + 1), {font:'head', size:26, color:k === 0 ? '#FFFFFF' : '#B9A6FF', lh:1, caps:false});
    C.icon(x + cw - 38, y + 16, 18, 'mdi:arrow-top-right', '#FFFFFF');
    C.text(x + 20, y + 50, cw - 40, rh - 72, t.h || t.d, {font:'head', fit:{max:19, min:12}, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#B24DFF'}); return; }
  C.rect(150, 140, W - 300, H - 280, {fill:'#14141C', line:'#2A2A38', lw:1, r:24});
  D.B.quote(C, {x:210, y:170, w:W - 420, h:H - 340}, {qc:'#B24DFF'});
}
D.register({
  id:'neon', name:'Midnight Neon', tag:'Black & neon · pitch', desc:'Midnight black with electric blue-to-violet gradient cards, glow corners and arrow chips.',
  fonts:{head:'Manrope', body:'Manrope', headW:700, bodyW:400, headLh:1.08, gf:'Manrope:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Neon circles',{}), V('v2','Arch image',{img:'left'})],
    agenda:[V('v1','Neon tiles',{})],
    p2:[V('v1','Gradient panels',{body:'duo', hl:true}), V('n2','Panels + VS',{body:'duo', vs:true}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Neon cards',{body:'cols', hl:'first'}), V('n2','Gradient bars',{body:'bars', grad:function(k){ return {ang:90, stops:[[0,['#2F63FF','#7C4DFF','#B24DFF'][k%3]],[100,'#14141C']]}; }}), V('n3','Numbered list',{body:'list', mark:'num', rules:true}), V('v2','Image left + cards',{body:'list', img:'left', cards:true}), V('v3','Cards + image right',{body:'list', img:'right', cards:true}), V('v4','Cards + image bottom',{body:'cols', img:'bottom'})],
    p4:[V('v1','Neon grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', hl:'alt'}), V('n3','Hub diagram',{body:'hub', hubGrad:GR}), V('v2','Image left + grid',{body:'list', img:'left', cards:true}), V('v3','Grid + image right',{body:'list', img:'right', mark:'num'})],
    p56:[V('v1','Neon grid',{body:'grid', hl:'first'}), V('n2','Gradient bars',{body:'bars'}), V('n3','Two-column list',{body:'list', cols:2, mark:'num'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Neon grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Gradient bars',{body:'statBars'}), V('n2','Neon stat cards',{body:'statCards', hl:'first'}), V('n3','Big numbers',{body:'statCols', vc:'#B9A6FF'}), V('n4','Progress rings',{body:'statRings', track:'#1E1E2A'}), V('v2','Image left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Neon milestones',{body:'tlCards', hlLast:true}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Card quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 10 — Bronze Executive : espresso + bronze glow, frosted panels, tall condensed caps (Pitch Deck 2 sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#1C1815', text:'#FFFFFF', title:'#FFFFFF', muted:'#D2C7BC', accent:'#E8903F', accent2:'#F4B878', card:'#FFFFFF', line:'#5E544C', onAccent:'#1C1815'};
var S = {
  margin:72,
  header:{kind:'split', tMax:52, tMin:28, sMax:15, top:84},
  card:{kind:'glass', r:14, fill:'#FFFFFF', op:0.09, line:'#FFFFFF', lop:0.18, lw:1, dark:true, tc:'#FFFFFF', mc:'#D8CEC4', hl:{kind:'grad', grad:{ang:135, stops:[[0,'#E8903F'],[100,'#B8612A']]}, dark:true, tc:'#FFFFFF', mc:'#FFE9D6'}},
  icon:{kind:'plain', fg:'#FFFFFF'},
  num:{kind:'big', fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#FFFFFF', op:0.08, line:'#FFFFFF', lw:1, r:12, lc:'#F4B878', label:'Bottom line'},
  footer:{pagePrefix:'PAGE ', color:'#A89A8D'},
  bodyGap:26,
  imgFramed:true, imgR:12, imgPh:{grad:{ang:135, stops:[[0,'#6B4A33'],[100,'#2E2622']]}}
};
function frame(C){
  C.rect(28, 28, W - 56, H - 56, {fill:'#FFFFFF', op:0.05, line:'#FFFFFF', lop:0.14, lw:1, r:18});
  var name = C.cover.company || 'Company';
  C.path([['M',72,48],['L',72,62],['L',82,62]], {stroke:'#FFFFFF', lw:2.4});
  C.text(90, 44, 400, 20, name, {font:'body', size:13, color:'#FFFFFF', va:'m', lh:1});
  C.icon(W - 116, 46, 18, 'mdi:magnify', '#FFFFFF');
  C.icon(W - 88, 46, 18, 'mdi:menu', '#FFFFFF');
  C.rect(72, 72, W - 144, 1, {fill:'#FFFFFF', op:0.25});
}
function bg(C, kind){
  C.bg({fill:'#16120F'});
  var k = C.i % 2;
  D.glow(C, k ? 1100 : 180, 720, 620, 360, '#A2521B', 0.85);
  D.glow(C, k ? 200 : 1080, 0, 460, 260, '#6E3A18', 0.55);
  frame(C);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.text(72, 120, W - 144, 200, C.s.title, {font:'head', fit:{max:120, min:48}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, lh:0.98, va:'b', ls:0.01});
  if (C.s.subtitle) C.text(72, 330, W - 144, 60, C.s.subtitle, {font:'body', fit:{max:28, min:15}, color:'#EFE6DD', ed:{f:'subtitle'}, lh:1.25, maxLines:2});
  C.rect(92, 460, 560, 140, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  D.imgBox(C, 112, 486, 88, 88, {r:44, shape:'ell'});
  C.text(220, 486, 140, 16, 'Presented by', {font:'body', size:12, color:'#D2C7BC', lh:1});
  C.text(220, 506, 400, 22, f.presenter || f.company || '', {font:'body', size:17, wt:700, color:'#FFFFFF', lh:1, ed:{f:f.presenter ? 'tf-presenter' : 'tf-company'}});
  if (f.designation) C.text(220, 534, 400, 18, f.designation, {font:'body', size:13, color:'#D2C7BC', lh:1, ed:{f:'tf-designation'}});
  if (f.website) C.text(420, 486, 220, 16, f.website, {font:'body', size:12, wt:700, color:'#FFFFFF', lh:1, ed:{f:'tf-website'}});
  C.rect(690, 460, 300, 140, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  C.text(714, 480, 260, 60, f.date || f.tagline || 'Strategy briefing', {font:'head', fit:{max:32, min:16}, color:'#F4B878', lh:1, maxLines:1, va:'m'});
  C.text(714, 540, 260, 44, f.tagline && f.date ? f.tagline : 'Confidential board presentation', {font:'body', fit:{max:13, min:10}, color:'#D2C7BC', lh:1.3, maxLines:2});
}
function closing(C, v){
  var P = C.P, f = C.cover;
  C.text(72, 130, W - 144, 170, C.s.title || 'Thank You', {font:'head', fit:{max:140, min:56}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, lh:1, align:'c', va:'b'});
  if (C.s.subtitle) C.text(160, 310, W - 320, 50, C.s.subtitle, {font:'body', fit:{max:26, min:14}, color:'#EFE6DD', ed:{f:'subtitle'}, lh:1.25, align:'c', maxLines:2});
  C.rect(92, 450, 700, 150, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  D.contactList(C, 118, 470, 650, {gap:40, markO:{kind:'plain', fg:'#F4B878'}});
  C.rect(820, 450, 368, 150, {fill:'#FFFFFF', op:0.1, line:'#FFFFFF', lop:0.2, lw:1, r:14});
  if (C.s.conclusion) C.text(846, 470, 316, 110, C.s.conclusion, {font:'body', fit:{max:17, min:11}, color:'#FFFFFF', ed:{f:'x-conc'}, lh:1.35, maxLines:4, va:'m'});
}
function agenda(C, v){
  var P = C.P;
  C.text(72, 90, W - 144, 80, C.s.title || 'List of Contents', {font:'head', fit:{max:58, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  D.B.agTiles(C, {x:72, y:190, w:W - 144, h:430}, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#E8903F'}); return; }
  D.B.quote(C, {x:150, y:130, w:W - 300, h:H - 250}, {qc:'#E8903F', qFont:'head', qMax:44});
}
D.register({
  id:'bronze', name:'Bronze Executive', tag:'Espresso & bronze · executive', desc:'Espresso darkness with bronze light, frosted glass panels and tall condensed headlines.',
  fonts:{head:'Oswald', body:'Source Sans 3', headW:700, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Oswald:wght@500;600;700|Source Sans 3:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Glass cards',{})],
    agenda:[V('v1','Content tiles',{})],
    p2:[V('v1','Vision / mission panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p3:[V('v1','Service panels',{body:'list', cards:true}), V('n2','Frosted columns',{body:'cols', hl:'first'}), V('n3','Numbered columns',{body:'numbered', lineC:'#5E544C'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true}), V('v4','Image top + columns',{body:'cols', img:'top'})],
    p4:[V('v1','Frosted grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', mark:'num'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Image left + panels',{body:'list', img:'left', cards:true}), V('v3','Panels + image right',{body:'list', img:'right', cards:true})],
    p56:[V('v1','Frosted grid',{body:'grid', hl:'first'}), V('n2','Two-column panels',{body:'list', cols:2, cards:true}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Image left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Frosted grid',{body:'grid'}), V('n2','Two-column panels',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Big numbers',{body:'statCols', vc:'#FFFFFF', vMax:104}), V('n2','Frosted stat panels',{body:'statCards', hl:'first'}), V('n3','Progress bars',{body:'statBars'}), V('v2','Image left + numbers',{body:'statBars', img:'left'})],
    timeline:[V('v1','Frosted milestones',{body:'tlCards', hlLast:true}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Growth stairs',{body:'tlStairs'}), V('v2','Vertical + image right',{body:'tlV', img:'right'})],
    quote:[V('v1','Bronze quote',{}), V('v2','Quote + image right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 11 — Azure Corporate : vivid blue gradient, white type, rounded photo frames (Business Project sample) */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#0B63C5', text:'#FFFFFF', title:'#FFFFFF', muted:'#DCEBFA', accent:'#FFFFFF', accent2:'#9FD3FF', card:'#0A57AE', line:'#5CA0E6', onAccent:'#0B63C5'};
var S = {
  margin:64,
  header:{kind:'left', tMax:48, tMin:26, sMax:18, top:54},
  card:{kind:'grad', r:4, grad:{ang:180, stops:[[0,'#0A4FA3'],[100,'#1A74D6',0.6]]}, line:'#FFFFFF', lw:1, lop:0.25, shadow:0.18, dark:true, tc:'#FFFFFF', mc:'#DCEBFA', hl:{kind:'solid', fill:'#FFFFFF', dark:false, tc:'#0B4A96', mc:'#2C5C92'}},
  icon:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF', lw:2},
  num:{kind:'ring', bg:'#FFFFFF', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#FFFFFF', op:1, label:'Takeaway', lc:'#0B63C5', tc:'#0B4A96'},
  footer:{pagePrefix:'PAGE ', color:'#DCEBFA'},
  imgFramed:true, imgR:26, imgPh:{grad:{ang:135, stops:[[0,'#7DBBF5'],[100,'#0A4FA3']]}}
};
function bg(C, kind){
  var k = C.i % 3;
  C.bg({grad:{ang:[125, 60, 200][k], stops:[[0,'#0A56B4'],[55,'#1478DA'],[100,'#62B3F2']]}});
  D.glow(C, [1100, 150, 640][k], [90, 650, 700][k], 520, 300, '#CDE9FF', 0.35);
  D.glow(C, [200, 1100, 100][k], [700, 60, 80][k], 420, 260, '#073E8C', 0.45);
}
function brand(C, x, y){
  C.path([['M',x,y+14],['L',x+16,y+2],['L',x+32,y+14],['M',x+6,y+12],['L',x+6,y+30],['L',x+26,y+30],['L',x+26,y+12],['M',x+13,y+30],['L',x+13,y+19],['L',x+19,y+19],['L',x+19,y+30]], {stroke:'#FFFFFF', lw:1.6});
  var n = String(C.cover.company || 'Company').split(' ');
  C.text(x + 42, y, 260, 34, n.slice(0, 1).join(' ') + (n.length > 1 ? '\n' + n.slice(1).join(' ') : ''), {font:'body', size:12.5, color:'#FFFFFF', lh:1.2, va:'m', ed:C.i === 0 ? {f:'tf-company'} : null});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.bg({grad:{ang:135, stops:[[0,'#1E7BDB'],[60,'#0B5FC0'],[100,'#063E8A']]}});
  D.glow(C, 900, 180, 560, 360, '#D6EEFF', 0.45);
  brand(C, 90, 60);
  C.rect(W - 132, 64, 44, 2, {fill:'#FFFFFF', op:0.7}); C.rect(W - 132, 74, 44, 2, {fill:'#FFFFFF', op:0.7}); C.rect(W - 132, 84, 44, 2, {fill:'#FFFFFF', op:0.7});
  if (o.img) { D.imgBox(C, 700, 150, 500, 420, {r:28}); }
  var al = o.img ? 'l' : 'c', x = o.img ? 90 : 120, w = o.img ? 560 : W - 240;
  var words = String(C.s.title || '').split(/\s+/), cut = words.length > 2 ? Math.floor(words.length / 2) : 1;
  C.text(x, 200, w, 60, words.slice(0, cut).join(' '), {font:'body', fit:{max:44, min:20}, color:'#FFFFFF', align:al, caps:true, lh:1, maxLines:1, va:'b', ed:{f:'x-tpart', p:0}});
  if (words.length > cut) C.text(x - 10, 266, w + 20, 170, words.slice(cut).join(' '), {font:'head', fit:{max:104, min:40}, color:'#FFFFFF', align:al, caps:true, lh:1, maxLines:2, ed:{f:'x-tpart', p:1}});
  if (C.s.subtitle) C.text(x, 450, w, 56, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#E4F1FD', align:al, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  if (f.website) C.text(x, 520, w, 22, f.website, {font:'body', size:16, color:'#FFFFFF', align:al, lh:1, ed:{f:'tf-website'}});
  C.rect(90, H - 70, W - 180, 1, {fill:'#FFFFFF', op:0.5});
}
function closing(C, v){
  var P = C.P;
  C.bg({grad:{ang:135, stops:[[0,'#1E7BDB'],[60,'#0B5FC0'],[100,'#063E8A']]}});
  D.imgBox(C, 640, 40, 600, 360, {r:40});
  brand(C, 90, 80);
  C.text(90, 190, 520, 140, C.s.title || "Thank's For Watching", {font:'head', fit:{max:64, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, lh:1.05, caps:true, va:'b'});
  if (C.s.subtitle) C.text(90, 344, 520, 110, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#E4F1FD', ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var cs = D.contacts(C), x = 90;
  cs.forEach(function(c){ var w = Math.min(320, c.val.length * 8.6 + 40); C.rect(x, 480, w, 34, {fill:'#FFFFFF', r:17}); C.text(x, 480, w, 34, c.val, {font:'body', fit:{max:14, min:9}, wt:600, color:'#0B4A96', align:'c', va:'m', lh:1, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1}); x += w + 14; });
  if (C.s.conclusion) C.text(640, 440, 560, 80, C.s.conclusion, {font:'body', fit:{max:17, min:12}, color:'#FFFFFF', ed:{f:'x-conc'}, lh:1.4, maxLines:3});
}
function agenda(C, v){
  var P = C.P, it = C.it.slice(0, 10), n = it.length;
  D.imgBox(C, 640, 0, 640, H, {});
  C.text(90, 70, 520, 70, C.s.title || 'Overview', {font:'head', fit:{max:52, min:28}, color:'#FFFFFF', caps:true, ed:{f:'title'}, maxLines:1});
  if (C.s.subtitle) C.text(90, 145, 500, 80, C.s.subtitle, {font:'body', fit:{max:15, min:11}, color:'#E4F1FD', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var top = 250, rh = Math.min(44, 400 / Math.max(1, n));
  C.rect(90, top - 10, 30, rh * n + 20, {fill:'#FFFFFF', r:2});
  it.forEach(function(t, k){
    var y = top + k * rh;
    C.text(90, y, 30, rh, D.u.pad2(k + 1), {font:'body', size:13, wt:700, color:'#0B4A96', align:'c', va:'m', lh:1});
    C.text(140, y, 460, rh, t.h || t.d, {font:'body', fit:{max:17, min:11}, wt:600, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#FFFFFF'}); return; }
  C.rect(140, 120, W - 280, H - 240, {fill:'#FFFFFF', op:0.12, line:'#FFFFFF', lop:0.5, lw:1, r:6});
  D.B.quote(C, {x:200, y:150, w:W - 400, h:H - 300}, {qc:'#FFFFFF'});
}
D.register({
  id:'azure', name:'Azure Corporate', tag:'Vivid blue · business', desc:'Bright azure gradients, crisp white typography, deep-blue panels and rounded photo frames.',
  fonts:{head:'Nunito Sans', body:'Nunito Sans', headW:800, bodyW:400, headLh:1.05, gf:'Nunito Sans:wght@400;600;700;800;900'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Centered title',{}), V('v2','Title + photo',{img:'right'})],
    agenda:[V('v1','Numbered overview',{body:'agList', hk:'left'}), V('v2','Overview + photo',{fn:function(C){ agenda(C); }})],
    p2:[V('v1','Deep-blue panels',{body:'duo', hl:true}), V('n2','Numbered columns',{body:'numbered', lineC:'#9FD3FF', numC:'#FFFFFF'}), V('v2','Photo left + list',{body:'list', img:'left', mark:'num'}), V('v3','List + photo right',{body:'list', img:'right', mark:'num'})],
    p3:[V('v1','Service cards',{body:'cols', center:true, mark:'num', ms:70}), V('n2','Numbered list',{body:'list', mark:'num'}), V('n3','Numbered columns',{body:'numbered', lineC:'#9FD3FF', numC:'#FFFFFF'}), V('v2','Photo left + objectives',{body:'list', img:'left', mark:'num', cards:true}), V('v3','Objectives + photo right',{body:'list', img:'right', mark:'num'}), V('v4','Photo top + columns',{body:'cols', img:'top'})],
    p4:[V('v1','Blue card grid',{body:'grid', hl:'first'}), V('n2','Four columns',{body:'cols', center:true}), V('n3','Hub diagram',{body:'hub', hubGrad:{type:'radial', stops:[[0,'#FFFFFF'],[100,'#CDE6FF']]}}), V('v2','Photo left + list',{body:'list', img:'left', mark:'num'}), V('v3','List + photo right',{body:'list', img:'right'})],
    p56:[V('v1','Blue card grid',{body:'grid', hl:'first'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'}), V('n3','Numbered grid',{body:'numbered', lineC:'#9FD3FF', numC:'#FFFFFF'}), V('v2','Photo left + list',{body:'list', img:'left', cols:1})],
    p78:[V('v1','Blue card grid',{body:'grid'}), V('n2','Two-column list',{body:'list', cols:2, mark:'num'})],
    stats:[V('v1','Progress rings',{body:'statRings', track:'#4F95DE'}), V('n2','Blue stat cards',{body:'statCards', hl:'first'}), V('n3','Big numbers',{body:'statCols'}), V('n4','Progress bars',{body:'statBars'}), V('v2','Photo left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Milestone cards',{body:'tlCards', hlLast:true, hc:'#FFFFFF'}), V('n2','Horizontal line',{body:'tlH'}), V('n3','Arrow steps',{body:'tlChev'}), V('v2','Vertical + photo right',{body:'tlV', img:'right'})],
    quote:[V('v1','Glass quote',{}), V('v2','Quote + photo right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 12 — Executive Serif : ivory, navy and gold, classic board-room serif */
(function(){
var D = window.DSTE; if (!D || !D.register) return;
var W = D.W, H = D.H, V = D.V;
var P0 = {bg:'#FBF9F4', text:'#14213D', title:'#14213D', muted:'#4B5563', accent:'#B8893B', accent2:'#14213D', card:'#FFFFFF', line:'#E3DCCD', onAccent:'#FFFFFF'};
var NAVY = {bg:'#14213D', text:'#FFFFFF', title:'#FFFFFF', muted:'#C9CFDC', accent:'#D4AA5E', accent2:'#D4AA5E', card:'#1D2C4F', line:'#34456B', onAccent:'#14213D'};
var S = {
  margin:96,
  header:{kind:'left', tMax:44, tMin:26, sMax:17, top:64, kicker:function(C){ return 'Section ' + D.u.pad2(C.i); }, kc:'#B8893B', kSize:12, kGap:30},
  card:{kind:'top', r:0, fill:'#FFFFFF', line:'#E3DCCD', lw:1, barW:3, shadow:0.06, hl:{kind:'solid', fill:'#14213D', dark:true, tc:'#FFFFFF', mc:'#C9CFDC'}},
  icon:{kind:'ring', bg:'#B8893B', fg:'#B8893B', lw:1.4},
  num:{kind:'big', fg:'#B8893B'},
  conc:{kind:'line', label:'In summary', lc:'#B8893B', lineC:'#D9CFB9', italic:true, wt:400},
  footer:{fn:foot},
  imgFramed:false, imgPct:0.38, imgPh:{grad:{ang:135, stops:[[0,'#2A3B63'],[100,'#14213D']]}}
};
var SN = D.merge(S, {card:{kind:'top', fill:'#1D2C4F', line:'#34456B', shadow:0, hl:{kind:'solid', fill:'#D4AA5E', dark:false, tc:'#14213D', mc:'#3A3322'}}, icon:{bg:'#D4AA5E', fg:'#D4AA5E'}, num:{fg:'#D4AA5E'}, header:{kc:'#D4AA5E'}, conc:{lc:'#D4AA5E', lineC:'#34456B'}});
function foot(C){
  var P = C.P;
  C.rect(96, H - 50, W - 192, 1, {fill:P.accent, op:0.6});
  C.text(96, H - 40, 600, 16, (C.cover.company || '') + (C.cover.company ? '  —  ' : '') + 'Confidential', {font:'body', size:11, color:P.muted, lh:1, ls:0.08, caps:true});
  C.text(W - 96 - 100, H - 40, 100, 16, String(C.i + 1), {font:'head', size:14, italic:true, color:P.accent, align:'r', lh:1});
}
function navy(C, kind){ return kind === 'stats' || kind === 'quote'; }
function bg(C, kind){
  if (navy(C, kind)) { C.P = NAVY; C.S = SN; } else { C.P = P0; C.S = S; }
  C.bg({fill:C.P.bg});
  C.rect(0, 0, 44, H, {fill:navy(C, kind) ? '#0E1830' : '#14213D'});
  C.rect(44, 0, 4, H, {fill:C.P.accent});
}
function cover(C, v){
  var f = C.cover, o = C.o || {};
  C.P = NAVY; C.bg({fill:'#14213D'});
  if (o.img) D.imgBox(C, 780, 0, 500, H, {});
  var w = o.img ? 600 : 900;
  C.rect(110, 110, 80, 3, {fill:'#D4AA5E'});
  if (f.company) C.text(110, 130, 600, 24, f.company, {font:'body', size:14, wt:700, color:'#D4AA5E', caps:true, ls:0.24, lh:1, ed:{f:'tf-company'}});
  C.text(106, 200, w, 250, C.s.title, {font:'head', fit:{max:78, min:36}, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, lh:1.08, va:'b'});
  if (C.s.subtitle) C.text(110, 470, w - 60, 70, C.s.subtitle, {font:'head', fit:{max:21, min:13}, italic:true, wt:400, color:'#C9CFDC', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  C.rect(110, 580, w - 20, 1, {fill:'#D4AA5E', op:0.6});
  var who = [f.presenter, f.designation].filter(Boolean).join(', ');
  if (who) C.text(110, 600, 520, 20, who, {font:'body', size:14, wt:700, color:'#FFFFFF', lh:1});
  if (f.date) C.text(110, 626, 520, 18, f.date, {font:'body', size:13, color:'#C9CFDC', lh:1, ed:{f:'tf-date'}});
  if (!o.img) { C.ell(W - 330, 150, 420, 420, {line:'#D4AA5E', lw:1, lop:0.35}); C.ell(W - 290, 190, 340, 340, {line:'#D4AA5E', lw:1, lop:0.2}); }
}
function closing(C, v){
  var f = C.cover;
  C.P = NAVY; C.bg({fill:'#14213D'});
  C.ell(W / 2 - 300, H / 2 - 300, 600, 600, {line:'#D4AA5E', lw:1, lop:0.25});
  C.rect(W / 2 - 40, 170, 80, 3, {fill:'#D4AA5E'});
  C.text(140, 200, W - 280, 130, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:36}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1, align:'c', va:'m'});
  if (C.s.subtitle) C.text(240, 340, W - 480, 60, C.s.subtitle, {font:'head', fit:{max:21, min:13}, italic:true, wt:400, color:'#C9CFDC', ed:{f:'subtitle'}, align:'c', lh:1.4, maxLines:2});
  D.contactRow(C, 200, 470, W - 400, {lc:'#D4AA5E', vc:'#FFFFFF'});
}
function agenda(C, v){
  D.agendaSplit(C, {split:0.4, titleY:200, tMax:64, numC:'#B8893B', itemFont:'head', itemWt:400, iMax:22, rowMax:62, ruleC:'#D9CFB9'});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#D4AA5E', qFont:'head', italic:true}); return; }
  D.B.quote(C, {x:180, y:140, w:W - 360, h:H - 260}, {qc:'#D4AA5E', qFont:'head', italic:true, qMax:42});
}
D.register({
  id:'executive', name:'Executive Serif', tag:'Navy & gold · board room', desc:'Ivory pages, navy spine, gold rules and a classic serif — made for boards and investors.',
  fonts:{head:'Playfair Display', body:'Lato', headW:700, bodyW:400, headLh:1.12, gf:'Playfair Display:ital,wght@0,400;0,600;0,700;1,400|Lato:wght@400;700'},
  voice:'Formal, board-level and precise. Measured sentences, evidence first, no hype.',
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Navy classic',{}), V('v2','Navy + photo',{img:'right'})],
    agenda:[V('v1','Serif contents',{})],
    p2:[V('v1','Ruled columns',{body:'numbered'}), V('n2','Two cards',{body:'duo', hl:true}), V('v2','Photo right + list',{body:'list', img:'right', mark:'num', rules:true}), V('v3','Photo left + list',{body:'list', img:'left', mark:'num', rules:true})],
    p3:[V('v1','Ruled cards',{body:'cols', mark:'num'}), V('n2','Numbered columns',{body:'numbered'}), V('n3','Ruled list',{body:'list', mark:'num', rules:true}), V('v2','Photo right + list',{body:'list', img:'right', rules:true}), V('v3','Photo left + list',{body:'list', img:'left', rules:true}), V('v4','Cards + photo band',{body:'cols', img:'bottom', mark:'num'})],
    p4:[V('v1','Ruled grid',{body:'grid', mark:'num'}), V('n2','Four columns',{body:'numbered'}), V('n3','Hub diagram',{body:'hub'}), V('v2','Photo right + list',{body:'list', img:'right', rules:true}), V('v3','Photo left + list',{body:'list', img:'left', rules:true, mark:'num'})],
    p56:[V('v1','Ruled grid',{body:'grid', mark:'num'}), V('n2','Two-column list',{body:'list', cols:2, rules:true, mark:'num'}), V('n3','Numbered grid',{body:'numbered'}), V('v2','Photo left + list',{body:'list', img:'left', cols:1, rules:true})],
    p78:[V('v1','Ruled grid',{body:'grid', mark:'num'}), V('n2','Two-column list',{body:'list', cols:2, rules:true, mark:'num'})],
    stats:[V('v1','Gold numbers',{body:'statCols', vc:'#D4AA5E'}), V('n2','Stat cards',{body:'statCards', hl:'first'}), V('n3','Progress rings',{body:'statRings', track:'#2A3B63'}), V('v2','Photo left + bars',{body:'statBars', img:'left'})],
    timeline:[V('v1','Classic timeline',{body:'tlH'}), V('n2','Ruled milestones',{body:'tlCards'}), V('n3','Vertical timeline',{body:'tlV'}), V('v2','Vertical + photo right',{body:'tlV', img:'right'})],
    quote:[V('v1','Serif quote',{}), V('v2','Quote + photo right',{img:'right'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* ============================================================
   DS Template Kit 2 — shared infographic layouts + decorations for
   templates 13-29. Adds new body layouts to DSTE.B (all prefixed x…)
   and drawing helpers on DSTE.K2. Needs the DS Template Engine (T00).
   Nothing in the engine or older templates is changed.
   ============================================================ */
(function(){
var D = window.DSTE; if (!D || !D.B || D.K2) return;
var W = D.W, H = D.H, u = D.u, B = D.B, mix = u.mix, lum = u.lum, pad2 = u.pad2, clamp = u.clamp;
var K = D.K2 = {};

/* ---------- colour + text helpers ---------- */
function col(C, k){ var s = C.S.series || [C.P.accent, C.P.accent2 || C.P.accent]; return s[((k % s.length) + s.length) % s.length]; }
function onC(c){ return lum(c) > 0.62 ? '#15171C' : '#FFFFFF'; }
K.col = col; K.on = onC;
function heads(it){ return it.map(function(t){ return t.h || ''; }); }
function descs(it){ return it.map(function(t){ return t.d || ''; }); }
/* one common heading + description size for a group of equal boxes */
function fitHD(C, it, w, hBox, dBox, o){
  o = o || {};
  var n = it.length, bst = o.noBoost ? 1 : (n <= 2 ? 1.32 : n === 3 ? 1.22 : n === 4 ? 1.1 : 1);   /* fewer items → larger text */
  o.hMax = Math.round((o.hMax || 20) * bst); o.dMax = Math.round((o.dMax || 15) * bst * 2) / 2;
  var hs = C.groupFit(heads(it), w, hBox, {font:'head', max:o.hMax || 20, min:o.hMin || 11, maxLines:o.hLines || 2});
  var hh = 0; it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, w, {font:'head', max:hs, min:hs, maxLines:o.hLines || 2}).h); });
  var ds = C.groupFit(descs(it), w, Math.max(20, dBox - (hh ? hh + 6 : 0)), {font:'body', max:o.dMax || 15, min:o.dMin || 9.5, lh:1.36});
  return {hs:hs, hh:hh, ds:ds};
}
K.fitHD = fitHD;
/* heading + description block; returns bottom y */
function hd(C, x, y, w, h, t, F, o){
  o = o || {}; var P = C.P, al = o.align || 'l';
  var hh = t.h ? Math.min(F.hh || F.hs * 1.3, h) : 0;
  if (t.h) C.text(x, y, w, hh + 2, t.h, {font:'head', size:F.hs, color:o.tc || P.text, align:al, lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:o.hLines || 2});
  var dy = y + (hh ? hh + 6 : 0), dh = Math.max(14, h - (dy - y));
  C.text(x, dy, w, dh, t.d, {font:'body', size:F.ds, color:o.mc || P.muted, align:al, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  return dy + dh;
}
K.hd = hd;
function badge(C, x, y, d, n, fill, o){
  o = o || {};
  C.ell(x, y, d, d, {fill:fill, line:o.line, lw:o.lw});
  C.text(x, y, d, d, o.label != null ? o.label : pad2(n), {font:'head', size:Math.round(d * 0.4), wt:800, color:o.fg || onC(fill), align:'c', va:'m', lh:1, caps:false, ls:0});
}
K.badge = badge;
function icon(C, x, y, sz, t, color){ C.icon(x, y, sz, D.iconFor(C.s, t.i, (t.h || '') + ' ' + (t.d || '')), color); }
K.icon = icon;

/* ---------- geometry ---------- */
var RAD = Math.PI / 180;
function pol(cx, cy, r, a){ return [cx + r * Math.cos(a * RAD), cy + r * Math.sin(a * RAD)]; }
K.pol = pol;
function arcCmds(cx, cy, r, a0, a1, first){
  var n = Math.max(1, Math.ceil(Math.abs(a1 - a0) / 45)), st = (a1 - a0) / n, k = 4 / 3 * Math.tan(st * RAD / 4), d = [];
  var p0 = pol(cx, cy, r, a0); d.push([first || 'M', p0[0], p0[1]]);
  for (var i = 0; i < n; i++) {
    var s0 = (a0 + st * i) * RAD, s1 = (a0 + st * (i + 1)) * RAD;
    var x0 = cx + r * Math.cos(s0), y0 = cy + r * Math.sin(s0), x3 = cx + r * Math.cos(s1), y3 = cy + r * Math.sin(s1);
    d.push(['C', x0 - k * r * Math.sin(s0), y0 + k * r * Math.cos(s0), x3 + k * r * Math.sin(s1), y3 - k * r * Math.cos(s1), x3, y3]);
  }
  return d;
}
K.arcCmds = arcCmds;
/* ring segment (annulus slice) */
K.seg = function(C, cx, cy, r0, r1, a0, a1, o){
  var d = arcCmds(cx, cy, r1, a0, a1).concat(arcCmds(cx, cy, r0, a1, a0, 'L')); d.push(['Z']);
  return C.path(d, o);
};
K.poly = function(C, pts, o){ var d = pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; }); d.push(['Z']); return C.path(d, o); };
/* smooth curve through points (Catmull-Rom → cubic) */
K.curve = function(C, pts, o){
  var d = [['M', pts[0][0], pts[0][1]]];
  for (var i = 0; i < pts.length - 1; i++) {
    var p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2;
    d.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]);
  }
  return C.path(d, o);
};

/* ---------- decorations ---------- */
K.grid = function(C, step, color, op, x, y, w, h){
  x = x || 0; y = y || 0; w = w || W; h = h || H;
  for (var gx = x + step; gx < x + w; gx += step) C.rect(gx, y, 1, h, {fill:color, op:op});
  for (var gy = y + step; gy < y + h; gy += step) C.rect(x, gy, w, 1, {fill:color, op:op});
};
K.dots = function(C, x, y, cols, rows, gap, r, color, op){
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) C.ell(x + i * gap - r, y + j * gap - r, r * 2, r * 2, {fill:color, op:op});
};
K.halftone = function(C, x, y, cols, rows, gap, rMax, color, op, dir){
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) {
    var t = dir === 'x' ? 1 - i / cols : 1 - (i + j) / (cols + rows);
    var r = rMax * Math.max(0.12, t); C.ell(x + i * gap - r, y + j * gap - r, r * 2, r * 2, {fill:color, op:op});
  }
};
K.starburst = function(C, cx, cy, r, pts, fill, o){
  o = o || {}; var p = [], n = pts || 12;
  for (var k = 0; k < n * 2; k++) { var rr = k % 2 ? r * (o.inner || 0.62) : r, a = -90 + k * 180 / n; p.push(pol(cx, cy, rr, a)); }
  return K.poly(C, p, {fill:fill, stroke:o.line, lw:o.lw, op:o.op});
};
K.xmark = function(C, cx, cy, s, fill, o){
  o = o || {}; var t = s * 0.28, h = s / 2;
  var p = [[-h, -h + t], [-h + t, -h], [0, -t], [h - t, -h], [h, -h + t], [t, 0], [h, h - t], [h - t, h], [0, t], [-h + t, h], [-h, h - t], [-t, 0]];
  var r = (o.rot || 0) * RAD, cs = Math.cos(r), sn = Math.sin(r);
  return K.poly(C, p.map(function(q){ return [cx + q[0] * cs - q[1] * sn, cy + q[0] * sn + q[1] * cs]; }), {fill:fill, stroke:o.line, lw:o.lw});
};
K.target = function(C, cx, cy, r, c1, c2, n){
  n = n || 4;
  for (var k = 0; k < n; k++) { var rr = r * (1 - k / n); C.ell(cx - rr, cy - rr, rr * 2, rr * 2, {fill:k % 2 ? c2 : c1}); }
};
K.chev3 = function(C, x, y, s, color, o){
  o = o || {};
  for (var k = 0; k < 3; k++) {
    var xx = x + k * s * 0.9, pts = [[xx, y], [xx + s * 0.7, y + s / 2], [xx, y + s]];
    if (o.hollow && k === 1) K.poly(C, pts, {stroke:color, lw:2}); else K.poly(C, pts, {fill:color});
  }
};
K.pill = function(C, x, y, w, h, text, o){
  o = o || {};
  C.rect(x, y, w, h, {fill:o.fill, op:o.op, line:o.line, lw:o.lw || 1.2, r:h / 2});
  C.text(x, y, w, h, text, {font:o.font || 'body', size:o.size || Math.round(h * 0.42), wt:o.wt || 600, color:o.color || C.P.text, align:'c', va:'m', lh:1, caps:!!o.caps, ls:o.ls || 0, ed:o.ed || null, maxLines:1});
};
/* soft organic blob (for glass / neon looks) */
K.blob = function(C, cx, cy, r, color, op, seed){
  var p = [], n = 8, s = seed || 1;
  for (var k = 0; k < n; k++) { var a = k * 360 / n, rr = r * (0.78 + 0.22 * Math.sin(s * 2.3 + k * 1.7)); p.push(pol(cx, cy, rr, a)); }
  p.push(p[0], p[1], p[2]);
  var d = [['M', p[1][0], p[1][1]]];
  for (var i = 1; i < p.length - 2; i++) { var p0 = p[i - 1], p1 = p[i], p2 = p[i + 1], p3 = p[i + 2]; d.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]); }
  d.push(['Z']); return C.path(d, {fill:color, op:op});
};
/* rotated-square photo frame: image with the corners covered in the page colour, plus a white border */
K.diamondImg = function(C, cx, cy, r, bgc, o){
  o = o || {};
  D.imgBox(C, cx - r, cy - r, r * 2, r * 2, {r:0});
  var e = r + 2;
  K.poly(C, [[cx - e, cy - e], [cx, cy - e], [cx - e, cy]], {fill:bgc}); K.poly(C, [[cx + e, cy - e], [cx + e, cy], [cx, cy - e]], {fill:bgc});
  K.poly(C, [[cx + e, cy + e], [cx, cy + e], [cx + e, cy]], {fill:bgc}); K.poly(C, [[cx - e, cy + e], [cx - e, cy], [cx, cy + e]], {fill:bgc});
  if (o.border) K.poly(C, [[cx, cy - r], [cx + r, cy], [cx, cy + r], [cx - r, cy]], {stroke:o.border, lw:o.bw || 6});
};
/* 4-point sparkle */
K.spark = function(C, cx, cy, r, color, op){ D.sparkle(C, cx, cy, r, color, op); };
/* torus / loop outline (drawn chrome loops) */
K.loop = function(C, cx, cy, rx, ry, rot, color, lw, op){
  var d = [], n = 48, r = rot * RAD;
  for (var k = 0; k <= n; k++) { var a = k / n * Math.PI * 2, x = rx * Math.cos(a), y = ry * Math.sin(a); d.push([k ? 'L' : 'M', cx + x * Math.cos(r) - y * Math.sin(r), cy + x * Math.sin(r) + y * Math.cos(r)]); }
  C.path(d, {stroke:color, lw:lw, lop:op});
};

/* ============================================================
   BODY LAYOUTS  B.x…  (C, Z, o)
   ============================================================ */

/* half-ring of coloured segments on the left, items on the right */
B.xArc = function(C, Z, o){
  var it = C.it.slice(0, 7), n = it.length; if (!n) return; var P = C.P;
  var R = Math.min(Z.h * 0.5, Z.w * 0.3), cx = Z.x + 6, cy = Z.y + Z.h / 2, r0 = R * 0.58;
  var gap = 1.6, step = 180 / n;
  it.forEach(function(t, k){
    var a0 = -90 + k * step + gap / 2, a1 = -90 + (k + 1) * step - gap / 2, c = col(C, k);
    K.seg(C, cx, cy, r0, R, a0, a1, {fill:c});
    var m = pol(cx, cy, (r0 + R) / 2, (a0 + a1) / 2), is = Math.min(30, (R - r0) * 0.5);
    icon(C, m[0] - is / 2, m[1] - is / 2, is, t, onC(c));
  });
  K.seg(C, cx, cy, 0, r0 * 0.8, -90, 90, {fill:C.S.hubFill || mix(P.bg, P.text, 0.06)});
  var lx = cx + R + 60, lw = Z.x + Z.w - lx, gy = 12, rh = (Z.h - gy * (n - 1)) / n, bd = Math.min(38, rh * 0.62);
  var F = fitHD(C, it, lw - bd - 22, rh * 0.45, rh - 4, {hMax:19, dMax:14.5, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k), mid = y + rh / 2;
    var a = -90 + (k + 0.5) * step, p = pol(cx, cy, R + 6, a);
    C.line(p[0], p[1], lx - 10, mid, {color:c, lw:1.5, op:0.7});
    C.ell(p[0] - 4, p[1] - 4, 8, 8, {fill:c});
    if (C.S.xRows) C.rect(lx, y, lw, rh, {fill:C.S.xRows, r:rh / 2 > 14 ? 12 : 6});
    badge(C, lx + 8, mid - bd / 2, bd, k + 1, c);
    hd(C, lx + bd + 22, y + Math.max(2, (rh - (F.hh + 6 + F.ds * 2.7)) / 2), lw - bd - 30, rh, t, F);
  });
};

/* winding road with pins; labels alternate above and below */
B.xRoad = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var road = C.S.road || '#2A2D34', mid = Z.y + Z.h * 0.52, A = Z.h * 0.16, pts = [], N = 60;
  for (var i = 0; i <= N; i++) { var t = i / N; pts.push([Z.x - 10 + t * (Z.w + 20), mid + A * Math.sin(t * Math.PI * (n <= 3 ? 2 : 3) + 0.4)]); }
  K.curve(C, pts, {stroke:road, lw:30});
  for (var j = 0; j < N; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:'#FFFFFF', lw:2.2, op:0.85});
  var cw = Z.w / n, F = fitHD(C, it, cw - 18, 40, Z.h * 0.26, {hMax:18, dMax:13.5});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), idx = Math.round((x - Z.x + 10) / (Z.w + 20) * N), p = pts[clamp(idx, 0, N)], c = col(C, k), up = k % 2 === 0, d = 50;
    C.ell(p[0] - d / 2 - 5, p[1] - d / 2 - 5, d + 10, d + 10, {fill:'#FFFFFF'});
    C.ell(p[0] - d / 2, p[1] - d / 2, d, d, {fill:c});
    icon(C, p[0] - 13, p[1] - 13, 26, t, onC(c));
    var ty = up ? Z.y : p[1] + d / 2 + 22, th = up ? p[1] - d / 2 - 22 - Z.y : Z.y + Z.h - ty;
    C.line(p[0], up ? p[1] - d / 2 - 5 : p[1] + d / 2 + 5, p[0], up ? p[1] - d / 2 - 16 : p[1] + d / 2 + 16, {color:c, lw:2});
    C.text(x - cw / 2 + 6, up ? ty + th - F.hh - F.ds * 4.2 - 30 : ty, cw - 12, 22, pad2(k + 1), {font:'head', size:15, wt:800, color:c, align:'c', lh:1, caps:false, ls:0});
    hd(C, x - cw / 2 + 9, up ? ty + th - F.hh - F.ds * 4.2 - 6 : ty + 24, cw - 18, F.hh + 6 + F.ds * 4.4, t, F, {align:'c'});
  });
};

/* isometric blocks rising left to right, text above each */
B.xStairs = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var gap = 14, cw = (Z.w - gap * (n - 1)) / n, depth = Math.min(28, cw * 0.18), baseY = Z.y + Z.h, minH = Z.h * 0.18, maxH = Z.h * 0.52;
  var F = fitHD(C, it, cw - 10, 44, Z.h - maxH - depth - 40, {hMax:18, dMax:13.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), h = minH + (maxH - minH) * (n === 1 ? 1 : k / (n - 1)), y = baseY - h, c = col(C, k), bw = cw - depth;
    C.rect(x, y, bw, h, {fill:c});
    K.poly(C, [[x, y], [x + depth, y - depth], [x + bw + depth, y - depth], [x + bw, y]], {fill:mix(c, '#FFFFFF', 0.35)});
    K.poly(C, [[x + bw, y], [x + bw + depth, y - depth], [x + bw + depth, baseY - depth], [x + bw, baseY]], {fill:mix(c, '#000000', 0.25)});
    C.text(x, y + 10, bw, 44, pad2(k + 1), {font:'head', size:30, wt:800, color:onC(c), align:'c', lh:1, caps:false, ls:0});
    icon(C, x + bw / 2 - 15, y + 58, 30, t, onC(c));
    var ty = y - depth - 14 - (F.hh + 6 + F.ds * 4.2);
    hd(C, x, Math.max(Z.y, ty), cw, F.hh + 6 + F.ds * 4.4, t, F, {align:'l'});
  });
};

/* funnel of stacked bands on the left, rows of text on the right */
B.xFunnel = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var fw = Math.min(Z.w * 0.42, 460), gy = 8, bh = (Z.h - gy * (n - 1)) / n, x0 = Z.x;
  var F = fitHD(C, it, Z.w - fw - 90, bh * 0.45, bh, {hMax:19, dMax:14.5, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (bh + gy), c = col(C, k), inset1 = fw * 0.36 * k / n, inset2 = fw * 0.36 * (k + 1) / n;
    K.poly(C, [[x0 + inset1, y], [x0 + fw - inset1, y], [x0 + fw - inset2, y + bh], [x0 + inset2, y + bh]], {fill:c});
    C.text(x0 + inset1, y, fw - inset1 * 2, bh, pad2(k + 1), {font:'head', size:Math.min(26, bh * 0.45), wt:800, color:onC(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    var tx = x0 + fw + 34;
    C.line(x0 + fw - inset1 - 4, y + bh / 2, tx - 8, y + bh / 2, {color:c, lw:1.5, dash:true, op:0.8});
    C.ell(tx - 10, y + bh / 2 - 5, 10, 10, {fill:c});
    hd(C, tx + 14, y + Math.max(0, (bh - (F.hh + 6 + F.ds * 2.6)) / 2), Z.x + Z.w - tx - 14, bh, t, F);
  });
};

/* chevron ribbon row with text + icon below each */
B.xChevrons = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var ov = 16, cw = (Z.w + ov * (n - 1)) / n, ch = 58;
  var F = fitHD(C, it, cw - 40, 1, Z.h - ch - 110, {dMax:14.5});
  var hs = C.groupFit(heads(it), cw - 56, ch - 12, {font:'head', max:17, min:10.5, maxLines:2});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw - ov), c = col(C, k);
    C.shape(k === 0 ? 'homePlate' : 'chevron', x, Z.y, cw, ch, {fill:c});
    C.text(x + (k ? 22 : 14), Z.y, cw - (k ? 44 : 34), ch, t.h || pad2(k + 1), {font:'head', size:hs, color:onC(c), align:'c', va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    var cx = x + cw / 2 - ov / 2;
    C.line(cx, Z.y + ch + 6, cx, Z.y + ch + 26, {color:c, lw:2, dash:true});
    C.ell(cx - 26, Z.y + ch + 28, 52, 52, {fill:mix(c, P.bg, 0.82), line:c, lw:1.5});
    icon(C, cx - 13, Z.y + ch + 41, 26, t, c);
    C.text(x + 14, Z.y + ch + 96, cw - 40, Z.h - ch - 96, t.d, {font:'body', size:F.ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
};

/* numbered step tabs: coloured label bar on the left, text on the right */
B.xTabs = function(C, Z, o){
  var it = C.it.slice(0, 7), n = it.length; if (!n) return; var P = C.P;
  var gy = 12, rh = (Z.h - gy * (n - 1)) / n, lw = Math.min(360, Z.w * 0.36);
  var hs = C.groupFit(heads(it), lw - 110, rh - 10, {font:'head', max:n <= 3 ? 22 : 18, min:11, maxLines:2});
  var ds = C.groupFit(descs(it), Z.w - lw - 110, rh - 6, {font:'body', max:n <= 3 ? 20 : (n <= 5 ? 17 : 15), min:9.5, lh:1.35});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k), sw = 74;
    C.shape('homePlate', Z.x, y, lw, rh, {fill:c});
    C.rect(Z.x, y, sw, rh, {fill:mix(c, '#000000', 0.28)});
    C.text(Z.x, y, sw, rh, (C.S.stepWord || 'Step') + ' ' + pad2(k + 1), {font:'body', size:Math.min(12, rh * 0.3), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1.1, caps:true, ls:0.04});
    if (t.h) C.text(Z.x + sw + 14, y, lw - sw - 40, rh, t.h, {font:'head', size:hs, color:onC(c), va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    var dx = Z.x + lw + 26;
    C.ell(dx, y + rh / 2 - 5, 10, 10, {fill:c});
    C.line(dx + 14, y + rh / 2, dx + 34, y + rh / 2, {color:c, lw:1.5, dash:true});
    icon(C, dx + 40, y + rh / 2 - 12, 24, t, c);
    C.text(dx + 76, y, Z.x + Z.w - dx - 76, rh, t.d, {font:'body', size:ds, color:P.muted, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
  });
};

/* big circle nodes joined by arrows, text below */
B.xFlow = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, d = Math.min(cw * 0.56, 128, Z.h * 0.36), cy = Z.y + d / 2 + 6;
  var F = fitHD(C, it, cw - 22, 44, Z.h - d - 90, {hMax:18, dMax:13.5});
  C.rect(Z.x, cy - 7, Z.w, 14, {fill:C.S.flowBar || mix(P.bg, P.text, 0.12), r:7});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = col(C, k);
    if (k < n - 1) C.shape('chevron', cx + d / 2 + 6, cy - 12, Math.max(16, cw - d - 12), 24, {fill:c, op:0.9});
    C.ell(cx - d / 2 - 6, cy - d / 2 - 6, d + 12, d + 12, {fill:'#FFFFFF', shadow:0.12});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    icon(C, cx - d * 0.22, cy - d * 0.22, d * 0.44, t, onC(c));
    badge(C, cx - 17, cy + d / 2 + 16, 34, k + 1, c);
    hd(C, cx - cw / 2 + 11, cy + d / 2 + 62, cw - 22, Z.y + Z.h - (cy + d / 2 + 62), t, F, {align:'c'});
  });
};

/* hexagon markers alternating up/down along a line */
B.xHex = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, hx = Math.min(124, cw * 0.66, Z.h * 0.34), mid = Z.y + Z.h / 2;
  C.rect(Z.x, mid - 1.5, Z.w, 3, {fill:mix(P.bg, P.text, 0.18)});
  var F = fitHD(C, it, cw - 16, 44, Z.h / 2 - 40, {hMax:19, dMax:15});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = col(C, k), up = k % 2 === 0, y = up ? mid - hx - 12 : mid + 12;
    C.shape('hexagon', cx - hx / 2 - 5, y - 5, hx + 10, hx * 0.88 + 10, {fill:'#FFFFFF', shadow:0.1});
    C.shape('hexagon', cx - hx / 2, y, hx, hx * 0.88, {fill:c});
    icon(C, cx - hx * 0.2, y + hx * 0.44 - hx * 0.2, hx * 0.4, t, onC(c));
    badge(C, cx - 13, mid - 13, 26, k + 1, c, {line:'#FFFFFF', lw:3});
    var ty = up ? mid + 26 : Z.y, th = up ? Z.y + Z.h - ty : y - 14 - Z.y;
    hd(C, cx - cw / 2 + 8, up ? ty : ty + Math.max(0, th - (F.hh + 6 + F.ds * 4.2)), cw - 16, Math.min(th, F.hh + 8 + F.ds * 4.6), t, F, {align:'c'});
  });
};

/* diamonds on a zig-zag line, text above / below */
B.xDiamonds = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, s = Math.min(70, cw * 0.46), mid = Z.y + Z.h / 2, pts = [];
  it.forEach(function(t, k){ pts.push([Z.x + cw * (k + 0.5), mid + (k % 2 ? 34 : -34)]); });
  C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1]]; }), {stroke:mix(P.bg, P.text, 0.35), lw:2});
  var F = fitHD(C, it, cw - 14, 40, Z.h / 2 - s - 30, {hMax:17, dMax:13});
  it.forEach(function(t, k){
    var p = pts[k], c = col(C, k), up = k % 2 === 1;
    C.shape('diamond', p[0] - s / 2 - 6, p[1] - s / 2 - 6, s + 12, s + 12, {fill:'#FFFFFF', line:c, lw:2});
    C.shape('diamond', p[0] - s / 2, p[1] - s / 2, s, s, {fill:c});
    C.text(p[0] - s / 2, p[1] - s / 2, s, s, pad2(k + 1), {font:'head', size:Math.round(s * 0.3), wt:800, color:onC(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    var bh = F.hh + 8 + F.ds * 4.4;
    icon(C, p[0] - 13, up ? p[1] + s / 2 + 14 : p[1] - s / 2 - 40, 26, t, c);
    hd(C, p[0] - cw / 2 + 7, up ? p[1] + s / 2 + 46 : p[1] - s / 2 - 46 - bh, cw - 14, bh, t, F, {align:'c'});
  });
};

/* cards with an overlapping round badge on top */
B.xBadgeCards = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var gap = n > 4 ? 16 : 22, cw = (Z.w - gap * (n - 1)) / n, d = Math.min(76, cw * 0.46), top = Z.y + d / 2;
  var F = fitHD(C, it, cw - 30, 52, Z.h - d - 70, {hMax:20, dMax:16});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), c = col(C, k), cc = D.card(C, x, top, cw, Z.h - d / 2, {});
    C.rect(x, Z.y + Z.h - 8, cw, 8, {fill:c, r:3});
    C.ell(x + cw / 2 - d / 2 - 5, Z.y - 5, d + 10, d + 10, {fill:P.bg});
    C.ell(x + cw / 2 - d / 2, Z.y, d, d, {fill:c});
    icon(C, x + cw / 2 - d * 0.23, Z.y + d * 0.27, d * 0.46, t, onC(c));
    C.text(x, top + d / 2 + 10, cw, 22, (C.S.stepWord || 'Step') + ' ' + pad2(k + 1), {font:'body', size:12, wt:800, color:c, align:'c', caps:true, ls:0.1, lh:1});
    hd(C, x + 15, top + d / 2 + 40, cw - 30, Z.y + Z.h - (top + d / 2 + 40) - 14, t, F, {align:'c', tc:cc.tc, mc:cc.mc});
  });
};

/* two sides compared: first half left, second half right, VS disc between */
B.xVersus = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var L = it.slice(0, Math.ceil(n / 2)), R = it.slice(Math.ceil(n / 2)), cw = (Z.w - 150) / 2, rows = Math.max(L.length, R.length), gy = 12, rh = (Z.h - gy * (rows - 1)) / rows;
  var F = fitHD(C, it, cw - 70, rh * 0.45, rh - 8, {hMax:18, dMax:14, hLines:1});
  function side(list, x, k0, al){
    list.forEach(function(t, k){
      var y = Z.y + k * (rh + gy), c = col(C, al === 'r' ? 0 : 1);
      C.rect(x, y, cw, rh, {fill:C.S.xRows || mix(P.bg, P.text, 0.05), r:12});
      badge(C, al === 'r' ? x + cw - 46 : x + 12, y + rh / 2 - 17, 34, k0 + k + 1, c);
      hd(C, al === 'r' ? x + 16 : x + 58, y + Math.max(4, (rh - (F.hh + 6 + F.ds * 2.6)) / 2), cw - 74, rh - 6, t, F, {align:al});
    });
  }
  side(L, Z.x, 0, 'r'); side(R, Z.x + Z.w - cw, L.length, 'l');
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, d = 96;
  C.rect(cx - 1.5, Z.y, 3, Z.h, {fill:mix(P.bg, P.text, 0.2)});
  C.ell(cx - d / 2, cy - d / 2, d, d, {fill:P.accent, line:P.bg, lw:6});
  C.text(cx - d / 2, cy - d / 2, d, d, 'VS', {font:'head', size:28, wt:800, color:P.onAccent || '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0.04});
};

/* concentric target on the left, icon rows on the right */
B.xTarget = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var r = Math.min(Z.h * 0.46, Z.w * 0.2), cx = Z.x + r + 10, cy = Z.y + Z.h / 2;
  K.target(C, cx, cy, r, col(C, 0), mix(col(C, 0), '#FFFFFF', 0.82), 5);
  C.ell(cx - r * 0.16, cy - r * 0.16, r * 0.32, r * 0.32, {fill:col(C, 1)});
  C.line(cx + 4, cy - 4, cx + r * 1.15, cy - r * 0.95, {color:P.text, lw:4});
  K.poly(C, [[cx + r * 1.1, cy - r * 1.05], [cx + r * 1.32, cy - r * 1.02], [cx + r * 1.2, cy - r * 0.86]], {fill:col(C, 1)});
  var lx = cx + r + 70, lw = Z.x + Z.w - lx, gy = 12, rh = (Z.h - gy * (n - 1)) / n, ms = Math.min(50, rh - 10);
  var F = fitHD(C, it, lw - ms - 30, rh * 0.45, rh - 6, {hMax:19, dMax:14.5, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k);
    C.rect(lx, y, lw, rh, {fill:C.S.xRows || mix(P.bg, P.text, 0.05), r:12});
    C.rect(lx + 10, y + (rh - ms) / 2, ms, ms, {fill:c, r:12});
    icon(C, lx + 10 + ms * 0.22, y + (rh - ms) / 2 + ms * 0.22, ms * 0.56, t, onC(c));
    hd(C, lx + ms + 28, y + Math.max(4, (rh - (F.hh + 6 + F.ds * 2.6)) / 2), lw - ms - 40, rh - 6, t, F);
  });
};

/* ring of nodes around a centre title, text in columns left and right */
B.xCycle = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var R = Math.min(Z.h * 0.4, 170), cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, d = Math.min(62, R * 0.55);
  D.ring(C, cx, cy, R, {color:mix(P.bg, P.text, 0.25), lw:3});
  C.ell(cx - R * 0.58, cy - R * 0.58, R * 1.16, R * 1.16, {fill:P.accent});
  C.text(cx - R * 0.5, cy - R * 0.5, R, R, String(C.s.title || '').split(/\s+/).slice(0, 4).join(' '), {font:'head', fit:{max:20, min:11}, color:P.onAccent || '#FFFFFF', align:'c', va:'m', maxLines:3, lh:1.1});
  var left = Math.ceil(n / 2), sw = (Z.w - R * 2 - d - 80) / 2, rows = left, gy = 10, rh = (Z.h - gy * (rows - 1)) / rows;
  var F = fitHD(C, it, sw, rh * 0.42, rh, {hMax:18, dMax:13.5, hLines:1});
  it.forEach(function(t, k){
    var a = -90 + k * 360 / n, p = pol(cx, cy, R, a), c = col(C, k);
    C.ell(p[0] - d / 2 - 4, p[1] - d / 2 - 4, d + 8, d + 8, {fill:P.bg});
    C.ell(p[0] - d / 2, p[1] - d / 2, d, d, {fill:c});
    icon(C, p[0] - d * 0.24, p[1] - d * 0.24, d * 0.48, t, onC(c));
    var side = k < left ? 'r' : 'l', r = k < left ? k : k - left, y = Z.y + r * (rh + gy), x = side === 'r' ? Z.x + Z.w - sw : Z.x;
    C.text(side === 'r' ? x - 34 : x + sw + 8, y, 26, 22, pad2(k + 1), {font:'head', size:15, wt:800, color:c, align:'c', lh:1, caps:false, ls:0});
    hd(C, x, y, sw, rh, t, F, {align:side === 'r' ? 'l' : 'r'});
  });
};

/* rising bubbles on a diagonal (month-by-month plans) */
B.xRise = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var d = Math.min(62, Z.h / n - 6), stepX = (Z.w * 0.46) / Math.max(1, n - 1), stepY = (Z.h - d) / Math.max(1, n - 1);
  var x0 = Z.x + d / 2, y0 = Z.y + Z.h - d / 2;
  C.line(x0, y0, x0 + stepX * (n - 1) + 60, y0 - stepY * (n - 1) - 60, {color:mix(P.bg, P.text, 0.3), lw:2, dash:true});
  var F = fitHD(C, it, Z.w * 0.5 - d, 28, Math.max(24, stepY), {hMax:17, dMax:13.5, hLines:1});
  it.forEach(function(t, k){
    var cx = x0 + stepX * k, cy = y0 - stepY * k, c = col(C, k);
    C.ell(cx - d / 2 - 5, cy - d / 2 - 5, d + 10, d + 10, {fill:mix(c, P.bg, 0.75)});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    icon(C, cx - d * 0.24, cy - d * 0.24, d * 0.48, t, onC(c));
    K.pill(C, cx + d / 2 + 14, cy - 13, 74, 26, pad2(k + 1), {fill:c, color:onC(c), size:12, wt:800});
    hd(C, cx + d / 2 + 100, cy - Math.min(stepY, 64) / 2, Z.x + Z.w - (cx + d / 2 + 100), Math.max(40, Math.min(stepY + 10, 70)), t, F);
  });
};

/* horizontal pill phases on top, milestone columns below (testing timeline) */
B.xPhases = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, ph = 40;
  C.rect(Z.x, Z.y, Z.w, ph, {fill:mix(P.bg, P.text, 0.08), r:ph / 2});
  var F = fitHD(C, it, cw - 26, 44, Z.h - ph - 120, {hMax:17, dMax:13.5});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, c = col(C, k);
    C.shape(k === 0 ? 'homePlate' : 'chevron', x + (k ? -8 : 0), Z.y, cw + (k ? 8 : 0), ph, {fill:c});
    C.text(x + 12, Z.y, cw - 24, ph, (C.S.phaseWord || 'Phase') + ' ' + (k + 1), {font:'body', size:13, wt:800, color:onC(c), align:'c', va:'m', lh:1, maxLines:1});
    var cx = x + cw / 2;
    C.line(cx, Z.y + ph, cx, Z.y + ph + 34, {color:c, lw:2});
    C.ell(cx - 7, Z.y + ph + 32, 14, 14, {fill:c});
    var by = Z.y + ph + 58;
    D.card(C, x + 8, by, cw - 16, Z.y + Z.h - by, {});
    icon(C, cx - 14, by + 16, 28, t, c);
    hd(C, x + 13, by + 54, cw - 26, Z.y + Z.h - by - 62, t, F, {align:'c'});
  });
};

/* glass cards with a small glowing dot (dark glass templates) */
B.xGlass = function(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)), rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var F = fitHD(C, it, cw - 52, 60, ch - 76, {hMax:24, dMax:18});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy);
    C.rect(x, y, cw, ch, {fill:C.S.glassFill || '#FFFFFF', op:C.S.glassOp != null ? C.S.glassOp : 0.06, line:C.S.glassLine || '#FFFFFF', lop:0.35, lw:1.2, r:14});
    C.ell(x + cw - 30, y + 18, 11, 11, {fill:P.accent});
    D.glow(C, x + cw - 24.5, y + 23.5, 22, 22, P.accent, 0.35);
    C.text(x + 26, y + 20, cw - 70, 24, (C.S.cardWord || '') + (C.S.cardWord ? ' ' : '') + pad2(k + 1), {font:'body', size:13, wt:800, color:P.accent, caps:true, ls:0.12, lh:1});
    var bh = F.hh + 8 + C.measureText(t.d, cw - 52, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h;
    hd(C, x + 26, y + Math.max(56, (ch - bh) / 2 + 14), cw - 52, ch - 70, t, F);
  });
};

/* magazine: big photo + text columns (portfolio / editorial templates) */
B.xMagazine = function(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (!n) return; var P = C.P;
  var iw = Math.round(Z.w * (o.imgW || 0.46)), right = o.imgSide !== 'l';
  var ix = right ? Z.x + Z.w - iw : Z.x;
  D.imgBox(C, ix, Z.y, iw, Z.h, {r:C.S.imgR || 0});
  var tx = right ? Z.x : Z.x + iw + 40, tw = Z.w - iw - 40, gy = 18, rh = (Z.h - gy * (n - 1)) / n;
  var F = fitHD(C, it, tw, 48, rh - 10, {hMax:24, dMax:18});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy);
    if (k) C.rect(tx, y - gy / 2, tw, 1, {fill:P.line});
    hd(C, tx, y, tw, rh, t, F);
  });
};

/* stat bubbles: big coloured discs with the number inside, label below */
B.xStatDiscs = function(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, d = Math.min(cw * 0.7, Z.h * 0.58, 220);
  var vals = st.map(function(t){ return t.h || String(t.d).split(' ')[0]; }), labs = st.map(function(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); });
  var vs = C.groupFit(vals, d * 0.8, d * 0.4, {font:'head', max:56, min:20, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, Z.h - d - 30, {font:'body', max:18, min:11, lh:1.35});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = col(C, k), cy = Z.y + d / 2;
    C.ell(cx - d / 2 - 8, cy - d / 2 - 8, d + 16, d + 16, {fill:mix(c, P.bg, 0.78)});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    C.text(cx - d * 0.42, cy - d * 0.25, d * 0.84, d * 0.5, vals[k], {font:'head', size:vs, color:onC(c), align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + cw * k + 15, Z.y + d + 22, cw - 30, Z.h - d - 22, labs[k], {font:'body', size:ls, color:P.text, align:'c', lh:1.35, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
  });
};

/* vertical stepped pills (continuous improvement) */
B.xStack = function(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var gy = 10, rh = (Z.h - gy * (n - 1)) / n, indent = Math.min(40, Z.w * 0.04);
  var F = fitHD(C, it, Z.w - 260, rh * 0.45, rh - 4, {hMax:20, dMax:16, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), c = col(C, k), x = Z.x + (k % 2) * indent, w = Z.w - indent;
    C.rect(x, y, w, rh, {fill:C.S.xRows || '#FFFFFF', line:c, lw:2, r:rh / 2 > 20 ? 14 : rh / 2, shadow:C.S.xShadow});
    C.rect(x, y, 110, rh, {fill:c, r:rh / 2 > 20 ? 14 : rh / 2});
    C.text(x, y, 110, rh, (C.S.stepWord || 'Step') + ' ' + pad2(k + 1), {font:'head', size:Math.min(18, rh * 0.36), wt:800, color:onC(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    icon(C, x + 128, y + rh / 2 - 13, 26, t, c);
    hd(C, x + 172, y + Math.max(2, (rh - (F.hh + 6 + F.ds * 2.6)) / 2), w - 190, rh - 4, t, F);
  });
};
})();

/* ---------- post-render helpers (used from T.decor) ---------- */
(function(){
var D = window.DSTE, K = D && D.K2; if (!K) return;
/* offset "pop" shadow behind the slide title (and cover title) */
K.popTitle = function(C, color, dx, dy, fields){
  fields = fields || {title:1};
  for (var i = C.els.length - 1; i >= 0; i--) {
    var e = C.els[i];
    if (e.t === 'text' && e.ed && fields[e.ed.f] && !e._pop) {
      var s = {}; for (var k in e) s[k] = e[k];
      s.x += dx; s.y += dy; s.color = color; s.ed = null; s._pop = 1; e._pop = 1;
      C.els.splice(i, 0, s);
    }
  }
};
/* solid offset shadow behind every rectangle that matches (sticker cards) */
K.offsetShadow = function(C, test, color, d){
  for (var i = C.els.length - 1; i >= 0; i--) {
    var e = C.els[i];
    if (e.t === 'rect' && !e._sh && test(e)) {
      e._sh = 1; C.els.splice(i, 0, {t:'rect', x:e.x + d, y:e.y + d, w:e.w, h:e.h, fill:color, r:e.r, _sh:1});
    }
  }
};
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 13 — Graphite Loop : charcoal canvas, drawn silver loops, glass tiles, cyan light */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var P0 = {bg:'#0E0F12', text:'#E9EBEE', title:'#FFFFFF', muted:'#A2A7B0', accent:'#58D6E8', accent2:'#A9B2BE', card:'#1A1C21', line:'#30333A', onAccent:'#0B0C0E'};
var S = {
  margin:64, series:['#58D6E8', '#A9B2BE', '#3C9FB0', '#DCE1E6', '#7FC8D6', '#6B7380'],
  header:{kind:'left', tMax:46, tMin:26, sMax:17, top:52, kicker:function(C){ return C.cover.company || ''; }, kPill:'#5A5F68', kc:'#C9CED6', kSize:11, kGap:44},
  card:{kind:'solid', r:14, fill:'#191B20', line:'#2E3138', lw:1, hl:{kind:'solid', fill:'#E9EBEE', dark:false, tc:'#0E0F12', mc:'#3A3E46'}},
  icon:{kind:'square', r:12, bg:'#2A2D34', fg:'#58D6E8'},
  num:{kind:'pill', bg:'#2A2D34', fg:'#E9EBEE'},
  conc:{kind:'box', fill:'#15171B', line:'#30333A', lw:1, r:12, lc:'#58D6E8', label:'Key takeaway'},
  footer:{brand:true, pagePrefix:''}, xRows:'#17191D', hubFill:'#15171B', road:'#2A2D34', flowBar:'#22252B',
  imgFramed:true, imgR:16, glassFill:'#FFFFFF', glassOp:0.05
};
/* silver loops: a bundle of thin tilted ellipses, lighter on the inside */
function loops(C, cx, cy, s, rot){
  var cols = ['#F3F5F7', '#C9CED6', '#8D939C', '#5C626B'];
  for (var k = 0; k < 7; k++) K.loop(C, cx + k * 3, cy - k * 2, 260 * s - k * 9 * s, 96 * s - k * 3 * s, rot + k * 2.2, cols[k % 4], k === 0 ? 5 : 2.2, 0.9 - k * 0.08);
  for (var j = 0; j < 5; j++) K.loop(C, cx - 40 * s, cy + 40 * s, 180 * s - j * 8 * s, 180 * s - j * 8 * s, 0, cols[(j + 1) % 4], j === 0 ? 4 : 1.6, 0.75 - j * 0.1);
  K.spark(C, cx + 150 * s, cy - 110 * s, 18 * s, '#FFFFFF', 0.95);
}
function bg(C, kind){
  C.bg({grad:{type:'radial', stops:[[0, '#23262C'], [60, '#121317'], [100, '#08090B']]}});
  D.glow(C, 1150, 60, 320, 240, '#58D6E8', 0.07);
  if (kind !== 'cover' && kind !== 'closing' && kind !== 'quote') {
    for (var k = 0; k < 4; k++) K.loop(C, W + 10, -10, 150 - k * 14, 70 - k * 6, 30, k ? '#8D939C' : '#DCE1E6', k ? 1.4 : 2.6, 0.55);
    K.spark(C, W - 170, 118, 11, '#FFFFFF', 0.85);
  }
}
function metaLines(C, x, y, w){
  var f = C.cover, lines = [f.website, f.presenter ? f.presenter + (f.designation ? ' · ' + f.designation : '') : '', f.date].filter(Boolean);
  lines.forEach(function(l, k){ C.text(x, y + k * 24, w, 20, l, {font:'body', size:13, color:'#C9CED6', lh:1}); });
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {}, side = o.side || 'l';
  if (o.img) {
    var ix = side === 'l' ? 40 : W - 560;
    D.imgBox(C, ix, 40, 520, H - 80, {r:22});
    K.loop(C, ix + 260, H / 2, 330, 120, -24, '#DCE1E6', 3, 0.8);
    K.spark(C, ix + 470, 90, 20, '#FFFFFF', 1);
  } else if (o.center) { for (var k = 0; k < 5; k++) { K.loop(C, 150, H - 60, 240 - k * 16, 90 - k * 6, -20, k ? '#8D939C' : '#DCE1E6', k ? 1.4 : 2.6, 0.6); K.loop(C, W - 130, 70, 220 - k * 16, 80 - k * 6, 25, k ? '#8D939C' : '#DCE1E6', k ? 1.4 : 2.6, 0.6); } K.spark(C, W - 260, 150, 16, '#FFFFFF', 0.9); }
  else loops(C, side === 'l' ? 330 : W - 330, 380, 1.15, -28);
  var x = o.img ? (side === 'l' ? 620 : 64) : (side === 'l' ? 660 : 64), w = o.img ? 590 : 560;
  if (o.center) { x = 140; w = W - 280; }
  if (f.company) K.pill(C, x, 70, Math.min(260, f.company.length * 9 + 44), 30, f.company, {line:'#5A5F68', color:'#C9CED6', size:11, wt:700, caps:true, ls:0.1, ed:{f:'tf-company'}});
  C.text(x, 130, w, 320, C.s.title, {font:'head', fit:{max:74, min:34}, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.02, align:o.center ? 'c' : 'l'});
  C.rect(o.center ? W / 2 - 40 : x, 470, 80, 4, {fill:P.accent, r:2});
  if (C.s.subtitle) C.text(x, 494, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3, align:o.center ? 'c' : 'l'});
  if (!o.center) metaLines(C, x, 600, w);
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) { D.imgBox(C, W - 520, 40, 480, H - 80, {r:22}); K.loop(C, W - 280, H / 2, 300, 110, 24, '#DCE1E6', 3, 0.7); }
  else if (!o.center) loops(C, W - 300, 380, 1.1, 24);
  var x = o.center ? 140 : 64, w = o.center ? W - 280 : 640, al = o.center ? 'c' : 'l';
  C.text(x, 130, w, 260, C.s.title || 'Thank You', {font:'head', fit:{max:104, min:44}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1, align:al});
  if (C.s.subtitle) C.text(x + (o.center ? 120 : 0), 410, w - (o.center ? 240 : 0), 64, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, o.center ? 140 : 64, 540, o.center ? W - 280 : 640, {cards:true});
}
function agenda(C, v){
  var P = C.P, M = 64;
  C.text(M, 60, 700, 70, C.s.title || 'Table of Content', {font:'head', fit:{max:48, min:28}, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols), cw = cols === 2 ? 555 : 820, rh = Math.min(96, (H - 230) / per), top = 160;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = M + c * (cw + 26), y = top + r * rh;
    C.rect(x, y, cw, rh - 12, {fill:'#1A1C21', line:'#2E3138', lw:1, r:10});
    C.rect(x, y, 5, rh - 12, {fill:P.accent, r:2});
    C.text(x + 20, y, 56, rh - 12, u.pad2(k + 1) + '.', {font:'head', size:22, color:P.accent, va:'m', lh:1, ls:0});
    C.text(x + 78, y, cw - 96, rh - 12, t.h || t.d, {font:'body', fit:{max:22, min:11}, wt:700, color:'#EDEFF2', va:'m', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
  for (var k = 0; k < 4; k++) K.loop(C, W - 40, H + 10, 190 - k * 16, 80 - k * 6, -30, '#8D939C', 1.4, 0.5);
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#58D6E8'}); return; }
  loops(C, 160, 600, 0.7, -20);
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:'#58D6E8'});
}
/* ============================================================
   SIGNATURE BODIES — orbit lines, silver loops, one cyan light
   ============================================================ */
var CY = '#58D6E8', SV = '#C9CED6', SV2 = '#8D939C', INK = '#0E0F12';
function fit(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function arc(C, cx, cy, r, a0, a1, o){ C.path(K.arcCmds(cx, cy, r, a0, a1), {stroke:o.color || SV, lw:o.lw || 1.2, lop:o.op != null ? o.op : 0.6}); }
function dHeight(C, t, w, ds){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:1.36}).h : 0; }
/* heading + description; headless items (agenda) show their text as the heading */
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || '#FFFFFF', align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || '#FFFFFF', mc:o.mc || '#A2A7B0', hLines:o.hLines});
}
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dHeight(C, t, w, F.ds) : F.hs * 1.2; }
function node(C, cx, cy, d, k, hot){
  C.ell(cx - d / 2, cy - d / 2, d, d, {fill:hot ? CY : '#15171B', line:hot ? CY : SV, lw:1.4});
  C.text(cx - d / 2, cy - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.max(11, Math.round(d * 0.36)), color:hot ? INK : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
}

/* 1 — rows hung on a sweeping orbit arc (lists, agenda, vertical timeline) */
function orbitRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var a = Z.h / 2 + 10, b = Math.min(96, Z.w * 0.1), R = (a * a + b * b) / (2 * b), cy = Z.y + Z.h / 2, cx = Z.x + b - R;
  var th = Math.asin(Math.min(1, (a + 16) / (R + 30))) / Math.PI * 180;
  [-34, 0, 30].forEach(function(dr, j){ arc(C, cx, cy, R + dr, -th, th, {color:j === 1 ? SV : SV2, lw:j === 1 ? 1.8 : 1, op:j === 1 ? 0.8 : 0.45}); });
  D.glow(C, Z.x - 40, cy, 160, Z.h * 0.55, CY, 0.08);
  var rh = Z.h / n, d = Math.max(26, Math.min(44, rh - 12)), tx = Z.x + b + d / 2 + 46, tw = Z.x + Z.w - tx;
  var F = fit(C, it, tw, Math.min(34, rh * 0.5), rh - 6, {hMax:n > 6 ? 17 : 21, dMax:n > 6 ? 13 : 16, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * rh, my = y + rh / 2, dy = my - cy, nx = cx + Math.sqrt(Math.max(0, R * R - dy * dy));
    if (k) C.rect(tx, y, tw, 1, {fill:'#30333A'});
    C.line(nx + d / 2 + 4, my, tx - 12, my, {color:SV2, lw:1, op:0.7, dash:true});
    node(C, nx, my, d, k, k === 0);
    var bh = blockH(C, t, tw, F);
    block(C, tx, my - Math.min(bh, rh) / 2, tw, Math.min(rh, bh + 4), t, F, {hLines:1});
  });
}

/* 2 — two overlapping orbit circles (p2) */
function twinOrbits(C, Z, o){
  var it = C.it.slice(0, 2); if (!it.length) return;
  var ov = 70, d = Math.min(Z.h + 10, (Z.w + ov) / 2), cy = Z.y + Z.h / 2, x0 = Z.x + (Z.w - (d * 2 - ov)) / 2;
  var bw = d * 0.66, F = fit(C, it, bw, 70, d * 0.5, {hMax:24, dMax:17, noBoost:true});
  it.forEach(function(t, k){
    var cx = x0 + d / 2 + k * (d - ov), light = k === 1;
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:light ? '#E9EBEE' : '#16181C', line:light ? '#E9EBEE' : SV2, lw:1.2});
    for (var j = 1; j < 4; j++) K.loop(C, cx + j * 2, cy - j * 3, d / 2 + j * 9, d / 2 + j * 5, 12 + j * 6, j === 1 ? SV : SV2, j === 1 ? 1.6 : 1, 0.55 - j * 0.1);
    var bh = blockH(C, t, bw, F), by = cy - (bh + 44) / 2;
    C.text(cx - bw / 2, by, bw, 26, u.pad2(k + 1), {font:'head', size:20, color:light ? '#1E7F8E' : CY, align:'c', lh:1, caps:false, ls:0});
    block(C, cx - bw / 2, by + 40, bw, bh + 6, t, F, {align:'c', tc:light ? INK : '#FFFFFF', mc:light ? '#3A3E46' : '#A2A7B0'});
  });
  var ix = x0 + d - ov / 2, iy = cy - Math.sqrt(Math.max(0, d * d / 4 - (ov / 2) * (ov / 2)));
  K.spark(C, ix, iy, 16, CY, 1); D.glow(C, ix, iy, 40, 40, CY, 0.3);
}

/* 3 — row of orbit planets: each ring carries an offset eclipse halo, joined by one orbit line; text below */
function eclipseRow(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var pitch = Z.w / n, cw = pitch - 28, d = Math.min(Z.h * 0.52, 250, pitch * 0.78), cy = Z.y + d / 2 + 4;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), d * 0.7, d * 0.34, {font:'head', max:n <= 3 ? 26 : 21, min:13, maxLines:2});
  var ty = Z.y + d + 34, ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw, Z.y + Z.h - ty, {font:'body', max:n <= 3 ? 18 : 16, min:11, lh:1.4});
  C.line(Z.x - 20, cy, Z.x + Z.w + 20, cy, {color:SV2, lw:1, op:0.4});
  it.forEach(function(t, k){
    var cx = Z.x + pitch * (k + 0.5), hot = k === n - 1;
    C.ell(cx - d / 2 + 14, cy - d / 2 - 6, d, d, {line:hot ? CY : SV2, lw:1, lop:hot ? 0.6 : 0.35});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:'#15171B', line:hot ? CY : SV, lw:hot ? 2 : 1.3, lop:hot ? 1 : 0.75});
    if (hot) D.glow(C, cx + d * 0.3, cy - d * 0.42, 50, 36, CY, 0.4);
    var sp = K.pol(cx, cy, d / 2, -50); C.ell(sp[0] - 5, sp[1] - 5, 10, 10, {fill:hot ? CY : SV});
    C.text(cx - d * 0.35, cy - d * 0.3, d * 0.7, 26, u.pad2(k + 1), {font:'head', size:Math.max(16, Math.round(d * 0.09)), color:CY, align:'c', lh:1, caps:false, ls:0});
    C.text(cx - d * 0.35, cy - d * 0.3 + 34, d * 0.7, d * 0.45, t.h || t.d, {font:'head', size:hs, color:'#FFFFFF', align:'c', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    C.line(cx, cy + d / 2 + 6, cx, ty - 8, {color:hot ? CY : SV2, lw:1, op:0.6, dash:true});
    if (t.h && t.d) C.text(cx - cw / 2, ty, cw, Z.y + Z.h - ty, t.d, {font:'body', size:ds, color:'#A2A7B0', align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 4 — constellation: open grid, star nodes joined in a snake line */
function constellation(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n === 3 ? 3 : (n <= 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 800 && cols > 2) cols = 2;
  var rows = Math.ceil(n / cols), gx = 40, gy = 26, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var F = fit(C, it, cw - 6, 50, ch - 50, {hMax:cols >= 4 ? 20 : 23, dMax:cols >= 4 ? 15 : 17});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, cw - 6, F)); });
  var cell = Math.min(ch, 44 + bh + 10), gy2 = rows > 1 ? Math.min(gy + (ch - cell) * 0.8, 90) : 0, tot = rows * cell + (rows - 1) * gy2, oy = Z.y + Math.max(0, (Z.h - tot) / 2);
  var pts = [];
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols; if (r % 2) c = Math.min(cols, n - r * cols) - 1 - c;
    pts.push([Z.x + c * (cw + gx) + 12, oy + r * (cell + gy2) + 12, t, k]);
  });
  for (var j = 1; j < pts.length; j++) C.line(pts[j - 1][0], pts[j - 1][1], pts[j][0], pts[j][1], {color:SV2, lw:1, op:0.55, dash:true});
  pts.forEach(function(p){
    var x = p[0] - 12, y = p[1] - 12, t = p[2], k = p[3], hot = k === 0;
    C.rect(x + 34, y + 11, cw - 90, 1, {fill:'#30333A'});
    C.ell(p[0] - 14, p[1] - 14, 28, 28, {fill:INK, line:hot ? CY : SV, lw:1.3});
    K.spark(C, p[0], p[1], 9, hot ? CY : '#FFFFFF', 1);
    if (hot) D.glow(C, p[0], p[1], 34, 34, CY, 0.35);
    C.text(x + cw - 52, y - 2, 50, 26, u.pad2(k + 1), {font:'head', size:15, color:hot ? CY : SV, align:'r', va:'m', lh:1, caps:false, ls:0});
    block(C, x, y + 42, cw - 6, cell - 42, t, F);
  });
}

/* 5 — four quadrants around a small loop bundle (p4) */
function quadOrbit(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4) return constellation(C, Z, o);
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r = Math.min(64, Z.h * 0.16);
  C.line(Z.x, cy, cx - r - 22, cy, {color:'#3A3E46', lw:1}); C.line(cx + r + 22, cy, Z.x + Z.w, cy, {color:'#3A3E46', lw:1});
  C.line(cx, Z.y, cx, cy - r - 22, {color:'#3A3E46', lw:1}); C.line(cx, cy + r + 22, cx, Z.y + Z.h, {color:'#3A3E46', lw:1});
  for (var j = 0; j < 4; j++) K.loop(C, cx, cy, r + 16 - j * 5, r * 0.5 - j * 2, -28 + j * 30, j ? SV2 : '#DCE1E6', j ? 1.2 : 2, 0.7);
  C.ell(cx - r * 0.42, cy - r * 0.42, r * 0.84, r * 0.84, {fill:CY}); D.glow(C, cx, cy, r * 1.2, r * 1.2, CY, 0.25);
  var qw = Z.w / 2 - r - 50, qh = Z.h / 2 - 22;
  var F = fit(C, it, qw, 64, qh - 50, {hMax:26, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var right = k % 2 === 1, low = k >= 2, x = right ? cx + r + 50 : Z.x, al = right ? 'l' : 'r';
    var bh = blockH(C, t, qw, F) + 40, y = low ? cy + 22 : Math.max(Z.y, cy - 22 - bh);
    C.text(x, y, qw, 30, u.pad2(k + 1), {font:'head', size:28, color:k === 0 ? CY : SV, align:al, lh:1, caps:false, ls:0});
    block(C, x, y + 40, qw, Math.min(qh - 40, bh), t, F, {align:al});
  });
}

/* 6 — orbit dials: thin track, cyan progress arc, satellite dot (2 × 2 dial + text when narrow) */
function dial(C, cx, cy, d, pct, val, vs, t){
  var r = d / 2;
  C.ell(cx - r, cy - r, d, d, {fill:'#15171B', line:SV2, lw:1, lop:0.5});
  K.loop(C, cx, cy, r + 12, r * 0.34, -18, SV2, 1, 0.45);
  var a1 = -90 + pct * 3.6; arc(C, cx, cy, r, -90, a1, {color:CY, lw:4, op:1});
  var e = K.pol(cx, cy, r, a1); C.ell(e[0] - 7, e[1] - 7, 14, 14, {fill:CY}); D.glow(C, e[0], e[1], 26, 26, CY, 0.4);
  C.text(cx - d * 0.38, cy - d * 0.2, d * 0.76, d * 0.4, val, {font:'head', size:vs, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
}
function orbitStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var vals = st.map(function(t){ return t.h || t.d; }), labs = st.map(function(t){ return t.h ? t.d : ''; });
  function pctOf(k){ var m = String(vals[k]).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.min(100, +m[1]) : [72, 58, 84, 64][k % 4]; }
  if (Z.w / n < 230 && n > 2) {
    var cols = 2, rows = Math.ceil(n / 2), gx = 30, gy = 24, cw = (Z.w - gx) / 2, ch = (Z.h - gy * (rows - 1)) / rows, d = Math.min(ch - 30, cw * 0.46, 170);
    var vs2 = C.groupFit(vals, d * 0.72, d * 0.36, {font:'head', max:40, min:16, maxLines:1}), tw = cw - d - 40;
    var ls2 = C.groupFit(labs, tw, ch * 0.4, {font:'head', max:19, min:13, maxLines:3}), ds2 = C.groupFit(st.map(function(t){ return t.x; }), tw, ch * 0.45, {font:'body', max:15, min:11, lh:1.36});
    st.forEach(function(t, k){
      var x = Z.x + (k % 2) * (cw + gx), y = Z.y + Math.floor(k / 2) * (ch + gy), cy = y + ch / 2;
      dial(C, x + d / 2 + 12, cy, d, pctOf(k), vals[k], vs2, t);
      var lh = labs[k] ? C.measureText(labs[k], tw, {font:'head', max:ls2, min:ls2, maxLines:3}).h : 0, dh = t.x ? C.measureText(t.x, tw, {font:'body', max:ds2, min:ds2, lh:1.36}).h : 0, by = cy - (lh + dh + 6) / 2;
      if (labs[k]) C.text(x + d + 40, by, tw, lh + 2, labs[k], {font:'head', size:ls2, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:3});
      if (t.x) C.text(x + d + 40, by + lh + 6, tw, dh + 4, t.x, {font:'body', size:ds2, color:'#A2A7B0', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
    });
    return;
  }
  var cw = Z.w / n, d = Math.min(cw - 44, Z.h * 0.56, 230);
  var vs = C.groupFit(vals, d * 0.72, d * 0.36, {font:'head', max:n <= 2 ? 60 : 50, min:22, maxLines:1});
  var ls = C.groupFit(labs, cw - 30, 56, {font:'head', max:22, min:13, maxLines:2});
  var lh = 0; labs.forEach(function(l){ if (l) lh = Math.max(lh, C.measureText(l, cw - 30, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(t){ return t.x; }), cw - 30, Z.h - d - lh - 70, {font:'body', max:17, min:11, lh:1.38});
  var dh = 0; st.forEach(function(t){ if (t.x) dh = Math.max(dh, C.measureText(t.x, cw - 30, {font:'body', max:ds, min:ds, lh:1.38}).h); });
  var tot = d + 36 + lh + (dh ? dh + 10 : 0), y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = y0 + d / 2;
    dial(C, cx, cy, d, pctOf(k), vals[k], vs, t);
    var ly = cy + d / 2 + 30;
    if (labs[k]) C.text(cx - cw / 2 + 15, ly, cw - 30, lh + 2, labs[k], {font:'head', size:ls, color:'#FFFFFF', align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (t.x) C.text(cx - cw / 2 + 15, ly + lh + 10, cw - 30, Z.y + Z.h - ly - lh - 10, t.x, {font:'body', size:ds, color:'#A2A7B0', align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — ledger: oversized values on thin rules, meta text to the right */
function ledgerStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var rh = Z.h / n, vw = Math.round(Z.w * (Z.w < 800 ? 0.4 : 0.36)), tx = Z.x + vw + 40, tw = Z.x + Z.w - tx;
  var vals = st.map(function(t){ return t.h || t.d; });
  var vs = C.groupFit(vals, vw - 60, rh * 0.8, {font:'head', max:Math.min(92, rh * 0.7), min:24, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.h ? t.d : ''; }), tw, rh * 0.4, {font:'head', max:22, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x; }), tw, rh * 0.45, {font:'body', max:16, min:11, lh:1.38});
  st.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.rect(Z.x, y + rh - 1, Z.w, 1, {fill:k === n - 1 ? SV2 : '#30333A'});
    C.text(Z.x, y, 50, rh, u.pad2(k + 1), {font:'head', size:13, color:k === 0 ? CY : SV2, va:'m', lh:1, caps:false, ls:0});
    C.text(Z.x + 54, y, vw - 54, rh, vals[k], {font:'head', size:vs, color:k === 0 ? CY : '#FFFFFF', va:'m', lh:1, caps:false, ls:-0.01, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    K.loop(C, tx - 20, y + rh / 2, 6, 6, 0, SV, 1.4, 0.8);
    var lh = t.h ? C.measureText(t.d, tw, {font:'head', max:ls, min:ls, maxLines:2}).h : 0, dh = t.x ? C.measureText(t.x, tw, {font:'body', max:ds, min:ds, lh:1.38}).h : 0;
    var by = y + Math.max(4, (rh - lh - dh - (dh ? 6 : 0)) / 2);
    if (t.h) C.text(tx, by, tw, lh + 2, t.d, {font:'head', size:ls, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (t.x) C.text(tx, by + lh + 6, tw, Math.min(dh + 4, rh - lh - 10), t.x, {font:'body', size:ds, color:'#A2A7B0', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 8 — timeline on the front arc of a wide flat orbit */
function orbitTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, rx = Z.w / 2 + 30, ry = Math.min(70, Z.h * 0.16), cx = Z.x + Z.w / 2, cy = Z.y + ry + 20;
  K.loop(C, cx, cy, rx, ry, 0, SV, 1.6, 0.75); K.loop(C, cx, cy + 6, rx - 30, ry - 10, 0, SV2, 1, 0.4);
  C.ell(cx - 30, cy - 30, 60, 60, {fill:'#1A1C21', line:SV2, lw:1}); D.glow(C, cx, cy, 70, 50, CY, 0.18); K.spark(C, cx, cy, 12, CY, 1);
  var ys = [], d = 24;
  it.forEach(function(t, k){ var x = Z.x + cw * (k + 0.5), q = (x - cx) / rx; ys.push(cy + ry * Math.sqrt(Math.max(0, 1 - q * q))); });
  var ty = cy + ry + 44, F = fit(C, it, cw - 22, 40, Z.y + Z.h - ty - 6, {hMax:30, dMax:18.5, hLines:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), y = ys[k], hot = k === n - 1;
    C.line(x, y + d / 2, x, ty - 8, {color:hot ? CY : SV2, lw:1, op:0.7, dash:true});
    C.ell(x - d / 2, y - d / 2, d, d, {fill:hot ? CY : INK, line:hot ? CY : '#FFFFFF', lw:2});
    if (hot) D.glow(C, x, y, 34, 34, CY, 0.4);
    if (k) C.rect(x - cw / 2, ty, 1, Z.y + Z.h - ty, {fill:'#30333A'});
    block(C, x - cw / 2 + 11, ty, cw - 22, Z.y + Z.h - ty, t, F, {align:'c', tc:hot ? CY : '#FFFFFF'});
  });
}

/* 9 — trajectory hops: arcs leap from milestone to milestone */
function hopTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(cw / 2, Z.h * 0.26), base = Z.y + r + 12, d = 22;
  C.line(Z.x, base, Z.x + Z.w, base, {color:'#3A3E46', lw:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5);
    if (k < n - 1) { arc(C, x + cw / 2, base, cw / 2 - 2, 180, 360, {color:k === n - 2 ? CY : SV, lw:k === n - 2 ? 2 : 1.3, op:0.85}); K.spark(C, x + cw / 2, base - cw / 2 + 2, 7, k === n - 2 ? CY : '#FFFFFF', 1); }
  });
  var ty = base + 34, F = fit(C, it, cw - 24, 44, Z.y + Z.h - ty, {hMax:30, dMax:18.5, hLines:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), hot = k === n - 1;
    C.ell(x - d / 2, base - d / 2, d, d, {fill:hot ? CY : INK, line:hot ? CY : '#FFFFFF', lw:2});
    C.ell(x - 4, base - 4, 8, 8, {fill:hot ? INK : '#FFFFFF'});
    block(C, x - cw / 2 + 12, ty, cw - 24, Z.y + Z.h - ty, t, F, {align:'c', tc:hot ? CY : '#FFFFFF'});
  });
}

D.register({
  id:'graphite', name:'Graphite Loop', tag:'Charcoal & silver · agency', desc:'Charcoal canvas, hand-drawn silver loops, glass tiles and a cool cyan light.',
  fonts:{head:'Sora', body:'Inter', headW:700, bodyW:400, headLh:1.05, headLs:0, gf:'Sora:wght@500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Silver loops',{}), V('v2','Image left + loops',{img:true, side:'l'}), V('v3','Title left + image',{img:true, side:'r'}), V('n2','Centered title',{center:true})],
    closing:[V('v1','Loops right',{}), V('v2','Image right',{img:true}), V('n2','Centered',{center:true})],
    agenda:[V('v1','Content tiles',{}), V('n2','Orbit list',{body:orbitRows}), V('v2','Orbit list + image right',{body:orbitRows, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Statement',{body:'text'}), V('n2','Statement in glass card',{body:'text', box:true}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Twin orbits',{body:twinOrbits}), V('n2','Eclipse rings',{body:eclipseRow}), V('n3','Orbit list',{body:orbitRows}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Orbit list + image right',{body:orbitRows, img:'right'}), V('n4','Graphite panels',{body:'duo'})],
    p3:[V('v1','Eclipse rings',{body:eclipseRow}), V('n2','Orbit list',{body:orbitRows}), V('n3','Arc segments',{body:'xArc'}), V('n4','Constellation',{body:constellation}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Constellation + image right',{body:constellation, img:'right', cols:1}), V('n5','Numbered columns',{body:'numbered', lineC:'#30333A'})],
    p4:[V('v1','Orbit quadrants',{body:quadOrbit}), V('n2','Eclipse rings',{body:eclipseRow}), V('n3','Arc segments',{body:'xArc'}), V('n4','Constellation',{body:constellation}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Quadrants + image right',{body:quadOrbit, img:'right'}), V('n5','Graphite grid',{body:'grid'})],
    p56:[V('v1','Constellation',{body:constellation}), V('n2','Orbit list',{body:orbitRows}), V('n3','Arc segments',{body:'xArc'}), V('v2','Image left + orbit list',{body:orbitRows, img:'left'}), V('v3','Constellation + image right',{body:constellation, img:'right'}), V('n4','Graphite grid',{body:'grid'})],
    p78:[V('v1','Constellation',{body:constellation}), V('n2','Orbit list',{body:orbitRows}), V('v2','Constellation + image right',{body:constellation, img:'right'}), V('n3','Two-column tiles',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Orbit dials',{body:orbitStats}), V('n2','Ledger',{body:ledgerStats}), V('v2','Image left + ledger',{body:ledgerStats, img:'left'}), V('v3','Orbit dials + image right',{body:orbitStats, img:'right'}), V('n3','Big numbers',{body:'statCols'})],
    timeline:[V('v1','Orbit timeline',{body:orbitTimeline}), V('n2','Trajectory hops',{body:hopTimeline}), V('v2','Orbit list + image right',{body:orbitRows, img:'right'}), V('v3','Image left + orbit list',{body:orbitRows, img:'left'}), V('n3','Vertical timeline',{body:'tlV'})],
    quote:[V('v1','Loop quote',{foot:false}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 14 — Grid Paper Classroom : blue grid paper, red + sunflower blocks, navy outlines, drawn geometry doodles */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#1E2A47', RED = '#E8453C', SUN = '#FBC25A', SKY = '#6FA8F5', LEAF = '#7CC96A';
var P0 = {bg:'#F4F8FC', text:'#1E2A47', title:'#E8453C', muted:'#3E4A66', accent:'#E8453C', accent2:'#FBC25A', card:'#FBC25A', line:'#1E2A47', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[RED, SUN, SKY, LEAF, '#B384F0', '#F28BB6'],
  header:{kind:'left', tMax:44, tMin:26, sMax:17, sc:'#3E4A66', top:48},
  card:{kind:'outline', r:6, fill:'#FFE3A6', line:NAVY, lw:2.5, dark:false, tc:NAVY, mc:'#2E3A58', hl:{kind:'solid', fill:RED, dark:true, tc:'#FFFFFF', mc:'#FFE9E7'}},
  icon:{kind:'circle', bg:NAVY, fg:'#FFFFFF'},
  num:{kind:'circle', bg:RED, fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#FFFFFF', line:NAVY, lw:2, r:6, lc:RED, label:'Remember'},
  footer:{brand:false, page:true, color:NAVY}, xRows:'#FFFFFF', hubFill:'#FFE3A6', road:NAVY, flowBar:'#DCE6F2',
  imgFramed:true, imgR:8, stepWord:'Step'
};
/* ---- drawn doodles (all original shapes, navy outline + flat fill) ---- */
function setSquare(C, x, y, s, rot){
  var r = rot * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r);
  function T(p){ return [x + p[0] * cs - p[1] * sn, y + p[0] * sn + p[1] * cs]; }
  K.poly(C, [[0, 0], [s, s], [0, s]].map(T), {fill:SKY, stroke:NAVY, lw:3});
  K.poly(C, [[s * 0.2, s * 0.45], [s * 0.55, s * 0.8], [s * 0.2, s * 0.8]].map(T), {fill:'#F4F8FC', stroke:NAVY, lw:2.5});
}
function protractor(C, cx, cy, r){
  K.seg(C, cx, cy, r * 0.55, r, 180, 360, {fill:'#FFFFFF', stroke:NAVY, lw:3});
  for (var a = 190; a < 360; a += 20) { var p = K.pol(cx, cy, r, a), q = K.pol(cx, cy, r * 0.84, a); C.line(p[0], p[1], q[0], q[1], {color:NAVY, lw:2}); }
}
function pencil(C, x, y, len, rot){
  var r = rot * Math.PI / 180, cs = Math.cos(r), sn = Math.sin(r), w = 22;
  function T(p){ return [x + p[0] * cs - p[1] * sn, y + p[0] * sn + p[1] * cs]; }
  K.poly(C, [[0, 0], [len, 0], [len, w], [0, w]].map(T), {fill:SUN, stroke:NAVY, lw:2.5});
  K.poly(C, [[len, 0], [len + 30, w / 2], [len, w]].map(T), {fill:'#F6D9B0', stroke:NAVY, lw:2.5});
  K.poly(C, [[len + 20, w / 2 - 4], [len + 30, w / 2], [len + 20, w / 2 + 4]].map(T), {fill:NAVY});
  K.poly(C, [[-16, 0], [0, 0], [0, w], [-16, w]].map(T), {fill:'#F28BB6', stroke:NAVY, lw:2.5});
}
function cube(C, x, y, s){
  K.poly(C, [[x, y + s * 0.3], [x + s * 0.5, y], [x + s, y + s * 0.3], [x + s * 0.5, y + s * 0.6]], {fill:'#B7E3A8', stroke:NAVY, lw:2.5});
  K.poly(C, [[x, y + s * 0.3], [x + s * 0.5, y + s * 0.6], [x + s * 0.5, y + s * 1.1], [x, y + s * 0.8]], {fill:LEAF, stroke:NAVY, lw:2.5});
  K.poly(C, [[x + s, y + s * 0.3], [x + s * 0.5, y + s * 0.6], [x + s * 0.5, y + s * 1.1], [x + s, y + s * 0.8]], {fill:'#5AA84A', stroke:NAVY, lw:2.5});
}
function ball(C, cx, cy, r){ C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:RED, line:NAVY, lw:3}); C.ell(cx - r * 0.5, cy - r * 0.6, r * 0.45, r * 0.3, {fill:'#FFFFFF', op:0.6}); }
function spark(C, x, y, r){ K.spark(C, x, y, r, NAVY, 1); }
function bg(C, kind){
  C.bg({fill:'#F4F8FC'});
  K.grid(C, 36, '#C9DCEC', 0.9);
  var odd = (C.i || 0) % 2 === 1;
  if (kind === 'cover' || kind === 'closing') return;
  /* one bold red wedge on alternate sides + a couple of doodles in the free corner */
  if (odd) { K.poly(C, [[W, H - 150], [W, H], [W - 200, H]], {fill:RED}); }
  else { K.poly(C, [[0, H - 150], [0, H], [200, H]], {fill:RED}); ball(C, 46, H - 40, 18); }
  spark(C, W - 110, 64, 13); spark(C, W - 70, 120, 8);
}
function tag(C, x, y, w, h, text, ed){
  C.rect(x + 5, y + 5, w, h, {fill:NAVY});
  C.rect(x, y, w, h, {fill:SUN, line:NAVY, lw:2.5});
  C.text(x + 12, y, w - 24, h, text, {font:'body', fit:{max:18, min:11}, wt:700, color:NAVY, align:'c', va:'m', ed:ed, maxLines:2, lh:1.2});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) {
    C.rect(W - 548, 44, 510, H - 88, {fill:NAVY}); D.imgBox(C, W - 556, 36, 510, H - 88, {r:0});
    C.rect(W - 556, 36, 510, H - 88, {line:NAVY, lw:3});
    pencil(C, W - 600, H - 120, 180, -18); spark(C, 80, 80, 18); setSquare(C, 60, H - 200, 120, 0);
    var te = C.text(64, 150, 620, 300, C.s.title, {font:'head', fit:{max:78, min:36}, color:RED, ed:{f:'title'}, maxLines:4, va:'b', lh:1.02});
    if (C.s.subtitle) tag(C, 64, 480, 560, 66, C.s.subtitle, {f:'subtitle'});
    if (f.presenter || f.date) C.text(64, 580, 600, 24, [f.presenter, f.date].filter(Boolean).join('  ·  '), {font:'body', size:15, wt:700, color:P.muted});
    return;
  }
  /* centred title with doodles around the edges */
  setSquare(C, 70, 70, 150, 0); protractor(C, W - 150, 170, 110); pencil(C, W - 360, 70, 150, 20);
  cube(C, W - 200, H - 190, 110); ball(C, 170, H - 120, 46); K.poly(C, [[0, H - 170], [0, H], [260, H]], {fill:RED});
  spark(C, 330, 120, 16); spark(C, W - 330, H - 90, 14); spark(C, 540, H - 70, 10);
  C.text(160, 190, W - 320, 220, C.s.title, {font:'head', fit:{max:92, min:40}, color:RED, ed:{f:'title'}, maxLines:3, align:'c', va:'b', lh:1.02});
  if (C.s.subtitle) tag(C, W / 2 - 300, 440, 600, 62, C.s.subtitle, {f:'subtitle'});
  if (f.presenter || f.company) C.text(200, 540, W - 400, 24, [f.company, f.presenter, f.date].filter(Boolean).join('  ·  '), {font:'body', size:15, wt:700, color:P.muted, align:'c'});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) { C.rect(52, 52, 470, H - 104, {fill:NAVY}); D.imgBox(C, 44, 44, 470, H - 104, {r:0}); C.rect(44, 44, 470, H - 104, {line:NAVY, lw:3}); }
  else { protractor(C, 170, 190, 110); setSquare(C, W - 220, 60, 150, 0); cube(C, 90, H - 200, 100); ball(C, W - 140, H - 120, 44); }
  var x = o.img ? 580 : 160, w = o.img ? W - 640 : W - 320, al = o.img ? 'l' : 'c';
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:100, min:44}, color:RED, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:1});
  if (C.s.subtitle) C.text(x, 370, w, 64, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, x, 480, w, {cards:true});
  spark(C, W / 2 + 360, 110, 16);
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:RED}); return; }
  C.rect(206, 146, W - 400, H - 280, {fill:NAVY}); C.rect(200, 140, W - 400, H - 280, {fill:'#FFE3A6', line:NAVY, lw:3});
  D.B.quote(C, {x:250, y:170, w:W - 500, h:H - 340}, {qc:RED});
  setSquare(C, 40, H - 170, 120, 0); spark(C, W - 120, 90, 18);
}
/* ============================================================
   SIGNATURE BODIES — sticker notes, tape, pins, ruled paper, pencils
   ============================================================ */
var INK2 = '#2E3A58', RULE = '#C3D5EA', HOLE = '#DCE6F2';
var TINT = ['#FFE3A6', '#CFE2FF', '#D8F2CF', '#FFD6D3', '#E8DAFF', '#FFD9EA'];
function tint(k){ return TINT[k % TINT.length]; }
function ser(k){ return S.series[k % S.series.length]; }
function fitC(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
/* sticker card: navy outline; decor() adds the solid offset shadow */
function sticker(C, x, y, w, h, fill, r){ C.rect(x, y, w, h, {fill:fill, line:NAVY, lw:2.5, r:r == null ? 6 : r}); }
function tape(C, cx, y, w, rot){ C.shape('parallelogram', cx - w / 2, y - 13, w, 26, {fill:'#FFF6D6', op:0.82, rot:rot == null ? -3 : rot}); }
function pin(C, cx, cy, c){ C.ell(cx - 11, cy - 11, 22, 22, {fill:c, line:NAVY, lw:2.2}); C.ell(cx - 6, cy - 7, 7, 5, {fill:'#FFFFFF', op:0.75}); }
/* text with a navy "pop" shadow */
function pop(C, x, y, w, h, text, o, dd){
  var q = {}; for (var k in o) q[k] = o[k]; q.color = NAVY; q.ed = null; dd = dd || 3;
  C.text(x + dd, y + dd, w, h, text, q); return C.text(x, y, w, h, text, o);
}
function arrowHead(C, fx, fy, tx, ty, s, c){
  var a = Math.atan2(ty - fy, tx - fx), b = 0.5;
  K.poly(C, [[tx, ty], [tx - s * Math.cos(a - b), ty - s * Math.sin(a - b)], [tx - s * Math.cos(a + b), ty - s * Math.sin(a + b)]], {fill:c});
}
function dH(C, t, w, ds, lh){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:lh || 1.36}).h : 0; }
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dH(C, t, w, F.ds) : F.hs * 1.2 * Math.min(2, C.measureText(t.d, w, {font:'head', max:F.hs, min:F.hs}).n); }
/* heading + description; a headless item shows its text as the heading */
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || NAVY, align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || NAVY, mc:o.mc || INK2, hLines:o.hLines});
}

/* 1 — two taped sticky notes, staggered, joined by a drawn arrow (p2) */
function stickyPair(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var mid = n > 1 ? Math.min(110, Z.w * 0.12) : 0, nw = n > 1 ? (Z.w - mid) / 2 : Math.min(Z.w, 620), st = Math.min(34, Z.h * 0.08), nh = Z.h - st - 12;
  var pad = Math.min(34, nw * 0.08), d = 58, F = fitC(C, it, nw - pad * 2, 84, nh - pad * 2 - d - 22, {hMax:30, dMax:21, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * (nw + mid), y = Z.y + (k ? st : 0) + 6;
    sticker(C, x, y, nw, nh, k ? '#CFE2FF' : '#FFE3A6', 4);
    tape(C, x + nw / 2, y, 130, k ? 3 : -3);
    C.ell(x + pad, y + pad, d, d, {fill:k ? NAVY : RED, line:NAVY, lw:2.5});
    C.text(x + pad, y + pad, d, d, String(k + 1), {font:'head', size:32, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    var by = y + pad + d + 22;
    block(C, x + pad, by, nw - pad * 2, y + nh - pad - by, t, F);
  });
  if (n > 1) {
    var x1 = Z.x + nw + 12, x2 = Z.x + nw + mid - 8, y1 = Z.y + Z.h * 0.3, y2 = y1 + 40;
    C.path([['M', x1, y1], ['C', x1 + mid * 0.45, y1 - 70, x2 - mid * 0.1, y2 - 60, x2, y2]], {stroke:NAVY, lw:3});
    arrowHead(C, x2 - mid * 0.1, y2 - 60, x2, y2, 18, NAVY);
    K.spark(C, Z.x + nw + mid / 2, Z.y + Z.h * 0.72, 14, RED, 1);
  }
}

/* 2 — T-chart drawn on a taped white sheet (p2) */
function tChart(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return stickyPair(C, Z, o);
  sticker(C, Z.x, Z.y + 8, Z.w, Z.h - 8, '#FFFFFF', 4);
  tape(C, Z.x + 40, Z.y + 8, 110, -8); tape(C, Z.x + Z.w - 40, Z.y + 8, 110, 8);
  var hh = Math.min(100, Z.h * 0.27), cx = Z.x + Z.w / 2, bar = Z.y + 8 + hh, cw = Z.w / 2 - 70;
  for (var y = bar + 44; y < Z.y + Z.h - 14; y += 36) C.rect(Z.x + 16, y, Z.w - 32, 1.2, {fill:RULE});
  C.rect(Z.x + 28, bar, Z.w - 56, 5, {fill:NAVY, r:2}); C.rect(cx - 2.5, bar, 5, Z.y + Z.h - 22 - bar, {fill:NAVY, r:2});
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 50, hh - 26, {font:'head', max:36, min:16, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw, Z.y + Z.h - bar - 60, {font:'body', max:23, min:12, lh:1.5});
  it.forEach(function(t, k){
    var x = k ? cx + 40 : Z.x + 30, c = k ? SKY : RED;
    C.ell(x, Z.y + 8 + (hh - 40) / 2, 40, 40, {fill:c, line:NAVY, lw:2.5});
    C.text(x, Z.y + 8 + (hh - 40) / 2, 40, 40, String(k + 1), {font:'head', size:22, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    pop(C, x + 54, Z.y + 12, cw - 50, hh - 10, t.h || t.d, {font:'head', size:hs, color:RED, va:'m', lh:1.05, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2}, 2);
    if (t.h && t.d) C.text(x + 6, bar + 30, cw, Z.y + Z.h - bar - 50, t.d, {font:'body', size:ds, color:NAVY, lh:1.5, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 3 — pin board: staggered sticky cards pinned and strung together with red yarn */
function pinBoard(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = n <= 4 ? (Z.w < 800 && n === 4 ? 2 : n) : 3;
  var rows = Math.ceil(n / cols), gx = cols > 3 ? 24 : 34, gy = 36, stag = rows > 1 ? 10 : 22;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1) - stag - 8) / rows, pad = cw < 240 ? 18 : 24, nh = rows > 1 ? 34 : 44;
  var F = fitC(C, it, cw - pad * 2, 56, ch - pad * 2 - nh - 16, {hMax:rows > 1 ? 20 : 23, dMax:rows > 1 ? 15 : 17});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, cw - pad * 2, F)); });
  var pins = [];
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + 8 + r * (ch + gy) + (c % 2 ? stag : 0);
    sticker(C, x, y, cw, ch, tint(k), 4);
    pins.push([x + cw / 2, y + 2, r, k]);
    var top = y + pad + 10 + Math.max(0, (ch - pad * 2 - 10 - nh - 14 - bh) * 0.35);
    pop(C, x + pad, top, cw - pad * 2, nh, u.pad2(k + 1), {font:'head', size:nh - 4, color:RED, lh:1, caps:false, ls:0}, 2);
    block(C, x + pad, top + nh + 14, cw - pad * 2, y + ch - pad - (top + nh + 14), t, F);
  });
  for (var r = 0; r < rows; r++) {
    var row = pins.filter(function(p){ return p[2] === r; }); if (row.length < 2) continue;
    var pts = [[row[0][0] - cw * 0.5 - 10, row[0][1] - 14]];
    row.forEach(function(p, j){ if (j) pts.push([(row[j - 1][0] + p[0]) / 2, (row[j - 1][1] + p[1]) / 2 + 22]); pts.push([p[0], p[1]]); });
    pts.push([row[row.length - 1][0] + cw * 0.5 + 10, row[row.length - 1][1] - 14]);
    K.curve(C, pts, {stroke:RED, lw:2.5});
  }
  pins.forEach(function(p){ pin(C, p[0], p[1], ser(p[3] + 1)); });
}

/* 4 — worksheet: white binder sheet, punched holes, red margin, numbered ruled lines */
function worksheet(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  sticker(C, Z.x, Z.y, Z.w, Z.h, '#FFFFFF', 4);
  var ml = Z.x + 62; C.rect(ml, Z.y + 3, 2, Z.h - 6, {fill:RED, op:0.75});
  [0.18, 0.5, 0.82].forEach(function(f){ C.ell(Z.x + 18, Z.y + Z.h * f - 11, 22, 22, {fill:HOLE, line:'#9FB3C8', lw:1.5}); });
  var cols = n > 5 && (Z.w > 760 || n > 6) ? 2 : 1, per = Math.ceil(n / cols), padT = 18, padB = 14, gx = 44;
  var ax = ml + 26, aw = Z.x + Z.w - 26 - ax, cw = (aw - gx * (cols - 1)) / cols, rh = (Z.h - padT - padB) / per, nw = Math.min(50, rh * 0.8);
  var F = fitC(C, it, cw - nw - 8, Math.min(34, rh * 0.42), rh - 12, {hMax:per <= 3 ? 26 : (it.some(function(t){ return t.h; }) ? 22 : 25), dMax:per <= 3 ? 19 : 16, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = ax + c * (cw + gx), y = Z.y + padT + r * rh;
    C.rect(x, y + rh - 3, cw, 1.5, {fill:RULE});
    C.text(x, y, nw, rh - 4, (k + 1) + '.', {font:'head', size:Math.min(30, Math.max(18, rh * 0.42)), color:RED, va:'m', lh:1, caps:false, ls:0});
    var bh = Math.min(rh - 6, blockH(C, t, cw - nw - 8, F));
    block(C, x + nw, y + (rh - 3 - bh) / 2, cw - nw - 8, bh + 4, t, F, {hLines:1});
  });
}

/* 5 — index flashcards: numbered sticker dot, red header rule, blue ruled lines under the text */
function flashcards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))); if (Z.w < 800 && cols > 2) cols = 2;
  var rows = Math.ceil(n / cols), gx = cols > 3 ? 20 : 26, gy = rows > 2 ? 20 : 28, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1) - 6) / rows;
  var pad = cw < 240 ? 16 : 22, head = Math.min(rows > 1 ? 58 : 76, ch * 0.34), bd = Math.min(36, head - 12);
  var bare = !it.some(function(t){ return t.h; });
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - pad * 2 - bd - 12, head - 10, {font:'head', max:cols >= 4 ? 20 : 26, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return bare ? '' : t.d; }), cw - pad * 2, ch - head - 20, {font:'body', max:rows > 1 ? (cols > 3 ? 17 : 19) : 21, min:11, lh:1.55});
  var bs = C.groupFit(it.map(function(t){ return bare ? t.d : ''; }), cw - pad * 2, ch - head - 24, {font:'head', max:30, min:13, maxLines:2});
  var need = 0; it.forEach(function(t){ need = Math.max(need, bare ? C.measureText(t.d, cw - pad * 2, {font:'head', max:bs, min:bs, lh:1.25, maxLines:2}).h : dH(C, t, cw - pad * 2, ds, 1.55)); });
  var ch2 = Math.min(ch, Math.max(ch * 0.62, head + 12 + need) + (bare ? bs * 1.25 : ds * 1.55) * 3), oy = Z.y + Math.max(0, (Z.h - (ch2 * rows + gy * (rows - 1))) / 2); ch = ch2;
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = oy + r * (ch + gy), c1 = ser(k);
    sticker(C, x, y, cw, ch, '#FFFFFF', 3);
    C.ell(x + pad, y + (head - bd) / 2, bd, bd, {fill:c1, line:NAVY, lw:2});
    C.text(x + pad, y + (head - bd) / 2, bd, bd, u.pad2(k + 1), {font:'head', size:Math.round(bd * 0.42), color:K.on(c1), align:'c', va:'m', lh:1, caps:false, ls:0});
    if (!bare && t.h) C.text(x + pad + bd + 12, y + 4, cw - pad * 2 - bd - 12, head - 8, t.h, {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.rect(x + 3, y + head, cw - 6, 2, {fill:RED, op:0.8});
    var ty = y + head + 12, step = (bare ? bs * 1.25 : ds * 1.55);
    for (var ly = ty + step; ly < y + ch - 6; ly += step) C.rect(x + 3, ly + 1, cw - 6, 1, {fill:RULE});
    if (bare) C.text(x + pad, ty, cw - pad * 2, ch - head - 18, t.d, {font:'head', size:bs, color:NAVY, lh:1.25, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    else C.text(x + pad, ty, cw - pad * 2, ch - head - 18, t.d, {font:'body', size:ds, color:INK2, lh:1.55, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 6 — ruler stats: a big wooden ruler, value stickers pointing at their marks */
function statsOf(C){ return C.it.slice(0, 4).map(function(t){ return {t:t, v:t.h || t.d, l:t.h ? t.d : '', x:t.x || ''}; }); }
function rulerStats(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var cw = Z.w / n, vh = Math.min(124, Z.h * 0.3), rh = 54, lw = cw - 24;
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 60, {font:'head', max:24, min:13, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Z.h - vh - rh - 64 - lh - 10, {font:'body', max:18, min:11, lh:1.4});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var tot = vh + 34 + rh + 30 + lh + (dh ? dh + 8 : 0), ry = Z.y + Math.max(0, (Z.h - tot) / 2) + vh + 34;
  sticker(C, Z.x, ry, Z.w, rh, SUN, 4);
  for (var i = 0, tx = Z.x + 16; tx < Z.x + Z.w - 8; tx += 16, i++) C.rect(tx, ry, 2, i % 5 === 4 ? 24 : 12, {fill:NAVY});
  var sw = Math.min(cw - 24, 270);
  var vs = C.groupFit(st.map(function(s){ return s.v; }), sw - 30, vh - 24, {font:'head', max:62, min:20, maxLines:1});
  var ly = ry + rh + 30;
  st.forEach(function(s, k){
    var cx = Z.x + cw * (k + 0.5), x = cx - sw / 2, y = ry - 30 - vh, c = ser(k), t = s.t;
    sticker(C, x, y, sw, vh, '#FFFFFF', 6);
    tape(C, x + 26, y + 4, 60, -30);
    K.poly(C, [[cx - 13, y + vh + 2], [cx + 13, y + vh + 2], [cx, ry - 3]], {fill:NAVY});
    pop(C, x + 15, y, sw - 30, vh, s.v, {font:'head', size:vs, color:RED, align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1}, 3);
    C.ell(cx - 12, ry + rh / 2 - 12, 24, 24, {fill:c, line:NAVY, lw:2.5});
    if (s.l) C.text(cx - lw / 2, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(cx - lw / 2, ly + lh + 8, lw, Z.y + Z.h - ly - lh - 8, s.x, {font:'body', size:ds, color:INK2, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — report card: spiral-bound sheet, teacher's red circled marks on the right */
function reportCard(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var top = Z.y + 16; sticker(C, Z.x, top, Z.w, Z.h - 16, '#FFFFFF', 4);
  for (var sx = Z.x + 34; sx < Z.x + Z.w - 20; sx += 44) { C.ell(sx - 6, top + 14, 12, 12, {fill:HOLE, line:'#9FB3C8', lw:1.2}); C.ell(sx - 4, top - 12, 8, 34, {line:NAVY, lw:2.2}); }
  var y0 = top + 40, rh = (Z.y + Z.h - 14 - y0) / n, gw = Math.min(290, Z.w * 0.34), ml = Z.x + 58;
  C.rect(ml, top + 34, 2, Z.h - 16 - 38, {fill:RED, op:0.75});
  var tx = ml + 26, tw = Z.x + Z.w - gw - 30 - tx;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), gw - 70, rh * 0.62, {font:'head', max:56, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), tw, rh * 0.46, {font:'head', max:26, min:13, maxLines:1});
  var ds = C.groupFit(st.map(function(s){ return s.x; }), tw, rh * 0.4, {font:'body', max:17, min:11, lh:1.35});
  st.forEach(function(s, k){
    var y = y0 + k * rh, t = s.t, gx = Z.x + Z.w - gw / 2 - 24, cy = y + rh / 2;
    if (k < n - 1) C.rect(Z.x + 12, y + rh - 1, Z.w - 24, 1.5, {fill:RULE});
    var lh = s.l ? ls * 1.2 : 0, dh = s.x ? C.measureText(s.x, tw, {font:'body', max:ds, min:ds, lh:1.35}).h : 0, by = cy - (lh + dh + (dh ? 6 : 0)) / 2;
    if (s.l) C.text(tx, by, tw, lh + 2, s.l, {font:'head', size:ls, color:NAVY, lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    if (s.x) C.text(tx, by + lh + 6, tw, dh + 4, s.x, {font:'body', size:ds, color:INK2, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
    var rx = gw / 2 - 12, ry = Math.min(rh * 0.42, 50);
    K.loop(C, gx, cy, rx, ry, -4, RED, 3, 0.95); K.loop(C, gx + 3, cy - 2, rx + 5, ry - 3, 3, RED, 1.5, 0.7);
    C.text(gx - rx + 20, cy - ry, rx * 2 - 40, ry * 2, s.v, {font:'head', size:vs, color:RED, align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}

/* 8 — pencil timeline: milestones taped above and below a long drawn pencil */
function pencilTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var ph = 46, py = Z.y + Z.h / 2 - ph / 2, x0 = Z.x + 18, x1 = Z.x + Z.w - 50;
  C.rect(x0 - 18, py, 22, ph, {fill:'#F28BB6', line:NAVY, lw:2.5, r:6});
  C.rect(x0 + 4, py, 22, ph, {fill:'#C9D3E0', line:NAVY, lw:2.5});
  C.rect(x0 + 26, py, x1 - x0 - 26, ph, {fill:SUN, line:NAVY, lw:2.5});
  C.rect(x0 + 26, py + ph / 2 - 2, x1 - x0 - 26, 4, {fill:'#E9A93A'});
  K.poly(C, [[x1, py], [x1 + 46, py + ph / 2], [x1, py + ph]], {fill:'#F6D9B0', stroke:NAVY, lw:2.5});
  K.poly(C, [[x1 + 30, py + ph / 2 - 7], [x1 + 46, py + ph / 2], [x1 + 30, py + ph / 2 + 7]], {fill:NAVY});
  var cw = (x1 - x0 - 40) / n, w = Math.min(n > 1 ? cw * 1.86 - 18 : 420, 330), avail = py - 34 - Z.y, pad = 16;
  var F = fitC(C, it, w - pad * 2, 44, avail - pad * 2 - 6, {hMax:30, dMax:19, hLines:1});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, w - pad * 2, F)); });
  var ch = Math.min(avail, bh + pad * 2 + 8);
  it.forEach(function(t, k){
    var cx = x0 + 40 + cw * (k + 0.5), up = k % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - w, cx - w / 2));
    var y = up ? py - 34 - ch : py + ph + 34;
    C.rect(cx - 1.5, up ? y + ch : py + ph, 3, 34, {fill:NAVY});
    C.ell(cx - 10, py + ph / 2 - 10, 20, 20, {fill:ser(k) === SUN ? RED : ser(k), line:NAVY, lw:2.5});
    sticker(C, x, y, w, ch, tint(k), 4);
    tape(C, x + w / 2, y + 2, 70, k % 2 ? 4 : -4);
    block(C, x + pad, y + pad + 4, w - pad * 2, ch - pad * 2, t, F, {tc:RED});
  });
}

/* 9 — calendar pages: ring-bound pages with a coloured header band and a big date number */
function calendarPages(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var rows = (Z.w / n < 170 && n > 3) ? 2 : 1, cols = Math.ceil(n / rows), gx = 22, gy = 30;
  var stag = rows === 1 ? 14 : 0, pw = (Z.w - gx * (cols - 1)) / cols, ph = (Z.h - gy * (rows - 1) - stag - 12) / rows;
  var band = Math.min(rows > 1 ? 56 : 74, ph * 0.3), num = Math.min(rows > 1 ? 36 : 54, ph * 0.2);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), pw - 24, band - 14, {font:'head', max:26, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), pw - 28, ph - band - num - 44, {font:'body', max:rows > 1 ? 15 : 17, min:11, lh:1.4});
  var need = 0; it.forEach(function(t){ if (t.h) need = Math.max(need, dH(C, t, pw - 28, ds, 1.4)); });
  var ph2 = Math.min(ph, band + num + 40 + need + 24), oy = Z.y + 12 + Math.max(0, (Z.h - 12 - stag - (ph2 * rows + gy * (rows - 1))) / 2); ph = ph2;
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (pw + gx), y = oy + r * (ph + gy) + (c % 2 ? stag : 0), c1 = ser(k);
    sticker(C, x, y, pw, ph, '#FFFFFF', 4);
    C.rect(x + 1.5, y + 1.5, pw - 3, band, {fill:c1, r:4});
    C.rect(x + 1.5, y + band - 2, pw - 3, 3.5, {fill:NAVY});
    [0.28, 0.72].forEach(function(f){ C.ell(x + pw * f - 5, y + 10, 10, 10, {fill:HOLE}); C.rect(x + pw * f - 4, y - 14, 8, 30, {fill:'#FFFFFF', line:NAVY, lw:2, r:4}); });
    C.text(x + 12, y + 18, pw - 24, band - 20, t.h || t.d, {font:'head', size:hs, color:K.on(c1), align:'c', va:'m', lh:1.05, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    pop(C, x, y + band + 12, pw, num, u.pad2(k + 1), {font:'head', size:num, color:RED, align:'c', lh:1, caps:false, ls:0}, 2);
    if (t.h && t.d) C.text(x + 14, y + band + num + 26, pw - 28, ph - band - num - 34, t.d, {font:'body', size:ds, color:INK2, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
D.register({
  id:'classroom', name:'Grid Paper Classroom', tag:'Illustrated · education', desc:'Blue grid paper, bold red titles with a navy pop shadow, sunflower sticker boxes and hand-drawn geometry doodles.',
  fonts:{head:'Lilita One', body:'Nunito', headW:400, bodyW:600, headLh:1.02, headLs:0.01, gf:'Lilita One|Nunito:wght@400;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  decor:function(C, kind){
    K.popTitle(C, NAVY, 3, 3, {title:1});
    K.offsetShadow(C, function(e){ return e.line === NAVY && e.fill && e.w > 60 && e.h > 40; }, NAVY, 6);
  },
  variants:{
    cover:[V('v1','Doodle frame',{}), V('v2','Title + framed image',{img:true})],
    closing:[V('v1','Doodle frame',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Worksheet',{body:worksheet}), V('n2','Flashcards',{body:flashcards}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Sticker box',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Sticker + image right',{body:'text', box:true, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Sticky notes',{body:stickyPair}), V('n2','T-chart',{body:tChart}), V('n3','Flashcards',{body:flashcards}), V('v2','Image left + sticky notes',{body:stickyPair, img:'left'}), V('v3','T-chart + image right',{body:tChart, img:'right'}), V('n4','Two sticker boxes',{body:'duo'})],
    p3:[V('v1','Pin board',{body:pinBoard}), V('n2','Calendar pages',{body:calendarPages}), V('n3','Flashcards',{body:flashcards}), V('n4','Step pills',{body:'xStack'}), V('v2','Image left + worksheet',{body:worksheet, img:'left'}), V('v3','Pin board + image right',{body:pinBoard, img:'right'}), V('n5','Three sticker cards',{body:'cols'})],
    p4:[V('v1','Flashcards',{body:flashcards}), V('n2','Pin board',{body:pinBoard}), V('n3','Calendar pages',{body:calendarPages}), V('n4','Step pills',{body:'xStack'}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('v3','Image left + flashcards',{body:flashcards, img:'left'}), V('n5','Sticker grid',{body:'grid'})],
    p56:[V('v1','Pin board',{body:pinBoard}), V('n2','Worksheet',{body:worksheet}), V('n3','Flashcards',{body:flashcards}), V('n4','Step pills',{body:'xStack'}), V('v2','Image left + worksheet',{body:worksheet, img:'left'}), V('v3','Pin board + image right',{body:pinBoard, img:'right'}), V('n5','Sticker grid',{body:'grid'})],
    p78:[V('v1','Flashcards',{body:flashcards}), V('n2','Worksheet',{body:worksheet}), V('n3','Step pills',{body:'xStack'}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('n4','Two-column list',{body:'list', cols:2, cards:true})],
    stats:[V('v1','Ruler marks',{body:rulerStats}), V('n2','Report card',{body:reportCard}), V('v2','Image left + report card',{body:reportCard, img:'left'}), V('v3','Ruler + image right',{body:rulerStats, img:'right'}), V('n3','Sticker stats',{body:'statCards', icons:true})],
    timeline:[V('v1','Pencil timeline',{body:pencilTimeline}), V('n2','Calendar pages',{body:calendarPages}), V('n3','Step pills',{body:'xStack'}), V('v2','Worksheet + image right',{body:worksheet, img:'right'}), V('v3','Image left + calendar',{body:calendarPages, img:'left'}), V('n4','Milestone boxes',{body:'tlCards'})],
    quote:[V('v1','Sticker quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 15 — Spectrum Infographic : white canvas, halftone corners, five-colour infographic system */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var SER = ['#5B3FA8', '#2AA7B8', '#6CC24A', '#F29A38', '#3B82C4', '#E0558A'];
var P0 = {bg:'#F6F7F9', text:'#1E1E2E', title:'#1E1E2E', muted:'#5A5F6E', accent:'#5B3FA8', accent2:'#2AA7B8', card:'#FFFFFF', line:'#DCDFE6', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:SER,
  header:{kind:'center', tMax:38, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'solid', r:14, fill:'#FFFFFF', shadow:0.08, hl:{kind:'solid', fill:'#5B3FA8', dark:true}},
  icon:{kind:'circle', bg:'#5B3FA8', fg:'#FFFFFF'},
  num:{kind:'circle', bg:'#5B3FA8', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#5B3FA8', op:0.1, lc:'#5B3FA8', label:'In short'},
  footer:{brand:false, page:true, color:'#9AA0AE'}, xRows:'#FFFFFF', hubFill:'#EEF0F4', road:'#2E3140', flowBar:'#E6E8EE', xShadow:0.06,
  imgFramed:true, imgR:16
};
function halftone(C){
  K.halftone(C, W - 250, 20, 14, 7, 18, 5, '#C9CDD8', 0.8);
  K.halftone(C, 20, H - 150, 14, 7, 18, 5, '#C9CDD8', 0.8);
}
function bg(C, kind){ C.bg({fill:'#F6F7F9'}); halftone(C); }
/* decorative five-colour half ring (cover / closing) */
function bigArc(C, cx, cy, R, flip){
  for (var k = 0; k < 5; k++) {
    var a0 = (flip ? 90 : -90) + k * 36 + 1, a1 = a0 + 34;
    K.seg(C, cx, cy, R * 0.58, R, a0, a1, {fill:SER[k]});
    var m = K.pol(cx, cy, R * 0.79, (a0 + a1) / 2);
    C.ell(m[0] - 20, m[1] - 20, 40, 40, {fill:'#FFFFFF', op:0.25});
  }
  C.ell(cx - R * 0.46, cy - R * 0.46, R * 0.92, R * 0.92, {fill:'#FFFFFF', shadow:0.1});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) { D.imgBox(C, W - 560, 40, 520, H - 80, {r:20}); for (var k = 0; k < 5; k++) C.rect(W - 560 + k * 104, H - 40, 104, 10, {fill:SER[k]}); }
  else bigArc(C, 30, H / 2, 330, false);
  var x = o.img ? 64 : 440, w = o.img ? 600 : W - 504;
  if (f.company) C.text(x, 110, w, 22, f.company, {font:'body', size:14, wt:800, color:SER[0], caps:true, ls:0.18, ed:{f:'tf-company'}});
  C.text(x, 150, w, 280, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  for (var j = 0; j < 5; j++) C.rect(x + j * 34, 452, 28, 6, {fill:SER[j], r:3});
  if (C.s.subtitle) C.text(x, 478, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 600, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 480, H - 80, {r:20}); else bigArc(C, W - 30, H / 2, 330, true);
  var x = o.img ? 580 : 64, w = o.img ? W - 640 : 720;
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:92, min:40}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  for (var j = 0; j < 5; j++) C.rect(x + j * 34, 370, 28, 6, {fill:SER[j], r:3});
  if (C.s.subtitle) C.text(x, 396, w, 64, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  if (o.img) D.contactList(C, x, 480, w, {gap:54}); else D.contactRow(C, x, 500, w, {cards:true});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:SER[0]}); return; }
  for (var k = 0; k < 5; k++) C.rect(200 + k * 176, 130, 170, 8, {fill:SER[k], r:4});
  D.B.quote(C, {x:200, y:160, w:W - 400, h:H - 290}, {qc:SER[0]});
}
/* ============================================================
   SIGNATURE BODIES — segmented rings, colour bands, ribbons, rainbow waves
   ============================================================ */
var INK = '#1E1E2E', MUT = '#5A5F6E', TRACK = '#E6E8EE';
function sc(k){ return SER[((k % SER.length) + SER.length) % SER.length]; }
function onS(c){ return u.lum(c) > 0.55 ? INK : '#FFFFFF'; }
function soft(c, t){ return u.mix(c, '#FFFFFF', t == null ? 0.88 : t); }
function fitS(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function dH(C, t, w, ds){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dH(C, t, w, F.ds) : F.hs * 1.2 * Math.min(2, C.measureText(t.d, w, {font:'head', max:F.hs, min:F.hs}).n); }
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || INK, align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || INK, mc:o.mc || MUT, hLines:o.hLines});
}
function numDot(C, x, y, d, k, c){ C.ell(x, y, d, d, {fill:c}); C.text(x, y, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.4), wt:700, color:onS(c), align:'c', va:'m', lh:1, caps:false, ls:0}); }
/* half ellipse as a thick stroked curve (up = bulges upward) */
function halfEll(C, x0, x1, y, ry, up, color, lw){
  var rx = (x1 - x0) / 2, cx = x0 + rx, k = 0.5523, s = up ? -1 : 1;
  C.path([['M', x0, y], ['C', x0, y + s * ry * k, cx - rx * k, y + s * ry, cx, y + s * ry], ['C', cx + rx * k, y + s * ry, x1, y + s * ry * k, x1, y]], {stroke:color, lw:lw});
}

/* 1 — split ring: one ring cut in two colour halves, each side carries its text (p2) */
function splitRing(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var R = Math.min(Z.h * 0.48, 200, Z.w * 0.2), cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r0 = R * 0.6;
  var sw = (Z.w - 2 * R) / 2 - 60, F = fitS(C, it, sw, 80, Z.h - 60, {hMax:30, dMax:21, noBoost:true});
  it.forEach(function(t, k){
    var c = sc(k), a0 = k ? -88 : 92, a1 = k ? 88 : 268;
    K.seg(C, cx, cy, r0, R, a0, a1, {fill:c});
    var m = K.pol(cx, cy, (r0 + R) / 2, k ? 0 : 180);
    C.text(m[0] - 40, m[1] - 24, 80, 48, u.pad2(k + 1), {font:'head', size:Math.round(R * 0.2), wt:800, color:onS(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    var bx = k ? cx + R + 60 : Z.x, bh = blockH(C, t, sw, F), al = k ? 'l' : 'r', by = cy - (bh + 26) / 2;
    C.line(k ? cx + R + 8 : cx - R - 8, cy, k ? bx - 14 : bx + sw + 14, cy, {color:c, lw:2});
    C.ell(k ? bx - 20 : bx + sw + 8, cy - 6, 12, 12, {fill:c});
    C.rect(k ? bx : bx + sw - 60, by, 60, 6, {fill:c, r:3});
    block(C, bx, by + 22, sw, bh + 6, t, F, {align:al, tc:u.mix(c, INK, 0.25)});
  });
  C.ell(cx - r0 + 14, cy - r0 + 14, (r0 - 14) * 2, (r0 - 14) * 2, {fill:'#FFFFFF', shadow:0.1});
  for (var j = 0; j < 5; j++) C.rect(cx - 44 + j * 18, cy - 3, 12, 6, {fill:sc(j), r:3});
}

/* 2 — ribbon cards: white cards with a folded colour ribbon carrying the number */
function ribbonCards(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : 3; if (Z.w < 800 && n === 4) cols = 2; if (Z.w < 800 && n > 4) cols = 3;
  var rows = Math.ceil(n / cols), gx = 26, gy = 28, cw = (Z.w - gx * (cols - 1) - 14) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var rb = Math.min(rows > 1 ? 40 : 52, ch * 0.22), pad = cw < 240 ? 18 : 24, top = 20 + rb + 22;
  var F = fitS(C, it, cw - pad * 2, 60, ch - top - pad - 8, {hMax:rows > 1 ? 21 : 25, dMax:rows > 1 ? 16 : 19.5});
  var bh = 0; it.forEach(function(t){ bh = Math.max(bh, blockH(C, t, cw - pad * 2, F)); });
  ch = Math.min(ch, Math.max(ch * 0.72, top + bh + pad + 40)); var oy = Z.y + Math.max(0, (Z.h - (ch * rows + gy * (rows - 1))) / 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + 14 + c * (cw + gx), y = oy + r * (ch + gy), col = sc(k);
    D.card(C, x, y, cw, ch, {});
    C.rect(x, y + ch - 6, cw, 6, {fill:col});
    var rw = Math.min(cw * 0.62, 150);
    C.rect(x - 14, y + 20, rw, rb, {fill:col});
    K.poly(C, [[x - 14, y + 20 + rb], [x, y + 20 + rb], [x, y + 20 + rb + 12]], {fill:u.mix(col, '#000000', 0.35)});
    K.poly(C, [[x - 14 + rw, y + 20], [x - 14 + rw + rb * 0.4, y + 20], [x - 14 + rw, y + 20 + rb / 2], [x - 14 + rw + rb * 0.4, y + 20 + rb], [x - 14 + rw, y + 20 + rb]], {fill:col});
    C.text(x + 4, y + 20, rw - 24, rb, u.pad2(k + 1), {font:'head', size:Math.round(rb * 0.56), wt:800, color:onS(col), va:'m', lh:1, caps:false, ls:0.02});
    var by = y + top + Math.max(0, (ch - top - pad - bh) * 0.3);
    block(C, x + pad, by, cw - pad * 2, y + ch - pad - by, t, F, {tc:u.mix(col, INK, 0.3)});
  });
}

/* 3 — segmented donut: one colour segment per item, labels fanned left and right */
function segDonut(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var R = Math.min(Z.h * 0.5, 200, Z.w * 0.19), r0 = R * 0.58, cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, step = 360 / n;
  var nr = Math.ceil(n / 2), nl = n - nr, sw = (Z.w - 2 * R) / 2 - 70, rows = Math.max(nr, 1), gy = 12, rh = Math.min(120, (Z.h - gy * (rows - 1)) / rows);
  var F = fitS(C, it, sw - 44, Math.min(34, rh * 0.45), rh - 6, {hMax:n <= 4 ? 23 : 20, dMax:n <= 4 ? 17 : 15, hLines:1});
  it.forEach(function(t, k){
    var c = sc(k), a0 = -90 + k * step + 1.2, a1 = -90 + (k + 1) * step - 1.2, am = (a0 + a1) / 2;
    K.seg(C, cx, cy, r0, R, a0, a1, {fill:c});
    var m = K.pol(cx, cy, (r0 + R) / 2, am), is = Math.min(34, (R - r0) * 0.45);
    K.icon(C, m[0] - is / 2, m[1] - is / 2, is, t, onS(c));
    var right = k < nr, idx = right ? k : n - 1 - k, cnt = right ? nr : nl;
    var y0 = cy - (cnt * rh + (cnt - 1) * gy) / 2, y = y0 + idx * (rh + gy), my = y + rh / 2;
    var bx = right ? cx + R + 70 : Z.x, e = K.pol(cx, cy, R + 6, am), ex = right ? bx - 16 : bx + sw + 16;
    C.path([['M', e[0], e[1]], ['L', right ? Math.max(e[0] + 12, ex - 26) : Math.min(e[0] - 12, ex + 26), my], ['L', ex, my]], {stroke:c, lw:1.8});
    C.ell(e[0] - 4, e[1] - 4, 8, 8, {fill:c});
    numDot(C, right ? bx : bx + sw - 34, my - 17, 34, k, c);
    var tx = right ? bx + 44 : bx, tw = sw - 44, bh = Math.min(rh, blockH(C, t, tw, F));
    block(C, tx, my - bh / 2, tw, bh + 4, t, F, {align:right ? 'l' : 'r', hLines:1});
  });
  C.ell(cx - r0 + 10, cy - r0 + 10, (r0 - 10) * 2, (r0 - 10) * 2, {fill:'#FFFFFF', shadow:0.1});
  var tt = String(C.s.title || '').split(/\s+/).slice(0, 3).join(' ');
  C.text(cx - r0 + 26, cy - r0 * 0.5, (r0 - 26) * 2, r0, tt, {font:'head', fit:{max:22, min:12}, color:INK, align:'c', va:'m', lh:1.1, maxLines:3});
}

/* 4 — colour bands: numbered colour tab + tinted band per item (one or two columns) */
function colorBands(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = n > 5 && (Z.w > 760 || n > 6) ? 2 : 1, per = Math.ceil(n / cols), gx = 28, gy = per > 4 ? 10 : 14;
  var cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(110, (Z.h - gy * (per - 1)) / per), lw = o.stat ? Math.min(190, cw * 0.36) : Math.min(cols > 1 ? 86 : 110, cw * 0.2), tx0 = lw + 22, tw = cw - tx0 - 26;
  var oy = Z.y + Math.max(0, (Z.h - (rh * per + gy * (per - 1))) / 2);
  var F = fitS(C, it, tw, Math.min(34, rh * 0.45), rh - 10, {hMax:per <= 3 ? 26 : 21, dMax:per <= 3 ? 19 : 15.5, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = oy + r * (rh + gy), col = sc(k);
    C.rect(x, y, cw, rh, {fill:soft(col), r:rh / 2 > 16 ? 12 : 6});
    C.shape('homePlate', x, y, lw + 12, rh, {fill:col});
    C.rect(x + cw - 8, y + rh * 0.2, 4, rh * 0.6, {fill:col, r:2});
    if (o.stat && t.h) {
      C.text(x + 8, y, lw - 12, rh, t.h, {font:'head', fit:{max:Math.min(40, rh * 0.5), min:14}, wt:800, color:onS(col), align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:0}, maxLines:1});
      var sl = C.measureText(t.d, tw, {font:'head', max:F.hs + 2, min:13, maxLines:1}), sx = t.x ? C.measureText(t.x, tw, {font:'body', max:F.ds, min:11, h:rh - sl.h - 14}) : null, sh = sl.h + (sx ? sx.h + 4 : 0), sy = y + (rh - sh) / 2;
      C.text(x + tx0, sy, tw, sl.h + 2, t.d, {font:'head', size:sl.s, color:u.mix(col, INK, 0.45), lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
      if (sx) C.text(x + tx0, sy + sl.h + 4, tw, sx.h + 4, t.x, {font:'body', size:sx.s, color:MUT, lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
      return;
    }
    C.text(x, y, lw - 6, rh, u.pad2(k + 1), {font:'head', size:Math.min(34, rh * 0.46), wt:800, color:onS(col), align:'c', va:'m', lh:1, caps:false, ls:0});
    var bh = Math.min(rh - 6, blockH(C, t, tw, F));
    block(C, x + tx0, y + (rh - bh) / 2, tw, bh + 4, t, F, {hLines:1, tc:u.mix(col, INK, 0.45)});
  });
}

/* 5 — spectrum strip: one thick multi-colour bar holding the values, notes hanging below */
function statsOf(C){ return C.it.slice(0, 4).map(function(t){ return {t:t, v:t.h || t.d, l:t.h ? t.d : '', x:t.x || ''}; }); }
function spectrumStrip(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var cw = Z.w / n, bh = Math.min(150, Z.h * 0.36), lw = cw - 28;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), cw - 36, bh * 0.55, {font:'head', max:64, min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 60, {font:'head', max:22, min:13, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Z.h - bh - lh - 70, {font:'body', max:17, min:11, lh:1.38});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.38}).h); });
  var tot = bh + 44 + lh + (dh ? dh + 8 : 0), y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
  C.rect(Z.x, y0 + 8, Z.w, bh, {fill:'#000000', op:0.06, r:bh / 2});
  st.forEach(function(s, k){
    var x = Z.x + k * cw, c = sc(k), t = s.t, first = k === 0, last = k === n - 1;
    C.rect(x, y0, cw, bh, {fill:c, r:first || last ? bh / 2 : 0});
    if (first && !last) C.rect(x + cw / 2, y0, cw / 2, bh, {fill:c});
    if (last && !first) C.rect(x, y0, cw / 2, bh, {fill:c});
    C.text(x + 18, y0 + bh * 0.16, cw - 36, bh * 0.5, s.v, {font:'head', size:vs, wt:800, color:onS(c), align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    C.rect(x + cw / 2 - 18, y0 + bh * 0.72, 36, 4, {fill:onS(c), op:0.6, r:2});
    K.poly(C, [[x + cw / 2 - 14, y0 + bh], [x + cw / 2 + 14, y0 + bh], [x + cw / 2, y0 + bh + 14]], {fill:c});
    var ly = y0 + bh + 34;
    if (s.l) C.text(x + 14, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:u.mix(c, INK, 0.5), align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(x + 14, ly + lh + 8, lw, Z.y + Z.h - ly - lh - 8, s.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 6 — segmented gauges: half-rings of ten ticks lit in the item colour */
function pctOf(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(5, Math.min(100, +m[1])) : [70, 55, 85, 60][k % 4]; }
function segGauges(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var grid = Z.w / n < 230 && n > 2, cols = grid ? 2 : n, rows = Math.ceil(n / cols), gy = 24;
  var cw = Z.w / cols, chh = (Z.h - gy * (rows - 1)) / rows;
  var R = Math.min(cw * 0.42, grid ? chh * 0.6 : Z.h * 0.5, 175), r0 = R * 0.66, lw = cw - 30;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), r0 * 1.6, r0 * 0.6, {font:'head', max:52, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 56, {font:'head', max:grid ? 17 : 21, min:12, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Math.max(20, chh - R - lh - 40), {font:'body', max:grid ? 14 : 16, min:11, lh:1.36});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.36}).h); });
  var tot = R + 22 + lh + (dh ? dh + 6 : 0), oy = Math.max(0, (chh - tot) / 2);
  st.forEach(function(s, k){
    var cx = Z.x + cw * (k % cols + 0.5), top = Z.y + Math.floor(k / cols) * (chh + gy) + oy, cy = top + R, c = sc(k), t = s.t, lit = Math.round(pctOf(s.v, k) / 10);
    for (var j = 0; j < 10; j++) K.seg(C, cx, cy, r0, R, 180 + j * 18 + 1.5, 180 + (j + 1) * 18 - 1.5, {fill:j < lit ? c : TRACK});
    C.text(cx - r0 * 0.85, cy - r0 * 0.62, r0 * 1.7, r0 * 0.62, s.v, {font:'head', size:vs, wt:800, color:u.mix(c, INK, 0.2), align:'c', va:'b', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    C.rect(cx - R, cy + 2, R * 2, 2, {fill:TRACK});
    var ly = cy + 20;
    if (s.l) C.text(cx - lw / 2, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:INK, align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(cx - lw / 2, ly + lh + 6, lw, dh + 4, s.x, {font:'body', size:ds, color:MUT, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — rainbow wave: thick colour half-loops swing between milestones, text above / below */
function waveTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, ry = Math.min(92, Z.h * 0.2), base = Z.y + Z.h / 2, d = Math.min(62, cw * 0.36);
  C.line(Z.x, base, Z.x + cw / 2 - d / 2, base, {color:sc(0), lw:3}); C.line(Z.x + Z.w - cw / 2 + d / 2, base, Z.x + Z.w, base, {color:sc(n - 1), lw:3});
  for (var k = 0; k < n - 1; k++) halfEll(C, Z.x + cw * (k + 0.5), Z.x + cw * (k + 1.5), base, ry, k % 2 === 0, sc(k), 16);
  var tw = Math.min(cw * 1.7, 340), th = base - ry - 22 - Z.y;
  var F = fitS(C, it, tw - 10, 44, th - 8, {hMax:30, dMax:19, hLines:1});
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), c = sc(k), up = k % 2 === 1;
    C.ell(x - d / 2, base - d / 2, d, d, {fill:'#FFFFFF', line:c, lw:5, shadow:0.1});
    C.text(x - d / 2, base - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.36), wt:800, color:c, align:'c', va:'m', lh:1, caps:false, ls:0});
    var bx = Math.max(Z.x, Math.min(Z.x + Z.w - tw, x - tw / 2)), bh = Math.min(th, blockH(C, t, tw - 10, F));
    var by = up ? base - ry - 22 - bh : base + ry + 22;
    C.line(x, up ? by + bh + 4 : base + d / 2 + 4, x, up ? base - d / 2 - 4 : by - 4, {color:c, lw:1.5, dash:true});
    block(C, bx + 5, by, tw - 10, bh + 4, t, F, {align:'c', tc:u.mix(c, INK, 0.25)});
  });
}

/* 8 — colour columns: tall tinted columns with solid heads, a dotted rail through them */
function bandColumns(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var gx = n > 4 ? 14 : 20, cw = (Z.w - gx * (n - 1)) / n, hb = Math.min(110, Z.h * 0.26), pad = cw < 180 ? 12 : 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - pad * 2, hb - 16, {font:'head', max:n <= 3 ? 32 : 27, min:13, maxLines:2});
  var ty = hb + 58, ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - pad * 2, Z.h - ty - pad, {font:'body', max:n <= 3 ? 21 : 18, min:11, lh:1.4});
  var dh = 0; it.forEach(function(t){ if (t.h) dh = Math.max(dh, dH(C, t, cw - pad * 2, ds)); });
  var ch = Math.min(Z.h, Math.max(Z.h * 0.78, ty + dh + 30)), oy = Z.y + (Z.h - ch) / 2, rail = oy + hb + 28;
  C.rect(Z.x, rail - 1.5, Z.w, 3, {fill:TRACK});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), c = sc(k);
    C.rect(x, oy, cw, ch, {grad:{ang:180, stops:[[0, soft(c, 0.82)], [100, soft(c, 0.97)]]}, r:14});
    C.rect(x, oy, cw, hb, {fill:c, r:14}); C.rect(x, oy + hb - 14, cw, 14, {fill:c});
    K.poly(C, [[x + cw / 2 - 12, oy + hb], [x + cw / 2 + 12, oy + hb], [x + cw / 2, oy + hb + 12]], {fill:c});
    C.text(x + pad, oy, cw - pad * 2, hb, t.h || t.d, {font:'head', size:hs, color:onS(c), align:'c', va:'m', lh:1.08, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    C.ell(x + cw / 2 - 9, rail - 9, 18, 18, {fill:'#FFFFFF', line:c, lw:4});
    if (t.h && t.d) C.text(x + pad, oy + ty, cw - pad * 2, ch - ty - 10, t.d, {font:'body', size:ds, color:INK, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
D.register({
  id:'spectrum', name:'Spectrum Infographic', tag:'Five-colour · infographic', desc:'Clean white canvas, halftone corners and a five-colour infographic system: arcs, funnels, cycles and hexagon paths.',
  fonts:{head:'Poppins', body:'Poppins', headW:700, bodyW:400, headLh:1.08, gf:'Poppins:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Colour arc',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Colour arc',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Colour bands',{body:colorBands}), V('n2','Segmented donut',{body:segDonut}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Split ring',{body:splitRing}), V('n2','Ribbon cards',{body:ribbonCards}), V('n3','Colour columns',{body:bandColumns}), V('v2','Image left + bands',{body:colorBands, img:'left'}), V('v3','Ribbons + image right',{body:ribbonCards, img:'right'}), V('n4','Two panels',{body:'duo'})],
    p3:[V('v1','Segmented donut',{body:segDonut}), V('n2','Ribbon cards',{body:ribbonCards}), V('n3','Funnel',{body:'xFunnel'}), V('n4','Colour columns',{body:bandColumns}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('v3','Image left + ribbons',{body:ribbonCards, img:'left'}), V('n5','Three cards',{body:'cols'})],
    p4:[V('v1','Ribbon cards',{body:ribbonCards}), V('n2','Segmented donut',{body:segDonut}), V('n3','Funnel',{body:'xFunnel'}), V('n4','Colour columns',{body:bandColumns}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('v3','Image left + ribbons',{body:ribbonCards, img:'left'}), V('n5','Card grid',{body:'grid'})],
    p56:[V('v1','Segmented donut',{body:segDonut}), V('n2','Colour bands',{body:colorBands}), V('n3','Ribbon cards',{body:ribbonCards}), V('n4','Funnel',{body:'xFunnel'}), V('v2','Image left + bands',{body:colorBands, img:'left'}), V('n5','Card grid',{body:'grid'})],
    p78:[V('v1','Colour bands',{body:colorBands}), V('n2','Segmented donut',{body:segDonut}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('n3','Card grid',{body:'grid'})],
    stats:[V('v1','Segment gauges',{body:segGauges}), V('n2','Spectrum strip',{body:spectrumStrip}), V('v2','Image left + bands',{body:colorBands, img:'left', stat:true}), V('v3','Gauges + image right',{body:segGauges, img:'right'}), V('n3','Stat cards',{body:'statCards', icons:true})],
    timeline:[V('v1','Rainbow wave',{body:waveTimeline}), V('n2','Colour columns',{body:bandColumns}), V('n3','Phases',{body:'xPhases'}), V('v2','Bands + image right',{body:colorBands, img:'right'}), V('v3','Image left + wave',{body:waveTimeline, img:'left'}), V('n4','Horizontal line',{body:'tlH'})],
    quote:[V('v1','Colour quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 16 — Navy & Coral Flow : soft lavender-grey, navy + coral + sand, roads and flow diagrams */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#1E2F6B', CORAL = '#F07B3F', SLATE = '#7D8BB5', SAND = '#E6DCCB';
var P0 = {bg:'#E9ECF4', text:'#1C2447', title:'#1E2F6B', muted:'#4F5878', accent:'#1E2F6B', accent2:'#F07B3F', card:'#FFFFFF', line:'#CDD3E3', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[NAVY, CORAL, SLATE, '#2F4A9A', '#C9B79A', '#F2A477'],
  header:{kind:'center', tMax:40, tMin:24, sMax:16, top:46, gap:8, bar:true, barW:46, barH:4, barC:CORAL, barGap:14},
  card:{kind:'solid', r:16, fill:'#FFFFFF', shadow:0.08, hl:{kind:'solid', fill:NAVY, dark:true}},
  icon:{kind:'circle', bg:NAVY, fg:'#FFFFFF'},
  num:{kind:'circle', bg:CORAL, fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#FFFFFF', r:14, lc:CORAL, label:'Takeaway'},
  footer:{brand:true, page:true, color:'#7D86A6'}, xRows:'#FFFFFF', hubFill:'#DDE2EE', road:'#262B3A', flowBar:'#D6DBE8',
  imgFramed:true, imgR:18
};
function road(C, y, amp, x0, x1){
  var pts = [], N = 48;
  for (var i = 0; i <= N; i++) { var t = i / N; pts.push([x0 + t * (x1 - x0), y + amp * Math.sin(t * Math.PI * 2.6 + 0.6)]); }
  /* flowing navy ribbon with a coral thread and a soft echo curve (no road markings) */
  K.curve(C, pts.map(function(p){ return [p[0], p[1] + 34]; }), {stroke:SLATE, lw:3, lop:0.45});
  K.curve(C, pts, {stroke:NAVY, lw:22});
  K.curve(C, pts.map(function(p){ return [p[0], p[1] - 2]; }), {stroke:CORAL, lw:3});
  return pts;
}
function pin(C, x, y, c, ic){
  C.ell(x - 30, y - 30, 60, 60, {fill:'#FFFFFF', shadow:0.15}); C.ell(x - 24, y - 24, 48, 48, {fill:c});
  C.icon(x - 12, y - 12, 24, ic, '#FFFFFF');
}
function bg(C, kind){
  C.bg({grad:{ang:160, stops:[[0, '#EEF1F8'], [100, '#E3E7F1']]}});
  if (kind !== 'cover' && kind !== 'closing') { C.ell(W - 90, -90, 180, 180, {fill:SAND, op:0.7}); C.ell(-60, H - 60, 120, 120, {fill:SLATE, op:0.25}); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) {
    D.imgBox(C, W - 540, 0, 540, H, {r:0});
    C.rect(W - 548, 0, 8, H, {fill:CORAL});
  } else {
    var pts = road(C, H - 130, 60, -20, W + 20);
    pin(C, pts[12][0], pts[12][1], CORAL, 'mdi:flag-outline'); pin(C, pts[24][0], pts[24][1], NAVY, 'mdi:rocket-launch-outline'); pin(C, pts[36][0], pts[36][1], SLATE, 'mdi:trophy-outline');
  }
  var x = 64, w = o.img ? 620 : 900;
  if (f.company) C.text(x, 70, w, 22, f.company, {font:'body', size:14, wt:800, color:CORAL, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 110, w, o.img ? 300 : 230, C.s.title, {font:'head', fit:{max:70, min:34}, color:NAVY, ed:{f:'title'}, maxLines:3, va:'b', lh:1.04});
  var sy = o.img ? 430 : 360;
  C.rect(x, sy, 60, 5, {fill:CORAL, r:2});
  if (C.s.subtitle) C.text(x, sy + 22, w, 64, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta && o.img) C.text(x, 600, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 0, 0, 480, H, {r:0});
  else { var pts = road(C, 110, 40, -20, W + 20); pin(C, pts[40][0], pts[40][1], CORAL, 'mdi:flag-checkered'); }
  var x = o.img ? 540 : 120, w = o.img ? W - 600 : W - 240, al = o.img ? 'l' : 'c';
  C.text(x, 220, w, 170, C.s.title || 'Thank You', {font:'head', fit:{max:90, min:40}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:1});
  if (C.s.subtitle) C.text(x, 404, w, 60, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, x, 510, w, {cards:true});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:CORAL}); return; }
  C.rect(160, 110, W - 320, H - 220, {fill:'#FFFFFF', r:24, shadow:0.08});
  C.rect(160, 110, 12, H - 220, {fill:CORAL, r:6});
  D.B.quote(C, {x:220, y:150, w:W - 440, h:H - 300}, {qc:CORAL});
}
/* ============================================================
   SIGNATURE BODIES — curved split panels, flowing connectors, versus
   ============================================================ */
var INKN = '#1C2447', MUTN = '#4F5878', LITE = '#C9D1EE';
function fitN(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function dH(C, t, w, ds){ return t.d ? C.measureText(t.d, w, {font:'body', max:ds, min:ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return t.h ? F.hh + 6 + dH(C, t, w, F.ds) : F.hs * 1.2 * Math.min(2, C.measureText(t.d, w, {font:'head', max:F.hs, min:F.hs}).n); }
function block(C, x, y, w, h, t, F, o){
  o = o || {};
  if (!t.h) { C.text(x, y, w, h, t.d, {font:'head', size:F.hs, color:o.tc || INKN, align:o.align || 'l', lh:1.15, va:o.va || 't', ed:{f:'x-part', i:t.i, p:1}, maxLines:2}); return; }
  K.hd(C, x, y, w, h, t, F, {align:o.align, tc:o.tc || NAVY, mc:o.mc || MUTN, hLines:o.hLines});
}
function nc(k){ return k % 2 ? CORAL : NAVY; }
function onN(c){ return c === CORAL ? INKN : '#FFFFFF'; }
function dot(C, x, y, d, k, c, o){ o = o || {}; C.ell(x - d / 2, y - d / 2, d, d, {fill:o.fill || c, line:o.line, lw:o.lw}); C.text(x - d / 2, y - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.38), wt:800, color:o.fg || onN(c), align:'c', va:'m', lh:1, caps:false, ls:0}); }
function arrowHead(C, fx, fy, tx, ty, s, c){
  var a = Math.atan2(ty - fy, tx - fx), b = 0.5;
  K.poly(C, [[tx, ty], [tx - s * Math.cos(a - b), ty - s * Math.sin(a - b)], [tx - s * Math.cos(a + b), ty - s * Math.sin(a + b)]], {fill:c});
}
/* panel with a flowing S-curved right edge */
function curvedPanel(C, x, y, w, h, b, fill){
  var r = 18, mx = x + w;
  C.path([['M', x + r, y], ['L', mx + b, y], ['C', mx + b, y + h * 0.45, mx - b, y + h * 0.55, mx - b, y + h], ['L', x + r, y + h], ['Q', x, y + h, x, y + h - r], ['L', x, y + r], ['Q', x, y, x + r, y], ['Z']], {fill:fill});
  C.path([['M', mx + b, y], ['C', mx + b, y + h * 0.45, mx - b, y + h * 0.55, mx - b, y + h]], {stroke:CORAL, lw:6});
}

/* 1 — curved split: one card, navy half with an S-curved edge, coral seam, VS on the seam (p2) */
function splitPanel(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var b = Math.min(46, Z.w * 0.04), pw = Z.w / 2, pad = Math.min(48, Z.w * 0.045);
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF', r:18, shadow:0.08});
  curvedPanel(C, Z.x, Z.y, pw, Z.h, b, NAVY);
  var tw = pw - b - pad * 2, F = fitN(C, it, tw, 80, Z.h - pad * 2 - 70, {hMax:32, dMax:22, noBoost:true});
  it.forEach(function(t, k){
    var x = k ? Z.x + pw + b + pad : Z.x + pad, bh = blockH(C, t, tw, F), y = Z.y + Math.max(pad, (Z.h - bh - 70) / 2);
    C.text(x, y, 120, 50, u.pad2(k + 1), {font:'head', size:46, wt:800, color:CORAL, lh:1, caps:false, ls:0});
    block(C, x, y + 66, tw, bh + 6, t, F, {tc:k ? NAVY : '#FFFFFF', mc:k ? MUTN : LITE});
  });
  if (n > 1) { var cx = Z.x + pw, cy = Z.y + Z.h / 2, d = 76; C.ell(cx - d / 2, cy - d / 2, d, d, {fill:'#FFFFFF', line:CORAL, lw:5, shadow:0.12}); C.text(cx - d / 2, cy - d / 2, d, d, 'VS', {font:'head', size:24, wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0.04}); }
}

/* 2 — versus blades: two slanted panels, navy against coral, VS disc in the gap (p2) */
function vsPanels(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return splitPanel(C, Z, o);
  var s = Math.min(40, Z.w * 0.035), g = 26, mid = Z.x + Z.w / 2, pad = Math.min(44, Z.w * 0.04);
  K.poly(C, [[Z.x, Z.y], [mid - g / 2 + s, Z.y], [mid - g / 2 - s, Z.y + Z.h], [Z.x, Z.y + Z.h]], {fill:NAVY});
  K.poly(C, [[mid + g / 2 + s, Z.y], [Z.x + Z.w, Z.y], [Z.x + Z.w, Z.y + Z.h], [mid + g / 2 - s, Z.y + Z.h]], {fill:CORAL});
  var tw = Z.w / 2 - s - g / 2 - pad * 2 - 20, F = fitN(C, it, tw, 80, Z.h - pad * 2 - 80, {hMax:32, dMax:22, noBoost:true});
  it.forEach(function(t, k){
    var x = k ? mid + g / 2 + s + pad + 20 : Z.x + pad, bh = blockH(C, t, tw, F), y = Z.y + Math.max(pad, (Z.h - bh - 80) / 2);
    C.rect(x, y, 46, 5, {fill:k ? NAVY : CORAL, r:2});
    C.text(x, y + 16, tw, 50, u.pad2(k + 1), {font:'head', size:44, wt:800, color:k ? '#FFFFFF' : CORAL, lh:1, caps:false, ls:0});
    block(C, x, y + 78, tw, bh + 6, t, F, {tc:k ? INKN : '#FFFFFF', mc:k ? '#2A2F4A' : LITE});
  });
  var cy = Z.y + Z.h / 2, d = 84;
  C.ell(mid - d / 2, cy - d / 2, d, d, {fill:'#FFFFFF', shadow:0.15}); C.ell(mid - d / 2 + 7, cy - d / 2 + 7, d - 14, d - 14, {line:NAVY, lw:2});
  C.text(mid - d / 2, cy - d / 2, d, d, 'VS', {font:'head', size:26, wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0.04});
}

/* 3 — curved flow: cards zig-zag across two rows, joined by flowing S connectors */
function curvedFlow(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  if (n < 3) return splitPanel(C, Z, o);
  var cw = Math.min(n === 3 ? 440 : 380, (2 * Z.w - 24 * (n - 1)) / (n + 1)), step = (Z.w - cw) / (n - 1), ch = Math.min(Z.h * 0.44, 240), pad = cw < 240 ? 18 : 24;
  var F = fitN(C, it, cw - pad * 2 - 50, 50, ch - pad * 2 - 4, {hMax:21, dMax:16, hLines:2});
  var cards = [];
  it.forEach(function(t, k){
    var x = Z.x + k * step, y = k % 2 ? Z.y + Z.h - ch : Z.y; cards.push([x, y]);
  });
  for (var k = 0; k < n - 1; k++) {
    var a = cards[k], b = cards[k + 1], down = k % 2 === 0, x1 = a[0] + cw * 0.62, x2 = b[0] + cw * 0.38;
    var y1 = down ? a[1] + ch : a[1], y2 = down ? b[1] - 6 : b[1] + ch + 6, dy = (y2 - y1) * 0.55;
    C.path([['M', x1, y1], ['C', x1, y1 + dy, x2, y2 - dy, x2, y2 - (down ? 10 : -10)]], {stroke:k % 2 ? CORAL : NAVY, lw:3});
    arrowHead(C, x2, y2 - dy, x2, y2, 16, k % 2 ? CORAL : NAVY);
    C.ell(x1 - 6, y1 - 6, 12, 12, {fill:k % 2 ? CORAL : NAVY});
  }
  it.forEach(function(t, k){
    var x = cards[k][0], y = cards[k][1], c = nc(k);
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:16, shadow:0.08});
    C.rect(x, y + 18, 5, ch - 36, {fill:c, r:2});
    dot(C, x + pad + 18, y + pad + 18, 36, k, c);
    var bh = Math.min(ch - pad * 2, blockH(C, t, cw - pad * 2 - 50, F));
    block(C, x + pad + 50, y + Math.max(pad, (ch - bh) / 2), cw - pad * 2 - 50, bh + 4, t, F);
  });
}

/* 4 — branch tree: a navy source disc fans flowing curves out to every item */
function branchTree(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var d = Math.min(Z.h * 0.52, Z.w * 0.24, 230), sx = Z.x + d / 2 + 10, sy = Z.y + Z.h / 2;
  var x0 = Z.x + d + Math.min(150, Z.w * 0.12), lw = Z.x + Z.w - x0, gy = n > 6 ? 8 : 14, rh = Math.min(120, (Z.h - gy * (n - 1)) / n), oy = Z.y + (Z.h - (rh * n + gy * (n - 1))) / 2;
  var bd = Math.min(40, rh - 10), tx = bd + 30, tw = lw - tx - 20;
  var F = fitN(C, it, tw, Math.min(32, rh * 0.45), rh - 8, {hMax:n > 5 ? 19 : 22, dMax:n > 5 ? 14.5 : 16.5, hLines:1});
  C.ell(sx - d / 2 - 12, sy - d / 2 - 12, d + 24, d + 24, {line:CORAL, lw:2, lop:0.7});
  it.forEach(function(t, k){
    var y = oy + k * (rh + gy), my = y + rh / 2, c = nc(k), a = -60 + 120 * (n > 1 ? k / (n - 1) : 0.5), p = K.pol(sx, sy, d / 2 + 12, a);
    C.path([['M', p[0], p[1]], ['C', p[0] + 70, p[1], x0 - 90, my, x0 - 8, my]], {stroke:c, lw:2.5, lop:0.9});
    C.ell(p[0] - 5, p[1] - 5, 10, 10, {fill:c});
    C.rect(x0, y, lw, rh, {fill:'#FFFFFF', r:Math.min(16, rh / 2), shadow:0.06});
    dot(C, x0 + 12 + bd / 2, my, bd, k, c);
    var bh = Math.min(rh - 6, blockH(C, t, tw, F));
    block(C, x0 + tx, my - bh / 2, tw, bh + 4, t, F, {hLines:1});
  });
  C.ell(sx - d / 2, sy - d / 2, d, d, {fill:NAVY, shadow:0.12});
  var tt = String(C.s.title || '').split(/\s+/).slice(0, 4).join(' ');
  C.text(sx - d * 0.36, sy - d * 0.3, d * 0.72, d * 0.6, tt, {font:'head', fit:{max:26, min:12}, color:'#FFFFFF', align:'c', va:'m', lh:1.1, maxLines:3});
  C.rect(sx - 18, sy + d * 0.3, 36, 4, {fill:CORAL, r:2});
}

/* 5 — serpentine: one flowing ribbon runs across, turns, and runs back; numbered stops on it */
function serpentine(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rows = n > 3 ? 2 : 1, m = Math.ceil(n / rows), bh = Z.h / rows, y1 = Z.y + (rows > 1 ? 26 : Z.h * 0.26), y2 = rows > 1 ? Z.y + bh + 26 : y1, r = (y2 - y1) / 2;
  var xL = Z.x + 6, xR = Z.x + Z.w - (rows > 1 ? r + 6 : 30), seg = (xR - xL) / m, d = Math.min(46, seg * 0.36);
  var dpath = [['M', xL, y1], ['L', xR, y1]];
  if (rows > 1) { dpath = dpath.concat(K.arcCmds(xR, y1 + r, r, -90, 90, 'L')); dpath.push(['L', xL + seg * 0.3, y2]); }
  C.path(dpath, {stroke:NAVY, lw:14}); C.path(dpath, {stroke:CORAL, lw:2.5});
  var ex = rows > 1 ? xL + seg * 0.3 : xR, ey = rows > 1 ? y2 : y1;
  arrowHead(C, rows > 1 ? ex + 10 : ex - 10, ey, rows > 1 ? ex - 22 : ex + 22, ey, 30, CORAL);
  var tw = seg - 18, F = fitN(C, it, tw, 44, (rows > 1 ? bh : Z.h * 0.6) - d - 40, {hMax:n > 6 ? 18 : 22, dMax:n > 6 ? 14 : 17});
  it.forEach(function(t, k){
    var r2 = Math.floor(k / m), j = k % m, x = r2 ? xR - seg * (j + 0.5) : xL + seg * (j + 0.5), y = r2 ? y2 : y1, c = nc(k);
    C.ell(x - d / 2 - 5, y - d / 2 - 5, d + 10, d + 10, {fill:'#FFFFFF', shadow:0.12});
    dot(C, x, y, d, k, c);
    block(C, x - tw / 2, y + d / 2 + 18, tw, Z.y + (r2 + 1) * bh - (y + d / 2 + 18) - 8, t, F, {align:'c'});
  });
}

/* 6 — split stats: headline figure on a curved navy panel, the rest listed beside it */
function statsOf(C){ return C.it.slice(0, 4).map(function(t){ return {t:t, v:t.h || t.d, l:t.h ? t.d : '', x:t.x || ''}; }); }
function splitStats(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var b = 30, pw = n > 1 ? Z.w * (Z.w < 800 ? 0.46 : 0.4) : Z.w, pad = 40;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:'#FFFFFF', r:18, shadow:0.08});
  if (n > 1) curvedPanel(C, Z.x, Z.y, pw, Z.h, b, NAVY); else C.rect(Z.x, Z.y, Z.w, Z.h, {fill:NAVY, r:18});
  var s0 = st[0], t0 = s0.t, lw = pw - b - pad * 2;
  var v0 = C.measureText(s0.v, lw, {font:'head', max:Math.min(120, Z.h * 0.32), min:30, maxLines:1});
  var l0 = s0.l ? C.measureText(s0.l, lw, {font:'head', max:26, min:14, maxLines:2}) : {h:0, s:14}, x0 = s0.x ? C.measureText(s0.x, lw, {font:'body', max:18, min:11, h:Z.h * 0.3}) : {h:0, s:11};
  var tot = v0.h + 20 + l0.h + (x0.h ? x0.h + 10 : 0), y = Z.y + (Z.h - tot) / 2;
  C.text(Z.x + pad, y, lw, v0.h + 4, s0.v, {font:'head', size:v0.s, wt:800, color:'#FFFFFF', lh:1, caps:false, ls:-0.01, ed:{f:'x-part', i:t0.i, p:t0.h ? 0 : 1}, maxLines:1});
  C.rect(Z.x + pad, y + v0.h + 8, 50, 5, {fill:CORAL, r:2});
  if (s0.l) C.text(Z.x + pad, y + v0.h + 20, lw, l0.h + 2, s0.l, {font:'head', size:l0.s, color:'#FFFFFF', lh:1.15, ed:{f:'x-part', i:t0.i, p:1}, maxLines:2});
  if (s0.x) C.text(Z.x + pad, y + v0.h + 20 + l0.h + 10, lw, x0.h + 4, s0.x, {font:'body', size:x0.s, color:LITE, lh:1.36, ed:{f:'x-part', i:t0.i, p:2}});
  var rest = st.slice(1); if (!rest.length) return;
  var rx = Z.x + pw + b + 36, rw = Z.x + Z.w - 32 - rx, rh = (Z.h - 40) / rest.length, vw = Math.min(210, rw * 0.42);
  var vs = C.groupFit(rest.map(function(s){ return s.v; }), vw, rh * 0.62, {font:'head', max:60, min:20, maxLines:1});
  var ls = C.groupFit(rest.map(function(s){ return s.l; }), rw - vw - 20, rh * 0.5, {font:'head', max:21, min:13, maxLines:2});
  var ds = C.groupFit(rest.map(function(s){ return s.x; }), rw - vw - 20, rh * 0.4, {font:'body', max:16, min:11, lh:1.36});
  rest.forEach(function(s, k){
    var y = Z.y + 20 + k * rh, t = s.t, c = k % 2 ? NAVY : CORAL;
    if (k) C.rect(rx, y, rw, 1.5, {fill:'#E3E7F1'});
    C.text(rx, y, vw, rh, s.v, {font:'head', size:vs, wt:800, color:c, va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    var lh = s.l ? C.measureText(s.l, rw - vw - 20, {font:'head', max:ls, min:ls, maxLines:2}).h : 0, dh = s.x ? C.measureText(s.x, rw - vw - 20, {font:'body', max:ds, min:ds, lh:1.36}).h : 0, by = y + (rh - lh - dh - (dh ? 4 : 0)) / 2;
    if (s.l) C.text(rx + vw + 20, by, rw - vw - 20, lh + 2, s.l, {font:'head', size:ls, color:INKN, lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(rx + vw + 20, by + lh + 4, rw - vw - 20, dh + 4, s.x, {font:'body', size:ds, color:MUTN, lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 7 — flow stats: value discs riding a flowing curve, alternating navy and coral */
function flowStats(C, Z, o){
  var st = statsOf(C), n = st.length; if (!n) return;
  var cw = Z.w / n, d = Math.min(cw * 0.66, Z.h * 0.44, 200), off = Math.min(46, Z.h * 0.1), lw = cw - 20;
  var vs = C.groupFit(st.map(function(s){ return s.v; }), d * 0.78, d * 0.4, {font:'head', max:54, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(s){ return s.l; }), lw, 56, {font:'head', max:21, min:13, maxLines:2});
  var lh = 0; st.forEach(function(s){ if (s.l) lh = Math.max(lh, C.measureText(s.l, lw, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var ds = C.groupFit(st.map(function(s){ return s.x; }), lw, Math.max(20, Z.h - d - off - lh - 50), {font:'body', max:16, min:11, lh:1.36});
  var dh = 0; st.forEach(function(s){ if (s.x) dh = Math.max(dh, C.measureText(s.x, lw, {font:'body', max:ds, min:ds, lh:1.36}).h); });
  var oy = Math.max(0, (Z.h - (d + off + 24 + lh + (dh ? dh + 6 : 0))) / 2); Z = {x:Z.x, y:Z.y + oy, w:Z.w, h:Z.h - oy};
  var pts = [[Z.x - 40, Z.y + d / 2 + off]];
  st.forEach(function(s, k){ pts.push([Z.x + cw * (k + 0.5), Z.y + d / 2 + (k % 2 ? off : 0)]); });
  pts.push([Z.x + Z.w + 40, Z.y + d / 2 + (n % 2 ? off : 0)]);
  K.curve(C, pts, {stroke:CORAL, lw:3, lop:0.8});
  st.forEach(function(s, k){
    var cx = pts[k + 1][0], cy = pts[k + 1][1], c = nc(k), t = s.t;
    C.ell(cx - d / 2 - 8, cy - d / 2 - 8, d + 16, d + 16, {fill:'#FFFFFF', shadow:0.12});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c});
    C.text(cx - d * 0.39, cy - d * 0.2, d * 0.78, d * 0.4, s.v, {font:'head', size:vs, wt:800, color:onN(c), align:'c', va:'m', lh:1, caps:false, ls:0, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    var ly = cy + d / 2 + 24;
    if (s.l) C.text(cx - lw / 2, ly, lw, lh + 2, s.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
    if (s.x) C.text(cx - lw / 2, ly + lh + 6, lw, Math.max(16, Z.y + Z.h - ly - lh - 6), s.x, {font:'body', size:ds, color:MUTN, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* 8 — split timeline: navy band with a flowing lower edge; stops sit on the wave */
function splitTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, A = Math.min(22, Z.h * 0.05), bandH = Math.min(Z.h * 0.42, 190);
  var ds0 = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - 24, Z.h - bandH - A - 50, {font:'body', max:n <= 4 ? 22 : 19, min:11, lh:1.4});
  var dh0 = 0; it.forEach(function(t){ if (t.h) dh0 = Math.max(dh0, dH(C, t, cw - 24, ds0) * 1.03); });
  var oy = Math.max(0, (Z.h - (bandH + A + 44 + dh0)) / 2), mid = Z.y + oy + bandH; Z = {x:Z.x, y:Z.y + oy, w:Z.w, h:Z.h - oy};
  function wy(x){ return mid + A * Math.sin((x - Z.x) / Z.w * Math.PI * 2.4 + 0.5); }
  C.rect(Z.x, Z.y, Z.w, mid - Z.y - 10, {fill:NAVY, r:18});
  var wp = []; for (var i = 0; i <= 30; i++) { var xx = Z.x + Z.w * i / 30; wp.push([xx, wy(xx)]); }
  var dd = [['M', Z.x, mid - 40], ['L', Z.x, wp[0][1]]];
  for (var j = 0; j < wp.length - 1; j++) { var p0 = wp[j - 1] || wp[j], p1 = wp[j], p2 = wp[j + 1], p3 = wp[j + 2] || p2; dd.push(['C', p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6, p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6, p2[0], p2[1]]); }
  dd.push(['L', Z.x + Z.w, mid - 40]); dd.push(['Z']);
  C.path(dd, {fill:NAVY});
  K.curve(C, wp, {stroke:CORAL, lw:4});
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 24, mid - Z.y - 60, {font:'head', max:n <= 4 ? 40 : 34, min:14, maxLines:2});
  var ty = mid + A + 40, ds = ds0;
  it.forEach(function(t, k){
    var x = Z.x + cw * (k + 0.5), y = wy(x), d = 34;
    if (k) C.rect(Z.x + cw * k, Z.y + 24, 1, mid - Z.y - 70, {fill:'#FFFFFF', op:0.15});
    C.text(x - cw / 2 + 12, Z.y + 18, cw - 24, mid - Z.y - 60, t.h || t.d, {font:'head', size:hs, color:'#FFFFFF', align:'c', va:'m', lh:1.08, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    C.line(x, y - d / 2 - 22, x, y - d / 2 - 4, {color:CORAL, lw:2});
    C.ell(x - d / 2 - 5, y - d / 2 - 5, d + 10, d + 10, {fill:'#FFFFFF', shadow:0.15});
    dot(C, x, y, d, k, CORAL);
    if (t.h && t.d) C.text(x - cw / 2 + 12, ty, cw - 24, Z.y + Z.h - ty, t.d, {font:'body', size:ds, color:INKN, align:'c', lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}
D.register({
  id:'navycoral', name:'Navy & Coral Flow', tag:'Navy · coral · process', desc:'Soft lavender-grey pages, navy and coral accents, flowing curved connectors, split panels and versus layouts for processes and transformations.',
  fonts:{head:'DM Sans', body:'DM Sans', headW:700, bodyW:400, headLh:1.06, gf:'DM Sans:wght@400;500;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Flowing ribbon',{}), V('v2','Title + image right',{img:true})],
    closing:[V('v1','Ribbon + centred',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Branch tree',{body:branchTree}), V('n2','Serpentine',{body:serpentine}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Curved split',{body:splitPanel}), V('n2','Versus blades',{body:vsPanels}), V('n3','Branch tree',{body:branchTree}), V('v2','Image left + curved split',{body:splitPanel, img:'left'}), V('v3','Versus + image right',{body:vsPanels, img:'right'}), V('n4','Two panels',{body:'duo'})],
    p3:[V('v1','Curved flow',{body:curvedFlow}), V('n2','Branch tree',{body:branchTree}), V('n3','Serpentine',{body:serpentine}), V('n4','Chevron ribbon',{body:'xChevrons'}), V('v2','Image left + branches',{body:branchTree, img:'left'}), V('v3','Curved flow + image right',{body:curvedFlow, img:'right'}), V('n5','Three cards',{body:'cols'})],
    p4:[V('v1','Branch tree',{body:branchTree}), V('n2','Curved flow',{body:curvedFlow}), V('n3','Versus',{body:'xVersus'}), V('n4','Serpentine',{body:serpentine}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n5','Card grid',{body:'grid'})],
    p56:[V('v1','Serpentine',{body:serpentine}), V('n2','Curved flow',{body:curvedFlow}), V('n3','Benefits vs challenges',{body:'xVersus'}), V('n4','Branch tree',{body:branchTree}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n5','Card grid',{body:'grid'})],
    p78:[V('v1','Serpentine',{body:serpentine}), V('n2','Benefits vs challenges',{body:'xVersus'}), V('n3','Branch tree',{body:branchTree}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('n4','Card grid',{body:'grid'})],
    stats:[V('v1','Split headline',{body:splitStats}), V('n2','Flow discs',{body:flowStats}), V('v2','Image left + split',{body:splitStats, img:'left'}), V('v3','Flow discs + image right',{body:flowStats, img:'right'}), V('n3','Stat cards',{body:'statCards', icons:true})],
    timeline:[V('v1','Split wave',{body:splitTimeline}), V('n2','Serpentine',{body:serpentine}), V('n3','Chevron ribbon',{body:'xChevrons'}), V('v2','Branches + image right',{body:branchTree, img:'right'}), V('v3','Image left + serpentine',{body:serpentine, img:'left'}), V('n4','Milestone cards',{body:'tlCards'})],
    quote:[V('v1','Card quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 17 — Isometric Slate : white pages, navy ribbon labels, orange signal colour, isometric blocks + targets */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#16284F', BLUE = '#3E5F9E', ORNG = '#F4843C', STEEL = '#B8C3D9';
var P0 = {bg:'#F3F5FA', text:'#16284F', title:'#16284F', muted:'#56627E', accent:'#F4843C', accent2:'#3E5F9E', card:'#FFFFFF', line:'#D5DBE8', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[NAVY, BLUE, ORNG, '#7F95C4', '#2B3F6E', '#F7A76E'],
  header:{kind:'left', tMax:40, tMin:24, sMax:16, top:50, kicker:function(C){ return u.pad2(C.i + 1) + ' / ' + (C.cover.company || 'Report'); }, kc:ORNG, kSize:12, kGap:30},
  card:{kind:'top', r:10, fill:'#FFFFFF', shadow:0.07, barW:5, hl:{kind:'solid', fill:NAVY, dark:true}},
  icon:{kind:'square', r:10, bg:NAVY, fg:'#FFFFFF'},
  num:{kind:'square', r:8, bg:NAVY, fg:'#FFFFFF'},
  conc:{kind:'band', fill:NAVY, r:10, label:'Bottom line'},
  footer:{brand:false, page:true, color:'#8A94AD'}, xRows:'#FFFFFF', hubFill:'#E4E9F3', road:NAVY, flowBar:'#DCE2EE', stepWord:'Step',
  imgFramed:true, imgR:12
};
/* isometric cube cluster (decoration) */
function iso(C, x, y, s, c){
  K.poly(C, [[x, y], [x + s, y - s * 0.55], [x + 2 * s, y], [x + s, y + s * 0.55]], {fill:u.mix(c, '#FFFFFF', 0.35)});
  K.poly(C, [[x, y], [x + s, y + s * 0.55], [x + s, y + s * 1.55], [x, y + s]], {fill:c});
  K.poly(C, [[x + 2 * s, y], [x + s, y + s * 0.55], [x + s, y + s * 1.55], [x + 2 * s, y + s]], {fill:u.mix(c, '#000000', 0.25)});
}
function bg(C, kind){
  C.bg({fill:'#F3F5FA'});
  if (kind !== 'cover' && kind !== 'closing') { C.rect(0, 0, 10, H, {fill:NAVY}); C.rect(0, 0, 10, 120, {fill:ORNG}); iso(C, W - 104, 40, 22, STEEL); iso(C, W - 60, 64, 16, ORNG); }
}
function stairsDecor(C, x0, y0, n, s){
  for (var k = 0; k < n; k++) iso(C, x0 + k * s * 1.6, y0 - k * s * 1.1, s, [NAVY, BLUE, STEEL, ORNG, BLUE][k % 5]);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.rect(0, 0, W * 0.5, H, {fill:NAVY});
  if (o.img) D.imgBox(C, W * 0.5, 0, W * 0.5, H, {r:0});
  else { stairsDecor(C, W * 0.56, H - 150, 5, 58); K.target(C, W - 150, 170, 90, ORNG, '#FFFFFF', 4); }
  var x = 64, w = W * 0.5 - 120;
  if (f.company) C.text(x, 70, w, 22, f.company, {font:'body', size:14, wt:800, color:ORNG, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 120, w, 300, C.s.title, {font:'head', fit:{max:62, min:30}, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  C.rect(x, 446, 70, 6, {fill:ORNG});
  if (C.s.subtitle) C.text(x, 472, w, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#C9D2E6', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 620, w, 22, meta, {font:'body', size:14, wt:600, color:'#C9D2E6'});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  C.rect(W * 0.5, 0, W * 0.5, H, {fill:NAVY});
  if (o.img) D.imgBox(C, 0, 0, W * 0.5, H, {r:0}); else stairsDecor(C, 90, H - 140, 4, 60);
  var x = W * 0.5 + 64, w = W * 0.5 - 128;
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  C.rect(x, 370, 70, 6, {fill:ORNG});
  if (C.s.subtitle) C.text(x, 396, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:'#C9D2E6', ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 490, w, {vc:'#FFFFFF', markO:{bg:ORNG}});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:ORNG}); return; }
  K.target(C, 150, H - 140, 90, NAVY, '#FFFFFF', 4);
  D.B.quote(C, {x:260, y:130, w:W - 460, h:H - 260}, {qc:ORNG});
}

/* ---------- signature helpers ---------- */
function DK(c, t){ return u.mix(c, '#000000', t == null ? 0.28 : t); }
function LT(c, t){ return u.mix(c, '#FFFFFF', t == null ? 0.4 : t); }
/* oblique box: front face x,y,w,h; depth d up-right */
function obox(C, x, y, w, h, d, c, o){
  o = o || {}; var dy = d * 0.75;
  K.poly(C, [[x, y], [x + d, y - dy], [x + w + d, y - dy], [x + w, y]], {fill:o.top || LT(c, 0.38)});
  K.poly(C, [[x + w, y], [x + w + d, y - dy], [x + w + d, y + h - dy], [x + w, y + h]], {fill:o.side || DK(c, 0.25)});
  C.rect(x, y, w, h, {fill:c, line:o.line, lw:o.lw});
}
/* isometric slab: top rhombus centred cx,cy */
function slab(C, cx, cy, w, t, c){
  var hh = w * 0.29;
  K.poly(C, [[cx - w / 2, cy], [cx, cy + hh], [cx, cy + hh + t], [cx - w / 2, cy + t]], {fill:DK(c, 0.12)});
  K.poly(C, [[cx + w / 2, cy], [cx, cy + hh], [cx, cy + hh + t], [cx + w / 2, cy + t]], {fill:DK(c, 0.34)});
  K.poly(C, [[cx - w / 2, cy], [cx, cy - hh], [cx + w / 2, cy], [cx, cy + hh]], {fill:c});
}
/* slanted technical tag */
function tag(C, x, y, w, h, txt, fill, fg, sz){
  var k = h * 0.35;
  K.poly(C, [[x + k, y], [x + w, y], [x + w - k, y + h], [x, y + h]], {fill:fill});
  C.text(x, y, w, h, txt, {font:'body', size:sz || Math.min(13, Math.round(h * 0.5)), wt:800, color:fg || K.on(fill), align:'c', va:'m', caps:true, ls:0.06, lh:1, maxLines:1});
}
/* corner brackets */
function corners(C, x, y, w, h, c, s, lw){
  s = s || 14; lw = lw || 2;
  [[x, y, 1, 1], [x + w, y, -1, 1], [x, y + h, 1, -1], [x + w, y + h, -1, -1]].forEach(function(q){
    C.rect(q[2] > 0 ? q[0] : q[0] - s, q[3] > 0 ? q[1] : q[1] - lw, s, lw, {fill:c});
    C.rect(q[2] > 0 ? q[0] : q[0] - lw, q[3] > 0 ? q[1] : q[1] - s, lw, s, {fill:c});
  });
}
/* dimension line with arrow ends and end ticks */
function dim(C, x1, x2, y, c){
  C.line(x1, y - 8, x1, y + 8, {color:c, lw:1.5}); C.line(x2, y - 8, x2, y + 8, {color:c, lw:1.5});
  C.line(x1 + 2, y, x2 - 2, y, {color:c, lw:1.5});
  K.poly(C, [[x1 + 1, y], [x1 + 11, y - 4.5], [x1 + 11, y + 4.5]], {fill:c}); K.poly(C, [[x2 - 1, y], [x2 - 11, y - 4.5], [x2 - 11, y + 4.5]], {fill:c});
}
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
/* heading + description block, vertically centred in h */
function hdc(C, x, y, w, h, t, F, o){
  var bh = (t.h ? F.hh + 6 : 0) + descH(C, t, w, F);
  var yy = y + Math.max(0, (h - bh) / 2);
  return K.hd(C, x, yy, w, h - (yy - y), t, F, o);
}
/* single-line label items (agenda) */
function lab(C, x, y, w, h, t, sz, color, o){
  o = o || {};
  C.text(x, y, w, h, t.h || t.d, {font:'head', size:sz, color:color, va:'m', lh:1.15, align:o.align || 'l', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
}
function labFit(C, it, w, h){ return C.groupFit(it.map(function(t){ return t.h || t.d; }), w, h, {font:'head', max:it.length <= 4 ? 28 : 22, min:13, maxLines:2}); }

/* 1. exploded layer stack: isometric slabs on the left, leader lines to rows */
function slabStack(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var gy = n > 6 ? 6 : 12, rh = (Z.h - gy * (n - 1)) / n, sp = rh + gy, t = Math.max(8, Math.min(16, rh * 0.16));
  var sw = Math.max(96, Math.min(Z.w * 0.24, 280, (sp - t - 8) / 0.58)), open = sw * 0.58 + t + 6 <= sp;
  var cx = Z.x + sw / 2 + 4, lx = Z.x + sw + 70, tw = Z.x + Z.w - lx - 72;
  var cy0 = Z.y + rh / 2 - t / 2, cyN = cy0 + (n - 1) * sp;
  [cx - sw / 2, cx, cx + sw / 2].forEach(function(gx, j){ C.line(gx, cy0 + (j === 1 ? sw * 0.29 : 0), gx, cyN + (j === 1 ? sw * 0.29 : 0), {color:STEEL, lw:1.2, dash:true}); });
  for (var k = n - 1; k >= 0; k--) {
    var cy = cy0 + k * sp, c = K.col(C, k);
    slab(C, cx, cy, sw, t, c);
    if (open) C.text(cx - sw * 0.3, cy - sw * 0.14, sw * 0.6, sw * 0.28, u.pad2(k + 1), {font:'head', size:Math.min(26, Math.max(13, sw * 0.12)), wt:800, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0});
  }
  var H2 = hasH(it), F = H2 ? K.fitHD(C, it, tw, rh * 0.45, rh - 4, {hMax:20, dMax:15.5, hMin:13, dMin:11, hLines:1}) : null, ls = H2 ? 0 : labFit(C, it, tw, rh);
  it.forEach(function(t, k){
    var cy = cy0 + k * sp, y = Z.y + k * sp, c = K.col(C, k);
    C.line(cx + sw / 2 + 6, cy, lx - 10, cy, {color:c, lw:1.5});
    C.ell(lx - 13, cy - 4, 8, 8, {fill:c});
    tag(C, lx, cy - 13, 58, 26, 'L' + u.pad2(k + 1), k % 3 === 2 ? ORNG : NAVY);
    if (H2) hdc(C, lx + 72, y, tw, rh, t, F, {tc:NAVY}); else lab(C, lx + 72, y, tw, rh, t, ls, NAVY);
  });
}

/* 2. module grid: bracket-framed plates with an oblique numbered cube and grid coordinates */
function moduleGrid(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : 4)), rows = Math.ceil(n / cols);
  var gx = 22, gyy = 22, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gyy * (rows - 1)) / rows, pad = 22, cs = rows > 1 ? 38 : 48, d = cs * 0.28;
  var hw = cw - pad * 2 - cs - d - 16, dy0 = pad + d * 0.75 + cs + 14;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), hw, cs + 8, {font:'head', max:rows > 1 ? 19 : 22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - pad * 2, ch - dy0 - pad - 10, {font:'body', max:rows > 1 ? 17 : (n <= 3 ? 21 : 19), min:11, lh:1.38});
  var dmax = 0; it.forEach(function(t){ if (t.h) dmax = Math.max(dmax, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h); });
  var need = dy0 + dmax + pad + 30, zy = Z.y;
  if (rows === 1 && ch > need + 20) { zy = Z.y + (ch - need) * 0.4; ch = need; }
  var off = Math.max(0, (ch - pad - 14 - dy0 - dmax) * 0.42);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * (cw + gx) + (cols - Math.min(cols, n - r * cols)) * (cw + gx) / 2, y0 = zy + r * (ch + gyy), c = K.col(C, k), y = y0 + off;
    C.rect(x, y0, cw, ch, {fill:'#FFFFFF', r:4, shadow:0.06});
    corners(C, x - 4, y0 - 4, cw + 8, ch + 8, NAVY, 16, 2);
    obox(C, x + pad, y + pad + d * 0.75, cs, cs, d, c);
    C.text(x + pad, y + pad + d * 0.75, cs, cs, u.pad2(k + 1), {font:'head', size:Math.round(cs * 0.4), wt:800, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad + cs + d + 16, y + pad, hw, cs + d * 0.75, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    if (t.h) C.text(x + pad, y + dy0, cw - pad * 2, dmax + 6, t.d, {font:'body', size:ds, color:P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
    C.rect(x + pad, y0 + ch - 10, 36, 4, {fill:ORNG});
    C.text(x + cw - 70, y0 + ch - 24, 56, 16, String.fromCharCode(65 + r) + '·' + (cc + 1), {font:'body', size:11, wt:800, color:'#8A94AD', align:'r', lh:1, caps:false, ls:0.1});
  });
}

/* 3. two oblique boxes with an orange direction block between them */
function obliqueDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var d = 30, dy = d * 0.75, mid = n > 1 ? 96 : 0, bw = (Z.w - mid - d) / n, top = Z.y + dy + 4, bh = Z.y + Z.h - top - 6;
  var pad = bw > 400 ? 38 : 26, iw = bw - pad * 2;
  var F = K.fitHD(C, it, iw, 80, bh - pad * 2 - 44, {hMax:27, dMax:19, hMin:14, dMin:11.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (bw + mid), dark = k === 0, fill = dark ? NAVY : '#FFFFFF';
    obox(C, x, top, bw, bh, d, fill, dark ? {} : {top:'#D9DFEC', side:STEEL});
    if (!dark) C.rect(x, top, bw, bh, {line:'#C9D1E2', lw:1.5});
    corners(C, x + 12, top + 12, bw - 24, bh - 24, dark ? ORNG : NAVY, 18, 2);
    tag(C, x + pad, top + pad, 92, 28, (k ? 'Side B' : 'Side A'), dark ? ORNG : NAVY, '#FFFFFF', 12);
    hdc(C, x + pad, top + pad + 44, iw, bh - pad * 2 - 44, t, F, {tc:dark ? '#FFFFFF' : NAVY, mc:dark ? '#C9D2E6' : P.muted});
  });
  if (n > 1) {
    var ax = Z.x + bw + 14, ay = top + bh / 2 - 34;
    obox(C, ax, ay + 10, mid - 38, 48, 12, ORNG);
    K.poly(C, [[ax + mid - 38, ay - 2], [ax + mid - 12, ay + 34], [ax + mid - 38, ay + 70]], {fill:ORNG});
  }
}

/* 4. isometric prism chart for stats */
function prismStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, pw = Math.min(cw * 0.42, 118), d = pw * 0.32, dy = d * 0.75;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), cw - 24, 50, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), cw - 24, 44, {font:'body', max:14, min:11, maxLines:2});
  var anyD = st.some(function(t){ return t.d; }), below = ls * 1.2 * 2 + 12 + (anyD ? ds * 1.36 * 2 + 6 : 0) + 14;
  var by = Z.y + Z.h - below;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), cw - 16, 64, {font:'head', max:52, min:24, maxLines:1});
  var hMax = by - Z.y - vs * 1.1 - dy - 14, hMin = hMax * 0.42;
  K.poly(C, [[Z.x, by], [Z.x + 22, by - 18], [Z.x + Z.w, by - 18], [Z.x + Z.w - 22, by]], {fill:'#E1E6F0'});
  C.rect(Z.x, by, Z.w - 22, 5, {fill:NAVY});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = [BLUE, NAVY, '#2B3F6E', ORNG][k % 4], h = hMin + (hMax - hMin) * (n === 1 ? 1 : k / (n - 1));
    var x = cx - (pw + d) / 2, y = by - h - 4;
    obox(C, x, y, pw, h, d, c);
    for (var j = 1; j < 4; j++) C.rect(x + 10, y + h * j / 4, pw - 20, 1, {fill:'#FFFFFF', op:0.18});
    C.text(Z.x + cw * k + 8, y - dy - vs * 1.1 - 6, cw - 16, vs * 1.1, t.v, {font:'head', size:vs, color:k === n - 1 ? ORNG : NAVY, align:'c', lh:1, caps:false, ls:0, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + cw * k + 12, by + 16, cw - 24, ls * 2.5, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(Z.x + cw * k + 12, by + 16 + ls * 2.4 + 6, cw - 24, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 5. technical-drawing stats: code label, huge value, dimension line, label */
function dimStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var narrow = Z.w < 820 && n > 2, cols = narrow ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, rH = Z.h / rows, iw = cw - 44;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, rH * 0.4, {font:'head', max:rows > 1 ? 54 : (n <= 2 ? 96 : 76), min:26, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 52, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, rH * 0.22, {font:'body', max:15, min:11, lh:1.38});
  var blk = 30 + vs * 1.05 + 24 + ls * 2.45 + (st.some(function(t){ return t.d; }) ? ds * 1.38 * 2 + 8 : 0);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * cw + 22, y = Z.y + r * rH + Math.max(0, (rH - blk) / 2);
    if (cc) { C.line(Z.x + cc * cw, Z.y + r * rH + 10, Z.x + cc * cw, Z.y + (r + 1) * rH - 10, {color:STEEL, lw:1.2, dash:true}); }
    tag(C, x, y, 78, 22, 'Fig ' + u.pad2(k + 1), k === 0 ? ORNG : NAVY, '#FFFFFF', 11);
    C.text(x, y + 30, iw, vs * 1.05, t.v, {font:'head', size:vs, color:NAVY, lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    dim(C, x, x + iw, y + 30 + vs * 1.05 + 12, k === 0 ? ORNG : BLUE);
    C.text(x, y + 30 + vs * 1.05 + 24, iw, ls * 2.45, t.l, {font:'head', size:ls, color:NAVY, lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x, y + 30 + vs * 1.05 + 24 + ls * 2.45 + 6, iw, ds * 1.38 * 2 + 4, t.d, {font:'body', size:ds, color:P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 6. rising isometric rail of tiles (timeline) */
function isoRail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, tw = Math.min(cw * 0.78, 168), hh = tw * 0.29, t = 14, rise = Math.min(0.56, 0.3 + n * 0.05);
  var pts = it.map(function(q, k){ return [Z.x + cw * (k + 0.5), Z.y + Z.h * (0.5 + rise / 2 - rise * (n === 1 ? 0.5 : k / (n - 1)))]; });
  C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1] + hh * 0.5]; }), {stroke:STEEL, lw:12});
  C.path(pts.map(function(p, i){ return [i ? 'L' : 'M', p[0], p[1] + hh * 0.5]; }), {stroke:ORNG, lw:2});
  var room = Z.h * (0.5 - rise / 2) + Z.h * rise * 0.5 - hh - 30;
  var F = K.fitHD(C, it.map(function(q){ return {i:q.i, h:'', d:q.d}; }), cw - 16, 1, Math.max(60, room), {dMax:18, dMin:11, noBoost:true});
  var ys = C.groupFit(it.map(function(q){ return q.h || ''; }), tw * 0.6, hh * 0.8, {font:'head', max:24, min:13, maxLines:1});
  it.forEach(function(q, k){
    var p = pts[k], c = K.col(C, k), up = p[1] > Z.y + Z.h / 2 || (n === 1);
    slab(C, p[0], p[1], tw, t, c);
    C.text(p[0] - tw * 0.3, p[1] - hh * 0.45, tw * 0.6, hh * 0.9, q.h || u.pad2(k + 1), {font:'head', size:ys, color:K.on(c), align:'c', va:'m', lh:1, caps:false, ls:0, ed:q.h ? {f:'x-part', i:q.i, p:0} : null, maxLines:1});
    var dh = descH(C, q, cw - 16, F);
    if (up) {
      var yb = p[1] - hh - 16; C.line(p[0], p[1] - hh - 2, p[0], yb + 4, {color:c, lw:1.5, dash:true});
      C.text(Z.x + cw * k + 8, yb - dh - 6, cw - 16, dh + 4, q.d, {font:'body', size:F.ds, color:P.text, align:'c', lh:1.36, ed:{f:'x-part', i:q.i, p:1}});
    } else {
      var yt = p[1] + hh + t + 16; C.line(p[0], p[1] + hh + t + 2, p[0], yt - 4, {color:c, lw:1.5, dash:true});
      C.text(Z.x + cw * k + 8, yt + 2, cw - 16, dh + 4, q.d, {font:'body', size:F.ds, color:P.text, align:'c', lh:1.36, ed:{f:'x-part', i:q.i, p:1}});
    }
  });
}

/* 7. staggered oblique bars (gantt-style) with labels in slanted tags */
function isoGantt(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var d = 14, dy = d * 0.75, gy = 12, rh = (Z.h - dy - gy * (n - 1)) / n;
  var hw = 0; it.forEach(function(t){ if (t.h) hw = Math.max(hw, C.measureText(t.h, 900, {font:'head', max:18, min:18, maxLines:1}).h ? t.h.length : 0); });
  var lw = hw > 7 ? Math.min(230, Z.w * 0.26) : 128, x0 = Z.x + lw + 18, span = Z.x + Z.w - d - x0, step = n > 1 ? span * 0.34 / (n - 1) : 0;
  var hs = C.groupFit(it.map(function(t){ return t.h || u.pad2(1); }), lw - 30, rh - 6, {font:'head', max:20, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), span - step * (n - 1) - 40, rh - 12, {font:'body', max:19, min:11, lh:1.3});
  for (var g = 0; g <= 4; g++) { var gx = x0 + span * g / 4; C.line(gx, Z.y, gx, Z.y + Z.h, {color:'#DCE1EC', lw:1}); }
  it.forEach(function(t, k){
    var y = Z.y + dy + k * (rh + gy), c = k === n - 1 ? ORNG : [NAVY, BLUE, '#2B3F6E', '#4A6AA8'][k % 4], bx = x0 + step * k, bw = x0 + span - bx;
    C.shape('homePlate', Z.x, y + 4, lw, rh - 8, {fill:k === n - 1 ? ORNG : '#E4E9F3'});
    C.text(Z.x + 14, y + 4, lw - 36, rh - 8, t.h || u.pad2(k + 1), {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    obox(C, bx, y, bw, rh, d, c);
    C.text(bx + 18, y, bw - 34, rh, t.d, {font:'body', size:ds, wt:500, color:k === n - 1 ? NAVY : '#FFFFFF', va:'m', lh:1.3, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 8. data sheet: REF tags, item column, specification column, dotted rules */
function specSheet(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var H2 = hasH(it), hy = 26, rh = (Z.h - hy - 4) / n, refW = 64, iw = H2 ? Math.min(300, Z.w * 0.3) : Z.w - refW - 24, dx = Z.x + refW + 20 + iw + 20, dw = Z.x + Z.w - dx;
  C.text(Z.x, Z.y, refW, 16, 'Ref', {font:'body', size:11, wt:800, color:ORNG, caps:true, ls:0.14, lh:1});
  C.text(Z.x + refW + 20, Z.y, iw, 16, 'Item', {font:'body', size:11, wt:800, color:ORNG, caps:true, ls:0.14, lh:1});
  if (H2) C.text(dx, Z.y, dw, 16, 'Specification', {font:'body', size:11, wt:800, color:ORNG, caps:true, ls:0.14, lh:1});
  C.rect(Z.x, Z.y + 20, Z.w, 2.5, {fill:NAVY});
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), iw, rh - 6, {font:'head', max:H2 ? (n <= 4 ? 23 : 20) : 22, min:13, maxLines:H2 ? 2 : 1});
  var ds = H2 ? C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 6, {font:'body', max:n <= 2 ? 22 : (n <= 4 ? 20 : 17), min:11, lh:1.3}) : 0;
  it.forEach(function(t, k){
    var y = Z.y + hy + k * rh;
    if (k % 2 === 0) C.rect(Z.x, y, Z.w, rh, {fill:'#FFFFFF'});
    if (k < n - 1) C.line(Z.x, y + rh, Z.x + Z.w, y + rh, {color:STEEL, lw:1, dash:true});
    tag(C, Z.x + 8, y + rh / 2 - 12, refW - 8, 24, String.fromCharCode(65 + Math.floor(k / 4)) + '-' + (k % 4 + 1), k === 0 ? ORNG : NAVY, '#FFFFFF', 11);
    C.text(Z.x + refW + 20, y, iw, rh, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:H2 ? 2 : 1});
    if (H2) { C.rect(dx - 12, y + rh / 2 - 1, 4, 2, {fill:ORNG}); C.text(dx, y, dw, rh, t.d, {font:'body', size:ds, color:P.muted, va:'m', lh:1.3, ed:{f:'x-part', i:t.i, p:1}}); }
  });
}

/* 9. statement inside a large oblique box */
function isoStatement(C, Z, o){
  var P = C.P, t = C.it[0], d = 34, dy = d * 0.75, x = Z.x, y = Z.y + dy + 6, w = Z.w - d - 8, h = Z.h - dy - 10;
  obox(C, x, y, w, h, d, '#FFFFFF', {top:'#D9DFEC', side:STEEL});
  C.rect(x, y, 10, h, {fill:NAVY}); C.rect(x, y, 10, h * 0.3, {fill:ORNG});
  corners(C, x + 26, y + 18, w - 44, h - 36, NAVY, 20, 2);
  var pad = 58;
  if (!t) { C.text(x + pad, y + pad, w - pad * 2, h - pad * 2, C.s.subtitle || '', {font:'body', fit:{max:30, min:16}, color:NAVY, va:'m', lh:1.4, ed:{f:'subtitle'}}); return; }
  if (t.h) tag(C, x + pad, y + 44, Math.min(260, t.h.length * 11 + 50), 30, t.h, ORNG, '#FFFFFF', 13);
  var ty = y + (t.h ? 96 : 50);
  C.text(x + pad, ty, w - pad * 2, y + h - 44 - ty, t.d, {font:'head', fit:{max:36, min:17}, color:NAVY, va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'isometric', name:'Isometric Slate', tag:'Navy · orange · analysis', desc:'Crisp slate pages with a navy spine, orange signal colour, isometric blocks, targets and step tabs for risk and strategy decks.',
  fonts:{head:'Outfit', body:'Inter', headW:700, bodyW:400, headLh:1.06, gf:'Outfit:wght@400;500;600;700|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Navy half + blocks',{}), V('v2','Navy half + image',{img:true})],
    closing:[V('v1','Blocks + navy half',{}), V('v2','Image + navy half',{img:true})],
    agenda:[V('v1','Exploded layers',{body:slabStack}), V('n2','Data sheet',{body:specSheet}), V('n3','Module grid',{body:moduleGrid}), V('v2','Layers + image right',{body:slabStack, img:'right'}), V('n4','Numbered list',{body:'agList'})],
    text:[V('v1','Oblique statement box',{body:isoStatement}), V('n2','Statement',{body:'text'}), V('v2','Box + image right',{body:isoStatement, img:'right'}), V('v3','Image left + box',{body:isoStatement, img:'left'})],
    p2:[V('v1','Oblique A / B boxes',{body:obliqueDuo}), V('n2','Module plates',{body:moduleGrid}), V('n3','Data sheet',{body:specSheet}), V('v2','Image left + layers',{body:slabStack, img:'left'}), V('v3','Plates + image right',{body:moduleGrid, img:'right'})],
    p3:[V('v1','Exploded layers',{body:slabStack}), V('n2','Module plates',{body:moduleGrid}), V('n3','Staggered bars',{body:isoGantt}), V('n4','Target + list',{body:'xTarget'}), V('n5','Isometric steps',{body:'xStairs'}), V('v2','Image left + layers',{body:slabStack, img:'left'}), V('v3','Data sheet + image right',{body:specSheet, img:'right'})],
    p4:[V('v1','Module plates',{body:moduleGrid}), V('n2','Exploded layers',{body:slabStack}), V('n3','Data sheet',{body:specSheet}), V('n4','Target + list',{body:'xTarget'}), V('n5','Isometric steps',{body:'xStairs'}), V('v2','Layers + image right',{body:slabStack, img:'right'}), V('v3','Image left + plates',{body:moduleGrid, img:'left'})],
    p56:[V('v1','Exploded layers',{body:slabStack}), V('n2','Module plates',{body:moduleGrid}), V('n3','Data sheet',{body:specSheet}), V('n4','Target + list',{body:'xTarget'}), V('n5','Isometric steps',{body:'xStairs'}), V('v2','Data sheet + image right',{body:specSheet, img:'right'})],
    p78:[V('v1','Module plates',{body:moduleGrid}), V('n2','Data sheet',{body:specSheet}), V('n3','Exploded layers',{body:slabStack}), V('v2','Data sheet + image right',{body:specSheet, img:'right'})],
    stats:[V('v1','Isometric prisms',{body:prismStats}), V('n2','Dimensioned figures',{body:dimStats}), V('n3','Stat cards',{body:'statCards', icons:true}), V('v2','Image left + figures',{body:dimStats, img:'left'}), V('v3','Prisms + image right',{body:prismStats, img:'right'})],
    timeline:[V('v1','Rising tile rail',{body:isoRail}), V('n2','Staggered bars',{body:isoGantt}), V('n3','Isometric steps',{body:'xStairs'}), V('v2','Bars + image right',{body:isoGantt, img:'right'})],
    quote:[V('v1','Target quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 18 — Lumen Gradient : light grey, lime → teal → blue → indigo gradient system, diamonds and rising paths */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var SER = ['#8FCB3A', '#2DBE8C', '#1FA6C9', '#3B7BE0', '#6A5AE0', '#9B6BE8'];
var P0 = {bg:'#F2F3F6', text:'#1B2033', title:'#1B2033', muted:'#5B6275', accent:'#3B7BE0', accent2:'#2DBE8C', card:'#FFFFFF', line:'#DDE0E7', onAccent:'#FFFFFF'};
var GR = {ang:90, stops:[[0, '#8FCB3A'], [35, '#2DBE8C'], [65, '#1FA6C9'], [100, '#6A5AE0']]};
var S = {
  margin:64, series:SER,
  header:{kind:'center', tMax:38, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'outline', r:16, fill:'#FFFFFF', line:'#DDE0E7', lw:1.5, hl:{kind:'grad', grad:{ang:135, stops:[[0, '#2DBE8C'], [100, '#3B7BE0']]}, dark:true}},
  icon:{kind:'circle', bg:'#3B7BE0', fg:'#FFFFFF', grad:{ang:135, stops:[[0, '#2DBE8C'], [100, '#3B7BE0']]}},
  num:{kind:'circle', bg:'#3B7BE0', fg:'#FFFFFF'},
  conc:{kind:'band', grad:GR, r:30, label:'Key result'},
  footer:{brand:false, page:true, color:'#9AA0AE'}, xRows:'#FFFFFF', hubFill:'#E6E9EF', road:'#1B2033', flowBar:'#E1E4EB',
  imgFramed:true, imgR:20
};
function bg(C, kind){
  C.bg({fill:'#F2F3F6'});
  C.rect(0, H - 8, W, 8, {grad:GR});
  if (kind !== 'cover' && kind !== 'closing') { D.glow(C, W - 60, 40, 220, 160, '#2DBE8C', 0.14); D.glow(C, 60, H - 40, 220, 160, '#6A5AE0', 0.1); }
}
function ribbon(C, x, y, w, h){
  for (var k = 0; k < 5; k++) K.poly(C, [[x + k * w * 0.2, y + h], [x + k * w * 0.2 + w * 0.12, y + h - (k + 1) * h * 0.18], [x + (k + 1) * w * 0.2 + w * 0.12, y + h - (k + 1) * h * 0.18], [x + (k + 1) * w * 0.2, y + h]], {fill:SER[k]});
  for (var j = 0; j < 5; j++) { var cx = x + j * w * 0.2 + w * 0.16, cy = y + h - (j + 1) * h * 0.18 - 44; C.shape('diamond', cx - 22, cy - 22, 44, 44, {fill:'#FFFFFF', line:SER[j], lw:3}); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) { D.imgBox(C, W - 560, 40, 520, H - 88, {r:26}); C.rect(W - 560, H - 60, 520, 12, {grad:GR, r:6}); }
  else ribbon(C, W * 0.5, 180, W * 0.46, 420);
  var x = 64, w = W * 0.5 - 100;
  if (f.company) K.pill(C, x, 90, Math.min(300, f.company.length * 10 + 50), 32, f.company, {fill:'#FFFFFF', line:'#DDE0E7', color:'#1B2033', size:12, wt:700, caps:true, ls:0.1, ed:{f:'tf-company'}});
  C.text(x, 150, w, 280, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  C.rect(x, 452, 140, 8, {grad:GR, r:4});
  if (C.s.subtitle) C.text(x, 480, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 610, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 480, H - 88, {r:26}); else ribbon(C, 40, 200, W * 0.42, 400);
  var x = o.img ? 580 : W * 0.5 + 20, w = W - x - 64;
  C.text(x, 170, w, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  C.rect(x, 380, 140, 8, {grad:GR, r:4});
  if (C.s.subtitle) C.text(x, 404, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 490, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#2DBE8C'}); return; }
  C.rect(W / 2 - 110, 120, 220, 8, {grad:GR, r:4});
  D.B.quote(C, {x:200, y:150, w:W - 400, h:H - 280}, {qc:'#2DBE8C'});
}

/* ---------- signature helpers ---------- */
var INK = '#1B2033', GS = [[0, '#8FCB3A'], [35, '#2DBE8C'], [65, '#1FA6C9'], [100, '#6A5AE0']];
function at(t){ t = Math.max(0, Math.min(1, t)) * 100; for (var i = 1; i < GS.length; i++) if (t <= GS[i][0]) { var a = GS[i - 1], b = GS[i]; return u.mix(a[1], b[1], (t - a[0]) / (b[0] - a[0])); } return GS[GS.length - 1][1]; }
function deep(t){ return u.mix(at(t), '#101633', 0.34); }
function tn(k, n){ return n <= 1 ? 0.5 : k / (n - 1); }
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function pct(v, k, n){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : 50 + 45 * tn(k, n); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + descH(C, t, w, F); }
function hdc(C, x, y, w, h, t, F, o){ var bh = blockH(C, t, w, F), yy = y + Math.max(0, (h - bh) / 2); K.hd(C, x, yy, w, h - (yy - y), t, F, o); }
function sdiam(C, cx, cy, s, c, o){
  o = o || {};
  if (o.glow !== false) D.glow(C, cx, cy, s * 1.1, s * 1.1, c, o.gop || 0.35);
  C.shape('diamond', cx - s / 2, cy - s / 2, s, s, {fill:o.fill || '#FFFFFF', line:c, lw:o.lw || 3});
  if (o.label != null) C.text(cx - s / 2, cy - s / 2, s, s, o.label, {font:'head', size:o.size || Math.round(s * 0.3), wt:800, color:o.tc || INK, align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1});
}

/* 1. rising light beams on a gradient baseline, diamond lamp on each */
function lightBeams(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, base = Z.y + Z.h * (n <= 3 ? 0.46 : 0.5), bw = Math.min(cw * 0.34, 84), ds = Math.min(52, cw * 0.36);
  var F = K.fitHD(C, it, cw - 22, 52, Z.y + Z.h - base - 26, {hMax:21, dMax:17, hMin:13, dMin:11});
  C.rect(Z.x, base - 2, Z.w, 4, {grad:GR, r:2});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = at(tn(k, n)), h = (base - Z.y - ds / 2 - 8) * (0.42 + 0.58 * tn(k, n));
    C.rect(cx - bw / 2, base - h, bw, h, {grad:{ang:0, stops:[[0, c, 0.85], [100, c, 0.04]]}, r:6});
    D.glow(C, cx, base, bw * 1.1, 16, c, 0.6);
    sdiam(C, cx, base - h, ds, c, {label:u.pad2(k + 1)});
    K.hd(C, Z.x + cw * k + 11, base + 22, cw - 22, Z.y + Z.h - base - 22, t, F, {align:'c', tc:INK});
  });
}

/* 2. tiles cut from one continuous gradient */
function spectrumTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : Math.ceil(n / 2))), rows = Math.ceil(n / cols);
  var g = 14, cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = cw > 220 ? 24 : 18, top = rows > 1 ? 50 : 70;
  var H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, cw - pad * 2, 56, ch - top - pad, {hMax:21, dMax:15.5, hMin:13, dMin:11}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - top - pad, {font:'head', max:24, min:13, maxLines:2});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inLast = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + g) + (cols - inLast) * (cw + g) / 2, y = Z.y + r * (ch + g), t0 = k / n, t1 = (k + 1) / n;
    C.rect(x, y, cw, ch, {grad:{ang:120, stops:[[0, deep(t0)], [100, deep(t1)]]}, r:18});
    C.shape('diamond', x + cw - pad - 30, y + pad - 2, 30, 30, {line:'#FFFFFF', lw:2, lop:0.55});
    C.shape('diamond', x + cw - pad - 21, y + pad + 7, 12, 12, {fill:'#FFFFFF', op:0.85});
    C.text(x + pad, y + pad - 6, 120, 60, u.pad2(k + 1), {font:'head', size:rows > 1 ? 26 : 48, wt:800, color:'#FFFFFF', op:0.92, lh:1, caps:false, ls:0});
    if (H2) { var bh = blockH(C, t, cw - pad * 2, F); K.hd(C, x + pad, Math.max(y + top, y + ch - pad - bh - 4), cw - pad * 2, bh + 6, t, F, {tc:'#FFFFFF', mc:'#E6EEF6'}); }
    else C.text(x + pad, y + top, cw - pad * 2, ch - top - pad, t.d, {font:'head', size:ls, color:'#FFFFFF', lh:1.15, va:'b', ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
  });
}

/* 3. diamond of four diamonds with text in the four corners */
function diamondCluster(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (n !== 4) return lightBeams(C, Z, o); var P = C.P;
  var cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, s = Math.min(Z.h * 0.24, Z.w * 0.1, 116), a = s * 0.94;
  var pos = [[0, -s], [s, 0], [0, s], [-s, 0]], box = [[0, 0], [1, 0], [1, 1], [0, 1]];
  var colw = cx - Z.x - s - a - 44, bh = Z.h / 2 - 10;
  D.glow(C, cx, cy, s * 2.4, s * 2.2, '#2DBE8C', 0.16);
  var F = K.fitHD(C, it, colw, 56, bh - 30, {hMax:21, dMax:16, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var px = cx + pos[k][0], py = cy + pos[k][1], c = deep(k / 3);
    C.shape('diamond', px - a, py - a, a * 2, a * 2, {grad:{ang:135, stops:[[0, at(k / 4)], [100, c]]}});
    C.text(px - a / 2, py - a / 2, a, a, u.pad2(k + 1), {font:'head', size:Math.round(a * 0.42), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    var right = box[k][0] === 1, bx = right ? Z.x + Z.w - colw : Z.x, by = Z.y + box[k][1] * (bh + 20);
    var ty = by + Math.max(0, (bh - blockH(C, t, colw, F)) / 2), ly = ty + Math.min(F.hh || 20, 26) / 2;
    var ex = k === 0 ? px + (right ? a * 0.5 : -a * 0.5) : (k === 2 ? px + a * 0.5 : px + (right ? a : -a)), ey = k === 0 ? py - a * 0.5 : (k === 2 ? py + a * 0.5 : py);
    var tx = right ? bx - 16 : bx + colw + 16;
    C.path([['M', ex, ey], ['L', ex + (tx - ex) * 0.4, ly], ['L', tx, ly]], {stroke:at(k / 3), lw:1.6});
    C.ell(tx - 4, ly - 4, 8, 8, {fill:at(k / 3)});
    K.hd(C, bx, ty, colw, bh - (ty - by), t, F, {align:right ? 'l' : 'r', tc:INK});
  });
}

/* 4. equaliser level meters for stats */
function levelStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, iw = cw - 30, seg = 9;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, 70, {font:'head', max:n <= 2 ? 64 : 52, min:24, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 48, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 40, {font:'body', max:14, min:11, maxLines:2});
  var anyD = st.some(function(t){ return t.d; }), bot = ls * 1.2 * 2 + (anyD ? ds * 1.36 * 2 + 8 : 0) + 18;
  var mTop = Z.y + vs * 1.1 + 16, mH = Z.y + Z.h - bot - mTop, sh = (mH - (seg - 1) * 6) / seg, mw = Math.min(iw * 0.72, 150);
  st.forEach(function(t, k){
    var x = Z.x + cw * k, cx = x + cw / 2, lit = Math.max(1, Math.round(seg * pct(t.v, k, n) / 100));
    C.text(x + 15, Z.y, iw, vs * 1.1, t.v, {font:'head', size:vs, color:INK, align:'c', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    D.glow(C, cx, mTop + mH - (mH * lit / seg) / 2, mw * 0.8, mH * lit / seg * 0.6 + 10, at(tn(k, n)), 0.22);
    for (var j = 0; j < seg; j++) {
      var yy = mTop + mH - (j + 1) * sh - j * 6, on = j < lit;
      C.rect(cx - mw / 2, yy, mw, sh, {fill:on ? at(j / (seg - 1)) : '#E3E6EE', r:Math.min(8, sh / 2)});
    }
    var ly = Z.y + Z.h - bot + 14;
    C.text(x + 15, ly, iw, ls * 2.45, t.l, {font:'head', size:ls, color:INK, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x + 15, ly + ls * 2.45 + 4, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 5. numbers floating in soft halos */
function haloStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var narrow = Z.w < 820 && n > 2, cols = narrow ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, rh = Z.h / rows, iw = cw - 30;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, rh * 0.36, {font:'head', max:rows > 1 ? 50 : (n <= 2 ? 88 : 66), min:24, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 48, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 44, {font:'body', max:15, min:11, maxLines:2});
  var blk = 30 + vs * 1.08 + 22 + ls * 2.45 + (st.some(function(t){ return t.d; }) ? ds * 2.8 + 4 : 0);
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, x = Z.x + cc * cw, cx = x + cw / 2, y = Z.y + r * rh + Math.max(0, (rh - blk) / 2), c = at(tn(k, n));
    D.glow(C, cx, y + 30 + vs * 0.55, Math.min(cw * 0.5, 170), vs * 1.3 + 20, c, 0.3);
    C.shape('diamond', cx - 9, y, 18, 18, {fill:c});
    C.text(x + 15, y + 30, iw, vs * 1.08, t.v, {font:'head', size:vs, color:INK, align:'c', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.rect(cx - 36, y + 30 + vs * 1.08 + 8, 72, 5, {grad:{ang:90, stops:[[0, c], [100, at(Math.min(1, tn(k, n) + 0.3))]]}, r:3});
    C.text(x + 15, y + 30 + vs * 1.08 + 22, iw, ls * 2.45, t.l, {font:'head', size:ls, color:INK, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x + 15, y + 30 + vs * 1.08 + 22 + ls * 2.45, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 6. gradient spectrum line with glowing diamond stops, text alternating */
function spectrumLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, mid = Z.y + Z.h / 2, s = Math.min(58, cw * 0.4);
  C.rect(Z.x, mid - 5, Z.w, 10, {grad:GR, r:5});
  var F = K.fitHD(C, it, cw * (n > 3 ? 1.5 : 1) - 24, 48, Z.h / 2 - s / 2 - 34, {hMax:21, dMax:17, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = at(tn(k, n)), up = k % 2 === 0, tw = Math.min(cw * (n > 3 ? 1.5 : 1) - 24, Z.w - 8), tx = Math.max(Z.x, Math.min(Z.x + Z.w - tw, cx - tw / 2));
    sdiam(C, cx, mid, s, c, {label:u.pad2(k + 1), gop:0.45});
    var bh = blockH(C, t, tw, F);
    C.line(cx, up ? mid - s / 2 - 4 : mid + s / 2 + 4, cx, up ? mid - s / 2 - 18 : mid + s / 2 + 18, {color:c, lw:2});
    K.hd(C, tx, up ? mid - s / 2 - 24 - bh : mid + s / 2 + 24, tw, bh + 4, t, F, {align:'c', tc:INK});
  });
}

/* 7. lamp rows: glowing diamond lamps getting brighter down the list */
function lampRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = n > 5 && Z.w > 800 ? 2 : 1, per = Math.ceil(n / cols), gx = 48, cw = (Z.w - gx * (cols - 1)) / cols, gy = 10, rh = (Z.h - gy * (per - 1)) / per;
  var ls = Math.min(56, rh), tw = cw - ls - 30, H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, tw, rh * 0.45, rh - 8, {hMax:20, dMax:15.5, hMin:13, dMin:11, hLines:1}) : null;
  var lsz = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 6, {font:'head', max:n <= 4 ? 28 : 22, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * (rh + gy), cy = y + rh / 2, col = at(tn(k, n));
    D.glow(C, x + ls / 2, cy, ls * 0.8, ls * 0.8, col, 0.15 + 0.4 * tn(k, n));
    C.shape('diamond', x + ls * 0.2, cy - ls * 0.3, ls * 0.6, ls * 0.6, {fill:col});
    C.text(x + ls * 0.2, cy - ls * 0.3, ls * 0.6, ls * 0.6, String(k + 1), {font:'head', size:Math.max(11, Math.round(ls * 0.22)), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (H2) hdc(C, x + ls + 22, y, tw, rh, t, F, {tc:INK});
    else C.text(x + ls + 22, y, tw, rh, t.d, {font:'head', size:lsz, color:INK, va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    if (r < per - 1 && k < n - 1) C.rect(x + ls + 22, y + rh + gy / 2, tw, 1.5, {grad:{ang:90, stops:[[0, col, 0.9], [100, col, 0]]}});
  });
}

/* 8. before / after: outlined card, gradient card, diamond hinge */
function shiftDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var g = 60, cw = (Z.w - g) / 2, pad = cw > 400 ? 40 : 26, iw = cw - pad * 2;
  var F = K.fitHD(C, it, iw, 80, Z.h - pad * 2 - 56, {hMax:26, dMax:18, hMin:14, dMin:11.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), lit = k === 1;
    if (lit) { D.glow(C, x + cw / 2, Z.y + Z.h / 2, cw * 0.62, Z.h * 0.62, '#2DBE8C', 0.3); C.rect(x, Z.y, cw, Z.h, {grad:{ang:135, stops:[[0, deep(0.15)], [100, deep(0.95)]]}, r:22}); }
    else C.rect(x, Z.y, cw, Z.h, {fill:'#FFFFFF', line:'#DDE0E7', lw:1.5, r:22});
    K.pill(C, x + pad, Z.y + pad, 64, 30, u.pad2(k + 1), lit ? {fill:'#FFFFFF', color:INK, size:13, wt:800} : {fill:'#EEF0F4', color:INK, size:13, wt:800});
    hdc(C, x + pad, Z.y + pad + 50, iw, Z.h - pad * 2 - 50, t, F, {tc:lit ? '#FFFFFF' : INK, mc:lit ? '#E4ECF4' : P.muted});
  });
  if (n > 1) {
    var cx = Z.x + cw + g / 2, cy = Z.y + Z.h / 2, s = 92;
    C.shape('diamond', cx - s / 2, cy - s / 2, s, s, {fill:'#F2F3F6'});
    C.shape('diamond', cx - s / 2 + 10, cy - s / 2 + 10, s - 20, s - 20, {grad:GR});
    K.poly(C, [[cx - 9, cy - 13], [cx + 11, cy], [cx - 9, cy + 13]], {fill:'#FFFFFF'});
  }
}

/* 9. lit statement */
function glowStatement(C, Z, o){
  var P = C.P, t = C.it[0], dx = Z.x + Z.w - Math.min(260, Z.w * 0.24);
  D.glow(C, dx + 90, Z.y + Z.h / 2, 220, 200, '#2DBE8C', 0.25);
  [[0, 150, 0.9], [60, 90, 0.6], [-40, 60, 0.4]].forEach(function(q, j){ C.shape('diamond', dx + 90 + q[0] - q[1] / 2, Z.y + Z.h / 2 - q[0] * 0.9 - q[1] / 2, q[1], q[1], {line:at(j / 2), lw:3, lop:q[2]}); });
  C.shape('diamond', dx + 60, Z.y + Z.h / 2 - 30, 60, 60, {grad:GR});
  C.rect(Z.x, Z.y + 10, 8, Z.h - 20, {grad:{ang:180, stops:GS}, r:4});
  var x = Z.x + 44, w = dx - x - 40;
  if (!t) { C.text(x, Z.y, w, Z.h, C.s.subtitle || '', {font:'head', fit:{max:36, min:18}, color:INK, va:'m', lh:1.3, ed:{f:'subtitle'}}); return; }
  if (t.h) K.pill(C, x, Z.y + 20, Math.min(w, t.h.length * 11 + 50), 34, t.h, {fill:'#FFFFFF', line:'#DDE0E7', color:INK, size:13, wt:800, caps:true, ls:0.1, ed:{f:'x-part', i:t.i, p:0}});
  C.text(x, Z.y + (t.h ? 74 : 20), w, Z.h - (t.h ? 94 : 40), t.d, {font:'head', fit:{max:38, min:18}, color:INK, va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'lumen', name:'Lumen Gradient', tag:'Lime-to-indigo · frameworks', desc:'Light grey pages lit by a lime-to-indigo gradient, diamond milestones, rising paths and badge cards for frameworks and funnels.',
  fonts:{head:'Manrope', body:'Manrope', headW:800, bodyW:500, headLh:1.06, gf:'Manrope:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Gradient steps',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Gradient steps',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Lamp rows',{body:lampRows}), V('n2','Gradient tiles',{body:spectrumTiles}), V('v2','Lamps + image right',{body:lampRows, img:'right'}), V('n3','Numbered list',{body:'agList'})],
    text:[V('v1','Lit statement',{body:glowStatement}), V('n2','Statement card',{body:'text', box:true}), V('v2','Lit statement + image right',{body:glowStatement, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Before / after',{body:shiftDuo}), V('n2','Light beams',{body:lightBeams}), V('n3','Gradient tiles',{body:spectrumTiles}), V('v2','Image left + lamps',{body:lampRows, img:'left'}), V('v3','Tiles + image right',{body:spectrumTiles, img:'right'})],
    p3:[V('v1','Light beams',{body:lightBeams}), V('n2','Gradient tiles',{body:spectrumTiles}), V('n3','Spectrum line',{body:spectrumLine}), V('n4','Rising path',{body:'xRise'}), V('n5','Lamp rows',{body:lampRows}), V('v2','Image left + lamps',{body:lampRows, img:'left'}), V('v3','Beams + image right',{body:lightBeams, img:'right'})],
    p4:[V('v1','Diamond cluster',{body:diamondCluster}), V('n2','Light beams',{body:lightBeams}), V('n3','Gradient tiles',{body:spectrumTiles}), V('n4','Spectrum line',{body:spectrumLine}), V('n5','Rising path',{body:'xRise'}), V('v2','Lamps + image right',{body:lampRows, img:'right'}), V('v3','Image left + tiles',{body:spectrumTiles, img:'left'})],
    p56:[V('v1','Gradient tiles',{body:spectrumTiles}), V('n2','Light beams',{body:lightBeams}), V('n3','Lamp rows',{body:lampRows}), V('n4','Spectrum line',{body:spectrumLine}), V('n5','Card grid',{body:'grid'}), V('v2','Lamps + image right',{body:lampRows, img:'right'})],
    p78:[V('v1','Gradient tiles',{body:spectrumTiles}), V('n2','Lamp rows',{body:lampRows}), V('n3','Card grid',{body:'grid'}), V('v2','Lamps + image right',{body:lampRows, img:'right'})],
    stats:[V('v1','Level meters',{body:levelStats}), V('n2','Halo numbers',{body:haloStats}), V('n3','Progress rings',{body:'statRings'}), V('v2','Image left + halos',{body:haloStats, img:'left'}), V('v3','Meters + image right',{body:levelStats, img:'right'})],
    timeline:[V('v1','Spectrum line',{body:spectrumLine}), V('n2','Light beams',{body:lightBeams}), V('n3','Rising path',{body:'xRise'}), V('v2','Beams + image right',{body:lightBeams, img:'right'})],
    quote:[V('v1','Gradient quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 19 — Mint & Lilac : airy off-white, mint → aqua → periwinkle → lilac pastels, soft 3D shapes, navy roads */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var SER = ['#5FD6B4', '#49BFE0', '#7F95F5', '#A081EE', '#62D3CF', '#8C7BF0'];
var NAVY = '#1B1F4B';
var P0 = {bg:'#F7F8FC', text:'#1B1F4B', title:'#1B1F4B', muted:'#5A5F80', accent:'#7F95F5', accent2:'#5FD6B4', card:'#FFFFFF', line:'#E1E4F0', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:SER,
  header:{kind:'left', tMax:40, tMin:24, sMax:16, top:50},
  card:{kind:'solid', r:22, fill:'#FFFFFF', shadow:0.06, hl:{kind:'grad', grad:{ang:135, stops:[[0, '#5FD6B4'], [100, '#A081EE']]}, dark:true}},
  icon:{kind:'soft', bg:'#7F95F5', op:0.18},
  num:{kind:'circle', bg:'#7F95F5', fg:'#FFFFFF'},
  conc:{kind:'pill', fill:'#A081EE', op:0.14, lc:'#7F4FE0', label:'Takeaway'},
  footer:{brand:true, page:true, color:'#9A9EB8'}, xRows:'#FFFFFF', hubFill:'#EEF0FA', road:NAVY, flowBar:'#E6E8F4',
  imgFramed:true, imgR:26
};
/* stacked discs — drawn 1:1 from the supplied artwork (12 ellipses); on a colour theme the two light rings and their white tops use theme tints, so the top of the stack never blends into the background */
function coil(C, cx, cy, s){
  var f = s / 204.44, th = !!(C.T && C.T.__rec), PG = th ? '#C4C9F2' : '#FFFFFF', RG = th ? '#A3A3FF' : '#CCCCFF';
  /* on a colour theme the lower discs get clear dark / light steps, so each layer stays readable in one-colour themes (red, green ...) */
  var M = th ? {'#6633FF':'#3D1FCC', '#6666FF':'#6C6CFF', '#6699FF':'#5C85F0', '#66CCFF':'#8FD8FF', '#9933FF':'#7A29E6'} : {};
  [[325.19, 484.74, 204.44, 61.33, '#6633FF'], [325.19, 440.89, 204.44, 61.33, '#6666FF'], [323.11, 433.48, 131.36, 39.41, '#6633FF'],
   [325.19, 379.65, 204.44, 61.33, '#6699FF'], [325.19, 335.80, 204.44, 61.33, '#66CCFF'], [323.11, 328.40, 131.36, 39.41, '#9933FF'],
   [325.19, 274.57, 204.44, 61.33, RG], [325.19, 230.72, 204.44, 61.33, PG], [323.11, 223.31, 131.36, 39.41, '#9999FF'],
   [325.19, 169.48, 204.44, 61.33, RG], [325.19, 125.63, 204.44, 61.33, PG], [323.11, 118.22, 131.36, 39.41, '#9999FF']
  ].forEach(function(e){ C.ell(cx + (e[0] - 325.19 - e[2]) * f, cy + (e[1] - 305.2 - e[3]) * f, 2 * e[2] * f, 2 * e[3] * f, {fill:M[e[4]] || e[4]}); });
}
function bg(C, kind){
  C.bg({grad:{ang:135, stops:[[0, '#F7F8FC'], [100, '#EEF1FB']]}});
  D.glow(C, W - 80, 60, 240, 180, '#A081EE', 0.16); D.glow(C, 60, H - 40, 240, 180, '#5FD6B4', 0.14);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, W - 540, 40, 500, H - 80, {r:34}); else coil(C, W - 300, 360, 190);
  var x = 64, w = W * 0.52;
  if (f.company) C.text(x, 90, w, 22, f.company, {font:'body', size:14, wt:800, color:'#7F4FE0', caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 130, w, 300, C.s.title, {font:'head', fit:{max:68, min:32}, color:NAVY, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  if (C.s.subtitle) C.text(x, 456, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) K.pill(C, x, 600, Math.min(w, meta.length * 8 + 60), 40, meta, {fill:'#FFFFFF', color:NAVY, size:14, wt:600});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 500, H - 80, {r:34}); else coil(C, 300, 360, 180);
  var x = 600, w = W - 664;
  C.text(x, 170, w, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(x, 384, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 480, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#7F95F5'}); return; }
  C.rect(150, 100, W - 300, H - 200, {fill:'#FFFFFF', r:40, shadow:0.06});
  D.B.quote(C, {x:220, y:150, w:W - 440, h:H - 300}, {qc:'#7F95F5'});
}

/* ---------- signature helpers ---------- */
function LP(c, t){ return u.mix(c, '#FFFFFF', t == null ? 0.55 : t); }
function DP(c){ return u.mix(c, NAVY, 0.35); }
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function pct(v, k, n){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(8, Math.min(100, parseFloat(m[1]))) : 55 + 40 * (n <= 1 ? 1 : k / (n - 1)); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + descH(C, t, w, F); }
function hdc(C, x, y, w, h, t, F, o){ var bh = blockH(C, t, w, F), yy = y + Math.max(0, (h - bh) / 2); K.hd(C, x, yy, w, h - (yy - y), t, F, o); }
function numPill(C, x, y, k, c, o){ o = o || {}; K.pill(C, x, y, o.w || 54, o.h || 28, u.pad2(k + 1), {fill:o.fill || '#FFFFFF', color:NAVY, size:o.size || 13, wt:800, line:o.line, lw:1.5}); }

/* 1. two overlapping pastel circles (venn) */
function vennDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var d = Math.min(Z.h + 10, Z.w / 1.75), ov = d * 0.22, cy = Z.y + Z.h / 2, cx0 = Z.x + Z.w / 2 - d / 2 + ov / 2, cx1 = Z.x + Z.w / 2 + d / 2 - ov / 2;
  var tw = d * 0.56, th = d * 0.56;
  var F = K.fitHD(C, it, tw, 70, th - 40, {hMax:24, dMax:17, hMin:14, dMin:11.5});
  [cx0, cx1].slice(0, n).forEach(function(cx, k){
    var c = k ? '#A081EE' : '#5FD6B4';
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:c, op:0.3});
    var tx = k ? cx - d / 2 + ov + 22 : cx - d * 0.4, t = it[k];
    numPill(C, tx, cy - th / 2 - 8, k, c, {fill:'#FFFFFF'});
    hdc(C, tx, cy - th / 2 + 32, tw, th - 32, t, F, {tc:NAVY, mc:'#3E4368'});
  });
  if (n > 1) { var mx = Z.x + Z.w / 2; C.ell(mx - 30, cy - 30, 60, 60, {fill:'#FFFFFF', shadow:0.08}); C.text(mx - 30, cy - 30, 60, 60, '&', {font:'head', size:26, wt:800, color:'#7F4FE0', align:'c', va:'m', lh:1, caps:false, ls:0}); }
}

/* 2. items inside soft organic blobs, gently bobbing */
function blobCluster(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; if (n > 4) return pillRows(C, Z, o); var P = C.P;
  var cw = Z.w / n, r = Math.min(cw / 2 - 4, Z.h / 2 - Z.h * 0.05), tw = r * 1.3, th = r * 1.08;
  var F = K.fitHD(C, it, tw, 60, th - 36, {hMax:20, dMax:15, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var c = SER[k % 4], cx = Z.x + cw * (k + 0.5), cy = Z.y + Z.h / 2 + (k % 2 ? 1 : -1) * Z.h * 0.04;
    K.blob(C, cx + r * 0.08, cy + r * 0.1, r * 0.98, c, 0.35, k + 2);
    K.blob(C, cx, cy, r, LP(c, 0.5), 1, k + 5);
    numPill(C, cx - 27, cy - th / 2 - 14, k, c, {fill:'#FFFFFF'});
    hdc(C, cx - tw / 2, cy - th / 2 + 24, tw, th - 24, t, F, {align:'c', tc:NAVY, mc:'#3E4368'});
  });
}

/* 3. soft rows led by a pastel pill tag */
function pillRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = n > 5 && Z.w > 800 ? 2 : 1, per = Math.ceil(n / cols), gx = 28, cw = (Z.w - gx * (cols - 1)) / cols, gy = n > 6 ? 10 : 14, rh = (Z.h - gy * (per - 1)) / per;
  var H2 = hasH(it), pw = H2 ? Math.min(250, cw * 0.36) : Math.min(64, rh * 1.3), ph = Math.min(rh - 12, 52), dx = pw + 34, dw = cw - dx - 22;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), pw - 34, ph - 6, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 10, H2 ? {font:'body', max:n <= 3 ? 19 : 16, min:11, lh:1.35} : {font:'head', max:n <= 4 ? 26 : 21, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * (rh + gy), col = SER[k % 4];
    C.rect(x, y, cw, rh, {fill:'#FFFFFF', r:Math.min(rh / 2, 26), shadow:0.05});
    C.rect(x + 8, y + (rh - ph) / 2, pw, ph, {fill:LP(col, 0.45), r:ph / 2});
    if (H2) C.text(x + 26, y + (rh - ph) / 2, pw - 34, ph, t.h || u.pad2(k + 1), {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:2});
    else C.text(x + 8, y + (rh - ph) / 2, pw, ph, u.pad2(k + 1), {font:'head', size:Math.min(20, ph * 0.42), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + dx, y, dw, rh, t.d, H2 ? {font:'body', size:ds, color:P.muted, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}} : {font:'head', size:ds, color:NAVY, va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
  });
}

/* 4. racetrack loop: cards ride a soft rounded track, arrows at the turns */
function loopTiles(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var a = Math.ceil(n / 2), b = n - a, gyy = 34, ch = (Z.h - gyy) / 2, y1 = Z.y + ch / 2, y2 = Z.y + Z.h - ch / 2, R = (y2 - y1) / 2, cy = (y1 + y2) / 2;
  var xl = Z.x + R, xr = Z.x + Z.w - R, g = 18, cw = (xr - xl + R * 0.9 - g * (a - 1)) / a, cx0 = xl - R * 0.45;
  var d = [['M', xl, y1], ['L', xr, y1]].concat(K.arcCmds(xr, cy, R, -90, 90, 'L')).concat([['L', xl, y2]]).concat(K.arcCmds(xl, cy, R, 90, 270, 'L'));
  C.path(d, {stroke:'#D9DDF7', lw:16});
  C.path(d, {stroke:'#A081EE', lw:2.5, lop:0.7});
  K.poly(C, [[xr + R - 13, cy - 10], [xr + R + 13, cy - 10], [xr + R, cy + 10]], {fill:'#7F95F5'});
  K.poly(C, [[xl - R - 13, cy + 10], [xl - R + 13, cy + 10], [xl - R, cy - 10]], {fill:'#5FD6B4'});
  var pad = 20, F = K.fitHD(C, it, cw - pad * 2, 48, ch - pad * 2 - 40, {hMax:20, dMax:16, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var top = k < a, slot = top ? k : (b - 1) - (k - a), x = cx0 + slot * (cw + g) + (top ? 0 : (a - b) * (cw + g) / 2), y = top ? Z.y : Z.y + ch + gyy, c = SER[k % 4];
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:24, shadow:0.07});
    C.rect(x, y, cw, 8, {fill:LP(c, 0.2), r:4});
    numPill(C, x + pad, y + pad, k, c, {fill:LP(c, 0.55)});
    hdc(C, x + pad, y + pad + 38, cw - pad * 2, ch - pad * 2 - 38, t, F, {tc:NAVY});
  });
}

/* 5. overlapping bubbles for stats */
function bubbleStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, iw = cw - 20;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 48, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 40, {font:'body', max:14, min:11, maxLines:2});
  var bot = ls * 2.45 + (st.some(function(t){ return t.d; }) ? ds * 2.8 + 6 : 0) + 20;
  var D = Math.min(Z.h - bot - 10, cw * 1.2, 300), top = Z.y + Math.max(0, (Z.h - bot - D - 10) / 2), cy = top + D / 2;
  var sz = st.map(function(t, k){ return D * (0.72 + 0.28 * pct(t.v, k, n) / 100); });
  var vs = C.groupFit(st.map(function(t){ return t.v; }), Math.min.apply(null, sz) * 0.72, 80, {font:'head', max:60, min:22, maxLines:1});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), d = sz[k], c = SER[k % 4];
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:LP(c, 0.3), op:0.92, line:'#FFFFFF', lw:6});
    C.text(cx - d * 0.4, cy - vs * 0.6, d * 0.8, vs * 1.2, t.v, {font:'head', size:vs, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
  });
  st.forEach(function(t, k){
    var x = Z.x + cw * k + 10, ly = cy + D / 2 + 22;
    C.text(x, ly, iw, ls * 2.45, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x, ly + ls * 2.45 + 2, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 6. stats as big soft pills with the number in a white disc */
function pillStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cols = n > 2 && Z.w > 700 ? 2 : 1, rows = Math.ceil(n / cols), gx = 26, gy = 22, cw = (Z.w - gx * (cols - 1)) / cols, rh = (Z.h - gy * (rows - 1)) / rows;
  var ph = Math.min(rh, 150), dd = ph - 20, tw = cw - dd - 60;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), dd * 0.8, dd * 0.5, {font:'head', max:44, min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), tw, ph * 0.45, {font:'head', max:n <= 2 ? 28 : 22, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), tw, ph * 0.35, {font:'body', max:15, min:11, maxLines:2});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + gx) + (cols - inRow) * (cw + gx) / 2, y = Z.y + r * (rh + gy) + (rh - ph) / 2, c = SER[k % 4];
    C.rect(x, y, cw, ph, {fill:LP(c, 0.45), r:ph / 2});
    C.ell(x + 10, y + 10, dd, dd, {fill:'#FFFFFF', shadow:0.06});
    C.text(x + 10 + dd * 0.1, y + 10, dd * 0.8, dd, t.v, {font:'head', size:vs, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:-0.01, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    var lh = C.measureText(t.l, tw, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.d ? ds * 1.36 * Math.min(2, C.measureText(t.d, tw, {font:'body', max:ds, min:ds}).n) : 0, ty = y + (ph - lh - (dh ? dh + 6 : 0)) / 2;
    C.text(x + dd + 36, ty, tw, lh + 4, t.l, {font:'head', size:ls, color:NAVY, lh:1.2, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(x + dd + 36, ty + lh + 6, tw, dh + 4, t.d, {font:'body', size:ds, color:'#3E4368', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 7. stepping-stone path: dotted wave with pebbles and pill tags */
function stonePath(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, mid = Z.y + Z.h / 2, A = Math.min(34, Z.h * 0.07), N = 90;
  function yAt(x){ return mid + A * Math.sin((x - Z.x) / Z.w * Math.PI * n); }
  for (var i = 0; i <= N; i++) { var x = Z.x + Z.w * i / N; C.ell(x - 3, yAt(x) - 3, 6, 6, {fill:'#B9C2F5'}); }
  var F = K.fitHD(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), cw - 16, 1, Z.h / 2 - A - 110, {dMax:19, dMin:11});
  var ps = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - 40, 22, {font:'head', max:17, min:13, maxLines:1});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = mid + A * Math.sin((k + 0.5) * Math.PI), c = SER[k % 4], r = Math.min(52, cw * 0.22), up = k % 2 === 0;
    K.blob(C, cx + 4, cy + 6, r, c, 0.35, k + 3);
    K.blob(C, cx, cy, r, LP(c, 0.2), 1, k + 7);
    C.text(cx - r, cy - r, r * 2, r * 2, u.pad2(k + 1), {font:'head', size:Math.round(r * 0.5), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    var pw = Math.min(cw - 16, Math.max(84, (t.h || '').length * ps * 0.7 + 34)), dh = descH(C, t, cw - 16, F);
    var py = up ? cy - r - 30 - 36 - dh : cy + r + 22;
    K.pill(C, cx - pw / 2, py, pw, 34, t.h || u.pad2(k + 1), {fill:'#FFFFFF', line:LP(c, 0.2), lw:2, color:NAVY, size:ps, wt:800, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(Z.x + cw * k + 8, py + 44, cw - 16, dh + 4, t.d, {font:'body', size:F.ds, color:P.text, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 8. rounded white tiles with a pastel number blob */
function softTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : Math.ceil(n / 2))), rows = Math.ceil(n / cols);
  var gx = 22, gyy = 22, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gyy * (rows - 1)) / rows, pad = 24, br = rows > 1 ? 24 : 32;
  var H2 = hasH(it), hw = cw - pad * 2 - br * 2 - 14, zy = Z.y;
  if (!H2 && ch > 150) { zy = Z.y + (ch - 150) * rows / 2 * 0.8; ch = 150; }
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), hw, br * 2, {font:'head', max:rows > 1 ? 19 : 22, min:13, maxLines:2});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - pad * 2, ch - pad * 2 - br * 2 - 16, {font:'body', max:rows > 1 ? 16 : 19, min:11, lh:1.38});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + gx) + (cols - inRow) * (cw + gx) / 2, y = zy + r * (ch + gyy), c = SER[k % 4];
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:26, shadow:0.06});
    C.ell(x + cw - 64, y + ch - 50, 44, 44, {fill:LP(c, 0.6)}); C.ell(x + cw - 40, y + ch - 70, 22, 22, {fill:LP(c, 0.75)});
    K.blob(C, x + pad + br, y + pad + br, br, LP(c, 0.35), 1, k + 1);
    C.text(x + pad, y + pad, br * 2, br * 2, u.pad2(k + 1), {font:'head', size:Math.round(br * 0.6), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad + br * 2 + 14, y + pad, hw, br * 2, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.12, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    if (t.h) C.text(x + pad, y + pad + br * 2 + 16, cw - pad * 2 - 30, ch - pad * 2 - br * 2 - 16, t.d, {font:'body', size:ds, color:P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* 9. statement on a soft double blob */
function blobStatement(C, Z, o){
  var P = C.P, t = C.it[0], cx = Z.x + Z.w / 2, cy = Z.y + Z.h / 2, r = Math.min(Z.h * 0.62, Z.w * 0.42);
  K.blob(C, cx - r * 0.25, cy, r, '#5FD6B4', 0.22, 2); K.blob(C, cx + r * 0.3, cy + 10, r * 0.95, '#A081EE', 0.22, 5);
  var w = Math.min(Z.w - 80, r * 1.9), x = cx - w / 2;
  if (!t) { C.text(x, Z.y, w, Z.h, C.s.subtitle || '', {font:'head', fit:{max:36, min:18}, color:NAVY, align:'c', va:'m', lh:1.3, ed:{f:'subtitle'}}); return; }
  if (t.h) K.pill(C, cx - Math.min(w, t.h.length * 11 + 60) / 2, Z.y + 24, Math.min(w, t.h.length * 11 + 60), 36, t.h, {fill:'#FFFFFF', color:'#7F4FE0', size:14, wt:800, caps:true, ls:0.1, ed:{f:'x-part', i:t.i, p:0}});
  C.text(x, Z.y + (t.h ? 80 : 30), w, Z.h - (t.h ? 110 : 60), t.d, {font:'head', fit:{max:36, min:18}, color:NAVY, align:'c', va:'m', lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'pastel', name:'Mint & Lilac', tag:'Soft pastel · marketing', desc:'Airy pages with mint, aqua, periwinkle and lilac pastels, rounded cards, soft 3D coils and navy roadmaps.',
  fonts:{head:'Plus Jakarta Sans', body:'Plus Jakarta Sans', headW:800, bodyW:500, headLh:1.06, gf:'Plus Jakarta Sans:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Pastel coil',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Pastel coil',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Pill rows',{body:pillRows}), V('n2','Soft tiles',{body:softTiles}), V('v2','Pill rows + image right',{body:pillRows, img:'right'}), V('n3','Numbered tiles',{body:'agTiles'})],
    text:[V('v1','Blob statement',{body:blobStatement}), V('n2','Statement card',{body:'text', box:true}), V('v2','Blob statement + image right',{body:blobStatement, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Venn circles',{body:vennDuo}), V('n2','Soft blobs',{body:blobCluster}), V('n3','Pill rows',{body:pillRows}), V('v2','Image left + pill rows',{body:pillRows, img:'left'}), V('v3','Tiles + image right',{body:softTiles, img:'right'})],
    p3:[V('v1','Soft blobs',{body:blobCluster}), V('n2','Loop track',{body:loopTiles}), V('n3','Pill rows',{body:pillRows}), V('n4','Soft tiles',{body:softTiles}), V('n5','Cycle',{body:'xCycle'}), V('v2','Image left + pill rows',{body:pillRows, img:'left'}), V('v3','Tiles + image right',{body:softTiles, img:'right'})],
    p4:[V('v1','Loop track',{body:loopTiles}), V('n2','Soft blobs',{body:blobCluster}), V('n3','Soft tiles',{body:softTiles}), V('n4','Pill rows',{body:pillRows}), V('n5','Cycle',{body:'xCycle'}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    p56:[V('v1','Loop track',{body:loopTiles}), V('n2','Soft tiles',{body:softTiles}), V('n3','Pill rows',{body:pillRows}), V('n4','Cycle',{body:'xCycle'}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    p78:[V('v1','Soft tiles',{body:softTiles}), V('n2','Pill rows',{body:pillRows}), V('n3','Loop track',{body:loopTiles}), V('n4','Cycle',{body:'xCycle'}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    stats:[V('v1','Bubble chain',{body:bubbleStats}), V('n2','Stat pills',{body:pillStats}), V('n3','Progress rings',{body:'statRings'}), V('v2','Image left + pills',{body:pillStats, img:'left'}), V('v3','Bubbles + image right',{body:bubbleStats, img:'right'})],
    timeline:[V('v1','Stepping stones',{body:stonePath}), V('n2','Pill rows',{body:pillRows}), V('n3','Loop track',{body:loopTiles}), V('v2','Pill rows + image right',{body:pillRows, img:'right'})],
    quote:[V('v1','Soft card quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 20 — Horizon Launch : pale sky pages with faint four-point stars, navy + orange + sunflower + sky, uppercase heads */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#0F2557', ORNG = '#F58A1F', SUN = '#F7B928', SKY = '#5AB4E5';
var P0 = {bg:'#EEF3FB', text:'#0F2557', title:'#0F2557', muted:'#4C5B80', accent:'#F58A1F', accent2:'#5AB4E5', card:'#FFFFFF', line:'#D3DDEE', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[ORNG, SUN, NAVY, SKY, '#2E4D8F', '#F9A65A'],
  header:{kind:'center', tMax:40, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'solid', r:12, fill:'#FFFFFF', shadow:0.07, hl:{kind:'solid', fill:NAVY, dark:true}},
  icon:{kind:'circle', bg:ORNG, fg:'#FFFFFF'},
  num:{kind:'circle', bg:NAVY, fg:'#FFFFFF'},
  conc:{kind:'band', fill:NAVY, r:12, label:'Next move'},
  footer:{brand:true, page:true, color:'#7C8AAB'}, xRows:'#FFFFFF', hubFill:'#DFE8F5', road:NAVY, flowBar:'#D9E3F2',
  imgFramed:true, imgR:14
};
function stars(C, seed){
  var pts = [[90, 90, 40], [W - 120, 110, 52], [220, H - 90, 34], [W - 260, H - 70, 44], [W / 2 + 40, 40, 26], [40, H / 2, 30], [W - 40, H / 2 + 60, 28]];
  pts.forEach(function(p, k){ K.spark(C, p[0], p[1], p[2], '#D2DFF3', 1); });
}
function bg(C, kind){ C.bg({fill:'#EEF3FB'}); stars(C); }
function wave(C, y, amp, x0, x1, pins){
  var pts = [], N = 60;
  for (var i = 0; i <= N; i++) { var t = i / N; pts.push([x0 + t * (x1 - x0), y - amp * Math.abs(Math.sin(t * Math.PI * 3.2)) * (0.6 + 0.4 * t)]); }
  K.curve(C, pts, {stroke:NAVY, lw:26});
  (pins || []).forEach(function(pk, k){ var p = pts[pk]; C.ell(p[0] - 22, p[1] - 22, 44, 44, {fill:'#FFFFFF', line:[ORNG, SUN, SKY][k % 3], lw:6}); });
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, W - 520, 40, 480, H - 80, {r:18});
  else wave(C, 640, 150, 60, W - 40, [30, 42, 54]);
  var x = 64, w = o.img ? 620 : 700;
  if (f.company || f.date) C.text(x, 56, w, 20, [f.company, f.date].filter(Boolean).join('   ·   '), {font:'body', size:13, wt:700, color:P.muted, caps:true, ls:0.14});
  C.text(x, 100, w, o.img ? 300 : 230, C.s.title, {font:'head', fit:{max:64, min:30}, color:NAVY, ed:{f:'title'}, maxLines:4, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(x, o.img ? 420 : 344, w, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  if (f.website) C.text(x, H - 60, 400, 20, f.website, {font:'body', size:13, wt:700, color:NAVY});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 460, H - 80, {r:18}); else wave(C, 650, 90, 40, W - 40, [20, 40]);
  var x = o.img ? 560 : 120, w = o.img ? W - 620 : W - 240, al = o.img ? 'l' : 'c';
  C.text(x, 120, w, 190, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:1});
  if (C.s.subtitle) C.text(x, 330, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4, align:al});
  D.contactRow(C, x, 420, w, {cards:true});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:ORNG}); return; }
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:ORNG});
}

/* ---------- signature helpers ---------- */
function sItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), d:t.x, own:!!t.h}; }); }
function hasH(it){ return it.some(function(t){ return !!t.h; }); }
function descH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + descH(C, t, w, F); }
function hdc(C, x, y, w, h, t, F, o){ var bh = blockH(C, t, w, F), yy = y + Math.max(0, (h - bh) / 2); K.hd(C, x, yy, w, h - (yy - y), t, F, o); }
var SKYS = ['#D6E6F8', '#A9D2F1', '#8CC8EC', '#D5D3EE', '#FBDDB0', '#F7C95A', '#F9A65A', '#F58A1F'];
function skyAt(t){ t = Math.max(0, Math.min(1, t)) * (SKYS.length - 1); var i = Math.floor(t), f = t - i; return i >= SKYS.length - 1 ? SKYS[SKYS.length - 1] : u.mix(SKYS[i], SKYS[i + 1], f); }
function sun(C, cx, cy, r, c, rays){
  K.seg(C, cx, cy, 0, r, 180, 360, {fill:c});
  if (rays) for (var a = 195; a <= 345; a += 25) { var p0 = K.pol(cx, cy, r + 8, a), p1 = K.pol(cx, cy, r + 8 + rays, a); C.line(p0[0], p0[1], p1[0], p1[1], {color:c, lw:3}); }
}
function starBadge(C, cx, cy, d, k, fill){
  C.ell(cx - d / 2, cy - d / 2, d, d, {fill:fill || NAVY});
  C.text(cx - d / 2, cy - d / 2, d, d, u.pad2(k + 1), {font:'head', size:Math.round(d * 0.38), wt:800, color:K.on(fill || NAVY), align:'c', va:'m', lh:1, caps:false, ls:0});
  K.spark(C, cx + d * 0.42, cy - d * 0.42, d * 0.3, SUN, 1);
}

/* 1. launch trajectory: stars along a rising arc, each text on its own altitude band */
function launchArc(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var bh = Z.h / n, aw = Math.min(Z.w * 0.4, 440), pts = [];
  for (var k = 0; k < n; k++) { var t = n === 1 ? 0.5 : k / (n - 1); pts.push([Z.x + 30 + aw * Math.pow(t, 0.55), Z.y + Z.h - bh * (k + 0.5)]); }
  var tr = [[Z.x + 4, Z.y + Z.h + 10]].concat(pts).concat([[Z.x + 30 + aw + 60, Z.y - 6]]);
  K.curve(C, tr, {stroke:ORNG, lw:3});
  K.curve(C, tr.map(function(p){ return [p[0] - 10, p[1] + 8]; }), {stroke:SUN, lw:1.5, lop:0.8});
  var tx = Z.x + 30 + aw + 90, tw = Z.x + Z.w - tx;
  var F = K.fitHD(C, it, tw - 70, bh * 0.45, bh - 8, {hMax:20, dMax:16, hMin:13, dMin:11, hLines:1});
  it.forEach(function(t, k){
    var p = pts[k], c = [ORNG, SUN, SKY, NAVY][k % 4], y = Z.y + Z.h - bh * (k + 1);
    K.spark(C, p[0], p[1], 20, c, 1); C.ell(p[0] - 4, p[1] - 4, 8, 8, {fill:'#FFFFFF'});
    C.line(p[0] + 24, p[1], tx - 12, p[1], {color:'#B8C6E0', lw:1.5, dash:true});
    starBadge(C, tx + 20, p[1], 40, k, k === n - 1 ? ORNG : NAVY);
    hdc(C, tx + 58, y, tw - 60, bh, t, F, {tc:NAVY});
  });
}

/* 2. star-badge cards */
function starGrid(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = Z.w < 820 ? Math.min(n, 2) : (n <= 4 ? n : (n <= 6 ? 3 : Math.ceil(n / 2))), rows = Math.ceil(n / cols);
  var gx = 20, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, pad = 22, bd = rows > 1 ? 40 : 50;
  var H2 = hasH(it), top = pad + bd + 16;
  var F = H2 ? K.fitHD(C, it, cw - pad * 2, 56, ch - top - pad - 8, {hMax:19, dMax:15.5, hMin:13, dMin:11}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - top - pad, {font:'head', max:20, min:13, maxLines:2});
  var zy = Z.y;
  if (rows === 1 || !H2) { var mb = 0; it.forEach(function(t){ mb = Math.max(mb, H2 ? blockH(C, t, cw - pad * 2, F) : ls * 2.3); }); var need = top + mb + pad + 24; if (need < ch) { zy = Z.y + (ch - need) * rows * 0.4; ch = need; } }
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), cc = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + cc * (cw + gx) + (cols - inRow) * (cw + gx) / 2, y = zy + r * (ch + gy);
    C.rect(x, y, cw, ch, {fill:'#FFFFFF', r:12, shadow:0.07});
    C.rect(x, y + ch - 6, cw, 6, {fill:[ORNG, SUN, SKY, NAVY][k % 4], r:3});
    K.spark(C, x + cw - 30, y + 28, 12, '#D2DFF3', 1);
    starBadge(C, x + pad + bd / 2, y + pad + bd / 2 + 4, bd, k, k % 2 ? ORNG : NAVY);
    if (H2) K.hd(C, x + pad, y + top, cw - pad * 2, ch - top - pad, t, F, {tc:NAVY});
    else C.text(x + pad, y + top, cw - pad * 2, ch - top - pad, t.d, {font:'head', size:ls, color:NAVY, lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:2});
  });
}

/* 3. sunrise bands: rows shading from pale sky to orange, sun rising at the end */
function horizonBands(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = n > 6 && Z.w > 800 ? 2 : 1, per = Math.ceil(n / cols), gx = 24, cw = (Z.w - gx * (cols - 1)) / cols, gy = 6, rh = (Z.h - gy * (per - 1)) / per;
  var nw = Math.min(90, rh * 1.3), tw = cw - nw - 40, H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, tw, rh * 0.45, rh - 8, {hMax:19, dMax:15.5, hMin:13, dMin:11, hLines:1}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 6, {font:'head', max:n <= 4 ? 26 : 20, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * (rh + gy), col = skyAt(n === 1 ? 0 : k / (n - 1));
    C.rect(x, y, cw, rh, {fill:col, r:8});
    C.text(x + 10, y, nw, rh, u.pad2(k + 1), {font:'head', size:Math.min(34, rh * 0.5), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0, op:0.9});
    C.rect(x + nw + 14, y + rh * 0.22, 2, rh * 0.56, {fill:NAVY, op:0.35});
    if (H2) hdc(C, x + nw + 30, y, tw - 60, rh, t, F, {tc:NAVY, mc:'#27375E'});
    else C.text(x + nw + 30, y, tw - 60, rh, t.d, {font:'head', size:ls, color:NAVY, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    K.spark(C, x + cw - 30, y + rh / 2, Math.min(12, rh * 0.22), '#FFFFFF', 0.85);
  });
}

/* 4. half suns rising on a horizon, number inside */
function sunStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, iw = cw - 24;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 50, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), iw, 40, {font:'body', max:15, min:11, maxLines:2});
  var bot = ls * 1.1 * 2 + 16 + (st.some(function(t){ return t.d; }) ? ds * 2.8 + 6 : 0) + 20;
  var R = Math.min(cw * 0.42, Z.h - bot - 40, 190), hy = Z.y + Math.max(R + 36, (Z.h - bot + R + 36) / 2);
  var vs = C.groupFit(st.map(function(t){ return t.v; }), R * 1.4, R * 0.5, {font:'head', max:56, min:20, maxLines:1});
  C.rect(Z.x, hy, Z.w, 3, {fill:NAVY});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), c = [ORNG, SUN, SKY, '#2E4D8F'][k % 4];
    sun(C, cx, hy, R, c, 16);
    C.text(cx - R * 0.8, hy - R * 0.62, R * 1.6, R * 0.55, t.v, {font:'head', size:vs, color:K.on(c) === '#FFFFFF' && c !== SKY ? '#FFFFFF' : NAVY, align:'c', va:'m', lh:1, caps:false, ls:0, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
    C.text(Z.x + cw * k + 12, hy + 18, iw, ls * 2.3, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.1, ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.d) C.text(Z.x + cw * k + 12, hy + 18 + ls * 2.3 + 4, iw, ds * 2.9, t.d, {font:'body', size:ds, color:P.muted, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:2}, maxLines:2});
  });
}

/* 5. departure board: split-flap value tiles on a navy board */
function boardStats(C, Z, o){
  var st = sItems(C), n = st.length; if (!n) return; var P = C.P;
  C.rect(Z.x, Z.y, Z.w, Z.h, {fill:NAVY, r:14});
  var pad = 28, hy = 34, rh = (Z.h - pad * 2 - hy) / n, maxc = 1;
  st.forEach(function(t){ maxc = Math.max(maxc, String(t.v).length); });
  var tH = Math.min(rh - 16, 84), bw = Math.min(Z.w * 0.4, 340), tW = 0, lx = Z.x + pad, lw = Z.w - pad * 3 - bw;
  C.text(lx, Z.y + 20, 300, 18, 'Metric', {font:'body', size:12, wt:800, color:SUN, caps:true, ls:0.16, lh:1});
  C.text(Z.x + Z.w - pad - bw, Z.y + 20, bw, 18, 'Value', {font:'body', size:12, wt:800, color:SUN, caps:true, ls:0.16, lh:1, align:'r'});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), lw, rh * 0.5, {font:'head', max:22, min:13, maxLines:1});
  var ds = C.groupFit(st.map(function(t){ return t.d || ''; }), lw, rh * 0.35, {font:'body', max:15, min:11, maxLines:1});
  var vs = C.groupFit(st.map(function(t){ return t.v; }), bw - 24, tH * 0.8, {font:'head', max:Math.round(tH * 0.62), min:18, maxLines:1, ls:0.04});
  st.forEach(function(t, k){
    var y = Z.y + pad + hy + k * rh, cy = y + rh / 2;
    if (k) C.rect(lx, y, Z.w - pad * 2, 1, {fill:'#FFFFFF', op:0.15});
    K.spark(C, lx + 8, cy, 8, k === 0 ? ORNG : SKY, 1);
    var two = !!t.d, lh = ls * 1.2;
    C.text(lx + 26, two ? cy - lh - 1 : cy - lh / 2, lw - 26, lh, t.l, {font:'head', size:ls, color:'#FFFFFF', lh:1.15, va:two ? 'b' : 'm', ed:t.own ? {f:'x-part', i:t.i, p:1} : null, maxLines:1});
    if (two) C.text(lx + 26, cy + 4, lw - 26, ds * 1.5, t.d, {font:'body', size:ds, color:'#AFC0E0', lh:1.3, ed:{f:'x-part', i:t.i, p:2}, maxLines:1});
    var fx = Z.x + Z.w - pad - bw, fy = cy - tH / 2;
    C.rect(fx, fy, bw, tH, {fill:'#1C3673', r:6});
    C.rect(fx, fy, bw, tH / 2, {fill:'#23407F', r:6});
    C.rect(fx, fy + tH / 2 - 1, bw, 2, {fill:NAVY});
    C.rect(fx - 3, fy + tH / 2 - 5, 6, 10, {fill:'#0A1A40', r:2}); C.rect(fx + bw - 3, fy + tH / 2 - 5, 6, 10, {fill:'#0A1A40', r:2});
    C.text(fx + 8, fy, bw - 16, tH, t.v, {font:'head', size:vs, color:k === 0 ? ORNG : '#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0.04, ed:t.own ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
  });
}

/* 6. sun path: stars on an arc over the horizon, notes below the horizon */
function skyArc(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var hy = Z.y + Z.h * 0.5, cx = Z.x + Z.w / 2, Rx = Z.w / 2 - 20, Ry = hy - Z.y - 10, cw = Z.w / n, pts = [];
  var arc = []; for (var a = 180; a <= 360; a += 6) arc.push([cx + Rx * Math.cos(a * Math.PI / 180), hy + Ry * Math.sin(a * Math.PI / 180)]);
  K.curve(C, arc, {stroke:'#F7C95A', lw:2.5});
  sun(C, cx, hy, Math.min(90, Ry * 0.55), '#FBD98A', 0);
  C.rect(Z.x, hy - 1.5, Z.w, 3, {fill:NAVY});
  var F = K.fitHD(C, it, cw - 20, 48, Z.y + Z.h - hy - 44, {hMax:20, dMax:16, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var ang = 180 + 180 * (k + 0.5) / n, px = cx + Rx * Math.cos(ang * Math.PI / 180), py = hy + Ry * Math.sin(ang * Math.PI / 180), colx = Z.x + cw * (k + 0.5), c = [ORNG, SUN, SKY, NAVY][k % 4];
    C.line(px, py + 20, colx, hy + 14, {color:'#B8C6E0', lw:1.5, dash:true});
    K.spark(C, px, py, 26, c, 1); C.ell(px - 5, py - 5, 10, 10, {fill:'#FFFFFF'});
    C.ell(colx - 7, hy - 7, 14, 14, {fill:'#FFFFFF', line:NAVY, lw:3});
    K.hd(C, Z.x + cw * k + 10, hy + 26, cw - 20, Z.y + Z.h - hy - 26, t, F, {align:'c', tc:k === n - 1 ? ORNG : NAVY});
  });
}

/* 7. day / night split */
function dayNight(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return; var P = C.P;
  var g = 20, cw = (Z.w - g * (n - 1)) / n, pad = cw > 400 ? 40 : 26, iw = cw - pad * 2;
  var F = K.fitHD(C, it, iw, 80, Z.h - pad * 2 - 110, {hMax:26, dMax:18, hMin:14, dMin:11.5});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + g), night = k === 1;
    C.rect(x, Z.y, cw, Z.h, night ? {fill:NAVY, r:16} : {grad:{ang:180, stops:[[0, '#CFE4F7'], [100, '#FDE7C4']]}, r:16});
    if (night) { [[0.78, 0.18, 14], [0.9, 0.34, 8], [0.66, 0.1, 6], [0.84, 0.72, 9]].forEach(function(q){ K.spark(C, x + cw * q[0], Z.y + Z.h * q[1], q[2], q[2] > 10 ? SUN : '#FFFFFF', 0.9); }); C.ell(x + cw - 96, Z.y + 30, 50, 50, {fill:'#F4F1E4'}); C.ell(x + cw - 82, Z.y + 22, 50, 50, {fill:NAVY}); }
    else { sun(C, x + cw - 90, Z.y + 96, 52, ORNG, 14); }
    K.pill(C, x + pad, Z.y + pad, 96, 32, (night ? 'Next' : 'Now'), {fill:night ? ORNG : NAVY, color:'#FFFFFF', size:13, wt:800, caps:true, ls:0.1});
    hdc(C, x + pad, Z.y + pad + 80, iw, Z.h - pad * 2 - 80, t, F, {tc:night ? '#FFFFFF' : NAVY, mc:night ? '#C9D5EE' : '#34456E'});
  });
}

/* 8. vertical winding road with pins, rows alongside */
function roadList(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var rw = Math.min(170, Z.w * 0.17), gy = 8, rh = (Z.h - gy * (n - 1)) / n, x0 = Z.x + rw / 2, amp = rw * 0.28, pts = [];
  for (var i = 0; i <= 40; i++) { var t = i / 40; pts.push([x0 + amp * Math.sin(t * Math.PI * Math.max(2, n * 0.7)), Z.y - 10 + t * (Z.h + 20)]); }
  K.curve(C, pts, {stroke:NAVY, lw:24});
  for (var j = 0; j < 40; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:'#FFFFFF', lw:2, op:0.9});
  var tx = Z.x + rw + 30, tw = Z.x + Z.w - tx, H2 = hasH(it);
  var F = H2 ? K.fitHD(C, it, tw - 24, rh * 0.45, rh - 8, {hMax:20, dMax:16, hMin:13, dMin:11, hLines:1}) : null;
  var ls = H2 ? 0 : C.groupFit(it.map(function(t){ return t.d; }), tw - 24, rh - 6, {font:'head', max:n <= 4 ? 26 : 20, min:13, maxLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), cy = y + rh / 2, tt = (cy - Z.y + 10) / (Z.h + 20), px = x0 + amp * Math.sin(tt * Math.PI * Math.max(2, n * 0.7)), c = [ORNG, SUN, SKY][k % 3], d = Math.min(34, rh * 0.7);
    C.line(px + d / 2 + 4, cy, tx - 6, cy, {color:c, lw:2, dash:true});
    C.ell(px - d / 2 - 4, cy - d / 2 - 4, d + 8, d + 8, {fill:'#FFFFFF'});
    C.ell(px - d / 2, cy - d / 2, d, d, {fill:c});
    C.text(px - d / 2, cy - d / 2, d, d, String(k + 1), {font:'head', size:Math.round(d * 0.45), wt:800, color:NAVY, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.rect(tx, y, tw, rh, {fill:'#FFFFFF', r:10});
    C.rect(tx, y + 8, 4, rh - 16, {fill:c, r:2});
    if (H2) hdc(C, tx + 20, y, tw - 34, rh, t, F, {tc:NAVY});
    else C.text(tx + 20, y, tw - 34, rh, t.d, {font:'head', size:ls, color:NAVY, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
  });
}

/* 9. statement above the horizon with a rising sun */
function horizonStatement(C, Z, o){
  var P = C.P, t = C.it[0], hy = Z.y + Z.h - 30, R = Math.min(170, Z.h * 0.42, Z.w * 0.16), sx = Z.x + Z.w - R - 20;
  sun(C, sx, hy, R, SUN, 22); sun(C, sx, hy, R * 0.7, ORNG, 0);
  C.rect(Z.x, hy, Z.w, 4, {fill:NAVY});
  K.spark(C, sx - R - 60, hy - R - 10, 18, ORNG, 1); K.spark(C, sx - R - 20, hy - R - 50, 10, SKY, 1);
  var x = Z.x, w = sx - R - 90 - Z.x;
  if (!t) { C.text(x, Z.y, w, hy - Z.y - 30, C.s.subtitle || '', {font:'body', fit:{max:32, min:16}, wt:600, color:NAVY, va:'m', lh:1.35, ed:{f:'subtitle'}}); return; }
  if (t.h) K.pill(C, x, Z.y + 10, Math.min(w, t.h.length * 12 + 56), 36, t.h, {fill:ORNG, color:'#FFFFFF', size:14, wt:800, caps:true, ls:0.1, ed:{f:'x-part', i:t.i, p:0}});
  C.text(x, Z.y + (t.h ? 64 : 10), w, hy - Z.y - (t.h ? 64 : 10) - 30, t.d, {font:'body', fit:{max:34, min:16}, wt:600, color:NAVY, va:'m', lh:1.35, ed:t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}});
}
D.register({
  id:'horizon', name:'Horizon Launch', tag:'Sky · orange · product', desc:'Pale sky pages with faint star shapes, navy roads and orange / sunflower tags for product lifecycles and launches.',
  fonts:{head:'Archivo', body:'Archivo', headW:800, bodyW:400, headLh:1.02, headCaps:true, headLs:0.01, gf:'Archivo:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Winding lifecycle',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Centred + road',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Sunrise bands',{body:horizonBands}), V('n2','Winding road list',{body:roadList}), V('n3','Star cards',{body:starGrid}), V('v2','Road list + image right',{body:roadList, img:'right'}), V('n4','Numbered list',{body:'agList'})],
    text:[V('v1','Horizon statement',{body:horizonStatement}), V('n2','Statement card',{body:'text', box:true}), V('v2','Horizon + image right',{body:horizonStatement, img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Now / next',{body:dayNight}), V('n2','Star cards',{body:starGrid}), V('n3','Sunrise bands',{body:horizonBands}), V('v2','Image left + road list',{body:roadList, img:'left'}), V('v3','Now / next + image right',{body:dayNight, img:'right'})],
    p3:[V('v1','Launch arc',{body:launchArc}), V('n2','Star cards',{body:starGrid}), V('n3','Sun path',{body:skyArc}), V('n4','Road',{body:'xRoad'}), V('n5','Sunrise bands',{body:horizonBands}), V('v2','Image left + road list',{body:roadList, img:'left'}), V('v3','Bands + image right',{body:horizonBands, img:'right'})],
    p4:[V('v1','Star cards',{body:starGrid}), V('n2','Launch arc',{body:launchArc}), V('n3','Sun path',{body:skyArc}), V('n4','Road',{body:'xRoad'}), V('n5','Sunrise bands',{body:horizonBands}), V('v2','Road list + image right',{body:roadList, img:'right'})],
    p56:[V('v1','Launch arc',{body:launchArc}), V('n2','Star cards',{body:starGrid}), V('n3','Sunrise bands',{body:horizonBands}), V('n4','Road',{body:'xRoad'}), V('n5','Winding road list',{body:roadList}), V('v2','Road list + image right',{body:roadList, img:'right'})],
    p78:[V('v1','Star cards',{body:starGrid}), V('n2','Sunrise bands',{body:horizonBands}), V('n3','Winding road list',{body:roadList}), V('v2','Bands + image right',{body:horizonBands, img:'right'})],
    stats:[V('v1','Rising suns',{body:sunStats}), V('n2','Departure board',{body:boardStats}), V('n3','Stat cards',{body:'statCards', icons:true}), V('v2','Image left + board',{body:boardStats, img:'left'}), V('v3','Suns + image right',{body:sunStats, img:'right'})],
    timeline:[V('v1','Sun path',{body:skyArc}), V('n2','Launch arc',{body:launchArc}), V('n3','Road',{body:'xRoad'}), V('v2','Road list + image right',{body:roadList, img:'right'})],
    quote:[V('v1','Starry quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 21 — Forest Campaign : warm grey pages, forest green + mustard + teal, step bars and hexagon markers */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var FOREST = '#1F4D3A', MUST = '#F2C230', GREEN = '#4E9A51', TEAL = '#2E8C86', CHAR = '#232323';
var P0 = {bg:'#F1F2EF', text:'#1E2A24', title:'#1F4D3A', muted:'#56605A', accent:'#F2C230', accent2:'#4E9A51', card:'#FFFFFF', line:'#D9DCD5', onAccent:'#1E2A24'};
var S = {
  margin:64, series:[MUST, '#A5C23A', GREEN, TEAL, FOREST, CHAR],
  header:{kind:'left', tMax:40, tMin:24, sMax:16, top:50, bar:true, barW:44, barH:5, barC:MUST, barGap:16},
  card:{kind:'bar', r:10, fill:'#FFFFFF', shadow:0.06, barW:6, hl:{kind:'solid', fill:FOREST, dark:true}},
  icon:{kind:'hex', bg:FOREST, fg:'#FFFFFF'},
  num:{kind:'square', r:6, bg:MUST, fg:'#1E2A24'},
  conc:{kind:'band', fill:FOREST, r:10, label:'Goal', lc:MUST},
  footer:{brand:true, page:true, color:'#8A928C'}, xRows:'#FFFFFF', hubFill:'#E4E7E0', road:CHAR, flowBar:'#DEE1DA', stepWord:'Step',
  imgFramed:true, imgR:12
};
/* sweeping double road (decor) */
function roads(C, x0, y0, s){
  var a = [], b = [];
  for (var i = 0; i <= 40; i++) { var t = i / 40; a.push([x0 + t * 520 * s, y0 - Math.sin(t * Math.PI * 1.1) * 220 * s + t * 60 * s]); b.push([x0 + 60 * s + t * 520 * s, y0 + 40 * s - Math.sin(t * Math.PI * 1.05) * 200 * s + t * 60 * s]); }
  K.curve(C, a, {stroke:CHAR, lw:34 * s}); K.curve(C, b, {stroke:FOREST, lw:26 * s});
  for (var j = 0; j < 40; j += 2) C.line(a[j][0], a[j][1], a[j + 1][0], a[j + 1][1], {color:MUST, lw:2.5, op:0.9});
  [10, 22, 34].forEach(function(k, n){ var p = b[k]; C.ell(p[0] - 26, p[1] - 26, 52, 52, {fill:'#FFFFFF'}); C.ell(p[0] - 20, p[1] - 20, 40, 40, {fill:[MUST, GREEN, TEAL][n]}); });
}
function bg(C, kind){
  C.bg({fill:'#F1F2EF'});
  if (kind !== 'cover' && kind !== 'closing') { C.shape('hexagon', W - 120, 30, 80, 70, {fill:MUST, op:0.9}); C.shape('hexagon', W - 170, 76, 54, 47, {fill:FOREST, op:0.9}); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  C.rect(W * 0.55, 0, W * 0.45, H, {fill:FOREST});
  if (o.img) D.imgBox(C, W * 0.55 + 40, 40, W * 0.45 - 80, H - 80, {r:16}); else roads(C, W * 0.55 + 20, 520, 1);
  var x = 64, w = W * 0.55 - 120;
  if (f.company) C.text(x, 80, w, 22, f.company, {font:'body', size:14, wt:800, color:GREEN, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 130, w, 300, C.s.title, {font:'head', fit:{max:64, min:30}, color:FOREST, ed:{f:'title'}, maxLines:4, va:'b', lh:1.04});
  C.rect(x, 454, 70, 7, {fill:MUST});
  if (C.s.subtitle) C.text(x, 480, w, 80, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 620, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  C.rect(0, 0, W * 0.45, H, {fill:FOREST});
  if (o.img) D.imgBox(C, 40, 40, W * 0.45 - 80, H - 80, {r:16}); else roads(C, 10, 520, 0.95);
  var x = W * 0.45 + 64, w = W * 0.55 - 128;
  C.text(x, 150, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:FOREST, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  C.rect(x, 370, 70, 7, {fill:MUST});
  if (C.s.subtitle) C.text(x, 396, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 490, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:MUST}); return; }
  C.rect(0, 0, 18, H, {fill:MUST});
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:GREEN});
}
/* ================= signature bodies (forest motif: honeycomb, leaves, vines, roads) ================= */
var FILLS = [FOREST, MUST, GREEN, TEAL, '#A5C23A', CHAR];
function fillK(k){ return FILLS[k % FILLS.length]; }
function numOn(c){ return c === MUST || c === '#A5C23A' ? FOREST : MUST; }
function fhd(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function blockH(C, t, F, w){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
/* rectangle with two opposite corners fully rounded (leaf card) */
function leafRect(C, x, y, w, h, r, o){
  r = Math.min(r, w / 2, h / 2); var k = 0.552 * r;
  C.path([['M', x + r, y], ['L', x + w, y], ['L', x + w, y + h - r], ['C', x + w, y + h - r + k, x + w - r + k, y + h, x + w - r, y + h], ['L', x, y + h], ['L', x, y + r], ['C', x, y + r - k, x + r - k, y, x + r, y], ['Z']], o);
}
/* pointed leaf from a base point along an angle */
function leaf(C, x0, y0, len, wid, ang, fill, op){
  var a = ang * Math.PI / 180, c = Math.cos(a), s = Math.sin(a);
  function T(px, py){ return [x0 + px * c - py * s, y0 + px * s + py * c]; }
  var p1 = T(len, 0), q1 = T(len * 0.5, -wid), q2 = T(len * 0.5, wid);
  C.path([['M', x0, y0], ['Q', q1[0], q1[1], p1[0], p1[1]], ['Q', q2[0], q2[1], x0, y0], ['Z']], {fill:fill, op:op});
}
/* pointy-top hexagon */
function phex(C, cx, cy, r, o){ var p = []; for (var k = 0; k < 6; k++) { var a = (-90 + 60 * k) * Math.PI / 180; p.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]); } return K.poly(C, p, o); }
function hexBadge(C, x, y, w, n, fill, o){
  o = o || {}; var h = w * 0.866;
  if (o.ring) C.shape('hexagon', x - 4, y - 3.5, w + 8, h + 7, {fill:o.ring});
  C.shape('hexagon', x, y, w, h, {fill:fill});
  C.text(x, y, w, h, o.label != null ? o.label : u.pad2(n), {font:'head', size:Math.round(w * 0.36), wt:800, color:o.fg || K.on(fill), align:'c', va:'m', lh:1, caps:false, ls:0});
}
/* staggered row of flat hexagons (honeycomb strip) */
function zigCells(Z, n){
  var g = 12, w = Math.min(400, (Z.w - g * (n - 1)) / (1 + 0.75 * (n - 1)), (Z.h - g) / (1.5 * 0.866)), h = w * 0.866;
  var tw = w + (n - 1) * (0.75 * w + g), th = h * 1.5 + g / 2, x0 = Z.x + (Z.w - tw) / 2, y0 = Z.y + (Z.h - th) / 2, out = [];
  for (var k = 0; k < n; k++) out.push({x:x0 + k * (0.75 * w + g), y:y0 + (k % 2 ? h / 2 + g / 2 : 0), w:w, h:h});
  return out;
}
function zigDecor(C, Z, cells){
  var a = cells[0], b = cells[cells.length - 1], w = a.w, h = a.h, g = 12;
  var px = a.x - 0.75 * w - g, py = a.y + h / 2 + g / 2;
  if (px >= Z.x - 30) C.shape('hexagon', px, py, w, h, {line:'#C9CFC4', lw:1.5});
  var qx = b.x + 0.75 * w + g, qy = cells.length % 2 ? b.y + h / 2 + g / 2 : b.y - h / 2 - g / 2;
  if (qx + w <= Z.x + Z.w + 30 && qy >= Z.y - 20) C.shape('hexagon', qx, qy, w, h, {line:'#C9CFC4', lw:1.5});
}
/* 1. honeycomb strip: items inside staggered hexagons */
function honeyRow(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cells = zigCells(Z, n), w = cells[0].w, h = cells[0].h, iw = w * 0.62, ih = h * 0.74 - 34;
  var F = fhd(C, it, iw, 60, ih - 10, {hMax:24, dMax:18, noBoost:true});
  zigDecor(C, Z, cells);
  it.forEach(function(t, k){
    var c = cells[k], f = fillK(k), tc = K.on(f), bhh = blockH(C, t, F, iw), top = c.y + (h - (bhh + 34)) / 2;
    C.shape('hexagon', c.x, c.y, w, h, {fill:f});
    C.text(c.x, top, w, 24, u.pad2(k + 1), {font:'head', size:20, wt:800, color:numOn(f), align:'c', lh:1, caps:false, ls:0});
    K.hd(C, c.x + (w - iw) / 2, top + 34, iw, bhh + 4, t, F, {align:'c', tc:tc, mc:tc === '#FFFFFF' ? '#E4EDE6' : '#2E3A33'});
  });
}
/* 2. two-row pointy honeycomb with text in every cell */
function honeycomb(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var a = Math.ceil(n / 2), b = n - a, g = 10;
  var r = Math.min((Z.h - g) / 3.5, ((Z.w + g) / (a + (b === a ? 0.5 : 0)) - g) / 1.732, 150), w = r * 1.732;
  var tw = (b === a ? a + 0.5 : a) * (w + g) - g, x0 = Z.x + (Z.w - tw) / 2, y0 = Z.y + (Z.h - (3.5 * r + g)) / 2;
  var iw = w * 0.78, ih = r * 1.22;
  var F = fhd(C, it, iw, 44, ih - 30, {hMax:20, dMax:16, noBoost:true, hLines:2});
  function pos(k){ return k < a ? [x0 + w / 2 + k * (w + g), y0 + r] : [x0 + w / 2 + (k - a + 0.5) * (w + g), y0 + r + 1.5 * r + g * 0.87]; }
  /* faded cells continuing the comb */
  var l = [x0 + w / 2 - 0.5 * (w + g), y0 + r + 1.5 * r + g * 0.87], rr = [x0 + w / 2 + a * (w + g), y0 + r];
  if (l[0] - w / 2 >= Z.x - 40) phex(C, l[0], l[1], r, {stroke:'#C9CFC4', lw:1.5});
  if (rr[0] + w / 2 <= Z.x + Z.w + 40) phex(C, rr[0], rr[1], r, {stroke:'#C9CFC4', lw:1.5});
  it.forEach(function(t, k){
    var p = pos(k), f = fillK(k === 0 ? 0 : k), tc = K.on(f), bhh = blockH(C, t, F, iw), top = p[1] - (bhh + 28) / 2;
    phex(C, p[0], p[1], r, {fill:f});
    C.text(p[0] - iw / 2, top, iw, 22, u.pad2(k + 1), {font:'head', size:17, wt:800, color:numOn(f), align:'c', lh:1, caps:false, ls:0});
    K.hd(C, p[0] - iw / 2, top + 28, iw, bhh + 4, t, F, {align:'c', tc:tc, mc:tc === '#FFFFFF' ? '#E4EDE6' : '#2E3A33'});
  });
}
/* 3. vine: curving stem with hexagon nodes, leaves and text rows */
function vine(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 6 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 48, cw = (Z.w - gx * (cols - 1)) / cols, rh = Z.h / per;
  var hw = Math.min(58, (rh - 8) / 0.866), tx0 = 26 + hw / 2 + 26, tw = cw - tx0 - 6;
  var F = fhd(C, it, tw, Math.min(34, rh * 0.4), rh - 10, {hMax:20, dMax:16, hLines:1});
  for (var c = 0; c < cols; c++) {
    var sx = Z.x + c * (cw + gx) + 26, cnt = Math.min(per, n - c * per), pts = [[sx, Z.y - 6]];
    for (var r = 0; r < cnt; r++) pts.push([sx + (r % 2 ? 7 : -7), Z.y + rh * (r + 0.5)]);
    pts.push([sx, Z.y + rh * cnt + 4]);
    K.curve(C, pts, {stroke:FOREST, lw:4});
    for (var q = 0; q < cnt; q++) {
      var ly = Z.y + rh * (q + 1) - (q === cnt - 1 ? rh * 0.3 : 0);
      if (q < cnt - 1 || rh > 90) leaf(C, sx, ly, Math.min(34, rh * 0.42), 20, q % 2 ? -150 : -30, q % 2 ? GREEN : '#A5C23A', 0.95);
    }
  }
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh, cy = y + rh / 2, sx = x + 26 + (r % 2 ? 7 : -7);
    var f = [MUST, GREEN, TEAL, FOREST][k % 4];
    hexBadge(C, sx - hw / 2, cy - hw * 0.433, hw, k + 1, f, {ring:'#F1F2EF'});
    var bhh = blockH(C, t, F, tw);
    K.hd(C, x + tx0, cy - bhh / 2, tw, bhh + 4, t, F, {tc:FOREST, hLines:1});
    if (r < per - 1 && k < n - 1) C.rect(x + tx0, y + rh - 0.5, tw, 1, {fill:'#D9DCD5'});
  });
}
/* 4. two leaf-shaped panels with a hexagon hand-off */
function leafPanels(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = 44, cw = (Z.w - gap * (n - 1)) / n, pad = Math.min(40, cw * 0.08), bw = 62, iw = cw - pad * 2;
  var F = fhd(C, it, iw, 80, Z.h - pad * 2 - bw - 24, {hMax:26, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), dark = k === 0, f = dark ? FOREST : '#FFFFFF';
    leafRect(C, x, Z.y, cw, Z.h, 70, {fill:f, stroke:dark ? null : '#C9CFC4', lw:1.5});
    leaf(C, x + cw - 30, Z.y + Z.h - 40, 110, 70, -140, dark ? GREEN : '#E4E9DF', dark ? 0.55 : 1);
    hexBadge(C, x + pad, Z.y + pad, bw, k + 1, dark ? MUST : FOREST, {fg:dark ? FOREST : MUST});
    C.rect(x + pad + bw + 16, Z.y + pad + bw * 0.433 - 2, Math.min(120, cw - pad * 2 - bw - 16), 4, {fill:dark ? MUST : GREEN, r:2});
    var bhh = blockH(C, t, F, iw);
    K.hd(C, x + pad, Z.y + pad + bw + 24, iw, Math.max(bhh + 4, 40), t, F, {tc:dark ? '#FFFFFF' : FOREST, mc:dark ? '#D6E3D9' : '#56605A'});
  });
  if (n === 2) { var mx = Z.x + cw + gap / 2; hexBadge(C, mx - 30, Z.y + Z.h / 2 - 26, 60, 0, MUST, {ring:'#F1F2EF', label:'→', fg:FOREST}); }
}
/* 5. fork in the road: two routes, text above each destination */
function forkPath(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var roadH = Math.min(210, Z.h * 0.44), ny = Z.y + Z.h - roadH + 44, cx = Z.x + Z.w / 2, bot = Z.y + Z.h + 8;
  var nodes = n === 2 ? [Z.x + Z.w * 0.25, Z.x + Z.w * 0.75] : [cx];
  nodes.forEach(function(nx, k){
    var pts = [];
    for (var i = 0; i <= 24; i++) { var s = i / 24, a = 1 - s;
      pts.push([a * a * a * cx + 3 * a * a * s * cx + 3 * a * s * s * nx + s * s * s * nx, a * a * a * bot + 3 * a * a * s * (ny + 40) + 3 * a * s * s * (ny + 80) + s * s * s * ny]); }
    K.curve(C, pts, {stroke:CHAR, lw:30});
    for (var j = 1; j < 23; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:MUST, lw:2.5});
  });
  var colW = n === 2 ? Z.w / 2 - 50 : Z.w * 0.7;
  var F = fhd(C, it, colW - 40, 80, ny - 56 - Z.y - 20, {hMax:28, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var nx = nodes[k], f = k ? GREEN : MUST;
    hexBadge(C, nx - 44, ny - 38, 88, k + 1, f, {ring:'#FFFFFF'});
    var bx = nx - colW / 2 + 20, bhh = blockH(C, t, F, colW - 40), by = ny - 56 - bhh;
    C.rect(nx - 28, by - 20, 56, 5, {fill:f, r:2});
    K.hd(C, bx, by, colW - 40, bhh + 4, t, F, {align:'c', tc:FOREST});
  });
}
/* 6. stats in staggered hexagons */
function hexStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cells = zigCells(Z, n), w = cells[0].w, h = cells[0].h, iw = w * 0.62;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, h * 0.3, {font:'head', max:Math.round(w * 0.2), min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, h * 0.22, {font:'body', wt:700, max:17, min:12, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, h * 0.2, {font:'body', max:14, min:11, maxLines:2});
  zigDecor(C, Z, cells);
  st.forEach(function(t, k){
    var c = cells[k], f = fillK(k), tc = K.on(f), cx = c.x;
    C.shape('hexagon', c.x, c.y, w, h, {fill:f});
    var lh2 = C.measureText(t.l, iw, {font:'body', wt:700, max:ls, min:ls, maxLines:2}).h, xh = t.x ? C.measureText(t.x, iw, {font:'body', max:xs, min:xs}).h + 8 : 0;
    var tot = vs * 1.05 + 14 + lh2 + xh, y = c.y + (h - tot) / 2;
    C.text(cx + (w - iw) / 2, y, iw, vs * 1.1, t.v, {font:'head', size:vs, color:numOn(f) === MUST && tc === '#FFFFFF' ? '#FFFFFF' : tc, align:'c', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx + w / 2 - 20, y + vs * 1.05 + 4, 40, 4, {fill:numOn(f), r:2});
    C.text(cx + (w - iw) / 2, y + vs * 1.05 + 14, iw, lh2 + 2, t.l, {font:'body', size:ls, wt:700, color:tc, align:'c', lh:1.3, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(cx + (w - iw) / 2, y + vs * 1.05 + 14 + lh2 + 8, iw, xh, t.x, {font:'body', size:xs, color:tc === '#FFFFFF' ? '#DCE8DF' : '#2E3A33', align:'c', lh:1.3});
  });
}
/* 7. growth stems: rising stalks with leaves, hexagon value at the top */
function stemStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, hw = Math.min(cw * 0.78, 210, Z.h * 0.42), hh = hw * 0.866, iw = hw * 0.64;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), cw - 24, 52, {font:'body', wt:700, max:n <= 2 ? 21 : 18, min:12, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 24, 40, {font:'body', max:15, min:11, maxLines:2});
  var labH = ls * 1.3 * 2 + 6 + (st.some(function(t){ return t.x; }) ? xs * 1.3 * 2 + 6 : 0), gy = Z.y + Z.h - labH - 16;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, hh * 0.5, {font:'head', max:Math.round(hw * 0.26), min:20, maxLines:1});
  C.rect(Z.x, gy, Z.w, 4, {fill:FOREST, r:2});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), minS = 26, maxS = gy - Z.y - hh, sh = n === 1 ? maxS : minS + (maxS - minS) * k / (n - 1), top = gy - sh - hh, f = [MUST, GREEN, TEAL, FOREST][k % 4];
    C.rect(cx - 2.5, gy - sh - 4, 5, sh + 4, {fill:FOREST});
    for (var q = 0; q < Math.floor(sh / 46); q++) leaf(C, cx, gy - 18 - q * 46, 30, 18, q % 2 ? -140 : -40, q % 2 ? GREEN : '#A5C23A', 1);
    C.shape('hexagon', cx - hw / 2 - 5, top - 4.3, hw + 10, hh + 8.6, {fill:'#F1F2EF'});
    C.shape('hexagon', cx - hw / 2, top, hw, hh, {fill:f});
    C.text(cx - iw / 2, top, iw, hh, t.v, {font:'head', size:vs, color:K.on(f), align:'c', va:'m', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    var le = C.text(Z.x + cw * k + 12, gy + 16, cw - 24, ls * 1.3 * 2 + 2, t.l, {font:'body', size:ls, wt:700, color:FOREST, align:'c', lh:1.3, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(Z.x + cw * k + 12, gy + 16 + le._h + 6, cw - 24, xs * 1.3 * 2 + 2, t.x, {font:'body', size:xs, color:'#56605A', align:'c', lh:1.3});
  });
}
/* 8. hexagon chain timeline with hanging leaf cards */
function hexChain(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, hw = Math.min(170, cw - 26), hh = hw * 0.866, iw = cw - 50;
  var dd = it.map(function(t){ return {i:t.i, h:'', d:t.d}; });
  var F = fhd(C, dd, iw, 1, Z.h - hh - 46 - 70, {dMax:17});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var ch = Math.min(Z.h - hh - 46, th + 76), top = Z.y + Math.max(0, (Z.h - (hh + 46 + ch)) / 2), cy = top + hh / 2, cardY = top + hh + 46;
  C.rect(Z.x + cw / 2, cy - 4, Z.w - cw, 8, {fill:CHAR, r:4});
  for (var i = 0; i < n - 1; i++) C.rect(Z.x + cw * (i + 0.5) + hw / 2 + 8, cy - 1, cw - hw - 16, 2, {fill:MUST});
  var hs = C.groupFit(it.map(function(t){ return t.h; }), hw * 0.66, hh * 0.5, {font:'head', max:Math.round(hw * 0.22), min:13, maxLines:1});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), f = [FOREST, MUST, GREEN, TEAL][k % 4];
    C.shape('hexagon', cx - hw / 2 - 5, cy - hh / 2 - 4.3, hw + 10, hh + 8.6, {fill:'#F1F2EF'});
    C.shape('hexagon', cx - hw / 2, cy - hh / 2, hw, hh, {fill:f});
    C.text(cx - hw * 0.33, cy - hh / 2, hw * 0.66, hh, t.h || u.pad2(k + 1), {font:'head', size:hs, color:K.on(f), align:'c', va:'m', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.line(cx, cy + hh / 2 + 4, cx, cardY, {color:f === MUST ? '#C9A21F' : f, lw:2, dash:true});
    leafRect(C, cx - cw / 2 + 8, cardY, cw - 16, ch, 26, {fill:'#FFFFFF', stroke:'#D9DCD5', lw:1.2});
    leaf(C, cx - 13, cardY + 30, 26, 16, -35, f, 1);
    C.text(cx - iw / 2, cardY + 52, iw, ch - 60, t.d, {font:'body', size:F.ds, color:'#2E3A33', align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 9. agenda: zig-zag hexagon ladder */
function hexLadder(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 800 ? 2 : 1), per = Math.ceil(n / cols), gx = 60, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(n <= 4 ? 112 : 96, Z.h / per);
  var hw = Math.min(n <= 4 ? 84 : 70, (rh - 6) / 0.866), off = hw * 0.62, tx = off + hw + 22, tw = cw - tx;
  var y0 = Z.y + (Z.h - rh * per) / 2;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), tw, rh * 0.5, {font:'head', max:n <= 4 ? 30 : 26, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), tw, rh * 0.4, {font:'body', max:17, min:11, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, hx = x + (r % 2 ? off : 0), f = [FOREST, MUST, GREEN, TEAL][k % 4];
    if (r > 0) C.line(x + (r % 2 ? 0 : off) + hw / 2, y - rh / 2 + hw * 0.3, hx + hw / 2, y + rh / 2 - hw * 0.3, {color:'#C9CFC4', lw:2});
    hexBadge(C, hx, y + (rh - hw * 0.866) / 2, hw, k + 1, f);
    var two = t.h && t.d;
    C.text(x + tx, y + (two ? rh * 0.1 : 0), tw, two ? rh * 0.48 : rh, t.h || t.d, {font:'head', size:hs, color:FOREST, va:two ? 'b' : 'm', lh:1.15, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    if (two) C.text(x + tx, y + rh * 0.6, tw, rh * 0.38, t.d, {font:'body', size:ds, color:'#56605A', lh:1.3, maxLines:1, ed:{f:'x-part', i:t.i, p:1}});
    C.rect(x + tx, y + rh - 1, tw, 1, {fill:'#D9DCD5'});
  });
}
/* 10. leaf tiles grid (agenda + many items) */
function leafTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : Math.ceil(n / 2), rows = Math.ceil(n / cols), gap = 18, cw = (Z.w - gap * (cols - 1)) / cols, ch = Math.min((Z.h - gap * (rows - 1)) / rows, cw * 0.95), pad = 20, iw = cw - pad * 2, oy = (Z.h - ch * rows - gap * (rows - 1)) / 2;
  var ns = Math.min(44, ch * 0.26), tBox = ch - pad * 2 - ns - 14;
  var F = fhd(C, it, iw, 56, tBox, {hMax:22, dMax:16.5});
  var ls = C.groupFit(it.map(function(t){ return t.h ? '' : t.d; }), iw, tBox, {font:'head', max:n <= 5 ? 26 : 21, min:13, maxLines:3});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gap) / 2;
    var x = Z.x + sh + c * (cw + gap), y = Z.y + oy + r * (ch + gap), dark = k % 3 === 0, f = dark ? FOREST : '#FFFFFF';
    leafRect(C, x, y, cw, ch, Math.min(44, ch * 0.3), {fill:f, stroke:dark ? null : '#D5DAD0', lw:1.2});
    leaf(C, x + cw - 16, y + 18, Math.min(52, ch * 0.3), 34, 150, dark ? GREEN : '#DDE4D6', dark ? 0.7 : 1);
    C.text(x + pad, y + pad - 4, iw, ns, u.pad2(k + 1), {font:'head', size:ns, wt:800, color:dark ? MUST : GREEN, lh:1, caps:false, ls:0});
    var tc = dark ? '#FFFFFF' : FOREST, by = y + ch - pad;
    if (!t.h) { var m = C.measureText(t.d, iw, {font:'head', max:ls, min:ls, maxLines:3}); C.text(x + pad, by - m.h, iw, m.h + 2, t.d, {font:'head', size:ls, color:tc, lh:m.lh, ed:{f:'x-part', i:t.i, p:1}, maxLines:3}); }
    else { var bhh = blockH(C, t, F, iw); K.hd(C, x + pad, by - bhh, iw, bhh + 2, t, F, {tc:tc, mc:dark ? '#D6E3D9' : '#56605A'}); }
    C.rect(x + pad, y + pad + ns + 6, 36, 4, {fill:dark ? MUST : GREEN, r:2});
  });
}
D.register({
  id:'forest', name:'Forest Campaign', tag:'Green · mustard · campaigns', desc:'Warm grey pages with forest green and mustard, sweeping roads, step bars and hexagon markers for campaigns and roadmaps.',
  fonts:{head:'Urbanist', body:'Urbanist', headW:800, bodyW:500, headLh:1.04, gf:'Urbanist:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Forest panel + roads',{}), V('v2','Forest panel + image',{img:true})],
    closing:[V('v1','Roads + forest panel',{}), V('v2','Image + forest panel',{img:true})],
    agenda:[V('v1','Hexagon ladder',{body:hexLadder}), V('n2','Leaf tiles',{body:leafTiles}), V('v2','Ladder + image right',{body:hexLadder, img:'right', cols:1})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Leaf panels',{body:leafPanels}), V('n2','Fork in the road',{body:forkPath}), V('n3','Honeycomb pair',{body:honeyRow}), V('v2','Image left + leaf panels',{body:leafPanels, img:'left'}), V('v3','Vine + image right',{body:vine, img:'right'})],
    p3:[V('v1','Honeycomb strip',{body:honeyRow}), V('n2','Vine',{body:vine}), V('n3','Leaf tabs',{body:'xTabs'}), V('n4','Hexagon path',{body:'xHex'}), V('v2','Image left + vine',{body:vine, img:'left'}), V('v3','Leaf tiles + image right',{body:leafTiles, img:'right'})],
    p4:[V('v1','Honeycomb strip',{body:honeyRow}), V('n2','Honeycomb',{body:honeycomb}), V('n3','Vine',{body:vine}), V('n4','Leaf tabs',{body:'xTabs'}), V('n5','Leaf tiles',{body:leafTiles}), V('v2','Vine + image right',{body:vine, img:'right'})],
    p56:[V('v1','Honeycomb',{body:honeycomb}), V('n2','Vine',{body:vine}), V('n3','Leaf tiles',{body:leafTiles}), V('n4','Leaf tabs',{body:'xTabs'}), V('v2','Vine + image right',{body:vine, img:'right'})],
    p78:[V('v1','Honeycomb',{body:honeycomb}), V('n2','Leaf tiles',{body:leafTiles}), V('n3','Two vines',{body:vine, cols:2}), V('v2','Vine + image right',{body:vine, img:'right', cols:1})],
    stats:[V('v1','Hexagon stats',{body:hexStats}), V('n2','Growth stems',{body:stemStats}), V('v2','Image left + stems',{body:stemStats, img:'left'})],
    timeline:[V('v1','Hexagon chain',{body:hexChain}), V('n2','Vine',{body:vine}), V('n3','Hexagon path',{body:'xHex'}), V('v2','Vine + image right',{body:vine, img:'right'})],
    quote:[V('v1','Mustard quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 22 — Terracotta & Teal : warm paper with soft brush sweeps, teal / rust / amber badges and flows */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var TEAL = '#2F7E87', RUST = '#A63D2B', AMBER = '#E0952C', DEEP = '#1F4E5F';
var P0 = {bg:'#FAF6F1', text:'#2B2622', title:'#2B2622', muted:'#6A5F57', accent:'#2F7E87', accent2:'#A63D2B', card:'#FFFFFF', line:'#E9E0D6', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[TEAL, RUST, AMBER, DEEP, '#C0674F', '#5FA5A8'],
  header:{kind:'center', tMax:38, tMin:24, sMax:16, top:44, gap:8},
  card:{kind:'solid', r:18, fill:'#FFFFFF', shadow:0.07, hl:{kind:'solid', fill:TEAL, dark:true}},
  icon:{kind:'circle', bg:TEAL, fg:'#FFFFFF'},
  num:{kind:'circle', bg:RUST, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:TEAL, op:0.1, lc:TEAL, label:'Focus'},
  footer:{brand:false, page:true, color:'#A3968B'}, xRows:'#FFFFFF', hubFill:'#F1EAE1', road:DEEP, flowBar:'#EDE4D9',
  imgFramed:true, imgR:20
};
/* soft brush sweeps (layered translucent curves) */
function sweeps(C, x, y, w, c){
  for (var k = 0; k < 6; k++) {
    var pts = []; for (var i = 0; i <= 12; i++) { var t = i / 12; pts.push([x + t * w, y + k * 7 + Math.sin(t * Math.PI + k) * 16]); }
    K.curve(C, pts, {stroke:c, lw:16 - k * 2, lop:0.07});
  }
}
function badgeArc(C, cx, cy, r, c, ic){
  K.seg(C, cx, cy, r + 8, r + 20, 200, 340, {fill:c});
  C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:'#FFFFFF', shadow:0.12});
  C.ell(cx - r * 0.78, cy - r * 0.78, r * 1.56, r * 1.56, {fill:c});
  C.icon(cx - r * 0.4, cy - r * 0.4, r * 0.8, ic, '#FFFFFF');
}
function bg(C, kind){
  C.bg({fill:'#FAF6F1'});
  sweeps(C, -40, 40, 460, RUST); sweeps(C, W - 480, H - 90, 520, TEAL);
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  if (o.img) D.imgBox(C, W - 540, 40, 500, H - 80, {r:26});
  else { badgeArc(C, W - 380, 250, 80, TEAL, 'mdi:account-group-outline'); badgeArc(C, W - 170, 330, 66, RUST, 'mdi:chart-line'); badgeArc(C, W - 330, 500, 58, AMBER, 'mdi:handshake-outline'); }
  var x = 64, w = W * 0.52;
  if (f.company) C.text(x, 90, w, 22, f.company, {font:'body', size:14, wt:800, color:RUST, caps:true, ls:0.16, ed:{f:'tf-company'}});
  C.text(x, 130, w, 300, C.s.title, {font:'head', fit:{max:66, min:32}, color:P.title, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  [TEAL, RUST, AMBER].forEach(function(c, k){ C.rect(x + k * 46, 454, 40, 6, {fill:c, r:3}); });
  if (C.s.subtitle) C.text(x, 480, w, 80, C.s.subtitle, {font:'body', fit:{max:19, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x, 610, w, 22, meta, {font:'body', size:14, wt:600, color:P.muted});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  if (o.img) D.imgBox(C, 40, 40, 480, H - 80, {r:26}); else { badgeArc(C, 260, 300, 90, TEAL, 'mdi:hand-wave-outline'); badgeArc(C, 440, 470, 60, AMBER, 'mdi:email-outline'); }
  var x = 580, w = W - 644;
  C.text(x, 160, w, 200, C.s.title || 'Thank You', {font:'head', fit:{max:84, min:40}, color:P.title, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(x, 384, w, 60, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, maxLines:2, lh:1.4});
  D.contactList(C, x, 480, w, {});
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:RUST}); return; }
  D.B.quote(C, {x:200, y:130, w:W - 400, h:H - 260}, {qc:RUST});
}
/* ================= signature bodies (terracotta motif: brush strokes, badge arcs, flow circles) ================= */
var CYC = [TEAL, RUST, AMBER, DEEP];
function cK(k){ return CYC[k % CYC.length]; }
function fhd(C, it, w, hb, db, o){ o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; return K.fitHD(C, it, w, hb, db, o); }
function blockH(C, t, F, w){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
function pctOf(v, k){ var m = String(v).match(/(\d+(?:\.\d+)?)\s*%/); return m ? Math.max(4, Math.min(100, parseFloat(m[1]))) : [72, 84, 64, 90][k % 4]; }
/* painted brush band */
function brush(C, x, y, w, h, c, op){
  op = op || 0.14; var lw = h * 0.36;
  for (var k = 0; k < 4; k++) {
    var yy = y + h * (0.24 + 0.17 * k), pts = [], x0 = x + lw / 2 + (k % 2 ? 10 : 0), x1 = x + w - lw / 2 - (k % 3 ? 14 : 0);
    for (var i = 0; i <= 8; i++) { var t = i / 8; pts.push([x0 + t * (x1 - x0), yy + Math.sin(t * Math.PI * 1.4 + k * 1.3) * h * 0.05]); }
    K.curve(C, pts, {stroke:c, lw:lw, lop:op});
  }
}
/* circle with a badge arc hugging its top-left */
function arcBadge(C, cx, cy, r, c, label, o){
  o = o || {};
  K.seg(C, cx, cy, r + 5, r + 13, 190, 330, {fill:c});
  C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:'#FFFFFF', shadow:0.1});
  C.ell(cx - r * 0.8, cy - r * 0.8, r * 1.6, r * 1.6, {fill:c});
  C.text(cx - r * 0.72, cy - r * 0.72, r * 1.44, r * 1.44, label, {font:'head', fit:{max:Math.round(r * (o.big ? 0.62 : 0.7)), min:12}, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.ed || null});
}
function dashCurve(C, pts, c, lw){ for (var j = 0; j < pts.length - 1; j += 2) C.line(pts[j][0], pts[j][1], pts[j + 1][0], pts[j + 1][1], {color:c, lw:lw || 2.5}); }
/* 1. brush rows: every item painted on its own stroke */
function brushRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols, gy = per > 5 ? 8 : 14, rh = Math.min(140, (Z.h - gy * (per - 1)) / per);
  var y0 = Z.y + (Z.h - (rh * per + gy * (per - 1))) / 2, d = Math.min(62, rh * 0.74), tx = d + 34, tw = cw - tx - 24;
  var agendaLike = it.every(function(t){ return !t.h; }) && n > 2;
  var F = fhd(C, it, tw, Math.min(34, rh * 0.42), rh - 12, {hMax:21, dMax:16.5, hLines:1});
  var ls = agendaLike ? C.groupFit(it.map(function(t){ return t.d; }), tw, rh * 0.7, {font:'head', max:n <= 5 ? 28 : 24, min:13, maxLines:1}) : 0;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), col = cK(k);
    brush(C, x + d * 0.4, y, cw - d * 0.4, rh, col, 0.1);
    arcBadge(C, x + d / 2 + 6, y + rh / 2, d / 2, col, u.pad2(k + 1));
    if (agendaLike) C.text(x + tx, y, tw, rh, t.d, {font:'head', size:ls, color:'#2B2622', va:'m', lh:1.15, maxLines:1, ed:{f:'x-part', i:t.i, p:1}});
    else { var bhh = blockH(C, t, F, tw); K.hd(C, x + tx, y + (rh - bhh) / 2, tw, bhh + 4, t, F, {tc:'#2B2622', mc:'#4F4640', hLines:1}); }
  });
}
/* 2. badge arcs row joined by dotted bridges */
function arcBadges(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var cw = Z.w / n, r = Math.min(80, cw * 0.26, Z.h * 0.18), iw = cw - 36;
  var F = fhd(C, it, iw, 60, Z.h - 2 * r - 70, {hMax:23, dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, blockH(C, t, F, iw)); });
  var top = Z.y + Math.max(0, (Z.h - (2 * r + 60 + th)) / 2), cy = top + r + 16, ty = cy + r + 44;
  for (var k = 0; k < n - 1; k++) {
    var ax = Z.x + cw * (k + 0.5), bx = ax + cw, pts = [];
    for (var j = 0; j <= 16; j++) { var tt = j / 16; pts.push([ax + r + 14 + tt * (bx - ax - 2 * r - 28), cy - 10 - Math.sin(tt * Math.PI) * r * 0.8]); }
    dashCurve(C, pts, cK(k), 2.5);
    K.poly(C, [[bx - r - 16, cy - 20], [bx - r - 4, cy - 9], [bx - r - 18, cy - 5]], {fill:cK(k)});
  }
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), col = cK(k);
    arcBadge(C, cx, cy, r, col, u.pad2(k + 1), {big:true});
    C.rect(cx - 22, cy + r + 24, 44, 4, {fill:col, r:2});
    K.hd(C, cx - iw / 2, ty, iw, th + 4, t, F, {align:'c', tc:'#2B2622', mc:'#5E544C'});
  });
}
/* 3. serpentine flow: circles on an S-shaped path, text under each */
function serpentine(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rows = n <= 3 ? 1 : 2, per = Math.ceil(n / rows), step0 = Z.h / rows, d = Math.min(80, step0 * 0.34);
  var uw0 = Z.w - (rows > 1 ? step0 / 2 : 0) - 8, iw = uw0 / per - 22;
  var F, th, step, turnR, ext, uw, cw;
  for (var pass = 0; pass < 4; pass++) { /* keep each text block inside its own column (no overlap with its neighbour) */
    F = fhd(C, it, iw, 48, step0 - d - 36, {hMax:20, dMax:15.5});
    th = 0; it.forEach(function(t){ th = Math.max(th, blockH(C, t, F, iw)); });
    step = Math.min(step0, d + 30 + th + 44); turnR = rows > 1 ? step / 2 : 0; ext = rows > 1 ? Math.max(0, iw / 2 + 14 - turnR * 0.55) : 0; uw = Z.w - turnR - ext - 8; cw = uw / per;
    if (cw - 22 >= iw - 0.5) break;
    iw = cw - 22;
  }
  var top = Z.y + Math.max(0, (Z.h - (step * (rows - 1) + d + 30 + th)) / 2);
  var ys = []; for (var r = 0; r < rows; r++) ys.push(top + d / 2 + 4 + r * step);
  function pos(k){ var r = Math.floor(k / per), j = k % per; return [r % 2 ? Z.x + uw - cw * (j + 0.5) : Z.x + cw * (j + 0.5), ys[r]]; }
  var xR = Z.x + uw - cw * 0.5;
  for (var q = 0; q < rows; q++) { var last = pos(Math.min(n - 1, (q + 1) * per - 1)), first = pos(q * per), x0 = Math.min(first[0], last[0]); C.rect(x0, ys[q] - 5, xR + ext - x0, 10, {fill:'#EDE4D9', r:5}); }
  if (rows > 1) {
    var cx = xR + ext, cy = (ys[0] + ys[1]) / 2, rr = (ys[1] - ys[0]) / 2;
    D.arc(C, cx, cy, rr, -90, 90, {color:'#EDE4D9', lw:10, op:1});
    var pts = []; for (var i = 0; i <= 18; i++) { var a = (-90 + 180 * i / 18) * Math.PI / 180; pts.push([cx + rr * Math.cos(a), cy + rr * Math.sin(a)]); }
    dashCurve(C, pts, AMBER, 2.5);
  }
  it.forEach(function(t, k){
    var p = pos(k), col = cK(k);
    if (k < n - 1 && Math.floor(k / per) === Math.floor((k + 1) / per)) { var p2 = pos(k + 1), dir = p2[0] > p[0] ? 1 : -1; C.shape('chevron', (p[0] + p2[0]) / 2 - 9, p[1] - 9, 18, 18, {fill:col, rot:dir < 0 ? 180 : 0}); }
    C.ell(p[0] - d / 2 - 6, p[1] - d / 2 - 6, d + 12, d + 12, {fill:'#FAF6F1'});
    arcBadge(C, p[0], p[1], d / 2, col, u.pad2(k + 1), {big:true});
    K.hd(C, p[0] - iw / 2, p[1] + d / 2 + 24, iw, th + 4, t, F, {align:'c', tc:'#2B2622', mc:'#5E544C'});
  });
}
/* 4. two notes with badge arcs and a flow disc between */
function twoNotes(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = 64, cw = (Z.w - gap * (n - 1)) / n, pad = Math.min(40, cw * 0.08), r = 38, iw = cw - pad * 2;
  var F = fhd(C, it, iw, 80, Z.h - pad * 2 - r * 2 - 30, {hMax:26, dMax:19, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), dark = k === 1, col = k ? TEAL : RUST;
    C.rect(x, Z.y, cw, Z.h, {fill:dark ? TEAL : '#FFFFFF', r:22, shadow:0.08});
    if (!dark) brush(C, x + 20, Z.y + Z.h - 110, cw - 40, 90, RUST, 0.06); else brush(C, x + 20, Z.y + Z.h - 110, cw - 40, 90, '#FFFFFF', 0.07);
    if (dark) { K.seg(C, x + pad + r, Z.y + pad + r, r + 5, r + 13, 190, 330, {fill:AMBER}); C.ell(x + pad, Z.y + pad, 2 * r, 2 * r, {fill:'#FFFFFF'}); C.text(x + pad, Z.y + pad, 2 * r, 2 * r, u.pad2(k + 1), {font:'head', size:28, wt:700, color:TEAL, align:'c', va:'m', lh:1, caps:false, ls:0}); }
    else arcBadge(C, x + pad + r, Z.y + pad + r, r, col, u.pad2(k + 1), {big:true});
    var bhh = blockH(C, t, F, iw), by = Math.max(Z.y + pad + r * 2 + 30, Z.y + (Z.h - bhh) / 2 - 10);
    K.hd(C, x + pad, by, iw, Math.max(bhh + 4, 40), t, F, {tc:dark ? '#FFFFFF' : '#2B2622', mc:dark ? '#DCEFF0' : '#5E544C'});
  });
  if (n === 2) {
    var mx = Z.x + cw + gap / 2, my = Z.y + Z.h / 2;
    C.ell(mx - 30, my - 30, 60, 60, {fill:AMBER, line:'#FAF6F1', lw:5});
    C.shape('chevron', mx - 10, my - 11, 22, 22, {fill:'#FFFFFF'});
  }
}
/* 5. half-gauge stats */
function gaugeStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, R = Math.min(cw * 0.4, Z.h * 0.4, 160), r0 = R * 0.76, iw = cw - 30;
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 52, {font:'head', max:n <= 2 ? 24 : 20, min:13, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, 44, {font:'body', max:16, min:11, maxLines:2});
  var vs = C.groupFit(st.map(function(t){ return t.v; }), r0 * 1.6, r0 * 0.7, {font:'head', max:Math.round(R * 0.5), min:20, maxLines:1});
  var blk = R + 24 + ls * 1.2 * 2 + 10 + xs * 1.35 * 2, top = Z.y + Math.max(0, (Z.h - blk) / 2), cy = top + R;
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), col = cK(k), p = pctOf(t.v, k), a1 = 180 + 180 * p / 100;
    K.seg(C, cx, cy, r0, R, 180, 360, {fill:'#EDE4D9'});
    K.seg(C, cx, cy, r0, R, 180, a1, {fill:col});
    var e = K.pol(cx, cy, (R + r0) / 2, a1); C.ell(e[0] - (R - r0) * 0.62, e[1] - (R - r0) * 0.62, (R - r0) * 1.24, (R - r0) * 1.24, {fill:col, line:'#FAF6F1', lw:3});
    for (var q = 0; q <= 4; q++) { var a = 180 + 45 * q, p1 = K.pol(cx, cy, r0 - 6, a), p2 = K.pol(cx, cy, r0 - 16, a); C.line(p1[0], p1[1], p2[0], p2[1], {color:'#C9B9A8', lw:2}); }
    C.text(cx - r0 * 0.8, cy - vs * 1.1, r0 * 1.6, vs * 1.1, t.v, {font:'head', size:vs, color:col, align:'c', va:'b', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx - R, cy + 8, R * 2, 2, {fill:'#E2D6C8'});
    var le = C.text(cx - iw / 2, cy + 24, iw, ls * 1.2 * 2 + 2, t.l, {font:'head', size:ls, color:'#2B2622', align:'c', lh:1.2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(cx - iw / 2, cy + 24 + le._h + 10, iw, xs * 1.35 * 2 + 2, t.x, {font:'body', size:xs, color:'#6A5F57', align:'c', lh:1.35});
  });
}
/* 6. stats painted on brush strokes */
function brushStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var gy = 16, rh = Math.min(150, (Z.h - gy * (n - 1)) / n), y0 = Z.y + (Z.h - rh * n - gy * (n - 1)) / 2, vw = Math.min(Z.w * 0.42, 380), tw = Z.w - vw - 30;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), vw - 50, rh * 0.72, {font:'head', max:84, min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), tw, rh * 0.5, {font:'head', max:26, min:13, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, rh * 0.36, {font:'body', max:17, min:11, maxLines:2});
  st.forEach(function(t, k){
    var y = y0 + k * (rh + gy), col = cK(k);
    brush(C, Z.x, y, vw, rh, col, 0.2);
    C.text(Z.x + 26, y, vw - 50, rh, t.v, {font:'head', size:vs, color:col === AMBER ? '#9A5F12' : col, va:'m', lh:1, maxLines:1, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    var lh2 = C.measureText(t.l, tw, {font:'head', max:ls, min:ls, maxLines:2}).h, xh = t.x ? C.measureText(t.x, tw, {font:'body', max:xs, min:xs}).h + 6 : 0, by = y + (rh - lh2 - xh) / 2;
    C.text(Z.x + vw + 30, by, tw, lh2 + 2, t.l, {font:'head', size:ls, color:'#2B2622', lh:1.1, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(Z.x + vw + 30, by + lh2 + 6, tw, xh, t.x, {font:'body', size:xs, color:'#6A5F57', lh:1.35});
    if (k < n - 1) C.rect(Z.x + vw + 30, y + rh + gy / 2, tw, 1, {fill:'#E2D6C8'});
  });
}
/* 7. milestones on a rising arch */
function archTimeline(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, d = Math.min(104, cw * 0.58), iw = cw - 22, A0 = Math.min(90, Z.h * 0.2);
  var F = fhd(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), iw, 1, Z.h - d - A0 - 40, {dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var A = Math.min(A0 + Math.max(0, (Z.h - (d + A0 + 30 + th)) * 0.35), Z.h * 0.3), tot = d + A + 30 + th, top = Z.y + Math.max(0, (Z.h - tot) / 2), base = top + d / 2 + 8 + A;
  function yAt(x){ var t = (x - Z.x) / Z.w; return base - A * Math.sin(Math.PI * t); }
  var pts = []; for (var i = 0; i <= 48; i++) { var x = Z.x + Z.w * i / 48; pts.push([x, yAt(x)]); }
  K.curve(C, pts, {stroke:'#EDE4D9', lw:16});
  dashCurve(C, pts, DEEP, 2);
  var hs = C.groupFit(it.map(function(t){ return t.h; }), d * 0.8, d * 0.5, {font:'head', max:Math.round(d * 0.3), min:13, maxLines:1});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = yAt(cx), col = cK(k), ty = cy + d / 2 + 26;
    C.ell(cx - d / 2 - 6, cy - d / 2 - 6, d + 12, d + 12, {fill:'#FAF6F1'});
    K.seg(C, cx, cy, d / 2 + 5, d / 2 + 13, 190, 330, {fill:col});
    C.ell(cx - d / 2, cy - d / 2, d, d, {fill:col});
    C.text(cx - d * 0.42, cy - d / 2, d * 0.84, d, t.h || u.pad2(k + 1), {font:'head', size:hs, color:'#FFFFFF', align:'c', va:'m', lh:1, maxLines:1, caps:false, ls:0, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx - 1, cy + d / 2 + 4, 2, 16, {fill:col});
    C.text(cx - iw / 2, ty, iw, Math.min(th + 6, Z.y + Z.h - ty), t.d, {font:'body', size:F.ds, color:'#4F4640', align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 8. centred flow spine, items alternating left / right */
function spineFlow(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rh = Z.h / n, d = Math.min(58, rh * 0.78), cx = Z.x + Z.w / 2, sw = Z.w / 2 - d / 2 - 44;
  var F = fhd(C, it, sw, Math.min(34, rh * 0.42), rh - 8, {hMax:22, dMax:17, hLines:1});
  C.rect(cx - 5, Z.y, 10, Z.h, {fill:'#EDE4D9', r:5});
  var pts = []; for (var i = 0; i <= 2 * n; i++) pts.push([cx, Z.y + Z.h * i / (2 * n)]);
  it.forEach(function(t, k){
    var y = Z.y + k * rh, cy = y + rh / 2, col = cK(k), left = k % 2 === 0;
    C.ell(cx - d / 2 - 5, cy - d / 2 - 5, d + 10, d + 10, {fill:'#FAF6F1'});
    arcBadge(C, cx, cy, d / 2, col, u.pad2(k + 1));
    var bx = left ? cx - d / 2 - 36 - sw : cx + d / 2 + 36;
    C.line(left ? cx - d / 2 - 30 : cx + d / 2 + 14, cy, left ? cx - d / 2 - 14 : cx + d / 2 + 30, cy, {color:col, lw:3});
    var bhh = blockH(C, t, F, sw), bb = Math.min(rh - 8, bhh + 34);
    brush(C, bx - 12, cy - bb / 2, sw + 24, bb, col, 0.08);
    K.hd(C, bx, cy - bhh / 2, sw, bhh + 4, t, F, {align:left ? 'r' : 'l', tc:'#2B2622', mc:'#5E544C', hLines:1});
  });
}
/* 9. agenda on a curved arc */
function agendaArc(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var R = Z.h * 0.78, cy = Z.y + Z.h / 2, mid = Z.x + (Z.w > 900 ? Z.w * 0.24 : 130), cx = mid - R, rh = Z.h / n, d = Math.min(64, rh * 0.8);
  var am = Math.asin(Math.min(0.98, (Z.h / 2 + 10) / R)) * 180 / Math.PI;
  D.arc(C, cx, cy, R, -am, am, {color:'#EDE4D9', lw:14, op:1});
  D.arc(C, cx, cy, R, -am, am, {color:DEEP, lw:2, op:0.6});
  D.arc(C, cx, cy, R - 40, -am * 0.8, am * 0.8, {color:AMBER, lw:3, op:0.5});
  var ls = C.groupFit(it.map(function(t){ return t.h || t.d; }), Z.x + Z.w - mid - d / 2 - 30, rh * 0.62, {font:'head', max:n <= 5 ? 32 : 24, min:13, maxLines:1});
  it.forEach(function(t, k){
    var y = Z.y + rh * (k + 0.5), dy = y - cy, x = cx + Math.sqrt(Math.max(0, R * R - dy * dy)), col = cK(k);
    C.ell(x - d / 2 - 5, y - d / 2 - 5, d + 10, d + 10, {fill:'#FAF6F1'});
    arcBadge(C, x, y, d / 2, col, u.pad2(k + 1));
    var tx = x + d / 2 + 28;
    C.text(tx, y - rh / 2, Z.x + Z.w - tx, rh, t.h || t.d, {font:'head', size:ls, color:'#2B2622', va:'m', lh:1.1, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
  });
}
D.register({
  id:'terracotta', name:'Terracotta & Teal', tag:'Warm · people & process', desc:'Warm paper with soft brush sweeps, teal, rust and amber badges, arrows and cycles for HR, people and process decks.',
  fonts:{head:'Rubik', body:'Rubik', headW:700, bodyW:400, headLh:1.06, gf:'Rubik:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Badge trio',{}), V('v2','Title + image',{img:true})],
    closing:[V('v1','Badges left',{}), V('v2','Image left',{img:true})],
    agenda:[V('v1','Curved agenda',{body:agendaArc}), V('n2','Brush rows',{body:brushRows}), V('v2','Curve + image right',{body:agendaArc, img:'right'})],
    text:[V('v1','Statement card',{body:'text', box:true}), V('n2','Statement',{body:'text'}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Two notes',{body:twoNotes}), V('n2','Badge arcs',{body:arcBadges}), V('n3','Brush rows',{body:brushRows}), V('v2','Image left + brush rows',{body:brushRows, img:'left'}), V('v3','Notes + image right',{body:twoNotes, img:'right'})],
    p3:[V('v1','Badge arcs',{body:arcBadges}), V('n2','Brush rows',{body:brushRows}), V('n3','Flow circles',{body:'xFlow'}), V('v2','Image left + brush rows',{body:brushRows, img:'left'}), V('v3','Flow spine + image right',{body:spineFlow, img:'right'})],
    p4:[V('v1','Serpentine flow',{body:serpentine}), V('n2','Badge arcs',{body:arcBadges}), V('n3','Brush rows',{body:brushRows}), V('v2','Brush rows + image right',{body:brushRows, img:'right'})],
    p56:[V('v1','Serpentine flow',{body:serpentine}), V('n2','Brush rows',{body:brushRows}), V('n3','Flow spine',{body:spineFlow}), V('n4','Flow circles',{body:'xFlow'}), V('v2','Brush rows + image right',{body:brushRows, img:'right'})],
    p78:[V('v1','Serpentine flow',{body:serpentine}), V('n2','Brush rows',{body:brushRows, cols:2}), V('n3','Flow spine',{body:spineFlow}), V('v2','Brush rows + image right',{body:brushRows, img:'right', cols:1})],
    stats:[V('v1','Half gauges',{body:gaugeStats}), V('n2','Brush numbers',{body:brushStats}), V('v2','Image left + brush numbers',{body:brushStats, img:'left'})],
    timeline:[V('v1','Rising arch',{body:archTimeline}), V('n2','Flow spine',{body:spineFlow}), V('n3','Flow circles',{body:'xFlow'}), V('v2','Flow spine + image right',{body:spineFlow, img:'right'})],
    quote:[V('v1','Warm quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 23 — Mono Portfolio : black editorial pages, oversized grey headlines, photo-led layouts, thin meta lines */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var P0 = {bg:'#0A0A0A', text:'#E8E8E8', title:'#D9D9D9', muted:'#9A9A9A', accent:'#FFFFFF', accent2:'#8C8C8C', card:'#151515', line:'#2C2C2C', onAccent:'#0A0A0A'};
var S = {
  margin:64, series:['#E8E8E8', '#9A9A9A', '#5C5C5C', '#C8C8C8', '#7A7A7A', '#B0B0B0'],
  header:{kind:'left', tMax:54, tMin:28, sMax:16, top:78, gap:14, tc:'#D9D9D9'},
  card:{kind:'rule', r:0, fill:'#151515', dark:true, tc:'#FFFFFF', mc:'#A5A5A5'},
  icon:{kind:'plain', bg:'#FFFFFF', fg:'#FFFFFF'},
  num:{kind:'big', fg:'#FFFFFF'},
  mark:'num',
  conc:{kind:'line', lineC:'#3A3A3A', lc:'#FFFFFF', label:'Note'},
  footer:{fn:function(C){ meta(C, true); }}, xRows:'#141414', hubFill:'#161616', road:'#2C2C2C', flowBar:'#1C1C1C',
  imgFramed:false, imgR:0, imgPct:0.46
};
function meta(C, bottom){
  var f = C.cover, fc = '#8C8C8C', o = C.o || {}, iw = Math.round(W * 0.46), x0 = 64, x1 = W - 64;
  if (C.kind !== 'cover' && C.kind !== 'closing') { if (o.img === 'left') x0 = iw + 48; if (o.img === 'right') x1 = W - iw - 48; }
  var mw = x1 - x0;
  if (o.img !== 'top' || C.kind === 'cover' || C.kind === 'closing') {
    C.text(x0, 26, mw / 2, 16, f.company || 'Creative Presentation', {font:'body', size:11, wt:700, color:fc, lh:1});
    C.text(x0 + mw / 2, 26, mw / 2, 16, f.date || u.pad2(C.i + 1), {font:'body', size:11, wt:700, color:fc, align:'r', lh:1});
  }
  if (bottom) {
    C.rect(x0, H - 44, mw, 1, {fill:'#2C2C2C'});
    var three = mw > 900;
    C.text(x0, H - 32, mw / 3, 16, f.website || '', {font:'body', size:11, color:fc, lh:1});
    if (three) C.text(x0 + mw / 3, H - 32, mw / 3, 16, f.email || f.presenter || '', {font:'body', size:11, color:fc, align:'c', lh:1});
    C.text(x1 - mw / 3, H - 32, mw / 3, 16, u.pad2(C.i + 1), {font:'body', size:11, wt:700, color:fc, align:'r', lh:1});
  }
}
function bg(C, kind){ C.bg({fill:'#0A0A0A'}); }
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  meta(C, true);
  if (o.img) { D.imgBox(C, W * 0.5, 70, W * 0.5 - 64, H - 150, {r:0}); }
  var w = o.img ? W * 0.5 - 100 : W - 128;
  C.text(56, 60, w, o.img ? 480 : 420, C.s.title, {font:'head', fit:{max:o.img ? 96 : 124, min:40}, color:'#D9D9D9', ed:{f:'title'}, maxLines:o.img ? 5 : 3, va:'t', lh:0.9, ls:-0.02});
  if (C.s.subtitle) C.text(64, H - 190, o.img ? w : 760, 110, C.s.subtitle, {font:'body', fit:{max:17, min:11}, color:'#BDBDBD', ed:{f:'subtitle'}, lh:1.5, maxLines:4});
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  meta(C, true);
  if (o.img) D.imgBox(C, W * 0.55, 70, W * 0.45 - 64, H - 150, {r:0});
  C.text(56, 60, o.img ? W * 0.55 - 100 : W - 128, 440, C.s.title || 'Thank You.', {font:'head', fit:{max:132, min:44}, color:'#D9D9D9', ed:{f:'title'}, maxLines:2, va:'t', lh:0.88, ls:-0.02});
  if (C.s.subtitle) C.text(64, H - 190, 640, 60, C.s.subtitle, {font:'body', fit:{max:17, min:11}, color:'#BDBDBD', ed:{f:'subtitle'}, lh:1.5, maxLines:3});
  D.contactList(C, o.img ? 64 : 780, o.img ? H - 120 : 480, 440, {vc:'#E8E8E8', gap:48, markO:{bg:'#2A2A2A'}});
}
/* photo mosaic agenda: title left, two photos, items list */
function agenda(C, v){
  var P = C.P;
  meta(C, true);
  C.text(56, 70, 600, 140, C.s.title || 'Table of Contents', {font:'head', fit:{max:64, min:32}, color:'#D9D9D9', ed:{f:'title'}, maxLines:2, lh:0.92});
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per;
    C.text(720 + c * 250, 80 + r * 30, 240, 26, '•  ' + (t.h || t.d), {font:'body', size:15, wt:700, color:'#E8E8E8', caps:true, ls:0.04, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
  D.imgBox(C, 64, 250, 560, H - 320, {r:0}); C.rect(640, 250, W - 704, H - 320, {fill:'#161616'});
  if (C.s.subtitle) C.text(670, 280, W - 764, H - 380, C.s.subtitle, {font:'body', fit:{max:20, min:12}, color:'#BDBDBD', ed:{f:'subtitle'}, lh:1.5, va:'b'});
}
function quote(C, v){
  var o = C.o || {};
  meta(C, true);
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:'#FFFFFF'}); return; }
  D.B.quote(C, {x:120, y:120, w:W - 240, h:H - 260}, {qc:'#FFFFFF'});
}
/* ================= signature bodies (mono motif: oversized grey type, thin rules, photo spreads) ================= */
var WH = '#F2F2F2', GR = '#A5A5A5', RULE = '#2C2C2C', GHOST = '#242424';
function fhd(C, it, w, hb, db, o){
  o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; var F = K.fitHD(C, it, w, hb, db, o), hh = 0;
  it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, w, {font:'head', max:F.hs, min:F.hs, lh:1.15, maxLines:o.hLines || 2}).h); });
  F.hh = hh; return F;
}
function blockH(C, t, F, w){ return (t.h ? F.hh + 8 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function hdm(C, x, y, w, h, t, F, o){ o = o || {}; o.tc = o.tc || WH; o.mc = o.mc || GR; return K.hd(C, x, y, w, h, t, F, o); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
function metaTxt(C, x, y, w, s, o){ o = o || {}; C.text(x, y, w, 16, s, {font:'body', size:11, wt:700, color:o.c || '#8C8C8C', align:o.align || 'l', ls:0.12, caps:true, lh:1}); }
/* 1. index rows: ghost numerals behind each row, thin rules */
function indexRows(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 56, cw = (Z.w - gx * (cols - 1)) / cols, rh = Z.h / per;
  var compact = rh < 76, ns = compact ? 17 : Math.min(rh * 0.95, 130), nw = ns * 0.9, wide = cw > 780;
  var hx = compact ? 46 : nw * 0.55, hw = wide ? (cw - hx) * 0.38 : cw - hx - 10, dx = wide ? hx + hw + 30 : hx, dw = wide ? cw - dx : hw;
  var F = wide ? {hs:C.groupFit(it.map(function(t){ return t.h; }), hw, rh - 16, {font:'head', max:28, min:13, maxLines:2}), hh:0, ds:C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 16, {font:'body', max:19, min:11, lh:1.36})}
               : fhd(C, it, hw, Math.min(40, rh * 0.36), rh - 14, {hMax:22, dMax:16.5, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = Z.y + r * rh;
    C.rect(x, y, cw, 1, {fill:RULE});
    if (r === per - 1 || k === n - 1) C.rect(x, y + rh, cw, 1, {fill:RULE});
    C.text(x - (compact ? 0 : 4), y, compact ? 40 : nw * 1.4, rh, u.pad2(k + 1), {font:'head', size:ns, color:compact ? '#6E6E6E' : GHOST, va:'m', lh:1, caps:false, ls:-0.02, maxLines:1});
    metaTxt(C, x + cw - 120, y + 8, 120, 'N° ' + u.pad2(k + 1), {align:'r'});
    if (wide) {
      if (t.h) C.text(x + hx, y, hw, rh, t.h, {font:'head', size:F.hs, color:WH, va:'m', lh:1.05, maxLines:2, ed:{f:'x-part', i:t.i, p:0}});
      C.text(x + dx, y + 8, dw - 130 > 200 ? dw - 130 : dw, rh - 16, t.d, {font:'body', size:F.ds, color:GR, va:'m', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      var bhh = blockH(C, t, F, hw);
      hdm(C, x + hx, y + (rh - bhh) / 2 + 2, hw, bhh + 4, t, F, {hLines:1});
    }
  });
}
/* 2. ghost columns: giant numerals with the heading laid over their foot */
function ghostColumns(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var gap = 36, cw = (Z.w - gap * (n - 1)) / n, ns = Math.min(cw * 0.62, 190, Z.h * 0.42), iw = cw - 8;
  var F = fhd(C, it, iw, 70, Z.h - ns * 0.8 - 40, {hMax:26, dMax:18});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    if (k) C.rect(x - gap / 2, Z.y, 1, Z.h, {fill:RULE});
    metaTxt(C, x, Z.y, iw, '(' + u.pad2(k + 1) + ')  ' + (k === 0 ? '—  Index' : ''));
    C.text(x - 6, Z.y + 16, cw + 40, ns, u.pad2(k + 1), {font:'head', size:ns, color:k === 0 ? '#3A3A3A' : GHOST, lh:0.9, caps:false, ls:-0.03, maxLines:1});
    var hy = Z.y + 16 + ns * 0.78;
    hdm(C, x, hy, iw, Z.y + Z.h - hy, t, F);
  });
}
/* 3. film strip: one photo sliced into frames, captions under each */
function filmStrip(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var gap = 12, bh = Math.round(Z.h * 0.5), cw = (Z.w - gap * (n - 1)) / n;
  D.imgBox(C, Z.x, Z.y, Z.w, bh, {r:0});
  for (var k = 1; k < n; k++) C.rect(Z.x + k * (cw + gap) - gap, Z.y - 1, gap, bh + 2, {fill:'#0A0A0A'});
  var ty = Z.y + bh + 22, iw = cw - 6;
  var F = fhd(C, it, iw, 56, Z.y + Z.h - ty - 30, {hMax:22, dMax:16});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    C.rect(x, Z.y + bh - 30, 54, 30, {fill:'#0A0A0A'});
    C.text(x, Z.y + bh - 30, 54, 30, u.pad2(k + 1), {font:'body', size:13, wt:700, color:WH, align:'c', va:'m', lh:1});
    metaTxt(C, x, ty, iw, 'Frame ' + u.pad2(k + 1));
    hdm(C, x, ty + 24, iw, Z.y + Z.h - ty - 24, t, F);
  });
}
/* 4. two giant grey statements split by a hairline */
function bigSplit(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = Z.w > 900 ? 80 : 48, cw = (Z.w - gap * (n - 1)) / n;
  var hs = C.groupFit(it.map(function(t){ return t.h; }), cw, Z.h * 0.45, {font:'head', max:Z.w > 900 ? 84 : 52, min:22, maxLines:3});
  var hh = 0; it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, cw, {font:'head', max:hs, min:hs, maxLines:3}).h); });
  var dBox = Z.h - hh - 110;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw, dBox, {font:'body', max:24, min:12, lh:1.45});
  var dh = 0; it.forEach(function(t){ dh = Math.max(dh, C.measureText(t.d, cw, {font:'body', max:ds, min:ds, lh:1.45}).h); });
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    if (k) C.rect(x - gap / 2, Z.y, 1, Z.h, {fill:RULE});
    C.rect(x, Z.y, cw, 1, {fill:'#3A3A3A'});
    metaTxt(C, x, Z.y + 12, cw, '(' + u.pad2(k + 1) + ')');
    metaTxt(C, x, Z.y + 12, cw, k ? 'Answer' : 'Question', {align:'r'});
    if (t.h) C.text(x, Z.y + 44, cw, hh + 4, t.h, {font:'head', size:hs, color:k ? '#D9D9D9' : '#7A7A7A', lh:0.95, maxLines:3, ed:{f:'x-part', i:t.i, p:0}});
    C.rect(x, Z.y + Z.h - dh - 22, 40, 2, {fill:k ? '#FFFFFF' : '#7A7A7A'});
    C.text(x, Z.y + Z.h - dh - 4, cw, dh + 4, t.d, {font:'body', size:ds, color:'#BDBDBD', lh:1.45, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 5. mega stat: one oversized figure, the rest in a ruled column */
function megaStat(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  function big(t, x, w, y, h){
    var vs = C.measureText(t.v, w, {font:'head', max:Math.min(200, h * 0.6), min:30, maxLines:1}).s;
    C.text(x - 4, y, w + 8, vs * 1.02, t.v, {font:'head', size:vs, color:'#D9D9D9', lh:0.95, maxLines:1, caps:false, ls:-0.03, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(x, y + vs + 16, w, 1, {fill:'#3A3A3A'});
    var le = C.text(x, y + vs + 34, w, 70, t.l, {font:'head', fit:{max:28, min:14}, color:WH, lh:1.05, maxLines:2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x, y + vs + 34 + le._h + 12, w, 50, t.x, {font:'body', fit:{max:17, min:11}, color:GR, lh:1.4, maxLines:2});
  }
  if (n <= 2) { var cw = (Z.w - 80 * (n - 1)) / n; st.forEach(function(t, k){ if (k) C.rect(Z.x + k * (cw + 80) - 40, Z.y, 1, Z.h, {fill:RULE}); metaTxt(C, Z.x + k * (cw + 80), Z.y, cw, '(' + u.pad2(k + 1) + ')'); big(t, Z.x + k * (cw + 80), cw, Z.y + 26, Z.h - 26); }); return; }
  var lw = Z.w * 0.52, rx = Z.x + lw + 60, rw = Z.x + Z.w - rx, rest = st.slice(1), rh = Z.h / rest.length;
  metaTxt(C, Z.x, Z.y, lw, '(01)  —  Headline figure');
  big(st[0], Z.x, lw, Z.y + 30, Z.h - 30);
  C.rect(rx - 30, Z.y, 1, Z.h, {fill:RULE});
  var vs = C.groupFit(rest.map(function(t){ return t.v; }), rw * 0.5, rh * 0.5, {font:'head', max:60, min:22, maxLines:1});
  var ls = C.groupFit(rest.map(function(t){ return t.l; }), rw * 0.46, rh * 0.5, {font:'body', wt:600, max:17, min:11, maxLines:3});
  rest.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.rect(rx, y, rw, 1, {fill:RULE});
    metaTxt(C, rx, y + 10, rw, '(' + u.pad2(k + 2) + ')');
    C.text(rx, y + 26, rw * 0.52, rh - 34, t.v, {font:'head', size:vs, color:'#E8E8E8', va:'m', lh:1, maxLines:1, caps:false, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    C.text(rx + rw * 0.54, y + 26, rw * 0.46, rh - 34, t.l + (t.x ? '' : ''), {font:'body', size:ls, wt:600, color:GR, va:'m', lh:1.35, maxLines:3, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
  });
}
/* 6. ledger: ruled table rows, value flush right */
function statLedger(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var rh = Z.h / n, vw = Math.min(Z.w * 0.42, 420), lw = Z.w - vw - 80;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), vw, rh * 0.8, {font:'head', max:96, min:22, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), lw, rh * 0.42, {font:'head', max:26, min:13, maxLines:2, lh:1.05});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), lw, rh * 0.36, {font:'body', max:16, min:11, maxLines:2});
  st.forEach(function(t, k){
    var y = Z.y + k * rh;
    C.rect(Z.x, y, Z.w, 1, {fill:RULE}); if (k === n - 1) C.rect(Z.x, y + rh, Z.w, 1, {fill:RULE});
    C.text(Z.x, y, 60, rh, '(' + u.pad2(k + 1) + ')', {font:'body', size:13, wt:700, color:'#8C8C8C', va:'m', lh:1});
    var lh2 = C.measureText(t.l, lw, {font:'head', max:ls, min:ls, maxLines:2, lh:1.05}).h, xh = t.x ? C.measureText(t.x, lw, {font:'body', max:xs, min:xs}).h + 6 : 0, by = y + (rh - lh2 - xh) / 2;
    C.text(Z.x + 70, by, lw, lh2 + 2, t.l, {font:'head', size:ls, color:WH, lh:1.05, maxLines:2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(Z.x + 70, by + lh2 + 6, lw, xh, t.x, {font:'body', size:xs, color:GR, lh:1.35});
    C.text(Z.x + Z.w - vw, y, vw, rh, t.v, {font:'head', size:vs, color:k === 0 ? '#FFFFFF' : '#BDBDBD', align:'r', va:'m', lh:1, maxLines:1, caps:false, ls:-0.02, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
  });
}
/* 7. editorial timeline: oversized years standing on a hairline */
function tlEditorial(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, iw = cw - 22;
  var ys = C.groupFit(it.map(function(t){ return t.h || u.pad2(1); }), iw, 160, {font:'head', max:Math.min(150, cw * 0.4), min:20, maxLines:1});
  var F = fhd(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), iw, 1, Z.h - ys - 70, {dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var top = Z.y + Math.max(0, (Z.h - (ys + 60 + th)) / 2), ry = top + ys + 18;
  C.rect(Z.x, ry, Z.w, 1, {fill:'#4A4A4A'});
  var shades = ['#6E6E6E', '#808080', '#939393', '#A8A8A8', '#C4C4C4', '#E0E0E0'];
  it.forEach(function(t, k){
    var x = Z.x + k * cw, sh = shades[Math.max(0, 6 - n + k)], last = k === n - 1;
    C.text(x, top, iw, ys * 1.05, t.h || u.pad2(k + 1), {font:'head', size:ys, color:last ? '#FFFFFF' : sh, lh:1, va:'b', maxLines:1, caps:false, ls:-0.03, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.ell(x - 1, ry - 4, 9, 9, {fill:last ? '#FFFFFF' : '#0A0A0A', line:'#FFFFFF', lw:1.5});
    metaTxt(C, x + 16, ry + 12, iw - 16, u.pad2(k + 1) + ' / ' + u.pad2(n));
    C.text(x, ry + 38, iw, Z.y + Z.h - ry - 38, t.d, {font:'body', size:F.ds, color:last ? '#E8E8E8' : GR, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 8. contents index: numbered ruled entries */
function agendaIndex(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 60, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(96, Z.h / per);
  var y0 = Z.y + (Z.h - rh * per) / 2, nw = Math.min(90, rh * 1.1);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - nw - 60, rh * 0.62, {font:'head', max:n <= 5 ? 34 : 26, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    C.rect(x, y + rh, cw, 1, {fill:RULE}); if (r === 0) C.rect(x, y, cw, 1, {fill:RULE});
    C.text(x, y, nw, rh, u.pad2(k + 1), {font:'head', size:Math.min(rh * 0.6, 44), color:'#5C5C5C', va:'m', lh:1, caps:false, ls:-0.02});
    C.text(x + nw, y, cw - nw - 50, rh, t.h || t.d, {font:'head', size:hs, color:WH, va:'m', lh:1.05, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    C.text(x + cw - 40, y, 40, rh, '→', {font:'body', size:18, color:'#8C8C8C', align:'r', va:'m', lh:1});
  });
}
/* 9. ruled grid: hairline cross-grid with ghost numerals */
function gridRules(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : (n <= 6 ? 3 : 4), rows = Math.ceil(n / cols), cw = Z.w / cols, ch = Z.h / rows, pad = 22, iw = cw - pad * 2;
  var F = fhd(C, it, iw, 50, ch - pad * 2 - 30, {hMax:21, dMax:15.5});
  for (var c = 1; c < cols; c++) C.rect(Z.x + c * cw, Z.y, 1, Z.h, {fill:RULE});
  for (var r = 0; r <= rows; r++) C.rect(Z.x, Z.y + r * ch, Z.w, 1, {fill:r === 0 || r === rows ? '#3A3A3A' : RULE});
  var gs = Math.min(ch * 0.7, cw * 0.5, 140);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * ch;
    C.text(x + cw - gs * 1.25 - 6, y + ch - gs - 2, gs * 1.25, gs, u.pad2(k + 1), {font:'head', size:gs, color:'#1C1C1C', align:'r', lh:1, caps:false, ls:-0.03, maxLines:1});
    metaTxt(C, x + pad, y + pad - 4, iw, 'N° ' + u.pad2(k + 1));
    hdm(C, x + pad, y + pad + 26, iw, ch - pad * 2 - 26, t, F);
  });
}
D.register({
  id:'mono', name:'Mono Portfolio', tag:'Black editorial · portfolio', desc:'Black editorial pages with oversized grey headlines, full-bleed photo layouts and thin meta lines — for portfolios and creative profiles.',
  fonts:{head:'Syne', body:'Inter', headW:800, bodyW:400, headLh:0.95, headCaps:true, headLs:-0.01, gf:'Syne:wght@600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v1','Giant title',{}), V('v2','Title + photo right',{img:true})],
    closing:[V('v1','Giant thank you',{}), V('v2','Thank you + photo',{img:true})],
    agenda:[V('v1','Photo + contents',{}), V('n2','Contents index',{body:agendaIndex}), V('v2','Index + photo right',{body:agendaIndex, img:'right', cols:1})],
    text:[V('v2','Statement + photo right',{body:'text', img:'right'}), V('v1','Statement',{body:'text'}), V('v3','Photo left + statement',{body:'text', img:'left'}), V('v4','Photo top + statement',{body:'text', img:'top'})],
    p2:[V('v1','Big split',{body:bigSplit}), V('n2','Index rows',{body:indexRows}), V('v2','Film strip',{body:filmStrip}), V('v3','Photo left + split',{body:bigSplit, img:'left'}), V('v4','Photo right + index',{body:indexRows, img:'right'})],
    p3:[V('v1','Ghost columns',{body:ghostColumns}), V('n2','Index rows',{body:indexRows}), V('v2','Film strip',{body:filmStrip}), V('v3','Ghost columns + photo top',{body:ghostColumns, img:'top'}), V('v4','Photo left + index',{body:indexRows, img:'left'})],
    p4:[V('v1','Index rows',{body:indexRows}), V('n2','Ghost columns',{body:ghostColumns}), V('n3','Ruled grid',{body:gridRules}), V('v2','Film strip',{body:filmStrip}), V('v3','Photo left + index',{body:indexRows, img:'left'})],
    p56:[V('v1','Index rows',{body:indexRows}), V('n2','Ruled grid',{body:gridRules}), V('v2','Photo right + index',{body:indexRows, img:'right', cols:1})],
    p78:[V('v1','Ruled grid',{body:gridRules}), V('n2','Index rows',{body:indexRows, cols:2}), V('v2','Photo right + index',{body:indexRows, img:'right', cols:1})],
    stats:[V('v1','Mega figure',{body:megaStat}), V('n2','Ledger',{body:statLedger}), V('v2','Photo right + ledger',{body:statLedger, img:'right'}), V('v3','Photo left + ledger',{body:statLedger, img:'left'})],
    timeline:[V('v1','Oversized years',{body:tlEditorial}), V('n2','Index rows',{body:indexRows}), V('v2','Photo right + index',{body:indexRows, img:'right', cols:1})],
    quote:[V('v1','Mono quote',{}), V('v2','Quote + photo right',{img:'right'}), V('v3','Photo left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 24 — Navy Proposal : glossy white pages, navy gradient edge blocks, rotated-square photos, triple chevrons, dot grids */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u;
var NAVY = '#0D2B5E', MID = '#3F5F93', PALE = '#DCE5F2';
var FADE = {ang:90, stops:[[0, '#0D2B5E'], [100, '#0D2B5E', 0]]}, FADEV = {ang:180, stops:[[0, '#0D2B5E'], [100, '#0D2B5E', 0]]};
var P0 = {bg:'#F7F9FC', text:'#17233D', title:'#0D2B5E', muted:'#55627E', accent:'#0D2B5E', accent2:'#3F5F93', card:'#FFFFFF', line:'#D8E0EC', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[NAVY, MID, '#6F8CB8', '#1D4E8F', '#9DB2D3', '#2C4776'],
  header:{kind:'left', tMax:42, tMin:24, sMax:16, top:88, kicker:null},
  card:{kind:'solid', r:4, fill:'#FFFFFF', shadow:0.08, hl:{kind:'grad', grad:{ang:135, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}, dark:true}},
  icon:{kind:'square', r:6, bg:NAVY, fg:'#FFFFFF', grad:{ang:135, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}},
  num:{kind:'square', r:4, bg:NAVY, fg:'#FFFFFF'},
  conc:{kind:'band', grad:{ang:90, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}, r:4, label:'Summary'},
  footer:{brand:false, page:true, color:'#8C98B0'}, xRows:'#FFFFFF', hubFill:'#E6ECF5', road:NAVY, flowBar:'#DCE3EE',
  imgFramed:true, imgR:4
};
function logo(C, x, y){
  var f = C.cover; if (!f.company) return;
  C.shape('diamond', x, y + 2, 18, 18, {line:NAVY, lw:2}); C.shape('diamond', x + 11, y + 2, 18, 18, {line:MID, lw:2});
  C.text(x + 38, y, 300, 22, f.company, {font:'body', size:13, wt:700, color:NAVY, va:'m', lh:1, ed:{f:'tf-company'}});
}
function bg(C, kind){
  C.bg({grad:{ang:120, stops:[[0, '#FFFFFF'], [55, '#F1F4F9'], [100, '#E6EBF3']]}});
  K.poly(C, [[W * 0.35, 0], [W * 0.55, 0], [W * 0.25, H], [W * 0.05, H]], {fill:'#FFFFFF', op:0.5});
  if (kind === 'cover' || kind === 'closing') return;
  var odd = (C.i || 0) % 2;
  logo(C, 70, 34);
  K.chev3(C, W - 150, 38, 26, NAVY, {hollow:true});
  if (odd) { C.rect(0, H - 60, W * 0.5, 22, {grad:FADE}); C.rect(W - 26, 90, 26, 200, {grad:FADEV}); K.dots(C, W - 170, H - 54, 6, 2, 12, 2.2, NAVY, 0.55); }
  else { C.rect(W * 0.5, H - 60, W * 0.5, 22, {grad:{ang:270, stops:[[0, '#0D2B5E'], [100, '#0D2B5E', 0]]}}); C.rect(0, 120, 26, 220, {grad:FADEV}); K.dots(C, 90, H - 54, 6, 2, 12, 2.2, NAVY, 0.55); }
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  logo(C, 70, 40);
  C.rect(0, 250, 40, 300, {grad:FADEV});
  K.diamondImg(C, W - 330, 330, 250, '#EEF1F7', {border:'#FFFFFF', bw:8});
  K.poly(C, [[W - 330, 80], [W - 80, 330], [W - 330, 580], [W - 580, 330]], {stroke:NAVY, lw:2});
  K.chev3(C, 520, 110, 30, NAVY, {hollow:true});
  C.ell(W - 60, 70, 12, 12, {fill:NAVY}); C.ell(W - 60, 96, 12, 12, {fill:NAVY}); C.ell(W - 60, 122, 12, 12, {fill:NAVY});
  C.text(70, 180, 560, 240, C.s.title, {font:'head', fit:{max:66, min:32}, color:NAVY, ed:{f:'title'}, maxLines:3, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(70, 440, 520, 70, C.s.subtitle, {font:'body', fit:{max:18, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var ls = [f.website, f.email].filter(Boolean);
  ls.forEach(function(l, k){ C.ell(70, 560 + k * 30, 18, 18, {fill:NAVY}); C.text(98, 558 + k * 30, 420, 22, l, {font:'body', size:14, wt:600, color:NAVY, va:'m', lh:1}); });
  K.dots(C, 420, H - 70, 8, 3, 14, 2.4, NAVY, 0.6);
}
function closing(C, v){
  var P = C.P;
  logo(C, 70, 40);
  C.rect(0, 250, 40, 300, {grad:FADEV});
  K.diamondImg(C, W - 330, 330, 250, '#EEF1F7', {border:'#FFFFFF', bw:8});
  K.poly(C, [[W - 330, 80], [W - 80, 330], [W - 330, 580], [W - 580, 330]], {stroke:NAVY, lw:2});
  K.chev3(C, 520, 130, 30, NAVY, {hollow:true});
  C.text(70, 180, 560, 180, C.s.title || 'Thank You', {font:'head', fit:{max:72, min:34}, color:NAVY, ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(70, 380, 520, 60, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:P.muted, ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  D.contactList(C, 70, 470, 520, {gap:50});
}
function agenda(C, v){
  var P = C.P;
  D.imgBox(C, 120, 110, 360, 420, {r:4}); C.rect(100, 530, 380, 16, {grad:FADE});
  K.dots(C, 90, H - 110, 6, 4, 14, 2.4, NAVY, 0.6);
  C.text(560, 110, 600, 70, C.s.title || 'Content', {font:'head', fit:{max:46, min:28}, color:NAVY, ed:{f:'title'}, maxLines:1});
  var it = C.it.slice(0, 10), rh = Math.min(50, 400 / Math.max(1, it.length));
  it.forEach(function(t, k){
    var y = 200 + k * rh;
    C.rect(560, y + 8, 34, rh - 16, {grad:{ang:135, stops:[[0, '#0D2B5E'], [100, '#3F5F93']]}, r:3});
    C.text(560, y + 8, 34, rh - 16, u.pad2(k + 1), {font:'body', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1});
    C.text(612, y, 560, rh, t.h || t.d, {font:'body', fit:{max:20, min:12}, wt:600, color:P.text, va:'m', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}
function quote(C, v){
  var o = C.o || {};
  if (o.img) { D.quoteSlide(C, {img:o.img, align:'l', qc:NAVY}); return; }
  D.B.quote(C, {x:200, y:140, w:W - 400, h:H - 280}, {qc:NAVY});
}
/* ================= signature bodies (navy proposal motif: rotated squares, triple chevrons, gradient edges, dot grids) ================= */
var GRAD = {ang:135, stops:[[0, NAVY], [100, MID]]}, TXT = '#17233D', MUT = '#55627E';
function fhd(C, it, w, hb, db, o){
  o = o || {}; o.hMin = o.hMin || 13; o.dMin = o.dMin || 11; var F = K.fitHD(C, it, w, hb, db, o), hh = 0;
  it.forEach(function(t){ if (t.h) hh = Math.max(hh, C.measureText(t.h, w, {font:'head', max:F.hs, min:F.hs, lh:1.15, maxLines:o.hLines || 2}).h); });
  F.hh = hh; return F;
}
function blockH(C, t, F, w){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function hdn(C, x, y, w, h, t, F, o){ o = o || {}; o.tc = o.tc || NAVY; o.mc = o.mc || MUT; return K.hd(C, x, y, w, h, t, F, o); }
function statItems(C){ return C.it.slice(0, 4).map(function(t){ return {i:t.i, v:t.h || String(t.d).split(' ')[0], l:t.h ? t.d : String(t.d).split(' ').slice(1).join(' '), x:t.x, hasH:!!t.h}; }); }
function dia(C, cx, cy, s, o){
  var h = s / 2;
  if (o.fill || o.grad) C.shape('diamond', cx - h, cy - h, s, s, {fill:o.fill, grad:o.grad, op:o.op});
  if (o.line) K.poly(C, [[cx, cy - h], [cx + h, cy], [cx, cy + h], [cx - h, cy]], {stroke:o.line, lw:o.lw || 1.5});
}
function diaNum(C, cx, cy, s, n, o){
  o = o || {};
  if (o.ring) dia(C, cx, cy, s + o.ring, {line:NAVY, lw:1.5});
  dia(C, cx, cy, s, {grad:GRAD});
  C.text(cx - s * 0.35, cy - s * 0.35, s * 0.7, s * 0.7, o.label != null ? o.label : u.pad2(n), {font:'head', size:Math.round(s * (o.fs || 0.3)), wt:800, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.ed || null});
}
/* 1. diamond frames joined by triple chevrons */
function diamondFrames(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.42, Z.h * 0.34, 150), iw = cw - 30;
  var F = fhd(C, it, iw, 60, Z.h - s - 90, {hMax:22, dMax:18});
  var th = 0; it.forEach(function(t){ th = Math.max(th, blockH(C, t, F, iw)); });
  var top = Z.y + Math.max(0, (Z.h - (s + 28 + 40 + th)) / 2), cy = top + s / 2 + 14;
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    diaNum(C, cx, cy, s, k + 1, {ring:28});
    if (k < n - 1) K.chev3(C, cx + cw / 2 - 16, cy - 8, 16, MID, {hollow:true});
    C.rect(cx - 18, cy + s / 2 + 26, 36, 3, {fill:NAVY});
    hdn(C, cx - iw / 2, cy + s / 2 + 42, iw, th + 4, t, F, {align:'c'});
  });
}
/* 2. rows with a gradient edge, big number and triple chevron */
function chevronRows(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 30, cw = (Z.w - gx * (cols - 1)) / cols, gy = per > 5 ? 8 : 14, rh = Math.min(130, (Z.h - gy * (per - 1)) / per);
  var y0 = Z.y + (Z.h - rh * per - gy * (per - 1)) / 2, ns = Math.min(34, rh * 0.5), tx = 22 + ns * 1.5 + 44, tw = cw - tx - 20;
  var F = fhd(C, it, tw, Math.min(34, rh * 0.42), rh - 12, {hMax:20, dMax:16, hLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    C.rect(x, y, cw, rh, {fill:'#FFFFFF', r:4, shadow:0.07});
    C.rect(x, y, 8, rh, {grad:{ang:180, stops:[[0, NAVY], [100, MID]]}});
    C.rect(x + 8, y, cw * 0.35, 3, {grad:{ang:90, stops:[[0, NAVY, 0.9], [100, NAVY, 0]]}});
    C.text(x + 22, y, ns * 1.5, rh, u.pad2(k + 1), {font:'head', size:ns, wt:800, color:NAVY, va:'m', lh:1, caps:false, ls:0});
    K.chev3(C, x + 22 + ns * 1.5 + 6, y + rh / 2 - 6, 12, MID, {hollow:true});
    var bhh = blockH(C, t, F, tw);
    hdn(C, x + tx, y + (rh - bhh) / 2, tw, bhh + 4, t, F, {hLines:1});
  });
}
/* 3. cards with a diamond badge, gradient top edge and dot grid */
function diamondCards(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4)), rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var s = Math.min(54, ch * 0.34), side = cw > 380, pad = 22, tx = side ? pad + s + 24 : pad, tw = cw - tx - pad;
  var F = fhd(C, it, tw, 48, ch - pad * 2 - (side ? 0 : s + 14), {hMax:21, dMax:16});
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), shx = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + shx + c * (cw + gx), y = Z.y + r * (ch + gy), hl = k === 0;
    C.rect(x, y, cw, ch, hl ? {grad:GRAD, r:4} : {fill:'#FFFFFF', r:4, shadow:0.07});
    if (!hl) C.rect(x, y, cw * 0.6, 4, {grad:{ang:90, stops:[[0, NAVY], [100, NAVY, 0]]}});
    K.dots(C, x + cw - 58, y + ch - 30, 4, 2, 11, 1.8, hl ? '#FFFFFF' : NAVY, 0.35);
    var bhh = blockH(C, t, F, tw);
    if (side) {
      var cy = y + ch / 2; dia(C, x + pad + s / 2, cy, s + 12, {line:hl ? '#FFFFFF' : NAVY, lw:1.2});
      dia(C, x + pad + s / 2, cy, s, hl ? {fill:'#FFFFFF'} : {grad:GRAD});
      C.text(x + pad, cy - s / 2, s, s, u.pad2(k + 1), {font:'head', size:Math.round(s * 0.3), wt:800, color:hl ? NAVY : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
      hdn(C, x + tx, cy - bhh / 2, tw, bhh + 4, t, F, hl ? {tc:'#FFFFFF', mc:'#DCE5F2'} : {});
    } else {
      var blk = s + 14 + bhh, by = y + Math.max(pad, (ch - blk) / 2);
      dia(C, x + pad + s / 2, by + s / 2, s, hl ? {fill:'#FFFFFF'} : {grad:GRAD});
      C.text(x + pad, by, s, s, u.pad2(k + 1), {font:'head', size:Math.round(s * 0.3), wt:800, color:hl ? NAVY : '#FFFFFF', align:'c', va:'m', lh:1, caps:false});
      hdn(C, x + tx, by + s + 14, tw, bhh + 4, t, F, hl ? {tc:'#FFFFFF', mc:'#DCE5F2'} : {});
    }
  });
}
/* 4. proposal split: navy panel vs white panel, diamond hand-off */
function proposalSplit(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var cw = Z.w / n, pad = Math.min(48, cw * 0.09), iw = cw - pad * 2 - (n > 1 ? 30 : 0);
  var F = fhd(C, it, iw, 90, Z.h - pad * 2 - 40, {hMax:28, dMax:20, noBoost:true});
  it.forEach(function(t, k){
    var x = Z.x + k * cw, dark = k === 0, tx = x + pad + (k ? 30 : 0);
    C.rect(x, Z.y, cw, Z.h, dark ? {grad:GRAD, r:4} : {fill:'#FFFFFF', r:4, shadow:0.08});
    if (dark) K.dots(C, x + 26, Z.y + Z.h - 44, 6, 2, 12, 2, '#FFFFFF', 0.35); else K.dots(C, x + cw - 96, Z.y + Z.h - 44, 6, 2, 12, 2, NAVY, 0.4);
    var bhh = blockH(C, t, F, iw), by = Z.y + (Z.h - bhh - 40) / 2;
    C.text(tx, by, iw, 18, u.pad2(k + 1) + '  /  ' + (k ? 'Our answer' : 'The situation'), {font:'body', size:12, wt:700, color:dark ? '#C9D6EA' : MID, caps:true, ls:0.14, lh:1});
    C.rect(tx, by + 24, 44, 3, {fill:dark ? '#FFFFFF' : NAVY});
    hdn(C, tx, by + 40, iw, bhh + 4, t, F, dark ? {tc:'#FFFFFF', mc:'#DCE5F2'} : {});
  });
  if (n === 2) { var mx = Z.x + cw, my = Z.y + Z.h / 2; dia(C, mx, my, 104, {fill:'#FFFFFF', line:NAVY, lw:2}); K.chev3(C, mx - 24, my - 11, 22, NAVY, {hollow:true}); }
}
/* 5. stats inside rotated-square frames */
function diamondStats(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.74, Z.h * 0.58, 250), iw = cw - 24;
  var vs = C.groupFit(st.map(function(t){ return t.v; }), s * 0.58, s * 0.3, {font:'head', max:Math.round(s * 0.24), min:18, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 50, {font:'head', max:n <= 2 ? 22 : 18, min:13, maxLines:2});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, 40, {font:'body', max:15, min:11, maxLines:2});
  var blk = s + 26 + ls * 1.2 * 2 + 8 + xs * 1.35 * 2, top = Z.y + Math.max(0, (Z.h - blk) / 2), cy = top + s / 2;
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), hl = k === 0;
    dia(C, cx, cy, s, {line:NAVY, lw:1.5});
    dia(C, cx, cy, s - 26, hl ? {grad:GRAD} : {fill:'#FFFFFF'});
    if (!hl) dia(C, cx, cy, s - 26, {line:'#C7D3E6', lw:1.5});
    C.text(cx - s * 0.29, cy - s * 0.15, s * 0.58, s * 0.3, t.v, {font:'head', size:vs, color:hl ? '#FFFFFF' : NAVY, align:'c', va:'m', lh:1, maxLines:1, caps:false, ls:0, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    var le = C.text(cx - iw / 2, cy + s / 2 + 22, iw, ls * 1.2 * 2 + 2, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(cx - iw / 2, cy + s / 2 + 22 + le._h + 8, iw, xs * 1.35 * 2 + 2, t.x, {font:'body', size:xs, color:MUT, align:'c', lh:1.35});
  });
}
/* 6. navy gradient band of figures, labels underneath */
function statBand(C, Z, o){
  var st = statItems(C), n = st.length; if (!n) return;
  var cw = Z.w / n, iw = cw - 30, bh = Math.min(Z.h * 0.42, 180);
  var vs = C.groupFit(st.map(function(t){ return t.v; }), iw, bh * 0.55, {font:'head', max:72, min:20, maxLines:1});
  var ls = C.groupFit(st.map(function(t){ return t.l; }), iw, 80, {font:'head', max:21, min:13, maxLines:3, lh:1.2});
  var lh3 = 0; st.forEach(function(t){ lh3 = Math.max(lh3, C.measureText(t.l, iw, {font:'head', max:ls, min:ls, maxLines:3, lh:1.2}).h); });
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, 44, {font:'body', max:16, min:11, maxLines:2});
  var blk = bh + 30 + lh3 + 10 + xs * 1.35 * 2, top = Z.y + Math.max(0, (Z.h - blk) / 2);
  C.rect(Z.x, top, Z.w, bh, {grad:{ang:90, stops:[[0, NAVY], [100, MID]]}, r:4});
  K.chev3(C, Z.x + Z.w - 64, top + 16, 16, '#FFFFFF', {hollow:true});
  K.dots(C, Z.x + 20, top + bh - 26, 6, 2, 11, 1.8, '#FFFFFF', 0.35);
  st.forEach(function(t, k){
    var x = Z.x + k * cw;
    if (k) C.rect(x, top + 28, 1, bh - 56, {fill:'#FFFFFF', op:0.3});
    C.text(x + 15, top, iw, bh, t.v, {font:'head', size:vs, color:'#FFFFFF', align:'c', va:'m', lh:1, maxLines:1, caps:false, ls:0, ed:t.hasH ? {f:'x-part', i:t.i, p:0} : null});
    dia(C, x + cw / 2, top + bh, 16, {fill:'#FFFFFF', line:NAVY, lw:2});
    var le = C.text(x + 15, top + bh + 26, iw, lh3 + 2, t.l, {font:'head', size:ls, color:NAVY, align:'c', lh:1.2, ed:t.hasH ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 15, top + bh + 26 + le._h + 8, iw, xs * 1.35 * 2 + 2, t.x, {font:'body', size:xs, color:MUT, align:'c', lh:1.35});
  });
}
/* 7. diamond rail timeline: years above a fading rail, notes below */
function tlDiamondRail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, iw = cw - 26;
  var ys = C.groupFit(it.map(function(t){ return t.h || '00'; }), iw, 60, {font:'head', max:Math.min(54, cw * 0.22), min:14, maxLines:1});
  var F = fhd(C, it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), iw - 24, 1, Z.h - ys - 110, {dMax:18.5});
  var th = 0; it.forEach(function(t){ th = Math.max(th, C.measureText(t.d, iw - 24, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h); });
  var top = Z.y + Math.max(0, (Z.h - (ys + 40 + 44 + th + 40)) / 2), ry = top + ys + 40, cardY = ry + 40, ch = th + 40;
  C.rect(Z.x, ry - 1.5, Z.w, 3, {grad:{ang:90, stops:[[0, NAVY, 0], [12, NAVY], [88, NAVY], [100, NAVY, 0]]}});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    C.text(cx - iw / 2, top, iw, ys * 1.1, t.h || u.pad2(k + 1), {font:'head', size:ys, color:k === n - 1 ? MID : NAVY, align:'c', lh:1, maxLines:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    dia(C, cx, ry, 34, {fill:'#F7F9FC', line:NAVY, lw:1.5});
    dia(C, cx, ry, 18, {grad:GRAD});
    if (k < n - 1) K.chev3(C, cx + cw / 2 - 12, ry - 22, 10, MID);
    C.rect(cx - iw / 2, cardY, iw, ch, {fill:'#FFFFFF', r:4, shadow:0.07});
    C.rect(cx - iw / 2, cardY, iw, 4, {grad:{ang:90, stops:[[0, NAVY], [100, MID]]}});
    C.text(cx - iw / 2 + 12, cardY + 20, iw - 24, ch - 26, t.d, {font:'body', size:F.ds, color:TXT, align:'c', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
  });
}
/* 8. vertical quarters: fading edge rail with diamond nodes */
function tlQuarters(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var rh = Z.h / n, s = Math.min(30, rh * 0.5), rx = Z.x + 16, yw = Math.min(150, Z.w * 0.2), wide = Z.w > 760;
  var ys = C.groupFit(it.map(function(t){ return t.h || '00'; }), yw, rh * 0.7, {font:'head', max:40, min:14, maxLines:1});
  var tx = rx + s + 30, dx = wide ? tx + yw + 20 : tx, dw = Z.x + Z.w - dx;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw, wide ? rh - 12 : rh - ys * 1.1 - 12, {font:'body', max:20, min:11, lh:1.36});
  C.rect(rx - 2, Z.y, 4, Z.h, {grad:{ang:180, stops:[[0, NAVY, 0], [10, NAVY], [90, MID], [100, MID, 0]]}});
  it.forEach(function(t, k){
    var y = Z.y + k * rh, cy = y + rh / 2;
    dia(C, rx, cy, s + 12, {fill:'#F7F9FC', line:NAVY, lw:1.2});
    dia(C, rx, cy, s, {grad:GRAD});
    if (k) C.rect(tx, y, Z.x + Z.w - tx, 1, {fill:'#D8E0EC'});
    var dh = C.measureText(t.d, dw, {font:'body', max:ds, min:ds, lh:1.36}).h;
    if (wide) {
      C.text(tx, y, yw, rh, t.h || u.pad2(k + 1), {font:'head', size:ys, color:NAVY, va:'m', lh:1, maxLines:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      C.text(dx, cy - dh / 2, dw, dh + 4, t.d, {font:'body', size:ds, color:TXT, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      var by = cy - (ys * 1.1 + 4 + dh) / 2;
      C.text(tx, by, dw, ys * 1.1, t.h || u.pad2(k + 1), {font:'head', size:ys, color:NAVY, lh:1, maxLines:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      C.text(tx, by + ys * 1.1 + 4, dw, dh + 4, t.d, {font:'body', size:ds, color:TXT, lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    }
  });
}
/* 9. agenda: diamond-numbered ruled list */
function agendaDiamond(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 && Z.w > 900 ? 2 : 1), per = Math.ceil(n / cols), gx = 60, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(90, Z.h / per);
  var y0 = Z.y + (Z.h - rh * per) / 2, s = Math.min(48, rh * 0.7), tx = s + 30;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - tx - 60, rh * 0.6, {font:'head', max:n <= 5 ? 26 : 21, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    diaNum(C, x + s / 2 + 4, y + rh / 2, s, k + 1, {fs:0.3});
    C.text(x + tx, y, cw - tx - 60, rh, t.h || t.d, {font:'head', size:hs, color:NAVY, va:'m', lh:1.1, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    K.chev3(C, x + cw - 34, y + rh / 2 - 6, 12, MID, {hollow:true});
    if (r < per - 1 && k < n - 1) C.rect(x + tx, y + rh - 0.5, cw - tx, 1, {fill:'#D8E0EC'});
  });
}
D.register({
  id:'navyproposal', name:'Navy Proposal', tag:'Glossy white · proposals', desc:'Glossy white pages, navy gradient edge blocks, rotated-square photo frames, triple chevrons and dot grids for business proposals.',
  fonts:{head:'Raleway', body:'Open Sans', headW:800, bodyW:400, headLh:1.02, headCaps:true, gf:'Raleway:wght@600;700;800|Open Sans:wght@400;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, agenda:agenda, quote:quote,
  variants:{
    cover:[V('v2','Diamond photo',{img:'diamond'})],
    closing:[V('v2','Diamond photo + contacts',{img:'diamond'})],
    agenda:[V('v2','Photo + contents',{}), V('n2','Diamond list',{body:agendaDiamond}), V('v3','Diamond list + image right',{body:agendaDiamond, img:'right', cols:1})],
    text:[V('v1','Statement',{body:'text'}), V('n2','Statement card',{body:'text', box:true}), V('v2','Statement + image right',{body:'text', img:'right'}), V('v3','Image left + statement',{body:'text', img:'left'})],
    p2:[V('v1','Proposal split',{body:proposalSplit}), V('n2','Diamond frames',{body:diamondFrames}), V('n3','Chevron rows',{body:chevronRows}), V('v2','Image left + chevron rows',{body:chevronRows, img:'left'}), V('v3','Split + image right',{body:proposalSplit, img:'right'})],
    p3:[V('v1','Diamond frames',{body:diamondFrames}), V('n2','Chevron rows',{body:chevronRows}), V('n3','Diamond cards',{body:diamondCards}), V('n4','Diamond path',{body:'xDiamonds'}), V('v2','Image left + chevron rows',{body:chevronRows, img:'left'}), V('v3','Chevron rows + image right',{body:chevronRows, img:'right'})],
    p4:[V('v1','Diamond frames',{body:diamondFrames}), V('n2','Diamond cards',{body:diamondCards}), V('n3','Chevron rows',{body:chevronRows}), V('n4','Diamond path',{body:'xDiamonds'}), V('v2','Chevron rows + image right',{body:chevronRows, img:'right'})],
    p56:[V('v1','Diamond cards',{body:diamondCards}), V('n2','Chevron rows',{body:chevronRows}), V('n3','Diamond path',{body:'xDiamonds'}), V('v2','Chevron rows + image right',{body:chevronRows, img:'right'})],
    p78:[V('v1','Diamond cards',{body:diamondCards}), V('n2','Chevron rows',{body:chevronRows, cols:2}), V('v2','Chevron rows + image right',{body:chevronRows, img:'right', cols:1})],
    stats:[V('v1','Diamond figures',{body:diamondStats}), V('n2','Navy band',{body:statBand}), V('v2','Band + image right',{body:statBand, img:'right'}), V('v3','Image left + band',{body:statBand, img:'left'})],
    timeline:[V('v1','Diamond rail',{body:tlDiamondRail}), V('n2','Quarter rail',{body:tlQuarters}), V('n3','Diamond path',{body:'xDiamonds'}), V('v2','Quarter rail + image left',{body:tlQuarters, img:'left'}), V('v3','Quarter rail + image right',{body:tlQuarters, img:'right'})],
    quote:[V('v1','Navy quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 25 — Retro Grid Pop : cream grid paper, chunky indigo headlines, charcoal pills with offset shadows, periwinkle starburst / X stickers, tilted tags, torn ticket stubs */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2;
var INK = '#2B2E6B', CHAR = '#2D2D2D', PERI = '#9DB3E6', CREAM = '#F1ECDD', WHITE = '#FFFFFF', SOFT = '#D9DCEB', MUT = '#4C4E66';
var P0 = {bg:'#F1ECDD', text:'#23243F', title:'#2B2E6B', muted:'#4C4E66', accent:'#2B2E6B', accent2:'#9DB3E6', card:'#2D2D2D', line:'#D6CFBC', onAccent:'#FFFFFF'};
var S = {
  margin:64, series:[INK, '#5C73C7', PERI, CHAR, '#7C8FD8', '#3E4390'],
  header:{kind:'left', tMax:44, tMin:26, sMax:16, top:62},
  card:{kind:'solid', r:22, fill:'#2D2D2D', dark:true, tc:'#FFFFFF', mc:'#D8D8D8', hl:{kind:'solid', fill:INK, dark:true}},
  icon:{kind:'circle', bg:PERI, fg:INK},
  num:{kind:'circle', bg:INK, fg:'#FFFFFF'},
  conc:{kind:'box', fill:'#2D2D2D', r:22, tc:'#FFFFFF', lc:PERI, label:'Why it matters'},
  footer:{fn:function(C){ strip(C); }}, xRows:'#FFFFFF', hubFill:'#E7E1CF', road:CHAR, flowBar:'#E3DCC8',
  imgFramed:true, imgR:18
};

/* ---------- sticker kit ---------- */
function burst(C, cx, cy, r, fill){ K.starburst(C, cx, cy, r, 12, fill || PERI, {line:INK, lw:3, inner:0.62}); }
function cross(C, cx, cy, s, rot, fill){ K.xmark(C, cx, cy, s, fill || PERI, {line:INK, lw:Math.max(2, s * 0.05), rot:rot}); }
function ringDot(C, cx, cy, r){ C.ell(cx - r, cy - r, 2 * r, 2 * r, {fill:WHITE, line:INK, lw:3}); C.ell(cx - r * 0.55, cy - r * 0.55, r * 1.1, r * 1.1, {fill:CHAR}); C.ell(cx - r * 0.2, cy - r * 0.2, r * 0.4, r * 0.4, {fill:WHITE}); }
/* sticker slab: solid ink offset shadow + filled body with ink outline */
function slab(C, x, y, w, h, fill, r, d){
  d = d == null ? 7 : d;
  C.rect(x + d, y + d, w, h, {fill:INK, r:r});
  C.rect(x, y, w, h, {fill:fill, r:r, line:INK, lw:2.5});
}
/* rotated tag (rotation on the shape only; text stays level so it keeps reading) */
function tag(C, x, y, w, h, rot, fill, text, tc, o){
  o = o || {};
  C.shape('rect', x + 4, y + 4, w, h, {fill:INK, rot:rot});
  C.shape('rect', x, y, w, h, {fill:fill, line:INK, lw:2.5, rot:rot});
  C.text(x + 6, y, w - 12, h, text, {font:o.font || 'body', size:o.size || Math.round(h * 0.44), wt:800, color:tc, align:'c', va:'m', lh:1, caps:!!o.caps, ls:o.ls || 0, ed:o.ed || null, maxLines:1});
}
/* width a single line of text needs */
function lineW(C, s, size, font, wt, maxW, caps, ls){
  for (var w = 40; w < maxW; w += 8) { var m = C.measureText(s, w, {font:font, wt:wt, caps:caps, ls:ls, max:size, min:Math.max(6, size * 0.7), maxLines:1}); if (m.n === 1 && m.s >= size) return w; }
  return maxW;
}
function F2(C, it, w, hBox, dBox, hMax, dMax, hLines){ return K.fitHD(C, it, w, hBox, dBox, {hMax:hMax, dMax:dMax, hMin:13, dMin:11, hLines:hLines || 2}); }
function dH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + dH(C, t, w, F); }
var ROT = [-5, 4, -3, 6, -6, 3, -4, 5];

/* ---------- page chrome ---------- */
function strip(C){
  var f = C.cover;
  if (f.company) C.text(64, 24, 400, 18, f.company, {font:'body', size:12, wt:700, color:CHAR, lh:1, ed:{f:'tf-company'}});
  C.rect(W - 170, 20, 106, 26, {fill:CHAR, r:13});
  C.text(W - 170, 20, 58, 26, 'Page', {font:'body', size:11, wt:700, color:WHITE, align:'c', va:'m', lh:1});
  C.ell(W - 104, 22, 36, 22, {fill:PERI}); C.text(W - 104, 22, 36, 22, pad2(C.i + 1), {font:'body', size:11, wt:800, color:INK, align:'c', va:'m', lh:1});
  C.text(64, H - 36, 400, 18, f.website || '', {font:'body', size:12, wt:700, color:CHAR, lh:1});
  if (f.presenter) C.text(W - 464, H - 36, 400, 18, 'Presented by ' + f.presenter, {font:'body', size:12, wt:700, color:CHAR, align:'r', lh:1});
}
function bg(C, kind){
  C.bg({fill:CREAM}); K.grid(C, 44, '#D9D1BB', 0.9);
  if (kind !== 'cover' && kind !== 'closing' && kind !== 'quote') { var odd = (C.i || 0) % 2; if (odd) burst(C, W - 92, 96, 34); else cross(C, W - 92, 94, 46, 12); }
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* 1. charcoal pills with offset ink shadow, periwinkle X-sticker bullet */
function rpPills(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 34, gy = n > 5 ? 16 : 20, cw = (Z.w - gx * (cols - 1) - 8) / cols;
  var rh = Math.min(o.rMax || 150, (Z.h - 8 - gy * (per - 1)) / per), bs = Math.min(50, rh * 0.52), tx = bs + 44, tw = cw - tx - 30;
  var F = F2(C, it, tw, rh * 0.36, rh - 22, n <= 3 ? 20 : 18, n <= 3 ? 16 : 14.5, 1);
  var y0 = Z.y + Math.max(0, (Z.h - 8 - (rh * per + gy * (per - 1))) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    slab(C, x, y, cw, rh, CHAR, Math.min(rh / 2, 34), 6);
    cross(C, x + 20 + bs / 2, y + rh / 2, bs, ROT[k % 8] * 2);
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + tx, y + Math.max(8, (rh - bh) / 2), tw, rh - 12, t, F, {tc:PERI, mc:'#ECECEC', hLines:1});
  });
}

/* 2. outlined cream cards topped with a tilted periwinkle tag */
function rpTagCards(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : 3, rows = Math.ceil(n / cols), gx = n >= 4 ? 22 : 30, gy = 40, cw = (Z.w - gx * (cols - 1) - 8) / cols;
  var top = 20, ch = (Z.h - top - 8 - (gy - top) * (rows - 1) - top * (rows - 1)) / rows;
  ch = Math.min(ch, 330);
  var pad = 22, tw = cw - pad * 2, tagH = o.tagH ? 40 : (rows > 1 ? 32 : 38);
  var tagFs = o.tagH ? 19 : 15;
  var F = F2(C, o.tagH ? it.map(function(t){ return {i:t.i, h:'', d:t.d}; }) : it, tw, 60, ch - tagH - 30 - pad, n <= 3 ? 22 : 19, o.tagH ? 20 : (n <= 3 ? 16 : 14.5), 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + sh + c * (cw + gx), y = Z.y + top + r * (ch + gy);
    slab(C, x, y, cw, ch, WHITE, 20, 7);
    var lab = o.tagH && t.h ? t.h : 'No. ' + pad2(k + 1), tgw = Math.min(cw - 30, lineW(C, lab, tagFs, 'body', 800, cw) + 30);
    tag(C, x + 18, y - tagH / 2 - 4, tgw, tagH, ROT[k % 8], k % 2 ? CHAR : PERI, lab, k % 2 ? WHITE : INK, {size:tagFs, ed:o.tagH && t.h ? {f:'x-part', i:t.i, p:0} : null});
    var tt = o.tagH ? {i:t.i, h:'', d:t.d} : t, bh = blockH(C, tt, tw, F), ty = y + tagH / 2 + 18;
    K.hd(C, x + pad, ty + Math.max(0, (y + ch - pad - ty - bh) * 0.35), tw, y + ch - pad - ty, tt, F, {tc:INK, mc:MUT});
  });
}

/* 3. charcoal cards with a starburst number badge */
function rpBurst(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))), rows = Math.ceil(n / cols), gx = 24, gy = 22;
  var cw = (Z.w - gx * (cols - 1) - 8) / cols, ch = (Z.h - gy * (rows - 1) - 8) / rows, pad = 22;
  var br = Math.min(34, ch * 0.2), side = cols <= 2;                       /* wide cards: text beside the burst */
  var tx = side ? pad + br * 2 + 22 : pad, tw = cw - tx - pad;
  var hTop = side ? 0 : br * 2 + 16;
  var F = F2(C, it, tw, 60, ch - pad * 2 - hTop, n <= 3 ? 21 : 18, n <= 3 ? 16 : 14, 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + sh + c * (cw + gx), y = Z.y + r * (ch + gy);
    slab(C, x, y, cw, ch, CHAR, 22, 7);
    var bh = blockH(C, t, tw, F);
    var by = side ? y + Math.max(pad, (ch - Math.max(bh, br * 2)) / 2) : y + Math.max(pad, (ch - hTop - bh) / 2);
    burst(C, x + pad + br, by + br, br + 4, k % 2 ? WHITE : PERI);
    C.text(x + pad, by, br * 2, br * 2, pad2(k + 1), {font:'head', size:Math.round(br * 0.62), color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    var ty = side ? by + Math.max(0, (br * 2 - bh) / 2) : by + hTop;
    K.hd(C, x + tx, ty, tw, y + ch - pad - ty, t, F, {tc:WHITE, mc:'#DADADA'});
  });
}

/* 4. torn ticket stubs: periwinkle stub with number, perforation, torn right edge */
function rpTickets(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 ? 2 : 1), per = Math.ceil(n / cols), gx = 36, gy = n > 4 ? 16 : 22, cw = (Z.w - gx * (cols - 1) - 8) / cols;
  var rh = Math.min(o.rMax || 140, (Z.h - 8 - gy * (per - 1)) / per), stub = o.stubYear ? Math.min(150, cw * 0.26) : Math.min(104, cw * 0.2);
  var tx = stub + 26, tw = cw - tx - 34;
  var tt = it.map(function(t){ return o.stubYear ? {i:t.i, h:'', d:t.d} : t; });
  var F = F2(C, tt, tw, rh * 0.38, rh - 20, n <= 3 ? 21 : 18, o.stubYear ? 19 : (n <= 3 ? 16.5 : 15), 1);
  var ss = C.groupFit(it.map(function(t){ return o.stubYear ? (t.h || '') : '00'; }), stub - 20, rh - 20, {font:'head', max:o.stubYear ? 30 : 36, min:13, maxLines:1});
  var y0 = Z.y + Math.max(0, (Z.h - 8 - (rh * per + gy * (per - 1))) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    /* torn right edge: zig-zag */
    var pts = [[x, y], [x + cw - 12, y]], teeth = Math.max(4, Math.round(rh / 14)), th = rh / teeth;
    for (var j = 0; j < teeth; j++) { pts.push([x + cw, y + th * (j + 0.5)]); pts.push([x + cw - 12, y + th * (j + 1)]); }
    pts.push([x, y + rh]);
    K.poly(C, pts.map(function(p){ return [p[0] + 6, p[1] + 6]; }), {fill:INK});
    K.poly(C, pts, {fill:WHITE, stroke:INK, lw:2.5});
    C.rect(x + 1.5, y + 1.5, stub - 1.5, rh - 3, {fill:k % 2 ? CHAR : PERI});
    C.line(x + stub, y + 10, x + stub, y + rh - 10, {color:INK, lw:2, dash:true});
    C.ell(x + stub - 9, y - 9, 18, 18, {fill:CREAM, line:INK, lw:2.5}); C.ell(x + stub - 9, y + rh - 9, 18, 18, {fill:CREAM, line:INK, lw:2.5});
    C.rect(x + stub - 11, y - 12, 22, 10, {fill:CREAM}); C.rect(x + stub - 11, y + rh + 2, 22, 10, {fill:CREAM});
    var lab = o.stubYear ? (t.h || pad2(k + 1)) : pad2(k + 1);
    C.text(x + 6, y, stub - 12, rh, lab, {font:'head', size:ss, color:k % 2 ? PERI : INK, align:'c', va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.stubYear && t.h ? {f:'x-part', i:t.i, p:0} : null});
    var tk = tt[k], bh = blockH(C, tk, tw, F);
    K.hd(C, x + tx, y + Math.max(8, (rh - bh) / 2), tw, rh - 12, tk, F, {tc:INK, mc:MUT, hLines:1});
  });
}

/* 5. open list with X-sticker bullets and dotted rules */
function rpXList(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n > 4 ? 2 : 1), per = Math.ceil(n / cols), gx = 44, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(130, Z.h / per);
  var bs = Math.min(44, rh * 0.56), tx = bs + 24, tw = cw - tx;
  var F = F2(C, it, tw, rh * 0.4, rh - 14, n <= 4 ? 21 : 18, n <= 4 ? 16 : 14.5, 1);
  var y0 = Z.y + Math.max(0, (Z.h - rh * per) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    cross(C, x + bs / 2, y + rh / 2, bs, ROT[k % 8] * 3, k % 3 === 1 ? WHITE : PERI);
    var bh = blockH(C, t, tw, F);
    K.hd(C, x + tx, y + Math.max(4, (rh - bh) / 2), tw, rh - 8, t, F, {tc:INK, mc:MUT, hLines:1});
    if (r < per - 1) C.line(x + tx, y + rh, x + cw, y + rh, {color:INK, lw:1.5, dash:true, op:0.45});
  });
}

/* 6. two big sticker panels (charcoal + periwinkle) joined by an X sticker */
function rpDuo(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (!n) return;
  var gap = 70, cw = (Z.w - gap - 8) / 2, ch = Z.h - 8, pad = 36;
  var F = F2(C, it, cw - pad * 2, 80, ch - pad * 2 - 90, 26, 19, 2);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), y = Z.y, fill = k ? PERI : CHAR, tc = k ? INK : PERI, mc = k ? '#1F2150' : '#ECECEC';
    slab(C, x, y, cw, ch, fill, 30, 8);
    tag(C, x + pad, y + pad - 4, 118, 40, ROT[k] , k ? WHITE : PERI, 'No. ' + pad2(k + 1), INK, {size:16});
    var bh = blockH(C, t, cw - pad * 2, F), ty = y + pad + 70;
    K.hd(C, x + pad, ty + Math.max(0, (y + ch - pad - ty - bh) * 0.4), cw - pad * 2, y + ch - pad - ty, t, F, {tc:tc, mc:mc});
  });
  if (n === 2) { cross(C, Z.x + cw + gap / 2 + 4, Z.y + ch / 2, 74, 8, WHITE); }
}

/* 7. stats as big starburst stickers + a label pill sized to its text */
function rpStatStickers(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var vals = st.map(function(t){ return t.h || String(t.d).split(' ')[0]; }), labs = st.map(function(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); });
  var cw = Z.w / n, r = Math.min(cw * 0.36, Z.h * 0.3, 118), cy = Z.y + r + 10;
  var vs = C.groupFit(vals, r * 1.2, r * 0.6, {font:'head', max:52, min:18, maxLines:1});
  var lw = cw - 40, ls = C.groupFit(labs, lw - 36, 60, {font:'body', wt:700, max:n <= 2 ? 19 : 16, min:11.5, maxLines:2, lh:1.25});
  var xs = st.map(function(t){ return t.x || ''; }), xsz = C.groupFit(xs, cw - 30, Z.y + Z.h - (cy + r + 110), {font:'body', max:15, min:11, lh:1.35});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    K.starburst(C, cx + 6, cy + 6, r, 14, INK, {inner:0.8});
    K.starburst(C, cx, cy, r, 14, k % 2 ? WHITE : PERI, {line:INK, lw:3, inner:0.8});
    C.text(cx - r * 0.62, cy - r * 0.35, r * 1.24, r * 0.7, vals[k], {font:'head', size:vs, color:INK, align:'c', va:'m', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    var m = C.measureText(labs[k], lw - 36, {font:'body', wt:700, max:ls, min:ls, lh:1.25}), one = m.n === 1;
    var pw = one ? Math.min(lw, lineW(C, labs[k], ls, 'body', 700, lw) + 36) : lw, ph = m.h + 22, py = cy + r + 22;
    C.rect(cx - pw / 2, py, pw, ph, {fill:CHAR, r:Math.min(ph / 2, 22)});
    C.text(cx - pw / 2 + 18, py, pw - 36, ph, labs[k], {font:'body', size:ls, wt:700, color:WHITE, align:'c', va:'m', lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(Z.x + cw * k + 15, py + ph + 12, cw - 30, Z.y + Z.h - (py + ph + 12), t.x, {font:'body', size:xsz, color:MUT, align:'c', lh:1.35});
  });
}

/* 8. stats in tall charcoal pills (columns) or pill rows when the zone is narrow */
function rpStatPills(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var vals = st.map(function(t){ return t.h || String(t.d).split(' ')[0]; }), labs = st.map(function(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); });
  var rows = o.rows || Z.w / n < 230;
  if (rows) {
    var gy = 16, rh = Math.min(120, (Z.h - 8 - gy * (n - 1)) / n), vw = Math.min(230, Z.w * 0.4), y0 = Z.y + Math.max(0, (Z.h - 8 - rh * n - gy * (n - 1)) / 2);
    var vs = C.groupFit(vals, vw - 40, rh - 16, {font:'head', max:40, min:18, maxLines:1});
    var lsz = C.groupFit(labs, Z.w - vw - 60, rh * 0.5, {font:'body', wt:700, max:18, min:12, maxLines:2, lh:1.25});
    var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), Z.w - vw - 60, rh * 0.4, {font:'body', max:14, min:11, maxLines:2});
    st.forEach(function(t, k){
      var y = y0 + k * (rh + gy);
      slab(C, Z.x, y, Z.w - 8, rh, CHAR, rh / 2, 6);
      C.rect(Z.x + 8, y + 8, vw - 16, rh - 16, {fill:k % 2 ? WHITE : PERI, r:(rh - 16) / 2});
      C.text(Z.x + 16, y + 8, vw - 32, rh - 16, vals[k], {font:'head', size:vs, color:INK, align:'c', va:'m', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      var lh2 = C.measureText(labs[k], Z.w - vw - 60, {font:'body', wt:700, max:lsz, min:lsz, lh:1.25}).h, xh = t.x ? xs * 1.35 + 4 : 0;
      var ty = y + (rh - lh2 - xh) / 2;
      C.text(Z.x + vw + 12, ty, Z.w - vw - 60, lh2 + 2, labs[k], {font:'body', size:lsz, wt:700, color:WHITE, lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
      if (t.x) C.text(Z.x + vw + 12, ty + lh2 + 4, Z.w - vw - 60, xh, t.x, {font:'body', size:xs, color:'#CFCFCF', lh:1.3, maxLines:1});
    });
    return;
  }
  var gx = 26, cw = (Z.w - gx * (n - 1) - 8) / n, ch = Z.h - 8, pad = 24, tw = cw - pad * 2;
  var vs2 = C.groupFit(vals, tw, 90, {font:'head', max:n <= 2 ? 64 : 46, min:22, maxLines:1});
  var ls2 = C.groupFit(labs, tw, 70, {font:'body', wt:700, max:n <= 2 ? 22 : 18, min:12, maxLines:3, lh:1.25});
  var xs2 = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs2 * 1.1 - 150, {font:'body', max:15, min:11, lh:1.35});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gx), y = Z.y + 14;
    slab(C, x, y, cw, ch - 14, CHAR, Math.min(cw / 2, 60), 7);
    cross(C, x + cw - 18, y + 6, 46, ROT[k] * 3, k % 2 ? WHITE : PERI);
    var yy = y + Math.max(40, (ch - 14) * 0.2);
    C.text(x + pad, yy, tw, vs2 * 1.1, vals[k], {font:'head', size:vs2, color:k % 2 ? WHITE : PERI, align:'c', lh:1, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    yy += vs2 * 1.1 + 16; C.rect(x + cw / 2 - 24, yy, 48, 4, {fill:PERI, r:2}); yy += 20;
    var lh3 = C.measureText(labs[k], tw, {font:'body', wt:700, max:ls2, min:ls2, lh:1.25}).h;
    C.text(x + pad, yy, tw, lh3 + 2, labs[k], {font:'body', size:ls2, wt:700, color:WHITE, align:'c', lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + pad, yy + lh3 + 10, tw, y + ch - 14 - pad - (yy + lh3 + 10), t.x, {font:'body', size:xs2, color:'#CFCFCF', align:'c', lh:1.35});
  });
}

/* 9. timeline: chunky charcoal track, ring stops, tilted year tags above, sticker notes below */
function rpTrack(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, ty = Z.y + 96, th = 26;
  C.rect(Z.x + 4 + 5, ty + 5, Z.w - 8, th, {fill:INK, r:th / 2});
  C.rect(Z.x + 4, ty, Z.w - 8, th, {fill:CHAR, r:th / 2, line:INK, lw:2});
  var ys = it.map(function(t){ return t.h || pad2(t.i + 1); }), yz = C.groupFit(ys, cw - 60, 40, {font:'head', max:24, min:13, maxLines:1});
  var tt = it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), ny = ty + th + 44, nh = Z.y + Z.h - 8 - ny;
  var F = F2(C, tt, cw - 58, 1, nh - 44, 16, 18, 1);
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    var tw = Math.min(cw - 20, lineW(C, ys[k], yz, 'head', 400, cw) + 34);
    tag(C, cx - tw / 2, Z.y + 8, tw, 50, ROT[k % 8], k % 2 ? WHITE : PERI, ys[k], INK, {font:'head', size:yz, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.line(cx, Z.y + 62, cx, ty - 4, {color:INK, lw:2.5});
    ringDot(C, cx, ty + th / 2, 17);
    var bh = dH(C, tt[k], cw - 58, F), h = Math.min(nh, Math.max(bh + 56, nh * 0.7));
    slab(C, cx - cw / 2 + 8, ny, cw - 22, h, WHITE, 18, 5);
    C.rect(cx - 16, ny - 26, 3, 26, {fill:INK});
    K.hd(C, cx - cw / 2 + 26, ny + (h - bh) / 2, cw - 58, h - 20, tt[k], F, {tc:INK, mc:CHAR, align:'c'});
  });
}

/* 10. agenda: white sticker tiles, periwinkle number stickers */
function rpAgendaTiles(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = n <= 4 ? n : Math.ceil(n / 2), rows = Math.ceil(n / cols), gx = 20, gy = 24, cw = (Z.w - gx * (cols - 1) - 8) / cols, ch = Math.min(200, (Z.h - gy * (rows - 1) - 8) / rows);
  var d = Math.min(56, ch * 0.4), pad = 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - pad * 2, ch - d - pad * 2 - 12, {font:'head', max:n <= 4 ? 28 : 23, min:13, maxLines:2});
  var y0 = Z.y + Math.max(0, (Z.h - 8 - ch * rows - gy * (rows - 1)) / 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), sh = (cols - inRow) * (cw + gx) / 2;
    var x = Z.x + sh + c * (cw + gx), y = y0 + r * (ch + gy);
    slab(C, x, y, cw, ch, k % 2 ? WHITE : '#FBF8F0', 18, 6);
    C.ell(x + pad, y + pad, d, d, {fill:k % 2 ? CHAR : PERI, line:INK, lw:2.5});
    C.text(x + pad, y + pad, d, d, pad2(k + 1), {font:'head', size:Math.round(d * 0.36), color:k % 2 ? WHITE : INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + pad, y + pad + d + 10, cw - pad * 2, ch - d - pad * 2 - 10, t.h || t.d, {font:'head', size:hs, color:INK, lh:1.12, va:'m', ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
  });
}

/* 11. agenda: charcoal pill chips with starburst numbers */
function rpAgendaPills(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, gy = 12, cw = (Z.w - gx * (cols - 1) - 8) / cols, rh = Math.min(70, (Z.h - 8 - gy * (per - 1)) / per);
  var br = rh * 0.5, hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - br * 2 - 60, rh - 12, {font:'body', wt:700, max:20, min:12.5, maxLines:1});
  var y0 = Z.y + Math.max(0, (Z.h - 8 - rh * per - gy * (per - 1)) / 2);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    slab(C, x + br, y, cw - br, rh, CHAR, rh / 2, 5);
    burst(C, x + br, y + rh / 2, br + 2, k % 2 ? WHITE : PERI);
    C.text(x, y, br * 2, rh, pad2(k + 1), {font:'head', size:Math.round(br * 0.6), color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + br * 2 + 20, y, cw - br * 2 - 40, rh, t.h || t.d, {font:'body', size:hs, wt:700, color:WHITE, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
  });
}

/* 12. statement in a charcoal slab with sticker corners */
function rpStatement(C, Z, o){
  var t = C.it[0], txt = t ? t.d : (C.s.subtitle || ''), pad = Z.w > 800 ? 64 : 44;
  var h = Z.h - 16, y = Z.y + 8;
  slab(C, Z.x, y, Z.w - 8, h, CHAR, 30, 8);
  burst(C, Z.x + 14, y + 12, 38); cross(C, Z.x + Z.w - 34, y + h - 18, 52, 14, WHITE);
  var hy = y + pad - 8;
  if (t && t.h) { C.text(Z.x + pad, hy, Z.w - pad * 2, 40, t.h, {font:'head', fit:{max:30, min:16}, color:PERI, lh:1.1, maxLines:1, ed:{f:'x-part', i:t.i, p:0}}); hy += 54; }
  C.text(Z.x + pad, hy, Z.w - pad * 2 - 8, y + h - pad - hy, txt, {font:'body', fit:{max:30, min:16}, wt:600, color:WHITE, lh:1.42, va:'m', ed:t ? (t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}) : null});
}

/* 13. statement as a white note card with a tilted tag */
function rpNote(C, Z, o){
  var t = C.it[0], txt = t ? t.d : (C.s.subtitle || ''), pad = Z.w > 800 ? 60 : 40;
  var y = Z.y + 24, h = Z.h - 32;
  slab(C, Z.x, y, Z.w - 8, h, WHITE, 18, 8);
  var lab = t && t.h ? t.h : 'Note', tw = Math.min(Z.w * 0.6, lineW(C, lab, 17, 'body', 800, Z.w) + 36);
  tag(C, Z.x + pad - 10, y - 24, tw, 46, -4, PERI, lab, INK, {size:17, ed:t && t.h ? {f:'x-part', i:t.i, p:0} : null});
  ringDot(C, Z.x + Z.w - 44, y + 34, 20);
  C.text(Z.x + pad, y + 44, Z.w - pad * 2 - 8, h - 44 - pad * 0.7, txt, {font:'head', fit:{max:32, min:15}, color:INK, lh:1.3, va:'m', ed:t ? (t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}) : null});
}

/* ============================================================
   FULL SLIDES
   ============================================================ */
function subPill(C, x, y, maxW, text, center){
  var s = 18, m = C.measureText(text, maxW - 56, {font:'body', wt:700, max:s, min:13, maxLines:2, lh:1.3});
  s = m.s;
  var one = m.n <= 1, w = one ? Math.min(maxW, lineW(C, text, s, 'body', 700, maxW) + 56) : maxW, h = one ? 54 : Math.round(m.h + 30);
  var px = center ? x + (maxW - w) / 2 : x;
  C.rect(px + 6, y + 6, w, h, {fill:INK, r:Math.min(h / 2, 27)});
  C.rect(px, y, w, h, {fill:CHAR, r:Math.min(h / 2, 27)});
  C.text(px + 28, y, w - 56, h, text, {font:'body', size:s, wt:700, color:WHITE, align:'c', va:'m', lh:1.3, ed:{f:'subtitle'}, maxLines:2});
  return y + h;
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {};
  strip(C);
  if (o.img) { C.rect(W - 492, 98, 440, H - 190, {fill:INK, r:26}); D.imgBox(C, W - 500, 90, 440, H - 190, {r:26}); C.rect(W - 500, 90, 440, H - 190, {line:INK, lw:3, r:26}); burst(C, W - 490, 104, 48); }
  var x = o.img ? 70 : 150, w = o.img ? 640 : W - 300;
  ringDot(C, x - 10, 130, 26); cross(C, o.img ? x + w - 50 : x + w - 10, o.img ? 480 : 330, 60, 10);
  var te = C.text(x, 100, w, 320, C.s.title, {font:'head', fit:{max:o.img ? 84 : 118, min:40}, color:INK, ed:{f:'title'}, maxLines:3, va:'b', lh:0.96, align:o.img ? 'l' : 'c'});
  var y = 440;
  if (C.s.subtitle) y = subPill(C, x, y, o.img ? Math.min(560, w) : 820, C.s.subtitle, !o.img) + 26;
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  ·  ');
  if (meta) {
    var mw = lineW(C, meta, 14, 'body', 700, w) + 40;
    tag(C, o.img ? x : W / 2 - mw / 2, Math.min(y, H - 110), mw, 38, -2, PERI, meta, INK, {size:14});
  }
}
function closing(C, v){
  var P = C.P, o = C.o || {};
  strip(C);
  if (o.img) { C.rect(78, 98, 440, H - 190, {fill:INK, r:26}); D.imgBox(C, 70, 90, 440, H - 190, {r:26}); C.rect(70, 90, 440, H - 190, {line:INK, lw:3, r:26}); burst(C, 494, 106, 48); }
  var x = o.img ? 580 : 150, w = o.img ? W - 640 : W - 300, al = o.img ? 'l' : 'c';
  if (!o.img) { burst(C, 170, 170, 46); cross(C, W - 180, 190, 60, -10); ringDot(C, W - 250, H - 140, 28); }
  C.text(x, 110, w, 220, C.s.title || 'Thank You', {font:'head', fit:{max:108, min:44}, color:INK, ed:{f:'title'}, maxLines:2, va:'b', align:al, lh:0.96});
  var y = 360;
  if (C.s.subtitle) y = subPill(C, x, y, o.img ? w : 820, C.s.subtitle, !o.img) + 36;
  var cs = D.contacts(C); if (!cs.length) return;
  var gap = 20, cw = o.img ? w : Math.min(w, 960), n = cs.length, pw = (cw - gap * (n - 1) - 6) / n, cx0 = x + (w - cw) / 2;
  if (o.img) { pw = cw - 6; }
  cs.forEach(function(c, k){
    var px = o.img ? x : cx0 + k * (pw + gap), py = o.img ? y + k * 64 : Math.max(y, 470);
    slab(C, px, py, pw, 52, WHITE, 26, 5);
    C.ell(px + 8, py + 8, 36, 36, {fill:k % 2 ? CHAR : PERI});
    C.icon(px + 16, py + 16, 20, c.icon, k % 2 ? WHITE : INK);
    C.text(px + 54, py, pw - 70, 52, c.val, {font:'body', fit:{max:16, min:11}, wt:700, color:INK, va:'m', maxLines:1, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null});
  });
}
function quote(C, v){
  var o = C.o || {};
  var a = C.it[0], by = a ? (a.h ? a.h + ': ' + a.d : a.d) : (C.s.subtitle || '');
  if (o.img) {
    var iw = 400, ix = o.img === 'right' ? W - iw - 70 : 64, sx = o.img === 'right' ? 64 : iw + 120, sw = W - iw - 190;
    C.rect(ix + 8, 98, iw, H - 190, {fill:INK, r:26}); D.imgBox(C, ix, 90, iw, H - 190, {r:26}); C.rect(ix, 90, iw, H - 190, {line:INK, lw:3, r:26});
    cross(C, o.img === 'right' ? ix + iw - 10 : ix + 16, 104, 56, 12, WHITE);
    slab(C, sx, 110, sw, H - 230, CHAR, 30, 9);
    burst(C, sx + 40, 128, 50);
    C.text(sx, 90, 80, 76, '“', {font:'head', size:64, color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(sx + 50, 180, sw - 100, 230, C.s.title || '', {font:'head', fit:{max:32, min:17}, color:WHITE, align:'l', va:'m', lh:1.28, ed:{f:'title'}, maxLines:6});
    if (by) { var bw2 = Math.min(sw - 100, lineW(C, by, 14, 'body', 800, sw - 100, true, 0.06) + 44); tag(C, sx + 50, 440, bw2, 42, -3, PERI, by, INK, {size:14, caps:true, ls:0.06, ed:a ? {f:'content-plain', i:a.i} : null}); }
    return;
  }
  slab(C, 150, 110, W - 300, H - 230, CHAR, 34, 10);
  burst(C, 190, 130, 56);
  C.text(150, 94, 80, 80, '“', {font:'head', size:70, color:INK, align:'c', va:'m', lh:1, caps:false, ls:0});
  cross(C, W - 170, H - 130, 64, 12, WHITE);
  C.text(250, 170, W - 500, 260, C.s.title || '', {font:'head', fit:{max:36, min:18}, color:WHITE, align:'c', va:'m', lh:1.28, ed:{f:'title'}, maxLines:5});
  if (by) {
    var bw = Math.min(W - 520, lineW(C, by, 15, 'body', 800, W - 520, true, 0.06) + 44);
    tag(C, W / 2 - bw / 2, 470, bw, 44, -3, PERI, by, INK, {size:15, caps:true, ls:0.06, ed:a ? {f:'content-plain', i:a.i} : null});
  }
}

D.register({
  id:'retropop', name:'Retro Grid Pop', tag:'Playful · social media', desc:'Cream grid paper, chunky indigo headlines, charcoal pills with ink offset shadows, tilted sticker tags, starburst badges and torn ticket stubs — for social, marketing and creator decks.',
  fonts:{head:'Rammetto One', body:'Poppins', headW:400, bodyW:500, headLh:0.98, headLs:-0.01, gf:'Rammetto One|Poppins:wght@400;500;600;700;800'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Giant title + stickers',{}), V('v2','Title + framed photo',{img:true})],
    closing:[V('v1','Stickers + contact pills',{}), V('v2','Framed photo left',{img:true})],
    agenda:[V('v1','Sticker tiles',{body:rpAgendaTiles}), V('n2','Starburst pills',{body:rpAgendaPills}), V('v2','Pills + image right',{body:rpAgendaPills, img:'right', cols:1})],
    text:[V('v1','Charcoal slab',{body:rpStatement}), V('n2','Tagged note',{body:rpNote}), V('v2','Note + image right',{body:rpNote, img:'right'}), V('v3','Image left + slab',{body:rpStatement, img:'left'})],
    p2:[V('v1','Sticker panels',{body:rpDuo}), V('n2','Ticket stubs',{body:rpTickets}), V('n3','Charcoal pills',{body:rpPills}), V('v2','Image left + pills',{body:rpPills, img:'left'}), V('v3','Tag cards + image right',{body:rpTagCards, img:'right'})],
    p3:[V('v1','Tilted tag cards',{body:rpTagCards}), V('n2','Starburst cards',{body:rpBurst}), V('n3','Ticket stubs',{body:rpTickets}), V('n4','Charcoal pills',{body:rpPills}), V('v2','Image left + pills',{body:rpPills, img:'left'}), V('v3','X list + image right',{body:rpXList, img:'right'})],
    p4:[V('v1','Starburst cards',{body:rpBurst}), V('n2','Tilted tag cards',{body:rpTagCards}), V('n3','Ticket stubs',{body:rpTickets}), V('n4','X-sticker list',{body:rpXList}), V('v2','Pills + image right',{body:rpPills, img:'right'})],
    p56:[V('v1','Ticket stubs',{body:rpTickets}), V('n2','Starburst cards',{body:rpBurst}), V('n3','X-sticker list',{body:rpXList}), V('n4','Charcoal pills',{body:rpPills}), V('v2','X list + image right',{body:rpXList, img:'right', cols:1})],
    p78:[V('v1','X-sticker list',{body:rpXList}), V('n2','Ticket stubs',{body:rpTickets}), V('n3','Charcoal pills',{body:rpPills}), V('v2','X list + image right',{body:rpXList, img:'right', cols:2})],
    stats:[V('v1','Starburst stickers',{body:rpStatStickers}), V('n2','Charcoal columns',{body:rpStatPills}), V('n3','Colour discs',{body:'xStatDiscs'}), V('v2','Image left + pill rows',{body:rpStatPills, img:'left', rows:true})],
    timeline:[V('v1','Sticker track',{body:rpTrack}), V('n2','Year tickets',{body:rpTickets, stubYear:true}), V('n3','Year tag cards',{body:rpTagCards, tagH:true}), V('v2','Year tickets + image right',{body:rpTickets, stubYear:true, img:'right', cols:1})],
    quote:[V('v1','Charcoal quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 26 — Brutal Orange : black and cream pages in turn, huge condensed caps, vivid orange side bands and blocks, checker marks, hard square edges */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2;
var INK = '#0E0E0E', ORG = '#FF4A12', CRM = '#F5EDDC', GREY = '#1F1F1F';
var PD = {bg:INK, text:CRM, title:CRM, muted:'#BDB4A4', accent:ORG, accent2:CRM, card:GREY, line:'#3A3A3A', onAccent:INK};
var PL = {bg:CRM, text:INK, title:INK, muted:'#4F4A42', accent:ORG, accent2:INK, card:INK, line:'#CFC5B0', onAccent:INK};
var S = {
  margin:84, series:[ORG, CRM, '#FF7A4D', '#B8B0A0', '#FF4A12', '#6B6B6B'],
  header:{kind:'left', tMax:62, tMin:30, sMax:17, sMin:13, top:58, gap:8, tH:120},
  card:{kind:'solid', r:0, fill:GREY, dark:true, tc:CRM, mc:'#CFC7B8'},
  icon:{kind:'square', r:0, bg:ORG, fg:INK}, num:{kind:'square', r:0, bg:ORG, fg:INK},
  conc:{kind:'band', fill:ORG, r:0, tc:INK, lc:INK, label:'Bottom line'},
  footer:{fn:function(C){ foot(C); }}, bodyGap:30, imgFramed:false, imgR:0
};

/* ---------- motif kit ---------- */
function isDk(C){ return C.P.bg === INK; }
/* checker mark: alternating squares */
function checker(C, x, y, cols, rows, s, c1, c2){
  for (var i = 0; i < cols; i++) for (var j = 0; j < rows; j++) {
    if ((i + j) % 2 === 0) C.rect(x + i * s, y + j * s, s, s, {fill:c1});
    else if (c2) C.rect(x + i * s, y + j * s, s, s, {fill:c2});
  }
}
/* slanted tape strip */
function tape(C, x, y, w, h, fill, sl){ sl = sl == null ? Math.min(16, h * 0.3) : sl; K.poly(C, [[x + sl, y], [x + w, y], [x + w - sl, y + h], [x, y + h]], {fill:fill}); }
/* width a single line needs */
function lineW(C, s, size, font, wt, maxW, caps, ls){
  for (var w = 30; w < maxW; w += 6) { var m = C.measureText(s, w, {font:font, wt:wt, caps:caps, ls:ls, max:size, min:Math.max(6, size * 0.7), maxLines:1}); if (m.n === 1 && m.s >= size) return w; }
  return maxW;
}
function F2(C, it, w, hb, db, hMax, dMax, hl){ return K.fitHD(C, it, w, hb, db, {hMax:hMax, dMax:dMax, hMin:13, dMin:11, hLines:hl || 2}); }
function dH(C, t, w, F){ return t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + dH(C, t, w, F); }
/* heading + description (heading edit part can be overridden with t.hp) */
function hdx(C, x, y, w, h, t, F, tc, mc, al){
  al = al || 'l'; var hh = t.h ? F.hh : 0;
  if (t.h) C.text(x, y, w, hh + 2, t.h, {font:'head', size:F.hs, color:tc, align:al, lh:1.15, ed:{f:'x-part', i:t.i, p:t.hp != null ? t.hp : 0}, maxLines:2});
  if (t.d) { var dy = y + (hh ? hh + 6 : 0); C.text(x, dy, w, Math.max(14, h - (dy - y)), t.d, {font:'body', size:F.ds, color:mc, align:al, lh:1.36, ed:{f:'x-part', i:t.i, p:1}}); }
}
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }

/* ---------- page chrome ---------- */
function foot(C){
  var P = C.P, f = C.cover, im = (C.o || {}).img;
  if (f.company && im !== 'left') C.text(84, H - 40, 420, 20, f.company, {font:'body', size:12, wt:800, color:P.muted, caps:true, ls:0.16, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.rect(W - 84 - 34, H - 46, 34, 30, {fill:ORG});
  C.text(W - 84 - 34, H - 46, 34, 30, pad2(C.i + 1), {font:'body', size:12, wt:800, color:INK, align:'c', va:'m', lh:1});
}
function bg(C, kind){
  var dk = kind === 'cover' || kind === 'closing' ? true : (C.i || 0) % 2 === 0;
  C.P = dk ? PD : PL;
  C.S = D.merge(C.T.s, {header:{tc:C.P.title, sc:C.P.muted}});
  C.bg({fill:C.P.bg});
  if (kind === 'cover' || kind === 'closing') return;
  C.rect(0, 0, 24, H, {fill:ORG});
  checker(C, 24, H - 132, 2, 8, 12, dk ? CRM : INK);
  checker(C, W - 84 - 60, 24, 6, 2, 10, ORG);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing' || kind === 'quote') return;
  var im = (C.o || {}).img, iw = Math.round(W * (C.S.imgPct || 0.4));
  if (im === 'right') { C.rect(W - iw, 0, 18, H, {fill:ORG}); checker(C, W - iw + 18, 0, 1, 6, 18, INK); }
  if (im === 'left') { C.rect(iw - 18, 0, 18, H, {fill:ORG}); checker(C, iw - 36, H - 108, 1, 6, 18, INK); }
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* 1. index table: thick rules, huge orange numbers (or years), heading | description */
function brIndex(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 48, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(o.rMax || 150, Z.h / per);
  var y0 = Z.y + Math.max(0, (Z.h - rh * per) / 2);
  var labs = it.map(function(t, k){ return o.year ? (t.h || pad2(k + 1)) : pad2(k + 1); });
  var ns = C.groupFit(labs, o.year ? Math.min(220, cw * 0.3) : 160, rh - 18, {font:'head', max:Math.min(72, rh * 0.72), min:18, maxLines:1});
  var nw = 0; labs.forEach(function(l){ nw = Math.max(nw, lineW(C, l, ns, 'head', 400, cw * 0.4)); }); nw += 26;
  var tt = it.map(function(t){ return o.year ? {i:t.i, h:'', d:t.d} : t; });
  var three = cols === 1 && cw - nw >= 680 && !o.year;
  var hw = three ? Math.round((cw - nw) * 0.36) : 0, tx = nw, tw = cw - nw - hw;
  var F = three ? {hs:C.groupFit(it.map(function(t){ return t.h; }), hw - 30, rh - 24, {font:'head', max:n <= 3 ? 36 : 30, min:14, maxLines:2}), hh:0,
                   ds:C.groupFit(it.map(function(t){ return t.d; }), tw, rh - 24, {font:'body', max:n <= 3 ? 22 : 19, min:11, lh:1.36})}
                : F2(C, tt, tw, rh * 0.42, rh - 22, 26, o.year ? 20 : 18, 1);
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh;
    C.rect(x, y, cw, 3, {fill:P.text});
    if (r === per - 1 || k === n - 1) C.rect(x, y + rh - 3, cw, 3, {fill:P.text});
    C.text(x, y + 3, nw, rh - 6, labs[k], {font:'head', size:ns, color:ORG, va:'m', lh:1, caps:false, ls:0, maxLines:1, ed:o.year && t.h ? {f:'x-part', i:t.i, p:0} : null});
    if (three) {
      if (t.h) C.text(x + tx, y + 3, hw - 30, rh - 6, t.h, {font:'head', size:F.hs, color:P.text, va:'m', lh:1.08, maxLines:2, ed:{f:'x-part', i:t.i, p:0}});
      C.text(x + tx + hw, y + 3, tw, rh - 6, t.d, {font:'body', size:F.ds, color:P.muted, va:'m', lh:1.36, ed:{f:'x-part', i:t.i, p:1}});
    } else {
      var bh = blockH(C, tt[k], tw, F);
      hdx(C, x + tx, y + Math.max(6, (rh - bh) / 2), tw, rh - 10, tt[k], F, P.text, P.muted);
    }
  });
}

/* 2. hard-edged block grid: first block orange, huge number, checker corner, text bottom */
function brBlocks(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P, dk = isDk(C);
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : 4))), rows = Math.ceil(n / cols), g = 14;
  var cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = Math.max(16, Math.min(26, cw * 0.08));
  var labs = it.map(function(t, k){ return o.year ? (t.h || pad2(k + 1)) : pad2(k + 1); });
  var ns = C.groupFit(labs, cw - pad * 2 - 40, Math.min(ch * 0.34, 90), {font:'head', max:Math.min(ch * 0.34, 84), min:18, maxLines:1});
  var tt = it.map(function(t){
    if (o.year) return {i:t.i, h:'', d:t.d};
    if (o.agenda) return {i:t.i, h:t.h || t.d, d:t.h ? t.d : '', hp:t.h ? 0 : 1};
    return t;
  });
  var F = F2(C, tt, cw - pad * 2, 64, ch - pad * 2 - ns - 12, o.agenda ? 30 : 24, o.year ? 19 : 17, 2);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), x = Z.x + (cols - inRow) * (cw + g) / 2 + c * (cw + g), y = Z.y + r * (ch + g);
    var hot = k === 0, fill = hot ? ORG : (dk ? GREY : INK), tc = hot ? INK : CRM, mc = hot ? '#2A1208' : '#CFC7B8', nc = hot ? INK : ORG;
    C.rect(x, y, cw, ch, {fill:fill});
    checker(C, x + cw - pad - 30, y + pad, 3, 3, 10, hot ? INK : ORG);
    C.text(x + pad, y + pad - 2, cw - pad * 2 - 40, ns * 1.02, labs[k], {font:'head', size:ns, color:nc, lh:1, caps:false, ls:0, maxLines:1, ed:o.year && t.h ? {f:'x-part', i:t.i, p:0} : null});
    var bh = blockH(C, tt[k], cw - pad * 2, F), ty = Math.max(y + pad + ns + 10, y + ch - pad - bh);
    hdx(C, x + pad, ty, cw - pad * 2, y + ch - pad - ty, tt[k], F, tc, mc);
  });
}

/* 3. two big panels: orange slab vs heavy outline, giant numbers */
function brPanels(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (n < 2) return brIndex(C, Z, o); var P = C.P;
  var gap = 26, cw = (Z.w - gap) / 2, ch = Z.h, pad = cw > 420 ? 36 : 24, big = Math.min(150, ch * 0.34, cw * 0.42);
  var F = F2(C, it, cw - pad * 2, 96, ch - pad * 2 - big - 24, cw > 420 ? 34 : 22, 20, 2);
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), y = Z.y, tc = k ? P.text : INK, mc = k ? P.muted : '#2A1208';
    if (k) C.rect(x, y, cw, ch, {line:P.text, lw:5}); else C.rect(x, y, cw, ch, {fill:ORG});
    C.text(x + pad, y + pad - 6, cw - pad * 2, big * 1.02, pad2(k + 1), {font:'head', size:big, color:k ? ORG : INK, lh:1, caps:false, ls:0, maxLines:1});
    checker(C, x + cw - pad - 48, y + pad, 4, 2, 12, k ? ORG : INK);
    var bh = blockH(C, t, cw - pad * 2, F), ty = Math.max(y + pad + big + 16, y + ch - pad - bh);
    hdx(C, x + pad, ty, cw - pad * 2, y + ch - pad - ty, t, F, tc, mc);
  });
}

/* 4. tall columns split by thick rules, orange cap bar, giant number, checker foot */
function brColumns(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, pad = n > 3 ? 18 : 26, ns = Math.min(100, Z.h * 0.24, cw * 0.42);
  var iw = function(k){ return cw - (k ? pad : 0) - (k < n - 1 ? pad : 0); }, mw = iw(Math.min(1, n - 1));
  var ty0 = Z.y + 26 + ns + 20;
  var F = F2(C, it, Math.min(iw(0), mw), 80, Z.y + Z.h - 34 - ty0, 26, 18, 2);
  it.forEach(function(t, k){
    var ix = Z.x + k * cw + (k ? pad : 0), w = iw(k);
    if (k) C.rect(Z.x + k * cw - 2, Z.y, 4, Z.h, {fill:P.text});
    C.rect(ix, Z.y, w, 12, {fill:ORG});
    C.text(ix, Z.y + 26, w, ns * 1.02, pad2(k + 1), {font:'head', size:ns, color:k % 2 ? P.text : ORG, lh:1, caps:false, ls:0, maxLines:1});
    hdx(C, ix, ty0, Math.min(w, Math.min(iw(0), mw)), Z.y + Z.h - 34 - ty0, t, F, P.text, P.muted);
    checker(C, ix, Z.y + Z.h - 14, 6, 1, 14, ORG);
  });
}

/* 5. orange tape labels with the heading, description beside */
function brTape(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols, gy = per > 4 ? 10 : 16;
  var rh = Math.min(o.rMax || 118, (Z.h - gy * (per - 1)) / per), y0 = Z.y + Math.max(0, (Z.h - rh * per - gy * (per - 1)) / 2);
  var tw = Math.round(Math.min(cw * 0.42, 360)), slant = Math.min(18, rh * 0.3), nw = Math.min(46, rh * 0.8);
  var labs = it.map(function(t){ return t.h || t.d; });
  var hs = C.groupFit(labs, tw - slant * 2 - nw - 10, rh - 14, {font:'head', max:28, min:13, maxLines:2, lh:1.05});
  var dw = cw - tw - 22;
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), dw, rh - 6, {font:'body', max:n <= 3 ? 20 : 17, min:11, lh:1.34});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), hot = k % 2 === 0;
    tape(C, x, y, tw, rh, hot ? ORG : P.text, slant);
    C.text(x + slant, y, nw, rh, pad2(k + 1), {font:'body', size:Math.min(15, rh * 0.32), wt:800, color:hot ? INK : ORG, va:'m', lh:1, maxLines:1});
    C.text(x + slant + nw, y, tw - slant * 2 - nw - 4, rh, labs[k], {font:'head', size:hs, color:hot ? INK : P.bg, va:'m', lh:1.05, maxLines:2, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    if (t.h && t.d) C.text(x + tw + 22, y, dw, rh, t.d, {font:'body', size:ds, color:P.text, va:'m', lh:1.34, ed:{f:'x-part', i:t.i, p:1}});
    if (r < per - 1) C.rect(x + tw + 22, y + rh + gy / 2 - 1, dw, 2, {fill:P.text, op:0.18});
  });
}

/* 6. giant figures in columns with heavy under-bars */
function brBigStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return; var P = C.P;
  var vals = st.map(sv), labs = st.map(sl), cw = Z.w / n, pad = n > 3 ? 16 : 24, w = cw - pad * 2;
  var vs = C.groupFit(vals, w, Z.h * 0.42, {font:'head', max:n <= 2 ? 160 : 124, min:28, maxLines:1});
  var ls = C.groupFit(labs, w, 70, {font:'body', wt:700, max:n <= 2 ? 22 : 19, min:12, maxLines:3, lh:1.25});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), w, 60, {font:'body', max:15, min:11, lh:1.35});
  var lh = 0; labs.forEach(function(l){ lh = Math.max(lh, C.measureText(l, w, {font:'body', wt:700, max:ls, min:ls, lh:1.25}).h); });
  var xh = 0; st.forEach(function(t){ if (t.x) xh = Math.max(xh, C.measureText(t.x, w, {font:'body', max:xs, min:xs, lh:1.35}).h + 10); });
  var tot = 34 + vs * 1.02 + 18 + 10 + 18 + lh + xh, y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
  st.forEach(function(t, k){
    var x = Z.x + k * cw + pad, y = y0;
    if (k) C.rect(Z.x + k * cw - 1, Z.y + 10, 2, Z.h - 20, {fill:P.text, op:0.25});
    checker(C, x, y, 4, 2, 9, ORG);
    y += 34;
    C.text(x, y, w, vs * 1.02, vals[k], {font:'head', size:vs, color:k % 2 ? P.text : ORG, lh:1, caps:false, ls:0, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    y += vs * 1.02 + 18;
    C.rect(x, y, w, 10, {fill:k % 2 ? ORG : P.text});
    y += 28;
    C.text(x, y, w, lh + 2, labs[k], {font:'body', size:ls, wt:700, color:P.text, lh:1.25, caps:true, ls:0.04, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x, y + lh + 10, w, xh, t.x, {font:'body', size:xs, color:P.muted, lh:1.35});
  });
}

/* 7. stat blocks: orange lead block, dark blocks, figures top, labels bottom */
function brStatBlocks(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return; var P = C.P, dk = isDk(C);
  var cols = o.cols || (Z.w / n >= 230 ? n : 2), rows = Math.ceil(n / cols), g = 14;
  var cw = (Z.w - g * (cols - 1)) / cols, ch = (Z.h - g * (rows - 1)) / rows, pad = Math.min(26, cw * 0.1), w = cw - pad * 2;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, w - 10, ch * 0.42, {font:'head', max:rows > 1 ? 70 : 96, min:22, maxLines:1});
  var ls = C.groupFit(labs, w, ch * 0.3, {font:'body', wt:700, max:rows > 1 ? 17 : 19, min:11.5, maxLines:3, lh:1.25});
  var xs = C.groupFit(st.map(function(t){ return t.x || ''; }), w, ch * 0.2, {font:'body', max:14, min:11, lh:1.3, maxLines:2});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + g), y = Z.y + r * (ch + g), hot = k === 0;
    var fill = hot ? ORG : (dk ? GREY : INK), tc = hot ? INK : CRM, vc = hot ? INK : ORG, mc = hot ? '#2A1208' : '#CFC7B8';
    C.rect(x, y, cw, ch, {fill:fill});
    checker(C, x + cw - pad - 24, y + ch - pad - 24, 3, 3, 8, hot ? INK : ORG);
    C.text(x + pad, y + pad - 4, w, vs * 1.05, vals[k], {font:'head', size:vs, color:vc, lh:1, caps:false, ls:0, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(x + pad, y + pad + vs * 1.05 + 4, 44, 6, {fill:tc});
    var lh = C.measureText(labs[k], w - 34, {font:'body', wt:700, max:ls, min:ls, lh:1.25}).h, xh = t.x ? C.measureText(t.x, w - 34, {font:'body', max:xs, min:xs, lh:1.3}).h + 6 : 0;
    var ly = Math.max(y + pad + vs * 1.05 + 22, y + ch - pad - lh - xh);
    C.text(x + pad, ly, w - 34, lh + 2, labs[k], {font:'body', size:ls, wt:700, color:tc, lh:1.25, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + pad, ly + lh + 6, w - 34, xh, t.x, {font:'body', size:xs, color:mc, lh:1.3, maxLines:2});
  });
}

/* 8. ruler timeline: heavy bar with tick marks, huge years above, notes below */
function brRuler(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return; var P = C.P;
  var cw = Z.w / n, ys = it.map(function(t, k){ return t.h || pad2(k + 1); });
  var yz = C.groupFit(ys, cw - 20, Z.h * 0.3, {font:'head', max:96, min:18, maxLines:1});
  var by = Math.max(Z.y + yz * 1.02 + 34, Z.y + Z.h * 0.42), bh = 14, yy = by - 34 - yz * 1.02;
  C.rect(Z.x, by, Z.w, bh, {fill:P.text});
  for (var tx = Z.x + 8; tx < Z.x + Z.w - 4; tx += 16) C.rect(tx, by + bh, 2, (Math.round((tx - Z.x) / 16) % 4 === 0) ? 14 : 7, {fill:P.text});
  var tt = it.map(function(t){ return {i:t.i, h:'', d:t.d}; }), ny = by + bh + 40;
  var F = F2(C, tt, cw - 30, 1, Z.y + Z.h - ny, 16, 21, 1);
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    C.text(cx - cw / 2 + 10, yy, cw - 20, yz * 1.02, ys[k], {font:'head', size:yz, color:k % 2 ? P.text : ORG, align:'c', lh:1, caps:false, ls:0, maxLines:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.rect(cx - 2, yy + yz * 1.02 + 8, 4, by - (yy + yz * 1.02 + 8), {fill:ORG});
    C.rect(cx - 15, by - 8, 30, 30, {fill:ORG});
    C.rect(cx - 5, by + 2, 10, 10, {fill:INK});
    hdx(C, cx - cw / 2 + 15, ny, cw - 30, Z.y + Z.h - ny, tt[k], F, P.text, P.muted, 'c');
  });
}

/* 9. statement: huge condensed caps, orange rule or full orange block */
function brStatement(C, Z, o){
  var t = C.it[0], txt = t ? t.d : (C.s.subtitle || ''), P = C.P, blk = !!o.block;
  var pad = blk ? (Z.w > 800 ? 50 : 36) : 0, x = Z.x + (blk ? pad : 44), y = Z.y + (blk ? pad : 0), w = Z.w - (blk ? pad * 2 : 44), h = Z.h - (blk ? pad * 2 : 0);
  var tc = blk ? INK : P.text;
  if (blk) { C.rect(Z.x, Z.y, Z.w, Z.h, {fill:ORG}); checker(C, Z.x + Z.w - pad - 60, Z.y + pad - 14, 6, 2, 10, INK); }
  else C.rect(Z.x, Z.y, 14, Z.h, {fill:ORG});
  if (t && t.h) {
    var lw = Math.min(w * 0.6, lineW(C, t.h, 15, 'body', 800, w, true, 0.12) + 36);
    C.rect(x, y, lw, 36, {fill:blk ? INK : P.text});
    C.text(x, y, lw, 36, t.h, {font:'body', size:15, wt:800, color:blk ? ORG : P.bg, caps:true, ls:0.12, align:'c', va:'m', lh:1, maxLines:1, ed:{f:'x-part', i:t.i, p:0}});
    y += 56; h -= 56;
  }
  C.text(x, y, w - (blk ? 0 : 20), h, txt, {font:'head', fit:{max:58, min:20}, color:tc, lh:1.08, va:'m', ed:t ? (t.h ? {f:'x-part', i:t.i, p:1} : {f:'content-plain', i:t.i}) : null});
}

/* 10. agenda: orange / ink number squares, huge caps titles, heavy rules */
function brAgenda(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return; var P = C.P;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 44, cw = (Z.w - gx * (cols - 1)) / cols, rh = Math.min(88, Z.h / per);
  var sq = Math.min(rh - 14, 56), y0 = Z.y + Math.max(0, (Z.h - rh * per) / 2);
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - sq - 30, rh - 12, {font:'head', max:36, min:14, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * rh, hot = k % 2 === 0;
    C.rect(x, y + (rh - sq) / 2, sq, sq, {fill:hot ? ORG : P.text});
    C.text(x, y + (rh - sq) / 2, sq, sq, pad2(k + 1), {font:'head', size:Math.round(sq * 0.5), color:hot ? INK : P.bg, align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + sq + 22, y, cw - sq - 30, rh, t.h || t.d, {font:'head', size:hs, color:P.text, va:'m', lh:1, maxLines:1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}});
    C.rect(x + sq + 22, y + rh - 2, cw - sq - 22, 2, {fill:P.text, op:r < per - 1 ? 0.22 : 0});
  });
}

/* ============================================================
   FULL SLIDES
   ============================================================ */
function cover(C, v){
  var f = C.cover, o = C.o || {}, img = !!o.img;
  var x = 84, w = img ? W * 0.5 - 140 : W - 260;
  if (img) {
    D.imgBox(C, W * 0.5, 0, W * 0.5, H, {r:0});
    C.rect(W * 0.5 - 10, 0, 30, H, {fill:ORG});
    checker(C, W - 144, H - 144, 6, 6, 20, CRM, INK);
  } else {
    C.rect(0, 0, 28, H, {fill:ORG});
    checker(C, W - 250, 60, 10, 3, 16, ORG);
    C.rect(W - 110, 170, 26, H - 290, {fill:ORG});
  }
  if (f.company) C.text(x, 56, 480, 22, f.company, {font:'body', size:14, wt:800, color:CRM, caps:true, ls:0.18, lh:1, va:'m', ed:{f:'tf-company'}, maxLines:1});
  C.text(x, 110, w, 390, C.s.title, {font:'head', fit:{max:img ? 108 : 150, min:44}, color:CRM, ed:{f:'title'}, maxLines:img ? 4 : 3, va:'b', lh:0.94});
  C.rect(x, 522, 140, 12, {fill:ORG});
  if (C.s.subtitle) C.text(x, 552, img ? w : Math.min(w, 820), 64, C.s.subtitle, {font:'body', fit:{max:20, min:13}, wt:500, color:'#CFC7B8', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var meta = [f.presenter, f.designation, f.date].filter(Boolean).join('  /  ');
  if (meta) C.text(x, H - 58, w, 22, meta, {font:'body', size:13, wt:700, color:ORG, caps:true, ls:0.12, lh:1, va:'m', maxLines:1});
}
function closing(C, v){
  var o = C.o || {}, img = !!o.img;
  var x = img ? W * 0.5 + 70 : 84, w = img ? W * 0.5 - 150 : W - 260;
  if (img) {
    D.imgBox(C, 0, 0, W * 0.5, H, {r:0});
    C.rect(W * 0.5 - 20, 0, 30, H, {fill:ORG});
    checker(C, 24, 24, 6, 6, 20, CRM, INK);
  } else {
    C.rect(0, 0, 28, H, {fill:ORG});
    checker(C, W - 250, 60, 10, 3, 16, ORG);
  }
  C.text(x, 90, w, 250, C.s.title || 'Thank You', {font:'head', fit:{max:img ? 120 : 170, min:48}, color:CRM, ed:{f:'title'}, maxLines:2, va:'b', lh:0.92});
  C.rect(x, 360, 140, 12, {fill:ORG});
  if (C.s.subtitle) C.text(x, 390, img ? w : Math.min(w, 860), 64, C.s.subtitle, {font:'body', fit:{max:20, min:13}, wt:500, color:'#CFC7B8', ed:{f:'subtitle'}, lh:1.4, maxLines:2});
  var cs = D.contacts(C); if (!cs.length) return;
  var n = cs.length, gap = 16, cols = img ? 1 : n, cw = img ? w : (Math.min(w, 1060) - gap * (n - 1)) / n;
  cs.forEach(function(c, k){
    var cx = img ? x : x + k * (cw + gap), cy = img ? 490 + k * 62 : 510;
    C.rect(cx, cy, 50, 50, {fill:k % 2 ? CRM : ORG});
    C.icon(cx + 13, cy + 13, 24, c.icon, INK);
    C.rect(cx + 50, cy, cw - 50, 50, {line:'#3A3A3A', lw:2});
    C.text(cx + 66, cy, cw - 80, 50, c.val, {font:'body', fit:{max:17, min:11}, wt:700, color:CRM, va:'m', maxLines:1, ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null});
  });
}
function quote(C, v){
  var o = C.o || {}, P = C.P, a = C.it[0], by = a ? (a.h ? a.h + ': ' + a.d : a.d) : (C.s.subtitle || '');
  var x = 150, w = W - 300;
  if (o.img) {
    var iw = Math.round(W * 0.4), ix = o.img === 'right' ? W - iw : 0;
    D.imgBox(C, ix, 0, iw, H, {r:0});
    C.rect(o.img === 'right' ? ix : ix + iw - 18, 0, 18, H, {fill:ORG});
    x = o.img === 'right' ? 110 : iw + 80; w = W - iw - 190;
  }
  var qm = C.measureText(C.s.title || '', w, {font:'head', max:o.img ? 52 : 64, min:22, lh:1.04, h:290, maxLines:6});
  var y0 = Math.max(60, (H - 40 - (146 + Math.min(290, qm.h) + (by ? 86 : 0))) / 2);
  C.rect(x, y0, 110, 110, {fill:ORG});
  C.text(x, y0 + 6, 110, 130, '“', {font:'head', size:170, color:INK, align:'c', va:'t', lh:1, caps:false, ls:0});
  checker(C, x + 130, y0, 6, 2, 12, ORG);
  var q = C.text(x, y0 + 146, w, 290, C.s.title || '', {font:'head', fit:{max:o.img ? 52 : 64, min:22}, color:P.text, lh:1.04, va:'t', ed:{f:'title'}, maxLines:6});
  if (by) {
    var yy = y0 + 146 + q._h + 30;
    C.rect(x, yy, 60, 8, {fill:ORG});
    C.text(x, yy + 22, w, 26, by, {font:'body', fit:{max:16, min:11}, wt:800, color:P.text, caps:true, ls:0.12, lh:1, maxLines:1, ed:a ? {f:'content-plain', i:a.i} : null});
  }
}

D.register({
  id:'brutal', name:'Brutal Orange', tag:'Bold · agency', desc:'Black and cream pages in turn with huge condensed caps, vivid orange side bands and blocks, checker marks and hard square edges — for agencies, launches and bold pitches.',
  fonts:{head:'Anton', body:'Inter', headW:400, bodyW:400, headLh:1.0, headCaps:true, headLs:0.01, gf:'Anton|Inter:wght@400;500;600;700|Yellowtail'},
  pal:PD, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1','Giant caps',{}), V('v2','Photo + orange band',{img:true})],
    closing:[V('v1','Giant thanks + contact blocks',{}), V('v2','Photo left + contacts',{img:true})],
    agenda:[V('v1','Number squares',{body:brAgenda}), V('n2','Block grid',{body:brBlocks, agenda:true}), V('v2','Squares + image right',{body:brAgenda, img:'right', cols:1})],
    text:[V('v1','Huge statement',{body:brStatement}), V('n2','Orange block',{body:brStatement, block:true}), V('v2','Statement + image right',{body:brStatement, img:'right'}), V('v3','Image left + orange block',{body:brStatement, block:true, img:'left'})],
    p2:[V('v1','Orange vs outline',{body:brPanels}), V('n2','Index table',{body:brIndex}), V('n3','Tape labels',{body:brTape}), V('v2','Panels + image right',{body:brPanels, img:'right'}), V('v3','Image left + index',{body:brIndex, img:'left'})],
    p3:[V('v1','Ruled columns',{body:brColumns}), V('n2','Block grid',{body:brBlocks}), V('n3','Index table',{body:brIndex}), V('n4','Tape labels',{body:brTape}), V('v2','Image left + index',{body:brIndex, img:'left'}), V('v3','Tape + image right',{body:brTape, img:'right'})],
    p4:[V('v1','Block grid',{body:brBlocks}), V('n2','Ruled columns',{body:brColumns}), V('n3','Index table',{body:brIndex}), V('n4','Tape labels',{body:brTape}), V('v2','Tape + image right',{body:brTape, img:'right'})],
    p56:[V('v1','Tape labels',{body:brTape}), V('n2','Block grid',{body:brBlocks}), V('n3','Index table',{body:brIndex}), V('v2','Index + image right',{body:brIndex, img:'right', cols:1})],
    p78:[V('v1','Block grid',{body:brBlocks}), V('n2','Tape labels',{body:brTape}), V('n3','Index table',{body:brIndex}), V('v2','Tape + image right',{body:brTape, img:'right', cols:1})],
    stats:[V('v1','Giant figures',{body:brBigStats}), V('n2','Stat blocks',{body:brStatBlocks}), V('v2','Image left + blocks',{body:brStatBlocks, img:'left'}), V('v3','Figures + image right',{body:brBigStats, img:'right'})],
    timeline:[V('v1','Ruler',{body:brRuler}), V('n2','Year table',{body:brIndex, year:true}), V('n3','Year blocks',{body:brBlocks, year:true}), V('v2','Year table + image right',{body:brIndex, year:true, img:'right', cols:1})],
    quote:[V('v1','Orange mark quote',{}), V('v2','Quote + image right',{img:'right'}), V('v3','Image left + quote',{img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 27 — Emerald Corporate : white pages, emerald gradient diagonal shards, diamond photo frames with white borders, arrow-tag buttons */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2, mix = u.mix;
var E1 = '#0B8A5C', E2 = '#12B886', DEEP = '#075E3F', INK = '#0A3D2B', MINT = '#E9F7F0', MINT2 = '#D3EFE2';
var GR = {ang:135, stops:[[0, E1], [100, E2]]}, GRV = {ang:160, stops:[[0, E2], [100, E1]]}, GRD = {ang:135, stops:[[0, DEEP], [100, E1]]};
var P0 = {bg:'#FFFFFF', text:'#1F2F28', title:INK, muted:'#56695F', accent:E1, accent2:E2, card:'#F1FAF5', line:'#D6EBE1', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[E1, E2, DEEP, '#34C38F', '#0E7A52', '#5AD1A3'],
  header:{kind:'left', top:96, tMax:44, tMin:26, sMax:17, sMin:13, kicker:null},
  card:{kind:'solid', r:4, fill:'#FFFFFF', line:'#D6EBE1', lw:1.2},
  icon:{kind:'square', r:4, bg:E1, fg:'#FFFFFF'}, num:{kind:'square', r:4, bg:E1, fg:'#FFFFFF'},
  conc:{kind:'band', grad:GR, r:4, label:'Key point'},
  footer:{brand:false, page:true, color:'#7A9187'}, bodyGap:28
};

/* ---------- motif helpers ---------- */
/* arrow-tag button (home-plate) */
function tag(C, x, y, w, h, text, o){
  o = o || {};
  C.shape('homePlate', x, y, w, h, o.fill ? {fill:o.fill, line:o.line, lw:o.lw} : {grad:o.grad || GR});
  if (text != null && text !== '') C.text(x + (o.padL || 14), y, w - (o.padL || 14) - w * 0.18 - 4, h, text, {font:o.font || 'body', fit:{max:o.size || Math.round(h * 0.42), min:o.min || 11}, wt:o.wt || 600, color:o.color || '#FFFFFF', va:'m', lh:1.1, ed:o.ed || null, maxLines:o.lines || 1, caps:!!o.caps, ls:o.caps ? 0.08 : 0});
}
/* gradient diamond with an icon or number */
function gem(C, cx, cy, s, t, label){
  C.shape('diamond', cx - s / 2 - 5, cy - s / 2 - 5, s + 10, s + 10, {fill:'#FFFFFF', line:MINT2, lw:1.5});
  C.shape('diamond', cx - s / 2, cy - s / 2, s, s, {grad:GR});
  if (label != null) C.text(cx - s / 2, cy - s / 2, s, s, label, {font:'head', size:Math.round(s * 0.28), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
  else K.icon(C, cx - s * 0.2, cy - s * 0.2, s * 0.4, t, '#FFFFFF');
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function logo(C, x, y){
  var f = C.cover; if (!f.company) return;
  var w = Math.min(260, f.company.length * 8.6 + 58);
  tag(C, x, y, w, 30, f.company, {size:12.5, wt:700, caps:true, ed:{f:'tf-company'}});
}

/* ---------- page background: white + emerald shards ---------- */
function bg(C, kind){
  C.bg({fill:'#FFFFFF'});
  if (kind === 'cover' || kind === 'closing') return;
  var odd = (C.i || 0) % 2;
  C.shape('rtTriangle', W - 430, 0, 430, 118, {fill:MINT, rot:180});
  C.shape('rtTriangle', W - 250, 0, 250, 150, {grad:GR, rot:180});
  C.shape('rtTriangle', W - 104, 0, 104, 210, {grad:GRD, rot:180});
  if (odd) C.line(W - 560, 0, W - 250, 150, {color:E2, lw:1.2, op:0.6});
  C.shape('rtTriangle', 0, H - 34, 460, 34, {fill:MINT2});
  C.shape('rtTriangle', 0, H - 52, 240, 52, {grad:GR});
  logo(C, 70, 34);
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* columns: white cards, gradient shard in the corner with icon, big number */
function shardCols(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var gap = n > 3 ? 20 : 28, cw = (Z.w - gap * (n - 1)) / n, pad = n > 3 ? 22 : 30, iw = cw - pad * 2;
  var numH = n > 3 ? 40 : 50, top = pad + numH + 20;
  var F = K.fitHD(C, it, iw, 70, Z.h - top - pad - 8, {hMax:21, dMax:15.5, hMin:13, dMin:11});
  var bmax = 0; it.forEach(function(t){ bmax = Math.max(bmax, blockH(C, t, iw, F)); });
  var chh = Math.min(Z.h, Math.max(Z.h * 0.62, top + bmax + pad + 36)), off = Math.round((chh - top - pad - 8 - bmax) * 0.35), zy = Z.y + (Z.h - chh) / 2;
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), y = zy;
    C.rect(x, y, cw, chh, {fill:'#FFFFFF', line:'#D6EBE1', lw:1.2, r:4, shadow:0.05});
    var sw = Math.min(96, cw * 0.4);
    C.shape('rtTriangle', x + cw - sw, y, sw, sw * 0.72, {grad:GR, rot:180});
    K.icon(C, x + cw - 36, y + 9, 24, t, '#FFFFFF');
    C.text(x + pad, y + pad + off, iw * 0.6, numH, pad2(k + 1), {font:'head', size:numH, wt:700, color:E1, lh:1, caps:false, ls:0});
    C.rect(x + pad, y + pad + off + numH + 8, 34, 3, {fill:E2});
    K.hd(C, x + pad, y + top + off, iw, chh - top - off - pad - 8, t, F, {tc:INK});
    C.rect(x, y + chh - 5, cw, 5, {grad:{ang:90, stops:[[0, E1], [100, E2]]}});
  });
}

/* diamonds on one line, text centred below */
function diamondChain(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, s = Math.min(cw * 0.52, 118, Z.h * 0.3), cy = Z.y + s / 2 + 8;
  var F0 = K.fitHD(C, it, cw - 28, 60, Z.h - s - 60, {hMax:20, dMax:15, hMin:13, dMin:11}), bm = 0;
  it.forEach(function(t){ bm = Math.max(bm, blockH(C, t, cw - 28, F0)); });
  var used = s + 60 + bm; if (used < Z.h) { cy += (Z.h - used) * 0.4; Z = {x:Z.x, y:Z.y + (Z.h - used) * 0.4, w:Z.w, h:Z.h - (Z.h - used) * 0.4}; }
  C.line(Z.x, cy, Z.x + Z.w, cy, {color:E2, lw:2, op:0.55});
  C.rect(Z.x, cy - 1, cw * 0.5, 2, {fill:E1});
  var ty = cy + s / 2 + 26, nh = 22;
  var F = K.fitHD(C, it, cw - 28, 60, Z.y + Z.h - ty - nh - 6, {hMax:20, dMax:15, hMin:13, dMin:11});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    gem(C, cx, cy, s, t);
    C.text(cx - 40, ty, 80, nh, pad2(k + 1), {font:'head', size:15, wt:700, color:E1, align:'c', lh:1, caps:false, ls:0.1});
    K.hd(C, cx - cw / 2 + 14, ty + nh + 6, cw - 28, Z.y + Z.h - ty - nh - 6, t, F, {align:'c', tc:INK});
  });
}

/* grid of arrow tags carrying the headings, text under a green rule */
function tagGrid(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? 2 : (n <= 6 ? 3 : 4)), rows = Math.ceil(n / cols), gx = 26, gy = 24;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, tH = Math.min(50, Math.max(38, ch * 0.3));
  var nb = tH, twid = cw * 0.94 - nb - 14 - cw * 0.94 * 0.18;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), twid, tH - 6, {font:'head', max:cols >= 4 ? 17 : 20, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d || ''; }), cw - 22, ch - tH - 22, {font:'body', max:n <= 4 ? 20 : (n <= 6 ? 18 : 16), min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d || '', cw - 22, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = Math.min(ch, tH + 12 + dmx + 30); if (ch2 < ch) { var tot = ch2 * rows + gy * (rows - 1) + 20 * (rows - 1); Z = {x:Z.x, y:Z.y + (Z.h - tot) / 2, w:Z.w, h:tot}; gy += 20; ch = ch2; }
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, inRow = Math.min(cols, n - r * cols), shift = inRow < cols ? (cols - inRow) * (cw + gx) / 2 : 0;
    var x = Z.x + shift + c * (cw + gx), y = Z.y + r * (ch + gy), tw = cw * 0.94;
    C.shape('homePlate', x, y, tw, tH, {grad:k % 2 ? GRV : GR});
    C.rect(x, y, nb, tH, {fill:DEEP});
    C.text(x, y, nb, tH, pad2(k + 1), {font:'head', size:Math.round(tH * 0.38), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (t.h) C.text(x + nb + 14, y, twid, tH, t.h, {font:'head', size:hs, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:1});
    C.rect(x + 2, y + tH + 12, 2, ch - tH - 14, {fill:MINT2});
    C.text(x + 18, y + tH + 12, cw - 22, ch - tH - 12, t.d, {font:'body', size:ds, color:P0.muted, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* rows: arrow tag (number + heading), diamond bullet, text */
function tagRows(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 6 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - gx * (cols - 1)) / cols;
  var gy = per > 5 ? 10 : 16, rh = Math.min((Z.h - gy * (per - 1)) / per, 104), y0 = Z.y + Math.max(0, (Z.h - (rh * per + gy * (per - 1))) / 2);
  var hasH = it.some(function(t){ return t.h; }), tH = Math.min(rh - 6, 56), nb = Math.min(tH, 52);
  var compact = hasH && cw < 520;
  var tw = hasH && !compact ? Math.min(cw * 0.38, 290) : nb + 30, hw = tw - nb - 14 - tw * 0.18;
  if (compact) {
    var dx0 = tw + 34, F = K.fitHD(C, it, cw - dx0, rh * 0.5, rh - 2, {hMax:17, dMax:14, hMin:13, dMin:11, hLines:1, noBoost:per > 4});
    it.forEach(function(t, k){
      var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), my = y + rh / 2;
      C.shape('homePlate', x, my - tH / 2, tw, tH, {grad:GR});
      C.rect(x, my - tH / 2, nb, tH, {fill:DEEP});
      C.text(x, my - tH / 2, nb, tH, pad2(k + 1), {font:'head', size:Math.round(nb * 0.36), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
      var bh = blockH(C, t, cw - dx0, F);
      K.hd(C, x + dx0, y + Math.max(0, (rh - bh) / 2), cw - dx0, rh, t, F, {tc:INK});
      if (r < per - 1 && k < n - 1) C.rect(x + dx0, y + rh + gy / 2, cw - dx0, 1, {fill:P0.line});
    });
    return;
  }
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h || ''; }), hw, tH - 6, {font:'head', max:19, min:13, maxLines:2}) : 0;
  var dx = tw + 44, dw = cw - dx;
  var ds = C.groupFit(it.map(function(t){ return t.d || ''; }), dw, rh - 4, {font:hasH ? 'body' : 'head', max:hasH ? (per <= 4 ? 18 : 16) : 20, min:hasH ? 11 : 13, lh:1.35});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy), my = y + rh / 2;
    C.shape('homePlate', x, my - tH / 2, tw, tH, {grad:GR});
    C.rect(x, my - tH / 2, nb, tH, {fill:DEEP});
    C.text(x, my - tH / 2, nb, tH, pad2(k + 1), {font:'head', size:Math.round(nb * 0.36), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (hasH && t.h) C.text(x + nb + 12, my - tH / 2, hw, tH, t.h, {font:'head', size:hs, color:'#FFFFFF', va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.shape('diamond', x + tw + 14, my - 7, 14, 14, {fill:E2});
    C.text(x + dx, y, dw, rh, t.d, {font:hasH ? 'body' : 'head', size:ds, wt:hasH ? null : 500, color:hasH ? P0.muted : INK, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
    if (r < per - 1 && k < n - 1) C.rect(x + dx, y + rh + gy / 2, dw, 1, {fill:P0.line});
  });
}

/* two panels split by a diagonal: mint left, emerald gradient right */
function splitDiag(C, Z, o){
  var it = C.it.slice(0, 2), n = it.length; if (n < 2) return shardCols(C, Z, o);
  var mid = Z.x + Z.w / 2, sl = Math.min(64, Z.h * 0.14), pad = 44;
  C.rect(Z.x, Z.y, Z.w, Z.h, {grad:{ang:120, stops:[[0, E1], [100, E2]]}});
  K.poly(C, [[Z.x, Z.y], [mid + sl, Z.y], [mid - sl, Z.y + Z.h], [Z.x, Z.y + Z.h]], {fill:MINT});
  var lw = mid - sl - Z.x - pad * 2, rx = mid + sl + pad, rw = Z.x + Z.w - rx - pad;
  var w = Math.min(lw, rw), top = 40 + 34 + 22;
  var F = K.fitHD(C, it, w, 80, Z.h - top - pad, {hMax:24, dMax:17, hMin:14, dMin:11.5});
  [[Z.x + pad, 0], [rx, 1]].forEach(function(p){
    var t = it[p[1]], x = p[0], dark = p[1] === 1, bh = blockH(C, t, w, F), y = Z.y + Math.max(pad, (Z.h - bh - top + 40) / 2 - 10);
    C.text(x + w - 120, Z.y + 16, 120, 80, pad2(p[1] + 1), {font:'head', size:72, wt:700, color:dark ? '#FFFFFF' : E1, op:0.18, align:'r', lh:1, caps:false, ls:0});
    tag(C, x, y, 118, 34, 'Option ' + pad2(p[1] + 1), dark ? {fill:'#FFFFFF', color:E1, size:13, wt:700} : {size:13, wt:700});
    K.hd(C, x, y + 34 + 22, w, Z.y + Z.h - (y + 56) - 20, t, F, dark ? {tc:'#FFFFFF', mc:'#E6FFF3'} : {tc:INK});
  });
  var d = 74, cy = Z.y + Z.h / 2;
  C.shape('diamond', mid - d / 2, cy - d / 2, d, d, {fill:'#FFFFFF', line:E1, lw:2});
  C.icon(mid - 16, cy - 16, 32, 'mdi:arrow-right', E1);
}

/* diamond photo on the left, list on the right */
function diamondPhotoList(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var r = Math.min(Z.h / 2 - 6, 205, Z.w * 0.2), cx = Z.x + r + 16, cy = Z.y + Z.h / 2;
  K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
  K.poly(C, [[cx, cy - r - 16], [cx + r + 16, cy], [cx, cy + r + 16], [cx - r - 16, cy]], {stroke:E2, lw:1.5});
  C.shape('diamond', cx + r * 0.5, cy + r * 0.42, 58, 58, {grad:GR});
  var lx = cx + r + 64, lw = Z.x + Z.w - lx, gy = n > 4 ? 8 : 14, rh = (Z.h - gy * (n - 1)) / n;
  var F = K.fitHD(C, it, lw - 64, rh * 0.5, rh - 6, {hMax:20, dMax:15.5, hMin:13, dMin:11, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (rh + gy), bh = blockH(C, t, lw - 64, F);
    C.shape('diamond', lx, y + rh / 2 - 19, 38, 38, {grad:k % 2 ? GRV : GR});
    C.text(lx, y + rh / 2 - 19, 38, 38, pad2(k + 1), {font:'head', size:12, wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    K.hd(C, lx + 58, y + Math.max(0, (rh - bh) / 2), lw - 64, rh, t, F, {tc:INK});
    if (k < n - 1) C.rect(lx + 58, y + rh + gy / 2, lw - 58, 1, {fill:P0.line});
  });
}

/* stats: huge value, arrow-tag label, rising mint shard behind */
function statShards(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = (Z.w - 26 * (n - 1)) / n < 230 && n > 2 ? 2 : n, rows = Math.ceil(n / cols);
  if (rows > 1) { var zh = (Z.h - 24) / 2; [0, 1].forEach(function(r){ var sub = {x:Z.x, y:Z.y + r * (zh + 24), w:Z.w, h:zh}, keep = C.it; C.it = keep.slice(r * cols, r * cols + cols); statShards(C, sub, o); C.it = keep; }); return; }
  var gap = 26, cw = (Z.w - gap * (n - 1)) / n, iw = cw - 12;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, iw, Z.h * 0.3, {font:'head', max:n >= 4 ? 60 : 76, min:26, maxLines:1});
  var tH = Z.h < 300 ? 46 : 54, lsz = C.groupFit(labs, cw * 0.8 - 30, tH - 8, {font:'body', wt:600, max:16, min:12, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, Z.h * 0.3, {font:'body', max:15, min:11, lh:1.4});
  var dh = 0; st.forEach(function(t){ if (t.x) dh = Math.max(dh, C.measureText(t.x, iw, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var blk = vs * 1.1 + 16 + tH + (dh ? 14 + dh : 0), y0 = Z.y + Math.max(10, (Z.h - blk) / 2);
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gap);
    C.shape('rtTriangle', x, Z.y, cw * 0.8, Z.h, {fill:k % 2 ? MINT2 : MINT, op:0.9});
    C.text(x + 6, y0, iw, vs * 1.1, vals[k], {font:'head', size:vs, wt:700, color:k % 2 ? E1 : INK, lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.01, maxLines:1});
    tag(C, x + 6, y0 + vs * 1.1 + 16, cw * 0.86, tH, labs[k], {size:lsz, min:11, lines:2, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 6, y0 + vs * 1.1 + 16 + tH + 14, iw, dh + 4, t.x, {font:'body', size:ds, color:P0.muted, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* stats: values inside outlined diamonds */
function statDiamonds(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cw = Z.w / n, d = Math.min(cw - 18, Z.h * 0.62, 236);
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, d * 0.56, d * 0.26, {font:'head', max:48, min:18, maxLines:1});
  var ls = C.groupFit(labs, cw - 24, 50, {font:'head', max:19, min:13, maxLines:2});
  var lh = 0; labs.forEach(function(l){ lh = Math.max(lh, C.measureText(l, cw - 24, {font:'head', max:ls, min:ls, maxLines:2}).h); });
  var room = Z.h - d - 18 - lh - 10;
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 30, room, {font:'body', max:14.5, min:11, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = Z.y + d / 2;
    C.shape('diamond', cx - d / 2, cy - d / 2, d, d, {line:E1, lw:2.5});
    C.shape('diamond', cx - d * 0.42, cy - d * 0.42, d * 0.84, d * 0.84, {fill:k % 2 ? MINT2 : MINT});
    C.shape('diamond', cx - 11, Z.y - 11, 22, 22, {grad:GR});
    C.text(cx - d * 0.3, cy - d * 0.15, d * 0.6, d * 0.3, vals[k], {font:'head', size:vs, wt:700, color:INK, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:0, maxLines:1});
    C.text(cx - cw / 2 + 12, Z.y + d + 18, cw - 24, lh + 4, labs[k], {font:'head', size:ls, color:E1, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - cw / 2 + 15, Z.y + d + 18 + lh + 10, cw - 30, room, t.x, {font:'body', size:ds, color:P0.muted, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* timeline: rising diagonal shard band with diamond nodes, tags + text below */
function riseLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, yL = Z.y + Z.h * 0.4, yR = Z.y + Z.h * 0.06, step = n > 1 ? (yL - yR) / (n - 1) : 0;
  var ya = yL + step * 0.5, yb = yR - step * 0.5;
  K.poly(C, [[Z.x, ya], [Z.x + Z.w, yb], [Z.x + Z.w, Z.y + Z.h], [Z.x, Z.y + Z.h]], {fill:MINT, op:0.7});
  K.poly(C, [[Z.x, ya - 5], [Z.x + Z.w, yb - 5], [Z.x + Z.w, yb + 5], [Z.x, ya + 5]], {fill:E2});
  K.poly(C, [[Z.x, ya - 5], [Z.x + Z.w * 0.5, (ya + yb) / 2 - 5], [Z.x + Z.w * 0.5, (ya + yb) / 2 + 5], [Z.x, ya + 5]], {fill:E1});
  var off = Math.max(30, step / 2 + 16), tH = 34;
  var room = Z.y + Z.h - (yL + off + tH + 12);
  var hs = C.groupFit(it.map(function(t){ return t.h || pad2(1); }), cw * 0.86 - 40, tH - 6, {font:'head', max:18, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - 24, room, {font:'body', max:n <= 4 ? 17 : 15, min:11, lh:1.38});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), cy = yL - step * k, x = cx - cw / 2 + 12;
    C.shape('diamond', cx - 18, cy - 18, 36, 36, {fill:'#FFFFFF', line:E1, lw:2});
    C.shape('diamond', cx - 10, cy - 10, 20, 20, {grad:GRD});
    tag(C, x, cy + off, cw * 0.86, tH, t.h || pad2(k + 1), {font:'head', size:hs, min:12, wt:700, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x, cy + off + tH + 12, cw - 24, Z.y + Z.h - (cy + off + tH + 12), t.d, {font:'body', size:ds, color:P0.text, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* statement: tall gradient shard + large text */
function statement(C, Z, o){
  var it = C.it[0], P = C.P; if (!it) return;
  var x = Z.x, w = Z.w, y = Z.y, h = Z.h;
  if (o.card) {
    C.rect(Z.x, Z.y, Z.w, Z.h, {fill:MINT, r:4});
    C.shape('rtTriangle', Z.x + Z.w - 200, Z.y, 200, 130, {grad:GR, rot:180});
    x += 48; w -= 260; y += 36; h -= 72;
  } else {
    C.shape('parallelogram', Z.x, Z.y, 72, Z.h, {grad:GRV});
    C.shape('parallelogram', Z.x + 50, Z.y + Z.h * 0.35, 40, Z.h * 0.65, {fill:MINT2});
    x += 128; w -= 128;
  }
  var ty = y;
  if (it.h) { tag(C, x, y, Math.min(w, Math.max(160, it.h.length * 10 + 70)), 38, it.h, {font:'head', size:17, wt:700, ed:{f:'x-part', i:it.i, p:0}}); ty += 58; }
  C.text(x, ty, w, y + h - ty, it.d, {font:'head', fit:{max:36, min:17}, wt:400, color:INK, lh:1.35, va:'m', ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function crystal(C){
  C.shape('rtTriangle', W - 600, 0, 600, H, {fill:MINT, rot:180});
  C.shape('rtTriangle', W - 330, 0, 330, 440, {grad:GR, rot:180});
  K.poly(C, [[W - 470, H], [W, H], [W, 300]], {fill:E2, op:0.9});
  K.poly(C, [[W - 220, H], [W, H], [W, 470]], {fill:DEEP});
  C.shape('parallelogram', W - 720, 520, 300, 96, {grad:GR, op:0.95});
  C.shape('parallelogram', W - 560, 140, 180, 56, {fill:MINT2});
  C.line(W - 780, 0, W - 330, 440, {color:E1, lw:1.5, op:0.5});
}
function slivers(C){
  C.shape('rtTriangle', 0, H - 40, 560, 40, {fill:MINT2});
  C.shape('rtTriangle', 0, H - 64, 300, 64, {grad:GR});
}
function cover(C, v){
  var P = C.P, f = C.cover, o = C.o || {}, img = o.img === 'diamond';
  if (img) {
    var cx = W - 330, cy = H / 2 + 10, r = 250;
    C.shape('rtTriangle', W - 470, 0, 470, 300, {fill:MINT, rot:180});
    K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
    K.poly(C, [[cx, cy - r - 22], [cx + r + 22, cy], [cx, cy + r + 22], [cx - r - 22, cy]], {stroke:E1, lw:2});
    C.shape('rtTriangle', W - 250, 0, 250, 190, {grad:GR, rot:180});
    C.shape('diamond', cx - r - 50, cy + 120, 70, 70, {grad:GR});
    C.shape('diamond', cx - r - 10, cy + 200, 36, 36, {fill:MINT2});
  } else crystal(C);
  slivers(C);
  logo(C, 70, 50);
  var tw = img ? 560 : 640;
  C.text(70, 150, tw, 270, C.s.title, {font:'head', fit:{max:68, min:32}, color:INK, ed:{f:'title'}, maxLines:4, va:'b', lh:1.05});
  C.rect(70, 440, 90, 5, {grad:{ang:90, stops:[[0, E1], [100, E2]]}});
  if (C.s.subtitle) C.text(70, 462, tw - 40, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
  if (who) tag(C, 70, 566, Math.min(tw - 20, who.length * 8.2 + 64), 40, who, {size:14, wt:600});
  if (f.date) C.text(70, 616, 400, 24, f.date, {font:'body', size:14, wt:600, color:E1, va:'m', lh:1});
}
function closing(C, v){
  var P = C.P, o = C.o || {}, img = o.img === 'diamond';
  if (img) {
    var cx = W - 330, cy = H / 2, r = 230;
    K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
    K.poly(C, [[cx, cy - r - 22], [cx + r + 22, cy], [cx, cy + r + 22], [cx - r - 22, cy]], {stroke:E1, lw:2});
    C.shape('rtTriangle', W - 250, 0, 250, 190, {grad:GR, rot:180});
    C.shape('diamond', cx + r - 20, cy + r - 90, 80, 80, {grad:GR});
  } else crystal(C);
  slivers(C);
  logo(C, 70, 50);
  var tw = img ? 560 : 640;
  C.text(70, 150, tw, 200, C.s.title || 'Thank You', {font:'head', fit:{max:80, min:36}, color:INK, ed:{f:'title'}, maxLines:2, va:'b', lh:1.02});
  if (C.s.subtitle) C.text(70, 372, tw - 60, 76, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){
    var y = 476 + k * 50;
    C.shape('homePlate', 70, y, 50, 38, {grad:GR});
    C.icon(80, y + 8, 22, c.icon, '#FFFFFF');
    C.text(136, y, tw - 80, 38, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:INK, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaList(C, x, y, w, h){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var rh = Math.min(62, h / n), y0 = y + (h - rh * n) / 2, s = Math.min(40, rh - 10);
  var fs = C.groupFit(it.map(function(t){ return t.h || t.d; }), w - s - 40, rh - 8, {font:'head', max:22, min:13, maxLines:1});
  it.forEach(function(t, k){
    var yy = y0 + k * rh;
    C.shape('diamond', x, yy + (rh - s) / 2, s, s, {grad:k % 2 ? GRV : GR});
    C.text(x, yy + (rh - s) / 2, s, s, pad2(k + 1), {font:'head', size:Math.round(s * 0.32), wt:700, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    C.text(x + s + 24, yy, w - s - 24, rh, t.h || t.d, {font:'head', size:fs, wt:500, color:INK, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:1});
    if (k < n - 1) C.rect(x + s + 24, yy + rh - 0.5, w - s - 24, 1, {fill:P0.line});
  });
}
function agendaA(C, o){
  var P = C.P;
  C.shape('rtTriangle', 0, H - 300, 420, 300, {fill:MINT});
  C.shape('rtTriangle', 0, H - 190, 250, 190, {grad:GR});
  var te = C.text(70, 150, 420, 200, C.s.title || 'Agenda', {font:'head', fit:{max:64, min:32}, color:INK, ed:{f:'title'}, maxLines:3, va:'t', lh:1.05});
  C.rect(70, 150 + te._h + 20, 80, 5, {grad:{ang:90, stops:[[0, E1], [100, E2]]}});
  if (C.s.subtitle) C.text(70, 150 + te._h + 44, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  agendaList(C, 560, 110, W - 560 - 90, H - 200);
}
function agendaPhoto(C, o){
  var P = C.P;
  C.text(70, 96, 700, 60, C.s.title || 'Agenda', {font:'head', fit:{max:44, min:26}, color:INK, ed:{f:'title'}, maxLines:1});
  var cx = 290, cy = 420, r = 196;
  K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
  K.poly(C, [[cx, cy - r - 18], [cx + r + 18, cy], [cx, cy + r + 18], [cx - r - 18, cy]], {stroke:E2, lw:1.5});
  C.shape('diamond', cx + r * 0.5, cy + r * 0.45, 54, 54, {grad:GR});
  agendaList(C, 590, 180, W - 590 - 80, H - 250);
}
function statementPhoto(C, o){
  var P = C.P, it = C.it[0];
  var cx = W - 340, cy = H / 2 + 20, r = 240;
  K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
  K.poly(C, [[cx, cy - r - 20], [cx + r + 20, cy], [cx, cy + r + 20], [cx - r - 20, cy]], {stroke:E1, lw:2});
  var y = D.header(C, {x:70, w:560, top:96}, {});
  C.shape('parallelogram', 70, y + 40, 44, H - 120 - y - 40, {grad:GRV});
  if (it) C.text(146, y + 40, 470, H - 120 - y - 40, it.h ? it.h + ': ' + it.d : it.d, {font:'head', fit:{max:30, min:16}, wt:400, color:INK, lh:1.38, va:'m', ed:{f:'content-plain', i:it.i}});
}
function quote(C, v){
  var P = C.P, o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:E1}); return; }
  var photo = o.img === 'diamond', x = photo ? 70 : 330, w = photo ? 580 : W - 330 - 100;
  if (photo) {
    var cx = W - 320, cy = H / 2, r = 240;
    K.diamondImg(C, cx, cy, r, '#FFFFFF', {border:'#FFFFFF', bw:8});
    K.poly(C, [[cx, cy - r - 20], [cx + r + 20, cy], [cx, cy + r + 20], [cx - r - 20, cy]], {stroke:E1, lw:2});
    C.shape('diamond', 70, 110, 90, 90, {grad:GR});
    C.icon(95, 135, 40, 'mdi:format-quote-open', '#FFFFFF');
  } else {
    C.shape('diamond', 90, 200, 190, 190, {grad:GR});
    C.shape('diamond', 190, 330, 90, 90, {fill:MINT2});
    C.icon(140, 250, 90, 'mdi:format-quote-open', '#FFFFFF');
  }
  var qy = photo ? 240 : 170;
  var qe = C.text(x, qy, w, 290, C.s.title, {font:'head', fit:{max:40, min:20}, wt:400, color:INK, lh:1.3, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ': ' + by.d : by.d; tag(C, x, qy + qe._h + 34, Math.min(w, bt.length * 8.4 + 60), 40, bt, {size:14, wt:600, ed:{f:'content-plain', i:by.i}}); }
}

D.register({
  id:'emerald', name:'Emerald Corporate', tag:'Corporate · emerald',
  desc:'Crisp white pages cut by emerald gradient diagonal shards, diamond photo frames with white borders and arrow-tag buttons for corporate decks.',
  fonts:{head:'Lexend', body:'Lexend', headW:600, bodyW:300, headLh:1.1, gf:'Lexend:wght@300;400;500;600;700'},
  pal:P0, s:S, bg:bg, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Emerald crystal', {}), V('v2', 'Diamond photo', {img:'diamond'})],
    closing:[V('v1', 'Emerald crystal', {}), V('v2', 'Diamond photo + contacts', {img:'diamond'})],
    agenda:[V('v1', 'Title + diamond list', {fn:agendaA}), V('n2', 'Arrow-tag rows', {body:tagRows, cols:2}), V('v2', 'Diamond photo + list', {fn:agendaPhoto})],
    text:[V('v1', 'Shard statement', {body:statement}), V('n2', 'Statement card', {body:statement, card:true}), V('v2', 'Statement + diamond photo', {fn:statementPhoto}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Diagonal split', {body:splitDiag}), V('n2', 'Shard cards', {body:shardCols}), V('n3', 'Diamond chain', {body:diamondChain}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Tag rows + image right', {body:tagRows, img:'right'})],
    p3:[V('v1', 'Shard cards', {body:shardCols}), V('n2', 'Diamond chain', {body:diamondChain}), V('n3', 'Arrow-tag rows', {body:tagRows}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Image top + shard cards', {body:shardCols, img:'top'}), V('v4', 'Tag rows + image right', {body:tagRows, img:'right'})],
    p4:[V('v1', 'Diamond chain', {body:diamondChain}), V('n2', 'Shard cards', {body:shardCols}), V('n3', 'Arrow-tag grid', {body:tagGrid}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Image left + tag rows', {body:tagRows, img:'left'})],
    p56:[V('v1', 'Arrow-tag grid', {body:tagGrid}), V('n2', 'Arrow-tag rows', {body:tagRows}), V('n3', 'Diamond chain', {body:diamondChain}), V('v2', 'Diamond photo + list', {body:diamondPhotoList}), V('v3', 'Tag rows + image right', {body:tagRows, img:'right'})],
    p78:[V('v1', 'Arrow-tag grid', {body:tagGrid}), V('n2', 'Two-column tag rows', {body:tagRows, cols:2}), V('v2', 'Tag rows + image right', {body:tagRows, img:'right', cols:2})],
    stats:[V('v1', 'Shard numbers', {body:statShards}), V('n2', 'Diamond numbers', {body:statDiamonds}), V('v2', 'Shard numbers + image right', {body:statShards, img:'right'}), V('v3', 'Image left + shard numbers', {body:statShards, img:'left'})],
    timeline:[V('v1', 'Rising shard line', {body:riseLine}), V('n2', 'Arrow-tag rows', {body:tagRows}), V('n3', 'Diamond chain', {body:diamondChain}), V('v2', 'Tag rows + image right', {body:tagRows, img:'right'})],
    quote:[V('v1', 'Diamond quote', {}), V('v2', 'Quote + diamond photo', {img:'diamond'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 28 — Violet Pitch : indigo→violet gradient pages alternating with lavender pages, pill tag rows, stacked rounded photo cards, glass cards, huge light titles */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2, mix = u.mix;
var IND = '#1A1046', VIO = '#5B2BD6', VIO2 = '#8A5CFF', LAV = '#EDE8FC', LAV2 = '#DCD2F7', LILAC = '#B9A6F2';
var DARKBG = {ang:135, stops:[[0, '#110A33'], [55, '#26146E'], [100, '#4521B0']]};
var LIGHTBG = {ang:135, stops:[[0, '#F7F4FF'], [100, '#E2D9FA']]};
var VGR = {ang:135, stops:[[0, '#4A22C2'], [100, '#7B4DF5']]};
var P0 = {bg:'#F1ECFD', text:'#221A4D', title:'#1A1046', muted:'#5C5390', accent:VIO, accent2:VIO2, card:'#FFFFFF', line:'#D4C9F3', onAccent:'#FFFFFF'};
var PD = {bg:'#1E1158', text:'#ECE8FF', title:'#FFFFFF', muted:'#C4B8F2', accent:'#A98BFF', accent2:VIO2, card:'#2B1C74', line:'#4C3A9E', onAccent:'#FFFFFF'};
var S = {
  margin:70, series:[VIO, VIO2, '#3D1FA0', LILAC, '#6E44E8', '#9E84F0'],
  header:{kind:'left', top:92, tMax:54, tMin:30, sMax:17, sMin:13, kicker:null, gap:14},
  card:{kind:'solid', r:20, fill:'#FFFFFF', op:0.75, line:LAV2, lw:1.2},
  icon:{kind:'circle', bg:VIO, fg:'#FFFFFF'}, num:{kind:'pill', bg:VIO, fg:'#FFFFFF'},
  conc:{kind:'pill', fill:VIO, op:0.14, label:'Takeaway'},
  footer:{brand:false, page:true}, imgFramed:true, imgR:24, bodyGap:30,
  imgPh:{grad:{ang:135, stops:[[0, '#9C84F2'], [100, '#4A2BB0']]}}
};
function isDark(C, kind){ return kind === 'cover' || kind === 'closing' || (C.i || 0) % 2 === 0; }

/* ---------- motif helpers ---------- */
function pill(C, x, y, w, h, text, o){
  o = o || {}; var dk = C.dk;
  C.rect(x, y, w, h, {fill:o.fill || null, grad:o.grad || null, op:o.op, line:o.fill || o.grad ? null : (o.line || (dk ? '#FFFFFF' : VIO)), lop:dk ? 0.55 : 0.7, lw:1.2, r:h / 2});
  C.text(x + h * 0.4, y, w - h * 0.8, h, text, {font:o.font || 'body', fit:{max:o.size || Math.round(h * 0.44), min:o.min || 11}, wt:o.wt || 500, color:o.color || (o.fill || o.grad ? '#FFFFFF' : (dk ? '#FFFFFF' : VIO)), align:o.align || 'c', va:'m', lh:1.1, ed:o.ed || null, maxLines:1});
}
function pw(txt, size){ return String(txt || '').length * size * 0.56 + size * 2.4; }
/* glass (dark page) / lavender glass (light page) card; hl = violet gradient */
function gcard(C, x, y, w, h, hl, r){
  r = r == null ? 20 : r;
  if (hl) { C.rect(x, y, w, h, {grad:VGR, r:r, shadow:0.18}); return {tc:'#FFFFFF', mc:'#E4DCFF', pl:'#FFFFFF'}; }
  if (C.dk) { C.rect(x, y, w, h, {fill:'#FFFFFF', op:0.08, line:'#FFFFFF', lop:0.32, lw:1.2, r:r}); return {tc:'#FFFFFF', mc:'#CFC5F5', pl:'#FFFFFF'}; }
  C.rect(x, y, w, h, {fill:'#FFFFFF', op:0.78, line:LAV2, lw:1.2, r:r, shadow:0.06}); return {tc:IND, mc:'#5C5390', pl:VIO};
}
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  D.sparkle(C, x + 9, y + 11, 10, C.dk ? '#FFFFFF' : VIO);
  C.text(x + 26, y, 300, 22, f.company, {font:'body', size:14, wt:600, color:C.dk ? '#FFFFFF' : IND, va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
}
function pillRow(C, y, right){
  var f = C.cover, ps = [], x = right;
  if (f.date) ps.push(f.date);
  if (f.presenter) ps.push('Presented by: ' + f.presenter);
  ps.forEach(function(t, k){
    var w = Math.min(300, pw(t, 12)); x -= w;
    pill(C, x, y, w, 28, t, k === ps.length - 1 && !C.dk ? {fill:IND, size:12} : {size:12, fill:C.dk && k === ps.length - 1 ? '#FFFFFF' : null, color:C.dk && k === ps.length - 1 ? IND : null});
    x -= 10;
  });
}

/* ---------- page background ---------- */
function bg(C, kind){
  var dk = C.dk = isDark(C, kind);
  var p = {}, k; for (k in C.T.pal) p[k] = C.T.pal[k];
  if (dk) for (k in PD) p[k] = PD[k];
  C.P = p;
  C.S = D.merge(C.T.s, {header:{tc:p.title, sc:p.muted}, footer:{color:dk ? '#A99BE0' : '#8A80B8'}});
  C.bg({grad:dk ? DARKBG : LIGHTBG});
  if (dk) { D.glow(C, W * 0.85, H * 0.1, 420, 300, '#7B4DF5', 0.35); D.glow(C, W * 0.1, H * 1.05, 380, 220, '#3B1BA8', 0.5); }
  else { D.glow(C, W * 0.92, H * 0.95, 420, 280, '#C9B8FA', 0.6); }
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'left' && im !== 'top') brand(C, 70, 30);
  if (im !== 'right' && im !== 'top') pillRow(C, 27, W - 70);
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* two cards overlapping; the front one violet */
function overlapPair(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return cascade(C, Z, o);
  var cw = Z.w * 0.56, ov = cw * 2 - Z.w, dy = 44, ch = Z.h - dy, pad = 36;
  var twA = cw - ov - pad * 2, twB = cw - pad * 2, top = pad + 36 + 22;
  var F = K.fitHD(C, it, twA, 80, ch - top - pad, {hMax:26, dMax:17, hMin:14, dMin:11.5});
  var cards = [[Z.x, Z.y + dy, twA, 0], [Z.x + Z.w - cw, Z.y, twB, 1]];
  cards.forEach(function(c){
    var t = it[c[3]], x = c[0], y = c[1], hl = c[3] === 1, cc = gcard(C, x, y, cw, ch, hl, 26);
    var tx = hl ? x + ov + pad - 20 : x + pad, tw = hl ? cw - ov - pad * 2 + 20 : c[2];
    pill(C, tx, y + pad, 96, 34, pad2(c[3] + 1) + ' / ' + pad2(2), hl ? {fill:'#FFFFFF', color:VIO, size:13, wt:600} : {size:13});
    var bh = blockH(C, t, tw, F), by = y + top + Math.max(0, (ch - top - pad - bh) * 0.35);
    K.hd(C, tx, by, tw, y + ch - by - pad + 10, t, F, {tc:cc.tc, mc:cc.mc});
  });
}

/* cascading cards, each lower and overlapping the previous */
function cascade(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var ov = n > 3 ? 26 : 40, cw = (Z.w + ov * (n - 1)) / n, step = Math.min(34, Z.h * 0.08), ch = Z.h - step * (n - 1), pad = n > 3 ? 22 : 28;
  var tw = cw - ov - pad * 2, top = pad + 32 + 20;
  var F = K.fitHD(C, it, tw, 70, ch - top - pad, {hMax:21, dMax:15.5, hMin:13, dMin:11});
  var hlk = o.hl != null ? o.hl : n - 1;
  it.forEach(function(t, k){
    var x = Z.x + k * (cw - ov), y = Z.y + k * step, cc = gcard(C, x, y, cw, ch, k === hlk, 24);
    var tx = x + (k ? ov : 0) + pad, w = k ? tw : cw - ov - pad * 2;
    pill(C, tx, y + pad, 64, 30, pad2(k + 1), k === hlk ? {fill:'#FFFFFF', color:VIO, size:13, wt:600} : {size:13});
    K.icon(C, x + cw - pad - 28, y + pad + 1, 28, t, cc.pl);
    K.hd(C, tx, y + top, w, ch - top - pad, t, F, {tc:cc.tc, mc:cc.mc});
  });
}

/* glass grid, heading in an outlined pill, faint giant number */
function pillGlass(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 3 ? n : (n === 4 ? 2 : 3)), rows = Math.ceil(n / cols), gx = 22, gy = 20;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows, pad = rows > 1 ? 22 : 28, pH = rows > 1 ? 34 : 38;
  var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), cw - pad * 2 - pH, pH - 6, {font:'head', max:18, min:13, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - pH - 16, {font:'body', max:n <= 3 ? 19 : (n <= 4 ? 17 : 15), min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2 - 20, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = Math.min(ch, Math.max(pad * 2 + pH + 16 + dmx + 70, ch * 0.55));
  if (ch2 < ch) { var tot = ch2 * rows + gy * (rows - 1); Z = {x:Z.x, y:Z.y + (Z.h - tot) * 0.4, w:Z.w, h:tot}; ch = ch2; }
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy), cc = gcard(C, x, y, cw, ch, k === 0 && o.hlFirst, 20);
    C.text(x + cw - 150, y + ch - 96, 136, 90, pad2(k + 1), {font:'head', size:84, wt:300, color:cc.pl, op:0.14, align:'r', va:'b', lh:1, caps:false, ls:0});
    var hw = t.h ? Math.min(cw - pad * 2, C.measureText(t.h, 2000, {font:'head', max:hs, min:hs}).h ? pw(t.h, hs) + 6 : 0) : 0;
    if (t.h) { C.rect(x + pad, y + pad, hw, pH, {line:cc.pl, lop:0.7, lw:1.2, r:pH / 2}); C.text(x + pad + 14, y + pad, hw - 28, pH, t.h, {font:'head', size:hs, color:cc.tc, align:'c', va:'m', lh:1, ed:{f:'x-part', i:t.i, p:0}, maxLines:1}); }
    C.text(x + pad, y + pad + pH + 16, cw - pad * 2 - 20, ch - pad * 2 - pH - 16, t.d, {font:'body', size:ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* rounded pill rows: number disc, heading, description in one capsule */
function capsules(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 26, cw = (Z.w - gx * (cols - 1)) / cols;
  var gy = per > 4 ? 10 : 14, rh = Math.min((Z.h - gy * (per - 1)) / per, 92), y0 = Z.y + Math.max(0, (Z.h - rh * per - gy * (per - 1)) / 2);
  var hasH = it.some(function(t){ return t.h; }), d = rh - 16, tx = d + 26;
  var stack = !hasH || cw < 560, hw = stack ? cw - tx - 24 : Math.min(230, cw * 0.32), dw = stack ? cw - tx - 24 : cw - tx - hw - 40;
  var F = stack && hasH ? K.fitHD(C, it, dw, rh * 0.45, rh - 10, {hMax:17, dMax:14, hMin:13, dMin:11, hLines:1, noBoost:true}) : null;
  var hs = !stack ? C.groupFit(it.map(function(t){ return t.h; }), hw, rh - 12, {font:'head', max:19, min:13, maxLines:2}) : 0;
  var ds = !stack ? C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 10, {font:'body', max:15.5, min:11, lh:1.35}) : C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 10, {font:'head', max:20, min:13, maxLines:1});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + gy);
    var cc = gcard(C, x, y, cw, rh, false, rh / 2);
    C.ell(x + 8, y + 8, d, d, {grad:VGR});
    C.text(x + 8, y + 8, d, d, pad2(k + 1), {font:'head', size:Math.round(d * 0.34), wt:500, color:'#FFFFFF', align:'c', va:'m', lh:1, caps:false, ls:0});
    if (!hasH) C.text(x + tx, y, dw, rh, t.d, {font:'head', size:ds, color:cc.tc, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:1}, maxLines:1});
    else if (stack) { var bh = blockH(C, t, dw, F); K.hd(C, x + tx, y + Math.max(2, (rh - bh) / 2), dw, rh - 4, t, F, {tc:cc.tc, mc:cc.mc}); }
    else {
      C.text(x + tx, y, hw, rh, t.h, {font:'head', size:hs, color:cc.tc, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
      C.rect(x + tx + hw + 14, y + rh * 0.25, 1.2, rh * 0.5, {fill:cc.pl, op:0.4});
      C.text(x + tx + hw + 28, y, dw, rh, t.d, {font:'body', size:ds, color:cc.mc, va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
    }
  });
}

/* stacked rounded photo cards on the left, item cards overlapping the photo */
function photoStack(C, Z, o){
  var it = C.it.slice(0, 4), n = it.length; if (!n) return;
  var pwid = Math.min(Z.w * 0.44, 480), px = Z.x, py = Z.y + 24, ph = Z.h - 24;
  C.rect(px + 44, py - 24, pwid - 44, ph - 12, {fill:C.dk ? '#FFFFFF' : LILAC, op:C.dk ? 0.12 : 0.45, r:26});
  C.rect(px + 22, py - 12, pwid - 22, ph - 6, {fill:C.dk ? '#FFFFFF' : LILAC, op:C.dk ? 0.22 : 0.7, r:26});
  C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:28});
  D.imgBox(C, px, py, pwid, ph, {r:24});
  var ov = 70, lx = px + pwid - ov, lw = Z.x + Z.w - lx, gy = 14, ch = (Z.h - gy * (n - 1)) / n, pad = 22;
  var F = K.fitHD(C, it, lw - pad * 2 - 20, ch * 0.4, ch - pad * 2 + 8, {hMax:20, dMax:15, hMin:13, dMin:11, hLines:1});
  it.forEach(function(t, k){
    var y = Z.y + k * (ch + gy), x = lx + (k % 2 ? 26 : 0), w = lw - 26;
    if (k) C.rect(x, y, w, ch, {fill:C.dk ? '#24166A' : '#FFFFFF', r:20});
    var cc = gcard(C, x, y, w, ch, k === 0, 20);
    var bh = blockH(C, t, w - pad * 2 - 20, F);
    K.hd(C, x + pad, y + Math.max(8, (ch - bh) / 2), w - pad * 2 - 20, ch - 12, t, F, {tc:cc.tc, mc:cc.mc});
    C.text(x + w - 44, y + 12, 32, 20, pad2(k + 1), {font:'body', size:12, wt:600, color:cc.pl, align:'r', lh:1, op:0.8});
  });
}

/* stats: huge light numerals, thin dividers, label pills */
function bigNumbers(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n > 2 && (Z.w / n) < 230 ? 2 : n, rows = Math.ceil(n / cols), cw = Z.w / cols, rhh = Z.h / rows, iw = cw - 36;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, iw, rhh * 0.45, {font:'head', wt:300, max:rows > 1 ? 60 : (n >= 4 ? 76 : 96), min:28, maxLines:1});
  var ls = C.groupFit(labs, iw - 28, 20, {font:'body', max:14, min:11, maxLines:1});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), iw, rhh * 0.25, {font:'body', max:15, min:11, lh:1.38});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * cw, y = Z.y + r * rhh, blk = vs * 1.05 + 18 + 32 + (t.x ? 12 + ds * 2.8 : 0), y0 = y + Math.max(0, (rhh - blk) / 2);
    if (c > 0) C.rect(x, y + rhh * 0.12, 1.2, rhh * 0.76, {fill:C.dk ? '#FFFFFF' : VIO, op:0.3});
    C.text(x + 18, y0, iw, vs * 1.05, vals[k], {font:'head', size:vs, wt:300, color:C.P.title, lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.02, maxLines:1});
    var lw = Math.min(iw, pw(labs[k], ls) + 8);
    pill(C, x + 18, y0 + vs * 1.05 + 18, lw, 32, labs[k], {size:ls, grad:k === n - 1 ? VGR : null, ed:t.h ? {f:'x-part', i:t.i, p:1} : null});
    if (t.x) C.text(x + 18, y0 + vs * 1.05 + 62, iw, ds * 2.9, t.x, {font:'body', size:ds, color:C.P.muted, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* stats: rising violet columns with a value pill on top */
function riseCols(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var gap = 30, cw = (Z.w - gap * (n - 1)) / n, bw = Math.min(cw * 0.62, 200), labH = 70, base = Z.y + Z.h - labH;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, bw - 30, 60, {font:'head', max:40, min:12, maxLines:1});
  var ls = C.groupFit(labs, cw - 10, labH - 18, {font:'head', max:18, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), bw - 28, 90, {font:'body', max:13.5, min:11, lh:1.35});
  C.rect(Z.x, base, Z.w, 1.5, {fill:C.dk ? '#FFFFFF' : VIO, op:0.35});
  st.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cx = x + cw / 2, h = (base - Z.y - 10) * (0.45 + 0.55 * (k + 1) / n), y = base - h;
    C.rect(cx - bw / 2, y, bw, h, {grad:{ang:180, stops:[[0, k === n - 1 ? '#8A5CFF' : (C.dk ? '#6B45E0' : '#7A5AE8')], [100, k === n - 1 ? '#4A22C2' : (C.dk ? '#2D1880' : '#C9B8FA')]]}, r:22});
    C.rect(cx - bw / 2 + 10, y + 12, bw - 20, vs * 1.3 + 12, {fill:'#FFFFFF', r:(vs * 1.3 + 12) / 2});
    C.text(cx - bw / 2 + 12, y + 12, bw - 24, vs * 1.3 + 12, vals[k], {font:'head', size:vs, wt:500, color:VIO, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:0, maxLines:1});
    if (t.x && h > vs * 1.3 + 90) C.text(cx - bw / 2 + 14, y + vs * 1.3 + 36, bw - 28, h - vs * 1.3 - 46, t.x, {font:'body', size:ds, color:'#FFFFFF', align:'c', lh:1.35, ed:{f:'x-part', i:t.i, p:2}});
    C.text(x, base + 12, cw, labH - 12, labs[k], {font:'head', size:ls, wt:500, color:C.P.title, align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
  });
}

/* timeline: year pills on a line, glass cards hanging above / below */
function pillLine(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var cw = Z.w / n, ly = Z.y + Z.h / 2, pH = 36, cardH = Z.h / 2 - pH / 2 - 26;
  C.rect(Z.x, ly - 1, Z.w, 2, {fill:C.dk ? '#FFFFFF' : VIO, op:0.4});
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - 60, pH - 8, {font:'head', max:18, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw * 1.5 - 40, cardH - 32, {font:'body', max:18, min:11, lh:1.38});
  it.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5), up = k % 2 === 0, w = Math.min(cw * 1.6 - 20, 300), x = Math.max(Z.x, Math.min(Z.x + Z.w - w, cx - w / 2));
    var pwid = Math.min(cw - 16, 130);
    pill(C, cx - pwid / 2, ly - pH / 2, pwid, pH, t.h || pad2(k + 1), {font:'head', size:hs, wt:500, grad:k === n - 1 ? VGR : null, fill:k === n - 1 ? null : (C.dk ? '#1E1158' : '#FFFFFF'), color:k === n - 1 ? '#FFFFFF' : (C.dk ? '#FFFFFF' : VIO), ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    if (k !== n - 1) C.rect(cx - pwid / 2, ly - pH / 2, pwid, pH, {line:C.dk ? '#FFFFFF' : VIO, lop:0.6, lw:1.2, r:pH / 2});
    var cy = up ? Z.y : ly + pH / 2 + 26;
    C.line(cx, up ? cy + cardH : ly + pH / 2, cx, up ? ly - pH / 2 : cy, {color:C.dk ? '#FFFFFF' : VIO, lw:1.2, op:0.5, dash:true});
    var dh = C.measureText(t.d, w - 36, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(cardH, dh + 32), yy = up ? cy + cardH - hh : cy;
    var cc = gcard(C, x, yy, w, hh, false, 18);
    C.text(x + 18, yy + 16, w - 36, hh - 28, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}, va:'m'});
  });
}

/* timeline: staircase of stacked cards */
function stairStack(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  var stepX = Math.min(Z.w * 0.1, 110, Z.w * 0.4 / Math.max(1, n - 1)), w = Z.w - stepX * (n - 1), band = Z.h / (n - 1 + 1.5), pad = 20;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), 120, 26, {font:'head', max:16, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), w - 190 - pad * 2, band - 12, {font:'body', max:16, min:11, lh:1.3});
  it.forEach(function(t, k){
    var x = Z.x + k * stepX, y = Z.y + k * band, h = Z.y + Z.h - y, last = k === n - 1;
    var cc = gcard(C, x, y, w, h, last, 22);
    if (!last && !C.dk) C.rect(x, y, w, h, {fill:k % 2 ? LAV : '#FFFFFF', r:22, line:LAV2, lw:1.2});
    var bh = last ? Math.min(h, band * 1.5) : band, my = y + Math.min(bh, band) / 2;
    pill(C, x + pad, my - 16, 140, 32, t.h || pad2(k + 1), {font:'head', size:hs, wt:600, fill:last ? '#FFFFFF' : null, color:last ? VIO : null, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x + pad + 160, y + 4, w - 160 - pad * 2 - 40, Math.min(bh, band) - 8, t.d, {font:'body', size:ds, color:cc.mc, va:'m', lh:1.3, ed:{f:'x-part', i:t.i, p:1}});
    C.text(x + w - 60, y + 4, 44, Math.min(bh, band) - 8, pad2(k + 1), {font:'head', size:14, wt:500, color:cc.pl, op:0.6, align:'r', va:'m', lh:1, caps:false, ls:0});
  });
}

/* statement: pill label and huge light text, stacked card corner */
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var x = Z.x, w = Z.w * 0.8, y = Z.y;
  if (!o.img) {
    C.rect(Z.x + Z.w - 190, Z.y + Z.h - 150, 170, 140, {fill:C.dk ? '#FFFFFF' : LILAC, op:C.dk ? 0.1 : 0.4, r:22});
    C.rect(Z.x + Z.w - 160, Z.y + Z.h - 120, 170, 140, {grad:VGR, r:22});
    D.sparkle(C, Z.x + Z.w - 75, Z.y + Z.h - 50, 22, '#FFFFFF');
  }
  if (it.h) { pill(C, x, y, Math.min(w, pw(it.h, 16) + 20), 40, it.h, {size:16, grad:VGR, ed:{f:'x-part', i:it.i, p:0}}); y += 64; }
  C.text(x, y, o.img ? Z.w : w, Z.y + Z.h - y - (o.img ? 0 : 20), it.d, {font:'head', fit:{max:44, min:18}, wt:300, color:C.P.title, lh:1.25, va:'m', ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function coverPills(C, x, y){
  var f = C.cover, xx = x;
  [f.presenter ? 'Presented by: ' + f.presenter : '', f.date].filter(Boolean).forEach(function(t, k){
    var w = Math.min(340, pw(t, 14)); pill(C, xx, y, w, 36, t, k ? {size:14} : {size:14, fill:'#FFFFFF', color:IND}); xx += w + 12;
  });
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, img = o.img === 'stack';
  brand(C, 70, 40);
  if (img) {
    var x = 690, y = 110, w = 520, h = 500;
    C.rect(x + 60, y - 40, w - 60, h - 20, {fill:'#FFFFFF', op:0.12, r:30});
    C.rect(x + 30, y - 20, w - 30, h - 10, {fill:'#FFFFFF', op:0.22, r:30});
    C.rect(x - 6, y - 6, w + 12, h + 12, {fill:'#FFFFFF', r:32});
    D.imgBox(C, x, y, w, h, {r:28});
    pill(C, x - 40, y + h - 70, 220, 44, f.company || 'Pitch Deck', {size:15, grad:VGR});
    C.text(70, 150, 580, 300, C.s.title, {font:'head', fit:{max:76, min:34}, wt:300, color:'#FFFFFF', ed:{f:'title'}, maxLines:4, va:'b', lh:1.02});
    if (C.s.subtitle) C.text(70, 474, 560, 80, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:'#CFC5F5', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
    coverPills(C, 70, 590);
    return;
  }
  var sx = 820, sy = 360;
  [0, 1, 2].forEach(function(k){ C.rect(sx + k * 40, sy - k * 44, 320, 220, {fill:k === 2 ? null : '#FFFFFF', grad:k === 2 ? VGR : null, op:k === 2 ? 1 : 0.08 + k * 0.08, line:'#FFFFFF', lop:0.35, lw:1.2, r:30}); });
  D.sparkle(C, sx + 80 + 260, sy - 88 + 160, 34, '#FFFFFF');
  C.text(70, 110, 740, 330, C.s.title, {font:'head', fit:{max:112, min:44}, wt:300, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:0.98});
  if (C.s.subtitle) C.text(70, 470, 640, 80, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:'#CFC5F5', ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  coverPills(C, 70, 590);
}
function contactBar(C, x, y, w){
  var cs = D.contacts(C); if (!cs.length) return;
  C.rect(x, y, w, 64, {fill:'#FFFFFF', op:C.dk ? 0.1 : 0.8, line:C.dk ? '#FFFFFF' : LAV2, lop:0.4, lw:1.2, r:32});
  var cw = w / cs.length;
  cs.forEach(function(c, k){
    var xx = x + k * cw;
    if (k) C.rect(xx, y + 16, 1.2, 32, {fill:C.dk ? '#FFFFFF' : VIO, op:0.35});
    C.ell(xx + 22, y + 14, 36, 36, {grad:VGR}); C.icon(xx + 31, y + 23, 18, c.icon, '#FFFFFF');
    C.text(xx + 70, y, cw - 86, 64, c.val, {font:'body', fit:{max:16, min:11}, wt:500, color:C.dk ? '#FFFFFF' : IND, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function closing(C, v){
  var o = C.o || {};
  brand(C, 70, 40);
  if (o.img === 'stack') {
    var x = 70, y = 270, w = W - 140, h = 240;
    C.rect(x + 40, y - 30, w - 80, h, {fill:'#FFFFFF', op:0.14, r:28});
    C.rect(x - 5, y - 5, w + 10, h + 10, {fill:'#FFFFFF', r:30});
    D.imgBox(C, x, y, w, h, {r:26});
    C.text(70, 66, W - 140, 104, C.s.title || 'Thank You', {font:'head', fit:{max:76, min:36}, wt:300, color:'#FFFFFF', align:'c', ed:{f:'title'}, maxLines:1, va:'b'});
    if (C.s.subtitle) C.text(200, 182, W - 400, 36, C.s.subtitle, {font:'body', fit:{max:17, min:12}, color:'#CFC5F5', align:'c', ed:{f:'subtitle'}, maxLines:1});
    contactBar(C, 170, 560, W - 340);
    return;
  }
  C.text(70, 150, W - 140, 220, C.s.title || 'Thank You', {font:'head', fit:{max:120, min:44}, wt:300, color:'#FFFFFF', align:'c', ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(220, 392, W - 440, 70, C.s.subtitle, {font:'body', fit:{max:20, min:13}, color:'#CFC5F5', align:'c', ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  contactBar(C, 170, 520, W - 340);
}
function agendaA(C, o){
  var P = C.P;
  var te = C.text(70, 120, 440, 260, C.s.title || 'Agenda', {font:'head', fit:{max:92, min:40}, wt:300, color:P.title, ed:{f:'title'}, maxLines:3, va:'t', lh:1});
  if (C.s.subtitle) C.text(70, 120 + te._h + 24, 400, 90, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  C.rect(70, 470, 200, 150, {fill:LILAC, op:0.45, r:24}); C.rect(110, 500, 200, 150, {grad:VGR, r:24}); D.sparkle(C, 250, 560, 24, '#FFFFFF');
  var x = 540, y = 100, w = W - 540 - 70, h = H - 170;
  gcard(C, x, y, w, h, false, 28);
  var it = C.it.slice(0, 10), n = it.length, cols = n > 5 ? 2 : 1, per = Math.ceil(n / cols), cw = (w - 60 - 30 * (cols - 1)) / cols, rh = Math.min(76, (h - 60) / per);
  var fs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 70, rh - 10, {font:'head', max:21, min:13, maxLines:2});
  var y0 = y + 30 + (h - 60 - rh * per) / 2;
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, xx = x + 30 + c * (cw + 30), yy = y0 + r * rh;
    pill(C, xx, yy + rh / 2 - 15, 52, 30, pad2(k + 1), {size:13, grad:k === 0 ? VGR : null});
    C.text(xx + 66, yy, cw - 70, rh, t.h || t.d, {font:'head', size:fs, wt:500, color:P.title, va:'m', lh:1.1, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, maxLines:2});
    if (r < per - 1) C.rect(xx + 66, yy + rh, cw - 66, 1, {fill:P.line});
  });
}
function agendaPhoto(C, o){
  var P = C.P;
  C.text(70, 86, 560, 80, C.s.title || 'Agenda', {font:'head', fit:{max:54, min:30}, wt:300, color:P.title, ed:{f:'title'}, maxLines:1});
  var px = 70, py = 200, pwid = 400, ph = 440;
  C.rect(px + 40, py - 26, pwid - 40, ph - 10, {fill:LILAC, op:0.45, r:26});
  C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:28});
  D.imgBox(C, px, py, pwid, ph, {r:24});
  capsules(C, {x:520, y:180, w:W - 520 - 70, h:H - 250}, {cols:1});
}
function quote(C, v){
  var P = C.P, o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:C.dk ? '#A98BFF' : VIO, tc:P.title}); return; }
  var photo = o.img === 'stack', x = 70, w = photo ? 640 : W - 140;
  if (photo) {
    var px = 800, py = 130, pwid = 410, ph = 470;
    C.rect(px + 40, py - 30, pwid - 40, ph - 10, {fill:'#FFFFFF', op:C.dk ? 0.14 : 0.5, r:28});
    C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:30});
    D.imgBox(C, px, py, pwid, ph, {r:26});
  }
  C.text(x - 6, 70, 200, 170, '“', {font:'head', size:200, wt:300, color:C.dk ? '#A98BFF' : VIO, lh:1, caps:false, ls:0});
  var qe = C.text(x, 220, w, 280, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:300, color:P.title, lh:1.25, ed:{f:'title'}, maxLines:5, va:'t'});
  if (by) { var bt = by.h ? by.h + ': ' + by.d : by.d; pill(C, x, 220 + qe._h + 36, Math.min(w, pw(bt, 15)), 42, bt, {size:15, grad:VGR, ed:{f:'content-plain', i:by.i}}); }
}
function statementPhoto(C, o){
  var P = C.P;
  var y = D.header(C, {x:70, w:600, top:92}, {});
  var px = 740, py = 110, pwid = 470, ph = 500;
  C.rect(px + 44, py - 30, pwid - 44, ph - 10, {fill:'#FFFFFF', op:C.dk ? 0.14 : 0.5, r:28});
  C.rect(px - 5, py - 5, pwid + 10, ph + 10, {fill:'#FFFFFF', r:30});
  D.imgBox(C, px, py, pwid, ph, {r:26});
  statement(C, {x:70, y:y + 40, w:600, h:H - 110 - y - 40}, {img:true});
}

D.register({
  id:'violetpitch', name:'Violet Pitch', tag:'Startup · pitch deck',
  desc:'Indigo-to-violet gradient pages alternating with soft lavender pages, pill tag header rows, stacked rounded photo cards, glass cards and huge light titles for startup pitches.',
  fonts:{head:'Inter Tight', body:'Inter', headW:400, bodyW:400, headLh:1.08, gf:'Inter Tight:wght@300;400;500;600|Inter:wght@400;500;600'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Huge title + card stack', {}), V('v2', 'Stacked photo card', {img:'stack'})],
    closing:[V('v1', 'Thank you + contact pill', {}), V('v2', 'Photo card + contact pill', {img:'stack'})],
    agenda:[V('v1', 'Title + glass list', {fn:agendaA}), V('n2', 'Capsule rows', {body:capsules, cols:2}), V('v2', 'Photo stack + capsules', {fn:agendaPhoto})],
    text:[V('v1', 'Huge light statement', {body:statement}), V('v2', 'Statement + photo stack', {fn:statementPhoto}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Overlapping cards', {body:overlapPair}), V('n2', 'Cascade cards', {body:cascade}), V('n3', 'Pill glass cards', {body:pillGlass}), V('v2', 'Photo stack + cards', {body:photoStack}), V('v3', 'Capsules + image right', {body:capsules, img:'right'})],
    p3:[V('v1', 'Cascade cards', {body:cascade}), V('n2', 'Pill glass cards', {body:pillGlass}), V('n3', 'Capsule rows', {body:capsules}), V('v2', 'Photo stack + cards', {body:photoStack}), V('v3', 'Image top + pill cards', {body:pillGlass, img:'top'})],
    p4:[V('v1', 'Pill glass grid', {body:pillGlass}), V('n2', 'Cascade cards', {body:cascade}), V('n3', 'Capsule rows', {body:capsules}), V('v2', 'Photo stack + cards', {body:photoStack}), V('v3', 'Image left + capsules', {body:capsules, img:'left'})],
    p56:[V('v1', 'Capsule rows', {body:capsules}), V('n2', 'Pill glass grid', {body:pillGlass}), V('v2', 'Capsules + image right', {body:capsules, img:'right', cols:1})],
    p78:[V('v1', 'Two-column capsules', {body:capsules, cols:2}), V('n2', 'Staircase cards', {body:stairStack}), V('v2', 'Capsules + image right', {body:capsules, img:'right', cols:2})],
    stats:[V('v1', 'Huge light numbers', {body:bigNumbers}), V('n2', 'Rising columns', {body:riseCols}), V('v2', 'Numbers + image right', {body:bigNumbers, img:'right'}), V('v3', 'Image left + columns', {body:riseCols, img:'left'})],
    timeline:[V('v1', 'Pill timeline', {body:pillLine}), V('n2', 'Staircase cards', {body:stairStack}), V('v2', 'Staircase + image right', {body:stairStack, img:'right'})],
    quote:[V('v1', 'Huge light quote', {}), V('v2', 'Quote + photo stack', {img:'stack'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
try{

/* Template 29 — Neon Lime Glass : near-black green pages, lime glows and organic blobs, frosted glass cards, lime pill tags and dot labels */
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2) return;
var W = D.W, H = D.H, V = D.V, K = D.K2, u = D.u, pad2 = u.pad2, mix = u.mix;
var LIME = '#7CFF4F', LIME2 = '#3FD62B', PALE = '#C9FFB5', INK = '#07120A', MUTE = '#A3BDA8';
var BG = {ang:160, stops:[[0, '#0B1A0F'], [55, '#060E08'], [100, '#0E2615']]};
var LGR = {ang:135, stops:[[0, '#A6FF7F'], [100, '#4FE02E']]};
var P0 = {bg:'#08120A', text:'#E6F4E8', title:'#FFFFFF', muted:MUTE, accent:LIME, accent2:LIME2, card:'#132418', line:'#28402D', onAccent:INK};
var S = {
  margin:70, series:[LIME, '#B6FF9A', LIME2, '#E4FFD8', '#5EEA3A', '#9CFF7A'],
  header:{kind:'left', top:92, tMax:50, tMin:28, sMax:17, sMin:13, kicker:null},
  card:{kind:'glass', r:16, fill:'#FFFFFF', op:0.05, line:'#FFFFFF', lop:0.22, dark:true},
  icon:{kind:'soft', bg:LIME, fg:LIME}, num:{kind:'ring', bg:LIME, fg:LIME},
  conc:{kind:'pill', fill:LIME, op:0.1, label:'Takeaway', lc:LIME, tc:'#E6F4E8'},
  footer:{brand:false, page:true, color:'#6F8A74'}, glassFill:'#FFFFFF', glassOp:0.05, glassLine:'#FFFFFF',
  imgFramed:true, imgR:20, bodyGap:30,
  imgPh:{grad:{ang:135, stops:[[0, '#1E4A26'], [100, '#0B1A0F']]}}
};

/* ---------- motif helpers ---------- */
function glass(C, x, y, w, h, o){
  o = o || {};
  if (o.lime) { C.rect(x, y, w, h, {grad:LGR, r:o.r || 16}); return {tc:INK, mc:'#1E3A22', ac:INK}; }
  C.rect(x, y, w, h, {fill:'#FFFFFF', op:o.op != null ? o.op : 0.05, line:'#FFFFFF', lop:0.22, lw:1.2, r:o.r || 16});
  if (o.dot !== false) { D.glow(C, x + w - 24, y + 24, 20, 20, LIME, 0.45); C.ell(x + w - 29, y + 19, 10, 10, {fill:LIME}); }
  return {tc:'#FFFFFF', mc:'#B7CCBB', ac:LIME};
}
function dotLabel(C, x, y, w, text, o){
  o = o || {};
  C.ell(x, y + 5, 10, 10, {fill:o.color || LIME});
  C.text(x + 20, y, w - 20, 20, text, {font:'body', size:o.size || 13, wt:700, color:o.tc || '#FFFFFF', caps:true, ls:0.1, lh:1, va:'m', ed:o.ed || null, maxLines:1});
}
function pill(C, x, y, w, h, text, o){
  o = o || {};
  C.rect(x, y, w, h, {fill:o.fill || null, grad:o.grad || null, line:o.fill || o.grad ? null : (o.line || LIME), lw:1.4, r:h / 2});
  C.text(x + h * 0.4, y, w - h * 0.8, h, text, {font:o.font || 'body', fit:{max:o.size || Math.round(h * 0.44), min:o.min || 11}, wt:o.wt || 600, color:o.color || (o.fill || o.grad ? INK : '#FFFFFF'), align:'c', va:'m', lh:1.1, ed:o.ed || null, maxLines:1});
}
function pw(t, s){ return String(t || '').length * s * 0.58 + s * 2.4; }
function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
function sv(t){ return t.h || String(t.d).split(' ')[0]; }
function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
function brand(C, x, y){
  var f = C.cover; if (!f.company) return;
  K.blob(C, x + 10, y + 11, 11, LIME, 1, 3);
  C.text(x + 30, y, 320, 22, f.company, {font:'head', size:16, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:{f:'tf-company'}, maxLines:1});
}

/* ---------- page background ---------- */
function bg(C, kind){
  C.bg({grad:BG});
  var odd = (C.i || 0) % 2;
  D.glow(C, odd ? W * 0.05 : W * 0.97, H * 0.98, 380, 260, LIME, 0.2);
  D.glow(C, odd ? W * 0.98 : W * 0.02, H * 0.02, 360, 240, '#1F7A2E', 0.45);
}
function decor(C, kind){
  if (kind === 'cover' || kind === 'closing') return;
  var im = (C.o || {}).img;
  if (im !== 'left' && im !== 'top') brand(C, 70, 32);
  if (im !== 'right' && im !== 'top' && C.cover.date) C.text(W - 370, 32, 300, 22, C.cover.date, {font:'body', size:13, wt:600, color:MUTE, align:'r', va:'m', lh:1});
}

/* ============================================================
   SIGNATURE BODIES
   ============================================================ */
/* open dot-label columns + the last item in a tall glowing glass card */
function dotCols(C, Z, o){
  var it = C.it.slice(0, 5), n = it.length; if (!n) return;
  var open = it.slice(0, n - 1), last = it[n - 1], narrow = Z.w < 820;
  if (narrow) {
    var gh = Math.min(Z.h * 0.42, 220), rows = open.length, ry = Z.y + gh + 22, rh = rows ? (Z.y + Z.h - ry - 14 * (rows - 1)) / rows : 0;
    var cc = glass(C, Z.x, Z.y, Z.w, gh, {});
    var FL = K.fitHD(C, [last], Z.w - 60, 30, gh - 76, {hMax:16, dMax:16, hMin:13, dMin:11, noBoost:true});
    dotLabel(C, Z.x + 28, Z.y + 26, Z.w - 80, last.h || pad2(n), {ed:last.h ? {f:'x-part', i:last.i, p:0} : null});
    C.text(Z.x + 28, Z.y + 60, Z.w - 60, gh - 76, last.d, {font:'body', size:FL.ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:last.i, p:1}});
    var F2 = K.fitHD(C, open.map(function(t){ return {i:t.i, h:'', d:t.d}; }), Z.w - 20, 1, rh - 30, {dMax:15, dMin:11});
    open.forEach(function(t, k){
      var y = ry + k * (rh + 14);
      dotLabel(C, Z.x, y, Z.w, t.h || pad2(k + 1), {ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
      C.text(Z.x + 20, y + 28, Z.w - 20, rh - 28, t.d, {font:'body', size:F2.ds, color:MUTE, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    });
    return;
  }
  var gw = Math.min(Z.w * (n > 3 ? 0.3 : 0.36), 400), ow = Z.w - gw - 40, m = open.length, gx = 34, cw = m ? (ow - gx * (m - 1)) / m : 0;
  var F = K.fitHD(C, open.map(function(t){ return {i:t.i, h:'', d:t.d}; }), cw, 1, Z.h * 0.7 - 36, {dMax:19, dMin:11});
  var FL2 = K.fitHD(C, [{i:last.i, h:'', d:last.d}], gw - 56, 1, Z.h - 100, {dMax:19, dMin:11, noBoost:true});
  var odh = 0; open.forEach(function(t){ odh = Math.max(odh, C.measureText(t.d, cw, {font:'body', max:F.ds, min:F.ds, lh:1.42}).h); });
  var ldh = C.measureText(last.d, gw - 56, {font:'body', max:FL2.ds, min:FL2.ds, lh:1.42}).h, gh = Math.min(Z.h, Math.max(Z.h * 0.6, 68 + ldh + 60));
  var gy0 = Z.y + (Z.h - gh) * 0.4, top = Math.max(Z.y + 20, gy0 + gh - odh - 34 - 10);
  open.forEach(function(t, k){
    var x = Z.x + k * (cw + gx);
    C.rect(x, top - 20, cw, 1, {fill:'#FFFFFF', op:0.18});
    dotLabel(C, x, top, cw, t.h || pad2(k + 1), {ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x, top + 34, cw, Z.y + Z.h - top - 34, t.d, {font:'body', size:F.ds, color:MUTE, lh:1.42, ed:{f:'x-part', i:t.i, p:1}});
  });
  var gx0 = Z.x + Z.w - gw, cc = glass(C, gx0, gy0, gw, gh, {});
  D.glow(C, gx0 + gw * 0.2, gy0 + gh * 0.9, gw * 0.6, 90, LIME, 0.12);
  dotLabel(C, gx0 + 28, gy0 + 30, gw - 80, last.h || pad2(n), {ed:last.h ? {f:'x-part', i:last.i, p:0} : null});
  C.text(gx0 + 28, gy0 + 68, gw - 56, gh - 96, last.d, {font:'body', size:FL2.ds, color:cc.mc, lh:1.42, ed:{f:'x-part', i:last.i, p:1}});
}

/* problem / solution: glass card and solid lime card with an arrow disc */
function limeDuo(C, Z, o){
  var it = C.it.slice(0, 2); if (it.length < 2) return dotCols(C, Z, o);
  var gap = 70, cw = (Z.w - gap) / 2, pad = 38, top = pad + 40;
  var F = K.fitHD(C, it, cw - pad * 2, 90, Z.h - top - pad, {hMax:28, dMax:18, hMin:15, dMin:12});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cc = glass(C, x, Z.y, cw, Z.h, {lime:k === 1, dot:k === 0});
    C.text(x + pad, Z.y + pad - 6, 200, 30, pad2(k + 1) + ' —', {font:'head', size:20, wt:700, color:k ? INK : LIME, lh:1, caps:false, ls:0});
    var bh = blockH(C, t, cw - pad * 2, F);
    K.hd(C, x + pad, Z.y + top + Math.max(0, (Z.h - top - pad - bh) * 0.4), cw - pad * 2, Z.h - top - pad, t, F, {tc:cc.tc, mc:cc.mc});
  });
  var cx = Z.x + cw + gap / 2, cy = Z.y + Z.h / 2;
  D.glow(C, cx, cy, 70, 70, LIME, 0.35);
  C.ell(cx - 28, cy - 28, 56, 56, {fill:INK, line:LIME, lw:2});
  C.icon(cx - 14, cy - 14, 28, 'mdi:arrow-right', LIME);
}

/* pill-tag grid: heading in lime outline pill, text below, no cards */
function pillGrid(C, Z, o){
  var it = C.it.slice(0, 10), n = it.length; if (!n) return;
  var cols = o.cols || (n <= 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5))), rows = Math.ceil(n / cols), gx = 30, gy = 26;
  var cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var hasH = it.some(function(t){ return t.h; }), pH = hasH ? (cols <= 3 ? 42 : 36) : 48;
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), cw - 70, pH - 8, {font:'body', wt:600, max:hasH ? (cols <= 3 ? 18 : 16) : 19, min:11, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.h ? t.d : ''; }), cw - 4, ch - pH - 60, {font:'body', max:n <= 4 ? 21 : (n <= 6 ? 18 : 15.5), min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ if (t.h) dmx = Math.max(dmx, C.measureText(t.d, cw - 4, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch2 = hasH ? Math.min(ch, 30 + pH + 16 + dmx + 16) : Math.min(ch, 30 + pH + 20);
  var tot = ch2 * rows + gy * (rows - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.4);
  it.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = y0 + r * (ch2 + gy);
    C.text(x, y, 60, 22, pad2(k + 1), {font:'head', size:16, wt:700, color:LIME, lh:1, caps:false, ls:0});
    C.rect(x + 36, y + 10, cw - 36, 1, {fill:'#FFFFFF', op:0.18});
    var lab = t.h || t.d, w = Math.min(cw, pw(lab, hs) + 10);
    pill(C, x, y + 30, w, pH, lab, {size:hs, ed:{f:'x-part', i:t.i, p:t.h ? 0 : 1}, fill:k === 0 ? LIME : null});
    if (t.h) C.text(x, y + 30 + pH + 16, cw - 4, ch2 - pH - 46, t.d, {font:'body', size:ds, color:MUTE, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* one tall glass panel with numbered rows (index look) */
function glassIndex(C, Z, o){
  var it = C.it.slice(0, 8), n = it.length; if (!n) return;
  glass(C, Z.x, Z.y, Z.w, Z.h, {});
  var pad = 30, cols = o.cols || (n > 5 ? 2 : 1), per = Math.ceil(n / cols), gx = 40, cw = (Z.w - pad * 2 - gx * (cols - 1)) / cols, rh = (Z.h - pad * 2) / per;
  var hasH = it.some(function(t){ return t.h; }), hw = hasH ? Math.min(220, cw * 0.36) : 0, dw = cw - 60 - hw - (hasH ? 20 : 0);
  var hs = hasH ? C.groupFit(it.map(function(t){ return t.h; }), hw, rh - 8, {font:'head', max:19, min:13, maxLines:2}) : 0;
  var ds = C.groupFit(it.map(function(t){ return t.d; }), dw, rh - 8, {font:hasH ? 'body' : 'head', max:hasH ? 16 : 20, min:11, lh:1.35});
  it.forEach(function(t, k){
    var c = Math.floor(k / per), r = k % per, x = Z.x + pad + c * (cw + gx), y = Z.y + pad + r * rh;
    C.text(x, y, 50, rh, pad2(k + 1), {font:'head', size:18, wt:700, color:LIME, va:'m', lh:1, caps:false, ls:0});
    if (hasH) C.text(x + 60, y, hw, rh, t.h, {font:'head', size:hs, color:'#FFFFFF', va:'m', lh:1.15, ed:{f:'x-part', i:t.i, p:0}, maxLines:2});
    C.text(x + 60 + hw + (hasH ? 20 : 0), y, dw, rh, t.d, {font:hasH ? 'body' : 'head', size:ds, wt:hasH ? null : 500, color:hasH ? MUTE : '#FFFFFF', va:'m', lh:1.35, ed:{f:'x-part', i:t.i, p:1}});
    if (r < per - 1) C.rect(x, y + rh, cw, 1, {fill:'#FFFFFF', op:0.12});
  });
}

/* stats: glass cards with huge value left, label right, glowing dot */
function glassPct(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  var cols = n <= 2 ? 1 : 2, rows = Math.ceil(n / cols), gx = 22, gy = 20, cw = (Z.w - gx * (cols - 1)) / cols, ch = (Z.h - gy * (rows - 1)) / rows;
  var vals = st.map(sv), labs = st.map(sl), vw = cw * 0.46;
  var vs = C.groupFit(vals, vw - 30, ch * 0.6, {font:'head', max:72, min:24, maxLines:1});
  var lw = cw - vw - 50;
  var ls = C.groupFit(labs, lw, 48, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), lw, ch - 90, {font:'body', max:14.5, min:11, lh:1.38});
  st.forEach(function(t, k){
    var r = Math.floor(k / cols), c = k % cols, x = Z.x + c * (cw + gx), y = Z.y + r * (ch + gy), cc = glass(C, x, y, cw, ch, {});
    C.text(x + 28, y, vw - 30, ch, vals[k], {font:'head', size:vs, wt:700, color:'#FFFFFF', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.02, maxLines:1});
    C.rect(x + vw, y + ch * 0.22, 1, ch * 0.56, {fill:LIME, op:0.5});
    var lh = C.measureText(labs[k], lw, {font:'head', max:ls, min:ls, maxLines:2}).h, dh = t.x ? C.measureText(t.x, lw, {font:'body', max:ds, min:ds, lh:1.38}).h : 0, by = y + Math.max(14, (ch - lh - (dh ? dh + 8 : 0)) / 2);
    C.text(x + vw + 22, by, lw, lh + 4, labs[k], {font:'head', size:ls, color:LIME, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(x + vw + 22, by + lh + 8, lw, dh + 4, t.x, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* stats: values sitting on lime blobs */
function blobStats(C, Z, o){
  var st = C.it.slice(0, 4), n = st.length; if (!n) return;
  if (n > 2 && Z.w / n < 200) { var zh = (Z.h - 20) / 2, keep = C.it; [0, 1].forEach(function(q){ C.it = keep.slice(q * 2, q * 2 + 2); blobStats(C, {x:Z.x, y:Z.y + q * (zh + 20), w:Z.w, h:zh}, o); }); C.it = keep; return; }
  var cw = Z.w / n, r = Math.min(cw * 0.36, Z.h * 0.28, 110), cy = Z.y + r + 6;
  var vals = st.map(sv), labs = st.map(sl);
  var vs = C.groupFit(vals, r * 1.2, r * 0.6, {font:'head', max:46, min:13, maxLines:1});
  var ls = C.groupFit(labs, cw - 24, 50, {font:'head', max:19, min:13, maxLines:2});
  var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), cw - 30, Z.h - r * 2 - 90, {font:'body', max:15, min:11, lh:1.38});
  st.forEach(function(t, k){
    var cx = Z.x + cw * (k + 0.5);
    D.glow(C, cx, cy, r * 1.5, r * 1.3, LIME, 0.22);
    K.blob(C, cx, cy, r * 1.08, k % 2 ? '#B6FF9A' : LIME, 1, k + 2);
    C.ell(cx - r * 0.82, cy - r * 0.82, r * 1.64, r * 1.64, {fill:k % 2 ? '#B6FF9A' : LIME});
    C.text(cx - r * 0.66, cy - r * 0.4, r * 1.32, r * 0.8, vals[k], {font:'head', size:vs, wt:700, color:INK, align:'c', va:'m', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:-0.01, maxLines:1});
    var ly = cy + r + 30, lh = C.measureText(labs[k], cw - 24, {font:'head', max:ls, min:ls, maxLines:2}).h;
    C.text(cx - cw / 2 + 12, ly, cw - 24, lh + 4, labs[k], {font:'head', size:ls, color:'#FFFFFF', align:'c', lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
    if (t.x) C.text(cx - cw / 2 + 15, ly + lh + 10, cw - 30, Z.y + Z.h - ly - lh - 10, t.x, {font:'body', size:ds, color:MUTE, align:'c', lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
  });
}

/* timeline: glass cards on a neon rail, glowing node under each */
function rail(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var gap = 16, cw = (Z.w - gap * (n - 1)) / n, ry = Z.y + Z.h - 22, pad = 20;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), cw - pad * 2 - 20, 40, {font:'head', max:30, min:15, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, Z.h - 70 - hs * 1.2 - pad * 2, {font:'body', max:n <= 4 ? 17 : 15, min:11, lh:1.4});
  var dmx = 0; it.forEach(function(t){ dmx = Math.max(dmx, C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.4}).h); });
  var ch = Math.min(Z.h - 60, pad * 2 + hs * 1.2 + 14 + dmx + 10);
  ry = Math.min(ry, Z.y + (Z.h + ch + 38) / 2 + 10); var cy = ry - 38 - ch;
  C.rect(Z.x, ry - 1.5, Z.w, 3, {fill:LIME, op:0.35});
  C.rect(Z.x, ry - 1.5, Z.w * 0.6, 3, {grad:{ang:90, stops:[[0, LIME, 0], [100, LIME]]}});
  it.forEach(function(t, k){
    var x = Z.x + k * (cw + gap), cx = x + cw / 2, cc = glass(C, x, cy, cw, ch, {});
    C.text(x + pad, cy + pad, cw - pad * 2 - 20, hs * 1.2, t.h || pad2(k + 1), {font:'head', size:hs, wt:700, color:'#FFFFFF', lh:1, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, caps:false, ls:0, maxLines:1});
    C.text(x + pad, cy + pad + hs * 1.2 + 14, cw - pad * 2, ch - pad * 2 - hs * 1.2 - 14, t.d, {font:'body', size:ds, color:cc.mc, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    C.line(cx, cy + ch, cx, ry - 8, {color:LIME, lw:1.2, op:0.5, dash:true});
    D.glow(C, cx, ry, 22, 22, LIME, 0.5);
    C.ell(cx - 7, ry - 7, 14, 14, {fill:k === n - 1 ? LIME : INK, line:LIME, lw:2});
  });
}

/* timeline: two staggered rows of glass cards joined by a circuit line */
function circuit(C, Z, o){
  var it = C.it.slice(0, 6), n = it.length; if (!n) return;
  var slot = Z.w / (n + 1), cw = Math.min(slot * 2 - 20, 330), my = Z.y + Z.h / 2, ch = Z.h / 2 - 34, pad = 18;
  var hs = C.groupFit(it.map(function(t){ return t.h || '00'; }), 110, 28, {font:'head', max:17, min:12, maxLines:1});
  var ds = C.groupFit(it.map(function(t){ return t.d; }), cw - pad * 2, ch - pad * 2 - 44, {font:'body', max:16, min:11, lh:1.38});
  var pts = [];
  it.forEach(function(t, k){ pts.push([Z.x + slot * (k + 1), my]); });
  C.line(Z.x, my, Z.x + Z.w, my, {color:LIME, lw:2, op:0.45});
  it.forEach(function(t, k){
    var cx = pts[k][0], up = k % 2 === 0, x = Math.max(Z.x, Math.min(Z.x + Z.w - cw, cx - cw / 2));
    var dh = C.measureText(t.d, cw - pad * 2, {font:'body', max:ds, min:ds, lh:1.38}).h, hh = Math.min(ch, pad * 2 + 44 + dh), y = up ? my - 34 - hh : my + 34;
    var cc = glass(C, x, y, cw, hh, {dot:false});
    C.line(cx, up ? y + hh : my, cx, up ? my : y, {color:LIME, lw:2});
    D.glow(C, cx, my, 20, 20, LIME, 0.6);
    C.rect(cx - 7, my - 7, 14, 14, {fill:LIME, r:3});
    pill(C, x + pad, y + pad, Math.min(cw - pad * 2, pw(t.h || '00', hs) + 10), 30, t.h || pad2(k + 1), {font:'head', size:hs, fill:k === n - 1 ? LIME : null, ed:t.h ? {f:'x-part', i:t.i, p:0} : null});
    C.text(x + pad, y + pad + 44, cw - pad * 2, hh - pad * 2 - 44, t.d, {font:'body', size:ds, color:cc.mc, lh:1.38, ed:{f:'x-part', i:t.i, p:1}});
  });
}

/* statement: lime blob mark + big white text */
function statement(C, Z, o){
  var it = C.it[0]; if (!it) return;
  var x = Z.x, w = Z.w, y = Z.y;
  if (!o.img) { D.glow(C, Z.x + Z.w - 120, Z.y + Z.h - 100, 200, 160, LIME, 0.25); K.blob(C, Z.x + Z.w - 120, Z.y + Z.h - 100, 90, LIME, 1, 5); K.blob(C, Z.x + Z.w - 40, Z.y + Z.h - 190, 34, '#B6FF9A', 1, 2); w = Z.w - 300; }
  if (it.h) { pill(C, x, y, Math.min(w, pw(it.h, 15) + 10), 38, it.h, {size:15, fill:LIME, ed:{f:'x-part', i:it.i, p:0}}); y += 60; }
  C.text(x, y, w, Z.y + Z.h - y, it.d, {font:'head', fit:{max:44, min:18}, wt:600, color:'#FFFFFF', lh:1.25, va:'m', ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
}

/* ============================================================
   FULL-SLIDE COMPOSITIONS
   ============================================================ */
function blobCluster(C, cx, cy, s){
  D.glow(C, cx, cy, 300 * s, 260 * s, LIME, 0.28);
  K.blob(C, cx, cy, 150 * s, LIME2, 1, 1);
  K.blob(C, cx + 40 * s, cy - 30 * s, 118 * s, LIME, 1, 4);
  K.blob(C, cx - 30 * s, cy + 40 * s, 60 * s, '#C9FFB5', 0.9, 7);
  K.blob(C, cx + 150 * s, cy - 150 * s, 36 * s, LIME, 1, 2);
  K.blob(C, cx - 170 * s, cy + 120 * s, 22 * s, '#B6FF9A', 1, 6);
}
function cover(C, v){
  var o = C.o || {}, f = C.cover, img = o.img === 'photo';
  brand(C, 70, 42);
  if (f.date) C.text(W - 370, 42, 300, 22, f.date, {font:'body', size:14, wt:600, color:MUTE, align:'r', va:'m', lh:1});
  if (img) {
    C.rect(700, 110, 510, 500, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26});
    D.imgBox(C, 716, 126, 478, 468, {r:20});
    D.glow(C, 710, 590, 160, 120, LIME, 0.35);
    K.blob(C, 712, 588, 70, LIME, 1, 3); K.blob(C, 780, 640, 26, '#B6FF9A', 1, 6);
  } else blobCluster(C, W - 300, H / 2 + 20, 1.2);
  var tw = img ? 580 : 720;
  C.text(70, 140, tw, 300, C.s.title, {font:'head', fit:{max:96, min:38}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'b', lh:1});
  if (C.s.subtitle) C.text(70, 462, tw - 60, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  var xx = 70;
  [f.presenter, f.designation].filter(Boolean).forEach(function(t, k){ var w = Math.min(320, pw(t, 15)); pill(C, xx, 570, w, 42, t, {size:15, fill:k ? null : LIME}); xx += w + 12; });
}
function closing(C, v){
  var o = C.o || {}, img = o.img === 'photo';
  brand(C, 70, 42);
  if (img) {
    C.rect(700, 110, 510, 500, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26});
    D.imgBox(C, 716, 126, 478, 468, {r:20});
    K.blob(C, 1190, 130, 60, LIME, 1, 3);
  } else blobCluster(C, W - 280, H / 2, 1.1);
  var tw = img ? 580 : 720;
  C.text(70, 130, tw, 220, C.s.title || 'Thank You', {font:'head', fit:{max:104, min:40}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:2, va:'b', lh:1});
  if (C.s.subtitle) C.text(70, 370, tw - 60, 70, C.s.subtitle, {font:'body', fit:{max:19, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:2});
  var cs = D.contacts(C);
  cs.forEach(function(c, k){
    var y = 470 + k * 56;
    C.ell(70, y, 40, 40, {fill:LIME}); C.icon(80, y + 10, 20, c.icon, INK);
    C.text(126, y, tw - 60, 40, c.val, {font:'body', fit:{max:18, min:12}, wt:600, color:'#FFFFFF', va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
  });
}
function agendaA(C, o){
  var te = C.text(70, 130, 440, 260, C.s.title || 'Agenda', {font:'head', fit:{max:84, min:38}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:3, va:'t', lh:1});
  if (C.s.subtitle) C.text(70, 130 + te._h + 20, 400, 80, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:MUTE, ed:{f:'subtitle'}, lh:1.45, maxLines:3});
  D.glow(C, 120, 640, 220, 160, LIME, 0.3);
  K.blob(C, 150, 640, 110, LIME2, 1, 1); K.blob(C, 180, 610, 84, LIME, 1, 4);
  glassIndex(C, {x:560, y:90, w:W - 560 - 70, h:H - 160}, {cols:1});
}
function agendaPhoto(C, o){
  C.text(70, 86, 560, 70, C.s.title || 'Agenda', {font:'head', fit:{max:50, min:28}, wt:800, color:'#FFFFFF', ed:{f:'title'}, maxLines:1});
  D.imgBox(C, 70, 180, 400, 460, {r:20});
  D.glow(C, 470, 180, 90, 90, LIME, 0.4); K.blob(C, 466, 184, 40, LIME, 1, 2);
  glassIndex(C, {x:530, y:180, w:W - 530 - 70, h:460}, {cols:1});
}
function statementPhoto(C, o){
  var y = D.header(C, {x:70, w:600, top:92}, {});
  C.rect(730, 100, 480, 520, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26});
  D.imgBox(C, 746, 116, 448, 488, {r:20});
  D.glow(C, 740, 610, 130, 100, LIME, 0.35); K.blob(C, 740, 606, 54, LIME, 1, 3);
  statement(C, {x:70, y:y + 40, w:600, h:H - 110 - y - 40}, {img:true});
}
function quote(C, v){
  var o = C.o || {}, by = C.it[0];
  if (o.img === 'left') { D.quoteSlide(C, {img:'left', align:'l', qc:LIME, tc:'#FFFFFF'}); return; }
  var photo = o.img === 'photo', x = 70, w = photo ? 600 : 900;
  if (photo) { C.rect(740, 110, 470, 500, {fill:'#FFFFFF', op:0.06, line:'#FFFFFF', lop:0.25, r:26}); D.imgBox(C, 756, 126, 438, 468, {r:20}); }
  else { D.glow(C, W - 200, H / 2, 260, 220, LIME, 0.22); K.blob(C, W - 190, H / 2, 110, LIME, 1, 4); C.text(W - 280, H / 2 - 70, 180, 180, '”', {font:'head', size:200, wt:800, color:INK, align:'c', va:'m', lh:1, caps:false, ls:0}); }
  C.rect(x, 150, 70, 6, {fill:LIME, r:3});
  var qe = C.text(x, 190, w, 300, C.s.title, {font:'head', fit:{max:photo ? 40 : 48, min:20}, wt:600, color:'#FFFFFF', lh:1.25, ed:{f:'title'}, maxLines:6, va:'t'});
  if (by) { var bt = by.h ? by.h + ': ' + by.d : by.d; pill(C, x, 190 + qe._h + 36, Math.min(w, pw(bt, 15)), 42, bt, {size:15, ed:{f:'content-plain', i:by.i}}); }
}

D.register({
  id:'neonlime', name:'Neon Lime Glass', tag:'Tech · dark glass',
  desc:'Near-black green pages lit by lime glows and organic blobs, frosted glass cards with glowing dots, lime pill tags and dot labels for tech and startup decks.',
  fonts:{head:'Outfit', body:'Inter', headW:700, bodyW:400, headLh:1.05, gf:'Outfit:wght@400;600;700;800|Inter:wght@400;500;600;700'},
  pal:P0, s:S, bg:bg, decor:decor, cover:cover, closing:closing, quote:quote,
  variants:{
    cover:[V('v1', 'Lime blobs', {}), V('v2', 'Glass photo frame', {img:'photo'})],
    closing:[V('v1', 'Lime blobs + contacts', {}), V('v2', 'Glass photo + contacts', {img:'photo'})],
    agenda:[V('v1', 'Title + glass index', {fn:agendaA}), V('n2', 'Pill tag grid', {body:pillGrid}), V('v2', 'Photo + glass index', {fn:agendaPhoto})],
    text:[V('v1', 'Lime blob statement', {body:statement}), V('v2', 'Statement + glass photo', {fn:statementPhoto}), V('v3', 'Image left + statement', {body:statement, img:'left'})],
    p2:[V('v1', 'Glass vs lime', {body:limeDuo}), V('n2', 'Dot columns + glass', {body:dotCols}), V('n3', 'Glass cards', {body:'xGlass'}), V('v2', 'Image left + dot rows', {body:dotCols, img:'left'}), V('v3', 'Glass index + image right', {body:glassIndex, img:'right'})],
    p3:[V('v1', 'Dot columns + glass', {body:dotCols}), V('n2', 'Glass cards', {body:'xGlass'}), V('n3', 'Pill tag grid', {body:pillGrid, cols:3}), V('v2', 'Image left + dot rows', {body:dotCols, img:'left'}), V('v3', 'Glass index + image right', {body:glassIndex, img:'right'})],
    p4:[V('v1', 'Glass cards', {body:'xGlass'}), V('n2', 'Dot columns + glass', {body:dotCols}), V('n3', 'Pill tag grid', {body:pillGrid}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right'}), V('v3', 'Image left + dot rows', {body:dotCols, img:'left'})],
    p56:[V('v1', 'Pill tag grid', {body:pillGrid}), V('n2', 'Glass index', {body:glassIndex}), V('n3', 'Glass cards', {body:'xGlass'}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right', cols:1})],
    p78:[V('v1', 'Pill tag grid', {body:pillGrid}), V('n2', 'Glass index', {body:glassIndex}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right', cols:2})],
    stats:[V('v1', 'Glass numbers', {body:glassPct}), V('n2', 'Lime blob numbers', {body:blobStats}), V('v2', 'Glass numbers + image right', {body:glassPct, img:'right'}), V('v3', 'Image left + blobs', {body:blobStats, img:'left'})],
    timeline:[V('v1', 'Glass cards on rail', {body:rail}), V('n2', 'Circuit timeline', {body:circuit}), V('v2', 'Glass index + image right', {body:glassIndex, img:'right'})],
    quote:[V('v1', 'Lime blob quote', {}), V('v2', 'Quote + glass photo', {img:'photo'}), V('v3', 'Image left + quote', {img:'left'})]
  }
});
})();


}catch(e){if(window.console)console.error(e);}
