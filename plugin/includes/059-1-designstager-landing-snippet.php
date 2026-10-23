<?php
/**
 * Code Snippets export — 1 designstager-landing-snippet
 *
 * Snippet ID : 745
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-059
 * Modified   : 2026-09-09 02:41:23
 * Load order : 059 of 144
 */
/**
 * Designstager — Landing / "Become a Creator" page — WordPress embed snippet.
 *
 * Light colors: designstager_landing_page_light_mode (Luminous Creator System).
 * Dark colors: designstager_landing_page (dark reference export) — brand-dark
 * #0f172a, brand-card #1e293b, brand-border #334155, brand-blue #3b82f6,
 * gradient text #60a5fa -> #c084fc -> #f43f5e, body radial-gradient
 * rgb(15,30,60) -> rgb(10,15,30). Both color sets are exactly what's in the
 * two Stitch exports — nothing invented.
 *
 * Font: Poppins (per request), replacing the exports' original font.
 * Adds a fixed top-left light/dark toggle button (persists via localStorage).
 *
 * INSTALL
 * 1) Paste this whole file into Code Snippets -> Add New -> Run everywhere,
 *    and activate.
 * 2) Put the shortcode  [designstager_landing]  on your homepage.
 * 3) Sign in / auth buttons link into the designstager-portal shortcode page
 *    (assumed at /creator-portal/ — change DS_LANDING_PORTAL_URL if different).
 *
 * NOTE: the [designstager_portal] app itself is a pre-built JS bundle (no
 * source available here), so this toggle only covers this landing page and
 * the agreement page — the portal keeps its own fixed dark theme.
 */

if (!defined('ABSPATH')) { exit; }

define('DS_LANDING_VER', '2.0.0');

// Points at the WordPress page where you put [designstager_portal_ui].
// Change the slug below if you publish it somewhere other than /creator-portal/.
if (!defined('DS_LANDING_PORTAL_URL')) {
    define('DS_LANDING_PORTAL_URL', home_url('/creator-portal/'));
}

function ds_landing_register_assets() {
    wp_register_script(
        'ds-tailwind-cdn',
        'https://cdn.tailwindcss.com?plugins=forms,container-queries',
        array(),
        null,
        false
    );
    wp_register_style(
        'ds-landing-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'ds_landing_register_assets');

function ds_landing_inline_config() {
    return "tailwind.config = {\n"
        . "  darkMode: \"class\",\n"
        . "  theme: {\n"
        . "    extend: {\n"
        . "      colors: {\n"
        . "        \"surface-container-low\": \"#f2f3ff\", \"outline\": \"#737686\",\n"
        . "        \"on-secondary-fixed-variant\": \"#8e0048\", \"tertiary-fixed-dim\": \"#ffb596\",\n"
        . "        \"surface\": \"#faf8ff\", \"on-error\": \"#ffffff\", \"on-tertiary-fixed\": \"#360f00\",\n"
        . "        \"primary-fixed\": \"#dbe1ff\", \"on-secondary-container\": \"#fffbff\",\n"
        . "        \"surface-container-high\": \"#e2e7ff\", \"on-secondary-fixed\": \"#3f001c\",\n"
        . "        \"outline-variant\": \"#c3c6d7\", \"surface-variant\": \"#dae2fd\", \"tertiary\": \"#943700\",\n"
        . "        \"on-tertiary-container\": \"#ffede6\", \"inverse-surface\": \"#283044\",\n"
        . "        \"on-error-container\": \"#93000a\", \"tertiary-container\": \"#bc4800\",\n"
        . "        \"on-tertiary\": \"#ffffff\", \"secondary-fixed\": \"#ffd9e2\", \"surface-container\": \"#eaedff\",\n"
        . "        \"on-surface-variant\": \"#434655\", \"secondary\": \"#b5005d\", \"error\": \"#ba1a1a\",\n"
        . "        \"surface-tint\": \"#0053db\", \"surface-bright\": \"#faf8ff\", \"inverse-primary\": \"#b4c5ff\",\n"
        . "        \"secondary-container\": \"#da2676\", \"error-container\": \"#ffdad6\", \"tertiary-fixed\": \"#ffdbcd\",\n"
        . "        \"background\": \"#faf8ff\", \"surface-container-lowest\": \"#ffffff\",\n"
        . "        \"secondary-fixed-dim\": \"#ffb1c7\", \"primary-fixed-dim\": \"#b4c5ff\", \"on-background\": \"#131b2e\",\n"
        . "        \"primary-container\": \"#2563eb\", \"on-primary-fixed\": \"#00174b\",\n"
        . "        \"on-tertiary-fixed-variant\": \"#7d2d00\", \"on-primary-container\": \"#eeefff\",\n"
        . "        \"inverse-on-surface\": \"#eef0ff\", \"primary\": \"#004ac6\", \"surface-container-highest\": \"#dae2fd\",\n"
        . "        \"on-primary\": \"#ffffff\", \"surface-dim\": \"#d2d9f4\", \"on-surface\": \"#131b2e\",\n"
        . "        \"on-secondary\": \"#ffffff\", \"on-primary-fixed-variant\": \"#003ea8\",\n"
        . "        \"brand-dark\": \"#0f172a\", \"brand-card\": \"#1e293b\", \"brand-input\": \"#0f172a\",\n"
        . "        \"brand-border\": \"#334155\", \"brand-blue\": \"#3b82f6\"\n"
        . "      },\n"
        . "      borderRadius: { DEFAULT: \"0.25rem\", lg: \"0.5rem\", xl: \"0.75rem\", full: \"9999px\" },\n"
        . "      spacing: {\n"
        . "        \"container-max\": \"1280px\", \"stack-sm\": \"0.5rem\", \"gutter\": \"1.5rem\",\n"
        . "        \"section-gap\": \"5rem\", \"margin-x\": \"2rem\", \"stack-lg\": \"2rem\", \"stack-md\": \"1rem\"\n"
        . "      },\n"
        . "      fontFamily: {\n"
        . "        sans: [\"Poppins\", \"sans-serif\"],\n"
        . "        \"headline-md\": [\"Poppins\"], \"headline-lg\": [\"Poppins\"],\n"
        . "        \"body-md\": [\"Poppins\"], \"body-lg\": [\"Poppins\"],\n"
        . "        \"label-sm\": [\"Poppins\"], \"headline-lg-mobile\": [\"Poppins\"],\n"
        . "        \"label-md\": [\"Poppins\"], \"display-lg\": [\"Poppins\"]\n"
        . "      },\n"
        . "      fontSize: {\n"
        . "        \"headline-md\": [\"24px\", { lineHeight: \"1.3\", fontWeight: \"600\" }],\n"
        . "        \"headline-lg\": [\"48px\", { lineHeight: \"1.2\", letterSpacing: \"-0.02em\", fontWeight: \"700\" }],\n"
        . "        \"body-md\": [\"16px\", { lineHeight: \"1.6\", fontWeight: \"400\" }],\n"
        . "        \"body-lg\": [\"18px\", { lineHeight: \"1.6\", fontWeight: \"400\" }],\n"
        . "        \"label-sm\": [\"12px\", { lineHeight: \"1.4\", letterSpacing: \"0.02em\", fontWeight: \"500\" }],\n"
        . "        \"headline-lg-mobile\": [\"36px\", { lineHeight: \"1.2\", letterSpacing: \"-0.02em\", fontWeight: \"700\" }],\n"
        . "        \"label-md\": [\"14px\", { lineHeight: \"1.4\", letterSpacing: \"0.01em\", fontWeight: \"600\" }],\n"
        . "        \"display-lg\": [\"72px\", { lineHeight: \"1.1\", letterSpacing: \"-0.04em\", fontWeight: \"800\" }]\n"
        . "      }\n"
        . "    }\n"
        . "  }\n"
        . "};";
}

function ds_landing_inline_css() {
    return ".ds-landing-root { background: #faf8ff; color: #131b2e; font-family: 'Poppins', sans-serif; position: relative; transition: background .25s ease, color .25s ease; }"
         . "html.dark .ds-landing-root { background: radial-gradient(circle at 10% 20%, rgb(15,30,60) 0%, rgb(10,15,30) 90%); color: #f8fafc; }"
         . ".ds-landing-root .text-gradient { background: linear-gradient(90deg, #3b82f6 0%, #a855f7 50%, #e11d48 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }"
         . "html.dark .ds-landing-root .text-gradient { background: linear-gradient(90deg, #60a5fa 0%, #c084fc 50%, #f43f5e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }"
         . ".ds-theme-toggle { position: fixed; top: 1rem; left: 1rem; z-index: 9999; width: 44px; height: 44px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,.12); cursor: pointer; }"
         . "html.dark .ds-theme-toggle { background: #1e293b; border-color: #334155; }"
         . ".ds-theme-toggle .ds-icon-sun { display: block; } .ds-theme-toggle .ds-icon-moon { display: none; }"
         . "html.dark .ds-theme-toggle .ds-icon-sun { display: none; } html.dark .ds-theme-toggle .ds-icon-moon { display: block; }";
}

function ds_landing_toggle_script() {
    return "(function(){"
         . "if (window.__dsThemeInit) return; window.__dsThemeInit = true;"
         . "var saved = null; try { saved = localStorage.getItem('ds-theme'); } catch(e){}"
         . "if (saved === 'dark') document.documentElement.classList.add('dark');"
         . "document.addEventListener('click', function(e){"
         . "  var btn = e.target.closest && e.target.closest('.ds-theme-toggle');"
         . "  if (!btn) return;"
         . "  var isDark = document.documentElement.classList.toggle('dark');"
         . "  try { localStorage.setItem('ds-theme', isDark ? 'dark' : 'light'); } catch(err){}"
         . "});"
         . "})();";
}

function ds_landing_shortcode($atts) {
    wp_enqueue_script('ds-tailwind-cdn');
    wp_add_inline_script('ds-tailwind-cdn', ds_landing_inline_config());
    wp_add_inline_script('ds-tailwind-cdn', ds_landing_toggle_script());
    wp_enqueue_style('ds-landing-fonts');
    wp_add_inline_style('ds-landing-fonts', ds_landing_inline_css());

    $portal_url = esc_url(DS_LANDING_PORTAL_URL);

    ob_start();
    ?>
<div class="ds-landing-root antialiased text-on-surface bg-background">
<!-- Light/Dark Toggle -->
<button type="button" class="ds-theme-toggle" aria-label="Toggle light and dark theme">
<svg class="ds-icon-sun w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
<svg class="ds-icon-moon w-5 h-5 text-slate-200" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</button>
<!-- BEGIN: Header -->
<header class="flex justify-between items-center px-8 py-6 max-w-[1280px] mx-auto w-full">
<nav>
<a class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors" href="<?php echo esc_url(rtrim($portal_url, '/') . '/#dashboard'); ?>">Sign in</a>
</nav>
</header>
<!-- END: Header -->
<!-- BEGIN: Main Content -->
<main class="max-w-[1280px] mx-auto px-8 pt-12 pb-24 grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
<!-- Left Column: Copy & Features -->
<section>
<!-- Badge -->
<div class="inline-flex items-center space-x-2 bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20 rounded-full px-4 py-1.5 text-sm font-medium mb-8 shadow-sm dark:shadow-none">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
<span class="">Designstager Creator Portal</span>
</div>
<!-- Headline -->
<h1 class="text-6xl md:text-7xl font-extrabold tracking-tight leading-[1.1] text-slate-900 dark:text-white mb-6">
        Create. <span class="text-gradient">Upload.</span><br/>Earn.
      </h1>
<p class="text-lg text-slate-600 dark:text-slate-300 mb-12 max-w-lg leading-relaxed">
        Share your creative work with the Designstager community and earn from your digital content.
      </p>
<!-- Feature Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
<!-- Card 1 -->
<div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:border-blue-300 hover:shadow-md dark:bg-brand-card/50 dark:border-brand-border/50 dark:shadow-none dark:hover:bg-brand-card dark:hover:border-brand-border transition-all">
<div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mb-4 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</div>
<h3 class="font-semibold text-slate-900 dark:text-white mb-2">Upload in minutes</h3>
<p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">A guided wizard for decks, graphics, motion and video.</p>
</div>
<!-- Card 2 -->
<div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:border-blue-300 hover:shadow-md dark:bg-brand-card/50 dark:border-brand-border/50 dark:shadow-none dark:hover:bg-brand-card dark:hover:border-brand-border transition-all">
<div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mb-4 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</div>
<h3 class="font-semibold text-slate-900 dark:text-white mb-2">Track every download</h3>
<p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">Live analytics on views, downloads and earnings.</p>
</div>
<!-- Card 3 -->
<div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:border-blue-300 hover:shadow-md dark:bg-brand-card/50 dark:border-brand-border/50 dark:shadow-none dark:hover:bg-brand-card dark:hover:border-brand-border transition-all">
<div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mb-4 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</div>
<h3 class="font-semibold text-slate-900 dark:text-white mb-2">Secure payouts</h3>
<p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">Tokenized payment details and audited withdrawals.</p>
</div>
</div>
<!-- Stats -->
<div class="flex items-center space-x-6 text-sm">
<div><span class="font-bold text-slate-900 dark:text-white">18k+</span> <span class="text-slate-600 dark:text-slate-400">creators</span></div>
<div><span class="font-bold text-slate-900 dark:text-white">2.4M</span> <span class="text-slate-600 dark:text-slate-400">downloads</span></div>
<div><span class="font-bold text-slate-900 dark:text-white">$6.1M</span> <span class="text-slate-600 dark:text-slate-400">paid out</span></div>
</div>
</section>
<!-- Right Column: Auth Form -->
<section class="lg:pl-12 pt-8 lg:pt-0">
<div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-lg dark:bg-brand-card dark:border-brand-border dark:shadow-2xl">
<h2 class="text-2xl font-bold mb-3 text-slate-900 dark:text-white">Become a Creator</h2>
<p class="text-slate-600 dark:text-slate-400 text-sm mb-8 leading-relaxed">
          Registration takes a couple of minutes. You can finish your creator profile later.
        </p>
<div class="space-y-4">
<!-- Google Auth -->
<a href="<?php echo esc_url($portal_url); ?>" class="w-full flex items-center px-4 py-3 bg-white hover:bg-slate-50 border border-slate-200 shadow-sm dark:bg-brand-input dark:hover:bg-brand-input/80 dark:border-brand-border dark:shadow-none rounded-xl transition-colors group">
<svg class="w-5 h-5 mr-3" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
<path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
<path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
<path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
</svg>
<span class="text-sm font-medium text-slate-700 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white">Continue with Google</span>
</a>
<!-- Microsoft Auth -->
<a href="<?php echo esc_url($portal_url); ?>" class="w-full flex items-center px-4 py-3 bg-white hover:bg-slate-50 border border-slate-200 shadow-sm dark:bg-brand-input dark:hover:bg-brand-input/80 dark:border-brand-border dark:shadow-none rounded-xl transition-colors group">
<svg class="w-5 h-5 mr-3" viewbox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
<rect fill="#f25022" height="9" width="9" x="1" y="1"></rect>
<rect fill="#7fba00" height="9" width="9" x="11" y="1"></rect>
<rect fill="#00a4ef" height="9" width="9" x="1" y="11"></rect>
<rect fill="#ffb900" height="9" width="9" x="11" y="11"></rect>
</svg>
<span class="text-sm font-medium text-slate-700 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white">Continue with Microsoft</span>
</a>
<!-- Email Auth -->
<a href="<?php echo esc_url($portal_url); ?>" class="w-full flex items-center px-4 py-3 bg-white hover:bg-slate-50 border border-slate-200 shadow-sm dark:bg-brand-input dark:hover:bg-brand-input/80 dark:border-brand-border dark:shadow-none rounded-xl transition-colors group">
<svg class="w-5 h-5 mr-3 text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-slate-300" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
<span class="text-sm font-medium text-slate-700 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white">Continue with Email</span>
</a>
</div>
<div class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
          Already a creator? <a class="text-primary hover:text-blue-700 hover:underline dark:text-brand-blue dark:hover:text-blue-400 font-medium" href="<?php echo esc_url(rtrim($portal_url, '/') . '/#dashboard'); ?>">Go to your dashboard</a>
</div>
</div>
</section>
</main>
<!-- END: Main Content -->
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('designstager_landing', 'ds_landing_shortcode');
