<?php
/**
 * Code Snippets export — Snippet 16 variant pptx renderers [CLONE3] [Classic Editor: 9-14 points + Key takeaway + Logo in PPTX] [CLONE4] [Classic: up to 16 points in PPTX]
 *
 * Snippet ID : 957
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-03 07:51:53
 * Load order : 73 of 192
 */
/**
 * Snippet 16: DS PPT Variant PPTX Renderers
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * Adds new REST endpoint /ds/v1/build-pptx-v2 that accepts
 * slide_variants (per-slide layout choices) and generates
 * PPTX with variant-specific layouts.
 *
 * Variants per slide type:
 *   title:        v1=plain | v2=full-bg-image | v3=image-right | v4=image-left | v5=image-top
 *   bullet:       v1=list  | v2=image-right   | v3=image-left
 *   two_column:   v1=plain | v2=image-right   | v3=image-left
 *   three_column: v1=plain | v2=image-right   | v3=image-left
 *   feature_grid: v1=2x2  | v2=image-right   | v3=image-left
 *   stats:        v1=plain | v2=image-right   | v3=image-bg
 *   timeline:     v1=vert  | v2=horizontal
 *   quote:        v1=plain | v2=image-right   | v3=image-left
 *   image_text:   v1=right | v2=left          (image side)
 */

if ( ! defined('ABSPATH') ) exit;

// ─── New REST endpoint ────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/build-pptx-v2', array(
        'methods'             => 'POST',
        'callback'            => 'ds_pptv2_build',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_parse_title_fields') ) {
function ds_parse_title_fields($content) {
    $out = array('company'=>'','presenter'=>'','designation'=>'','date'=>'','website'=>'','tagline'=>'','confidential'=>'');
    if ( ! is_array($content) ) return $out;
    foreach ($content as $item) {
        $item = (string) $item;
        $ci = strpos($item, ':');
        if ($ci === false || $ci === 0) continue;
        $label = strtolower(trim(substr($item, 0, $ci)));
        $val   = trim(substr($item, $ci + 1));
        if (array_key_exists($label, $out)) $out[$label] = $val;
    }
    return $out;
}
}

if ( ! function_exists('ds_title_header_shapes') ) {
function ds_title_header_shapes(&$id, $f, $tc, $bc, $ac, $x, $y, $w, $align) {
    if ( ! empty($GLOBALS['DS_CE_COVER_HIDE']) ) return ''; /* DS Classic Editor: cover logo placed at its own position (ds_ce_cover_free_xml) */
		if ( ! empty($GLOBALS['DS_CE_COVER_LOGO']) && function_exists('ds_ce_header_logo') ) return ds_ce_header_logo($id, $GLOBALS['DS_CE_COVER_LOGO'], $ac, $GLOBALS['DS_CE_BG'] ?? '1B2A4A', $x, $y, $w, $align);   /* DS Classic Editor: customer logo */
    if ($f['company'] === '' && $f['tagline'] === '') return '';
    $shapes   = '';
    $badge_sz = 400000;
    $text_w   = ($align === 'ctr') ? 3000000 : ($x + $w - ($x + $badge_sz + 150000));
    $badge_x  = $x;
    if ($align === 'ctr') {
        $total_w = $badge_sz + 150000 + $text_w;
        $badge_x = $x + (int) round(($w - $total_w) / 2);
    }
    $shapes .= ds_ellipse($id++, 'LogoBadge', $badge_x, $y, $badge_sz, $badge_sz, $ac);
    $initial = strtoupper(substr($f['company'] !== '' ? $f['company'] : '?', 0, 1));
    $init_p  = '<a:p><a:pPr algn="ctr"/>' . ds_run($initial, 2000, 'FFFFFF', true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'LogoInit', $badge_x, $y, $badge_sz, $badge_sz, $init_p, 'ctr', 'ctr');
    $text_x = $badge_x + $badge_sz + 150000;
    if ($f['company'] !== '') {
        $c_p = '<a:p><a:pPr algn="l"/>' . ds_run($f['company'], 1400, $tc, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'CompanyName', $text_x, $y - 20000, $text_w, 260000, $c_p, 'l', 't');
    }
    if ($f['tagline'] !== '') {
        $tg_p = '<a:p><a:pPr algn="l"/>' . ds_run($f['tagline'], 1000, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'CompanyTagline', $text_x, $y + 200000, $text_w, 200000, $tg_p, 'l', 't');
    }
    return $shapes;
}
}

if ( ! function_exists('ds_title_footer_shapes') ) {
function ds_title_footer_shapes(&$id, $f, $tc, $bc, $ac, $x, $y, $w, $align, $website_y = null) {
    $shapes    = '';
    $cursor_y  = $y;
    $has_pres  = ($f['presenter'] !== '' || $f['designation'] !== '');
    $has_date  = ($f['date'] !== '');
    if ($has_pres || $has_date) {
        $col_w   = (int) round($w / 2) - 100000;
        $icon_sz = 300000;
        if ($has_pres) {
            $shapes .= ds_ellipse($id++, 'PresIcon', $x, $cursor_y, $icon_sz, $icon_sz, $ac, 20000);
            $lbl_p = '<a:p><a:pPr algn="l"/>' . ds_run('Presented By', 800, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'PresLbl', $x + $icon_sz + 100000, $cursor_y - 30000, $col_w - $icon_sz - 100000, 180000, $lbl_p, 'l', 't');
            if ($f['presenter'] !== '') {
                $n_p = '<a:p><a:pPr algn="l"/>' . ds_run($f['presenter'], 1200, $tc, true) . '</a:p>';
                $shapes .= ds_text_shape($id++, 'PresName', $x + $icon_sz + 100000, $cursor_y + 130000, $col_w - $icon_sz - 100000, 200000, $n_p, 'l', 't');
            }
            if ($f['designation'] !== '') {
                $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($f['designation'], 1000, $bc) . '</a:p>';
                $shapes .= ds_text_shape($id++, 'PresDesig', $x + $icon_sz + 100000, $cursor_y + 320000, $col_w - $icon_sz - 100000, 180000, $d_p, 'l', 't');
            }
        }
        if ($has_date) {
            $date_x = $x + $col_w + 200000;
            $shapes .= ds_ellipse($id++, 'DateIcon', $date_x, $cursor_y, $icon_sz, $icon_sz, $ac, 20000);
            $lbl_p = '<a:p><a:pPr algn="l"/>' . ds_run('Date', 800, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'DateLbl', $date_x + $icon_sz + 100000, $cursor_y - 30000, $col_w - $icon_sz - 100000, 180000, $lbl_p, 'l', 't');
            $v_p = '<a:p><a:pPr algn="l"/>' . ds_run($f['date'], 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'DateVal', $date_x + $icon_sz + 100000, $cursor_y + 130000, $col_w - $icon_sz - 100000, 200000, $v_p, 'l', 't');
        }
        $cursor_y += 560000;
    }
    $palgn = ($align === 'ctr') ? 'ctr' : 'l';
    if ($website_y !== null) { $cursor_y = $website_y; }
    if ($f['website'] !== '') {
        $w_p = '<a:p><a:pPr algn="' . $palgn . '"/>' . ds_run('🌐 ' . $f['website'], 1000, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Website', $x, $cursor_y, $w, 200000, $w_p, $align, 't');
        $cursor_y += 220000;
    }
    if ($f['confidential'] !== '') {
        $c_p = '<a:p><a:pPr algn="' . $palgn . '"/>' . ds_run($f['confidential'], 800, $bc, false, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Confidential', $x, $cursor_y, $w, 200000, $c_p, $align, 't');
    }
    return $shapes;
}
}
if ( ! function_exists('ds_title_font_size') ) {
function ds_title_font_size($title, $max_size) {
    $len = strlen((string) $title);
    if ($len <= 14) return $max_size;
    if ($len <= 24) return (int) round($max_size * 0.87);
    if ($len <= 34) return (int) round($max_size * 0.74);
    return (int) round($max_size * 0.61);
}
}

// ─── Strip markdown from slide text before PPTX rendering ────────────────────
if ( ! function_exists('ds_strip_md') ) {
function ds_strip_md($text) {
    $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text); // **bold**
    $text = preg_replace('/\*([^*]+)\*/',     '$1', $text); // *italic*
    $text = preg_replace('/^#+\s*/m',         '',   $text); // ## Header
    $text = preg_replace('/`([^`]+)`/',       '$1', $text); // `code`
    $text = preg_replace('/__([^_]+)__/',     '$1', $text); // __bold__
    return trim($text);
}
}

// Apply markdown stripping to a full slide array
if ( ! function_exists('ds_clean_slide') ) {
function ds_clean_slide($slide) {
    if ( isset($slide['title'])    ) $slide['title']    = ds_strip_md($slide['title']);
    if ( isset($slide['subtitle']) ) $slide['subtitle'] = ds_strip_md($slide['subtitle']);
    if ( isset($slide['content'])  && is_array($slide['content']) ) {
        $slide['content'] = array_map('ds_strip_md', $slide['content']);
    }
    return $slide;
}
}
if ( ! function_exists('ds_pptv2_build') ) {
function ds_pptv2_build( WP_REST_Request $req ) {
    if ( ! function_exists('ds_pptv2_full_build') ) {
        return new WP_REST_Response(array('success'=>false,'error'=>'Variant builder not loaded'), 500);
    }

    $slides   = $req->get_param('slides')         ?: array();
    $variants = $req->get_param('slide_variants') ?: array();
    $template = $req->get_param('template')       ?: 'navy-pro';
    $layout_p = $req->get_param('layout')         ?: '16:9';
    $title    = sanitize_text_field($req->get_param('title') ?: 'Presentation');

    // Strip markdown + inject variant choices
    if ( is_array($slides) ) {
        foreach ( $slides as $i => $_ ) {
            $vk = 'slide_' . $i;
            if ( ! empty($variants[$vk]) ) {
                $slides[$i]['_variant'] = sanitize_key($variants[$vk]);
            }
            if ( function_exists('ds_clean_slide') ) {
                $slides[$i] = ds_clean_slide($slides[$i]);
            }
        }
    }

    // Use the VARIANT-AWARE builder, not the old 12b one
    $new_req = new WP_REST_Request('POST', '/ds/v1/build-pptx-v2');
    $new_req->set_param('slides',   $slides);
    $new_req->set_param('template', $template);
    $new_req->set_param('layout',   $layout_p);
    $new_req->set_param('title',    $title);
    $new_req->set_param('font_family', sanitize_text_field($req->get_param('font_family') ?: ''));
    $new_req->set_param('logo', $req->get_param('logo'));   /* DS Classic Editor */
    $new_req->set_param('style', $req->get_param('style'));   /* DS Deck Style */
    return ds_pptv2_full_build($new_req);
}
}
// ─── Self-contained build function (modified copy of ds_ppt_v3_build_pptx) ──
// Changes from original 12b:
//   1. Images extracted for ALL slide types with image_data (not just image_text)
//   2. Variant-aware renderer: calls ds_pptv2_TYPE_VARIANT() when _variant is set
// This function exists independently — no load-order dependency on 12a or 12b.
if ( ! function_exists('ds_pptv2_full_build') ) {
/* Iconify icons were re-fetched over HTTP for every single slide, so a long
   deck fired 50+ identical network calls. Once the remote started failing or
   timing out, later slides got no icon and fell back to an emoji glyph. The
   same handful of icons now resolves once and is reused for the whole deck. */
/* DS-FIX 2026-09-16 — find a real Iconify icon from a point title when the
   AI-supplied icon name is missing or does not exist, so every point gets an icon. */
if ( ! function_exists('ds_pptv2_icon_search') ) {
function ds_pptv2_icon_search( $text ) {
    $text = strtolower( wp_strip_all_tags( (string) $text ) );
    $text = trim( preg_replace('/[^a-z0-9 ]+/', ' ', $text) );
    if ( $text === '' ) return '';
    $ck = 'ds_icsrch_' . md5($text);
    $hit = get_transient($ck);
    if ( $hit !== false ) return $hit === 'none' ? '' : $hit;
    $stop = array('the','and','for','with','your','our','from','into','that','this','are','was','per','all','new','key','of','to','in','on','a','an','by','at','is','it','as','or','vs');
    $words = array_values(array_filter(explode(' ', $text), function($w) use ($stop){ return strlen($w) > 2 && ! in_array($w, $stop, true); }));
    $tries = array();
    if ( $words ) $tries[] = implode(' ', array_slice($words, 0, 2));
    foreach ( $words as $w ) $tries[] = $w;
    $found = '';
    foreach ( array_slice(array_unique($tries), 0, 4) as $q ) {
        $resp = wp_remote_get('https://api.iconify.design/search?query=' . rawurlencode($q) . '&limit=5&prefixes=mdi', array('timeout' => 6, 'sslverify' => false));
        if ( is_wp_error($resp) ) break;
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        if ( ! empty($data['icons'][0]) ) { $found = (string) $data['icons'][0]; break; }
    }
    set_transient($ck, $found !== '' ? $found : 'none', WEEK_IN_SECONDS);
    return $found;
}
}
if ( ! function_exists('ds_ppt_fetch_iconify_cached') ) {
function ds_ppt_fetch_iconify_cached( $icon_name, $color = 'FFFFFF', $size = 128 ) {
    static $mem = array();
    $key = md5( $icon_name . '|' . $color . '|' . $size );
    if ( isset($mem[$key]) ) { return $mem[$key]; }
    $cached = get_transient( 'ds_ico2_' . $key );
    if ( $cached !== false ) { $mem[$key] = $cached; return $cached; }
    $res = function_exists('ds_ppt_fetch_iconify')
        ? ds_ppt_fetch_iconify( $icon_name, $color, $size )
        : null;
    if ( $res ) {
        set_transient( 'ds_ico2_' . $key, $res, WEEK_IN_SECONDS );
        $mem[$key] = $res;
    }
    return $res;
}
}

function ds_pptv2_full_build( WP_REST_Request $req ) {
    @set_time_limit(0);
    @ignore_user_abort(true);
        // Raise, never lower: the combined-deck endpoint merges real marketplace
    // .pptx files AFTER this runs, and the 512M cap made that fatal with
    // "Allowed memory size exhausted", which REST returned as a bare 500.
    $__ds_mem_cur = wp_convert_hr_to_bytes( (string) ini_get('memory_limit') );
    if ( $__ds_mem_cur > 0 && $__ds_mem_cur < 1536 * MB_IN_BYTES ) {
        @ini_set('memory_limit', '1536M');
    }

    $title    = sanitize_text_field( $req->get_param('title') ?: 'Presentation' );
    $template = sanitize_text_field( $req->get_param('template') ?: 'navy-pro' );
    $GLOBALS['DS_PPT_FONT_FAMILY'] = sanitize_text_field( $req->get_param('font_family') ?: '' );
    $layout   = sanitize_text_field( $req->get_param('layout') ?: '16:9' );
    $slides   = $req->get_param('slides');

    // Slide dims (EMU). 16:9=standard widescreen. 4:5 and 9:16 use correct portrait EMUs.
    $layouts = array(
        '16:9' => array('cx' => 12192000, 'cy' => 6858000,  'type' => 'screen16x9'),
        '4:5'  => array('cx' => 6858000,  'cy' => 8572500,  'type' => 'custom'),
        '9:16' => array('cx' => 6858000,  'cy' => 12192000, 'type' => 'custom'),
    );
    $L = isset($layouts[$layout]) ? $layouts[$layout] : $layouts['16:9'];
    $sld_cx = $L['cx']; $sld_cy = $L['cy']; $sld_type = $L['type'];

    if ( ! is_array($slides) || empty($slides) ) {
        return rest_ensure_response(array('success'=>false,'error'=>'No slides data.'));
    }
    if ( ! class_exists('ZipArchive') ) {
        return rest_ensure_response(array('success'=>false,'error'=>'ZipArchive not available.'));
    }
  
    // theme: bg/title/body/accent
    $themes = array(
    'navy-pro'        => array('1B2A4A','FFFFFF','C8D8F0','4A9EFF'),
    'dark-modern'     => array('0D1117','FFFFFF','C9D1D9','58A6FF'),
    'clean-corporate' => array('FFFFFF','1A365D','2D3748','2B6CB0'),
    'bold-red'        => array('1A0000','FFFFFF','FFD0D0','FF4444'),
    'corporate-dark'  => array('111827','F9FAFB','D1D5DB','6366F1'),
    'clean-white'     => array('F8FAFC','0F172A','334155','0EA5E9'),
    'gradient-pro'    => array('0F0C29','FFFFFF','E0D7FF','A78BFA'),
    'bold-impact'     => array('111111','FFFFFF','CCCCCC','FFD700'),
    // Gradient themes — index [4] = gradient end color (used by bg2)
'aurora'          => array('0F2027','FFFFFF','B8D4DE','4FD1C5', '1A3A4A'),
'sunset-blaze'    => array('7B0000','FFFFFF','FFE0E9','FFD700',  'DD2476'),
'cosmic-purple'   => array('1A0050','FFFFFF','E0D4FF','00E5FF',  '4A00E0'),
'ocean-depth'     => array('000028','FFFFFF','CDEFFF','FFD93D',  '003366'),
'emerald-dusk'    => array('0A2E1A','FFFFFF','DFF5E8','FFC857',  '134E5E'),
'rose-gold-glow'  => array('0D0720','FFFFFF','FFD9E6','FFD700',  '1D2671'),
'amber-blaze'     => array('1A0A00','FFFFFF','FFD9A0','FFA500',  '7A3800'),
'midnight-bloom'  => array('0B0020','FFFFFF','D9F7FA','F72585',  '0B3D47'),
'deep-navy'       => array('0A1628','FFFFFF','C8D8F0','4A9EFF',  '0D3B6E'),
'dark-crimson'    => array('1A0000','FFFFFF','FFD0D0','FF6666',  '5C0000'),
'dark-slate'      => array('0F172A','F1F5F9','94A3B8','38BDF8',  '1E293B'),
'dark-forest'     => array('052010','FFFFFF','C8E6D0','4ADE80',  '0A3020'),
    // Non-gradient themes
    'sunset-glow'     => array('2D1B2E','FFFFFF','F4C9D9','FF6B9D'),
    'forest-green'    => array('0F2419','FFFFFF','C8E6D0','4ADE80'),
    'ocean-breeze'    => array('F0F9FF','0C4A6E','334155','0284C7'),
    'golden-hour'     => array('1C1408','FFFFFF','F5DEB3','F59E0B'),
    'lavender-mist'   => array('F5F3FF','3B0764','4C1D95','8B5CF6'),
    'slate-minimal'   => array('F8F9FA','1E293B','475569','64748B'),
    'cherry-bold'     => array('FFF5F5','7F1D1D','450A0A','DC2626'),
    'midnight-teal'   => array('042F2E','FFFFFF','99F6E4','2DD4BF'),
    'coral-sunset'    => array('FFF7ED','7C2D12','9A3412','FB923C'),
    'royal-purple'    => array('1E0A3C','FFFFFF','DDD6FE','A855F7'),
    'mint-fresh'      => array('F0FDF4','14532D','166534','22C55E'),
    'rose-gold'       => array('FFF1F2','881337','9F1239','FB7185'),
    'steel-blue'      => array('0F172A','F1F5F9','94A3B8','38BDF8'),
    'amber-warm'      => array('1C1917','FEF3C7','D6D3D1','FBBF24'),
    'arctic-white'    => array('FFFFFF','0F172A','334155','06B6D4'),
    'crimson-night'   => array('1A0A0A','FFFFFF','FCA5A5','EF4444'),
);
    $t = isset($themes[$template]) ? $themes[$template] : $themes['navy-pro'];
    list($bg, $tc, $bc, $ac) = $t;
    $bg2 = isset($t[4]) ? $t[4] : null;
    /* DS Deck Style: the deck-wide background / accent chosen in the editor */
    $__ds_style = function_exists('ds_style_clean') ? ds_style_clean($req->get_param('style')) : null;
    if ( $__ds_style ) {
        if ( $__ds_style['bg'] !== '' ) { $bg = $__ds_style['bg']; $bg2 = null; }
        if ( $__ds_style['ac'] !== '' ) $ac = $__ds_style['ac'];
    }

    $upload  = wp_upload_dir();
    $dl_dir  = $upload['basedir'] . '/ds-ppt-downloads';
    wp_mkdir_p($dl_dir);
    $safe    = sanitize_file_name($title);
    $fname   = 'ds-ppt-' . $safe . '-' . time() . '.pptx';
    $fpath   = $dl_dir . '/' . $fname;
    $furl    = $upload['baseurl'] . '/ds-ppt-downloads/' . $fname;

    $zip = new ZipArchive();
    if ( $zip->open($fpath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true ) {
        return rest_ensure_response(array('success'=>false,'error'=>'Cannot create file on server.'));
    }

    $n = count($slides);

    // [Content_Types].xml
    $ct_slides = '';
    for ($i = 1; $i <= $n; $i++) {
        $ct_slides .= '<Override PartName="/ppt/slides/slide'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
    }
    $ovr = function($p,$c){return '<Override PartName="/ppt/'.$p.'" ContentType="application/vnd.openxmlformats-officedocument.'.$c.'+xml"/>';};
    $zip->addFromString('[Content_Types].xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
        '<Default Extension="xml" ContentType="application/xml"/>'.
        '<Default Extension="png" ContentType="image/png"/>'.
		/* DS-FIX — icons are now shipped as SVG as well as PNG. */
		'<Default Extension="svg" ContentType="image/svg+xml"/>'.
        '<Default Extension="jpeg" ContentType="image/jpeg"/>'.
        '<Default Extension="jpg" ContentType="image/jpeg"/>'.
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'.
        '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'.
        $ovr('presentation.xml','presentationml.presentation.main').
        $ovr('presProps.xml','presentationml.presProps').
        $ovr('viewProps.xml','presentationml.viewProps').
        $ovr('tableStyles.xml','presentationml.tableStyles').
        '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>'.
        $ovr('slideMasters/slideMaster1.xml','presentationml.slideMaster').
        $ovr('slideLayouts/slideLayout1.xml','presentationml.slideLayout').
        $ct_slides.
        '</Types>');

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'.
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'.
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'.
        '</Relationships>');

    $zip->addFromString('docProps/core.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'.
        ' xmlns:dc="http://purl.org/dc/elements/1.1/"'.
        ' xmlns:dcterms="http://purl.org/dc/terms/"'.
        ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"'.
        ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'.
        '<dc:title>'.htmlspecialchars($title, ENT_XML1).'</dc:title>'.
        '<dc:creator>DS PPT Generator</dc:creator>'.
        '<cp:lastModifiedBy>DS PPT Generator</cp:lastModifiedBy>'.
        '<dcterms:created xsi:type="dcterms:W3CDTF">'.gmdate('Y-m-d\TH:i:s\Z').'</dcterms:created>'.
        '<dcterms:modified xsi:type="dcterms:W3CDTF">'.gmdate('Y-m-d\TH:i:s\Z').'</dcterms:modified>'.
        '</cp:coreProperties>');

    $zip->addFromString('docProps/app.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"'.
        ' xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'.
        '<Application>Microsoft Office PowerPoint</Application>'.
        '<PresentationFormat>On-screen Show (16:9)</PresentationFormat>'.
        '<Slides>'.$n.'</Slides>'.
        '<Company>DS</Company>'.
        '</Properties>');

    $sld_rels = '';
    for ($i = 1; $i <= $n; $i++) {
        $sld_rels .= '<Relationship Id="rId'.($i+5).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide'.$i.'.xml"/>';
    }
    $zip->addFromString('ppt/_rels/presentation.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>'.
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/presProps" Target="presProps.xml"/>'.
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/viewProps" Target="viewProps.xml"/>'.
        '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>'.
        '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/tableStyles" Target="tableStyles.xml"/>'.
        $sld_rels.
        '</Relationships>');

    $sld_id_lst = '';
    for ($i = 1; $i <= $n; $i++) {
        $sld_id_lst .= '<p:sldId id="'.($i+255).'" r:id="rId'.($i+5).'"/>';
    }
    $zip->addFromString('ppt/presentation.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'.
        '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'.
        '<p:sldIdLst>'.$sld_id_lst.'</p:sldIdLst>'.
        '<p:sldSz cx="'.$sld_cx.'" cy="'.$sld_cy.'" type="'.$sld_type.'"/>'.
        '<p:notesSz cx="6858000" cy="9144000"/>'.
        '<p:defaultTextStyle>'.
        '<a:defPPr><a:defRPr lang="en-US"/></a:defPPr>'.
        '<a:lvl1pPr marL="0" algn="l" defTabSz="457200" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1">'.
        '<a:defRPr sz="1800" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill>'.
        '<a:latin typeface="+mn-lt"/></a:defRPr></a:lvl1pPr>'.
        '</p:defaultTextStyle>'.
        '</p:presentation>');

    $zip->addFromString('ppt/presProps.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:presentationPr xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"/>');

    $zip->addFromString('ppt/viewProps.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:viewPr xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'.
        '<p:normalViewPr><p:restoredLeft sz="15620"/><p:restoredTop sz="94660"/></p:normalViewPr>'.
        '<p:slideViewPr><p:cSldViewPr><p:cViewPr varScale="1">'.
        '<p:scale><a:sx n="100" d="100"/><a:sy n="100" d="100"/></p:scale>'.
        '<p:origin x="0" y="0"/></p:cViewPr></p:cSldViewPr></p:slideViewPr>'.
        '</p:viewPr>');

    $zip->addFromString('ppt/tableStyles.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<a:tblStyleLst xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' def="{5C22544A-7EE6-4342-B048-85BDC9FD1C3A}"/>');

    // Theme — Calibri = major + minor (system-installed everywhere, like Gamma uses Mona Sans)
    $sf = '<a:solidFill><a:schemeClr val="phClr"/></a:solidFill>';
    $clr = function($k,$v){return '<a:'.$k.'><a:srgbClr val="'.$v.'"/></a:'.$k.'>';};
    $zip->addFromString('ppt/theme/theme1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="DS">'.
        '<a:themeElements><a:clrScheme name="DS">'.
        $clr('dk1',$bg).$clr('lt1',$tc).$clr('dk2',$bc).$clr('lt2',$ac).
        $clr('accent1',$ac).$clr('accent2',$ac).$clr('accent3',$ac).
        $clr('accent4',$ac).$clr('accent5',$ac).$clr('accent6',$ac).
        $clr('hlink',$ac).$clr('folHlink',$ac).
        '</a:clrScheme>'.
        '<a:fontScheme name="DS">'.
        '<a:majorFont><a:latin typeface="Arial Black" pitchFamily="34" charset="0"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont>'.
        '<a:minorFont><a:latin typeface="Arial" pitchFamily="34" charset="0"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont>'.
        '</a:fontScheme>'.
        '<a:fmtScheme name="DS">'.
        '<a:fillStyleLst>'.$sf.$sf.$sf.'</a:fillStyleLst>'.
        '<a:lnStyleLst>'.
        '<a:ln w="6350">'.$sf.'</a:ln><a:ln w="12700">'.$sf.'</a:ln><a:ln w="19050">'.$sf.'</a:ln>'.
        '</a:lnStyleLst>'.
        '<a:effectStyleLst><a:effectStyle><a:effectLst/></a:effectStyle><a:effectStyle><a:effectLst/></a:effectStyle><a:effectStyle><a:effectLst/></a:effectStyle></a:effectStyleLst>'.
        '<a:bgFillStyleLst>'.$sf.$sf.$sf.'</a:bgFillStyleLst>'.
        '</a:fmtScheme></a:themeElements></a:theme>');

    $zip->addFromString('ppt/slideMasters/slideMaster1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:sldMaster xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'.
        '<p:cSld>'.($bg2
            ? '<p:bg><p:bgPr><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:srgbClr val="'.$bg.'"/></a:gs><a:gs pos="100000"><a:srgbClr val="'.$bg2.'"/></a:gs></a:gsLst><a:lin ang="2700000" scaled="0"/></a:gradFill><a:effectLst/></p:bgPr></p:bg>'
            : '<p:bg><p:bgPr><a:solidFill><a:srgbClr val="'.$bg.'"/></a:solidFill><a:effectLst/></p:bgPr></p:bg>'
        ).
        '<p:spTree>'.
        '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
        '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'.
        '</p:spTree></p:cSld>'.
        '<p:clrMap bg1="dk1" tx1="lt1" bg2="dk2" tx2="lt2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'.
        '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst>'.
        '<p:txStyles>'.
        '<p:titleStyle><a:lvl1pPr><a:defRPr sz="4800" b="1"><a:solidFill><a:srgbClr val="'.$tc.'"/></a:solidFill><a:latin typeface="Arial Black" pitchFamily="34" charset="0"/></a:defRPr></a:lvl1pPr></p:titleStyle>'.
        '<p:bodyStyle><a:lvl1pPr><a:defRPr sz="1900"><a:solidFill><a:srgbClr val="'.$bc.'"/></a:solidFill><a:latin typeface="Arial" pitchFamily="34" charset="0"/></a:defRPr></a:lvl1pPr></p:bodyStyle>'.
        '<p:otherStyle/>'.
        '</p:txStyles>'.
        '</p:sldMaster>');

    $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'.
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="../theme/theme1.xml"/>'.
        '</Relationships>');

    $zip->addFromString('ppt/slideLayouts/slideLayout1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<p:sldLayout xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'.
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" type="blank" preserve="1">'.
        '<p:cSld name="Blank">'.
        '<p:spTree>'.
        '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
        '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'.
        '</p:spTree></p:cSld>'.
        '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'.
        '</p:sldLayout>');

    $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'.
        '</Relationships>');

	// Marketplace-picked images: deduct from the user's free/pro quota and
    // swap in the real, unwatermarked original ONLY now, at actual file
    // download time -- picking/editing a slide never deducts anything.
    $mkt_unlocked = 0;
    $mkt_locked   = 0;
    foreach ( $slides as $idx => $slide ) {
        $mkt_id   = isset($slide['image_market_id'])   ? (int) $slide['image_market_id']   : 0;
        $mkt_tier = isset($slide['image_market_tier']) ? sanitize_text_field($slide['image_market_tier']) : '';
        if ( ! $mkt_id || ! in_array($mkt_tier, array('free', 'pro'), true) ) continue;
        // New flow: real file already in image_data and quota already
        // deducted at pick time — nothing to do at build time.
        if ( ! empty($slide['image_mkt_fetched']) ) { $mkt_unlocked++; continue; }
		if ( ! function_exists('ds_dl_check_core') || ! function_exists('ds_mkt_get_unwatermarked_file_bytes') ) continue;

        $check = ds_dl_check_core( get_current_user_id(), $mkt_tier );
        if ( empty($check['ok']) ) { $mkt_locked++; continue; }

        $real = ds_mkt_get_unwatermarked_file_bytes( $mkt_id );
        if ( $real && ! empty($real['bytes']) ) {
            $slides[$idx]['image_data'] = 'data:' . $real['mime'] . ';base64,' . base64_encode($real['bytes']);
            $mkt_unlocked++;
        } else {
            $mkt_locked++;
        }
    }
    // Pre-fetched AI images (data URIs from frontend)
    $slide_images = array();
    foreach ($slides as $idx => $slide) {
        $stype = $slide['type'] ?? 'bullet';
        if (empty($slide['image_data'])) continue; // v2: extract images for ALL types with image_data
        $data_uri = trim((string)($slide['image_data'] ?? ''));
        if ( strpos($data_uri, 'data:image/') !== 0 ) continue;
        $parts = explode(',', $data_uri, 2);
        if ( count($parts) !== 2 ) continue;
        $bytes = base64_decode($parts[1]);
        if ( empty($bytes) || strlen($bytes) < 1000 ) continue;
        $ext = null;
        $b0 = ord($bytes[0]); $b1 = ord($bytes[1]);
        $b2 = ord($bytes[2]); $b3 = ord($bytes[3]);
        if ($b0 === 0x89 && $b1 === 0x50 && $b2 === 0x4E && $b3 === 0x47) {
            $ext = 'png';
        } elseif ($b0 === 0xFF && $b1 === 0xD8 && $b2 === 0xFF) {
            $ext = 'jpg';
        } elseif ( $b0 === 0x52 && $b1 === 0x49 && $b2 === 0x46 && $b3 === 0x46 ) {
            // RIFF container — check for WEBP at bytes 8-11
            if ( strlen($bytes) > 12 && substr($bytes, 8, 4) === 'WEBP' ) {
                // Convert WebP -> PNG using GD if available
                if ( function_exists('imagecreatefromwebp') && function_exists('imagepng') ) {
                    $tmp_img = @imagecreatefromwebp('data://image/webp;base64,' . base64_encode($bytes));
                    if ( $tmp_img !== false ) {
                        ob_start();
                        imagepng($tmp_img);
                        $png_data = ob_get_clean();
                        imagedestroy($tmp_img);
                        if ( ! empty($png_data) ) {
                            $bytes = $png_data;
                            $ext = 'png';
                        }
                    }
                }
            }
        }
        if ($ext === null ) continue;
        // Detect ACTUAL image dimensions WITHOUT requiring GD extension
        // Parse PNG/JPEG binary headers directly
        $img_ar = 16.0 / 9.0; // safe fallback
        if ( $ext === 'png' && strlen($bytes) >= 24 ) {
            // PNG: width at bytes 16-19, height at 20-23 (big-endian uint32)
            $w = (ord($bytes[16])<<24)|(ord($bytes[17])<<16)|(ord($bytes[18])<<8)|ord($bytes[19]);
            $h = (ord($bytes[20])<<24)|(ord($bytes[21])<<16)|(ord($bytes[22])<<8)|ord($bytes[23]);
            if ( $w > 0 && $h > 0 ) $img_ar = $w / $h;
        } elseif ( $ext === 'jpg' && strlen($bytes) > 100 ) {
            // JPEG: scan for SOF marker (0xFFC0, 0xFFC1, 0xFFC2) to get dimensions
            $pos = 2; $len = strlen($bytes);
            while ( $pos + 9 < $len ) {
                if ( ord($bytes[$pos]) !== 0xFF ) break;
                $marker = ord($bytes[$pos+1]);
                if ( $marker === 0xC0 || $marker === 0xC1 || $marker === 0xC2 ) {
                    $jh = (ord($bytes[$pos+5])<<8)|ord($bytes[$pos+6]);
                    $jw = (ord($bytes[$pos+7])<<8)|ord($bytes[$pos+8]);
                    if ( $jw > 0 && $jh > 0 ) $img_ar = $jw / $jh;
                    break;
                }
                $seg_len = (ord($bytes[$pos+2])<<8)|ord($bytes[$pos+3]);
                $pos += 2 + $seg_len;
            }
        }
        $slide_images[$idx] = array('bytes' => $bytes, 'ext' => $ext, 'ar' => $img_ar);
    }

    // Pre-fetch Iconify icons for slides that use them
    $slide_icon_rids = array(); // idx => array of rIds for icons
    $slide_icon_rels = array(); // idx => relationship XML strings
    $icon_types_needing = array('feature_grid','two_column','three_column','bullet','image_text','five_point','six_point','seven_point','eight_point','timeline');
    foreach ($slides as $idx => $slide) {
        $stype = $slide['type'] ?? 'bullet';
        if ( ! in_array($stype, $icon_types_needing, true) ) continue;
        $icons_list = isset($slide['icons']) && is_array($slide['icons']) ? $slide['icons'] : array();
        /* DS-FIX 2026-09-16 — one icon slot per point; accept {name:..} objects. */
        $__pts = array_values( (array) ($slide['content'] ?? array()) );
        $__norm = array();
        foreach ( $icons_list as $__ic ) {
            if ( is_array($__ic) ) $__ic = $__ic['name'] ?? ($__ic['icon'] ?? '');
            $__norm[] = is_scalar($__ic) ? (string) $__ic : '';
        }
        $icons_list = $__norm;
        for ( $__k = count($icons_list); $__k < count($__pts); $__k++ ) $icons_list[$__k] = '';
        // For bullet/image_text use default icons if none provided
        if ( empty($icons_list) ) {
            $defaults = array('mdi:lightbulb','mdi:chart-bar','mdi:target','mdi:check-circle','mdi:rocket-launch','mdi:star','mdi:cog','mdi:trophy-outline');
$cnt = count($slide['content'] ?? array());
$icons_list = array_slice($defaults, 0, max($cnt, 4));
        }
        $rids = array();
        $rels = array();
        $icon_rid_start = 20; // start from rId20 to avoid conflicts
        foreach ($icons_list as $ii => $icon_name) {
            $__ic_col = function_exists('ds_style_icon_color') ? ds_style_icon_color(isset($slides[$idx]) ? $slides[$idx] : null, $ii) : 'FFFFFF';   /* DS Deck Style: this icon's own colour */
            $icon_name = sanitize_text_field((string)$icon_name);
            /* DS-FIX 2026-09-16 — never drop a point's icon: bad/missing name -> search by point title -> default set. */
            $result = ( $icon_name !== '' && strpos($icon_name, ':') !== false ) ? ds_ppt_fetch_iconify_cached( $icon_name, $__ic_col, 128 ) : null;
            if ( ! $result ) {
                $__pt = isset($__pts[$ii]) && is_scalar($__pts[$ii]) ? (string) $__pts[$ii] : '';
                $__cp = strpos($__pt, ':');
                $__kw = $__cp !== false ? substr($__pt, 0, $__cp) : $__pt;
                if ( trim($__kw) === '' ) $__kw = str_replace(array(':', '-'), ' ', $icon_name);
                $__alt = function_exists('ds_pptv2_icon_search') ? ds_pptv2_icon_search($__kw) : '';
                if ( $__alt ) $result = ds_ppt_fetch_iconify_cached( $__alt, $__ic_col, 128 );
            }
            if ( ! $result ) {
                $__defs = array('mdi:lightbulb','mdi:chart-bar','mdi:target','mdi:check-circle','mdi:rocket-launch','mdi:star','mdi:shield-check','mdi:account-group');
                $result = ds_ppt_fetch_iconify_cached( $__defs[ $ii % count($__defs) ], $__ic_col, 128 );
            }
            if ( ! $result ) continue;
            $png_bytes = null;
		$svg_bytes = null;
		if ( is_array($result) && isset($result['svg']) ) {
			$svg_bytes = (string) $result['svg'];
			/* DS-FIX 2026-09-12 - reuse the raster the fetcher already made. */
			$png_bytes = ! empty($result['png']) ? $result['png'] : ds_ppt_svg_to_png($svg_bytes, 128);
		} elseif ( is_string($result) ) {
			$png_bytes = $result;
		}
		if ( empty($png_bytes) || strlen($png_bytes) < 100 ) continue;
		$rid_num = $icon_rid_start + $ii;
		$rid = 'rId'.$rid_num;
		$img_file = 'icon_'.$idx.'_'.$ii.'.png';
		$zip->addFromString('ppt/media/'.$img_file, $png_bytes);
		$rids[$ii] = $rid;
		$rels[] = '<Relationship Id="'.$rid.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$img_file.'"/>';

		/* DS-FIX — ship the real SVG beside the PNG. Only the raster used to go
		   into the deck, so the icon colour was baked in and could not be
		   changed in PowerPoint. The PNG stays as the OOXML-required fallback
		   for PowerPoint < 2016; 2016+ reads the asvg:svgBlip below and renders
		   the vector, which is what makes the icon recolourable. */
		if ( $svg_bytes && strpos($svg_bytes, '<svg') !== false ) {
			if ( strpos($svg_bytes, '<?xml') === false ) {
				$svg_bytes = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n" . $svg_bytes;
			}
			if ( strpos($svg_bytes, 'xmlns=') === false ) {
				$svg_bytes = str_replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg"', $svg_bytes);
			}
			$svg_rid  = 'rId' . ( $icon_rid_start + 500 + $ii );
			$svg_file = 'icon_'.$idx.'_'.$ii.'.svg';
			$zip->addFromString('ppt/media/'.$svg_file, $svg_bytes);
			$rels[] = '<Relationship Id="'.$svg_rid.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$svg_file.'"/>';
			if ( ! isset($GLOBALS['ds_icon_svg_rid']) || ! is_array($GLOBALS['ds_icon_svg_rid']) ) $GLOBALS['ds_icon_svg_rid'] = array();
			$GLOBALS['ds_icon_svg_rid'][$idx][$rid] = $svg_rid;
		}
        }
        if ( ! empty($rids) ) {
            $slide_icon_rids[$idx] = $rids;
            $slide_icon_rels[$idx] = $rels;
        }
    }

    // Build slides
    // Strip markdown from all slide text before rendering
    if ( function_exists('ds_clean_slide') ) {
        $slides = array_map('ds_clean_slide', $slides);
    }

    $__ce_logo = function_exists('ds_ce_logo_prepare') ? ds_ce_logo_prepare($req->get_param('logo'), $zip) : null;   /* DS Classic Editor: deck logo */
    foreach ($slides as $idx => $slide) {
        $sn = $idx + 1;
        $type = $slide['type'] ?? 'bullet';
        if ( function_exists('ds_ce_norm_type') ) $type = ds_ce_norm_type($type, $slide);
        $GLOBALS['DS_CE_COVER_LOGO'] = ( $type === 'title' && ! empty($__ce_logo) && ! empty($__ce_logo['cover']) && ( $__ce_logo['cmode'] ?? 'auto' ) !== 'free' ) ? $__ce_logo : null; $GLOBALS['DS_CE_COVER_HIDE'] = ( $type === 'title' && ! empty($__ce_logo) && ! empty($__ce_logo['cover']) && ( $__ce_logo['cmode'] ?? 'auto' ) === 'free' ); $GLOBALS['DS_CE_BG'] = $bg;   /* DS Classic Editor: 9-14 points */
        // image_text is retired — render any legacy slide of that type as 3-column
        if ( $type === 'image_text' ) { $type = 'three_column'; }

        $image_rid = null;
        $slide_rels_extra = '';
        if ( isset($slide_images[$idx]) ) {
            $img = $slide_images[$idx];
            $img_filename = 'image'.$sn.'.'.$img['ext'];
            $zip->addFromString('ppt/media/'.$img_filename, $img['bytes']);
            $image_rid = 'rId10';
            // Store actual AR in global so ds_pptv2_img_cover uses it (not assumed 16:9)
            $GLOBALS['_pptv2_img_ar'][$image_rid . '_' . $sn] = $img['ar'] ?? 16.0/9.0;
            $GLOBALS['_pptv2_img_rid_key'] = $image_rid . '_' . $sn;
            $GLOBALS['_pptv2_img_pos'] = ( isset($slide['image_position']) && is_array($slide['image_position']) )
                ? $slide['image_position'] : null;
            $slide_rels_extra =
                '<Relationship Id="rId10" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$img_filename.'"/>';
        }

        $icon_rids_for_slide = isset($slide_icon_rids[$idx]) ? $slide_icon_rids[$idx] : array();
		/* DS-FIX — the icon rIds repeat per slide, so the SVG lookup has to know
		   which slide's relationships we are writing into right now. */
		$GLOBALS['ds_icon_cur_slide'] = $idx;
        if ( ! function_exists('ds_render_slide_body') ) {
            $zip->close();
            return rest_ensure_response(array('success'=>false,'error'=>'Renderer snippet (12a) not active.'));
        }
        if ( ! function_exists('ds_render_slide_body') ) {
    $zip->close();
    return rest_ensure_response(array('success'=>false,'error'=>'12a not loaded.'));
}
// v2: variant-aware render — check for _variant field set by ds_pptv2_build
$_variant = isset($slide['_variant']) ? $slide['_variant'] : 'v1';
$_vfn = 'ds_pptv2_' . $type . '_' . $_variant;
// Types that MUST use the v2 renderer even for v1 — 12a (ds_render_slide_body)
// does not know about these types and falls back to bullet layout.
$_v2_only_types = array('five_point','six_point','seven_point','many_point',
                         'timeline','quote','image_text',
                         'two_column','three_column','feature_grid');
if ( $type === 'stats' && function_exists('ds_ce_stats_many') && ds_ce_stats_count($slide) > 0 ) {
    $body_xml = ds_ce_stats_many($slide, $tc, $bc, $ac, $image_rid, $_variant, $bg);   /* DS Classic Editor: Numbers slides, 1-16 numbers, takeaway under the numbers */
    $slide['takeaway'] = '';   /* already drawn inside the layout: no second strip along the bottom */
} elseif ( function_exists($_vfn) ) {
    $body_xml = $_vfn($slide, $tc, $bc, $ac, $image_rid, $icon_rids_for_slide, $bg, $bg2);
} elseif ( in_array($type, $_v2_only_types, true) && function_exists('ds_pptv2_' . $type . '_v1') ) {
    $body_xml = call_user_func('ds_pptv2_' . $type . '_v1', $slide, $tc, $bc, $ac, $image_rid, $icon_rids_for_slide);
} else {
    $body_xml = ds_render_slide_body($type, $slide, $tc, $bc, $ac, $bg, $image_rid, $icon_rids_for_slide);
}
if ( function_exists('ds_ce_post_body') ) $body_xml = ds_ce_post_body($body_xml, $slide, $type, $tc, $ac, $__ce_logo, $bg);   /* DS Classic Editor: takeaway + logo */
if ( function_exists('ds_style_xml') ) $body_xml = ds_style_xml($body_xml, $slide, $type, $__ds_style);   /* DS Deck Style */

        $slide_xml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'.
            ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'.
            ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
            '<p:cSld>'.
            '<p:bg><p:bgPr>'.($bg2
                ? '<a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:srgbClr val="'.$bg.'"/></a:gs><a:gs pos="100000"><a:srgbClr val="'.$bg2.'"/></a:gs></a:gsLst><a:lin ang="2700000" scaled="0"/></a:gradFill>'
                : '<a:solidFill><a:srgbClr val="'.$bg.'"/></a:solidFill>'
            ).'</p:bgPr></p:bg>'.
            '<p:spTree>'.
            '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'.
            '<p:grpSpPr><a:xfrm>'.
            '<a:off x="0" y="0"/>'.
            '<a:ext cx="'.$sld_cx.'" cy="'.$sld_cy.'"/>'.
            '<a:chOff x="0" y="0"/>'.
            '<a:chExt cx="12192000" cy="6858000"/>'.
            '</a:xfrm></p:grpSpPr>'. 
            $body_xml.
            '</p:spTree>'.
            '</p:cSld>'.
            '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'.
            '</p:sld>';

        $zip->addFromString('ppt/slides/slide'.$sn.'.xml', $slide_xml);

        // ── FIX: Only write relationships that are ACTUALLY referenced in the slide XML ──
        // Orphaned relationships (defined in .rels but not used in slide XML) cause
        // PowerPoint's "found a problem" repair dialog.
        $used_rids_in_body = array();
        preg_match_all('/r:embed="(rId\d+)"/', $slide_xml, $body_rid_matches);
        if ( ! empty($body_rid_matches[1]) ) {
            $used_rids_in_body = array_unique($body_rid_matches[1]);
        }

        // Filter slide_rels_extra (main image rId10) 
        $filtered_rels_extra = '';
        if ( ! empty($slide_rels_extra) && in_array('rId10', $used_rids_in_body) ) {
            $filtered_rels_extra = $slide_rels_extra;
        }

        // Filter icon rels (rId20, rId21, rId22, rId23)
        $icon_rels_str = isset($slide_icon_rels[$idx]) ? implode('', $slide_icon_rels[$idx]) : '';
        $filtered_icon_rels = '';
        if ( ! empty($icon_rels_str) ) {
            foreach ( preg_split('/(?=<Relationship )/', $icon_rels_str) as $rel_chunk ) {
                if ( empty(trim($rel_chunk)) ) continue;
                preg_match('/Id="(rId\d+)"/', $rel_chunk, $rid_m);
                if ( isset($rid_m[1]) && in_array($rid_m[1], $used_rids_in_body) ) {
                    $filtered_icon_rels .= $rel_chunk;
                }
            }
        }

        $zip->addFromString('ppt/slides/_rels/slide'.$sn.'.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'.
            $filtered_rels_extra.
            $filtered_icon_rels.
            ( ( $__ce_logo && strpos($slide_xml, 'r:embed="rIdDsLogo"') !== false ) ? '<Relationship Id="rIdDsLogo" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$__ce_logo['file'].'"/>' : '' ).
            ( ( $__ce_logo && ! empty($__ce_logo['file2']) && strpos($slide_xml, 'r:embed="rIdDsLogo2"') !== false ) ? '<Relationship Id="rIdDsLogo2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$__ce_logo['file2'].'"/>' : '' ).
            '</Relationships>');
    }

    $zip->close();
    if ( function_exists('ds_chart_finalize') ) ds_chart_finalize($fpath);   /* DS Charts: add the chart parts */

    // Clean up: remove orphaned icon media files never referenced by any slide rels
    // (PowerPoint's validator flags unreferenced parts and triggers repair)
    $zip2 = new ZipArchive();
    if ( $zip2->open($fpath) === true ) {
        $all_rels_content = '';
        for ($ci = 0; $ci < $zip2->numFiles; $ci++) {
            $nm = $zip2->getNameIndex($ci);
            if ( strpos($nm, '_rels/') !== false && substr($nm, -5) === '.rels' ) {
                $all_rels_content .= $zip2->getFromIndex($ci);
            }
        }
        $orphans = array();
        for ($ci = 0; $ci < $zip2->numFiles; $ci++) {
            $nm = $zip2->getNameIndex($ci);
            if ( strpos($nm, 'ppt/media/icon_') === 0 ) {
                $basename = basename($nm);
                if ( strpos($all_rels_content, $basename) === false ) {
                    $orphans[] = $nm;
                }
            }
        }
        foreach ( $orphans as $o ) {
            $zip2->deleteName($o);
        }
        $zip2->close();
    }

    if ( ! file_exists($fpath) || filesize($fpath) < 500 ) {
        return rest_ensure_response(array('success'=>false,'error'=>'PPTX file creation failed.'));
    }

    return rest_ensure_response(array(
        'success'      => true,
        'url'          => $furl,
        'filename'     => $safe . '.pptx',
        'images'       => count($slide_images),
        'mkt_unlocked' => $mkt_unlocked,
        'mkt_locked'   => $mkt_locked,
    ));
}
}

// Variant routing now handled directly inside ds_pptv2_full_build

// Filter approach replaced by direct ds_render_slide_body override above

// ─── TITLE slide variants ────────────────────────────────────────────────────


// ─── Cover image helper: crops image to fill container with correct aspect ratio ─
// Simulates CSS background-size:cover for PPTX images.
// Assumes AI-generated images are 16:9. Crops proportionally so image fills container.
if ( ! function_exists('ds_pptv2_img_cover') ) {
function ds_pptv2_img_cover($id, $name, $x, $y, $cx, $cy, $rid, $fx = 50, $fy = 50) {
	if ( $fx == 50 && $fy == 50 && ! empty($GLOBALS['_pptv2_img_pos']) ) {
        $fx = isset($GLOBALS['_pptv2_img_pos']['x']) ? (float)$GLOBALS['_pptv2_img_pos']['x'] : 50;
        $fy = isset($GLOBALS['_pptv2_img_pos']['y']) ? (float)$GLOBALS['_pptv2_img_pos']['y'] : 50;
    }
    // Use ACTUAL image AR from global registry (set when image was embedded)
    // Falls back to 16:9 if not available
    $key = isset($GLOBALS['_pptv2_img_rid_key']) ? $GLOBALS['_pptv2_img_rid_key'] : '';
    $img_ar = ( $key && isset($GLOBALS['_pptv2_img_ar'][$key]) )
        ? (float)$GLOBALS['_pptv2_img_ar'][$key]
        : 16.0 / 9.0;
    $cont_ar = (float)$cx / (float)$cy;
    $fx = max(0, min(100, (float)$fx)) / 100.0;
    $fy = max(0, min(100, (float)$fy)) / 100.0;
    $t = $b = $l = $r = 0;
    if ( $cont_ar > $img_ar ) {
        // Container wider -> image fills width, crop top/bottom around the focal point
        $visible_h_frac = $img_ar / $cont_ar;
        $half = $visible_h_frac / 2.0;
        $win_top = $fy - $half;
        if ($win_top < 0) $win_top = 0;
        if ($win_top > 1 - $visible_h_frac) $win_top = 1 - $visible_h_frac;
        $win_bottom = $win_top + $visible_h_frac;
        $t = (int) round($win_top * 100000);
        $b = (int) round((1 - $win_bottom) * 100000);
    } else {
        // Container taller -> image fills height, crop left/right around the focal point
        $visible_w_frac = $cont_ar / $img_ar;
        $half = $visible_w_frac / 2.0;
        $win_left = $fx - $half;
        if ($win_left < 0) $win_left = 0;
        if ($win_left > 1 - $visible_w_frac) $win_left = 1 - $visible_w_frac;
        $win_right = $win_left + $visible_w_frac;
        $l = (int) round($win_left * 100000);
        $r = (int) round((1 - $win_right) * 100000);
    }
    return '<p:pic>'
        . '<p:nvPicPr><p:cNvPr id="'.$id.'" name="'.$name.'"/>'
        . '<p:cNvPicPr><a:picLocks noChangeAspect="0"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'
        . '<p:blipFill><a:blip r:embed="'.$rid.'"/>'
        . '<a:srcRect l="'.$l.'" t="'.$t.'" r="'.$r.'" b="'.$b.'"/>'
        . '<a:stretch><a:fillRect/></a:stretch></p:blipFill>'
        . '<p:spPr><a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic>';
}
}
/* ── Layout config (adjustable) ──────────────────────────────────
   DS_PPT_OVERLAY_PCT : opacity % of the theme-coloured wash laid over a
                        full-background image (0-100).
   DS_PPT_IMG_PCT     : image share % of an image+text layout (0-100).
   Mirror of DS_OVERLAY_PCT / DS_IMG_PCT in Snippet 17.               */
if ( ! defined('DS_PPT_OVERLAY_PCT') ) { define('DS_PPT_OVERLAY_PCT', 80); }
if ( ! defined('DS_PPT_IMG_PCT') )     { define('DS_PPT_IMG_PCT',     60); }

if ( ! function_exists('ds_pptv2_title_v2') ) {
function ds_pptv2_title_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $bg = 'FFFFFF', $bg2 = null) {
    $title    = $slide['title'] ?? 'Presentation';
    $subtitle = $slide['subtitle'] ?? '';
    $f        = ds_parse_title_fields($slide['content'] ?? array());

    $shapes = '';
    if ( $img_rid ) {
        $shapes .= ds_pptv2_img_cover(2, 'TitleBG', 0, 0, 12192000, 6858000, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    }
    // Use first color of gradient (or solid color)
$theme_bg = $bg;
$shapes .= ds_filled_rect(3, 'Overlay', 0, 0, 12192000, 6858000, $theme_bg, (int) (DS_PPT_OVERLAY_PCT * 1000));                                            
    
    $id = 5;
    $shapes .= ds_title_header_shapes($id, $f, 'FFFFFF', 'E5EAF2', $ac, 800000, 1200000, 10592000, 'ctr');
    $t_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($title, 5600, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 2200000, 11277600, 1800000, $t_p, 'ctr', 'ctr');
    $shapes .= ds_filled_rect($id++, 'AccentLine', 5596000, 4100000, 1000000, 60000, $ac);
    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($subtitle, 2000, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 800000, 4400000, 10592000, 900000, $s_p, 'ctr', 't');
    }
    $shapes .= ds_title_footer_shapes($id, $f, 'FFFFFF', 'E5EAF2', $ac, 800000, 5500000, 10592000, 'ctr');
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_title_v3') ) {
function ds_pptv2_title_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title'] ?? 'Presentation';
    $subtitle = $slide['subtitle'] ?? '';
    $f        = ds_parse_title_fields($slide['content'] ?? array());
    $half     = (int) (12192000 * (100 - DS_PPT_IMG_PCT) / 100);
    $zx       = 400000;
    $zw       = $half - 500000;

    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'TitleImg', $half, 0, 12192000 - $half, 6858000, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect(2, 'ImgPH', $half, 0, 12192000 - $half, 6858000, $ac, 15000);
    }

    $id = 3;
    $shapes .= ds_title_header_shapes($id, $f, $tc, $bc, $ac, $zx, 900000, $zw, 'l');
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, ds_title_font_size($title, 5000), $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $zx, 1800000, $zw, 2200000, $t_p, 'l', 'ctr');
    $shapes .= ds_filled_rect($id++, 'AccentLine', $zx, 4100000, 900000, 60000, $ac);
    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1900, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $zx, 4300000, $zw, 900000, $s_p, 'l', 't');
    }
    $shapes .= ds_title_footer_shapes($id, $f, $tc, $bc, $ac, $zx, 5300000, $zw, 'l');
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_title_v4') ) {
function ds_pptv2_title_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title'] ?? 'Presentation';
    $subtitle = $slide['subtitle'] ?? '';
    $f        = ds_parse_title_fields($slide['content'] ?? array());
    $half     = (int) (12192000 * DS_PPT_IMG_PCT / 100);
    $zx       = $half + 300000;
    $zw       = 12192000 - $half - 600000;

    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'TitleImg', 0, 0, $half, 6858000, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect(2, 'ImgPH', 0, 0, $half, 6858000, $ac, 15000);
    }

    $id = 3;
    $shapes .= ds_title_header_shapes($id, $f, $tc, $bc, $ac, $zx, 900000, $zw, 'l');
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, ds_title_font_size($title, 4600), $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $zx, 1800000, $zw, 2200000, $t_p, 'l', 'ctr');
    $shapes .= ds_filled_rect($id++, 'AccentLine', $zx, 4100000, 900000, 60000, $ac);
    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1800, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $zx, 4300000, $zw, 900000, $s_p, 'l', 't');
    }
    $shapes .= ds_title_footer_shapes($id, $f, $tc, $bc, $ac, $zx, 5300000, $zw, 'l');
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_title_v5') ) {
/* Blend two hex colours. Used so the image-title variants tint their muted
   label text from the ACTIVE theme instead of a hardcoded navy grey. */
if ( ! function_exists('ds_mix_hex') ) {
function ds_mix_hex( $from, $to, $t ) {
    $from = ltrim((string) $from, '#');
    $to   = ltrim((string) $to,   '#');
    if ( strlen($from) !== 6 || strlen($to) !== 6 ) { return $from; }
    $t = max(0, min(1, (float) $t));
    $out = '';
    for ( $i = 0; $i < 3; $i++ ) {
        $a = hexdec(substr($from, $i * 2, 2));
        $b = hexdec(substr($to,   $i * 2, 2));
        $out .= str_pad(dechex((int) round($a + ($b - $a) * $t)), 2, '0', STR_PAD_LEFT);
    }
    return strtoupper($out);
}
}

function ds_pptv2_title_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title']    ?? 'Presentation';
    $subtitle = $slide['subtitle'] ?? '';
    $f        = ds_parse_title_fields($slide['content'] ?? array());
    $slideH   = 6858000;
    $slideW   = 12192000;
    // Widened from 45% to 60% of slide height so there's room for the new
    // header/footer fields; the bottom image strip shrinks to compensate.
    $textH    = (int) ($slideH * 0.60);
    $imgY     = $textH;
    $imgH     = $slideH - $textH;

    $shapes = '';
    // Removed: opaque 'TextBg' panel was filled with the TITLE colour, not the
    // theme background, so it exported as a white rectangle covering the text.
    // The slide's own <p:bg> already paints the theme background/gradient.

    $id = 3;
    $shapes .= ds_title_header_shapes($id, $f, 'FFFFFF', ds_mix_hex($tc, 'FFFFFF', 0.62), $ac, 800000, 130000, $slideW - 1600000, 'ctr');

    $t_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($title, 3600, 'FFFFFF', true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 700000, $slideW - 914400, 1000000, $t_p, 'ctr', 'ctr');

    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($subtitle, 1600, ds_mix_hex($tc, 'FFFFFF', 0.62)) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 800000, 1750000, $slideW - 1600000, 500000, $s_p, 'ctr', 't');
    }

    $shapes .= ds_title_footer_shapes($id, $f, 'FFFFFF', ds_mix_hex($tc, 'FFFFFF', 0.62), $ac, 800000, 2400000, $slideW - 1600000, 'ctr');

    $shapes .= ds_filled_rect($id++, 'AccentBar', 0, $imgY - 80000, $slideW, 80000, $ac);

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BottomImg', 0, $imgY, $slideW, $imgH, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect($id, 'ImgPH', 0, $imgY, $slideW, $imgH, $ac);
    }

    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_title_v6') ) {
// v6: Top = image (full bleed), Bottom = text
function ds_pptv2_title_v6($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title']    ?? 'Presentation';
    $subtitle = $slide['subtitle'] ?? '';
    $f        = ds_parse_title_fields($slide['content'] ?? array());
    $slideH   = 6858000;
    $slideW   = 12192000;
    // Widened from 45% to 60% text (image shrinks from 55% to 40%) so
    // there's room for the new header/footer fields — same fix as v5.
    $imgH     = (int) ($slideH * 0.40);
    $textY    = $imgH;
    $textH    = $slideH - $imgH;

    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'TopImg', 0, 0, $slideW, $imgH, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect(2, 'ImgPH', 0, 0, $slideW, $imgH, $ac);
    }
    $shapes .= ds_filled_rect(3, 'AccentBar', 0, $textY, $slideW, 80000, $ac);
    // Removed: see note in ds_pptv2_title_v5 - 'TextBg' used \$tc (title colour).

    $id = 5;
    $shapes .= ds_title_header_shapes($id, $f, 'FFFFFF', ds_mix_hex($tc, 'FFFFFF', 0.62), $ac, 800000, $textY + 200000, $slideW - 1600000, 'ctr');

    $t_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($title, 3600, 'FFFFFF', true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, $textY + 780000, $slideW - 914400, 1000000, $t_p, 'ctr', 'ctr');

    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($subtitle, 1600, ds_mix_hex($tc, 'FFFFFF', 0.62)) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 800000, $textY + 1830000, $slideW - 1600000, 500000, $s_p, 'ctr', 't');
    }

    $shapes .= ds_title_footer_shapes($id, $f, 'FFFFFF', ds_mix_hex($tc, 'FFFFFF', 0.62), $ac, 800000, $textY + 2480000, $slideW - 1600000, 'ctr');

    return $shapes;
}
}

// ─── BULLET slide variants ───────────────────────────────────────────────────

if ( ! function_exists('ds_pptv2_bullet_v2') ) {
// v2: Bullet list on left, image on right
function ds_pptv2_bullet_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;
    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($slide['title'] ?? '', 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 200000, 7856321, 780000, $title_p, 'l', 't');
    if ( !empty($slide['subtitle']) ) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($slide['subtitle'], 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 457200, 1000000, 7856321, 420000, $sub_p, 'l', 't');
    }
    // Bullet rows (left half)
    $symbols = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('check','star','trending','target','lightbulb','rocket');
    $content = array_slice($slide['content'] ?? array(), 0, 6);
    $n = max(1, count($content));
    $content_y = 1600000;
    // FIX: row height used to be a fixed 750000 EMU no matter how many
    // bullets there were, so longer lists (or longer text per bullet)
    // simply overflowed into the next row. Now each row gets an even
    // share of the actual remaining space, and text/icon sizes scale
    // down a little only when that share drops below the original size.
    $available = 6858000 - $content_y - 250000;
    $row_h = max(550000, (int) floor($available / $n));
    $scale = min(1.0, $row_h / 750000);
    $head_sz = max(1300, (int) round(1700 * $scale));
    $body_sz = max(1050, (int) round(1380 * $scale));
    $icon_size = max(300000, (int) round(480000 * $scale));
    $icon_x = 457200;
    $text_x = $icon_x + $icon_size + 240000;
    $text_w = 6100000 - $icon_size - 300000;

    foreach ($content as $i => $item) {
        $item = (string)$item;
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $explanation = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);
        $row_y = $content_y + $i * $row_h;
        $icy = $row_y + (int)(($row_h - $icon_size) / 2);
        $shapes .= ds_ellipse($id++, 'Bg'.$i, $icon_x, $icy, $icon_size, $icon_size, $ac, 18000);
        $sn = $defaults[$i % count($defaults)];
        $sym = isset($symbols[$sn]) ? $symbols[$sn] : '●';
        $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($sym, max(1200,(int)round(2000*$scale)), $ac, false, false, 'Segoe UI Emoji') . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sym'.$i, $icon_x, $icy, $icon_size, $icon_size, $sym_p, 'ctr', 'ctr');
        $txt = '';
        if ($heading) $txt .= '<a:p><a:pPr algn="l"/>' . ds_run($heading, $head_sz, $tc, true) . '</a:p>';
        if ($explanation) $txt .= '<a:p><a:pPr algn="l"><a:spcBef><a:spcPts val="60"/></a:spcBef></a:pPr>' . ds_run($explanation, $body_sz, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Txt'.$i, $text_x, $row_y + 10000, $text_w, $row_h - 10000, $txt, 'l', 't');
    }
    // Image RIGHT — full bleed
    $imgX = 8656321; $imgY = 0;
    $imgCX = 3535679; $imgCY = 6858000;
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BulletImg', $imgX, $imgY, $imgCX, $imgCY, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', $imgX, $imgY, $imgCX, $imgCY, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_bullet_v3') ) {
// v3: Image on left, bullet list on right
function ds_pptv2_bullet_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;
    // Image LEFT — full bleed
    $imgW = 3535679; $imgH = 6858000;
    $imgX = 0; $imgY = 0;
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id++, 'BulletImg', $imgX, $imgY, $imgW, $imgH, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', $imgX, $imgY, $imgW, $imgH, $ac, 15000, 3000);
    }
    $textAreaX = $imgW + 100000;
    $textAreaW = 12192000 - $textAreaX - 400000;
    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($slide['title'] ?? '', 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $textAreaX, 200000, $textAreaW, 780000, $title_p, 'l', 't');
    if ( !empty($slide['subtitle']) ) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($slide['subtitle'], 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $textAreaX, 1000000, $textAreaW, 420000, $sub_p, 'l', 't');
    }
    $symbols = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('check','star','trending','target','lightbulb','rocket');
    $content = array_slice($slide['content'] ?? array(), 0, 5);
    $n = max(1, count($content));
    $content_y = 1600000;
    // FIX: same fixed-row-height overflow bug as bullet_v2, fixed the same way.
    $available = 6858000 - $content_y - 250000;
    $row_h = max(550000, (int) floor($available / $n));
    $scale = min(1.0, $row_h / 820000);
    $head_sz = max(1250, (int) round(1600 * $scale));
    $body_sz = max(1000, (int) round(1300 * $scale));
    $icon_size = max(300000, (int) round(460000 * $scale));
    $icon_x = $textAreaX;
    $text_x = $icon_x + $icon_size + 200000;
    $text_w = $textAreaW - $icon_size - 260000;
    foreach ($content as $i => $item) {
        $item = (string)$item;
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $explanation = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);
        $row_y = $content_y + $i * $row_h;
        $icy = $row_y + (int)(($row_h - $icon_size)/2);
        $shapes .= ds_ellipse($id++, 'Bg'.$i, $icon_x, $icy, $icon_size, $icon_size, $ac, 18000);
        $sn = $defaults[$i % count($defaults)]; $sym = isset($symbols[$sn]) ? $symbols[$sn] : '●';
        $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($sym, max(1100,(int)round(1900*$scale)), $ac, false, false, 'Segoe UI Emoji') . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sym'.$i, $icon_x, $icy, $icon_size, $icon_size, $sym_p, 'ctr', 'ctr');
        $txt = '';
        if ($heading) $txt .= '<a:p><a:pPr algn="l"/>' . ds_run($heading, $head_sz, $tc, true) . '</a:p>';
        if ($explanation) $txt .= '<a:p><a:pPr algn="l"><a:spcBef><a:spcPts val="60"/></a:spcBef></a:pPr>' . ds_run($explanation, $body_sz, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Txt'.$i, $text_x, $row_y + 10000, $text_w, $row_h - 10000, $txt, 'l', 't');
    }
    return $shapes;
}
}

// ─── FEATURE_GRID variants (same pattern for two_column, three_column) ──────

if ( ! function_exists('ds_pptv2_feature_grid_v4') ) {
// v4: Title + row of 4 cards on top, image full-width on bottom (full bleed)
function ds_pptv2_feature_grid_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 4);
    while (count($content) < 4) $content[] = '';

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 400000, 200000, 11392000, 700000, $t_p, 'l', 't');
    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 400000, 950000, 11392000, 420000, $s_p, 'l', 't');
    }

    $img_y    = 4100000;
    $card_y   = 1550000;
    $card_h   = $img_y - $card_y - 200000;
    $gap      = 180000;
    $card_w   = (int) round((11392000 - 3 * $gap) / 4);

    for ($i = 0; $i < 4; $i++) {
        $cx   = 400000 + $i * ($card_w + $gap);
        $item = (string)$content[$i];
        $colon   = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $card_y, $card_w, $card_h, $ac, 10000, 3500);

        /* Icon badge on the LEFT with the heading beside it, a hairline rule,
           then left-aligned body copy underneath. */
        $tile_sz = 420000;
        $tile_x  = $cx + 200000;
        $tile_y  = $card_y + 180000;
        $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $pad = (int) ($tile_sz * 0.26);
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x + $pad, $tile_y + $pad, $tile_sz - $pad * 2, $icon_rids[$i]);
        }
        if ($heading) {
            $hx  = $tile_x + $tile_sz + 170000;
            $hw  = ($cx + $card_w - 200000) - $hx;
            $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, 1600, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CH'.$i, $hx, $tile_y, $hw, $tile_sz, $h_p, 'l', 'ctr');
        }
        $ruleY = $tile_y + $tile_sz + 130000;
        $shapes .= ds_filled_rect($id++, 'Rule'.$i, $tile_x, $ruleY, (int) $card_w - 400000, 9000, $ac);
        if ($desc) {
            $d_p    = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1300, $bc) . '</a:p>';
            $desc_y = $ruleY + 170000;
            $desc_h = max(300000, (int) $card_h - ($desc_y - $card_y) - 200000);
            $shapes .= ds_text_shape($id++, 'CD'.$i, $tile_x, $desc_y, (int) $card_w - 400000, $desc_h, $d_p, 'l', 't');
        }
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BotImg', 0, $img_y, 12192000, 6858000 - $img_y, $img_rid);
    } else {
        $shapes .= ds_filled_rect($id++, 'BotPH', 0, $img_y, 12192000, 6858000 - $img_y, $ac);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_feature_grid_v5') ) {
// v5: 4 stacked rows (icon LEFT, heading+description RIGHT) + image full-height right (full bleed)
function ds_pptv2_feature_grid_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 4);
    while (count($content) < 4) $content[] = '';

    $imgW    = 3535679;
    $margin  = 400000;
    $gap2img = 300000;
    $imgX    = 12192000 - $imgW;
    $rowX    = $margin;
    $rowW    = 12192000 - $imgW - $margin - $gap2img;

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $rowX, 200000, $rowW, 780000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $rowX, 1000000, $rowW, 420000, $sub_p, 'l', 't');
    }

    $row_y0  = 1650000;
    $row_gap = 150000;
    $row_h   = (int) round((6858000 - $row_y0 - 250000 - 3 * $row_gap) / 4);

    for ($i = 0; $i < 4; $i++) {
        $ry   = $row_y0 + $i * ($row_h + $row_gap);
        $item = (string)$content[$i];
        $colon   = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Row'.$i, $rowX, $ry, $rowW, $row_h, $ac, 10000, 3500);

        $tile_sz = min(620000, $row_h - 200000);
        $tile_x  = $rowX + 220000;
        $tile_y  = $ry + (int) round(($row_h - $tile_sz) / 2);

        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 120000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x + $pad, $tile_y + $pad, $tile_sz - $pad * 2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2400, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $div_x = $tile_x + $tile_sz + 150000;
        $shapes .= ds_filled_rect($id++, 'Div'.$i, $div_x, $ry + 150000, 15000, $row_h - 300000, $ac);

        $text_x = $div_x + 150000;
        $text_w = $rowX + $rowW - $text_x - 200000;

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, 1700, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'RH'.$i, $text_x, $ry + 140000, $text_w, 400000, $h_p, 'l', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1200, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'RD'.$i, $text_x, $ry + 540000, $text_w, $row_h - 600000, $d_p, 'l', 't');
        }
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'GridImg', $imgX, 0, $imgW, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', $imgX, 0, $imgW, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_feature_grid_v2') ) {
// v2: 2x2 grid on left, image on right
function ds_pptv2_feature_grid_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 4, 2);
}
}
if ( ! function_exists('ds_pptv2_feature_grid_v3') ) {
// v3: image on left, 2x2 grid on right
function ds_pptv2_feature_grid_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 4, 2);
}
}
if ( ! function_exists('ds_pptv2_two_column_v2') ) {
function ds_pptv2_two_column_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 2, 1);
}
}
if ( ! function_exists('ds_pptv2_two_column_v3') ) {
function ds_pptv2_two_column_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 2, 1);
}
}
if ( ! function_exists('ds_pptv2_three_column_v2') ) {
function ds_pptv2_three_column_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 3, 1);
}
}
if ( ! function_exists('ds_pptv2_three_column_v3') ) {
function ds_pptv2_three_column_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 3, 1);
}
}
if ( ! function_exists('ds_pptv2_three_column_v4') ) {
// v4: Title + 3-card row on top, image full-width on bottom
function ds_pptv2_three_column_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($content) < 3) $content[] = '';

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2800, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 400000, 220000, 11392000, 620000, $t_p, 'l', 't');

    $card_y = 1050000; $card_h = 2550000; $gap = 200000;
    $card_w = (int) round((11392000 - 2*$gap) / 3);

    for ($i = 0; $i < 3; $i++) {
        $cx = 400000 + $i * ($card_w + $gap);
        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $card_y, $card_w, $card_h, $ac, 10000, 3500);

        $tile_sz = 420000;
        $tile_x  = $cx + (int) round(($card_w - $tile_sz) / 2);
        $tile_y  = $card_y + 180000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 100000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2000, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }
        $ch_w  = (int)$card_w - 500000;
$ch_cpl = max(8, (int) floor($ch_w / 128000));
$ch_max = $ch_cpl * 2;
$h_len  = function_exists('mb_strlen') ? mb_strlen($heading) : strlen($heading);
if ($h_len > $ch_max) {
    $heading = (function_exists('mb_substr') ? mb_substr($heading, 0, $ch_max - 1) : substr($heading, 0, $ch_max - 1)) . '…';
}
$ch_h = 560000;
if ($heading) {
    $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, 1800, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'CH'.$i, $cx + 250000, $tile_y + $tile_sz + 120000, $ch_w, $ch_h, $h_p, 'l', 't');
}
if ($desc) {
    $cd_y   = $tile_y + $tile_sz + 120000 + $ch_h + 60000;
    $cd_h   = max(300000, (int)$card_h - ($cd_y - $card_y) - 150000);
    $cd_cpl = max(10, (int) floor($ch_w / 82550));
    $cd_max = $cd_cpl * max(1, (int) floor($cd_h / 214630));
    $d_len  = function_exists('mb_strlen') ? mb_strlen($desc) : strlen($desc);
    if ($d_len > $cd_max) {
        $desc = (function_exists('mb_substr') ? mb_substr($desc, 0, $cd_max - 1) : substr($desc, 0, $cd_max - 1)) . '…';
    }
    $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1300, $bc) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'CD'.$i, $cx + 250000, $cd_y, $ch_w, $cd_h, $d_p, 'l', 't');
}
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BottomImg', 0, 3840000, 12192000, 3018000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 0, 3840000, 12192000, 3018000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_three_column_v5') ) {
// v5: Image left, 3 stacked split (icon+heading | divider | description) cards right
function ds_pptv2_three_column_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($content) < 3) $content[] = '';

    $img_w = 4145000;
    $cx0 = $img_w + 300000;
    $cw  = 12192000 - $cx0 - 300000;
    $left_w = (int) round($cw * 0.3);

    $shapes = '';
    $id = 2;
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id++, 'LeftImg', 0, 0, $img_w, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 0, 0, $img_w, 6858000, $ac, 15000, 3000);
    }

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2400, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $cx0, 220000, $cw, 600000, $t_p, 'l', 't');

    $row_y = 1100000; $row_h = 1800000; $gap = 130000;
    for ($i = 0; $i < 3; $i++) {
        $ry = $row_y + $i * ($row_h + $gap);
        $shapes .= ds_rounded_rect($id++, 'Row'.$i, $cx0, $ry, $cw, $row_h, $ac, 10000, 3500);

        $tile_sz = 380000;
        $tile_x  = $cx0 + (int) round(($left_w - $tile_sz) / 2);
        $tile_y  = $ry + 130000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 90000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1800, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'H'.$i, $cx0+30000, $tile_y+$tile_sz+50000, $left_w-60000, $row_h-($tile_sz+180000), $h_p, 'ctr', 't');
        }

        $div_x = $cx0 + $left_w;
        $shapes .= ds_filled_rect($id++, 'Div'.$i, $div_x, $ry+180000, 12700, $row_h-360000, $ac);

        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'D'.$i, $div_x+150000, $ry, $cx0+$cw-$div_x-250000, $row_h, $d_p, 'l', 'ctr');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_three_column_v6') ) {
// v6: 3 stacked split cards left, image right — mirror of v5
function ds_pptv2_three_column_v6($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($content) < 3) $content[] = '';

    $img_w = 4145000;
    $cx0 = 300000;
    $cw  = 12192000 - $img_w - $cx0 - 300000;
    $left_w = (int) round($cw * 0.3);

    $shapes = '';
    $id = 2;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2400, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $cx0, 220000, $cw, 600000, $t_p, 'l', 't');

    $row_y = 1100000; $row_h = 1800000; $gap = 130000;
    for ($i = 0; $i < 3; $i++) {
        $ry = $row_y + $i * ($row_h + $gap);
        $shapes .= ds_rounded_rect($id++, 'Row'.$i, $cx0, $ry, $cw, $row_h, $ac, 10000, 3500);

        $tile_sz = 380000;
        $tile_x  = $cx0 + (int) round(($left_w - $tile_sz) / 2);
        $tile_y  = $ry + 130000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 90000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1800, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'H'.$i, $cx0+30000, $tile_y+$tile_sz+50000, $left_w-60000, $row_h-($tile_sz+180000), $h_p, 'ctr', 't');
        }

        $div_x = $cx0 + $left_w;
        $shapes .= ds_filled_rect($id++, 'Div'.$i, $div_x, $ry+180000, 12700, $row_h-360000, $ac);

        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'D'.$i, $div_x+150000, $ry, $cx0+$cw-$div_x-250000, $row_h, $d_p, 'l', 'ctr');
        }
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'RightImg', 12192000-$img_w, 0, $img_w, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 12192000-$img_w, 0, $img_w, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_three_column_v7') ) {
// v7: 2 cards top + 1 wide split card bottom, image right
function ds_pptv2_three_column_v7($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($content) < 3) $content[] = '';

    $content_w = 8656321;
    $id = 2;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2600, $tc, true) . '</a:p>';
    $shapes = ds_text_shape($id++, 'Title', 400000, 220000, $content_w-400000, 600000, $t_p, 'l', 't');

    // Top row: 2 icon-topped cards
    $top_y = 1050000; $top_h = 2350000; $gap = 200000;
    $col_w = (int) round(($content_w - 400000 - $gap) / 2);
    for ($i = 0; $i < 2; $i++) {
        $cx = 400000 + $i * ($col_w + $gap);
        $shapes .= ds_rounded_rect($id++, 'Top'.$i, $cx, $top_y, $col_w, $top_h, $ac, 10000, 3500);

        $tile_sz = 400000;
        $tile_x  = $cx + (int) round(($col_w - $tile_sz) / 2);
        $tile_y  = $top_y + 160000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 100000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1900, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);
        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1400, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'TH'.$i, $cx+120000, $tile_y+$tile_sz+110000, $col_w-240000, 460000, $h_p, 'ctr', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'TD'.$i, $cx+120000, $tile_y+$tile_sz+610000, $col_w-240000, $top_h-$tile_sz-800000, $d_p, 'ctr', 't');
        }
    }

    // Bottom row: 1 wide split card (icon+heading | divider | description)
    $bot_y = $top_y + $top_h + 200000;
    $bot_h = 6858000 - $bot_y - 300000;
    $bw = $content_w - 400000;
    $left_w = (int) round($bw * 0.28);
    $shapes .= ds_rounded_rect($id++, 'Bot', 400000, $bot_y, $bw, $bot_h, $ac, 10000, 3500);

    $tile_sz = 380000;
    $tile_x  = 400000 + (int) round(($left_w - $tile_sz) / 2);
    $tile_y  = $bot_y + (int) round(($bot_h - $tile_sz) / 2);
    if ( isset($icon_rids[2]) && $icon_rids[2] ) {
        $shapes .= ds_ellipse($id++, 'IBg2', $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
        $pad = 90000;
        $shapes .= _ds_pptv2_icon_img($id++, 'IIc2', $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[2]);
    } else {
        $icon_n = $defaults[2 % count($defaults)];
        if ( isset($symbols[$icon_n]) ) {
            $shapes .= ds_ellipse($id++, 'IBg2', $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1800, 'FFFFFF', true, false, 'Arial') . '</a:p>';
            $shapes .= ds_text_shape($id++, 'ISy2', $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
        }
    }

    $item = (string)$content[2];
    $colon = strpos($item, ':');
    $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
    $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);
    if ($heading) {
        $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1200, $tc, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'BH', 400000+30000, $tile_y+$tile_sz+40000, $left_w-60000, $bot_h-($tile_sz+180000), $h_p, 'ctr', 't');
    }
    $div_x = 400000 + $left_w;
    $shapes .= ds_filled_rect($id++, 'BDiv', $div_x, $bot_y+150000, 12700, $bot_h-300000, $ac);
    if ($desc) {
        $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'BD', $div_x+150000, $bot_y, 400000+$bw-$div_x-250000, $bot_h, $d_p, 'l', 'ctr');
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'RightImg', $content_w, 0, 12192000-$content_w, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', $content_w, 0, 12192000-$content_w, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}
if ( ! function_exists('ds_pptv2_is_dark_color') ) {
function ds_pptv2_is_dark_color($hex){
    $c = ltrim((string)$hex, '#');
    if (strlen($c) === 3) $c = $c[0].$c[0].$c[1].$c[1].$c[2].$c[2];
    if (strlen($c) < 6) return false;
    $r=hexdec(substr($c,0,2)); $g=hexdec(substr($c,2,2)); $b=hexdec(substr($c,4,2));
    return (0.299*$r+0.587*$g+0.114*$b) < 128;
}
}

if ( ! function_exists('ds_pptv2_two_column_v4') ) {
// v4: Title + 2-card row on top, image full-width on bottom
function ds_pptv2_two_column_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 2);
    while (count($content) < 2) $content[] = '';

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2800, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 400000, 220000, 11392000, 620000, $t_p, 'l', 't');

    $card_y = 1050000; $card_h = 2550000; $gap = 200000;
    $card_w = (int) round((11392000 - $gap) / 2);

    for ($i = 0; $i < 2; $i++) {
        $cx = 400000 + $i * ($card_w + $gap);
        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $card_y, $card_w, $card_h, $ac, 10000, 3500);

        $tile_sz = 420000;
        $tile_x  = $cx + (int) round(($card_w - $tile_sz) / 2);
        $tile_y  = $card_y + 180000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 100000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2000, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }
        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1500, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CH'.$i, $cx+150000, $tile_y+$tile_sz+120000, $card_w-300000, 460000, $h_p, 'ctr', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 1100, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CD'.$i, $cx+150000, $tile_y+$tile_sz+640000, $card_w-300000, $card_h-$tile_sz-840000, $d_p, 'ctr', 't');
        }
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BottomImg', 0, 3840000, 12192000, 3018000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 0, 3840000, 12192000, 3018000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_two_column_v5') ) {
// v5: Full-bleed background image, dark/light overlay, title + 2 cards on top
function ds_pptv2_two_column_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 2);
    while (count($content) < 2) $content[] = '';

    $shapes = '';
    $id = 2;
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id++, 'BgImg', 0, 0, 12192000, 6858000, $img_rid);
        $ov = ds_pptv2_is_dark_color($tc) ? 'FFFFFF' : '000000';
        $shapes .= ds_filled_rect($id++, 'Overlay', 0, 0, 12192000, 6858000, $ov, 90000);
    }

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2800, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 400000, 250000, 11392000, 650000, $t_p, 'l', 't');

    $card_y = 1200000; $card_h = 5300000; $gap = 200000;
    $card_w = (int) round((11392000 - $gap) / 2);

    for ($i = 0; $i < 2; $i++) {
        $cx = 400000 + $i * ($card_w + $gap);
        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        $shapes .= ds_filled_rect($id++, 'Card'.$i, $cx, $card_y, $card_w, $card_h, $ac, 20000);

        $tile_sz = 460000;
        $tile_x  = $cx + (int) round(($card_w - $tile_sz) / 2);
        $tile_y  = $card_y + 350000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 110000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2200, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }
        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1700, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CH'.$i, $cx+150000, $tile_y+$tile_sz+150000, $card_w-300000, 500000, $h_p, 'ctr', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 1200, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CD'.$i, $cx+150000, $tile_y+$tile_sz+700000, $card_w-300000, $card_h-($tile_sz+900000), $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_two_column_v6') ) {
// v6: Content left (title + 2 stacked icon/heading/divider/desc cards), image right
function ds_pptv2_two_column_v6($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 2);
    while (count($content) < 2) $content[] = '';

    $img_w = 5850000;
    $cx0 = 300000;
    $cw  = 12192000 - $img_w - $cx0 - 340000;

    $shapes = '';
    $id = 2;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2400, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $cx0, 220000, $cw, 600000, $t_p, 'l', 't');

    $card_y = 1000000; $gap = 200000;
    $card_h = (int) round((6858000 - $card_y - 300000 - $gap) / 2);

    for ($i = 0; $i < 2; $i++) {
        $cy = $card_y + $i * ($card_h + $gap);
        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx0, $cy, $cw, $card_h, $ac, 10000, 3500);

        $tile_sz = 380000;
        $tile_x  = $cx0 + 200000;
        $tile_y  = $cy + 180000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 90000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1800, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        $head_y = $tile_y + $tile_sz + 80000;
        if ($heading) {
            $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, 1300, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'H'.$i, $cx0+200000, $head_y, $cw-400000, 420000, $h_p, 'l', 't');
        }
        $div_y = $head_y + 420000 + 60000;
        $shapes .= ds_filled_rect($id++, 'Div'.$i, $cx0+200000, $div_y, 280000, 12700, $ac);
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'D'.$i, $cx0+200000, $div_y+80000, $cw-400000, $card_h-($div_y-$cy)-180000, $d_p, 'l', 't');
        }
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'RightImg', 12192000-$img_w, 0, $img_w, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 12192000-$img_w, 0, $img_w, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_two_column_v7') ) {
// v7: Image left, content right (title + 2 split icon+heading|divider|desc rows)
function ds_pptv2_two_column_v7($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 2);
    while (count($content) < 2) $content[] = '';

    $img_w = 5850000;
    $cx0 = $img_w + 340000;
    $cw  = 12192000 - $cx0 - 300000;
    $left_w = (int) round($cw * 0.32);

    $shapes = '';
    $id = 2;
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id++, 'LeftImg', 0, 0, $img_w, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 0, 0, $img_w, 6858000, $ac, 15000, 3000);
    }

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2400, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $cx0, 220000, $cw, 600000, $t_p, 'l', 't');

    $row_y = 1000000; $gap = 200000;
    $row_h = (int) round((6858000 - $row_y - 300000 - $gap) / 2);
    for ($i = 0; $i < 2; $i++) {
        $ry = $row_y + $i * ($row_h + $gap);
        $shapes .= ds_rounded_rect($id++, 'Row'.$i, $cx0, $ry, $cw, $row_h, $ac, 10000, 3500);

        $tile_sz = 380000;
        $tile_x  = $cx0 + (int) round(($left_w - $tile_sz) / 2);
        $tile_y  = $ry + 150000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 90000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1800, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'H'.$i, $cx0+30000, $tile_y+$tile_sz+50000, $left_w-60000, $row_h-($tile_sz+200000), $h_p, 'ctr', 't');
        }

        $div_x = $cx0 + $left_w;
        $shapes .= ds_filled_rect($id++, 'Div'.$i, $div_x, $ry+180000, 12700, $row_h-360000, $ac);

        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1050, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'D'.$i, $div_x+150000, $ry, $cx0+$cw-$div_x-250000, $row_h, $d_p, 'l', 'ctr');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_two_column_v8') ) {
// v8: Content left (title + 2 split rows), image right — mirror of v7
function ds_pptv2_two_column_v8($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 2);
    while (count($content) < 2) $content[] = '';

    $img_w = 5850000;
    $cx0 = 300000;
    $cw  = 12192000 - $img_w - $cx0 - 340000;
    $left_w = (int) round($cw * 0.32);

    $shapes = '';
    $id = 2;

    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2400, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $cx0, 220000, $cw, 600000, $t_p, 'l', 't');

    $row_y = 1000000; $gap = 200000;
    $row_h = (int) round((6858000 - $row_y - 300000 - $gap) / 2);
    for ($i = 0; $i < 2; $i++) {
        $ry = $row_y + $i * ($row_h + $gap);
        $shapes .= ds_rounded_rect($id++, 'Row'.$i, $cx0, $ry, $cw, $row_h, $ac, 10000, 3500);

        $tile_sz = 380000;
        $tile_x  = $cx0 + (int) round(($left_w - $tile_sz) / 2);
        $tile_y  = $ry + 150000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 90000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x+$pad, $tile_y+$pad, $tile_sz-$pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1800, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($heading, 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'H'.$i, $cx0+30000, $tile_y+$tile_sz+50000, $left_w-60000, $row_h-($tile_sz+200000), $h_p, 'ctr', 't');
        }

        $div_x = $cx0 + $left_w;
        $shapes .= ds_filled_rect($id++, 'Div'.$i, $div_x, $ry+180000, 12700, $row_h-360000, $ac);

        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1050, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'D'.$i, $div_x+150000, $ry, $cx0+$cw-$div_x-250000, $row_h, $d_p, 'l', 'ctr');
        }
    }

    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'RightImg', 12192000-$img_w, 0, $img_w, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 12192000-$img_w, 0, $img_w, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('_ds_pptv2_icon_img') ) {
// Draws a real fetched icon (already square, e.g. 128x128) stretched to fit
// a square box — separate from ds_pptv2_img_cover, which is built for
// cropping wide photos and would misjudge a square icon's aspect ratio.
/* DS-FIX — emit the vector form of the icon when one was shipped.
   PowerPoint 2016+ reads asvg:svgBlip and renders the SVG, so the icon can be
   recoloured (Graphics Format > Graphics Fill, or Convert to Shape). Older
   versions ignore the extension and use the PNG named in r:embed. */
if ( ! function_exists('_ds_pptv2_icon_blip') ) {
function _ds_pptv2_icon_blip( $rid ) {
	$slide = isset($GLOBALS['ds_icon_cur_slide']) ? $GLOBALS['ds_icon_cur_slide'] : null;
	$map   = ( $slide !== null && isset($GLOBALS['ds_icon_svg_rid'][$slide]) && is_array($GLOBALS['ds_icon_svg_rid'][$slide]) )
		? $GLOBALS['ds_icon_svg_rid'][$slide]
		: array();
	if ( empty($map[$rid]) ) {
		return '<a:blip r:embed="'.$rid.'"/>';
	}
	return '<a:blip r:embed="'.$rid.'">'
		. '<a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}">'
		. '<asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="'.$map[$rid].'"/>'
		. '</a:ext></a:extLst>'
		. '</a:blip>';
}
}

function _ds_pptv2_icon_img($id, $name, $x, $y, $size, $rid) {
	return '<p:pic>'
		. '<p:nvPicPr><p:cNvPr id="'.$id.'" name="'.$name.'"/>'
		. '<p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'
		. '<p:blipFill>' . _ds_pptv2_icon_blip($rid)
        . '<a:stretch><a:fillRect/></a:stretch></p:blipFill>'
        . '<p:spPr><a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$size.'" cy="'.$size.'"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic>';
}
}

if ( ! function_exists('_ds_pptv2_grid_with_img') ) {
function _ds_pptv2_grid_with_img($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $img_side, $item_count, $rows) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('lightbulb','chart','users','target','rocket','star');
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, $item_count);
    while (count($content) < $item_count) $content[] = '';

    $cols = ceil($item_count / $rows);
    // Full-bleed: image at one edge, grid in the remaining space with a clear gap.
    // FIX: the grid used to assume it could fill nearly the whole remaining
    // width, which actually overlapped the image panel by 200000 EMU. This
    // now derives the grid width from the image width + margins, so the two
    // areas can never touch — and the cards end up a bit narrower too.
    $imgW    = 3535679;
    $margin  = 400000;   // outer margin on the side away from the image
    $gap2img = 300000;   // gap between the grid and the image panel
    $imgX    = ($img_side === 'right') ? (12192000 - $imgW) : 0;
    $gridX   = ($img_side === 'right') ? $margin : ($imgW + $gap2img);
    $gridW   = 12192000 - $imgW - $margin - $gap2img;

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    // FIX: title must start AFTER image for left-image layouts
    $title_x = ($img_side === 'left') ? ($imgW + $gap2img) : $margin;
    $title_w  = 12192000 - $title_x - 200000;
    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $title_x, 200000, $title_w, 780000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $title_x, 1000000, $title_w, 420000, $sub_p, 'l', 't');
    }

    $card_y   = 1650000;
    $card_h   = (int) round((6858000 - $card_y - 300000) / $rows);
    $gap      = 200000;
    $card_w   = (int) round(($gridW - ($cols - 1) * $gap) / $cols);

    for ($i = 0; $i < $item_count; $i++) {
        $col = $i % $cols; $row = (int)($i / $cols);
        $cx  = (int) round($gridX + $col * ($card_w + $gap));
        $cy  = (int) round($card_y + $row * ($card_h + $gap));
        $item = (string)$content[$i];
        $colon = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon+1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $cy, (int)$card_w, (int)$card_h, $ac, 10000, 3500);

        $cpad    = 220000;
        $tile_sz = 420000;
        $tile_x  = $cx + $cpad;
        $tile_y  = $cy + $cpad;

        // FIX: a real, AI-chosen icon (relevant to this card's heading) was
        // being fetched and embedded but never actually drawn — only a
        // generic round-robin fallback symbol was rendered, and only when
        // no real icon existed. Now the real icon is used whenever present.
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 130000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x + $pad, $tile_y + $pad, $tile_sz - $pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2600, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }
        if ($heading) {
            $h_sz = ($cols >= 4) ? 1400 : (($cols == 3) ? 1600 : 1800);
            $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, $h_sz, $tc, true) . '</a:p>';
            $hx  = $tile_x + $tile_sz + 160000;
            $hw  = max(300000, ($cx + (int)$card_w - $cpad) - $hx);
            $shapes .= ds_text_shape($id++, 'CH'.$i, $hx, $tile_y, $hw, $tile_sz, $h_p, 'l', 'ctr');
        }
        $ruleY = $tile_y + $tile_sz + 150000;
        $shapes .= ds_filled_rect($id++, 'CRule'.$i, $cx + $cpad, $ruleY, (int)$card_w - $cpad * 2, 12700, $ac);
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1300, $bc) . '</a:p>';
            $d_y = $ruleY + 150000;
            $d_h = max(260000, ($cy + (int)$card_h - $cpad) - $d_y);
            $shapes .= ds_text_shape($id++, 'CD'.$i, $cx + $cpad, $d_y, (int)$card_w - $cpad * 2, $d_h, $d_p, 'l', 't');
        }
    }

    // Image panel
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'GridImg', $imgX, 0, $imgW, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', $imgX, 0, $imgW, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

// ─── STATS variants ──────────────────────────────────────────────────────────

if ( ! function_exists('ds_pptv2_stats_v2') ) {
// v2: Stats left, image right
function ds_pptv2_stats_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($items) < 3) $items[] = '';

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 400000, 200000, 8056321, 780000, $t_p, 'l', 't');

    $col_w = 2100000; $start_x = 400000; $stat_y = 2000000; $label_y = 4000000;
    for ($i = 0; $i < 3; $i++) {
        $cx = $start_x + $i * $col_w;
        $item = (string)$items[$i];
        $parts = explode(':', $item, 2);
        $num = trim($parts[0]); $lbl = isset($parts[1]) ? trim($parts[1]) : '';
        $nl = function_exists('mb_strlen') ? mb_strlen($num) : strlen($num);
        // Smaller sizes prevent "85.2%" splitting — the % stays on same line
        $sz = $nl <= 3 ? 6000 : ($nl <= 4 ? 5000 : ($nl <= 5 ? 4000 : ($nl <= 7 ? 3200 : 2800)));
        $n_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($num, $sz, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Stat'.$i, $cx, $stat_y, $col_w, 1800000, $n_p, 'ctr', 'ctr');
        if ($lbl) {
            $l_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($lbl, 1400, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Lbl'.$i, $cx + 100000, $label_y, $col_w - 200000, 1000000, $l_p, 'ctr', 't');
        }
    }
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'StatsImg', 8656321, 0, 3535679, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect($id++, 'ImgPH', 8656321, 0, 3535679, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

// ─── TIMELINE variants ───────────────────────────────────────────────────────

if ( ! function_exists('ds_pptv2_timeline_v2') ) {
// v2: Horizontal timeline (original style - kept as option)
function ds_pptv2_timeline_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 5);
    $count = max(1, count($items));
    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3600, $tc, true) . '</a:p>';
    $title_box = ds_text_shape(2, 'Title', 457200, 400000, 11277600, 900000, $title_p, 'l', 't');
    $line_y = 2600000; $line_x1 = 1100000; $line_x2 = 11092000;
    $line_w = $line_x2 - $line_x1;
    $line = ds_filled_rect(3, 'TL', $line_x1, $line_y, $line_w, 30000, $ac);
    $shapes = $title_box . $line; $id = 4; $dot_size = 280000;
    for ($i = 0; $i < $count; $i++) {
        $t = $count === 1 ? 0.5 : $i / ($count - 1);
        $dot_cx = $line_x1 + (int)($t * $line_w);
        $dot_x = $dot_cx - (int)($dot_size/2);
        $dot_y = $line_y + 15000 - (int)($dot_size/2);
        $shapes .= ds_ellipse($id++, 'Dot'.$i, $dot_x, $dot_y, $dot_size, $dot_size, $ac);
        $item = (string)($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step = trim($parts[0]); $desc = isset($parts[1]) ? trim($parts[1]) : '';
        $box_w = 1800000; $box_x = $dot_cx - (int)($box_w/2);
        $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($step, 2000, $tc, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Step'.$i, $box_x, $line_y - 1100000, $box_w, 600000, $s_p, 'ctr', 'b');
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 1300, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $box_x, $line_y + 350000, $box_w, 1500000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v3') ) {
// v3: Zig-zag timeline
function ds_pptv2_timeline_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $items    = array_slice($slide['content'] ?? array(), 0, 5);
    $count    = max(1, count($items));

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes  = ds_text_shape(2, 'Title', 400000, 200000, 11392000, 700000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
        $shapes .= ds_text_shape(3, 'Sub', 400000, 950000, 11392000, 400000, $sub_p, 'l', 't');
    }
    $id = 4;

    $content_top = 1650000;
    $content_h   = 6858000 - $content_top - 300000;
    $spine_x     = 6096000;
    $shapes .= ds_filled_rect($id++, 'Spine', $spine_x - 7500, $content_top, 15000, $content_h, $ac, 40000);

    $row_h = (int) round($content_h / $count);
    for ($i = 0; $i < $count; $i++) {
        $is_l    = ($i % 2 === 0);
        $row_top = $content_top + $i * $row_h;
        $row_mid = $row_top + (int) round($row_h / 2);
        $box_h   = (int) round($row_h * 0.72);
        $box_y   = $row_top + (int) round(($row_h - $box_h) / 2);
        $box_w   = 5000000;
        $box_x   = $is_l ? ($spine_x - 400000 - $box_w) : ($spine_x + 400000);

        $item  = (string)($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';

        $shapes .= ds_rounded_rect($id++, 'Box'.$i, $box_x, $box_y, $box_w, $box_h, $ac, 12000, 3500);

        $tick_x1 = $is_l ? ($box_x + $box_w) : $spine_x;
        $tick_x2 = $is_l ? $spine_x : $box_x;
        $shapes .= ds_filled_rect($id++, 'Tick'.$i, $tick_x1, $row_mid - 7500, $tick_x2 - $tick_x1, 15000, $ac, 50000);

        $node_sz = 340000;
        $shapes .= ds_ellipse($id++, 'Node'.$i, $spine_x - (int)($node_sz/2), $row_mid - (int)($node_sz/2), $node_sz, $node_sz, $ac);
        $n_p = '<a:p><a:pPr algn="ctr"/>' . ds_run(str_pad((string)($i+1), 2, '0', STR_PAD_LEFT), 1300, 'FFFFFF', true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Num'.$i, $spine_x - (int)($node_sz/2), $row_mid - (int)($node_sz/2), $node_sz, $node_sz, $n_p, 'ctr', 'ctr');

        if ($step) {
            $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($step, 1700, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Step'.$i, $box_x + 220000, $box_y + 100000, $box_w - 440000, 420000, $s_p, 'l', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1200, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $box_x + 220000, $box_y + 520000, $box_w - 440000, $box_h - 620000, $d_p, 'l', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v4') ) {
// v4: Curved journey timeline
function ds_pptv2_timeline_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('rocket','chart','users','target','lightbulb','star');
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $items    = array_slice($slide['content'] ?? array(), 0, 5);
    $count    = max(1, count($items));

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes  = ds_text_shape(2, 'Title', 400000, 200000, 11392000, 700000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
        $shapes .= ds_text_shape(3, 'Sub', 400000, 950000, 11392000, 400000, $sub_p, 'l', 't');
    }
    $id = 4;

    $content_top = 1650000;
    $content_h   = 6858000 - $content_top - 300000;
    $base_y      = $content_top + (int) round($content_h * 0.5);
    $shapes .= ds_filled_rect($id++, 'Base', 400000, $base_y - 7500, 11392000, 15000, $ac, 35000);

    $x0 = 400000 + (int) round(0.08 * 11392000);
    $x1 = 400000 + (int) round(0.92 * 11392000);
    $node_sz = 620000;

    for ($i = 0; $i < $count; $i++) {
        $t  = $count === 1 ? 0.5 : $i / ($count - 1);
        $cx = $count === 1 ? (int) round(($x0 + $x1) / 2) : $x0 + (int) round($t * ($x1 - $x0));
        $up = ($i % 2 === 0);
        $cy = $up ? ($content_top + (int) round($content_h * 0.24)) : ($content_top + (int) round($content_h * 0.76));

        $item  = (string)($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';

        if ($up) {
            $stem_y = $cy + (int)($node_sz/2);
            $stem_h = max(0, $base_y - $stem_y);
        } else {
            $stem_y = $base_y;
            $stem_h = max(0, ($cy - (int)($node_sz/2)) - $base_y);
        }
        $shapes .= ds_filled_rect($id++, 'Stem'.$i, $cx - 7500, $stem_y, 15000, $stem_h, $ac, 35000);

        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $cx - (int)($node_sz/2), $cy - (int)($node_sz/2), $node_sz, $node_sz, $ac);
            $pad = 150000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $cx - (int)($node_sz/2) + $pad, $cy - (int)($node_sz/2) + $pad, $node_sz - $pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $cx - (int)($node_sz/2), $cy - (int)($node_sz/2), $node_sz, $node_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2200, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $cx - (int)($node_sz/2), $cy - (int)($node_sz/2), $node_sz, $node_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        $label_w = 2200000;
        $label_x = $cx - (int)($label_w/2);
        $label_y = $up ? $content_top : ($cy + (int)($node_sz/2) + 80000);
        if ($step) {
            $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($step, 1400, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Step'.$i, $label_x, $label_y, $label_w, 360000, $s_p, 'ctr', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $label_x, $label_y + 360000, $label_w, 500000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v5') ) {
// v5: Milestone cards timeline
function ds_pptv2_timeline_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defaults = array('rocket','chart','users','target','lightbulb','star');
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $items    = array_slice($slide['content'] ?? array(), 0, 5);
    $count    = max(1, count($items));

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes  = ds_text_shape(2, 'Title', 400000, 200000, 11392000, 700000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
        $shapes .= ds_text_shape(3, 'Sub', 400000, 950000, 11392000, 400000, $sub_p, 'l', 't');
    }
    $id = 4;

    $content_top = 1650000;
    $content_h   = 6858000 - $content_top - 300000;
    $base_y      = $content_top + (int) round($content_h * 0.5);
    $shapes .= ds_filled_rect($id++, 'Base', 400000, $base_y - 7500, 11392000, 15000, $ac, 35000);

    $x0 = 400000 + (int) round(0.06 * 11392000);
    $x1 = 400000 + (int) round(0.94 * 11392000);
    $card_w  = min(2400000, (int) round(11392000 / $count) + 500000);
    $card_h  = (int) round($content_h * 0.38);

    for ($i = 0; $i < $count; $i++) {
        $t  = $count === 1 ? 0.5 : $i / ($count - 1);
        $cx = $count === 1 ? (int) round(($x0 + $x1) / 2) : $x0 + (int) round($t * ($x1 - $x0));
        $up = ($i % 2 === 0);
        $card_x = $cx - (int) round($card_w / 2);
        $card_x = max(400000, min($card_x, 11392000 - $card_w + 400000));
        $card_y = $up ? ($base_y - 200000 - $card_h) : ($base_y + 200000);

        $item  = (string)($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';

        $stem_y = $up ? ($card_y + $card_h) : $base_y;
        $stem_h = 200000;
        $shapes .= ds_filled_rect($id++, 'Stem'.$i, $cx - 7500, $stem_y, 15000, $stem_h, $ac, 40000);
        $dot_sz = 140000;
        $shapes .= ds_ellipse($id++, 'Dot'.$i, $cx - (int)($dot_sz/2), $base_y - (int)($dot_sz/2), $dot_sz, $dot_sz, $ac);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $card_x, $card_y, $card_w, $card_h, $ac, 12000, 3500);

        $tile_sz = 420000;
        $tile_x  = $card_x + 200000;
        $tile_y  = $card_y + 180000;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = 100000;
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x + $pad, $tile_y + $pad, $tile_sz - $pad*2, $icon_rids[$i]);
        } else {
            $icon_n = $defaults[$i % count($defaults)];
            if ( isset($symbols[$icon_n]) ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 1900, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                $shapes .= ds_text_shape($id++, 'ISy'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
            }
        }

        if ($step) {
            $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($step, 1400, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Step'.$i, $card_x + 200000, $tile_y + $tile_sz + 80000, $card_w - 400000, 340000, $s_p, 'l', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
            $desc_h = $card_h - ($tile_y - $card_y) - $tile_sz - 460000;
            if ($desc_h < 100000) { $desc_h = 100000; }
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $card_x + 200000, $tile_y + $tile_sz + 420000, $card_w - 400000, $desc_h, $d_p, 'l', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v6') ) {
// v6: Circular timeline
function ds_pptv2_timeline_v6($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $items    = array_slice($slide['content'] ?? array(), 0, 5);
    $count    = max(1, count($items));

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes  = ds_text_shape(2, 'Title', 400000, 200000, 11392000, 700000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
        $shapes .= ds_text_shape(3, 'Sub', 400000, 950000, 11392000, 400000, $sub_p, 'l', 't');
    }
    $id = 4;

    $content_top = 1650000;
    $content_h   = 6858000 - $content_top - 300000;
    $ccx = 6096000;
    $ccy = $content_top + (int) round($content_h * 0.5);
    $rx  = 3600000;
    $ry  = (int) round($content_h * 0.40);

    $hub_sz = 1500000;
    $shapes .= ds_ellipse($id++, 'Hub', $ccx - (int)($hub_sz/2), $ccy - (int)($hub_sz/2), $hub_sz, $hub_sz, $ac, 22000);
    $hub_txt = $subtitle !== '' ? $subtitle : $title;
    $h_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($hub_txt, 1300, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'HubTxt', $ccx - (int)($hub_sz/2) + 80000, $ccy - (int)($hub_sz/2) + 80000, $hub_sz - 160000, $hub_sz - 160000, $h_p, 'ctr', 'ctr');

    $node_sz = 460000;
    for ($i = 0; $i < $count; $i++) {
        $ang = deg2rad(-90 + $i * (360 / $count));
        $cx  = $ccx + (int) round($rx * cos($ang));
        $cy  = $ccy + (int) round($ry * sin($ang));

        $item  = (string)($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';

        $shapes .= ds_ellipse($id++, 'Node'.$i, $cx - (int)($node_sz/2), $cy - (int)($node_sz/2), $node_sz, $node_sz, $ac);
        $n_p = '<a:p><a:pPr algn="ctr"/>' . ds_run(str_pad((string)($i+1), 2, '0', STR_PAD_LEFT), 1400, 'FFFFFF', true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Num'.$i, $cx - (int)($node_sz/2), $cy - (int)($node_sz/2), $node_sz, $node_sz, $n_p, 'ctr', 'ctr');

        $label_w = 1900000;
        $label_x = $cx - (int) round($label_w / 2);
        $label_y = $cy + (int)($node_sz/2) + 60000;
        if ($step) {
            $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($step, 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Step'.$i, $label_x, $label_y, $label_w, 320000, $s_p, 'ctr', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 900, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $label_x, $label_y + 320000, $label_w, 480000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v7') ) {
// v7: Stepped growth timeline
function ds_pptv2_timeline_v7($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $items    = array_slice($slide['content'] ?? array(), 0, 5);
    $count    = max(1, count($items));

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes  = ds_text_shape(2, 'Title', 400000, 200000, 11392000, 700000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
        $shapes .= ds_text_shape(3, 'Sub', 400000, 950000, 11392000, 400000, $sub_p, 'l', 't');
    }
    $id = 4;

    $content_top = 1650000;
    $content_h   = 6858000 - $content_top - 300000;
    $base_y      = 6858000 - 300000;
    $shapes .= ds_filled_rect($id++, 'Base', 400000, $base_y - 7500, 11392000, 15000, $ac, 30000);

    $bar_w = min(1450000, (int) round(6912000 / $count));
    $gap   = $count > 1 ? (int) round((10992000 - $bar_w * $count) / ($count - 1)) : 0;
    $max_h = (int) round($content_h * 0.58);
    $min_h = (int) round($content_h * 0.16);

    for ($i = 0; $i < $count; $i++) {
        $bx = 800000 + $i * ($bar_w + $gap);
        $bh = $count === 1 ? $max_h : $min_h + (int) round($i * (($max_h - $min_h) / ($count - 1)));
        $by = $base_y - $bh;

        $item  = (string)($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';

        $shapes .= ds_rounded_rect($id++, 'Bar'.$i, $bx, $by, $bar_w, $bh, $ac, 22000, 4000);

        $bcx = $bx + (int) round($bar_w / 2);
        $badge_sz = 420000;
        $shapes .= ds_ellipse($id++, 'Badge'.$i, $bcx - (int)($badge_sz/2), $by - (int) round($badge_sz * 0.6), $badge_sz, $badge_sz, $ac);
        $n_p = '<a:p><a:pPr algn="ctr"/>' . ds_run(str_pad((string)($i+1), 2, '0', STR_PAD_LEFT), 1300, 'FFFFFF', true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Num'.$i, $bcx - (int)($badge_sz/2), $by - (int) round($badge_sz * 0.6), $badge_sz, $badge_sz, $n_p, 'ctr', 'ctr');

        $label_w = min(1900000, $bar_w + $gap + 400000);
        $label_x = $bcx - (int) round($label_w / 2);
        $label_y = $by - (int) round($badge_sz * 0.6) - 760000;
        if ($label_y < $content_top) { $label_y = $content_top; }
        if ($step) {
            $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($step, 1200, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Step'.$i, $label_x, $label_y, $label_w, 320000, $s_p, 'ctr', 't');
        }
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 900, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $label_x, $label_y + 320000, $label_w, 400000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

// ─── QUOTE variants ──────────────────────────────────────────────────────────

if ( ! function_exists('ds_pptv2_quote_v2') ) {
// v2: Quote on left, image on right
function ds_pptv2_quote_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $quote = $slide['title'] ?? '';
    $attr  = isset($slide['content'][0]) ? $slide['content'][0] : '';
    $half  = (int) (12192000 * (100 - DS_PPT_IMG_PCT) / 100);
    $shapes = '';
    $mark_p = '<a:p><a:pPr algn="l"/>' . ds_run('"', 14000, $ac, true) . '</a:p>';
    $shapes .= ds_text_shape(2, 'Mark', 500000, 600000, 1200000, 1200000, $mark_p, 'l', 'ctr');
    $q_p = '<a:p><a:pPr algn="l"/>' . ds_run($quote, 2800, $tc, false, true) . '</a:p>';
    $shapes .= ds_text_shape(3, 'Quote', 500000, 1800000, $half - 800000, 3000000, $q_p, 'l', 'ctr');
    if ($attr) {
        $a_p = '<a:p><a:pPr algn="l"/>' . ds_run(DS_PPT_EMDASH . ' ' . $attr, 1700, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape(4, 'Attr', 500000, 5200000, $half - 800000, 600000, $a_p, 'l', 't');
    }
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(5, 'QuoteImg', $half, 0, 12192000-$half, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect(5, 'ImgPH', $half, 0, 12192000-$half, 6858000, $ac, 15000, 3000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_quote_v3') ) {
// v3: Image on left, quote on right
function ds_pptv2_quote_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $quote = $slide['title'] ?? '';
    $attr  = isset($slide['content'][0]) ? $slide['content'][0] : '';
    $imgW  = (int) (12192000 * DS_PPT_IMG_PCT / 100);
    $textX = $imgW + 200000;
    $textW = 12192000 - $textX - 300000;
    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'QuoteImg', 0, 0, $imgW, 6858000, $img_rid);
    } else {
        $shapes .= ds_rounded_rect(2, 'ImgPH', 0, 0, $imgW, 6858000, $ac, 15000, 3000);
    }
    $mark_p = '<a:p><a:pPr algn="l"/>' . ds_run('"', 14000, $ac, true) . '</a:p>';
    $shapes .= ds_text_shape(3, 'Mark', $textX, 600000, 1200000, 1200000, $mark_p, 'l', 'ctr');
    $q_p = '<a:p><a:pPr algn="l"/>' . ds_run($quote, 2600, $tc, false, true) . '</a:p>';
    $shapes .= ds_text_shape(4, 'Quote', $textX, 1800000, $textW, 3200000, $q_p, 'l', 'ctr');
    if ($attr) {
        $a_p = '<a:p><a:pPr algn="l"/>' . ds_run(DS_PPT_EMDASH . ' ' . $attr, 1700, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape(5, 'Attr', $textX, 5200000, $textW, 600000, $a_p, 'l', 't');
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_quote_v4') ) {
// v4: full-bleed background image + theme-coloured overlay, quote centred
function ds_pptv2_quote_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $bg = 'FFFFFF', $bg2 = null) {
    $quote = $slide['title'] ?? '';
    $attr  = isset($slide['content'][0]) ? $slide['content'][0] : '';
    $W = 12192000; $H = 6858000;
    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'QuoteBG', 0, 0, $W, $H, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    }
    $shapes .= ds_filled_rect(3, 'Overlay', 0, 0, $W, $H, $bg, (int) (DS_PPT_OVERLAY_PCT * 1000));
    $mark_p = '<a:p><a:pPr algn="ctr"/>' . ds_run('"', 14000, $ac, true) . '</a:p>';
    $shapes .= ds_text_shape(4, 'Mark', 0, 900000, $W, 1200000, $mark_p, 'ctr', 'ctr');
    $q_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($quote, 2800, $tc, false, true) . '</a:p>';
    $shapes .= ds_text_shape(5, 'Quote', 1200000, 2200000, $W - 2400000, 2800000, $q_p, 'ctr', 'ctr');
    if ($attr) {
        $a_p = '<a:p><a:pPr algn="ctr"/>' . ds_run(DS_PPT_EMDASH . ' ' . $attr, 1700, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape(6, 'Attr', 1200000, 5300000, $W - 2400000, 600000, $a_p, 'ctr', 't');
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_quote_v5') ) {
// v5: quote on top (60%), full-bleed image strip along the bottom (40%)
function ds_pptv2_quote_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $quote = $slide['title'] ?? '';
    $attr  = isset($slide['content'][0]) ? $slide['content'][0] : '';
    $W = 12192000; $H = 6858000;
    $textH = (int) ($H * 0.60);
    $shapes = '';
    $mark_p = '<a:p><a:pPr algn="ctr"/>' . ds_run('"', 11000, $ac, true) . '</a:p>';
    $shapes .= ds_text_shape(2, 'Mark', 0, 350000, $W, 900000, $mark_p, 'ctr', 'ctr');
    $q_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($quote, 2400, $tc, false, true) . '</a:p>';
    $shapes .= ds_text_shape(3, 'Quote', 1200000, 1250000, $W - 2400000, 1900000, $q_p, 'ctr', 'ctr');
    if ($attr) {
        $a_p = '<a:p><a:pPr algn="ctr"/>' . ds_run(DS_PPT_EMDASH . ' ' . $attr, 1500, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape(4, 'Attr', 1200000, 3250000, $W - 2400000, 500000, $a_p, 'ctr', 't');
    }
    $shapes .= ds_filled_rect(5, 'AccentBar', 0, $textH - 80000, $W, 80000, $ac);
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(6, 'BottomImg', 0, $textH, $W, $H - $textH, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect(6, 'ImgPH', 0, $textH, $W, $H - $textH, $ac, 15000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_stats_body') ) {
// Shared: title + 3 big stats laid across a given vertical band.
function ds_pptv2_stats_body($slide, $tc, $bc, $ac, &$id, $top, $band_h) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($items) < 3) $items[] = '';
    $shapes = '';
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 400000, $top + 150000, 11392000, 780000, $t_p, 'l', 't');
    $col_w   = 3600000;
    $start_x = 500000;
    $stat_y  = $top + (int) ($band_h * 0.34);
    $label_y = $top + (int) ($band_h * 0.62);
    $__ce_nl = 0; foreach ( $items as $__it ) { $__p = explode(':', (string) $__it, 2); $__l = function_exists('mb_strlen') ? mb_strlen(trim($__p[0])) : strlen(trim($__p[0])); if ( $__l > $__ce_nl ) $__ce_nl = $__l; }   /* DS Classic Editor: same size for every number */
    for ($i = 0; $i < 3; $i++) {
        $cx    = $start_x + $i * $col_w;
        $parts = explode(':', (string) $items[$i], 2);
        $num   = trim($parts[0]);
        $lbl   = isset($parts[1]) ? trim($parts[1]) : '';
        $nl    = $__ce_nl;
        $sz    = $nl <= 3 ? 6000 : ($nl <= 4 ? 5000 : ($nl <= 5 ? 4000 : ($nl <= 7 ? 3200 : 2800)));
        $n_p   = '<a:p><a:pPr algn="ctr"/>' . ds_run($num, $sz, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Stat'.$i, $cx, $stat_y, $col_w, 1200000, $n_p, 'ctr', 'ctr');
        if ($lbl) {
            $l_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($lbl, 1400, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Lbl'.$i, $cx + 100000, $label_y, $col_w - 200000, 900000, $l_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_stats_v3') ) {
// v3: full-bleed background image + theme-coloured overlay, stats on top
function ds_pptv2_stats_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $bg = 'FFFFFF', $bg2 = null) {
    $W = 12192000; $H = 6858000;
    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'StatsBG', 0, 0, $W, $H, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    }
    $shapes .= ds_filled_rect(3, 'Overlay', 0, 0, $W, $H, $bg, (int) (DS_PPT_OVERLAY_PCT * 1000));
    $shapes .= ds_filled_rect(4, 'AccentBar', 0, 0, $W, 60000, $ac);
    $id = 5;
    $shapes .= ds_pptv2_stats_body($slide, $tc, $bc, $ac, $id, 0, $H);
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_stats_v5') ) {
// v5: stats on top (60%), full-bleed image strip along the bottom (40%)
function ds_pptv2_stats_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $W = 12192000; $H = 6858000;
    $textH = (int) ($H * 0.60);
    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, $W, 60000, $ac);
    $id = 3;
    $shapes .= ds_pptv2_stats_body($slide, $tc, $bc, $ac, $id, 0, $textH);
    $shapes .= ds_filled_rect($id++, 'Divider', 0, $textH - 80000, $W, 80000, $ac);
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BottomImg', 0, $textH, $W, $H - $textH, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect($id, 'ImgPH', 0, $textH, $W, $H - $textH, $ac, 15000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_body') ) {
// Shared: title + vertical dotted timeline inside a given vertical band.
function ds_pptv2_timeline_body($slide, $tc, $bc, $ac, &$id, $top, $band_h, $left = 500000, $width = 11192000) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 5);
    $count = max(1, count($items));
    $shapes = '';
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $left, $top + 150000, $width, 780000, $t_p, 'l', 't');
    $list_top = $top + 1100000;
    $avail    = max(400000, $band_h - 1400000);
    $row_h    = (int) ($avail / $count);
    $dot      = 200000;
    for ($i = 0; $i < $count; $i++) {
        $y     = $list_top + $i * $row_h;
        $parts = explode(':', (string) $items[$i], 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';
        $shapes .= ds_ellipse($id++, 'Dot'.$i, $left + 120000, $y + 90000, $dot, $dot, $ac);
        if ($i < $count - 1) {
            $shapes .= ds_filled_rect($id++, 'Rail'.$i, $left + 120000 + (int)($dot/2) - 15000, $y + 90000 + $dot, 30000, max(0, $row_h - $dot - 90000), $ac, 30000);
        }
        $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($step, 1700, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Step'.$i, $left + 500000, $y, max(600000, $width - 500000), 420000, $s_p, 'l', 't');
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1250, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $left + 500000, $y + 400000, max(600000, $width - 500000), max(300000, $row_h - 420000), $d_p, 'l', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v8') ) {
// v8: full-bleed background image + theme-coloured overlay, timeline on top
function ds_pptv2_timeline_v8($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $bg = 'FFFFFF', $bg2 = null) {
    $W = 12192000; $H = 6858000;
    $shapes = '';
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover(2, 'TLBG', 0, 0, $W, $H, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    }
    $shapes .= ds_filled_rect(3, 'Overlay', 0, 0, $W, $H, $bg, (int) (DS_PPT_OVERLAY_PCT * 1000));
    $shapes .= ds_filled_rect(4, 'AccentBar', 0, 0, $W, 60000, $ac);
    $id = 5;
    $shapes .= ds_pptv2_timeline_body($slide, $tc, $bc, $ac, $id, 0, $H);
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v9') ) {
// v9: vertical timeline on the left, full-bleed image on the opposite side
function ds_pptv2_timeline_v9($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $W = 12192000; $H = 6858000;
    $textW = (int) ($W * (100 - DS_PPT_IMG_PCT) / 100);
    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, $W, 60000, $ac);
    $id = 3;
    $shapes .= ds_pptv2_timeline_body($slide, $tc, $bc, $ac, $id, 0, $H, 400000, max(1200000, $textW - 700000));
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'SideImg', $textW, 0, $W - $textW, $H, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect($id, 'ImgPH', $textW, 0, $W - $textW, $H, $ac, 15000);
    }
    return $shapes;
}
}

if ( ! function_exists('ds_pptv2_timeline_v10') ) {
// v10: horizontal timeline on top (60%), full-bleed image strip along the bottom (40%)
function ds_pptv2_timeline_v10($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $W = 12192000; $H = 6858000;
    $textH = (int) ($H * 0.60);
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 4);
    $count = max(1, count($items));
    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, $W, 60000, $ac);
    $id = 3;
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 500000, 200000, 11192000, 780000, $t_p, 'l', 't');
    $line_y  = (int) ($textH * 0.62);
    $line_x1 = 900000;
    $line_w  = $W - 1800000;
    $shapes .= ds_filled_rect($id++, 'TL', $line_x1, $line_y, $line_w, 30000, $ac, 40000);
    $dot = 220000;
    for ($i = 0; $i < $count; $i++) {
        $t  = $count === 1 ? 0.5 : $i / ($count - 1);
        $cx = $line_x1 + (int) ($t * $line_w);
        $shapes .= ds_ellipse($id++, 'Dot'.$i, $cx - (int)($dot/2), $line_y + 15000 - (int)($dot/2), $dot, $dot, $ac);
        $parts = explode(':', (string) $items[$i], 2);
        $step  = trim($parts[0]);
        $desc  = isset($parts[1]) ? trim($parts[1]) : '';
        $box_w = (int) ($line_w / $count);
        $box_x = $cx - (int) ($box_w / 2);
        $s_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($step, 1600, $ac, true) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Step'.$i, $box_x, $line_y - 700000, $box_w, 500000, $s_p, 'ctr', 'b');
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($desc, 1150, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $box_x, $line_y + 320000, $box_w, max(300000, $textH - $line_y - 400000), $d_p, 'ctr', 't');
        }
    }
    $shapes .= ds_filled_rect($id++, 'Divider', 0, $textH - 80000, $W, 80000, $ac);
    if ($img_rid) {
        $shapes .= ds_pptv2_img_cover($id, 'BottomImg', 0, $textH, $W, $H - $textH, $img_rid, $slide['image_position']['x'] ?? 50, $slide['image_position']['y'] ?? 50);
    } else {
        $shapes .= ds_filled_rect($id, 'ImgPH', 0, $textH, $W, $H - $textH, $ac, 15000);
    }
    return $shapes;
}
}

// ─── IMAGE_TEXT variants ─────────────────────────────────────────────────────

if ( ! function_exists('ds_pptv2_image_text_v2') ) {
// v2: Image on left (flipped from default which has image on right)
function ds_pptv2_image_text_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    $title   = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content = array_slice($slide['content'] ?? array(), 0, 4);
    $symbols = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $defs    = array('check','star','trending','target');

    $id = 2; $imgX = 0; $imgY = 0;
    $imgCX = 5120640; $imgCY = 6858000;

    if ($img_rid) {
        $shapes = ds_pptv2_img_cover($id++, 'AIImg', $imgX, $imgY, $imgCX, $imgCY, $img_rid);
    } else {
        $shapes = ds_rounded_rect($id++, 'ImgPH', $imgX, $imgY, $imgCX, $imgCY, $ac, 20000);
    }
    $textX = 5120640 + 200000;
    $textW = 12192000 - $textX - 300000;
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $textX, 200000, $textW, 780000, $t_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $textX, 1020000, $textW, 480000, $sub_p, 'l', 't');
    }
    $it_y = 1900000; $it_row_h = (int) min( 1600000, 4500000 / max( 1, count( $content ) ) ); $it_icsz = 520000; $it_icx = $textX;
    $it_txtx = $it_icx + $it_icsz + 240000; $it_txtw = $textW - $it_icsz - 300000;
    foreach ( $content as $ii => $b ) {
        $b     = (string) $b;
        $colon = strpos( $b, ':' );
        $bh    = $colon !== false ? trim( substr( $b, 0, $colon ) )   : '';
        $bd    = $colon !== false ? trim( substr( $b, $colon + 1 ) )  : trim( $b );
        $by    = $it_y + $ii * $it_row_h;

        /* Card: rounded panel behind the whole item. */
        $cardX = $it_icx - 150000;
        $cardW = ( $it_txtx + $it_txtw ) - $cardX;
        $cardH = $it_row_h - 160000;
        $shapes .= ds_rounded_rect( $id++, 'Card'.$ii, $cardX, $by, $cardW, $cardH, $ac, 10000, 3500 );

        /* Icon badge on the LEFT, heading beside it. */
        $icsz = 300000;
        $icx  = $cardX + 150000;
        $icy  = $by + 80000;
        $shapes .= ds_ellipse( $id++, 'IcBg'.$ii, $icx, $icy, $icsz, $icsz, $ac );
        if ( isset( $icon_rids[ $ii ] ) && $icon_rids[ $ii ] ) {
            $pad = (int) ( $icsz * 0.26 );
            $shapes .= _ds_pptv2_icon_img( $id++, 'IIc'.$ii, $icx + $pad, $icy + $pad, $icsz - $pad * 2, $icon_rids[ $ii ] );
        }

        $hx = $icx + $icsz + 170000;
        $hw = ( $cardX + $cardW - 150000 ) - $hx;
        if ( $bh ) {
            $h_p = '<a:p><a:pPr algn="l"/>' . ds_run( $bh, 1700, $tc, true ) . '</a:p>';
            $shapes .= ds_text_shape( $id++, 'Hd'.$ii, $hx, $icy, $hw, $icsz, $h_p, 'l', 'ctr' );
        }

        /* Hairline divider under the icon + heading row. */
        $ruleY = $icy + $icsz + 80000;
        $shapes .= ds_filled_rect( $id++, 'Rule'.$ii, $icx, $ruleY, $cardW - 300000, 9000, $ac );

        /* Body copy, left aligned, below the divider. */
        if ( $bd ) {
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run( $bd, 1380, $bc ) . '</a:p>';
            $shapes .= ds_text_shape( $id++, 'Txt'.$ii, $icx, $ruleY + 110000, $cardW - 300000, max( 220000, $cardH - ( $ruleY - $by ) - 150000 ), $d_p, 'l', 't' );
        }
    }
    return $shapes;
}
}
// ─── five_point / six_point / seven_point PPTX Renderers ─────────────────────
// v1 = no image (full width grid)
// v2 = image right
// v3 = image left

if ( ! function_exists('_ds_pptv2_truncate_desc') ) {
	function _ds_pptv2_truncate_desc($text, $max_chars) {
		$text = trim((string)$text);
		$len = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
		if ($len <= $max_chars) return $text;
		$cut = function_exists('mb_substr') ? mb_substr($text, 0, $max_chars) : substr($text, 0, $max_chars);
		$last_space = strrpos($cut, ' ');
		if ($last_space !== false && $last_space > $max_chars * 0.6) {
			$cut = substr($cut, 0, $last_space);
		}
		return rtrim($cut, " ,.;:-") . "\xE2\x80\xA6";
	}
}

if ( ! function_exists('_ds_pptv2_multipoint_grid') ) {
function _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $img_side, $item_count) {
    $defaults = array('lightbulb','chart','users','target','rocket','star','award','flag');
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();

    $title    = $slide['title']    ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, $item_count);
    while (count($content) < $item_count) $content[] = '';

    // Row layout: 5→3+2, 6→3+3, 7→4+3, 8→4+4
    $rows     = 2;
    $top_cols = ($item_count === 7 || $item_count === 8) ? 4 : 3;
    $bot_cols = ($item_count === 5) ? 2 : (($item_count === 8) ? 4 : 3);

    $sld_cx = 12192000;
    $sld_cy = 6858000;
    $imgW   = 3200000;
    $margin = 380000;
    $gap2img = 280000;

    if ($img_side === 'none') {
        $gridX = $margin;
        $gridW = $sld_cx - 2 * $margin;
        $imgX  = null;
    } elseif ($img_side === 'right') {
        $imgX  = $sld_cx - $imgW;
        $gridX = $margin;
        $gridW = $sld_cx - $imgW - $margin - $gap2img;
    } else {
        $imgX  = 0;
        $gridX = $imgW + $gap2img;
        $gridW = $sld_cx - $imgW - $margin - $gap2img;
    }

    $id     = 2;
    $shapes = ds_filled_rect($id++, 'AccentBar', 0, 0, $sld_cx, 60000, $ac);

    $title_x = ($img_side === 'left') ? ($imgW + $gap2img) : $margin;
    $title_w  = $sld_cx - $title_x - 200000;

    $title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2800, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $title_x, 180000, $title_w, 700000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $title_x, 900000, $title_w, 380000, $sub_p, 'l', 't');
    }

    $card_y_start = 1480000;
    $grid_h       = $sld_cy - $card_y_start - 250000;
    $row_gap      = 180000;
    $col_gap      = 180000;
    $card_h       = (int) round(($grid_h - ($rows - 1) * $row_gap) / $rows);
    $row_configs  = array($top_cols, $bot_cols);
    $global_i     = 0;

    for ($r = 0; $r < $rows; $r++) {
        $cols   = $row_configs[$r];
        $card_w = (int) round(($gridW - ($cols - 1) * $col_gap) / $cols);
        $cy     = (int) round($card_y_start + $r * ($card_h + $row_gap));
        $row_total_w = $cols * $card_w + ($cols - 1) * $col_gap;
        $row_offset  = ($gridW - $row_total_w) / 2;

        for ($c = 0; $c < $cols; $c++) {
            if ($global_i >= $item_count) break;
            $cx   = (int) round($gridX + $row_offset + $c * ($card_w + $col_gap));
            $item = (string)$content[$global_i];
            $colon   = strpos($item, ':');
            $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
            $desc    = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);
            $desc_w  = (int) $card_w - 380000;
            $desc_h  = max(150000, (int) $card_h - 1360000);
            $d_cpl   = max(10, (int) floor($desc_w / 63500));
            $d_maxch = $d_cpl * max(1, (int) floor($desc_h / 165100));
            $desc = _ds_pptv2_truncate_desc($desc, $d_maxch);

            $shapes .= ds_rounded_rect($id++, 'Card'.$global_i, $cx, $cy, $card_w, $card_h, $ac, 10000, 3500);

            $tile_sz = 450000;
            $tile_x  = $cx + 200000;
            $tile_y  = $cy + 170000;

            if ( isset($icon_rids[$global_i]) && $icon_rids[$global_i] ) {
                $shapes .= ds_ellipse($id++, 'IBg'.$global_i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                $pad = 110000;
                $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$global_i, $tile_x + $pad, $tile_y + $pad, $tile_sz - $pad * 2, $icon_rids[$global_i]);
            } else {
                $icon_n = $defaults[$global_i % count($defaults)];
                if ( isset($symbols[$icon_n]) ) {
                    $shapes .= ds_ellipse($id++, 'IBg'.$global_i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
                    $sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2300, 'FFFFFF', true, false, 'Arial') . '</a:p>';
                    $shapes .= ds_text_shape($id++, 'ISy'.$global_i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
                }
            }

            if ($heading) {
                $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, 1400, $tc, true) . '</a:p>';
                $shapes .= ds_text_shape($id++, 'CH'.$global_i, $cx + 200000, $tile_y + $tile_sz + 100000, $card_w - 380000, 380000, $h_p, 'l', 't');
            }

            $rule_y = $tile_y + $tile_sz + 620000;
            $shapes .= ds_filled_rect($id++, 'Rule'.$global_i, $cx + 200000, $rule_y, 500000, 35000, $ac);

            if ($desc) {
                $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1000, $bc) . '</a:p>';
                $shapes .= ds_text_shape($id++, 'CD'.$global_i, $cx + 200000, $rule_y + 80000, $card_w - 380000, $card_h - ($rule_y - $cy) - 120000, $d_p, 'l', 't');
            }

            $global_i++;
        }
    }

    if ($img_side !== 'none') {
        if ($img_rid) {
            $shapes .= ds_pptv2_img_cover($id, 'GridImg', $imgX, 0, $imgW, $sld_cy, $img_rid);
        } else {
            $shapes .= ds_rounded_rect($id++, 'ImgPH', $imgX, 0, $imgW, $sld_cy, $ac, 15000, 3000);
        }
    }

    return $shapes;
}
}

// five_point
if ( ! function_exists('ds_pptv2_five_point_v1') ) {
function ds_pptv2_five_point_v1($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 5);
}
}
if ( ! function_exists('ds_pptv2_five_point_v2') ) {
function ds_pptv2_five_point_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 5);
}
}
if ( ! function_exists('ds_pptv2_five_point_v3') ) {
function ds_pptv2_five_point_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 5);
}
}

// six_point
if ( ! function_exists('ds_pptv2_six_point_v1') ) {
function ds_pptv2_six_point_v1($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 6);
}
}
if ( ! function_exists('ds_pptv2_six_point_v2') ) {
function ds_pptv2_six_point_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 6);
}
}
if ( ! function_exists('ds_pptv2_six_point_v3') ) {
function ds_pptv2_six_point_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 6);
}
}

// seven_point
if ( ! function_exists('ds_pptv2_seven_point_v1') ) {
function ds_pptv2_seven_point_v1($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 7);
}
}
if ( ! function_exists('ds_pptv2_seven_point_v2') ) {
function ds_pptv2_seven_point_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 7);
}
}
if ( ! function_exists('ds_pptv2_seven_point_v3') ) {
function ds_pptv2_seven_point_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 7);
}
}

// eight_point
if ( ! function_exists('ds_pptv2_eight_point_v1') ) {
function ds_pptv2_eight_point_v1($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 8);
}
}
if ( ! function_exists('ds_pptv2_eight_point_v2') ) {
function ds_pptv2_eight_point_v2($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 8);
}
}
if ( ! function_exists('ds_pptv2_eight_point_v3') ) {
function ds_pptv2_eight_point_v3($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_multipoint_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'left', 8);
}
}

// v4 = long cards, no image (2-per-row, icon+header left ~30% / divider / description right ~70%)
// v5 = long cards + image right

if ( ! function_exists('_ds_pptv2_longcard_grid') ) {
function _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $img_side, $item_count) {
$defaults = array('lightbulb','chart','users','target','rocket','star','award','flag');
$symbols = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();

$title = $slide['title'] ?? '';
$subtitle = $slide['subtitle'] ?? '';
$content = array_slice($slide['content'] ?? array(), 0, $item_count);
while (count($content) < $item_count) $content[] = '';

$sld_cx = 12192000;
$sld_cy = 6858000;
$imgW = 3200000;
$margin = 380000;
$gap2img = 280000;

if ($img_side === 'right') {
$imgX = $sld_cx - $imgW;
$gridX = $margin;
$gridW = $sld_cx - $imgW - $margin - $gap2img;
} else {
$imgX = null;
$gridX = $margin;
$gridW = $sld_cx - 2 * $margin;
}

$id = 2;
$shapes = ds_filled_rect($id++, 'AccentBar', 0, 0, $sld_cx, 60000, $ac);

$title_x = $margin;
$title_w = $sld_cx - $title_x - 200000;

$title_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 2800, $tc, true) . '</a:p>';
$shapes .= ds_text_shape($id++, 'Title', $title_x, 180000, $title_w, 700000, $title_p, 'l', 't');
if ($subtitle) {
$sub_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1500, $ac) . '</a:p>';
$shapes .= ds_text_shape($id++, 'Sub', $title_x, 900000, $title_w, 380000, $sub_p, 'l', 't');
}

$cols = 2;
$rows = (int) ceil($item_count / $cols);
$card_y_start = 1480000;
$grid_h = $sld_cy - $card_y_start - 250000;
$row_gap = 160000;
$col_gap = 220000;
$card_w = (int) round(($gridW - ($cols - 1) * $col_gap) / $cols);
$card_h = (int) round(($grid_h - ($rows - 1) * $row_gap) / $rows);
$left_w = (int) round($card_w * 0.30);

$global_i = 0;
for ($r = 0; $r < $rows; $r++) {
$remaining = $item_count - $global_i;
$cols_this_row = min($cols, $remaining);
$row_total_w = $cols_this_row * $card_w + ($cols_this_row - 1) * $col_gap;
$row_offset = ($gridW - $row_total_w) / 2;
$cy = (int) round($card_y_start + $r * ($card_h + $row_gap));

for ($c = 0; $c < $cols_this_row; $c++) {
$cx = (int) round($gridX + $row_offset + $c * ($card_w + $col_gap));
$item = (string)$content[$global_i];
$colon = strpos($item, ':');
$heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
$desc = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);
				$desc = _ds_pptv2_truncate_desc($desc, 170);

$shapes .= ds_rounded_rect($id++, 'Card'.$global_i, $cx, $cy, $card_w, $card_h, $ac, 10000, 3500);

$pad = (int) round($card_h * 0.12);
$tile_sz = (int) min(560000, $card_h * 0.38, $left_w - 2 * $pad);
if ($tile_sz < 260000) $tile_sz = 260000;
$tile_x = $cx + $pad;
$tile_y = $cy + $pad;

if ( isset($icon_rids[$global_i]) && $icon_rids[$global_i] ) {
$shapes .= ds_ellipse($id++, 'IBg'.$global_i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
$ipad = (int) round($tile_sz * 0.24);
$shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$global_i, $tile_x + $ipad, $tile_y + $ipad, $tile_sz - $ipad * 2, $icon_rids[$global_i]);
} else {
$icon_n = $defaults[$global_i % count($defaults)];
if ( isset($symbols[$icon_n]) ) {
$shapes .= ds_ellipse($id++, 'IBg'.$global_i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
$sym_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($symbols[$icon_n], 2000, 'FFFFFF', true, false, 'Arial') . '</a:p>';
$shapes .= ds_text_shape($id++, 'ISy'.$global_i, $tile_x, $tile_y, $tile_sz, $tile_sz, $sym_p, 'ctr', 'ctr');
}
}

$head_gap = (int) round($card_h * 0.05);
$head_y = $tile_y + $tile_sz + $head_gap;
$head_h = ($cy + $card_h) - $head_y - $pad;
if ($head_h < 180000) $head_h = 180000;
if ($heading) {
$h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, 1300, $tc, true) . '</a:p>';
$shapes .= ds_text_shape($id++, 'CH'.$global_i, $cx + $pad, $head_y, $left_w - $pad, $head_h, $h_p, 'l', 't');
}

$div_x = $cx + $left_w;
$div_y = $cy + (int) round($card_h * 0.14);
$div_h = (int) round($card_h * 0.72);
$shapes .= ds_filled_rect($id++, 'Div'.$global_i, $div_x, $div_y, 22000, $div_h, $ac, 55000);

$right_x = $div_x + 200000;
$right_w = ($cx + $card_w) - $right_x - $pad;
if ($right_w < 200000) $right_w = 200000;
if ($desc) {
$d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1050, $bc) . '</a:p>';
$shapes .= ds_text_shape($id++, 'CD'.$global_i, $right_x, $cy + $pad, $right_w, $card_h - 2 * $pad, $d_p, 'l', 'ctr');
}

$global_i++;
}
}

if ($img_side === 'right') {
if ($img_rid) {
$shapes .= ds_pptv2_img_cover($id, 'GridImg', $imgX, 0, $imgW, $sld_cy, $img_rid);
} else {
$shapes .= ds_rounded_rect($id++, 'ImgPH', $imgX, 0, $imgW, $sld_cy, $ac, 15000, 3000);
}
}

return $shapes;
}
}
// five_point long-card variants
if ( ! function_exists('ds_pptv2_five_point_v4') ) {
function ds_pptv2_five_point_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 5);
}
}
if ( ! function_exists('ds_pptv2_five_point_v5') ) {
function ds_pptv2_five_point_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 5);
}
}
// six_point long-card variants
if ( ! function_exists('ds_pptv2_six_point_v4') ) {
function ds_pptv2_six_point_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 6);
}
}
if ( ! function_exists('ds_pptv2_six_point_v5') ) {
function ds_pptv2_six_point_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 6);
}
}
// seven_point long-card variants
if ( ! function_exists('ds_pptv2_seven_point_v4') ) {
function ds_pptv2_seven_point_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 7);
}
}
if ( ! function_exists('ds_pptv2_seven_point_v5') ) {
function ds_pptv2_seven_point_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 7);
}
}
// eight_point long-card variants
if ( ! function_exists('ds_pptv2_eight_point_v4') ) {
function ds_pptv2_eight_point_v4($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, null, $icon_rids, 'none', 8);
}
}
if ( ! function_exists('ds_pptv2_eight_point_v5') ) {
function ds_pptv2_eight_point_v5($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
return _ds_pptv2_longcard_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 'right', 8);
}
}


/* ---- Fused card layout: default (v1) 3-column and 4-column renderers ----
   Draws the same card the browser preview draws: icon badge on the LEFT with
   the heading beside it, hairline rule, then left-aligned body copy.
   3 points => 3 across.  4 points => 2 x 2.  No emoji fallback. ---- */
if ( ! function_exists('_ds_pptv2_plain_grid') ) {
function _ds_pptv2_plain_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, $count, $cols) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, $count);
    while (count($content) < $count) $content[] = '';
    $rows = (int) ceil($count / $cols);

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    $mx = 400000; $gw = 11392000;
    $t_p = '<a:p><a:pPr algn="l"/>' . ds_run($title, 3200, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', $mx, 200000, $gw, 700000, $t_p, 'l', 't');
    $grid_y = 1550000;
    if ($subtitle) {
        $s_p = '<a:p><a:pPr algn="l"/>' . ds_run($subtitle, 1700, $ac) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', $mx, 950000, $gw, 420000, $s_p, 'l', 't');
        $grid_y = 1700000;
    }

    $gap    = 200000;
    $card_w = (int) round(($gw - ($cols - 1) * $gap) / $cols);
    $card_h = (int) round((6858000 - $grid_y - 400000 - ($rows - 1) * $gap) / $rows);

    $cpad    = 260000;
    $tile_sz = ($rows > 1) ? 440000 : 520000;
    $h_sz    = ($cols >= 4) ? 1400 : (($cols == 3) ? 1600 : 1800);

    for ($i = 0; $i < $count; $i++) {
        $col = $i % $cols; $row = (int) ($i / $cols);
        $cx = (int) round($mx + $col * ($card_w + $gap));
        $cy = (int) round($grid_y + $row * ($card_h + $gap));
        $item = (string) $content[$i];
        $colon   = strpos($item, ':');
        $heading = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $desc    = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $cy, (int)$card_w, (int)$card_h, $ac, 10000, 3500);

        $tile_x = $cx + $cpad;
        $tile_y = $cy + $cpad;
        if ( isset($icon_rids[$i]) && $icon_rids[$i] ) {
            $shapes .= ds_ellipse($id++, 'IBg'.$i, $tile_x, $tile_y, $tile_sz, $tile_sz, $ac);
            $pad = (int) ($tile_sz * 0.26);
            $shapes .= _ds_pptv2_icon_img($id++, 'IIc'.$i, $tile_x + $pad, $tile_y + $pad, $tile_sz - $pad * 2, $icon_rids[$i]);
        }
        if ($heading) {
            $hx  = $tile_x + $tile_sz + 170000;
            $hw  = max(300000, ($cx + (int)$card_w - $cpad) - $hx);
            $h_p = '<a:p><a:pPr algn="l"/>' . ds_run($heading, $h_sz, $tc, true) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CH'.$i, $hx, $tile_y, $hw, $tile_sz, $h_p, 'l', 'ctr');
        }
        $ruleY = $tile_y + $tile_sz + 150000;
        $shapes .= ds_filled_rect($id++, 'CRule'.$i, $cx + $cpad, $ruleY, (int)$card_w - $cpad * 2, 12700, $ac);
        if ($desc) {
            $d_y = $ruleY + 170000;
            $d_h = max(300000, ($cy + (int)$card_h - $cpad) - $d_y);
            $d_p = '<a:p><a:pPr algn="l"/>' . ds_run($desc, 1300, $bc) . '</a:p>';
            $shapes .= ds_text_shape($id++, 'CD'.$i, $cx + $cpad, $d_y, (int)$card_w - $cpad * 2, $d_h, $d_p, 'l', 't');
        }
    }
    return $shapes;
}
}
if ( ! function_exists('ds_pptv2_three_column_v1') ) {
function ds_pptv2_three_column_v1($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_plain_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 3, 3);
}
}
if ( ! function_exists('ds_pptv2_feature_grid_v1') ) {
function ds_pptv2_feature_grid_v1($slide, $tc, $bc, $ac, $img_rid, $icon_rids) {
    return _ds_pptv2_plain_grid($slide, $tc, $bc, $ac, $img_rid, $icon_rids, 4, 2);
}
}

/* ================================================================
   DS CLASSIC EDITOR — PowerPoint side (added in this clone)
   - many_point: 9-14 point layouts (v1 grid, v2 grid + image right,
     v3 two-column list, v4 image left + list)
   - Key takeaway strip under point slides
   - Company logo in a corner of every slide
   ================================================================ */
if ( ! function_exists('ds_ce_point_types') ) {
function ds_ce_point_types() {
    return array('bullet','two_column','three_column','feature_grid','five_point','six_point','seven_point','eight_point','many_point');
}
}
if ( ! function_exists('ds_ce_norm_type') ) {
function ds_ce_norm_type( $type, $slide ) {
    $n = isset($slide['content']) && is_array($slide['content']) ? count($slide['content']) : 0;
    if ( $n >= 9 && in_array($type, ds_ce_point_types(), true) ) return 'many_point';
    return $type;
}
}
if ( ! function_exists('_ds_ce_split') ) {
function _ds_ce_split( $item ) {
    $item = (string) $item; $c = strpos($item, ':');
    if ( $c !== false && $c > 0 && $c < 80 ) return array(trim(substr($item, 0, $c)), trim(substr($item, $c + 1)));
    return array('', trim($item));
}
}
if ( ! function_exists('_ds_ce_fit') ) {
/* trim text so it fits a box: chars per line x lines for a font size (1/100 pt) */
function _ds_ce_fit( $text, $w, $h, $sz ) {
    $cpl   = max(8, (int) floor($w / ($sz * 0.55 * 127)));
    $lines = max(1, (int) floor($h / ($sz * 1.28 * 127)));
    return function_exists('_ds_pptv2_truncate_desc') ? _ds_pptv2_truncate_desc($text, $cpl * $lines) : $text;
}
}
if ( ! function_exists('_ds_ce_header') ) {
function _ds_ce_header( &$id, $slide, $tc, $ac, $x, $w ) {
    $s = '';
    $title = $slide['title'] ?? ''; $sub = $slide['subtitle'] ?? '';
    $s .= ds_text_shape($id++, 'Title', $x, 170000, $w, 640000, '<a:p><a:pPr algn="l"/>' . ds_run($title, 2600, $tc, true) . '</a:p>', 'l', 't');
    if ( $sub !== '' ) $s .= ds_text_shape($id++, 'Sub', $x, 800000, $w, 380000, '<a:p><a:pPr algn="l"/>' . ds_run($sub, 1400, $ac) . '</a:p>', 'l', 't');
    return $s;
}
}
if ( ! function_exists('_ds_ce_num') ) {
/* number inside a badge: no insets, no wrapping, so "01" never breaks */
function _ds_ce_num( $id, $x, $y, $d, $txt, $sz ) {
    return '<p:sp><p:nvSpPr><p:cNvPr id="'.$id.'" name="Num'.$id.'"/><p:cNvSpPr txBox="1"><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
        . '<p:spPr><a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$d.'" cy="'.$d.'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/></p:spPr>'
        . '<p:txBody><a:bodyPr wrap="none" lIns="0" tIns="0" rIns="0" bIns="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/>'.ds_run($txt, $sz, 'FFFFFF', true).'</a:p></p:txBody></p:sp>';
}
}
if ( ! function_exists('_ds_ce_many') ) {
function _ds_ce_many( $slide, $tc, $bc, $ac, $img_rid, $side, $list ) {
    $W = 12192000; $H = 6858000; $m = 420000;
    $items = array_slice(array_values($slide['content'] ?? array()), 0, 16);
    $n = count($items);
    $imgW = (int) round($W * 0.28);
    if ( $side === 'r' ) { $gx = $m; $gw = $W - $imgW - $m - 260000; }
    elseif ( $side === 'l' ) { $gx = $imgW + 260000; $gw = $W - $imgW - 260000 - $m; }
    else { $gx = $m; $gw = $W - 2 * $m; }
    $id = 2;
    $s = ds_filled_rect($id++, 'AccentBar', 0, 0, $W, 30000, $ac);
    $s .= _ds_ce_header($id, $slide, $tc, $ac, $gx, $gw);
    $top = 1300000; $gh = $H - $top - 300000;
    if ( $list ) {
        $per = (int) ceil($n / 2); $cg = 360000; $cw = (int) (($gw - $cg) / 2);
        $rh = (int) floor($gh / max(1, $per));
        foreach ( $items as $i => $it ) {
            list($hd, $ds) = _ds_ce_split($it);
            $c = $i < $per ? 0 : 1; $r = $i < $per ? $i : $i - $per;
            $x = $gx + $c * ($cw + $cg); $y = $top + $r * $rh;
            $d = min(400000, (int) ($rh * 0.62));
            $s .= ds_ellipse($id++, 'N'.$i, $x, $y + (int) (($rh - $d) / 2), $d, $d, $ac);
            $s .= _ds_ce_num($id++, $x, $y + (int) (($rh - $d) / 2), $d, sprintf('%02d', $i + 1), 1000);
            $tx = $x + $d + 140000; $tw = $cw - $d - 140000;
            $runs = ($hd !== '' ? ds_run($hd, 1150, $tc, true) . ds_run('  ·  ', 1150, $ac, true) : '') . ds_run(_ds_ce_fit($ds, $tw, $rh - 60000, 1000), 1000, $bc);
            $s .= ds_text_shape($id++, 'NL'.$i, $tx, $y, $tw, $rh - 30000, '<a:p><a:pPr algn="l"/>' . $runs . '</a:p>', 'l', 'ctr');
            $s .= ds_filled_rect($id++, 'NR'.$i, $tx, $y + $rh - 22000, $tw, 9525, $ac, 30000);
        }
    } else {
        $cols = $side ? ($n <= 9 ? 3 : 4) : (array(9 => 3, 10 => 5, 11 => 4, 12 => 4, 13 => 5, 14 => 5, 15 => 5, 16 => 4)[$n] ?? 4);
        $rows = (int) ceil($n / $cols); $g = 150000; $rg = 170000;
        $cw = (int) (($gw - ($cols - 1) * $g) / $cols); $ch = (int) (($gh - ($rows - 1) * $rg) / $rows);
        for ( $r = 0; $r < $rows; $r++ ) {
            $rowItems = array_slice($items, $r * $cols, $cols, true); $k = count($rowItems);
            $off = (int) (($gw - ($k * $cw + ($k - 1) * $g)) / 2);
            $c = 0;
            foreach ( $rowItems as $i => $it ) {
                list($hd, $ds) = _ds_ce_split($it);
                $x = $gx + $off + $c * ($cw + $g); $y = $top + $r * ($ch + $rg); $c++;
                $s .= ds_rounded_rect($id++, 'Card'.$i, $x, $y, $cw, $ch, $ac, 9000, 7000);
                $d = 340000;
                $s .= ds_ellipse($id++, 'N'.$i, $x + 140000, $y + 140000, $d, $d, $ac);
                $s .= _ds_ce_num($id++, $x + 140000, $y + 140000, $d, sprintf('%02d', $i + 1), 950);
                $hx = $x + 140000 + $d + 90000; $hw = $cw - ($hx - $x) - 100000;
                if ( $hd !== '' ) $s .= ds_text_shape($id++, 'CH'.$i, $hx, $y + 90000, $hw, 440000, '<a:p><a:pPr algn="l"/>' . ds_run(_ds_ce_fit($hd, $hw, 420000, 1100), 1100, $tc, true) . '</a:p>', 'l', 'ctr');
                $dy = $y + 580000; $dh = $ch - 620000; $dw = $cw - 230000;
                if ( $ds !== '' && $dh > 150000 ) $s .= ds_text_shape($id++, 'CD'.$i, $x + 110000, $dy, $dw, $dh, '<a:p><a:pPr algn="l"/>' . ds_run(_ds_ce_fit($ds, $dw, $dh, 900), 900, $bc) . '</a:p>', 'l', 't');
            }
        }
    }
    if ( $side ) {
        $ix = $side === 'r' ? $W - $imgW : 0;
        if ( $img_rid && function_exists('ds_pptv2_img_cover') ) $s .= ds_pptv2_img_cover($id++, 'ManyImg', $ix, 0, $imgW, $H, $img_rid);
        else $s .= ds_rounded_rect($id++, 'ImgPH', $ix, 0, $imgW, $H, $ac, 15000, 3000);
    }
    return $s;
}
}
if ( ! function_exists('ds_pptv2_many_point_v1') ) { function ds_pptv2_many_point_v1($slide, $tc, $bc, $ac, $img_rid = null, $icon_rids = array()) { return _ds_ce_many($slide, $tc, $bc, $ac, null, '', false); } }
if ( ! function_exists('ds_pptv2_many_point_v2') ) { function ds_pptv2_many_point_v2($slide, $tc, $bc, $ac, $img_rid = null, $icon_rids = array()) { return _ds_ce_many($slide, $tc, $bc, $ac, $img_rid, 'r', false); } }
if ( ! function_exists('ds_pptv2_many_point_v3') ) { function ds_pptv2_many_point_v3($slide, $tc, $bc, $ac, $img_rid = null, $icon_rids = array()) { return _ds_ce_many($slide, $tc, $bc, $ac, null, '', true); } }
if ( ! function_exists('ds_pptv2_many_point_v4') ) { function ds_pptv2_many_point_v4($slide, $tc, $bc, $ac, $img_rid = null, $icon_rids = array()) { return _ds_ce_many($slide, $tc, $bc, $ac, $img_rid, 'l', true); } }


if ( ! function_exists('_ds_ce_stat_size') ) {
/* one size for every number on the slide, from the longest value */
function _ds_ce_stat_size( $items, $sizes ) {
    $mx = 0;
    foreach ( $items as $it ) { $p = explode(':', (string) $it, 2); $l = function_exists('mb_strlen') ? mb_strlen(trim($p[0])) : strlen(trim($p[0])); if ( $l > $mx ) $mx = $l; }
    foreach ( $sizes as $lim => $sz ) if ( $mx <= $lim ) return $sz;
    return end($sizes);
}
}
if ( ! function_exists('ds_pptv2_stats_v1') ) {
/* v1: centred title, three big numbers of the SAME size in heavy type, labels below */
function ds_pptv2_stats_v1( $slide, $tc, $bc, $ac, $img_rid = null, $icon_rids = array() ) {
    $W = 12192000;
    $items = array_slice(array_values($slide['content'] ?? array()), 0, 3);
    $n = max(1, count($items));
    $s = ds_filled_rect(2, 'AccentBar', 0, 0, $W, 30000, $ac);
    $s .= ds_text_shape(3, 'Title', 457200, 520000, 11277600, 820000, '<a:p><a:pPr algn="ctr"/>' . ds_run($slide['title'] ?? '', 3400, $tc, true) . '</a:p>', 'ctr', 't');
    if ( ! empty($slide['subtitle']) ) $s .= ds_text_shape(4, 'Sub', 1200000, 1330000, 9792000, 420000, '<a:p><a:pPr algn="ctr"/>' . ds_run($slide['subtitle'], 1500, $ac) . '</a:p>', 'ctr', 't');
    $sz = _ds_ce_stat_size($items, array(3 => 8000, 4 => 7200, 5 => 6200, 6 => 5400, 7 => 4800, 9 => 4000, 99 => 3200));
    $col = (int) (11277600 / $n); $id = 5;
    foreach ( $items as $i => $it ) {
        $p = explode(':', (string) $it, 2); $num = trim($p[0]); $lbl = isset($p[1]) ? trim(explode('|', $p[1])[0]) : '';
        $x = 457200 + $i * $col;
        $s .= ds_text_shape($id++, 'Stat'.$i, $x, 2250000, $col, 1700000, '<a:p><a:pPr algn="ctr"/>' . ds_run($num, $sz, $ac, true, false, 'Arial Black') . '</a:p>', 'ctr', 'ctr');
        if ( $lbl !== '' ) $s .= ds_text_shape($id++, 'Lbl'.$i, $x + 200000, 4050000, $col - 400000, 1200000, '<a:p><a:pPr algn="ctr"/>' . ds_run($lbl, 1500, $bc) . '</a:p>', 'ctr', 't');
    }
    return $s;
}
}
if ( ! function_exists('ds_ce_squeeze') ) {
/* make room for the takeaway strip: everything moves into the top $f of the slide */
function ds_ce_squeeze( $xml, $f, $fs, $off = 0 ) {
    $xml = preg_replace_callback('#<a:off x="(-?\d+)" y="(-?\d+)"/>(\s*)<a:ext cx="(\d+)" cy="(\d+)"/>#', function ($m) use ($f, $off) {
        $x = (int) $m[1]; $y = (int) $m[2]; $cx = (int) $m[4]; $cy = (int) $m[5];
        if ( $cx <= 700000 && $cy <= 700000 ) { $ny = (int) round($off + ($y + $cy / 2) * $f - $cy / 2); $ncy = $cy; }
        else { $ny = (int) round($off + $y * $f); $ncy = (int) round($cy * $f); }
        return '<a:off x="'.$x.'" y="'.$ny.'"/>'.$m[3].'<a:ext cx="'.$cx.'" cy="'.$ncy.'"/>';
    }, $xml);
    $xml = preg_replace_callback('#<a:chOff x="(-?\d+)" y="(-?\d+)"/>(\s*)<a:chExt cx="(\d+)" cy="(\d+)"/>#', function ($m) use ($f, $off) {
        return '<a:chOff x="'.$m[1].'" y="'.(int) round($off + $m[2] * $f).'"/>'.$m[3].'<a:chExt cx="'.$m[4].'" cy="'.(int) round($m[5] * $f).'"/>';
    }, $xml);
    $xml = preg_replace_callback('#(<a:(?:rPr|defRPr|endParaRPr)\b[^>]*?\s)sz="(\d+)"#', function ($m) use ($fs) {
        return $m[1].'sz="'.max(700, (int) (round($m[2] * $fs / 50) * 50)).'"';
    }, $xml);
    /* large pictures got shorter: crop a little more top/bottom so they are not squashed */
    $xml = preg_replace_callback('#<p:pic>.*?</p:pic>#s', function ($m) use ($f) {
        $p = $m[0];
        if ( ! preg_match('#<a:ext cx="(\d+)" cy="(\d+)"/>#', $p, $e) || ((int) $e[1] <= 700000 && (int) $e[2] <= 700000) ) return $p;
        $get = function ($k, $attrs) { return preg_match('#\b'.$k.'="(-?\d+)"#', $attrs, $mm) ? (int) $mm[1] : 0; };
        if ( preg_match('#<a:srcRect([^>]*)/>#', $p, $sr) ) {
            $l = $get('l', $sr[1]); $t = $get('t', $sr[1]); $r = $get('r', $sr[1]); $b = $get('b', $sr[1]);
            $vis = 100000 - $t - $b; $d = (int) round($vis * (1 - $f) / 2);
            return str_replace($sr[0], '<a:srcRect l="'.$l.'" t="'.($t + $d).'" r="'.$r.'" b="'.($b + $d).'"/>', $p);
        }
        $d = (int) round(100000 * (1 - $f) / 2);
        return preg_replace('#<a:stretch>#', '<a:srcRect t="'.$d.'" b="'.$d.'"/><a:stretch>', $p, 1);
    }, $xml);
    return $xml;
}
}
if ( ! function_exists('ds_ce_takeaway_xml') ) {
function ds_ce_takeaway_xml( $text, $tc, $ac, $logo = null, $area = null ) {
    $W = 12192000; $H = 6858000;
    $x = (int) ($W * 0.045); $w = (int) ($W * 0.91);
    if ( is_array($area) ) { $x = (int) $area[0]; $w = (int) $area[1]; }   /* strip kept inside the text area beside a full-height picture */
    if ( $logo ) {
        $b = ds_ce_logo_box($logo); $gap = (int) ($W * 0.012);
        if ( $b[1] + $b[3] > 629 && $b[1] < 694 && $b[2] < 640 ) {   /* logo on the strip's row: shorten the strip on that side */
            if ( $b[0] + $b[2] / 2 < 640 ) { $nx = (int) round(($b[0] + $b[2]) * 9525) + $gap; if ( $nx > $x ) { $w -= ($nx - $x); $x = $nx; } }
            else { $w = min($w, (int) round($b[0] * 9525) - $gap - $x); }
        }
    } $y = (int) ($H * 0.874); $h = (int) ($H * 0.096);
    $id = 900;
    $s  = ds_rounded_rect($id++, 'TakeawayBox', $x, $y, $w, $h, $ac, 12000, 12000);
    $s .= ds_filled_rect($id++, 'TakeawayBar', $x, $y, 55000, $h, $ac);
    $s .= ds_text_shape($id++, 'TakeawayLabel', $x + 110000, $y, 2050000, $h, '<a:p><a:pPr algn="l"/>' . ds_run('KEY TAKEAWAY', 1000, $ac, true) . '</a:p>', 'l', 'ctr');
    $s .= ds_filled_rect($id++, 'TakeawayRule', $x + 2200000, $y + 110000, 9525, $h - 220000, $ac, 45000);
    $tw = $w - 2350000;
    $s .= ds_text_shape($id++, 'Takeaway', $x + 2290000, $y, $tw, $h, '<a:p><a:pPr algn="l"/>' . ds_run(_ds_ce_fit($text, $tw, $h - 60000, 1300), 1300, $tc) . '</a:p>', 'l', 'ctr');
    return $s;
}
}
if ( ! function_exists('ds_ce_logo_prepare') ) {
/* decode the deck logo once and store it in the package */
/* DS logo options: the glow round the logo badge, 0 (none) .. 100 (strong), 50 = the look it always had */
function ds_ce_glow_xml( $ac ) {
    $g = isset($GLOBALS['DS_CE_GLOW']) ? (float) $GLOBALS['DS_CE_GLOW'] : 50.0; $g = max(0.0, min(100.0, $g));
    if ( $g < 1 ) return '';
    return '<a:effectLst><a:glow rad="' . (int) round(101600 * $g / 50) . '"><a:srgbClr val="' . $ac . '"><a:alpha val="' . (int) min(70000, round(22000 * $g / 50)) . '"/></a:srgbClr></a:glow></a:effectLst>';
}
function ds_ce_logo_prepare( $logo, $zip ) {
    if ( ! is_array($logo) || empty($logo['src']) || ! is_string($logo['src']) ) return null;
    if ( ! preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $logo['src'], $m) ) return null;
    $bytes = base64_decode($m[2], true);
    if ( ! $bytes || strlen($bytes) > 3 * 1024 * 1024 ) return null;
    $ext = $m[1] === 'png' ? 'png' : 'jpg';
    $w = isset($logo['w']) ? (float) $logo['w'] : 0; $h = isset($logo['h']) ? (float) $logo['h'] : 0;
    if ( $w <= 0 || $h <= 0 ) { $sz = @getimagesizefromstring($bytes); if ( $sz ) { $w = $sz[0]; $h = $sz[1]; } }
    if ( $w <= 0 || $h <= 0 ) { $w = 250; $h = 100; }
    $file = 'ds-logo.' . $ext;
    $zip->addFromString('ppt/media/' . $file, $bytes);
    /* DS logo options: an optional second (short) logo for the slides after the cover, and the glow strength */
    $file2 = ''; $w2 = 0; $h2 = 0;
    if ( ! empty($logo['src2']) && is_string($logo['src2']) && preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $logo['src2'], $m2) ) {
        $b2 = base64_decode($m2[2], true);
        if ( $b2 && strlen($b2) <= 3 * 1024 * 1024 ) {
            $file2 = 'ds-logo2.' . ( $m2[1] === 'png' ? 'png' : 'jpg' );
            $w2 = isset($logo['w2']) ? (float) $logo['w2'] : 0; $h2 = isset($logo['h2']) ? (float) $logo['h2'] : 0;
            if ( $w2 <= 0 || $h2 <= 0 ) { $s2 = @getimagesizefromstring($b2); if ( $s2 ) { $w2 = $s2[0]; $h2 = $s2[1]; } }
            if ( $w2 <= 0 || $h2 <= 0 ) { $w2 = 250; $h2 = 100; }
            $zip->addFromString('ppt/media/' . $file2, $b2);
        }
    }
    $__gl = function ( $v ) { return ( is_numeric($v) ) ? max(0.0, min(100.0, (float) $v)) : 50.0; };
    $GLOBALS['DS_CE_GLOW'] = $__gl($logo['glow'] ?? null);
    $pos = in_array(($logo['pos'] ?? ''), array('tl','tr','bl','br'), true) ? $logo['pos'] : 'tr';
    $size = in_array(($logo['size'] ?? ''), array('s','m','l'), true) ? $logo['size'] : 'm';
    $nm = function ($v, $d) { return is_numeric($v) ? max(0, min(100, (float) $v)) : $d; };
    $lx = $nm($logo['x'] ?? null, $pos[1] === 'l' ? 0 : 100);
    $ly = $nm($logo['y'] ?? null, $pos[0] === 'b' ? 100 : 0);
    $lsz = $nm($logo['sz'] ?? null, $size === 's' ? 12 : ($size === 'l' ? 34 : 22));
    $badge = ! (isset($logo['badge']) && ($logo['badge'] === false || $logo['badge'] === 'false' || $logo['badge'] === 0 || $logo['badge'] === '0'));
    $bgv = $nm($logo['bgv'] ?? null, -1);
    $cmode = ( ($logo['cmode'] ?? '') === 'free' ) ? 'free' : 'auto'; /* cover slide: its own placement and size */
		$cx = $nm($logo['cx'] ?? null, 50); $cy = $nm($logo['cy'] ?? null, 0); $csz = $nm($logo['csz'] ?? null, $lsz);
		$sbadge = isset($logo['sbadge']) && ( $logo['sbadge'] === true || $logo['sbadge'] === 'true' || $logo['sbadge'] === 1 || $logo['sbadge'] === '1' ); /* other slides: optional badge */
		return array('file2' => $file2, 'w2' => $w2, 'h2' => $h2, 'glow' => $__gl($logo['glow'] ?? null), 'sglow' => $__gl($logo['sglow'] ?? null), 'sbadge' => $sbadge, 'sbgv' => $nm($logo['sbgv'] ?? null, -1), 'cmode' => $cmode, 'cx' => $cx, 'cy' => $cy, 'csz' => $csz, 'badge' => $badge, 'bgv' => $bgv, 'file' => $file, 'pos' => $pos, 'size' => $size, 'x' => $lx, 'y' => $ly, 'sz' => $lsz, 'w' => $w, 'h' => $h, 'cover' => ! (isset($logo['cover']) && ($logo['cover'] === false || $logo['cover'] === 'false' || $logo['cover'] === 0 || $logo['cover'] === '0')));
}
}
if ( ! function_exists('ds_ce_logo_box') ) {
/* corner logo box in 1280 x 720 units: x, y, w, h (Snippet 17 uses the same numbers) */
function ds_ce_logo_box( $L ) {
    $f = ($L['sz'] ?? 22) / 100; $a = $L['w'] / $L['h'];
    $w = 50 + 1230 * pow($f, 1.6); $h = $w / $a;
    if ( $h > 720 ) { $h = 720; $w = $h * $a; }
    if ( $w > 1280 ) { $w = 1280; $h = $w / $a; }
    $m = 26 * (1 - $f);
    $x = $m + (1280 - 2 * $m - $w) * ($L['x'] ?? 100) / 100;
    $y = $m + (720 - 2 * $m - $h) * ($L['y'] ?? 0) / 100;
    return array($x, $y, $w, $h);
}
}
if ( ! function_exists('ds_ce_pic') ) {
function ds_ce_pic( $id, $x, $y, $w, $h ) {
    $e = 9525;
    return '<p:pic><p:nvPicPr><p:cNvPr id="'.$id.'" name="Company Logo" descr="Logo"/><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'
        . '<p:blipFill><a:blip r:embed="rIdDsLogo"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
        . '<p:spPr><a:xfrm><a:off x="'.(int) round($x * $e).'" y="'.(int) round($y * $e).'"/><a:ext cx="'.(int) round($w * $e).'" cy="'.(int) round($h * $e).'"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/></p:spPr></p:pic>';
}
}
if ( ! function_exists('ds_ce_header_logo') ) {
/* cover slide: the uploaded logo takes the place of the AI's placeholder company mark */
function ds_ce_header_logo( &$id, $L, $ac, $bg, $x, $y, $w, $align ) {
    $e = 9525; $szv = $L['csz'] ?? ($L['sz'] ?? 22); $mxw = 200 + 2.6 * $szv; $mxh = 50 + 0.5 * $szv;
    $a = $L['w'] / $L['h']; $lw = $mxw; $lh = $lw / $a; if ( $lh > $mxh ) { $lh = $mxh; $lw = $lh * $a; }
    $px = max(16, $lh * 0.3); $py = 10; $bw = $lw + 2 * $px; $bh = $lh + 2 * $py;
    $bx = $align === 'ctr' ? ($x / $e) + ($w / $e - $bw) / 2 : $x / $e;
    $by = $y / $e - 6;
    $c = ltrim((string) $bg, '#'); $lum = strlen($c) >= 6 ? (0.299 * hexdec(substr($c, 0, 2)) + 0.587 * hexdec(substr($c, 2, 2)) + 0.114 * hexdec(substr($c, 4, 2))) : 0;
    $v = ( isset($L['bgv']) && $L['bgv'] >= 0 ) ? $L['bgv'] : ( $lum < 128 ? 56 : 47 );
    $al = (int) round(abs($v - 50) / 50 * 100000);
    $fill = $al > 0 ? '<a:solidFill><a:srgbClr val="'.($v >= 50 ? 'FFFFFF' : '000000').'"><a:alpha val="'.$al.'"/></a:srgbClr></a:solidFill>' : '<a:noFill/>';
    if ( isset($L['badge']) && ! $L['badge'] ) { $s = ds_ce_pic($id++, $bx + $px, $by + $py, $lw, $lh); return $s; }
    $s = '<p:sp><p:nvSpPr><p:cNvPr id="'.($id++).'" name="Logo Badge"/><p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
        . '<p:spPr><a:xfrm><a:off x="'.(int) round($bx * $e).'" y="'.(int) round($by * $e).'"/><a:ext cx="'.(int) round($bw * $e).'" cy="'.(int) round($bh * $e).'"/></a:xfrm>'
        . '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 18000"/></a:avLst></a:prstGeom>' . $fill
        . '<a:ln w="9525"><a:solidFill><a:srgbClr val="'.$ac.'"><a:alpha val="45000"/></a:srgbClr></a:solidFill></a:ln>'
        . '' . ds_ce_glow_xml($ac) . '</p:spPr>'
        . '<p:txBody><a:bodyPr/><a:lstStyle/><a:p/></p:txBody></p:sp>';
    $s .= ds_ce_pic($id++, $bx + $px, $by + $py, $lw, $lh);
    return $s;
}
}
if ( ! function_exists('ds_ce_cover_logo_xml') ) {
/* cover slide: bigger logo centred at the top inside a soft, accent-edged badge */
function ds_ce_cover_logo_xml( $L, $ac, $bg ) {
    $sz = array('s' => array(220, 64), 'm' => array(300, 78), 'l' => array(380, 90));
    $mx = $sz[$L['size']] ?? $sz['m'];
    $a = $L['w'] / $L['h']; $w = $mx[0]; $h = $w / $a;
    if ( $h > $mx[1] ) { $h = $mx[1]; $w = $h * $a; }
    $px = max(18, $h * 0.28); $py = 12; $bw = $w + 2 * $px; $bh = $h + 2 * $py; $by = (115 - $bh) / 2 + 4; $bx = 640 - $bw / 2;
    $e = 9525;
    $c = ltrim((string) $bg, '#'); $lum = strlen($c) >= 6 ? (0.299 * hexdec(substr($c, 0, 2)) + 0.587 * hexdec(substr($c, 2, 2)) + 0.114 * hexdec(substr($c, 4, 2))) : 0;
    $fill = $lum < 128 ? '<a:solidFill><a:srgbClr val="FFFFFF"><a:alpha val="10000"/></a:srgbClr></a:solidFill>' : '<a:solidFill><a:srgbClr val="000000"><a:alpha val="5000"/></a:srgbClr></a:solidFill>';
    $badge = '<p:sp><p:nvSpPr><p:cNvPr id="988" name="Logo Badge"/><p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
        . '<p:spPr><a:xfrm><a:off x="'.(int) round($bx * $e).'" y="'.(int) round($by * $e).'"/><a:ext cx="'.(int) round($bw * $e).'" cy="'.(int) round($bh * $e).'"/></a:xfrm>'
        . '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 18000"/></a:avLst></a:prstGeom>' . $fill
        . '<a:ln w="9525"><a:solidFill><a:srgbClr val="'.$ac.'"><a:alpha val="45000"/></a:srgbClr></a:solidFill></a:ln>'
        . '' . ds_ce_glow_xml($ac) . '</p:spPr>'
        . '<p:txBody><a:bodyPr/><a:lstStyle/><a:p/></p:txBody></p:sp>';
    return $badge . ds_ce_pic(990, 640 - $w / 2, $by + $py, $w, $h);
}
}
if ( ! function_exists('ds_ce_logo_xml') ) {
function ds_ce_logo_xml( $L, $ac = '', $bg = '' ) {
		if ( ! empty($L['sbadge']) && function_exists('ds_ce_cover_free_xml') ) { /* other slides: badge behind the logo */
			$C = $L; $C['cx'] = $L['x'] ?? 100; $C['cy'] = $L['y'] ?? 0; $C['csz'] = $L['sz'] ?? 22; $C['badge'] = true; $C['bgv'] = $L['sbgv'] ?? -1;
			$__g0 = $GLOBALS['DS_CE_GLOW'] ?? 50; $GLOBALS['DS_CE_GLOW'] = $L['sglow'] ?? 50;   /* other slides have their own glow strength */
			$__x = ds_ce_cover_free_xml($C, $ac !== '' ? $ac : '888888', $bg !== '' ? $bg : '1B2A4A');
			$GLOBALS['DS_CE_GLOW'] = $__g0; return $__x;
		}
    list($x, $y, $w, $h) = ds_ce_logo_box($L);
    $e = 9525;
    return '<p:pic><p:nvPicPr><p:cNvPr id="990" name="Company Logo" descr="Logo"/><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'
        . '<p:blipFill><a:blip r:embed="rIdDsLogo"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
        . '<p:spPr><a:xfrm><a:off x="'.(int) round($x * $e).'" y="'.(int) round($y * $e).'"/><a:ext cx="'.(int) round($w * $e).'" cy="'.(int) round($h * $e).'"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/></p:spPr></p:pic>';
}
}
if ( ! function_exists('ds_ce_cover_free_xml') ) {
	/* cover slide logo at its own position and size, inside the badge (Snippet 17 uses the same numbers) */
	function ds_ce_cover_free_xml( $L, $ac, $bg ) {
		$C = $L; $C['x'] = $L['cx'] ?? 50; $C['y'] = $L['cy'] ?? 0; $C['sz'] = $L['csz'] ?? ($L['sz'] ?? 22);
		list($x, $y, $w, $h) = ds_ce_logo_box($C);
		$id = 985; $e = 9525;
		if ( isset($L['badge']) && ! $L['badge'] ) return ds_ce_pic($id, $x, $y, $w, $h);
		$px = max(14, $h * 0.22); $py = max(8, $h * 0.14);
		$bx = $x - $px; $by = $y - $py; $bw = $w + 2 * $px; $bh = $h + 2 * $py;
		if ( $bw > 1280 || $bh > 720 ) { $bx = $x; $by = $y; $bw = $w; $bh = $h; }
		$dx = $bx < 0 ? -$bx : ( $bx + $bw > 1280 ? 1280 - ($bx + $bw) : 0 );
		$dy = $by < 0 ? -$by : ( $by + $bh > 720 ? 720 - ($by + $bh) : 0 );
		$bx += $dx; $by += $dy; $x += $dx; $y += $dy;
		$c = ltrim((string) $bg, '#'); $lum = strlen($c) >= 6 ? (0.299 * hexdec(substr($c, 0, 2)) + 0.587 * hexdec(substr($c, 2, 2)) + 0.114 * hexdec(substr($c, 4, 2))) : 0;
		$v = ( isset($L['bgv']) && $L['bgv'] >= 0 ) ? $L['bgv'] : ( $lum < 128 ? 56 : 47 );
		$al = (int) round(abs($v - 50) / 50 * 100000);
		$fill = $al > 0 ? '<a:solidFill><a:srgbClr val="'.($v >= 50 ? 'FFFFFF' : '000000').'"><a:alpha val="'.$al.'"/></a:srgbClr></a:solidFill>' : '<a:noFill/>';
		$s = '<p:sp><p:nvSpPr><p:cNvPr id="'.($id++).'" name="Logo Badge"/><p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
			. '<p:spPr><a:xfrm><a:off x="'.(int) round($bx * $e).'" y="'.(int) round($by * $e).'"/><a:ext cx="'.(int) round($bw * $e).'" cy="'.(int) round($bh * $e).'"/></a:xfrm>'
			. '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 18000"/></a:avLst></a:prstGeom>' . $fill
			. '<a:ln w="9525"><a:solidFill><a:srgbClr val="'.$ac.'"><a:alpha val="45000"/></a:srgbClr></a:solidFill></a:ln>'
			. '' . ds_ce_glow_xml($ac) . '</p:spPr>'
			. '<p:txBody><a:bodyPr/><a:lstStyle/><a:p/></p:txBody></p:sp>';
		$s .= ds_ce_pic($id++, $x, $y, $w, $h);
		return $s;
	}
}
if ( ! function_exists('ds_ce_post_body') ) {
function ds_ce_post_body( $xml, $slide, $type, $tc, $ac, $logo, $bg = '1B2A4A' ) {
    if ( $type === 'title' ) { /* cover logo: in the title's company slot (ds_title_header_shapes) or at its own position */
			if ( $logo && ! empty($logo['cover']) && ( $logo['cmode'] ?? 'auto' ) === 'free' && function_exists('ds_ce_cover_free_xml') ) $xml .= ds_ce_cover_free_xml($logo, $ac, $bg);
			return $xml;
		}   /* cover logo is drawn in the title's company slot (ds_title_header_shapes) */
    $tk = isset($slide['takeaway']) ? trim(wp_strip_all_tags((string) $slide['takeaway'])) : '';
    $tk_ok = in_array($type, ds_ce_point_types(), true) || $type === 'stats' || ( $type === 'chart' && empty($slide['dg']) && empty($slide['dg_els']) );
    if ( $tk !== '' && $tk_ok ) {
        /* a full-height picture beside the text keeps its full height (down to the bottom of the slide);
           only the text area moves up, and the strip stays inside the text area */
        $W = 12192000; $H = 6858000; $keep = array(); $area = null;
        $xml = preg_replace_callback('#<p:pic>.*?</p:pic>#s', function ($m) use (&$keep, &$area, $W, $H) {
            if ( $area !== null ) return $m[0];
            if ( ! preg_match('#<a:off x="(-?\d+)" y="(-?\d+)"/>\s*<a:ext cx="(\d+)" cy="(\d+)"/>#', $m[0], $g) ) return $m[0];
            $x = (int) $g[1]; $y = (int) $g[2]; $cx = (int) $g[3]; $cy = (int) $g[4];
            if ( $y > 20000 || $cy < $H * 0.97 || $cx > $W * 0.6 || $cx < $W * 0.12 ) return $m[0];
            $gap = (int) ($W * 0.026); $pad = (int) ($W * 0.045);
            if ( $x <= 20000 ) $area = array($cx + $gap, $W - $cx - $gap - $pad);
            elseif ( $x + $cx >= $W - 20000 ) $area = array($pad, $x - $gap - $pad);
            else return $m[0];
            $keep[] = $m[0];
            return '<!--DSCE_KEEP_PIC-->';
        }, $xml);
        $xml = ds_ce_squeeze($xml, 0.86, 0.94);
        if ( $keep ) $xml = str_replace('<!--DSCE_KEEP_PIC-->', $keep[0], $xml);
        if ( $type !== 'chart' ) {   /* the strip lines up with the content boxes: same left edge and same right edge */
            $L = PHP_INT_MAX; $R = 0;
            if ( preg_match_all('#<p:sp>.*?</p:sp>#s', $xml, $sm) ) foreach ( $sm[0] as $sp ) {
                if ( ! preg_match('#name="([^"]*)"#', $sp, $nm) || preg_match('#^(Title|Sub|Accent|Overlay|ImgPH|Divider|Takeaway)#i', $nm[1]) ) continue;
                if ( ! preg_match('#<a:off x="(-?\d+)" y="(-?\d+)"/>\s*<a:ext cx="(\d+)" cy="(\d+)"/>#', $sp, $g) ) continue;
                $sx = (int) $g[1]; $scx = (int) $g[3];
                if ( $scx > $W * 0.97 || $sx < 0 ) continue;
                if ( $sx < $L ) $L = $sx;
                if ( $sx + $scx > $R ) $R = $sx + $scx;
            }
            if ( $R - $L > $W * 0.4 ) $area = array($L, $R - $L);
        }
        $xml .= ds_ce_takeaway_xml($tk, $tc, $ac, $logo, $area);
    }
    if ( $logo ) {   /* DS logo options: the slides after the cover use the second logo when one was uploaded */
        if ( ! empty($logo['file2']) ) { $__l2 = $logo; $__l2['w'] = $logo['w2']; $__l2['h'] = $logo['h2']; $xml .= str_replace('r:embed="rIdDsLogo"', 'r:embed="rIdDsLogo2"', ds_ce_logo_xml($__l2, $ac, $bg)); }
        else $xml .= ds_ce_logo_xml($logo, $ac, $bg);
    }
    return $xml;
}
}
/* ================= end DS CLASSIC EDITOR ================= */

/* ================================================================
   DS Classic Editor — Numbers slides (2026-10-03)
   One layout engine for every classic Numbers slide, 1 to 16 numbers:
   columns split by hairlines, a rule under the numbers, labels below,
   and the Key takeaway right under the numbers inside the text area
   (v1 centred, v2 image right, v3 image background, v4 image left,
   v5 image along the bottom) instead of across the bottom of the slide.
   ================================================================ */
if ( ! function_exists('ds_ce_stats_count') ) {
function ds_ce_stats_count( $slide ) {
    $n = 0;
    foreach ( (array) ( $slide['content'] ?? array() ) as $it ) { if ( trim((string) $it) !== '' ) $n++; }
    return $n;
}
}
if ( ! function_exists('ds_ce_stats_many') ) {
function ds_ce_stats_many( $slide, $tc, $bc, $ac, $img_rid, $variant, $bg = 'FFFFFF' ) {
    $W = 12192000; $H = 6858000;
    $items = array();
    foreach ( (array) ( $slide['content'] ?? array() ) as $it ) { if ( trim((string) $it) !== '' ) $items[] = (string) $it; }
    $items = array_slice($items, 0, 16);
    $n = max(1, count($items));
    $tk = isset($slide['takeaway']) ? trim(wp_strip_all_tags((string) $slide['takeaway'])) : '';
    $px = isset($slide['image_position']['x']) ? $slide['image_position']['x'] : 50;
    $py = isset($slide['image_position']['y']) ? $slide['image_position']['y'] : 50;
    $s = ''; $id = 2;
    $x0 = 560000; $w = $W - 1120000; $bandH = $H; $centre = false; $after = ''; $barW = $W;
    if ( $variant === 'v2' ) {                                   /* image right */
        $iw = (int) ($W * 0.28);
        if ( $img_rid && function_exists('ds_pptv2_img_cover') ) $after = ds_pptv2_img_cover(90, 'StatsImg', $W - $iw, 0, $iw, $H, $img_rid, $px, $py);
        else $after = ds_filled_rect(90, 'ImgPH', $W - $iw, 0, $iw, $H, $ac, 15000);
        $w = $W - $iw - $x0 - 380000; $barW = $W - $iw;
    } elseif ( $variant === 'v4' ) {                             /* image left */
        $iw = (int) ($W * 0.28);
        if ( $img_rid && function_exists('ds_pptv2_img_cover') ) $s .= ds_pptv2_img_cover($id++, 'StatsImg', 0, 0, $iw, $H, $img_rid, $px, $py);
        else $s .= ds_filled_rect($id++, 'ImgPH', 0, 0, $iw, $H, $ac, 15000);
        $x0 = $iw + 420000; $w = $W - $x0 - 560000;
    } elseif ( $variant === 'v3' ) {                             /* image behind, theme overlay */
        if ( $img_rid && function_exists('ds_pptv2_img_cover') ) $s .= ds_pptv2_img_cover($id++, 'StatsBG', 0, 0, $W, $H, $img_rid, $px, $py);
        $s .= ds_filled_rect($id++, 'Overlay', 0, 0, $W, $H, $bg, (int) ( max(88, defined('DS_PPT_OVERLAY_PCT') ? DS_PPT_OVERLAY_PCT : 80) * 1000 ));   /* numbers must stay readable: the picture shows through only faintly */
    } elseif ( $variant === 'v5' ) {                             /* image along the bottom */
        $bandH = (int) ($H * 0.60);
        $after = ds_filled_rect(90, 'Divider', 0, $bandH - 80000, $W, 80000, $ac);
        if ( $img_rid && function_exists('ds_pptv2_img_cover') ) $after .= ds_pptv2_img_cover(91, 'BottomImg', 0, $bandH, $W, $H - $bandH, $img_rid, $px, $py);
        else $after .= ds_filled_rect(91, 'ImgPH', 0, $bandH, $W, $H - $bandH, $ac, 15000);
    } else {
        $centre = true;                                          /* v1 */
    }
    $s .= ds_filled_rect($id++, 'AccentBar', ( $variant === 'v4' ? (int) ($W * 0.28) : 0 ), 0, ( $variant === 'v4' ? $W - (int) ($W * 0.28) : $barW ), 45000, $ac);
    $al = $centre ? 'ctr' : 'l';
    $small = ( $variant === 'v5' );
    $s .= ds_text_shape($id++, 'Title', $x0, $small ? 230000 : 520000, $w, 600000, '<a:p><a:pPr algn="' . $al . '"/>' . ds_run($slide['title'] ?? '', $small ? 2600 : 3000, $tc, true) . '</a:p>', $al, 't');
    $gy = $small ? 830000 : 1150000;
    if ( ! empty($slide['subtitle']) ) {
        $s .= ds_text_shape($id++, 'Sub', $x0, $gy - 20000, $w, 340000, '<a:p><a:pPr algn="' . $al . '"/>' . ds_run($slide['subtitle'], $small ? 1200 : 1400, $ac, true) . '</a:p>', $al, 't');
        $gy += $small ? 330000 : 400000;
    }
    $gy += $small ? 60000 : 200000;
    $cols = $n <= 4 ? $n : ( $n <= 6 ? 3 : ( $n <= 8 ? 4 : ( $n <= 9 ? 3 : 4 ) ) );
    $rows = (int) ceil($n / $cols);
    $bottom = $bandH - ( $small ? 200000 : 430000 );
    $th = $tk !== '' ? ( $small ? 470000 : 640000 ) : 0; $tgap = $tk !== '' ? ( $small ? 190000 : 540000 ) : 0;
    $avail = max(700000, $bottom - $gy);
    $ch = (int) min( $rows === 1 ? 2900000 : 2200000, ( $avail - $th - $tgap ) / $rows );
    $ch = max(430000, $ch);
    $group = $rows * $ch + $th + $tgap;
    $y0 = $gy + (int) max(0, ( $avail - $group ) / 2);
    $cw = (int) ($w / $cols);
    /* one number size for the whole slide: from the row height, the column width and the longest number */
    $mx = 1;
    foreach ( $items as $it ) { $p = explode(':', $it, 2); $l = function_exists('mb_strlen') ? mb_strlen(trim($p[0])) : strlen(trim($p[0])); if ( $l > $mx ) $mx = $l; }
    $nh = (int) ($ch * ( $rows === 1 ? 0.44 : 0.48 ));
    $byH = (int) ( $nh * 0.86 / 12700 / 1.15 * 100 );
    $byW = (int) ( ( $cw * 0.90 ) / 12700 / ( $mx * 0.74 ) * 100 );
    $nsz = max(1200, min(8000, $byH, $byW));
    $lsz = max(900, min(1800, (int) ( $ch * 0.085 / 12700 * 100 )));
    $hair = 9525;
    foreach ( $items as $i => $it ) {
        $p = explode(':', $it, 2); $num = trim($p[0]); $lbl = isset($p[1]) ? trim(explode('|', $p[1])[0]) : '';
        $c = $i % $cols; $r = (int) floor($i / $cols);
        $inRow = ( $r === $rows - 1 ) ? ( $n - $r * $cols ) : $cols;                 /* a short last row is centred */
        $x = $x0 + (int) ( ( $w - $inRow * $cw ) / 2 ) + $c * $cw; $y = $y0 + $r * $ch;
        $s .= ds_text_shape($id++, 'Stat' . $i, $x, $y, $cw, $nh, '<a:p><a:pPr algn="ctr"/>' . ds_run($num, $nsz, $tc, true, false, 'Arial Black') . '</a:p>', 'ctr', 'b');
        $s .= ds_filled_rect($id++, 'StatRule' . $i, $x, $y + $nh + 45000, $cw, $hair, $tc, 18000);
        if ( $c > 0 ) $s .= ds_filled_rect($id++, 'StatDiv' . $i, $x, $y, $hair, $ch - ( $rows > 1 ? 70000 : 0 ), $tc, 18000);
        if ( $lbl !== '' ) $s .= ds_text_shape($id++, 'Lbl' . $i, $x + 110000, $y + $nh + 130000, $cw - 220000, max(200000, $ch - $nh - 200000), '<a:p><a:pPr algn="ctr"/>' . ds_run($lbl, $lsz, $variant === 'v3' ? $tc : $bc) . '</a:p>', 'ctr', 't');
    }
    if ( $tk !== '' ) {                                              /* Key takeaway: right under the numbers, as wide as the text area */
        $ty = $y0 + $rows * $ch + $tgap; $lw = $small ? 1750000 : 2050000; $tsz = $small ? 1100 : 1300;
        $s .= ds_rounded_rect($id++, 'TakeawayBox', $x0, $ty, $w, $th, $ac, 12000, 12000);
        $s .= ds_filled_rect($id++, 'TakeawayBar', $x0, $ty, 55000, $th, $ac);
        $s .= ds_text_shape($id++, 'TakeawayLabel', $x0 + 110000, $ty, $lw, $th, '<a:p><a:pPr algn="l"/>' . ds_run('KEY TAKEAWAY', $small ? 900 : 1000, $ac, true) . '</a:p>', 'l', 'ctr');
        $s .= ds_filled_rect($id++, 'TakeawayRule', $x0 + $lw + 150000, $ty + 110000, 9525, $th - 220000, $ac, 45000);
        $tw = $w - $lw - 300000;
        $txt = function_exists('_ds_ce_fit') ? _ds_ce_fit($tk, $tw, $th - 60000, $tsz) : $tk;
        $s .= ds_text_shape($id++, 'Takeaway', $x0 + $lw + 240000, $ty, $tw, $th, '<a:p><a:pPr algn="l"/>' . ds_run($txt, $tsz, $tc, true) . '</a:p>', 'l', 'ctr');
    }
    return $s . $after;
}
}
