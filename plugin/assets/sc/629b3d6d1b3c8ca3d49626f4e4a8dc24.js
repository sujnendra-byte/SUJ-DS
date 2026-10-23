
/* Forest Campaign (template 21): second road of the cover / closing decor was drawn in the panel colour (it only cut a gap into the first road) and ran past the slide edge. Give it a quiet lighter tone of the panel and keep it inside the panel. */
(function(){
var D = window.DSTE; if (!D || !D.renderPage || D.__forestRoads) return; D.__forestRoads = 1;
function rgb(c){ c = String(c || '').replace('#', ''); if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2]; if (!/^[0-9a-fA-F]{6}$/.test(c)) return null; return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)]; }
function mix(a, b, t){ var x = rgb(a), y = rgb(b); if (!x || !y) return a; var h = '#'; for (var i = 0; i < 3; i++) { var v = Math.round(x[i] + (y[i] - x[i]) * t).toString(16); h += (v.length < 2 ? '0' : '') + v; } return h.toUpperCase(); }
function fix(page){
  var els = page.els || [], panel = null, roads = [], i, j;
  for (i = 0; i < els.length; i++) { var e = els[i]; if (!e) continue;
    if (!panel && e.t === 'rect' && e.h >= 700 && e.w > 400 && e.w < 800 && e.fill) panel = e;
    if (e.t === 'path' && e.stroke && !e.fill && (e.lw || 0) >= 18 && e.d && e.d.length > 10) roads.push(e); }
  if (!panel || roads.length !== 2) return;
  var b = roads[1], c = rgb(panel.fill); if (!c) return;
  var lum = (c[0] * 299 + c[1] * 587 + c[2] * 114) / 1000;
  b.stroke = lum < 150 ? mix(panel.fill, '#FFFFFF', 0.2) : mix(panel.fill, '#000000', 0.14);
  var mn = 1e9, mx = -1e9;
  for (i = 0; i < b.d.length; i++) { var s = b.d[i]; for (j = 1; j + 1 < s.length; j += 2) { if (s[j] < mn) mn = s[j]; if (s[j] > mx) mx = s[j]; } }
  var half = (b.lw || 0) / 2 + 4, dx = 0;
  if (mx + half > panel.x + panel.w) dx = panel.x + panel.w - (mx + half);
  if (mn + dx - half < panel.x) dx = panel.x - (mn - half);
  if (!dx) return;
  for (i = 0; i < b.d.length; i++) { var q = b.d[i]; for (j = 1; j + 1 < q.length; j += 2) q[j] += dx; }
  for (i = 0; i < els.length; i++) { var p = els[i]; if (p && p.t === 'ell' && p.w <= 60 && p.x + p.w / 2 >= mn - 30 && p.x + p.w / 2 <= mx + 30) p.x += dx; }
}
var rp = D.renderPage;
D.renderPage = function(T){
  var page = rp.apply(this, arguments);
  try { var id = T && (T.id || (T.__rec && T.__rec.id)); if (page && id === 'forest' && (page.kind === 'cover' || page.kind === 'closing')) fix(page); } catch (err) {}
  return page;
};
if (D.refreshEditor) { try { D.refreshEditor(); } catch (e2) {} }
})();
