<?php
/**
 * Code Snippets export — Snippet 35 ds ai blog writer
 *
 * Snippet ID : 737
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-051
 * Modified   : 2026-09-09 02:45:08
 * Load order : 051 of 144
 */
/**
 * Snippet 35: DS AI Blog Writer
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * Uses your existing Groq API key (same as the rest of DesignStager).
 *
 * TWO MODES:
 *  A) MANUAL  — wp-admin → DS Blog → Add New → "✨ Generate with AI" button
 *               Type a topic/title, click generate, full post appears instantly.
 *
 *  B) AUTO    — wp-admin → DS Blog → AI Auto-Schedule
 *               Add a list of topics + pick frequency (daily / weekly).
 *               WP-Cron picks the next topic and publishes automatically.
 *
 * GENERATES PER POST:
 *  - Full blog post (intro, H2 sections with body paragraphs, conclusion)
 *  - SEO meta title + meta description
 *  - Image query per section (for use with Snippet 18 / your image system)
 *
 * MODEL: uses ds_groq_ppt_model option (same as rest of site).
 *        Default: llama-3.3-70b-versatile
 *
 * COLORS: DesignStager brand — #E8481C (orange-red), #1B2A4A (navy)
 */

if ( ! defined('ABSPATH') ) exit;

// ═══════════════════════════════════════════════════════════════
// PART A — Shared Groq helper
// ═══════════════════════════════════════════════════════════════

if ( ! function_exists('ds_blog_ai_groq_key') ) {
function ds_blog_ai_groq_key() {
    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    return $keys['groq']
        ?? $keys['groq_api_key']
        ?? get_option('ds_groq_api_key', '')
        ?? get_option('groq_api_key', '');
}
}

if ( ! function_exists('ds_blog_ai_call_groq') ) {
function ds_blog_ai_call_groq( $system_prompt, $user_prompt, $max_tokens = 4000 ) {
    $api_k = ds_blog_ai_groq_key();
    if ( empty($api_k) ) return new WP_Error('no_key', 'Groq API key not configured.');

    $model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');

    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'timeout' => 90,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_k,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode(array(
            'model'           => $model,
            'messages'        => array(
                array('role' => 'system', 'content' => $system_prompt),
                array('role' => 'user',   'content' => $user_prompt),
            ),
            'temperature'     => 0.7,
            'max_tokens'      => $max_tokens,
            'response_format' => array('type' => 'json_object'),
        )),
    ));

    if ( is_wp_error($resp) ) return $resp;

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    $raw  = $body['choices'][0]['message']['content'] ?? '';
    $raw  = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
    $raw  = preg_replace('/```\s*$/i', '', $raw);

    $parsed = json_decode(trim($raw), true);
    if ( ! is_array($parsed) ) return new WP_Error('parse_error', 'AI returned invalid JSON.');
    return $parsed;
}
}

// ═══════════════════════════════════════════════════════════════
// PART B — Core blog generation logic
// ═══════════════════════════════════════════════════════════════

if ( ! function_exists('ds_blog_ai_generate') ) {
function ds_blog_ai_generate( $topic, $tone = 'professional', $word_count = 800 ) {

    $system = 'You are an expert blog writer for DesignStager, an AI-powered design and presentation platform. '
        . 'You write engaging, SEO-optimised blog posts in a ' . $tone . ' tone. '
        . 'Always respond with a single valid JSON object and nothing else. No markdown fences.';

    $user = 'Write a complete blog post about: "' . $topic . '"

Return ONLY a JSON object with this exact structure:
{
  "title": "SEO-optimised blog post title",
  "seo_title": "Meta title under 60 chars",
  "seo_description": "Meta description under 155 chars that includes the main keyword",
  "sections": [
    {
      "heading": "Section heading (H2)",
      "body": "2-3 paragraphs of body text for this section. Use <p> tags.",
      "image_query": "Real photo scene 8-12 words. Example: diverse team collaborating around conference table. NEVER use: laptop, screen, software, interface, technology, concept."
    }
  ],
  "introduction": "<p>Opening paragraph that hooks the reader and introduces the topic.</p>",
  "conclusion": "<p>Closing paragraph with a call to action relevant to DesignStager.</p>",
  "focus_keyword": "main SEO keyword",
  "tags": ["tag1", "tag2", "tag3"]
}

Requirements:
- Write approximately ' . $word_count . ' words total
- Include between 4 and 12 sections (vary it every time - sometimes 4, sometimes 8, sometimes 12)
- Each section body must use <p> tags
- image_query: vivid real photo scene, no laptops, no screens, no abstract tech words
- featured_image_query: cinematic dramatic hero photo 10-15 words, colourful, no laptops. Add this field to the JSON root level alongside title and tags.
- seo_title must be under 60 characters
- seo_description must be under 155 characters
- tags: 3-5 relevant lowercase tags';

    return ds_blog_ai_call_groq($system, $user, 4000);
}
}

// ═══════════════════════════════════════════════════════════════
// PART C — REST endpoint: manual generate
// ═══════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/ai-blog-generate', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_ai_rest_generate',
        'permission_callback' => function () { return current_user_can('edit_posts'); },
    ));
    register_rest_route('ds/v1', '/ai-blog-save', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_ai_rest_save',
        'permission_callback' => function () { return current_user_can('edit_posts'); },
    ));
    register_rest_route('ds/v1', '/ai-blog-schedule', array(
        'methods'             => 'POST',
        'callback'            => 'ds_blog_ai_rest_schedule',
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));
    register_rest_route('ds/v1', '/ai-blog-schedule', array(
        'methods'             => 'GET',
        'callback'            => 'ds_blog_ai_rest_get_schedule',
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));
});

if ( ! function_exists('ds_blog_ai_rest_generate') ) {
function ds_blog_ai_rest_generate( WP_REST_Request $req ) {
    $topic      = sanitize_text_field($req->get_param('topic') ?? '');
    $tone       = sanitize_text_field($req->get_param('tone') ?? 'professional');
    $word_count = intval($req->get_param('word_count') ?? 800);

    if ( empty($topic) ) return rest_ensure_response(array('success' => false, 'error' => 'Topic is required.'));

    $result = ds_blog_ai_generate($topic, $tone, $word_count);
    if ( is_wp_error($result) ) return rest_ensure_response(array('success' => false, 'error' => $result->get_error_message()));

    return rest_ensure_response(array('success' => true, 'data' => $result));
}
}

if ( ! function_exists('ds_blog_ai_rest_save') ) {
function ds_blog_ai_rest_save( WP_REST_Request $req ) {
    $post_id    = intval($req->get_param('post_id') ?? 0);
    $data       = $req->get_param('data');
    $status     = sanitize_text_field($req->get_param('status') ?? 'draft');
    $topic_slug = sanitize_text_field($req->get_param('topic_slug') ?? '');

    if ( ! is_array($data) || empty($data['title']) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No blog data provided.'));
    }

    // Build full post content from sections
    $content = wp_kses_post($data['introduction'] ?? '');
    foreach ( (array)($data['sections'] ?? array()) as $sec ) {
        $content .= "\n\n<h2>" . sanitize_text_field($sec['heading'] ?? '') . "</h2>\n";
        $content .= wp_kses_post($sec['body'] ?? '');
    }
    $content .= "\n\n" . wp_kses_post($data['conclusion'] ?? '');

    $post_data = array(
        'post_title'   => sanitize_text_field($data['title'] ?? ''),
        'post_content' => $content,
        'post_status'  => in_array($status, array('draft', 'publish'), true) ? $status : 'draft',
        'post_type'    => 'ds_blog_post',
    );

    if ( $post_id > 0 && get_post_type($post_id) === 'ds_blog_post' ) {
        $post_data['ID'] = $post_id;
        $saved_id = wp_update_post($post_data, true);
    } else {
        $saved_id = wp_insert_post($post_data, true);
    }

    if ( is_wp_error($saved_id) ) return rest_ensure_response(array('success' => false, 'error' => $saved_id->get_error_message()));

    // Save SEO meta
    update_post_meta($saved_id, '_ds_blog_seo_title',       sanitize_text_field($data['seo_title'] ?? ''));
    update_post_meta($saved_id, '_ds_blog_seo_description', sanitize_text_field($data['seo_description'] ?? ''));
    update_post_meta($saved_id, '_ds_blog_focus_keyword',   sanitize_text_field($data['focus_keyword'] ?? ''));

    // Save image queries per section
    $image_queries = array_map(function($s) {
        return sanitize_text_field($s['image_query'] ?? '');
    }, (array)($data['sections'] ?? array()));
    update_post_meta($saved_id, '_ds_blog_image_queries', array_filter($image_queries));

    // Assign topic taxonomy
    if ( $topic_slug ) {
        $term = get_term_by('slug', $topic_slug, 'ds_blog_topic');
        if ( $term ) wp_set_post_terms($saved_id, array($term->term_id), 'ds_blog_topic');
    }

    // Assign tags
    if ( ! empty($data['tags']) && is_array($data['tags']) ) {
        $tags = array_map('sanitize_text_field', $data['tags']);
        wp_set_post_terms($saved_id, $tags, 'post_tag');
    }

    return rest_ensure_response(array(
        'success' => true,
        'post_id' => $saved_id,
        'edit_url'=> get_edit_post_link($saved_id, 'raw'),
    ));
}
}

// ═══════════════════════════════════════════════════════════════
// PART D — Auto-schedule REST + WP-Cron
// ═══════════════════════════════════════════════════════════════

if ( ! function_exists('ds_blog_ai_rest_schedule') ) {
function ds_blog_ai_rest_schedule( WP_REST_Request $req ) {
    $topics    = $req->get_param('topics') ?? array();
    $frequency = sanitize_text_field($req->get_param('frequency') ?? 'weekly');
    $tone      = sanitize_text_field($req->get_param('tone') ?? 'professional');
    $status    = sanitize_text_field($req->get_param('auto_status') ?? 'publish');
    $word_count= intval($req->get_param('word_count') ?? 800);

    if ( ! is_array($topics) ) $topics = array_filter(array_map('trim', explode("\n", (string)$topics)));
    $topics = array_values(array_filter(array_map('sanitize_text_field', $topics)));

    if ( empty($topics) ) return rest_ensure_response(array('success' => false, 'error' => 'Please add at least one topic.'));
    if ( ! in_array($frequency, array('daily', 'weekly', 'twiceweekly'), true) ) $frequency = 'weekly';

    update_option('ds_ai_blog_topics',     $topics);
    update_option('ds_ai_blog_frequency',  $frequency);
    update_option('ds_ai_blog_tone',       $tone);
    update_option('ds_ai_blog_status',     $status);
    update_option('ds_ai_blog_word_count', $word_count);
    update_option('ds_ai_blog_queue',      $topics); // queue = remaining topics

    // Clear old cron and reschedule
    $hook = 'ds_ai_blog_cron';
    $ts   = wp_next_scheduled($hook);
    if ( $ts ) wp_unschedule_event($ts, $hook);

    $recurrence = $frequency === 'daily' ? 'daily'
                : ($frequency === 'twiceweekly' ? 'ds_twice_weekly' : 'weekly');
    wp_schedule_event(time() + 60, $recurrence, $hook);

    return rest_ensure_response(array('success' => true, 'message' => count($topics) . ' topics queued on ' . $frequency . ' schedule.'));
}
}

if ( ! function_exists('ds_blog_ai_rest_get_schedule') ) {
function ds_blog_ai_rest_get_schedule() {
    return rest_ensure_response(array(
        'success'    => true,
        'topics'     => get_option('ds_ai_blog_topics', array()),
        'queue'      => get_option('ds_ai_blog_queue', array()),
        'frequency'  => get_option('ds_ai_blog_frequency', 'weekly'),
        'tone'       => get_option('ds_ai_blog_tone', 'professional'),
        'auto_status'=> get_option('ds_ai_blog_status', 'publish'),
        'word_count' => get_option('ds_ai_blog_word_count', 800),
        'next_run'   => wp_next_scheduled('ds_ai_blog_cron'),
    ));
}
}

// Register custom cron interval: twice weekly
add_filter('cron_schedules', function ($schedules) {
    $schedules['ds_twice_weekly'] = array(
        'interval' => 3 * DAY_IN_SECONDS + HOUR_IN_SECONDS * 12,
        'display'  => 'Twice Weekly',
    );
    return $schedules;
});

// Cron callback — write next post from queue
add_action('ds_ai_blog_cron', function () {
    $queue      = get_option('ds_ai_blog_queue', array());
    $tone       = get_option('ds_ai_blog_tone', 'professional');
    $status     = get_option('ds_ai_blog_status', 'publish');
    $word_count = intval(get_option('ds_ai_blog_word_count', 800));

    if ( empty($queue) ) {
        // Refill queue from original list
        $queue = get_option('ds_ai_blog_topics', array());
        if ( empty($queue) ) return;
        shuffle($queue);
    }

    $topic = array_shift($queue);
    update_option('ds_ai_blog_queue', $queue);

    $data = ds_blog_ai_generate($topic, $tone, $word_count);
    if ( is_wp_error($data) || ! is_array($data) ) {
        // Log failure, don't crash cron
        update_option('ds_ai_blog_last_error', is_wp_error($data) ? $data->get_error_message() : 'Invalid AI response');
        return;
    }

    // Build content
    $content = wp_kses_post($data['introduction'] ?? '');
    foreach ( (array)($data['sections'] ?? array()) as $sec ) {
        $content .= "\n\n<h2>" . sanitize_text_field($sec['heading'] ?? '') . "</h2>\n";
        $content .= wp_kses_post($sec['body'] ?? '');
    }
    $content .= "\n\n" . wp_kses_post($data['conclusion'] ?? '');

    $post_id = wp_insert_post(array(
        'post_title'   => sanitize_text_field($data['title'] ?? $topic),
        'post_content' => $content,
        'post_status'  => $status,
        'post_type'    => 'ds_blog_post',
    ));

    if ( ! is_wp_error($post_id) ) {
    update_post_meta($post_id, '_ds_blog_seo_title',       sanitize_text_field($data['seo_title'] ?? ''));
    update_post_meta($post_id, '_ds_blog_seo_description', sanitize_text_field($data['seo_description'] ?? ''));
    update_post_meta($post_id, '_ds_blog_focus_keyword',   sanitize_text_field($data['focus_keyword'] ?? ''));
    $image_queries = array_map(fn($s) => sanitize_text_field($s['image_query'] ?? ''), (array)($data['sections'] ?? array()));
    update_post_meta($post_id, '_ds_blog_image_queries', array_filter($image_queries));
		if ( ! empty($data['featured_image_query']) ) {
    update_post_meta($post_id, '_ds_blog_featured_image_query', sanitize_text_field($data['featured_image_query']));
}
    if ( ! empty($data['tags']) ) wp_set_post_terms($post_id, array_map('sanitize_text_field', (array)$data['tags']), 'post_tag');
    update_option('ds_ai_blog_last_published', array('post_id' => $post_id, 'title' => $data['title'] ?? $topic, 'time' => time()));

    // Auto-generate images + references for cron posts
    do_action('ds_ai_blog_post_created', $post_id, $data);
}
});

// ═══════════════════════════════════════════════════════════════
// PART E — SEO meta output on front end
// ═══════════════════════════════════════════════════════════════

add_action('wp_head', function () {
    if ( ! isset($_GET['ds_post_id']) ) return;
    $pid = intval($_GET['ds_post_id']);
    if ( ! $pid || get_post_type($pid) !== 'ds_blog_post' ) return;

    $seo_title = get_post_meta($pid, '_ds_blog_seo_title', true);
    $seo_desc  = get_post_meta($pid, '_ds_blog_seo_description', true);
    $keyword   = get_post_meta($pid, '_ds_blog_focus_keyword', true);

    if ( $seo_title ) echo '<meta name="title" content="' . esc_attr($seo_title) . '">' . "\n";
    if ( $seo_desc )  echo '<meta name="description" content="' . esc_attr($seo_desc) . '">' . "\n";
    if ( $keyword )   echo '<meta name="keywords" content="' . esc_attr($keyword) . '">' . "\n";
    // Open Graph
    if ( $seo_title ) echo '<meta property="og:title" content="' . esc_attr($seo_title) . '">' . "\n";
    if ( $seo_desc )  echo '<meta property="og:description" content="' . esc_attr($seo_desc) . '">' . "\n";
});

// ═══════════════════════════════════════════════════════════════
// PART F — wp-admin UI: Manual generator meta box
// ═══════════════════════════════════════════════════════════════

add_action('add_meta_boxes', function () {
    add_meta_box(
        'ds_ai_blog_writer',
        '✨ AI Blog Writer (Groq)',
        'ds_ai_blog_meta_box',
        'ds_blog_post',
        'normal',
        'high'
    );
    add_meta_box(
        'ds_ai_blog_seo',
        '🔍 SEO Details',
        'ds_ai_blog_seo_box',
        'ds_blog_post',
        'normal',
        'default'
    );
});

if ( ! function_exists('ds_ai_blog_meta_box') ) {
function ds_ai_blog_meta_box( $post ) {
    $nonce     = wp_create_nonce('wp_rest');
    $gen_url   = rest_url('ds/v1/ai-blog-generate');
    $save_url  = rest_url('ds/v1/ai-blog-save');
    $topics    = get_terms(array('taxonomy' => 'ds_blog_topic', 'hide_empty' => false));
    $post_id   = $post->ID;
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');
    #ds-ai-writer-box * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
    #ds-ai-writer-box { padding: 4px 0; }
    .ds-ai-row { display: flex; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
    .ds-ai-row > * { flex: 1; min-width: 160px; }
    .ds-ai-label { display: block; font-size: 11px; font-weight: 700; color: #1B2A4A; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px; }
    .ds-ai-input, .ds-ai-select { width: 100%; padding: 9px 12px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #1a1a2e; background: #f9fafc; transition: border-color .15s; }
    .ds-ai-input:focus, .ds-ai-select:focus { outline: none; border-color: #E8481C; background: #fff; }
    .ds-ai-btn { padding: 10px 22px; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; transition: opacity .15s; }
    .ds-ai-btn-primary { background: linear-gradient(135deg, #E8481C, #c93a15); color: #fff; }
    .ds-ai-btn-secondary { background: #1B2A4A; color: #fff; }
    .ds-ai-btn-outline { background: #fff; color: #1B2A4A; border: 2px solid #C8D8F0; }
    .ds-ai-btn:hover { opacity: .88; }
    .ds-ai-btn:disabled { opacity: .4; cursor: default; }
    #ds-ai-status { font-size: 12px; color: #6b7280; margin: 10px 0; min-height: 20px; display: flex; align-items: center; gap: 8px; }
    #ds-ai-status.error { color: #dc2626; }
    #ds-ai-status.success { color: #16a34a; }
    .ds-ai-preview { border: 2px solid #e8edf5; border-radius: 10px; padding: 18px 20px; background: #fff; margin-top: 16px; display: none; }
    .ds-ai-preview h3 { margin: 0 0 12px; font-size: 14px; color: #1B2A4A; font-weight: 800; }
    .ds-ai-section-card { background: #f4f6fb; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; border-left: 3px solid #E8481C; }
    .ds-ai-section-card h4 { margin: 0 0 4px; font-size: 13px; color: #1B2A4A; font-weight: 700; }
    .ds-ai-section-card p { margin: 0; font-size: 11px; color: #6b7280; }
    .ds-ai-section-card .img-q { display: inline-block; margin-top: 6px; font-size: 10px; background: #1B2A4A; color: #C8D8F0; padding: 2px 8px; border-radius: 99px; }
    .ds-ai-seo-preview { background: #f0f7ff; border-radius: 8px; padding: 12px 14px; margin: 12px 0; font-size: 12px; }
    .ds-ai-seo-preview .seo-title { color: #1a0dab; font-weight: 700; font-size: 14px; }
    .ds-ai-seo-preview .seo-url { color: #006621; font-size: 11px; margin: 2px 0; }
    .ds-ai-seo-preview .seo-desc { color: #545454; }
    .ds-ai-divider { border: none; border-top: 2px solid #e8edf5; margin: 18px 0; }
    .ds-ai-tags { display: flex; gap: 5px; flex-wrap: wrap; margin: 8px 0; }
    .ds-ai-tag { background: #e8f0fc; color: #1B2A4A; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
    .ds-ai-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid #e8edf5; border-top-color: #E8481C; border-radius: 50%; animation: ds-spin .7s linear infinite; }
    @keyframes ds-spin { to { transform: rotate(360deg); } }
    </style>

    <div id="ds-ai-writer-box">
        <div class="ds-ai-row">
            <div style="flex:2;min-width:220px;">
                <label class="ds-ai-label">Topic / Title Idea</label>
                <input class="ds-ai-input" id="ds-ai-topic" type="text"
                    value="<?php echo esc_attr($post->post_title !== 'Auto Draft' ? $post->post_title : ''); ?>"
                    placeholder="e.g. Top 10 AI tools for designers in 2025">
            </div>
            <div>
                <label class="ds-ai-label">Tone</label>
                <select class="ds-ai-select" id="ds-ai-tone">
                    <option value="professional">Professional</option>
                    <option value="friendly">Friendly & casual</option>
                    <option value="educational">Educational</option>
                    <option value="persuasive">Persuasive / Sales</option>
                    <option value="inspiring">Inspiring</option>
                </select>
            </div>
            <div>
                <label class="ds-ai-label">Word Count</label>
                <select class="ds-ai-select" id="ds-ai-wc">
                    <option value="500">~500 words</option>
                    <option value="800" selected>~800 words</option>
                    <option value="1200">~1200 words</option>
                    <option value="1600">~1600 words</option>
                </select>
            </div>
            <div>
                <label class="ds-ai-label">Topic Category</label>
                <select class="ds-ai-select" id="ds-ai-topic-cat">
                    <option value="">— None —</option>
                    <?php if ( ! is_wp_error($topics) ) foreach ($topics as $t): ?>
                    <option value="<?php echo esc_attr($t->slug); ?>"><?php echo esc_html($t->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="ds-ai-btn ds-ai-btn-primary" id="ds-ai-gen-btn">✨ Generate with AI</button>
            <button class="ds-ai-btn ds-ai-btn-outline" id="ds-ai-regen-btn" style="display:none;">🔄 Regenerate</button>
        </div>

        <div id="ds-ai-status"></div>

        <!-- Preview panel -->
        <div class="ds-ai-preview" id="ds-ai-preview">
            <h3>📝 Generated Post Preview</h3>

            <!-- SEO Google preview -->
            <div class="ds-ai-seo-preview">
                <div class="seo-title" id="ds-ai-prev-seo-title"></div>
                <div class="seo-url"><?php echo esc_html(home_url('/ds-blog/')); ?><span id="ds-ai-prev-slug"></span></div>
                <div class="seo-desc" id="ds-ai-prev-seo-desc"></div>
            </div>

            <div style="font-size:11px;color:#6b7280;margin-bottom:8px;">
                🔑 Focus keyword: <strong id="ds-ai-prev-keyword" style="color:#1B2A4A;"></strong>
            </div>

            <!-- Tags -->
            <div class="ds-ai-tags" id="ds-ai-prev-tags"></div>

            <hr class="ds-ai-divider">

            <!-- Sections -->
            <div style="font-size:12px;font-weight:700;color:#1B2A4A;margin-bottom:8px;">📚 Sections & Image Queries</div>
            <div id="ds-ai-prev-sections"></div>

            <hr class="ds-ai-divider">

            <!-- Save actions -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:4px;">
                <button class="ds-ai-btn ds-ai-btn-secondary" id="ds-ai-save-draft">💾 Save as Draft</button>
                <button class="ds-ai-btn ds-ai-btn-primary" id="ds-ai-publish">🚀 Save & Publish</button>
            </div>
            <div id="ds-ai-save-status" style="font-size:12px;color:#16a34a;margin-top:8px;"></div>
        </div>
    </div>

    <script>
    (function(){
    var nonce   = <?php echo wp_json_encode($nonce); ?>;
    var genUrl  = <?php echo wp_json_encode($gen_url); ?>;
    var saveUrl = <?php echo wp_json_encode($save_url); ?>;
    var postId  = <?php echo intval($post_id); ?>;
    var _data   = null;

    function status(msg, type) {
        var el = document.getElementById('ds-ai-status');
        el.className = type || '';
        el.innerHTML = msg;
    }

    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function slugify(s) { return String(s).toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); }

    document.getElementById('ds-ai-gen-btn').onclick = generate;
    document.getElementById('ds-ai-regen-btn').onclick = generate;

    function generate() {
        var topic = document.getElementById('ds-ai-topic').value.trim();
        if (!topic) { status('⚠️ Please enter a topic first.', 'error'); return; }

        var btn = document.getElementById('ds-ai-gen-btn');
        btn.disabled = true;
        document.getElementById('ds-ai-preview').style.display = 'none';
        status('<span class="ds-ai-spinner"></span> Writing your blog post with Groq AI…');

        fetch(genUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({
                topic:      topic,
                tone:       document.getElementById('ds-ai-tone').value,
                word_count: document.getElementById('ds-ai-wc').value,
            })
        })
        .then(function(r){ return r.json(); })
        .then(function(r) {
            btn.disabled = false;
            if (!r.success) { status('❌ ' + (r.error || 'Unknown error'), 'error'); return; }
            _data = r.data;
            renderPreview(_data);
            status('✅ Blog post generated! Review below, then save.', 'success');
            document.getElementById('ds-ai-regen-btn').style.display = '';
        })
        .catch(function(e) {
            btn.disabled = false;
            status('❌ Network error: ' + e.message, 'error');
        });
    }

    function renderPreview(d) {
        document.getElementById('ds-ai-prev-seo-title').textContent = d.seo_title || d.title || '';
        document.getElementById('ds-ai-prev-slug').textContent      = slugify(d.title || '');
        document.getElementById('ds-ai-prev-seo-desc').textContent  = d.seo_description || '';
        document.getElementById('ds-ai-prev-keyword').textContent   = d.focus_keyword || '';

        // Tags
        var tagsEl = document.getElementById('ds-ai-prev-tags');
        tagsEl.innerHTML = (d.tags||[]).map(function(t){ return '<span class="ds-ai-tag">#' + esc(t) + '</span>'; }).join('');

        // Sections
        var secEl = document.getElementById('ds-ai-prev-sections');
        secEl.innerHTML = (d.sections||[]).map(function(s,i){
            return '<div class="ds-ai-section-card">'
                + '<h4>' + esc(s.heading) + '</h4>'
                + '<p>' + esc((s.body||'').replace(/<[^>]+>/g,'').substring(0,120)) + '…</p>'
                + (s.image_query ? '<span class="img-q">🖼 ' + esc(s.image_query) + '</span>' : '')
                + '</div>';
        }).join('');

        // Update page title field
        var titleInput = document.querySelector('#titlewrap #title');
        if (titleInput && d.title) titleInput.value = d.title;

        document.getElementById('ds-ai-preview').style.display = 'block';
        document.getElementById('ds-ai-preview').scrollIntoView({behavior:'smooth', block:'nearest'});
    }

    function savePost(status_val) {
        if (!_data) return;
        var saveStatus = document.getElementById('ds-ai-save-status');
        saveStatus.textContent = '⏳ Saving…';

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({
                post_id:    postId,
                data:       _data,
                status:     status_val,
                topic_slug: document.getElementById('ds-ai-topic-cat').value,
            })
        })
        .then(function(r){ return r.json(); })
        .then(function(r) {
            if (r.success) {
                postId = r.post_id;
                saveStatus.textContent = '✅ Saved! ';
                if (r.edit_url) {
                    saveStatus.innerHTML += '<a href="' + r.edit_url + '" style="color:#E8481C;font-weight:700;">Open post editor →</a>';
                }
            } else {
                saveStatus.textContent = '❌ ' + (r.error || 'Save failed.');
                saveStatus.style.color = '#dc2626';
            }
        })
        .catch(function(){ saveStatus.textContent = '❌ Network error.'; saveStatus.style.color = '#dc2626'; });
    }

    document.getElementById('ds-ai-save-draft').onclick = function(){ savePost('draft'); };
    document.getElementById('ds-ai-publish').onclick    = function(){ savePost('publish'); };

    })();
    </script>
    <?php
}
}

if ( ! function_exists('ds_ai_blog_seo_box') ) {
function ds_ai_blog_seo_box( $post ) {
    $seo_title = get_post_meta($post->ID, '_ds_blog_seo_title', true);
    $seo_desc  = get_post_meta($post->ID, '_ds_blog_seo_description', true);
    $keyword   = get_post_meta($post->ID, '_ds_blog_focus_keyword', true);
    $queries   = get_post_meta($post->ID, '_ds_blog_image_queries', true) ?: array();
    wp_nonce_field('ds_ai_seo_nonce', 'ds_ai_seo_nonce_field');
    ?>
    <style>
    .ds-seo-box label { display:block; font-size:11px; font-weight:700; color:#1B2A4A; margin:10px 0 4px; text-transform:uppercase; letter-spacing:.4px; }
    .ds-seo-box input, .ds-seo-box textarea { width:100%; padding:8px 12px; border:2px solid #e8edf5; border-radius:7px; font-size:13px; font-family:'Poppins',sans-serif; }
    .ds-seo-box input:focus, .ds-seo-box textarea:focus { outline:none; border-color:#E8481C; }
    .ds-seo-char { font-size:10px; color:#9ca3af; float:right; margin-top:-2px; }
    </style>
    <div class="ds-seo-box">
        <label>SEO Title <span class="ds-seo-char" id="ds-seo-title-len"><?php echo strlen($seo_title); ?>/60</span></label>
        <input type="text" name="ds_blog_seo_title" id="ds-seo-title-inp" value="<?php echo esc_attr($seo_title); ?>" maxlength="70"
            oninput="document.getElementById('ds-seo-title-len').textContent=this.value.length+'/60'">

        <label>SEO Description <span class="ds-seo-char" id="ds-seo-desc-len"><?php echo strlen($seo_desc); ?>/155</span></label>
        <textarea name="ds_blog_seo_description" rows="2" maxlength="170"
            oninput="document.getElementById('ds-seo-desc-len').textContent=this.value.length+'/155'"><?php echo esc_textarea($seo_desc); ?></textarea>

        <label>Focus Keyword</label>
        <input type="text" name="ds_blog_focus_keyword" value="<?php echo esc_attr($keyword); ?>" placeholder="e.g. AI design tools">

        <?php if ($queries): ?>
        <label>Image Queries (per section)</label>
        <p style="font-size:12px;color:#6b7280;margin:4px 0 8px;"><?php echo esc_html(implode(' · ', $queries)); ?></p>
        <?php endif; ?>
    </div>
    <?php
}
}

add_action('save_post_ds_blog_post', function ($post_id) {
    if (!isset($_POST['ds_ai_seo_nonce_field']) || !wp_verify_nonce($_POST['ds_ai_seo_nonce_field'], 'ds_ai_seo_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    update_post_meta($post_id, '_ds_blog_seo_title',       sanitize_text_field($_POST['ds_blog_seo_title'] ?? ''));
    update_post_meta($post_id, '_ds_blog_seo_description', sanitize_textarea_field($_POST['ds_blog_seo_description'] ?? ''));
    update_post_meta($post_id, '_ds_blog_focus_keyword',   sanitize_text_field($_POST['ds_blog_focus_keyword'] ?? ''));
}, 20);

// ═══════════════════════════════════════════════════════════════
// PART G — wp-admin page: Auto-Schedule manager
// ═══════════════════════════════════════════════════════════════

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=ds_blog_post',
        'AI Auto-Schedule',
        '🤖 AI Auto-Schedule',
        'manage_options',
        'ds-ai-blog-schedule',
        'ds_ai_blog_schedule_page'
    );
});

if ( ! function_exists('ds_ai_blog_schedule_page') ) {
function ds_ai_blog_schedule_page() {
    $nonce    = wp_create_nonce('wp_rest');
    $sch_url  = rest_url('ds/v1/ai-blog-schedule');
    $next_run = wp_next_scheduled('ds_ai_blog_cron');
    $last_pub = get_option('ds_ai_blog_last_published', null);
    $last_err = get_option('ds_ai_blog_last_error', '');
    $queue    = get_option('ds_ai_blog_queue', array());
    $topics   = get_option('ds_ai_blog_topics', array());
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
    #ds-sched-wrap * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
    #ds-sched-wrap { max-width: 760px; padding: 24px 0; }
    #ds-sched-wrap h1 { font-size: 22px; font-weight: 800; color: #1B2A4A; margin: 0 0 6px; }
    #ds-sched-wrap h1 span { color: #E8481C; }
    .ds-sched-card { background: #fff; border-radius: 14px; padding: 24px 26px; margin-bottom: 20px; box-shadow: 0 2px 14px rgba(27,42,74,.08); border: 1px solid #e8edf5; }
    .ds-sched-card h2 { font-size: 14px; font-weight: 800; color: #1B2A4A; margin: 0 0 16px; padding-bottom: 10px; border-bottom: 2px solid #e8edf5; }
    .ds-sched-label { display: block; font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px; }
    .ds-sched-input, .ds-sched-select, .ds-sched-textarea { width: 100%; padding: 10px 13px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #1a1a2e; background: #f9fafc; margin-bottom: 14px; }
    .ds-sched-textarea { resize: vertical; min-height: 120px; }
    .ds-sched-input:focus, .ds-sched-textarea:focus, .ds-sched-select:focus { outline: none; border-color: #E8481C; background: #fff; }
    .ds-sched-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .ds-sched-btn { padding: 11px 24px; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .ds-sched-btn-primary { background: linear-gradient(135deg, #E8481C, #c93a15); color: #fff; }
    .ds-sched-btn:hover { opacity: .88; }
    .ds-sched-btn:disabled { opacity: .4; }
    .ds-sched-status { background: #f4f6fb; border-radius: 8px; padding: 12px 16px; font-size: 12px; color: #374151; margin-top: 14px; display: none; }
    .ds-sched-status.show { display: block; }
    .ds-stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .ds-stat-card { background: #f4f6fb; border-radius: 10px; padding: 14px 16px; text-align: center; }
    .ds-stat-card .num { font-size: 24px; font-weight: 900; color: #E8481C; }
    .ds-stat-card .lbl { font-size: 11px; color: #6b7280; font-weight: 600; margin-top: 2px; }
    .ds-queue-list { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; max-height: 180px; overflow-y: auto; }
    .ds-queue-item { background: #f4f6fb; border-radius: 6px; padding: 7px 12px; font-size: 12px; color: #1B2A4A; display: flex; align-items: center; gap: 8px; }
    .ds-queue-item::before { content: '📝'; }
    </style>

    <div id="ds-sched-wrap">
        <h1>🤖 AI <span>Auto-Schedule</span></h1>
        <p style="color:#6b7280;font-size:13px;margin:0 0 20px;">Set up topics and a schedule — Groq AI will write and publish blog posts automatically.</p>

        <!-- Stats -->
        <div class="ds-sched-card">
            <h2>📊 Current Status</h2>
            <div class="ds-stat-grid">
                <div class="ds-stat-card">
                    <div class="num"><?php echo count($topics); ?></div>
                    <div class="lbl">Total Topics</div>
                </div>
                <div class="ds-stat-card">
                    <div class="num"><?php echo count($queue); ?></div>
                    <div class="lbl">Remaining in Queue</div>
                </div>
                <div class="ds-stat-card">
                    <div class="num"><?php echo $next_run ? human_time_diff($next_run) : '—'; ?></div>
                    <div class="lbl">Next Run<?php echo $next_run ? ' (in)' : ' (not scheduled)'; ?></div>
                </div>
            </div>
            <?php if ($last_pub): ?>
            <p style="font-size:12px;color:#16a34a;margin:12px 0 0;">✅ Last published: <strong><?php echo esc_html($last_pub['title']); ?></strong> — <?php echo human_time_diff($last_pub['time']); ?> ago
                <a href="<?php echo esc_url(get_edit_post_link($last_pub['post_id'])); ?>" style="color:#E8481C;margin-left:8px;">Edit →</a>
            </p>
            <?php endif; ?>
            <?php if ($last_err): ?>
            <p style="font-size:12px;color:#dc2626;margin:8px 0 0;">⚠️ Last error: <?php echo esc_html($last_err); ?></p>
            <?php endif; ?>

            <?php if (!empty($queue)): ?>
            <div style="margin-top:14px;font-size:12px;font-weight:700;color:#1B2A4A;">Upcoming topics in queue:</div>
            <div class="ds-queue-list">
                <?php foreach (array_slice($queue, 0, 8) as $q_topic): ?>
                <div class="ds-queue-item"><?php echo esc_html($q_topic); ?></div>
                <?php endforeach; ?>
                <?php if (count($queue) > 8): ?>
                <div class="ds-queue-item" style="color:#9ca3af;">+ <?php echo count($queue) - 8; ?> more…</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Schedule form -->
        <div class="ds-sched-card">
            <h2>⚙️ Configure Auto-Schedule</h2>
            <label class="ds-sched-label">Topics (one per line) <span style="color:#E8481C">*</span></label>
            <textarea class="ds-sched-textarea" id="ds-sched-topics" placeholder="Top 10 AI tools for designers&#10;How to make a pitch deck in 5 minutes&#10;Best presentation templates 2025&#10;How to use AI for marketing presentations"><?php echo esc_textarea(implode("\n", $topics)); ?></textarea>

            <div class="ds-sched-row">
                <div>
                    <label class="ds-sched-label">Publish Frequency</label>
                    <select class="ds-sched-select" id="ds-sched-freq">
                        <option value="daily" <?php selected(get_option('ds_ai_blog_frequency'), 'daily'); ?>>Every Day</option>
                        <option value="twiceweekly" <?php selected(get_option('ds_ai_blog_frequency'), 'twiceweekly'); ?>>Twice a Week</option>
                        <option value="weekly" <?php selected(get_option('ds_ai_blog_frequency'), 'weekly'); ?>>Once a Week</option>
                    </select>
                </div>
                <div>
                    <label class="ds-sched-label">Post Status</label>
                    <select class="ds-sched-select" id="ds-sched-status">
                        <option value="publish" <?php selected(get_option('ds_ai_blog_status'), 'publish'); ?>>Publish immediately</option>
                        <option value="draft" <?php selected(get_option('ds_ai_blog_status'), 'draft'); ?>>Save as draft (review first)</option>
                    </select>
                </div>
            </div>
            <div class="ds-sched-row">
                <div>
                    <label class="ds-sched-label">Writing Tone</label>
                    <select class="ds-sched-select" id="ds-sched-tone">
                        <option value="professional" <?php selected(get_option('ds_ai_blog_tone'), 'professional'); ?>>Professional</option>
                        <option value="friendly" <?php selected(get_option('ds_ai_blog_tone'), 'friendly'); ?>>Friendly & Casual</option>
                        <option value="educational" <?php selected(get_option('ds_ai_blog_tone'), 'educational'); ?>>Educational</option>
                        <option value="persuasive" <?php selected(get_option('ds_ai_blog_tone'), 'persuasive'); ?>>Persuasive / Sales</option>
                    </select>
                </div>
                <div>
                    <label class="ds-sched-label">Word Count per Post</label>
                    <select class="ds-sched-select" id="ds-sched-wc">
                        <option value="500" <?php selected(get_option('ds_ai_blog_word_count'), '500'); ?>>~500 words</option>
                        <option value="800" <?php selected(get_option('ds_ai_blog_word_count', 800), '800'); ?>>~800 words</option>
                        <option value="1200" <?php selected(get_option('ds_ai_blog_word_count'), '1200'); ?>>~1200 words</option>
                    </select>
                </div>
            </div>

            <button class="ds-sched-btn ds-sched-btn-primary" id="ds-sched-save">🚀 Save & Activate Schedule</button>
            <div class="ds-sched-status" id="ds-sched-result"></div>
        </div>
    </div>

    <script>
    document.getElementById('ds-sched-save').onclick = function() {
        var btn = this;
        var topics = document.getElementById('ds-sched-topics').value.trim();
        if (!topics) { alert('Please enter at least one topic.'); return; }
        btn.disabled = true;
        btn.textContent = '⏳ Saving…';

        fetch(<?php echo wp_json_encode($sch_url); ?>, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': <?php echo wp_json_encode($nonce); ?> },
            body: JSON.stringify({
                topics:      topics.split('\n').map(function(t){ return t.trim(); }).filter(Boolean),
                frequency:   document.getElementById('ds-sched-freq').value,
                auto_status: document.getElementById('ds-sched-status').value,
                tone:        document.getElementById('ds-sched-tone').value,
                word_count:  document.getElementById('ds-sched-wc').value,
            })
        })
        .then(function(r){ return r.json(); })
        .then(function(r) {
            btn.disabled = false;
            btn.textContent = '🚀 Save & Activate Schedule';
            var res = document.getElementById('ds-sched-result');
            res.classList.add('show');
            res.style.color = r.success ? '#16a34a' : '#dc2626';
            res.textContent = r.success ? '✅ ' + r.message + ' Page will refresh in 3s…' : '❌ ' + (r.error || 'Error saving.');
            if (r.success) setTimeout(function(){ location.reload(); }, 3000);
        })
        .catch(function(e) {
            btn.disabled = false;
            btn.textContent = '🚀 Save & Activate Schedule';
            var res = document.getElementById('ds-sched-result');
            res.classList.add('show');
            res.style.color = '#dc2626';
            res.textContent = '❌ Network error: ' + e.message;
        });
    };
    </script>
    <?php
}
}
