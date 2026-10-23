<?php
/**
 * Code Snippets export — Snippet 37 DS Terms and Conditions
 *
 * Snippet ID : 735
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-049
 * Modified   : 2026-09-09 02:42:38
 * Load order : 049 of 144
 */
/**
 * Snippet 37: DS Terms & Conditions Page
 * Run: Everywhere
 * NEW SNIPPET — does NOT modify any existing snippet
 *
 * Registers [ds_terms] shortcode for a styled Terms & Conditions page.
 * Paste [ds_terms] into any WordPress page to render it.
 *
 * ADMIN: Edit the terms text directly inside this snippet's $sections array
 * or use the WordPress Customizer (see ds_terms_update REST endpoint below).
 *
 * Colors match DesignStager brand:
 *   Orange-red #E8481C  |  Navy #1B2A4A  |  Mid #243a5a
 */

if ( ! defined('ABSPATH') ) exit;

add_shortcode('ds_terms', function () {

    // Get sections from admin editor (Snippet 33B) or use defaults
    $default_sections = array(
        array('icon'=>'','title'=>'1. Acceptance of Terms','body'=>'By accessing or using DesignStager ("the Service"), you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use the Service. We reserve the right to update these terms at any time with notice posted on this page.'),
        array('icon'=>'','title'=>'2. Use of the Service','body'=>'You may use DesignStager to generate AI presentations, download templates, and access marketplace assets for personal and commercial projects. You agree not to reverse-engineer, scrape, or misuse the platform.'),
        array('icon'=>'','title'=>'3. Accounts & Subscriptions','body'=>'You are responsible for maintaining the confidentiality of your account credentials. Free plan users receive limited daily AI generations as published on the Pricing page. We may suspend accounts that violate these terms.'),
        array('icon'=>'','title'=>'4. Intellectual Property','body'=>'All AI-generated presentations and designs you create using the Service are owned by you. DesignStager retains ownership of its platform, templates, underlying models, and source code.'),
        array('icon'=>'','title'=>'5. Prohibited Content','body'=>'You must not use the Service to generate content that is unlawful, defamatory, harassing, obscene, or that infringes the intellectual property rights of others.'),
        array('icon'=>'','title'=>'6. Privacy & Data','body'=>'Your use of the Service is also governed by our Privacy Policy. We collect minimal data necessary to operate the Service and do not sell your personal data to third parties.'),
        array('icon'=>'','title'=>'7. Payments & Refunds','body'=>'Payments are processed securely via our payment provider. Subscriptions renew automatically unless cancelled. We offer a 7-day refund window for new subscriptions.'),
        array('icon'=>'','title'=>'8. Disclaimer & Limitation of Liability','body'=>'The Service is provided "as is" without warranties of any kind. DesignStager is not liable for indirect, incidental, or consequential damages.'),
        array('icon'=>'','title'=>'9. Governing Law','body'=>'These Terms are governed by applicable laws. Disputes shall be resolved by binding arbitration or in the courts of the jurisdiction in which DesignStager operates.'),
        array('icon'=>'','title'=>'10. Contact','body'=>'For questions about these Terms, email us at <a href="mailto:legal@designstager.com" style="color:#E8481C;">legal@designstager.com</a> or use the Contact page.'),
    );

    $sections     = apply_filters('ds_terms_sections', $default_sections);
    $last_updated = apply_filters('ds_terms_updated_date', get_option('ds_terms_updated', '1 July 2025'));

    ob_start();
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');
    body.page.page-id-3, html body.page-id-3 { background: #10131c !important; }
        body.page-id-3 .entry-content, body.page-id-3 .page-content, body.page-id-3 .site-main { background: #10131c !important; }
        .ds-terms-wrap { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #10131c; min-height: 60vh; padding: 0 0 60px; }
    .ds-terms-hero { background: linear-gradient(135deg, #10131c 0%, #191c25 65%, #1f222b 100%); color: #fff; padding: 52px 24px 44px; text-align: center; }
    .ds-terms-hero h1 { margin: 0 0 8px; font-size: clamp(26px, 5vw, 44px); font-weight: 900; letter-spacing: -1px; color: #fff; }
    .ds-terms-hero h1 span { color: #E8481C; }
    .ds-terms-hero p { margin: 0; color: #C8D8F0; font-size: 14px; }
    .ds-terms-body { max-width: 820px; margin: 0 auto; padding: 40px 24px 0; }
    .ds-terms-intro { background: #191c25; border-left: 4px solid #E8481C; border-radius: 10px; padding: 18px 22px; margin-bottom: 32px; color: #cbd5e1; font-size: 14px; line-height: 1.7; }
    .ds-terms-intro strong { color: #fff; }
    .ds-terms-section { background: #191c25; border-radius: 14px; padding: 24px 26px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.12); transition: box-shadow .15s; }
    .ds-terms-section:hover { box-shadow: 0 4px 22px rgba(0,0,0,.4); }
    .ds-terms-section-hdr { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; cursor: pointer; }
    .ds-terms-section-icon { font-size: 22px; flex-shrink: 0; }
    .ds-terms-section-icon:empty { display: none; }
    .ds-terms-section-title { font-size: 16px; font-weight: 800; color: #fff; margin: 0; flex: 1; }
    .ds-terms-section-arrow { font-size: 14px; color: #E8481C; font-weight: 900; transition: transform .2s; }
    .ds-terms-section-arrow.open { transform: rotate(90deg); }
    .ds-terms-section-body { font-size: 14px; color: #cbd5e1; line-height: 1.75; display: none; padding-top: 4px; border-top: 1px solid rgba(255,255,255,.15); }
    .ds-terms-section-body.open { display: block; }
    .ds-terms-section-body a { color: #E8481C; }
    .ds-terms-toc { background: #191c25; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; border: 2px solid rgba(255,255,255,.15); }
    .ds-terms-toc h3 { margin: 0 0 12px; font-size: 13px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: .5px; }
    .ds-terms-toc-list { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px; }
    .ds-terms-toc-list li a { font-size: 12px; color: #cbd5e1; text-decoration: none; display: flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: 6px; transition: background .15s; }
    .ds-terms-toc-list li a:hover { background: #1f222b; color: #E8481C; }
    .ds-terms-expand-all { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #E8481C; cursor: pointer; background: none; border: none; padding: 0; margin-bottom: 20px; font-family: inherit; }
    .ds-terms-expand-all:hover { color: #fff; }
    .ds-terms-updated { text-align: center; margin-top: 40px; font-size: 12px; color: #8b97af; }
    @media(max-width:640px) { .ds-terms-toc-list { grid-template-columns: 1fr 1fr; } }
    </style>
    <div class="ds-terms-wrap">
        <div class="ds-terms-hero">
            <h1><span style="color:#fff;">Terms &amp;</span> <span style="color:#E8481C;">Conditions</span></h1>
            <p>Please read these terms carefully before using DesignStager.</p>
        </div>
        <div class="ds-terms-body">
            <div class="ds-terms-intro">
                <strong>Summary:</strong> By using DesignStager you agree to these terms. You own the designs you create. Marketplace assets come with their own licenses. We protect your data. Questions? <a href="mailto:legal@designstager.com" style="color:#E8481C;">Email us.</a>
            </div>

            <!-- Table of Contents -->
            <div class="ds-terms-toc">
                <h3>Quick navigation</h3>
                <ul class="ds-terms-toc-list">
                <?php foreach ($sections as $i => $s): ?>
                    <li><a href="#" onclick="dsTmsOpen(<?php echo $i; ?>);return false;"><?php echo esc_html(trim($s['icon'] . ' ' . $s['title'])); ?></a></li>
                <?php endforeach; ?>
                </ul>
            </div>

            <button class="ds-terms-expand-all" onclick="dsTmsToggleAll()">Expand all sections</button>

            <?php foreach ($sections as $i => $s): ?>
            <div class="ds-terms-section" id="ds-tms-<?php echo $i; ?>">
                <div class="ds-terms-section-hdr" onclick="dsTmsOpen(<?php echo $i; ?>)">
                    <span class="ds-terms-section-icon"><?php echo $s['icon']; ?></span>
                    <h3 class="ds-terms-section-title"><?php echo esc_html($s['title']); ?></h3>
                    <span class="ds-terms-section-arrow" id="ds-tms-arrow-<?php echo $i; ?>">▶</span>
                </div>
                <div class="ds-terms-section-body" id="ds-tms-body-<?php echo $i; ?>">
                    <br><?php echo $s['body']; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <p class="ds-terms-updated">Last updated: <?php echo esc_html($last_updated); ?></p>
        </div>
    </div>
    <script>
    var _dsTmsTotal = <?php echo count($sections); ?>;
    function dsTmsOpen(i) {
        var body  = document.getElementById('ds-tms-body-' + i);
        var arrow = document.getElementById('ds-tms-arrow-' + i);
        if (!body) return;
        var isOpen = body.classList.contains('open');
        body.classList.toggle('open', !isOpen);
        if (arrow) { arrow.classList.toggle('open', !isOpen); }
        if (!isOpen) { document.getElementById('ds-tms-' + i).scrollIntoView({behavior:'smooth', block:'nearest'}); }
    }
    var _dsTmsAllOpen = false;
    function dsTmsToggleAll() {
        _dsTmsAllOpen = !_dsTmsAllOpen;
        for (var i = 0; i < _dsTmsTotal; i++) {
            var body  = document.getElementById('ds-tms-body-'  + i);
            var arrow = document.getElementById('ds-tms-arrow-' + i);
            if (body)  body.classList.toggle('open',  _dsTmsAllOpen);
            if (arrow) arrow.classList.toggle('open', _dsTmsAllOpen);
        }
        var btn = document.querySelector('.ds-terms-expand-all');
        if (btn) btn.textContent = _dsTmsAllOpen ? 'Collapse all sections' : 'Expand all sections';
    }
    // Auto-open first section
    dsTmsOpen(0);
    </script>
    <?php
    return ob_get_clean();
});
