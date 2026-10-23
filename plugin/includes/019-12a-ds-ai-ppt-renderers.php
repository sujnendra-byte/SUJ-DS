<?php
/**
 * Code Snippets export — 12a-DS AI PPT Renderers
 *
 * Snippet ID : 705
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-019
 * Modified   : 2026-09-09 02:43:00
 * Load order : 019 of 144
 */
/**
 * Snippet Name: DS AI PPT v6 - Renderers
 * Run: Everywhere
 *
 * v6 changes from v5:
 * - REMOVED: 50 base64 Lucide icons (too big for snippet limit)
 * - REMOVED: Embedded Montserrat/Poppins fonts (too big, fonts now reference system fonts)
 * - FIXED: feature_grid now uses Unicode symbols inside circles (renders everywhere)
 * - FIXED: image_text now uses 4:3 landscape ratio (was 3:4 portrait)
 * - FIXED: Stronger typography hierarchy (bigger titles, bolder weights)
 * - FIXED: Card visual depth (shadows via line stroke + better padding)
 * - Fonts: Calibri (titles bold) + Calibri Light (body) - installed everywhere
 *
 * Required companion snippets:
 *   - 12b-ds-ppt-builder.php (API endpoints + PPTX assembler)
 *   - 12c-ds-ppt-ui.php       (frontend shortcode)
 */

if ( ! defined('ABSPATH') ) exit;

// Safe Unicode constants (source stays pure ASCII)
if ( ! defined('DS_PPT_BULLET') ) define('DS_PPT_BULLET', "\u{2022}"); // *
if ( ! defined('DS_PPT_EMDASH') ) define('DS_PPT_EMDASH', "\u{2014}"); // -

// Icon symbols: intentionally EMPTY.
// These emoji were the fallback whenever an Iconify icon could not be fetched,
// and they shipped raw emoji characters into customers' PPTX files - which
// render differently on every machine and cannot be recoloured. A slide now
// shows a real vector icon or nothing at all, never an emoji glyph.
if ( ! defined( 'DS_PPT_ICON_SYMBOLS' ) ) {
    define( 'DS_PPT_ICON_SYMBOLS', array() );
}

// --- XML helpers --------------------------------------------------------------
if ( ! function_exists( 'ds_xml_esc' ) ) {
function ds_xml_esc($s) {
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
}

if ( ! function_exists( 'ds_run' ) ) {
function ds_run($text, $size, $color, $bold = false, $italic = false, $typeface = null) {
    $b = $bold ? ' b="1"' : '';
    $i = $italic ? ' i="1"' : '';
    // Default: Calibri for titles (bold), Calibri Light for body. Installed on all systems.
    if ($typeface === null) {
        $gf = isset($GLOBALS['DS_PPT_FONT_FAMILY']) ? $GLOBALS['DS_PPT_FONT_FAMILY'] : '';
        $typeface = ($gf !== '') ? $gf : ($bold ? 'Arial Black' : 'Arial');
    }
    return '<a:r><a:rPr lang="en-US" sz="'.$size.'"'.$b.$i.' dirty="0">'.
        '<a:solidFill><a:srgbClr val="'.$color.'"/></a:solidFill>'.
        '<a:latin typeface="'.$typeface.'" pitchFamily="34" charset="0"/>'.
        '<a:cs typeface="'.$typeface.'" pitchFamily="34" charset="0"/>'.
        '</a:rPr><a:t>'.ds_xml_esc($text).'</a:t></a:r>';
}
}

if ( ! function_exists( 'ds_text_shape' ) ) {
function ds_text_shape($id, $name, $x, $y, $cx, $cy, $body_xml, $align = 'l', $anchor = 't') {
    return '<p:sp><p:nvSpPr><p:cNvPr id="'.$id.'" name="'.$name.'"/>'.
        '<p:cNvSpPr txBox="1"><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'.
        '<p:spPr>'.
        '<a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'.
        '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/>'.
        '</p:spPr>'.
        '<p:txBody>'.
        '<a:bodyPr wrap="square" lIns="91440" tIns="45720" rIns="91440" bIns="45720" anchor="'.$anchor.'"/>'.
        '<a:lstStyle/>'.
        $body_xml.
        '</p:txBody></p:sp>';
}
}

if ( ! function_exists( 'ds_filled_rect' ) ) {
function ds_filled_rect($id, $name, $x, $y, $cx, $cy, $color, $opacity = 100000) {
    $fill = $opacity < 100000
        ? '<a:solidFill><a:srgbClr val="'.$color.'"><a:alpha val="'.$opacity.'"/></a:srgbClr></a:solidFill>'
        : '<a:solidFill><a:srgbClr val="'.$color.'"/></a:solidFill>';
    return '<p:sp><p:nvSpPr><p:cNvPr id="'.$id.'" name="'.$name.'"/>'.
        '<p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'.
        '<p:spPr>'.
        '<a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'.
        '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'.
        $fill.
        '<a:ln><a:noFill/></a:ln></p:spPr>'.
        '<p:txBody><a:bodyPr/><a:lstStyle/><a:p/></p:txBody></p:sp>';
}
}

if ( ! function_exists( 'ds_rounded_rect' ) ) {
function ds_rounded_rect($id, $name, $x, $y, $cx, $cy, $fill_color, $opacity = 100000, $radius = 8000) {
    $fill = $opacity < 100000
        ? '<a:solidFill><a:srgbClr val="'.$fill_color.'"><a:alpha val="'.$opacity.'"/></a:srgbClr></a:solidFill>'
        : '<a:solidFill><a:srgbClr val="'.$fill_color.'"/></a:solidFill>';
    return '<p:sp><p:nvSpPr><p:cNvPr id="'.$id.'" name="'.$name.'"/>'.
        '<p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'.
        '<p:spPr>'.
        '<a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'.
        '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val '.$radius.'"/></a:avLst></a:prstGeom>'.
        $fill.
        '<a:ln><a:noFill/></a:ln></p:spPr>'.
        '<p:txBody><a:bodyPr/><a:lstStyle/><a:p/></p:txBody></p:sp>';
}
}

// Create a picture (icon image) shape inside a circle background
if ( ! function_exists( 'ds_icon_pic' ) ) {
function ds_icon_pic($id, $name, $x, $y, $cx, $cy, $rid, $bg_color, $padding_emu = 80000) {
    // Circle background
    $shapes = ds_ellipse($id, $name.'Bg', $x, $y, $cx, $cy, $bg_color, 40000);
    // Icon image (padded inside circle)
    $px = $x + $padding_emu;
    $py = $y + $padding_emu;
    $pcx = $cx - 2*$padding_emu;
    $pcy = $cy - 2*$padding_emu;
    $shapes .= '<p:pic>'.
        '<p:nvPicPr><p:cNvPr id="'.($id+1).'" name="'.$name.'Ico"/><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'.
        '<p:blipFill><a:blip r:embed="'.$rid.'"/><a:srcRect/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'.
        '<p:spPr><a:xfrm><a:off x="'.$px.'" y="'.$py.'"/><a:ext cx="'.$pcx.'" cy="'.$pcy.'"/></a:xfrm>'.
        '<a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom><a:noFill/></p:spPr>'.
        '</p:pic>';
    return $shapes;
}
}

if ( ! function_exists( 'ds_ellipse' ) ) {
function ds_ellipse($id, $name, $x, $y, $cx, $cy, $fill_color, $opacity = 100000) {
    $fill = $opacity < 100000
        ? '<a:solidFill><a:srgbClr val="'.$fill_color.'"><a:alpha val="'.$opacity.'"/></a:srgbClr></a:solidFill>'
        : '<a:solidFill><a:srgbClr val="'.$fill_color.'"/></a:solidFill>';
    return '<p:sp><p:nvSpPr><p:cNvPr id="'.$id.'" name="'.$name.'"/>'.
        '<p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'.
        '<p:spPr>'.
        '<a:xfrm><a:off x="'.$x.'" y="'.$y.'"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'.
        '<a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom>'.
        $fill.
        '<a:ln><a:noFill/></a:ln></p:spPr>'.
        '<p:txBody><a:bodyPr/><a:lstStyle/><a:p/></p:txBody></p:sp>';
}
}

// --- Slide type renderers (8 total) -------------------------------------------
function ds_render_title($slide, $tc, $bc, $ac) {
    $title    = $slide['title'] ?? 'Presentation';
    $subtitle = $slide['subtitle'] ?? '';
    $f        = ds_parse_title_fields($slide['content'] ?? array());
    $id       = 2;

    $shapes  = ds_title_header_shapes($id, $f, $tc, $bc, $ac, 800000, 900000, 10592000, 'ctr');
    $title_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($title, 5600, $tc, true) . '</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 1800000, 11277600, 1800000, $title_p, 'ctr', 'ctr');
    $shapes .= ds_filled_rect($id++, 'AccentLine', 5596000, 3900000, 1000000, 60000, $ac);
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="ctr"/>' . ds_run($subtitle, 2000, $bc) . '</a:p>';
        $shapes .= ds_text_shape($id++, 'Subtitle', 800000, 4200000, 10592000, 900000, $sub_p, 'ctr', 't');
    }
    $shapes .= ds_title_footer_shapes($id, $f, $tc, $bc, $ac, 800000, 5300000, 10592000, 'ctr', 6500000);
    return $shapes;
}

if ( ! function_exists( 'ds_render_bullet' ) ) {
function ds_render_bullet($slide, $tc, $bc, $ac, $icon_rids = array()) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = $slide['content'] ?? array();
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $default_icons = array('check','star','trending','target','lightbulb','rocket');

    $shapes = ds_filled_rect(2, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $id = 3;

    $title_p = '<a:p><a:pPr algn="l"/>'.ds_run($title, 3200, $tc, true).'</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 200000, 11277600, 780000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>'.ds_run($subtitle, 1700, $ac).'</a:p>';
        $shapes .= ds_text_shape($id++, 'Subtitle', 457200, 1000000, 11277600, 480000, $sub_p, 'l', 't');
    }

    $content_y = 1700000;
    $row_h     = 820000;   // fits 6 rows within slide height
    $icon_size = 500000;
    $icon_x    = 457200;
    $text_x    = $icon_x + $icon_size + 260000;
    $text_w    = 11277600 - $icon_size - 320000;

    foreach (array_slice($content, 0, 6) as $i => $item) {
        $item   = (string)$item;
        $colon  = strpos($item, ':');
        $heading     = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $explanation = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);
        $row_y  = $content_y + $i * $row_h;

        // Icon circle background + icon image or emoji fallback
        $icon_y = $row_y + (int)(($row_h - $icon_size) / 2) - 60000;
        if ( isset($icon_rids[$i]) ) {
            $shapes .= ds_icon_pic($id, 'BulletIco'.$i, $icon_x, $icon_y, $icon_size, $icon_size, $icon_rids[$i], $ac, 70000);
            $id += 2;
        } else {
            $shapes .= ds_ellipse($id++, 'IconBg'.$i, $icon_x, $icon_y, $icon_size, $icon_size, $ac, 18000);
            $icon_name = $default_icons[$i % count($default_icons)];
            $sym = isset($symbols[$icon_name]) ? $symbols[$icon_name] : '●';
            $sym_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($sym, 2000, $ac, false, false, 'Segoe UI Emoji').'</a:p>';
            $shapes .= ds_text_shape($id++, 'IconSym'.$i, $icon_x, $icon_y, $icon_size, $icon_size, $sym_p, 'ctr', 'ctr');
        }

        // Heading bold + explanation text
        $txt_xml = '';
        if ($heading) {
            $txt_xml .= '<a:p><a:pPr algn="l"/>'.ds_run($heading, 1800, $tc, true).'</a:p>';
        }
        if ($explanation) {
            $txt_xml .= '<a:p><a:pPr algn="l"><a:spcBef><a:spcPts val="80"/></a:spcBef></a:pPr>'.ds_run($explanation, 1480, $bc).'</a:p>';
        }
        if (!$heading && !$explanation) {
            $txt_xml .= '<a:p><a:pPr algn="l"/>'.ds_run($item, 1600, $bc).'</a:p>';
        }
        $shapes .= ds_text_shape($id++, 'BulletTxt'.$i, $text_x, $row_y + 20000, $text_w, $row_h - 20000, $txt_xml, 'l', 't');
    }
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_two_column' ) ) {
function ds_render_two_column($slide, $tc, $bc, $ac, $icon_rids = array()) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 2);
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $icons_row = array('lightbulb','chart');

    $id = 2;
    $shapes = ds_filled_rect($id++, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $title_p = '<a:p><a:pPr algn="l"/>'.ds_run($title, 3200, $tc, true).'</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 200000, 11277600, 780000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>'.ds_run($subtitle, 1700, $ac).'</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 457200, 1000000, 11277600, 480000, $sub_p, 'l', 't');
    }

    $col_w     = 5410200;
    $col_gap   = 457200;
    $card_y    = 1650000;
    $card_h    = 4900000;
    $col_x     = array(457200, 457200 + $col_w + $col_gap);

    foreach (array_slice($content, 0, 2) as $i => $item) {
        $item   = (string)$item;
        $cx     = $col_x[$i];
        $colon  = strpos($item, ':');
        $heading     = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $explanation = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $card_y, $col_w, $card_h, $ac, 8000, 3500);

        $icon_sz = 700000;
        $icon_cx = $cx + (int)(($col_w - $icon_sz) / 2);
        $icon_cy = $card_y + 350000;
        if ( isset($icon_rids[$i]) ) {
            $shapes .= ds_icon_pic($id, 'TwoIco'.$i, $icon_cx, $icon_cy, $icon_sz, $icon_sz, $icon_rids[$i], $ac, 90000);
            $id += 2;
        } else {
            $shapes .= ds_ellipse($id++, 'IconBg'.$i, $icon_cx, $icon_cy, $icon_sz, $icon_sz, $ac, 40000);
            $icon_name = $icons_row[$i] ?? 'star';
            $sym = isset($symbols[$icon_name]) ? $symbols[$icon_name] : '●';
            $sym_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($sym, 3200, $ac, false, false, 'Segoe UI Emoji').'</a:p>';
            $shapes .= ds_text_shape($id++, 'IconSym'.$i, $icon_cx, $icon_cy, $icon_sz, $icon_sz, $sym_p, 'ctr', 'ctr');
        }

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($heading, 2000, $tc, true).'</a:p>';
            $shapes .= ds_text_shape($id++, 'Head'.$i, $cx + 160000, $card_y + $icon_sz + 560000, $col_w - 320000, 600000, $h_p, 'ctr', 't');
        }
        if ($explanation) {
            $d_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($explanation, 1450, $bc).'</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $cx + 160000, $card_y + $icon_sz + 1220000, $col_w - 320000, 2700000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_three_column' ) ) {
function ds_render_three_column($slide, $tc, $bc, $ac, $icon_rids = array()) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 3);
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $icons_row = array('rocket','users','target');

    $id = 2;
    $shapes = ds_filled_rect($id++, 'AccentBar', 0, 0, 12192000, 60000, $ac);
    $title_p = '<a:p><a:pPr algn="l"/>'.ds_run($title, 3200, $tc, true).'</a:p>';
    $shapes .= ds_text_shape($id++, 'Title', 457200, 200000, 11277600, 780000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>'.ds_run($subtitle, 1700, $ac).'</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 457200, 1000000, 11277600, 480000, $sub_p, 'l', 't');
    }

    $col_w   = 3454400;
    $col_gap = 457200;
    $card_y  = 1650000;
    $card_h  = 4900000;
    $col_x   = array(457200, 457200 + $col_w + $col_gap, 457200 + 2*($col_w + $col_gap));

    foreach (array_slice($content, 0, 3) as $i => $item) {
        $item  = (string)$item;
        $cx    = $col_x[$i];
        $colon = strpos($item, ':');
        $heading     = $colon !== false ? trim(substr($item, 0, $colon)) : '';
        $explanation = $colon !== false ? trim(substr($item, $colon + 1)) : trim($item);

        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $card_y, $col_w, $card_h, $ac, 8000, 3500);

        $icon_sz = 620000;
        $icon_cx = $cx + (int)(($col_w - $icon_sz) / 2);
        $icon_cy = $card_y + 300000;
        if ( isset($icon_rids[$i]) ) {
            $shapes .= ds_icon_pic($id, 'ThreeIco'.$i, $icon_cx, $icon_cy, $icon_sz, $icon_sz, $icon_rids[$i], $ac, 80000);
            $id += 2;
        } else {
            $shapes .= ds_ellipse($id++, 'IconBg'.$i, $icon_cx, $icon_cy, $icon_sz, $icon_sz, $ac, 40000);
            $icon_name = $icons_row[$i] ?? 'star';
            $sym = isset($symbols[$icon_name]) ? $symbols[$icon_name] : '●';
            $sym_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($sym, 2800, $ac, false, false, 'Segoe UI Emoji').'</a:p>';
            $shapes .= ds_text_shape($id++, 'IconSym'.$i, $icon_cx, $icon_cy, $icon_sz, $icon_sz, $sym_p, 'ctr', 'ctr');
        }

        if ($heading) {
            $h_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($heading, 1900, $tc, true).'</a:p>';
            $shapes .= ds_text_shape($id++, 'Head'.$i, $cx + 160000, $card_y + $icon_sz + 420000, $col_w - 320000, 950000, $h_p, 'ctr', 't');
        }
        if ($explanation) {
            $d_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($explanation, 1400, $bc).'</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $cx + 160000, $card_y + $icon_sz + 1410000, $col_w - 320000, 2530000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_image_text' ) ) {
function ds_render_image_text($slide, $tc, $bc, $ac, $image_rid = null, $icon_rids = array(), $img_pos = null, $img_w = 0, $img_h = 0) {
    $title    = $slide['title'] ?? '';
    $subtitle = $slide['subtitle'] ?? '';
    $content  = array_slice($slide['content'] ?? array(), 0, 4);
    $symbols  = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();
    $def_icons = array('check','star','trending','target');

    $id = 2;
    $title_p = '<a:p><a:pPr algn="l"/>'.ds_run($title, 3200, $tc, true).'</a:p>';
    $shapes  = ds_text_shape($id++, 'Title', 457200, 200000, 11277600, 780000, $title_p, 'l', 't');
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>'.ds_run($subtitle, 1700, $ac).'</a:p>';
        $shapes .= ds_text_shape($id++, 'Sub', 457200, 1020000, 5600000, 480000, $sub_p, 'l', 't');
    }

    $it_y      = 1900000;  // start lower to clear subtitle
    $it_row_h  = 1020000;  // taller rows - no overlap
    $it_icsz   = 520000;
    $it_icx    = 457200;
    $it_txtx   = $it_icx + $it_icsz + 240000;
    $it_txtw   = 5400000 - $it_icsz - 300000;

    foreach ($content as $ii => $b) {
        $b = (string)$b;
        $colon = strpos($b, ':');
        $bh = $colon !== false ? trim(substr($b, 0, $colon)) : '';
        $bd = $colon !== false ? trim(substr($b, $colon + 1)) : trim($b);
        $by = $it_y + $ii * $it_row_h;
        $icy = $by + (int)(($it_row_h - $it_icsz) / 2) - 40000;

        if ( isset($icon_rids[$ii]) ) {
            $shapes .= ds_icon_pic($id, 'ItIco'.$ii, $it_icx, $icy, $it_icsz, $it_icsz, $icon_rids[$ii], $ac, 55000);
            $id += 2;
        } else {
            $shapes .= ds_ellipse($id++, 'ItIcBg'.$ii, $it_icx, $icy, $it_icsz, $it_icsz, $ac, 20000);
            $iname = $def_icons[$ii % 4];
            $isym = isset($symbols[$iname]) ? $symbols[$iname] : '●';
            $sp = '<a:p><a:pPr algn="ctr"/>'.ds_run($isym, 2000, $ac, false, false, 'Segoe UI Emoji').'</a:p>';
            $shapes .= ds_text_shape($id++, 'ItIcSy'.$ii, $it_icx, $icy, $it_icsz, $it_icsz, $sp, 'ctr', 'ctr');
        }
        $txt = '';
        if ($bh) $txt .= '<a:p><a:pPr algn="l"/>'.ds_run($bh, 1700, $tc, true).'</a:p>';
        if ($bd) $txt .= '<a:p><a:pPr algn="l"><a:spcBef><a:spcPts val="80"/></a:spcBef></a:pPr>'.ds_run($bd, 1380, $bc).'</a:p>';
        $shapes .= ds_text_shape($id++, 'ItTxt'.$ii, $it_txtx, $by + 40000, $it_txtw, $it_row_h - 40000, $txt, 'l', 't');
    }

    $img_x = 6300000; $img_y = 1500000; $img_cx = 5435000; $img_cy = 5000000;
    if ($image_rid) {
        $pic_xml = '<p:pic>'.            '<p:nvPicPr><p:cNvPr id="'.$id.'" name="AIImage"/>'.            '<p:cNvPicPr><a:picLocks noChangeAspect="0"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'.            '<p:blipFill><a:blip r:embed="'.$image_rid.'"/>'.ds_ppt_blip_fill_crop($img_pos,$img_w,$img_h,5435000,5000000).'</p:blipFill>'.            '<p:spPr><a:xfrm><a:off x="'.$img_x.'" y="'.$img_y.'"/><a:ext cx="'.$img_cx.'" cy="'.$img_cy.'"/></a:xfrm>'.            '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 3000"/></a:avLst></a:prstGeom></p:spPr>'.            '</p:pic>';
        return $shapes.$pic_xml;
    }
    $shapes .= ds_rounded_rect($id++, 'ImgPlaceholder', $img_x, $img_y, $img_cx, $img_cy, $ac, 20000);
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_feature_grid' ) ) {
function ds_render_feature_grid($slide, $tc, $bc, $ac, $icon_rids = array()) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 4);
    while (count($items) < 4) $items[] = '';

    $slide_icon = trim((string)($slide['icon'] ?? ''));
    $card_icons = isset($slide['icons']) && is_array($slide['icons']) ? $slide['icons'] : array();
    $default_icons = array('lightbulb','chart','users','target');
    $symbols = defined('DS_PPT_ICON_SYMBOLS') ? DS_PPT_ICON_SYMBOLS : array();

    $subtitle = $slide['subtitle'] ?? '';
    $title_p = '<a:p><a:pPr algn="l"/>'.ds_run($title, 3200, $tc, true).'</a:p>';
    $title_box = ds_text_shape(2, 'Title', 457200, 200000, 11277600, 780000, $title_p, 'l', 't');
    $sub_shapes = '';
    if ($subtitle) {
        $sub_p = '<a:p><a:pPr algn="l"/>'.ds_run($subtitle, 1700, $ac).'</a:p>';
        $sub_shapes = ds_text_shape(99, 'Sub', 457200, 1000000, 11277600, 480000, $sub_p, 'l', 't');
    }

    $grid_x = 457200; $grid_y = 1650000;
    $cell_w = 5410200; $cell_h = 2225400; $gap = 457200;
    $shapes = $title_box;
    $id = 3;

    for ($i = 0; $i < 4; $i++) {
        $col = $i % 2; $row = (int)($i / 2);
        $cx = $grid_x + $col * ($cell_w + $gap);
        $cy = $grid_y + $row * ($cell_h + $gap);

        // 1) Card background
        $shapes .= ds_rounded_rect($id++, 'Card'.$i, $cx, $cy, $cell_w, $cell_h, $ac, 10000, 3500);

        // 2) Icon circle
        $tile_size = 520000;
        $tile_x = $cx + 280000;
        $tile_y = $cy + 220000;
        if ( isset($icon_rids[$i]) ) {
            $shapes .= ds_icon_pic($id, 'FgIco'.$i, $tile_x, $tile_y, $tile_size, $tile_size, $icon_rids[$i], $ac, 60000);
            $id += 2;
        } else {
            $shapes .= ds_ellipse($id++, 'IconTile'.$i, $tile_x, $tile_y, $tile_size, $tile_size, $ac);

        // 3) Per-card icon: prefer icons[i], fall back to slide_icon, then default
        $icon_name = '';
        if ( isset($card_icons[$i]) ) $icon_name = trim((string)$card_icons[$i]);
        if ( $icon_name === '' || ! isset($symbols[$icon_name]) ) $icon_name = $slide_icon;
        if ( $icon_name === '' || ! isset($symbols[$icon_name]) ) $icon_name = $default_icons[$i];
        if ( ! isset($symbols[$icon_name]) ) $icon_name = 'star';
        $symbol = $symbols[$icon_name];
        $sym_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($symbol, 2800, 'FFFFFF', true, false, 'Arial').'</a:p>';
            $shapes .= ds_text_shape($id++, 'IconSym'.$i, $tile_x, $tile_y, $tile_size, $tile_size, $sym_p, 'ctr', 'ctr');
        }

        // 4) Heading + (bigger) description
        $item = (string) $items[$i];
        $parts = explode(':', $item, 2);
        $heading = trim($parts[0]);
        $desc = isset($parts[1]) ? trim($parts[1]) : '';
        $head_x = $cx + 280000;
        $head_y = $tile_y + $tile_size + 110000;
        $head_w = $cell_w - 560000;
        $h_p = '<a:p><a:pPr algn="l"/>'.ds_run($heading, 1900, $tc, true).'</a:p>';
        $shapes .= ds_text_shape($id++, 'CardH'.$i, $head_x, $head_y, $head_w, 340000, $h_p, 'l', 't');
        if ($desc) {
            $desc_y = $head_y + 360000;
            $d_p = '<a:p><a:pPr algn="l"/>'.ds_run($desc, 1300, $bc).'</a:p>';
            $shapes .= ds_text_shape($id++, 'CardD'.$i, $head_x, $desc_y, $head_w, 865400, $d_p, 'l', 't');
        }
    }
    return $sub_shapes.$shapes;
}
}

if ( ! function_exists( 'ds_render_stats' ) ) {
function ds_render_stats($slide, $tc, $bc, $ac) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 3);
    while (count($items) < 3) $items[] = '';
    $title_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($title, 3600, $tc, true).'</a:p>';
    $title_box = ds_text_shape(2, 'Title', 457200, 600000, 11277600, 900000, $title_p, 'ctr', 't');
    $shapes = $title_box;
    $id = 3;
    $col_w = 3759200;
    $start_x = 457200;
    $stat_y = 2400000;
    $label_y = 4400000;
    for ($i = 0; $i < 3; $i++) {
        $cx = $start_x + $i * $col_w;
        $item = (string) $items[$i];
        $parts = explode(':', $item, 2);
        $num = trim($parts[0]);
        $lbl = isset($parts[1]) ? trim($parts[1]) : '';
        $num_len = function_exists('mb_strlen') ? mb_strlen($num) : strlen($num);
        if ($num_len <= 3)      $num_sz = 8000;
        elseif ($num_len <= 5)  $num_sz = 6000;
        elseif ($num_len <= 7)  $num_sz = 4400;
        else                    $num_sz = 3400;
        $n_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($num, $num_sz, $ac, true).'</a:p>';
        $shapes .= ds_text_shape($id++, 'Stat'.$i, $cx, $stat_y, $col_w, 1800000, $n_p, 'ctr', 'ctr');
        if ($lbl) {
            $l_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($lbl, 1400, $bc).'</a:p>';
            $shapes .= ds_text_shape($id++, 'Lbl'.$i, $cx + 200000, $label_y, $col_w - 400000, 1500000, $l_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_timeline' ) ) {
function ds_render_timeline($slide, $tc, $bc, $ac) {
    $title = $slide['title'] ?? '';
    $items = array_slice($slide['content'] ?? array(), 0, 8);
    $step_sz = count($items) <= 5 ? 2000 : 1500;
    $desc_sz  = count($items) <= 5 ? 1300 : 1050;
    $count = count($items);
    if ($count === 0) $count = 1;
    $title_p = '<a:p><a:pPr algn="l"/>'.ds_run($title, 3600, $tc, true).'</a:p>';
    $title_box = ds_text_shape(2, 'Title', 457200, 400000, 11277600, 900000, $title_p, 'l', 't');
    $line_y = 3800000;
    $line_x1 = 1100000;
    $line_x2 = 11092000;
    $line_w = $line_x2 - $line_x1;
    $line = ds_filled_rect(3, 'TimelineLine', $line_x1, $line_y, $line_w, 30000, $ac);
    $shapes = $title_box.$line;
    $id = 4;
    $dot_size = 280000;
    for ($i = 0; $i < $count; $i++) {
        $t = $count === 1 ? 0.5 : $i / ($count - 1);
        $dot_cx = $line_x1 + (int)($t * $line_w);
        $dot_x = $dot_cx - (int)($dot_size/2);
        $dot_y = $line_y + 15000 - (int)($dot_size/2);
        $shapes .= ds_ellipse($id++, 'Dot'.$i, $dot_x, $dot_y, $dot_size, $dot_size, $ac);
        $item = (string) ($items[$i] ?? '');
        $parts = explode(':', $item, 2);
        $step = trim($parts[0]);
        $desc = isset($parts[1]) ? trim($parts[1]) : '';
        $box_w = 1800000;
        $box_x = $dot_cx - (int)($box_w/2);
        $s_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($step, $step_sz, $tc, true).'</a:p>';
        $shapes .= ds_text_shape($id++, 'Step'.$i, $box_x, $line_y - 1100000, $box_w, 600000, $s_p, 'ctr', 'b');
        if ($desc) {
            $d_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($desc, $desc_sz, $bc).'</a:p>';
            $shapes .= ds_text_shape($id++, 'Desc'.$i, $box_x, $line_y + 350000, $box_w, 1500000, $d_p, 'ctr', 't');
        }
    }
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_quote' ) ) {
function ds_render_quote($slide, $tc, $bc, $ac) {
    $quote = $slide['title'] ?? '';
    $content = $slide['content'] ?? array();
    $attribution = !empty($content[0]) ? $content[0] : '';
    $mark_p = '<a:p><a:pPr algn="ctr"/>'.ds_run('"', 16000, $ac, true).'</a:p>';
    $mark = ds_text_shape(2, 'QuoteMark', 457200, 600000, 11277600, 1400000, $mark_p, 'ctr', 'ctr');
    $q_p = '<a:p><a:pPr algn="ctr"/>'.ds_run($quote, 3400, $tc, false, true).'</a:p>';
    $quote_box = ds_text_shape(3, 'Quote', 800000, 2400000, 10592000, 2400000, $q_p, 'ctr', 'ctr');
    $shapes = $mark.$quote_box;
    if ($attribution) {
        $a_p = '<a:p><a:pPr algn="ctr"/>'.ds_run(DS_PPT_EMDASH.' '.$attribution, 1900, $ac, true).'</a:p>';
        $shapes .= ds_text_shape(4, 'Attribution', 800000, 5300000, 10592000, 600000, $a_p, 'ctr', 't');
    }
    return $shapes;
}
}

if ( ! function_exists( 'ds_render_slide_body' ) ) {
function ds_render_slide_body($type, $slide, $tc, $bc, $ac, $bg, $image_rid = null, $icon_rids = array(), $img_pos = null, $img_w = 0, $img_h = 0) {
    switch ($type) {
        case 'title':        return ds_render_title($slide, $tc, $bc, $ac);
        case 'two_column':   return ds_render_two_column($slide, $tc, $bc, $ac, $icon_rids);
        case 'three_column': return ds_render_three_column($slide, $tc, $bc, $ac, $icon_rids);
        case 'image_text':   return ds_render_image_text($slide, $tc, $bc, $ac, $image_rid, $icon_rids, $img_pos ?? null, $img_w ?? 0, $img_h ?? 0);
        case 'feature_grid': return ds_render_feature_grid($slide, $tc, $bc, $ac, $icon_rids);
        case 'five_point':
case 'six_point':
case 'seven_point':
case 'eight_point': {
$dsv_variant = (isset($slide['variant']) && preg_match('/^v[1-5]$/', (string)$slide['variant'])) ? $slide['variant'] : 'v1';
$dsv_fn = 'ds_pptv2_' . $type . '_' . $dsv_variant;
update_option('_dsv_debug_last', array('type'=>$type,'raw_variant'=>(isset($slide['variant'])?$slide['variant']:null),'resolved'=>$dsv_variant,'fn'=>$dsv_fn,'fn_exists'=>(function_exists($dsv_fn)?1:0),'slide_keys'=>array_keys($slide)), false);
if (function_exists($dsv_fn)) return $dsv_fn($slide, $tc, $bc, $ac, $image_rid, $icon_rids);
$dsv_fallback = 'ds_pptv2_' . $type . '_v1';
if (function_exists($dsv_fallback)) return $dsv_fallback($slide, $tc, $bc, $ac, null, $icon_rids);
return ds_render_feature_grid($slide, $tc, $bc, $ac, $icon_rids);
}
case 'stats':        return ds_render_stats($slide, $tc, $bc, $ac);
        case 'timeline':     return ds_render_timeline($slide, $tc, $bc, $ac);
        case 'quote':        return ds_render_quote($slide, $tc, $bc, $ac);
        case 'bullet':
        default:             return ds_render_bullet($slide, $tc, $bc, $ac, $icon_rids);
    }
}
}
