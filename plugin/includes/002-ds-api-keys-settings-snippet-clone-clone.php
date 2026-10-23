<?php
/**
 * Code Snippets export — DS API Keys Settings snippet [CLONE] [CLONE]
 *
 * Snippet ID : 688
 * Active     : yes
 * Priority   : 5
 * Scope      : global
 * Tags       : designstager, ds-order-002
 * Modified   : 2026-09-09 02:41:10
 * Load order : 002 of 144
 */
/**
 * Snippet: DS API Keys Settings
 * Run: Admin only
 * Centralizes ALL API keys into one WP Admin settings page.
 * Keys stored in wp_options (server-side only, never exposed to browser).
 * After saving here, update Snippet 2a and Snippet 1a to read from
 * get_option() instead of hardcoded values (instructions below).
 */
if ( ! defined('ABSPATH') ) exit;

/* ── Admin page ── */
add_action('admin_menu', function() {
    add_options_page(
        'DS API Keys',
        'DS API Keys',
        'manage_options',
        'ds-api-keys',
        'ds_api_keys_page'
    );
});

function ds_api_keys_page() {
    if ( ! current_user_can('manage_options') ) return;

    if ( isset($_POST['ds_api_keys_save']) && check_admin_referer('ds_api_keys_nonce') ) {
        $fields = ds_api_keys_fields();
        foreach ( $fields as $key => $field ) {
            $val = isset($_POST[$key]) ? trim($_POST[$key]) : '';
            if ( ! empty($val) ) {
                update_option('ds_key_'.$key, sanitize_text_field($val));
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p><strong>API Keys saved successfully.</strong></p></div>';
    }

    $fields = ds_api_keys_fields();
    ?>
    <div class="wrap">
        <h1>🔑 DS API Keys</h1>
        <p style="color:#6b7280;">All keys are stored server-side only — never exposed to the browser or page source.</p>

        <form method="post" style="max-width:720px;">
            <?php wp_nonce_field('ds_api_keys_nonce'); ?>

            <?php
            $current_group = '';
            foreach ( $fields as $key => $field ) :
                if ( $field['group'] !== $current_group ) :
                    if ( $current_group !== '' ) echo '</table><br>';
                    $current_group = $field['group'];
                    echo '<h2 style="border-bottom:1px solid #e5e7eb;padding-bottom:6px;">'.$field['group'].'</h2>';
                    echo '<table class="form-table">';
                endif;
                $saved = get_option('ds_key_'.$key, '');
                $masked = $saved ? str_repeat('•', min(20, strlen($saved)-4)).substr($saved,-4) : '';
            ?>
            <tr>
                <th style="width:200px;"><label for="<?php echo $key; ?>"><?php echo esc_html($field['label']); ?></label></th>
                <td>
                    <input type="password"
                           id="<?php echo $key; ?>"
                           name="<?php echo $key; ?>"
                           value=""
                           placeholder="<?php echo $saved ? 'Current: '.$masked.' (leave blank to keep)' : 'Enter '.$field['label']; ?>"
                           class="regular-text"
                           autocomplete="new-password">
                    <?php if ( $saved ) : ?>
                        <span style="color:#16a34a;margin-left:8px;">✓ Set</span>
                    <?php else : ?>
                        <span style="color:#ef4444;margin-left:8px;">✗ Not set</span>
                    <?php endif; ?>
                    <?php if ( ! empty($field['help']) ) : ?>
                        <p class="description"><?php echo $field['help']; ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </table>

            <br>
            <?php submit_button('Save All Keys', 'primary large', 'ds_api_keys_save'); ?>
        </form>

        <hr>
        <h2>⚙️ PPT Generation Model</h2>
        <?php
        if ( isset($_POST['ds_model_save']) && check_admin_referer('ds_api_keys_nonce') ) {
            update_option('ds_groq_ppt_model', sanitize_text_field($_POST['ds_groq_ppt_model']));
            echo '<div class="notice notice-success is-dismissible"><p>Model saved!</p></div>';
        }
        $model = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');
        ?>
        <form method="post" style="max-width:720px;">
            <?php wp_nonce_field('ds_api_keys_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th style="width:200px;"><label for="ds_groq_ppt_model">Groq PPT Model</label></th>
                    <td>
                        <input type="text" id="ds_groq_ppt_model" name="ds_groq_ppt_model"
                               value="<?php echo esc_attr($model); ?>"
                               class="regular-text">
                        <p class="description">
                            Current: <strong><?php echo esc_html($model); ?></strong> —
                            <a href="https://console.groq.com/docs/models" target="_blank">View available models →</a>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Model', 'secondary', 'ds_model_save'); ?>
        </form>
    </div>
    <?php
}

function ds_api_keys_fields() {
    return array(
        'turnstile_site'   => array('group'=>'Anti-Bot / Security','label'=>'Cloudflare Turnstile Site Key','help'=>'Public key, safe to expose in page HTML. Shown on the signup form. From the Cloudflare dashboard → Turnstile.'),
        'turnstile_secret' => array('group'=>'Anti-Bot / Security','label'=>'Cloudflare Turnstile Secret Key','help'=>'Private key, used server-side only to verify signup tokens. Never exposed to the browser.'),
        'groq'                  => array('group'=>'AI Generation','label'=>'Groq API Key','help'=>'Used for AI presentation generation. Get from console.groq.com'),
        'huggingface'           => array('group'=>'AI Generation','label'=>'HuggingFace Token','help'=>'Used for AI image generation inside PPT editor.'),
        'cloudflare_account_id' => array('group'=>'AI Generation','label'=>'Cloudflare Account ID','help'=>'Used for Cloudflare image generation model.'),
        'cloudflare_token'      => array('group'=>'AI Generation','label'=>'Cloudflare API Token','help'=>'Used for Cloudflare image generation model.'),
        'replicate'             => array('group'=>'AI Generation','label'=>'Replicate API Key','help'=>'Used for Flux/Seedream/NanoBanana image models.'),
        'gemini'                => array('group'=>'AI Generation','label'=>'Gemini API Key','help'=>'Used for Google Gemini AI features.'),
        'openai'                => array('group'=>'AI Generation','label'=>'OpenAI API Key','help'=>'Used for OpenAI features if enabled.'),
        'openrouter'            => array('group'=>'AI Generation','label'=>'OpenRouter API Key','help'=>'Used for OpenRouter model access.'),
        'razorpay_key_id'       => array('group'=>'Payments','label'=>'Razorpay Key ID','help'=>'Your Razorpay publishable key.'),
        'razorpay_key_secret'   => array('group'=>'Payments','label'=>'Razorpay Key Secret','help'=>'Your Razorpay secret key. Never exposed to frontend.'),
    );
}

/* ── Helper: get any key from settings (falls back to old hardcoded value) ── */
if ( ! function_exists('ds_get_key') ) {
function ds_get_key( $name, $fallback = '' ) {
    $val = get_option('ds_key_'.$name, '');
    return $val ?: $fallback;
}
}
