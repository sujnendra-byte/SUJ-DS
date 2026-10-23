
(function(){
(function(){
  var D = window.DSTE; if (!D || !D.kindOf || !D.renderPage || !D.items || D.__ptOverflow2) return; D.__ptOverflow2 = 1;
  var KINDS = {agenda:1, timeline:1, stats:1}, flags = {}, curId = '', ko = D.kindOf, rp = D.renderPage;
  function byCount(n){ return n <= 1 ? 'text' : n === 2 ? 'p2' : n === 3 ? 'p3' : n === 4 ? 'p4' : n <= 6 ? 'p56' : n <= 8 ? 'p78' : 'p9'; }
  function shown(p){ var m = {}, c = 0; (p && p.els || []).forEach(function(e){ if (e && e.ed && (e.ed.f === 'x-part' || e.ed.f === 'content-plain') && !m[e.ed.i]) { m[e.ed.i] = 1; c++; } }); return c; }
  function plain(slide){ var c = {}; for (var k in slide) c[k] = slide[k]; c.type = 'bullet'; return c; }
  /* the layout picker asks for the kind: answer with the many-point kind once a slide is known to overflow */
  D.kindOf = function(slide){
    var k = ko.apply(this, arguments);
    if (KINDS[k]) { var n = 0; try { n = D.items(slide).length; } catch (e) {} if (flags[curId + '|' + k + '|' + n]) return byCount(n); }
    return k;
  };
  D.renderPage = function(T, slide){
    if (T && T.id) curId = T.id;
    var a = Array.prototype.slice.call(arguments), k = '', n = 0, key = '';
    try { k = ko.call(D, slide); if (KINDS[k]) { n = D.items(slide).length; key = curId + '|' + k + '|' + n; } } catch (e) { k = ''; }
    if (key && flags[key]) { a[1] = plain(slide); return rp.apply(this, a); }
    var p = rp.apply(this, a);
    if (key && n >= 2) { try { if (shown(p) < n) { a[1] = plain(slide); var p2 = rp.apply(this, a); if (shown(p2) > shown(p)) { flags[key] = 1; p = p2; } } } catch (e) {} }
    return p;
  };
})();
})();
