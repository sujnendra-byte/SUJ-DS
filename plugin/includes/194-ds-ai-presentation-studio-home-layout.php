<?php
/**
 * Code Snippets export — DS AI Presentation — Studio Home Layout
 *
 * Snippet ID : 960
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : ds-ppt, front-end
 * Modified   : 2026-10-02 07:08:59
 * Load order : 189 of 191
 */
/**
 * DS AI Presentation — Studio Home Layout
 * Restyles the AI Presentation home screen: two-column layout (prompt panel on the
 * left, Design Templates + Color & Typography on the right), dark panels that blend
 * with the page, a 3-slide sample of the picked template, and a My Presentations
 * grid with search, sort, slide-count badge and a bin icon on every thumbnail.
 * It only rearranges and restyles what the other snippets already output — no
 * existing snippet is changed. Deactivate this snippet to get the old page back.
 */
add_action( 'wp_head', function () { /* in the head: the layout is applied as soon as the section appears, so the old layout never shows */
	if ( is_admin() ) return;
	if ( function_exists( 'ds_tpl_should_print' ) && ! ds_tpl_should_print() ) return;
	?>
<style id="ds-studio-layout-css">
#dsp-home:not(.dssl-ready){opacity:0;animation:dsslIn .01s linear 6s forwards}
@keyframes dsslIn{to{opacity:1}}
#dsp-home.dssl-ready{opacity:1;transition:opacity .2s ease}
/* until the theme / template panels arrive: soft placeholders, and never show them outside the right column */
#dsp-home.dssl :not(#dssl-right)>#dste-sec,#dsp-home.dssl :not(#dssl-right)>#dsp-theme-section{display:none!important}
#dsp-home.dssl #dssl-right:not(:has(#dsp-theme-section))::before,#dsp-home.dssl #dssl-right:not(:has(#dste-sec))::after{content:"";display:block;border-radius:20px;background:linear-gradient(100deg,#141927 40%,#1a2133 50%,#141927 60%);background-size:200% 100%;border:1px solid rgba(255,255,255,.07);animation:dsslSk 1.2s linear infinite}
#dsp-home.dssl #dssl-right:not(:has(#dsp-theme-section))::before{height:300px}
#dsp-home.dssl #dssl-right:not(:has(#dste-sec))::after{height:520px}
@keyframes dsslSk{from{background-position:120% 0}to{background-position:-80% 0}}
#dsp-home.dssl{--p:#141927;--p2:#0d111b;--p3:#1a2030;--bd:rgba(255,255,255,.07);--tx:#e9ecf5;--mu:#8a93a8;--ac:#6ea8ff;max-width:1460px;margin:0 auto;padding:28px 24px 56px!important;box-sizing:border-box;position:relative;align-items:stretch!important;font-family:'Plus Jakarta Sans',Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
#dsp-home.dssl.dsp-active{display:block!important}
#dsp-home.dssl::before{content:"";position:absolute;left:0;right:0;top:0;height:460px;background:radial-gradient(55% 100% at 16% 0,rgba(190,40,70,.15),transparent 70%),radial-gradient(50% 100% at 86% 0,rgba(45,80,210,.17),transparent 70%);pointer-events:none;z-index:0}
#dsp-home.dssl #dsp-home-hero{display:none!important}
#dsp-home.dssl .dssl-top{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:28px;align-items:start;position:relative;z-index:1}
#dsp-home.dssl .dssl-right{display:flex;flex-direction:column;gap:28px;min-width:0}
#dsp-home.dssl .dssl-left,#dsp-home.dssl #dste-sec,#dsp-home.dssl #dsp-theme-section{background:var(--p)!important;border:1px solid var(--bd)!important;border-radius:20px!important;padding:28px!important;margin:0!important;max-width:none!important;width:100%!important;box-sizing:border-box!important;box-shadow:0 18px 48px rgba(0,0,0,.28);position:relative;min-width:0}
#dsp-home.dssl .dssl-ph{display:flex;align-items:center;gap:10px;font-size:19px;font-weight:600;color:var(--tx);margin:0 0 18px;line-height:1.3}
#dsp-home.dssl .dssl-ph svg{width:22px;height:22px;color:var(--ac);flex-shrink:0}
/* modes */
#dsp-home.dssl #dsp-mode-row{width:100%!important;max-width:none!important;margin:0 0 14px!important;gap:10px!important}
#dsp-home.dssl #dsp-mode-row>*{min-height:0!important;height:auto!important;padding:11px 14px!important;border-radius:12px!important;background:var(--p2)!important;border:1px solid var(--bd)!important;box-shadow:none!important}
#dsp-home.dssl #dsp-mode-row>.dsp-m-on{border-color:#3b6cf0!important;background:#131b33!important}
/* prompt */
#dsp-home.dssl #dsp-pbox{width:100%!important;max-width:none!important;margin:0 0 18px!important;background:var(--p2)!important;border:1px solid var(--bd)!important;border-radius:14px!important;padding:16px 16px 10px!important;box-sizing:border-box!important}
#dsp-home.dssl #dsp-prompt{min-height:330px!important;font-size:16px!important;line-height:1.65!important;color:var(--tx)!important;background:transparent!important;width:100%!important;box-sizing:border-box!important}
#dsp-home.dssl #dsp-prompt::placeholder{color:#5d667b!important}
#dsp-home.dssl #dsp-pbox-footer{display:flex!important;align-items:center!important;gap:16px!important;border-top:1px solid var(--bd)!important;padding-top:10px!important;margin-top:8px!important;flex-wrap:wrap}
#dsp-home.dssl #dsp-pbox-tip{display:none!important}
#dsp-home.dssl #dsdi-tip{background:var(--p3)!important;border:1px solid var(--bd)!important;border-radius:9px!important;padding:8px 13px!important;color:var(--tx)!important;font-size:14px!important;font-weight:600!important;text-transform:none!important;letter-spacing:0!important;margin:0!important;cursor:pointer}
#dsp-home.dssl .dssl-sample{color:var(--ac);font-size:14px;font-weight:600;text-decoration:underline;text-underline-offset:3px;cursor:pointer;background:none;border:0;padding:0;font-family:inherit;text-transform:none;letter-spacing:0}
#dsp-home.dssl #dsp-char{margin-left:auto!important;color:#b9c0d0!important;font-size:13.5px!important}
#dsp-home.dssl #dsp-char::after{content:" chars"}
/* directives */
#dsp-home.dssl #dsdi-ibox{width:100%!important;max-width:none!important;margin:0 0 22px!important;background:var(--p2)!important;border:1px solid var(--bd)!important;border-radius:14px!important;padding:18px 20px!important;box-sizing:border-box!important}
#dsp-home.dssl #dsdi-ibox label{display:flex!important;align-items:center;gap:9px;font-size:16.5px!important;font-weight:600!important;color:var(--tx)!important;margin:0 0 10px!important;text-transform:none!important;letter-spacing:0!important}
#dsp-home.dssl #dsdi-ibox label svg{width:19px;height:19px;color:#ff7b6b;flex-shrink:0}
#dsp-home.dssl #dsdi-ibox label span{color:var(--tx)!important;font-weight:600!important}
#dsp-home.dssl #dsdi-instr{font-size:15px!important;line-height:1.6!important;color:var(--tx)!important;min-height:52px!important}
#dsp-home.dssl #dsdi-instr::placeholder{color:#5d667b!important}
/* fields */
#dsp-home.dssl #dsp-pills-row{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:12px!important;width:100%!important;max-width:none!important;margin:0 0 22px!important;padding:0 0 24px!important;border-bottom:1px solid var(--bd);box-sizing:border-box!important}
#dsp-home.dssl .dssl-f{min-width:0}
#dsp-home.dssl .dssl-f>label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.07em;color:#c3c9d8;text-transform:uppercase;margin:0 0 9px}
#dsp-home.dssl .dssl-f .dsp-pill{width:100%!important;display:flex!important;justify-content:space-between!important;align-items:center!important;gap:8px;background:var(--p3)!important;border:1px solid var(--bd)!important;border-radius:10px!important;padding:11px 14px!important;color:var(--tx)!important;font-size:15px!important;font-weight:600!important;text-transform:none!important;letter-spacing:0!important;height:auto!important;min-height:0!important;margin:0!important;box-sizing:border-box!important;cursor:pointer}
#dsp-home.dssl .dssl-f .dsp-pill>span:first-child{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;text-transform:none!important}
#dsp-home.dssl .dssl-f .dsp-pill img.emoji{display:none!important}
#dsp-home.dssl .dssl-f .dsp-pill-arrow{font-size:9px!important;color:#b9c0d0!important;flex-shrink:0}
#dsp-home.dssl .dssl-f .dsp-pill.dsp-pill-open{border-color:#3b6cf0!important}
/* cost + generate (button colour left as it is) */
#dsp-home.dssl .dssl-go{display:flex;align-items:center;gap:18px}
#dsp-home.dssl #dsp-costbar{flex:1 1 auto!important;min-width:0;background:transparent!important;border:0!important;padding:0!important;margin:0!important;width:auto!important;max-width:none!important;justify-content:flex-start!important;gap:20px!important;flex-wrap:wrap;font-size:14.5px!important;color:#c9cfdd!important;box-shadow:none!important}
#dsp-home.dssl #dsp-costsumm{color:#c9cfdd!important;font-size:14.5px!important}
#dsp-home.dssl #dsp-costsumm::before{content:"";display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--ac);margin-right:8px;vertical-align:middle}
#dsp-home.dssl #dsp-costamt{color:#ff7b6b!important;font-size:14.5px!important;font-weight:600!important}
#dsp-home.dssl #dsp-genbtn{flex:0 0 46%!important;width:auto!important;max-width:none!important;margin:0!important;min-height:56px!important;border-radius:12px!important;font-size:16.5px!important;font-weight:800!important;letter-spacing:.03em!important;text-transform:uppercase!important;justify-content:center!important;gap:10px}
/* design templates */
#dsp-home.dssl #dste-sec h4,#dsp-home.dssl #dsp-theme-section h4{color:var(--tx)!important;font-size:19px!important;font-weight:600!important;margin:0 0 6px!important;line-height:1.3!important}
#dsp-home.dssl #dste-sec .dste-sub,#dsp-home.dssl #dsp-theme-section .dsp-th-hdr p{color:#aeb6c8!important;font-size:13.5px!important;margin:0!important}
#dsp-home.dssl #dste-sec .dste-hd,#dsp-home.dssl #dsp-theme-section .dsp-th-hdr{margin:0 0 20px!important;padding-right:150px}
#dsp-home.dssl #dste-sec .dste-foot,#dsp-home.dssl #dsp-theme-section .dste-foot{position:absolute!important;top:30px!important;right:28px!important;margin:0!important;padding:0!important;display:block!important;width:auto!important;text-align:right}
#dsp-home.dssl #dste-more,#dsp-home.dssl #dsp-th-more-btn{background:none!important;border:0!important;color:var(--ac)!important;font-size:14px!important;font-weight:600!important;padding:4px 0!important;margin:0!important;cursor:pointer;text-transform:none!important;letter-spacing:0!important;box-shadow:none!important;width:auto!important;min-height:0!important;border-radius:0!important}
#dsp-home.dssl #dste-more{font-size:0!important}
#dsp-home.dssl #dste-more::after{content:attr(data-l) "  \203A";font-size:14px}
#dsp-home.dssl #dsp-th-more-btn{font-size:0!important}
#dsp-home.dssl #dsp-th-more-btn::after{content:attr(data-l) "  \203A";font-size:14px}
#dsp-home.dssl #dste-grid{gap:14px!important}
#dsp-home.dssl .dste-card{background:var(--p3)!important;border:1px solid var(--bd)!important;border-radius:14px!important;padding:10px 10px 4px!important;box-shadow:none!important}
#dsp-home.dssl .dste-card:hover{border-color:rgba(110,168,255,.55)!important}
#dsp-home.dssl .dste-card .dste-th{border-radius:9px!important;overflow:hidden!important}
#dsp-home.dssl .dste-card .dste-nm{background:transparent!important;border:0!important;padding:10px 2px 8px!important;color:var(--tx)!important;font-size:14.5px!important;font-weight:600!important;display:flex;align-items:center;justify-content:space-between;gap:8px}
#dsp-home.dssl .dste-card .dste-nm>div{color:var(--tx)!important;font-size:14.5px!important;font-weight:600!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0}
#dsp-home.dssl .dste-card .dste-nm small{display:none!important}
#dsp-home.dssl .dste-card .dste-ck{display:none!important}
#dsp-home.dssl .dste-card.dste-on{border-color:#3b6cf0!important;box-shadow:0 0 0 1px #3b6cf0,0 0 30px rgba(59,108,240,.38)!important;background:#16203a!important}
#dsp-home.dssl .dste-card.dste-on .dste-nm>div{color:#8fb8ff!important}
#dsp-home.dssl .dste-card.dste-on .dste-ck{display:inline-flex!important;align-items:center;justify-content:center;width:18px;height:18px;border:1.5px solid #8fb8ff;border-radius:50%;color:#8fb8ff!important;background:transparent!important;font-size:10px!important;font-weight:800;flex-shrink:0;line-height:1}
#dsp-home.dssl #dste-note{color:#aeb6c8!important}
#dsp-home.dssl .dssl-arch{margin-top:18px;background:var(--p2);border:1px solid var(--bd);border-radius:14px;padding:14px}
#dsp-home.dssl .dssl-arch-h{display:flex;justify-content:space-between;align-items:center;gap:10px;margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:.07em;color:#d5daE6;text-transform:uppercase}
#dsp-home.dssl .dssl-arch-h span:last-child{color:var(--ac);text-transform:none;letter-spacing:0;font-size:13.5px;font-weight:600}
#dsp-home.dssl .dssl-arch-g{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
#dsp-home.dssl .dssl-at{position:relative;aspect-ratio:16/9;border-radius:9px;overflow:hidden;background:var(--p3);container-type:size}
/* compulsory picks */
#dsp-home.dssl #dste-sec h4::after,#dsp-home.dssl #dsp-theme-section h4::after{content:"\2713  Selected";display:inline-block;margin-left:10px;padding:2px 9px;border-radius:999px;font-size:11.5px;font-weight:700;letter-spacing:.03em;vertical-align:middle;background:rgba(52,211,153,.14);color:#6ee7b7}
#dsp-home.dssl #dste-sec.dssl-nopick h4::after,#dsp-home.dssl #dsp-theme-section.dssl-nopick h4::after{content:"Required";background:rgba(255,123,107,.16);color:#ff9d8f}
#dsp-home.dssl #dste-sec.dssl-nopick .dste-card.dste-on{border-color:var(--bd)!important;box-shadow:none!important;background:var(--p3)!important}
#dsp-home.dssl #dste-sec.dssl-nopick .dste-card.dste-on .dste-nm>div{color:var(--tx)!important}
#dsp-home.dssl #dste-sec.dssl-nopick .dste-card.dste-on .dste-ck{display:none!important}
#dsp-home.dssl #dsp-theme-section.dssl-nopick #dsp-th-grid .dsp-th-card.dsp-th-sel{border-color:transparent!important;background:var(--p3)!important;outline:1px solid var(--bd)}
#dsp-home.dssl #dsp-theme-section.dssl-nopick #dsp-th-grid .dsp-th-card.dsp-th-sel .dsp-th-name{color:var(--tx)!important}
#dsp-home.dssl #dsp-theme-section.dssl-nopick #dsp-th-grid .dsp-th-card.dsp-th-sel::after{color:var(--mu)!important}
#dsp-home.dssl .dssl-need{animation:dsslNeed .55s ease 0s 2;border-color:#ff7b6b!important;box-shadow:0 0 0 2px rgba(255,123,107,.55),0 18px 48px rgba(0,0,0,.28)!important}
@keyframes dsslNeed{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}
#dsp-home.dssl .dssl-hint{display:none;margin-top:12px;color:#ff9d8f;font-size:13.5px;font-weight:600}
#dsp-home.dssl #dsp-genbtn.dssl-wait{opacity:.6}
#dsp-home.dssl .dssl-arch-e{grid-column:1/-1;padding:22px 10px;text-align:center;color:var(--mu);font-size:13.5px}
/* scroll boxes */
#dsp-home.dssl #dste-sec:not(.dssl-open) #dste-grid.dssl-scroll,#dsp-home.dssl #dsp-theme-section:not(.dssl-open) #dsp-th-grid.dssl-scroll{max-height:var(--dssl-h,340px);overflow-y:auto;padding:4px 10px 4px 4px;margin:-4px -8px -4px -4px;scrollbar-width:thin;scrollbar-color:#3b4560 #0d111b}
#dsp-home.dssl #dste-grid.dssl-scroll::-webkit-scrollbar,#dsp-home.dssl #dsp-th-grid.dssl-scroll::-webkit-scrollbar{width:8px}
#dsp-home.dssl #dste-grid.dssl-scroll::-webkit-scrollbar-track,#dsp-home.dssl #dsp-th-grid.dssl-scroll::-webkit-scrollbar-track{background:#0d111b;border-radius:8px}
#dsp-home.dssl #dste-grid.dssl-scroll::-webkit-scrollbar-thumb,#dsp-home.dssl #dsp-th-grid.dssl-scroll::-webkit-scrollbar-thumb{background:#3b4560;border-radius:8px}
#dsp-home.dssl #dste-grid.dssl-scroll::-webkit-scrollbar-thumb:hover,#dsp-home.dssl #dsp-th-grid.dssl-scroll::-webkit-scrollbar-thumb:hover{background:#56638a}
/* colour & typography */
#dsp-home.dssl #dsp-th-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:12px!important}
#dsp-home.dssl #dsp-th-grid .dsp-th-card{background:var(--p3)!important;border:2px solid transparent!important;outline:1px solid var(--bd);outline-offset:-1px;border-radius:12px!important;padding:9px 10px 9px!important;min-width:0;flex:none!important}
#dsp-home.dssl #dsp-th-grid .dsp-th-swatch{height:24px!important;min-height:0!important;border-radius:6px!important;padding:0!important;aspect-ratio:auto!important}
#dsp-home.dssl #dsp-th-grid .dsp-th-swatch>*{display:none!important}
#dsp-home.dssl #dsp-th-grid .dsp-th-name{color:var(--tx)!important;font-size:13px!important;font-weight:600!important;padding:9px 0 0!important;background:none!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3!important}
#dsp-home.dssl #dsp-th-grid .dsp-th-name .ck{display:none!important}
#dsp-home.dssl #dsp-th-grid .dsp-th-card[data-th-font]::after{content:attr(data-th-font);display:block;color:var(--mu);font-size:12.5px;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#dsp-home.dssl #dsp-th-grid .dsp-th-card.dsp-th-sel{border-color:#9dbcff!important;background:#1c2a4d!important;outline:none}
#dsp-home.dssl #dsp-th-grid .dsp-th-card.dsp-th-sel .dsp-th-name,#dsp-home.dssl #dsp-th-grid .dsp-th-card.dsp-th-sel::after{color:#9dbcff!important}
/* my presentations */
#dsp-home.dssl #dsp-my-ppts-section{max-width:none!important;width:100%!important;margin:44px 0 0!important;padding-top:44px;border-top:1px solid var(--bd);position:relative;z-index:1;box-sizing:border-box}
#dsp-home.dssl .dssl-hide{display:none!important}
#dsp-home.dssl .dssl-mh{display:flex;align-items:center;gap:14px;margin:0 0 28px;flex-wrap:wrap}
#dsp-home.dssl .dssl-mh-ic{width:38px;height:38px;border-radius:10px;background:var(--p3);display:flex;align-items:center;justify-content:center;color:#c9d6f5;flex-shrink:0}
#dsp-home.dssl .dssl-mh-ic svg{width:21px;height:21px}
#dsp-home.dssl .dssl-mh-t{font-size:26px;font-weight:600;color:var(--tx);line-height:1.2}
#dsp-home.dssl .dssl-mh-n{background:var(--p3);color:#dbe1ee;font-size:14px;font-weight:600;padding:4px 11px;border-radius:9px}
#dsp-home.dssl .dssl-mh-sp{flex:1}
#dsp-home.dssl .dssl-srch{position:relative}
#dsp-home.dssl .dssl-srch svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:17px;height:17px;color:#b9c0d0;pointer-events:none}
#dsp-home.dssl .dssl-srch input{background:var(--p)!important;border:1px solid var(--bd)!important;border-radius:10px!important;color:var(--tx)!important;font:500 14px inherit!important;font-family:inherit!important;padding:9px 14px 9px 40px!important;width:256px;max-width:60vw;height:auto!important;outline:0;box-shadow:none!important;margin:0!important}
#dsp-home.dssl .dssl-srch input::placeholder{color:#8a93a8}
#dsp-home.dssl .dssl-sort{background:var(--p)!important;border:1px solid var(--bd)!important;border-radius:10px!important;color:var(--tx)!important;font:600 13.5px inherit!important;font-family:inherit!important;padding:9px 12px!important;height:auto!important;width:auto!important;cursor:pointer;margin:0!important}
#dsp-home.dssl #dsp-ppt-grid{grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:28px!important}
#dsp-home.dssl #dsp-ppt-grid>[id^="dsp-card-"]{background:var(--p)!important;border-width:1px!important;border-radius:18px!important;position:relative;min-width:0}
#dsp-home.dssl #dsp-ppt-grid>[id^="dsp-card-"] .dsp-pv-num{display:none!important}
#dsp-home.dssl .dssl-badge{position:absolute;top:14px;left:14px;z-index:3;background:rgba(10,13,22,.78);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);color:#dbe6ff;font-size:12.5px;font-weight:700;padding:5px 10px;border-radius:8px;letter-spacing:.02em;line-height:1.2}
#dsp-home.dssl .dssl-bin{position:absolute!important;top:12px;right:12px;z-index:4;width:34px;height:34px;display:flex!important;align-items:center;justify-content:center;border-radius:10px!important;background:rgba(10,13,22,.78)!important;color:#e5e9f5!important;padding:0!important;font-size:0!important;-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.14);cursor:pointer;transition:background .15s,border-color .15s}
#dsp-home.dssl .dssl-bin:hover{background:#dc2626!important;border-color:#dc2626;color:#fff!important}
#dsp-home.dssl .dssl-bin svg{width:17px;height:17px;pointer-events:none}
#dsp-home.dssl .dssl-info{padding:18px 20px 18px!important;display:block!important}
#dsp-home.dssl .dssl-info>div{overflow:hidden}
#dsp-home.dssl .dssl-t{font-size:19px!important;font-weight:600!important;color:var(--tx)!important;max-width:none!important;line-height:1.3}
#dsp-home.dssl .dssl-d{color:#aeb6c8;font-size:14px;margin-top:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-height:1.3em}
#dsp-home.dssl .dssl-m{display:flex!important;align-items:center;gap:8px;margin-top:14px!important;color:#c9cfdd!important;font-size:13.5px!important;font-weight:600}
#dsp-home.dssl .dssl-m svg{width:16px;height:16px;flex-shrink:0}
#dsp-home.dssl #dsp-ppt-foot{padding:36px 0 4px!important}
#dsp-home.dssl #dsp-ppt-more{background:var(--p)!important;border:1px solid var(--bd)!important;color:var(--tx)!important;padding:14px 56px!important;border-radius:12px!important;font-size:15px!important;font-weight:600!important}
#dsp-home.dssl .dssl-none{color:#8a93a8;font-size:14px;padding:6px 2px 0;display:none}
/* medium screens (laptops, or a 1920 screen with Windows scaling): still two columns, the prompt panel is just more compact */
@media(min-width:901px) and (max-width:1400px){#dsp-home.dssl .dssl-top{gap:20px}#dsp-home.dssl .dssl-go{flex-direction:column;align-items:stretch;gap:14px}#dsp-home.dssl #dsp-genbtn{flex:none!important;width:100%!important}#dsp-home.dssl #dsp-mode-row{gap:8px!important}#dsp-home.dssl #dsp-mode-row>*{padding:10px 10px!important;min-width:0!important}#dsp-home.dssl #dsp-mode-row .dsp-mode-sub{font-size:10.5px!important;line-height:1.35!important}#dsp-home.dssl #dsp-mode-row .dsp-mode-title,#dsp-home.dssl #ds30-mode-card>div:first-child{font-size:12px!important;line-height:1.3!important}#dsp-home.dssl #ds30-mode-card>div{font-size:10.5px;line-height:1.35}#dsp-home.dssl #dsp-prompt{min-height:220px!important}}
/* phones and small tablets: one column, and the Generate block moves below the theme + template panels */
@media(max-width:900px){#dsp-home.dssl .dssl-top{grid-template-columns:minmax(0,1fr)}#dsp-home.dssl #dsp-prompt{min-height:200px!important}}
#dsp-home.dssl #dssl-gom{display:none}
@media(max-width:900px){#dsp-home.dssl #dssl-gom{display:block}}
@media(max-width:980px){#dsp-home.dssl #dsp-ppt-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:18px!important}}
@media(max-width:680px){#dsp-home.dssl{padding:16px 12px 40px!important}#dsp-home.dssl .dssl-left,#dsp-home.dssl #dste-sec,#dsp-home.dssl #dsp-theme-section{padding:18px!important;border-radius:16px!important}#dsp-home.dssl #dsp-pills-row{grid-template-columns:repeat(2,minmax(0,1fr))!important}#dsp-home.dssl #dsp-mode-row{flex-wrap:wrap!important}#dsp-home.dssl #dsp-mode-row>*{flex:1 1 100%!important}#dsp-home.dssl #dsp-prompt{min-height:170px!important}#dsp-home.dssl .dssl-go{flex-direction:column;align-items:stretch}#dsp-home.dssl #dsp-genbtn{flex:none!important;width:100%!important}#dsp-home.dssl #dsp-th-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#dsp-home.dssl #dste-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#dsp-home.dssl #dsp-ppt-grid{grid-template-columns:minmax(0,1fr)!important}#dsp-home.dssl #dste-sec .dste-foot,#dsp-home.dssl #dsp-theme-section .dste-foot{top:20px!important;right:18px!important}#dsp-home.dssl .dssl-mh-t{font-size:21px}#dsp-home.dssl .dssl-srch input{width:100%}}

#dsp-home.dssl #dste-strip{display:none!important}

/* dropdown menus must sit above the My Presentations section */
#dsp-home.dssl .dssl-top{z-index:5!important}
#dsp-home.dssl #dsp-my-ppts-section{z-index:1!important}
</style>
<script id="ds-studio-layout-js">
(function(){
if(window.__dssl)return;window.__dssl=1;
function $(id){return document.getElementById(id);}
function el(tag,cls,html){var e=document.createElement(tag);if(cls)e.className=cls;if(html!=null)e.innerHTML=html;return e;}
function svg(d,extra){return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"'+(extra||'')+'>'+d+'</svg>';}
var IC={
 head:svg('<path d="M9.5 21h5M12 3a6.5 6.5 0 0 0-4 11.6V17h8v-2.4A6.5 6.5 0 0 0 12 3z"/><circle cx="12" cy="9.5" r="1.6"/>'),
 dir:svg('<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="1.8"/><circle cx="10" cy="12" r="1.8"/><circle cx="18" cy="18" r="1.8"/>'),
 folder:svg('<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v1H3z"/><path d="M3 10h18l-1.4 7.2A2 2 0 0 1 17.6 19H6.4a2 2 0 0 1-2-1.8z"/>'),
 search:svg('<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2"/>'),
 clock:svg('<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>'),
 bin:svg('<path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/>')
};
var TONES=['Standard','Executive / Crisp','Friendly / Conversational','Persuasive / Sales','Educational / Clear','Bold / Inspirational','Formal / Corporate'];
var FLBL={'dsp-pill-slides':'Slide count','dsp-pill-design':'Theme','dsp-pill-layout':'Aspect ratio','dsp-pill-lang':'Language'};
var SAMPLE_PROMPT='A 10-slide pitch deck for a sustainable coffee startup focusing on ethical sourcing, B-Corp validation, DTC subscription model, unit economics ($38 CAC vs $180 LTV), and a $2.5M Seed round expansion plan.';

var _t0=0;
function build(){
 var home=$('dsp-home');if(!home)return false;
 if(home.classList.contains('dssl'))return true;
 var pbox=$('dsp-pbox'),pills=$('dsp-pills-row'),cost=$('dsp-costbar'),gen=$('dsp-genbtn');
 if(!pbox||!pills||!cost||!gen)return false;
 home.classList.add('dssl');home.classList.add('dssl-ready');_t0=Date.now(); /* arranged: show it */
 var top=el('div','dssl-top'),left=el('div','dssl-left'),right=el('div','dssl-right');right.id='dssl-right';
 left.appendChild(el('div','dssl-ph',IC.head+'<span>Presentation Prompt &amp; Structure</span>'));
 var mode=$('dsp-mode-row');if(mode)left.appendChild(mode);
 left.appendChild(pbox);
 var slot=el('div','');slot.id='dssl-ibox-slot';left.appendChild(slot);
 left.appendChild(pills);
 var go=el('div','dssl-go');go.appendChild(cost);go.appendChild(gen);left.appendChild(go);
 top.appendChild(left);top.appendChild(right);
 home.insertBefore(top,home.firstChild);
 /* fields: label above each control */
 Array.prototype.slice.call(pills.querySelectorAll('.dsp-pill')).forEach(function(p){
  if(p.parentNode!==pills)return;
  var w=el('div','dssl-f');w.appendChild(el('label','',FLBL[p.id]||''));
  pills.insertBefore(w,p);w.appendChild(p);
  if(p.id==='dsp-pill-design'||getComputedStyle(p).display==='none')w.style.display='none'; /* themes are picked in the Color & Typography panel */
 });
 /* use sample */
 var foot=$('dsp-pbox-footer');
 if(foot&&!foot.querySelector('.dssl-sample')){var sb=el('button','dssl-sample','Use Sample');sb.type='button';sb.onclick=function(){var ta=$('dsp-prompt');if(!ta)return;ta.value=SAMPLE_PROMPT;try{ta.dispatchEvent(new Event('input',{bubbles:true}));}catch(e){}if(typeof window.dspChar==='function')window.dspChar();ta.focus();};
  var ch=$('dsp-char');if(ch)foot.insertBefore(sb,ch);else foot.appendChild(sb);}
 return true;
}
function setTone(t){
 var lbl=$('dssl-tone-lbl');if(lbl)lbl.textContent=t;
 var m=$('dssl-tone-menu');if(m)Array.prototype.forEach.call(m.children,function(o){o.classList.toggle('dsp-pill-opt-on',o.getAttribute('data-tone')===t);});
 var ta=$('dsdi-instr');if(!ta)return;
 var lines=ta.value.split('\n').filter(function(l){return !/^\s*Voice & tone:/i.test(l);});
 while(lines.length&&!lines[lines.length-1].trim())lines.pop();
 if(t&&t!=='Standard')lines.push('Voice & tone: '+t+'.');
 ta.value=lines.join('\n');
 try{ta.dispatchEvent(new Event('input',{bubbles:true}));}catch(e){}
}
/* parts that other snippets mount a little later */
function place(){
 var home=$('dsp-home');if(!home||!home.classList.contains('dssl'))return;
 var right=$('dssl-right'),slot=$('dssl-ibox-slot');
 var ib=$('dsdi-ibox');
 if(ib&&slot&&ib.parentNode!==slot)slot.appendChild(ib);
 if(ib&&!ib.__dssl){var lb=ib.querySelector('label');if(lb){ib.__dssl=1;lb.innerHTML=IC.dir+'<span>Specific AI Directives (Optional)</span>';}}
 var th=$('dsp-theme-section');
 if(th&&right&&(th.parentNode!==right||th!==right.firstElementChild))right.insertBefore(th,right.firstChild); /* theme first: picking a theme recolours the template previews below it */
 var te=$('dste-sec');
 if(te&&right&&te.parentNode!==right)right.appendChild(te);
 var nt=$('dste-note');if(nt&&nt.innerHTML.indexOf('theme below')>=0)nt.innerHTML=nt.innerHTML.replace('theme below','theme above');
 if(te){
  var h=te.querySelector('.dste-hd h4'),s=te.querySelector('.dste-sub');
  if(h&&h.textContent!=='Design Templates')h.textContent='Design Templates';
  if(s&&s.textContent!=='Select layout structure and visual syntax')s.textContent='Select layout structure and visual syntax';
  
  if(!$('dssl-arch')){var g=$('dste-grid');if(g){var a=el('div','dssl-arch','<div class="dssl-arch-h"><span>Template visual archetype</span><span>3-Slide Sample Preview</span></div><div class="dssl-arch-g" id="dssl-arch-g"></div>');a.id='dssl-arch';g.parentNode.insertBefore(a,g.nextSibling);}}
  arch();
 }
 if(th){
  var h2=th.querySelector('.dsp-th-hdr h4'),p2=th.querySelector('.dsp-th-hdr p');
  if(h2&&h2.textContent!=='Color & Typography')h2.textContent='Color & Typography';
  if(p2&&p2.textContent!=='Cohesive color palettes and font styling')p2.textContent='Cohesive color palettes and font styling';
  Array.prototype.forEach.call(th.querySelectorAll('#dsp-th-grid .dsp-th-swatch'),function(sw){
   if(sw.__dssl)return;sw.__dssl=1;
   var b=sw.querySelector('.b'),bg=sw.style.background||sw.style.backgroundColor||'',ac=b?(b.style.color||''):'';
   if(ac&&bg&&bg.indexOf('gradient')<0)sw.style.background='linear-gradient(90deg,'+ac+' 0 50%,'+bg+' 50% 100%)';
  });
 }
 scrollers();
 required();
 ppts();
 if(!home.__rdy&&((te&&th&&te.querySelector('#dste-grid .dste-card')&&th.querySelector('#dsp-th-grid .dsp-th-card'))||Date.now()-_t0>3500)){home.__rdy=1;home.classList.add('dssl-ready');}
}
/* scroll boxes: every template / theme is loaded, two rows show, the rest scrolls;
   "View All" / "Explore" opens the whole list, "Show less" goes back to the scroll box */
var _pass=false,_open={t:false,h:false},_seen={},_tries={t:0,h:0};
function engineExpand(k,btn,grid,sel,min){
 if(!btn||!grid||_tries[k]>=3)return;
 if(grid.querySelectorAll(sel).length>min)return;
 _tries[k]++;_pass=true;try{btn.click();}catch(e){}_pass=false;
}
function boxH(grid,sel){
 var c=grid.querySelector(sel);if(!c||!c.offsetHeight)return;
 var cs=getComputedStyle(grid),gap=parseFloat(cs.rowGap)||12,cols=cs.gridTemplateColumns.split(' ').length,n=grid.querySelectorAll(sel).length;
 var h=Math.round(c.offsetHeight*2+gap+8)+'px';if(grid.style.getPropertyValue('--dssl-h')!==h)grid.style.setProperty('--dssl-h',h);
 grid.classList.toggle('dssl-scroll',n>cols*2);
}
function reveal(k,grid,sel){
 if(_seen[k]||!grid.classList.contains('dssl-scroll'))return;
 var c=grid.querySelector(sel);if(!c)return;_seen[k]=1;
 var d=c.getBoundingClientRect().top-grid.getBoundingClientRect().top;
 if(d+c.offsetHeight>grid.clientHeight)grid.scrollTop+=d-4;
}
function setL(btn,l){if(btn.getAttribute('data-l')!==l)btn.setAttribute('data-l',l);}
function scrollers(){
 var te=$('dste-sec'),tb=$('dste-more'),tg=$('dste-grid');
 if(te&&tb&&tg){
  engineExpand('t',tb,tg,'.dste-card',6);
  te.classList.toggle('dssl-open',_open.t);
  setL(tb,_open.t?'Show less':'View All ('+((window.DSTE&&DSTE.order?DSTE.order.length:0)+1)+')');
  boxH(tg,'.dste-card');if(!_open.t&&_chosen.t)reveal('t',tg,'.dste-card.dste-on');
 }
 var th=$('dsp-theme-section'),hb=$('dsp-th-more-btn'),hg=$('dsp-th-grid');
 if(th&&hb&&hg){
  engineExpand('h',hb,hg,'.dsp-th-card',9);
  th.classList.toggle('dssl-open',_open.h);
  var tn=(window.DSTE&&DSTE.THEMES)?Object.keys(DSTE.THEMES).length:hg.querySelectorAll('.dsp-th-card').length;
  setL(hb,_open.h?'Show less':'Explore '+(tn||'All')+' Themes');
  boxH(hg,'.dsp-th-card');if(!_open.h&&_chosen.h)reveal('h',hg,'.dsp-th-card.dsp-th-sel');
 }
}
document.addEventListener('click',function(e){
 if(_pass)return;var t=e.target;if(!t||!t.id||!t.closest||!t.closest('#dsp-home.dssl'))return;
 var k=t.id==='dste-more'?'t':(t.id==='dsp-th-more-btn'?'h':'');if(!k)return;
 e.stopPropagation();e.preventDefault();_open[k]=!_open[k];_seen[k]=0;scrollers();
},true);
/* a colour theme and a design template must both be picked before generating:
   nothing shows as selected when the page opens; Generate asks for the missing pick */
var _chosen={h:false,t:false};
function required(){
 var te=$('dste-sec'),th=$('dsp-theme-section'),left=document.querySelector('#dsp-home.dssl .dssl-left');
 if(te)te.classList.toggle('dssl-nopick',!_chosen.t);
 if(th)th.classList.toggle('dssl-nopick',!_chosen.h);
 var gen=$('dsp-genbtn');if(gen)gen.classList.toggle('dssl-wait',!(_chosen.t&&_chosen.h));
 if(!left)return;
 var hint=$('dssl-hint');if(!hint){hint=el('div','dssl-hint');hint.id='dssl-hint';left.appendChild(hint);}
 var msg=(!_chosen.h&&!_chosen.t)?'Pick a colour theme and a design template to generate.':(!_chosen.h?'Pick a colour theme to generate.':(!_chosen.t?'Pick a design template to generate.':''));
 if(hint.textContent!==msg)hint.textContent=msg;
 hint.style.display=msg?'block':'none';
}
function needPick(){
 var first=null;
 [['h','dsp-theme-section'],['t','dste-sec']].forEach(function(p){
  var s=$(p[1]);if(!s||_chosen[p[0]])return;if(!first)first=s;
  s.classList.remove('dssl-need');void s.offsetWidth;s.classList.add('dssl-need');
  setTimeout(function(){s.classList.remove('dssl-need');},2600);
 });
 var msg=(!_chosen.h&&!_chosen.t)?'Please pick a colour theme and a design template first.':(!_chosen.h?'Please pick a colour theme first.':'Please pick a design template first.');
 if(typeof window.dspToast==='function')window.dspToast(msg,'error',4000);
 if(first&&first.scrollIntoView){var r=first.getBoundingClientRect();if(r.top<70||r.top>window.innerHeight-140)try{first.scrollIntoView({behavior:'smooth',block:'center'});}catch(e){first.scrollIntoView();}}
}
document.addEventListener('click',function(e){
 var t=e.target;if(!t||!t.closest||!t.closest('#dsp-home.dssl'))return;
 if(t.closest('#dsp-th-grid .dsp-th-card')){_chosen.h=true;setTimeout(required,0);return;}
 if(t.closest('#dste-grid .dste-card')){_chosen.t=true;setTimeout(required,0);return;}
 if(t.closest('#dsp-genbtn')&&!(_chosen.h&&_chosen.t)){e.stopPropagation();e.preventDefault();needPick();}
},true);
/* 3-slide sample of the picked template */
var _ak='';
function selTheme(){var c=document.querySelector('#dsp-th-grid .dsp-th-card.dsp-th-sel');if(!c)return null;var sw=c.querySelector('.dsp-th-swatch'),t=sw&&sw.querySelector('.t'),b=sw&&sw.querySelector('.b');
 var bg=sw?(sw.getAttribute('data-dssl-bg')||''):'';return {key:c.getAttribute('data-th-key')||'',tc:t?t.style.color:'#fff',ac:b?b.style.color:'#4A9EFF'};}
function arch(){
 var g=$('dssl-arch-g');if(!g)return;var D=window.DSTE;
 var id=(D&&D.pick&&D.pick!=='classic'&&D.reg&&D.reg[D.pick])?D.pick:'classic',th=selTheme();
 var key=id+'|'+(D&&D.homeTheme||'')+'|'+(th?th.key:'')+'|'+(_chosen.t?1:0);
 if(!_chosen.t){if(key!==_ak||!g.children.length){_ak=key;g.innerHTML='<div class="dssl-arch-e">Pick a design template to see its 3-slide sample.</div>';}return;}
 if(key===_ak&&g.children.length)return;_ak=key;
 var h='';
 if(id!=='classic'&&D.SAMPLE&&D.renderHTML){
  try{var T=D.themed?D.themed(D.reg[id],D.homeTheme||''):D.reg[id],S=D.SAMPLE,sl=[S.cover,S.stats||S.p3,S.timeline||S.p3],deck={slides:sl};
   sl.forEach(function(s,i){h+='<div class="dssl-at">'+D.renderHTML(T,s,'v1',i,deck,{edit:false})+'</div>';});}catch(e){h='';}
 }
 if(!h){
  var bg='#1a2030',tc=(th&&th.tc)||'#e9ecf5',ac=(th&&th.ac)||'#6ea8ff';
  var c=document.querySelector('#dsp-th-grid .dsp-th-card.dsp-th-sel .dsp-th-swatch');
  var raw=c?(c.style.background||''):'';var m=raw.match(/50%,\s*([^ ]+(?:\([^)]*\))?)\s*50%/);if(m)bg=m[1];
  function ln(l,t,w,hh,col,op){return '<div style="position:absolute;left:'+l+'%;top:'+t+'%;width:'+w+'%;height:'+hh+'%;background:'+col+';opacity:'+(op==null?1:op)+';border-radius:99px;"></div>';}
  function ft(a,b){return '<div style="position:absolute;left:7%;right:7%;bottom:8%;display:flex;justify-content:space-between;font:600 5cqw/1 Arial,sans-serif;color:'+tc+';opacity:.6;letter-spacing:.04em;"><span>'+a+'</span><span>'+b+'</span></div>';}
  var s1='<div style="position:absolute;left:7%;top:11%;width:3.5%;aspect-ratio:1;border-radius:50%;background:'+ac+';"></div>'+ln(14,11.5,28,3.5,tc)+ln(7,40,46,7,ac)+ln(7,54,34,3,tc,.4)+ft('DS-26','01');
  var st=[['94%','Retention'],['3.2x','Velocity'],['$4.8M','ARR']].map(function(x,i){return '<div style="position:absolute;left:'+(7+i*29.5)+'%;top:34%;width:27%;height:32%;border-radius:6cqw;background:'+tc+';opacity:.08;"></div><div style="position:absolute;left:'+(7+i*29.5)+'%;top:38%;width:27%;text-align:center;font:800 7.5cqw/1.1 Arial,sans-serif;color:'+ac+';">'+x[0]+'</div><div style="position:absolute;left:'+(7+i*29.5)+'%;top:53%;width:27%;text-align:center;font:500 4cqw/1.1 Arial,sans-serif;color:'+tc+';opacity:.6;">'+x[1]+'</div>';}).join('');
  var s2=ln(7,11.5,38,3.5,tc,.5)+st+ft('METRICS','02');
  var s3=ln(7,11.5,30,3.5,tc,.5)+ln(7,43,26,7,ac,.4)+ln(36,43,26,7,ac,.7)+ln(65,43,28,7,ac,1)+ft('ROADMAP','03');
  h=[s1,s2,s3].map(function(x){return '<div class="dssl-at" style="background:'+bg+';">'+x+'</div>';}).join('');
 }
 g.innerHTML=h;
}
/* my presentations */
var _q='',_sort='recent';
function fmtDate(s){var d=new Date(s);if(isNaN(d))return s;try{return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});}catch(e){return s;}}
function ppts(){
 var sec=$('dsp-my-ppts-section'),grid=$('dsp-ppt-grid');if(!sec)return;
 if(!$('dssl-mh')){
  var first=sec.firstElementChild;if(first&&first.id!=='dsp-ppt-grid')first.classList.add('dssl-hide');
  var mh=el('div','dssl-mh','<span class="dssl-mh-ic">'+IC.folder+'</span><span class="dssl-mh-t">My Presentations</span><span class="dssl-mh-n" id="dssl-mh-n"></span><span class="dssl-mh-sp"></span><label class="dssl-srch">'+IC.search+'<input type="search" id="dssl-q" placeholder="Search presentations..." autocomplete="off"/></label><select class="dssl-sort" id="dssl-sort" aria-label="Sort"><option value="recent">Last modified</option><option value="az">Title A–Z</option></select>');
  mh.id='dssl-mh';sec.insertBefore(mh,sec.firstChild);
  var none=el('div','dssl-none','No presentations match your search.');none.id='dssl-none';
  if(grid)grid.parentNode.insertBefore(none,grid.nextSibling);
  $('dssl-q').addEventListener('input',function(){_q=this.value.toLowerCase().trim();filter();});
  $('dssl-sort').addEventListener('change',function(){_sort=this.value;filter();});
 }
 if(!grid)return;
 var cards=grid.querySelectorAll('[id^="dsp-card-"]'),fresh=false;
 Array.prototype.forEach.call(cards,function(c){
  if(c.__dssl)return;c.__dssl=1;fresh=true;
  var thumb=c.children[0],info=c.children[1];if(!thumb||!info)return;
  info.classList.add('dssl-info');
  var box=info.children[0],x=info.querySelector('span[onclick*="dspDeleteDraft"]');
  var t=box&&box.children[0],m=box&&box.children[1];
  var meta=m?m.textContent:'',mm=meta.match(/(\d+)\s*slides?/i),dt=(meta.split('·')[1]||'').trim();
  if(t){t.classList.add('dssl-t');c.setAttribute('data-dssl-t',t.textContent.toLowerCase());}
  if(mm){var b=el('div','dssl-badge',mm[1]+' Slide'+(mm[1]==='1'?'':'s'));c.appendChild(b);}
  /* one line under the title: the cover subtitle */
  var cnt=thumb.querySelector('.dsp-pv-cnt'),desc='';
  if(cnt){for(var i=1;i<cnt.children.length;i++){var tx=(cnt.children[i].textContent||'').trim();if(tx){desc=tx.replace(/^Subtitle:\s*/i,'');break;}}}
  if(box&&t){var d=el('div','dssl-d');d.textContent=desc;box.insertBefore(d,t.nextSibling);}
  if(m){m.classList.add('dssl-m');m.innerHTML=IC.clock+'<span></span>';m.lastChild.textContent=dt?fmtDate(dt):meta;}
  if(x){x.classList.add('dssl-bin');x.innerHTML=IC.bin;x.setAttribute('title','Delete presentation');x.setAttribute('aria-label','Delete presentation');x.removeAttribute('onmouseover');x.removeAttribute('onmouseout');c.appendChild(x);}
 });
 var more=$('dsp-ppt-more'),left=0;if(more&&more.offsetParent!==null){var lm=more.textContent.match(/(\d+)/);if(lm)left=parseInt(lm[1],10);}
 var n=$('dssl-mh-n'),total=cards.length+left,txt=total+' Deck'+(total===1?'':'s');if(n&&n.textContent!==txt)n.textContent=txt;
 if(fresh)filter();
}
function filter(){
 var grid=$('dsp-ppt-grid');if(!grid)return;var cards=Array.prototype.slice.call(grid.querySelectorAll('[id^="dsp-card-"]')),shown=0;
 cards.forEach(function(c){var ok=!_q||(c.getAttribute('data-dssl-t')||'').indexOf(_q)>=0;c.style.display=ok?'':'none';if(ok)shown++;c.style.order='';});
 if(_sort==='az'){cards.slice().sort(function(a,b){return (a.getAttribute('data-dssl-t')||'').localeCompare(b.getAttribute('data-dssl-t')||'');}).forEach(function(c,i){c.style.order=String(i+1);});}
 var none=$('dssl-none');if(none)none.style.display=(cards.length&&!shown)?'block':'none';
}
/* phones: Generate (price, button, hint) sits after the theme and template panels; wider screens: back in the prompt panel */
function goPlace(){
 var top=document.querySelector('#dsp-home.dssl .dssl-top');if(!top)return;
 var left=top.querySelector('.dssl-left:not(#dssl-gom)'),go=top.querySelector('.dssl-go'),hint=$('dssl-hint'),m=$('dssl-gom');
 if(!left||!go)return;
 var narrow=!!(window.matchMedia&&window.matchMedia('(max-width:900px)').matches);
 if(narrow){
  if(!m){m=el('div','dssl-left');m.id='dssl-gom';}
  if(top.lastElementChild!==m)top.appendChild(m);
  if(go.parentNode!==m)m.appendChild(go);
  if(hint&&(hint.parentNode!==m||hint.previousElementSibling!==go))m.appendChild(hint);
 }else{
  if(go.parentNode!==left)left.appendChild(go);
  if(hint&&(hint.parentNode!==left||hint.previousElementSibling!==go))left.appendChild(hint);
 }
}
window.addEventListener('resize',function(){try{goPlace();}catch(e){}});
function tick(){try{if(build()){place();goPlace();}}catch(e){if(window.console)console.warn('[DSSL]',e);}}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',tick);else tick();
setInterval(tick,700);
var _fast=setInterval(tick,50);setTimeout(function(){clearInterval(_fast);},15000); /* quick checks while the page is still loading */
document.addEventListener('click',function(){setTimeout(tick,80);},true);
/* keep the scroll position of a scroll box when a pick re-draws its cards */
document.addEventListener('click',function(e){var g=e.target&&e.target.closest?e.target.closest('#dste-grid.dssl-scroll,#dsp-th-grid.dssl-scroll'):null;if(!g)return;var st=g.scrollTop;[0,60,200,450,900].forEach(function(ms){setTimeout(function(){if(Math.abs(g.scrollTop-st)>2)g.scrollTop=st;},ms);});},true);
})();

/* keep an open dropdown fully on screen: open upward or shorten when there is no room below */
(function(){
 function fit(){
  var m=document.querySelector("#dsp-home.dssl .dsp-pill-menu.dsp-menu-open");if(!m)return;
  var b=m.parentElement;while(b&&!(b.classList&&b.classList.contains("dsp-pill")))b=b.parentElement;if(!b)return;
  var r=b.getBoundingClientRect(),vh=window.innerHeight;
  m.style.maxHeight="";
  var h=Math.min(m.scrollHeight+2,280),below=vh-r.bottom-14,above=r.top-14;
  if(h<=below){m.style.top=(r.bottom+6)+"px";}
  else if(above>below){var hh=Math.max(120,Math.min(h,above));m.style.maxHeight=hh+"px";m.style.top=Math.max(8,r.top-6-hh)+"px";}
  else{m.style.maxHeight=Math.max(120,below)+"px";m.style.top=(r.bottom+6)+"px";}
 }
 document.addEventListener("click",function(){setTimeout(fit,0);});
 window.addEventListener("scroll",fit,{passive:true});
 window.addEventListener("resize",fit);
})();
</script>
	<?php
}, 99 );