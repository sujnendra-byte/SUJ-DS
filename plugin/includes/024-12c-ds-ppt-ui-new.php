<?php
/**
 * Code Snippets export — 12c ds ppt ui New
 *
 * Snippet ID : 710
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-024
 * Modified   : 2026-09-09 02:42:14
 * Load order : 024 of 144
 */
/**
 * Snippet Name: DS AI PPT v6 - UI (HTML + CSS)
 * Run: Everywhere
 *
 * Frontend shortcode: [ds_ai_ppt]
 *
 * Required companion snippets:
 *   - 12a-ds-ppt-renderers.php (slide renderers)
 *   - 12b-ds-ppt-builder.php   (API endpoints + PPTX builder)
 *   - 12d-ds-ppt-ui-js.php     (JavaScript logic -- must also be active)
 */

if ( ! defined('ABSPATH') ) exit;

add_shortcode('ds_ai_ppt', function() {
    if (!is_user_logged_in()) return '';
    $uid    = get_current_user_id();
    $cr     = (int) get_user_meta($uid,'ds_ai_credits',true);
    $nonce  = wp_create_nonce('wp_rest');
    $purl   = esc_js(rest_url('ds/v1/generate-ppt'));
    $bldurl = esc_js(rest_url('ds/v1/build-pptx'));
    $imgurl = esc_js(rest_url('ds/v1/fetch-slide-image'));

    $slide_opts = '';
    for ($s = 1; $s <= 20; $s++) {
        $on = ($s === 10) ? ' dsp-pill-opt-on' : '';
        $slide_opts .= '<div class="dsp-pill-opt'.$on.'" onclick="dspPillSet(\'slides\','.$s.',\''.$s.' card'.($s>1?'s':'').'\')">'.$s.' card'.($s > 1 ? 's' : '').'</div>';
    }
    $themes = array(
        'navy-pro'      => 'Navy Pro',
        'dark-modern'   => 'Dark Modern',
        'clean-corp'    => 'Clean Corporate',
        'bold-red'      => 'Bold Red',
        'corp-dark'     => 'Corporate Dark',
        'clean-white'   => 'Clean White',
        'gradient-pro'  => 'Gradient Pro',
        'bold-impact'   => 'Bold Impact',
    );
    $theme_opts = '';
    foreach ($themes as $val => $label) {
        $on = ($val === 'navy-pro') ? ' dsp-pill-opt-on' : '';
        $theme_opts .= '<div class="dsp-pill-opt'.$on.'" onclick="dspPillSet(\'design\',\''.esc_js($val).'\',\''.esc_js($label).'\')">'.esc_html($label).'</div>';
    }
    $langs = array(
		'English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese',
		'Dutch', 'Russian', 'Ukrainian', 'Polish', 'Czech', 'Romanian',
		'Hungarian', 'Greek', 'Swedish', 'Norwegian', 'Danish', 'Finnish',
		'Turkish', 'Arabic', 'Hebrew', 'Persian', 'Chinese (Simplified)', 'Chinese (Traditional)',
		'Japanese', 'Korean', 'Vietnamese', 'Thai', 'Indonesian', 'Malay',
		'Filipino', 'Swahili', 'Hindi', 'Bengali', 'Marathi', 'Gujarati',
		'Punjabi', 'Urdu', 'Tamil', 'Telugu', 'Kannada', 'Malayalam',
		'Odia', 'Assamese', 'Nepali', 'Sinhala'
	);
    $lang_opts = '';
    foreach ($langs as $lg) {
        $on = ($lg === 'English') ? ' dsp-pill-opt-on' : '';
        $lang_opts .= '<div class="dsp-pill-opt'.$on.'" onclick="dspPillSet(\'lang\',\''.esc_js($lg).'\',\''.esc_js($lg).'\')">'.esc_html($lg).'</div>';
    }
    $cr_fmt = number_format($cr);

    ob_start();

echo '<style>
/* =============================================
   DS PPT - Global reset
   ============================================= */
#dsp-wrap,#dsp-wrap *{box-sizing:border-box!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif!important;}
#dsp-wrap{padding:0!important;margin:0!important;}
.dsp-scr{display:none!important;}
.dsp-scr.dsp-active{display:block!important;}

/* =============================================
   HOME SCREEN - Stitch design
   ============================================= */
#dsp-home{
  min-height:100vh!important;
  background:#0a0a0a!important;
  display:none!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:flex-start!important;
  padding:60px 20px 40px!important;
}
#dsp-home.dsp-active{display:flex!important;}

/* Hero heading */
#dsp-home-hero{
  text-align:center!important;
  margin-bottom:36px!important;
  max-width:600px!important;
  width:100%!important;
}
#dsp-home-hero h1{
  font-size:clamp(28px,5vw,42px)!important;
  font-weight:800!important;
  color:#e8eaed!important;
  margin:0 0 10px!important;
  line-height:1.2!important;
  letter-spacing:-0.5px!important;
}
#dsp-home-hero p{
  font-size:14px!important;
  color:#9ca3af!important;
  margin:0!important;
  line-height:1.6!important;
}

/* Mode toggle */
#dsp-mode-row{
  display:flex!important;
  gap:8px!important;
  margin-bottom:12px!important;
  width:100%!important;
  max-width:680px!important;
}
.dsp-mode{
  flex:1!important;
  padding:10px 14px!important;
  border-radius:8px!important;
  cursor:pointer!important;
  border:1.5px solid #1e1e1e!important;
  background:#111!important;
  transition:all .15s!important;
}
.dsp-mode.dsp-m-on{background:#0a1628!important;border-color:#2563eb!important;}
.dsp-mode-title{font-size:12px!important;font-weight:700!important;color:#d1d5db!important;display:block!important;}
.dsp-mode.dsp-m-on .dsp-mode-title{color:#60a5fa!important;}
.dsp-mode-sub{font-size:10px!important;color:#6b7280!important;display:block!important;margin-top:2px!important;}

/* Prompt box */
#dsp-pbox{
  background:#0d0d0d!important;
  border:1px solid #1e1e1e!important;
  border-radius:12px!important;
  overflow:hidden!important;
  margin-bottom:16px!important;
  width:100%!important;
  max-width:680px!important;
}
#dsp-prompt{
  width:100%!important;
  background:#0d0d0d!important;
  border:none!important;
  outline:none!important;
  resize:vertical!important;
  min-height:120px!important;
  padding:16px!important;
  font-size:14px!important;
  color:#e5e7eb!important;
  line-height:1.7!important;
  display:block!important;
}
#dsp-prompt::placeholder{color:#4b5563!important;}
#dsp-pbox-footer{
  display:flex!important;
  justify-content:space-between!important;
  align-items:center!important;
  padding:6px 14px 10px!important;
}
#dsp-pbox-tip{font-size:10.5px!important;color:#4b5563!important;}
#dsp-char{font-size:10.5px!important;color:#4b5563!important;}

/* Pills row */
#dsp-pills-row{
  display:flex!important;
  gap:8px!important;
  flex-wrap:wrap!important;
  margin-bottom:16px!important;
  width:100%!important;
  max-width:680px!important;
  position:relative!important;
}
.dsp-pill{
  display:inline-flex!important;
  align-items:center!important;
  gap:6px!important;
  padding:7px 12px!important;
  background:#191c25!important;
  border:1.5px solid #1f222b!important;
  border-radius:5px!important;
  font-size:12px!important;
  font-weight:600!important;
  color:#d1d5db!important;
  cursor:pointer!important;
  position:relative!important;
  white-space:nowrap!important;
  transition:all .15s!important;
}
.dsp-pill:hover{border-color:#2563eb!important;color:#e8eaed!important;background:#0a1628!important;}
.dsp-pill-arrow{font-size:9px!important;color:#6b7280!important;margin-left:2px!important;}
.dsp-pill-menu{
  position:fixed!important;
  background:#10131c!important;
  border:1.5px solid #1f222b!important;
  border-radius:5px!important;
  padding:6px!important;
  box-shadow:0 8px 28px rgba(0,0,0,.7)!important;
  z-index:9900!important;
  min-width:160px!important;
  max-width:220px!important;
  max-height:280px!important;
  overflow-y:auto!important;
  display:none!important;
  pointer-events:none!important;
}
.dsp-pill-menu.dsp-menu-open{display:block!important;pointer-events:all!important;}
.dsp-pill-opt{
  padding:8px 10px!important;
  border-radius:6px!important;
  font-size:12px!important;
  color:#9ca3af!important;
  cursor:pointer!important;
  transition:all .12s!important;
}
.dsp-pill-opt:hover{background:#1a1a1a!important;color:#e5e7eb!important;}
.dsp-pill-opt.dsp-pill-opt-on{background:#0a1628!important;color:#60a5fa!important;font-weight:700!important;}

/* Cost bar */
#dsp-costbar{
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  background:#111!important;
  border:1.5px solid #2a2a2a!important;
  border-radius:10px!important;
  padding:10px 14px!important;
  margin-bottom:16px!important;
  width:100%!important;
  max-width:680px!important;
  flex-wrap:wrap!important;
  gap:6px!important;
}
#dsp-costsumm{font-size:12px!important;color:#9ca3af!important;}
#dsp-costamt{font-size:13px!important;font-weight:700!important;color:#2563eb!important;}
#dsp-costhave{font-size:11px!important;font-weight:400!important;color:#6b7280!important;}

/* Generate button */
#dsp-genbtn{
  width:100%!important;
  max-width:680px!important;
  background:#2563eb!important;
  color:#fff!important;
  border:none!important;
  padding:16px 20px!important;
  border-radius:10px!important;
  font-size:15px!important;
  font-weight:800!important;
  cursor:pointer!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:8px!important;
  text-transform:uppercase!important;
  letter-spacing:.5px!important;
  transition:background .15s!important;
  margin-bottom:28px!important;
}
#dsp-genbtn:hover{background:#1d4ed8!important;}

/* Quick examples */
#dsp-examples-wrap{width:100%!important;max-width:680px!important;}
.dsp-ex-lbl{
  display:block!important;
  font-size:10px!important;
  font-weight:700!important;
  letter-spacing:.8px!important;
  text-transform:uppercase!important;
  color:#6b7280!important;
  margin-bottom:10px!important;
}
#dsp-examples-grid{
  display:grid!important;
  grid-template-columns:repeat(auto-fill,minmax(200px,1fr))!important;
  gap:8px!important;
}
.dsp-ex{
  padding:10px 12px!important;
  background:#111!important;
  border:1.5px solid #1e1e1e!important;
  border-radius:8px!important;
  cursor:pointer!important;
  font-size:11.5px!important;
  color:#9ca3af!important;
  line-height:1.4!important;
  transition:all .15s!important;
}
.dsp-ex:hover{border-color:#2563eb!important;color:#e5e7eb!important;background:#0a1628!important;}

/* Hidden inputs */
#dsp-slides,#dsp-design,#dsp-layout,#dsp-lang,#dsp-imgs{display:none!important;}

/* =============================================
   LOADING SCREEN
   ============================================= */
#dsp-loading{
  min-height:100vh!important;
  background:#0a0a0a!important;
  display:none!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:center!important;
  padding:40px 20px!important;
  text-align:center!important;
}
#dsp-loading.dsp-active{display:flex!important;}
.dsp-spinner{position:relative!important;width:68px!important;height:68px!important;margin:0 auto 24px!important;}
.dsp-r1{position:absolute!important;inset:0!important;border-radius:50%!important;border:3px solid #1e1e1e!important;border-top-color:#2563eb!important;animation:dspSpin 1s linear infinite!important;}
.dsp-r2{position:absolute!important;inset:10px!important;border-radius:50%!important;border:3px solid #1e1e1e!important;border-top-color:#1d4ed8!important;animation:dspSpin .75s linear infinite reverse!important;}
.dsp-ric{position:absolute!important;inset:0!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:18px!important;}
@keyframes dspSpin{to{transform:rotate(360deg)}}
#dsp-ltitle{font-size:22px!important;font-weight:800!important;color:#e8eaed!important;margin:0 0 6px!important;}
#dsp-lsub{font-size:13px!important;color:#6b7280!important;margin:0 0 24px!important;}
#dsp-steps{width:100%!important;max-width:420px!important;margin:0 auto 20px!important;}
.dsp-step{
  display:flex!important;
  align-items:center!important;
  gap:10px!important;
  padding:11px 14px!important;
  border-radius:8px!important;
  background:#111!important;
  border:1px solid #1a1a1a!important;
  margin-bottom:8px!important;
  transition:all .3s!important;
}
.dsp-step.act{border-color:#2563eb!important;background:#0a1628!important;}
.dsp-step.done{border-color:#10b981!important;background:#0a1f16!important;}
.dsp-stic{
  width:26px!important;height:26px!important;border-radius:50%!important;
  background:#1f1f1f!important;display:flex!important;align-items:center!important;
  justify-content:center!important;font-size:13px!important;flex-shrink:0!important;
}
.dsp-step.act .dsp-stic{background:#2563eb!important;}
.dsp-step.done .dsp-stic{background:#10b981!important;}
.dsp-stxt{font-size:13px!important;color:#9ca3af!important;font-weight:600!important;flex:1!important;text-align:left!important;}
.dsp-step.act .dsp-stxt{color:#e8eaed!important;}
.dsp-step.done .dsp-stxt{color:#6ee7b7!important;}
#dsp-prog-wrap{width:100%!important;max-width:420px!important;background:#1a1a1a!important;border-radius:100px!important;height:4px!important;overflow:hidden!important;}
#dsp-prog{height:100%!important;background:linear-gradient(90deg,#2563eb,#1d4ed8)!important;border-radius:100px!important;width:0%!important;transition:width .5s ease!important;}
#dsp-cancel-btn{
  margin-top:20px!important;
  padding:8px 24px!important;
  background:transparent!important;
  border:1px solid #2a2a2a!important;
  color:#6b7280!important;
  border-radius:100px!important;
  font-size:12px!important;
  font-weight:600!important;
  letter-spacing:.5px!important;
  text-transform:uppercase!important;
  cursor:pointer!important;
}
#dsp-cancel-btn:hover{border-color:#5f6368!important;color:#9ca3af!important;}

/* =============================================
   EDITOR FULLSCREEN OVERLAY
   ============================================= */
body.dsp-editor-open header,
body.dsp-editor-open .site-header,
body.dsp-editor-open nav.main-navigation,
body.dsp-editor-open footer,
body.dsp-editor-open .site-footer,
body.dsp-editor-open #masthead,
body.dsp-editor-open #colophon,
body.dsp-editor-open .admin-bar{display:none!important;}
body.dsp-editor-open{overflow:hidden!important;}

/* The fullscreen overlay wraps the whole editor */
#dsp-editor-fullscreen{
  display:none!important;
  position:fixed!important;
  inset:0!important;
  z-index:99999!important;
  flex-direction:column!important;
  background:#3c4043!important;
}
#dsp-editor-fullscreen.dsp-open{display:flex!important;}

/* Toolbar */
#dsp-ppt-toolbar{
  display:flex!important;
  align-items:center!important;
  padding:0 16px!important;
  height:48px!important;
  background:#202124!important;
  border-bottom:1px solid #2a2a2a!important;
  gap:12px!important;
  flex-shrink:0!important;
  z-index:10!important;
}
.dsp-tb-back{
  display:inline-flex!important;
  align-items:center!important;
  gap:6px!important;
  padding:6px 12px!important;
  border-radius:4px!important;
  border:none!important;
  background:transparent!important;
  color:#9ca3af!important;
  font-size:13px!important;
  cursor:pointer!important;
  transition:color .15s!important;
}
.dsp-tb-back:hover{color:#e8eaed!important;}
.dsp-tb-back svg{width:18px!important;height:18px!important;fill:currentColor!important;}
#dsp-tb-divider{width:1px!important;height:24px!important;background:#3c4043!important;flex-shrink:0!important;}
.dsp-tb-title{
  flex:1!important;
  font-size:14px!important;
  font-weight:600!important;
  color:#e8eaed!important;
  overflow:hidden!important;
  text-overflow:ellipsis!important;
  white-space:nowrap!important;
}
#dsp-imgstatus-tb{font-size:11px!important;color:#8ab4f8!important;display:none!important;}
.dsp-tb-build{
  display:inline-flex!important;
  align-items:center!important;
  gap:6px!important;
  padding:8px 18px!important;
  background:#1a73e8!important;
  color:#fff!important;
  border:none!important;
  border-radius:6px!important;
  font-size:13px!important;
  font-weight:600!important;
  cursor:pointer!important;
  transition:background .15s!important;
  white-space:nowrap!important;
}
.dsp-tb-build:hover{background:#1557b0!important;}

/* Workspace = left panel + canvas area */
#dsp-ppt-workspace{
  display:flex!important;
  flex:1!important;
  overflow:hidden!important;
  background:#3c4043!important;
}

/* Left thumbnail panel */
#dsp-ppt-panel-left{
  width:148px!important;
  flex-shrink:0!important;
  background:#2d2e30!important;
  border-right:1px solid #1a1a1a!important;
  display:flex!important;
  flex-direction:column!important;
  overflow:hidden!important;
}
#dsp-ppt-panel-left-header{
  padding:8px 10px 6px!important;
  font-size:9px!important;
  font-weight:700!important;
  letter-spacing:.8px!important;
  text-transform:uppercase!important;
  color:#6b7280!important;
  border-bottom:1px solid #1a1a1a!important;
  flex-shrink:0!important;
}
#dsp-ppt-thumbs{
  flex:1!important;
  overflow-y:auto!important;
  padding:10px 8px!important;
  display:flex!important;
  flex-direction:column!important;
  gap:8px!important;
}
#dsp-ppt-thumbs::-webkit-scrollbar{width:4px!important;}
#dsp-ppt-thumbs::-webkit-scrollbar-track{background:#2d2e30!important;}
#dsp-ppt-thumbs::-webkit-scrollbar-thumb{background:#434655!important;border-radius:3px!important;}

/* Thumbnail item - Stitch style */
.dsp-thumb-item{
  display:flex!important;
  flex-direction:column!important;
  align-items:flex-start!important;
  cursor:pointer!important;
  position:relative!important;
}
.dsp-thumb-num{
  font-size:9px!important;
  color:#9aa0a6!important;
  margin-bottom:3px!important;
  padding-left:2px!important;
  font-family:sans-serif!important;
}
.dsp-thumb-item.dsp-thumb-active .dsp-thumb-num{color:#8ab4f8!important;}
.dsp-thumb-box{
  width:100%!important;
  aspect-ratio:16/9;/* overridden by JS per layout */
  border-radius:3px!important;
  overflow:hidden!important;
  border:2px solid transparent!important;
  position:relative!important;
  background:#1B2A4A;/* default navy, overridden by JS theme */
  box-shadow:0 1px 3px rgba(0,0,0,.3)!important;
  transition:border-color .15s!important;
  font-size:3.5px!important;
}
.dsp-thumb-box *{
  font-family:-apple-system,BlinkMacSystemFont,sans-serif!important;
  box-sizing:border-box!important;
  line-height:1.3!important;
}
.dsp-thumb-item:hover .dsp-thumb-box{border-color:#5f6368!important;}
.dsp-thumb-item.dsp-thumb-active .dsp-thumb-box{
  border-color:#8ab4f8!important;
  box-shadow:0 0 0 3px rgba(138,180,248,.2)!important;
}

#dsp-ppt-panel-left-footer{
  padding:8px!important;
  border-top:1px solid #3c4043!important;
  flex-shrink:0!important;
}
#dsp-ppt-panel-left-footer button{
  width:100%!important;
  padding:8px!important;
  border:1px dashed #5f6368!important;
  border-radius:6px!important;
  background:transparent!important;
  color:#9ca3af!important;
  font-size:11px!important;
  cursor:pointer!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:4px!important;
  transition:all .15s!important;
}
#dsp-ppt-panel-left-footer button:hover{border-color:#1a73e8!important;color:#8ab4f8!important;}

/* Right canvas panel */
#dsp-ppt-panel-right{
  flex:1!important;
  display:flex!important;
  flex-direction:column!important;
  overflow:hidden!important;
}
#dsp-ppt-canvas-area{
  flex:1!important;
  overflow:auto!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  padding:32px!important;
  background:#3c4043!important;
}
#dsp-ppt-slide-outer{
  transform-origin:center center!important;
  transition:transform .15s!important;
}
#dsp-ppt-main-slide{
  width:900px;/* set by JS via getLayoutDims() */
  height:506px;/* set by JS via getLayoutDims() */
  overflow:hidden!important;
  position:relative!important;
  box-shadow:0 2px 8px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.5)!important;
  background:#1B2A4A;/* default navy, set by JS per theme */
  flex-shrink:0!important;
  border-radius:2px!important;
}
#dsp-ppt-main-slide *,
#dsp-ppt-main-slide *::before,
#dsp-ppt-main-slide *::after{
  font-family:-apple-system,BlinkMacSystemFont,sans-serif!important;
  box-sizing:border-box!important;
}

/* Editable elements */
.dsp-editable{cursor:text!important;outline:none!important;}
.dsp-editable:hover{outline:2px solid rgba(138,180,248,.5)!important;}
.dsp-editable:focus{outline:2px solid #8ab4f8!important;background:rgba(138,180,248,.06)!important;}
.dsp-img-zone{cursor:pointer!important;position:relative!important;}
.dsp-img-zone::after{
  content:"Click to change image"!important;
  position:absolute!important;inset:0!important;
  background:rgba(0,0,0,.55)!important;
  color:#fff!important;font-size:11px!important;font-weight:600!important;
  display:flex!important;align-items:center!important;justify-content:center!important;
  opacity:0!important;transition:opacity .2s!important;
  font-family:sans-serif!important;
}
.dsp-img-zone:hover::after{opacity:1!important;}

/* Bottom bar - Stitch pill style */
#dsp-ppt-bottom-bar{
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  padding:12px 16px!important;
  background:#202124!important;
  border-top:1px solid #2a2a2a!important;
  gap:0!important;
  flex-shrink:0!important;
}
#dsp-bottom-pill{
  display:inline-flex!important;
  align-items:center!important;
  background:#202124!important;
  border:1px solid #2a2a2a!important;
  border-radius:100px!important;
  padding:6px 16px!important;
  gap:8px!important;
}
.dsp-ppt-navbtn{
  width:28px!important;height:28px!important;
  border-radius:50%!important;
  border:none!important;
  background:transparent!important;
  color:#9ca3af!important;
  font-size:16px!important;
  cursor:pointer!important;
  display:flex!important;align-items:center!important;justify-content:center!important;
  transition:color .15s!important;
}
.dsp-ppt-navbtn:hover{color:#e8eaed!important;}
#dsp-ppt-nav-lbl{
  font-size:12px!important;
  font-weight:600!important;
  color:#e8eaed!important;
  min-width:64px!important;
  text-align:center!important;
}
.dsp-bottom-divider{
  width:1px!important;height:16px!important;
  background:#3c4043!important;
  margin:0 4px!important;
}
.dsp-zoom-btn{
  width:24px!important;height:24px!important;
  border:none!important;background:transparent!important;
  color:#9ca3af!important;font-size:16px!important;
  cursor:pointer!important;
  display:flex!important;align-items:center!important;justify-content:center!important;
  border-radius:4px!important;
  transition:color .15s!important;
}
.dsp-zoom-btn:hover{color:#e8eaed!important;}
#dsp-ppt-zoom-lbl{
  font-size:12px!important;
  color:#e8eaed!important;
  min-width:36px!important;
  text-align:center!important;
  font-weight:500!important;
}

/* Image modal */
#dsp-img-modal{
  display:none!important;
  position:fixed!important;inset:0!important;
  background:rgba(0,0,0,.65)!important;
  backdrop-filter:blur(4px)!important;
  z-index:110000!important;
  align-items:center!important;justify-content:center!important;
}
#dsp-img-modal.dsp-open{display:flex!important;}
#dsp-img-modal-card{
  background:#1d1f27!important;
  border:1px solid #2a2a2a!important;
  border-radius:12px!important;
  width:480px!important;
  max-width:92vw!important;
  overflow:hidden!important;
  box-shadow:0 8px 40px rgba(0,0,0,.6)!important;
}
#dsp-img-modal-head{
  padding:16px 20px!important;
  border-bottom:1px solid #2a2a2a!important;
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  background:#11131b!important;
}
#dsp-img-modal-head h3{margin:0!important;font-size:14px!important;font-weight:600!important;color:#e8eaed!important;}
#dsp-img-modal-close{
  background:none!important;border:none!important;
  color:#9ca3af!important;cursor:pointer!important;
  font-size:20px!important;line-height:1!important;
  transition:color .15s!important;
}
#dsp-img-modal-close:hover{color:#e8eaed!important;}
#dsp-img-modal-body{padding:20px!important;}
#dsp-img-modal-body label{
  display:block!important;
  font-size:10px!important;font-weight:700!important;
  letter-spacing:.7px!important;text-transform:uppercase!important;
  color:#6b7280!important;margin-bottom:6px!important;
}
#dsp-img-prompt{
  width:100%!important;
  background:#0d0d0d!important;
  border:1.5px solid #2a2a2a!important;
  border-radius:8px!important;
  padding:12px!important;
  font-size:13px!important;
  color:#e8eaed!important;
  resize:vertical!important;
  min-height:90px!important;
  outline:none!important;
  transition:border-color .15s!important;
}
#dsp-img-prompt:focus{border-color:#1a73e8!important;}
#dsp-img-modal-footer{
  padding:14px 20px!important;
  background:#11131b!important;
  border-top:1px solid #2a2a2a!important;
  display:flex!important;
  justify-content:flex-end!important;
  gap:10px!important;
}
.dsp-img-modal-btns button{
  padding:8px 20px!important;
  border-radius:6px!important;
  font-size:13px!important;
  cursor:pointer!important;
  font-weight:500!important;
}

/* Build overlay — Live Preview */
#dsp-build-overlay{
  display:none!important;
  position:fixed!important;inset:0!important;
  background:#202124!important;
  z-index:110000!important;
  flex-direction:column!important;
  align-items:stretch!important;
}
#dsp-build-overlay.dsp-open{display:flex!important;}

/* Top banner */
#dsp-build-banner{
  display:flex!important;
  align-items:center!important;
  gap:10px!important;
  padding:10px 20px!important;
  background:#1a1a2e!important;
  border-bottom:1px solid #2a2a2a!important;
  font-size:13px!important;
  font-weight:600!important;
  color:#8ab4f8!important;
  flex-shrink:0!important;
}
.dsp-build-spinner-sm{
  width:16px!important;height:16px!important;
  border:2px solid #2a2a4a!important;
  border-top-color:#8ab4f8!important;
  border-radius:50%!important;
  animation:dspSpin .8s linear infinite!important;
  flex-shrink:0!important;
}

/* Preview area */
#dsp-build-preview-wrap{
  display:flex!important;
  flex:1!important;
  overflow:hidden!important;
  gap:0!important;
}

/* Left thumbnails column */
#dsp-build-thumbs-col{
  width:140px!important;
  flex-shrink:0!important;
  background:#2d2e30!important;
  border-right:1px solid #1a1a1a!important;
  overflow-y:auto!important;
  padding:10px 8px!important;
  display:flex!important;
  flex-direction:column!important;
  gap:8px!important;
}
#dsp-build-thumbs-col::-webkit-scrollbar{width:3px!important;}
#dsp-build-thumbs-col::-webkit-scrollbar-thumb{background:#444!important;border-radius:3px!important;}
.dsp-build-thumb{
  width:100%!important;
  border-radius:4px!important;
  overflow:hidden!important;
  border:2px solid transparent!important;
  background:#1a1a2e!important;
  aspect-ratio:16/9!important;
  flex-shrink:0!important;
  position:relative!important;
  font-size:3.5px!important;
  transition:border-color .2s!important;
  opacity:0.4!important;
}
.dsp-build-thumb.dsp-bt-ready{
  opacity:1!important;
  border-color:#8ab4f8!important;
}
.dsp-build-thumb-num{
  position:absolute!important;
  bottom:3px!important;left:4px!important;
  font-size:8px!important;
  color:rgba(255,255,255,.7)!important;
  font-weight:600!important;
}

/* Main preview column */
#dsp-build-main-col{
  flex:1!important;
  display:flex!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:center!important;
  padding:24px!important;
  gap:16px!important;
  overflow:hidden!important;
}
#dsp-build-main-slide{
  width:min(800px,90%)!important;
  aspect-ratio:16/9!important;
  border-radius:4px!important;
  overflow:hidden!important;
  box-shadow:0 4px 24px rgba(0,0,0,.6)!important;
  background:#1B2A4A!important;
  font-size:8px!important;
  position:relative!important;
  transition:background .3s!important;
}
#dsp-build-progress-row{
  display:flex!important;
  flex-direction:column!important;
  align-items:center!important;
  gap:8px!important;
  width:min(600px,90%)!important;
}
#dsp-build-msg{font-size:13px!important;font-weight:600!important;color:#e8eaed!important;text-align:center!important;}
#dsp-bprog-wrap{width:100%!important;height:4px!important;background:#2a2a2a!important;border-radius:100px!important;overflow:hidden!important;}
#dsp-bprog{height:100%!important;background:linear-gradient(90deg,#1a73e8,#8ab4f8)!important;border-radius:100px!important;width:5%!important;transition:width .6s ease!important;}
#dsp-blog{font-size:11px!important;color:#6b7280!important;text-align:center!important;}

/* Add Slide AI Modal */
#dsp-addslide-modal{
  display:none!important;
  position:fixed!important;inset:0!important;
  background:rgba(0,0,0,.65)!important;
  backdrop-filter:blur(4px)!important;
  z-index:110000!important;
  align-items:center!important;justify-content:center!important;
}
#dsp-addslide-modal.dsp-open{display:flex!important;}
#dsp-addslide-card{
  background:#1d1f27!important;
  border:1px solid #2a2a2a!important;
  border-radius:10px!important;
  width:480px!important;max-width:92vw!important;
  overflow:hidden!important;
  box-shadow:0 8px 40px rgba(0,0,0,.6)!important;
}
#dsp-addslide-head{
  padding:16px 20px!important;
  border-bottom:1px solid #2a2a2a!important;
  display:flex!important;align-items:center!important;justify-content:space-between!important;
  background:#11131b!important;
}
#dsp-addslide-head h3{margin:0!important;font-size:14px!important;font-weight:600!important;color:#e8eaed!important;}
#dsp-addslide-close{background:none!important;border:none!important;color:#9ca3af!important;cursor:pointer!important;font-size:20px!important;line-height:1!important;}
#dsp-addslide-close:hover{color:#e8eaed!important;}
#dsp-addslide-body{padding:20px!important;}
#dsp-addslide-body label{display:block!important;font-size:10px!important;font-weight:700!important;letter-spacing:.7px!important;text-transform:uppercase!important;color:#6b7280!important;margin-bottom:6px!important;}
#dsp-addslide-input{
  width:100%!important;background:#0d0d0d!important;border:1.5px solid #2a2a2a!important;border-radius:8px!important;
  padding:12px!important;font-size:13px!important;color:#e8eaed!important;outline:none!important;
}
#dsp-addslide-input:focus{border-color:#1a73e8!important;}
#dsp-addslide-foot{
  padding:14px 20px!important;background:#11131b!important;border-top:1px solid #2a2a2a!important;
  display:flex!important;justify-content:flex-end!important;gap:10px!important;
}

/* Toast notification */
#dsp-toast{
  position:fixed!important;
  bottom:24px!important;
  left:50%!important;
  transform:translateX(-50%) translateY(20px)!important;
  background:#1d2b1d!important;
  border:1.5px solid #10b981!important;
  border-radius:100px!important;
  padding:12px 24px!important;
  display:flex!important;
  align-items:center!important;
  gap:10px!important;
  font-size:13px!important;
  font-weight:600!important;
  color:#6ee7b7!important;
  box-shadow:0 4px 24px rgba(0,0,0,.5)!important;
  z-index:200000!important;
  opacity:0!important;
  transition:opacity .3s,transform .3s!important;
  pointer-events:none!important;
  white-space:nowrap!important;
}
#dsp-toast.dsp-toast-show{
  opacity:1!important;
  transform:translateX(-50%) translateY(0)!important;
}
#dsp-toast-icon{font-size:16px!important;}

/* Done screen */
#dsp-done{
  min-height:100vh!important;
  background:#0a0a0a!important;
  display:none!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:center!important;
  padding:40px 20px!important;
  text-align:center!important;
}
#dsp-done.dsp-active{display:flex!important;}
.dsp-dnbtn{
  padding:11px 22px!important;
  border-radius:8px!important;
  font-size:13px!important;
  font-weight:700!important;
  cursor:pointer!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:6px!important;
  text-decoration:none!important;
  transition:all .15s!important;
}
</style>';

// HTML
echo '<div id="dsp-wrap">

<!-- ==================== HOME SCREEN ==================== -->
<div id="dsp-home" class="dsp-scr dsp-active">

  <div id="dsp-home-hero">
    <h1>What would you like to create today?</h1>
    <p>Describe your presentation and AI will generate a professional deck in seconds.</p>
  </div>

  <div id="dsp-mode-row">
    <div class="dsp-mode dsp-m-on" id="dsp-m-ai" onclick="dspMode(\'ai\')">
      <span class="dsp-mode-title">&#10024; Update with AI</span>
      <span class="dsp-mode-sub">AI rewrites &amp; enhances your content</span>
    </div>
    <div class="dsp-mode" id="dsp-m-keep" onclick="dspMode(\'preserve\')">
      <span class="dsp-mode-title">&#9998; Preserve My Text</span>
      <span class="dsp-mode-sub">Keep your exact words, AI formats only</span>
    </div>
  </div>

  <div id="dsp-pbox">
    <textarea id="dsp-prompt" rows="5" placeholder="E.g. A 10-slide pitch deck for a sustainable coffee startup focusing on ethical sourcing and premium branding..." oninput="dspChar()"></textarea>
    <div id="dsp-pbox-footer">
      <span id="dsp-pbox-tip">Tip: Type &ldquo;Slide 1:&rdquo; to auto-switch to Preserve mode</span>
      <span id="dsp-char">0/3000</span>
    </div>
  </div>

  <div id="dsp-pills-row">
    <button type="button" class="dsp-pill" id="dsp-pill-slides" onclick="dspPillTog(this)">
      <span>&#128202; <span id="dsp-pill-slides-lbl">10 cards</span></span>
      <span class="dsp-pill-arrow">&#9660;</span>
      <div class="dsp-pill-menu" id="dsp-pill-slides-menu">'.$slide_opts.'</div>
    </button>
    <button type="button" class="dsp-pill" id="dsp-pill-design" onclick="dspPillTog(this)">
      <span>&#127912; <span id="dsp-pill-design-lbl">Navy Pro</span></span>
      <span class="dsp-pill-arrow">&#9660;</span>
      <div class="dsp-pill-menu" id="dsp-pill-design-menu">'.$theme_opts.'</div>
    </button>
    <button type="button" class="dsp-pill" id="dsp-pill-layout" onclick="dspPillTog(this)">
      <span>&#128208; <span id="dsp-pill-layout-lbl">16:9</span></span>
      <span class="dsp-pill-arrow">&#9660;</span>
      <div class="dsp-pill-menu" id="dsp-pill-layout-menu">
        <div class="dsp-pill-opt dsp-pill-opt-on" onclick="dspPillSet(\'layout\',\'16:9\',\'16:9\')">16:9 Landscape</div>
      </div>
    </button>
    <button type="button" class="dsp-pill" id="dsp-pill-lang" onclick="dspPillTog(this)">
      <span>&#127760; <span id="dsp-pill-lang-lbl">English</span></span>
      <span class="dsp-pill-arrow">&#9660;</span>
      <div class="dsp-pill-menu" id="dsp-pill-lang-menu">'.$lang_opts.'</div>
    </button>
    </div>

  <input type="hidden" id="dsp-slides" value="10"/>
  <input type="hidden" id="dsp-design" value="navy-pro"/>
  <input type="hidden" id="dsp-layout" value="16:9"/>
  <input type="hidden" id="dsp-lang" value="English"/>
  <input type="hidden" id="dsp-imgs" value="0"/>

  <div id="dsp-costbar">
    <span id="dsp-costsumm">10 slides &middot; Navy Pro &middot; AI Mode</span>
    <span id="dsp-costamt">20 credits <span id="dsp-costhave">(You have '.$cr_fmt.'cr)</span></span>
  </div>

  <button id="dsp-genbtn" type="button" onclick="dspGen()">
    &#10024; <span id="dsp-genbtn-lbl">Generate Presentation</span>
  </button>

</div><!-- #dsp-home -->

<!-- ==================== LOADING SCREEN ==================== -->
<div id="dsp-loading" class="dsp-scr">
  <div class="dsp-spinner">
    <div class="dsp-r1"></div>
    <div class="dsp-r2"></div>
    <div class="dsp-ric" id="dsp-lic">&#128202;</div>
  </div>
  <h3 id="dsp-ltitle">Creating Presentation...</h3>
  <p id="dsp-lsub"></p>
  <div id="dsp-steps">
    <div class="dsp-step" id="dsp-s0"><div class="dsp-stic">&#129504;</div><span class="dsp-stxt" id="dsp-s0t">Processing content...</span></div>
    <div class="dsp-step" id="dsp-s1"><div class="dsp-stic">&#128203;</div><span class="dsp-stxt">Writing slides...</span></div>
    <div class="dsp-step" id="dsp-s2"><div class="dsp-stic">&#127912;</div><span class="dsp-stxt">Applying design...</span></div>
    <div class="dsp-step" id="dsp-s3"><div class="dsp-stic">&#9989;</div><span class="dsp-stxt">Finalising...</span></div>
  </div>
  <div id="dsp-prog-wrap"><div id="dsp-prog"></div></div>
  <button id="dsp-cancel-btn" onclick="dspShow(\'dsp-home\')">CANCEL</button>
</div><!-- #dsp-loading -->

<!-- ==================== EDITOR (dummy container for dspShow compat) ==================== -->
<div id="dsp-editor" class="dsp-scr"></div>

<!-- ==================== EDITOR FULLSCREEN OVERLAY ==================== -->
<div id="dsp-editor-fullscreen">

  <!-- Toolbar -->
  <div id="dsp-ppt-toolbar">
    <button class="dsp-tb-back" onclick="dspEditorClose()">
      <svg viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
      Back
    </button>
    <div id="dsp-tb-divider"></div>
    <span id="dsp-etitle" class="dsp-tb-title">Presentation</span>
    <span id="dsp-imgstatus-tb"></span>
    <button id="dsp-savebtn" type="button" onclick="dspSaveDraft()" class="dsp-tb-build" style="background:#374151;margin-right:6px;">&#128190; Save</button>
    <button id="dsp-buildbtn" type="button" onclick="dspBuild()" class="dsp-tb-build">&#11015; Download PPTX</button>
    <button id="dsp-tb-sharebtn" type="button" onclick="dspSharePPT()" class="dsp-tb-build" style="background:#16a34a;margin-left:6px;">&#128279; Share</button>
  </div>

  <!-- Workspace -->
  <div id="dsp-ppt-workspace">

    <!-- Left panel -->
    <div id="dsp-ppt-panel-left">
      <div id="dsp-ppt-panel-left-header">SLIDES</div>
      <div id="dsp-ppt-thumbs"></div>
      <div id="dsp-ppt-panel-left-footer">
        <button onclick="dspAddSlide()">&#43; Add slide</button>
      </div>
    </div>

    <!-- Canvas area -->
    <div id="dsp-ppt-panel-right">
      <div id="dsp-ppt-canvas-area">
        <div id="dsp-ppt-slide-outer">
          <div id="dsp-ppt-main-slide"></div>
        </div>
      </div>

      <!-- Bottom bar - pill style -->
      <div id="dsp-ppt-bottom-bar">
        <div id="dsp-bottom-pill">
          <button class="dsp-ppt-navbtn" onclick="dspPptNav(-1)">&#10094;</button>
          <span id="dsp-ppt-nav-lbl">1 / 1</span>
          <button class="dsp-ppt-navbtn" onclick="dspPptNav(1)">&#10095;</button>
          <div class="dsp-bottom-divider"></div>
          <button class="dsp-zoom-btn" onclick="dspZoom(-0.1)">&#8722;</button>
          <span id="dsp-ppt-zoom-lbl">100%</span>
          <button class="dsp-zoom-btn" onclick="dspZoom(0.1)">&#43;</button>
        </div>
      </div>
    </div>

  </div><!-- #dsp-ppt-workspace -->

  <!-- Image change modal -->
  <div id="dsp-img-modal">
    <div id="dsp-img-modal-card">
      <div id="dsp-img-modal-head">
        <h3>Generate Image with AI</h3>
        <button id="dsp-img-modal-close" onclick="dspImgModalClose()">&#10005;</button>
      </div>
      <div id="dsp-img-modal-body">
        <label>Image Description</label>
        <textarea id="dsp-img-prompt" placeholder="E.g. A futuristic city at night with glowing blue skyline..."></textarea>
      </div>
      <div id="dsp-img-modal-footer">
        <div class="dsp-img-modal-btns">
          <button onclick="dspImgModalClose()" style="border:1px solid #2a2a2a;background:#111;color:#9ca3af;">Cancel</button>
          <button onclick="dspImgModalGenerate()" style="background:#1a73e8;color:#fff;border:none;" id="dsp-img-modal-btn">&#10024; Generate</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Build overlay — Live Preview -->
  <div id="dsp-build-overlay">
    <div id="dsp-build-banner">
      <div class="dsp-build-spinner-sm"></div>
      <span id="dsp-build-banner-msg">&#10024; AI Generating: Don\'t close this tab while generation is in progress.</span>
    </div>
    <div id="dsp-build-preview-wrap">
      <div id="dsp-build-thumbs-col">
        <div id="dsp-build-thumbs-list"></div>
      </div>
      <div id="dsp-build-main-col">
        <div id="dsp-build-main-slide"></div>
        <div id="dsp-build-progress-row">
          <div id="dsp-bprog-wrap"><div id="dsp-bprog"></div></div>
          <span id="dsp-build-msg">Building PPTX...</span>
        </div>
        <div id="dsp-blog"></div>
      </div>
    </div>
  </div>

  <!-- Add Slide AI Modal -->
  <div id="dsp-addslide-modal">
    <div id="dsp-addslide-card">
      <div id="dsp-addslide-head">
        <h3>&#10024; Add Slide with AI</h3>
        <button id="dsp-addslide-close" onclick="dspAddSlideClose()">&#10005;</button>
      </div>
      <div id="dsp-addslide-body">
        <label>What should this slide be about?</label>
        <input id="dsp-addslide-input" type="text" placeholder="e.g. Revenue growth in Q3 2024, Customer testimonials, Team introduction..." 
          onkeydown="if(event.key===&#39;Enter&#39;)dspAddSlideGenerate()"/>
        <div style="margin-top:14px;display:flex;align-items:center;justify-content:flex-end;">
          <span id="dsp-addslide-cost" style="font-size:11px;color:#6b7280;background:#111;padding:4px 10px;border-radius:100px;border:1px solid #2a2a2a;">5 credits</span>
        </div>
        <div style="margin-top:8px;font-size:11px;color:#6b7280;">Want an image on this slide? Add one afterward from the layout panel on the right.</div>
      </div>
      <div id="dsp-addslide-foot">
        <button onclick="dspAddSlideClose()" style="padding:8px 16px;border-radius:6px;border:1px solid #2a2a2a;background:#111;color:#9ca3af;cursor:pointer;font-size:13px;">Cancel</button>
        <button id="dsp-addslide-btn" onclick="dspAddSlideGenerate()" style="padding:8px 20px;border-radius:6px;background:#1a73e8;color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600;">&#10024; Generate Slide</button>
      </div>
    </div>
  </div>

  <!-- Hidden compat elements -->
  <div id="dsp-list" style="display:none;"></div>
  <div id="dsp-preview" style="display:none;"></div>
  <div id="dsp-buildpanel" style="display:none;"><p></p><h4></h4><div id="dsp-btxt"></div></div>
  <div id="dsp-imgstatus" style="display:none;"></div>
  <div id="dsp-ppt-edit-panel" style="display:none;"></div>

</div><!-- #dsp-editor-fullscreen -->

<!-- ==================== DONE SCREEN ==================== -->
<div id="dsp-done" class="dsp-scr">
  <div style="font-size:52px;margin-bottom:12px;">&#127881;</div>
  <h3 style="font-size:22px;font-weight:800;color:#e8eaed;margin:0 0 8px;">PPTX Ready!</h3>
  <p style="font-size:13px;color:#6b7280;margin:0 0 24px;">Your file is ready to download.</p>
  <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
    <a id="dsp-dllink" href="#" download class="dsp-dnbtn" style="background:#2563eb;color:#fff;">&#11015; Download PPTX</a>
    <button id="dsp-sharebtn" onclick="dspSharePPT()" class="dsp-dnbtn" style="background:#16a34a;color:#fff;border:none;">&#128279; Share Link</button>
    <button onclick="dspReturnToEditor()" class="dsp-dnbtn" style="border:1.5px solid #2a2a2a;background:#111;color:#9ca3af;">&#9998; Edit More</button>
    <button onclick="dspShow(\'dsp-home\')" class="dsp-dnbtn" style="background:#1d4ed8;color:#fff;border:none;">&#43; New</button>
  </div>
</div>

<!-- Toast notification -->
<div id="dsp-toast"><span id="dsp-toast-icon">&#10003;</span> <span id="dsp-toast-msg">PPTX downloaded successfully!</span></div>

</div>';

    if ( function_exists('ds_ppt_ui_emit_js') ) {
        ds_ppt_ui_emit_js($nonce, $purl, $bldurl, $imgurl, $cr);
    } else {
        echo '<div style="padding:14px;background:#1a0000;border:1px solid #ef4444;color:#fca5a5;border-radius:8px;font-family:sans-serif;font-size:13px;">Snippet 12d (UI JS) is not active. Activate it in WP Code Snippets.</div>';
    }
    return ob_get_clean();
});
