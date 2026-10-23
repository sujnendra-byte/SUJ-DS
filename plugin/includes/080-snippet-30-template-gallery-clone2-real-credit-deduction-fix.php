<?php
/**
 * Code Snippets export — Snippet 30 template gallery [CLONE2] [Real Credit Deduction Fix]
 *
 * Snippet ID : 763
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-080
 * Modified   : 2026-09-18 07:13:44
 * Load order : 080 of 147
 */
/**
 * Snippet: 30 DS PPT Template Gallery v2
 * Full-screen layout: sidebar (content input) + template grid + preview modal
 * Run: Everywhere
 */
if ( ! defined('ABSPATH') ) exit;add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/mkt-templates', [
        'methods'             => 'GET',
        'callback'            => 'ds30_list_templates',
        'permission_callback' => fn() => is_user_logged_in(),
    ]);
    register_rest_route('ds/v1', '/fill-template', [
        'methods'             => 'POST',
        'callback'            => 'ds30_fill_template',
        'permission_callback' => fn() => is_user_logged_in(),
    ]);
});

if ( ! function_exists('ds30_list_templates') ) {
function ds30_list_templates( WP_REST_Request $req ) {
    $search   = sanitize_text_field( $req->get_param('s') ?: '' );
    $per_page = min(48, max(8, (int)( $req->get_param('per_page') ?: 24 )));
    $page     = max(1, (int)( $req->get_param('page') ?: 1 ));

    $ppt_cat = get_term_by('name', 'PowerPoint Templates', 'dlm_download_category');
    if ( ! $ppt_cat || is_wp_error($ppt_cat) ) {
        $ppt_cat = get_term_by('slug', 'powerpoint-templates', 'dlm_download_category');
    }

    $args = [
        'post_type'      => 'dlm_download',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    if ( ! empty($search) ) $args['s'] = $search;
    if ( $ppt_cat && ! is_wp_error($ppt_cat) ) {
        $args['tax_query'] = [[
            'taxonomy' => 'dlm_download_category',
            'field'    => 'term_id',
            'terms'    => $ppt_cat->term_id,
        ]];
    }

    $q     = new WP_Query($args);
    $items = [];

    foreach ( $q->posts as $post ) {
        $thumb_id = get_post_thumbnail_id($post->ID);
        $thumb    = '';
        if ( $thumb_id ) {
            $thumb = function_exists('ds_mkt_get_grid_url')
                ? ds_mkt_get_grid_url($thumb_id)
                : ( wp_get_attachment_image_url($thumb_id, 'large') ?: '' );
        }
        if ( ! $thumb ) {
            $att = get_posts([
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'post_parent'    => $post->ID,
                'posts_per_page' => 1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ]);
            if ( $att ) {
                $thumb = function_exists('ds_mkt_get_grid_url')
                    ? ds_mkt_get_grid_url($att[0]->ID)
                    : ( wp_get_attachment_image_url($att[0]->ID, 'large') ?: '' );
            }
        }
        if ( ! $thumb ) continue;

        $tags      = get_the_terms($post->ID, 'dlm_download_tag') ?: [];
        $tag_slugs = array_map(fn($t) => strtolower($t->slug), is_array($tags) ? $tags : []);
        $tier      = in_array('pro', $tag_slugs, true) ? 'pro' : 'free';

        $items[] = [
            'id'    => $post->ID,
            'title' => $post->post_title,
            'thumb' => $thumb,
            'tier'  => $tier,
        ];
    }

    return rest_ensure_response([
        'items' => $items,
        'total' => $q->found_posts,
        'pages' => $q->max_num_pages,
        'page'  => $page,
    ]);
}
}

if ( ! function_exists('ds30_fill_template') ) {
function ds30_fill_template( WP_REST_Request $req ) {
    // memory_limit ini_set removed — was clamping this request to exactly 1GB even though the site's natural default limit is higher (~1.2GB+), causing self-inflicted OOM fatals in LiteSpeed's object cache.
    @ini_set('max_execution_time', '180');
    @set_time_limit(180);
    @ini_set('pcre.backtrack_limit', '100000000'); @ini_set('pcre.recursion_limit', '100000000'); // FIX 2026-09-16: large slide XML (1-3 MB) silently broke text/icon matching
    // wp_cache_flush() removed — was forcing a full alloptions/object-cache rebuild that exhausted PHP memory in LiteSpeed's object cache (see debug).
global $wp_object_cache;
$ds30_orig_cache = $wp_object_cache;
if ( class_exists('WP_Object_Cache') ) { $wp_object_cache = new WP_Object_Cache(); }


    $uid         = get_current_user_id();
    $template_id = (int) $req->get_param('template_id');
    $user_text   = wp_strip_all_tags( (string) $req->get_param('text') );

    if ( ! $template_id || trim($user_text) === '' )
        return rest_ensure_response(['error' => 'Missing template or text.']);
    if ( strlen($user_text) > 15000 )
        return rest_ensure_response(['error' => 'Text too long.']);

    $credits = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
    $__ppt_opt_tpl2 = get_option('ds_ppt_credit_settings');
    $cost    = ( is_array($__ppt_opt_tpl2) && isset($__ppt_opt_tpl2['template_cost']) && $__ppt_opt_tpl2['template_cost'] !== '' ) ? (int)$__ppt_opt_tpl2['template_cost'] : 5;

    // Credits are ALWAYS verified server-side before doing the (expensive)
    // template fill. $cost/$credits were previously computed here but never
    // actually enforced or deducted anywhere in this function (dead code) --
    // that was the bug: template inserts cost 0 credits regardless of setting.
    if ( $credits < $cost ) {
        return rest_ensure_response([
            'error'   => 'insufficient_credits',
            'message' => 'You need at least '.$cost.' credit'.( $cost === 1 ? '' : 's' ).' to insert a template. You currently have '.$credits.'.',
        ]);
    }

    $file_path = ds30_get_pptx_path($template_id);
    if ( ! $file_path || ! file_exists($file_path) )
        return rest_ensure_response(['error' => 'Template file not found.']);

    $zip = new ZipArchive();
    if ( $zip->open($file_path) !== true )
        return rest_ensure_response(['error' => 'Template is not a valid PPTX.']);

    $slide_map = [];
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $name = $zip->getNameIndex($i);
        if ( preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name) ) {
            $xml = $zip->getFromIndex($i);
            // Fix namespace typos — PowerPoint rejects any variation
            $xml = preg_replace(
                '#http://schemas\.openxmlformats\.org/office[^/]*/2006/relationships#',
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                $xml
            );
            $slide_map[$name] = $xml;
        }
    }
    $zip->close();

    if ( empty($slide_map) )
        return rest_ensure_response(['error' => 'No slides found.']);

    uksort($slide_map, function($a, $b) {
        preg_match('/(\d+)/', basename($a), $ma);
        preg_match('/(\d+)/', basename($b), $mb);
        return ($ma[1] ?? 0) <=> ($mb[1] ?? 0);
    });

    $all_boxes = [];
    foreach ( $slide_map as $name => $xml ) {
        foreach ( ds30_extract_text_boxes($xml) as $box ) {
            $all_boxes[] = ['file' => $name, 'slot_name' => $box['text'], 'is_placeholder' => $box['is_placeholder']];
        }
    }

    // FIX 2026-09-16: never silently download an unfilled template
    if ( empty($all_boxes) ) {
        error_log('DS30: no editable text found in template ' . $template_id);
        return rest_ensure_response(['error' => 'This template could not be read for auto-fill. Please choose another template.']);
    }

    $keys  = function_exists('ds_get_api_keys') ? ds_get_api_keys() : [];
    $api_k = $keys['groq']
          ?? $keys['groq_api_key']
          ?? get_option('ds_groq_api_key', '')
          ?? get_option('groq_api_key', '');
    if ( empty($api_k) ) return rest_ensure_response(['error' => 'Groq API key not found.']);
    $model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');

        $sys = "You are a presentation slot-filling assistant.\n"
         . "You receive template SLOT NAMES and user content. Map user content to slots.\n\n"
         . "SLOT IDENTIFICATION RULES:\n"
         . "- A slot named exactly 'Title' or 'Title Header' or 'Main Title' = THE MAIN TITLE SLOT\n"
         . "- A slot named exactly 'Sub Header' or 'Subtitle' or 'Sub Title' = THE SUBTITLE SLOT\n"
         . "- These are single-word or short placeholder labels IN the template — replace them with user content\n"
         . "- 'Point N Heading' or 'Step N Heading' = heading for item N\n"
         . "- 'Point N Explanation' or 'Step N Description' = body text for item N\n\n"
         . "CRITICAL MAPPING EXAMPLE:\n"
         . "If user writes 'Title: Core Business Functions' and slot_name is 'Title' → replacement = 'Core Business Functions'\n"
         . "If user writes 'Sub Header: Eight essential functions' and slot_name is 'Sub Header' → replacement = 'Eight essential functions'\n"
         . "NEVER put the title text into the Sub Header slot or vice versa.\n"
         . "NEVER keep the slot_name value as the replacement.\n"
         . "Strip label prefixes: 'Title: X' → 'X', 'Point 1: X' → 'X'\n\n"
         . "LEAVE UNCHANGED: numbers like '01','02', copyright, dates, URLs, page numbers.\n"
     . "LEAVE UNCHANGED: any slot_name matching 'Icon' followed by a number (e.g. 'Icon 1', 'Icon2', 'Icon 3') — these are icon placeholders, NOT text content. Always set replacement = slot_name for these (i.e. no change).\n"
     . "If the template has MORE slots than the user provided points, fill remaining slots with relevant supporting content that fits the topic naturally — do NOT leave them blank. This rule does NOT apply to 'Icon N' slots.\n"
         . "Example: if user gives 6 points but template has 8 slots, create 2 additional relevant points based on the same topic.\n"
         . "NEVER leave a heading or explanation slot empty.\n\n"
         . "OUTPUT: JSON only. {\"slots\":[{\"slot_name\":\"exact original text\",\"file\":\"ppt/slides/slideN.xml\",\"replacement\":\"user content here\"}]}";

    $slots_json = wp_json_encode(
        array_map(fn($b) => ['slot_name' => $b['slot_name'], 'file' => $b['file']], $all_boxes),
        JSON_UNESCAPED_UNICODE
    );

    $api_resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', [
        'timeout' => 60,
        'headers' => ['Authorization' => 'Bearer ' . $api_k, 'Content-Type' => 'application/json'],
        'body'    => wp_json_encode([
            'model'           => $model,
            'messages'        => [
                ['role' => 'system', 'content' => $sys],
                ['role' => 'user',   'content' => "SLOTS:\n" . $slots_json . "\n\nUSER CONTENT:\n" . $user_text],
            ],
            'temperature'     => 0.1,
            'max_tokens'      => 4000,
            'response_format' => ['type' => 'json_object'],
        ]),
    ]);

    if ( is_wp_error($api_resp) ) return rest_ensure_response(['error' => 'AI error: ' . $api_resp->get_error_message()]);

    $ai_data    = json_decode(wp_remote_retrieve_body($api_resp), true);
    $ai_content = $ai_data['choices'][0]['message']['content'] ?? '{}';
    $ai_content = preg_replace('/^```(?:json)?\s*/i', '', trim($ai_content));
    $ai_content = preg_replace('/\s*```$/', '', $ai_content);
    $ai_json    = json_decode($ai_content, true);
    if ( isset($ai_json['slots']) ) $ai_json = $ai_json['slots'];
    if ( ! is_array($ai_json) ) return rest_ensure_response(['error' => 'AI returned unparsable output.']);

    foreach ( $ai_json as $slot ) {
        $file = sanitize_text_field($slot['file'] ?? '');
        $orig = (string)($slot['slot_name'] ?? '');
        $repl = (string)($slot['replacement'] ?? '');
        if ( ! $file || ! isset($slide_map[$file]) ) continue;
        if ( $orig === '' || $orig === $repl ) continue;
        if ( strpos($repl, '[NO CONTENT') !== false ) continue;
        if ( strtolower(trim($repl)) === strtolower(trim($orig)) ) continue;
		if ( preg_match('/^Icon\s*\d+$/i', trim($orig)) ) continue;
        $slide_map[$file] = ds30_replace_text_in_xml($slide_map[$file], $orig, $repl);
    }

    // Rebuild zip preserving exact file order and compression
    $tmp_out = tempnam(sys_get_temp_dir(), 'ds30out_') . '.pptx';
    $zip_src = new ZipArchive();
    $zip_src->open($file_path);
    $zip_out = new ZipArchive();
    $zip_out->open($tmp_out, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    for ( $i = 0; $i < $zip_src->numFiles; $i++ ) {
        $name  = $zip_src->getNameIndex($i);
        $stat  = $zip_src->statIndex($i);
        $meth  = isset($stat['comp_method']) ? (int)$stat['comp_method'] : 8;
        $bytes = isset($slide_map[$name]) ? $slide_map[$name] : $zip_src->getFromIndex($i);
        $zip_out->addFromString($name, $bytes);
        if ( $meth === 0 && ! isset($slide_map[$name]) ) {
            $zip_out->setCompressionIndex($zip_out->numFiles - 1, 0);
        }
    }
    $zip_out->close();
    $zip_src->close();

      $template_post = get_post($template_id);
    $filename      = sanitize_file_name(($template_post->post_title ?? 'presentation') . '.pptx');
    $filled_bytes  = file_get_contents($tmp_out);
    @unlink($tmp_out);
    if ( ! $filled_bytes ) return rest_ensure_response(['error' => 'Could not read output file.']);

	// Allow Snippet 32 to inject icons
    $filtered = apply_filters('ds30_filled_pptx_bytes', $filled_bytes, $ai_json, $api_k);
    $filled_bytes = ( is_string($filtered) && strlen($filtered) > 100 ) ? $filtered : $filled_bytes;
    $filled_b64 = base64_encode($filled_bytes);
    unset($filled_bytes);
    $preview_img_b64 = '';

    // Also return extracted slots for preview overlay
    $preview_slots = [];
    foreach ( $ai_json as $slot ) {
        $orig = (string)($slot['slot_name'] ?? '');
        $repl = (string)($slot['replacement'] ?? '');
        if ( $orig && $repl && $orig !== $repl && strpos($repl, '[NO CONTENT') === false ) {
            $preview_slots[] = ['slot' => $orig, 'value' => $repl];
        }
    }

    if ( isset($ds30_orig_cache) ) { global $wp_object_cache; $wp_object_cache = $ds30_orig_cache; }

    // Deduct now that the fill has actually succeeded -- never charge for a
    // failed/errored attempt (every earlier error return above skips this).
    if ( function_exists('ds_deduct_credits') ) {
        // Refused when another request spent the credits in the meantime.
        if ( ! ds_deduct_credits($uid, $cost) ) {
            return rest_ensure_response(['error' => "You need {$cost} credits for this."]);
        }
    } else {
        update_user_meta($uid, 'ds_ai_credits', max(0, $credits - $cost));
    }

return rest_ensure_response([
        'success'         => true,
        'filename'        => $filename,
		'pptx_b64'        => $filled_b64,
        'preview_img_b64' => $preview_img_b64,
        'preview_slots'   => $preview_slots,
        'credits_balance' => (int) get_user_meta($uid, 'ds_ai_credits', true),
    ]);
}
}

// ── Aspose PPTX → PNG preview ─────────────────────────────────────────────
if ( ! function_exists('ds30_aspose_preview') ) {
function ds30_aspose_preview( $pptx_bytes, $client_id, $client_secret ) {
    // Step 1: Get token
    $ch = curl_init('https://api.aspose.cloud/connect/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type'    => 'client_credentials',
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $token_resp = curl_exec($ch);
    curl_close($ch);

    if ( ! $token_resp ) return '';
    $token_data = json_decode($token_resp, true);
    $token      = $token_data['access_token'] ?? '';
    if ( ! $token ) return '';

    // Step 2: POST PPTX → get ZIP of PNGs back
    $ch = curl_init('https://api.aspose.cloud/v3.0/slides/convert/Png');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/octet-stream',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pptx_bytes);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $zip_bytes = curl_exec($ch);
    $zip_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ( $zip_code !== 200 || ! $zip_bytes || strlen($zip_bytes) < 100 ) return '';

    // Step 3: Extract first PNG from the ZIP in memory
    $tmp_zip = tempnam(sys_get_temp_dir(), 'asp_zip_') . '.zip';
    file_put_contents($tmp_zip, $zip_bytes);

    $zip = new ZipArchive();
    if ( $zip->open($tmp_zip) !== true ) {
        @unlink($tmp_zip);
        return '';
    }

    $png_bytes = '';
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $name = $zip->getNameIndex($i);
        if ( preg_match('/\.png$/i', $name) ) {
            $png_bytes = $zip->getFromIndex($i);
            break;
        }
    }
    $zip->close();
    @unlink($tmp_zip);

    if ( ! $png_bytes || strlen($png_bytes) < 1000 ) return '';

    return base64_encode($png_bytes);
}
}

if ( ! function_exists('ds30_get_pptx_path') ) {
function ds30_get_pptx_path( $post_id ) {
    // FIX 2026-09-16: template files use DLM's "_files" JSON meta and live off-site (R2).
    // 1) Reuse Snippet 21's resolver when available (handles JSON meta + remote download).
    if ( function_exists('ds_tpl_fetch_template_src_path') ) {
        list( $p, $tmp ) = ds_tpl_fetch_template_src_path( $post_id );
        if ( $p && file_exists($p) ) {
            if ( $tmp ) { register_shutdown_function( function() use ( $tmp ) { @unlink( $tmp ); } ); }
            return $p;
        }
    }

    // 2) Fallback: read the file URL ourselves.
    $url = '';
    $versions = get_posts([
        'post_type'      => 'dlm_download_version',
        'post_parent'    => $post_id,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ]);
    if ( ! empty($versions) ) {
        foreach ( ['_files', '_download_files'] as $key ) {
            $raw = get_post_meta($versions[0], $key, true);
            if ( is_array($raw) ) { $raw = reset($raw); }
            $raw = trim((string) $raw);
            if ( $raw === '' ) continue;
            if ( strpos($raw, '[') === 0 ) {
                $dec = json_decode($raw, true);
                if ( is_array($dec) && ! empty($dec[0]) ) { $url = trim($dec[0]); break; }
            } else {
                $lines = array_filter(array_map('trim', explode("\n", $raw)));
                if ( $lines ) { $url = reset($lines); break; }
            }
        }
    }
    if ( empty($url) ) return false;

    // Local file?
    $upload   = wp_upload_dir();
    $base_url = preg_replace('#^https?://#', '', rtrim($upload['baseurl'], '/'));
    $file_url = preg_replace('#^https?://#', '', $url);
    if ( strpos($file_url, $base_url) === 0 ) {
        $path = rtrim($upload['basedir'], '/') . '/' . ltrim(substr($file_url, strlen($base_url)), '/');
        if ( file_exists($path) ) return $path;
    }
    $site_url = preg_replace('#^https?://#', '', rtrim(site_url(), '/'));
    if ( strpos($file_url, $site_url) === 0 ) {
        $path = rtrim(ABSPATH, '/') . '/' . ltrim(substr($file_url, strlen($site_url)), '/');
        if ( file_exists($path) ) return $path;
    }
    if ( file_exists($url) ) return $url;

    // Remote (R2) file: download to a temp file, removed at end of request.
    if ( strpos($url, 'http') === 0 ) {
        if ( ! function_exists('download_url') ) { require_once ABSPATH . 'wp-admin/includes/file.php'; }
        $tmp = download_url( $url, 60 );
        if ( ! is_wp_error($tmp) && file_exists($tmp) ) {
            register_shutdown_function( function() use ( $tmp ) { @unlink( $tmp ); } );
            return $tmp;
        }
    }
    return false;
}
}

if ( ! function_exists('ds30_extract_text_boxes') ) {
function ds30_extract_text_boxes( $xml ) {
    $boxes = [];
    if ( ! preg_match_all('/<p:sp\b[^>]*>(.*?)<\/p:sp>/s', $xml, $sp_matches) ) return $boxes;
    $decorative = '/^\s*(slide\s*\d+|\d{4}|©.*|www\.[^\s]+|https?:\/\/[^\s]+|[\w.+-]+@[\w-]+\.[a-z]{2,}|\d+\s*\/\s*\d+|^0\d$)\s*$/i';
    foreach ( $sp_matches[1] as $idx => $sp_content ) {
        preg_match_all('/<a:t(?:\s[^>]*)?>([^<]*)<\/a:t>/s', $sp_content, $t_matches);
        $text = trim(preg_replace('/[ \t]{2,}/', ' ', implode('', array_map('html_entity_decode', $t_matches[1]))));
        if ( $text === '' || preg_match($decorative, $text) ) continue;
        $boxes[] = ['id' => $idx, 'text' => $text, 'is_placeholder' => (bool)preg_match('/\{\{[^}]+\}\}|\[[A-Z ]{2,}\]/', $text)];
    }
    return $boxes;
}
}

if ( ! function_exists('ds30_replace_text_in_xml') ) {
function ds30_replace_text_in_xml( $xml, $original, $replacement ) {
    $enc_orig = htmlspecialchars($original, ENT_XML1|ENT_QUOTES, 'UTF-8');
    $enc_repl = htmlspecialchars($replacement, ENT_XML1|ENT_QUOTES, 'UTF-8');
    // Escape $ and \ so user text like "Revenue: $2M" isn't treated as a
    // regex backreference in the preg_replace replacement string (that
    // corrupts the slide XML and PowerPoint refuses to open the file).
    $safe_repl = strtr($enc_repl, ['\\' => '\\\\', '$' => '\\$']);
    // Only replace inside <a:t> text nodes — never in XML attributes
    if ( strpos($xml, $enc_orig) !== false ) {
        return preg_replace(
            '#(<a:t(?:\s[^>]*)?>)' . preg_quote($enc_orig, '#') . '(</a:t>)#',
            '$1' . $safe_repl . '$2', $xml
        );
    }
    if ( strpos($xml, $original) !== false ) {
        return preg_replace(
            '#(<a:t(?:\s[^>]*)?>)' . preg_quote($original, '#') . '(</a:t>)#',
            '$1' . $safe_repl . '$2', $xml
        );
    }
    $result = preg_replace_callback('/<p:sp\b[^>]*>.*?<\/p:sp>/s', function($m) use($original, $enc_repl, $safe_repl) {
        $shape = $m[0];
        preg_match_all('/<a:t(?:\s[^>]*)?>([^<]*)<\/a:t>/s', $shape, $tm, PREG_OFFSET_CAPTURE);
        if ( empty($tm[1]) ) return $shape;
        $joined = trim(preg_replace('/[ \t]{2,}/', ' ', html_entity_decode(implode('', array_column($tm[1], 0)))));
        if ( strcasecmp(trim($joined), trim($original)) !== 0 ) return $shape;
        preg_match('/<a:rPr\b[^>]*(?:\/>|>.*?<\/a:rPr>)/s', $shape, $rpr_m);
        $rpr   = $rpr_m[0] ?? '';
        $shape = preg_replace('/<a:r\b[^>]*>.*?<\/a:r>/s', '', $shape, -1);
        $shape = preg_replace('/(<\/a:p>)/', '<a:r>' . strtr($rpr, ['\\' => '\\\\', '$' => '\\$']) . '<a:t>' . $safe_repl . '</a:t></a:r>$1', $shape, 1);
        return $shape;
    }, $xml);
    return $result ?? $xml;
}
}

// ═══════════════════════════════════════════════════════
// FRONT-END: Full-screen gallery layout
// ═══════════════════════════════════════════════════════
add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    $nonce    = wp_create_nonce('wp_rest');
    $api_base = esc_js(rest_url('ds/v1'));
?>
<style>
/* ── Full screen overlay ── */
#ds30-screen{position:fixed;inset:0;z-index:100010;background:#0a0a0f;display:none;flex-direction:row;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;overflow:hidden;}
#ds30-screen.open{display:flex;}

/* ── Sidebar ── */
#ds30-sidebar{width:280px;min-width:280px;background:#111118;border-right:1px solid #1e1e2e;display:flex;flex-direction:column;overflow:hidden;}
.ds30-sb-header{padding:16px 18px 12px;border-bottom:1px solid #1e1e2e;flex-shrink:0;}
.ds30-sb-header h3{margin:0 0 2px;font-size:15px;font-weight:700;color:#f9fafb;}
.ds30-sb-header p{margin:0;font-size:11px;color:#6b7280;}
.ds30-sb-body{flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:12px;}
.ds30-sb-label{font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;}
#ds30-content-input{width:100%;box-sizing:border-box;background:#1a1a28;border:1px solid #2a2a3e;border-radius:10px;color:#f9fafb;font-size:12.5px;padding:10px 12px;resize:none;font-family:inherit;line-height:1.6;min-height:280px;}
#ds30-content-input:focus{border-color:#2563eb;outline:none;}
#ds30-content-input::placeholder{color:#3d3d5c;}
.ds30-selected-tpl{background:#1a1a28;border:1px solid #2a2a3e;border-radius:10px;padding:10px 12px;display:none;}
.ds30-selected-tpl img{width:100%;border-radius:6px;aspect-ratio:16/9;object-fit:cover;margin-bottom:8px;}
.ds30-selected-tpl-name{font-size:12px;font-weight:600;color:#f9fafb;margin-bottom:2px;}
.ds30-selected-tpl-change{font-size:11px;color:#6b7280;cursor:pointer;text-decoration:underline;background:none;border:none;padding:0;}
.ds30-sb-footer{padding:14px 16px;border-top:1px solid #1e1e2e;flex-shrink:0;display:flex;flex-direction:column;gap:8px;}
.ds30-credits-bar{font-size:11px;color:#6b7280;text-align:center;}
#ds30-generate-btn{width:100%;padding:12px;background:#2563eb;color:#fff;font-size:13px;font-weight:700;border:none;border-radius:10px;cursor:pointer;transition:background .15s;}
#ds30-generate-btn:hover{background:#1d4ed8;}
#ds30-generate-btn:disabled{background:#1e3a5f;color:#4b6ea8;cursor:not-allowed;}
.ds30-gen-hint{font-size:11px;color:#4b5563;text-align:center;}

/* ── Main grid area ── */
#ds30-main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
#ds30-topbar{display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #1e1e2e;flex-shrink:0;}
#ds30-topbar h2{margin:0;font-size:15px;font-weight:700;color:#f9fafb;flex:1;}
#ds30-search-v2{background:#1a1a28;border:1px solid #2a2a3e;border-radius:8px;color:#f9fafb;padding:7px 12px;font-size:13px;outline:none;width:220px;}
#ds30-search-v2:focus{border-color:#2563eb;}
#ds30-close-v2{background:transparent;border:1px solid #2a2a3e;color:#9ca3af;border-radius:8px;padding:6px 14px;font-size:13px;cursor:pointer;}
#ds30-close-v2:hover{border-color:#ef4444;color:#ef4444;}
#ds30-grid-wrap{flex:1;overflow-y:auto;padding:16px 18px;}
.ds30-grid-v2{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:12px;}
.ds30-card-v2{border-radius:10px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:transform .12s,border-color .12s;background:#111118;}
.ds30-card-v2:hover{border-color:#3b82f6;transform:translateY(-2px);}
.ds30-card-v2.selected{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.25);}
.ds30-card-v2 img{width:100%;aspect-ratio:16/9;object-fit:cover;display:block;}
.ds30-card-v2-meta{padding:8px 10px;background:#17171f;}
.ds30-card-v2-title{color:#f9fafb;font-size:11.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ds30-card-v2-row{display:flex;align-items:center;justify-content:space-between;margin-top:3px;}
.ds30-badge-v2{font-size:9px;font-weight:700;padding:2px 6px;border-radius:3px;}
.ds30-badge-v2.free{background:#14532d;color:#86efac;}
.ds30-badge-v2.pro{background:#312e81;color:#c4b5fd;}
.ds30-checkmark{color:#22c55e;font-size:12px;display:none;}
.ds30-card-v2.selected .ds30-checkmark{display:inline;}
#ds30-load-more-v2{display:block;margin:16px auto 0;background:#1a1a28;border:1px solid #2a2a3e;color:#9ca3af;border-radius:8px;padding:8px 24px;font-size:13px;cursor:pointer;}
#ds30-grid-msg-v2{color:#4b5563;text-align:center;padding:48px 20px;font-size:14px;}

/* ── Status overlay ── */
#ds30-status-overlay{position:absolute;inset:0;background:rgba(10,10,15,.85);z-index:10;display:none;align-items:center;justify-content:center;flex-direction:column;gap:16px;}
#ds30-status-overlay.show{display:flex;}
.ds30-big-spin{width:48px;height:48px;border:3px solid #1e1e2e;border-top-color:#2563eb;border-radius:50%;animation:ds30spin .8s linear infinite;}
.ds30-status-text{font-size:15px;color:#9ca3af;font-weight:500;}
@keyframes ds30spin{to{transform:rotate(360deg);}}

/* ── Preview modal ── */
#ds30-preview-modal{position:fixed;inset:0;z-index:100020;background:rgba(0,0,0,.85);display:none;align-items:center;justify-content:center;}
#ds30-preview-modal.open{display:flex;}
.ds30-preview-box{background:#fff;border-radius:16px;overflow:hidden;max-width:820px;width:92%;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 32px 80px rgba(0,0,0,.5);}
.ds30-preview-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;flex-shrink:0;}
.ds30-preview-header h3{margin:0;font-size:15px;font-weight:700;color:#111;}
.ds30-preview-close{background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;line-height:1;}
.ds30-preview-body{flex:1;overflow-y:auto;position:relative;}
.ds30-preview-img-wrap{position:relative;width:100%;}
.ds30-preview-img-wrap img{width:100%;display:block;}
/* Slot overlay labels */
.ds30-slot-overlay{position:absolute;inset:0;pointer-events:none;}
.ds30-preview-slots{padding:16px 18px;border-top:1px solid #e5e7eb;background:#f9fafb;}
.ds30-preview-slots-title{font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;}
.ds30-slots-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.ds30-slot-item{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;}
.ds30-slot-name{font-size:10px;color:#9ca3af;font-weight:500;margin-bottom:2px;}
.ds30-slot-value{font-size:12.5px;color:#111;font-weight:500;line-height:1.4;}
.ds30-preview-footer{padding:14px 18px;border-top:1px solid #e5e7eb;display:flex;gap:10px;align-items:center;flex-shrink:0;}
#ds30-download-btn{flex:1;padding:12px;background:#2563eb;color:#fff;font-size:13px;font-weight:700;border:none;border-radius:10px;cursor:pointer;}
#ds30-download-btn:hover{background:#1d4ed8;}
#ds30-another-btn{padding:12px 18px;background:transparent;border:1px solid #d1d5db;color:#374151;font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;}
#ds30-another-btn:hover{border-color:#2563eb;color:#2563eb;}

/* ── Success popup ── */
#ds30-success-popup{position:fixed;inset:0;z-index:100030;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;}
#ds30-success-popup.open{display:flex;}
.ds30-success-box{background:#fff;border-radius:20px;padding:40px 36px;max-width:360px;width:90%;text-align:center;}
.ds30-success-emoji{font-size:52px;margin-bottom:16px;}
.ds30-success-title{font-size:22px;font-weight:800;color:#111;margin-bottom:8px;}
.ds30-success-sub{font-size:14px;color:#666;margin-bottom:28px;}
.ds30-success-ok{width:100%;padding:14px;background:#f59e0b;color:#fff;font-size:14px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;border:none;border-radius:12px;cursor:pointer;margin-bottom:12px;}
.ds30-success-another{background:none;border:none;color:#888;font-size:13px;cursor:pointer;text-decoration:underline;}

/* ── Mode card ── */
#dsp-tpl-trigger{display:none!important;}
#dsp-tpl-panel{display:none!important;}
</style>

<!-- Full screen gallery -->
<div id="ds30-screen" role="dialog" aria-modal="true">

  <!-- Sidebar -->
  <div id="ds30-sidebar">
    <div class="ds30-sb-header">
      <h3>Your Content</h3>
      <p>Paste your text — AI maps it to the template</p>
    </div>
    <div class="ds30-sb-body">
      <div>
        <div class="ds30-sb-label">Content</div>
        <textarea id="ds30-content-input" placeholder="Title: My Presentation&#10;Sub Header: A brief description&#10;Point 1: First topic&#10;Description of first topic.&#10;Point 2: Second topic&#10;Description of second topic."></textarea>
      </div>
      <div id="ds30-selected-tpl-panel" class="ds30-selected-tpl">
        <div class="ds30-sb-label">Selected Template</div>
        <img id="ds30-selected-tpl-img" src="" alt="">
        <div class="ds30-selected-tpl-name" id="ds30-selected-tpl-name"></div>
        <button class="ds30-selected-tpl-change" onclick="ds30ClearSel()">&#8592; Change template</button>
      </div>
    </div>
    <div class="ds30-sb-footer">
      <div class="ds30-gen-hint" id="ds30-gen-hint">&#8592; Pick a template first</div>
      <button id="ds30-generate-btn" onclick="ds30Generate()" disabled>Generate &amp; Preview</button>
      <div class="ds30-credits-bar" id="ds30-credits-bar"></div>
    </div>
  </div>

  <!-- Main -->
  <div id="ds30-main" style="position:relative;">
    <div id="ds30-topbar">
      <h2>Select Template</h2>
      <input type="text" id="ds30-search-v2" placeholder="Search templates…" autocomplete="off">
      <button id="ds30-close-v2" onclick="ds30ScreenClose()">Close ✕</button>
    </div>
    <div id="ds30-grid-wrap">
      <div class="ds30-grid-v2" id="ds30-grid-v2"></div>
      <div id="ds30-grid-msg-v2" style="display:none;"></div>
      <button id="ds30-load-more-v2" style="display:none;" onclick="ds30LoadMore()">Load more</button>
    </div>
    <!-- Status overlay -->
    <div id="ds30-status-overlay">
      <div class="ds30-big-spin"></div>
      <div class="ds30-status-text" id="ds30-status-text">Generating your presentation…</div>
    </div>
  </div>
</div>

<!-- Preview modal -->
<div id="ds30-preview-modal">
  <div class="ds30-preview-box">
    <div class="ds30-preview-header">
      <h3 id="ds30-preview-title">Preview</h3>
      <button class="ds30-preview-close" onclick="ds30PreviewClose()">&#10005;</button>
    </div>
    <div class="ds30-preview-body">
      <div class="ds30-preview-img-wrap">
        <img id="ds30-preview-img" src="" alt="Template preview">
      </div>
      <div class="ds30-preview-slots" id="ds30-preview-slots"></div>
    </div>
    <div class="ds30-preview-footer">
      <button id="ds30-another-btn" onclick="ds30PreviewClose()">&#8592; Pick another</button>
      <button id="ds30-download-btn" onclick="ds30Download()">&#11015; Download PPTX</button>
    </div>
  </div>
</div>

<!-- Success popup -->
<div id="ds30-success-popup">
  <div class="ds30-success-box">
    <div class="ds30-success-emoji">&#127881;</div>
    <div class="ds30-success-title">Download Complete!</div>
    <div class="ds30-success-sub">Check your downloads folder.</div>
    <button class="ds30-success-ok" onclick="ds30SuccessClose(false)">AWESOME!</button><br>
    <button class="ds30-success-another" onclick="ds30SuccessClose(true)">Generate another &#8594;</button>
  </div>
</div>

<script>
(function(){
'use strict';
var API='<?php echo $api_base; ?>';
var NONCE='<?php echo $nonce; ?>';
var page=1,maxPages=1,loading=false,searchTimer=null;
var selId=null,selThumb=null,selTitle=null;
var lastPptxB64=null,lastFilename=null;

/* ── Open/close ── */
var DS30_TPL_PATH  = '/ai-presentation/browse-templates/';
var DS30_BASE_PATH = '/ai-presentation/';

function ds30ScreenOpen(skipUrl){
  document.getElementById('ds30-screen').classList.add('open');
  if(!document.getElementById('ds30-grid-v2').children.length) ds30Load(true);
  ds30UpdateCredits();
  if(skipUrl!==true && location.pathname!==DS30_TPL_PATH){
    try{ history.pushState({ds30:'open'}, '', DS30_TPL_PATH); }catch(e){}
  }
}
function ds30ScreenClose(skipUrl){
  document.getElementById('ds30-screen').classList.remove('open');
  if(skipUrl!==true && location.pathname.indexOf('browse-templates')!==-1){
    try{ history.pushState({ds30:'closed'}, '', DS30_BASE_PATH); }catch(e){}
  }
}

/* restore picker on refresh / direct link / back-forward */
function ds30SyncFromUrl(){
  var scr=document.getElementById('ds30-screen'); if(!scr) return;
  var wantOpen = location.pathname.indexOf('browse-templates')!==-1;
  var isOpen   = scr.classList.contains('open');
  if(wantOpen && !isOpen) ds30ScreenOpen(true);
  else if(!wantOpen && isOpen) ds30ScreenClose(true);
}
if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', ds30SyncFromUrl); }
else { ds30SyncFromUrl(); }
window.addEventListener('popstate', ds30SyncFromUrl);

/* ── Load templates ── */
function ds30Load(reset){
  if(loading)return;
  if(reset){page=1;maxPages=1;}
  loading=true;
  var grid=document.getElementById('ds30-grid-v2');
  var msg=document.getElementById('ds30-grid-msg-v2');
  var more=document.getElementById('ds30-load-more-v2');
  if(reset){grid.innerHTML='';msg.style.display='none';}
  var s=(document.getElementById('ds30-search-v2')||{}).value||'';
  var uc=(document.getElementById('ds30-content-input')||{}).value||'';
  fetch(API+'/mkt-templates?page='+page+'&per_page=24'+(s?'&s='+encodeURIComponent(s):'')+(uc?'&uc='+encodeURIComponent(uc.slice(0,4000)):''),{headers:{'X-WP-Nonce':NONCE}})
  .then(function(r){return r.json();})
  .then(function(d){
    loading=false;
    if(!d||!d.items||!d.items.length){
      if(reset){msg.textContent='No templates found.';msg.style.display='block';}
      more.style.display='none';return;
    }
    maxPages=d.pages||1;
    d.items.forEach(function(t){grid.appendChild(ds30MakeCard(t));});
    more.style.display=(page<maxPages)?'block':'none';
  })
  .catch(function(){loading=false;if(reset){msg.textContent='Could not load templates.';msg.style.display='block';}});
}
function ds30LoadMore(){page++;ds30Load(false);}

/* ── Card ── */
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function ds30MakeCard(t){
  var el=document.createElement('div');
  el.className='ds30-card-v2';
  el.innerHTML='<img src="'+esc(t.thumb)+'" alt="'+esc(t.title)+'" loading="lazy">'
    +'<div class="ds30-card-v2-meta"><div class="ds30-card-v2-title">'+esc(t.title)+'</div>'
    +'<div class="ds30-card-v2-row"><span class="ds30-badge-v2 '+esc(t.tier)+'">'+esc(t.tier.toUpperCase())+'</span>'
    +'<span class="ds30-checkmark">&#10003;</span></div></div>';
  el.onclick=function(){
    document.querySelectorAll('.ds30-card-v2').forEach(function(c){c.classList.remove('selected');});
    el.classList.add('selected');
    selId=t.id; selThumb=t.thumb; selTitle=t.title;
    // Show in sidebar
    var panel=document.getElementById('ds30-selected-tpl-panel');
    document.getElementById('ds30-selected-tpl-img').src=t.thumb;
    document.getElementById('ds30-selected-tpl-name').textContent=t.title;
    panel.style.display='block';
    // Enable generate
    document.getElementById('ds30-generate-btn').disabled=false;
    document.getElementById('ds30-gen-hint').style.display='none';
  };
  return el;
}
function ds30ClearSel(){
  selId=null;selThumb=null;selTitle=null;
  document.querySelectorAll('.ds30-card-v2').forEach(function(c){c.classList.remove('selected');});
  document.getElementById('ds30-selected-tpl-panel').style.display='none';
  document.getElementById('ds30-generate-btn').disabled=true;
  document.getElementById('ds30-gen-hint').style.display='';
}

/* ── Generate ── */
function ds30Generate(){
  var text=(document.getElementById('ds30-content-input')||{}).value||'';
  if(!selId){alert('Please select a template first.');return;}
  if(!text.trim()){alert('Please add your content first.');return;}
  var status=document.getElementById('ds30-status-overlay');
  document.getElementById('ds30-status-text').textContent='Filling template with your content…';
  status.classList.add('show');
  fetch(API+'/fill-template',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({template_id:selId,text:text})
  })
  .then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
  .then(function(d){
    status.classList.remove('show');
    if(!d||d.error){alert(d&&d.error?d.error:'Something went wrong.');return;}
    lastPptxB64 = d.pptx_b64;
    window.lastPptxB64 = d.pptx_b64;
    window.lastDs31Slots = d.preview_slots || [];
    window.lastPreviewImg = '';
    window.lastFilename = d.filename || 'presentation.pptx';
    lastFilename = d.filename || 'presentation.pptx';
    if(d.credits_balance!==undefined){
      if(window.G)window.G.credits=d.credits_balance;
      var ce=document.getElementById('ds-cr-display');
      if(ce)ce.textContent=d.credits_balance.toLocaleString();
      ds30UpdateCredits();
    }
    ds30ShowPreview(d.preview_slots||[]);
  })
  .catch(function(e){
    status.classList.remove('show');
    alert('Error: '+e.message+'. Please try again.');
  });
}

/* ── Preview modal ── */
function ds30ShowPreview(slots){
  document.getElementById('ds30-preview-title').textContent=selTitle||'Preview';

  // Show selected template thumbnail
  document.getElementById('ds30-preview-img').src=selThumb||'';

  // Hide slots panel — not needed
  document.getElementById('ds30-preview-slots').style.display='none';

  document.getElementById('ds30-preview-modal').classList.add('open');
}
function ds30PreviewClose(){
  document.getElementById('ds30-preview-modal').classList.remove('open');
  lastPptxB64=null;lastFilename=null;
}

/* ── Download ── */
function ds30Download(){
  if(typeof window.ds31Download === 'function'){ window.ds31Download(); return; }
  if(!lastPptxB64)return;
  var byteStr=atob(lastPptxB64),ab=new ArrayBuffer(byteStr.length),ia=new Uint8Array(ab);
  for(var i=0;i<byteStr.length;i++)ia[i]=byteStr.charCodeAt(i);
  var blob=new Blob([ab],{type:'application/vnd.openxmlformats-officedocument.presentationml.presentation'});
  var url=URL.createObjectURL(blob),a=document.createElement('a');
  a.href=url;a.download=lastFilename;
  document.body.appendChild(a);a.click();document.body.removeChild(a);
  setTimeout(function(){URL.revokeObjectURL(url);},8000);
  document.getElementById('ds30-preview-modal').classList.remove('open');
  document.getElementById('ds30-success-popup').classList.add('open');
}

/* ── Success ── */
function ds30SuccessClose(another){
  document.getElementById('ds30-success-popup').classList.remove('open');
  if(!another) ds30ScreenClose();
  else {
    // Reset for next generation
    ds30ClearSel();
    document.getElementById('ds30-content-input').value='';
    lastPptxB64=null;lastFilename=null;
  }
}

/* ── Credits ── */
function ds30UpdateCredits(){
  var g=window.G,bar=document.getElementById('ds30-credits-bar');
  if(bar&&g&&g.credits!==undefined) bar.textContent='Credits: '+g.credits.toLocaleString();
}

/* ── Search ── */
var searchEl=document.getElementById('ds30-search-v2');
if(searchEl) searchEl.addEventListener('input',function(){clearTimeout(searchTimer);searchTimer=setTimeout(function(){ds30Load(true);},400);});

/* ── Mode card injection ── */
function ds30FindModeRow(){
  var aiEl=null,all=document.querySelectorAll('*');
  for(var i=0;i<all.length;i++){var tn=(all[i].textContent||'').trim();if(tn.indexOf('Update with AI')!==-1&&tn.length<60){aiEl=all[i];break;}}
  if(!aiEl)return null;
  var node=aiEl;
  while(node&&node.parentElement&&node.parentElement.tagName!=='BODY'){
    node=node.parentElement;
    var kids=node.children;if(kids.length<2)continue;
    var hasAI=false,hasPre=false;
    for(var k=0;k<kids.length;k++){
      var kt=(kids[k].textContent||'').trim();
      if(kt.indexOf('Update with AI')!==-1&&kt.length<200)hasAI=true;
      if(kt.indexOf('Preserve My Text')!==-1&&kt.length<200)hasPre=true;
    }
    if(hasAI&&hasPre)return node;
  }
  return null;
}
function ds30InjectModeCard(){
  if(document.getElementById('ds30-mode-card'))return;
  var row=ds30FindModeRow();if(!row)return;
  var sib=row.children[1]||row.children[0],s=sib?window.getComputedStyle(sib):null;
  var card=document.createElement('div');card.id='ds30-mode-card';
  card.style.cursor='pointer';card.style.boxSizing='border-box';
  card.style.transition='border-color .15s,background .15s';
  card.style.flex='1 1 0';card.style.minWidth='0'; /* equal share of the row; no longer copies the neighbour's flex once (a page first opened narrow kept "full width" and squeezed the other two cards) */
  if(s){card.style.borderRadius=s.borderRadius;card.style.border=s.border;card.style.background=s.background;card.style.padding=s.padding;}
  else{card.style.border='1px solid rgba(255,255,255,.1)';card.style.borderRadius='12px';card.style.padding='16px';}
  var sibTitle=sib?sib.querySelector('div,p,span'):null;
  var tc=sibTitle?window.getComputedStyle(sibTitle).color:'#f9fafb';
  var sibDesc=sibTitle?sibTitle.nextElementSibling:null;
  var dc=sibDesc?window.getComputedStyle(sibDesc).color:'#9ca3af';
  var ts=sibTitle?window.getComputedStyle(sibTitle).fontSize:'14px';
  var ds=sibDesc?window.getComputedStyle(sibDesc).fontSize:'12px';
  card.innerHTML='<div style="font-size:'+ts+';font-weight:700;color:'+tc+';margin-bottom:6px;display:flex;align-items:center;gap:6px;"><span>&#128444;&#65039;</span><span>Browse Templates</span></div>'
    +'<div style="font-size:'+ds+';color:'+dc+';line-height:1.4;">Pick a real PPTX template, AI fills your content in</div>';
  card.addEventListener('mouseenter',function(){if(!card.classList.contains('ds30-mc-on'))card.style.borderColor='rgba(255,255,255,.3)';});
  card.addEventListener('mouseleave',function(){if(!card.classList.contains('ds30-mc-on'))card.style.borderColor=s?s.borderColor:'';});
  card.addEventListener('click',function(){
    Array.prototype.forEach.call(row.children,function(c){c.classList&&c.classList.remove('ds30-mc-on');c.style.borderColor='';c.style.background=s?s.background:'';});
    card.classList.add('ds30-mc-on');card.style.borderColor='#2563eb';card.style.background='rgba(37,99,235,.12)';
    ds30ScreenOpen();
  });
  row.appendChild(card);
}

var ds30Obs=new MutationObserver(ds30InjectModeCard);
ds30Obs.observe(document.body,{childList:true,subtree:true});
var ds30PT=0,ds30PI=setInterval(function(){ds30InjectModeCard();if(document.getElementById('ds30-mode-card')||++ds30PT>80)clearInterval(ds30PI);},250);

window.ds30ScreenOpen=ds30ScreenOpen;
window.ds30GetSelId=function(){ return selId; };
window.ds30GetText=function(){ return (document.getElementById('ds30-content-input')||{}).value||''; };
window.ds30ScreenClose=ds30ScreenClose;
window.ds30LoadMore=ds30LoadMore;
window.ds30Generate=ds30Generate;
window.ds30ClearSel=ds30ClearSel;
window.ds30PreviewClose=ds30PreviewClose;
window.ds30Download=ds30Download;
window.ds30SuccessClose=ds30SuccessClose;
window.ds30InjectModeCard=ds30InjectModeCard;
// Legacy aliases
window.ds30Open=ds30ScreenOpen;
window.ds30Close=ds30ScreenClose;
})();
</script>
<?php
}, 1003);
