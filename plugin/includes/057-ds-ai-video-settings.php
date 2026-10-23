<?php
/**
 * Code Snippets export — DS AI Video Settings
 *
 * Snippet ID : 743
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-057
 * Modified   : 2026-09-09 02:44:13
 * Load order : 057 of 144
 */
/**
 * Snippet Name: DS AI Video Settings
 * Run: Admin only
 *
 * Fully standalone settings page for the AI Video feature — its own menu
 * entry, its own option (ds_wavespeed_api_key), completely independent of
 * your existing "DS API Keys Settings" snippet. Nothing there is touched.
 *
 * Settings -> AI Video (WaveSpeed)
 */

if ( ! defined('ABSPATH') ) exit;

add_action('admin_menu', function () {
    add_options_page(
        'AI Video (WaveSpeed)',
        'AI Video (WaveSpeed)',
        'manage_options',
        'ds-ai-video-settings',
        'ds_ai_video_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('ds_ai_video_settings_group', 'ds_wavespeed_api_key', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ));
});

function ds_ai_video_settings_page() {
    if ( ! current_user_can('manage_options') ) return;
    $key = get_option('ds_wavespeed_api_key', '');
    ?>
    <div class="wrap">
        <h1>🎬 AI Video (WaveSpeed) Settings</h1>
        <p style="color:#6b7280;">Independent of your other API keys — stored server-side only, never exposed to the browser.</p>
        <form method="post" action="options.php">
            <?php settings_fields('ds_ai_video_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th style="width:200px;"><label for="ds_wavespeed_api_key">WaveSpeed API Key</label></th>
                    <td>
                        <input type="password" id="ds_wavespeed_api_key" name="ds_wavespeed_api_key"
                               value="<?php echo esc_attr($key); ?>" class="regular-text"
                               autocomplete="off" placeholder="wsp_xxxxxxxxxxxxxxxx">
                        <?php if ($key) : ?>
                            <span style="color:#16a34a;margin-left:8px;">✓ Set</span>
                        <?php else : ?>
                            <span style="color:#ef4444;margin-left:8px;">✗ Not set</span>
                        <?php endif; ?>
                        <p class="description">
                            Get your key at
                            <a href="https://wavespeed.ai/accesskey" target="_blank" rel="noopener">wavespeed.ai/accesskey</a>.
                            Used for AI Video generation (Seedance 1.5 Pro).
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save API Key'); ?>
        </form>
        <p>Place the shortcode <code>[ds_ai_video]</code> on your AI Video page.</p>
    </div>
    <?php
}

/* ── Helper used by ds-ai-video-api-callers.php ───────────────────── */
if ( ! function_exists('ds_video_get_wavespeed_key') ) {
function ds_video_get_wavespeed_key() {
    return get_option('ds_wavespeed_api_key', '');
}
}
