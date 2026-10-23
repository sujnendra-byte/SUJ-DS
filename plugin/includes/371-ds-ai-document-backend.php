<?php
/**
 * Code Snippets export — DS AI Document — Backend
 *
 * Snippet ID : 1256
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-10-23 07:37:05
 * Load order : 371 of 374
 */
/**
 * DS AI Document — Backend (generate, writing tools, save/load)
 *
 * Routes (logged-in users, or the Word add-in's token):
 *   POST ds/v1/doc-start    plan a document, charge credits (same price per page as a slide)
 *   POST ds/v1/doc-section  write one section of a planned document
 *   POST ds/v1/doc-finish   finish; refunds credits for any part that could not be written
 *   POST ds/v1/doc-assist   AI writing tools (rewrite, shorten, expand, tone, translate, ...)
 *   GET  ds/v1/my-docs      list saved documents
 *   POST ds/v1/save-doc     save (create or update)
 *   POST ds/v1/load-doc     open one
 *   POST ds/v1/delete-doc   delete one
 * Settings: Settings → DS AI Document.
 * Uses the site's existing credit functions (ds_get_credits, ds_deduct_credits, ds_add_credits,
 * ds_ppt_credit_cost) and Groq key (ds_get_api_keys) — the same ones AI Presentation uses.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'DS_AI_Doc' ) ) :

final class DS_AI_Doc {
	const OPT     = 'ds_doc_settings';
	const CPT     = 'ds_ai_doc';
	const JOB_TTL = 7200;
	const BLOCKS  = 'h1,h2,h3,p,ul,ol,check,quote,callout,stats,table,timeline,cols,kv,image,chart,pb,toc,lh,title,addr,sign,inv,todo,week,cal,sigs,r_head,r_sec,r_entry,r_skills,s_kicker,s_title,s_sub,s_text,s_cta,s_details,s_menu,s_tickets,s_cert';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function settings() {
		$s = get_option( self::OPT, array() );
		return wp_parse_args( is_array( $s ) ? $s : array(), array(
			'enabled'     => 1,
			'assist_cost' => 1,
			'model'       => '',
			'max_pages'   => 20,
			'max_saved'   => 300,
		) );
	}

	public static function register_cpt() {
		register_post_type( self::CPT, array(
			'label'           => 'AI Documents',
			'public'          => false,
			'show_ui'         => false,
			'show_in_rest'    => false,
			'supports'        => array( 'title', 'author' ),
			'capability_type' => 'post',
			'rewrite'         => false,
			'query_var'       => false,
		) );
	}

	public static function routes() {
		$li = function () { return is_user_logged_in(); };
		$r  = array(
			array( '/doc-start', 'POST', 'route_start' ),
			array( '/doc-section', 'POST', 'route_section' ),
			array( '/doc-finish', 'POST', 'route_finish' ),
			array( '/doc-assist', 'POST', 'route_assist' ),
			array( '/my-docs', 'GET', 'route_list' ),
			array( '/save-doc', 'POST', 'route_save' ),
			array( '/load-doc', 'POST', 'route_load' ),
			array( '/delete-doc', 'POST', 'route_delete' ),
			array( '/doc-cost', 'GET', 'route_cost' ),
		);
		foreach ( $r as $x ) {
			register_rest_route( 'ds/v1', $x[0], array( 'methods' => $x[1], 'permission_callback' => $li, 'callback' => array( __CLASS__, $x[2] ) ) );
		}
	}

	/* ---------------- credits (same functions and prices as AI Presentation) ---------------- */

	public static function balance( $uid ) {
		return function_exists( 'ds_get_credits' ) ? (float) ds_get_credits( $uid ) : (float) get_user_meta( $uid, 'ds_ai_credits', true );
	}
	public static function cost( $pages, $mode = 'ai' ) {
		$pages = max( 1, (int) $pages );
		if ( function_exists( 'ds_ppt_credit_cost' ) ) {
			return (int) ds_ppt_credit_cost( $pages, 'preserve' === $mode ? 'preserve' : 'ai' );
		}
		return max( 5, $pages );
	}
	private static function charge( $uid, $amount ) {
		if ( $amount <= 0 ) {
			return true;
		}
		if ( function_exists( 'ds_deduct_credits' ) ) {
			return (bool) ds_deduct_credits( $uid, $amount );
		}
		$c = (float) get_user_meta( $uid, 'ds_ai_credits', true );
		if ( $c < $amount ) {
			return false;
		}
		update_user_meta( $uid, 'ds_ai_credits', $c - $amount );
		return true;
	}
	private static function refund( $uid, $amount ) {
		if ( $amount <= 0 ) {
			return;
		}
		if ( function_exists( 'ds_add_credits' ) ) {
			ds_add_credits( $uid, $amount );
		} else {
			update_user_meta( $uid, 'ds_ai_credits', (float) get_user_meta( $uid, 'ds_ai_credits', true ) + $amount );
		}
	}
	private static function err( $code, $msg, $status = 400, $extra = array() ) {
		return new WP_REST_Response( array_merge( array( 'success' => false, 'error' => $code, 'message' => $msg ), $extra ), $status );
	}

	public static function route_cost( WP_REST_Request $req ) {
		$pages = max( 1, min( (int) self::settings()['max_pages'], (int) $req->get_param( 'pages' ) ) );
		return rest_ensure_response( array( 'pages' => $pages, 'cost' => self::cost( $pages, (string) $req->get_param( 'mode' ) ), 'assist_cost' => (int) self::settings()['assist_cost'], 'credits' => self::balance( get_current_user_id() ) ) );
	}

	/* ---------------- AI ---------------- */

	private static function model() {
		$m = trim( (string) self::settings()['model'] );
		return $m ? $m : get_option( 'ds_groq_ppt_model', 'llama-3.3-70b-versatile' );
	}

	/** Calls the AI and returns decoded JSON (array) or WP_Error. One retry on bad JSON. */
	public static function ai_json( $system, $user, $max_tokens = 7000, $temp = 0.7 ) {
		$pre = apply_filters( 'ds_doc_ai_response', null, $system, $user ); // tests / other providers
		if ( null !== $pre ) {
			return is_array( $pre ) ? $pre : json_decode( (string) $pre, true );
		}
		$keys = function_exists( 'ds_get_api_keys' ) ? ds_get_api_keys() : array();
		$key  = isset( $keys['groq'] ) ? $keys['groq'] : '';
		if ( ! $key ) {
			return new WP_Error( 'ds_doc_no_key', 'The AI service is not set up on this site yet.' );
		}
		$last = null;
		for ( $try = 0; $try < 2; $try++ ) {
			$resp = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', array(
				'timeout' => 90,
				'headers' => array( 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'model'           => self::model(),
					'messages'        => array( array( 'role' => 'system', 'content' => $system ), array( 'role' => 'user', 'content' => $user ) ),
					'temperature'     => $try ? 0.4 : $temp,
					'max_tokens'      => $max_tokens,
					'response_format' => array( 'type' => 'json_object' ),
				) ),
			) );
			if ( is_wp_error( $resp ) ) {
				$last = $resp;
				continue;
			}
			$code = wp_remote_retrieve_response_code( $resp );
			$data = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( 200 !== (int) $code ) {
				$msg  = isset( $data['error']['message'] ) ? $data['error']['message'] : 'HTTP ' . $code;
				$last = new WP_Error( 'ds_doc_ai_http', 'The AI service is busy. Please try again. (' . sanitize_text_field( $msg ) . ')' );
				if ( 429 === (int) $code ) {
					sleep( 3 );
				}
				continue;
			}
			$content = isset( $data['choices'][0]['message']['content'] ) ? $data['choices'][0]['message']['content'] : '';
			$content = preg_replace( '/^```(?:json)?\s*|\s*```$/i', '', trim( $content ) );
			$json    = json_decode( $content, true );
			if ( is_array( $json ) ) {
				return $json;
			}
			$last = new WP_Error( 'ds_doc_ai_json', 'The AI returned an unreadable answer. Please try again.' );
		}
		return $last ? $last : new WP_Error( 'ds_doc_ai', 'The AI service did not answer. Please try again.' );
	}

	private static $TYPE_NAMES = array(
		'report' => 'business report', 'proposal' => 'business proposal', 'business_plan' => 'business plan', 'case_study' => 'case study', 'company_profile' => 'company profile (about the organisation, mission, services, how it works, contact)', 'annual_report' => 'annual report (leadership message, year highlights, financial summary, outlook)',
		'whitepaper' => 'white paper', 'sow' => 'statement of work', 'press' => 'press release', 'newsletter' => 'newsletter', 'ebook' => 'eBook / practical guide',
		'research' => 'research paper', 'essay' => 'article / essay', 'student_report' => 'student project report', 'lesson' => 'lesson plan with teaching notes', 'study_guide' => 'study guide (key ideas, key terms, practice questions, revision tips)',
		'resume' => 'resume / CV', 'cover_letter' => 'cover letter', 'professional_bio' => 'professional biography (third person, short and polished)', 'job_description' => 'job description (role summary, responsibilities, requirements, benefits, how to apply)', 'letter' => 'business letter', 'formal_letter' => 'formal letter', 'email_draft' => 'professional email (to, cc, subject, short clear body)', 'memo' => 'office memo', 'agenda' => 'meeting agenda',
		'minutes' => 'meeting minutes', 'todo' => 'to-do list', 'project_plan' => 'project plan (overview, goals, scope, timeline, team, risks)', 'project_timeline' => 'project timeline with milestones', 'budget_plan' => 'budget plan (totals, budget table by area and period, approval note)', 'financial_report' => 'financial report (summary, income statement, balance sheet highlights, outlook; only numbers the user gave)', 'quote' => 'price quote / estimate', 'flyer' => 'two-sided event or promotional flyer (front: headline and offer; back: details)', 'invitation' => 'invitation',
		'menu' => 'restaurant or event menu', 'card' => 'folded greeting or thank-you card (front, short quote, message inside)',
		'brochure' => 'tri-fold brochure (cover, about, contact, then three inside panels: services, how it works, why choose us)', 'poster' => 'event or promotional poster (headline, short line, key details)',
		'social_post' => 'social media post (very short: small line, headline of under 8 words, one line, call to action)', 'infographic' => 'infographic (headline, three key figures, a simple chart, short tips)', 'business_card' => 'business card (name, job title, company, contact lines; use [placeholders] for any detail not given)',
	);
	private static $FIXED = array( 'press', 'resume', 'cover_letter', 'professional_bio', 'letter', 'formal_letter', 'email_draft', 'memo', 'agenda', 'minutes', 'todo', 'quote', 'flyer', 'invitation', 'menu', 'card', 'brochure', 'poster', 'social_post', 'infographic', 'business_card' );

	private static function blocks_guide() {
		return 'Blocks (JSON objects, field "t" is the type):
{"t":"h2","text":"Subheading"} {"t":"h3","text":"Small heading"}
{"t":"p","text":"Paragraph. Use **bold** for key phrases and *italic* sparingly."}
{"t":"ul","items":["point","point"]} {"t":"ol","items":["step","step"]}
{"t":"quote","text":"A short memorable line","by":"Who said it"}
{"t":"callout","tone":"key|tip|info|warn","title":"Short title","text":"1-2 sentences"}
{"t":"stats","items":[{"v":"38%","l":"What it measures"}]}  (2-4 items; ONLY numbers the user gave - otherwise do not use this block)
{"t":"table","head":["Col","Col","Col"],"rows":[["a","b","c"]]}  (2-5 columns, 2-8 rows)
{"t":"timeline","items":[{"d":"Week 1","title":"Step","text":"What happens"}]}  (3-6 items)
{"t":"cols","items":[{"title":"Option A","text":"..."},{"title":"Option B","text":"..."}]}  (2-3 items)
{"t":"kv","rows":[["Label","Value"]]}
{"t":"image","q":"3-5 English words describing a stock photo","cap":"Caption"}
{"t":"chart","kind":"column|bar|line|area|pie|doughnut","title":"Short chart title","labels":["2023","2024","2025"],"series":[{"name":"Revenue","values":[12,18,25]}],"unit":"%","cap":"One line explaining the chart"}
  Use a chart ONLY when the user gave the numbers for it (never make numbers up): 3-8 labels, numbers only in values (no units), 1-3 series (pie/doughnut: 1 series).';
	}

	/** The AI must not make up facts about the user's own organisation, people or numbers. */
	private static function facts_rule() {
		return 'ACCURACY RULES (very important): Use only facts the user gave you, or general knowledge that is widely known and certain. NEVER invent specific facts about the user\'s organisation, product, place or people: no made-up names of people, founding years, dates, addresses, phone numbers, statistics, percentages, prices, bed or staff or customer counts, awards, partners, quotes or testimonials. When the document needs such a detail and the user did not give it, write a short placeholder in square brackets in the document language (for example [Year founded], [Founder name], [Number of beds]) so the user can fill it in. Prefer explaining what the organisation does, its services, values and plans in general terms over inventing details.';
	}

	private static function lang_rule( $lang ) {
		return 'Write ALL text in ' . $lang . ' (headings, paragraphs, table cells, captions). Keep JSON keys and the "t" values in English. Image "q" values must be in English.';
	}

	/* ---------------- generate ---------------- */

	public static function route_start( WP_REST_Request $req ) {
		$uid = get_current_user_id();
		$s   = self::settings();
		if ( empty( $s['enabled'] ) ) {
			return self::err( 'disabled', 'AI Document is turned off right now.', 503 );
		}
		$p        = $req->get_json_params();
		$p        = is_array( $p ) ? $p : $req->get_params();
		$type     = sanitize_key( isset( $p['type'] ) ? $p['type'] : 'report' );
		$type     = isset( self::$TYPE_NAMES[ $type ] ) ? $type : 'report';
		$topic    = trim( wp_strip_all_tags( (string) ( isset( $p['topic'] ) ? $p['topic'] : '' ) ) );
		$lang     = sanitize_text_field( isset( $p['language'] ) ? $p['language'] : 'English' );
		$tone     = sanitize_text_field( isset( $p['tone'] ) ? $p['tone'] : 'Professional' );
		$dir      = trim( wp_strip_all_tags( (string) ( isset( $p['directives'] ) ? $p['directives'] : '' ) ) );
		$mode     = 'preserve' === ( isset( $p['mode'] ) ? $p['mode'] : '' ) ? 'preserve' : 'ai';
		$source   = trim( wp_strip_all_tags( (string) ( isset( $p['source_text'] ) ? $p['source_text'] : '' ) ) );
		$fixed    = in_array( $type, self::$FIXED, true );
		$pages    = $fixed ? 1 : max( 2, min( (int) $s['max_pages'], (int) ( isset( $p['pages'] ) ? $p['pages'] : 5 ) ) );
		$example  = isset( $p['example'] ) ? wp_json_encode( $p['example'] ) : '';
		$topic    = mb_substr( $topic, 0, 3000 );
		$dir      = mb_substr( $dir, 0, 800 );
		$source   = mb_substr( $source, 0, 60000 );

		if ( 'preserve' === $mode ) {
			if ( mb_strlen( $source ) < 40 ) {
				return self::err( 'no_text', 'Paste or upload the text you want to format.' );
			}
			if ( ! $fixed ) {
				$pages = max( 1, min( (int) $s['max_pages'], (int) ceil( str_word_count( $source ) / 380 ) ) );
			}
		} elseif ( mb_strlen( $topic ) < 3 ) {
			return self::err( 'no_topic', 'Please describe what the document is about.' );
		}

		$cost = self::cost( $pages, $mode );
		if ( self::balance( $uid ) < $cost ) {
			return self::err( 'credits', 'You need ' . $cost . ' credits for this document.', 402, array( 'cost' => $cost, 'credits_balance' => self::balance( $uid ) ) );
		}
		if ( ! self::charge( $uid, $cost ) ) {
			return self::err( 'credits', 'You need ' . $cost . ' credits for this document.', 402, array( 'cost' => $cost ) );
		}

		$job = array( 'uid' => $uid, 'type' => $type, 'pages' => $pages, 'cost' => $cost, 'mode' => $mode, 'lang' => $lang, 'tone' => $tone, 'topic' => $topic, 'dir' => $dir, 'tries' => array(), 'ok' => array(), 'created' => time() );
		try {
			if ( $fixed ) {
				$out = self::write_fixed( $type, $topic, $lang, $tone, $dir, $example, 'preserve' === $mode ? $source : '' );
				if ( is_wp_error( $out ) ) {
					throw new Exception( $out->get_error_message() );
				}
				$id = self::save_job( $job + array( 'sections' => array(), 'done' => 1 ) );
				return rest_ensure_response( array( 'success' => true, 'job' => $id, 'fixed' => true, 'doc' => $out, 'cost' => $cost, 'credits_balance' => self::balance( $uid ) ) );
			}
			if ( 'preserve' === $mode ) {
				$outline = self::outline_from_text( $source, $lang );
			} else {
				$outline = self::outline( $type, $topic, $pages, $lang, $tone, $dir );
			}
			if ( is_wp_error( $outline ) ) {
				throw new Exception( $outline->get_error_message() );
			}
		} catch ( Exception $e ) {
			self::refund( $uid, $cost );
			return self::err( 'ai', $e->getMessage() . ' Your credits were refunded.', 502, array( 'credits_balance' => self::balance( $uid ) ) );
		}
		$job['sections'] = $outline['sections'];
		$job['outline']  = array( 'title' => $outline['title'], 'subtitle' => $outline['subtitle'] );
		if ( 'preserve' === $mode ) {
			$job['chunks'] = $outline['chunks'];
		}
		$id = self::save_job( $job );
		$public = array_map( function ( $x ) { return array( 'heading' => $x['heading'] ); }, $outline['sections'] );
		return rest_ensure_response( array(
			'success' => true, 'job' => $id, 'fixed' => false, 'cost' => $cost, 'credits_balance' => self::balance( $uid ),
			'outline' => array( 'title' => $outline['title'], 'subtitle' => $outline['subtitle'], 'kicker' => $outline['kicker'], 'cover_q' => $outline['cover_q'], 'sections' => $public ),
		) );
	}

	private static function save_job( $job ) {
		$id = wp_generate_password( 20, false );
		set_transient( 'ds_doc_job_' . $id, $job, self::JOB_TTL );
		return $id;
	}
	private static function get_job( $id, $uid ) {
		$id  = preg_replace( '/[^A-Za-z0-9]/', '', (string) $id );
		$job = $id ? get_transient( 'ds_doc_job_' . $id ) : false;
		return ( is_array( $job ) && (int) $job['uid'] === (int) $uid ) ? $job : null;
	}

	private static function outline( $type, $topic, $pages, $lang, $tone, $dir ) {
		$n   = max( 2, min( 22, $pages - 1 ) );
		$sys = 'You plan professional documents. Reply with JSON only.';
		$usr = 'Plan a ' . self::$TYPE_NAMES[ $type ] . ' about: "' . $topic . '".' . "\n" .
			'Length: about ' . $pages . ' pages (a cover page plus ' . $n . ' sections of roughly one page each). Tone: ' . $tone . '.' . "\n" .
			( $dir ? 'Extra instructions from the user: ' . $dir . "\n" : '' ) .
			self::lang_rule( $lang ) . "\n" . self::facts_rule() . "\n" .
			'Return: {"title":"Document title (max 8 words)","subtitle":"One line under the title","kicker":"Document type label, 1-3 words","cover_q":"English stock photo words for the cover","sections":[{"heading":"Section heading","brief":"What this section covers, 1-2 sentences, specific to the topic","features":["one or two of: stats, table, chart, timeline, callout, quote, cols, image, list"]}]}' . "\n" .
			'Exactly ' . $n . ' sections, in a logical order for this kind of document (for example an executive summary first and next steps or a conclusion last). Make the headings specific, not generic. Give "chart" or "stats" to a section only when the description from the user contains the numbers for it; otherwise use features like list, table, timeline, callout or cols.';
		$j = self::ai_json( $sys, $usr, 2500, 0.7 );
		if ( is_wp_error( $j ) ) {
			return $j;
		}
		$secs = array();
		foreach ( (array) ( isset( $j['sections'] ) ? $j['sections'] : array() ) as $x ) {
			if ( ! is_array( $x ) || empty( $x['heading'] ) ) {
				continue;
			}
			$secs[] = array(
				'heading'  => self::clean( $x['heading'], 140 ),
				'brief'    => self::clean( isset( $x['brief'] ) ? $x['brief'] : '', 500 ),
				'features' => array_slice( array_map( 'sanitize_key', (array) ( isset( $x['features'] ) ? $x['features'] : array() ) ), 0, 3 ),
			);
		}
		if ( ! $secs ) {
			return new WP_Error( 'ds_doc_outline', 'The AI could not plan this document.' );
		}
		return array(
			'title'    => self::clean( isset( $j['title'] ) ? $j['title'] : $topic, 160 ),
			'subtitle' => self::clean( isset( $j['subtitle'] ) ? $j['subtitle'] : '', 240 ),
			'kicker'   => self::clean( isset( $j['kicker'] ) ? $j['kicker'] : '', 60 ),
			'cover_q'  => self::clean( isset( $j['cover_q'] ) ? $j['cover_q'] : '', 80 ),
			'sections' => array_slice( $secs, 0, 24 ),
		);
	}

	private static function outline_from_text( $text, $lang ) {
		$paras  = preg_split( "/\n\s*\n|\r\n\s*\r\n/", $text );
		$chunks = array();
		$cur    = '';
		foreach ( $paras as $pp ) {
			$pp = trim( $pp );
			if ( '' === $pp ) {
				continue;
			}
			if ( mb_strlen( $cur ) + mb_strlen( $pp ) > 3200 && '' !== $cur ) {
				$chunks[] = $cur;
				$cur      = '';
			}
			$cur .= ( $cur ? "\n\n" : '' ) . $pp;
		}
		if ( '' !== $cur ) {
			$chunks[] = $cur;
		}
		$chunks = array_slice( $chunks, 0, 24 );
		$first  = mb_substr( $text, 0, 1200 );
		$j      = self::ai_json( 'Reply with JSON only.', 'Read the start of this document and return {"title":"its title (max 8 words)","subtitle":"one line describing it","kicker":"document type in 1-3 words","cover_q":"English stock photo words"}. Keep the language of the text.' . "\n\n" . $first, 400, 0.3 );
		$j      = is_wp_error( $j ) ? array() : $j;
		$secs   = array();
		foreach ( $chunks as $i => $c ) {
			$secs[] = array( 'heading' => 'Part ' . ( $i + 1 ), 'brief' => '', 'features' => array() );
		}
		return array(
			'title' => self::clean( isset( $j['title'] ) ? $j['title'] : 'Document', 160 ), 'subtitle' => self::clean( isset( $j['subtitle'] ) ? $j['subtitle'] : '', 240 ),
			'kicker' => self::clean( isset( $j['kicker'] ) ? $j['kicker'] : '', 60 ), 'cover_q' => self::clean( isset( $j['cover_q'] ) ? $j['cover_q'] : '', 80 ),
			'sections' => $secs, 'chunks' => $chunks,
		);
	}

	public static function route_section( WP_REST_Request $req ) {
		$uid = get_current_user_id();
		$job = self::get_job( $req->get_param( 'job' ), $uid );
		if ( ! $job ) {
			return self::err( 'job', 'This document session has expired. Please start again.', 404 );
		}
		$i = (int) $req->get_param( 'index' );
		if ( $i < 0 || $i >= count( $job['sections'] ) ) {
			return self::err( 'index', 'Unknown section.' );
		}
		$tries = isset( $job['tries'][ $i ] ) ? (int) $job['tries'][ $i ] : 0;
		if ( $tries >= 2 ) {
			return self::err( 'tries', 'This section could not be written.', 429 );
		}
		$job['tries'][ $i ] = $tries + 1;
		set_transient( 'ds_doc_job_' . preg_replace( '/[^A-Za-z0-9]/', '', (string) $req->get_param( 'job' ) ), $job, self::JOB_TTL );

		$sec   = $job['sections'][ $i ];
		$words = 'preserve' === $job['mode'] ? 0 : 360;
		$sys   = 'You write well-structured, specific, useful document content. Reply with JSON only.';
		if ( 'preserve' === $job['mode'] ) {
			$usr = 'Format the text below into document blocks WITHOUT rewriting it: keep every sentence and its wording. Only split it into headings, paragraphs and lists, and turn clearly tabular parts into tables.' . "\n" .
				'The first block must be {"t":"h1","text":"..."} with a heading taken from or summarising the text (in its language).' . "\n" . self::blocks_guide() . "\n" .
				'Return {"blocks":[...]}' . "\n\nTEXT:\n" . $job['chunks'][ $i ];
		} else {
			$all = array();
			foreach ( $job['sections'] as $k => $x ) {
				$all[] = ( $k + 1 ) . '. ' . $x['heading'];
			}
			$usr = 'Document: a ' . self::$TYPE_NAMES[ $job['type'] ] . ' titled "' . $job['outline']['title'] . '" about "' . $job['topic'] . '". Tone: ' . $job['tone'] . '.' . "\n" .
				( $job['dir'] ? 'User instructions: ' . $job['dir'] . "\n" : '' ) .
				'All sections: ' . implode( '; ', $all ) . "\n" .
				'Write section ' . ( $i + 1 ) . ': "' . $sec['heading'] . '". ' . $sec['brief'] . "\n" .
				'About ' . $words . ' words (one page). Start with {"t":"h1","text":"' . $sec['heading'] . '"}. Mix paragraphs with ' . ( $sec['features'] ? implode( ' and ', $sec['features'] ) : 'one helpful visual block' ) . '. Use one or two h2 subheadings if useful. Be clear and practical: explanations, examples and steps; no filler. Use specific numbers, names and dates only when the user gave them.' . "\n" . self::facts_rule() . "\n" .
				self::lang_rule( $job['lang'] ) . "\n" . self::blocks_guide() . "\n" . 'Return {"blocks":[...]}';
		}
		$j = self::ai_json( $sys, $usr, 'preserve' === $job['mode'] ? 7000 : 5000, 'preserve' === $job['mode'] ? 0.3 : 0.55 );
		if ( is_wp_error( $j ) ) {
			return self::err( 'ai', $j->get_error_message(), 502 );
		}
		$blocks = self::clean_blocks( isset( $j['blocks'] ) ? $j['blocks'] : array() );
		if ( ! $blocks ) {
			return self::err( 'ai', 'The AI returned an empty section.', 502 );
		}
		if ( 'h1' !== $blocks[0]['t'] ) {
			array_unshift( $blocks, array( 't' => 'h1', 'text' => $sec['heading'] ) );
		}
		// Mark this section done on the job as it is NOW: other sections may have finished
		// meanwhile (saving the old copy lost their marks), and if the document was already
		// finished and refunded, this section is not handed out and the job is not re-created.
		$jid = preg_replace( '/[^A-Za-z0-9]/', '', (string) $req->get_param( 'job' ) );
		$now = self::get_job( $jid, $uid );
		if ( ! $now ) {
			return self::err( 'job', 'This document session has ended. Please start again.', 410 );
		}
		$now['ok'][ $i ] = 1;
		set_transient( 'ds_doc_job_' . $jid, $now, self::JOB_TTL );
		return rest_ensure_response( array( 'success' => true, 'index' => $i, 'blocks' => $blocks ) );
	}

	public static function route_finish( WP_REST_Request $req ) {
		$uid = get_current_user_id();
		$id  = preg_replace( '/[^A-Za-z0-9]/', '', (string) $req->get_param( 'job' ) );
		$job = self::get_job( $id, $uid );
		if ( ! $job ) {
			return rest_ensure_response( array( 'success' => true, 'refund' => 0, 'credits_balance' => self::balance( $uid ) ) );
		}
		delete_transient( 'ds_doc_job_' . $id );
		$refund = 0;
		// A document is settled once: a second finish (sent at the same moment) refunds nothing.
		if ( function_exists( 'ds_rzp_claim_once' ) && ! ds_rzp_claim_once( 'docfin:' . $id ) ) {
			return rest_ensure_response( array( 'success' => true, 'refund' => 0, 'credits_balance' => self::balance( $uid ) ) );
		}
		$total  = count( $job['sections'] );
		if ( $total ) {
			$ok = count( $job['ok'] );
			if ( $ok < $total ) {
				$got    = $ok ? max( 1, (int) round( $job['pages'] * $ok / $total ) ) : 0;
				$refund = $got ? max( 0, $job['cost'] - self::cost( $got, $job['mode'] ) ) : $job['cost'];
				self::refund( $uid, $refund );
			}
		}
		return rest_ensure_response( array( 'success' => true, 'refund' => $refund, 'credits_balance' => self::balance( $uid ) ) );
	}

	private static function write_fixed( $type, $topic, $lang, $tone, $dir, $example, $source ) {
		$sys = 'You write ready-to-use documents. Reply with JSON only.';
		$usr = 'Write a ' . self::$TYPE_NAMES[ $type ] . ( $topic ? ' for: "' . $topic . '"' : '' ) . '. Tone: ' . $tone . '.' . "\n" .
			( $dir ? 'User instructions: ' . $dir . "\n" : '' ) .
			( $source ? "Use this text as the content, keeping its wording where possible:\n" . mb_substr( $source, 0, 6000 ) . "\n" : '' ) .
			self::lang_rule( $lang ) . "\n" .
			'Use exactly the same JSON structure as this example (same block types and fields, keep every "pg" and "zn" value as it is because they place the text on the right page and panel, keep text short enough to fit the design, new content that fits the request; replace every sample value; use only details the user gave, and placeholders in square brackets like [Your Name], [Date] or [Amount] for any name, date, number or contact detail the user did not give):' . "\n" . self::facts_rule() . "\n" .
			mb_substr( $example, 0, 7000 ) . "\n" .
			'Return {"title":"...","subtitle":"...","blocks":[...]}';
		$j = self::ai_json( $sys, $usr, 4500, 0.7 );
		if ( is_wp_error( $j ) ) {
			return $j;
		}
		$blocks = self::clean_blocks( isset( $j['blocks'] ) ? $j['blocks'] : array() );
		if ( ! $blocks ) {
			return new WP_Error( 'ds_doc_fixed', 'The AI could not write this document.' );
		}
		return array( 'title' => self::clean( isset( $j['title'] ) ? $j['title'] : '', 160 ), 'subtitle' => self::clean( isset( $j['subtitle'] ) ? $j['subtitle'] : '', 240 ), 'blocks' => $blocks );
	}

	/* ---------------- sanitising AI output and saved documents ---------------- */

	public static function clean( $v, $max = 4000 ) {
		if ( is_array( $v ) || is_object( $v ) ) {
			return '';
		}
		$v = wp_strip_all_tags( (string) $v );
		$v = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v );
		return mb_substr( trim( $v ), 0, $max );
	}
	private static function clean_value( $v, $depth = 0 ) {
		if ( $depth > 8 ) {
			return null;
		}
		if ( is_array( $v ) ) {
			$out = array();
			$i   = 0;
			foreach ( $v as $k => $x ) {
				if ( ++$i > 80 ) {
					break;
				}
				$key = is_int( $k ) ? $k : preg_replace( '/[^a-z0-9_]/i', '', (string) $k );
				if ( '' === $key ) {
					continue;
				}
				$cv = self::clean_value( $x, $depth + 1 );
				if ( null !== $cv ) {
					$out[ $key ] = $cv;
				}
			}
			return $out;
		}
		if ( is_bool( $v ) || is_int( $v ) || is_float( $v ) ) {
			return $v;
		}
		if ( null === $v ) {
			return '';
		}
		return self::clean( $v, 4000 );
	}
	public static function clean_blocks( $blocks ) {
		$allowed = array_flip( explode( ',', self::BLOCKS ) );
		$out     = array();
		foreach ( (array) $blocks as $b ) {
			if ( ! is_array( $b ) || empty( $b['t'] ) || ! isset( $allowed[ $b['t'] ] ) ) {
				continue;
			}
			$c = self::clean_value( $b );
			unset( $c['src'] ); // pictures are added by the page, never by the AI
			if ( 'chart' === $c['t'] ) {
				$c = array_merge( array( 't' => 'chart', 'id' => isset( $c['id'] ) ? $c['id'] : '', 'cap' => isset( $c['cap'] ) ? self::clean( $c['cap'], 300 ) : '', 'w' => isset( $c['w'] ) ? self::fnum( $c['w'], 0.3, 1, 1 ) : 1 ), self::clean_chart( $b ) );
			}
			if ( isset( $c['id'] ) && ! preg_match( '/^[a-z0-9]{2,20}$/i', (string) $c['id'] ) ) {
				unset( $c['id'] );
			}
			$out[] = $c;
			if ( count( $out ) >= 400 ) {
				break;
			}
		}
		return $out;
	}

	/* ---------------- free elements on the pages (shapes, text boxes, icons, pictures, charts) ---------------- */

	private static function color( $c, $def = '' ) {
		$c = is_string( $c ) ? trim( $c ) : '';
		return preg_match( '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|@(p|s|a|ink|mute|tint|line|w|k))$/i', $c ) ? $c : $def;
	}
	private static function fnum( $v, $min, $max, $def = 0 ) {
		return is_numeric( $v ) ? max( $min, min( $max, round( (float) $v, 3 ) ) ) : $def;
	}
	private static function paint( $p ) {
		if ( ! is_array( $p ) ) {
			return null;
		}
		$m   = isset( $p['m'] ) && in_array( $p['m'], array( 'none', 'solid', 'grad' ), true ) ? $p['m'] : 'none';
		$out = array( 'm' => $m, 'c' => self::color( isset( $p['c'] ) ? $p['c'] : '', '@p' ), 'o' => self::fnum( isset( $p['o'] ) ? $p['o'] : 1, 0, 1, 1 ), 'w' => self::fnum( isset( $p['w'] ) ? $p['w'] : 2, 0, 60, 2 ),
			'd' => isset( $p['d'] ) && in_array( $p['d'], array( 'solid', 'dash', 'dot' ), true ) ? $p['d'] : 'solid' );
		if ( isset( $p['g'] ) && is_array( $p['g'] ) ) {
			$stops = array();
			foreach ( array_slice( (array) ( isset( $p['g']['s'] ) ? $p['g']['s'] : array() ), 0, 8 ) as $st ) {
				if ( is_array( $st ) && count( $st ) >= 2 ) {
					$stops[] = array( self::fnum( $st[0], 0, 100, 0 ), self::color( $st[1], '#000000' ), self::fnum( isset( $st[2] ) ? $st[2] : 1, 0, 1, 1 ) );
				}
			}
			$out['g'] = array( 't' => isset( $p['g']['t'] ) && 'radial' === $p['g']['t'] ? 'radial' : 'linear', 'a' => self::fnum( isset( $p['g']['a'] ) ? $p['g']['a'] : 0, -360, 360, 0 ), 's' => $stops );
		}
		return $out;
	}
	/** wp_kses writes attribute (and some tag) names in lower case; SVG needs its camelCase names back */
	private static function svg_case( $m ) {
		return str_ireplace( array( 'clippathunits', 'maskunits', 'gradientunits', 'gradienttransform', 'preserveaspectratio', 'viewbox', 'lineargradient', 'radialgradient', '<clippath', '</clippath' ),
			array( 'clipPathUnits', 'maskUnits', 'gradientUnits', 'gradientTransform', 'preserveAspectRatio', 'viewBox', 'linearGradient', 'radialGradient', '<clipPath', '</clipPath' ), $m );
	}
	public static function clean_layers( $layers ) {
		$out    = array();
		$shapes = array( 'rect', 'roundRect', 'ellipse', 'line', 'triangle', 'rtTriangle', 'diamond', 'pentagon', 'hexagon', 'octagon', 'star5', 'rightArrow', 'leftArrow', 'chevron', 'parallelogram' );
		$svg    = array( 'path' => array( 'd' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'stroke-linecap' => 1, 'stroke-linejoin' => 1, 'fill-rule' => 1, 'clip-rule' => 1, 'opacity' => 1, 'transform' => 1, 'fill-opacity' => 1, 'stroke-opacity' => 1, 'stroke-miterlimit' => 1, 'stroke-dasharray' => 1 ),
			'g' => array( 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'stroke-linecap' => 1, 'stroke-linejoin' => 1, 'opacity' => 1, 'transform' => 1, 'fill-rule' => 1 ),
			'circle' => array( 'cx' => 1, 'cy' => 1, 'r' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'opacity' => 1, 'transform' => 1 ),
			'ellipse' => array( 'cx' => 1, 'cy' => 1, 'rx' => 1, 'ry' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'opacity' => 1, 'transform' => 1 ),
			'rect' => array( 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1, 'rx' => 1, 'ry' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'opacity' => 1, 'transform' => 1 ),
			'line' => array( 'x1' => 1, 'y1' => 1, 'x2' => 1, 'y2' => 1, 'stroke' => 1, 'stroke-width' => 1, 'stroke-linecap' => 1, 'opacity' => 1 ),
			'polyline' => array( 'points' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'stroke-linecap' => 1, 'stroke-linejoin' => 1 ),
			'polygon' => array( 'points' => 1, 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'stroke-linejoin' => 1, 'opacity' => 1 ) );
		// colour icons and the design's own shapes also use gradients, clip areas and nested drawings
		$geo = array( 'id' => 1, 'clip-path' => 1, 'mask' => 1, 'fill-opacity' => 1, 'stroke-opacity' => 1, 'stroke-dasharray' => 1, 'stroke-linecap' => 1, 'stroke-linejoin' => 1, 'stroke-miterlimit' => 1, 'fill-rule' => 1, 'clip-rule' => 1, 'opacity' => 1, 'transform' => 1, 'data-f' => 1, 'data-s' => 1, 'style' => 1 );
		foreach ( $svg as $tag => $attrs ) {
			$svg[ $tag ] = array_merge( $attrs, $geo );
		}
		$svg['svg']            = array_merge( $geo, array( 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1, 'viewbox' => 1, 'preserveaspectratio' => 1, 'overflow' => 1 ) );
		$svg['defs']           = array( 'id' => 1 );
		$svg['lineargradient'] = array( 'id' => 1, 'x1' => 1, 'y1' => 1, 'x2' => 1, 'y2' => 1, 'gradientunits' => 1, 'gradienttransform' => 1, 'href' => 1 );
		$svg['radialgradient'] = array( 'id' => 1, 'cx' => 1, 'cy' => 1, 'r' => 1, 'fx' => 1, 'fy' => 1, 'gradientunits' => 1, 'gradienttransform' => 1, 'href' => 1 );
		$svg['stop']           = array( 'offset' => 1, 'stop-color' => 1, 'stop-opacity' => 1, 'style' => 1 );
		$svg['clippath']       = array( 'id' => 1, 'clippathunits' => 1, 'transform' => 1 );
		$svg['mask']           = array( 'id' => 1, 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1, 'maskunits' => 1 );
		$svg['use']            = array_merge( $geo, array( 'href' => 1, 'x' => 1, 'y' => 1, 'width' => 1, 'height' => 1 ) );
		$svg['text']           = array_merge( $geo, array( 'x' => 1, 'y' => 1, 'font-size' => 1, 'font-family' => 1, 'font-weight' => 1, 'text-anchor' => 1, 'letter-spacing' => 1 ) );
		foreach ( array_slice( (array) $layers, 0, 300 ) as $L ) {
			if ( ! is_array( $L ) || empty( $L['k'] ) || ! in_array( $L['k'], array( 'shape', 'text', 'icon', 'image', 'chart', 'vec' ), true ) ) {
				continue;
			}
			$c = array(
				'id' => isset( $L['id'] ) && preg_match( '/^[A-Za-z0-9_-]{1,24}$/', (string) $L['id'] ) ? (string) $L['id'] : 'l' . wp_generate_password( 8, false ),
				'k'  => $L['k'],
				'pg' => isset( $L['pg'] ) && ( 'c' === $L['pg'] || 'm' === $L['pg'] ) ? $L['pg'] : (int) self::fnum( isset( $L['pg'] ) ? $L['pg'] : 0, 0, 500, 0 ),
				'x'  => self::fnum( isset( $L['x'] ) ? $L['x'] : 0, -2000, 4000, 0 ),
				'y'  => self::fnum( isset( $L['y'] ) ? $L['y'] : 0, -2000, 4000, 0 ),
				'w'  => self::fnum( isset( $L['w'] ) ? $L['w'] : 100, 1, 4000, 100 ),
				'h'  => self::fnum( isset( $L['h'] ) ? $L['h'] : 100, 1, 4000, 100 ),
				'rot' => self::fnum( isset( $L['rot'] ) ? $L['rot'] : 0, -360, 360, 0 ),
				'op' => self::fnum( isset( $L['op'] ) ? $L['op'] : 1, 0, 1, 1 ),
			);
			if ( 'vec' === $L['k'] ) {
				$c['svg'] = self::svg_case( wp_kses( isset( $L['svg'] ) && is_string( $L['svg'] ) ? substr( $L['svg'], 0, 200000 ) : '', $svg ) );
				if ( '' === $c['svg'] ) {
					continue;
				}
				$bx = isset( $L['bx'] ) && is_array( $L['bx'] ) ? array_values( $L['bx'] ) : array();
				if ( 4 === count( $bx ) ) {
					$c['bx'] = array( self::fnum( $bx[0], -5000, 5000, 0 ), self::fnum( $bx[1], -5000, 5000, 0 ), self::fnum( $bx[2], 0.01, 10000, 1 ), self::fnum( $bx[3], 0.01, 10000, 1 ) );
				}
			}
			foreach ( array( 'hide', 'lock', 'behind', 'it', 'ul', 'st', 'caps', 'art' ) as $b ) {
				if ( ! empty( $L[ $b ] ) ) {
					$c[ $b ] = 1;
				}
			}
			foreach ( array( 'r' => array( 0, 2000 ), 'rr' => array( 0, 2000 ), 'fs' => array( 4, 300 ), 'fw' => array( 100, 900 ), 'lh' => array( 0.6, 4 ), 'ls' => array( -10, 60 ), 'pad' => array( 0, 80 ) ) as $k => $rg ) {
				if ( isset( $L[ $k ] ) && is_numeric( $L[ $k ] ) ) {
					$c[ $k ] = self::fnum( $L[ $k ], $rg[0], $rg[1] );
				}
			}
			if ( isset( $L['shape'] ) && in_array( $L['shape'], $shapes, true ) ) {
				$c['shape'] = $L['shape'];
			}
			if ( isset( $L['al'] ) && in_array( $L['al'], array( 'left', 'center', 'right', 'justify' ), true ) ) {
				$c['al'] = $L['al'];
			}
			if ( isset( $L['va'] ) && in_array( $L['va'], array( 'top', 'middle', 'bottom' ), true ) ) {
				$c['va'] = $L['va'];
			}
			if ( isset( $L['ff'] ) && preg_match( '/^[A-Za-z0-9 ]{1,40}$/', (string) $L['ff'] ) ) {
				$c['ff'] = (string) $L['ff'];
			}
			if ( isset( $L['grp'] ) && preg_match( '/^[A-Za-z0-9_-]{1,24}$/', (string) $L['grp'] ) ) {
				$c['grp'] = (string) $L['grp'];
			}
			if ( isset( $L['name'] ) ) {
				$c['name'] = self::clean( $L['name'], 60 );
			}
			if ( isset( $L['tc'] ) ) {
				$c['tc'] = self::color( $L['tc'], '@ink' );
			}
			if ( isset( $L['tx'] ) ) {
				$c['tx'] = self::clean( $L['tx'], 4000 );
			}
			foreach ( array( 'fill', 'ln' ) as $pk ) {
				if ( isset( $L[ $pk ] ) && is_array( $L[ $pk ] ) ) {
					$c[ $pk ] = self::paint( $L[ $pk ] );
				}
			}
			if ( isset( $L['sh'] ) && is_array( $L['sh'] ) ) {
				$c['sh'] = array( 'c' => self::color( isset( $L['sh']['c'] ) ? $L['sh']['c'] : '', '#000000' ), 'o' => self::fnum( isset( $L['sh']['o'] ) ? $L['sh']['o'] : 0.35, 0, 1, 0.35 ),
					'b' => self::fnum( isset( $L['sh']['b'] ) ? $L['sh']['b'] : 8, 0, 200, 8 ), 'd' => self::fnum( isset( $L['sh']['d'] ) ? $L['sh']['d'] : 6, 0, 200, 6 ), 'a' => self::fnum( isset( $L['sh']['a'] ) ? $L['sh']['a'] : 45, -360, 360, 45 ) );
			}
			if ( 'icon' === $L['k'] && isset( $L['ic'] ) && is_array( $L['ic'] ) ) {
				$body    = isset( $L['ic']['b'] ) && is_string( $L['ic']['b'] ) ? substr( $L['ic']['b'], 0, 30000 ) : '';
				$c['ic'] = array( 'n' => isset( $L['ic']['n'] ) && preg_match( '/^[a-z0-9-]{1,40}:[a-z0-9-]{1,80}$/', (string) $L['ic']['n'] ) ? (string) $L['ic']['n'] : '',
					'b' => self::svg_case( wp_kses( $body, $svg ) ), 'vw' => self::fnum( isset( $L['ic']['vw'] ) ? $L['ic']['vw'] : 24, 1, 4096, 24 ), 'vh' => self::fnum( isset( $L['ic']['vh'] ) ? $L['ic']['vh'] : 24, 1, 4096, 24 ) );
			}
			if ( 'image' === $L['k'] ) {
				$src = isset( $L['src'] ) && is_string( $L['src'] ) ? $L['src'] : '';
				if ( preg_match( '#^https://#', $src ) ) {
					$c['src'] = esc_url_raw( $src );
				} elseif ( preg_match( '#^data:image/(png|jpe?g);base64,[A-Za-z0-9+/=]+$#', $src ) && strlen( $src ) < 750000 ) {
					$c['src'] = $src;
				}
				if ( ! empty( $L['pid'] ) ) {
					$c['pid'] = (int) $L['pid'];
					$c['url'] = esc_url_raw( isset( $L['url'] ) ? (string) $L['url'] : '' );
					if ( isset( $c['src'] ) && 0 === strpos( $c['src'], 'data:' ) ) {
						unset( $c['src'] ); // marketplace pictures are fetched again when the document opens
					}
				}
			}
			if ( 'chart' === $L['k'] && isset( $L['ch'] ) && is_array( $L['ch'] ) ) {
				$c['ch'] = self::clean_chart( $L['ch'] );
			}
			$out[] = $c;
		}
		return $out;
	}
	public static function clean_chart( $ch ) {
		$kinds  = array( 'column', 'bar', 'line', 'area', 'pie', 'doughnut' );
		$series = array();
		foreach ( array_slice( (array) ( isset( $ch['series'] ) ? $ch['series'] : array() ), 0, 8 ) as $s ) {
			if ( ! is_array( $s ) ) {
				continue;
			}
			$vals = array();
			foreach ( array_slice( (array) ( isset( $s['values'] ) ? $s['values'] : array() ), 0, 40 ) as $v ) {
				$vals[] = is_numeric( $v ) ? (float) $v : (float) preg_replace( '/[^\d.\-]/', '', (string) $v );
			}
			$series[] = array( 'name' => self::clean( isset( $s['name'] ) ? $s['name'] : '', 60 ), 'values' => $vals, 'c' => self::color( isset( $s['c'] ) ? $s['c'] : '' ) );
		}
		return array(
			'kind'   => isset( $ch['kind'] ) && in_array( $ch['kind'], $kinds, true ) ? $ch['kind'] : 'column',
			'title'  => self::clean( isset( $ch['title'] ) ? $ch['title'] : '', 200 ),
			'labels' => array_map( function ( $l ) { return DS_AI_Doc::clean( $l, 60 ); }, array_slice( (array) ( isset( $ch['labels'] ) ? $ch['labels'] : array() ), 0, 40 ) ),
			'series' => $series,
			'unit'   => self::clean( isset( $ch['unit'] ) ? $ch['unit'] : '', 10 ),
			'legend' => ! isset( $ch['legend'] ) || ! empty( $ch['legend'] ),
			'vals'   => ! isset( $ch['vals'] ) || ! empty( $ch['vals'] ),
		);
	}

	/* ---------------- writing tools ---------------- */

	public static function route_assist( WP_REST_Request $req ) {
		$uid  = get_current_user_id();
		$act  = sanitize_key( (string) $req->get_param( 'action' ) );
		$text = mb_substr( trim( (string) $req->get_param( 'text' ) ), 0, 8000 );
		$lang = sanitize_text_field( (string) ( $req->get_param( 'language' ) ? $req->get_param( 'language' ) : '' ) );
		$tone = sanitize_text_field( (string) ( $req->get_param( 'tone' ) ? $req->get_param( 'tone' ) : 'Professional' ) );
		$ask  = mb_substr( self::clean( (string) $req->get_param( 'instruction' ), 600 ), 0, 600 );
		$acts = array(
			'improve'   => 'Improve the writing: clearer, more concise and more professional. Keep the meaning and language.',
			'rewrite'   => 'Rewrite it in a fresh way with the same meaning. Keep the language.',
			'shorten'   => 'Make it about half as long, keeping the key points. Keep the language.',
			'expand'    => 'Expand it to about twice the length with useful detail and examples. Keep the language.',
			'grammar'   => 'Fix spelling, grammar and punctuation only. Do not change anything else.',
			'tone'      => 'Rewrite it in a ' . $tone . ' tone. Keep the meaning and language.',
			'translate' => 'Translate it into ' . ( $lang ? $lang : 'English' ) . '. Keep formatting.',
			'summarize' => 'Summarise it in 2-4 sentences. Keep the language.',
			'bullets'   => 'Turn it into a short bullet list, one point per line starting with "- ". Keep the language.',
			'continue'  => 'Continue writing after this text with one or two more paragraphs in the same style and language. Return only the new text.',
			'custom'    => $ask,
			'write'     => 'Write the text the user asks for. ' . ( $lang ? 'Write in ' . $lang . '.' : '' ),
		);
		if ( ! isset( $acts[ $act ] ) || ( 'custom' === $act && ! $ask ) ) {
			return self::err( 'action', 'Unknown writing action.' );
		}
		if ( '' === $text && 'write' !== $act ) {
			return self::err( 'text', 'Select some text first.' );
		}
		if ( 'write' === $act && ! $ask ) {
			return self::err( 'text', 'Describe what to write.' );
		}
		$cost = (int) self::settings()['assist_cost'];
		if ( self::balance( $uid ) < $cost || ! self::charge( $uid, $cost ) ) {
			return self::err( 'credits', 'You need ' . $cost . ' credit' . ( 1 === $cost ? '' : 's' ) . ' for this.', 402, array( 'cost' => $cost ) );
		}
		$usr = $acts[ $act ] . ( 'write' === $act ? "\n\nRequest: " . $ask : ( 'custom' === $act ? '' : '' ) ) . "\n" .
			'Keep **bold** and *italic* markers if present. Return {"text":"..."}' . ( 'write' === $act ? '' : "\n\nTEXT:\n" . $text );
		$j = self::ai_json( 'You are a careful writing assistant. Reply with JSON only.', $usr, 3000, 0.6 );
		if ( is_wp_error( $j ) || empty( $j['text'] ) ) {
			self::refund( $uid, $cost );
			return self::err( 'ai', ( is_wp_error( $j ) ? $j->get_error_message() : 'No text came back.' ) . ' Your credits were refunded.', 502 );
		}
		return rest_ensure_response( array( 'success' => true, 'text' => self::clean( $j['text'], 12000 ), 'cost' => $cost, 'credits_balance' => self::balance( $uid ) ) );
	}

	/* ---------------- saved documents ---------------- */

	private static function own_post( $id, $uid ) {
		$p = get_post( (int) $id );
		return ( $p && self::CPT === $p->post_type && (int) $p->post_author === (int) $uid ) ? $p : null;
	}
	public static function route_list( WP_REST_Request $req ) {
		$uid    = get_current_user_id();
		$offset = max( 0, (int) $req->get_param( 'offset' ) );
		$limit  = max( 1, min( 50, (int) ( $req->get_param( 'limit' ) ? $req->get_param( 'limit' ) : 20 ) ) );
		$q      = new WP_Query( array( 'post_type' => self::CPT, 'post_status' => 'private', 'author' => $uid, 'posts_per_page' => $limit, 'offset' => $offset, 'orderby' => 'modified', 'order' => 'DESC', 'no_found_rows' => false ) );
		$items  = array();
		foreach ( $q->posts as $p ) {
			$m       = get_post_meta( $p->ID, '_ds_doc_meta', true );
			$items[] = array_merge( array( 'id' => $p->ID, 'title' => $p->post_title, 'updated' => strtotime( $p->post_modified_gmt . ' UTC' ) ), is_array( $m ) ? $m : array() );
		}
		return rest_ensure_response( array( 'success' => true, 'docs' => $items, 'total' => (int) $q->found_posts, 'next_offset' => $offset + count( $items ) ) );
	}
	public static function route_save( WP_REST_Request $req ) {
		$uid = get_current_user_id();
		$doc = $req->get_param( 'doc' );
		if ( ! is_array( $doc ) ) {
			return self::err( 'doc', 'Nothing to save.' );
		}
		$clean           = self::clean_value( array_diff_key( $doc, array( 'blocks' => 1, 'layers' => 1 ) ) );
		$clean['layers'] = self::clean_layers( isset( $doc['layers'] ) ? $doc['layers'] : array() );
		$clean['blocks'] = self::clean_blocks( isset( $doc['blocks'] ) ? $doc['blocks'] : array() );
		// pictures: keep links and marketplace ids, never large embedded data
		foreach ( $clean['blocks'] as &$b ) {
			if ( 'image' === $b['t'] && isset( $doc['blocks'] ) ) {
				foreach ( $doc['blocks'] as $ob ) {
					if ( is_array( $ob ) && isset( $ob['id'], $b['id'] ) && $ob['id'] === $b['id'] && ! empty( $ob['src'] ) && is_string( $ob['src'] ) ) {
						if ( preg_match( '#^https://#', $ob['src'] ) ) {
							$b['src'] = esc_url_raw( $ob['src'] );
						} elseif ( preg_match( '#^data:image/(png|jpe?g);base64,[A-Za-z0-9+/=]+$#', $ob['src'] ) && strlen( $ob['src'] ) < 750000 ) {
							$b['src'] = $ob['src']; // a picture the user uploaded (resized in the browser)
						}
					}
				}
			}
		}
		unset( $b );
		if ( isset( $clean['cover'] ) && is_array( $clean['cover'] ) ) {
			$cs             = isset( $doc['cover']['src'] ) ? (string) $doc['cover']['src'] : '';
			$clean['cover'] = array( 'pid' => (int) ( isset( $doc['cover']['pid'] ) ? $doc['cover']['pid'] : 0 ), 'url' => esc_url_raw( isset( $doc['cover']['url'] ) ? $doc['cover']['url'] : '' ), 'src' => preg_match( '#^https://#', $cs ) ? esc_url_raw( $cs ) : '' );
		}
		// the logo: a link, or a small picture the user uploaded
		if ( isset( $clean['logo'] ) && is_array( $clean['logo'] ) ) {
			$ls = isset( $doc['logo']['src'] ) ? (string) $doc['logo']['src'] : '';
			if ( preg_match( '#^https://#', $ls ) ) {
				$clean['logo']['src'] = esc_url_raw( $ls );
			} elseif ( preg_match( '#^data:image/(png|jpe?g|webp);base64,[A-Za-z0-9+/=]+$#', $ls ) && strlen( $ls ) < 750000 ) {
				$clean['logo']['src'] = $ls;
			} else {
				$clean['logo']['src'] = '';
			}
			$clean['logo']['url'] = esc_url_raw( isset( $doc['logo']['url'] ) ? (string) $doc['logo']['url'] : '' );
			$l2 = isset( $doc['logo']['src2'] ) ? (string) $doc['logo']['src2'] : '';
			if ( preg_match( '#^https://#', $l2 ) ) {
				$clean['logo']['src2'] = esc_url_raw( $l2 );
			} elseif ( preg_match( '#^data:image/(png|jpe?g|webp);base64,[A-Za-z0-9+/=]+$#', $l2 ) && strlen( $l2 ) < 750000 ) {
				$clean['logo']['src2'] = $l2;
			} else {
				unset( $clean['logo']['src2'] );
			}
		}
		$json = wp_json_encode( $clean );
		if ( strlen( $json ) > 4000000 ) {
			return self::err( 'size', 'This document is too large to save.' );
		}
		$title = self::clean( isset( $clean['title'] ) && $clean['title'] ? $clean['title'] : 'Untitled document', 180 );
		$meta  = array( 'type' => sanitize_key( isset( $clean['type'] ) ? $clean['type'] : '' ), 'tpl' => sanitize_key( isset( $clean['tpl'] ) ? $clean['tpl'] : '' ), 'theme' => sanitize_key( isset( $clean['theme'] ) ? $clean['theme'] : '' ), 'subtitle' => self::clean( isset( $clean['subtitle'] ) ? $clean['subtitle'] : '', 160 ), 'kicker' => self::clean( isset( $clean['kicker'] ) ? $clean['kicker'] : '', 60 ), 'blocks' => count( $clean['blocks'] ) );
		$id    = (int) $req->get_param( 'doc_id' );
		if ( $id ) {
			if ( ! self::own_post( $id, $uid ) ) {
				return self::err( 'doc', 'Document not found.', 404 );
			}
			wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_content' => wp_slash( $json ) ) );
		} else {
			$count = (int) ( new WP_Query( array( 'post_type' => self::CPT, 'post_status' => 'private', 'author' => $uid, 'posts_per_page' => 1, 'fields' => 'ids' ) ) )->found_posts;
			if ( $count >= (int) self::settings()['max_saved'] ) {
				return self::err( 'limit', 'You have reached the limit of saved documents. Delete some to save new ones.' );
			}
			$id = wp_insert_post( array( 'post_type' => self::CPT, 'post_status' => 'private', 'post_author' => $uid, 'post_title' => $title, 'post_content' => wp_slash( $json ) ), true );
			if ( is_wp_error( $id ) ) {
				return self::err( 'save', 'The document could not be saved.', 500 );
			}
		}
		update_post_meta( $id, '_ds_doc_meta', $meta );
		return rest_ensure_response( array( 'success' => true, 'doc_id' => (int) $id ) );
	}
	public static function route_load( WP_REST_Request $req ) {
		$p = self::own_post( $req->get_param( 'doc_id' ), get_current_user_id() );
		if ( ! $p ) {
			return self::err( 'doc', 'Document not found.', 404 );
		}
		$doc = json_decode( $p->post_content, true );
		return rest_ensure_response( array( 'success' => true, 'doc_id' => $p->ID, 'doc' => is_array( $doc ) ? $doc : array() ) );
	}
	public static function route_delete( WP_REST_Request $req ) {
		$p = self::own_post( $req->get_param( 'doc_id' ), get_current_user_id() );
		if ( ! $p ) {
			return self::err( 'doc', 'Document not found.', 404 );
		}
		wp_delete_post( $p->ID, true );
		return rest_ensure_response( array( 'success' => true ) );
	}

	/* ---------------- settings ---------------- */

	public static function admin_menu() {
		add_options_page( 'DS AI Document', 'DS AI Document', 'manage_options', 'ds-ai-document', array( __CLASS__, 'settings_page' ) );
	}
	public static function register_settings() {
		register_setting( 'ds_doc', self::OPT, array( 'sanitize_callback' => function ( $v ) {
			return array(
				'enabled'     => empty( $v['enabled'] ) ? 0 : 1,
				'assist_cost' => max( 0, (int) ( isset( $v['assist_cost'] ) ? $v['assist_cost'] : 1 ) ),
				'model'       => sanitize_text_field( isset( $v['model'] ) ? $v['model'] : '' ),
				'max_pages'   => max( 2, min( 30, (int) ( isset( $v['max_pages'] ) ? $v['max_pages'] : 20 ) ) ),
				'max_saved'   => max( 10, (int) ( isset( $v['max_saved'] ) ? $v['max_saved'] : 300 ) ),
			);
		} ) );
	}
	public static function settings_page() {
		$s = self::settings();
		echo '<div class="wrap"><h1>DS AI Document</h1><form method="post" action="options.php">';
		settings_fields( 'ds_doc' );
		echo '<table class="form-table">';
		echo '<tr><th>Enabled</th><td><label><input type="checkbox" name="' . esc_attr( self::OPT ) . '[enabled]" value="1" ' . checked( $s['enabled'], 1, false ) . '> AI Document is available</label></td></tr>';
		echo '<tr><th>Price per document</th><td>Same as AI Presentation: the price of a deck with the same number of slides as the document has pages (set in your credit settings). For example, 5 pages cost ' . (int) self::cost( 5 ) . ' credits and 10 pages cost ' . (int) self::cost( 10 ) . '.</td></tr>';
		echo '<tr><th>Writing tools price</th><td><input type="number" min="0" name="' . esc_attr( self::OPT ) . '[assist_cost]" value="' . (int) $s['assist_cost'] . '" class="small-text"> credits per use (rewrite, shorten, translate, ...)</td></tr>';
		echo '<tr><th>Maximum pages</th><td><input type="number" min="2" max="30" name="' . esc_attr( self::OPT ) . '[max_pages]" value="' . (int) $s['max_pages'] . '" class="small-text"></td></tr>';
		echo '<tr><th>Saved documents per user</th><td><input type="number" min="10" name="' . esc_attr( self::OPT ) . '[max_saved]" value="' . (int) $s['max_saved'] . '" class="small-text"></td></tr>';
		echo '<tr><th>AI model</th><td><input type="text" name="' . esc_attr( self::OPT ) . '[model]" value="' . esc_attr( $s['model'] ) . '" class="regular-text" placeholder="' . esc_attr( get_option( 'ds_groq_ppt_model', 'llama-3.3-70b-versatile' ) ) . '"><p class="description">Empty = the same Groq model as AI Presentation.</p></td></tr>';
		echo '</table>';
		submit_button();
		echo '</form></div>';
	}
}
DS_AI_Doc::init();
endif;
