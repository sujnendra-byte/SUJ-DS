<?php
/**
 * Code Snippets export — DS Storage & API Settings (R2 + Retention) v3 [CLONE] [CLONE] [CLONE] [Tier-Based Save Cap]
 *
 * Snippet ID : 755
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-069
 * Modified   : 2026-10-03 15:46:36
 * Load order : 69 of 218
 */
/**
 * Snippet: DS Storage & API Settings (R2 + Retention) — Phase 1
 * Run: Everywhere (admin UI + AJAX only; no public output)
 *
 * Adds:
 *   WordPress Admin -> Designstager -> Settings          (Cloudflare R2 credentials + Test Connection)
 *   WordPress Admin -> Designstager -> AI API Settings    (dynamic per-provider AI API keys)
 *   WordPress Admin -> Designstager -> Storage Settings   (retention / expiration policy)
 *
 * Security:
 *   - All secrets (R2 keys, AI API keys) are encrypted at rest with AES-256-CBC.
 *   - The encryption key is derived from WordPress's own AUTH_KEY / SECURE_AUTH_KEY
 *     salts (already unique, server-side-only secrets defined in wp-config.php) —
 *     nothing new needs to be added to wp-config.php. If you later add a dedicated
 *     constant DS_HUB_ENC_KEY to wp-config.php, it is folded into the derivation too.
 *   - Decrypted secrets are NEVER echoed back into HTML, JS, or REST responses.
 *     Saved fields always render as an empty password input with a masked
 *     placeholder (e.g. "Current: ••••••••1234 (leave blank to keep)").
 *   - Options are NOT registered with register_setting()/show_in_rest, so they
 *     are not reachable via the REST API.
 *
 * This snippet is Phase 1 of a phased rollout: it builds the settings/vault/R2
 * client/DB table foundation only. It does NOT touch any existing AI generation
 * code path yet — nothing about how images/presentations/videos are currently
 * generated or stored changes when this snippet is activated.
 *
 * Shared helpers other snippets (Phase 2+) can call once wired in:
 *   ds_hub_get_r2_client()                -> Ds_R2_Client|WP_Error
 *   ds_hub_secure_get_key( $slug, $fallback = '' )
 *   ds_hub_get_provider( $slug )
 *   ds_hub_upload_generated_file( $local_path, $args )
 *   ds_hub_get_retention_days()
 *   ds_hub_get_saved_retention_days()      // null = permanent
 *   ds_hub_format_expiry_notice( $expires_at_gmt )
 */
if ( ! defined('ABSPATH') ) exit;

/* =====================================================================
 * 1. ENCRYPTION VAULT
 * ===================================================================== */

if ( ! function_exists('ds_hub_encryption_key') ) {
	function ds_hub_encryption_key() {
		$material = ( defined('DS_HUB_ENC_KEY') ? DS_HUB_ENC_KEY : '' )
			. ( defined('AUTH_KEY') ? AUTH_KEY : '' )
			. ( defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : '' )
			. ( defined('AUTH_SALT') ? AUTH_SALT : '' );
		if ( strlen( $material ) < 20 ) {
			// Extremely unlikely (means wp-config salts are missing/default),
			// but never fall back to a hardcoded key silently.
			$material .= wp_salt( 'auth' ) . wp_salt( 'secure_auth' );
		}
		return hash( 'sha256', $material, true ); // 32 raw bytes for AES-256
	}
}

if ( ! function_exists('ds_hub_encrypt') ) {
	function ds_hub_encrypt( $plaintext ) {
		if ( $plaintext === '' || $plaintext === null ) return '';
		$iv  = random_bytes( 16 );
		$ct  = openssl_encrypt( (string) $plaintext, 'AES-256-CBC', ds_hub_encryption_key(), OPENSSL_RAW_DATA, $iv );
		if ( $ct === false ) return '';
		return base64_encode( $iv . $ct );
	}
}

if ( ! function_exists('ds_hub_decrypt') ) {
	function ds_hub_decrypt( $encoded ) {
		if ( empty( $encoded ) ) return '';
		$raw = base64_decode( $encoded, true );
		if ( $raw === false || strlen( $raw ) <= 16 ) return '';
		$iv  = substr( $raw, 0, 16 );
		$ct  = substr( $raw, 16 );
		$pt  = openssl_decrypt( $ct, 'AES-256-CBC', ds_hub_encryption_key(), OPENSSL_RAW_DATA, $iv );
		return $pt === false ? '' : $pt;
	}
}

if ( ! function_exists('ds_hub_mask') ) {
	function ds_hub_mask( $plaintext ) {
		if ( empty( $plaintext ) ) return '';
		$len = strlen( $plaintext );
		if ( $len <= 4 ) return str_repeat( '•', $len );
		return str_repeat( '•', min( 20, $len - 4 ) ) . substr( $plaintext, -4 );
	}
}

/* =====================================================================
 * 2. DATABASE TABLE BOOTSTRAP
 *    (Code Snippets has no activation hook, so we check a version flag
 *    on admin_init — cheap, and safe to run repeatedly.)
 * ===================================================================== */

define( 'DS_HUB_DB_VERSION', '1.0.0' );

if ( ! function_exists('ds_hub_maybe_upgrade_db') ) {
	function ds_hub_maybe_upgrade_db() {
		if ( get_option( 'ds_hub_db_version' ) === DS_HUB_DB_VERSION ) return;

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table            = $wpdb->prefix . 'ds_generated_files';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			file_type VARCHAR(32) NOT NULL DEFAULT 'other',
			source VARCHAR(20) NOT NULL DEFAULT 'generated',
			r2_key VARCHAR(500) NOT NULL DEFAULT '',
			original_filename VARCHAR(255) NOT NULL DEFAULT '',
			mime_type VARCHAR(100) NOT NULL DEFAULT '',
			file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'temporary',
			created_at DATETIME NOT NULL,
			expires_at DATETIME NULL,
			saved_at DATETIME NULL,
			deleted_at DATETIME NULL,
			delete_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			delete_error TEXT NULL,
			last_delete_attempt_at DATETIME NULL,
			meta LONGTEXT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY status (status),
			KEY expires_at (expires_at),
			KEY source (source),
			KEY file_type (file_type)
		) {$charset_collate};";

		dbDelta( $sql );

		$log_table = $wpdb->prefix . 'ds_storage_log';
		$sql2 = "CREATE TABLE {$log_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			file_id BIGINT UNSIGNED NULL,
			event VARCHAR(40) NOT NULL,
			message TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY file_id (file_id),
			KEY event (event)
		) {$charset_collate};";
		dbDelta( $sql2 );

		update_option( 'ds_hub_db_version', DS_HUB_DB_VERSION );
	}
}
add_action( 'admin_init', 'ds_hub_maybe_upgrade_db' );

if ( ! function_exists('ds_hub_log') ) {
	function ds_hub_log( $event, $message = '', $file_id = null ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'ds_storage_log',
			array(
				'file_id'    => $file_id,
				'event'      => sanitize_key( $event ),
				'message'    => is_string( $message ) ? $message : wp_json_encode( $message ),
				'created_at' => current_time( 'mysql', true ),
			)
		);
	}
}

/* =====================================================================
 * 3. LIGHTWEIGHT R2 (S3-COMPATIBLE) CLIENT — AWS SigV4, no SDK dependency
 * ===================================================================== */

if ( ! class_exists('Ds_R2_Client') ) {
	class Ds_R2_Client {

		private $account_id, $access_key, $secret_key, $bucket, $endpoint, $region;

		public function __construct( $account_id, $access_key, $secret_key, $bucket, $endpoint, $region = 'auto' ) {
			$this->account_id = $account_id;
			$this->access_key = $access_key;
			$this->secret_key = $secret_key;
			$this->bucket      = $bucket;
			$this->endpoint    = rtrim( $endpoint, '/' );
			$this->region      = $region ?: 'auto';
		}

		public static function from_saved_settings() {
			$s = ds_hub_get_r2_settings_decrypted();
			if ( empty( $s['access_key_id'] ) || empty( $s['secret_access_key'] ) || empty( $s['bucket_name'] ) || empty( $s['endpoint'] ) ) {
				return new WP_Error( 'ds_r2_not_configured', 'R2 is not fully configured yet. Fill in Account ID, Access Key, Secret Key, Bucket and Endpoint under Designstager → Settings.' );
			}
			return new self( $s['account_id'], $s['access_key_id'], $s['secret_access_key'], $s['bucket_name'], $s['endpoint'], $s['region'] ?: 'auto' );
		}

		private function host() {
			$parsed = wp_parse_url( $this->endpoint );
			return isset( $parsed['host'] ) ? $parsed['host'] : $this->endpoint;
		}

		/**
		 * Core signed request. $key may be '' for bucket-root operations (e.g. list).
		 */
		private function request( $method, $key = '', $query = array(), $body = '', $extra_headers = array(), $timeout = 30 ) {
			$host        = $this->host();
			$path        = '/' . ltrim( $this->bucket, '/' ) . ( $key !== '' ? '/' . ltrim( $key, '/' ) : '' );
			$amz_date    = gmdate( 'Ymd\THis\Z' );
			$date_stamp  = gmdate( 'Ymd' );
			$payload_hash = hash( 'sha256', $body );

			ksort( $query );
			$canonical_query = array();
			foreach ( $query as $qk => $qv ) {
				$canonical_query[] = rawurlencode( $qk ) . '=' . rawurlencode( $qv );
			}
			$canonical_query_string = implode( '&', $canonical_query );

			$headers = array_merge( array(
				'host'                 => $host,
				'x-amz-content-sha256' => $payload_hash,
				'x-amz-date'           => $amz_date,
			), $extra_headers );
			ksort( $headers );

			$canonical_headers = '';
			$signed_headers_arr = array();
			foreach ( $headers as $hk => $hv ) {
				$canonical_headers .= strtolower( $hk ) . ':' . trim( $hv ) . "\n";
				$signed_headers_arr[] = strtolower( $hk );
			}
			$signed_headers = implode( ';', $signed_headers_arr );

			$canonical_request = implode( "\n", array(
				$method,
				$this->uri_encode_path( $path ),
				$canonical_query_string,
				$canonical_headers,
				$signed_headers,
				$payload_hash,
			) );

			$credential_scope = "{$date_stamp}/{$this->region}/s3/aws4_request";
			$string_to_sign   = implode( "\n", array(
				'AWS4-HMAC-SHA256',
				$amz_date,
				$credential_scope,
				hash( 'sha256', $canonical_request ),
			) );

			$k_date    = hash_hmac( 'sha256', $date_stamp, 'AWS4' . $this->secret_key, true );
			$k_region  = hash_hmac( 'sha256', $this->region, $k_date, true );
			$k_service = hash_hmac( 'sha256', 's3', $k_region, true );
			$k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
			$signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

			$authorization = 'AWS4-HMAC-SHA256 ' .
				"Credential={$this->access_key}/{$credential_scope}, " .
				"SignedHeaders={$signed_headers}, Signature={$signature}";

			$url = $this->endpoint . $this->uri_encode_path( $path );
			if ( $canonical_query_string ) $url .= '?' . $canonical_query_string;

			$req_headers = array();
			foreach ( $headers as $hk => $hv ) $req_headers[ $hk ] = $hv;
			$req_headers['Authorization'] = $authorization;
			unset( $req_headers['host'] ); // wp_remote_request sets Host itself

			$response = wp_remote_request( $url, array(
				'method'  => $method,
				'headers' => $req_headers,
				'body'    => $body === '' ? null : $body,
				'timeout' => $timeout,
			) );

			return $response;
		}

		private function uri_encode_path( $path ) {
			$segments = explode( '/', $path );
			foreach ( $segments as $i => $seg ) {
				$segments[ $i ] = rawurlencode( $seg );
			}
			return implode( '/', $segments );
		}

		/** Auth + bucket access check: list up to 1 object. */
		public function list_objects( $prefix = '', $max_keys = 1 ) {
			$query = array( 'list-type' => '2', 'max-keys' => (string) $max_keys );
			if ( $prefix !== '' ) $query['prefix'] = $prefix;
			return $this->request( 'GET', '', $query );
		}

		public function list_objects_page( $prefix = '', $max_keys = 1000, $continuation_token = '' ) {
			$query = array( 'list-type' => '2', 'max-keys' => (string) $max_keys );
			if ( $prefix !== '' ) $query['prefix'] = $prefix;
			if ( $continuation_token !== '' ) $query['continuation-token'] = $continuation_token;
			return $this->request( 'GET', '', $query );
		}

		public function put_object( $key, $data, $content_type = 'application/octet-stream' ) {
			return $this->request( 'PUT', $key, array(), $data, array( 'content-type' => $content_type ) );
		}

		public function get_object( $key ) {
			return $this->request( 'GET', $key );
		}

		public function delete_object( $key ) {
			return $this->request( 'DELETE', $key );
		}

		public function head_object( $key ) {
			return $this->request( 'HEAD', $key );
		}

		/**
		 * Presigned GET URL (query-string signing variant), valid for $expires seconds.
		 * Used so browsers/download links never see R2 credentials directly.
		 */
		/**
		 * $response_overrides may include 'response-content-disposition' and/or
		 * 'response-content-type' — standard S3/R2 signed query overrides that make
		 * the presigned URL itself force a browser "Save As" with a friendly
		 * filename, matching the UX of the old local-disk download proxy.
		 */
		public function presign_get_url( $key, $expires_seconds = 3600, $response_overrides = array() ) {
			$host       = $this->host();
			$path       = '/' . ltrim( $this->bucket, '/' ) . '/' . ltrim( $key, '/' );
			$amz_date   = gmdate( 'Ymd\THis\Z' );
			$date_stamp = gmdate( 'Ymd' );
			$credential_scope = "{$date_stamp}/{$this->region}/s3/aws4_request";

			$query = array(
				'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
				'X-Amz-Credential'    => $this->access_key . '/' . $credential_scope,
				'X-Amz-Date'          => $amz_date,
				'X-Amz-Expires'       => (string) max( 1, (int) $expires_seconds ),
				'X-Amz-SignedHeaders' => 'host',
			);
			foreach ( (array) $response_overrides as $rk => $rv ) {
				if ( $rv === '' || $rv === null ) continue;
				$query[ $rk ] = $rv;
			}
			ksort( $query );
			$canonical_query = array();
			foreach ( $query as $qk => $qv ) $canonical_query[] = rawurlencode( $qk ) . '=' . rawurlencode( $qv );
			$canonical_query_string = implode( '&', $canonical_query );

			$canonical_headers = 'host:' . $host . "\n";
			$canonical_request = implode( "\n", array(
				'GET',
				$this->uri_encode_path( $path ),
				$canonical_query_string,
				$canonical_headers,
				'host',
				'UNSIGNED-PAYLOAD',
			) );

			$string_to_sign = implode( "\n", array(
				'AWS4-HMAC-SHA256',
				$amz_date,
				$credential_scope,
				hash( 'sha256', $canonical_request ),
			) );

			$k_date    = hash_hmac( 'sha256', $date_stamp, 'AWS4' . $this->secret_key, true );
			$k_region  = hash_hmac( 'sha256', $this->region, $k_date, true );
			$k_service = hash_hmac( 'sha256', 's3', $k_region, true );
			$k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
			$signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

			return $this->endpoint . $this->uri_encode_path( $path ) . '?' . $canonical_query_string . '&X-Amz-Signature=' . $signature;
		}

		/**
		 * Full round-trip test: list (auth+bucket access), put (upload), get (read),
		 * delete (delete) — using a small throwaway test object.
		 * Returns array( 'success' => bool, 'steps' => array(...), 'message' => string )
		 */
		public function test_connection() {
			$steps = array();
			$test_key = '.designstager-connection-test/' . wp_generate_password( 12, false, false ) . '.txt';
			$test_body = 'designstager-r2-test-' . time();

			// 1. Auth + bucket access
			$list = $this->list_objects();
			if ( is_wp_error( $list ) ) {
				$steps['auth_bucket_access'] = 'failed: ' . $list->get_error_message();
				return array( 'success' => false, 'steps' => $steps, 'message' => 'Could not reach R2 endpoint: ' . $list->get_error_message() );
			}
			$code = wp_remote_retrieve_response_code( $list );
			if ( $code < 200 || $code >= 300 ) {
				$steps['auth_bucket_access'] = "failed: HTTP {$code}";
				return array( 'success' => false, 'steps' => $steps, 'message' => $this->explain_http_error( $code, wp_remote_retrieve_body( $list ) ) );
			}
			$steps['auth_bucket_access'] = 'ok';

			// 2. Upload permission
			$put = $this->put_object( $test_key, $test_body, 'text/plain' );
			if ( is_wp_error( $put ) || wp_remote_retrieve_response_code( $put ) >= 300 ) {
				$msg = is_wp_error( $put ) ? $put->get_error_message() : $this->explain_http_error( wp_remote_retrieve_response_code( $put ), wp_remote_retrieve_body( $put ) );
				$steps['upload_permission'] = 'failed: ' . $msg;
				return array( 'success' => false, 'steps' => $steps, 'message' => 'Upload permission check failed: ' . $msg );
			}
			$steps['upload_permission'] = 'ok';

			// 3. Read permission
			$get = $this->get_object( $test_key );
			if ( is_wp_error( $get ) || wp_remote_retrieve_response_code( $get ) >= 300 || wp_remote_retrieve_body( $get ) !== $test_body ) {
				$steps['read_permission'] = 'failed';
				$this->delete_object( $test_key ); // best-effort cleanup
				return array( 'success' => false, 'steps' => $steps, 'message' => 'Read permission check failed — object could not be read back correctly.' );
			}
			$steps['read_permission'] = 'ok';

			// 4. Delete permission
			$del = $this->delete_object( $test_key );
			if ( is_wp_error( $del ) || wp_remote_retrieve_response_code( $del ) >= 300 ) {
				$msg = is_wp_error( $del ) ? $del->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $del );
				$steps['delete_permission'] = 'failed: ' . $msg;
				return array( 'success' => false, 'steps' => $steps, 'message' => 'Delete permission check failed: ' . $msg . ' (a small test file may remain in your bucket under .designstager-connection-test/)' );
			}
			$steps['delete_permission'] = 'ok';

			return array( 'success' => true, 'steps' => $steps, 'message' => 'Authentication, bucket access, upload, read and delete all verified successfully.' );
		}

		private function explain_http_error( $code, $body ) {
			$code = (int) $code;
			if ( $code === 403 ) return 'HTTP 403 Forbidden — check your Access Key ID / Secret Access Key and that the token has the right permissions.';
			if ( $code === 404 ) return 'HTTP 404 Not Found — check the Bucket Name and Endpoint (Account ID) are correct.';
			if ( $code === 0 )   return 'Could not connect — check the Endpoint URL is correct and reachable.';
			return "HTTP {$code}" . ( $body ? ' — ' . substr( wp_strip_all_tags( $body ), 0, 200 ) : '' );
		}
	}
}

if ( ! function_exists('ds_hub_get_r2_client') ) {
	function ds_hub_get_r2_client() {
		return Ds_R2_Client::from_saved_settings();
	}
}

/* =====================================================================
 * 4. SETTINGS STORAGE HELPERS
 * ===================================================================== */

$GLOBALS['ds_hub_r2_fields'] = array(
	'account_id'        => 'R2 Account ID',
	'access_key_id'     => 'R2 Access Key ID',
	'secret_access_key' => 'R2 Secret Access Key',
	'bucket_name'       => 'R2 Bucket Name',
	'endpoint'          => 'R2 Endpoint',
	'region'            => 'R2 Region',
);

if ( ! function_exists('ds_hub_get_r2_settings_decrypted') ) {
	function ds_hub_get_r2_settings_decrypted() {
		$stored = get_option( 'ds_hub_r2_settings', array() );
		$out = array();
		foreach ( array_keys( $GLOBALS['ds_hub_r2_fields'] ) as $field ) {
			$out[ $field ] = isset( $stored[ $field ] ) ? ds_hub_decrypt( $stored[ $field ] ) : '';
		}
		return $out;
	}
}

/* Providers are stored as one option: array of
 * [ 'id'=>slug, 'category'=>'image|text|video', 'label'=>..., 'api_key_enc'=>..., 'endpoint'=>..., 'model'=>..., 'enabled'=>bool ]
 */
if ( ! function_exists('ds_hub_get_providers') ) {
	function ds_hub_get_providers() {
		$providers = get_option( 'ds_hub_ai_providers', array() );
		return is_array( $providers ) ? $providers : array();
	}
}

if ( ! function_exists('ds_hub_save_providers') ) {
	function ds_hub_save_providers( $providers ) {
		update_option( 'ds_hub_ai_providers', $providers );
	}
}

if ( ! function_exists('ds_hub_get_provider') ) {
	function ds_hub_get_provider( $slug ) {
		foreach ( ds_hub_get_providers() as $p ) {
			if ( $p['id'] === $slug ) return $p;
		}
		return null;
	}
}

/** Primary lookup helper for other snippets: returns decrypted key or $fallback. */
if ( ! function_exists('ds_hub_secure_get_key') ) {
	function ds_hub_secure_get_key( $slug, $fallback = '' ) {
		$p = ds_hub_get_provider( $slug );
		if ( $p && ! empty( $p['enabled'] ) && ! empty( $p['api_key_enc'] ) ) {
			$val = ds_hub_decrypt( $p['api_key_enc'] );
			if ( $val !== '' ) return $val;
		}
		// Fall back to the legacy plaintext "DS API Keys Settings" snippet, if present.
		if ( function_exists( 'ds_get_key' ) ) {
			$legacy = ds_get_key( $slug, '' );
			if ( $legacy !== '' ) return $legacy;
		}
		return $fallback;
	}
}

define( 'DS_HUB_KNOWN_PROVIDERS', wp_json_encode( array(
	'image' => array(
		'openai'      => array( 'label' => 'OpenAI (Images)',        'endpoint' => 'https://api.openai.com/v1',                     'model' => 'gpt-image-1' ),
		'gemini'      => array( 'label' => 'Google / Gemini (Images)','endpoint' => 'https://generativelanguage.googleapis.com/v1beta','model' => 'imagen-3.0-generate' ),
		'xai'         => array( 'label' => 'xAI / Grok (Images)',     'endpoint' => 'https://api.x.ai/v1',                            'model' => 'grok-2-image' ),
		'replicate'   => array( 'label' => 'Replicate',                'endpoint' => 'https://api.replicate.com/v1',                   'model' => 'black-forest-labs/flux-schnell' ),
		'huggingface' => array( 'label' => 'Hugging Face',             'endpoint' => 'https://api-inference.huggingface.co',           'model' => '' ),
		'cloudflare'  => array( 'label' => 'Cloudflare Workers AI',    'endpoint' => 'https://api.cloudflare.com/client/v4',           'model' => '@cf/stabilityai/stable-diffusion-xl-base-1.0' ),
	),
	'text' => array(
		'openai'    => array( 'label' => 'OpenAI',        'endpoint' => 'https://api.openai.com/v1',         'model' => 'gpt-4.1' ),
		'gemini'    => array( 'label' => 'Google / Gemini','endpoint' => 'https://generativelanguage.googleapis.com/v1beta', 'model' => 'gemini-2.0-flash' ),
		'xai'       => array( 'label' => 'xAI / Grok',    'endpoint' => 'https://api.x.ai/v1',                 'model' => 'grok-3' ),
		'anthropic' => array( 'label' => 'Anthropic / Claude', 'endpoint' => 'https://api.anthropic.com/v1',  'model' => 'claude-sonnet-4-5' ),
		'groq'      => array( 'label' => 'Groq',          'endpoint' => 'https://api.groq.com/openai/v1',     'model' => 'llama-3.3-70b-versatile' ),
		'openrouter'=> array( 'label' => 'OpenRouter',    'endpoint' => 'https://openrouter.ai/api/v1',       'model' => '' ),
	),
	'video' => array(
		'seedance'  => array( 'label' => 'Seedance',   'endpoint' => '', 'model' => '' ),
		'kling'     => array( 'label' => 'Kling',      'endpoint' => '', 'model' => '' ),
		'runway'    => array( 'label' => 'Runway',     'endpoint' => 'https://api.dev.runwayml.com/v1', 'model' => '' ),
		'replicate' => array( 'label' => 'Replicate (Video)', 'endpoint' => 'https://api.replicate.com/v1', 'model' => '' ),
		'wavespeed' => array( 'label' => 'WaveSpeed',  'endpoint' => '', 'model' => '' ),
	),
) ) );

/* =====================================================================
 * 5. ADMIN MENU
 * ===================================================================== */

add_action( 'admin_menu', function() {
	add_menu_page(
		'Designstager',
		'Designstager',
		'manage_options',
		'designstager-hub',
		'ds_hub_render_settings_page',
		'dashicons-cloud',
		58
	);
	add_submenu_page( 'designstager-hub', 'Settings (R2)', 'Settings', 'manage_options', 'designstager-hub', 'ds_hub_render_settings_page' );
	add_submenu_page( 'designstager-hub', 'AI API Settings', 'AI API Settings', 'manage_options', 'designstager-ai-keys', 'ds_hub_render_ai_keys_page' );
	add_submenu_page( 'designstager-hub', 'Storage Settings', 'Storage Settings', 'manage_options', 'designstager-storage-settings', 'ds_hub_render_storage_settings_page' );
} );

/**
 * Print the admin JS directly into the footer of our own pages only.
 * (Deliberately NOT using wp_enqueue_script()/wp_add_inline_script() with an
 * empty src — on this site that combination silently produced no output,
 * likely due to another active plugin's script-loader filtering. Echoing the
 * <script> tag directly during admin_footer is simple and reliable.)
 */
add_action( 'admin_footer', function() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$id     = $screen ? $screen->id : '';
	if ( strpos( $id, 'designstager' ) === false ) return;

	$ajax = array(
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'ds_hub_ajax' ),
	);
	echo '<script>var dsHubAjax = ' . wp_json_encode( $ajax ) . ';</script>';
	echo '<script>' . ds_hub_admin_js() . '</script>';
} );

if ( ! function_exists('ds_hub_admin_js') ) {
	function ds_hub_admin_js() {
		return <<<JS
(function($){
	function runTest(btn, action, extraData, resultSelector) {
		var \$btn = $(btn), \$result = $(resultSelector);
		var original = \$btn.text();
		\$btn.prop('disabled', true).text('Testing…');
		\$result.html('').removeClass('ds-hub-ok ds-hub-fail');
		var data = Object.assign({ action: action, nonce: dsHubAjax.nonce }, extraData || {});
		$.post(dsHubAjax.url, data).done(function(resp){
			if (resp && resp.success) {
				\$result.addClass('ds-hub-ok').html('✓ ' + resp.data.message);
			} else {
				var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Connection failed.';
				\$result.addClass('ds-hub-fail').html('✕ ' + msg);
			}
		}).fail(function(){
			\$result.addClass('ds-hub-fail').html('✕ Request failed — check your internet connection or server error log.');
		}).always(function(){
			\$btn.prop('disabled', false).text(original);
		});
	}
	$(document).on('click', '.ds-hub-test-r2', function(e){
		e.preventDefault();
		runTest(this, 'ds_hub_test_r2', {}, '#ds-hub-r2-test-result');
	});
	$(document).on('click', '.ds-hub-test-provider', function(e){
		e.preventDefault();
		var id = $(this).data('provider-id');
		runTest(this, 'ds_hub_test_provider', { provider_id: id }, '#ds-hub-provider-test-result-' + id);
	});
})(jQuery);
JS;
	}
}

if ( ! function_exists('ds_hub_admin_css') ) {
	function ds_hub_admin_css() {
		?>
		<style>
			.ds-hub-wrap { max-width: 900px; }
			.ds-hub-tabs { margin: 14px 0 20px; border-bottom: 1px solid #dcdcde; }
			.ds-hub-tabs a { display:inline-block; padding:8px 14px; margin-right:4px; text-decoration:none; border:1px solid transparent; border-bottom:none; color:#50575e; }
			.ds-hub-tabs a.active { background:#fff; border-color:#dcdcde; color:#1d2327; font-weight:600; border-radius:4px 4px 0 0; }
			.ds-hub-card { background:#fff; border:1px solid #dcdcde; border-radius:6px; padding:20px 24px; margin-bottom:20px; }
			.ds-hub-set { color:#00a32a; font-weight:600; margin-left:8px; }
			.ds-hub-notset { color:#d63638; font-weight:600; margin-left:8px; }
			.ds-hub-ok { color:#00a32a; font-weight:600; display:block; margin-top:8px; }
			.ds-hub-fail { color:#d63638; font-weight:600; display:block; margin-top:8px; }
			.ds-hub-provider-row { border:1px solid #dcdcde; border-radius:6px; padding:16px 18px; margin-bottom:14px; }
			.ds-hub-provider-row h3 { margin:0 0 10px; display:flex; align-items:center; gap:10px; }
			.ds-hub-badge { font-size:11px; padding:2px 8px; border-radius:10px; background:#f0f0f1; color:#50575e; }
			.ds-hub-badge.on { background:#d7f4dd; color:#00a32a; }
			.ds-hub-group-title { margin-top:28px; }
		</style>
		<?php
	}
}

if ( ! function_exists('ds_hub_tabs_nav') ) {
	function ds_hub_tabs_nav( $active ) {
		$tabs = array(
			'designstager-hub'              => 'R2 Storage',
			'designstager-ai-keys'          => 'AI API Settings',
			'designstager-storage-settings' => 'Storage Settings',
		);
		echo '<h2 class="ds-hub-title" style="display:flex;align-items:center;gap:8px;"><span class="dashicons dashicons-cloud"></span> Designstager — Storage &amp; API Settings</h2>';
		echo '<div class="ds-hub-tabs">';
		foreach ( $tabs as $slug => $label ) {
			$class = ( $slug === $active ) ? 'active' : '';
			printf( '<a class="%s" href="%s">%s</a>', esc_attr( $class ), esc_url( admin_url( 'admin.php?page=' . $slug ) ), esc_html( $label ) );
		}
		echo '</div>';
	}
}

/* =====================================================================
 * 6. R2 SETTINGS PAGE
 * ===================================================================== */

if ( ! function_exists('ds_hub_render_settings_page') ) {
	function ds_hub_render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		if ( isset( $_POST['ds_hub_save_r2'] ) && check_admin_referer( 'ds_hub_r2_nonce' ) ) {
			$stored = get_option( 'ds_hub_r2_settings', array() );
			foreach ( array_keys( $GLOBALS['ds_hub_r2_fields'] ) as $field ) {
				$posted = isset( $_POST[ 'ds_r2_' . $field ] ) ? trim( wp_unslash( $_POST[ 'ds_r2_' . $field ] ) ) : '';
				if ( $posted !== '' ) {
					$stored[ $field ] = ds_hub_encrypt( sanitize_text_field( $posted ) );
				}
			}
			update_option( 'ds_hub_r2_settings', $stored );
			echo '<div class="notice notice-success is-dismissible"><p><strong>R2 settings saved.</strong> Secret values are encrypted before storage.</p></div>';
		}

		$decrypted = ds_hub_get_r2_settings_decrypted();
		ds_hub_admin_css();
		?>
		<div class="wrap ds-hub-wrap">
			<?php ds_hub_tabs_nav( 'designstager-hub' ); ?>
			<div class="ds-hub-card">
				<h2>Cloudflare R2 Settings</h2>
				<p style="color:#6b7280;">Credentials are encrypted at rest and never sent to the browser, REST API, or page source. After saving a secret, it is shown masked (e.g. <code>••••••••1234</code>).</p>
				<form method="post">
					<?php wp_nonce_field( 'ds_hub_r2_nonce' ); ?>
					<table class="form-table">
						<?php foreach ( $GLOBALS['ds_hub_r2_fields'] as $field => $label ) :
							$is_secret = in_array( $field, array( 'secret_access_key', 'access_key_id' ), true );
							$saved     = $decrypted[ $field ];
							$masked    = $saved ? ds_hub_mask( $saved ) : '';
						?>
						<tr>
							<th style="width:220px;"><label for="ds_r2_<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td>
								<input type="<?php echo $is_secret ? 'password' : 'text'; ?>"
									id="ds_r2_<?php echo esc_attr( $field ); ?>"
									name="ds_r2_<?php echo esc_attr( $field ); ?>"
									value=""
									placeholder="<?php echo $saved ? esc_attr( 'Current: ' . $masked . ' (leave blank to keep)' ) : esc_attr( 'Enter ' . $label ); ?>"
									class="regular-text" autocomplete="new-password">
								<?php if ( $saved ) : ?><span class="ds-hub-set">✓ Set</span><?php else : ?><span class="ds-hub-notset">✗ Not set</span><?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
					<?php submit_button( 'Save Settings', 'primary', 'ds_hub_save_r2' ); ?>
				</form>

				<hr>
				<p>
					<button type="button" class="button button-secondary ds-hub-test-r2">Test R2 Connection</button>
					<span style="color:#6b7280;font-size:12px;margin-left:8px;">Verifies authentication, bucket access, upload, read and delete permissions.</span>
				</p>
				<div id="ds-hub-r2-test-result"></div>
			</div>
		</div>
		<?php
	}
}

add_action( 'wp_ajax_ds_hub_test_r2', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Not allowed.' ), 403 );
	check_ajax_referer( 'ds_hub_ajax', 'nonce' );

	$client = ds_hub_get_r2_client();
	if ( is_wp_error( $client ) ) {
		wp_send_json_error( array( 'message' => $client->get_error_message() ) );
	}
	$result = $client->test_connection();
	if ( $result['success'] ) {
		wp_send_json_success( array( 'message' => 'R2 Connected Successfully — ' . $result['message'] ) );
	}
	wp_send_json_error( array( 'message' => 'R2 Connection Failed — ' . $result['message'] ) );
} );

/* =====================================================================
 * 7. AI API SETTINGS PAGE
 * ===================================================================== */

if ( ! function_exists('ds_hub_render_ai_keys_page') ) {
	function ds_hub_render_ai_keys_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$providers = ds_hub_get_providers();

		// Save / update a provider
		if ( isset( $_POST['ds_hub_save_provider'] ) && check_admin_referer( 'ds_hub_provider_nonce' ) ) {
			$id       = sanitize_key( wp_unslash( $_POST['provider_id'] ?? '' ) );
			$category = sanitize_key( wp_unslash( $_POST['provider_category'] ?? 'text' ) );
			$label    = sanitize_text_field( wp_unslash( $_POST['provider_label'] ?? '' ) );
			$endpoint = esc_url_raw( trim( wp_unslash( $_POST['provider_endpoint'] ?? '' ) ) );
			$model    = sanitize_text_field( wp_unslash( $_POST['provider_model'] ?? '' ) );
			$enabled  = ! empty( $_POST['provider_enabled'] );
			$new_key  = trim( wp_unslash( $_POST['provider_api_key'] ?? '' ) );

			if ( $id === '' && $label !== '' ) $id = sanitize_key( $label ) . '-' . substr( md5( $label . time() ), 0, 5 );

			if ( $id !== '' ) {
				$found = false;
				foreach ( $providers as &$p ) {
					if ( $p['id'] === $id ) {
						$p['category'] = $category; $p['label'] = $label; $p['endpoint'] = $endpoint;
						$p['model'] = $model; $p['enabled'] = $enabled;
						if ( $new_key !== '' ) $p['api_key_enc'] = ds_hub_encrypt( $new_key );
						$found = true;
						break;
					}
				}
				unset( $p );
				if ( ! $found ) {
					$providers[] = array(
						'id' => $id, 'category' => $category, 'label' => $label,
						'endpoint' => $endpoint, 'model' => $model, 'enabled' => $enabled,
						'api_key_enc' => $new_key !== '' ? ds_hub_encrypt( $new_key ) : '',
					);
				}
				ds_hub_save_providers( $providers );
				echo '<div class="notice notice-success is-dismissible"><p><strong>Provider saved.</strong></p></div>';
			}
		}

		// Delete a provider
		if ( isset( $_POST['ds_hub_delete_provider'] ) && check_admin_referer( 'ds_hub_provider_nonce' ) ) {
			$id = sanitize_key( wp_unslash( $_POST['provider_id'] ?? '' ) );
			$providers = array_values( array_filter( $providers, function( $p ) use ( $id ) { return $p['id'] !== $id; } ) );
			ds_hub_save_providers( $providers );
			echo '<div class="notice notice-success is-dismissible"><p><strong>Provider removed.</strong></p></div>';
		}

		// Import legacy plaintext keys (from "DS API Keys Settings snippet")
		if ( isset( $_POST['ds_hub_import_legacy'] ) && check_admin_referer( 'ds_hub_provider_nonce' ) ) {
			$legacy_map = array(
				'groq' => 'text', 'huggingface' => 'image', 'replicate' => 'image',
				'gemini' => 'text', 'openai' => 'text', 'openrouter' => 'text',
			);
			$imported = 0;
			foreach ( $legacy_map as $slug => $cat ) {
				$val = get_option( 'ds_key_' . $slug, '' );
				if ( $val === '' ) continue;
				$exists = ds_hub_get_provider( $slug );
				if ( $exists ) continue; // don't overwrite something already migrated
				$providers[] = array(
					'id' => $slug, 'category' => $cat, 'label' => ucfirst( $slug ),
					'endpoint' => '', 'model' => '', 'enabled' => true,
					'api_key_enc' => ds_hub_encrypt( $val ),
				);
				$imported++;
			}
			if ( $imported ) {
				ds_hub_save_providers( $providers );
				echo '<div class="notice notice-success is-dismissible"><p><strong>Imported ' . (int) $imported . ' legacy key(s)</strong> into the secure vault. The original plaintext options were left untouched — deactivate the old "DS API Keys Settings snippet" once you\'ve confirmed everything still works.</p></div>';
			} else {
				echo '<div class="notice notice-info is-dismissible"><p>No new legacy keys found to import.</p></div>';
			}
			$providers = ds_hub_get_providers();
		}

		ds_hub_admin_css();
		$categories = array( 'image' => 'AI Image Models', 'text' => 'AI Presentation / Text Models', 'video' => 'AI Video Models' );
		?>
		<div class="wrap ds-hub-wrap">
			<?php ds_hub_tabs_nav( 'designstager-ai-keys' ); ?>

			<?php if ( function_exists( 'ds_get_key' ) ) : ?>
			<div class="ds-hub-card">
				<p style="margin:0 0 10px;">A legacy plaintext "DS API Keys Settings snippet" is still active on this site.</p>
				<form method="post" style="display:inline;">
					<?php wp_nonce_field( 'ds_hub_provider_nonce' ); ?>
					<?php submit_button( 'Import Legacy Keys Into Secure Vault', 'secondary', 'ds_hub_import_legacy', false ); ?>
				</form>
			</div>
			<?php endif; ?>

			<?php foreach ( $categories as $cat_key => $cat_label ) :
				$rows = array_values( array_filter( $providers, function( $p ) use ( $cat_key ) { return $p['category'] === $cat_key; } ) );
			?>
			<h2 class="ds-hub-group-title"><?php echo esc_html( $cat_label ); ?></h2>
			<?php foreach ( $rows as $p ) : $saved_masked = ! empty( $p['api_key_enc'] ) ? ds_hub_mask( ds_hub_decrypt( $p['api_key_enc'] ) ) : ''; ?>
				<div class="ds-hub-provider-row">
					<h3><?php echo esc_html( $p['label'] ); ?> <span class="ds-hub-badge <?php echo ! empty( $p['enabled'] ) ? 'on' : ''; ?>"><?php echo ! empty( $p['enabled'] ) ? 'Enabled' : 'Disabled'; ?></span></h3>
					<form method="post">
						<?php wp_nonce_field( 'ds_hub_provider_nonce' ); ?>
						<input type="hidden" name="provider_id" value="<?php echo esc_attr( $p['id'] ); ?>">
						<input type="hidden" name="provider_category" value="<?php echo esc_attr( $cat_key ); ?>">
						<table class="form-table">
							<tr><th>Provider name</th><td><input type="text" name="provider_label" value="<?php echo esc_attr( $p['label'] ); ?>" class="regular-text"></td></tr>
							<tr><th>API Key</th><td>
								<input type="password" name="provider_api_key" value="" placeholder="<?php echo $saved_masked ? esc_attr( 'Current: ' . $saved_masked . ' (leave blank to keep)' ) : 'Enter API key'; ?>" class="regular-text" autocomplete="new-password">
								<?php echo $saved_masked ? '<span class="ds-hub-set">✓ Set</span>' : '<span class="ds-hub-notset">✗ Not set</span>'; ?>
							</td></tr>
							<tr><th>API Endpoint <em>(optional)</em></th><td><input type="text" name="provider_endpoint" value="<?php echo esc_attr( $p['endpoint'] ); ?>" class="regular-text"></td></tr>
							<tr><th>Model name <em>(optional)</em></th><td><input type="text" name="provider_model" value="<?php echo esc_attr( $p['model'] ); ?>" class="regular-text"></td></tr>
							<tr><th>Status</th><td><label><input type="checkbox" name="provider_enabled" <?php checked( ! empty( $p['enabled'] ) ); ?>> Enabled</label></td></tr>
						</table>
						<?php submit_button( 'Save', 'primary', 'ds_hub_save_provider', false ); ?>
						<button type="button" class="button ds-hub-test-provider" data-provider-id="<?php echo esc_attr( $p['id'] ); ?>">Test Connection</button>
						<button type="submit" name="ds_hub_delete_provider" value="1" class="button-link-delete" style="margin-left:14px;color:#b32d2e;" onclick="return confirm('Remove this provider?');">Remove</button>
						<div id="ds-hub-provider-test-result-<?php echo esc_attr( $p['id'] ); ?>"></div>
					</form>
				</div>
			<?php endforeach; ?>

			<div class="ds-hub-card">
				<h4>Add a provider to "<?php echo esc_html( $cat_label ); ?>"</h4>
				<form method="post">
					<?php wp_nonce_field( 'ds_hub_provider_nonce' ); ?>
					<input type="hidden" name="provider_category" value="<?php echo esc_attr( $cat_key ); ?>">
					<table class="form-table">
						<tr><th style="width:220px;">Provider name</th><td><input type="text" name="provider_label" class="regular-text" placeholder="e.g. xAI / Grok"></td></tr>
						<tr><th>API Key</th><td><input type="password" name="provider_api_key" class="regular-text" autocomplete="new-password"></td></tr>
						<tr><th>API Endpoint <em>(optional)</em></th><td><input type="text" name="provider_endpoint" class="regular-text" placeholder="https://api.x.ai/v1"></td></tr>
						<tr><th>Model name <em>(optional)</em></th><td><input type="text" name="provider_model" class="regular-text" placeholder="grok-2-image"></td></tr>
						<tr><th>Status</th><td><label><input type="checkbox" name="provider_enabled" checked> Enabled</label></td></tr>
					</table>
					<?php submit_button( 'Add Provider', 'secondary', 'ds_hub_save_provider' ); ?>
				</form>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}

/**
 * Generic provider test. Uses a verified lightweight endpoint for well-known
 * providers; for everything else it does a best-effort authenticated GET
 * against the configured endpoint and reports the raw HTTP status — labelled
 * clearly as a basic reachability check, not a guaranteed deep validation.
 */
add_action( 'wp_ajax_ds_hub_test_provider', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Not allowed.' ), 403 );
	check_ajax_referer( 'ds_hub_ajax', 'nonce' );

	$id = sanitize_key( wp_unslash( $_POST['provider_id'] ?? '' ) );
	$p  = ds_hub_get_provider( $id );
	if ( ! $p ) wp_send_json_error( array( 'message' => 'Provider not found.' ) );

	$key = ! empty( $p['api_key_enc'] ) ? ds_hub_decrypt( $p['api_key_enc'] ) : '';
	if ( $key === '' ) wp_send_json_error( array( 'message' => 'No API key saved for this provider yet.' ) );

	$known = array(
		'openai'    => array( 'url' => 'https://api.openai.com/v1/models',        'headers' => array( 'Authorization' => 'Bearer ' . $key ) ),
		'groq'      => array( 'url' => 'https://api.groq.com/openai/v1/models',   'headers' => array( 'Authorization' => 'Bearer ' . $key ) ),
		'xai'       => array( 'url' => 'https://api.x.ai/v1/models',              'headers' => array( 'Authorization' => 'Bearer ' . $key ) ),
		'openrouter'=> array( 'url' => 'https://openrouter.ai/api/v1/auth/key',   'headers' => array( 'Authorization' => 'Bearer ' . $key ) ),
		'replicate' => array( 'url' => 'https://api.replicate.com/v1/account',    'headers' => array( 'Authorization' => 'Token ' . $key ) ),
		'huggingface' => array( 'url' => 'https://huggingface.co/api/whoami-v2', 'headers' => array( 'Authorization' => 'Bearer ' . $key ) ),
		'anthropic' => array( 'url' => 'https://api.anthropic.com/v1/models',     'headers' => array( 'x-api-key' => $key, 'anthropic-version' => '2023-06-01' ) ),
		'gemini'    => array( 'url' => 'https://generativelanguage.googleapis.com/v1beta/models?key=' . rawurlencode( $key ), 'headers' => array() ),
		'cloudflare'=> array( 'url' => 'https://api.cloudflare.com/client/v4/user/tokens/verify', 'headers' => array( 'Authorization' => 'Bearer ' . $key ) ),
	);

	if ( isset( $known[ $id ] ) ) {
		$resp = wp_remote_get( $known[ $id ]['url'], array( 'headers' => $known[ $id ]['headers'], 'timeout' => 20 ) );
		if ( is_wp_error( $resp ) ) wp_send_json_error( array( 'message' => $resp->get_error_message() ) );
		$code = wp_remote_retrieve_response_code( $resp );
		if ( $code >= 200 && $code < 300 ) {
			wp_send_json_success( array( 'message' => 'Key verified successfully (HTTP ' . $code . ').' ) );
		}
		wp_send_json_error( array( 'message' => 'Provider rejected the key — HTTP ' . $code . '. Double-check the API key.' ) );
	}

	// Generic fallback for providers without a verified lightweight check (e.g. most video providers).
	if ( empty( $p['endpoint'] ) ) {
		wp_send_json_error( array( 'message' => 'No verified test is built in for this provider yet, and no API Endpoint is set to try a basic reachability check. This provider is not currently validated automatically.' ) );
	}
	$resp = wp_remote_get( $p['endpoint'], array( 'headers' => array( 'Authorization' => 'Bearer ' . $key ), 'timeout' => 20 ) );
	if ( is_wp_error( $resp ) ) wp_send_json_error( array( 'message' => 'Basic reachability check: ' . $resp->get_error_message() ) );
	$code = wp_remote_retrieve_response_code( $resp );
	wp_send_json_success( array( 'message' => "Basic reachability check only (not a verified deep test for this provider) — endpoint responded HTTP {$code}." ) );
} );

/* =====================================================================
 * 8. STORAGE SETTINGS PAGE (retention policy)
 * ===================================================================== */

if ( ! function_exists('ds_hub_render_storage_settings_page') ) {
	function ds_hub_render_storage_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		if ( isset( $_POST['ds_hub_save_storage_settings'] ) && check_admin_referer( 'ds_hub_storage_nonce' ) ) {
			$retention = sanitize_text_field( wp_unslash( $_POST['ds_hub_retention'] ?? '60' ) );
			$custom_days = max( 1, (int) ( $_POST['ds_hub_retention_custom'] ?? 60 ) );
			$saved_retention = sanitize_text_field( wp_unslash( $_POST['ds_hub_saved_retention'] ?? 'permanent' ) );
			$saved_custom_days = max( 1, (int) ( $_POST['ds_hub_saved_retention_custom'] ?? 365 ) );
			$notify_enabled = ! empty( $_POST['ds_hub_notify_enabled'] );
			$notify_days_before = max( 1, (int) ( $_POST['ds_hub_notify_days_before'] ?? 7 ) );

			update_option( 'ds_hub_retention_policy', array( 'value' => $retention, 'custom_days' => $custom_days ) );
			update_option( 'ds_hub_saved_retention_policy', array( 'value' => $saved_retention, 'custom_days' => $saved_custom_days ) );
			update_option( 'ds_hub_notify_settings', array( 'enabled' => $notify_enabled, 'days_before' => $notify_days_before ) );

			echo '<div class="notice notice-success is-dismissible"><p><strong>Storage settings saved.</strong></p></div>';
		}

		$retention        = get_option( 'ds_hub_retention_policy', array( 'value' => '60', 'custom_days' => 60 ) );
		$saved_retention  = get_option( 'ds_hub_saved_retention_policy', array( 'value' => 'permanent', 'custom_days' => 365 ) );
		$notify           = get_option( 'ds_hub_notify_settings', array( 'enabled' => false, 'days_before' => 7 ) );

		ds_hub_admin_css();
		?>
		<div class="wrap ds-hub-wrap">
			<?php ds_hub_tabs_nav( 'designstager-storage-settings' ); ?>
			<div class="ds-hub-card">
				<form method="post">
					<?php wp_nonce_field( 'ds_hub_storage_nonce' ); ?>

					<h2>Generated File Retention</h2>
					<p style="color:#6b7280;">Applies to AI-generated images, presentations, PPTX files, videos, PDFs and other temporary generated assets. Marketplace content is never affected by this setting.</p>
					<table class="form-table">
						<tr>
							<th style="width:220px;">Retention period</th>
							<td>
								<?php foreach ( array( '7'=>'7 days','14'=>'14 days','30'=>'30 days','60'=>'60 days','90'=>'90 days','custom'=>'Custom' ) as $val => $label ) : ?>
									<label style="display:block;margin-bottom:6px;">
										<input type="radio" name="ds_hub_retention" value="<?php echo esc_attr( $val ); ?>" <?php checked( $retention['value'], $val ); ?>> <?php echo esc_html( $label ); ?>
									</label>
								<?php endforeach; ?>
								<input type="number" min="1" name="ds_hub_retention_custom" value="<?php echo esc_attr( $retention['custom_days'] ); ?>" style="width:100px;margin-top:4px;"> days (used when "Custom" is selected)
							</td>
						</tr>
					</table>

					<h2>Saved Creation Retention</h2>
					<p style="color:#6b7280;">Applies once a user clicks "Save to My Creations" on a file.</p>
					<table class="form-table">
						<tr>
							<th style="width:220px;">Retention</th>
							<td>
								<?php foreach ( array( 'permanent'=>'Permanent','1year'=>'1 year','custom'=>'Custom' ) as $val => $label ) : ?>
									<label style="display:block;margin-bottom:6px;">
										<input type="radio" name="ds_hub_saved_retention" value="<?php echo esc_attr( $val ); ?>" <?php checked( $saved_retention['value'], $val ); ?>> <?php echo esc_html( $label ); ?>
									</label>
								<?php endforeach; ?>
								<input type="number" min="1" name="ds_hub_saved_retention_custom" value="<?php echo esc_attr( $saved_retention['custom_days'] ); ?>" style="width:100px;margin-top:4px;"> days (used when "Custom" is selected)
							</td>
						</tr>
					</table>

					<h2>Expiration Email Notification</h2>
					<table class="form-table">
						<tr><th style="width:220px;">Send reminder email</th><td><label><input type="checkbox" name="ds_hub_notify_enabled" <?php checked( ! empty( $notify['enabled'] ) ); ?>> Enabled</label></td></tr>
						<tr><th>Days before deletion</th><td><input type="number" min="1" name="ds_hub_notify_days_before" value="<?php echo esc_attr( $notify['days_before'] ); ?>" style="width:100px;"> — sent once per file, never repeated.</td></tr>
					</table>

					<?php submit_button( 'Save Settings', 'primary', 'ds_hub_save_storage_settings' ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists('ds_hub_get_retention_days') ) {
	function ds_hub_get_retention_days() {
		$r = get_option( 'ds_hub_retention_policy', array( 'value' => '60', 'custom_days' => 60 ) );
		return $r['value'] === 'custom' ? max( 1, (int) $r['custom_days'] ) : (int) $r['value'];
	}
}

/** Returns null for "permanent". */
if ( ! function_exists('ds_hub_get_saved_retention_days') ) {
	function ds_hub_get_saved_retention_days() {
		$r = get_option( 'ds_hub_saved_retention_policy', array( 'value' => 'permanent', 'custom_days' => 365 ) );
		if ( $r['value'] === 'permanent' ) return null;
		if ( $r['value'] === '1year' ) return 365;
		return max( 1, (int) $r['custom_days'] );
	}
}

/** Human-readable, dynamic-date expiry notice for generated-file result pages (used by Phase 2+). */
if ( ! function_exists('ds_hub_format_expiry_notice') ) {
	function ds_hub_format_expiry_notice( $expires_at_gmt ) {
		if ( empty( $expires_at_gmt ) ) return '';
		$ts = strtotime( $expires_at_gmt . ' UTC' );
		if ( ! $ts ) return '';
		$date_str = date_i18n( 'F j, Y', $ts );
		return sprintf(
			'This file will be permanently deleted on %s. Download or save this file before the expiration date.',
			$date_str
		);
	}
}

/* =====================================================================
 * 9. GENERATED FILE LIFECYCLE — shared library used by every Phase 2+
 *    generation flow (images, presentations, videos). Building this once
 *    here means each generation snippet only needs to call
 *    ds_hub_upload_generated_bytes() at save time and
 *    ds_hub_render_expiry_widget() at display time — no duplicated
 *    R2/expiry/delete logic per content type.
 * ===================================================================== */

if ( ! function_exists('ds_hub_register_generated_file') ) {
	function ds_hub_register_generated_file( $args ) {
		global $wpdb;
		$a = wp_parse_args( $args, array(
			'user_id'           => get_current_user_id(),
			'file_type'         => 'other',
			'source'            => 'generated',
			'r2_key'            => '',
			'original_filename' => '',
			'mime_type'         => '',
			'file_size'         => 0,
			'meta'              => array(),
		) );
		$now            = current_time( 'mysql', true );
		$retention_days = ds_hub_get_retention_days();
		$expires_at     = gmdate( 'Y-m-d H:i:s', strtotime( $now . " +{$retention_days} days" ) );

		$wpdb->insert( $wpdb->prefix . 'ds_generated_files', array(
			'user_id'           => (int) $a['user_id'],
			'file_type'         => sanitize_key( $a['file_type'] ),
			'source'            => sanitize_key( $a['source'] ),
			'r2_key'            => $a['r2_key'],
			'original_filename' => $a['original_filename'],
			'mime_type'         => $a['mime_type'],
			'file_size'         => (int) $a['file_size'],
			'status'            => 'temporary',
			'created_at'        => $now,
			'expires_at'        => $expires_at,
			'meta'              => wp_json_encode( $a['meta'] ),
		) );
		if ( ! empty( $wpdb->last_error ) ) {
    ds_hub_log( 'insert_failed', $wpdb->last_error );
    return 0;
}
		$id = (int) $wpdb->insert_id;
		ds_hub_log( 'file_registered', array( 'file_type' => $a['file_type'], 'r2_key' => $a['r2_key'] ), $id );
		return $id;
	}
}

if ( ! function_exists('ds_hub_get_generated_file') ) {
	function ds_hub_get_generated_file( $id ) {
		global $wpdb;
		if ( ! $id ) return null;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ds_generated_files WHERE id = %d", (int) $id ) );
	}
}

if ( ! function_exists('ds_hub_user_owns_file') ) {
	function ds_hub_user_owns_file( $file, $user_id ) {
		if ( ! $file ) return false;
		return ( (int) $file->user_id === (int) $user_id ) || current_user_can( 'manage_options' );
	}
}

if ( ! function_exists('ds_hub_file_is_available') ) {
	function ds_hub_file_is_available( $file ) {
		if ( ! $file ) return false;
		if ( in_array( $file->status, array( 'expired', 'deleted' ), true ) ) return false;
		if ( $file->status !== 'saved' && $file->expires_at && strtotime( $file->expires_at . ' UTC' ) < time() ) return false;
		return true;
	}
}

/** Fresh short-lived presigned download URL for a file row, or WP_Error if not downloadable. */
if ( ! function_exists('ds_hub_get_download_url') ) {
	function ds_hub_get_download_url( $file, $expires_seconds = 300 ) {
		if ( ! ds_hub_file_is_available( $file ) ) {
			return new WP_Error( 'ds_hub_expired', 'This file has expired and is no longer available. It cannot be downloaded.' );
		}
		$client = ds_hub_get_r2_client();
		if ( is_wp_error( $client ) ) return $client;
		$ext      = pathinfo( (string) $file->r2_key, PATHINFO_EXTENSION );
		$friendly = 'designstager-' . sanitize_key( $file->file_type ) . '-' . (int) $file->id . ( $ext ? '.' . $ext : '' );
		return $client->presign_get_url( $file->r2_key, $expires_seconds, array(
			'response-content-disposition' => 'attachment; filename="' . $friendly . '"',
			'response-content-type'        => ! empty( $file->mime_type ) ? $file->mime_type : 'application/octet-stream',
		) );
	}
}

if ( ! function_exists('ds_hub_get_variant_url') ) {
	function ds_hub_get_variant_url( $file, $variant, $expires_seconds = 300 ) {
		$key = null;
		if ( $variant === 'thumb' || $variant === 'display' ) {
			$meta = json_decode( (string) $file->meta, true );
			if ( is_array( $meta ) ) {
				$meta_field = ( $variant === 'thumb' ) ? 'thumb_r2_key' : 'disp_r2_key';
				if ( ! empty( $meta[ $meta_field ] ) ) {
					$key = $meta[ $meta_field ];
				}
			}
		}
		if ( ! $key ) {
			// No variant image was generated for this file (e.g. older upload) — fall back to the full original.
			return ds_hub_get_download_url( $file, $expires_seconds );
		}
		if ( ! ds_hub_file_is_available( $file ) ) {
			return new WP_Error( 'ds_hub_expired', 'This file has expired and is no longer available.' );
		}
		$client = ds_hub_get_r2_client();
		if ( is_wp_error( $client ) ) return $client;
		return $client->presign_get_url( $key, $expires_seconds, array(
			'response-content-disposition' => 'inline',
			'response-content-type'        => 'image/webp',
		) );
	}
}

if ( ! defined('DS_SAVED_ITEMS_CAP') ) define('DS_SAVED_ITEMS_CAP', 100);

/**
 * Combined count of everything this user has permanently "saved" across all
 * content types — saved images/videos (ds_generated_files, status=saved),
 * saved presentation drafts (ds_ppt_drafts), and built PPTX downloads
 * (ds_ppt_built_files). One shared pool of DS_SAVED_ITEMS_CAP across all types.
 */
if ( ! function_exists('ds_hub_count_saved_items') ) {
	function ds_hub_count_saved_items( $uid ) {
		global $wpdb;
		$uid = (int) $uid;
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}ds_generated_files WHERE user_id = %d AND status = 'saved'",
			$uid
		) );
		$drafts = get_user_meta( $uid, 'ds_ppt_drafts', true );
		if ( is_array( $drafts ) ) $count += count( $drafts );
		/* PowerPoint files left over from downloads are not saved items and are not counted */
		return $count;
	}
}

if ( ! function_exists('ds_hub_saved_cap_remaining') ) {
	function ds_hub_saved_cap_remaining( $uid ) {
		return max( 0, ds_get_user_saved_cap( $uid ) - ds_hub_count_saved_items( $uid ) );
	}
}

/** Mark a temporary file as saved ("Save to My Creations"), applying the saved-retention policy. */
if ( ! function_exists('ds_hub_mark_file_saved') ) {
	function ds_hub_mark_file_saved( $id ) {
		global $wpdb;
		$saved_days = ds_hub_get_saved_retention_days(); // null = permanent
		$expires_at = $saved_days ? gmdate( 'Y-m-d H:i:s', strtotime( '+' . $saved_days . ' days' ) ) : null;
		$wpdb->update( $wpdb->prefix . 'ds_generated_files',
			array( 'status' => 'saved', 'saved_at' => current_time( 'mysql', true ), 'expires_at' => $expires_at ),
			array( 'id' => (int) $id )
		);
		ds_hub_log( 'file_saved', '', $id );
	}
}

/**
 * Immediately delete a file's R2 object + mark the DB row deleted. Used by the
 * "Delete Now" button and by the Phase 3 cron for expired files. Per the
 * "never falsely report success" requirement: on failure the DB row is kept,
 * marked with the error, and a WP_Error is returned — never silently treated
 * as deleted.
 */
if ( ! function_exists('ds_hub_delete_generated_file_now') ) {
	function ds_hub_delete_generated_file_now( $id ) {
		global $wpdb;
		$file = ds_hub_get_generated_file( $id );
		if ( ! $file ) return new WP_Error( 'ds_hub_no_file', 'File not found.' );
		if ( $file->status === 'deleted' ) return true;
		if ( $file->status === 'saved' ) return new WP_Error( 'ds_hub_is_saved', 'This is a saved creation and will not be auto-deleted.' );

		$client = ds_hub_get_r2_client();
		if ( is_wp_error( $client ) ) {
			$wpdb->update( $wpdb->prefix . 'ds_generated_files', array(
				'delete_attempts'        => (int) $file->delete_attempts + 1,
				'delete_error'           => $client->get_error_message(),
				'last_delete_attempt_at' => current_time( 'mysql', true ),
			), array( 'id' => (int) $id ) );
			ds_hub_log( 'delete_failed', $client->get_error_message(), $id );
			return $client;
		}

		$__ds_meta_for_thumb = json_decode( $file->meta, true );
if ( is_array( $__ds_meta_for_thumb ) && ! empty( $__ds_meta_for_thumb['thumb_r2_key'] ) ) {
@ $client->delete_object( $__ds_meta_for_thumb['thumb_r2_key'] );
}
$__ds_meta_for_disp = json_decode( $file->meta, true );
if ( is_array( $__ds_meta_for_disp ) && ! empty( $__ds_meta_for_disp['disp_r2_key'] ) ) {
@ $client->delete_object( $__ds_meta_for_disp['disp_r2_key'] );
}
$result = $client->delete_object( $file->r2_key );
		$ok     = ! is_wp_error( $result ) && wp_remote_retrieve_response_code( $result ) < 300;

		if ( $ok ) {
			$wpdb->update( $wpdb->prefix . 'ds_generated_files', array(
				'status'                 => 'deleted',
				'deleted_at'             => current_time( 'mysql', true ),
				'delete_error'           => null,
				'last_delete_attempt_at' => current_time( 'mysql', true ),
			), array( 'id' => (int) $id ) );
			ds_hub_log( 'file_deleted', array( 'r2_key' => $file->r2_key ), $id );
			return true;
		}

		$msg = is_wp_error( $result ) ? $result->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $result );
		$wpdb->update( $wpdb->prefix . 'ds_generated_files', array(
			'delete_attempts'        => (int) $file->delete_attempts + 1,
			'delete_error'           => $msg,
			'last_delete_attempt_at' => current_time( 'mysql', true ),
		), array( 'id' => (int) $id ) );
		ds_hub_log( 'delete_failed', $msg, $id );
		return new WP_Error( 'ds_hub_delete_failed', $msg );
	}
}

/**
 * Upload raw bytes to R2 for a newly generated file, register the DB row, and
 * return array('id'=>.., 'r2_key'=>.., 'url'=>proxy url) or WP_Error.
 * $args: user_id, file_type ('image'|'presentation'|'video'|'pdf'|'other'),
 *        source ('generated'|'marketplace'), extension, mime_type, meta (array).
 */
if ( ! function_exists('ds_hub_upload_generated_bytes') ) {
	function ds_hub_upload_generated_bytes( $bytes, $args ) {
		$client = ds_hub_get_r2_client();
		if ( is_wp_error( $client ) ) return $client;

		$a = wp_parse_args( $args, array(
			'user_id'   => get_current_user_id(),
			'file_type' => 'other',
			'source'    => 'generated',
			'extension' => 'bin',
			'mime_type' => 'application/octet-stream',
			'meta'      => array(),
		) );

		$safe_ext = preg_replace( '/[^a-z0-9]/i', '', $a['extension'] );
		if ( ! $safe_ext ) $safe_ext = 'bin';
		$r2_key = sprintf(
			'generated/%s/%d/%s-%s.%s',
			sanitize_key( $a['file_type'] ),
			(int) $a['user_id'],
			gmdate( 'Ymd-His' ),
			wp_generate_password( 8, false, false ),
			$safe_ext
		);

		$put = $client->put_object( $r2_key, $bytes, $a['mime_type'] );
		if ( is_wp_error( $put ) || wp_remote_retrieve_response_code( $put ) >= 300 ) {
			$msg = is_wp_error( $put ) ? $put->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $put );
			ds_hub_log( 'upload_failed', $msg );
			return new WP_Error( 'ds_hub_upload_failed', $msg );
		}

		$id = ds_hub_register_generated_file( array(
			'user_id'           => $a['user_id'],
			'file_type'         => $a['file_type'],
			'source'            => $a['source'],
			'r2_key'            => $r2_key,
			'original_filename' => basename( $r2_key ),
			'mime_type'         => $a['mime_type'],
			'file_size'         => strlen( $bytes ),
			'meta'              => $a['meta'],
		) );

		if ( empty( $id ) ) {
    return new WP_Error( 'ds_hub_register_failed', 'Could not save generated file record.' );
}
		return array( 'id' => $id, 'r2_key' => $r2_key, 'url' => ds_hub_file_proxy_url( $id ) );
	}
}

/** Stable, permanent-looking proxy URL — resolves to a fresh presigned R2 URL on each request. */
if ( ! function_exists('ds_hub_file_proxy_url') ) {
	function ds_hub_file_proxy_url( $id ) {
		return rest_url( 'ds-hub/v1/file/' . (int) $id );
	}
}

/**
 * Given any URL, returns the ds_generated_files row ID if it is one of our
 * stable proxy URLs (rest_url('ds-hub/v1/file/{id}')), else null. Lets
 * generation REST handlers recover the file_id after ds_save_image_to_uploads()
 * (and the equivalent PPT/video savers) only had room to return a single URL
 * string through their existing legacy call-site contract.
 */
if ( ! function_exists('ds_hub_extract_file_id_from_proxy_url') ) {
	function ds_hub_extract_file_id_from_proxy_url( $url ) {
		if ( empty( $url ) || ! is_string( $url ) ) return null;
		if ( preg_match( '#ds-hub/v1/file/(\d+)#', $url, $m ) ) {
			return (int) $m[1];
		}
		return null;
	}
}

/**
 * Just-in-time bridge between an R2-stored generated file and any legacy
 * code path that still requires a real WordPress attachment post —
 * currently only Marketplace submission (ds_create_upload_from_image /
 * ds_create_upload_from_video), which calls wp_get_attachment_url(),
 * set_post_thumbnail(), get_post_mime_type(), etc. on a genuine attachment.
 * Everyday generation, gallery display, download, and retention/expiry all
 * continue to go through R2 exclusively — this only ever runs the first
 * time a given R2 file is actually submitted to the marketplace, not on
 * every generation, so it does not undo the storage savings R2 migration
 * was for.
 *
 * $extra_meta may include 'prompt' and 'model' — the caller's own
 * generation-history record (ds_ai_generations / ds_ai_video_generations)
 * is the most authoritative source for these, so pass what you have;
 * anything omitted falls back to the hub file's own stored meta.
 *
 * Returns (int) attachment_id on success, WP_Error on failure. Caches the
 * created attachment_id on the hub file row so a retried/duplicate call
 * (e.g. a resubmission attempt) reuses the same attachment instead of
 * re-downloading and creating a second one.
 */
if ( ! function_exists('ds_hub_materialize_attachment') ) {
	function ds_hub_materialize_attachment( $file_id, $user_id = null, $extra_meta = array() ) {
		$file = ds_hub_get_generated_file( $file_id );
		if ( ! $file ) {
			return new WP_Error( 'ds_hub_no_file', 'File not found.' );
		}
		if ( $user_id !== null && ! ds_hub_user_owns_file( $file, $user_id ) ) {
			return new WP_Error( 'ds_hub_forbidden', 'Not allowed.' );
		}
		if ( ! ds_hub_file_is_available( $file ) ) {
			return new WP_Error( 'ds_hub_expired', 'This file has expired and is no longer available.' );
		}

		$file_meta = json_decode( (string) $file->meta, true );
		if ( ! is_array( $file_meta ) ) $file_meta = array();

		// Already materialized by an earlier call — reuse it, as long as the
		// attachment post wasn't separately deleted since.
		$existing_aid = ! empty( $file_meta['materialized_attachment_id'] ) ? (int) $file_meta['materialized_attachment_id'] : 0;
		if ( $existing_aid && get_post( $existing_aid ) ) {
			return $existing_aid;
		}

		$client = ds_hub_get_r2_client();
		if ( is_wp_error( $client ) ) return $client;

		$result = $client->get_object( $file->r2_key );
		if ( is_wp_error( $result ) || wp_remote_retrieve_response_code( $result ) >= 300 ) {
			$msg = is_wp_error( $result ) ? $result->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $result );
			ds_hub_log( 'materialize_download_failed', $msg, $file_id );
			return new WP_Error( 'ds_hub_download_failed', 'Could not retrieve file from storage: ' . $msg );
		}
		$bytes = wp_remote_retrieve_body( $result );
		if ( empty( $bytes ) ) {
			return new WP_Error( 'ds_hub_empty_file', 'Retrieved file was empty.' );
		}

		$upload = wp_upload_dir();
		$ext    = pathinfo( (string) $file->r2_key, PATHINFO_EXTENSION );
		if ( ! $ext ) $ext = 'bin';
		$fname  = 'ds-hub-' . sanitize_key( $file->file_type ) . '-' . (int) $file->id . '-' . wp_generate_password( 6, false, false ) . '.' . $ext;
		$fpath  = trailingslashit( $upload['path'] ) . $fname;

		if ( false === file_put_contents( $fpath, $bytes ) ) {
			return new WP_Error( 'ds_hub_write_failed', 'Could not write file locally.' );
		}

		$prompt = ! empty( $extra_meta['prompt'] ) ? $extra_meta['prompt'] : ( isset( $file_meta['prompt'] ) ? $file_meta['prompt'] : '' );
		$model  = ! empty( $extra_meta['model'] ) ? $extra_meta['model'] : ( isset( $file_meta['model'] ) ? $file_meta['model'] : '' );

		$att = array(
			'post_mime_type' => $file->mime_type ?: 'application/octet-stream',
			'post_title'     => $prompt ? sanitize_text_field( substr( $prompt, 0, 60 ) ) : ( 'Designstager AI ' . $file->file_type . ' #' . $file->id ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$aid = wp_insert_attachment( $att, $fpath );
		if ( is_wp_error( $aid ) ) {
			@unlink( $fpath );
			return $aid;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$attach_meta = wp_generate_attachment_metadata( $aid, $fpath );
		wp_update_attachment_metadata( $aid, $attach_meta );

		// Preserve the same attachment meta the legacy local-save path always
		// set, so every downstream reader (marketplace submission, share
		// cards, admin tools) keeps working exactly as before regardless of
		// whether the file came from R2 or local disk.
		update_post_meta( $aid, 'ds_ai_generated', 1 );
		if ( $prompt ) update_post_meta( $aid, 'ds_ai_prompt', $prompt );
		if ( $model )  update_post_meta( $aid, 'ds_ai_model', $model );
		update_post_meta( $aid, 'ds_hub_file_id', (int) $file->id );

		global $wpdb;
		$file_meta['materialized_attachment_id'] = $aid;
		$wpdb->update( $wpdb->prefix . 'ds_generated_files', array( 'meta' => wp_json_encode( $file_meta ) ), array( 'id' => (int) $file->id ) );

		ds_hub_log( 'materialized_attachment', array( 'attachment_id' => $aid ), $file->id );

		return $aid;
	}
}

/* REST: fresh-presign redirect + save/delete actions — one shared endpoint set for every content type. */
add_action( 'rest_api_init', function() {
	register_rest_route( 'ds-hub/v1', '/file/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'permission_callback' => function() { return is_user_logged_in(); },
		'callback'            => function( WP_REST_Request $req ) {
			$file = ds_hub_get_generated_file( $req['id'] );
			if ( ! ds_hub_user_owns_file( $file, get_current_user_id() ) ) {
				return new WP_Error( 'ds_hub_forbidden', 'Not allowed.', array( 'status' => 403 ) );
			}
			$variant = $req->get_param( 'variant' );
			$url = ( $variant && function_exists( 'ds_hub_get_variant_url' ) ) ? ds_hub_get_variant_url( $file, $variant ) : ds_hub_get_download_url( $file );
			if ( is_wp_error( $url ) ) {
				return new WP_Error( 'ds_hub_unavailable', $url->get_error_message(), array( 'status' => 410 ) );
			}
			wp_redirect( $url, 302 );
			exit;
		},
	) );
	register_rest_route( 'ds-hub/v1', '/files', array(
		'methods'              => 'GET',
		'permission_callback'  => function() { return is_user_logged_in(); },
		'callback'             => function( WP_REST_Request $req ) {
			$ids = array_filter( array_map( 'intval', explode( ',', (string) $req->get_param( 'ids' ) ) ) );
			$ids = array_slice( array_unique( $ids ), 0, 80 );
			$uid = get_current_user_id();
			$out = array();
			$variant = $req->get_param( 'variant' );
		foreach ( $ids as $id ) {
				$file = ds_hub_get_generated_file( $id );
				if ( ! ds_hub_user_owns_file( $file, $uid ) ) {
					$out[ $id ] = null;
					continue;
				}
				// Optional 'variant' param (thumb/display) lets grid/gallery callers request the
				// small WebP variant in one batch call instead of the full original -- mirrors the
				// single-file 'file/{id}?variant=' proxy route's logic. No variant param = unchanged
				// legacy behaviour (full download URL), so existing callers are unaffected.
				$u = ( $variant && function_exists( 'ds_hub_get_variant_url' ) ) ? ds_hub_get_variant_url( $file, $variant ) : ds_hub_get_download_url( $file );
				$out[ $id ] = is_wp_error( $u ) ? null : $u;
			}
			return rest_ensure_response( $out );
		},
	) );


	register_rest_route( 'ds-hub/v1', '/save/(?P<id>\d+)', array(
		'methods'             => 'POST',
		'permission_callback' => function() { return is_user_logged_in(); },
		'callback'            => function( WP_REST_Request $req ) {
			$file = ds_hub_get_generated_file( $req['id'] );
			if ( ! ds_hub_user_owns_file( $file, get_current_user_id() ) ) {
				return new WP_Error( 'ds_hub_forbidden', 'Not allowed.', array( 'status' => 403 ) );
			}
			$uid = get_current_user_id();
			if ( $file->status !== 'saved' && ds_hub_count_saved_items( $uid ) >= ds_get_user_saved_cap( $uid ) ) {
				return new WP_Error( 'ds_hub_saved_cap', 'You\'ve reached your ' . ds_get_user_saved_cap( $uid ) . ' saved-item limit. Delete an older saved item before saving a new one.', array( 'status' => 409 ) );
			}
			ds_hub_mark_file_saved( $file->id );
			$fresh = ds_hub_get_generated_file( $file->id );
			return rest_ensure_response( array( 'success' => true, 'status' => $fresh->status, 'expires_at' => $fresh->expires_at ) );
		},
	) );

	register_rest_route( 'ds-hub/v1', '/delete/(?P<id>\d+)', array(
		'methods'             => 'POST',
		'permission_callback' => function() { return is_user_logged_in(); },
		'callback'            => function( WP_REST_Request $req ) {
			$file = ds_hub_get_generated_file( $req['id'] );
			if ( ! ds_hub_user_owns_file( $file, get_current_user_id() ) ) {
				return new WP_Error( 'ds_hub_forbidden', 'Not allowed.', array( 'status' => 403 ) );
			}
			$result = ds_hub_delete_generated_file_now( $file->id );
			if ( is_wp_error( $result ) ) {
				return new WP_Error( 'ds_hub_delete_failed', $result->get_error_message(), array( 'status' => 500 ) );
			}
			return rest_ensure_response( array( 'success' => true ) );
		},
	) );
} );

/**
 * Shared front-end expiry widget: badge + warning text + Download / Save to
 * My Creations / Delete Now buttons. Every Phase 2 generation UI (images,
 * presentations, videos) calls this the same way, so all three content types
 * present the identical concept as required.
 */
if ( ! function_exists('ds_hub_render_expiry_widget') ) {
	function ds_hub_render_expiry_widget( $file_id ) {
		$file = ds_hub_get_generated_file( $file_id );
		if ( ! $file ) return '';
		$GLOBALS['ds_hub_frontend_js_needed'] = true;

		ob_start();
		echo '<div class="ds-hub-widget" data-file-id="' . esc_attr( $file->id ) . '">';

		if ( $file->status === 'saved' ) {
			echo '<div class="ds-hub-badge ds-hub-badge-saved">Saved Creation' .
				( $file->expires_at ? ' — expires ' . esc_html( date_i18n( 'F j, Y', strtotime( $file->expires_at . ' UTC' ) ) ) : ' — kept permanently' ) .
				'</div>';
		} elseif ( ! ds_hub_file_is_available( $file ) ) {
			echo '<div class="ds-hub-badge ds-hub-badge-expired">File expired</div>';
		} else {
			$days_left = $file->expires_at ? max( 0, ceil( ( strtotime( $file->expires_at . ' UTC' ) - time() ) / DAY_IN_SECONDS ) ) : null;
			echo '<div class="ds-hub-badge ds-hub-badge-temp">Temporary storage' .
				( $days_left !== null ? ' — Expires in ' . (int) $days_left . ' day' . ( $days_left === 1 ? '' : 's' ) . ' (' . esc_html( date_i18n( 'F j, Y', strtotime( $file->expires_at . ' UTC' ) ) ) . ')' : '' ) .
				'</div>';
			echo '<p class="ds-hub-warning">This generated file will be permanently deleted from our storage after the expiration date. Download it or save it to My Creations if you want to keep it.</p>';
		}

		echo '<div class="ds-hub-actions">';
		if ( ds_hub_file_is_available( $file ) ) {
			echo '<a href="' . esc_url( ds_hub_file_proxy_url( $file->id ) . '?_wpnonce=' . wp_create_nonce('wp_rest') ) . '" class="ds-hub-btn ds-hub-download" target="_blank" rel="noopener">Download</a>';
			if ( $file->status !== 'saved' ) {
				echo '<button type="button" class="ds-hub-btn ds-hub-save" data-file-id="' . esc_attr( $file->id ) . '">Save to My Creations</button>';
			}
			echo '<button type="button" class="ds-hub-btn ds-hub-delete" data-file-id="' . esc_attr( $file->id ) . '">Delete Now</button>';
		}
		echo '</div>';
		echo '<div class="ds-hub-action-result" style="display:none;"></div>';
		echo '</div>';
		return ob_get_clean();
	}
}

add_action( 'wp_footer', function() {
	if ( empty( $GLOBALS['ds_hub_frontend_js_needed'] ) ) return;
	?>
	<style>
		.ds-hub-widget { margin: 10px 0; font-family: inherit; }
		.ds-hub-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
		.ds-hub-badge-temp { background: #fef3c7; color: #92400e; }
		.ds-hub-badge-saved { background: #d1fae5; color: #065f46; }
		.ds-hub-badge-expired { background: #fee2e2; color: #991b1b; }
		.ds-hub-warning { font-size: 12px; color: #92400e; margin: 4px 0 8px; }
		.ds-hub-actions { display: flex; gap: 8px; flex-wrap: wrap; }
		.ds-hub-btn { display: inline-block; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #d1d5db; background: #fff; color: #111827; text-decoration: none; }
		.ds-hub-btn.ds-hub-download { background: #2563eb; color: #fff; border-color: #2563eb; }
		.ds-hub-btn.ds-hub-delete { color: #b91c1c; border-color: #fecaca; }
		.ds-hub-action-result { font-size: 12px; margin-top: 6px; }
	</style>
	<script>
	(function($){
		function post(action, id, cb) {
			$.ajax({
				url: '<?php echo esc_url_raw( rest_url( 'ds-hub/v1/' ) ); ?>' + action + '/' + id,
				method: 'POST',
				beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'); }
			}).done(function(resp){ cb(null, resp); }).fail(function(xhr){
				var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Request failed.';
				cb(msg);
			});
		}
		$(document).on('click', '.ds-hub-save', function(){
			var $btn = $(this), id = $btn.data('file-id'), $widget = $btn.closest('.ds-hub-widget'), $r = $widget.find('.ds-hub-action-result');
			$btn.prop('disabled', true);
			post('save', id, function(err, resp){
				$btn.prop('disabled', false);
				$r.show().text(err ? ('✕ ' + err) : '✓ Saved to My Creations.');
				if (!err) setTimeout(function(){ location.reload(); }, 900);
			});
		});
		$(document).on('click', '.ds-hub-delete', function(){
			if (!confirm('Permanently delete this file now? This cannot be undone.')) return;
			var $btn = $(this), id = $btn.data('file-id'), $widget = $btn.closest('.ds-hub-widget'), $r = $widget.find('.ds-hub-action-result');
			$btn.prop('disabled', true);
			post('delete', id, function(err, resp){
				$btn.prop('disabled', false);
				$r.show().text(err ? ('✕ ' + err) : '✓ Deleted.');
				if (!err) setTimeout(function(){ location.reload(); }, 900);
			});
		});
	})(jQuery);
	</script>
	<?php
}, 100 );


/* ===== Account deletion cleanup (added 2026-09-23) =====
 * When a user account is deleted, permanently remove that user's generated files
 * from R2 (saved AND unsaved creations) and their Voice Clone samples.
 * Marketplace products use their own copies and are not touched. */
if ( ! function_exists( 'ds_acct_purge_user_files' ) ) {
	function ds_acct_purge_user_files( $user_id, $limit = 40 ) {
		global $wpdb;
		$t = $wpdb->prefix . 'ds_generated_files';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) {
			return 0;
		}
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, r2_key, meta FROM {$t} WHERE user_id = %d AND status <> 'deleted' ORDER BY id ASC LIMIT %d",
			(int) $user_id, (int) $limit
		) );
		if ( ! $rows ) {
			return 0;
		}
		$client = function_exists( 'ds_hub_get_r2_client' ) ? ds_hub_get_r2_client() : new WP_Error( 'ds_no_client', 'R2 client unavailable' );
		if ( is_wp_error( $client ) ) {
			return -1;
		}
		$done = 0;
		foreach ( $rows as $f ) {
			$m = json_decode( (string) $f->meta, true );
			foreach ( array( 'thumb_r2_key', 'disp_r2_key' ) as $k ) {
				if ( is_array( $m ) && ! empty( $m[ $k ] ) ) {
					$client->delete_object( $m[ $k ] );
				}
			}
			$ok = true;
			if ( ! empty( $f->r2_key ) ) {
				$res  = $client->delete_object( $f->r2_key );
				$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
				$ok   = ( $code > 0 && ( $code < 300 || 404 === $code ) );
			}
			if ( $ok ) {
				$wpdb->update( $t, array(
					'status'       => 'deleted',
					'deleted_at'   => current_time( 'mysql', true ),
					'delete_error' => null,
				), array( 'id' => (int) $f->id ) );
				if ( function_exists( 'ds_hub_log' ) ) {
					ds_hub_log( 'file_deleted_account', 'Account deleted (user ' . (int) $user_id . ')', (int) $f->id );
				}
				$done++;
			}
		}
		return $done;
	}
}

if ( ! function_exists( 'ds_acct_purge_left' ) ) {
	function ds_acct_purge_left( $user_id ) {
		global $wpdb;
		$t = $wpdb->prefix . 'ds_generated_files';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) {
			return 0;
		}
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE user_id = %d AND status <> 'deleted'", (int) $user_id ) );
	}
}

add_action( 'delete_user', function ( $user_id ) {
	// 1) Voice Clone samples (stored as Media Library attachments).
	$voices = get_user_meta( $user_id, 'ds_ai_voices', true );
	if ( is_array( $voices ) ) {
		foreach ( $voices as $v ) {
			if ( is_array( $v ) && ! empty( $v['attachment_id'] ) ) {
				wp_delete_attachment( (int) $v['attachment_id'], true );
			}
		}
	}
	// 2) Generated files in R2 -- first batch now, the rest in the background.
	ds_acct_purge_user_files( $user_id, 40 );
	if ( ds_acct_purge_left( $user_id ) > 0 ) {
		wp_schedule_single_event( time() + 120, 'ds_acct_purge_continue', array( (int) $user_id, 1 ) );
	}
}, 5 );

add_action( 'ds_acct_purge_continue', function ( $user_id, $run = 1 ) {
	ds_acct_purge_user_files( $user_id, 60 );
	if ( ds_acct_purge_left( $user_id ) > 0 && (int) $run < 30 ) {
		wp_schedule_single_event( time() + 300, 'ds_acct_purge_continue', array( (int) $user_id, (int) $run + 1 ) );
	}
}, 10, 2 );
