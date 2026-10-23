<?php
/**
 * DS Branded Account Emails (2026-09-21)
 *
 * Replaces the WordPress plain-text "Login Details" and password-reset emails
 * with branded HTML, so Gmail stops treating them as phishing.
 *
 * Install: add as a new snippet in Code Snippets (paste WITHOUT the opening
 * <?php line), or drop this file into the DesignStager plugin's includes folder.
 * Deactivate / remove to go back to the WordPress defaults.
 */

if ( ! defined( 'DS_MAIL_SUPPORT' ) ) { define( 'DS_MAIL_SUPPORT', 'support@designstager.com' ); }
// Optional but good for deliverability: your business postal address, e.g.
// define( 'DS_MAIL_ADDRESS', 'DesignStager, 12 MG Road, Bengaluru 560001, India' );
if ( ! defined( 'DS_MAIL_ADDRESS' ) ) { define( 'DS_MAIL_ADDRESS', '' ); }

function ds_mail_first_url( $text ) {
	if ( preg_match( '#https?://\S+#', (string) $text, $m ) ) {
		return rtrim( $m[0], '.,>)' );
	}
	return '';
}

function ds_mail_html_on() {
	$GLOBALS['ds_mail_is_html'] = true;
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( empty( $GLOBALS['ds_mail_is_html'] ) ) { return; }
	$phpmailer->isHTML( true );
	$phpmailer->ContentType = 'text/html';
	if ( empty( $phpmailer->AltBody ) ) {
		$phpmailer->AltBody = wp_strip_all_tags( $phpmailer->Body );
	}
	$GLOBALS['ds_mail_is_html'] = false;
}, 99 );

function ds_mail_template( $heading, $intro, $btn_label, $btn_url, $note = '' ) {
	$brand   = '#6366f1';
	$site    = 'DesignStager';
	$home    = esc_url( home_url( '/' ) );
	$support = sanitize_email( DS_MAIL_SUPPORT );
	$addr    = trim( DS_MAIL_ADDRESS );

	$h  = '<!DOCTYPE html><html><head><meta charset="utf-8">';
	$h .= '<meta name="viewport" content="width=device-width,initial-scale=1"></head>';
	$h .= '<body style="margin:0;padding:0;background:#f4f5f9;">';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f9;padding:32px 12px;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Arial,sans-serif;"><tr><td align="center">';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:14px;border:1px solid #e6e8f0;">';
	$h .= '<tr><td style="padding:28px 32px 6px;"><a href="' . $home . '" style="text-decoration:none;font-size:20px;font-weight:700;color:' . $brand . ';">' . $site . '</a></td></tr>';
	$h .= '<tr><td style="padding:8px 32px 0;"><h1 style="margin:0;font-size:22px;line-height:1.3;color:#161b30;font-weight:700;">' . esc_html( $heading ) . '</h1></td></tr>';
	$h .= '<tr><td style="padding:14px 32px 0;font-size:15px;line-height:1.6;color:#41465c;">' . wp_kses_post( $intro ) . '</td></tr>';

	if ( $btn_url ) {
		$h .= '<tr><td style="padding:24px 32px 4px;"><a href="' . esc_url( $btn_url ) . '" style="display:inline-block;background:' . $brand . ';color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:13px 26px;border-radius:8px;">' . esc_html( $btn_label ) . '</a></td></tr>';
		$h .= '<tr><td style="padding:14px 32px 0;font-size:12px;line-height:1.5;color:#7a8099;">If the button does not work, copy this link into your browser:<br><span style="color:#41465c;word-break:break-all;">' . esc_html( $btn_url ) . '</span></td></tr>';
	}

	if ( $note ) {
		$h .= '<tr><td style="padding:18px 32px 0;font-size:13px;line-height:1.6;color:#7a8099;">' . wp_kses_post( $note ) . '</td></tr>';
	}

	$h .= '<tr><td style="padding:24px 32px 30px;font-size:13px;line-height:1.6;color:#41465c;">Need help? Reply to this email or write to <a href="mailto:' . $support . '" style="color:' . $brand . ';">' . $support . '</a>.</td></tr>';
	$h .= '</table>';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;"><tr><td align="center" style="padding:16px 24px;font-size:12px;line-height:1.6;color:#8b90a6;">' . $site . ' - AI presentations, images and video.';
	if ( $addr ) { $h .= '<br>' . esc_html( $addr ); }
	$h .= '<br>You are receiving this email because an account was created or a password reset was requested at <a href="' . $home . '" style="color:#8b90a6;">designstager.com</a>.</td></tr></table>';
	$h .= '</td></tr></table></body></html>';

	return $h;
}

/* New account: replaces the "[DesignStager] Login Details" email */
add_filter( 'wp_new_user_notification_email', function ( $email, $user ) {
	$url  = ds_mail_first_url( $email['message'] );
	$name = ( ! empty( $user->first_name ) ) ? $user->first_name : $user->user_login;

	$email['subject'] = 'Set your DesignStager password';
	$email['message'] = ds_mail_template(
		'Welcome to DesignStager',
		'Hi ' . esc_html( $name ) . ',<br><br>Your account is ready. Choose a password to finish setting it up, then you can start creating presentations, images and video.',
		'Set my password',
		$url,
		'This link works once. If it has expired, use Forgot password on the login page.'
	);
	$email['headers'] = array( 'Content-Type: text/html; charset=UTF-8' );
	ds_mail_html_on();

	return $email;
}, 20, 2 );

/* Password reset request */
add_filter( 'retrieve_password_title', function () {
	return 'Reset your DesignStager password';
}, 20 );

add_filter( 'retrieve_password_message', function ( $message, $key, $user_login, $user_data ) {
	$url  = ds_mail_first_url( $message );
	$name = ( is_object( $user_data ) && ! empty( $user_data->first_name ) ) ? $user_data->first_name : $user_login;
	ds_mail_html_on();

	return ds_mail_template(
		'Reset your password',
		'Hi ' . esc_html( $name ) . ',<br><br>We received a request to reset the password for your DesignStager account.',
		'Choose a new password',
		$url,
		'If you did not ask for this, you can ignore this email. Your password stays the same.'
	);
}, 20, 4 );

/* Confirmation after a password change */
add_filter( 'password_change_email', function ( $email, $user ) {
	$home = esc_url( home_url( '/' ) );
	$name = ( ! empty( $user['first_name'] ) ) ? $user['first_name'] : $user['user_login'];

	$email['subject'] = 'Your DesignStager password was changed';
	$email['message'] = ds_mail_template(
		'Your password was changed',
		'Hi ' . esc_html( $name ) . ',<br><br>The password for your DesignStager account was just changed.',
		'Go to DesignStager',
		$home,
		'If this was not you, contact us straight away so we can secure the account.'
	);
	$email['headers'] = array( 'Content-Type: text/html; charset=UTF-8' );
	ds_mail_html_on();

	return $email;
}, 20, 2 );

add_filter( 'wp_mail_content_type', function ( $type ) {
	return empty( $GLOBALS['ds_mail_is_html'] ) ? $type : 'text/html';
}, 20 );
