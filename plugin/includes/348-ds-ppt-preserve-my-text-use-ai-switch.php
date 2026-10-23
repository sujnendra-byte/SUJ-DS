<?php
/**
 * Code Snippets export — DS PPT Preserve My Text - Use AI switch
 *
 * Snippet ID : 1185
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-ppt, free-plan
 * Modified   : 2026-10-12
 * Load order : 348 of 348
 */
/**
 * DS PPT Preserve My Text - "Use AI" switch
 * Run: Everywhere
 *
 * Adds a "Use AI" switch next to "Use Sample" while Preserve My Text is selected.
 *
 *   Paid users (anyone who is not on the free plan): ON by default.
 *     ON  = exactly what happens today: AI arranges the user's own words into slides.
 *     OFF = no AI call at all: the text is split into slides by its headings, "Slide 1:" markers,
 *           bullets and line breaks (the same rules the free plan uses; an attached file follows its
 *           own headings). The user still reviews the slides, and pays the normal Preserve My Text
 *           price when they press Generate.
 *   Free users (no live paid plan and not enough valid credits for one presentation): always OFF.
 *   Turning it on opens the "Available on paid plans" pop-up, and the server refuses the Preserve
 *   AI requests for them. Someone whose plan ended but who still holds credits counts as paid.
 *
 * How OFF works: the browser answers the two Preserve preview requests itself instead of sending
 * them to the AI endpoints. The review panel and the confirm step (where credits are charged) are
 * the existing ones, unchanged.
 *
 * NEW SNIPPET - edits no other snippet. Deactivate to remove the switch; everything works as before.
 */
if ( ! defined('ABSPATH') ) exit;

/*
 * Free = no paid access: no live paid plan, and not enough valid credits for one presentation
 * (see ds_access_plan() in the credit core). Admins count as paid, except in the free-plan test
 * mode (?ds_free_preview=1).
 */
if ( ! function_exists('ds_uai_is_free') ) {
function ds_uai_is_free( $uid ) {
	$uid = (int) $uid;
	if ( ! $uid ) return true;
	if ( user_can($uid, 'manage_options') ) {
		return function_exists('ds_fp_preview_on') && ds_fp_preview_on();
	}
	if ( function_exists('ds_fp_is_free') && ds_fp_is_free($uid) ) return true;
	if ( function_exists('ds_access_plan') ) return ds_access_plan($uid) === 'free';   /* paid plan, or enough credits after it ended */
	$plan = function_exists('ds_get_user_plan') ? (string) ds_get_user_plan($uid) : (string) get_user_meta($uid, 'ds_subscription_plan', true);
	if ( $plan === '' || $plan === 'free' ) return true;
	$status = function_exists('ds_get_user_plan_status') ? (string) ds_get_user_plan_status($uid) : 'active';
	if ( $status === 'active' ) return false;
	$end = function_exists('ds_get_subscription_end') ? (string) ds_get_subscription_end($uid) : '';
	return ! ( $end && strtotime($end . ' 23:59:59') >= current_time('timestamp') );   /* cancelled but still inside the paid period = paid */
}
}

/* Server side: free accounts never reach the AI that arranges Preserve My Text, even if the browser is tampered with. */
add_filter('rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( $result !== null ) return $result;
	$route = $request->get_route();
	if ( ! in_array($route, array('/ds/v1/preserve-preview', '/ds/v1/doc-preserve-preview', '/ds/v1/preserve-ai-rearrange'), true) ) return $result;
	$uid = get_current_user_id();
	if ( ! $uid ) return $result;
	if ( ds_uai_is_free($uid) ) {
		$msg = 'Use AI is part of the paid plans. Turn Use AI off to build from your own text.';
		return new WP_REST_Response(array('success' => false, 'error' => 'use_ai_paid', 'message' => $msg), 200);
	}
	/* Not enough credits for even the cheapest presentation: stop here, before the AI is called. */
	$need = ds_uai_min_credits();
	$have = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
	if ( $have < $need ) {
		$msg = 'You need at least ' . $need . ' credits to build a presentation. You have ' . $have . '. Please buy credits to continue.';
		return new WP_REST_Response(array('success' => false, 'error' => 'not_enough_credits', 'message' => $msg), 200);
	}
	return $result;
}, -40, 3);

/* Credits needed for the cheapest presentation (same price the confirm step charges for 1 slide). */
if ( ! function_exists('ds_uai_min_credits') ) {
function ds_uai_min_credits() {
	if ( function_exists('ds_access_credit_floor') ) return (int) ds_access_credit_floor();
	$c = function_exists('ds_ppt_credit_cost') ? (int) ds_ppt_credit_cost(1, 'preserve') : 5;
	return $c > 0 ? $c : 5;
}
}

add_action('wp_footer', function () {
	if ( ! is_user_logged_in() ) return;
	global $post;
	if ( ! is_a($post, 'WP_Post') ) return;
	if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
	$uid  = get_current_user_id();
	$free = ds_uai_is_free($uid);
	$have = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
	echo '<script id="ds-useai-cfg">window.DS_UAI_CFG=' . wp_json_encode(array('free' => $free, 'pricing' => esc_url_raw(home_url('/pricing/')), 'minCredits' => ds_uai_min_credits(), 'credits' => $have)) . ';</script>' . "\n";
	?>
<style id="ds-useai-css">
#dsuai{display:inline-flex;align-items:center;gap:8px;margin:0 0 0 14px;padding:4px 6px;border:0;background:transparent;color:#c9d1dd;font:600 14px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;cursor:pointer;text-transform:none;letter-spacing:normal;vertical-align:middle;white-space:nowrap;}
#dsuai:hover{color:#fff;}
#dsuai:focus-visible{outline:2px solid #60a5fa;outline-offset:2px;border-radius:8px;}
#dsuai .dsuai-tr{position:relative;flex:0 0 auto;width:34px;height:20px;border-radius:999px;background:#2a3346;border:1px solid #3a465f;transition:background .15s,border-color .15s;}
#dsuai .dsuai-tr::after{content:"";position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#8b97ad;transition:transform .15s,background .15s;}
#dsuai[aria-checked="true"] .dsuai-tr{background:#2563eb;border-color:#3b82f6;}
#dsuai[aria-checked="true"] .dsuai-tr::after{transform:translateX(14px);background:#fff;}
#dsuai[hidden]{display:none!important;}
</style>
<script id="ds-useai-js">
(function(){
	if (window.DSUAI) return;
	var CFG = window.DS_UAI_CFG || {};
	var S = window.DSUAI = {on: !CFG.free, local: false};

	function $(id){ return document.getElementById(id); }
	function isFree(){ return !!(CFG.free || window.DSFP || (document.body && document.body.classList.contains('dsfp'))); }
	function preserveMode(){ return !!(window.G && window.G.mode === 'preserve'); }
	function clean(s){ return String(s == null ? '' : s).replace(/ /g, ' ').replace(/[​﻿]/g, '').replace(/\s+/g, ' ').trim(); }
	function toast(m){ if (typeof window.dspToast === 'function') window.dspToast(m, 'success', 3500); }

	/* "Available on paid plans" pop-up (same look as the free-plan one, for accounts where that snippet is not loaded) */
	function upgrade(msg, title, cta){
		var old = $('dsuai-up'); if (old) old.remove();
		var o = document.createElement('div'); o.id = 'dsuai-up';
		o.style.cssText = 'position:fixed;inset:0;z-index:2147482500;background:rgba(4,7,13,.78);display:flex;align-items:center;justify-content:center;padding:20px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;';
		o.innerHTML = '<div style="width:min(94vw,440px);background:#141a26;border:1px solid #2a3550;border-radius:16px;padding:26px 26px 22px;color:#e6ecf7;box-shadow:0 24px 70px rgba(0,0,0,.6);">'
			+ '<h3 style="margin:0 0 8px;font-size:19px;font-weight:800;color:#f8fafc;line-height:1.3;"></h3>'
			+ '<p style="margin:0 0 18px;font-size:14px;line-height:1.55;color:#b9c0cf;"></p>'
			+ '<div style="display:flex;gap:10px;"><button type="button" data-a="close" style="flex:1;padding:12px;border-radius:10px;border:0;background:#1e2740;color:#dbe4f3;font:800 13.5px/1 inherit;font-family:inherit;cursor:pointer;text-transform:none;letter-spacing:normal;">Maybe later</button>'
			+ '<a data-a="plans" style="flex:1;display:block;text-align:center;padding:12px;border-radius:10px;background:#f59e0b;color:#1a1204;font:800 13.5px/1 inherit;font-family:inherit;text-decoration:none;">See plans</a></div></div>';
		o.querySelector('h3').textContent = title || String.fromCodePoint(128274) + ' Available on paid plans';
		o.querySelector('p').textContent = msg;
		if (cta) o.querySelector('[data-a="plans"]').textContent = cta;
		o.querySelector('[data-a="plans"]').href = CFG.pricing || '/pricing/';
		o.querySelector('[data-a="close"]').onclick = function(){ o.remove(); };
		o.addEventListener('click', function(e){ if (e.target === o) o.remove(); });
		document.body.appendChild(o);
	}

	/* ---------- the switch ---------- */
	function paint(b){
		var on = S.on && !isFree();
		b.setAttribute('aria-checked', on ? 'true' : 'false');
		b.title = isFree()
			? 'Use AI is part of the paid plans.'
			: on ? 'AI arranges your exact words into slides. Turn off to split your text by its headings and line breaks instead.'
			     : 'AI is off: your text is split into slides by its headings and line breaks.';
	}
	function ensure(){
		var foot = $('dsp-pbox-footer'); if (!foot) return;
		var b = $('dsuai');
		if (!b) {
			b = document.createElement('button');
			b.type = 'button'; b.id = 'dsuai'; b.setAttribute('role', 'switch');
			b.innerHTML = '<span class="dsuai-tr" aria-hidden="true"></span><span class="dsuai-l">Use AI</span>';
			b.addEventListener('click', function(e){
				e.preventDefault(); e.stopPropagation();
				if (isFree()) {
					paint(b);
					var msg = 'Use AI is part of the paid plans. On the free plan your text is split into slides by its headings and line breaks.';
					if (window.DSFP && typeof window.DSFP.upgrade === 'function') window.DSFP.upgrade(msg); else upgrade(msg);
					return;
				}
				S.on = !S.on; paint(b);
				toast(S.on ? 'Use AI is on: AI arranges your words into slides.' : 'Use AI is off: slides follow your headings and line breaks.');
			});
		}
		/* right after "Use Sample"; before the character counter when there is no sample button */
		var sample = foot.querySelector('.dssl-sample'), ch = $('dsp-char');
		if (sample && sample.parentNode === foot) { if (b.previousSibling !== sample) foot.insertBefore(b, sample.nextSibling); }
		else if (ch && ch.parentNode === foot) { if (b.nextSibling !== ch) foot.insertBefore(b, ch); }
		else if (b.parentNode !== foot) foot.appendChild(b);
		var hide = !preserveMode();
		if (b.hidden !== hide) b.hidden = hide;
		paint(b);
		fixNotes();
	}

	/* the review panels say "AI organized ..." - not true when the switch was off */
	function fixNotes(){
		if (!S.local) return;
		var p1 = $('dsp-rv-panel');
		if (p1) {
			var n1 = p1.querySelector('div > div > div:nth-child(2)');
			if (n1 && /AI organized your text/.test(n1.textContent)) n1.textContent = 'Use AI is off, so your slides follow your headings and line breaks, in your own words. Edit, delete a slide, or add/remove points, then confirm.';
		}
		var p2 = $('dsdi-rv');
		if (p2) {
			var n2 = p2.querySelector('div > div > div:nth-child(2)');
			if (n2 && !n2.getAttribute('data-uai') && /Edit, delete a slide/.test(n2.textContent)) {
				n2.setAttribute('data-uai', '1');
				n2.textContent = 'Use AI is off, so your slides follow your headings and line breaks, in your own words. Edit, delete a slide, or add/remove points, then confirm.';
			}
		}
	}

	/* ---------- rule-based splitter: text -> slides (no AI). Same rules as the free plan. ---------- */
	function blocksOf(text){
		var lines = String(text).replace(/\r/g, '').split('\n'), out = [], prevBlank = true, seenAny = false;
		function nextLine(i){ for (var j = i + 1; j < lines.length; j++) if (lines[j].trim()) return lines[j].trim(); return ''; }
		for (var i = 0; i < lines.length; i++) {
			var l = lines[i].trim(), m;
			if (!l) { prevBlank = true; continue; }
			if ((m = l.match(/^(#{1,4})\s+(.+)$/))) { out.push({k: 'h', lvl: Math.min(3, m[1].length), t: clean(m[2].replace(/[*_]+/g, ''))}); }
			else if ((m = l.match(/^(?:[-*•●▪◦►✓]|\d{1,2}[.)])\s+(.+)$/))) { out.push({k: 'li', t: clean(m[1].replace(/\*\*/g, ''))}); }
			else {
				var words = l.split(/\s+/).length, nx = nextLine(i), bare = l.replace(/\*\*/g, ''), head = false;
				if (words <= 9 && bare.length <= 70 && nx) {
					if (/:$/.test(bare) && prevBlank) head = true;
					else if (!/[.!?,;:]$/.test(bare) && (prevBlank || !seenAny) && bare.indexOf(':') < 0) head = true;
				}
				if (head) out.push({k: 'h', lvl: seenAny ? 2 : 1, t: clean(bare.replace(/:$/, ''))});
				else if (words >= 26) out.push({k: 'p', t: clean(bare)});
				else out.push({k: 'li', t: clean(bare)});
			}
			prevBlank = false; seenAny = true;
		}
		if (out.length > 2 && out[0].k === 'h' && out[1].k === 'h' && out[2].k === 'h') out[1] = {k: 'sub', t: out[1].t};
		return out;
	}
	function firstWords(t){
		var m = String(t).match(/^[^.!?]{8,80}[.!?]/);
		if (m) return clean(m[0].replace(/[.!?]$/, ''));
		var w = String(t).split(/\s+/);
		return w.length <= 9 ? clean(t) : clean(w.slice(0, 8).join(' ')) + '…';
	}
	function splitText(text, max){
		var t = String(text).replace(/\r/g, ''), DI = window.DS_DOCIMPORT;
		var marks = t.match(/(?:^|\n)[ \t]*Slide\s+\d+\s*[:\-–—.]/gi) || [];
		if (marks.length >= 2 && window.dspPreserveParse) {          /* the author wrote "Slide 1: ..." - follow it exactly */
			return window.dspPreserveParse(t.replace(/(^|\n)([ \t]*Slide\s+\d+)\s*[\-–—.]\s*/gi, '$1$2: ')) || [];
		}
		if (!DI || !DI.sections || !DI.heuristic || !DI.toDeck) return window.dspPreserveParse ? (window.dspPreserveParse(t) || []) : [];
		var blocks = blocksOf(t);
		if (!blocks.length) return [];
		var title = blocks[0].k === 'h' ? blocks[0].t : firstWords(blocks[0].t);
		var secs = DI.sections(blocks, title);
		return DI.toDeck(DI.heuristic(secs, max, title), secs);
	}
	function tidy(slides){
		var out = (slides || []).filter(function(s){ return s && (clean(s.title) || (s.content && s.content.length)); }).slice(0, 20).map(function(s){
			var o = {}; for (var k in s) if (k !== 'image_data' && k !== '_img') o[k] = s[k];
			o.title = clean(o.title); o.subtitle = clean(o.subtitle); o.content = (o.content || []).map(clean).filter(Boolean);
			return o;
		});
		/* "Title" + one short line under it: that line is the cover's subtitle, not a slide of its own */
		var a = out[0], b = out[1];
		if (a && b && !a.content.length && !a.subtitle && b.title === a.title && b.content.length === 1 && b.content[0].split(/\s+/).length <= 14) {
			a.subtitle = b.content[0];
			out.splice(1, 1);
		}
		return out;
	}
	function cost(n){ return Math.max(window.DS_MIN_CREDITS || 5, n * (window.DS_CREDITS_PER_SLIDE || 1)); }
	function reply(obj){ return Promise.resolve(new Response(JSON.stringify(obj), {status: 200, headers: {'Content-Type': 'application/json'}})); }

	/* answer a Preserve preview request in the browser, without AI */
	function local(opts, kind){
		var body = {};
		try { body = JSON.parse(opts && typeof opts.body === 'string' ? opts.body : '{}') || {}; } catch(e){ body = {}; }
		/* a file is attached: the doc-import snippet then follows the document's own headings */
		if (kind === 'doc' && body.sections && body.sections.length) { S.local = true; return reply({success: false, message: 'Use AI is off'}); }
		var text = String(body.text || '');
		var max = parseInt(body.num_slides_hint || body.num_slides, 10) || 20;
		max = Math.max(1, Math.min(20, max));
		var slides = [];
		try { slides = tidy(splitText(text, max)); } catch(e){ slides = []; }
		if (!slides.length) return reply({success: false, error: 'empty', message: 'No slides could be made from this text. Add a few lines, or turn Use AI on.'});
		S.local = true;
		return reply({success: true, ppt_data: {title: slides[0].title || 'Presentation', slides: slides}, cost: cost(slides.length), method: 'rules', translated: false});
	}

	var _fetch = window.fetch;
	if (_fetch) window.fetch = function(url, opts){
		try {
			if (preserveMode() && !(S.on && !isFree())) {
				var us = typeof url === 'string' ? url : (url && url.url) || '';
				try { us = decodeURIComponent(us); } catch(e){}
				if (/\/ds\/v1\/preserve-preview(\?|&|$)/.test(us)) return local(opts, 'pp');
				if (/\/ds\/v1\/doc-preserve-preview(\?|&|$)/.test(us)) return local(opts, 'doc');
				if (/\/ds\/v1\/preserve-ai-rearrange(\?|&|$)/.test(us)) return reply({error: 'ai_off', message: 'Use AI is off, so your text is formatted without AI.'});
			}
			if (S.on && !isFree()) S.local = false;
		} catch(err){}
		return _fetch.apply(this, arguments);
	};

	/* ---------- "Build from My Content" without enough credits: stop before the AI runs ---------- */
	function credits(){
		var n = parseInt(window.G && window.G.credits, 10);      /* every generation flow keeps G.credits up to date */
		if (isNaN(n)) n = parseInt(CFG.credits, 10);
		return isNaN(n) ? 0 : n;
	}
	document.addEventListener('click', function(e){
		var t = e.target;
		if (!t || !t.closest || !t.closest('#dsp-genbtn')) return;
		if (!preserveMode() || window.DSFP) return;               /* the free plan builds its free presentations without credits */
		var need = parseInt(CFG.minCredits, 10) || 5, have = credits();
		if (have >= need) return;
		e.preventDefault(); e.stopImmediatePropagation();
		upgrade('You need at least ' + need + ' credits to build a presentation. You have ' + have + '. Buy credits to continue.', 'Not enough credits', 'Buy credits');
	}, true);

	setInterval(ensure, 700);
	if (document.readyState !== 'loading') ensure(); else document.addEventListener('DOMContentLoaded', ensure);
})();
</script>
	<?php
}, 1500);
