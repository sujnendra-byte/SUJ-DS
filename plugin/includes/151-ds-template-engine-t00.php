<?php
/**
 * Code Snippets export — DS Template Engine (T00) — design templates for AI PPT [CLONE] [Template editing: 9-16 points + Logo]
 *
 * Snippet ID : 952
 * Active     : yes
 * Priority   : 9
 * Scope      : global
 * Tags       : ds-templates
 * Modified   : 2026-10-15 15:46:31 (V41 update from staging)
 * Load order : 142 of 257
 */
/**
 * DS Template Engine (T00) — shared engine for the design-template snippets
 * Run: Everywhere
 *
 * What it does
 *  - Keeps a registry of design templates. Each template lives in its OWN
 *    snippet ("DS Template 01 — …", "DS Template 02 — …") and registers its
 *    own generate + build functions with ds_tpl_register().
 *  - When the user picks a template, requests to /ds/v1/ai-preview,
 *    /ds/v1/ai-confirm, /ds/v1/generate-ppt and /ds/v1/build-pptx-v2 carry
 *    tpl=<id>. The request is handed to THAT template's snippet; Classic
 *    decks (no tpl) still go to 12b / 26 / 16 exactly as before.
 *  - Provides the shared helpers the templates use: Groq generation,
 *    credit charging and a PPTX writer that turns the layout elements
 *    (the same ones the editor preview shows) into PowerPoint shapes.
 *  - Prints the template engine JavaScript (picker, renderer, routing).
 *
 * Requires: 1a (ds_ppt_credit_cost / ds_deduct_credits), 2a (ds_get_api_keys).
 * Undo: deactivate this snippet — every template snippet stands down too.
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! defined('DS_TPL_ENGINE') ) {
define('DS_TPL_ENGINE', '1.0.0');

/* ---------------------------------------------------------------
 * Registry
 * ------------------------------------------------------------- */
function ds_tpl_register( $id, $cfg ) {
    if ( ! isset($GLOBALS['ds_tpl_registry']) ) $GLOBALS['ds_tpl_registry'] = array();
    $id = sanitize_key($id);
    $cfg['id'] = $id;
    $GLOBALS['ds_tpl_registry'][$id] = $cfg;
}
function ds_tpl_get( $id ) {
    $id = sanitize_key((string)$id);
    return ( $id && isset($GLOBALS['ds_tpl_registry'][$id]) ) ? $GLOBALS['ds_tpl_registry'][$id] : null;
}

/* ---------------------------------------------------------------
 * Dispatcher: send template requests to the template's own snippet
 * ------------------------------------------------------------- */
add_filter('rest_pre_dispatch', function( $result, $server, $request ) {
    if ( $result !== null ) return $result;
    $route = $request->get_route();
    if ( strpos($route, '/ds/v1/') !== 0 ) return $result;
    $map = array(
        '/ds/v1/ai-preview'    => 'preview',
        '/ds/v1/ai-confirm'    => 'confirm',
        '/ds/v1/generate-ppt'  => 'generate',
        '/ds/v1/build-pptx-v2' => 'build',
        '/ds/v1/build-pptx'    => 'build',
    );
    if ( ! isset($map[$route]) || strtoupper($request->get_method()) !== 'POST' ) return $result;
    $tpl_id = $request->get_param('tpl');
    if ( ! $tpl_id && $map[$route] === 'confirm' ) {
        $pd = $request->get_param('ppt_data');
        if ( is_array($pd) && ! empty($pd['tpl']) ) $tpl_id = $pd['tpl'];
    }
    if ( ! $tpl_id && $map[$route] === 'generate' ) {          // Add Slide (snippet 17 uses the native fetch, so the tag rides on "design")
        $dz = (string) $request->get_param('design');
        if ( strpos($dz, 'tpl:') === 0 ) $tpl_id = sanitize_key(substr($dz, 4));
    }
    $T = ds_tpl_get($tpl_id);
    if ( ! $T ) return $result;                        // Classic deck: untouched
    if ( ! is_user_logged_in() ) {
        return new WP_Error('rest_forbidden', 'Please log in.', array('status' => 401));
    }
    $mode = $map[$route];
    if ( $mode === 'generate' && $request->get_param('deduct_only') ) return $result; // Preserve-mode charge stays in 12b
    $cb = ( $mode === 'build' ) ? ( $T['build'] ?? 'ds_tpl_build' ) : ( $T['generate'] ?? 'ds_tpl_generate' );
    if ( ! is_callable($cb) ) return $result;
    /* Pictures the site keeps on the server arrive here as short "dsimg:" references (the save / download
       off-loader). This filter runs before the one that turns them back into pictures, so do it here:
       without this a template .pptx came out with empty picture areas. */
    if ( $mode === 'build' && function_exists('ds_pptimg_hydrate_slides') ) {
        $ds_sl = $request->get_param('slides'); $ds_uid = get_current_user_id();
        if ( is_array($ds_sl) ) {
            if ( function_exists('ds_pptimg_missing') ) {
                $ds_miss = ds_pptimg_missing($ds_uid, $ds_sl);
                if ( $ds_miss ) return rest_ensure_response(array('success' => false, 'error' => 'Some images need to be re-uploaded', 'missing_images' => $ds_miss));   /* the browser uploads them and asks again */
            }
            $request->set_param('slides', ds_pptimg_hydrate_slides($ds_uid, $ds_sl));
        }
    }
    try {
        $out = ( $mode === 'build' ) ? call_user_func($cb, $request, $T) : call_user_func($cb, $request, $T, $mode);
    } catch ( Throwable $e ) {
        error_log('[DS TPL] ' . $T['id'] . ' ' . $mode . ' failed: ' . $e->getMessage() . ' @' . $e->getLine());
        return rest_ensure_response(array('success' => false, 'error' => 'Template error', 'message' => 'Something went wrong while building this template. Please try again.'));
    }
    return rest_ensure_response($out);
}, 5, 3);

/* ---------------------------------------------------------------
 * GENERATION
 * ------------------------------------------------------------- */
function ds_tpl_slide_plan( $n, $T ) {
    $n = max(1, min(20, (int)$n));
    $plan = array();
    for ( $i = 1; $i <= $n; $i++ ) $plan[$i] = 'content';
    $plan[1] = 'title';
    if ( $n >= 6 ) $plan[2] = 'agenda';
    if ( $n >= 4 ) $plan[$n] = 'closing';
    $mid = array();
    foreach ( $plan as $k => $v ) if ( $v === 'content' ) $mid[] = $k;
    $m = count($mid);
    if ( $m >= 3 ) $plan[$mid[(int)floor($m * 0.45)]] = 'stats';
    if ( $m >= 4 ) $plan[$mid[$m - 1]] = 'timeline';
    if ( $n >= 12 && $m >= 7 ) $plan[$mid[(int)floor($m * 0.7)]] = 'quote';
    return $plan;
}

function ds_tpl_prompt( $topic, $n, $language, $T, $forced_type = '' ) {
$voice = isset($T['voice']) ? $T['voice'] : 'Confident, clear, professional. Short sentences.';
$icons = 'mdi:rocket-launch-outline, mdi:chart-line, mdi:chart-bar, mdi:chart-pie, mdi:account-group, mdi:account-heart, mdi:target, mdi:lightbulb-on-outline, mdi:shield-check, mdi:earth, mdi:trending-up, mdi:cog-outline, mdi:calendar-clock, mdi:handshake-outline, mdi:brain, mdi:cloud-outline, mdi:lock-outline, mdi:cash-multiple, mdi:leaf, mdi:heart-pulse, mdi:school-outline, mdi:bullhorn-outline, mdi:message-text-outline, mdi:trophy-outline, mdi:cube-outline, mdi:factory, mdi:scale-balance, mdi:magnify, mdi:puzzle-outline, mdi:lightning-bolt, mdi:star-four-points-outline, mdi:map-marker-outline, mdi:truck-fast-outline, mdi:database-outline, mdi:robot-outline, mdi:palette-outline, mdi:store-outline, mdi:file-document-outline, mdi:book-open-variant, mdi:account-tie, mdi:clipboard-check-outline, mdi:compass-outline, mdi:flag-checkered, mdi:help-circle-outline';
$p = "Write a {$n}-slide presentation in {$language}.\nTOPIC: {$topic}\n\n";
$p .= "WRITING TONE (word choice only - it never changes the storyline or the slide types): {$voice}\n\n";
$p .= "=== WRITING RULES ===\n";
$p .= "- Every point = 'Short Heading: explanation'. Heading 2-4 words. Explanation 15-28 words, specific to THIS topic: a concrete example, a named step, tool, place, group of people, a date, or a result. Never generic filler like 'drives growth' or 'improves efficiency' on its own.\n";
$p .= "- Use the facts, names and numbers given in the topic first.\n";
$p .= "- Numbers belong only where a real presenter would use them: results, market size, prices, budgets, dates, targets, traction. Slides about ideas, values, mission, team, story, method or how something works use clear explanations and examples, NOT percentages.\n";
$p .= "- Do not invent precise statistics, studies or sources. If an illustrative figure is needed, keep it realistic and round, and use it at most once or twice in the whole deck.\n";
$p .= "- Every slide except title/quote/closing has: title (3-7 words, says the slide's point), subtitle (10-16 words), content points, icons (one Iconify name per point) and conclusion (one 12-20 word takeaway sentence).\n";
$p .= "- Pick icons that match each point from: {$icons}\n";
$p .= "- No markdown, asterisks or emojis anywhere.\n\n";
$p .= "=== SLIDE TYPES ===\n";
$p .= "title: cover. subtitle 12-16 words. content = any of 'Company: <name>', 'Presenter: <full name>', 'Designation: <job title>', 'Date: <e.g. 14 March 2026>', 'Website: <www.company.com>', 'Tagline: <3-6 words>'. Only include lines you can reasonably fill.\n";
$p .= "agenda: content = the section titles that follow, 'Section Title: 5-9 word teaser', 4-7 items.\n";
$p .= "two_column (2 points): a comparison (before / after, problem / solution, today / tomorrow, option A / option B, pros / cons) or two big ideas.\n";
$p .= "three_column (3 points) / feature_grid (4 points): parallel pillars, features, benefits, services, audiences.\n";
$p .= "bullet (5 or 6 points): checklists, tips, do's and don'ts, requirements, detailed lists.\n";
$p .= "timeline (4 or 5 items 'Phase, Step or Date: 14-20 word description'): ONLY for things that happen in order - history, roadmap, phases, a step-by-step process, a schedule.\n";
$p .= "stats (3 or 4 items, EXACT format 'VALUE: 3-6 word label | 10-16 word explanation'): ONLY for headline numbers that truly matter to this topic (results, traction, market size, survey findings).\n";
$p .= "quote: title = a 16-26 word testimonial, founder statement or well-known quote that fits (no quote marks), content[0] = 'Name, Title, Organisation'.\n";
$p .= "closing: title = a short thank-you or call to action (2-5 words), subtitle = 10-16 word closing message, content = contact lines that fit ('Email: ...', 'Website: ...', 'Phone: ...'), conclusion = one sentence next step.\n\n";
if ( $forced_type !== '' ) {
$p .= "=== OUTPUT ===\nGenerate EXACTLY 1 slide of type '{$forced_type}' (never a title slide).\n";
$p .= "\nReturn ONLY valid JSON:\n";
$ex = $forced_type === 'stats' ? '"content":["94%: label | explanation","3.2x: label | explanation","$4.8M: label | explanation"],"icons":[]'
: ( $forced_type === 'timeline' ? '"content":["Phase 1: description","Phase 2: description","Phase 3: description","Phase 4: description"],"icons":[]'
: '"content":["Heading: explanation","Heading: explanation","Heading: explanation"],"icons":["mdi:target","mdi:chart-line","mdi:account-group"]' );
$p .= '{"title":"Slide title","slides":[{"type":"' . $forced_type . '","title":"...","subtitle":"...",' . $ex . ',"conclusion":"..."}]}';
$p .= "\nThis is ONE content slide added to an existing deck: no company, presenter, date, email or website lines.";
return $p;
}
$p .= "=== SLIDE TYPE RULES ===\n";
$p .= "- Pick each slide's type from what that slide says: a comparison = two_column; three parallel items = three_column; four = feature_grid; five or six = five_point / six_point; a short list = bullet; things in order (history, roadmap, steps) = timeline; real headline numbers = stats; a testimonial = quote.\n";
$p .= "- Never use the same type on three slides in a row. Do not add stats, timeline or quote slides just to fill the deck.\n";
$p .= "- A stats slide needs real figures from the topic or the business itself. If there are none, explain that section with a content layout and concrete examples instead.\n";
$p .= "- A quote is a testimonial from a customer or a person in the topic's own organisation. Never put invented words in the mouth of a real, famous person.\n\n";
$p .= "=== CHARTS (optional) ===\n";
$p .= "Add a \"chart\" object to a content slide ONLY when a chart shows something words cannot: a trend over time, a breakdown that adds up to 100%, or a comparison of amounts. When the topic itself gives numbers that compare or change over time (results vs target, growth by year, share by segment, figures per region or product), the slide about them MUST include a \"chart\" object with exactly those numbers. Most other decks need 0 or 1 chart; a data-heavy report may have 2. Never chart mission, values, team, story, process or concept slides, and never chart invented numbers just to decorate.\n";
$p .= 'Chart format: "chart":{"style":"column|bar|line|donut","unit":"% or $ or empty","labels":["2-8 labels"],"values":[numbers only]}. The slide\'s points then explain what the chart shows.' . "\n\n";
/* storyline: the deck-structure snippet (DS Deck Structure) swaps this section for its own plan when it recognises the kind of deck */
$kind = ds_tpl_deck_kind($topic);
$names = array('investor' => 'investor pitch', 'sales' => 'sales / product pitch', 'company' => 'company profile', 'training' => 'training session', 'lesson' => 'lesson for learners', 'report' => 'performance / status report', 'marketing' => 'marketing plan', 'strategy' => 'strategy / business plan', 'research' => 'research presentation', 'general' => 'topic overview');
$name = $names[$kind]; $an = preg_match('/^[aeiou]/i', $name) ? 'an' : 'a';
$has_close = $n >= 5; $has_agenda = $n >= 12;
$body = $n - 1 - ( $has_close ? 1 : 0 ) - ( $has_agenda ? 1 : 0 );
$plan = $body > 0 ? ds_tpl_story_plan($kind, $body) : array();
$p .= "=== SLIDE PLAN (follow exactly, {$n} slides) ===\n";
$p .= "This looks like {$an} {$name}. Plan it the way an expert presenter of {$an} {$name} would, writing each slide about THIS topic:\n";
$no = 1;
$p .= "Slide {$no}: title\n"; $no++;
if ( $has_agenda ) { $p .= "Slide {$no}: agenda\n"; $no++; }
foreach ( $plan as $sec ) { $p .= "Slide {$no}: {$sec[0]} - best as {$sec[1]}\n"; $no++; }
if ( count($plan) < $body ) {
$last = $no + ( $body - count($plan) ) - 1;
$p .= "Slides {$no}-{$last}: more sections this topic needs (split the most important one, or add an example, a case or deeper detail) - choose the type from the content\n";
$no = $last + 1;
}
if ( $has_close ) $p .= "Slide {$no}: closing (call to action or thank you)\n";
$p .= "If the topic is clearly a different kind of presentation, keep the same number of slides but follow the topic's own natural storyline instead. Put the kind of presentation in \"deck_type\".\n\n";
$p .= "Return ONLY valid JSON, with exactly {$n} slides, shaped like this example (the example shows the format only - choose your own types and order):\n";
$p .= '{"title":"Deck title","deck_type":"...","slides":[{"type":"title","title":"...","subtitle":"...","content":["Company: ...","Presenter: ..."]},{"type":"two_column","title":"...","subtitle":"...","content":["Heading: explanation","Heading: explanation"],"icons":["mdi:magnify","mdi:lightbulb-on-outline"],"conclusion":"..."},{"type":"three_column","title":"...","subtitle":"...","content":["Heading: explanation","Heading: explanation","Heading: explanation"],"icons":["mdi:target","mdi:account-group","mdi:cog-outline"],"conclusion":"...","chart":{"style":"column","unit":"%","labels":["2024","2025","2026"],"values":[12,18,25]}},{"type":"closing","title":"Thank You","subtitle":"...","content":["Email: hello@company.com","Website: www.company.com"],"conclusion":"..."}]}';
return $p;
}
/* a chart the AI planned for a slide: kept only when it is a real, small data set */
/* a chart from numbers the user actually gave: a slide whose points each carry one of the topic's own figures */
function ds_tpl_auto_chart( $slide, $topic ) {
if ( ! is_array($slide) || ! empty($slide['chart_ai']) ) return null;
if ( in_array($slide['type'] ?? '', array('title','closing','agenda','quote','timeline','stats'), true) ) return null;
$norm = function( $s ){ $s = strtolower((string) $s); $s = str_replace(array('million','billion','thousand'), array('m','b','k'), $s); return preg_replace('/[\s,$€£₹]+/u', '', $s); };
$tn = $norm($topic);
if ( ! preg_match('/\d/', $tn) ) return null;
$L = array(); $V = array(); $U = null; $S = null;
foreach ( (array) ($slide['content'] ?? array()) as $it ) {
$it = (string) $it; $ci = strpos($it, ':');
if ( $ci === false || $ci > 50 ) continue;
$lab = trim(substr($it, 0, $ci)); $rest = substr($it, $ci + 1);
if ( ! preg_match('/([$€£₹])?\s?(\d+(?:[.,]\d+)?)\s?(%|k\b|m\b|b\b|million|billion|thousand)?/iu', $rest, $m) ) continue;
$tok = $norm($m[2] . ( isset($m[3]) ? $m[3] : '' ));
if ( $tok === '' || strpos($tn, $tok) === false ) continue; /* only numbers the user typed */
$v = (float) str_replace(',', '.', $m[2]);
$suf = strtolower($m[3] ?? '');
$sx = array('k'=>'K','thousand'=>'K','m'=>'M','million'=>'M','b'=>'B','billion'=>'B');
$sx = isset($sx[$suf]) ? $sx[$suf] : '';
$u = $suf === '%' ? '%' : ( ! empty($m[1]) ? $m[1] : '' );
if ( $S === null ) $S = $sx; elseif ( $S !== $sx ) continue; /* same scale only */
$pc = $u === '%';
if ( $U === null ) $U = $u; elseif ( ($U === '%') !== $pc ) continue; elseif ( $U === '' && $u !== '' ) $U = $u;
$L[] = function_exists('mb_substr') ? mb_substr($lab, 0, 40) : substr($lab, 0, 40); $V[] = $v;
}
if ( count($V) < 2 ) return null;
$sum = array_sum($V);
$style = ( $U === '%' && $sum >= 95 && $sum <= 105 ) ? 'donut' : 'column';
return array('style' => $style, 'unit' => (string) $U . ( $U === '%' ? '' : (string) $S ), 'labels' => $L, 'values' => $V);
}
function ds_tpl_clean_chart( $c ) {
if ( ! is_array($c) ) return null;
$map = array('column'=>'column','bar'=>'bar','line'=>'line','area'=>'area','donut'=>'donut','doughnut'=>'donut','pie'=>'pie','progress'=>'progress','gauge'=>'gauge');
$style = strtolower(preg_replace('/[^a-z]/i', '', (string) ($c['style'] ?? 'column')));
$style = isset($map[$style]) ? $map[$style] : 'column';
$labels = is_array($c['labels'] ?? null) ? array_values($c['labels']) : array();
$values = is_array($c['values'] ?? null) ? array_values($c['values']) : array();
$L = array(); $V = array();
for ( $i = 0; $i < min(8, count($labels), count($values)); $i++ ) {
$v = is_numeric($values[$i]) ? (float) $values[$i] : (float) preg_replace('/[^0-9.\-]/', '', (string) $values[$i]);
if ( ! is_finite($v) ) continue;
$l = ds_tpl_clean_text($labels[$i], 40);
if ( $l === '' ) continue;
$L[] = $l; $V[] = $v;
}
if ( count($V) < 2 ) return null;
$unit = trim(substr(ds_tpl_clean_text((string) ($c['unit'] ?? ''), 8), 0, 8));
if ( preg_match('/empty|none/i', $unit) ) $unit = '';
return array('style' => $style, 'unit' => $unit, 'labels' => $L, 'values' => $V);
}
function ds_tpl_clean_icon( $ic ) {
    $ic = strtolower(trim((string)$ic));
    return preg_match('/^[a-z0-9-]+:[a-z0-9-]+$/', $ic) ? $ic : '';
}
function ds_tpl_clean_text( $s, $max = 600 ) {
    $s = (string)$s;
    $s = preg_replace('/\*\*([^*]+)\*\*/', '$1', $s);
    $s = preg_replace('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', $s);
    $s = sanitize_text_field($s);
    if ( function_exists('mb_substr') ) $s = mb_substr($s, 0, $max);
    return trim($s);
}
/* What kind of presentation the topic asks for, and a storyline of slide purposes that fits it (DS storyline planner) */
function ds_tpl_deck_kind( $topic ) {
$t = ' ' . strtolower((string) $topic) . ' ';
$rules = array(
'investor'  => '/investor|fundrais|funding round|series [abc]\b|seed round|pre-seed|venture capital|pitch deck|startup pitch|raise capital|raising/',
'report'    => '/\breport\b|quarterly|\bq[1-4]\b|annual results|performance review|results for|sales performance|financial results|status update|progress update|kpi review|monthly review|year in review/',
'training'  => '/training|workshop|onboarding|induction|session for (new )?(employees|staff|team)|upskill|how to train|coaching|bootcamp/',
'lesson'    => '/lesson|class \d|class [ivx]+\b|grade \d|students|school|teach|explain(er)?\b|for kids|chapter|syllabus|science of|how (do|does) .* work|what is /',
'research'  => '/research|thesis|dissertation|study on|case study of|literature review|academic|hypothesis|survey results/',
'sales'     => '/product pitch|sales pitch|sales presentation|proposal for|client proposal|demo|our product|launch|solution for|offer to|pricing|service proposal/',
'marketing' => '/marketing|campaign|brand(ing)?\b|social media|go-to-market|gtm|advertis|content strategy|seo\b/',
'company'   => '/company (overview|profile|introduction)|about (us|our company)|corporate profile|who we are|introduc(e|ing) (our|the) company|agency profile|firm overview/',
'strategy'  => '/strategy|business plan|strategic plan|roadmap|vision 20|transformation|expansion plan|growth plan|operating plan/',
);
foreach ( $rules as $k => $re ) if ( preg_match($re, $t) ) return $k;
return 'general';
}
function ds_tpl_story_plan( $kind, $k ) {
/* each section: purpose, best slide type, priority (1 = always keep) */
$S = array(
'investor' => array(
array('The problem: what is broken today and for whom', 'two_column or three_column', 1),
array('The solution: what the product does and why it works', 'three_column', 1),
array('Why now and the market: who buys and how big the opportunity is', 'stats, or a chart if the topic gives market numbers', 2),
array('The product: the key features a customer sees', 'feature_grid or bullet', 2),
array('Business model: how the company makes money', 'three_column', 1),
array('Traction: customers, pilots, revenue or other proof so far', 'stats, or a chart if numbers are given', 1),
array('Competition: how this is different from the alternatives', 'two_column', 3),
array('The team: the people who will make it happen', 'three_column or feature_grid', 3),
array('Roadmap: the next 12-24 months', 'timeline', 2),
array('The ask: how much is being raised and what it will be used for', 'three_column, or a donut chart for use of funds', 1),
),
'sales' => array(
array("The customer's challenge in their own terms", 'two_column or three_column', 1),
array('What the challenge costs them today', 'three_column (stats only if the topic gives numbers)', 2),
array('Introducing the product or service', 'three_column', 1),
array('How it works, step by step', 'timeline', 1),
array('Key features', 'feature_grid', 2),
array('Outcomes the customer can expect', 'three_column', 1),
array('A customer story: before and after', 'two_column', 2),
array('A short customer testimonial', 'quote', 3),
array('Packages or pricing', 'three_column', 2),
array('Next steps to get started', 'timeline or bullet', 1),
),
'company' => array(
array('Who we are and what we do, in one view', 'two_column', 1),
array('Mission and values', 'three_column or feature_grid', 1),
array('Our story: how the company grew', 'timeline', 2),
array('Services or products we offer', 'feature_grid or bullet', 1),
array('How we work with clients', 'timeline', 2),
array('The team and leadership', 'three_column or feature_grid', 2),
array('Clients, industries and results', 'three_column (stats only with real numbers)', 2),
array('What a client says about us', 'quote', 3),
array('Why choose us', 'bullet', 1),
),
'training' => array(
array('Learning objectives for this session', 'bullet', 1),
array('Why this matters at work', 'two_column', 1),
array('Key concept one, explained with an example', 'three_column or two_column', 1),
array('Key concept two, explained with an example', 'three_column or two_column', 1),
array('Key concept three, explained with an example', 'three_column or two_column', 2),
array('The method, step by step', 'timeline', 1),
array("Do's and don'ts", 'two_column', 2),
array('A real workplace scenario to discuss', 'two_column', 2),
array('Practice questions for the group', 'bullet', 3),
array('Recap: the key takeaways', 'three_column', 1),
),
'lesson' => array(
array('An opening question that makes students curious', 'two_column', 2),
array('Background: what students need to know first', 'three_column', 1),
array('Key idea one, explained simply', 'three_column or two_column', 1),
array('Key idea two, explained simply', 'three_column or two_column', 1),
array('How it happens, step by step', 'timeline', 1),
array('Real-world examples', 'feature_grid', 2),
array('Common mistakes or myths', 'two_column', 3),
array('Amazing facts', 'bullet', 3),
array('Summary: what to remember', 'bullet', 1),
),
'report' => array(
array('Executive summary: the headline result', 'three_column', 1),
array('Results against target', 'stats, or a chart with the numbers given', 1),
array('What drove the results', 'three_column', 1),
array('Results by segment, region or product', 'a chart if numbers are given, otherwise feature_grid', 2),
array('Challenges and how they were handled', 'two_column', 2),
array('Lessons learned', 'bullet', 3),
array('Plan for the next period', 'timeline', 1),
array('Decisions or support needed', 'bullet', 2),
),
'marketing' => array(
array('Goals of the plan', 'three_column', 1),
array('Target audience', 'three_column or feature_grid', 1),
array('Positioning and key message', 'two_column', 2),
array('Channels and why each one', 'feature_grid', 1),
array('Content and campaign ideas', 'bullet', 2),
array('Campaign calendar', 'timeline', 1),
array('Budget split', 'a donut chart if amounts are given, otherwise three_column', 2),
array('How success will be measured', 'three_column (stats only if targets are given)', 1),
),
'strategy' => array(
array('Where we are today', 'two_column', 1),
array('Vision and goals', 'three_column', 1),
array('Strategic pillars', 'three_column or feature_grid', 1),
array('Key initiatives', 'bullet', 1),
array('Roadmap', 'timeline', 1),
array('Resources and budget', 'three_column', 2),
array('Risks and how we reduce them', 'two_column', 2),
array('How we will measure success', 'three_column', 2),
),
'research' => array(
array('Background and context', 'three_column', 1),
array('Research question and objectives', 'two_column', 1),
array('Method', 'timeline or three_column', 1),
array('Key findings', 'three_column (stats or a chart only with real data)', 1),
array('Discussion: what the findings mean', 'two_column', 2),
array('Limitations', 'bullet', 3),
array('Conclusion', 'three_column', 1),
array('Future work', 'bullet', 3),
),
'general' => array(
array('Introduction: what this is', 'two_column', 1),
array('Why it matters', 'three_column', 1),
array('The main aspects', 'feature_grid', 1),
array('How it works or how it developed', 'timeline', 2),
array('Examples', 'three_column', 2),
array('Benefits and challenges', 'two_column', 2),
array('What comes next', 'bullet', 3),
array('Summary', 'bullet', 1),
),
);
$L = isset($S[$kind]) ? $S[$kind] : $S['general'];
$k = max(1, (int) $k);
if ( count($L) > $k ) {
/* keep the most important sections, in story order */
$idx = array_keys($L);
usort($idx, function($a, $b) use ($L){ return $L[$a][2] === $L[$b][2] ? $a - $b : $L[$a][2] - $L[$b][2]; });
$keep = array_slice($idx, 0, $k); sort($keep);
$L = array_map(function($i) use ($L){ return $L[$i]; }, $keep);
}
return $L;
}
function ds_tpl_sanitize_slides( $raw, $n, $keep_media = false ) {
    $allowed = array('title','agenda','bullet','two_column','three_column','feature_grid','five_point','six_point','seven_point','eight_point','stats','timeline','quote','closing','image_text');
    $out = array();
    if ( ! is_array($raw) ) $raw = array();
    foreach ( $raw as $s ) {
        if ( ! is_array($s) ) continue;
        $type = sanitize_key($s['type'] ?? 'bullet');
        if ( ! in_array($type, $allowed, true) ) $type = 'bullet';
        $content = array();
        if ( ! empty($s['content']) && is_array($s['content']) ) {
            foreach ( $s['content'] as $c ) { $c = ds_tpl_clean_text($c, 400); if ( $c !== '' ) $content[] = $c; }
        }
        $content = array_slice($content, 0, 10);
        if ( in_array($type, array('two_column','three_column','feature_grid','bullet','five_point','six_point','seven_point','eight_point','image_text'), true) && $content ) {
            $map = array(2=>'two_column',3=>'three_column',4=>'feature_grid',5=>'five_point',6=>'six_point',7=>'seven_point',8=>'eight_point');
            $cnt = count($content);
            $type = isset($map[$cnt]) ? $map[$cnt] : ( $cnt > 8 ? 'eight_point' : 'bullet' );
            if ( $cnt > 8 ) $content = array_slice($content, 0, 8);
        }
        $icons = array();
        if ( ! empty($s['icons']) && is_array($s['icons']) ) foreach ( $s['icons'] as $ic ) $icons[] = ds_tpl_clean_icon($ic);
        $row = array(
            'type'       => $type,
            'title'      => ds_tpl_clean_text($s['title'] ?? '', 260),
            'subtitle'   => ds_tpl_clean_text($s['subtitle'] ?? '', 260),
            'content'    => $content,
            'icons'      => $icons,
            'conclusion' => ds_tpl_clean_text($s['conclusion'] ?? '', 260),
            'image_query'=> '',
            'icon'       => '',
        );
        if ( ! empty($s['chart']) && is_array($s['chart']) && ! in_array($type, array('title','closing','agenda','quote','stats','timeline'), true) && ( $ch = ds_tpl_clean_chart($s['chart']) ) ) $row['chart_ai'] = $ch; /* the AI's own chart for this slide */
        if ( ! empty($s['chart_ai']) && is_array($s['chart_ai']) && ! isset($row['chart_ai']) && ( $ch = ds_tpl_clean_chart($s['chart_ai']) ) ) $row['chart_ai'] = $ch;
        if ( isset($s['planner']) && (int) $s['planner'] === 2 ) $row['planner'] = 2; /* planned storyline: no automatic % charts */
        if ( $keep_media ) {
            foreach ( array('image_data','image_position','image_market_id','image_market_tier','image_mkt_fetched') as $k ) if ( isset($s[$k]) ) $row[$k] = $s[$k];
        }
        $out[] = $row;
    }
    if ( count($out) > $n ) $out = array_slice($out, 0, $n);
    return $out;
}

function ds_tpl_groq_json( $sys, $prompt, $max_tokens ) {
    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $api_key = isset($keys['groq']) ? $keys['groq'] : '';
    if ( empty($api_key) ) return new WP_Error('nokey', 'Groq API key missing (snippet 2a).');
    $model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');
    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'timeout' => 90,
        'headers' => array('Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'),
        'body'    => wp_json_encode(array(
            'model' => $model,
            'messages' => array(array('role'=>'system','content'=>$sys), array('role'=>'user','content'=>$prompt)),
            'temperature' => 0.8, 'top_p' => 0.95, 'max_tokens' => (int)$max_tokens,
            'response_format' => array('type' => 'json_object'),
        )),
    ));
    if ( is_wp_error($resp) ) return $resp;
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
    $content = preg_replace('/\s*```$/', '', $content);
    $ppt = json_decode($content, true);
    if ( ( ! $ppt || empty($ppt['slides']) ) && function_exists('ds_ppt_repair_truncated_json') ) $ppt = ds_ppt_repair_truncated_json($content);
    if ( ! $ppt || empty($ppt['slides']) ) return new WP_Error('parse', 'AI response could not be parsed.');
    return $ppt;
}

function ds_tpl_credits( $uid ) {
    return function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
}

/**
 * Default generate handler. $mode: preview | confirm | generate
 * A template snippet calls this from its own generate function (so it can
 * pass its own voice / storyline), or replaces it completely.
 */
function ds_tpl_generate( WP_REST_Request $req, $T, $mode = 'generate' ) {
    $uid = get_current_user_id();
    $tid = $T['id'];

    if ( $mode === 'confirm' ) {
        $pd = $req->get_param('ppt_data');
        if ( ! is_array($pd) || empty($pd['slides']) ) return array('error'=>'No data','message'=>'Nothing to generate. Please build a preview first.');
        $n = max(1, min(20, count($pd['slides'])));
        $slides = ds_tpl_sanitize_slides($pd['slides'], $n);
        if ( ! $slides ) return array('error'=>'No data','message'=>'No valid slides to generate.');
        $cost = ds_ppt_credit_cost($n, 'ai');
        if ( ds_tpl_credits($uid) < $cost || ! ds_deduct_credits($uid, $cost) ) return array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.');
        $ppt = array('title' => ds_tpl_clean_text($pd['title'] ?? $slides[0]['title'], 200), 'tpl' => $tid, 'tplTheme' => sanitize_key($pd['tplTheme'] ?? ''), 'slides' => $slides);
        return ds_tpl_record_job($uid, $ppt, $cost);
    }

    $topic = ds_tpl_clean_text($req->get_param('topic') ?: '', 2000);
    $n     = (int) ( $req->get_param('num_slides') ?: $req->get_param('slides') ?: 10 );
    $n     = max(1, min(20, $n));
    $lang  = sanitize_text_field($req->get_param('language') ?: 'English');
    $forced = sanitize_key($req->get_param('slide_type') ?: '');
    $is_add = ! empty($req->get_param('append_slide')) || ( $n === 1 && $forced !== '' );
    if ( $topic === '' ) return array('error'=>'No topic','message'=>'Please enter a topic.');

    if ( $is_add ) {
        $opt  = get_option('ds_ppt_credit_settings');
        $rate = ( is_array($opt) && isset($opt['extra_slide_rate']) && $opt['extra_slide_rate'] !== '' ) ? (int)$opt['extra_slide_rate'] : 1;
        $cost = (int) ( function_exists('ds_ppt_add_slide_cost') ? ds_ppt_add_slide_cost() : apply_filters('ds_credits_per_slide', max(1, $rate), 'ai') );
        $n = 1;
        if ( $forced === 'swot' ) $forced = 'feature_grid';
        if ( $forced === '' || $forced === 'title' ) $forced = 'three_column';
    } else {
        $cost = ds_ppt_credit_cost($n, 'ai');
    }
    if ( ds_tpl_credits($uid) < $cost ) return array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.');

    if ( $mode === 'preview' ) {
        $k = 'ds_tpl_rl_' . $uid; $c = (int) get_transient($k);
        if ( $c >= 6 ) return array('error'=>'Slow down','message'=>'Too many previews in a minute. Please wait a moment.');
        set_transient($k, $c + 1, 60);
    }

    $sys = "You are a world-class presentation strategist and designer. You plan each presentation like an expert presenter for its real audience and write clear, specific slide content in clean JSON. " . ( ! empty($T['voice']) ? 'Tone: ' . $T['voice'] . ' (tone only; the storyline always follows the topic). ' : '' ) . "Output ONLY valid JSON, no markdown.";
    $prompt = ds_tpl_prompt($topic, $n, $lang, $T, $is_add ? $forced : '');
    $max_out = (int) min(20000, max(4000, $n * 900));
    $ppt = null; $slides = array();
    for ( $attempt = 1; $attempt <= 2; $attempt++ ) {
        $ppt = ds_tpl_groq_json($sys, $prompt, $max_out);
        if ( is_wp_error($ppt) ) { if ( $attempt === 2 ) return array('error'=>'Generation failed','message'=>$ppt->get_error_message()); continue; }
        $slides = ds_tpl_sanitize_slides($ppt['slides'], $n);
        if ( $is_add && $slides ) {
            $bad = in_array($slides[0]['type'], array('title','closing','agenda'), true)
                || preg_match('/^(Company|Presenter|Email|Website)\s*:/i', (string)($slides[0]['content'][0] ?? ''));
            if ( $bad && $attempt < 2 ) continue;
            if ( $bad ) { $slides[0]['type'] = $forced; if ( preg_match('/^(Company|Presenter|Email|Website)\s*:/i', (string)($slides[0]['content'][0] ?? '')) ) return array('error'=>'Generation failed','message'=>'Could not generate that slide. Please try again.'); }
        }
        if ( count($slides) >= max(1, (int)floor($n * 0.8)) ) break;
    }
    if ( ! $slides ) return array('error'=>'Generation failed','message'=>'The AI returned no slides. Please try again.');
    if ( ! $is_add && $slides[0]['type'] !== 'title' ) $slides[0]['type'] = 'title';
    $nch = 0; foreach ( $slides as $si => $sv ) { if ( ! empty($sv['chart_ai']) && ++$nch > 2 ) unset($slides[$si]['chart_ai']); } /* at most 2 charts */
    if ( ! $is_add && $nch === 0 ) { foreach ( $slides as $si => $sv ) { $ac = ds_tpl_auto_chart($sv, $topic); if ( $ac ) { $slides[$si]['chart_ai'] = $ac; if ( ++$nch >= 2 ) break; } } } /* the AI gave no chart: chart the topic's own numbers where a slide lists them */
if ( ! $is_add && $slides ) $slides[0]['planner'] = 2;
$out = array('deck_type' => ds_tpl_clean_text($ppt['deck_type'] ?? '', 60), 'title' => ds_tpl_clean_text($ppt['title'] ?? $topic, 200), 'tpl' => $tid, 'tplTheme' => sanitize_key($req->get_param('tpl_theme') ?: ''), 'slides' => $slides);

    if ( $mode === 'preview' ) {
        return array('success' => true, 'ppt_data' => $out, 'cost' => ds_ppt_credit_cost(count($slides), 'ai'), 'truncated' => false,
                     'note' => count($slides) < $n ? ('The AI generated ' . count($slides) . ' of the ' . $n . ' slides you asked for.') : '');
    }
    if ( ! ds_deduct_credits($uid, $cost) ) return array('error'=>'Not enough credits','message'=>'You need '.$cost.' credits.');
    return ds_tpl_record_job($uid, $out, $cost);
}

function ds_tpl_record_job( $uid, $ppt, $cost ) {
    try {
        $id = 'ppt_' . time() . '_' . wp_generate_password(8, false, false);
        $jobs = get_user_meta($uid, 'ds_ppt_jobs', true) ?: array();
        array_unshift($jobs, array('id'=>$id, 'title'=>$ppt['title'], 'slides'=>count($ppt['slides']), 'time'=>time()));
        update_user_meta($uid, 'ds_ppt_jobs', array_slice($jobs, 0, 30));
        $log = get_user_meta($uid, 'ds_ppt_generations', true) ?: array();
        array_unshift($log, array('date'=>current_time('mysql'), 'prompt'=>$ppt['title'], 'credits'=>$cost, 'num_slides'=>count($ppt['slides'])));
        update_user_meta($uid, 'ds_ppt_generations', array_slice($log, 0, 100));
        return array('success'=>true, 'ppt_id'=>$id, 'ppt_data'=>$ppt, 'credits_balance'=>ds_tpl_credits($uid));
    } catch ( Throwable $e ) {
        if ( function_exists('ds_add_credits') ) ds_add_credits($uid, $cost);
        return array('error'=>'Generation failed','message'=>'Something went wrong after payment - your credits have been refunded.');
    }
}

/* ---------------------------------------------------------------
 * PPTX WRITER
 * ------------------------------------------------------------- */
function ds_tpl_emu( $v ) { return (int) round( (float)$v * 9525 ); }
function ds_tpl_hex( $c, $def = '000000' ) {
    $c = strtoupper(ltrim(trim((string)$c), '#'));
    if ( strlen($c) === 3 ) $c = $c[0].$c[0].$c[1].$c[1].$c[2].$c[2];
    return preg_match('/^[0-9A-F]{6}$/', $c) ? $c : $def;
}
function ds_tpl_x( $s ) {
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$s);
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
function ds_tpl_clr( $hex, $op = null ) {
    $a = ( $op !== null && $op !== '' && (float)$op < 1 ) ? '<a:alpha val="' . max(0, min(100000, (int) round((float)$op * 100000))) . '"/>' : '';
    return '<a:srgbClr val="' . ds_tpl_hex($hex) . '">' . $a . '</a:srgbClr>';
}
function ds_tpl_grad_xml( $g, $op = null ) {
    if ( ! is_array($g) || empty($g['stops']) || ! is_array($g['stops']) ) return '';
    /* A radial fill in PowerPoint runs out to the CORNERS of the shape's box, the editor's runs to its SIDES. Written
       1:1, a glow or glass shine was still partly visible at the rim of its ellipse, so the rim showed as a hard edge.
       Radial stops are therefore pulled in (x 0.68, just inside the rim) and the last colour is held to the end. */
    $rad = ( ( $g['type'] ?? '' ) === 'radial' );
    $gs = ''; $last = null;
    foreach ( $g['stops'] as $s ) {
        if ( ! is_array($s) || count($s) < 2 ) continue;
        $o = isset($s[2]) ? (float)$s[2] : 1.0;
        if ( $op !== null && $op !== '' ) $o *= (float)$op;
        $pos = max(0, min(100000, (int) round((float)$s[0] * 1000)));
        if ( $rad ) $pos = (int) round($pos * 0.68);
        $gs .= '<a:gs pos="' . $pos . '">' . ds_tpl_clr($s[1], $o) . '</a:gs>';
        $last = array($pos, $s[1], $o);
    }
    if ( $gs === '' ) return '';
    if ( $rad ) {
        if ( $last && $last[0] < 100000 ) $gs .= '<a:gs pos="100000">' . ds_tpl_clr($last[1], $last[2]) . '</a:gs>';
        return '<a:gradFill rotWithShape="1"><a:gsLst>' . $gs . '</a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="50000" r="50000" b="50000"/></a:path></a:gradFill>';
    }
    $css = isset($g['ang']) ? (float)$g['ang'] : 135.0;
    $ang = fmod($css - 90 + 720, 360);
    return '<a:gradFill rotWithShape="1"><a:gsLst>' . $gs . '</a:gsLst><a:lin ang="' . (int) round($ang * 60000) . '" scaled="0"/></a:gradFill>';
}
function ds_tpl_fill_xml( $e ) {
    if ( ! empty($e['grad']) ) { $g = ds_tpl_grad_xml($e['grad'], $e['op'] ?? null); if ( $g ) return $g; }
    if ( ! empty($e['fill']) ) return '<a:solidFill>' . ds_tpl_clr($e['fill'], $e['op'] ?? null) . '</a:solidFill>';
    return '<a:noFill/>';
}
function ds_tpl_ln_xml( $e ) {
    /* DS Template Element Editor: an outline in two colours */
    if ( ! empty($e['lgrad']) && is_array($e['lgrad']) && ( $lg = ds_tpl_grad_xml($e['lgrad'], null) ) ) {
        return '<a:ln w="' . max(3175, ds_tpl_emu( isset($e['lw']) ? $e['lw'] : 2 )) . '">' . $lg . '</a:ln>';
    }
    if ( empty($e['line']) ) return '<a:ln><a:noFill/></a:ln>';
    $w = ds_tpl_emu( isset($e['lw']) ? $e['lw'] : 1 );
    return '<a:ln w="' . max(3175, $w) . '"><a:solidFill>' . ds_tpl_clr($e['line'], $e['lop'] ?? null) . '</a:solidFill>' . ( ! empty($e['dash']) ? '<a:prstDash val="dash"/>' : '' ) . '</a:ln>';
}
function ds_tpl_xfrm( $x, $y, $w, $h, $rot = 0, $flipH = false, $flipV = false ) {
    return '<a:xfrm' . ( $rot ? ' rot="' . (int) round($rot * 60000) . '"' : '' ) . ( $flipH ? ' flipH="1"' : '' ) . ( $flipV ? ' flipV="1"' : '' ) . '><a:off x="' . ds_tpl_emu($x) . '" y="' . ds_tpl_emu($y) . '"/><a:ext cx="' . max(0, ds_tpl_emu($w)) . '" cy="' . max(0, ds_tpl_emu($h)) . '"/></a:xfrm>';
}
function ds_tpl_geom( $e ) {
    $t = $e['t']; $w = max(1, (float)$e['w']); $h = max(1, (float)$e['h']); $mn = min($w, $h);
if ( $t === 'shape' && ! empty($e['pd']) && is_array($e['pd']) && function_exists('ds_tpl_pd_geom') && ( $gpd = ds_tpl_pd_geom($e['pd'], $w, $h) ) ) return $gpd; /* round corners / own number of sides */
    if ( $t === 'ell' ) return '<a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom>';
    if ( $t === 'shape' ) {
        $p = $e['prst'] ?? 'rect';
        $adj = array(
            'chevron'       => array('adj', 0.18 * $w / $mn * 100000),
            'homePlate'     => array('adj', 0.18 * $w / $mn * 100000),
            'hexagon'       => array('adj', 0.25 * $w / $mn * 100000),
            'parallelogram' => array('adj', 0.25 * $w / $mn * 100000),
        );
        $prst = array('chevron'=>'chevron','homePlate'=>'homePlate','triangle'=>'triangle','rtTriangle'=>'rtTriangle','diamond'=>'diamond','hexagon'=>'hexagon','parallelogram'=>'parallelogram','pentagon'=>'pentagon');
        $pn = $prst[$p] ?? 'rect';
        $av = isset($adj[$p]) ? '<a:gd name="' . $adj[$p][0] . '" fmla="val ' . (int) min(100000, round($adj[$p][1])) . '"/>' : '';
        if ( $p === 'hexagon' ) $av .= '<a:gd name="vf" fmla="val 115470"/>';
        return '<a:prstGeom prst="' . $pn . '"><a:avLst>' . $av . '</a:avLst></a:prstGeom>';
    }
    $r = isset($e['r']) ? (float)$e['r'] : 0;
    if ( $r > 0 ) return '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val ' . (int) min(50000, round($r / $mn * 100000)) . '"/></a:avLst></a:prstGeom>';
    return '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>';
}
function ds_tpl_effect( $e ) {
    /* DS Template Element Editor: a shadow set by the user - colour, opacity, blur, distance, angle, size */
    if ( isset($e['sh']) && is_array($e['sh']) ) {
        $s = $e['sh'];
        $n = function($k, $def, $lo, $hi) use ($s) { return isset($s[$k]) && is_numeric($s[$k]) ? max($lo, min($hi, (float)$s[$k])) : $def; };
        $o = $n('o', 0.35, 0, 1); $b = $n('b', 12, 0, 100); $d = $n('d', 6, 0, 100); $a = fmod($n('a', 45, -360, 720) + 720, 360); $sp = $n('s', 0, 0, 60);
        $sc = '';
        if ( $sp > 0 && ! empty($e['w']) && ! empty($e['h']) ) {
            $sc = ' sx="' . (int) round(100000 * ( (float)$e['w'] + 2 * $sp ) / max(1, (float)$e['w'])) . '" sy="' . (int) round(100000 * ( (float)$e['h'] + 2 * $sp ) / max(1, (float)$e['h'])) . '"';
        }
        return '<a:effectLst><a:outerShdw blurRad="' . ds_tpl_emu($b) . '" dist="' . ds_tpl_emu($d) . '" dir="' . (int) round($a * 60000) . '"' . $sc . ' algn="ctr" rotWithShape="0">' . ds_tpl_clr( isset($s['c']) && is_string($s['c']) ? $s['c'] : '000000', $o ) . '</a:outerShdw></a:effectLst>';
    }
    if ( empty($e['shadow']) ) return '';
    $a = is_numeric($e['shadow']) ? (float)$e['shadow'] : 0.18;
    return '<a:effectLst><a:outerShdw blurRad="' . ds_tpl_emu(24) . '" dist="' . ds_tpl_emu(8) . '" dir="5400000" algn="ctr" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="' . (int) round($a * 100000) . '"/></a:srgbClr></a:outerShdw></a:effectLst>';
}
function ds_tpl_sp( $id, $name, $e, $txBody = '' ) {
    return '<p:sp><p:nvSpPr><p:cNvPr id="' . $id . '" name="' . ds_tpl_x($name) . ' ' . $id . '"/><p:cNvSpPr' . ( $txBody ? ' txBox="1"' : '' ) . '/><p:nvPr/></p:nvSpPr><p:spPr>'
        . ds_tpl_xfrm($e['x'], $e['y'], $e['w'], $e['h'], $e['rot'] ?? 0)
        . ds_tpl_geom($e) . ds_tpl_fill_xml($e) . ds_tpl_ln_xml($e) . ds_tpl_effect($e)
        . '</p:spPr>' . ( $txBody ?: '<p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:endParaRPr lang="en-US"/></a:p></p:txBody>' ) . '</p:sp>';
}
function ds_tpl_text_sp( $id, $e, $fonts ) {
    $size = max(6, min(400, (float)($e['size'] ?? 16)));
    $sz   = (int) ( round($size * 0.75 * 2) * 50 );
    $fam  = ( ($e['font'] ?? 'body') === 'head' ) ? $fonts['head'] : $fonts['body'];
    if ( ! empty($e['ff']) && is_string($e['ff']) && preg_match('/^[A-Za-z0-9 ]{2,40}$/', $e['ff']) ) $fam = $e['ff'];   /* DS Template Element Editor: a font picked for this text */
    $dec  = isset($e['dec']) && is_string($e['dec']) ? $e['dec'] : '';
    $algn = array('l'=>'l','c'=>'ctr','r'=>'r');
    $anc  = array('t'=>'t','m'=>'ctr','b'=>'b');
    $lh   = isset($e['lh']) ? (float)$e['lh'] : 1.2;
    $spc  = (int) round( max(50, min(250, $lh / 1.2 * 100)) * 1000 );
    $ls   = isset($e['ls']) ? (float)$e['ls'] : 0;
    $rpr  = '<a:rPr lang="en-US" sz="' . $sz . '"' . ( (int)($e['wt'] ?? 400) >= 600 ? ' b="1"' : ' b="0"' ) . ( ! empty($e['italic']) ? ' i="1"' : '' )
          . ( strpos($dec, 'u') !== false ? ' u="sng"' : '' ) . ( strpos($dec, 's') !== false ? ' strike="sngStrike"' : '' )
          . ( ! empty($e['caps']) ? ' cap="all"' : '' ) . ( $ls ? ' spc="' . (int) round($ls * $size * 0.75 * 100) . '"' : '' ) . ' dirty="0">'
          . ( ( ! empty($e['tline']) && is_string($e['tline']) ) ? '<a:ln w="' . max(3175, ds_tpl_emu( isset($e['tlw']) && is_numeric($e['tlw']) ? (float)$e['tlw'] : 1 )) . '">' . ( ( ! empty($e['tlgrad']) && is_array($e['tlgrad']) && ( $tlg = ds_tpl_grad_xml($e['tlgrad'], null) ) ) ? $tlg : '<a:solidFill>' . ds_tpl_clr($e['tline'], null) . '</a:solidFill>' ) . '</a:ln>' : '' )
          . ( ( ! empty($e['tgrad']) && is_array($e['tgrad']) && ( $tg = ds_tpl_grad_xml($e['tgrad'], $e['op'] ?? null) ) ) ? $tg : '<a:solidFill>' . ds_tpl_clr($e['color'] ?? '000000', $e['op'] ?? null) . '</a:solidFill>' )
          . ( ( isset($e['sh']) && is_array($e['sh']) ) ? ds_tpl_effect( array('sh' => $e['sh']) ) : '' )
          . '<a:latin typeface="' . ds_tpl_x($fam) . '"/><a:ea typeface="' . ds_tpl_x($fam) . '"/><a:cs typeface="' . ds_tpl_x($fam) . '"/></a:rPr>';
    $paras = '';
    foreach ( explode("\n", (string)($e['text'] ?? '')) as $line ) {
        $paras .= '<a:p><a:pPr algn="' . ( $algn[$e['align'] ?? 'l'] ?? 'l' ) . '"><a:lnSpc><a:spcPct val="' . $spc . '"/></a:lnSpc><a:spcBef><a:spcPts val="0"/></a:spcBef></a:pPr>'
                . ( $line !== '' ? '<a:r>' . $rpr . '<a:t>' . ds_tpl_x($line) . '</a:t></a:r>' : '' )
                . '<a:endParaRPr lang="en-US" sz="' . $sz . '"/></a:p>';
    }
    $body = '<p:txBody><a:bodyPr wrap="' . ( ! empty($e['nw']) ? 'none' : 'square' ) . '" lIns="0" tIns="0" rIns="0" bIns="0" anchor="' . ( $anc[$e['va'] ?? 't'] ?? 't' ) . '" rtlCol="0"><a:normAutofit/></a:bodyPr><a:lstStyle/>' . $paras . '</p:txBody>';
    $box = array('t'=>'rect','x'=>$e['x'],'y'=>$e['y'],'w'=>$e['w'],'h'=>$e['h'],'rot'=>isset($e['rot']) ? (float)$e['rot'] : 0);
    return ds_tpl_sp($id, 'Text', $box, $body);
}
function ds_tpl_line_sp( $id, $e ) {
    $x1 = (float)$e['x1']; $y1 = (float)$e['y1']; $x2 = (float)$e['x2']; $y2 = (float)$e['y2'];
    $w = abs($x2 - $x1); $h = abs($y2 - $y1);
    $ln = '<a:ln w="' . max(3175, ds_tpl_emu($e['lw'] ?? 1)) . '"><a:solidFill>' . ds_tpl_clr($e['color'] ?? '888888', $e['op'] ?? null) . '</a:solidFill>' . ( ! empty($e['dash']) ? '<a:prstDash val="dash"/>' : '' ) . '</a:ln>';
    return '<p:cxnSp><p:nvCxnSpPr><p:cNvPr id="' . $id . '" name="Line ' . $id . '"/><p:cNvCxnSpPr/><p:nvPr/></p:nvCxnSpPr><p:spPr>'
        . ds_tpl_xfrm(min($x1, $x2), min($y1, $y2), $w, $h, 0, $x2 < $x1, $y2 < $y1)
        . '<a:prstGeom prst="line"><a:avLst/></a:prstGeom>' . $ln . ( ( isset($e['sh']) && is_array($e['sh']) ) ? ds_tpl_effect( array('sh' => $e['sh']) ) : '' ) . '</p:spPr></p:cxnSp>';
}
function ds_tpl_path_sp( $id, $e ) {
    $d = is_array($e['d'] ?? null) ? $e['d'] : array();
    $xs = array(); $ys = array();
    foreach ( $d as $c ) { if ( ! is_array($c) ) continue; $cn = count($c); for ( $i = 1; $i + 1 < $cn; $i += 2 ) { $xs[] = (float)$c[$i]; $ys[] = (float)$c[$i+1]; } }
    if ( ! $xs ) return '';
    $minx = min($xs); $miny = min($ys); $w = max(1, max($xs) - $minx); $h = max(1, max($ys) - $miny);
    $pt = function($x, $y) use ($minx, $miny) { return '<a:pt x="' . ds_tpl_emu((float)$x - $minx) . '" y="' . ds_tpl_emu((float)$y - $miny) . '"/>'; };
    $p = '';
    foreach ( $d as $c ) {
        if ( ! is_array($c) || ! $c ) continue;
        switch ( $c[0] ) {
            case 'M': $p .= '<a:moveTo>' . $pt($c[1], $c[2]) . '</a:moveTo>'; break;
            case 'L': $p .= '<a:lnTo>' . $pt($c[1], $c[2]) . '</a:lnTo>'; break;
            case 'C': $p .= '<a:cubicBezTo>' . $pt($c[1], $c[2]) . $pt($c[3], $c[4]) . $pt($c[5], $c[6]) . '</a:cubicBezTo>'; break;
            case 'Q': $p .= '<a:quadBezTo>' . $pt($c[1], $c[2]) . $pt($c[3], $c[4]) . '</a:quadBezTo>'; break;
            case 'Z': $p .= '<a:close/>'; break;
        }
    }
    $fill = ! empty($e['fill']) ? '<a:solidFill>' . ds_tpl_clr($e['fill'], $e['op'] ?? null) . '</a:solidFill>' : '<a:noFill/>';
    $ln = ! empty($e['stroke']) ? '<a:ln w="' . max(3175, ds_tpl_emu($e['lw'] ?? 1)) . '" cap="rnd"><a:solidFill>' . ds_tpl_clr($e['stroke'], $e['lop'] ?? null) . '</a:solidFill><a:round/></a:ln>' : '<a:ln><a:noFill/></a:ln>';
    return '<p:sp><p:nvSpPr><p:cNvPr id="' . $id . '" name="Shape ' . $id . '"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr>' . ds_tpl_xfrm($minx, $miny, $w, $h)
        . '<a:custGeom><a:avLst/><a:gdLst/><a:ahLst/><a:cxnLst/><a:rect l="0" t="0" r="r" b="b"/><a:pathLst><a:path w="' . ds_tpl_emu($w) . '" h="' . ds_tpl_emu($h) . '">' . $p . '</a:path></a:pathLst></a:custGeom>'
        . $fill . $ln . ( ( isset($e['sh']) && is_array($e['sh']) ) ? ds_tpl_effect( array('sh' => $e['sh']) ) : '' ) . '</p:spPr><p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:endParaRPr lang="en-US"/></a:p></p:txBody></p:sp>';
}
function ds_tpl_ring_sps( &$id, $e ) {
    $w = (float)$e['w']; $th = max(1, (float)($e['th'] ?? 12)); $pct = max(0, min(100, (float)($e['pct'] ?? 0)));
    $adj3 = (int) min(50000, round($th / $w * 100000));
    $track = '<p:sp><p:nvSpPr><p:cNvPr id="' . ($id++) . '" name="RingTrack"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr>' . ds_tpl_xfrm($e['x'], $e['y'], $w, $w)
        . '<a:prstGeom prst="donut"><a:avLst><a:gd name="adj" fmla="val ' . $adj3 . '"/></a:avLst></a:prstGeom><a:solidFill>' . ds_tpl_clr($e['track'] ?? 'DDDDDD') . '</a:solidFill><a:ln><a:noFill/></a:ln></p:spPr></p:sp>';
    if ( $pct >= 99.5 ) {
        return $track . '<p:sp><p:nvSpPr><p:cNvPr id="' . ($id++) . '" name="Ring"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr>' . ds_tpl_xfrm($e['x'], $e['y'], $w, $w)
            . '<a:prstGeom prst="donut"><a:avLst><a:gd name="adj" fmla="val ' . $adj3 . '"/></a:avLst></a:prstGeom><a:solidFill>' . ds_tpl_clr($e['color'] ?? '3366FF') . '</a:solidFill><a:ln><a:noFill/></a:ln></p:spPr></p:sp>';
    }
    if ( $pct <= 0.5 ) return $track;
    $st = 270; $en = fmod(270 + $pct * 3.6, 360);
    return $track . '<p:sp><p:nvSpPr><p:cNvPr id="' . ($id++) . '" name="Ring"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr>' . ds_tpl_xfrm($e['x'], $e['y'], $w, $w)
        . '<a:prstGeom prst="blockArc"><a:avLst><a:gd name="adj1" fmla="val ' . (int) round($st * 60000) . '"/><a:gd name="adj2" fmla="val ' . (int) round($en * 60000) . '"/><a:gd name="adj3" fmla="val ' . $adj3 . '"/></a:avLst></a:prstGeom><a:solidFill>' . ds_tpl_clr($e['color'] ?? '3366FF') . '</a:solidFill><a:ln><a:noFill/></a:ln></p:spPr></p:sp>';
}
function ds_tpl_crop( $pos, $iw, $ih, $zw, $zh ) {
    $px = isset($pos['x']) ? max(0, min(100, (float)$pos['x'])) : 50;
    $py = isset($pos['y']) ? max(0, min(100, (float)$pos['y'])) : 50;
    if ( ! $iw || ! $ih || ! $zw || ! $zh ) return '<a:stretch><a:fillRect/></a:stretch>';
    $zr = $zw / $zh; $ir = $iw / $ih; $l = $r = $t = $b = 0.0;
    if ( $ir > $zr ) { $c = 1 - $zr / $ir; $l = $c * $px / 100; $r = $c * (1 - $px / 100); }
    else { $c = 1 - $ir / $zr; $t = $c * $py / 100; $b = $c * (1 - $py / 100); }
    $f = function($v){ return (int) round($v * 100000); };
    return '<a:srcRect l="' . $f($l) . '" t="' . $f($t) . '" r="' . $f($r) . '" b="' . $f($b) . '"/><a:stretch><a:fillRect/></a:stretch>';
}
function ds_tpl_pic( $id, $name, $rid, $e, $crop, $geom, $svg_rid = '' ) {
    $ext = $svg_rid ? '<a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}"><asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="' . $svg_rid . '"/></a:ext></a:extLst>' : '';
    /* DS Template Element Editor: a picture or icon can be turned and made see-through */
    $alpha = ( isset($e['op']) && is_numeric($e['op']) && (float)$e['op'] < 1 ) ? '<a:alphaModFix amt="' . max(0, min(100000, (int) round((float)$e['op'] * 100000))) . '"/>' : '';
    return '<p:pic><p:nvPicPr><p:cNvPr id="' . $id . '" name="' . ds_tpl_x($name) . ' ' . $id . '"/><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'
        . '<p:blipFill><a:blip r:embed="' . $rid . '">' . $alpha . $ext . '</a:blip>' . $crop . '</p:blipFill>'
        . '<p:spPr>' . ds_tpl_xfrm($e['x'], $e['y'], $e['w'], $e['h'], isset($e['rot']) ? (float)$e['rot'] : 0) . $geom
        . ( ( ! empty($e['line']) && is_string($e['line']) && isset($e['t']) && $e['t'] === 'img' ) ? ds_tpl_ln_xml($e) : '' )
        . ( ( isset($e['sh']) && is_array($e['sh']) ) ? ds_tpl_effect( array('sh' => $e['sh'], 'w' => $e['w'], 'h' => $e['h']) ) : '' )
        . '</p:spPr></p:pic>';
}

/* Iconify icon -> svg (+ png fallback), cached for 30 days */
function ds_tpl_icon( $name, $color ) {
    $name = ds_tpl_clean_icon($name); $color = ds_tpl_hex($color, 'FFFFFF');
    if ( ! $name ) return null;
    $key = 'ds_tpl_ic_' . md5($name . '|' . $color);
    $c = get_transient($key);
    if ( is_array($c) && ! empty($c['svg']) ) return array('svg' => base64_decode($c['svg']), 'png' => ! empty($c['png']) ? base64_decode($c['png']) : '');
    list($col, $nm) = explode(':', $name, 2);
    $resp = wp_remote_get('https://api.iconify.design/' . rawurlencode($col) . '/' . rawurlencode($nm) . '.svg?color=%23' . $color . '&width=256&height=256', array('timeout' => 8, 'headers' => array('User-Agent' => 'Mozilla/5.0')));
    if ( is_wp_error($resp) ) return null;
    $svg = wp_remote_retrieve_body($resp);
    if ( ! $svg || strpos($svg, '<svg') === false ) return null;
    $png = '';
    if ( class_exists('Imagick') ) {
        try {
            $im = new Imagick(); $im->setBackgroundColor(new ImagickPixel('transparent')); $im->setResolution(288, 288);
            $im->readImageBlob($svg); $im->setImageFormat('png32'); $im->resizeImage(256, 256, Imagick::FILTER_LANCZOS, 1);
            $png = $im->getImageBlob(); $im->destroy();
        } catch ( Exception $ex ) { $png = ''; }
    }
    set_transient($key, array('svg' => base64_encode($svg), 'png' => $png ? base64_encode($png) : ''), 30 * DAY_IN_SECONDS);
    return array('svg' => $svg, 'png' => $png);
}
function ds_tpl_blank_png() {
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
}

/* DS Template Element Editor: Fill > Picture - a box, circle or shape filled with a picture is written as a picture in that shape */
if ( ! function_exists('ds_tpl_pfill_pic') ) {
function ds_tpl_pfill_pic( $zip, $sn, &$pf_n, &$sid, $e, &$rels ) {
	$pg = $e['pimg'];
	if ( empty($pg['src']) || ! is_string($pg['src']) || ! preg_match('#^data:image/(png|jpeg);base64,(.+)$#s', $pg['src'], $m) ) return '';
	$b = base64_decode($m[2], true);
	if ( ! $b || strlen($b) < 100 || strlen($b) > 6 * 1024 * 1024 ) return '';
	$png = substr($b, 0, 4) === "\x89PNG";
	if ( ! $png && substr($b, 0, 3) !== "\xFF\xD8\xFF" ) return '';
	$sz = @getimagesizefromstring($b); $iw = $sz ? (float)$sz[0] : 0; $ih = $sz ? (float)$sz[1] : 0;
	$pf_n++; $ext = $png ? 'png' : 'jpeg';
	$zip->addFromString('ppt/media/slidefill' . $sn . '_' . $pf_n . '.' . $ext, $b);
	$rid = 'rId' . ( 960 + $pf_n );
	$rels[] = '<Relationship Id="' . $rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/slidefill' . $sn . '_' . $pf_n . '.' . $ext . '"/>';
	$zw = max(1, (float)$e['w']); $zh = max(1, (float)$e['h']);
	if ( ! empty($pg['st']) || ! $iw || ! $ih ) {
		$crop = '<a:stretch><a:fillRect/></a:stretch>';
	} else {
		$z  = isset($pg['z'])  && is_numeric($pg['z'])  ? max(1, min(4, (float)$pg['z'])) : 1;
		$px = isset($pg['px']) && is_numeric($pg['px']) ? max(0, min(100, (float)$pg['px'])) : 50;
		$py = isset($pg['py']) && is_numeric($pg['py']) ? max(0, min(100, (float)$pg['py'])) : 50;
		$ir = $iw / $ih; $zr = $zw / $zh;
		if ( $ir > $zr ) { $wv = $zr / $ir; $hv = 1; } else { $wv = 1; $hv = $ir / $zr; }
		$wv /= $z; $hv /= $z;
		$l = ( 1 - $wv ) * $px / 100; $t = ( 1 - $hv ) * $py / 100; $r = 1 - $l - $wv; $bt = 1 - $t - $hv;
		$f = function($v){ return (int) round($v * 100000); };
		$crop = '<a:srcRect l="' . $f($l) . '" t="' . $f($t) . '" r="' . $f($r) . '" b="' . $f($bt) . '"/><a:stretch><a:fillRect/></a:stretch>';
	}
	$pe = $e; $pe['t'] = 'img';
	$pe['op'] = isset($pg['op']) && is_numeric($pg['op']) ? max(0, min(1, (float)$pg['op'])) : 1;
	return ds_tpl_pic($sid++, 'Picture fill', $rid, $pe, $crop, ds_tpl_geom($e));
}
}
/* DS Template Element Editor: an icon with a gradient colour and / or an outline (solid or gradient) in the PowerPoint download */
if ( ! function_exists('ds_tpl_icon_fx') ) {
function ds_tpl_icon_fxkey( $e ) {
	$g = ( ! empty($e['tgrad']) && is_array($e['tgrad']) ) ? $e['tgrad'] : null;
	$l = ( ! empty($e['tline']) && is_string($e['tline']) ) ? $e['tline'] : '';
	if ( ! $g && ! $l ) return '';
	return '|fx' . md5( wp_json_encode( array( $g, $l, $l ? ( $e['tlgrad'] ?? null ) : null, $l ? ( $e['tlw'] ?? 1 ) : 0, round( (float) ( $e['w'] ?? 0 ) ) ) ) );
}
function ds_tpl_icon_grad_svg( $g, $id, $vb ) {
	if ( ! is_array($g) || empty($g['stops']) || ! is_array($g['stops']) ) return '';
	$st = '';
	foreach ( array_slice($g['stops'], 0, 6) as $s ) {
		if ( ! is_array($s) || count($s) < 2 ) continue;
		$st .= '<stop offset="' . max(0, min(100, (float)$s[0])) . '%" stop-color="#' . ds_tpl_hex($s[1], '000000') . '" stop-opacity="' . ( isset($s[2]) ? max(0, min(1, (float)$s[2])) : 1 ) . '"/>';
	}
	if ( ! $st ) return '';
	$x0 = $vb[0]; $y0 = $vb[1]; $vw = $vb[2]; $vh = $vb[3]; $cx = $x0 + $vw / 2; $cy = $y0 + $vh / 2;
	if ( ( $g['type'] ?? '' ) === 'radial' ) return '<radialGradient id="' . $id . '" gradientUnits="userSpaceOnUse" cx="' . $cx . '" cy="' . $cy . '" r="' . ( max($vw, $vh) / 2 ) . '">' . $st . '</radialGradient>';
	$a = deg2rad( isset($g['ang']) ? (float)$g['ang'] : 135 ); $dx = sin($a) / 2; $dy = -cos($a) / 2;
	return '<linearGradient id="' . $id . '" gradientUnits="userSpaceOnUse" x1="' . round($cx - $dx * $vw, 3) . '" y1="' . round($cy - $dy * $vh, 3) . '" x2="' . round($cx + $dx * $vw, 3) . '" y2="' . round($cy + $dy * $vh, 3) . '">' . $st . '</linearGradient>';
}
function ds_tpl_icon_fx( $e ) {
	$base = ds_tpl_icon( $e['name'] ?? '', '000000' );
	if ( ! $base || empty($base['svg']) ) return null;
	$svg = $base['svg'];
	if ( ! preg_match('#<svg\b[^>]*>#i', $svg, $m, PREG_OFFSET_CAPTURE) ) return $base;
	$open = $m[0][0]; $at = $m[0][1] + strlen($open);
	$end = strripos($svg, '</svg>'); if ( $end === false || $end < $at ) return $base;
	$inner = substr($svg, $at, $end - $at);
	$vb = array(0, 0, 24, 24);
	if ( preg_match('#viewBox="\s*([-\d.]+)[\s,]+([-\d.]+)[\s,]+([-\d.]+)[\s,]+([-\d.]+)\s*"#i', $open, $v) ) $vb = array((float)$v[1], (float)$v[2], max(1, (float)$v[3]), max(1, (float)$v[4]));
	$paint = function( $s, $p ) { return preg_replace('/(fill|stroke)="#000(000)?"/i', '$1="' . $p . '"', $s); };
	$defs = ''; $fill = '#' . ds_tpl_hex($e['color'] ?? 'FFFFFF', 'FFFFFF');
	if ( ! empty($e['tgrad']) && ( $gx = ds_tpl_icon_grad_svg($e['tgrad'], 'dsig', $vb) ) ) { $defs .= $gx; $fill = 'url(#dsig)'; }
	$out = ''; $pad = 0;
	if ( ! empty($e['tline']) && is_string($e['tline']) ) {
		$lc = '#' . ds_tpl_hex($e['tline'], 'FFFFFF');
		if ( ! empty($e['tlgrad']) && ( $gl = ds_tpl_icon_grad_svg($e['tlgrad'], 'dsil', $vb) ) ) { $defs .= $gl; $lc = 'url(#dsil)'; }
		$sw = max(0.05, ( isset($e['tlw']) && is_numeric($e['tlw']) ? (float)$e['tlw'] : 1 ) * 2 * $vb[2] / max(1, (float)( $e['w'] ?? 24 )));
		$pad = $sw / 2;
		$out = '<g fill="' . $lc . '" stroke="' . $lc . '" stroke-width="' . round($sw, 3) . '" stroke-linejoin="round" stroke-linecap="round">' . $paint($inner, $lc) . '</g>';
	}
	$body = '<g fill="' . $fill . '">' . $paint($inner, $fill) . '</g>';
	if ( $pad > 0 ) {
		$nvb = round($vb[0] - $pad, 3) . ' ' . round($vb[1] - $pad, 3) . ' ' . round($vb[2] + 2 * $pad, 3) . ' ' . round($vb[3] + 2 * $pad, 3);
		$open = preg_match('#viewBox="[^"]*"#i', $open) ? preg_replace('#viewBox="[^"]*"#i', 'viewBox="' . $nvb . '"', $open) : str_replace('<svg', '<svg viewBox="' . $nvb . '"', $open);
	}
	$new = $open . ( $defs ? '<defs>' . $defs . '</defs>' : '' ) . $out . $body . '</svg>';
	$key = 'ds_tpl_icfx_' . md5($new);
	$c = get_transient($key);
	if ( is_array($c) && ! empty($c['png']) ) return array('svg' => $new, 'png' => base64_decode($c['png']));
	$png = '';
	if ( class_exists('Imagick') ) {
		try {
			$im = new Imagick(); $im->setBackgroundColor(new ImagickPixel('transparent')); $im->setResolution(288, 288);
			$im->readImageBlob($new); $im->setImageFormat('png32'); $im->resizeImage(256, 256, Imagick::FILTER_LANCZOS, 1);
			$png = $im->getImageBlob(); $im->destroy();
		} catch ( Exception $ex ) { $png = ''; }
	}
	if ( $png ) set_transient($key, array('png' => base64_encode($png)), 30 * DAY_IN_SECONDS);
	return array('svg' => $new, 'png' => $png ? $png : $base['png']);
}
}
/* DS Template Element Editor: round corners / own number of sides on a shape - its outline as a custom shape */
if ( ! function_exists('ds_tpl_pd_geom') ) {
function ds_tpl_pd_geom( $pd, $w, $h ) {
	if ( ! is_array($pd) ) return '';
	$gp = '';
	foreach ( array_slice($pd, 0, 200) as $c ) {
		if ( ! is_array($c) || ! $c ) continue;
		$q = array(); $n = count($c);
		for ( $i = 1; $i < $n; $i++ ) $q[] = ds_tpl_emu((float)$c[$i]);
		if ( $c[0] === 'M' && count($q) >= 2 ) $gp .= '<a:moveTo><a:pt x="' . $q[0] . '" y="' . $q[1] . '"/></a:moveTo>';
		elseif ( $c[0] === 'L' && count($q) >= 2 ) $gp .= '<a:lnTo><a:pt x="' . $q[0] . '" y="' . $q[1] . '"/></a:lnTo>';
		elseif ( $c[0] === 'Q' && count($q) >= 4 ) $gp .= '<a:quadBezTo><a:pt x="' . $q[0] . '" y="' . $q[1] . '"/><a:pt x="' . $q[2] . '" y="' . $q[3] . '"/></a:quadBezTo>';
		elseif ( $c[0] === 'C' && count($q) >= 6 ) $gp .= '<a:cubicBezTo><a:pt x="' . $q[0] . '" y="' . $q[1] . '"/><a:pt x="' . $q[2] . '" y="' . $q[3] . '"/><a:pt x="' . $q[4] . '" y="' . $q[5] . '"/></a:cubicBezTo>';
		elseif ( $c[0] === 'Z' ) $gp .= '<a:close/>';
	}
	if ( ! $gp ) return '';
	return '<a:custGeom><a:avLst/><a:gdLst/><a:ahLst/><a:cxnLst/><a:rect l="0" t="0" r="r" b="b"/><a:pathLst><a:path w="' . ds_tpl_emu($w) . '" h="' . ds_tpl_emu($h) . '">' . $gp . '</a:path></a:pathLst></a:custGeom>';
}
}
function ds_tpl_page_bg_xml( $bg ) {
    if ( is_array($bg) && ! empty($bg['grad']) ) { $g = ds_tpl_grad_xml($bg['grad']); if ( $g ) return '<p:bg><p:bgPr>' . $g . '<a:effectLst/></p:bgPr></p:bg>'; }
    $f = is_array($bg) && ! empty($bg['fill']) ? $bg['fill'] : 'FFFFFF';
    return '<p:bg><p:bgPr><a:solidFill>' . ds_tpl_clr($f) . '</a:solidFill><a:effectLst/></p:bgPr></p:bg>';
}

/**
 * Default build handler: request carries tpl_pages (layout elements for
 * each slide, computed by the template's own layouts) + slides (images).
 */
function ds_tpl_build( WP_REST_Request $req, $T ) {
    @set_time_limit(0); @ignore_user_abort(true); @ini_set('memory_limit', '1024M');
    if ( ! class_exists('ZipArchive') ) return array('success'=>false,'error'=>'ZipArchive not available.');
    $uid    = get_current_user_id();
    $title  = sanitize_text_field($req->get_param('title') ?: 'Presentation');
    $slides = $req->get_param('slides');
    $pages  = $req->get_param('tpl_pages');
    if ( ! is_array($pages) || ! $pages ) return array('success'=>false,'error'=>'No slides to build.');
    if ( ! is_array($slides) ) $slides = array();
    if ( function_exists('ds_hub_count_saved_items') && $uid ) {
        $cap = function_exists('ds_get_user_saved_cap') ? ds_get_user_saved_cap($uid) : 100;
        $ds_at_cap = ( ds_hub_count_saved_items($uid) >= $cap );   /* at the limit the file is still built and downloaded - it is just not kept in the saved list */
    }

    /* marketplace-picked images: same unlock rules as 12b */
    $mkt_unlocked = 0; $mkt_locked = 0;
    foreach ( $slides as $i => $s ) {
        $mid = isset($s['image_market_id']) ? (int)$s['image_market_id'] : 0;
        $tier = isset($s['image_market_tier']) ? sanitize_text_field($s['image_market_tier']) : '';
        if ( ! $mid || ! in_array($tier, array('free','pro'), true) ) continue;
        if ( ! empty($s['image_mkt_fetched']) ) { $mkt_unlocked++; continue; }
        if ( ! function_exists('ds_dl_check_core') || ! function_exists('ds_mkt_get_unwatermarked_file_bytes') ) continue;
        $chk = ds_dl_check_core($uid, $tier);
        if ( empty($chk['ok']) ) { $mkt_locked++; continue; }
        $real = ds_mkt_get_unwatermarked_file_bytes($mid);
        if ( $real && ! empty($real['bytes']) ) { $slides[$i]['image_data'] = 'data:' . $real['mime'] . ';base64,' . base64_encode($real['bytes']); $mkt_unlocked++; } else $mkt_locked++;
    }

    $fonts = array('head' => sanitize_text_field($T['fonts']['head'] ?? 'Arial'), 'body' => sanitize_text_field($T['fonts']['body'] ?? 'Arial'));
    $tfo = $req->get_param('tpl_fonts');                       // theme picked on a template deck: its heading font
    if ( is_array($tfo) ) foreach ( array('head','body') as $fk ) if ( ! empty($tfo[$fk]) && preg_match('/^[A-Za-z0-9 ]{2,40}$/', $tfo[$fk]) ) $fonts[$fk] = $tfo[$fk];
    $pal   = $T['pal'] ?? array();
    $GLOBALS['DS_CHART_FONT'] = $fonts['body'];

    $upload = wp_upload_dir();
    $dir = $upload['basedir'] . '/ds-ppt-downloads'; wp_mkdir_p($dir);
    $safe = sanitize_file_name($title) ?: 'presentation';
    $fname = 'ds-ppt-' . $safe . '-' . time() . '.pptx';
    $fpath = $dir . '/' . $fname; $furl = $upload['baseurl'] . '/ds-ppt-downloads/' . $fname;
    $zip = new ZipArchive();
    if ( $zip->open($fpath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true ) return array('success'=>false,'error'=>'Cannot create file on server.');

    $pages = array_values(array_slice($pages, 0, 40));
    /* company logo (same feature as Classic decks): decoded once, shared by every slide */
    $logo_media = null; $tl = $req->get_param('tpl_logo');
    if ( is_array($tl) && ! empty($tl['src']) && is_string($tl['src']) && preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $tl['src'], $lm) ) {
        $lb = base64_decode($lm[2], true);
        if ( $lb && strlen($lb) > 60 && strlen($lb) < 4 * 1024 * 1024 ) { $lext = $lm[1] === 'png' ? 'png' : 'jpeg'; $zip->addFromString('ppt/media/dstlogo.' . $lext, $lb); $logo_media = 'dstlogo.' . $lext; }
    }
    /* DS Deck Style: a second (short) logo for the slides after the cover */
    $logo2_media = null; $tl2 = $req->get_param('tpl_logo2');
    if ( is_array($tl2) && ! empty($tl2['src']) && is_string($tl2['src']) && preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $tl2['src'], $lm2) ) {
        $lb2 = base64_decode($lm2[2], true);
        if ( $lb2 && strlen($lb2) > 60 && strlen($lb2) < 4 * 1024 * 1024 ) { $lext2 = $lm2[1] === 'png' ? 'png' : 'jpeg'; $zip->addFromString('ppt/media/dstlogo2.' . $lext2, $lb2); $logo2_media = 'dstlogo2.' . $lext2; }
    }
    $order = array();                                    // slide order: each page, promo before last
    foreach ( $pages as $i => $p ) $order[] = array('page', $i);
    if ( defined('DS_PROMO_SLIDE_BODY_XML') && count($order) >= 2 && apply_filters('ds_tpl_include_promo', true, $T) ) array_splice($order, count($order) - 1, 0, array(array('promo', -1)));
    $n = count($order);

    $icon_media = array(); $icon_count = 0; $has_promo = false;
    $slide_xml = array(); $slide_rels = array();
    foreach ( $order as $sn0 => $o ) {
        $sn = $sn0 + 1;
        if ( $o[0] === 'promo' ) { $has_promo = true; $slide_xml[$sn] = null; continue; }
        $pg = $pages[$o[1]]; $sl = isset($slides[$o[1]]) && is_array($slides[$o[1]]) ? $slides[$o[1]] : array();
        $els = isset($pg['els']) && is_array($pg['els']) ? array_slice($pg['els'], 0, 600) : array();
        $rels = array(); $sid = 2; $tree = '';
        /* slide image */
        $img = null; $img_rid = '';
        $du = isset($sl['image_data']) ? trim((string)$sl['image_data']) : '';
        if ( strpos($du, 'data:image/') === 0 ) {
            $parts = explode(',', $du, 2);
            $bytes = count($parts) === 2 ? base64_decode($parts[1]) : '';
            if ( $bytes && strlen($bytes) > 500 ) {
                $b = substr($bytes, 0, 4); $ext = null;
                if ( $b === "\x89PNG" ) $ext = 'png'; elseif ( substr($b, 0, 3) === "\xFF\xD8\xFF" ) $ext = 'jpeg';
                elseif ( function_exists('imagecreatefromstring') ) {          /* WebP, GIF, BMP ...: PowerPoint takes PNG or JPEG, so the picture is converted (it used to be left out) */
                    $gd = @imagecreatefromstring($bytes);
                    if ( ! $gd && substr($bytes, 8, 4) === 'WEBP' && function_exists('imagecreatefromwebp') ) $gd = @imagecreatefromwebp('data://image/webp;base64,' . base64_encode($bytes));
                    if ( $gd ) {
                        $alpha = ( substr($bytes, 0, 3) === 'GIF' ) || ( substr($bytes, 8, 4) === 'WEBP' && ( substr($bytes, 12, 4) === 'VP8L' || ( substr($bytes, 12, 4) === 'VP8X' && ( ord($bytes[20]) & 0x10 ) ) ) );
                        ob_start();
                        if ( $alpha ) { imagealphablending($gd, false); imagesavealpha($gd, true); imagepng($gd, null, 6); $ext = 'png'; }
                        else { imagejpeg($gd, null, 90); $ext = 'jpeg'; }
                        $conv = ob_get_clean(); imagedestroy($gd);
                        if ( $conv && strlen($conv) > 500 ) $bytes = $conv; else $ext = null;
                    }
                }
                if ( $ext ) {
                    $sz = @getimagesizefromstring($bytes);
                    $img = array('ext'=>$ext, 'w'=>$sz ? $sz[0] : 0, 'h'=>$sz ? $sz[1] : 0);
                    $zip->addFromString('ppt/media/slideimg' . $sn . '.' . $ext, $bytes);
                    $img_rid = 'rId100';
                    $rels[] = '<Relationship Id="rId100" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/slideimg' . $sn . '.' . $ext . '"/>';
                }
            }
        }
        /* second picture of a slide (a layout with two picture boxes): its own JPEG / PNG, kept in image2_data */
        $img2 = null; $img2_rid = null;
        if ( ! empty($sl['image2_data']) && is_string($sl['image2_data']) && preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $sl['image2_data'], $m2) ) {
            $b2 = base64_decode($m2[2], true);
            if ( $b2 && strlen($b2) > 500 && strlen($b2) < 8 * 1024 * 1024 ) {
                $s2 = @getimagesizefromstring($b2);
                if ( $s2 && ( $s2[2] === IMAGETYPE_PNG || $s2[2] === IMAGETYPE_JPEG ) ) {
                    $e2 = $s2[2] === IMAGETYPE_PNG ? 'png' : 'jpeg';
                    $img2 = array('ext'=>$e2, 'w'=>$s2[0], 'h'=>$s2[1]);
                    $zip->addFromString('ppt/media/slideimg' . $sn . 'b.' . $e2, $b2);
                    $img2_rid = 'rId102';
                    $rels[] = '<Relationship Id="rId102" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/slideimg' . $sn . 'b.' . $e2 . '"/>';
                }
            }
        }
        $icon_rids = array(); $logo_rel = false; $logo2_rel = false; $pic_n = 0; $pf_n = 0;
        foreach ( $els as $e ) {
            if ( ! is_array($e) || empty($e['t']) ) continue;
            foreach ( array('x','y','w','h') as $k ) $e[$k] = isset($e[$k]) ? (float)$e[$k] : 0;
            switch ( $e['t'] ) {
                case 'rect': case 'ell': case 'shape':
                    if ( ! empty($e['pimg']) && is_array($e['pimg']) && $pf_n < 30 && ( $pfx = ds_tpl_pfill_pic($zip, $sn, $pf_n, $sid, $e, $rels) ) ) { $tree .= $pfx; break; } /* Fill > Picture */
$tree .= ds_tpl_sp($sid++, 'Shape', $e); break;
                case 'text':
                    if ( isset($e['text']) && trim((string)$e['text']) !== '' ) $tree .= ds_tpl_text_sp($sid++, $e, $fonts);
                    break;
                case 'line': $tree .= ds_tpl_line_sp($sid++, $e); break;
                case 'path': $tree .= ds_tpl_path_sp($sid++, $e); break;
                case 'ring': $tree .= ds_tpl_ring_sps($sid, $e); break;
                case 'chart':                                 /* DS Charts snippet: native, editable PowerPoint chart */
                    if ( function_exists('ds_chart_frames') ) $tree .= ds_chart_frames($e['x'], $e['y'], $e['w'], $e['h'], is_array($e['spec'] ?? null) ? $e['spec'] : array(),
                        array('colors' => is_array($e['colors'] ?? null) ? $e['colors'] : array(), 'tc' => $e['tc'] ?? 'FFFFFF', 'mc' => $e['mc'] ?? null, 'bgc' => $e['bgc'] ?? null, 'legend' => $e['legend'] ?? 'none'));
                    break;
                case 'pic':                                   /* a picture that carries its own data: the blurred backdrop of a frosted-glass box (DS Template Frosted Glass) */
                    if ( $pic_n < 24 && ! empty($e['src']) && is_string($e['src']) && preg_match('#^data:image/(png|jpeg);base64,(.+)$#s', $e['src'], $mp) ) {
                        $bp = base64_decode($mp[2], true);
                        if ( $bp && strlen($bp) > 100 && strlen($bp) < 3 * 1024 * 1024 && ( substr($bp, 0, 4) === "\x89PNG" || substr($bp, 0, 3) === "\xFF\xD8\xFF" ) ) {
                            $pic_n++; $pxt = substr($bp, 0, 4) === "\x89PNG" ? 'png' : 'jpeg';
                            $zip->addFromString('ppt/media/slidepic' . $sn . '_' . $pic_n . '.' . $pxt, $bp);
                            $rels[] = '<Relationship Id="rId' . ( 900 + $pic_n ) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/slidepic' . $sn . '_' . $pic_n . '.' . $pxt . '"/>';
                            $tree .= ds_tpl_pic($sid++, 'Glass', 'rId' . ( 900 + $pic_n ), $e, '<a:stretch><a:fillRect/></a:stretch>', ds_tpl_geom(array('t' => ( ! empty($e['prst']) ? 'shape' : 'rect' ), 'prst' => preg_replace('/[^A-Za-z0-9]/', '', (string) ( $e['prst'] ?? '' )),'w'=>$e['w'],'h'=>$e['h'],'r'=>$e['r'] ?? 0)));
                        }
                    }
                    break;
                case 'img':
                    $geom = ( ($e['shape'] ?? '') === 'ell' ) ? '<a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom>' : ds_tpl_geom(array('t'=>'rect','w'=>$e['w'],'h'=>$e['h'],'r'=>$e['r'] ?? 0));
                    $slot2 = ( (int) ( $e['slot'] ?? 1 ) === 2 );
                    if ( $slot2 && $img2_rid ) {
                        $tree .= ds_tpl_pic($sid++, 'Picture 2', $img2_rid, $e, ds_tpl_crop(array('x'=>50,'y'=>50), $img2['w'], $img2['h'], $e['w'], $e['h']), $geom);
                        break;
                    }
                    if ( $img_rid && ! $slot2 ) {
                        $pos = isset($sl['image_position']) && is_array($sl['image_position']) ? $sl['image_position'] : array('x'=>50,'y'=>50);
                        $tree .= ds_tpl_pic($sid++, 'Picture', $img_rid, $e, ds_tpl_crop($pos, $img['w'], $img['h'], $e['w'], $e['h']), $geom);
                    } else {
                        $ph = is_array($e['ph'] ?? null) ? $e['ph'] : array('fill' => '8899AA');
                        $pe = array('t' => ( ($e['shape'] ?? '') === 'ell' ) ? 'ell' : 'rect', 'x'=>$e['x'],'y'=>$e['y'],'w'=>$e['w'],'h'=>$e['h'],'r'=>$e['r'] ?? 0,'fill'=>$ph['fill'] ?? null,'grad'=>$ph['grad'] ?? null);
                        $tree .= ds_tpl_sp($sid++, 'Image area', $pe);
                    }
                    break;
                case 'logo':
                if ( ! empty($e['alt']) && $logo2_media ) {
                    if ( ! $logo2_rel ) { $rels[] = '<Relationship Id="rId151" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/' . $logo2_media . '"/>'; $logo2_rel = true; }
                    $tree .= ds_tpl_pic($sid++, 'Company Logo', 'rId151', $e, '<a:stretch><a:fillRect/></a:stretch>', '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>');
                    break;
                }
                if ( $logo_media ) {
                    if ( ! $logo_rel ) { $rels[] = '<Relationship Id="rId150" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/' . $logo_media . '"/>'; $logo_rel = true; }
                    $tree .= ds_tpl_pic($sid++, 'Company Logo', 'rId150', $e, '<a:stretch><a:fillRect/></a:stretch>', '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>');
                }
                break;
            case 'icon':
                    $ifx = function_exists('ds_tpl_icon_fxkey') ? ds_tpl_icon_fxkey($e) : ''; /* gradient / outline on the icon */
$key = ( $e['name'] ?? '' ) . '|' . ds_tpl_hex($e['color'] ?? 'FFFFFF') . $ifx;
                    if ( ! array_key_exists($key, $icon_media) ) {
                        $ic = $ifx ? ds_tpl_icon_fx($e) : ds_tpl_icon($e['name'] ?? '', $e['color'] ?? 'FFFFFF');
                        if ( $ic ) {
                            $icon_count++;
                            $zip->addFromString('ppt/media/dsicon' . $icon_count . '.svg', $ic['svg']);
                            $zip->addFromString('ppt/media/dsicon' . $icon_count . '.png', $ic['png'] ?: ds_tpl_blank_png());
                            $icon_media[$key] = $icon_count;
                        } else $icon_media[$key] = 0;
                    }
                    $mi = $icon_media[$key];
                    if ( ! $mi ) break;
                    if ( ! isset($icon_rids[$mi]) ) {
                        $r1 = 'rId' . (200 + 2 * $mi); $r2 = 'rId' . (201 + 2 * $mi);
                        $rels[] = '<Relationship Id="' . $r1 . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/dsicon' . $mi . '.png"/>';
                        $rels[] = '<Relationship Id="' . $r2 . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/dsicon' . $mi . '.svg"/>';
                        $icon_rids[$mi] = array($r1, $r2);
                    }
                    $tree .= ds_tpl_pic($sid++, 'Icon', $icon_rids[$mi][0], $e, '<a:stretch><a:fillRect/></a:stretch>', '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>', $icon_rids[$mi][1]);
                    break;
            }
        }
        /* PNG objects the user placed on the slide (DS Template PNG Objects): transparent pictures on top of everything else */
        if ( ! empty($sl['png_objects']) && is_array($sl['png_objects']) ) {
            $on = 0;
            foreach ( array_slice($sl['png_objects'], 0, 6) as $ob ) {
                if ( ! is_array($ob) || empty($ob['src']) || ! is_string($ob['src']) || ! preg_match('#^data:image/png;base64,(.+)$#s', $ob['src'], $mo) ) continue;
                $bo = base64_decode($mo[1], true);
                if ( ! $bo || strlen($bo) < 60 || strlen($bo) > 6 * 1024 * 1024 || substr($bo, 0, 4) !== "\x89PNG" ) continue;
                $oe = array('x' => (float) ( $ob['x'] ?? 0 ), 'y' => (float) ( $ob['y'] ?? 0 ), 'w' => (float) ( $ob['w'] ?? 0 ), 'h' => (float) ( $ob['h'] ?? 0 ));
                if ( $oe['w'] < 4 || $oe['h'] < 4 ) continue;
                $on++;
                $zip->addFromString('ppt/media/slideobj' . $sn . '_' . $on . '.png', $bo);
                $rels[] = '<Relationship Id="rId' . ( 110 + $on ) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/slideobj' . $sn . '_' . $on . '.png"/>';
                $tree .= ds_tpl_pic($sid++, 'Object ' . $on, 'rId' . ( 110 + $on ), $oe, '<a:stretch><a:fillRect/></a:stretch>', '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>');
            }
        }
        $slide_xml[$sn] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><p:cSld>'
            . ds_tpl_page_bg_xml($pg['bg'] ?? array()) . '<p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="12192000" cy="6858000"/><a:chOff x="0" y="0"/><a:chExt cx="12192000" cy="6858000"/></a:xfrm></p:grpSpPr>'
            . $tree . '</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sld>';
        $slide_rels[$sn] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>' . implode('', $rels) . '</Relationships>';
    }

    /* promo slide (same as 12b) */
    if ( $has_promo ) {
        foreach ( $slide_xml as $sn => $x ) {
            if ( $x !== null ) continue;
            $zip->addFromString('ppt/media/promo-mail.png', base64_decode(DS_PROMO_ICON_MAIL_B64));
            $zip->addFromString('ppt/media/promo-globe.png', base64_decode(DS_PROMO_ICON_GLOBE_B64));
            $zip->addFromString('ppt/media/promo-check.png', base64_decode(DS_PROMO_ICON_CHECK_B64));
            $slide_xml[$sn] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><p:cSld><p:bg><p:bgPr><a:solidFill><a:srgbClr val="0E1A33"/></a:solidFill></p:bgPr></p:bg><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="12192000" cy="6858000"/><a:chOff x="0" y="0"/><a:chExt cx="12192000" cy="6858000"/></a:xfrm></p:grpSpPr>' . DS_PROMO_SLIDE_BODY_XML . '</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sld>';
            $r = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
               . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-mail.png"/>'
               . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-globe.png"/>';
            for ( $k = 3; $k <= 8; $k++ ) $r .= '<Relationship Id="rId' . $k . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/promo-check.png"/>';
            $slide_rels[$sn] = $r . '<Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/></Relationships>';
        }
    }

    ds_tpl_write_package($zip, $n, $fonts, $pal, $title);
    foreach ( $slide_xml as $sn => $x ) { $zip->addFromString('ppt/slides/slide' . $sn . '.xml', $x); $zip->addFromString('ppt/slides/_rels/slide' . $sn . '.xml.rels', $slide_rels[$sn]); }
    $zip->close();
    if ( function_exists('ds_chart_finalize') ) ds_chart_finalize($fpath);   /* DS Charts: add the chart parts */

    if ( ! file_exists($fpath) || filesize($fpath) < 500 ) return array('success'=>false,'error'=>'PPTX file creation failed.');
    /* a download is not a saved item: the file is handed to the user and removed from the server after an hour */
    wp_schedule_single_event( time() + 3600, 'ds_ppt_unlink_tmp', array( $fpath ) );
    if ( false ) {
        $built = get_user_meta($uid, 'ds_ppt_built_files', true); if ( ! is_array($built) ) $built = array();
        $built[] = array('id'=>wp_generate_uuid4(),'title'=>$title,'filename'=>$safe . '.pptx','url'=>$furl,'path'=>$fpath,'size'=>filesize($fpath),'date'=>current_time('mysql'),'template'=>$T['id']);
        update_user_meta($uid, 'ds_ppt_built_files', $built);
    }
    return array('success'=>true,'url'=>$furl,'filename'=>$safe . '.pptx','images'=>0,'mkt_unlocked'=>$mkt_unlocked,'mkt_locked'=>$mkt_locked,'template'=>$T['id']);
}

function ds_tpl_write_package( $zip, $n, $fonts, $pal, $title ) {
    $ct = '';
    for ( $i = 1; $i <= $n; $i++ ) $ct .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
        . '<Default Extension="png" ContentType="image/png"/><Default Extension="jpeg" ContentType="image/jpeg"/><Default Extension="jpg" ContentType="image/jpeg"/><Default Extension="svg" ContentType="image/svg+xml"/>'
        . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
        . '<Override PartName="/ppt/presProps.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presProps+xml"/>'
        . '<Override PartName="/ppt/viewProps.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.viewProps+xml"/>'
        . '<Override PartName="/ppt/tableStyles.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.tableStyles+xml"/>'
        . '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>'
        . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
        . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
        . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
        . $ct . '</Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/></Relationships>');
    $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>' . ds_tpl_x($title) . '</dc:title><dc:creator>DesignStager</dc:creator></cp:coreProperties>');
    $rels = ''; $ids = '';
    for ( $i = 1; $i <= $n; $i++ ) { $rels .= '<Relationship Id="rId' . ($i + 5) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide' . $i . '.xml"/>'; $ids .= '<p:sldId id="' . ($i + 255) . '" r:id="rId' . ($i + 5) . '"/>'; }
    $zip->addFromString('ppt/_rels/presentation.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/presProps" Target="presProps.xml"/>'
        . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/viewProps" Target="viewProps.xml"/>'
        . '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>'
        . '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/tableStyles" Target="tableStyles.xml"/>' . $rels . '</Relationships>');
    $zip->addFromString('ppt/presentation.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" saveSubsetFonts="1">'
        . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst><p:sldIdLst>' . $ids . '</p:sldIdLst><p:sldSz cx="12192000" cy="6858000" type="screen16x9"/><p:notesSz cx="6858000" cy="9144000"/>'
        . '<p:defaultTextStyle><a:defPPr><a:defRPr lang="en-US"/></a:defPPr><a:lvl1pPr marL="0" algn="l" defTabSz="914400" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:defRPr sz="1800" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl1pPr></p:defaultTextStyle></p:presentation>');
    $ns = 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"';
    $zip->addFromString('ppt/presProps.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:presentationPr ' . $ns . '/>');
    $zip->addFromString('ppt/viewProps.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:viewPr ' . $ns . '><p:normalViewPr><p:restoredLeft sz="15620"/><p:restoredTop sz="94660"/></p:normalViewPr><p:slideViewPr><p:cSldViewPr><p:cViewPr varScale="1"><p:scale><a:sx n="100" d="100"/><a:sy n="100" d="100"/></p:scale><p:origin x="0" y="0"/></p:cViewPr></p:cSldViewPr></p:slideViewPr></p:viewPr>');
    $zip->addFromString('ppt/tableStyles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><a:tblStyleLst xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" def="{5C22544A-7EE6-4342-B048-85BDC9FD1C3A}"/>');
    $bg = ds_tpl_hex($pal['bg'] ?? 'FFFFFF', 'FFFFFF'); $tx = ds_tpl_hex($pal['text'] ?? '111111', '111111');
    $ac = ds_tpl_hex($pal['accent'] ?? '2563EB', '2563EB'); $a2 = ds_tpl_hex($pal['accent2'] ?? $ac, $ac);
    $dk1 = $tx;
    $c = function($k, $v){ return '<a:' . $k . '><a:srgbClr val="' . $v . '"/></a:' . $k . '>'; };
    $sf = '<a:solidFill><a:schemeClr val="phClr"/></a:solidFill>';
    $zip->addFromString('ppt/theme/theme1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="DesignStager"><a:themeElements><a:clrScheme name="DesignStager">'
        . $c('dk1', $dk1) . $c('lt1', 'FFFFFF') . $c('dk2', $tx) . $c('lt2', $bg) . $c('accent1', $ac) . $c('accent2', $a2) . $c('accent3', $ac) . $c('accent4', $a2) . $c('accent5', $ac) . $c('accent6', $a2) . $c('hlink', $ac) . $c('folHlink', $a2)
        . '</a:clrScheme><a:fontScheme name="DesignStager"><a:majorFont><a:latin typeface="' . ds_tpl_x($fonts['head']) . '"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont><a:minorFont><a:latin typeface="' . ds_tpl_x($fonts['body']) . '"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont></a:fontScheme>'
        . '<a:fmtScheme name="DesignStager"><a:fillStyleLst>' . $sf . $sf . $sf . '</a:fillStyleLst><a:lnStyleLst><a:ln w="6350">' . $sf . '</a:ln><a:ln w="12700">' . $sf . '</a:ln><a:ln w="19050">' . $sf . '</a:ln></a:lnStyleLst>'
        . '<a:effectStyleLst><a:effectStyle><a:effectLst/></a:effectStyle><a:effectStyle><a:effectLst/></a:effectStyle><a:effectStyle><a:effectLst/></a:effectStyle></a:effectStyleLst><a:bgFillStyleLst>' . $sf . $sf . $sf . '</a:bgFillStyleLst></a:fmtScheme></a:themeElements></a:theme>');
    $zip->addFromString('ppt/slideMasters/slideMaster1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:sldMaster ' . $ns . '><p:cSld><p:bg><p:bgPr><a:solidFill><a:srgbClr val="' . $bg . '"/></a:solidFill><a:effectLst/></p:bgPr></p:bg><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr/></p:spTree></p:cSld>'
        . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
        . '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst><p:txStyles><p:titleStyle><a:lvl1pPr><a:defRPr sz="4400" b="1"><a:solidFill><a:srgbClr val="' . $tx . '"/></a:solidFill><a:latin typeface="+mj-lt"/></a:defRPr></a:lvl1pPr></p:titleStyle>'
        . '<p:bodyStyle><a:lvl1pPr><a:defRPr sz="1800"><a:solidFill><a:srgbClr val="' . $tx . '"/></a:solidFill><a:latin typeface="+mn-lt"/></a:defRPr></a:lvl1pPr></p:bodyStyle><p:otherStyle/></p:txStyles></p:sldMaster>');
    $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="../theme/theme1.xml"/></Relationships>');
    $zip->addFromString('ppt/slideLayouts/slideLayout1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><p:sldLayout ' . $ns . ' type="blank" preserve="1"><p:cSld name="Blank"><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr/></p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sldLayout>');
    $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/></Relationships>');
}

/* ---------------------------------------------------------------
 * Front-end: engine JavaScript (templates print theirs after this)
 * ------------------------------------------------------------- */
function ds_tpl_should_print() {
    if ( ! is_user_logged_in() ) return false;
    global $post;
    $ok = is_a($post, 'WP_Post') && ( has_shortcode($post->post_content, 'ds_ai_ppt') || has_shortcode($post->post_content, 'ds_ai_studio') );
    return (bool) apply_filters('ds_tpl_print_js', $ok);
}
add_action('wp_footer', function () {
    if ( ! ds_tpl_should_print() ) return;
    echo "<script id=\"ds-tpl-engine\">\n" . ds_tpl_engine_js() . "\n</script>\n";
}, 1005);

function ds_tpl_engine_js() {
    ob_start(); ?>
/* ============================================================
   DS TEMPLATE ENGINE — core (renderer, text fitting, layouts)
   Coordinate space: 1280 x 720 px (1 px = 9525 EMU in PPTX)
   Every layout produces a list of primitive elements. The same
   list is turned into the editor preview (HTML) and into the
   PPTX (PHP), so preview and download always match.
   ============================================================ */
(function(){
'use strict';
if (window.DSTE && window.DSTE.core) return;
var W = 1280, H = 720;
var DSTE = window.DSTE = window.DSTE || {};
DSTE.core = 1; DSTE.W = W; DSTE.H = H;
DSTE.reg = DSTE.reg || {}; DSTE.order = DSTE.order || [];

/* ---------------- small utils ---------------- */
function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function strip(s){return String(s==null?'':s).replace(/\*\*([^*]+)\*\*/g,'$1').replace(/\*([^*]+)\*/g,'$1').replace(/^#+\s*/gm,'').replace(/`([^`]+)`/g,'$1').replace(/__([^_]+)__/g,'$1').trim();}
function hx(c){c=String(c||'').replace('#','').trim();if(c.length===3)c=c[0]+c[0]+c[1]+c[1]+c[2]+c[2];return /^[0-9a-fA-F]{6}$/.test(c)?('#'+c.toUpperCase()):'#000000';}
function rgb(c){c=hx(c).slice(1);return [parseInt(c.slice(0,2),16),parseInt(c.slice(2,4),16),parseInt(c.slice(4,6),16)];}
function toHex(a){return '#'+a.map(function(v){v=Math.max(0,Math.min(255,Math.round(v)));return (v<16?'0':'')+v.toString(16);}).join('').toUpperCase();}
function mix(a,b,t){var x=rgb(a),y=rgb(b);return toHex([x[0]+(y[0]-x[0])*t,x[1]+(y[1]-x[1])*t,x[2]+(y[2]-x[2])*t]);}
function lum(c){var x=rgb(c);return (0.299*x[0]+0.587*x[1]+0.114*x[2])/255;}
function rgba(c,op){var x=rgb(c);return 'rgba('+x[0]+','+x[1]+','+x[2]+','+(op==null?1:op)+')';}
function pad2(n){return (n<10?'0':'')+n;}
function clamp(v,a,b){return Math.max(a,Math.min(b,v));}
DSTE.u = {esc:esc,strip:strip,hx:hx,mix:mix,lum:lum,rgba:rgba,pad2:pad2,clamp:clamp};

/* ---------------- content parsing ---------------- */
function splitItem(str){
  str = strip(str).replace(/^\s*(\d{1,2}[\.\)]|[-•*])\s+/,'');
  var h='', d=str, x='';
  var ci = str.indexOf(':');
  if (ci > 0 && ci < 70) { h = str.slice(0,ci).trim(); d = str.slice(ci+1).trim(); }
  var pi = d.indexOf(' | ');
  if (pi >= 0) { x = d.slice(pi+3).trim(); d = d.slice(0,pi).trim(); }
  return {h:h, d:d, x:x};
}
function joinItem(p){
  var s = p.h ? (p.h + (p.d||p.x ? ': ' : '')) : '';
  s += (p.d||'');
  if (p.x) s += ' | ' + p.x;
  return s;
}
DSTE.splitItem = splitItem; DSTE.joinItem = joinItem;

function items(slide){
  var c = Array.isArray(slide.content) ? slide.content : [];
  var out = [];
  c.forEach(function(it,i){ var s=String(it==null?'':it).trim(); if(!s) return; var p=splitItem(s); p.i=i; out.push(p); });
  return out;
}
function titleFields(slide){
  var o = {company:'',presenter:'',designation:'',date:'',website:'',tagline:'',email:'',phone:'',address:''};
  (slide && Array.isArray(slide.content) ? slide.content : []).forEach(function(it){
    var s=String(it||''); var ci=s.indexOf(':'); if(ci<=0) return;
    var k=s.slice(0,ci).trim().toLowerCase(), v=s.slice(ci+1).trim();
    if (k==='e-mail') k='email';
    if (o.hasOwnProperty(k)) o[k]=v;
  });
  return o;
}
DSTE.items = items; DSTE.titleFields = titleFields;

function kindOf(slide){
  var t = String(slide && slide.type || 'bullet');
  if (t==='chart') return 'chart';
  if (t==='title') return 'cover';
  if (t==='agenda') return 'agenda';
  if (t==='closing') return 'closing';
  if (t==='quote') return 'quote';
  if (t==='stats') return 'stats';
  if (t==='timeline') return 'timeline';
  if (t==='section') return 'section';
  var n = items(slide).length;
  if (n <= 1) return 'text';
  if (n === 2) return 'p2';
  if (n === 3) return 'p3';
  if (n === 4) return 'p4';
  if (n <= 6) return 'p56';
  if (n <= 8) return 'p78';
  return 'p9'; /* 9-16 points */
}
DSTE.kindOf = kindOf;

/* ---------------- icons ---------------- */
var ICON_KW = [
 [/team|people|staff|talent|hire|hiring|member|employee|human/,'mdi:account-group'],
 [/customer|client|user|audience|buyer|persona/,'mdi:account-heart'],
 [/revenue|profit|money|cash|price|pricing|cost|fund|budget|financ|invest|capital|\$/,'mdi:cash-multiple'],
 [/growth|increase|scale|expan|grow|rise/,'mdi:trending-up'],
 [/market|share|segment|competit/,'mdi:chart-pie'],
 [/data|analytic|metric|insight|report|measure|kpi/,'mdi:chart-bar'],
 [/ai|machine|model|intelligen|neural|algorithm|smart/,'mdi:brain'],
 [/secur|privacy|protect|safe|trust|complian|risk/,'mdi:shield-check'],
 [/cloud|platform|infra|server|hosting/,'mdi:cloud-outline'],
 [/tech|software|digital|app|code|develop|engineer|system|tool|automat/,'mdi:cog-outline'],
 [/speed|fast|efficien|quick|agil|perform/,'mdi:lightning-bolt'],
 [/goal|target|focus|objective|aim|mission/,'mdi:target'],
 [/idea|innovat|creativ|concept|vision/,'mdi:lightbulb-on-outline'],
 [/global|world|international|region|country|expansion/,'mdi:earth'],
 [/time|schedule|deadline|timeline|plan|roadmap|phase/,'mdi:calendar-clock'],
 [/partner|collab|deal|allian|relationship|network/,'mdi:handshake-outline'],
 [/sustain|green|eco|environment|energy|climate|carbon/,'mdi:leaf'],
 [/health|care|wellness|medical|patient/,'mdi:heart-pulse'],
 [/learn|educat|train|skill|knowledge|course/,'mdi:school-outline'],
 [/market(ing)?|brand|campaign|social|content|advert|media|promot/,'mdi:bullhorn-outline'],
 [/communicat|message|email|chat|support|service/,'mdi:message-text-outline'],
 [/quality|award|best|excellen|win|success|achiev/,'mdi:trophy-outline'],
 [/launch|start|mvp|rocket|begin/,'mdi:rocket-launch-outline'],
 [/product|feature|design|build/,'mdi:cube-outline'],
 [/operat|process|workflow|logistic|supply|manufactur/,'mdi:factory'],
 [/legal|regulat|policy|govern|law/,'mdi:scale-balance'],
 [/mobile|phone/,'mdi:cellphone'],
 [/search|research|discover|explor/,'mdi:magnify'],
 [/problem|challenge|issue|pain|barrier/,'mdi:alert-circle-outline'],
 [/solution|solve|fix|answer|resolve/,'mdi:puzzle-outline'],
 [/check|complete|done|result|outcome|deliver/,'mdi:check-decagram-outline']
];
var ICON_DEF = ['mdi:lightbulb-on-outline','mdi:chart-line','mdi:target','mdi:rocket-launch-outline','mdi:shield-check-outline','mdi:account-group-outline','mdi:cog-outline','mdi:star-four-points-outline'];
function iconFor(slide, i, text){
  var ic = slide && Array.isArray(slide.icons) ? slide.icons[i] : null;
  if (ic && /^[a-z0-9-]+:[a-z0-9-]+$/.test(String(ic).trim())) return String(ic).trim();
  var t = String(text||'').toLowerCase();
  for (var k=0;k<ICON_KW.length;k++){ if (ICON_KW[k][0].test(t)) return ICON_KW[k][1]; }
  return ICON_DEF[i % ICON_DEF.length];
}
DSTE.iconFor = iconFor;

/* ---------------- text measurement + fitting ---------------- */
var _cx = null, _fitCache = {};
function cx(){ if(_cx===null){ try{ _cx=document.createElement('canvas').getContext('2d'); }catch(e){ _cx=false; } } return _cx; }
function measure(txt, size, fam, wt){
  var c = cx();
  if (!c) return txt.length * size * 0.52;
  c.font = (wt||400) + ' ' + size + 'px "' + fam + '", Arial, sans-serif';
  return c.measureText(txt).width;
}
function countLines(text, w, size, fam, wt, caps, ls){
  var paras = String(text).split('\n'), n = 0, over = false;
  var lsPx = (ls||0) * size;
  var spaceW = measure(' ', size, fam, wt) + lsPx;
  for (var p=0;p<paras.length;p++){
    var words = (caps ? paras[p].toUpperCase() : paras[p]).split(/\s+/).filter(Boolean);
    if (!words.length){ n++; continue; }
    var line = 0; n++;
    for (var i=0;i<words.length;i++){
      var ww = measure(words[i], size, fam, wt) + lsPx*words[i].length;
      if (ww > w) over = true;
      if (line === 0) line = ww;
      else if (line + spaceW + ww <= w) line += spaceW + ww;
      else { n++; line = ww; }
    }
  }
  return {n:n, over:over};
}
/* o: {text,w,h,max,min,font(family),wt,lh,caps,ls,maxLines} -> {s,n} */
function fit(o){
  var text = String(o.text||'');
  var key = [text,o.w,o.h,o.max,o.min,o.font,o.wt,o.lh,o.caps?1:0,o.ls||0,o.maxLines||0].join('|');
  if (_fitCache[key]) return _fitCache[key];
  var w = o.w * 0.94, lh = o.lh || 1.2, res = null;
  for (var s = o.max; s >= o.min; s -= (s > 30 ? 2 : 1)) {
    var r = countLines(text, w, s, o.font, o.wt, o.caps, o.ls);
    if (!r.over && r.n * s * lh <= o.h + 0.5 && (!o.maxLines || r.n <= o.maxLines)) { res = {s:s, n:r.n}; break; }
  }
  if (!res) { var r2 = countLines(text, w, o.min, o.font, o.wt, o.caps, o.ls); res = {s:o.min, n:r2.n}; }
  _fitCache[key] = res;
  return res;
}
DSTE.fit = fit;
DSTE.clearFit = function(){ _fitCache = {}; };

/* ---------------- render context ---------------- */
function Ctx(T, slide, idx, vid, deck){
  this.T = T; this.P = T.pal; this.S = T.s || {};
  this.s = slide || {}; this.i = idx; this.vid = vid; this.deck = deck || null;
  this.els = []; this.bgFill = {fill:T.pal.bg};
  this.kind = kindOf(this.s);
  this.it = items(this.s);
  this.total = deck && deck.slides ? deck.slides.length : 0;
  this.cover = deck && deck.slides && deck.slides[0] ? titleFields(deck.slides[0]) : titleFields(this.s);
}
var CP = Ctx.prototype;
CP.font = function(k){ return k==='head' ? this.T.fonts.head : this.T.fonts.body; };
CP.add = function(e){ this.els.push(e); return e; };
CP.bg = function(o){ this.bgFill = o; };
CP.rect = function(x,y,w,h,o){ o=o||{}; return this.add({t:'rect',x:x,y:y,w:w,h:h,fill:o.fill,op:o.op,r:o.r,line:o.line,lw:o.lw,lop:o.lop,grad:o.grad,shadow:o.shadow,dash:o.dash}); };
CP.ell = function(x,y,w,h,o){ o=o||{}; return this.add({t:'ell',x:x,y:y,w:w,h:h,fill:o.fill,op:o.op,line:o.line,lw:o.lw,lop:o.lop,grad:o.grad,shadow:o.shadow,dash:o.dash}); };
CP.shape = function(prst,x,y,w,h,o){ o=o||{}; return this.add({t:'shape',prst:prst,x:x,y:y,w:w,h:h,fill:o.fill,op:o.op,line:o.line,lw:o.lw,lop:o.lop,grad:o.grad,rot:o.rot}); };
CP.line = function(x1,y1,x2,y2,o){ o=o||{}; return this.add({t:'line',x1:x1,y1:y1,x2:x2,y2:y2,color:o.color,lw:o.lw||1,op:o.op,dash:o.dash}); };
CP.path = function(d,o){ o=o||{}; return this.add({t:'path',d:d,stroke:o.stroke,lw:o.lw,fill:o.fill,op:o.op,lop:o.lop}); };
CP.icon = function(x,y,sz,name,color){ return this.add({t:'icon',x:x,y:y,w:sz,h:sz,name:name,color:hx(color)}); };
CP.ring = function(x,y,d,pct,o){ o=o||{}; return this.add({t:'ring',x:x,y:y,w:d,h:d,pct:clamp(pct,0,100),color:o.color,track:o.track,th:o.th||12,top:o.top}); };
CP.img = function(x,y,w,h,o){ o=o||{}; return this.add({t:'img',x:x,y:y,w:w,h:h,r:o.r||0,ph:o.ph||null,shape:o.shape||null}); };
/* text. o: {size|fit:{max,min}, font:'head'|'body', wt, color, align, va, lh, caps, ls, italic, op, ed:{f,i,p}, maxLines} */
CP.text = function(x,y,w,h,text,o){
  o=o||{}; text = strip(text);
  var fam = this.font(o.font||'body');
  var wt = o.wt || (o.font==='head' ? this.T.fonts.headW : this.T.fonts.bodyW) || 400;
  var lh = o.lh || (o.font==='head' ? (this.T.fonts.headLh||1.1) : 1.35);
  var caps = o.caps!=null ? o.caps : (o.font==='head' ? !!this.T.fonts.headCaps : false);
  var ls = o.ls!=null ? o.ls : (o.font==='head' ? (this.T.fonts.headLs||0) : 0);
  var size = o.size, n = 1;
  if (!size || o.fit) {
    var f = o.fit || {max:size, min:Math.max(9,Math.round(size*0.6))};
    var r = fit({text:text,w:w,h:h,max:f.max,min:f.min,font:fam,wt:wt,lh:lh,caps:caps,ls:ls,maxLines:o.maxLines});
    size = r.s; n = r.n;
  } else {
    n = countLines(text, w*0.94, size, fam, wt, caps, ls).n;
  }
  var e = this.add({t:'text',x:x,y:y,w:w,h:h,text:text,size:size,font:(o.font||'body'),fam:fam,wt:wt,color:hx(o.color||this.P.text),align:o.align||'l',va:o.va||'t',lh:lh,caps:caps,ls:ls,italic:!!o.italic,op:o.op,ed:o.ed||null,nw:o.maxLines===1?1:0});
  e._n = n; e._h = Math.min(h, n*size*lh);
  return e;
};
/* measure only (no element) */
CP.measureText = function(text,w,o){
  o=o||{}; var fam=this.font(o.font||'body');
  var wt=o.wt||(o.font==='head'?this.T.fonts.headW:this.T.fonts.bodyW)||400;
  var lh=o.lh||(o.font==='head'?(this.T.fonts.headLh||1.1):1.35);
  var caps=o.caps!=null?o.caps:(o.font==='head'?!!this.T.fonts.headCaps:false);
  var ls=o.ls!=null?o.ls:(o.font==='head'?(this.T.fonts.headLs||0):0);
  var r=fit({text:strip(text),w:w,h:o.h||9999,max:o.max,min:o.min,font:fam,wt:wt,lh:lh,caps:caps,ls:ls,maxLines:o.maxLines});
  return {s:r.s,n:r.n,h:r.n*r.s*lh,lh:lh};
};
/* common fit size for a group of texts in equal boxes */
CP.groupFit = function(texts,w,h,o){
  var self=this, s=o.max;
  texts.forEach(function(t){ if(!t) return; var r=self.measureText(t,w,{font:o.font,wt:o.wt,lh:o.lh,caps:o.caps,ls:o.ls,max:o.max,min:o.min,h:h,maxLines:o.maxLines}); if(r.s<s) s=r.s; });
  return s;
};
DSTE.Ctx = Ctx;

/* ============================================================
   STYLE PIECES (driven by template tokens T.s)
   ============================================================ */
/* card background */
function card(C,x,y,w,h,o){
  o=o||{}; var S=C.S.card||{}, P=C.P, r=S.r!=null?S.r:16, k=o.kind||S.kind||'solid';
  var hl = !!o.hl;
  if (hl && S.hl) { k = S.hl.kind || 'solid'; }
  switch(k){
    case 'none': break;
    case 'rule':  C.rect(x,y,w,3,{fill:hl?P.accent2||P.accent:P.accent}); break;
    case 'outline': C.rect(x,y,w,h,{fill:S.fill||null,op:S.op,line:S.line||P.line,lw:S.lw||1.5,r:r}); break;
    case 'glass': C.rect(x,y,w,h,{fill:S.fill||'#FFFFFF',op:S.op!=null?S.op:0.42,line:S.line||'#FFFFFF',lop:S.lop!=null?S.lop:0.7,lw:S.lw||1.2,r:r,shadow:S.shadow}); break;
    case 'grad': C.rect(x,y,w,h,{grad:hl&&S.hl&&S.hl.grad?S.hl.grad:(S.grad||{ang:135,stops:[[0,P.card],[100,mix(P.card,P.accent,0.25)]]}),r:r,line:S.line,lw:S.lw,lop:S.lop,shadow:S.shadow}); break;
    case 'bar': C.rect(x,y,w,h,{fill:S.fill||P.card,op:S.op,r:r,shadow:S.shadow}); C.rect(x,y,S.barW||6,h,{fill:P.accent,r:0}); break;
    case 'top': C.rect(x,y,w,h,{fill:S.fill||P.card,op:S.op,r:r,shadow:S.shadow,line:S.line,lw:S.lw}); C.rect(x,y,w,S.barW||5,{fill:P.accent}); break;
    default: C.rect(x,y,w,h,{fill:hl&&S.hl&&S.hl.fill?S.hl.fill:(S.fill||P.card),op:S.op,r:r,line:S.line,lw:S.lw,lop:S.lop,shadow:S.shadow});
  }
  var dark = hl && S.hl ? (S.hl.dark!=null?S.hl.dark:true) : (S.dark!=null ? S.dark : lum(S.fill||P.card) < 0.45 && k!=='none' && k!=='rule');
  return {hl: hl&&!!S.hl, tc: hl&&S.hl&&S.hl.tc ? S.hl.tc : (S.tc || (dark ? '#FFFFFF' : P.text)), mc: hl&&S.hl&&S.hl.mc ? S.hl.mc : (S.mc || (dark ? mix('#FFFFFF',S.fill||P.card,0.28) : P.muted))};
}
DSTE.card = card;

/* icon mark (icon inside a shape) */
function mark(C,x,y,sz,name,o){
  o=o||{}; var S=C.S.icon||{}, P=C.P, k=o.kind||S.kind||'circle';
  var bg=o.bg||S.bg||P.accent, fg=o.fg||S.fg||P.onAccent||'#FFFFFF';
  var isz=Math.round(sz*(S.scale||0.52));
  var ix=x+(sz-isz)/2, iy=y+(sz-isz)/2;
  var gr=o.bg?null:S.grad;
  if (k==='circle') C.ell(x,y,sz,sz,{fill:bg,grad:gr});
  else if (k==='square') C.rect(x,y,sz,sz,{fill:bg,r:S.r!=null?S.r:Math.round(sz*0.26),grad:gr});
  else if (k==='ring') { C.ell(x,y,sz,sz,{line:bg,lw:S.lw||2}); fg=o.fg||S.fg||bg; }
  else if (k==='soft') { C.ell(x,y,sz,sz,{fill:bg,op:S.op||0.14}); fg=o.fg||S.fg||bg; }
  else if (k==='softsq') { C.rect(x,y,sz,sz,{fill:bg,op:S.op||0.14,r:S.r!=null?S.r:Math.round(sz*0.26)}); fg=o.fg||S.fg||bg; }
  else if (k==='hex') C.shape('hexagon',x,y,sz,sz,{fill:bg});
  else { isz=sz; ix=x; iy=y; fg=o.fg||S.fg||bg; }
  C.icon(ix,iy,isz,name,fg);
}
DSTE.mark = mark;

/* number badge */
function num(C,x,y,sz,n,o){
  o=o||{}; var S=C.S.num||{}, P=C.P, k=o.kind||S.kind||'circle';
  var bg=o.bg||S.bg||P.accent, fg=o.fg||S.fg||P.onAccent||'#FFFFFF', t=pad2(n);
  if (k==='big') { C.text(x,y,sz*2,sz,t,{font:'head',size:Math.round(sz*0.9),color:o.fg||S.fg||P.accent,wt:C.T.fonts.headW,lh:1,va:'m',caps:false,ls:0}); return; }
  if (k==='circle') C.ell(x,y,sz,sz,{fill:bg});
  else if (k==='square') C.rect(x,y,sz,sz,{fill:bg,r:S.r!=null?S.r:6});
  else if (k==='ring') { C.ell(x,y,sz,sz,{line:bg,lw:2}); fg=o.fg||S.fg||bg; }
  else if (k==='pill') C.rect(x,y,sz*1.5,sz,{fill:bg,r:sz/2});
  var w = k==='pill'?sz*1.5:sz;
  C.text(x,y,w,sz,t,{font:S.font||'head',size:Math.round(sz*0.42),color:fg,align:'c',va:'m',lh:1,wt:700,caps:false,ls:0});
}
DSTE.num = num;

/* ---------------- header ---------------- */
function header(C,A,o){
  o=o||{}; var S=C.S.header||{}, P=C.P, T=C.T;
  var k=o.hk||S.kind||'left', y=A.top!=null?A.top:(S.top||56), x=A.x, w=A.w;
  var tMax=o.tMax||S.tMax||44, tMin=S.tMin||26, sMax=S.sMax||19, sMin=S.sMin||13;
  var title=C.s.title||'', sub=C.s.subtitle||'';
  var tc=o.tc||S.tc||P.title||P.text, sc=o.sc||S.sc||P.muted, al=k==='center'?'c':'l';
  if (S.kicker && !o.noKicker) {
    var kt = typeof S.kicker==='function' ? S.kicker(C) : pad2(C.i+1);
    if (kt) {
      if (S.kPill) {
        var ks=S.kSize||12, kw=Math.min(w, C.measureText(kt, 2000, {font:'body', wt:700, max:ks, min:ks, caps:true, ls:0.14}).s*0 + String(kt).length*ks*0.72+34);
        var kx=al==='c'?x+(w-kw)/2:x;
        C.rect(kx,y,kw,28,{fill:S.kPillFill||null,op:S.kPillOp,line:S.kPill===true?(S.kc||P.accent):S.kPill,lw:1.2,r:14});
        C.text(kx,y,kw,28,kt,{font:'body',size:ks,wt:700,color:S.kc||P.accent,align:'c',va:'m',caps:true,ls:0.14,lh:1});
        y+=S.kGap||44;
      } else { C.text(x,y,w,18,kt,{font:'body',size:S.kSize||13,wt:700,color:S.kc||P.accent,align:al,caps:true,ls:0.18,lh:1}); y+=S.kGap||28; }
    }
  }
  if (S.bar && !o.noBar) {
    var bw=S.barW||56; C.rect(al==='c'?x+(w-bw)/2:x,y,bw,S.barH||5,{fill:S.barC||P.accent,r:S.barR!=null?S.barR:3}); y+=S.barGap||22;
  }
  if (k==='split') {
    var tw=Math.round(w*0.5);
    var te=C.text(x,y,tw,S.tH||118,title,{font:'head',fit:{max:tMax,min:tMin},color:tc,ed:{f:'title'},maxLines:3});
    var sw=w-tw-40;
    if (sub) C.text(x+tw+40,y,sw,te._h,sub,{font:'body',fit:{max:sMax,min:sMin},color:sc,va:'b',ed:{f:'subtitle'},lh:1.4});
    return y+te._h;
  }
  var te2=C.text(x,y,w,S.tH||(o.tH||104),title,{font:'head',fit:{max:tMax,min:tMin},color:tc,align:al,ed:{f:'title'},maxLines:2});
  y+=te2._h+(S.gap||12);
  if (sub && !o.noSub) { var se=C.text(x,y,w,S.sH||52,sub,{font:'body',fit:{max:sMax,min:sMin},color:sc,align:al,ed:{f:'subtitle'},lh:1.4,maxLines:2}); y+=se._h; }
  if (S.rule && !o.noRule) { y+=16; C.rect(al==='c'?x+w/2-40:x,y,al==='c'?80:w,S.ruleH||1.5,{fill:S.ruleC||P.line,op:S.ruleOp}); }
  return y;
}
DSTE.header = header;

/* ---------------- conclusion strip ---------------- */
function conclusion(C,x,y,w,h,o){
  var txt = C.s.conclusion; if (!txt) return;
  o=o||{}; var S=C.S.conc||{}, P=C.P, k=o.kind||S.kind||'bar';
  var label = S.label!=null ? S.label : 'Key takeaway';
  var tc=S.tc||P.text, lc=S.lc||P.accent, pad=S.pad||20, tx=x, tw=w;
  if (k==='band') { C.rect(x,y,w,h,{fill:S.fill||P.accent,op:S.op,r:S.r!=null?S.r:0,grad:S.grad}); tc=S.tc||P.onAccent||'#FFFFFF'; lc=S.lc||tc; tx=x+pad; tw=w-pad*2; }
  else if (k==='pill') { C.rect(x,y,w,h,{fill:S.fill||P.accent,op:S.op!=null?S.op:0.12,r:h/2,line:S.line,lw:S.lw}); tx=x+h/2; tw=w-h; }
  else if (k==='box') { C.rect(x,y,w,h,{fill:S.fill||P.card,op:S.op,r:S.r!=null?S.r:10,line:S.line,lw:S.lw}); tx=x+pad; tw=w-pad*2; }
  else if (k==='line') { C.rect(x,y,w,1.5,{fill:S.lineC||P.line}); y+=8; h-=8; }
  else { C.rect(x,y+6,5,h-12,{fill:S.barC||P.accent,r:2}); tx=x+22; tw=w-22; }
  var lw=0;
  if (label) {
    var lbw = Math.min(190, Math.max(110, label.length*9.2+20));
    if (S.chip) { C.rect(tx,y+(h-26)/2,lbw,26,{fill:lc,r:13}); C.text(tx,y+(h-26)/2,lbw,26,label,{font:'body',size:11.5,wt:700,color:S.chipTc||P.onAccent||'#FFFFFF',caps:true,ls:0.12,align:'c',va:'m',lh:1}); }
    else C.text(tx,y,lbw,h,label,{font:'body',size:12,wt:800,color:lc,caps:true,ls:0.14,va:'m',lh:1.1});
    lw=lbw+14;
  }
  C.text(tx+lw,y+4,tw-lw,h-8,txt,{font:'body',fit:{max:S.max||17,min:12},color:tc,va:'m',italic:!!S.italic,wt:S.wt||500,lh:1.3,ed:{f:'x-conc'},maxLines:2});
}
DSTE.conclusion = conclusion;

/* ---------------- footer ---------------- */
function footer(C){
  var S=C.S.footer; if (S===false) return;
  S=S||{}; var P=C.P, fc=S.color||P.muted, M=C.S.margin||64, y=S.y||(H-34);
  if (typeof S.fn==='function') return S.fn(C);
  var brand = C.cover.company || '';
  if (brand && S.brand!==false) C.text(M,y,500,18,brand,{font:'body',size:12,wt:700,color:fc,caps:true,ls:0.12,lh:1,va:'m'});
  if (S.page!==false) C.text(W-M-120,y,120,18,(S.pagePrefix!=null?S.pagePrefix:'')+pad2(C.i+1),{font:'body',size:12,wt:700,color:fc,align:'r',ls:0.1,lh:1,va:'m'});
}
DSTE.footer = footer;

/* image zone helper: bleed or framed */
function imgBox(C,x,y,w,h,o){
  o=o||{}; var S=C.S, P=C.P;
  var ph = S.imgPh || {grad:{ang:135,stops:[[0,mix(P.accent,P.bg,0.35)],[100,mix(P.accent2||P.accent,P.bg,0.65)]]}};
  return C.img(x,y,w,h,{r:o.r!=null?o.r:0,ph:ph,shape:o.shape});
}
DSTE.imgBox = imgBox;

/* ============================================================
   BODY LAYOUTS  (C, Z={x,y,w,h}, o)
   ============================================================ */
var B = DSTE.B = {};

function markOrNum(C,x,y,sz,it,k,o,cc){
  var inv = cc && cc.hl ? {bg:cc.tc, fg:(C.S.card&&C.S.card.hl&&(C.S.card.hl.fill||(C.S.card.hl.grad&&C.S.card.hl.grad.stops[0][1])))||C.P.accent} : null;
  if ((o.mark||C.S.mark||'icon')==='num') num(C,x,y,sz,k+1,inv?{bg:inv.bg,fg:(C.S.num&&C.S.num.kind==='big'||C.S.num&&C.S.num.kind==='ring')?inv.bg:inv.fg}:o.numO);
  else mark(C,x,y,sz,iconFor(C.s,it.i,it.h+' '+it.d),inv?((C.S.icon&&/plain|ring|soft/.test(C.S.icon.kind||''))?{fg:inv.bg}:inv):o.markO);
}

/* cards in one row (2-5 items), icon top — content fills / centres in the card */
B.cols = function(C,Z,o){
  var it=C.it.slice(0,o.max||6), n=it.length; if(!n) return;
  var gap=o.gap||24, cw=(Z.w-gap*(n-1))/n, P=C.P, S=C.S;
  var flat=S.card&&(S.card.kind==='none'||S.card.kind==='rule');
  var pad=o.pad!=null?o.pad:(flat?0:28);
  var ms=o.ms||(n>=4?50:58), innerW=cw-pad*2;
  var topY = pad + (S.card&&S.card.kind==='rule'?20:0);
  var hMax=o.hMax||(n>=5?19:(n===4?21:(n===3?25:28))), dMax=o.dMax||(n>=5?15:(n===4?17:(n===3?20:22)));
  var hs=C.groupFit(it.map(function(t){return t.h;}),innerW,70,{font:'head',max:hMax,min:14,maxLines:2});
  var hh=0; it.forEach(function(t){var m=C.measureText(t.h,innerW,{font:'head',max:hs,min:hs});hh=Math.max(hh,m.h);});
  var dAvail=Z.h-topY-ms-18-hh-12-pad;
  var ds=C.groupFit(it.map(function(t){return t.d;}),innerW,dAvail,{font:'body',max:dMax,min:11,lh:1.45});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,innerW,{font:'body',max:ds,min:ds,lh:1.45}).h);});
  var slack=Math.max(0,dAvail-dh), off=o.top?0:Math.round(slack*(o.center?0.5:0.42));
  it.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), hl=o.hl==='first'?k===0:(o.hl==='alt'?k%2===1:false);
    var cc=card(C,x,Z.y,cw,Z.h,{hl:hl});
    var al=o.center?'c':'l', y=Z.y+topY+off;
    var mx=o.center?x+(cw-ms)/2:x+pad;
    markOrNum(C,mx,y,ms,t,k,o,cc);
    if (t.h) C.text(x+pad,y+ms+18,innerW,hh,t.h,{font:'head',size:hs,color:cc.tc,align:al,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x+pad,y+ms+18+hh+12,innerW,dh+4,t.d,{font:'body',size:ds,color:cc.mc,align:al,ed:{f:'x-part',i:t.i,p:1},lh:1.45});
  });
};

/* grid of cards: icon left + text (or stacked when narrow), centred vertically */
B.grid = function(C,Z,o){
  var it=C.it.slice(0,8), n=it.length; if(!n) return;
  var cols=o.cols||(n<=4?2:(n<=6?3:4)), rows=Math.ceil(n/cols);
  var gx=o.gap||22, gy=o.gapY||20, cw=(Z.w-gx*(cols-1))/cols, ch=(Z.h-gy*(rows-1))/rows;
  var pad=o.pad||(ch>160?26:20), ms=o.ms||(ch>170?54:(ch>120?46:40));
  var stacked = cols>=4 || cw<330;
  var tx=stacked?pad:pad+ms+18, tw=cw-tx-pad;
  var hMax=o.hMax||(n<=4?23:(n<=6?19:17)), dMax=o.dMax||(n<=4?19:(n<=6?16:14));
  var hs=C.groupFit(it.map(function(t){return t.h;}),tw,60,{font:'head',max:hMax,min:13,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,tw,{font:'head',max:hs,min:hs}).h);});
  var head=(stacked?ms+14:0)+hh+8, dAvail=ch-pad*2-head;
  var ds=C.groupFit(it.map(function(t){return t.d;}),tw,dAvail,{font:'body',max:dMax,min:10.5,lh:1.42});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,tw,{font:'body',max:ds,min:ds,lh:1.42}).h);});
  var off=Math.round(Math.max(0,dAvail-dh)*0.45);
  it.forEach(function(t,k){
    var r=Math.floor(k/cols), c=k%cols, inRow=Math.min(cols,n-r*cols);
    var shift = inRow<cols ? (cols-inRow)*(cw+gx)/2 : 0;
    var x=Z.x+shift+c*(cw+gx), y=Z.y+r*(ch+gy);
    var cc=card(C,x,y,cw,ch,{hl:o.hl==='first'?k===0:(o.hl==='alt'?k%2===1:false)});
    var cy=y+pad+off;
    markOrNum(C,x+pad,stacked?cy:cy-2,ms,t,k,o,cc);
    var hy=stacked?cy+ms+14:cy;
    if(t.h) C.text(x+tx,hy,tw,hh,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x+tx,hy+hh+8,tw,dh+4,t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.42});
  });
};

/* vertical list rows (one or two columns) */
B.list = function(C,Z,o){
  var it=C.it.slice(0,o.max||16), n=it.length; if(!n) return;
  var cols=o.cols||(n>5?2:1), per=Math.ceil(n/cols), gx=36, cw=(Z.w-gx*(cols-1))/cols;
  var gy=o.gap!=null?o.gap:(o.cards?14:10), rh=(Z.h-gy*(per-1))/per, ms=o.ms||Math.min(46,rh-12);
  var pad=o.cards?18:0, tx=pad+ms+18, tw=cw-tx-pad;
  var inline = rh < 76;
  var hs=C.groupFit(it.map(function(t){return t.h;}),inline?tw*0.36:tw,32,{font:'head',max:o.hMax||clamp(Math.round(rh*0.2),16,24),min:13,maxLines:inline?2:1});
  var hH = inline?0:hs*1.2+4;
  var ds=C.groupFit(it.map(function(t){return t.d;}),inline?tw*0.62:tw,rh-hH-(o.cards?20:4),{font:'body',max:o.dMax||clamp(Math.round(rh*0.19),14,21),min:10.5});
  it.forEach(function(t,k){
    var c=Math.floor(k/per), r=k%per, x=Z.x+c*(cw+gx), y=Z.y+r*(rh+gy);
    var cc={tc:C.P.text,mc:C.P.muted};
    if (o.cards) cc=card(C,x,y,cw,rh,{});
    else if (o.rules && r>0) C.rect(x,y-gy/2,cw,1,{fill:C.P.line,op:0.7});
    markOrNum(C,x+pad,y+(rh-ms)/2,ms,t,k,o);
    if (inline) {
      if(t.h) C.text(x+tx,y,tw*0.34,rh,t.h,{font:'head',size:hs,color:cc.tc,va:'m',ed:{f:'x-part',i:t.i,p:0},lh:1.15});
      C.text(x+tx+tw*0.37,y,tw*0.63,rh,t.d,{font:'body',size:ds,color:cc.mc,va:'m',ed:{f:'x-part',i:t.i,p:1},lh:1.35});
    } else {
      var blockH = (t.h?hH:0)+C.measureText(t.d,tw,{font:'body',max:ds,min:ds}).h;
      var by = y+Math.max(o.cards?10:0,(rh-blockH)/2);
      if(t.h) C.text(x+tx,by,tw,hH,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15,maxLines:1});
      C.text(x+tx,by+(t.h?hH:0),tw,rh-(by-y)-(t.h?hH:0),t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.38});
    }
  });
};

/* big numbered columns with top rule & dot (value proposition style) */
B.numbered = function(C,Z,o){
  var it=C.it.slice(0,8), n=it.length; if(!n) return;
  var cols=n<=4?n:Math.ceil(n/2), rows=Math.ceil(n/cols), gx=o.gap||30, gy=26;
  var cw=(Z.w-gx*(cols-1))/cols, ch=(Z.h-gy*(rows-1))/rows, P=C.P;
  var nh=o.numSize||(rows>1?38:52);
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw,54,{font:'head',max:o.hMax||(n<=3?24:21),min:13,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,cw,{font:'head',max:hs,min:hs}).h);});
  var dTop=22+nh+12+hh+8;
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw,ch-dTop,{font:'body',max:o.dMax||(n<=3?20:(n<=4?17:15)),min:10.5});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,cw,{font:'body',max:ds,min:ds}).h);});
  var blk=(dTop+dh)*rows+gy*(rows-1), oy=Math.max(0,(Z.h-blk)/2); if(rows>1) oy=0;
  if(rows===1){ ch=dTop+dh+10; }
  for (var r=0;r<rows;r++){ var ry=Z.y+oy+r*(ch+gy); C.rect(Z.x,ry+6,Z.w,o.lineW||2,{fill:o.lineC||P.line}); }
  it.forEach(function(t,k){
    var r=Math.floor(k/cols), c=k%cols, x=Z.x+c*(cw+gx), y=Z.y+oy+r*(ch+gy);
    C.ell(x,y,14,14,{fill:P.accent});
    C.text(x,y+22,cw,nh,pad2(k+1)+(o.dot?'.':''),{font:'head',size:nh,color:o.numC||P.accent,lh:1,caps:false,ls:0});
    if(t.h) C.text(x,y+22+nh+12,cw,hh,t.h,{font:'head',size:hs,color:P.text,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x,y+dTop,cw,ch-dTop,t.d,{font:'body',size:ds,color:P.muted,ed:{f:'x-part',i:t.i,p:1},lh:1.4});
  });
};

/* hub: centre medallion with items on both sides */
B.hub = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, d=Math.min(Z.h*0.78, 250), cx0=Z.x+Z.w/2, cy0=Z.y+Z.h/2;
  var left=Math.ceil(n/2), right=n-left, sideW=(Z.w-d)/2-44;
  C.ell(cx0-d/2-14,cy0-d/2-14,d+28,d+28,{line:P.accent,lw:1.5,lop:0.5,dash:true});
  C.ell(cx0-d/2,cy0-d/2,d,d,{grad:o.hubGrad||C.S.hubGrad||{type:'radial',stops:[[0,mix(P.accent,'#FFFFFF',0.2)],[100,P.accent]]}});
  C.icon(cx0-d*0.18,cy0-d*0.18-10,d*0.36,iconFor(C.s,99,C.s.title),P.onAccent||'#FFFFFF');
  var tt = String(C.s.title||'').split(/\s+/).slice(0,3).join(' ');
  C.text(cx0-d/2+22,cy0+d*0.2,d-44,d*0.24,tt,{font:'head',fit:{max:18,min:11},color:P.onAccent||'#FFFFFF',align:'c',maxLines:2,lh:1.1});
  var rows=Math.max(left,right), rh=(Z.h-(rows-1)*16)/rows;
  var hs=C.groupFit(it.map(function(t){return t.h;}),sideW-64,40,{font:'head',max:19,min:13,maxLines:1});
  var ds=C.groupFit(it.map(function(t){return t.d;}),sideW-64,rh-hs*1.25-8,{font:'body',max:15,min:10.5});
  it.forEach(function(t,k){
    var isL=k<left, r=isL?k:k-left, y=Z.y+r*(rh+16), x=isL?Z.x:cx0+d/2+44;
    var ms=44, mx=isL?x+sideW-ms:x, tx=isL?x:x+ms+16, al=isL?'r':'l';
    mark(C,mx,y+4,ms,iconFor(C.s,t.i,t.h+' '+t.d),{});
    if(t.h) C.text(isL?x:tx,y,sideW-64,hs*1.25,t.h,{font:'head',size:hs,color:P.text,align:al,ed:{f:'x-part',i:t.i,p:0},lh:1.15,maxLines:1});
    C.text(isL?x:tx,y+hs*1.25+6,sideW-64,rh-hs*1.25-6,t.d,{font:'body',size:ds,color:P.muted,align:al,ed:{f:'x-part',i:t.i,p:1},lh:1.38});
    var ly=y+4+ms/2; if(isL) C.line(x+sideW+4,ly,cx0-d/2-16,cy0+(ly-cy0)*0.5,{color:P.accent,lw:1.2,op:0.5}); else C.line(cx0+d/2+16,cy0+(ly-cy0)*0.5,x-4,ly,{color:P.accent,lw:1.2,op:0.5});
  });
};

/* horizontal gradient bars, one per item */
B.bars = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, gy=14, bh=(Z.h-gy*(n-1))/n, lw=Math.round(Z.w*0.3);
  var hs=C.groupFit(it.map(function(t){return t.h;}),lw-bh-24,bh-8,{font:'head',max:20,min:13,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),Z.w-lw-40,bh-10,{font:'body',max:15.5,min:10.5});
  it.forEach(function(t,k){
    var y=Z.y+k*(bh+gy), f=1-k*0.12;
    var g=o.grad?o.grad(k):{ang:90,stops:[[0,P.accent],[100,mix(P.accent2||P.accent,P.bg,0.35)]]};
    C.rect(Z.x,y,lw,bh,{grad:g,r:C.S.card&&C.S.card.r!=null?Math.min(C.S.card.r,bh/2):10,op:f});
    C.text(Z.x+18,y,bh*0.9,bh,pad2(k+1),{font:'head',size:Math.min(34,bh*0.5),color:P.onAccent||'#FFFFFF',va:'m',lh:1,caps:false,ls:0});
    if(t.h) C.text(Z.x+18+bh*0.9,y,lw-bh*0.9-30,bh,t.h,{font:'head',size:hs,color:P.onAccent||'#FFFFFF',va:'m',ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(Z.x+lw+28,y,Z.w-lw-28,bh,t.d,{font:'body',size:ds,color:P.muted,va:'m',ed:{f:'x-part',i:t.i,p:1},lh:1.38});
  });
};

/* two big panels side by side (p2) */
B.duo = function(C,Z,o){
  var it=C.it.slice(0,2); if(!it.length) return;
  var P=C.P, gap=o.gap||28, cw=(Z.w-gap)/2, pad=34;
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-pad*2,70,{font:'head',max:28,min:16,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,cw-pad*2,{font:'head',max:hs,min:hs}).h);});
  var ms=58, dTop=pad+ms+20+hh+12;
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-pad*2,Z.h-dTop-pad,{font:'body',max:23,min:12});
  it.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), cc=card(C,x,Z.y,cw,Z.h,{hl:o.hl?k===1:false});
    if (o.bigNum) C.text(x+cw-pad-120,Z.y+pad-8,120,70,pad2(k+1),{font:'head',size:64,color:cc.hl?cc.tc:P.accent,op:0.25,align:'r',lh:1,caps:false,ls:0});
    markOrNum(C,x+pad,Z.y+pad,ms,t,k,o,cc);
    if(t.h) C.text(x+pad,Z.y+pad+ms+20,cw-pad*2,hh,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
    C.text(x+pad,Z.y+dTop,cw-pad*2,Z.h-dTop-pad,t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.45});
  });
  if (o.vs) { var d=58; C.ell(Z.x+cw+gap/2-d/2,Z.y+Z.h/2-d/2,d,d,{fill:P.accent,line:P.bg,lw:4}); C.text(Z.x+cw+gap/2-d/2,Z.y+Z.h/2-d/2,d,d,'VS',{font:'head',size:18,color:P.onAccent||'#FFF',align:'c',va:'m',lh:1,caps:false,ls:0.05}); }
};

/* many points (9-16): compact card grid, icon or number on the left */
B.many = function(C,Z,o){
var it=C.it.slice(0,16), n=it.length; if(!n) return;
var cols=o.cols||(n<=9?3:4), rows=Math.ceil(n/cols);
var gx=o.gap||16, gy=o.gapY||14, cw=(Z.w-gx*(cols-1))/cols, ch=(Z.h-gy*(rows-1))/rows;
var flat=C.S.card&&(C.S.card.kind==='none'||C.S.card.kind==='rule');
var pad=flat?4:Math.max(10,Math.min(18,Math.round(ch*0.14)));
var ms=Math.round(Math.max(22,Math.min(40,ch-pad*2-4,cw*0.18)));
var tx=pad+ms+12, tw=cw-tx-pad;
var hs=C.groupFit(it.map(function(t){return t.h;}),tw,ch*0.42,{font:'head',max:o.hMax||16,min:10,maxLines:2});
var hh=0; it.forEach(function(t){ if(t.h) hh=Math.max(hh,C.measureText(t.h,tw,{font:'head',max:hs,min:hs,maxLines:2}).h); });
var dAvail=Math.max(14,ch-pad*2-(hh?hh+4:0));
var ds=C.groupFit(it.map(function(t){return t.d;}),tw,dAvail,{font:'body',max:o.dMax||13.5,min:8.5,lh:1.3});
it.forEach(function(t,k){
var r=Math.floor(k/cols), c=k%cols, inRow=Math.min(cols,n-r*cols);
var shift = inRow<cols ? (cols-inRow)*(cw+gx)/2 : 0;
var x=Z.x+shift+c*(cw+gx), y=Z.y+r*(ch+gy);
var cc=card(C,x,y,cw,ch,{hl:o.hl==='first'?k===0:false});
var dh=Math.min(dAvail,C.measureText(t.d,tw,{font:'body',max:ds,min:ds,lh:1.3}).h);
var blk=(t.h?hh+4:0)+dh, by=y+Math.max(pad,(ch-blk)/2);
markOrNum(C,x+pad,y+(ch-ms)/2,ms,t,k,o,cc);
if(t.h) C.text(x+tx,by,tw,hh,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
C.text(x+tx,by+(t.h?hh+4:0),tw,Math.max(dh,y+ch-pad-(by+(t.h?hh+4:0))),t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.3});
});
};

/* single rich paragraph */
B.text = function(C,Z,o){
  var P=C.P, it=C.it[0];
  var txt = it ? (it.h ? it.h+': '+it.d : it.d) : (C.s.subtitle||'');
  if (o.box) card(C,Z.x,Z.y,Z.w,Z.h,{});
  var pad=o.box?40:0;
  C.text(Z.x+pad,Z.y+pad,Z.w-pad*2,Z.h-pad*2,txt,{font:'body',fit:{max:30,min:16},color:P.text,ed:it?{f:'content-plain',i:it.i}:null,lh:1.45,va:'m'});
};

/* ---------------- stats ---------------- */
function statItems(C){ return C.it.slice(0,4).map(function(t){ return {i:t.i, v:t.h||t.d.split(' ')[0], l:t.h?t.d:t.d.split(' ').slice(1).join(' '), d:t.x}; }); }
function pctOf(v,k){ var m=String(v).match(/(\d+(?:\.\d+)?)\s*%/); if(m) return clamp(parseFloat(m[1]),3,100); return [78,64,86,55][k%4]; }
B.statCols = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gap=o.gap||30, cw=(Z.w-gap*(n-1))/n, iw=cw-16;
  var vs=C.groupFit(st.map(function(t){return t.v;}),iw,130,{font:'head',max:o.vMax||(n>=4?80:96),min:30,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),iw,60,{font:'head',max:n>=4?20:23,min:12,maxLines:2});
  var lh=0; st.forEach(function(t){lh=Math.max(lh,C.measureText(t.l,iw,{font:'head',max:ls,min:ls}).h);});
  var top=vs*1.08+14+lh+12, dAvail=Z.h-top-20;
  var ds=C.groupFit(st.map(function(t){return t.d;}),iw,dAvail,{font:'body',max:n>=4?16:18,min:10.5,lh:1.42});
  var dh=0; st.forEach(function(t){dh=Math.max(dh,t.d?C.measureText(t.d,iw,{font:'body',max:ds,min:ds,lh:1.42}).h:0);});
  var blockH=top+dh, y0=Z.y+Math.max(0,(Z.h-blockH)/2);
  st.forEach(function(t,k){
    var x=Z.x+k*(cw+gap);
    if (k>0 && o.dividers!==false) C.rect(x-gap/2,y0,1.5,blockH,{fill:P.line});
    C.text(x+8,y0,iw,vs*1.08,t.v,{font:'head',size:vs,color:o.vc||P.accent,lh:1.05,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:-0.01});
    C.text(x+8,y0+vs*1.08+14,iw,lh,t.l,{font:'head',size:ls,color:P.text,lh:1.2,ed:{f:'x-part',i:t.i,p:1}});
    if(t.d) C.text(x+8,y0+top,iw,dh+4,t.d,{font:'body',size:ds,color:P.muted,lh:1.42,ed:{f:'x-part',i:t.i,p:2}});
  });
};
B.statCards = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gap=o.gap||22, cw=(Z.w-gap*(n-1))/n, pad=28, iw=cw-pad*2;
  var vs=C.groupFit(st.map(function(t){return t.v;}),iw,110,{font:'head',max:o.vMax||(n>=4?60:74),min:28,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),iw,56,{font:'head',max:n>=4?19:22,min:12,maxLines:2});
  var lh=0; st.forEach(function(t){lh=Math.max(lh,C.measureText(t.l,iw,{font:'head',max:ls,min:ls}).h);});
  var ms=o.icons?50:0, top=(ms?ms+16:0)+vs*1.08+10+lh+10, dAvail=Z.h-pad*2-top;
  var ds=C.groupFit(st.map(function(t){return t.d;}),iw,dAvail,{font:'body',max:n>=4?15.5:17,min:10.5,lh:1.42});
  var dh=0; st.forEach(function(t){dh=Math.max(dh,t.d?C.measureText(t.d,iw,{font:'body',max:ds,min:ds,lh:1.42}).h:0);});
  var off=Math.round(Math.max(0,dAvail-dh)*0.45);
  st.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), cc=card(C,x,Z.y,cw,Z.h,{hl:o.hl==='first'?k===0:(o.hl==='alt'?k%2===0:false)});
    var y=Z.y+pad+off;
    if (ms) { mark(C,x+pad,y,ms,iconFor(C.s,t.i,t.l),{}); y+=ms+16; }
    var cbase=(C.S.card&&(C.S.card.fill||(C.S.card.grad&&C.S.card.grad.stops[0][1])))||P.card;
    var vcol=cc.hl?cc.tc:(o.vc||(Math.abs(lum(P.accent)-lum(cbase))<0.3?cc.tc:P.accent));
    C.text(x+pad,y,iw,vs*1.08,t.v,{font:'head',size:vs,color:vcol,lh:1.05,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:-0.01});
    C.text(x+pad,y+vs*1.08+10,iw,lh,t.l,{font:'head',size:ls,color:cc.tc,lh:1.2,ed:{f:'x-part',i:t.i,p:1}});
    if(t.d) C.text(x+pad,y+vs*1.08+10+lh+10,iw,dh+4,t.d,{font:'body',size:ds,color:cc.mc,lh:1.42,ed:{f:'x-part',i:t.i,p:2}});
  });
};
B.statRings = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gap=o.gap||26, cw=(Z.w-gap*(n-1))/n, d=Math.min(cw-40, Z.h*0.52, 190);
  var vs=C.groupFit(st.map(function(t){return t.v;}),d*0.72,d*0.4,{font:'head',max:40,min:16,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),cw-10,48,{font:'head',max:18,min:12,maxLines:2});
  var dTop=d+18+ls*2.5+6;
  var ds=C.groupFit(st.map(function(t){return t.d;}),cw-10,Z.h-dTop,{font:'body',max:14.5,min:10.5});
  st.forEach(function(t,k){
    var x=Z.x+k*(cw+gap), rx=x+(cw-d)/2;
    if (o.cardBg) card(C,x,Z.y-10,cw,Z.h+10,{});
    C.ring(rx,Z.y,d,pctOf(t.v,k),{color:P.accent,track:o.track||mix(P.accent,P.bg,0.78),th:o.th||Math.round(d*0.09)});
    C.text(rx,Z.y,d,d,t.v,{font:'head',size:vs,color:P.text,align:'c',va:'m',lh:1,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:0});
    C.text(x+5,Z.y+d+18,cw-10,ls*2.5,t.l,{font:'head',size:ls,color:P.text,align:'c',lh:1.2,ed:{f:'x-part',i:t.i,p:1}});
    if(t.d) C.text(x+5,Z.y+dTop,cw-10,Z.h-dTop,t.d,{font:'body',size:ds,color:P.muted,align:'c',lh:1.4,ed:{f:'x-part',i:t.i,p:2}});
  });
};
B.statBars = function(C,Z,o){
  var st=statItems(C), n=st.length; if(!n) return;
  var P=C.P, gy=18, rh=(Z.h-gy*(n-1))/n, vw=Math.round(Z.w*0.2);
  var vs=C.groupFit(st.map(function(t){return t.v;}),vw,rh*0.8,{font:'head',max:56,min:22,maxLines:1});
  var ls=C.groupFit(st.map(function(t){return t.l;}),Z.w-vw-30,32,{font:'head',max:19,min:12,maxLines:1});
  st.forEach(function(t,k){
    var y=Z.y+k*(rh+gy), bx=Z.x+vw+30, bw=Z.w-vw-30;
    C.text(Z.x,y,vw,rh,t.v,{font:'head',size:vs,color:P.accent,va:'m',lh:1,ed:{f:'x-part',i:t.i,p:0},caps:false,ls:-0.01});
    C.text(bx,y+rh*0.06,bw,ls*1.3,t.l,{font:'head',size:ls,color:P.text,lh:1.2,ed:{f:'x-part',i:t.i,p:1},maxLines:1});
    var by=y+rh*0.06+ls*1.3+10, bh=Math.max(10,Math.min(16,rh*0.14));
    C.rect(bx,by,bw,bh,{fill:mix(P.accent,P.bg,0.8),r:bh/2});
    C.rect(bx,by,bw*pctOf(t.v,k)/100,bh,{grad:{ang:90,stops:[[0,P.accent],[100,P.accent2||P.accent]]},r:bh/2});
    if(t.d) C.text(bx,by+bh+10,bw,y+rh-(by+bh+10),t.d,{font:'body',fit:{max:14.5,min:10.5},color:P.muted,lh:1.35,ed:{f:'x-part',i:t.i,p:2},maxLines:2});
  });
};

/* ---------------- timeline ---------------- */
B.tlH = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, cw=Z.w/n, ly=Z.y+Z.h*(o.lineAt||0.5), d=o.dot||22;
  C.rect(Z.x,ly-1.5,Z.w,3,{fill:o.lineC||P.line});
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-20,40,{font:'head',max:24,min:13,maxLines:1});
  var room=Z.h*0.5-d-26-hs*1.3;
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-20,room,{font:'body',max:n<=4?18:16.5,min:10.5});
  it.forEach(function(t,k){
    var x=Z.x+k*cw, cx0=x+cw/2, up=o.alt!==false?k%2===0:false;
    C.ell(cx0-d/2,ly-d/2,d,d,{fill:P.accent,line:P.bg,lw:4});
    if (up) {
      C.line(cx0,ly-d/2,cx0,ly-26,{color:P.accent,lw:1.5,op:0.6});
      var dh=C.measureText(t.d,cw-20,{font:'body',max:ds,min:ds}).h;
      C.text(x+10,ly-26-dh,cw-20,dh,t.d,{font:'body',size:ds,color:P.muted,align:'c',lh:1.38,ed:{f:'x-part',i:t.i,p:1},va:'b'});
      C.text(x+10,ly-26-dh-hs*1.3-4,cw-20,hs*1.3,t.h,{font:'head',size:hs,color:P.accent,align:'c',lh:1.1,ed:{f:'x-part',i:t.i,p:0},maxLines:1});
    } else {
      C.line(cx0,ly+d/2,cx0,ly+26,{color:P.accent,lw:1.5,op:0.6});
      C.text(x+10,ly+30,cw-20,hs*1.3,t.h,{font:'head',size:hs,color:P.accent,align:'c',lh:1.1,ed:{f:'x-part',i:t.i,p:0},maxLines:1});
      C.text(x+10,ly+30+hs*1.3+4,cw-20,room+10,t.d,{font:'body',size:ds,color:P.muted,align:'c',lh:1.38,ed:{f:'x-part',i:t.i,p:1}});
    }
  });
};
B.tlCards = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, gap=16, cw=(Z.w-gap*(n-1))/n, ly=Z.y+14, d=20, pad=20, cy=ly+32, ch=Z.h-(cy-Z.y), iw=cw-pad*2;
  C.rect(Z.x,ly-1.5,Z.w,3,{fill:P.line})._tlLine=1;
  var hs=C.groupFit(it.map(function(t){return t.h;}),iw,60,{font:'head',max:n>=5?21:24,min:13,maxLines:2});
  var hh=0; it.forEach(function(t){hh=Math.max(hh,C.measureText(t.h,iw,{font:'head',max:hs,min:hs}).h);});
  var dAvail=ch-pad*2-hh-12;
  var ds=C.groupFit(it.map(function(t){return t.d;}),iw,dAvail,{font:'body',max:n>=5?16:18,min:10.5,lh:1.42});
  var dh=0; it.forEach(function(t){dh=Math.max(dh,C.measureText(t.d,iw,{font:'body',max:ds,min:ds,lh:1.42}).h);});
  var need=pad*2+hh+12+dh+36;
  if (ch>need){ var sh=(ch-need)/2; ly+=sh; cy+=sh; ch=need; dAvail=ch-pad*2-hh-12; }
  var off=Math.round(Math.max(0,dAvail-dh)*0.35);
  C.els.forEach(function(e){ if(e._tlLine){ e.y=ly-1.5; } });
  it.forEach(function(t,k){
    var x=Z.x+k*(cw+gap);
    C.ell(x+pad,ly-d/2,d,d,{fill:P.accent,line:P.bg,lw:3});
    var cc=card(C,x,cy,cw,ch,{hl:k===n-1&&o.hlLast});
    C.text(x+pad,cy+pad+off,iw,hh,t.h,{font:'head',size:hs,color:o.hc||(k===n-1&&o.hlLast?cc.tc:P.accent),lh:1.15,ed:{f:'x-part',i:t.i,p:0}});
    C.text(x+pad,cy+pad+off+hh+12,iw,dh+4,t.d,{font:'body',size:ds,color:cc.mc,lh:1.42,ed:{f:'x-part',i:t.i,p:1}});
  });
};
B.tlV = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, cols=n>4?2:1, per=Math.ceil(n/cols), gx=40, cw=(Z.w-gx*(cols-1))/cols, gy=10, rh=(Z.h-gy*(per-1))/per, d=18, lx=o.lx||120;
  var hs=C.groupFit(it.map(function(t){return t.h;}),lx-20,rh,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-lx-40,rh-4,{font:'body',max:15.5,min:10.5});
  for (var c=0;c<cols;c++){ var x0=Z.x+c*(cw+gx)+lx+8; C.rect(x0-1,Z.y+rh/2,2,(Math.min(per,n-c*per)-1)*(rh+gy),{fill:P.line}); }
  it.forEach(function(t,k){
    var c=Math.floor(k/per), r=k%per, x=Z.x+c*(cw+gx), y=Z.y+r*(rh+gy), dx=x+lx+8;
    C.text(x,y,lx-10,rh,t.h,{font:'head',size:hs,color:P.accent,align:'r',va:'m',lh:1.15,ed:{f:'x-part',i:t.i,p:0}});
    C.ell(dx-d/2,y+rh/2-d/2,d,d,{fill:P.accent,line:P.bg,lw:3});
    C.text(dx+22,y,cw-lx-40,rh,t.d,{font:'body',size:ds,color:P.text,va:'m',lh:1.38,ed:{f:'x-part',i:t.i,p:1}});
  });
};
B.tlChev = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, cw=Z.w/n, ch=o.chevH||86, pad=14;
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-60,ch-20,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-pad*2,Z.h-ch-22,{font:'body',max:15,min:10.5});
  it.forEach(function(t,k){
    var x=Z.x+k*cw, col=mix(P.accent,P.accent2||P.accent,n>1?k/(n-1):0);
    C.shape(k===0?'homePlate':'chevron',x,Z.y,cw+(k<n-1?18:0),ch,{fill:col});
    C.text(x+(k===0?20:40),Z.y,cw-60,ch,t.h,{font:'head',size:hs,color:P.onAccent||'#FFFFFF',va:'m',align:'c',lh:1.1,ed:{f:'x-part',i:t.i,p:0}});
    C.text(x+pad,Z.y+ch+22,cw-pad*2,Z.h-ch-22,t.d,{font:'body',size:ds,color:P.muted,lh:1.4,ed:{f:'x-part',i:t.i,p:1},align:'c'});
  });
};
B.tlStairs = function(C,Z,o){
  var it=C.it.slice(0,6), n=it.length; if(!n) return;
  var P=C.P, gap=14, cw=(Z.w-gap*(n-1))/n, minH=Z.h*0.46, step=(Z.h-minH)/Math.max(1,n-1), pad=16;
  var hs=C.groupFit(it.map(function(t){return t.h;}),cw-pad*2,48,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.d;}),cw-pad*2,minH-pad*2-hs*2.4-6,{font:'body',max:14.5,min:10});
  it.forEach(function(t,k){
    var h=minH+step*k, x=Z.x+k*(cw+gap), y=Z.y+Z.h-h;
    C.rect(x,y,cw,h,{grad:{ang:180,stops:[[0,mix(P.accent,P.accent2||P.accent,k/(Math.max(1,n-1)))],[100,mix(P.accent,P.bg,0.55)]]},r:C.S.card&&C.S.card.r!=null?Math.min(C.S.card.r,14):10});
    C.text(x+pad,y+pad,cw-pad*2,hs*2.4,t.h,{font:'head',size:hs,color:P.onAccent||'#FFFFFF',lh:1.15,ed:{f:'x-part',i:t.i,p:0}});
    C.text(x+pad,y+pad+hs*2.4+6,cw-pad*2,h-pad*2-hs*2.4-6,t.d,{font:'body',size:ds,color:mix(P.onAccent||'#FFFFFF',P.accent,0.12),lh:1.38,ed:{f:'x-part',i:t.i,p:1}});
  });
};

/* ---------------- agenda ---------------- */
B.agTiles = function(C,Z,o){
  var it=C.it.slice(0,8), n=it.length; if(!n) return;
  var P=C.P, cols=n<=4?n:Math.ceil(n/2), rows=Math.ceil(n/cols), gap=18, cw=(Z.w-gap*(cols-1))/cols, ch=(Z.h-gap*(rows-1))/rows, pad=20;
  var hs=C.groupFit(it.map(function(t){return t.h||t.d;}),cw-pad*2,48,{font:'head',max:20,min:12,maxLines:2});
  var ds=C.groupFit(it.map(function(t){return t.h?t.d:'';}),cw-pad*2,ch-pad*2-44-hs*2.4,{font:'body',max:14,min:10});
  it.forEach(function(t,k){
    var r=Math.floor(k/cols), c=k%cols, x=Z.x+c*(cw+gap), y=Z.y+r*(ch+gap);
    var cc=card(C,x,y,cw,ch,{});
    C.text(x+pad,y+pad-4,cw-pad*2,44,pad2(k+1),{font:'head',size:36,color:P.accent,lh:1,caps:false,ls:0});
    C.text(x+pad,y+pad+44,cw-pad*2,hs*2.4,t.h||t.d,{font:'head',size:hs,color:cc.tc,lh:1.15,ed:{f:'x-part',i:t.i,p:t.h?0:1}});
    if (t.h && t.d && ds>=10) C.text(x+pad,y+pad+44+hs*2.4,cw-pad*2,ch-pad*2-44-hs*2.4,t.d,{font:'body',size:ds,color:cc.mc,lh:1.35,ed:{f:'x-part',i:t.i,p:1}});
  });
};
B.agList = function(C,Z,o){
  var it=C.it.slice(0,10), n=it.length; if(!n) return;
  var P=C.P, cols=o.cols||(n>5?2:1), per=Math.ceil(n/cols), gx=48, cw=(Z.w-gx*(cols-1))/cols, rh=Math.min(92,Z.h/per);
  var hs=C.groupFit(it.map(function(t){return t.h||t.d;}),cw-110,rh*0.5,{font:'head',max:23,min:13,maxLines:1});
  var ds=C.groupFit(it.map(function(t){return t.h?t.d:'';}),cw-110,rh*0.42,{font:'body',max:14.5,min:10,maxLines:1});
  it.forEach(function(t,k){
    var c=Math.floor(k/per), r=k%per, x=Z.x+c*(cw+gx), y=Z.y+r*rh;
    num(C,x,y+(rh-50)/2,50,k+1,o.numO);
    var two=t.h&&t.d;
    C.text(x+72,y+(two?rh*0.12:0),cw-80,two?rh*0.46:rh,t.h||t.d,{font:'head',size:hs,color:P.text,va:two?'b':'m',lh:1.15,ed:{f:'x-part',i:t.i,p:t.h?0:1},maxLines:1});
    if (two) C.text(x+72,y+rh*0.6,cw-80,rh*0.38,t.d,{font:'body',size:ds,color:P.muted,lh:1.3,ed:{f:'x-part',i:t.i,p:1},maxLines:1});
    if (o.rules!==false && r<per-1) C.rect(x+72,y+rh-1,cw-72,1,{fill:P.line,op:0.8});
  });
};

/* ---------------- quote ---------------- */
B.quote = function(C,Z,o){
  var P=C.P, q=C.s.title||'', by=(C.it[0]?(C.it[0].h?C.it[0].h+': '+C.it[0].d:C.it[0].d):(C.s.subtitle||''));
  var al=o.align||'c', qc=o.qc||P.accent;
  C.text(al==='c'?Z.x+Z.w/2-60:Z.x,Z.y-10,120,110,'“',{font:'head',size:150,color:qc,lh:1,align:al==='c'?'c':'l',op:0.9,caps:false,ls:0,wt:700});
  var qe=C.text(Z.x,Z.y+96,Z.w,Z.h-190,q,{font:o.qFont||'head',fit:{max:o.qMax||40,min:20},color:o.tc||P.text,align:al,lh:1.3,italic:!!o.italic,ed:{f:'title'},maxLines:5,va:'t'});
  var yy=Z.y+96+qe._h+30;
  C.rect(al==='c'?Z.x+Z.w/2-30:Z.x,yy,60,3,{fill:qc});
  if (by) C.text(Z.x,yy+18,Z.w,34,by,{font:'body',fit:{max:18,min:12},wt:700,color:o.bc||P.muted,align:al,caps:true,ls:0.1,ed:C.it[0]?{f:'content-plain',i:C.it[0].i}:null,maxLines:1});
};

/* chart slide (DS Charts snippet draws the chart: t:'chart' element) */
B.chart = function(C,Z,o){
  var DC = window.DS_CHART, P = C.P; if (!DC) return;
  var d = DC.norm(C.s.chart), n = d.rows.length, pie = d.kind === 'pie', it = C.it.slice(0,4), has = it.length > 0;
  var dark = lum(P.bg||'#FFFFFF') < 0.5, cols = DC.palette(P.accent||'#2563EB', dark, Math.max(n,3));
  var tc = P.title||P.text, mc = P.muted||mix(P.text,P.bg,0.35);
  var spec = {style:d.style, angle:d.angle, unit:d.unit, max:(C.s.chart&&C.s.chart.max)||'', labels:d.rows.map(function(r){return r.l;}), values:d.rows.map(function(r){return r.v;})};
  var noLeg = d.style==='rings'||d.style==='progress'||d.style==='gauge';
  function chart(x,y,w,h,leg){ C.add({t:'chart',x:x,y:y,w:w,h:h,fam:C.font('body'),spec:spec,colors:cols,tc:tc,mc:mc,bgc:hx(P.bg||'#FFFFFF'),legend:noLeg?'none':leg}); }
  function pts(x,y,w,h){
    var m=it.length, gap=14, ch=(h-gap*(m-1))/m;
    var hs=C.groupFit(it.map(function(t){return t.h;}),w-44,40,{font:'head',max:m>3?19:22,min:13,maxLines:2});
    it.forEach(function(t,k){
      var yy=y+k*(ch+gap), cc=card(C,x,yy,w,ch,{});
      C.rect(x,yy+ch*0.18,6,ch*0.64,{fill:cols[k%cols.length],r:3});
      var th=t.h?C.measureText(t.h,w-44,{font:'head',max:hs,min:hs}).h:0;
      var ds=C.measureText(t.d,w-44,{font:'body',max:m>3?15:17,min:11,h:ch-th-22}).s;
      var dh=t.d?C.measureText(t.d,w-44,{font:'body',max:ds,min:ds}).h:0, ty=yy+Math.max(8,(ch-th-dh-6)/2);
      if (t.h) C.text(x+26,ty,w-44,th,t.h,{font:'head',size:hs,color:cc.tc,ed:{f:'x-part',i:t.i,p:0},lh:1.15});
      if (t.d) C.text(x+26,ty+th+6,w-44,dh+4,t.d,{font:'body',size:ds,color:cc.mc,ed:{f:'x-part',i:t.i,p:1},lh:1.38});
    });
  }
  function legend(x,y,w,h,kpi){
    var rows=d.rows.map(function(r,i){return {r:r,i:i};}), one=d.kind==='bar'||d.kind==='line';
    if (kpi) rows=rows.slice().sort(function(a,b){return b.r.v-a.r.v;}).slice(0,3); else rows=rows.slice(0,7);
    var m=Math.max(1,rows.length), rh=kpi?Math.min(150,h/m):Math.min(64,h/m), y0=y+(h-rh*m)/2;
    rows.forEach(function(o,k){
      var yy=y0+k*rh, val=DC.fmt(o.r.v,d.unit), col=cols[one?0:o.i%cols.length];
      if (k) C.rect(x,yy,w,1,{fill:P.line||mix(P.text,P.bg,0.8)});
      if (kpi) {
        C.text(x,yy+10,w,rh*0.55,val,{font:'head',size:m>2?46:54,wt:800,color:col,lh:1,va:'b',caps:false,ls:0,maxLines:1});
        C.text(x,yy+10+rh*0.55+4,w,rh*0.45-20,o.r.l,{font:'body',fit:{max:17,min:12},wt:600,color:mc,lh:1.3,maxLines:2});
      } else {
        C.rect(x,yy+rh/2-8,16,16,{fill:col,r:5});
        C.text(x+30,yy,w-150,rh,o.r.l,{font:'body',fit:{max:18,min:12},wt:600,color:P.text,va:'m',maxLines:1});
        C.text(x+w-120,yy,120,rh,val,{font:'head',size:m>5?22:26,wt:800,color:tc,va:'m',align:'r',caps:false,ls:0,maxLines:1});
      }
    });
  }
  var gap=36;
  /* Tall (9:16) layouts: the chart gets only the height it needs and the insights stack under it, the whole
     group centred - the chart used to stretch over the full slide height and leave large empty areas. */
  if (Z.h > Z.w*1.15 || (window.DSTE && DSTE.H > DSTE.W)) {
    var nmax=Math.max(1,Math.min(4,n)), lh=pie?20+Math.ceil(n/3)*36:0, nat;
    if (d.kind==='gauge') nat = d.style==='progress' ? 60+n*64 : d.style==='rings' ? Z.w*0.8 : Z.w/nmax*0.82;
    else if (pie) nat = (d.style==='half' ? Z.w*0.6 : Z.w*0.9) + lh;
    else nat = Z.w*0.78;
    nat = Math.max(Z.h*0.24, Math.min(nat, Z.h*(has?0.58:0.8)));
    var tg=28, m=has?Math.max(1,Math.min(it.length,Math.floor((Z.h-nat-tg+14)/110))):0;
    it=it.slice(0,m);
    var ph=has?Math.min(Z.h-nat-tg, m*170):0, tot=nat+(has?tg+ph:0), y0=Z.y+Math.max(0,(Z.h-tot)/2);
    chart(Z.x,y0,Z.w,nat,pie?'bottom':'none');
    if (has) pts(Z.x,y0+nat+tg,Z.w,ph);
    return;
  }
  if (o.full) { chart(Z.x,Z.y,Z.w,Z.h,'right'); return; }
  if (o.center) {
    var bh=has?110:0, cw=Math.min(Z.w,960);
    chart(Z.x+(Z.w-cw)/2,Z.y,cw,Z.h-bh-(has?16:0),'right');
    if (has) { var i3=it.slice(0,3), w3=(Z.w-28*(i3.length-1))/i3.length;
      i3.forEach(function(t,k){ var x=Z.x+k*(w3+28), y=Z.y+Z.h-bh; C.rect(x,y,w3,4,{fill:cols[k%cols.length]});
        if (t.h) C.text(x,y+14,w3,26,t.h,{font:'head',fit:{max:19,min:13},color:tc,ed:{f:'x-part',i:t.i,p:0},maxLines:1});
        if (t.d) C.text(x,y+44,w3,bh-44,t.d,{font:'body',fit:{max:15.5,min:11},color:mc,ed:{f:'x-part',i:t.i,p:1},lh:1.35,maxLines:3}); }); }
    return;
  }
  if (o.kpi) { var kw=Math.round(Z.w*0.3); legend(Z.x,Z.y,kw,Z.h,true); chart(Z.x+kw+gap,Z.y,Z.w-kw-gap,Z.h,'bottom'); return; }
  var pw=Math.round(Z.w*0.4), cw2=Z.w-pw-gap, right=o.side==='r';
  var cx=right?Z.x+pw+gap:Z.x, px2=right?Z.x:Z.x+cw2+gap;
  chart(cx,Z.y,cw2,Z.h,pie?(has?'bottom':'none'):'none');
  if (has) pts(px2,Z.y,pw,Z.h); else legend(px2,Z.y,pw,Z.h,!(pie||d.kind==='gauge'));
};

/* ============================================================
   COMPOSED STANDARD SLIDE
   opt: {body, img:'right'|'left'|'bottom'|'top', imgPct, framed, hk (header kind), ...body opts}
   ============================================================ */
DSTE.std = function(C,o){
  o=o||{}; var S=C.S, P=C.P, M=S.margin||64;
  var A={x:M,w:W-2*M,top:null};
  var framed = o.framed!=null?o.framed:!!S.imgFramed, ir=S.imgR!=null?S.imgR:(framed?20:0);
  var bottom = H - (S.footer===false?40:(S.footerH||62));
  var imgPct = o.imgPct || S.imgPct || 0.4;
  if (o.img==='right'||o.img==='left'){
    var iw=Math.round(W*imgPct), ix=o.img==='right'?W-iw:0;
    if (framed) imgBox(C,ix+(o.img==='right'?0:28),28,iw-28,H-56,{r:ir});
    else imgBox(C,ix,0,iw,H,{r:0});
    A.x = o.img==='right'?M:iw+48; A.w = W-iw-M-48;
  }
  if (o.img==='top'){ var th=Math.round(H*0.3); if(framed) imgBox(C,28,28,W-56,th-28,{r:ir}); else imgBox(C,0,0,W,th,{r:0}); A.top=th+30; }
  var hb = header(C,A,o);
  var concH = C.s.conclusion && o.conc!==false ? (S.concH||54) : 0;
  if (o.img==='bottom'){ var bh=Math.round(H*0.26); if(framed) imgBox(C,28,H-bh-28+0,W-56,bh,{r:ir}); else imgBox(C,0,H-bh,W,bh,{r:0}); bottom=H-bh-24; }
  var zTop = hb + (S.bodyGap||30);
  var zBot = bottom - (concH?concH+18:0);
  var Z = {x:A.x, y:zTop, w:A.w, h:Math.max(80,zBot-zTop)};
  var fn = typeof o.body==='function' ? o.body : B[o.body||'cols'];
  if (fn) fn(C,Z,o);
  if (concH) conclusion(C,A.x,zBot+18,A.w,concH,o.concO);
  return Z;
};

/* ============================================================
   VARIANT SETS — sensible defaults a template may override
   ids: v1 + n2..n6 are text-only; v2..v10 have an image zone
   ============================================================ */
function V(id,lbl,o){ var v={id:id,lbl:lbl,o:o||{}}; if(o&&typeof o.fn==='function') v.fn=o.fn; return v; }
DSTE.V = V;
DSTE.defaultVariants = {
  text:[V('v1','Statement',{body:'text'}),V('n2','Statement in card',{body:'text',box:true}),V('v2','Statement + image right',{body:'text',img:'right'}),V('v3','Image left + statement',{body:'text',img:'left'})],
  p2:[V('v1','Two panels',{body:'duo'}),V('n2','Two panels + VS',{body:'duo',vs:true}),V('n3','Numbered columns',{body:'numbered'}),V('v2','Panels + image right',{body:'list',img:'right',cards:true}),V('v3','Image left + list',{body:'list',img:'left'}),V('v4','Columns + image bottom',{body:'cols',img:'bottom'})],
  p3:[V('v1','Three cards',{body:'cols'}),V('n2','Numbered columns',{body:'numbered'}),V('n3','Icon list',{body:'list',rules:true}),V('n4','Gradient bars',{body:'bars'}),V('v2','List + image right',{body:'list',img:'right'}),V('v3','Image left + cards',{body:'list',img:'left',cards:true}),V('v4','Cards + image bottom',{body:'cols',img:'bottom'})],
  p4:[V('v1','2 × 2 grid',{body:'grid'}),V('n2','Four columns',{body:'cols'}),V('n3','Hub diagram',{body:'hub'}),V('n4','Numbered row',{body:'numbered'}),V('v2','Grid + image right',{body:'list',img:'right',cards:true}),V('v3','Image left + list',{body:'list',img:'left'})],
  p56:[V('v1','Card grid',{body:'grid'}),V('n2','Two-column list',{body:'list',cols:2,rules:true}),V('n3','Numbered grid',{body:'numbered'}),V('n4','Hub diagram',{body:'hub'}),V('v2','List + image right',{body:'list',img:'right',cols:1}),V('v3','Image left + list',{body:'list',img:'left',cols:1})],
  p78:[V('v1','Card grid',{body:'grid'}),V('n2','Two-column list',{body:'list',cols:2,rules:true}),V('n3','Numbered grid',{body:'numbered'}),V('v2','List + image right',{body:'list',img:'right',cols:2})],
  p9:[V('v1','Card grid',{body:'many'}),V('n2','Numbered grid',{body:'many',mark:'num'}),V('n3','Two-column list',{body:'list',cols:2,rules:true,max:16}),V('n4','Three-column list',{body:'list',cols:3,rules:true,max:16}),V('v2','List + image right',{body:'list',img:'right',cols:2,max:16})],
  stats:[V('v1','Big numbers',{body:'statCols'}),V('n2','Stat cards',{body:'statCards',icons:true}),V('n3','Progress rings',{body:'statRings'}),V('n4','Progress bars',{body:'statBars'}),V('v2','Numbers + image right',{body:'statBars',img:'right'}),V('v3','Image left + cards',{body:'statBars',img:'left'})],
  timeline:[V('v1','Horizontal timeline',{body:'tlH'}),V('n2','Milestone cards',{body:'tlCards'}),V('n3','Arrow steps',{body:'tlChev'}),V('n4','Growth stairs',{body:'tlStairs'}),V('n5','Vertical timeline',{body:'tlV'}),V('v2','Vertical + image right',{body:'tlV',img:'right'})],
  agenda:[V('v1','Numbered tiles',{body:'agTiles'}),V('n2','Numbered list',{body:'agList'}),V('v2','List + image right',{body:'agList',img:'right',cols:1})],
  cover:[V('v1','Cover',{})],
  closing:[V('v1','Closing',{})],
  chart:[V('v1','Chart + insights',{body:'chart'}),V('n2','Insights + chart right',{body:'chart',side:'r'}),V('n3','Centered chart',{body:'chart',center:true,hk:'center'}),V('n4','Big numbers + chart',{body:'chart',kpi:true}),V('n5','Full-width chart',{body:'chart',full:true})],
  quote:[V('v1','Centered quote',{body:'quote'}),V('v2','Quote + image right',{body:'quote',img:'right',align:'l'}),V('v3','Image left + quote',{body:'quote',img:'left',align:'l'})]
};

DSTE.variantsFor = function(T, kind){
  var tv = T.variants || {};
  var list = tv[kind] || DSTE.defaultVariants[kind] || DSTE.defaultVariants.p3;
  if (kind === 'agenda' && list.length < 3) {           /* every template offers at least 3 agenda layouts */
    if (!T.__agx) {
      var have = {}; list.forEach(function(v){ have[v.id] = 1; });
      var extra = [V('n5','Numbered tiles',{body:'agTiles'}), V('n6','Numbered list',{body:'agList'}), V('v9','List + image right',{body:'agList', img:'right', cols:1})];
      T.__agx = list.concat(extra.filter(function(v){ return !have[v.id]; }));
    }
    return T.__agx;
  }
  return list;
};

/* ============================================================
   THEMES on template decks
   A theme picked from the Theme section re-maps every colour of a template
   by its role, the way a Classic deck takes a theme:
     - slide backgrounds  -> the theme background (solid or gradient)
     - greys / blacks / whites -> placed between the theme background and
       text colour by how light they are (cards, lines, muted text)
     - titles / body text -> the theme title / body colours
     - accent colours     -> the theme accent (lighter / darker shades kept)
   Inverted parts (a dark band on a light template) stay inverted.
   Headings switch to the theme font.
   ============================================================ */
/* key: [accent, font, background, title colour, body colour] */
DSTE.THEMES = {
 'navy-pro':['#4A9EFF','Inter','#1B2A4A','#FFFFFF','#C8D8F0'],
 'dark-modern':['#58A6FF','Inter','#0D1117','#FFFFFF','#C9D1D9'],
 'clean-corporate':['#2B6CB0','Roboto','#FFFFFF','#1A365D','#2D3748'],
 'bold-red':['#FF4444','Oswald','#1A0000','#FFFFFF','#FFD0D0'],
 'corporate-dark':['#6366F1','IBM Plex Sans','#111827','#F9FAFB','#D1D5DB'],
 'clean-white':['#0EA5E9','Work Sans','#F8FAFC','#0F172A','#334155'],
 'gradient-pro':['#A78BFA','Space Grotesk','#0F0C29','#FFFFFF','#E0D7FF'],
 'bold-impact':['#FFD700','Bebas Neue','#111111','#FFFFFF','#CCCCCC'],
 'sunset-glow':['#FF6B9D','Poppins','#2D1B2E','#FFFFFF','#F4C9D9'],
 'forest-green':['#4ADE80','Nunito','#0F2419','#FFFFFF','#C8E6D0'],
 'ocean-breeze':['#0284C7','DM Sans','#F0F9FF','#0C4A6E','#334155'],
 'golden-hour':['#F59E0B','Playfair Display','#1C1408','#FFFFFF','#F5DEB3'],
 'lavender-mist':['#8B5CF6','Quicksand','#F5F3FF','#3B0764','#4C1D95'],
 'slate-minimal':['#64748B','Inter','#F8F9FA','#1E293B','#475569'],
 'cherry-bold':['#DC2626','Montserrat','#FFF5F5','#7F1D1D','#450A0A'],
 'midnight-teal':['#2DD4BF','Josefin Sans','#042F2E','#FFFFFF','#99F6E4'],
 'coral-sunset':['#FB923C','Raleway','#FFF7ED','#7C2D12','#9A3412'],
 'royal-purple':['#A855F7','Playfair Display','#1E0A3C','#FFFFFF','#DDD6FE'],
 'mint-fresh':['#22C55E','Nunito','#F0FDF4','#14532D','#166534'],
 'rose-gold':['#FB7185','Merriweather','#FFF1F2','#881337','#9F1239'],
 'steel-blue':['#38BDF8','Source Sans Pro','#0F172A','#F1F5F9','#94A3B8'],
 'amber-warm':['#FBBF24','Crimson Text','#1C1917','#FEF3C7','#D6D3D1'],
 'arctic-white':['#06B6D4','Work Sans','#FFFFFF','#0F172A','#334155'],
 'crimson-night':['#EF4444','Libre Baskerville','#1A0A0A','#FFFFFF','#FCA5A5'],
 'aurora':['#4FD1C5','Poppins','linear-gradient(135deg,#0F2027,#1A3A4A)','#FFFFFF','#B8D4DE'],
 'sunset-blaze':['#FFD700','Montserrat','linear-gradient(135deg,#7B0000,#DD2476)','#FFFFFF','#FFE0E9'],
 'cosmic-purple':['#00E5FF','Space Grotesk','linear-gradient(135deg,#1A0050,#4A00E0)','#FFFFFF','#E0D4FF'],
 'ocean-depth':['#FFD93D','DM Sans','linear-gradient(135deg,#000028,#003366)','#FFFFFF','#CDEFFF'],
 'emerald-dusk':['#FFC857','Nunito','linear-gradient(135deg,#0A2E1A,#134E5E)','#FFFFFF','#DFF5E8'],
 'rose-gold-glow':['#FFD700','Playfair Display','linear-gradient(135deg,#0D0720,#1D2671)','#FFFFFF','#FFD9E6'],
 'amber-blaze':['#FFA500','Poppins','linear-gradient(135deg,#1A0A00,#7A3800)','#FFFFFF','#FFD9A0'],
 'midnight-bloom':['#F72585','Josefin Sans','linear-gradient(135deg,#0B0020,#0B3D47)','#FFFFFF','#D9F7FA'],
 'deep-navy':['#4A9EFF','Inter','linear-gradient(135deg,#0A1628,#0D3B6E)','#FFFFFF','#C8D8F0'],
 'dark-crimson':['#FF6666','Montserrat','linear-gradient(135deg,#1A0000,#5C0000)','#FFFFFF','#FFD0D0'],
 'dark-slate':['#38BDF8','Inter','linear-gradient(135deg,#0F172A,#1E293B)','#F1F5F9','#94A3B8'],
 'dark-forest':['#4ADE80','Nunito','linear-gradient(135deg,#052010,#0A3020)','#FFFFFF','#C8E6D0']
};
function hsl(c){ var x=rgb(c), r=x[0]/255, g=x[1]/255, b=x[2]/255, mx=Math.max(r,g,b), mn=Math.min(r,g,b), l=(mx+mn)/2, d=mx-mn, h=0, s=0;
  if (d>0){ s=d/(1-Math.abs(2*l-1)); h = mx===r ? ((g-b)/d)%6 : (mx===g ? (b-r)/d+2 : (r-g)/d+4); h*=60; if(h<0)h+=360; }
  return {h:h, s:s, l:l, c:d}; }
function fromHsl(h,s,l){ var c=(1-Math.abs(2*l-1))*s, x=c*(1-Math.abs((h/60)%2-1)), m=l-c/2, r=0,g=0,b=0; h=((h%360)+360)%360;
  if(h<60){r=c;g=x;}else if(h<120){r=x;g=c;}else if(h<180){g=c;b=x;}else if(h<240){g=x;b=c;}else if(h<300){r=x;b=c;}else{r=c;b=x;}
  return toHex([(r+m)*255,(g+m)*255,(b+m)*255]); }
function clamp01(v, a, b){ return Math.max(a, Math.min(b, v)); }
function tplBaseHue(T){
  var P=T.pal||{}, best=null, a0=hsl(P.accent||'#888888');
  if (a0.c > 0.12) return a0;
  ['accent2','bg','title','card','line','text'].forEach(function(k){ if(!P[k]) return; var q=hsl(P[k]); if(q.c>0.12 && (!best || q.c>best.c)) best=q; });
  return best || {h:210, s:0.5, l:0.5, c:0.3};
}
function parseThemeBg(bg){
  var m = String(bg).match(/linear-gradient\(\s*(\d+)deg\s*,([^)]*)\)/i);
  if (!m) return {solid:hx(bg), grad:null};
  var cols = m[2].match(/#[0-9a-fA-F]{6}/g) || [];
  if (cols.length < 2) return {solid:hx(cols[0] || '#FFFFFF'), grad:null};
  var stops = cols.map(function(c, i){ return [Math.round(i * 100 / (cols.length - 1)), hx(c)]; });
  return {solid:mix(cols[0], cols[cols.length - 1], 0.5), grad:{ang:parseInt(m[1], 10), stops:stops}};
}
DSTE.themed = function(T, key){
  var th = key && DSTE.THEMES[key]; if (!T || !th) return T;
  T.__th = T.__th || {};
  if (T.__th[key]) return T.__th[key];
  var D = Object.create(T), P = T.pal || {}, bgT = parseThemeBg(th[2]);
  D.fonts = {}; for (var k in T.fonts) D.fonts[k] = T.fonts[k];
  D.fonts.head = th[1];
  D.fonts.gf = (T.fonts.gf ? T.fonts.gf + '|' : '') + th[1] + (th[1] === 'Bebas Neue' ? '' : ':wght@400;700');
  var Lb = lum(P.bg || '#FFFFFF'), Lt = lum(P.text || P.title || '#000000');
  if (Math.abs(Lt - Lb) < 0.2) Lt = Lb > 0.5 ? 0 : 1;
  D.__rec = {
    Lb:Lb, Lt:Lt, base:tplBaseHue(T), bgq:hsl(P.bg || '#FFFFFF'),
    tpl:{title:hx(P.title || P.text || '#000000'), text:hx(P.text || '#000000'), muted:P.muted ? hx(P.muted) : null},
    bg:bgT.solid, grad:bgT.grad, title:hx(th[3]), text:hx(th[4]), muted:mix(th[4], bgT.solid, 0.3), ac:hsl(th[0]), acx:hx(th[0])
  };
  D.__themeKey = key;
  T.__th[key] = D; return D;
};
function toneT(c, R){ return (lum(c) - R.Lb) / (R.Lt - R.Lb); }
function mapColor(c, R, role){
  c = hx(c);
  if (role === 'fg') {
    if (c === R.tpl.title) return R.title;
    if (c === R.tpl.text) return R.text;
    if (R.tpl.muted && c === R.tpl.muted) return R.muted;
  }
  var q = hsl(c), G = R.bgq;
  var surface = G.c >= 0.12 && Math.abs(((q.h - G.h) % 360 + 540) % 360 - 180) <= 22 && Math.abs(q.l - G.l) <= 0.25;   /* tinted panels of a coloured template */
  if (!surface && q.c >= 0.16 && q.l > 0.2 && q.l < 0.86) {           /* accent family -> theme accent */
    var B = R.base, A = R.ac;
    var dh = ((q.h - B.h) % 360 + 540) % 360 - 180; if (Math.abs(dh) > 45) dh = 0;
    return fromHsl(A.h + dh * 0.5, clamp01((A.s || 0.05) * (q.s / (B.s || 0.5)), 0.05, 1), clamp01(A.l + (q.l - (B.l != null ? B.l : 0.5)), 0.12, 0.92));
  }
  var t = toneT(c, R);                                                   /* neutrals / tints -> between theme bg and text */
  if (role === 'fg') return t >= 0.85 ? mix(R.bg, R.title, clamp01(t, 0, 1)) : mix(R.bg, R.text, clamp01(t, 0, 1));
  if (t < 0) return mix(R.bg, R.title, Math.min(0.12, -t * 0.8));       /* cards lifted off the background */
  return mix(R.bg, R.title, clamp01(t, 0, 1));
}
function mapPageBg(bg, R){
  if (!bg) return bg;
  var cols = [];
  if (bg.grad && bg.grad.stops) bg.grad.stops.forEach(function(s){ if (typeof s[1] === 'string') cols.push(s[1]); });
  else if (bg.fill) cols.push(bg.fill);
  if (!cols.length) return bg;
  var t = 0; cols.forEach(function(c){ t += toneT(hx(c), R); }); t /= cols.length;
  var q = hsl(cols[0]);
  if (t < 0.5 || (q.c >= 0.16 && Math.abs(lum(cols[0]) - R.Lb) < 0.2)) return R.grad ? {grad:R.grad} : {fill:R.bg};   /* the template's normal background */
  return recolorAll(bg, R, 'fill');                                     /* an inverted (dark on light) slide stays inverted */
}
/* returns a recoloured copy (template constants such as shared gradients are never mutated) */
function recolorAll(o, R, role){
if (o && typeof o === 'object' && !Array.isArray(o) && o.keep === 1) return o; /* illustration parts that keep their own colours on every theme */
  if (typeof o === 'string') return /^#[0-9a-fA-F]{6}$/.test(o) ? mapColor(o, R, role || 'fill') : o;
  if (!o || typeof o !== 'object') return o;
  var out = Array.isArray(o) ? [] : {};
  for (var k in o) {
    if (!Object.prototype.hasOwnProperty.call(o, k)) continue;
    if (k === 'ed' || k === 'text' || k === 'd' || k === 'name' || k === 'src' || k === 'fam' || k === 'spec') { out[k] = o[k]; continue; }
    var r = role || 'fill';
    if (!role && k === 'color' && (o.t === 'text' || o.t === 'icon')) r = 'fg';
    if (!role && (k === 'tc' || k === 'mc') && o.t === 'chart') r = 'fg';
    if (!role && k === 'colors' && o.t === 'chart' && window.DS_CHART && Array.isArray(o[k])) { out[k] = window.DS_CHART.palette(R.acx, lum(R.bg) < 0.5, o[k].length); continue; }
    out[k] = recolorAll(o[k], R, r);
  }
  return out;
}
/* colour a point of the slide shows (page background + filled shapes stacked under it) */
function avgFill(e){
  if (e.grad && e.grad.stops && e.grad.stops.length) { var c = hx(e.grad.stops[0][1]), n = e.grad.stops.length; return mix(c, hx(e.grad.stops[n-1][1]), 0.5); }
  return e.fill ? hx(e.fill) : null;
}
function gradAt(e, px, py){
  var g = e.grad; if (!g || !g.stops || !g.stops.length) return e.fill ? hx(e.fill) : null;
  var st = g.stops, t;
  if (g.type === 'radial') { var dx = (px - (e.x + e.w/2)) / (e.w/2), dy = (py - (e.y + e.h/2)) / (e.h/2); t = Math.min(1, Math.sqrt(dx*dx + dy*dy)); }
  else {
    var a = (g.ang != null ? g.ang : 135) * Math.PI / 180, vx = Math.sin(a), vy = -Math.cos(a);
    var half = Math.abs(vx) * e.w / 2 + Math.abs(vy) * e.h / 2 || 1;
    t = clamp01(0.5 + ((px - (e.x + e.w/2)) * vx + (py - (e.y + e.h/2)) * vy) / (2 * half), 0, 1);
  }
  var pct = t * 100, lo = st[0], hi = st[st.length - 1];
  for (var i = 0; i < st.length - 1; i++) if (pct >= st[i][0] && pct <= st[i+1][0]) { lo = st[i]; hi = st[i+1]; break; }
  var span = (hi[0] - lo[0]) || 1;
  return mix(hx(lo[1]), hx(hi[1]), clamp01((pct - lo[0]) / span, 0, 1));
}
function colorUnder(C, idx, px, py){
  var bg = C.bgFill || {}, col = bg.grad ? avgFill(bg) : hx(bg.fill || '#FFFFFF');
  for (var i = 0; i < idx; i++) {
    var e = C.els[i];
    if (e.t !== 'rect' && e.t !== 'ell' && e.t !== 'shape' && e.t !== 'img') continue;
    if (px < e.x || px > e.x + e.w || py < e.y || py > e.y + e.h) continue;
    if (e.t === 'img') { col = '#6B7280'; continue; }
    var f = gradAt(e, px, py); if (!f) continue;
    var op = e.op != null ? e.op : 1;
    if (e.grad && e.grad.stops) { var so = e.grad.stops[0][2]; if (so != null) op *= (so + (e.grad.stops[e.grad.stops.length-1][2] != null ? e.grad.stops[e.grad.stops.length-1][2] : 1)) / 2; }
    if (e.grad && e.grad.type === 'radial') op *= 0.5;
    col = mix(col, f, clamp01(op, 0, 1));
  }
  return col;
}
/* after re-colouring, make sure every text / icon still reads on what is behind it */
function fixContrast(C, R){
  C.els.forEach(function(e, i){
    if ((e.t !== 'text' && e.t !== 'icon') || !e.color) return;
    var under = colorUnder(C, i, e.x + e.w / 2, e.y + Math.min(e.h / 2, 30)), lu = lum(under), lc = lum(e.color);
    var need = e.t === 'icon' ? 0.3 : 0.38;
    if (Math.abs(lc - lu) >= need) return;
    var cands = [R.title, R.text, R.bg, '#FFFFFF', '#111827'], best = e.color, bd = Math.abs(lc - lu);
    cands.forEach(function(c, k){ var d = Math.abs(lum(c) - lu) + (k < 3 ? 0.08 : 0); if (d > bd) { bd = d; best = c; } });
    e.color = best;
  });
}
function recolorPage(C, R){
  C.bgFill = mapPageBg(C.bgFill, R);
  C.els = C.els.map(function(e){
    if ((e.t === 'rect' || e.t === 'shape') && e.x <= 4 && e.y <= 4 && e.w >= W - 8 && e.h >= H - 8 && !e.line) {   /* full-slide backdrop */
      var b = mapPageBg(e.grad ? {grad:e.grad} : {fill:e.fill}, R), c = recolorAll(e, R);
      if (b.grad) { c.grad = b.grad; delete c.fill; } else if (b.fill) { c.fill = b.fill; delete c.grad; }
      return c;
    }
    return recolorAll(e, R);
  });
  fixContrast(C, R);
}

/* ============================================================
   RENDER a slide -> {bg, els}
   ============================================================ */
/* company logo on template decks: Snippet 17 (window.dsceLogoEls) gives the boxes in 1280x720 units */
DSTE.addLogo = function(C, kind){
var d = C.deck, L = d && d.logo; if (!L || !L.src || typeof window.dsceLogoEls !== 'function') return null;
var cov = kind==='cover'; if (cov && L.cover===false) return null;
var bg = (C.bgFill && (C.bgFill.fill || (C.bgFill.grad && C.bgFill.grad.stops && C.bgFill.grad.stops[0] && C.bgFill.grad.stops[0][1]))) || C.P.bg || '#FFFFFF';
var ac = hx(C.P.accent || '#3B82F6');
var r = window.dsceLogoEls(L, cov, hx(bg), ac); if (!r || !r.logo) return null;
var badge = r.badge ? C.rect(r.badge.x, r.badge.y, r.badge.w, r.badge.h, {fill:r.badge.fill, op:r.badge.op, r:r.badge.r, line:ac, lw:1, lop:0.4}) : null;
C.add({t:'logo', x:r.logo.x, y:r.logo.y, w:r.logo.w, h:r.logo.h, _src:L.src});
return {fix:function(){ if (badge) { badge.fill = r.badge.fill; badge.op = r.badge.op; } }};
};
DSTE.renderPage = function(T, slide, vid, idx, deck){
  var C = new Ctx(T, slide, idx, vid, deck);
  var kind = C.kind, list = DSTE.variantsFor(T, kind);
  if (kind==='section') kind='text';
  var v = null; for (var i=0;i<list.length;i++){ if(list[i].id===vid){ v=list[i]; break; } }
  if (!v) v = list[0];
  C.v = v; C.o = v.o || {};
  try {
    if (T.bg) T.bg(C, kind);
    if (kind==='cover') { (T.cover||DSTE.genericCover)(C, v); }
    else if (kind==='closing') { (T.closing||DSTE.genericClosing)(C, v); }
    else if (v.fn) { v.fn(C, C.o); }
    else if (kind==='quote' && T.quote) { T.quote(C, v); }
    else if (kind==='agenda' && T.agenda && !C.o.body) { T.agenda(C, v); }
    else {
      var o = {}; for (var k in C.o) o[k]=C.o[k];
      if (kind==='quote') { o.hk='none'; DSTE.quoteSlide(C,o); }
      else DSTE.std(C, o);
    }
    if (T.decor) T.decor(C, kind);
    if (kind!=='cover' && kind!=='closing' && C.o.foot!==false) footer(C);
  } catch(err) {
    if (window.console) console.error('[DSTE] render error', T.id, kind, err);
  }
  var _lg = null; try { _lg = DSTE.addLogo(C, kind); } catch(e){}
    if (T.__rec) recolorPage(C, T.__rec);
    if (_lg) _lg.fix();
  return {bg:C.bgFill, els:C.els, kind:kind, vid:v.id};
};

DSTE.quoteSlide = function(C,o){
  var S=C.S, M=S.margin||64, A={x:M,w:W-2*M};
  if (o.img==='right'||o.img==='left'){
    var iw=Math.round(W*0.42), framed=!!S.imgFramed;
    if (framed) imgBox(C,o.img==='right'?W-iw:28,28,iw-28,H-56,{r:S.imgR!=null?S.imgR:20}); else imgBox(C,o.img==='right'?W-iw:0,0,iw,H,{r:0});
    A.x=o.img==='right'?M:iw+56; A.w=W-iw-M-56;
  }
  B.quote(C,{x:A.x,y:120,w:A.w,h:H-220},o);
};

/* generic cover / closing (templates normally override) */
DSTE.genericCover = function(C,v){
  var P=C.P, f=C.cover, M=C.S.margin||64, o=C.o||{};
  var x=M, w=W-2*M;
  if (o.img==='right'){ imgBox(C,W*0.52,0,W*0.48,H,{}); w=W*0.52-M-40; }
  if (f.company) C.text(x,56,w,24,f.company,{font:'body',size:15,wt:700,color:P.accent,caps:true,ls:0.16,ed:{f:'tf-company'}});
  var te=C.text(x,190,w,230,C.s.title,{font:'head',fit:{max:76,min:36},color:P.title||P.text,ed:{f:'title'},maxLines:3,va:'b'});
  C.rect(x,436,90,5,{fill:P.accent});
  if (C.s.subtitle) C.text(x,462,w,80,C.s.subtitle,{font:'body',fit:{max:22,min:14},color:P.muted,ed:{f:'subtitle'},lh:1.4,maxLines:3});
  var meta=[f.presenter,f.designation,f.date].filter(Boolean).join('  ·  ');
  if (meta) C.text(x,640,w,24,meta,{font:'body',size:14,wt:600,color:P.muted,ed:null});
};
DSTE.genericClosing = function(C,v){
  var P=C.P, M=C.S.margin||64;
  C.text(M,200,W-2*M,160,C.s.title||'Thank You',{font:'head',fit:{max:96,min:40},color:P.title||P.text,align:'c',ed:{f:'title'},maxLines:2,va:'b'});
  if (C.s.subtitle) C.text(M+120,380,W-2*M-240,70,C.s.subtitle,{font:'body',fit:{max:22,min:14},color:P.muted,align:'c',ed:{f:'subtitle'},maxLines:2});
  DSTE.contactRow(C,M,520,W-2*M,{});
};
/* contact lines from closing content (Email:, Website:, Phone:) with icons */
DSTE.contacts = function(C){
  var out=[], map={email:'mdi:email-outline',website:'mdi:web',phone:'mdi:phone-outline',address:'mdi:map-marker-outline',linkedin:'mdi:linkedin'};
  C.it.forEach(function(t){ var k=(t.h||'').toLowerCase(); var ic=map[k]||(/@/.test(t.d)?map.email:(/www|http|\.com/.test(t.d)?map.website:(/\+?\d[\d\s\-()]{6,}/.test(t.d)?map.phone:null))); if(ic) out.push({i:t.i,icon:ic,label:t.h,val:t.d}); });
  if (!out.length && C.cover.website) out.push({i:-1,icon:map.website,label:'Website',val:C.cover.website});
  return out.slice(0,3);
};
DSTE.contactRow = function(C,x,y,w,o){
  var P=C.P, cs=DSTE.contacts(C); if(!cs.length) return;
  var n=cs.length, gap=24, cw=(w-gap*(n-1))/n;
  cs.forEach(function(c,k){
    var cx0=x+k*(cw+gap);
    if (o.cards) card(C,cx0,y,cw,84,{});
    mark(C,cx0+(o.cards?18:0),y+(o.cards?20:0),44,c.icon,{});
    C.text(cx0+(o.cards?18:0)+58,y+(o.cards?18:0),cw-80,20,c.label||'',{font:'body',size:12,wt:700,color:o.lc||P.accent,caps:true,ls:0.12,lh:1});
    C.text(cx0+(o.cards?18:0)+58,y+(o.cards?40:22),cw-80,26,c.val,{font:'body',fit:{max:17,min:11},wt:600,color:o.vc||P.text,ed:c.i>=0?{f:'x-part',i:c.i,p:1}:null,maxLines:1});
  });
};
DSTE.contactList = function(C,x,y,w,o){
  var P=C.P, cs=DSTE.contacts(C); o=o||{};
  cs.forEach(function(c,k){
    var yy=y+k*(o.gap||56);
    mark(C,x,yy,40,c.icon,o.markO||{});
    C.text(x+56,yy,w-56,40,c.val,{font:'body',fit:{max:18,min:11},wt:600,color:o.vc||P.text,va:'m',ed:c.i>=0?{f:'x-part',i:c.i,p:1}:null,maxLines:1});
  });
};

/* ============================================================
   HTML OUTPUT (editor preview + thumbnails + layout panel)
   Sizes use container-query units so one render scales anywhere.
   ============================================================ */
function cq(v){ return (v/12.8).toFixed(3)+'cqw'; }
function px(v,d){ return (v/d*100).toFixed(3)+'%'; }
function cssGrad(g,op){
  if (!g || !g.stops) return '';
  var st=g.stops.map(function(s){ return rgba(s[1], (s[2]!=null?s[2]:1)*(op!=null?op:1))+' '+s[0]+'%'; }).join(',');
  if (g.type==='radial') return 'radial-gradient(closest-side,'+st+')';
  return 'linear-gradient('+(g.ang!=null?g.ang:135)+'deg,'+st+')';
}
DSTE.cssGrad = cssGrad;
var CLIP = {
  chevron:'polygon(0 0,calc(100% - 18%) 0,100% 50%,calc(100% - 18%) 100%,0 100%,18% 50%)',
  homePlate:'polygon(0 0,calc(100% - 18%) 0,100% 50%,calc(100% - 18%) 100%,0 100%)',
  triangle:'polygon(50% 0,100% 100%,0 100%)',
  rtTriangle:'polygon(0 0,100% 100%,0 100%)',
  diamond:'polygon(50% 0,100% 50%,50% 100%,0 50%)',
  hexagon:'polygon(25% 0,75% 0,100% 50%,75% 100%,25% 100%,0 50%)',
  parallelogram:'polygon(25% 0,100% 0,75% 100%,0 100%)',
  pentagon:'polygon(50% 0,100% 38%,82% 100%,18% 100%,0 38%)'
};
DSTE.toHTML = function(page, idx, opts){
  opts=opts||{};
  var edit = opts.edit!==false;
  var bg = page.bg||{}, bgCss = bg.grad ? ('background:'+cssGrad(bg.grad)+';') : ('background:'+hx(bg.fill||'#FFFFFF')+';');
  var h = '<div class="dste-slide" style="position:absolute;inset:0;overflow:hidden;container-type:size;'+bgCss+'line-height:1.2!important;">';
  page.els.forEach(function(e){
    var _h0 = h.length;                                   /* DS Template Element Editor: where this element's HTML starts */
    var box = 'position:absolute;left:'+px(e.x,W)+';top:'+px(e.y,H)+';width:'+px(e.w,W)+';height:'+px(e.h,H)+';';
    if (e.t==='shape' && e.pd && e.pd.length) { /* DS Template Element Editor: a shape with round corners or its own number of sides, drawn as its outline */
  var sw0 = Math.max(1, +e.w || 1), sh0 = Math.max(1, +e.h || 1), sid = 'dss' + (DSTE.__sv = (DSTE.__sv || 0) + 1), sdf = '', sfill = 'none', sop = e.op != null ? +e.op : 1;
  var sgr = function(g, id, op){
    var st = (g.stops || []).map(function(q){ return '<stop offset="' + (+q[0] || 0) + '%" stop-color="' + hx(q[1]) + '" stop-opacity="' + ((q[2] != null ? +q[2] : 1) * (op != null ? op : 1)) + '"/>'; }).join('');
    if (g.type === 'radial') return '<radialGradient id="' + id + '" cx="0.5" cy="0.5" r="0.5">' + st + '</radialGradient>';
    var ga = (g.ang != null ? +g.ang : 135) * Math.PI / 180, gx = Math.sin(ga) / 2, gy = -Math.cos(ga) / 2;
    return '<linearGradient id="' + id + '" x1="' + (0.5 - gx) + '" y1="' + (0.5 - gy) + '" x2="' + (0.5 + gx) + '" y2="' + (0.5 + gy) + '">' + st + '</linearGradient>';
  };
  if (e.grad && e.grad.stops) { sdf += sgr(e.grad, sid + 'f', sop); sfill = 'url(#' + sid + 'f)'; }
  else if (e.fill) sfill = hx(e.fill);
  var spd = pathD(e.pd), sbody = '<path d="' + spd + '" fill="' + sfill + '"' + (sfill.charAt(0) === '#' && sop < 1 ? ' fill-opacity="' + sop + '"' : '') + '/>';
  if (e.pimg && e.pimg.src) {
    var pq = e.pimg, iw0 = +pq.iw || 0, ih0 = +pq.ih || 0, ix = 0, iy = 0, iW = sw0, iH = sh0;
    if (!pq.st && iw0 > 0 && ih0 > 0) { var ksc = Math.max(sw0 / iw0, sh0 / ih0) * Math.max(1, +pq.z || 1); iW = iw0 * ksc; iH = ih0 * ksc; ix = (sw0 - iW) * (pq.px != null ? +pq.px : 50) / 100; iy = (sh0 - iH) * (pq.py != null ? +pq.py : 50) / 100; }
    sdf += '<clipPath id="' + sid + 'c"><path d="' + spd + '"/></clipPath>';
    sbody += '<image href="' + String(pq.src).replace(/["<>\s]/g, '') + '" x="' + ix + '" y="' + iy + '" width="' + iW + '" height="' + iH + '" preserveAspectRatio="none" clip-path="url(#' + sid + 'c)" opacity="' + (pq.op != null ? Math.max(0, Math.min(1, +pq.op)) : 1) + '"/>';
  }
  if (e.line) {
    var sst = hx(e.line);
    if (e.lgrad && e.lgrad.stops) { sdf += sgr(e.lgrad, sid + 'l', null); sst = 'url(#' + sid + 'l)'; }
    sbody += '<path d="' + spd + '" fill="none" stroke="' + sst + '" stroke-opacity="' + (e.lop != null ? +e.lop : 1) + '" stroke-width="' + (+e.lw || 1) + '" stroke-linejoin="round"' + (e.dash ? ' stroke-dasharray="6 5"' : '') + '/>';
  }
  h += '<svg style="' + box + 'overflow:visible;pointer-events:none;' + (e.rot ? 'transform:rotate(' + e.rot + 'deg);' : '') + '" viewBox="0 0 ' + sw0 + ' ' + sh0 + '" preserveAspectRatio="none">' + (sdf ? '<defs>' + sdf + '</defs>' : '') + sbody + '</svg>';
} else
if (e.t==='rect' || e.t==='ell' || e.t==='shape') {
      var s = box;
      if (e.grad) s += 'background:'+cssGrad(e.grad,e.op)+';';
      else if (e.fill) s += 'background:'+rgba(e.fill,e.op!=null?e.op:1)+';';
      if (e.line) s += 'border:'+(e.dash?'dashed ':'solid ')+'max(0.5px,'+cq(e.lw||1)+') '+rgba(e.line,e.lop!=null?e.lop:1)+';box-sizing:border-box;';
      if (e.t==='ell') s += 'border-radius:50%;';
      else if (e.r) s += 'border-radius:'+cq(e.r)+';';
      if (e.t==='shape' && CLIP[e.prst]) s += 'clip-path:'+CLIP[e.prst]+';';
      if (e.rot) s += 'transform:rotate('+e.rot+'deg);';
      if (e.shadow) s += 'box-shadow:0 '+cq(8)+' '+cq(24)+' rgba(0,0,0,'+(typeof e.shadow==='number'?e.shadow:0.18)+');';
      var pin = ''; /* DS Template Element Editor: Fill > Picture */
if (e.pimg && e.pimg.src) { var pg0 = e.pimg, bs = '100% 100%'; if (!pg0.st) { var piw = +pg0.iw || 0, pih = +pg0.ih || 0; if (piw > 0 && pih > 0 && e.w > 0 && e.h > 0) { var psc = Math.max(e.w / piw, e.h / pih) * Math.max(1, +pg0.z || 1); bs = (piw * psc / e.w * 100).toFixed(3) + '% ' + (pih * psc / e.h * 100).toFixed(3) + '%'; } else bs = 'cover'; } s += 'overflow:hidden;'; pin = '<div style="position:absolute;inset:0;background-image:url(\'' + String(pg0.src).replace(/[\'"()\\<>\s]/g, '') + '\');background-repeat:no-repeat;background-size:' + bs + ';background-position:' + (pg0.px != null ? +pg0.px : 50) + '% ' + (pg0.py != null ? +pg0.py : 50) + '%;opacity:' + (pg0.op != null ? Math.max(0, Math.min(1, +pg0.op)) : 1) + ';pointer-events:none;"></div>'; }
h += '<div style="'+s+'pointer-events:none;">'+pin+'</div>';
    } else if (e.t==='line') {
      h += svgWrap('<line x1="'+e.x1+'" y1="'+e.y1+'" x2="'+e.x2+'" y2="'+e.y2+'" stroke="'+hx(e.color)+'" stroke-opacity="'+(e.op!=null?e.op:1)+'" stroke-width="'+(e.lw||1)+'"'+(e.dash?' stroke-dasharray="6 5"':'')+'/>');
    } else if (e.t==='path') {
      h += svgWrap('<path d="'+pathD(e.d)+'" fill="'+(e.fill?hx(e.fill):'none')+'" fill-opacity="'+(e.op!=null?e.op:1)+'" stroke="'+(e.stroke?hx(e.stroke):'none')+'" stroke-opacity="'+(e.lop!=null?e.lop:1)+'" stroke-width="'+(e.lw||1)+'" stroke-linecap="round" stroke-linejoin="round"/>');
    } else if (e.t==='icon') {
      var u='https://api.iconify.design/'+String(e.name).replace(':','/')+'.svg?color='+encodeURIComponent(hx(e.color));
      if ((e.tgrad && e.tgrad.stops) || e.tline) { /* DS Template Element Editor: a gradient colour and / or an outline (solid or gradient) on an icon */
  var iw1 = Math.max(1, +e.w || 1), ih1 = Math.max(1, +e.h || 1), iid = 'dsi' + (DSTE.__sv = (DSTE.__sv || 0) + 1), iu = 'https://api.iconify.design/' + String(e.name).replace(':', '/') + '.svg?color=%23ffffff', idf = '', ibd = '', ipd = (+e.tlw || 1) * 2 + 2;
  var igr = function(g, id){
    var st = (g.stops || []).map(function(q){ return '<stop offset="' + (+q[0] || 0) + '%" stop-color="' + hx(q[1]) + '" stop-opacity="' + (q[2] != null ? +q[2] : 1) + '"/>'; }).join('');
    if (g.type === 'radial') return '<radialGradient id="' + id + '" cx="0.5" cy="0.5" r="0.5">' + st + '</radialGradient>';
    var ga = (g.ang != null ? +g.ang : 135) * Math.PI / 180, gx = Math.sin(ga) / 2, gy = -Math.cos(ga) / 2;
    return '<linearGradient id="' + id + '" x1="' + (0.5 - gx) + '" y1="' + (0.5 - gy) + '" x2="' + (0.5 + gx) + '" y2="' + (0.5 + gy) + '">' + st + '</linearGradient>';
  };
  var irc = function(paint, mk){ return '<rect x="' + (-ipd) + '" y="' + (-ipd) + '" width="' + (iw1 + 2 * ipd) + '" height="' + (ih1 + 2 * ipd) + '" fill="' + paint + '" mask="url(#' + mk + ')"/>'; };
  var imk = function(id, filt){ return '<mask id="' + id + '" maskUnits="userSpaceOnUse" x="' + (-ipd) + '" y="' + (-ipd) + '" width="' + (iw1 + 2 * ipd) + '" height="' + (ih1 + 2 * ipd) + '"><image href="' + iu + '" x="0" y="0" width="' + iw1 + '" height="' + ih1 + '"' + (filt ? ' filter="url(#' + filt + ')"' : '') + '/></mask>'; };
  var ifl = hx(e.color); if (e.tgrad && e.tgrad.stops) { idf += igr(e.tgrad, iid + 'f'); ifl = 'url(#' + iid + 'f)'; }
  if (e.tline) {
    var ilc = hx(e.tline); if (e.tlgrad && e.tlgrad.stops) { idf += igr(e.tlgrad, iid + 'l'); ilc = 'url(#' + iid + 'l)'; }
    idf += '<filter id="' + iid + 'd" x="-50%" y="-50%" width="200%" height="200%"><feMorphology operator="dilate" radius="' + (+e.tlw || 1) + '"/></filter>' + imk(iid + 'o', iid + 'd');
    ibd += irc(ilc, iid + 'o');
  }
  idf += imk(iid + 'm', ''); ibd += irc(ifl, iid + 'm');
  h += '<svg style="' + box + 'display:block;overflow:visible;pointer-events:none;" viewBox="0 0 ' + iw1 + ' ' + ih1 + '" preserveAspectRatio="none"><defs>' + idf + '</defs>' + ibd + '</svg>';
} else
h += '<img src="'+u+'" alt="" draggable="false" style="'+box+'display:block;pointer-events:none;" onerror="this.style.visibility=\'hidden\'"/>';
    } else if (e.t==='ring') {
      var th=(e.th/e.w*50).toFixed(2), pc=e.pct;
      h += '<div style="'+box+'border-radius:50%;background:conic-gradient('+hx(e.color)+' 0 '+pc+'%,'+hx(e.track||'#DDDDDD')+' '+pc+'% 100%);-webkit-mask:radial-gradient(farthest-side,transparent calc(100% - '+th+'%),#000 calc(100% - '+th+'% + 0.5px));mask:radial-gradient(farthest-side,transparent calc(100% - '+th+'%),#000 calc(100% - '+th+'% + 0.5px));pointer-events:none;"></div>';
    } else if (e.t==='img') {
      var sl = opts.slide || {};
      var img = sl.image_data || null, pos = sl.image_position || {x:50,y:50};
      var rad = e.shape==='ell' ? 'border-radius:50%;' : (e.r ? 'border-radius:'+cq(e.r)+';' : '');
      var inner = img ? ('background-image:url('+img+');background-size:cover;background-repeat:no-repeat;background-position:'+pos.x+'% '+pos.y+'%;')
                      : ('background:'+(e.ph&&e.ph.grad?cssGrad(e.ph.grad):(e.ph&&e.ph.fill?hx(e.ph.fill):'#8899AA'))+';');
      var phHtml = img ? '' : '<div style="display:flex;flex-direction:column;align-items:center;gap:'+cq(8)+';opacity:.75;color:#fff;font:600 '+cq(13)+' Arial,sans-serif;"><svg viewBox="0 0 24 24" style="width:'+cq(40)+';height:'+cq(40)+'" fill="#fff"><path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2M8.5 13.5l2.5 3 3.5-4.5 4.5 6H5z"/></svg>Click to add image</div>';
      if (edit) {
        h += '<div class="dsp-vp-imgzone" data-slide-idx="'+idx+'" style="'+box+rad+'overflow:hidden;">'
          + '<div style="position:absolute;inset:0;'+inner+'display:flex;align-items:center;justify-content:center;pointer-events:none;">'+phHtml+'</div>'
          + (img?'<button type="button" class="dsp-vp-imgreset" onclick="event.stopPropagation();dspResetImagePosition('+idx+');" title="Reset image position">⟲</button><button type="button" class="dsp-vp-cropbtn" onclick="event.stopPropagation();window.vpStartCropEdit('+idx+');" title="Crop image">✂️ Crop</button>':'')
          + '<div class="dsp-vp-ihover" style="pointer-events:none;"><div class="dsp-vp-ihover-inner" style="pointer-events:auto;"><div>🖼</div><div>Change Image</div></div></div>'
          + '</div>';
      } else {
        h += '<div style="'+box+rad+'overflow:hidden;"><div style="position:absolute;inset:0;'+inner+'display:flex;align-items:center;justify-content:center;">'+phHtml+'</div></div>';
      }
    } else if (e.t==='chart') {
      var DCc = window.DS_CHART, ci = edit && idx>=0 ? idx : -1;
      h += '<div class="dsch-box" data-dsch-idx="'+ci+'" style="'+box+'">'+(DCc ? DCc.svg(e.spec,{w:e.w,h:e.h,colors:e.colors,tc:e.tc,mc:e.mc,bgc:e.bgc,legend:e.legend,uid:'dst'+idx,font:C_font(e)}) : '')
        + (ci>=0 ? '<button type="button" class="dsch-edit" onclick="event.stopPropagation();window.DS_CHART&&DS_CHART.openEditor('+ci+')">&#9998; Edit chart</button>' : '') + '</div>';
    } else if (e.t==='logo') {
      h += '<img class="dste-logo" src="'+(e._src||'')+'" alt="" draggable="false" style="'+box+'display:block;object-fit:contain;pointer-events:none;"/>';
    } else if (e.t==='text') {
      var jc = e.va==='m'?'center':(e.va==='b'?'flex-end':'flex-start');
      var fam = "'"+e.fam+"',Arial,sans-serif";
      var ts = 'font-family:'+fam+'!important;font-size:'+cq(e.size)+';font-weight:'+e.wt+';color:'+rgba(e.color,e.op!=null?e.op:1)+';line-height:'+(e.lh||1.2)+'!important;text-align:'+(e.align==='c'?'center':(e.align==='r'?'right':'left'))+';'
             + (e.caps?'text-transform:uppercase;':'') + (e.ls?'letter-spacing:'+e.ls+'em;':'') + (e.italic?'font-style:italic;':'')
             + (e.dec?'text-decoration:'+(String(e.dec).indexOf('u')>=0?'underline ':'')+(String(e.dec).indexOf('s')>=0?'line-through':'')+';':'')
             + (e.nw?'white-space:nowrap;':'white-space:pre-wrap;overflow-wrap:break-word;')+'margin:0;padding:0;outline:none;';
      var ed = '';
      if (edit && e.ed && idx>=0) {
        ed = ' contenteditable="true" spellcheck="false" data-ed-slide="'+idx+'" data-ed-field="'+e.ed.f+'"'+(e.ed.i!=null?' data-ed-idx="'+e.ed.i+'"':'')+(e.ed.p!=null?' data-ed-part="'+e.ed.p+'"':'');
        ts += 'cursor:text;pointer-events:auto;min-height:1em;';
      }
      h += '<div style="'+box+'display:flex;flex-direction:column;justify-content:'+jc+';pointer-events:none;"><div'+ed+' style="'+ts+'">'+esc(e.text)+'</div></div>';
    }
    /* DS Template Element Editor: the element's key on its node (data-dk); rotation for the kinds that had none;
       transparency for icons, pictures and the logo */
    if (h.length > _h0 && (e._dk || e.rot || e.op != null || e._css || e._cssIn || e._wrap)) {
      var _seg = h.slice(_h0), _ex = '', _box = (e.t==='icon'||e.t==='ring'||e.t==='img'||e.t==='chart'||e.t==='logo'||e.t==='text');
      if (e.rot && _box) _ex += 'transform:rotate('+(+e.rot||0)+'deg);';
      if (e.op != null && (e.t==='icon'||e.t==='img'||e.t==='logo')) _ex += 'opacity:'+Math.max(0,Math.min(1,+e.op||0))+';';
      if (_ex) _seg = _seg.replace(' style="', ' style="'+_ex);
      if (e._dk) _seg = _seg.replace(/^<([a-z]+)/, '<$1 data-dk="'+String(e._dk).replace(/[^A-Za-z0-9_.~-]/g,'')+'"');
      /* DS Template Element Editor: shadow, gradient outline, text outline, gradient text (CSS made by that snippet) */
      if (e._css) { var _c1 = String(e._css).replace(/["<>]/g,''); _seg = _seg.replace(/ style="([^"]*)"/, function(m0, a){ return ' style="'+a+_c1+'"'; }); }
      if (e._cssIn) { var _c2 = String(e._cssIn).replace(/["<>]/g,''), _n2 = 0; _seg = _seg.replace(/ style="([^"]*)"/g, function(m0, a){ return (++_n2 === 2) ? ' style="'+a+_c2+'"' : m0; }); }
      if (e._wrap) _seg = '<div style="position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;'+String(e._wrap).replace(/["<>]/g,'')+'">'+_seg+'</div>';
      h = h.slice(0,_h0) + _seg;
    }
  });
  return h + '</div>';
};
function C_font(e){ return e.fam || ''; }
function svgWrap(inner){ return '<svg viewBox="0 0 1280 720" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;overflow:visible;">'+inner+'</svg>'; }
function pathD(d){ return d.map(function(c){ return c[0]+(c.length>1?' '+c.slice(1).join(' '):''); }).join(' '); }

/* ============================================================
   REGISTRY
   ============================================================ */
DSTE.register = function(T){
  if (!T || !T.id) return;
  if (!DSTE.reg[T.id]) DSTE.order.push(T.id);
  T.fonts = T.fonts || {head:'Arial',body:'Arial',headW:700,bodyW:400};
  T.s = T.s || {}; T.pal = T.pal || {};
  var P = T.pal; P.title = P.title || P.text; P.onAccent = P.onAccent || '#FFFFFF'; P.line = P.line || mix(P.text||'#000000',P.bg||'#FFFFFF',0.82);
  DSTE.reg[T.id] = T;
  if (DSTE.onRegister) try { DSTE.onRegister(T); } catch(e){}
};
DSTE.get = function(id){ return DSTE.reg[id] || null; };
DSTE.renderHTML = function(T, slide, vid, idx, deck, opts){
  var page = DSTE.renderPage(T, slide, vid, idx, deck);
  opts = opts || {}; opts.slide = slide;
  return DSTE.toHTML(page, idx, opts);
};
})();

/* ---------- reusable full-slide compositions ---------- */
(function(){
var DSTE = window.DSTE, W = DSTE.W, H = DSTE.H, u = DSTE.u;
/* agenda: big title on the left, numbered list on the right */
DSTE.agendaSplit = function(C, o){
  o = o || {}; var P = C.P, M = C.S.margin || 64, split = o.split || 0.42;
  var lw = Math.round((W - 2*M) * split);
  var te = C.text(M, o.titleY || 200, lw - 30, 240, C.s.title || 'Agenda', {font:'head', fit:{max:o.tMax||76, min:36}, color:P.title, ed:{f:'title'}, maxLines:3, va:o.titleVa||'t'});
  if (C.s.subtitle) C.text(M, (o.titleY||200) + te._h + 20, lw - 40, 120, C.s.subtitle, {font:'body', fit:{max:18, min:13}, color:P.muted, ed:{f:'subtitle'}, lh:1.45, maxLines:4});
  var x = M + lw + 30, w = W - M - x;
  if (o.rule !== false) C.rect(x - 30, 70, 1.5, H - 140, {fill:o.ruleC || P.line});
  var it = C.it.slice(0, 10), n = it.length, top = o.listY || 90, bot = H - 90, rh = Math.min(o.rowMax || 64, (bot - top) / Math.max(1, n));
  var hs = C.groupFit(it.map(function(t){ return t.h || t.d; }), w - 90, rh * 0.62, {font:o.itemFont||'body', wt:o.itemWt||600, max:o.iMax||21, min:12, maxLines:1});
  var y0 = top + ((bot - top) - rh * n) / 2;
  it.forEach(function(t, k){
    var y = y0 + k * rh;
    C.text(x + 10, y, 60, rh, u.pad2(k+1), {font:'head', size:Math.min(hs+2, 22), color:o.numC || P.accent, va:'m', lh:1, caps:false, ls:0});
    C.text(x + 80, y, w - 90, rh, t.h || t.d, {font:o.itemFont||'body', wt:o.itemWt||600, size:hs, color:P.text, va:'m', lh:1.2, ed:{f:'x-part', i:t.i, p:t.h?0:1}, maxLines:1});
    if (o.rows !== false && k < n - 1) C.rect(x + 10, y + rh - 0.5, w - 10, 1, {fill:P.line, op:0.8});
  });
};
/* hexagon outline decoration */
DSTE.hexOutline = function(C, cx, cy, r, o){
  o = o || {}; var pts = [];
  for (var k = 0; k < 6; k++){ var a = Math.PI/180*(60*k - 30); pts.push([cx + r*Math.cos(a), cy + r*Math.sin(a)]); }
  var d = [['M', pts[0][0], pts[0][1]]]; for (var j = 1; j < 6; j++) d.push(['L', pts[j][0], pts[j][1]]); d.push(['Z']);
  C.path(d, {stroke:o.color || '#FFFFFF', lw:o.lw || 1.5, lop:o.op != null ? o.op : 0.5});
};
/* soft glow blob (radial gradient ellipse fading to transparent) */
DSTE.glow = function(C, cx, cy, rx, ry, color, op){
  C.ell(cx - rx, cy - ry, rx*2, ry*2, {grad:{type:'radial', stops:[[0, color, op == null ? 0.9 : op], [100, color, 0]]}});
};
/* four-point sparkle star */
DSTE.sparkle = function(C, cx, cy, r, color, op){
  var k = r * 0.18;
  C.path([['M',cx,cy-r],['Q',cx+k,cy-k,cx+r,cy],['Q',cx+k,cy+k,cx,cy+r],['Q',cx-k,cy+k,cx-r,cy],['Q',cx-k,cy-k,cx,cy-r],['Z']], {fill:color, op:op == null ? 1 : op});
};
/* merge helper for per-slide style overrides */
DSTE.merge = function(a, b){ var o = {}, k; for (k in a) o[k] = a[k]; for (k in b) { if (b[k] && typeof b[k] === 'object' && !Array.isArray(b[k]) && a && a[k] && typeof a[k] === 'object') o[k] = DSTE.merge(a[k], b[k]); else o[k] = b[k]; } return o; };
})();

(function(){
var D = window.DSTE;
/* bundle of flowing lines (wave ribbons) */
D.waveBundle = function(C, o){
  var n = o.n || 14, x0 = o.x0, y0 = o.y0, x1 = o.x1, y1 = o.y1, amp = o.amp || 90, sp = o.spread || 6;
  for (var k = 0; k < n; k++){
    var t = k / Math.max(1, n - 1), dy = (k - n/2) * sp, ph = (o.phase || 0) + t * 1.4;
    var cx1 = x0 + (x1 - x0) * 0.33, cx2 = x0 + (x1 - x0) * 0.66;
    var a1 = amp * Math.sin(ph * 2.1), a2 = amp * Math.cos(ph * 1.7);
    C.path([['M', x0, y0 + dy], ['C', cx1, y0 + dy - a1 + (y1 - y0) * 0.2, cx2, y1 + dy + a2 - (y1 - y0) * 0.2, x1, y1 + dy * (o.fan || 1.6)]], {stroke:o.color, lw:o.lw || 1.2, lop:(o.op != null ? o.op : 0.6) * (o.fade ? (0.35 + 0.65 * (1 - Math.abs(t - 0.5) * 2)) : 1)});
  }
};
/* circle arc (for orbit / chrome decorations) */
D.arc = function(C, cx, cy, r, a0, a1, o){
  /* true circular arc: cubic Bezier segments of at most 45 degrees (no visible facets) */
  var n = Math.max(1, Math.ceil(Math.abs(a1 - a0) / 45)), step = (a1 - a0) / n, rad = Math.PI / 180;
  var k = 4 / 3 * Math.tan(step * rad / 4);
  var d = [['M', cx + r * Math.cos(a0 * rad), cy + r * Math.sin(a0 * rad)]];
  for (var i = 0; i < n; i++) {
    var s0 = (a0 + step * i) * rad, s1 = (a0 + step * (i + 1)) * rad;
    var x0 = cx + r * Math.cos(s0), y0 = cy + r * Math.sin(s0), x3 = cx + r * Math.cos(s1), y3 = cy + r * Math.sin(s1);
    d.push(['C', x0 - k * r * Math.sin(s0), y0 + k * r * Math.cos(s0), x3 + k * r * Math.sin(s1), y3 - k * r * Math.cos(s1), x3, y3]);
  }
  C.path(d, {stroke:o.color, lw:o.lw || 1.5, lop:o.op != null ? o.op : 1});
};
/* clean full ring (outline circle) */
D.ring = function(C, cx, cy, r, o){ o = o || {}; C.ell(cx - r, cy - r, r * 2, r * 2, {line:o.color || '#FFFFFF', lw:o.lw || 2, lop:o.op != null ? o.op : 1, fill:o.fill, op:o.fop}); };
})();

/* ============================================================
   DS TEMPLATE ENGINE — router + editor integration
   - template picker on the AI Presentation home screen
   - sends generation / download to the picked template's snippet
   - renders the editor, thumbnails and layout panel for that template
   ============================================================ */
(function(){
'use strict';
var DSTE = window.DSTE; if (!DSTE || DSTE.router) return;
DSTE.router = 1;
var u = DSTE.u;
var PICK_KEY = 'dsteTplPick';

/* ---------- which template is picked / active ---------- */
var _pick0 = (function(){ try { return sessionStorage.getItem(PICK_KEY) || 'classic'; } catch(e){ return 'classic'; } })();
  /* the Classic card now makes decks with the editable Classic (template 'classicx', snippet "DS Template 65") when it is active; old Classic decks still open the old way */
  Object.defineProperty(DSTE, 'pick', {configurable:true, enumerable:true, get:function(){ return (!_pick0 || _pick0 === 'classic') && DSTE.reg && DSTE.reg.classicx ? 'classicx' : _pick0; }, set:function(v){ _pick0 = v; }});
function setPick(id){
  DSTE.pick = id;
  try { sessionStorage.setItem(PICK_KEY, id); } catch(e){}
  syncHomeUI();
}
function deck(){ if (window.S && window.S.data && window.S.data.slides) return window.S.data; if (window.G && window.G.pptData) return window.G.pptData; return null; }
function activeId(){
  var d1 = window.S && window.S.data, d2 = window.G && window.G.pptData;
  var id = (d1 && d1.tpl) || (d2 && d2.tpl) || null;
  return id && DSTE.reg[id] ? id : null;
}
DSTE.activeId = activeId;
function pickedTpl(){ return DSTE.pick && DSTE.pick!=='classic' && DSTE.reg[DSTE.pick] ? DSTE.pick : null; }

/* ---------- theme on a template deck ('' = the template's own colours) ---------- */
var THEME_KEY = 'dsteTheme';
DSTE.homeTheme = (function(){ try { return sessionStorage.getItem(THEME_KEY) || ''; } catch(e){ return ''; } })();
function setHomeTheme(k){ DSTE.homeTheme = k || ''; try { sessionStorage.setItem(THEME_KEY, DSTE.homeTheme); } catch(e){} }
function deckTheme(){ var d = deck(); return (d && d.tplTheme) || ''; }
function setDeckTheme(k){
  [window.S && window.S.data, window.G && window.G.pptData].forEach(function(d){ if (d && d.slides) d.tplTheme = k || ''; });
}
function tplFor(id, key){ var T = DSTE.reg[id]; return T ? DSTE.themed(T, key) : null; }
function curT(){ var id = activeId(); return id ? tplFor(id, deckTheme()) : null; }
DSTE.curT = curT;

/* ---------- hook used by Snippet 17 (renderSlide / vpRender / canvas observer) ---------- */
window.DS_TPL_ACTIVE = function(){
  var id = activeId(); if (!id) return null;
  var T = curT();
  return {
    id: id,
    render: function(s, v, idx){ return DSTE.renderHTML(curT(), s, v || 'v1', typeof idx==='number' ? idx : -1, deck(), {edit:true}); },
    variants: function(type, slide){ var k = DSTE.kindOf(slide || {type:type}); if (k==='section') k='text'; return DSTE.variantsFor(T, k).map(function(v){ return {id:v.id, lbl:v.lbl}; }); }
  };
};

/* ---------- fonts ---------- */
var _fontsReq = {};
DSTE.loadFonts = function(T){
  if (!T || !T.fonts || !T.fonts.gf || _fontsReq[T.id + '|' + T.fonts.head]) return;
  _fontsReq[T.id + '|' + T.fonts.head] = 1;
  T.fonts.gf.split('|').forEach(function(f){               /* one request per family, so one bad family never blocks the rest */
    if (_fontsReq['gf:' + f]) return; _fontsReq['gf:' + f] = 1;
    var l = document.createElement('link'); l.rel = 'stylesheet';
    l.href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g,'+') + '&display=swap';
    document.head.appendChild(l);
  });
  if (document.fonts && document.fonts.load) {
    var hw = T.fonts.headW || 700, bw = T.fonts.bodyW || 400;
    var reqs = [];
    [400,500,600,700,800].forEach(function(w){ reqs.push(document.fonts.load(w + ' 40px "' + T.fonts.head + '"')); reqs.push(document.fonts.load(w + ' 16px "' + T.fonts.body + '"')); });
    reqs.push(document.fonts.load('italic 400 16px "' + T.fonts.head + '"'));
    Promise.all(reqs).then(function(){ DSTE.clearFit(); refreshAll(); }).catch(function(){});
  }
};
DSTE.onRegister = function(T){ if (pickedTpl()===T.id || activeId()===T.id) DSTE.loadFonts(T); scheduleHomeUI(); };

/* ---------- re-render editor (after fonts load / edits) ---------- */
function refreshAll(){
  var id = activeId(); if (!id) { syncHomeUI(true); return; }
  var d = deck(); if (!d || !d.slides) return;
  var T = curT(), ch = (window.S && window.S.choices) || {};
  var thumbs = document.getElementById('dsp-ppt-thumbs');
  if (thumbs) Array.prototype.forEach.call(thumbs.children, function(item, i){
    var box = item.querySelector('.dsp-thumb-box'); if (!box || !d.slides[i]) return;
    box.innerHTML = DSTE.renderHTML(T, d.slides[i], ch['slide_'+i] || 'v1', i, d, {edit:false});
  });
  var idx = (window.S && window.S.idx >= 0) ? window.S.idx : 0;
  if (typeof window.applyCanvas === 'function') window.applyCanvas(idx, ch['slide_'+idx] || 'v1');
}
DSTE.refreshEditor = refreshAll;

/* ---------- inline editing for template-only fields ---------- */
document.addEventListener('input', function(e){
  var el = e.target; if (!el || !el.getAttribute) return;
  var f = el.getAttribute('data-ed-field'); if (!f || f.indexOf('x-') !== 0) return;
  var si = parseInt(el.getAttribute('data-ed-slide'), 10);
  var targets = [];
  if (window.S && window.S.data && window.S.data.slides && window.S.data.slides[si]) targets.push(window.S.data.slides[si]);
  if (window.G && window.G.pptData && window.G.pptData.slides && window.G.pptData.slides[si] && targets.indexOf(window.G.pptData.slides[si]) < 0) targets.push(window.G.pptData.slides[si]);
  var txt = (el.innerText || '').replace(/\n+/g,' ').trim();
  targets.forEach(function(slide){
    if (f === 'x-conc') { slide.conclusion = txt; return; }
    if (f === 'x-tpart') {
      var root = el.closest ? el.closest('.dste-slide') : null, pts = [];
      if (root) Array.prototype.forEach.call(root.querySelectorAll('[data-ed-field="x-tpart"]'), function(n){ pts[parseInt(n.getAttribute('data-ed-part'),10)||0] = (n.innerText||'').replace(/\n+/g,' ').trim(); });
      slide.title = pts.filter(Boolean).join(' ');
      return;
    }
    if (f === 'x-part') {
      var i = parseInt(el.getAttribute('data-ed-idx'), 10), p = parseInt(el.getAttribute('data-ed-part'), 10);
      if (!slide.content) slide.content = [];
      var parts = DSTE.splitItem(slide.content[i] || '');
      if (p === 0) parts.h = txt; else if (p === 1) parts.d = txt; else parts.x = txt;
      slide.content[i] = DSTE.joinItem(parts);
    }
  });
}, true);

/* ---------- request routing ---------- */
function isJsonBody(opts){ return opts && typeof opts.body === 'string' && opts.body.charAt(0) === '{'; }
function withBody(opts, b){ var no = {}; for (var k in opts) no[k] = opts[k]; no.body = JSON.stringify(b); return no; }
function buildPages(d, choices){
  var T = tplFor(d.tpl, d.tplTheme || '');
  return d.slides.map(function(s, i){
    var p = DSTE.renderPage(T, s, choices['slide_'+i] || 'v1', i, d);
    return {bg: p.bg, els: p.els.map(function(e){ var c = {}; for (var k in e) { if (k.charAt(0) !== '_' && k !== 'ed' && k !== 'fam') c[k] = e[k]; } return c; })};
  });
}
/* Add Slide: snippet 17 posts with the fetch it saved at load time, so tag the request through the hidden design field */
function wrapAddSlide(){
  var f = window.dspAddSlideGenerate;
  if (typeof f !== 'function' || f.__dste) return;
  var w = function(){
    var id = activeId(), el = document.getElementById('dsp-design'), old = el ? el.value : null;
    if (id && el) el.value = 'tpl:' + id;
    try { return f.apply(this, arguments); } finally { if (el && old !== null) el.value = old; }
  };
  w.__dste = 1; window.dspAddSlideGenerate = w;
}
function installFetch(){
  if (window.__dsteFetch) return; window.__dsteFetch = 1;
  var prev = window.fetch;
  window.fetch = function(url, opts){
    var us = typeof url === 'string' ? url : (url && url.url) || '';
    try {
      if (/\/ds\/v1\/(ai-preview|ai-confirm|generate-ppt)(\?|$)/.test(us) && isJsonBody(opts)) {
        var b = JSON.parse(opts.body);
        var isAdd = !!b.append_slide || b.slides === 1 || b.num_slides === 1;
        if (b.deduct_only) {                       // "Preserve My Text" deck: tag it with the picked template
          var pk = pickedTpl();
          if (pk && window.G && window.G.pptData && !window.G.pptData.tpl) { window.G.pptData.tpl = pk; window.G.pptData.tplTheme = DSTE.homeTheme; if (window.S && window.S.data && window.S.data.slides) { window.S.data.tpl = pk; window.S.data.tplTheme = DSTE.homeTheme; } }
        } else {
          var tid = isAdd ? activeId() : (b.ppt_data && b.ppt_data.tpl && DSTE.reg[b.ppt_data.tpl] ? b.ppt_data.tpl : pickedTpl());
          if (tid) {
            b.tpl = tid;
            if (!isAdd) { b.tpl_theme = DSTE.homeTheme; if (b.ppt_data && typeof b.ppt_data === 'object' && b.ppt_data.tplTheme == null) b.ppt_data.tplTheme = DSTE.homeTheme; }
            opts = withBody(opts, b);
          }
        }
      }
      if (/\/ds\/v1\/build-pptx(\?|$)/.test(us) && isJsonBody(opts)) {
        var id = activeId(), d = deck();
        if (id && d && d.slides) {
          var b2 = JSON.parse(opts.body);
          b2.tpl = id; b2.layout = '16:9';
          b2.tpl_pages = buildPages(d, (window.S && window.S.choices) || {});
          if (d.logo && typeof d.logo.src === 'string' && d.logo.src) b2.tpl_logo = {src: d.logo.src}; /* company logo */
          var TT = tplFor(id, d.tplTheme || ''); if (TT && TT.__rec) b2.tpl_fonts = {head: TT.fonts.head, body: TT.fonts.body};
          opts = withBody(opts, b2);
        }
      }
    } catch(err) { if (window.console) console.warn('[DSTE] route', err); }
    return prev.apply(this, [url, opts]);
  };
}

/* ---------- live "generating" overlay for template decks ---------- */
function wrapLiveGen(){
  if (window.__dsteLive || typeof window.dspLiveGenStream !== 'function') return;
  window.__dsteLive = 1;
  var orig = window.dspLiveGenStream;
  window.dspLiveGenStream = function(ppt, design, onDone){
    var T = ppt && ppt.tpl && tplFor(ppt.tpl, ppt.tplTheme || '');
    if (!T) return orig.apply(this, arguments);
    DSTE.loadFonts(T);
    var slides = ppt.slides || [], total = slides.length;
    var thumbs = document.getElementById('dsp-lg-thumbs'), main = document.getElementById('dsp-lg-slide-content'), sh = document.getElementById('dsp-lg-shimmer');
    var lgMain = document.getElementById('dsp-lg-main'); if (lgMain) lgMain.style.background = T.pal.bg;
    if (thumbs) thumbs.innerHTML = '';
    slides.forEach(function(s, i){
      var box = document.createElement('div');
      box.style.cssText = 'position:relative;width:100%;aspect-ratio:16/9;border-radius:3px;overflow:hidden;opacity:.3;transition:opacity .4s;border:2px solid transparent;background:' + T.pal.bg + ';flex-shrink:0;';
      if (thumbs) thumbs.appendChild(box);
      setTimeout(function(){
        var html = DSTE.renderHTML(T, s, 'v1', -1, ppt, {edit:false});
        box.innerHTML = html; box.style.opacity = '1'; box.style.borderColor = '#8ab4f8';
        if (main) main.innerHTML = html;
        if (sh) { sh.style.transition = 'opacity .4s'; sh.style.opacity = '0'; }
        try { box.scrollIntoView({block:'nearest'}); } catch(e){}
        if (i === total - 1) setTimeout(function(){ if (sh) sh.style.display = 'none'; var bt = document.getElementById('dsp-lg-banner-txt'); if (bt) bt.innerHTML = '<b style="color:#111;">Done!</b> Opening your presentation editor...'; setTimeout(function(){ if (onDone) onDone(); }, 700); }, 400);
      }, i * 250);
    });
    if (!total && onDone) onDone();
  };
}

/* ============================================================
   HOME: template picker
   ============================================================ */
var SAMPLE = {
  cover:{type:'title',title:'Quarterly Growth Strategy',subtitle:'How we double revenue in 2026 through focused execution and smarter partnerships',content:['Company: Northwind Group','Presenter: Alex Morgan','Designation: Chief Strategy Officer','Date: March 2026','Website: www.northwind.com']},
  p3:{type:'three_column',title:'Our Three Growth Pillars',subtitle:'Where we will invest time, talent and capital over the next four quarters',content:['Product Expansion: Launch two enterprise modules that lift average contract value by 28% within the first year.','Channel Partners: Sign 40 regional resellers across India and the Gulf to cut acquisition cost by 35%.','Customer Success: A dedicated onboarding squad reduces churn from 11% to under 6% by Q4.'],icons:['mdi:cube-outline','mdi:handshake-outline','mdi:account-heart'],conclusion:'Focused investment in three pillars compounds growth faster than scattered bets.'},
  stats:{type:'stats',title:'Results That Matter',subtitle:'Twelve months of measurable progress across the business',content:['94%: Customer retention rate | Up from 81% after the new success program','3.2x: Pipeline growth | Driven by partner-sourced enterprise deals','$4.8M: Annual recurring revenue | Crossed our target two months early'],conclusion:'Momentum is real and repeatable when the whole team runs one plan.'},
  timeline:{type:'timeline',title:'Execution Roadmap',subtitle:'Five milestones that take us from pilot to market leader',content:['Q1 2026: Pilot with 12 enterprise design partners and refine pricing.','Q2 2026: Launch partner portal and onboard the first 15 resellers.','Q3 2026: Release analytics module and expand into the Gulf region.','Q4 2026: Reach $8M ARR with churn below 6%.'],conclusion:'Each milestone de-risks the next, so capital is released stage by stage.'}
};
DSTE.SAMPLE = SAMPLE;

var CSS = ''
+ '#dste-sec{background:#fff;border-radius:16px;padding:20px 22px 22px;margin:14px auto;width:100%;max-width:680px;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,sans-serif;}'
+ '#dste-sec .dste-hd{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px;}'
+ '#dste-sec h4{margin:0 0 3px;font-size:16px;font-weight:700;color:#111827;}'
+ '#dste-sec .dste-sub{margin:0;font-size:12.5px;color:#6b7280;}'
+ '#dste-sec .dste-more{flex-shrink:0;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;}'
+ '#dste-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}'
+ '@media(max-width:560px){#dste-grid{grid-template-columns:repeat(2,1fr);}}'
+ '.dste-card{border:2px solid #e5e7eb;border-radius:12px;overflow:hidden;cursor:pointer;background:#fff;transition:border-color .15s,box-shadow .15s;text-align:left;padding:0;text-transform:none!important;letter-spacing:normal!important;font-weight:400;line-height:normal;}'
+ '#dsp-th-grid .dste-th-hide{display:none!important;}'
+ '.dste-card:hover{border-color:#93c5fd;}'
+ '.dste-card.dste-on{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15);}'
+ '.dste-th{position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;background:#1B2A4A;}'
+ '.dste-nm{padding:7px 10px 8px;font-size:12px;font-weight:700;color:#111827;display:flex;justify-content:space-between;align-items:center;gap:6px;}'
+ '.dste-nm small{display:block;font-size:10.5px;font-weight:500;color:#6b7280;margin-top:1px;}'
+ '.dste-ck{width:18px;height:18px;border-radius:50%;background:#2563eb;color:#fff;font-size:11px;display:none;align-items:center;justify-content:center;flex-shrink:0;}'
+ '.dste-on .dste-ck{display:flex;}'
+ '#dste-strip{display:none;margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0;}'
+ '#dste-strip.on{display:block;}'
+ '#dste-strip .dste-sl{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}'
+ '#dste-strip .dste-sl>div{position:relative;aspect-ratio:16/9;border-radius:6px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.15);}'
+ '#dste-strip p{margin:0 0 8px;font-size:12px;color:#374151;}'
+ '#dste-sec .dste-hide{display:none!important;}'
+ '#dste-sec .dste-note{margin-top:10px;font-size:11.5px;color:#6b7280;}'
+ '.dste-foot{display:flex;justify-content:center;margin-top:16px;}'
+ '.dste-viewall{background:#f3f4f6!important;border:1px solid #e5e7eb!important;border-radius:999px!important;padding:8px 22px!important;font-size:12px!important;font-weight:700!important;color:#374151!important;letter-spacing:.06em!important;text-transform:uppercase!important;line-height:1.2!important;cursor:pointer;box-shadow:none!important;}'
+ '.dste-viewall:hover{background:#e5e7eb!important;}';

function classicThumb(){
  return '<div style="position:absolute;inset:0;background:#1B2A4A;container-type:size;">'
    + '<div style="position:absolute;left:8%;top:30%;width:84%;font:800 7.5cqw/1.05 Arial,sans-serif;color:#fff;">Quarterly Growth Strategy</div>'
    + '<div style="position:absolute;left:8%;top:62%;width:14%;height:1.2%;background:#4A9EFF;"></div>'
    + '<div style="position:absolute;left:8%;top:68%;width:70%;font:400 3cqw/1.3 Arial,sans-serif;color:#C8D8F0;">Classic layouts with theme colours</div></div>';
}
var _built = false, _showAll = false;
function buildHome(){
  var home = document.getElementById('dsp-home'), theme = document.getElementById('dsp-theme-section');
  if (!home || !DSTE.order.length) return false;
  var sec = document.getElementById('dste-sec');
  if (!sec) {
    if (!document.getElementById('dste-css')) { var st = document.createElement('style'); st.id = 'dste-css'; st.textContent = CSS; document.head.appendChild(st); }
    sec = document.createElement('div'); sec.id = 'dste-sec';
    sec.innerHTML = '<div class="dste-hd"><div><h4>Design Template</h4><p class="dste-sub">Every template has its own layouts, colours and typography.</p></div></div><div id="dste-grid"></div><div id="dste-strip"></div><div class="dste-note" id="dste-note"></div><div class="dste-foot"><button type="button" class="dste-viewall" id="dste-more">View All</button></div>';
    if (theme && theme.parentNode) theme.parentNode.insertBefore(sec, theme);
    else { var gb = document.getElementById('dsp-costbar'); if (gb && gb.parentNode) gb.parentNode.insertBefore(sec, gb); else home.appendChild(sec); }
    sec.querySelector('#dste-more').onclick = function(){ _showAll = !_showAll; renderGrid(); };
  }
  renderGrid();
  _built = true;
  return true;
}
function renderGrid(){
  var g = document.getElementById('dste-grid'); if (!g) return;
  var ids = DSTE.reg.classicx ? ['classicx'].concat(DSTE.order.filter(function(x){ return x !== 'classicx'; })) : ['classic'].concat(DSTE.order);   /* editable Classic takes the Classic card's place */
  var cur = DSTE.pick || 'classic';
  var shown = _showAll ? ids : ids.slice(0, 6);
  if (!_showAll && shown.indexOf(cur) < 0) shown[5] = cur;
  var html = '';
  shown.forEach(function(id){
    var T = id === 'classic' ? null : tplFor(id, DSTE.homeTheme), on = id === cur;
    if (T) DSTE.loadFonts(T);
    var th = id === 'classic' ? classicThumb() : DSTE.renderHTML(T, SAMPLE.cover, 'v1', -1, {slides:[SAMPLE.cover]}, {edit:false});
    var nm = id === 'classic' ? 'Classic' : T.name, tg = id === 'classic' ? 'Original layouts + themes' : (T.tag || '');
    html += '<button type="button" class="dste-card' + (on ? ' dste-on' : '') + '" data-id="' + id + '"><div class="dste-th">' + th + '</div><div class="dste-nm"><div>' + u.esc(nm) + '<small>' + u.esc(tg) + '</small></div><span class="dste-ck">✓</span></div></button>';
  });
  g.innerHTML = html;
  Array.prototype.forEach.call(g.querySelectorAll('.dste-card'), function(b){ b.onclick = function(){ setPick(b.getAttribute('data-id')); }; });
  var more = document.getElementById('dste-more'); if (more) more.textContent = _showAll ? 'Show Less' : 'View All';
  renderStrip();
}
function renderStrip(){
  var s = document.getElementById('dste-strip'); if (!s) return;
  var id = pickedTpl();
  if (!id) { s.className = ''; s.innerHTML = ''; return; }
  var T = tplFor(id, DSTE.homeTheme), d = {slides:[SAMPLE.cover, SAMPLE.p3, SAMPLE.stats, SAMPLE.timeline]};
  var h = '<p><b>' + u.esc(T.name) + '</b> — ' + u.esc(T.desc || T.tag || '') + '</p><div class="dste-sl">';
  d.slides.forEach(function(sl, i){ h += '<div>' + DSTE.renderHTML(T, sl, 'v1', -1, d, {edit:false}).replace('class="dste-slide"', 'class="dste-slide" data-n="' + i + '"') + '</div>'; });
  s.innerHTML = h + '</div>'; s.className = 'on';
}
function syncHomeUI(){
  var id = pickedTpl();
  if (id) DSTE.loadFonts(tplFor(id, DSTE.homeTheme));
  var theme = document.getElementById('dsp-theme-section');
  if (theme) theme.style.display = '';
  hookThemeGrids();
  var lay = document.getElementById('dsp-layout');
  if (id && lay) { lay.value = '16:9'; var ll = document.getElementById('dsp-pill-layout-lbl'); if (ll) ll.textContent = '16:9'; }
  var pl = document.getElementById('dsp-pill-layout'); if (pl) { pl.style.opacity = id ? '.45' : ''; pl.style.pointerEvents = id ? 'none' : ''; pl.title = id ? 'Design templates are 16:9' : ''; }
  var note = document.getElementById('dste-note');
  if (note) note.textContent = id ? 'Pick a theme below to recolour this template (colours + heading font), or keep "Template colours". Slides are 16:9.' : '';
  renderGrid();
}
var _homeT = null;
function scheduleHomeUI(){ clearTimeout(_homeT); _homeT = setTimeout(function(){ if (buildHome()) syncHomeUI(); }, 60); }

/* ---------- Theme section: "Template colours" card + theme picks for template decks ---------- */
function tplThemeCard(T, sel){
  var P = T.pal || {};
  return '<div class="dsp-th-card dste-thc' + (sel ? ' dsp-th-sel' : '') + '" data-dste-th="1" title="Keep this template\'s own colours and fonts">'
    + '<div class="dsp-th-swatch" style="background:' + P.bg + ';"><div class="t" style="color:' + (P.title || P.text) + ';font-family:\'' + T.fonts.head + '\',sans-serif;">Title</div>'
    + '<div class="b" style="color:' + P.accent + ';font-family:\'' + T.fonts.body + '\',sans-serif;">' + u.esc(T.name) + '</div></div>'
    + '<div class="dsp-th-name">Template colours<span class="ck">✓</span></div></div>';
}
function gridCtx(g){
  if (g.id === 'dsp-th-grid') { var p = pickedTpl(); return p ? {id:p, key:DSTE.homeTheme} : null; }
  var a = activeId(); return a ? {id:a, key:deckTheme()} : null;
}
function decorateGrid(g){
  if (!g) return;
  var cx = gridCtx(g), mine = g.querySelector('[data-dste-th]');
  if (!cx) {
    if (mine) {
      mine.parentNode.removeChild(mine);
      Array.prototype.forEach.call(g.querySelectorAll('.dste-th-hide'), function(c){ c.classList.remove('dste-th-hide'); });
      var dz = document.getElementById('dsp-design'), cur = dz ? dz.value : '';
      Array.prototype.forEach.call(g.querySelectorAll('[data-th-key]'), function(c){ c.classList.toggle('dsp-th-sel', c.getAttribute('data-th-key') === cur); });
    }
    return;
  }
  var T = DSTE.reg[cx.id];
  if (!mine || mine.getAttribute('data-tpl') !== cx.id) {
    if (mine) mine.parentNode.removeChild(mine);
    var tmp = document.createElement('div'); tmp.innerHTML = tplThemeCard(T, false);
    mine = tmp.firstChild; mine.setAttribute('data-tpl', cx.id);
    g.insertBefore(mine, g.firstChild);
  }
  mine.classList.toggle('dsp-th-sel', !cx.key);
  if (cx.key && g.id === 'dsp-th-grid') {                         /* after a reload keep the cost bar / hidden field on the remembered theme */
    var dz2 = document.getElementById('dsp-design'), kc = g.querySelector('[data-th-key="' + cx.key + '"]');
    if (dz2 && dz2.value !== cx.key && kc && typeof window.dspPillSet === 'function') window.dspPillSet('design', cx.key, kc.getAttribute('data-th-label') || cx.key);
  }
  var keyed = g.querySelectorAll('[data-th-key]');                /* keep the collapsed home grid at 2 full rows */
  Array.prototype.forEach.call(keyed, function(c, i){ c.classList.toggle('dste-th-hide', g.id === 'dsp-th-grid' && keyed.length === 8 && i === 7 && c.getAttribute('data-th-key') !== cx.key); });
  Array.prototype.forEach.call(g.querySelectorAll('[data-th-key]'), function(c){ c.classList.toggle('dsp-th-sel', !!cx.key && c.getAttribute('data-th-key') === cx.key); });
}
/* Theme section: its "View more" button moves to the bottom and reads "View All", like the template section */
function placeThemeMore(){
  var btn = document.getElementById('dsp-th-more-btn'), sec = document.getElementById('dsp-theme-section');
  if (!btn || !sec) return;
  var foot = document.getElementById('dste-th-foot');
  if (!foot) { foot = document.createElement('div'); foot.id = 'dste-th-foot'; foot.className = 'dste-foot'; }
  if (foot.parentNode !== sec || sec.lastElementChild !== foot) sec.appendChild(foot);
  if (btn.parentNode !== foot) foot.appendChild(btn);
  btn.classList.add('dste-viewall');
  var g = document.getElementById('dsp-th-grid'), open = g && g.querySelectorAll('[data-th-key]').length > 8;
  var txt = open ? 'Show Less' : 'View All';
  if (btn.textContent !== txt) btn.textContent = txt;
}
document.addEventListener('click', function(e){ if (e.target && e.target.id === 'dsp-th-more-btn') setTimeout(placeThemeMore, 0); });
function hookThemeGrids(){
  if (!document.getElementById('dste-css')) { var st0 = document.createElement('style'); st0.id = 'dste-css'; st0.textContent = CSS; document.head.appendChild(st0); }
  placeThemeMore();
  ['dsp-th-grid', 'dsp-ed-drawer-row'].forEach(function(id){
    var g = document.getElementById(id); if (!g) return;
    if (!g.__dsteObs) { g.__dsteObs = 1; new MutationObserver(function(){ decorateGrid(g); }).observe(g, {childList:true}); }
    decorateGrid(g);
  });
}
function afterDeckTheme(){
  DSTE.clearFit();
  DSTE.loadFonts(curT());
  refreshAll();
  hookThemeGrids();
}
document.addEventListener('click', function(e){
  var t = e.target; if (!t || !t.closest) return;
  var card = t.closest('[data-dste-th],[data-th-key]'); if (!card) return;
  var key = card.hasAttribute('data-dste-th') ? '' : (card.getAttribute('data-th-key') || '');
  if (card.closest('#dsp-ed-drawer-row') && activeId()) {
    setDeckTheme(key);
    if (!key) { var cl = document.getElementById('dsp-ed-drawer-close'); if (cl) cl.click(); }
    setTimeout(afterDeckTheme, 30);
  } else if (card.closest('#dsp-th-grid')) {
    /* also before any template is picked: the template previews follow the theme straight away, and the template picked afterwards keeps it */
    setHomeTheme(key);
    setTimeout(function(){ var pt = pickedTpl(); if (pt) DSTE.loadFonts(tplFor(pt, DSTE.homeTheme)); renderGrid(); hookThemeGrids(); }, 30);
  }
}, true);

/* ---------- boot ---------- */
function boot(){
  installFetch();
  wrapLiveGen();
  scheduleHomeUI();
  var tries = 0, t = setInterval(function(){
    tries++;
    wrapLiveGen(); wrapAddSlide();
    if (!document.getElementById('dste-sec')) scheduleHomeUI();
    if (tries > 40) clearInterval(t);
  }, 500);
  var ai = activeId(); if (ai) DSTE.loadFonts(curT());
}
if (document.readyState === 'complete') setTimeout(boot, 0); else window.addEventListener('load', function(){ setTimeout(boot, 0); });

/* a template can mark a layout as the default for a slide kind (e.g. Chrome Noir's cover with a round image):
   when the open slide has no layout picked yet, pick that one through Snippet 17 so its image loads from the marketplace */
function applyDefaultLayout(){
  var id = activeId(); if (!id || !window.S || !window.S.data || !window.S.data.slides || !window.S.choices || typeof window.dspVPPick !== 'function') return;
  var i = window.S.idx; if (typeof i !== 'number' || i < 0) return;
  var sl = window.S.data.slides[i]; if (!sl || window.S.choices['slide_' + i]) return;
  var k = DSTE.kindOf(sl); if (k === 'section') k = 'text';
  var def = DSTE.variantsFor(DSTE.reg[id], k).filter(function(v){ return v.o && v.o.def; })[0];
  if (!def || def.id === 'v1') return;
  var t = window.dspToast; window.dspToast = function(){};
  try { window.dspVPPick(i, def.id); } catch(err){} finally { window.dspToast = t; }
}
/* load fonts whenever a template deck opens (generate / load draft) */
var _lastActive = null;
setInterval(function(){ wrapAddSlide(); hookThemeGrids(); applyDefaultLayout(); var a = activeId(); if (a && a + '|' + deckTheme() !== _lastActive) { _lastActive = a + '|' + deckTheme(); DSTE.loadFonts(curT()); } }, 800);
})();

<?php return ob_get_clean();
}

} // DS_TPL_ENGINE
