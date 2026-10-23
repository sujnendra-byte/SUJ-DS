<?php
/**
 * Code Snippets export — DS Social Login (Continue with Google / Microsoft)
 *
 * Snippet ID : 1067
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager
 * Modified   : 2026-10-07
 * Load order : 262
 */
/**
 * DS Social Login - "Continue with Google" / "Continue with Microsoft"
 * Run: Everywhere
 * NEW SNIPPET - does not modify any existing snippet.
 *
 * What it does
 *  - Adds a "Google / Microsoft" panel beside the Login form (Theme My
 *    Login): the two buttons on one side, the form on the other. The Sign Up
 *    page is left as it is: the buttons create the account by themselves.
 *    On a narrow screen the panel sits above the form.
 *  - First visit: creates the account from the provider's name and email and
 *    signs the visitor in. Later visits: signs them in.
 *  - An existing account with the same email is used instead of making a
 *    second one, but only when the provider confirms the email is verified.
 *  - A provider-verified email counts as verified for the OTP flow (no code
 *    email). The sign-up bonus, referral and every other user_register hook
 *    run as for a normal sign-up.
 *  - Administrators always sign in with their password (never through here).
 *
 * Set up: Settings > DS Social Login. Each provider needs a client ID and a
 * client secret, and the redirect address shown on that page.
 *
 * Addresses (no permalink rules needed):
 *   /ds-sso/start/google/      /ds-sso/callback/google/
 *   /ds-sso/start/microsoft/   /ds-sso/callback/microsoft/
 *   /ds-sso/finish/<provider>/ (second step: sets the login cookie)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'DS_SSO_LOADED' ) ) {
	return;
}
define( 'DS_SSO_LOADED', '1.3.3' );

/* ------------------------------------------------------------------ settings */

function ds_sso_opts() {
	$saved = get_option( 'ds_sso_settings', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), array(
		'google_on'        => 0,
		'google_id'        => '',
		'google_secret'    => '',
		'google_logo'      => '',
		'microsoft_on'     => 0,
		'microsoft_id'     => '',
		'microsoft_secret' => '',
		'microsoft_logo'   => '',
	) );
}

function ds_sso_providers() {
	return array(
		'google'    => array(
			'label' => 'Google',
			'auth'  => 'https://accounts.google.com/o/oauth2/v2/auth',
			'token' => 'https://oauth2.googleapis.com/token',
			'scope' => 'openid email profile',
		),
		'microsoft' => array(
			'label' => 'Microsoft',
			'auth'  => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
			'token' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
			'scope' => 'openid profile email',
		),
	);
}

function ds_sso_enabled( $p ) {
	$o = ds_sso_opts();
	return ! empty( $o[ $p . '_on' ] ) && '' !== trim( (string) $o[ $p . '_id' ] ) && '' !== trim( (string) $o[ $p . '_secret' ] );
}

function ds_sso_url( $kind, $p ) {
	return home_url( '/ds-sso/' . $kind . '/' . $p . '/' );
}

/* ------------------------------------------------------------- small helpers */

function ds_sso_b64url_decode( $s ) {
	$s = strtr( (string) $s, '-_', '+/' );
	$pad = strlen( $s ) % 4;
	if ( $pad ) {
		$s .= str_repeat( '=', 4 - $pad );
	}
	return base64_decode( $s ); // phpcs:ignore
}

/* Messages shown on the login page. Only these keys are ever displayed. */
function ds_sso_messages() {
	return array(
		'off'       => 'This sign-in method is not available right now. Please use your email and password.',
		'cancelled' => 'Sign-in was cancelled. Please try again.',
		'state'     => 'That sign-in took too long or was opened twice. Please try again.',
		'provider'  => 'We could not complete the sign-in with that provider. Please try again.',
		'noemail'   => 'That account did not share an email address, so we cannot sign you in with it. Please use your email and password.',
		'unverified'=> 'That account has not confirmed its email address yet. Please confirm it with the provider, or use your email and password.',
		'exists'    => 'An account with this email address already exists. Please log in with your email and password.',
		'admin'     => 'Administrator accounts sign in with their email and password.',
		'closed'    => 'New sign-ups are not open right now.',
		'create'    => 'We could not create your account. Please try the Sign Up form.',
	);
}

/* Where the current request has got to, and a short note about it. Saved when
   the request ends (ds_sso_shutdown) so Settings > DS Social Login can show why
   the last sign-in stopped. Never holds an email, a code or a secret. */
function ds_sso_stage( $stage, $note = null ) {
	$GLOBALS['ds_sso_stage'] = (string) $stage;
	if ( null !== $note ) {
		$GLOBALS['ds_sso_note'] = substr( sanitize_text_field( (string) $note ), 0, 300 );
	}
}

function ds_sso_shutdown() {
	$stage = isset( $GLOBALS['ds_sso_stage'] ) ? (string) $GLOBALS['ds_sso_stage'] : '';
	/* A short trail of the last sign-in requests, for the settings page. */
	if ( '' !== $stage && 'start' !== $stage ) {
		$tf = '';
		$tl = 0;
		$th = headers_sent( $tf, $tl );
		$trail = get_option( 'ds_sso_trail', array() );
		$trail = is_array( $trail ) ? $trail : array();
		$trail[] = gmdate( 'H:i:s' ) . ' ' . ( isset( $GLOBALS['ds_sso_p'] ) ? $GLOBALS['ds_sso_p'] : '' ) . ' ' . ( isset( $GLOBALS['ds_sso_kind'] ) ? $GLOBALS['ds_sso_kind'] : '' )
			. ' | ' . ( isset( $GLOBALS['ds_sso_in'] ) ? $GLOBALS['ds_sso_in'] : '' )
			. ' | ended: ' . $stage . ( isset( $GLOBALS['ds_sso_sent'] ) ? ' | sent by ' . $GLOBALS['ds_sso_sent'] : ' | nothing sent' )
			. ( $th ? ' | output started in ' . basename( (string) $tf ) . ':' . (int) $tl : '' );
		update_option( 'ds_sso_trail', array_slice( $trail, -14 ), false );
	}
	if ( '' === $stage || 'start' === $stage || 'ok' === $stage ) {
		if ( 'ok' === $stage ) {
			delete_option( 'ds_sso_diag' );
		}
		return;
	}
	$err = error_get_last();
	$hf  = '';
	$hl  = 0;
	$hs  = headers_sent( $hf, $hl );
	update_option( 'ds_sso_diag', array(
		'time'    => current_time( 'mysql' ),
		'p'       => isset( $GLOBALS['ds_sso_p'] ) ? (string) $GLOBALS['ds_sso_p'] : '',
		'stage'   => $stage,
		'note'    => isset( $GLOBALS['ds_sso_note'] ) ? (string) $GLOBALS['ds_sso_note'] : '',
		'error'   => ( is_array( $err ) && in_array( (int) $err['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) )
			? substr( wp_strip_all_tags( (string) $err['message'] ), 0, 600 ) . ' @ ' . basename( (string) $err['file'] ) . ':' . (int) $err['line'] : '',
		'headers' => $hs ? basename( (string) $hf ) . ':' . (int) $hl : '',
	), false );
}

/* Sends the visitor on. If something else already printed output (so a
   redirect header cannot be sent) or blocked the redirect, the page carries
   the visitor forward itself instead of staying blank. */
function ds_sso_go( $url ) {
	if ( ! empty( $GLOBALS['ds_sso_tkey'] ) ) {
		set_transient( (string) $GLOBALS['ds_sso_tkey'] . '_done', 1, 2 * MINUTE_IN_SECONDS );
	}
	/* An empty address is not replaced by wp_validate_redirect(), so it is
	   handled here: no destination means the home page. */
	$url = wp_validate_redirect( wp_sanitize_redirect( trim( (string) $url ) ), home_url( '/' ) );
	if ( '' === (string) $url ) {
		$url = home_url( '/' );
	}
	/* Anything another plugin printed while the sign-in ran was held back
	   (see ob_start in ds_sso_route) so the cookies and the redirect could
	   still be sent. It is thrown away here and noted for the admin. */
	$junk = '';
	while ( ob_get_level() > 0 ) {
		$junk .= (string) ob_get_clean();
	}
	if ( '' !== trim( $junk ) ) {
		update_option( 'ds_sso_printed', array(
			'time'  => current_time( 'mysql' ),
			'stage' => isset( $GLOBALS['ds_sso_stage'] ) ? (string) $GLOBALS['ds_sso_stage'] : '',
			'chars' => strlen( $junk ),
			'text'  => substr( trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $junk ) ) ), 0, 300 ),
		), false );
	}
	$GLOBALS['ds_sso_sent'] = 'page';
	if ( ! headers_sent() ) {
		nocache_headers();
		header( 'Referrer-Policy: no-referrer' );
		/* Sent directly: $url is already checked (same site only) and cleaned
		   above, and a filter on wp_redirect elsewhere must not be able to
		   drop this redirect. */
		header( 'X-Redirect-By: DS Social Login' );
		header( 'Location: ' . $url, true, 302 );
		$GLOBALS['ds_sso_sent'] = 'redirect';
		exit;
	}
	echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><meta http-equiv="refresh" content="0;url=' . esc_attr( $url ) . '">'
		. '<title>Signing you in</title><script>location.replace(' . wp_json_encode( $url ) . ');</script>'
		. '<p style="font:16px/1.5 sans-serif;padding:40px;text-align:center"><a href="' . esc_url( $url ) . '">Continue</a></p>';
	exit;
}

function ds_sso_fail( $code, $note = null ) {
	$msgs = ds_sso_messages();
	if ( ! isset( $msgs[ $code ] ) ) {
		$code = 'provider';
	}
	ds_sso_stage( 'stopped: ' . $code, $note );
	ds_sso_go( add_query_arg( 'ds_sso_error', $code, home_url( '/login/' ) ) );
}

/* ------------------------------------------------- who is this? (id_token) */

/**
 * Reads the identity out of the token response. The id_token comes straight
 * from the provider's token endpoint over HTTPS, so its content is trusted
 * once the audience, issuer, expiry and nonce match.
 * Returns array( sub, email, verified, name, first, last ) or a failure code.
 */
function ds_sso_identity( $p, $tokens, $client_id, $nonce ) {
	if ( ! is_array( $tokens ) || empty( $tokens['id_token'] ) ) {
		return 'provider';
	}
	$parts = explode( '.', (string) $tokens['id_token'] );
	if ( 3 !== count( $parts ) ) {
		return 'provider';
	}
	$c = json_decode( (string) ds_sso_b64url_decode( $parts[1] ), true );
	if ( ! is_array( $c ) ) {
		return 'provider';
	}
	$aud = isset( $c['aud'] ) ? $c['aud'] : '';
	if ( is_array( $aud ) ? ! in_array( $client_id, $aud, true ) : (string) $aud !== (string) $client_id ) {
		return 'provider';
	}
	if ( empty( $c['exp'] ) || (int) $c['exp'] < time() - 60 ) {
		return 'provider';
	}
	if ( '' === (string) $nonce || ! isset( $c['nonce'] ) || ! hash_equals( (string) $nonce, (string) $c['nonce'] ) ) {
		return 'provider';
	}
	$iss = isset( $c['iss'] ) ? (string) $c['iss'] : '';
	$sub = isset( $c['sub'] ) ? (string) $c['sub'] : '';
	if ( '' === $sub ) {
		return 'provider';
	}

	$email    = '';
	$verified = false;
	if ( 'google' === $p ) {
		if ( 'https://accounts.google.com' !== $iss && 'accounts.google.com' !== $iss ) {
			return 'provider';
		}
		$email    = isset( $c['email'] ) ? (string) $c['email'] : '';
		$verified = ! empty( $c['email_verified'] ) && 'false' !== $c['email_verified'];
	} else {
		if ( ! preg_match( '#^https://login\.microsoftonline\.com/[0-9a-f-]{36}/v2\.0$#i', $iss ) ) {
			return 'provider';
		}
		if ( ! empty( $c['email'] ) ) {
			$email = (string) $c['email'];
		} elseif ( ! empty( $c['preferred_username'] ) && is_email( $c['preferred_username'] ) ) {
			$email = (string) $c['preferred_username'];
		}
		/* Personal Microsoft accounts (outlook.com, hotmail.com, ...) have a
		   confirmed email. A work or school directory can put any address in
		   the email field, so those only count when Microsoft says the domain
		   owner is verified (xms_edov). */
		$tid      = isset( $c['tid'] ) ? strtolower( (string) $c['tid'] ) : '';
		$verified = ( '9188040d-6c67-4c5b-b112-36a304b66dad' === $tid ) || ( ! empty( $c['xms_edov'] ) && 'false' !== $c['xms_edov'] );
	}
	$email = sanitize_email( $email );

	return array(
		'sub'      => $sub,
		'email'    => $email,
		'verified' => (bool) $verified,
		'name'     => isset( $c['name'] ) ? sanitize_text_field( (string) $c['name'] ) : '',
		'first'    => isset( $c['given_name'] ) ? sanitize_text_field( (string) $c['given_name'] ) : '',
		'last'     => isset( $c['family_name'] ) ? sanitize_text_field( (string) $c['family_name'] ) : '',
	);
}

/* ----------------------------------------------- find or create the account */

function ds_sso_mark_verified( $uid ) {
	update_user_meta( $uid, 'ds_email_verified', 1 );
	if ( ! get_user_meta( $uid, 'ds_email_verified_date', true ) ) {
		update_user_meta( $uid, 'ds_email_verified_date', current_time( 'mysql' ) );
	}
	delete_user_meta( $uid, 'ds_otp_code' );
	delete_user_meta( $uid, 'ds_otp_expires' );
	delete_user_meta( $uid, 'ds_otp_attempts' );
}

/** Returns a user id, or a failure code (string). */
function ds_sso_resolve_user( $p, $id ) {
	$key = 'ds_sso_' . $p . '_sub';

	/* 1. Already connected to this provider account. */
	$found = get_users( array( 'meta_key' => $key, 'meta_value' => $id['sub'], 'number' => 2, 'fields' => 'ids' ) ); // phpcs:ignore
	if ( is_array( $found ) && 1 === count( $found ) ) {
		$uid = (int) $found[0];
		if ( user_can( $uid, 'manage_options' ) ) {
			return 'admin';
		}
		return $uid;
	}

	if ( '' === $id['email'] || ! is_email( $id['email'] ) ) {
		return 'noemail';
	}

	/* 2. An account with this email exists: use it, but only on a verified email. */
	$existing = email_exists( $id['email'] );
	if ( $existing ) {
		$existing = (int) $existing;
		if ( user_can( $existing, 'manage_options' ) ) {
			return 'admin';
		}
		if ( ! $id['verified'] ) {
			return 'exists';
		}
		update_user_meta( $existing, $key, $id['sub'] );
		ds_sso_mark_verified( $existing );
		return $existing;
	}

	/* 3. New visitor: create the account. */
	if ( ! get_option( 'users_can_register' ) ) {
		return 'closed';
	}
	if ( 'google' === $p && ! $id['verified'] ) {
		return 'unverified';
	}
	$base = sanitize_user( strtolower( (string) strtok( $id['email'], '@' ) ), true );
	if ( '' === $base ) {
		$base = 'user';
	}
	$login = $base;
	for ( $n = 0; username_exists( $login ) && $n < 50; $n++ ) {
		$login = $base . '_' . wp_rand( 100, 99999 );
	}

	/* The OTP snippet emails a code to every new account. A provider-verified
	   address does not need one, so that single email is held back. */
	$hold = null;
	if ( $id['verified'] ) {
		$addr = strtolower( $id['email'] );
		$hold = function ( $pre, $atts ) use ( $addr ) {
			$to   = isset( $atts['to'] ) ? $atts['to'] : '';
			$to   = strtolower( is_array( $to ) ? implode( ',', $to ) : (string) $to );
			$subj = isset( $atts['subject'] ) ? (string) $atts['subject'] : '';
			if ( false !== strpos( $to, $addr ) && false !== stripos( $subj, 'verification code' ) ) {
				return true;
			}
			return $pre;
		};
		add_filter( 'pre_wp_mail', $hold, 10, 2 );
	}
	$uid = wp_insert_user( array(
		'user_login'   => $login,
		'user_pass'    => wp_generate_password( 32, true, true ),
		'user_email'   => $id['email'],
		'display_name' => '' !== $id['name'] ? $id['name'] : $base,
		'first_name'   => $id['first'],
		'last_name'    => $id['last'],
	) );
	if ( $hold ) {
		remove_filter( 'pre_wp_mail', $hold, 10 );
	}
	if ( is_wp_error( $uid ) || ! $uid ) {
		return 'create';
	}
	$uid = (int) $uid;
	update_user_meta( $uid, $key, $id['sub'] );
	update_user_meta( $uid, 'ds_sso_signup', $p );
	if ( $id['verified'] ) {
		ds_sso_mark_verified( $uid );
	}
	return $uid;
}

/* ------------------------------------------------------------------- routes */

function ds_sso_route() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ); // phpcs:ignore
	$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );
	if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	if ( ! preg_match( '#^/?ds-sso/(start|callback|finish)/(google|microsoft)/?$#', $path, $m ) ) {
		return;
	}
	$kind = $m[1];
	$p    = $m[2];

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	do_action( 'litespeed_control_set_nocache', 'ds social login' );
	nocache_headers();
	$GLOBALS['ds_sso_p']    = $p;
	$GLOBALS['ds_sso_kind'] = $kind;
	$GLOBALS['ds_sso_in']   = ( is_user_logged_in() ? 'signed in' : 'guest' ) . ( isset( $_COOKIE['ds_sso_state'] ) ? ', state cookie' : ', no state cookie' );
	ds_sso_stage( 'start' === $kind ? 'start' : $kind );
	register_shutdown_function( 'ds_sso_shutdown' );
	ob_start();

	if ( ! ds_sso_enabled( $p ) ) {
		ds_sso_fail( 'off' );
	}
	$o    = ds_sso_opts();
	$prov = ds_sso_providers();
	$prov = $prov[ $p ];
	$cid  = trim( (string) $o[ $p . '_id' ] );

	/* ---- start: send the visitor to the provider ---- */
	if ( 'start' === $kind ) {
		$back = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore
		if ( is_user_logged_in() ) {
			wp_safe_redirect( wp_validate_redirect( $back, home_url( '/' ) ) );
			exit;
		}
		$state = wp_generate_password( 40, false, false );
		$nonce = wp_generate_password( 32, false, false );
		set_transient( 'ds_sso_' . md5( $state ), array( 'p' => $p, 'nonce' => $nonce, 'back' => $back ), 10 * MINUTE_IN_SECONDS );
		setcookie( 'ds_sso_state', $state, array(
			'expires'  => time() + 10 * MINUTE_IN_SECONDS,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		) );
		$url = $prov['auth'] . '?' . http_build_query( array(
			'client_id'     => $cid,
			'redirect_uri'  => ds_sso_url( 'callback', $p ),
			'response_type' => 'code',
			'scope'         => $prov['scope'],
			'state'         => $state,
			'nonce'         => $nonce,
			'prompt'        => 'select_account',
		), '', '&', PHP_QUERY_RFC3986 );
		wp_redirect( $url ); // phpcs:ignore -- the provider's own sign-in address
		exit;
	}

	/* ---- finish: second, light step. The account work is already done; this
	   request only sets the login cookie, so nothing that runs while an
	   account is created can get in the way of it. ---- */
	if ( 'finish' === $kind ) {
		$t    = isset( $_GET['t'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_GET['t'] ) ) : ''; // phpcs:ignore
		$key  = 'ds_sso_t_' . md5( $t );
		$tick = '' !== $t ? get_transient( $key ) : false;
		delete_transient( $key );
		if ( ! is_array( $tick ) || empty( $tick['uid'] ) || ! isset( $tick['p'] ) || $tick['p'] !== $p ) {
			if ( is_user_logged_in() ) {
				ds_sso_stage( 'ok' );
				ds_sso_go( home_url( '/' ) );
			}
			ds_sso_fail( 'state', 'finish step: ticket missing or already used' );
		}
		$uid  = (int) $tick['uid'];
		$user = get_userdata( $uid );
		if ( ! $user || user_can( $uid, 'manage_options' ) ) {
			ds_sso_fail( 'provider', 'finish step: account not found' );
		}
		ds_sso_stage( 'signing in (account ' . $uid . ')' );
		wp_set_current_user( $uid );
		wp_set_auth_cookie( $uid, true );
		update_user_meta( $uid, 'ds_sso_last', $p . ' ' . current_time( 'mysql' ) );
		$back = wp_validate_redirect( isset( $tick['back'] ) ? (string) $tick['back'] : '', home_url( '/' ) );
		/* Same hook a password login fires. An account whose email is still
		   unverified is sent to the code screen by the OTP snippet from here.
		   A fault in another plugin's login hook must not leave a blank page:
		   the visitor is already signed in, so they are sent on regardless. */
		ds_sso_stage( 'running the after-login steps (account ' . $uid . ')' );
		try {
			do_action( 'wp_login', $user->user_login, $user );
			ds_sso_stage( 'ok' );
		} catch ( \Throwable $e ) {
			ds_sso_stage( 'signed in, but an after-login step failed', $e->getMessage() . ' @ ' . basename( $e->getFile() ) . ':' . $e->getLine() );
		}
		ds_sso_go( $back );
	}

	/* ---- callback: the provider sent the visitor back ---- */
	$state  = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : ''; // phpcs:ignore
	$cookie = isset( $_COOKIE['ds_sso_state'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['ds_sso_state'] ) ) : '';
	setcookie( 'ds_sso_state', '', array( 'expires' => time() - 3600, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );

	if ( isset( $_GET['error'] ) ) { // phpcs:ignore
		if ( '' !== $state ) {
			delete_transient( 'ds_sso_' . md5( $state ) );
		}
		ds_sso_fail( 'cancelled' );
	}
	$tkey  = 'ds_sso_' . md5( $state );
	$saved = '' !== $state ? get_transient( $tkey ) : false;
	/* The same return address arriving a second time (a browser, an extension
	   or a proxy repeating the request). The first arrival does the sign-in;
	   this one only waits for it and sends the visitor on, with no error. It
	   signs nobody in and reads nothing from the provider. */
	if ( is_array( $saved ) && ! empty( $saved['used'] ) && isset( $saved['p'] ) && $saved['p'] === $p ) {
		ds_sso_stage( 'ok' );
		for ( $i = 0; $i < 16 && ! get_transient( $tkey . '_done' ); $i++ ) {
			usleep( 500000 );
		}
		$again = get_transient( $tkey );
		if ( is_array( $again ) && ! empty( $again['finish'] ) && ! is_user_logged_in() ) {
			ds_sso_go( (string) $again['finish'] );
		}
		ds_sso_go( wp_validate_redirect( isset( $saved['back'] ) ? (string) $saved['back'] : '', home_url( '/' ) ) );
	}
	if ( is_user_logged_in() ) {
		ds_sso_stage( 'ok' );
		ds_sso_go( home_url( '/' ) );
	}
	if ( '' === $state || '' === $cookie || ! hash_equals( $cookie, $state ) ) {
		ds_sso_fail( 'state' );
	}
	if ( ! is_array( $saved ) || empty( $saved['p'] ) || $saved['p'] !== $p ) {
		delete_transient( $tkey );
		ds_sso_fail( 'state' );
	}
	$GLOBALS['ds_sso_tkey'] = $tkey;
	/* One use only: from here this state can never start a sign-in again. */
	set_transient( $tkey, array( 'p' => $p, 'used' => time(), 'back' => isset( $saved['back'] ) ? (string) $saved['back'] : '' ), 2 * MINUTE_IN_SECONDS );
	$code = isset( $_GET['code'] ) ? (string) wp_unslash( $_GET['code'] ) : ''; // phpcs:ignore
	if ( '' === $code ) {
		ds_sso_fail( 'provider', 'no code in the address' );
	}

	ds_sso_stage( 'asking the provider for the sign-in details' );
	$res = wp_remote_post( $prov['token'], array(
		'timeout' => 20,
		'body'    => array(
			'code'          => $code,
			'client_id'     => $cid,
			'client_secret' => trim( (string) $o[ $p . '_secret' ] ),
			'redirect_uri'  => ds_sso_url( 'callback', $p ),
			'grant_type'    => 'authorization_code',
		),
	) );
	if ( is_wp_error( $res ) ) {
		ds_sso_fail( 'provider', 'could not reach the provider: ' . $res->get_error_message() );
	}
	if ( 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		$bad = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		ds_sso_fail( 'provider', 'provider answered ' . (int) wp_remote_retrieve_response_code( $res ) . ': '
			. ( is_array( $bad ) && isset( $bad['error'] ) && is_string( $bad['error'] ) ? $bad['error'] : '' )
			. ( is_array( $bad ) && isset( $bad['error_description'] ) && is_string( $bad['error_description'] ) ? ' - ' . $bad['error_description'] : '' ) );
	}
	ds_sso_stage( 'reading the sign-in details' );
	$id = ds_sso_identity( $p, json_decode( (string) wp_remote_retrieve_body( $res ), true ), $cid, isset( $saved['nonce'] ) ? $saved['nonce'] : '' );
	if ( ! is_array( $id ) ) {
		ds_sso_fail( $id, 'the sign-in details did not pass the checks' );
	}
	ds_sso_stage( 'finding or creating the account' );
	try {
		$uid = ds_sso_resolve_user( $p, $id );
	} catch ( \Throwable $e ) {
		ds_sso_fail( 'create', 'error while creating the account: ' . $e->getMessage() . ' @ ' . basename( $e->getFile() ) . ':' . $e->getLine() );
	}
	if ( ! is_int( $uid ) || $uid <= 0 ) {
		ds_sso_fail( is_string( $uid ) ? $uid : 'provider' );
	}
	$user = get_userdata( $uid );
	if ( ! $user ) {
		ds_sso_fail( 'provider' );
	}

	/* Hand over to the finish step with a one-time ticket (2 minutes, one use). */
	$ticket = wp_generate_password( 40, false, false );
	set_transient( 'ds_sso_t_' . md5( $ticket ), array( 'uid' => $uid, 'p' => $p, 'back' => isset( $saved['back'] ) ? (string) $saved['back'] : '' ), 2 * MINUTE_IN_SECONDS );
	$finish = add_query_arg( 't', $ticket, ds_sso_url( 'finish', $p ) );
	set_transient( $tkey, array( 'p' => $p, 'used' => time(), 'back' => isset( $saved['back'] ) ? (string) $saved['back'] : '', 'finish' => $finish ), 2 * MINUTE_IN_SECONDS );
	ds_sso_stage( 'ok' );
	ds_sso_go( $finish );
}
add_action( 'init', 'ds_sso_route', 5 );

/* ------------------------------------------- buttons on Login / Sign Up form */

function ds_sso_buttons() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore
	if ( ! preg_match( '#/login(/|\?|$)#i', $uri ) ) {
		return;
	}
	$o  = ds_sso_opts();
	$on = array();
	foreach ( ds_sso_providers() as $p => $cfg ) {
		if ( ds_sso_enabled( $p ) ) {
			$on[ $p ] = array( 'label' => $cfg['label'], 'url' => ds_sso_url( 'start', $p ), 'logo' => esc_url_raw( (string) $o[ $p . '_logo' ] ) );
		}
	}
	$msgs = ds_sso_messages();
	$err  = isset( $_GET['ds_sso_error'] ) ? sanitize_key( wp_unslash( $_GET['ds_sso_error'] ) ) : ''; // phpcs:ignore
	$err  = isset( $msgs[ $err ] ) ? $msgs[ $err ] : '';
	if ( ! $on && '' === $err ) {
		return;
	}
	?>
<style id="ds-sso-css">
.tml.ds-sso-on{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);column-gap:22px;align-items:stretch;max-width:960px!important;width:100%}
.tml.ds-sso-on>*{grid-column:1/-1;min-width:0;order:4}
.tml.ds-sso-on>.tml-alerts,.tml.ds-sso-on>.ds-sso-err{order:0}
.tml.ds-sso-on>.ds-sso{grid-column:1;order:1;align-self:start}
.tml.ds-sso-on>.ds-sso-or{grid-column:2;order:2}
.tml.ds-sso-on>form{grid-column:3;order:3;margin:0}
.tml .ds-sso{box-sizing:border-box;display:block;background:#191c25;border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:36px 32px;box-shadow:0 8px 40px rgba(0,0,0,.4);font-family:inherit;text-align:left}
.tml .ds-sso .ds-sso-title{margin:0 0 6px;padding:0;font-size:22px;line-height:1.3;font-weight:700;color:#fff;text-align:center;font-family:inherit}
.tml .ds-sso .ds-sso-sub{margin:0 0 24px;padding:0;font-size:13px;line-height:1.5;color:#a9b4c8;text-align:center}
.tml .ds-sso a.ds-sso-btn{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;box-sizing:border-box;margin:0 0 12px;padding:13px 16px;min-height:50px;border-radius:10px;border:1px solid rgba(255,255,255,.22);background:#fff;color:#1f2937!important;font-size:15px;line-height:1.2;font-weight:600;font-family:inherit;text-decoration:none!important;text-transform:none;letter-spacing:0;cursor:pointer;transition:background .15s,border-color .15s,transform .15s}
.tml .ds-sso a.ds-sso-btn span{color:#1f2937!important}
.tml .ds-sso a.ds-sso-btn:hover,.tml .ds-sso a.ds-sso-btn:focus{background:#eef0f3;border-color:#fff}
.tml .ds-sso a.ds-sso-btn:active{transform:translateY(1px)}
.tml .ds-sso a.ds-sso-btn:focus-visible{outline:2px solid #E8481C;outline-offset:2px}
.tml .ds-sso a.ds-sso-btn img{width:20px;height:20px;max-width:20px;flex:0 0 20px;display:block;object-fit:contain;margin:0;padding:0;border:0;border-radius:0;box-shadow:none;background:none}
.tml .ds-sso .ds-sso-pts{margin:20px 0 0;padding:18px 0 0;border-top:1px solid rgba(255,255,255,.12)}
.tml .ds-sso .ds-sso-pt{display:block;position:relative;margin:0 0 8px;padding:0 0 0 24px;font-size:13px;line-height:1.5;color:#cbd5e1;text-align:left}
.tml .ds-sso .ds-sso-pt:last-child{margin-bottom:0}
.tml .ds-sso .ds-sso-pt:before{content:"";position:absolute;left:3px;top:5px;width:9px;height:5px;border-left:2px solid #E8481C;border-bottom:2px solid #E8481C;transform:rotate(-45deg)}
.tml .ds-sso-or{display:flex;flex-direction:column;align-items:center;gap:10px;color:#8b97af;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.6px}
.tml .ds-sso-or:before,.tml .ds-sso-or:after{content:"";flex:1;width:1px;background:rgba(255,255,255,.15)}
.tml .ds-sso-err{box-sizing:border-box;margin:0 0 16px;padding:12px 14px;border-radius:10px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.4);color:#fecaca;font-size:13px;line-height:1.5}
.tml.ds-sso-on.ds-sso-narrow{display:flex;flex-direction:column;max-width:460px!important}
.tml.ds-sso-on.ds-sso-narrow>.ds-sso{align-self:stretch;padding:28px 24px}
.tml.ds-sso-on.ds-sso-narrow>.ds-sso-or{flex-direction:row;margin:16px 0}
.tml.ds-sso-on.ds-sso-narrow>.ds-sso-or:before,.tml.ds-sso-on.ds-sso-narrow>.ds-sso-or:after{width:auto;height:1px}
.tml.ds-sso-on.ds-sso-narrow>.ds-sso .ds-sso-pts{display:none}
@media (prefers-reduced-motion:reduce){.tml .ds-sso a.ds-sso-btn{transition:none}}
</style>
<script id="ds-sso-js" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false">
(function () {
  var ON = <?php echo wp_json_encode( $on ); ?>, ERR = <?php echo wp_json_encode( $err ); ?>;
  function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }
  function fit(tml) {
    var room = tml.parentNode ? tml.parentNode.clientWidth : 0;
    tml.classList.toggle('ds-sso-narrow', room > 0 && room < 760);
  }
  function build() {
    var form = document.querySelector('.tml-login form[name="login"]');
    if (!form) return;
    var tml = form.parentNode;
    if (!tml || tml.querySelector('.ds-sso, .ds-sso-err')) return;
    var signup = form.getAttribute('name') === 'register';
    var back = '';
    try { back = new URLSearchParams(location.search).get('redirect_to') || ''; } catch (e) {}
    if (ERR) { var er = el('div', 'ds-sso-err', ERR); er.setAttribute('role', 'alert'); tml.insertBefore(er, form); }
    var box = el('div', 'ds-sso'), n = 0;
    box.appendChild(el('h2', 'ds-sso-title', signup ? 'Sign up in one click' : 'Log in or sign up in one click'));
    box.appendChild(el('p', 'ds-sso-sub', signup ? 'Use an account you already have.' : 'New here? Your account is created automatically.'));
    for (var p in ON) {
      if (!Object.prototype.hasOwnProperty.call(ON, p)) continue;
      var a = el('a', 'ds-sso-btn ds-sso-' + p);
      a.href = ON[p].url + (back ? '?redirect_to=' + encodeURIComponent(back) : '');
      if (ON[p].logo) { var im = document.createElement('img'); im.src = ON[p].logo; im.alt = ''; im.width = 20; im.height = 20; a.appendChild(im); }
      a.appendChild(el('span', '', (signup ? 'Sign up with ' : 'Continue with ') + ON[p].label));
      box.appendChild(a); n++;
    }
    if (!n) return;
    var ul = el('div', 'ds-sso-pts');
    var pts = signup
      ? ['No new password to remember', 'No email code to enter', 'We receive only your name and email address']
      : ['No sign-up form to fill in', 'No password to type', 'We receive only your name and email address'];
    for (var i = 0; i < pts.length; i++) ul.appendChild(el('div', 'ds-sso-pt', pts[i]));
    box.appendChild(ul);
    var or = el('div', 'ds-sso-or', 'or'); or.setAttribute('aria-hidden', 'true');
    /* The form itself is not moved (it holds the security check), the new
       panel is added after it and CSS order shows it first:
       Google and Microsoft | or | form. */
    var after = form.nextSibling;
    tml.insertBefore(or, after);
    tml.insertBefore(box, after);
    tml.classList.add('ds-sso-on');
    fit(tml);
    window.addEventListener('resize', function () { fit(tml); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build); else build();
})();
</script>
	<?php
}
add_action( 'wp_footer', 'ds_sso_buttons', 30 );

/* A signed-in visitor never needs to see a sign-in message. */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() && isset( $_GET['ds_sso_error'] ) ) { // phpcs:ignore
		nocache_headers();
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
}, 1 );

/* ------------------------------------------------------------ settings page */

add_action( 'admin_menu', function () {
	add_options_page( 'DS Social Login', 'DS Social Login', 'manage_options', 'ds-social-login', 'ds_sso_settings_page' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_ds-social-login' === $hook ) {
		wp_enqueue_media();
	}
} );

add_action( 'admin_init', function () {
	register_setting( 'ds_sso', 'ds_sso_settings', array(
		'type'              => 'array',
		'sanitize_callback' => function ( $in ) {
			$old = ds_sso_opts();
			$in  = is_array( $in ) ? $in : array();
			$out = array();
			foreach ( array( 'google', 'microsoft' ) as $p ) {
				$out[ $p . '_on' ] = empty( $in[ $p . '_on' ] ) ? 0 : 1;
				$out[ $p . '_id' ] = isset( $in[ $p . '_id' ] ) ? sanitize_text_field( trim( (string) $in[ $p . '_id' ] ) ) : '';
				/* An empty secret box means "keep the saved one". */
				$sec = isset( $in[ $p . '_secret' ] ) ? trim( (string) $in[ $p . '_secret' ] ) : '';
				$out[ $p . '_secret' ] = '' !== $sec ? sanitize_text_field( $sec ) : (string) $old[ $p . '_secret' ];
				$out[ $p . '_logo' ]   = isset( $in[ $p . '_logo' ] ) ? esc_url_raw( trim( (string) $in[ $p . '_logo' ] ), array( 'https', 'http' ) ) : '';
			}
			return $out;
		},
	) );
} );

function ds_sso_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = ds_sso_opts();
	?>
	<div class="wrap">
		<h1>DS Social Login</h1>
		<p>Adds "Continue with Google" and "Continue with Microsoft" to the Login and Sign Up forms. A provider is shown only when it is switched on and has both a client ID and a client secret.</p>
		<?php $d = get_option( 'ds_sso_diag' ); if ( is_array( $d ) && ! empty( $d['stage'] ) ) : ?>
		<div class="notice notice-warning inline" id="ds-sso-diag"><p><strong>Last sign-in that did not finish</strong> (<?php echo esc_html( ( isset( $d['time'] ) ? $d['time'] : '' ) . ( ! empty( $d['p'] ) ? ', ' . ucfirst( $d['p'] ) : '' ) ); ?>)<br>
			Stopped at: <code><?php echo esc_html( $d['stage'] ); ?></code>
			<?php if ( ! empty( $d['note'] ) ) : ?><br>Detail: <code><?php echo esc_html( $d['note'] ); ?></code><?php endif; ?>
			<?php if ( ! empty( $d['error'] ) ) : ?><br>Error: <code><?php echo esc_html( $d['error'] ); ?></code><?php endif; ?>
			<?php if ( ! empty( $d['headers'] ) ) : ?><br>Output had already started in: <code><?php echo esc_html( $d['headers'] ); ?></code><?php endif; ?>
		</p></div>
		<?php endif; ?>
		<?php $d = get_option( 'ds_sso_printed' ); if ( is_array( $d ) && ! empty( $d['chars'] ) ) : ?>
		<div class="notice notice-info inline" id="ds-sso-printed"><p><strong>Another plugin or snippet printed text during a sign-in</strong> (<?php echo esc_html( isset( $d['time'] ) ? $d['time'] : '' ); ?>). It was held back so the sign-in could finish.<br>
			While: <code><?php echo esc_html( isset( $d['stage'] ) ? $d['stage'] : '' ); ?></code>, <?php echo (int) $d['chars']; ?> characters<?php if ( ! empty( $d['text'] ) ) : ?>: <code><?php echo esc_html( $d['text'] ); ?></code><?php endif; ?>
		</p></div>
		<?php endif; ?>
		<?php $tr = get_option( 'ds_sso_trail' ); if ( is_array( $tr ) && $tr ) : ?>
		<details id="ds-sso-trail" style="margin:12px 0"><summary>Last sign-in requests (for troubleshooting, times in UTC)</summary><pre style="background:#fff;border:1px solid #c3c4c7;padding:10px;overflow:auto;font-size:12px"><?php echo esc_html( implode( "\n", array_map( 'strval', $tr ) ) ); ?></pre></details>
		<?php endif; ?>
		<?php if ( isset( $_GET['ds_sso_hooks'] ) ) : ?><pre id="ds-sso-hooks" style="background:#fff;border:1px solid #c3c4c7;padding:10px;overflow:auto;font-size:12px"><?php echo esc_html( implode( "\n", ds_sso_hook_list() ) ); ?></pre><?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'ds_sso' ); ?>
			<?php foreach ( ds_sso_providers() as $p => $cfg ) : ?>
			<h2><?php echo esc_html( $cfg['label'] ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Show the button</th><td><label><input type="checkbox" name="ds_sso_settings[<?php echo esc_attr( $p ); ?>_on]" value="1" <?php checked( $o[ $p . '_on' ], 1 ); ?>> Continue with <?php echo esc_html( $cfg['label'] ); ?></label>
					<?php if ( ! empty( $o[ $p . '_on' ] ) && ! ds_sso_enabled( $p ) ) : ?><p class="description" style="color:#b32d2e">Switched on, but the client ID or secret is missing, so the button is hidden.</p><?php endif; ?></td></tr>
				<tr><th scope="row"><label for="ds-sso-<?php echo esc_attr( $p ); ?>-id">Client ID</label></th><td><input class="regular-text code" id="ds-sso-<?php echo esc_attr( $p ); ?>-id" name="ds_sso_settings[<?php echo esc_attr( $p ); ?>_id]" value="<?php echo esc_attr( $o[ $p . '_id' ] ); ?>" autocomplete="off"></td></tr>
				<tr><th scope="row"><label for="ds-sso-<?php echo esc_attr( $p ); ?>-secret">Client secret</label></th><td><input class="regular-text code" type="password" id="ds-sso-<?php echo esc_attr( $p ); ?>-secret" name="ds_sso_settings[<?php echo esc_attr( $p ); ?>_secret]" value="" autocomplete="new-password" placeholder="<?php echo '' !== (string) $o[ $p . '_secret' ] ? 'Saved - leave empty to keep it' : ''; ?>"></td></tr>
				<tr><th scope="row"><label for="ds-sso-<?php echo esc_attr( $p ); ?>-logo">Button logo</label></th><td>
					<img class="ds-sso-logo-prev" src="<?php echo esc_url( $o[ $p . '_logo' ] ); ?>" alt="" width="20" height="20" style="vertical-align:middle;margin-right:8px;object-fit:contain;<?php echo '' === (string) $o[ $p . '_logo' ] ? 'display:none' : ''; ?>">
					<button type="button" class="button ds-sso-logo-pick" data-target="ds-sso-<?php echo esc_attr( $p ); ?>-logo">Upload or choose image</button>
					<button type="button" class="button-link ds-sso-logo-clear" data-target="ds-sso-<?php echo esc_attr( $p ); ?>-logo" style="margin-left:10px;color:#b32d2e">Remove</button>
					<p style="margin:8px 0 0"><input class="regular-text code" type="url" id="ds-sso-<?php echo esc_attr( $p ); ?>-logo" name="ds_sso_settings[<?php echo esc_attr( $p ); ?>_logo]" value="<?php echo esc_attr( $o[ $p . '_logo' ] ); ?>" placeholder="https://"></p>
					<p class="description">Optional. Upload the <?php echo esc_html( $cfg['label'] ); ?> logo as a square PNG (about 64 x 64 or larger). It is shown at 20 x 20 on the button. Empty = text only. Press "Save Changes" after choosing.</p></td></tr>
				<tr><th scope="row">Redirect address</th><td><code><?php echo esc_html( ds_sso_url( 'callback', $p ) ); ?></code><p class="description">Copy this exactly into the "Authorized redirect URI" (Google) / "Redirect URI - Web" (Microsoft) box.</p></td></tr>
			</table>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>
		<script>
		(function () {
			function set(id, url) {
				var input = document.getElementById(id); if (!input) return;
				input.value = url || '';
				var img = input.closest('td').querySelector('.ds-sso-logo-prev');
				if (img) { img.src = url || ''; img.style.display = url ? '' : 'none'; }
			}
			document.addEventListener('click', function (e) {
				var pick = e.target.closest ? e.target.closest('.ds-sso-logo-pick') : null;
				var clear = e.target.closest ? e.target.closest('.ds-sso-logo-clear') : null;
				if (clear) { e.preventDefault(); set(clear.getAttribute('data-target'), ''); return; }
				if (!pick || !window.wp || !wp.media) return;
				e.preventDefault();
				var frame = wp.media({ title: 'Choose a logo', button: { text: 'Use this image' }, library: { type: 'image' }, multiple: false });
				frame.on('select', function () {
					var a = frame.state().get('selection').first().toJSON();
					set(pick.getAttribute('data-target'), a.url);
				});
				frame.open();
			});
		})();
		</script>
	</div>
	<?php
}

/* Troubleshooting (admins only): what is attached to the sign-in hooks. Settings > DS Social Login, add &ds_sso_hooks=1 */
function ds_sso_hook_list() {
	global $wp_filter;
	$out = array();
	foreach ( array( 'wp_login', 'set_auth_cookie', 'set_logged_in_cookie', 'set_current_user', 'wp_redirect', 'wp_redirect_status', 'shutdown', 'wp_headers', 'send_headers', 'nocache_headers', 'auth_cookie_expiration', 'secure_auth_cookie', 'send_auth_cookies' ) as $h ) {
		if ( empty( $wp_filter[ $h ] ) ) { continue; }
		foreach ( $wp_filter[ $h ]->callbacks as $prio => $cbs ) {
			foreach ( $cbs as $cb ) {
				$f = $cb['function']; $name = '?'; $where = '';
				try {
					if ( is_string( $f ) ) { $name = $f; $r = new ReflectionFunction( $f ); }
					elseif ( $f instanceof Closure ) { $name = 'closure'; $r = new ReflectionFunction( $f ); }
					elseif ( is_array( $f ) ) { $name = ( is_object( $f[0] ) ? get_class( $f[0] ) : $f[0] ) . '::' . $f[1]; $r = new ReflectionMethod( $f[0], $f[1] ); }
					else { $r = null; }
					if ( $r && $r->getFileName() ) { $where = str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( $r->getFileName() ) ) . ':' . $r->getStartLine(); }
				} catch ( \Throwable $e ) { $where = ''; }
				$out[] = $h . ' [' . $prio . '] ' . $name . ' ' . $where;
			}
		}
	}
	return $out;
}
