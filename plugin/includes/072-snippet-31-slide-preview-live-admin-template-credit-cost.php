<?php
/**
 * Code Snippets export — Snippet 31 slide preview [Live Admin Template Credit Cost]
 *
 * Snippet ID : 758
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-072
 * Modified   : 2026-09-09 02:43:53
 * Load order : 072 of 144
 */
/**
 * Snippet 31 v4 — DS PPT Live Preview + Download
 * Priority 1004 (runs after Snippet 30 at 1003)
 *
 * REQUIRES in Snippet 30 ds30Generate() after lastPptxB64=d.pptx_b64; :
 *   window.lastPptxB64  = d.pptx_b64;
 *   window.lastFilename = d.filename || 'presentation.pptx';
 *   window.lastDs31Slots = d.preview_slots || [];
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/confirm-download', [
        'methods'             => 'POST',
        'callback'            => 'ds31_confirm_download',
        'permission_callback' => fn() => is_user_logged_in(),
    ]);
});

if ( ! function_exists('ds31_confirm_download') ) {
function ds31_confirm_download( WP_REST_Request $req ) {
    $uid     = get_current_user_id();
    $__ppt_opt_dl = get_option('ds_ppt_credit_settings');
    $cost    = ( is_array($__ppt_opt_dl) && isset($__ppt_opt_dl['template_cost']) && $__ppt_opt_dl['template_cost'] !== '' ) ? (int)$__ppt_opt_dl['template_cost'] : 5;
    $credits = (int) get_user_meta($uid, 'ds_ai_credits', true);
    if ( $credits < $cost ) {
        return rest_ensure_response(['error' => "You need {$cost} credits to download."]);
    }
    if ( function_exists('ds_deduct_credits') ) {
        ds_deduct_credits($uid, $cost);
    } else {
        update_user_meta($uid, 'ds_ai_credits', $credits - $cost);
    }

    // ── Log to credit history (so Account Dropdown "Credit History" tab shows this debit) ──
    $template_id = (int) ( $req->get_param('template_id') ?: 0 );
    $template_title = $template_id ? ( get_the_title($template_id) ?: 'Marketplace Template' ) : 'Marketplace Template';
    $ppt_log = get_user_meta($uid, 'ds_ppt_generations', true) ?: [];
    array_unshift($ppt_log, [
        'date'       => current_time('mysql'),
        'prompt'     => $template_title,
        'credits'    => $cost,
        'num_slides' => 0,
        'source'     => 'marketplace',
    ]);
    update_user_meta($uid, 'ds_ppt_generations', array_slice($ppt_log, 0, 100));

    return rest_ensure_response([
        'success'         => true,
        'credits_balance' => (int) get_user_meta($uid, 'ds_ai_credits', true),
    ]);
}
}

add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') &&
         ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;

    $nonce    = wp_create_nonce('wp_rest');
    $api_base = esc_js(rest_url('ds/v1'));
    $__ppt_opt_dl2 = get_option('ds_ppt_credit_settings');
    $tpl_dl_cost = ( is_array($__ppt_opt_dl2) && isset($__ppt_opt_dl2['template_cost']) && $__ppt_opt_dl2['template_cost'] !== '' ) ? (int)$__ppt_opt_dl2['template_cost'] : 5;
?>
<style>
/* ── Snippet 31 v4 ── */
#ds31-dl-bar {
    padding: 14px 18px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    flex-shrink: 0;
    gap: 12px;
}
.ds31-credit-note { font-size: 12px; color: #6b7280; }
.ds31-credit-note strong { color: #2563eb; font-weight: 700; }
#ds31-dl-btn {
    padding: 11px 24px;
    background: #2563eb; color: #fff;
    font-size: 13px; font-weight: 700;
    border: none; border-radius: 9px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 7px;
    transition: background .15s; white-space: nowrap;
}
#ds31-dl-btn:hover    { background: #1d4ed8; }
#ds31-dl-btn:disabled { background: #93c5fd; cursor: not-allowed; }
#ds31-another-btn {
    padding: 11px 18px;
    background: transparent; border: 1px solid #d1d5db; color: #374151;
    font-size: 13px; font-weight: 600; border-radius: 9px; cursor: pointer;
    font-family: inherit; transition: border-color .15s, color .15s;
    white-space: nowrap;
}
#ds31-another-btn:hover { border-color: #2563eb; color: #2563eb; }

/* Hide Snippet 30's footer — we provide our own */
.ds30-preview-footer { display: none !important; }
.ds30-preview-slots  { display: none !important; }

/* Live slide container */
#ds31-slide-wrap {
    width: 100%;
    background: #e8edf2;
    overflow: hidden;
    position: relative;
}
#ds31-slide-stage {
    width: 960px;
    min-height: 540px;
    transform-origin: top left;
    background: #fff;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    box-sizing: border-box;
    padding: 52px 60px 44px;
}

/* Success popup */
#ds31-success {
    position: fixed; inset: 0; z-index: 200000;
    background: rgba(0,0,0,.65); display: none;
    align-items: center; justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
#ds31-success.open { display: flex; }
.ds31-success-box {
    background: #fff; border-radius: 20px;
    padding: 44px 36px; max-width: 360px; width: 90%;
    text-align: center; box-shadow: 0 24px 60px rgba(0,0,0,.3);
}
.ds31-s-emoji { font-size: 54px; margin-bottom: 14px; }
.ds31-s-title { font-size: 22px; font-weight: 800; color: #111; margin-bottom: 8px; }
.ds31-s-sub   { font-size: 14px; color: #666; margin-bottom: 28px; line-height: 1.5; }
.ds31-s-ok {
    width: 100%; padding: 13px; background: #f59e0b; color: #fff;
    font-size: 14px; font-weight: 800; letter-spacing: .06em;
    text-transform: uppercase; border: none; border-radius: 12px;
    cursor: pointer; margin-bottom: 12px; transition: background .15s;
}
.ds31-s-ok:hover { background: #d97706; }
.ds31-s-another {
    background: none; border: none; color: #9ca3af;
    font-size: 13px; cursor: pointer; text-decoration: underline;
}
</style>

<div id="ds31-success">
    <div class="ds31-success-box">
        <div class="ds31-s-emoji">🎉</div>
        <div class="ds31-s-title">Download Complete!</div>
        <div class="ds31-s-sub">Your presentation is in your downloads folder.</div>
        <button class="ds31-s-ok" onclick="ds31SuccessOk()">AWESOME!</button>
        <button class="ds31-s-another" onclick="ds31SuccessAnother()">Generate another →</button>
    </div>
</div>

<script>
(function(){
'use strict';

var API31   = '<?php echo $api_base; ?>';
var NONCE31 = '<?php echo $nonce; ?>';
var TPL_DL_COST31 = <?php echo (int)$tpl_dl_cost; ?>;
var ds31Ready = false; // prevent double-inject

/* ── Helpers ── */
function esc(s) {
    return String(s||'')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function ds31Scale() {
    var wrap  = document.getElementById('ds31-slide-wrap');
    var stage = document.getElementById('ds31-slide-stage');
    if (!wrap || !stage) return;
    var w = wrap.offsetWidth || 760;
    var scale = w / 960;
    stage.style.transform = 'scale(' + scale + ')';
    wrap.style.height = Math.round(540 * scale) + 'px';
    wrap.style.overflow = 'hidden';
}

/* ── Build live HTML slide from slots ── */
function ds31BuildSlide(slots, templateTitle) {
    var title = '', subtitle = '', pairs = {}, extras = [];

    (slots || []).forEach(function(s) {
        var name = (s.slot || '').trim();
        var val  = (s.value || '').trim();
        if (!val) return;

        if (!title && /^(title|title header|main title)$/i.test(name)) {
            title = val; return;
        }
        if (!subtitle && /^(sub header|subtitle|sub title|subheading)$/i.test(name)) {
            subtitle = val; return;
        }

        var numMatch  = name.match(/(\d+)/);
        var isHeading = /heading|title|name/i.test(name);
        var isBody    = /explanation|description|body|detail|text/i.test(name);

        if (numMatch) {
            var n = numMatch[1];
            if (!pairs[n]) pairs[n] = {head:'', body:''};
            if (isHeading)           pairs[n].head = val;
            else if (isBody)         pairs[n].body = val;
            else if (!pairs[n].head) pairs[n].head = val;
            else                     pairs[n].body = val;
        } else {
            extras.push({head: name, body: val});
        }
    });

    if (!title) title = templateTitle || 'Your Presentation';

    var cards = [];
    Object.keys(pairs).sort(function(a,b){return +a-+b;}).forEach(function(n){
        var p = pairs[n];
        if (p.head || p.body) cards.push(p);
    });
    cards = cards.concat(extras);

    var COLORS = ['#2563eb','#059669','#d97706','#7c3aed','#dc2626','#0891b2','#be185d','#0d9488'];
    var cols = cards.length <= 2 ? (cards.length || 1) : cards.length <= 4 ? 2 : 3;
    var gridStyle = 'display:grid;grid-template-columns:repeat('+cols+',1fr);gap:14px;';

    var cardsHtml = cards.map(function(c, i) {
        var col = COLORS[i % COLORS.length];
        return '<div style="background:#f8fafc;border-radius:10px;padding:16px 14px;border-left:4px solid '+col+';box-sizing:border-box;">'
            + '<div style="font-size:11px;font-weight:700;color:'+col+';margin:0 0 5px;text-transform:uppercase;letter-spacing:.04em;">'+esc(c.head)+'</div>'
            + (c.body ? '<div style="font-size:11px;color:#374151;line-height:1.5;margin:0;">'+esc(c.body)+'</div>' : '')
            + '</div>';
    }).join('');

    return '<div style="font-size:32px;font-weight:800;color:#111827;margin:0 0 8px;line-height:1.15;">'+esc(title)+'</div>'
        + (subtitle ? '<div style="font-size:14px;color:#6b7280;margin:0 0 28px;line-height:1.5;">'+esc(subtitle)+'</div>' : '<div style="margin-bottom:28px;"></div>')
        + (cardsHtml ? '<div style="'+gridStyle+'">'+cardsHtml+'</div>' : '');
}

/* ── Main inject: replace modal body with live slide ── */
function ds31Inject() {
    if (ds31Ready) return;

    var modal = document.getElementById('ds30-preview-modal');
    if (!modal || !modal.classList.contains('open')) return;

    var box = modal.querySelector('.ds30-preview-box');
    if (!box) return;

    ds31Ready = true;

    /* Hide Select Theme bar */
    document.querySelectorAll('body > *').forEach(function(el) {
        if (el.id === 'ds31-success') return;
        if (el.id === 'ds30-preview-modal') return;
        if (el.id === 'ds30-screen') return;
        if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
        var rect = el.getBoundingClientRect();
        if (rect.top >= window.innerHeight * 0.5) {
            el.dataset.ds31v4 = el.style.display || '';
            el.style.display = 'none';
        }
    });

    /* Remove old dl-bar if any, add fresh one */
    var oldBar = document.getElementById('ds31-dl-bar');
    if (oldBar) oldBar.remove();

    var bar = document.createElement('div');
    bar.id = 'ds31-dl-bar';
    bar.innerHTML =
        '<button id="ds31-another-btn" onclick="ds31PickAnother()">&#8592; Pick another</button>'
        + '<span class="ds31-credit-note">&nbsp;&middot;&nbsp; <strong>'+TPL_DL_COST31+' credits</strong> on download</span>'
        + '<button id="ds31-dl-btn" onclick="ds31Download()">&#8681; Download PPTX</button>';

    /* Hide Snippet 30 footer, add ours */
    var footer = box.querySelector('.ds30-preview-footer');
    if (footer) footer.style.display = 'none';

    box.appendChild(bar);
}

/* ── Watch ds30ShowPreview — patch it directly ── */
function ds31PatchShowPreview() {
    if (typeof window.ds30ShowPreview !== 'function') return false;
    if (window.__ds31v4Patched) return true;
    window.__ds31v4Patched = true;

    var _orig = window.ds30ShowPreview;
    window.ds30ShowPreview = function(slots) {
        ds31Ready = false; // reset for new generate
        _orig.apply(this, arguments);
        /* Inject after Snippet 30 finishes building the modal */
        setTimeout(ds31Inject, 350);
    };
    return true;
}

/* ── Also watch via MutationObserver as backup ── */
function ds31WatchModal() {
    var modal = document.getElementById('ds30-preview-modal');
    if (!modal) { setTimeout(ds31WatchModal, 300); return; }

    var obs = new MutationObserver(function() {
        if (modal.classList.contains('open')) {
            setTimeout(ds31Inject, 350);
        } else {
            /* Modal closed — restore theme bars */
            ds31Ready = false;
            document.querySelectorAll('[data-ds31v4]').forEach(function(el) {
                el.style.display = el.dataset.ds31v4;
                delete el.dataset.ds31v4;
            });
            /* Remove our bar */
            var bar = document.getElementById('ds31-dl-bar');
            if (bar) bar.remove();
        }
    });
    obs.observe(modal, { attributes: true, attributeFilter: ['class'] });
}

/* ── Init: try patching every 200ms until found ── */
var _tries = 0;
var _iv = setInterval(function() {
    ds31PatchShowPreview();
    if (window.__ds31v4Patched || ++_tries > 100) clearInterval(_iv);
}, 200);

ds31WatchModal();

/* ── Pick another ── */
window.ds31PickAnother = function() {
    if (typeof window.ds30PreviewClose === 'function') window.ds30PreviewClose();
};

/* ── Download ── */
window.ds31Download = function() {
    var fn  = window.lastFilename || 'presentation.pptx';
    var btn = document.getElementById('ds31-dl-btn');
    if (btn) { btn.disabled = true; btn.innerHTML = '&#8987; Generating…'; }

    // Step 1: Call fill-template NOW (Groq runs here)
    var text = (document.getElementById('ds30-content-input') || {}).value || '';

    var selId = (typeof window.ds30GetSelId === 'function') ? window.ds30GetSelId() : window.selId;
    var text  = (typeof window.ds30GetText  === 'function') ? window.ds30GetText()  : text;

    fetch(API31 + '/fill-template', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE31 },
        body: JSON.stringify({ template_id: selId, text: text })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (!d || d.error) {
            if (btn) { btn.disabled = false; btn.innerHTML = '&#8681; Download PPTX'; }
            alert(d && d.error ? d.error : 'Something went wrong.');
            return;
        }

        // Step 2: Deduct credits
        return fetch(API31 + '/confirm-download', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE31 },
            body: JSON.stringify({})
        }).then(function(r) { return r.json(); }).then(function(cd) {
            if (btn) { btn.disabled = false; btn.innerHTML = '&#8681; Download PPTX'; }
            if (!cd || cd.error) { alert(cd && cd.error ? cd.error : 'Not enough credits.'); return; }

            // Update credits display
            if (cd.credits_balance !== undefined) {
                if (window.G) window.G.credits = cd.credits_balance;
                var ce = document.getElementById('ds-cr-display');
                if (ce) ce.textContent = cd.credits_balance.toLocaleString();
                var cb = document.getElementById('ds30-credits-bar');
                if (cb) cb.textContent = 'Credits: ' + cd.credits_balance.toLocaleString();
            }

            // Step 3: Download the file
            var b64 = d.pptx_b64;
            var byteStr = atob(b64), ab = new ArrayBuffer(byteStr.length), ia = new Uint8Array(ab);
            for (var i = 0; i < byteStr.length; i++) ia[i] = byteStr.charCodeAt(i);
            var blob = new Blob([ab], { type: 'application/vnd.openxmlformats-officedocument.presentationml.presentation' });
            var url = URL.createObjectURL(blob), a = document.createElement('a');
            a.href = url; a.download = d.filename || fn;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(function() { URL.revokeObjectURL(url); }, 8000);

            if (typeof window.ds30PreviewClose === 'function') window.ds30PreviewClose();
            document.getElementById('ds31-success').classList.add('open');
        });
    })
    .catch(function(e) {
        if (btn) { btn.disabled = false; btn.innerHTML = '&#8681; Download PPTX'; }
        alert('Download failed. Please try again.');
    });
};

/* ── Success popup ── */
window.ds31SuccessOk = function() {
    document.getElementById('ds31-success').classList.remove('open');
    if (typeof window.ds30ClearSel === 'function') window.ds30ClearSel();
};

window.ds31SuccessAnother = function() {
    document.getElementById('ds31-success').classList.remove('open');
    if (typeof window.ds30ClearSel === 'function') window.ds30ClearSel();
    var ta = document.getElementById('ds30-content-input');
    if (ta) { ta.value = ''; ta.focus(); }
};

})();
</script>
<?php
}, 1004);
