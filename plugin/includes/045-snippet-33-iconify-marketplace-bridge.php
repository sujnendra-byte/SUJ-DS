<?php
/**
 * Code Snippets export — Snippet 33 iconify marketplace bridge
 *
 * Snippet ID : 731
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-045
 * Modified   : 2026-09-11 14:20:11
 * Load order : 045 of 144
 */
/**
 * Snippet 33 — DS PPT Iconify Bridge for Marketplace-Picked Slides
 * Priority: 1006
 *
 * REQUIRES:
 *  - Snippet 21 (Smart Template Matcher) with ONE small addition inside
 *    ds_tpl_inject_into_pptx(): right after $zip->close();, add:
 *
 *        if ( $changed_any && has_filter('ds_tpl_filled_pptx_bytes') ) {
 *            $bytes = file_get_contents($dest_path);
 *            if ( $bytes !== false ) {
 *                $filtered = apply_filters('ds_tpl_filled_pptx_bytes', $bytes, $map);
 *                if ( is_string($filtered) && strlen($filtered) > 100 ) {
 *                    file_put_contents($dest_path, $filtered);
 *                }
 *            }
 *        }
 *
 *    That's the only edit needed to Snippet 21. This new snippet does
 *    everything else and does not modify Snippet 21, 30, or 32.
 *
 *  - Snippet 32 (Iconify Icon Injector) must be active, since this
 *    reuses its functions: ds32_inject_icons(), ds32_pick_icon_color(),
 *    ds32_fetch_svg(), ds32_get_icon_keywords().
 *
 * WHAT THIS DOES:
 * Snippet 32 only ever fires inside Snippet 30's AI-generation pipeline
 * (via the ds30_filled_pptx_bytes filter). Marketplace-picked templates
 * (Snippet 21's "Pick a Design" flow) never pass through that pipeline,
 * so those slides never get icons. This snippet bridges the gap: it
 * listens on a new ds_tpl_filled_pptx_bytes filter, converts Snippet 21's
 * flat content map (e.g. 'point 1 heading' => 'Strategy') into the
 * {slot_name, replacement} shape ds32_inject_icons() expects, skips the
 * dummy/placeholder points Snippet 21 auto-fills beyond the user's real
 * content, and then calls Snippet 32's existing icon injector unchanged.
 */
if ( ! defined('ABSPATH') ) exit;

add_filter('ds_tpl_filled_pptx_bytes', 'ds33_inject_icons_from_map', 10, 2);

if ( ! function_exists('ds33_inject_icons_from_map') ) {
function ds33_inject_icons_from_map( $pptx_bytes, $map ) {
    if ( ! function_exists('ds32_inject_icons') ) {
        error_log('DS33: Snippet 32 (ds32_inject_icons) not found — skipping icon injection on marketplace slide.');
        return $pptx_bytes;
    }
    if ( empty($pptx_bytes) || strlen($pptx_bytes) < 100 ) return $pptx_bytes;
    if ( empty($map) || ! is_array($map) ) return $pptx_bytes;

	/* DS33-FIX — Snippet 21 now hands this filter a LIST of
	   {slot_name, replacement} entries instead of the flat
	   'slot name' => 'text' map this bridge was written for. Cast the
	   list back to a map; without this every replacement became the
	   literal string "Array", nothing paired, and marketplace-template
	   downloads shipped with no icons. */
	if ( function_exists('ds32_normalise_slots') ) {
		$flat = array();
		foreach ( ds32_normalise_slots( $map ) as $row ) {
			if ( $row['slot_name'] !== '' ) $flat[ $row['slot_name'] ] = $row['replacement'];
		}
		if ( ! empty($flat) ) $map = $flat;
	}

    // Adapt Snippet 21's flat map into the {slot_name, replacement} shape
    // ds32_inject_icons() already expects from Snippet 30's pipeline.
    // Skip Snippet 21's own auto-filled dummy points ("Additional Point N",
    // "You can delete this point...") so icons only get generated for the
    // user's real content, not placeholder filler.
    $ai_json = [];
    foreach ( $map as $slot_name => $replacement ) {
        $replacement = (string) $replacement;
        if ( $replacement === '' ) continue;
        if ( stripos($replacement, 'Additional Point') === 0 ) continue;
        if ( stripos($replacement, 'You can delete this point') === 0 ) continue;
        $ai_json[] = ['slot_name' => (string) $slot_name, 'replacement' => $replacement];
    }

    if ( empty($ai_json) ) return $pptx_bytes;

    $keys  = function_exists('ds_get_api_keys') ? ds_get_api_keys() : [];
    $api_k = $keys['groq']
          ?? $keys['groq_api_key']
          ?? get_option('ds_groq_api_key', '')
          ?? get_option('groq_api_key', '');
    if ( empty($api_k) ) {
		/* DS33-FIX — a missing key is no longer fatal: Snippet 32 derives a
		   keyword locally so the deck still gets real Iconify icons. */
		error_log('DS33: no Groq API key found — falling back to local keyword derivation.');
	}

    error_log('DS33: bridging ' . count($ai_json) . ' slots into ds32_inject_icons for marketplace-picked slide.');

    return ds32_inject_icons( $pptx_bytes, $ai_json, $api_k );
}
}
