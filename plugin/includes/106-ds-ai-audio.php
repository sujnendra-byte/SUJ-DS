<?php
/**
 * Code Snippets export — DS AI Audio
 *
 * Snippet ID : 787
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-101
 * Modified   : 2026-09-09 02:45:35
 * Load order : 106 of 144
 */
/**
* Snippet Name: DS AI Audio
* Shortcode: [ds_ai_audio]
* Run: Everywhere
*
* SELF-CONTAINED — does NOT touch or depend on any existing snippet's code.
* Draws its OWN copy of the tab bar (Images / Presentation / Video / Audio)
* so the page looks consistent with AI Studio, but that's a fresh block of
* markup here, not a shared include — nothing in any other "DS AI *"
* snippet is read, called, or modified. Class names are prefixed dsaa-/
* dsaaBar/dsaaTab so they never collide with the AI Video / AI Images
* stylesheets on the same site.
*
* Setup: create a new WP page (e.g. /ai-studio/ai-audio/) whose content is
* just [ds_ai_audio]. Nothing to change on your existing AI Studio / AI
* Images / AI Presentation / AI Video pages.
*
* Three tools in one studio:
* - Generate Music — free-form tags/lyrics/duration, ACE-Step 1.5.
* - Text to Speech — MiniMax Speech 02 HD, 17 curated voices.
* - Background Music — structured Purpose/Mood/Style/Intensity/Vocals/
* Duration controls (+ optional video upload for duration-matching —
* the file is analyzed server-side only, never on the frontend). This
* is the SAME ACE-Step 1.5 model as Generate Music, just driven by a
* generated prompt instead of free text, so users never need to know
* any music terminology.
*
* Requires (all new snippets added this session, none pre-existing touched):
* - ds_get_credits() (DS Credit Core — read-only, already on your site)
* - /ds/v1/generate-music, /ds/v1/poll-music, /ds/v1/generate-speech,
* /ds/v1/poll-speech, /ds/v1/download-audio, /ds/v1/rehost-audio,
* /ds/v1/my-audio, /ds/v1/analyze-video (DS AI Audio REST)
* - ds_get_music_cost() / ds_get_speech_cost() (DS AI Audio API - Part A)
*/

if ( ! defined('ABSPATH') ) exit;

/* Signed-out visitors get the same sign-in card as the AI Studio page instead of
   a bare "Please log in." line. Shared by AI Video, AI Audio and My Creations. */
if ( ! function_exists( 'ds_guest_login_card' ) ) {
function ds_guest_login_card( $title, $text ) {
    $here = get_permalink();
    $back = $here ? $here : home_url( '/ai-studio/' );
    $url  = home_url( '/login?redirect_to=' . urlencode( $back ) );
    return '<div style="display:flex;align-items:center;justify-content:center;min-height:65vh;background:#0a0a0a;">'
        . '<div style="text-align:center;padding:52px 36px;background:#111;border-radius:24px;border:1px solid #1e1e1e;max-width:400px;">'
        . '<div style="width:60px;height:60px;background:#2563eb;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">'
        . '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="#fff"/></svg>'
        . '</div>'
        . '<h3 style="font-size:22px;font-weight:800;color:#fff;margin:0 0 10px;">' . esc_html( $title ) . '</h3>'
        . '<p style="font-size:14px;color:#6b7280;margin:0 0 26px;line-height:1.6;">' . esc_html( $text ) . '</p>'
        . '<a href="' . esc_url( $url ) . '" style="display:inline-block;background:#2563eb;color:#fff;padding:13px 32px;border-radius:100px;font-size:14px;font-weight:700;text-decoration:none;">Login / Sign Up Free</a>'
        . '</div></div>';
}
}

add_shortcode('ds_ai_audio', 'ds_ai_audio_shortcode');
function ds_ai_audio_shortcode() {

if ( ! is_user_logged_in() ) {
return ds_guest_login_card('Login to Use AI Audio', 'Create music, voiceovers and sound effects.');
}

$uid = get_current_user_id();
$credits = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : 0;
$nonce = wp_create_nonce('wp_rest');
$rest = trailingslashit( rest_url('ds/v1') );
$rest_hub = trailingslashit( rest_url('ds-hub/v1') );
$aud_music_tiers = function_exists('ds_get_audio_music_tiers') ? array_map('intval', ds_get_audio_music_tiers()) : array(1,2,3);
$aud_speech_tiers = function_exists('ds_get_audio_speech_tiers') ? array_map('intval', ds_get_audio_speech_tiers()) : array(2,4,8,12);
$aud_platform = function_exists('ds_get_audio_platform_charge') ? (int)ds_get_audio_platform_charge() : 1;

$voices = array(
'Wise_Woman' => 'Wise Woman', 'Friendly_Person' => 'Friendly Person', 'Inspirational_girl' => 'Inspirational Girl',
'Deep_Voice_Man' => 'Deep Voice Man', 'Calm_Woman' => 'Calm Woman', 'Casual_Guy' => 'Casual Guy',
'Lively_Girl' => 'Lively Girl', 'Patient_Man' => 'Patient Man', 'Young_Knight' => 'Young Knight',
'Determined_Man' => 'Determined Man', 'Lovely_Girl' => 'Lovely Girl', 'Decent_Boy' => 'Decent Boy',
'Imposing_Manner' => 'Imposing Manner', 'Elegant_Man' => 'Elegant Man', 'Abbess' => 'Abbess',
'Sweet_Girl_2' => 'Sweet Girl', 'Exuberant_Girl' => 'Exuberant Girl',
);

ob_start();
?>
<!-- DS AI Audio Studio — self-contained, own tab bar copy, own styles. -->
<style>
.dsaaBar{display:flex;align-items:center;gap:6px;flex-wrap:wrap;background:#111;border:1px solid #1e1e1e;border-radius:12px;padding:8px;margin:0 0 18px}
.dsaaBar a.dstv-btn{display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;color:#94a3b8;font-size:14px;font-weight:600;text-decoration:none;transition:.15s}
.dsaaBar a.dstv-btn:hover{background:#161616;color:#e5e7eb}
.dsaaBar a.dstv-btn.on{background:#2563eb;color:#fff}
.dsaaWrap{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#e5e7eb;box-sizing:border-box;padding:0 4px 60px;background:#0a0a0a}
body.page-id-15301 .ast-container{max-width:none!important;width:100%!important;margin-left:0!important;margin-right:0!important;padding-left:0!important;padding-right:0!important}
.dst-bar{display:flex;gap:14px;margin:24px auto 28px;justify-content:center;flex-wrap:wrap}
.dst-btn{padding:10px 22px;border-radius:3px;border:none;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:.15s;letter-spacing:.3px;text-transform:uppercase;text-decoration:none}
.dst-btn.on{background:#2563eb;color:#fff}
.dst-btn:not(.on){background:#161616;color:#555;border:1px solid #1e1e1e}
.dst-btn:not(.on):hover{color:#9ca3af;background:#1a1a1a}
.dsAI-bc{padding:9px 12px;border-bottom:1px solid #141414;display:flex;align-items:center;justify-content:space-between;background:#080808;flex-shrink:0;margin-bottom:20px}
.dsaaTop{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.dsaaTitle{font-size:22px;font-weight:800;margin:0}
.dsaaCredits{display:flex;align-items:center;gap:6px;background:#111;border:1px solid #1e1e1e;border-radius:999px;padding:8px 14px;font-size:14px;font-weight:600;color:#e8442a}
.dsaaGrid{display:grid;grid-template-columns:360px 1fr;gap:20px;align-items:stretch}
@media (max-width:980px){.dsaaGrid{grid-template-columns:1fr}}
.dsaaSide{background:#111;border:1px solid #1e1e1e;border-radius:14px;padding:16px;position:sticky;top:16px;height:calc(100vh - 32px);overflow-y:auto;align-self:stretch;display:flex;flex-direction:column;scrollbar-width:thin;scrollbar-color:#1a1a1a transparent}
.dsaaSide::-webkit-scrollbar{width:3px}
.dsaaSide::-webkit-scrollbar-thumb{background:#1e1e1e;border-radius:3px}
@media (max-width:980px){.dsaaSide{position:static;height:auto;max-height:none;overflow-y:visible}}
.dsaaTabs{display:flex;background:#0a0a0a;border:1px solid #1e1e1e;border-radius:10px;padding:4px;margin-bottom:16px;gap:4px}
.dsaaTabBtn{flex:1;text-align:center;padding:7px 2px;border-radius:8px;font-size:10px;line-height:1.25;letter-spacing:-.01em;font-weight:700;color:#94a3b8;cursor:pointer;background:none;border:none;transition:.15s}
.dsaaTabBtn.on{background:#2563eb;color:#fff}
.dsaaPane{display:none}
.dsaaPane.on{display:flex;flex-direction:column;flex:1;min-height:0}
.dsaaLbl{display:block;font-size:12.5px;font-weight:700;color:#94a3b8;margin:14px 0 6px}
.dsaaLbl:first-child{margin-top:0}
.dsaaHint{font-size:11.5px;color:#6b7280;margin-top:4px;line-height:1.4}
.dsaaInput,.dsaaSelect,.dsaaTextarea{width:100%;background:#0a0a0a;border:1px solid #1f222b;border-radius:8px;color:#e5e7eb;padding:10px 12px;font-size:13.5px;font-family:inherit;box-sizing:border-box}
.dsaaSelect{color-scheme:dark}
.dsaaSelect option{color:#111827;background:#ffffff}
.dsaaInput:focus,.dsaaSelect:focus,.dsaaTextarea:focus{outline:none;border-color:#2563eb}
.dsaaTextarea{resize:vertical;min-height:70px;flex:1 1 auto}
.dsaaRow2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.dsaaRange{-webkit-appearance:none;appearance:none;width:100%;height:4px;background:#374151;border-radius:999px;outline:none;border:none;box-shadow:none}.dsaaRange::-webkit-slider-runnable-track{height:4px!important;background:#374151!important;border-radius:999px!important;box-shadow:none!important;border:none!important}.dsaaRange::-webkit-slider-thumb{-webkit-appearance:none!important;appearance:none!important;width:16px!important;height:16px!important;border-radius:50%!important;background:#2563eb!important;cursor:pointer;margin-top:-6px!important;border:none!important;box-shadow:none!important}.dsaaRange::-moz-range-track{height:4px!important;background:#374151!important;border-radius:999px!important;box-shadow:none!important;border:none!important}.dsaaRange::-moz-range-thumb{width:16px!important;height:16px!important;border-radius:50%!important;background:#2563eb!important;border:none!important;cursor:pointer;box-shadow:none!important}
.dsaaRangeRow{display:flex;align-items:center;gap:10px}
.dsaaRangeVal{font-size:12.5px;font-weight:700;color:#e5e7eb;min-width:46px;text-align:right}
.dsaaToggle{display:flex;align-items:center;justify-content:space-between;background:#0a0a0a;border:1px solid #1f222b;border-radius:8px;padding:10px 12px;margin-top:14px}
.dsaaToggle span{font-size:13px;font-weight:600;color:#e5e7eb}
.dsaaSwitch{position:relative;width:38px;height:22px;flex:none}
.dsaaSwitch input{opacity:0;width:0;height:0}
.dsaaSlider{position:absolute;inset:0;background:#374151;border-radius:999px;cursor:pointer;transition:.15s}
.dsaaSlider:before{content:"";position:absolute;width:16px;height:16px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.15s}
.dsaaSwitch input:checked + .dsaaSlider{background:#2563eb}
.dsaaSwitch input:checked + .dsaaSlider:before{transform:translateX(16px)}
.dsaaUpload{border:1.5px dashed #1f222b;border-radius:10px;padding:14px;text-align:center;cursor:pointer;margin-top:6px;font-size:12.5px;color:#6b7280;transition:.15s}
.dsaaUpload:hover{border-color:#2563eb;color:#94a3b8}
.dsaaUpload.has{border-color:#2563eb;color:#e5e7eb}
.dsaaGenBtn{width:100%;margin-top:24px;padding:13px;border:none;border-radius:10px;background:linear-gradient(180deg,#f99c94 0%,#d2292c 100%);color:#fff;font-size:14.5px;font-weight:800;cursor:pointer;transition:.15s}
.dsaaGenBtn:hover{filter:brightness(1.06)}
.dsaaGenBtn:disabled{opacity:.55;cursor:not-allowed}
.dsaaErr{background:#2a1212;border:1px solid #5c1f1f;color:#fca5a5;border-radius:8px;padding:9px 12px;font-size:12.5px;margin-top:12px;display:none}
.dsaaFeed{background:#111;border:1px solid #1e1e1e;border-radius:14px;padding:16px;min-height:400px;max-height:calc(100vh - 32px);overflow-y:auto;scrollbar-width:thin;scrollbar-color:#1a1a1a transparent}
.dsaaFeed::-webkit-scrollbar{width:3px}
.dsaaFeed::-webkit-scrollbar-thumb{background:#1e1e1e;border-radius:3px}
@media (max-width:980px){.dsaaFeed{max-height:none}}
.dsaaFeedTop{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.dsaaFeedTitle{font-size:15px;font-weight:700}
.dsaaFilterRow{display:flex;gap:6px}
.dsaaFilterBtn{padding:6px 12px;border-radius:999px;border:1px solid #1f222b;background:#0a0a0a;color:#94a3b8;font-size:12px;font-weight:600;cursor:pointer}
.dsaaFilterBtn.on{background:#2563eb;border-color:#2563eb;color:#fff}
.dsaaEmpty{text-align:center;padding:60px 20px;color:#6b7280}
.dsaaEmpty svg{opacity:.35;margin-bottom:10px}
.dsaaCards{display:flex;flex-direction:column;gap:10px}
.dsaaCard{background:#0a0a0a;border:1px solid #1f222b;border-radius:12px;padding:14px}
.dsaaCard.pending{opacity:.75}
.dsaaCardRow{display:flex;gap:12px;align-items:flex-start}
.dsaaCardThumb{width:88px!important;height:88px!important;min-width:88px!important;min-height:88px!important;max-width:88px!important;max-height:88px!important;border-radius:50%!important;object-fit:cover!important;flex:none;background:#161616;border:1px solid #1f222b;display:block!important;margin:0!important;padding:0!important;animation:dsaaDiscSpin 9s linear infinite;animation-play-state:paused}
.dsaaCardThumb.dsaaSpinning{animation-play-state:running}
@keyframes dsaaDiscSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.dsaaThumbWrap{position:relative;flex:none;width:88px;height:88px}
.dsaaThumbPlay{position:absolute!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;width:38px!important;height:38px!important;min-width:38px!important;min-height:38px!important;max-width:38px!important;max-height:38px!important;padding:0!important;margin:0!important;border-radius:50%!important;background:rgba(0,0,0,.7)!important;border:2px solid #fff!important;color:#fff!important;display:flex!important;align-items:center!important;justify-content:center!important;cursor:pointer;line-height:1!important;box-shadow:none!important}
.dsaaThumbPlay svg{margin-left:1px}
.dsaaCardBody{flex:1;min-width:0}
.dsaaMoreWrap{position:relative;flex:none}
.dsaaMoreMenu{position:absolute;top:calc(100% + 6px);right:0;background:#161616;border:1px solid #262b36;border-radius:10px;padding:6px;min-width:150px;box-shadow:0 8px 24px rgba(0,0,0,.4);z-index:20;display:none}
.dsaaMoreMenu.open{display:block}
.dsaaMoreMenu button{display:block;width:100%;text-align:left;background:none;border:none;color:#e5e7eb;font-size:12.5px;padding:7px 9px;border-radius:6px;cursor:pointer}
.dsaaMoreMenu button:hover{background:#1f222b}
.dsaaMoreMenu button.danger{color:#f87171}
.dsaaIconBtn.active{color:#f43f5e;border-color:#f43f5e}
.dsaaCardTop{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}
.dsaaCardMeta{min-width:0;display:flex;align-items:center;gap:8px}
.dsaaCardKind{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;color:#2563eb;background:rgba(37,99,235,.12);padding:3px 8px;border-radius:999px;margin:0;flex:none}
.dsaaCardKind.speech{color:#a855f7;background:rgba(168,85,247,.12)}
.dsaaCardKind.bg{color:#10b981;background:rgba(16,185,129,.12)}
.dsaaCardLabel{font-size:13.5px;font-weight:700;color:#e5e7eb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px;min-width:0}
.dsaaCardDate{font-size:11px;color:#6b7280;text-align:right;margin-top:6px}
.dsaaBadge{font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap}
.dsaaBadge.temp{background:rgba(234,179,8,.15);color:#eab308}
.dsaaBadge.saved{background:rgba(34,197,94,.15);color:#22c55e}
.dsaaCardActions{display:flex;gap:6px;flex:none}
.dsaaIconBtn{width:30px!important;height:30px!important;min-width:30px!important;min-height:30px!important;max-width:30px!important;max-height:30px!important;padding:0!important;border-radius:8px!important;background:#161616!important;border:1px solid #1f222b!important;color:#94a3b8!important;display:flex!important;align-items:center!important;justify-content:center!important;cursor:pointer;box-shadow:none!important;margin:0!important}
.dsaaIconBtn svg{width:15px!important;height:15px!important;min-width:15px!important;min-height:15px!important;flex:none!important;display:block!important}
.dsaaIconBtn:hover{color:#e5e7eb;border-color:#2563eb}
.dsaaIconBtn.confirming{background:#3b1418;border-color:#f87171;color:#f87171}
.dsaaVolWrap{display:flex;align-items:center;gap:5px;flex:none}
.dsaaVolIcon{color:#94a3b8;display:flex;flex:none;cursor:pointer}
.dsaaVolIcon:hover{color:#e5e7eb}
.dsaaVolIcon svg{width:14px!important;height:14px!important;min-width:14px!important;min-height:14px!important;flex:none!important;display:block!important}
.dsaaVolSlider{-webkit-appearance:none!important;appearance:none!important;width:54px!important;height:4px!important;background:#2a2e38!important;border-radius:999px!important;outline:none!important;border:none!important;box-shadow:none!important;cursor:pointer}
.dsaaVolSlider::-webkit-slider-runnable-track{height:4px!important;background:#2a2e38!important;border-radius:999px!important;box-shadow:none!important;border:none!important}
.dsaaVolSlider::-webkit-slider-thumb{-webkit-appearance:none!important;appearance:none!important;width:11px!important;height:11px!important;border-radius:50%!important;background:#e5e7eb!important;cursor:pointer;margin-top:-3.5px!important;border:none!important;box-shadow:none!important}
.dsaaVolSlider::-moz-range-track{height:4px!important;background:#2a2e38!important;border-radius:999px!important;box-shadow:none!important;border:none!important}
.dsaaVolSlider::-moz-range-thumb{width:11px!important;height:11px!important;border-radius:50%!important;background:#e5e7eb!important;border:none!important;cursor:pointer;box-shadow:none!important}
.dsaaPlayer{display:flex;align-items:center;gap:10px}
.dsaaPlayBtn{width:38px;height:38px;border-radius:50%;background:#2563eb;border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:none}
.dsaaWave{flex:1;display:flex;align-items:center;justify-content:space-between;height:34px;cursor:pointer;overflow:visible;position:relative}
.dsaaWaveCursor{position:absolute;top:-7px;bottom:-7px;width:4px;border-radius:2px;background:#fff;pointer-events:none;left:0%;z-index:2;box-shadow:0 0 6px rgba(255,255,255,.8)}
.dsaaWave b{display:block;width:2px;min-width:2px;border-radius:2px;background:#2a2e38;transition:background .1s}
.dsaaWave b.fill{background:#2563eb}
.dsaaTime{font-size:11px;color:#6b7280;min-width:38px;text-align:right;flex:none}
.dsaaPendingRow{display:flex;align-items:center;gap:10px;color:#94a3b8;font-size:12.5px}
.dsaaSpin{width:16px;height:16px;border:2px solid #1f222b;border-top-color:#2563eb;border-radius:50%;animation:dsaaSpin .8s linear infinite;flex:none}
@keyframes dsaaSpin{to{transform:rotate(360deg)}}
.dsaaLoadMore{display:block;margin:14px auto 0;padding:9px 20px;border-radius:999px;background:#161616;border:1px solid #1f222b;color:#94a3b8;font-size:12.5px;font-weight:600;cursor:pointer}
.dsaaToast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#161616;border:1px solid #2563eb;color:#e5e7eb;padding:10px 18px;border-radius:999px;font-size:13px;z-index:9999;opacity:0;pointer-events:none;transition:opacity .2s}
.dsaaToast.show{opacity:1}
</style>

<div class="dsaaWrap">

<div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0 18px;flex-wrap:wrap;gap:12px;">
<div class="dst-bar" style="margin:0;">
<a class="dst-btn" href="<?php echo esc_url( home_url('/ai-studio/') ); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="2"></rect><path d="M3 16l5-5 4 4 3-3 4 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"></circle></svg> AI Images</a>
<a class="dst-btn" href="<?php echo esc_url( home_url('/ai-studio/ai-presentation/') ); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"></rect><line x1="8" y1="21" x2="16" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line><line x1="12" y1="17" x2="12" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line></svg> AI Presentation</a>
<a class="dst-btn" data-ds-video-tab="1" href="<?php echo esc_url( home_url('/ai-studio/ai-video/') ); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polygon points="5 3 19 12 5 21 5 3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></polygon></svg> AI Video</a>
<button class="dst-btn on" id="dst-audio" type="button" data-ds-audio-tab="1"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18V5l12-2v13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="2"></circle><circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="2"></circle></svg> AI Audio</button>
</div>
<div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
<a href="javascript:void(0)" style="width:34px;height:34px;border-radius:100px;background:#111;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;" onclick="if(window.dsmeOpen){window.dsmeOpen();}return false;" title="Expired Files"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></a>
<a href="javascript:void(0)" style="width:34px;height:34px;border-radius:100px;background:#111;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;" onclick="if(window.dsmsOpenSaved){window.dsmsOpenSaved();}return false;" title="Saved Files"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg></a>
<div style="background:#111;border:1px solid #1e1e1e;border-radius:100px;padding:7px 14px;display:flex;align-items:center;gap:6px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="#e8442a"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg><span id="dsaaCreditsVal" style="font-weight:800;color:#e8442a;font-size:14px;"><?php echo esc_html($credits); ?></span><span style="font-size:11px;color:#374151;">cr</span></div>
<a href="<?php echo esc_url( home_url('/pricing') ); ?>" style="background:#2563eb;color:#fff;padding:8px 16px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">+ Buy</a>
<a href="<?php echo esc_url( home_url('/my-generated-images') ); ?>" style="padding:8px 14px;border-radius:100px;border:1px solid #1e1e1e;background:#111;color:#6b7280;font-size:13px;font-weight:600;text-decoration:none;">My Creations</a>
</div>
</div>

<div class="dsAI-bc"><span style="color:#64748b;font-size:11px;">AI Studio <span style="color:#334155;">/</span> <span style="color:#64748b;">Audio Studio</span></span></div>

<div class="dsaaGrid">

<div class="dsaaSide">
<div class="dsaaTabs">
<button class="dsaaTabBtn on" data-tab="music" type="button">Generate Music</button>
<button class="dsaaTabBtn" data-tab="speech" type="button">Text to Speech</button>
<button class="dsaaTabBtn" data-tab="bg" type="button">Background Music</button>
</div>

<!-- ── Generate Music ─────────────────────────────────────────── -->
<div class="dsaaPane on" id="dsaaPane-music">
<label class="dsaaLbl">Describe the music</label>
<textarea class="dsaaTextarea" id="dsaaTags" placeholder="e.g. upbeat pop with electric guitar and energetic drums"></textarea>
<div class="dsaaHint">Just describe the vibe in plain words — no music jargon needed.</div>

<label class="dsaaLbl">Lyrics (optional)</label>
<textarea class="dsaaTextarea" id="dsaaLyrics" placeholder="Leave blank for instrumental, or write your own lyrics"></textarea>

<div class="dsaaToggle">
<span>Instrumental only</span>
<label class="dsaaSwitch"><input type="checkbox" id="dsaaInstr" checked><span class="dsaaSlider"></span></label>
</div>

<label class="dsaaLbl">Duration</label>
<div class="dsaaRangeRow">
<input type="range" class="dsaaRange" id="dsaaDuration" min="5" max="240" step="5" value="60">
<span class="dsaaRangeVal"><span id="dsaaDurationVal">60</span>s</span>
</div>

<div style="flex:0 0 24px"></div>
<button class="dsaaGenBtn" id="dsaaGenMusicBtn" type="button">Generate Music</button>
<div class="dsaaErr" id="dsaaErrMusic"></div>
</div>

<!-- ── Text to Speech ─────────────────────────────────────────── -->
<div class="dsaaPane" id="dsaaPane-speech">
<label class="dsaaLbl">Text</label>
<textarea class="dsaaTextarea" id="dsaaText" placeholder="Type or paste up to 10,000 characters..." style="min-height:120px"></textarea>
<div class="dsaaHint"><span id="dsaaTextCount">0</span> / 10,000 characters</div>

<label class="dsaaLbl">Voice</label>
<select class="dsaaSelect" id="dsaaVoice">
<?php foreach ($voices as $vid => $vlabel): ?>
<option value="<?php echo esc_attr($vid); ?>"><?php echo esc_html($vlabel); ?></option>
<?php endforeach; ?>
</select>

<div class="dsaaRow2" style="margin-top:14px">
<div>
<label class="dsaaLbl" style="margin-top:0">Emotion</label>
<select class="dsaaSelect" id="dsaaEmotion">
<option value="neutral">Neutral</option>
<option value="happy">Happy</option>
<option value="sad">Sad</option>
<option value="angry">Angry</option>
<option value="fearful">Fearful</option>
<option value="disgusted">Disgusted</option>
<option value="surprised">Surprised</option>
</select>
</div>
<div>
<label class="dsaaLbl" style="margin-top:0">Format</label>
<select class="dsaaSelect" id="dsaaFormat">
<option value="mp3">MP3</option>
<option value="wav">WAV</option>
<option value="flac">FLAC</option>
</select>
</div>
</div>

<label class="dsaaLbl">Speed</label>
<div class="dsaaRangeRow">
<input type="range" class="dsaaRange" id="dsaaSpeed" min="0.5" max="2" step="0.1" value="1">
<span class="dsaaRangeVal"><span id="dsaaSpeedVal">1.0</span>x</span>
</div>

<div style="flex:0 0 24px"></div>
<button class="dsaaGenBtn" id="dsaaGenSpeechBtn" type="button">Generate Speech</button>
<div class="dsaaErr" id="dsaaErrSpeech"></div>
</div>

<!-- ── Background Music ───────────────────────────────────────── -->
<div class="dsaaPane" id="dsaaPane-bg">
<label class="dsaaLbl" style="margin-top:0">Purpose</label>
<select class="dsaaSelect" id="dsaaPurpose">
<option value="vlog">Vlog</option>
<option value="corporate">Corporate</option>
<option value="podcast">Podcast Intro</option>
<option value="gaming">Gaming</option>
<option value="wedding">Wedding</option>
<option value="social">Social Media</option>
<option value="presentation">Presentation</option>
<option value="documentary">Documentary</option>
</select>

<label class="dsaaLbl">Mood</label>
<select class="dsaaSelect" id="dsaaMood">
<option value="uplifting">Uplifting</option>
<option value="calm">Calm</option>
<option value="energetic">Energetic</option>
<option value="emotional">Emotional</option>
<option value="dramatic">Dramatic</option>
<option value="happy">Happy</option>
<option value="mysterious">Mysterious</option>
</select>

<label class="dsaaLbl">Music Style</label>
<select class="dsaaSelect" id="dsaaStyle">
<option value="acoustic">Acoustic</option>
<option value="electronic">Electronic</option>
<option value="cinematic">Cinematic</option>
<option value="lofi">Lo-fi</option>
<option value="pop">Pop</option>
<option value="jazz">Jazz</option>
<option value="ambient">Ambient</option>
<option value="rock">Rock</option>
</select>

<label class="dsaaLbl">Intensity</label>
<select class="dsaaSelect" id="dsaaIntensity">
<option value="low">Low</option>
<option value="medium" selected>Medium</option>
<option value="high">High</option>
</select>

<div class="dsaaToggle">
<span>Vocals</span>
<label class="dsaaSwitch"><input type="checkbox" id="dsaaBgVocals"><span class="dsaaSlider"></span></label>
</div>
<div class="dsaaHint">Off = instrumental only (recommended so it never talks over your video).</div>

<label class="dsaaLbl">Match a video's length (optional)</label>
<div class="dsaaUpload" id="dsaaVideoUpload">
<input type="file" id="dsaaVideoFile" accept="video/*" style="display:none">
<span id="dsaaVideoUploadTxt">Click to upload a video — we'll match the duration</span>
</div>

<label class="dsaaLbl">Duration</label>
<div class="dsaaRangeRow">
<input type="range" class="dsaaRange" id="dsaaBgDuration" min="5" max="240" step="5" value="60">
<span class="dsaaRangeVal"><span id="dsaaBgDurationVal">60</span>s</span>
</div>

<label class="dsaaLbl">Output Format</label>
<select class="dsaaSelect" id="dsaaBgFormat">
<option value="mp3">MP3</option>
<option value="wav">WAV</option>
<option value="flac">FLAC</option>
</select>

<div style="flex:1"></div>
<button class="dsaaGenBtn" id="dsaaGenBgBtn" type="button">Generate Background Music</button>
<div class="dsaaErr" id="dsaaErrBg"></div>
</div>

</div>

<div class="dsaaFeed">
<div class="dsaaFeedTop">
<div class="dsaaFeedTitle" style="font-size:11px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:#64748b;">My Creations</div>
<div class="dsaaFilterRow">
<button class="dsaaFilterBtn on" data-kind="">All</button>
<button class="dsaaFilterBtn" data-kind="music">Music</button>
<button class="dsaaFilterBtn" data-kind="speech">Speech</button>
</div>
</div>
<div id="dsaaFeedBody">
<div class="dsaaEmpty">Loading your creations…</div>
</div>
<button class="dsaaLoadMore" id="dsaaLoadMore" style="display:none" type="button">Load more</button>
</div>

</div>
</div>

<div class="dsaaToast" id="dsaaToast"></div>

<div id="dsaaMktModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
<div style="background:#fffdf7;border-radius:14px;padding:26px 24px;max-width:380px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.4);">
<h3 style="font-size:20px;font-weight:800;color:#1a1a2e;margin:0 0 8px;letter-spacing:-.3px;">Submit to Marketplace</h3>
<p style="font-size:13.5px;color:#5a6478;margin:0 0 18px;line-height:1.55;">Share your audio and earn credits on approval.</p>
<p style="font-size:12px;color:#94a3b8;margin:0 0 8px;line-height:1.5;">Your audio will be submitted using the original prompt as the title.</p>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:11px 13px;font-size:12px;color:#15803d;margin-bottom:14px;line-height:1.6;">
Earn <strong>2 credits</strong> back when admin approves.
</div>
<label style="color:#3d4659;display:flex;width:100%;align-items:center;gap:10px;margin:14px 0 18px;font-size:13px;line-height:1.5;cursor:pointer;">
<input type="checkbox" id="dsaaMktConsent" style="width:18px;height:18px;flex-shrink:0;margin:0;accent-color:#d97706;cursor:pointer;"/>
<span style="flex:1;color:#3d4659;">I confirm I have the right to submit this AI-generated audio to the Designstager Marketplace and grant Designstager a license to display and distribute it.</span>
</label>
<div style="display:flex;gap:8px;">
<button onclick="dsaaMktClose()" style="flex:1;padding:12px;border-radius:8px;background:#ece6d6;color:#3d4659;font-size:13px;font-weight:700;border:none;cursor:pointer;">Cancel</button>
<button id="dsaaMktSubmitBtn" onclick="dsaaMktSubmit()" style="flex:1;padding:12px;border-radius:8px;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;opacity:.5;pointer-events:none;">Submit to Marketplace</button>
</div>
</div>
</div>

<script>
(function(){
var REST = <?php echo wp_json_encode($rest); ?>;
var NONCE = <?php echo wp_json_encode($nonce); ?>;
var REST_HUB = <?php echo wp_json_encode($rest_hub); ?>;
var AUD_MUSIC_TIERS = <?php echo wp_json_encode($aud_music_tiers); ?>;
var AUD_SPEECH_TIERS = <?php echo wp_json_encode($aud_speech_tiers); ?>;
var AUD_PLATFORM = <?php echo (int)$aud_platform; ?>;
function dsaaCostMusic(dur){ var d=Math.max(5,parseInt(dur,10)||0); var base; if(d<=80){base=AUD_MUSIC_TIERS[0];} else if(d<=160){base=AUD_MUSIC_TIERS[1];} else {base=AUD_MUSIC_TIERS[2];} return base+AUD_PLATFORM; }
function dsaaCostSpeech(text){ var len=Math.max(1,(text||'').length); if(len<=500){return AUD_SPEECH_TIERS[0];} else if(len<=2000){return AUD_SPEECH_TIERS[1];} else if(len<=5000){return AUD_SPEECH_TIERS[2];} else {return AUD_SPEECH_TIERS[3];} }
function dsaaLabelMusic(){ var b=document.getElementById('dsaaGenMusicBtn'); if(b) b.textContent='Generate Music ('+dsaaCostMusic(document.getElementById('dsaaDuration').value)+' cr)'; }
function dsaaLabelSpeech(){ var b=document.getElementById('dsaaGenSpeechBtn'); if(b) b.textContent='Generate Speech ('+dsaaCostSpeech(document.getElementById('dsaaText').value)+' cr)'; }
function dsaaLabelBg(){ var b=document.getElementById('dsaaGenBgBtn'); if(b) b.textContent='Generate Background Music ('+dsaaCostMusic(document.getElementById('dsaaBgDuration').value)+' cr)'; }
dsaaLabelMusic(); dsaaLabelSpeech(); dsaaLabelBg();
var currentAudio = null;
var feedOffset = 0;
var feedKind = '';
var feedItems = [];
var pendingPolls = {};

function $(id){ return document.getElementById(id); }
function toast(msg){
var t = $('dsaaToast'); t.textContent = msg; t.classList.add('show');
clearTimeout(t._h); t._h = setTimeout(function(){ t.classList.remove('show'); }, 2600);
}
function esc(s){ return (s||'').toString().replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
function fmtTime(sec){ sec = Math.max(0, Math.floor(sec||0)); var m = Math.floor(sec/60), s = sec%60; return (m<10?'0':'')+m + ':' + (s<10?'0':'')+s; }

var mktState = { fileId: 0, btn: null };
window.dsaaMktOpen = function(fileId, btnEl){
mktState.fileId = fileId; mktState.btn = btnEl;
var cb = $('dsaaMktConsent'); if (cb) cb.checked = false;
var sbtn = $('dsaaMktSubmitBtn');
sbtn.textContent = 'Submit to Marketplace'; sbtn.style.opacity = '.5'; sbtn.style.pointerEvents = 'none';
$('dsaaMktModal').style.display = 'flex';
};
window.dsaaMktClose = function(){
$('dsaaMktModal').style.display = 'none';
};
window.dsaaMktSubmit = function(){
if (!mktState.fileId) return;
var sbtn = $('dsaaMktSubmitBtn');
sbtn.textContent = 'Submitting…'; sbtn.style.pointerEvents = 'none'; sbtn.style.opacity = '.6';
fetch(REST + 'creator-from-audio', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE }, body: JSON.stringify({ file_id: mktState.fileId, consent: true }) })
.then(function(r){ return r.json().catch(function(){ return null; }).then(function(j){ return { ok: r.ok, body: j }; }); })
.then(function(res){
if (!res.ok) {
toast((res.body && res.body.message) || 'Could not submit — please try again.');
sbtn.textContent = 'Submit to Marketplace'; sbtn.style.pointerEvents = 'auto'; sbtn.style.opacity = '1';
return;
}
dsaaMktClose();
toast('Submitted to Creator Uploads for review.');
if (mktState.btn) mktState.btn.remove();
})
.catch(function(){ toast('Network error — please try again.'); sbtn.textContent = 'Submit to Marketplace'; sbtn.style.pointerEvents = 'auto'; sbtn.style.opacity = '1'; });
};
document.addEventListener('change', function(e){
if (e.target && e.target.id === 'dsaaMktConsent') {
var sbtn = $('dsaaMktSubmitBtn');
if (e.target.checked) { sbtn.style.opacity = '1'; sbtn.style.pointerEvents = 'auto'; }
else { sbtn.style.opacity = '.5'; sbtn.style.pointerEvents = 'none'; }
}
});

function apiJSON(path, body){
return fetch(REST + path, {
method: 'POST', credentials: 'same-origin',
headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE },
body: JSON.stringify(body || {}),
}).then(function(r){ return r.json(); });
}
function apiGET(path){
return fetch(REST + path, { credentials: 'same-origin', headers: { 'X-WP-Nonce': NONCE } }).then(function(r){ return r.json(); });
}

// ── Tab switching ──────────────────────────────────────────────────
document.querySelectorAll('.dsaaTabBtn').forEach(function(btn){
btn.addEventListener('click', function(){
document.querySelectorAll('.dsaaTabBtn').forEach(function(b){ b.classList.remove('on'); });
document.querySelectorAll('.dsaaPane').forEach(function(p){ p.classList.remove('on'); });
btn.classList.add('on');
$('dsaaPane-' + btn.getAttribute('data-tab')).classList.add('on');
});
});

// ── Range readouts ─────────────────────────────────────────────────
$('dsaaDuration').addEventListener('input', function(){ $('dsaaDurationVal').textContent = this.value; ; dsaaLabelMusic();});
$('dsaaBgDuration').addEventListener('input', function(){ $('dsaaBgDurationVal').textContent = this.value; ; dsaaLabelBg();});
$('dsaaSpeed').addEventListener('input', function(){ $('dsaaSpeedVal').textContent = parseFloat(this.value).toFixed(1); });
$('dsaaText').addEventListener('input', function(){ $('dsaaTextCount').textContent = this.value.length; ; dsaaLabelSpeech();});
$('dsaaInstr').addEventListener('change', function(){ $('dsaaLyrics').disabled = this.checked; $('dsaaLyrics').style.opacity = this.checked ? .5 : 1; });

function showErr(id, msg){ var e = $(id); e.textContent = msg; e.style.display = 'block'; }
function hideErr(id){ $(id).style.display = 'none'; }

function setCredits(v){ $('dsaaCreditsVal').textContent = v; }

// ── Generate Music ──────────────────────────────────────────────────
$('dsaaGenMusicBtn').addEventListener('click', function(){
hideErr('dsaaErrMusic');
var tags = $('dsaaTags').value.trim();
if (!tags) { showErr('dsaaErrMusic', 'Please describe the style of music you want.'); return; }
var btn = this; btn.disabled = true; btn.textContent = 'Generating…';
apiJSON('generate-music', {
mode: 'generate', tags: tags, lyrics: $('dsaaLyrics').value.trim(),
duration: parseInt($('dsaaDuration').value, 10), instrumental: $('dsaaInstr').checked,
}).then(function(res){
btn.disabled = false; dsaaLabelMusic();
if (res.error) { showErr('dsaaErrMusic', res.message || 'Something went wrong.'); return; }
if (typeof res.credits_left !== 'undefined') setCredits(res.credits_left);
addPendingCard(res.prediction_id, 'music', tags.slice(0,60) || 'Music track', 'music');
}).catch(function(){ btn.disabled = false; dsaaLabelMusic(); showErr('dsaaErrMusic', 'Network error — please try again.'); });
});

// ── Generate Speech ──────────────────────────────────────────────────
$('dsaaGenSpeechBtn').addEventListener('click', function(){
hideErr('dsaaErrSpeech');
var text = $('dsaaText').value.trim();
if (!text) { showErr('dsaaErrSpeech', 'Please enter some text to convert to speech.'); return; }
var btn = this; btn.disabled = true; btn.textContent = 'Generating…';
apiJSON('generate-speech', {
text: text, voice_id: $('dsaaVoice').value, speed: parseFloat($('dsaaSpeed').value),
emotion: $('dsaaEmotion').value, format: $('dsaaFormat').value,
}).then(function(res){
btn.disabled = false; dsaaLabelSpeech();
if (res.error) { showErr('dsaaErrSpeech', res.message || 'Something went wrong.'); return; }
if (typeof res.credits_left !== 'undefined') setCredits(res.credits_left);
addPendingCard(res.prediction_id, 'speech', text.slice(0,60), 'speech');
}).catch(function(){ btn.disabled = false; dsaaLabelSpeech(); showErr('dsaaErrSpeech', 'Network error — please try again.'); });
});

// ── Background Music video upload → server-side duration analysis ────
var bgDetectedDuration = null;
$('dsaaVideoUpload').addEventListener('click', function(){ $('dsaaVideoFile').click(); });
$('dsaaVideoFile').addEventListener('change', function(){
var f = this.files && this.files[0];
if (!f) return;
$('dsaaVideoUploadTxt').textContent = 'Analyzing "' + f.name + '"…';
$('dsaaVideoUpload').classList.add('has');
var fd = new FormData();
fd.append('video', f);
fetch(REST + 'analyze-video', { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': NONCE }, body: fd })
.then(function(r){ return r.json(); })
.then(function(res){
if (res.error) { $('dsaaVideoUploadTxt').textContent = res.message || 'Could not read video duration — set it manually below.'; return; }
bgDetectedDuration = res.duration;
$('dsaaBgDuration').value = res.duration;
$('dsaaBgDurationVal').textContent = res.duration;
$('dsaaVideoUploadTxt').textContent = '"' + f.name + '" — matched to ' + res.duration + 's';
}).catch(function(){ $('dsaaVideoUploadTxt').textContent = 'Upload failed — set the duration manually below.'; });
});

// ── Generate Background Music ─────────────────────────────────────────
$('dsaaGenBgBtn').addEventListener('click', function(){
hideErr('dsaaErrBg');
var btn = this; btn.disabled = true; btn.textContent = 'Generating…';
var purpose = $('dsaaPurpose').value, mood = $('dsaaMood').value;
apiJSON('generate-music', {
mode: 'background', purpose: purpose, mood: mood, style: $('dsaaStyle').value,
intensity: $('dsaaIntensity').value, instrumental: !$('dsaaBgVocals').checked,
duration: parseInt($('dsaaBgDuration').value, 10),
}).then(function(res){
btn.disabled = false; dsaaLabelBg();
if (res.error) { showErr('dsaaErrBg', res.message || 'Something went wrong.'); return; }
if (typeof res.credits_left !== 'undefined') setCredits(res.credits_left);
var label = (mood.charAt(0).toUpperCase()+mood.slice(1)) + ' ' + purpose + ' background music';
addPendingCard(res.prediction_id, 'music', label, 'bg');
}).catch(function(){ btn.disabled = false; dsaaLabelBg(); showErr('dsaaErrBg', 'Network error — please try again.'); });
});

// ── Pending card + polling ────────────────────────────────────────────
function addPendingCard(pid, kind, label, cssKind){
var body = $('dsaaFeedBody');
if (body.querySelector('.dsaaEmpty')) body.innerHTML = '<div class="dsaaCards" id="dsaaCardsList"></div>';
var list = $('dsaaCardsList') || (function(){ var d = document.createElement('div'); d.className='dsaaCards'; d.id='dsaaCardsList'; body.appendChild(d); return d; })();
var el = document.createElement('div');
el.className = 'dsaaCard pending';
el.id = 'dsaaCard-' + pid;
el.innerHTML = '<div class="dsaaCardTop"><div class="dsaaCardMeta"><span class="dsaaCardKind ' + (cssKind==='bg'?'bg':(kind==='speech'?'speech':'')) + '">' + (kind==='speech'?'Speech':(cssKind==='bg'?'Background Music':'Music')) + '</span><div class="dsaaCardLabel">' + esc(label) + '</div></div></div><div class="dsaaPendingRow"><div class="dsaaSpin"></div><span>Generating… this can take a minute or two</span></div>';
list.insertBefore(el, list.firstChild);
pollGeneration(pid, kind, label, cssKind, el, 0);
}

function pollGeneration(pid, kind, label, cssKind, el, attempt){
if (attempt > 90) { el.querySelector('.dsaaPendingRow span').textContent = 'Taking longer than expected — check back soon.'; return; }
var path = kind === 'speech' ? 'poll-speech' : 'poll-music';
apiJSON(path, { prediction_id: pid }).then(function(res){
if (res.status === 'succeeded' && res.audio && res.audio[0] && res.audio[0].url) {
var newItem = { url: res.audio[0].url, label: label, kind: kind, mode: cssKind, date: 'Just now', exp_badge: 'temp', prediction_id: pid };
renderResultCard(el, newItem);
newItem._rehostPromise = apiJSON('rehost-audio', { prediction_id: pid }).then(function(rh){
if (rh && rh.success) {
if (rh.file_id) newItem.file_id = rh.file_id;
if (rh.attachment_id) newItem.attachment_id = rh.attachment_id;
}
return rh;
}).catch(function(){ return null; });
} else if (res.status === 'failed') {
el.classList.remove('pending');
el.innerHTML = '<div class="dsaaCardTop"><div class="dsaaCardMeta"><span class="dsaaCardKind">' + (kind==='speech'?'Speech':'Music') + '</span><div class="dsaaCardLabel">' + esc(label) + '</div></div></div><div style="color:#fca5a5;font-size:12.5px">' + esc(res.error || 'Generation failed — credits were refunded.') + '</div>';
} else {
setTimeout(function(){ pollGeneration(pid, kind, label, cssKind, el, attempt + 1); }, 5000);
}
}).catch(function(){ setTimeout(function(){ pollGeneration(pid, kind, label, cssKind, el, attempt + 1); }, 5000); });
}

// ── Fake waveform bars (deterministic per-card, no CORS/analyser needed) ─
function playIconSvg(){ return '<svg width="13" height="13" viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg>'; }
function pauseIconSvg(){ return '<svg width="13" height="13" viewBox="0 0 24 24" fill="#fff"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg>'; }

function volIconSvg(){ return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 5V4L8 9H4z" fill="currentColor"/><path d="M16.5 8.5a5 5 0 010 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 6a9 9 0 010 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'; }
function volMuteIconSvg(){ return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 9v6h4l5 5V4L8 9H4z" fill="currentColor"/><line x1="16" y1="9" x2="21" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="21" y1="9" x2="16" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'; }

function thumbForKind(kindClass){
return 'https://designstager.com/wp-content/uploads/2026/08/image-1.png';
}

function waveformBars(seedStr){
var seed = 0; for (var i=0;i<seedStr.length;i++) seed = (seed * 31 + seedStr.charCodeAt(i)) >>> 0;
function rnd(){ seed = (seed * 1103515245 + 12345) >>> 0; return (seed % 1000) / 1000; }
var html = '';
for (var b = 0; b < 90; b++) { html += '<b style="height:' + Math.round(6 + rnd()*28) + 'px"></b>'; }
return html;
}

function renderResultCard(el, item){
el.classList.remove('pending');
var kindLabel = item.kind === 'speech' ? 'Speech' : (item.mode === 'bg' ? 'Background Music' : 'Music');
var kindClass = item.kind === 'speech' ? 'speech' : (item.mode === 'bg' ? 'bg' : '');
var badge = item.exp_badge === 'saved' ? '<span class="dsaaBadge saved">Saved</span>' : (item.exp_badge === 'temp' ? '<span class="dsaaBadge temp">Temporary</span>' : '');
el.innerHTML =
'<div class="dsaaCardRow">' +
'<div class="dsaaCardBody">' +
'<div class="dsaaCardTop">' +
'<div class="dsaaCardMeta"><span class="dsaaCardKind ' + kindClass + '">' + kindLabel + '</span><div class="dsaaCardLabel" title="' + esc(item.label) + '">' + esc(item.label) + '</div></div>' +
'<div style="display:flex;align-items:center;gap:8px">' + badge +
'<div class="dsaaCardActions">' +
(item.file_id && item.exp_badge !== 'saved' ? '<button class="dsaaIconBtn" data-act="save" title="Save to My Creations"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' : '') +
'<button class="dsaaIconBtn" data-act="dl" title="Download"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></button>' +
'<button class="dsaaIconBtn" data-act="share" title="Share"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2.2"/><circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2.2"/><circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2.2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2.2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2.2"/></svg></button>' +
((item.file_id && item.kind !== 'speech') ? '<button class="dsaaIconBtn" data-act="mkt" title="Submit to Marketplace"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>' : '') +
(item.file_id ? '<button class="dsaaIconBtn" data-act="delete" title="Delete"><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' : '') +
'</div></div></div>' +
'<div class="dsaaPlayer">' +
'<div class="dsaaThumbWrap">' +
'<img class="dsaaCardThumb" src="' + thumbForKind(kindClass) + '" alt="">' +
'<button class="dsaaThumbPlay" data-act="thumbplay" type="button">' + playIconSvg() + '</button>' +
'</div>' +
'<div class="dsaaWave" data-act="seek">' + waveformBars(item.label + item.date) + '<div class="dsaaWaveCursor"></div>' + '</div>' +
'<span class="dsaaTime">0:00</span>' +
'<div class="dsaaVolWrap">' +
'<span class="dsaaVolIcon" data-act="mute" title="Mute">' + volIconSvg() + '</span>' +
'<input type="range" class="dsaaVolSlider" min="0" max="1" step="0.01" value="1" data-act="volume">' +
'</div>' +
'</div>' +
'<div class="dsaaCardDate">' + esc(item.date || '') + '</div>' +
'</div></div>';

var audio = new Audio();
audio.preload = 'none';
var dsaaLazySrc = item.url ? (item.url + (item.url.indexOf('?') === -1 ? '?' : '&') + '_wpnonce=' + NONCE) : item.url;
var dsaaSrcLoaded = false;
var thumbPlayBtn = el.querySelector('[data-act="thumbplay"]');
var thumbImg = el.querySelector('.dsaaCardThumb');
var wave = el.querySelector('.dsaaWave');
var bars = wave.querySelectorAll('b');
var waveCursor = el.querySelector('.dsaaWaveCursor');
var timeEl = el.querySelector('.dsaaTime');
audio.addEventListener('loadedmetadata', function(){ if (!audio.currentTime) timeEl.textContent = fmtTime(audio.duration); });
var volSlider = el.querySelector('[data-act="volume"]');
var volIcon = el.querySelector('[data-act="mute"]');
if (volSlider && volIcon) {
audio.volume = 1;
volSlider.addEventListener('input', function(){
audio.volume = parseFloat(volSlider.value);
volIcon.innerHTML = audio.volume === 0 ? volMuteIconSvg() : volIconSvg();
});
volIcon.addEventListener('click', function(){
if (audio.volume > 0) {
volSlider.dataset.prev = audio.volume;
audio.volume = 0; volSlider.value = 0;
} else {
audio.volume = parseFloat(volSlider.dataset.prev || '1'); volSlider.value = audio.volume;
}
volIcon.innerHTML = audio.volume === 0 ? volMuteIconSvg() : volIconSvg();
});
}

function pauseAll(){ document.querySelectorAll('.dsaaCard audio').forEach(function(a){ a.pause(); }); document.querySelectorAll('.dsaaThumbPlay').forEach(function(b){ b.innerHTML = playIconSvg(); }); document.querySelectorAll('.dsaaCardThumb').forEach(function(t){ t.classList.remove('dsaaSpinning'); }); }
el.appendChild(audio);

function dsaaToggle(){
if (audio.paused) {
if (currentAudio && currentAudio !== audio) currentAudio.pause();
document.querySelectorAll('.dsaaThumbPlay').forEach(function(b){ b.innerHTML = playIconSvg(); });
document.querySelectorAll('.dsaaCardThumb').forEach(function(t){ t.classList.remove('dsaaSpinning'); });
if (!dsaaSrcLoaded && dsaaLazySrc) { audio.src = dsaaLazySrc; dsaaSrcLoaded = true; }
audio.play().catch(function(){ toast('Could not play this file.'); });
currentAudio = audio;
thumbPlayBtn.innerHTML = pauseIconSvg();
if (thumbImg) thumbImg.classList.add('dsaaSpinning');
} else {
audio.pause();
thumbPlayBtn.innerHTML = playIconSvg();
if (thumbImg) thumbImg.classList.remove('dsaaSpinning');
}
}
thumbPlayBtn.addEventListener('click', dsaaToggle);
audio.addEventListener('ended', function(){ thumbPlayBtn.innerHTML = playIconSvg(); if (thumbImg) thumbImg.classList.remove('dsaaSpinning'); setFill(0); timeEl.textContent = fmtTime(audio.duration); });
audio.addEventListener('timeupdate', function(){
var pct = audio.duration ? (audio.currentTime / audio.duration) : 0;
setFill(pct);
if (waveCursor) waveCursor.style.left = (pct*100) + '%';
timeEl.textContent = fmtTime(audio.duration ? (audio.duration - audio.currentTime) : 0);
});
function setFill(pct){
var n = Math.round(pct * bars.length);
bars.forEach(function(b, i){ b.classList.toggle('fill', i < n); });
}
function seekFromClientX(clientX){
if (!audio.duration) return;
var r = wave.getBoundingClientRect();
var pct = Math.min(1, Math.max(0, (clientX - r.left) / r.width));
audio.currentTime = pct * audio.duration;
setFill(pct);
if (waveCursor) waveCursor.style.left = (pct*100) + '%';
timeEl.textContent = fmtTime(audio.duration - audio.currentTime);
}
var waveDragging = false;
wave.addEventListener('mousedown', function(e){
if (!audio.duration) { toast('Still loading — try again in a moment.'); return; }
waveDragging = true;
seekFromClientX(e.clientX);
e.preventDefault();
});
document.addEventListener('mousemove', function(e){
if (waveDragging) seekFromClientX(e.clientX);
});
document.addEventListener('mouseup', function(){
waveDragging = false;
});
wave.addEventListener('touchstart', function(e){
if (!audio.duration) { toast('Still loading — try again in a moment.'); return; }
waveDragging = true;
seekFromClientX(e.touches[0].clientX);
}, {passive:true});
wave.addEventListener('touchmove', function(e){
if (waveDragging) seekFromClientX(e.touches[0].clientX);
}, {passive:true});
wave.addEventListener('touchend', function(){ waveDragging = false; });

el.querySelector('[data-act="dl"]').addEventListener('click', function(){
function doDownload(){
var dlUrl = item.file_id ? (REST + 'download-audio?file_id=' + item.file_id + '&_wpnonce=' + NONCE) : (REST + 'download-audio?url=' + encodeURIComponent(item.url) + '&_wpnonce=' + NONCE);
var a = document.createElement('a'); a.href = dlUrl; a.download = '';
document.body.appendChild(a); a.click(); a.remove();
}
if (!item.file_id && item._rehostPromise) {
toast('Preparing your file…');
item._rehostPromise.then(doDownload);
} else {
doDownload();
}
});
el.querySelector('[data-act="share"]').addEventListener('click', function(){
if (navigator.share) {
navigator.share({ title: item.label, url: item.url }).catch(function(){});
} else {
navigator.clipboard.writeText(item.url).then(function(){ toast('Link copied to clipboard.'); }).catch(function(){ toast(item.url); });
}
});
var delBtn = el.querySelector('[data-act="delete"]');
if (delBtn) {
var confirmingDelete = false;
delBtn.addEventListener('click', function(){
if (!confirmingDelete) {
confirmingDelete = true;
delBtn.classList.add('confirming');
delBtn.title = 'Click again to confirm';
setTimeout(function(){ confirmingDelete = false; delBtn.classList.remove('confirming'); delBtn.title = 'Delete'; }, 3000);
return;
}
delBtn.disabled = true;
fetch(REST_HUB + 'delete/' + item.file_id, { method: 'POST', headers: { 'X-WP-Nonce': NONCE } })
.then(function(r){ return r.json().catch(function(){ return null; }).then(function(j){ return { ok: r.ok, body: j }; }); })
.then(function(res){
if (!res.ok) {
toast((res.body && res.body.message) || 'Could not delete \u2014 please try again.');
delBtn.disabled = false; delBtn.classList.remove('confirming'); delBtn.title = 'Delete';
return;
}
toast('Deleted.');
el.remove();
})
.catch(function(){ toast('Network error \u2014 please try again.'); delBtn.disabled = false; delBtn.classList.remove('confirming'); delBtn.title = 'Delete'; });
});
}
var mktBtn = el.querySelector('[data-act="mkt"]');
if (mktBtn) {
mktBtn.addEventListener('click', function(){
dsaaMktOpen(item.file_id, mktBtn);
});
}
var saveBtn = el.querySelector('[data-act="save"]');
if (saveBtn) {
saveBtn.addEventListener('click', function(){
var b = this; b.disabled = true; b.style.opacity = '.5';
fetch(REST_HUB + 'save/' + item.file_id, { method: 'POST', headers: { 'X-WP-Nonce': NONCE } })
.then(function(r){ return r.json().catch(function(){ return null; }).then(function(j){ return { ok: r.ok, body: j }; }); })
.then(function(res){
if (!res.ok || !res.body || res.body.code) {
var msg = (res.body && res.body.message) || 'Could not save — please try again.';
toast(msg); b.disabled = false; b.style.opacity = '1';
return;
}
toast('Saved to My Creations.');
var actionsWrap = el.querySelector('.dsaaCardActions').parentElement;
var oldBadge = actionsWrap.querySelector('.dsaaBadge');
if (oldBadge) { oldBadge.className = 'dsaaBadge saved'; oldBadge.textContent = 'Saved'; }
else { var nb = document.createElement('span'); nb.className = 'dsaaBadge saved'; nb.textContent = 'Saved'; actionsWrap.insertBefore(nb, actionsWrap.firstChild); }
b.remove();
})
.catch(function(){ toast('Network error — please try again.'); b.disabled = false; b.style.opacity = '1'; });
});
}
}

// ── Feed: load from /my-audio ─────────────────────────────────────────
document.querySelectorAll('.dsaaFilterBtn').forEach(function(btn){
btn.addEventListener('click', function(){
document.querySelectorAll('.dsaaFilterBtn').forEach(function(b){ b.classList.remove('on'); });
btn.classList.add('on');
feedKind = btn.getAttribute('data-kind');
feedOffset = 0;
loadFeed(true);
});
});
$('dsaaLoadMore').addEventListener('click', function(){ loadFeed(false); });

function loadFeed(reset){
apiGET('my-audio?kind=' + encodeURIComponent(feedKind) + '&offset=' + feedOffset + '&limit=20').then(function(res){
if (!res.success) return;
var body = $('dsaaFeedBody');
if (reset) { body.innerHTML = '<div class="dsaaCards" id="dsaaCardsList"></div>'; }
var list = $('dsaaCardsList');
if (!list) { body.innerHTML = '<div class="dsaaCards" id="dsaaCardsList"></div>'; list = $('dsaaCardsList'); }
if (reset && (!res.audio || !res.audio.length)) {
body.innerHTML = '<div class="dsaaEmpty">No creations yet — generate your first track or voiceover on the left.</div>';
$('dsaaLoadMore').style.display = 'none';
return;
}
(res.audio || []).forEach(function(item){
if (item.status === 'pending') return; // still generating from a previous session — skip in the static list
var el = document.createElement('div');
el.className = 'dsaaCard';
list.appendChild(el);
renderResultCard(el, {
url: item.url, label: item.label || (item.kind === 'speech' ? 'Speech' : 'Music'),
kind: item.kind, mode: item.mode === 'background' ? 'bg' : '', date: item.date,
exp_badge: item.exp_badge, file_id: item.file_id, prediction_id: item.prediction_id,
});
});
feedOffset = res.next_offset;
$('dsaaLoadMore').style.display = (res.next_offset < res.total) ? 'block' : 'none';
}).catch(function(){});
}

loadFeed(true);

function dsaaEsc(s){ return String(s==null?'':s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
function dsaaFmtDate(s){ if(!s) return ''; try { return new Date(String(s).replace(' ','T')).toLocaleString(); } catch(e){ return s; } }

function dsaaExpBuildModal(){
  if (document.getElementById('dsaaExpOverlay')) return;
  var wrap = document.createElement('div');
  wrap.id = 'dsaaExpOverlay';
  wrap.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:100000;overflow:auto;padding:40px 16px;';
  wrap.innerHTML =
    '<div style="max-width:560px;margin:0 auto;background:#0d1117;border-radius:12px;padding:24px;color:#fff;">' +
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">' +
        '<h2 style="margin:0;font-size:18px;">Expired Audio Files</h2>' +
        '<button type="button" id="dsaaExpClose" style="background:none;border:none;color:#9ca3af;font-size:22px;cursor:pointer;line-height:1;">&times;</button>' +
      '</div>' +
      '<div id="dsaaExpBody"><div style="text-align:center;padding:40px;color:#64748b;">Loading...</div></div>' +
    '</div>';
  document.body.appendChild(wrap);
  wrap.addEventListener('click', function(e){ if (e.target === wrap) dsaaExpClose(); });
  document.getElementById('dsaaExpClose').addEventListener('click', dsaaExpClose);
}
function dsaaExpClose(){ var w = document.getElementById('dsaaExpOverlay'); if (w) w.style.display='none'; }
window.dsmeOpen = function(){
  dsaaExpBuildModal();
  var w = document.getElementById('dsaaExpOverlay');
  w.style.display = 'block';
  var body = document.getElementById('dsaaExpBody');
  body.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;">Loading...</div>';
  fetch(REST + 'my-expired-items', { headers: {'X-WP-Nonce': NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    var audio = (d && d.audio) || [];
    if (!audio.length) { body.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;">No expired audio files.</div>'; return; }
    var html = '';
    audio.forEach(function(a){
      html += '<div style="background:#161b22;border:1px solid #30363d;border-radius:8px;padding:12px 14px;margin-bottom:10px;">';
      html += '<div style="font-weight:600;font-size:13px;word-break:break-word;">' + dsaaEsc(a.filename||'Audio file') + '</div>';
      html += '<div style="font-size:11px;color:#8b949e;margin-top:4px;">Generated: ' + dsaaEsc(dsaaFmtDate(a.created_at)) + ' &middot; Expired: ' + dsaaEsc(dsaaFmtDate(a.expires_at)) + '</div>';
      html += '</div>';
    });
    body.innerHTML = html;
  })
  .catch(function(){ body.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;">Could not load expired files.</div>'; });
};

function dsaaSavBuildModal(){
  if (document.getElementById('dsaaSavOverlay')) return;
  var wrap = document.createElement('div');
  wrap.id = 'dsaaSavOverlay';
  wrap.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:100000;overflow:auto;padding:40px 16px;';
  wrap.innerHTML =
    '<div style="max-width:560px;margin:0 auto;background:#0d1117;border-radius:12px;padding:24px;color:#fff;">' +
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">' +
        '<h2 style="margin:0;font-size:18px;">Saved Audio Files</h2>' +
        '<button type="button" id="dsaaSavClose" style="background:none;border:none;color:#9ca3af;font-size:22px;cursor:pointer;line-height:1;">&times;</button>' +
      '</div>' +
      '<div id="dsaaSavBody"><div style="text-align:center;padding:40px;color:#64748b;">Loading...</div></div>' +
    '</div>';
  document.body.appendChild(wrap);
  wrap.addEventListener('click', function(e){ if (e.target === wrap) dsaaSavClose(); });
  document.getElementById('dsaaSavClose').addEventListener('click', dsaaSavClose);
}
function dsaaSavClose(){ var w = document.getElementById('dsaaSavOverlay'); if (w) w.style.display='none'; }
window.dsmsOpenSaved = function(){
  dsaaSavBuildModal();
  var w = document.getElementById('dsaaSavOverlay');
  w.style.display = 'block';
  var body = document.getElementById('dsaaSavBody');
  body.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;">Loading...</div>';
  fetch(REST + 'my-audio?offset=0&limit=1000', { headers: {'X-WP-Nonce': NONCE}, credentials: 'include' })
  .then(function(r){ return r.json(); })
  .then(function(d){
    var all = (d && d.success) ? (d.audio || []) : [];
    var saved = all.filter(function(a){ return a.exp_badge === 'saved'; });
    if (!saved.length) { body.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;">No saved audio files.</div>'; return; }
    var ids = [];
    saved.forEach(function(a){ if (a.file_id) ids.push(a.file_id); });
    var resolve = ids.length ? fetch(REST_HUB + 'files?ids=' + ids.join(','), { headers: {'X-WP-Nonce': NONCE}, credentials: 'include' }).then(function(r){ return r.json(); }).catch(function(){ return {}; }) : Promise.resolve({});
    resolve.then(function(files){
      var html = '';
      saved.forEach(function(a){
        var label = a.label || a.tags || (a.kind === 'speech' ? 'Text to Speech' : 'Music');
        var _url = (files && files[a.file_id]) ? files[a.file_id] : a.url;
        html += '<div style="background:#161b22;border:1px solid #30363d;border-radius:8px;padding:12px 14px;margin-bottom:10px;">';
        html += '<div style="font-weight:600;font-size:13px;word-break:break-word;margin-bottom:8px;">' + dsaaEsc(String(label).substring(0,80)) + '</div>';
        html += '<audio controls preload="none" src="' + _url + '" style="width:100%;"></audio>';
        html += '<div style="margin-top:8px;"><a href="' + _url + '" target="_blank" rel="noopener" style="color:#60a5fa;font-size:12px;text-decoration:none;">Download</a></div>';
        html += '</div>';
      });
      body.innerHTML = html;
    });
  })
  .catch(function(){ body.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;">Could not load saved files.</div>'; });
};
})();
</script>
</div>
<?php
return ob_get_clean();
}
