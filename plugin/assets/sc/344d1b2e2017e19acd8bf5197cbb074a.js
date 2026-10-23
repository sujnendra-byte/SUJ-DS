
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
    var _h0 = h.length;                                   /* DS Template Element Editor: where this element's HTML starts */
    var box = 'position:absolute;left:'+px(e.x,W)+';top:'+px(e.y,H)+';width:'+px(e.w,W)+';height:'+px(e.h,H)+';';
    if (e.t==='shape' && e.pd && e.pd.length) { /* DS Template Element Editor: a shape with round corners or its own number of sides, drawn as its outline */
  var sw0 = Math.max(1, +e.w || 1), sh0 = Math.max(1, +e.h || 1), sid = 'dss' + (DSTE.__sv = (DSTE.__sv || 0) + 1), sdf = '', sfill = 'none', sop = e.op != null ? +e.op : 1;
  var sgr = function(g, id, op){
    var st = (g.stops || []).map(function(q){ return '<stop offset="' + (+q[0] || 0) + '%" stop-color="' + hx(q[1]) + '" stop-opacity="' + ((q[2] != null ? +q[2] : 1) * (op != null ? op : 1)) + '"/>'; }).join('');
    if (g.type === 'radial') return '<radialGradient id="' + id + '" cx="0.5" cy="0.5" r="0.5">' + st + '</radialGradient>';
    var ga = (g.ang != null ? +g.ang : 135) * Math.PI / 180, gx = Math.sin(ga) / 2, gy = -Math.cos(ga) / 2;
    return '<linearGradient id="' + id + '" x1="' + (0.5 - gx) + '" y1="' + (0.5 - gy) + '" x2="' + (0.5 + gx) + '" y2="' + (0.5 + gy) + '">' + st + '</linearGradient>';
  };
  if (e.grad && e.grad.stops) { sdf += sgr(e.grad, sid + 'f', sop); sfill = 'url(#' + sid + 'f)'; }
  else if (e.fill) sfill = hx(e.fill);
  var spd = pathD(e.pd), sbody = '<path d="' + spd + '" fill="' + sfill + '"' + (sfill.charAt(0) === '#' && sop < 1 ? ' fill-opacity="' + sop + '"' : '') + '/>';
  if (e.pimg && e.pimg.src) {
    var pq = e.pimg, iw0 = +pq.iw || 0, ih0 = +pq.ih || 0, ix = 0, iy = 0, iW = sw0, iH = sh0;
    if (!pq.st && iw0 > 0 && ih0 > 0) { var ksc = Math.max(sw0 / iw0, sh0 / ih0) * Math.max(1, +pq.z || 1); iW = iw0 * ksc; iH = ih0 * ksc; ix = (sw0 - iW) * (pq.px != null ? +pq.px : 50) / 100; iy = (sh0 - iH) * (pq.py != null ? +pq.py : 50) / 100; }
    sdf += '<clipPath id="' + sid + 'c"><path d="' + spd + '"/></clipPath>';
    sbody += '<image href="' + String(pq.src).replace(/["<>\s]/g, '') + '" x="' + ix + '" y="' + iy + '" width="' + iW + '" height="' + iH + '" preserveAspectRatio="none" clip-path="url(#' + sid + 'c)" opacity="' + (pq.op != null ? Math.max(0, Math.min(1, +pq.op)) : 1) + '"/>';
  }
  if (e.line) {
    var sst = hx(e.line);
    if (e.lgrad && e.lgrad.stops) { sdf += sgr(e.lgrad, sid + 'l', null); sst = 'url(#' + sid + 'l)'; }
    sbody += '<path d="' + spd + '" fill="none" stroke="' + sst + '" stroke-opacity="' + (e.lop != null ? +e.lop : 1) + '" stroke-width="' + (+e.lw || 1) + '" stroke-linejoin="round"' + (e.dash ? ' stroke-dasharray="6 5"' : '') + '/>';
  }
  h += '<svg style="' + box + 'overflow:visible;pointer-events:none;' + (e.rot ? 'transform:rotate(' + e.rot + 'deg);' : '') + '" viewBox="0 0 ' + sw0 + ' ' + sh0 + '" preserveAspectRatio="none">' + (sdf ? '<defs>' + sdf + '</defs>' : '') + sbody + '</svg>';
} else
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
      var pin = ''; /* DS Template Element Editor: Fill > Picture */
if (e.pimg && e.pimg.src) { var pg0 = e.pimg, bs = '100% 100%'; if (!pg0.st) { var piw = +pg0.iw || 0, pih = +pg0.ih || 0; if (piw > 0 && pih > 0 && e.w > 0 && e.h > 0) { var psc = Math.max(e.w / piw, e.h / pih) * Math.max(1, +pg0.z || 1); bs = (piw * psc / e.w * 100).toFixed(3) + '% ' + (pih * psc / e.h * 100).toFixed(3) + '%'; } else bs = 'cover'; } s += 'overflow:hidden;'; pin = '<div style="position:absolute;inset:0;background-image:url(\'' + String(pg0.src).replace(/[\'"()\\<>\s]/g, '') + '\');background-repeat:no-repeat;background-size:' + bs + ';background-position:' + (pg0.px != null ? +pg0.px : 50) + '% ' + (pg0.py != null ? +pg0.py : 50) + '%;opacity:' + (pg0.op != null ? Math.max(0, Math.min(1, +pg0.op)) : 1) + ';pointer-events:none;"></div>'; }
h += '<div style="'+s+'pointer-events:none;">'+pin+'</div>';
    } else if (e.t==='line') {
      h += svgWrap('<line x1="'+e.x1+'" y1="'+e.y1+'" x2="'+e.x2+'" y2="'+e.y2+'" stroke="'+hx(e.color)+'" stroke-opacity="'+(e.op!=null?e.op:1)+'" stroke-width="'+(e.lw||1)+'"'+(e.dash?' stroke-dasharray="6 5"':'')+'/>');
    } else if (e.t==='path') {
      h += svgWrap('<path d="'+pathD(e.d)+'" fill="'+(e.fill?hx(e.fill):'none')+'" fill-opacity="'+(e.op!=null?e.op:1)+'" stroke="'+(e.stroke?hx(e.stroke):'none')+'" stroke-opacity="'+(e.lop!=null?e.lop:1)+'" stroke-width="'+(e.lw||1)+'" stroke-linecap="round" stroke-linejoin="round"/>');
    } else if (e.t==='icon') {
      var u='https://api.iconify.design/'+String(e.name).replace(':','/')+'.svg?color='+encodeURIComponent(hx(e.color));
      if ((e.tgrad && e.tgrad.stops) || e.tline) { /* DS Template Element Editor: a gradient colour and / or an outline (solid or gradient) on an icon */
  var iw1 = Math.max(1, +e.w || 1), ih1 = Math.max(1, +e.h || 1), iid = 'dsi' + (DSTE.__sv = (DSTE.__sv || 0) + 1), iu = 'https://api.iconify.design/' + String(e.name).replace(':', '/') + '.svg?color=%23ffffff', idf = '', ibd = '', ipd = (+e.tlw || 1) * 2 + 2;
  var igr = function(g, id){
    var st = (g.stops || []).map(function(q){ return '<stop offset="' + (+q[0] || 0) + '%" stop-color="' + hx(q[1]) + '" stop-opacity="' + (q[2] != null ? +q[2] : 1) + '"/>'; }).join('');
    if (g.type === 'radial') return '<radialGradient id="' + id + '" cx="0.5" cy="0.5" r="0.5">' + st + '</radialGradient>';
    var ga = (g.ang != null ? +g.ang : 135) * Math.PI / 180, gx = Math.sin(ga) / 2, gy = -Math.cos(ga) / 2;
    return '<linearGradient id="' + id + '" x1="' + (0.5 - gx) + '" y1="' + (0.5 - gy) + '" x2="' + (0.5 + gx) + '" y2="' + (0.5 + gy) + '">' + st + '</linearGradient>';
  };
  var irc = function(paint, mk){ return '<rect x="' + (-ipd) + '" y="' + (-ipd) + '" width="' + (iw1 + 2 * ipd) + '" height="' + (ih1 + 2 * ipd) + '" fill="' + paint + '" mask="url(#' + mk + ')"/>'; };
  var imk = function(id, filt){ return '<mask id="' + id + '" maskUnits="userSpaceOnUse" x="' + (-ipd) + '" y="' + (-ipd) + '" width="' + (iw1 + 2 * ipd) + '" height="' + (ih1 + 2 * ipd) + '"><image href="' + iu + '" x="0" y="0" width="' + iw1 + '" height="' + ih1 + '"' + (filt ? ' filter="url(#' + filt + ')"' : '') + '/></mask>'; };
  var ifl = hx(e.color); if (e.tgrad && e.tgrad.stops) { idf += igr(e.tgrad, iid + 'f'); ifl = 'url(#' + iid + 'f)'; }
  if (e.tline) {
    var ilc = hx(e.tline); if (e.tlgrad && e.tlgrad.stops) { idf += igr(e.tlgrad, iid + 'l'); ilc = 'url(#' + iid + 'l)'; }
    idf += '<filter id="' + iid + 'd" x="-50%" y="-50%" width="200%" height="200%"><feMorphology operator="dilate" radius="' + (+e.tlw || 1) + '"/></filter>' + imk(iid + 'o', iid + 'd');
    ibd += irc(ilc, iid + 'o');
  }
  idf += imk(iid + 'm', ''); ibd += irc(ifl, iid + 'm');
  h += '<svg style="' + box + 'display:block;overflow:visible;pointer-events:none;" viewBox="0 0 ' + iw1 + ' ' + ih1 + '" preserveAspectRatio="none"><defs>' + idf + '</defs>' + ibd + '</svg>';
} else
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
             + (e.dec?'text-decoration:'+(String(e.dec).indexOf('u')>=0?'underline ':'')+(String(e.dec).indexOf('s')>=0?'line-through':'')+';':'')
             + (e.nw?'white-space:nowrap;':'white-space:pre-wrap;overflow-wrap:break-word;')+'margin:0;padding:0;outline:none;';
      var ed = '';
      if (edit && e.ed && idx>=0) {
        ed = ' contenteditable="true" spellcheck="false" data-ed-slide="'+idx+'" data-ed-field="'+e.ed.f+'"'+(e.ed.i!=null?' data-ed-idx="'+e.ed.i+'"':'')+(e.ed.p!=null?' data-ed-part="'+e.ed.p+'"':'');
        ts += 'cursor:text;pointer-events:auto;min-height:1em;';
      }
      h += '<div style="'+box+'display:flex;flex-direction:column;justify-content:'+jc+';pointer-events:none;"><div'+ed+' style="'+ts+'">'+esc(e.text)+'</div></div>';
    }
    /* DS Template Element Editor: the element's key on its node (data-dk); rotation for the kinds that had none;
       transparency for icons, pictures and the logo */
    if (h.length > _h0 && (e._dk || e.rot || e.op != null || e._css || e._cssIn || e._wrap)) {
      var _seg = h.slice(_h0), _ex = '', _box = (e.t==='icon'||e.t==='ring'||e.t==='img'||e.t==='chart'||e.t==='logo'||e.t==='text');
      if (e.rot && _box) _ex += 'transform:rotate('+(+e.rot||0)+'deg);';
      if (e.op != null && (e.t==='icon'||e.t==='img'||e.t==='logo')) _ex += 'opacity:'+Math.max(0,Math.min(1,+e.op||0))+';';
      if (_ex) _seg = _seg.replace(' style="', ' style="'+_ex);
      if (e._dk) _seg = _seg.replace(/^<([a-z]+)/, '<$1 data-dk="'+String(e._dk).replace(/[^A-Za-z0-9_.~-]/g,'')+'"');
      /* DS Template Element Editor: shadow, gradient outline, text outline, gradient text (CSS made by that snippet) */
      if (e._css) { var _c1 = String(e._css).replace(/["<>]/g,''); _seg = _seg.replace(/ style="([^"]*)"/, function(m0, a){ return ' style="'+a+_c1+'"'; }); }
      if (e._cssIn) { var _c2 = String(e._cssIn).replace(/["<>]/g,''), _n2 = 0; _seg = _seg.replace(/ style="([^"]*)"/g, function(m0, a){ return (++_n2 === 2) ? ' style="'+a+_c2+'"' : m0; }); }
      if (e._wrap) _seg = '<div style="position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;'+String(e._wrap).replace(/["<>]/g,'')+'">'+_seg+'</div>';
      h = h.slice(0,_h0) + _seg;
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
var _pick0 = (function(){ try { return sessionStorage.getItem(PICK_KEY) || 'classic'; } catch(e){ return 'classic'; } })();
  /* the Classic card now makes decks with the editable Classic (template 'classicx', snippet "DS Template 65") when it is active; old Classic decks still open the old way */
  Object.defineProperty(DSTE, 'pick', {configurable:true, enumerable:true, get:function(){ return (!_pick0 || _pick0 === 'classic') && DSTE.reg && DSTE.reg.classicx ? 'classicx' : _pick0; }, set:function(v){ _pick0 = v; }});
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
  var ids = DSTE.reg.classicx ? ['classicx'].concat(DSTE.order.filter(function(x){ return x !== 'classicx'; })) : ['classic'].concat(DSTE.order);   /* editable Classic takes the Classic card's place */
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


