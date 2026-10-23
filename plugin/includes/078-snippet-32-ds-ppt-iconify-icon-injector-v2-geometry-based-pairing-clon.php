<?php
/**
 * Code Snippets export — Snippet 32 — DS PPT Iconify Icon Injector (v2 — geometry-based pairing) [CLONE] [mdi-only icon lookup + guaranteed fallback]
 *
 * Snippet ID : 762
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-076
 * Modified   : 2026-09-11 14:19:50
 * Load order : 078 of 144
 */
/**
 * Snippet 32 — DS PPT Iconify Icon Injector (v2 — geometry-based pairing)
 * Priority: 1005
 */
if ( ! defined('ABSPATH') ) exit;

add_filter('ds30_filled_pptx_bytes', 'ds32_inject_icons', 10, 3);

/* The deck can be produced by three different pipelines. Previously only the
   single-template one ran the icon injector, so template and combined decks
   shipped with raw emoji in the text. Hook the same injector onto the others. */
add_filter('ds_tpl_filled_pptx_bytes', 'ds32_inject_icons', 10, 3);
add_filter('ds_combined_pptx_bytes',   'ds32_inject_icons', 10, 3);
if ( ! function_exists('ds32_inject_icons') ) {
// $ai_json and $api_k default so callers that pass fewer args (the template
// and combined-deck pipelines pass only the bytes + a slot map) do not raise
// ArgumentCountError, which PHP turns into a fatal and REST returns as a 500.
function ds32_inject_icons( $pptx_bytes, $ai_json = array(), $api_k = '' ) {
    if ( empty($pptx_bytes) || strlen($pptx_bytes) < 100 ) return $pptx_bytes;

    /* DS32-FIX — icons were missing on marketplace-template downloads.
       The template and combined-deck pipelines call this filter with an
       empty API key (and, for combined decks, an empty slot list), so the
       keyword lookup returned nothing and every "Icon N" placeholder was
       left as raw text. Resolve the key here, and accept either slot shape. */
    if ( ! is_string($api_k) || $api_k === '' ) {
        $api_k = ds32_resolve_groq_key();
    }
    $ai_json = ds32_normalise_slots( $ai_json );

    error_log('DS32: filter fired, pptx size=' . strlen($pptx_bytes));

    $tmp = tempnam(sys_get_temp_dir(), 'ds32_') . '.pptx';
    file_put_contents($tmp, $pptx_bytes);

    $zip = new ZipArchive();
    if ( $zip->open($tmp) !== true ) {
        @unlink($tmp);
        error_log('DS32: failed to open zip');
        return $pptx_bytes;
    }

    $slide_files = [];
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $name = $zip->getNameIndex($i);
        if ( preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name) ) {
            $slide_files[$name] = $zip->getFromName($name);
        }
    }
    $zip->close();

    if ( empty($slide_files) ) {
        @unlink($tmp);
        error_log('DS32: no slides found');
        return $pptx_bytes;
    }

    // ── Build a set of "heading-like" replacement texts from ai_json ──────────
    // (used only to decide which shapes on the slide count as candidate headings;
    //  actual pairing happens per-slide using real coordinates below)
    $heading_texts = [];
    foreach ( (array)$ai_json as $slot ) {
        $slot_name = (string)($slot['slot_name'] ?? '');
        $repl      = trim((string)($slot['replacement'] ?? ''));
        if ( $repl === '' ) continue;
        if ( preg_match('/^(?:Icon|IconSym|ISy|ItIcSy|IcSy)\s*\d+$/i', trim($slot_name)) ) continue; // never treat icon slots as headings
        if ( preg_match('/^(title|title\s*header|main\s*title|sub\s*header|subtitle|sub\s*title)$/i', trim($slot_name)) ) continue; // exclude slide title/subtitle
        // Heading slots: short text (title-like), OR explicit "Point N Heading" naming
        $is_named_heading = preg_match('/point\s*\d+\s*(heading|title|name)|step\s*\d+\s*(heading|title|name)/i', $slot_name);
        $is_short          = mb_strlen($repl) <= 40;
        if ( $is_named_heading || $is_short ) {
            $heading_texts[] = $repl;
        }
    }
    $heading_texts = array_values(array_unique($heading_texts));

    /* DS32-FIX — the combined-deck pipeline passes no slot map at all.
       Instead of bailing out (which left that deck with no icons), fall back
       to treating short on-slide text as the heading candidates. */
    $headings_from_slide = empty($heading_texts);
    if ( $headings_from_slide ) {
        error_log('DS32: no heading texts in slot map — falling back to on-slide short text');
    }

    // ── Process each slide: find icon shapes + heading shapes, pair by distance ─
    $new_slide_files = [];
    $new_media       = [];
    $new_rels        = [];
    $media_counter   = 200;
    $icons_injected  = 0;

    // First pass: find icon shapes + heading shapes with coordinates, per slide
    $slide_icon_shapes   = []; // slide_name => [ [icon_num, offset, length, x, y, cx, cy, full_xml], ... ]
    $slide_heading_shapes= []; // slide_name => [ [text, cx_center, cy_center], ... ]

    foreach ( $slide_files as $slide_name => $slide_xml ) {
        preg_match_all('/<p:sp\b[^>]*>.*?<\/p:sp>/s', $slide_xml, $sp_matches, PREG_OFFSET_CAPTURE);
        // Absolute (slide-space) centres, resolved through group transforms.
        $abs_centres  = ds32_abs_centres( $slide_xml );
        $icon_list    = [];
        $heading_list = [];

        foreach ( $sp_matches[0] as $sp_match ) {
            $sp_full   = $sp_match[0];
            $sp_offset = $sp_match[1];

            preg_match_all('/<a:t(?:\s[^>]*)?>([^<]*)<\/a:t>/s', $sp_full, $t_matches);
            $text = trim(implode(' ', $t_matches[1]));
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if ( $text === '' ) continue;

            if ( ! preg_match('/<a:off\s+x="(-?\d+)"\s+y="(-?\d+)"/s', $sp_full, $off_m) ) continue;
            if ( ! preg_match('/<a:ext\s+cx="(\d+)"\s+cy="(\d+)"/s', $sp_full, $ext_m) ) continue;
            $x  = (int)$off_m[1];
            $y  = (int)$off_m[2];
            $cx = (int)$ext_m[1];
            $cy = (int)$ext_m[2];
            if ( $cx === 0 || $cy === 0 ) continue;

            // Raw x/y stay as-is — the replacement <p:pic> sits in the same
            // parent group, so it must keep child-space coordinates.
            // Only the PAIRING centre becomes absolute.
            $cxc = $x + $cx / 2;
            $cyc = $y + $cy / 2;
            if ( preg_match('/<p:cNvPr\s+id="(\d+)"/', $sp_full, $id_m) ) {
                $shape_id = (int) $id_m[1];
                if ( isset( $abs_centres[ $shape_id ] ) ) {
                    $cxc = $abs_centres[ $shape_id ]['cxc'];
                    $cyc = $abs_centres[ $shape_id ]['cyc'];
                }
            }

            if ( preg_match('/^(?:Icon|IconSym|ISy|ItIcSy|IcSy)\s*(\d+)$/i', $text, $icon_m) ) {
                $icon_list[] = [
                    'num' => (int)$icon_m[1], 'offset' => $sp_offset, 'length' => strlen($sp_full),
                    'x' => $x, 'y' => $y, 'cx' => $cx, 'cy' => $cy, 'full' => $sp_full,
                    'cxc' => $cxc, 'cyc' => $cyc,
                ];
            } elseif ( in_array($text, $heading_texts, true)
                        || ( $headings_from_slide && mb_strlen($text) <= 40 ) ) {
                $heading_list[] = ['text' => $text, 'cxc' => $cxc, 'cyc' => $cyc];
            }
        }

        if ( ! empty($icon_list) ) $slide_icon_shapes[$slide_name] = $icon_list;
        if ( ! empty($heading_list) ) $slide_heading_shapes[$slide_name] = $heading_list;
    }

    if ( empty($slide_icon_shapes) ) {
        @unlink($tmp);
        error_log('DS32: no Icon N shapes found on any slide');
        return $pptx_bytes;
    }

    // ── Pair each icon with its nearest heading (per slide, Euclidean distance) ─
    // icon_num => heading text
    $icon_num_to_heading = [];
    foreach ( $slide_icon_shapes as $slide_name => $icons ) {
        $headings_here = $slide_heading_shapes[$slide_name] ?? [];
        if ( empty($headings_here) ) continue;
        foreach ( $icons as $icon ) {
            $best_dist = null; $best_text = null;
            foreach ( $headings_here as $h ) {
                $dist = sqrt( pow($h['cxc'] - $icon['cxc'], 2) + pow($h['cyc'] - $icon['cyc'], 2) );
                if ( $best_dist === null || $dist < $best_dist ) {
                    $best_dist = $dist; $best_text = $h['text'];
                }
            }
            if ( $best_text !== null ) {
                $icon_num_to_heading[$icon['num']] = $best_text;
            }
        }
    }

    error_log('DS32: icon-to-heading pairing=' . json_encode($icon_num_to_heading));

    if ( empty($icon_num_to_heading) ) {
        @unlink($tmp);
        return $pptx_bytes;
    }

    // ── Get icon keywords from Groq, keyed by icon number ──────────────────────
    $icon_keywords = ds32_get_icon_keywords($icon_num_to_heading, $api_k);
    error_log('DS32: keywords=' . json_encode($icon_keywords));

    if ( empty($icon_keywords) ) {
        @unlink($tmp);
        return $pptx_bytes;
    }

    // ── Build replacement <p:pic> XML per slide, using previously located shapes ─
    foreach ( $slide_icon_shapes as $slide_name => $icons ) {
        $slide_xml    = $slide_files[$slide_name];
        $replacements = [];

        foreach ( $icons as $icon ) {
            $icon_num = $icon['num'];
            $keyword  = $icon_keywords[$icon_num] ?? null;
            if ( ! $keyword ) continue;

            $icon_color = ds32_pick_icon_color($icon['full']);
            $svg = ds32_fetch_svg($keyword, $icon_color);
            if ( ! $svg ) {
                error_log('DS32: no SVG for keyword=' . $keyword);
                continue;
            }

            $media_counter++;
            // Store the SVG file (the real icon — recolorable, sharp at any zoom)
            $svg_filename = 'ds32icon' . $media_counter . '.svg';
            $new_media[ 'ppt/media/' . $svg_filename ] = $svg;

            // Convert SVG to PNG using GD (works on shared hosting).
            // The PNG is only the OOXML-required fallback for PowerPoint < 2016.
            $png_bytes = ds32_svg_to_png($svg, 128);

            // If PNG conversion failed, use colored circle fallback
            if ( empty($png_bytes) ) {
                $png_bytes = ds32_transparent_png($keyword, $icon_color);
            }

            $png_filename = 'ds32icon' . $media_counter . '.png';
            $new_media[ 'ppt/media/' . $png_filename ] = $png_bytes;

            // Two relationships: PNG fallback + SVG
            $rId_png = 'rId9' . $media_counter . 'p';
            $rId_svg = 'rId9' . $media_counter . 's';
            if ( ! isset($new_rels[$slide_name]) ) $new_rels[$slide_name] = [];
            $new_rels[$slide_name][$rId_png] = [
                'file' => $png_filename,
                'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image',
            ];
            $new_rels[$slide_name][$rId_svg] = [
                'file' => $svg_filename,
                'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image',
            ];

            // Correct OOXML SVG structure:
            // blip points to PNG fallback; asvg:svgBlip inside extLst points to SVG.
            // This is the only way PowerPoint 2016+ renders SVG in a slide.
            $pic_xml = '<p:pic>'
                . '<p:nvPicPr>'
                . '<p:cNvPr id="' . $media_counter . '" name="Icon' . $icon_num . '"/>'
                . '<p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr>'
                . '<p:nvPr/>'
                . '</p:nvPicPr>'
                . '<p:blipFill>'
                . '<a:blip r:embed="' . $rId_png . '">'
                . '<a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}">'
                . '<asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="' . $rId_svg . '"/>'
                . '</a:ext></a:extLst>'
                . '</a:blip>'
                . '<a:stretch><a:fillRect/></a:stretch>'
                . '</p:blipFill>'
                . '<p:spPr>'
                . '<a:xfrm><a:off x="' . $icon['x'] . '" y="' . $icon['y'] . '"/>'
                . '<a:ext cx="' . $icon['cx'] . '" cy="' . $icon['cy'] . '"/></a:xfrm>'
                . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
                . '</p:spPr>'
                . '</p:pic>';

            $replacements[] = [
                'offset' => $icon['offset'],
                'length' => $icon['length'],
                'new'    => $pic_xml,
            ];
            $icons_injected++;
        }

        if ( ! empty($replacements) ) {
            usort($replacements, fn($a, $b) => $b['offset'] - $a['offset']);
            foreach ( $replacements as $r ) {
                $slide_xml = substr_replace($slide_xml, $r['new'], $r['offset'], $r['length']);
            }
            $new_slide_files[$slide_name] = $slide_xml;
        }
    }

    error_log('DS32: icons_injected=' . $icons_injected . ' new_media=' . count($new_media));

    if ( empty($new_media) ) {
        @unlink($tmp);
        return $pptx_bytes;
    }

    // ── Rebuild PPTX ────────────────────────────────────────────────────────
    $zip_in = new ZipArchive();
    $zip_in->open($tmp);

    $tmp_out = tempnam(sys_get_temp_dir(), 'ds32out_') . '.pptx';
    $zip_out = new ZipArchive();
    $zip_out->open($tmp_out, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $content_types = $zip_in->getFromName('[Content_Types].xml');
    if ( ! strpos($content_types, 'image/svg+xml') ) {
        $content_types = str_replace(
            '</Types>',
            '<Default Extension="svg" ContentType="image/svg+xml"/></Types>',
            $content_types
        );
    }
    if ( ! strpos($content_types, 'image/png') ) {
        $content_types = str_replace(
            '</Types>',
            '<Default Extension="png" ContentType="image/png"/></Types>',
            $content_types
        );
    }

    for ( $i = 0; $i < $zip_in->numFiles; $i++ ) {
        $name = $zip_in->getNameIndex($i);

        if ( $name === '[Content_Types].xml' ) {
            $zip_out->addFromString($name, $content_types);
            continue;
        }

        if ( isset($new_slide_files[$name]) ) {
            $zip_out->addFromString($name, $new_slide_files[$name]);
            continue;
        }

        if ( preg_match('#^ppt/slides/_rels/slide(\d+)\.xml\.rels$#', $name, $rm) ) {
            $slide_key = 'ppt/slides/slide' . $rm[1] . '.xml';
            $rels_xml  = $zip_in->getFromName($name);
            if ( isset($new_rels[$slide_key]) ) {
                foreach ( $new_rels[$slide_key] as $rId => $rel_info ) {
                    $rel_type = $rel_info['type'];
                    $rel_file = $rel_info['file'];
                    $rels_xml = str_replace(
                        '</Relationships>',
                        '<Relationship Id="' . $rId . '" '
                        . 'Type="' . $rel_type . '" '
                        . 'Target="../media/' . $rel_file . '"/></Relationships>',
                        $rels_xml
                    );
                }
            }
            $zip_out->addFromString($name, $rels_xml);
            continue;
        }

        $zip_out->addFromString($name, $zip_in->getFromIndex($i));
    }

    foreach ( $new_media as $path => $bytes ) {
        $zip_out->addFromString($path, $bytes);
    }

    $zip_out->close();
    $zip_in->close();
    @unlink($tmp);

    $result = file_get_contents($tmp_out);
    @unlink($tmp_out);

    if ( empty($result) || strlen($result) < strlen($pptx_bytes) / 2 ) {
        error_log('DS32: rebuild failed, returning original');
        return $pptx_bytes;
    }

    error_log('DS32: success! result size=' . strlen($result));
    return $result;
}
}

if ( ! function_exists('ds32_pick_icon_color') ) {
function ds32_pick_icon_color( string $shape_xml ) : string {
    // The icon placeholder shape is transparent (no solidFill), so reading its
    // fill never works. Instead look at the NEAREST sibling circle/ellipse fill
    // which is the coloured background disc behind the icon. We receive the full
    // sp XML so search backwards from the icon position in the slide for the
    // closest preceding ellipse solidFill — that IS the circle colour.
    // If nothing found, default to white (icons sit on coloured circles).
    if ( preg_match('/<a:srgbClr val="([0-9A-Fa-f]{6})"/s', $shape_xml, $m ) ) {
        $hex = $m[1];
        $r = hexdec(substr($hex,0,2))/255;
        $g = hexdec(substr($hex,2,2))/255;
        $b = hexdec(substr($hex,4,2))/255;
        $lum = 0.2126*$r + 0.7152*$g + 0.0722*$b;
        return $lum < 0.5 ? 'FFFFFF' : '1F2937';
    }
    // No fill on the icon shape itself — it sits on a coloured circle.
    // Return white so the icon is visible on any coloured background disc.
    // Neither template has a coloured disc behind the icon placeholder, so
    // white renders invisible. Dark slate is visible on light backgrounds.
    // Override per-site with: add_filter('ds32_default_icon_color', fn() => 'RRGGBB');
    return apply_filters( 'ds32_default_icon_color', '1F2937' );
}
}

if ( ! function_exists('ds32_fetch_svg') ) {
function ds32_fetch_svg( string $keyword, string $color_hex = '4A90D9' ) : string {
    // Restrict every lookup to a single curated set (Material Design Icons)
    // so the whole deck stays visually consistent — mixing icon sets was
    // producing a mash of outline/filled styles, and some sets (arcticons,
    // academicons, brand logo packs) return literal text/wordmark glyphs
    // instead of pictograms (e.g. "risk" -> arcticons:risk, a text badge).
    $fallback_icons = ['mdi:lightbulb','mdi:chart-bar','mdi:target','mdi:check-circle','mdi:rocket-launch','mdi:star'];
    $fallback_pick  = $fallback_icons[ abs(crc32($keyword)) % count($fallback_icons) ];

    $search = wp_remote_get(
        'https://api.iconify.design/search?query=' . urlencode($keyword) . '&limit=1&prefixes=mdi',
        ['timeout' => 10]
    );
    if ( is_wp_error($search) ) {
        // Network hiccup — still return a real, on-brand icon rather than
        // leaving the original "Icon N" placeholder text visible.
        [$prefix, $name] = explode(':', $fallback_pick, 2);
    } else {
        $data  = json_decode(wp_remote_retrieve_body($search), true);
        $icons = $data['icons'] ?? [];
        if ( empty($icons) ) {
            // No MDI icon matched this keyword — fall back instead of
            // skipping the slot entirely.
            [$prefix, $name] = explode(':', $fallback_pick, 2);
        } else {
            [$prefix, $name] = explode(':', $icons[0], 2);
        }
    }

    $svg_resp = wp_remote_get(
        "https://api.iconify.design/{$prefix}/{$name}.svg?color=%23{$color_hex}&width=128&height=128",
        ['timeout' => 10]
    );
    if ( is_wp_error($svg_resp) ) return '';

    $svg = wp_remote_retrieve_body($svg_resp);
    if ( empty($svg) || strpos($svg, '<svg') === false ) return '';

    // Ensure SVG has proper XML declaration and namespace for PowerPoint
    if ( strpos($svg, '<?xml') === false ) {
        $svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n" . $svg;
    }
    // Add xmlns if missing
    if ( strpos($svg, 'xmlns=') === false ) {
        $svg = str_replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg"', $svg);
    }

    return $svg;
}
}
if ( ! function_exists('ds32_transparent_png') ) {
function ds32_transparent_png( string $keyword = '', string $color_hex = '4A90D9' ) : string {
    // Generate a real PNG fallback using GD so older PowerPoint versions
    // show something meaningful instead of a broken image
    if ( function_exists('imagecreatetruecolor') ) {
        $size = 128;
        $im   = imagecreatetruecolor($size, $size);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans);

        // Draw colored circle
        $r   = hexdec(substr($color_hex, 0, 2));
        $g   = hexdec(substr($color_hex, 2, 2));
        $b   = hexdec(substr($color_hex, 4, 2));
        $col = imagecolorallocate($im, $r, $g, $b);
        imagefilledellipse($im, $size/2, $size/2, $size-4, $size-4, $col);

        // Draw first letter of keyword
        if ( $keyword !== '' ) {
            $white = imagecolorallocate($im, 255, 255, 255);
            $char  = strtoupper(substr($keyword, 0, 1));
            imagestring($im, 5, $size/2 - 5, $size/2 - 8, $char, $white);
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);
        if ( $png && strlen($png) > 100 ) return $png;
    }

    // Absolute fallback — minimal valid 1x1 transparent PNG
    return base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR4nGNg' .
        'AAIAAAUAAXpeqz8AAAAASUVORK5CYII='
    );
}
}

if ( ! function_exists('ds32_svg_to_png') ) {
function ds32_svg_to_png( string $svg, int $size = 128 ) : string {
    // Method 1: Imagick (best quality, pure vector rasterization)
    if ( class_exists('Imagick') ) {
        try {
            $im = new Imagick();
            $im->setBackgroundColor( new ImagickPixel('transparent') );
            $im->setResolution( 144, 144 );
            $im->readImageBlob( $svg );
            $im->resizeImage( $size, $size, Imagick::FILTER_LANCZOS, 1 );
            $im->setImageFormat('png32');
            $png = $im->getImageBlob();
            $im->destroy();
            if ( $png && strlen($png) > 100 ) return $png;
        } catch ( Exception $e ) {
            error_log('DS32 Imagick error: ' . $e->getMessage() );
        }
    }

    // Method 2: GD via base64 data-URI trick (works on most shared hosts)
    // Embed the SVG as a data URI inside a tiny HTML page and ask GD
    // to decode it — GD can't read SVG directly, so instead we extract
    // the raw path data and draw a placeholder colored square that at
    // least fills the slot with the right accent colour instead of
    // leaving a broken image.
    if ( function_exists('imagecreatetruecolor') ) {
        // Pull accent colour from the SVG fill attribute
        $color_hex = '4A9EFF';
        if ( preg_match('/fill=["\']#?([0-9A-Fa-f]{6})["\']/', $svg, $cm ) ) {
            $color_hex = $cm[1];
        }
        $r = hexdec( substr($color_hex,0,2) );
        $g = hexdec( substr($color_hex,2,2) );
        $b = hexdec( substr($color_hex,4,2) );

        $im  = imagecreatetruecolor( $size, $size );
        imagealphablending( $im, false );
        imagesavealpha( $im, true );
        $transparent = imagecolorallocatealpha( $im, 0, 0, 0, 127 );
        imagefill( $im, 0, 0, $transparent );
        $col = imagecolorallocate( $im, $r, $g, $b );
        // Draw a simple filled circle as icon placeholder
        imagefilledellipse( $im, $size/2, $size/2, $size-4, $size-4, $col );
        ob_start();
        imagepng( $im );
        $png = ob_get_clean();
        imagedestroy( $im );
        if ( $png && strlen($png) > 100 ) return $png;
    }

    return ''; // both methods unavailable — caller falls back to SVG
}
}

if ( ! function_exists('ds32_get_icon_keywords') ) {
function ds32_get_icon_keywords( array $icon_num_to_heading, string $api_k ) : array {
    if ( empty($icon_num_to_heading) ) return [];

    /* DS32-FIX — resolve the key when the caller did not supply one, and
       still return real keywords (derived locally) when no key exists, so a
       template deck gets icons instead of silently keeping "Icon N" text. */
    if ( ! is_string($api_k) || $api_k === '' ) {
        $api_k = ds32_resolve_groq_key();
    }
    if ( $api_k === '' ) {
        return ds32_local_keywords( $icon_num_to_heading );
    }

    $list = '';
    foreach ( $icon_num_to_heading as $n => $h ) {
        $list .= "Icon {$n}: {$h}\n";
    }

    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', [
        'timeout' => 20,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_k,
            'Content-Type'  => 'application/json',
        ],
        'body' => wp_json_encode([
            'model'           => get_option('ds_groq_ppt_model', 'openai/gpt-oss-120b'),
            'temperature'     => 0.3,
            'max_tokens'      => 500,
            'response_format' => ['type' => 'json_object'],
            'messages'        => [[
                'role'    => 'user',
                'content' => "For each item below, pick a single English keyword (1 word, lowercase) that best fits an icon representing that specific item's meaning.\n"
                           . "EVERY keyword in your answer MUST be different from every other keyword you return — no repeats, even if two items feel related. Pick the most distinctive, specific word for each item so they don't collide.\n"
                           . "Draw from a broad icon vocabulary, e.g.: growth, chart, team, finance, strategy, target, rocket, globe, shield, star, idea, settings, clock, check, award, handshake, funnel, megaphone, gear, briefcase, compass, puzzle, chart-bar, chart-pie, trophy, lightbulb, network, database, cloud, lock, key, map, flag, calendar, message, mail, phone, camera, book, graduation, heart, leaf, sun, bolt, wrench, tools, chart-line, users, dollar, wallet, receipt, calculator, presentation, magnifier, filter, layers, box, truck, factory.\n\n"
                           . "Items:\n{$list}\n\n"
                           . "Return JSON only: {\"icons\":{\"1\":\"keyword\",\"2\":\"keyword\"}}",
            ]],
        ]),
    ]);

    if ( is_wp_error($resp) ) return ds32_local_keywords( $icon_num_to_heading );

    $body    = json_decode(wp_remote_retrieve_body($resp), true);
    $content = $body['choices'][0]['message']['content'] ?? '{}';
    $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
    $content = preg_replace('/\s*```$/', '', $content);
    $data    = json_decode($content, true);
    $icons   = ( is_array($data) && isset($data['icons']) && is_array($data['icons']) ) ? $data['icons'] : [];

    $result = [];
    foreach ( $icons as $n => $kw ) {
        $result[(int)$n] = strtolower(trim((string)$kw));
    }

    /* DS32-FIX — fill in any icon the model skipped: an empty keyword
       means ds32_fetch_svg() is never called and that slot stays blank. */
    foreach ( $icon_num_to_heading as $n => $h ) {
        if ( empty($result[(int)$n]) ) {
            $result[(int)$n] = ds32_local_keyword_for( (string) $h );
        }
    }
    ksort($result);

    // Safety net: guarantee every returned keyword is unique, even if the
    // model repeated one across 2+ points (seen on real headings like
    // "Sales"/"Strategy"/"Finance" that don't give the fallback much to
    // work with). Single pass, tracking $seen as we assign — so a value
    // that was JUST reassigned still blocks the next collision instead of
    // being missed the way a "first pass only" check would.
    $seen = [];
    foreach ( $result as $n => $kw ) {
        if ( $kw === '' ) continue;

        $final = $kw;

        if ( isset($seen[$final]) ) {
            $heading = (string)($icon_num_to_heading[$n] ?? '');
            $words   = preg_split('/[^a-zA-Z]+/', $heading, -1, PREG_SPLIT_NO_EMPTY);
            $alt = '';
            foreach ( $words as $w ) {
                $w = strtolower($w);
                if ( $w !== $kw && strlen($w) > 2 && ! isset($seen[$w]) ) { $alt = $w; break; }
            }
            $final = $alt !== '' ? $alt : $kw;
        }

        if ( isset($seen[$final]) ) {
            // Never fabricate "keyword-N" — Iconify has no such icon, so
            // ds32_fetch_svg() returns '' and the icon is silently skipped.
            // Fall back to a real, searchable generic instead.
            $generics = ['star','circle','check','flag','bolt','gear','target',
                         'layers','box','compass','puzzle','award','bookmark','globe'];
            $final = $kw;
            foreach ( $generics as $g ) {
                if ( ! isset($seen[$g]) ) { $final = $g; break; }
            }
        }

        $result[$n] = $final;
        $seen[$final] = true;
    }

    return $result;
}
}
if ( ! function_exists('ds32_abs_centres') ) {
function ds32_abs_centres( string $slide_xml ) : array {
    $out = [];
    if ( ! class_exists('DOMDocument') ) return $out;

    $prev = libxml_use_internal_errors(true);
    $doc  = new DOMDocument();
    $ok   = $doc->loadXML( $slide_xml );
    libxml_clear_errors();
    libxml_use_internal_errors( $prev );
    if ( ! $ok ) return $out;

    $A = 'http://schemas.openxmlformats.org/drawingml/2006/main';
    $P = 'http://schemas.openxmlformats.org/presentationml/2006/main';

    $kid = function( $node, $name ) {
        if ( ! $node ) return null;
        foreach ( $node->childNodes as $c ) {
            if ( $c->nodeType === XML_ELEMENT_NODE && $c->localName === $name ) return $c;
        }
        return null;
    };

    $walk = function( $node, $ox, $oy, $sx, $sy ) use ( &$walk, &$out, $kid, $A, $P ) {
        foreach ( $node->childNodes as $ch ) {
            if ( $ch->nodeType !== XML_ELEMENT_NODE ) continue;
            $tag = $ch->localName;

            if ( $tag === 'grpSp' ) {
                $nox = $ox; $noy = $oy; $nsx = $sx; $nsy = $sy;
                $xf  = $kid( $kid( $ch, 'grpSpPr' ), 'xfrm' );
                if ( $xf ) {
                    $off = $kid($xf,'off');   $ext = $kid($xf,'ext');
                    $cho = $kid($xf,'chOff'); $che = $kid($xf,'chExt');
                    if ( $off && $ext && $cho && $che ) {
                        $X   = (float) $off->getAttribute('x');
                        $Y   = (float) $off->getAttribute('y');
                        $CX  = (float) $ext->getAttribute('cx');
                        $CY  = (float) $ext->getAttribute('cy');
                        $hx  = (float) $cho->getAttribute('x');
                        $hy  = (float) $cho->getAttribute('y');
                        $hcx = (float) $che->getAttribute('cx');
                        $hcy = (float) $che->getAttribute('cy');
                        $kx  = $hcx ? $CX / $hcx : 1.0;
                        $ky  = $hcy ? $CY / $hcy : 1.0;
                        $nsx = $sx * $kx;
                        $nsy = $sy * $ky;
                        $nox = $ox + ( $X - $hx * $kx ) * $sx;
                        $noy = $oy + ( $Y - $hy * $ky ) * $sy;
                    }
                }
                $walk( $ch, $nox, $noy, $nsx, $nsy );
                continue;
            }

            if ( $tag === 'sp' || $tag === 'pic' ) {
                $cn = $ch->getElementsByTagNameNS( $P, 'cNvPr' )->item(0);
                if ( ! $cn ) continue;
                $id = (int) $cn->getAttribute('id');
                if ( ! $id ) continue;

                $xfl = $ch->getElementsByTagNameNS( $A, 'xfrm' );
                if ( ! $xfl->length ) continue;
                $xf  = $xfl->item(0);
                $off = $kid($xf,'off');
                $ext = $kid($xf,'ext');
                if ( ! $off || ! $ext ) continue;

                $x  = (float) $off->getAttribute('x');
                $y  = (float) $off->getAttribute('y');
                $cx = (float) $ext->getAttribute('cx');
                $cy = (float) $ext->getAttribute('cy');

                $out[ $id ] = [
                    'cxc' => $ox + ( $x + $cx / 2 ) * $sx,
                    'cyc' => $oy + ( $y + $cy / 2 ) * $sy,
                ];
                continue;
            }
        }
    };

    $csld    = $kid( $doc->documentElement, 'cSld' );
    $sp_tree = $csld ? $kid( $csld, 'spTree' ) : null;
    if ( ! $sp_tree ) return $out;

    $walk( $sp_tree, 0.0, 0.0, 1.0, 1.0 );
    return $out;
}
}


/* ───────────────────────────────────────────────────────────────────────────
   DS32-FIX helpers (added for: marketplace-template downloads had no icons)
   ─────────────────────────────────────────────────────────────────────────── */

if ( ! function_exists('ds32_resolve_groq_key') ) {
/** Same key lookup Snippet 30 / 33 use, so every pipeline finds the key. */
function ds32_resolve_groq_key() : string {
    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $k = '';
    if ( is_array($keys) ) {
        if ( ! empty($keys['groq']) )             $k = (string) $keys['groq'];
        elseif ( ! empty($keys['groq_api_key']) ) $k = (string) $keys['groq_api_key'];
    }
    if ( $k === '' ) $k = (string) get_option('ds_groq_api_key', '');
    if ( $k === '' ) $k = (string) get_option('groq_api_key', '');
    return trim($k);
}
}

if ( ! function_exists('ds32_normalise_slots') ) {
/**
 * Accept either slot shape:
 *   list : [ ['slot_name' => 'point 1 heading', 'replacement' => 'Strategy'], ... ]
 *   map  : [ 'point 1 heading' => 'Strategy', ... ]
 * Snippet 21 changed from the second shape to the first, which silently
 * turned every replacement into the string "Array" for callers expecting
 * a map — that is why marketplace decks stopped getting icons.
 */
function ds32_normalise_slots( $slots ) : array {
    $out = array();
    if ( empty($slots) || ! is_array($slots) ) return $out;
    foreach ( $slots as $key => $val ) {
        if ( is_object($val) ) $val = (array) $val;
        if ( is_array($val) && ( isset($val['slot_name']) || isset($val['replacement']) ) ) {
            $out[] = array(
                'slot_name'   => (string) ( $val['slot_name'] ?? '' ),
                'replacement' => (string) ( $val['replacement'] ?? '' ),
            );
        } elseif ( is_scalar($val) ) {
            $out[] = array(
                'slot_name'   => (string) $key,
                'replacement' => (string) $val,
            );
        }
    }
    return $out;
}
}

if ( ! function_exists('ds32_local_keyword_for') ) {
/** Derive one Iconify (mdi) search word from a heading, with no API call. */
function ds32_local_keyword_for( string $heading ) : string {
    $map = array(
        'growth'=>'trending-up','increase'=>'trending-up','revenue'=>'cash','sales'=>'cash-multiple',
        'finance'=>'finance','budget'=>'wallet','cost'=>'currency-usd','price'=>'tag','pricing'=>'tag',
        'profit'=>'chart-line','market'=>'storefront','marketing'=>'bullhorn','brand'=>'palette',
        'team'=>'account-group','people'=>'account-group','customer'=>'account-heart','client'=>'handshake',
        'partner'=>'handshake','support'=>'lifebuoy','service'=>'room-service','quality'=>'medal',
        'strategy'=>'chess-knight','plan'=>'clipboard-text','planning'=>'clipboard-text','goal'=>'target',
        'target'=>'target','vision'=>'eye','mission'=>'flag','value'=>'diamond-stone','result'=>'chart-box',
        'performance'=>'speedometer','analysis'=>'chart-bar','analytics'=>'chart-bar','data'=>'database',
        'report'=>'file-chart','research'=>'magnify','insight'=>'lightbulb-on','idea'=>'lightbulb',
        'innovation'=>'creation','technology'=>'chip','product'=>'package-variant','design'=>'pencil-ruler',
        'development'=>'code-tags','process'=>'cog-sync','workflow'=>'sitemap','automation'=>'robot',
        'security'=>'shield-check','risk'=>'alert-octagon','compliance'=>'scale-balance','legal'=>'gavel',
        'time'=>'clock-outline','schedule'=>'calendar-clock','timeline'=>'timeline','launch'=>'rocket-launch',
        'global'=>'earth','world'=>'earth','network'=>'lan','cloud'=>'cloud','mobile'=>'cellphone',
        'communication'=>'message-text','email'=>'email','contact'=>'phone','training'=>'school',
        'education'=>'school','learning'=>'book-open-page-variant','experience'=>'star-circle',
        'award'=>'trophy','success'=>'check-decagram','challenge'=>'flag-checkered','solution'=>'puzzle',
        'benefit'=>'gift','feature'=>'star-four-points','conclusion'=>'flag-checkered',
        'summary'=>'text-box-check','overview'=>'view-dashboard','about'=>'information',
    );
    $stop = array('the','and','for','with','our','your','their','from','that','this','are','was',
                  'has','have','will','can','not','all','you','its','into','out','more','new','how',
                  'why','what','who','when','key','point','slide','step','part','one','two','three');

    $words = preg_split('/[^a-z]+/', strtolower(trim($heading)), -1, PREG_SPLIT_NO_EMPTY);
    foreach ( (array) $words as $w ) {
        if ( isset($map[$w]) ) return $map[$w];
    }
    foreach ( (array) $words as $w ) {
        $s = rtrim($w, 's');
        if ( $s !== $w && isset($map[$s]) ) return $map[$s];
    }
    foreach ( (array) $words as $w ) {
        if ( strlen($w) > 3 && ! in_array($w, $stop, true) ) return $w;
    }
    return 'star';
}
}

if ( ! function_exists('ds32_local_keywords') ) {
/** Whole-deck keyword set without the model — used when no key is available. */
function ds32_local_keywords( array $icon_num_to_heading ) : array {
    $generics = array('lightbulb','chart-bar','target','check-circle','rocket-launch','star',
                      'compass','puzzle','trophy','flag','layers','shield-check','clock-outline','earth');
    $result = array();
    $seen   = array();
    $gi     = 0;
    $total  = count($generics);
    foreach ( $icon_num_to_heading as $n => $h ) {
        $kw = ds32_local_keyword_for( (string) $h );
        while ( ( $kw === '' || isset($seen[$kw]) ) && $gi < $total ) {
            $kw = $generics[$gi];
            $gi++;
        }
        if ( $kw === '' ) $kw = 'star';
        $seen[$kw] = true;
        $result[(int)$n] = $kw;
    }
    ksort($result);
    error_log('DS32: local keyword fallback=' . json_encode($result));
    return $result;
}
}
