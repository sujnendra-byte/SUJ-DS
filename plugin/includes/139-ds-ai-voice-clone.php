<?php
/**
 * Code Snippets export — DS AI Voice Clone
 *
 * Snippet ID : 815
 * Active     : yes
 * Priority   : 1000
 * Scope      : global
 * Tags       : designstager, ds-order-129
 * Modified   : 2026-09-09 02:45:29
 * Load order : 139 of 144
 */
/**
 * DesignStager module: DS AI Voice Clone
 *
 * Folded in from the standalone "DS AI Voice Clone" plugin.
 * Execution order 129 of 130 (priority 1000, scope global).
 *
 * Adds the Voice Clone tab to the AI Audio studio (WaveSpeed qwen3-tts). Folded in from the standalone DS AI Voice Clone plugin v1.0.9.
 *
 * Migration note: Only the WordPress plugin header was removed; the code is otherwise byte-identical to ds-ai-voice-clone.php v1.0.9.
 *
 * @package DesignStager
 */

defined( 'ABSPATH' ) || exit;

/* ---------- original plugin code ---------- */

/**
 * DesignStager module: DS AI Voice Clone
 *
 * Adds a 4th tool — "Voice Clone" — to the existing AI Audio Studio page
 * ([ds_ai_audio], /ai-studio/ai-audio/) without modifying ANY existing
 * module. Everything here is additive:
 *
 *   - New REST routes under the existing ds/v1 namespace:
 *       POST ds/v1/voice-sample        upload a reference voice sample
 *       GET  ds/v1/my-voices           list the user's saved voices
 *       POST ds/v1/delete-voice        remove a saved voice
 *       POST ds/v1/generate-voice-clone   start a cloned-voice generation
 *       POST ds/v1/poll-voice-clone       poll it (delegates to the
 *                                          existing ds_audio_poll_common)
 *
 *   - Credits: uses the SAME shared pool and the SAME functions as every
 *     other AI tool on the site (ds_get_credits / ds_deduct_credits).
 *     Cost defaults to the existing Text-to-Speech tier cost
 *     (ds_get_speech_cost) and can be multiplied / topped up from
 *     Settings -> DS Voice Clone.
 *
 *   - History: writes into the SAME user meta the audio studio already
 *     uses, 'ds_ai_audio_generations', with kind 'speech' plus a
 *     'sub' => 'voice_clone' marker. That means the existing My Creations
 *     feed, the "Save"/expiry logic, the R2 rehost cron, the marketplace
 *     submit popup and the "resume pending after refresh" module all work
 *     on cloned audio with zero changes.
 *
 *   - Refunds: handled by the existing ds_audio_poll_common() refund-once
 *     logic, because the poll route delegates to it.
 *
 *   - Provider: WaveSpeed AI, model wavespeed-ai/qwen3-tts/voice-clone
 *     (zero-shot cloning: reference audio + text -> speech in that voice).
 *     Reuses the existing WaveSpeed key (option ds_wavespeed_api_key) via
 *     ds_audio_get_wavespeed_key().
 *
 * Consent: cloning requires an explicit consent flag, enforced server-side
 * on both the sample upload and every generation, and the consent event is
 * stored with the voice record.
 *
 * @package DesignStager
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * 1. SETTINGS
 * ===================================================================== */

if ( ! function_exists( 'ds_vc_settings' ) ) {
	/**
	 * Voice Clone settings with safe defaults.
	 *
	 * @return array
	 */
	function ds_vc_settings() {
		$defaults = array(
			'enabled'           => 1,     // Master on/off for the tab.
			'cost_mode'         => 'block', // 'block' = per N characters, 'flat' = fixed per clip.
			'block_chars'       => 200,   // Characters per charged block.
			'credits_per_block' => 2,     // Credits charged per block (or part of one).
			'flat_cost'         => 8,     // Credits per generation when cost_mode = flat.
			'max_chars'         => 10000, // Safety ceiling only — the real limit is the credit balance.
			'max_voices'        => 10,    // Saved reference voices per user.
			'max_upload_mb'     => 12,    // Reference sample size cap.
		);

		$saved = get_option( 'ds_voice_clone_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$out = array_merge( $defaults, $saved );

		$out['enabled']           = (int) $out['enabled'] ? 1 : 0;
		$out['cost_mode']         = ( 'flat' === $out['cost_mode'] ) ? 'flat' : 'block';
		$out['block_chars']       = max( 10, min( 5000, (int) $out['block_chars'] ) );
		$out['credits_per_block'] = max( 1, (int) $out['credits_per_block'] );
		$out['flat_cost']         = max( 1, (int) $out['flat_cost'] );
		$out['max_chars']         = max( 200, min( 100000, (int) $out['max_chars'] ) );
		$out['max_voices']        = max( 1, min( 50, (int) $out['max_voices'] ) );
		$out['max_upload_mb']     = max( 1, min( 50, (int) $out['max_upload_mb'] ) );

		return $out;
	}
}

if ( ! function_exists( 'ds_vc_get_cost' ) ) {
	/**
	 * Credit cost for one cloned-voice generation.
	 *
	 * @param string $text Script to synthesise.
	 * @return int
	 */
	function ds_vc_get_cost( $text ) {
		$s = ds_vc_settings();

		if ( 'flat' === $s['cost_mode'] ) {
			$cost = $s['flat_cost'];
		} else {
			// Per block of characters, rounding any part of a block up.
			$chars  = max( 1, mb_strlen( (string) $text ) );
			$blocks = (int) ceil( $chars / $s['block_chars'] );
			$cost   = $blocks * $s['credits_per_block'];
		}

		return (int) apply_filters( 'ds_voice_clone_cost_calculated', max( 1, (int) $cost ), $text );
	}
}

/* =====================================================================
 * 2. VOICE LIBRARY (per user, stored in user meta ds_ai_voices)
 * ===================================================================== */

if ( ! function_exists( 'ds_vc_get_voices' ) ) {
	/**
	 * All saved reference voices for a user.
	 *
	 * @param int $uid User ID.
	 * @return array
	 */
	function ds_vc_get_voices( $uid ) {
		$voices = get_user_meta( $uid, 'ds_ai_voices', true );
		if ( ! is_array( $voices ) ) {
			$voices = array();
		}
		return $voices;
	}
}

if ( ! function_exists( 'ds_vc_find_voice' ) ) {
	/**
	 * Look up one saved voice by its ID.
	 *
	 * @param int    $uid      User ID.
	 * @param string $voice_id Voice ID.
	 * @return array|null
	 */
	function ds_vc_find_voice( $uid, $voice_id ) {
		foreach ( ds_vc_get_voices( $uid ) as $v ) {
			if ( isset( $v['id'] ) && (string) $v['id'] === (string) $voice_id ) {
				return $v;
			}
		}
		return null;
	}
}

/* =====================================================================
 * 2b. PUBLIC SHARE LINKS
 *
 * The studio's share button copies the item's url, which for stored audio
 * is /wp-json/ds-hub/v1/file/<id> — a login-only route, so anyone else
 * opening it gets a 401. These helpers mint an unguessable public link
 * instead, signed with the site's auth salt so nothing has to be stored.
 * ===================================================================== */

if ( ! function_exists( 'ds_vc_share_sig' ) ) {
	/**
	 * Signature for one user's file.
	 *
	 * @param int $uid User ID.
	 * @param int $fid File ID.
	 * @return string
	 */
	function ds_vc_share_sig( $uid, $fid ) {
		return substr( hash_hmac( 'sha256', 'ds-vc-share|' . (int) $uid . '|' . (int) $fid, wp_salt( 'auth' ) ), 0, 24 );
	}
}

if ( ! function_exists( 'ds_vc_share_url' ) ) {
	/**
	 * Public, no-login URL for one of a user's audio files.
	 *
	 * @param int $uid User ID.
	 * @param int $fid File ID.
	 * @return string
	 */
	function ds_vc_share_url( $uid, $fid ) {
		$token = (int) $uid . '.' . (int) $fid . '.' . ds_vc_share_sig( $uid, $fid );
		return add_query_arg( 't', $token, rest_url( 'ds/v1/shared-audio' ) );
	}
}

if ( ! function_exists( 'ds_vc_find_own_file' ) ) {
	/**
	 * Find one of the user's own generations by its stored file ID.
	 *
	 * @param int $uid User ID.
	 * @param int $fid File ID.
	 * @return array|null
	 */
	function ds_vc_find_own_file( $uid, $fid ) {
		$gens = get_user_meta( $uid, 'ds_ai_audio_generations', true );
		if ( ! is_array( $gens ) ) {
			return null;
		}
		foreach ( $gens as $g ) {
			if ( is_array( $g ) && (int) ( isset( $g['file_id'] ) ? $g['file_id'] : 0 ) === (int) $fid ) {
				return $g;
			}
		}
		return null;
	}
}

/* =====================================================================
 * 3. WAVESPEED CALL — qwen3-tts voice clone (zero-shot)
 * ===================================================================== */

if ( ! function_exists( 'ds_vc_call_wavespeed_clone' ) ) {
	/**
	 * Submit a voice-clone generation task to WaveSpeed.
	 *
	 * @param string $audio_url Public URL of the reference sample.
	 * @param string $text      Script to speak.
	 * @param string $ref_text  Optional transcript of the reference sample.
	 * @param string $language  Language hint or 'auto'.
	 * @return array {prediction_id} or {error}
	 */
	function ds_vc_call_wavespeed_clone( $audio_url, $text, $ref_text = '', $language = 'auto' ) {

		$key = function_exists( 'ds_audio_get_wavespeed_key' )
			? ds_audio_get_wavespeed_key()
			: get_option( 'ds_wavespeed_api_key', '' );

		if ( empty( $key ) ) {
			return array( 'error' => 'WaveSpeed API key not set. Add it on Settings -> AI Video (WaveSpeed) — the same key is shared by AI Audio.' );
		}

		$text = trim( (string) $text );
		if ( '' === $text ) {
			return array( 'error' => 'Text is required.' );
		}

		if ( empty( $audio_url ) || ! filter_var( $audio_url, FILTER_VALIDATE_URL ) ) {
			return array( 'error' => 'Reference voice sample is missing or invalid.' );
		}

		$allowed_langs = array( 'auto', 'Chinese', 'English', 'German', 'Italian', 'Portuguese', 'Spanish', 'Japanese', 'Korean', 'French', 'Russian' );
		if ( ! in_array( $language, $allowed_langs, true ) ) {
			$language = 'auto';
		}

		$payload = array(
			'audio'    => esc_url_raw( $audio_url ),
			'text'     => $text,
			'language' => $language,
		);

		$ref_text = trim( (string) $ref_text );
		if ( '' !== $ref_text ) {
			$payload['reference_text'] = mb_substr( $ref_text, 0, 500 );
		}

		$endpoint = 'https://api.wavespeed.ai/api/v3/wavespeed-ai/qwen3-tts/voice-clone';

		$resp = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 45,
			)
		);

		if ( is_wp_error( $resp ) ) {
			return array( 'error' => $resp->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );

		if ( $code < 200 || $code >= 300 ) {
			$msg = '';
			if ( is_array( $body ) ) {
				if ( ! empty( $body['message'] ) ) {
					$msg = (string) $body['message'];
				} elseif ( ! empty( $body['error'] ) ) {
					$msg = is_string( $body['error'] ) ? $body['error'] : wp_json_encode( $body['error'] );
				}
			}
			if ( '' === $msg ) {
				$msg = 'Voice clone service returned HTTP ' . $code . '.';
			}
			return array( 'error' => $msg );
		}

		$pid = '';
		if ( is_array( $body ) && isset( $body['data']['id'] ) ) {
			$pid = (string) $body['data']['id'];
		} elseif ( is_array( $body ) && isset( $body['id'] ) ) {
			$pid = (string) $body['id'];
		}

		if ( '' === $pid ) {
			return array( 'error' => 'No task ID returned by the voice clone service.' );
		}

		return array( 'prediction_id' => $pid );
	}
}

/* =====================================================================
 * 4. REST ROUTES
 * ===================================================================== */

add_action(
	'rest_api_init',
	function () {

		// ── Upload a reference voice sample ──────────────────────────
		register_rest_route(
			'ds/v1',
			'/voice-sample',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {

					$s   = ds_vc_settings();
					$uid = get_current_user_id();

					if ( ! $s['enabled'] ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Voice cloning is currently disabled.' ) );
					}

					$consent = $req->get_param( 'consent' );
					if ( ! ( '1' === (string) $consent || 'true' === (string) $consent || true === $consent ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'You must confirm you own this voice or have written permission to clone it.' ) );
					}

					$voices = ds_vc_get_voices( $uid );
					if ( count( $voices ) >= $s['max_voices'] ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'You have reached the limit of ' . $s['max_voices'] . ' saved voices. Delete one first.' ) );
					}

					$files = $req->get_file_params();
					if ( empty( $files['sample'] ) || empty( $files['sample']['tmp_name'] ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'No audio file received.' ) );
					}

					$file = $files['sample'];

					if ( ! empty( $file['error'] ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Upload failed (code ' . (int) $file['error'] . ').' ) );
					}

					$max_bytes = $s['max_upload_mb'] * 1024 * 1024;
					if ( (int) $file['size'] > $max_bytes ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Sample is too large. Maximum ' . $s['max_upload_mb'] . ' MB.' ) );
					}
					if ( (int) $file['size'] < 1024 ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'That file looks empty — please upload a real recording.' ) );
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

					$ext = strtolower( (string) pathinfo( $file['name'], PATHINFO_EXTENSION ) );
					if ( ! isset( $allowed[ $ext ] ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Unsupported audio format. Use MP3, WAV, M4A, OGG, WEBM or FLAC.' ) );
					}

					// Sanity-check the real bytes, then trust our own extension map.
					// WordPress rejects perfectly valid .m4a / .webm recordings because
					// finfo reports audio/x-m4a where core expects audio/mp4.
					$real_mime = function_exists( 'mime_content_type' ) ? (string) mime_content_type( $file['tmp_name'] ) : '';
					if ( '' !== $real_mime
						&& 0 !== strpos( $real_mime, 'audio/' )
						&& 0 !== strpos( $real_mime, 'video/' )
						&& 'application/octet-stream' !== $real_mime ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'That file does not look like an audio recording.' ) );
					}

					// media.php is required too: wp_generate_attachment_metadata() calls
					// wp_read_audio_metadata() for audio files, which lives there.
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';

					// Random, unguessable filename — the reference sample has to be
					// publicly fetchable for the model, so it must not be enumerable.
					$file['name'] = 'ds-voice-' . wp_generate_password( 20, false, false ) . '.' . $ext;

					// The filename above is always one of our own safe extensions, so
					// forcing the type here cannot smuggle an executable file through.
					$forced_type = array(
						'ext'             => $ext,
						'type'            => $allowed[ $ext ],
						'proper_filename' => false,
					);
					$force_cb = function () use ( $forced_type ) {
						return $forced_type;
					};
					add_filter( 'wp_check_filetype_and_ext', $force_cb, 99 );

					$moved = wp_handle_upload(
						$file,
						array(
							'test_form' => false,
							'mimes'     => $allowed,
						)
					);

					remove_filter( 'wp_check_filetype_and_ext', $force_cb, 99 );

					if ( ! is_array( $moved ) || ! empty( $moved['error'] ) ) {
						$msg = is_array( $moved ) && ! empty( $moved['error'] ) ? $moved['error'] : 'Could not save the file.';
						return rest_ensure_response( array( 'error' => true, 'message' => $msg ) );
					}

					$name = sanitize_text_field( (string) $req->get_param( 'name' ) );
					if ( '' === $name ) {
						$name = 'My voice ' . ( count( $voices ) + 1 );
					}
					$name = mb_substr( $name, 0, 40 );

					$attach_id = wp_insert_attachment(
						array(
							'post_mime_type' => $moved['type'],
							'post_title'     => 'Voice sample — ' . $name,
							'post_content'   => '',
							'post_status'    => 'inherit',
							'post_author'    => $uid,
						),
						$moved['file']
					);

					if ( ! is_wp_error( $attach_id ) && $attach_id ) {
						// Metadata is a nice-to-have — never let it break the upload.
						if ( function_exists( 'wp_generate_attachment_metadata' ) && function_exists( 'wp_read_audio_metadata' ) ) {
							$att_meta = wp_generate_attachment_metadata( $attach_id, $moved['file'] );
							if ( is_array( $att_meta ) ) {
								wp_update_attachment_metadata( $attach_id, $att_meta );
							}
						}
						update_post_meta( $attach_id, '_ds_voice_sample_owner', $uid );
					} else {
						$attach_id = 0;
					}

					$voice = array(
						'id'            => 'v' . time() . wp_generate_password( 6, false, false ),
						'name'          => $name,
						'url'           => $moved['url'],
						'attachment_id' => (int) $attach_id,
						'ref_text'      => mb_substr( sanitize_textarea_field( (string) $req->get_param( 'ref_text' ) ), 0, 500 ),
						'created'       => current_time( 'Y-m-d H:i:s' ),
						'consent'       => array(
							'given' => 1,
							'at'    => current_time( 'mysql' ),
							'ip'    => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
						),
					);

					array_unshift( $voices, $voice );
					update_user_meta( $uid, 'ds_ai_voices', array_slice( $voices, 0, $s['max_voices'] ) );

					return rest_ensure_response(
						array(
							'success' => true,
							'voice'   => array(
								'id'       => $voice['id'],
								'name'     => $voice['name'],
								'url'      => $voice['url'],
								'ref_text' => $voice['ref_text'],
								'created'  => $voice['created'],
							),
						)
					);
				},
			)
		);

		// ── List saved voices ────────────────────────────────────────
		register_rest_route(
			'ds/v1',
			'/my-voices',
			array(
				'methods'             => 'GET',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function () {
					$uid  = get_current_user_id();
					$out  = array();
					foreach ( ds_vc_get_voices( $uid ) as $v ) {
						$out[] = array(
							'id'       => isset( $v['id'] ) ? $v['id'] : '',
							'name'     => isset( $v['name'] ) ? $v['name'] : 'Voice',
							'url'      => isset( $v['url'] ) ? $v['url'] : '',
							'ref_text' => isset( $v['ref_text'] ) ? $v['ref_text'] : '',
							'created'  => isset( $v['created'] ) ? $v['created'] : '',
						);
					}
					return rest_ensure_response( array( 'success' => true, 'voices' => $out ) );
				},
			)
		);

		// ── Delete a saved voice ─────────────────────────────────────
		register_rest_route(
			'ds/v1',
			'/delete-voice',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {
					$uid      = get_current_user_id();
					$voice_id = sanitize_text_field( (string) $req->get_param( 'voice_id' ) );
					if ( '' === $voice_id ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'No voice specified.' ) );
					}

					$voices = ds_vc_get_voices( $uid );
					$kept   = array();
					$found  = null;

					foreach ( $voices as $v ) {
						if ( isset( $v['id'] ) && (string) $v['id'] === $voice_id ) {
							$found = $v;
							continue;
						}
						$kept[] = $v;
					}

					if ( null === $found ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Voice not found.' ) );
					}

					update_user_meta( $uid, 'ds_ai_voices', $kept );

					// Only delete the media file if it really belongs to this user.
					if ( ! empty( $found['attachment_id'] ) ) {
						$owner = (int) get_post_meta( (int) $found['attachment_id'], '_ds_voice_sample_owner', true );
						if ( $owner === $uid ) {
							wp_delete_attachment( (int) $found['attachment_id'], true );
						}
					}

					return rest_ensure_response( array( 'success' => true ) );
				},
			)
		);

		// ── Generate speech in a cloned voice ────────────────────────
		register_rest_route(
			'ds/v1',
			'/generate-voice-clone',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {

					$s   = ds_vc_settings();
					$uid = get_current_user_id();

					if ( ! $s['enabled'] ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Voice cloning is currently disabled.' ) );
					}

					$consent = $req->get_param( 'consent' );
					if ( ! ( '1' === (string) $consent || 'true' === (string) $consent || true === $consent ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Please confirm you have the right to use this voice.' ) );
					}

					$text = trim( (string) $req->get_param( 'text' ) );
					if ( '' === $text ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Please enter some text to speak in the cloned voice.' ) );
					}
					if ( mb_strlen( $text ) > $s['max_chars'] ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'That script is very long — please keep it under ' . number_format_i18n( $s['max_chars'] ) . ' characters and split longer pieces into parts.' ) );
					}

					$voice_id = sanitize_text_field( (string) $req->get_param( 'voice_id' ) );
					$voice    = ds_vc_find_voice( $uid, $voice_id );
					if ( ! $voice || empty( $voice['url'] ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Pick a saved voice first, or upload a new sample.' ) );
					}

					$language = sanitize_text_field( (string) $req->get_param( 'language' ) );
					if ( '' === $language ) {
						$language = 'auto';
					}

					$ref_text = (string) $req->get_param( 'ref_text' );
					if ( '' === trim( $ref_text ) && ! empty( $voice['ref_text'] ) ) {
						$ref_text = $voice['ref_text'];
					}

					$cost    = ds_vc_get_cost( $text );
					$credits = function_exists( 'ds_get_credits' ) ? ds_get_credits( $uid ) : 0;

					if ( $credits < $cost ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'Not enough credits. Need ' . $cost . '.' ) );
					}

					$result = ds_vc_call_wavespeed_clone( $voice['url'], $text, $ref_text, $language );

					if ( ! empty( $result['error'] ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => $result['error'] ) );
					}
					if ( empty( $result['prediction_id'] ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'No task ID returned.' ) );
					}

					// Charge only after the task was accepted upstream.
					if ( function_exists( 'ds_deduct_credits' ) ) {
						ds_deduct_credits( $uid, $cost );
					}

					$label = 'Voice clone (' . $voice['name'] . ') — ' . mb_substr( sanitize_text_field( $text ), 0, 40 );

					$gens = get_user_meta( $uid, 'ds_ai_audio_generations', true );
					if ( ! is_array( $gens ) ) {
						$gens = array();
					}

					array_unshift(
						$gens,
						array(
							'prediction_id' => $result['prediction_id'],
							'kind'          => 'speech',       // Keeps the existing feed, filters and rehost logic working.
							'sub'           => 'voice_clone',  // Marks it as a clone for credit history / reporting.
							'label'         => mb_substr( $label, 0, 60 ),
							'voice_id'      => $voice['id'],
							'voice_name'    => $voice['name'],
							'credits'       => $cost,
							'date'          => current_time( 'Y-m-d H:i:s' ),
							'status'        => 'pending',
							'url'           => '',
						)
					);

					update_user_meta( $uid, 'ds_ai_audio_generations', array_slice( $gens, 0, 200 ) );

					do_action( 'ds_voice_clone_started', $uid, $result['prediction_id'], $cost, $voice['id'] );

					$credits_after = function_exists( 'ds_get_credits' ) ? ds_get_credits( $uid ) : ( $credits - $cost );

					return rest_ensure_response(
						array(
							'prediction_id' => $result['prediction_id'],
							'credits_left'  => $credits_after,
							'cost'          => $cost,
							'label'         => mb_substr( $label, 0, 60 ),
						)
					);
				},
			)
		);

		// ── Create a public share link for one of my audio files ────
		register_rest_route(
			'ds/v1',
			'/share-audio',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {

					$uid = get_current_user_id();
					$fid = (int) $req->get_param( 'file_id' );

					if ( $fid <= 0 ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'No file specified.' ) );
					}

					// Ownership: the file must appear in this user's own history.
					if ( ! ds_vc_find_own_file( $uid, $fid ) ) {
						return rest_ensure_response( array( 'error' => true, 'message' => 'That file is not yours to share.' ) );
					}

					return rest_ensure_response(
						array(
							'success' => true,
							'url'     => ds_vc_share_url( $uid, $fid ),
						)
					);
				},
			)
		);

		// ── Public playback of a shared file (no login needed) ───────
		register_rest_route(
			'ds/v1',
			'/shared-audio',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function ( WP_REST_Request $req ) {

					$parts = explode( '.', (string) $req->get_param( 't' ) );
					if ( count( $parts ) !== 3 ) {
						return new WP_Error( 'ds_vc_bad_link', 'This share link is not valid.', array( 'status' => 404 ) );
					}

					$uid = (int) $parts[0];
					$fid = (int) $parts[1];
					$sig = (string) $parts[2];

					if ( $uid <= 0 || $fid <= 0 || ! hash_equals( ds_vc_share_sig( $uid, $fid ), $sig ) ) {
						return new WP_Error( 'ds_vc_bad_link', 'This share link is not valid.', array( 'status' => 404 ) );
					}

					$entry = ds_vc_find_own_file( $uid, $fid );
					if ( ! $entry ) {
						return new WP_Error( 'ds_vc_gone', 'This audio is no longer available.', array( 'status' => 404 ) );
					}

					$target = '';

					// 1. A media-library copy is already public.
					if ( ! empty( $entry['attachment_id'] ) ) {
						$att = wp_get_attachment_url( (int) $entry['attachment_id'] );
						if ( $att ) {
							$target = $att;
						}
					}

					// 2. A stored URL that isn't one of our login-only REST routes.
					if ( '' === $target && ! empty( $entry['url'] ) && false === strpos( $entry['url'], '/wp-json/' ) ) {
						$target = $entry['url'];
					}

					if ( '' !== $target ) {
						wp_redirect( esc_url_raw( $target ), 302 );
						exit;
					}

					// 3. Fall back to the hub route, fetched as the owner.
					$previous = get_current_user_id();
					wp_set_current_user( $uid );

					$inner = new WP_REST_Request( 'GET', '/ds-hub/v1/file/' . $fid );
					ob_start();
					$resp = rest_do_request( $inner );
					$echoed = ob_get_clean();

					wp_set_current_user( $previous );

					if ( '' !== $echoed ) {
						header( 'Content-Type: audio/mpeg' );
						header( 'Cache-Control: public, max-age=3600' );
						echo $echoed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						exit;
					}

					if ( ! is_wp_error( $resp ) && ! $resp->is_error() ) {
						$data = $resp->get_data();
						if ( is_string( $data ) && '' !== $data ) {
							header( 'Content-Type: audio/mpeg' );
							echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							exit;
						}
						if ( is_array( $data ) && ! empty( $data['url'] ) ) {
							wp_redirect( esc_url_raw( $data['url'] ), 302 );
							exit;
						}
					}

					return new WP_Error( 'ds_vc_gone', 'This audio could not be loaded.', array( 'status' => 404 ) );
				},
			)
		);

		// ── Poll a clone job (reuses the shared audio poller) ────────
		register_rest_route(
			'ds/v1',
			'/poll-voice-clone',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function ( WP_REST_Request $req ) {
					if ( ! function_exists( 'ds_audio_poll_common' ) ) {
						return rest_ensure_response( array( 'status' => 'error', 'error' => 'Audio poll function not available.' ) );
					}
					// 'speech' keeps refund, rehost and history handling identical
					// to the existing Text to Speech flow.
					return ds_audio_poll_common( $req, 'speech' );
				},
			)
		);
	}
);

/* =====================================================================
 * 5. FRONTEND — inject the Voice Clone tab into the AI Audio studio
 * ===================================================================== */

if ( ! function_exists( 'ds_vc_is_audio_page' ) ) {
	/**
	 * True only on the page that renders [ds_ai_audio].
	 *
	 * @return bool
	 */
	function ds_vc_is_audio_page() {
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
			$path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
			if ( $path && false !== strpos( $path, 'ai-audio' ) ) {
				$is = true;
			}
		}

		return (bool) apply_filters( 'ds_voice_clone_is_audio_page', $is );
	}
}

add_action(
	'wp_footer',
	function () {

		if ( ! ds_vc_is_audio_page() || ! is_user_logged_in() ) {
			return;
		}

		$s = ds_vc_settings();
		if ( ! $s['enabled'] ) {
			return;
		}

		$config = array(
			'rest'            => esc_url_raw( rest_url( 'ds/v1/' ) ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'costMode'        => $s['cost_mode'],
			'blockChars'      => (int) $s['block_chars'],
			'creditsPerBlock' => (int) $s['credits_per_block'],
			'flatCost'        => (int) $s['flat_cost'],
			'maxChars'        => (int) $s['max_chars'],
			'maxVoices'       => (int) $s['max_voices'],
			'maxUpload'       => (int) $s['max_upload_mb'],
		);
		?>
<style id="dsvc-css">
.dsvcRow{display:flex;gap:8px;align-items:center}
.dsvcUpload{border:1px dashed #334155;border-radius:10px;padding:14px;text-align:center;cursor:pointer;color:#94a3b8;font-size:12px;background:rgba(255,255,255,.02)}
.dsvcUpload:hover{border-color:#2563eb;color:#cbd5e1}
.dsvcUpload.has{border-style:solid;border-color:#16a34a;color:#86efac}
.dsvcVoiceItem{display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid #1e293b;border-radius:8px;margin-bottom:6px;background:rgba(255,255,255,.02)}
.dsvcVoiceItem audio{height:28px;flex:1;min-width:0}
.dsvcVoiceName{font-size:12px;color:#cbd5e1;font-weight:600;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dsvcDel{background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:2px 6px}
.dsvcConsent{display:flex;gap:8px;align-items:flex-start;font-size:11px;color:#94a3b8;line-height:1.45;margin:10px 0 4px}
.dsvcConsent input{margin-top:2px;flex:0 0 auto}
.dsvcNote{font-size:11px;color:#64748b;margin-top:6px}
.dsvcAddLink{margin-top:10px;background:none;border:1px dashed #334155;border-radius:8px;color:#93c5fd;font-size:12px;font-weight:600;padding:8px 12px;cursor:pointer;width:100%}
.dsvcAddLink:hover{border-color:#2563eb;color:#bfdbfe}
#dsvcAddPanel{border:1px solid #1e293b;border-radius:10px;padding:12px;margin-top:10px;background:rgba(255,255,255,.02)}
.dsvcPanelHead{display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:700;color:#cbd5e1;margin-bottom:4px}
.dsvcCancel{background:none;border:none;color:#94a3b8;font-size:11px;cursor:pointer;padding:2px 4px;text-decoration:underline}
#dsvcSaveVoiceBtn{margin-top:10px}
#dsvcSaveVoiceBtn[disabled]{opacity:.45;cursor:not-allowed}
.dsvcReady{border:1px solid #16a34a!important;box-shadow:0 0 0 2px rgba(22,163,74,.15)}
.dsvcDivider{height:1px;background:#1e293b;margin:12px 0}
.dsvcPreviewRow{display:flex;align-items:center;gap:8px;margin-top:8px;padding:6px 10px;border:1px solid #1e293b;border-radius:8px;background:rgba(255,255,255,.02)}
.dsvcPlay{flex:0 0 auto;width:26px;height:26px;border-radius:50%;border:none;background:#2563eb;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
.dsvcPlay:hover{background:#1d4ed8}
.dsvcPreviewTxt{font-size:12px;color:#94a3b8;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dsvcPreviewTime{font-size:11px;color:#64748b;font-variant-numeric:tabular-nums}
</style>

<div id="dsvcTpl" style="display:none">
	<div class="dsaaPane" id="dsaaPane-clone">

		<div id="dsvcPick">
			<label class="dsaaLbl">Voice</label>
			<select class="dsaaSelect" id="dsvcVoice"></select>
			<div class="dsvcPreviewRow" id="dsvcPreviewRow" style="display:none">
				<button type="button" class="dsvcPlay" id="dsvcPlayBtn" title="Preview this voice">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
				</button>
				<span class="dsvcPreviewTxt" id="dsvcPreviewTxt">Preview voice</span>
				<span class="dsvcPreviewTime" id="dsvcPreviewTime"></span>
			</div>

			<button type="button" class="dsvcAddLink" id="dsvcAddBtn">+ Add a new voice</button>
		</div>

		<div id="dsvcAddPanel" style="display:none">
			<div class="dsvcPanelHead">
				<span>Voices</span>
				<button type="button" class="dsvcCancel" id="dsvcCancelBtn">Cancel</button>
			</div>

			<div id="dsvcManage" style="display:none">
				<label class="dsaaLbl">Your saved voices</label>
				<div id="dsvcVoiceList"></div>
				<div class="dsvcDivider"></div>
			</div>

			<label class="dsaaLbl">Add a new voice</label>

			<label class="dsaaLbl">Voice name</label>
			<input class="dsaaSelect" type="text" id="dsvcName" placeholder="e.g. My narration voice">

			<label class="dsaaLbl">Sample transcript (optional)</label>
			<textarea class="dsaaTextarea" id="dsvcRefText" placeholder="Type exactly what is said in the recording — improves accuracy" style="min-height:60px"></textarea>

			<div class="dsvcConsent">
				<input type="checkbox" id="dsvcConsent">
				<label for="dsvcConsent">This is my own voice, or I have written permission from the person whose voice it is. I will not use it to impersonate anyone.</label>
			</div>

			<div class="dsvcUpload" id="dsvcUpload">
				<span id="dsvcUploadTxt">Click to choose a 5–15 second clean recording (MP3, WAV, M4A)</span>
			</div>
			<input type="file" id="dsvcFile" accept="audio/*" style="display:none">

			<button class="dsaaGenBtn" id="dsvcSaveVoiceBtn" type="button" disabled>Save voice</button>
			<div class="dsvcNote">Choose a file and tick the box above, then save. Clips longer than 15 seconds are trimmed automatically — the clone works best on 5–15 seconds of clean speech.</div>
		</div>

		<label class="dsaaLbl">Script</label>
		<textarea class="dsaaTextarea" id="dsvcText" placeholder="Type what the cloned voice should say"></textarea>
		<div class="dsaaHint"><span id="dsvcTextCount">0</span> characters · <span id="dsvcCostHint">0</span> credits <span id="dsvcRate"></span></div>

		<label class="dsaaLbl">Language</label>
		<select class="dsaaSelect" id="dsvcLang">
			<option value="auto">Auto-detect</option>
			<option value="English">English</option>
			<option value="Chinese">Chinese</option>
			<option value="Spanish">Spanish</option>
			<option value="French">French</option>
			<option value="German">German</option>
			<option value="Italian">Italian</option>
			<option value="Portuguese">Portuguese</option>
			<option value="Japanese">Japanese</option>
			<option value="Korean">Korean</option>
			<option value="Russian">Russian</option>
		</select>

		<div style="flex:1"></div>
		<button class="dsaaGenBtn" id="dsvcGenBtn" type="button">Generate Voice Clone</button>
		<div class="dsaaErr" id="dsvcErr"></div>
		<div class="dsvcNote">Cloned audio is saved to My Creations and charged from the same credit balance as the rest of AI Studio.</div>
	</div>
</div>

<script>
(function () {
	var CFG = <?php echo wp_json_encode( $config ); ?>;
	var REST = CFG.rest, NONCE = CFG.nonce;
	var voices = [];
	var pendingFile = null;

	function $(id) { return document.getElementById(id); }
	function esc(s) {
		return (s || '').toString().replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	function apiJSON(path, body) {
		return fetch(REST + path, {
			method: 'POST', credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE },
			body: JSON.stringify(body || {})
		}).then(function (r) { return r.json(); });
	}
	function apiGET(path) {
		return fetch(REST + path, { credentials: 'same-origin', headers: { 'X-WP-Nonce': NONCE } })
			.then(function (r) { return r.json(); });
	}
	function toastLike(msg) {
		var t = document.getElementById('dsaaToast');
		if (t) {
			t.textContent = msg;
			t.classList.add('show');
			clearTimeout(t._dsvc);
			t._dsvc = setTimeout(function () { t.classList.remove('show'); }, 2600);
		}
	}
	function showErr(msg) { var e = $('dsvcErr'); if (e) { e.textContent = msg; e.style.display = 'block'; } }
	function hideErr() { var e = $('dsvcErr'); if (e) { e.style.display = 'none'; e.textContent = ''; } }

	function cloneCost(text) {
		var len = Math.max(1, (text || '').length);
		if (CFG.costMode === 'flat') { return Math.max(1, CFG.flatCost); }
		return Math.max(1, Math.ceil(len / CFG.blockChars) * CFG.creditsPerBlock);
	}
	function labelBtn() {
		var t = $('dsvcText') ? $('dsvcText').value : '';
		var cost = cloneCost(t);
		var b = $('dsvcGenBtn');
		if (b) { b.textContent = 'Generate Voice Clone (' + cost + ' cr)'; }
		var c = $('dsvcCostHint');
		if (c) { c.textContent = t.length ? cost : 0; }
	}
	function setCredits(n) {
		var el = document.getElementById('dsaaCreditsVal');
		if (el && typeof n !== 'undefined') { el.textContent = n; }
	}

	// ── Mount the tab + pane into the existing studio ────────────────
	function mount() {
		var tabs = document.querySelector('.dsaaTabs');
		var side = document.querySelector('.dsaaSide');
		var tpl  = $('dsvcTpl');
		if (!tabs || !side || !tpl || tabs.querySelector('[data-tab="clone"]')) { return false; }

		var pane = tpl.firstElementChild;
		side.appendChild(pane);
		tpl.parentNode.removeChild(tpl);

		var btn = document.createElement('button');
		btn.className = 'dsaaTabBtn';
		btn.type = 'button';
		btn.setAttribute('data-tab', 'clone');
		btn.textContent = 'Voice Clone';
		tabs.appendChild(btn);

		btn.addEventListener('click', function () {
			document.querySelectorAll('.dsaaTabBtn').forEach(function (b) { b.classList.remove('on'); });
			document.querySelectorAll('.dsaaPane').forEach(function (p) { p.classList.remove('on'); });
			btn.classList.add('on');
			pane.classList.add('on');
		});

		var rate = $('dsvcRate');
		if (rate) {
			rate.textContent = (CFG.costMode === 'flat')
				? '(' + CFG.flatCost + ' credits per clip)'
				: '(' + CFG.creditsPerBlock + ' credits per ' + CFG.blockChars + ' characters)';
		}
		return true;
	}

	// ── Voice library ────────────────────────────────────────────────
	function renderVoices() {
		var sel = $('dsvcVoice');
		var list = $('dsvcVoiceList');
		if (!sel || !list) { return; }

		sel.innerHTML = '';
		if (!voices.length) {
			var o = document.createElement('option');
			o.value = ''; o.textContent = 'No saved voices yet — upload one below';
			sel.appendChild(o);
		} else {
			voices.forEach(function (v) {
				var o = document.createElement('option');
				o.value = v.id; o.textContent = v.name;
				sel.appendChild(o);
			});
		}

		var manage = document.getElementById('dsvcManage');
		if (manage) { manage.style.display = voices.length ? 'block' : 'none'; }

		list.innerHTML = voices.map(function (v) {
			return '<div class="dsvcVoiceItem" data-vid="' + esc(v.id) + '">' +
				'<span class="dsvcVoiceName" title="' + esc(v.name) + '">' + esc(v.name) + '</span>' +
				'<audio controls preload="none" src="' + esc(v.url) + '"></audio>' +
				'<button class="dsvcDel" type="button" title="Delete this voice">&times;</button>' +
				'</div>';
		}).join('');

		list.querySelectorAll('.dsvcDel').forEach(function (b) {
			b.addEventListener('click', function () {
				var row = b.closest('.dsvcVoiceItem');
				var vid = row ? row.getAttribute('data-vid') : '';
				if (!vid) { return; }
				b.disabled = true;
				apiJSON('delete-voice', { voice_id: vid }).then(function (res) {
					if (res && res.success) {
						voices = voices.filter(function (v) { return v.id !== vid; });
						renderVoices();
						refreshPreview();
						if (!voices.length) { openAdd(); }
					} else {
						b.disabled = false;
						showErr((res && res.message) || 'Could not delete that voice.');
					}
				}).catch(function () { b.disabled = false; });
			});
		});
	}

	// ── Preview of the currently selected voice ──────────────────────
	var previewAudio = null;

	function stopPreview() {
		if (previewAudio) { previewAudio.pause(); previewAudio = null; }
		var b = $('dsvcPlayBtn');
		if (b) { b.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>'; }
	}

	function selectedVoice() {
		var sel = $('dsvcVoice');
		if (!sel || !sel.value) { return null; }
		for (var i = 0; i < voices.length; i++) {
			if (voices[i].id === sel.value) { return voices[i]; }
		}
		return null;
	}

	function refreshPreview() {
		stopPreview();
		var row = $('dsvcPreviewRow');
		var v = selectedVoice();
		if (!row) { return; }
		if (!v || !v.url) { row.style.display = 'none'; return; }
		row.style.display = 'flex';
		$('dsvcPreviewTxt').textContent = 'Preview "' + v.name + '"';
		$('dsvcPreviewTime').textContent = '';
	}

	function wirePreview() {
		$('dsvcVoice').addEventListener('change', refreshPreview);

		$('dsvcPlayBtn').addEventListener('click', function () {
			var v = selectedVoice();
			if (!v || !v.url) { return; }

			if (previewAudio && !previewAudio.paused) { stopPreview(); return; }

			stopPreview();
			previewAudio = new Audio(v.url);
			this.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="4" height="14"></rect><rect x="14" y="5" width="4" height="14"></rect></svg>';

			var fmt = function (s) {
				s = Math.max(0, Math.floor(s || 0));
				var m = Math.floor(s / 60), r = s % 60;
				return m + ':' + (r < 10 ? '0' : '') + r;
			};
			previewAudio.addEventListener('timeupdate', function () {
				$('dsvcPreviewTime').textContent = fmt(previewAudio.currentTime) + ' / ' + fmt(previewAudio.duration);
			});
			previewAudio.addEventListener('ended', function () {
				stopPreview();
				$('dsvcPreviewTime').textContent = '';
			});
			previewAudio.play().catch(function () { stopPreview(); });
		});
	}

	function loadVoices() {
		return apiGET('my-voices').then(function (res) {
			voices = (res && res.voices) ? res.voices : [];
			renderVoices();
			refreshPreview();
			if (voices.length) { closeAdd(); } else { openAdd(); }
		}).catch(function () { renderVoices(); openAdd(); });
	}

	// ── Upload a new sample ──────────────────────────────────────────
	function openAdd() {
		$('dsvcAddPanel').style.display = 'block';
		$('dsvcAddBtn').style.display = 'none';
		hideErr();
	}
	function closeAdd() {
		$('dsvcAddPanel').style.display = 'none';
		$('dsvcAddBtn').style.display = voices.length ? 'block' : 'none';
		$('dsvcConsent').checked = false;
		$('dsvcName').value = '';
		$('dsvcRefText').value = '';
		$('dsvcUpload').classList.remove('has');
		$('dsvcUploadTxt').textContent = 'Click to choose a 5–15 second clean recording (MP3, WAV, M4A)';
		pendingFile = null;
		$('dsvcFile').value = '';
		refreshSaveBtn();
		if (!voices.length) { openAdd(); }
	}

	// ── Trim a long sample to 15s in the browser ─────────────────────
	// The clone model wants 3–15 seconds of clean speech; longer clips make
	// the result worse. Done client-side so it needs nothing on the server.
	var MAX_SAMPLE_SECONDS = 15;

	function encodeWav(samples, sampleRate) {
		var n = samples.length;
		var buf = new ArrayBuffer(44 + n * 2);
		var dv = new DataView(buf);
		var w = function (o, s) { for (var i = 0; i < s.length; i++) { dv.setUint8(o + i, s.charCodeAt(i)); } };
		w(0, 'RIFF'); dv.setUint32(4, 36 + n * 2, true); w(8, 'WAVE');
		w(12, 'fmt '); dv.setUint32(16, 16, true); dv.setUint16(20, 1, true); dv.setUint16(22, 1, true);
		dv.setUint32(24, sampleRate, true); dv.setUint32(28, sampleRate * 2, true);
		dv.setUint16(32, 2, true); dv.setUint16(34, 16, true);
		w(36, 'data'); dv.setUint32(40, n * 2, true);
		for (var i = 0; i < n; i++) {
			var s = Math.max(-1, Math.min(1, samples[i]));
			dv.setInt16(44 + i * 2, s < 0 ? s * 0x8000 : s * 0x7fff, true);
		}
		return buf;
	}

	function trimSample(file) {
		return new Promise(function (resolve) {
			var AC = window.AudioContext || window.webkitAudioContext;
			if (!AC) { resolve({ file: file, trimmed: false }); return; }

			var fr = new FileReader();
			fr.onerror = function () { resolve({ file: file, trimmed: false }); };
			fr.onload = function () {
				var ctx;
				try { ctx = new AC(); } catch (e) { resolve({ file: file, trimmed: false }); return; }
				var done = function (out) { try { ctx.close(); } catch (e) {} resolve(out); };
				try {
					ctx.decodeAudioData(fr.result, function (buf) {
						if (buf.duration <= MAX_SAMPLE_SECONDS + 0.5) {
							done({ file: file, trimmed: false });
							return;
						}
						var sr = buf.sampleRate;
						var len = Math.floor(MAX_SAMPLE_SECONDS * sr);
						var wav = encodeWav(buf.getChannelData(0).subarray(0, len), sr);
						done({
							file: new File([wav], 'voice-sample.wav', { type: 'audio/wav' }),
							trimmed: true,
							from: Math.round(buf.duration)
						});
					}, function () { done({ file: file, trimmed: false }); });
				} catch (e) { done({ file: file, trimmed: false }); }
			};
			fr.readAsArrayBuffer(file);
		});
	}

	function refreshSaveBtn() {
		var b = $('dsvcSaveVoiceBtn');
		if (b) { b.disabled = !(pendingFile && $('dsvcConsent').checked); }
	}

	function wireUpload() {
		$('dsvcAddBtn').addEventListener('click', openAdd);
		$('dsvcCancelBtn').addEventListener('click', function () {
			if (!voices.length) { return; }
			closeAdd();
		});
		$('dsvcUpload').addEventListener('click', function () { $('dsvcFile').click(); });
		$('dsvcConsent').addEventListener('change', refreshSaveBtn);

		// Choosing a file only selects it — nothing is sent until "Save voice".
		$('dsvcFile').addEventListener('change', function () {
			hideErr();
			pendingFile = null;
			var f = this.files && this.files[0];
			if (!f) { refreshSaveBtn(); return; }

			if (f.size > CFG.maxUpload * 1024 * 1024) {
				showErr('That file is larger than ' + CFG.maxUpload + ' MB.');
				this.value = '';
				refreshSaveBtn();
				return;
			}
			if (voices.length >= CFG.maxVoices) {
				showErr('You already have ' + CFG.maxVoices + ' saved voices. Delete one first.');
				this.value = '';
				refreshSaveBtn();
				return;
			}

			pendingFile = f;
			$('dsvcUploadTxt').textContent = 'Selected: ' + f.name;
			$('dsvcUpload').classList.add('has');
			refreshSaveBtn();
		});

		$('dsvcSaveVoiceBtn').addEventListener('click', function () {
			hideErr();
			if (!pendingFile) { showErr('Choose an audio file first.'); return; }
			if (!$('dsvcConsent').checked) { showErr('Please tick the permission box before saving this voice.'); return; }

			var btn = this;
			btn.disabled = true;
			btn.textContent = 'Preparing…';

			trimSample(pendingFile).then(function (prep) {
				btn.textContent = 'Saving…';

				var fd = new FormData();
				fd.append('sample', prep.file);
				fd.append('name', $('dsvcName').value.trim());
				fd.append('ref_text', $('dsvcRefText').value.trim());
				fd.append('consent', '1');

				return fetch(REST + 'voice-sample', {
					method: 'POST', credentials: 'same-origin',
					headers: { 'X-WP-Nonce': NONCE }, body: fd
				}).then(function (r) { return r.json(); })
				.then(function (res) { return { res: res, prep: prep }; });
			})
			.then(function (out) {
				var res = out.res, prep = out.prep;
				btn.textContent = 'Save voice';
				if (res.error) {
					refreshSaveBtn();
					showErr(res.message || 'Upload failed.');
					return;
				}
				voices.unshift(res.voice);
				renderVoices();
				$('dsvcVoice').value = res.voice.id;
				refreshPreview();
				pendingFile = null;
				$('dsvcFile').value = '';
				closeAdd();
				toastLike(prep.trimmed
					? '"' + res.voice.name + '" saved (trimmed from ' + prep.from + 's to ' + MAX_SAMPLE_SECONDS + 's) — now type your script and press Generate.'
					: '"' + res.voice.name + '" saved — now type your script and press Generate.');
				var s = $('dsvcText');
				if (s) {
					s.classList.add('dsvcReady');
					s.focus();
					s.scrollIntoView({ block: 'center', behavior: 'smooth' });
					setTimeout(function () { s.classList.remove('dsvcReady'); }, 2500);
				}
			}).catch(function () {
				btn.textContent = 'Save voice';
				refreshSaveBtn();
				showErr('Upload failed — please try again.');
			});
		});
	}

	// ── Result cards in the existing My Creations feed ───────────────
	function feedList() {
		var body = document.getElementById('dsaaFeedBody');
		if (!body) { return null; }
		var list = document.getElementById('dsaaCardsList');
		if (!list) {
			body.innerHTML = '<div class="dsaaCards" id="dsaaCardsList"></div>';
			list = document.getElementById('dsaaCardsList');
		}
		return list;
	}

	function addPendingCard(pid, label) {
		var list = feedList();
		if (!list) { return null; }
		var el = document.createElement('div');
		el.className = 'dsaaCard pending';
		el.id = 'dsvcCard-' + pid;
		el.innerHTML = '<div class="dsaaCardTop"><div class="dsaaCardMeta">' +
			'<span class="dsaaCardKind speech">Voice Clone</span>' +
			'<div class="dsaaCardLabel">' + esc(label) + '</div></div></div>' +
			'<div class="dsaaPendingRow"><div class="dsaaSpin"></div><span>Cloning your voice… this usually takes under a minute</span></div>';
		list.insertBefore(el, list.firstChild);
		return el;
	}

	// Hand the finished clip over to the studio's own My Creations feed so it
	// renders with the same waveform player, badges and save / download / share
	// / delete icons as every other item — and the working download route.
	function handOverToFeed(el) {
		if (el && el.parentNode) { el.parentNode.removeChild(el); }
		var f = document.querySelector('.dsaaFilterBtn.on') || document.querySelector('.dsaaFilterBtn');
		if (f) { f.click(); }
		toastLike('Voice clone ready — it is at the top of My Creations.');
	}

	function renderFailed(el, msg) {
		el.classList.remove('pending');
		el.innerHTML = '<div class="dsaaCardTop"><div class="dsaaCardMeta">' +
			'<span class="dsaaCardKind speech">Voice Clone</span>' +
			'<div class="dsaaCardLabel">Failed</div></div></div>' +
			'<div style="font-size:12px;color:#f87171;margin-top:8px">' + esc(msg || 'Generation failed — your credits were refunded.') + '</div>';
	}

	function poll(pid, label, el, attempt) {
		if (attempt > 90) {
			var row = el.querySelector('.dsaaPendingRow span');
			if (row) { row.textContent = 'Taking longer than expected — refresh the page to check.'; }
			return;
		}
		apiJSON('poll-voice-clone', { prediction_id: pid, label: label }).then(function (res) {
			if (res.status === 'succeeded' && res.audio && res.audio[0]) {
				var row = el.querySelector('.dsaaPendingRow span');
				if (row) { row.textContent = 'Finishing up…'; }
				// Move it to permanent storage the same way the rest of the studio
				// does, then let the feed redraw it with the standard card.
				apiJSON('rehost-audio', { prediction_id: pid })
					.catch(function () { return null; })
					.then(function () { handOverToFeed(el); });
			} else if (res.status === 'failed' || res.status === 'error') {
				renderFailed(el, res.error);
			} else {
				setTimeout(function () { poll(pid, label, el, attempt + 1); }, 3000);
			}
		}).catch(function () {
			setTimeout(function () { poll(pid, label, el, attempt + 1); }, 4000);
		});
	}

	// ── Generate ─────────────────────────────────────────────────────
	function wireGenerate() {
		// No hard character limit — the credit balance is the real limit. The
		// server keeps a very high safety ceiling only.
		$('dsvcText').addEventListener('input', function () {
			$('dsvcTextCount').textContent = this.value.length;
			labelBtn();
		});

		$('dsvcGenBtn').addEventListener('click', function () {
			hideErr();
			var btn = this;
			var text = $('dsvcText').value.trim();
			var vid = $('dsvcVoice').value;

			if (!vid) { showErr('Add a voice first, then pick it in the Voice list.'); return; }
			if (!text) { showErr('Please enter the text you want spoken.'); return; }

			btn.disabled = true;
			btn.textContent = 'Generating…';

			apiJSON('generate-voice-clone', {
				voice_id: vid,
				text: text,
				language: $('dsvcLang').value,
				ref_text: $('dsvcRefText').value.trim(),
				consent: true
			}).then(function (res) {
				btn.disabled = false;
				labelBtn();
				if (res.error) { showErr(res.message || 'Something went wrong.'); return; }
				setCredits(res.credits_left);
				var el = addPendingCard(res.prediction_id, res.label || text.slice(0, 40));
				if (el) { poll(res.prediction_id, res.label || text.slice(0, 40), el, 0); }
			}).catch(function () {
				btn.disabled = false;
				labelBtn();
				showErr('Network error — please try again.');
			});
		});
	}

	// ── Share: swap the studio's login-only link for a public one ────
	// The card's share button copies item.url, which is /wp-json/ds-hub/v1/
	// file/<id> — that route needs a session, so anyone else opening it gets
	// a 401. We intercept in the capture phase, mint a signed public link and
	// share that instead. Applies to music and speech cards too.
	function wireShare() {
		document.addEventListener('click', function (e) {
			var btn = (e.target && e.target.closest) ? e.target.closest('[data-act="share"]') : null;
			if (!btn) { return; }

			var list = document.getElementById('dsaaCardsList');
			var card = btn.closest('.dsaaCard');
			if (!list || !card) { return; }

			e.preventDefault();
			e.stopImmediatePropagation();

			var cards = [].slice.call(list.querySelectorAll('.dsaaCard'));
			var index = cards.indexOf(card);
			if (index < 0) { return; }

			var f = document.querySelector('.dsaaFilterBtn.on');
			var kind = f ? (f.getAttribute('data-kind') || '') : '';

			apiGET('my-audio?kind=' + encodeURIComponent(kind) + '&offset=0&limit=' + Math.min(100, index + 1))
				.then(function (res) {
					var items = ((res && res.audio) || []).filter(function (a) { return a.status !== 'pending'; });
					var item = items[index];
					if (!item || !item.file_id) {
						toastLike('Save this clip first, then share it.');
						return null;
					}
					return apiJSON('share-audio', { file_id: item.file_id }).then(function (s) {
						if (!s || !s.success || !s.url) {
							toastLike((s && s.message) || 'Could not create a share link.');
							return null;
						}
						if (navigator.share) {
							navigator.share({ title: item.label || 'Audio', url: s.url }).catch(function () {});
						} else if (navigator.clipboard && navigator.clipboard.writeText) {
							navigator.clipboard.writeText(s.url).then(function () {
								toastLike('Public link copied — anyone with it can play this.');
							}).catch(function () { window.prompt('Copy this link:', s.url); });
						} else {
							window.prompt('Copy this link:', s.url);
						}
						return null;
					});
				})
				.catch(function () { toastLike('Could not create a share link.'); });
		}, true);
	}

	function boot() {
		if (!mount()) { return; }
		wireUpload();
		wireGenerate();
		wireShare();
		wirePreview();
		labelBtn();
		loadVoices();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { setTimeout(boot, 50); });
	} else {
		setTimeout(boot, 50);
	}
})();
</script>
		<?php
	},
	99
);

/* =====================================================================
 * 6. ADMIN SETTINGS — Settings -> DS Voice Clone
 * ===================================================================== */

add_action(
	'admin_menu',
	function () {
		add_options_page(
			'DS Voice Clone',
			'DS Voice Clone',
			'manage_options',
			'ds-voice-clone',
			'ds_vc_render_settings_page'
		);
	}
);

if ( ! function_exists( 'ds_vc_render_settings_page' ) ) {
	/**
	 * Settings screen for the Voice Clone tool.
	 */
	function ds_vc_render_settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$saved_msg = '';

		if ( isset( $_POST['ds_vc_save'] ) && check_admin_referer( 'ds_vc_settings' ) ) {
			$new = array(
				'enabled'           => isset( $_POST['ds_vc_enabled'] ) ? 1 : 0,
				'cost_mode'         => ( isset( $_POST['ds_vc_cost_mode'] ) && 'flat' === $_POST['ds_vc_cost_mode'] ) ? 'flat' : 'block',
				'block_chars'       => isset( $_POST['ds_vc_block_chars'] ) ? (int) $_POST['ds_vc_block_chars'] : 200,
				'credits_per_block' => isset( $_POST['ds_vc_credits_per_block'] ) ? (int) $_POST['ds_vc_credits_per_block'] : 2,
				'flat_cost'         => isset( $_POST['ds_vc_flat_cost'] ) ? (int) $_POST['ds_vc_flat_cost'] : 8,
				'max_chars'         => isset( $_POST['ds_vc_max_chars'] ) ? (int) $_POST['ds_vc_max_chars'] : 10000,
				'max_voices'        => isset( $_POST['ds_vc_max_voices'] ) ? (int) $_POST['ds_vc_max_voices'] : 10,
				'max_upload_mb'     => isset( $_POST['ds_vc_max_upload'] ) ? (int) $_POST['ds_vc_max_upload'] : 12,
			);
			update_option( 'ds_voice_clone_settings', $new );
			$saved_msg = 'Settings saved.';
		}

		$s = ds_vc_settings();
		?>
		<div class="wrap">
			<h1>DS Voice Clone</h1>

			<?php if ( $saved_msg ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( $saved_msg ); ?></p></div>
			<?php endif; ?>

			<p>
				Adds a <strong>Voice Clone</strong> tab to the AI Audio studio. It uses the same
				WaveSpeed API key, the same credit pool and the same My Creations history as the
				rest of AI Studio.
			</p>
			<p>
				<strong>Current price:</strong>
				<?php if ( 'flat' === $s['cost_mode'] ) : ?>
					<code><?php echo (int) $s['flat_cost']; ?> credits</code> per clip, any length.
				<?php else : ?>
					<code><?php echo (int) $s['credits_per_block']; ?> credits</code> per
					<code><?php echo (int) $s['block_chars']; ?></code> characters, rounded up.
					A 500-character script costs
					<code><?php echo (int) ( ceil( 500 / max( 1, (int) $s['block_chars'] ) ) * (int) $s['credits_per_block'] ); ?></code> credits.
				<?php endif; ?>
			</p>
			<p class="description">
				There is no separate length limit for users — they can write as long a script as
				their credit balance covers. The ceiling below is only a safety stop.
			</p>

			<form method="post">
				<?php wp_nonce_field( 'ds_vc_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">Enable Voice Clone</th>
						<td>
							<label>
								<input type="checkbox" name="ds_vc_enabled" value="1" <?php checked( 1, $s['enabled'] ); ?>>
								Show the Voice Clone tab on the AI Audio page
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">Pricing mode</th>
						<td>
							<label style="margin-right:16px">
								<input type="radio" name="ds_vc_cost_mode" value="block" <?php checked( 'block', $s['cost_mode'] ); ?>>
								Per block of characters
							</label>
							<label>
								<input type="radio" name="ds_vc_cost_mode" value="flat" <?php checked( 'flat', $s['cost_mode'] ); ?>>
								Flat cost per clip
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">Credits per block</th>
						<td>
							<input type="number" min="1" name="ds_vc_credits_per_block" value="<?php echo esc_attr( $s['credits_per_block'] ); ?>" class="small-text">
							credits per
							<input type="number" min="10" max="5000" name="ds_vc_block_chars" value="<?php echo esc_attr( $s['block_chars'] ); ?>" class="small-text">
							characters
							<p class="description">
								Any part of a block counts as a whole block — at 2 credits per 200
								characters, a 201-character script costs 4 credits.
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Flat cost (credits)</th>
						<td><input type="number" min="1" name="ds_vc_flat_cost" value="<?php echo esc_attr( $s['flat_cost'] ); ?>" class="small-text"> <span class="description">Used only in flat mode.</span></td>
					</tr>
					<tr>
						<th scope="row">Safety ceiling (characters)</th>
						<td>
							<input type="number" min="200" max="100000" name="ds_vc_max_chars" value="<?php echo esc_attr( $s['max_chars'] ); ?>" class="small-text">
							<p class="description">
								Not a user quota — credits are the real limit. This only stops a
								runaway paste from making one enormous API call.
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Saved voices per user</th>
						<td><input type="number" min="1" max="50" name="ds_vc_max_voices" value="<?php echo esc_attr( $s['max_voices'] ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th scope="row">Max sample upload (MB)</th>
						<td><input type="number" min="1" max="50" name="ds_vc_max_upload" value="<?php echo esc_attr( $s['max_upload_mb'] ); ?>" class="small-text"></td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" name="ds_vc_save" value="1" class="button button-primary">Save Changes</button>
				</p>
			</form>
		</div>
		<?php
	}
}
