<?php
/**
 * Code Snippets export — Snippet 37B: DS Terms & Conditions Admin Editor
 *
 * Snippet ID : 739
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-053
 * Modified   : 2026-09-09 02:41:39
 * Load order : 053 of 144
 */
/**
 * Snippet 37B: DS Terms & Conditions Admin Editor
 * Run: Everywhere
 * Works alongside Snippet 33.
 *
 * Adds wp-admin -> Settings -> Terms & Conditions
 * where you can add, edit, remove and reorder sections
 * without touching any code.
 *
 * Snippet 33 automatically reads from this saved data.
 */

if ( ! defined('ABSPATH') ) exit;

// ── 1. Admin menu page ───────────────────────────────────────────────────────

add_action('admin_menu', function () {
    add_options_page(
        'Terms & Conditions Editor',
        'Terms & Conditions',
        'manage_options',
        'ds-terms-editor',
        'ds_terms_admin_page'
    );
});

// ── 2. Save handler ──────────────────────────────────────────────────────────

add_action('admin_init', function () {
    if (
        isset($_POST['ds_terms_save'])
        && check_admin_referer('ds_terms_save_action', 'ds_terms_nonce')
        && current_user_can('manage_options')
    ) {
        $icons  = array_map('sanitize_text_field', $_POST['ds_terms_icon']  ?? array());
        $titles = array_map('sanitize_text_field', $_POST['ds_terms_title'] ?? array());
        $bodies = array_map('wp_kses_post',         $_POST['ds_terms_body']  ?? array());

        $sections = array();
        foreach ($titles as $i => $title) {
            if ( empty(trim($title)) ) continue;
            $sections[] = array(
                'icon'  => $icons[$i]  ?? '',
                'title' => $title,
                'body'  => $bodies[$i] ?? '',
            );
        }

        update_option('ds_terms_sections', $sections);
        update_option('ds_terms_updated', sanitize_text_field($_POST['ds_terms_updated'] ?? date('j F Y')));

        wp_redirect(admin_url('options-general.php?page=ds-terms-editor&saved=1'));
        exit;
    }
});

// ── 3. Admin page HTML ───────────────────────────────────────────────────────

if ( ! function_exists('ds_terms_admin_page') ) {
function ds_terms_admin_page() {
    // Default sections if none saved yet
    $default = array(
        array('icon' => '👋', 'title' => '1. Acceptance of Terms',              'body' => 'By accessing or using DesignStager ("the Service"), you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use the Service. We reserve the right to update these terms at any time with notice posted on this page.'),
        array('icon' => '🛠️', 'title' => '2. Use of the Service',               'body' => 'You may use DesignStager to generate AI presentations, download templates, and access marketplace assets for personal and commercial projects, subject to any plan-level restrictions. You agree not to reverse-engineer, scrape, or misuse the platform.'),
        array('icon' => '🔑', 'title' => '3. Accounts & Subscriptions',         'body' => 'You are responsible for maintaining the confidentiality of your account credentials. Free plan users receive limited daily AI generations as published on the Pricing page. We may suspend accounts that violate these terms.'),
        array('icon' => '🖼️', 'title' => '4. Intellectual Property',            'body' => 'All AI-generated presentations and designs you create using the Service are owned by you. DesignStager retains ownership of its platform, templates, underlying models, and source code.'),
        array('icon' => '⚠️', 'title' => '5. Prohibited Content',               'body' => 'You must not use the Service to generate content that is unlawful, defamatory, harassing, obscene, or that infringes the intellectual property rights of others.'),
        array('icon' => '🔒', 'title' => '6. Privacy & Data',                   'body' => 'Your use of the Service is also governed by our Privacy Policy. We collect minimal data necessary to operate the Service and do not sell your personal data to third parties.'),
        array('icon' => '💳', 'title' => '7. Payments & Refunds',               'body' => 'Payments are processed securely via our payment provider. Subscriptions renew automatically unless cancelled before the renewal date. We offer a 7-day refund window for new subscriptions.'),
        array('icon' => '🚫', 'title' => '8. Disclaimer & Limitation of Liability', 'body' => 'The Service is provided "as is" without warranties of any kind. DesignStager is not liable for indirect, incidental, or consequential damages arising from your use of the Service.'),
        array('icon' => '🌍', 'title' => '9. Governing Law',                    'body' => 'These Terms are governed by applicable laws. Any disputes shall be resolved by binding arbitration or in the courts of the jurisdiction in which DesignStager operates.'),
        array('icon' => '📧', 'title' => '10. Contact',                         'body' => 'For questions about these Terms, email us at <a href="mailto:legal@designstager.com" style="color:#E8481C;">legal@designstager.com</a> or use the Contact page.'),
    );

    $sections     = get_option('ds_terms_sections', $default);
    $last_updated = get_option('ds_terms_updated', date('j F Y'));
    $saved        = isset($_GET['saved']);
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
    #ds-terms-wrap { font-family: 'Poppins', sans-serif; max-width: 860px; padding: 20px 0 60px; }
    #ds-terms-wrap h1 { font-size: 22px; font-weight: 800; color: #1B2A4A; margin: 0 0 6px; }
    #ds-terms-wrap h1 span { color: #E8481C; }
    .ds-t-saved { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 16px; color: #16a34a; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .ds-t-card { background: #fff; border: 1px solid #e8edf5; border-radius: 12px; padding: 20px; margin-bottom: 14px; position: relative; box-shadow: 0 2px 10px rgba(27,42,74,.06); }
    .ds-t-card-handle { cursor: grab; color: #9ca3af; font-size: 18px; margin-right: 8px; user-select: none; }
    .ds-t-row { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px; }
    .ds-t-icon-wrap { flex-shrink: 0; }
    .ds-t-icon-inp { width: 60px; padding: 8px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 20px; text-align: center; }
    .ds-t-title-inp { flex: 1; padding: 9px 12px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif; font-weight: 600; color: #1B2A4A; }
    .ds-t-title-inp:focus, .ds-t-icon-inp:focus { outline: none; border-color: #E8481C; }
    .ds-t-body-inp { width: 100%; padding: 10px 12px; border: 2px solid #e8edf5; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #374151; resize: vertical; min-height: 90px; box-sizing: border-box; line-height: 1.6; }
    .ds-t-body-inp:focus { outline: none; border-color: #E8481C; }
    .ds-t-remove { position: absolute; top: 14px; right: 14px; background: #fee2e2; color: #dc2626; border: none; border-radius: 6px; padding: 5px 10px; font-size: 11px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .ds-t-remove:hover { background: #dc2626; color: #fff; }
    .ds-t-add { background: #1B2A4A; color: #fff; border: none; border-radius: 8px; padding: 11px 22px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif; margin-right: 10px; }
    .ds-t-add:hover { background: #243a5a; }
    .ds-t-save { background: linear-gradient(135deg, #E8481C, #c93a15); color: #fff; border: none; border-radius: 8px; padding: 11px 28px; font-size: 14px; font-weight: 800; cursor: pointer; font-family: 'Poppins', sans-serif; }
    .ds-t-save:hover { opacity: .9; }
    .ds-t-section-num { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
    .ds-t-updated-wrap { background: #f4f6fb; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
    .ds-t-updated-wrap label { font-size: 12px; font-weight: 700; color: #1B2A4A; flex-shrink: 0; }
    .ds-t-updated-inp { padding: 7px 12px; border: 2px solid #e8edf5; border-radius: 7px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #374151; }
    .ds-t-updated-inp:focus { outline: none; border-color: #E8481C; }
    .ds-t-hint { font-size: 11px; color: #9ca3af; margin-bottom: 18px; line-height: 1.5; }
    </style>

    <div id="ds-terms-wrap">
        <h1>Terms &amp; <span>Conditions</span> Editor</h1>
        <p style="color:#6b7280;font-size:13px;margin:4px 0 20px;">Edit your Terms & Conditions sections here. Changes appear instantly on the <a href="<?php echo esc_url(home_url('/')); ?>terms-conditions/" target="_blank" style="color:#E8481C;">Terms page</a> after saving.</p>

        <?php if ($saved): ?>
        <div class="ds-t-saved">✅ Terms & Conditions saved successfully!</div>
        <?php endif; ?>

        <form method="post" id="ds-terms-form">
            <?php wp_nonce_field('ds_terms_save_action', 'ds_terms_nonce'); ?>
            <input type="hidden" name="ds_terms_save" value="1">

            <!-- Last Updated -->
            <div class="ds-t-updated-wrap">
                <label>Last Updated Date:</label>
                <input type="text" name="ds_terms_updated" class="ds-t-updated-inp"
                    value="<?php echo esc_attr($last_updated); ?>"
                    placeholder="e.g. 1 July 2025">
                <span style="font-size:11px;color:#9ca3af;">This date shows at the bottom of your Terms page.</span>
            </div>

            <p class="ds-t-hint">Drag sections to reorder them. Click Remove to delete a section. Click + Add Section to add a new one. HTML is allowed in the body text.</p>

            <!-- Sections list -->
            <div id="ds-terms-sections">
            <?php foreach ($sections as $i => $s): ?>
            <div class="ds-t-card" data-index="<?php echo $i; ?>">
                <div class="ds-t-section-num">Section <?php echo $i + 1; ?></div>
                <button type="button" class="ds-t-remove" onclick="dsTermsRemove(this)">✕ Remove</button>
                <div class="ds-t-row">
                    <div class="ds-t-icon-wrap">
                        <input type="text" name="ds_terms_icon[]" class="ds-t-icon-inp"
                            value="<?php echo esc_attr($s['icon']); ?>"
                            placeholder="👋" title="Emoji icon">
                    </div>
                    <input type="text" name="ds_terms_title[]" class="ds-t-title-inp"
                        value="<?php echo esc_attr($s['title']); ?>"
                        placeholder="Section title e.g. 1. Acceptance of Terms">
                </div>
                <textarea name="ds_terms_body[]" class="ds-t-body-inp"
                    placeholder="Section body text... HTML allowed e.g. <a href='...'>link</a>"><?php echo esc_textarea($s['body']); ?></textarea>
            </div>
            <?php endforeach; ?>
            </div>

            <!-- Action buttons -->
            <div style="margin-top:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" class="ds-t-add" onclick="dsTermsAdd()">+ Add Section</button>
                <button type="submit" class="ds-t-save">💾 Save Terms & Conditions</button>
                <a href="<?php echo esc_url(home_url('/')); ?>terms-conditions/" target="_blank"
                   style="font-size:12px;color:#E8481C;font-weight:700;text-decoration:none;">
                   View live page →
                </a>
            </div>
        </form>
    </div>

    <script>
    function dsTermsRemove(btn) {
        if (!confirm('Remove this section?')) return;
        btn.closest('.ds-t-card').remove();
        dsTermsReindex();
    }

    function dsTermsAdd() {
        var list = document.getElementById('ds-terms-sections');
        var count = list.querySelectorAll('.ds-t-card').length;
        var div = document.createElement('div');
        div.className = 'ds-t-card';
        div.innerHTML =
            '<div class="ds-t-section-num">Section ' + (count + 1) + '</div>'
            + '<button type="button" class="ds-t-remove" onclick="dsTermsRemove(this)">X Remove</button>'
            + '<div class="ds-t-row">'
            +   '<div class="ds-t-icon-wrap"><input type="text" name="ds_terms_icon[]" class="ds-t-icon-inp" placeholder="👋" title="Emoji icon"></div>'
            +   '<input type="text" name="ds_terms_title[]" class="ds-t-title-inp" placeholder="' + (count + 1) + '. New Section Title">'
            + '</div>'
            + '<textarea name="ds_terms_body[]" class="ds-t-body-inp" placeholder="Section body text... HTML allowed"></textarea>';
        list.appendChild(div);
        div.querySelector('input[name="ds_terms_title[]"]').focus();
    }

    function dsTermsReindex() {
        document.querySelectorAll('#ds-terms-sections .ds-t-card').forEach(function(card, i) {
            var label = card.querySelector('.ds-t-section-num');
            if (label) label.textContent = 'Section ' + (i + 1);
        });
    }

    // Drag to reorder
    (function() {
        var list = document.getElementById('ds-terms-sections');
        var dragging = null;

        list.addEventListener('mousedown', function(e) {
            var card = e.target.closest('.ds-t-card');
            if (!card || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'BUTTON') return;
            dragging = card;
            card.style.opacity = '0.5';
        });

        list.addEventListener('mouseover', function(e) {
            if (!dragging) return;
            var target = e.target.closest('.ds-t-card');
            if (target && target !== dragging) {
                var rect = target.getBoundingClientRect();
                var mid  = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    list.insertBefore(dragging, target);
                } else {
                    list.insertBefore(dragging, target.nextSibling);
                }
                dsTermsReindex();
            }
        });

        document.addEventListener('mouseup', function() {
            if (dragging) {
                dragging.style.opacity = '1';
                dragging = null;
            }
        });
    })();
    </script>
    <?php
}
}

// ── 4. Override Snippet 33 sections with admin-saved data ────────────────────

// Hook into Snippet 33's $sections variable by filtering it
add_filter('ds_terms_sections', function($default_sections) {
    $saved = get_option('ds_terms_sections', array());
    return ! empty($saved) ? $saved : $default_sections;
});

add_filter('ds_terms_updated_date', function($default_date) {
    return get_option('ds_terms_updated', $default_date);
});
