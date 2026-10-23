<?php
/**
 * Code Snippets export — DS Marketplace AI Auto-Tagger (Groq Vision)
 *
 * Snippet ID : 775
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-089
 * Modified   : 2026-09-09 02:44:05
 * Load order : 094 of 144
 */
/**
 * Snippet Name: DS Marketplace AI Auto-Tagger (Groq Vision)
 * Run: Everywhere
 *
 * Automatically generates descriptive search tags (dominant colors, subject,
 * style, mood, setting) for Marketplace designs using Groq's vision model,
 * and attaches them as real dlm_download_tag terms on the design. The
 * EXISTING marketplace search already matches both post title/content AND
 * tag names (see "ds-marketplace-shortcode 04/28"), so once these AI tags
 * are attached as real terms, search picks them up automatically — no
 * changes to any other snippet required.
 *
 * Trigger: fires once, automatically, the moment a design's
 * ds_marketplace_status postmeta is set to 'approved' (the same moment it
 * becomes visible in the Marketplace, per "DS Marketplace Admin Approve").
 * Also exposes two admin-only REST endpoints:
 *   POST /wp-json/ds/v1/mkt-ai-tag-one        { download_id, force }
 *   POST /wp-json/ds/v1/mkt-ai-backfill-tags  { batch }  — call repeatedly
 *        until more_remaining:false to tag existing approved designs.
 *
 * Idempotent: skips designs already tagged (tracked via _ds_ai_tagged_v1
 * postmeta) unless force=true is passed.
 *
 * Reuses the site's existing Groq API key (same lookup chain as
 * "Snippet 33 iconify marketplace bridge") — no new key needed if one is
 * already configured under DS API Keys Settings.
 */

if ( ! function_exists( 'ds_mkt_ai_get_groq_key' ) ) {
	function ds_mkt_ai_get_groq_key() {
		$keys = function_exists( 'ds_get_api_keys' ) ? ds_get_api_keys() : array();
		$k = $keys['groq'] ?? $keys['groq_api_key'] ?? '';
		if ( $k === '' ) $k = get_option( 'ds_groq_api_key', '' );
		if ( $k === '' ) $k = get_option( 'groq_api_key', '' );
		return $k;
	}
}

if ( ! function_exists( 'ds_mkt_ai_find_cover_attachment_id' ) ) {
	function ds_mkt_ai_find_cover_attachment_id( $download_id ) {
		// Same resolution order the live marketplace grid itself uses
		// (see "Snippet 19 marketplace images" / "Snippet 30 template
		// gallery"): featured image first, then the oldest child
		// attachment by menu_order. This keeps the AI-tagged cover image
		// identical to whatever image shoppers actually see in the grid.
		$thumb_id = get_post_thumbnail_id( $download_id );
		if ( $thumb_id ) {
			return (int) $thumb_id;
		}
		$atts = get_posts( array(
			'post_type'         => 'attachment',
			'post_parent'       => $download_id,
			'posts_per_page'    => 1,
			'orderby'           => 'menu_order',
			'order'             => 'ASC',
			'fields'            => 'ids',
			'suppress_filters'  => true,
		) );
		return $atts ? (int) $atts[0] : 0;
	}
}

if ( ! function_exists( 'ds_mkt_ai_get_dimension_tags' ) ) {
	function ds_mkt_ai_get_dimension_tags( $attachment_id ) {
		// Exact, deterministic tags from the attachment's real metadata --
		// not an AI guess -- so "1920x1080", "landscape", "portrait", and
		// resolution-tier searches always match reliably.
		$meta = wp_get_attachment_metadata( $attachment_id );
		$w = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
		$h = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
		$tags = array();
		if ( $w && $h ) {
			$tags[] = $w . 'x' . $h;
			if ( $w === $h ) {
				$tags[] = 'Square';
			} elseif ( $w > $h ) {
				$tags[] = 'Landscape';
			} else {
				$tags[] = 'Portrait';
			}
			$long_side = max( $w, $h );
			if ( $long_side >= 3000 ) {
				$tags[] = '4k';
				$tags[] = 'High Resolution';
			} elseif ( $long_side >= 1600 ) {
				$tags[] = 'High Resolution';
			} else {
				$tags[] = 'Standard Resolution';
			}
		}
		return $tags;
	}
}

if ( ! function_exists( 'ds_mkt_ai_autotag' ) ) {
	function ds_mkt_ai_autotag( $download_id, $force = false ) {
		$download_id = (int) $download_id;
		if ( ! $download_id || get_post_type( $download_id ) !== 'dlm_download' ) {
			return array( 'success' => false, 'error' => 'Not a marketplace download.' );
		}
		if ( ! $force && get_post_meta( $download_id, '_ds_ai_tagged_v1', true ) ) {
			return array( 'success' => true, 'skipped' => true, 'reason' => 'Already tagged.' );
		}

		$attachment_id = ds_mkt_ai_find_cover_attachment_id( $download_id );
		if ( ! $attachment_id ) {
			return array( 'success' => false, 'error' => 'No cover image found for this design.' );
		}
		$image_url = wp_get_attachment_url( $attachment_id );
		if ( ! $image_url ) {
			return array( 'success' => false, 'error' => 'Could not resolve image URL.' );
		}

		$api_key = ds_mkt_ai_get_groq_key();
		if ( empty( $api_key ) ) {
			return array( 'success' => false, 'error' => 'No Groq API key configured (DS API Keys Settings).' );
		}

		// Groq's vision model rejects animated GIFs outright -- skip the AI
		// call for those and fall back to deterministic dimension tags only,
		// so these designs still get tagged instead of blocking every future
		// backfill run.
		if ( get_post_mime_type( $attachment_id ) === 'image/gif' ) {
			$dim_tags = ds_mkt_ai_get_dimension_tags( $attachment_id );
			$tags = array_merge( array( 'Animated' ), $dim_tags );
			if ( empty( $tags ) ) {
				return array( 'success' => false, 'error' => 'Animated GIF with no dimension metadata.' );
			}
			wp_set_post_terms( $download_id, $tags, 'dlm_download_tag', true );
			update_post_meta( $download_id, '_ds_ai_tagged_v1', current_time( 'mysql' ) );
			update_post_meta( $download_id, '_ds_ai_tags', implode( ', ', $tags ) );
			return array( 'success' => true, 'tags' => $tags, 'attachment_id' => $attachment_id, 'note' => 'Animated GIF - AI vision skipped, dimension tags only.' );
		}

		$prompt = 'Look at this image and generate 12 to 18 short, specific search tags covering everything a shopper might search for. '
			. 'Include: every dominant and accent color, the main subject(s) and any secondary objects, the visual/art style '
			. '(e.g. flat, 3D, realistic, minimalist, illustration, photo), the mood or tone (e.g. cinematic, playful, corporate, dark, bright), '
			. 'the setting or background, the composition (e.g. centered, close-up, wide shot), the apparent image quality '
			. '(e.g. sharp, high resolution, blurry, low resolution), and the likely use case, industry, or theme '
			. '(e.g. technology, finance, healthcare, presentation background, social media graphic, product mockup). '
			. 'Output ONLY a comma-separated list of short tags (one to three words each), nothing else. '
			. 'Example: navy blue, teal accent, server room, futuristic, cinematic, dark mood, technology, sharp, high resolution, corporate presentation';

		$resp = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model'    => 'qwen/qwen3.6-27b',
				'messages' => array( array(
					'role'    => 'user',
					'content' => array(
						array( 'type' => 'text', 'text' => $prompt ),
						array( 'type' => 'image_url', 'image_url' => array( 'url' => $image_url ) ),
					),
				) ),
				'max_completion_tokens' => 200,
				'temperature'           => 0.3,
			) ),
		) );

		if ( is_wp_error( $resp ) ) {
			return array( 'success' => false, 'error' => $resp->get_error_message() );
		}
		$code = wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( $code !== 200 || empty( $body['choices'][0]['message']['content'] ) ) {
			$err = $body['error']['message'] ?? ( 'HTTP ' . $code );
			return array( 'success' => false, 'error' => 'Groq error: ' . $err );
		}

		$raw = trim( $body['choices'][0]['message']['content'] );
		$raw = preg_replace( '/^tags?\s*:\s*/i', '', $raw );
		$tags = array_filter( array_map( function ( $t ) {
			$t = trim( preg_replace( '/\s+/', ' ', $t ) );
			$t = trim( $t, " \t\n\r\0\x0B.\"'" );
			if ( $t === '' || strlen( $t ) > 30 || str_word_count( $t ) > 4 ) return '';
			return ucwords( strtolower( $t ) );
		}, explode( ',', $raw ) ) );

		// Merge AI-observed tags with deterministic, exact tags pulled straight
		// from the attachment's own metadata (dimensions, orientation,
		// resolution tier) so searches like "1920x1080", "landscape", or
		// "high resolution" work reliably and aren't left to the model's guess.
		$dim_tags = ds_mkt_ai_get_dimension_tags( $attachment_id );
		$tags = array_slice( array_values( array_unique( array_merge( $tags, $dim_tags ) ) ), 0, 20 );

		if ( empty( $tags ) ) {
			return array( 'success' => false, 'error' => 'Groq returned no usable tags.', 'raw' => $raw );
		}

		wp_set_post_terms( $download_id, $tags, 'dlm_download_tag', true );
		update_post_meta( $download_id, '_ds_ai_tagged_v1', current_time( 'mysql' ) );
		update_post_meta( $download_id, '_ds_ai_tags', implode( ', ', $tags ) );

		return array( 'success' => true, 'tags' => $tags, 'attachment_id' => $attachment_id );
	}
}

// ── Auto-trigger the moment a design is approved into the Marketplace ────
if ( ! function_exists( 'ds_mkt_ai_on_meta_change' ) ) {
	function ds_mkt_ai_on_meta_change( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( $meta_key !== 'ds_marketplace_status' || $meta_value !== 'approved' ) return;
		ds_mkt_ai_autotag( $object_id );
	}
}
add_action( 'updated_post_meta', 'ds_mkt_ai_on_meta_change', 20, 4 );
add_action( 'added_post_meta', 'ds_mkt_ai_on_meta_change', 20, 4 );

// ── Admin REST endpoints: manual re-tag + backfill existing designs ──────
add_action( 'rest_api_init', function () {
	register_rest_route( 'ds/v1', '/mkt-ai-tag-one', array(
		'methods'             => 'POST',
		'permission_callback' => function () { return current_user_can( 'manage_options' ); },
		'callback'            => function ( WP_REST_Request $req ) {
			$id    = (int) $req->get_param( 'download_id' );
			$force = (bool) $req->get_param( 'force' );
			return rest_ensure_response( ds_mkt_ai_autotag( $id, $force ) );
		},
	) );

	register_rest_route( 'ds/v1', '/mkt-ai-backfill-tags', array(
		'methods'             => 'POST',
		'permission_callback' => function () { return current_user_can( 'manage_options' ); },
		'callback'            => function ( WP_REST_Request $req ) {
			$batch = max( 1, min( 10, (int) ( $req->get_param( 'batch' ) ?: 5 ) ) );
			$query_args = array(
				'post_type'        => 'dlm_download',
				'post_status'      => 'publish',
				'posts_per_page'   => $batch,
				'fields'           => 'ids',
				'suppress_filters' => true,
				'meta_query'       => array(
					array( 'key' => 'ds_marketplace_status', 'value' => 'approved', 'compare' => '=' ),
					array( 'key' => '_ds_ai_tagged_v1', 'compare' => 'NOT EXISTS' ),
				),
			);
			$ids = get_posts( $query_args );

			$results = array();
			foreach ( $ids as $id ) {
				$results[ $id ] = ds_mkt_ai_autotag( $id );
			}

			$remaining = get_posts( array_merge( $query_args, array( 'posts_per_page' => 1 ) ) );

			return rest_ensure_response( array(
				'processed'      => count( $ids ),
				'results'        => $results,
				'more_remaining' => ! empty( $remaining ),
			) );
		},
	) );
} );
