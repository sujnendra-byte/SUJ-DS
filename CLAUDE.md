# DesignStager plugin — notes for Claude

## What this is
The WordPress plugin that runs designstager.com (owner: Laxmi, not a developer — explain in plain words).
`plugin/designstager-v42.php` is the main file. It loads `plugin/includes/NNN-*.php` in the order listed in
`$dsv42_snippets`, each inside try/catch, on `plugins_loaded` priority 1. Each include was a Code Snippets
snippet; its header says which staging snippet id it came from. `plugin/assets/sc/` holds cached copies of big
inline scripts (`script-cache.php` swaps an inline script for its file only when the bytes match exactly — if
you change an inline script it simply stays inline; do NOT edit files in assets/sc/ by hand).

Features: AI Presentation (PPT generator + editor, design templates T00–T141, includes 019/024/076/151/165/265…),
marketplace on Download Monitor (dlm_download), credits and plans (Razorpay), AI image / video / audio,
creator portal, referral system, blog, PowerPoint add-in bridge (148), Word add-in (374), AI Document (371–373).

## Rules
- Never change how a feature behaves unless that is the fix. Small, targeted changes only.
- One commit per fix, on a branch, with a plain-language message saying what was wrong and what changed.
- Add a changelog line to the top of `plugin/designstager-v42.php` (CHANGELOG block) and `plugin/readme.txt`,
  and bump the version (42.0.x) once per batch of fixes.
- Every changed PHP file must pass `php -l`. JavaScript inside PHP heredocs/strings must stay valid.
- The live site and staging site are NOT reachable from cloud sessions. Do not try to deploy.
- Never weaken security checks; never print secrets; never add external services.

## What to look for when reviewing
- PHP fatals / warnings on PHP 8.3 (undefined functions, variables, array offsets, wrong types e.g. WP_Error used as array).
- REST routes (`register_rest_route`): data-changing routes must have a real `permission_callback`
  (logged in, capability or ownership check), not `__return_true`, unless they are deliberately public forms.
- AJAX handlers (`wp_ajax_*`, `wp_ajax_nopriv_*`): nonce + capability checks.
- SQL: values must go through `$wpdb->prepare`.
- Output: escape with `esc_html`, `esc_attr`, `esc_url`, `wp_json_encode` etc.
- File uploads: type and size checks, no executable uploads.
- Credits / payments: no way to get credits or paid downloads without paying (race conditions, client-trusted prices).

## Already checked (2026-10-23)
Every shortcode page (guest + admin), all 41 DesignStager admin screens, all 186 REST routes (GET and empty POST)
were loaded on a clean WordPress 6.5 + PHP 8.3 test site without Download Monitor / WooCommerce. Four bugs were
found and fixed in 42.0.1 (includes 058, 093, 108, 124). Download, payment and real AI flows were not exercised.

## Build
`./build.sh 42` → `dist/designstager-v42.zip` (folder inside: designstager-v42/).
