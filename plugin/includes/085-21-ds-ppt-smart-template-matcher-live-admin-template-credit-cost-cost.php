<?php
/**
 * Code Snippets export — 21 ds ppt smart template matcher [Live Admin Template Credit Cost] [Cost Badge On Marketplace Modal]
 *
 * Snippet ID : 767
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-081
 * Modified   : 2026-09-16 (V19 update from staging)
 * Load order : 085 of 144
 */
/**
 * Snippet 21 (v2): DS PPT Smart Template Matcher — Live Preview Gallery
 * Run: Everywhere
 *
 * REPLACES the earlier "21 DS PPT Smart Template Matcher" snippet — this
 * is the same matching/injection engine plus a different front-end (a
 * dedicated "🏆 Pick a Design" gallery instead of inline panel cards) and
 * live, content-filled previews instead of static template thumbnails.
 *
 * IMPORTANT: if you imported the earlier version, deactivate/delete it
 * before activating this one — both define the same PHP function names
 * and REST routes, so running both together will fatal-error the site.
 *
 * Still does not edit Snippet 17, 19, or any existing snippet — it only
 * appends a button into Snippet 17's panel header and otherwise runs as
 * its own independent overlay.
 *
 * ──────────────────────────────────────────────────────────────────────
 * WHAT'S NEW IN v2
 * ──────────────────────────────────────────────────────────────────────
 *  • "🏆 Pick a Design" button (appended once into the existing Change
 *    Layout panel header) opens its own larger gallery overlay — like
 *    PowerPoint's Designer pane — instead of small cards stuck under the
 *    coded layouts.
 *  • Each candidate template is rendered with YOUR ACTUAL TEXT already
 *    filled in, live, via LibreOffice (soffice --headless --convert-to
 *    png) — not just the template's blank preview photo. Cards populate
 *    progressively as each render finishes (a few seconds each).
 *  • If LibreOffice isn't available/working on this server, every card
 *    automatically falls back to the template's static thumbnail instead
 *    of breaking — check GET /wp-json/ds/v1/tpl-debug (while logged in)
 *    to see exactly what this server can/can't do, and to force a fresh
 *    capability check after installing LibreOffice.
 *
 * ──────────────────────────────────────────────────────────────────────
 * SETUP — same as before, one-time per template:
 *   1. Template's placeholder text boxes use: Title Header, Sub Header,
 *      Explanation, Point 1 Heading, Point 1 Explanation, Point 2
 *      Heading, ...
 *   2. On that template's dlm_download post, fill in the "AI Studio —
 *      Smart Template Matching" box: enable it, set its shape (steps_4,
 *      timeline, swot, pros_cons...), mark Semantic only for templates
 *      with fixed meaning labels baked in (SWOT, PESTLE, etc.).
 *   3. Visit /wp-json/ds/v1/tpl-debug once to confirm live preview will
 *      actually render on this server (or that it'll gracefully fall
 *      back to thumbnails if not — either way nothing breaks).
 */
if ( ! defined('ABSPATH') ) exit;

// ════════════════════════════════════════════════════════════════════════
// PART A — Admin tagging: one meta box on the dlm_download edit screen
// ════════════════════════════════════════════════════════════════════════

add_action('add_meta_boxes', function () {
    add_meta_box(
        'ds_tpl_box',
        'AI Studio — Smart Template Matching',
        'ds_tpl_meta_box_html',
        'dlm_download',
        'side',
        'default'
    );
});

if ( ! function_exists('ds_tpl_meta_box_html') ) {
function ds_tpl_meta_box_html( $post ) {
    wp_nonce_field('ds_tpl_save', 'ds_tpl_nonce');
    $enabled  = get_post_meta($post->ID, '_ds_tpl_enabled', true);
    $shape    = get_post_meta($post->ID, '_ds_tpl_shape', true);
    $semantic = get_post_meta($post->ID, '_ds_tpl_semantic', true);
    $keywords = get_post_meta($post->ID, '_ds_tpl_keywords', true);
    ?>
    <p>
      <label>
        <input type="checkbox" name="ds_tpl_enabled" value="1" <?php checked($enabled, '1'); ?> />
        Show this template in AI Studio's "Pick a Design" matches
      </label>
    </p>
    <p>
      <label for="ds_tpl_shape" style="font-weight:600;display:block;margin-bottom:4px;">Content shape</label>
      <input type="text" id="ds_tpl_shape" name="ds_tpl_shape" list="ds_tpl_shape_options"
             value="<?php echo esc_attr($shape); ?>" style="width:100%;"
             placeholder="e.g. steps_4, timeline, swot, pros_cons" />
      <datalist id="ds_tpl_shape_options">
        <option value="header"></option>
        <option value="swot"></option>
        <option value="pros_cons"></option>
        <option value="pestle"></option>
        <option value="business_model_canvas"></option>
        <option value="steps_1"></option>
        <option value="steps_2"></option>
        <option value="steps_3"></option>
        <option value="steps_4"></option>
        <option value="steps_5"></option>
        <option value="steps_6"></option>
        <option value="steps_7"></option>
        <option value="steps_8"></option>
        <option value="steps_9"></option>
        <option value="steps_10"></option>
        <option value="steps_11"></option>
        <option value="steps_12"></option>
        <option value="steps_13"></option>
        <option value="steps_14"></option>
        <option value="timeline"></option>
        <option value="generic_grid_1"></option>
        <option value="generic_grid_2"></option>
        <option value="generic_grid_3"></option>
        <option value="generic_grid_4"></option>
        <option value="generic_grid_5"></option>
        <option value="generic_grid_6"></option>
        <option value="generic_grid_7"></option>
        <option value="generic_grid_8"></option>
        <option value="generic_grid_9"></option>
        <option value="generic_grid_10"></option>
        <option value="generic_grid_11"></option>
        <option value="generic_grid_12"></option>
        <option value="generic_grid_13"></option>
        <option value="generic_grid_14"></option>
      </datalist>
    </p>
    <p>
      <label>
        <input type="checkbox" name="ds_tpl_semantic" value="1" <?php checked($semantic, '1'); ?> />
        Semantic (fixed meaningful labels baked in — e.g. STRENGTHS / WEAKNESSES).
        Only checked templates get matched on meaning; leave unchecked for plain
        numbered/structural templates so they can match any content of that shape.
      </label>
    </p>
    <p>
      <label for="ds_tpl_keywords" style="font-weight:600;display:block;margin-bottom:4px;">Notes / keywords (optional, for your own reference)</label>
      <input type="text" id="ds_tpl_keywords" name="ds_tpl_keywords" value="<?php echo esc_attr($keywords); ?>"
             style="width:100%;" placeholder="strength, weakness, opportunity, threat" />
    </p>
    <p style="color:#777;font-size:11px;line-height:1.5;">
      The .pptx file's fillable text boxes must use the exact labels:
      <code>Title Header</code>, <code>Sub Header</code>, <code>Explanation</code>,
      <code>Point 1 Heading</code>, <code>Point 1 Explanation</code>, <code>Point 2 Heading</code>… —
      matching is case-insensitive. Fixed numbers, dates, and category names
      (e.g. "STRENGTHS", "01", "2024") should be left exactly as-is in the file.
    </p>
    <p style="color:#777;font-size:11px;line-height:1.5;">
      For shape <code>header</code> (cover/title-slide templates), use instead:
      <code>Presentation Title</code>, <code>Subtitle</code>, <code>Presenter Name</code>,
      <code>Job Title / Designation</code>, <code>Company Name</code>, <code>Date</code>,
      <code>Website</code>, <code>Email</code>. Presenter Name, Job Title / Designation, and
      Company Name are optional — if the user hasn't provided them, they're filled from
      their WordPress account profile instead, or left blank if that's empty too.
    </p>
    <?php
}
}

add_action('save_post_dlm_download', function ( $post_id ) {
    if ( ! isset($_POST['ds_tpl_nonce']) || ! wp_verify_nonce($_POST['ds_tpl_nonce'], 'ds_tpl_save') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;

    update_post_meta($post_id, '_ds_tpl_enabled',  isset($_POST['ds_tpl_enabled'])  ? '1' : '');
    update_post_meta($post_id, '_ds_tpl_shape',     sanitize_text_field($_POST['ds_tpl_shape']    ?? ''));
    update_post_meta($post_id, '_ds_tpl_semantic',  isset($_POST['ds_tpl_semantic']) ? '1' : '');
    update_post_meta($post_id, '_ds_tpl_keywords',  sanitize_text_field($_POST['ds_tpl_keywords'] ?? ''));
});

// ════════════════════════════════════════════════════════════════════════
// PART B — Shape classification (semantic first, structural fallback)
// ════════════════════════════════════════════════════════════════════════

if ( ! function_exists('ds_tpl_split_colon') ) {
function ds_tpl_split_colon( $line ) {
    $line = (string) $line;
    $i = strpos($line, ':');
    if ( $i === false ) return array('h' => trim($line), 'd' => '');
    return array('h' => trim(substr($line, 0, $i)), 'd' => trim(substr($line, $i + 1)));
}
}

if ( ! function_exists('ds_tpl_classify_slide') ) {
/**
 * Returns an ordered list of candidate shapes to search for. Semantic
 * candidates only ever appear when their keywords were actually detected
 * in the content — this is what stops a generic "4 things" slide from
 * landing on a SWOT template.
 */
function ds_tpl_classify_slide( $title, $subtitle, $content, $type = '' ) {
    $content = is_array($content) ? $content : array();
    $haystack = strtolower(trim($title . ' ' . $subtitle . ' ' . implode(' ', $content)));

    $candidates = array();

    // Quote slides — show nothing
if ( $type === 'quote' ) {
    return array();
}

// Title/header slides — show only header templates
if ( $type === 'title' ) {
    return array( array('shape' => 'header', 'semantic' => false) );
}

    if ( preg_match('/\bswot\b/', $haystack) ||
         (strpos($haystack, 'strength') !== false && strpos($haystack, 'weakness') !== false) ) {
        $candidates[] = array('shape' => 'swot', 'semantic' => true);
    }

if ( preg_match('/\bpros\s*(\&|and)\s*cons\b/', $haystack) ||
         (strpos($haystack, 'pros') !== false && strpos($haystack, 'cons') !== false) ) {
        $candidates[] = array('shape' => 'pros_cons', 'semantic' => true);
    }
    if ( preg_match('/\bpestle\b/', $haystack) ||
         (strpos($haystack, 'political') !== false && strpos($haystack, 'economic') !== false && strpos($haystack, 'legal') !== false) ) {
        $candidates[] = array('shape' => 'pestle', 'semantic' => true);
    }
    if ( preg_match('/\bbusiness model canvas\b/', $haystack) ) {
        $candidates[] = array('shape' => 'business_model_canvas', 'semantic' => true);
    }

    $count = count($content);
$count = max(1, min(14, $count ?: 1));

    $heads = array();
    foreach ( $content as $line ) {
        $p = ds_tpl_split_colon($line);
        $heads[] = strtolower($p['h']);
    }

    $is_timeline = ($type === 'timeline');

// Detect roadmap/milestone by slide title keywords
$title_lower = strtolower(trim($title . ' ' . $subtitle));
$roadmap_keywords = array('roadmap', 'milestone', 'milestones', 'timeline', 'journey', 'phases', 'rollout', 'plan', 'schedule', 'roadshow');
$is_roadmap_title = false;
foreach ($roadmap_keywords as $kw) {
    if (strpos($title_lower, $kw) !== false) {
        $is_roadmap_title = true;
        break;
    }
}

if ( ! $is_timeline && count($heads) ) {
    $year_like = array_filter($heads, function ($h) {
        return preg_match('/^(19|20)\d{2}\b|^q[1-4]\b|^phase\b|^stage\b|^year\b|^milestone\b/', $h);
    });
    $is_timeline = count($year_like) === count($heads);
}

// If title says roadmap/milestone, force timeline detection
if ( $is_roadmap_title ) $is_timeline = true;

    $is_steps = false;
    if ( ! $is_timeline && count($heads) ) {
        $step_like = array_filter($heads, function ($h) { return preg_match('/^step\b/', $h); });
        $is_steps = count($step_like) === count($heads);
    }

    if ( $is_timeline ) {
    $candidates[] = array('shape' => 'timeline',                'semantic' => false);
    $candidates[] = array('shape' => 'roadmap',                 'semantic' => false);
    $candidates[] = array('shape' => 'milestone',               'semantic' => false);
    $candidates[] = array('shape' => 'steps_' . $count,        'semantic' => false);
    $candidates[] = array('shape' => 'generic_grid_' . $count, 'semantic' => false);
} elseif ( $is_steps ) {
    $candidates[] = array('shape' => 'steps_' . $count,        'semantic' => false);
    $candidates[] = array('shape' => 'generic_grid_' . $count, 'semantic' => false);
} else {
    // Only show steps templates — no generic_grid
    // For 1-point slides, only match if it has real heading+explanation content
    if ( $count === 1 ) {
        $first = ds_tpl_split_colon($content[0] ?? '');
        if ( $first['h'] && $first['d'] ) {
            $candidates[] = array('shape' => 'steps_1', 'semantic' => false);
        }
        // else: no candidates — show nothing for image/quote-like single slides
    } else {
        $candidates[] = array('shape' => 'steps_' . $count, 'semantic' => false);
    }
}

    return $candidates;
}
}

// ════════════════════════════════════════════════════════════════════════
// PART C — DLM file lookup helpers
// ════════════════════════════════════════════════════════════════════════

if ( ! function_exists('ds_tpl_get_template_file_url') ) {
function ds_tpl_get_template_file_url( $dlm_post_id ) {
    $versions = get_posts(array(
        'post_type'      => 'dlm_download_version',
        'post_parent'    => $dlm_post_id,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ));
    if ( empty($versions) ) return '';
    $vid = $versions[0];

    // Try all known DLM meta keys for file URL
    $keys = array('_files', '_download_files', '_dlm_download_file', 'dlm_download_file', '_file_url', 'file_url');
    foreach ( $keys as $key ) {
        $val = get_post_meta($vid, $key, true);
        // JSON array format: ["https://..."]
        if ( is_string($val) && strpos($val, '[') === 0 ) {
            $decoded = json_decode($val, true);
            if ( is_array($decoded) && ! empty($decoded[0]) && strpos($decoded[0], 'http') === 0 ) return $decoded[0];
        }
        if ( $val && is_string($val) && strpos($val, 'http') === 0 ) return $val;
        if ( is_array($val) && ! empty($val[0]) && strpos($val[0], 'http') === 0 ) return $val[0];
    }

    // Fallback: check all meta for this version and find any URL ending in .pptx
    global $wpdb;
    $all_meta = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
            $vid
        )
    );
    foreach ( $all_meta as $row ) {
        $v = maybe_unserialize($row->meta_value);
        if ( is_string($v) && strpos($v, 'http') === 0 && strpos($v, '.pptx') !== false ) return $v;
        if ( is_array($v) ) {
            foreach ( $v as $item ) {
                if ( is_string($item) && strpos($item, 'http') === 0 && strpos($item, '.pptx') !== false ) return $item;
            }
        }
    }

    return '';
}
}

if ( ! function_exists('ds_tpl_url_to_path') ) {
function ds_tpl_url_to_path( $url ) {
    if ( ! $url ) return '';
    $upload_dir = wp_upload_dir();
    if ( strpos($url, $upload_dir['baseurl']) === 0 ) {
        return str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
    }
    $site_url = site_url();
    if ( strpos($url, $site_url) === 0 ) {
        return ABSPATH . ltrim(str_replace($site_url, '', $url), '/');
    }
    return '';
}
}

if ( ! function_exists('ds_tpl_rrmdir') ) {
function ds_tpl_rrmdir( $dir ) {
    if ( ! $dir || ! is_dir($dir) ) return;
    $items = @scandir($dir);
    if ( ! $items ) { @rmdir($dir); return; }
    foreach ( $items as $it ) {
        if ( $it === '.' || $it === '..' ) continue;
        $p = $dir . '/' . $it;
        is_dir($p) ? ds_tpl_rrmdir($p) : @unlink($p);
    }
    @rmdir($dir);
}
}

// ════════════════════════════════════════════════════════════════════════
// PART D — The injection engine
// ════════════════════════════════════════════════════════════════════════

if ( ! function_exists('ds_tpl_get_profile_defaults') ) {
/**
 * Fallback values for the header/cover-slide fields when the user hasn't
 * typed their own. Job Title and Company Name aren't standard WordPress
 * profile fields, so this checks a few common custom-field key names —
 * adjust the candidate list below if this site stores them under a
 * different meta key.
 */
function ds_tpl_get_profile_defaults() {
    $user = wp_get_current_user();
    $defaults = array(
        'presenter_name' => $user->display_name ?: trim($user->first_name . ' ' . $user->last_name),
        'job_title'      => '',
        'company_name'   => '',
        'website'        => $user->user_url ?: '',
        'email'          => $user->user_email ?: '',
        'date'           => date_i18n('F j, Y'),
    );
    $candidates = array(
        'job_title'    => array('job_title', 'position', 'designation', 'title'),
        'company_name' => array('company', 'company_name', 'organization', 'organisation'),
    );
    foreach ( $candidates as $field => $meta_keys ) {
        foreach ( $meta_keys as $meta_key ) {
            $val = get_user_meta($user->ID, $meta_key, true);
            if ( $val !== '' ) { $defaults[$field] = sanitize_text_field($val); break; }
        }
    }
    return $defaults;
}
}

if ( ! function_exists('ds_tpl_sanitize_slide_param') ) {
function ds_tpl_sanitize_slide_param( $slide_in ) {
    $slide = array(
        'title'    => sanitize_text_field(is_array($slide_in) ? ($slide_in['title']    ?? '') : ''),
        'subtitle' => sanitize_text_field(is_array($slide_in) ? ($slide_in['subtitle'] ?? '') : ''),
        'content'  => array(),
    );
    if ( is_array($slide_in) && ! empty($slide_in['content']) && is_array($slide_in['content']) ) {
        foreach ( $slide_in['content'] as $line ) $slide['content'][] = sanitize_text_field($line);
    }

    // Header/cover-slide fields: use whatever the user actually typed; for
    // anything left blank, fall back to their WordPress account profile.
    $profile = ds_tpl_get_profile_defaults();
    foreach ( array('presenter_name', 'job_title', 'company_name', 'website', 'email', 'date') as $key ) {
        $provided = is_array($slide_in) ? sanitize_text_field($slide_in[$key] ?? '') : '';
        $slide[$key] = ($provided !== '') ? $provided : $profile[$key];
    }

    return $slide;
}
}

if ( ! function_exists('ds_tpl_build_content_map') ) {
function ds_tpl_build_content_map( $slide ) {
    $map = array();
    $map['title header'] = isset($slide['title']) ? (string) $slide['title'] : '';
    $map['sub header']   = isset($slide['subtitle']) ? (string) $slide['subtitle'] : '';

    // Header/cover-slide shape labels (separate from the body-slide labels
    // above -- both can exist in the same map with no conflict).
    $map['presentation title']        = isset($slide['title'])          ? (string) $slide['title']          : '';
    $map['subtitle']                  = isset($slide['subtitle'])       ? (string) $slide['subtitle']       : '';
    $map['presenter name']            = isset($slide['presenter_name']) ? (string) $slide['presenter_name'] : '';
    $map['job title / designation']   = isset($slide['job_title'])      ? (string) $slide['job_title']      : '';
    $map['company name']              = isset($slide['company_name'])  ? (string) $slide['company_name']   : '';
    $map['date']                      = isset($slide['date'])           ? (string) $slide['date']           : '';
    $map['website']                  = isset($slide['website'])        ? (string) $slide['website']        : '';
    $map['email']                    = isset($slide['email'])          ? (string) $slide['email']          : '';

    $content = isset($slide['content']) && is_array($slide['content']) ? $slide['content'] : array();
$n = 0;
foreach ( $content as $line ) {
    $n++;
    $pair = ds_tpl_split_colon($line);
    $map['point ' . $n . ' heading']     = $pair['h'];
    $map['point ' . $n . ' explanation'] = $pair['d'];
}
// Fill dummy points up to 14 so templates with more slots than content still render
// User can delete these dummy points themselves after downloading
for ( $extra = $n + 1; $extra <= 14; $extra++ ) {
    $map['point ' . $extra . ' heading']     = 'Additional Point ' . $extra;
    $map['point ' . $extra . ' explanation'] = 'You can delete this point or add your own content here.';
}

    if ( ! empty($map['sub header']) ) {
        $map['explanation'] = $map['sub header'];
    } elseif ( $n === 1 && $map['point 1 explanation'] === '' ) {
        $map['explanation'] = $map['point 1 heading'];
    }

    return $map;
}
}

if ( ! function_exists('ds_tpl_inject_slide_xml') ) {
function ds_tpl_inject_slide_xml( $xml, $map, &$changed_any, &$matched_keys = null ) {
    $A_NS = 'http://schemas.openxmlformats.org/drawingml/2006/main';

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = true;
    $prev_errs = libxml_use_internal_errors(true);
    $ok = $dom->loadXML($xml);
    libxml_use_internal_errors($prev_errs);
    if ( ! $ok ) return null;

    $paragraphs = $dom->getElementsByTagNameNS($A_NS, 'p');

    foreach ( $paragraphs as $p ) {
        $runs = array();
        $text = '';
        foreach ( $p->childNodes as $child ) {
            if ( $child->nodeType === XML_ELEMENT_NODE && $child->localName === 'r' && $child->namespaceURI === $A_NS ) {
                $tNodes = $child->getElementsByTagNameNS($A_NS, 't');
                $t = $tNodes->length ? $tNodes->item(0) : null;
                $runs[] = array('run' => $child, 't' => $t);
                if ( $t ) $text .= $t->textContent;
            }
        }
        if ( ! count($runs) ) continue;

        $label = strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
        if ( $label === '' || ! array_key_exists($label, $map) ) continue;

        $new_text = $map[$label];
        $first = $runs[0];
        if ( $first['t'] ) {
            $first['t']->nodeValue = $new_text;
        } else {
            $tNode = $dom->createElementNS($A_NS, 'a:t');
            $tNode->appendChild($dom->createTextNode($new_text));
            $first['run']->appendChild($tNode);
        }
        for ( $i = 1; $i < count($runs); $i++ ) {
            $p->removeChild($runs[$i]['run']);
        }
        $changed_any = true;
        if ( is_array($matched_keys) ) $matched_keys[$label] = true;
    }

    return $dom->saveXML();
}
}

if ( ! function_exists('ds_tpl_inject_into_pptx') ) {
function ds_tpl_inject_into_pptx( $src_path, $dest_path, $slide ) {
    if ( ! class_exists('ZipArchive') ) {
        return new WP_Error('no_zip', 'ZipArchive is not available on this server.');
    }
    if ( ! copy($src_path, $dest_path) ) {
        return new WP_Error('copy_fail', 'Could not copy the template file.');
    }

    $zip = new ZipArchive();
    if ( $zip->open($dest_path) !== true ) {
        @unlink($dest_path);
        return new WP_Error('zip_open', 'Could not open the template .pptx.');
    }

    $map = ds_tpl_build_content_map($slide);
    $changed_any  = false;
    $matched_keys = array();
    $idx = 1;
    while ( ($slide_xml = $zip->getFromName('ppt/slides/slide' . $idx . '.xml')) !== false ) {
        $new_xml = ds_tpl_inject_slide_xml($slide_xml, $map, $changed_any, $matched_keys);
        if ( $new_xml !== null ) {
            $zip->addFromString('ppt/slides/slide' . $idx . '.xml', $new_xml);
        }
        $idx++;
    }
    $zip->close();
	if ( $changed_any && has_filter('ds_tpl_filled_pptx_bytes') ) {
    $bytes = file_get_contents($dest_path);
    if ( $bytes !== false ) {
        // Snippet 32's injector expects a list of {slot_name, replacement}
        // entries, not the raw key=>text map, and takes three arguments.
        $slots = array();
        foreach ( (array) $map as $slot_name => $slot_text ) {
            $slots[] = array('slot_name' => $slot_name, 'replacement' => $slot_text);
        }
        $filtered = apply_filters('ds_tpl_filled_pptx_bytes', $bytes, $slots, '');
        if ( is_string($filtered) && strlen($filtered) > 100 ) {
            file_put_contents($dest_path, $filtered);
        }
    }
}

    $has_point_match = false;
foreach ( array_keys($matched_keys) as $k ) {
    if ( preg_match('/^point\s+\d+\s+(heading|explanation)$/', $k) ) { $has_point_match = true; break; }
}
$has_cover_match = isset($matched_keys['presentation title'])
    || isset($matched_keys['title header'])
    || isset($matched_keys['company name']);
if ( ! $has_point_match && ! $has_cover_match ) {
    return false;
}

    return $changed_any;
}
}

// ════════════════════════════════════════════════════════════════════════
// PART E — Optional live-preview rendering via LibreOffice. Every public
// function here degrades to "not available" instead of erroring, so the
// rest of the feature keeps working with static thumbnails if this
// server can't run soffice.
// ════════════════════════════════════════════════════════════════════════

if ( ! function_exists('ds_tpl_find_soffice') ) {
function ds_tpl_find_soffice( $force_refresh = false ) {
    if ( $force_refresh ) delete_transient('ds_tpl_soffice_path');
    $cached = get_transient('ds_tpl_soffice_path');
    if ( $cached !== false ) return $cached; // '' means "checked, not found"

    $path = '';
    if ( function_exists('shell_exec') ) {
        $which = @shell_exec('command -v soffice 2>/dev/null');
        if ( $which && trim($which) !== '' ) {
            $path = trim($which);
        } else {
            foreach ( array('/usr/bin/soffice', '/usr/lib/libreoffice/program/soffice', '/opt/libreoffice/program/soffice') as $cand ) {
                if ( file_exists($cand) && is_executable($cand) ) { $path = $cand; break; }
            }
        }
    }

    set_transient('ds_tpl_soffice_path', $path, HOUR_IN_SECONDS);
    return $path;
}
}

if ( ! function_exists('ds_tpl_convert_pptx_to_png') ) {
/**
 * Converts a single-slide .pptx to a PNG of its first slide. Returns the
 * local PNG path on success, or false on any failure — never throws,
 * never leaves the request hanging past $timeout_secs.
 */
function ds_tpl_convert_pptx_to_png( $pptx_path, $timeout_secs = 25 ) {
    if ( ! file_exists($pptx_path) ) return false;
    $soffice = ds_tpl_find_soffice();
    if ( ! $soffice ) return false;
    if ( ! function_exists('proc_open') ) return false;

    $tmp = get_temp_dir();
    $out_dir     = trailingslashit($tmp) . 'ds-tpl-out-'  . wp_generate_password(8, false, false);
    $profile_dir = trailingslashit($tmp) . 'ds-tpl-prof-' . wp_generate_password(8, false, false);
    if ( ! wp_mkdir_p($out_dir) ) return false;

    $cmd = escapeshellarg($soffice)
         . ' --headless --norestore -env:UserInstallation=file://' . $profile_dir
         . ' --convert-to png --outdir ' . escapeshellarg($out_dir) . ' ' . escapeshellarg($pptx_path);

    $env = getenv();
    if ( ! is_array($env) ) $env = array();
    $env['SAL_USE_VCLPLUGIN'] = 'svp'; // headless rendering backend, avoids display-server issues

    $descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
    $proc = @proc_open($cmd, $descriptors, $pipes, null, $env);

    $png_path = false;
    if ( is_resource($proc) ) {
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $start  = time();
        $status = proc_get_status($proc);
        while ( $status && $status['running'] && (time() - $start) < $timeout_secs ) {
            usleep(200000);
            $status = proc_get_status($proc);
        }
        if ( $status && $status['running'] ) {
            @proc_terminate($proc, 9);
            usleep(200000);
        }
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        @proc_close($proc);

        $base = pathinfo($pptx_path, PATHINFO_FILENAME);
        $expected = trailingslashit($out_dir) . $base . '.png';
        if ( file_exists($expected) ) {
            $png_path = $expected;
        } else {
            $found = glob(trailingslashit($out_dir) . '*.png');
            if ( ! empty($found) ) $png_path = $found[0];
        }
    }

    ds_tpl_rrmdir($profile_dir);
    if ( ! $png_path ) ds_tpl_rrmdir($out_dir);

    return $png_path;
}
}

if ( ! function_exists('ds_tpl_render_cache_key') ) {
function ds_tpl_render_cache_key( $template_id, $slide ) {
    return 'ds_tpl_render_' . md5($template_id . '|' . wp_json_encode(array(
        $slide['title'] ?? '', $slide['subtitle'] ?? '', $slide['content'] ?? array(),
    )));
}
}

// ════════════════════════════════════════════════════════════════════════
// PART F — REST endpoints
// ════════════════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/match-templates', array(
        'methods'             => 'POST',
        'callback'            => 'ds_tpl_rest_match',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/inject-template', array(
        'methods'             => 'POST',
        'callback'            => 'ds_tpl_rest_inject',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/render-template-preview', array(
        'methods'             => 'POST',
        'callback'            => 'ds_tpl_rest_render_preview',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/tpl-debug', array(
        'methods'             => 'GET',
        'callback'            => 'ds_tpl_rest_debug',
        'permission_callback' => function () { return current_user_can('manage_options'); }, // server details: admin only
    ));
});

if ( ! function_exists('ds_tpl_query_templates_for_shape') ) {
function ds_tpl_query_templates_for_shape( $shape, $limit = 6 ) {
    if ( ! $shape ) return array();
    $q = new WP_Query(array(
        'post_type'      => 'dlm_download',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_query'     => array(
            'relation' => 'AND',
            array('key' => '_ds_tpl_enabled', 'value' => '1'),
            array('key' => '_ds_tpl_shape',   'value' => $shape),
        ),
    ));
    $out = array();
    foreach ( $q->posts as $post ) {
        $thumb = get_the_post_thumbnail_url($post->ID, 'medium') ?: get_the_post_thumbnail_url($post->ID, 'full');
        $out[] = array(
            'id'    => $post->ID,
            'title' => $post->post_title,
            'thumb' => $thumb ?: '',
        );
    }
    return $out;
}
}

if ( ! function_exists('ds_tpl_query_templates_by_count_fallback') ) {
/**
 * Looser fallback used only when the exact shape vocabulary finds nothing:
 * any enabled, non-semantic template whose shape tag simply ends in the
 * same item count (e.g. "stats_3", "metrics_3") counts as a match too,
 * instead of requiring everyone to standardize on the exact suggested
 * wording. Semantic templates (SWOT, etc.) are never included here.
 */
function ds_tpl_query_templates_by_count_fallback( $count, $exclude_ids, $limit = 6 ) {
    $q = new WP_Query(array(
        'post_type'      => 'dlm_download',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array('key' => '_ds_tpl_enabled', 'value' => '1'),
        ),
    ));
    $out = array();
    foreach ( $q->posts as $post ) {
        if ( in_array($post->ID, $exclude_ids) ) continue;
        if ( get_post_meta($post->ID, '_ds_tpl_semantic', true) ) continue;
        $shape = get_post_meta($post->ID, '_ds_tpl_shape', true);
        if ( preg_match('/_(\d+)$/', $shape, $m) && (int) $m[1] === (int) $count ) {
            $thumb = get_the_post_thumbnail_url($post->ID, 'medium') ?: get_the_post_thumbnail_url($post->ID, 'full');
            $out[] = array('id' => $post->ID, 'title' => $post->post_title, 'thumb' => $thumb ?: '');
            if ( count($out) >= $limit ) break;
        }
    }
    return $out;
}
}

if ( ! function_exists('ds_tpl_rest_match') ) {
function ds_tpl_rest_match( WP_REST_Request $req ) {
    $slide_in = $req->get_param('slide');
    if ( ! is_array($slide_in) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No slide data'));
    }
    $type  = sanitize_text_field($slide_in['type'] ?? '');
    $slide = ds_tpl_sanitize_slide_param($slide_in);

    $candidates = ds_tpl_classify_slide($slide['title'], $slide['subtitle'], $slide['content'], $type);
    // Check if this slide was classified as timeline
    $is_timeline = false;
    foreach ( $candidates as $cand ) {
        if ( in_array($cand['shape'], array('timeline', 'roadmap', 'milestone')) ) {
            $is_timeline = true;
            break;
        }
    }

    $results = array();
    $seen    = array();
    $timeline_shapes = array('timeline', 'roadmap', 'milestone');

    if ( $is_timeline ) {
        // Timeline/roadmap/milestone: show timeline/roadmap/milestone first, then steps_3 to steps_6
        $timeline_search = array_merge(
            $timeline_shapes,
            array('steps_3', 'steps_4', 'steps_5', 'steps_6')
        );
        foreach ( $timeline_search as $shape ) {
            $posts = ds_tpl_query_templates_for_shape($shape, 6);
            foreach ( $posts as $p ) {
                if ( isset($seen[$p['id']]) ) continue;
                $seen[$p['id']] = true;
                $p['matched_shape'] = $shape;
                $p['semantic']      = false;
                $results[] = $p;
            }
            if ( count($results) >= 6 ) break;
        }
    } else {
        // Non-timeline: exact shape match only, no fallback, no cross-contamination
        foreach ( $candidates as $cand ) {
            // Skip timeline/roadmap/milestone shapes for non-timeline slides
            if ( in_array($cand['shape'], $timeline_shapes) ) continue;
            $posts = ds_tpl_query_templates_for_shape($cand['shape'], 6);
            foreach ( $posts as $p ) {
                if ( isset($seen[$p['id']]) ) continue;
                // Double-check: never show timeline templates on non-timeline slides
                $p_shape = get_post_meta($p['id'], '_ds_tpl_shape', true);
                if ( in_array($p_shape, $timeline_shapes) ) continue;
                $seen[$p['id']] = true;
                $p['matched_shape'] = $cand['shape'];
                $p['semantic']      = $cand['semantic'];
                $results[] = $p;
            }
            if ( count($results) >= 6 ) break;
        }
        // NO fallback for non-timeline slides — if no exact match, show nothing
    }

    return rest_ensure_response(array(
        'success'         => true,
        'templates'       => array_slice($results, 0, 6),
        'detected_shapes' => wp_list_pluck($candidates, 'shape'),
    ));
}
}

if ( ! function_exists('ds_tpl_fetch_template_src_path') ) {
/**
 * Resolves a dlm_download template to a local filesystem path, downloading
 * a temp copy if needed. Returns array($path, $tmp_to_cleanup) or
 * array(false, '') on failure.
 */
function ds_tpl_fetch_template_src_path( $template_id ) {
    $file_url = ds_tpl_get_template_file_url($template_id);
    if ( ! $file_url ) return array(false, '');

    $src_path = ds_tpl_url_to_path($file_url);
    if ( $src_path && file_exists($src_path) ) return array($src_path, '');

    // download_url() lives in wp-admin/includes/file.php, which is NOT loaded
    // during a REST request. Marketplace template files now live off-site (R2),
    // so ds_tpl_url_to_path() returns nothing and this branch runs -- it fataled
    // with "undefined function download_url()", surfacing as a 500.
    if ( ! function_exists('download_url') ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $downloaded = download_url($file_url, 60);
    if ( is_wp_error($downloaded) ) return array(false, '');
    return array($downloaded, $downloaded);
}
}

if ( ! function_exists('ds_tpl_rest_inject') ) {
function ds_tpl_rest_inject( WP_REST_Request $req ) {
    $template_id = (int) $req->get_param('template_id');
    if ( ! $template_id || get_post_type($template_id) !== 'dlm_download' ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Invalid template'));
    }
    if ( ! get_post_meta($template_id, '_ds_tpl_enabled', true) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Template is not enabled for AI Studio matching'));
    }

    $slide = ds_tpl_sanitize_slide_param($req->get_param('slide'));

    // ── Credit gate for Pick-a-Design ──────────────────────────────────────
    $uid        = get_current_user_id();
    $__ppt_opt_tpl = get_option('ds_ppt_credit_settings');
    $tpl_cost   = ( is_array($__ppt_opt_tpl) && isset($__ppt_opt_tpl['template_cost']) && $__ppt_opt_tpl['template_cost'] !== '' ) ? (int)$__ppt_opt_tpl['template_cost'] : 5;
    $tpl_bal    = function_exists('ds_get_credits') ? ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
    if ( $tpl_bal < $tpl_cost ) {
        return rest_ensure_response(array(
            'success' => false,
            'error'   => "You need {$tpl_cost} credits to download this template.",
        ));
    }
    // Credits are charged only after the filled .pptx actually exists.
    // Previously the deduction ran here, so every failed build still cost
    // the user credits.
    $ds_tpl_charge = function() use ($uid, $tpl_cost, $tpl_bal, $template_id) {
    if ( function_exists('ds_deduct_credits') ) {
        if ( ! ds_deduct_credits($uid, $tpl_cost) ) return false;
    } else {
        update_user_meta($uid, 'ds_ai_credits', max(0, $tpl_bal - $tpl_cost));
    }
    // Log to credit history
    $tpl_title  = get_the_title($template_id) ?: 'Pick-a-Design Template';
    $ppt_log    = get_user_meta($uid, 'ds_ppt_generations', true) ?: [];
    array_unshift($ppt_log, [
        'date'       => current_time('mysql'),
        'prompt'     => $tpl_title,
        'credits'    => $tpl_cost,
        'num_slides' => 0,
        'source'     => 'pick-a-design',
    ]);
    update_user_meta($uid, 'ds_ppt_generations', array_slice($ppt_log, 0, 100));
    return true;
    };

    // ────────────────────────────────────────────────────────────────────────

      list($src_path, $tmp_fetch) = ds_tpl_fetch_template_src_path($template_id);
    if ( ! $src_path ) {
        return rest_ensure_response(array('success' => false, 'error' => "Could not locate this template's file"));
    }

    $upload_dir = wp_upload_dir();
    $out_dir    = trailingslashit($upload_dir['basedir']) . 'ds-ppt-templates';
    if ( ! file_exists($out_dir) ) wp_mkdir_p($out_dir);

    $fname     = 'tpl-' . $template_id . '-' . get_current_user_id() . '-' . time() . '-' . wp_generate_password(5, false, false) . '.pptx';
    $dest_path = trailingslashit($out_dir) . $fname;

    $result = ds_tpl_inject_into_pptx($src_path, $dest_path, $slide);
    if ( $tmp_fetch ) @unlink($tmp_fetch);

    if ( is_wp_error($result) ) {
        return rest_ensure_response(array('success' => false, 'error' => $result->get_error_message()));
    }

    if ( $result === false ) {
        @unlink($dest_path);
        return rest_ensure_response(array(
            'success' => false,
            'error'   => "This template's placeholder labels don't match the expected format (Title Header, Sub Header, Point 1 Heading, etc.), so nothing could be filled in.",
        ));
    }

    if ( ! $ds_tpl_charge() ) {
        @unlink($dest_path);
        return rest_ensure_response(array('success' => false, 'error' => "You need {$tpl_cost} credits to download this template."));
    }

    $dest_url = trailingslashit($upload_dir['baseurl']) . 'ds-ppt-templates/' . $fname;

    return rest_ensure_response(array(
        'success'      => true,
        'download_url' => $dest_url,
        'filled'       => (bool) $result,
    ));
}
}

if ( ! function_exists('ds_tpl_rest_render_preview') ) {
/**
 * Fills the template with the slide's real content AND tries to render a
 * live PNG of the result. Always returns a usable preview_url + a real
 * download_url for the filled .pptx — preview_url is the live render when
 * possible, or the template's own static thumbnail when LibreOffice isn't
 * available on this server. The "rendered" flag tells the front-end which
 * one it got.
 */
function ds_tpl_rest_render_preview( WP_REST_Request $req ) {
    $template_id = (int) $req->get_param('template_id');
    if ( ! $template_id || get_post_type($template_id) !== 'dlm_download' ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Invalid template'));
    }
    if ( ! get_post_meta($template_id, '_ds_tpl_enabled', true) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Template is not enabled for AI Studio matching'));
    }

    $slide = ds_tpl_sanitize_slide_param($req->get_param('slide'));

    $cache_key = ds_tpl_render_cache_key($template_id, $slide);
    $cached = get_transient($cache_key);
    if ( is_array($cached) && ! empty($cached['download_path']) && file_exists($cached['download_path']) ) {
        return rest_ensure_response(array(
            'success'      => true,
            'rendered'     => $cached['rendered'],
            'preview_url'  => $cached['preview_url'],
            'download_url' => $cached['download_url'],
        ));
    }

    list($src_path, $tmp_fetch) = ds_tpl_fetch_template_src_path($template_id);
    if ( ! $src_path ) {
        return rest_ensure_response(array('success' => false, 'error' => "Could not locate this template's file"));
    }

    $upload_dir = wp_upload_dir();
    $out_dir    = trailingslashit($upload_dir['basedir']) . 'ds-ppt-templates';
    if ( ! file_exists($out_dir) ) wp_mkdir_p($out_dir);

    $fname     = 'tpl-' . $template_id . '-' . get_current_user_id() . '-' . time() . '-' . wp_generate_password(5, false, false) . '.pptx';
    $dest_path = trailingslashit($out_dir) . $fname;

    $fill_result = ds_tpl_inject_into_pptx($src_path, $dest_path, $slide);
    if ( $tmp_fetch ) @unlink($tmp_fetch);

    if ( is_wp_error($fill_result) ) {
        return rest_ensure_response(array('success' => false, 'error' => $fill_result->get_error_message()));
    }

    if ( $fill_result === false ) {
        @unlink($dest_path);
        return rest_ensure_response(array(
            'success' => false,
            'error'   => "This template's placeholder labels don't match the expected format (Title Header, Sub Header, Point 1 Heading, etc.), so nothing could be filled in.",
        ));
    }

    $download_url = trailingslashit($upload_dir['baseurl']) . 'ds-ppt-templates/' . $fname;

    $rendered    = false;
    $preview_url = '';
    $png_path    = ds_tpl_convert_pptx_to_png($dest_path);
    if ( $png_path && file_exists($png_path) ) {
        $png_fname = 'tpl-preview-' . $template_id . '-' . get_current_user_id() . '-' . time() . '.png';
        $png_dest  = trailingslashit($out_dir) . $png_fname;
        if ( @copy($png_path, $png_dest) ) {
            $preview_url = trailingslashit($upload_dir['baseurl']) . 'ds-ppt-templates/' . $png_fname;
            $rendered    = true;
        }
        ds_tpl_rrmdir(dirname($png_path));
    }
    if ( ! $rendered ) {
        $preview_url = get_the_post_thumbnail_url($template_id, 'large') ?: get_the_post_thumbnail_url($template_id, 'full') ?: '';
    }

    set_transient($cache_key, array(
        'rendered'      => $rendered,
        'preview_url'   => $preview_url,
        'download_url'  => $download_url,
        'download_path' => $dest_path,
    ), HOUR_IN_SECONDS);

    return rest_ensure_response(array(
        'success'      => true,
        'rendered'     => $rendered,
        'preview_url'  => $preview_url,
        'download_url' => $download_url,
    ));
}
}

if ( ! function_exists('ds_tpl_rest_debug') ) {
function ds_tpl_rest_debug( WP_REST_Request $req ) {
    $force   = (bool) $req->get_param('refresh');
    $soffice = ds_tpl_find_soffice($force);

    return rest_ensure_response(array(
        'ZipArchive'   => class_exists('ZipArchive') ? 'YES' : 'NO — required for everything in this feature',
        'shell_exec'   => function_exists('shell_exec') ? 'YES' : 'NO (disabled by host) — live preview cannot work',
        'proc_open'    => function_exists('proc_open')  ? 'YES' : 'NO (disabled by host) — live preview cannot work',
        'soffice_path' => $soffice ?: 'NOT FOUND — falling back to static template thumbnails',
        'live_preview' => $soffice ? 'ENABLED' : 'DISABLED (using static thumbnails — everything else still works)',
        'tip'          => 'Visit this URL again with ?refresh=1 to force a fresh check (e.g. right after installing LibreOffice).',
    ));
}
}


// ════════════════════════════════════════════════════════════════════════
// PART G — Front-end: a dedicated "🏆 Pick a Design" button + unified gallery
// (slide sidebar + design picker + combined-download, all in one panel)
// ════════════════════════════════════════════════════════════════════════

add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    $has_ppt = has_shortcode($post->post_content, 'ds_ai_ppt') || has_shortcode($post->post_content, 'ds_ai_studio');
    if ( ! $has_ppt ) return;

    $nonce = wp_create_nonce('wp_rest');
    ?>
<style>
#ds-tpl-pick-btn{position:absolute;left:130px;top:50%;transform:translateY(-50%);background:#e8442a;color:#fff;border:none;border-radius:8px;padding:9px 14px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;z-index:10;box-shadow:0 4px 14px rgba(0,0,0,.45);letter-spacing:.3px;white-space:nowrap;}
#ds-tpl-pick-btn:hover{background:#d13a22;}
#dsp-ppt-canvas-area{position:relative!important;}
#ds-tpl-pick-btn .ds-tpl-badge{background:#10b981;color:#fff;border-radius:10px;padding:1px 6px;font-size:9px;margin-left:4px;}

#ds-tpl-gallery-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:300000;display:none;align-items:center;justify-content:center;padding:20px;}
#ds-tpl-gallery-overlay.open{display:flex!important;}
#ds-tpl-gallery-box{background:#161221;border:1px solid #2d2245;border-radius:16px;width:100%;max-width:1280px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
#ds-tpl-gallery-head{padding:18px 22px;border-bottom:1px solid #2d2245;display:flex;align-items:center;gap:12px;flex-shrink:0;}
#ds-tpl-gallery-head h3{margin:0;color:#f3f4f6;font-size:17px;font-weight:800;}
#ds-tpl-gallery-head p{margin:2px 0 0;color:#9ca3af;font-size:12px;}
#ds-tpl-gallery-close{background:#221c33;border:1px solid #382a55;color:#c4b5fd;border-radius:8px;padding:8px 16px;cursor:pointer;font-size:12px;font-family:inherit;flex-shrink:0;}
#ds-tpl-combined-btn{background:#10b981;color:#fff;border:none;border-radius:8px;padding:10px 18px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;flex-shrink:0;display:inline-flex;align-items:center;gap:6px;}
#ds-tpl-combined-btn:hover{background:#0ea371;}

#ds-tpl-modal-body{flex:1;display:flex;overflow:hidden;}
#ds-tpl-slide-sidebar{width:170px;flex-shrink:0;overflow-y:auto;border-right:1px solid #2d2245;padding:14px 10px;background:#13101d;}
.ds-tpl-sb-label{color:#6b6485;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin:0 4px 10px;}
.ds-tpl-slide-thumb{position:relative;cursor:pointer;border:2px solid transparent;border-radius:8px;margin-bottom:10px;overflow:hidden;background:#1c1730;}
.ds-tpl-slide-thumb.active{border-color:#7c3aed;}
.ds-tpl-sb-img{position:relative;width:100%;aspect-ratio:16/9;background:#0d0a16;overflow:hidden;display:block;}
.ds-tpl-sb-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;}
/* DS-FIX — the sidebar thumbnail is a picture of the slide, not the slide.
   The clone inherits the editor's interactive chrome (grab cursor, Change
   Image / Crop buttons, selectable text), which made it look editable.
   Make it inert: no hover UI, no cursor change, no text selection. The
   click still lands on the card, so choosing a slide keeps working. */
#ds-tpl-slide-sidebar .ds-tpl-sb-img,
#ds-tpl-slide-sidebar .ds-tpl-sb-img *{pointer-events:none!important;cursor:default!important;user-select:none!important;-webkit-user-select:none!important;-moz-user-select:none!important;}
#ds-tpl-slide-sidebar .ds-tpl-sb-img .dsp-vp-cropbtn,
#ds-tpl-slide-sidebar .ds-tpl-sb-img .dsp-vp-imgreset,
#ds-tpl-slide-sidebar .ds-tpl-sb-img button,
#ds-tpl-slide-sidebar .ds-tpl-sb-img a{display:none!important;}
#ds-tpl-slide-sidebar .ds-tpl-slide-thumb{cursor:pointer;}
.ds-tpl-sb-blank{color:#6b6485;font-size:10px;padding:6px;text-align:center;line-height:1.3;}
.ds-tpl-sb-num{position:absolute;top:4px;left:4px;background:rgba(0,0,0,.7);color:#fff;font-size:10px;font-weight:700;border-radius:4px;padding:1px 5px;z-index:2;}
.ds-tpl-sb-badge{background:#10b981;color:#fff;font-size:10px;font-weight:700;padding:3px 0;text-align:center;}

#ds-tpl-gallery-grid-wrap{flex:1;overflow-y:auto;padding:20px 22px;}
#ds-tpl-gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;align-content:start;}
.ds-tpl-card{background:#1c1730;border:2px solid #2d2245;border-radius:12px;overflow:hidden;cursor:pointer;transition:border-color .15s,transform .15s;}
.ds-tpl-card:hover{border-color:#7c3aed;transform:translateY(-2px);}
.ds-tpl-card.ds-tpl-loading{cursor:default;}
.ds-tpl-card-img{width:100%;aspect-ratio:16/9;overflow:hidden;background:#0a0a0a;display:flex;align-items:center;justify-content:center;position:relative;}
.ds-tpl-card-img img{width:100%;height:100%;object-fit:cover;display:block;}
.ds-tpl-shimmer{width:100%;height:100%;background:linear-gradient(100deg,#1c1730 30%,#2a2245 50%,#1c1730 70%);background-size:200% 100%;animation:dsTplShimmer 1.3s infinite;}
@keyframes dsTplShimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
.ds-tpl-card-lbl{padding:9px 12px;font-size:12px;font-weight:700;color:#e5e7eb;background:#19142b;border-top:1px solid #2d2245;}
.ds-tpl-card-sub{padding:0 12px 9px;font-size:10.5px;color:#8b7fae;}
#ds-tpl-gallery-empty{grid-column:1/-1;text-align:center;padding:50px 20px;color:#4b5563;font-size:13px;}
#ds-tpl-gallery-foot{padding:8px 22px;border-top:1px solid #2d2245;color:#5b5375;font-size:11px;flex-shrink:0;display:flex;justify-content:space-between;align-items:center;}
#ds-tpl-combined-status{color:#9ca3af;font-size:11px;}
</style>

<div id="ds-tpl-gallery-overlay">
  <div id="ds-tpl-gallery-box">
    <div id="ds-tpl-gallery-head">
      <div style="flex:1;">
        <h3>🏆 Pick a Design from Marketplace</h3>
        <p id="ds-tpl-gallery-sub">Pick a design for this slide</p>
        <p id="ds-tpl-gallery-cost" style="margin:4px 0 0;font-size:12px;color:#a78bfa;">Applying a design costs <strong id="ds-tpl-gallery-cost-num">1</strong> credit<span id="ds-tpl-gallery-cost-plural"></span> per template</p>
      </div>
      <button id="ds-tpl-combined-btn" onclick="window.dsTplDownloadCombined()">⬇ Download Combined PPTX</button>
      <button id="ds-tpl-gallery-close" onclick="window.dsTplGalleryClose()">✕ Close</button>
    </div>
    <div id="ds-tpl-modal-body">
      <div id="ds-tpl-slide-sidebar"></div>
      <div id="ds-tpl-gallery-grid-wrap"><div id="ds-tpl-gallery-grid"><div id="ds-tpl-gallery-empty">Loading matches…</div></div></div>
    </div>
    <div id="ds-tpl-gallery-foot">
      <span>Designs render with your real content — each card finishes in a few seconds.</span>
      <span id="ds-tpl-combined-status"></span>
    </div>
  </div>
</div>

<script>
window.DSP_TPL_NONCE        = <?php echo wp_json_encode($nonce); ?>;
window.DSP_TPL_MATCH_URL    = <?php echo wp_json_encode(rest_url('ds/v1/match-templates'));          ?>;
window.DSP_TPL_RENDER_URL   = <?php echo wp_json_encode(rest_url('ds/v1/render-template-preview'));   ?>;
window.DSP_TPL_MERGE_URL    = <?php echo wp_json_encode(rest_url('ds/v1/merge-real-templates'));      ?>;
window.DSP_TPL_COMBINE_URL  = <?php echo wp_json_encode(rest_url('ds/v1/combine-full-deck')); ?>;
window.DSP_TPL_CREDIT_COST  = <?php
    $__dsp_tpl_cost_opt = get_option('ds_ppt_credit_settings');
    echo (int) ( ( is_array($__dsp_tpl_cost_opt) && isset($__dsp_tpl_cost_opt['template_cost']) && $__dsp_tpl_cost_opt['template_cost'] !== '' ) ? $__dsp_tpl_cost_opt['template_cost'] : 5 );
?>;
(function () {
'use strict';

function tplNonce() { return window.DSP_NONCE || window.DSP_TPL_NONCE || ''; }
function tplEsc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

var GAL = { idx: -1, open: false };
var _pickedCache = {};

// Clear picked cache when new presentation is generated
document.addEventListener('dsp-slides-ready', function() {
    _pickedCache = {};
    // Also clear _real_template from all slides
    if (window.S && window.S.data && window.S.data.slides) {
        window.S.data.slides.forEach(function(s) {
            if (s) delete s._real_template;
        });
    }
    tplRefreshButtonBadge();
});

// Also clear when S.data changes (new generation)
var _ds21LastSlideCount = 0;
setInterval(function() {
    if (!window.S || !window.S.data || !window.S.data.slides) return;
    var count = window.S.data.slides.length;
    if (count !== _ds21LastSlideCount) {
        _ds21LastSlideCount = count;
        // New slides loaded — clear all template selections
        _pickedCache = {};
        if (window.S.data.slides) {
            window.S.data.slides.forEach(function(s) {
                if (s) delete s._real_template;
            });
        }
        tplRefreshButtonBadge();
    }
}, 1000);

function tplInjectButton() {
  var bar = document.getElementById('dsp-ppt-bottom-bar');
  if (bar && !document.getElementById('ds-tpl-pick-btn')) {
    var btn = document.createElement('button');
    btn.id = 'ds-tpl-pick-btn';
    btn.innerHTML = 'Pick a Design';
    btn.onclick = window.dsTplGalleryOpen;
    bar.appendChild(btn);
  }
}

function tplRefreshButtonBadge() {
  if (!window.S || !window.S.data || !window.S.data.slides) return;
  var n = window.S.data.slides.filter(function (s) { return s && s._real_template; }).length;

  var btn = document.getElementById('ds-tpl-pick-btn');
  if (btn) {
    var existing = btn.querySelector('.ds-tpl-badge');
    if (existing) existing.remove();
    if (_pickedCache[window.S.idx] || (window.S.data.slides[window.S.idx] && window.S.data.slides[window.S.idx]._real_template)) {
      var b = document.createElement('span');
      b.className = 'ds-tpl-badge';
      b.textContent = '✓';
      btn.appendChild(b);
    }
  }
}

/* ── Apply the picked design straight onto the canvas + thumbnail ── */
function tplImgWrap(idx, url, rendered) {
  var esc = tplEsc(url);
  var banner = rendered ? '' :
    '<div style="position:absolute;top:0;left:0;right:0;background:rgba(0,0,0,.8);color:#fbbf24;font-size:.6em;font-weight:700;padding:8px 10px;text-align:center;line-height:1.4;">⚠ Static preview shown (live render unavailable on this server) — your real content IS saved in the downloaded file</div>';
  return '<div class="ds-tpl-applied" data-ds-tpl-url="' + esc + '" style="position:relative;width:100%;height:100%;overflow:hidden;">'
    + banner
    + '<img src="' + esc + '" style="width:100%;height:100%;object-fit:contain;display:block;background:#000;">'
    + '<button onclick="window.dsTplRemoveDesign(' + idx + ')" style="position:absolute;' + (rendered ? 'top:8px;' : 'top:38px;') + 'right:8px;background:rgba(0,0,0,.75);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;font-family:inherit;">✕ Remove design</button>'
    + '</div>';
}

function tplReassertCanvas() {
      /* DS-FIX — picking a design from the marketplace used to paint the
         template's preview image over the main editor canvas and keep it
         there. The pick is still recorded on the slide (_real_template) and
         still drives the download; it is now shown only inside the
         "Pick a Design" popup, and the editor keeps its own live slide. */
      return;
  var main = document.getElementById('dsp-ppt-main-slide');
  if (!main || !window.S || !window.S.data || !window.S.data.slides) return;
  var idx = window.S.idx;
  var slide = window.S.data.slides[idx];
  var rt = slide && slide._real_template;
  if (!rt || !rt.preview_url) return;
  var applied = main.querySelector('.ds-tpl-applied');
  if (applied && applied.getAttribute('data-ds-tpl-url') === rt.preview_url) return;
  main.innerHTML = tplImgWrap(idx, rt.preview_url, !!rt.rendered);
}

function tplReassertThumb(idx) {
      /* DS-FIX — same for the editor's thumbnail rail: the template preview
         no longer replaces the real slide thumbnail. */
      return;
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (!thumbs || !window.S || !window.S.data || !window.S.data.slides) return;
  var slide = window.S.data.slides[idx];
  var rt = slide && slide._real_template;
  var el = thumbs.children[idx];
  if (!el || !rt || !rt.preview_url) return;
  if (el.getAttribute('data-ds-tpl-url') === rt.preview_url) return;
  el.setAttribute('data-ds-tpl-url', rt.preview_url);
  el.innerHTML = '<img src="' + tplEsc(rt.preview_url) + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
}

function tplReassertAllThumbs() {
  if (!window.S || !window.S.data || !window.S.data.slides) return;
  window.S.data.slides.forEach(function (s, i) { if (s && s._real_template) tplReassertThumb(i); });
}

function tplEnsureCanvasObserver() {
      /* DS-FIX — the observer existed only to re-paint the template preview
         over the canvas after every re-render. Nothing to re-assert now. */
      return;
  var main = document.getElementById('dsp-ppt-main-slide');
  if (!main || main._dsTplObserved) return;
  main._dsTplObserved = true;
  var obs = new MutationObserver(function () { tplReassertCanvas(); });
  obs.observe(main, { childList: true });
}

window.dsTplRemoveDesign = function (idx) {
  if (!window.S || !window.S.data || !window.S.data.slides[idx]) return;
  delete window.S.data.slides[idx]._real_template;
  delete _pickedCache[idx];
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (thumbs && thumbs.children[idx]) thumbs.children[idx].removeAttribute('data-ds-tpl-url');
  var vid = (window.S.choices && window.S.choices['slide_' + idx]) || 'v1';
  if (window.dspVPPick) window.dspVPPick(idx, vid);
  tplRefreshButtonBadge();
  renderSlideSidebar();
  if (window.dspToast) window.dspToast('Reverted to the editable layout for this slide.', 'success');
};

/* ── Unified modal: slide sidebar + gallery + combined download ── */

function tplCloneRealThumbInto(targetEl, slideIdx) {
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (!thumbs || !thumbs.children[slideIdx]) return false;
  var src = thumbs.children[slideIdx];

  var clone = src.cloneNode(true);
  clone.removeAttribute('id');
  Array.prototype.forEach.call(clone.querySelectorAll('[id]'), function (el) { el.removeAttribute('id'); });
  clone.removeAttribute('onclick');
  clone.classList.remove('active', 'selected', 'dsp-thumb-active');

  targetEl.innerHTML = '';

      /* DS-FIX — the clone used to be pinned to the top-left and scaled by
         width alone. The editor's thumbnail box is 128x100 while this slot
         is 16:9, so the thumbnail's own letterbox bar showed as a black
         strip and the slide was cut off at the bottom. Scale to COVER and
         centre instead: the bars are cropped and the card shows the whole
         slide, edge to edge. */
      var wrap = document.createElement('div');
      wrap.style.cssText = 'position:absolute;inset:0;overflow:hidden;';
      wrap.appendChild(clone);
      targetEl.appendChild(wrap);

      function place() {
        /* DS-FIX — the editor's thumbnail ITEM is 128x100, but the real slide
           is the .dsp-thumb-box inside it: 128x72, sitting 29px down, with the
           slide-number label occupying the strip above. Centring the whole
           item therefore left that label strip visible as a black band and cut
           the bottom off the slide. Measure and align on the slide box. */
        var box = src.querySelector('.dsp-thumb-box') || src;
        var itemRect = src.getBoundingClientRect();
        var boxRect = box.getBoundingClientRect();
        var dstW = targetEl.clientWidth;
        var dstH = targetEl.clientHeight;
        if (!(boxRect.width > 0 && boxRect.height > 0 && dstW > 0 && dstH > 0)) return false;
        var offX = boxRect.left - itemRect.left;
        var offY = boxRect.top - itemRect.top;
        var scale = Math.max(dstW / boxRect.width, dstH / boxRect.height);
        clone.style.position = 'absolute';
        clone.style.width = itemRect.width + 'px';
        clone.style.height = itemRect.height + 'px';
        clone.style.transformOrigin = '0 0';
        clone.style.transform = 'scale(' + scale + ')';
        /* Put the slide box's centre on the slot's centre. */
        clone.style.left = (dstW / 2 - (offX + boxRect.width / 2) * scale) + 'px';
        clone.style.top = (dstH / 2 - (offY + boxRect.height / 2) * scale) + 'px';
        clone.style.pointerEvents = 'none';
        return true;
      }
      /* DS-FIX — the slot is measured before the sidebar's scrollbar appears,
         which made the first measurement ~20px too wide and over-scaled the
         thumbnail. Re-place on every size change of the slot instead of
         trusting one early reading. */
      place();
      if (window.ResizeObserver) {
        var _ro = new ResizeObserver(function () { place(); });
        _ro.observe(targetEl);
        targetEl.__dsThumbRO = _ro;
      } else {
        setTimeout(place, 80);
        setTimeout(place, 300);
        setTimeout(place, 900);
      }

      return true;
    }

function renderSlideSidebar() {
  var sb = document.getElementById('ds-tpl-slide-sidebar');
  if (!sb || !window.S || !window.S.data || !window.S.data.slides) return;
  /* DS-FIX — rebuilding the list reset it to the top, so picking a design
         for a slide further down snapped the sidebar back to slide 1.
         Remember where the user was and put them back at the end. */
      var _dsKeepScroll = sb.scrollTop;
      sb.innerHTML = '<div class="ds-tpl-sb-label">Slides</div>';
  window.S.data.slides.forEach(function (s, i) {
    var rt = s && s._real_template;
    var card = document.createElement('div');
    card.className = 'ds-tpl-slide-thumb' + (i === GAL.idx ? ' active' : '');
    card.innerHTML =
      '<div class="ds-tpl-sb-num">' + (i + 1) + '</div>' +
      '<div class="ds-tpl-sb-img"></div>' +
      (rt ? '<div class="ds-tpl-sb-badge">✓ Selected</div>' : '');
    card.onclick = function () { selectSlideInModal(i); };
    sb.appendChild(card);

    // Add remove button if this slide has a selected design
    if (rt) {
      var removeBtn = document.createElement('button');
      removeBtn.textContent = '✕ Remove design';
      removeBtn.style.cssText = 'width:100%;margin-top:4px;padding:4px 8px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:11px;cursor:pointer;font-weight:600;';
      removeBtn.onclick = (function(slideIdx) {
        return function(e) {
          e.stopPropagation();
          window.dsTplGalleryRemove(slideIdx);
        };
      })(i);
      card.appendChild(removeBtn);
    }

    var imgSlot = card.querySelector('.ds-tpl-sb-img');
    if (rt && rt.preview_url) {
      imgSlot.innerHTML = '<img src="' + tplEsc(rt.preview_url) + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
    } else {
      tplCloneRealThumbInto(imgSlot, i);
        }
      });

      /* DS-FIX — put the sidebar back where it was before the rebuild. */
      sb.scrollTop = _dsKeepScroll;
    }

function selectSlideInModal(idx) {
  GAL.idx = idx;
  renderSlideSidebar();
  dsTplGalleryLoad(idx);
}

window.dsTplGalleryOpen = function () {
  if (!window.S || !window.S.data || !window.S.data.slides || window.S.idx < 0) return;
  GAL.open = true;
  document.getElementById('ds-tpl-gallery-overlay').classList.add('open');
  document.getElementById('ds-tpl-combined-status').textContent = '';
  selectSlideInModal(window.S.idx);
};

window.dsTplGalleryClose = function () {
  GAL.open = false;
  document.getElementById('ds-tpl-gallery-overlay').classList.remove('open');
};

function dsTplGalleryLoad(idx) {
  var grid = document.getElementById('ds-tpl-gallery-grid');
  var sub  = document.getElementById('ds-tpl-gallery-sub');
  if (!grid) return;
  grid.innerHTML = '<div id="ds-tpl-gallery-empty">Looking for matching templates…</div>';

  var slide = window.S.data.slides[idx];
  if (sub) sub.textContent = 'Pick a design for slide ' + (idx + 1) + (slide.title ? ' — ' + slide.title : '');

  var costNumEl = document.getElementById('ds-tpl-gallery-cost-num');
  var costPluralEl = document.getElementById('ds-tpl-gallery-cost-plural');
  var tplCost = (typeof window.DSP_TPL_CREDIT_COST === 'number') ? window.DSP_TPL_CREDIT_COST : 1;
  if (costNumEl) costNumEl.textContent = tplCost;
  if (costPluralEl) costPluralEl.textContent = (tplCost === 1) ? '' : 's';

  fetch(window.DSP_TPL_MATCH_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': tplNonce() },
    body: JSON.stringify({ slide: { type: slide.type, title: slide.title, subtitle: slide.subtitle, content: slide.content } })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    var templates = (d && d.success && d.templates) ? d.templates : [];
    if (!GAL.open || GAL.idx !== idx) return;
    if (!templates.length) {
      grid.innerHTML = '<div id="ds-tpl-gallery-empty">No real templates tagged for this slide\'s shape yet.<br>Tag a few in wp-admin (per the AI Studio matching box) to see them here.</div>';
      return;
    }
    grid.innerHTML = '';
    var _tplQueue = templates.slice();
var _tplConcurrent = 0;
var _tplMaxConcurrent = 2;
function _tplRunNext() {
  while (_tplConcurrent < _tplMaxConcurrent && _tplQueue.length > 0) {
    var t = _tplQueue.shift();
    _tplConcurrent++;
    var _origFetchRef = window.fetch;
    dsTplRenderSkeleton(grid, idx, t);
    (function() {
      var checkDone = setInterval(function() {
        var loading = grid.querySelectorAll('.ds-tpl-loading').length;
        var total = grid.querySelectorAll('.ds-tpl-card').length;
        if (loading < _tplConcurrent || total === 0) {
          clearInterval(checkDone);
          _tplConcurrent = Math.max(0, _tplConcurrent - 1);
          _tplRunNext();
        }
      }, 500);
    })();
  }
}
_tplRunNext();
  })
  .catch(function () {
    grid.innerHTML = '<div id="ds-tpl-gallery-empty">Could not load matches. Please try again.</div>';
  });
}

function dsTplRenderSkeleton(grid, idx, t) {
  var card = document.createElement('div');
  card.className = 'ds-tpl-card ds-tpl-loading';
  card.innerHTML =
      '<div class="ds-tpl-card-img"><div class="ds-tpl-shimmer"></div></div>' +
      '<div class="ds-tpl-card-lbl">' + tplEsc(t.title || 'Template') + '</div>' +
      '<div class="ds-tpl-card-sub">✨ Rendering your content…</div>';
  grid.appendChild(card);

  var slide = window.S.data.slides[idx];

  function dropCard() {
    if (card.parentNode) card.parentNode.removeChild(card);
    if (!grid.querySelector('.ds-tpl-card')) {
      grid.innerHTML = '<div id="ds-tpl-gallery-empty">None of the matched templates could be rendered for this slide.</div>';
    }
  }

  fetch(window.DSP_TPL_RENDER_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': tplNonce() },
    body: JSON.stringify({ template_id: t.id, slide: { title: slide.title, subtitle: slide.subtitle, content: slide.content } })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    if (!GAL.open || GAL.idx !== idx) return;

    var img = card.querySelector('.ds-tpl-card-img');
    var subEl = card.querySelector('.ds-tpl-card-sub');
    card.classList.remove('ds-tpl-loading');

    if (d && d.success) {
      img.innerHTML = d.preview_url ? '<img src="' + tplEsc(d.preview_url) + '">' : '🏆';
      subEl.textContent = d.rendered
        ? '✓ Click to apply — your content, live preview'
        : '✓ Click to apply (no live preview here — content is still correctly filled)';
      card.onclick = function () { window.dsTplGalleryPick(idx, t.id, d.download_url, d.preview_url, d.rendered); };
    } else {
  var thumb = t.thumb || '';
  img.innerHTML = thumb ? '<img src="' + tplEsc(thumb) + '">' : '🏆';
  subEl.textContent = '✓ Click to apply (preview unavailable — content will still be filled)';
  card.onclick = (function(tid, tthumb) { return function () { window.dsTplGalleryPick(idx, tid, 'pending:' + tid, tthumb, false); }; })(t.id, thumb);
}
  })
  .catch(function () {
    if (!GAL.open || GAL.idx !== idx) return;
    var img = card.querySelector('.ds-tpl-card-img');
    var subEl = card.querySelector('.ds-tpl-card-sub');
    card.classList.remove('ds-tpl-loading');
    var thumb = t.thumb || '';
    img.innerHTML = thumb ? '<img src="' + tplEsc(thumb) + '">' : '🏆';
    subEl.textContent = '✓ Click to apply (preview unavailable)';
    card.onclick = function () { window.dsTplGalleryPick(idx, t.id, 'pending:' + t.id, thumb, false); };
  });
}

window.dsTplGalleryPick = function (idx, templateId, downloadUrl, previewUrl, rendered) {
  if (!window.S || !window.S.data || !window.S.data.slides[idx]) return;
  window.S.data.slides[idx]._real_template = { id: templateId, download_url: downloadUrl, preview_url: previewUrl, rendered: !!rendered };
  _pickedCache[idx] = window.S.data.slides[idx]._real_template;
  tplReassertCanvas();
  tplReassertThumb(idx);
  tplRefreshButtonBadge();
  renderSlideSidebar();
  if (window.dspToast) {
    window.dspToast(
      rendered ? 'Design applied to slide ' + (idx + 1) + '!' : 'Design applied to slide ' + (idx + 1) + ' (no live preview here).',
      'success', 4000
    );
  }
};

// ── Remove / restore original design ─────────────────────────────────────────
window.dsTplGalleryRemove = function (idx) {
  if (!window.S || !window.S.data || !window.S.data.slides[idx]) return;

  // Store original thumb URL before deleting
  var origThumbUrl = null;
  var thumbsEl = document.getElementById('dsp-ppt-thumbs');
  if (thumbsEl && thumbsEl.children[idx]) {
    var origImg = thumbsEl.children[idx].querySelector('img');
    if (origImg) origThumbUrl = origImg.src;
  }

  delete window.S.data.slides[idx]._real_template;
  delete _pickedCache[idx];
  tplReassertCanvas();
  tplReassertThumb(idx);
  tplRefreshButtonBadge();
  renderSlideSidebar();

  // Force sidebar thumbnail back to original
  setTimeout(function() {
    var sb = document.getElementById('ds-tpl-slide-sidebar');
    if (!sb) return;
    var cards = sb.querySelectorAll('.ds-tpl-slide-thumb');
    if (!cards[idx]) return;
    var imgSlot = cards[idx].querySelector('.ds-tpl-sb-img');
    if (!imgSlot) return;
    imgSlot.innerHTML = '';

    if (origThumbUrl) {
      // Use original thumbnail URL directly
      imgSlot.innerHTML = '<img src="' + origThumbUrl + '" style="width:100%;height:100%;object-fit:cover;display:block;">';
    } else {
      tplCloneRealThumbInto(imgSlot, idx);
    }
  }, 150);

  if (window.dspToast) {
    window.dspToast('Design removed — original slide restored.', 'info', 3000);
  }
};

window.dsTplDownloadCombined = function () {
  if (!window.S || !window.S.data || !window.S.data.slides) return;
  var slides = window.S.data.slides.map(function (s, si) {
    var clean = {};
    for (var k in s) { if (k !== '_real_template') clean[k] = s[k]; }
    var vid = (window.S.choices || {})['slide_'+si] || 'v1';
    var cnt = (clean.content || []).length;
    if(clean.type==='bullet'&&cnt===5) clean.type='five_point';
    else if(clean.type==='bullet'&&cnt===6) clean.type='six_point';
    else if(clean.type==='bullet'&&cnt===7) clean.type='seven_point';
    return clean;
  });
  var realMap = {};
  window.S.data.slides.forEach(function (s, i) {
    if (s && s._real_template && s._real_template.id) realMap[i] = s._real_template.download_url || ('pending:' + s._real_template.id);
  });

  var status = document.getElementById('ds-tpl-combined-status');
  if (status) status.textContent = 'Building your full deck (' + slides.length + ' slide(s))…';

  // Same full-screen "Downloading PPTX..." overlay the regular download
  // button uses (Snippet 12B's window.dspDlPopup) -- shown the instant the
  // button is clicked, same as that flow does.
  if (window.dspDlPopup) window.dspDlPopup('downloading');

  function closeDlPopup() {
    var old = document.getElementById('dsp-dl-popup');
    if (old && old.parentNode) old.parentNode.removeChild(old);
  }

  var designEl = document.getElementById('dsp-design');
  var layoutEl = document.getElementById('dsp-layout');

  fetch(window.DSP_TPL_COMBINE_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': tplNonce() },
    body: JSON.stringify({
      slides: slides,
      slide_variants: window.S.choices || {},
      template: designEl ? designEl.value : 'navy-pro',
      layout: layoutEl ? layoutEl.value : '16:9',
		font_family: (document.getElementById('dsp-font') ? document.getElementById('dsp-font').value : ''),
      title: (window.G && window.G.pptTitle) || 'Presentation',
      real_template_map: realMap
    })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    if (d && d.success) {
      if (status) status.textContent = '✓ Built ' + d.slide_count + ' of ' + d.requested_count + ' slide(s) (' + d.real_count + ' real, ' + d.coded_count + ' coded). Downloading…';
      var a = document.createElement('a');
      a.href = d.download_url; a.download = ''; a.style.display = 'none';
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
      if (window.dspDlPopup) window.dspDlPopup('done'); else closeDlPopup();
    } else {
      if (status) status.textContent = (d && d.error) || 'Could not combine the files.';
      closeDlPopup();
    }
  })
  .catch(function () {
    if (status) status.textContent = 'Network error — please try again.';
    closeDlPopup();
  });
};

setInterval(function () {
  if (!document.getElementById('dsp-vp-panel') && !document.getElementById('dsp-editor-fullscreen')) return;
  tplInjectButton();
  tplRefreshButtonBadge();
  tplEnsureCanvasObserver();
  tplReassertCanvas();
  tplReassertAllThumbs();
}, 400);

})();
</script>
    <?php
});
