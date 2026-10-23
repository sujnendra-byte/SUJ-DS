<?php
/**
 * Code Snippets export — DS AI Instruction Guard — AI follows the prompt + Specific AI Directives
 *
 * Snippet ID : 1190
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-ppt, ai
 * Modified   : 2026-10-13
 * Load order : 352 of 352
 */
/**
 * DS AI Instruction Guard — the AI follows the prompt and "Specific AI Directives"
 * Run: Everywhere
 *
 * Problem (BlueLeaf test deck): the user's directives did reach the AI, but only as one line squeezed
 * into the topic, while the fixed writing rules told the AI the opposite - "invent plausible specifics",
 * "30-45 words per point minimum", "add a key takeaway", "use stats, timeline and quote at least once",
 * at temperature 0.85. So it invented funding, a CEO and a website, wrote 150-word slides and skipped
 * the exact phrase it was asked to show.
 *
 * Now, whenever the user writes directives (or the prompt itself sets hard rules such as "do not invent",
 * "use only", exact titles or a word limit), this snippet writes the deck itself instead of the standard
 * preview, with the user's text as binding requirements:
 *  - brief and directives are sent with their line breaks, at the top, marked as the highest priority;
 *  - requirements are read from the text and checked: slide count, exact titles / subtitles, exact phrases
 *    to show, number of points on a slide, a word limit per slide, "do not invent";
 *  - "do not invent" mode: only the user's facts; no made-up funding, revenue, customers, partners, names,
 *    websites or dates (cover details the user did not give are removed);
 *  - after writing, the deck is checked; anything missed is sent back to the AI once to fix, then exact
 *    titles / subtitles / phrases and the word limit are applied directly;
 *  - lower temperature, and the stronger reasoning model first (same model list as Doc Import).
 * Without directives or hard rules the standard generator runs unchanged. Same response shape, same
 * credit check and rate limit; credits are still only charged on Generate.
 * Endpoints covered: /ds/v1/ai-preview and /ds/v1/doc-ai-preview (prompt + directives + attached file).
 * NEW SNIPPET. Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_aig_words') ) {

	function ds_aig_words( $s ) { $s = trim(preg_replace('/\s+/u', ' ', (string) $s)); return $s === '' ? array() : preg_split('/\s+/u', $s); }
	function ds_aig_wc( $s ) { return count(ds_aig_words($s)); }
	function ds_aig_txt( $s, $max = 4000 ) { $s = wp_strip_all_tags((string) $s); $s = preg_replace("/\r\n?/", "\n", $s); $s = preg_replace("/[ \t]+/u", ' ', $s); return trim(mb_substr($s, 0, $max)); }
	function ds_aig_lc( $s ) { return mb_strtolower(preg_replace('/\s+/u', ' ', (string) $s)); }
	function ds_aig_q() { return '["\x{201C}\x{201D}]'; }

	/* requirements written in the brief / directives */
	function ds_aig_rules( $brief, $instr ) {
		$all = $brief . "\n" . $instr; $q = ds_aig_q(); $r = array('slides' => array(), 'n' => 0, 'limit' => 0, 'strict' => false, 'any' => false);
		if ( preg_match('/\b(do\s*n[o\x{2019}\']?t|never|no)\s+(invent|make\s+up|fabricat|add\s+(any\s+)?(fake|made)|hallucinat)|\buse\s+only\b|\bonly\s+(use|the\s+(information|facts|details))\b|\bno\s+invented\b|\bstick\s+to\s+the\s+(facts|information)\b/iu', $all) ) $r['strict'] = true;
		if ( preg_match('/(?:under|below|less\s+than|fewer\s+than|no\s+more\s+than|at\s+most|max(?:imum)?(?:\s+of)?|within|up\s+to|not\s+exceed(?:ing)?)\s+(\d{1,3})\s+words/iu', $all, $m) ) $r['limit'] = (int) $m[1];
		if ( preg_match('/\b(?:exactly|only|use)\s+(\d{1,2})\s+slides?\b/iu', $instr, $m) || preg_match('/\b(\d{1,2})\s*-?\s*slides?\b/iu', $instr, $m) ) $r['n'] = (int) $m[1];
		$segs = preg_split('/(?=\bslide\s*#?\s*\d{1,2}\b)/iu', $instr);
		foreach ( $segs as $seg ) {
			if ( ! preg_match('/^\s*slide\s*#?\s*(\d{1,2})\b/iu', $seg, $sm) ) continue;
			$k = (int) $sm[1]; if ( $k < 1 || $k > 20 ) continue;
			$S = isset($r['slides'][$k]) ? $r['slides'][$k] : array('phrases' => array());
			if ( preg_match('/\b(?:titled|title(?:d)?\s*(?:is|:|of)?|heading)\s*' . $q . '([^"\x{201C}\x{201D}]{1,120})' . $q . '/iu', $seg, $m) ) $S['title'] = trim($m[1]);
			if ( preg_match('/\bsub-?title\s*(?:is|:|of)?\s*' . $q . '([^"\x{201C}\x{201D}]{1,160})' . $q . '/iu', $seg, $m) ) $S['subtitle'] = trim($m[1]);
			if ( preg_match_all('/\b(?:display|displays|show|shows|showing|include|includes|feature|features|highlight|highlights|state|states|mention|headline)\b[^"\x{201C}\x{201D}\n]{0,40}' . $q . '([^"\x{201C}\x{201D}]{1,120})' . $q . '/iu', $seg, $mm) ) foreach ( $mm[1] as $ph ) { $ph = trim($ph); if ( $ph !== '' && $ph !== ($S['title'] ?? '') && $ph !== ($S['subtitle'] ?? '') ) $S['phrases'][] = $ph; }
			if ( preg_match('/\bexactly\s+(\d{1,2})\s+(?:bullet\s*points?|bullets?|points?|items?|steps?|milestones?|cards?|reasons?|benefits?|features?)\b/iu', $seg, $m) ) $S['points'] = (int) $m[1];
			elseif ( preg_match('/\b(\d{1,2})\s*-?\s*(?:step|stage|phase|point|item|milestone)s?\b/iu', $seg, $m) ) $S['points'] = (int) $m[1];
			if ( preg_match('/\btimeline|roadmap|milestones?\b/iu', $seg) ) $S['type'] = 'timeline';
			elseif ( $k === 1 && preg_match('/\bcover|title\s+slide\b/iu', $seg) ) $S['type'] = 'title';
			$S['text'] = trim(preg_replace('/\s+/u', ' ', $seg));
			$r['slides'][$k] = $S;
		}
		$r['any'] = trim($instr) !== '' || $r['strict'] || $r['limit'] > 0 || ! empty($r['slides']);
		return $r;
	}

	function ds_aig_models() {
		if ( function_exists('ds_di_models') ) return ds_di_models();
		$l = array('openai/gpt-oss-120b', get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile'), 'llama-3.3-70b-versatile');
		return array_values(array_unique($l));
	}
	function ds_aig_llm( $sys, $user, $n ) {
		$keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
		$key = isset($keys['groq']) ? $keys['groq'] : '';
		if ( ! $key ) return new WP_Error('nokey', 'Groq API key missing.');
		$last = null;
		foreach ( ds_aig_models() as $m ) {
			$body = array('model' => $m, 'messages' => array(array('role' => 'system', 'content' => $sys), array('role' => 'user', 'content' => $user)),
				'temperature' => 0.2, 'max_tokens' => (int) min(20000, max(8000, $n * 900)), 'response_format' => array('type' => 'json_object'));
			if ( strpos($m, 'gpt-oss') !== false ) { $body['reasoning_effort'] = 'medium'; $body['max_tokens'] = max($body['max_tokens'], 16000); }
			$resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array('timeout' => 120, 'headers' => array('Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'), 'body' => wp_json_encode($body)));
			if ( is_wp_error($resp) ) { $last = $resp; continue; }
			$code = wp_remote_retrieve_response_code($resp);
			$data = json_decode(wp_remote_retrieve_body($resp), true);
			if ( $code >= 400 ) { $last = new WP_Error('groq', isset($data['error']['message']) ? $data['error']['message'] : 'HTTP ' . $code); continue; }
			$c = (string) ( $data['choices'][0]['message']['content'] ?? '' );
			$c = preg_replace('/<think>.*?<\/think>/s', '', $c);
			$c = preg_replace('/^\x60{3}(?:json)?\s*|\s*\x60{3}$/i', '', trim($c));
			$j = json_decode($c, true);
			if ( ! is_array($j) && preg_match('/\{.*\}/s', $c, $mm) ) $j = json_decode($mm[0], true);
			if ( ( ! is_array($j) || empty($j['slides']) ) && function_exists('ds_ppt_repair_truncated_json') ) $j = ds_ppt_repair_truncated_json($c);
			if ( is_array($j) && ! empty($j['slides']) && is_array($j['slides']) ) { $j['slides'] = array_values($j['slides']); return $j; }
			$last = new WP_Error('parse', 'The AI answer could not be read.');
		}
		return $last ?: new WP_Error('ai', 'The AI is busy. Please try again.');
	}

	function ds_aig_body( $s ) { $t = (string) ( $s['subtitle'] ?? '' ); if ( ! empty($s['content']) && is_array($s['content']) ) $t .= ' ' . implode(' ', array_map('strval', $s['content'])); return $t; }
	function ds_aig_all( $s ) { return ( $s['title'] ?? '' ) . ' ' . ds_aig_body($s); }

	/* what the deck still gets wrong */
	function ds_aig_check( $ppt, $R, $n ) {
		$v = array(); $sl = $ppt['slides'];
		if ( count($sl) !== $n ) $v[] = 'The deck must have exactly ' . $n . ' slides (it has ' . count($sl) . ').';
		foreach ( $R['slides'] as $k => $S ) {
			$s = isset($sl[$k - 1]) ? $sl[$k - 1] : null; if ( ! $s ) continue;
			if ( isset($S['title']) && ds_aig_lc($s['title'] ?? '') !== ds_aig_lc($S['title']) ) $v[] = "Slide $k title must be exactly \"{$S['title']}\".";
			if ( isset($S['subtitle']) && ds_aig_lc($s['subtitle'] ?? '') !== ds_aig_lc($S['subtitle']) ) $v[] = "Slide $k subtitle must be exactly \"{$S['subtitle']}\".";
			foreach ( $S['phrases'] as $ph ) if ( mb_stripos(ds_aig_all($s), $ph) === false ) $v[] = "Slide $k must show the exact text \"$ph\" prominently (as the subtitle, a stat value or a point heading).";
			if ( ! empty($S['points']) && ( $s['type'] ?? '' ) !== 'title' && count((array) ( $s['content'] ?? array() )) !== $S['points'] ) $v[] = "Slide $k must have exactly {$S['points']} points (it has " . count((array) ( $s['content'] ?? array() )) . ').';
			if ( ( $S['type'] ?? '' ) === 'timeline' && ( $s['type'] ?? '' ) !== 'timeline' ) $v[] = "Slide $k must be a timeline slide.";
		}
		if ( $R['limit'] ) foreach ( $sl as $i => $s ) { $w = ds_aig_wc(ds_aig_body($s)) + ds_aig_wc($s['takeaway'] ?? ''); if ( $w > $R['limit'] && ( $s['type'] ?? '' ) !== 'title' ) $v[] = 'Slide ' . ($i + 1) . " body text has $w words; it must be under {$R['limit']} words (subtitle + points + takeaway)."; }
		return $v;
	}

	/* shorten a slide's text to the word limit (keeps every point's heading) */
	function ds_aig_fit( $s, $L ) {
		$s['takeaway'] = '';
		if ( ds_aig_wc(ds_aig_body($s)) < $L ) return $s;
		$sub = ds_aig_words($s['subtitle'] ?? ''); if ( count($sub) > 10 ) $s['subtitle'] = implode(' ', array_slice($sub, 0, 10));
		$items = (array) ( $s['content'] ?? array() ); $n = max(1, count($items));
		$budget = max($n * 3, $L - 1 - ds_aig_wc($s['subtitle'] ?? '')); $per = max(3, (int) floor($budget / $n));
		foreach ( $items as $i => $it ) {
			$it = (string) $it; $p = strpos($it, ':');
			$head = $p !== false && $p < 60 ? trim(substr($it, 0, $p)) : ''; $rest = $p !== false && $p < 60 ? trim(substr($it, $p + 1)) : $it;
			$keep = max(2, $per - ds_aig_wc($head)); $rw = ds_aig_words($rest);
			if ( count($rw) > $keep ) { $rest = rtrim(implode(' ', array_slice($rw, 0, $keep)), ',;:-') ; if ( ! preg_match('/[.!?%]$/u', $rest) ) $rest .= '.'; }
			$items[$i] = $head !== '' ? $head . ': ' . $rest : $rest;
		}
		$s['content'] = $items;
		return $s;
	}

	/* apply what can be applied directly */
	function ds_aig_apply( $ppt, $R, $userText ) {
		$ut = ds_aig_lc($userText);
		foreach ( $ppt['slides'] as $i => $s ) {
			$k = $i + 1; $S = isset($R['slides'][$k]) ? $R['slides'][$k] : null;
			if ( ! is_array($s) ) $s = array();
			$s['content'] = array_values(array_map('strval', array_filter((array) ( $s['content'] ?? array() ), 'is_scalar')));
			if ( $S ) {
				if ( isset($S['title']) ) $s['title'] = $S['title'];
				if ( isset($S['subtitle']) ) $s['subtitle'] = $S['subtitle'];
				foreach ( $S['phrases'] as $ph ) if ( mb_stripos(ds_aig_all($s), $ph) === false ) {
					if ( ! isset($S['subtitle']) ) $s['subtitle'] = $ph;
					else array_unshift($s['content'], $ph);
				}
				if ( ! empty($S['points']) && ( $s['type'] ?? '' ) !== 'title' && count((array) $s['content']) > $S['points'] ) $s['content'] = array_slice($s['content'], 0, $S['points']);
			}
			/* do-not-invent: the cover only keeps details the user actually gave */
			if ( $R['strict'] && ( $s['type'] ?? '' ) === 'title' && ! empty($s['content']) ) {
				$s['content'] = array_values(array_filter((array) $s['content'], function($c) use ($ut){
					$c = (string) $c; $p = strpos($c, ':'); if ( $p === false ) return false;
					$val = ds_aig_lc(trim(substr($c, $p + 1))); return $val !== '' && mb_strpos($ut, $val) !== false;
				}));
			}
			if ( $R['limit'] && ( $s['type'] ?? '' ) !== 'title' ) $s = ds_aig_fit($s, $R['limit']);
			$ppt['slides'][$i] = $s;
		}
		return $ppt;
	}

	function ds_aig_prompt( $brief, $instr, $doc, $file, $n, $lang, $R ) {
		$icons = 'mdi:rocket-launch,mdi:chart-bar,mdi:account-group,mdi:target,mdi:lightbulb,mdi:shield-check,mdi:earth,mdi:star,mdi:check-circle,mdi:flag,mdi:trending-up,mdi:cog,mdi:clock-outline,mdi:heart,mdi:account,mdi:lightning-bolt,mdi:trophy,mdi:book-open,mdi:briefcase,mdi:calendar,mdi:leaf,mdi:cash,mdi:handshake,mdi:brain,mdi:cloud,mdi:lock,mdi:puzzle,mdi:water,mdi:sprout,mdi:chip';
		$L = $R['limit'];
		$p = "Write a {$n}-slide presentation in {$lang}.\n\n";
		$p .= "=== USER BRIEF (binding) ===\n" . ( $brief !== '' ? $brief : '(see the instructions and the document)' ) . "\n\n";
		if ( $instr !== '' ) $p .= "=== USER DIRECTIVES (binding - highest priority, they override every rule below) ===\n{$instr}\n\n";
		if ( $doc !== '' ) $p .= "=== SOURCE DOCUMENT" . ( $file ? " ({$file})" : '' ) . " - build the deck from it; use its facts, names and numbers ===\n{$doc}\n\n";
		$p .= "=== HARD REQUIREMENTS (the result is checked automatically) ===\n";
		$p .= "- Exactly {$n} slides in the slides array.\n";
		foreach ( $R['slides'] as $k => $S ) {
			if ( isset($S['title']) ) $p .= "- Slide {$k}: title exactly \"{$S['title']}\" (same words, same spelling).\n";
			if ( isset($S['subtitle']) ) $p .= "- Slide {$k}: subtitle exactly \"{$S['subtitle']}\".\n";
			foreach ( $S['phrases'] as $ph ) $p .= "- Slide {$k}: show the exact text \"{$ph}\" prominently (as the subtitle, a stat value or the first point heading).\n";
			if ( ! empty($S['points']) ) $p .= "- Slide {$k}: exactly {$S['points']} items in content.\n";
			if ( ( $S['type'] ?? '' ) === 'timeline' ) $p .= "- Slide {$k}: type timeline, items in the order the user gave.\n";
		}
		if ( $L ) $p .= "- WORD LIMIT: on every slide, subtitle + all content items together must stay UNDER {$L} words (the title does not count). Short, plain sentences. Do NOT add a 'takeaway' field.\n";
		if ( $R['strict'] ) {
			$p .= "- FACTS: use ONLY the facts the user gave (brief, directives, document). Do NOT invent revenue, funding, investment amounts, valuations, prices, customers, partners, investors, people's names, job titles, websites, emails, phone numbers, dates, product or technology names, study results or any other figure. Where a slide needs support, explain qualitatively or reuse the user's own numbers.\n";
			$p .= "- Slide 1 content: only 'Company: <name>' / 'Tagline: <...>' items whose values come from the user's text; never add a Presenter, Designation, Date or Website the user did not give.\n";
		} else {
			$p .= "- FACTS: the user's facts are always used exactly and never contradicted. Extra figures may only be general, clearly true context - never presented as the company's own results, funding, customers or partners unless the user gave them.\n";
		}
		$p .= "- Follow the directives' slide order, slide types and wording. Do not add slides, do not merge or split slides, do not rename slides that were given a title.\n\n";
		$p .= "=== SLIDE TYPES (field 'type') ===\n";
		$p .= "title (slide 1 cover: title, subtitle, content = 'Company: ...' style details), bullet (3-6 items), two_column (2), three_column (3), feature_grid (4), five_point/six_point/seven_point/eight_point (5/6/7/8), stats (exactly 3 'VALUE: short label'), timeline (2-6 'YEAR or PHASE: short description'), quote (title = the quote, content[0] = 'Name, Role').\n";
		$p .= "Pick the type that fits each slide and the directives (a milestone list = timeline, one headline number = stats or the subtitle, a closing call to action = bullet or two_column).\n";
		$p .= "Point items are 'Short Heading: explanation'" . ( $L ? " - keep the explanation short enough to respect the word limit" : " - explanation 15-30 words, concrete" ) . ".\n";
		$p .= "Point slides also get an 'icons' array (one per item) from: {$icons}.\n";
		if ( ! $L ) $p .= "Point and stats slides may add 'takeaway': one sentence of 10-18 words.\n";
		$p .= "Every slide except title/quote has a 3-6 word 'title' and an 8-12 word 'subtitle' (unless the directives give them).\n\n";
		$p .= "Return ONLY JSON: {\"title\":\"Deck title\",\"slides\":[{\"type\":\"title\",\"title\":\"...\",\"subtitle\":\"...\",\"content\":[\"Company: ...\"],\"icons\":[]},{\"type\":\"three_column\",\"title\":\"...\",\"subtitle\":\"...\",\"content\":[\"Heading: ...\",\"Heading: ...\",\"Heading: ...\"],\"icons\":[\"mdi:water\",\"mdi:leaf\",\"mdi:chart-bar\"]}]}\n";
		return $p;
	}

	function ds_aig_generate( WP_REST_Request $req, $route ) {
		$uid = get_current_user_id();
		if ( $route === '/ds/v1/doc-ai-preview' ) {
			$brief = ds_aig_txt($req->get_param('prompt'), 4000);
			$instr = ds_aig_txt($req->get_param('instructions'), 3000);
			$doc = mb_substr(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $req->get_param('doc'))), 0, 24000);
			$file = sanitize_text_field((string) $req->get_param('file'));
		} else {
			$topic = ds_aig_txt($req->get_param('topic'), 8000);
			$instr = ''; $doc = ''; $file = '';
			$parts = preg_split('/\s*\|\|\|\s*/u', $topic);
			$brief = trim(array_shift($parts));
			foreach ( $parts as $pt ) {
				if ( preg_match('/^USER INSTRUCTIONS[^:]*:\s*(.*)$/su', $pt, $m) ) $instr = trim($m[1]);
				elseif ( preg_match('/^DOCUMENT TEXT:\s*(.*)$/su', $pt, $m) ) $doc = trim($m[1]);
			}
		}
		$R = ds_aig_rules($brief, $instr);
		if ( ! $R['any'] ) return null; /* nothing binding: the standard generator runs */
		if ( $brief === '' && $doc === '' && $instr === '' ) return null;
		$n = $R['n'] ?: (int) ( $req->get_param('num_slides') ?: $req->get_param('slides') ?: 10 );
		$n = max(1, min(20, $n));
		$lang = sanitize_text_field($req->get_param('language') ?: 'English');
		if ( function_exists('ds_ppt_ai_preview_ratelimit') && ! ds_ppt_ai_preview_ratelimit($uid) ) return rest_ensure_response(array('error' => 'rate_limited', 'message' => 'Please wait a moment before previewing again.'));
		$credits = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
		$cost = function_exists('ds_ppt_credit_cost') ? (int) ds_ppt_credit_cost($n, 'ai') : max(5, $n);
		if ( $credits < $cost ) return rest_ensure_response(array('error' => 'Not enough credits', 'message' => 'You need ' . $cost . ' credits.'));

		$sys = "You are a meticulous presentation writer. The user's brief and directives are binding requirements, not suggestions: follow every one exactly (slide count, order, exact titles and phrases, number of points, word limits, facts). When a general writing habit conflicts with the user's text, the user's text wins. Output ONLY valid JSON, no markdown.";
		$ppt = ds_aig_llm($sys, ds_aig_prompt($brief, $instr, $doc, $file, $n, $lang, $R), $n);
		if ( is_wp_error($ppt) ) return null; /* AI unavailable: fall back to the standard generator */
		$v = ds_aig_check($ppt, $R, $n);
		if ( $v ) { /* one repair round */
			$fix = "Here is a presentation JSON and the requirements it breaks. Return the corrected JSON in the same format: fix every listed problem, keep everything else as it is, and keep following the original brief and directives.\n\n=== USER BRIEF ===\n{$brief}\n\n=== USER DIRECTIVES ===\n{$instr}\n\n=== PROBLEMS ===\n- " . implode("\n- ", $v) . "\n\n=== JSON ===\n" . wp_json_encode($ppt);
			$p2 = ds_aig_llm($sys, $fix, $n);
			if ( ! is_wp_error($p2) && count($p2['slides']) >= min($n, count($ppt['slides'])) ) $ppt = $p2;
		}
		$ppt = ds_aig_apply($ppt, $R, $brief . "\n" . $instr . "\n" . $doc);
		$have = count($ppt['slides']); $eff = min($n, max(1, $have));
		$slides = ds_ppt_ai_sanitize_slides($ppt['slides'], $eff);
		if ( $R['limit'] ) foreach ( $slides as $i => $s ) $slides[$i]['takeaway'] = '';
		foreach ( $R['slides'] as $k => $S ) { if ( isset($slides[$k - 1]) && isset($S['title']) ) $slides[$k - 1]['title'] = sanitize_text_field($S['title']); if ( isset($slides[$k - 1]) && isset($S['subtitle']) ) $slides[$k - 1]['subtitle'] = sanitize_text_field($S['subtitle']); }
		return rest_ensure_response(array(
			'success' => true,
			'ppt_data' => array('title' => sanitize_text_field($ppt['title'] ?? ( $slides[0]['title'] ?? 'Presentation' )), 'slides' => $slides),
			'cost' => $cost,
			'truncated' => false,
			'guided' => true,
			'note' => $have < $n ? ('The AI wrote ' . $have . ' of the ' . $n . ' slides you asked for. Regenerate to try again, or add slides below.') : '',
		));
	}
}

add_filter('rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( $result !== null || ! ( $request instanceof WP_REST_Request ) ) return $result;
	$route = $request->get_route();
	if ( $route !== '/ds/v1/ai-preview' && $route !== '/ds/v1/doc-ai-preview' ) return $result;
	if ( strtoupper($request->get_method()) !== 'POST' || ! is_user_logged_in() || ! function_exists('ds_ppt_ai_sanitize_slides') ) return $result;
	try { $out = ds_aig_generate($request, $route); } catch ( Throwable $e ) { $out = null; }
	return $out === null ? $result : $out;
}, 10, 3);
