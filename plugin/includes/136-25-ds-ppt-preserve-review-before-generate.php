<?php
/**
 * Code Snippets export — 25 ds ppt preserve review before generate
 *
 * Snippet ID : 813
 * Active     : yes
 * Priority   : 99
 * Scope      : global
 * Tags       : designstager, ds-order-127
 * Modified   : 2026-09-09 02:42:32
 * Load order : 136 of 144
 */
/**
 * Snippet: 25 DS PPT Preserve Mode - Review Before Generate
 * Run: Everywhere
 * NEW SNIPPET — does not edit Snippet 12B, the Preserve Mode snippet, or
 * Snippet 24 (Preserve AI Rearrange). Loads after all of them.
 *
 * WHAT THIS DOES:
 * Inserts a review step between "Build From My Content" and the actual
 * slide build. Flow becomes:
 *
 *   1. User pastes text, hits "Build From My Content" (Preserve mode).
 *   2. This snippet structures the text into slides — but does NOT
 *      charge credits or save anything yet. It's a preview.
 *   3. User sees a "Review Your Slides" panel: title, subtitle, and every
 *      content point exactly as it was organized, per slide.
 *   4. User picks:
 *        - "Generate Presentation" -> THIS is when credits are charged
 *          and the deck actually gets built (Image 1's editor screen).
 *        - "Back to Edit" -> panel closes, nothing charged, user edits
 *          their pasted text and tries again.
 *
 * REVISION: structuring is now deterministic-first, AI-fallback-second.
 * Testing showed the AI (Groq/Llama) does not reliably follow multi-rule
 * text-structuring instructions — it merged/split content across slide
 * boundaries that were already unambiguous in the source text. Since a
 * "Slide N" marker (colon, dash, or period after the number) is a fact
 * about the text, not something that needs judgement, this is now
 * detected and parsed in plain PHP first — 100% consistent, instant, and
 * free. The AI is only invoked as a fallback when the pasted text has
 * fewer than 2 "Slide N" markers, i.e. text with no explicit structure
 * to fall back on, where some judgement genuinely is required.
 *
 * Two new endpoints, both separate from Snippet 24's paid endpoint:
 *   POST /ds/v1/preserve-preview  — structures text, returns it, FREE.
 *   POST /ds/v1/preserve-confirm  — re-validates the reviewed data,
 *                                   charges credits, saves the job.
 *
 * Falls back to whatever window.dspGen already did (Snippet 24, then the
 * original local-parser Preserve Mode) if the preview call fails outright.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/preserve-preview', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_preserve_preview',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/preserve-confirm', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_preserve_confirm',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_ppt_preserve_sanitize_slides') ) {
function ds_ppt_preserve_sanitize_slides( $raw_slides ) {
    $allowed = array('title','bullet','two_column','three_column','image_text','feature_grid','stats','timeline','quote');
    $clean_slides = array();
    if ( ! is_array($raw_slides) ) return $clean_slides;
    foreach ( $raw_slides as $s ) {
        if ( ! is_array($s) ) continue;
        $type = sanitize_text_field( $s['type'] ?? 'bullet' );
        if ( ! in_array($type, $allowed, true) ) $type = 'bullet';

        $clean_content = array();
        if ( ! empty($s['content']) && is_array($s['content']) ) {
            foreach ( $s['content'] as $c ) {
                $c = sanitize_text_field( (string) $c );
                if ( $c === '' ) continue;
                // Ensure every item has a colon so renderers can split heading : description
                if ( strpos($c, ':') === false ) $c = $c . ': ';
                $clean_content[] = $c;
            }
        }
        if ( $type === 'bullet' && count($clean_content) > 0 ) {
            $cnt = count($clean_content);
            if ($cnt === 2) $type = 'two_column';
            elseif ($cnt === 3) $type = 'three_column';
            elseif ($cnt === 4) $type = 'feature_grid';
        }

        $clean_slides[] = array(
            'type'         => $type,
            'title'        => sanitize_text_field( $s['title'] ?? '' ),
            'subtitle'     => sanitize_text_field( $s['subtitle'] ?? '' ),
            'content'      => $clean_content,
            'image_query'  => '',
            'icon'         => '',
            'icons'        => array(),
        );
    }
    if ( count($clean_slides) > 20 ) $clean_slides = array_slice($clean_slides, 0, 20);
    return $clean_slides;
}
}
// Helper: if an item has no colon, the AI returned a bare heading.
    // Promote it to "Heading: (see slide)" so renderers show something visible
    // rather than a blank explanation zone.
    $ensure_colon = function( $item ) {
        $item = trim((string) $item);
        if ( $item === '' ) return $item;
        if ( strpos($item, ':') === false ) return $item . ': ';
        return $item;
    };

// Simple per-user rate limit so free previews can't be hammered to run up
// Groq costs without ever confirming. 6 previews per rolling 60 seconds.
if ( ! function_exists('ds_ppt_preserve_preview_ratelimit') ) {
function ds_ppt_preserve_preview_ratelimit( $uid ) {
    $key = 'ds_pp_rl_' . $uid;
    $count = (int) get_transient($key);
    if ( $count >= 6 ) return false;
    set_transient($key, $count + 1, 60);
    return true;
}
}

// ===================== Deterministic parser (tried first) =====================
// Ported from the client-side local parser, with two fixes made during
// testing: (1) the "Slide N" marker now accepts colon, hyphen, en-dash,
// em-dash, or period — not just colon, with proper /u (UTF-8) handling
// for the dash characters; (2) the heading/description merge no longer
// fires just because a line is short — only when the NEXT line is not
// already its own complete "Heading: description" pair. That was the
// exact bug that collapsed 4-point slides down to fewer.

if ( ! function_exists('ds_pp_split_colon') ) {
function ds_pp_split_colon( $line ) {
    $i = strpos($line, ':');
    if ( $i === false ) return array('h' => trim($line), 'd' => '');
    return array('h' => trim(substr($line, 0, $i)), 'd' => trim(substr($line, $i + 1)));
}
}

if ( ! function_exists('ds_pp_detect_type') ) {
function ds_pp_detect_type( $items, $slide_title = '', $slide_index = 0 ) {
    $n = count($items);
    if ( $n === 0 ) return $slide_index === 0 ? 'title' : 'bullet';
    $t = strtolower($slide_title);

    // Title keyword wins first
    if ( preg_match('/timeline|history|journey|roadmap|milestone|phase|quarter|schedule/i', $t) ) return 'timeline';
    if ( preg_match('/metric|stat|kpi|number|result|figure|rate|percentage|score|projection|forecast/i', $t) ) return 'stats';
    if ( preg_match('/\bvs\.?\b|versus|compar|pro.?con|advantage|disadvantage/i', $t) ) return 'two_column';
    if ( preg_match('/step|process|workflow|method|pillar|approach|framework/i', $t) ) return 'three_column';
    if ( preg_match('/feature|benefit|service|offering|capability|why.us|what.we|choose/i', $t) ) return 'feature_grid';
    if ( preg_match('/quote|testimonial|said|according|feedback/i', $t) ) return 'quote';

    $all_colon = true;
    foreach ( $items as $it ) { if ( strpos($it, ':') === false ) { $all_colon = false; break; } }
    if ( $all_colon ) {
        $heads = array_map(function($it){ return ds_pp_split_colon($it)['h']; }, $items);
        $all_numeric = true;
        foreach ( $heads as $h ) { if ( ! preg_match('/^[\$€£]?[\d.,]+\s*[%MKBx+]*$/iu', $h) ) { $all_numeric = false; break; } }
        if ( $all_numeric && $n <= 5 ) return 'stats';
        $all_yearish = true;
        foreach ( $heads as $h ) { if ( ! preg_match('/^(19|20)\d{2}|^(Q[1-4]|Phase|Step|Stage|Year|Week|Month)\b/iu', $h) ) { $all_yearish = false; break; } }
        if ( $all_yearish ) return 'timeline';
        if ( $n === 2 ) return 'two_column';
        if ( $n === 3 ) return 'three_column';
        if ( $n >= 4 ) return 'feature_grid';
    }
    if ( $n === 2 ) return 'two_column';
    if ( $n === 3 ) return 'three_column';
    if ( $n === 4 ) return 'feature_grid';
    return 'bullet';
}
}

if ( ! defined('DS_PP_SKIP') ) {
    define('DS_PP_SKIP', '/^(key\s+point|key\s+feature|key\s+takeaway|main\s+point|highlight|overview|summary|agenda|table\s+of\s+content|contact\s+us|get\s+in\s+touch|questions?\??|thank\s+you|next\s+step|action\s+item|note|tip|closing\s+statement|contact|social\s+media|website|email|phone)/iu');
}
if ( ! defined('DS_PP_LABEL') ) {
    define('DS_PP_LABEL', '/^(title|sub\s*-?\s*header|subtitle|heading)\s*:?\s*$/iu');
}

if ( ! function_exists('ds_pp_strip_slide_prefix') ) {
function ds_pp_strip_slide_prefix( $line ) {
    return trim(preg_replace('/^Slide\s+\d+\s*[:\-–—.]?\s*/iu', '', $line));
}
}

if ( ! function_exists('ds_pp_strip_point_prefix') ) {
function ds_pp_strip_point_prefix( $line ) {
    return trim(preg_replace('/^Point\s+\d+\s*:?\s*/iu', '', $line));
}
}

if ( ! function_exists('ds_pp_deterministic_parse') ) {
function ds_pp_deterministic_parse( $text ) {
    $pattern = '/(?:^|\n)\s*Slide\s+\d+\s*[:\-–—.]?\s*/iu';
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
    if ( count($matches[0]) < 2 ) return null; // not enough structure — let the AI handle it

    $offsets = array_map(function($m){ return $m[1]; }, $matches[0]);
    $blocks = array();
    for ( $i = 0; $i < count($offsets); $i++ ) {
        $start = $offsets[$i];
        $end = isset($offsets[$i+1]) ? $offsets[$i+1] : strlen($text);
        $blocks[] = trim(substr($text, $start, $end - $start));
    }

    $slides = array();
    foreach ( $blocks as $bi => $block ) {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), function($l){ return $l !== ''; }));
        if ( empty($lines) ) continue;

        $lines = array_values(array_filter($lines, function($l){ return ! preg_match(DS_PP_LABEL, $l); }));
        if ( empty($lines) ) continue;

        $heading = ds_pp_strip_slide_prefix($lines[0]);
        $rest = array_slice($lines, 1);
        $rest = array_map(function($l){
            $l = preg_replace('/^[-*•►✓]\s*/u', '', $l);
            $l = preg_replace('/^\d+[.)]\s*/u', '', $l);
            return trim($l);
        }, $rest);
        $rest = array_values(array_filter($rest, function($l){ return strlen($l) > 1; }));

        if ( preg_match('/^(title|untitled|slide)$/iu', $heading) && ! empty($rest) ) {
            $heading = array_shift($rest);
        }
        if ( $heading === '' ) continue;

        $subtitle = '';
        if ( ! empty($rest) && strpos($rest[0], ':') === false && str_word_count($rest[0]) <= 14 && ! preg_match(DS_PP_SKIP, $rest[0]) ) {
            $subtitle = array_shift($rest);
        }

        $filtered = array_values(array_filter($rest, function($l){
            $stripped = preg_replace('/^\d+[.)]\s*/u', '', $l);
            return ! preg_match(DS_PP_SKIP, $stripped);
        }));
        $filtered = array_map('ds_pp_strip_point_prefix', $filtered);

        $content = array();
        $fi = 0;
        $fcount = count($filtered);
        while ( $fi < $fcount ) {
            $fl = $filtered[$fi];
            $fn = isset($filtered[$fi+1]) ? $filtered[$fi+1] : '';

            $colon_idx = strpos($fl, ':');
            $has_colon = $colon_idx !== false;
            $after_colon = $has_colon ? trim(substr($fl, $colon_idx + 1)) : '';
            $is_bare_heading = ( ! $has_colon && str_word_count($fl) <= 8 )
                || ( $has_colon && str_word_count($after_colon) <= 1 );

            $next_long = $fn !== '' && str_word_count($fn) >= 4;
            // Only refuse to merge if the NEXT line is itself already a
            // complete "Heading: real description" pair. Being short is
            // not disqualifying — plenty of genuine descriptions are short.
            $fn_colon_idx = strpos($fn, ':');
            $fn_has_colon = $fn_colon_idx !== false;
            $fn_after_colon = $fn_has_colon ? trim(substr($fn, $fn_colon_idx + 1)) : '';
            $next_is_heading = $fn_has_colon && str_word_count($fn_after_colon) >= 2;

            if ( $is_bare_heading && $fn !== '' && $next_long && ! $next_is_heading ) {
                $content[] = rtrim(preg_replace('/:\s*$/u', '', $fl)) . ': ' . $fn;
                $fi += 2;
            } else {
                $content[] = $fl;
                $fi += 1;
            }
        }

        $type = ( $bi === 0 && empty($content) ) ? 'title' : ds_pp_detect_type($content, $heading, $bi);
        $slides[] = array(
            'type'     => $type,
            'title'    => $heading,
            'subtitle' => $subtitle,
            'content'  => $content,
        );
    }

    if ( empty($slides) ) return null;
    return $slides;
}
}


if ( ! function_exists('ds_ppt_preserve_preview') ) {
function ds_ppt_preserve_preview( WP_REST_Request $req ) {
    $uid = get_current_user_id();

    /* Credit-gated, not plan-gated: anyone signed in may generate while they
   have credits. The confirm step still deducts them. */
	$ds_bal = (int) get_user_meta($uid, 'ds_ai_credits', true);
	if ( $ds_bal <= 0 ) {
		return rest_ensure_response(array(
			'error'   => 'no_credits',
			'message' => 'You have no credits left. Add credits to keep generating.',
		));
	}
	if ( ! ds_ppt_preserve_preview_ratelimit($uid) ) {
        return rest_ensure_response(array('error'=>'rate_limited','message'=>'Please wait a moment before previewing again.'));
    }

    $raw_text = wp_strip_all_tags( (string) $req->get_param('text') );
    if ( trim($raw_text) === '' ) {
        return rest_ensure_response(array('error'=>'No text','message'=>'Please paste your presentation text.'));
    }
    if ( strlen($raw_text) > 12000 ) {
        return rest_ensure_response(array('error'=>'Too long','message'=>'Please shorten your text to under 12,000 characters and try again.'));
    }

    $hint = (int) ( $req->get_param('num_slides_hint') ?: 0 );
    $hint = max(0, min(20, $hint));

    // ---- Try deterministic parsing first. If the text has explicit
    // "Slide N" markers, there is nothing for the AI to judge — code
    // handles it 100% consistently and for free. Only fall through to
    // the AI below when this returns null (no clear markers found). ----
    $det_slides = ds_pp_deterministic_parse($raw_text);
    if ( $det_slides !== null ) {
        $clean_slides = ds_ppt_preserve_sanitize_slides($det_slides);
        if ( ! empty($clean_slides) ) {
            $ppt_out = array(
                'title'  => $clean_slides[0]['title'] ?: 'Presentation',
                'slides' => $clean_slides,
            );
            return rest_ensure_response(array(
                'success'  => true,
                'ppt_data' => $ppt_out,
                'cost'     => 8,
                'method'   => 'deterministic',
            ));
        }
        // fall through to AI if sanitizing somehow left nothing usable
    }

    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $api_key = isset($keys['groq']) ? $keys['groq'] : '';
    if ( empty($api_key) ) {
        return rest_ensure_response(array('error'=>'No API key','message'=>'Groq API key missing in snippet 2a (ds_get_api_keys).'));
    }
    $groq_model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');

    $sys_msg = "You are a text-structuring assistant, NOT a content writer. You take a user's own raw, messily-formatted presentation text and reorganize it into a slide JSON structure. "
        . "STRICT RULES: "
        . "1) NEVER invent, add, rewrite, paraphrase, summarize, or embellish any fact, sentence, statistic, or word. Every word of every title, subtitle, and content string in your output must come from the user's original text. "
        . "2) You MAY do light structural cleanup only: (a) merge an obvious short heading line with the description line immediately following it into one 'Heading: Description' string, (b) drop pure structural label lines that carry no content of their own, such as a line that only says 'Title', 'Subtitle', 'Sub Header', 'Heading', or a bare 'Point 1' with nothing else on that line, (c) drop a leading slide-number marker like 'Slide 1', 'Slide 1:', or 'Slide 1 -' from the front of a heading, (d) trim whitespace and stray bullet characters. "
        . "3) Do not drop any real content. If unsure whether a line is a label or real content, KEEP it. "
        . "4) CRITICAL — SLIDE BOUNDARIES ARE FIXED: if you see a marker like 'Slide 8', 'Slide 8:', 'Slide 8 -', or 'Slide 8 –' anywhere, that marker starts a brand new slide. The number of output slides must equal the number of 'Slide N' markers found. "
        . "5) For each slide choose a 'type' AND format content exactly as follows: "
        . "  title: first/cover slide only — empty content array, subtitle from user text. "
        . "  two_column: exactly 2 points — 'Heading: description' each. "
        . "  three_column: exactly 3 points — 'Heading: description' each. "
        . "  feature_grid: exactly 4 points — 'Heading: description' each. "
        . "  bullet: 5 or 6 points — 'Heading: description' each. "
        . "  stats: exactly 3 numeric metrics — 'VALUE: short label' e.g. '94%: Customer satisfaction'. "
        . "  timeline: sequential steps — 'YEAR or PHASE: what happened'. "
        . "  quote: single quote — put full quote in title, attribution in content[0]. "
        . "  CRITICAL TYPE SELECTION: count actual content points per slide: 2=two_column, 3=three_column, 4=feature_grid, 5-6=bullet, sequential dates/phases=timeline, short numbers=stats. NEVER use bullet when 2-4 points fit a grid/column type. "
        . "6) Every slide except title and quote MUST have both 'title' (3-6 words) and 'subtitle' (6-10 words) from the user's own text. "
        . "7) Output ONLY valid JSON, no markdown, no code fences, in this exact shape: "
        . '{"title":"...","slides":[{"type":"two_column","title":"Our Challenges","subtitle":"Key problems we are solving","content":["Challenge 1: Customers face inefficient processes.","Challenge 2: Existing solutions lack flexibility."]},{"type":"three_column","title":"Our Solution","subtitle":"Three pillars of our platform","content":["Speed: Platform processes tasks 3x faster.","Cost: Reduces operational expense by 40%.","Scale: Handles 10,000 concurrent users."]},{"type":"feature_grid","title":"Key Features","subtitle":"Four things that set us apart","content":["AI Engine: Automates complex workflows instantly.","Analytics: Real-time dashboards for all teams.","Security: Bank-grade encryption on every request.","Integrations: Connects with 200+ business tools."]},{"type":"stats","title":"Our Traction","subtitle":"Numbers that tell the story","content":["10,000+: Active users on the platform","94%: Customer satisfaction score","3x: Faster than nearest competitor"]},{"type":"timeline","title":"Our Journey","subtitle":"Key milestones from launch to today","content":["2022: Founded with seed funding of $500K.","2023: Launched beta, onboarded 200 customers.","2024: Series A raise, expanded to 10 markets."]}]}'
        . " EXAMPLE — input: 'Slide 3 – Our Team / Sub Header / Meet the people. / Point 1: Jane Lee / Co-founder, ex-Google. / Point 2: Sam Patel / CTO, ex-Stripe.' "
        . "-> {\"type\":\"two_column\",\"title\":\"Our Team\",\"subtitle\":\"Meet the people.\",\"content\":[\"Jane Lee: Co-founder, ex-Google.\",\"Sam Patel: CTO, ex-Stripe.\"]}";

    $user_msg = "Reorganize the following text into slides. Keep every word from the source text; only restructure it.\n\n";
    if ( $hint > 0 ) {
        $user_msg .= "If the text divides naturally into roughly {$hint} slides, aim for that — but never split or merge real content just to force an exact count.\n\n";
    }
    $user_msg .= "=== SOURCE TEXT ===\n" . $raw_text;

    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'timeout' => 60,
        'headers' => array(
            'Authorization' => 'Bearer '.$api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode(array(
            'model'           => $groq_model,
            'messages'        => array(
                array('role'=>'system','content'=>$sys_msg),
                array('role'=>'user','content'=>$user_msg),
            ),
            'temperature'     => 0.15,
            'top_p'           => 0.9,
            'max_tokens'      => 6000,
            'response_format' => array('type'=>'json_object'),
        )),
    ));

    if ( is_wp_error($resp) ) {
        return rest_ensure_response(array('error'=>'API error','message'=>$resp->get_error_message()));
    }
    $body = wp_remote_retrieve_body($resp);
    $data = json_decode($body, true);
    $content = $data['choices'][0]['message']['content'] ?? '';

    $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
    $content = preg_replace('/\s*```$/', '', $content);
    $ppt = json_decode($content, true);

    if ( ! $ppt || empty($ppt['slides']) ) {
        return rest_ensure_response(array('error'=>'Parse failed','message'=>'AI could not structure this text. Try again, or use manual formatting.'));
    }

    $clean_slides = ds_ppt_preserve_sanitize_slides($ppt['slides']);
    if ( empty($clean_slides) ) {
        return rest_ensure_response(array('error'=>'Parse failed','message'=>'AI returned no usable slides.'));
    }

    $ppt_out = array(
        'title'  => sanitize_text_field( $ppt['title'] ?? ( $clean_slides[0]['title'] ?: 'Presentation' ) ),
        'slides' => $clean_slides,
    );

    // No credit deduction, no job save — this is just a preview.
    return rest_ensure_response(array(
        'success'  => true,
        'ppt_data' => $ppt_out,
        'cost'     => 8, // shown to the user so they know the price before confirming
    ));
}
}

if ( ! function_exists('ds_ppt_preserve_confirm') ) {
function ds_ppt_preserve_confirm( WP_REST_Request $req ) {
    $uid = get_current_user_id();

    /* Credit-gated, not plan-gated: anyone signed in may generate while they
   have credits. The confirm step still deducts them. */
	$ds_bal = (int) get_user_meta($uid, 'ds_ai_credits', true);
	if ( $ds_bal <= 0 ) {
		return rest_ensure_response(array(
			'error'   => 'no_credits',
			'message' => 'You have no credits left. Add credits to keep generating.',
		));
	}
	$ppt_data = $req->get_param('ppt_data');
    if ( ! is_array($ppt_data) || empty($ppt_data['slides']) ) {
        return rest_ensure_response(array('error'=>'No data','message'=>'Nothing to generate. Please build a preview first.'));
    }

    // Never trust the client's reviewed data blindly — re-run the exact
    // same sanitize/type-fix rules used for the preview.
    $clean_slides = ds_ppt_preserve_sanitize_slides($ppt_data['slides']);
    if ( empty($clean_slides) ) {
        return rest_ensure_response(array('error'=>'No data','message'=>'No valid slides to generate.'));
    }

    $ppt_out = array(
        'title'  => sanitize_text_field( $ppt_data['title'] ?? ( $clean_slides[0]['title'] ?: 'Presentation' ) ),
        'slides' => $clean_slides,
    );

    // Cost is fixed server-side, same as the preview quoted — never trust
    // a client-supplied amount.
    $credits = (int) get_user_meta($uid, 'ds_ai_credits', true);
    $cost = ds_ppt_credit_cost( count( $clean_slides ), 'preserve' );
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
    array_unshift($ppt_log, array('date'=>current_time('mysql'), 'prompt'=>'Preserve My Text (reviewed)', 'credits'=>$cost, 'num_slides'=>count($ppt_out['slides'])));
    update_user_meta($uid, 'ds_ppt_generations', array_slice($ppt_log, 0, 100));

    return rest_ensure_response(array(
    'success'         => true,
    'ppt_id'          => $id,
    'ppt_data'        => $ppt_out,
    'credits_balance' => (int) get_user_meta($uid, 'ds_ai_credits', true),
));
}
}

/* ===== Client-side: wrap window.dspGen a third time, Preserve-mode only ===== */
add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<script>
(function () {
'use strict';

var _prevDspGen = window.dspGen; // Snippet 24, then the original Preserve/AI logic beneath it
if (!_prevDspGen) return;

var BASE = window.PURL || '';
var PREVIEW_URL = BASE.replace('generate-ppt', 'preserve-preview');
var CONFIRM_URL = BASE.replace('generate-ppt', 'preserve-confirm');

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
  feature_grid: '2x2 Grid', stats: 'Stats', timeline: 'Timeline', quote: 'Quote', image_text: 'Image + Text'
};

function showPreviewLoader() {
  var el = document.createElement('div');
  el.id = 'dsp-rv-loading';
  el.style.cssText = 'position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  el.innerHTML =
    '<div style="background:#111827;border:1px solid #2a2a2a;border-radius:12px;padding:32px 40px;text-align:center;color:#e5e7eb;">' +
      '<div style="width:36px;height:36px;border:3px solid #2a2a2a;border-top-color:#4A9EFF;border-radius:50%;margin:0 auto 16px;animation:dsprvspin 0.8s linear infinite;"></div>' +
      '<div style="font-weight:700;font-size:15px;">Organizing your content&hellip;</div>' +
      '<div style="color:#9ca3af;font-size:12.5px;margin-top:4px;">This is just a preview — nothing is charged yet.</div>' +
    '</div>' +
    '<style>@keyframes dsprvspin{to{transform:rotate(360deg);}}</style>';
  document.body.appendChild(el);
  return el;
}

function removeEl(el) { if (el && el.parentNode) el.parentNode.removeChild(el); }

function recalcType(s) {
  if (s._typeFixed) return;
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
  overlay.id = 'dsp-rv-panel';
  overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;padding:24px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';

  overlay.innerHTML =
    '<div style="background:#161b22;border:1px solid #2a2a2a;border-radius:14px;max-width:720px;width:100%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">' +
      '<div style="padding:22px 26px;border-bottom:1px solid #21262d;">' +
        '<div style="color:#f9fafb;font-weight:800;font-size:19px;">Review Your Slides</div>' +
        '<div style="color:#9ca3af;font-size:13px;margin-top:3px;">AI organized your text below, using only your own words. Edit, delete a slide, or add/remove points, then confirm.</div>' +
      '</div>' +
      '<div id="dsp-rv-list" style="padding:20px 26px;overflow-y:auto;flex:1;"></div>' +
      '<div style="padding:16px 26px;border-top:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;gap:12px;">' +
        '<button id="dsp-rv-back" style="background:transparent;border:1px solid #30363d;color:#c9d1d9;border-radius:8px;padding:11px 18px;font-weight:700;font-size:13px;cursor:pointer;">&larr; Back to Edit</button>' +
        '<button id="dsp-rv-confirm" style="background:#2563eb;border:none;color:#fff;border-radius:8px;padding:11px 22px;font-weight:800;font-size:13px;cursor:pointer;">&#10003; Generate Presentation &middot; ' + cost + ' credits</button>' +
      '</div>' +
    '</div>';
  document.body.appendChild(overlay);

  var listEl = document.getElementById('dsp-rv-list');

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

    var confirmBtn = document.getElementById('dsp-rv-confirm');
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

  // Text edits update the data model only — no re-render, so the cursor
  // and focus are never disturbed while typing.
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

  document.getElementById('dsp-rv-back').onclick = function () { removeEl(overlay); onBack(); };
  document.getElementById('dsp-rv-confirm').onclick = function () {
    document.getElementById('dsp-rv-confirm').textContent = 'Generating…';
    document.getElementById('dsp-rv-confirm').disabled = true;
    // Re-apply smart layout types before confirming
    if (typeof window.dspPreserveParse === 'function' && ppt && ppt.slides) {
      var _prompt = (document.getElementById('dsp-prompt')||{}).value||'';
      var _retyped = window.dspPreserveParse(_prompt);
      _retyped.forEach(function(_s,_i){ if(ppt.slides[_i]) ppt.slides[_i].type = _s.type; });
    }
    onConfirm();
  };
}

window.dspGen = function () {
  var _outerThis = this, _outerArgs = arguments;

  if (typeof window.G === 'undefined' || window.G.mode !== 'preserve') {
    return _prevDspGen.apply(_outerThis, _outerArgs);
  }

  var promptEl = document.getElementById('dsp-prompt');
  var prompt = promptEl ? promptEl.value.trim() : '';
  if (!prompt) {
    if (promptEl) promptEl.focus();
    return;
  }

  var slidesInput = document.getElementById('dsp-slides');
  var hint = slidesInput ? (parseInt(slidesInput.value, 10) || 0) : 0;
  var design = (document.getElementById('dsp-design') || {}).value || 'navy-pro';

  var loader = showPreviewLoader();

  fetch(PREVIEW_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
    body: JSON.stringify({ text: prompt, num_slides_hint: hint })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    removeEl(loader);
    if (!d || d.error || !d.ppt_data) {
      alert((d && d.message) ? d.message : 'Preview failed. Falling back to manual formatting.');
      return _prevDspGen.apply(_outerThis, _outerArgs);
    }

    var ppt = d.ppt_data;
    var cost = d.cost || 8;

    showReviewPanel(ppt, cost, function onConfirm() {
      fetch(CONFIRM_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
        body: JSON.stringify({ ppt_data: ppt, slides: ppt.slides, design: (document.getElementById('dsp-design')||{}).value||'navy-pro', title: ppt.title||'Presentation' })
      })
      .then(function (r) { return r.json(); })
      .then(function (d2) {
        var panel = document.getElementById('dsp-rv-panel');
        removeEl(panel);
        if (!d2 || d2.error || (!d2.ppt_data && !d2.url)) {
          alert((d2 && d2.message) ? d2.message : 'Could not generate the presentation. Please try again.');
          return;
        }
        if (d2.credits_balance !== undefined) {
          window.G.credits = d2.credits_balance;
          var ce = document.getElementById('ds-cr-display');
          if (ce) ce.textContent = window.G.credits.toLocaleString();
        }
        var finalPpt = d2.ppt_data;
        window.dspLiveGenShow(prompt, finalPpt.slides.length, design);
        var bannerTxt = document.getElementById('dsp-lg-banner-txt');
        if (bannerTxt) bannerTxt.innerHTML = '<b style="color:#111;">Building Your Slides:</b> Turning your reviewed content into a deck.';

        // Call build-pptx-v2 — returns a URL directly like AI mode does
        var bldUrl = window.DSP_BLD_URL || window.BLDURL || '/wp-json/ds/v1/build-pptx-v2';
        fetch(bldUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
          body: JSON.stringify({
            slides: finalPpt.slides,
            design: design,
            title: finalPpt.title,
            slide_variants: {}
          })
        })
        .then(function(r){ return r.json(); })
        .then(function(bd){
          // Store the built PPTX URL so download button uses it
          if (bd && bd.url) {
            window.G.preserveDownloadUrl = bd.url;
            window.G.preserveDownloadFilename = bd.filename || 'presentation.pptx';
          }
          // Open editor with the correctly typed slides
          function finish() {
            window.dspLiveGenHide();
            window.G.pptId = d2.ppt_id || ('preserve_' + Date.now());
            window.G.pptData = finalPpt;
            window.G.pptTitle = finalPpt.title;
            if (typeof window.buildEditor === 'function') window.buildEditor(finalPpt);
            if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(finalPpt);
          }
          window.dspLiveGenStream(finalPpt, design, finish);
        })
        .catch(function(){
          function finish() {
            window.dspLiveGenHide();
            window.G.pptId = d2.ppt_id || ('preserve_' + Date.now());
            window.G.pptData = finalPpt;
            window.G.pptTitle = finalPpt.title;
            if (typeof window.buildEditor === 'function') window.buildEditor(finalPpt);
            if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(finalPpt);
          }
          window.dspLiveGenStream(finalPpt, design, finish);
        });
      })
      .catch(function () {
        removeEl(document.getElementById('dsp-rv-panel'));
        alert('Network error while generating. Please try again.');
      });
    }, function onBack() {
      // Nothing charged, nothing to undo — user just edits the topic/settings and retries.
    }, function costFn(n) { return Math.max(window.DS_MIN_CREDITS||5, n*(window.DS_CREDITS_PER_SLIDE||1)); });
  })
  .catch(function () {
    removeEl(loader);
    alert('Network error while previewing. Falling back to manual formatting.');
    return _prevDspGen.apply(_outerThis, _outerArgs);
  });
};

})();
</script>
    <?php
}, 1000);


/* =====================================================================
 * Sample text file for Preserve My Text.
 *
 * People paste text that the preserve parser cannot read, so the card
 * now carries a small "Sample format" chip that downloads a .txt laid
 * out the way the parser expects: "Slide N: Title", a subtitle line,
 * then "Label:" + description pairs, blank line between blocks.
 * ===================================================================== */
if ( ! function_exists('ds_ppt_preserve_sample_text') ) {

	function ds_ppt_preserve_sample_text() {
		return <<<'DSSAMPLETXT'
Slide 1: Business Growth Strategy
Building a scalable and sustainable path for future growth


Slide 2: Business Overview
Understanding the foundation of the business

Vision:
Creating meaningful solutions that deliver long-term value.

Mission:
Combining innovation, quality, and customer-focused strategies.


Slide 3: Market Opportunity
Identifying opportunities for sustainable growth

Growing Demand:
Increasing demand is creating new opportunities across the market.

Customer Needs:
Customers are looking for faster, simpler, and more effective solutions.

Market Expansion:
New segments and regions provide opportunities for business expansion.


Slide 4: Key Business Challenges
Addressing the factors that can impact growth

Competition:
Increasing competition requires stronger differentiation.

Customer Retention:
Building long-term relationships is essential for sustainable revenue.

Operational Efficiency:
Efficient processes can reduce costs and improve productivity.

Technology Adoption:
Keeping pace with new technology is important for staying competitive.


Slide 5: Growth Strategy
Key areas that can accelerate business development

Product Innovation:
Develop solutions that respond to changing customer expectations.

Customer Experience:
Create simple and consistent experiences across every interaction.

Digital Marketing:
Use targeted digital channels to reach relevant audiences.

Strategic Partnerships:
Collaborate with complementary businesses to expand capabilities.

Market Expansion:
Enter new customer segments and geographic markets strategically.


Slide 6: Customer Acquisition
Building a consistent pipeline of new customers

Audience Research:
Understand customer needs, behaviors, and purchasing decisions.

Content Marketing:
Create useful content that attracts and educates potential customers.

Social Media:
Build awareness through relevant and engaging social content.

Paid Advertising:
Reach specific audiences through targeted advertising campaigns.

Lead Generation:
Convert interest into qualified leads through effective landing pages.

Conversion Optimization:
Improve the customer journey to increase conversion opportunities.


Slide 7: Digital Transformation
Using technology to create a more efficient business

Cloud Infrastructure:
Build flexible systems that can scale with business requirements.

Automation:
Automate repetitive processes to save time and reduce manual work.

Data Analytics:
Use business data to identify trends and support better decisions.

Artificial Intelligence:
Apply AI to improve productivity, personalization, and innovation.

Customer Platforms:
Provide customers with convenient digital tools and services.

Process Integration:
Connect systems to create smoother workflows across departments.

Security:
Protect business systems and customer information with appropriate controls.

Slide 8: Performance Management
Measuring progress through meaningful business metrics

Revenue Growth:
Track revenue performance and identify major growth drivers.

Customer Growth:
Monitor new customers and overall customer acquisition.

Retention Rate:
Measure how effectively the business retains existing customers.

Conversion Rate:
Evaluate how efficiently prospects become paying customers.

Customer Value:
Understand the long-term value generated by each customer.

Marketing Performance:
Measure the effectiveness of campaigns and acquisition channels.

Operational Efficiency:
Track productivity, costs, and resource utilization.

Business Profitability:
Monitor margins and profitability to support sustainable growth.


Slide 9: Implementation Roadmap
A structured approach to turning strategy into action

Define Goals:
Establish clear and measurable business objectives.

Analyze Current State:
Identify existing strengths, weaknesses, and opportunities.

Prioritize Initiatives:
Focus resources on the activities with the greatest potential impact.

Allocate Resources:
Assign the required people, budget, and technology.

Build the Plan:
Create timelines, responsibilities, and measurable milestones.

Launch Initiatives:
Begin executing the highest-priority activities.

Track Progress:
Monitor performance against established objectives.

Optimize Results:
Make adjustments based on performance and customer feedback.

Scale Success:
Expand initiatives that demonstrate strong and sustainable results.


Slide 10: Future Growth Priorities
Key areas to focus on for long-term success

Customer Focus:
Keep customer needs at the center of business decisions.

Innovation:
Continuously improve products, services, and processes.

Technology:
Adopt relevant technologies that improve efficiency and value.

Brand Building:
Strengthen brand visibility, trust, and market recognition.

Talent Development:
Invest in skills, creativity, and employee growth.

Operational Excellence:
Build efficient processes that support consistent performance.

Strategic Partnerships:
Develop partnerships that create new capabilities and opportunities.

Market Expansion:
Explore new segments, products, and geographic opportunities.

Sustainable Growth:
Balance growth with profitability, efficiency, and long-term planning.

Continuous Improvement:
Use data and feedback to continuously refine the business strategy.
DSSAMPLETXT;
	}

	/* /?ds_sample_format=1 -> downloads the sample as a .txt */
	add_action('init', function () {
		if ( empty($_GET['ds_sample_format']) ) {
			return;
		}
		nocache_headers();
		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Disposition: attachment; filename="DesignStager-Preserve-Text-Sample.txt"');
		header('X-Content-Type-Options: nosniff');
		echo ds_ppt_preserve_sample_text();
		exit;
	}, 0);

	add_action('wp_footer', function () {
		?>
		<style>
		#dsp-m-keep{position:relative;padding-bottom:14px}
		.dsp-sample-dl{display:flex;width:fit-content;align-items:center;gap:6px;margin:12px 0 0;
		 padding:5px 11px;border-radius:999px;border:1px solid rgba(255,255,255,.28);
		 background:rgba(255,255,255,.06);color:#dbe4ff;font-size:11.5px;line-height:1.1;
		 text-decoration:none;white-space:nowrap}
		.dsp-sample-dl:hover{background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.5)}
		.dsp-sample-dl svg{width:12px;height:12px}
		@media (max-width:640px){.dsp-sample-dl{font-size:11px;padding:5px 9px}}
		</style>
		<script>
		(function () {
		  function add() {
		    var card = document.getElementById('dsp-m-keep');
		    if (!card || card.querySelector('.dsp-sample-dl')) { return; }
		    var a = document.createElement('a');
		    a.className = 'dsp-sample-dl';
		    a.href = '/?ds_sample_format=1';
		    a.setAttribute('download', 'DesignStager-Preserve-Text-Sample.txt');
		    a.title = 'Download a sample .txt showing how to lay out your text';
		    a.innerHTML = '<span>Sample format</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="M7 12l5 5 5-5"/><path d="M4 20h16"/></svg>';
		    a.addEventListener('click', function (e) { e.stopPropagation(); });
		    card.appendChild(a);
		  }
		  if (document.readyState === 'loading') {
		    document.addEventListener('DOMContentLoaded', add);
		  } else { add(); }
		  setTimeout(add, 1200);
		  setTimeout(add, 3000);
		})();
		</script>
		<?php
	}, 1100);
}
