<?php
/**
 * Code Snippets export — DS Perf - wp-json Fetch Concurrency Limiter (503 Mitigation)
 *
 * Snippet ID : 807
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-121
 * Modified   : 2026-09-09 02:40:22
 * Load order : 127 of 144
 */
/**
 * Mitigates the frequent "503 Service Unavailable - The server is
 * temporarily busy" errors on this hosting plan. Root cause: the
 * hosting has a low concurrent PHP-worker/connection ceiling (tested
 * directly - roughly 4 concurrent requests succeed, anything beyond
 * that gets rejected with 503 by the host's edge). The AI Studio pages
 * already fire several REST calls in parallel on every load (my-cap,
 * my-videos, my-audio, my-saved-images, my-ppts, notifications, etc.),
 * and any active generation adds its own polling on top - it's very
 * easy to exceed that ceiling, especially with more than one AI Studio
 * tab open.
 *
 * This is purely additive and touches no existing snippet: it patches
 * window.fetch (installed as early as possible, in wp_head) so that
 * same-origin /wp-json/ calls are queued client-side to a max of 3
 * in flight at once, and any 503 that still slips through is retried
 * automatically with a short backoff before the caller ever sees it.
 * Existing code's own fetch(...) calls are unaffected in behavior -
 * they just resolve slightly later instead of getting rejected.
 *
 * This does not raise the server's real capacity - if the site gets
 * genuinely busy (real traffic, not just this browser), 503s can still
 * happen after retries are exhausted. The durable fix for that is a
 * hosting plan with a higher PHP worker/connection limit.
 */
add_action('wp_head', function () {
    ?>
    <style>
    /* Spinner shown over a slide's image area while an imported image is
       being fetched and re-encoded, so the slide never looks frozen. */
    .dsp-vp-imgzone.ds-img-busy::before{
        content:'';position:absolute;left:50%;top:50%;z-index:6;
        width:34px;height:34px;margin:-30px 0 0 -17px;
        border:3px solid rgba(255,255,255,.25);border-top-color:#4a9eff;
        border-radius:50%;animation:dsImgSpin .8s linear infinite;
        pointer-events:none;
    }
    .dsp-vp-imgzone.ds-img-busy::after{
        content:'Optimizing image...';
        position:absolute;left:0;right:0;top:50%;z-index:6;
        margin-top:16px;text-align:center;pointer-events:none;
        color:#e8eef7;letter-spacing:.02em;
        font:600 12px/1.4 -apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;
        text-shadow:0 1px 3px rgba(0,0,0,.6);
    }
    .dsp-vp-imgzone.ds-img-busy{
        background:rgba(10,14,22,.45);
    }
    @keyframes dsImgSpin{to{transform:rotate(360deg)}}
    </style>
    <script>
    (function(){
        if (window.__dsFetchLimiterInstalled) return;
        window.__dsFetchLimiterInstalled = true;

        var MAX_CONCURRENT = 3;
        var MAX_RETRIES = 3;
        var REQUEST_TIMEOUT_MS = 45000;   // 16.3: nothing may hold a slot forever
        /* Long-running builders (PPTX / video / audio / deck combine) can
           legitimately run for minutes; the 45s cap was killing them
           mid-build and showing "Request timed out after 45s". */
        var SLOW_TIMEOUT_MS = 300000;
        var SLOW_RE = /(build-pptx|combine-full-deck|generate-ppt|mkt-slide-images|fetch-slide-image|download-video|download-audio|blog-generate|save-ppt|load-ppt|ppt-img-put|create-share-link|ai-preview|ai-confirm|preserve-|match-templates|inject-template|merge-real-templates|render-template-preview|add-slide|generate-image|ai-image)/;
        var active = 0;
        var queue = [];
        var originalFetch = window.fetch.bind(window);

        /* 16.3: visible counters, so a stuck queue can be diagnosed from the
           console instead of guessed at. */
        window.__dsFetchLimiterStats = function () {
            return { active: active, queued: queue.length, max: MAX_CONCURRENT };
        };

        function isOwnRest(url) {
            try {
                var u = new URL(url, location.href);
                return u.origin === location.origin && u.pathname.indexOf('/wp-json/') === 0;
            } catch (e) { return false; }
        }

        function runNext() {
            /* Fill every free slot, not just one. */
            while (active < MAX_CONCURRENT && queue.length > 0) {
                var job = queue.shift();
                active++;
                attempt(job, 0);
            }
        }

        /* Release a job exactly once, whatever happens to it. A job that has
           already settled can never decrement `active` a second time, and a
           job that never settles is released by its own timeout. */
        function settle(job, fn, arg) {
            if (job.settled) { return; }
            job.settled = true;
            if (job.timer) { clearTimeout(job.timer); job.timer = null; }
            active--;
            try { fn(arg); } finally { runNext(); }
        }

        function attempt(job, retryCount) {
            if (job.settled) { return; }

            if (!job.timer) {
                var _to = job.timeoutMs || REQUEST_TIMEOUT_MS;
                job.timer = setTimeout(function () {
                    /* 17.2: actually cancel the request. Before, the upload kept
                       running after the caller was told it failed, the caller
                       retried, and several multi-MB saves ran in parallel. */
                    if (job.abort) { try { job.abort.abort(); } catch (e) {} }
                    settle(job, job.reject, new Error('Request timed out after ' +
                        Math.round(_to / 1000) + 's'));
                }, _to);
            }

            var _init = job.init;
            if (typeof AbortController !== 'undefined' && !(_init && _init.signal) &&
                typeof job.input === 'string') {
                if (!job.abort) { job.abort = new AbortController(); }
                _init = {};
                for (var _k in (job.init || {})) { _init[_k] = job.init[_k]; }
                _init.signal = job.abort.signal;
            }
            originalFetch(job.input, _init).then(function (res) {
                if (job.settled) { return; }
                if (res.status === 503 && retryCount < MAX_RETRIES) {
                    var delay = 600 * Math.pow(2, retryCount);
                    setTimeout(function () { attempt(job, retryCount + 1); }, delay);
                    return;   // slot stays held, but job.timer still guards it
                }
                settle(job, job.resolve, res);
            }).catch(function (err) {
                if (job.settled) { return; }
                settle(job, job.reject, err);
            });
        }

        window.fetch = function (input, init) {
            var url = (typeof input === 'string') ? input : (input && input.url);
            if (!url || !isOwnRest(url)) {
                return originalFetch(input, init);
            }
            return new Promise(function (resolve, reject) {
                queue.push({ input: input, init: init, resolve: resolve,
                             reject: reject, settled: false, timer: null,
                             timeoutMs: SLOW_RE.test(url) ? SLOW_TIMEOUT_MS : REQUEST_TIMEOUT_MS });
                runNext();
            });
        };

        /* ---- Compress inline slide images AT IMPORT TIME ----
           The image generator and the marketplace return ~5000px PNGs, which
           become 40-60MB base64 strings once they land in a slide. That made
           the slide panel, Save and Download all crawl. Every image response
           is re-encoded ONCE here, as it arrives, to a 1600px JPEG. The image
           is still fetched only once - this never re-requests it, so it does
           not touch anyone's daily marketplace download count. */
        var IMG_URL_RE = /(fetch-image|slide-image|slide-images|generate-image|ai-image|image-proxy)/;
        var IMG_MIN_LEN = 400000;
        var IMG_MAX_PX  = 1920;
        var IMG_QUALITY = 0.92;

        /* Re-encode one data URL. Quality is deliberately high: a slide is
           at most ~1920px on screen and in the PPTX, so capping the long edge
           there is invisible, and q0.92 JPEG is visually lossless for photos.
           An image that carries transparency is kept as PNG (just resized),
           because JPEG has no alpha and would black out the transparent parts. */
        function dsHasAlpha(cv, cw, ch) {
            try {
                var px = cv.getContext('2d').getImageData(0, 0, cw, ch).data;
                for (var i = 3; i < px.length; i += 40) {
                    if (px[i] < 250) { return true; }
                }
            } catch (e) { return true; }
            return false;
        }

        function dsShrinkDataUrl(d) {
            return new Promise(function (res) {
                if (typeof d !== 'string' || d.length < IMG_MIN_LEN ||
                    d.indexOf('data:image') !== 0) { res(d); return; }
                var im = new Image();
                im.onload = function () {
                    try {
                        var w = im.naturalWidth, h = im.naturalHeight;
                        var sc = Math.min(1, IMG_MAX_PX / Math.max(w, h));
                        var cw = Math.max(1, Math.round(w * sc));
                        var ch = Math.max(1, Math.round(h * sc));
                        var cv = document.createElement('canvas');
                        cv.width = cw; cv.height = ch;
                        var cx = cv.getContext('2d');
                        cx.imageSmoothingEnabled = true;
                        cx.imageSmoothingQuality = 'high';
                        cx.drawImage(im, 0, 0, cw, ch);
                        var isPng = d.indexOf('data:image/png') === 0;
                        var out = (isPng && dsHasAlpha(cv, cw, ch))
                            ? cv.toDataURL('image/png')
                            : cv.toDataURL('image/jpeg', IMG_QUALITY);
                        res(out && out.length < d.length ? out : d);
                    } catch (e) { res(d); }
                };
                im.onerror = function () { res(d); };
                im.src = d;
            });
        }

        /* Walk a parsed JSON body and shrink every data:image string in it. */
        function dsShrinkTree(node, depth, jobs) {
            if (!node || depth > 4) { return; }
            if (Array.isArray(node)) {
                node.forEach(function (val, i) {
                    if (typeof val === 'string') {
                        jobs.push(dsShrinkDataUrl(val).then(function (nv) { node[i] = nv; }));
                    } else { dsShrinkTree(val, depth + 1, jobs); }
                });
                return;
            }
            if (typeof node === 'object') {
                Object.keys(node).forEach(function (k) {
                    var val = node[k];
                    if (typeof val === 'string') {
                        jobs.push(dsShrinkDataUrl(val).then(function (nv) { node[k] = nv; }));
                    } else { dsShrinkTree(val, depth + 1, jobs); }
                });
            }
        }

        /* ---- "Optimizing image..." feedback -------------------------
           Fetching plus re-encoding takes a few seconds, and with an empty
           image area users assume the app has frozen. Mark the slide's image
           zones busy for as long as that runs. The interval re-applies the
           class because the editor re-renders the slide while it works. */
        var imgBusy = 0, imgBusyTimer = null;

        function dsPaintImgBusy() {
            var zones = document.querySelectorAll('.dsp-vp-imgzone');
            for (var i = 0; i < zones.length; i++) {
                if (zones[i].getBoundingClientRect().width < 150) { continue; }
                /* Skip the layout-preview cards in the right panel - the
                   spinner belongs on the slide being edited, not on the menu. */
                if (zones[i].closest && zones[i].closest('.vp-card')) { continue; }
                zones[i].classList.add('ds-img-busy');
            }
        }
        function dsClearImgBusy() {
            var zones = document.querySelectorAll('.ds-img-busy');
            for (var i = 0; i < zones.length; i++) {
                zones[i].classList.remove('ds-img-busy');
            }
        }
        function dsImgBusyStart() {
            imgBusy++;
            if (!imgBusyTimer) {
                dsPaintImgBusy();
                imgBusyTimer = setInterval(dsPaintImgBusy, 400);
            }
        }
        function dsImgBusyEnd() {
            imgBusy = Math.max(0, imgBusy - 1);
            if (imgBusy === 0 && imgBusyTimer) {
                clearInterval(imgBusyTimer); imgBusyTimer = null;
                dsClearImgBusy();
            }
        }

        var queuedFetch = window.fetch;
        window.fetch = function (input, init) {
            var url = (typeof input === 'string') ? input : (input && input.url);
            var p = queuedFetch(input, init);
            if (!url || !IMG_URL_RE.test(url)) { return p; }
            dsImgBusyStart();
            return p.then(function (res) {
                if (!res || !res.ok) { return res; }
                return res.clone().text().then(function (txt) {
                    if (txt.indexOf('data:image') < 0) { return res; }
                    var j;
                    try { j = JSON.parse(txt); } catch (e) { return res; }
                    var jobs = [];
                    dsShrinkTree(j, 0, jobs);
                    if (!jobs.length) { return res; }
                    return Promise.all(jobs).then(function () {
                        return new Response(JSON.stringify(j), {
                            status: res.status,
                            statusText: res.statusText,
                            headers: { 'Content-Type': 'application/json' }
                        });
                    });
                }).catch(function () { return res; });
            }).then(
                function (v) { dsImgBusyEnd(); return v; },
                function (e) { dsImgBusyEnd(); throw e; }
            );
        };
    })();
    </script>
    <?php
}, 1);
