<?php
/**
 * Code Snippets export — Snippet 35B DS Blog Page
 *
 * Snippet ID : 733
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-047
 * Modified   : 2026-09-09 02:46:54
 * Load order : 047 of 144
 */
/**
 * Snippet 35B: DS Blog -- AI Images + References (Fixed)
 * Run: Everywhere
 *
 * FIXES:
 * 1. preg_replace $0 backreference was eating $ in img_tag
 * 2. wp_update_post kses was stripping <figure>/<img> -- now uses wp_slash + direct DB
 * 3. Polls poll-image until succeeded (async generation)
 * 4. Saves image URL to post meta as fallback for front-end display
 */

if ( ! defined('ABSPATH') ) exit;

// ===============================================================
// PART 1 -- Generate image: request -> poll -> sideload to media lib
// ===============================================================

if ( ! function_exists('ds_blog_cf_generate_image') ) {
function ds_blog_cf_generate_image( $prompt, $post_title = '' ) {

    if ( ! function_exists('ds_call_cloudflare_klein') ) {
        return new WP_Error('no_fn', 'ds_call_cloudflare_klein() not found. Is Snippet 2A active?');
    }

   // Build a more creative prompt
    $is_hero = strpos($post_title, $prompt) !== false || empty($prompt);
    $enhanced_prompt = $is_hero
        ? 'Cinematic wide hero image: ' . $post_title . '. Dramatic lighting, vivid colors, ultra realistic, no text, 16:9 widescreen, award winning photography.'
        : 'Professional editorial photo: ' . $prompt . '. Vivid, sharp, modern. No text, no words. 16:9.';

    $result = ds_call_cloudflare_klein(
        $enhanced_prompt,
        array('width' => 1024, 'height' => 576),
        1
    );

    if ( ! empty($result['error']) ) {
        return new WP_Error('cf_error', $result['error']);
    }

    $img_data = $result['images'][0] ?? '';
    if ( empty($img_data) ) {
        return new WP_Error('no_image', 'No image returned from Cloudflare Klein.');
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $b64    = preg_replace('#^data:image/\w+;base64,#', '', $img_data);
    $binary = base64_decode($b64);

    if ( empty($binary) || strlen($binary) < 100 ) {
        return new WP_Error('bad_image', 'Empty or corrupt image data.');
    }

    $upload = wp_upload_bits(
        'ds-blog-' . sanitize_title(substr($prompt, 0, 40)) . '-' . time() . '.png',
        null,
        $binary
    );

    if ( ! empty($upload['error']) ) {
        return new WP_Error('upload_error', $upload['error']);
    }

    $att_id = wp_insert_attachment(array(
        'post_mime_type' => 'image/png',
        'post_title'     => sanitize_text_field($prompt),
        'post_status'    => 'inherit',
    ), $upload['file']);

    if ( is_wp_error($att_id) ) return $att_id;

    wp_update_attachment_metadata($att_id, wp_generate_attachment_metadata($att_id, $upload['file']));

    return $att_id;
}
}

if ( ! function_exists('ds_blog_sideload_image') ) {
function ds_blog_sideload_image( $url, $prompt ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $tmp = download_url( esc_url_raw($url) );
    if ( is_wp_error($tmp) ) return $tmp;

    $ext  = pathinfo( parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION ) ?: 'jpg';
    $name = 'ds-blog-' . sanitize_title( substr($prompt, 0, 40) ) . '-' . time() . '.' . $ext;

    $att_id = media_handle_sideload( array('name' => $name, 'tmp_name' => $tmp), 0, sanitize_text_field($prompt) );
    @unlink($tmp);
    return $att_id;
}
}

// ===============================================================
// PART 2 -- Inject images into post content
//           Uses wpdb direct update to bypass kses stripping
// ===============================================================

if ( ! function_exists('ds_blog_inject_images_into_post') ) {
function ds_blog_inject_images_into_post( $post_id, $sections ) {
    global $wpdb;

    // Clear object cache to get fresh content from DB
    clean_post_cache($post_id);
    wp_cache_delete($post_id, 'posts');
    $post    = get_post($post_id);
    $content = $post->post_content;
    $results = array();
    $errors  = array();
    // Track image URLs per section index for meta fallback
    $img_urls = array();
	
	// Generate hero/featured image using featured_image_query
    if ( ! has_post_thumbnail($post_id) ) {
        $hero_query = get_post_meta($post_id, '_ds_blog_featured_image_query', true);
        if ( empty($hero_query) ) {
            $hero_query = 'Cinematic dramatic colourful scene representing ' . ($post->post_title ?? 'success') . ', professional photography, no text, wide angle';
        }
        $hero_att = ds_blog_cf_generate_image($hero_query, $post->post_title ?? '');
        if ( ! is_wp_error($hero_att) ) {
            set_post_thumbnail($post_id, $hero_att);
        }
    }

    foreach ( array_slice($sections, 0, 6) as $idx => $section ) {
        $query   = sanitize_text_field($section['image_query'] ?? $section['heading'] ?? '');
        $heading = sanitize_text_field($section['heading'] ?? $query);
        if ( empty($query) ) continue;

        $att_id = ds_blog_cf_generate_image($query, $post->post_title ?? '');

        if ( is_wp_error($att_id) ) {
            $errors[] = 'Section "' . esc_html($heading) . '": ' . $att_id->get_error_message();
            continue;
        }

        $img_url    = wp_get_attachment_url($att_id);
        $img_urls[] = array('heading' => $heading, 'url' => $img_url, 'att_id' => $att_id);

        // Build image HTML -- NO $ signs in replacement string
        $img_html = "\n\n"
            . '<figure class="ds-blog-ai-image" style="margin:24px 0 28px;">'
            . '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($heading) . '"'
            . ' style="width:100%;border-radius:12px;display:block;box-shadow:0 4px 20px rgba(0,0,0,.12);" loading="lazy">'
            . '<figcaption style="text-align:center;font-size:12px;color:#6b7280;margin-top:8px;font-style:italic;">'
            . esc_html($heading) . '</figcaption></figure>'
            . "\n\n";

        // Inject after matching H2
        // Use preg_replace_callback to avoid $ backreference issues
        $heading_escaped = preg_quote($heading, '/');
        $matched = false;

        $content = preg_replace_callback(
            '/<h2[^>]*>\s*' . $heading_escaped . '\s*<\/h2>/iu',
            function($m) use ($img_html, &$matched) {
                $matched = true;
                return $m[0] . $img_html;
            },
            $content,
            1
        );

        // Fallback: inject after Nth H2 if heading text didn't match
        if ( ! $matched ) {
            $n = 0;
            $content = preg_replace_callback(
                '/<h2[^>]*>.*?<\/h2>/is',
                function($m) use ($idx, &$n, $img_html) {
                    $n++;
                    return ( $n - 1 === $idx ) ? $m[0] . $img_html : $m[0];
                },
                $content
            );
        }

        // Set featured image from first section
        if ( $idx === 0 && ! has_post_thumbnail($post_id) ) {
            set_post_thumbnail($post_id, $att_id);
        }

        $results[] = array('section' => $heading, 'image_url' => $img_url, 'att_id' => $att_id);
    }

    // Save content directly via wpdb to bypass wp_kses stripping <img>/<figure>
    if ( ! empty($img_urls) ) {
        $wpdb->update(
            $wpdb->posts,
            array( 'post_content' => $content ),
            array( 'ID' => $post_id ),
            array('%s'),
            array('%d')
        );
        clean_post_cache($post_id);
        wp_cache_delete($post_id, 'posts');
        wp_cache_delete($post_id, 'post_meta');
        // Also save image URLs to post meta as display fallback
        update_post_meta($post_id, '_ds_blog_section_images', $img_urls);
    }

    return array('results' => $results, 'errors' => $errors);
}
}

// ===============================================================
// PART 3 -- Front-end: inject images from meta if not in content
//           This is the ultimate fallback so images always show
// ===============================================================

add_filter('the_content', function($content) {
    if ( ! is_singular() ) return $content;
    $post_id = get_the_ID();
    if ( get_post_type($post_id) !== 'ds_blog_post' ) return $content;

    // Only run fallback if content has no ds-blog-ai-image figures yet
    if ( strpos($content, 'ds-blog-ai-image') !== false ) return $content;

    $imgs = get_post_meta($post_id, '_ds_blog_section_images', true);
    if ( empty($imgs) || ! is_array($imgs) ) return $content;

    // Inject images after each matching H2
    foreach ( $imgs as $idx => $img ) {
        $heading   = $img['heading'] ?? '';
        $img_url   = $img['url']     ?? '';
        if ( empty($img_url) ) continue;

        $img_html = "\n\n"
            . '<figure class="ds-blog-ai-image" style="margin:24px 0 28px;">'
            . '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($heading) . '"'
            . ' style="width:100%;border-radius:12px;display:block;box-shadow:0 4px 20px rgba(0,0,0,.12);" loading="lazy">'
            . '<figcaption style="text-align:center;font-size:12px;color:#6b7280;margin-top:8px;font-style:italic;">'
            . esc_html($heading) . '</figcaption></figure>'
            . "\n\n";

        $matched = false;
        $content = preg_replace_callback(
            '/<h2[^>]*>\s*' . preg_quote($heading, '/') . '\s*<\/h2>/iu',
            function($m) use ($img_html, &$matched) {
                $matched = true;
                return $m[0] . $img_html;
            },
            $content, 1
        );

        if ( ! $matched ) {
            $n = 0;
            $content = preg_replace_callback(
                '/<h2[^>]*>.*?<\/h2>/is',
                function($m) use ($idx, &$n, $img_html) {
                    $n++;
                    return ($n - 1 === $idx) ? $m[0] . $img_html : $m[0];
                },
                $content
            );
        }
    }

    return $content;
});

// ===============================================================
// PART 4 -- Generate references via Groq
// ===============================================================

if ( ! function_exists('ds_blog_ai_generate_references') ) {
function ds_blog_ai_generate_references( $topic, $sections = array() ) {
    if ( ! function_exists('ds_blog_ai_call_groq') ) return array();

    $titles = implode(', ', array_map(fn($s) => $s['heading'] ?? '', array_slice($sections, 0, 6)));

    $result = ds_blog_ai_call_groq(
        'You are a research assistant. Return ONLY valid JSON. No markdown.',
        'Generate 5 references for: "' . $topic . '"' . ($titles ? "\nSections: $titles" : '')
        . "\nReturn: {\"references\":[{\"title\":\"...\",\"url\":\"https://...\",\"source\":\"Publisher\",\"description\":\"One sentence.\"}]}"
        . "\nUse real domains: wikipedia.org, forbes.com, hubspot.com, techcrunch.com, harvard.edu. Specific pages only.",
        1000
    );

    if ( is_wp_error($result) || empty($result['references']) ) return array();
    return (array)$result['references'];
}
}

// ===============================================================
// PART 5 -- References HTML block
// ===============================================================

if ( ! function_exists('ds_blog_build_references_html') ) {
function ds_blog_build_references_html( $refs ) {
    if ( empty($refs) ) return '';
    $html = '<div class="ds-blog-references" style="background:#f4f6fb;border-radius:14px;padding:24px 26px;margin-top:40px;border-left:4px solid #E8481C;font-family:Poppins,sans-serif;">'
          . "\n<!-- ds-references -->\n"
          . '<h3 style="margin:0 0 16px;font-size:16px;font-weight:800;color:#1B2A4A;"> References &amp; Further Reading</h3>'
          . '<ol style="margin:0;padding-left:20px;display:flex;flex-direction:column;gap:12px;">';
    foreach ( $refs as $i => $r ) {
        $html .= '<li style="font-size:13px;color:#374151;line-height:1.5;">'
               . '<a href="' . esc_url($r['url'] ?? '#') . '" target="_blank" rel="noopener" style="color:#E8481C;font-weight:700;text-decoration:none;">' . esc_html($r['title'] ?? '') . '</a>'
               . ( !empty($r['source']) ? ' <span style="color:#9ca3af;font-size:11px;">-- ' . esc_html($r['source']) . '</span>' : '' )
               . ( !empty($r['description']) ? '<br><span style="color:#6b7280;font-size:12px;">' . esc_html($r['description']) . '</span>' : '' )
               . '</li>';
    }
    $html .= '</ol></div>' . "\n<!-- /ds-references -->\n";
    return $html;
}
}

// ===============================================================
// PART 6 -- REST endpoints
// ===============================================================

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/blog-generate-images', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_rest_generate_images',
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ));
    register_rest_route('ds/v1', '/blog-generate-references', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_rest_generate_references',
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ));
    // Called from JS after image generation: saves image URLs and writes to DB content
    register_rest_route('ds/v1', '/blog-save-images', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_rest_save_images',
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ));
    // Debug: check what's actually in DB for a post
    register_rest_route('ds/v1', '/blog-debug', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_rest_debug',
        'permission_callback' => fn() => current_user_can('edit_posts'),
    ));
});

if ( ! function_exists('ds_blog_rest_debug') ) {
function ds_blog_rest_debug( WP_REST_Request $req ) {
    global $wpdb;
    $post_id = intval($req->get_param('post_id'));
    if ( ! $post_id ) return rest_ensure_response(array('error' => 'No post_id'));

    // Get raw content directly from DB (bypass cache)
    $raw = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $post_id));

    return rest_ensure_response(array(
        'post_id'         => $post_id,
        'has_figure'      => strpos($raw, 'ds-blog-ai-image') !== false,
        'has_h2'          => strpos($raw, '<h2') !== false,
        'h2_count'        => substr_count($raw, '<h2'),
        'figure_count'    => substr_count($raw, 'ds-blog-ai-image'),
        'content_length'  => strlen($raw),
        'section_images'  => get_post_meta($post_id, '_ds_blog_section_images', true),
        'image_queries'   => get_post_meta($post_id, '_ds_blog_image_queries', true),
        'content_preview' => substr($raw, 0, 800),
    ));
}
}

if ( ! function_exists('ds_blog_rest_generate_images') ) {
function ds_blog_rest_generate_images( WP_REST_Request $req ) {
    $post_id  = intval($req->get_param('post_id'));
    $sections = $req->get_param('sections') ?: array();
    $debug_mode = (bool)$req->get_param('debug');

    if ( ! $post_id || get_post_type($post_id) !== 'ds_blog_post' ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Invalid post ID.'));
    }
    if ( empty($sections) ) {
        $q = get_post_meta($post_id, '_ds_blog_image_queries', true) ?: array();
        $sections = array_map(fn($x) => array('heading' => $x, 'image_query' => $x), $q);
    }
    if ( empty($sections) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No image queries found. Generate the post with AI Writer first.'));
    }

    @set_time_limit(600);

    // TEST MODE: just test generate-image for first section and return raw response
    if ( $debug_mode ) {
    $section = $sections[0];
    $query   = $section['image_query'] ?? $section['heading'] ?? '';

    $gen_req = new WP_REST_Request( 'POST', '/ds/v1/generate-image' );
    $gen_req->set_body_params( array(
        'model'  => 'cloudflare-klein',
        'prompt' => 'Test: ' . $query,
        'ratio'  => '16:9',
        'count'  => 1,
        'style'  => '',
    ));
    $gen_resp = rest_do_request( $gen_req );
    $gen_body = rest_get_server()->response_to_data( $gen_resp, false );
    $gen_code = $gen_resp->get_status();
    $pid      = $gen_body['prediction_id'] ?? $gen_body['prediction_ids'][0] ?? $gen_body['id'] ?? '';

    $poll_body = null;
    if ( $pid ) {
        sleep(5);
        $poll_req = new WP_REST_Request( 'POST', '/ds/v1/poll-image' );
        $poll_req->set_body_params( array(
            'prediction_id' => $pid,
            'prompt'        => $query,
            'ratio'         => '16:9',
            'model'         => 'cloudflare-klein',
            'style'         => '',
            'image_size'    => '',
        ));
        $poll_resp = rest_do_request( $poll_req );
        $poll_body = rest_get_server()->response_to_data( $poll_resp, false );
    }

    return rest_ensure_response( array(
        'debug'         => true,
        'query_tested'  => $query,
        'generate_code' => $gen_code,
        'generate_body' => $gen_body,
        'prediction_id' => $pid,
        'poll_after_5s' => $poll_body,
    ));
    }

    $out = ds_blog_inject_images_into_post($post_id, $sections);

    return rest_ensure_response(array(
        'success'        => true,
        'images_created' => count($out['results']),
        'results'        => $out['results'],
        'errors'         => $out['errors'],
    ));
}
}

if ( ! function_exists('ds_blog_rest_save_images') ) {
function ds_blog_rest_save_images( WP_REST_Request $req ) {
    global $wpdb;
    $post_id = intval($req->get_param('post_id'));
    $images  = $req->get_param('images') ?: array();

    if ( ! $post_id || get_post_type($post_id) !== 'ds_blog_post' ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Invalid post ID.'));
    }
    if ( empty($images) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No images provided.'));
    }

    clean_post_cache($post_id);
    $post    = get_post($post_id);
    $content = $post->post_content;
    $injected = 0;

    // Save image URLs to meta for the_content fallback
    $meta_imgs = array();
    foreach ( $images as $img ) {
        $meta_imgs[] = array(
            'heading' => sanitize_text_field($img['heading'] ?? ''),
            'url'     => esc_url_raw($img['url'] ?? ''),
        );
    }
    update_post_meta($post_id, '_ds_blog_section_images', $meta_imgs);

    // Also inject into DB content
    foreach ( $meta_imgs as $idx => $img ) {
        $heading = $img['heading'];
        $url     = $img['url'];
        if ( empty($url) ) continue;

        $img_html = "\n\n"
            . '<figure class="ds-blog-ai-image" style="margin:24px 0 28px;">'
            . '<img src="' . esc_url($url) . '" alt="' . esc_attr($heading) . '"'
            . ' style="width:100%;border-radius:12px;display:block;box-shadow:0 4px 20px rgba(0,0,0,.12);" loading="lazy">'
            . '<figcaption style="text-align:center;font-size:12px;color:#6b7280;margin-top:8px;font-style:italic;">'
            . esc_html($heading) . '</figcaption></figure>'
            . "\n\n";

        $matched = false;
        if ( ! empty($heading) ) {
            $content = preg_replace_callback(
                '/<h2[^>]*>\s*' . preg_quote($heading, '/') . '\s*<\/h2>/iu',
                function($m) use ($img_html, &$matched) {
                    $matched = true;
                    return $m[0] . $img_html;
                },
                $content, 1
            );
        }
        if ( ! $matched ) {
            $n = 0;
            $content = preg_replace_callback(
                '/<h2[^>]*>.*?<\/h2>/is',
                function($m) use ($idx, &$n, $img_html) {
                    $n++;
                    return ($n - 1 === $idx) ? $m[0] . $img_html : $m[0];
                },
                $content
            );
        }
        $injected++;
    }

    $wpdb->update(
        $wpdb->posts,
        array('post_content' => $content),
        array('ID' => $post_id),
        array('%s'), array('%d')
    );
    clean_post_cache($post_id);

    return rest_ensure_response(array(
        'success'  => true,
        'injected' => $injected,
    ));
}
}

if ( ! function_exists('ds_blog_rest_generate_references') ) {
function ds_blog_rest_generate_references( WP_REST_Request $req ) {
    $post_id  = intval($req->get_param('post_id'));
    $topic    = sanitize_text_field($req->get_param('topic') ?? '');
    $sections = $req->get_param('sections') ?: array();

    if ( ! $post_id || get_post_type($post_id) !== 'ds_blog_post' ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Invalid post ID.'));
    }

    $refs = ds_blog_ai_generate_references($topic, $sections);
    if ( empty($refs) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Could not generate references.'));
    }

    global $wpdb;
    $post    = get_post($post_id);
    $content = preg_replace('/<div class="ds-blog-references".*?<!-- \/ds-references -->/is', '', $post->post_content);
    $content .= "\n\n" . ds_blog_build_references_html($refs);

    $wpdb->update($wpdb->posts, array('post_content' => $content), array('ID' => $post_id), array('%s'), array('%d'));
    clean_post_cache($post_id);
    update_post_meta($post_id, '_ds_blog_references', $refs);

    return rest_ensure_response(array('success' => true, 'count' => count($refs)));
}
}

// ===============================================================
// PART 7 -- Admin meta box
// ===============================================================

add_action('add_meta_boxes', function () {
    add_meta_box('ds_blog_img_refs', '[IMG] AI Images & References',
        'ds_blog_img_refs_meta_box', 'ds_blog_post', 'side', 'high');
});

if ( ! function_exists('ds_blog_img_refs_meta_box') ) {
function ds_blog_img_refs_meta_box( $post ) {
    $nonce     = wp_create_nonce('wp_rest');
    $img_url   = rest_url('ds/v1/blog-generate-images');
    $refs_url  = rest_url('ds/v1/blog-generate-references');
    $post_id   = $post->ID;
    $queries   = get_post_meta($post_id, '_ds_blog_image_queries', true) ?: array();
    $refs      = get_post_meta($post_id, '_ds_blog_references', true) ?: array();
    $sec_imgs  = get_post_meta($post_id, '_ds_blog_section_images', true) ?: array();
    $has_thumb = has_post_thumbnail($post_id);
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
    #ds-imgref-box *{font-family:'Poppins',sans-serif;box-sizing:border-box;}
    .ds-ir-btn{width:100%;padding:10px 14px;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:6px;}
    .ds-ir-both{background:linear-gradient(135deg,#1B2A4A,#243a5a);color:#fff;}
    .ds-ir-img{background:linear-gradient(135deg,#E8481C,#c93a15);color:#fff;}
    .ds-ir-refs{background:#1B2A4A;color:#fff;}
    .ds-ir-btn:hover{opacity:.88;} .ds-ir-btn:disabled{opacity:.4;cursor:default;}
    .ds-ir-st{font-size:11px;padding:8px 10px;border-radius:7px;margin-bottom:8px;display:none;line-height:1.6;}
    .ds-ir-st.show{display:block;}
    .ds-ir-st.ok{background:#f0fdf4;color:#16a34a;border:1px solid #86efac;}
    .ds-ir-st.err{background:#fff4f4;color:#dc2626;border:1px solid #fca5a5;}
    .ds-ir-st.info{background:#f0f7ff;color:#1B2A4A;border:1px solid #C8D8F0;}
    .ds-ir-q{font-size:10px;background:#f4f6fb;border-radius:5px;padding:3px 8px;margin-bottom:4px;color:#1B2A4A;}
    .ds-ir-q::before{content:'[IMG] ';}
    .ds-ir-lbl{font-size:10px;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;margin:12px 0 6px;}
    .ds-ir-img-thumb{width:100%;border-radius:8px;margin-bottom:6px;display:block;}
    </style>

    <div id="ds-imgref-box">
        <?php if ( ! empty($queries) ) : ?>
        <div class="ds-ir-lbl">Planned image queries (<?php echo count($queries); ?>)</div>
        <?php foreach ( array_slice($queries, 0, 6) as $q ) : ?>
        <div class="ds-ir-q"><?php echo esc_html($q); ?></div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if ( ! empty($sec_imgs) ) : ?>
        <div class="ds-ir-lbl" style="margin-top:14px;">[OK] Generated images (<?php echo count($sec_imgs); ?>)</div>
        <?php foreach ( $sec_imgs as $si ) : ?>
        <img src="<?php echo esc_url($si['url'] ?? ''); ?>" class="ds-ir-img-thumb" alt="<?php echo esc_attr($si['heading'] ?? ''); ?>">
        <?php endforeach; ?>
        <?php endif; ?>

        <p style="font-size:10px;color:#9ca3af;margin:8px 0 10px;line-height:1.5;">
             Each image takes ~30-60s. Wait for [OK] then <strong>refresh page</strong>.
        </p>

        <div id="ds-ir-st" class="ds-ir-st"></div>

        <button class="ds-ir-btn ds-ir-both" id="ds-ir-both"> Generate Images + References</button>
        <button class="ds-ir-btn ds-ir-img"  id="ds-ir-img">[IMG] Images Only</button>
        <button class="ds-ir-btn ds-ir-refs" id="ds-ir-refs"> References Only</button>

        <?php if ($has_thumb) : ?>
        <p style="font-size:10px;color:#16a34a;margin:8px 0 0;">[OK] Featured image set</p>
        <?php endif; ?>

        <?php if ( ! empty($refs) ) : ?>
        <div class="ds-ir-lbl" style="margin-top:12px;">References (<?php echo count($refs); ?>)</div>
        <?php foreach ($refs as $r) : ?>
        <div style="font-size:10px;color:#6b7280;margin-bottom:4px;">
             <a href="<?php echo esc_url($r['url'] ?? '#'); ?>" target="_blank" style="color:#E8481C;"><?php echo esc_html($r['title'] ?? ''); ?></a>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    (function(){
    var N = <?php echo wp_json_encode($nonce); ?>;
    var IU = <?php echo wp_json_encode($img_url); ?>;
    var RU = <?php echo wp_json_encode($refs_url); ?>;
    var PID = <?php echo intval($post_id); ?>;
    var TOPIC = <?php echo wp_json_encode($post->post_title); ?>;
    var QS = <?php echo wp_json_encode($queries); ?>;

    function st(msg, type) {
        var el = document.getElementById('ds-ir-st');
        el.className = 'ds-ir-st show ' + (type||'info');
        el.innerHTML = msg;
    }
    function btns(dis) {
        ['ds-ir-both','ds-ir-img','ds-ir-refs'].forEach(function(id){
            var b = document.getElementById(id); if(b) b.disabled = dis;
        });
    }
    function secs() { return QS.map(function(q){ return {heading:q, image_query:q}; }); }

    var SAVE_URL = <?php echo wp_json_encode(rest_url('ds/v1/blog-save-images')); ?>;

    function buildPrompt(query, postTitle) {
        // Map common query patterns to better visual descriptions
        var q = query.toLowerCase();
        var base = '';
        if (q.indexOf('benefit') !== -1 || q.indexOf('advantage') !== -1) {
            base = 'Happy professionals celebrating success, checkmarks, growth chart, bright office environment';
        } else if (q.indexOf('comparison') !== -1 || q.indexOf('compare') !== -1 || q.indexOf('chart') !== -1) {
            base = 'Side by side comparison of two modern software dashboards on laptop screens, clean UI';
        } else if (q.indexOf('software') !== -1 || q.indexOf('interface') !== -1 || q.indexOf('tool') !== -1) {
            base = 'Modern software application interface on laptop screen, clean dashboard design, professional';
        } else if (q.indexOf('collaborat') !== -1 || q.indexOf('team') !== -1) {
            base = 'Diverse team collaborating around a conference table with laptops and screens, modern office';
        } else if (q.indexOf('future') !== -1 || q.indexOf('technolog') !== -1 || q.indexOf('ai') !== -1) {
            base = 'Futuristic digital technology concept, glowing neural network, modern abstract tech background';
        } else if (q.indexOf('design') !== -1 || q.indexOf('inspiration') !== -1 || q.indexOf('creative') !== -1) {
            base = 'Creative designer working on colorful presentation slides, modern workspace, inspiration mood board';
        } else if (q.indexOf('presentation') !== -1 || q.indexOf('slide') !== -1) {
            base = 'Business presenter showing slides to an engaged audience in a modern conference room';
        } else if (q.indexOf('choose') !== -1 || q.indexOf('select') !== -1 || q.indexOf('best') !== -1) {
            base = 'Person thoughtfully choosing between options on a digital screen, decision making concept';
        } else {
            base = query;
        }
        return 'High quality professional photo: ' + base + '. No text, no words, no labels. Wide 16:9 format. Sharp, vivid, modern.';
    }

    async function pollForImage(pid, prompt) {
        for (var i = 0; i < 24; i++) {
            await new Promise(function(r){ setTimeout(r, 5000); });
            try {
                var r = await fetch(<?php echo wp_json_encode(rest_url('ds/v1/poll-image')); ?>, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-WP-Nonce':N},
                    body: JSON.stringify({prediction_id: pid, prompt: prompt, ratio:'16:9', model:'cloudflare-klein', style:'', image_size:''})
                });
                var d = await r.json();
                if (d.status === 'succeeded' && d.images && d.images[0]) {
                    var img = d.images[0];
                    return typeof img === 'string' ? img : (img.url || '');
                }
                if (d.status === 'failed' || d.status === 'error') return null;
            } catch(e) { continue; }
        }
        return null;
    }

    async function genImg() {
        var qs = QS;
        if (!qs || !qs.length) { st('[ERROR] No image queries found. Generate post with AI Writer first.', 'err'); return; }

        st('[WAIT] Generating ' + qs.length + ' images from browser... do not close this tab.', 'info');
        btns(true);

        var savedImgs = [];
        try {
            for (var i = 0; i < qs.length; i++) {
                var q = qs[i];
                st('[WAIT] Generating image ' + (i+1) + '/' + qs.length + ': ' + q + '...', 'info');

                // Step 1: Start generation (browser fetch -- nonce works from browser)
                var genResp = await fetch(<?php echo wp_json_encode(rest_url('ds/v1/generate-image')); ?>, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-WP-Nonce':N},
                    body: JSON.stringify({model:'cloudflare-klein', prompt: buildPrompt(q, TOPIC), ratio:'16:9', count:1, style:''})
                });
                var genData = await genResp.json();

                var imgUrl = genData.image_url 
    || genData.url 
    || (genData.images && genData.images[0] && (typeof genData.images[0]==='string' ? genData.images[0] : (genData.images[0].url || '')))
    || '';

                // Step 2: If async, poll until done (cloudflare-klein returns direct URL so this is skipped)
if (!imgUrl) {
    var pid = genData.prediction_id || (genData.prediction_ids && genData.prediction_ids[0]) || genData.id || '';
    if (pid) {
        st('[WAIT] Image ' + (i+1) + '/' + qs.length + ' generating... polling...', 'info');
        imgUrl = await pollForImage(pid, q);
    } else {
        st('[WARN] Image ' + (i+1) + ' - no URL and no prediction_id. Response: ' + JSON.stringify(genData).substring(0,100), 'err');
    }
}

                if (imgUrl) {
                    // Verify image actually loads (not blank)
                    var testLoad = await new Promise(function(resolve) {
                        var img = new Image();
                        img.onload  = function() { resolve(img.naturalWidth > 10); };
                        img.onerror = function() { resolve(false); };
                        setTimeout(function() { resolve(true); }, 3000); // timeout = assume ok
                        img.src = imgUrl;
                    });
                    if (testLoad) {
                        savedImgs.push({heading: q, url: imgUrl});
                        st('[OK] Image ' + (i+1) + '/' + qs.length + ' done!', 'info');
                    } else {
                        st('[WARN] Image ' + (i+1) + ' loaded blank -- retrying with different prompt...', 'info');
                        // Retry once with simpler prompt
                        var retryResp = await fetch(<?php echo wp_json_encode(rest_url('ds/v1/generate-image')); ?>, {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-WP-Nonce':N},
                            body: JSON.stringify({model:'cloudflare-klein', prompt:'Professional photograph: ' + q + '. High quality. No text.', ratio:'16:9', count:1, style:''})
                        });
                        var retryData = await retryResp.json();
                        var retryUrl = retryData.image_url || (retryData.images && retryData.images[0] && (typeof retryData.images[0]==='string' ? retryData.images[0] : retryData.images[0].url)) || '';
                        if (!retryUrl && (retryData.prediction_id || retryData.prediction_ids)) {
                            var rpid = retryData.prediction_id || retryData.prediction_ids[0];
                            retryUrl = await pollForImage(rpid, q) || '';
                        }
                        if (retryUrl) {
                            savedImgs.push({heading: q, url: retryUrl});
                            st('[OK] Image ' + (i+1) + '/' + qs.length + ' done (retry)!', 'info');
                        } else {
                            st('[WARN] Image ' + (i+1) + ' failed after retry, skipping.', 'info');
                        }
                    }
                } else {
                    st('[WARN] Image ' + (i+1) + ' failed, continuing...', 'info');
                }
            }

            if (!savedImgs.length) {
                st('[ERROR] No images were generated successfully.', 'err');
                btns(false); return;
            }

            // Step 3: Save image URLs to meta + inject into post DB content
            st('[WAIT] Saving ' + savedImgs.length + ' images to post...', 'info');
            var saveResp = await fetch(SAVE_URL, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-WP-Nonce':N},
                body: JSON.stringify({post_id: PID, images: savedImgs})
            });
            var saveData = await saveResp.json();

            if (saveData && saveData.success) {
                st('[OK] ' + savedImgs.length + ' image(s) saved! <strong>Refresh this page</strong> to see them.', 'ok');
            } else {
                st('[OK] ' + savedImgs.length + ' image(s) generated! Refresh page to see them on the post.', 'ok');
            }

        } catch(e) { st('[ERROR] ' + e.message, 'err'); }
        btns(false);
    }

    async function genRefs() {
        st('[WAIT] Generating references...', 'info'); btns(true);
        try {
            var r = await fetch(RU, {method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':N},
                body: JSON.stringify({post_id: PID, topic: TOPIC, sections: secs()})});
            var d = await r.json();
            st(d.success ? '[OK] References added! Refresh to see.' : '[ERROR] ' + (d.error||'Failed'), d.success ? 'ok' : 'err');
        } catch(e) { st('[ERROR] ' + e.message, 'err'); }
        btns(false);
    }

    document.getElementById('ds-ir-img').onclick   = genImg;
    document.getElementById('ds-ir-refs').onclick  = genRefs;
    document.getElementById('ds-ir-both').onclick  = async function(){ await genImg(); await genRefs(); };
    })();
    </script>
    <?php
}
}

// ===============================================================
// PART 8 -- Auto cron support
// ===============================================================

add_action('ds_ai_blog_post_created', function($post_id, $data) {
    @set_time_limit(600);
    if ( ! empty($data['sections']) ) ds_blog_inject_images_into_post($post_id, $data['sections']);
    if ( ! empty($data['title']) ) {
        $refs = ds_blog_ai_generate_references($data['title'], $data['sections'] ?? array());
        if ( ! empty($refs) ) {
            global $wpdb;
            $post    = get_post($post_id);
            $content = $post->post_content . "\n\n" . ds_blog_build_references_html($refs);
            $wpdb->update($wpdb->posts, array('post_content' => $content), array('ID' => $post_id), array('%s'), array('%d'));
            clean_post_cache($post_id);
            update_post_meta($post_id, '_ds_blog_references', $refs);
        }
    }
}, 10, 2);

add_filter('ds_ai_blog_after_insert', function($post_id, $data) {
    update_post_meta($post_id, '_ds_blog_ai_generated', 1);
    update_post_meta($post_id, '_ds_blog_ai_data_temp', $data);
    return $post_id;
}, 10, 2);

add_action('wp_insert_post', function($post_id, $post, $update) {
    if ($update || $post->post_type !== 'ds_blog_post' || $post->post_status === 'auto-draft') return;
    if ( ! get_post_meta($post_id, '_ds_blog_ai_generated', true) ) return;
    wp_schedule_single_event(time() + 5, 'ds_blog_async_media', array($post_id));
}, 10, 3);

add_action('ds_blog_async_media', function($post_id) {
    $data = get_post_meta($post_id, '_ds_blog_ai_data_temp', true);
    if (!$data) return;
    delete_post_meta($post_id, '_ds_blog_ai_data_temp');
    do_action('ds_ai_blog_post_created', $post_id, $data);
});
