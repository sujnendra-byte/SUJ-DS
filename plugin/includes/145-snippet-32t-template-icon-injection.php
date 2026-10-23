<?php
/**
 * Code Snippets export — Snippet 32T — Template Icon Injection (separate from Snippet 32)
 *
 * Snippet ID : 844
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager
 * Modified   : 2026-09-16 (new in V19)
 * Load order : 145 of 145
 */
/**
 * Snippet 32T — Template Icon Injection (marketplace template gallery + smart template matcher)
 * Separate from Snippet 32 so AI-generated deck icons are never affected.
 * - Skips number-only labels ("1", "2") when pairing icons to point titles
 * - Prefers the point title in the same column as the icon
 * Uses Snippet 32's helper functions (read-only); Snippet 32 itself is unchanged.
 */
add_action('init', function () {
    if ( function_exists('ds32_inject_icons') ) {
        remove_filter('ds30_filled_pptx_bytes',   'ds32_inject_icons', 10);
        remove_filter('ds_tpl_filled_pptx_bytes', 'ds32_inject_icons', 10);
    }
    // Marketplace design picks: take over from Snippet 33's bridge (Snippet 33 code unchanged).
    remove_filter('ds_tpl_filled_pptx_bytes', 'ds33_inject_icons_from_map', 10);
    add_filter('ds30_filled_pptx_bytes',   'ds32t_inject_icons', 10, 3);
    add_filter('ds_tpl_filled_pptx_bytes', 'ds32t_bridge', 10, 3);
}, 20);

if ( ! function_exists('ds32t_inject_icons') ) {
function ds32t_inject_icons( $pptx_bytes, $ai_json = array(), $api_k = '' ) {
    @ini_set('pcre.backtrack_limit', '100000000'); @ini_set('pcre.recursion_limit', '100000000'); // FIX 2026-09-16: large slide XML (1-3 MB) silently broke text/icon matching
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

    error_log('DS32T: filter fired, pptx size=' . strlen($pptx_bytes));

    $tmp = tempnam(sys_get_temp_dir(), 'ds32_') . '.pptx';
    file_put_contents($tmp, $pptx_bytes);

    $zip = new ZipArchive();
    if ( $zip->open($tmp) !== true ) {
        @unlink($tmp);
        error_log('DS32T: failed to open zip');
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
        error_log('DS32T: no slides found');
        return $pptx_bytes;
    }

    // ── Build a set of "heading-like" replacement texts from ai_json ──────────
    // (used only to decide which shapes on the slide count as candidate headings;
    //  actual pairing happens per-slide using real coordinates below)
    $heading_texts = [];
    $ds32t_titles  = []; // FIX: slide title / sub header texts are never icon headings
    foreach ( (array)$ai_json as $slot ) {
        $slot_name = (string)($slot['slot_name'] ?? '');
        $repl      = trim((string)($slot['replacement'] ?? ''));
        if ( $repl === '' ) continue;
        if ( preg_match('/^(?:Icon|IconSym|ISy|ItIcSy|IcSy)\s*\d+$/i', trim($slot_name)) ) continue; // never treat icon slots as headings
        if ( preg_match('/^(title|title\s*header|main\s*title|sub\s*header|subtitle|sub\s*title)$/i', trim($slot_name)) ) { $ds32t_titles[] = $repl; continue; } // exclude slide title/subtitle
        // Heading slots: short text (title-like), OR explicit "Point N Heading" naming
        $is_named_heading = preg_match('/point\s*\d+\s*(heading|title|name)|step\s*\d+\s*(heading|title|name)/i', $slot_name);
        $is_short          = mb_strlen($repl) <= 40;
        if ( ( $is_named_heading || $is_short ) && preg_match('/\p{L}{3,}/u', $repl) ) { // FIX: skip number-only labels
            $heading_texts[] = $repl;
        }
    }
    $heading_texts = array_values(array_unique($heading_texts));

    /* DS32-FIX — the combined-deck pipeline passes no slot map at all.
       Instead of bailing out (which left that deck with no icons), fall back
       to treating short on-slide text as the heading candidates. */
    $headings_from_slide = empty($heading_texts);
    if ( $headings_from_slide ) {
        error_log('DS32T: no heading texts in slot map — falling back to on-slide short text');
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
            } elseif ( preg_match('/\p{L}{3,}/u', $text ) && ! in_array($text, $ds32t_titles, true) && $cyc > 1028700 && ( in_array($text, $heading_texts, true)
                        || ( $headings_from_slide && mb_strlen($text) <= 40 ) ) ) {
                $heading_list[] = ['text' => $text, 'cxc' => $cxc, 'cyc' => $cyc];
            }
        }

        if ( ! empty($icon_list) ) $slide_icon_shapes[$slide_name] = $icon_list;
        if ( ! empty($heading_list) ) $slide_heading_shapes[$slide_name] = $heading_list;
    }

    if ( empty($slide_icon_shapes) ) {
        @unlink($tmp);
        error_log('DS32T: no Icon N shapes found on any slide');
        return $pptx_bytes;
    }

    // ── Pair each icon with its nearest heading (per slide, Euclidean distance) ─
    // icon_num => heading text
    $icon_num_to_heading = [];
    // FIX: pair each icon with its own heading (closest first, each heading used once),
    // horizontal distance weighted so the heading in the same column wins.
    foreach ( $slide_icon_shapes as $slide_name => $icons ) {
        $headings_here = $slide_heading_shapes[$slide_name] ?? [];
        if ( empty($headings_here) ) continue;
        $pairs = [];
        foreach ( $icons as $ik => $icon ) {
            foreach ( $headings_here as $hk => $h ) {
                $pairs[] = [ sqrt( pow( 1.5 * ( $h['cxc'] - $icon['cxc'] ), 2 ) + pow( $h['cyc'] - $icon['cyc'], 2 ) ), $ik, $hk ];
            }
        }
        usort( $pairs, function( $a, $b ) { return $a[0] <=> $b[0]; } );
        $icon_done = []; $head_used = [];
        foreach ( $pairs as $p ) {
            if ( isset($icon_done[$p[1]]) || isset($head_used[$p[2]]) ) continue;
            $icon_done[$p[1]] = $p[2]; $head_used[$p[2]] = true;
        }
        foreach ( $pairs as $p ) { // more icons than headings: reuse nearest
            if ( ! isset($icon_done[$p[1]]) ) $icon_done[$p[1]] = $p[2];
        }
        foreach ( $icon_done as $ik => $hk ) {
            $icon_num_to_heading[ $icons[$ik]['num'] ] = $headings_here[$hk]['text'];
        }
    }

    error_log('DS32T: icon-to-heading pairing=' . json_encode($icon_num_to_heading));

    if ( empty($icon_num_to_heading) ) {
        @unlink($tmp);
        return $pptx_bytes;
    }

    // ── Get icon keywords from Groq, keyed by icon number ──────────────────────
    $icon_keywords = ds32_get_icon_keywords($icon_num_to_heading, $api_k);
    error_log('DS32T: keywords=' . json_encode($icon_keywords));

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
            $keyword = $icon_keywords[$icon_num] ?? null;

			/* DS32-FIX (guaranteed icon, 2026-09-16) - this icon's slide had no
			   heading shape it could be paired with (e.g. a template whose caption
			   layout this pass does not recognise), so it never got a content-matched
			   keyword above. Rather than leaving the raw "Icon N" placeholder text -
			   the exact bug being fixed - give it a generic, on-brand icon instead.
			   Deterministic on the icon number so the same deck renders the same way
			   twice, and varied so a slide with several unmatched icons does not show
			   the same picture repeated. */
			if ( ! $keyword && ! empty($icon_num_to_heading[$icon_num]) ) { $keyword = $icon_num_to_heading[$icon_num]; }
			if ( ! $keyword ) {
				$ds32_generics = ['star','target','check-circle','flag','lightbulb','rocket-launch','shield-check','trophy','compass','puzzle'];
				$keyword = $ds32_generics[ $icon_num % count($ds32_generics) ];
			}

            $icon_color = ds32_pick_icon_color($icon['full']);
            $svg = ds32t_fetch_svg($keyword, $icon_color, (string)($icon_num_to_heading[$icon_num] ?? ''), (int)$icon_num);
            if ( ! $svg ) {
                error_log('DS32T: no SVG for keyword=' . $keyword);
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

    error_log('DS32T: icons_injected=' . $icons_injected . ' new_media=' . count($new_media));

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
        error_log('DS32T: rebuild failed, returning original');
        return $pptx_bytes;
    }

    error_log('DS32T: success! result size=' . strlen($result));
    return $result;
}
}

/* Bridge for marketplace template picks (same slot clean-up Snippet 33 did). */
if ( ! function_exists('ds32t_bridge') ) {
function ds32t_bridge( $pptx_bytes, $map = array(), $api_k = '' ) {
    if ( empty($pptx_bytes) || strlen($pptx_bytes) < 100 ) return $pptx_bytes;
    $rows = function_exists('ds32_normalise_slots') ? ds32_normalise_slots( $map ) : array();
    if ( empty($rows) && is_array($map) ) {
        foreach ( $map as $k => $v ) { if ( is_scalar($v) ) $rows[] = array('slot_name' => (string)$k, 'replacement' => (string)$v); }
    }
    $ai_json = array();
    foreach ( (array)$rows as $row ) {
        $rep = (string)($row['replacement'] ?? '');
        if ( $rep === '' ) continue;
        if ( stripos($rep, 'Additional Point') === 0 || stripos($rep, 'You can delete this point') === 0 ) continue;
        $ai_json[] = array('slot_name' => (string)($row['slot_name'] ?? ''), 'replacement' => $rep);
    }
    if ( empty($ai_json) ) return $pptx_bytes;
    error_log('DS32T: bridging ' . count($ai_json) . ' slots for marketplace-picked slide.');
    return ds32t_inject_icons( $pptx_bytes, $ai_json, $api_k );
}
}

/* Reliable icon fetch: cached, retried, never a plain star unless the network is fully down. */
if ( ! function_exists('ds32t_http_get') ) {
function ds32t_http_get( $url ) {
    for ( $try = 0; $try < 3; $try++ ) {
        if ( $try ) usleep( 400000 * $try );
        $r = wp_remote_get( $url, array('timeout' => 8, 'sslverify' => false, 'headers' => array('User-Agent' => 'Mozilla/5.0 DesignStager')) );
        if ( is_wp_error($r) ) continue;
        $code = (int) wp_remote_retrieve_response_code($r);
        if ( $code === 200 ) return wp_remote_retrieve_body($r);
        if ( $code === 404 ) return '';
    }
    return '';
}
}
if ( ! function_exists('ds32t_search_icon') ) {
function ds32t_search_icon( $text ) {
    $text = strtolower( trim( preg_replace('/[^a-z0-9 ]+/i', ' ', (string)$text) ) );
    if ( $text === '' ) return '';
    $ck = 'ds32t_q_' . md5($text);
    $hit = get_transient($ck);
    if ( $hit !== false ) return $hit === 'none' ? '' : $hit;
    $stop = array('the','and','for','with','your','our','from','into','that','this','are','per','all','new','key','based','driven');
    $words = array_values(array_filter(explode(' ', $text), function($w) use ($stop){ return strlen($w) > 2 && ! in_array($w, $stop, true); }));
    $tries = array($text);
    if ( count($words) > 1 ) $tries[] = implode(' ', array_slice($words, 0, 2));
    foreach ( $words as $w ) $tries[] = $w;
    $found = ''; $net_ok = false;
    foreach ( array_slice(array_unique($tries), 0, 5) as $q ) {
        $body = ds32t_http_get('https://api.iconify.design/search?query=' . rawurlencode($q) . '&limit=32&prefixes=mdi');
        if ( $body === '' ) continue;
        $net_ok = true;
        $d = json_decode($body, true);
        foreach ( (array)($d['icons'] ?? array()) as $ic ) {
            if ( strpos($ic, '-off') === false ) { $found = (string)$ic; break 2; }
        }
    }
    if ( $found !== '' || $net_ok ) set_transient($ck, $found !== '' ? $found : 'none', WEEK_IN_SECONDS);
    return $found;
}
}
if ( ! function_exists('ds32t_download_svg') ) {
function ds32t_download_svg( $icon, $color_hex ) {
    if ( strpos($icon, ':') === false ) return '';
    list($p, $n) = explode(':', $icon, 2);
    $p = preg_replace('/[^a-z0-9-]/', '', strtolower($p)); $n = preg_replace('/[^a-z0-9-]/', '', strtolower($n));
    $color_hex = preg_replace('/[^0-9A-Fa-f]/', '', (string)$color_hex) ?: '1F2937';
    if ( $p === '' || $n === '' ) return '';
    $ck = 'ds32t_svg_' . md5($p . ':' . $n . '|' . $color_hex);
    $hit = get_transient($ck);
    if ( $hit !== false && strpos($hit, '<svg') !== false ) return $hit;
    $svg = ds32t_http_get("https://api.iconify.design/{$p}/{$n}.svg?color=%23{$color_hex}&width=128&height=128");
    if ( $svg === '' || strpos($svg, '<svg') === false ) return '';
    if ( strpos($svg, '<?xml') === false ) $svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n" . $svg;
    if ( strpos($svg, 'xmlns=') === false ) $svg = str_replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg"', $svg);
    set_transient($ck, $svg, MONTH_IN_SECONDS);
    return $svg;
}
}
if ( ! function_exists('ds32t_fetch_svg') ) {
function ds32t_fetch_svg( $keyword, $color_hex = '1F2937', $heading = '', $num = 0 ) {
    foreach ( array($keyword, $heading) as $q ) {
        if ( trim((string)$q) === '' ) continue;
        $icon = ds32t_search_icon($q);
        if ( $icon ) { $svg = ds32t_download_svg($icon, $color_hex); if ( $svg ) return $svg; }
    }
    // Relevant-looking generic set (varied, not all stars)
    $generic = array('mdi:lightbulb-on','mdi:target','mdi:chart-line','mdi:account-group','mdi:rocket-launch','mdi:shield-check','mdi:cog','mdi:trophy','mdi:compass','mdi:puzzle');
    $start = abs(crc32((string)$heading . $keyword . $num)) % count($generic);
    for ( $i = 0; $i < count($generic); $i++ ) {
        $svg = ds32t_download_svg($generic[($start + $i) % count($generic)], $color_hex);
        if ( $svg ) { error_log('DS32T: used generic icon for "' . $keyword . '"'); return $svg; }
    }
    error_log('DS32T: Iconify unreachable — offline fallback for "' . $keyword . '"');
    return function_exists('ds32_offline_fallback_svg') ? ds32_offline_fallback_svg($color_hex) : '';
}
}
