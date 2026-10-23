<?php
/**
 * Code Snippets export — 24 ds ppt preserve ai rearrange [CLONE] [Credit-gated instead of Plan-gated]
 *
 * Snippet ID : 814
 * Active     : yes
 * Priority   : 999
 * Scope      : global
 * Tags       : designstager, ds-order-128
 * Modified   : 2026-09-09 02:45:13
 * Load order : 137 of 144
 */
/**
 * Snippet: 24 DS PPT Preserve Mode - AI Rearrange
 * Run: Everywhere
 * NEW SNIPPET — does not edit Snippet 12B, the Preserve Mode snippet, or
 * any other existing snippet.
 *
 * WHAT THIS DOES:
 * Adds a second way to run "Preserve My Text": instead of the local
 * JS regex parser (which only understands text shaped a very specific
 * way), this sends the user's raw pasted text to the AI with strict
 * instructions to REORGANIZE ONLY — never invent, rewrite, or add
 * content. Any input pattern (dashes, colons, paragraphs, stray labels
 * like "Title"/"Sub Header", etc.) gets handled correctly because the
 * AI is doing the structural reasoning instead of a fixed regex.
 *
 * This snippet must load AFTER the existing Preserve Mode snippet's
 * wp_footer script so it can wrap window.dspGen a second time. That's
 * handled here with priority 999 on the wp_footer hook — no manual
 * snippet reordering needed.
 *
 * If the AI call fails for any reason (no API key, network error,
 * unparsable response), this automatically falls back to whatever
 * window.dspGen already did before (the existing local-parser Preserve
 * Mode, or the original AI-topic generator) so nothing regresses.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/preserve-ai-rearrange', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_preserve_ai_rearrange',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_ppt_preserve_ai_rearrange') ) {
function ds_ppt_preserve_ai_rearrange( WP_REST_Request $req ) {
    $uid = get_current_user_id();

    // Credit-based gate: any plan may use AI-assisted Preserve Mode as
    // long as the account has enough credits to cover the Groq call.
    // (Actual credit deduction for the generated deck still happens at
    // build-pptx-v2, same as every other generation path.)
    $credits = function_exists('ds_get_credits')
        ? (int) ds_get_credits($uid)
        : (int) get_user_meta($uid, 'ds_ai_credits', true);
    $min_cost = function_exists('ds_ppt_credit_cost') ? (int) ds_ppt_credit_cost(1, 'ai') : 5;
    if ( $credits < $min_cost ) {
        return rest_ensure_response(array(
            'error'   => 'insufficient_credits',
            'message' => 'You need at least '.$min_cost.' credits to use AI-assisted formatting. You currently have '.$credits.'.',
        ));
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

    // Cost is computed server-side, never trusted from the client — same
    // rule Snippet 12B follows for full generation. This is priced well
    // below full generation (num_slides * 4) since it never invents new
    // content, only restructures what's already there. Adjust to match
    // whatever window.dspUpdCost() shows in the UI for Preserve Mode so
    // the displayed price and the actual charge stay in sync.
    $credits = (int) get_user_meta($uid, 'ds_ai_credits', true);
    $cost = ds_ppt_credit_cost( $hint ?: 5, 'preserve' );
    if ( $credits < $cost ) {
        return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
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
        . "4) Detect slide boundaries from whatever structure is present — explicit 'Slide N' markers in any punctuation style, markdown headings, or blank-line paragraph breaks — and use your judgement on how the material naturally divides. "
        . "5) For each slide choose a 'type' AND format content exactly as follows: "
        . "  title: first/cover slide only — empty content array, strong subtitle from user text. "
        . "  two_column: exactly 2 points — format each as 'Heading: description'. "
        . "  three_column: exactly 3 points — format each as 'Heading: description'. "
        . "  feature_grid: exactly 4 points — format each as 'Heading: description'. "
        . "  bullet: 5 or 6 points — format each as 'Heading: description'. "
        . "  stats: exactly 3 short numeric metrics — format each as 'VALUE: short label' e.g. '94%: Customer satisfaction'. "
        . "  timeline: 4-8 sequential steps or phases — format each as 'YEAR or PHASE: what happened'. "
        . "  quote: single quote — put full quote text in title field, attribution in content[0]. "
        . "  CRITICAL TYPE SELECTION: count the real content points in each slide section, then: 2 points=two_column, 3 points=three_column, 4 points=feature_grid, 5+ points=bullet. If points look like dates/phases in sequence=timeline. If points are short numbers/percentages=stats. Never use bullet when 2-4 points fit a grid/column type. "
        . "6) Every slide except title and quote MUST have both a 'title' (3-6 words) and a 'subtitle' (6-10 words) from the user's own text. "
        . "7) Output ONLY valid JSON, no markdown, no code fences, in this exact shape: "
        . '{"title":"...","slides":[{"type":"two_column","title":"Our Challenges","subtitle":"Key problems we are solving","content":["Challenge 1: Customers face inefficient processes.","Challenge 2: Existing solutions lack flexibility."]},{"type":"three_column","title":"Our Solution","subtitle":"Three pillars of our platform","content":["Speed: Platform processes tasks 3x faster.","Cost: Reduces operational expense by 40%.","Scale: Handles 10,000 concurrent users."]},{"type":"feature_grid","title":"Key Features","subtitle":"Four things that set us apart","content":["AI Engine: Automates complex workflows instantly.","Analytics: Real-time dashboards for all teams.","Security: Bank-grade encryption on every request.","Integrations: Connects with 200+ business tools."]},{"type":"stats","title":"Our Traction","subtitle":"Numbers that tell the story","content":["10,000+: Active users on the platform","94%: Customer satisfaction score","3x: Faster than nearest competitor"]},{"type":"timeline","title":"Our Journey","subtitle":"Key milestones from launch to today","content":["2022: Founded with seed funding of $500K.","2023: Launched beta, onboarded 200 customers.","2024: Series A raise, expanded to 10 markets."]},{"type":"quote","title":"The best products solve real problems for real people.","subtitle":"","content":["Jane Lee, CEO, AcmeCorp"]}]}';

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

    // Sanitize — same allowed-type / auto-fix-by-count rules as
    // ds_ppt_v3_generate in Snippet 12B, kept consistent on purpose.
    $allowed = array('title','bullet','two_column','three_column','image_text','feature_grid','stats','timeline','quote');
    $clean_slides = array();
    foreach ( $ppt['slides'] as $s ) {
        if ( ! is_array($s) ) continue;
        $type = sanitize_text_field( $s['type'] ?? 'bullet' );
        if ( ! in_array($type, $allowed, true) ) $type = 'bullet';

        $clean_content = array();
        if ( ! empty($s['content']) && is_array($s['content']) ) {
            foreach ( $s['content'] as $c ) {
                $c = sanitize_text_field( (string) $c );
                if ( $c === '' ) continue;
                if ( strpos($c, ':') === false ) $c = $c . ': ';
                $clean_content[] = $c;
            }
        }
        if ( in_array($type, array('two_column','three_column','bullet','feature_grid'), true) && count($clean_content) > 0 ) {
            $cnt = count($clean_content);
            if ($cnt === 2) $type = 'two_column';
            elseif ($cnt === 3) $type = 'three_column';
            elseif ($cnt === 4) $type = 'feature_grid';
            elseif ($cnt >= 5) $type = 'bullet';
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
    if ( empty($clean_slides) ) {
        return rest_ensure_response(array('error'=>'Parse failed','message'=>'AI returned no usable slides.'));
    }
    if ( count($clean_slides) > 20 ) $clean_slides = array_slice($clean_slides, 0, 20);

    $ppt_out = array(
        'title'  => sanitize_text_field( $ppt['title'] ?? ( $clean_slides[0]['title'] ?: 'Presentation' ) ),
        'slides' => $clean_slides,
    );

    if ( ! ds_deduct_credits($uid, $cost) ) {
        return rest_ensure_response(array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.'));
    }

    $id = 'ppt_'.time().'_'.wp_generate_password(8, false, false);
    $jobs = get_user_meta($uid, 'ds_ppt_jobs', true) ?: array();
    array_unshift($jobs, array('id'=>$id, 'title'=>$ppt_out['title'], 'slides'=>count($ppt_out['slides']), 'time'=>time()));
    update_user_meta($uid, 'ds_ppt_jobs', array_slice($jobs, 0, 30));

    $ppt_log = get_user_meta($uid, 'ds_ppt_generations', true) ?: array();
    array_unshift($ppt_log, array('date'=>current_time('mysql'), 'prompt'=>'Preserve My Text (AI-rearranged)', 'credits'=>$cost, 'num_slides'=>count($ppt_out['slides'])));
    update_user_meta($uid, 'ds_ppt_generations', array_slice($ppt_log, 0, 100));

    return rest_ensure_response(array(
        'success'         => true,
        'ppt_id'          => $id,
        'ppt_data'        => $ppt_out,
        'credits_balance' => (int) get_user_meta($uid, 'ds_ai_credits', true),
    ));
}
}

/* ===== Client-side: wrap window.dspGen a second time, Preserve-mode only ===== */
add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<script>
(function () {
'use strict';

var _prevDspGen = window.dspGen; // whatever Preserve Mode / AI mode already does
if (!_prevDspGen) return;

var AI_URL = (window.PURL || '').replace('generate-ppt', 'preserve-ai-rearrange');

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

  window.dspLiveGenShow(prompt, hint || 10, design);
  var bannerTxt = document.getElementById('dsp-lg-banner-txt');
  if (bannerTxt) bannerTxt.innerHTML = '<b style="color:#111;">Formatting Your Content:</b> AI is organizing your exact words into slides.';

  fetch(AI_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
    body: JSON.stringify({ text: prompt, num_slides_hint: hint })
  })
  .then(function (r) { return r.json(); })
  .then(function (d) {
    if (!d || d.error || !d.ppt_data) {
      window.dspLiveGenHide();
      alert((d && d.message) ? d.message : 'AI formatting failed. Falling back to manual formatting.');
      return _prevDspGen.apply(_outerThis, _outerArgs);
    }
    if (d.credits_balance !== undefined) {
      window.G.credits = d.credits_balance;
      var ce = document.getElementById('ds-cr-display');
      if (ce) ce.textContent = window.G.credits.toLocaleString();
    }
    var ppt = d.ppt_data;
    function finish() {
      window.dspLiveGenHide();
      window.G.pptId = d.ppt_id || ('preserve_' + Date.now());
      window.G.pptData = ppt;
      window.G.pptTitle = ppt.title;
      if (typeof window.buildEditor === 'function') window.buildEditor(ppt);
      if (typeof window.dspSyncPreserveData === 'function') window.dspSyncPreserveData(ppt);
    }
    // Route through build-pptx-v2 so layouts render correctly in PPTX
    var bldUrl = window.DSP_BLD_URL || window.BLDURL || '/wp-json/ds/v1/build-pptx-v2';
    fetch(bldUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.NONCE },
      body: JSON.stringify({ slides: ppt.slides, design: design, title: ppt.title, slide_variants: {} })
    })
    .then(function(r){ return r.json(); })
    .then(function(bd){
      if (bd && bd.url) {
        // Store the pre-built URL so download button uses it directly
        window.G.preserveDownloadUrl = bd.url;
        window.G.preserveDownloadFilename = bd.filename || 'presentation.pptx';
      }
      window.dspLiveGenStream(ppt, design, finish);
    })
    .catch(function(){
      window.dspLiveGenStream(ppt, design, finish);
    });
  })
  .catch(function () {
    window.dspLiveGenHide();
    alert('Network error while formatting with AI. Falling back to manual formatting.');
    return _prevDspGen.apply(_outerThis, _outerArgs);
  });
};

})();
</script>
    <?php
}, 999);
