<?php
/**
 * Code Snippets export — DS Privacy Policy page [ds_privacy]
 *
 * Snippet ID : 1061
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager
 * Modified   : 2026-10-17
 * Load order : 261
 */
/**
 * DS Privacy Policy page - [ds_privacy]
 * Run: Everywhere
 * NEW SNIPPET - does not modify any existing snippet.
 *
 * Registers the [ds_privacy] shortcode: a Privacy Policy page in the same
 * style as [ds_terms] (Snippet 37). Put [ds_privacy] on any WordPress page.
 *
 * To change the text, edit the $sections array below, or return your own
 * sections from the 'ds_privacy_sections' filter. The contact address comes
 * from the 'ds_privacy_email' filter (default support@<this site's domain>).
 */

if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_privacy_shortcode') ) {
function ds_privacy_shortcode() {

    $host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
    $host = preg_replace( '/^(www|stagingtest)\./i', '', $host );
    $email = sanitize_email( apply_filters( 'ds_privacy_email', 'support@' . $host ) );
    $mail  = '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';

    $default_sections = array(
        array('title'=>'1. Who we are','body'=>'DesignStager ("we", "us") operates the website ' . esc_html( $host ) . ' and the DesignStager add-in for Microsoft PowerPoint (together, "the Service"). This policy explains what personal information we collect, why we collect it and the choices you have. For any privacy question, contact us at ' . $mail . '.'),
        array('title'=>'2. Information we collect','body'=>'<strong>Account details:</strong> your name, email address and password (stored in hashed form) when you sign up, and your plan and credit balance.<br><br><strong>Content you give us:</strong> the prompts and text you type, the documents and pictures you upload, and the presentations, images, video and audio you generate or save.<br><br><strong>Purchase details:</strong> the plan you buy, the amount and the payment status. Card and bank details are entered with our payment provider and are not stored on our servers.<br><br><strong>Usage and device data:</strong> pages visited, downloads, generation history, IP address, browser type and similar log data, and cookies as described below.<br><br><strong>Sign-in with Google or Microsoft:</strong> if you choose to sign in this way, we receive your name, your email address and an account identifier from that provider. We never receive your Google or Microsoft password.<br><br><strong>Messages:</strong> what you send us through the contact, project-request and copyright forms or by email.'),
        array('title'=>'3. How we use your information','body'=>'We use your information to provide the Service: to create and secure your account, generate and store the content you ask for, apply your plan, credits and download limits, process payments and referrals, answer your requests, send service emails (such as email verification and receipts), prevent abuse and fraud, and understand how the Service is used so we can improve it. We do not sell your personal information.'),
        array('title'=>'4. The PowerPoint add-in','body'=>'The add-in shows the DesignStager library inside PowerPoint and inserts the slides, pictures, icons, video and audio you choose into your open presentation. It only adds content that you pick. It does not read, collect or upload the existing content of your presentation, and it does not access other files on your device. To keep you signed in, the add-in stores a sign-in token in its own browser storage on your device; signing out removes it. When you sign in through the add-in, we handle your account information as described in this policy. Nothing can be bought inside the add-in: plans and credits are bought only on designstager.com.'),
        array('title'=>'5. AI features','body'=>'When you use an AI feature, the prompt, text or file you submit is sent to the AI service that produces the result, and the result is returned to you and saved in your account. Our AI processing partners are Cloudflare, Replicate, WaveSpeed, Google and OpenAI; they receive only what is needed to create your result. We do not use your prompts, uploads or creations to train AI models. Please do not include sensitive personal information in prompts or uploaded files.'),
        array('title'=>'6. Sharing your information','body'=>'We share personal information only with service providers that help us run the Service, and only as needed for that purpose: website hosting and file storage, payment processing, AI generation, email delivery, security (for example bot protection) and analytics. We may also disclose information when the law requires it, or to protect the rights and safety of our users and of DesignStager. Content you choose to publish or share (for example a public share link or a marketplace submission) is visible to others.'),
        array('title'=>'7. Cookies','body'=>'We use cookies that are needed for the Service to work (for example to keep you signed in and remember your settings), and analytics cookies that help us measure how the site is used. You can block or delete cookies in your browser settings; some features may then stop working.'),
        array('title'=>'8. How long we keep information','body'=>'We keep your account information for as long as your account is open. Generated and uploaded files are kept for the period that applies to your plan and may be removed automatically after that. When you delete your account, we delete or anonymise your personal information, except where we must keep records for legal, tax or security reasons.'),
        array('title'=>'9. Your choices and rights','body'=>'You can view your account details in your account, cancel a subscription, and delete your account at any time with the "Delete my account" option. Depending on where you live, you may also have the right to ask for a copy of your personal information, to have it corrected or deleted, or to object to certain uses. To make a request, email ' . $mail . '. We reply within a reasonable time.'),
        array('title'=>'10. Security','body'=>'We use reasonable technical and organisational measures to protect your information, including encrypted connections (HTTPS) and restricted access. No online service can be completely secure, so please use a strong password and keep it private.'),
        array('title'=>'11. Children','body'=>'The Service is not directed to children under 13, and we do not knowingly collect personal information from them. If you believe a child has given us personal information, contact us and we will delete it.'),
        array('title'=>'12. International transfers','body'=>'DesignStager is operated from India, and our service providers may process information in other countries. By using the Service, you understand that your information may be processed outside the country where you live.'),
        array('title'=>'13. Changes to this policy','body'=>'We may update this policy from time to time. The date at the bottom of this page shows when it was last updated. If a change is significant, we will give notice on the website.'),
        array('title'=>'14. Contact','body'=>'Questions or requests about this policy or your personal information: ' . $mail . ', or use the Contact page.'),
    );

    $sections     = apply_filters('ds_privacy_sections', $default_sections);
    $last_updated = apply_filters('ds_privacy_updated_date', '17 October 2026');

    ob_start();
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');
    .ds-privacy-wrap { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #10131c; min-height: 60vh; padding: 0 0 60px; }
    .ds-privacy-hero { background: linear-gradient(135deg, #10131c 0%, #191c25 65%, #1f222b 100%); color: #fff; padding: 52px 24px 44px; text-align: center; }
    .ds-privacy-hero h1 { margin: 0 0 8px; font-size: clamp(26px, 5vw, 44px); font-weight: 900; letter-spacing: -1px; color: #fff; }
    .ds-privacy-hero h1 span { color: #E8481C; }
    .ds-privacy-hero p { margin: 0; color: #C8D8F0; font-size: 14px; }
    .ds-privacy-body { max-width: 820px; margin: 0 auto; padding: 40px 24px 0; }
    .ds-privacy-intro { background: #191c25; border-left: 4px solid #E8481C; border-radius: 10px; padding: 18px 22px; margin-bottom: 32px; color: #cbd5e1; font-size: 14px; line-height: 1.7; }
    .ds-privacy-intro strong { color: #fff; }
    .ds-privacy-section { background: #191c25; border-radius: 14px; padding: 24px 26px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.12); }
    .ds-privacy-section h2 { font-size: 16px; font-weight: 800; color: #fff; margin: 0 0 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,.15); }
    .ds-privacy-section-body { font-size: 14px; color: #cbd5e1; line-height: 1.75; }
    .ds-privacy-section-body strong { color: #fff; }
    .ds-privacy-wrap a { color: #E8481C; }
    .ds-privacy-updated { text-align: center; margin-top: 40px; font-size: 12px; color: #8b97af; }
    </style>
    <div class="ds-privacy-wrap">
        <div class="ds-privacy-hero">
            <h1><span style="color:#fff;">Privacy</span> <span>Policy</span></h1>
            <p>How DesignStager collects, uses and protects your information.</p>
        </div>
        <div class="ds-privacy-body">
            <div class="ds-privacy-intro">
                <strong>Summary:</strong> We collect only what we need to run DesignStager: your account details, the content you create, and basic usage data. We do not sell your personal information. You can delete your account at any time. Questions? <?php echo $mail; // phpcs:ignore ?>
            </div>
            <?php foreach ( (array) $sections as $s ) : ?>
            <div class="ds-privacy-section">
                <h2><?php echo esc_html( isset( $s['title'] ) ? $s['title'] : '' ); ?></h2>
                <div class="ds-privacy-section-body"><?php echo wp_kses_post( isset( $s['body'] ) ? $s['body'] : '' ); ?></div>
            </div>
            <?php endforeach; ?>
            <p class="ds-privacy-updated">Last updated: <?php echo esc_html( $last_updated ); ?></p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ds_privacy', 'ds_privacy_shortcode' );
}
