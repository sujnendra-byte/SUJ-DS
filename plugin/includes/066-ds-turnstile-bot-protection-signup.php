<?php
/**
 * Code Snippets export — DS Turnstile Bot Protection (Signup)
 *
 * Snippet ID : 752
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-066
 * Modified   : 2026-09-09 02:46:46
 * Load order : 066 of 144
 */
/**
 * Snippet: DS Turnstile Bot Protection (Signup)
 * Run: Everywhere
 *
 * Adds Cloudflare Turnstile to the /register/ signup form (Theme My Login,
 * which re-fires the standard WP register_form / registration_errors hooks)
 * and validates the token server-side before an account is created.
 *
 * Reads keys from the DS API Keys settings page (Anti-Bot / Security section).
 * If either key is empty, this snippet is a complete no-op -- signup behaves
 * exactly as before. Fill in both keys there to turn protection on.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('register_form', function() {
    echo '<!-- ds-turnstile-register-form-hook-fired -->';
    $site_key = get_option('ds_key_turnstile_site');
    if ( empty($site_key) ) return;
    ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($site_key); ?>" data-action="signup" style="margin-bottom:16px;"></div>
    <?php
});

add_filter('registration_errors', function($errors, $sanitized_user_login, $user_email) {
    $secret   = get_option('ds_key_turnstile_secret');
    $site_key = get_option('ds_key_turnstile_site');
    if ( empty($secret) || empty($site_key) ) return $errors;

    $token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field( wp_unslash($_POST['cf-turnstile-response']) ) : '';
    if ( empty($token) || strlen($token) > 2048 ) {
        $errors->add('turnstile_missing', __('<strong>Error</strong>: Please complete the verification challenge.'));
        return $errors;
    }

    $resp = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
        'timeout' => 10,
        'body'    => array(
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
        ),
    ));

    if ( is_wp_error($resp) ) {
        $errors->add('turnstile_error', __('<strong>Error</strong>: Verification service unavailable, please try again.'));
        return $errors;
    }

    $body = json_decode( wp_remote_retrieve_body($resp), true );
    $ok = is_array($body) && ! empty($body['success']);

    if ( $ok && ! empty($body['action']) && $body['action'] !== 'signup' ) {
        $ok = false;
    }

    if ( ! $ok ) {
        $errors->add('turnstile_failed', __('<strong>Error</strong>: Verification failed. Please try again.'));
    }

    return $errors;
}, 10, 3);
