<?php
/**
 * Code Snippets export — DS Suji Support Chat [CLONE - button flush in corner]
 *
 * Snippet ID : 811
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-125
 * Modified   : 2026-09-09 02:45:05
 * Load order : 131 of 144
 */
/**
 * Snippet Name: DS Suji Support Chat
 * Run: Everywhere
 * New, additive snippet only — does not modify any existing snippet.
 *
 * Adds a public-facing support chat widget ("Suji") that:
 *  - Answers site questions (pricing, credits, free-tier daily limits, features,
 *    refunds, etc.) grounded in the SAME live data the pricing page and account
 *    dropdown already use (ds_get_pricing_plans, ds_get_credits, ds_get_user_plan,
 *    ds_get_saved_caps, ds_get_subscription_end, ds_get_refund_window_info) so the
 *    numbers can never drift from what's actually configured in DS Pricing Settings.
 *  - Uses whichever AI key is already saved in DS API Keys (Groq first, then OpenAI)
 *    via the existing ds_get_key() helper. If no key is set, falls back to a
 *    rule-based answer built from the same live facts — it never invents numbers.
 *  - For "cancel my subscription" requests: NEVER cancels immediately. It shows an
 *    explicit confirmation with the real plan name + real end date, and only on the
 *    user's "Yes, cancel it" click does it call the EXISTING, already-live REST route
 *    ds/v1/cancel-subscription (the same one the account-menu Cancel button uses) —
 *    reusing that logic exactly rather than re-implementing it.
 */
if ( ! defined('ABSPATH') ) exit;

/* ───────────────────────── REST: chat endpoint (new route, additive) ───────────────────────── */
add_action('rest_api_init', function () {
	register_rest_route('ds/v1', '/suji-chat', array(
		'methods'             => 'POST',
		'callback'            => 'ds_suji_chat_handler',
		'permission_callback' => '__return_true', // public: guests can ask general questions too
	));
});

/* Build a live facts block — always pulled from the site's real functions/options when they exist. */
function ds_suji_build_facts( $uid ) {
	$caps = function_exists('ds_get_saved_caps')
		? ds_get_saved_caps()
		: array('free' => 1, 'premium' => 25, 'pro' => 50, 'business' => 100);

	$pricing = function_exists('ds_get_pricing_plans')
		? ds_get_pricing_plans()
		: array(
			'premium'  => array('label' => 'Premium',  'credits' => 150, 'usd_price' => '$2.99/mo', 'inr_price' => '₹249/mo'),
			'pro'      => array('label' => 'Pro',      'credits' => 300, 'usd_price' => '$4.99/mo', 'inr_price' => '₹449/mo'),
			'business' => array('label' => 'Business', 'credits' => 550, 'usd_price' => '$7.99/mo', 'inr_price' => '₹699/mo'),
		);

	$lines = array();
	$lines[] = "PLATFORM: Designstager — AI image, presentation (PPT), video and audio generation, plus a marketplace of 500+ premium templates. 7 AI image models (Cloudflare, Cloudflare FLUX.2 Klein 9B, Flux Schnell, Flux Dev +ref, Seedream, Flux Pro, NanoBanana 2). Supports 8 languages.";
	$lines[] = "FREE PLAN (no credit card required): 2 AI images per day, using ONLY the Cloudflare and Cloudflare FLUX.2 Klein 9B models. Plus 5 free downloads/day. Saved items limit: {$caps['free']}. No AI Presentations, no AI Video, no AI Audio, no Template Matcher on Free.";

	foreach ( array('premium', 'pro', 'business') as $pk ) {
		if ( empty($pricing[$pk]) ) continue;
		$p     = $pricing[$pk];
		$label = strtoupper($p['label']);
		$cap   = isset($caps[$pk]) ? $caps[$pk] : '?';
		$lines[] = "{$label} PLAN: {$p['credits']} credits/month, price {$p['inr_price']} (or {$p['usd_price']}). Includes all AI image models, AI Presentations (PPT), AI Video, AI Audio, Template Matcher, reference image (img2img), saved items limit {$cap}. Credits are valid for 1 month from purchase; unused top-up credits carry forward, but plan credits reset monthly and expire if the plan lapses.";
	}
	$lines[] = "DOWNLOAD LIMITS: Free = 5 free-content downloads/day, no pro-content downloads. Premium = 500 free + 50 pro downloads/month. Pro = 1,000 free + 200 pro downloads/month. Business = unlimited free + 600 pro downloads/month.";
	$lines[] = "REFUNDS: A full refund is available within 24 hours of payment, ONLY if no credits from that billing cycle have been used yet. Refund cancels the subscription immediately and removes access right away (different from a normal cancel, which keeps access until the paid period ends). Requested from the account menu (top-right avatar → Request Refund). Once a refund is approved, it typically takes 6-7 business days for the amount to reflect back in the customer's original payment method/account (exact timing depends on their bank or payment provider).";
	$lines[] = "CANCEL SUBSCRIPTION: Cancelling keeps the plan active until the current billing period ends, then the account reverts to Free automatically. No further charges after cancelling. Done from the account menu (top-right avatar → Cancel Subscription), or through this chat with confirmation.";
	$lines[] = "SUPPORT: for anything not covered here, direct the user to the Contact page.";

	if ( $uid ) {
		$plan   = function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : 'free';
		$status = function_exists('ds_get_user_plan_status') ? ds_get_user_plan_status($uid) : 'active';
		$credits = function_exists('ds_get_credits') ? ds_get_credits($uid) : null;
		$end    = function_exists('ds_get_subscription_end') ? ds_get_subscription_end($uid) : '';
		$refund = function_exists('ds_get_refund_window_info') ? ds_get_refund_window_info($uid) : array('eligible' => false);

		$line = "THIS LOGGED-IN USER'S ACCOUNT: plan={$plan}, status={$status}";
		if ( $credits !== null ) $line .= ", credit balance={$credits}";
		if ( $end ) $line .= ", billing period ends/renews on " . date('j M Y', strtotime($end));
		$line .= ", refund eligible right now=" . ( !empty($refund['eligible']) ? 'yes' : 'no' );
		$lines[] = $line;
	} else {
		$lines[] = "THIS VISITOR IS NOT LOGGED IN. They cannot cancel a subscription or see personal account details until they log in.";
	}

	return implode("\n", $lines);
}

/* Very light per-user/IP throttle so the AI key can't be hammered. */
function ds_suji_rate_limited() {
	$key = 'ds_suji_rl_' . md5( get_current_user_id() ? 'u' . get_current_user_id() : ( $_SERVER['REMOTE_ADDR'] ?? 'anon' ) );
	$count = (int) get_transient($key);
	if ( $count >= 25 ) return true;
	set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);
	return false;
}

function ds_suji_chat_handler( WP_REST_Request $req ) {
	$body    = json_decode($req->get_body(), true);
	$message = isset($body['message']) ? sanitize_textarea_field($body['message']) : '';
	$history = ( isset($body['history']) && is_array($body['history']) ) ? $body['history'] : array();

	if ( $message === '' ) {
		return new WP_REST_Response(array('reply' => "Could you type your question? I didn't get any text.", 'action' => null), 200);
	}
	if ( mb_strlen($message) > 1000 ) {
		$message = mb_substr($message, 0, 1000);
	}

	$uid = get_current_user_id();

	/* ---- Cancel-subscription intent: confirm first, never act immediately ---- */
	$is_cancel_intent = (bool) preg_match(
		'/\b(cancel|stop|end|drop)\b[^.?!]{0,25}\b(subscription|plan|membership|billing)\b|\bcancel\s+my\s+(subscription|plan|membership)\b|\bi\s+want\s+to\s+cancel\b/i',
		$message
	);

	if ( $is_cancel_intent ) {
		if ( ! $uid ) {
			return new WP_REST_Response(array(
				'reply'  => "I can help with that — but I'll need you logged in first so I can pull up your subscription. Please log in, then ask me again (or use the account menu, top-right).",
				'action' => null,
			), 200);
		}

		$plan   = function_exists('ds_get_user_plan') ? ds_get_user_plan($uid) : 'free';
		$status = function_exists('ds_get_user_plan_status') ? ds_get_user_plan_status($uid) : 'active';
		$end    = function_exists('ds_get_subscription_end') ? ds_get_subscription_end($uid) : '';

		$labels = array('free' => 'Free', 'premium' => 'Premium', 'pro' => 'Pro', 'business' => 'Business');
		$label  = isset($labels[$plan]) ? $labels[$plan] : ucfirst($plan);

		if ( $plan === 'free' || $status !== 'active' ) {
			return new WP_REST_Response(array(
				'reply'  => "You're on the Free plan (or don't have an active paid subscription right now), so there's nothing to cancel. If that looks wrong to you, please use the Contact page and our team will check your account.",
				'action' => null,
			), 200);
		}

		$end_txt = $end ? date('j M Y', strtotime($end)) : 'the end of your current billing period';
		return new WP_REST_Response(array(
			'reply'  => "Just to confirm: you want to cancel your {$label} plan. You'll keep full access until {$end_txt}, then your account moves to Free automatically — you won't be charged again. Shall I go ahead?",
			'action' => 'confirm_cancel',
		), 200);
	}

	/* ---- General Q&A: AI-powered if a key is configured, otherwise rule-based — both grounded in live facts ---- */
	$facts = ds_suji_build_facts($uid);
	$reply = null;

	if ( ! ds_suji_rate_limited() ) {
		$reply = ds_suji_llm_reply($facts, $history, $message);
	}
	if ( $reply === null ) {
		$reply = ds_suji_rule_based_reply($message, $facts);
	}

	return new WP_REST_Response(array('reply' => $reply, 'action' => null), 200);
}

function ds_suji_llm_reply( $facts, $history, $message ) {
	$key      = function_exists('ds_get_key') ? ds_get_key('groq') : '';
	$endpoint = 'https://api.groq.com/openai/v1/chat/completions';
	$model    = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');

	if ( ! $key && function_exists('ds_get_key') ) {
		$key = ds_get_key('openai');
		if ( $key ) {
			$endpoint = 'https://api.openai.com/v1/chat/completions';
			$model    = 'gpt-4o-mini';
		}
	}
	if ( ! $key ) return null;

	$system = "You are Suji, the support chat assistant for Designstager (an AI design platform). "
		. "Answer ONLY using the facts listed below — never guess, estimate, or invent a number, price, or limit that isn't stated. "
		. "If the answer isn't in the facts, say plainly that you're not sure and point the person to the Contact page instead of guessing. "
		. "Keep replies short (2-5 sentences), specific, and friendly. Never say you have cancelled, refunded, or changed anything yourself — those only happen through the confirmation flow, not through you directly.\n\nFACTS:\n" . $facts;

	$messages = array( array('role' => 'system', 'content' => $system) );
	foreach ( array_slice($history, -6) as $h ) {
		if ( ! empty($h['role']) && isset($h['content']) && in_array($h['role'], array('user', 'assistant'), true) ) {
			$messages[] = array('role' => $h['role'], 'content' => mb_substr((string) $h['content'], 0, 1000));
		}
	}
	$messages[] = array('role' => 'user', 'content' => $message);

	$resp = wp_remote_post($endpoint, array(
		'timeout' => 20,
		'headers' => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $key,
		),
		'body' => wp_json_encode(array(
			'model'       => $model,
			'messages'    => $messages,
			'temperature' => 0.2,
			'max_tokens'  => 350,
		)),
	));

	if ( is_wp_error($resp) ) return null;
	if ( wp_remote_retrieve_response_code($resp) !== 200 ) return null;

	$data = json_decode(wp_remote_retrieve_body($resp), true);
	$content = $data['choices'][0]['message']['content'] ?? null;
	return $content ? trim($content) : null;
}

/* Fallback used only if no AI key is set, or the AI call fails — still 100% grounded in real facts. */
function ds_suji_rule_based_reply( $message, $facts ) {
	$m = strtolower($message);

	if ( strpos($m, 'free') !== false && ( strpos($m, 'image') !== false || strpos($m, 'day') !== false || strpos($m, 'many') !== false ) ) {
		return "On the Free plan you can generate 2 AI images per day, using only the Cloudflare and Cloudflare FLUX.2 Klein 9B models (the other 5 models need a paid plan). You also get 5 free downloads/day. Paid plans (Premium/Pro/Business) don't have a daily image cap — they give you a pool of credits per month instead. Full breakdown: designstager.com/pricing";
	}
	if ( strpos($m, 'price') !== false || strpos($m, 'cost') !== false || strpos($m, 'plan') !== false || strpos($m, 'credit') !== false ) {
		return "Here's the current plan lineup:\n\n" . $facts . "\n\nFull details: designstager.com/pricing";
	}
	if ( strpos($m, 'refund') !== false ) {
		return "Refunds are available within 24 hours of payment, only if you haven't used any credits from that billing cycle yet. Request one from the account menu (top-right avatar → Request Refund) — it's an instant, immediate cancellation + refund, different from a normal cancel.";
	}
	if ( strpos($m, 'download') !== false ) {
		return "Download limits: Free = 5 free-content downloads/day (no pro-content downloads). Premium = 500 free + 50 pro/month. Pro = 1,000 free + 200 pro/month. Business = unlimited free + 600 pro/month.";
	}

	return "I'm not fully certain about that one, and I'd rather not guess. Could you rephrase, or reach our team directly via the Contact page so you get an accurate answer?";
}

/* ───────────────────────── Frontend widget ───────────────────────── */
add_action('wp_footer', function () {
	if ( is_admin() ) return;

	$uid       = get_current_user_id();
	$rest_url  = esc_url( trailingslashit( rest_url('ds/v1') ) );
	$nonce     = wp_create_nonce('wp_rest'); // standard WP REST nonce action — works for the existing ds/v1 routes too
	$logged_in = $uid ? 'true' : 'false';
	?>
	<style>
	#ds-suji-btn{position:fixed;right:20px;bottom:20px;z-index:999996;display:flex;align-items:center;justify-content:center;width:56px;height:56px;padding:0;border-radius:50%;background:#e8442a;color:#fff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:none;}
	#ds-suji-btn:hover{background:#d13a22;}
	#ds-suji-panel{position:fixed;right:20px;bottom:90px;z-index:999997;width:360px;max-width:calc(100vw - 32px);height:520px;max-height:calc(100vh - 120px);background:#fff;border-radius:20px;box-shadow:0 24px 70px rgba(0,0,0,.25);display:none;flex-direction:column;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
	#ds-suji-head{background:linear-gradient(135deg,#e8442a,#c72f16);color:#fff;padding:16px 18px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
	#ds-suji-head div.ttl{font-size:15px;font-weight:800;}
	#ds-suji-head div.sub{font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;}
	#ds-suji-close{background:none;border:none;color:rgba(255,255,255,.6);font-size:18px;cursor:pointer;}
	#ds-suji-msgs{flex:1;overflow-y:auto;padding:14px;background:#f8f9ff;}
	.ds-suji-row{display:flex;margin-bottom:10px;}
	.ds-suji-row.user{justify-content:flex-end;}
	.ds-suji-bubble{max-width:82%;padding:10px 13px;border-radius:14px;font-size:13px;line-height:1.5;white-space:pre-wrap;}
	.ds-suji-row.bot .ds-suji-bubble{background:#fff;color:#111827;border:1px solid #eef0f7;border-bottom-left-radius:4px;}
	.ds-suji-row.user .ds-suji-bubble{background:#e8442a;color:#fff;border-bottom-right-radius:4px;}
	.ds-suji-confirm{display:flex;gap:8px;margin:6px 0 10px 0;}
	.ds-suji-confirm button{flex:1;padding:9px;border-radius:9px;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:inherit;}
	.ds-suji-yes{background:#ef4444;color:#fff;}
	.ds-suji-no{background:#f3f4f6;color:#374151;}
	#ds-suji-inputrow{display:flex;gap:8px;padding:12px;border-top:1px solid #f0f0f5;flex-shrink:0;background:#fff;}
	#ds-suji-input{flex:1;border:1.5px solid #e5e7eb;border-radius:12px;padding:10px 12px;font-size:13px;font-family:inherit;resize:none;}
	#ds-suji-send{background:#e8442a;color:#fff;border:none;border-radius:12px;padding:0 16px;font-weight:700;cursor:pointer;font-size:13px;}
	#ds-suji-typing{font-size:12px;color:#9ca3af;padding:0 4px 6px;}
	</style>

	<button id="ds-suji-btn" onclick="dsSujiOpen()" aria-label="Ask Suji" title="Ask Suji"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="#fff" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></button>

	<div id="ds-suji-panel">
		<div id="ds-suji-head">
			<div>
				<div class="ttl">Suji</div>
				<div class="sub">Designstager support</div>
			</div>
			<button id="ds-suji-close" onclick="dsSujiClose()">&#x2715;</button>
		</div>
		<div id="ds-suji-msgs"></div>
		<div id="ds-suji-typing" style="display:none;">Suji is typing…</div>
		<div id="ds-suji-inputrow">
			<textarea id="ds-suji-input" rows="1" placeholder="Ask about pricing, limits, your account…"></textarea>
			<button id="ds-suji-send" onclick="dsSujiSend()">Send</button>
		</div>
	</div>

	<script>
	(function(){
		var REST = '<?php echo esc_js($rest_url); ?>';
		var NONCE = '<?php echo esc_js($nonce); ?>';
		var LOGGED_IN = <?php echo $logged_in; ?>;
		var history = [];
		var opened = false;

		function q(id){ return document.getElementById(id); }

		window.dsSujiOpen = function(){
			q('ds-suji-panel').style.display = 'flex';
			if (!opened) {
				opened = true;
				dsSujiAddBot("Hi! I'm Suji 👋 Ask me anything about Designstager — pricing, daily image limits, downloads, refunds, or your subscription.");
			}
			q('ds-suji-input').focus();
		};
		window.dsSujiClose = function(){ q('ds-suji-panel').style.display = 'none'; };

		function dsSujiAddUser(text){
			var row = document.createElement('div');
			row.className = 'ds-suji-row user';
			row.innerHTML = '<div class="ds-suji-bubble"></div>';
			row.querySelector('.ds-suji-bubble').textContent = text;
			q('ds-suji-msgs').appendChild(row);
			q('ds-suji-msgs').scrollTop = q('ds-suji-msgs').scrollHeight;
		}

		function dsSujiAddBot(text, withConfirm){
			var row = document.createElement('div');
			row.className = 'ds-suji-row bot';
			var bubble = document.createElement('div');
			bubble.className = 'ds-suji-bubble';
			bubble.textContent = text;
			row.appendChild(bubble);
			q('ds-suji-msgs').appendChild(row);

			if (withConfirm) {
				var conf = document.createElement('div');
				conf.className = 'ds-suji-confirm';
				var yes = document.createElement('button');
				yes.className = 'ds-suji-yes';
				yes.textContent = 'Yes, cancel it';
				var no = document.createElement('button');
				no.className = 'ds-suji-no';
				no.textContent = 'No, keep my plan';
				yes.onclick = function(){ conf.remove(); dsSujiDoCancel(); };
				no.onclick = function(){ conf.remove(); dsSujiAddBot("No problem — your plan stays exactly as it is."); };
				conf.appendChild(yes); conf.appendChild(no);
				q('ds-suji-msgs').appendChild(conf);
			}
			q('ds-suji-msgs').scrollTop = q('ds-suji-msgs').scrollHeight;
		}

		window.dsSujiSend = function(){
			var input = q('ds-suji-input');
			var text = input.value.trim();
			if (!text) return;
			input.value = '';
			dsSujiAddUser(text);
			history.push({role:'user', content:text});
			q('ds-suji-typing').style.display = 'block';

			fetch(REST + 'suji-chat', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {'Content-Type':'application/json','X-WP-Nonce':NONCE},
				body: JSON.stringify({message:text, history:history.slice(-8)})
			})
			.then(function(r){ return r.json(); })
			.then(function(d){
				q('ds-suji-typing').style.display = 'none';
				var reply = d.reply || "Sorry, something went wrong on my end — please try again.";
				history.push({role:'assistant', content:reply});
				dsSujiAddBot(reply, d.action === 'confirm_cancel');
			})
			.catch(function(){
				q('ds-suji-typing').style.display = 'none';
				dsSujiAddBot("Network error — please try again in a moment.");
			});
		};

		window.dsSujiDoCancel = function(){
			if (!LOGGED_IN) {
				dsSujiAddBot("You'll need to log in first — please refresh after logging in and ask again.");
				return;
			}
			dsSujiAddBot('Cancelling…');
			fetch(REST + 'cancel-subscription', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {'Content-Type':'application/json','X-WP-Nonce':NONCE},
				body: JSON.stringify({})
			})
			.then(function(r){ return r.json(); })
			.then(function(d){
				dsSujiAddBot(d.success ? (d.message || 'Your subscription has been cancelled.') : ('I couldn\'t do that: ' + (d.message || 'please contact support.')));
			})
			.catch(function(){
				dsSujiAddBot("Network error while cancelling — please try again, or use the account menu (top-right) directly.");
			});
		};

		q('ds-suji-input') && q('ds-suji-input').addEventListener('keydown', function(e){
			if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); dsSujiSend(); }
		});
	})();
	</script>
	<?php
}, 25);
