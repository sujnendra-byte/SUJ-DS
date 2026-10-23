<?php
/**
 * Code Snippets export — DS AI Settings
 *
 * Snippet ID : 723
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-037
 * Modified   : 2026-09-09 02:46:16
 * Load order : 037 of 144
 */
add_action('admin_menu', function() {
    add_options_page('DS AI Settings', 'DS AI Settings', 'manage_options', 'ds-ai-settings', 'ds_ai_settings_page');
});

function ds_ai_settings_page() {
    if ( isset($_POST['ds_groq_model_save']) && check_admin_referer('ds_ai_settings') ) {
        update_option('ds_groq_ppt_model', sanitize_text_field($_POST['ds_groq_ppt_model']));
        echo '<div class="notice notice-success"><p>Saved!</p></div>';
    }
    $current = get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile');
    ?>
    <div class="wrap">
        <h1>DS AI Settings</h1>
        <form method="post">
            <?php wp_nonce_field('ds_ai_settings'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="ds_groq_ppt_model">Groq Model for PPT Generation</label></th>
                    <td>
                        <input type="text" id="ds_groq_ppt_model" name="ds_groq_ppt_model"
                               value="<?php echo esc_attr($current); ?>"
                               class="regular-text">
                        <p class="description">
                            Current: <strong><?php echo esc_html($current); ?></strong><br>
                            Find available models at <a href="https://console.groq.com/docs/models" target="_blank">console.groq.com/docs/models</a>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save', 'primary', 'ds_groq_model_save'); ?>
        </form>
    </div>
    <?php
}
