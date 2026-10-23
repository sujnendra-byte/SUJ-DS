
(function(){
var D = window.DSTE; if (!D || !D.register || !D.K2 || D.glassKit) return;
var W = D.W, H = D.H, u = D.u, V = D.V, K = D.K2, pad2 = u.pad2;
/* DS Template Glass Kit - one frosted-glass layout system that a template fills with its own colours, fonts, glass shape and background scene */
D.glassKit = function(c){
  var TX = c.tx || '#FFFFFF', MU = c.mu || '#B9C0CC', AC = c.ac, BG = c.bg, R0 = c.r != null ? c.r : 20, CAPS = !!c.caps, LS = CAPS ? 0.03 : 0;
  var S = {
    margin:72, series:c.series || [AC, TX, c.ac2 || MU, '#7C8798', '#4B5563', '#D1D5DB'],
    header:{kind:'left', top:58, tMax:42, tMin:26, sMax:15, sMin:12, kicker:null},
    card:{kind:'glass', r:R0, fill:'#FFFFFF', op:0.08, line:c.rim || '#FFFFFF', lop:0.4, lw:1.2, shadow:0, dark:true, tc:TX, mc:MU,
          hl:{kind:'glass', fill:'#FFFFFF', op:0.16, dark:true, tc:TX, mc:MU}},
    icon:{kind:'plain', fg:AC}, num:{kind:'big', fg:AC},
    conc:{kind:'box', fill:'#FFFFFF', op:0.08, r:Math.min(R0, 22), line:c.rim || '#FFFFFF', lw:1.2, label:'Takeaway', lc:AC, tc:TX},
    footer:{fn:foot}, imgFramed:true, imgR:R0, bodyGap:26, imgPh:{grad:c.ph}
  };
  var U = {W:W, H:H, K:K, D:D, AC:AC, AC2:c.ac2, TX:TX, BG:BG};
  /* frosted glass: a dark veil (the layer that receives the blurred backdrop), a bright rim and a short shine on the top edge */
  function glass(C, x, y, w, h, r, o){
    o = o || {}; r = Math.min(r, h / 2, w / 2);
    var e = C.rect(x, y, w, h, {fill:c.veil || '#05070C', op:o.veil != null ? o.veil : (c.clear != null ? c.clear : 0.2), r:r}); e.frost = c.blur || 26;
    var f = C.rect(x, y, w, h, {fill:'#FFFFFF', op:c.tint != null ? c.tint : 0.08, line:c.rim || '#FFFFFF', lop:c.rimOp != null ? c.rimOp : 0.45, lw:1.2, r:r}); f.frost = 0;
    if (c.edge !== false) { var s = C.rect(x + r + 6, y + 1, Math.max(10, w * 0.4 - r), 1.5, {fill:'#FFFFFF', op:0.5}); s.frost = 0; }
  }
  function mark(C, x, y){
    var m = c.mark || 'bar';
    if (m === 'dot') { C.ell(x, y - 2, 9, 9, {fill:AC}); C.ell(x + 15, y - 2, 9, 9, {line:AC, lw:1.5}); }
    else if (m === 'tick') { C.rect(x, y - 4, 3, 16, {fill:AC}); C.rect(x, y - 4, 16, 3, {fill:AC}); }
    else if (m === 'line') C.rect(x, y + 1, 96, 2, {fill:AC});
    else C.rect(x, y, 46, 5, {fill:AC, r:R0 ? 3 : 0});
  }
  function num(C, x, y, k, s){
    var n = c.num || 'plain';
    if (n === 'box' && s < 20) { C.rect(x, y - 2, s * 1.9, s * 1.5, {fill:AC, r:3}); C.text(x, y - 2, s * 1.9, s * 1.5, pad2(k + 1), {font:'head', size:s * 0.9, wt:800, color:c.onAc || '#0A0C10', align:'c', va:'m', lh:1, caps:false}); }
    else if (n === 'ring' && s < 20) { C.ell(x, y - 4, s * 1.9, s * 1.9, {line:AC, lw:1.4}); C.text(x, y - 4, s * 1.9, s * 1.9, String(k + 1), {font:'head', size:s * 0.9, wt:700, color:AC, align:'c', va:'m', lh:1, caps:false}); }
    else C.text(x, y, 60, s * 1.2, pad2(k + 1), {font:'head', size:s, wt:800, color:AC, lh:1, caps:false});
  }
  /* the background scene stays quiet so the text leads: everything the scene draws is faded by the template's calm factor */
  function back(C, R, k){
    var n = C.els.length, q = c.calm != null ? c.calm : 0.5;
    try { c.scene(C, R, k || 0, U); } catch (e) {}
    for (var i = n; i < C.els.length; i++) {
      var e = C.els[i];
      if (e.grad && e.grad.stops) e.grad = {type:e.grad.type, ang:e.grad.ang, stops:e.grad.stops.map(function(s){ return [s[0], s[1], (s[2] != null ? s[2] : 1) * q]; })};
      else if (e.fill) e.op = (e.op != null ? e.op : 1) * q;
      if (e.line || e.stroke) e.lop = (e.lop != null ? e.lop : 1) * q;
    }
  }
  function brand(C, x, y){
    var f = C.cover; if (!f.company) return;
    C.rect(x, y + 4, 14, 14, {fill:AC, r:Math.min(R0, 7)});
    C.text(x + 26, y, 420, 22, f.company, {font:'head', size:13.5, wt:700, color:TX, va:'m', lh:1, caps:CAPS, ls:CAPS ? 0.1 : 0, ed:C.i === 0 ? {f:'tf-company'} : null, maxLines:1});
  }
  function foot(C){
    var M = 72, y = H - 38, f = C.cover;
    C.rect(M, y + 2, 12, 12, {fill:AC, r:Math.min(R0, 6)});
    C.text(M + 24, y, 500, 16, f.company || f.website || '', {font:'head', size:12, wt:600, color:TX, lh:1, va:'m'});
    var e = C.rect(W - M - 62, y - 7, 62, 30, {fill:'#FFFFFF', op:0.1, line:c.rim || '#FFFFFF', lop:0.4, lw:1, r:Math.min(R0, 15)}); e.frost = 0;
    C.text(W - M - 62, y - 7, 62, 30, pad2(C.i + 1), {font:'head', size:12.5, wt:700, color:TX, align:'c', va:'m', lh:1});
  }
  function bg(C, kind){
    C.bg({fill:BG});
    if (kind === 'cover' || kind === 'closing' || kind === 'quote' || (C.o && C.o.fn)) return;
    back(C, {x:72, y:170, w:1136, h:450}, C.i || 0);
  }
  function blockH(C, t, w, F){ return (t.h ? F.hh + 6 : 0) + (t.d ? C.measureText(t.d, w, {font:'body', max:F.ds, min:F.ds, lh:1.36}).h : 0); }
  /* ---------- bodies made of several glass boxes ---------- */
  function cols(C, Z, o){
    var it = C.it.slice(0, 4), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
    var g = 20, off = c.stag === false ? 0 : Math.min(24, Z.h * 0.06), cw = (Z.w - g * (n - 1)) / n, pad = 24, tw = cw - pad * 2, top = 50;
    var F = K.fitHD(C, it, tw, 54, Z.h - off - top - pad * 2, {hMax:20, dMax:15, hMin:13, dMin:10.5});
    var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
    var ch = Math.min(Z.h - off, top + bmx + pad * 2 + 8), y0 = Z.y + Math.max(0, (Z.h - off - ch) * 0.3);
    it.forEach(function(t, k){
      var x = Z.x + k * (cw + g), y = y0 + (k % 2 ? off : 0);
      glass(C, x, y, cw, ch, R0);
      C.text(x + pad, y + pad - 2, 80, 30, pad2(k + 1), {font:'head', size:24, wt:800, color:AC, lh:1, caps:false});
      K.hd(C, x + pad, y + pad + top - 8, tw, ch - top - pad * 2 + 8, t, F, {tc:TX, mc:MU});
    });
  }
  function rows(C, Z, o){
    var it = C.it.slice(0, o.max || 7), n = it.length; if (!n) return;
    var g = 12, rh = Math.min((Z.h - g * (n - 1)) / n, 112), nw = 70, pad = 24, tw = Z.w - nw - pad * 2;
    var F = K.fitHD(C, it, tw, 30, rh - 20, {hMax:19, dMax:it.some(function(t){ return t.h; }) ? 14.5 : 19, hMin:12.5, dMin:10.5});
    var tot = rh * n + g * (n - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
    it.forEach(function(t, k){
      var y = y0 + k * (rh + g);
      glass(C, Z.x, y, Z.w, rh, R0);
      C.text(Z.x + pad, y, nw - 10, rh, pad2(k + 1), {font:'head', size:Math.min(26, rh * 0.34), wt:800, color:AC, va:'m', lh:1, caps:false});
      K.hd(C, Z.x + pad + nw, y + Math.max(8, (rh - blockH(C, t, tw, F)) / 2), tw, rh - 10, t, F, {tc:TX, mc:MU});
    });
  }
  function grid(C, Z, o){
    var it = C.it.slice(0, o.max || 10), n = it.length; if (!n) return;
    var cl = o.cols || (n <= 3 ? n : (n === 4 ? 2 : (n <= 6 ? 3 : (n <= 8 ? 4 : 5)))); if (Z.w < 720) cl = Math.min(cl, 2);
    var rw = Math.ceil(n / cl), g = 16, cw = (Z.w - g * (cl - 1)) / cl, ch = (Z.h - g * (rw - 1)) / rw, pad = 20, tw = cw - pad * 2;
    var F = K.fitHD(C, it, tw, 50, ch - pad * 2 - 30, {hMax:18.5, dMax:14.5, hMin:12, dMin:10.5});
    var bmx = 0; it.forEach(function(t){ bmx = Math.max(bmx, blockH(C, t, tw, F)); });
    var ch2 = Math.min(ch, bmx + pad * 2 + 36), tot = ch2 * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
    it.forEach(function(t, k){
      var r = Math.floor(k / cl), cc = k % cl, x = Z.x + cc * (cw + g), y = y0 + r * (ch2 + g);
      glass(C, x, y, cw, ch2, Math.min(R0, 20));
      num(C, x + pad, y + pad - 1, k, 14);
      K.hd(C, x + pad, y + pad + 28, tw, ch2 - pad * 2 - 28, t, F, {tc:TX, mc:MU});
    });
  }
  function sv(t){ return t.h || String(t.d).split(' ')[0]; }
  function sl(t){ return t.h ? t.d : String(t.d).split(' ').slice(1).join(' '); }
  function statCards(C, Z, o){
    var st = C.it.slice(0, 4), n = st.length; if (!n) return;
    var cl = Z.w < 760 ? (n > 2 ? 2 : 1) : n, rw = Math.ceil(n / cl), g = 20, cw = (Z.w - g * (cl - 1)) / cl, ch = Math.min((Z.h - g * (rw - 1)) / rw, 300), pad = 26, tw = cw - pad * 2, vals = st.map(sv), labs = st.map(sl);
    var vs = C.groupFit(vals, tw, ch * 0.4, {font:'head', max:64, min:22, maxLines:1});
    var ls = C.groupFit(labs, tw, 50, {font:'head', max:18, min:12.5, maxLines:2});
    var ds = C.groupFit(st.map(function(t){ return t.x || ''; }), tw, ch - vs * 1.1 - 110, {font:'body', max:14, min:10.5, lh:1.4});
    var tot = ch * rw + g * (rw - 1), y0 = Z.y + Math.max(0, (Z.h - tot) * 0.3);
    st.forEach(function(t, k){
      var r = Math.floor(k / cl), cc = k % cl, x = Z.x + cc * (cw + g), y = y0 + r * (ch + g);
      glass(C, x, y, cw, ch, R0);
      C.text(x + pad, y + pad, tw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:AC, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.rect(x + pad, y + pad + vs * 1.1 + 10, 40, 3, {fill:TX});
      var le = C.text(x + pad, y + pad + vs * 1.1 + 26, tw, 50, labs[k], {font:'head', size:ls, wt:700, color:TX, lh:1.15, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:2});
      if (t.x) C.text(x + pad, y + pad + vs * 1.1 + 34 + le._h, tw, ch - (pad * 2 + vs * 1.1 + 34 + le._h), t.x, {font:'body', size:ds, color:MU, lh:1.4, ed:{f:'x-part', i:t.i, p:2}});
    });
  }
  function steps(C, Z, o){
    var it = C.it.slice(0, 6), n = it.length; if (n < 2 || Z.w < 760) return rows(C, Z, o);
    var g = 14, cw = (Z.w - g * (n - 1)) / n, pad = 18, tw = cw - pad * 2, st = c.stag === false ? 0 : Math.min(26, Z.h * 0.32 / (n - 1)), ch = Math.min(Z.h - st * (n - 1), 330);
    var hs = C.groupFit(it.map(function(t){ return t.h || ''; }), tw, 34, {font:'head', max:26, min:13, maxLines:1});
    var ds = C.groupFit(it.map(function(t){ return t.d; }), tw, ch - 84, {font:'body', max:14.5, min:10.5, lh:1.4});
    if (!st) C.rect(Z.x, Z.y + 20, Z.w, 2, {fill:AC});
    it.forEach(function(t, k){
      var x = Z.x + k * (cw + g), y = Z.y + k * st + (st ? 0 : 44);
      if (!st) C.ell(x + pad - 4, Z.y + 12, 18, 18, {fill:AC});
      glass(C, x, y, cw, ch - (st ? 0 : 44), R0);
      C.text(x + pad, y + pad, tw, 34, t.h || pad2(k + 1), {font:'head', size:hs, wt:800, color:AC, lh:1.05, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
      C.rect(x + pad, y + pad + 42, 34, 3, {fill:TX});
      C.text(x + pad, y + pad + 58, tw, ch - (st ? 0 : 44) - pad * 2 - 58, t.d, {font:'body', size:ds, color:TX, lh:1.4, ed:{f:'x-part', i:t.i, p:1}});
    });
  }
  function statement(C, Z, o){
    var it = C.it[0]; if (!it) return;
    glass(C, Z.x, Z.y, Z.w, Z.h, R0);
    var pad = 46, y = Z.y + pad, w = Z.w - pad * 2;
    mark(C, Z.x + pad, y); y += 28;
    if (it.h) { var he = C.text(Z.x + pad, y, w, 70, it.h, {font:'head', fit:{max:30, min:16}, wt:800, color:AC, lh:1.12, caps:CAPS, ls:LS, ed:{f:'x-part', i:it.i, p:0}, maxLines:2}); y += he._h + 14; }
    C.text(Z.x + pad, y, w, Z.y + Z.h - pad - y, it.d, {font:'body', fit:{max:it.h ? 24 : 29, min:14}, color:TX, lh:1.5, ed:it.h ? {f:'x-part', i:it.i, p:1} : {f:'content-plain', i:it.i}});
  }
  /* ---------- one glass panel that holds the whole slide: any side or corner, over the scene or over a photo ---------- */
  var RECTS = {l:[56, 56, 600, 596], r:[624, 56, 600, 596], t:[56, 48, 1168, 336], b:[56, 320, 1168, 332], c:[190, 76, 900, 556], tl:[56, 56, 680, 420], tr:[544, 56, 680, 420], bl:[56, 232, 680, 420], br:[544, 232, 680, 420]};
  function place(C, o){
    var ph = o.ph, a = RECTS[o.pos || 'l'] || RECTS.l, R = {x:a[0], y:a[1], w:a[2], h:a[3]};
    if (ph === 'left') R = {x:470, y:64, w:754, h:580}; else if (ph === 'right') R = {x:56, y:64, w:754, h:580}; else if (ph === 'top') R = {x:90, y:230, w:1100, h:422}; else if (ph === 'bottom') R = {x:90, y:56, w:1100, h:422};
    if (ph === 'full') D.imgBox(C, 0, 0, W, H, {r:0});
    else {
      back(C, R, (C.i || 0) + (o.pos === 'r' || o.pos === 'tr' ? 1 : 0));
      if (ph === 'left') D.imgBox(C, 0, 0, 600, H, {r:0}); else if (ph === 'right') D.imgBox(C, 680, 0, 600, H, {r:0}); else if (ph === 'top') D.imgBox(C, 0, 0, W, 350, {r:0}); else if (ph === 'bottom') D.imgBox(C, 0, 370, W, 350, {r:0});
    }
    glass(C, R.x, R.y, R.w, R.h, Math.min(R0 * 1.2, 34), {veil:ph ? (c.clearPh != null ? c.clearPh : 0.3) : null});
    return R;
  }
  function listIn(C, Z){
    var it = C.it.slice(0, 8), n = it.length; if (!n) return;
    var wide = Z.w > 860, cl = wide ? (n <= 4 ? n : Math.ceil(n / 2)) : (n > 4 && Z.w > 520 ? 2 : 1), rw = Math.ceil(n / cl), gx = 28, cw = (Z.w - gx * (cl - 1)) / cl, ch = Math.min(Z.h / rw, cl === 1 ? 104 : 190);
    if (C.kind === 'stats') {
      var vals = it.map(sv), labs = it.map(sl), vs = C.groupFit(vals, cw, ch * 0.5, {font:'head', max:54, min:20, maxLines:1}), ls = C.groupFit(labs, cw, Math.max(24, ch - vs * 1.15 - 10), {font:'body', max:15, min:11, lh:1.3, maxLines:3});
      it.forEach(function(t, k){
        var x = Z.x + (k % cl) * (cw + gx), y = Z.y + Math.floor(k / cl) * ch;
        C.text(x, y, cw, vs * 1.1, vals[k], {font:'head', size:vs, wt:800, color:AC, lh:1, caps:false, ed:t.h ? {f:'x-part', i:t.i, p:0} : null, maxLines:1});
        C.text(x, y + vs * 1.1 + 4, cw, Math.max(24, ch - vs * 1.15 - 10), labs[k], {font:'body', size:ls, wt:500, color:TX, lh:1.3, ed:t.h ? {f:'x-part', i:t.i, p:1} : null, maxLines:3});
      });
      return;
    }
    var nw = 44, tw = cw - nw, F = K.fitHD(C, it, tw, 30, ch - 14, {hMax:18, dMax:it.some(function(t){ return t.h; }) ? 14 : 17, hMin:12, dMin:10.5});
    it.forEach(function(t, k){
      var cc = k % cl, r = Math.floor(k / cl), x = Z.x + cc * (cw + gx), y = Z.y + r * ch, oy = cl === 1 ? Math.max(8, (ch - blockH(C, t, tw, F)) / 2) : 8;
      if (r && cl === 1) C.rect(x, y - 1, cw, 1, {fill:TX, op:0.22});
      num(C, x, y + oy + 2, k, 13);
      K.hd(C, x + nw, y + oy, tw, ch - 12, t, F, {tc:TX, mc:MU});
    });
  }
  function scene(C, o){
    o = o || C.o || {};
    var R = place(C, o), pad = Math.min(40, Math.max(28, R.w * 0.05)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, wide = R.w > 860;
    mark(C, x, y); y += 22;
    var te = C.text(x, y, wide ? w * 0.7 : w, 100, C.s.title || '', {font:'head', fit:{max:wide ? 36 : 31, min:19}, wt:700, color:TX, lh:1.1, caps:CAPS, ls:LS, ed:{f:'title'}, maxLines:2}); y += te._h + 6;
    if (C.s.subtitle) { var se = C.text(x, y, wide ? w * 0.7 : w, 44, C.s.subtitle, {font:'body', fit:{max:15, min:12}, color:MU, lh:1.35, ed:{f:'subtitle'}, maxLines:2}); y += se._h + 6; }
    y += 12;
    if (C.s.conclusion && bot - y > 230) { bot -= 50; C.rect(x, bot + 12, w, 1, {fill:TX, op:0.25}); C.text(x, bot + 18, w, 32, C.s.conclusion, {font:'body', fit:{max:14, min:11}, wt:600, color:AC, va:'m', lh:1.25, ed:{f:'x-conc'}, maxLines:2}); }
    listIn(C, {x:x, y:y, w:w, h:bot - y});
  }
  function quote(C, v){
    var o = C.o || {}, R = place(C, o), by = C.it[0], pad = Math.min(54, Math.max(34, R.w * 0.06)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, mid = (o.pos || 'c') === 'c' || o.pos === 't' || o.pos === 'b';
    C.text(mid ? x + w / 2 - 40 : x - 4, y - 16, 80, 80, '“', {font:'head', size:92, wt:800, color:AC, lh:1, align:mid ? 'c' : 'l', caps:false, ls:0}); y += 62;
    C.text(x, y, w, R.y + R.h - pad - y - (by ? 46 : 0), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? 38 : 31, min:18}, wt:600, color:TX, lh:1.22, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:6});
    if (by) { var bt = by.h ? by.h + ' — ' + by.d : by.d; C.text(x, R.y + R.h - pad - 26, w, 26, bt, {font:'body', size:14, wt:600, color:AC, lh:1.1, align:mid ? 'c' : 'l', va:'m', ed:{f:'content-plain', i:by.i}, maxLines:1}); }
  }
  function cover(C, v){
    var o = C.o || {}, f = C.cover, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, bot = R.y + R.h - pad, mid = o.pos === 'c';
    if (f.company) { if (mid) C.text(x, y, w, 22, f.company, {font:'head', size:13.5, wt:700, color:AC, align:'c', va:'m', lh:1, caps:true, ls:0.14, ed:{f:'tf-company'}, maxLines:1}); else brand(C, x, y); }
    var who = [f.presenter, f.designation].filter(Boolean).join('  ·  '), th = bot - y - 40 - (who ? 50 : 0) - (C.s.subtitle ? 70 : 0);
    C.text(x, y + 40, w, Math.max(80, th), C.s.title || '', {font:'head', fit:{max:R.w > 860 ? (c.big ? 86 : 62) : (c.big ? 60 : 50), min:26}, wt:800, color:TX, lh:1.06, caps:CAPS, ls:LS, align:mid ? 'c' : 'l', va:'m', ed:{f:'title'}, maxLines:R.h < 360 ? 2 : 4});
    if (C.s.subtitle) C.text(x, y + 48 + Math.max(80, th), w, 62, C.s.subtitle, {font:'body', fit:{max:18, min:12.5}, color:MU, lh:1.4, align:mid ? 'c' : 'l', ed:{f:'subtitle'}, maxLines:3});
    if (who) { if (!mid) C.rect(x, bot - 30, 34, 3, {fill:AC}); C.text(x + (mid ? 0 : 46), bot - 40, w - (mid ? 0 : 46), 24, who, {font:'head', size:14, wt:700, color:TX, lh:1, va:'m', align:mid ? 'c' : 'l', ed:{f:'tf-presenter'}, maxLines:1}); }
  }
  function closing(C, v){
    var o = C.o || {}, R = place(C, o), pad = Math.min(48, Math.max(32, R.w * 0.055)), x = R.x + pad, w = R.w - pad * 2, y = R.y + pad, wide = R.w > 860, cs = D.contacts(C), tw = wide ? w * 0.52 : w;
    mark(C, x, y); y += 24;
    var te = C.text(x, y, tw, wide ? R.h - pad * 2 - 110 : 150, C.s.title || 'Thank You', {font:'head', fit:{max:wide ? 66 : 54, min:28}, wt:800, color:TX, lh:1.06, caps:CAPS, ls:LS, va:wide ? 'm' : 't', ed:{f:'title'}, maxLines:2});
    var sy = wide ? R.y + R.h - pad - 70 : y + te._h + 10;
    if (C.s.subtitle) C.text(x, sy, tw, 64, C.s.subtitle, {font:'body', fit:{max:17, min:12.5}, color:MU, lh:1.4, ed:{f:'subtitle'}, maxLines:2});
    var cx = wide ? x + w * 0.58 : x, cw = wide ? w * 0.42 : w, n = cs.length, rh = Math.min(56, (wide ? R.h - pad * 2 : R.y + R.h - pad - (sy + 80)) / Math.max(1, n)), cy = wide ? R.y + (R.h - rh * n) / 2 : R.y + R.h - pad - rh * n;
    cs.forEach(function(q, k){
      var yy = cy + k * rh;
      C.icon(cx, yy + rh / 2 - 11, 22, q.icon, AC);
      C.text(cx + 38, yy, cw - 38, rh, q.val, {font:'body', fit:{max:17, min:12}, wt:600, color:TX, va:'m', ed:q.i >= 0 ? {f:'x-part', i:q.i, p:1} : null, maxLines:1});
      if (k) C.rect(cx, yy, cw, 1, {fill:TX, op:0.2});
    });
  }
  function sc(fn, few){
    var a = [
      V('g1', 'Glass panel left', {fn:fn, pos:'l'}), V('g2', 'Glass panel right', {fn:fn, pos:'r'}), V('g3', 'Glass panel centre', {fn:fn, pos:'c'}),
      V('g4', 'Glass panel on top', {fn:fn, pos:'t'}), V('g5', 'Glass panel at the bottom', {fn:fn, pos:'b'}),
      V('f1', 'Full photo + glass left', {fn:fn, img:'photo', ph:'full', pos:'l'}), V('f2', 'Full photo + glass right', {fn:fn, img:'photo', ph:'full', pos:'r'}),
      V('f3', 'Full photo + glass at the bottom', {fn:fn, img:'photo', ph:'full', pos:'b'}), V('f4', 'Full photo + glass on top', {fn:fn, img:'photo', ph:'full', pos:'t'}),
      V('f5', 'Full photo + glass centre', {fn:fn, img:'photo', ph:'full', pos:'c'}),
      V('h1', 'Photo left half + glass', {fn:fn, img:'photo', ph:'left'}), V('h2', 'Photo right half + glass', {fn:fn, img:'photo', ph:'right'}),
      V('h3', 'Photo on top + glass below', {fn:fn, img:'photo', ph:'top'}), V('h4', 'Glass above + photo below', {fn:fn, img:'photo', ph:'bottom'})
    ];
    if (few) a = a.concat([
      V('c1', 'Full photo + glass top-left', {fn:fn, img:'photo', ph:'full', pos:'tl'}), V('c2', 'Full photo + glass top-right', {fn:fn, img:'photo', ph:'full', pos:'tr'}),
      V('c3', 'Full photo + glass bottom-left', {fn:fn, img:'photo', ph:'full', pos:'bl'}), V('c4', 'Full photo + glass bottom-right', {fn:fn, img:'photo', ph:'full', pos:'br'}),
      V('c5', 'Glass panel top-left', {fn:fn, pos:'tl'}), V('c6', 'Glass panel bottom-right', {fn:fn, pos:'br'})
    ]);
    return a;
  }
  /* cover / closing / quote: the same positions; the template's favourite one comes first */
  function own(first){
    var all = sc(null, true).map(function(v){ var o = {}; for (var k in v.o) if (k !== 'fn') o[k] = v.o[k]; return [v.lbl, o]; }), a = [], b = [];
    all.forEach(function(p){ ((p[1].pos || '') === first && !p[1].ph ? a : b).push(p); });
    var i = -1; b.some(function(p, k){ if (p[1].ph === 'full' && p[1].pos === first) { i = k; return true; } return false; });
    if (i >= 0) a.push(b.splice(i, 1)[0]);
    return a.concat(b).map(function(p, k){ return V(k < 2 ? 'v' + (k + 1) : 'g' + (k + 1), p[0], p[1]); });
  }
  var B3 = c.b3 === 'rows' ? rows : cols, B3b = c.b3 === 'rows' ? cols : rows, n3 = c.b3 === 'rows' ? ['Glass rows', 'Glass columns'] : ['Glass columns', 'Glass rows'];
  D.register({
    id:c.id, name:c.name, tag:c.tag, desc:c.desc, frost:true, fonts:c.fonts,
    pal:{bg:BG, text:TX, title:TX, muted:MU, accent:AC, accent2:c.ac2 || MU, card:c.card || '#161A22', line:c.line || '#2B313D', onAccent:c.onAc || '#0A0C10'},
    s:S, bg:bg, cover:cover, closing:closing, quote:quote,
    variants:{
      cover:own(c.cover || 'l'), closing:own(c.close || 'c'), quote:own(c.quote || 'c'),
      agenda:[V('v1', 'Glass rows', {body:rows}), V('n2', 'Glass grid', {body:grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene)),
      text:[V('v1', 'Glass statement', {body:statement}), V('v2', 'Statement + image right', {body:statement, img:'right'}), V('v3', 'Image left + statement', {body:statement, img:'left'})].concat(sc(scene, true)),
      p2:[V('v1', n3[0], {body:B3}), V('n2', n3[1], {body:B3b}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
      p3:[V('v1', n3[0], {body:B3}), V('n2', n3[1], {body:B3b}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene, true)),
      p4:[V('v1', c.b4 === 'grid' ? 'Glass grid' : 'Glass columns', {body:c.b4 === 'grid' ? grid : cols}), V('n2', 'Glass rows', {body:rows}), V('n3', c.b4 === 'grid' ? 'Glass columns' : 'Glass grid', {body:c.b4 === 'grid' ? cols : grid}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
      p56:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass grid', {body:grid, img:'left', cols:2})].concat(sc(scene)),
      p78:[V('v1', 'Glass grid', {body:grid}), V('n2', 'Glass rows', {body:rows, max:8}), V('v2', 'Glass grid + image right', {body:grid, img:'right', cols:2})].concat(sc(scene)),
      stats:[V('v1', 'Glass numbers', {body:statCards}), V('n2', 'Progress rings', {body:'statRings', track:c.line || '#2B313D'}), V('n3', 'Progress bars', {body:'statBars'}), V('v2', 'Numbers + image right', {body:statCards, img:'right'}), V('v3', 'Image left + numbers', {body:statCards, img:'left'})].concat(sc(scene, true)),
      timeline:[V('v1', 'Glass steps', {body:steps}), V('n2', 'Glass rows', {body:rows}), V('v2', 'Glass rows + image right', {body:rows, img:'right'}), V('v3', 'Image left + glass rows', {body:rows, img:'left'})].concat(sc(scene))
    }
  });
};
})();
