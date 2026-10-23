<?php
/**
 * Code Snippets export — 9 DS AI Studio [Quick Icons] [Inline Popups] [Fix dmcFmtTs] [Modal CSS Fix] [Saved Images Resolve+CSS] [Fix CSS Chain Break] [Saved Items Modal Usage Counter] [Badge Style Top-Right]
 *
 * Snippet ID : 768
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-082
 * Modified   : 2026-09-09 02:46:37
 * Load order : 087 of 144
 */
/**
 * Snippet Name: DS AI Studio
 * Run: Everywhere
 * Shortcode: [ds_ai_studio]
 * Master container — ONE set of tabs only, switches between panels
 */
add_shortcode('ds_ai_studio', function() {
    if (!is_user_logged_in()) {
        ob_start(); ?>
        <div style="display:flex;align-items:center;justify-content:center;min-height:65vh;background:#0a0a0a;">
          <div style="text-align:center;padding:52px 36px;background:#111;border-radius:24px;border:1px solid #1e1e1e;max-width:400px;">
            <div style="width:60px;height:60px;background:#2563eb;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#fff"/></svg>
            </div>
            <h3 style="font-size:22px;font-weight:800;color:#fff;margin:0 0 10px;">Login to Use AI Studio</h3>
            <p style="font-size:14px;color:#6b7280;margin:0 0 26px;line-height:1.6;">Create AI images and presentations.</p>
            <a href="<?php echo home_url('/login?redirect_to='.urlencode(home_url('/ai-studio'))); ?>" style="display:inline-block;background:#2563eb;color:#fff;padding:13px 32px;border-radius:100px;font-size:14px;font-weight:700;text-decoration:none;">Login / Sign Up Free</a>
            <p style="font-size:12px;color:#374151;margin:16px 0 0;">Free: <strong style="color:#2563eb;">2 AI images/day</strong> + free marketplace access!</p>
          </div>
        </div>
        <?php return ob_get_clean();
    }
    $credits = (int) get_user_meta(get_current_user_id(), 'ds_ai_credits', true);
    $__dsq_rest = trailingslashit(rest_url('ds/v1'));
    $__dsq_rest_hub = trailingslashit(rest_url('ds-hub/v1'));
    $__dsq_nonce = wp_create_nonce('wp_rest');
    $__dsq_site = esc_js(home_url());
    ob_start();
?>
<style>
#ds-stu{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0a0a;padding:0 4px;}
#ds-stu *{box-sizing:border-box;}
/* ONE tab bar — only defined here in Snippet 5 */
.dst-bar{display:flex;gap:14px;margin:24px auto 28px;justify-content:center;flex-wrap:wrap;}
.dst-btn{padding:10px 22px;border-radius:3px;border:none;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .15s;letter-spacing:.3px;text-transform:uppercase;}
.dst-btn.on{background:#2563eb;color:#fff;}
.dst-btn:not(.on){background:#161616;color:#555;border:1px solid #1e1e1e;}
.dst-btn:not(.on):hover{color:#9ca3af;background:#1a1a1a;}
</style>

<div id="ds-stu">

  <!-- Top bar: credits + buy -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0 18px;flex-wrap:wrap;gap:12px;">
    <div class="dst-bar" style="margin:0;">
      <?php if (is_page('ai-presentation')) : ?>
  <a href="<?php echo home_url('/ai-studio/'); ?>" class="dst-btn">
<?php else : ?>
  <button class="dst-btn on" id="dst-img" onclick="dsSTab('img')">
<?php endif; ?>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="2"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/></svg>
        AI Images
      <?php if (is_page('ai-presentation')) : ?>
  </a>
<?php else : ?>
  </button>
<?php endif; ?>
      <a href="<?php echo home_url('/ai-studio/ai-presentation/'); ?>" 
   class="dst-btn <?php echo is_page('ai-presentation') ? 'on' : ''; ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        AI Presentation
      </a>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
      <a href="javascript:void(0)" onclick="if(window.dsmeOpen){window.dsmeOpen();}return false;" title="Expired Files" style="width:34px;height:34px;border-radius:100px;background:#111;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </a>
      <a href="javascript:void(0)" onclick="if(window.dsmsOpenSaved){window.dsmsOpenSaved();}return false;" title="Saved Files" style="width:34px;height:34px;border-radius:100px;background:#111;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      </a>
      <div style="background:#111;border:1px solid #1e1e1e;border-radius:100px;padding:7px 14px;display:flex;align-items:center;gap:6px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="#e8442a"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <span style="font-weight:800;color:#e8442a;font-size:14px;" id="ds-cr-display"><?php echo number_format($credits); ?></span>
        <span style="font-size:11px;color:#374151;">cr</span>
      </div>
      <a href="<?php echo home_url('/pricing'); ?>" style="background:#2563eb;color:#fff;padding:8px 16px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">+ Buy</a>
      <a href="<?php echo home_url('/my-generated-images'); ?>" style="padding:8px 14px;border-radius:100px;border:1px solid #1e1e1e;background:#111;color:#6b7280;font-size:13px;font-weight:600;text-decoration:none;">My Creations</a>
    </div>
  </div>

  <!-- Panel: AI Images — shortcode renders WITHOUT its own tabs -->
  <?php if (!is_page('ai-presentation')) : ?>
  <div id="dsp-img" style="display:block;">
    <?php echo do_shortcode('[ds_ai_images]'); ?>
  </div>
  <div id="dsp-ppt" style="display:none;">
    <?php echo do_shortcode('[ds_ai_ppt]'); ?>
  </div>
<?php else : ?>
  <div id="dsp-ppt" style="display:block;">
    <?php echo do_shortcode('[ds_ai_ppt]'); ?>
  </div>
<?php endif; ?>

</div>

<script>
function dsSTab(id) {
  ['img','ppt'].forEach(function(t){
    var btn = document.getElementById('dst-'+t);
    var pnl = document.getElementById('dsp-'+t);
    var on  = (t === id);
    if(btn){ btn.className = on ? 'dst-btn on' : 'dst-btn'; }
    if(pnl){ pnl.style.display = on ? 'block' : 'none'; }
  });
  localStorage.setItem('ds_active_tab', id);
}
var savedTab = localStorage.getItem('ds_active_tab') || 'img';
var pnlExists = document.getElementById('dsp-'+savedTab);
if(pnlExists){
  pnlExists.style.display = 'block';
  var btnExists = document.getElementById('dst-'+savedTab);
  if(btnExists){ btnExists.className = 'dst-btn on'; }
} else {
  var pptPanel = document.getElementById('dsp-ppt');
  if(pptPanel){ pptPanel.style.display = 'block'; }
  var pptBtn = document.getElementById('dst-ppt');
  if(pptBtn){ pptBtn.className = 'dst-btn on'; }
}


var DMC_REST = '<?php echo esc_js($__dsq_rest); ?>';
var DMC_HUB = '<?php echo esc_js($__dsq_rest_hub); ?>';
var DMC_NONCE = '<?php echo esc_js($__dsq_nonce); ?>';
var DMC_SITE = '<?php echo $__dsq_site; ?>';
function dmcEsc(s){ var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function dmcFmtDateStr(s){ if (!s) return ''; try { var d = new Date(String(s).replace(' ', 'T') + 'Z'); return isNaN(d.getTime()) ? s : d.toLocaleDateString(); } catch(e){ return s; } }
function dmcFmtTs(ts){ if (!ts) return ''; try { return new Date(ts * 1000).toLocaleDateString(); } catch(e){ return ''; } }
var DSMP_THEMES={'navy-pro':['#1B2A4A','#FFFFFF'],'dark-modern':['#0D1117','#FFFFFF'],'clean-corporate':['#FFFFFF','#1A365D'],'bold-red':['#1A0000','#FFFFFF'],'corporate-dark':['#111827','#F9FAFB'],'clean-white':['#F8FAFC','#0F172A'],'gradient-pro':['#0F0C29','#FFFFFF'],'bold-impact':['#111111','#FFFFFF'],'sunset-glow':['#2D1B2E','#FFFFFF'],'forest-green':['#0F2419','#FFFFFF'],'ocean-breeze':['#F0F9FF','#0C4A6E'],'golden-hour':['#1C1408','#FFFFFF'],'lavender-mist':['#F5F3FF','#3B0764'],'slate-minimal':['#F8F9FA','#1E293B'],'cherry-bold':['#FFF5F5','#7F1D1D'],'midnight-teal':['#042F2E','#FFFFFF'],'coral-sunset':['#FFF7ED','#7C2D12'],'royal-purple':['#1E0A3C','#FFFFFF'],'mint-fresh':['#F0FDF4','#14532D'],'rose-gold':['#FFF1F2','#881337'],'steel-blue':['#0F172A','#F1F5F9'],'amber-warm':['#1C1917','#FEF3C7'],'arctic-white':['#FFFFFF','#0F172A'],'crimson-night':['#1A0A0A','#FFFFFF'],'aurora':['linear-gradient(135deg,#0F2027,#1A3A4A)','#FFFFFF'],'sunset-blaze':['linear-gradient(135deg,#7B0000,#DD2476)','#FFFFFF'],'cosmic-purple':['linear-gradient(135deg,#1A0050,#4A00E0)','#FFFFFF'],'ocean-depth':['linear-gradient(135deg,#000028,#003366)','#FFFFFF'],'emerald-dusk':['linear-gradient(135deg,#0A2E1A,#134E5E)','#FFFFFF'],'rose-gold-glow':['linear-gradient(135deg,#0D0720,#1D2671)','#FFFFFF'],'amber-blaze':['linear-gradient(135deg,#1A0A00,#7A3800)','#FFFFFF'],'midnight-bloom':['linear-gradient(135deg,#0B0020,#0B3D47)','#FFFFFF'],'deep-navy':['linear-gradient(135deg,#0A1628,#0D3B6E)','#FFFFFF'],'dark-crimson':['linear-gradient(135deg,#1A0000,#5C0000)','#FFFFFF'],'dark-slate':['linear-gradient(135deg,#0F172A,#1E293B)','#F1F5F9'],'dark-forest':['linear-gradient(135deg,#052010,#0A3020)','#FFFFFF']};
var DSME_PLACEHOLDER = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMTIwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iIzFlMjUzMCIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjQ2IiByPSIxOCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOTRhM2I4IiBzdHJva2Utd2lkdGg9IjMiLz48bGluZSB4MT0iMTAwIiB5MT0iNDYiIHgyPSIxMDAiIHkyPSIzNCIgc3Ryb2tlPSIjOTRhM2I4IiBzdHJva2Utd2lkdGg9IjMiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxsaW5lIHgxPSIxMDAiIHkxPSI0NiIgeDI9IjEwOSIgeTI9IjUyIiBzdHJva2U9IiM5NGEzYjgiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PHRleHQgeD0iMTAwIiB5PSI5MCIgZm9udC1zaXplPSIxMyIgZmlsbD0iI2NiZDVlMSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9IkFyaWFsLHNhbnMtc2VyaWYiPkV4cGlyZWQ8L3RleHQ+PC9zdmc+';
// ==== Saved Items Modal (added) ====
(function(){
  var dsmsModalBuilt = false;
  var dsmsCapDebounce = null;

  function dsmsScheduleCapRefresh(){
    if (dsmsCapDebounce) clearTimeout(dsmsCapDebounce);
    dsmsCapDebounce = setTimeout(function(){ if (window.dsmcRefreshCap) window.dsmcRefreshCap(); }, 1200);
  }

  function dsmsBuildModal(){
    if (dsmsModalBuilt) return;
    dsmsModalBuilt = true;
    var wrap = document.createElement('div');
    wrap.id = 'dsms-modal-overlay';
    wrap.innerHTML =
      '<div id="dsms-modal-panel">' +
        '<div id="dsms-modal-head"><h2>Saved Items</h2><button type="button" id="dsms-modal-close" aria-label="Close">&times;</button></div>' +
        '<div id="dsms-cap-line" style="display:flex;justify-content:flex-end;padding:0 24px 16px;">' +
          '<span style="display:inline-block;padding:6px 16px;border:1px solid #c026d3;border-radius:999px;font-size:13px;color:#f0abfc;background:rgba(192,38,211,0.08);white-space:nowrap;"><span id="dsms-cap-used">0</span> / <span id="dsms-cap-total">0</span> saved items used</span>' +
        '</div>' +
        '<div id="dsms-modal-body">' +
          '<div class="dsms-section"><h3>Images</h3><div id="dsms-images" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Videos</h3><div id="dsms-videos" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Saved Drafts</h3><div id="dsms-drafts" class="dsms-grid"><div class="dsmv-loading">Loading...</div></div></div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);

    var style = document.createElement('style');
    style.textContent =
      '#dsms-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:100000;overflow:auto;padding:40px 16px;}' +
      '#dsms-modal-overlay.open{display:block;}' +
      '#dsms-modal-panel{max-width:1200px;margin:0 auto;background:#0d1117;border-radius:12px;padding:24px;color:#fff;}' +
      '#dsms-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}' +
      '#dsms-modal-head h2{margin:0;font-size:20px;color:#fff;}' +
      '#dsms-modal-close{background:none;border:none;color:#fff;font-size:28px;line-height:1;cursor:pointer;}' +
      '.dsms-section{margin-bottom:28px;}' +
      '.dsms-section h3{font-size:15px;color:#93c5fd;margin:0 0 10px;}' +
      '.dsms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;}' +
      '.dsms-empty{color:#94a3b8;font-size:13px;padding:8px 0;}' +
      '#dsmc-capbar:hover{opacity:.82;text-decoration:underline;}' +
      '.dsmy-card{position:relative;border-radius:8px;overflow:hidden;background:#111;cursor:pointer;aspect-ratio:16/9;}' +
      '.dsmy-card img{width:100%;height:100%;object-fit:contain;background:#111;display:block;transition:transform .25s;}' +
      '.dsmy-card:hover img{transform:scale(1.03);}' +
      '.dsmy-card-tag{position:absolute;top:5px;left:5px;background:rgba(0,0,0,.7);color:#64748b;font-size:9px;padding:2px 6px;border-radius:3px;backdrop-filter:blur(4px);}' +
      '.dsmy-card-sub{position:absolute;top:5px;right:5px;background:rgba(37,99,235,.8);color:#fff;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;}' +
      '.dsmy-card-exp{position:absolute;bottom:5px;left:5px;font-size:8px;font-weight:700;padding:2px 6px;border-radius:3px;color:#fff;}' +
      '.dsmy-card-exp-expired{background:rgba(220,38,38,.88);}' +
      '.dsmy-card-exp-temp,.dsmy-card-exp-saved{background:transparent;padding:0;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 1px 2px rgba(0,0,0,.7));}' +
      '.dsmy-card-expired-ph{display:flex;align-items:center;justify-content:center;height:100%;color:#64748b;font-size:11px;text-align:center;padding:10px;}' +
      '.dsmy-card-actions{position:absolute;bottom:5px;right:5px;display:flex;gap:2px;opacity:.9;transition:opacity .2s;}' +
      '.dsmy-card:hover .dsmy-card-actions{opacity:1;}' +
      '.dsmy-btn-sm{width:26px !important;height:26px !important;border-radius:5px !important;background:transparent !important;border:none !important;outline:none !important;box-shadow:none !important;padding:0 !important;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;filter:drop-shadow(0 1px 3px rgba(0,0,0,.85));}' +
      '.dsmy-btn-sm:hover{background:rgba(255,255,255,.18) !important;}' +
      '.ds-spin{width:18px;height:18px;border:2px solid #1a1a1a;border-top-color:#2563eb;border-radius:50%;animation:dsSpin .7s linear infinite;}' +
      '@keyframes dsSpin{to{transform:rotate(360deg)}}' +
      '.ds-img-spin{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1;pointer-events:none;}' +
      '.dsmy-card img[data-fid]{opacity:0;transition:opacity .25s;}' +
      '.dsmy-card img[data-fid].ds-img-loaded{opacity:1;}' +
      '.dsmv-card{background:#111;border-radius:8px;overflow:hidden;border:1px solid #1e1e1e;}' +
      '.dsmv-card video{width:100%;aspect-ratio:16/9;background:#000;display:block;}' +
      '.dsmv-meta{padding:8px 10px;}' +
      '.dsmv-prompt{font-size:11px;color:#cbd5e1;margin:0 0 6px;line-height:1.4;max-height:32px;overflow:hidden;}' +
      '.dsmv-badge{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px;margin-bottom:6px;}' +
      '.dsmv-badge.expired{background:#2a0505;color:#f87171;}' +
      '.dsmv-badge.saved,.dsmv-badge.temp{background:transparent;padding:0;display:inline-flex;align-items:center;justify-content:center;}' +
      '.dsmv-actions{display:flex;gap:5px;padding:0 10px 10px;}' +
      '.dsmv-btn{flex:1;text-align:center;padding:6px 4px;font-size:10.5px;font-weight:700;border-radius:6px;border:1px solid #262626;background:#1a1a1a;color:#cbd5e1;cursor:pointer;text-decoration:none;}' +
      '.dsmv-btn:hover{background:#242424;}' +
      '.dsmv-btn.save{color:#4ade80;}' +
      '.dsmv-btn.delete{color:#f87171;}' +
      '.dsmv-empty,.dsmv-loading{grid-column:1/-1;text-align:center;padding:60px 20px;color:#64748b;font-size:13px;}' +
      '.dsmp-section{padding:14px 16px;}' +
      '.dsmp-section h3{font-size:13px;color:#f1f5f9;margin:0 0 8px;font-weight:700;}' +
      '.dsmp-card{background:#111;border:1px solid #1e1e1e;border-radius:8px;overflow:hidden;}' +
      '.dsmp-thumb{aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;padding:12px;text-align:center;box-sizing:border-box;overflow:hidden;}' +
      '.dsmp-thumb-title{font-size:12px;font-weight:700;line-height:1.35;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}' +
      '.dsmp-thumb-sub{font-size:10px;line-height:1.3;margin-top:4px;opacity:.8;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}' +
      '.dsmp-thumb-generic{background:linear-gradient(135deg,#1e293b,#0f172a);}' +
      '.dsmp-thumb-generic .dsmp-thumb-title{color:#e2e8f0;}' +
      '.dsmp-thumb-photo{background-size:cover;background-position:center;position:relative;}' +
      '.dsmp-thumb-photo .dsmp-thumb-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 35%,rgba(0,0,0,.78) 100%);display:flex;align-items:flex-end;padding:10px;box-sizing:border-box;}' +
      '.dsmp-thumb-photo .dsmp-thumb-title{color:#fff;}' +
      '.dsmp-thumb-photo .dsmp-thumb-sub{color:#f1f5f9;}' +
      '.dsmp-thumb-icon{font-size:26px;margin-bottom:6px;opacity:.5;}' +
      '.dsmp-body{padding:8px 10px 0;}' +
      '.dsmp-card h4{font-size:12px;color:#f1f5f9;margin:0 0 4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}' +
      '.dsmp-card p{font-size:10.5px;color:#64748b;margin:0 0 8px;}';
    document.head.appendChild(style);

    wrap.addEventListener('click', function(e){
      if (e.target === wrap) dsmsClose();
      dsmsScheduleCapRefresh();
    });
    document.getElementById('dsms-modal-close').addEventListener('click', dsmsClose);
  }

  function dsmsClose(){
    var wrap = document.getElementById('dsms-modal-overlay');
    if (wrap) wrap.classList.remove('open');
    if (window.dsmcRefreshCap) window.dsmcRefreshCap();
  }

  function dsmsSetImgSrc(img, url){
img.onload = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
img.onerror = function(){ img.classList.add('ds-img-loaded'); var sp = img.nextElementSibling; if (sp && sp.className === 'ds-img-spin') sp.style.display = 'none'; };
img.src = url;
}
function dsmsResolveImgSrcs(root){
root = root || document;
var els = Array.prototype.slice.call(root.querySelectorAll('img[data-fid]:not([data-resolved])'));
var byFid = {}; var ids = [];
els.forEach(function(img){
img.setAttribute('data-resolved','1');
var fid = img.getAttribute('data-fid');
if (fid && fid !== '0') {
if (!byFid[fid]) { byFid[fid] = []; ids.push(fid); }
byFid[fid].push(img);
} else {
var fb0 = img.getAttribute('data-fallback');
if (fb0) dsmsSetImgSrc(img, fb0);
}
});
if (!ids.length) return;
function chunk(arr,size){var out=[];for(var k=0;k<arr.length;k+=size)out.push(arr.slice(k,k+size));return out;}
chunk(ids, 40).forEach(function(group){
fetch(DMC_HUB + 'files?ids=' + group.join(',') + '&_wpnonce=' + DMC_NONCE, {credentials:'include'})
.then(function(r){return r.json();})
.then(function(map){
group.forEach(function(fid){
var url = map && map[fid];
(byFid[fid]||[]).forEach(function(img){
if (url) { dsmsSetImgSrc(img, url); } else { var fb = img.getAttribute('data-fallback'); if (fb) dsmsSetImgSrc(img, fb); }
});
});
})
.catch(function(){
group.forEach(function(fid){
(byFid[fid]||[]).forEach(function(img){ var fb = img.getAttribute('data-fallback'); if (fb) dsmsSetImgSrc(img, fb); });
});
});
});
}
function dsmsLoadImages(){
    var el = document.getElementById('dsms-images');
    fetch(DMC_REST + 'my-saved-images', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d || !d.success || !d.count) { el.innerHTML = '<div class="dsms-empty">No saved images.</div>'; return; }
      el.innerHTML = d.html;
      dsmsResolveImgSrcs(el);
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load images.</div>'; });
  }

  function dsmsLoadVideos(){
    var el = document.getElementById('dsms-videos');
    fetch(DMC_REST + 'my-videos?offset=0&limit=1000', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      var all = (d && d.success) ? (d.videos || []) : [];
      var saved = all.filter(function(v){ return v.exp_badge === 'saved'; });
      if (!saved.length) { el.innerHTML = '<div class="dsms-empty">No saved videos.</div>'; return; }
      var html = '';
      saved.forEach(function(v){
        html += '<div class="dsmv-card">';
        html += '<video controls preload="none" src="' + v.url + '"></video>';
        html += '<div class="dsmv-meta"><span class="dsmv-badge saved">Saved</span><p class="dsmv-prompt">' + dmcEsc((v.prompt||'').substring(0,80)) + '</p></div>';
        html += '<div class="dsmv-actions">';
        html += '<a class="dsmv-btn" href="' + v.url + '" target="_blank" rel="noopener">Download</a>';
        if (v.file_id) html += '<button type="button" class="dsmv-btn delete" onclick="dsmsDeleteVideo(' + v.file_id + ', this)">Delete</button>';
        html += '</div></div>';
      });
      el.innerHTML = html;
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load videos.</div>'; });
  }

  function dsmsLoadDrafts(){
    var el = document.getElementById('dsms-drafts');
    fetch(DMC_REST + 'my-ppts?offset=0&limit=1000', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      var drafts = (d && d.success) ? (d.drafts || []) : [];
      if (!drafts.length) { el.innerHTML = '<div class="dsms-empty">No saved drafts.</div>'; return; }
      var html = '';
      drafts.forEach(function(dr){
        var theme = DSMP_THEMES[dr.design] || DSMP_THEMES['dark-modern'];
        var fs = dr.first_slide || {};
        html += '<div class="dsmp-card">';
        if (fs.image_data) {
          html += '<div class="dsmp-thumb dsmp-thumb-photo" style="background-image:url(\'' + fs.image_data + '\');">';
          html += '<div class="dsmp-thumb-overlay"><div>';
          html += '<div class="dsmp-thumb-title">' + dmcEsc(fs.title || dr.title || 'Untitled') + '</div>';
          if (fs.subtitle) html += '<div class="dsmp-thumb-sub">' + dmcEsc(fs.subtitle) + '</div>';
          html += '</div></div></div>';
        } else {
          html += '<div class="dsmp-thumb" style="background:' + theme[0] + ';"><div>';
          html += '<div class="dsmp-thumb-title" style="color:' + theme[1] + ';">' + dmcEsc(fs.title || dr.title || 'Untitled') + '</div>';
          if (fs.subtitle) html += '<div class="dsmp-thumb-sub" style="color:' + theme[1] + ';">' + dmcEsc(fs.subtitle) + '</div>';
          html += '</div></div>';
        }
        html += '<div class="dsmp-body"><h4>' + dmcEsc(dr.title || 'Untitled') + '</h4><p>' + (dr.slides_count || 0) + ' slides &middot; ' + dmcFmtTs(dr.updated) + '</p></div>';
        html += '<div class="dsmv-actions">';
        html += '<a class="dsmv-btn" href="' + DMC_SITE + '/ai-presentation/?ds_load_ppt=' + encodeURIComponent(dr.id) + '">Open</a>';
        html += '<button type="button" class="dsmv-btn delete" onclick="dsmsDeleteDraft(\'' + dr.id + '\', this)">Delete</button>';
        html += '</div></div>';
      });
      el.innerHTML = html;
    })
    .catch(function(){ el.innerHTML = '<div class="dsms-empty">Could not load drafts.</div>'; });
  }

    window.dsmsDeleteVideo = function(fileId, btn){
    if (!confirm('Delete this video permanently?')) return;
    btn.disabled = true;
    fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(){
      var card = btn.closest('.dsmv-card'); if (card) card.remove();
      vLoaded = false;
      if (window.dsmcRefreshCap) window.dsmcRefreshCap();
    })
    .catch(function(){ btn.disabled = false; });
  };

  window.dsmsDeleteDraft = function(id, btn){
    if (!confirm('Delete this draft permanently?')) return;
    btn.disabled = true;
    fetch(DMC_REST + 'delete-ppt', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-WP-Nonce': DMC_NONCE}, credentials: 'include', body: JSON.stringify({ ppt_id: id }) })
    .then(function(r){ return r.json(); })
    .then(function(){
      var card = btn.closest('.dsmp-card'); if (card) card.remove();
      pLoaded = false;
      if (window.dsmcRefreshCap) window.dsmcRefreshCap();
    })
    .catch(function(){ btn.disabled = false; });
  };

  function dsmsRefreshCapLine(){
    var u = document.getElementById('dsms-cap-used');
    var t = document.getElementById('dsms-cap-total');
    if (!u || !t) return;
    fetch(DMC_REST + 'my-cap', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (!d || !d.success) return;
      u.textContent = d.used;
      t.textContent = d.total;
    })
    .catch(function(){});
  }

  window.dsmsOpenSaved = function(){
    dsmsBuildModal();
    var wrap = document.getElementById('dsms-modal-overlay');
    wrap.classList.add('open');
    dsmsLoadImages();
    dsmsLoadVideos();
    dsmsLoadDrafts();
    dsmsRefreshCapLine();
  };

  var capEl = document.getElementById('dsmc-capbar');
  if (capEl) {
    capEl.style.cursor = 'pointer';
    capEl.title = 'View your saved items';
    capEl.addEventListener('click', window.dsmsOpenSaved);
  }
})();


// ==== Expired Files Modal (added) ====
(function(){
  var dsmeModalBuilt = false;

  var DSME_PLACEHOLDER = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMTIwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iIzFlMjUzMCIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjQ2IiByPSIxOCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOTRhM2I4IiBzdHJva2Utd2lkdGg9IjMiLz48bGluZSB4MT0iMTAwIiB5MT0iNDYiIHgyPSIxMDAiIHkyPSIzNCIgc3Ryb2tlPSIjOTRhM2I4IiBzdHJva2Utd2lkdGg9IjMiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxsaW5lIHgxPSIxMDAiIHkxPSI0NiIgeDI9IjEwOSIgeTI9IjUyIiBzdHJva2U9IiM5NGEzYjgiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PHRleHQgeD0iMTAwIiB5PSI5MCIgZm9udC1zaXplPSIxMyIgZmlsbD0iI2NiZDVlMSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9IkFyaWFsLHNhbnMtc2VyaWYiPkV4cGlyZWQ8L3RleHQ+PC9zdmc+';

  function dsmeBuildModal(){
    if (dsmeModalBuilt) return;
    dsmeModalBuilt = true;
    var wrap = document.createElement('div');
    wrap.id = 'dsme-modal-overlay';
    wrap.innerHTML =
      '<div id="dsme-modal-panel">' +
        '<div id="dsme-modal-head"><h2>Expired Files</h2><button type="button" id="dsme-modal-close" aria-label="Close">&times;</button></div>' +
        '<div id="dsme-modal-body">' +
          '<div class="dsme-note">These are removed automatically once deleted from storage \u2014 nothing to do here.</div>' +
          '<div class="dsms-section"><h3>Expired Images</h3><div id="dsme-images" class="dsme-list"><div class="dsmv-loading">Loading...</div></div></div>' +
          '<div class="dsms-section"><h3>Expired Videos</h3><div id="dsme-videos" class="dsme-list"><div class="dsmv-loading">Loading...</div></div></div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);

    var style = document.createElement('style');
    style.textContent =
      '#dsme-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:100000;overflow:auto;padding:40px 16px;}' +
      '#dsme-modal-overlay.open{display:block;}' +
      '#dsme-modal-panel{max-width:700px;margin:0 auto;background:#0d1117;border-radius:12px;padding:24px;color:#fff;}' +
      '#dsme-modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}' +
      '#dsme-modal-head h2{margin:0;font-size:20px;color:#fff;}' +
      '#dsme-modal-close{background:none;border:none;color:#fff;font-size:28px;line-height:1;cursor:pointer;}' +
      '.dsms-section h3{font-size:15px;color:#93c5fd;margin:0 0 10px;}' +
      '.dsme-note{font-size:11.5px;color:#94a3b8;margin:-4px 0 14px;}' +
      '.dsme-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;}' +
      '.dsme-row{display:flex;flex-direction:column;background:#161b22;border:1px solid #a21caf;border-radius:8px;overflow:hidden;}' +
      '.dsme-thumb{width:100%;height:70px;object-fit:cover;display:block;background:#1e2530;}' +
      '.dsme-info{padding:8px 10px 10px;}' +
      '.dsme-info div{font-size:10px;color:#e879f9;line-height:1.5;word-break:break-word;}' +
      '.dsme-info .dsmv-btn{margin-top:8px;width:100%;font-size:11px;padding:6px 0;}' +
      '#dsme-badge{cursor:pointer;color:#f59e0b;font-size:13px;margin-left:12px;}' +
      '#dsme-badge:hover{opacity:.8;text-decoration:underline;}';
    document.head.appendChild(style);

    wrap.addEventListener('click', function(e){ if (e.target === wrap) dsmeClose(); });
    document.getElementById('dsme-modal-close').addEventListener('click', dsmeClose);
  }

  function dsmeClose(){
    var wrap = document.getElementById('dsme-modal-overlay');
    if (wrap) wrap.classList.remove('open');
  }

  function dsmeRow(item){
    var fname = item.filename ? String(item.filename) : '(untitled)';
    var created = item.created_at ? dmcFmtDateStr(item.created_at) : '\u2014';
    var expired = item.expires_at ? dmcFmtDateStr(item.expires_at) : '\u2014';
    return '<div class="dsme-row">' +
      '<img class="dsme-thumb" src="' + DSME_PLACEHOLDER + '" alt="Expired" loading="lazy"/>' +
      '<div class="dsme-info">' +
        '<div>File name: ' + dmcEsc(fname) + '</div>' +
        '<div>Date of creation: ' + dmcEsc(created) + '</div>' +
        '<div>Expired date: ' + dmcEsc(expired) + '</div>' +
        '<button type="button" class="dsmv-btn delete" onclick="dsmeDelete(' + item.file_id + ', this)">Delete</button>' +
      '</div>' +
    '</div>';
  }

  function dsmeRender(data){
    var imgEl = document.getElementById('dsme-images');
    var vidEl = document.getElementById('dsme-videos');
    var images = (data && data.images) || [];
    var videos = (data && data.videos) || [];
    imgEl.innerHTML = images.length ? images.map(dsmeRow).join('') : '<div class="dsms-empty">No expired images.</div>';
    vidEl.innerHTML = videos.length ? videos.map(dsmeRow).join('') : '<div class="dsms-empty">No expired videos.</div>';
  }

  function dsmeFetch(){
    return fetch(DMC_REST + 'my-expired-items', { headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
      .then(function(r){ return r.json(); });
  }

  function dsmeUpdateBadge(count){
    var badge = document.getElementById('dsme-badge');
    if (!badge) {
      var capEl2 = document.getElementById('dsmc-capbar');
      if (capEl2 && capEl2.parentNode) {
        var hub2 = capEl2.parentNode;
        var toprow = document.getElementById('dsmc-toprow');
        if (!toprow) {
          var tabsEl2 = hub2.querySelector('.dsmc-tabs');
          toprow = document.createElement('div');
          toprow.id = 'dsmc-toprow';
          hub2.insertBefore(toprow, tabsEl2 || capEl2);
          if (tabsEl2) toprow.appendChild(tabsEl2);
          var rightWrap2 = document.createElement('div');
          rightWrap2.id = 'dsmc-topbar-wrap';
          toprow.appendChild(rightWrap2);
          rightWrap2.appendChild(capEl2);
        }
        var wrap2 = document.getElementById('dsmc-topbar-wrap');
        if (wrap2) {
          badge = document.createElement('span');
          badge.id = 'dsme-badge';
          wrap2.insertBefore(badge, capEl2);
          badge.addEventListener('click', window.dsmeOpen);
        }
      }
    }
    if (badge) badge.textContent = String.fromCharCode(9888) + ' ' + count + ' expired file' + (count === 1 ? '' : 's');
  }

  function dsmeUpdateBadge2(d){ dsmeUpdateBadge(d && d.count ? d.count : 0); }

  window.dsmeDelete = function(fileId, btn){
    if (!confirm('Delete this expired file permanently?')) return;
    btn.disabled = true;
    fetch(DMC_HUB + 'delete/' + fileId, { method: 'POST', headers: {'X-WP-Nonce': DMC_NONCE}, credentials: 'include' })
    .then(function(r){ return r.json(); })
    .then(function(){
      var row = btn.closest('.dsme-row'); if (row) row.remove();
      vLoaded = false; pLoaded = false;
      dsmeFetch().then(dsmeUpdateBadge2);
    })
    .catch(function(){ btn.disabled = false; });
  };

  window.dsmeOpen = function(){
    dsmeBuildModal();
    document.getElementById('dsme-modal-overlay').classList.add('open');
    dsmeFetch().then(function(d){
      if (d && d.success) dsmeRender(d);
    });
  };

  dsmeFetch().then(function(d){
    if (d && d.success) dsmeUpdateBadge(d.count || 0);
  });

  try {
    var __dsOpenParam = new URLSearchParams(window.location.search).get('ds_open');
    if (__dsOpenParam === 'saved' && window.dsmsOpenSaved) { window.dsmsOpenSaved(); }
    else if (__dsOpenParam === 'expired' && window.dsmeOpen) { window.dsmeOpen(); }
  } catch(e) {}
})();
</script>

<?php return ob_get_clean();
});
