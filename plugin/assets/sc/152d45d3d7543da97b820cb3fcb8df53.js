
(function(){
'use strict';
var X = window.DSEL, D = window.DSTE;
if (!X || !D || !X.fx || !X.orig || !D.renderPage || X.__twin) return;
X.__twin = 1;
var orig0 = X.orig, fx0 = X.fx, rp0 = D.renderPage, cur = null, mem = (typeof WeakMap === 'function') ? new WeakMap() : null, loose = {};

/* remember how each element looked before the user's changes were applied */
X.orig = function(e){
  if (e && !e._tw0) e._tw0 = {t: e.t, r: +e.r || 0, prst: e.prst || '', sides: +e.sides || 0};
  return orig0.apply(this, arguments);
};
function near(a, b){ return Math.abs((+a || 0) - (+b || 0)) < 0.6; }
function gSig(e){ return [Math.round(e.x * 10), Math.round(e.y * 10), Math.round(e.w * 10), Math.round(e.h * 10), Math.round((+e.rot || 0) * 10)].join(','); }
function sSig(e){ return [e.t, +e.r || 0, e.prst || '', +e.sides || 0].join(','); }
function gChanged(e){ var b = e._b; return !near(e.x, b.x) || !near(e.y, b.y) || !near(e.w, b.w) || !near(e.h, b.h) || !!(+e.rot || 0); }
function sChanged(e){ var o = e._tw0; return e.t !== o.t || (+e.r || 0) !== o.r || (e.prst || '') !== o.prst || (+e.sides || 0) !== o.sides; }
function store(){
  var s = cur && cur.slide;
  if (s && mem && typeof s === 'object') { var m = mem.get(s); if (!m) { m = {}; mem.set(s, m); } return m; }
  return loose;
}
/* which of the two was changed last: the one whose values moved since the previous drawing */
function leader(st, id, sa, sb, ca, cb){
  var p = st[id], lead;
  if (p && p.sa !== sa && p.sb === sb) lead = 'a';
  else if (p && p.sb !== sb && p.sa === sa) lead = 'b';
  else if (p && p.sa === sa && p.sb === sb) lead = p.lead;
  else lead = cb ? 'b' : (ca ? 'a' : '');
  st[id] = {sa: sa, sb: sb, lead: lead};
  return lead;
}
function copyGeo(s, d){ d.x = s.x; d.y = s.y; d.w = s.w; d.h = s.h; if (+s.rot) d.rot = s.rot; else delete d.rot; }
function copyShape(s, d){
  d.t = s.t;
  ['r', 'prst', 'sides'].forEach(function(k){ if (s[k] != null) d[k] = s[k]; else delete d[k]; });
  delete d.pd;
  if (d.t === 'shape' && X.mkPd) X.mkPd(d);
}
/* a card drawn as two boxes (the fill and, on top of it, the outline) behaves as one card */
function sync(arr){
  var map = {}, st = store(), vid = (cur && cur.vid) || '';
  arr.forEach(function(e){ if (e && e._dk) map[e._dk] = e; });
  arr.forEach(function(a){
    var m = /^rect(\d+)$/.exec((a && a._dk) || ''); if (!m || a._add) return;
    var b = map['rect' + (+m[1] + 1)]; if (!b || b._add) return;
    var oa = a._o, ob = b._o, ta = a._tw0, tb = b._tw0, ba = a._b, bb = b._b;
    if (!oa || !ob || !ta || !tb || !ba || !bb) return;
    if (ta.t !== 'rect' || tb.t !== 'rect' || ta.r !== tb.r) return;
    if (!(oa.fill || oa.grad) || oa.line) return;          /* a: filled, no outline */
    if (ob.fill || ob.grad || !ob.line) return;            /* b: outline only */
    if (!near(ba.x, bb.x) || !near(ba.y, bb.y) || !near(ba.w, bb.w) || !near(ba.h, bb.h)) return;
    if (ba.w < 40 || ba.h < 24) return;
    var id = vid + '|' + a._dk;
    var lg = leader(st, id + '|g', gSig(a), gSig(b), gChanged(a), gChanged(b));
    var ls = leader(st, id + '|s', sSig(a), sSig(b), sChanged(a), sChanged(b));
    if (lg === 'a') copyGeo(a, b); else if (lg === 'b') copyGeo(b, a);
    if (ls === 'a') copyShape(a, b); else if (ls === 'b') copyShape(b, a);
    else if (lg && a.t === 'shape' && X.mkPd) { X.mkPd(a); X.mkPd(b); }
    if ((lg || ls) && a.t === 'shape' && X.mkPd) { X.mkPd(a); X.mkPd(b); }
    /* one shadow for the card, cast by the filled box so it has the card's shape */
    if (b.sh && !a.sh) { a.sh = b.sh; delete b.sh; delete a.shadow; }
    else if (b.sh && a.sh) delete b.sh;
    if (b.shadow && !a.sh && !a.shadow) { a.shadow = b.shadow; delete b.shadow; }
  });
}
X.fx = function(e, i, arr){
  if (i === 0 && arr && arr.length && typeof arr.forEach === 'function') { try { sync(arr); } catch (er) { if (window.console) console.warn('[DS card sync]', er); } }
  return fx0.call(this, e);
};
D.renderPage = function(T, slide, vid){
  var prev = cur; cur = {slide: slide, vid: vid || 'v1'};
  try { return rp0.apply(this, arguments); } finally { cur = prev; }
};
})();
