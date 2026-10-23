
/* DS Template Kit 5 - shared helpers for templates 90-129 (new snippet, changes nothing else) */
(function(){
var D = window.DSTE; if (!D || !D.K2 || !D.K3 || !D.K4 || D.K5) return;
var K = D.K2, K3 = D.K3, K4 = D.K4, W = D.W, H = D.H, u = D.u, mix = u.mix, pad2 = u.pad2, RAD = Math.PI / 180;
var K5 = D.K5 = {};
/* ---- path builders: many shapes in ONE element (one layer in the editor, one shape in PowerPoint) ---- */
var Q = K5.p = {
poly:function(d, pts){ pts.forEach(function(p, i){ d.push([i ? 'L' : 'M', p[0], p[1]]); }); d.push(['Z']); return d; },
rect:function(d, x, y, w, h){ return Q.poly(d, [[x, y], [x + w, y], [x + w, y + h], [x, y + h]]); },
ell:function(d, cx, cy, rx, ry){ var kx = 0.5523 * rx, ky = 0.5523 * ry; d.push(['M', cx + rx, cy], ['C', cx + rx, cy + ky, cx + kx, cy + ry, cx, cy + ry], ['C', cx - kx, cy + ry, cx - rx, cy + ky, cx - rx, cy], ['C', cx - rx, cy - ky, cx - kx, cy - ry, cx, cy - ry], ['C', cx + kx, cy - ry, cx + rx, cy - ky, cx + rx, cy], ['Z']); return d; },
circ:function(d, cx, cy, r){ return Q.ell(d, cx, cy, r, r); },
line:function(d, x1, y1, x2, y2){ d.push(['M', x1, y1], ['L', x2, y2]); return d; },
pts:function(d, pts){ pts.forEach(function(p, i){ d.push([i ? 'L' : 'M', p[0], p[1]]); }); return d; },
arc:function(d, cx, cy, r, a0, a1, first){ K.arcCmds(cx, cy, r, a0, a1, first).forEach(function(c){ d.push(c); }); return d; },
sector:function(d, cx, cy, r0, r1, a0, a1){ Q.arc(d, cx, cy, r1, a0, a1); if (r0 > 0) Q.arc(d, cx, cy, r0, a1, a0, 'L'); else d.push(['L', cx, cy]); d.push(['Z']); return d; },
rrect:function(d, x, y, w, h, r){ r = Math.min(r, w / 2, h / 2); var k = r * 0.4477; d.push(['M', x + r, y], ['L', x + w - r, y], ['C', x + w - k, y, x + w, y + k, x + w, y + r], ['L', x + w, y + h - r], ['C', x + w, y + h - k, x + w - k, y + h, x + w - r, y + h], ['L', x + r, y + h], ['C', x + k, y + h, x, y + h - k, x, y + h - r], ['L', x, y + r], ['C', x, y + k, x + k, y, x + r, y], ['Z']); return d; }
};
K5.fill = function(C, d, color, op){ return d.length ? C.path(d, {fill:color, op:op}) : null; };
K5.stroke = function(C, d, color, lw, op){ return d.length ? C.path(d, {stroke:color, lw:lw || 1, lop:op}) : null; };
K5.pol = K.pol;
K5.rnd = function(s){ return function(){ s = (s * 16807) % 2147483647; return s / 2147483647; }; };
K5.on = K.on;
/* ---- cover / closing text stacks ---- */
K5.coverText = function(C, x, y, w, o){
o = o || {}; var f = C.cover, P = C.P, al = o.align || 'l', tc = o.tc || P.title || P.text, sc = o.sc || P.muted, kc = o.kc || P.accent;
if (f.company) C.text(x, y, w, 22, f.company, {font:'head', size:o.ks || 14, wt:800, color:kc, caps:true, ls:0.12, va:'m', lh:1, align:al, ed:{f:'tf-company'}, maxLines:1});
var ty = y + (o.kGap || 46), te = C.text(x, ty, w, o.th || 190, C.s.title, {font:'head', fit:{max:o.max || 56, min:o.min || 26}, wt:o.wt, color:tc, lh:o.lh || 1.05, align:al, ed:{f:'title'}, maxLines:3});
var yy = ty + te._h + (o.gap || 20);
if (o.rule) { o.rule(C, al === 'c' ? x + w / 2 : (al === 'r' ? x + w : x), yy); yy += o.ruleH || 26; }
var sw = Math.min(w, o.sw || w), sx = al === 'c' ? x + (w - sw) / 2 : (al === 'r' ? x + w - sw : x);
if (C.s.subtitle) { var se = C.text(sx, yy, sw, 76, C.s.subtitle, {font:'body', fit:{max:o.smax || 18, min:12.5}, color:sc, lh:1.4, align:al, ed:{f:'subtitle'}, maxLines:3}); yy += se._h + 24; }
var who = [f.presenter, f.designation].filter(Boolean).join(' · ');
if (who) { C.text(x, yy, w, 24, who, {font:'head', size:15, wt:700, color:tc, va:'m', lh:1, align:al, ed:{f:'tf-presenter'}, maxLines:1}); yy += 32; }
if (f.website) C.text(x, o.by != null ? o.by : yy + 2, w, 18, f.website, {font:'body', size:12.5, wt:600, color:kc, lh:1, align:al, ed:{f:'tf-website'}, maxLines:1});
return yy;
};
K5.closeText = function(C, x, y, w, o){
o = o || {}; var P = C.P, tc = o.tc || P.title || P.text, sc = o.sc || P.muted, al = o.align || 'l';
var te = C.text(x, y, w, o.th || 150, C.s.title || 'Thank You', {font:'head', fit:{max:o.max || 72, min:32}, wt:o.wt, color:tc, lh:1.02, align:al, ed:{f:'title'}, maxLines:2}), yy = y + te._h + 16;
if (C.s.subtitle) { var se = C.text(x, yy, w, 56, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:sc, lh:1.4, align:al, ed:{f:'subtitle'}, maxLines:2}); yy += se._h + 28; }
D.contacts(C).forEach(function(c, k){
var cy = yy + k * 50, cw = Math.min(w, 440), cx = al === 'c' ? x + (w - cw) / 2 : x;
if (o.mark) o.mark(C, cx, cy, k, c); else { C.ell(cx, cy, 36, 36, {fill:o.mc || P.accent}); C.icon(cx + 10, cy + 10, 16, c.icon, o.mi || P.onAccent || '#FFFFFF'); }
C.text(cx + 50, cy, cw - 50, 36, c.val, {font:'body', fit:{max:16, min:11}, wt:600, color:tc, va:'m', ed:c.i >= 0 ? {f:'x-part', i:c.i, p:1} : null, maxLines:1});
});
return yy;
};
K5.quote = function(C, sp){
var o = C.o || {}, P = C.P, by = C.it[0], photo = o.img === 'photo', M = sp.M || 72, w = photo ? 690 : W - 2 * M, q = sp.q || {};
if (photo) D.imgBox(C, 830, 0, 450, H, {r:0});
if (sp.quoteDeco) sp.quoteDeco(C, photo, w);
C.text(M, 92, 160, 160, '“', {font:'head', size:124, wt:800, color:q.mc || P.accent, lh:1, caps:false});
C.text(M, 250, w, 250, C.s.title, {font:'head', fit:{max:photo ? 34 : 42, min:18}, wt:700, color:q.tc || P.title || P.text, lh:1.22, ed:{f:'title'}, maxLines:6});
if (by) C.text(M, 520, w, 26, by.h ? by.h + ' — ' + by.d : by.d, {font:'body', size:15, wt:700, color:q.bc || P.accent, lh:1.1, ed:{f:'content-plain', i:by.i}, maxLines:1});
};
/* ---- parametric body layouts: the template supplies only its own art ---- */
K5.cols = function(C, Z, q, art){
var it = C.it.slice(0, q.max || 6), n = it.length; if (!n) return; if (Z.w < 700 && n > 3 && q.fallback) return q.fallback(C, Z, {});
var g = q.gap != null ? q.gap : 18, cw = (Z.w - g * (n - 1)) / n, pad = q.pad || 0, tg = q.tg != null ? q.tg : 18;
var ah = typeof q.ah === 'function' ? q.ah(n, cw, Z) : (q.ah || Math.min(Z.h * 0.42, cw * 0.7)), tw = cw - pad * 2;
var F = K.fitHD(C, it, tw, 50, Z.h - ah - tg - pad, {hMax:q.hMax || 22, dMax:q.dMax || 16.5, hMin:12, dMin:10});
if (q.pre) q.pre(C, {x:Z.x, y:Z.y, w:Z.w, h:Z.h, cw:cw, g:g, n:n, ah:ah});
it.forEach(function(t, k){
var x = Z.x + k * (cw + g), cc = art(C, k, t, {x:x, y:Z.y, w:cw, h:Z.h, cx:x + cw / 2, ah:ah, n:n, F:F}) || {};
K.hd(C, x + pad, Z.y + ah + tg, tw, Z.h - ah - tg - pad, t, F, {tc:cc.tc || q.tc || C.P.text, mc:cc.mc || q.mc || C.P.muted, align:q.align || 'l'});
});
};
K5.list = function(C, Z, q, art){
var it = C.it.slice(0, q.max || 8), n = it.length; if (!n) return;
var cl = q.cols || (n > (q.split || 5) && Z.w > 760 ? 2 : 1), per = Math.ceil(n / cl), gx = q.gx || 30, cw = (Z.w - gx * (cl - 1)) / cl, g = q.gap != null ? q.gap : 10;
var rh = Math.min((Z.h - g * (per - 1)) / per, q.rhMax || 132), aw = typeof q.aw === 'function' ? q.aw(rh, cw) : (q.aw || 70), tw = cw - aw - (q.pr || 16);
var F = K.fitHD(C, it, tw, 36, rh - (q.vp || 14), {hMax:q.hMax || 22, dMax:q.dMax || (it.some(function(t){ return t.h; }) ? 17 : 22), hMin:12, dMin:10, noBoost:n > 5});
var tot = rh * per + g * (per - 1), y0 = Z.y + Math.max(0, (Z.h - tot) / 2);
if (q.pre) q.pre(C, {x:Z.x, y:y0, w:Z.w, h:tot, rh:rh, g:g, per:per, cl:cl, cw:cw, gx:gx, n:n, aw:aw});
it.forEach(function(t, k){
var c = Math.floor(k / per), r = k % per, x = Z.x + c * (cw + gx), y = y0 + r * (rh + g);
var cc = art(C, k, t, {x:x, y:y, w:cw, h:rh, cy:y + rh / 2, aw:aw, n:n, r:r, c:c, per:per, last:r === per - 1 || k === n - 1}) || {};
var bh = K3.bH(C, t, tw, F); K.hd(C, x + aw, y + Math.max(4, (rh - bh) / 2), tw, rh - 4, t, F, {tc:cc.tc || q.tc || C.P.text, mc:cc.mc || q.mc || C.P.muted});
});
};
K5.alt = function(C, Z, q, art){
var it = C.it.slice(0, q.max || 6), n = it.length; if (n < 2 || Z.w < 760) return q.fallback(C, Z, {});
var ly = Z.y + Z.h * (q.same ? (q.at || 0.3) : 0.5), cw = Z.w / n, tw = cw - 20, off = q.off || 46, hh = (q.same ? Z.y + Z.h - ly : Z.h / 2) - off - 4;
var F = K.fitHD(C, it, tw, 44, hh, {hMax:q.hMax || 21, dMax:q.dMax || 16, hMin:12, dMin:10, noBoost:n > 3});
if (q.pre) q.pre(C, {x:Z.x, y:ly, w:Z.w, cw:cw, n:n, top:Z.y, h:Z.h});
it.forEach(function(t, k){
var cx = Z.x + cw * (k + 0.5), up = q.same ? false : k % 2 === 0, bh = K3.bH(C, t, tw, F);
art(C, k, t, {cx:cx, cy:ly, up:up, cw:cw, n:n, top:Z.y, h:Z.h});
K.hd(C, cx - tw / 2, up ? ly - off - bh : ly + off, tw, hh, t, F, {tc:q.tc || C.P.text, mc:q.mc || C.P.muted, align:'c'});
});
};
K5.stat = function(C, Z, q, art){
var st = C.it.slice(0, 4), n = st.length; if (!n) return;
var cw = Z.w / n, vals = st.map(K3.sv), labs = st.map(K3.sl), pad = q.pad || 34, tw = cw - pad, ah = typeof q.ah === 'function' ? q.ah(cw, Z) : (q.ah || Z.h * 0.5), tg = q.tg != null ? q.tg : 16, al = q.align || 'c';
var vs = C.groupFit(vals, q.vw ? q.vw(cw, ah) : tw, ah * 0.5, {font:'head', max:q.vMax || 54, min:16, maxLines:1}), ls = C.groupFit(labs, tw, 50, {font:'head', max:19, min:12, maxLines:2});
var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, Math.max(30, Z.h - ah - tg - 62), {font:'body', max:14.5, min:10, lh:1.38});
if (q.pre) q.pre(C, {x:Z.x, y:Z.y, w:Z.w, h:Z.h, cw:cw, n:n, ah:ah});
st.forEach(function(t, k){
var x = Z.x + cw * k, cx = x + cw / 2, tx = x + pad / 2, r = art(C, k, t, {x:x, y:Z.y, w:cw, h:Z.h, cx:cx, ah:ah, vs:vs, val:vals[k], n:n}) || {};
if (!r.noVal) C.text(r.vx != null ? r.vx : tx, r.vy != null ? r.vy : Z.y + ah - vs * 1.25, r.vw || tw, vs * 1.15, vals[k], {font:'head', size:vs, wt:800, color:r.vc || C.P.accent, align:r.va || al, va:'m', lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
var ly = Z.y + ah + tg, le = C.text(tx, ly, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:q.tc || C.P.text, align:al, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
if (t.x) C.text(tx, ly + le._h + 8, tw, Math.max(20, Z.y + Z.h - (ly + le._h + 8)), t.x, {font:'body', size:ds, color:q.mc || C.P.muted, align:al, lh:1.38, ed:{f:'x-part', i:t.i, p:2}});
});
};
/* ---- template factory: palette + skin + art in, registered template out ---- */
K5.make = function(sp){
var P = sp.P, LC = sp.LC || [P.accent, P.accent2 || P.accent], M = sp.M || 72, ON = P.onAccent || '#FFFFFF';
function lc(k){ return LC[((k % LC.length) + LC.length) % LC.length]; }
var X = {
M:M, tc:P.text, mc:P.muted, ac:P.accent, markS:sp.markS || 44, g:sp.g || 18, ptc:P.text, pmc:P.muted, gap:sp.gap, footH:sp.footH, imgPct:sp.imgPct, rg:sp.rg, pad:sp.pad,
head:sp.head || function(C, A, o){
var y = sp.headTop || 58, hw = Math.min(A.w, sp.headW || 900);
var te = C.text(A.x, y, hw, 92, C.s.title || '', {font:'head', fit:{max:sp.tMax || 40, min:22}, color:P.title || P.text, lh:1.08, ed:{f:'title'}, maxLines:2});
y += te._h + 12;
if (sp.rule) y += sp.rule(C, A.x, y, A) || 16; else { C.rect(A.x, y, 56, 5, {fill:P.accent, r:2.5}); y += 16; }
if (C.s.subtitle) { var se = C.text(A.x, y, Math.min(A.w, 760), 44, C.s.subtitle, {font:'body', fit:{max:15.5, min:12}, color:P.muted, lh:1.42, ed:{f:'subtitle'}, maxLines:2}); y += se._h; }
return y;
},
card:sp.card || function(C, x, y, w, h, k, o){
var r = sp.cardR != null ? sp.cardR : 14;
if (o.big) { C.rect(x, y, w, h, {fill:P.card, r:r}); return {tc:P.text, mc:P.muted, ac:P.accent}; }
if (o.hot) { C.rect(x, y, w, h, {fill:P.accent, r:r}); return {tc:ON, mc:mix(ON, P.accent, 0.22), ac:ON, hot:1}; }
C.rect(x, y, w, h, {fill:P.card, r:r, line:P.line, lw:1.2}); return {tc:P.text, mc:P.muted, ac:lc(k)};
},
row:sp.row || function(C, x, y, w, h, k){ C.rect(x, y + h - 1, w, 1, {fill:P.line}); return {tc:P.text, mc:P.muted, ac:P.accent}; },
mark:sp.mark || function(C, x, y, s, t, k, cc){ var c = lc(k), hot = cc && cc.hot; C.ell(x, y, s, s, {fill:hot ? ON : c}); K.icon(C, x + s * 0.25, y + s * 0.25, s * 0.5, t, hot ? P.accent : K.on(c)); },
rowMark:sp.rowMark, node:sp.node, track:sp.track, stepCard:sp.stepCard,
photo:sp.photo || function(C, Z, side){
if (side === 'bottom') { D.imgBox(C, M, Z.y + 8, W - 2 * M, Z.h - 26, {r:sp.imgR || 0}); if (sp.photoDeco) sp.photoDeco(C, {x:M, y:Z.y + 8, w:W - 2 * M, h:Z.h - 26}, side); return; }
var b = {x:Z.x + (side === 'right' ? 6 : 34), y:64, w:Z.w - 40, h:H - 150};
D.imgBox(C, b.x, b.y, b.w, b.h, {r:sp.imgR || 0}); if (sp.photoDeco) sp.photoDeco(C, b, side);
}
};
var B = K3.make(X);
function foot(C){ var a = K3.footArea(C), y = H - 40; C.text(a.x, y, 420, 16, C.cover.website || C.cover.company || '', {font:'body', size:11.5, wt:600, color:P.muted, lh:1, va:'m'}); C.text(a.x + a.w - 60, y, 60, 16, pad2(C.i + 1), {font:'head', size:12.5, wt:800, color:P.accent, align:'r', va:'m', lh:1}); }
var S = {margin:M, series:LC.concat(sp.LC2 || []), header:{kind:'left', top:58}, card:{kind:'outline', r:sp.cardR != null ? sp.cardR : 14}, icon:{kind:'plain', fg:P.accent}, num:{kind:'big', fg:P.accent},
conc:sp.conc || {kind:'pill', fill:P.accent, op:0.1, label:sp.concLabel || 'Key takeaway', lc:P.accent, tc:P.text}, footer:{fn:sp.foot || foot}, imgFramed:true, imgR:sp.imgR || 0, bodyGap:24,
imgPh:sp.imgPh || {grad:{ang:135, stops:[[0, mix(P.accent, P.bg, 0.55)], [100, P.accent]]}}};
for (var sk in sp.S || {}) S[sk] = sp.S[sk];
function pg(body, ex){ var o = {fn:function(C, oo){ K3.page(C, oo, X); }, body:body}; for (var k in ex || {}) o[k] = ex[k]; return o; }
function bg(C, kind){ C.bg(sp.bgFill ? (typeof sp.bgFill === 'function' ? sp.bgFill(C, kind) : sp.bgFill) : {fill:P.bg}); if (sp.deco) sp.deco(C, kind); }
var L0 = sp.L || {}, L = {cards:L0.cards || 'Cards', rows:L0.rows || 'Rows', steps:L0.steps || 'Steps', stmt:L0.stmt || 'Statement', stats:L0.stats || 'Numbers', photo:'photo', track:L0.track || P.line};
var ctx = {P:P, LC:LC, lc:lc, X:X, B:B, pg:pg, M:M, S:S};
var sig = sp.sig ? sp.sig(pg, B, ctx) : {}, cl = sp.coverLbl || 'Cover', el = sp.closeLbl || 'Closing';
if (!sig.cover) sig.cover = sp.noCoverPhoto ? [[cl, {}]] : [[cl, {}], [cl + ' + photo', {img:'photo'}]];
if (!sig.closing) sig.closing = sp.noClosePhoto ? [[el, {}]] : [[el, {}], [el + ' + photo', {img:'photo'}]];
if (!sig.quote) sig.quote = [['Quote', {}], ['Quote + photo', {img:'photo'}]];
D.register({id:sp.id, name:sp.name, tag:sp.tag, desc:sp.desc, fonts:sp.fonts, pal:P, s:S, bg:bg, cover:sp.cover, closing:sp.closing, quote:sp.quote || function(C){ K5.quote(C, sp); }, variants:K4.vset(pg, B, L, sig)});
return ctx;
};
})();
