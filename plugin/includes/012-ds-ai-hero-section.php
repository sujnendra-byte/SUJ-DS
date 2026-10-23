<?php
/**
 * Code Snippets export — DS AI Hero Section
 *
 * Snippet ID : 698
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-012
 * Modified   : 2026-10-07 (video in the top row, home banner slot, 6 cards on their own row)
 * Load order : 012 of 147
 */
/**
 * Designstager — DS Animated AI Hero Section (V2)
 * Snippet Name: DS AI Hero Section
 * Run: Everywhere
 * Usage: [ds_ai_hero]
 */
add_shortcode('ds_ai_hero', function () {
    ob_start();
    ?>
<!-- ========== DS HERO V2 — START ========== -->
<style id="dsh-style">
#dsHero *{box-sizing:border-box}
#dsHero{
  --dsh-accent:#E8442A; --dsh-accent2:#FF7A45;
  --dsh-bg:#080A12; --dsh-ink:#fff; --dsh-mute:#9AA6BD; --dsh-line:rgba(255,255,255,.09);
  position:relative;overflow:hidden;background:var(--dsh-bg);
  font-family:Poppins,sans-serif;color:var(--dsh-ink);
  padding:70px 20px 60px;isolation:isolate;
}
#dsHero:before{content:"";position:absolute;inset:0;z-index:0;
  background:
    radial-gradient(900px 480px at 78% 22%,rgba(60,110,220,.20),transparent 62%),
    radial-gradient(700px 420px at 14% 14%,rgba(232,68,42,.16),transparent 65%),
    radial-gradient(1000px 500px at 50% 108%,rgba(30,58,120,.35),transparent 70%);}
#dsHero:after{content:"";position:absolute;inset:0;z-index:0;opacity:.35;
  background-image:radial-gradient(1.4px 1.4px at 12% 18%,#fff,transparent),radial-gradient(1.2px 1.2px at 32% 62%,#cfe0ff,transparent),radial-gradient(1.6px 1.6px at 58% 12%,#fff,transparent),radial-gradient(1.2px 1.2px at 74% 48%,#ffd9cf,transparent),radial-gradient(1.4px 1.4px at 88% 76%,#fff,transparent),radial-gradient(1.2px 1.2px at 46% 88%,#cfe0ff,transparent),radial-gradient(1.1px 1.1px at 6% 70%,#fff,transparent),radial-gradient(1.3px 1.3px at 66% 82%,#fff,transparent);
  animation:dshTwinkle 5s ease-in-out infinite alternate}
@keyframes dshTwinkle{from{opacity:.2}to{opacity:.5}}
#dsHero>*{position:relative;z-index:2}
.dsh-wrap{max-width:1280px;margin:0 auto}

/* ---------- top grid ---------- */
.dsh-top{display:grid;grid-template-columns:1fr 1.2fr;gap:34px;align-items:center}
.dsh-badge{display:inline-flex;align-items:center;gap:9px;padding:9px 18px;border-radius:40px;
  background:rgba(232,68,42,.10);border:1px solid rgba(232,68,42,.28);color:var(--dsh-accent);
  font-size:13.5px;font-weight:600;letter-spacing:.2px}
.dsh-badge i{width:8px;height:8px;border-radius:50%;background:var(--dsh-accent);box-shadow:0 0 0 0 rgba(232,68,42,.6);animation:dshPing 2s infinite}
@keyframes dshPing{70%{box-shadow:0 0 0 9px rgba(232,68,42,0)}100%{box-shadow:0 0 0 0 rgba(232,68,42,0)}}
.dsh-h1{margin:22px 0 0;font-size:clamp(36px,4.1vw,58px);line-height:1.06;font-weight:900;letter-spacing:-1.2px}
.dsh-h1 .dsh-grad{background:linear-gradient(95deg,var(--dsh-accent) 10%,var(--dsh-accent2) 90%);-webkit-background-clip:text;background-clip:text;color:transparent}
.dsh-sub{margin:20px 0 0;max-width:560px;font-size:clamp(15px,1.25vw,17.5px);line-height:1.68;color:var(--dsh-mute)}
.dsh-sub b{color:#fff;font-weight:600}
.dsh-btns{display:flex;flex-wrap:wrap;gap:14px;margin:30px 0 0}
.dsh-btn{display:inline-flex;align-items:center;gap:10px;padding:16px 28px;border-radius:13px;
  font-size:15.5px;font-weight:700;text-decoration:none;transition:.25s ease;border:1px solid transparent}
.dsh-btn-1{background:linear-gradient(95deg,var(--dsh-accent),var(--dsh-accent2));color:#fff;box-shadow:0 12px 34px rgba(232,68,42,.34)}
.dsh-btn-1:hover{transform:translateY(-3px);box-shadow:0 18px 44px rgba(232,68,42,.46);color:#fff}
.dsh-btn-1 .dsh-arrow{transition:.25s}
.dsh-btn-1:hover .dsh-arrow{transform:translateX(4px)}
.dsh-btn-2{background:rgba(255,255,255,.05);border-color:var(--dsh-line);color:#fff;backdrop-filter:blur(8px)}
.dsh-btn-2:hover{background:rgba(255,255,255,.1);transform:translateY(-3px);color:#fff}
.dsh-btn svg{flex:none}
.dsh-ticks{display:flex;flex-wrap:wrap;gap:12px 26px;margin:24px 0 0;font-size:13.5px;color:#B6C1D6}
.dsh-ticks span{display:inline-flex;align-items:center;gap:8px}
.dsh-ticks svg{flex:none;color:#2FD07E}

/* ---------- laptop scene ---------- */
.dsh-visual{position:relative;min-height:500px;display:flex;align-items:center;justify-content:center}
/* golden orbit ring: back half sits behind the laptop, front half sweeps over it */
.dsh-ring{position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;overflow:visible}
.dsh-ring-back{z-index:1}
.dsh-ring-front{z-index:7}
.dsh-ring .dsh-track-l{fill:none;stroke:rgba(245,196,81,.22);stroke-width:1.5}
.dsh-ring .dsh-beam{fill:none;stroke-width:2.6;stroke-linecap:round;
  stroke-dasharray:160 1215;stroke-dashoffset:0;animation:dshOrbit 7s linear infinite;
  filter:drop-shadow(0 0 5px rgba(245,196,81,.65))}
.dsh-ring-back .dsh-beam{stroke:url(#dshGoldB)}
.dsh-ring-front .dsh-beam{stroke:url(#dshGoldF)}
@keyframes dshOrbit{to{stroke-dashoffset:-1375}}

.dsh-laptop{position:relative;z-index:6;width:56%;max-width:400px;filter:drop-shadow(0 38px 60px rgba(0,0,0,.65));animation:dshFloat 7s ease-in-out infinite}
@keyframes dshFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.dsh-lid{background:linear-gradient(150deg,#222d44,#0c1120);border:1.5px solid rgba(130,160,215,.28);
  border-radius:15px 15px 5px 5px;padding:11px 11px 14px;box-shadow:inset 0 1px 0 rgba(255,255,255,.14),0 0 70px rgba(232,68,42,.14)}
.dsh-screen{position:relative;aspect-ratio:16/10.2;border-radius:8px;overflow:hidden;background:#060912;border:1px solid rgba(255,255,255,.07)}
.dsh-chrome{position:absolute;top:0;left:0;right:0;height:9.5%;z-index:9;display:flex;align-items:center;gap:4px;
  padding:0 9px;background:rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.07)}
.dsh-chrome b{width:5px;height:5px;border-radius:50%;background:#ff5f57;flex:none}
.dsh-chrome b:nth-child(2){background:#febc2e}.dsh-chrome b:nth-child(3){background:#28c840}
.dsh-chrome u{flex:1;height:60%;margin-left:7px;border-radius:20px;background:rgba(255,255,255,.07)}
.dsh-base{height:13px;width:118%;margin-left:-9%;background:linear-gradient(180deg,#3d4a66,#131928);
  border-radius:0 0 14px 14px;position:relative}
.dsh-base:after{content:"";position:absolute;top:0;left:50%;transform:translateX(-50%);width:16%;height:4px;border-radius:0 0 6px 6px;background:rgba(255,255,255,.12)}

.dsh-scene{position:absolute;top:9.5%;left:0;right:0;bottom:0;padding:9px;opacity:0;transform:scale(.985);
  transition:opacity .7s ease,transform .7s ease;pointer-events:none}
.dsh-scene.is-on{opacity:1;transform:none}
.dsh-scene-t{position:absolute;left:11px;top:8px;z-index:5;font-size:7px;font-weight:700;letter-spacing:1.3px;
  color:rgba(255,255,255,.62);text-transform:uppercase;padding:2px 6px;border-radius:4px;
  background:rgba(6,9,18,.78);border:1px solid rgba(255,255,255,.08)}
.dsh-s1 .dsh-scene-t{left:auto;right:11px}

.dsh-img{width:100%;height:100%;object-fit:cover;display:block;border:0}

/* scene: presentations — a real slide */
.dsh-s1{display:flex;gap:7px}
.dsh-rail{width:18%;display:flex;flex-direction:column;gap:4px}
.dsh-rail i{display:block;flex:1;border-radius:3px;position:relative;overflow:hidden;
  background:linear-gradient(135deg,#131c31,#0c1220);border:1px solid rgba(255,255,255,.09)}
.dsh-rail i:before{content:"";position:absolute;left:12%;top:20%;width:52%;height:9%;border-radius:2px;background:rgba(255,255,255,.55)}
.dsh-rail i:after{content:"";position:absolute;left:12%;top:38%;width:70%;height:6%;border-radius:2px;
  background:rgba(255,255,255,.22);box-shadow:0 5px 0 rgba(255,255,255,.22),0 10px 0 rgba(255,255,255,.22)}
.dsh-rail i.on{border-color:var(--dsh-accent);box-shadow:0 0 0 1px var(--dsh-accent)}
.dsh-canvas{flex:1;border-radius:5px;position:relative;overflow:hidden;
  background:linear-gradient(140deg,#101c33,#0a1020);border:1px solid rgba(255,255,255,.09)}
.dsh-slide{position:absolute;inset:0;display:flex}
.dsh-slide-l{width:54%;padding:0 6%;display:flex;flex-direction:column;justify-content:center;position:relative;z-index:2}
.dsh-kick{font-size:6px;letter-spacing:1.5px;text-transform:uppercase;color:var(--dsh-accent);font-weight:800;margin-bottom:4px}
.dsh-slide-l h4{margin:0 0 7px;font-size:clamp(9px,1.9vw,15px);font-weight:800;line-height:1.14;letter-spacing:-.3px;color:#fff}
.dsh-bul{display:flex;align-items:flex-start;gap:5px;margin-bottom:4px;font-size:clamp(5.4px,1.02vw,8px);line-height:1.35;color:#C3CFE4}
.dsh-bul svg{width:8px;height:8px;flex:none;color:var(--dsh-accent);margin-top:.5px}
.dsh-slide-r{width:46%;position:relative;overflow:hidden}
.dsh-slide-r:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,#0d1526 2%,rgba(13,21,38,0) 42%)}
.dsh-slide-bar{position:absolute;left:0;bottom:0;height:3px;width:34%;background:linear-gradient(90deg,var(--dsh-accent),var(--dsh-accent2));z-index:3}
.dsh-cbtn{position:absolute;right:7px;bottom:7px;z-index:4;padding:4px 11px;border-radius:5px;
  background:linear-gradient(95deg,var(--dsh-accent),var(--dsh-accent2));font-size:7px;font-weight:700;
  box-shadow:0 4px 14px rgba(232,68,42,.5);overflow:hidden}
.dsh-cbtn:after{content:"";position:absolute;inset:0;background:rgba(255,255,255,.35);opacity:0;animation:dshFlash 2.6s ease-out infinite}
@keyframes dshFlash{0%,70%{opacity:0}80%{opacity:.5}100%{opacity:0}}

/* scene: images */
.dsh-s2{display:grid;grid-template-columns:repeat(3,1fr);grid-template-rows:repeat(2,1fr);gap:6px}
.dsh-s2 i{border-radius:5px;position:relative;overflow:hidden;animation:dshPop .5s ease backwards;background:#101827}
.dsh-s2 i:nth-of-type(1){background:linear-gradient(150deg,#1f6f8b,#7fd8c0);animation-delay:.05s}
.dsh-s2 i:nth-of-type(2){background:linear-gradient(150deg,#5b2a86,#c86dd7);animation-delay:.12s}
.dsh-s2 i:nth-of-type(3){background:linear-gradient(150deg,#b3541e,#f7b733);animation-delay:.19s}
.dsh-s2 i:nth-of-type(4){background:linear-gradient(150deg,#132c52,#4f8cf7);animation-delay:.26s}
.dsh-s2 i:nth-of-type(5){background:linear-gradient(150deg,#7a1f3d,#ff7a45);animation-delay:.33s}
.dsh-s2 i:nth-of-type(6){background:linear-gradient(150deg,#1b4332,#74c69d);animation-delay:.4s}
.dsh-s2 i:after{content:"";position:absolute;inset:0;background:linear-gradient(115deg,transparent 35%,rgba(255,255,255,.4) 50%,transparent 65%);
  transform:translateX(-110%);animation:dshShine 3.4s ease-in-out infinite}
.dsh-s2 i:nth-of-type(even):after{animation-delay:1.1s}
@keyframes dshShine{0%{transform:translateX(-110%)}55%,100%{transform:translateX(110%)}}
@keyframes dshPop{from{opacity:0;transform:scale(.85)}}

/* scene: video */
.dsh-s3{display:flex;flex-direction:column;gap:6px}
.dsh-frame{flex:1;border-radius:6px;position:relative;overflow:hidden;
  background:linear-gradient(160deg,#0b1a33,#25406e 60%,#7a3f2a);display:flex;align-items:center;justify-content:center}
.dsh-frame .dsh-img{position:absolute;inset:0}
.dsh-vid{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;border:0;
  opacity:0;transition:opacity .55s ease}
.dsh-frame.is-playing .dsh-vid{opacity:1}
.dsh-frame:after{content:"";position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,rgba(6,9,18,.12),rgba(6,9,18,.45))}
.dsh-play{position:relative;z-index:2;width:16%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,.92);display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 0 0 rgba(255,255,255,.4);animation:dshPing2 2.2s infinite;transition:opacity .45s ease,transform .45s ease}
.dsh-frame.is-playing .dsh-play{opacity:0;transform:scale(.72)}
.dsh-play:after{content:"";border-left:8px solid #0b1120;border-top:5px solid transparent;border-bottom:5px solid transparent;margin-left:2px}
@keyframes dshPing2{70%{box-shadow:0 0 0 14px rgba(255,255,255,0)}100%{box-shadow:0 0 0 0 rgba(255,255,255,0)}}
.dsh-track{height:9px;border-radius:4px;background:rgba(255,255,255,.1);position:relative;overflow:hidden}
.dsh-track i{position:absolute;left:0;top:0;bottom:0;width:12%;border-radius:4px;
  background:linear-gradient(95deg,var(--dsh-accent),var(--dsh-accent2));animation:dshSeek 4s ease-in-out infinite}
.dsh-s3.is-live .dsh-track i{animation:none;transition:width .15s linear}
@keyframes dshSeek{0%{width:12%}60%{width:78%}100%{width:12%}}

/* scene: audio */
.dsh-s4{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:9px}
.dsh-wave{display:flex;align-items:center;gap:3px;height:42%}
.dsh-wave i{width:3.5px;border-radius:3px;background:linear-gradient(180deg,#8b5cf6,#e8442a);animation:dshBar 1.1s ease-in-out infinite}
@keyframes dshBar{0%,100%{height:16%}50%{height:100%}}
.dsh-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:7.5px;font-weight:600;
  background:rgba(139,92,246,.18);border:1px solid rgba(139,92,246,.4);color:#cbb6ff}

/* scene: marketplace — real template cards */
.dsh-s5{display:grid;grid-template-columns:repeat(3,1fr);grid-template-rows:repeat(2,1fr);gap:5px}
.dsh-s5 figure{margin:0;border-radius:5px;border:1px solid rgba(255,255,255,.1);overflow:hidden;display:flex;flex-direction:column;
  background:#0e1524;animation:dshPop .5s ease backwards}
.dsh-s5 figure:nth-of-type(2){animation-delay:.07s}.dsh-s5 figure:nth-of-type(3){animation-delay:.14s}
.dsh-s5 figure:nth-of-type(4){animation-delay:.21s}.dsh-s5 figure:nth-of-type(5){animation-delay:.28s}.dsh-s5 figure:nth-of-type(6){animation-delay:.35s}
.dsh-s5 .th{flex:1;position:relative;overflow:hidden;min-height:0}
.dsh-s5 figcaption{padding:2px 4px 3px;display:flex;align-items:center;justify-content:space-between;gap:3px;
  font-size:5.2px;line-height:1.2;color:#D2DAE8;white-space:nowrap;overflow:hidden}
.dsh-s5 figcaption b{color:var(--dsh-accent);font-weight:700}

/* scene: template matcher */
.dsh-s6{display:flex;align-items:center;gap:6px}
.dsh-tmS{width:38%;border-radius:5px;padding:6% 5%;background:linear-gradient(140deg,#101c33,#0a1020);
  border:1px solid rgba(255,255,255,.1);align-self:stretch;display:flex;flex-direction:column;justify-content:center}
.dsh-tmS b{display:block;font-size:7.5px;font-weight:800;line-height:1.15;margin-bottom:5px;color:#fff}
.dsh-tmS i{display:block;height:3px;border-radius:2px;background:rgba(255,255,255,.22);margin-bottom:3px}
.dsh-tmS i:nth-of-type(2){width:82%}.dsh-tmS i:nth-of-type(3){width:64%}
.dsh-tmA{flex:none;width:12%;display:flex;align-items:center;justify-content:center;color:#FBBF24}
.dsh-tmA svg{width:70%}
.dsh-tmL{flex:1;display:flex;flex-direction:column;gap:4px;align-self:stretch}
.dsh-tmL figure{margin:0;flex:1;position:relative;border-radius:4px;overflow:hidden;border:1px solid rgba(255,255,255,.12);min-height:0}
.dsh-tmL figure.on{border-color:#FBBF24;box-shadow:0 0 0 1px #FBBF24,0 0 14px rgba(251,191,36,.45)}
.dsh-tmL figure span{position:absolute;right:3px;top:3px;padding:1px 4px;border-radius:3px;font-size:5px;font-weight:700;
  background:#FBBF24;color:#2b1a00}

/* scene: customise designs */
.dsh-s7{display:flex;gap:7px}
.dsh-czC{width:52%;position:relative;border-radius:5px;overflow:hidden;border:1px solid rgba(255,255,255,.1)}
.dsh-czC span{position:absolute;left:4px;top:4px;padding:2px 6px;border-radius:3px;font-size:5.4px;font-weight:700;
  background:linear-gradient(95deg,#4F46E5,#818CF8);color:#fff}
.dsh-czP{flex:1;display:flex;flex-direction:column;justify-content:center;gap:5px;padding:0 3%}
.dsh-czR{display:flex;align-items:center;justify-content:space-between;gap:5px;font-size:6px;color:#C3CFE4}
.dsh-czR em{display:flex;gap:3px;align-items:center;font-style:normal}
.dsh-czR u{width:8px;height:8px;border-radius:50%;display:block;text-decoration:none}
.dsh-czR s{display:block;width:34px;height:4px;border-radius:3px;background:rgba(255,255,255,.18);position:relative;text-decoration:none}
.dsh-czR s:after{content:"";position:absolute;left:0;top:0;bottom:0;width:62%;border-radius:3px;background:#818CF8}
.dsh-czR:nth-of-type(3) s:after{width:38%}
.dsh-czB{margin-top:3px;text-align:center;padding:4px 6px;border-radius:5px;font-size:6px;font-weight:700;
  background:linear-gradient(95deg,var(--dsh-accent),var(--dsh-accent2));box-shadow:0 4px 12px rgba(232,68,42,.45)}

/* ---------- floating cards ---------- */
.dsh-fc{position:absolute;z-index:8;display:flex;align-items:center;gap:8px;padding:9px 13px 9px 9px;border-radius:12px;
  background:rgba(16,22,38,.78);border:1px solid rgba(255,255,255,.1);backdrop-filter:blur(12px);
  box-shadow:0 14px 34px rgba(0,0,0,.45);font-size:12.5px;font-weight:600;white-space:nowrap;
  transition:.45s cubic-bezier(.2,.8,.3,1);animation:dshFloat 8s ease-in-out infinite}
.dsh-fc:nth-of-type(2){animation-delay:-1.1s}.dsh-fc:nth-of-type(3){animation-delay:-2.2s}
.dsh-fc:nth-of-type(4){animation-delay:-3.3s}.dsh-fc:nth-of-type(5){animation-delay:-4.4s}
.dsh-fc:nth-of-type(6){animation-delay:-5.5s}.dsh-fc:nth-of-type(7){animation-delay:-6.6s}
.dsh-fc em{width:24px;height:24px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex:none;color:#fff}
.dsh-fc em svg{width:13px;height:13px}
.dsh-fc.is-on{border-color:rgba(232,68,42,.65);box-shadow:0 0 0 1px rgba(232,68,42,.35),0 18px 40px rgba(232,68,42,.28);transform:scale(1.07)}
.dsh-fc[data-i="0"]{top:2%;left:3%}
.dsh-fc[data-i="1"]{top:-5%;left:36%}
.dsh-fc[data-i="2"]{top:4%;right:2%}
.dsh-fc[data-i="5"]{top:34%;left:0}
.dsh-fc[data-i="4"]{top:35%;right:0}
.dsh-fc[data-i="6"]{bottom:10%;left:2%}
.dsh-fc[data-i="3"]{bottom:8%;right:0}
.dsh-ic-ppt{background:linear-gradient(140deg,#E8442A,#FF7A45)}
.dsh-ic-img{background:linear-gradient(140deg,#2563EB,#38BDF8)}
.dsh-ic-vid{background:linear-gradient(140deg,#7C3AED,#C084FC)}
.dsh-ic-gfx{background:linear-gradient(140deg,#DB2777,#FB7185)}
.dsh-ic-aud{background:linear-gradient(140deg,#0D9488,#34D399)}
.dsh-ic-tm{background:linear-gradient(140deg,#D97706,#FBBF24)}
.dsh-ic-cus{background:linear-gradient(140deg,#4F46E5,#818CF8)}


/* ---------- feature cards ---------- */
.dsh-feats{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin:58px 0 0}
.dsh-feat{display:flex;flex-direction:column;text-decoration:none;padding:20px;border-radius:15px;position:relative;overflow:hidden;
  background:linear-gradient(160deg,rgba(255,255,255,.055),rgba(255,255,255,.015));
  border:1px solid var(--dsh-line);transition:.3s ease}
.dsh-feat:before{content:"";position:absolute;inset:0;opacity:0;transition:.3s;
  background:radial-gradient(420px 160px at 50% 0,rgba(232,68,42,.16),transparent 70%)}
.dsh-feat:hover{transform:translateY(-6px);border-color:rgba(232,68,42,.4);box-shadow:0 20px 44px rgba(0,0,0,.45)}
.dsh-feat:hover:before{opacity:1}
.dsh-feat>*{position:relative}
.dsh-feat-ic{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:14px}
.dsh-feat-ic svg{width:21px;height:21px}
.dsh-feat h3{font-size:15px;font-weight:700;margin:0 0 7px;color:#fff}
.dsh-feat p{margin:0 0 14px;font-size:12.8px;line-height:1.55;color:var(--dsh-mute)}
.dsh-feat-go{margin-top:auto;margin-bottom:0;width:28px;height:28px;border-radius:50%;border:1px solid var(--dsh-line);
  display:flex;align-items:center;justify-content:center;color:#fff;transition:.3s}
.dsh-feat:hover .dsh-feat-go{background:var(--dsh-accent);border-color:var(--dsh-accent);transform:translateX(4px)}

/* ---------- stats ---------- */
.dsh-stats{display:flex;flex-wrap:wrap;justify-content:center;gap:18px 54px;margin:50px 0 0;padding:26px 20px;
  border-top:1px solid var(--dsh-line);border-bottom:1px solid var(--dsh-line)}
.dsh-stat{display:flex;align-items:center;gap:13px}
.dsh-stat-ic{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex:none;color:#fff}
.dsh-stat-ic svg{width:19px;height:19px}
.dsh-stat b{display:block;font-size:22px;font-weight:800;line-height:1.15}
.dsh-stat span{font-size:12.5px;color:var(--dsh-mute)}

/* ---------- formats ---------- */
.dsh-formats{margin:30px 0 0;text-align:center}
.dsh-formats p{margin:0 0 16px;font-size:13px;color:var(--dsh-mute)}
.dsh-formats div{display:flex;flex-wrap:wrap;justify-content:center;gap:10px 12px}
.dsh-formats span{padding:8px 17px;border-radius:9px;font-size:12.5px;font-weight:600;letter-spacing:.4px;color:#C6D2E6;
  background:rgba(255,255,255,.045);border:1px solid var(--dsh-line);transition:.25s}
.dsh-formats span:hover{color:#fff;border-color:rgba(232,68,42,.45);background:rgba(232,68,42,.1)}

/* ---------- theme overrides (Elementor / Astra set h1 + link colours) ---------- */
#dsHero .dsh-h1,#dsHero .dsh-slide-l h4,#dsHero .dsh-feat h3,#dsHero .dsh-stat b,
#dsHero .dsh-btn,#dsHero .dsh-btn:hover,#dsHero .dsh-btn:visited,#dsHero .dsh-btn:focus,
#dsHero .dsh-cbtn,#dsHero .dsh-sub b{color:#fff!important;-webkit-text-fill-color:#fff!important}
#dsHero .dsh-h1 .dsh-grad{background:linear-gradient(95deg,var(--dsh-accent) 10%,var(--dsh-accent2) 90%)!important;
  -webkit-background-clip:text!important;background-clip:text!important;color:transparent!important;-webkit-text-fill-color:transparent!important}
#dsHero .dsh-badge,#dsHero .dsh-kick,#dsHero .dsh-s5 figcaption b{color:var(--dsh-accent)!important;-webkit-text-fill-color:var(--dsh-accent)!important}
#dsHero .dsh-feat p{color:var(--dsh-mute)!important;-webkit-text-fill-color:var(--dsh-mute)!important}
#dsHero .dsh-feat-go,#dsHero .dsh-fc,#dsHero .dsh-pill{color:#fff!important;-webkit-text-fill-color:#fff!important}
#dsHero .dsh-fc,#dsHero .dsh-feat{text-decoration:none!important}
#dsHero .dsh-ticks span{color:#B6C1D6!important;-webkit-text-fill-color:#B6C1D6!important}
#dsHero .dsh-formats span{color:#C6D2E6!important;-webkit-text-fill-color:#C6D2E6!important}
#dsHero .dsh-stat span,#dsHero .dsh-formats p,#dsHero .dsh-sub{color:var(--dsh-mute)!important;-webkit-text-fill-color:var(--dsh-mute)!important}

/* ---------- responsive ---------- */
@media(max-width:1100px){
  .dsh-feats{grid-template-columns:repeat(3,1fr)}
  .dsh-fc{font-size:11.5px;padding:8px 11px 8px 8px}
}
@media(max-width:900px){
  #dsHero{padding:50px 16px 50px}
  .dsh-top{grid-template-columns:1fr;gap:44px}
  .dsh-h1,.dsh-sub,.dsh-badge{text-align:left}
  .dsh-visual{order:2;display:flex;flex-wrap:wrap;justify-content:center;gap:9px;min-height:0}
  .dsh-ring{display:none}
  .dsh-laptop{order:-1;width:100%;max-width:430px;margin:0 auto 14px}
  .dsh-fc{position:static;order:1;animation:none;box-shadow:none}
  .dsh-fc.is-on{transform:none}
  .dsh-visual:after{content:"";order:0;flex:0 0 100%;height:0}
}
@media(max-width:680px){
  .dsh-feats{grid-template-columns:repeat(2,1fr);gap:12px;margin-top:40px}
  .dsh-stats{display:grid;grid-template-columns:1fr 1fr;gap:18px;justify-content:start}
  .dsh-stat b{font-size:19px}
  .dsh-btns{flex-direction:column;align-items:stretch}
  .dsh-btn{justify-content:center;padding:15px 18px;white-space:nowrap}
  .dsh-visual{gap:8px}
  .dsh-laptop{max-width:330px;margin-bottom:10px}
  .dsh-fc{font-size:11.5px;padding:7px 11px 7px 7px}
  .dsh-fc em{width:20px;height:20px;border-radius:6px}
  .dsh-fc em svg{width:11px;height:11px}
}
@media(max-width:460px){.dsh-feats{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){
  #dsHero *,#dsHero *:before,#dsHero *:after{animation:none!important;transition:opacity .3s!important}
}
/* ---------- lower row: cards + video ---------- */
.dsh-lower{display:grid;grid-template-columns:minmax(0,1.75fr) minmax(0,1fr);gap:22px;margin:58px 0 0;align-items:start}
.dsh-lower.dsh-no-video{grid-template-columns:1fr}
.dsh-lower .dsh-feats{grid-template-columns:repeat(3,1fr);margin:0}
.dsh-hv{min-width:0}
.dsh-hv-box{position:relative;aspect-ratio:16/9;border-radius:15px;overflow:hidden;background:#000;border:1px solid var(--dsh-line);box-shadow:0 20px 44px rgba(0,0,0,.45)}
.dsh-hv-box:fullscreen{border-radius:0;border:0}
.dsh-hv-box iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
#dsHero .dsh-hv button{box-shadow:none;text-transform:none;min-height:0;line-height:1;font-family:inherit}
#dsHero .dsh-hv-poster{position:absolute;inset:0;width:100%;height:100%;padding:0!important;margin:0;border:0!important;border-radius:0!important;background:#000!important;cursor:pointer}
.dsh-hv-poster img{width:100%;height:100%;object-fit:cover;display:block;opacity:.88;transition:.35s}
.dsh-hv-poster:hover img{opacity:1;transform:scale(1.03)}
.dsh-hv-play{position:absolute;left:50%;top:50%;width:64px;height:64px;margin:-32px 0 0 -32px;border-radius:50%;background:linear-gradient(135deg,var(--dsh-accent),var(--dsh-accent2));color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 30px rgba(232,68,42,.5);transition:.3s}
.dsh-hv-poster:hover .dsh-hv-play{transform:scale(1.08)}
.dsh-hv-play svg{width:28px;height:28px;margin-left:3px}
#dsHero .dsh-hv-fs{position:absolute;right:10px;top:10px;z-index:3;width:34px;height:34px;padding:0!important;border-radius:9px!important;border:1px solid rgba(255,255,255,.2)!important;background:rgba(8,10,18,.65)!important;color:#fff!important;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(6px)}
#dsHero .dsh-hv-fs:hover{background:var(--dsh-accent)!important;border-color:var(--dsh-accent)!important}
.dsh-hv-fs svg{width:16px;height:16px}
.dsh-hv-meta{padding:16px 4px 14px;border-bottom:1px solid var(--dsh-line)}
#dsHero .dsh-hv small{display:block;font-size:12.5px;color:var(--dsh-mute)!important;-webkit-text-fill-color:var(--dsh-mute)!important}
#dsHero .dsh-hv-meta h3{margin:4px 0 0;font-size:20px;font-weight:600;color:#fff!important;-webkit-text-fill-color:#fff!important}
.dsh-hv-more{padding:14px 4px 0}
.dsh-hv-strip{display:flex;align-items:center;gap:8px;margin-top:12px}
.dsh-hv-thumbs{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;flex:1;min-width:0}
.dsh-hv-thumbs::-webkit-scrollbar{display:none}
#dsHero .dsh-hv-th{flex:0 0 calc((100% - 30px)/4);aspect-ratio:16/9;padding:0!important;border:2px solid transparent!important;border-radius:8px!important;overflow:hidden;cursor:pointer;background:#000!important;scroll-snap-align:start;transition:.25s}
#dsHero .dsh-hv-th:hover,#dsHero .dsh-hv-th.is-on{border-color:var(--dsh-accent)!important}
.dsh-hv-th img{width:100%;height:100%;object-fit:cover;display:block}
#dsHero .dsh-hv-nav{flex:0 0 28px;width:28px;height:28px;padding:0!important;border-radius:50%!important;border:1px solid var(--dsh-line)!important;background:rgba(255,255,255,.05)!important;color:#fff!important;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center}
#dsHero .dsh-hv-nav:hover{background:var(--dsh-accent)!important;border-color:var(--dsh-accent)!important}
/* ---------- video in the top row (where the laptop was) ---------- */
.dsh-top.dsh-has-video{grid-template-columns:minmax(0,1fr) minmax(0,1.08fr);gap:44px;align-items:center}
.dsh-top.dsh-has-video .dsh-hv{width:100%;max-width:640px;justify-self:end}
/* ---------- marketing banner (image set in Hero Videos > Home Banner) ---------- */
.dsh-banner{display:block;position:relative;z-index:1;margin:54px 0 0;border-radius:18px;overflow:hidden;border:1px solid var(--dsh-line);box-shadow:0 20px 44px rgba(0,0,0,.35);line-height:0;text-decoration:none;transition:transform .25s,box-shadow .25s}
a.dsh-banner:hover{transform:translateY(-2px);box-shadow:0 26px 54px rgba(0,0,0,.5)}
a.dsh-banner:focus-visible{outline:2px solid var(--dsh-accent2);outline-offset:3px}
.dsh-banner img{display:block;width:100%;height:auto;max-height:340px;object-fit:cover;margin:0;border-radius:0}
.dsh-banner + .dsh-lower{margin-top:30px}
@media(max-width:1100px){.dsh-lower{grid-template-columns:1fr}}
@media(max-width:900px){.dsh-top.dsh-has-video{grid-template-columns:1fr;gap:36px}.dsh-top.dsh-has-video .dsh-hv{max-width:720px;justify-self:center;margin:0 auto}.dsh-banner{margin-top:40px;border-radius:14px}}
@media(max-width:680px){.dsh-lower{margin-top:40px}.dsh-lower .dsh-feats{grid-template-columns:repeat(2,1fr)}#dsHero .dsh-hv-th{flex-basis:calc((100% - 20px)/3)}}
@media(max-width:460px){.dsh-lower .dsh-feats{grid-template-columns:1fr}}
</style>

<section id="dsHero">
 <div class="dsh-wrap">

  <?php $dsh_videos = function_exists( 'dsh_get_hero_videos' ) ? dsh_get_hero_videos() : array(); ?>
  <div class="dsh-top<?php echo empty( $dsh_videos ) ? '' : ' dsh-has-video'; ?>">
   <div class="dsh-copy">
    <span class="dsh-badge"><i></i> All-in-One AI Creative Platform</span>

    <h1 class="dsh-h1">Turn Ideas Into<br><span class="dsh-grad">Stunning</span> Creations</h1>

    <p class="dsh-sub">Create professional <b>presentations</b>, images, videos, voiceovers and designs &mdash; faster, with the power of AI. Everything you need to design, present and grow, in one place.</p>

    <div class="dsh-btns">
     <a class="dsh-btn dsh-btn-1" href="/ai-presentation/">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.2 5.8L20 10l-5.8 2.2L12 18l-2.2-5.8L4 10l5.8-2.2L12 2z"/></svg>
      Start Creating Free <span class="dsh-arrow">&rarr;</span>
     </a>
     <a class="dsh-btn dsh-btn-2" href="https://designstager.com/marketplace/">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
      Browse Marketplace
     </a>
    </div>

    <div class="dsh-ticks">
     <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.6 2.6L16 9.5"/></svg> No credit card required</span>
     <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.6 2.6L16 9.5"/></svg> 46 languages</span>
     <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.6 2.6L16 9.5"/></svg> Ready in seconds</span>
    </div>
   </div>

   <?php if ( ! empty( $dsh_videos ) ) : $dsh_v0 = $dsh_videos[0]; ?>
   <div class="dsh-hv">
    <div class="dsh-hv-box" id="dshVidBox" data-id="<?php echo esc_attr( $dsh_v0['id'] ); ?>">
     <button type="button" class="dsh-hv-poster" id="dshVidPoster" aria-label="Play video">
      <img src="https://i.ytimg.com/vi/<?php echo esc_attr( $dsh_v0['id'] ); ?>/hqdefault.jpg" alt="<?php echo esc_attr( $dsh_v0['title'] ); ?>" loading="lazy">
      <span class="dsh-hv-play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
     </button>
     <button type="button" class="dsh-hv-fs" id="dshVidFs" aria-label="Full screen" title="Full screen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg></button>
    </div>
    <div class="dsh-hv-meta">
     <small>This video</small>
     <h3 id="dshVidTitle"><?php echo esc_html( $dsh_v0['title'] ); ?></h3>
    </div>
    <?php if ( count( $dsh_videos ) > 1 ) : ?>
    <div class="dsh-hv-more">
     <small>Watch other related videos</small>
     <div class="dsh-hv-strip">
      <button type="button" class="dsh-hv-nav" data-dir="-1" aria-label="Previous">&lsaquo;</button>
      <div class="dsh-hv-thumbs" id="dshVidThumbs">
       <?php foreach ( $dsh_videos as $dsh_i => $dsh_v ) : ?>
       <button type="button" class="dsh-hv-th<?php echo $dsh_i === 0 ? ' is-on' : ''; ?>" data-id="<?php echo esc_attr( $dsh_v['id'] ); ?>" data-title="<?php echo esc_attr( $dsh_v['title'] ); ?>" title="<?php echo esc_attr( $dsh_v['title'] ); ?>">
        <img src="https://i.ytimg.com/vi/<?php echo esc_attr( $dsh_v['id'] ); ?>/mqdefault.jpg" alt="<?php echo esc_attr( $dsh_v['title'] ); ?>" loading="lazy">
       </button>
       <?php endforeach; ?>
      </div>
      <button type="button" class="dsh-hv-nav" data-dir="1" aria-label="Next">&rsaquo;</button>
     </div>
    </div>
    <?php endif; ?>
   </div>
   <?php else : ?>
   <div class="dsh-visual">
    <svg class="dsh-ring dsh-ring-back" viewBox="0 0 600 440" preserveAspectRatio="none" aria-hidden="true">
     <defs><linearGradient id="dshGoldB" x1="0" y1="0" x2="1" y2="0">
       <stop offset="0" stop-color="#B8801F"/><stop offset=".45" stop-color="#FFE9A8"/><stop offset="1" stop-color="#E8B54A"/>
     </linearGradient></defs>
     <ellipse class="dsh-track-l" cx="300" cy="250" rx="292" ry="130"/>
     <ellipse class="dsh-beam" cx="300" cy="250" rx="292" ry="130"/>
    </svg>
    <svg class="dsh-ring dsh-ring-front" viewBox="0 0 600 440" preserveAspectRatio="none" aria-hidden="true">
     <defs>
      <linearGradient id="dshGoldF" x1="0" y1="0" x2="1" y2="0">
       <stop offset="0" stop-color="#B8801F"/><stop offset=".45" stop-color="#FFE9A8"/><stop offset="1" stop-color="#E8B54A"/>
      </linearGradient>
      <clipPath id="dshLower"><rect x="-40" y="250" width="680" height="250"/></clipPath>
     </defs>
     <g clip-path="url(#dshLower)">
      <ellipse class="dsh-track-l" cx="300" cy="250" rx="292" ry="130"/>
      <ellipse class="dsh-beam" cx="300" cy="250" rx="292" ry="130"/>
     </g>
    </svg>

    <a class="dsh-fc" href="/ai-presentation/" data-i="0"><em class="dsh-ic-ppt"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v12H3z" opacity=".55"/><path d="M6 7h8v2H6zm0 4h6v2H6zm5 7h2v3h-2z"/></svg></em>AI Presentations</a>
    <a class="dsh-fc" href="/ai-studio/" data-i="1"><em class="dsh-ic-img"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v16H3z" opacity=".5"/><path d="M6 16l3.5-4.5 2.5 3 3-3.8L19 16z"/><circle cx="8" cy="8.5" r="1.6"/></svg></em>AI Images</a>
    <a class="dsh-fc" href="/ai-video/" data-i="2"><em class="dsh-ic-vid"><svg viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="5" width="14" height="14" rx="2" opacity=".55"/><path d="M17 10l5-3v10l-5-3z"/><path d="M8 9l4 3-4 3z" fill="#fff"/></svg></em>AI Videos</a>
    <a class="dsh-fc" href="/ai-studio/ai-audio/" data-i="3"><em class="dsh-ic-aud"><svg viewBox="0 0 24 24" fill="currentColor"><rect x="9.5" y="2.5" width="5" height="11" rx="2.5"/><path d="M6 11a6 6 0 0012 0h2a8 8 0 01-7 7.9V22h-2v-3.1A8 8 0 014 11z"/></svg></em>AI Audio &amp; Voice Clone</a>
    <a class="dsh-fc" href="/marketplace/" data-i="4"><em class="dsh-ic-gfx"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16l1 5a3 3 0 01-6 0 3 3 0 01-6 0 3 3 0 01-6 0z"/><path d="M5 11h14v9H5z" opacity=".55"/></svg></em>Marketplace</a>
    <a class="dsh-fc" href="/ai-presentation/browse-templates/" data-i="5"><em class="dsh-ic-tm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/><path d="M7.8 10.6l1.9 1.9 3.6-3.9" stroke-width="2.4"/></svg></em>Template Matcher</a>
    <a class="dsh-fc" href="/customize/" data-i="6"><em class="dsh-ic-cus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2.4" fill="currentColor" stroke="none"/><circle cx="10" cy="17" r="2.4" fill="currentColor" stroke="none"/></svg></em>Customise Designs</a>

    <div class="dsh-laptop">
     <div class="dsh-lid">
      <div class="dsh-screen">
       <div class="dsh-chrome"><b></b><b></b><b></b><u></u></div>

       <div class="dsh-scene dsh-s1 is-on" data-s="0">
        <span class="dsh-scene-t">AI Presentations</span>
        <div class="dsh-rail"><i class="on"></i><i></i><i></i><i></i></div>
        <div class="dsh-canvas">
         <div class="dsh-slide">
          <div class="dsh-slide-l">
           <div class="dsh-kick">Strategy</div>
           <h4>Q4 Growth Plan</h4>
           <div class="dsh-bul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4.5 4.5L19 7"/></svg>Expand into three new markets</div>
           <div class="dsh-bul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4.5 4.5L19 7"/></svg>Double content output with AI</div>
           <div class="dsh-bul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4.5 4.5L19 7"/></svg>Cut design turnaround to 48 hours</div>
          </div>
          <div class="dsh-slide-r">
           <img class="dsh-img" alt="" data-dsh="exec">
          </div>
         </div>
         <div class="dsh-slide-bar"></div>
         <div class="dsh-cbtn">Generate</div>
        </div>
       </div>

       <div class="dsh-scene dsh-s2" data-s="1">
        <span class="dsh-scene-t">AI Images</span>
        <i><img class="dsh-img" alt="" data-dsh="city"></i>
        <i><img class="dsh-img" alt="" data-dsh="artist"></i>
        <i><img class="dsh-img" alt="" data-dsh="music"></i>
        <i><img class="dsh-img" alt="" data-dsh="film"></i>
        <i><img class="dsh-img" alt="" data-dsh="swatch"></i>
        <i><img class="dsh-img" alt="" data-dsh="folio"></i>
       </div>

       <div class="dsh-scene dsh-s3" data-s="2">
        <span class="dsh-scene-t">AI Video</span>
        <div class="dsh-frame">
         <img class="dsh-img" alt="" data-dsh="film">
         <video class="dsh-vid" muted loop playsinline preload="none" disablepictureinpicture></video>
         <div class="dsh-play"></div>
        </div>
        <div class="dsh-track"><i></i></div>
       </div>

       <div class="dsh-scene dsh-s4" data-s="3">
        <span class="dsh-scene-t">AI Audio &amp; Voice</span>
        <div class="dsh-wave">
         <i style="animation-delay:0s"></i><i style="animation-delay:.09s"></i><i style="animation-delay:.18s"></i><i style="animation-delay:.27s"></i><i style="animation-delay:.36s"></i><i style="animation-delay:.45s"></i><i style="animation-delay:.54s"></i><i style="animation-delay:.63s"></i><i style="animation-delay:.72s"></i><i style="animation-delay:.63s"></i><i style="animation-delay:.54s"></i><i style="animation-delay:.45s"></i><i style="animation-delay:.36s"></i><i style="animation-delay:.27s"></i><i style="animation-delay:.18s"></i><i style="animation-delay:.09s"></i><i style="animation-delay:0s"></i>
        </div>
        <span class="dsh-pill">● Voice Clone &amp; Text-to-Speech</span>
       </div>

       <div class="dsh-scene dsh-s5" data-s="4">
        <span class="dsh-scene-t">Marketplace</span>
        <figure><div class="th"><img class="dsh-img" alt="" data-dsh="folio"></div><figcaption>Portfolio Boards<b>Free</b></figcaption></figure>
        <figure><div class="th"><img class="dsh-img" alt="" data-dsh="swatch"></div><figcaption>Brand Palette<b>Free</b></figcaption></figure>
        <figure><div class="th"><img class="dsh-img" alt="" data-dsh="city"></div><figcaption>City Skyline<b>Free</b></figcaption></figure>
        <figure><div class="th"><img class="dsh-img" alt="" data-dsh="exec"></div><figcaption>Business Deck<b>Free</b></figcaption></figure>
        <figure><div class="th"><img class="dsh-img" alt="" data-dsh="artist"></div><figcaption>Creative Set<b>Free</b></figcaption></figure>
        <figure><div class="th"><img class="dsh-img" alt="" data-dsh="music"></div><figcaption>Audio Cover<b>Free</b></figcaption></figure>
       </div>

       <div class="dsh-scene dsh-s6" data-s="5">
        <span class="dsh-scene-t">Template Matcher</span>
        <div class="dsh-tmS"><b>Renewable Energy</b><i></i><i></i><i></i></div>
        <div class="dsh-tmA"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M4 12h15M13 6l6 6-6 6"/></svg></div>
        <div class="dsh-tmL">
         <figure class="on"><img class="dsh-img" alt="" data-dsh="city"><span>98% match</span></figure>
         <figure><img class="dsh-img" alt="" data-dsh="artist"></figure>
         <figure><img class="dsh-img" alt="" data-dsh="folio"></figure>
        </div>
       </div>

       <div class="dsh-scene dsh-s7" data-s="6">
        <span class="dsh-scene-t">Customise Designs</span>
        <div class="dsh-czC"><img class="dsh-img" alt="" data-dsh="swatch"><span>Custom</span></div>
        <div class="dsh-czP">
         <div class="dsh-czR">Colours <em><u style="background:#E8442A"></u><u style="background:#2563EB"></u><u style="background:#34D399"></u></em></div>
         <div class="dsh-czR">Typography <em><s></s></em></div>
         <div class="dsh-czR">Layout <em><s></s></em></div>
         <div class="dsh-czB">Request Customisation</div>
        </div>
       </div>
      </div>
     </div>
     <div class="dsh-base"></div>
    </div>
   </div>
   <?php endif; ?>
  </div>

  <?php echo function_exists( 'dsh_home_banner_html' ) ? dsh_home_banner_html() : ''; // phpcs:ignore -- built and escaped in dsh_home_banner_html() ?>
  <div class="dsh-lower dsh-no-video">
  <div class="dsh-feats">
   <a class="dsh-feat" href="/ai-presentation/">
    <div class="dsh-feat-ic dsh-ic-ppt"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v12H3z" opacity=".55"/><path d="M6 7h8v2H6zm0 4h6v2H6zm5 7h2v3h-2z"/></svg></div>
    <h3>AI Presentation Maker</h3>
    <p>Type a prompt, get a fully designed, editable deck &mdash; download as PPTX.</p>
    <div class="dsh-feat-go">&rarr;</div>
   </a>
   <a class="dsh-feat" href="/ai-studio/">
    <div class="dsh-feat-ic dsh-ic-img"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v16H3z" opacity=".5"/><path d="M6 16l3.5-4.5 2.5 3 3-3.8L19 16z"/><circle cx="8" cy="8.5" r="1.6"/></svg></div>
    <h3>AI Images</h3>
    <p>Generate stunning visuals, backgrounds and product shots in seconds.</p>
    <div class="dsh-feat-go">&rarr;</div>
   </a>
   <a class="dsh-feat" href="/ai-video/">
    <div class="dsh-feat-ic dsh-ic-vid"><svg viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="5" width="14" height="14" rx="2" opacity=".55"/><path d="M17 10l5-3v10l-5-3z"/><path d="M8 9l4 3-4 3z" fill="#fff"/></svg></div>
    <h3>AI Video</h3>
    <p>Turn a prompt or an image into a polished short video clip.</p>
    <div class="dsh-feat-go">&rarr;</div>
   </a>
   <a class="dsh-feat" href="/ai-studio/ai-audio/">
    <div class="dsh-feat-ic dsh-ic-aud"><svg viewBox="0 0 24 24" fill="currentColor"><rect x="9.5" y="2.5" width="5" height="11" rx="2.5"/><path d="M6 11a6 6 0 0012 0h2a8 8 0 01-7 7.9V22h-2v-3.1A8 8 0 014 11z"/></svg></div>
    <h3>AI Audio &amp; Voice</h3>
    <p>Natural voiceovers, music and your own cloned voice for any script.</p>
    <div class="dsh-feat-go">&rarr;</div>
   </a>
   <a class="dsh-feat" href="https://designstager.com/ai-presentation/browse-templates/">
     <div class="dsh-feat-ic dsh-ic-tm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/><path d="M7.8 10.6l1.9 1.9 3.6-3.9" stroke-width="2.4"/></svg></div>
     <h3>Template Matcher</h3>
     <p>500+ ready templates &mdash; describe what you need and we match the best one for you.</p>
     <div class="dsh-feat-go">&rarr;</div>
    </a>
    <a class="dsh-feat" href="https://designstager.com/customize/">
     <div class="dsh-feat-ic dsh-ic-cus"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2.4" fill="currentColor" stroke="none"/><circle cx="10" cy="17" r="2.4" fill="currentColor" stroke="none"/></svg></div>
     <h3>Customize</h3>
     <p>Send us your requirements and our designers will customise any template for you.</p>
     <div class="dsh-feat-go">&rarr;</div>
    </a>
   </div>

  </div>

<script>
(function(){
  var box=document.getElementById('dshVidBox'); if(!box) return;
  var titleEl=document.getElementById('dshVidTitle'), thumbs=document.getElementById('dshVidThumbs');
  function play(id){
    var old=box.querySelector('iframe'); if(old) old.remove();
    var p=document.getElementById('dshVidPoster'); if(p) p.remove();
    var f=document.createElement('iframe');
    f.src='https://www.youtube-nocookie.com/embed/'+id+'?autoplay=1&rel=0&modestbranding=1&playsinline=1';
    f.allow='autoplay; encrypted-media; picture-in-picture; fullscreen';
    f.setAttribute('allowfullscreen',''); f.title='Designstager video';
    box.insertBefore(f, box.firstChild); box.dataset.id=id;
  }
  var poster=document.getElementById('dshVidPoster');
  if(poster) poster.addEventListener('click',function(){ play(box.dataset.id); });
  document.getElementById('dshVidFs').addEventListener('click',function(){
    if(!box.querySelector('iframe')) play(box.dataset.id);
    var fs=box.requestFullscreen||box.webkitRequestFullscreen; if(fs) fs.call(box);
  });
  if(thumbs){
    thumbs.addEventListener('click',function(e){
      var b=e.target.closest('.dsh-hv-th'); if(!b) return;
      thumbs.querySelectorAll('.dsh-hv-th').forEach(function(t){t.classList.remove('is-on');});
      b.classList.add('is-on'); titleEl.textContent=b.dataset.title; play(b.dataset.id);
    });
    document.querySelectorAll('#dsHero .dsh-hv-nav').forEach(function(n){
      n.addEventListener('click',function(){ thumbs.scrollBy({left:(+n.dataset.dir)*thumbs.clientWidth*0.8,behavior:'smooth'}); });
    });
  }
})();
</script>

 </div>
</section>

<script>
(function(){
  var root=document.getElementById('dsHero');if(!root)return;
  if(!root.querySelector('.dsh-laptop'))return;   /* the video sits in this place: nothing to animate */

  /* ---- images shown inside the laptop screen. Change these URLs to your own. ---- */
  var BASE='https://designstager.com/wp-content/uploads/2026/09/';
  var PIC={
    exec  : BASE+'ds-blog-diverse-executive-team-discussing-strate-1789541394.png',
    city  : BASE+'ds-blog-vibrant-sunrise-over-bustling-city-skyli-1789627849.png',
    artist: BASE+'ds-blog-artist-sketching-vibrant-illustrations-o-1789627853.png',
    music : BASE+'ds-blog-musician-recording-acoustic-sounds-besid-1789627857.png',
    film  : BASE+'ds-blog-filmmaker-directing-a-lively-street-perf-1789627855.png',
    swatch: BASE+'ds-blog-art-director-arranging-color-swatches-an-1789455034.png',
    folio : BASE+'ds-blog-designer-arranging-printed-portfolio-boa-1789455030.png'
  };
  function fill(scene){
    scene.querySelectorAll('img[data-dsh]').forEach(function(img){
      if(img.getAttribute('src'))return;
      var u=PIC[img.getAttribute('data-dsh')];if(u)img.setAttribute('src',u);
    });
  }

  /* ---- the clip that plays inside the laptop on the AI Video screen ---- */
  var VIDEO='https://designstager.com/wp-content/uploads/2026/09/ds-hub-video-530-c0bORn.mp4';
  var vScene=root.querySelector('.dsh-s3'),
      vEl=root.querySelector('.dsh-vid'),
      vFrame=root.querySelector('.dsh-frame'),
      vBar=root.querySelector('.dsh-track i');
  var conn=navigator.connection||{};
  var canPlay=!!vEl&&window.matchMedia('(min-width:901px)').matches
              &&!window.matchMedia('(prefers-reduced-motion:reduce)').matches&&!conn.saveData;
  if(canPlay){
    vEl.addEventListener('playing',function(){vFrame.classList.add('is-playing');vScene.classList.add('is-live');});
    vEl.addEventListener('timeupdate',function(){
      if(vBar&&vEl.duration)vBar.style.width=Math.min(100,(vEl.currentTime/vEl.duration)*100)+'%';
    });
    vEl.addEventListener('error',function(){vFrame.classList.remove('is-playing');vScene.classList.remove('is-live');});
  }
  function videoPlay(){
    if(!canPlay)return;
    if(!vEl.getAttribute('src'))vEl.setAttribute('src',VIDEO);
    var p=vEl.play();if(p&&p.catch)p.catch(function(){});
  }
  function videoStop(){
    if(!vEl)return;
    try{vEl.pause();}catch(e){}
    vFrame.classList.remove('is-playing');vScene.classList.remove('is-live');
  }

  var scenes=root.querySelectorAll('.dsh-scene'),cards=root.querySelectorAll('.dsh-fc'),i=0,timer=null;
  var order=[0,1,2,3,5,6,4];
  function show(n){
    scenes.forEach(function(s){var on=+s.dataset.s===n;s.classList.toggle('is-on',on);if(on)fill(s);});
    cards.forEach(function(c){c.classList.toggle('is-on',+c.dataset.i===n);});
    if(n===2)videoPlay();else videoStop();
  }
  function next(){i=(i+1)%order.length;show(order[i]);}
  function start(){stop();if(!window.matchMedia('(prefers-reduced-motion:reduce)').matches)timer=setInterval(next,3000);}
  function stop(){if(timer){clearInterval(timer);timer=null;}}
  document.addEventListener('visibilitychange',function(){if(document.hidden)videoStop();});
  show(0);start();
  cards.forEach(function(c){
    c.addEventListener('mouseenter',function(){stop();i=order.indexOf(+c.dataset.i);show(+c.dataset.i);});
    c.addEventListener('mouseleave',start);
  });
  document.addEventListener('visibilitychange',function(){document.hidden?stop():start();});
})();
</script>
<!-- ========== DS HERO V2 — END ========== -->

    <?php
    return ob_get_clean();
});

/* ---------- DS Hero Videos (admin page + helper) ---------- */
if ( ! function_exists( 'dsh_yt_id' ) ) {
	function dsh_yt_id( $url ) {
		$url = trim( $url );
		if ( preg_match( '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([A-Za-z0-9_-]{11})~', $url, $m ) ) return $m[1];
		if ( preg_match( '~^[A-Za-z0-9_-]{11}$~', $url ) ) return $url;
		return '';
	}
}
if ( ! function_exists( 'dsh_yt_title' ) ) {
	function dsh_yt_title( $id ) {
		$key    = 'dsh_yt_t_' . $id;
		$cached = get_transient( $key );
		if ( $cached !== false ) return (string) $cached;
		$title = '';
		$res   = wp_remote_get( 'https://www.youtube.com/oembed?format=json&url=' . rawurlencode( 'https://www.youtube.com/watch?v=' . $id ), array( 'timeout' => 6 ) );
		if ( ! is_wp_error( $res ) && (int) wp_remote_retrieve_response_code( $res ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $res ), true );
			if ( ! empty( $data['title'] ) ) $title = sanitize_text_field( $data['title'] );
		}
		set_transient( $key, $title, $title !== '' ? WEEK_IN_SECONDS : HOUR_IN_SECONDS );
		return $title;
	}
}
if ( ! function_exists( 'dsh_get_hero_videos' ) ) {
	function dsh_get_hero_videos() {
		$raw = (string) get_option( 'dsh_hero_videos', '' );
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( $line === '' ) continue;
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$title = count( $parts ) === 2 ? $parts[0] : '';
			$id    = dsh_yt_id( count( $parts ) === 2 ? $parts[1] : $parts[0] );
			if ( ! $id ) continue;
			$yt    = dsh_yt_title( $id );
			$out[] = array( 'id' => $id, 'title' => $yt !== '' ? $yt : ( $title !== '' ? $title : 'Designstager video' ) );
		}
		return $out;
	}
}
/* ---------- Home banner: one image between the hero and the six cards ---------- */
if ( ! function_exists( 'dsh_home_banner' ) ) {
	function dsh_home_banner() {
		$o = get_option( 'dsh_home_banner', array() );
		$o = is_array( $o ) ? $o : array();
		return array(
			'on'   => empty( $o['on'] ) ? 0 : 1,
			'img'  => isset( $o['img'] ) ? (string) $o['img'] : '',
			'link' => isset( $o['link'] ) ? (string) $o['link'] : '',
			'alt'  => isset( $o['alt'] ) ? (string) $o['alt'] : '',
			'tab'  => empty( $o['tab'] ) ? 0 : 1,
		);
	}
	function dsh_home_banner_html() {
		$b = dsh_home_banner();
		if ( ! $b['on'] || '' === $b['img'] ) {
			return '';
		}
		$img = '<img src="' . esc_url( $b['img'] ) . '" alt="' . esc_attr( $b['alt'] ) . '" loading="lazy" decoding="async">';
		if ( '' !== $b['link'] ) {
			return '<a class="dsh-banner" href="' . esc_url( $b['link'] ) . '"' . ( $b['tab'] ? ' target="_blank" rel="noopener"' : '' ) . '>' . $img . '</a>';
		}
		return '<div class="dsh-banner">' . $img . '</div>';
	}
}
add_action( 'admin_menu', function () {
	add_submenu_page( 'dsh-hero-videos', 'Home Banner', 'Home Banner', 'manage_options', 'dsh-home-banner', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( isset( $_POST['dsh_hb_save'] ) && check_admin_referer( 'dsh_home_banner_save' ) ) {
			update_option( 'dsh_home_banner', array(
				'on'   => empty( $_POST['dsh_hb_on'] ) ? 0 : 1,
				'img'  => esc_url_raw( trim( (string) wp_unslash( $_POST['dsh_hb_img'] ?? '' ) ), array( 'http', 'https' ) ),
				'link' => esc_url_raw( trim( (string) wp_unslash( $_POST['dsh_hb_link'] ?? '' ) ), array( 'http', 'https' ) ),
				'alt'  => sanitize_text_field( wp_unslash( $_POST['dsh_hb_alt'] ?? '' ) ),
				'tab'  => empty( $_POST['dsh_hb_tab'] ) ? 0 : 1,
			) );
			echo '<div class="notice notice-success is-dismissible"><p>Banner saved.</p></div>';
		}
		$b = dsh_home_banner();
		?>
		<div class="wrap">
			<h1>Home Banner</h1>
			<p>One image shown on the home page, between the top section and the six feature cards. It is as wide as the page content and keeps its own proportions.</p>
			<form method="post">
				<?php wp_nonce_field( 'dsh_home_banner_save' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row">Show the banner</th><td><label><input type="checkbox" name="dsh_hb_on" value="1" <?php checked( $b['on'], 1 ); ?>> Show it on the home page</label></td></tr>
					<tr><th scope="row"><label for="dsh-hb-img">Image</label></th><td>
						<div id="dsh-hb-prev" style="margin:0 0 10px;max-width:640px;<?php echo '' === $b['img'] ? 'display:none' : ''; ?>"><img src="<?php echo esc_url( $b['img'] ); ?>" alt="" style="display:block;max-width:100%;height:auto;border-radius:10px;border:1px solid #c3c4c7"></div>
						<button type="button" class="button" id="dsh-hb-pick">Upload or choose image</button>
						<button type="button" class="button-link" id="dsh-hb-clear" style="margin-left:10px;color:#b32d2e">Remove</button>
						<p style="margin:8px 0 0"><input type="url" class="large-text code" id="dsh-hb-img" name="dsh_hb_img" value="<?php echo esc_attr( $b['img'] ); ?>" placeholder="https://"></p>
						<p class="description">Best size: 2560 x 400 pixels (a wide strip), JPG, PNG or WebP. A taller image is cut to 340 pixels high on a computer screen, so keep text in the middle. On a phone the same image is shown smaller.</p></td></tr>
					<tr><th scope="row"><label for="dsh-hb-link">Link (optional)</label></th><td><input type="url" class="large-text code" id="dsh-hb-link" name="dsh_hb_link" value="<?php echo esc_attr( $b['link'] ); ?>" placeholder="https://">
						<p><label><input type="checkbox" name="dsh_hb_tab" value="1" <?php checked( $b['tab'], 1 ); ?>> Open the link in a new tab</label></p></td></tr>
					<tr><th scope="row"><label for="dsh-hb-alt">Description for screen readers</label></th><td><input type="text" class="large-text" id="dsh-hb-alt" name="dsh_hb_alt" value="<?php echo esc_attr( $b['alt'] ); ?>" placeholder="e.g. Diwali offer: 30% off all plans">
						<p class="description">Say what the banner says. Leave empty only if the image is decoration.</p></td></tr>
				</table>
				<p class="submit"><button type="submit" name="dsh_hb_save" value="1" class="button button-primary">Save Banner</button></p>
			</form>
			<script>
			(function () {
				var input = document.getElementById('dsh-hb-img'), prev = document.getElementById('dsh-hb-prev');
				function set(url) { input.value = url || ''; prev.querySelector('img').src = url || ''; prev.style.display = url ? '' : 'none'; }
				document.getElementById('dsh-hb-clear').addEventListener('click', function () { set(''); });
				document.getElementById('dsh-hb-pick').addEventListener('click', function () {
					if (!window.wp || !wp.media) return;
					var frame = wp.media({ title: 'Choose the banner image', button: { text: 'Use this image' }, library: { type: 'image' }, multiple: false });
					frame.on('select', function () { set(frame.state().get('selection').first().toJSON().url); });
					frame.open();
				});
			})();
			</script>
		</div>
		<?php
	} );
}, 20 );
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( false !== strpos( (string) $hook, 'dsh-home-banner' ) ) {
		wp_enqueue_media();
	}
} );
add_action( 'admin_menu', function () {
	add_menu_page( 'Hero Videos', 'Hero Videos', 'manage_options', 'dsh-hero-videos', function () {
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( isset( $_POST['dsh_hero_videos'] ) && check_admin_referer( 'dsh_hero_videos_save' ) ) {
			update_option( 'dsh_hero_videos', sanitize_textarea_field( wp_unslash( $_POST['dsh_hero_videos'] ) ) );
			echo '<div class="notice notice-success is-dismissible"><p>Videos saved.</p></div>';
		}
		$val   = (string) get_option( 'dsh_hero_videos', '' );
		?>
		<div class="wrap">
			<h1>Homepage Hero Videos</h1>
			<p>Paste one YouTube link per line. The first line is the main video; all lines show as thumbnails below it. The title is taken automatically from YouTube.</p>
			<p>Example: <code>https://www.youtube.com/watch?v=XXXXXXXXXXX</code></p>
			<form method="post">
				<?php wp_nonce_field( 'dsh_hero_videos_save' ); ?>
				<textarea name="dsh_hero_videos" rows="12" class="large-text code"><?php echo esc_textarea( $val ); ?></textarea>
				<?php $dsh_list = dsh_get_hero_videos(); ?>
				<p><?php echo (int) count( $dsh_list ); ?> valid video(s) found. Leave empty to hide the video box.</p>
				<?php if ( $dsh_list ) : ?><ol><?php foreach ( $dsh_list as $dsh_x ) : ?><li><?php echo esc_html( $dsh_x['title'] ); ?> <code><?php echo esc_html( $dsh_x['id'] ); ?></code></li><?php endforeach; ?></ol><?php endif; ?>
				<?php submit_button( 'Save Videos' ); ?>
			</form>
		</div>
		<?php
	}, 'dashicons-video-alt3', 58 );
} );
