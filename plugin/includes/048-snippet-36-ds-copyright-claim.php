<?php
/**
 * Code Snippets export — Snippet 36 DS Copyright Claim
 *
 * Snippet ID : 734
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-048
 * Modified   : 2026-09-09 02:41:26
 * Load order : 048 of 144
 */
/**
 * Snippet 36: DS Copyright Claim Page
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * Registers [ds_copyright_claim] shortcode for a Copyright / DMCA takedown
 * page where companies/individuals can:
 *   1. Fill in their contact + claim details
 *   2. Upload proof documents (PDF, JPG, PNG — max 5MB each, up to 3 files)
 *   3. Submit the claim → saved as a CPT (ds_copyright_claim) for admin review
 *   4. Admin receives email notification with submitted details + file links
 *
 * ADMIN: Posts → Copyright Claims — review all submissions there.
 *
 * Colors match DesignStager brand: #E8481C (red), #1B2A4A (navy)
 */

if ( ! defined('ABSPATH') ) exit;

// ─── 1. CPT for storing claims ───────────────────────────────────────────────

add_action('init', function () {
    register_post_type('ds_copyright_claim', array(
        'label'        => 'Copyright Claims',
        'labels'       => array(
            'name'          => 'Copyright Claims',
            'singular_name' => 'Copyright Claim',
            'all_items'     => 'All Copyright Claims',
        ),
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-shield',
        'supports'     => array('title', 'custom-fields'),
        'capabilities' => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap' => true,
    ));
});

// ─── 2. REST endpoint: submit claim ─────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/submit-copyright-claim', array(
        'methods'             => 'POST',
        'callback'            => 'ds_copyright_handle_claim',
        'permission_callback' => '__return_true',
    ));
});

if ( ! function_exists('ds_copyright_handle_claim') ) {
function ds_copyright_handle_claim( WP_REST_Request $req ) {

    // Rate limit: 3 submissions per IP per hour (stored in transient)
    $ip      = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $rl_key  = 'ds_cr_rl_' . md5($ip);
    $rl_count = (int) get_transient($rl_key);
    if ( $rl_count >= apply_filters('ds_cr_rate_limit', 3) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Too many submissions from your IP. Please wait an hour and try again.'));
    }

    // Required fields
    $required = array('claimant_name','claimant_email','company_name','work_description','infringing_url','claim_statement');
    $data     = array();
    foreach ($required as $field) {
        $val = sanitize_text_field($req->get_param($field) ?? '');
        if ( empty($val) ) {
            return rest_ensure_response(array('success' => false, 'error' => 'Missing required field: ' . $field));
        }
        $data[$field] = $val;
    }
    $data['claimant_email'] = sanitize_email($data['claimant_email']);
    if ( ! is_email($data['claimant_email']) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Invalid email address.'));
    }
    $data['infringing_url'] = esc_url_raw($data['infringing_url']);
    $data['phone']          = sanitize_text_field($req->get_param('phone') ?? '');
    $data['country']        = sanitize_text_field($req->get_param('country') ?? '');
    $data['original_url']   = esc_url_raw($req->get_param('original_url') ?? '');
    $data['sworn']          = (bool) $req->get_param('sworn');
    $data['submitted_ip']   = $ip;
    $data['submitted_at']   = current_time('mysql');

    // Handle uploaded files (base64 via JSON body)
    $files      = $req->get_param('files') ?: array();
    $saved_urls = array();
    $allowed    = array('image/jpeg', 'image/png', 'application/pdf');
    $upload_dir = wp_upload_dir();
    $claim_dir  = trailingslashit($upload_dir['basedir']) . 'ds-copyright-claims';
    if ( ! file_exists($claim_dir) ) wp_mkdir_p($claim_dir);

    if ( is_array($files) ) {
        foreach ( array_slice($files, 0, apply_filters('ds_cr_max_files', 3)) as $fi => $file ) {
            $mime     = sanitize_text_field($file['type'] ?? '');
            $b64data  = $file['data'] ?? '';
            $filename = sanitize_file_name($file['name'] ?? 'file');
            if ( ! in_array($mime, $allowed, true) || empty($b64data) ) continue;

            $binary = base64_decode($b64data, true);
            $max_mb = apply_filters('ds_cr_max_file_size', 5);
            if ( ! $binary || strlen($binary) > $max_mb * 1024 * 1024 ) continue;

            $ext   = pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin';
            $fname = 'claim-' . time() . '-' . wp_generate_password(6, false, false) . '.' . $ext;
            $fpath = trailingslashit($claim_dir) . $fname;

            file_put_contents($fpath, $binary);

            // Protect from direct access
            $htaccess = trailingslashit($claim_dir) . '.htaccess';
            if ( ! file_exists($htaccess) ) {
                file_put_contents($htaccess, "Options -Indexes\n<Files *.php>\nDeny from all\n</Files>");
            }

            $saved_urls[] = trailingslashit($upload_dir['baseurl']) . 'ds-copyright-claims/' . $fname;
        }
    }

    // Create the CPT post
    $post_id = wp_insert_post(array(
        'post_type'   => 'ds_copyright_claim',
        'post_title'  => sanitize_text_field($data['company_name'] . ' — ' . $data['claimant_email']),
        'post_status' => 'private',
    ));

    if ( is_wp_error($post_id) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Could not save claim. Please try again.'));
    }

    foreach ($data as $key => $val) {
        update_post_meta($post_id, '_ds_claim_' . $key, $val);
    }
    update_post_meta($post_id, '_ds_claim_files', $saved_urls);
    update_post_meta($post_id, '_ds_claim_status', 'pending');

    // Email admin
    $admin_email = get_option('admin_email');
    $legal_email = apply_filters('ds_cr_legal_email', 'legal@designstager.com');
    $files_list  = $saved_urls ? "\n\nUploaded files:\n" . implode("\n", $saved_urls) : '';
    wp_mail(
        $admin_email,
        '[DesignStager] New Copyright Claim #' . $post_id . ' — ' . $data['company_name'],
        "A new copyright claim has been submitted.\n\n"
        . "Claimant: {$data['claimant_name']} ({$data['claimant_email']})\n"
        . "Company:  {$data['company_name']}\n"
        . "Phone:    {$data['phone']}\n"
        . "Country:  {$data['country']}\n\n"
        . "Work description:\n{$data['work_description']}\n\n"
        . "Infringing URL: {$data['infringing_url']}\n"
        . "Original work URL: {$data['original_url']}\n\n"
        . "Claim statement:\n{$data['claim_statement']}\n\n"
        . "Sworn statement: " . ($data['sworn'] ? 'Yes' : 'No') . "\n"
        . "Submitted from IP: {$data['submitted_ip']}\n"
        . "Date: {$data['submitted_at']}"
        . $files_list
        . "\n\nReview: " . admin_url('edit.php?post_type=ds_copyright_claim'),
        array('Content-Type: text/plain; charset=UTF-8')
    );

    // Confirmation email to claimant
    wp_mail(
        $data['claimant_email'],
        'DesignStager — Copyright Claim Received (#' . $post_id . ')',
        "Dear {$data['claimant_name']},\n\n"
        . "Thank you for submitting your copyright claim to DesignStager. We have received your claim (Reference #: {$post_id}) and will review it within 3–5 business days.\n\n"
        . "If we need additional information, we will contact you at this email address.\n\n"
        . "Details submitted:\n"
        . "Company: {$data['company_name']}\n"
        . "Infringing URL: {$data['infringing_url']}\n\n"
        . "— The DesignStager Legal Team\nlegal@designstager.com",
        array('Content-Type: text/plain; charset=UTF-8')
    );

    set_transient($rl_key, $rl_count + 1, HOUR_IN_SECONDS);

    return rest_ensure_response(array('success' => true, 'claim_id' => $post_id));
}
}

// ─── 3. Admin column: show claim status ──────────────────────────────────────

add_filter('manage_ds_copyright_claim_posts_columns', function ($cols) {
    return array(
        'cb'          => $cols['cb'],
        'title'       => 'Claimant / Company',
        'ds_status'   => 'Status',
        'ds_url'      => 'Infringing URL',
        'ds_files'    => 'Files',
        'ds_date'     => 'Submitted',
    );
});

add_action('manage_ds_copyright_claim_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'ds_status':
            $s = get_post_meta($post_id, '_ds_claim_status', true) ?: 'pending';
            $colors = array('pending' => '#E8481C', 'reviewing' => '#1d4ed8', 'resolved' => '#16a34a', 'rejected' => '#6b7280');
            echo '<span style="background:' . ($colors[$s] ?? '#888') . ';color:#fff;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700;">' . esc_html(ucfirst($s)) . '</span>';
            break;
        case 'ds_url':
            $url = get_post_meta($post_id, '_ds_claim_infringing_url', true);
            if ($url) echo '<a href="' . esc_url($url) . '" target="_blank" style="color:#E8481C;font-size:11px;">' . esc_url($url) . '</a>';
            break;
        case 'ds_files':
            $files = get_post_meta($post_id, '_ds_claim_files', true) ?: array();
            foreach ((array)$files as $i => $f) {
                echo '<a href="' . esc_url($f) . '" target="_blank" style="display:block;font-size:11px;color:#1B2A4A;">📎 File ' . ($i+1) . '</a>';
            }
            if (empty($files)) echo '<span style="color:#9ca3af;font-size:11px;">None</span>';
            break;
        case 'ds_date':
            echo esc_html(get_post_meta($post_id, '_ds_claim_submitted_at', true) ?: '—');
            break;
    }
}, 10, 2);

// ─── 4. Admin: status update dropdown on claim edit screen ───────────────────

add_action('add_meta_boxes_ds_copyright_claim', function () {
    add_meta_box('ds_claim_details', '📋 Claim Details', 'ds_claim_details_box', 'ds_copyright_claim', 'normal', 'high');
});

if ( ! function_exists('ds_claim_details_box') ) {
function ds_claim_details_box( $post ) {
    wp_nonce_field('ds_claim_status_nonce', 'ds_claim_nonce');
    $fields = array(
        'claimant_name'   => 'Claimant Name',
        'claimant_email'  => 'Email',
        'company_name'    => 'Company',
        'phone'           => 'Phone',
        'country'         => 'Country',
        'work_description'=> 'Work Description',
        'infringing_url'  => 'Infringing URL',
        'original_url'    => 'Original Work URL',
        'claim_statement' => 'Claim Statement',
        'sworn'           => 'Sworn Statement',
        'submitted_ip'    => 'Submitted From IP',
        'submitted_at'    => 'Submitted At',
    );
    $status = get_post_meta($post->ID, '_ds_claim_status', true) ?: 'pending';
    $files  = get_post_meta($post->ID, '_ds_claim_files', true) ?: array();
    ?>
    <table class="form-table" style="font-size:13px;">
    <tr><th style="width:160px;">Status</th><td>
        <select name="ds_claim_status_select" style="font-weight:700;">
            <?php foreach (array('pending','reviewing','resolved','rejected') as $s): ?>
            <option value="<?php echo $s; ?>" <?php selected($status, $s); ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
        </select>
    </td></tr>
    <?php foreach ($fields as $key => $label): ?>
    <tr><th><?php echo esc_html($label); ?></th><td style="word-break:break-all;">
        <?php $v = get_post_meta($post->ID, '_ds_claim_' . $key, true);
              echo is_array($v) ? esc_html(implode(', ', $v)) : esc_html($v ?: '—'); ?>
    </td></tr>
    <?php endforeach; ?>
    <?php if ($files): ?>
    <tr><th>Uploaded Files</th><td>
        <?php foreach ($files as $i => $f): ?>
        <a href="<?php echo esc_url($f); ?>" target="_blank">📎 Download File <?php echo $i + 1; ?></a><br>
        <?php endforeach; ?>
    </td></tr>
    <?php endif; ?>
    </table>
    <?php
}
}

add_action('save_post_ds_copyright_claim', function ($post_id) {
    if (!isset($_POST['ds_claim_nonce']) || !wp_verify_nonce($_POST['ds_claim_nonce'], 'ds_claim_status_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    $status = sanitize_text_field($_POST['ds_claim_status_select'] ?? 'pending');
    if (in_array($status, array('pending','reviewing','resolved','rejected'), true)) {
        update_post_meta($post_id, '_ds_claim_status', $status);
    }
});

// ─── 5. [ds_copyright_claim] shortcode — public form ────────────────────────

add_shortcode('ds_copyright_claim', function () {
    $nonce = wp_create_nonce('wp_rest');
    $api   = rest_url('ds/v1/submit-copyright-claim');

    ob_start();
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');
    body.page.page-id-10035, html body.page-id-10035 { background: #10131c !important; }
    body.page-id-10035 .entry-content, body.page-id-10035 .page-content, body.page-id-10035 .site-main { background: #10131c !important; }
    .ds-cr-wrap { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #10131c; min-height: 60vh; padding: 0 0 60px; }
    .ds-cr-hero { background: linear-gradient(135deg, #10131c 0%, #191c25 65%, #1f222b 100%); color: #fff; padding: 52px 24px 44px; text-align: center; }
    .ds-cr-hero h1 { margin: 0 0 8px; font-size: clamp(24px, 5vw, 42px); font-weight: 900; letter-spacing: -1px; color: #fff; }
    .ds-cr-hero h1 span { color: #E8481C; }
    .ds-cr-hero p { margin: 0; color: #C8D8F0; font-size: 14px; max-width: 560px; margin: 8px auto 0; line-height: 1.6; }
    .ds-cr-container { max-width: 760px; margin: 0 auto; padding: 40px 24px 0; }
    .ds-cr-notice { background: #191c25; border-left: 4px solid #E8481C; border-radius: 10px; padding: 16px 20px; margin-bottom: 28px; font-size: 13px; color: #cbd5e1; line-height: 1.65; }
    .ds-cr-notice strong { color: #fff; }
    .ds-cr-notice a { color: #E8481C; }
    .ds-cr-form { background: #191c25; border-radius: 16px; padding: 32px; box-shadow: 0 4px 24px rgba(0,0,0,.3); }
    .ds-cr-section-title { font-size: 13px; font-weight: 800; color: #fff; letter-spacing: .5px; text-transform: uppercase; margin: 0 0 16px; padding-bottom: 8px; border-bottom: 2px solid rgba(255,255,255,.15); display: flex; align-items: center; gap: 8px; }
    .ds-cr-row { margin-bottom: 18px; }
    .ds-cr-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px; }
    .ds-cr-label { display: block; font-size: 12px; font-weight: 700; color: #cbd5e1; margin-bottom: 6px; }
    .ds-cr-label .req { color: #E8481C; }
    .ds-cr-input, .ds-cr-textarea, .ds-cr-select { width: 100%; padding: 10px 14px; border: 2px solid rgba(255,255,255,.12) !important; border-radius: 8px; font-size: 14px; color: #e5e7eb !important; font-family: inherit; background: #10131c !important; transition: border-color .15s; box-sizing: border-box; }
    .ds-cr-input:focus, .ds-cr-textarea:focus { outline: none; border-color: #E8481C !important; background: #191c25 !important; }
    .ds-cr-textarea { resize: vertical; min-height: 100px; }
    .ds-cr-upload-zone { border: 2px dashed rgba(255,255,255,.15); border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; position: relative; }
    .ds-cr-upload-zone:hover, .ds-cr-upload-zone.drag { border-color: #E8481C; background: #241811; }
    .ds-cr-upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .ds-cr-upload-icon { font-size: 32px; margin-bottom: 8px; }
    .ds-cr-upload-hint { font-size: 12px; color: #8b97af; }
    .ds-cr-file-list { margin-top: 12px; display: flex; flex-direction: column; gap: 6px; }
    .ds-cr-file-item { display: flex; align-items: center; gap: 8px; background: #10131c; border-radius: 7px; padding: 8px 12px; font-size: 12px; color: #e5e7eb; }
    .ds-cr-file-item .size { color: #8b97af; margin-left: auto; }
    .ds-cr-file-remove { background: none; border: none; color: #E8481C; cursor: pointer; font-size: 14px; font-weight: 900; padding: 0 2px; line-height: 1; }
    .ds-cr-checkbox-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }
    .ds-cr-checkbox-row input[type=checkbox] { margin-top: 2px; accent-color: #E8481C; width: 16px; height: 16px; flex-shrink: 0; }
    .ds-cr-checkbox-row label { font-size: 13px; color: #cbd5e1; line-height: 1.55; }
    .ds-cr-submit { width: 100%; padding: 14px; background: linear-gradient(135deg, #E8481C, #c93a15); color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 800; cursor: pointer; letter-spacing: .3px; transition: opacity .15s, transform .1s; font-family: inherit; }
    .ds-cr-submit:hover { opacity: .92; }
    .ds-cr-submit:active { transform: scale(.98); }
    .ds-cr-submit:disabled { opacity: .5; cursor: default; }
    .ds-cr-error { background: #2a1414; border: 1px solid #7f1d1d; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #fca5a5; margin-bottom: 16px; display: none; }
    .ds-cr-success { background: #0f2418; border: 2px solid #166534; border-radius: 14px; padding: 32px; text-align: center; display: none; }
    .ds-cr-success-icon { font-size: 48px; margin-bottom: 12px; }
    .ds-cr-success h3 { font-size: 22px; font-weight: 900; color: #fff; margin: 0 0 8px; }
    .ds-cr-success p { font-size: 14px; color: #cbd5e1; margin: 0; line-height: 1.65; }
    @media(max-width:600px) { .ds-cr-row-2 { grid-template-columns: 1fr; } .ds-cr-form { padding: 20px; } }
    </style>

    <div class="ds-cr-wrap">
        <div class="ds-cr-hero">
            <h1><span style="color:#fff;">Copyright</span> <span style="color:#E8481C;"><?php echo esc_html(apply_filters("ds_cr_hero_title", "Claim")); ?></span></h1>
            <p><?php echo esc_html(apply_filters("ds_cr_hero_subtitle", "If you believe your copyrighted work has been used without permission on DesignStager, submit a claim below.")); ?></p>
        </div>
        <div class="ds-cr-container">
            <div class="ds-cr-notice">
                <strong>Before you submit:</strong> <?php echo wp_kses_post(apply_filters("ds_cr_warning_text", "Please ensure you are the <strong>copyright owner</strong> or an authorized representative.")); ?> For questions, contact <a href="mailto:<?php echo esc_attr(apply_filters('ds_cr_legal_email', 'legal@designstager.com')); ?>"><?php echo esc_html(apply_filters('ds_cr_legal_email', 'legal@designstager.com')); ?></a>.
            </div>

            <div class="ds-cr-form" id="ds-cr-form-wrap">
                <div id="ds-cr-error" class="ds-cr-error"></div>

                <!-- Section 1: Your Details -->
                <div class="ds-cr-section-title">👤 Your Contact Details</div>
                <div class="ds-cr-row-2">
                    <div>
                        <label class="ds-cr-label">Full Name <span class="req">*</span></label>
                        <input class="ds-cr-input" type="text" id="ds-cr-name" placeholder="Your full legal name">
                    </div>
                    <div>
                        <label class="ds-cr-label">Email Address <span class="req">*</span></label>
                        <input class="ds-cr-input" type="email" id="ds-cr-email" placeholder="you@company.com">
                    </div>
                </div>
                <div class="ds-cr-row-2">
                    <div>
                        <label class="ds-cr-label">Company / Organisation <span class="req">*</span></label>
                        <input class="ds-cr-input" type="text" id="ds-cr-company" placeholder="Your company name">
                    </div>
                    <div>
                        <label class="ds-cr-label">Phone Number</label>
                        <input class="ds-cr-input" type="tel" id="ds-cr-phone" placeholder="+1 (555) 000-0000">
                    </div>
                </div>
                <div class="ds-cr-row">
                    <label class="ds-cr-label">Country</label>
                    <input class="ds-cr-input" type="text" id="ds-cr-country" placeholder="United States">
                </div>

                <!-- Section 2: Claim Details -->
                <div class="ds-cr-section-title" style="margin-top:28px;">⚖️ Claim Details</div>
                <div class="ds-cr-row">
                    <label class="ds-cr-label">Description of Your Original Work <span class="req">*</span></label>
                    <textarea class="ds-cr-textarea" id="ds-cr-work-desc" placeholder="Describe the original copyrighted work (e.g., logo design created in 2022, published at designcompany.com/portfolio)…"></textarea>
                </div>
                <div class="ds-cr-row">
                    <label class="ds-cr-label">URL of Your Original Work (if online)</label>
                    <input class="ds-cr-input" type="url" id="ds-cr-original-url" placeholder="https://yoursite.com/original-work">
                </div>
                <div class="ds-cr-row">
                    <label class="ds-cr-label">URL of the Infringing Content on DesignStager <span class="req">*</span></label>
                    <input class="ds-cr-input" type="url" id="ds-cr-inf-url" placeholder="https://designstager.com/marketplace/...">
                </div>
                <div class="ds-cr-row">
                    <label class="ds-cr-label">Detailed Claim Statement <span class="req">*</span></label>
                    <textarea class="ds-cr-textarea" id="ds-cr-statement" style="min-height:130px;" placeholder="Explain specifically how the content infringes your copyright. Include how you own the work, when it was created, and in what way it has been used without permission…"></textarea>
                </div>

                <!-- Section 3: Upload Documents -->
                <div class="ds-cr-section-title" style="margin-top:28px;">📎 Upload Supporting Documents</div>
                <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">Upload proof of ownership: copyright registration, original source files, invoices, screenshots etc. <strong>PDF, JPG or PNG only. Max 5MB each. Up to 3 files.</strong></p>
                <div class="ds-cr-upload-zone" id="ds-cr-dropzone">
                    <input type="file" id="ds-cr-files" accept=".pdf,.jpg,.jpeg,.png" multiple>
                    <div class="ds-cr-upload-icon">📂</div>
                    <div style="font-size:14px;font-weight:700;color:#1B2A4A;margin-bottom:4px;">Click to choose files or drag & drop</div>
                    <div class="ds-cr-upload-hint">PDF, JPG, PNG — max 5MB each — up to 3 files</div>
                </div>
                <div class="ds-cr-file-list" id="ds-cr-file-list"></div>

                <!-- Section 4: Declaration -->
                <div class="ds-cr-section-title" style="margin-top:28px;">✍️ Declaration</div>
                <div class="ds-cr-checkbox-row">
                    <input type="checkbox" id="ds-cr-sworn">
                    <label for="ds-cr-sworn">I have a good faith belief that the use of the copyrighted material described above is not authorised by the copyright owner, its agent, or the law. I declare under penalty of perjury that the information in this claim is accurate and that I am the copyright owner or am authorised to act on behalf of the owner. <strong style="color:#E8481C;">*</strong></label>
                </div>

                <div id="ds-cr-btn-wrap">
                    <button class="ds-cr-submit" id="ds-cr-submit" disabled>⚖️ Submit Copyright Claim</button>
                    <p style="text-align:center;font-size:11px;color:#9ca3af;margin:10px 0 0;">Your submission is encrypted and handled confidentially by the DesignStager Legal Team.</p>
                </div>
            </div>

            <!-- Success state -->
            <div class="ds-cr-success" id="ds-cr-success">
                <div class="ds-cr-success-icon">✅</div>
                <h3>Claim Submitted Successfully</h3>
                <p>Thank you. We have received your copyright claim and sent a confirmation to your email address.<br>Our legal team will review your submission within <strong>3–5 business days</strong> and contact you at the email provided.<br><br>Reference number: <strong id="ds-cr-ref">#—</strong></p>
            </div>
        </div>
    </div>

    <script>
    (function(){
    var _files = [];
    var inp    = document.getElementById('ds-cr-files');
    var list   = document.getElementById('ds-cr-file-list');
    var dz     = document.getElementById('ds-cr-dropzone');
    var sworn  = document.getElementById('ds-cr-sworn');
    var submit = document.getElementById('ds-cr-submit');
    var errEl  = document.getElementById('ds-cr-error');

    // Drag and drop visual
    dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.classList.add('drag'); });
    dz.addEventListener('dragleave', function(){ dz.classList.remove('drag'); });
    dz.addEventListener('drop', function(e){ e.preventDefault(); dz.classList.remove('drag'); handleFiles(e.dataTransfer.files); });

    inp.addEventListener('change', function(){ handleFiles(inp.files); inp.value = ''; });

    function handleFiles(fileList) {
        Array.from(fileList).forEach(function(f) {
            if (_files.length >= 3) { showErr('Maximum 3 files allowed.'); return; }
            if (f.size > 5 * 1024 * 1024) { showErr(f.name + ' is over 5MB.'); return; }
            if (!['image/jpeg','image/png','application/pdf'].includes(f.type)) { showErr(f.name + ': only PDF, JPG, PNG allowed.'); return; }
            _files.push(f);
        });
        renderFileList();
    }

    function renderFileList() {
        list.innerHTML = '';
        _files.forEach(function(f, i) {
            var item = document.createElement('div');
            item.className = 'ds-cr-file-item';
            var icon = f.type === 'application/pdf' ? '📄' : '🖼️';
            item.innerHTML = icon + ' ' + escHtml(f.name)
                + '<span class="size">' + (f.size / 1024).toFixed(0) + ' KB</span>'
                + '<button class="ds-cr-file-remove" data-i="' + i + '">✕</button>';
            list.appendChild(item);
        });
        list.querySelectorAll('.ds-cr-file-remove').forEach(function(btn){
            btn.onclick = function(){ _files.splice(parseInt(this.dataset.i), 1); renderFileList(); };
        });
    }

    sworn.addEventListener('change', function() {
        submit.disabled = !sworn.checked;
    });

    function showErr(msg) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
        errEl.scrollIntoView({behavior:'smooth', block:'nearest'});
    }
    function hideErr() { errEl.style.display = 'none'; }
    function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function val(id) { return (document.getElementById(id)||{}).value || ''; }

    submit.addEventListener('click', function() {
        hideErr();
        // Validate
        if (!val('ds-cr-name'))     { showErr('Please enter your full name.'); return; }
        if (!val('ds-cr-email'))    { showErr('Please enter your email address.'); return; }
        if (!val('ds-cr-company'))  { showErr('Please enter your company name.'); return; }
        if (!val('ds-cr-work-desc')){ showErr('Please describe your original work.'); return; }
        if (!val('ds-cr-inf-url'))  { showErr('Please enter the URL of the infringing content.'); return; }
        if (!val('ds-cr-statement')){ showErr('Please provide a detailed claim statement.'); return; }
        if (!sworn.checked)         { showErr('You must confirm the sworn declaration.'); return; }

        submit.disabled = true;
        submit.textContent = '⏳ Submitting…';

        // Read files as base64
        var filePromises = _files.map(function(f) {
            return new Promise(function(res) {
                var r = new FileReader();
                r.onload = function(e) {
                    res({ name: f.name, type: f.type, data: e.target.result.split(',')[1] });
                };
                r.readAsDataURL(f);
            });
        });

        Promise.all(filePromises).then(function(filesData) {
            return fetch(<?php echo wp_json_encode($api); ?>, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': <?php echo wp_json_encode($nonce); ?> },
                body: JSON.stringify({
                    claimant_name:    val('ds-cr-name'),
                    claimant_email:   val('ds-cr-email'),
                    company_name:     val('ds-cr-company'),
                    phone:            val('ds-cr-phone'),
                    country:          val('ds-cr-country'),
                    work_description: val('ds-cr-work-desc'),
                    original_url:     val('ds-cr-original-url'),
                    infringing_url:   val('ds-cr-inf-url'),
                    claim_statement:  val('ds-cr-statement'),
                    sworn:            true,
                    files:            filesData,
                })
            });
        }).then(function(r){ return r.json(); }).then(function(d) {
            if (d && d.success) {
                document.getElementById('ds-cr-form-wrap').style.display = 'none';
                var suc = document.getElementById('ds-cr-success');
                suc.style.display = 'block';
                document.getElementById('ds-cr-ref').textContent = '#' + (d.claim_id || '—');
                suc.scrollIntoView({behavior:'smooth'});
            } else {
                showErr(d.error || 'Submission failed. Please try again.');
                submit.disabled = false;
                submit.textContent = '⚖️ Submit Copyright Claim';
                sworn.checked && (submit.disabled = false);
            }
        }).catch(function() {
            showErr('Network error. Please check your connection and try again.');
            submit.disabled = false;
            submit.textContent = '⚖️ Submit Copyright Claim';
        });
    });
    })();
    </script>
    <?php
    return ob_get_clean();
});
