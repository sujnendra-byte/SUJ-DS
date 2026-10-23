<?php
/**
 * Code Snippets export — Snippet 36b ds copyright admin
 *
 * Snippet ID : 740
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-054
 * Modified   : 2026-09-09 02:46:48
 * Load order : 054 of 144
 */
/**
 * Snippet 36B: DS Copyright Claim Admin Editor
 * Run: Everywhere
 * Works alongside Snippet 34.
 *
 * Adds wp-admin -> Settings -> Copyright Claim Page
 * where you can edit the page title, intro text, warning notice,
 * and email address — without touching any code.
 */

if ( ! defined('ABSPATH') ) exit;

// ── 1. Admin menu page ───────────────────────────────────────────────────────

add_action('admin_menu', function () {
    add_options_page(
        'Copyright Claim Editor',
        'Copyright Claim Page',
        'manage_options',
        'ds-copyright-editor',
        'ds_copyright_admin_page'
    );
});

// ── 2. Save handler ──────────────────────────────────────────────────────────

add_action('admin_init', function () {
    if (
        isset($_POST['ds_copyright_save'])
        && check_admin_referer('ds_copyright_save_action', 'ds_copyright_nonce')
        && current_user_can('manage_options')
    ) {
        update_option('ds_cr_hero_title',    sanitize_text_field($_POST['ds_cr_hero_title']    ?? 'Copyright Claim'));
        update_option('ds_cr_hero_subtitle', sanitize_text_field($_POST['ds_cr_hero_subtitle'] ?? ''));
        update_option('ds_cr_warning_text',  wp_kses_post($_POST['ds_cr_warning_text']         ?? ''));
        update_option('ds_cr_legal_email',   sanitize_email($_POST['ds_cr_legal_email']        ?? 'legal@designstager.com'));
        update_option('ds_cr_response_days', sanitize_text_field($_POST['ds_cr_response_days'] ?? '3-5'));
        update_option('ds_cr_max_files',     intval($_POST['ds_cr_max_files']                  ?? 3));
        update_option('ds_cr_max_file_size', intval($_POST['ds_cr_max_file_size']              ?? 5));
        update_option('ds_cr_rate_limit',    intval($_POST['ds_cr_rate_limit']                 ?? 3));

        // Save custom form fields visibility
        $fields = array('phone', 'country', 'original_url');
        $show   = array();
        foreach ($fields as $f) {
            $show[$f] = isset($_POST['ds_cr_show_' . $f]) ? 1 : 0;
        }
        update_option('ds_cr_show_fields', $show);

        wp_redirect(admin_url('options-general.php?page=ds-copyright-editor&saved=1'));
        exit;
    }
});

// ── 3. Admin page HTML ───────────────────────────────────────────────────────

if ( ! function_exists('ds_copyright_admin_page') ) {
function ds_copyright_admin_page() {
    $saved = isset($_GET['saved']);

    $hero_title    = get_option('ds_cr_hero_title',    'Copyright Claim');
    $hero_subtitle = get_option('ds_cr_hero_subtitle', 'If you believe your copyrighted work has been used without permission on DesignStager, submit a claim below. We take intellectual property seriously and will respond within 3-5 business days.');
    $warning_text  = get_option('ds_cr_warning_text',  'Please ensure you are the <strong>copyright owner</strong> or an authorized representative. Filing a false claim may result in legal liability.');
    $legal_email   = get_option('ds_cr_legal_email',   'legal@designstager.com');
    $response_days = get_option('ds_cr_response_days', '3-5');
    $max_files     = get_option('ds_cr_max_files',     3);
    $max_file_size = get_option('ds_cr_max_file_size', 5);
    $rate_limit    = get_option('ds_cr_rate_limit',    3);
    $show_fields   = get_option('ds_cr_show_fields',   array('phone' => 1, 'country' => 1, 'original_url' => 1));
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
    #ds-cr-admin * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
    #ds-cr-admin { max-width: 780px; padding: 20px 0 60px; }
    #ds-cr-admin h1 { font-size: 22px; font-weight: 800; color: #1B2A4A; margin: 0 0 6px; }
    #ds-cr-admin h1 span { color: #E8481C; }
    .ds-cr-saved { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 16px; color: #16a34a; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
    .ds-cr-card { background: #fff; border: 1px solid #e8edf5; border-radius: 12px; padding: 22px 24px; margin-bottom: 16px; box-shadow: 0 2px 10px rgba(27,42,74,.06); }
    .ds-cr-card h3 { font-size: 13px; font-weight: 800; color: #1B2A4A; margin: 0 0 16px; padding-bottom: 10px; border-bottom: 2px solid #e8edf5; text-transform: uppercase; letter-spacing: .5px; }
    .ds-cr-field { margin-bottom: 16px; }
    .ds-cr-field label { display: block; font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .4px; }
    .ds-cr-field input[type=text], .ds-cr-field input[type=email], .ds-cr-field input[type=number], .ds-cr-field textarea { width: 100%; padding: 9px 13px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #1a1a2e; }
    .ds-cr-field input:focus, .ds-cr-field textarea:focus { outline: none; border-color: #E8481C; }
    .ds-cr-field textarea { resize: vertical; min-height: 80px; line-height: 1.6; }
    .ds-cr-field .hint { font-size: 10px; color: #9ca3af; margin-top: 4px; }
    .ds-cr-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ds-cr-checkbox-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .ds-cr-checkbox-row input[type=checkbox] { width: 16px; height: 16px; accent-color: #E8481C; }
    .ds-cr-checkbox-row label { font-size: 13px; color: #374151; font-weight: 500; }
    .ds-cr-save { background: linear-gradient(135deg, #E8481C, #c93a15); color: #fff; border: none; border-radius: 8px; padding: 12px 28px; font-size: 14px; font-weight: 800; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .ds-cr-save:hover { opacity: .9; }
    </style>

    <div id="ds-cr-admin">
        <h1>Copyright <span>Claim</span> Page Editor</h1>
        <p style="color:#6b7280;font-size:13px;margin:4px 0 20px;">Edit your Copyright Claim page settings here. Changes appear instantly on the live page after saving.</p>

        <?php if ($saved): ?>
        <div class="ds-cr-saved">✅ Copyright Claim page settings saved successfully!</div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('ds_copyright_save_action', 'ds_copyright_nonce'); ?>
            <input type="hidden" name="ds_copyright_save" value="1">

            <!-- Hero Section -->
            <div class="ds-cr-card">
                <h3>Hero Section</h3>
                <div class="ds-cr-field">
                    <label>Page Title (the orange word)</label>
                    <input type="text" name="ds_cr_hero_title" value="<?php echo esc_attr($hero_title); ?>" placeholder="Copyright Claim">
                    <div class="hint">Shows as "Copyright <span style="color:#E8481C;">[your title]</span>" in the hero banner.</div>
                </div>
                <div class="ds-cr-field">
                    <label>Hero Subtitle / Description</label>
                    <textarea name="ds_cr_hero_subtitle"><?php echo esc_textarea($hero_subtitle); ?></textarea>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="ds-cr-card">
                <h3>Warning Notice (shown above the form)</h3>
                <div class="ds-cr-field">
                    <label>Warning Text (HTML allowed)</label>
                    <textarea name="ds_cr_warning_text" style="min-height:100px;"><?php echo esc_textarea($warning_text); ?></textarea>
                    <div class="hint">You can use &lt;strong&gt;, &lt;a href="..."&gt;, &lt;em&gt; tags.</div>
                </div>
            </div>

            <!-- Contact & Response -->
            <div class="ds-cr-card">
                <h3>Contact & Response Settings</h3>
                <div class="ds-cr-row">
                    <div class="ds-cr-field">
                        <label>Legal Email Address</label>
                        <input type="email" name="ds_cr_legal_email" value="<?php echo esc_attr($legal_email); ?>" placeholder="legal@designstager.com">
                        <div class="hint">Admin notification emails go here. Also shown in the warning notice.</div>
                    </div>
                    <div class="ds-cr-field">
                        <label>Response Time (days)</label>
                        <input type="text" name="ds_cr_response_days" value="<?php echo esc_attr($response_days); ?>" placeholder="3-5">
                        <div class="hint">Shown in confirmation email e.g. "3-5 business days".</div>
                    </div>
                </div>
            </div>

            <!-- File Upload Settings -->
            <div class="ds-cr-card">
                <h3>File Upload Settings</h3>
                <div class="ds-cr-row">
                    <div class="ds-cr-field">
                        <label>Max Files per Submission</label>
                        <input type="number" name="ds_cr_max_files" value="<?php echo intval($max_files); ?>" min="1" max="10">
                        <div class="hint">How many proof documents a claimant can upload.</div>
                    </div>
                    <div class="ds-cr-field">
                        <label>Max File Size (MB)</label>
                        <input type="number" name="ds_cr_max_file_size" value="<?php echo intval($max_file_size); ?>" min="1" max="20">
                        <div class="hint">Maximum size per uploaded file in megabytes.</div>
                    </div>
                </div>
                <div class="ds-cr-field">
                    <label>Rate Limit (submissions per IP per hour)</label>
                    <input type="number" name="ds_cr_rate_limit" value="<?php echo intval($rate_limit); ?>" min="1" max="20" style="width:120px;">
                    <div class="hint">Prevents spam. Recommended: 3.</div>
                </div>
            </div>

            <!-- Form Fields Visibility -->
            <div class="ds-cr-card">
                <h3>Optional Form Fields</h3>
                <p style="font-size:12px;color:#6b7280;margin:0 0 14px;">Choose which optional fields to show on the claim form. Required fields (name, email, company, work description, infringing URL, claim statement) are always shown.</p>
                <div class="ds-cr-checkbox-row">
                    <input type="checkbox" name="ds_cr_show_phone" id="ds_cr_show_phone" value="1" <?php checked(!empty($show_fields['phone'])); ?>>
                    <label for="ds_cr_show_phone">Phone Number field</label>
                </div>
                <div class="ds-cr-checkbox-row">
                    <input type="checkbox" name="ds_cr_show_country" id="ds_cr_show_country" value="1" <?php checked(!empty($show_fields['country'])); ?>>
                    <label for="ds_cr_show_country">Country field</label>
                </div>
                <div class="ds-cr-checkbox-row">
                    <input type="checkbox" name="ds_cr_show_original_url" id="ds_cr_show_original_url" value="1" <?php checked(!empty($show_fields['original_url'])); ?>>
                    <label for="ds_cr_show_original_url">Original Work URL field</label>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:14px;margin-top:4px;">
                <button type="submit" class="ds-cr-save">💾 Save Settings</button>
                <a href="<?php echo esc_url(home_url('/copyright-claim/')); ?>" target="_blank"
                   style="font-size:12px;color:#E8481C;font-weight:700;text-decoration:none;">View live page →</a>
            </div>
        </form>
    </div>
    <?php
}
}

// ── 4. Filters so Snippet 34 reads from admin settings ──────────────────────

add_filter('ds_cr_hero_title',    fn() => get_option('ds_cr_hero_title',    'Claim'));
add_filter('ds_cr_hero_subtitle', fn() => get_option('ds_cr_hero_subtitle', 'If you believe your copyrighted work has been used without permission on DesignStager, submit a claim below.'));
add_filter('ds_cr_warning_text',  fn() => get_option('ds_cr_warning_text',  'Please ensure you are the <strong>copyright owner</strong> or an authorized representative.'));
add_filter('ds_cr_legal_email',   fn() => get_option('ds_cr_legal_email',   'legal@designstager.com'));
add_filter('ds_cr_response_days', fn() => get_option('ds_cr_response_days', '3-5'));
add_filter('ds_cr_max_files',     fn() => intval(get_option('ds_cr_max_files', 3)));
add_filter('ds_cr_max_file_size', fn() => intval(get_option('ds_cr_max_file_size', 5)));
add_filter('ds_cr_rate_limit',    fn() => intval(get_option('ds_cr_rate_limit', 3)));
add_filter('ds_cr_show_fields',   fn() => get_option('ds_cr_show_fields', array('phone'=>1,'country'=>1,'original_url'=>1)));
