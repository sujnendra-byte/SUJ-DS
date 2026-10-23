<?php
/**
 * Code Snippets export — DS AI Audio Outpaint (Extend Audio)
 *
 * Snippet ID : 818
 * Active     : yes
 * Priority   : 1003
 * Scope      : global
 * Tags       : 
 * Modified   : 2026-09-09 12:49:36
 * Load order : 143 of 144
 */
/**
 * DS AI Audio Outpaint  —  WaveSpeed  wavespeed-ai/ace-step/audio-outpaint
 *
 * Adds an "Extend Audio" tab to the DS AI Audio studio. The user supplies a
 * source track (upload or HTTPS URL) plus style tags, chooses how many seconds
 * to grow the track at the start and/or the end, and the model writes new music
 * that continues seamlessly from the original.
 *
 * Self-contained, in the same shape as "DS AI Voice Clone":
 *   - its own REST routes under ds/v1
 *   - its own admin settings screen (Settings -> DS Audio Outpaint)
 *   - reuses the existing audio plumbing where it already exists
 *     (ds_get_music_cost, ds_get_credits, ds_deduct_credits,
 *      ds_audio_poll_common, ds_audio_get_wavespeed_key)
 *
 * Deactivate this snippet to remove the feature completely. Nothing else is
 * modified.
 */

/* ── 1. Settings ─────────────────────────────────────────────────────────── */

if ( ! function_exists( 'ds_op_settings' ) ) {
	function ds_op_settings() {

		$defaults = array(
			'enabled'        => 1,   // Master on/off for the tab.
			'max_extend'     => 120, // Per-side ceiling; the finished track is also capped at 120s.
			'default_before' => 0,   // Pre-filled "extend from the start" value.
			'default_after'  => 30,  // Pre-filled "extend from the end" value.
			'max_upload_mb'  => 25,  // Source-file size cap.
			'allow_url'      => 1,   // Let users paste an HTTPS audio URL.
		);

		$saved = get_option( 'ds_audio_outpaint_settings', array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$out = array_merge( $defaults, $saved );

		$out['enabled']        = (int) $out['enabled'] ? 1 : 0;
		$out['allow_url']      = (int) $out['allow_url'] ? 1 : 0;
		$out['max_extend']     = max( 5, min( 240, (int) $out['max_extend'] ) );
		$out['default_before'] = max( 0, min( $out['max_extend'], (int) $out['default_before'] ) );
		$out['default_after']  = max( 0, min( $out['max_extend'], (int) $out['default_after'] ) );
		$out['max_upload_mb']  = max( 1, min( 200, (int) $out['max_upload_mb'] ) );

		return $out;
	}
}

if ( ! function_exists( 'ds_op_get_cost' ) ) {
	/**
	 * Credits for a given amount of *added* audio.
	 *
	 * Delegates to ds_get_music_cost() so this tab always tracks whatever the
	 * admin has set for ace-step music, including the platform charge. The
	 * fallback only matters if the audio API snippet is deactivated.
	 */
	function ds_op_get_cost( $seconds ) {

		$seconds = max( 1, (int) $seconds );

		if ( function_exists( 'ds_get_music_cost' ) ) {
			$cost = (int) ds_get_music_cost( $seconds );
		} else {
			$blocks = (int) ceil( $seconds / 30 );
			$cost   = ( $blocks * 6 ) + 1;
		}

		return (int) apply_filters( 'ds_audio_outpaint_cost_calculated', max( 1, $cost ), $seconds );
	}
}

if ( ! function_exists( 'ds_op_api_key' ) ) {
	function ds_op_api_key() {

		if ( function_exists( 'ds_audio_get_wavespeed_key' ) ) {
			$k = ds_audio_get_wavespeed_key();
			if ( ! empty( $k ) ) {
				return (string) $k;
			}
		}

		return (string) get_option( 'ds_wavespeed_api_key', '' );
	}
}

/* ── 2. Model call ───────────────────────────────────────────────────────── */

if ( ! function_exists( 'ds_op_call_wavespeed' ) ) {
	/**
	 * Submit an outpaint task. Returns array( 'prediction_id' => string ) or
	 * array( 'error' => string ), matching ds_call_wavespeed_acestep() so the
	 * shared poller works unchanged.
	 */
	function ds_op_call_wavespeed( $audio_url, $tags, $lyrics = '', $before = 0, $after = 30, $seed = -1 ) {

		$key = ds_op_api_key();

		if ( empty( $key ) ) {
			return array( 'error' => 'The audio service is not configured yet. Please contact support.' );
		}

		$s   = ds_op_settings();
		$max = (int) $s['max_extend'];

		$payload = array(
			'audio'                  => (string) $audio_url,
			'tags'                   => (string) $tags,
			'extend_before_duration' => max( 0, min( $max, (int) $before ) ),
			'extend_after_duration'  => max( 0, min( $max, (int) $after ) ),
			'lyrics'                 => (string) $lyrics,
			'seed'                   => (int) $seed,
		);

		$resp = wp_remote_post(
			'https://api.wavespeed.ai/api/v3/wavespeed-ai/ace-step/audio-outpaint',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $resp ) ) {
			return array( 'error' => $resp->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );

		if ( $code < 200 || $code > 299 ) {
			$msg = ( is_array( $body ) && isset( $body['message'] ) ) ? (string) $body['message'] : '';
			return array( 'error' => '' !== $msg ? $msg : ( 'The audio service returned an error (' . $code . ').' ) );
		}

		$pid = '';
		if ( is_array( $body ) && isset( $body['data']['id'] ) ) {
			$pid = (string) $body['data']['id'];
		} elseif ( is_array( $body ) && isset( $body['id'] ) ) {
			$pid = (string) $body['id'];
		}

		if ( '' === $pid ) {
			return array( 'error' => 'The audio service did not return a task id.' );
		}

		return array( 'prediction_id' => $pid );
	}
}

/* ── 3. REST API ─────────────────────────────────────────────────────────── */

add_action(
	'rest_api_init',
	function () {

		/* ── Upload a source track ───────────────────────────────────────── */
		register_rest_route(
			'ds/v1',
			'/outpaint-source',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {

					$s = ds_op_settings();

					if ( ! $s['enabled'] ) {
						return new WP_Error( 'ds_op_off', 'Extend Audio is currently unavailable.', array( 'status' => 403 ) );
					}

					$uid   = get_current_user_id();
					$files = $req->get_file_params();

					if ( empty( $files['source'] ) || empty( $files['source']['tmp_name'] ) ) {
						return new WP_Error( 'ds_op_nofile', 'No audio file was received.', array( 'status' => 400 ) );
					}

					$file = $files['source'];

					if ( ! empty( $file['error'] ) ) {
						return new WP_Error( 'ds_op_upload', 'The upload did not complete. Please try again.', array( 'status' => 400 ) );
					}

					$max_bytes = (int) $s['max_upload_mb'] * 1024 * 1024;

					if ( (int) $file['size'] > $max_bytes ) {
						return new WP_Error( 'ds_op_big', 'That file is larger than ' . (int) $s['max_upload_mb'] . ' MB.', array( 'status' => 400 ) );
					}

					if ( (int) $file['size'] < 1024 ) {
						return new WP_Error( 'ds_op_small', 'That file looks empty.', array( 'status' => 400 ) );
					}

					$allowed = array(
						'mp3'  => 'audio/mpeg',
						'wav'  => 'audio/wav',
						'm4a'  => 'audio/mp4',
						'mp4'  => 'audio/mp4',
						'ogg'  => 'audio/ogg',
						'oga'  => 'audio/ogg',
						'webm' => 'audio/webm',
						'flac' => 'audio/flac',
					);

					$ext = strtolower( pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) );

					if ( ! isset( $allowed[ $ext ] ) ) {
						return new WP_Error( 'ds_op_type', 'Please upload an MP3, WAV, FLAC, M4A, OGG or WebM file.', array( 'status' => 400 ) );
					}

					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';

					// The model fetches this file over plain HTTPS, so the name
					// must not be guessable from the outside.
					$file['name'] = 'ds-outpaint-' . wp_generate_password( 20, false, false ) . '.' . $ext;

					// The extension above is always one of our own, so pinning
					// the type here cannot smuggle an executable through.
					$forced_type = array(
						'ext'             => $ext,
						'type'            => $allowed[ $ext ],
						'proper_filename' => false,
					);

					$force_cb = function () use ( $forced_type ) {
						return $forced_type;
					};

					add_filter( 'wp_check_filetype_and_ext', $force_cb, 99 );

					$moved = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => $allowed ) );

					remove_filter( 'wp_check_filetype_and_ext', $force_cb, 99 );

					if ( ! is_array( $moved ) || ! empty( $moved['error'] ) ) {
						$err = ( is_array( $moved ) && ! empty( $moved['error'] ) ) ? (string) $moved['error'] : 'The upload could not be saved.';
						return new WP_Error( 'ds_op_save', $err, array( 'status' => 500 ) );
					}

					$attach_id = wp_insert_attachment(
						array(
							'post_mime_type' => $moved['type'],
							'post_title'     => 'Outpaint source ' . sanitize_text_field( (string) $file['name'] ),
							'post_content'   => '',
							'post_status'    => 'inherit',
							'post_author'    => $uid,
						),
						$moved['file']
					);

					if ( is_wp_error( $attach_id ) || ! $attach_id ) {
						return new WP_Error( 'ds_op_attach', 'The upload could not be stored.', array( 'status' => 500 ) );
					}

					// Metadata is a nice-to-have — never let it break the upload.
					if ( function_exists( 'wp_generate_attachment_metadata' ) ) {
						$meta = @wp_generate_attachment_metadata( $attach_id, $moved['file'] );
						if ( ! empty( $meta ) ) {
							wp_update_attachment_metadata( $attach_id, $meta );
						}
					}

					update_post_meta( $attach_id, '_ds_outpaint_source_owner', $uid );

					/* The finished track is capped at 240s, so the caller needs this length. */
					$src_seconds = 0;
					$a_meta      = wp_get_attachment_metadata( $attach_id );
					if ( ! empty( $a_meta['length'] ) ) {
						$src_seconds = (int) round( (float) $a_meta['length'] );
					}

					return rest_ensure_response(
						array(
							'url'           => wp_get_attachment_url( $attach_id ),
							'attachment_id' => (int) $attach_id,
							'duration'      => (int) $src_seconds,
							'name'          => sanitize_text_field( (string) $files['source']['name'] ),
						)
					);
				},
			)
		);

		/* ── Start an outpaint ───────────────────────────────────────────── */
		register_rest_route(
			'ds/v1',
			'/generate-outpaint',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {

					@ignore_user_abort( true );
					@set_time_limit( 60 );

					$s = ds_op_settings();

					if ( ! $s['enabled'] ) {
						return new WP_Error( 'ds_op_off', 'Extend Audio is currently unavailable.', array( 'status' => 403 ) );
					}

					$uid = get_current_user_id();

					$audio_url = esc_url_raw( trim( (string) $req->get_param( 'audio' ) ) );
					$tags      = sanitize_text_field( (string) $req->get_param( 'tags' ) );
					$lyrics    = (string) $req->get_param( 'lyrics' );
					$before    = (int) $req->get_param( 'extend_before' );
					$after     = (int) $req->get_param( 'extend_after' );
					$seed      = ( null === $req->get_param( 'seed' ) ) ? -1 : (int) $req->get_param( 'seed' );

					if ( empty( $audio_url ) || 0 !== stripos( $audio_url, 'https://' ) ) {
						return new WP_Error( 'ds_op_src', 'Please upload a track or paste an https:// link to one.', array( 'status' => 400 ) );
					}

					if ( '' === trim( $tags ) ) {
						return new WP_Error( 'ds_op_tags', 'Describe the style of the music so the new part matches - for example "acoustic guitar, warm, mellow".', array( 'status' => 400 ) );
					}

					$before = max( 0, min( (int) $s['max_extend'], $before ) );
					$after  = max( 0, min( (int) $s['max_extend'], $after ) );
					/* ACE-Step caps the FINISHED track at 240s - not each added side.
					   Trim the request to whatever room the source track leaves. */
					$src_len = (int) $req->get_param( 'src_duration' );
					$local_id = function_exists( 'attachment_url_to_postid' ) ? (int) attachment_url_to_postid( $audio_url ) : 0;
					if ( $local_id ) {
						$local_meta = wp_get_attachment_metadata( $local_id );
						if ( ! empty( $local_meta['length'] ) ) {
							$src_len = (int) round( (float) $local_meta['length'] );
						}
					}
					if ( $src_len > 0 ) {
						$room = 120 - $src_len;
						if ( $room < 5 ) {
							return new WP_Error(
								'ds_op_len',
								'This track is already ' . $src_len . ' seconds long. A finished track can be 120 seconds at most, so there is no room left to add to it.',
								array( 'status' => 400 )
							);
						}
						if ( $before + $after > $room ) {
							$before = min( $before, $room );
							$after  = min( $after, $room - $before );
						}
					}

					$total  = $before + $after;

					if ( $total < 1 ) {
						return new WP_Error( 'ds_op_len', 'Choose how much audio to add - at least a few seconds at the start or the end.', array( 'status' => 400 ) );
					}

					$lyrics = trim( $lyrics );
					if ( '' === $lyrics ) {
						$lyrics = '[inst]';
					} else {
						$lyrics = wp_strip_all_tags( $lyrics );
						if ( strlen( $lyrics ) > 4000 ) {
							$lyrics = substr( $lyrics, 0, 4000 );
						}
					}

					/* ── Credit check ──────────────────────────────────────── */
					if ( ! function_exists( 'ds_get_credits' ) || ! function_exists( 'ds_deduct_credits' ) ) {
						return new WP_Error( 'ds_op_credits', 'Credits are unavailable right now.', array( 'status' => 500 ) );
					}

					$cost    = ds_op_get_cost( $total );
					$credits = (int) ds_get_credits( $uid );

					if ( $credits < $cost ) {
						return new WP_Error( 'ds_op_nocredits', 'You need ' . $cost . ' credits for this and have ' . $credits . '.', array( 'status' => 402 ) );
					}

					$result = ds_op_call_wavespeed( $audio_url, $tags, $lyrics, $before, $after, $seed );

					if ( ! empty( $result['error'] ) ) {
						return new WP_Error( 'ds_op_api', (string) $result['error'], array( 'status' => 502 ) );
					}

					if ( empty( $result['prediction_id'] ) ) {
						return new WP_Error( 'ds_op_api', 'The audio service did not start the task.', array( 'status' => 502 ) );
					}

					/* ── Deduct only after the task was accepted ────────────── */
					ds_deduct_credits( $uid, $cost );

					$label = 'Extended · ' . $total . 's added';
					$now   = current_time( 'Y-m-d H:i:s' );

					$gens = get_user_meta( $uid, 'ds_ai_audio_generations', true );
					if ( ! is_array( $gens ) ) {
						$gens = array();
					}

					array_unshift(
						$gens,
						array(
							'prediction_id' => $result['prediction_id'],
							'kind'          => 'outpaint',
							'mode'          => 'outpaint',
							'label'         => $label,
							'tags'          => $tags,
							'duration'      => $total,
							'extend_before' => $before,
							'extend_after'  => $after,
							'source'        => $audio_url,
							'credits'       => $cost,
							'date'          => $now,
							'status'        => 'pending',
							'url'           => '',
						)
					);

					update_user_meta( $uid, 'ds_ai_audio_generations', array_slice( $gens, 0, 200 ) );

					do_action( 'ds_audio_outpaint_started', $uid, $result['prediction_id'], $total, $cost );

					return rest_ensure_response(
						array(
							'prediction_id' => $result['prediction_id'],
							'status'        => 'pending',
							'label'         => $label,
							'credits'       => (int) ds_get_credits( $uid ),
							'cost'          => $cost,
						)
					);
				},
			)
		);

		/* ── Poll ────────────────────────────────────────────────────────── */
		register_rest_route(
			'ds/v1',
			'/poll-outpaint',
			array(
				'methods'             => 'GET',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {

					if ( ! function_exists( 'ds_audio_poll_common' ) ) {
						return new WP_Error( 'ds_op_poll', 'The audio service is unavailable right now.', array( 'status' => 500 ) );
					}

					// The shared poller stores the finished file against the
					// pending card, refunds on failure and schedules the rehost.
					return ds_audio_poll_common( $req, 'outpaint' );
				},
			)
		);
	}
);

/* ── 4. Front end ────────────────────────────────────────────────────────── */

if ( ! function_exists( 'ds_op_is_audio_page' ) ) {
	function ds_op_is_audio_page() {

		if ( is_admin() ) {
			return false;
		}

		$is = false;

		if ( is_singular() ) {
			$post = get_post();
			if ( $post && has_shortcode( (string) $post->post_content, 'ds_ai_audio' ) ) {
				$is = true;
			}
		}

		// Fallback for page builders that don't keep the shortcode in post_content.
		if ( ! $is && isset( $_SERVER['REQUEST_URI'] ) ) {
			$uri = strtolower( (string) $_SERVER['REQUEST_URI'] );
			if ( false !== strpos( $uri, 'ai-audio' ) || false !== strpos( $uri, 'ai-music' ) ) {
				$is = true;
			}
		}

		return (bool) apply_filters( 'ds_audio_outpaint_is_audio_page', $is );
	}
}

add_action(
	'wp_footer',
	function () {

		$s = ds_op_settings();

		if ( ! $s['enabled'] || ! is_user_logged_in() || ! ds_op_is_audio_page() ) {
			return;
		}

		// Precompute the price of every selectable length so the number the
		// user sees is exactly the number the server will charge.
		$costs = array();
		for ( $sec = 5; $sec <= ( (int) $s['max_extend'] * 2 ); $sec += 5 ) {
			$costs[ (string) $sec ] = ds_op_get_cost( $sec );
		}

		$cfg = array(
			'rest'      => esc_url_raw( rest_url( 'ds/v1/' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'maxExtend' => (int) $s['max_extend'],
			'before'    => (int) $s['default_before'],
			'after'     => (int) $s['default_after'],
			'maxMb'     => (int) $s['max_upload_mb'],
			'allowUrl'  => (int) $s['allow_url'],
			'costs'     => $costs,
		);
		?>
<style id="dsopCss">
.dsopSrcTabs{display:flex;gap:6px;margin:6px 0 8px}
.dsopSrcTab{flex:1;padding:6px 8px;font-size:12px;border-radius:8px;border:1px solid #1e293b;background:transparent;color:#94a3b8;cursor:pointer}
.dsopSrcTab.on{background:#2563eb;border-color:#2563eb;color:#fff}
.dsopUpload{border:1px dashed #334155;border-radius:10px;padding:16px 12px;text-align:center;font-size:12px;color:#94a3b8;cursor:pointer;background:rgba(255,255,255,.02)}
.dsopUpload:hover{border-color:#2563eb;color:#cbd5e1}
.dsopUpload.has{border-style:solid;border-color:#2563eb;color:#cbd5e1}
.dsopSrcRow{display:flex;align-items:center;gap:8px;margin-top:8px;padding:6px 10px;border:1px solid #1e293b;border-radius:8px;background:rgba(255,255,255,.02)}
.dsopPlay{flex:0 0 auto;width:26px;height:26px;border-radius:50%;border:none;background:#2563eb;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
.dsopPlay:hover{background:#1d4ed8}
.dsopSrcTxt{font-size:12px;color:#94a3b8;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dsopSrcClear{background:none;border:none;color:#64748b;font-size:16px;line-height:1;cursor:pointer;padding:0 2px}
.dsopSrcClear:hover{color:#f87171}
.dsopDiagram{display:flex;align-items:stretch;gap:2px;height:26px;margin:10px 0 4px}
.dsopSeg{border-radius:4px;font-size:10px;color:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;white-space:nowrap;min-width:0}
.dsopSegNew{background:#2563eb}
.dsopSegOrig{background:#334155;flex:1 1 auto;color:#cbd5e1}
.dsopNote{font-size:11px;color:#64748b;margin-top:6px;line-height:1.5}
.dsopErr{display:none;margin-top:8px;padding:8px 10px;border-radius:8px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.35);color:#fca5a5;font-size:12px}
</style>

<div id="dsopTpl" style="display:none">
	<div class="dsaaPane" id="dsaaPane-outpaint">

		<label class="dsaaLbl">Track to extend</label>

		<div class="dsopSrcTabs" id="dsopSrcTabs">
			<button type="button" class="dsopSrcTab on" data-src="file">Upload a file</button>
			<button type="button" class="dsopSrcTab" data-src="url">Paste a link</button>
		</div>

		<div id="dsopFileWrap">
			<div class="dsopUpload" id="dsopUpload">
				<span id="dsopUploadTxt">Click to choose an MP3, WAV, FLAC, M4A, OGG or WebM file</span>
			</div>
			<input type="file" id="dsopFile" accept="audio/*" style="display:none">
		</div>

		<div id="dsopUrlWrap" style="display:none">
			<input class="dsaaSelect" type="url" id="dsopUrl" placeholder="https://example.com/my-track.mp3">
			<div class="dsaaHint">The link has to be public - the model downloads the file itself.</div>
		</div>

		<div class="dsopSrcRow" id="dsopSrcRow" style="display:none">
			<button type="button" class="dsopPlay" id="dsopPlayBtn" title="Preview the source">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
			</button>
			<span class="dsopSrcTxt" id="dsopSrcTxt">Source track</span>
			<button type="button" class="dsopSrcClear" id="dsopSrcClear" title="Remove">&times;</button>
		</div>

		<label class="dsaaLbl">Describe the style</label>
		<textarea class="dsaaTextarea" id="dsopTags" placeholder="e.g. acoustic guitar, warm, mellow, slow tempo" style="min-height:60px"></textarea>
		<div class="dsaaHint">The new section is written to match this. Describe the original track in plain words.</div>

		<label class="dsaaLbl">Lyrics for the new part (optional)</label>
		<textarea class="dsaaTextarea" id="dsopLyrics" placeholder="Leave empty for an instrumental intro or outro" style="min-height:50px"></textarea>

		<label class="dsaaLbl">Add to the start <span id="dsopBeforeVal">0s</span></label>
		<input type="range" class="dsaaRange" id="dsopBefore" min="0" max="240" step="5" value="0">

		<label class="dsaaLbl">Add to the end <span id="dsopAfterVal">30s</span></label>
		<input type="range" class="dsaaRange" id="dsopAfter" min="0" max="240" step="5" value="30">

		<div class="dsopDiagram" id="dsopDiagram">
			<div class="dsopSeg dsopSegNew" id="dsopSegBefore" style="display:none"></div>
			<div class="dsopSeg dsopSegOrig">your track</div>
			<div class="dsopSeg dsopSegNew" id="dsopSegAfter"></div>
		</div>

		<div class="dsaaHint"><span id="dsopTotal">30</span>s of new audio · <span id="dsopCostHint">0</span> credits</div>

		<div class="dsaaHint" id="dsopCapHint" style="margin-top:6px"></div>
		<div class="dsopErr" id="dsopErr"></div>

		<button class="dsaaGenBtn" id="dsopGenBtn" type="button">Extend audio</button>

		<div class="dsopNote">The model writes new music that continues from your track and returns the whole thing as one file. Longer extensions take longer to render.</div>
	</div>
</div>

<script>
(function () {
	'use strict';

	var CFG = <?php echo wp_json_encode( $cfg ); ?>;
	if (!CFG || !CFG.rest) { return; }

	var srcUrl = '';
	var srcName = '';
	var previewAudio = null;
	var polling = null;

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function api(path, opts) {
		opts = opts || {};
		opts.headers = opts.headers || {};
		opts.headers['X-WP-Nonce'] = CFG.nonce;
		opts.credentials = 'same-origin';
		return fetch(CFG.rest + path, opts).then(function (r) {
			return r.json().then(function (j) {
				if (!r.ok) { throw new Error((j && j.message) || 'Something went wrong.'); }
				return j;
			});
		});
	}

	function showErr(msg) {
		var el = document.getElementById('dsopErr');
		if (!el) { return; }
		el.textContent = msg;
		el.style.display = 'block';
	}

	function hideErr() {
		var el = document.getElementById('dsopErr');
		if (el) { el.style.display = 'none'; }
	}

	function toastLike(msg) {
		var t = document.getElementById('dsaaToast');
		if (!t) { return; }
		t.textContent = msg;
		t.classList.add('on');
		setTimeout(function () { t.classList.remove('on'); }, 4000);
	}

	function setCredits(n) {
		var el = document.getElementById('dsaaCreditsVal');
		if (el && typeof n === 'number') { el.textContent = n; }
	}

	function costFor(total) {
		if (total < 1) { return 0; }
		var step = Math.ceil(total / 5) * 5;
		var c = CFG.costs[String(step)];
		return typeof c === 'number' ? c : 0;
	}

	function refreshMeters() {
		var b = parseInt(document.getElementById('dsopBefore').value, 10) || 0;
		var a = parseInt(document.getElementById('dsopAfter').value, 10) || 0;
		var total = b + a;

		document.getElementById('dsopBeforeVal').textContent = b + 's';
		document.getElementById('dsopAfterVal').textContent = a + 's';
		document.getElementById('dsopTotal').textContent = total;
		document.getElementById('dsopCostHint').textContent = costFor(total);

		var segB = document.getElementById('dsopSegBefore');
		var segA = document.getElementById('dsopSegAfter');
		segB.style.display = b > 0 ? 'flex' : 'none';
		segA.style.display = a > 0 ? 'flex' : 'none';
		segB.style.flex = '0 0 ' + Math.max(8, Math.min(35, b / 3)) + '%';
		segA.style.flex = '0 0 ' + Math.max(8, Math.min(35, a / 3)) + '%';
		segB.textContent = b > 0 ? ('+' + b + 's') : '';
		segA.textContent = a > 0 ? ('+' + a + 's') : '';
	}

	function stopPreview() {
		if (previewAudio) {
			previewAudio.pause();
			previewAudio = null;
		}
	}

		var srcDuration = 0;                      /* length of the chosen source track, in seconds */

	/* ACE-Step caps the FINISHED track at 240s, not each added side. Keep the
	   sliders inside whatever room is left after the source track's own length. */
	function applyDurationCap() {
		var MODEL_MAX = 120;
		var bEl = document.getElementById('dsopBefore');
		var aEl = document.getElementById('dsopAfter');
		if (!bEl || !aEl) { return; }

		var hard = CFG.maxExtend || MODEL_MAX;
		var room = srcDuration ? Math.max(0, MODEL_MAX - Math.ceil(srcDuration)) : hard;
		var cap  = Math.floor(Math.min(hard, room) / 5) * 5;

		bEl.max = String(cap);
		aEl.max = String(cap);
		if ((parseInt(bEl.value, 10) || 0) > cap) { bEl.value = String(cap); }
		if ((parseInt(aEl.value, 10) || 0) > cap) { aEl.value = String(cap); }
		var b = parseInt(bEl.value, 10) || 0;
		var a = parseInt(aEl.value, 10) || 0;
		if (b + a > cap) { aEl.value = String(Math.max(0, cap - b)); }

		var hint = document.getElementById('dsopCapHint');
		if (hint) {
			hint.textContent = srcDuration
				? 'Source track is ' + Math.round(srcDuration) + 's. A finished track can be ' + MODEL_MAX + 's at most, so you can add up to ' + cap + 's in total.'
				: '';
		}
		if (srcDuration && cap <= 0) {
			showErr('This track is already ' + Math.round(srcDuration) + 's long. A finished track can be ' + MODEL_MAX + 's at most, so there is no room left to extend it. Try a shorter source track.');
		}
		if (typeof refreshMeters === 'function') { refreshMeters(); }
	}

	function setSource(url, name, knownSeconds) {
		srcDuration = (typeof knownSeconds === 'number' && knownSeconds > 0) ? knownSeconds : 0;
		applyDurationCap();
		srcUrl = url || '';
		srcName = name || '';
		var row = document.getElementById('dsopSrcRow');
		if (!srcUrl) {
			row.style.display = 'none';
			stopPreview();
			return;
		}
		document.getElementById('dsopSrcTxt').textContent = srcName || srcUrl;
		row.style.display = 'flex';
		/* Pasted links have no server-side length, so ask the browser for it. */
		if (!srcDuration) {
			var probe = new Audio();
			probe.preload = 'metadata';
			probe.addEventListener('loadedmetadata', function () {
				if (isFinite(probe.duration) && probe.duration > 0) {
					srcDuration = probe.duration;
					applyDurationCap();
				}
			});
			probe.src = srcUrl;
		}
	}

	function wireSourceTabs() {
		document.querySelectorAll('#dsopSrcTabs .dsopSrcTab').forEach(function (btn) {
			btn.addEventListener('click', function () {
				document.querySelectorAll('#dsopSrcTabs .dsopSrcTab').forEach(function (b) { b.classList.remove('on'); });
				btn.classList.add('on');
				var mode = btn.getAttribute('data-src');
				document.getElementById('dsopFileWrap').style.display = (mode === 'file') ? '' : 'none';
				document.getElementById('dsopUrlWrap').style.display = (mode === 'url') ? '' : 'none';
				setSource('', '');
			});
		});

		if (!CFG.allowUrl) {
			var t = document.querySelector('#dsopSrcTabs .dsopSrcTab[data-src="url"]');
			if (t) { t.style.display = 'none'; }
		}

		var urlInput = document.getElementById('dsopUrl');
		urlInput.addEventListener('change', function () {
			var v = urlInput.value.trim();
			if (!v) { setSource('', ''); return; }
			if (v.indexOf('https://') !== 0) {
				showErr('The link has to start with https://');
				setSource('', '');
				return;
			}
			hideErr();
			setSource(v, v.split('/').pop());
		});
	}

	function wireUpload() {
		var box = document.getElementById('dsopUpload');
		var input = document.getElementById('dsopFile');
		var txt = document.getElementById('dsopUploadTxt');
		var idle = 'Click to choose an MP3, WAV, FLAC, M4A, OGG or WebM file';

		box.addEventListener('click', function () { input.click(); });

		input.addEventListener('change', function () {
			var f = input.files && input.files[0];
			if (!f) { return; }

			if (f.size > CFG.maxMb * 1024 * 1024) {
				showErr('That file is larger than ' + CFG.maxMb + ' MB.');
				input.value = '';
				return;
			}

			hideErr();
			box.classList.add('has');
			txt.textContent = 'Uploading ' + f.name + '...';

			var fd = new FormData();
			fd.append('source', f);

			api('outpaint-source', { method: 'POST', body: fd }).then(function (j) {
				txt.textContent = f.name;
				setSource(j.url, j.name || f.name, j.duration);
			}).catch(function (e) {
				box.classList.remove('has');
				txt.textContent = idle;
				showErr(e.message);
				input.value = '';
			});
		});
	}

	function wirePreview() {
		document.getElementById('dsopPlayBtn').addEventListener('click', function () {
			if (previewAudio) { stopPreview(); return; }
			if (!srcUrl) { return; }
			previewAudio = new Audio(srcUrl);
			previewAudio.addEventListener('ended', stopPreview);
			previewAudio.play().catch(function () {
				showErr('That file could not be played here, but the model may still be able to read it.');
			});
		});

		document.getElementById('dsopSrcClear').addEventListener('click', function () {
			setSource('', '');
			document.getElementById('dsopUrl').value = '';
			document.getElementById('dsopFile').value = '';
			document.getElementById('dsopUpload').classList.remove('has');
			document.getElementById('dsopUploadTxt').textContent = 'Click to choose an MP3, WAV, FLAC, M4A, OGG or WebM file';
		});
	}

	function feedList() {
		var body = document.getElementById('dsaaFeedBody');
		if (!body) { return null; }
		return document.getElementById('dsaaCardsList') || body;
	}

	function addPendingCard(pid, label) {
		var list = feedList();
		if (!list) { return null; }
		var el = document.createElement('div');
		el.className = 'dsaaCard dsaaPendingRow';
		el.setAttribute('data-pid', pid);
		el.innerHTML = '<div class="dsaaCardTop"><div class="dsaaCardMeta">' +
			'<span class="dsaaCardKind music">Extend Audio</span>' +
			'<div class="dsaaCardLabel">' + esc(label) + '</div></div></div>' +
			'<div class="dsaaPendingRow"><span>Extending your track...</span></div>';
		list.insertBefore(el, list.firstChild);
		return el;
	}

	function handOverToFeed(el) {
		if (el && el.parentNode) { el.parentNode.removeChild(el); }
		var f = document.querySelector('.dsaaFilterBtn.on') || document.querySelector('.dsaaFilterBtn');
		if (f) { f.click(); }
		toastLike('Your extended track is ready - it is at the top of My Creations.');
	}

	function renderFailed(el, msg) {
		showErr(msg);
		if (!el) { return; }
		var row = el.querySelector('.dsaaPendingRow span');
		if (row) { row.textContent = msg; }
		setTimeout(function () {
			if (el && el.parentNode) { el.parentNode.removeChild(el); }
		}, 6000);
	}

	function poll(pid, card, btn) {
		var tries = 0;

		clearInterval(polling);
		polling = setInterval(function () {
			tries++;

			if (tries > 200) { // roughly ten minutes
				clearInterval(polling);
				btn.disabled = false;
				btn.textContent = 'Extend audio';
				renderFailed(card, 'This is taking longer than expected. Check My Creations in a few minutes.');
				return;
			}

			api('poll-outpaint?prediction_id=' + encodeURIComponent(pid)).then(function (j) {
				var st = j && j.status;

				if (st === 'succeeded' || st === 'completed') {
					clearInterval(polling);
					btn.disabled = false;
					btn.textContent = 'Extend audio';
					handOverToFeed(card);
				} else if (st === 'failed' || st === 'cancelled' || st === 'timeout') {
					clearInterval(polling);
					btn.disabled = false;
					btn.textContent = 'Extend audio';
					renderFailed(card, (j && j.error) || 'That extension failed. Your credits have been returned.');
				}
			}).catch(function () {
				/* Transient - keep polling. */
			});
		}, 3000);
	}

	function wireGenerate() {
		var btn = document.getElementById('dsopGenBtn');

		btn.addEventListener('click', function () {
			hideErr();

			if (!srcUrl) {
				showErr('Choose a track to extend first.');
				return;
			}

			var tags = document.getElementById('dsopTags').value.trim();
			if (!tags) {
				showErr('Describe the style so the new part matches the original.');
				return;
			}

			var b = parseInt(document.getElementById('dsopBefore').value, 10) || 0;
			var a = parseInt(document.getElementById('dsopAfter').value, 10) || 0;

			if (b + a < 1) {
				showErr('Choose how much audio to add at the start or the end.');
				return;
			}

			btn.disabled = true;
			btn.textContent = 'Starting...';

			var body = new FormData();
			body.append('audio', srcUrl);
			body.append('tags', tags);
			body.append('lyrics', document.getElementById('dsopLyrics').value.trim());
			body.append('extend_before', String(b));
			body.append('extend_after', String(a));
		body.append('src_duration', String(Math.round(srcDuration || 0)));

			api('generate-outpaint', { method: 'POST', body: body }).then(function (j) {
				setCredits(j.credits);
				btn.textContent = 'Extending...';
				var card = addPendingCard(j.prediction_id, j.label || 'Extended track');
				poll(j.prediction_id, card, btn);
			}).catch(function (e) {
				btn.disabled = false;
				btn.textContent = 'Extend audio';
				showErr(e.message);
			});
		});
	}

	function mount() {
		var tabs = document.querySelector('.dsaaTabs');
		var side = document.querySelector('.dsaaSide');
		var tpl = document.getElementById('dsopTpl');

		if (!tabs || !side || !tpl) { return false; }
		if (document.querySelector('.dsaaTabBtn[data-tab="outpaint"]')) { return true; }

		var pane = tpl.firstElementChild;
		side.appendChild(pane);
		tpl.parentNode.removeChild(tpl);

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'dsaaTabBtn';
		btn.setAttribute('data-tab', 'outpaint');
		btn.textContent = 'Extend Audio';
		tabs.appendChild(btn);

		btn.addEventListener('click', function (e) {
			if (e) {
				e.stopPropagation();
				if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); }
			}
			// Re-assert after any delegated handler the studio runs on the tab bar.
			setTimeout(function () {
				document.querySelectorAll('.dsaaTabBtn').forEach(function (x) { x.classList.remove('on'); });
				document.querySelectorAll('.dsaaPane').forEach(function (p) { p.classList.remove('on'); });
				btn.classList.add('on');
				pane.classList.add('on');
			}, 0);
			document.querySelectorAll('.dsaaTabBtn').forEach(function (x) { x.classList.remove('on'); });
			document.querySelectorAll('.dsaaPane').forEach(function (p) { p.classList.remove('on'); });
			btn.classList.add('on');
			pane.classList.add('on');
		});

		var maxE = CFG.maxExtend;
		var bEl = document.getElementById('dsopBefore');
		var aEl = document.getElementById('dsopAfter');
		bEl.max = maxE;
		aEl.max = maxE;
		bEl.value = Math.min(CFG.before, maxE);
		aEl.value = Math.min(CFG.after, maxE);
		bEl.addEventListener('input', refreshMeters);
		aEl.addEventListener('input', refreshMeters);

		wireSourceTabs();
		wireUpload();
		wirePreview();
		wireGenerate();
		refreshMeters();

		return true;
	}

	function boot() {
		if (mount()) { return; }
		var tries = 0;
		var iv = setInterval(function () {
			tries++;
			if (mount() || tries > 60) { clearInterval(iv); }
		}, 250);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
}());
</script>
		<?php
	},
	60
);

/* ── 5. Admin settings ───────────────────────────────────────────────────── */

add_action(
	'admin_menu',
	function () {
		add_options_page(
			'DS Audio Outpaint',
			'DS Audio Outpaint',
			'manage_options',
			'ds-audio-outpaint',
			'ds_op_render_settings_page'
		);
	}
);

if ( ! function_exists( 'ds_op_render_settings_page' ) ) {
	function ds_op_render_settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$saved = false;

		if ( isset( $_POST['ds_op_save'] ) && check_admin_referer( 'ds_op_settings' ) ) {

			$new = array(
				'enabled'        => isset( $_POST['ds_op_enabled'] ) ? 1 : 0,
				'allow_url'      => isset( $_POST['ds_op_allow_url'] ) ? 1 : 0,
				'max_extend'     => isset( $_POST['ds_op_max_extend'] ) ? (int) $_POST['ds_op_max_extend'] : 240,
				'default_before' => isset( $_POST['ds_op_default_before'] ) ? (int) $_POST['ds_op_default_before'] : 0,
				'default_after'  => isset( $_POST['ds_op_default_after'] ) ? (int) $_POST['ds_op_default_after'] : 30,
				'max_upload_mb'  => isset( $_POST['ds_op_max_upload_mb'] ) ? (int) $_POST['ds_op_max_upload_mb'] : 25,
			);

			update_option( 'ds_audio_outpaint_settings', $new );
			$saved = true;
		}

		$s = ds_op_settings();
		?>
		<div class="wrap">
			<h1>DS Audio Outpaint</h1>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
			<?php endif; ?>

			<p>
				Adds an <strong>Extend Audio</strong> tab to the AI Audio studio, powered by
				WaveSpeed <code>ace-step/audio-outpaint</code>. Users give it a track and it
				writes new music onto the start, the end, or both.
			</p>

			<p>
				Credits are charged per <strong>30 seconds of added audio</strong>, using the same
				rate as ace-step music generation. Change that rate in your existing
				<em>DS AI Settings</em> screen and this tab follows automatically.
				Current price for a 30-second extension:
				<strong><?php echo (int) ds_op_get_cost( 30 ); ?> credits</strong>.
			</p>

			<form method="post">
				<?php wp_nonce_field( 'ds_op_settings' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">Enabled</th>
						<td>
							<label>
								<input type="checkbox" name="ds_op_enabled" value="1" <?php checked( 1, (int) $s['enabled'] ); ?>>
								Show the Extend Audio tab
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">Allow pasted links</th>
						<td>
							<label>
								<input type="checkbox" name="ds_op_allow_url" value="1" <?php checked( 1, (int) $s['allow_url'] ); ?>>
								Let users paste an https:// audio URL instead of uploading
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ds_op_max_extend">Maximum per side</label></th>
						<td>
							<input type="number" id="ds_op_max_extend" name="ds_op_max_extend" min="5" max="240" value="<?php echo (int) $s['max_extend']; ?>" class="small-text"> seconds
							<p class="description">The model's own ceiling is 240 seconds each side.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ds_op_default_before">Default added to the start</label></th>
						<td><input type="number" id="ds_op_default_before" name="ds_op_default_before" min="0" max="240" step="5" value="<?php echo (int) $s['default_before']; ?>" class="small-text"> seconds</td>
					</tr>
					<tr>
						<th scope="row"><label for="ds_op_default_after">Default added to the end</label></th>
						<td><input type="number" id="ds_op_default_after" name="ds_op_default_after" min="0" max="240" step="5" value="<?php echo (int) $s['default_after']; ?>" class="small-text"> seconds</td>
					</tr>
					<tr>
						<th scope="row"><label for="ds_op_max_upload_mb">Maximum upload size</label></th>
						<td><input type="number" id="ds_op_max_upload_mb" name="ds_op_max_upload_mb" min="1" max="200" value="<?php echo (int) $s['max_upload_mb']; ?>" class="small-text"> MB</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="ds_op_save" value="1" class="button button-primary">Save changes</button>
				</p>
			</form>
		</div>
		<?php
	}
}
