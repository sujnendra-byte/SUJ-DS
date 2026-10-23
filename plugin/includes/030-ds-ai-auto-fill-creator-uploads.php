<?php
/**
 * Code Snippets export — DS AI Auto-Fill Creator Uploads
 *
 * Snippet ID : 716
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-030
 * Modified   : 2026-09-09 02:42:23
 * Load order : 030 of 144
 */
/**
 * Snippet: DS AI Auto-Fill Creator Uploads (v5 - GROQ PRIMARY)
 * Run: Everywhere
 * Description: Adds " Auto-Fill with AI" button to Creator Uploads approval popup,
 * AND powers the same "Auto-Fill with AI" button on the DLM product edit screen
 * (Snippet 5 dlm product ai fill) — both call this same wp_ajax_ds_ai_analyze_image
 * handler, so there is only one place the AI call itself lives.
 * Uses admin-ajax.php instead of REST API to avoid permission issues.
 *
 * Provider order: Groq is called FIRST (fast, reliable, no more "Gemini is
 * overloaded" errors). Gemini is kept only as an automatic silent fallback in
 * case Groq itself has a bad moment — so Auto-Fill doesn't go down completely
 * if one provider has an outage. Requires a Groq API key (already configured
 * via ds_get_api_keys()) and optionally a Gemini key for the fallback.
 */

// ========== CONFIGURATION ==========
define('GOOGLE_GEMINI_API_KEY', function_exists('ds_get_key') ? ds_get_key('gemini') : '');
// Get from: https://makersuite.google.com/app/apikey

// Detects whether the creator's upload is a still image, a video/motion file,
// or a PowerPoint file — based on the real file's MIME type, not a guess.
// This drives which prompt wording is used and which top-level category
// the upload is assigned to, so it's never left to chance.
function ds_detect_asset_type($file_attachment_id) {
    if (!$file_attachment_id) return 'images';
    $mime = get_post_mime_type($file_attachment_id);
    if (!$mime) return 'images';
    if (strpos($mime, 'audio/') === 0) return 'audio';
    if (strpos($mime, 'video/') === 0) return 'motion_graphics';
    if ($mime === 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        || $mime === 'application/vnd.ms-powerpoint') return 'powerpoint';
    return 'images';
}

// Downscales raw image bytes to a bounded JPEG before it's sent to any AI
// vision API. WP core sub-sizes are disabled site-wide, so the "large"/"full"
// URL now points at the untouched original upload, which can be tens of MB —
// both Groq and Gemini reject an inline image that size with HTTP 413.
// Always re-encodes (even if already small) so a huge lossless PNG doesn't
// slip through as-is. Falls back to the original bytes untouched if GD is
// unavailable or the data can't be decoded as an image.
function ds_ai_downscale_for_analysis($body, $max_dimension = 1200, $quality = 82) {
    if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
        return array($body, null);
    }
    if (function_exists('wp_raise_memory_limit')) {
        wp_raise_memory_limit('image');
    }
    $im = @imagecreatefromstring($body);
    if (!$im) {
        return array($body, null);
    }
    $width = imagesx($im);
    $height = imagesy($im);
    if ($width < 1 || $height < 1) {
        imagedestroy($im);
        return array($body, null);
    }
    $ratio = min($max_dimension / $width, $max_dimension / $height, 1);
    $new_w = max(1, (int) round($width * $ratio));
    $new_h = max(1, (int) round($height * $ratio));

    $resized = imagecreatetruecolor($new_w, $new_h);
    // Flatten any transparency onto white — JPEG has no alpha channel.
    $white = imagecolorallocate($resized, 255, 255, 255);
    imagefill($resized, 0, 0, $white);
    imagecopyresampled($resized, $im, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
    imagedestroy($im);

    ob_start();
    imagejpeg($resized, null, $quality);
    $data = ob_get_clean();
    imagedestroy($resized);

    if (empty($data)) {
        return array($body, null);
    }
    return array($data, 'image/jpeg');
}

// ========== AJAX handler - Get Image URL ==========
add_action('wp_ajax_ds_ai_get_image_url', 'ds_ai_get_image_url_handler');

function ds_ai_get_image_url_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Not allowed');
    }
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    if (!$post_id) {
        wp_send_json_error('No post ID');
    }

    $file_id = (int)get_post_meta($post_id, 'ds_creator_file_id', true);
    $asset_type = ds_detect_asset_type($file_id);

    // Resolve the matching real top-level category term ID for this asset type,
    // so the dropdown can be set deterministically — never guessed by the AI.
    $category_id = 0;
    $category_name_map = array(
        'images' => 'Images',
        'motion_graphics' => 'Motion Graphics',
        'powerpoint' => 'PowerPoint Templates',
        'audio' => 'Audio',
    );
    $target_name = $category_name_map[$asset_type] ?? 'Images';
    foreach (ds_get_category_tree() as $cat_id => $cat) {
        if ($cat['name'] === $target_name) { $category_id = $cat_id; break; }
    }

    // Audio has no visual thumbnail or "image" representation at all -- skip the
    // image lookup entirely and hand back the track's own prompt/title text instead,
    // which Step 2 sends to the AI in text-only mode.
    if ($asset_type === 'audio') {
        $prompt_text = (string) get_post_meta($file_id, 'ds_ai_prompt', true);
        $model_text = (string) get_post_meta($file_id, 'ds_ai_model', true);
        $title_text = get_the_title($post_id);
        $text_context = trim(
            'Track title: ' . $title_text . '. ' .
            ($prompt_text ? 'Original generation prompt: "' . $prompt_text . '". ' : '') .
            ($model_text ? 'Generated with model: ' . $model_text . '.' : '')
        );
        if (!$text_context) {
            $text_context = 'AI-generated audio track titled "' . $title_text . '".';
        }
        wp_send_json_success(array('text_context' => $text_context, 'asset_type' => 'audio', 'category_id' => $category_id));
    }

    // Get thumbnail attachment ID — wp_get_attachment_image_url() only works for
    // actual images (no registered "image size" exists for video files), so a
    // video thumbnail needs the raw file URL instead.
    $thumb_id = (int)get_post_meta($post_id, 'ds_creator_thumbnail_id', true);
    if ($thumb_id) {
        $thumb_mime = get_post_mime_type($thumb_id);
        $thumb_url = (strpos($thumb_mime, 'video/') === 0) ? wp_get_attachment_url($thumb_id) : wp_get_attachment_image_url($thumb_id, 'full');
        if ($thumb_url) {
            wp_send_json_success(array('image_url' => $thumb_url, 'asset_type' => $asset_type, 'category_id' => $category_id));
        }
    }

    // Fallback: try file attachment
    if ($file_id) {
        $file_mime = get_post_mime_type($file_id);
        $file_url = (strpos($file_mime, 'video/') === 0) ? wp_get_attachment_url($file_id) : wp_get_attachment_image_url($file_id, 'full');
        if ($file_url) {
            wp_send_json_success(array('image_url' => $file_url, 'asset_type' => $asset_type, 'category_id' => $category_id));
        }
    }

    wp_send_json_error('No image found');
}

// ========== AJAX handler - Analyze (Groq primary, Gemini fallback) ==========
// Shared by BOTH Creator Uploads approval popup AND the DLM product edit
// screen's "Auto-Fill with AI" button — both post to action=ds_ai_analyze_image.
add_action('wp_ajax_ds_ai_analyze_image', 'ds_ai_analyze_image_handler');

function ds_ai_analyze_image_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Not allowed');
    }
    $image_url = isset($_POST['image_url']) ? sanitize_text_field($_POST['image_url']) : '';
    $text_context = isset($_POST['text_context']) ? sanitize_textarea_field($_POST['text_context']) : '';
    $asset_type = isset($_POST['asset_type']) ? sanitize_text_field($_POST['asset_type']) : 'images';
    if (!in_array($asset_type, array('images', 'motion_graphics', 'powerpoint', 'audio'), true)) {
        $asset_type = 'images';
    }

    $image_base64 = '';
    $mime_type = '';

    if ($asset_type === 'audio') {
        // Audio has no visual to fetch/analyze — the AI is called in text-only mode
        // using the track's own prompt/title text instead of image bytes.
        if (!$text_context) {
            wp_send_json_error('Missing track information');
        }
    } else {
        if (!$image_url || !filter_var($image_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error('Invalid image URL');
        }

        // Fetch image
        $response = wp_remote_get($image_url, array('sslverify' => true, 'timeout' => 30));
        if (is_wp_error($response)) {
            wp_send_json_error('Could not fetch image: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            wp_send_json_error('Image body is empty');
        }

        // Downscale before sending to any AI provider. WP core sub-sizes are
        // disabled site-wide (see the marketplace media pipeline snippet), so
        // "large"/"full" attachment URLs now point at the untouched original —
        // creator uploads can be 50MB+. Both Groq and Gemini reject an inline
        // image that big with HTTP 413, which is what was showing up as
        // constant Auto-Fill errors. Always re-encode to a bounded JPEG.
        list($resized_body, $resized_mime) = ds_ai_downscale_for_analysis($body);
        $image_base64 = base64_encode($resized_body);
        $mime_type = $resized_mime ?: (wp_remote_retrieve_header($response, 'content-type') ?: 'image/jpeg');
    }

    // Call Groq FIRST — it's the reliable provider now. Gemini is only tried
    // as a silent automatic fallback if Groq itself fails for any reason
    // (missing key, rate limit, bad response, etc.), so Auto-Fill still works
    // even if one provider has a bad moment.
    $result = ds_call_groq_autofill($image_base64, $mime_type, $asset_type, $text_context);

    if (!$result['success']) {
        $fallback = ds_call_gemini_api($image_base64, $mime_type, $asset_type, $text_context);
        if ($fallback['success']) {
            $result = $fallback;
        } else {
            // Keep whichever error message is more informative; prefer the
            // original Groq error since Groq is the primary provider.
            error_log('DS AI Auto-Fill: both providers failed. Groq: ' . $result['error'] . ' | Gemini: ' . $fallback['error']);
        }
    }

    if (!$result['success']) {
        wp_send_json_error($result['error']);
    }

    wp_send_json_success($result['data']);
}

function ds_sanitize_json_text($json_text) {
    $result = '';
    $in_string = false;
    $escaped = false;
    $len = strlen($json_text);
    for ($i = 0; $i < $len; $i++) {
        $ch = $json_text[$i];
        if ($in_string) {
            if ($escaped) { $result .= $ch; $escaped = false; continue; }
            if ($ch === '\\') { $result .= $ch; $escaped = true; continue; }
            if ($ch === '"') { $in_string = false; $result .= $ch; continue; }
            if ($ch === "\n") { $result .= '\n'; continue; }
            if ($ch === "\r") { $result .= '\r'; continue; }
            if ($ch === "\t") { $result .= '\t'; continue; }
            $result .= $ch;
        } else {
            if ($ch === '"') { $in_string = true; }
            $result .= $ch;
        }
    }
    return $result;
}

// Builds [category_term_id => ['name'=>.., 'subs'=>[sub_id=>sub_name,...]]] from the real
// dlm_download_category taxonomy under "Marketplace". Always live — add/rename a term in
// wp-admin and this picks it up automatically, no code change needed.
if (!function_exists('ds_get_category_tree')) {
    function ds_get_category_tree() {
        $tree = array();
        $marketplace = get_term_by('name', 'Marketplace', 'dlm_download_category');
        if (!$marketplace || is_wp_error($marketplace)) return $tree;

        $cats = get_terms(array(
            'taxonomy' => 'dlm_download_category',
            'hide_empty' => false,
            'parent' => $marketplace->term_id,
        ));
        if (is_wp_error($cats)) return $tree;

        foreach ($cats as $cat) {
            $subs = get_terms(array(
                'taxonomy' => 'dlm_download_category',
                'hide_empty' => false,
                'parent' => $cat->term_id,
            ));
            $sub_list = array();
            if (!is_wp_error($subs)) {
                foreach ($subs as $sub) {
                    $sub_list[$sub->term_id] = wp_specialchars_decode($sub->name, ENT_QUOTES);
                }
            }
            $tree[$cat->term_id] = array('name' => wp_specialchars_decode($cat->name, ENT_QUOTES), 'subs' => $sub_list);
        }
        // "Audio" is its own top-level term (a sibling of "Marketplace"), not nested
        // under it, so it has to be merged in separately.
        $audio_term = get_term_by('name', 'Audio', 'dlm_download_category');
        if ($audio_term && !is_wp_error($audio_term) && !$audio_term->parent) {
            $audio_subs = get_terms(array(
                'taxonomy' => 'dlm_download_category',
                'hide_empty' => false,
                'parent' => $audio_term->term_id,
            ));
            $audio_sub_list = array();
            if (!is_wp_error($audio_subs)) {
                foreach ($audio_subs as $sub) {
                    $audio_sub_list[$sub->term_id] = wp_specialchars_decode($sub->name, ENT_QUOTES);
                }
            }
            $tree[$audio_term->term_id] = array('name' => wp_specialchars_decode($audio_term->name, ENT_QUOTES), 'subs' => $audio_sub_list);
        }

        return $tree;
    }
}

// ========== Groq API Call (PRIMARY provider) ==========
// Handles both vision (images/motion_graphics/powerpoint) and text-only (audio) modes.
function ds_call_groq_autofill($image_base64, $mime_type, $asset_type = 'images', $text_context = '') {
    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $api_key = isset($keys['groq']) ? $keys['groq'] : '';
    if ( empty($api_key) || strpos($api_key, 'YOUR_') === 0 ) {
        return array('success' => false, 'error' => 'Groq API key not configured.');
    }

    $category_name_map = array(
        'images' => 'Images',
        'motion_graphics' => 'Motion Graphics',
        'powerpoint' => 'PowerPoint Templates',
        'audio' => 'Audio',
    );
    $target_category_name = $category_name_map[$asset_type] ?? 'Images';
    $category_tree = ds_get_category_tree();
    $sub_names = array();
    foreach ($category_tree as $cat) {
        if ($cat['name'] === $target_category_name) {
            $sub_names = array_values($cat['subs']);
            break;
        }
    }
    $subcategory_list_text = $sub_names ? implode(', ', $sub_names) : '(none available — return an empty string)';

    $json_shape = '{"title":"...","subcategory":"...","short_description":"...","meta_description":"...","full_description":"...","keywords":"...","tags":"..."}';

    if ($asset_type === 'audio') {
        // Audio: no visual to fetch/analyze — Groq is called in plain text-only mode
        // using the track's own prompt/title text instead of image bytes.
        if (!$text_context) {
            return array('success' => false, 'error' => 'Missing track information');
        }

        $prompt = "You are an SEO expert for a design marketplace. Write marketplace listing copy for this AI-generated AUDIO TRACK using ONLY the following information about it (no image is available): {$text_context}. Describe the sound, musical style, mood, and likely use case using words like \"track\", \"audio\", \"music\", \"sound\", \"loop\" — never visual language (never say image, photo, picture, shows, depicts). Return ONLY valid JSON (no markdown, no extra text) in this exact shape:
{$json_shape}

Rules:
- title: 8-11 words, 55+ characters, starts with the 2-3 word keyphrase describing the track
- short_description: one sentence, 22-32 words
- meta_description: 150-156 characters total
- full_description: 3-5 sentences of plain HTML using <p> tags
- keywords: 6-8 comma-separated phrases, keyphrase first
- tags: 10-15 comma-separated tags
- subcategory: pick EXACTLY one option, copied exactly, from this list: {$subcategory_list_text}";

        $payload = array(
            'model' => 'llama-3.3-70b-versatile',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt,
                ),
            ),
            'temperature' => 0.4,
            'max_tokens' => 2048,
        );
    } else {
        // Groq's vision models accept base64 images inline.
        $prompt = "You are an SEO expert for a design marketplace. Analyze the image and return ONLY valid JSON (no markdown, no extra text) in this exact shape:
{$json_shape}

Rules:
- title: 8-11 words, 55+ characters, starts with the 2-3 word keyphrase describing the image
- short_description: one sentence, 22-32 words
- meta_description: 150-156 characters total
- full_description: 3-5 sentences of plain HTML using <p> tags
- keywords: 6-8 comma-separated phrases, keyphrase first
- tags: 10-15 comma-separated tags
- subcategory: pick EXACTLY one option, copied exactly, from this list: {$subcategory_list_text}, for this " . $asset_type . " asset";

        $payload = array(
            'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array(
                            'type' => 'image_url',
                            'image_url' => array(
                                'url' => 'data:' . $mime_type . ';base64,' . $image_base64,
                            ),
                        ),
                        array(
                            'type' => 'text',
                            'text' => $prompt,
                        ),
                    ),
                ),
            ),
            'temperature' => 0.4,
            'max_tokens' => 2048,
        );
    }

    $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => wp_json_encode($payload),
        'timeout' => 45,
    ));

    if (is_wp_error($response)) {
        return array('success' => false, 'error' => 'Groq error: ' . $response->get_error_message());
    }

    $status = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($status !== 200 || empty($data['choices'][0]['message']['content'])) {
        $msg = isset($data['error']['message']) ? $data['error']['message'] : substr($body, 0, 200);
        error_log('Groq Auto-Fill API error (HTTP ' . $status . '): ' . $msg);
        return array('success' => false, 'error' => 'Groq error (HTTP ' . $status . '): ' . $msg);
    }

    $text = $data['choices'][0]['message']['content'];
    $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
    $text = preg_replace('/\s*```$/', '', $text);

    if (!preg_match('/\{[\s\S]*\}/', $text, $matches)) {
        return array('success' => false, 'error' => 'Groq returned no JSON');
    }

    $analysis = json_decode(ds_sanitize_json_text($matches[0]), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return array('success' => false, 'error' => 'Groq JSON parse error: ' . json_last_error_msg());
    }

    return array('success' => true, 'data' => $analysis);
}

// ========== Google Gemini API Call (FALLBACK provider only) ==========
function ds_call_gemini_api($image_base64, $mime_type, $asset_type = 'images', $text_context = '') {
    $api_key = GOOGLE_GEMINI_API_KEY;

    if ($api_key === 'YOUR_API_KEY_HERE' || empty($api_key)) {
        return array('success' => false, 'error' => 'API key not configured');
    }

    // gemini-1.5-flash was fully shut down (returns 404 on every call as of mid-2026).
    // gemini-2.5-flash-lite is the current stable, budget multimodal model for this kind of
    // lightweight image -> structured-data extraction task.
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . urlencode($api_key);

    // Category is now determined deterministically from the actual uploaded file's
    // type (see ds_detect_asset_type) — never guessed by the AI. Only the subcategory,
    // and the WORDING of the copy, need to adapt per type.
    $category_name_map = array(
        'images' => 'Images',
        'motion_graphics' => 'Motion Graphics',
        'powerpoint' => 'PowerPoint Templates',
        'audio' => 'Audio',
    );
    $target_category_name = $category_name_map[$asset_type] ?? 'Images';

    $category_tree = ds_get_category_tree();
    $sub_names = array();
    foreach ($category_tree as $cat) {
        if ($cat['name'] === $target_category_name) {
            $sub_names = array_values($cat['subs']);
            break;
        }
    }
    $subcategory_list_text = $sub_names ? implode(', ', $sub_names) : '(none available — return an empty string)';

    switch ($asset_type) {
        case 'motion_graphics':
            $type_context = "This image is a PREVIEW FRAME from a video / motion graphic animation, not a standalone photo. Write all copy describing the ANIMATED CLIP as a whole — assume motion, transitions, and movement consistent with the visual style shown. Use words like \"animation\", \"motion graphic\", \"video clip\", \"animated\" rather than \"photo\" or \"still image\". Never describe it as frozen or static.";
            $subcategory_instruction = "Pick the subcategory that best matches the THEME/SUBJECT of this animation. Choose EXACTLY one option from this list, copied exactly: {$subcategory_list_text}";
            break;
        case 'powerpoint':
            $type_context = "This image is a PREVIEW of ONE SLIDE from a PowerPoint TEMPLATE, not a photo. Write all copy describing the TEMPLATE as a whole (a multi-slide, editable deck a buyer can customize) — use words like \"PowerPoint template\", \"slide design\", \"editable slides\", \"presentation template\". Never describe it as a photograph.";
            $subcategory_instruction = "Look carefully at this slide and COUNT the distinct content sections/bullet points/boxes/numbered items visible on it. Pick the subcategory matching that count EXACTLY (e.g. 5 distinct content blocks → the \"5 Points explanation\" option). If the slide has no countable content points (a title slide, divider, or agenda/header-only slide), pick \"Header slides\" instead. Choose EXACTLY one option from this list, copied exactly: {$subcategory_list_text}";
            break;
        case 'audio':
            $type_context = "This is an AI-GENERATED AUDIO TRACK (music/sound), not an image — there is no visual to describe. Write all copy describing the SOUND, MUSICAL STYLE, MOOD, and LIKELY USE CASE based on the track information given below. Use words like \"track\", \"audio\", \"music\", \"sound\", \"loop\" rather than any visual language (never say \"image\", \"photo\", \"picture\", \"shows\", \"depicts\").";
            $subcategory_instruction = "Pick the subcategory that best matches the mood/genre/use of this track. Choose EXACTLY one option from this list, copied exactly: {$subcategory_list_text}";
            break;
        default:
            $type_context = "This is a standalone photo or illustration for an online design marketplace listing.";
            $subcategory_instruction = "Pick the subcategory that best matches the subject of the image. Choose EXACTLY one option from this list, copied exactly: {$subcategory_list_text}";
            break;
    }

    if ($asset_type === 'audio') {
        $intro_line = "Write marketplace listing copy for this AI-generated AUDIO TRACK using ONLY the following information about it (no image is available): {$text_context}.";
    } else {
        $intro_line = "Analyze this image for an online design marketplace listing.";
    }

    $prompt = "{$intro_line} {$type_context} Return ONLY valid JSON (no markdown, no extra text). Output is graded by an SEO/readability plugin. Follow every rule exactly.

STEP 1 — FOCUS KEYPHRASE: 2-3 plain, common words a 10-year-old would use — just WHO/WHAT this asset is about, worded appropriately for its type as described above, plus at most one simple adjective (banned: dramatic, cinematic, intense, ambiance, atmosphere, captivating, stunning, portrait, aesthetic). Call this {KEYPHRASE}. First item in \"keywords\".

STEP 2 — \"title\": MUST be 8-11 words long, and at least 55 characters — this is mandatory, not optional. Start with {KEYPHRASE} exactly, then add enough extra descriptive words (style, format, use, color, mood) to reach 8-11 words total. If in doubt, add one more word — going slightly over 60 characters is fine, going under 55 is not.

STEP 3 — \"meta_description\": 150-156 characters TOTAL (never shorter). {KEYPHRASE} within the first 60 characters, full complete sentences filling the length.

STEP 4 — \"short_description\": one sentence, 22-32 words, beginning with the literal words {KEYPHRASE}.

STEP 5 — \"full_description\": 420-520 words of simple HTML. Build it as exactly 9 short paragraphs:
- Paragraph 1-3: <p> tag, MAX 2 sentences each, MAX 35 words each. No {KEYPHRASE} here.
- Then one subheading line: <h2>short relevant subheading</h2> — this line is MANDATORY, never skip it, write it as its own line between paragraph 3 and paragraph 4.
- Paragraph 4-6: <p> tag, MAX 2 sentences each, MAX 35 words each. Use {KEYPHRASE} exactly once, only in paragraph 4 or 5.
- Paragraph 7-9: <p> tag, MAX 2 sentences each, MAX 35 words each. Use {KEYPHRASE} exactly once more, only in paragraph 8 or 9.
Hard rules for every paragraph:
- The first sentence of each paragraph must contain a transition word: however, additionally, for example, as a result, in contrast, meanwhile, therefore, similarly, in fact, on the other hand, on top of that, what's more.
- No two consecutive sentences start with the same word.
- ZERO passive voice. Before writing each sentence, check: does it use 'is/was/are/were/been/being' + a verb ending in -ed or -en describing something done TO the subject? If yes, rewrite it so the subject performs the action directly (e.g. not \"the man is shown frowning\" but \"the man frowns\").
- Only short, plain, 1-2 syllable words wherever possible.
- No <img> tags or links.

STEP 6 — \"keywords\": 6-8 comma-separated phrases, {KEYPHRASE} first exactly as written, then secondary phrases.

STEP 7 — \"tags\": 10-15 comma-separated specific tags (style, color, mood, use case, industry, file type).

STEP 8 — \"subcategory\": {$subcategory_instruction}

Return JSON: {\"title\":\"...\",\"subcategory\":\"...\",\"short_description\":\"...\",\"meta_description\":\"...\",\"full_description\":\"...\",\"keywords\":\"...\",\"tags\":\"...\"}";

    $parts = array( array('text' => $prompt) );
    if (!empty($image_base64)) {
        $parts[] = array(
            'inlineData' => array(
                'mimeType' => $mime_type,
                'data' => $image_base64,
            )
        );
    }

    $payload = array(
        'contents' => array(
            array(
                'parts' => $parts
            )
        ),
        // Ask Gemini to return raw JSON directly instead of relying on the regex
        // fallback below to fish JSON out of a markdown-wrapped response.
        'generationConfig' => array(
            'responseMimeType' => 'application/json',
            'thinkingConfig' => array( 'thinkingBudget' => 0 ),
            'maxOutputTokens' => 8192,
        ),
    );

    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode($payload),
        'timeout' => 45,
        'sslverify' => true,
    ));

    if (is_wp_error($response)) {
        return array('success' => false, 'error' => 'Gemini API error: ' . $response->get_error_message());
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Log the actual response for debugging
    error_log('Gemini API HTTP status: ' . $status_code);
    error_log('Gemini API Response: ' . substr($body, 0, 500));

    if ($status_code !== 200) {
        $api_error_msg = isset($data['error']['message']) ? $data['error']['message'] : substr($body, 0, 200);
        error_log('Gemini API returned non-200 status: ' . $status_code . ' - ' . $api_error_msg);
        return array('success' => false, 'error' => 'Gemini API error (HTTP ' . $status_code . '): ' . $api_error_msg);
    }

    if (empty($data)) {
        error_log('Failed to decode Gemini response as JSON');
        return array('success' => false, 'error' => 'Gemini returned invalid JSON: ' . substr($body, 0, 200));
    }

    if (empty($data['candidates'])) {
        error_log('No candidates in Gemini response. Keys: ' . implode(', ', array_keys($data)));
        return array('success' => false, 'error' => 'Invalid response structure: no candidates. Response keys: ' . implode(', ', array_keys($data)));
    }

    if (empty($data['candidates'][0]['content']['parts'][0]['text'])) {
        error_log('No text in candidates. Structure: ' . print_r($data['candidates'][0], true));
        return array('success' => false, 'error' => 'No text content in response');
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'];

    // Extract JSON from response
    if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
        $json_text = $matches[0];
    } else {
        return array('success' => false, 'error' => 'No JSON in Gemini response');
    }

    $json_text = ds_sanitize_json_text($json_text);
    $analysis = json_decode($json_text, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode failed. Raw text: ' . substr($json_text, 0, 1500));
        return array('success' => false, 'error' => 'JSON decode error: ' . json_last_error_msg());
    }

    return array('success' => true, 'data' => $analysis);
}

// ========== Add UI to Approval Popup ==========
add_action('admin_footer', function() {
    if (!function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'ds_creator_upload') return;

    ?>
    <style>
        #ds-ai-fill-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin: 12px 0;
            transition: all 0.3s ease;
            font-size: 13px;
            width: 100%;
        }
        #ds-ai-fill-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        #ds-ai-fill-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .ds-ai-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: dsAiSpin 0.8s linear infinite;
            margin-right: 6px;
        }
        @keyframes dsAiSpin { to { transform: rotate(360deg); } }
        #ds-ai-status {
            margin: 8px 0;
            padding: 10px 12px;
            border-radius: 4px;
            font-size: 12px;
            display: none;
        }
        #ds-ai-status.success {
            background: #ecfdf5;
            color: #065f46;
            display: block;
            border-left: 3px solid #10b981;
        }
        #ds-ai-status.error {
            background: #fee2e2;
            color: #991b1b;
            display: block;
            border-left: 3px solid #ef4444;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var observer = new MutationObserver(function() {
            var table = document.querySelector('#ds-approve-overlay table.form-table');
            if (table && !document.getElementById('ds-ai-fill-btn')) {
                dsAiAddButton(table);
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });

    function dsAiAddButton(table) {
        var container = document.createElement('div');
        container.style.cssText = 'margin-top: 16px;';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'ds-ai-fill-btn';
        btn.textContent = ' Auto-Fill with AI';

        var status = document.createElement('div');
        status.id = 'ds-ai-status';

        container.appendChild(btn);
        container.appendChild(status);
        table.parentNode.insertBefore(container, table.nextSibling);

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            dsAiFill();
        });
    }

    function dsApplySubcategoryByName(catId, subName) {
        var tree = window.dsCategoryTree || {};
        var catSelect = document.getElementById('ds-ap-cat');
        if (!catSelect || !catId || !tree[catId]) {
            console.warn('DS AI: category not applied — catId:', catId, 'tree keys:', Object.keys(tree));
            return;
        }
        catSelect.value = catId;

        var matchedSubId = null;
        if (subName) {
            var subs = tree[catId].subs || {};
            Object.keys(subs).forEach(function(id) {
                if (subs[id].toLowerCase() === subName.toLowerCase()) matchedSubId = id;
            });

            // If exact match not found, try partial match
            if (!matchedSubId) {
                Object.keys(subs).forEach(function(id) {
                    if (subs[id].toLowerCase().indexOf(subName.toLowerCase()) !== -1 ||
                        subName.toLowerCase().indexOf(subs[id].toLowerCase()) !== -1) {
                        matchedSubId = id;
                    }
                });
            }
        }

        // Populate subcategory dropdown
        if (typeof dsPopulateSubcategories === 'function') {
            dsPopulateSubcategories(catId, matchedSubId);
        } else {
            // Fallback: manually populate subcategory select
            var subSelect = document.getElementById('ds-ap-subcat');
            if (subSelect && tree[catId] && tree[catId].subs) {
                subSelect.innerHTML = '<option value="">— Select subcategory —</option>';
                Object.keys(tree[catId].subs).forEach(function(id) {
                    var opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = tree[catId].subs[id];
                    if (id == matchedSubId) opt.selected = true;
                    subSelect.appendChild(opt);
                });
            }
        }
    }

    function dsAiFill() {
        var btn = document.getElementById('ds-ai-fill-btn');
        var status = document.getElementById('ds-ai-status');
        var postId = document.getElementById('ds-approve-post-id').value;

        if (!postId) {
            showStatus('No post selected', 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="ds-ai-spinner"></span>Fetching image...';

        // Step 1: Fetch image URL
        var formData = new FormData();
        formData.append('action', 'ds_ai_get_image_url');
        formData.append('post_id', postId);

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.data || 'Could not fetch image');
            }

            btn.innerHTML = '<span class="ds-ai-spinner"></span>Analyzing...';
            var imageUrl = data.data.image_url;
            var textContext = data.data.text_context || '';
            var assetType = data.data.asset_type || 'images';
            var categoryId = data.data.category_id || '';

            // Step 2: Analyze
            var analyzeForm = new FormData();
            analyzeForm.append('action', 'ds_ai_analyze_image');
            analyzeForm.append('image_url', imageUrl || '');
            analyzeForm.append('text_context', textContext);
            analyzeForm.append('asset_type', assetType);

            return fetch(ajaxurl, {
                method: 'POST',
                body: analyzeForm
            }).then(r => r.json()).then(aiData => ({ aiData: aiData, categoryId: categoryId }));
        })
        .then(function(result) {
            var aiData = result.aiData;
            if (!aiData.success) {
                throw new Error(aiData.data || 'AI analysis failed');
            }

            var a = aiData.data;

            // Fill form fields
            document.getElementById('ds-ap-title').value = a.title || '';
            dsApplySubcategoryByName(result.categoryId, a.subcategory);
            document.getElementById('ds-ap-short').value = a.short_description || '';
            document.getElementById('ds-ap-meta').value = a.meta_description || '';
            document.getElementById('ds-ap-long').value = a.full_description || '';
            document.getElementById('ds-ap-kw').value = a.keywords || '';
            document.getElementById('ds-ap-tags').value = a.tags || '';

            // Trigger events
            ['ds-ap-title', 'ds-ap-short', 'ds-ap-meta', 'ds-ap-long', 'ds-ap-kw', 'ds-ap-tags'].forEach(id => {
                var el = document.getElementById(id);
                if (el) el.dispatchEvent(new Event('input', { bubbles: true }));
            });

            btn.disabled = false;
            btn.innerHTML = ' Auto-Fill with AI';
            showStatus(' Complete! Review and click "Approve & Publish to DLM"', 'success');
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = ' Auto-Fill with AI';
            showStatus('Error: ' + err.message, 'error');
            console.error('DS AI Error:', err);
        });
    }

    function showStatus(msg, type) {
        var status = document.getElementById('ds-ai-status');
        if (status) {
            status.textContent = msg;
            status.className = type === 'error' ? 'error' : 'success';
        }
    }
    </script>
    <?php
}, 999);
