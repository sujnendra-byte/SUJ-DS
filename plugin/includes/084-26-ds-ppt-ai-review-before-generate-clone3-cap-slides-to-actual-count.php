<?php
/**
 * Code Snippets export — 26 ds ppt ai review before generate [CLONE3] [Cap Slides To Actual Count Even When Not Truncated]
 *
 * Snippet ID : 933 (staging clone of 766; merged into the V28 file)
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-080
 * Modified   : 2026-10-01 01:35:05
 * Load order : 084 of 144
 */
/**
 * Snippet: 26 DS PPT AI Mode - Review Before Generate
 * Run: Everywhere
 * NEW SNIPPET — does not edit Snippet 12B or any Preserve Mode snippet
 * (22/24/25). Loads after all of them.
 *
 * WHAT THIS DOES:
 * Same idea as Snippet 25, applied to "Update with AI" mode instead of
 * "Preserve My Text". Flow becomes:
 *
 *   1. User enters a topic, hits "Build From My Content" (AI mode).
 *   2. This snippet calls the AI to generate the script — same prompt
 *      Snippet 12B already uses, unchanged — but does NOT charge credits
 *      or save anything yet. It's a preview.
 *   3. User sees the same "Review Your Slides" panel: title, subtitle,
 *      and every generated content point, per slide.
 *   4. User picks:
 *        - "Generate Presentation" -> THIS is when credits are charged
 *          and the deck actually gets built.
 *        - "Back to Edit" -> panel closes, nothing charged, user edits
 *          the topic/slide count and tries again.
 *
 * NOTE — this is NOT the same fix as Snippet 25. Preserve Mode had a
 * deterministic fallback available because the user's own text already
 * contains the real structure (explicit "Slide N" markers). Full AI
 * generation has no source text to fall back on — the AI is inventing
 * content from a topic, so there's no deterministic path here. This
 * snippet only adds the review/confirm step, using Snippet 12B's
 * existing generation prompt exactly as-is, so output quality/style
 * doesn't change — you just get to see it before paying for it.
 *
 * Two new endpoints, separate from Snippet 12B's paid /generate-ppt:
 *   POST /ds/v1/ai-preview  — generates the script, returns it, FREE.
 *   POST /ds/v1/ai-confirm  — re-validates the reviewed data, charges
 *                             credits, saves the job.
 *
 * Falls back to whatever window.dspGen already did (Snippet 12B's
 * original AI generation) if the preview call fails outright.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/ai-preview', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_ai_preview',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/ai-confirm', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_ai_confirm',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_ppt_ai_sanitize_slides') ) {
function ds_ppt_ai_sanitize_slides( $raw_slides, $num_slides ) {
    $allowed = array('title','bullet','two_column','three_column','image_text','feature_grid','five_point','six_point','seven_point','eight_point','stats','timeline','quote');
    $clean_slides = array();
    if ( ! is_array($raw_slides) ) $raw_slides = array();
    foreach ( $raw_slides as $s ) {
        if ( ! is_array($s) ) continue;
        $type = sanitize_text_field( $s['type'] ?? 'bullet' );
        if ( ! in_array($type, $allowed, true) ) $type = 'bullet';
        $clean_content = array();
        if ( ! empty($s['content']) && is_array($s['content']) ) {
            foreach ( $s['content'] as $c ) $clean_content[] = sanitize_text_field( (string) $c );
        }
        if ( in_array($type, array('two_column','three_column','bullet','feature_grid','five_point','six_point','seven_point','eight_point'), true) && count($clean_content) > 0 ) {
            $cnt = count($clean_content);
            if ($cnt === 2) $type = 'two_column';
            elseif ($cnt === 3) $type = 'three_column';
            elseif ($cnt === 4) $type = 'feature_grid';
            elseif ($cnt === 5) $type = 'five_point';
            elseif ($cnt === 6) $type = 'six_point';
            elseif ($cnt === 7) $type = 'seven_point';
            elseif ($cnt === 8) $type = 'eight_point';
        }
        $clean_icons = array();
        if ( ! empty($s['icons']) && is_array($s['icons']) ) {
            foreach ( $s['icons'] as $ic ) $clean_icons[] = sanitize_text_field( (string) $ic );
        }
        $clean_slides[] = array(
            'type'         => $type,
            'title'        => sanitize_text_field( $s['title'] ?? '' ),
            'subtitle'     => sanitize_text_field( $s['subtitle'] ?? '' ),
            'content'      => $clean_content,
            'image_query'  => sanitize_text_field( $s['image_query'] ?? '' ),
            'icon'         => sanitize_text_field( $s['icon'] ?? '' ),
            'icons'        => $clean_icons,
            /* DS Classic Editor: one-line Key takeaway for point slides */
            'takeaway'     => in_array($type, array('bullet','two_column','three_column','feature_grid','five_point','six_point','seven_point','eight_point','stats'), true) ? sanitize_text_field( (string) ( $s['takeaway'] ?? '' ) ) : '',
        );
    }
    if ( count($clean_slides) > $num_slides ) $clean_slides = array_slice($clean_slides, 0, $num_slides);
    while ( count($clean_slides) < $num_slides ) {
        $clean_slides[] = array(
            'type'         => 'bullet',
            'title'        => 'Section ' . (count($clean_slides) + 1),
            'subtitle'     => '',
            'content'      => array('Add content here'),
            'image_query'  => '',
            'icon'         => '',
            'icons'        => array(),
        );
    }
    return $clean_slides;
}
}

if ( ! function_exists('ds_ppt_ai_preview_ratelimit') ) {
function ds_ppt_ai_preview_ratelimit( $uid ) {
    $key = 'ds_aip_rl_' . $uid;
    $count = (int) get_transient($key);
    if ( $count >= 4 ) return false; // full generation is pricier than a restructure, keep this tighter
    set_transient($key, $count + 1, 60);
    return true;
}
}

if ( ! function_exists('ds_ppt_repair_truncated_json') ) {
function ds_ppt_repair_truncated_json( $content ) {
    // Best-effort recovery for a JSON response that got cut off mid-stream
    // (almost always because the model hit max_tokens while writing a rich,
    // verbose deck). Salvages every COMPLETE slide object written so far,
    // drops the partial slide being written when the cutoff happened, and
    // closes the JSON structure so the user gets a usable, if shorter,
    // presentation instead of a hard parse error.
    $content = trim($content);
    $slides_pos = strpos($content, '"slides"');
    if ( $slides_pos === false ) return null;
    $arr_start = strpos($content, '[', $slides_pos);
    if ( $arr_start === false ) return null;

    $depth = 0; $in_str = false; $esc = false; $last_complete_end = -1;
    $len = strlen($content);
    for ( $i = $arr_start; $i < $len; $i++ ) {
        $ch = $content[$i];
        if ( $in_str ) {
            if ( $esc ) { $esc = false; }
            elseif ( $ch === '\\' ) { $esc = true; }
            elseif ( $ch === '"' ) { $in_str = false; }
            continue;
        }
        if ( $ch === '"' ) { $in_str = true; continue; }
        if ( $ch === '{' || $ch === '[' ) { $depth++; continue; }
        if ( $ch === '}' || $ch === ']' ) {
            $depth--;
            if ( $depth === 1 && $ch === '}' ) { $last_complete_end = $i; }
            if ( $depth === 0 ) { break; }
        }
    }
    if ( $last_complete_end === -1 ) return null;

    $title_part = '';
    $head = substr($content, 0, $slides_pos);
    if ( preg_match('/"title"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/', $head, $m) ) {
        $title_part = '"title":"' . $m[1] . '",';
    }
    $body = substr($content, $arr_start, $last_complete_end - $arr_start + 1);
    $repaired = '{' . $title_part . '"slides":' . $body . ']}';

    $decoded = json_decode($repaired, true);
    return ( is_array($decoded) && ! empty($decoded['slides']) ) ? $decoded : null;
}
}

if ( ! function_exists('ds_ppt_ai_preview') ) {
function ds_ppt_ai_preview( WP_REST_Request $req ) {
    $uid = get_current_user_id();

    // Plan gate removed: any logged-in user with enough credits can
    // generate an AI presentation, regardless of plan tier. The credit
    // check below (using ds_ppt_credit_cost) is the real gate now.

    if ( ! ds_ppt_ai_preview_ratelimit($uid) ) {
        return rest_ensure_response(array('error'=>'rate_limited','message'=>'Please wait a moment before previewing again.'));
    }

    $topic      = sanitize_text_field( $req->get_param('topic') ?: '' );
    $num_slides = (int) ( $req->get_param('num_slides') ?: $req->get_param('slides') ?: 10 );
    $num_slides = max(1, min(20, $num_slides));
    $language   = sanitize_text_field( $req->get_param('language') ?: 'English' );

    if ( empty($topic) ) {
        return rest_ensure_response(array('error'=>'No topic','message'=>'Please enter a topic.'));
    }

    // Cost is computed server-side, same formula as Snippet 12B, quoted
    // here for display but not charged until /ai-confirm.
    $credits = function_exists('ds_get_credits')
        ? (int) ds_get_credits($uid)
        : (int) get_user_meta($uid, 'ds_ai_credits', true);
    $cost = ds_ppt_credit_cost( $num_slides, 'ai' );
    if ( $credits < $cost ) {
        return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
    }

    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $api_key = isset($keys['groq']) ? $keys['groq'] : '';
    if ( empty($api_key) ) {
        return rest_ensure_response(array('error'=>'No API key','message'=>'Groq API key missing in snippet 2a (ds_get_api_keys).'));
    }
    $groq_model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');

    // ---- Identical prompt to Snippet 12B's ds_ppt_v3_generate. Kept
    // word-for-word so preview output matches what full generation would
    // have produced anyway — this only adds a review step, it does not
    // change generation quality or style. ----
    $icons = 'mdi:rocket-launch,mdi:chart-bar,mdi:account-group,mdi:target,mdi:lightbulb,mdi:shield-check,mdi:earth,mdi:star,mdi:check-circle,mdi:flag,mdi:trending-up,mdi:cog,mdi:clock-outline,mdi:heart,mdi:home,mdi:email,mdi:cellphone,mdi:magnify,mdi:tune,mdi:account,mdi:lightning-bolt,mdi:trophy,mdi:book-open,mdi:briefcase,mdi:calendar,mdi:sparkles,mdi:weather-sunny,mdi:alert,mdi:play-circle,mdi:plus-circle,mdi:leaf,mdi:cash,mdi:handshake,mdi:brain,mdi:cloud,mdi:lock,mdi:fire,mdi:diamond,mdi:puzzle,mdi:thumb-up';

    $prompt  = "Create a {$num_slides}-slide professional presentation in {$language}.\n\n";
    $prompt .= "TOPIC: {$topic}\n\n";
    $prompt .= "=== CONTENT RULES ===\n";
    $prompt .= "- BANNED PHRASES - never write these or anything this generic: Key point, Important aspect, Description here, Clearly defining the issue, Introducing the product or service, Analyzing the target audience, Highlighting unique selling points, Setting the stage, Showcasing the solution, Demonstrating traction and demand, Building rapport with the audience, Crafting a compelling narrative.\n";
    $prompt .= "- Every explanation MUST contain at least ONE concrete element: a number or percentage, a year, a named tool/company/person, or a measurable real-world outcome.\n";
    $prompt .= "- BAD example: Market Opportunity: Analyzing the target audience. GOOD example: Market Opportunity: India SMB payroll market hit \$2.1B in 2025, growing 14% YoY, yet 68% of firms under 50 staff still process salaries manually in Excel.\n";
    $prompt .= "- Every point must have a BOLD HEADING (2-4 words) AND a rich EXPLANATION (30-45 words minimum) with specific facts, numbers, named tools, real examples.\n";
    $prompt .= "- If user gives vague topic, INVENT plausible specifics (real-sounding names, percentages, years, product names).\n";
    $prompt .= "- Subtitles must also be specific - never write subtitles like Key Components of a Successful Pitch. Write subtitles with substance like Why 9 of 10 pitches fail in the first 3 minutes.\n\n";
    $prompt .= "=== SLIDE TYPE SELECTION RULES ===\n";
	$prompt .= "  If the topic implies a real company, product, or person, also populate slide 1's content array with any that apply, each its own item, EXACT format:\n";
    $prompt .= "  'Company: <name>', 'Presenter: <full name>', 'Designation: <job title>', 'Date: <e.g. 11 July 2026>', 'Website: <e.g. www.companyname.com>', 'Tagline: <3-6 words>'.\n";
    $prompt .= "  Only include items you can fill with something real or plausible - omit any you can't. Never include a 'Confidential:' item unless the topic is explicitly sensitive/internal.\n";
    $prompt .= "- Slide 1: ALWAYS type 'title' with 12-16 word subtitle.\n";
    $prompt .= "- Use your expert knowledge of the TOPIC to decide the natural number of points for each slide.\n";
    $prompt .= "  Do NOT force a fixed count. The topic itself tells you: SWOT=4, pros/cons=2, RGB=3, 4 seasons=4, 5 steps=5.\n";
    $prompt .= "- Choose slide type based on point count AND content nature:\n";
    $prompt .= "  two_column: exactly 2 parallel/contrasting points\n";
    $prompt .= "  three_column: exactly 3 parallel points\n";
    $prompt .= "  feature_grid: exactly 4 key items/quadrants/pillars\n";
    $prompt .= "  five_point: exactly 5 steps/items in a process, framework, or list\n";
    $prompt .= "  six_point: exactly 6 steps/items in a process, framework, or list\n";
    $prompt .= "  seven_point: exactly 7 steps/items in a process, framework, or list\n";
    $prompt .= "  eight_point: exactly 8 steps/items in a process, framework, or list\n";
    $prompt .= "  bullet: 4-6 points needing detail (use only when content genuinely does not fit a card or step layout)\n";
    $prompt .= "  stats: 3 key numbers/metrics\n";
    $prompt .= "  timeline: sequential steps/phases/milestones\n";
    $prompt .= "  quote: single powerful quote\n";
    $prompt .= "- Use stats AND timeline AND quote at least once each.\n";
    $prompt .= "- CRITICAL SLIDE TYPE BUDGET: title exactly once (slide 1). timeline at most ONCE. quote at most ONCE. stats at most ONCE. Every other type (two_column, three_column, image_text, feature_grid, five_point, six_point, seven_point, eight_point, bullet) at most TWICE. Rotate types so the same type never appears on two slides in a row, and spread the repeats far apart. Hitting the exact requested slide count is more important than this budget: never drop a slide to satisfy it - reuse a type instead.\n";
    $prompt .= "- Whenever a slide naturally has 5, 6, 7, or 8 distinct points (steps of a process, pillars of a framework, features, phases), use five_point/six_point/seven_point/eight_point instead of bullet - these render as numbered step cards and are strongly preferred over bullet for any list of 5 or more items.\n\n";
    $prompt .= "=== PER-TYPE FORMAT (content array items) ===\n";
    $prompt .= "bullet / two_column / three_column:\n";
    $prompt .= "  Each item = 'BOLD HEADING: Full explanation sentence 30-45 words with specific fact or number.'\n";
    $prompt .= "  Example: 'Customer Retention: Our loyalty program reduced annual churn from 18% to 6.2% by introducing tiered rewards across 3 membership levels, saving approximately \$2.4M in re-acquisition costs each quarter.'\n";
    $prompt .= "  Number of items: two_column=2, three_column=3, bullet=4-6\n\n";
    $prompt .= "feature_grid:\n";
    $prompt .= "  EXACTLY 4 items = 'Short Heading: 30-40 word explanation with concrete data.'\n";
    $prompt .= "  Also add 'icons' array of EXACTLY 4 Iconify icon names. Format: collection:name (e.g. mdi:rocket-launch).\n";
    $prompt .= "  Pick from: {$icons}\n";
    $prompt .= "  CRITICAL: Choose icons that DIRECTLY match each card's specific topic. Examples:\n";
    $prompt .= "  team/people/collaboration=mdi:account-group, money/revenue/finance=mdi:cash, security/trust/safety=mdi:shield-check, growth/increase/trending=mdi:trending-up, idea/innovation/creative=mdi:lightbulb, global/worldwide/reach=mdi:earth, technology/software/digital=mdi:cog, speed/fast/efficient=mdi:lightning-bolt, success/complete/done=mdi:check-circle, goal/strategy/focus=mdi:target, data/analytics/metrics=mdi:chart-bar, customer/user/person=mdi:account, heart/care/health=mdi:heart, time/schedule/plan=mdi:clock-outline, award/trophy/best=mdi:trophy, learning/education/book=mdi:book-open, communication/message=mdi:email, rocket/launch/startup=mdi:rocket-launch, leaf/green/eco/sustainable=mdi:leaf, handshake/partner/deal=mdi:handshake, brain/ai/smart=mdi:brain, lock/private/secure=mdi:lock, fire/hot/popular=mdi:fire, puzzle/solution/fit=mdi:puzzle, like/approve/positive=mdi:thumb-up\n";
    $prompt .= "five_point / six_point / seven_point / eight_point:\n";
    $prompt .= "  EXACTLY 5, 6, 7, or 8 items (matching the type name) = 'Short Heading: 25-35 word explanation with concrete data.'\n";
    $prompt .= "  Also add 'icons' array with the SAME number of Iconify icon names as content items, chosen the same way as feature_grid icons above.\n\n";
    $prompt .= "stats:\n";
    $prompt .= "  EXACTLY 3 items = 'METRIC_VALUE: 4-7 word descriptive label'\n";
    $prompt .= "  Example: '94.7%: Annual customer retention rate 2024'\n\n";
    $prompt .= "timeline:\n";
    $prompt .= "  3-5 items = 'YEAR or PHASE: 15-22 word description of what was achieved or planned.'\n\n";
    $prompt .= "quote:\n";
    $prompt .= "  title = full quote 20-30 words (no quotes in JSON), content[0] = 'Person Name, Title, Company'\n\n";
    $prompt .= "=== KEY TAKEAWAY (point slides) ===\n";
    $prompt .= "For EVERY two_column, three_column, feature_grid, five_point, six_point, seven_point, eight_point, bullet and stats slide, also add a 'takeaway' field: ONE sentence of 12-22 words stating the single most important insight or action from that slide, with a concrete number or outcome where possible. Never copy a point word-for-word. title, timeline and quote slides get no takeaway.\n\n";
    $prompt .= "=== MANDATORY SLIDE HEADER ===\n";
    $prompt .= "Every slide (except title/quote) MUST have:\n";
    $prompt .= "  - 'title': Strong 3-6 word slide heading\n";
    $prompt .= "  - 'subtitle': 8-12 word supporting sub-headline that adds context\n\n";
    $prompt .= "Return ONLY valid JSON in this shape (no markdown):\n";
    $prompt .= '{"title":"Presentation Title","slides":{"type":"title","title":"...","subtitle":"...","content":["Company: Acme Corp","Presenter: Jane Doe","Designation: Marketing Manager","Date: 11 July 2026","Website: www.acmecorp.com","Tagline: Building Solutions. Delivering Excellence."],"image_query":"","icon":"","icons":[]}{"type":"two_column","title":"...","subtitle":"...","takeaway":"One-sentence key insight from this slide with a concrete number.","content":["Heading One: Full 30 word explanation with specific data.","Heading Two: Full 30 word explanation with specific data."],"image_query":"","icon":"","icons":[]},{"type":"three_column","title":"...","subtitle":"...","content":["Heading: explanation...","Heading: explanation...","Heading: explanation..."],"image_query":"","icon":"","icons":[]},{"type":"feature_grid","title":"...","subtitle":"...","content":["Heading: explanation...","Heading: explanation...","Heading: explanation...","Heading: explanation..."],"image_query":"","icon":"","icons":["rocket","chart","users","target"]},{"type":"five_point","title":"...","subtitle":"...","content":["Step Heading: 25-35 word explanation.","Step Heading: 25-35 word explanation.","Step Heading: 25-35 word explanation.","Step Heading: 25-35 word explanation.","Step Heading: 25-35 word explanation."],"image_query":"","icon":"","icons":["mdi:rocket-launch","mdi:chart-bar","mdi:target","mdi:lightbulb","mdi:trophy"]},{"type":"stats","title":"...","subtitle":"...","content":["94.7%: Annual customer retention","1.2M: Active monthly users","$4.8M: Quarterly revenue 2024"],"image_query":"","icon":"","icons":[]},{"type":"timeline","title":"...","subtitle":"...","content":["2022 Q1: Launched MVP serving 200 pilot customers in 3 cities.","2023 Q2: Scaled to 50,000 users with Series A funding of $12M."],"image_query":"","icon":"","icons":[]},{"type":"quote","title":"The best salespeople are the ones who listen more than they talk and ask questions that reveal the true pain of the customer.","subtitle":"","content":["Brian Tracy, Sales Trainer, FocalPoint"],"image_query":"","icon":"","icons":[]},{"type":"image_text","title":"...","subtitle":"...","content":["Heading: explanation...","Heading: explanation...","Heading: explanation..."],"image_query":"modern office team collaboration technology","icon":"","icons":[]},{"type":"bullet","title":"...","subtitle":"...","content":["Heading: Full 35 word explanation with specific numbers or named tools.","Heading: Full 35 word explanation with specific numbers or named tools.","Heading: Full 35 word explanation with specific numbers or named tools.","Heading: Full 35 word explanation with specific numbers or named tools."],"image_query":"","icon":"","icons":[]}]}';
    $prompt .= "\n=== SLIDE COUNT COMPLIANCE (CRITICAL) ===\n";
    $prompt .= "You MUST output exactly {$num_slides} slide objects in the slides array - no more, no fewer. Do NOT stop early. If you are running low on room, write shorter explanations (still meeting the stated minimum word counts) for the remaining slides rather than omitting any slide. A presentation that stops at 60% of the requested slide count is a failure even if the content quality is high.\n";

    $sys_msg = "You are a world-class presentation designer and content strategist. Write RICH, SPECIFIC, DATA-DRIVEN content. NEVER write generic placeholders. Every bullet point has a bold SHORT HEADING (2-4 words) followed by a colon and a FULL EXPLANATION (30-45 words minimum) with real statistics, named tools, specific examples, or concrete outcomes. Use your deep knowledge of each topic to decide how many points naturally belong — do NOT force a fixed count. Choose slide type based on what the topic demands: SWOT=4 items feature_grid, pros/cons=2 items two_column, 3 pillars=three_column, 5-8 step process or framework=five_point/six_point/seven_point/eight_point, detailed list=bullet, numbers=stats, steps=timeline. CRITICAL SLIDE TYPE BUDGET: title is used exactly once, as slide 1. timeline may be used at most ONCE in the whole deck. quote may be used at most ONCE in the whole deck. stats may be used at most ONCE in the whole deck. Every other content type (two_column, three_column, image_text, feature_grid, five_point, six_point, seven_point, eight_point, bullet) may be used at most TWICE. Shuffle the content types so the same type never appears on two slides in a row. Meeting the exact requested slide count outranks this budget: if you cannot reach the count within these limits, reuse content types more rather than dropping any slide. Every slide except title/quote must have both title AND subtitle. Output ONLY valid JSON, no markdown, no code fences.";

    // Scale the output token budget with slide count - a fixed 8000
    // budget was cutting large decks (15-20 slides) off partway through,
    // leaving the rest as blank placeholder slides. ~650 tokens/slide is
    // enough headroom for the richest slide types (5-8 point cards).
    // 800 tokens/slide never applied below 12 slides because the 9000 floor
// swallowed it, so an 8- or 10-slide deck got the same budget as a 4-slide
// one and the JSON was cut off mid-deck. Scale properly and give the model
// real headroom (llama-3.3-70b on Groq allows 32k completion tokens).
$max_out_tokens = (int) min(15000, max(9000, $num_slides * 1500));

    /* Retry once: a single bad roll from the model (truncated JSON, or a
       deck shorter than the user asked for) should not reach the user. */
    $ds_small_retry = false;
    $ds_attempt = 0;
    /* 17.3: the host's gateway kills any request at 120s (504), which the
       browser shows as "Network error while previewing". Every attempt and
       retry now shares one ~100s budget, and the best deck seen so far is
       returned instead of starting another attempt that can't finish. */
    $ds_deadline   = microtime(true) + 100;
    $ds_best_ppt   = null;
    $ds_best_rep   = false;
    ds_ppt_retry:
    $ds_attempt++;

    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'timeout' => max(10, (int) floor($ds_deadline - microtime(true))),
        'headers' => array(
            'Authorization' => 'Bearer '.$api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode(array(
            'model'           => $groq_model,
            'messages'        => array(
                array('role'=>'system','content'=>$sys_msg),
                array('role'=>'user','content'=>$prompt),
            ),
            'temperature'     => 0.85,
            'top_p'           => 0.95,
            'max_tokens'      => $max_out_tokens,
            'response_format' => array('type'=>'json_object'),
        )),
    ));

    if ( is_wp_error($resp) ) {
        if ( $ds_best_ppt ) { $ppt = $ds_best_ppt; $was_repaired = $ds_best_rep; goto ds_ppt_have; }
        return rest_ensure_response(array('error'=>'API error','message'=>'The AI took too long to write this deck. Please try again, or choose fewer slides.'));
    }
    $body = wp_remote_retrieve_body($resp);
    $data = json_decode($body, true);

    // Groq answers 4xx with {"error":{"message":...}} and no "choices" key.
    // Without this the real reason - rate limit, token cap, bad key - was
    // discarded and the user just saw the generic "Parse failed".
    $ds_http = (int) wp_remote_retrieve_response_code($resp);
    if ( $ds_http >= 400 || isset($data['error']) ) {
        $ds_api_msg = isset($data['error']['message']) ? $data['error']['message'] : ( 'HTTP ' . $ds_http );
        error_log( 'DS PPT: Groq rejected the request - ' . $ds_api_msg );
        if ( $ds_attempt < 3 && ($ds_deadline - microtime(true)) > 30 ) {
            // Most rejections at this point are "asked for too many tokens".
            // Back off and try again rather than failing the whole deck.
            $max_out_tokens = (int) max(4000, floor($max_out_tokens * 0.6));
            goto ds_ppt_retry;
        }
        return rest_ensure_response(array('error'=>'API error','message'=>$ds_api_msg));
    }
    $content = $data['choices'][0]['message']['content'] ?? '';
    $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
    $content = preg_replace('/\s*```$/', '', $content);
    $ppt = json_decode($content, true);

    $was_repaired = false;
    if ( ! $ppt || empty($ppt['slides']) ) {
        // Most often the model hit max_tokens partway through a verbose
        // deck. Salvage whatever complete slides it did finish instead of
        // failing outright -- a shorter deck beats a hard error.
        $repaired = function_exists('ds_ppt_repair_truncated_json') ? ds_ppt_repair_truncated_json($content) : null;
        if ( $repaired ) {
            $ppt = $repaired;
            $was_repaired = true;
        } else {
            // Truncated JSON is probabilistic - a third attempt converts most
            // of the remaining failures on 12+ slide decks into full decks.
            if ( $ds_attempt < 3 && ($ds_deadline - microtime(true)) > 30 ) { goto ds_ppt_retry; }
            // Last resort: a smaller budget makes the model write a terser deck
            // that finishes inside the limit, or at least truncates somewhere the
            // salvager can recover. A short real deck beats a hard error.
            if ( empty($ds_small_retry) && $max_out_tokens > 9000 && ($ds_deadline - microtime(true)) > 30 ) {
                $ds_small_retry = true;
                $max_out_tokens = 9000;
                $ds_attempt     = 0;
                goto ds_ppt_retry;
            }
            if ( $ds_best_ppt ) { $ppt = $ds_best_ppt; $was_repaired = $ds_best_rep; goto ds_ppt_have; }
            return rest_ensure_response(array('error'=>'Parse failed','message'=>'AI response could not be parsed. Please try again.'));
        }
    }

    // 17.3: remember the most complete deck so far.
    if ( is_array($ppt['slides'] ?? null) && ( ! $ds_best_ppt || count($ppt['slides']) > count($ds_best_ppt['slides']) ) ) {
        $ds_best_ppt = $ppt;
        $ds_best_rep = $was_repaired;
    }

    // If we had to salvage a truncated response, only ask for as many
    // slides as actually came back real -- otherwise ds_ppt_ai_sanitize_slides
    // pads the rest with empty "Section N / Add content here" placeholders,
    // which is worse than a shorter, fully-real deck.
    // Cap to however many slides actually came back, whether we had to
    // salvage a truncated response OR the model just returned a valid but
    // shorter deck than requested -- either way, asking ds_ppt_ai_sanitize_slides
    // for more than we actually have pads the rest with empty
    // "Section N / Add content here" placeholders, which is worse than a
    // shorter, fully-real deck.
    $actual_slide_count = is_array($ppt['slides']) ? count($ppt['slides']) : 0;
    $was_short = $actual_slide_count > 0 && $actual_slide_count < $num_slides;
    if ( $was_short && $ds_attempt < 2 && ($ds_deadline - microtime(true)) > 35 ) { goto ds_ppt_retry; }
    ds_ppt_have:
    $actual_slide_count = is_array($ppt['slides'] ?? null) ? count($ppt['slides']) : 0;
    $was_short = $actual_slide_count > 0 && $actual_slide_count < $num_slides;
    $effective_num_slides = $was_short ? $actual_slide_count : $num_slides;

    $clean_slides = ds_ppt_ai_sanitize_slides($ppt['slides'], $effective_num_slides);
    $ppt_out = array(
        'title'  => sanitize_text_field( $ppt['title'] ?? $topic ),
        'slides' => $clean_slides,
    );

    // No credit deduction, no job save — this is just a preview.
    return rest_ensure_response(array(
        'success'   => true,
        'ppt_data'  => $ppt_out,
        'cost'      => $cost,
        'truncated' => $was_repaired,
        'note'      => $was_short ? ('The AI generated ' . count($clean_slides) . ' of the ' . $num_slides . ' slides you requested' . ( $was_repaired ? ' (the response was cut short).' : '.' ) . ' Regenerate to try for the full count, or add slides manually below.') : '',
    ));
}
}

if ( ! function_exists('ds_ppt_ai_confirm') ) {
function ds_ppt_ai_confirm( WP_REST_Request $req ) {
    $uid = get_current_user_id();

    // Plan gate removed: credit check below (ds_ppt_credit_cost) is the
    // real gate now - any logged-in user with enough credits can confirm.

    $ppt_data = $req->get_param('ppt_data');
    if ( ! is_array($ppt_data) || empty($ppt_data['slides']) ) {
        return rest_ensure_response(array('error'=>'No data','message'=>'Nothing to generate. Please build a preview first.'));
    }

    // Never trust the client's reviewed data blindly — re-run the exact
    // same sanitize rules used for the preview.
    $num_slides = max(1, min(20, count($ppt_data['slides'])));
    $clean_slides = ds_ppt_ai_sanitize_slides($ppt_data['slides'], $num_slides);
    if ( empty($clean_slides) ) {
        return rest_ensure_response(array('error'=>'No data','message'=>'No valid slides to generate.'));
    }

    $ppt_out = array(
        'title'  => sanitize_text_field( $ppt_data['title'] ?? ( $clean_slides[0]['title'] ?: 'Presentation' ) ),
        'slides' => $clean_slides,
    );

    // Cost is fixed server-side from the actual slide count — never
    // trust a client-supplied amount.
    $cost = ds_ppt_credit_cost( $num_slides, 'ai' );
    $credits = function_exists('ds_get_credits')
        ? (int) ds_get_credits($uid)
        : (int) get_user_meta($uid, 'ds_ai_credits', true);
    if ( $credits < $cost ) {
        return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
    }
    if ( ! ds_deduct_credits($uid, $cost) ) {
        return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
    }

    $id = 'ppt_'.time().'_'.wp_generate_password(8, false, false);
    $jobs = get_user_meta($uid, 'ds_ppt_jobs', true) ?: array();
    array_unshift($jobs, array('id'=>$id, 'title'=>$ppt_out['title'], 'slides'=>count($ppt_out['slides']), 'time'=>time()));
    update_user_meta($uid, 'ds_ppt_jobs', array_slice($jobs, 0, 30));

    $ppt_log = get_user_meta($uid, 'ds_ppt_generations', true) ?: array();
    array_unshift($ppt_log, array('date'=>current_time('mysql'), 'prompt'=>$ppt_out['title'], 'credits'=>$cost, 'num_slides'=>count($ppt_out['slides'])));
    update_user_meta($uid, 'ds_ppt_generations', array_slice($ppt_log, 0, 100));

    return rest_ensure_response(array(
        'success'         => true,
        'ppt_id'          => $id,
        'ppt_data'        => $ppt_out,
        'credits_balance' => function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true),
    ));
}
}

/* ===== Client-side: wrap window.dspGen again, AI-mode only ===== */
add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<script>
(function () {
'use strict';

var _prevDspGen = window.dspGen; // Snippet 25, then everything beneath it
if (!_prevDspGen) return;

var BASE = window.PURL || '';
var AI_PREVIEW_URL = BASE.replace('generate-ppt', 'ai-preview');
var AI_CONFIRM_URL = BASE.replace('generate-ppt', 'ai-confirm');

function esc(s) {
  return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function splitItem(item) {
  var i = item.indexOf(':');
  if (i === -1) return { h: item, d: '' };
  return { h: item.slice(0, i).trim(), d: item.slice(i + 1).trim() };
}
var TYPE_LABEL = {
  title: 'Title', bullet: 'Bullets', two_column: '2 Columns', three_column: '3 Columns',
  feature_grid: '2x2 Grid', five_point: '5 Steps', six_point: '6 Steps', seven_point: '7 Steps', eight_point: '8 Steps',
  stats: 'Stats', timeline: 'Timeline', quote: 'Quote', image_text: 'Image + Text'
};

function showPreviewLoader(label) {
  var el = document.createElement('div');
  el.id = 'dsp-ai-rv-loading';
  el.style.cssText = 'position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  el.innerHTML =
    '<div style="background:#111827;border:1px solid #2a2a2a;border-radius:12px;padding:32px 40px;text-align:center;color:#e5e7eb;">' +
      '<div style="width:36px;height:36px;border:3px solid #2a2a2a;border-top-color:#4A9EFF;border-radius:50%;margin:0 auto 16px;animation:dspairvspin 0.8s linear infinite;"></div>' +
      '<div style="font-weight:700;font-size:15px;">' + esc(label) + '</div>' +
      '<div style="color:#9ca3af;font-size:12.5px;margin-top:4px;">This is just a preview — nothing is charged yet.</div>' +
    '</div>' +
    '<style>@keyframes dspairvspin{to{transform:rotate(360deg);}}</style>';
  document.body.appendChild(el);
  return el;
}
function removeEl(el) { if (el && el.parentNode) el.parentNode.removeChild(el); }

function recalcType(s) {
  var n = (s.content || []).length;
  if (['two_column','three_column','feature_grid','bullet'].indexOf(s.type) !== -1 && n > 0) {
    if (n === 2) s.type = 'two_column';
    else if (n === 3) s.type = 'three_column';
    else if (n === 4) s.type = 'feature_grid';
    else if (n >= 5) s.type = 'bullet';
  }
}

function showReviewPanel(ppt, cost, onConfirm, onBack, costFn) {
  var overlay = document.createElement('div');
  overlay.id = 'dsp-ai-rv-panel';
  overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;padding:24px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';

  overlay.innerHTML =
    '<div style="background:#161b22;border:1px solid #2a2a2a;border-radius:14px;max-width:720px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">' +
      '<div style="padding:22px 26px;border-bottom:1px solid #21262d;">' +
        '<div style="color:#f9fafb;font-weight:800;font-size:19px;">Review Your Slides</div>' +
        '<div style="color:#9ca3af;font-size:13px;margin-top:3px;">Here\'s what the AI generated from your topic. Edit, delete a slide, or add/remove points, then confirm.</div>' +
      '</div>' +
      '<div id="dsp-ai-rv-list" style="padding:20px 26px;overflow-y:auto;flex:1;"></div>' +
      '<div style="padding:16px 26px;border-top:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;gap:12px;">' +
        '<button id="dsp-ai-rv-back" style="background:transparent;border:1px solid #30363d;color:#c9d1d9;border-radius:8px;padding:11px 18px;font-weight:700;font-size:13px;cursor:pointer;">&larr; Back to Edit</button>' +
        '<button id="dsp-ai-rv-confirm" style="background:#2563eb;border:none;color:#fff;border-radius:8px;padding:11px 22px;font-weight:800;font-size:13px;cursor:pointer;">&#10003; Generate Presentation &middot; ' + cost + ' credits</button>' +
      '</div>' +
    '</div>';
  document.body.appendChild(overlay);

  var listEl = document.getElementById('dsp-ai-rv-list');

  function renderBody() {
    listEl.innerHTML = ppt.slides.map(function (s, i) {
      var contentHtml = (s.content || []).map(function (c, ci) {
        return '<li style="margin-bottom:8px;display:flex;align-items:flex-start;gap:8px;">' +
          '<span contenteditable="true" data-idx="' + i + '" data-cidx="' + ci + '" style="flex:1;color:#c9d1d9;font-size:13px;line-height:1.5;outline:none;border-bottom:1px dashed transparent;" ' +
          'onfocus="this.style.borderBottomColor=\'#30363d\'" onblur="this.style.borderBottomColor=\'transparent\'">' + esc(c) + '</span>' +
          '<button class="dsp-rv-del-point" data-idx="' + i + '" data-cidx="' + ci + '" title="Remove point" style="flex-shrink:0;background:transparent;border:none;color:#f85149;font-size:15px;cursor:pointer;line-height:1;padding:2px 4px;">&times;</button>' +
        '</li>';
      }).join('');

      return (
        '<div style="background:#0d1117;border:1px solid #21262d;border-radius:10px;padding:18px 20px;margin-bottom:14px;">' +
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">' +
            '<span style="color:#58A6FF;font-weight:700;font-size:12px;letter-spacing:.03em;">SLIDE ' + (i + 1) + '</span>' +
            '<div style="display:flex;align-items:center;gap:8px;">' +
              '<span style="color:#6b7280;font-size:11px;background:#161b22;border:1px solid #21262d;border-radius:20px;padding:2px 10px;">' + esc(TYPE_LABEL[s.type] || s.type) + '</span>' +
              '<button class="dsp-rv-del-slide" data-idx="' + i + '" title="Delete slide" style="background:transparent;border:1px solid #30363d;color:#f85149;border-radius:6px;font-size:12px;cursor:pointer;padding:3px 8px;">&#128465; Delete</button>' +
            '</div>' +
          '</div>' +
          '<div contenteditable="true" data-idx="' + i + '" data-field="title" style="color:#f9fafb;font-weight:800;font-size:17px;margin-bottom:4px;outline:none;">' + esc(s.title) + '</div>' +
          '<div contenteditable="true" data-idx="' + i + '" data-field="subtitle" style="color:#8b95a5;font-size:13px;font-style:italic;margin-bottom:10px;outline:none;" data-placeholder="Add a subtitle…">' + esc(s.subtitle || '') + '</div>' +
          (contentHtml ? '<ul style="margin:10px 0 0;padding-left:6px;list-style:none;">' + contentHtml + '</ul>' : '') +
          '<button class="dsp-rv-add-point" data-idx="' + i + '" style="margin-top:8px;background:transparent;border:1px dashed #30363d;color:#8b95a5;border-radius:6px;font-size:12px;cursor:pointer;padding:5px 10px;">+ Add point</button>' +
        '</div>'
      );
    }).join('');

    var confirmBtn = document.getElementById('dsp-ai-rv-confirm');
    if (confirmBtn) {
      cost = costFn ? costFn(ppt.slides.length) : cost;
      confirmBtn.innerHTML = '&#10003; Generate Presentation &middot; ' + cost + ' credits';
    }
  }

  listEl.addEventListener('click', function (e) {
    var delSlideBtn = e.target.closest('.dsp-rv-del-slide');
    if (delSlideBtn) {
      if (ppt.slides.length <= 1) { alert('You need at least one slide.'); return; }
      ppt.slides.splice(parseInt(delSlideBtn.getAttribute('data-idx'), 10), 1);
      renderBody();
      return;
    }
    var delPointBtn = e.target.closest('.dsp-rv-del-point');
    if (delPointBtn) {
      var s1 = ppt.slides[parseInt(delPointBtn.getAttribute('data-idx'), 10)];
      s1.content.splice(parseInt(delPointBtn.getAttribute('data-cidx'), 10), 1);
      recalcType(s1);
      renderBody();
      return;
    }
    var addPointBtn = e.target.closest('.dsp-rv-add-point');
    if (addPointBtn) {
      var s2 = ppt.slides[parseInt(addPointBtn.getAttribute('data-idx'), 10)];
      s2.content.push('New Point: Click to edit this text.');
      recalcType(s2);
      renderBody();
      return;
    }
  });

  listEl.addEventListener('input', function (e) {
    var t = e.target;
    var idx = t.getAttribute('data-idx');
    if (idx === null) return;
    idx = parseInt(idx, 10);
    var field = t.getAttribute('data-field');
    if (field === 'title') { ppt.slides[idx].title = t.textContent; return; }
    if (field === 'subtitle') { ppt.slides[idx].subtitle = t.textContent; return; }
    var cidx = t.getAttribute('data-cidx');
    if (cidx !== null) { ppt.slides[idx].content[parseInt(cidx, 10)] = t.textContent; }
  });

  renderBody();

  document.getElementById('dsp-ai-rv-back').onclick = function () { removeEl(overlay); onBack(); };
  document.getElementById('dsp-ai-rv-confirm').onclick = function () {
    document.getElementById('dsp-ai-rv-confirm').textContent = 'Generating…';
    document.getElementById('dsp-ai-rv-confirm').disabled = true;
    onConfirm();
  };
}

window.dspGen = function () {
  var _outerThis = this, _outerArgs = arguments;

  if (typeof window.G === 'undefined' || window.G.mode !== 'ai') {
    return _prevDspGen.apply(_outerThis, _outerArgs);
  }

  var promptEl = document.getElementById('dsp-prompt');
  var topic = promptEl ? promptEl.value.trim() : '';
  if (!topic) {
    if (promptEl) promptEl.focus();
    return;
  }

  var slidesInput = document.getElementById('dsp-slides');
  var numSlides = slidesInput ? (parseInt(slidesInput.value, 10) || 10) : 10;
  var design = (document.getElementById('dsp-design') || {}).value || 'navy-pro';
  var langEl = document.getElementById('dsp-lang');
  var language = langEl ? langEl.value : 'English';

  var loader = showPreviewLoader('Generating your script…');

  fetch(AI_PREVIEW_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
    body: JSON.stringify({ topic: topic, num_slides: numSlides, language: language })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    removeEl(loader);
    if (!d || d.error || !d.ppt_data) {
      alert((d && d.message) ? d.message : 'Preview failed. Falling back to standard generation.');
      return _prevDspGen.apply(_outerThis, _outerArgs);
    }

    var ppt = d.ppt_data;
    var cost = d.cost || Math.max(window.DS_MIN_CREDITS || 5, numSlides * (window.DS_CREDITS_PER_SLIDE || 1));

    showReviewPanel(ppt, cost, function onConfirm() {
      fetch(AI_CONFIRM_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
        body: JSON.stringify({ ppt_data: ppt })
      })
      .then(function (r) { return r.json(); })
      .then(function (d2) {
        var panel = document.getElementById('dsp-ai-rv-panel');
        removeEl(panel);
        if (!d2 || d2.error || !d2.ppt_data) {
          alert((d2 && d2.message) ? d2.message : 'Could not generate the presentation. Please try again.');
          return;
        }
        if (d2.credits_balance !== undefined) {
          window.G.credits = d2.credits_balance;
          var ce = document.getElementById('ds-cr-display');
          if (ce) ce.textContent = window.G.credits.toLocaleString();
        }
        var finalPpt = d2.ppt_data;
        window.dspLiveGenShow(topic, finalPpt.slides.length, design);
        var bannerTxt = document.getElementById('dsp-lg-banner-txt');
        if (bannerTxt) bannerTxt.innerHTML = '<b style="color:#111;">Building Your Slides:</b> Turning your reviewed script into a deck.';
        function finish() {
          window.dspLiveGenHide();
          window.G.pptId = d2.ppt_id || ('ai_' + Date.now());
          window.G.pptData = finalPpt;
          window.G.pptTitle = finalPpt.title;
          if (typeof window.buildEditor === 'function') window.buildEditor(finalPpt);
          if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(finalPpt);
        }
        window.dspLiveGenStream(finalPpt, design, finish);
      })
      .catch(function () {
        removeEl(document.getElementById('dsp-ai-rv-panel'));
        alert('Network error while generating. Please try again.');
      });
    }, function onBack() {
      // Nothing charged, nothing to undo — user just edits the topic/settings and retries.
    }, function costFn(n) { return Math.max(window.DS_MIN_CREDITS || 5, n * (window.DS_CREDITS_PER_SLIDE || 1)); });
  })
  .catch(function () {
    removeEl(loader);
    alert('Network error while previewing. Falling back to standard generation.');
    return _prevDspGen.apply(_outerThis, _outerArgs);
  });
};

})();
</script>
    <?php
}, 1001);
